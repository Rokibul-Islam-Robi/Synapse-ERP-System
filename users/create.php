<?php
$pageTitle = "Create System User";
$pageSubtitle = "Add new authorized operator with custom role permissions";
require_once __DIR__ . '/../includes/header.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name) || empty($username) || empty($password)) {
        set_flash('error', 'Full Name, Username, and Password are required.');
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $username, $email, $phone, $hashedPassword, $role, $status]);
            log_activity($pdo, 'Created User', "Created account: $username ($role)");
            set_flash('success', "User account '{$username}' created successfully.");
            redirect("index.php");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                set_flash('error', "Username '{$username}' is already taken.");
            } else {
                set_flash('error', "Database error: " . $e->getMessage());
            }
        }
    }
}
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 760px;">
    <div class="card-header">
        <h2 class="card-title">New Operator Account</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Users</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" placeholder="e.g. Sarah Jenkins" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input class="form-control" type="text" name="username" placeholder="e.g. sjenkins" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" placeholder="user@company.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" placeholder="+880 1700-000000">
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input class="form-control" type="password" name="password" placeholder="Minimum 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label">System Role Permission</label>
                    <select class="form-control" name="role">
                        <option value="staff">Staff Operator (Stock In & Out only)</option>
                        <option value="manager">Manager (Stock, Catalogs & Reports)</option>
                        <option value="admin">Administrator (Full System Control)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" checked>
                    <span>Active Account (Permit login access)</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Create User Account</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
