<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['_action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// POST /api/auth/register
if ($method === 'POST' && $action === 'register') {
    $username = trim($body['username'] ?? '');
    $email    = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username, email and password are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address.']);
        exit;
    }
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters.']);
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email or username already taken.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$username, $email, $hash]);
    $userId = $db->lastInsertId();

    $user = $db->prepare('SELECT id, username, email, role, avatar FROM users WHERE id = ?');
    $user->execute([$userId]);
    $userData = $user->fetch();
    setSession($userData);

    http_response_code(201);
    echo json_encode(['message' => 'Registration successful.', 'user' => sessionData()]);
    exit;
}

// POST /api/auth/login
if ($method === 'POST' && $action === 'login') {
    $email    = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required.']);
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, username, email, password, role, avatar FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password.']);
        exit;
    }

    setSession($user);
    echo json_encode(['message' => 'Login successful.', 'user' => sessionData()]);
    exit;
}

// POST /api/auth/logout
if ($method === 'POST' && $action === 'logout') {
    session_destroy();
    echo json_encode(['message' => 'Logged out successfully.']);
    exit;
}

// GET /api/auth/me
if ($method === 'GET' && $action === 'me') {
    if (!isLoggedIn()) {
        echo json_encode(['user' => null]);
        exit;
    }
    echo json_encode(['user' => sessionData()]);
    exit;
}

// PUT /api/auth/profile — update username/email/avatar
if ($method === 'PUT' && $action === 'profile') {
    requireAuth();
    $username = trim($body['username'] ?? '');
    $email    = trim($body['email'] ?? '');
    $avatar   = trim($body['avatar'] ?? '');

    if (!$username || !$email) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and email are required.']);
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?');
    $stmt->execute([$email, $username, currentUserId()]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email or username already taken.']);
        exit;
    }

    $db->prepare('UPDATE users SET username = ?, email = ?, avatar = ? WHERE id = ?')
       ->execute([$username, $email, $avatar ?: null, currentUserId()]);

    $_SESSION['username'] = $username;
    $_SESSION['email']    = $email;
    $_SESSION['avatar']   = $avatar ?: null;

    echo json_encode(['message' => 'Profile updated.', 'user' => sessionData()]);
    exit;
}

// PUT /api/auth/password — change password
if ($method === 'PUT' && $action === 'password') {
    requireAuth();
    $current = $body['current_password'] ?? '';
    $new     = $body['new_password'] ?? '';

    if (!$current || !$new) {
        http_response_code(400);
        echo json_encode(['error' => 'Current and new password are required.']);
        exit;
    }
    if (strlen($new) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'New password must be at least 6 characters.']);
        exit;
    }

    $db   = getDB();
    $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([currentUserId()]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Current password is incorrect.']);
        exit;
    }

    $db->prepare('UPDATE users SET password = ? WHERE id = ?')
       ->execute([password_hash($new, PASSWORD_BCRYPT), currentUserId()]);

    echo json_encode(['message' => 'Password changed successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
