<style>

.hideMe {
  display: none;
}

</style>



<?= $this->asset->css('plugins/Reporting/assets/css/style.css') ?>

<?= $this->asset->js('plugins/Reporting/assets/js/jsbudget.js') ?>

<div class="page-header">
  <p class="page-headerName"><?= t('Budget overview') ?></p>
  <br>
  <br>
  <p><button class="addBudget"><i class="fa fa-plus" aria-hidden="true"></i> Create new budget line</button></p>
  <br>
  <br>

  <table class="table-fixed table-small tableData">
    <tr>
      <th class="column-5 col1B"><?= t('Edit') ?></th>
      <th class="column-10 col2B"><?= t('Project') ?></th>
      <th class="column-10 col3B"><?= t('Date') ?></th>
      <th class="column-10 col4B"><?= t('Budget') ?></th>
      <th class="column-10 col5B"><?= t('Used') ?></th>
      <th class="column-10 col5B"><?= t('Remaining') ?></th>
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

    print '<tr id="';
    print $pa['project_id'];
    print '" class="topRow ';
    if ($oldProject_id != $pa['project_id']) {
      print 'newProjectRow';
    }
    print '">';
    ?>
      <td>
        <button class="editBudget" data-id="<?php print $pa['id']; ?>" data-project="<?php print $pa['project_id']; ?>" data-date="<?php print $pa['date']; ?>" data-budget="<?php print $pa['budget']; ?>" data-comment="<?php print $pa['comment']; ?>"><i class="fa fa-pencil" aria-hidden="true"></i></button>
        <button class="deleteBudget" data-id="<?php print $pa['id']; ?>" data-project="<?php print $pa['project_id']; ?>" data-date="<?php print $pa['date']; ?>" data-budget="<?php print $pa['budget']; ?>" data-comment="<?php print $pa['comment']; ?>"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
      </td>
      <td>
        <?php print $pa['project_name']; ?>
      </td>
      <td>
        <?php print $pa['date']; ?>
      </td>
      <td>
        <?php print number_format($pa['budget'], 0, ',', '.'); ?>
      </td>
      <td style="padding-right: 20px;">
        <div style='float: left; text-align: left'><?php print number_format($sumUsed, 0, ',', '.'); ?></div>
        <div style='float: right; text-align: right; vertical-align: top; font-style: italic; font-size: 11px;'>(<?php print number_format((float)(($sumUsed / $pa['budget']) * 100), 2, '.', ''); ?>%)</div>
      </td>
      <td style="padding-right: 20px">
        <div style='float: left; text-align: left'><?php print number_format(($pa['budget'] - $sumUsed), 0, ',', '.'); ?></div>
        <div style='float: right; text-align: right; vertical-align: top; font-style: italic; font-size: 11px;'> (<?php print number_format((float)((($pa['budget'] - $sumUsed) / $pa['budget']) * 100), 2, '.', ''); ?>%)</div>
      </td>
      <td style="margin-left: 15px;">
        <?php
        print '<p style="white-space: pre-line;">';
        $comment = str_ireplace("<br >", "\r\n", $pa['comment']);
        print $comment;
        print '</p>';
        ?>
      </td>
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
  <div>
    <div id="chartArea"></div>
  </div>
  <div>
    <div id="chart"></div>
  </div>
  <div>
    <div id="chartTimeline"></div>
  </div>
</div>





<div class="hideMe" id="dialogAddBudget" title="New budget line">
  <?php
  foreach($projectAccess as $projectAccess) {
    $listCat .= '<option value="';
    $listCat .= $projectAccess['project_id'];
    $listCat .= '">';
    $listCat .= $projectAccess['project_name'];
    $listCat .= '</option>';
  }
  ?>

  <p>
  <label for="cat">Category</label>
  <select name="cat" id="cat">
  <?php print $listCat; ?>
  </select>
  <label for="date">Date</label>
  <input type="text" id="datepickerBNew">
  <label for="budgetNew">Budget</label>
  <input type=number name="budgetNew" id="budgetNew" value="" class="text ui-widget-content ui-corner-all">
  <label for="used">Comment</label>
  <input type="text" name="comment" id="commentNew" value="" class="text ui-widget-content ui-corner-all"> 
  </p>
</div>

<div class="hideMe" id="dialogEditBudget" title="Edit budget line">
  <label for="date">Date</label>
  <input type="text" id="datepickerB">
  <label for="budget">Budget</label>
  <input type=number name="budget" id="budget" value="" class="text ui-widget-content ui-corner-all">
  <label for="comment">Comment</label>
  <input type="text" name="comment" id="comment" value="" class="text ui-widget-content ui-corner-all"> 
  </p>
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

/*
  // Timeline
  var chart3 = c3.generate({
    bindto: '#chartTimeline',
    data: {
      x : 'x',
        columns: [
          <?php echo $dateSpline; ?>
          <?php echo $sum; ?>
        ],
      type: 'area'
    },
    axis: {
      x: {
         type: 'timeseries',
            tick: {
                format: '%Y-%m-%d'
            }
      }
    },

  });
*/

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
