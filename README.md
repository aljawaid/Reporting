# Reporting for Kanboard

Reporting adds personal project budgets, usage entries, visual comparisons and printable summaries to Kanboard.

> [!IMPORTANT]
> Reporting data belongs to the signed-in user. Other project members cannot view or change it. Removing a budget also removes that user's usage entries for the same project after an explicit confirmation.

## Features

- Create one personal budget for each active project you can access.
- Record dated usage entries with optional notes.
- Compare budget, used and remaining amounts in accessible tables and charts.
- Produce a printable report using the latest usage note for each project.
- Use the interface on desktop, tablet and mobile widths.
- Follow Kanboard's light or dark colour scheme and reduced-motion preferences.
- Run from Kanboard installations hosted at the domain root or below a subdirectory.

## Requirements

| Component | Supported and tested versions |
| --- | --- |
| Kanboard | Minimum 1.2.54; CI baseline 1.2.54 |
| PHP | 8.2, 8.3, 8.4 and 8.5 |
| Database | SQLite; CI also exercises MySQL 8.4 and PostgreSQL 18 |

The automated compatibility matrix uses Kanboard 1.2.54 as the supported baseline.

## Installation

1. Back up the Kanboard database and current `plugins/Reporting` directory.
2. Create the directory `plugins/Reporting` if it does not already exist.
3. Copy this repository's files into that directory.
4. Sign in to Kanboard and open **Settings > Plugins** to confirm that Reporting is loaded.
5. Open **Budget overview** from the dashboard sidebar.

The directory name must remain exactly `Reporting` so Kanboard can discover the plugin namespace.

## Upgrading from 0.0.1

Version 1.0.0 automatically migrates existing SQLite budget and usage rows to the portable schema. The migration:

- preserves record IDs, project ownership, user ownership, amounts, dates and comments;
- converts integer amounts to portable decimal-capable columns;
- normalises historical null comments to empty text;
- adds indexes for the user, project and date lookups used by each view; and
- removes the obsolete legacy tables only after their rows have been copied.

Back up the database before upgrading. Do not remove or rename the existing legacy tables manually; Kanboard's plugin migration handles them.

## Use

### Budget overview

1. Select **Budget overview** from the dashboard sidebar.
2. Choose **Create budget**.
3. Select an accessible project and enter an amount, date and optional comment.
4. Use the edit or remove controls on an existing row when needed.

One user cannot create duplicate budgets for the same project. Different users can maintain independent budgets for a shared project.

### Budget usage

1. Select **Budget usage**.
2. Choose **Add usage** on a budgeted project.
3. Enter an amount, date and optional comment.
4. Remove an incorrect entry through its confirmation dialogue.

### Budget report

The report shows current totals and the latest usage note for every budgeted project. A project can be hidden from the current printout without changing stored data. Refreshing the page restores hidden rows.

## Data and access boundaries

- Every page requires an authenticated Kanboard user.
- Every write requires POST and a valid Kanboard form token.
- Project IDs are checked against the current user's active project access.
- Budget and usage IDs are always scoped to the signed-in user.
- Submitted fields are allowlisted and amounts, dates and comment lengths are validated server-side.
- Project names and comments are escaped before HTML output; chart labels are JSON encoded.
- No third-party fonts, scripts or analytics requests are added.

## Validation

The focused regression suite boots the plugin against Kanboard and checks its data model, request boundary, translation catalogue and presentation contracts:

```sh
php tests/run.php /path/to/kanboard
php tests/database_migrations.php sqlite
node --check assets/js/reporting.js
```

CI additionally runs fresh MySQL and PostgreSQL migration checks and the PHP 8.2–8.5 compatibility matrix.

## Licence and maintainers

- Original author: [TTJ](https://gitlab.com/u/ThomasTJ)
- Maintainer: [aljawaid](https://github.com/aljawaid)
- Licence: MIT

## Release history

See [changelog.md](changelog.md) for release notes.
