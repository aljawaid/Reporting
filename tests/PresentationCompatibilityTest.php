<?php

/**
 * Verifies responsive, theme-aware and escaped Reporting presentation contracts.
 *
 * Related production files: assets/css/style.css, assets/js/reporting.js and Reporting templates.
 */

$stylesheet = file_get_contents($pluginRoot . '/assets/css/style.css');
$javascript = file_get_contents($pluginRoot . '/assets/js/reporting.js');
$budgetTemplate = file_get_contents($pluginRoot . '/Template/reporting/budget.php');
$usageTemplate = file_get_contents($pluginRoot . '/Template/reporting/used.php');
$reportTemplate = file_get_contents($pluginRoot . '/Template/reporting/report.php');

reportingAssertContains('light-dark(', $stylesheet, 'Reporting must provide paired light and dark colours.');
reportingAssertContains('--reporting-transition', $stylesheet, 'Interactive controls must retain smooth transitions.');
reportingAssertContains('@media screen and (max-width: 768px)', $stylesheet, 'Tables must adapt to mobile widths.');
reportingAssertContains('min-height: 44px', $stylesheet, 'Mobile primary actions must provide touch-sized targets.');
reportingAssertContains('@media (prefers-reduced-motion: reduce)', $stylesheet, 'Reduced-motion preferences must be respected.');
reportingAssertContains('@media (forced-colors: active)', $stylesheet, 'Forced-colour mode must retain visible boundaries.');
reportingAssertContains('@media print', $stylesheet, 'The report must have a focused print presentation.');
reportingAssertNotContains('fonts.googleapis.com', $stylesheet, 'Reporting must not contact a third-party font service.');

reportingAssertContains("JSON.parse(chartElement.getAttribute('data-columns'))", $javascript, 'Chart data must be parsed from encoded template data.');
reportingAssertContains("window.print()", $javascript, 'The explicit report print control must invoke browser printing.');
reportingAssertNotContains("'/kanboard/", $javascript, 'JavaScript must not assume one installation base path.');

foreach (array($budgetTemplate, $usageTemplate, $reportTemplate) as $templateSource) {
    reportingAssertContains('$this->text->e(', $templateSource, 'User-controlled template output must be escaped.');
}

reportingAssertContains('json_encode($chartColumns)', $budgetTemplate, 'Budget chart labels must use JSON serialisation.');
reportingAssertContains('json_encode($chartColumns)', $reportTemplate, 'Report chart labels must use JSON serialisation.');
reportingAssertNotContains('<script>', $budgetTemplate, 'Budget templates must not use inline scripts.');
reportingAssertNotContains('<script>', $usageTemplate, 'Usage templates must not use inline scripts.');
reportingAssertNotContains('<script>', $reportTemplate, 'Report templates must not use inline scripts.');
