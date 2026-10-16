<?php

/**
 * Reporting MySQL schema migrations.
 *
 * Related test file: tests/DatabaseMigrationTest.php.
 */

namespace Kanboard\Plugin\Reporting\Schema;

use PDO;

const VERSION = 4;

function version_4(PDO $pdo)
{
    // Create portable user-owned budget and usage tables for fresh MySQL installs.
    $pdo->exec('CREATE TABLE IF NOT EXISTS reporting_budgets (
        id INT NOT NULL AUTO_INCREMENT,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        budget DECIMAL(19,2) NOT NULL,
        comment TEXT NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY(id),
        INDEX reporting_budget_user_project_date_idx (user_id, project_id, date),
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS reporting_usage (
        id INT NOT NULL AUTO_INCREMENT,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        used DECIMAL(19,2) NOT NULL,
        comment TEXT NOT NULL,
        date DATE NOT NULL,
        PRIMARY KEY(id),
        INDEX reporting_usage_user_project_date_idx (user_id, project_id, date),
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB CHARSET=utf8mb4');
}
