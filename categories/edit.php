<?php
$pageTitle = "Edit Category";
$pageSubtitle = "Update category specifications and visibility";
require_once __DIR__ . '/../includes/header.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('error', 'Category not found.');
    redirect("index.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($name)) {
        set_flash('error', 'Category name is required.');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET category_name=?, description=?, status=? WHERE id=?");
            $stmt->execute([$name, $description, $status, $id]);
            log_activity($pdo, 'Updated Category', "Category ID: $id ($name)");
            set_flash('success', "Category '{$name}' updated successfully.");
            redirect("index.php");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                set_flash('error', "Category '{$name}' already exists.");
            } else {
                set_flash('error', "Database error: " . $e->getMessage());
            }
        }
    }
}
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="card" style="max-width: 680px;">
    <div class="card-header">
        <h2 class="card-title">Edit Category Details</h2>
        <a href="index.php" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Category Name <span class="required">*</span></label>
                <input class="form-control" type="text" name="category_name" value="<?= clean($category['category_name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description"><?= clean($category['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="status" value="1" <?= $category['status'] ? 'checked' : '' ?>>
                    <span>Active category</span>
                </label>
            </div>

            <div style="display:flex; gap: 12px; margin-top: 24px;">
                <button class="btn btn-primary" type="submit">Update Category</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
