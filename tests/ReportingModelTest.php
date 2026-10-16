<?php

/**
 * Verifies Reporting's user-scoped data model, validation and calculations.
 *
 * Related production file: Model/ReportingModel.php.
 */

$reportingModel = $container['reportingModel'];
$projectId = $container['projectModel']->create(array('name' => 'Reporting regression project'));

reportingAssertTrue($projectId > 0, 'The model test project must be created.');
reportingAssertSame(array(), $reportingModel->getProjects(array()), 'Empty project access must return an empty list.');

$projects = $reportingModel->getProjects(array($projectId));
reportingAssertSame(1, count($projects), 'The accessible active project must be returned.');
reportingAssertSame('Reporting regression project', $projects[0]['project_name'], 'The project name must be retained.');

list($validBudget, $budgetValues, $budgetErrors) = $reportingModel->validateBudget(array(
    'project_id' => $projectId,
    'budget' => '1500.5',
    'comment' => 'Annual allocation',
    'date' => '2026-10-16',
));
reportingAssertTrue($validBudget, 'A complete budget payload must validate.');
reportingAssertSame(array(), $budgetErrors, 'A valid budget must not return errors.');
reportingAssertSame('1500.50', $budgetValues['budget'], 'Budget decimals must be normalised to two places.');

list($invalidBudget, , $invalidBudgetErrors) = $reportingModel->validateBudget(array(
    'project_id' => 0,
    'budget' => '0',
    'comment' => str_repeat('x', 4001),
    'date' => '2026-02-30',
));
reportingAssertFalse($invalidBudget, 'Invalid project, amount, date and comment values must be rejected.');
reportingAssertSame(
    array('project_id', 'budget', 'date', 'comment'),
    array_keys($invalidBudgetErrors),
    'Validation errors must remain attached to their form fields.'
);

$firstUserId = 501;
$secondUserId = 502;
reportingAssertTrue($reportingModel->createBudget($firstUserId, $budgetValues), 'The first budget must be created.');
reportingAssertFalse(
    $reportingModel->createBudget($firstUserId, $budgetValues),
    'One user must not create duplicate budgets for one project.'
);
reportingAssertTrue(
    $reportingModel->createBudget($secondUserId, $budgetValues),
    'Different users must be able to budget the same project independently.'
);

list($validUsage, $usageValues) = $reportingModel->validateUsage(array(
    'project_id' => $projectId,
    'used' => '225.25',
    'comment' => '<script>retained as text</script>',
    'date' => '2026-10-16',
));
reportingAssertTrue($validUsage, 'A complete usage payload must validate.');
reportingAssertTrue($reportingModel->createUsage($firstUserId, $usageValues), 'The usage entry must be created.');

$budgets = $reportingModel->getBudgets($firstUserId, array($projectId));
$usage = $reportingModel->getUsage($firstUserId, array($projectId));
$summary = $reportingModel->buildSummary($budgets, $usage);
reportingAssertSame(1, count($summary), 'The summary must contain one budgeted project.');
reportingAssertSame(225.25, $summary[0]['used_total'], 'The summary must total usage accurately.');
reportingAssertSame(1275.25, $summary[0]['remaining_total'], 'The summary must calculate the remaining budget.');
reportingAssertSame(
    '<script>retained as text</script>',
    $summary[0]['latest_usage']['comment'],
    'The model must retain comment text for escaped rendering.'
);

$budgetId = (int) $budgets[0]['id'];
$usageId = (int) $usage[0]['id'];
reportingAssertSame(array(), $reportingModel->getBudgetById($budgetId, $secondUserId), 'Budget lookup must be owner-scoped.');
reportingAssertSame(array(), $reportingModel->getUsageById($usageId, $secondUserId), 'Usage lookup must be owner-scoped.');
reportingAssertFalse(
    $reportingModel->updateBudget($budgetId, $secondUserId, $budgetValues),
    'Another user must not update a budget by guessing its id.'
);
reportingAssertFalse(
    $reportingModel->removeUsage($usageId, $secondUserId),
    'Another user must not remove a usage entry by guessing its id.'
);
reportingAssertTrue(
    $reportingModel->removeBudget($budgetId, $firstUserId),
    'The owner must be able to remove a budget.'
);
reportingAssertSame(
    array(),
    $reportingModel->getUsage($firstUserId, array($projectId)),
    'Removing a budget must remove the same user and project usage entries.'
);
reportingAssertSame(
    1,
    count($reportingModel->getBudgets($secondUserId, array($projectId))),
    'Removing one user budget must not affect another user budget.'
);

$container['projectModel']->remove($projectId);
