<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

// Check if category has linked products
$check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
$check->execute([$id]);
$productCount = (int)$check->fetchColumn();

if ($productCount > 0) {
    set_flash('error', "Cannot delete category: {$productCount} product(s) are currently attached to it. Please reassign or delete the products first.");
    redirect("index.php");
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    log_activity($pdo, 'Deleted Category', "Category ID: $id");
    set_flash('success', "Category deleted successfully.");
} catch (PDOException $e) {
    set_flash('error', "Could not delete category: " . $e->getMessage());
}

redirect("index.php");
?>
