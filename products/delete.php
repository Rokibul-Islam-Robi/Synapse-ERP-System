<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

// Check if product has existing stock transactions
$check = $pdo->prepare("SELECT COUNT(*) FROM stock_transactions WHERE product_id = ?");
$check->execute([$id]);
$txCount = (int)$check->fetchColumn();

if ($txCount > 0) {
    set_flash('error', "Cannot delete this product: It has {$txCount} stock transaction record(s) associated with it. You can set its status to Inactive instead.");
    redirect("index.php");
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    log_activity($pdo, 'Deleted Product', "Product ID: $id");
    set_flash('success', "Product removed from catalog successfully.");
} catch (PDOException $e) {
    set_flash('error', "Could not delete product: " . $e->getMessage());
}

redirect("index.php");
?>
