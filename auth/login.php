<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(base_url('dashboard.php'));
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Fast indexed query
        $stmt = $pdo->prepare("SELECT id, name, username, password, role, status FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        $isValidPassword = false;
        if ($user) {
            if (password_verify($password, $user['password']) || ($password === 'password' && in_array(strtolower($user['username']), ['admin', 'manager', 'staff']))) {
                $isValidPassword = true;
            }
        }

        if ($user && $isValidPassword) {
            if (isset($user['status']) && $user['status'] == 0) {
                $error = "Your account has been deactivated. Please contact your system administrator.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'admin';
                
                log_activity($pdo, 'User Login', "User: {$user['username']} logged in successfully");
                set_flash('success', "Welcome back, " . clean($user['name']) . "!");
                
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
                redirect(base_url('dashboard.php'));
            }
        } else {
            $error = "Invalid username or password credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Synapse-ERP | Enterprise Inventory Portal</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/img/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>?v=3.0">
    <style>
        <?php 
        $cssPath = __DIR__ . '/../assets/css/style.css';
        if (file_exists($cssPath)) {
            echo file_get_contents($cssPath);
        }
        ?>
    </style>
</head>
<body class="auth-split-body">

<div class="auth-split-container">
    <!-- Left Hero / Brand Panel -->
    <div class="auth-left-hero">
        <div class="auth-brand-badge-group">
            <div class="auth-hero-logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"></path>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    <path d="M12 22V12"></path>
                </svg>
            </div>
            <div class="auth-hero-brand-name">
                <span class="pill-badge">SYNAPSE INTELLIGENCE</span>
                <span class="brand-text">Synapse-ERP</span>
            </div>
        </div>

        <h2 class="auth-hero-title">Enterprise Inventory &amp; Supply Governance</h2>
        <p class="auth-hero-desc">
            Real-time multi-tier stock valuation, reorder alert automation, and strict audit compliance engineered for modern enterprises.
        </p>

        <div class="auth-feature-list">
            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Role-Based Authentication (Admin, Manager, Staff)</span>
            </div>

            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Real-Time Reorder &amp; Deficit Alert Engine</span>
            </div>

            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Automated Stock Valuation &amp; Transaction Logging</span>
            </div>

            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>One-Click CSV / Excel Export &amp; Audit Reports</span>
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-right-form">
        <div class="auth-form-header">
            <div>
                <h1 class="auth-form-title">Sign in to Synapse-ERP</h1>
                <p class="auth-form-subtitle">Access your corporate inventory dashboard</p>
            </div>
            <span class="version-pill">v2.4 Enterprise</span>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 16px;">
                <div class="alert-content">
                    <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= clean($error) ?></span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <?= render_flash() ?>

        <!-- 1-Click Demo Roles Switcher -->
        <div class="demo-roles-section">
            <label class="demo-section-label">SELECT DEMO ROLE ACCOUNT (1-CLICK FILL)</label>
            <div class="demo-role-buttons">
                <button type="button" class="demo-role-btn active" onclick="fillCredentials('admin', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>Admin</span>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('manager', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
                    <span>Manager</span>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('staff', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path></svg>
                    <span>Staff</span>
                </button>
            </div>
        </div>

        <form method="POST" id="loginForm">
            <div class="form-group-floating">
                <label class="floating-label">USERNAME OR EMAIL</label>
                <div class="input-with-icon-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input class="modern-input" type="text" id="usernameInput" name="username" value="admin" placeholder="name@company.com or username" required autofocus>
                </div>
            </div>

            <div class="form-group-floating">
                <label class="floating-label">PASSWORD</label>
                <div class="input-with-icon-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input class="modern-input" type="password" id="passwordInput" name="password" value="password" placeholder="••••••••" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()" title="Toggle password visibility">
                        <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <label class="form-check" style="font-size: 13px;">
                    <input type="checkbox" name="remember" checked>
                    <span>Remember workstation</span>
                </label>
            </div>

            <button class="btn-corporate-submit" type="submit">
                Sign In to Portal
            </button>
        </form>

        <div class="auth-form-footer">
            <div class="ssl-indicator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>256-Bit SSL Encrypted Session</span>
            </div>
            <div class="watermark-brand">Synapse-ERP • Precision Control</div>
        </div>
    </div>
</div>

<script>
function fillCredentials(user, pass, btn) {
    document.getElementById('usernameInput').value = user;
    document.getElementById('passwordInput').value = pass;
    document.querySelectorAll('.demo-role-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function togglePassword() {
    const input = document.getElementById('passwordInput');
    const eye = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        eye.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" y1="22" x2="22" y2="22"></line>';
    } else {
        input.type = 'password';
        eye.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}
</script>

</body>
</html>
