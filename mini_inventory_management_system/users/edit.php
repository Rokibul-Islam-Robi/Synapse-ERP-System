<?php
$pageTitle = "Edit User Account";
$pageSubtitle = "Update operator credentials, roles, and security status";
require_once __DIR__ . '/../includes/header.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'User account not found.');
    redirect("index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        set_flash('error', 'Full Name is required.');
    } else {
        try {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, password=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, $phone, $hashed, $role, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, $phone, $role, $status, $id]);
            }
            log_activity($pdo, 'Updated User', "Updated account ID: $id ({$user['username']})");
            set_flash('success', "User profile updated successfully.");
            redirect("index.php");
        } catch (PDOException $e) {
            set_flash('error', "Database error: " . $e->getMessage());
        }
    }
}
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 760px;">
    <div class="card-header">
        <h2 class="card-title">Edit User Profile: <?= clean($user['username']) ?></h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Users</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" value="<?= clean($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input class="form-control" type="text" value="<?= clean($user['username']) ?>" disabled style="background-color:var(--bg-surface-secondary);">
                    <div class="form-help">Username cannot be altered</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" value="<?= clean($user['email']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" value="<?= clean($user['phone']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Change Password</label>
                    <input class="form-control" type="password" name="password" placeholder="Leave blank to keep existing password">
                </div>

                <div class="form-group">
                    <label class="form-label">System Role Permission</label>
                    <select class="form-control" name="role">
                        <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff Operator</option>
                        <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" <?= ($user['status'] ?? 1) ? 'checked' : '' ?>>
                    <span>Active Account</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Update User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
