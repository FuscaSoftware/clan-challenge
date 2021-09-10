<?php
/**
 * User: sbraun
 * Date: 11.01.18
 * Time: 12:17
 */

trait MY_Model_CRUD_Trait
{

    /**
     * @param array $filter optional
     *
     * @return array|false array of row-items(beans)
     * @deprecated since 2016-12-02 - only because of to avoid 'should be compatible' warning
     *             Default Method to get a bunch of items of the type of the model
     *
     */
    public function get_items($filter = null) {
        if (!is_null($filter) || is_array($filter))
            return $this->get_items2($filter, null);
        else
            return $this->get_items2();
    }

    /**
     * Default Method to get a bunch of items of the type of the model
     *
     * @param array|null  $filter optional
     * @param string|null $select optional
     *
     * @param array       $params
     *
     * @return CI_DB_result
     */
    public function get_items_result(array $filter = [], string $select = null, $params = []) {
        /** @var MY_DB_query_builder $db */
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        if (isset($params['where']))
            $db->where($params['where']);
        $table = ($params['table']) ?? $this->table;
        if (!is_null($select))
            $db->select($select);
        if (!is_null($filter) && !empty($filter))
            $this->append_filters($filter, $db);
//        $params = @func_get_arg(2);
        if (isset($params['order_by'])) {
            if (!is_array(['order_by']))
                $db->order_by($params['order_by']);
            else
                $db->order_by($params['order_by']['field'], $params['order_by']['direction']);
        }
        if (isset($params['limit'])) {
            $db->limit($params['limit']);
        }

//		if(!isset($this->db))
//			return null;
        if (in_array('sort', $this->db_fields))
            $db->order_by('sort', 'ASC');
        elseif (in_array('modified', $this->db_fields))
            $db->order_by($table . '.modified', 'DESC');
        elseif (in_array('updated_at', $this->db_fields)) {
            $db->order_by($table . '.id', 'DESC');
            $db->order_by($table . '.updated_at', 'DESC');
        }

        return $db->get($table);
    }

    /**
     * Default Method to get a bunch of items of the type of the model
     *
     * @param array|null  $filter optional
     * @param string|null $select optional
     *
     * @param array       $params
     *
     * @return array|false array of row-items(beans)
     */
    public function get_items2(array $filter = [], string $select = null, $params = []) {
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        $params['db'] = $db;
        if (!empty($filter) && !isset($filter[$this->table]['limit']) && @$this->default_limit && (!is_a(ci(), 'Api')))
            $db->limit($this->default_limit, (isset($filter['skip'])) ? $filter['skip'] : 0);
        return $this->get_items_result($filter, $select, $params)->custom_result_object($this->item_class);
    }

    /**
     * Default Method to get a bunch of items of the type of the model as a Collection
     *
     * @param array|null  $filter optional
     * @param string|null $select optional
     *
     * @param array       $params
     *
     * @return DynCollection
     */
    public function get_items3(array $filter = [], string $select = null, $params = []): DynCollection {
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        $params['db'] = $db;
//        if (!empty($filter))
//        $db->limit($this->default_limit, (@$filter['skip'])?: 0);#should be done in get_array()-Method-Call
        return new DynCollection($this->get_items_result($filter, $select, $params), $this->item_class);
    }


    /**
     * @param int    $id
     * @param string $table
     * @param array         third parameter (overloading) params array
     *
     * @return Item|object|void object-class is taken from the $item_class property if set.
     */
    public function get_item_by_id($id, $table = null) {
        $params = @func_get_arg(2);
        /** @var MY_DB_query_builder $db */
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        if (!is_numeric($id)) {
            $db->reset_query();
            return;
        }
        if (is_null($table))
            $table = $this->table;
//        $this->db->select("'$this->table' as _MODEL");
        if (isset($params['select']))
            $db->select($params['select']);
        else
            $db->select("$db->database.$table.*");

        $query = $db->get_where($table, [$table . '.id' => $id]);
        if (isset($params['item_class']) && class_exists($params['item_class']))
            return $query->custom_row_object(0, $params['item_class']);
        else if (isset($this->item_class))
            return $query->custom_row_object(0, $this->item_class);
        else
            return $query->custom_row_object(0, "Item");
    }

