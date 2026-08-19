<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

if ($id === (int)current_user_id()) {
    set_flash('error', "Security violation: You cannot delete your own active administrator account.");
    redirect("index.php");
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    log_activity($pdo, 'Deleted User', "User Account ID: $id deleted");
    set_flash('success', "User account deleted successfully.");
} catch (PDOException $e) {
    set_flash('error', "Could not delete user: " . $e->getMessage());
}

redirect("index.php");
?>
