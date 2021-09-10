if (typeof dt === 'undefined')
    var dt = [];
if (typeof dt_options === 'undefined')
    var dt = [];
if (typeof sb_init_datatable === 'undefined')
    var sb_init_datatable = [];


sb_init_datatable['user_roles'] = function (selector, ajax_source, options) {
    if (typeof dt_options === 'undefined')
        var dt_options = [];
    var table;
    dt_options['user_roles'] = {
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
                targets: "user_has_role",
                // visible: false
                "render": function (data, type, row) {
                    // console.log(row);
                    // console.log(type);
                    // return data + ' (' + row[3] + ')';
                    var onchange = "toggle_role(this)";
                    var data_attribs = {"role_id": row[0]};
                    return sb_draw_switch(data, row, onchange, data_attribs);
                }
            },
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
            // {
            //     text: 'My button',
            //     action: function (e, dt, node, config) {
            //         alert('Button activated');
            //     }
            // }
        ],
    };

    dt[selector] = table = $(selector).DataTable(dt_options['user_roles']);

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

function toggle_role(el, options) {
    var userHasRole = $(el).is(':checked');
    var role_id = $(el).data('role_id');
    var container = $(el).closest('.hazel-table-container');
    var user_id = container.data('user_id');


    // var request_data = {'data': data, 'options': options};
    var request_data = {
        'data': {
            'role_id': role_id,
            'user_id': user_id,
            'userHasRole': userHasRole
        }
    };
    var url = container.data('base_url') + "user/toggle_role";
    // console.log(options);
    // return false;

    if (1) {
        var success = function (json) {
            // console.log(json);
            // callback(json);
        };
        ajax_data2(url, request_data, success, 0);
    }
}