    /**
     * @param array  $map    key=>value pairs
     * @param string $select = null
     * @param string $table  = null
     *
     * @param string $item_class
     *
     * @return object[]|array
     */
    public function get_items_by_fields(array $map, $select = null, $table = null, $item_class = null) {
        if (!is_null($select))
            $this->db()->select($select);
        if (is_null($table))
            $table = $this->table;
        $this->db()->from($table);
        foreach ($map as $k => $v) {
            if (is_array($map[$k]))
                die("WRONG FORMAT OF \$map");
            $this->db()->where([$k => $v]);
        }
        $query = $this->db()->get();
//        return [];
        if (!is_null($item_class))
            return $query->custom_result_object($item_class);
        elseif ($table == $this->table)
            return $query->custom_result_object($this->item_class);
        else
            return $query->result_object();
    }

    /**
     * @param string $field_name
     * @param string $field_value
     * @param string $table = null
     * @param fourth parameter (overloading) params array - can handle db, select
     *
     * @return object|Item|mixed
     */
    public function get_item_by_field($field_name, $field_value, $table = null) {
        if (is_null($table))
            $table = $this->table;
        $params = @func_get_arg(3);
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        if (isset($params['select']))
            $db->select($params['select']);
        $db->from($table);
        $db->where(array($table . '.' . $field_name => $field_value));
//		$query = $this->db->get_where($table, array($table . '.' . $field_name => $field_value));
        $query = $db->get();
        return $query->custom_row_object(0, $this->item_class);
    }

    /**
     * @param string $field_name
     * @param string $field_value
     * @param string $table = null
     *
     * @return object[]|array|mixed
     */
    public function get_items_by_field($field_name, $field_value, $table = null) {
        if (is_null($table))
            $table = $this->table;
        $params = @func_get_arg(3);
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
        if (isset($params['select']))
            $db->select($params['select']);
        $query = $db->get_where($table, [$table . '.' . $field_name => $field_value]);
        if (isset($this->item_class))
            return $query->custom_result_object($this->item_class);
        return $query->result_object();
    }


    /**
     * used in create/edit-form
     *
     * @param array $excluded_ids
     *
     * @return array|object[]
     */
    public function get_values_for_select($excluded_ids = []) {
        $this->db->order_by('name');
        $items = $this->get_items2([$this->table => ['limit' => 0]], "id, name"); # @todo: performance leak!?
        $items2 = [];
        $items2[] = (object)["id" => 0, "name" => ""];
        foreach ($items as $item)
            if (!in_array($item->id, $excluded_ids))
//                $items2[] = (object)["id" => $item->id, "name" => $item->name];
                $items2[] = $item;
        return $items2;
    }

    /**
     * @param int|stdClass $item_or_id field for "id-field" in db/table | if is stdClass there have to be an id-property in the object
     * @param string|null  $table      for table-name if null it uses $this->table (this is your current model)
     *
     * @return bool|void
     */
    public function delete_item($item_or_id, $table = null, $params = []) {
        if ($this->read_only)
            return false;
        if (!is_numeric($item_or_id) && !is_object($item_or_id) && !is_array($item_or_id))
            return;
        if (!is_numeric($item_or_id) && isset($item_or_id->id))#id is item
            $id = $item_or_id->id;
        if (is_numeric($item_or_id))
            $id = $item_or_id;
        if (is_null($table))
            $table = $this->table;
        if (!@$params['no_history'] && @$this->use_history !== false)
            ci()->user_history_model()->check_changes($table, ['id' => $id], 'delete', null, $this->db->database);
        /** @var MY_DB_query_builder $db */
        $db = $this->db;
        $db->reset_query();
        if (@$id)
            $db->where('id', $id);
        else {
            $db->where((array)$item_or_id);
        }
        if ($params['return_delete_sql'] ?? false)
            $db->return_delete_sql = true;
        return $db->delete($table);
    }


