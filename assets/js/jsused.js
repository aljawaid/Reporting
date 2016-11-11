/* JS for used */

  // Button to activate modal for new budget line
  $(function() {
    $( "button" + ".addUsed" ).click(function() {
      var project_id = $(this).attr('data-project');
      var project_name = $(this).attr('data-name');
      $(".modalProjectName").html("Used on: " + project_name);
      modalAddBudget(project_id, project_name);
    });
  });

  // Modal for adding new budget line
  function modalAddBudget(project_id, project_name) {
    $( "#dialogAddUsed" ).dialog({
      resizable: false,
      height: "auto",
      modal: true,
      buttons: {
        "Add used": function() {
          $( this ).dialog( "close" );
          addUsed (project_id);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  };

  // SQL POST add used
  function addUsed (project_id)
  {
    var used = $("#used").val();
    var date = $("#datepickerUsed").val();
    var comment = $("#comment").val();
    if (comment) {
      var comment = $("#comment").val().replace(/\n/g, '<br >');
    }
    if (!comment) {
      var comment = "";
    }

    $.ajax({
      type: "POST",
      url: '/kanboard/?controller=ReportingController&action=AddUsed&plugin=reporting' + '&project_id=' + project_id + '&used=' + used + '&comment=' + comment + '&date=' + date,
      success: function(response) {
      },
      error: function(xhr,textStatus,e) {
      }
    });
    return false;
  }

  // Button to delete used
  $(function() {
    $( "button" + ".deleteUsed" ).click(function() {
      var id = $(this).attr('data-id');
      deleteUsed(id);
    });
  });

  // SQL POST add used
  function deleteUsed (id)
  {
    $.ajax({
      type: "POST",
      url: '/kanboard/?controller=ReportingController&action=DeleteUsed&plugin=reporting' + '&id=' + id,
      success: function(response) {
      },
      error: function(xhr,textStatus,e) {
      }
    });
    return false;
  }

  // Reverse table with values - needed for continous calculation (maybe not needed anyways?)
  $(function(){
    $("tbody").each(function(elem,index){
      var arr = $.makeArray($("tr",this).detach());
      arr.reverse();
        $(this).append(arr);
    });
  });

  // Activate jquery datepicker - notice name is changes
  $( function() {
    $( "#datepickerUsed" ).datepicker();
    $( "#datepickerUsed" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
  } );
