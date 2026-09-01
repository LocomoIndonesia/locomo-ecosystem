<?php
// ============================================================
// MANDEH.ONLINE - Server Diagnostic Tool
// PENTING: Hapus file ini setelah selesai debugging!
// ============================================================

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$results = [];
$errors = [];

// 1. PHP Version Check
$results['php_version'] = PHP_VERSION;
$results['php_sapi'] = PHP_SAPI;

// 2. Required PHP Extensions
$required_ext = ['pdo', 'pdo_pgsql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];
$results['extensions'] = [];
foreach ($required_ext as $ext) {
    $loaded = extension_loaded($ext);
    $results['extensions'][$ext] = $loaded ? '✅ Loaded' : '❌ MISSING';
    if (!$loaded) {
        $errors[] = "Extension missing: $ext";
    }
}

// 3. Check if .env is readable
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path) && is_readable($env_path)) {
    $results['env_file'] = '✅ Found & Readable';
    // Parse all non-sensitive .env lines
    $env_content = file_get_contents($env_path);
    $lines = explode("\n", $env_content);
    $results['env_vars'] = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Hide sensitive values
            if (preg_match('/(key|password|secret|token|pass|db_username)/i', $key)) {
                $val = '[REDACTED]';
            }
            $results['env_vars'][$key] = $val;
        }
    }
} else {
    $results['env_file'] = '❌ NOT FOUND or NOT READABLE at: ' . $env_path;
    $errors[] = '.env file not found!';
}

// 4. Check key directories
$directories = [
    'storage/framework' => __DIR__ . '/../storage/framework',
    'storage/logs'      => __DIR__ . '/../storage/logs',
    'bootstrap/cache'   => __DIR__ . '/../bootstrap/cache',
    'vendor'            => __DIR__ . '/../vendor',
];
$results['directories'] = [];
foreach ($directories as $label => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    $results['directories'][$label] = $exists
        ? ($writable ? '✅ Exists & Writable' : '⚠️  Exists but NOT Writable')
        : '❌ NOT FOUND';
    if (!$exists) {
        $errors[] = "Directory missing: $label";
    }
    if ($exists && !$writable) {
        $errors[] = "Directory not writable: $label";
    }
}

// 5. Check if bootstrap/cache/config.php exists (stale cache check)
$cache_config = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($cache_config)) {
    // Read just DB_HOST from cached config
    $cache_content = file_get_contents($cache_config);
    preg_match("/'host'\s*=>\s*'([^']+)'/", $cache_content, $m);
    $cached_host = $m[1] ?? 'unknown';
    $results['bootstrap_cache_config'] = "⚠️  EXISTS (cached DB_HOST appears to be: $cached_host) - Run: php artisan config:clear";
    $errors[] = "Stale bootstrap/cache/config.php found! DB_HOST in cache: $cached_host. Run: php artisan config:clear";
} else {
    $results['bootstrap_cache_config'] = '✅ No stale cache file';
}

// 6. Test PostgreSQL connection
$results['pgsql_connection'] = 'Not tested (pdo_pgsql missing)';
if (extension_loaded('pdo_pgsql')) {
    $env_path = __DIR__ . '/../.env';
    if (file_exists($env_path)) {
        $env_content = file_get_contents($env_path);
        preg_match('/DB_HOST=(.+)/', $env_content, $mh);
        preg_match('/DB_PORT=(.+)/', $env_content, $mp);
        preg_match('/DB_DATABASE=(.+)/', $env_content, $md);
        preg_match('/DB_USERNAME=(.+)/', $env_content, $mu);
        preg_match('/DB_PASSWORD=(.+)/', $env_content, $mpw);
        $host = trim($mh[1] ?? '127.0.0.1');
        $port = trim($mp[1] ?? '5432');
        $db   = trim($md[1] ?? '');
        $user = trim($mu[1] ?? '');
        $pass = trim($mpw[1] ?? '');
        try {
            $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
            $results['pgsql_connection'] = '✅ SUCCESS! Connected to PostgreSQL.';
            
            // Check tables and users
            try {
                $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $results['db_tables'] = implode(', ', $tables);
                
                if (in_array('users', $tables)) {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                    $results['users_count'] = $stmt->fetchColumn();
                    
                    $stmt = $pdo->query("SELECT id, name, email, role, password FROM users LIMIT 10");
                    $results['users_list'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $results['users_count'] = '❌ Table "users" does not exist!';
                }
            } catch (Exception $e) {
                $results['db_query_error'] = $e->getMessage();
            }
        } catch (PDOException $e) {
            $results['pgsql_connection'] = '❌ FAILED: ' . $e->getMessage();
            $errors[] = 'DB Connection failed: ' . $e->getMessage();
        }
    }
}

// 7. Try to load Laravel autoloader
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    try {
        require $autoloader;
        $results['laravel_autoload'] = '✅ vendor/autoload.php loaded OK';
    } catch (Throwable $e) {
        $results['laravel_autoload'] = '❌ FAILED: ' . $e->getMessage();
        $errors[] = 'Laravel autoload failed: ' . $e->getMessage();
    }
} else {
    $results['laravel_autoload'] = '❌ vendor/autoload.php NOT FOUND - run composer install!';
    $errors[] = 'vendor/autoload.php missing!';
}

