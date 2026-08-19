<?php
$pageTitle = "Edit Supplier";
$pageSubtitle = "Update supplier contact information and partnership status";
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    set_flash('error', 'Supplier record not found.');
    redirect("index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        set_flash('error', 'Contact Person Name is required.');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE suppliers SET name=?, company_name=?, email=?, phone=?, address=?, status=? WHERE id=?");
            $stmt->execute([$name, $companyName, $email, $phone, $address, $status, $id]);
            log_activity($pdo, 'Updated Supplier', "Supplier ID: $id ($name)");
            set_flash('success', "Supplier updated successfully.");
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
        <h2 class="card-title">Edit Supplier Profile</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Suppliers</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Contact Person Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" value="<?= clean($supplier['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Company / Business Name</label>
                    <input class="form-control" type="text" name="company_name" value="<?= clean($supplier['company_name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" value="<?= clean($supplier['phone']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" value="<?= clean($supplier['email']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Physical / Warehouse Address</label>
                <textarea class="form-control" name="address"><?= clean($supplier['address']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" <?= $supplier['status'] ? 'checked' : '' ?>>
                    <span>Active Supplier</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Update Supplier</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
