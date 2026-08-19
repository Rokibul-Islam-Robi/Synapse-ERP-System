<?php
$pageTitle = "Customers & Departments";
$pageSubtitle = "Manage corporate clients, dispatch recipients, and internal cost centers";
require_once __DIR__ . '/../includes/header.php';
require_login();

$sql = "SELECT c.*, COUNT(st.id) AS dispatch_count
        FROM customers c
        LEFT JOIN stock_transactions st ON st.customer_id = c.id
        GROUP BY c.id
        ORDER BY c.name ASC";
$customers = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="table-toolbar">
        <div style="display:flex; align-items:center; gap: 12px; flex-wrap: wrap;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search customers/departments...">
            <span style="font-size: 12.5px; color: var(--text-muted);"><?= count($customers) ?> records</span>
        </div>
        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'customers_list.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <a class="btn btn-primary btn-sm" href="create.php">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Customer / Dept
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>Customer / Department</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address / Location</th>
                    <th>Total Dispatches</th>
                    <th>Status</th>
                    <th class="no-print no-export" style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center; color: var(--text-muted); padding: 40px;">
                            No customers or departments registered yet. Click "Add Customer / Dept" to add one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): 
                        $typeBadge = [
                            'corporate' => '<span class="badge badge-info">Corporate</span>',
                            'internal_dept' => '<span class="badge badge-purple" style="background:#f5f3ff; color:#7c3aed;">Internal Dept</span>',
                            'individual' => '<span class="badge badge-secondary">Individual</span>'
                        ][$c['customer_type']] ?? '<span class="badge badge-secondary">General</span>';
                    ?>
                    <tr>
                        <td><strong><?= clean($c['name']) ?></strong></td>
                        <td><?= $typeBadge ?></td>
                        <td><?= clean($c['phone']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><?= clean($c['email']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><?= clean($c['address']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                        <td><span class="badge badge-warning"><?= $c['dispatch_count'] ?> dispatches</span></td>
                        <td>
                            <?php if ($c['status']): ?>
                                <span class="badge badge-success"><span class="badge-dot"></span> Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><span class="badge-dot"></span> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="no-print no-export" style="text-align: right; white-space: nowrap;">
                            <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= $c['id'] ?>" title="Edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                            </a>
                            <a class="btn btn-danger btn-sm" onclick="return confirm('Delete customer \'<?= clean($c['name']) ?>\'?')" href="delete.php?id=<?= $c['id'] ?>" title="Delete">
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
