function sb_modal_init(modal_selector, options) {
    // $(modal_selector).modal();
    $(modal_selector).modal({
        // backdrop: false,
        show: true
    });
    // $(modal_selector).addClass('in');
    $(modal_selector).on('hidden.bs.modal', function (e) {
        $("body").css("cursor", "progress");
        $(modal_selector).remove();
        var error = $('.error-messages iframe').contents().find('body').html();
        if (!error && typeof noreload === "undefined") {
            if (options.reload_onclose) {
                location.reload();
            } else {
                $("body").css("cursor", "default");
            }
        }
    });
    $(modal_selector).on('shown.bs.modal', function(e) {
        if (options.callbacks !== undefined && typeof options.callbacks.shown === 'function')
            options.callbacks.shown();
        console.log('modal shown');

        if (0) {
            var selected = [];
            var ajax_source = "http://localhost/hazelnut/hazel/customer/ajax_data_json?request_type=ajax";
            var fn = function () {
                sb_init_datatable(ajax_source);
            };
            window.setTimeout(fn, 1000);
        }
        if (0) {
            $('#example').DataTable( {
                dom: 'Bfrtip',
                buttons: [
                    // 'copy',
                    // 'csv',
                    // 'excel',
                    // 'pdf',
                    {
                        extend: 'print',
                        text: 'Print all (not just selected)',
                        exportOptions: {
                            modifier: {
                                selected: null
                            }
                        }
                    }
                ],
                select: true
            } );
        }

    });
    $(modal_selector + ' .box_edit button:submit').click(function () {
        $("body").css("cursor", "progress");
        ajax_submit(modal_selector + " .box_edit_form");
        $(modal_selector).modal('hide');
        $("body").css("cursor", "default");
        return false;
    });

    // $(modal_selector + ' .resizable .modal-content .modal-body').resizable({
    //     alsoResize: ".modal-content"
    // });
    $(modal_selector + ' .resizable .modal-content').resizable({
        containment: modal_selector,
        minWidth: 150,
        minHeight: 150
        // alsoResize: ".modal-header, .modal-body, .modal-footer"
        // alsoResize: ".modal-body"
    });
    $(modal_selector + ' .modal-dialog.draggable').draggable({
        // containment: 'parent',
        // scroll: false,
        handle: ".modal-header"
    });
    $(modal_selector + " .modal-body").css('overflow-y', 'auto');
    $('[data-toggle="tooltip"]').tooltip();
    $(modal_selector + ' .btn-delete').click(function () {
        return confirm('Sind Sie sicher?');
    });
}