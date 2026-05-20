<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['_action'] ?? '';
$id     = $_REQUEST['_id'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = getDB();

// ── GET /api/admin/stats ─────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $stats = [];

    $stats['total_movies']     = (int)$db->query('SELECT COUNT(*) FROM movies')->fetchColumn();
    $stats['published_movies'] = (int)$db->query('SELECT COUNT(*) FROM movies WHERE status = "published"')->fetchColumn();
    $stats['draft_movies']     = (int)$db->query('SELECT COUNT(*) FROM movies WHERE status = "draft"')->fetchColumn();
    $stats['total_users']      = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['total_reviews']    = (int)$db->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
    $stats['total_categories'] = (int)$db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    $stats['total_watchlists'] = (int)$db->query('SELECT COUNT(*) FROM watchlist')->fetchColumn();

    $topMovies = $db->query(
        'SELECT m.id, m.title, m.slug, m.rating, m.rating_count, m.poster,
                COUNT(w.id) AS watchlist_count
         FROM movies m
         LEFT JOIN watchlist w ON w.movie_id = m.id
         GROUP BY m.id
         ORDER BY watchlist_count DESC
         LIMIT 5'
    )->fetchAll();
    $stats['top_movies'] = $topMovies;

    $recentReviews = $db->query(
        'SELECT r.id, r.rating, r.review_text, r.created_at, u.username, m.title AS movie_title
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         JOIN movies m ON m.id = r.movie_id
         ORDER BY r.created_at DESC
         LIMIT 5'
    )->fetchAll();
    $stats['recent_reviews'] = $recentReviews;

    echo json_encode(['data' => $stats]);
    exit;
}

// ── GET /api/admin/users ─────────────────────────────────────────────────────
if ($method === 'GET' && $action === 'users') {
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');

    $where  = [];
    $params = [];
    if ($search) {
        $where[]  = '(username LIKE ? OR email LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int)$db->prepare("SELECT COUNT(*) FROM users $whereSQL")->execute($params) ? $db->prepare("SELECT COUNT(*) FROM users $whereSQL")->execute($params) : 0;
    $countStmt = $db->prepare("SELECT COUNT(*) FROM users $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare("SELECT id, username, email, role, avatar, created_at FROM users $whereSQL ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    $stmt->execute($params);

    echo json_encode([
        'data'       => $stmt->fetchAll(),
        'total'      => $total,
        'page'       => $page,
        'totalPages' => ceil($total / $limit),
    ]);
    exit;
}

// ── POST /api/admin/users ── create user ─────────────────────────────────────
if ($method === 'POST' && $action === 'users') {
    $username = trim($body['username'] ?? '');
    $email    = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';
    $role     = in_array($body['role'] ?? '', ['user', 'admin']) ? $body['role'] : 'user';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Username, email and password are required.']);
        exit;
    }

    $check = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $check->execute([$email, $username]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email or username already taken.']);
        exit;
    }

    $db->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)')
       ->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $role]);

    $newId = $db->lastInsertId();
    $stmt  = $db->prepare('SELECT id, username, email, role, avatar, created_at FROM users WHERE id = ?');
    $stmt->execute([$newId]);

    http_response_code(201);
    echo json_encode(['message' => 'User created.', 'data' => $stmt->fetch()]);
    exit;
}

// ── PUT /api/admin/users/{id} ── update user ─────────────────────────────────
if ($method === 'PUT' && $action === 'users' && $id) {
    $fields = [];
    $params = [];

    foreach (['username', 'email'] as $f) {
        if (array_key_exists($f, $body)) {
            $fields[] = "$f = ?";
            $params[] = $body[$f];
        }
    }
    if (array_key_exists('role', $body) && in_array($body['role'], ['user', 'admin'])) {
        $fields[] = 'role = ?';
        $params[] = $body['role'];
    }
    if (!empty($body['password'])) {
        $fields[] = 'password = ?';
        $params[] = password_hash($body['password'], PASSWORD_BCRYPT);
    }

    if ($fields) {
        $params[] = $id;
        $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    $stmt = $db->prepare('SELECT id, username, email, role, avatar, created_at FROM users WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['message' => 'User updated.', 'data' => $stmt->fetch()]);
    exit;
}

// ── DELETE /api/admin/users/{id} ─────────────────────────────────────────────
if ($method === 'DELETE' && $action === 'users' && $id) {
    if ((int)$id === currentUserId()) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete your own account.']);
        exit;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found.']);
        exit;
    }

    $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    echo json_encode(['message' => 'User deleted.']);
    exit;
}

// ── POST /api/admin/movies/bulk ── bulk status update ────────────────────────
if ($method === 'POST' && $action === 'bulk') {
    $ids    = array_map('intval', $body['ids'] ?? []);
    $status = in_array($body['status'] ?? '', ['published', 'draft']) ? $body['status'] : null;

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['error' => 'ids array is required.']);
        exit;
    }

    if ($status) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->prepare("UPDATE movies SET status = ? WHERE id IN ($placeholders)")
           ->execute(array_merge([$status], $ids));
        echo json_encode(['message' => count($ids) . ' movies updated to ' . $status . '.']);
        exit;
    }

    if (($body['action'] ?? '') === 'delete') {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->prepare("DELETE FROM movies WHERE id IN ($placeholders)")->execute($ids);
        echo json_encode(['message' => count($ids) . ' movies deleted.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'No valid bulk action specified.']);
    exit;
}

// ── GET /api/admin/platforms ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'platforms') {
    $stmt = $db->query('SELECT * FROM platforms ORDER BY name ASC');
    echo json_encode(['data' => $stmt->fetchAll()]);
    exit;
}

// ── POST /api/admin/platforms ─────────────────────────────────────────────────
if ($method === 'POST' && $action === 'platforms') {
    $name = trim($body['name'] ?? '');
    $logo = trim($body['logo'] ?? '');

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'Platform name is required.']);
        exit;
    }

    $db->prepare('INSERT INTO platforms (name, logo) VALUES (?, ?)')->execute([$name, $logo ?: null]);
    $newId = $db->lastInsertId();
    $stmt  = $db->prepare('SELECT * FROM platforms WHERE id = ?');
    $stmt->execute([$newId]);

    http_response_code(201);
    echo json_encode(['message' => 'Platform created.', 'data' => $stmt->fetch()]);
    exit;
}

// ── DELETE /api/admin/platforms/{id} ─────────────────────────────────────────
if ($method === 'DELETE' && $action === 'platforms' && $id) {
    $db->prepare('DELETE FROM platforms WHERE id = ?')->execute([$id]);
    echo json_encode(['message' => 'Platform deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
