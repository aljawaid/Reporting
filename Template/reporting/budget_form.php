<?php

/**
 * Renders the create or edit budget modal form.
 */

$isEdit = $action === 'EditBudget';
?>
<div class="page-header">
    <h2><?= $this->text->e($title) ?></h2>
</div>

<form method="post" action="<?= $this->url->href('ReportingController', $action, array('plugin' => 'Reporting')) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?php if ($isEdit): ?>
        <?= $this->form->hidden('id', $values) ?>
        <p class="form-help">
            <?= t('Project:') ?> <strong><?= $this->text->e($values['project_name']) ?></strong>
        </p>
    <?php else: ?>
        <?= $this->form->label(t('Project'), 'project_id') ?>
        <?= $this->form->select('project_id', $projects, $values, $errors, array('required')) ?>
    <?php endif ?>

    <?= $this->form->label(t('Budget'), 'budget') ?>
    <?= $this->form->number(
        'budget',
        $values,
        $errors,
        array('min="0.01"', 'step="0.01"', 'inputmode="decimal"', 'required')
    ) ?>

    <?= $this->form->label(t('Date'), 'date') ?>
    <?= $this->form->input('date', 'date', $values, $errors, array('required')) ?>

    <?= $this->form->label(t('Comment'), 'comment') ?>
    <?= $this->form->textarea('comment', $values, $errors, array('maxlength="4000"', 'rows="5"')) ?>

    <?= $this->modal->submitButtons(array('submitLabel' => $submitLabel)) ?>
</form>
