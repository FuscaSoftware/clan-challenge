<?php

/**
 * User: sbraun
 * Date: 19.07.17
 * Time: 14:16
 */
trait MY_Controller_Main
{

    public $default_header = 'common/header';

    public $default_footer = 'common/footer';

    public $templateDir = null;

    public $default_helpers = array('form', 'html', 'url', 'smr_html_helper');

    /** @var array $default_libraries Reihenfolge beibehalten! */
    public $default_libraries = array('fusca/bootstrap_lib', 'session', 'fusca/auth_ldap', 'fusca/acl_lib', 'fusca/smr_breadcrumbs', 'table');

    /** @var array $libraries */
    public $libraries = array();
    /** @var array $helpers */
    public $helpers = array();
    /** @var array $default_models */
    public $default_models = array();
    /** @var array $models */
    public $models = array();

    /** @var string html|ajax */
    public $request_type = null;

    /**
     * @var bool
     * true is active
     */
    public $auth_ldap_status = null;

    /** @var CI_Loader */
    public $load;

    /** @var CI_Input $input */
    public $input;

    /** @var MY_Output $output */
    public $output;

    /** @var CI_Session $session */
    public $session;

    /** @var CI_Config */
    public $config;

    /** @var Auth_ldap */
    public $auth_ldap;

    /** @var Acl_lib */
    public $acl_lib;

    /** @var bool $show_nav false for hide */
    public $show_nav;
    /** @var bool $show_sidebar false for hide */
    public $show_sidebar;
    /** @var bool $show_menu false for hide */
    public $show_menu;
    /** @var bool $no_overhead true to avoid loading of tags/info/sessions */
    public $no_overhead;

    /**
     * MY_Controller constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->init();
    }

    protected function init() {
        $this->server_name = $this->config->item('server_name');

        /* with setting "use_ldap" in config you can disable to connect to ldap, e.g. if you ar are working on a localhost */
        if (is_null($this->auth_ldap_status))
            $this->auth_ldap_status = (!is_null($this->config->item('use_ldap'))) ? $this->config->item('use_ldap') : true;

        # consider request_type and set auth_ldap_status - perhaps deactivate some libraries
        $this->get_request_type();


        $this->load->helper($this->default_helpers);
        $this->load->helper($this->helpers);
        $this->load->library($this->default_libraries);
        $this->load->library($this->libraries);
        $this->init_profiler();
        # Load Models - is deprecated - use ci()->any_model(name) methods!
        $this->load->model($this->default_models);
        $this->load->model($this->models);

        $this->init_auth();
//            if (!$this->is_ajax_request() && !@$this->no_overhead && isset($this->session))
//            if (!$this->is_ajax_request() && !@$this->no_overhead && isset($_SESSION['logged_in']))
        if (!$this->is_ajax_request() && !@$this->no_overhead)
            $this->log_user_browse_history();
//        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    protected function init_auth() {
        # ldap (not for Media, Api, Search)
        $controllers_without_login = [
            "Media",
            "Login",
            "Websocket",
            "Api",
            "Api_cw",
            "Api_dms",
            "Api_smr",
            "Product_feed_generator",
        ];

