-- Messenger schema (PostgreSQL)
-- 3NF normalized, indexes included

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Users
CREATE TABLE users (
    id              SERIAL PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    nickname        VARCHAR(50)  UNIQUE,
    avatar          VARCHAR(255),
    email_hidden    BOOLEAN      NOT NULL DEFAULT FALSE,
    email_verified  BOOLEAN      NOT NULL DEFAULT FALSE,
    verify_token    VARCHAR(64),
    created_at      TIMESTAMP    NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_users_email    ON users (email);
CREATE INDEX idx_users_nickname ON users (nickname);

-- Contacts (friendship is directional: user_id added contact_id)
CREATE TABLE contacts (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    contact_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE (user_id, contact_id),
    CHECK (user_id <> contact_id)
);

CREATE INDEX idx_contacts_user    ON contacts (user_id);
CREATE INDEX idx_contacts_contact ON contacts (contact_id);

-- Chats (private 1-to-1)
CREATE TABLE chats (
    id         SERIAL PRIMARY KEY,
    user1_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    user2_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE (user1_id, user2_id),
    CHECK (user1_id < user2_id)
);

CREATE INDEX idx_chats_user1 ON chats (user1_id);
CREATE INDEX idx_chats_user2 ON chats (user2_id);

-- Groups
CREATE TABLE groups (
    id         SERIAL PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    creator_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_groups_creator ON groups (creator_id);

-- Group members
CREATE TABLE group_members (
    group_id   INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    joined_at  TIMESTAMP NOT NULL DEFAULT NOW(),
    PRIMARY KEY (group_id, user_id)
);

CREATE INDEX idx_group_members_user ON group_members (user_id);

-- Messages
CREATE TABLE messages (
    id          SERIAL PRIMARY KEY,
    sender_id   INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    chat_id     INTEGER REFERENCES chats(id)  ON DELETE CASCADE,
    group_id    INTEGER REFERENCES groups(id) ON DELETE CASCADE,
    body        TEXT NOT NULL,
    edited      BOOLEAN   NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMP NOT NULL DEFAULT NOW(),
    CHECK (
        (chat_id IS NOT NULL AND group_id IS NULL) OR
        (chat_id IS NULL AND group_id IS NOT NULL)
    )
);

CREATE INDEX idx_messages_chat    ON messages (chat_id,  created_at);
CREATE INDEX idx_messages_group   ON messages (group_id, created_at);
CREATE INDEX idx_messages_sender  ON messages (sender_id);
