<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!is_admin()) {
    header("Location: ../../login.php");
    exit;
}

$admin_page_title = "Readership & Engagement Reports";
require_once __DIR__ . '/../includes/admin_header.php';

$total_views = $pdo->query("SELECT COALESCE(SUM(views), 0) FROM news WHERE status = 'published'")->fetchColumn();
$total_articles = $pdo->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetchColumn();
$avg_views = $total_articles > 0 ? round($total_views / $total_articles) : 0;

$cat_stmt = $pdo->query("
    SELECT c.name, COUNT(n.id) AS article_count, COALESCE(SUM(n.views), 0) AS total_views
    FROM categories c
    LEFT JOIN news n ON n.category_id = c.id AND n.status = 'published'
    GROUP BY c.id, c.name
    ORDER BY total_views DESC
");
$cat_reports = $cat_stmt->fetchAll();

$pie_labels = [];
$pie_views = [];
foreach ($cat_reports as $r) {
    $pie_labels[] = $r['name'];
    $pie_views[] = (int)$r['total_views'];
}

$top_stmt = $pdo->query("
    SELECT n.*, c.name AS category_name,
           (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id) AS comment_count
    FROM news n
    LEFT JOIN categories c ON n.category_id = c.id
    WHERE n.status = 'published'
    ORDER BY n.views DESC
    LIMIT 10
");
$top_articles = $top_stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold text-navy mb-1"><i class="bi bi-graph-up-arrow text-success me-2"></i> Campus Readership & Engagement Analytics</h2>
        <p class="text-muted small mb-0">Official analytics breakdown of student article readership, category traffic, and community engagement.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <span class="text-muted small fw-semibold text-uppercase">Total Campus Impressions</span>
            <h2 class="fw-bold text-navy mt-1 mb-0"><?php echo number_format($total_views); ?></h2>
            <small class="text-success mt-2 d-block"><i class="bi bi-arrow-up-right me-1"></i> Article Pageviews</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <span class="text-muted small fw-semibold text-uppercase">Published Stories</span>
            <h2 class="fw-bold text-navy mt-1 mb-0"><?php echo number_format($total_articles); ?></h2>
            <small class="text-muted mt-2 d-block"><i class="bi bi-journal-check me-1"></i> Across All Faculties</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <span class="text-muted small fw-semibold text-uppercase">Avg. Views Per Story</span>
            <h2 class="fw-bold text-navy mt-1 mb-0"><?php echo number_format($avg_views); ?></h2>
            <small class="text-muted mt-2 d-block"><i class="bi bi-people me-1"></i> Average Engagement</small>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
            <span class="text-muted small fw-semibold text-uppercase">Reader Discussions</span>
            <h2 class="fw-bold text-navy mt-1 mb-0"><?php echo number_format($total_comments); ?></h2>
            <small class="text-info mt-2 d-block"><i class="bi bi-chat-heart me-1"></i> Approved Comments</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h5 class="fw-bold text-navy mb-3 pb-2 border-bottom">
                <i class="bi bi-pie-chart text-primary me-2"></i> Readership by Category
            </h5>
            <div style="height: 280px; position: relative;" class="d-flex align-items-center justify-content-center">
                <canvas id="categoryPieChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h5 class="fw-bold text-navy mb-3 pb-2 border-bottom">
                <i class="bi bi-table text-success me-2"></i> Category Performance Breakdown
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-center">Articles</th>
                            <th class="text-center">Total Views</th>
                            <th class="text-center">Avg. Views</th>
                            <th class="text-end">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cat_reports as $cr): ?>
                            <?php
                                $share = $total_views > 0 ? round(($cr['total_views'] / $total_views) * 100, 1) : 0;
                                $avg_cat = $cr['article_count'] > 0 ? round($cr['total_views'] / $cr['article_count']) : 0;
                            ?>
                            <tr>
                                <td class="fw-semibold text-navy"><?php echo htmlspecialchars($cr['name']); ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?php echo $cr['article_count']; ?></span></td>
                                <td class="text-center fw-bold text-success"><?php echo number_format($cr['total_views']); ?></td>
                                <td class="text-center text-muted"><?php echo number_format($avg_cat); ?></td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                        <div class="progress" style="width: 50px; height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $share; ?>%"></div>
                                        </div>
                                        <small class="fw-bold"><?php echo $share; ?>%</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
    <h5 class="fw-bold text-navy mb-3 pb-2 border-bottom">
        <i class="bi bi-award-fill text-warning me-2"></i> Top 10 Most-Engaged Campus News Releases
    </h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Story Headline</th>
                    <th>Category</th>
                    <th class="text-center">Comments</th>
                    <th class="text-center">Total Views</th>
                    <th>Publish Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_articles as $idx => $art): ?>
                    <tr>
                        <td class="fw-bold text-muted"><?php echo $idx + 1; ?></td>
                        <td>
                            <a href="../../article.php?id=<?php echo $art['id']; ?>" target="_blank" class="fw-bold text-navy text-decoration-none">
                                <?php echo htmlspecialchars($art['title']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge <?php echo get_category_badge($art['category_name']); ?> text-uppercase"><?php echo htmlspecialchars($art['category_name'] ?? 'General'); ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><i class="bi bi-chat-text text-primary me-1"></i> <?php echo $art['comment_count']; ?></span>
                        </td>
                        <td class="text-center fw-bold text-success fs-6">
                            <?php echo number_format($art['views']); ?>
                        </td>
                        <td>
                            <small class="text-muted"><?php echo format_date($art['created_at']); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pieCanvas = document.getElementById('categoryPieChart');
    if (!pieCanvas) return;
    const pieCtx = pieCanvas.getContext('2d');

    function isDarkTheme() {
        return document.documentElement.getAttribute('data-bs-theme') === 'dark';
    }

    function getPieThemeColors() {
        const isDark = isDarkTheme();
        return {
            textColor: isDark ? '#E8F2EE' : '#1F2937',
            borderColor: isDark ? '#2A3B34' : '#FFFFFF',
            colors: isDark 
                ? ['#4CAF7D', '#4A90D9', '#7ED3A6', '#7CB7F3', '#3A9A6F', '#A7B1A9']
                : ['#2E7D5B', '#2563A6', '#A7D7C3', '#93C5FD', '#236B4E', '#6B7280']
        };
    }

    let pieColors = getPieThemeColors();

    const pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($pie_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($pie_views); ?>,
                backgroundColor: pieColors.colors,
                borderWidth: 2,
                borderColor: pieColors.borderColor
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: pieColors.textColor,
                        font: { weight: '600', size: 12 },
                        padding: 14
                    }
                }
            }
        }
    });

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'data-bs-theme') {
                const newPieColors = getPieThemeColors();
                pieChart.data.datasets[0].backgroundColor = newPieColors.colors;
                pieChart.data.datasets[0].borderColor = newPieColors.borderColor;
                pieChart.options.plugins.legend.labels.color = newPieColors.textColor;
                pieChart.update('none');
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

