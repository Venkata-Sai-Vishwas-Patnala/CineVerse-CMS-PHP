<?php
/**
 * Simple Direct Database Installer
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cineverse';

$success = [];
$errors = [];

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $success[] = "✅ Connected to MySQL";

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $db");
    $success[] = "✅ Database '$db' ready";

    // Create tables directly
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        avatar VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $success[] = "✅ Table 'users' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) UNIQUE NOT NULL,
        slug VARCHAR(50) UNIQUE NOT NULL,
        color VARCHAR(50) DEFAULT 'from-gray-500 to-gray-700',
        icon VARCHAR(50) DEFAULT 'Clapperboard',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $success[] = "✅ Table 'categories' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        description TEXT,
        director VARCHAR(100),
        cast TEXT,
        release_year YEAR,
        duration VARCHAR(20),
        rating DECIMAL(3,1) DEFAULT 0.0,
        rating_count INT DEFAULT 0,
        poster VARCHAR(255) DEFAULT NULL,
        backdrop VARCHAR(255) DEFAULT NULL,
        trailer_url VARCHAR(500) DEFAULT NULL,
        is_featured TINYINT(1) DEFAULT 0,
        is_trending TINYINT(1) DEFAULT 0,
        status ENUM('published', 'draft') DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $success[] = "✅ Table 'movies' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS platforms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        logo VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $success[] = "✅ Table 'platforms' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS movie_categories (
        movie_id INT NOT NULL,
        category_id INT NOT NULL,
        PRIMARY KEY (movie_id, category_id),
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )");
    $success[] = "✅ Table 'movie_categories' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS movie_platforms (
        movie_id INT NOT NULL,
        platform_id INT NOT NULL,
        available TINYINT(1) DEFAULT 1,
        watch_url VARCHAR(500) DEFAULT NULL,
        PRIMARY KEY (movie_id, platform_id),
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE
    )");
    $success[] = "✅ Table 'movie_platforms' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_id INT NOT NULL,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_review (movie_id, user_id),
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    $success[] = "✅ Table 'reviews' created";

    $pdo->exec("CREATE TABLE IF NOT EXISTS watchlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        movie_id INT NOT NULL,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_watchlist (user_id, movie_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
    )");
    $success[] = "✅ Table 'watchlist' created";

    // Insert admin user
    $pdo->exec("INSERT IGNORE INTO users (username, email, password, role) VALUES 
        ('admin', 'admin@cineverse.com', '\$2y\$12\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')");
    $success[] = "✅ Admin user created";

    // Insert categories
    $pdo->exec("INSERT IGNORE INTO categories (name, slug, color, icon) VALUES
        ('Action', 'action', 'from-orange-500 to-red-600', 'Zap'),
        ('Drama', 'drama', 'from-purple-500 to-pink-600', 'Drama'),
        ('Comedy', 'comedy', 'from-yellow-500 to-orange-600', 'Laugh'),
        ('Romance', 'romance', 'from-pink-500 to-red-600', 'Heart'),
        ('Sci-Fi', 'sci-fi', 'from-blue-500 to-cyan-600', 'Sparkles'),
        ('Thriller', 'thriller', 'from-gray-500 to-gray-700', 'Clapperboard'),
        ('Biography', 'biography', 'from-green-500 to-teal-600', 'BookOpen'),
        ('Crime', 'crime', 'from-red-700 to-red-900', 'Shield'),
        ('Adventure', 'adventure', 'from-emerald-500 to-green-600', 'Compass'),
        ('History', 'history', 'from-amber-500 to-yellow-600', 'Clock')");
    $success[] = "✅ Categories inserted";

    // Insert platforms
    $pdo->exec("INSERT IGNORE INTO platforms (name, logo) VALUES
        ('Netflix', '🎬'),
        ('Amazon Prime', '📺'),
        ('Apple TV+', '🍎'),
        ('Disney+', '🏰'),
        ('HBO Max', '🎭')");
    $success[] = "✅ Platforms inserted";

    // Insert sample movies
    $pdo->exec("INSERT IGNORE INTO movies (title, slug, description, director, cast, release_year, duration, rating, rating_count, is_featured, is_trending, status) VALUES
        ('Oppenheimer', 'oppenheimer', 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.', 'Christopher Nolan', 'Cillian Murphy,Emily Blunt,Matt Damon,Robert Downey Jr.', 2023, '180 min', 4.8, 1200, 1, 1, 'published'),
        ('The Dark Knight', 'dark-knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests.', 'Christopher Nolan', 'Christian Bale,Heath Ledger,Aaron Eckhart,Michael Caine', 2008, '152 min', 4.9, 2500, 0, 1, 'published'),
        ('Inception', 'inception', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 'Christopher Nolan', 'Leonardo DiCaprio,Joseph Gordon-Levitt,Elliot Page,Tom Hardy', 2010, '148 min', 4.7, 1800, 0, 1, 'published'),
        ('Interstellar', 'interstellar', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity survival.', 'Christopher Nolan', 'Matthew McConaughey,Anne Hathaway,Jessica Chastain,Michael Caine', 2014, '169 min', 4.6, 1600, 0, 1, 'published'),
        ('Dune', 'dune', 'A noble family becomes embroiled in a war for control over the galaxy most valuable asset.', 'Denis Villeneuve', 'Timothée Chalamet,Rebecca Ferguson,Zendaya,Oscar Isaac', 2021, '155 min', 4.5, 1100, 0, 1, 'published'),
        ('Pulp Fiction', 'pulp-fiction', 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', 'Quentin Tarantino', 'John Travolta,Uma Thurman,Samuel L. Jackson,Bruce Willis', 1994, '154 min', 4.8, 2200, 0, 1, 'published')");
    $success[] = "✅ Sample movies inserted";

    // Link movies to categories
    $pdo->exec("INSERT IGNORE INTO movie_categories (movie_id, category_id)
        SELECT m.id, c.id FROM movies m, categories c WHERE
        (m.slug='oppenheimer' AND c.slug IN ('biography','drama','history')) OR
        (m.slug='dark-knight' AND c.slug IN ('action','crime','drama')) OR
        (m.slug='inception' AND c.slug IN ('action','sci-fi','thriller')) OR
        (m.slug='interstellar' AND c.slug IN ('adventure','drama','sci-fi')) OR
        (m.slug='dune' AND c.slug IN ('action','adventure','sci-fi')) OR
        (m.slug='pulp-fiction' AND c.slug IN ('crime','drama'))");
    $success[] = "✅ Movie-category relationships created";

    // Link movies to platforms
    $pdo->exec("INSERT IGNORE INTO movie_platforms (movie_id, platform_id, available)
        SELECT m.id, p.id,
        CASE
            WHEN m.slug='oppenheimer' AND p.name IN ('Netflix','Amazon Prime','HBO Max') THEN 1
            WHEN m.slug='dark-knight' AND p.name IN ('Amazon Prime','Apple TV+','HBO Max') THEN 1
            WHEN m.slug='inception' AND p.name IN ('Netflix','Amazon Prime') THEN 1
            WHEN m.slug='interstellar' AND p.name IN ('Amazon Prime','Apple TV+','HBO Max') THEN 1
            WHEN m.slug='dune' AND p.name IN ('HBO Max') THEN 1
            WHEN m.slug='pulp-fiction' AND p.name IN ('Netflix','Amazon Prime') THEN 1
            ELSE 0
        END
        FROM movies m, platforms p
        WHERE m.slug IN ('oppenheimer','dark-knight','inception','interstellar','dune','pulp-fiction')");
    $success[] = "✅ Movie-platform relationships created";

    // Verify
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $success[] = "✅ Total tables: " . count($tables);

} catch (Exception $e) {
    $errors[] = "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Database Setup</title>
<style>body{font-family:sans-serif;background:#000;color:#fff;padding:40px;max-width:800px;margin:0 auto}
.success{color:#22c55e;padding:8px 0}.error{color:#ef4444;padding:8px 0}h1{color:#ef4444}
.btn{display:inline-block;padding:15px 30px;background:#ef4444;color:#fff;text-decoration:none;border-radius:8px;margin:20px 10px 0 0}
.btn:hover{background:#dc2626}</style></head><body>
<h1>🎬 CineVerse Database Setup</h1>
<?php if(empty($errors)): ?>
<h2 style="color:#22c55e">✅ Installation Complete!</h2>
<?php foreach($success as $msg): ?><div class="success"><?=$msg?></div><?php endforeach; ?>
<p style="margin-top:30px">Admin: <code>admin@cineverse.com</code> / <code>password</code></p>
<a href="/CineVerse/" class="btn">Go to Application</a>
<a href="/CineVerse/api/categories" class="btn">Test API</a>
<?php else: ?>
<h2 style="color:#ef4444">❌ Installation Failed</h2>
<?php foreach($errors as $msg): ?><div class="error"><?=$msg?></div><?php endforeach; ?>
<p>Make sure MySQL is running in XAMPP!</p>
<a href="setup.php" class="btn">Try Again</a>
<?php endif; ?>
</body></html>
