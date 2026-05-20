<?php
/**
 * Automatic Database Installer for CineVerse
 * Run this file once to set up the database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cineverse');

$success = [];
$errors = [];

try {
    // Step 1: Connect to MySQL (without database)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $success[] = "✅ Connected to MySQL server";

    // Step 2: Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $success[] = "✅ Database 'cineverse' created/verified";

    // Step 3: Use the database
    $pdo->exec("USE " . DB_NAME);
    $success[] = "✅ Using database 'cineverse'";

    // Step 4: Read and execute SQL file
    $sqlFile = __DIR__ . '/backend/setup.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found at: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    $success[] = "✅ SQL file loaded (" . strlen($sql) . " bytes)";

    // Remove comments and split properly
    $sql = preg_replace('/^--.*$/m', '', $sql); // Remove -- comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove /* */ comments
    
    // Split by semicolon but keep multi-line statements together
    $statements = [];
    $buffer = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $buffer .= $line . " ";
        
        if (substr($line, -1) === ';') {
            $stmt = trim($buffer);
            if (!empty($stmt)) {
                $statements[] = $stmt;
            }
            $buffer = '';
        }
    }

    $executed = 0;
    $failed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignore duplicate entry errors (for IGNORE statements)
                if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    $errors[] = "⚠️ SQL Error: " . substr($statement, 0, 50) . "... - " . $e->getMessage();
                    $failed++;
                }
            }
        }
    }
    $success[] = "✅ Executed $executed SQL statements" . ($failed > 0 ? " ($failed failed)" : "");

    // Step 5: Verify tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $expectedTables = ['users', 'movies', 'categories', 'platforms', 'reviews', 'watchlist', 'movie_categories', 'movie_platforms'];
    
    $missingTables = array_diff($expectedTables, $tables);
    if (empty($missingTables)) {
        $success[] = "✅ All 8 tables created successfully";
    } else {
        $errors[] = "❌ Missing tables: " . implode(', ', $missingTables);
    }

    // Step 6: Verify seed data
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $movieCount = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $platformCount = $pdo->query("SELECT COUNT(*) FROM platforms")->fetchColumn();

    $success[] = "✅ Seed data loaded:";
    $success[] = "   - $userCount user(s)";
    $success[] = "   - $movieCount movie(s)";
    $success[] = "   - $categoryCount categories";
    $success[] = "   - $platformCount platforms";

    // Step 7: Test admin login
    $admin = $pdo->query("SELECT email, role FROM users WHERE role = 'admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        $success[] = "✅ Admin account ready: " . $admin['email'];
    } else {
        $errors[] = "❌ Admin account not found";
    }

} catch (PDOException $e) {
    $errors[] = "❌ Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $errors[] = "❌ Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVerse - Database Installer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #000 0%, #1a0000 100%);
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(239, 68, 68, 0.3);
        }
        h1 {
            color: #ef4444;
            margin-bottom: 10px;
            font-size: 2.5rem;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #999;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }
        .result-box {
            background: #0a0a0a;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-box {
            border-left: 4px solid #22c55e;
        }
        .error-box {
            border-left: 4px solid #ef4444;
        }
        .result-item {
            padding: 8px 0;
            line-height: 1.6;
            color: #ccc;
        }
        .success-item { color: #22c55e; }
        .error-item { color: #ef4444; }
        .big-status {
            text-align: center;
            font-size: 4rem;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #ef4444;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            text-align: center;
            margin: 10px;
        }
        .btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.4);
        }
        .btn-secondary {
            background: #333;
        }
        .btn-secondary:hover {
            background: #444;
        }
        .actions {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #333;
        }
        .info-box {
            background: #1a1a2e;
            border: 1px solid #2a2a4e;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box h3 {
            color: #ef4444;
            margin-bottom: 15px;
        }
        .info-box p {
            color: #999;
            line-height: 1.6;
            margin: 8px 0;
        }
        code {
            background: #000;
            padding: 2px 8px;
            border-radius: 4px;
            color: #ef4444;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 CineVerse</h1>
        <p class="subtitle">Database Installation</p>

        <?php if (empty($errors)): ?>
            <div class="big-status">✅</div>
            <h2 style="text-align: center; color: #22c55e; margin-bottom: 30px;">Installation Successful!</h2>
        <?php else: ?>
            <div class="big-status">❌</div>
            <h2 style="text-align: center; color: #ef4444; margin-bottom: 30px;">Installation Failed</h2>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="result-box success-box">
                <h3 style="color: #22c55e; margin-bottom: 15px;">✅ Success Steps:</h3>
                <?php foreach ($success as $msg): ?>
                    <div class="result-item success-item"><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="result-box error-box">
                <h3 style="color: #ef4444; margin-bottom: 15px;">❌ Errors:</h3>
                <?php foreach ($errors as $msg): ?>
                    <div class="result-item error-item"><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($errors)): ?>
            <div class="info-box">
                <h3>🔐 Default Admin Login</h3>
                <p>Email: <code>admin@cineverse.com</code></p>
                <p>Password: <code>password</code></p>
            </div>

            <div class="info-box">
                <h3>📊 What Was Installed</h3>
                <p>✅ 8 database tables</p>
                <p>✅ 1 admin user account</p>
                <p>✅ 10 movie categories</p>
                <p>✅ 5 streaming platforms</p>
                <p>✅ 6 sample movies with data</p>
            </div>

            <div class="info-box">
                <h3>🚀 Next Steps</h3>
                <p>1. Build the frontend: <code>npm install && npm run build</code></p>
                <p>2. Or use dev server: <code>npm run dev</code></p>
                <p>3. Access the application</p>
            </div>

            <div class="actions">
                <a href="/CineVerse/" class="btn">Go to Application</a>
                <a href="/CineVerse/api/movies/featured" class="btn btn-secondary" target="_blank">Test API</a>
                <a href="install.php" class="btn btn-secondary">Run Again</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>🔧 Troubleshooting</h3>
                <p>1. Make sure MySQL is running in XAMPP</p>
                <p>2. Check database credentials in <code>backend/config/db.php</code></p>
                <p>3. Verify <code>backend/setup.sql</code> file exists</p>
                <p>4. Try running this installer again</p>
            </div>

            <div class="actions">
                <a href="install.php" class="btn">Try Again</a>
                <a href="/CineVerse/" class="btn btn-secondary">Back to Home</a>
            </div>
        <?php endif; ?>

        <p style="text-align: center; color: #666; margin-top: 40px; font-size: 0.9rem;">
            CineVerse Database Installer v1.0
        </p>
    </div>
</body>
</html>
