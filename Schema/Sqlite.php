<?php

namespace Kanboard\Plugin\Reporting\Schema;

use Kanboard\Core\Security\Token;
use Kanboard\Core\Security\Role;
use PDO;

const VERSION = 3;

function version_3(PDO $pdo)
{
    $pdo->exec("ALTER TABLE reportingBudget ADD COLUMN is_active INTERGER");
}

function version_2($pdo)
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS reportingBudget (
        "id" INTEGER PRIMARY KEY,
        "project_id" INTEGER NOT NULL,
        "user_id" INTEGER NOT NULL,
        "budget" INTEGER NOT NULL,
        "comment" TEXT,
        "date" TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS reportingUsed (
        "id" INTEGER PRIMARY KEY,
        "project_id" INTEGER NOT NULL,
        "user_id" INTEGER NOT NULL,
        "used" INTEGER NOT NULL,
        "comment" TEXT,
        "date" TEXT NOT NULL,
        FOREIGN KEY(project_id) REFERENCES projects(id) ON DELETE CASCADE
    )');
}
