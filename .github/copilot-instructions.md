# copilot-instructions.md

> **Project:** Saltcoats Victoria FC Operating System (SVFC OS)  
> **Owner:** Colin Lundy (client & assisting dev)  
> **Senior PM/Full‑Stack Lead:** (You, Copilot, act as disciplined pair‑programmer)  
> **Goal:** Build a modular, low-cost club OS with public site, team portal, admin, POS, facilities, volunteers, finance, analytics, and later automation/AI.

---

## 0) How to behave (VERY IMPORTANT)

- **Be a disciplined pair‑programmer and delivery PM.** Keep responses concise, code-first, and reference file paths.
- **Never invent external facts** (APIs, endpoints) not in this doc or the repo. If something is missing, **ask for the path or confirm assumptions**.
- **Prefer incremental diffs** (show the exact files/lines to add or change). Use fenced code blocks with correct language hints.
- **Respect the stack & constraints** below. Avoid introducing heavy libs unless explicitly approved.
- **Prioritise Phase 1 scope** (Core auth, public website MVP, POS basics, season tickets, basic stock) before later phases.
- **Security by default**: use prepared statements (PDO), bcrypt, CSRF tokens for POST forms, input validation & output escaping.
- **Sessions must work across subdomains** (see section 4).

---

## 1) Architecture & Scope

### 1.1 Subdomains (local → staging/testing)

- **Public website**: `project.localhost` → later `project.lundy.me.uk`
- **Team Portal**: `portal.localhost` → later `portal.lundy.me.uk`
- **Admin**: `admin.localhost` → later `admin.lundy.me.uk`
- **POS**: `pos.localhost` → later `pos.lundy.me.uk`
- **API (future-expanded)**: `api.localhost` → later `api.lundy.me.uk`

### 1.2 Modules delivered by phase

**Phase 1 (current):**

- Auth (login/logout, invites, manual add), Roles, Audit logs
- Public site MVP (homepage placeholders, news, fixtures, sponsors)
- POS MVP (locations, float open/close, sales, refunds with reason)
- Season tickets (manual list), Basic stock (deliveries, sales, wastage/donations)

**Later phases** are documented in `/docs/Phase_Implementation_Roadmap.docx` and master spec.

---

## 2) Tech Stack & Repo Layout

### 2.1 Stack

- **PHP** (8.x where possible) + **PDO** (MySQL)
- **MySQL** (single DB now: `svfc_os`)
- **XAMPP** on Windows (dev), Fasthosts (staging/prod later)
- **Frontend**: vanilla PHP + HTML; allow Bootstrap/Tailwind later (not required now)
- **Mail**: PHPMailer later (for invites), for now show invite code on screen
- **Payments**: Stripe (Phase 3+), not active in Phase 1

### 2.2 Repo structure (root)

```
SVFC_OS/
  docs/ ... (full document set)
  api/           # (reserved)
  portal/
    auth/ (login.php, logout.php, register.php, invite.php, manual_add.php, auth_functions.php, middleware.php)
    index.php
    players/ training/ matches/  # (stubs)
  admin/
    index.php
    dashboard/ sponsors/ finance/ users/
  pos/
    index.php  bar/ kiosk/ merch/ gate/
  public/
    index.php  news/ fixtures/ history/ sponsors/
  shared/
    config/ (config.local.php, config.staging.php, config.prod.php)
    includes/ (session_init.php, helpers.php [future])
    assets/ (css/js/images)
    vendor/  (composer vendors as needed)
  database/
    schema.sql  seed_data.sql  migrations/
  tests/
  .gitignore  README.md  LICENSE
```

---

## 3) Configuration & Environments

### 3.1 Localhost virtual hosts (Apache)

Add to hosts:

```
127.0.0.1 project.localhost
127.0.0.1 portal.localhost
127.0.0.1 admin.localhost
127.0.0.1 pos.localhost
127.0.0.1 api.localhost
```

vhosts (paths adjusted to your XAMPP install):

```
<VirtualHost *:80> ServerName portal.localhost DocumentRoot "C:/xampp/htdocs/SVFC_OS/portal" </VirtualHost>
<VirtualHost *:80> ServerName admin.localhost  DocumentRoot "C:/xampp/htdocs/SVFC_OS/admin"  </VirtualHost>
<VirtualHost *:80> ServerName pos.localhost    DocumentRoot "C:/xampp/htdocs/SVFC_OS/pos"    </VirtualHost>
<VirtualHost *:80> ServerName project.localhost DocumentRoot "C:/xampp/htdocs/SVFC_OS/public"</VirtualHost>
<VirtualHost *:80> ServerName api.localhost    DocumentRoot "C:/xampp/htdocs/SVFC_OS/api"    </VirtualHost>
```