        if ($this->auth_ldap_status && !in_array(get_class($this), $controllers_without_login)) {
            if ($this->request_type == "curl") {
                header('HTTP/1.0 403 Forbidden');
                die("CURL REQUEST FROM THIS HOST IS NOT ALLOWED!");
            }
            if (!isset($this->auth_ldap))
                die("Please load ??fusca/auth_lap??-Library or set ??auth_ldap_status?? to false");
            # check user is logged in (otherwise redirect to login)
            if ($this->auth_ldap->is_authenticated()) {

                $controllers_with_login_without_acl = ['Dashboard', 'Login', 'Product_feed_generator'];

                # rights / acl
                if (!in_array(get_class($this), $controllers_with_login_without_acl) && !$this->acl_lib->is_superuser()) {
                    if (!$this->acl_lib->has_right()) {
                        // user has no right
                        if (ci()->request_type == "ajax") {
                            ci()->message("ACL: NOT ALLOWED", "danger");
                            echo ci()->show_ajax_message(ci()->data, TRUE);
                            die;
                        } else {
                            ci()->message("ACL: NOT ALLOWED", "danger");
                            if (1) {
                                $view_data = [];
                                $view_data['site_title'] = $this->config->item('default_site_title');
                                echo $this->show_page("Keine Rechte!", $view_data, true);
                            } else {
                                redirect('Dashboard');
                            }
                            die;
                        }
                    } else {
                        // user has right
                    }
                }
            } //else die;
        }
    }

    protected function init_profiler() {
        # Start/Show Profiler ?
        if (!$this->is_ajax_request()) {
            if ($this->input->get_post("profiler")) {
                $this->output->enable_profiler(TRUE);
                $this->save_queries = TRUE;
            } elseif ($this->input->get_post('debug')) {
                $this->save_queries = TRUE;
            }
            # why and where is the useless session created?
            if (!isset($_SESSION['logged_in']) && $this->session && !$this->session->ignore_sessions())
                $this->session->set_userdata('uri', $_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Determines Request-type and disable login and session for console/curl
     *
     * @return string console|curl|iframe|html
     */
    private function get_request_type() {
        # set request_type
        $this->request_type = ($this->input->post_get("request_type")) ? $this->input->post_get("request_type") : $this->request_type;
        if (is_null($this->request_type) || $this->request_type == "html")
            if (is_cli())
                $this->request_type = "console";
            elseif (stristr(@$_SERVER["HTTP_USER_AGENT"], 'curl'))
                $this->request_type = "curl";
            elseif (stristr(@$_SERVER['HTTP_REFERER'], "request_type=iframe"))
                $this->request_type = "iframe";
            else
                $this->request_type = "html";

        # deactivate auth_ldap for curl, console or if not wanted for localhost
        if (
            ($this->server_name == "localhost" && is_null($this->config->item('use_ldap')))
            || $this->request_type == 'console'
            || $this->request_type == 'curl' # this is a sucurity hole! :(
        ) {
            $this->auth_ldap_status = false;
        }

        $is_curl = ($this->request_type == "curl") ? true : false;
        if ($is_curl) {
            /* Requests from Zombie(in most cases cli- or curl-requests) */
            $is_curl_from_zombie = ($_SERVER['REMOTE_ADDR'] == "5.35.241.150") ? true : false;
            $is_curl_from_cimo = ($_SERVER['REMOTE_ADDR'] == "83.169.43.103") ? true : false;
            $is_curl_from_myself = (0 && $_SERVER["REMOTE_ADDR"] == $_SERVER["SERVER_ADDR"]) ? true : false;
        }
        if (is_cli()
            || $is_curl && $is_curl_from_zombie
            || $is_curl && $is_curl_from_cimo
            || $is_curl && $is_curl_from_myself) {
            $this->auth_ldap_status = false;
        }

        # remove session-library from default_libraries-array if curl or console
        if (($this->request_type == 'console' || $this->request_type == "curl")
            && in_array('session', $this->default_libraries))
            $this->default_libraries = array_diff($this->default_libraries, ['session']);
        return $this->request_type;
    }

    private function log_user_browse_history() {
        if (!in_array($this->request_type, ['curl', 'console']) && ci()->session) {
            $key = "cms_user_browse_history";
            /** @var MY_Router $router */
            $router = $this->router;
            $current_url = $router->uri->uri_string;
            $new_value = ['href' => ($current_url) ? $current_url : "/", 'label' => ($current_url) ? $current_url : "/"];
            if ($values = $this->session->__get($key)) {
                if (count($values) > 10) {
                    foreach ($_SESSION[$key] as $k => $v) {
                        if ($k > 9)
                            unset($_SESSION[$key][$k]);
                    }
                }
                reset($values);
                if ($new_value != current($values)) {
                    $values = array_merge([$new_value], $values);
                    $this->session->set_userdata($key, $values);
                }
            } else {
                $this->session->set_userdata($key, [$new_value]);
            }
            $this->session->set_userdata('log_uri', $_SERVER['REQUEST_URI']);
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function is_ajax_request(): bool {
        if (is_null($this->request_type))
            $this->get_request_type();
        return ($this->request_type == "ajax") ? true : false;
    }


    public function get_calling_method_name($step = 2) {
        #way A
//		debug_backtrace()[1]['function'];
        #way B
        $e = new Exception();
        $trace = $e->getTrace();
        //position 0 would be the line that called this function so we ignore it
        $last_call = $trace[$step]['function'];
//		var_dump($last_call);
        return ($last_call);
    }

    /**
     * @return string e.g. "content"
     */
    public function get_controller_name() {
        return strtolower(get_class($this));
    }

    /**
     * @return string e.g. cms
     */
    public function get_controller_path() {
        return (isset($this->controller_path)) ? $this->controller_path : "";
    }

    /**
     * @return string e.g. cms/content
     */
    public function get_full_controller_name() {
        return $this->get_controller_path() . $this->get_controller_name();
    }


    /**
     * returns the current logged in username
     * @return string username
     */
    public function get_username() {
        return @$_SESSION['logged_in']['displayname'];
    }

    public function get_user_id() {
        return @$_SESSION['logged_in']['id'];
    }

    public function is_backup_view() {
        return @$_COOKIE['show_backup'];
    }

    /***
     * @return array of path components
     */
    public function where_am_i() {
        $arr[0] = $this->uri->segment(0); #dir??
        $arr[1] = $this->uri->segment(1); #dir?
        $arr[2] = $this->uri->segment(2); #dir
        $arr[3] = $this->uri->segment(3); #controller
        $arr[4] = $this->uri->segment(4); #action

        $arr['directory'] = $this->router->directory;
        $arr['class'] = $this->router->class;
        $arr['method'] = $this->router->method;
        $arr['uri_string'] = $this->router->uri->uri_string;
//        var_dump($this->router);
//		$arr[$i++] = @$this->router->fetch_module(); //Module Name if you are using HMVC Component;
//		$this->router->fetch_class(); // class = controller
//		$this->router->fetch_method();

        $arr['path_to_controller'] = $this->router->directory . $this->router->class;
        $arr['path_to_action'] = $this->router->directory . $this->router->class . "/" . $this->router->method;

        return $arr;
    }

    /**
     * In list views ( index() ) you need to load the items linked to it.
     *
     * @param array         $items array of items/beans/row-objects which will be filled with their linked objects triggered by input-variables
     * @param MY_Model|null $model [optional] if null: $this->model is used
     */
    public function load_linked_data_for_foreign_keys(&$items, $model = null) {
        if (is_null($model)) {
            $model = $this->model;
        }

        foreach ($model->linked_by_knot as $object_type) {
            if (is_numeric($this->input->get_post($object_type))) {
                $this->data['box_index']['foreign_keys'][$object_type] = (int)$this->input->get_post($object_type);
                $model->get_related($object_type, $items);
            }
            if (is_numeric($this->input->get_post($object_type . '_id'))) {
                $this->data['box_index']['foreign_keys'][$object_type] = (int)$this->input->get_post($object_type . '_id');
                $model->get_related($object_type, $items);
            }
        }
    }

    /**
     * prepares index-list
     */
    protected function box_index(&$items = null) {
        if (!isset($this->data)) {
            $this->data = array();
        }

//        if (is_null($items))
//		    $items = $this->model->get_items();

        if (is_null($items)) {
//          $filter = array("skip" => 0);
            $filter = $this->input->post_get('filter');
            if (isset($filter['topic']['parent_id'])) {
                if (@$filter['topic']['parent_id'])
                    $parent_topic = ci()->topic_model()->get_item_by_field('name', $filter['topic']['parent_id']);
                if (@$parent_topic)
                    $filter['topic']['parent_id'] = $parent_topic->id;
                else
                    unset($filter['topic']['parent_id']);
            }
            $items = $this->model->get_items(@$filter[$this->model->table]);
        }
        /** @var MY_Model $model */
        $model = $this->model;
        $model->get_data_for_list($items, 0, $this->data);
        $this->data['box_index']['title'] = "Liste " . $this->model->items_label;
        $this->data['box_index']['list_fields_2D'] = (isset($this->model->list_fields_2D)) ? $this->model->list_fields_2D : $this->model->get_fields_2D($this->model->list_fields);
        $this->data['box_index']['list_row_view'] = "cms/list_row_view";

        $this->load_linked_data_for_foreign_keys($items);
        return $this->data;
    }

    /**
     * default index view for modal
     */
    public function index_modal(&$items = null, $modal_content = null) {
        if (!isset($this->data)) {
            $this->data = array();
        }
        $this->data['box_index']['a_target'] = "_blank";
        if (is_null($modal_content)) {
            $modal_content = $this->get_modal_content($items);
        }

//		$output[] = $this->bootstrap_lib->edit_box($item, $this->data, $this->location_model, $params);
//		$this->data['box_modal']['modal']['footer']['view'] = "empty_view";
        $this->data['box_modal']['modal_title'] = $this->model->item_label . " ausw??hlen";
//		$this->data['box_modal']['modal_content'] = implode("", $output);
        $this->data['box_modal']['modal_content'] = $modal_content;
        $this->data['box_modal']['modal']['size'] = "modal-lg";
        $this->data['box_modal']['modal']['footer']['view'] = "empty_view";

        $this->show_modal();
    }

    /**
     * @param        $items
     * @param string $view
     *
     * @return string
     */
    public function get_modal_content($items, $view = "cms/list_view") {
        $output = array();
//		$this->data['box_index']['hide_actions'] = true;
//		$this->data['box_index']['hide_header'] = true;
        $this->box_index($items);
        $this->data['box_index']['title'] = false;
        $this->data['box_index']['hide_actions'] = true;
        return $this->get_view($view, $this->data);#custom view
    }

    /**
     * default create view for modal
     *
     * @param object|null $item
     */
    public function create_modal($item = null) {
        $model = $this->model;
        if (empty($this->data)) {
            $this->data = array();
        }

        if (is_null($item)) {
            $id = 0; #new item
            if (!$id || $id == 0) {
                $item = $model->get_empty_item();
                $item->id = 0;
            } elseif (is_numeric($id)) {
                $item = $model->get_item_by_id($id);
            } else {
                $this->message("$model->item_label mit ID: $id ist ung??ltig.");
            }

            $foreign_keys = $this->input->post_get("foreign_keys");
//            $this->dump($foreign_keys);

            ci()->controller_helper_lib()->assign_foreign_keys_to_item($item, $this, $foreign_keys);
        }

        $params = array();
        $this->data['box_edit']['hide_actions'] = false;
        $this->data['box_edit']['hide_header'] = true;

        $output[] = $this->bootstrap_lib()->edit_box($item, $this->data, $this->model, $params);
//		$this->smr_breadcrumbs->push('Location anlegen', '#');
//		$this->data['breadcrumbs'] = $this->smr_breadcrumbs->show();
        $this->data['box_modal']['modal']['footer']['view'] = "empty_view";
        $this->data['box_modal']['modal_title'] = $this->model->item_label . " anlegen";
        $this->data['box_modal']['modal_content'] = implode("", $output);
//        $this->data['box_modal']['modal']['reload_onclose'] = false;#for testing
        $this->show_modal();
    }

    /**
     * AJAX-Method: Shows filter-input above table
     *
     * @param string $modelname
     * @param string $fieldname
     */
    public function get_filter($modelname, $fieldname) {
        $this->data = array();
        $filter = $this->input->get_post("filter");
        if (@$this->settings_filter[$fieldname]['onDemand'] === false) {#autocomplete is switched to on-demand
            $items = $this->model->get_autocomplete($fieldname);
            $this->data['filter_field']['params'] = array("autocomplete_min_length" => 0);
        } else {
            $items = array();
        }
        $values = array();
        foreach ($items as $item) {
            $values[] = htmlspecialchars($item->{$fieldname});
        }
        $autocomplete = $values;

        $this->data['filter_field']['fieldvalue'] = ($filter[$this->model->table][$fieldname]) ? $filter[$this->model->table][$fieldname] : null;
        $this->data['filter_field']['fieldname'] = $fieldname;
        $this->data['filter_field']['modelname'] = $this->model->table;
        $this->data['filter_field'][$fieldname]['autocomplete'] = $autocomplete;
        $this->data['filter_field'][$fieldname]['autocomplete_url'] = site_url($this->get_full_controller_name() . "/get_autocomplete/$modelname/$fieldname");
        $this->data['html']['html'][".box_" . $modelname . "_index.controller_" . $modelname . " thead th.field_" . $fieldname . " .filter"] = $this->get_view("boxes/filter_field");
        $this->data['html']['remove'][".box_" . $modelname . "_index.controller_" . $modelname . " thead th.field_" . $fieldname . " .filter_btn"] = "";
        $this->show_ajax();
    }

    /**
     * AJAX-Method: Delivers autocomplete value list to the jquery-ui
     *
     * @param string $modelname
     * @param string $fieldname
     */
    public function get_autocomplete($modelname, $fieldname) {
        $term = $this->input->get_post("term");
        $this->data = [];
        /** @var MY_Model $model */
        $model = $this->model;
        $items = $model->get_autocomplete($fieldname, 20, addslashes($term));
        $autocomplete = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $autocomplete[] = (object)array(
                    "id" => $item->$fieldname,
                    "label" => $item->$fieldname,
                    "value" => $item->$fieldname
                );
            }
        }
        $this->data = $autocomplete;
        $this->show_ajax_message();
    }

    /**
     * AJAX-Method: Called by ajax after filter change/submit to update object list (append or replace rows inside the tbody of the
     * box_index table
     *
     * ! You can overload this method with params-array !!!
     */
    public function ajax_list() {
        $params = (is_array(func_get_arg(0))) ? func_get_arg(0) : array();
//        $params = array();
        $default_params = array();
        array_merge($default_params, $params);
        $modelname = $this->model->table;

        $filter = $this->input->post_get('filter');

//        $this->show_ajax();
        $items = null;
        $this->box_index($items);
//        $output[] =  $this->get_view("cms/list_view", $this->data);

        # way2: append rows to tbody:
        $model = $this->model;
        $box_index = $this->data['box_index'];
        $list_fields_2D = $this->data['box_index']['list_fields_2D'];
        $list_row_view = $this->data['box_index']['list_row_view'];

        if (@$params['always_append'] || @$filter[$this->model->table]['skip'] > 0)#filter changed
        {
            $append = true;
        } else {
            $append = false;
        }
        if (@$params['insert_after']) {
            foreach ($this->data['items'] as $item) {
                $rows[] = $this->bootstrap_lib->show_item_row($item, $model, $list_fields_2D, $box_index['modelname'],
                    $box_index['controllername'], @$box_index['foreign_keys'], @$box_index['active_id'], $list_row_view,
                    $box_index);
            }
            if (!empty($rows)) {
                $selector = ".box_index.controller_$modelname .tbody";
                if (strlen($params['insert_after']) > 0)
                    $selector = $params['insert_after'];
                $this->data['html']['insert_after'][$selector] = $output[] = implode("", $rows);#works not!? //todo
                $this->data['html']['append'][$selector] = $output[] = implode("", $rows);
            }

        } elseif ($append) {
            foreach ($this->data['items'] as $item) {
                $rows[] = $this->bootstrap_lib->show_item_row($item, $model, $list_fields_2D, $box_index['modelname'],
                    $box_index['controllername'], @$box_index['foreign_keys'], @$box_index['active_id'], $list_row_view,
                    $box_index);
            }
            if (!empty($rows)) {
                $this->data['html']['append'][".box_index.controller_$modelname .tbody"] = $output[] = implode("",
                    $rows);
                if (@$filter[$this->model->table]['skip'])
                    $this->data['html']['value']["[name='filter[" . $box_index['modelname'] . "][skip]']"]
                        = $filter[$this->model->table]['skip'] + count($rows);
            } else {
                $this->message("Es konnten keine weiteren Objekte gefunden werden.", "info", "default", 1500);
            }
//                $this->data['html']['html']['.box_index.controller_$modelname tfoot td'] = $output[] =  "Es werden bereits alle verf??gbaren Ergebnisse angezeigt.";

        } else { # way1: replace whole box:
//            $this->data['html']['html']['.box_index'] = $output[] =  $this->get_view("cms/list_view", $this->data);
            foreach ($this->data['items'] as $item) {
                $rows[] = $this->bootstrap_lib->show_item_row($item, $model, $list_fields_2D, $box_index['modelname'],
                    $box_index['controllername'], @$box_index['foreign_keys'], @$box_index['active_id'], $list_row_view,
                    $box_index);
            }
            if (isset($rows)) {
                $this->data['html']['html'][".box_index.controller_$modelname .tbody"] = $output[] = implode("", $rows);
            } else {
                $this->data['html']['html'][".box_index.controller_$modelname .tbody"] = "Keine Ergebnisse";
            }

        }
        $this->show_ajax_message();
    }
}