<?php
$pageTitle = "Enterprise Dashboard";
$pageSubtitle = "Real-time inventory intelligence & transaction metrics";
require_once __DIR__ . '/includes/header.php';
require_login();

// 1. Core Summary Metrics
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories WHERE status=1")->fetchColumn();
$totalSuppliers = (int)$pdo->query("SELECT COUNT(*) FROM suppliers WHERE status=1")->fetchColumn();

// 2. Dynamic Stock Calculations & Valuation
$stockSummarySql = "SELECT 
    p.id,
    p.buying_price,
    p.selling_price,
    p.alert_quantity,
    p.opening_stock 
    + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock
FROM products p
LEFT JOIN stock_transactions st ON st.product_id = p.id
WHERE p.status = 1
GROUP BY p.id, p.buying_price, p.selling_price, p.alert_quantity, p.opening_stock";

$stockRows = $pdo->query($stockSummarySql)->fetchAll();

$totalStockUnits = 0;
$totalAssetValue = 0.0;
$totalLowStock = 0;

foreach ($stockRows as $row) {
    $cStock = (int)$row['current_stock'];
    $totalStockUnits += $cStock;
    $totalAssetValue += ($cStock * (float)$row['buying_price']);
    if ($cStock <= (int)$row['alert_quantity']) {
        $totalLowStock++;
    }
}

// 3. Transactions Total Volume
$totalInQty = (int)$pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_transactions WHERE transaction_type='IN'")->fetchColumn();
$totalOutQty = (int)$pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_transactions WHERE transaction_type='OUT'")->fetchColumn();

// 4. Monthly Stock Flow (Last 6 Months) for Chart
$chartSql = "SELECT 
    DATE_FORMAT(transaction_date, '%b %Y') AS month_label,
    DATE_FORMAT(transaction_date, '%Y-%m') AS ym,
    SUM(CASE WHEN transaction_type = 'IN' THEN quantity ELSE 0 END) AS in_qty,
    SUM(CASE WHEN transaction_type = 'OUT' THEN quantity ELSE 0 END) AS out_qty
FROM stock_transactions
WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY ym, month_label
ORDER BY ym ASC";
$chartData = $pdo->query($chartSql)->fetchAll();

$months = [];
$inSeries = [];
$outSeries = [];
foreach ($chartData as $cd) {
    $months[] = $cd['month_label'];
    $inSeries[] = (int)$cd['in_qty'];
    $outSeries[] = (int)$cd['out_qty'];
}

// Fallback dummy labels if no transactions yet
if (empty($months)) {
    $months = [date('M Y')];
    $inSeries = [0];
    $outSeries = [0];
}

// 5. Category Distribution for Doughnut Chart
$catChartSql = "SELECT c.category_name, COUNT(p.id) as count
FROM categories c
LEFT JOIN products p ON p.category_id = c.id
WHERE c.status = 1
GROUP BY c.id, c.category_name
ORDER BY count DESC
LIMIT 5";
$catChartData = $pdo->query($catChartSql)->fetchAll();

$catLabels = [];
$catCounts = [];
foreach ($catChartData as $c) {
    $catLabels[] = $c['category_name'];
    $catCounts[] = (int)$c['count'];
}

// 6. Recent Transactions
$recentSql = "SELECT 
    st.*,
    p.product_name,
    p.sku,
    c.category_name,
    s.company_name AS supplier_name,
    cust.name AS customer_name
FROM stock_transactions st
JOIN products p ON p.id = st.product_id
JOIN categories c ON c.id = p.category_id
LEFT JOIN suppliers s ON s.id = st.supplier_id
LEFT JOIN customers cust ON cust.id = st.customer_id
ORDER BY st.transaction_date DESC, st.id DESC
LIMIT 7";
$recentTransactions = $pdo->query($recentSql)->fetchAll();

// 7. Low Stock Critical Items
$lowStockSql = "SELECT 
    p.id,
    p.product_name,
    p.sku,
    p.unit,
    c.category_name,
    p.alert_quantity,
    p.opening_stock 
    + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock
FROM products p
JOIN categories c ON c.id = p.category_id
LEFT JOIN stock_transactions st ON st.product_id = p.id
WHERE p.status = 1
GROUP BY p.id, p.product_name, p.sku, p.unit, c.category_name, p.alert_quantity, p.opening_stock
HAVING current_stock <= p.alert_quantity
ORDER BY current_stock ASC
LIMIT 5";
$criticalStockItems = $pdo->query($lowStockSql)->fetchAll();

include __DIR__ . '/includes/sidebar.php';
?>

