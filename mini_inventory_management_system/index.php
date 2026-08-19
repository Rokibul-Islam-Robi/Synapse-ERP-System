<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/helpers.php';

if (is_logged_in()) {
    redirect(base_url('dashboard.php'));
} else {
    redirect(base_url('auth/login.php'));
}
?>
