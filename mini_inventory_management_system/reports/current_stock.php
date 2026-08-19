<?php
$pageTitle = "Current Stock Valuation Report";
$pageSubtitle = "Real-time warehouse stock balance, asset valuation, and health indicators";
require_once __DIR__ . '/../includes/header.php';
require_login();

$selectedCategory = (int)($_GET['category_id'] ?? 0);
$selectedStatus = $_GET['status_filter'] ?? 'ALL';

$sql = "SELECT 
            p.id,
            p.product_name,
            p.sku,
            p.unit,
            p.buying_price,
            p.selling_price,
            p.opening_stock,
            p.alert_quantity,
            c.category_name,
            COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0) AS stock_in,
            COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS stock_out,
            p.opening_stock
            + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0)
            - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS current_stock
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN stock_transactions st ON st.product_id = p.id
        WHERE p.status = 1";

$params = [];
if ($selectedCategory > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $selectedCategory;
}

$sql .= " GROUP BY p.id ORDER BY c.category_name ASC, p.product_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allRows = $stmt->fetchAll();

// Filter rows by stock status if requested
$rows = [];
$grandTotalUnits = 0;
$grandTotalAssetValue = 0.0;
$grandTotalSalesValue = 0.0;
$totalLowStockCount = 0;

foreach ($allRows as $r) {
    $cStock = (int)$r['current_stock'];
    $alertQty = (int)$r['alert_quantity'];
    
    if ($selectedStatus === 'LOW' && $cStock > $alertQty) continue;
    if ($selectedStatus === 'OUT' && $cStock > 0) continue;
    if ($selectedStatus === 'HEALTHY' && $cStock <= $alertQty) continue;

    $rows[] = $r;
    $grandTotalUnits += $cStock;
    $grandTotalAssetValue += ($cStock * (float)$r['buying_price']);
    $grandTotalSalesValue += ($cStock * (float)$r['selling_price']);
    if ($cStock <= $alertQty) {
        $totalLowStockCount++;
    }
}

$categories = $pdo->query("SELECT id, category_name FROM categories WHERE status=1 ORDER BY category_name ASC")->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Print-Only Branded Header -->
<div class="print-only-header">
    <h1 class="print-title">SYNAPSE-ERP - WAREHOUSE STOCK REPORT</h1>
    <div class="print-meta">
        Generated on: <?= date('d M Y, h:i A') ?> | Prepared by: <?= clean($_SESSION['name']) ?>
    </div>
</div>

<!-- Summary Metrics Bar -->
<div class="kpi-grid no-print" style="margin-bottom: 20px;">
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Stock Balance</div>
            <div class="kpi-value"><?= number_format($grandTotalUnits) ?></div>
            <div class="kpi-subtext"><?= count($rows) ?> Listed Products</div>
        </div>
        <div class="kpi-icon-box blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path></svg>
        </div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-label">Inventory Asset Value</div>
            <div class="kpi-value"><?= format_currency($grandTotalAssetValue) ?></div>
            <div class="kpi-subtext">Calculated at Cost Price</div>
        </div>
        <div class="kpi-icon-box green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-label">Estimated Sales Value</div>
            <div class="kpi-value"><?= format_currency($grandTotalSalesValue) ?></div>
            <div class="kpi-subtext">Potential Revenue Value</div>
        </div>
        <div class="kpi-icon-box purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
        </div>
    </div>
</div>

<div class="card">
    <!-- Filter Toolbar -->
    <div class="table-toolbar no-print">
        <form method="GET" style="display:flex; align-items:center; gap: 10px; flex-wrap: wrap;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Quick filter table...">
            
            <select name="category_id" class="form-control" style="width: auto; padding: 7px 12px;" onchange="this.form.submit()">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                        <?= clean($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status_filter" class="form-control" style="width: auto; padding: 7px 12px;" onchange="this.form.submit()">
                <option value="ALL" <?= $selectedStatus === 'ALL' ? 'selected' : '' ?>>All Stock Health</option>
                <option value="LOW" <?= $selectedStatus === 'LOW' ? 'selected' : '' ?>>Low Stock Only (≤ Alert)</option>
                <option value="OUT" <?= $selectedStatus === 'OUT' ? 'selected' : '' ?>>Out of Stock (0)</option>
                <option value="HEALTHY" <?= $selectedStatus === 'HEALTHY' ? 'selected' : '' ?>>Healthy Stock</option>
            </select>

            <?php if ($selectedCategory > 0 || $selectedStatus !== 'ALL'): ?>
                <a href="current_stock.php" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </form>

        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'stock_valuation_report.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="printPage()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect width="12" height="8" x="6" y="14"></rect></svg>
                Print Report
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Opening</th>
                    <th>Received (In)</th>
                    <th>Dispatched (Out)</th>
                    <th>Current Stock</th>
                    <th>Cost Price</th>
                    <th>Total Asset Value</th>
                    <th>Health</th>
                    <th class="no-print no-export" style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center; color: var(--text-muted); padding: 40px;">
                            No stock records found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): 
                        $cStock = (int)$r['current_stock'];
                        $alertQty = (int)$r['alert_quantity'];
                        $itemAssetVal = $cStock * (float)$r['buying_price'];
                    ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= clean($r['category_name']) ?></span></td>
                        <td><strong><?= clean($r['product_name']) ?></strong></td>
                        <td><code style="font-size:11.5px;"><?= clean($r['sku']) ?></code></td>
                        <td><?= $r['opening_stock'] ?></td>
                        <td><span style="color:var(--success); font-weight:600;">+<?= $r['stock_in'] ?></span></td>
                        <td><span style="color:var(--warning); font-weight:600;">-<?= $r['stock_out'] ?></span></td>
                        <td>
                            <strong><?= $cStock ?></strong> <span style="font-size:11px; color:var(--text-muted);"><?= clean($r['unit']) ?></span>
                        </td>
                        <td><?= format_currency($r['buying_price']) ?></td>
                        <td><strong><?= format_currency($itemAssetVal) ?></strong></td>
                        <td>
                            <?php if ($cStock <= 0): ?>
                                <span class="badge badge-danger"><span class="badge-dot"></span> Out of Stock</span>
                            <?php elseif ($cStock <= $alertQty): ?>
                                <span class="badge badge-warning"><span class="badge-dot"></span> Low Stock</span>
                            <?php else: ?>
                                <span class="badge badge-success"><span class="badge-dot"></span> Optimal</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-print no-export" style="text-align: right;">
                            <a href="<?= base_url('stock/stock_in.php?product_id=' . $r['id']) ?>" class="btn btn-secondary btn-sm" title="Receive Stock">
                                + In
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: var(--bg-surface-secondary); font-weight: 700;">
                    <td colspan="6" style="text-align: right;">GRAND TOTAL SUMMARY:</td>
                    <td><strong><?= number_format($grandTotalUnits) ?> units</strong></td>
                    <td>-</td>
                    <td><strong><?= format_currency($grandTotalAssetValue) ?></strong></td>
                    <td colspan="2">-</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
