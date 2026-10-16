<?php

/**
 * Provides assertion helpers for Reporting's focused regression tests.
 *
 * Related test runner: tests/run.php.
 */

function reportingAssertTrue($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function reportingAssertFalse($condition, $message)
{
    reportingAssertTrue(!$condition, $message);
}

function reportingAssertSame($expected, $actual, $message)
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . '.'
        );
    }
}

function reportingAssertContains($needle, $haystack, $message)
{
    reportingAssertTrue(strpos($haystack, $needle) !== false, $message);
}

function reportingAssertNotContains($needle, $haystack, $message)
{
    reportingAssertFalse(strpos($haystack, $needle) !== false, $message);
}
