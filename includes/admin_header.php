<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    set_flash('danger', 'Access denied. You must be an administrator to access the admin portal.');
    header("Location: ../../login.php");
    exit;
}

$admin_user = current_user();

$is_admin_subdir = (basename(dirname($_SERVER['PHP_SELF'])) !== 'admin');
$admin_base = $is_admin_subdir ? '../' : './';
$root_base  = $is_admin_subdir ? '../../' : '../';

try {
    $pending_submissions_count = $pdo->query("SELECT COUNT(*) FROM article_submissions WHERE status = 'pending'")->fetchColumn();
    $pending_comments_count = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
} catch (Exception $e) {
    $pending_submissions_count = 0;
    $pending_comments_count = 0;
}

$admin_title = isset($admin_page_title) ? $admin_page_title . " - NewsDesk Admin" : "NewsDesk Admin Panel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($admin_title); ?></title>

    <link rel="icon" type="image/png" href="<?php echo $root_base; ?>assets/images/favicon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="<?php echo $root_base; ?>assets/css/style.css?v=<?php echo @filemtime(__DIR__ . '/../../assets/css/style.css') ?: time(); ?>">

    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('nsbm_theme');
                const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = savedTheme ? savedTheme : (systemDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-bs-theme', theme);
            } catch (e) {}
        })();
    </script>
    <style>
        html, body {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100vw !important;
        }
        body {
            background-color: #f1f5f9;
        }
        .admin-wrapper {
            width: 100%;
            max-width: 100vw;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        .admin-sidebar {
            width: 260px;
            background: #18231F;
            border-right: 1px solid #2A3B34;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding-top: 15px;
            overflow-y: auto;
        }
        .admin-main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            max-width: calc(100% - 260px);
            min-width: 0;
            padding: 24px;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        @media (max-width: 991.98px) {
            .admin-sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }
            .admin-main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <aside class="admin-sidebar shadow">
        <div class="px-4 pb-3 mb-3 border-bottom border-secondary border-opacity-25">
            <a href="<?php echo $root_base; ?>index.php" class="d-flex align-items-center text-white text-decoration-none">
                <img src="<?php echo $root_base; ?>assets/images/logo_nsbm.png" alt="NSBM" style="height: 38px; filter: brightness(0) invert(1);" class="me-2">
                <div>
                    <div class="fw-bold fs-6">NewsDesk</div>
                    <small class="text-success fw-semibold text-uppercase" style="font-size: 0.7rem;">Admin Control</small>
                </div>
            </a>
        </div>

        <nav class="nav flex-column mb-auto">
            <a href="<?php echo $admin_base; ?>dashboard.php" class="admin-nav-item">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo $admin_base; ?>news/index.php" class="admin-nav-item">
                <i class="bi bi-newspaper"></i> Manage News
            </a>
            <a href="<?php echo $admin_base; ?>categories/index.php" class="admin-nav-item">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a href="<?php echo $admin_base; ?>submissions/index.php" class="admin-nav-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-earmark-check"></i> Submissions</span>
                <?php if ($pending_submissions_count > 0): ?>
                    <span class="badge bg-warning text-dark rounded-pill"><?php echo $pending_submissions_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo $admin_base; ?>announcements/index.php" class="admin-nav-item">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="<?php echo $admin_base; ?>comments/index.php" class="admin-nav-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-chat-left-quote"></i> Comments</span>
                <?php if ($pending_comments_count > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?php echo $pending_comments_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo $admin_base; ?>reports/index.php" class="admin-nav-item">
                <i class="bi bi-graph-up-arrow"></i> Readership Reports
            </a>
            <a href="<?php echo $admin_base; ?>users/index.php" class="admin-nav-item">
                <i class="bi bi-people"></i> Manage Users
            </a>
            <hr class="border-secondary border-opacity-25 mx-3 my-3">
            <a href="<?php echo $root_base; ?>index.php" class="admin-nav-item text-success" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> View Live Site
            </a>
            <a href="<?php echo $root_base; ?>logout.php" class="admin-nav-item text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="admin-main-content">

        <div class="d-flex justify-content-between align-items-center bg-white p-3 px-4 rounded-4 shadow-sm mb-4 border">
            <div>
                <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?php echo date('l, F j, Y'); ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">

                <button class="btn btn-sm btn-outline-secondary rounded-pill theme-toggle-btn d-inline-flex align-items-center gap-1 px-3" type="button" aria-label="Toggle Theme">
                    <i class="bi bi-moon-stars-fill theme-toggle-icon"></i>
                    <span class="theme-toggle-text small d-none d-sm-inline">Theme</span>
                </button>

                <div class="text-end d-none d-sm-block">
                    <span class="fw-bold text-navy d-block" style="font-size: 0.9rem;"><?php echo htmlspecialchars($admin_user['name']); ?></span>
                    <small class="badge bg-danger-subtle text-danger border border-danger-subtle">Administrator</small>
                </div>
                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-shield-check fs-5"></i>
                </div>
            </div>
        </div>

        <?php display_flash(); ?>

