<?php
$pageTitle = "Transaction History & Audit Report";
$pageSubtitle = "Audit trail of all inventory inward and outward stock movements";
require_once __DIR__ . '/../includes/header.php';
require_login();

$from = $_GET['from_date'] ?? date('Y-m-01');
$to = $_GET['to_date'] ?? date('Y-m-d');
$type = $_GET['transaction_type'] ?? 'ALL';

$sql = "SELECT 
            st.*,
            p.product_name,
            p.sku,
            p.unit,
            c.category_name,
            s.company_name AS supplier_name,
            s.name AS supplier_contact,
            cust.name AS customer_name,
            cust.customer_type,
            u.name AS operator_name
        FROM stock_transactions st
        JOIN products p ON p.id = st.product_id
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN suppliers s ON s.id = st.supplier_id
        LEFT JOIN customers cust ON cust.id = st.customer_id
        LEFT JOIN users u ON u.id = st.created_by
        WHERE st.transaction_date BETWEEN ? AND ?";

$params = [$from, $to];

if ($type === 'IN' || $type === 'OUT') {
    $sql .= " AND st.transaction_type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY st.transaction_date DESC, st.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Calculate summary totals
$totalInUnits = 0;
$totalOutUnits = 0;
$totalInValue = 0.0;
$totalOutValue = 0.0;

foreach ($rows as $r) {
    if ($r['transaction_type'] === 'IN') {
        $totalInUnits += (int)$r['quantity'];
        $totalInValue += (float)$r['total_price'];
    } else {
        $totalOutUnits += (int)$r['quantity'];
        $totalOutValue += (float)$r['total_price'];
    }
}

include __DIR__ . '/../includes/sidebar.php';
?>

<!-- Print-Only Branded Header -->
<div class="print-only-header">
    <h1 class="print-title">SYNAPSE-ERP - TRANSACTION AUDIT REPORT</h1>
    <div class="print-meta">
        Period: <?= date('d M Y', strtotime($from)) ?> to <?= date('d M Y', strtotime($to)) ?> | Generated: <?= date('d M Y, h:i A') ?>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="kpi-grid no-print" style="margin-bottom: 20px;">
    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Stock Received (In)</div>
            <div class="kpi-value" style="color:var(--success);"><?= number_format($totalInUnits) ?> units</div>
            <div class="kpi-subtext">Value: <?= format_currency($totalInValue) ?></div>
        </div>
        <div class="kpi-icon-box green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path></svg>
        </div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Stock Issued (Out)</div>
            <div class="kpi-value" style="color:var(--warning);"><?= number_format($totalOutUnits) ?> units</div>
            <div class="kpi-subtext">Value: <?= format_currency($totalOutValue) ?></div>
        </div>
        <div class="kpi-icon-box amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"></path><path d="m5 12 7-7 7 7"></path></svg>
        </div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-label">Total Transactions</div>
            <div class="kpi-value"><?= count($rows) ?></div>
            <div class="kpi-subtext">Within Selected Date Range</div>
        </div>
        <div class="kpi-icon-box purple">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
    </div>
</div>

<div class="card">
    <!-- Filter Form -->
    <div class="table-toolbar no-print">
        <form method="GET" style="display:flex; align-items:center; gap: 10px; flex-wrap: wrap;">
            <label class="form-label" style="margin:0; font-size:12px;">From:</label>
            <input class="form-control" type="date" name="from_date" value="<?= clean($from) ?>" style="width:auto; padding: 6px 10px;">
            
            <label class="form-label" style="margin:0; font-size:12px;">To:</label>
            <input class="form-control" type="date" name="to_date" value="<?= clean($to) ?>" style="width:auto; padding: 6px 10px;">

            <select name="transaction_type" class="form-control" style="width:auto; padding: 6px 10px;">
                <option value="ALL" <?= $type === 'ALL' ? 'selected' : '' ?>>All Movements</option>
                <option value="IN" <?= $type === 'IN' ? 'selected' : '' ?>>Stock In Only</option>
                <option value="OUT" <?= $type === 'OUT' ? 'selected' : '' ?>>Stock Out Only</option>
            </select>

            <button class="btn btn-primary btn-sm" type="submit">Filter Report</button>
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search table results..." style="width: 200px;">
        </form>

        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'stock_transactions_history.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="printPage()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect width="12" height="8" x="6" y="14"></rect></svg>
                Print
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Product & SKU</th>
                    <th>Category</th>
                    <th>Partner / Entity</th>
                    <th>Ref / Invoice #</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                    <th>Operator</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center; color: var(--text-muted); padding: 40px;">
                            No stock transactions recorded in the period from <?= clean($from) ?> to <?= clean($to) ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= clean($r['transaction_date']) ?></strong></td>
                        <td>
                            <?php if ($r['transaction_type'] === 'IN'): ?>
                                <span class="badge badge-success"><span class="badge-dot"></span> IN</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><span class="badge-dot"></span> OUT</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= clean($r['product_name']) ?></strong>
                            <div style="font-size:11px; color:var(--text-muted);"><?= clean($r['sku']) ?></div>
                        </td>
                        <td><span class="badge badge-secondary"><?= clean($r['category_name']) ?></span></td>
                        <td>
                            <?php if ($r['transaction_type'] === 'IN'): ?>
                                <?= clean($r['supplier_name'] ?: ($r['supplier_contact'] ?: 'Vendor')) ?>
                            <?php else: ?>
                                <?= clean($r['customer_name'] ?: 'Internal Issue') ?>
                            <?php endif; ?>
                        </td>
                        <td><code style="font-size:11.5px;"><?= clean($r['reference_no']) ?: '-' ?></code></td>
                        <td>
                            <strong><?= ($r['transaction_type'] === 'IN' ? '+' : '-') . $r['quantity'] ?></strong> 
                            <span style="font-size:11px; color:var(--text-muted);"><?= clean($r['unit']) ?></span>
                        </td>
                        <td><?= format_currency($r['unit_price']) ?></td>
                        <td><strong><?= format_currency($r['total_price']) ?></strong></td>
                        <td><span style="font-size:12px; color:var(--text-secondary);"><?= clean($r['operator_name'] ?: 'System') ?></span></td>
                        <td style="font-size:12px; color:var(--text-muted); max-width: 180px;"><?= clean($r['remarks']) ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
