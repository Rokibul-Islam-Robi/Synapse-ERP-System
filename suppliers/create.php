<?php
$pageTitle = "Add Supplier";
$pageSubtitle = "Register procurement vendor contact and company profile";
require_once __DIR__ . '/../includes/header.php';
require_login();

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
            $stmt = $pdo->prepare("INSERT INTO suppliers (name, company_name, email, phone, address, status)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $companyName, $email, $phone, $address, $status]);
            log_activity($pdo, 'Created Supplier', "Supplier: $companyName ($name)");
            set_flash('success', "Supplier '{$name}' registered successfully.");
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
        <h2 class="card-title">New Supplier Information</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to Suppliers</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Contact Person Name <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" placeholder="e.g. John Doe" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Company / Business Name</label>
                    <input class="form-control" type="text" name="company_name" placeholder="e.g. Global Tech Distributors">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" placeholder="e.g. +1 555-0199 or +880 1700-000000">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" placeholder="vendor@example.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Physical / Warehouse Address</label>
                <textarea class="form-control" name="address" placeholder="Office / Warehouse street address..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" checked>
                    <span>Active Supplier (Available for stock-in shipments)</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Save Supplier</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
