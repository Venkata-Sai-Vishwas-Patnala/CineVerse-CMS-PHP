<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_REQUEST['_id'] ?? null;
$action = $_REQUEST['_action'] ?? null;
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$db     = getDB();

// ── Helpers ──────────────────────────────────────────────────────────────────

function movieWithRelations(PDO $db, int $id): ?array {
    $stmt = $db->prepare('SELECT * FROM movies WHERE id = ?');
    $stmt->execute([$id]);
    $movie = $stmt->fetch();
    if (!$movie) return null;

    $cats = $db->prepare(
        'SELECT c.id, c.name, c.slug, c.color, c.icon
         FROM categories c
         JOIN movie_categories mc ON mc.category_id = c.id
         WHERE mc.movie_id = ?'
    );
    $cats->execute([$id]);
    $movie['categories'] = $cats->fetchAll();

    $plats = $db->prepare(
        'SELECT p.id, p.name, p.logo, mp.available, mp.watch_url
         FROM platforms p
         JOIN movie_platforms mp ON mp.platform_id = p.id
         WHERE mp.movie_id = ?'
    );
    $plats->execute([$id]);
    $movie['platforms'] = $plats->fetchAll();

    $movie['cast'] = $movie['cast'] ? explode(',', $movie['cast']) : [];
    return $movie;
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// ── GET /api/movies ── list with search, filter, pagination ──────────────────
if ($method === 'GET' && !$id && !$action) {
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $limit    = min(50, max(1, (int)($_GET['limit'] ?? 12)));
    $offset   = ($page - 1) * $limit;
    $search   = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $year     = (int)($_GET['year'] ?? 0);
    $sort     = in_array($_GET['sort'] ?? '', ['rating', 'release_year', 'title', 'created_at']) ? $_GET['sort'] : 'created_at';
    $order    = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
    $featured = isset($_GET['featured']) ? (int)$_GET['featured'] : null;
    $trending = isset($_GET['trending']) ? (int)$_GET['trending'] : null;

    $where  = ['m.status = "published"'];
    $params = [];

    if ($search) {
        $where[]  = '(m.title LIKE ? OR m.description LIKE ? OR m.director LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($category) {
        $where[]  = 'EXISTS (SELECT 1 FROM movie_categories mc JOIN categories c ON c.id = mc.category_id WHERE mc.movie_id = m.id AND c.slug = ?)';
        $params[] = $category;
    }
    if ($year) {
        $where[]  = 'm.release_year = ?';
        $params[] = $year;
    }
    if ($featured !== null) {
        $where[]  = 'm.is_featured = ?';
        $params[] = $featured;
    }
    if ($trending !== null) {
        $where[]  = 'm.is_trending = ?';
        $params[] = $trending;
    }

    $whereSQL = implode(' AND ', $where);

    $countStmt = $db->prepare("SELECT COUNT(*) FROM movies m WHERE $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $db->prepare(
        "SELECT m.*, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ',') AS genre_names
         FROM movies m
         LEFT JOIN movie_categories mc ON mc.movie_id = m.id
         LEFT JOIN categories c ON c.id = mc.category_id
         WHERE $whereSQL
         GROUP BY m.id
         ORDER BY m.$sort $order
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute($params);
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
        'limit'      => $limit,
        'totalPages' => ceil($total / $limit),
    ]);
    exit;
}

// ── GET /api/movies/featured ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'featured') {
    $stmt = $db->query('SELECT * FROM movies WHERE is_featured = 1 AND status = "published" ORDER BY created_at DESC LIMIT 1');
    $movie = $stmt->fetch();
    if ($movie) {
        $movie = movieWithRelations($db, $movie['id']);
    }
    echo json_encode(['data' => $movie]);
    exit;
}

// ── GET /api/movies/trending ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'trending') {
    $stmt = $db->query(
        'SELECT m.*, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR \',\') AS genre_names
         FROM movies m
         LEFT JOIN movie_categories mc ON mc.movie_id = m.id
         LEFT JOIN categories c ON c.id = mc.category_id
         WHERE m.is_trending = 1 AND m.status = "published"
         GROUP BY m.id
         ORDER BY m.rating DESC
         LIMIT 12'
    );
    $movies = $stmt->fetchAll();
    foreach ($movies as &$movie) {
        $movie['cast']   = $movie['cast'] ? explode(',', $movie['cast']) : [];
        $movie['genres'] = $movie['genre_names'] ? explode(',', $movie['genre_names']) : [];
        unset($movie['genre_names']);
    }
    echo json_encode(['data' => $movies]);
    exit;
}

// ── GET /api/movies/{id} ─────────────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $movie = movieWithRelations($db, $id);
    if (!$movie) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found.']);
        exit;
    }
    echo json_encode(['data' => $movie]);
    exit;
}

// ── GET /api/movies/slug/{slug} ──────────────────────────────────────────────
if ($method === 'GET' && $action === 'slug') {
    $slug = $parts[2] ?? ($_GET['slug'] ?? '');
    $stmt = $db->prepare('SELECT id FROM movies WHERE slug = ?');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found.']);
        exit;
    }
    echo json_encode(['data' => movieWithRelations($db, $row['id'])]);
    exit;
}

