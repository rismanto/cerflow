# CERFlow — Developer Documentation

> **Version:** 1.0 | **Stack:** PHP 8+, MariaDB, Vanilla JS | **Server:** XAMPP (Apache)

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack](#2-technology-stack)
3. [Directory Structure](#3-directory-structure)
4. [Database Schema](#4-database-schema)
5. [Backend Architecture](#5-backend-architecture)
6. [API Reference](#6-api-reference)
7. [Frontend Architecture](#7-frontend-architecture)
8. [Authentication & Authorization](#8-authentication--authorization)
9. [Core Features & Logic](#9-core-features--logic)
10. [Settings System](#10-settings-system)
11. [Analytics & Logging](#11-analytics--logging)
12. [Page Reference](#12-page-reference)
13. [Deployment Guide](#13-deployment-guide)
14. [Known Limitations & Future Work](#14-known-limitations--future-work)

---

## Current Implementation Corrections (2026-05-13)

This document contains older sections that are still broadly useful, but the live application currently differs in these important ways:

- `cer_maps` now includes `allow_feedback`, and student feedback availability is controlled per map rather than by a single global setting.
- `maps.php` exists as the teacher Mission Control page for browsing, previewing, and opening maps in Studio.
- `api.php` now includes `get_map`, which returns a single map with its triplets for Studio editing.
- Triplet identity is now based on stable database IDs, not triplet array position.
- Student and read-only card IDs now follow the pattern `c-<triplet_id>`, `e-<triplet_id>`, and `r-<triplet_id>`.
- `CERMap::save()` no longer deletes and recreates all triplets on edit. Retained triplets keep their database IDs, new ones are inserted, and removed ones are deleted explicitly.
- Historical compatibility for index-based `scores.map_data` was intentionally removed after clearing old score history. Playback views now assume DB-ID-based card IDs only.

---

## 1. Project Overview

**CERFlow** is a web-based learning platform for practicing scientific argumentation using the **Claim-Evidence-Reasoning (CER)** framework. Students reconstruct concept maps by connecting pre-defined claim, evidence, and reasoning cards. Teachers can monitor student behavior through an analytics dashboard.

### User Roles

| Role | Access |
|---|---|
| `guru` (teacher/admin) | Create maps, view all scores, view logs, manage users, configure settings |
| `siswa` (student) | Select modules, reconstruct maps, submit answers, view history |

---

## 2. Technology Stack

| Layer | Technology |
|---|---|
| Server | Apache (XAMPP) |
| Backend | PHP 8+ (procedural pages + OOP models) |
| Database | MariaDB / MySQL |
| Database Access | PDO with prepared statements |
| Frontend | Vanilla HTML + CSS + JavaScript |
| CSS Framework | Tailwind CSS (CDN) |
| Drag & Drop | SortableJS (CDN) |
| Excel Export | SheetJS / xlsx (CDN) |
| Searchable Dropdowns | TomSelect (CDN) |

> **No build tools or package managers.** All dependencies are loaded via CDN. No `npm`, `composer`, or `webpack` required.

---

## 3. Directory Structure

```
cerflow/
├── app/
│   ├── Config/
│   │   └── Database.php          # PDO connection singleton
│   └── Models/
│       ├── CERMap.php            # Map & triplet CRUD
│       ├── Score.php             # Student score persistence
│       ├── Setting.php           # Application settings key-value store
│       ├── User.php              # Auth, user CRUD
│       └── UserLog.php           # Session & action log queries
│
├── assets/
│   ├── css/
│   │   └── style.css             # Global styles (SVG path rules, sidebar, etc.)
│   └── js/
│       ├── admin.js              # Map studio editor logic
│       └── siswa.js              # Student workspace logic (main)
│
├── partials/
│   ├── header.php                # HTML <head> + session start
│   ├── navbar.php                # Top navigation bar (role-aware)
│   └── footer.php                # Closing scripts + HTML
│
├── scratch/                      # Temporary/utility scripts (not production)
│
├── admin.php                     # Teacher: CER Map Studio (create/edit maps)
├── api.php                       # JSON API gateway (all AJAX endpoints)
├── history.php                   # Student: view past submissions
├── index.php                     # Login page
├── logs.php                      # Teacher: interaction log analytics
├── logout.php                    # Session destroy + redirect
├── preview_map.php               # Public preview of a CER map structure
├── report.php                    # Teacher: score analytics table
├── settings.php                  # Teacher: application feature settings
├── siswa.php                     # Student: interactive reconstruction workspace
├── users.php                     # Teacher: user management
├── view_map.php                  # Student/Teacher: view submitted map with feedback
├── database.sql                  # Full DB dump (schema + seed data)
└── seed_maps.sql                 # Sample CER map data only
```

---

## 4. Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `username` | VARCHAR(50) UNIQUE | Login identifier |
| `namalengkap` | VARCHAR(100) | Display name |
| `password` | VARCHAR(255) | bcrypt hashed (`password_hash`) |
| `role` | ENUM('guru','siswa') | Access control |

### `cer_maps`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `title` | VARCHAR(100) | Module name shown to students |
| `allow_feedback` | TINYINT(1) | Per-map feedback toggle for student workspace |
| `created_at` | TIMESTAMP | Auto-set |

### `triplets`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `map_id` | INT FK → cer_maps | |
| `claim` | TEXT | |
| `evidence` | TEXT | |
| `reasoning` | TEXT | |

### `user_sessions`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `user_id` | INT FK → users | |
| `map_id` | INT FK → cer_maps | |
| `start_time` | TIMESTAMP | Auto-set |
| `is_submitted` | TINYINT(1) | 0=active, 1=submitted |

### `user_logs`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `session_id` | INT FK → user_sessions | |
| `action_type` | VARCHAR(50) | See action types below |
| `action_data` | TEXT (JSON) | Context data |
| `created_at` | TIMESTAMP | Auto-set |

**Tracked action types:**

| `action_type` | When logged |
|---|---|
| `connect` | Student connects two nodes |
| `disconnect` | Student removes a connection |
| `move` | Student drags a card to a new position |
| `auto_arrange` | Student clicks Auto Arrange |
| `feedback` | Student clicks Show Feedback |

### `scores`
| Column | Type | Notes |
|---|---|---|
| `id` | INT PK AUTO_INCREMENT | |
| `user_id` | INT FK → users | |
| `map_id` | INT FK → cer_maps | |
| `score` | DECIMAL(5,2) | Percentage (0–100) |
| `session_id` | INT FK → user_sessions | Links to interaction log |
| `map_data` | LONGTEXT (JSON) | Snapshot of student's connections |
| `submitted_at` | DATETIME | |

### `settings`
| Column | Type | Notes |
|---|---|---|
| `setting_key` | VARCHAR(50) PK | Unique key |
| `setting_value` | TEXT | Value string |
| `updated_at` | TIMESTAMP | Auto-updated |

The `settings` table is still available for generic key-value configuration, but feedback availability in the student workspace is now controlled per map via `cer_maps.allow_feedback`.

---

## 5. Backend Architecture

### Pattern
The backend uses a **Page Controller** pattern — each `.php` page at the root handles its own request, including auth checks, DB queries (via models), and view rendering. There is no MVC framework or router.

### Database Layer
`app/Config/Database.php` wraps PDO instantiation. Every model receives a `$db` (PDO instance) via constructor injection:

```php
$database = new Database();
$db = $database->getConnection();
$model = new CERMap($db);
```

All SQL queries use **prepared statements with bound parameters** to prevent SQL injection.

### Models

#### `User.php`
- `login($username, $password)` — verifies bcrypt hash, writes session
- `checkAuth($role)` *(static)* — validates session exists and role matches; used as guard on every page
- `logout()` *(static)* — destroys session
- `create / update / delete / getAll / getById` — standard CRUD

#### `CERMap.php`
- `getAll()` — returns all maps ordered newest first
- `getTriplets($map_id)` — returns all triplets for a given map
- `save($title, $triplets, $map_id)` — upsert: creates new or replaces existing triplets
- `delete($map_id)` — cascades to triplets

#### `Score.php`
- `save($user_id, $map_id, $score, $session_id, $map_data)` — inserts a new score record
- `getAllReports()` — joins users + maps for teacher analytics
- `getById($id)` — single score with student name and map title
- `getByUserId($user_id)` — student's own history

#### `UserLog.php`
- `startSession($user_id, $map_id)` — inserts a new session row, returns `session_id`
- `logAction($session_id, $type, $data)` — inserts one row into `user_logs`
- `submitSession($session_id)` — sets `is_submitted = 1`
- `getCompletedSessions($filters)` — aggregate query with subquery counts per action type; supports filters by `materi`, `siswa`, date range
- `getLoggedMaps()` — distinct maps that have submitted sessions

#### `Setting.php`
- `get($key, $default)` — reads one setting by key
- `set($key, $value)` — upserts a setting (INSERT … ON DUPLICATE KEY UPDATE)

---

## 6. API Reference

All requests go through `api.php`. The `action` parameter is passed via query string (`GET`). Request body (for write actions) is JSON via `POST`.

### Auth-protected endpoints (require `guru` role)
| Action | Method | Description |
|---|---|---|
| `save_map` | POST | Create/update a CER map and its triplets |
| `delete_map` | GET | Delete a map by `?map_id=` |
| `get_map` | GET | Return a single CER map with its triplets |
| `get_users` | GET | Return all users |
| `save_user` | POST | Create or update a user |
| `delete_user` | POST | Delete a user by `id` |
| `import_users` | POST (multipart) | Bulk import from CSV |
| `download_template` | GET | Download CSV template (returns file) |

### Public / siswa-protected endpoints
| Action | Method | Description |
|---|---|---|
| `get_maps` | GET | Return all CER maps (no auth) |
| `get_triplets` | GET | Return triplets for `?map_id=` (no auth) |
| `save_score` | POST | Save student score (requires `siswa`) |
| `start_session` | POST | Start logging session (requires `siswa`) |
| `log_action` | POST | Log one student action (requires `siswa`) |

### Response format
All endpoints return JSON:
```json
{ "status": "success" }
{ "status": "error", "message": "Reason here" }
```

---

## 7. Frontend Architecture

### `siswa.js` — Student Workspace (704 lines)

The core interactive module. Loaded only on `siswa.php`.

#### Global State Variables

| Variable | Type | Purpose |
|---|---|---|
| `currentMap` | Object | The active CER map data |
| `currentMapIndex` | Number | Index in `cerMaps` array |
| `connections` | Array | Student's drawn connections `[{from, to}]` |
| `isDrawing` | Boolean | True while user is dragging from a node |
| `startNode` | Element | The node drag started from |
| `currentSessionId` | Number | Active session ID from server |
| `feedbackActive` | Boolean | Whether feedback colors are shown |
| `isLocked` | Boolean | True after submission |

#### Session Persistence (sessionStorage)
| Key | Value |
|---|---|
| `cer_map_index` | Current map index |
| `cer_session_id` | Server session ID |
| `cer_connections` | JSON array of connections |
| `cer_col_order` | JSON `{claim:[ids], evidence:[ids], reasoning:[ids]}` |

Saved on every state change via `saveState()`. Restored on page load via `loadState()`.

#### Key Functions

| Function | Description |
|---|---|
| `startLearning(i)` | Initialize workspace for map at index `i` |
| `restoreSession(i, sid, conns, order)` | Restore from sessionStorage |
| `renderCards(colOrder)` | Build card DOM for all three columns |
| `initNodeListeners()` | Attach mousedown/mousemove/mouseup to nodes |
| `redrawLines()` | Re-render all SVG connection lines |
| `toggleFeedback()` | Toggle `feedbackActive`, log action, redraw |
| `autoArrange()` | FLIP-animate cards into connection-sorted order |
| `submitScore()` | Calculate score, POST to API, call `lockEditor()` |
| `lockEditor()` | Enter read-only mode post-submission |
| `restoreEditorActions()` | Rebuild navbar buttons (respects `currentMap.allow_feedback`) |
| `logAction(type, data)` | POST to `api.php?action=log_action` |
| `saveState() / loadState() / clearState()` | SessionStorage management |

#### Scoring Algorithm
```
score = (correct_connections / total_triplets) * 100
```
A connection `{from: X, to: Y}` is correct if there exists a triplet where:
- `from` card's triplet ID == `to` card's triplet ID, **and**
- the connection direction matches the valid C→E or E→R flow

#### Feedback Color Logic (in `redrawLines()`)
```javascript
if (feedbackActive) {
    if (isCorrect) { stroke = '#10b981'; dasharray = 'none'; }   // green solid
    else           { stroke = '#ef4444'; dasharray = '8,8'; }    // red dashed
} else {
    stroke = '#4361ee'; dasharray = 'none';                       // blue solid
}
path.style.stroke = stroke;
path.style.strokeDasharray = dasharray;
```

> **Important:** Stroke colors are applied via **inline JS styles**, not CSS classes. Any CSS rules targeting SVG `path` stroke will be overridden. This was intentional to avoid class specificity conflicts.

#### Global JS Variable Injected by PHP
```javascript
const cerMaps = [...]; // All map data with triplets and stable DB IDs
```

---

## 8. Authentication & Authorization

### Session Variables (set on login)
```php
$_SESSION['user_id']     // int
$_SESSION['username']    // string
$_SESSION['namalengkap'] // string
$_SESSION['role']        // 'guru' | 'siswa'
```

### Guard Pattern
Every protected page starts with:
```php
if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}
```

`checkAuth()` is static — it starts a session if not already started and checks both that the session exists and that the role matches.

### Password Hashing
`password_hash($password, PASSWORD_DEFAULT)` (bcrypt) on create/update.  
`password_verify($input, $hash)` on login.

---

## 9. Core Features & Logic

### Map Reconstruction Flow
1. Student lands on `siswa.php`, sees module cards
2. Clicks a module → `startLearning(i)` fires
3. Three columns render (Claim / Evidence / Reasoning) with draggable cards
4. Student drags from output node (right side) to input node (left side) to create connections
5. SVG `<path>` elements are drawn between nodes in real-time
6. Cards can be reordered within columns via SortableJS drag
7. Auto Arrange sorts cards to align connected triplets visually
8. Submit → score calculated → `save_score` API called → session finalized → `lockEditor()` called

### Post-Submission State (Locked Mode)
After submission:
- Node drag events are disabled (`pointerEvents: none`)
- Action buttons replaced with: `SHOW FEEDBACK` + `AUTO ARRANGE` + 🔒 badge
- Cards remain draggable (SortableJS still active)
- Feedback is auto-enabled (feedback colors applied immediately)
- State is NOT saved back to sessionStorage

### Feedback System
- **During work:** Controlled by `currentMap.allow_feedback`. If `0`, the feedback button is hidden for that map.
- **After submission / history view:** Always shown, regardless of setting.
- **Auto-reset:** Clicking feedback ON → modifying any connection → feedback automatically turns OFF. Each deliberate feedback use is a separate logged action.

### Auto Arrange (FLIP Animation)
Uses the FLIP technique (First-Last-Invert-Play):
1. Record all card positions before reorder
2. Reorder DOM
3. In `requestAnimationFrame`: calculate delta, apply instant `transform` to simulate old position
4. In second `requestAnimationFrame`: set `transition` + remove transform → browser animates
5. Lines continuously redraw during animation (600ms loop)

---

## 10. Settings System

Settings are stored as key-value pairs in the `settings` table.

### Adding a New Setting

**Step 1:** Add a default row to `database.sql`:
```sql
INSERT INTO `settings` VALUES ('my_new_setting', 'default_value', current_timestamp());
```

**Step 2:** Add a UI control in `settings.php` with the key name as the input `name`

**Step 3:** Handle the new key in `settings.php` POST handler

**Step 4:** Read it where needed:
```php
require_once 'app/Models/Setting.php';
$settingModel = new Setting($db);
$value = $settingModel->get('my_new_setting', 'default');
```

**Step 5:** If needed in frontend JS, inject it in the page:
```php
<script>
    const MY_SETTING = <?php echo $value; ?>;
</script>
```

---

## 11. Analytics & Logging

### Session Lifecycle
```
start_session  →  [log_action × N]  →  save_score → submit_session (is_submitted=1)
```

### Log Query Architecture
`UserLog::getCompletedSessions()` uses correlated subqueries to count each action type per session. This is readable but may be slow with large datasets:

```sql
(SELECT COUNT(*) FROM user_logs WHERE session_id = s.id AND action_type = 'connect') as count_connect
```

**To add a new tracked action type:**
1. Log it from JS: `logAction('my_action', {...data})`
2. Add a subquery in `UserLog::getCompletedSessions()`
3. Add a column in `logs.php` table header and `<td>` row

### Excel Export
`logs.php` uses SheetJS client-side. Data is read directly from visible DOM table rows — not re-queried from the server. This means filters applied in the UI are respected in the export automatically.

---

## 12. Page Reference

| Page | Role | Description |
|---|---|---|
| `index.php` | Public | Login form |
| `admin.php` | guru | CER Map Studio — create/edit maps and triplets |
| `siswa.php` | siswa | Interactive reconstruction workspace |
| `history.php` | siswa | Table of past submissions with scores |
| `view_map.php` | both | Visual display of a submitted map; supports feedback mode and auto-arrange |
| `report.php` | guru | Score analytics table with export |
| `logs.php` | guru | Interaction log analytics with export |
| `users.php` | guru | User management (create, edit, delete, CSV import) |
| `settings.php` | guru | Application feature toggles |
| `logout.php` | both | Destroys session, redirects to `index.php` |
| `preview_map.php` | guru | Preview a CER map's correct structure |
| `api.php` | both | JSON API gateway — all AJAX calls go here |

---

## 13. Deployment Guide

### Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.4+
- Apache with `mod_rewrite` (not strictly required — no rewrites used)

### Steps

1. **Copy files** to your web root (e.g., `/var/www/html/cerflow/` or `htdocs/cerflow/`)

2. **Create database:**
```sql
mysql -u root -p < database.sql
```

3. **Configure database connection** in `app/Config/Database.php`:
```php
private $host = "localhost";
private $db_name = "cer_flow_db";
private $username = "root";
private $password = "";
```

4. **Verify default admin account** (from `database.sql`):
   - Username: `admin`
   - Password: `admin123`

5. **Set PHP session path** if needed (default PHP config usually sufficient on shared hosting)

6. **Permissions:** Ensure `scratch/` is writable if using any scratch scripts

### Environment Notes
- No `.env` file system — credentials are hardcoded in `Database.php`
- For production, consider extracting credentials to environment variables or a config file outside the web root

---

## 14. Known Limitations & Future Work

### Current Limitations

| Area | Issue |
|---|---|
| **Database credentials** | Hardcoded in `Database.php` — not suitable for multi-environment deployments |
| **No CSRF protection** | POST forms and API calls have no CSRF token validation |
| **Flat API file** | `api.php` is a single 207-line switch-style file — will need refactoring as endpoints grow |
| **Log query performance** | `getCompletedSessions()` uses correlated subqueries — may be slow with 1000+ sessions |
| **Multiple submissions** | Students can submit the same module multiple times — all are recorded in `scores` |
| **Session security** | PHP default session configuration — no explicit `session.cookie_secure` or `httponly` enforcement |

### Suggested Future Improvements

1. **Extract config to `.env`** — use `vlucas/phpdotenv` or similar
2. **Add post-test score column** to `scores` table for research correlation analysis
3. **Convert log subqueries to indexed materialized view** or aggregate table for performance
4. **Add CSRF tokens** to all forms
5. **Module locking** — allow teacher to lock a module so students can no longer submit
6. **Per-student feedback restriction** — currently global; could be per-module or per-student
7. **REST API refactor** — split `api.php` into resource-based controllers
8. **Rate limiting** on `log_action` — currently every student interaction sends an HTTP request

---

## 15. Advanced Analytics (Future Implementation Plan)

This section outlines the planned transition from raw logging to visual Learning Process Analytics (LPA).

### Proposed Visualizations (via Chart.js)

| Visualization | Metrics | Learning Insight |
|---|---|---|
| **Guessing vs. Reasoning Ratio** | `Disconnect` count vs. `Connect` count | Identifies "quality of thinking." High disconnect counts relative to connects signal trial-and-error/guessing behavior. |
| **Efficiency Scatter Plot** | Total Actions (X) vs. Final Score (Y) | Segments students into: Masters (low effort, high score), Grinders (high effort, high score), and Strugglers (high effort, low score). |
| **Module Difficulty Profile** | Avg. Score per CER Map | Class-wide diagnostic tool to identify modules where the content or triplets might be too difficult or ambiguous. |
| **Score Distribution (Bell Curve)** | Frequency of score ranges | Shows whether learning is uniform across the class or if there's a significant split in comprehension. |

### Technical Strategy
- **Library Selection**: **Chart.js** (CDN). Chosen for its lightweight footprint and premium, interactive aesthetics.
- **Data Metric Policy**: **Exclude `view_reading`**. This metric was deemed unreliable because students may read from physical sources or leave the dialog open throughout the session, creating "noisy" data.
- **Implementation Layout**: Add a "Summary Dashboard" header to `logs.php` with 3-4 key stat cards and 2 interactive charts, keeping the raw log table below for detailed evidence.

---

*Documentation generated: May 2026*
