<?php
$currentUri = $_SERVER['REQUEST_URI'];
$userName = $_SESSION['name'] ?? 'User';
$userRole = ucfirst(current_user_role());
$userInitial = strtoupper(substr($userName, 0, 1));
$notifications = get_system_notifications($pdo);
?>
<!-- Sidebar Navigation -->
<aside class="app-sidebar no-print">
    <div class="sidebar-header">
        <div class="brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7.5 4.27 9 5.15"></path>
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                <path d="m3.3 7 8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        </div>
        <div>
            <div class="brand-title">Synapse-ERP</div>
            <div class="brand-subtitle">Next-Gen Intelligence</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Overview</div>
        <a href="<?= base_url('dashboard.php') ?>" class="nav-item <?= strpos($currentUri, 'dashboard.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
            Dashboard
        </a>

        <div class="nav-section-title">Inventory Master</div>
        <?php if (is_manager()): ?>
        <a href="<?= base_url('categories/index.php') ?>" class="nav-item <?= strpos($currentUri, '/categories/') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path></svg>
            Categories
        </a>
        <?php endif; ?>

        <a href="<?= base_url('products/index.php') ?>" class="nav-item <?= strpos($currentUri, '/products/') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
            Products Catalog
        </a>
        <a href="<?= base_url('reports/low_stock.php') ?>" class="nav-item <?= strpos($currentUri, 'low_stock.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            Low Stock Alerts
            <?php if ($headerLowStockCount > 0): ?>
                <span class="nav-badge"><?= $headerLowStockCount ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">Stock Operations</div>
        <a href="<?= base_url('stock/stock_in.php') ?>" class="nav-item <?= strpos($currentUri, 'stock_in.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path></svg>
            Stock In (Receive)
        </a>
        <a href="<?= base_url('stock/stock_out.php') ?>" class="nav-item <?= strpos($currentUri, 'stock_out.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 19V5"></path><path d="m5 12 7-7 7 7"></path></svg>
            Stock Out (Dispatch)
        </a>

        <?php if (is_manager()): ?>
        <div class="nav-section-title">Business Partners</div>
        <a href="<?= base_url('suppliers/index.php') ?>" class="nav-item <?= strpos($currentUri, '/suppliers/') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            Suppliers (Vendors)
        </a>
        <a href="<?= base_url('customers/index.php') ?>" class="nav-item <?= strpos($currentUri, '/customers/') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Customers &amp; Depts
        </a>

        <div class="nav-section-title">Reports &amp; Intelligence</div>
        <a href="<?= base_url('reports/current_stock.php') ?>" class="nav-item <?= strpos($currentUri, 'current_stock.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3v18h18"></path><path d="m19 9-5 5-4-4-3 3"></path></svg>
            Stock Valuation Report
        </a>
        <a href="<?= base_url('reports/date_wise.php') ?>" class="nav-item <?= strpos($currentUri, 'date_wise.php') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Transaction Audit Logs
        </a>
        <?php endif; ?>

        <?php if (can_manage_users()): ?>
        <div class="nav-section-title">Administration</div>
        <a href="<?= base_url('users/index.php') ?>" class="nav-item <?= strpos($currentUri, '/users/') !== false ? 'active' : '' ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            User Management
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-avatar"><?= $userInitial ?></div>
        <div class="user-info">
            <div class="user-name"><?= clean($userName) ?></div>
            <span class="user-role-badge"><?= clean($userRole) ?></span>
        </div>
        <a href="<?= base_url('auth/logout.php') ?>" title="Logout" style="color: #94a3b8; display:flex; align-items:center; padding: 6px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </a>
    </div>
</aside>

<!-- Main App Section -->
<div class="app-main">
    <!-- Top Sticky Navigation Bar -->
    <header class="app-topbar no-print">
        <div class="topbar-left">
            <button class="menu-toggle-btn" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
            </button>
            <div>
                <h1 class="page-headline"><?= isset($pageTitle) ? clean($pageTitle) : "Inventory Operations" ?></h1>
                <?php if (isset($pageSubtitle)): ?>
                    <div class="page-subtitle"><?= clean($pageSubtitle) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="topbar-right">
            <!-- Notification Center Drawer Toggle -->
            <div class="notification-dropdown-wrapper" style="position: relative;">
                <button type="button" class="topbar-btn" onclick="toggleNotificationDrawer()" title="System Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
                    <?php if ($headerLowStockCount > 0): ?>
                        <span class="btn-badge-dot"><?= $headerLowStockCount ?></span>
                    <?php endif; ?>
                </button>

                <!-- Notifications Dropdown Menu -->
                <div class="notification-drawer" id="notificationDrawer" style="display: none;">
                    <div class="notif-header">
                        <strong>Live Notifications &amp; Alerts</strong>
                        <span class="badge badge-primary" style="font-size:10.5px;"><?= count($notifications) ?> alerts</span>
                    </div>
                    <div class="notif-body">
                        <?php if (empty($notifications)): ?>
                            <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">
                                No new notifications.
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <a href="<?= $n['link'] ?>" class="notif-item">
                                    <div class="notif-icon <?= $n['type'] ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-title"><?= clean($n['title']) ?></div>
                                        <div class="notif-msg"><?= clean($n['message']) ?></div>
                                        <div class="notif-time"><?= clean($n['time']) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Add Shortcut (Only if manager or admin) -->
            <?php if (is_manager()): ?>
            <a href="<?= base_url('products/create.php') ?>" class="btn btn-primary btn-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Product</span>
            </a>
            <?php endif; ?>
        </div>
    </header>

    <main class="app-content">
        <!-- Render Flash Messages -->
        <?= render_flash() ?>
