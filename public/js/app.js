'use strict';

// ── Helpers ────────────────────────────────────────────────────
function post(url, data) {
    data.csrf_token = CSRF;
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data).toString(),
    }).then(r => r.json());
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatTime(iso) {
    const d = new Date(iso);
    return d.toLocaleDateString('ru-RU') + ' ' +
           d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

// ── Identify current chat / group ──────────────────────────────
const msgBox = document.getElementById('messages');
if (msgBox) {
    const chatId  = msgBox.dataset.chatId  ? parseInt(msgBox.dataset.chatId)  : null;
    const groupId = msgBox.dataset.groupId ? parseInt(msgBox.dataset.groupId) : null;
    let lastId    = 0;

    msgBox.querySelectorAll('.message[data-id]').forEach(el => {
        const id = parseInt(el.dataset.id);
        if (id > lastId) lastId = id;
    });

    function scrollBottom() { msgBox.scrollTop = msgBox.scrollHeight; }
    scrollBottom();

    // ── Render incoming message ────────────────────────────────
    function renderMessage(m) {
        const isMine = parseInt(m.sender_id) === ME_ID;
        const cls    = isMine ? 'justify-content-end' : 'justify-content-start';
        const bCls   = isMine ? 'bubble-mine' : 'bubble-theirs';
        const name   = escapeHtml(m.nickname || m.email || 'User');
        const body   = escapeHtml(m.body);
        const time   = formatTime(m.created_at);

        const div = document.createElement('div');
        div.className = `message d-flex ${cls}`;
        div.dataset.id = m.id;
        div.innerHTML = `
            <div class="message-bubble ${bCls}">
                <div class="d-flex gap-2 mb-1">
                    <span class="fw-semibold" style="font-size:11px;color:#666">${name}</span>
                    <span class="text-muted" style="font-size:11px">${time}</span>
                    ${m.edited ? '<span class="text-muted fst-italic" style="font-size:11px">(edited)</span>' : ''}
                </div>
                <div class="message-body" id="msg-body-${m.id}">${body}</div>
            </div>`;
        msgBox.appendChild(div);
        if (parseInt(m.id) > lastId) lastId = parseInt(m.id);
    }

    // ── Long Polling ───────────────────────────────────────────
    function poll() {
        const params = new URLSearchParams({ last_id: lastId });
        if (chatId)  params.set('chat_id',  chatId);
        if (groupId) params.set('group_id', groupId);

        fetch('/messages/poll?' + params.toString())
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => {
                        if (parseInt(m.id) > lastId) {
                            lastId = parseInt(m.id);
                            renderMessage(m);
                        }
                    });
                    scrollBottom();
                }
            })
            .catch(() => {})
            .finally(() => setTimeout(poll, 200));
    }
    poll();

    // ── Send message ───────────────────────────────────────────
    const form  = document.getElementById('message-form');
    const input = document.getElementById('msg-input');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const body = input.value.trim();
        if (!body) return;
        const data = { body };
        if (chatId)  data.chat_id  = chatId;
        if (groupId) data.group_id = groupId;
        post('/messages/send', data).then(d => { if (d.ok) input.value = ''; });
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.dispatchEvent(new Event('submit')); }
    });

    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ── Right-click context menu ───────────────────────────────
    let ctxMenu = null;

    function removeCtx() { if (ctxMenu) { ctxMenu.remove(); ctxMenu = null; } }
    document.addEventListener('click', removeCtx);

    msgBox.addEventListener('contextmenu', function (e) {
        const msgEl = e.target.closest('.message');
        if (!msgEl || !msgEl.dataset.id) return;
        // Only show for own messages (bubble-mine present)
        if (!msgEl.querySelector('.bubble-mine')) return;
        e.preventDefault();
        removeCtx();

        const id = parseInt(msgEl.dataset.id);
        ctxMenu = document.createElement('ul');
        ctxMenu.className = 'ctx-menu';
        ctxMenu.style.top  = e.clientY + 'px';
        ctxMenu.style.left = e.clientX + 'px';
        ctxMenu.innerHTML  = `
            <li onclick="editMessage(${id})">Редактировать</li>
            <li onclick="deleteMessage(${id})">Удалить</li>`;
        document.body.appendChild(ctxMenu);
    });

    // ── Edit / Delete ──────────────────────────────────────────
    window.editMessage = function (id) {
        removeCtx();
        const bodyEl = document.getElementById('msg-body-' + id);
        if (!bodyEl) return;
        const newBody = prompt('Редактировать сообщение:', bodyEl.textContent);
        if (newBody === null || !newBody.trim()) return;
        post('/messages/edit', { id, body: newBody.trim() })
            .then(d => { if (d.ok) bodyEl.textContent = newBody.trim(); });
    };

    window.deleteMessage = function (id) {
        removeCtx();
        if (!confirm('Удалить это сообщение?')) return;
        post('/messages/delete', { id }).then(d => {
            if (d.ok) {
                const el = document.querySelector(`.message[data-id="${id}"]`);
                if (el) el.remove();
            }
        });
    };

    // ── Add member (group) via Bootstrap modal ─────────────────
    const addBtn  = document.getElementById('add-member-btn');
    const mSearch = document.getElementById('member-search');
    const mResults = document.getElementById('member-results');

    if (addBtn && groupId) {
        let bsModal;
        addBtn.addEventListener('click', () => {
            if (!bsModal) bsModal = new bootstrap.Modal(document.getElementById('addMemberModal'));
            bsModal.show();
        });

        let mTimer;
        mSearch && mSearch.addEventListener('input', function () {
            clearTimeout(mTimer);
            const q = this.value.trim();
            if (q.length < 2) { mResults.innerHTML = ''; return; }
            mTimer = setTimeout(() => {
                fetch('/contacts/search?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(users => {
                        mResults.innerHTML = '';
                        users.forEach(u => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item list-group-item-action';
                            li.style.cursor = 'pointer';
                            li.textContent = u.nickname || u.email || 'User';
                            li.addEventListener('click', () => {
                                post('/groups/' + groupId + '/add-member', { user_id: u.id })
                                    .then(d => { if (d.ok && bsModal) bsModal.hide(); });
                            });
                            mResults.appendChild(li);
                        });
                    });
            }, 300);
        });
    }
}

// ── User search → open/create DM ──────────────────────────────
const searchInput   = document.getElementById('user-search');
const searchResults = document.getElementById('search-results');

if (searchInput) {
    let timer;
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { searchResults.classList.add('d-none'); return; }
        timer = setTimeout(() => {
            fetch('/contacts/search?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(users => {
                    searchResults.innerHTML = '';
                    if (!users.length) {
                        searchResults.innerHTML = '<li class="text-muted">Пользователи не найдены</li>';
                    } else {
                        users.forEach(u => {
                            const li = document.createElement('li');
                            li.textContent = u.nickname || u.email || 'User';
                            li.dataset.id  = u.id;
                            li.addEventListener('click', () => openChat(u.id));
                            searchResults.appendChild(li);
                        });
                    }
                    searchResults.classList.remove('d-none');
                });
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.classList.add('d-none');
        }
    });

    function openChat(contactId) {
        post('/contacts/add', { contact_id: contactId })
            .then(() => post('/chats/create', { contact_id: contactId }))
            .then(d => { if (d.chat_id) location.href = '/chats/' + d.chat_id; });
    }
}
