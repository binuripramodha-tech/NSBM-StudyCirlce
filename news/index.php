<?php
$admin_page_title = "Manage News Articles";
require_once __DIR__ . '/../includes/admin_header.php';

if (isset($_GET['toggle_featured']) && filter_input(INPUT_GET, 'toggle_featured', FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['toggle_featured'];
    $pdo->prepare("UPDATE news SET is_featured = 1 - is_featured WHERE id = ?")->execute([$id]);
    set_flash('success', 'Featured status updated.');
    header("Location: index.php");
    exit;
}

if (isset($_GET['toggle_breaking']) && filter_input(INPUT_GET, 'toggle_breaking', FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['toggle_breaking'];
    $pdo->prepare("UPDATE news SET is_breaking = 1 - is_breaking WHERE id = ?")->execute([$id]);
    set_flash('success', 'Breaking news status updated.');
    header("Location: index.php");
    exit;
}

$q = trim($_GET['q'] ?? '');
$cat = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$status = $_GET['status'] ?? '';

$conditions = ["1=1"];
$params = [];

if (!empty($q)) {
    $conditions[] = "(n.title LIKE ? OR n.content LIKE ?)";
    $like = "%{$q}%";
    $params[] = $like;
    $params[] = $like;
}

if ($cat) {
    $conditions[] = "n.category_id = ?";
    $params[] = $cat;
}

if (!empty($status) && in_array($status, ['published', 'draft', 'archived'])) {
    $conditions[] = "n.status = ?";
    $params[] = $status;
}

$where_clause = implode(" AND ", $conditions);

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$stmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name, u.name AS author_name,
           (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id) AS comment_count
    FROM news n
    LEFT JOIN categories c ON n.category_id = c.id
    LEFT JOIN users u ON n.author_id = u.id
    WHERE {$where_clause}
    ORDER BY n.created_at DESC
");
$stmt->execute($params);
$news_list = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-newspaper text-success me-2"></i> Manage News Articles</h2>
        <p class="text-muted small mb-0">Create, edit, toggle visibility, and organize official campus news releases.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-nsbm rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Add New Article
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Search headline or keyword..." value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($cat == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="published" <?php echo ($status === 'published') ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo ($status === 'draft') ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-nsbm w-100">Filter</button>
            <?php if (!empty($q) || $cat || !empty($status)): ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70px;">Cover</th>
                    <th>Headline</th>
                    <th>Category</th>
                    <th>Reads</th>
                    <th>Badges</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($news_list)): ?>
                    <?php foreach ($news_list as $row): ?>
                        <tr>
                            <td>
                                <img src="<?php echo get_image_url($row['image']); ?>" alt="Cover" class="rounded-2 object-fit-cover shadow-sm" style="width: 60px; height: 45px;" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                            </td>
                            <td>
                                <a href="../../article.php?id=<?php echo $row['id']; ?>" target="_blank" class="fw-bold text-navy text-decoration-none">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </a>
                                <small class="text-muted d-block"><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($row['author_name'] ?? 'Admin'); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category_name'] ?? 'Unassigned'); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border"><?php echo number_format($row['views']); ?></span>
                            </td>
                            <td>
                                <a href="index.php?toggle_featured=<?php echo $row['id']; ?>" class="badge <?php echo $row['is_featured'] ? 'bg-primary' : 'bg-light text-muted border'; ?> text-decoration-none" title="Click to toggle featured">
                                    <i class="bi bi-star-fill me-1"></i> Featured
                                </a>
                                <a href="index.php?toggle_breaking=<?php echo $row['id']; ?>" class="badge <?php echo $row['is_breaking'] ? 'bg-danger' : 'bg-light text-muted border'; ?> text-decoration-none" title="Click to toggle breaking">
                                    <i class="bi bi-lightning-fill me-1"></i> Breaking
                                </a>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'published'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo format_date($row['created_at']); ?></small>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="../../article.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-sm btn-light border me-1" title="View live">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit story">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger btn-confirm-delete" title="Delete story">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-newspaper display-4 text-secondary d-block mb-3"></i>
                            <h6>No news articles found.</h6>
                            <a href="create.php" class="btn btn-nsbm btn-sm rounded-pill mt-2">Publish First Story</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

