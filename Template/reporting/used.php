<style>

.hideMe {
  display: none;
}

.headerProjectName {
  line-height: 1.9em;
}

</style>


<?= $this->asset->css('plugins/Reporting/assets/css/style.css') ?>
<?= $this->asset->js('plugins/Reporting/assets/js/jsused.js') ?>

<div class="page-header">
  <p class="page-headerName"><?= t('Used budget') ?></p>
  <br>
  <?php
  // Assign project_id as key for budget
  foreach ($budget as $oo) {
    $budgetForPrint[$oo['project_id']] = $oo['budget'];
  }

  // Variable defining if new project is arriving
  $oldProject_id = "1";

  // Run foreach on budget to ensure all projects with budget are shown
  foreach ($budget as $bn) {
  print '<hr>';
  print '<p class="headerProjectName">' . $bn['project_name'] . '</p>';

  // Loop through data from reportingUsed
  foreach ($used as $pa){

  // Make sure used array on chooses the right project_id from budget
  if ($bn['project_id'] == $pa['project_id']) {

  // Check 'if' it's a new project
  if ($oldProject_id == "1"){
    // Calculate sum of used
    $sumUsed = "0";
    foreach ($used as $po) {
      if ($po['project_id'] == $pa['project_id']) {
        $sumUsed = $sumUsed + $po['used'];
      }
    }

    // Percentages
    $usedPercentage = ($sumUsed / $budgetForPrint[$pa['project_id']]) * 100;
    $usedPercentage = number_format((float)$usedPercentage, 2, '.', '');
    $sumRemain = ($budgetForPrint[$pa['project_id']] - $sumUsed);
    $remainPercentage = ($budgetForPrint[$pa['project_id']] - $sumUsed) / $budgetForPrint[$pa['project_id']] * 100;
    $remainPercentage = number_format((float)$remainPercentage, 2, '.', '');

    // Define budget variables
    $budgetData = $budgetForPrint[$pa['project_id']];
    $budgetData = $budgetData - $pa['used'];

  ?>
  <p class="infoNoData">Budget: <?php print $budgetForPrint[$pa['project_id']]; ?> - Used: <?php print $sumUsed; ?> (<?php print $usedPercentage; ?>%) - Remain: <?php print $sumRemain; ?> (<?php print $remainPercentage; ?>%)</p><br>
  <div>
    <div style="display: none" id="chartBar<?php print $pa['project_id']; ?>"></div>
    <div style="display: none; margin-left: 40px" id="chartUsed<?php print $pa['project_id']; ?>"></div>
    <div style="display: none; margin-left: 40px" id="chartRemain<?php print $pa['project_id']; ?>"></div>
  </div>
<script>
var chart = c3.generate({
    bindto: '#chartUsed<?php print $pa['project_id']; ?>',
    data: {
        columns: [
            ['Used', <?php print $usedPercentage; ?>]
        ],
        type: 'gauge',
        
    },
    color: {
        pattern: ['#60B044', '#F6C600', '#F97600', '#FF0000'], // the three color levels for the percentage values.
        threshold: {
            values: [30, 60, 90, 100]
        }
    },
    padding: {
        bottom: 36,
   },

    size: {
        height: 160,
        width: 160
    }

});

var chart = c3.generate({
<?php $chartBudget = $budgetForPrint[$pa['project_id']]; ?>

    bindto: '#chartRemain<?php print $pa['project_id']; ?>',
    data: {
        columns: [
            ['Used', 20000]//<?php print $sumUsed; ?>]
        ],
        type: 'gauge',
        
    },
    color: {
        pattern: ['#60B044', '#BAB843', '#F6C600', '#F97600', '#FF0000'], // the three color levels for the percentage values.
        threshold: {
            values: [<?php print (($chartBudget/100)*45) . ', ' . (($chartBudget/100)*60) . ', ' . (($chartBudget/100)*80) . ', ' . (($chartBudget/100)*90) . ', ' . $chartBudget; ?>]
        }
    },
    gauge: {
     min: 0,
     max: <?php print $budgetForPrint[$pa['project_id']]; ?>,
    },
    size: {
        height: 160,
        width: 160
    },
    padding: {
        bottom: 36
   }

});
var chart = c3.generate({
    bindto: '#chartBar<?php print $pa['project_id']; ?>',
    data: {
        columns: [
            ['Budget', <?php print $budgetForPrint[$pa['project_id']]; ?>],
            ['Used', <?php print $sumUsed; ?>]
        ],
        type: 'bar',
        labels: true
    },
    bar: {
        width: {
            ratio: 0.9
        }
    },
    size: {
        width: 130,
        height: 160
    },
    legend: {
        hide: true
    }
});
</script>
  <button class="addUsed" data-id="<?php print $pa['id']; ?>" data-project="<?php print $pa['project_id']; ?>" data-name="<?php print $pa['project_name']; ?>"><i class="fa fa-plus" aria-hidden="true"></i> Add used time</button>
  <br>
  <?php
  print '<table class="tableData" id="';
  print $pa['project_id'];
  print '">';
  ?>
  <thead>
    <tr>
      <th class="column-10 col1U"><?= t('Delete') ?></th>
      <th class="column-10 col2U"><?= t('Date') ?></th>
      <th class="column-10 col3U"><?= t('Used') ?></th>
      <th class="column-10 col4U"><?= t('Remaining') ?></th>
      <th class="column-20 col5U"><?= t('Comment') ?></th>
    </tr>
  </thead>
  <tbody id="tbodyUsed">
  <?php
  // End 'if' it's a new project
  } else {
    $budgetData = $budgetData - $pa['used'];
  }

  ?>
    <tr>
      <td>
         <button class="deleteUsed" data-id="<?php print $pa['id']; ?>"><i class="fa fa-times" aria-hidden="true"></i></button>
      </td>
      <td>
        <?php print $pa['date']; ?>
      </td>
      <td>
        <?php print $pa['used']; ?>
      </td>
      <td>
        <?php print $budgetData; ?>
      </td>
      <td>
        <?php
        print '<p style="white-space: pre-line;">';
        $comment = str_ireplace("<br >", "\r\n", $pa['comment']);
        print $comment;
        print '</p>';
        ?>
      </td>
    </tr>
  <?php
  // Make sure, that it keeps adding to existing table
  $oldProject_id = "0";
  // End 'if' right project_id
  }
  // End foreach for $used array
  }
  // End table and get ready new project
  print '</tbody>';
  print '</table>';
?>

<?php
  if ($oldProject_id == "0") {
?>
<div style="display: inline-block; margin-left: 40px; vertical-align:top;" id="chartHead<?php print $bn['project_id']; ?>"></div>
<script>
var chart = c3.generate({
<?php $chartBudget = $budgetForPrint[$bn['project_id']]; ?>

    bindto: '#chartHead<?php print $bn['project_id']; ?>',
    data: {
        columns: [
            ['Used', <?php print $sumUsed; ?>]
        ],
        type: 'gauge',
        
    },
    color: {
        pattern: ['#60B044', '#BAB843', '#F6C600', '#F97600', '#FF0000'], // the three color levels for the percentage values.
        threshold: {
            values: [<?php print (($chartBudget/100)*45) . ', ' . (($chartBudget/100)*60) . ', ' . (($chartBudget/100)*80) . ', ' . (($chartBudget/100)*90) . ', ' . $chartBudget; ?>]
        }
    },
    gauge: {
     min: 0,
     max: <?php print $budgetForPrint[$bn['project_id']]; ?>,
    },
    size: {
        height: 160,
        width: 160
    },
    padding: {
        bottom: 36
   }

});
</script>
<?php
  }

  unset($sumUsed);
  if ($oldProject_id == "1") {
    print '<p class="infoNoData">Budget: ' . $bn['budget'] . ' - Used: 0</p>';
    print '<br>';
?>  <button class="addUsed" data-id="<?php print $pa['id']; ?>" data-project="<?php print $bn['project_id']; ?>" data-name="<?php print $bn['project_name']; ?>"><i class="fa fa-plus" aria-hidden="true"></i> Add used time</button>
<?php  }

  $oldProject_id = "1";

  // End loop
  }
  ?>



<style>

input[type=text] {
    display: inline-block;
    width: 80%;
    font-size: 16px;
    border: none;
    background-color: rgba(255, 255, 255, 0);
    padding: 0px 0px;
    margin-top: 0px;
    height: auto;
    padding-left: 10px;
}

input[type=text]:focus {
    color: #000;
    border-color: rgba(82,168,236,.8);
    outline: 0;
    border-bottom: 1px solid gray;
    box-shadow: none;
}

textarea {
    height: 70px;
    width: 280px;
    padding: 5px;
    color: #6d6c6c;
    margin-top: 5px;
}
</style>

</div>
<div class="hideMe" id="dialogAddUsed" title="Used budget">
  <p>
  <h3 class="modalProjectName"></h3>
  <label for="date">Date</label>
  <input type="text" id="datepickerUsed">
  <label for="used">Used</label>
  <input type=number name="used" id="used" value="" class="text ui-widget-content ui-corner-all">
  <label for="used">Comment</label>
  <textarea type="text" name="comment" id="comment" value="" class="text ui-widget-content ui-corner-all"></textarea>
  </p>
</div>

