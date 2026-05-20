<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_REQUEST['_id'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = getDB();

// ── GET /api/categories ──────────────────────────────────────────────────────
if ($method === 'GET' && !$id) {
    $stmt = $db->query(
        'SELECT c.*, COUNT(mc.movie_id) AS movie_count
         FROM categories c
         LEFT JOIN movie_categories mc ON mc.category_id = c.id
         GROUP BY c.id
         ORDER BY c.name ASC'
    );
    echo json_encode(['data' => $stmt->fetchAll()]);
    exit;
}

// ── GET /api/categories/{id} ─────────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    if (!$cat) {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found.']);
        exit;
    }
    echo json_encode(['data' => $cat]);
    exit;
}

// ── POST /api/categories ─────────────────────────────────────────────────────
if ($method === 'POST') {
    requireAdmin();

    $name  = trim($body['name'] ?? '');
    $color = trim($body['color'] ?? 'from-gray-500 to-gray-700');
    $icon  = trim($body['icon'] ?? 'Clapperboard');

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'Name is required.']);
        exit;
    }

    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));

    $check = $db->prepare('SELECT id FROM categories WHERE slug = ?');
    $check->execute([$slug]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Category already exists.']);
        exit;
    }

    $db->prepare('INSERT INTO categories (name, slug, color, icon) VALUES (?, ?, ?, ?)')->execute([$name, $slug, $color, $icon]);
    $newId = $db->lastInsertId();

    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$newId]);

    http_response_code(201);
    echo json_encode(['message' => 'Category created.', 'data' => $stmt->fetch()]);
    exit;
}

// ── PUT /api/categories/{id} ─────────────────────────────────────────────────
if ($method === 'PUT' && $id) {
    requireAdmin();

    $fields = [];
    $params = [];

    foreach (['name', 'color', 'icon'] as $f) {
        if (array_key_exists($f, $body)) {
            $fields[] = "$f = ?";
            $params[] = $body[$f];
        }
    }
    if (array_key_exists('name', $body)) {
        $fields[] = 'slug = ?';
        $params[] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $body['name']));
    }

    if ($fields) {
        $params[] = $id;
        $db->prepare('UPDATE categories SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['message' => 'Category updated.', 'data' => $stmt->fetch()]);
    exit;
}

// ── DELETE /api/categories/{id} ──────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    requireAdmin();

    $stmt = $db->prepare('SELECT id FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found.']);
        exit;
    }

    $db->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    echo json_encode(['message' => 'Category deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
