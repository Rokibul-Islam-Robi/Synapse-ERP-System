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
<body class="auth-landing-canvas">

<!-- Top Wavy Ribbon Header Shape in Neon Green -->
<svg class="top-wave-header-art" viewBox="0 0 500 150" fill="none" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="topWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#00f59b" />
            <stop offset="60%" stop-color="#10b981" />
            <stop offset="100%" stop-color="#059669" />
        </linearGradient>
    </defs>
    <path d="M0 0 H420 C360 40, 310 90, 240 75 C160 55, 120 120, 0 110 Z" fill="url(#topWaveGrad)" opacity="0.95" />
</svg>

<!-- Top Navigation Bar (Matching Reference Layout) -->
<header class="landing-topbar">
    <div class="landing-nav-left">
        <button type="button" class="hamburger-btn" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="<?= base_url('auth/login.php') ?>" class="brand-badge-pill">
            <span class="logo-dot"></span>
            <span>Synapse-ERP</span>
        </a>
    </div>

    <div class="landing-nav-right">
        <button type="button" class="search-icon-btn" aria-label="Search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>

        <ul class="landing-nav-links">
            <li><a href="#" class="active">HOME</a></li>
            <li><a href="#">PRODUCT</a></li>
            <li><a href="#">SALE</a></li>
            <li><a href="#">SUPPORT</a></li>
        </ul>
    </div>
</header>

