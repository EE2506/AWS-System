<?php
/**
 * ============================================================
 * AWS-System Deployment Script for Hostinger Shared Hosting
 * ============================================================
 *
 * This script runs essential Laravel setup commands through
 * the browser — useful when SSH is unavailable or tricky.
 *
 * HOW TO USE:
 *   1. Upload this file to public_html/public/deploy.php
 *   2. Visit: https://yourdomain.com/deploy.php?token=YOUR_SECRET_TOKEN
 *   3. Click the buttons to run each step IN ORDER
 *   4. DELETE THIS FILE when done (security risk!)
 *
 * IMPORTANT: Change the secret token below before uploading!
 */

// ===================== CONFIGURATION =====================
$SECRET_TOKEN = 'aws-deploy-2026-change-me';  // CHANGE THIS!
// =========================================================

// Security check
if (!isset($_GET['token']) || $_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    die('Access denied. Invalid or missing token.');
}

// Set up Laravel environment
define('LARAVEL_START', microtime(true));

// Go up one directory from public/ to the project root
$basePath = realpath(__DIR__ . '/..');

// Require autoloader
require $basePath . '/vendor/autoload.php';

// Bootstrap the application
$app = require_once $basePath . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Available commands
$commands = [
    'key:generate' => [
        'label' => 'Generate App Key',
        'description' => 'Generates a unique encryption key for your app',
        'args' => ['--force' => true],
        'order' => 1,
    ],
    'migrate' => [
        'label' => 'Run Migrations',
        'description' => 'Creates all database tables',
        'args' => ['--force' => true],
        'order' => 2,
    ],
    'db:seed' => [
        'label' => 'Seed Database',
        'description' => 'Creates admin user (Admin@123) and roles',
        'args' => ['--force' => true],
        'order' => 3,
    ],
    'storage:link' => [
        'label' => 'Create Storage Link',
        'description' => 'Links storage/app/public to public/storage',
        'args' => [],
        'order' => 4,
    ],
    'config:cache' => [
        'label' => 'Cache Config',
        'description' => 'Caches configuration for better performance',
        'args' => [],
        'order' => 5,
    ],
    'route:cache' => [
        'label' => 'Cache Routes',
        'description' => 'Caches routes for better performance',
        'args' => [],
        'order' => 6,
    ],
    'view:cache' => [
        'label' => 'Cache Views',
        'description' => 'Compiles and caches all Blade views',
        'args' => [],
        'order' => 7,
    ],
    'config:clear' => [
        'label' => 'Clear Config Cache',
        'description' => 'Clears cached configuration (run after changing .env)',
        'args' => [],
        'order' => 8,
    ],
    'cache:clear' => [
        'label' => 'Clear App Cache',
        'description' => 'Clears the application cache',
        'args' => [],
        'order' => 9,
    ],
];

$output = '';
$ranCommand = '';

// Custom storage:link handler for shared hosting (avoids symlink which is disabled)
function manualStorageLink($basePath)
{
    error_reporting(0);
    ini_set('display_errors', '0');

    $publicStorage = __DIR__ . '/storage';
    $storageTarget = $basePath . '/storage/app/public';

    // If link/dir already exists, skip
    if (is_link($publicStorage) || is_dir($publicStorage)) {
        error_reporting(E_ALL);
        return "SUCCESS - Storage link already exists at: {$publicStorage}";
    }

    // Ensure the target directory exists
    if (!is_dir($storageTarget)) {
        @mkdir($storageTarget, 0755, true);
    }

    // Check if symlink function is available (not disabled)
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));
    $symlinkAvailable = function_exists('symlink') && !in_array('symlink', $disabled);

    if ($symlinkAvailable) {
        try {
            if (@symlink($storageTarget, $publicStorage)) {
                error_reporting(E_ALL);
                return "SUCCESS - Created symlink: public/storage -> storage/app/public";
            }
        } catch (\Throwable $e) {
            // symlink failed, continue to fallback
        }
    }

    // Fallback: Create a real directory with a PHP file router
    if (!is_dir($publicStorage)) {
        @mkdir($publicStorage, 0755, true);
    }

    $routerCode = '<?php' . "\n"
        . '$base = realpath(__DIR__ . "/../..");' . "\n"
        . '$f = isset($_GET["file"]) ? basename($_GET["file"]) : "";' . "\n"
        . '$p = $base . "/storage/app/public/" . $f;' . "\n"
        . 'if ($f && file_exists($p) && !is_dir($p)) {' . "\n"
        . '  header("Content-Type: " . mime_content_type($p));' . "\n"
        . '  readfile($p); exit;' . "\n"
        . '} else { http_response_code(404); echo "Not found."; }' . "\n";

    @file_put_contents($publicStorage . '/index.php', $routerCode);

    $htaccess = "<IfModule mod_rewrite.c>\n"
        . "  RewriteEngine On\n"
        . "  RewriteCond %{REQUEST_FILENAME} !-f\n"
        . "  RewriteRule ^(.*)$ index.php?file=\$1 [L,QSA]\n"
        . "</IfModule>\n";

    @file_put_contents($publicStorage . '/.htaccess', $htaccess);

    error_reporting(E_ALL);

    return "SUCCESS (Fallback) - Created manual storage directory with file router.\n"
        . "Symlinks are disabled on this server, using PHP file router instead.\n"
        . "Location: public/storage/";
}

