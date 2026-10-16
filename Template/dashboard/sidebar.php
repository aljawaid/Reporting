<?php

/**
 * Adds Reporting navigation to Kanboard's dashboard sidebar.
 */

?>
<li <?= $this->app->checkMenuSelection('ReportingController', 'ReportingBudget') ?>>
    <?= $this->url->icon(
        'money',
        t('Budget overview'),
        'ReportingController',
        'ReportingBudget',
        array('plugin' => 'Reporting')
    ) ?>
</li>
<li <?= $this->app->checkMenuSelection('ReportingController', 'ReportingUsed') ?>>
    <?= $this->url->icon(
        'tasks',
        t('Budget usage'),
        'ReportingController',
        'ReportingUsed',
        array('plugin' => 'Reporting')
    ) ?>
</li>
<li <?= $this->app->checkMenuSelection('ReportingController', 'ReportingReport') ?>>
    <?= $this->url->icon(
        'bar-chart',
        t('Budget report'),
        'ReportingController',
        'ReportingReport',
        array('plugin' => 'Reporting')
    ) ?>
</li>
