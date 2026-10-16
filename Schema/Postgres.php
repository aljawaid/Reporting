<?php

/**
 * Reporting PostgreSQL schema migrations.
 *
 * Related test file: tests/DatabaseMigrationTest.php.
 */

namespace Kanboard\Plugin\Reporting\Schema;

use PDO;

const VERSION = 4;

function version_4(PDO $pdo)
{
    // Create portable user-owned budget and usage tables for fresh PostgreSQL installs.
    $pdo->exec('CREATE TABLE IF NOT EXISTS reporting_budgets (
        id SERIAL PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        budget NUMERIC(19,2) NOT NULL,
        comment TEXT NOT NULL,
        date DATE NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS reporting_budget_user_project_date_idx
        ON reporting_budgets (user_id, project_id, date)');

    $pdo->exec('CREATE TABLE IF NOT EXISTS reporting_usage (
        id SERIAL PRIMARY KEY,
        project_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        used NUMERIC(19,2) NOT NULL,
        comment TEXT NOT NULL,
        date DATE NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS reporting_usage_user_project_date_idx
        ON reporting_usage (user_id, project_id, date)');
}
