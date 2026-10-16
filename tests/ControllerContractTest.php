<?php

/**
 * Verifies the authenticated, request and ownership boundaries on Reporting actions.
 *
 * Related production files: Plugin.php and Controller/ReportingController.php.
 */

$pluginSource = file_get_contents($pluginRoot . '/Plugin.php');
$controllerSource = file_get_contents($pluginRoot . '/Controller/ReportingController.php');

reportingAssertContains(
    "applicationAccessMap->add('ReportingController', '*', Role::APP_USER)",
    $pluginSource,
    'Every Reporting action must require an authenticated application user.'
);
reportingAssertContains('isPost()', $controllerSource, 'Every write boundary must require POST.');
reportingAssertContains('checkCSRFForm()', $controllerSource, 'Every write boundary must validate a CSRF form token.');
reportingAssertContains('getExpectedValues(', $controllerSource, 'Writes must use exact field allowlists.');
reportingAssertContains('assertProjectAccess(', $controllerSource, 'Writes must verify current project access.');
reportingAssertContains(
    'getBudgetById($id, $this->userSession->getId())',
    $controllerSource,
    'Budget ownership must be session-scoped.'
);
reportingAssertContains(
    'getUsageById($id, $this->userSession->getId())',
    $controllerSource,
    'Usage ownership must be session-scoped.'
);
reportingAssertNotContains('getStringParam(', $controllerSource, 'Writes must not read mutable data from query-string parameters.');
reportingAssertNotContains("'/kanboard/", $controllerSource, 'Controller redirects must not assume one installation base path.');
