<?php

/**
 * Renders the printable project budget report and supporting chart.
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
?>
<section class="reporting-page reporting-report" aria-labelledby="reporting-report-heading">
    <header class="reporting-header">
        <div>
            <h1 id="reporting-report-heading"><?= t('Budget report') ?></h1>
            <p class="reporting-intro"><?= t('A printable summary of your current project budgets and latest usage notes.') ?></p>
        </div>
        <button type="button" class="btn reporting-print-button" data-reporting-print>
            <i class="fa fa-print" aria-hidden="true"></i>
            <?= t('Print report') ?>
        </button>
    </header>

    <?php if ($summary === array()): ?>
        <div class="reporting-empty" role="status">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
            <p><?= t('No budget data is available to report.') ?></p>
        </div>
    <?php else: ?>
        <div class="reporting-table-wrap">
            <table class="reporting-table">
                <thead>
                    <tr>
                        <th scope="col"><?= t('Project') ?></th>
                        <th scope="col"><?= t('Budget') ?></th>
                        <th scope="col"><?= t('Used') ?></th>
                        <th scope="col"><?= t('Remaining') ?></th>
                        <th scope="col"><?= t('Latest usage date') ?></th>
                        <th scope="col"><?= t('Latest usage comment') ?></th>
                        <th scope="col" class="reporting-actions-heading"><?= t('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $row): ?>
                        <?php $latest = $row['latest_usage']; ?>
                        <tr data-reporting-row="<?= (int) $row['project_id'] ?>">
                            <th scope="row" data-label="<?= t('Project') ?>">
                                <?= $this->text->e($row['project_name']) ?>
                            </th>
                            <td data-label="<?= t('Budget') ?>">
                                <?= number_format((float) $row['budget'], 2, '.', ',') ?>
                            </td>
                            <td data-label="<?= t('Used') ?>">
                                <?= number_format((float) $row['used_total'], 2, '.', ',') ?>
                                <small><?= number_format((float) $row['used_percentage'], 1) ?>%</small>
                            </td>
                            <td data-label="<?= t('Remaining') ?>">
                                <?= number_format((float) $row['remaining_total'], 2, '.', ',') ?>
                                <small><?= number_format((float) $row['remaining_percentage'], 1) ?>%</small>
                            </td>
                            <td data-label="<?= t('Latest usage date') ?>">
                                <?= $latest === array() ? '&mdash;' : $this->text->e($latest['date']) ?>
                            </td>
                            <td data-label="<?= t('Latest usage comment') ?>" class="reporting-comment">
                                <?= $latest === array() ? '&mdash;' : nl2br($this->text->e($latest['comment'])) ?>
                            </td>
                            <td data-label="<?= t('Actions') ?>" class="reporting-actions">
                                <button
                                    type="button"
                                    class="reporting-icon-button"
                                    data-reporting-hide="<?= (int) $row['project_id'] ?>"
                                    title="<?= t('Hide this project from the current printout') ?>"
                                    aria-label="<?= t('Hide this project from the current printout') ?>"
                                >
                                    <i class="fa fa-minus-square-o" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <section class="reporting-chart-panel" aria-labelledby="reporting-summary-chart-heading">
            <h2 id="reporting-summary-chart-heading"><?= t('Budget compared with usage') ?></h2>
            <div
                class="reporting-chart"
                data-reporting-chart
                data-columns="<?= $this->text->e(json_encode($chartColumns)) ?>"
                aria-hidden="true"
            ></div>
        </section>
    <?php endif ?>
</section>