    /**
     * Insert Row to database AND insert knot
     *
     * @param array  $item
     * @param string $table
     * @param bool   $return_bool
     *
     * @return array|bool array(result=>true|false, id=>int, knot=>)
     */
    public function insert_item($item = null, $table = null, $return_bool = true, $history = false) {
        if (is_object($item))
//            $data = (array)$data;
            # change to object to avoid accessing protected properties
            $data = clone $item;
        else
            $data = (object)$item;
        if ($this->read_only)
            return false;
        $this->load->helper('url');

        $table = (is_null($table)) ? $this->table : $table;
        $db = $this->db();
//        $slug = url_title($this->input->post('title'), 'dash', TRUE);
        if (is_null($data)) {
            $dataIn = $this->input->get_post("data");
            $data = @$dataIn[$table][0];
        }
        if ($data) {
            if (!(isset($this->inserts_preserve_ids) && $this->inserts_preserve_ids)) {
                if (is_array($data))
                    unset($data['id']);
                if (is_object($data))
                    unset($data->id);
            }
            $process_options = $this->_process_data_before_save($data, $table, $db);
            try {
                $returnDB = $db->insert($db->database . "." . $table, $data);
            } catch (Exception $e) {
                var_dump($e);
            }

            $error = (!@$returnDB) ? $db->error() : null;
            $new_id = $db->insert_id();
            if (is_object($item) && empty($item->id))
                $item->id = $new_id;
            elseif (is_array($item) && empty($item['id']))
                $item['id'] = $new_id;

            //link to Object
            $r_knot = $this->_link_to_object($new_id, $process_options);

            if ($history)
                ci()->user_history_model()->check_changes($table, $item, 'insert');

            if ($return_bool)
                return $returnDB;
            else
                return ["result" => $returnDB, "id" => $new_id, "knot" => @$r_knot[0], "error" => $error];
        }
        return false;
    }

    /**
     * @param null $data
     * @param null $table
     * @param bool $return_int = true - if false you get true or false if true an integer (or false)
     *
     * @return int|array|bool|mixed
     */
    public function insert_item2($data = null, $table = null, $return_int = true, $history = false) {
        $r = $this->insert_item($data, $table, false, $history);
        if (is_object($data) && !isset($data->id))
            $data->id = $r['id'];
        if (!$r['result'])
            return $r['result'];
        elseif ($return_int)
            return $r['id'];
        else
            return $r;
    }

    /**
     * @param array|object $item
     * @param string       $table
     * @param array        $where_map especially usefull for knots e.g. ['dogtag_id' => 123, 'object_type' => 'package', 'object_id' => 234]
     *
     * @return bool|array
     */
    public function update_item($item = null, $table = null, $where_map = null, $return_bool = true, $params = []) {
        # create array AND copy of item to allow unsetting of properties which should not be saved
        $data = (is_object($item)) ? clone $item : (array)$item;
//        $data = $item;
        $params = @func_get_arg(4);

        /** @var MY_DB_query_builder $db */
        $db = (isset($params['db'])) ? $params['db'] : $this->db;
        if ($this->read_only)
            return false;
        if (is_null($data)) {
            $data = $this->input->post($this->table);
        }
        if (is_null($table))
            $table = $this->table;
        $db->reset_query();

        if (
            (is_object($item) && in_array($item->modified, ["noupdate", "no-update"]))
            || (is_array($item) && in_array(@$item['modified'], ["noupdate", "no-update"]))
        ) {
            # unsetting is done later;
        } else {
            $db->set('modified', 'NOW()', FALSE);
            if (in_array("modifier", $this->db_fields))
                if (ci()->get_username())
                    $db->set("modifier", ci()->get_username());
        }

        if (!(($params['no_history'] ?? 0) || (($this->use_history ?? 0) === false)))
            ci()->user_history_model()->check_changes($this->table, $data, 'update');

        //TODO: Datentyp Timestamp als NULL speichern, wenn "" oder "0000-00-00 00:00:00"
        foreach ($data as $k => $v) { # protected properties will not be written
            $unset_current_property = false;
            if ($v === "noupdate" || $v === "no-update")
                $unset_current_property = true;
            if (in_array($v, ['now()', "NOW()", 'now', "NOW"], true)) {
                $db->set($k, 'NOW()', FALSE);
                $unset_current_property = true;
//                if (is_array($data)) unset($data[$k]);
//                if (is_object($data)) unset($data->$k);
            }
            if ($k === "id") {
                if (empty($v))
                    $unset_current_property = true;
            } elseif (empty($v) || $v == "0000-00-00 00:00:00") {
                $data_type = @$this->get_db_schema($k, $table)->DATA_TYPE;
                if (empty($data_type)) {
                    echo "<pre>\n Unknown Field:\n";
                    var_dump($k, $table, $this->db()->last_query(), $data, $data_type);
                    echo "</pre>";
                    die;
                }
                # if empty AND timestamp use null instead of date
                if (((empty($v) && !is_numeric($v)) || ($v == "0000-00-00 00:00:00")) && $data_type == "timestamp") {
                    $db->set($k, "NULL", false);
                    $unset_current_property = true;
//                    if (is_array($data))
//                        unset($data[$k]);
//                    if (is_object($data))
//                        unset($data->$k);
                }
            }
            if ($unset_current_property) {
                if (is_array($data)) unset($data[$k]);
                if (is_object($data)) unset($data->$k);
            }
        }

        if (!empty($where_map) && is_array($where_map)) {
            foreach ($where_map as $k => $v) {
                $db->where($k, $v);
                if (is_array($data)) unset($data[$k]);
                if (is_object($data)) unset($data->$k, $data->id);
            }
        } else {
            $id = (is_object($data)) ? $data->id : $data['id'];
            $db->where('id', $id);
        }
//        if (is_array($data)) unset($data['__MODEL'], $data['id']);
//        if (is_object($data)) unset($data->__MODEL, $data->id);

        $r = $db->update($table, $data);
        if ($return_bool == false) {
            $arr['success'] = $r;
            if ($r)
                $arr['affected_rows'] = $db->affected_rows();
            return $arr;
        }
        if (is_a($item, "Item"))
            $item->_updated = $r;
        return $r;
    }

