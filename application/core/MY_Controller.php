<?php
require_once APPPATH . "/core/traits/MY_Controller_Links.php";
require_once APPPATH . "/core/traits/MY_Controller_Output.php";
require_once APPPATH . "/core/traits/MY_Controller_Main.php";
/**
 * Description of MY_Controller
 *
 * @author sebra
 */
class MY_Controller extends CI_Controller
{
    use MY_Controller_Links;
    use MY_Controller_Output;
    use MY_Controller_Main;

}
