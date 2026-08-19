<?php
$pageTitle = "Product Categories";
$pageSubtitle = "Manage product classification and grouping taxonomy";
require_once __DIR__ . '/../includes/header.php';
require_login();

// Query categories with product counts
$sql = "SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        GROUP BY c.id
        ORDER BY c.category_name ASC";
$categories = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="table-toolbar">
        <div style="display:flex; align-items:center; gap: 12px;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search categories...">
            <span style="font-size: 12.5px; color: var(--text-muted);"><?= count($categories) ?> total items</span>
        </div>
        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'categories.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <a class="btn btn-primary btn-sm" href="create.php">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Category
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Products Count</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th class="no-print no-export" style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color: var(--text-muted); padding: 40px;">
                            No categories found. Click "Add Category" to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $index => $cat): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><strong><?= clean($cat['category_name']) ?></strong></td>
                        <td style="color: var(--text-secondary); max-width: 300px;">
                            <?= clean($cat['description']) ?: '<span style="color:var(--text-muted);">No description</span>' ?>
                        </td>
                        <td>
                            <span class="badge badge-info"><?= $cat['product_count'] ?> products</span>
                        </td>
                        <td>
                            <?php if ($cat['status']): ?>
                                <span class="badge badge-success"><span class="badge-dot"></span> Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><span class="badge-dot"></span> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($cat['created_at'])) ?></td>
                        <td class="no-print no-export" style="text-align: right;">
                            <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= $cat['id'] ?>" title="Edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                                Edit
                            </a>
                            <a class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete category \'<?= clean($cat['category_name']) ?>\'?')" href="delete.php?id=<?= $cat['id'] ?>" title="Delete">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                                Delete
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
