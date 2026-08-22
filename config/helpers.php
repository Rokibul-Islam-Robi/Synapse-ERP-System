<?php
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_path', '/');
    }
    session_start();
}

/**
 * Dynamic Base URL detection
 * Automatically detects whether running inside /synapse-erp/, /synapse_erp/, /Synapse-ERP/, or root domain
 */
function base_url($path = '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $rootMarkers = [
        '/auth/', '/categories/', '/products/', '/stock/', 
        '/reports/', '/suppliers/', '/customers/', '/users/', 
        '/config/', '/database/', '/includes/', '/assets/', '/tests/', '/api/'
    ];
    
    $base = '';
    foreach ($rootMarkers as $marker) {
        $pos = strpos($scriptName, $marker);
        if ($pos !== false) {
            $base = substr($scriptName, 0, $pos);
            break;
        }
    }
    
    if ($base === '') {
        $dir = dirname($scriptName);
        $base = ($dir === '/' || $dir === '\\' || $dir === '.') ? '' : str_replace('\\', '/', $dir);
    }
    
    $path = ltrim($path, '/');
    return rtrim($base, '/') . ($path ? '/' . $path : '');
}

function clean($value) {
    if (is_null($value)) return '';
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = base_url($url);
    }
    header("Location: " . $url);
    exit;
}

// -------------------------------------------------------------
// Authentication & Role-Based Access Control (RBAC)
// -------------------------------------------------------------

if (!defined('AUTH_SECRET_KEY')) {
    define('AUTH_SECRET_KEY', 'synapse_erp_secret_key_2026_jwt_token_auth');
}

function set_auth_cookie($userId, $name, $username, $role) {
    $payload = json_encode([
        'uid' => $userId,
        'name' => $name,
        'uname' => $username,
        'role' => $role,
        'exp' => time() + (86400 * 30)
    ]);
    $encoded = base64_encode($payload);
    $signature = hash_hmac('sha256', $encoded, AUTH_SECRET_KEY);
    $token = $encoded . '.' . $signature;
    
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie('synapse_auth_token', $token, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function clear_auth_cookie() {
    setcookie('synapse_auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'httponly' => true
    ]);
}

function restore_auth_from_cookie() {
    if (!empty($_SESSION['user_id'])) {
        return true;
    }
    if (empty($_COOKIE['synapse_auth_token'])) {
        return false;
    }
    $parts = explode('.', $_COOKIE['synapse_auth_token']);
    if (count($parts) !== 2) {
        return false;
    }
    list($encoded, $sig) = $parts;
    $expectedSig = hash_hmac('sha256', $encoded, AUTH_SECRET_KEY);
    if (!hash_equals($expectedSig, $sig)) {
        return false;
    }
    $data = json_decode(base64_decode($encoded), true);
    if (!$data || !isset($data['uid']) || !isset($data['exp']) || $data['exp'] < time()) {
        return false;
    }
    $_SESSION['user_id'] = $data['uid'];
    $_SESSION['name'] = $data['name'] ?? '';
    $_SESSION['username'] = $data['uname'] ?? '';
    $_SESSION['role'] = $data['role'] ?? 'admin';
    return true;
}

function is_logged_in() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    return restore_auth_from_cookie();
}

function require_login() {
    if (!is_logged_in()) {
        redirect(base_url('auth/login.php'));
    }
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function current_user_role() {
    return strtolower($_SESSION['role'] ?? 'staff');
}

function is_admin() {
    return current_user_role() === 'admin';
}

function is_manager() {
    return current_user_role() === 'manager' || is_admin();
}

function is_staff() {
    return current_user_role() === 'staff';
}

function can_manage_users() {
    return is_admin();
}

function can_delete() {
    return is_admin() || current_user_role() === 'manager';
}

function can_view_reports() {
    return is_admin() || current_user_role() === 'manager';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Access denied. Administrator privileges required.');
        redirect(base_url('dashboard.php'));
    }
}

function require_manager() {
    require_login();
    if (!is_manager()) {
        set_flash('error', 'Access denied. Managerial or Administrator authorization required.');
        redirect(base_url('dashboard.php'));
    }
}

