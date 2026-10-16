<?php

/**
 * Renders the current user's project budget overview.
 */

$chartColumns = array(
    array_merge(array('x'), array_column($summary, 'project_name')),
    array_merge(array(t('Budget')), array_map(function (array $row) {
        return (float) $row['budget'];
    }, $summary)),
    array_merge(array(t('Used')), array_map(function (array $row) {
        return (float) $row['used_total'];
    }, $summary)),
);
$hasAvailableProject = count($projects) > count($budgets);
?>
<section class="reporting-page" aria-labelledby="reporting-budget-heading">
    <header class="reporting-header">
        <div>
            <h1 id="reporting-budget-heading"><?= t('Budget overview') ?></h1>
            <p class="reporting-intro">
                <?= t('Set one personal budget for each project you can access.') ?>
                <?= $this->app->tooltipMarkdown(t('Reporting data is personal to your account and is not shared with other project members.')) ?>
            </p>
        </div>
        <?php if ($hasAvailableProject): ?>
            <?= $this->modal->mediumButton(
                'plus',
                t('Create budget'),
                'ReportingController',
                'ShowAddBudget',
                array('plugin' => 'Reporting')
            ) ?>
        <?php endif ?>
    </header>

    <?php if ($summary === array()): ?>
        <div class="reporting-empty" role="status">
            <i class="fa fa-line-chart" aria-hidden="true"></i>
            <p><?= t('No budgets have been created yet.') ?></p>
            <?php if (!$hasAvailableProject): ?>
                <p><?= t('You need access to an active project before creating a budget.') ?></p>
            <?php endif ?>
        </div>
    <?php else: ?>
        <div class="reporting-table-wrap">
            <table class="reporting-table">
                <thead>
                    <tr>
                        <th scope="col"><?= t('Project') ?></th>
                        <th scope="col"><?= t('Date') ?></th>
                        <th scope="col"><?= t('Budget') ?></th>
                        <th scope="col"><?= t('Used') ?></th>
                        <th scope="col"><?= t('Remaining') ?></th>
                        <th scope="col"><?= t('Comment') ?></th>
                        <th scope="col" class="reporting-actions-heading"><?= t('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                        <tr>
                            <th scope="row" data-label="<?= t('Project') ?>">
                                <?= $this->text->e($row['project_name']) ?>
                            </th>
                            <td data-label="<?= t('Date') ?>"><?= $this->text->e($row['date']) ?></td>
                            <td data-label="<?= t('Budget') ?>">
                                <?= number_format((float) $row['budget'], 2, '.', ',') ?>
                            </td>
                            <td data-label="<?= t('Used') ?>">
                                <span><?= number_format((float) $row['used_total'], 2, '.', ',') ?></span>
                                <small><?= number_format((float) $row['used_percentage'], 1) ?>%</small>
                            </td>
                            <td data-label="<?= t('Remaining') ?>">
                                <span><?= number_format((float) $row['remaining_total'], 2, '.', ',') ?></span>
                                <small><?= number_format((float) $row['remaining_percentage'], 1) ?>%</small>
                            </td>
                            <td data-label="<?= t('Comment') ?>" class="reporting-comment">
                                <?= nl2br($this->text->e($row['comment'])) ?>
                            </td>
                            <td data-label="<?= t('Actions') ?>" class="reporting-actions">
                                <?= $this->modal->mediumIcon(
                                    'pencil',
                                    t('Edit budget'),
                                    'ReportingController',
                                    'ShowEditBudget',
                                    array('plugin' => 'Reporting', 'id' => $row['id'])
                                ) ?>
                                <?= $this->modal->mediumIcon(
                                    'trash',
                                    t('Remove budget'),
                                    'ReportingController',
                                    'ConfirmDeleteBudget',
                                    array('plugin' => 'Reporting', 'id' => $row['id'])
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <section class="reporting-chart-panel" aria-labelledby="reporting-budget-chart-heading">
            <h2 id="reporting-budget-chart-heading"><?= t('Budget compared with usage') ?></h2>
            <div
                class="reporting-chart"
                data-reporting-chart
                data-columns="<?= $this->text->e(json_encode($chartColumns)) ?>"
                aria-hidden="true"
            ></div>
        </section>
    <?php endif ?>
</section>
