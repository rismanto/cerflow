# CER Flow AI Handoff

Last updated: 2026-05-16

This document is for the next AI agent or developer who needs to work quickly in this codebase. It focuses on the actual moving parts, current architecture, data flow, and known traps.

## 1. Project In One Paragraph

CER Flow is a PHP/MySQL web app for Claim-Evidence-Reasoning learning activities. Teachers (`guru`) create CER maps made of triplets: claim, evidence, reasoning. Students (`siswa`) reconstruct a disconnected map by connecting claim -> evidence -> reasoning cards. The app records scores, submitted map snapshots, and student interaction logs.

## 2. Stack And Runtime

- Backend: plain PHP with root-level page controllers and small model classes.
- Database: MySQL/MariaDB through PDO.
- Frontend: vanilla JavaScript, Tailwind CDN, SortableJS CDN.
- Server target: XAMPP/Apache, project located at `D:\xampp\htdocs\cerflow`.
- Containerization: `docker-compose.yml` provided for MySQL 8.0 environment.
- No Composer, npm build step, router, framework, or bundler.
- DB credentials are hardcoded in `app/Config/Database.php` (uses getenv fallback).

Common local URL is likely:

```text
http://localhost/cerflow/
```

## 3. Key Files

```text
admin.php                 Teacher Map Studio: create/edit CER maps.
maps.php                  Teacher Mission Control / Content Hub: list maps, open edit/preview.
siswa.php                 Student workspace page; injects all maps and triplets into JS.
api.php                   Single JSON API gateway for AJAX actions.
preview_map.php           Teacher preview of correct CER map structure.
view_map.php              Read-only view of submitted student map snapshot.
report.php                Teacher score analytics.
logs.php                  Teacher interaction-log analytics.
history.php               Student submission history.
users.php                 Teacher user CRUD, CSV import, and per-user Gemini API keys.
settings.php              Teacher settings: managed personal Gemini key and system name.
docker-compose.yml        Docker setup for MySQL 8.0 environment.

assets/js/admin.js        Map Studio frontend logic.
assets/js/siswa.js        Student reconstruction frontend logic.
assets/css/style.css      Shared styles for cards, SVG lines, active nav/sidebar.

app/Config/Database.php   PDO connection.
app/Models/CERMap.php     Maps and triplets.
app/Models/User.php       Auth and user CRUD.
app/Models/Score.php      Score persistence and reports.
app/Models/UserLog.php    Student sessions and action logs.
app/Models/Setting.php    Key-value settings (global).
app/Services/AIService.php Gemini API integration (Flash 1.5).

database.sql              Schema plus seed data dump (includes gemini_api_key column).
seed_maps.sql             Sample map/triplet inserts.
cerflow_developer_docs.md Older broader documentation; useful, but may be stale.
```

## 4. Roles And Auth

Roles are stored in `users.role`:

- `guru`: teacher/admin.
- `siswa`: student.

Sessions are set in `User::login()`:

```php
$_SESSION['user_id']
$_SESSION['username']
$_SESSION['namalengkap']
$_SESSION['role']
```

Protected pages call:

```php
if (!User::checkAuth('guru')) {
    header("Location: index.php");
    exit;
}
```

`User::checkAuth($required_role)` starts the session if needed and checks role equality.

## 5. Database Shape

Important tables:

- `users`: login accounts. Contains `gemini_api_key` for per-teacher AI features.
- `cer_maps`: teacher-created map metadata.
- `triplets`: claim/evidence/reasoning rows linked by `map_id`.
- `scores`: submitted student score, session id, and `map_data` JSON snapshot.
- `user_sessions`: one student working session per map start.
- `user_logs`: interaction events such as connect, disconnect, move, feedback.
- `settings`: global key-value settings (system name, etc).

Current schema includes:
- `cer_maps.allow_feedback`: used per map.
- `users.gemini_api_key`: per-user teacher credentials for AI features.

## 6. Main Teacher Flow

### Mission Control: `maps.php`

Purpose: list maps for teachers and open actions.

Frontend behavior:

- `loadHub()` calls `api.php?action=get_maps`.
- `renderHub()` builds cards.
- Edit Studio button calls:

