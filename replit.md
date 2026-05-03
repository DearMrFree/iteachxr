# iTeachXR — AI-First Learning Management System

## Overview
iTeachXR is a PHP LMS hosted on Replit targeting The VR School (Stanford, CA). It supports teachers and students with AI-enhanced dashboards, collaborative workspaces, and full transcript management.

## Architecture

### Stack
- **Backend**: PHP 8.2 (built-in server via `php -S 0.0.0.0:5000 router.php`)
- **Database**: PostgreSQL 17 on **Fly.io** (`sofaitranscript.fly.dev`) via PDO — uses `FLY_DATABASE_URL` secret
- **Auth**: Google SSO via `ai.thevrschool.org` canonical surface; HMAC-SHA256 bridge tokens
- **Frontend**: Bootstrap 5.3, Font Awesome 6, Poppins + Playfair Display + Source Serif 4 fonts

### Entry Point
`router.php` — maps clean URLs to PHP files, serves all requests through the built-in server.

### Auth Flow
1. Unauthenticated users → `auth/login.php` → redirect to `https://ai.thevrschool.org/api/auth/sso/handoff`
2. After Google sign-in, canonical site sends user to `/api/auth/sso/finish?token=...`
3. `api/auth/sso/finish.php` verifies HMAC-SHA256 bridge token using `NEXTAUTH_SECRET`
4. Calls `db_upsert_user()` → creates user row + auto-provisions `student_profiles` on first login
5. Sets PHP session via `auth/session.php` → redirect to destination

### Key Files
```
auth/
  session.php        — session helpers: auth_require(), auth_user(), auth_set(), auth_clear()
  login.php          — login page + redirect to SSO
  logout.php         — clear session + redirect to canonical signout

api/auth/sso/
  finish.php         — bridge token verifier + db_upsert_user() on every login

lib/
  db_connection.php  — get_db_connection() (FLY_DATABASE_URL → DATABASE_URL → PG* vars)
                       db_upsert_user()            — upsert user + auto-provision on first SSO login
                       _db_refresh_student_totals() — recomputes GPA/credits from transcript on every login
                       _db_compute_student_totals() — weighted GPA calculation (A+=4.3 … F=0.0)

includes/
  teacher_sidebar.php — teacher nav with user avatar + signout
  student_sidebar.php — student nav with user avatar + signout

teacher/
  dashboard.php, collaborative_workspace.php, document_editor.php, workspace_detail.php

student/
  dashboard.php         — GPA ring, stat cards, recent transcript, quick actions
  courses.php           — enrolled LMS courses with progress bars
  transcript.php        — full official transcript (print/PDF via ?print=1); DB-first, Ian fallback
  transcript_preview.php — auth-bypass preview for canvas/admin review

db/
  migrate.php             — complete idempotent schema migration (run on fresh deploys)
  seed.php                — idempotent seed: Ian's 32-course transcript + Freedom admin
  migrate_transcripts.php — legacy first-run script (superseded by migrate.php + seed.php)
```

## Database Schema (Fly.io Postgres — sofaitranscript.fly.dev)
```sql
users               — id, email, firstname, lastname, role, avatar_url, created_at, last_login
student_profiles    — id, user_id, student_id, address, current_grade, enrollment_status,
                      graduation_date, gpa, total_credits
transcript_entries  — id, user_id, grade_level, school_year, seq, ucag_id, subject_area,
                      course_title, course_level, grade, credits
```

### Auto-provisioning
Every new student who signs in via Google SSO gets:
- A `users` row (role=student)
- A `student_profiles` row with a unique `<8-char-hex>VR` student ID, Grade 9, Good Standing

Admins/teachers then add `transcript_entries` rows to populate their academic record.

## Real Student: Ian Jiang
- Email: `ian09jiang@gmail.com`
- Student ID: `28467382VR`
- Cumulative GPA: 4.00 (Unweighted)
- Grade: Sophomore (Grade 10, 2025–2026)
- Graduation: June 2029
- 32 transcript entries across Grade 9 (160 credits) + Grade 10 (80 credits)
- Fully seeded in Fly.io DB; hardcoded fallback in transcript.php/dashboard.php as backup

## Environment Secrets Required
| Secret | Purpose |
|---|---|
| `FLY_DATABASE_URL` | **Primary** — Fly.io Postgres (sofaitranscript.fly.dev:5432) |
| `DATABASE_URL` | Fallback — Replit/Neon PostgreSQL |
| `NEXTAUTH_SECRET` | Shared HMAC secret for SSO bridge token verification |
| `OPENAI_API_KEY` | AI features |
| `GITHUB_PERSONAL_ACCESS_TOKEN` | GitHub pushes |

## Deployment
- Dev: `php -S 0.0.0.0:5000 router.php`
- Sister sites: `ai.thevrschool.org` (canonical SSO), `sof.ai`, `iteachxr.com`
- GitHub: `github.com/DearMrFree/iteachxr`
- Fly.io DB: `sofaitranscript` app — public IP 137.66.20.212, port 5432
