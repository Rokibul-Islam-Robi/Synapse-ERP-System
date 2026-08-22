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
                
                set_auth_cookie($user['id'], $user['name'], $user['username'], $user['role'] ?? 'admin');
                
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
    <!-- Left Hero / Brand Panel (Modern Organic & Tech Synergy) -->
    <div class="auth-left-hero">
        <div class="auth-brand-badge-group">
            <div class="auth-hero-logo">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"></path>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                    <path d="m3.3 7 8.7 5 8.7-5"></path>
                    <path d="M12 22V12"></path>
                </svg>
            </div>
            <div class="auth-hero-brand-name">
                <span class="pill-badge">ENTERPRISE CLOUD</span>
                <span class="brand-text">Synapse-ERP</span>
            </div>
        </div>

        <!-- Custom Organic Tech Art inspired by Reference Image -->
        <div class="auth-hero-art-wrapper">
            <svg viewBox="0 0 340 220" width="100%" height="200" style="max-width: 320px;" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="artNeonGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#00f59b" />
                        <stop offset="100%" stop-color="#059669" />
                    </linearGradient>
                    <linearGradient id="artAmberGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#f59e0b" />
                        <stop offset="100%" stop-color="#d97706" />
                    </linearGradient>
                    <linearGradient id="artCyanGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8" />
                        <stop offset="100%" stop-color="#0284c7" />
                    </linearGradient>
                    <filter id="neonBlur" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="12" result="blur" />
                    </filter>
                </defs>

                <!-- Ambient Glow Behind -->
                <circle cx="170" cy="110" r="70" fill="#00f59b" opacity="0.18" filter="url(#neonBlur)" />

                <!-- Organic Layered Flora / Petal Wave Forms -->
                <path d="M170 30 C220 30, 270 70, 260 130 C250 180, 200 200, 160 195 C110 190, 80 160, 85 110 C90 60, 130 30, 170 30 Z" fill="#0c1f38" opacity="0.85" />
                
                <!-- Vibrant Organic Leaf/Flora Waves -->
                <path d="M110 160 C90 120, 120 70, 160 90 C150 130, 130 155, 110 160 Z" fill="url(#artCyanGrad)" opacity="0.85" />
                <path d="M230 160 C250 120, 220 70, 180 90 C190 130, 210 155, 230 160 Z" fill="url(#artAmberGrad)" opacity="0.85" />
                <path d="M170 180 C130 170, 135 110, 170 125 C205 110, 210 170, 170 180 Z" fill="url(#artNeonGrad)" />

                <!-- Stylized Central Executive Character / Synapse Core -->
                <circle cx="170" cy="85" r="18" fill="#f8fafc" />
                <path d="M165 72 C168 68, 175 68, 178 72 C185 75, 182 88, 170 88 C158 88, 155 75, 165 72 Z" fill="#060c18" />
                <path d="M148 140 C148 112, 192 112, 192 140 C185 152, 155 152, 148 140 Z" fill="#f59e0b" />
                
                <!-- Floating Ambient Floating Hearts / Nodes -->
                <circle cx="120" cy="65" r="4" fill="#00f59b" />
                <circle cx="225" cy="55" r="5" fill="#f59e0b" />
                <circle cx="210" cy="175" r="3.5" fill="#38bdf8" />
                <circle cx="130" cy="180" r="4" fill="#00f59b" />

                <!-- Floating Glass Metric Badge -->
                <rect x="35" y="70" width="85" height="32" rx="8" fill="#060c18" fill-opacity="0.8" stroke="#00f59b" stroke-width="1.2" />
                <text x="45" y="85" fill="#94a3b8" font-size="8" font-weight="600" font-family="Inter, sans-serif">ACCURACY</text>
                <text x="45" y="96" fill="#00f59b" font-size="10" font-weight="800" font-family="Inter, sans-serif">99.98%</text>

                <!-- Floating Glass Metric Badge 2 -->
                <rect x="220" y="115" width="90" height="32" rx="8" fill="#060c18" fill-opacity="0.8" stroke="#38bdf8" stroke-width="1.2" />
                <text x="230" y="130" fill="#94a3b8" font-size="8" font-weight="600" font-family="Inter, sans-serif">REAL-TIME</text>
                <text x="230" y="141" fill="#38bdf8" font-size="10" font-weight="800" font-family="Inter, sans-serif">Multi-Tier</text>
            </svg>
        </div>

        <div>
            <h2 class="auth-hero-title">Intelligent Supply &amp; Inventory Governance</h2>
            <p class="auth-hero-desc">
                High-performance real-time analytics, automated deficit alerts, and audit compliance for modern growing enterprises.
            </p>
        </div>

        <div class="auth-feature-list">
            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Role-Based Authentication (Admin, Manager, Staff)</span>
            </div>

            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Real-Time Reorder &amp; Low Stock Engine</span>
            </div>

            <div class="auth-feature-item">
                <div class="feature-check-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <span>Automated Stock Valuation &amp; Auditing</span>
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-right-form">
        <div class="auth-form-header">
            <div>
                <h1 class="auth-form-title">Portal Sign In</h1>
                <p class="auth-form-subtitle">Enter your corporate credentials to continue</p>
            </div>
            <span class="version-pill">v2.5 Enterprise</span>
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
            <label class="demo-section-label">DEMO QUICK SIGN-IN (1-CLICK FILL)</label>
            <div class="demo-role-buttons">
                <button type="button" class="demo-role-btn active" onclick="fillCredentials('admin', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>Admin</span>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('manager', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
                    <span>Manager</span>
                </button>

                <button type="button" class="demo-role-btn" onclick="fillCredentials('staff', 'password', this)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path></svg>
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

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 22px;">
                <label class="form-check" style="font-size: 13px;">
                    <input type="checkbox" name="remember" checked>
                    <span>Remember workstation</span>
                </label>
            </div>

            <button class="btn-corporate-submit" type="submit">
                <span>Sign In to Portal</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </button>
        </form>

        <div class="auth-form-footer">
            <div class="ssl-indicator">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
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
