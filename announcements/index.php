<?php
$admin_page_title = "Manage Campus Announcements";
require_once __DIR__ . '/../includes/admin_header.php';

$error = '';
$edit_ann = null;

if (isset($_GET['delete']) && filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT)) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$del_id]);
    set_flash('success', 'Announcement deleted successfully.');
    header("Location: index.php");
    exit;
}

if (isset($_GET['edit']) && filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT)) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_ann = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ann_id = filter_input(INPUT_POST, 'ann_id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['normal', 'important', 'urgent']) ? $_POST['priority'] : 'normal';
    $target_audience = trim($_POST['target_audience'] ?? 'All Students');

    if (empty($title) || empty($content)) {
        $error = "Announcement title and content cannot be blank.";
    } else {
        if ($ann_id) {
            $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, priority = ?, target_audience = ? WHERE id = ?");
            if ($stmt->execute([$title, $content, $priority, $target_audience, $ann_id])) {
                set_flash('success', 'Announcement updated successfully.');
                header("Location: index.php");
                exit;
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO announcements (title, content, priority, target_audience) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$title, $content, $priority, $target_audience])) {
                set_flash('success', 'New announcement broadcast published.');
                header("Location: index.php");
                exit;
            }
        }
    }
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY FIELD(priority, 'urgent', 'important', 'normal'), created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-megaphone text-danger me-2"></i> Manage Campus Announcements</h2>
        <p class="text-muted small mb-0">Publish official circulars, exam notices, and priority alerts for student body.</p>
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
                <?php echo $edit_ann ? '<i class="bi bi-pencil me-1"></i> Edit Notice' : '<i class="bi bi-plus-circle me-1 text-danger"></i> New Announcement'; ?>
            </h5>

            <form method="POST" action="index.php">
                <?php if ($edit_ann): ?>
                    <input type="hidden" name="ann_id" value="<?php echo $edit_ann['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="title" class="form-label small fw-semibold text-navy">Notice Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Library Extended Hours for Finals" value="<?php echo htmlspecialchars($edit_ann['title'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="priority" class="form-label small fw-semibold text-navy">Priority Level</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="normal" <?php echo (($edit_ann['priority'] ?? '') === 'normal') ? 'selected' : ''; ?>>Normal (Green)</option>
                        <option value="important" <?php echo (($edit_ann['priority'] ?? '') === 'important') ? 'selected' : ''; ?>>Important (Amber)</option>
                        <option value="urgent" <?php echo (($edit_ann['priority'] ?? '') === 'urgent') ? 'selected' : ''; ?>>Urgent Broadcast (Red)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="target_audience" class="form-label small fw-semibold text-navy">Target Audience</label>
                    <input type="text" class="form-control" id="target_audience" name="target_audience" placeholder="e.g. All Students / Computing Batch 26.1" value="<?php echo htmlspecialchars($edit_ann['target_audience'] ?? 'All Students'); ?>">
                </div>

                <div class="mb-4">
                    <label for="content" class="form-label small fw-semibold text-navy">Content Details <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="4" required placeholder="Type full notice circular text..."><?php echo htmlspecialchars($edit_ann['content'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <?php if ($edit_ann): ?>
                        <a href="index.php" class="btn btn-outline-secondary w-50">Cancel</a>
                        <button type="submit" class="btn btn-nsbm w-50">Update</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-nsbm w-100 shadow-sm">
                            <i class="bi bi-broadcast me-1"></i> Broadcast Notice
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
                            <th>Notice Title</th>
                            <th>Priority</th>
                            <th>Audience</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($announcements)): ?>
                            <?php foreach ($announcements as $a): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-navy d-block"><?php echo htmlspecialchars($a['title']); ?></span>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($a['content'], 0, 80)); ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo ($a['priority'] === 'urgent') ? 'danger' : (($a['priority'] === 'important') ? 'warning text-dark' : 'success'); ?>">
                                            <?php echo strtoupper($a['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo htmlspecialchars($a['target_audience']); ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo format_date($a['created_at']); ?></small>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="index.php?edit=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?delete=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No announcements on record.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

