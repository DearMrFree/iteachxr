#!/usr/bin/env bash
set -euo pipefail

# sync_to_github.sh
# Pushes the current Replit codebase to the connected GitHub repository.
# Requires two secrets to be set in Replit:
#   GITHUB_TOKEN    - A GitHub Personal Access Token with 'repo' scope
#   GITHUB_REPO_URL - The HTTPS URL of your GitHub repo
#                     e.g. https://github.com/your-username/your-repo

if [ -z "${GITHUB_TOKEN:-}" ]; then
  echo "ERROR: GITHUB_TOKEN is not set. Add it in Replit Secrets." >&2
  exit 1
fi

if [ -z "${GITHUB_REPO_URL:-}" ]; then
  echo "ERROR: GITHUB_REPO_URL is not set. Add it in Replit Secrets." >&2
  exit 1
fi

REMOTE_NAME="github"

# Strip protocol so we can inject the token
REPO_PATH="${GITHUB_REPO_URL#https://}"
AUTHENTICATED_URL="https://${GITHUB_TOKEN}@${REPO_PATH}"

# Add or update the remote
if git remote get-url "$REMOTE_NAME" &>/dev/null; then
  git remote set-url "$REMOTE_NAME" "$AUTHENTICATED_URL"
else
  git remote add "$REMOTE_NAME" "$AUTHENTICATED_URL"
fi

BRANCH="$(git rev-parse --abbrev-ref HEAD)"
echo "Pushing branch '${BRANCH}' to GitHub..."
git push "$REMOTE_NAME" "$BRANCH" --force

echo "Done! Changes are now live at: ${GITHUB_REPO_URL}"
