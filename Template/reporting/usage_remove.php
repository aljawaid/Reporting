<?php

/**
 * Confirms removal of one usage entry.
 */

?>
<div class="page-header">
    <h2><?= t('Remove usage entry') ?></h2>
</div>

<div class="alert alert-error">
    <?= t('This usage entry will be permanently removed. Continue?') ?>
</div>

<form method="post" action="<?= $this->url->href('ReportingController', 'DeleteUsed', array('plugin' => 'Reporting')) ?>">
    <?= $this->form->csrf() ?>
    <?= $this->form->hidden('id', $usage) ?>
    <?= $this->modal->submitButtons(array('submitLabel' => t('Remove usage entry'), 'color' => 'red')) ?>
</form>
