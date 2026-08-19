<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    log_activity($pdo, 'Deleted Supplier', "Supplier ID: $id");
    set_flash('success', "Supplier record removed successfully.");
} catch (PDOException $e) {
    set_flash('error', "Could not delete supplier: " . $e->getMessage());
}

redirect("index.php");
?>