// ── POST /api/movies ── create ───────────────────────────────────────────────
if ($method === 'POST' && !$id) {
    requireAdmin();

    $title       = trim($body['title'] ?? '');
    $description = trim($body['description'] ?? '');
    $director    = trim($body['director'] ?? '');
    $cast        = is_array($body['cast'] ?? null) ? implode(',', $body['cast']) : trim($body['cast'] ?? '');
    $year        = (int)($body['release_year'] ?? 0);
    $duration    = trim($body['duration'] ?? '');
    $poster      = trim($body['poster'] ?? '');
    $backdrop    = trim($body['backdrop'] ?? '');
    $trailer     = trim($body['trailer_url'] ?? '');
    $featured    = (int)($body['is_featured'] ?? 0);
    $trending    = (int)($body['is_trending'] ?? 0);
    $status      = in_array($body['status'] ?? '', ['published', 'draft']) ? $body['status'] : 'published';
    $categories  = $body['categories'] ?? [];
    $platforms   = $body['platforms'] ?? [];

    if (!$title || !$description) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and description are required.']);
        exit;
    }

    $slug = slugify($title);
    $base = $slug;
    $i    = 1;
    while (true) {
        $check = $db->prepare('SELECT id FROM movies WHERE slug = ?');
        $check->execute([$slug]);
        if (!$check->fetch()) break;
        $slug = "$base-$i";
        $i++;
    }

    $stmt = $db->prepare(
        'INSERT INTO movies (title, slug, description, director, cast, release_year, duration, poster, backdrop, trailer_url, is_featured, is_trending, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$title, $slug, $description, $director, $cast, $year ?: null, $duration, $poster ?: null, $backdrop ?: null, $trailer ?: null, $featured, $trending, $status]);
    $movieId = (int)$db->lastInsertId();

    foreach ($categories as $catId) {
        $db->prepare('INSERT IGNORE INTO movie_categories (movie_id, category_id) VALUES (?, ?)')->execute([$movieId, (int)$catId]);
    }
    foreach ($platforms as $plat) {
        $db->prepare('INSERT IGNORE INTO movie_platforms (movie_id, platform_id, available, watch_url) VALUES (?, ?, ?, ?)')
           ->execute([$movieId, (int)$plat['id'], (int)($plat['available'] ?? 1), $plat['watch_url'] ?? null]);
    }

    http_response_code(201);
    echo json_encode(['message' => 'Movie created.', 'data' => movieWithRelations($db, $movieId)]);
    exit;
}

// ── PUT /api/movies/{id} ── update ───────────────────────────────────────────
if ($method === 'PUT' && $id) {
    requireAdmin();

    $fields = [];
    $params = [];

    $map = ['title', 'description', 'director', 'release_year', 'duration', 'poster', 'backdrop', 'trailer_url', 'status'];
    foreach ($map as $f) {
        if (array_key_exists($f, $body)) {
            $fields[] = "$f = ?";
            $params[] = $body[$f] === '' ? null : $body[$f];
        }
    }
    if (array_key_exists('cast', $body)) {
        $fields[] = 'cast = ?';
        $params[] = is_array($body['cast']) ? implode(',', $body['cast']) : $body['cast'];
    }
    if (array_key_exists('is_featured', $body)) {
        $fields[] = 'is_featured = ?';
        $params[] = (int)$body['is_featured'];
    }
    if (array_key_exists('is_trending', $body)) {
        $fields[] = 'is_trending = ?';
        $params[] = (int)$body['is_trending'];
    }
    if (array_key_exists('title', $body)) {
        $fields[] = 'slug = ?';
        $params[] = slugify($body['title']);
    }

    if ($fields) {
        $params[] = $id;
        $db->prepare('UPDATE movies SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);
    }

    if (isset($body['categories'])) {
        $db->prepare('DELETE FROM movie_categories WHERE movie_id = ?')->execute([$id]);
        foreach ($body['categories'] as $catId) {
            $db->prepare('INSERT IGNORE INTO movie_categories (movie_id, category_id) VALUES (?, ?)')->execute([$id, (int)$catId]);
        }
    }
    if (isset($body['platforms'])) {
        $db->prepare('DELETE FROM movie_platforms WHERE movie_id = ?')->execute([$id]);
        foreach ($body['platforms'] as $plat) {
            $db->prepare('INSERT IGNORE INTO movie_platforms (movie_id, platform_id, available, watch_url) VALUES (?, ?, ?, ?)')
               ->execute([$id, (int)$plat['id'], (int)($plat['available'] ?? 1), $plat['watch_url'] ?? null]);
        }
    }

    echo json_encode(['message' => 'Movie updated.', 'data' => movieWithRelations($db, $id)]);
    exit;
}

// ── DELETE /api/movies/{id} ──────────────────────────────────────────────────
if ($method === 'DELETE' && $id) {
    requireAdmin();

    $stmt = $db->prepare('SELECT poster, backdrop FROM movies WHERE id = ?');
    $stmt->execute([$id]);
    $movie = $stmt->fetch();

    if (!$movie) {
        http_response_code(404);
        echo json_encode(['error' => 'Movie not found.']);
        exit;
    }

    // Delete uploaded files if local
    foreach (['poster', 'backdrop'] as $field) {
        if ($movie[$field] && str_starts_with($movie[$field], '/uploads/')) {
            $path = __DIR__ . '/../../public' . $movie[$field];
            if (file_exists($path)) unlink($path);
        }
    }

    $db->prepare('DELETE FROM movies WHERE id = ?')->execute([$id]);
    echo json_encode(['message' => 'Movie deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
