<?php
/**
 * User: sbraun
 * Date: 03.07.18
 * Time: 15:37
 */
if (!function_exists("fe_source_with_timestamp"))
    ci()->loader()->helper('sb_helper');
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $base ?>assets/images/favicon.png">
<!--    <link rel="icon" type="image/png" sizes="16x16" href="--><?//= $base ?><!--logo/logo 3/logo.png">-->
    <title><?= $site_title ?? 'Hazelnut 🌰' ?></title><!-- htmlentity: &#x1F330; -->
<!--    CSS for Datatables-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.2.7/css/select.dataTables.min.css">

    <!-- Custom CSS -->
    <link href="<?= $base ?>/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= $base ?>/dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="<?= $base ?>css/hazel_style.css" rel="stylesheet">
    <link href="<?= $base ?>css/messages.css" rel="stylesheet">
    <link href="<?= $base ?>css/toggle_switch_b.css" rel="stylesheet">
<!--    scripts-->
    <script src="<?= $base ?>/assets/libs/jquery/dist/jquery.min.js"></script>
    <!--    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <? /* handlebars is a mustache view-renderer for javascript */ ?>
    <script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.10/handlebars.min.js" type="text/javascript"></script>
    <script src="<?= fe_source_with_timestamp('js_hazel/frontend_renderer.js') ?>" type="text/javascript"></script>
    <script>
        var site_url = '<?= site_url() ?>';
    </script>
</head>