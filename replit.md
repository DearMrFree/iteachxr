# iTeachXR — Project Overview

## Stack
- **Backend**: PHP (served via `php -S 0.0.0.0:5000`)
- **AI Services**: Python 3.11 (OpenAI integration)
- **Database**: PostgreSQL 16
- **Runtime**: NixOS stable-24_05 on Replit

## Project Structure
- `index.php` — Main entry point / landing page
- `config.php` — Configuration (reads env vars for DB and OpenAI)
- `admin/` — Admin dashboard
- `course/` — Course viewing and editing
- `student/` — Student dashboard and tools
- `teacher/` — Teacher dashboard and tools
- `mod/assignment/` — Assignment submission and review
- `lib/` — Core libraries (`moodlelib.php`, `db_connection.php`, `setup.php`)
- `includes/` — Shared UI components
- `ai/` — Python AI services (feedback, recommendations, assistant)
- `api/` — PHP API endpoints
- `theme/iteachxr/` — Custom CSS, JS, layouts

## Environment Secrets Required
| Secret | Purpose |
|---|---|
| `DATABASE_URL` | PostgreSQL connection string |
| `OPENAI_API_KEY` | OpenAI API key for AI features |
| `GITHUB_TOKEN` | GitHub Personal Access Token (repo scope) — for GitHub sync |
| `GITHUB_REPO_URL` | HTTPS URL of the GitHub repository — for GitHub sync |

## GitHub Sync
Changes can be pushed to GitHub by running:
```bash
bash sync_to_github.sh
```

See **GITHUB_SYNC.md** for full setup instructions.

## Workflows
- **iTeachXR Server**: `php -S 0.0.0.0:5000` on port 5000 (external port 80)
