<?php
$admin_page_title = "Manage System Users";
require_once __DIR__ . '/../includes/admin_header.php';

if (isset($_GET['toggle_role']) && filter_input(INPUT_GET, 'toggle_role', FILTER_VALIDATE_INT)) {
    $u_id = (int)$_GET['toggle_role'];
    if ($u_id === current_user()['id']) {
        set_flash('danger', 'You cannot change your own administrative role.');
    } else {
        $u_stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $u_stmt->execute([$u_id]);
        $curr_role = $u_stmt->fetchColumn();

        if ($curr_role) {
            $new_role = ($curr_role === 'admin') ? 'student' : 'admin';
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $u_id]);
            set_flash('success', "User role updated to {$new_role}.");
        }
    }
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $faculty = trim($_POST['faculty'] ?? 'University Administration');
    $admin_id = trim($_POST['admin_id'] ?? 'ADM-' . rand(100, 999));

    if (empty($name) || empty($email) || empty($password)) {
        set_flash('danger', 'Please provide name, email, and password for the new administrator.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('danger', 'Please enter a valid email address.');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters long.');
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            set_flash('danger', 'An account with this email address already exists.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, student_id, faculty, password, role)
                VALUES (?, ?, ?, ?, ?, 'admin')
            ");
            if ($stmt->execute([$name, $email, $admin_id, $faculty, $hashed])) {
                set_flash('success', "New administrator account for '{$name}' created successfully!");
            } else {
                set_flash('danger', 'Failed to create administrator account. Please try again.');
            }
        }
    }
    header("Location: index.php");
    exit;
}

$search = trim($_GET['search'] ?? '');
$sql = "
    SELECT u.*,
           (SELECT COUNT(*) FROM bookmarks b WHERE b.user_id = u.id) AS bookmark_count,
           (SELECT COUNT(*) FROM article_submissions a WHERE a.user_id = u.id) AS submission_count
    FROM users u
";

$params = [];
if (!empty($search)) {
    $sql .= " WHERE u.name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ?";
    $like = "%{$search}%";
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-people text-info me-2"></i> Manage System Users</h2>
        <p class="text-muted small mb-0">Undergraduate accounts, faculty staff, student coordinators, and administrator privileges.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark border fs-6 py-2 px-3">
            Total Users: <?php echo count($users); ?>
        </span>
        <button type="button" class="btn btn-nsbm rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-person-plus-fill me-1"></i> Add New Admin
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="index.php" class="row g-2 align-items-center">
        <div class="col-md-10">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search text-success"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by name, student ID or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-nsbm w-100">Search</button>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>User / Student</th>
                    <th>Faculty</th>
                    <th>Email Address</th>
                    <th>Submissions</th>
                    <th>Bookmarks</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 38px; height: 38px;">
                                    <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <span class="fw-bold text-navy d-block"><?php echo htmlspecialchars($u['name']); ?></span>
                                    <small class="text-muted"><?php echo htmlspecialchars($u['student_id'] ?? 'N/A'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted"><?php echo htmlspecialchars($u['faculty'] ?? 'General'); ?></small>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($u['email']); ?></code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?php echo $u['submission_count']; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?php echo $u['bookmark_count']; ?></span>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">Administrator</span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Student</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?php echo format_date($u['created_at']); ?></small>
                        </td>
                        <td class="text-end">
                            <?php if ($u['id'] !== current_user()['id']): ?>
                                <a href="index.php?toggle_role=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="return confirm('Change user role between Admin and Student?');">
                                    <i class="bi bi-arrow-repeat me-1"></i> Switch Role
                                </a>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-navy" id="addAdminModalLabel">
                    <i class="bi bi-person-plus-fill text-success me-2"></i> Add New Administrator
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="add_admin">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="admin_name" class="form-label small fw-semibold text-navy">Full Name</label>
                        <input type="text" class="form-control" id="admin_name" name="name" placeholder="e.g. Dr. Nuwan Fernando" required>
                    </div>

                    <div class="mb-3">
                        <label for="admin_email" class="form-label small fw-semibold text-navy">Official Email Address</label>
                        <input type="email" class="form-control" id="admin_email" name="email" placeholder="name@gmail.com" required>
                        <small class="text-muted">Must be a unique email address used to log in.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="admin_id" class="form-label small fw-semibold text-navy">Staff / Admin ID</label>
                            <input type="text" class="form-control" id="admin_id" name="admin_id" placeholder="e.g. ADM-005" value="ADM-<?php echo rand(100, 999); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="admin_faculty" class="form-label small fw-semibold text-navy">Department / Faculty</label>
                            <select class="form-select" id="admin_faculty" name="faculty">
                                <option value="University Administration">University Administration</option>
                                <option value="Faculty of Computing">Faculty of Computing</option>
                                <option value="Faculty of Business">Faculty of Business</option>
                                <option value="Faculty of Engineering">Faculty of Engineering</option>
                                <option value="Faculty of Science">Faculty of Science</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_password" class="form-label small fw-semibold text-navy">Account Password</label>
                        <input type="password" class="form-control" id="admin_password" name="password" placeholder="At least 6 characters" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-nsbm rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Create Admin Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

