<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['_action'] ?? 'poster';

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize      = 5 * 1024 * 1024; // 5 MB

$fileKey = match ($action) {
    'backdrop' => 'backdrop',
    'avatar'   => 'avatar',
    default    => 'poster',
};

if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error.']);
    exit;
}

$file = $_FILES[$fileKey];

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Max 5MB allowed.']);
    exit;
}

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, WebP, GIF allowed.']);
    exit;
}

$ext      = match ($mimeType) {
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
};

$subDir = match ($action) {
    'backdrop' => 'backdrops',
    'avatar'   => 'avatars',
    default    => 'posters',
};

$uploadDir = __DIR__ . '/../../public/uploads/' . $subDir . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = uniqid($action . '_', true) . '.' . $ext;
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file.']);
    exit;
}

$publicUrl = '/uploads/' . $subDir . '/' . $filename;

// If uploading avatar, update user record
if ($action === 'avatar') {
    getDB()->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$publicUrl, currentUserId()]);
    $_SESSION['avatar'] = $publicUrl;
}

echo json_encode([
    'message' => 'File uploaded successfully.',
    'url'     => $publicUrl,
]);
