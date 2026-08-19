<?php
$pageTitle = "Stock In (Procurement & Receiving)";
$pageSubtitle = "Receive new inventory stock from suppliers and update asset valuation";
require_once __DIR__ . '/../includes/header.php';
require_login();

$preselectedProductId = (int)($_GET['product_id'] ?? 0);

// Load active products with prices and current stock
$prodSql = "SELECT 
    p.id, 
    p.product_name, 
    p.sku, 
    p.unit, 
    p.buying_price,
    p.opening_stock 
    + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock
FROM products p
LEFT JOIN stock_transactions st ON st.product_id = p.id
WHERE p.status = 1
GROUP BY p.id
ORDER BY p.product_name ASC";
$products = $pdo->query($prodSql)->fetchAll();

// Load active suppliers
$suppliers = $pdo->query("SELECT id, name, company_name FROM suppliers WHERE status = 1 ORDER BY company_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $supplierId = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $refNo = trim($_POST['reference_no'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unitPrice = (float)($_POST['unit_price'] ?? 0.00);
    $date = $_POST['transaction_date'] ?? date('Y-m-d');
    $remarks = trim($_POST['remarks'] ?? '');
    $totalPrice = $quantity * $unitPrice;

    if ($productId <= 0 || $quantity <= 0) {
        set_flash('error', 'Please select a valid product and enter a positive quantity.');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO stock_transactions
                (product_id, transaction_type, supplier_id, reference_no, quantity, unit_price, total_price, transaction_date, remarks, created_by)
                VALUES (?, 'IN', ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $productId,
                $supplierId,
                $refNo,
                $quantity,
                $unitPrice,
                $totalPrice,
                $date,
                $remarks,
                current_user_id()
            ]);

            // Optionally update product's latest buying price if provided
            if ($unitPrice > 0) {
                $upStmt = $pdo->prepare("UPDATE products SET buying_price = ? WHERE id = ?");
                $upStmt->execute([$unitPrice, $productId]);
            }

            log_activity($pdo, 'Stock In', "Added {$quantity} units for Product ID: {$productId} (Ref: {$refNo})");
            set_flash('success', "Stock In transaction of {$quantity} units recorded successfully.");
            redirect("stock_in.php");
        } catch (PDOException $e) {
            set_flash('error', "Database error: " . $e->getMessage());
        }
    }
}

// Fetch recent 8 Stock In transactions
$recentInSql = "SELECT 
    st.*,
    p.product_name,
    p.sku,
    p.unit,
    s.company_name,
    s.name AS supplier_person
FROM stock_transactions st
JOIN products p ON p.id = st.product_id
LEFT JOIN suppliers s ON s.id = st.supplier_id
WHERE st.transaction_type = 'IN'
ORDER BY st.transaction_date DESC, st.id DESC
LIMIT 8";
$recentIn = $pdo->query($recentInSql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 900px;">
    <div class="card-header">
        <h2 class="card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"></path><path d="m19 12-7 7-7-7"></path></svg>
            Stock In Receiving Voucher
        </h2>
        <span class="badge badge-success">+ Inventory Addition</span>
    </div>
    <div class="card-body">
        <form method="POST" id="stockInForm">
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Select Product Item <span class="required">*</span></label>
                    <select class="form-control" name="product_id" id="productSelect" required autofocus>
                        <option value="">-- Choose Product --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" 
                                    data-price="<?= $p['buying_price'] ?>"
                                    data-stock="<?= $p['current_stock'] ?>"
                                    data-unit="<?= clean($p['unit']) ?>"
                                    <?= ($p['id'] == $preselectedProductId) ? 'selected' : '' ?>>
                                <?= clean($p['product_name']) ?> (SKU: <?= clean($p['sku']) ?>) — Current Stock: <?= $p['current_stock'] ?> <?= clean($p['unit']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Supplier / Vendor</label>
                    <select class="form-control" name="supplier_id">
                        <option value="">-- Select Supplier (Optional) --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= clean($s['company_name'] ?: $s['name']) ?> (<?= clean($s['name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Invoice / Purchase Order (PO) #</label>
                    <input class="form-control" type="text" name="reference_no" placeholder="e.g. INV-2026-0891">
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity Received <span class="required">*</span></label>
                    <input class="form-control" type="number" id="qtyInput" name="quantity" min="1" placeholder="Enter units count" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Cost / Purchase Price ($ / ৳)</label>
                    <input class="form-control" type="number" step="0.01" id="priceInput" name="unit_price" value="0.00">
                </div>

                <div class="form-group">
                    <label class="form-label">Total Value ($ / ৳)</label>
                    <input class="form-control" type="text" id="totalInput" readonly style="background-color: var(--bg-surface-secondary); font-weight: 700; color: var(--primary);">
                </div>

                <div class="form-group">
                    <label class="form-label">Transaction Date <span class="required">*</span></label>
                    <input class="form-control" type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Remarks / Batch / Storage Bin Notes</label>
                <input class="form-control" type="text" name="remarks" placeholder="Optional notes, shipment bill number, bin location...">
            </div>

            <div style="display:flex; gap: 12px; margin-top: 20px;">
                <button class="btn btn-primary" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Confirm Stock In
                </button>
                <a href="<?= base_url('dashboard.php') ?>" class="btn btn-secondary">Dashboard</a>
            </div>
        </form>
    </div>
</div>

<!-- Recent Stock In Log Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Stock In Transactions</h2>
        <a href="<?= base_url('reports/date_wise.php?type=IN') ?>" class="btn btn-secondary btn-sm">Full Transaction Log</a>
    </div>
    <div class="table-responsive">
        <table class="corporate-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product Item</th>
                    <th>Supplier / Vendor</th>
                    <th>Invoice / Ref #</th>
                    <th>Quantity</th>
                    <th>Unit Cost</th>
                    <th>Total Valuation</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentIn)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--text-muted); padding: 30px;">
                            No stock-in transactions recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentIn as $r): ?>
                    <tr>
                        <td><?= clean($r['transaction_date']) ?></td>
                        <td>
                            <strong><?= clean($r['product_name']) ?></strong>
                            <div style="font-size:11px; color:var(--text-muted);"><?= clean($r['sku']) ?></div>
                        </td>
                        <td><?= clean($r['company_name'] ?: ($r['supplier_person'] ?: 'General Vendor')) ?></td>
                        <td><?= clean($r['reference_no']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><span class="badge badge-success">+ <?= $r['quantity'] ?> <?= clean($r['unit']) ?></span></td>
                        <td><?= format_currency($r['unit_price']) ?></td>
                        <td><strong><?= format_currency($r['total_price']) ?></strong></td>
                        <td style="color:var(--text-secondary); font-size:12px;"><?= clean($r['remarks']) ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const productSelect = document.getElementById('productSelect');
    const qtyInput = document.getElementById('qtyInput');
    const priceInput = document.getElementById('priceInput');
    const totalInput = document.getElementById('totalInput');

    function calculateTotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = '$ ' + (qty * price).toFixed(2);
    }

    productSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt && selectedOpt.dataset.price) {
            priceInput.value = parseFloat(selectedOpt.dataset.price).toFixed(2);
            calculateTotal();
        }
    });

    qtyInput.addEventListener('input', calculateTotal);
    priceInput.addEventListener('input', calculateTotal);

    // Initial calculation if preselected
    if (productSelect.selectedIndex > 0) {
        const selectedOpt = productSelect.options[productSelect.selectedIndex];
        if (selectedOpt && selectedOpt.dataset.price) {
            priceInput.value = parseFloat(selectedOpt.dataset.price).toFixed(2);
        }
        calculateTotal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
