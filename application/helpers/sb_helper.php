<?php
/**
 * User: sbraun
 * Date: 02.10.18
 * Time: 10:39
 */

function fe_source_with_timestamp($source):string {
    if (file_exists(FCPATH . $source))
        return (!empty($source))? site_url($source . '?fmt=' . filemtime(FCPATH . $source)) : '';
    return "[source $source not existing]";
}