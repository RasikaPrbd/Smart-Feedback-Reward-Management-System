# Smart Feedback & Reward Management System

A web-based customer feedback collection and incentive management platform developed for academic evaluation (HNDIT Final Year Project) and portfolio demonstration. The system bridges the gap between organizations seeking customer insights and respondents seeking tangible rewards for their feedback.

---

## Table of Contents
1. [Project Overview](#project-overview)
2. [Main Objectives](#main-objectives)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Key Features](#key-features)
5. [Technology Stack](#technology-stack)
6. [System Requirements](#system-requirements)
7. [Installation & Setup (WampServer)](#installation--setup-wampserver)
8. [Database Setup](#database-setup)
9. [Running the Application](#running-the-application)
10. [Project Directory Structure](#project-directory-structure)
11. [Security Implementations](#security-implementations)
12. [Future Enhancements](#future-enhancements)
13. [Author & Academic Information](#author--academic-information)

---

## Project Overview

Customer feedback is vital for service quality improvement, but user participation rates are traditionally low due to lack of engagement incentives. 

The **Smart Feedback & Reward Management System** addresses this problem by integrating structured feedback collection with a point-based gamified rewards marketplace. Companies publish targeted surveys with customizable question formats, while customers earn reward points upon survey completion that can be redeemed for company vouchers and gift codes. A centralized administration portal oversees platform integrity, manages users, and moderates content.

---

## Main Objectives

- **Automated Feedback Collection:** Provide organizations with a self-service module to design, deploy, and monitor multi-format customer surveys.
- **Incentivized User Participation:** Motivate customer engagement by automatically crediting points upon validated survey submission.
- **Transactional Reward Marketplace:** Ensure secure, atomic redemption of points for unique voucher codes, eliminating race conditions or point overdrafts.
- **Data Integrity & Security:** Implement role-based authentication, anti-tampering CSRF defenses, prepared statements against SQL injection, and duplicate response prevention.
- **Analytical Reporting:** Deliver aggregated response analytics and visual distribution reports to help companies make data-driven decisions.

---

## User Roles & Permissions

The platform enforces strict role separation across three dedicated portals:

| Role | Portal Entry | Core Responsibilities |
| :--- | :--- | :--- |
| **Customer / User** | `/user/login.php` | Browse available surveys, submit responses, track earned points, redeem rewards, and review past submission responses. |
| **Company** | `/company/login.php` | Create and manage surveys, configure questions/options, publish reward vouchers, track response reports, and view detailed analytics. |
| **Administrator** | `/admin/login.php` | Monitor platform metrics, manage user/company account statuses (activate/deactivate), archive/restore rewards, and review redemption audit trails. |

---

## Key Features

### 1. User / Customer Portal
- **Secure Registration & Authentication:** Input validation, matching password confirmation, bcrypt password hashing, and session fixation protection.
- **Available Surveys Marketplace:** Filter and sort active surveys by newest, oldest, expiring soon, or reward points value.
- **Dynamic Survey Completion:** Supports Multiple Choice (MCQ), 1–5 Star Rating, and Open-Ended Text questions with client-side and server-side validation.
- **Duplicate Prevention:** Prevents multiple submissions by the same user on the same survey.
- **Atomic Reward Redemption:** Modal confirmation dialog with transactional points deduction (`UPDATE users SET points = points - ? WHERE points >= ?`) and unique voucher coupon generation.
- **Response History & Review:** Dedicated read-only view displaying previously submitted answers alongside survey metadata.

### 2. Company Portal
- **Survey Lifecycle Management:** Automated status transitions (surveys remain in `draft` until at least 3 questions are added, transitioning to `active`).
- **Interactive Question Builder:** Real-time question creation supporting MCQ with custom answer options, star ratings, and text fields.
- **Rewards Management:** Create rewards with custom point thresholds; live preview cards with status indicators (e.g., "Archived by Admin").
- **Analytics & Reporting:** Aggregated survey completion counters, response breakdowns, and detailed individual respondent drill-downs.

### 3. Administrator Portal
- **System Dashboard:** High-level platform statistics (total users, companies, active surveys, total redemptions).
- **User & Company Moderation:** One-click activation and deactivation toggle switches with CSRF protection.
- **Platform Surveys Oversight:** Administrative review and cascading survey deletion.
- **Reward Moderation:** Archive and restore reward listings to preserve catalog safety.
- **Redemption Logs:** Complete platform-wide redemption audit table.

---

## Technology Stack

- **Backend:** PHP 8.x (Vanilla / Procedural with MySQLi Prepared Statements)
- **Database:** MySQL 8.x / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (ES6)
- **UI Framework:** Bootstrap 5.3.6 & Bootstrap Icons 1.11.3 (via jsDelivr CDN)
- **Development Environment:** WampServer (Apache + MySQL/MariaDB + PHP) on Windows

---

## System Requirements

- **Operating System:** Windows 10/11, macOS, or Linux
- **Web Server:** Apache 2.4+ (included with WampServer / XAMPP)
- **PHP Version:** PHP 8.1 or higher (configured with `mysqli` and `session` extensions enabled)
- **Database Server:** MySQL 8.0+ or MariaDB 10.4+
- **Web Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari (modern version with JavaScript enabled)

---

## Installation & Setup (WampServer)

1. **Install WampServer:**
   - Download and install WampServer from [wampserver.com](https://www.wampserver.com/).
   - Ensure the WampServer icon in the Windows taskbar turns green (Apache and MySQL services running).

2. **Clone / Place the Project in Web Root:**
   - Navigate to your WAMP `www` directory:
     ```text
     C:\wamp64\www\
     ```
   - Clone or copy this repository into a folder named `feedback_system`:
     ```text
     C:\wamp64\www\feedback_system\
     ```

3. **Verify Database Configuration:**
   - Open `config/db.php` in a code editor:
     ```php
     <?php
     $conn = new mysqli("localhost", "root", "", "feedback_system");

     if ($conn->connect_error) {
         die("Connection failed: " . $conn->connect_error);
     }
     ?>
     ```
   - If your MySQL server uses a non-empty root password or a custom port, update the parameters accordingly.

---

## Database Setup

1. **Open phpMyAdmin:**
   - In your browser, navigate to: `http://localhost/phpmyadmin`
   - Log in with your MySQL credentials (default user: `root`, password: *(blank)*).

2. **Import the SQL File:**
   - Click on the **Import** tab in phpMyAdmin.
   - Click **Choose File** and select:
     ```text
     feedback_system/database/feedback_system.sql
     ```
   - Click **Import** (or **Go**).
   - The script will automatically create the `feedback_system` database and all 10 required tables:
     - `admins`
     - `companies`
     - `users`
     - `surveys`
     - `questions`
     - `options`
     - `responses`
     - `answers`
     - `rewards`
     - `redemptions`
   - Pre-populated demo seed records will be imported for testing.

---

## Running the Application

1. Ensure WampServer is running.
2. Open your web browser and navigate to:
   ```text
   http://localhost/feedback_system/
   ```
3. Use the landing page portal cards to access the relevant login interface:
   - **User Portal:** `http://localhost/feedback_system/user/login.php`
   - **Company Portal:** `http://localhost/feedback_system/company/login.php`
   - **Admin Portal:** `http://localhost/feedback_system/admin/login.php`

> **Note on Login Credentials:**  
> Demo accounts are included in `database/feedback_system.sql` (e.g., Admin email: `admin@mail.com`, Company email: `abc@mail.com`, User email: `kasun@mail.com`). Alternatively, you can register new User or Company accounts directly through their respective registration forms.

---

## Project Directory Structure

```text
feedback_system/
├── admin/                     # Administrator Portal
│   ├── dashboard.php          # Platform statistics & summary
│   ├── delete_survey.php      # Cascading survey deletion handler
│   ├── login.php              # Administrator authentication
│   ├── logout.php             # Admin session termination
│   ├── manage_companies.php   # Company listing & status toggle
│   ├── manage_redemptions.php # System-wide redemption audit log
│   ├── manage_rewards.php     # Global rewards catalog overview
│   ├── manage_surveys.php     # Platform surveys listing & moderation
│   ├── manage_users.php       # User accounts listing & status toggle
│   ├── toggle_company_status.php # AJAX/POST company status toggle
│   ├── toggle_reward_status.php  # Archive / restore reward handler
│   └── toggle_user_status.php    # Activate / deactivate user handler
├── company/                   # Company / Organization Portal
│   ├── add_option.php         # MCQ option addition handler
│   ├── add_question.php       # Interactive question creator
│   ├── analytics.php          # Overview of company response analytics
│   ├── analytics_details.php  # Deep dive into survey questions & results
│   ├── create_reward.php      # Reward voucher creation form
│   ├── create_survey.php      # Survey title, description & expiry setup
│   ├── dashboard.php          # Company analytics & active campaigns
│   ├── delete_option.php      # Delete question option handler
│   ├── delete_question.php    # Delete survey question handler
│   ├── delete_reward.php      # Delete reward handler
│   ├── delete_survey.php      # Transactional survey deletion
│   ├── edit_option.php        # Edit option text handler
│   ├── edit_question.php      # Edit question content
│   ├── edit_reward.php        # Edit reward point values
│   ├── edit_survey.php        # Edit survey metadata
│   ├── login.php              # Company authentication
│   ├── logout.php             # Company session termination
│   ├── manage_options.php     # Question options overview
│   ├── manage_questions.php   # Survey questions list & management
│   ├── manage_rewards.php     # Company rewards list & status view
│   ├── manage_surveys.php     # Survey list (Drafts, Active, Expired)
│   ├── register.php           # Company account registration
│   ├── survey_reports.php     # Exportable report overview
│   ├── view_redemptions.php   # Specific reward redemption inspection
│   ├── view_response_details.php # Single response inspector
│   └── view_responses.php     # Respondent submissions list
├── config/                    # Configuration Files
│   └── db.php                 # Central MySQLi database connection
├── database/                  # Database Schemas & Migrations
│   └── feedback_system.sql    # Complete SQL dump (schema + seed data)
├── includes/                  # Reusable Components & Helpers
│   ├── admin_navbar.php       # Admin navigation bar
│   ├── company_navbar.php     # Company navigation bar
│   ├── csrf_helper.php        # Cryptographic CSRF token generator & validator
│   ├── footer.php             # Shared HTML footer component
│   ├── survey_helper.php      # Survey status & question counter logic
│   └── user_navbar.php        # Customer/User navigation bar
├── user/                      # Customer / Respondent Portal
│   ├── dashboard.php          # User point balance & quick actions
│   ├── login.php              # Customer authentication
│   ├── logout.php             # User session termination
│   ├── my_rewards.php         # Redeemed voucher codes history
│   ├── my_surveys.php         # Completed survey history list
│   ├── redeem_reward.php      # Atomic reward redemption processor
│   ├── register.php           # User account registration with validation
│   ├── submit_survey.php      # Transactional survey submission & points credit
│   ├── take_survey.php        # Multi-question survey participation form
│   ├── view_rewards.php       # Rewards catalog with point requirements
│   ├── view_survey_response.php # Completed response inspection
│   └── view_surveys.php       # Active surveys marketplace
├── .gitignore                 # Git ignore rules for clean repository
├── index.php                  # Public landing page & portal gateway
└── README.md                  # Project documentation & setup manual
```

---

## Security Implementations

1. **SQL Injection Defense:**
   - Parameterized SQL queries using Prepared Statements (`$conn->prepare()`, `bind_param()`, `execute()`) are implemented across authentication, data retrieval, and mutation operations.

2. **Cross-Site Request Forgery (CSRF) Protection:**
   - Forms processing POST submissions generate cryptographically secure tokens via `random_bytes(32)`.
   - Incoming submissions are verified using timing-attack-safe `hash_equals()`.

3. **Password Security:**
   - User and Company passwords are never stored in plain text.
   - Handled with PHP's `password_hash(..., PASSWORD_DEFAULT)` and verified using `password_verify()`.

4. **Session Hardening:**
   - `session_regenerate_id(true)` is executed upon every successful login to protect against session fixation attacks.
   - Role session keys (`admin_id`, `company_id`, `user_id`) strictly isolate authorization boundaries.

5. **Cross-Site Scripting (XSS) Prevention:**
   - User-supplied strings are sanitized with `htmlspecialchars()` before output in HTML attributes or inner content.

6. **Transactional Data Integrity:**
   - Multi-step operations (such as survey submissions and reward redemptions) utilize ACID transactions (`begin_transaction()`, `commit()`, `rollback()`) to eliminate partial writes or race conditions.

---

## Future Enhancements

- **RESTful API Services:** Provide token-authenticated JSON endpoints for mobile application integration.
- **Automated Email Notifications:** Notify customers of newly published surveys matching their profile and deliver redemption voucher codes via email.
- **Enhanced Data Visualizations:** Integrate Chart.js or ApexCharts for dynamic visual analytics dashboards.
- **Exporting Capabilities:** PDF and Excel / CSV export functionality for survey response reports.

---

## Author & Academic Information

- **Project Title:** Smart Feedback & Reward Management System
- **Program:** Higher National Diploma in Information Technology (HNDIT)
- **Student Name:** `[Your Full Name]`
- **Registration / Index No:** `[Your Registration Number]`
- **Institution:** `[Your Institution / Advanced Technological Institute]`
- **Academic Year:** `[Year / Batch]`
- **Supervisor / Evaluator:** `[Supervisor Name]`

---

## License

This project is developed for educational and academic assessment purposes.
