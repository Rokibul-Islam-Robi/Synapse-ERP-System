<?php
$pageTitle = "User & Access Management";
$pageSubtitle = "Manage system operators, role-based access permissions, and staff accounts";
require_once __DIR__ . '/../includes/header.php';
require_admin(); // Restrict to admin only

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card">
    <div class="table-toolbar">
        <div style="display:flex; align-items:center; gap: 12px; flex-wrap: wrap;">
            <input type="text" id="tableSearchInput" class="table-search-input" placeholder="Search system users...">
            <span style="font-size: 12.5px; color: var(--text-muted);"><?= count($users) ?> accounts configured</span>
        </div>
        <div style="display:flex; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="exportTableToCSV('dataTable', 'system_users_list.csv')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </button>
            <a class="btn btn-primary btn-sm" href="create.php">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add System User
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="corporate-table" id="dataTable">
            <thead>
                <tr>
                    <th>User / Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>System Role</th>
                    <th>Account Status</th>
                    <th>Created Date</th>
                    <th class="no-print no-export" style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): 
                    $rolePill = [
                        'admin' => '<span class="badge badge-danger">ADMINISTRATOR</span>',
                        'manager' => '<span class="badge badge-info">MANAGER</span>',
                        'staff' => '<span class="badge badge-secondary">STAFF OPERATOR</span>'
                    ][$u['role']] ?? '<span class="badge badge-secondary">STAFF</span>';
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap: 10px;">
                            <div class="user-avatar" style="width:32px; height:32px; font-size:12px; border-color:#2563eb; background:#0f172a;">
                                <?= strtoupper(substr($u['name'], 0, 1)) ?>
                            </div>
                            <strong><?= clean($u['name']) ?></strong>
                        </div>
                    </td>
                    <td><code><?= clean($u['username']) ?></code></td>
                    <td><?= clean($u['email']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                    <td><?= clean($u['phone']) ?: '<span style="color:var(--text-muted);">-</span>' ?></td>
                    <td><?= $rolePill ?></td>
                    <td>
                        <?php if ($u['status'] ?? 1): ?>
                            <span class="badge badge-success"><span class="badge-dot"></span> Active</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><span class="badge-dot"></span> Suspended</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="no-print no-export" style="text-align: right; white-space: nowrap;">
                        <a class="btn btn-secondary btn-sm" href="edit.php?id=<?= $u['id'] ?>" title="Edit User">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path></svg>
                        </a>
                        <?php if ($u['id'] != current_user_id()): ?>
                            <a class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete user account \'<?= clean($u['username']) ?>\'?')" href="delete.php?id=<?= $u['id'] ?>" title="Delete User">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
