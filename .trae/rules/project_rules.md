# TRAE Project Rules

1. **Check Context**
   - Load `docs/project_context.md` at the start of each session.

2. **Log Task**
   - When a new problem/idea is given, add it under `# Pending` in `docs/project_context.md` with today’s date.
   - Confirm task logging in chat before starting work.

3. **Implement**
   - Create a feature branch (`fix/<short>` or `feat/<short>`).
   - Write the code.
   - Run local tests (bot starts, changed command works).

4. **Commit & PR**
   - If the change works locally, commit with Conventional Commits format.
   - Push branch, open PR to `main` with template:
     - Summary
     - Affected commands
     - Manual test ✅
     - Rollback plan
   - Squash & Merge when criteria are satisfied.

5. **Update Context**
   - Move the task from `# Pending` → `# Progress` (with result and date).
   - Add any decisions or notes relevant to future work.

6. **Close Session**
   - End summary in chat with `[📝 MEMORY UPDATED]`.