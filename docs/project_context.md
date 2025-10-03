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

# Pending
- 2025-10-03: Re-run all jobs on Phase 1 PR to pick up updated ci.yml from main; owner: assistant; next step: verify green checks, then squash-merge
- 2025-10-03: Resolve PR conflicts on fix/memory-context-merge by retargeting the base to main or rebasing onto origin/feat/phase-1-release with conflict strategy; owner: assistant; next step: push and re-run checks
- 2025-10-03: Disable Composer scripts during CI install to prevent PR-target failures; owner: assistant; next step: create branch, update ci.yml, open PR, monitor checks