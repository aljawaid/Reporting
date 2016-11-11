/* JS for budget */
  
  // Button for new budget line
  $(function() {
    $( "button" + ".addBudget" ).click(function() {
      modalAddBudget();
    });
  });

  // Modal for adding new budget line
  function modalAddBudget() {
    $( "#dialogAddBudget" ).dialog({
      resizable: false,
      height: "auto",
      modal: true,
      buttons: {
        "Set new budget line": function() {
          $( this ).dialog( "close" );
          addBudget();
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  };

  // SQL POST add budget
  function addBudget ()
  {
    var budget = $("#budgetNew").val();
    var comment = $("#commentNew").val();
    var date = $("#datepickerBNew").val();
    var project_id = $('#cat option:selected').val();
    $.ajax({
      type: "POST",
      url: '/kanboard/?controller=ReportingController&action=AddBudget&plugin=reporting' + '&project_id=' + project_id + '&budget=' + budget + '&comment=' + comment + '&date=' + date,
      success: function(response) {
      },
      error: function(xhr,textStatus,e) {
      }
    });
    return false;
  }

  // Button for editing budget line
  // Notice: All date is collected in button. Give classes to td and get value = future.
  $(function() {
    $( "button" + ".editBudget" ).click(function() {
      var id = $(this).attr('data-id');
      var project_id = $(this).attr('data-project');
      $("#datepickerB").val($(this).attr('data-date'));
      $("#budget").val($(this).attr('data-budget'));
      $("#comment").val($(this).attr('data-comment'));
      modalEditBudget(id, project_id);
    });
  });

  function modalEditBudget(id, project_id) {
    $( "#dialogEditBudget" ).dialog({
      resizable: false,
      height: "auto",
      modal: true,
      buttons: {
        "Set new budget line": function() {
          $( this ).dialog( "close" );
          editBudget(id, project_id);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  };

  // SQL POST edit budget
  function editBudget (id, project_id)
  {
    var budget = $("#budget").val();
    var comment = $("#comment").val();
    var date = $("#datepickerB").val();

    $.ajax({
      type: "POST",
      url: '/kanboard/?controller=ReportingController&action=EditBudget&plugin=reporting' + '&id=' + id + '&project_id=' + project_id + '&budget=' + budget + '&comment=' + comment + '&date=' + date,
      success: function(response) {
      },
      error: function(xhr,textStatus,e) {
      }
    });
    return false;
  }

  // Datepicker for editing
  $( function() {
    $( "#datepickerB" ).datepicker();
    $( "#datepickerB" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
  } );
  // Datepicker for new
  $( function() {
    $( "#datepickerBNew" ).datepicker();
    $( "#datepickerBNew" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
  } );
