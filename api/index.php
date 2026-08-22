<?php
// Vercel serverless front-controller router for Synapse-ERP

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);
$uri = ltrim($uri, '/');

$baseDir = dirname(__DIR__);

// 1. Root route
if (empty($uri) || $uri === 'index.php') {
    require $baseDir . '/index.php';
    exit;
}

$targetPath = $baseDir . '/' . $uri;
$targetFile = realpath($targetPath);

// 2. Direct file match within base directory
if ($targetFile && strpos($targetFile, $baseDir) === 0 && file_exists($targetFile)) {
    if (is_dir($targetFile)) {
        $indexFile = $targetFile . '/index.php';
        if (file_exists($indexFile)) {
            require $indexFile;
            exit;
        }
    } else {
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if ($ext === 'php') {
            require $targetFile;
            exit;
        } else {
            // Static file serving fallback
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
            header("Cache-Control: public, max-age=86400");
            header("Content-Length: " . filesize($targetFile));
            readfile($targetFile);
            exit;
        }
    }
}

// 3. Fallback for URL without .php extension (e.g. /dashboard -> /dashboard.php)
$targetPhp = realpath($baseDir . '/' . $uri . '.php');
if ($targetPhp && strpos($targetPhp, $baseDir) === 0 && file_exists($targetPhp)) {
    require $targetPhp;
    exit;
}

// 4. Default: load index.php
require $baseDir . '/index.php';


