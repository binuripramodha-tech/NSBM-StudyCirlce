<?php
$admin_page_title = "Edit News Article";
require_once __DIR__ . '/../includes/admin_header.php';

$news_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$news_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$article = $stmt->fetch();

if (!$article) {
    set_flash('danger', 'Article not found.');
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft', 'archived']) ? $_POST['status'] : 'published';
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image_name = $article['image'];

    if (empty($title) || empty($content) || !$category_id) {
        $error = "Please fill in the title, category, and article content.";
    } else {

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $new_name = 'news_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $target_dir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $new_name)) {
                    $image_name = $new_name;
                }
            }
        } elseif (!empty($_POST['preset_image'])) {
            $image_name = $_POST['preset_image'];
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

        $upd_stmt = $pdo->prepare("
            UPDATE news
            SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, category_id = ?, status = ?, is_breaking = ?, is_featured = ?
            WHERE id = ?
        ");
        if ($upd_stmt->execute([$title, $slug, $excerpt, $content, $image_name, $category_id, $status, $is_breaking, $is_featured, $news_id])) {
            set_flash('success', 'Article updated successfully!');
            header("Location: index.php");
            exit;
        } else {
            $error = "Failed to update article.";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-pencil text-primary me-2"></i> Edit News Article</h2>
        <p class="text-muted small mb-0">Modify headline, story content, publishing flags, or category taxonomy.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
        <i class="bi bi-arrow-left me-1"></i> Back to News
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
    <form method="POST" action="edit.php?id=<?php echo $news_id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-navy small">Story Headline <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="title" name="title" required value="<?php echo htmlspecialchars($article['title']); ?>">
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label fw-semibold text-navy small">Short Excerpt / Lead</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label fw-semibold text-navy small">Full Body Content (HTML supported) <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="12" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                    <h6 class="fw-bold text-navy mb-3"><i class="bi bi-sliders me-1"></i> Settings</h6>

                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold text-navy">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($article['category_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold text-navy">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published" <?php echo ($article['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo ($article['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?php echo $article['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-semibold text-navy" for="is_featured">
                            Featured on Homepage Hero
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_breaking" name="is_breaking" value="1" <?php echo $article['is_breaking'] ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-semibold text-navy" for="is_breaking">
                            Breaking News Ticker
                        </label>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                    <h6 class="fw-bold text-navy mb-2"><i class="bi bi-image me-1"></i> Cover Photo</h6>
                    <div class="mb-2">
                        <img src="<?php echo get_image_url($article['image']); ?>" alt="Current cover" class="img-fluid rounded-3 mb-2 shadow-sm" style="max-height: 120px; width: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='<?php echo get_image_url(''); ?>';">
                        <small class="text-muted d-block mb-2">Current image: <code><?php echo htmlspecialchars($article['image']); ?></code></small>
                    </div>
                    <div class="mb-2">
                        <label for="image" class="form-label small text-muted">Upload replacement:</label>
                        <input type="file" class="form-control form-control-sm" id="image" name="image" accept="image/png, image/jpeg, image/webp">
                    </div>
                    <div>
                        <label class="form-label small text-muted">Or change to NSBM preset:</label>
                        <select class="form-select form-select-sm" name="preset_image">
                            <option value="">-- Keep Current Image --</option>
                            <option value="campus_hero.jpg">NSBM Green Campus Aerial (uni2.jpg)</option>
                            <option value="microsoft_session.jpg">Microsoft Student Tech Session</option>
                            <option value="film_festival.jpg">NSBM Youth Film Festival</option>
                            <option value="coffee_concepts.jpg">Coffee & Concepts AI Seminar</option>
                            <option value="southern_cross.jpg">Southern Cross University Partnership</option>
                            <option value="nursing_batch.jpg">Faculty of Science Nursing Program</option>
                            <option value="sports_club.jpg">Sports Fiesta & Recreation Club</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-nsbm w-100 py-3 fw-bold shadow">
                    <i class="bi bi-check-lg me-1"></i> Update Story
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