### 3.2 Config files

`/shared/config/config.local.php`

```php
<?php
define('DB_HOST','localhost'); define('DB_NAME','svfc_os'); define('DB_USER','root'); define('DB_PASS','');
define('BASE_URL','http://portal.localhost/');   // central login
define('ADMIN_URL','http://admin.localhost/');
define('API_URL','http://api.localhost/');
define('PUBLIC_URL','http://project.localhost/');
define('POS_URL','http://pos.localhost/');
```

**NEVER** commit real secrets. For staging/prod, use `config.staging.php` / `config.prod.php` and add `/shared/config/*.php` to `.gitignore`.

---

## 4) Sessions across subdomains (critical)

- Use a **single session policy** via a shared include:
  `/shared/includes/session_init.php`

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_domain', '.localhost'); // change to .lundy.me.uk in staging/prod
    session_start();
}
```

- Include this file **instead of** calling `session_start()` directly:
  - In `portal/auth/auth_functions.php`:
    ```php
    require_once __DIR__ . '/../../shared/includes/session_init.php';
    require_once __DIR__ . '/../../shared/config/config.local.php';
    ```
- After login on `portal.localhost`, `$_SESSION` must be visible on `admin.localhost`, `pos.localhost`, etc.

---

## 5) Database (Phase 1 schema)

- Located in `/database/schema.sql` (already generated).
- Core tables: `roles`, `users`, `audit_logs`, `news`, `fixtures`, `sponsors`, `pos_locations`, `pos_sessions`, `pos_sales`, `pos_refunds`, `stock_items`, `stock_movements`.
- Seed file `/database/seed_data.sql` contains default roles + Super Admin user.

**Rules**

- Always use **PDO prepared statements**.
- Add **created_at** timestamps.
- Add **FKs** with ON DELETE behavior.
- Use **decimal(10,2)** for money, not float.

---

## 6) Auth system (Phase 1)

**Files @ `/portal/auth/`**

- `auth_functions.php` → DB connection, login/logout, invites, manual add, audit logging.
- `middleware.php` → `requireLogin()` and `checkRole($allowedRoleIds)`.
- `login.php` → form → `login()` → redirect to `/portal/index.php` (or back to `redirect` param).
- `logout.php` → destroy session & redirect to login.
- `register.php` → via invite code → create user → mark invite used.
- `invite.php` (admin only) → create invite (show code; email later via PHPMailer).
- `manual_add.php` (admin only) → admin directly creates user.

**DB addition** for invites:

```sql
CREATE TABLE invites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  role_id INT NOT NULL,
  code VARCHAR(64) NOT NULL UNIQUE,
  status ENUM('pending','used','expired') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

**Redirect policy**

- When auth fails in any subdomain, redirect to central login: `BASE_URL . "auth/login.php"`.
- Support redirect-back using `?redirect=`. After successful login:

```php
if (!empty($_GET['redirect'])) { header("Location: " . $_GET['redirect']); exit; }
header("Location: /portal/index.php"); exit;
```

**Security**

- Passwords: `password_hash($pwd, PASSWORD_BCRYPT)`; verify via `password_verify`.
- CSRF: generate token per session (`$_SESSION['csrf']`), store hidden input, verify on POST.
- Rate-limiting login attempts (basic: count failed attempts and throttle).

---

## 7) Public website (Phase 1 MVP)

**admin** creates: News posts, Fixtures, Sponsors.  
**public** reads:

- `/public/index.php`: simple hero + “Next Match” placeholder + news list + sponsor logos.
- `/admin/` should provide barebones CRUD screens for News/Fixtures/Sponsors (can reuse portal auth).

---

## 8) POS MVP (Phase 1)

- `pos/index.php` should allow:
  - Select Location (Bar/Kiosk/Merch/Gate) or load by sub-URL.
  - **Open session** (enter start float) → `pos_sessions.status='open'`.
  - **Record sale**: one-tap buttons (item_name, price, quantity), payment method (cash/card).
  - **Refund**: requires reason; logs to `pos_refunds` (no stock increase unless configured).
  - **Close session**: enter end float; compute variance.
- Admin dashboard (`/admin/index.php`) shows: open sessions, last variance, total sales today.

---

## 9) Coding standards & patterns

