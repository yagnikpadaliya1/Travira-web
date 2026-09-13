# ✈️ Travira — Travel Explorer & Booking System

> A full-stack PHP travel booking web application built on the XAMPP (MySQL + PHP) stack, featuring a customer-facing booking portal and a protected admin dashboard.

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Components & Pages](#-components--pages)
  - [Shared Components](#shared-components)
  - [Customer-Facing Pages](#customer-facing-pages)
  - [Admin Panel Pages](#admin-panel-pages)
- [Workflows](#-workflows)
  - [User Registration Flow](#1-user-registration-flow)
  - [User Login Flow](#2-user-login-flow)
  - [Package Browsing & Search Flow](#3-package-browsing--search-flow)
  - [Booking Flow](#4-booking-flow)
  - [Admin Login Flow](#5-admin-login-flow)
  - [Admin Package Management Flow](#6-admin-package-management-flow)
  - [Admin Booking Management Flow](#7-admin-booking-management-flow)
- [Session Management](#-session-management)
- [Security Practices](#-security-practices)
- [Styling System](#-styling-system)
- [External Dependencies](#-external-dependencies)
- [Setup & Installation](#-setup--installation)
- [Default Credentials](#-default-credentials)

---

## 🌍 Project Overview

**Travira** is a minor-project travel booking system where:

- **Customers** can register, log in, browse travel packages, view package details, and submit bookings.
- **Admins** can log in to a separate protected portal to manage travel packages (full CRUD) and manage customer bookings (update status, delete).

The application uses a traditional multi-page PHP architecture with PDO for database access, PHP sessions for authentication, and vanilla CSS for styling.

---

## 🛠 Tech Stack

| Layer        | Technology                          |
|--------------|--------------------------------------|
| **Server**   | Apache (via XAMPP)                   |
| **Backend**  | PHP 7.4+ (procedural + PDO)          |
| **Database** | MySQL (via XAMPP / phpMyAdmin)       |
| **Frontend** | HTML5, Vanilla CSS, Vanilla JS       |
| **Icons**    | Font Awesome 6.4.0 (CDN)             |
| **Fonts**    | Google Fonts — Inter (CDN)           |
| **Images**   | Unsplash (fallback), local images/   |

---

## 📁 Project Structure

```
php/                              <- Project root (served at localhost/php/)
│
├── index.php                     <- Homepage: hero slider + package listing
├── package_details.php           <- Single package detail page
├── book.php                      <- Booking form (login required)
├── login.php                     <- Customer login page
├── register.php                  <- Customer registration page
├── user_logout.php               <- Customer session logout handler
│
├── config/
│   └── db.php                    <- PDO database connection singleton
│
├── includes/
│   ├── header.php                <- Shared HTML head + navbar (starts session)
│   └── footer.php                <- Shared HTML footer + closing tags
│
├── admin/
│   ├── index.php                 <- Admin login page
│   ├── dashboard.php             <- Admin dashboard (stats + recent bookings)
│   ├── manage_packages.php       <- Package CRUD (Add / Edit / Delete)
│   ├── manage_bookings.php       <- Booking management (status update / delete)
│   └── logout.php                <- Admin session logout handler
│
├── css/
│   ├── style.css                 <- Main stylesheet (2000+ lines, design system)
│   └── admin.css                 <- Admin-specific overrides
│
├── sql/
│   └── create_users_table.sql    <- Migration: users table + bookings FK
│
└── images/
    └── ui/
        ├── travira_logo.png      <- Site logo
        └── new_sky_bg.jpg        <- Background image for auth pages
```

---

## 🗄 Database Schema

Database name: **`travira_db`**

### Table: `packages`

| Column          | Type            | Notes                         |
|-----------------|-----------------|-------------------------------|
| `package_id`    | INT (PK, AI)    | Primary key, auto-increment   |
| `title`         | VARCHAR         | Package name/title            |
| `destination`   | VARCHAR         | Destination location          |
| `price`         | DECIMAL / FLOAT | Price per person (USD)        |
| `duration_days` | INT             | Trip duration in days         |
| `description`   | TEXT            | Full trip description         |
| `image_path`    | VARCHAR         | Relative path or URL to image |

### Table: `bookings`

| Column             | Type          | Notes                                           |
|--------------------|---------------|-------------------------------------------------|
| `booking_id`       | INT (PK, AI)  | Primary key                                     |
| `package_id`       | INT (FK)      | References `packages.package_id`                |
| `user_id`          | INT (FK, NULL)| References `users.user_id` — NULL for guests   |
| `customer_name`    | VARCHAR       | Traveler's full name                            |
| `customer_email`   | VARCHAR       | Traveler's email                                |
| `travel_date`      | DATE          | Desired travel date                             |
| `number_of_people` | INT           | Number of travelers (min 1, max 20)             |
| `status`           | VARCHAR       | `Pending` / `Confirmed` / `Cancelled`           |

### Table: `users`

| Column          | Type         | Notes                              |
|-----------------|--------------|------------------------------------|
| `user_id`       | INT (PK, AI) | Primary key                        |
| `full_name`     | VARCHAR(120) | Customer's full name               |
| `email`         | VARCHAR(180) | Unique — used as login identifier  |
| `password_hash` | VARCHAR(255) | Bcrypt hash via `password_hash()`  |
| `created_at`    | DATETIME     | Defaults to `CURRENT_TIMESTAMP`    |

### Table: `admins`

| Column          | Type         | Notes                             |
|-----------------|--------------|-----------------------------------|
| `admin_id`      | INT (PK, AI) | Primary key                       |
| `username`      | VARCHAR      | Unique admin username             |
| `password_hash` | VARCHAR(255) | Bcrypt hash via `password_hash()` |

> **Migration file:** `sql/create_users_table.sql` creates the `users` table and adds the `user_id` foreign key column to `bookings`. Run once in phpMyAdmin.

---

## 🧩 Components & Pages

### Shared Components

#### `includes/header.php`
- Starts PHP session (`session_start()`) if not already active.
- Outputs `<!DOCTYPE html>`, `<head>` (charset, viewport, title, CSS links).
- Loads `css/style.css` and Font Awesome 6.4.0 via CDN.
- Renders the sticky **navigation bar**:
  - Logo linking to `index.php`
  - **Home** link
  - **Login** button (shown when user is NOT logged in via `$_SESSION['user_id']`)
  - **Logout** button showing user's name (shown when user IS logged in)
  - **Admin Portal** button always visible

#### `includes/footer.php`
- Renders a `<footer>` with dynamic copyright year using `date('Y')`.
- Closes `</body>` and `</html>`.

---

### Customer-Facing Pages

#### `index.php` — Homepage
**Purpose:** Landing page with a hero image slider and package grid.

**PHP Logic:**
- Imports `config/db.php` and `includes/header.php`.
- Reads optional `?query=` GET parameter for live search.
- Fetches packages from `packages` table (filtered by `title` or `destination` with `LIKE` if a search query is present).
- Fetches up to 5 latest package images for the hero slider; falls back to 4 hardcoded Unsplash images if none exist.

**HTML Sections:**
1. **Hero Image Slider** (`.hero-slider`) — Full-viewport background slide deck with prev/next arrows and dot indicators. Overlay + centered heading + hero search form (GET to `index.php?query=`).
2. **Packages Grid** (`.packages-section` > `.modern-grid`) — Each package renders as a card (`.modern-card`) linking to `package_details.php?id=`. Card includes image, price badge, destination tag, and title. Empty state shown if no packages match.

**JavaScript:**
- Auto-playing slider (4500ms interval): `goTo()`, `slideChange()`, `resetTimer()`.
- Builds navigation dots dynamically on page load.

---

#### `package_details.php` — Package Detail Page
**Purpose:** Full details of a single package.

**PHP Logic:**
- Fetches `?id=` from GET (validated as numeric).
- Queries `packages` table by `package_id` using a prepared statement with `PDO::PARAM_INT`.
- Shows error or 404-style message if package not found.

**HTML Sections:**
1. **Breadcrumb navigation** — Home > Destination > Package Title.
2. **Two-column layout** (`.package-layout`):
   - **Left (`.package-main`):** Package title, location, hero image, Overview description, "What's Included" list (round-trip flights, 4-star hotel, daily breakfast, guided tours).
   - **Right sidebar (`.package-sidebar`):** Sticky booking card with price, free-cancellation note, and a **"Book This Package"** / **"Login to Book"** CTA (conditional on session).

---

#### `book.php` — Booking Form
**Purpose:** Lets logged-in users book a travel package.

**PHP Logic:**
- **Auth Guard:** Redirects guests to `login.php` if `$_SESSION['user_id']` is not set.
- Reads `?package_id=` from GET and fetches the full package record.
- On POST: validates all fields, inserts into `bookings` with `status = 'Pending'` and the logged-in `user_id`.

**HTML Sections:**
- **Success state** (`.booking-success`): Confirmation with user's name and email.
- **Booking layout** (`.booking-layout`):
  - Left card — package thumbnail, title, destination, duration, price.
  - Right form card — customer name (pre-filled/readonly), email (pre-filled/readonly), travel date picker (min = tomorrow), traveler count (1–20), live price total, submit button.

**JavaScript:** Live price calculator multiplying `basePrice × number_of_people` on every input event.

---

#### `login.php` — Customer Login
**PHP Logic:** Fetches user by email, verifies with `password_verify()`, sets session keys, redirects to `index.php`.

**UI:** Glassmorphism card over a full-page background. Email + password fields with icon prefixes, show/hide password toggle, link to `register.php`.

---

#### `register.php` — Customer Registration
**PHP Logic:** Validates all fields (non-empty, valid email, password >= 6 chars, passwords match), checks for duplicate email, hashes with `PASSWORD_BCRYPT`, inserts into `users`.

**UI:** Matching glassmorphism design. Four input fields + show/hide toggles. Success message shown after account creation.

---

#### `user_logout.php` — Customer Logout
Unsets `user_id`, `user_name`, `user_email` session keys (preserving admin session), calls `session_write_close()`, redirects to `index.php`.

---

### Admin Panel Pages

All admin pages check `$_SESSION['admin_logged_in'] === true` at the top — failing this redirects to `admin/index.php`.

#### `admin/index.php` — Admin Login
Queries `admins` table by username, verifies password hash. On success sets `$_SESSION['admin_logged_in'] = true` and `$_SESSION['admin_username']`.
UI includes an admin badge and a default credentials hint box.

---

#### `admin/dashboard.php` — Admin Dashboard
Runs 4 COUNT queries (total bookings, pending, confirmed, packages). Fetches 8 most recent bookings via JOIN with `packages`.

**UI:** Admin navbar + 4 colored stat cards (blue, yellow, green, purple) + recent bookings table with status badges.

---

#### `admin/manage_packages.php` — Package CRUD
**Operations:**
- `GET ?delete={id}` → DELETE package
- `GET ?edit={id}` → pre-fill form for editing
- `POST` with empty `package_id` → INSERT new package
- `POST` with a `package_id` value → UPDATE existing package

Uses PRG (Post/Redirect/Get) pattern on all mutations. Form fields: Title, Destination, Price, Duration Days, Image Path, Description.

---

#### `admin/manage_bookings.php` — Booking Management
**Operations:**
- `GET ?delete={id}` → DELETE booking
- `POST update_status` → validates status against allowlist `['Pending','Confirmed','Cancelled']`, then UPDATE

All bookings listed in a table with inline status `<select>` + Save button per row, plus a Delete link with JS confirmation.

---

## 🔄 Workflows

### 1. User Registration Flow

```
visit register.php
    │
    ├─ Already logged in? ──► Redirect to index.php
    │
    └─ Fill form (Full Name, Email, Password, Confirm)
           │
           ├─ Validation fails? ──► Show error, re-render form
           ├─ Email already exists? ──► Show error
           └─ All valid:
                  password_hash(PASSWORD_BCRYPT)
                  INSERT INTO users (full_name, email, password_hash)
                  Show success + link to login.php
```

### 2. User Login Flow

```
visit login.php
    │
    ├─ Already logged in? ──► Redirect to index.php
    │
    └─ POST email + password
           │
           ├─ Fields empty? ──► Show error
           └─ SELECT user WHERE email = :email
                  │
                  ├─ Not found OR password_verify() fails ──► Show error
                  └─ Success:
                         SESSION[user_id]    = user_id
                         SESSION[user_name]  = full_name
                         SESSION[user_email] = email
                         Redirect to index.php
```

### 3. Package Browsing & Search Flow

```
visit index.php
    │
    ├─ ?query= present ──► SELECT WHERE title LIKE '%q%' OR destination LIKE '%q%'
    └─ No query        ──► SELECT ALL packages ORDER BY package_id DESC
    │
    ├─ Hero slider: fetch up to 5 package images
    │      └─ None → use 4 Unsplash fallback slides
    │
    └─ Render package cards
           └─ click card ──► package_details.php?id={id}
```

### 4. Booking Flow

```
click "Book This Package" on package_details.php
    │
    ├─ NOT logged in ──► Redirect to login.php
    │
    └─ Logged in ──► GET book.php?package_id={id}
           │
           Form pre-filled with session name + email (readonly)
           User picks travel_date and number_of_people
           JS updates estimated total live
           │
           POST book.php
           │
           ├─ Validation fails ──► Show error
           └─ INSERT INTO bookings (..., status='Pending')
                  Show "Booking Confirmed!" success state
```

### 5. Admin Login Flow

```
visit admin/index.php
    │
    ├─ Already logged in ──► Redirect to dashboard.php
    │
    └─ POST username + password
           │
           SELECT admin WHERE username = :u
           password_verify() check
           │
           ├─ Fails ──► Show error
           └─ Success:
                  SESSION[admin_logged_in] = true
                  SESSION[admin_username]  = username
                  Redirect to dashboard.php
```

### 6. Admin Package Management Flow

```
manage_packages.php
    │
    ├─ GET ?edit={id}   ──► Fetch package, pre-fill form
    ├─ GET ?delete={id} ──► DELETE FROM packages WHERE package_id = :id
    └─ POST:
           ├─ package_id empty ──► INSERT new package
           └─ package_id set   ──► UPDATE package
           Redirect (PRG) to manage_packages.php
```

### 7. Admin Booking Management Flow

```
manage_bookings.php
    │
    ├─ GET ?delete={id} ──► DELETE FROM bookings WHERE booking_id = :id
    └─ POST update_status:
           Validate status IN ['Pending','Confirmed','Cancelled']
           UPDATE bookings SET status = :s WHERE booking_id = :id
           Redirect (PRG) to manage_bookings.php
```

---

## 🔐 Session Management

| Session Key                    | Set By            | Used By                                               |
|--------------------------------|-------------------|-------------------------------------------------------|
| `$_SESSION['user_id']`         | `login.php`       | `book.php` (auth), `header.php`, `package_details.php`|
| `$_SESSION['user_name']`       | `login.php`       | `header.php` (nav display), `book.php` (pre-fill)     |
| `$_SESSION['user_email']`      | `login.php`       | `book.php` (pre-fill)                                 |
| `$_SESSION['admin_logged_in']` | `admin/index.php` | All admin pages (auth guard)                          |
| `$_SESSION['admin_username']`  | `admin/index.php` | `dashboard.php` (welcome message)                     |

- Customer pages start session inside `includes/header.php`.
- Admin pages call `session_start()` manually at the top.
- Customer logout only unsets user keys, preserving admin session if active.

---

## 🛡 Security Practices

| Practice                    | Implementation                                                       |
|-----------------------------|----------------------------------------------------------------------|
| **Password hashing**        | `password_hash($pass, PASSWORD_BCRYPT)` + `password_verify()`       |
| **SQL injection prevention**| All user inputs use PDO prepared statements with named parameters    |
| **XSS prevention**          | All user-controlled output escaped via `htmlspecialchars()`          |
| **Admin auth guards**       | Every admin page checks `$_SESSION['admin_logged_in'] === true`      |
| **Customer auth guards**    | `book.php` checks `$_SESSION['user_id']`                            |
| **Input validation**        | Email format, password length >= 6, numeric ranges, status allowlist |
| **PRG pattern**             | POST → Redirect → GET on all admin mutations                         |
| **Type casting**            | IDs cast to `(int)` before use in queries                            |

---

## 🎨 Styling System

Main stylesheet: `css/style.css` (~2000 lines) | Admin overrides: `css/admin.css`

### CSS Custom Properties (`:root`)

```css
--primary:      #2563eb   /* Brand blue */
--primary-dark: #1d4ed8   /* Hover state */
--accent:       #0ea5e9   /* Sky blue accent */
--dark:         #0f172a   /* Near-black */
--nav-bg:       #0f172a   /* Navbar background */
--text-dark:    #1e293b
--text-mid:     #475569
--text-light:   #94a3b8
--bg:           #e8f4fb   /* Page background tint */
--white:        #ffffff
--green:        #16a34a
--radius-sm:    8px
--radius-md:    14px
--radius-lg:    20px
--shadow-sm / --shadow-md / --shadow-lg
--transition:   0.25s ease
```

### Key Design Patterns

| Pattern           | Description                                                               |
|-------------------|---------------------------------------------------------------------------|
| **Glassmorphism** | Auth pages use `backdrop-filter: blur(20px)` with semi-transparent cards  |
| **Sticky Navbar** | Dark gradient (`#0f172a → #1e293b`), `z-index: 1000`                     |
| **Hero Slider**   | Full-viewport `background-image` slides with CSS opacity transitions       |
| **Card Grid**     | CSS Grid with responsive columns for package cards                        |
| **Admin Tables**  | Styled `.admin-table` with zebra rows, status badges, action buttons      |
| **Fixed BG**      | `background-attachment: fixed` parallax sky image on all main pages       |
| **Font**          | Inter (Google Fonts), weights 300–800, loaded via `@import`               |

---

## 📦 External Dependencies

| Dependency          | Version | Source      | Usage                                          |
|---------------------|---------|-------------|------------------------------------------------|
| Font Awesome        | 6.4.0   | cdnjs CDN   | Icons throughout (navbar, admin, stat cards)   |
| Google Fonts: Inter | Latest  | Google CDN  | Primary typeface for all pages                 |
| Unsplash Images     | —       | CDN URLs    | Fallback hero slider images                    |

> No npm, no Composer, no build tools — all dependencies are CDN-loaded at runtime.

---

## ⚙️ Setup & Installation

### Prerequisites
- **XAMPP** with Apache + MySQL + PHP 7.4+

### Steps

1. **Clone / copy** the project into your XAMPP web root:
   ```
   C:\xampp\htdocs\php\
   ```

2. **Start XAMPP** — launch Apache and MySQL from the Control Panel.

3. **Create the database** in phpMyAdmin (`http://localhost/phpmyadmin`):
   ```sql
   CREATE DATABASE travira_db;
   ```

4. **Create tables** — create `packages`, `bookings`, `users`, `admins` tables as described in the [Database Schema](#-database-schema) section above.

5. **Run the migration** — import `sql/create_users_table.sql` in phpMyAdmin to add the `users` table and the `user_id` foreign key to `bookings`.

6. **Seed the admin account:**
   ```php
   // Run this once to generate the hash, then paste it into the INSERT
   echo password_hash('admin123', PASSWORD_BCRYPT);
   ```
   ```sql
   INSERT INTO admins (username, password_hash)
   VALUES ('admin', '<paste_bcrypt_hash_here>');
   ```

7. **Verify config** in `config/db.php`:
   ```php
   $host     = 'localhost';
   $dbname   = 'travira_db';
   $username = 'root';
   $password = '';   // XAMPP default is empty string
   ```

8. **Open the app:**
   - Customer portal → `http://localhost/php/`
   - Admin portal    → `http://localhost/php/admin/`

---

## 🔑 Default Credentials

### Admin Portal
| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ **Important:** Change default credentials immediately in any shared or production environment.

---

## 📄 License

This project is a **minor academic project** for educational purposes.

© 2026 Travira — Minor Project. All rights reserved.

### Admin Portal
| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ **Important:** Change default credentials immediately in any shared or production environment.

---

## 📄 License

This project is a **minor academic project** for educational purposes.

© 2026 Travira — Minor Project. All rights reserved.