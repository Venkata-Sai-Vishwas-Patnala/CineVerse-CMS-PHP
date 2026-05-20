<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_REQUEST['_id'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = getDB();

function recalcRating(PDO $db, int $movieId): void {
    $stmt = $db->prepare('SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE movie_id = ?');
    $stmt->execute([$movieId]);
    $row = $stmt->fetch();
    $db->prepare('UPDATE movies SET rating = ?, rating_count = ? WHERE id = ?')
       ->execute([round((float)$row['avg_r'], 1), (int)$row['cnt'], $movieId]);
}

// ── GET /api/reviews?movie_id=X ─────────────────────────────────────────────
if ($method === 'GET' && !$id) {
    $movieId = (int)($_GET['movie_id'] ?? 0);
    if (!$movieId) {
        http_response_code(400);
        echo json_encode(['error' => 'movie_id is required.']);
        exit;
    }

    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = min(50, max(1, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $count = $db->prepare('SELECT COUNT(*) FROM reviews WHERE movie_id = ?');
    $count->execute([$movieId]);
    $total = (int)$count->fetchColumn();

    $stmt = $db->prepare(
        'SELECT r.*, u.username, u.avatar
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.movie_id = ?
         ORDER BY r.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->execute([$movieId, $limit, $offset]);
    $reviews = $stmt->fetchAll();

    echo json_encode([
        'data'       => $reviews,
        'total'      => $total,
        'page'       => $page,
        'totalPages' => ceil($total / $limit),
    ]);
    exit;
}

// ── GET /api/reviews/{id} ────────────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $stmt = $db->prepare('SELECT r.*, u.username, u.avatar FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.id = ?');
    $stmt->execute([$id]);
    $review = $stmt->fetch();
    if (!$review) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found.']);
        exit;
    }
    echo json_encode(['data' => $review]);
    exit;
}

// ── POST /api/reviews ── create or update (upsert) ───────────────────────────
if ($method === 'POST') {
    requireAuth();

    $movieId = (int)($body['movie_id'] ?? 0);
    $rating  = (int)($body['rating'] ?? 0);
    $text    = trim($body['review_text'] ?? '');

    if (!$movieId || $rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'movie_id and rating (1-5) are required.']);
        exit;
    }

    $check = $db->prepare('SELECT id FROM movies WHERE id = ?');
    $check->execute([$movieId]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found.']);
        exit;
    }

    $db->prepare(
        'INSERT INTO reviews (movie_id, user_id, rating, review_text)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text), updated_at = NOW()'
    )->execute([currentUserId(), $movieId, $rating, $text ?: null]);

    recalcRating($db, $movieId);

    $stmt = $db->prepare('SELECT r.*, u.username, u.avatar FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.movie_id = ? AND r.user_id = ?');
    $stmt->execute([$movieId, currentUserId()]);

    echo json_encode(['message' => 'Review saved.', 'data' => $stmt->fetch()]);
    exit;
}

// ── DELETE /api/reviews/{id} ─────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    requireAuth();

    $stmt = $db->prepare('SELECT movie_id, user_id FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
    $review = $stmt->fetch();

    if (!$review) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found.']);
        exit;
    }
    if ($review['user_id'] !== currentUserId() && !isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden.']);
        exit;
    }

    $db->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
    recalcRating($db, (int)$review['movie_id']);

    echo json_encode(['message' => 'Review deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
