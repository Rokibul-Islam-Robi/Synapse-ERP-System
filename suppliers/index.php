<?php
$pageTitle = "Suppliers & Vendors";
$pageSubtitle = "Manage procurement partners and supply chain sources";
require_once __DIR__ . '/../includes/header.php';
require_login();

$sql = "SELECT s.*, COUNT(st.id) AS transaction_count
        FROM suppliers s
        LEFT JOIN stock_transactions st ON st.supplier_id = s.id
        GROUP BY s.id
        ORDER BY s.company_name ASC";
$suppliers = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="table-toolbar">
        <div style="display:flex; align-items:center; gap: 12px; flex-wrap: wrap;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search suppliers by name, company...">
            <span style="font-size: 12.5px; color: var(--text-muted);"><?= count($suppliers) ?> vendors registered</span>
        </div>
        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'suppliers_list.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <a class="btn btn-primary btn-sm" href="create.php">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Supplier
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>Company / Vendor</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Stock Purchases</th>
                    <th>Status</th>
                    <th class="no-print no-export" style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--text-muted); padding: 40px;">
                            No suppliers registered yet. Click "Add Supplier" to add one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td>
                            <strong><?= clean($s['company_name'] ?: $s['name']) ?></strong>
                        </td>
                        <td><?= clean($s['name']) ?></td>
                        <td><?= clean($s['phone']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><?= clean($s['email']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><?= clean($s['address']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><span class="badge badge-info"><?= $s['transaction_count'] ?> shipments</span></td>
                        <td>
                            <?php if ($s['status']): ?>
                                <span class="badge badge-success"><span class="badge-dot"></span> Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><span class="badge-dot"></span> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-print no-export" style="text-align: right; white-space: nowrap;">
                            <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= $s['id'] ?>" title="Edit Supplier">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                            </a>
                            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete supplier \'<?= clean($s['name']) ?>\'?')" href="delete.php?id=<?= $s['id'] ?>" title="Delete Supplier">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
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
