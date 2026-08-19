<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/pagination.php';

$pageTitle = "Products Inventory";
$pageSubtitle = "Manage corporate product catalog, pricing, and stock monitoring";

// Search and Filter Params
$search = trim($_GET['search'] ?? '');
$categoryId = trim($_GET['category_id'] ?? '');
$stockFilter = trim($_GET['stock_filter'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.product_name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($categoryId)) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryId;
}

$whereClause = implode(" AND ", $where);

// Categories for dropdown filter
$categories = $pdo->query("SELECT id, category_name FROM categories WHERE status = 1 ORDER BY category_name ASC")->fetchAll();

// Base SQL query for products
$baseSql = "SELECT 
                p.id,
                p.product_name,
                p.sku,
                p.unit,
                p.opening_stock,
                p.buying_price,
                p.selling_price,
                p.alert_quantity,
                p.status,
                c.category_name,
                COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0) AS stock_in,
                COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS stock_out,
                p.opening_stock 
                + COALESCE(SUM(CASE WHEN st.transaction_type='IN' THEN st.quantity ELSE 0 END),0)
                - COALESCE(SUM(CASE WHEN st.transaction_type='OUT' THEN st.quantity ELSE 0 END),0) AS current_stock
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN stock_transactions st ON st.product_id = p.id
            WHERE {$whereClause}
            GROUP BY p.id
            ORDER BY p.id DESC";

$pagination = paginate_data($pdo, $baseSql, $params, 12);
$products = $pagination['data'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Product Catalog</h2>
        <?php if (is_manager()): ?>
        <a href="create.php" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Product
        </a>
        <?php endif; ?>
    </div>

    <!-- Advanced Filter & Search Toolbar -->
    <div class="table-toolbar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input type="text" name="search" class="table-search-input" placeholder="Search by SKU or product name..." value="<?= clean($search) ?>">
            
            <select name="category_id" class="form-control" style="width: auto; padding: 7px 12px; font-size: 13px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                        <?= clean($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($categoryId)): ?>
                <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
            <?php endif; ?>
        </form>

        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('products-catalog.csv', 'productsTable')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                CSV
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect width="12" height="8" x="6" y="14"></rect></svg>
                Print
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="productsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product &amp; SKU</th>
                    <th>Category</th>
                    <th>Buying / Selling</th>
                    <th>Stock Balance</th>
                    <th>Status</th>
                    <th class="no-print no-export" style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted);">
                            No products found matching the criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $i => $p): ?>
                        <tr>
                            <td><?= $pagination['offset'] + $i + 1 ?></td>
                            <td>
                                <strong style="font-size: 14px;"><?= clean($p['product_name']) ?></strong>
                                <div style="font-family: monospace; font-size: 11px; color: var(--text-muted); margin-top:2px;">
                                    SKU: <?= clean($p['sku']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= clean($p['category_name']) ?></span>
                            </td>
                            <td>
                                <div style="font-size: 12.5px;">Buy: <?= format_currency($p['buying_price']) ?></div>
                                <div style="font-size: 12.5px; font-weight: 600; color: var(--primary);">Sell: <?= format_currency($p['selling_price']) ?></div>
                            </td>
                            <td>
                                <?php if ($p['current_stock'] <= 0): ?>
                                    <span class="badge badge-danger"><span class="badge-dot"></span> Out of Stock (0 <?= clean($p['unit']) ?>)</span>
                                <?php elseif ($p['current_stock'] <= $p['alert_quantity']): ?>
                                    <span class="badge badge-warning"><span class="badge-dot"></span> Low Stock (<?= $p['current_stock'] ?> <?= clean($p['unit']) ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><span class="badge-dot"></span> <?= $p['current_stock'] ?> <?= clean($p['unit']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $p['status'] == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?>
                            </td>
                            <td class="no-print no-export" style="text-align: right; white-space: nowrap;">
                                <a class="btn btn-secondary btn-sm" href="<?= base_url('stock/stock_in.php?product_id=' . $p['id']) ?>" title="Quick Stock In">
                                    + Stock
                                </a>
                                <?php if (is_manager()): ?>
                                <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= $p['id'] ?>" title="Edit Product">
                                    Edit
                                </a>
                                <?php endif; ?>
                                <?php if (can_delete()): ?>
                                <a class="btn btn-danger btn-sm" href="delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete Product">
                                    Delete
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Server-Side Pagination Controls -->
    <?= render_pagination($pagination) ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
