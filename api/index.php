<?php
// Vercel serverless front-controller router for Synapse-ERP

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);
$uri = ltrim($uri, '/');

$rootDir = dirname(__DIR__);

// 1. Root route
if (empty($uri) || $uri === 'index.php') {
    require $rootDir . '/index.php';
    exit;
}

// Strip query / path traversal
$cleanPath = str_replace(['../', '..\\'], '', $uri);
$targetFile = $rootDir . '/' . $cleanPath;

// 2. Direct static file match
if (file_exists($targetFile) && !is_dir($targetFile)) {
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        require $targetFile;
        exit;
    } else {
        $mimes = [
            'css'   => 'text/css; charset=UTF-8',
            'js'    => 'application/javascript; charset=UTF-8',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'ico'   => 'image/x-icon',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'json'  => 'application/json'
        ];
        $mimeType = $mimes[$ext] ?? 'application/octet-stream';
        header("Content-Type: $mimeType");
        header("Cache-Control: public, max-age=31536000, immutable");
        header("Access-Control-Allow-Origin: *");
        if (filesize($targetFile) > 0) {
            header("Content-Length: " . filesize($targetFile));
        }
        readfile($targetFile);
        exit;
    }
}

// 3. Directory with index.php
if (is_dir($targetFile) && file_exists($targetFile . '/index.php')) {
    require $targetFile . '/index.php';
    exit;
}

// 4. Extensionless PHP URL (e.g. /dashboard -> /dashboard.php)
if (file_exists($targetFile . '.php')) {
    require $targetFile . '.php';
    exit;
}

// 5. Handle nested/invalid /auth/ requests (e.g. /auth/dashboard.php or /auth/auth/...)
if (strpos($cleanPath, 'auth/') === 0) {
    $sub = substr($cleanPath, 5);
    if (file_exists($rootDir . '/' . $sub)) {
        header("Location: /" . $sub);
        exit;
    }
    header("Location: /auth/login.php");
    exit;
}

// 6. Default fallback
require $rootDir . '/index.php';



