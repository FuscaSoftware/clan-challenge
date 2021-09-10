if (typeof dt === 'undefined')
    var dt = [];
if (typeof sb_init_datatable === 'undefined')
    var sb_init_datatable = [];

sb_init_datatable['booking_articles'] = function (selector, ajax_source, options) {

    dt[selector] = table = $(selector).DataTable({
        'dom': 'ti',
        "processing": true,
        "serverSide": true,
        // select: {
        //     style: 'os'
        //     , selector: 'td'
            // , selector: 'td:first-child' // area where user can select the row
        // },
        'select': true,
        // "ajax": ajax_source,
        'ajax': function (data, callback, settings) {
            console.log(data);
            // console.log(callback);
            console.log(settings);
            // callback(
                // JSON.parse(localStorage.getItem('dataTablesData'))
            // );
            sb_datawrapper(data, callback, settings, options);
            // return {};
        },
        "drawCallback": function (settings) {
            console.log('Datatables has redrawn the table');
        },
        "rowCallback": function (row, data) {
            // if ($.inArray(data.DT_RowId, selected) !== -1) {
            //     $(row).addClass('selected');
            // }
        },
        // buttons: [
        //     // 'pageLength',
        //     {
        //         text: "Aktualisieren",
        //         action: function (e, dt, node, config) {
        //             table.ajax.reload();
        //             /* it would be better to just reload the data-json */
        //         }
        //     },
        // ]
        buttons: [
            {
                text: "Aktualisieren",
                action: function (e, dt, node, config) {
                    table.ajax.reload();
                    // dt[selector].ajax.reload();

                    /* it would be better to just reload the data-json */
                }
            },
            {
                text: 'My button',
                action: function (e, dt, node, config) {
                    alert('Button activated');
                }
            }
        ],
    });

    $(selector + ' tbody').on('click', 'tr', function () {
        var id = this.id;
        console.log(id);
        // var index = $.inArray(id, selected);

        // if (index === -1) {
        //     selected.push(id);
        // } else {
        //     selected.splice(index, 1);
        // }

        // $(this).toggleClass('selected');
    });
}