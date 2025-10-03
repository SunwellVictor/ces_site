# Overview
Clark English Learning (CEL) – Phase 1. A Laravel 10 app providing course catalog, checkout (Stripe), user auth, admin dashboard, SEO-ready pages, and deployment tooling.

# Goals
- Get Phase 1 release branch squash-merged into main with green CI
- Ensure CI stability (SQLite-based tests, artifact action v4) on main
- Complete cPanel deploy with a quick smoke test

# Progress
- 2025-10-03: CI workflow prepared on branch fix/ci-sqlite (SQLite test DB, APP_KEY generation without artisan, CACHE_DRIVER corrected, actions/upload-artifact migrated to v4)
- 2025-10-03: Verified ci.yml on main uses actions/upload-artifact@v4; created clean branch fix/ci-artifact-v4-main (no code changes needed)

# Pending
- 2025-10-03: Re-run all jobs on Phase 1 PR to pick up updated ci.yml from main; owner: assistant; next step: trigger re-run, verify green checks, then squash-merge