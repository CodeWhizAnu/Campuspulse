# 📡 CampusPulse — Smart Campus Problem Radar

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Frontend](https://img.shields.io/badge/Frontend-Vanilla_JS_--_Glassmorphism_CSS-orange?style=flat)](https://developer.mozilla.org/)
[![Architecture](https://img.shields.io/badge/Architecture-100%25_Offline--First-2ea44f?style=flat)](#)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

An offline-first, anonymous complaint management and smart prioritization platform designed for university campuses. Built with pure PHP 8, PDO, MySQL, and a modern custom Glassmorphism UI—requiring zero external cloud APIs or third-party frontend dependencies.

---

## 📌 Problem Statement

Traditional campus grievance systems rely on manual suggestion boxes, unorganized email chains, or fragmented WhatsApp groups. These methods suffer from:
- **Redundant Reports:** Multiple students file identical reports for the same broken utility.
- **Lack of Transparency:** Students receive no status updates after filing.
- **Prioritization Blindness:** Administrators cannot quantify which issues impact the most students.
- **Privacy Hesitancy:** Students fear administrative backlash when reporting sensitive maintenance failures.

**CampusPulse** solves this through community upvoting, duplicate detection, automated priority calculation, and interactive campus heatmapping.

---

## ✨ Key Features

### 👤 Student Experience
- **Optional Anonymity:** Submit infrastructure, hygiene, or electrical complaints without exposing student identity publicly.
- **Smart Duplicate Warning:** An algorithmic text-similarity scan checks if a similar issue exists before submission, directing users to support existing reports instead.
- **"I'm Facing This Too" Upvoting:** Upvote existing complaints to escalate priority without opening duplicate tickets.
- **Anti-Manipulation Lock:** Submitting authors are strictly restricted from upvoting their own reports.
- **Visual Audit Trail:** View timeline progress (`Pending` → `Under Review` → `In Progress` → `Resolved`) complete with administrator resolution remarks.
- **Dynamic Profile Header:** Custom profile avatar and personal dashboard shortcuts embedded in navigation.

### 🛡️ Administrator Operations
- **Algorithmic Priority Queue:** Complaints are dynamically ranked by an automated scoring system (0–100) instead of basic submission time.
- **SVG Campus Heatmap:** View location coordinates mapped to campus sectors with pulsing hotspots showing high-density problem zones.
- **Voter Transparency:** View all verified student PRNs supporting a specific ticket while preserving public anonymity.
- **Resolution Lock:** Status updates are locked once marked `Resolved` to maintain immutable audit trails.

---

## 🧠 Core Algorithms

### 1. Smart Duplicate Detection (`similar_text`)
When a student files an issue, the backend performs a similarity comparison against active tickets within the exact same category and location:

$$\text{Similarity \%} = \text{similar\_text}(\text{New Input}, \text{Existing Record})$$

If similarity exceeds **70%**, the submission intercepts the student with a warning modal linking directly to the existing ticket.

### 2. Smart Priority Scoring Engine (0–100)
Every complaint receives a real-time calculated score:

$$\text{Priority Score} = \min(100, \text{Base Severity} + \text{Recency Boost} + (\text{Student Votes} \times 2))$$

- **Base Severity:** Low = 10, Medium = 30, High = 60, Critical = 100
- **Recency Boost:** +5 points for newly reported issues
- **Community Impact:** +2 points per upvote

---

## 🛠️ Tech Stack

| Layer | Technology | Details |
|---|---|---|
| **Backend** | PHP 8+ | Native PDO, strict types, session-based auth |
| **Database** | MySQL | InnoDB relational schema with cascading foreign keys |
| **Frontend** | HTML5, CSS3, JavaScript | Custom Glassmorphism design system, Vanilla JS, zero CDN dependencies |
| **Mapping** | SVG + CSS | Pure vector campus map with absolute $(X, Y)$ coordinate overlay |
| **Runtime** | Apache (XAMPP / WAMP) | Fully functional on isolated local intranet environments |

---

## 🗄️ Database Architecture

The schema maintains relational integrity across eight core tables:

- `users` — Authentication credentials, role management (`student`, `admin`), hashed passwords.
- `students` — PRN, academic details, local profile photo paths.
- `admins` — Staff credentials and employee identifiers.
- `categories` — Predefined problem types (e.g., Electrical, Plumbing, Labs, Wi-Fi).
- `locations` — Campus zones with defined SVG anchor coordinates (`map_x`, `map_y`).
- `complaints` — Problem descriptions, photos, status, and calculated `priority_score`.
- `complaint_votes` — Upvote registry (`complaint_id`, `student_id`) enforcing uniqueness.
- `complaint_status_history` — Comprehensive audit logs with admin notes and timestamps.

---

## 🚀 Installation & Local Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.0+ and MySQL)
- Git

### 1. Clone the Repository
```bash
git clone [https://github.com/your-username/campuspulse.git](https://github.com/your-username/campuspulse.git)

```

Move the folder to your web server root:

* **Windows:** `C:/xampp/htdocs/campuspulse`
* **Linux:** `/var/www/html/campuspulse`

### 2. Database Setup

1. Start **Apache** and **MySQL** in your XAMPP Control Panel.
2. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Create a new database named `campuspulse_db`.
4. Import the provided SQL dump file:
```text
database/schema.sql

```



### 3. Application Configuration

Verify your database credentials in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'campuspulse_db');
define('DB_USER', 'root');
define('DB_PASS', '');

```

### 4. Configure Upload Permissions

Ensure your web server has write access to image directories:

```bash
chmod -R 775 uploads/complaints uploads/profile

```

### 5. Access the Platform

Navigate to [http://localhost/campuspulse](http://localhost/campuspulse) in any browser.

---

## 🔑 Default Demo Credentials

| Role | Email / Identifier | Password |
| --- | --- | --- |
| **Admin** | `admin@campus.edu` | `admin123` |
| **Student** | `student@campus.edu` | `student123` |

---

## 📂 Project Structure

```text
campuspulse/
├── actions/
│   ├── complaint_action.php       # Report handling & duplicate check
│   ├── status_action.php          # Admin status progression & locking
│   └── vote_action.php            # Voting logic & anti-gaming rules
├── assets/
│   ├── css/
│   │   ├── style.css              # Custom Glassmorphism styles
│   │   └── heatmap.css            # Hotspot and pulse animations
│   ├── images/                    # Campus SVG blueprint & icons
│   └── js/
│       └── main.js                # Modal handlers & client validation
├── config/
│   ├── auth.php                   # Session security & role verification
│   ├── config.php                 # Global constants & application paths
│   └── database.php               # PDO connection instance
├── database/
│   └── schema.sql                 # Complete database export
├── includes/
│   ├── admin_sidebar.php          # Admin dashboard navigation
│   ├── footer.php                 # Global footer
│   ├── header.php                 # Global meta and CSS imports
│   ├── navbar.php                 # Dynamic navigation bar with user pill
│   └── student_sidebar.php        # Student workspace navigation
├── uploads/
│   ├── complaints/                # Uploaded problem photos
│   └── profile/                   # Student profile photos
├── admin_complaint_details.php    # Resolution control room
├── admin_dashboard.php            # Administrative control center
├── complaint_details.php          # Student view, voting & audit trail
├── duplicate_warning.php          # Interactive duplicate interceptor
├── index.php                      # Public landing & resolved showcase
├── report_problem.php             # Complaint intake form
└── student_dashboard.php          # Personal ticket tracker & active list

```

---

## 🔒 Security Practices

* **Prepared Statements (PDO):** Eliminates SQL injection vulnerabilities across all dynamic queries.
* **CSRF Token Validation:** Hidden anti-forgery tokens validate all `POST` state changes.
* **Session Protection:** Session regeneration on login prevents session fixation attacks.
* **Strict File Upload Validation:** File MIME types and sizes (JPEG, PNG, WebP $\le$ 5MB) are verified on the server side prior to saving.
* **XSS Mitigation:** Output sanitization through `htmlspecialchars()` on all dynamic user-submitted strings.

---

## 📄 License

This project is open-source and available under the [MIT License](https://www.google.com/search?q=LICENSE).

```

```