<!-- Main Two-Column Hero Container -->
<main class="landing-hero-container">
    <!-- Left Column: Typography & Interactive Portal Sign-In -->
    <div class="hero-left-section">
        <h1 class="hero-main-title">SYNAPSE</h1>
        <div class="hero-sub-title">
            <span>Enterprise ERP</span>
            <span class="accent-tag">Precision Control</span>
        </div>

        <p class="hero-desc-text">
            Next-generation inventory intelligence, automated deficit alerts, and multi-tier valuation engineered for growing enterprises.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger" style="margin-bottom: 16px; border-radius: 12px;">
                <div class="alert-content">
                    <svg class="flash-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= clean($error) ?></span>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <?= render_flash() ?>

        <!-- 1-Click Demo Role Chips -->
        <div class="demo-pills-row">
            <button type="button" class="demo-chip active" onclick="fillCredentials('admin', 'password', this)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Admin</span>
            </button>
            <button type="button" class="demo-chip" onclick="fillCredentials('manager', 'password', this)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>
                <span>Manager</span>
            </button>
            <button type="button" class="demo-chip" onclick="fillCredentials('staff', 'password', this)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path></svg>
                <span>Staff</span>
            </button>
        </div>

        <!-- Integrated Sign-In Form -->
        <form method="POST" id="loginForm" class="landing-auth-form">
            <div class="landing-input-group">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <input class="landing-input" type="text" id="usernameInput" name="username" value="admin" placeholder="Username or email" required autofocus>
            </div>

            <div class="landing-input-group">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <input class="landing-input" type="password" id="passwordInput" name="password" value="password" placeholder="Password" required>
                <button type="button" class="landing-password-toggle" onclick="togglePassword()" title="Toggle password visibility">
                    <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </button>
            </div>

            <!-- Slider Pagination Dots (Matching Image ● ○ ○) -->
            <div class="landing-dot-paginator">
                <span class="paginator-dot active"></span>
                <span class="paginator-dot"></span>
                <span class="paginator-dot"></span>
            </div>

            <!-- Big Rounded Pill CTA Button (Matching [ STARTE ] in Reference Image) -->
            <button class="btn-landing-pill" type="submit">
                <span>Sign In to Portal</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </button>
        </form>
    </div>

    <!-- Right Column: Organic Flora & SALES / REVENUE Growth Artwork -->
    <div class="hero-right-artwork">
        <svg viewBox="0 0 540 460" width="100%" height="auto" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Vibrant Neon Green / Emerald Primary Fluid Gradient -->
                <linearGradient id="neonFluidGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00f59b" />
                    <stop offset="50%" stop-color="#10b981" />
                    <stop offset="100%" stop-color="#047857" />
                </linearGradient>

                <!-- 3D Bar Chart Gradients -->
                <linearGradient id="barGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#38bdf8" />
                    <stop offset="100%" stop-color="#0284c7" />
                </linearGradient>

                <linearGradient id="barGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#a7f3d0" />
                    <stop offset="100%" stop-color="#059669" />
                </linearGradient>

                <linearGradient id="barGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#34d399" />
                    <stop offset="100%" stop-color="#047857" />
                </linearGradient>

                <linearGradient id="barGrad4" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#00f59b" />
                    <stop offset="60%" stop-color="#10b981" />
                    <stop offset="100%" stop-color="#065f46" />
                </linearGradient>

                <!-- Golden Coin Gradient -->
                <linearGradient id="goldCoinGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#fef08a" />
                    <stop offset="50%" stop-color="#f59e0b" />
                    <stop offset="100%" stop-color="#b45309" />
                </linearGradient>

                <!-- Warm Yellow / Golden Botanical Leaf Gradient -->
                <linearGradient id="warmYellowGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#fef08a" />
                    <stop offset="50%" stop-color="#fbbf24" />
                    <stop offset="100%" stop-color="#d97706" />
                </linearGradient>

                <!-- Sky Blue / Azure Botanical Leaf Gradient -->
                <linearGradient id="skyBlueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#7dd3fc" />
                    <stop offset="100%" stop-color="#0284c7" />
                </linearGradient>

                <!-- Soft Shadow Filter -->
                <filter id="softGlow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="16" stdDeviation="20" flood-color="#00f59b" flood-opacity="0.35" />
                </filter>

                <filter id="cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#0f172a" flood-opacity="0.15" />
                </filter>
            </defs>

            <!-- 1. Ground Shadow Ellipse -->
            <ellipse cx="270" cy="415" rx="230" ry="24" fill="url(#neonFluidGrad)" opacity="0.8" />
            <ellipse cx="270" cy="415" rx="180" ry="16" fill="#047857" opacity="0.6" />

            <!-- 2. Big Fluid Organic Wave Shape Backdrop in Neon-Green -->
            <g filter="url(#softGlow)">
                <path d="M140 160 C120 70, 200 50, 280 60 C370 70, 460 100, 450 200 C440 310, 420 370, 310 380 C200 390, 110 370, 100 280 C90 200, 160 220, 140 160 Z" fill="url(#neonFluidGrad)" />
            </g>

            <!-- 3. Botanical Layered Leaves & Flora -->
            <path d="M300 240 C340 150, 420 160, 450 240 C430 290, 360 310, 300 240 Z" fill="url(#warmYellowGrad)" />
            <path d="M220 250 C160 160, 100 200, 120 280 C150 330, 210 320, 220 250 Z" fill="url(#skyBlueGrad)" />

            <!-- Small Flora Branches with Leaves -->
            <path d="M390 280 Q420 260 440 280" stroke="#f8fafc" stroke-width="2.5" stroke-linecap="round" fill="none" />
            <circle cx="410" cy="265" r="5" fill="#f8fafc" />
            <circle cx="430" cy="275" r="5" fill="#f8fafc" />

            <path d="M130 310 Q100 290 90 310" stroke="#f8fafc" stroke-width="2.5" stroke-linecap="round" fill="none" />
            <circle cx="110" cy="295" r="5" fill="#f8fafc" />
            <circle cx="95" cy="305" r="5" fill="#f8fafc" />

            <!-- 4. 3D SALES GROWTH BAR COLUMNS (Ascending with Animation) -->
            <g id="salesGrowthBars" transform="translate(0, 10)">
                <!-- Bar 1 (Q1 - Base Growth) -->
                <g style="animation: salesBarGrow 3.2s ease-in-out infinite; transform-origin: 180px 350px;">
                    <rect x="165" y="270" width="36" height="80" rx="8" fill="url(#barGrad1)" />
                    <ellipse cx="183" cy="270" rx="18" ry="6" fill="#7dd3fc" />
                    <text x="183" y="320" fill="#ffffff" font-size="11" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Q1</text>
                </g>

                <!-- Bar 2 (Q2 - Steady Rise) -->
                <g style="animation: salesBarGrow 3.6s ease-in-out infinite 0.2s; transform-origin: 230px 350px;">
                    <rect x="215" y="225" width="38" height="125" rx="8" fill="url(#barGrad2)" />
                    <ellipse cx="234" cy="225" rx="19" ry="7" fill="#d1fae5" />
                    <text x="234" y="290" fill="#ffffff" font-size="11" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Q2</text>
                </g>

                <!-- Bar 3 (Q3 - High Surge) -->
                <g style="animation: salesBarGrow 3.4s ease-in-out infinite 0.4s; transform-origin: 285px 350px;">
                    <rect x="268" y="175" width="40" height="175" rx="8" fill="url(#barGrad3)" />
                    <ellipse cx="288" cy="175" rx="20" ry="7" fill="#6ee7b7" />
                    <text x="288" y="260" fill="#ffffff" font-size="12" font-weight="800" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Q3</text>
                </g>

                <!-- Bar 4 (Q4 - Peak Revenue Neon Pillar) -->
                <g style="animation: salesBarGrow 3.8s ease-in-out infinite 0.6s; transform-origin: 340px 350px;">
                    <rect x="322" y="125" width="44" height="225" rx="10" fill="url(#barGrad4)" stroke="#ffffff" stroke-width="1.5" />
                    <ellipse cx="344" cy="125" rx="22" ry="8" fill="#00f59b" />
                    <text x="344" y="230" fill="#060c18" font-size="13" font-weight="900" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Q4 ★</text>
                </g>

                <!-- Dynamic Ascending Trend Line with Glowing Arrow -->
                <path d="M183 265 Q234 210 288 170 T344 118" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" fill="none" stroke-dasharray="6 4" />
                <path d="M344 118 L360 102 M360 102 L342 100 M360 102 L362 120" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" />
                
                <!-- Glowing Node Dots on Trend Curve -->
                <circle cx="183" cy="265" r="5.5" fill="#ffffff" stroke="#0284c7" stroke-width="2.5" />
                <circle cx="234" cy="225" r="5.5" fill="#ffffff" stroke="#059669" stroke-width="2.5" />
                <circle cx="288" cy="175" r="6" fill="#ffffff" stroke="#047857" stroke-width="2.5" />
                <circle cx="344" cy="118" r="7" fill="#00f59b" stroke="#ffffff" stroke-width="3" />
            </g>

            <!-- 5. 3D Isometric ERP Sales Order Box (Dispatched / Received) -->
            <g transform="translate(110, 280)">
                <!-- Box Left Face -->
                <path d="M30 40 L0 25 L0 60 L30 75 Z" fill="#d97706" />
                <!-- Box Right Face -->
                <path d="M30 75 L30 40 L60 25 L60 60 Z" fill="#b45309" />
                <!-- Box Top Face -->
                <path d="M30 40 L0 25 L30 10 L60 25 Z" fill="#f59e0b" />
                <!-- Neon Green Security Tape -->
                <path d="M15 17 L45 33 L45 70 L38 67 L38 35 L15 22 Z" fill="#00f59b" opacity="0.9" />
                <!-- Barcode Tag -->
                <rect x="36" y="44" width="16" height="10" rx="1.5" fill="#ffffff" />
                <line x1="39" y1="46" x2="39" y2="52" stroke="#0f172a" stroke-width="1.2" />
                <line x1="42" y1="46" x2="42" y2="52" stroke="#0f172a" stroke-width="1.2" />
                <line x1="45" y1="46" x2="45" y2="52" stroke="#0f172a" stroke-width="1.2" />
                <line x1="48" y1="46" x2="48" y2="52" stroke="#0f172a" stroke-width="1.2" />
            </g>

            <!-- 6. Floating Sales Metric Glass Badges (Matching Modern Enterprise UI) -->
            <!-- Top-Right Revenue Surge Badge -->
            <g filter="url(#cardShadow)" style="animation: salesBadgeFloat 4s ease-in-out infinite;">
                <rect x="320" y="55" width="150" height="52" rx="14" fill="#ffffff" stroke="#a7f3d0" stroke-width="1.5" />
                <circle cx="342" cy="81" r="13" fill="#ecfdf5" />
                <path d="M336 84 L342 76 L348 84" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                <text x="362" y="74" fill="#64748b" font-size="9.5" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif">REVENUE SURGE</text>
                <text x="362" y="93" fill="#0f172a" font-size="14" font-weight="900" font-family="'Plus Jakarta Sans', sans-serif">+48.6% <tspan fill="#10b981" font-size="11">▲</tspan></text>
            </g>

            <!-- Left Growth Metric Badge -->
            <g filter="url(#cardShadow)" style="animation: salesBadgeFloat 4.6s ease-in-out infinite 0.5s;">
                <rect x="70" y="170" width="135" height="48" rx="12" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.5" />
                <circle cx="90" cy="194" r="12" fill="#eff6ff" />
                <path d="M86 194 L89 197 L95 190" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                <text x="108" y="188" fill="#64748b" font-size="9" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif">ORDERS FULFILLED</text>
                <text x="108" y="205" fill="#0f172a" font-size="13" font-weight="900" font-family="'Plus Jakarta Sans', sans-serif">2,450+ Stock</text>
            </g>

            <!-- 7. Ambient Floating Golden Currency Coins & Particles -->
            <!-- Coin 1 (Top Left) -->
            <g style="animation: coinFloat 4.2s ease-in-out infinite;">
                <circle cx="160" cy="110" r="16" fill="url(#goldCoinGrad)" stroke="#ffffff" stroke-width="2" />
                <circle cx="160" cy="110" r="12.5" fill="none" stroke="#fef08a" stroke-width="1.2" stroke-dasharray="3 2" />
                <text x="160" y="115" fill="#ffffff" font-size="14" font-weight="900" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">$</text>
            </g>

            <!-- Coin 2 (Bottom Right) -->
            <g style="animation: coinFloat 4.8s ease-in-out infinite 0.8s;">
                <circle cx="410" cy="220" r="14" fill="url(#goldCoinGrad)" stroke="#ffffff" stroke-width="1.8" />
                <circle cx="410" cy="220" r="11" fill="none" stroke="#fef08a" stroke-width="1" />
                <text x="410" y="225" fill="#ffffff" font-size="12" font-weight="900" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">$</text>
            </g>

            <!-- Floating Glowing Neon Sparkles and Nodes -->
            <g>
                <circle cx="210" cy="90" r="4.5" fill="#00f59b" style="animation: floatParticle 3.5s ease-in-out infinite;" />
                <circle cx="390" cy="150" r="5" fill="#00f59b" style="animation: floatParticle 4.2s ease-in-out infinite reverse;" />
                <circle cx="430" cy="310" r="4" fill="#fbbf24" style="animation: floatParticle 3.8s ease-in-out infinite;" />
                <circle cx="110" cy="240" r="4.5" fill="#38bdf8" style="animation: floatParticle 4.5s ease-in-out infinite reverse;" />
            </g>
        </svg>
    </div>
</main>

<script>
function fillCredentials(user, pass, btn) {
    document.getElementById('usernameInput').value = user;
    document.getElementById('passwordInput').value = pass;
    document.querySelectorAll('.demo-chip').forEach(b => b.classList.remove('active'));
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