```js
window.location.href = `admin.php?edit_id=${id}`;
```

### Map Studio: `admin.php` + `assets/js/admin.js`

Purpose: create and edit map title, `allow_feedback`, and local triplet table.

Important PHP preload behavior:

- `admin.php` reads `$_GET['edit_id']`.
- If present, it loads the full map through `CERMap::getById($id)`.
- It injects:

```js
window.INITIAL_EDIT_MAP_ID = ...
window.INITIAL_EDIT_MAP_DATA = ...
```

- It loads `assets/js/admin.js?v=<filemtime>` to avoid stale browser cache after JS edits.

Important JS state:

```js
let currentMapId = null;
let triplets = [];
let allMapsCache = [];
```

Important JS functions:

- `applyMapToEditor(selectedMap)`: fills title, feedback toggle, preview link, and triplets table.
- `loadMaps()`: fills sidebar with all maps.
- `fetchMapForEditing(id)`: calls `api.php?action=get_map&map_id=...`.
- `editMap(id)`: fetches one map and applies it.
- `saveToDB()`: posts `map_id`, title, triplets, and allow_feedback to `save_map`.
- `newMap()`: clears editor and removes `edit_id` from URL.

Edit load sequence from `maps.php`:

```text
maps.php Edit Studio
  -> admin.php?edit_id=123
  -> admin.php preloads CERMap::getById(123)
  -> JS sees window.INITIAL_EDIT_MAP_DATA
  -> applyMapToEditor(...)
```

This path starts outside Map Studio, from Mission Control. It uses full page navigation, so the map ID travels in the URL as `admin.php?edit_id=123`. On the PHP side, `admin.php` reads `$_GET['edit_id']`, loads the map before the page is sent to the browser, and embeds the result into `window.INITIAL_EDIT_MAP_DATA`. When `admin.js` starts, it can fill the editor immediately from this preloaded data.

Sidebar edit sequence inside Studio:

```text
sidebar map click
  -> editMap(id)
  -> fetchMapForEditing(id)
  -> api.php?action=get_map&map_id=id
  -> applyMapToEditor(...)
```

This path starts after `admin.php` is already open. It does not reload the page. The map ID travels through a JavaScript click handler into `editMap(id)`, then through an AJAX request to `api.php?action=get_map&map_id=...`. The API returns the selected map plus its triplets, and the editor is updated in-place.

Both paths intentionally converge on the same final function:

```js
applyMapToEditor(selectedMap)
```

That function is the shared editor-population step. It sets `currentMapId`, fills the map title input, sets the `allow-feedback` checkbox, updates the Preview button URL, maps `selectedMap.triplets` into the local `triplets` array, and re-renders the triplet table.

The practical difference:

| Path | Starts From | Data Source | Page Reload |
|---|---|---|---|
| Mission Control edit | `maps.php` | PHP preloads map into `window.INITIAL_EDIT_MAP_DATA` | Yes |
| Studio sidebar edit | `admin.php` sidebar | JS fetches `api.php?action=get_map` | No |

In short: `maps.php` is for entering Studio with a selected map already chosen. The sidebar flow is for switching maps while already inside Studio.

## 7. Main Student Flow

`siswa.php` loads all maps and triplets server-side:

```php
$maps = $cerMapModel->getAll();
foreach ($maps as &$map) {
    $map['triplets'] = $cerMapModel->getTriplets($map['id']);
}
```

Then injects:

```js
const cerMaps = ...
```

`assets/js/siswa.js` handles the interactive workspace.

Key student JS state:

```js
currentMap
currentMapIndex
connections       // [{ from: "c-24", to: "e-24" }, ...]
currentSessionId
feedbackActive
isLocked
```

Session storage keys:

```js
cer_map_index
cer_session_id
cer_connections
cer_col_order
```

Student lifecycle:

```text
select module
  -> startLearning(index)
  -> api.php?action=start_session
  -> renderCards()
  -> student drags cards/connects nodes
  -> log_action calls along the way
  -> submitScore()
  -> api.php?action=save_score
  -> UserLog::submitSession(session_id)
  -> lockEditor()
```

