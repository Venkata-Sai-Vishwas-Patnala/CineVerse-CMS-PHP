<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_REQUEST['_id'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = getDB();

requireAuth();

// ── GET /api/watchlist ───────────────────────────────────────────────────────
if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 12)));
    $offset = ($page - 1) * $limit;

    $count = $db->prepare('SELECT COUNT(*) FROM watchlist WHERE user_id = ?');
    $count->execute([currentUserId()]);
    $total = (int)$count->fetchColumn();

    $stmt = $db->prepare(
        'SELECT m.*, w.added_at,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR \',\') AS genre_names
         FROM watchlist w
         JOIN movies m ON m.id = w.movie_id
         LEFT JOIN movie_categories mc ON mc.movie_id = m.id
         LEFT JOIN categories c ON c.id = mc.category_id
         WHERE w.user_id = ?
         GROUP BY m.id, w.added_at
         ORDER BY w.added_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([currentUserId(), $limit, $offset]);
    $movies = $stmt->fetchAll();

    foreach ($movies as &$movie) {
        $movie['cast']   = $movie['cast'] ? explode(',', $movie['cast']) : [];
        $movie['genres'] = $movie['genre_names'] ? explode(',', $movie['genre_names']) : [];
        unset($movie['genre_names']);
    }

    echo json_encode([
        'data'       => $movies,
        'total'      => $total,
        'page'       => $page,
        'totalPages' => ceil($total / $limit),
    ]);
    exit;
}

// ── POST /api/watchlist ── add movie ─────────────────────────────────────────
if ($method === 'POST') {
    $movieId = (int)($body['movie_id'] ?? 0);
    if (!$movieId) {
        http_response_code(400);
        echo json_encode(['error' => 'movie_id is required.']);
        exit;
    }

    $check = $db->prepare('SELECT id FROM movies WHERE id = ?');
    $check->execute([$movieId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found.']);
        exit;
    }

    $exists = $db->prepare('SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?');
    $exists->execute([currentUserId(), $movieId]);
    if ($exists->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Movie already in watchlist.']);
        exit;
    }

    $db->prepare('INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)')->execute([currentUserId(), $movieId]);
    echo json_encode(['message' => 'Added to watchlist.']);
    exit;
}

// ── DELETE /api/watchlist/{movie_id} ─────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    $stmt = $db->prepare('DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?');
    $stmt->execute([currentUserId(), $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not in watchlist.']);
        exit;
    }

    echo json_encode(['message' => 'Removed from watchlist.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
