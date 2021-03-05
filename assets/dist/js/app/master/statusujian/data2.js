$(document).ready(function () {

    var table = $('#statusujian').DataTable({
          'dom':
             "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'<'float-md-right ml-2'B>f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
          'ajax': 'http://localhost/ujiancbt/manager/data_status/data',
          
          
          'buttons': [ 
          
                   {
          extend: 'copy',
          exportOptions: { columns: [':visible'], rows: [':visible'] }
          //copies currently displayed columns and rows
          },
          'colvis',
          'csv', {
             'text': '<i class="fa fa-id-badge fa-fw" aria-hidden="true"></i>',
             'action': function (e, dt, node) {
 
                $(dt.table().node()).toggleClass('cards');
                $('.fa', node).toggleClass(['fa-table', 'fa-id-badge']);
 
                dt.draw('page');
             },
             'className': 'btn-sm',
             'attr': {
                'title': 'Change views',
             }
          }],
          'select': 'single',
          'columns': [
             {
                'orderable': false,
                'data': null,
                'className': 'text-center',
                'render': function(data, type, full, meta){
                   if (type === 'display'){
                      data = '<i class="fa fa-user fa-fw"></i>';
                      data = '<img src="https://i.pravatar.cc/150?name=' + encodeURIComponent(full['name']) + '" class="avatar border rounded-circle">';
                   }
                   
                   return data;
                }
             },
             {
                'data': 'modul_id'
             },
             {
                'data': 'modul_id'
             },
             {
                'data': 'modul_id',
                'class': 'text-right'
             },
             {
                'data': 'modul_id',
                'class': 'text-right'
             },
             {
                'data': 'modul_id'
             },
             {
                'data': 'modul_id'
             }
          ],
          'drawCallback': function (settings) {
             var api = this.api();
             var $table = $(api.table().node());
             
             if ($table.hasClass('cards')) {
 
                // Create an array of labels containing all table headers
                var labels = [];
                $('thead th', $table).each(function () {
                   labels.push($(this).text());
                });
 
                // Add data-label attribute to each cell
                $('tbody tr', $table).each(function () {
                   $(this).find('td').each(function (column) {
                      $(this).attr('data-label', labels[column]);
                   });
                });
 
                var max = 0;
                $('tbody tr', $table).each(function () {
                   max = Math.max($(this).height(), max);
                }).height(max);
 
             } else {
                // Remove data-label attribute from each cell
                $('tbody td', $table).each(function () {
                   $(this).removeAttr('data-label');
                });
 
                $('tbody tr', $table).each(function () {
                   $(this).height('auto');
                });
             }
          }
       })
       .on('select', function (e, dt, type, indexes) {
          var rowData = table.rows(indexes).data().toArray()
          $('#row-data').html(JSON.stringify(rowData));
       })
       .on('deselect', function () {
          $('#row-data').empty();
       })
 });