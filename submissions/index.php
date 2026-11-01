<?php
$admin_page_title = "Review Student Submissions";
require_once __DIR__ . '/../includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $sub_id = filter_input(INPUT_POST, 'submission_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'];
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    if ($sub_id) {
        $stmt = $pdo->prepare("SELECT * FROM article_submissions WHERE id = ?");
        $stmt->execute([$sub_id]);
        $sub = $stmt->fetch();

        if ($sub) {
            if ($action === 'approve') {

                $upd = $pdo->prepare("UPDATE article_submissions SET status = 'approved', admin_notes = ?, reviewed_at = NOW() WHERE id = ?");
                $upd->execute([$admin_notes ?: 'Approved for university publication by editorial team.', $sub_id]);

                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $sub['title']), '-'));
                $excerpt = substr(strip_tags($sub['content']), 0, 160) . '...';
                $news_insert = $pdo->prepare("
                    INSERT INTO news (title, slug, excerpt, content, image, category_id, author_id, status, is_featured, is_breaking)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'published', 0, 0)
                ");
                $news_insert->execute([$sub['title'], $slug, $excerpt, $sub['content'], $sub['image'], $sub['category_id'], $sub['user_id']]);

                set_flash('success', "Story '{$sub['title']}' approved and published to the live campus feed!");
            } elseif ($action === 'reject') {
                $upd = $pdo->prepare("UPDATE article_submissions SET status = 'rejected', admin_notes = ?, reviewed_at = NOW() WHERE id = ?");
                $upd->execute([$admin_notes ?: 'Article does not meet current publication guidelines.', $sub_id]);
                set_flash('info', "Submission marked as rejected.");
            }
        }
    }
    header("Location: index.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $sub_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM article_submissions WHERE id = ?");
    $stmt->execute([$sub_id]);
    $sub = $stmt->fetch();

    if ($sub) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE article_submissions SET status = 'approved', admin_notes = 'Quick approved by admin.', reviewed_at = NOW() WHERE id = ?")->execute([$sub_id]);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $sub['title']), '-'));
            $excerpt = substr(strip_tags($sub['content']), 0, 160) . '...';
            $pdo->prepare("INSERT INTO news (title, slug, excerpt, content, image, category_id, author_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'published')")
                ->execute([$sub['title'], $slug, $excerpt, $sub['content'], $sub['image'], $sub['category_id'], $sub['user_id']]);
            set_flash('success', "Submission approved and published!");
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE article_submissions SET status = 'rejected', admin_notes = 'Rejected by editorial board.', reviewed_at = NOW() WHERE id = ?")->execute([$sub_id]);
            set_flash('info', "Submission rejected.");
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM article_submissions WHERE id = ?")->execute([$sub_id]);
            set_flash('success', "Submission deleted permanently.");
        }
    }
    header("Location: index.php");
    exit;
}

$filter_status = $_GET['status'] ?? 'pending';
$sql = "
    SELECT a.*, c.name AS category_name, u.name AS student_name, u.email AS student_email, u.faculty AS student_faculty, u.student_id
    FROM article_submissions a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.user_id = u.id
";

if (in_array($filter_status, ['pending', 'approved', 'rejected'])) {
    $sql .= " WHERE a.status = '{$filter_status}'";
}
$sql .= " ORDER BY a.submitted_at DESC";

$submissions = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-file-earmark-check text-warning me-2"></i> Student Submissions Review</h2>
        <p class="text-muted small mb-0">Review, verify, approve and publish articles submitted by campus student societies.</p>
    </div>
    <div class="btn-group rounded-pill shadow-sm" role="group">
        <a href="index.php?status=pending" class="btn btn-sm <?php echo ($filter_status === 'pending') ? 'btn-warning text-dark' : 'btn-light border'; ?>">
            Pending Queue
        </a>
        <a href="index.php?status=approved" class="btn btn-sm <?php echo ($filter_status === 'approved') ? 'btn-success' : 'btn-light border'; ?>">
            Approved
        </a>
        <a href="index.php?status=rejected" class="btn btn-sm <?php echo ($filter_status === 'rejected') ? 'btn-danger' : 'btn-light border'; ?>">
            Rejected
        </a>
        <a href="index.php?status=all" class="btn btn-sm <?php echo ($filter_status === 'all') ? 'btn-navy' : 'btn-light border'; ?>">
            All History
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Article / Story</th>
                    <th>Submitted By</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-navy d-block"><?php echo htmlspecialchars($sub['title']); ?></span>
                                <small class="text-muted"><?php echo htmlspecialchars(substr(strip_tags($sub['content']), 0, 90)); ?>...</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($sub['student_name']); ?></span>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($sub['student_faculty']); ?> (<?php echo htmlspecialchars($sub['student_id'] ?? 'Student'); ?>)</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($sub['category_name']); ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo format_date($sub['submitted_at'], true); ?></small>
                            </td>
                            <td>
                                <?php if ($sub['status'] === 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                <?php elseif ($sub['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Pending Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $sub['id']; ?>">
                                    <i class="bi bi-eye"></i> View & Decide
                                </button>
                                <a href="index.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" title="Delete Submission">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="viewModal<?php echo $sub['id']; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $sub['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold text-navy" id="modalLabel<?php echo $sub['id']; ?>">
                                            Submission Review: <?php echo htmlspecialchars($sub['title']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
                                            <span><strong>Author:</strong> <?php echo htmlspecialchars($sub['student_name']); ?> (<?php echo htmlspecialchars($sub['student_faculty']); ?>)</span>
                                            <span><strong>Category:</strong> <?php echo htmlspecialchars($sub['category_name']); ?></span>
                                        </div>

                                        <?php if (!empty($sub['image'])): ?>
                                            <div class="mb-3 rounded-3 overflow-hidden text-center bg-light p-2">
                                                <img src="<?php echo get_image_url($sub['image']); ?>" alt="Cover" style="max-height: 240px; max-width: 100%; object-fit: contain;" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                                            </div>
                                        <?php endif; ?>

                                        <div class="p-3 bg-light rounded-3 mb-4 text-dark lh-base">
                                            <?php echo nl2br(htmlspecialchars($sub['content'])); ?>
                                        </div>

                                        <form method="POST" action="index.php">
                                            <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-navy">Admin Feedback Notes (Visible to Student):</label>
                                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Optional editorial feedback or instructions..."><?php echo htmlspecialchars($sub['admin_notes'] ?? ''); ?></textarea>
                                            </div>

                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Reject Story
                                                </button>
                                                <button type="submit" name="action" value="approve" class="btn btn-success">
                                                    <i class="bi bi-check-circle me-1"></i> Approve & Publish
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-check2-all display-4 text-secondary d-block mb-3"></i>
                            <h6>No submissions found in this status category.</h6>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