    /**
     *
     * @param       $data
     * @param array $params valid keys: [db, table]
     *
     * @return array [
     *              'success' => (bool)...
     *              'affected_rows' => (int)...
     *               ]
     * @todo set creator and modifier
     *
     */
    public function replace($data, array $params = []): array {
        /** @var MY_DB_query_builder $db */
        $db = (@$params['db']) ?: $this->db();
        $table = (@$params['table']) ?: $this->table;
        $r = [];
        $process_options = $this->_process_data_before_save($data, $table, $db);
        $r['success'] = $db->replace($table, $data);
        $r['new_id'] = $db->insert_id(); # todo: test behavior of new_id
        if ($r['new_id'])
            $this->_link_to_object($r['new_id'], $process_options);
        $r['affected_rows'] = $db->affected_rows();
        if (@ci()->save_queries)
            $r['last_query'] = $this->last_query();
        return $r;
    }

    /**
     * Verarbeitet daten vor Insert/Replace so dass keine Fehler entstehen und die Daten-Qualitaet gewaehrleistet bleibt.
     *
     * @param array|object        $data
     * @param string              $table
     * @param MY_DB_query_builder $db
     *
     * @return array
     */
    public function _process_data_before_save(&$data, $table, &$db) {
        $table = (@$table) ?: $this->table;
        # wenn id frei, nehme Paket-Nummer bzw. Hotel-Nummer
        if (empty($data->id) && (isset($data->package_number) || isset($data->hotel_number))) {
            if ((strtoupper(substr(@$data->package_number, 0, 2)) == 'CW') && $data->brand != 'CW') {
                ci()->message("Prefix der Paketnummer und Brand passen nicht zusammen!", 'danger');
                echo ci()->show_ajax_message(ci()->data, true);
                die;
            }
            $number = (@$data->package_number) ? $data->package_number : ((@$data->hotel_number) ? $data->hotel_number : false);
            $number = (strtoupper(substr($number, 0, 2)) == 'CW') ? substr($number, 2) : $number;
            if ($table == "hotel")
                $prefix = "110";
            elseif ($table == "package" && $data->brand == "SMR")
                $prefix = "120";
            elseif ($table == "package" && $data->brand == "CW")
                $prefix = "130";
            else
                $prefix = '';
            $o = $db->query("SELECT id FROM $table where id = '" . addslashes($prefix . $number) . "'")->row_object();
            if (empty($o)) { # wenn id frei, nehme Paket-Nummer bzw. Hotel-Nummer
                $data->id = $prefix . $number;
            }
        }

        $dataForeignKeys = (is_object($data)) ? @$data->foreign_keys : @$data['foreign_keys'];
        if (is_object($data)) {
            if (!is_a($data, "Item")) {
                unset($data->foreign_keys);
                if ($table !== 'picture')
                    unset($data->__MODEL);#protected
            }
        } else {
            unset($data['foreign_keys']);
            if ($table !== 'picture')
                unset($data['__MODEL']);
        }

        //TODO: Datentyp Timestamp als NULL speichern, wenn "" oder "0000-00-00 00:00:00"
        foreach ($data as $k => $v) {
            if (empty($v) || $v == "0000-00-00 00:00:00") {
                $data_type = @$this->get_db_schema($k, $table)->DATA_TYPE;
                if ((empty($v) || $v == "0000-00-00 00:00:00") && $data_type == "timestamp") {
                    $db->set($k, "NULL", false);
                    if (is_object($data))
                        unset($data->$k);
                    else
                        unset($data[$k]);
                }
            }
            if (strstr($k, " ")) {
                $db->set('`' . addslashes($k) . '`', $v, false);
                if (is_object($data)) unset($data->$k); else unset($data[$k]);
            }
            if (is_string($v) && in_array($v, ['now', 'now()', 'NOW', 'NOW()'])) {
                $db->set($k, 'NOW()', FALSE);
                if (is_object($data))
                    unset($data->$k);
                else
                    unset($data[$k]);
            }
        }
        if ((is_object($data) && empty($data->created) || is_array($data) && empty($data['created'])))
            $db->set('created', 'NOW()', FALSE);

        $in_db_fields = function ($field) {
            return (!empty($this->db_fields) && in_array($field, $this->db_fields));
        };

        if (
            (is_object($data) && empty($data->modified) && $in_db_fields('modified'))
            || (is_array($data) && empty($data['modified']) && $in_db_fields('modified'))
        ) {
            $db->set('modified', 'NOW()', FALSE);
        }

        if (in_array("uuid", $this->db_fields) && !@$data->uuid)
            $db->set("uuid", 'UUID()', FALSE);
        if (in_array("creator", $this->db_fields) && !@$data->creator && ci()->get_username())
            $db->set("creator", ci()->get_username());
        if (in_array("modifier", $this->db_fields) && !@$data->modifier)
            $db->set("modifier", ci()->get_username());
        return ['dataForeignKeys' => $dataForeignKeys];
    }

