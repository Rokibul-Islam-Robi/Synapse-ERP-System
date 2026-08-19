<?php
$pageTitle = "Add Customer / Department";
$pageSubtitle = "Register corporate clients or internal department destination";
require_once __DIR__ . '/../includes/header.php';
require_login();

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
            $stmt = $pdo->prepare("INSERT INTO customers (name, customer_type, email, phone, address, status)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $customerType, $email, $phone, $address, $status]);
            log_activity($pdo, 'Created Customer/Dept', "Name: $name ($customerType)");
            set_flash('success', "Customer/Department '{$name}' created successfully.");
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
        <h2 class="card-title">New Client / Department Form</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Client Name / Department Title <span class="required">*</span></label>
                    <input class="form-control" type="text" name="name" placeholder="e.g. Finance Division or AcadeMedia Inc." required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Entity Classification</label>
                    <select class="form-control" name="customer_type">
                        <option value="corporate">Corporate Client / Buyer</option>
                        <option value="internal_dept">Internal Department / Branch</option>
                        <option value="individual">Individual / Retail Customer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone" placeholder="e.g. +880 1700-112233">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input class="form-control" type="email" name="email" placeholder="contact@domain.com">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Delivery / Department Location Address</label>
                <textarea class="form-control" name="address" placeholder="Physical floor, room number, or shipping street address..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" checked>
                    <span>Active Entity (Available for stock-out dispatches)</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Save Customer / Dept</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
