<?php
/**
 * Synapse-ERP Automated Test Suite & Logic Validator
 * Run via CLI: php tests/run_tests.php
 */

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function assert_test($description, $condition, $details = '') {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$description}\n";
        if ($details) {
            echo "         Details: {$details}\n";
        }
    }
}

echo "========================================================\n";
echo "  SYNAPSE-ERP AUTOMATED TEST SUITE\n";
echo "========================================================\n\n";

// --- SUITE 1: DATABASE INTEGRITY ---
echo "▶ Running Suite 1: Database Connectivity & Tables...\n";
assert_test("Database PDO Connection is Active", $pdo instanceof PDO);

// Ensure migrations & seedings are executed
run_migrations($pdo);

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$expectedTables = ['users', 'categories', 'suppliers', 'customers', 'products', 'stock_transactions', 'activity_logs'];
foreach ($expectedTables as $tbl) {
    assert_test("Table '{$tbl}' exists in database", in_array($tbl, $tables));
}

// --- SUITE 2: DEMO ROLE CREDENTIALS VERIFICATION ---
echo "\n▶ Running Suite 2: Demo Role Credentials (Admin, Manager, Staff)...\n";
$demoRoles = ['admin', 'manager', 'staff'];
foreach ($demoRoles as $role) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$role]);
    $user = $stmt->fetch();
    
    assert_test("User account '{$role}' exists", !empty($user));
    assert_test("User '{$role}' has correct role '{$role}'", ($user['role'] ?? '') === $role);
    assert_test("User '{$role}' password 'password' verifies successfully", password_verify('password', $user['password'] ?? ''));
    assert_test("User '{$role}' status is Active (1)", ($user['status'] ?? 0) == 1);
}

// --- SUITE 3: RBAC GUARD MATRIX ---
echo "\n▶ Running Suite 3: RBAC Authorization Matrix...\n";
$_SESSION['role'] = 'admin';
assert_test("Admin role: is_admin() is TRUE", is_admin() === true);
assert_test("Admin role: can_manage_users() is TRUE", can_manage_users() === true);
assert_test("Admin role: can_delete() is TRUE", can_delete() === true);
assert_test("Admin role: can_view_reports() is TRUE", can_view_reports() === true);

$_SESSION['role'] = 'manager';
assert_test("Manager role: is_admin() is FALSE", is_admin() === false);
assert_test("Manager role: is_manager() is TRUE", is_manager() === true);
assert_test("Manager role: can_manage_users() is FALSE", can_manage_users() === false);
assert_test("Manager role: can_delete() is TRUE", can_delete() === true);
assert_test("Manager role: can_view_reports() is TRUE", can_view_reports() === true);

$_SESSION['role'] = 'staff';
assert_test("Staff role: is_admin() is FALSE", is_admin() === false);
assert_test("Staff role: is_manager() is FALSE", is_manager() === false);
assert_test("Staff role: is_staff() is TRUE", is_staff() === true);
assert_test("Staff role: can_manage_users() is FALSE", can_manage_users() === false);
assert_test("Staff role: can_delete() is FALSE", can_delete() === false);

// --- SUITE 4: INVENTORY MATHEMATICAL CALCULATION ---
echo "\n▶ Running Suite 4: Inventory Mathematical Logic...\n";
// Insert test category & product
$pdo->exec("INSERT INTO categories (category_name, description) VALUES ('Test Cat " . time() . "', 'Testing') ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
$catId = $pdo->lastInsertId();

$sku = "TEST-SKU-" . time();
$pdo->prepare("INSERT INTO products (category_id, product_name, sku, opening_stock, buying_price, selling_price, alert_quantity) VALUES (?, 'Test Laptop', ?, 50, 500.00, 750.00, 10)")->execute([$catId, $sku]);
$prodId = $pdo->lastInsertId();

// Add stock in +20
$pdo->prepare("INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_price, transaction_date) VALUES (?, 'IN', 20, 500.00, 10000.00, CURDATE())")->execute([$prodId]);

// Add stock out -15
$pdo->prepare("INSERT INTO stock_transactions (product_id, transaction_type, quantity, unit_price, total_price, transaction_date) VALUES (?, 'OUT', 15, 750.00, 11250.00, CURDATE())")->execute([$prodId]);

$calcStock = get_product_stock($pdo, $prodId);
$expectedStock = 50 + 20 - 15; // 55
assert_test("Stock Equation (50 Opening + 20 IN - 15 OUT = 55)", $calcStock === $expectedStock, "Got: {$calcStock}, Expected: {$expectedStock}");

// Clean up test data
$pdo->prepare("DELETE FROM stock_transactions WHERE product_id = ?")->execute([$prodId]);
$pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$prodId]);

// --- TEST SUMMARY ---
echo "\n========================================================\n";
echo "  TEST EXECUTION SUMMARY\n";
echo "========================================================\n";
echo "  Total Tests:  {$totalTests}\n";
echo "  Passed Tests: {$passedTests}\n";
echo "  Failed Tests: {$failedTests}\n";

if ($failedTests === 0) {
    echo "  Status:       ALL SYSTEMS GREEN (100% PASS)\n";
    exit(0);
} else {
    echo "  Status:       FAILURES DETECTED\n";
    exit(1);
}
?>
