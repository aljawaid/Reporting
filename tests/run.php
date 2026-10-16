<?php

// phpcs:ignoreFile PSR1.Files.SideEffects.FoundWithSymbols -- The test bootstrap defines Kanboard constants.

/**
 * Boots Kanboard and runs Reporting's focused production regression tests.
 *
 * Related test files: TestAssertions.php and every *Test.php file in this directory.
 */

$kanboardRoot = isset($argv[1]) ? realpath($argv[1]) : false;

if ($kanboardRoot === false || !is_file($kanboardRoot . '/app/common.php')) {
    fwrite(STDERR, "Usage: php tests/run.php <kanboard-root>\n");
    exit(2);
}

$pluginsRoot = getenv('KANBOARD_PLUGINS_DIR') ?: $kanboardRoot . '/plugins';
$databaseFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'reporting-' . bin2hex(random_bytes(8)) . '.sqlite';
$pluginRoot = realpath(dirname(__DIR__));

define('PLUGINS_DIR', $pluginsRoot);
define('DB_FILENAME', $databaseFile);

try {
    require $kanboardRoot . '/app/common.php';
    require __DIR__ . '/TestAssertions.php';

    $pluginName = 'Reporting';
    $loadedPlugins = $container['pluginLoader']->getPlugins();
    $incompatiblePlugins = $container['pluginLoader']->getIncompatiblePlugins();

    reportingAssertFalse(
        isset($incompatiblePlugins[$pluginName]),
        'Reporting must be compatible with the tested Kanboard release.'
    );
    reportingAssertTrue(isset($loadedPlugins[$pluginName]), 'Reporting must load before regression checks run.');

    $testFiles = glob(__DIR__ . '/*Test.php');
    sort($testFiles);

    foreach ($testFiles as $testFile) {
        require $testFile;
    }

    echo 'Reporting regression checks passed on Kanboard ' . APP_VERSION . ' with PHP ' . PHP_VERSION . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL);
    exit(1);
} finally {
    if (isset($container) && isset($container['db'])) {
        $container['db']->closeConnection();
    }

    foreach (array($databaseFile, $databaseFile . '-shm', $databaseFile . '-wal') as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
