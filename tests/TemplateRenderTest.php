<?php

/**
 * Renders empty and populated Reporting templates with warnings promoted to failures.
 *
 * Related production files: Template/reporting/*.php.
 */

$renderErrorHandler = function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
};

$sampleBudget = array(
    'id' => 10,
    'project_id' => 20,
    'user_id' => 30,
    'project_name' => 'Project <sample>',
    'budget' => '1000.00',
    'comment' => '<script>budget</script>',
    'date' => '2026-10-16',
);
$sampleUsage = array(
    'id' => 11,
    'project_id' => 20,
    'user_id' => 30,
    'project_name' => 'Project <sample>',
    'used' => '250.00',
    'comment' => '<script>usage</script>',
    'date' => '2026-10-16',
);
$sampleSummary = $sampleBudget + array(
    'used_total' => 250.0,
    'remaining_total' => 750.0,
    'used_percentage' => 25.0,
    'remaining_percentage' => 75.0,
    'latest_usage' => $sampleUsage,
);

set_error_handler($renderErrorHandler);

try {
    $emptyBudget = $container['template']->render('Reporting:reporting/budget', array(
        'budgets' => array(),
        'projects' => array(),
        'summary' => array(),
    ));
    $emptyUsage = $container['template']->render('Reporting:reporting/used', array(
        'summary' => array(),
        'usageByProject' => array(),
    ));
    $emptyReport = $container['template']->render('Reporting:reporting/report', array(
        'summary' => array(),
    ));

    reportingAssertContains('No budgets have been created yet.', $emptyBudget, 'The empty budget view must render.');
    reportingAssertContains('Create a project budget', $emptyUsage, 'The empty usage view must render.');
    reportingAssertContains('No budget data is available', $emptyReport, 'The empty report view must render.');

    $populatedBudget = $container['template']->render('Reporting:reporting/budget', array(
        'budgets' => array($sampleBudget),
        'projects' => array(array('project_id' => 20, 'project_name' => 'Project <sample>')),
        'summary' => array($sampleSummary),
    ));
    $populatedUsage = $container['template']->render('Reporting:reporting/used', array(
        'summary' => array($sampleSummary),
        'usageByProject' => array(20 => array($sampleUsage)),
    ));
    $populatedReport = $container['template']->render('Reporting:reporting/report', array(
        'summary' => array($sampleSummary),
    ));

    foreach (array($populatedBudget, $populatedUsage, $populatedReport) as $html) {
        reportingAssertNotContains('<script>budget</script>', $html, 'Budget comments must be escaped during rendering.');
        reportingAssertNotContains('<script>usage</script>', $html, 'Usage comments must be escaped during rendering.');
        reportingAssertContains('Project &lt;sample&gt;', $html, 'Project names must be escaped during rendering.');
    }

    $container['template']->render('Reporting:reporting/budget_form', array(
        'action' => 'AddBudget',
        'errors' => array(),
        'projects' => array(20 => 'Project <sample>'),
        'submitLabel' => 'Create budget',
        'title' => 'Create budget',
        'values' => array('date' => '2026-10-16'),
    ));
    $container['template']->render('Reporting:reporting/budget_form', array(
        'action' => 'EditBudget',
        'errors' => array(),
        'projects' => array(),
        'submitLabel' => 'Save budget',
        'title' => 'Edit budget',
        'values' => $sampleBudget,
    ));
    $container['template']->render('Reporting:reporting/budget_remove', array('budget' => $sampleBudget));
    $container['template']->render('Reporting:reporting/usage_form', array(
        'budget' => $sampleBudget,
        'errors' => array(),
        'project' => array('name' => 'Project <sample>'),
        'values' => array('project_id' => 20, 'date' => '2026-10-16'),
    ));
    $container['template']->render('Reporting:reporting/usage_remove', array('usage' => $sampleUsage));
} finally {
    restore_error_handler();
}
