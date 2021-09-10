<?php

/**
 * User: sbraun
 * Date: 19.07.17
 * Time: 10:41
 */
trait MY_Model_Trait
{

    /** @var bool $loadDb */
    public $load_db = true;
    /** @var bool $read_only */
    public $read_only = false;
    /** @var string $database_group */
    public $database_group = null;

    /** @var string $table - overwrite it!!! */
    protected $table = null;
    /** @var string */
    public $full_controller_name = null;
    /** @var string */
    public $item_label = null;
    /** @var string */
    public $items_label = null;
    /** @var string $label_field - defines the field in database which is used by default for e.g. popups */
    public $label_field = "name";
    /** @var array modelnames/tablenames of objects which can be linked by default object_object_knots */
    public $linked_by_knot = array();

    /** @var array */
    public $function_fields = array("created" => "now()", "modified" => "now()", "uuid" => "uuid()");
    /** @var string $item_class */
    public $item_class = "Item";
    /** @var null|array $primary_key to setup a combined primary key */
    public $primary_key = null;

    /**
     * Fields shown in Backend (edit)
     * @var array
     */
    public $backend_fields = array();

    /**
     * These fields are used for update/inserts to/in the database
     * @var array
     */
    public $db_fields = array();

    /**
     * These caches/stores the db-schema
     * @var array
     */
    public $db_schema = array();

    /**
     * @var MY_Controller $ci
     * @deprecated is ugly! use ci()!
     */
    public $ci;

    /**
     * @var MY_Controller $CI
     * @deprecated is ugly! use ci()!
     */
    public $CI;

    /**
     * Constructor which loads (by default) database
     * @throws RuntimeException
     */
    public function __construct() {
        parent::__construct();
        $this->init_db();
        $this->ci = $this->CI = MY_Controller::get_instance();
    }

    public function init_db($database_group = null) {
        if (!is_null($database_group))
            $this->database_group = $database_group;
        if ($this->load_db && !isset($this->db)) {
            if (!isset($this->database_group) || is_null($this->database_group))
                $this->load->database();
            else
//				$this->db = $this->load->database('smr-picture', TRUE);
                $this->db = $this->loader()->database($this->database_group, TRUE);
            if (@ci()->save_queries)
                $this->db->save_queries = true;

            if (empty($this->table) && !($this->table == false))
                throw new RuntimeException("Please define property \$table for '" . get_class($this) . "'-Class.");
//			var_dump(array(__LINE__,$this->db));
        }
    }

    public function list_fields_2D($foreign_object_type = null): array {
        if (isset($this->list_fields_2D))
            return $this->list_fields_2D;
        elseif (isset($this->list_fields))
            return $this->get_fields_2D($this->list_fields);
    }

    /**
     * Helper Function to give you object-type of $this->db
     * @return MY_DB_query_builder
     */
    public function db(): MY_DB_query_builder {
        return $this->db;
    }

    /**
     * Helper Function to give you object-type of $this->load
     * @return MY_Loader
     */
    public function loader(): MY_Loader {
        return $this->load;
    }

    public function mh(): Model_helper_lib {
        $this->loader()->library("model_helper_lib");
        return $this->model_helper_lib;
    }

