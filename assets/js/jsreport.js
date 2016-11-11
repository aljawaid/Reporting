/* JS for reporting */

  // Hide note in reporting
  $(function() {
    $( "button" + "#singleReportHide" ).click(function() {
      var id = $(this).attr('data-id');
      $( "#reporttrP" + id ).addClass( "hideMe", 10 );
    });
  });