- **PHP Style**: PSR-12-ish, but keep it pragmatic. Separate concerns:
  - DB connection via a function `getDB()` in `auth_functions.php` (or `shared/includes/db.php` later).
  - Business logic in functions/“service” files; keep page controllers light.
- **HTML escaping** for any user content: `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.
- **Input validation** with `filter_input()` or manual checks.
- **Error handling**: try/catch around PDO calls; log errors (avoid echoing raw errors in prod).
- **Audit logs**: call `logAction($userId, $description)` for meaningful actions (login, create user, open/close POS sessions, refund, create news item, etc.).

---

## 10) Testing & QA

- Manual testing plan in `/docs/Testing_Plan.docx`.
- Use `/docs/Bug_Tracker.xlsx` to track issues.
- Add minimal **smoke tests** (optional) in `/tests/` later.
- During Phase 1, test with **Robert** (volunteer) & **Malky** (coach).

---

## 11) Git workflow

- Branching:
  - `main` → stable
  - `develop` → integration
  - `feature/<short-name>` → new work
- Commits: present tense, scoped, e.g. `auth: add invite registration flow`.
- PR checklist:
  - Does it only touch Phase 1 modules?
  - Are SQL injections avoided (prepared statements)?
  - Are redirects correct (central login)?
  - Are sessions shared across subdomains?
  - Are actions audit-logged?

---

## 12) Tasks Copilot should be ready to do **on request**

Use prompts like: _“Copilot, implement X in file Y with Z acceptance criteria.”_

- Scaffold CRUD for **news** (admin create/edit/delete, public list/view).
- Build **fixtures** admin page + public list.
- Implement **POS open/close session** pages and sale form.
- Add **CSRF tokens** to auth forms.
- Add **redirect-back** logic to login/register flows.
- Create **admin users page** linking to `invite.php` and `manual_add.php`.
- Implement **basic dashboard cards** in `/admin/index.php` (counts).
- Add **helpers.php** with `e($str)` for escaping, `csrf_token()`, `csrf_field()`, `verify_csrf()`.
- Create **migration** under `/database/migrations/` for `invites` table.
- Generate **bootstrap** layout partials (optional) for consistent styling.

**Acceptance criteria example** for POS open/close:

- When user opens session with start float, row created in `pos_sessions` with `status='open'` and `opened_by` set.
- Sales can only be recorded if there’s an **open** session for the current location.
- Close session requires end float; logs closed timestamp; shows variance summary.
- All actions are recorded in `audit_logs` with user_id.

---

## 13) Known pitfalls (be proactive)

- **Sessions** not shared across subdomains → ensure `session_init.php` is used everywhere, cookie domain set.
- **Wrong login redirects** → always send to `BASE_URL . 'auth/login.php'` from any subdomain.
- **Bcrypt cost** too high in XAMPP → default is fine; don’t reduce security.
- **Silent PDO failures** → set `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
- **XSS in admin forms** → escape outputs, validate inputs, add CSRF.

---

## 14) Glossary (project-specific)

- **Float**: the starting cash in POS till for a session.
- **Variance**: difference between expected and counted cash at session end.
- **Invite**: time-limited registration token created by admin.
- **Trialist**: player attending training but not registered yet (phase 2).

---

## 15) Quick Start (what to do first)

1. Ensure vhosts & hosts entries are correct (Section 3.1).
2. Create DB `svfc_os` and import `/database/schema.sql` then `/database/seed_data.sql`.
3. Create `/shared/config/config.local.php` (Section 3.2).
4. Ensure `/shared/includes/session_init.php` exists and is required by auth files.
5. Visit `portal.localhost/auth/login.php`, log in as Super Admin (from seed).
6. Visit `admin.localhost` to see the admin dashboard working.

---

## 16) Example prompt templates (for VSCode Chat)

- “**Implement CSRF** in `/portal/auth/login.php` and `/portal/auth/register.php`. Create helpers in `/shared/includes/helpers.php`. Update forms and POST handlers accordingly. Acceptance: token is verified and mismatch shows 403.”
- “**Scaffold News CRUD** under `/admin/` with PDO prepared statements, list + create + edit + delete, and add public list under `/public/news/`. Include audit logs on create/update/delete.”
- “**Add POS open/close session pages** in `/pos/`. Include start/end float forms, basic validation, and variance summary. Log actions.”
- “**Add login redirect-back**: if a user hits `/admin/` and is unauthenticated, send to central login with `?redirect=`; after login, return to original URL.”

---

_This file is the single source of truth for Copilot behaviour on this project. Keep changes atomic, aligned to Phase 1 scope unless explicitly instructed to move to later phases._