Connections are card IDs derived from database IDs:

- Claims: `c-24`, `c-25`, ...
- Evidence: `e-24`, `e-25`, ...
- Reasoning: `r-24`, `r-25`, ...

The numeric suffix is the triplet database ID. A correct connection has matching triplet DB ID and valid direction:

- `c-N -> e-N`
- `e-N -> r-N`

## 8. API Reference

All AJAX goes through `api.php?action=...`.

Teacher-protected actions:

- `save_map` POST JSON
- `delete_map` GET `map_id`
- `get_map` GET `map_id`
- `get_users` GET
- `save_user` POST JSON
- `delete_user` POST JSON
- `import_users` POST multipart CSV
- `download_template` GET CSV
- `extract_cer` POST JSON (AI logic)

General/student actions:

- `get_maps` GET
- `get_triplets` GET `map_id`
- `save_score` POST JSON, requires `siswa`
- `start_session` POST JSON, requires `siswa`
- `log_action` POST JSON, requires `siswa`

Current `get_map` response:

```json
{
  "status": "success",
  "data": {
    "id": "1",
    "title": "...",
    "allow_feedback": "1",
    "created_at": "...",
    "triplets": [
      {
        "id": "1",
        "map_id": "1",
        "claim": "...",
        "evidence": "...",
        "reasoning": "..."
      }
    ]
  }
}
```

Current `save_map` request:

```json
{
  "map_id": 1,
  "title": "Map title",
  "allow_feedback": 1,
  "triplets": [
    {
      "claim": "...",
      "evidence": "...",
      "reasoning": "..."
    }
  ]
}
```

`CERMap::save()` updates an existing map if `map_id` is present. Existing triplets keep their database IDs when they are retained in the payload, new triplets are inserted, and removed triplets are deleted explicitly.

## 9. Models

### `CERMap.php`

- `getAll()`: all maps newest first.
- `getById($id)`: one map plus `triplets`.
- `getTriplets($map_id)`: triplets for map.
- `save($title, $triplets, $map_id = null, $allow_feedback = 1)`: create or update.
- `delete($id)`: deletes triplets, scores, then map.

### `Score.php`

- `save($user_id, $map_id, $score, $session_id = null, $map_data = null)`.
- `getAllReports()`.
- `getById($id)`.
- `getByUserId($user_id)`.

### `UserLog.php`

- `startSession($user_id, $map_id)`.
- `logAction($session_id, $action_type, $action_data = null)`.
- `submitSession($session_id)`.
- `getCompletedSessions($filters = [])`.
- `getLoggedMaps()`.

### `User.php`

- `login()`, `checkAuth()`, `logout()`.
- User CRUD (includes `gemini_api_key`).

### `Setting.php`

- `get($key, $default = null)`.
- `set($key, $value)`.

## 10. Frontend Rendering Notes

The app frequently builds HTML with template strings and assigns `innerHTML`. User-created map/card text is not consistently escaped on the frontend. If working on security or data with quotes/HTML, address this carefully.

SortableJS is used for card reordering:

- Student: in `assets/js/siswa.js`.
- Preview/read-only views: `preview_map.php`, `view_map.php`.

SVG connection lines are redrawn from DOM element positions. Any layout change affecting card positions should call `redrawLines()` after render, scroll, resize, drag, or animation.

## 11. Current Edit Feature Context

The user reported that Mission Control's "Edit Studio" opens `admin.php` but did not load the map/triplets.

Relevant changes already made:

- `maps.php` already sends `admin.php?edit_id=${id}`.
- `admin.php` now loads the target map server-side and injects `INITIAL_EDIT_MAP_DATA`.
- `api.php` now has `get_map`.
- `assets/js/admin.js` now has `applyMapToEditor()`, `fetchMapForEditing()`, and initialization logic.
- `admin.js` script URL uses filemtime cache busting from `admin.php`.

If it still does not load in browser, check in this order:

