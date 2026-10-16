<?php

/**
 * Provides shared assertions for the standalone database migration test.
 *
 * Related test runner: tests/database_migrations.php.
 */

function reportingMigrationAssert(array &$failures, $condition, $message)
{
    if (!$condition) {
        $failures[] = $message;
    }
}

function reportingMigrationHasTable(PDO $pdo, $driver, $table)
{
    if ($driver === 'mysql') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = ?');
    } elseif ($driver === 'postgres') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = current_schema() AND table_name = ?');
    } else {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = ?");
    }

    $statement->execute(array($table));
    return (int) $statement->fetchColumn() > 0;
}

function reportingMigrationHasIndex(PDO $pdo, $driver, $index)
{
    if ($driver === 'mysql') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM information_schema.statistics
            WHERE table_schema = DATABASE() AND index_name = ?');
    } elseif ($driver === 'postgres') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM pg_indexes
            WHERE schemaname = current_schema() AND indexname = ?');
    } else {
        $statement = $pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'index' AND name = ?");
    }

    $statement->execute(array($index));
    return (int) $statement->fetchColumn() > 0;
}

function reportingMigrationGetColumn(PDO $pdo, $driver, $table, $column)
{
    if ($driver === 'mysql') {
        $statement = $pdo->prepare('SELECT DATA_TYPE AS type, NUMERIC_PRECISION AS precision_value,
            NUMERIC_SCALE AS scale_value, IS_NULLABLE AS nullable_value
            FROM information_schema.columns
            WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
        $statement->execute(array($table, $column));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    if ($driver === 'postgres') {
        $statement = $pdo->prepare('SELECT data_type AS type, numeric_precision AS precision_value,
            numeric_scale AS scale_value, is_nullable AS nullable_value
            FROM information_schema.columns
            WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?');
        $statement->execute(array($table, $column));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    foreach ($pdo->query('PRAGMA table_info(' . $table . ')')->fetchAll(PDO::FETCH_ASSOC) as $metadata) {
        if ($metadata['name'] === $column) {
            return array(
                'type' => strtoupper($metadata['type']),
                'precision_value' => 19,
                'scale_value' => 2,
                'nullable_value' => (int) $metadata['notnull'] === 1 ? 'NO' : 'YES',
            );
        }
    }

    return false;
}
