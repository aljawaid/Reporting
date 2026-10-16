<?php

/**
 * Verifies fresh Reporting schemas on SQLite, MySQL and PostgreSQL, plus the legacy SQLite upgrade.
 *
 * Related production files: Schema/Sqlite.php, Schema/Mysql.php and Schema/Postgres.php.
 * Related helper file: tests/DatabaseMigrationTest.php.
 */

if ($argc !== 2 || !in_array($argv[1], array('sqlite', 'mysql', 'postgres'), true)) {
    fwrite(STDERR, 'Usage: php tests/database_migrations.php sqlite|mysql|postgres' . PHP_EOL);
    exit(2);
}

$driver = $argv[1];
$failures = array();

require __DIR__ . '/DatabaseMigrationTest.php';

function reportingConnectDatabase($driver)
{
    if ($driver === 'sqlite') {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('PRAGMA foreign_keys = ON');
    } else {
        $dsn = getenv('REPORTING_TEST_DSN');
        $user = getenv('REPORTING_TEST_USER');
        $password = getenv('REPORTING_TEST_PASSWORD');

        if ($dsn === false || $user === false || $password === false) {
            throw new RuntimeException('Database connection environment variables are required.');
        }

        $pdo = new PDO($dsn, $user, $password);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function reportingResetDatabase(PDO $pdo, $driver)
{
    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    }

    foreach (array(
        'reporting_usage',
        'reporting_budgets',
        'reportingUsed',
        'reportingBudget',
        'users',
        'projects',
    ) as $table) {
        $suffix = $driver === 'postgres' ? ' CASCADE' : '';
        $pdo->exec('DROP TABLE IF EXISTS ' . $table . $suffix);
    }

    if ($driver === 'mysql') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

function reportingCreateCoreTables(PDO $pdo, $driver)
{
    if ($driver === 'mysql') {
        $pdo->exec('CREATE TABLE users (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
        $pdo->exec('CREATE TABLE projects (id INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
    } else {
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY)');
        $pdo->exec('CREATE TABLE projects (id INTEGER PRIMARY KEY)');
    }

    $pdo->exec('INSERT INTO users (id) VALUES (1)');
    $pdo->exec('INSERT INTO projects (id) VALUES (1)');
}

function reportingRunMigrationVersions($namespace, $firstVersion, $lastVersion, PDO $pdo)
{
    for ($version = $firstVersion; $version <= $lastVersion; $version++) {
        $migration = $namespace . 'version_' . $version;

        if (function_exists($migration)) {
            $migration($pdo);
        }
    }
}

function reportingVerifyCurrentSchema(PDO $pdo, $driver, array &$failures, $scenario)
{
    $budget = reportingMigrationGetColumn($pdo, $driver, 'reporting_budgets', 'budget');
    $used = reportingMigrationGetColumn($pdo, $driver, 'reporting_usage', 'used');
    $expectedType = $driver === 'mysql' ? 'decimal' : ($driver === 'postgres' ? 'numeric' : 'NUMERIC(19,2)');

    reportingMigrationAssert($failures, $budget !== false, $scenario . ': budget column is missing.');
    reportingMigrationAssert($failures, $used !== false, $scenario . ': usage column is missing.');
    reportingMigrationAssert($failures, $budget !== false && $budget['type'] === $expectedType, $scenario . ': budget type is not fixed-scale.');
    reportingMigrationAssert($failures, $used !== false && $used['type'] === $expectedType, $scenario . ': usage type is not fixed-scale.');
    reportingMigrationAssert($failures, $budget !== false && (int) $budget['precision_value'] === 19, $scenario . ': budget precision is incorrect.');
    reportingMigrationAssert($failures, $budget !== false && (int) $budget['scale_value'] === 2, $scenario . ': budget scale is incorrect.');
    reportingMigrationAssert($failures, $used !== false && (int) $used['precision_value'] === 19, $scenario . ': usage precision is incorrect.');
    reportingMigrationAssert($failures, $used !== false && (int) $used['scale_value'] === 2, $scenario . ': usage scale is incorrect.');
    reportingMigrationAssert($failures, reportingMigrationHasIndex($pdo, $driver, 'reporting_budget_user_project_date_idx'), $scenario . ': budget index is missing.');
    reportingMigrationAssert($failures, reportingMigrationHasIndex($pdo, $driver, 'reporting_usage_user_project_date_idx'), $scenario . ': usage index is missing.');
}

try {
    $pdo = reportingConnectDatabase($driver);
    $schemaName = $driver === 'postgres' ? 'Postgres' : ucfirst($driver);
    $schemaFile = dirname(__DIR__) . '/Schema/' . $schemaName . '.php';
    $namespace = 'Kanboard\\Plugin\\Reporting\\Schema\\';

    require $schemaFile;
    $latestVersion = constant($namespace . 'VERSION');

    reportingResetDatabase($pdo, $driver);
    reportingCreateCoreTables($pdo, $driver);
    reportingRunMigrationVersions($namespace, 1, $latestVersion, $pdo);
    reportingVerifyCurrentSchema($pdo, $driver, $failures, 'Fresh install');

    $pdo->exec("INSERT INTO reporting_budgets (id, project_id, user_id, budget, comment, date)
        VALUES (11, 1, 1, 12345.67, 'fresh budget', '2026-10-16')");
    $pdo->exec("INSERT INTO reporting_usage (id, project_id, user_id, used, comment, date)
        VALUES (12, 1, 1, 234.56, 'fresh usage', '2026-10-16')");
    reportingMigrationAssert(
        $failures,
        number_format((float) $pdo->query('SELECT budget FROM reporting_budgets WHERE id = 11')->fetchColumn(), 2, '.', '') === '12345.67',
        'Fresh install: budget precision did not round-trip.'
    );
    reportingMigrationAssert(
        $failures,
        number_format((float) $pdo->query('SELECT used FROM reporting_usage WHERE id = 12')->fetchColumn(), 2, '.', '') === '234.56',
        'Fresh install: usage precision did not round-trip.'
    );
    $pdo->exec('DELETE FROM projects WHERE id = 1');
    reportingMigrationAssert(
        $failures,
        (int) $pdo->query('SELECT COUNT(*) FROM reporting_budgets')->fetchColumn() === 0,
        'Fresh install: budgets did not cascade with their project.'
    );
    reportingMigrationAssert(
        $failures,
        (int) $pdo->query('SELECT COUNT(*) FROM reporting_usage')->fetchColumn() === 0,
        'Fresh install: usage did not cascade with its project.'
    );

    if ($driver === 'sqlite') {
        reportingResetDatabase($pdo, $driver);
        reportingCreateCoreTables($pdo, $driver);
        reportingRunMigrationVersions($namespace, 2, 3, $pdo);
        $pdo->exec("INSERT INTO reportingBudget (id, project_id, user_id, budget, comment, date, is_active)
            VALUES (41, 1, 77, 9876, NULL, '2026-10-14', 1)");
        $pdo->exec("INSERT INTO reportingUsed (id, project_id, user_id, used, comment, date)
            VALUES (42, 1, 77, 321, 'preserve me', '2026-10-15')");
        reportingRunMigrationVersions($namespace, 4, 4, $pdo);
        reportingVerifyCurrentSchema($pdo, $driver, $failures, 'SQLite upgrade');

        $legacyBudget = $pdo->query('SELECT * FROM reporting_budgets WHERE id = 41')->fetch(PDO::FETCH_ASSOC);
        $legacyUsage = $pdo->query('SELECT * FROM reporting_usage WHERE id = 42')->fetch(PDO::FETCH_ASSOC);
        reportingMigrationAssert($failures, (int) $legacyBudget['user_id'] === 77, 'SQLite upgrade: budget ownership was not preserved.');
        reportingMigrationAssert($failures, (float) $legacyBudget['budget'] === 9876.0, 'SQLite upgrade: budget amount was not preserved.');
        reportingMigrationAssert($failures, $legacyBudget['comment'] === '', 'SQLite upgrade: null budget comments were not normalised.');
        reportingMigrationAssert($failures, $legacyUsage['comment'] === 'preserve me', 'SQLite upgrade: usage comments were not preserved.');
        reportingMigrationAssert($failures, !reportingMigrationHasTable($pdo, $driver, 'reportingBudget'), 'SQLite upgrade: legacy budget table remains.');
        reportingMigrationAssert($failures, !reportingMigrationHasTable($pdo, $driver, 'reportingUsed'), 'SQLite upgrade: legacy usage table remains.');
    }

    reportingResetDatabase($pdo, $driver);
} catch (Throwable $exception) {
    $failures[] = $driver . ': ' . $exception->getMessage();
}

if ($failures !== array()) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo 'Reporting ' . $driver . ' fresh and upgrade migrations passed.' . PHP_EOL;