1. Browser devtools console for JavaScript errors.
2. View source of `admin.php?edit_id=<id>` and confirm `window.INITIAL_EDIT_MAP_DATA` contains JSON with `triplets`.
3. Network tab for `assets/js/admin.js?v=...` and verify it is the current file.
4. Network tab for `api.php?action=get_map&map_id=<id>` when clicking sidebar maps.
5. Confirm the teacher is logged in as `guru`; `get_map` is teacher-protected.
6. Confirm `cer_maps.allow_feedback` exists in the local database. If not, run/update migration.

Potential issue still present in `assets/js/admin.js`:

- Sidebar rows still include inline `onclick="editMap(...)"`. This is okay for numeric IDs, but a future cleanup should replace inline JS with event listeners and `data-map-id`.

## 12. Known Footguns

- `api.php` starts with `error_reporting(0)`, which hides PHP notices/warnings and can make JSON failures quiet.
- `database.sql` was refreshed from the live database on 2026-05-13. If the app changes again, regenerate it from MySQL rather than hand-editing partial schema snippets.
- No CSRF protection on forms or API calls.
- No centralized escaping strategy for HTML generated from user-created content.
- `CERMap::save()` replaces all triplets on update. Existing triplet DB IDs change after every edit.
- Student correctness now depends on stable triplet database IDs, not triplet array position.
- Historical score compatibility for index-based `map_data` was intentionally removed after clearing old `scores` rows. Playback now assumes card IDs already use triplet DB IDs such as `c-24`.
- `get_maps` and `get_triplets` are not auth-protected in `api.php`.
- `download_template` changes response headers after the global JSON header. It works in many setups but is structurally awkward.
- Many pages include inline JavaScript. Use PHP lint plus browser console when debugging.
- CSS and JS contain mojibake in some emoji/text labels. Do not "fix" encoding casually unless you can verify file encoding and UI output.
- There is no automated test suite.

## 13. Verification Commands

Use PowerShell from repo root:

```powershell
php -l admin.php
php -l maps.php
php -l api.php
php -l siswa.php
node --check assets\js\admin.js
node --check assets\js\siswa.js
```

Useful search commands:

```powershell
rg -n "action ==|fetch\(|function |class " .
rg -n "edit_id|INITIAL_EDIT_MAP|get_map|applyMapToEditor" .
rg -n "allow_feedback" .
```

If browser verification is needed, use:

```text
http://localhost/cerflow/maps.php
http://localhost/cerflow/admin.php?edit_id=1
```

## 14. How To Add Common Features

### Add A New API Action

1. Add auth guard action name to the protected list if needed.
2. Add an `elseif ($action == 'new_action')` block in `api.php`.
3. Use model methods instead of raw SQL in page code when possible.
4. Return JSON consistently:

```php
echo json_encode(['status' => 'success', 'data' => $data]);
```

### Add A Map Field

1. Add column to `cer_maps` in DB.
2. Update `database.sql` and any migrations.
3. Update `CERMap::save()` insert and update SQL.
4. Update `CERMap::getAll()` or `getById()` if needed.
5. Add UI control in `admin.php`.
6. Include the field in `saveToDB()` in `assets/js/admin.js`.
7. Apply it in `applyMapToEditor()`.
8. Add display in `maps.php` or `siswa.php` if needed.

### Add A Student Logged Action

1. Call `logAction('action_name', data)` from `assets/js/siswa.js`.
2. Add count subquery in `UserLog::getCompletedSessions()`.
3. Add a column in `logs.php`.
4. Update Excel export if it assumes fixed columns.

## 15. AI Extraction (Gemini)
- Model: `gemini-flash-latest`.
- Auth: Header-based `x-goog-api-key`.
- Logic: `AIService::extractCER($text)` returns triplets.
- Key Storage: Fetched from the session user's ID using `User::getApiKey()`.

## 16. Docker Setup
- Database: MySQL 8.0 image.
- Port: `3306`.
- Root PW: `root`.
- Auto-Init: Loads `database.sql` on first start.
- Commands: `docker-compose up -d`.

## 17. Suggested Next Cleanup
- Move remaining inline JS handlers to event listeners.
- Implement more robust error handling for AI responses.
- Add "Test Connection" button in Settings for Gemini API keys.
