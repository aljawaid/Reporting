# Changelog

## v1.0.3 - 2026-10-20

- FIX: DEPENDENCY: Complete Reporting Update Automation
- FIX: DEPENDENCY: Upgrade Super-Linter To 9.0.0
- No database changes.

## 1.0.2 - 2026-10-18

- FIX: Renamed the plug-in asset directory to canonical `Assets/` casing so
  protected Linux Kanboard deployments serve the stylesheet and JavaScript
  instead of returning HTTP 403 responses.
- Relates to `1c863c0`.
- No database changes.

## 1.0.1 - 2026-10-18

- Renamed the canonical changelog to `changelog.md` so release auditing and
  Kanboard package preparation use the expected cross-platform filename.
- No database changes.

## 1.0.0 - 2026-10-16

- Added user-scoped POST and CSRF protection for every budget and usage write.
- Added server-side project access, ownership, amount, date and comment validation.
- Added upgrade-safe SQLite migration and fresh MySQL and PostgreSQL schemas.
- Fixed empty-project errors, duplicate user budget handling and zero-budget calculations.
- Replaced hard-coded asynchronous URLs with Kanboard-generated routes and modal forms.
- Escaped rendered project and comment data and JSON encoded chart labels.
- Added responsive tables and cards, light and dark colours, smooth transitions and print support.
- Added British English translations and an aligned translation starter catalogue.
- Added focused Kanboard, database, JavaScript, presentation and translation regression checks.
- Updated compatibility guidance for Kanboard 1.2.54 and PHP 8.2–8.5.

## 0.0.1

- Added the original SQLite project budget, usage and report views.
