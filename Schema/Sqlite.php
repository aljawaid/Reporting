<?php

/**
 * Reporting SQLite schema migrations.
 *
 * Related test file: tests/DatabaseMigrationTest.php.
 */

namespace Kanboard\Plugin\Reporting\Schema;

use PDO;

const VERSION = 4;

function version_4(PDO $pdo)
{
    // Rebuild the legacy tables under portable names while preserving every row.
    $pdo->exec('DROP TABLE IF EXISTS reporting_budgets_v4');
    $pdo->exec('CREATE TABLE reporting_budgets_v4 (
        id INTEGER PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        budget NUMERIC(19,2) NOT NULL,
        comment TEXT NOT NULL DEFAULT \'\',
        date TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
    $pdo->exec('INSERT INTO reporting_budgets_v4 (id, project_id, user_id, budget, comment, date)
        SELECT id, project_id, user_id, CAST(budget AS NUMERIC), COALESCE(comment, \'\'), date
        FROM reportingBudget');
    $pdo->exec('DROP TABLE reportingBudget');
    $pdo->exec('ALTER TABLE reporting_budgets_v4 RENAME TO reporting_budgets');

    $pdo->exec('DROP TABLE IF EXISTS reporting_usage_v4');
    $pdo->exec('CREATE TABLE reporting_usage_v4 (
        id INTEGER PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        used NUMERIC(19,2) NOT NULL,
        comment TEXT NOT NULL DEFAULT \'\',
        date TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
    $pdo->exec('INSERT INTO reporting_usage_v4 (id, project_id, user_id, used, comment, date)
        SELECT id, project_id, user_id, CAST(used AS NUMERIC), COALESCE(comment, \'\'), date
        FROM reportingUsed');
    $pdo->exec('DROP TABLE reportingUsed');
    $pdo->exec('ALTER TABLE reporting_usage_v4 RENAME TO reporting_usage');

    // Support the user/project/date lookups used by every Reporting view.
    $pdo->exec('CREATE INDEX reporting_budget_user_project_date_idx
        ON reporting_budgets (user_id, project_id, date)');
    $pdo->exec('CREATE INDEX reporting_usage_user_project_date_idx
        ON reporting_usage (user_id, project_id, date)');
}

function version_3(PDO $pdo)
{
    // Preserve the historical migration sequence required by existing installs.
    $pdo->exec('ALTER TABLE reportingBudget ADD COLUMN is_active INTEGER');
}

function version_2(PDO $pdo)
{
    // Create the original data model before version 4 performs the safe rebuild.
    $pdo->exec('CREATE TABLE IF NOT EXISTS reportingBudget (
        id INTEGER PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        budget INTEGER NOT NULL,
        comment TEXT,
        date TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS reportingUsed (
        id INTEGER PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        used INTEGER NOT NULL,
        comment TEXT,
        date TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
}
