# Overview
Clark English Learning (CEL) – Phase 1. A Laravel 10 app providing course catalog, checkout (Stripe), user auth, admin dashboard, SEO-ready pages, and deployment tooling.

# Goals
- Get Phase 1 release branch squash-merged into main with green CI
- Ensure CI stability (SQLite-based tests, artifact action v4) on main
- Complete cPanel deploy with a quick smoke test

# Progress
- 2025-10-03: CI workflow prepared on branch fix/ci-sqlite (SQLite test DB, APP_KEY generation without artisan, CACHE_DRIVER corrected, actions/upload-artifact migrated to v4)
- 2025-10-03: Resolved PR conflicts by taking main versions of ci.yml and project_context.md; retriggered CI on feat/phase-1-release
- 2025-10-03: Enforced CI working-directory to repo root via defaults.run and explicit working-directory on composer/test steps; pushed to feat/phase-1-release to retrigger checks
- 2025-10-03: Merged Docs PR (#6) into main via squash; CI updated and branch auto-deleted
- 2025-10-03: Fixed CI defaults.run working-directory to '.' and switched tests to vendor/bin/phpunit with artisan fallback; opened PR #7 and squash-merged; CI green on main (Run ID 18217456324)
- 2025-10-03: Disabled Composer scripts during CI install; opened PR #8 and squash-merged; CI green on main (Run ID 18217837642)
- 2025-10-03: Closed stale PR #3 (Fix/ci working directory) as superseded by later CI fixes (#4, #7, #8)
- 2025-10-03: Verified Phase 1 PR (#1) merged with main; checks green on main

# Pending
- 2025-10-03: Production deployment to GreenGeeks cPanel using deploy.sh package; owner: assistant; next step: fix deploy.sh .env copy, build assets, run deploy.sh, zip package, upload via cPanel/SFTP, configure .env, run artisan migrate/storage:link/cache