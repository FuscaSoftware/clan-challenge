function sb_datawrapper(data, callback, settings, options) {
    /*
        var data = {
            // "draw": data.draw,
            "recordsTotal": 57,
            "recordsFiltered": 57,
            "data": [
                // {
                //     "0": "Airi",
                //     "1": "Satou",
                //     "2": "Satou",
                //     "3": "Accountant",
                //     "4": "28th Nov 08",
                //     "5": "$162,700",
                //     "DT_RowId": "row_5"
                // }
                [
                    "Airi28",
                    "Satou",
                    "Satou",
                    "Accountant",
                    "28th Nov 08",
                    "$162,700"
                ],
                [
                    "Airi28",
                    "Satou",
                    "Satou",
                    "Accountant",
                    "28th Nov 08",
                    "$162,700"
                ]
            ]
        };
        */
    var request_data = {'data': data, 'options': options};
    // var request_data = {};
    var data;
    // var url = "http://localhost/hazelnut/hazel/customer/ajax_data_json";
    var url = options.ajax_source;
    // console.log(options);
    // return false;

    if (1) {
        var success = function (json) {
            console.log(json);
            callback(json);
        };
        ajax_data2(url, request_data, success, 1);
    } else {
        var doneFn = function (response) {
            if (response instanceof Object)
                var json = response;
            else
                var json = $.parseJSON(response);
            callback(json);
        };
        var failFn = function () {

        };
        var jqxhr = $.get(url, data, function () {
        }, 'json')
            .done(doneFn)
            .fail(failFn);
    }
    return true;
}

function sb_draw_switch(value, row, onchange, data) {
    var json = {
        "id": row[0],
        'data': data,
        checked: (parseInt(value))? 'checked=\"checked\"' : '',
        onchange: onchange,
    };
    // console.log(row);
    // console.log(data);
    // console.log(json);
    return Handlebars.template["toggle_switch"](json);
    // return (data)? "+" : "-";
}