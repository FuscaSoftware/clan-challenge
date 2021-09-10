/*
    @version: 2018-10-24
 */
function register_template(name, url) {
    if (Handlebars === undefined) {
        console.log("Handlebars not loaded yet");
    }
    if (Handlebars.template[name] === undefined) {
        var request = $.get({
            url: url,
            success: function (data) {
                Handlebars.template[name] = Handlebars.compile(data);
            },
            error: function (data) {
                console.log("Error requesting Template: " + url);
            }
        });
        if (request.status == "404") {
            console.log("404: Template nicht gefunden!");
        }
    }
}
function register_partial(name, url) {
    if (Handlebars === undefined) {
        console.log("Handlebars not loaded yet");
    }
    $.get({
        url: url,
        success: function (data) {
            Handlebars.registerPartial(name, data);
        },
        error: function (data) {
            console.log("Error requesting Template: "+ url);
        }
    });
}