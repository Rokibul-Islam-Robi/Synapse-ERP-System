<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    log_activity($pdo, 'Deleted Customer/Dept', "ID: $id");
    set_flash('success', "Customer/Department record deleted successfully.");
} catch (PDOException $e) {
    set_flash('error', "Could not delete record: " . $e->getMessage());
}

redirect("index.php");
?>
