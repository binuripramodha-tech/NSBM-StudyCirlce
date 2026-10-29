<?php
$admin_page_title = "Moderate Reader Comments";
require_once __DIR__ . '/../includes/admin_header.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $c_id = (int)$_GET['id'];
    $act = $_GET['action'];

    if ($act === 'approve') {
        $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$c_id]);
        set_flash('success', 'Comment approved and published.');
    } elseif ($act === 'reject') {
        $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?")->execute([$c_id]);
        set_flash('info', 'Comment hidden from public view.');
    } elseif ($act === 'delete') {
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$c_id]);
        set_flash('success', 'Comment deleted permanently.');
    }
    header("Location: index.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
$sql = "
    SELECT c.*, n.title AS article_title, u.name AS user_name, u.email AS user_email, u.faculty AS user_faculty
    FROM comments c
    JOIN news n ON c.news_id = n.id
    JOIN users u ON c.user_id = u.id
";

if (in_array($status_filter, ['approved', 'pending', 'rejected'])) {
    $sql .= " WHERE c.status = '{$status_filter}'";
}
$sql .= " ORDER BY c.created_at DESC";

$comments = $pdo->query($sql)->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-chat-left-quote text-primary me-2"></i> Reader Comments Moderation</h2>
        <p class="text-muted small mb-0">Monitor campus discussions, filter offensive language, approve or remove reader remarks.</p>
    </div>
    <div class="btn-group rounded-pill shadow-sm" role="group">
        <a href="index.php?status=all" class="btn btn-sm <?php echo ($status_filter === 'all') ? 'btn-navy' : 'btn-light border'; ?>">
            All (<?php echo count($comments); ?>)
        </a>
        <a href="index.php?status=pending" class="btn btn-sm <?php echo ($status_filter === 'pending') ? 'btn-warning text-dark' : 'btn-light border'; ?>">
            Pending
        </a>
        <a href="index.php?status=approved" class="btn btn-sm <?php echo ($status_filter === 'approved') ? 'btn-success' : 'btn-light border'; ?>">
            Approved
        </a>
        <a href="index.php?status=rejected" class="btn btn-sm <?php echo ($status_filter === 'rejected') ? 'btn-danger' : 'btn-light border'; ?>">
            Rejected
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Article</th>
                    <th>Commenter</th>
                    <th style="width: 40%;">Comment Content</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $c): ?>
                        <tr>
                            <td>
                                <a href="../../article.php?id=<?php echo $c['news_id']; ?>" target="_blank" class="fw-semibold text-navy text-decoration-none">
                                    <?php echo htmlspecialchars(substr($c['article_title'], 0, 45)); ?>...
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($c['user_name']); ?></span>
                                <small class="d-block text-muted"><?php echo htmlspecialchars($c['user_faculty'] ?? 'Student'); ?></small>
                            </td>
                            <td>
                                <p class="mb-0 text-dark small" style="line-height: 1.5;">
                                    <?php echo nl2br(htmlspecialchars($c['comment'])); ?>
                                </p>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                <?php elseif ($c['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo format_date($c['created_at']); ?></small>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php if ($c['status'] !== 'approved'): ?>
                                    <a href="index.php?action=approve&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-success me-1" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($c['status'] !== 'rejected'): ?>
                                    <a href="index.php?action=reject&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-warning me-1" title="Hide/Reject">
                                        <i class="bi bi-eye-slash"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No comments found matching this filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

