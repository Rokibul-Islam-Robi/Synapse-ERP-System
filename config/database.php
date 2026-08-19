<?php
/**
 * Synapse-ERP Database Connector
 * Supports Localhost (XAMPP/MAMP), Docker, and Cloud (Vercel/PlanetScale/Aiven/Railway)
 */

$host = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
$port = getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306');
$db_primary = getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'synapse_erp_db');
$db_fallback = 'mini_inventory_db';
$username = getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
$password = getenv('DB_PASS') ?: (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');

$dsnOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

// Handle SSL for Cloud MySQL providers if present
$sslCa = getenv('MYSQL_ATTR_SSL_CA') ?: (isset($_ENV['MYSQL_ATTR_SSL_CA']) ? $_ENV['MYSQL_ATTR_SSL_CA'] : null);
if ($sslCa && file_exists($sslCa)) {
    $dsnOptions[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
}

try {
    // 1. Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password, $dsnOptions);

    // 2. Select or create database
    $stmt = $pdo->query("SHOW DATABASES LIKE '$db_primary'");
    $hasPrimary = $stmt->fetch();

    $stmt2 = $pdo->query("SHOW DATABASES LIKE '$db_fallback'");
    $hasFallback = $stmt2->fetch();

    if ($hasPrimary) {
        $activeDb = $db_primary;
    } elseif ($hasFallback) {
        $activeDb = $db_fallback;
    } else {
        // Auto-create database if privileges allow
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_primary` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $activeDb = $db_primary;
        } catch (Exception $ex) {
            $activeDb = $db_primary;
        }
    }

    // 3. Switch to active database
    $pdo->exec("USE `$activeDb`");

    // 4. Run automatic schema migrations & seedings
    require_once __DIR__ . '/../database/migrate.php';
    run_migrations($pdo);

} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:32px;background:#fef2f2;color:#991b1b;border:1px solid #f87171;border-radius:12px;max-width:640px;margin:60px auto;box-shadow:0 10px 25px rgba(0,0,0,0.08);'>
        <div style='display:flex;align-items:center;gap:12px;margin-bottom:12px;'>
            <div style='width:36px;height:36px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#ef4444;font-weight:bold;font-size:20px;'>!</div>
            <h2 style='margin:0;font-size:20px;'>MySQL Database Connection Error</h2>
        </div>
        <p style='margin:8px 0;line-height:1.6;'>Could not connect to MySQL server on <code>$host:$port</code>.</p>
        <p style='background:#ffffff;padding:12px;border-radius:6px;border:1px solid #fecaca;font-family:monospace;font-size:12.5px;color:#b91c1c;overflow-x:auto;'>
            " . htmlspecialchars($e->getMessage()) . "
        </p>
        <hr style='border:0;border-top:1px solid #fca5a5;margin:18px 0;'>
        <div style='font-size:13.5px;color:#7f1d1d;line-height:1.6;'>
            <strong>Troubleshooting:</strong>
            <ul style='padding-left:20px;margin:8px 0;'>
                <li><strong>Localhost:</strong> Ensure MySQL service is running in XAMPP Control Panel.</li>
                <li><strong>Vercel / Cloud:</strong> Set environment variables <code>DB_HOST</code>, <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_NAME</code>, and <code>DB_PORT</code>.</li>
            </ul>
        </div>
    </div>");
}
?>
