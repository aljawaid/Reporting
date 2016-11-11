<li>
  <?= $this->url->link(t('Budget view'), 'ReportingController', 'ReportingBudget', array('plugin' => 'reporting')) ?>
</li>
<li>
  <?= $this->url->link(t('Budget used'), 'ReportingController', 'ReportingUsed', array('plugin' => 'reporting')) ?>
</li>
<li>
  <?= $this->url->link(t('Budget report'), 'ReportingController', 'ReportingReport', array('plugin' => 'reporting')) ?>
</li>
