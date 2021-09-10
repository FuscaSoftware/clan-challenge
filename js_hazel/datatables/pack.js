if (typeof dt === 'undefined')
    var dt = [];
if (typeof dt_options === 'undefined')
    var dt = [];
if (typeof sb_init_datatable === 'undefined')
    var sb_init_datatable = [];

sb_init_datatable['pack'] = function (selector, ajax_source, options) {
    var table;
    dt[selector] = table = $(selector).DataTable({
        'dom': 'Bfrtip',
        "processing": true,
        "serverSide": true,
        // select: {
        //     style: 'os'
        //     , selector: 'td'
        // , selector: 'td:first-child' // area where user can select the row
        // },
        'select': true,
        'paging': false,
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
        'columnDefs': [
            // { targets: 3, visible: false},
            {
                targets: "switch_law",
                // visible: false
                "render": function (data, type, row) {
                    // console.log(row);
                    // console.log(type);
                    // return data + ' (' + row[3] + ')';
                    var onchange = "toggle_law(this)";
                    return sb_draw_switch(data, row, onchange);
                }
            },
            {
                targets: "user_roles",
                // visible: false
                "render": function (data, type, row) {
                    // console.log(row);
                    // console.log(type);
                    // return data + ' (' + row[3] + ')';
                    if (data === null || data.length === 0)
                        data = '<em>' + '[keine]' + '</em>';
                    return "<a data-user_id='" + row[0] + "' href='javascript:void(0)' onclick='user_roles(this)'>"+ data + "</a>";
                }
            },
            {
                targets: "users_of_role",
                // visible: false
                "render": function (data, type, row) {
                    // console.log(row);
                    // console.log(type);
                    // return data + ' (' + row[3] + ')';
                    if (data === null || data.length === 0)
                        data = '<em>' + '[keine]' + '</em>';
                    return "<a data-role_id='" + row[0] + "' href='javascript:void(0)' onclick='role_users(this)'>"+ data + "</a>";
                }
            }
            // { targets: [0, 1], visible: true},
            // { targets: '_all', visible: false }
            // {
            //     // The `data` parameter refers to the data for the cell (defined by the
            //     // `data` option, which defaults to the column being worked with, in
            //     // this case `data: 0`.
            //     "render": function ( data, type, row ) {
            //         console.log(row);
            //         return data +' ('+ row[3]+')';
            //     },
            //     "targets": 0
            // },
            // { "visible": false,  "targets": [ 3 ] }
        ],
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
};

