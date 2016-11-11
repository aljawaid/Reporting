<style>

.hideMe {
  display: none;
}

</style>


<?= $this->asset->css('plugins/Reporting/assets/css/style.css') ?>
<link rel="stylesheet" href="/kanboard/plugins/Reporting/assets/css/style.css" media="print">

<?= $this->asset->js('plugins/Reporting/assets/js/jsreport.js') ?>

<div class="page-header">
  <p class="page-headerName"><?= t('Reporting') ?></p>
  <br>
  <br>

  <table class="table-fixed table-small tableReport">
    <tr>
      <th class="column-10 col2B"><?= t('Project') ?></th>
      <th class="column-10 col4B"><?= t('Budget') ?></th>
      <th class="column-10 col5B" colspan="2"><?= t('Used') ?></th>
      <th class="column-10 col5B" colspan="2"><?= t('Remaining') ?></th>
      <th class="column-10 col3B"><?= t('Report date') ?></th>
      <th class="column-20 col6B"><?= t('Comment') ?></th>
    </tr>

    <?php

    $chartCategories .= '"x", ';
    $chartBudget .= '"Budget", ';
    $chartUsed .= '"Used", ';


    // Loop through budget
    foreach ($budget as $pa){

    $sumUsed = "0";
    foreach ($used as $ur) {
      if ($ur['project_id'] == $pa['project_id']) {
        $sumUsed = $sumUsed + $ur['used'];
      }
    }

    foreach ($reportingData as $rd) {
      if ($rd['project_id'] == $pa['project_id']) {
        $reportingDate = $rd['date'];
        $reportingComment = $rd['comment'];
      }
    }

    print '<tr id="reporttrP';
    print $pa['project_id'];
    print '" class="topRow ';
    if ($oldProject_id != $pa['project_id']) {
      print 'newProjectRow';
    }
    print '">';
    ?>
      <td class="tdReport">
        <?php print $pa['project_name']; ?>
      </td>
      <td>
        <?php print number_format($pa['budget'], 0, ',', '.'); ?>
      </td>
      <td style="padding-right: 0px;">
        <div style='float: left; text-align: left'><?php print number_format($sumUsed, 0, ',', '.'); ?></div>
      </td>
      <td>
        <div style='text-align: left; vertical-align: top; font-style: italic; font-size: 11px;'>(<?php print number_format((float)(($sumUsed / $pa['budget']) * 100), 2, '.', ''); ?>%)</div>
      </td>
      <td style="padding-right: 0px">
        <div style='float: left; text-align: left'><?php print number_format(($pa['budget'] - $sumUsed), 0, ',', '.'); ?></div>
      </td>
      <td>
        <div style='text-align: left; vertical-align: top; font-style: italic; font-size: 11px;'> (<?php print number_format((float)((($pa['budget'] - $sumUsed) / $pa['budget']) * 100), 2, '.', ''); ?>%)</div>
      </td>
      <td>
        <?php print $reportingDate; ?>
      </td>
      <td style="margin-left: 15px;">
        <?php
        print '<p style="white-space: pre-line;">';
        $comment = str_ireplace("<br >", "\r\n", $reportingComment);
        print $comment;
        print '</p>';
        ?>
      </td>

<?php
    // Delete button viewed in detailed view
    print '<td class="noprint tdReportButton">';
    print '<button id="singleReportHide" class="singleReportHide" data-id="';
    print $pa['project_id'];
    print '"><i class="fa fa-minus-square-o" aria-hidden="true"></i></button>';
    print '</td>';
?>
    </tr>

    <?php

    $oldProjectId = $pa['project_id'];

    // Loading data into chart variables
    $chartCategories .= '"' . $pa['project_name'] . '", ';
    $chartBudget .= $pa['budget'] . ', ';
    $chartUsed .= $sumUsed . ', ';

    }

    // Cleaning up variables
    $chartCategories = substr_replace($chartCategories, "", -2);
    $chartBudget = substr_replace($chartBudget, "", -2);
    $chartUsed = substr_replace($chartUsed, "", -2);
    ?>

  </table>
  <br>
  <br>
  <div>
    <div id="chart"></div>
  </div>
  <div>
    <div id="chartArea"></div>
  </div>
</div>





<script>
  $(function() {
  // Area
  var chart1 = c3.generate({
    bindto: '#chartArea',
    data: {
      x : 'x',
        columns: [
          [<?php echo $chartCategories; ?>],
          [<?php echo $chartBudget; ?>],
          [<?php echo $chartUsed; ?>],
        ],
      type: 'area'
    },
    axis: {
      x: {
        type: 'category' // this needed to load string x value
      }
    },
    grid: {
        y: {
            show: true
        }
    },
    size: {
      height: 250
    },
  });


  // Bar chart
  var chart2 = c3.generate({
    bindto: '#chart',
    data: {
      x : 'x',
        columns: [
          [<?php echo $chartCategories; ?>],
          [<?php echo $chartBudget; ?>],
          [<?php echo $chartUsed; ?>],
        ],
      type: 'bar'
    },
    axis: {
      x: {
        type: 'category' // this needed to load string x value
      }
    },
    grid: {
        y: {
            show: true
        }
    },
    size: {
      height: 250
    },
  });
  });
</script>
