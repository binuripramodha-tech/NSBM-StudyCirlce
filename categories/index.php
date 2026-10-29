<?php
$admin_page_title = "Manage News Categories";
require_once __DIR__ . '/../includes/admin_header.php';

$error = '';
$edit_cat = null;

if (isset($_GET['delete']) && filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT)) {
    $del_id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$del_id]);
        set_flash('success', 'Category deleted successfully.');
    } catch (Exception $e) {
        set_flash('danger', 'Cannot delete category that is currently linked to articles.');
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['edit']) && filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT)) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_cat = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $badge_color = trim($_POST['badge_color'] ?? 'success');

    if (empty($name)) {
        $error = "Category name cannot be empty.";
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        if ($cat_id) {

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, badge_color = ? WHERE id = ?");
            if ($stmt->execute([$name, $slug, $description, $badge_color, $cat_id])) {
                set_flash('success', 'Category updated successfully.');
                header("Location: index.php");
                exit;
            } else {
                $error = "Failed to update category.";
            }
        } else {

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, badge_color) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $slug, $description, $badge_color])) {
                set_flash('success', 'New category added successfully.');
                header("Location: index.php");
                exit;
            } else {
                $error = "Failed to add category. Name might already exist.";
            }
        }
    }
}

$categories_stmt = $pdo->query("
    SELECT c.*, COUNT(n.id) AS article_count,
           (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS subscriber_count
    FROM categories c
    LEFT JOIN news n ON n.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $categories_stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-tags text-success me-2"></i> Manage News Categories</h2>
        <p class="text-muted small mb-0">Organize campus articles by faculties, university events, athletics, and student life.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>

<div class="row g-4 mb-5">

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold text-navy mb-3 pb-2 border-bottom">
                <?php echo $edit_cat ? '<i class="bi bi-pencil me-1"></i> Edit Category' : '<i class="bi bi-plus-circle me-1 text-success"></i> Add New Category'; ?>
            </h5>

            <form method="POST" action="index.php">
                <?php if ($edit_cat): ?>
                    <input type="hidden" name="cat_id" value="<?php echo $edit_cat['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold text-navy">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Computing & Tech" required value="<?php echo htmlspecialchars($edit_cat['name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label small fw-semibold text-navy">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief scope of articles falling in this category..."><?php echo htmlspecialchars($edit_cat['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="badge_color" class="form-label small fw-semibold text-navy">Color Theme</label>
                    <select class="form-select" id="badge_color" name="badge_color">
                        <option value="success" <?php echo (($edit_cat['badge_color'] ?? '') === 'success') ? 'selected' : ''; ?>>Green (Success)</option>
                        <option value="primary" <?php echo (($edit_cat['badge_color'] ?? '') === 'primary') ? 'selected' : ''; ?>>Blue (Primary)</option>
                        <option value="warning" <?php echo (($edit_cat['badge_color'] ?? '') === 'warning') ? 'selected' : ''; ?>>Amber (Warning)</option>
                        <option value="info" <?php echo (($edit_cat['badge_color'] ?? '') === 'info') ? 'selected' : ''; ?>>Cyan (Info)</option>
                        <option value="secondary" <?php echo (($edit_cat['badge_color'] ?? '') === 'secondary') ? 'selected' : ''; ?>>Gray (Secondary)</option>
                        <option value="dark" <?php echo (($edit_cat['badge_color'] ?? '') === 'dark') ? 'selected' : ''; ?>>Dark Navy</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <?php if ($edit_cat): ?>
                        <a href="index.php" class="btn btn-outline-secondary w-50">Cancel</a>
                        <button type="submit" class="btn btn-nsbm w-50">Update</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-nsbm w-100 shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Save Category
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Slug</th>
                            <th>Articles</th>
                            <th>Subscribers</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo get_category_badge($c['name']); ?> text-uppercase me-1">
                                        <?php echo htmlspecialchars($c['name']); ?>
                                    </span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.8rem;"><?php echo htmlspecialchars($c['description']); ?></small>
                                </td>
                                <td><code><?php echo htmlspecialchars($c['slug']); ?></code></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo $c['article_count']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-bell text-warning me-1"></i> <?php echo $c['subscriber_count']; ?></span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="index.php?edit=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="index.php?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

