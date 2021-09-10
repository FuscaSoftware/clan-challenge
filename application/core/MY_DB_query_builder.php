<?php
require_once APPPATH . "/core/traits/MY_DB_query_builder_Main.php";

/**
 * User: sbraun
 * Date: 02.01.17
 * Time: 15:02
 */
class MY_DB_query_builder extends CI_DB_query_builder
{
    use MY_DB_query_builder_Main;
}