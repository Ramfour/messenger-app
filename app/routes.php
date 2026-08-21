<?php
declare(strict_types=1);

/** @var \App\Core\Router $router */

// Auth
$router->get('/register',        'AuthController', 'showRegister');
$router->post('/register',       'AuthController', 'register');
$router->get('/login',           'AuthController', 'showLogin');
$router->post('/login',          'AuthController', 'login');
$router->get('/logout',          'AuthController', 'logout');
$router->get('/verify/{token}',  'AuthController', 'verify');

// Profile
$router->get('/profile',         'ProfileController', 'show');
$router->post('/profile',        'ProfileController', 'update');
$router->post('/profile/avatar', 'ProfileController', 'uploadAvatar');

// Contacts
$router->get('/contacts',        'ContactController', 'index');
$router->get('/contacts/search', 'ContactController', 'search');
$router->post('/contacts/add',   'ContactController', 'add');
$router->post('/contacts/remove','ContactController', 'remove');

// Chats
$router->get('/chats',           'ChatController', 'index');
$router->get('/chats/{id}',      'ChatController', 'show');
$router->post('/chats/create',   'ChatController', 'create');

// Groups
$router->get('/groups/create',   'GroupController', 'showCreate');
$router->post('/groups/create',  'GroupController', 'create');
$router->get('/groups/{id}',     'GroupController', 'show');
$router->post('/groups/{id}/add-member', 'GroupController', 'addMember');

// Messages (AJAX)
$router->post('/messages/send',   'MessageController', 'send');
$router->post('/messages/edit',   'MessageController', 'edit');
$router->post('/messages/delete', 'MessageController', 'delete');
$router->get('/messages/poll',    'MessageController', 'poll');

// Home
$router->get('/', 'AuthController', 'showLogin');
