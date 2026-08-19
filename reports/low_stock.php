<?php
$pageTitle = "Low Stock & Reorder Alert Center";
$pageSubtitle = "Critical inventory replenishment dashboard and stock deficit monitoring";
require_once __DIR__ . '/../includes/header.php';
require_login();

$sql = "SELECT 
            p.id,
            p.product_name,
            p.sku,
            p.unit,
            p.buying_price,
            p.alert_quantity,
            c.category_name,
            p.opening_stock
            + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0)
            - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS current_stock
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN stock_transactions st ON st.product_id = p.id
        WHERE p.status = 1
        GROUP BY p.id
        HAVING current_stock <= p.alert_quantity
        ORDER BY current_stock ASC, p.alert_quantity DESC";

$lowStockItems = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="table-toolbar">
        <div style="display:flex; align-items:center; gap: 12px; flex-wrap: wrap;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search low stock items...">
            <span style="font-size: 13px; font-weight: 600; color: var(--danger);">
                <?= count($lowStockItems) ?> product(s) require reordering
            </span>
        </div>
        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'reorder_alerts_list.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="printPage()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect width="12" height="8" x="6" y="14"></rect></svg>
                Print Reorder Sheet
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>SKU Code</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Alert Limit</th>
                    <th>Deficit Quantity</th>
                    <th>Unit Cost</th>
                    <th>Status</th>
                    <th class="no-print no-export" style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lowStockItems)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; color: var(--success); padding: 50px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                            <strong>All stock levels are optimal!</strong>
                            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">There are currently no products at or below their reorder threshold limits.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($lowStockItems as $item): 
                        $cStock = (int)$item['current_stock'];
                        $alertLimit = (int)$item['alert_quantity'];
                        $deficit = max(0, $alertLimit - $cStock);
                    ?>
                    <tr>
                        <td><code><?= clean($item['sku']) ?></code></td>
                        <td><strong><?= clean($item['product_name']) ?></strong></td>
                        <td><span class="badge badge-secondary"><?= clean($item['category_name']) ?></span></td>
                        <td>
                            <span class="badge <?= $cStock <= 0 ? 'badge-danger' : 'badge-warning' ?>">
                                <span class="badge-dot"></span> <strong><?= $cStock ?></strong> <?= clean($item['unit']) ?>
                            </span>
                        </td>
                        <td><?= $alertLimit ?> <?= clean($item['unit']) ?></td>
                        <td>
                            <strong style="color:var(--danger);">+<?= $deficit ?> <?= clean($item['unit']) ?> needed</strong>
                        </td>
                        <td><?= format_currency($item['buying_price']) ?></td>
                        <td>
                            <?php if ($cStock <= 0): ?>
                                <span class="badge badge-danger">OUT OF STOCK</span>
                            <?php else: ?>
                                <span class="badge badge-warning">LOW STOCK</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-print no-export" style="text-align: right;">
                            <a href="<?= base_url('stock/stock_in.php?product_id=' . $item['id']) ?>" class="btn btn-primary btn-sm">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                Restock Now
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