// Execute command if requested
if (isset($_GET['run']) && array_key_exists($_GET['run'], $commands)) {
    $ranCommand = $_GET['run'];
    $cmd = $commands[$ranCommand];

    try {
        if ($ranCommand === 'storage:link') {
            $output = manualStorageLink($basePath);
        } else {
            $exitCode = Artisan::call($ranCommand, $cmd['args']);
            $output = Artisan::output();

            if ($exitCode === 0) {
                $output = "SUCCESS\n\n" . $output;
            } else {
                $output = "Command finished with exit code: {$exitCode}\n\n" . $output;
            }
        }
    } catch (Exception $e) {
        $output = "ERROR: " . $e->getMessage() . "\n\nTrace:\n" . $e->getTraceAsString();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS-System Deploy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #60a5fa;
        }

        .subtitle {
            color: #94a3b8;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .warning {
            background: #7c2d12;
            border: 1px solid #ea580c;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            color: #fed7aa;
        }

        .commands {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .cmd-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: border-color 0.2s;
        }

        .cmd-card:hover {
            border-color: #60a5fa;
        }

        .cmd-info h3 {
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .cmd-info p {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .cmd-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: background 0.2s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-block;
        }

        .cmd-btn:hover {
            background: #1d4ed8;
        }

        .output-box {
            margin-top: 2rem;
            background: #0c0c0c;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 1.25rem;
        }

        .output-box h2 {
            font-size: 1rem;
            color: #60a5fa;
            margin-bottom: 0.75rem;
        }

        .output-box pre {
            font-family: 'Fira Code', 'Cascadia Code', monospace;
            font-size: 0.8rem;
            color: #a7f3d0;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .step-num {
            background: #2563eb;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }

        .env-info {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #64748b;
        }

        .env-info code {
            color: #fbbf24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>AWS-System Deployment</h1>
        <p class="subtitle">Run these commands in order (1 to 7). Steps 8-9 are for maintenance.</p>

        <div class="warning">
            WARNING: <strong>DELETE this file</strong> after deployment is complete! Leaving it accessible is a security
            risk.
        </div>

        <div class="commands">
            <?php foreach ($commands as $key => $cmd): ?>
                <div class="cmd-card">
                    <div style="display:flex;align-items:center;">
                        <span class="step-num"><?= $cmd['order'] ?></span>
                        <div class="cmd-info">
                            <h3><?= $cmd['label'] ?></h3>
                            <p><?= $cmd['description'] ?></p>
                        </div>
                    </div>
                    <a class="cmd-btn" href="?token=<?= urlencode($SECRET_TOKEN) ?>&run=<?= $key ?>">Run</a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($output): ?>
            <div class="output-box">
                <h2>Output: <?= $commands[$ranCommand]['label'] ?? $ranCommand ?></h2>
                <pre><?= htmlspecialchars($output) ?></pre>
            </div>
        <?php endif; ?>

        <p class="env-info">
            Environment: <code><?= app()->environment() ?></code> |
            PHP: <code><?= PHP_VERSION ?></code> |
            Laravel: <code><?= app()->version() ?></code> |
            Base Path: <code><?= $basePath ?></code>
        </p>
    </div>
</body>

</html>