<?php
// Check setup status
$dbConnected = false;
$tablesExist = false;
$frontendBuilt = false;
$apiWorking = false;

// Check database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cineverse;charset=utf8mb4", "root", "");
    $dbConnected = true;
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $tablesExist = count($tables) >= 8;
    
    if ($tablesExist) {
        $movieCount = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
        $apiWorking = $movieCount > 0;
    }
} catch (Exception $e) {
    $dbConnected = false;
}

// Check if frontend is built
$frontendBuilt = file_exists(__DIR__ . '/dist/index.html');

$allReady = $dbConnected && $tablesExist && $apiWorking;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVerse - Setup Status</title>
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
            max-width: 900px;
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
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .status-card {
            background: #0a0a0a;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #666;
        }
        .status-card.ready {
            border-left-color: #22c55e;
        }
        .status-card.pending {
            border-left-color: #eab308;
        }
        .status-card.error {
            border-left-color: #ef4444;
        }
        .status-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .status-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .status-desc {
            color: #999;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .step-box {
            background: #0a0a0a;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 10px;
        }
        .step-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .step-content {
            margin-left: 40px;
            color: #ccc;
        }
        code {
            background: #000;
            padding: 4px 8px;
            border-radius: 4px;
            color: #ef4444;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #ef4444;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin: 10px 10px 0 0;
        }
        .btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #333;
        }
        .btn-secondary:hover {
            background: #444;
        }
        .success-banner {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            margin: 30px 0;
        }
        .success-banner h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .warning-box {
            background: #1a1a00;
            border: 1px solid #eab308;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #eab308;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 CineVerse</h1>
        <p class="subtitle">Setup Status & Configuration</p>

        <?php if ($allReady && $frontendBuilt): ?>
            <div class="success-banner">
                <div style="font-size: 3rem; margin-bottom: 10px;">✅</div>
                <h2>Everything is Ready!</h2>
                <p style="margin-top: 10px;">Your CineVerse installation is complete and ready to use.</p>
            </div>
            <div class="actions">
                <a href="/CineVerse/dist/" class="btn">Launch Application</a>
                <a href="/CineVerse/api/movies/featured" class="btn btn-secondary" target="_blank">Test API</a>
            </div>
        <?php else: ?>
            <h2 style="color: #eab308; text-align: center; margin-bottom: 30px;">⚠️ Setup Required</h2>

            <div class="status-grid">
                <div class="status-card <?= $dbConnected ? 'ready' : 'error' ?>">
                    <div class="status-icon"><?= $dbConnected ? '✅' : '❌' ?></div>
                    <div class="status-title">Database Connection</div>
                    <div class="status-desc">
                        <?= $dbConnected ? 'MySQL connected successfully' : 'Cannot connect to MySQL' ?>
                    </div>
                </div>

                <div class="status-card <?= $tablesExist ? 'ready' : 'pending' ?>">
                    <div class="status-icon"><?= $tablesExist ? '✅' : '⏳' ?></div>
                    <div class="status-title">Database Tables</div>
                    <div class="status-desc">
                        <?= $tablesExist ? '8 tables created' : 'Tables not created yet' ?>
                    </div>
                </div>

                <div class="status-card <?= $apiWorking ? 'ready' : 'pending' ?>">
                    <div class="status-icon"><?= $apiWorking ? '✅' : '⏳' ?></div>
                    <div class="status-title">API & Data</div>
                    <div class="status-desc">
                        <?= $apiWorking ? 'API working with seed data' : 'No data loaded yet' ?>
                    </div>
                </div>

                <div class="status-card <?= $frontendBuilt ? 'ready' : 'pending' ?>">
                    <div class="status-icon"><?= $frontendBuilt ? '✅' : '⏳' ?></div>
                    <div class="status-title">Frontend Build</div>
                    <div class="status-desc">
                        <?= $frontendBuilt ? 'React app built' : 'Frontend not built yet' ?>
                    </div>
                </div>
            </div>

            <?php if (!$dbConnected): ?>
                <div class="warning-box">
                    <strong>⚠️ MySQL Not Running</strong><br>
                    Please start MySQL in XAMPP Control Panel before continuing.
                </div>
            <?php endif; ?>

            <h2 style="color: #fff; margin: 40px 0 20px;">📋 Setup Steps</h2>

            <?php if (!$tablesExist || !$apiWorking): ?>
                <div class="step-box">
                    <div class="step-title">
                        <span class="step-number">1</span>
                        Setup Database
                    </div>
                    <div class="step-content">
                        <p style="margin-bottom: 10px;">Run the automatic database installer:</p>
                        <a href="setup.php" class="btn">Run Database Setup</a>
                        <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                            This will create all tables and insert sample data.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$frontendBuilt): ?>
                <div class="step-box">
                    <div class="step-title">
                        <span class="step-number">2</span>
                        Build Frontend
                    </div>
                    <div class="step-content">
                        <p style="margin-bottom: 10px;">Open terminal in project folder and run:</p>
                        <code style="display: block; padding: 10px; margin: 10px 0;">npm install</code>
                        <code style="display: block; padding: 10px; margin: 10px 0;">npm run build</code>
                        <p style="margin-top: 15px; font-size: 0.9rem; color: #666;">
                            This compiles the React app into browser-ready files.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="step-box" style="border-left-color: #22c55e;">
                <div class="step-title">
                    <span class="step-number" style="background: #22c55e;">3</span>
                    Access Application
                </div>
                <div class="step-content">
                    <p style="margin-bottom: 10px;">After completing steps above, refresh this page!</p>
                    <p style="margin-top: 10px; color: #999;">
                        Default admin: <code>admin@cineverse.com</code> / <code>password</code>
                    </p>
                </div>
            </div>

            <div style="background: #0a0a0a; border-radius: 8px; padding: 20px; margin: 30px 0;">
                <h3 style="color: #ef4444; margin-bottom: 15px;">💡 Alternative: Use Dev Server</h3>
                <p style="color: #ccc; margin-bottom: 10px;">
                    For development with hot reload (no build needed):
                </p>
                <code style="display: block; padding: 10px; margin: 10px 0;">npm run dev</code>
                <p style="color: #999; font-size: 0.9rem; margin-top: 10px;">
                    Then open: <a href="http://localhost:5173" style="color: #ef4444;">http://localhost:5173</a>
                </p>
            </div>

            <div class="actions">
                <a href="status.php" class="btn btn-secondary">Refresh Status</a>
                <a href="/CineVerse/api/categories" class="btn btn-secondary" target="_blank">Test API</a>
            </div>
        <?php endif; ?>

        <p style="text-align: center; color: #666; margin-top: 40px; font-size: 0.9rem;">
            CineVerse Setup v1.0 | Need help? Check README.md
        </p>
    </div>
</body>
</html>