    public function _link_to_object($new_id, $process_options) {
        //link to Object
        if ($process_options['dataForeignKeys']) {
            foreach ($process_options['dataForeignKeys'] as $k => $v) {
                $r_knot[] = $this->assign_to_object($new_id, $v, $k);
            }
        }
        return (isset($r_knot)) ? $r_knot : null;
    }

    /**
     * @return array of result bools
     * @deprecated 2016-09 - use save_items() now!
     *
     */
    public function save_item() {
        if ($this->read_only)
            return false;
//        get_instance()->message("Deprecated! ".__CLASS__.":".__METHOD__);
        return $this->save_items();
    }

    /**
     * saves data[<modelname>][id] data (from input) to database
     *
     * @param array|null $data
     * @param array|null $params (by overloading)
     *
     * @return array|bool unqualified indexed array (0,1,2,3) of save-results
     */
    public function save_items($data = null) {
        if ($this->read_only)
            return false;
        $data = (is_null($data)) ? $this->input->post_get('data') : $data;
        $dataIn1 = $data[$this->table];
        $params = @func_get_arg(1);
        foreach ($dataIn1 as $k => $data2) {
            $r[] = $this->save_one_item2($data2, null, $params);
        }
        return (@$r) ?: false;
    }

    /**
     * saves one item to database
     * you should overwrite this method in the model-class and use save_one_item2()
     *
     * @param $data
     *
     * @return bool
     * @deprecated since 2016 - use save_one_item2()
     *
     */
    public function save_one_item($data) {
//        get_instance()->message("Called: ".__CLASS__.":".__METHOD__);
        return $this->save_one_item1($data);
    }