// -------------------------------------------------------------
// Flash Notifications System
// -------------------------------------------------------------

function set_flash($type, $message) {
    $_SESSION['flash_msg'] = [
        'type' => $type, // 'success', 'danger'/'error', 'warning', 'info'
        'text' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
        return $msg;
    }
    return null;
}

function render_flash() {
    $flash = get_flash();
    if (!$flash) return '';
    
    $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
    $iconMap = [
        'success' => '<svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        'danger' => '<svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        'warning' => '<svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        'info' => '<svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    ];
    $icon = $iconMap[$type] ?? $iconMap['info'];

    return "<div class='alert alert-{$type}'>
        <div class='alert-content'>{$icon}<span>" . clean($flash['text']) . "</span></div>
        <button type='button' class='alert-close' onclick='this.parentElement.remove()'>&times;</button>
    </div>";
}

// -------------------------------------------------------------
// Activity Logging & Notifications
// -------------------------------------------------------------

function log_activity($pdo, $action, $details = '') {
    try {
        $userId = current_user_id();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (Exception $e) {
        // Silently ignore logging failures
    }
}

function format_currency($amount, $currency = '$') {
    return $currency . number_format((float)$amount, 2);
}

function get_product_stock($pdo, $productId) {
    $sql = "SELECT 
                p.id,
                p.opening_stock,
                p.alert_quantity,
                COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0) AS stock_in,
                COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS stock_out,
                p.opening_stock
                + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0)
                - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS current_stock
            FROM products p
            LEFT JOIN stock_transactions st ON st.product_id = p.id
            WHERE p.id = ?
            GROUP BY p.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    $res = $stmt->fetch();
    return $res ? (int)$res['current_stock'] : 0;
}

function get_low_stock_count($pdo) {
    try {
        $sql = "SELECT COUNT(*) as total_low FROM (
            SELECT p.id,
                   p.opening_stock + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
                                   - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock,
                   COALESCE(p.alert_quantity, 5) AS alert_limit
            FROM products p
            LEFT JOIN stock_transactions st ON st.product_id = p.id
            WHERE p.status = 1
            GROUP BY p.id
            HAVING current_stock <= alert_limit
        ) AS low_stock_table";
        return (int)$pdo->query($sql)->fetch()['total_low'];
    } catch (Exception $e) {
        return 0;
    }
}

function get_system_notifications($pdo) {
    $notifications = [];
    try {
        // 1. Low stock critical notices
        $lowSql = "SELECT p.id, p.product_name, p.sku, p.alert_quantity,
                          p.opening_stock + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
                                          - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock
                   FROM products p
                   LEFT JOIN stock_transactions st ON st.product_id = p.id
                   WHERE p.status = 1
                   GROUP BY p.id
                   HAVING current_stock <= p.alert_quantity
                   LIMIT 4";
        $lowItems = $pdo->query($lowSql)->fetchAll();
        foreach ($lowItems as $li) {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Low Stock Reorder Alert',
                'message' => "{$li['product_name']} has only {$li['current_stock']} left (Limit: {$li['alert_quantity']})",
                'link' => base_url("stock/stock_in.php?product_id={$li['id']}"),
                'time' => 'Action required'
            ];
        }

        // 2. Recent transactions
        $recentSql = "SELECT st.transaction_type, st.quantity, st.transaction_date, p.product_name 
                      FROM stock_transactions st
                      JOIN products p ON p.id = st.product_id
                      ORDER BY st.id DESC LIMIT 3";
        $recent = $pdo->query($recentSql)->fetchAll();
        foreach ($recent as $rc) {
            $typeLabel = $rc['transaction_type'] === 'IN' ? 'Stock In' : 'Stock Out';
            $notifications[] = [
                'type' => $rc['transaction_type'] === 'IN' ? 'success' : 'info',
                'title' => "Recent {$typeLabel}",
                'message' => "{$rc['quantity']} units of {$rc['product_name']}",
                'link' => base_url('reports/date_wise.php'),
                'time' => date('d M', strtotime($rc['transaction_date']))
            ];
        }
    } catch (Exception $e) {
        // Silently handle
    }
    return $notifications;
}
?>
