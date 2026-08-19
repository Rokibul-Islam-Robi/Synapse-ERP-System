<?php
$pageTitle = "Add New Product";
$pageSubtitle = "Register master SKU item with cost valuation and reorder levels";
require_once __DIR__ . '/../includes/header.php';
require_login();

$categories = $pdo->query("SELECT * FROM categories WHERE status=1 ORDER BY category_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['product_name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $unit = trim($_POST['unit'] ?? 'pcs');
    $openingStock = (int)($_POST['opening_stock'] ?? 0);
    $buyingPrice = (float)($_POST['buying_price'] ?? 0.00);
    $sellingPrice = (float)($_POST['selling_price'] ?? 0.00);
    $alertQty = (int)($_POST['alert_quantity'] ?? 5);
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name) || empty($sku) || $categoryId <= 0) {
        set_flash('error', 'Please fill in all required fields (Product Name, SKU, and Category).');
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products 
                (category_id, product_name, sku, unit, opening_stock, buying_price, selling_price, alert_quantity, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $categoryId,
                $name,
                $sku,
                $unit,
                $openingStock,
                $buyingPrice,
                $sellingPrice,
                $alertQty,
                $description,
                $status
            ]);
            log_activity($pdo, 'Created Product', "Product: $name ($sku)");
            set_flash('success', "Product '{$name}' created successfully.");
            redirect("index.php");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                set_flash('error', "SKU '{$sku}' already exists. Please choose a unique SKU code.");
            } else {
                set_flash('error', "Database error: " . $e->getMessage());
            }
        }
    }
}
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 860px;">
    <div class="card-header">
        <h2 class="card-title">New Product Specifications</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Products</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select class="form-control" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= clean($cat['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="product_name" placeholder="e.g. Wireless Ergonomic Mouse" required>
                </div>

                <div class="form-group">
                    <label class="form-label">SKU / Item Code <span class="required">*</span></label>
                    <input class="form-control" type="text" name="sku" placeholder="e.g. ELEC-MOU-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit of Measure <span class="required">*</span></label>
                    <input class="form-control" type="text" name="unit" value="pcs" placeholder="pcs, box, kg, carton, litre" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Buying / Cost Price ($ / ৳)</label>
                    <input class="form-control" type="number" step="0.01" name="buying_price" value="0.00">
                    <div class="form-help">Used for inventory asset valuation</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Selling / Issue Price ($ / ৳)</label>
                    <input class="form-control" type="number" step="0.01" name="selling_price" value="0.00">
                    <div class="form-help">Standard unit price for orders/dispatch</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Opening Initial Stock</label>
                    <input class="form-control" type="number" name="opening_stock" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Low Stock Reorder Alert Limit</label>
                    <input class="form-control" type="number" name="alert_quantity" min="1" value="5" required>
                    <div class="form-help">Triggers system alert when stock drops below this</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description / Specifications</label>
                <textarea class="form-control" name="description" placeholder="Technical specifications, brand, warranty terms..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" checked>
                    <span>Active Product (Enabled for stock-in / stock-out transactions)</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Create Product</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
