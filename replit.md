# iTeachXR — AI-First Learning Management System

## Overview
iTeachXR is a PHP LMS hosted on Replit targeting The VR School (Stanford, CA). It supports teachers and students with AI-enhanced dashboards, collaborative workspaces, and full transcript management.

## Architecture

### Stack
- **Backend**: PHP 8 (built-in server via `php -S 0.0.0.0:5000 router.php`)
- **Database**: PostgreSQL (Neon/Replit) via PDO — connect using `DATABASE_URL`
- **Auth**: Google SSO via `ai.thevrschool.org` canonical surface; HMAC-SHA256 bridge tokens
- **Frontend**: Bootstrap 5.3, Font Awesome 6, Poppins + Playfair Display fonts

### Entry Point
`router.php` — maps clean URLs to PHP files, serves all requests through the built-in server.

### Auth Flow
1. Unauthenticated users → `auth/login.php` → redirect to `https://ai.thevrschool.org/api/auth/sso/handoff`
2. After Google sign-in, canonical site sends user to `/api/auth/sso/finish?token=...`
3. `api/auth/sso/finish.php` verifies HMAC-SHA256 bridge token using `NEXTAUTH_SECRET`
4. Sets PHP session via `auth/session.php` → redirect to destination

### Key Files
```
auth/
  session.php        — session helpers: auth_require(), auth_user(), auth_set(), auth_clear()
  login.php          — login page + redirect to SSO
  logout.php         — clear session + redirect to canonical signout

api/auth/sso/
  finish.php         — bridge token verifier (HMAC-SHA256)

lib/
  db_connection.php  — get_db_connection() using DATABASE_URL with sslmode=require

includes/
  teacher_sidebar.php — teacher nav with user avatar + signout
  student_sidebar.php — student nav with user avatar + signout

teacher/
  dashboard.php, collaborative_workspace.php, document_editor.php, workspace_detail.php

student/
  dashboard.php      — real DB data: GPA, transcript preview, enrolled courses
  courses.php        — enrolled LMS courses with progress
  transcript.php     — full official transcript (print/PDF mode via ?print=1)

db/
  migrate_transcripts.php — schema migration + Ian Jiang seed data
```

## Database Schema
Key tables: `users`, `courses`, `enrollments`, `modules`, `activities`, `submissions`,
`student_profiles`, `transcript_entries`

## Real Student: Ian Jiang
- Email: `ian09jiang@gmail.com`
- Student ID: `28467382VR`
- Cumulative GPA: 4.00 (Unweighted)
- Grade: Sophomore (Grade 10, 2025–2026)
- Graduation: June 2029
- 32 transcript entries across Grade 9 (160 credits) + Grade 10 (80 credits)

## Environment Secrets Required
| Secret | Purpose |
|---|---|
| `DATABASE_URL` | Neon PostgreSQL connection (includes sslmode=require) |
| `NEXTAUTH_SECRET` | Shared HMAC secret for bridge token verification |
| `OPENAI_API_KEY` | AI features |
| `GITHUB_PERSONAL_ACCESS_TOKEN` | GitHub pushes |

## Deployment
- Dev: `php -S 0.0.0.0:5000 router.php`
- Sister sites: `ai.thevrschool.org` (canonical SSO), `sof.ai`, `iteachxr.com`
- GitHub: `github.com/DearMrFree/iteachxr`
