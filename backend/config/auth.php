<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        die(json_encode(['error' => 'Unauthorized. Please log in.']));
    }
}

function requireAdmin(): void {
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die(json_encode(['error' => 'Forbidden. Admin access required.']));
    }
}

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function setSession(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['avatar']   = $user['avatar'];
}

function sessionData(): array {
    return [
        'id'       => $_SESSION['user_id']  ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email'    => $_SESSION['email']    ?? null,
        'role'     => $_SESSION['role']     ?? null,
        'avatar'   => $_SESSION['avatar']   ?? null,
    ];
}
