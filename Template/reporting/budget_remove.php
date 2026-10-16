<?php

/**
 * Confirms removal of a budget and its related usage entries.
 */

?>
<div class="page-header">
    <h2><?= t('Remove budget') ?></h2>
</div>

<div class="alert alert-error">
    <?= t('Removing this budget also permanently removes all of its usage entries. Continue?') ?>
</div>

<form method="post" action="<?= $this->url->href('ReportingController', 'DeleteBudget', array('plugin' => 'Reporting')) ?>">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('id', $budget) ?>
    <?= $this->modal->submitButtons(array('submitLabel' => t('Remove budget'), 'color' => 'red')) ?>
</form>