<!-- KPI Metric Cards Grid -->
<div class="kpi-grid">
    <!-- Asset Valuation -->
    <div class="kpi-card accent-green">
        <div>
            <div class="kpi-label">Inventory Valuation</div>
            <div class="kpi-value"><?= format_currency($totalAssetValue) ?></div>
            <div class="kpi-trend positive">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                <span>Live Valuation</span>
            </div>
        </div>
        <div class="kpi-icon-box green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>

    <!-- Total In-Stock Units -->
    <div class="kpi-card accent-blue">
        <div>
            <div class="kpi-label">Total Stock Units</div>
            <div class="kpi-value"><?= number_format($totalStockUnits) ?></div>
            <div class="kpi-trend neutral">
                <span><?= $totalProducts ?> Active Products</span>
            </div>
        </div>
        <div class="kpi-icon-box blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
        </div>
    </div>

    <!-- Stock In Total -->
    <div class="kpi-card accent-purple">
        <div>
            <div class="kpi-label">Total Received (In)</div>
            <div class="kpi-value"><?= number_format($totalInQty) ?></div>
            <div class="kpi-trend positive">
                <span><?= $totalSuppliers ?> Active Suppliers</span>
            </div>
        </div>
        <div class="kpi-icon-box purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path></svg>
        </div>
    </div>

    <!-- Stock Out Total -->
    <div class="kpi-card accent-amber">
        <div>
            <div class="kpi-label">Total Dispatched (Out)</div>
            <div class="kpi-value"><?= number_format($totalOutQty) ?></div>
            <div class="kpi-trend neutral">
                <span>Orders &amp; Requisitions</span>
            </div>
        </div>
        <div class="kpi-icon-box amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"></path><path d="m5 12 7-7 7 7"></path></svg>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="kpi-card accent-red">
        <div>
            <div class="kpi-label">Low Stock Alerts</div>
            <div class="kpi-value" style="<?= $totalLowStock > 0 ? 'color:#ef4444;' : '' ?>"><?= $totalLowStock ?></div>
            <div class="kpi-trend <?= $totalLowStock > 0 ? 'negative' : 'positive' ?>">
                <span><?= $totalLowStock > 0 ? 'Action Needed' : '● Optimal Health' ?></span>
            </div>
        </div>
        <div class="kpi-icon-box red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
    </div>
</div>

<!-- Analytics Charts Section -->
<div class="dashboard-grid-2">
    <!-- Monthly Movement Bar/Line Chart -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Monthly Stock Movement (In vs Out)
            </h2>
            <a href="<?= base_url('reports/date_wise.php') ?>" class="btn btn-secondary btn-sm">Full Report</a>
        </div>
        <div class="card-body">
            <div style="height: 280px; position: relative;">
                <canvas id="stockMovementChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution Doughnut Chart -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                Category Distribution
            </h2>
            <a href="<?= base_url('categories/index.php') ?>" class="btn btn-secondary btn-sm">Manage</a>
        </div>
        <div class="card-body">
            <div style="height: 280px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Low Stock Alerts Grid -->
<div class="dashboard-grid-2">
    <!-- Recent Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                Recent Stock Transactions
            </h2>
            <div style="display:flex; gap: 8px;">
                <a href="<?= base_url('stock/stock_in.php') ?>" class="btn btn-primary btn-sm">+ Stock In</a>
                <a href="<?= base_url('stock/stock_out.php') ?>" class="btn btn-secondary btn-sm">- Stock Out</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="corporate-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Partner / Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTransactions)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color: var(--text-muted); padding: 30px;">
                                No stock transactions recorded yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td><?= clean($tx['transaction_date']) ?></td>
                                <td>
                                    <strong><?= clean($tx['product_name']) ?></strong>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?= clean($tx['sku']) ?></div>
                                </td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'IN'): ?>
                                        <span class="badge badge-success"><span class="badge-dot"></span> Stock In</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><span class="badge-dot"></span> Stock Out</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= $tx['quantity'] ?></strong></td>
                                <td>
                                    <?php if ($tx['transaction_type'] === 'IN'): ?>
                                        <?= clean($tx['supplier_name'] ?: 'General Vendor') ?>
                                    <?php else: ?>
                                        <?= clean($tx['customer_name'] ?: 'Internal Issue') ?>
                                    <?php endif; ?>
                                    <?php if (!empty($tx['reference_no'])): ?>
                                        <span style="font-size:11px; color:var(--text-muted);"> (Ref: <?= clean($tx['reference_no']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Critical Low Stock Alerts Card -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                Reorder Warnings
            </h2>
            <a href="<?= base_url('reports/low_stock.php') ?>" class="btn btn-danger btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="corporate-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Alert Limit</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($criticalStockItems)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color: var(--success); padding: 30px;">
                                ✓ All product inventory levels are optimal.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($criticalStockItems as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= clean($item['product_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= clean($item['category_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $item['current_stock'] <= 0 ? 'badge-danger' : 'badge-warning' ?>">
                                        <span class="badge-dot"></span> <?= $item['current_stock'] ?> <?= clean($item['unit']) ?>
                                    </span>
                                </td>
                                <td><?= $item['alert_quantity'] ?> <?= clean($item['unit']) ?></td>
                                <td class="no-print">
                                    <a href="<?= base_url('stock/stock_in.php?product_id=' . $item['id']) ?>" class="btn btn-primary btn-sm">Restock</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Stock Movement Chart
    const ctxMovement = document.getElementById('stockMovementChart');
    if (ctxMovement) {
        new Chart(ctxMovement, {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [
                    {
                        label: 'Stock In (Received)',
                        data: <?= json_encode($inSeries) ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barPercentage: 0.6
                    },
                    {
                        label: 'Stock Out (Dispatched)',
                        data: <?= json_encode($outSeries) ?>,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { family: 'Inter', size: 12 } }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });
    }

    // 2. Category Doughnut Chart
    const ctxCategory = document.getElementById('categoryChart');
    if (ctxCategory) {
        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catCounts) ?>,
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#0ea5e9'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { family: 'Inter', size: 11 } }
                    }
                },
                cutout: '70%'
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
