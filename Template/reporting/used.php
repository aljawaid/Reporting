<?php

/**
 * Renders usage entries grouped under the current user's project budgets.
 */

?>
<section class="reporting-page" aria-labelledby="reporting-usage-heading">
    <header class="reporting-header">
        <div>
            <h1 id="reporting-usage-heading"><?= t('Budget usage') ?></h1>
            <p class="reporting-intro">
                <?= t('Record usage against your personal project budgets.') ?>
                <?= $this->app->tooltipMarkdown(t('Removing a budget also removes its usage entries. This is confirmed before deletion.')) ?>
            </p>
        </div>
    </header>

    <?php if ($summary === array()): ?>
        <div class="reporting-empty" role="status">
            <i class="fa fa-tasks" aria-hidden="true"></i>
            <p><?= t('Create a project budget before recording usage.') ?></p>
        </div>
    <?php else: ?>
        <div class="reporting-card-grid">
            <?php foreach ($summary as $row): ?>
                <?php $projectUsage = isset($usageByProject[(int) $row['project_id']])
                    ? $usageByProject[(int) $row['project_id']]
                    : array(); ?>
                <article class="reporting-card">
                    <header class="reporting-card-header">
                        <div>
                            <h2><?= $this->text->e($row['project_name']) ?></h2>
                            <p>
                                <?= t('Budget:') ?>
                                <strong><?= number_format((float) $row['budget'], 2, '.', ',') ?></strong>
                            </p>
                        </div>
                        <?= $this->modal->mediumButton(
                            'plus',
                            t('Add usage'),
                            'ReportingController',
                            'ShowAddUsed',
                            array('plugin' => 'Reporting', 'project_id' => $row['project_id'])
                        ) ?>
                    </header>

                    <div class="reporting-metrics" aria-label="<?= t('Budget totals') ?>">
                        <div>
                            <span><?= t('Used') ?></span>
                            <strong><?= number_format((float) $row['used_total'], 2, '.', ',') ?></strong>
                            <small><?= number_format((float) $row['used_percentage'], 1) ?>%</small>
                        </div>
                        <div>
                            <span><?= t('Remaining') ?></span>
                            <strong><?= number_format((float) $row['remaining_total'], 2, '.', ',') ?></strong>
                            <small><?= number_format((float) $row['remaining_percentage'], 1) ?>%</small>
                        </div>
                    </div>

                    <?php if ($projectUsage === array()): ?>
                        <p class="reporting-card-empty"><?= t('No usage has been recorded for this project.') ?></p>
                    <?php else: ?>
                        <div class="reporting-table-wrap">
                            <table class="reporting-table reporting-table-compact">
                                <thead>
                                    <tr>
                                        <th scope="col"><?= t('Date') ?></th>
                                        <th scope="col"><?= t('Used') ?></th>
                                        <th scope="col"><?= t('Comment') ?></th>
                                        <th scope="col" class="reporting-actions-heading"><?= t('Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projectUsage as $entry): ?>
                                        <tr>
                                            <td data-label="<?= t('Date') ?>"><?= $this->text->e($entry['date']) ?></td>
                                            <td data-label="<?= t('Used') ?>">
                                                <?= number_format((float) $entry['used'], 2, '.', ',') ?>
                                            </td>
                                            <td data-label="<?= t('Comment') ?>" class="reporting-comment">
                                                <?= nl2br($this->text->e($entry['comment'])) ?>
                                            </td>
                                            <td data-label="<?= t('Actions') ?>" class="reporting-actions">
                                                <?= $this->modal->mediumIcon(
                                                    'trash',
                                                    t('Remove usage entry'),
                                                    'ReportingController',
                                                    'ConfirmDeleteUsed',
                                                    array('plugin' => 'Reporting', 'id' => $entry['id'])
                                                ) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif ?>
                </article>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</section>
