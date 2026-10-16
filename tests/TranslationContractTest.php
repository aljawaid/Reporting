<?php

/**
 * Verifies that Reporting's production and starter translation catalogues remain aligned.
 *
 * Related production files: Locale/en_GB/translations.php and
 * Locale/translations-starter-template.php.
 */

$english = require $pluginRoot . '/Locale/en_GB/translations.php';
$starter = require $pluginRoot . '/Locale/translations-starter-template.php';

reportingAssertSame(
    array_keys($english),
    array_keys($starter),
    'The starter catalogue must contain the complete production key set in the same order.'
);

foreach ($english as $key => $translation) {
    reportingAssertTrue($translation !== '', 'The British English translation must not be empty for: ' . $key);
}

$translationKeys = array();
$sourcePaths = array_merge(
    array($pluginRoot . '/Plugin.php'),
    glob($pluginRoot . '/Controller/*.php'),
    glob($pluginRoot . '/Model/*.php'),
    glob($pluginRoot . '/Template/dashboard/*.php'),
    glob($pluginRoot . '/Template/reporting/*.php')
);

foreach ($sourcePaths as $sourcePath) {
    $source = file_get_contents($sourcePath);
    preg_match_all("/\\bt\\('((?:\\\\'|[^'])*)'\\)/", $source, $matches);

    foreach ($matches[1] as $key) {
        $translationKeys[str_replace("\\'", "'", $key)] = true;
    }
}

$missingKeys = array_diff(array_keys($translationKeys), array_keys($english));
sort($missingKeys);
reportingAssertSame(array(), $missingKeys, 'Every literal translation key must exist in the British English catalogue.');
