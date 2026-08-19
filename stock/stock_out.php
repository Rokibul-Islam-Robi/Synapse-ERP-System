<?php
$pageTitle = "Stock Out (Dispatch & Issue)";
$pageSubtitle = "Issue inventory items for corporate orders, internal departments, or customer deliveries";
require_once __DIR__ . '/../includes/header.php';
require_login();

// Load active products with prices and current stock
$prodSql = "SELECT 
    p.id, 
    p.product_name, 
    p.sku, 
    p.unit, 
    p.selling_price,
    p.opening_stock 
    + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END), 0) AS current_stock
FROM products p
LEFT JOIN stock_transactions st ON st.product_id = p.id
WHERE p.status = 1
GROUP BY p.id
ORDER BY p.product_name ASC";
$products = $pdo->query($prodSql)->fetchAll();

// Load active customers / departments
$customers = $pdo->query("SELECT id, name, customer_type FROM customers WHERE status = 1 ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $customerId = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
    $refNo = trim($_POST['reference_no'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $unitPrice = (float)($_POST['unit_price'] ?? 0.00);
    $date = $_POST['transaction_date'] ?? date('Y-m-d');
    $remarks = trim($_POST['remarks'] ?? '');
    $totalPrice = $quantity * $unitPrice;

    $availableStock = get_product_stock($pdo, $productId);

    if ($productId <= 0 || $quantity <= 0) {
        set_flash('error', 'Please select a valid product and enter a valid quantity.');
    } elseif ($quantity > $availableStock) {
        set_flash('error', "Insufficient stock: Requested {$quantity} units, but only {$availableStock} units are currently available in the warehouse.");
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO stock_transactions
                (product_id, transaction_type, customer_id, reference_no, quantity, unit_price, total_price, transaction_date, remarks, created_by)
                VALUES (?, 'OUT', ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $productId,
                $customerId,
                $refNo,
                $quantity,
                $unitPrice,
                $totalPrice,
                $date,
                $remarks,
                current_user_id()
            ]);

            log_activity($pdo, 'Stock Out', "Dispatched {$quantity} units for Product ID: {$productId} (Challan: {$refNo})");
            set_flash('success', "Stock Out transaction of {$quantity} units dispatched successfully.");
            redirect("stock_out.php");
        } catch (PDOException $e) {
            set_flash('error', "Database error: " . $e->getMessage());
        }
    }
}

// Fetch recent 8 Stock Out transactions
$recentOutSql = "SELECT 
    st.*,
    p.product_name,
    p.sku,
    p.unit,
    c.name AS customer_name,
    c.customer_type