    /**
     * @param db $db
     *
     * @return string
     */
    public function last_query($db = null) {
        if (is_null($db))
            $db = $this->db();
        return $db->last_query();
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function get_fields_2D(array $array) {
        foreach ($array as $v) {
            $fields_2D[$v] = array();
        }
        return $fields_2D;
    }

    /**
     * Wende Filter auf $db an.
     *
     * sample1 = [
     *  "hotel" => ["name" => $data['value']]
     * ]
     *
     *
     *
     * @param array               $filterArray
     * @param MY_DB_query_builder $db
     */
    public function append_filters($filterArray, &$db) {
        if ($filterArray) {
            if (isset($filterArray[$this->table]))
                $filters = $filterArray[$this->table];
            else
                $filters = $filterArray;
            unset($filters['skip']);
            foreach ($filters as $fK => $fV) {
                if (is_string($fK) && strtolower($fK) === "order_by") { # customer => order_by => [field => id, direction => asc]
//                    var_dump($fK, $fV);
                    if (is_array($fV))
                        $db->order_by($fV['field'], $fV['direction']);
                    else
                        $db->order_by($fV);
                } elseif (is_array($fV)) { # customer => id => []
                    foreach ($fV as $fVK => $fVV) {

//                        ci()->dump([
//                            $fVK, $fVV, @$a,
//                        ]);

                        if (in_array($fVK, ['skip', 'limit', 'order_by']))
                            continue;
                        if (is_string($fVK) && strtolower($fVK) == "condition")
                            $db->where($fVV);
                        elseif (is_string($fVK) && strtolower($fVK) == "or_condition")
                            $db->or_where($fVV);
                        elseif (is_array($fVV) && strtolower($fVK) == "in")
                            $db->where_in($fK, $fVV);
                        elseif (is_array($fVV) && strtolower($fVK) == "one_of")
                            foreach ($fVV as $fVVV)
                                $db->or_where($fK, $fVVV);
                        elseif (is_array($fVV) && strtolower($fVK) == "or_like")
                            foreach ($fVV as $fVVV)
                                $db->or_like($fK, $fVVV);
                        elseif (is_array($fVV) && strtolower($fVK) == "or_like2") {
                            $db->or_like($fVV, null, "none", false);
//                            ci()->dump($fVV);
                        } else
                            $db->or_like($fK, $fVV);

                    }
                } else {
                    if (!empty($fV) || is_numeric($fV)) # customer => id = value
                        $db->like($fK, $fV);
                }
            }
        }
    }


    /**
     * applies db-schema (data types) from database to field-Array
     *
     * @param array  $fields_2D to set types and values
     * @param string $table     optional table get schema from
     *
     * @return bool
     */
    public function apply_schema(&$fields_2D, $table = null) {
        if (@ci()->no_overhead)
            return false;
//		get_instance()->dump($fields);
//		var_dump($fields);
//		var_dump(array_keys($fields));
//		var_dump(array_values($fields));
        if (is_null($table))
            $table = $this->table;
        foreach ($fields_2D as $fK => $fV) {
            $schema = $this->get_db_schema($fK, $table);
            if (!is_object($schema)) {#occures when field is not in table eg. foreign_key-field or mis-typing
                continue;
            }
            $this->apply_shema_for_column($schema, $fields_2D, $fK);
        }
        if ($schema)
            return true;
    }

    public function apply_shema_for_column($schema, &$fields, $fK) {
        $fields[$fK]['type'] = (isset($fields[$fK]['type'])) ? $fields[$fK]['type'] : $schema->DATA_TYPE;
        if (!isset($fields[$fK]['COLUMN_COMMENT']))
            $fields[$fK]['COLUMN_COMMENT'] = (!empty($schema->COLUMN_COMMENT)) ? "" . $schema->COLUMN_COMMENT . "" : "";
        if ($schema->DATA_TYPE == "enum") {
            $fields[$fK]['values'] = $this->get_enum_values($fK, $schema);
        } elseif (in_array($schema->DATA_TYPE, array("varchar", "int", "tinyint")))
            $fields[$fK]['limit'] = isset($fields[$fK]['limit']) ?
                $fields[$fK]['limit'] : intval(str_replace(array($schema->DATA_TYPE . "(", ")"), array("", ""), $schema->COLUMN_TYPE));
    }

    /**
     * @param string|null $column_name
     * @param string|null $table
     *
     * @return object|mixed
     */
    public function get_db_schema($column_name = null, $table = null) {
        /** @var MY_DB_query_builder $db */
        $db = $this->db();
        if (is_null($table))
            $table = $this->table;
        if (!isset($this->db_schema) || !isset($this->db_schema[$column_name])) {
            $column_q = ($column_name) ? "COLUMN_NAME = '$column_name'" : "1";
            $q = "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT, COLUMN_TYPE, TABLE_SCHEMA, TABLE_NAME  FROM INFORMATION_SCHEMA.COLUMNS WHERE " .
                "TABLE_SCHEMA = '" . $db->database . "' AND" . " TABLE_NAME = '$table' AND " . $column_q . "\n";
            $query = $db->query($q);
            if ($column_name) {
                $this->db_schema[$column_name] = $query->row_object();
            } else {
                $this->db_schema[$column_name] = $query->result_object();
            }
        }
        return $this->db_schema[$column_name];
    }

    public function get_enum_values($column_name, $schema = null) {
        if (is_null($schema))
            $schema = $this->get_db_schema($column_name);
        if ($schema->DATA_TYPE != "enum")
            return FALSE;
        $types = str_replace(array("enum('", "')"), array("", ""), $schema->COLUMN_TYPE);
        $types_arr = explode("','", $types);
        $items = array();
        foreach ($types_arr as $k => $v) {
            $items[] = (object)array("id" => $v, "name" => $v);
        }
        return $items;
    }

    /**
     *
     * @return \Empty_Silent_Item Object so you can use a edit-form for create-action without 'undefindd-property'-warnings.
     */
    public function get_empty_item() {
        return new Empty_Silent_Item();
    }

    /**
     * Set some default values to use $output[] = $this->get_view("cms/list_view", $data);
     *
     * @param Collection|array $items
     * @param int              $active_id
     * @param array            $data
     *
     * @return array
     */
    public function get_data_for_list($items, int $active_id = 0, array &$data = null): array {
        if (is_null($data))
            $data = [];
        $data['box_index']['create_params'] = @$data['create_params'];
        $data['box_index']['modelname'] = $this->table;
        $data['box_index']['controllername'] = $this->full_controller_name;
//        $data['box_index']['title'] = "Liste " . $this->item_label;
        $data['box_index']['title'] = $this->item_label;
        $data['items'] = $data['box_index']['items'] = $items;
        $data['box_index']['active_id'] = (int)$active_id;
//        $data['box_index']['hide_actions'] = false;
//        $data['box_index']['list_fields'] = $this->list_fields;
//        $data['box_index']['list_fields_2D'] = $this->list_fields_2D;
        return $data;
    }
}