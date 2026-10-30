<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = "NSBM NewsDesk - Official Campus News Portal";
$user = is_logged_in() ? current_user() : null;
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link rel="icon" type="image/jpeg" href="assets/images/logo_newsfeed.jpg">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">

    
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
</head>
<body style="overflow-x: hidden;">

    <div class="video-hero">
        
     
        <div class="video-bg-container">
            <video class="video-bg" autoplay loop muted playsinline poster="assets/images/campus_hero.jpg">
                <source src="assets/videos/waves.mp4" type="video/mp4">
                Your browser does not support HTML5 background video.
            </video>
          
            <div class="video-overlay"></div>
        </div>

        
        <header class="landing-nav">
            <a href="landing.php" class="brand">
                <img src="assets/images/logo_newsfeed.jpg" alt="NSBM NewsDesk Corner Logo" class="landing-corner-logo">
                <div>
                    <div class="brand-title-text">NSBM NewsDesk</div>
                    <div class="brand-subtitle-text">Green University</div>
                </div>
            </a>

            <nav class="landing-nav-links">
                <a href="index.php" class="landing-nav-link">News Feed</a>
                <a href="news.php" class="landing-nav-link">Archive &amp; Categories</a>
                <a href="announcements.php" class="landing-nav-link">Circulars</a>
                <a href="events.php" class="landing-nav-link">Events</a>
                <a href="user/dashboard.php" class="landing-nav-link">Student Portal</a>
                
            </nav>

            <div class="landing-nav-actions">
                
                <button class="theme-toggle-btn rounded-circle" type="button" aria-label="Toggle Theme" title="Toggle Theme">
                    <i class="bi bi-moon-stars-fill theme-toggle-icon"></i>
                </button>

                <?php if (is_logged_in()): ?>
                    <a href="user/dashboard.php" class="btn btn-sm btn-nav-login d-flex align-items-center gap-1">
                        <i class="bi bi-person-circle text-success"></i>
                        <span><?php echo htmlspecialchars($user['name'] ?? 'Dashboard'); ?></span>
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill" title="Log Out">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <button type="button" class="btn-nav-login" onclick="openModal('loginModal')">Login</button>
                    <button type="button" class="btn-nav-register" onclick="openModal('registerModal')">Register</button>
                <?php endif; ?>
            </div>
        </header>

        
        <main class="landing-hero-center">
            <?php display_flash(); ?>

            <div class="landing-pill">
                <span>●</span> Campus Communications &amp; Media 2026
            </div>
            
            <h1 class="landing-hero-title">
                The Living Pulse of <br><span>NSBM Green University</span>
            </h1>

            <p class="landing-hero-subtitle">
                Stay connected with verified faculty announcements, research breakthroughs, campus life stories, and student innovations — all curated in one interactive news platform.
            </p>

            <div class="landing-hero-cta">
                <?php if (is_logged_in()): ?>
                    <a href="index.php" class="btn-hero-primary">
                        <span>Explore News Feed</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="user/dashboard.php" class="btn-hero-secondary">
                        <span>Student Dashboard</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                <?php else: ?>
                    <button type="button" class="btn-hero-primary" onclick="openModal('registerModal')">
                        <span>Create Free Account</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                    <a href="index.php" class="btn-hero-secondary">
                        <span>Browse News Feed</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </main>

        
        <section class="landing-features-grid">
            <div class="landing-feature-card">
                <div class="landing-feature-icon">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <h3 class="landing-feature-title">Verified Circulars</h3>
                <p class="landing-feature-text">
                    Direct announcements from administration and faculty deans categorized by urgent, important, and normal priorities.
                </p>
            </div>

            <div class="landing-feature-card">
                <div class="landing-feature-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <h3 class="landing-feature-title">Student Submissions</h3>
                <p class="landing-feature-text">
                    Undergraduates and campus societies can submit event reports, articles, and club stories directly for editorial review.
                </p>
            </div>

            <div class="landing-feature-card">
                <div class="landing-feature-icon">
                    <i class="bi bi-bookmark-heart-fill"></i>
                </div>
                <h3 class="landing-feature-title">Personalized Feed</h3>
                <p class="landing-feature-text">
                    Follow specific academic faculties and topics (Computing, Business, Engineering, Sports) with 1-click bookmarks.
                </p>
            </div>
        </section>

        
        <footer class="landing-footer">
            <div>&copy; 2026 NSBM Green University NewsDesk. Faculty of Computing (26.1 IFSL).</div>
            <div class="landing-footer-links">
                <a href="index.php">News Feed</a>
                <a href="news.php">Archive</a>
                <a href="announcements.php">Circulars</a>
                <a href="events.php">Events</a>
            </div>
        </footer>
    </div>

    
    <div class="modal-backdrop" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
        <div class="modal-card">
            <button type="button" class="modal-close" onclick="closeModal('loginModal')" aria-label="Close modal">&times;</button>
            
            <div class="modal-header">
                <span class="modal-badge">Student &amp; Staff Login</span>
                <h2 class="modal-title" id="loginTitle">Welcome Back</h2>
                <p class="modal-subtitle">Sign in to access your personalized feed, bookmarks, and submissions.</p>
            </div>

            
            <form id="loginForm" method="POST" action="login.php">
                <input type="hidden" name="redirect" value="index.php">

                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="email" class="form-control" placeholder="name@gmail.com" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <div class="form-row-remember">
                    <label class="form-checkbox">
                        <input type="checkbox" id="rememberMe" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="login.php" class="form-link">Need Help?</a>
                </div>

                <button type="submit" class="btn-modal-submit">Sign In to NewsDesk</button>

                <div class="modal-switch-prompt">
                    Don't have an account yet? 
                    <button type="button" class="modal-switch-btn" onclick="switchToModal('loginModal', 'registerModal')">Register now</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="registerModal" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
        <div class="modal-card">
            <button type="button" class="modal-close" onclick="closeModal('registerModal')" aria-label="Close modal">&times;</button>
            
            <div class="modal-header">
                <span class="modal-badge">Join NSBM NewsDesk</span>
                <h2 class="modal-title" id="registerTitle">Create Account</h2>
                <p class="modal-subtitle">Register to bookmark articles, submit stories, and follow campus news.</p>
            </div>

            
            <form id="registerForm" method="POST" action="register.php">
                <div class="form-group">
                    <label class="form-label" for="regUsername">Full Name</label>
                    <input type="text" id="regUsername" name="name" class="form-control" placeholder="e.g. Kavindu Perera" required autocomplete="name">
                </div>

                <div class="form-group">
                    <label class="form-label" for="regFaculty">Faculty</label>
                    <select id="regFaculty" name="faculty" class="form-control form-select" required>
                        <option value="Faculty of Computing" selected>Faculty of Computing</option>
                        <option value="Faculty of Business">Faculty of Business</option>
                        <option value="Faculty of Engineering">Faculty of Engineering</option>
                        <option value="Faculty of Science">Faculty of Science</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="regEmail">Email Address</label>
                    <input type="email" id="regEmail" name="email" class="form-control" placeholder="yourname@gmail.com" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label" for="regPassword">Password</label>
                    <input type="password" id="regPassword" name="password" class="form-control" placeholder="Create a secure password" minlength="6" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="regConfirmPassword">Confirm Password</label>
                    <input type="password" id="regConfirmPassword" name="confirm_password" class="form-control" placeholder="Re-enter password" minlength="6" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn-modal-submit">Complete Registration</button>

                <div class="modal-switch-prompt">
                    Already registered? 
                    <button type="button" class="modal-switch-btn" onclick="switchToModal('registerModal', 'loginModal')">Login here</button>
                </div>
            </form>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                const firstInput = modal.querySelector('input');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 150);
                }
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
                if (!document.querySelector('.modal-backdrop.active')) {
                    document.body.style.overflow = '';
                }
            }
        }

        function switchToModal(fromId, toId) {
            closeModal(fromId);
            setTimeout(() => {
                openModal(toId);
            }, 200);
        }

        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                closeModal(e.target.id);
            }
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal-backdrop.active');
                if (activeModal) {
                    closeModal(activeModal.id);
                }
            }
        });

   
        function handleGoogleAuth(actionType) {
            const email = prompt("Enter your Gmail address for fast Google Sign-In:", "student@gmail.com");
            if (email) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = (actionType === 'register') ? 'register.php' : 'login.php';

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'email';
                emailInput.value = email;
                form.appendChild(emailInput);

                const passInput = document.createElement('input');
                passInput.type = 'hidden';
                passInput.name = 'password';
                passInput.value = 'student123';
                form.appendChild(passInput);

                if (actionType === 'register') {
                    const nameInput = document.createElement('input');
                    nameInput.type = 'hidden';
                    nameInput.name = 'name';
                    nameInput.value = email.split('@')[0];
                    form.appendChild(nameInput);

                    const facultyInput = document.createElement('input');
                    facultyInput.type = 'hidden';
                    facultyInput.name = 'faculty';
                    facultyInput.value = 'Faculty of Computing';
                    form.appendChild(facultyInput);

                    const confirmPass = document.createElement('input');
                    confirmPass.type = 'hidden';
                    confirmPass.name = 'confirm_password';
                    confirmPass.value = 'student123';
                    form.appendChild(confirmPass);
                }

                document.body.appendChild(form);
                form.submit();
            }
        }

       
        document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
                const next = (current === 'dark') ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', next);
                localStorage.setItem('nsbm_theme', next);
                
                const icon = this.querySelector('.theme-toggle-icon');
                if (icon) {
                    if (next === 'dark') {
                        icon.className = 'bi bi-sun-fill theme-toggle-icon text-warning';
                    } else {
                        icon.className = 'bi bi-moon-stars-fill theme-toggle-icon text-primary';
                    }
                }
            });
        });

     
        document.addEventListener('DOMContentLoaded', () => {
            const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const icon = document.querySelector('.theme-toggle-icon');
            if (icon && current === 'dark') {
                icon.className = 'bi bi-sun-fill theme-toggle-icon text-warning';
            }
        });
    </script>
</body>
</html>