FROM stock_transactions st
JOIN products p ON p.id = st.product_id
LEFT JOIN customers c ON c.id = st.customer_id
WHERE st.transaction_type = 'OUT'
ORDER BY st.transaction_date DESC, st.id DESC
LIMIT 8";
$recentOut = $pdo->query($recentOutSql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 900px;">
    <div class="card-header">
        <h2 class="card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"></path><path d="m5 12 7-7 7 7"></path></svg>
            Stock Out Dispatch Voucher
        </h2>
        <span class="badge badge-warning">- Inventory Dispatch</span>
    </div>
    <div class="card-body">
        <form method="POST" id="stockOutForm">
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Select Product Item <span class="required">*</span></label>
                    <select class="form-control" name="product_id" id="productSelect" required autofocus>
                        <option value="">-- Choose Product to Dispatch --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" 
                                    data-price="<?= $p['selling_price'] ?>"
                                    data-stock="<?= $p['current_stock'] ?>"
                                    data-unit="<?= clean($p['unit']) ?>"
                                    <?= ($p['current_stock'] <= 0) ? 'disabled style="color:#cbd5e1;"' : '' ?>>
                                <?= clean($p['product_name']) ?> (SKU: <?= clean($p['sku']) ?>) — Available: <?= $p['current_stock'] ?> <?= clean($p['unit']) ?>
                                <?= ($p['current_stock'] <= 0) ? ' [OUT OF STOCK]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer / Department Recipient</label>
                    <select class="form-control" name="customer_id">
                        <option value="">-- Select Recipient (Optional) --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= clean($c['name']) ?> (<?= clean($c['customer_type']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Challan / Delivery Note / Ref #</label>
                    <input class="form-control" type="text" name="reference_no" placeholder="e.g. CHN-2026-0442">
                </div>

                <div class="form-group">
                    <label class="form-label">Dispatch Quantity <span class="required">*</span></label>
                    <input class="form-control" type="number" id="qtyInput" name="quantity" min="1" placeholder="Units to issue" required>
                    <div class="form-help" id="stockLimitHint">Select a product to view maximum available stock.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Selling Price ($ / ৳)</label>
                    <input class="form-control" type="number" step="0.01" id="priceInput" name="unit_price" value="0.00">
                </div>

                <div class="form-group">
                    <label class="form-label">Total Dispatch Value ($ / ৳)</label>
                    <input class="form-control" type="text" id="totalInput" readonly style="background-color: var(--bg-surface-secondary); font-weight: 700; color: var(--primary);">
                </div>

                <div class="form-group">
                    <label class="form-label">Dispatch Date <span class="required">*</span></label>
                    <input class="form-control" type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Purpose / Authorization / Remarks</label>
                <input class="form-control" type="text" name="remarks" placeholder="Department requisitions, client PO number, driver details...">
            </div>

            <div style="display:flex; gap: 12px; margin-top: 20px;">
                <button class="btn btn-primary" type="submit">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Confirm Stock Out
                </button>
                <a href="<?= base_url('dashboard.php') ?>" class="btn btn-secondary">Dashboard</a>
            </div>
        </form>
    </div>
</div>

<!-- Recent Stock Out Log Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Recent Stock Out Dispatches</h2>
        <a href="<?= base_url('reports/date_wise.php?type=OUT') ?>" class="btn btn-secondary btn-sm">Full Transaction Log</a>
    </div>
    <div class="table-responsive">
        <table class="corporate-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product Item</th>
                    <th>Recipient / Department</th>
                    <th>Challan / Ref #</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Value</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOut)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--text-muted); padding: 30px;">
                            No stock-out dispatches recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOut as $r): ?>
                    <tr>
                        <td><?= clean($r['transaction_date']) ?></td>
                        <td>
                            <strong><?= clean($r['product_name']) ?></strong>
                            <div style="font-size:11px; color:var(--text-muted);"><?= clean($r['sku']) ?></div>
                        </td>
                        <td><?= clean($r['customer_name'] ?: 'Internal General Issue') ?></td>
                        <td><?= clean($r['reference_no']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><span class="badge badge-warning">- <?= $r['quantity'] ?> <?= clean($r['unit']) ?></span></td>
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
    const stockLimitHint = document.getElementById('stockLimitHint');

    function calculateTotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = '$ ' + (qty * price).toFixed(2);
    }

    productSelect.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            const stock = parseInt(selectedOpt.dataset.stock) || 0;
            const unit = selectedOpt.dataset.unit || 'units';
            const price = parseFloat(selectedOpt.dataset.price) || 0;

            priceInput.value = price.toFixed(2);
            qtyInput.max = stock;
            stockLimitHint.innerHTML = `Available stock to issue: <strong>${stock} ${unit}</strong>`;
            if (stock <= 0) {
                stockLimitHint.innerHTML = `<span style="color:var(--danger); font-weight:600;">⚠ Item is out of stock. Cannot dispatch.</span>`;
            }
            calculateTotal();
        } else {
            stockLimitHint.textContent = 'Select a product to view maximum available stock.';
            qtyInput.removeAttribute('max');
        }
    });

    qtyInput.addEventListener('input', calculateTotal);
    priceInput.addEventListener('input', calculateTotal);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
