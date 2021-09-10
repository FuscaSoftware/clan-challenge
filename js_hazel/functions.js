/**
 * @link https://github.com/FuscaSoftware/jquery.fullscreen
 */
function toggle_fullscreen() {
    console.log ('toogle_fullscreen() called.');
    if ($.fullscreen.isFullScreen())
        $.fullscreen.exit();
    else
        $('body').fullscreen();
}