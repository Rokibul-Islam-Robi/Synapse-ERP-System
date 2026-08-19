<?php
$pageTitle = "Edit Customer / Department";
$pageSubtitle = "Update client contact information and routing address";
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    set_flash('error', 'Record not found.');
    redirect("index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $customerType = trim($_POST['customer_type'] ?? 'corporate');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        set_flash('error', 'Name / Department Title is required.');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name=?, customer_type=?, email=?, phone=?, address=?, status=? WHERE id=?");
            $stmt->execute([$name, $customerType, $email, $phone, $address, $status, $id]);
            log_activity($pdo, 'Updated Customer/Dept', "ID: $id ($name)");
            set_flash('success', "Record updated successfully.");
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
        <h2 class="card-title">Edit Entity Profile</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Client Name / Department Title <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" value="<?= clean($customer['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Entity Classification</label>
                    <select class="form-control" name="customer_type">
                        <option value="corporate" <?= $customer['customer_type'] === 'corporate' ? 'selected' : '' ?>>Corporate Client / Buyer</option>
                        <option value="internal_dept" <?= $customer['customer_type'] === 'internal_dept' ? 'selected' : '' ?>>Internal Department / Branch</option>
                        <option value="individual" <?= $customer['customer_type'] === 'individual' ? 'selected' : '' ?>>Individual / Retail Customer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" value="<?= clean($customer['phone']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" value="<?= clean($customer['email']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery / Department Location Address</label>
                <textarea class="form-control" name="address"><?= clean($customer['address']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" <?= $customer['status'] ? 'checked' : '' ?>>
                    <span>Active Entity</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Update Record</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
