"use strict";

if ($.fn.dataTable) {
  $.extend(true, $.fn.dataTable.defaults, {
    pageLength: 100,
    lengthMenu: [[100, 300, 500, 1000], [100, 300, 500, 1000]]
  });
}

$("[data-checkboxes]").each(function () {
  var me = $(this),
    group = me.data('checkboxes'),
    role = me.data('checkbox-role');

  me.change(function () {
    var all = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"])'),
      checked = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"]):checked'),
      dad = $('[data-checkboxes="' + group + '"][data-checkbox-role="dad"]'),
      total = all.length,
      checked_length = checked.length;

    if (role == 'dad') {
      if (me.is(':checked')) {
        all.prop('checked', true);
      } else {
        all.prop('checked', false);
      }
    } else {
      if (checked_length >= total) {
        dad.prop('checked', true);
      } else {
        dad.prop('checked', false);
      }
    }
  });
});

$("#table-1").dataTable({
  pageLength: 100,
  lengthMenu: [[100, 300, 500, 1000], [100, 300, 500, 1000]],
  "columnDefs": [
    { "sortable": false, "targets": [2, 3] }
  ]
});
$("#table-2").dataTable({
  pageLength: 100,
  lengthMenu: [[100, 300, 500, 1000], [100, 300, 500, 1000]],
  "columnDefs": [
    { "sortable": false, "targets": [0, 2, 3] }
  ],
  order: [[1, "asc"]] //column indexes is zero based

});
$('#save-stage').DataTable({
  pageLength: 100,
  lengthMenu: [[100, 300, 500, 1000], [100, 300, 500, 1000]],
  "scrollX": true,
  stateSave: true
});
$('#tableExport').DataTable({
  pageLength: 100,
  lengthMenu: [[100, 300, 500, 1000], [100, 300, 500, 1000]],
  dom: 'Bfrtip',
  buttons: [
    'copy', 'csv', 'excel', 'pdf', 'print'
  ]
});
