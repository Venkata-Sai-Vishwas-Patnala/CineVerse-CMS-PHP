<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cineverse;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get categories
    $stmt = $pdo->query("SELECT * FROM categories LIMIT 5");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get movies
    $stmt = $pdo->query("SELECT * FROM movies LIMIT 3");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Backend is working!',
        'database' => 'connected',
        'categories_count' => count($categories),
        'movies_count' => count($movies),
        'sample_category' => $categories[0] ?? null,
        'sample_movie' => $movies[0] ?? null,
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'error' => $e->getMessage(),
        'hint' => 'Run setup.php to create database'
    ], JSON_PRETTY_PRINT);
}
