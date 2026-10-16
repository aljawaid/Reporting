<?php

/**
 * Renders the create-usage modal form.
 */

?>
<div class="page-header">
    <h2><?= t('Add usage') ?></h2>
</div>

<p class="form-help">
    <?= t('Project:') ?> <strong><?= $this->text->e($project['name']) ?></strong><br>
    <?= t('Budget:') ?> <strong><?= number_format((float) $budget['budget'], 2, '.', ',') ?></strong>
</p>

<form method="post" action="<?= $this->url->href('ReportingController', 'AddUsed', array('plugin' => 'Reporting')) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('project_id', $values) ?>

    <?= $this->form->label(t('Used'), 'used') ?>
    <?= $this->form->number(
        'used',
        $values,
        $errors,
        array('min="0.01"', 'step="0.01"', 'inputmode="decimal"', 'required')
    ) ?>

    <?= $this->form->label(t('Date'), 'date') ?>
    <?= $this->form->input('date', 'date', $values, $errors, array('required')) ?>

    <?= $this->form->label(t('Comment'), 'comment') ?>
    <?= $this->form->textarea('comment', $values, $errors, array('maxlength="4000"', 'rows="5"')) ?>

    <?= $this->modal->submitButtons(array('submitLabel' => t('Add usage'))) ?>
</form>
