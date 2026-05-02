# GitHub Sync — Setup & Usage

This document explains how to keep your Replit project automatically in sync with GitHub.

## How It Works

A script (`sync_to_github.sh`) pushes your current Replit codebase to a GitHub repository using a Personal Access Token stored as a Replit Secret.

## One-Time Setup

### 1. Create a GitHub Personal Access Token

1. Go to **GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)**
2. Click **Generate new token**
3. Give it a descriptive name (e.g. `iTeachXR Replit sync`)
4. Select the **`repo`** scope (full control of private repositories)
5. Click **Generate token** and copy the value immediately — you won't see it again

### 2. Add Secrets to Replit

In your Replit project, open the **Secrets** panel and add:

| Key | Value |
|---|---|
| `GITHUB_TOKEN` | The token you just created |
| `GITHUB_REPO_URL` | Your repo's HTTPS URL, e.g. `https://github.com/your-username/iteachxr` |

### 3. Verify the Script Is Present

The file `sync_to_github.sh` should already exist in the project root. If not, it was deleted — restore it from version history.

## Pushing Changes to GitHub

Every time you want to sync Replit → GitHub, open the **Shell** tab and run:

```bash
bash sync_to_github.sh
```

The script will:
1. Configure the GitHub remote using your token
2. Push the current branch (`main`) to GitHub
3. Print a confirmation link when done

## Automating the Push (Optional)

If you want the push to happen after every Replit agent task, you can add the following to your `.replit` file under a workflow task or run it manually at the end of each session. There is no built-in Replit trigger that fires on every save, but you can call the script as often as you like from the Shell.

## Troubleshooting

| Error | Fix |
|---|---|
| `GITHUB_TOKEN is not set` | Add the secret in Replit Secrets panel |
| `GITHUB_REPO_URL is not set` | Add the secret in Replit Secrets panel |
| `Permission denied (publickey)` | Make sure you are using the HTTPS URL, not the SSH URL |
| `Authentication failed` | Token may be expired or missing the `repo` scope — regenerate it |
