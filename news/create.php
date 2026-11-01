<?php
$admin_page_title = "Create News Article";
require_once __DIR__ . '/../includes/admin_header.php';

$error = '';
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image_name = 'campus_hero.jpg';

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
        $admin_id = current_user()['id'];

        $stmt = $pdo->prepare("
            INSERT INTO news (title, slug, excerpt, content, image, category_id, author_id, status, is_breaking, is_featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$title, $slug, $excerpt, $content, $image_name, $category_id, $admin_id, $status, $is_breaking, $is_featured])) {
            set_flash('success', 'News story published successfully!');
            header("Location: index.php");
            exit;
        } else {
            $error = "Failed to save article into database.";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-pencil-square text-success me-2"></i> Publish News Story</h2>
        <p class="text-muted small mb-0">Compose and publish an official campus announcement or departmental headline.</p>
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
    <form method="POST" action="create.php" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row g-4">

            <div class="col-lg-8">
                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold text-navy small">Story Headline <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="Enter headline title..." required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    <div class="invalid-feedback">Headline title is required.</div>
                </div>

                <div class="mb-3">
                    <label for="excerpt" class="form-label fw-semibold text-navy small">Short Excerpt / Summary</label>
                    <textarea class="form-control" id="excerpt" name="excerpt" rows="2" placeholder="Brief 1-2 sentence lead paragraph for news previews..."><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label fw-semibold text-navy small">Full Body Content (HTML supported) <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="12" placeholder="Write the complete story text with paragraphs, bullet points, or quotes..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    <div class="invalid-feedback">Content is required.</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-light border-0 rounded-4 p-3 mb-3">
                    <h6 class="fw-bold text-navy mb-3"><i class="bi bi-sliders me-1"></i> Publishing Settings</h6>

                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold text-navy">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label small fw-semibold text-navy">Visibility Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="published">Published (Visible to all)</option>
                            <option value="draft">Draft (Saved only in Admin)</option>
                        </select>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1">
                        <label class="form-check-label small fw-semibold text-navy" for="is_featured">
                            <i class="bi bi-star-fill text-warning me-1"></i> Feature on Homepage Hero
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_breaking" name="is_breaking" value="1">
                        <label class="form-check-label small fw-semibold text-navy" for="is_breaking">
                            <i class="bi bi-lightning-charge-fill text-danger me-1"></i> Display in Breaking Ticker
                        </label>
                    </div>
                </div>

                <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                    <h6 class="fw-bold text-navy mb-2"><i class="bi bi-image me-1"></i> Article Cover Image</h6>
                    <div class="mb-3">
                        <label for="image" class="form-label small text-muted">Upload custom photo:</label>
                        <input type="file" class="form-control form-control-sm" id="image" name="image" accept="image/png, image/jpeg, image/webp">
                    </div>
                    <div>
                        <label class="form-label small text-muted">Or pick an authentic NSBM preset:</label>
                        <select class="form-select form-select-sm" name="preset_image">
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
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Publish Article
                </button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

