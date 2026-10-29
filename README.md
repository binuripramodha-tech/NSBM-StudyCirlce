# NSBM NewsDesk – Campus News Management System
**NSBM Green University – Faculty of Computing**  
**Module:** Web and Mobile Application Development  
**Batch:** 26.1 IFSL  
**Assignment:** Final Project Assignment (Project Topic 1)

---

## 1. Project Overview
**NSBM NewsDesk** is a comprehensive, production-ready web application designed for NSBM Green University to publish, organize, and manage campus news, departmental circulars, faculty breakthroughs, athletic events, and student club activities. 

All content, branding, color schemes (`#008751` NSBM Green, `#002147` NSBM Navy), and high-resolution photography are authentically sourced from the official university website ([www.nsbm.ac.lk](https://www.nsbm.ac.lk)).

---

## 2. Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, Chart.js (Analytics)
- **Backend:** PHP 8+ (Modular architecture, Prepared Statements, Secure Sessions, Flash Notifications)
- **Database:** MySQL / MariaDB (Relational schema with foreign keys and cascade rules)
- **Security:** Bcrypt Password Hashing (`password_hash`), XSS sanitization, CSRF & SQL Injection mitigation

---

## 3. Implemented Features Matrix

### 3.1 Administrator Features
1. **Secure Admin Authentication:** Role-based access control; prevents unauthorized users from accessing the back-office.
2. **News Articles CRUD:**
   - **Create:** Publish new campus articles with title, category, excerpt, HTML body, cover image upload, and preset selection.
   - **Read:** Paginated data table with search and category filtering.
   - **Update:** Edit existing stories, update text, replace image, and change visibility status (Published / Draft).
   - **Delete:** Remove outdated articles with cascade cleanup.
   - **Interactive Toggles:** 1-click toggle for Homepage Hero Feature (`is_featured`) and Breaking News Ticker (`is_breaking`).
3. **Categories Management (CRUD):**
   - Manage university categories (Academic, Computing & Tech, Events & Festivals, Sports & Fitness, Student Life & Clubs, General Announcements).
   - Add new categories, edit descriptions, adjust color badges, and delete unused categories.
4. **Student Submissions Review & Approval Workflow:**
   - Review stories and proposals submitted by campus societies and undergraduates.
   - **Approve:** Automatically publishes the student submission into the live campus news feed and updates status.
   - **Reject:** Marks submission as rejected with optional editorial feedback for the student.
5. **Campus Announcements & Priority Broadcasts:**
   - Publish campus circulars with priorities: **Urgent** (Red), **Important** (Amber), and **Normal** (Green).
   - Target audience categorization (e.g. All Students, Faculty of Computing, Final Year).
6. **Reader Comments Moderation:**
   - Review reader feedback across all articles.
   - Approve, hide/reject, or delete inappropriate remarks.
7. **Readership & Engagement Reports:**
   - Visual Doughnut Chart (Chart.js) showing traffic distribution across categories.
   - Total impressions, average views per article, and comment engagement rate.
   - Top 10 most-read articles leaderboard.
   - **Export to CSV:** Downloadable real-time report spreadsheet.
   - **Print Report:** Dedicated printer-friendly styling.
8. **User Account Administration:**
   - View registered students, student IDs, and faculties.
   - Switch user roles between Student and Administrator.

### 3.2 Student / Reader Features
1. **Student Registration & Login:**
   - Form validation for Name, Student ID, Email, Faculty, and Password confirmation.
   - Auto-subscription to faculty and general announcements upon signup.
2. **Campus News Discovery & Exploration:**
   - **Breaking News Ticker:** Dynamic live ticker at the top of the homepage.
   - **Hero Section:** Large featured headlines with author, date, and views count.
   - **Filter & Search:** Filter by category pills, keyword search, and sorting (Newest, Most Popular, Oldest).
3. **Interactive Article Reading:**
   - Full story view with high-res photography from `nsbm.ac.lk`.
   - Real-time pageview counter increment.
   - Social media share shortcuts (Facebook, X/Twitter, LinkedIn).
   - Related stories recommendations from the same faculty.
4. **1-Click Bookmark System:**
   - AJAX-powered save/unsave toggle without page reloading.
   - Dedicated **Saved Reading List** page (`user/bookmarks.php`).
5. **Discussion & Comments:**
   - Logged-in students can leave feedback, insights, or congratulatory remarks.
   - Comment threads display commenter name, faculty, and elapsed time ("2 hours ago").
6. **Submit Story for Approval:**
   - Student clubs can submit event reports with cover photos (`user/submit_article.php`).
   - Track approval status and editorial feedback in **My Submissions** (`user/my_submissions.php`).
7. **Personalized Category Subscriptions:**
   - Follow specific categories (Computing, Sports, Academic, etc.).
   - Access a customized personalized news feed inside the **Student Dashboard** (`user/dashboard.php`).
8. **Profile & Security Settings:**
   - Update full name, faculty, and student registration number.
   - Update account password with current password verification.

---

## 4. Default Demo Accounts

| Role | Email | Password | Details |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@gmail.com` | `admin123` | Full access to Admin Panel & Content Moderation |
| **Student (Computing)** | `kavindu@gmail.com` | `student123` | Undergrad, Faculty of Computing (ID: 26.1-IFSL-045) |
| **Student (Business)** | `sanduni@gmail.com` | `student123` | Undergrad, Faculty of Business (ID: 26.1-BM-112) |

---

## 5. Local Setup & Execution Guide

### Step 1: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.
   *(Note: The database connection helper in `config/database.php` automatically connects via port 3307 or 3306 with user `root` and password `""`)*.

### Step 2: Database Setup
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
2. Create a new database named `nsbm_newsdesk` (or import `database.sql` directly).
3. Select `nsbm_newsdesk` and click **Import**, choose the file `database.sql` from the project root, and click **Go**.

### Step 3: Access the Web Application
Open your browser and navigate to:
```text
http://localhost/nsbm-newsdesk/
```
Or if running via PHP built-in server:
```bash
php -S localhost:8000
```
Then visit: `http://localhost:8000/`

---

## 6. Directory Structure
```text
c:\Users\DELL\Desktop\102\WEB\Project\
├── index.php                      # Homepage (Breaking news, Hero slider, Latest news, Announcements)
├── news.php                       # All News archive with filters, search, pagination
├── article.php                    # Full article view, view counter, bookmarking, comments
├── category.php                   # Category topic feed with subscription toggle
├── announcements.php              # Campus circulars bulletin board with priority filters
├── events.php                     # Campus conferences & calendar events
├── search.php                     # Multi-resource keyword search
├── login.php                      # Unified login portal (Admin & Student)
├── register.php                   # Student registration with validation
├── logout.php                     # Secure session termination
├── database.sql                   # Clean MySQL database schema & authentic NSBM seed data
├── config/
│   └── database.php               # Multi-port PDO & MySQLi database connection helper
├── includes/
│   ├── header.php                 # Brand navbar, utility bar, user status
│   ├── footer.php                 # University footer, contacts, social links
│   └── functions.php              # Helper utilities (sanitization, dates, time_ago, badges)
├── user/
│   ├── dashboard.php              # Student Dashboard with personalized feed
│   ├── submit_article.php         # Student / Club story submission form
│   ├── my_submissions.php         # Submission approval history & feedback
│   ├── bookmarks.php              # Saved articles reading list
│   ├── subscriptions.php          # Category subscription manager
│   ├── profile.php                # Profile & security settings
│   └── toggle_bookmark.php        # AJAX bookmark toggle endpoint
├── admin/
│   ├── dashboard.php              # Admin overview with Chart.js analytics & queues
│   ├── news/                      # News CRUD (index, create, edit, delete)
│   ├── categories/                # Category CRUD management
│   ├── submissions/               # Student story review & 1-click publishing
│   ├── announcements/             # Announcements & broadcast manager
│   ├── comments/                  # Reader comments moderation
│   ├── reports/                   # Readership analytics, CSV export, print
│   ├── users/                     # User management & role switching
│   └── includes/                  # Admin header, sidebar & footer
└── assets/
    ├── css/style.css              # Custom responsive stylesheet (NSBM Green `#008751`)
    ├── js/main.js                 # AJAX handlers, validation, toast notifications
    ├── images/                    # Authentic images downloaded from www.nsbm.ac.lk
    └── uploads/                   # Storage for user and admin uploaded images
```

---

## 7. Submission Checklist for Batch 26.1 IFSL
- [x] Topic 1: NSBM NewsDesk chosen and fully developed.
- [x] Separate Admin and Student/User interfaces.
- [x] Complete CRUD operations across News, Categories, Announcements, Comments, and Submissions.
- [x] Authentic content, photography, and brand identity from `www.nsbm.ac.lk`.
- [x] PDO MySQL database integration with prepared statements.
- [x] Responsive layout with Bootstrap 5 and custom CSS.
- [x] Live testing completed and fully operational on `http://localhost/nsbm-newsdesk/`.

