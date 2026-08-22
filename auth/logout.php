<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/helpers.php';

$_SESSION = [];
clear_auth_cookie();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('info', 'You have been safely signed out of your session.');
redirect(base_url('auth/login.php'));
?>
