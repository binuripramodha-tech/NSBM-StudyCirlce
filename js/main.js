
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    const bookmarkBtns = document.querySelectorAll('.bookmark-toggle-btn');
    bookmarkBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const newsId = this.getAttribute('data-news-id');
            const icon = this.querySelector('i');
            const self = this;

            const toggleUrl = window.location.pathname.includes('/user/') ? 'toggle_bookmark.php' : 'user/toggle_bookmark.php';
            const redirectLoginUrl = window.location.pathname.includes('/user/') ? '../login.php?msg=login_required' : 'login.php?msg=login_required';

            fetch(toggleUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'news_id=' + encodeURIComponent(newsId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.action === 'added') {
                        icon.classList.remove('bi-bookmark');
                        icon.classList.add('bi-bookmark-fill', 'text-danger');
                        self.classList.add('active');
                        showToast('Article saved to your bookmarks!');
                    } else {
                        icon.classList.remove('bi-bookmark-fill', 'text-danger');
                        icon.classList.add('bi-bookmark');
                        self.classList.remove('active');
                        showToast('Article removed from bookmarks.');
                    }
                } else if (data.status === 'unauthorized') {
                    window.location.href = redirectLoginUrl;
                }
            })
            .catch(error => {
                console.error('Error toggling bookmark:', error);
            });
        });
    });

    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    const getStoredTheme = () => localStorage.getItem('nsbm_theme');
    const setStoredTheme = theme => localStorage.setItem('nsbm_theme', theme);

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        if (storedTheme) {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const applyTheme = function (theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        setStoredTheme(theme);
        updateThemeToggleUI(theme);
    };

    function updateThemeToggleUI(theme) {
        const isDark = (theme === 'dark');
        const toggleButtons = document.querySelectorAll('.theme-toggle-btn');
        toggleButtons.forEach(btn => {
            const icon = btn.querySelector('.theme-toggle-icon');
            const text = btn.querySelector('.theme-toggle-text');
            if (icon) {
                if (isDark) {
                    icon.className = 'bi bi-sun-fill theme-toggle-icon text-warning';
                } else {
                    icon.className = 'bi bi-moon-stars-fill theme-toggle-icon text-primary';
                }
            }
            if (text) {
                text.remove();
            }
            btn.setAttribute('title', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
            btn.setAttribute('aria-label', isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode');
        });
    }

    const currentTheme = document.documentElement.getAttribute('data-bs-theme') || getPreferredTheme();
    applyTheme(currentTheme);

    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const nextTheme = (activeTheme === 'dark') ? 'light' : 'dark';
            applyTheme(nextTheme);
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (!getStoredTheme()) {
            applyTheme(getPreferredTheme());
        }
    });

    function showToast(message) {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-white bg-dark border-0 shadow';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi bi-info-circle-fill text-success me-2 fs-6"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        container.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
});