    /**
     * @param $data
     *
     * @return bool
     * @deprecated 2016-07-25
     *
     * save data-array to database but does not handle foreign_keys
     *
     */
    public function save_one_item1($data) {
//        get_instance()->message("Called: ".__CLASS__.":".__METHOD__);
        if ($this->read_only)
            return false;
        if ((is_array($data) && $data['id'] > 0) || (is_object($data) && $data->id > 0)) {
            $r = $this->update_item($data);
        } else
            $r = $this->insert_item($data);
        return $r;
    }

    /**
     * saves data-array for one row to database
     * and save foreign-keys (data['foreign_keys'][<table>] => <id>)
     *
     * @param array|object $data   array for single item
     * @param string       $table
     *
     * @param array        $params ['force_insert',]
     *
     * @return array|bool [success|id]
     */
    public function save_one_item2($data, $table = null, $params = []) {
        $db = (isset($params['db'])) ? $params['db'] : $this->db();
//        if (is_object($data))
//            $data = (array)$data;
//        get_instance()->message("Called: ".__CLASS__.":".__METHOD__);
        if ($this->read_only)
            return false;
        if ((is_object($data) && !empty($data->foreign_keys))
            || (is_array($data) && @$data['foreign_keys'])
        ) {
            $foreign_keys = (is_object($data)) ? $data->foreign_keys : $data['foreign_keys'];
            if (is_object($data) && !is_a($data, "Item"))
                unset($data->foreign_keys);
            if (is_array($data))
                unset($data['foreign_keys']);#because it should not be saved directly
        }
//		MY_Controller::get_instance()->dump($foreign_keys, "foreign_keys");

        /* sb,2021 add: support for combined primary keys */
        if (isset($this->primary_key) && !empty($this->primary_key) && is_a($data, Item::class)) {
            $my_id = $data->_primary()->as_key();
            $r = $this->replace($data, array_merge($params, ['db' => $db, 'table' => $table]));
//            $r['success'] = $this->replace($data, array_merge($params, ['db' => $db, 'table' => $table]));
//            $r['affected_rows'] = $db->affected_rows();
//            $r['id'] = $my_id;
            $r[$this->table][$my_id]['type'] = 'replace';
        } elseif (@$params['force_insert'] !== true
            && ((is_object($data) && $data->id > 0)
                || (is_array($data) && @$data['id'] > 0)
            )
        ) { #update existing item
            $my_id = (is_object($data)) ? $data->id : $data['id'];

            $r['success'] = $this->update_item($data, $table, [], '', $params);
            $r['affected_rows'] = $db->affected_rows();
            $r['id'] = $my_id;
            $r[$this->table][$my_id]['type'] = 'update';
        } else { # insert new item
            if (@$params['force_insert'] === true) {
                $this->inserts_preserve_ids = true;
            }
            $r['success'] = $this->insert_item($data, $table);
            if (@$params['force_insert'] === true) {
                $this->inserts_preserve_ids = false;
            }
            $r['affected_rows'] = $db->affected_rows();
            if ($r['success']) {
                $my_id = $r['id'] = $db->insert_id();
                $r[$this->table][$my_id]['type'] = 'insert';
            }
        }
        if ($my_id) {
            $r[$this->table][$my_id]['success'] = $r['success'];
            if (isset($foreign_keys)) {
                foreach ($foreign_keys as $object_type => $object_id) {
                    if (method_exists($this, 'assign_to_object')) {
                        $knot_result = $this->assign_to_object($my_id, $object_id, $object_type);
                        $knot_item = $knot_result['knot_item'];
                        $r[$this->table][$my_id]['knot'][$object_type] = $knot_item;
                        $r[$this->table][$my_id]['foreign_keys'][$object_type] = $object_id;
                    } else
                        ci()->message(get_class($this) . ":" . 'assign_to_object' . "-Method is missing.");
                }
            }
        }
        $r['last_query'] = $this->last_query();
        return $r;
    }
}