// ============================================================
// OUTPUT HTML
// ============================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mandeh.Online - Server Diagnostic</title>
    <style>
        body { font-family: 'Segoe UI', monospace; background: #0f172a; color: #e2e8f0; margin: 0; padding: 24px; }
        h1 { color: #38bdf8; border-bottom: 2px solid #1e40af; padding-bottom: 8px; }
        h2 { color: #7dd3fc; margin-top: 28px; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        td, th { padding: 8px 12px; border: 1px solid #1e3a5f; text-align: left; font-size: 0.875rem; }
        th { background: #1e3a5f; color: #93c5fd; }
        tr:hover { background: #1e293b; }
        .error-box { background: #450a0a; border: 1px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .error-box h2 { color: #f87171; margin-top: 0; }
        .error-item { background: #7f1d1d; border-radius: 4px; padding: 8px 12px; margin: 4px 0; font-family: monospace; }
        .ok-box { background: #052e16; border: 1px solid #16a34a; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .ok-box h2 { color: #4ade80; margin-top: 0; }
        .warn { color: #fbbf24; }
    </style>
</head>
<body>
<h1>🔍 Mandeh.Online — Server Diagnostic Report</h1>
<p class="warn">⚠️ HAPUS FILE INI (debug.php) setelah selesai debugging!</p>

<?php if (!empty($errors)): ?>
<div class="error-box">
    <h2>❌ Ditemukan <?= count($errors) ?> Masalah</h2>
    <?php foreach ($errors as $e): ?>
        <div class="error-item"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="ok-box">
    <h2>✅ Tidak Ada Masalah Kritis Ditemukan</h2>
    <p>Semua komponen berjalan normal. Periksa storage/logs/laravel.log untuk detail error lebih lanjut.</p>
</div>
<?php endif; ?>

<h2>PHP Environment</h2>
<table>
    <tr><th>Item</th><th>Value</th></tr>
    <tr><td>PHP Version</td><td><?= htmlspecialchars($results['php_version']) ?></td></tr>
    <tr><td>PHP SAPI</td><td><?= htmlspecialchars($results['php_sapi']) ?></td></tr>
</table>

<h2>Required PHP Extensions</h2>
<table>
    <tr><th>Extension</th><th>Status</th></tr>
    <?php foreach ($results['extensions'] as $ext => $status): ?>
    <tr><td><?= htmlspecialchars($ext) ?></td><td><?= $status ?></td></tr>
    <?php endforeach; ?>
</table>

<h2>.env Configuration</h2>
<table>
    <tr><th>Item</th><th>Value</th></tr>
    <tr><td>.env File</td><td><?= $results['env_file'] ?></td></tr>
    <?php if (isset($results['env_vars'])): ?>
        <?php foreach ($results['env_vars'] as $key => $val): ?>
        <tr><td><?= htmlspecialchars($key) ?></td><td><?= htmlspecialchars($val) ?></td></tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<h2>Directories & Permissions</h2>
<table>
    <tr><th>Directory</th><th>Status</th></tr>
    <?php foreach ($results['directories'] as $dir => $status): ?>
    <tr><td><?= htmlspecialchars($dir) ?></td><td><?= $status ?></td></tr>
    <?php endforeach; ?>
</table>

<h2>Laravel Config Cache</h2>
<table>
    <tr><th>Item</th><th>Status</th></tr>
    <tr><td>bootstrap/cache/config.php</td><td><?= $results['bootstrap_cache_config'] ?></td></tr>
</table>

<h2>Database Connection Test</h2>
<table>
    <tr><th>Test</th><th>Result</th></tr>
    <tr><td>PostgreSQL Connect</td><td><?= $results['pgsql_connection'] ?></td></tr>
    <?php if (isset($results['db_tables'])): ?>
    <tr><td>Database Tables</td><td><?= htmlspecialchars($results['db_tables']) ?></td></tr>
    <?php endif; ?>
    <?php if (isset($results['users_count'])): ?>
    <tr><td>Users Count</td><td><?= htmlspecialchars($results['users_count']) ?></td></tr>
    <?php endif; ?>
    <?php if (isset($results['db_query_error'])): ?>
    <tr><td>Query Error</td><td style="color: #f87171;"><?= htmlspecialchars($results['db_query_error']) ?></td></tr>
    <?php endif; ?>
</table>

<?php if (isset($results['users_list']) && !empty($results['users_list'])): ?>
<h2>Users in Database (First 10)</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Password Hash</th></tr>
    <?php foreach ($results['users_list'] as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= htmlspecialchars($u['password']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Laravel Autoloader</h2>
<table>
    <tr><th>Test</th><th>Result</th></tr>
    <tr><td>vendor/autoload.php</td><td><?= $results['laravel_autoload'] ?></td></tr>
</table>

<p style="color:#64748b;margin-top:40px;font-size:0.75rem;">Generated: <?= date('Y-m-d H:i:s T') ?> | Server: <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></p>
</body>
</html>
