/* assets/js/app.js */
$(function(){
  $('.datatable').each(function(){
    $(this).DataTable({
      pageLength: 10
    });
  });
});
