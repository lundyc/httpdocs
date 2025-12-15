\# 🧭 MyClubHub Development Checklist

\_Last updated: 14 Oct 2025\_



---



\## Phase 1 — Core \& Foundation



\### ✅ Completed

\- \[x] Database structure finalised and patched

\- \[x] Core tables: users, roles, sessions, audit\_logs

\- \[x] Fixtures model (seasons, competitions, venues, teams)

\- \[x] POS schema: sessions, sales, refunds, locations

\- \[x] Sponsors, stock, season tickets base tables

\- \[x] Invite creation + registration system

\- \[x] Admin dashboard and stats layout

\- \[x] Audit logging with IP

\- \[x] News table and insert test



---



\## 🟡 In Progress

\- \[ ] Invite dashboard (`/admin/invites/index.php`)

\- \[ ] Session persistence cross-module (portal ↔ admin)

\- \[ ] Public fixtures + news frontend pages

\- \[ ] POS session handler refinement (`/pos/session.php`)

\- \[ ] POS UI (Open → Sell → Close → Refund)

\- \[ ] Team portal dashboard and fixture list

\- \[ ] Season tickets admin page

\- \[ ] SSL certificate install + HTTPS redirect

\- \[ ] CSRF helper integration

\- \[ ] `Testing\_Plan\_Phase1.md` creation



---



\## ⚙️ Upcoming (Phase 1.5 / 2 Prep)

\- \[ ] Training sessions + attendance tracking

\- \[ ] Player profile management

\- \[ ] Sponsor profiles + showcase page

\- \[ ] Financial dashboard (Treasurer view)

\- \[ ] Public homepage rebuild (MyClubHub branding)

\- \[ ] Automated email system (invites, receipts)

\- \[ ] Migration of season\_tickets.season → season\_id

\- \[ ] Add GDPR footer + policy pages

\- \[ ] Begin PWA/mobile optimisation



---



\## 📁 File Locations

| Directory | Purpose |

|------------|----------|

| `/admin/` | Administration \& CRUD dashboards |

| `/portal/` | Manager \& player portal |

| `/pos/` | Point of Sale system |

| `/public/` | Public website |

| `/shared/` | Includes, configs, auth libs |

| `/docs/` | Documentation (checklists, testing, deployment) |



---



\## 🔒 Verification Tests (Post-Deploy)

\- \[ ] Log in/out works across all sections

\- \[ ] New invite link successfully registers user

\- \[ ] POS session opens/closes correctly

\- \[ ] Sales + refunds recorded in audit log

\- \[ ] Admin dashboard stats update dynamically

\- \[ ] Public fixtures + news pages render without errors



---



\## 🧱 Next Commit Plan

1\. Finalise `/admin/invites/index.php`

2\. Implement `/pos/session.php`

3\. Add `/portal/dashboard.php` + `/portal/fixtures.php`

4\. Add basic CSRF helper (`/shared/includes/security.php`)

5\. Test live on VPS under HTTPS

6\. Export new DB snapshot → `mch\_os\_phase1\_complete.sql`



---



\_Authored by: Colin Lundy \& GPT-5 (Project Manager / Senior Full-Stack Dev)\_



