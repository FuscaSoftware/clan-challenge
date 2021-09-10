<?php
/**
 * User: sbraun
 * Date: 11.01.18
 * Time: 12:12
 */
trait MY_Model_Knot_Trait
{

    /**
     * Returns array of item(bean)-objects by given object and expected object_type
     * e.g. you have a package with id 5 and you want to get all contents
     *
     * @deprecated since 2017-08-24 use get_items_by_knot2!
     *
     * @param int                      $id
     * @param string                   $object_type
     * @param string|null              $expected_object_type (if null it takes the one of the current model)
     * @param string|null              $select               (if null: * is used in select)
     * @param Knot_model|object|null   $knot_model           object instance of knot_model (null is recomended)
     * @param CI_DB_query_builder|null $db                   object instance of $this->db
     * @param int                      $limit_num
     * @param int                      $limit_from
     *
     * @return array of object items
     */
    public function get_items_by_knot($id, $object_type, $expected_object_type = null, $select = null, $knot_model = null, &$db = null, $limit_num = 0, $limit_from = 0) {
        $q1 = $this->query_get_items_by_knot($id, $object_type, $expected_object_type, $select, $knot_model, $db, $limit_num, $limit_from);
        if (!empty($q1)) {
            $result = $db->query($q1);
            if ($result === false) { # debug
                print_r($db->last_query());
                die();
            }
            $items = $result->custom_result_object($this->item_class);
        } else
            return $q1;
        return $items;
    }

    public function get_items_by_knot2($id, $object_type, $params = []) {
        # defaults
        $expected_object_type = null; $select = null; $knot_model = null; $db = null; $limit_num = 0; $limit_from = 0;
        $where = [];
        $order_by = [];
        extract($params);
        if ($where) {
            $db = (@$db) ? $db : $this->db();
            $db->where($where);
        }
        if ($order_by) {
            $db = (@$db) ? $db : $this->db();
            $db->order_by($order_by);
        }

        $q1 = $this->query_get_items_by_knot($id, $object_type, $expected_object_type, $select, $knot_model, $db, $limit_num, $limit_from);
        if (!empty($q1))
            $items = new DynCollection($db->query($q1), $this->item_class);
        else
            return $q1;
        return $items;
    }

    public function query_get_items_by_knot($id, $object_type, $expected_object_type = null, $select = null, $knot_model = null, &$db = null, $limit_num = 0, $limit_from = 0): string {
        $return_type = "query";
        if (!is_numeric($id))
            return "";
        if (is_null($expected_object_type))
            $expected_object_type = $this->table;
//        ci()->dump([$object_type, $expected_object_type]);
        if (is_null($db)) {
            $db = $this->db();
            $db->reset_query();
            $db->distinct();
        }
//        print_r($db->database."\n");
        if (is_null($knot_model) && ($object_type == "picture" || $expected_object_type == "picture" || ($object_type === "old_picture" || $expected_object_type === "old_picture"))) {
            $knot_model = ci()->picture_knot_model();
        } elseif (is_null($knot_model) && $object_type == "dogtag") {
            $knot_model = ci()->dogtag_knot_model();
        } elseif (is_null($knot_model)) {
            $knot_model = ci()->knot_model();
        }
        if (is_null($select)) {
            $select = $this->table . ".*";
            # 2017-01-02 try:
//            $db->select($expected_object_type.".id as ".$expected_object_type."_id");
            $db->select("'$expected_object_type' as __MODEL");
        }
        $db->select($select);
        if ($this->table != $knot_model->table)
            $db->from($db->database . "." . $this->table);
        if (is_a($knot_model, "Dogtag_knot_model")) {
            $db->select("dogtag_knots.weight * global_score as score", false);
            $db->select("dogtag_knots.required");
            $db->select("dogtag_knots.page_linked");
            if ($this->table != "dogtag")
                $db->from($db->database . "." . "dogtag");
            $db->where("dogtag.id = dogtag_knots.dogtag_id");
        } elseif ($expected_object_type == "content") {
            $db->select("object_object_knots.page_linked");
            $db->select("content_type_sort");
            $db->order_by("content_type_sort", "asc");
            $db->select("object_object_knots.sort");
            $db->order_by("object_object_knots.sort", "asc");
            $db->order_by("content.title", "asc");
        } elseif ($object_type === "picture" || $expected_object_type === "picture" || ($object_type === "old_picture" || $expected_object_type === "old_picture")) {
            $db->select(ci()->picture_knot_model()->table . ".sort");
            $db->order_by(ci()->picture_knot_model()->table . ".sort");
        } else {
            $db->select("object_object_knots.sort");
            $db->order_by("object_object_knots.sort");
        }
        if ($limit_num > 0 && isset($limit_from)) {
            $db->limit($limit_num, $limit_from);
        }

        $knot_array = array($object_type => $id, $expected_object_type => 0);
//		var_dump($knot_array);echo "<br>";
        if ($knot_model->is_valid_array($knot_array)) {
            $knot_model->query_build_join_where($db, $knot_array);
            if ($return_type == "items") {
                $query = $db->get();
                if (isset($this->item_class))
                    return $query->custom_result_object($this->item_class);
                return $query->result_object();
            } elseif ($return_type == "query") {
                return $db->get_compiled_select();
            }
        }
    }

    /**
     * Returns one item(bean)-object by given object and expected object_type
     * e.g. you have a package with id 5 and you want to get all contents
     *
     * @param array           $knot_array
     * @param string|null     $select     (if null: $this->table.* is used in select, if: false "object_object_knots.*" is used)
     * @param Knot_model|null $knot_model object instance of knot_model (null is recomended)
     * @param object|null     $db         object instance of $this->db
     *
     * @return object item
     * @throws Exception
     */
    public function get_item_by_knot_array(array $knot_array, $select = null, $knot_model = null, &$db = null) {
//		if ( !is_numeric($id) )
//			return;
//		if ( is_null($expected_object_type) )
//			$expected_object_type = $this->table;
        if (is_null($knot_model)) {
//            $this->loader()->model("cms/knot_model");
            if (array_key_exists("picture", $knot_array) || array_key_exists("old_picture", $knot_array))
                $knot_model = ci()->picture_knot_model();
            else
                $knot_model = ci()->knot_model();
        }
        $item_class = $this->item_class;
        if ($select === false) { # only fields from knot-table
            $select = $knot_model->table . ".*";
            $item_class = $knot_model->item_class;
        } elseif (is_null($select)) # only fields from object-table
            $select = $this->table . ".*,";
        if (is_null($db)) {
            $db = $this->db();
            $db->reset_query();
            $db->distinct();
        }
        $db->select($select);
        $db->from($this->table);
//		$knot_array = array($object_type => $id, $expected_object_type => $expected_object_id);
//		var_dump($knot_array);echo "<br>";
        if ($knot_model->is_valid_array($knot_array)) {
            if (is_a($knot_model, "Knot_model"))
                $knot_model->query_build_join_where($db, $knot_array, $this->table);
            elseif (is_a($knot_model, "Dogtag_knot_model")) {
                /** @var Dogtag_knot_model $knot_model */
                $knot_model->query_build_join_where($db, $knot_array, $this->table);
            }
            $query = $db->get();
            return $query->custom_row_object(0, $item_class);
        }
    }


    /**
     * @deprecated since 2016-12 because of too-similar name to the codeigniter loader class; please use get_related()
     * Loads linked objects to objects so you can access the knots
     *
     * @param string $expected_object_type eg "picture","content" if item has $item->_MODEL property it will use this for loading knots
     * @param array  $items
     *
     * @throws Exception
     */
    public function load($expected_object_type, &$items) {
        return $this->get_related($expected_object_type, $items);
    }

    /**
     * Loads linked objects to objects so you can access the knots
     * There is no return because the found items are saved in the items-reference
     *
     * @param string              $expected_object_type eg "picture","content" if item has $item->_MODEL property it will use this for loading knots
     * @param array|DynCollection $items
     * @param int                 $limit_num
     * @param int                 $limit_from
     */
    public function get_related(string $expected_object_type, &$items, $limit_num = 0, $limit_from = 0) {
        if (!is_array($items) && !is_a($items, "DynCollection"))
            die ("\$items is of wrong type! " . __CLASS__ . ":" . __METHOD__);
        if (!isset($this->{$expected_object_type . "_model"})) {
            $this->loader()->model("cms/" . $expected_object_type . "_model");
//			throw new RuntimeException($expected_object_type . "_model" . " is not available in " . get_class($this) . " or " . get_class(get_instance()));
        }
        if (strtolower(get_class($this)) == strtolower($expected_object_type . "_model"))
            die ("Achtung Selbstverlinkung nicht möglich!\n" . var_export(__METHOD__, 1));
        foreach ($items as $k => $item) {
            if ($item->id > 0 && (!is_array(@$items[$k]->{$expected_object_type}) && !is_a(@$items[$k]->{$expected_object_type}, "Collection"))) {
                if ($expected_object_type == "gallery") # only for code tracing
                    $expected_object_type_model = ci()->gallery_model();
                else
                    $expected_object_type_model = ci()->any_model($expected_object_type);
                $db = $expected_object_type_model->db();
                if (isset($item->__MODEL)) {
//                    $items2 = $this->{$expected_object_type . "_model"}->get_items_by_knot($item->id, $item->__MODEL, $expected_object_type);
                    $items2 = $expected_object_type_model->get_items_by_knot($item->id, $item->__MODEL, $expected_object_type, null, null, $db, $limit_num, $limit_from);
                } else {
//                    $items2 = $this->{$expected_object_type . "_model"}->get_items_by_knot($item->id, $this->table, $expected_object_type);
                    $items2 = $expected_object_type_model->get_items_by_knot($item->id, $this->table, $expected_object_type, null, null, $db, $limit_num, $limit_from);
                }
//			if (count($items2) && !isset($item->$expected_object_type))
                @$items[$k]->{$expected_object_type} = [];
                foreach ($items2 as $item2) {
                    @$items[$k]->{$expected_object_type}[$item2->id] = $item2;
                }
            } #else { var_dump($item->id);}
        }
    }

    /**
     * Loads linked objects to objects so you can access the knots
     * There is no return because the found items are saved in the items-reference
     * ! Have to be called from the model of which the items are !
     *
     * @param string              $expected_object_type eg "picture","content" if item has $item->_MODEL property it will use this for loading knots
     * @param array|DynCollection $items
     *
     * @throws Exception
     */
    public function get_related2(string $expected_object_type, &$items) {
        if (!is_array($items) && !is_a($items, "DynCollection"))
            throw new Exception("\$items is of wrong type! " . __CLASS__ . ":" . __METHOD__);
        if (!isset($this->{$expected_object_type . "_model"})) {
            $this->loader()->model("cms/" . $expected_object_type . "_model");
//			throw new RuntimeException($expected_object_type . "_model" . " is not available in " . get_class($this) . " or " . get_class(get_instance()));
        }
        if (strtolower(get_class($this)) == strtolower($expected_object_type . "_model"))
            throw new Exception ("Achtung Selbstverlinkung nicht möglich!");
        foreach ($items as $k => $item) {
            if ($item->id > 0 && (!is_array(@$items[$k]->{$expected_object_type}) && !is_a(@$items[$k]->{$expected_object_type}, "Collection"))) {
                if ($expected_object_type == "gallery") # only for code tracing
                    $expected_object_type_model = ci()->gallery_model();
                else
                    $expected_object_type_model = ci()->any_model($expected_object_type);
                $db = $expected_object_type_model->db();
                $params = [
                    'db' => $db,
                    'join_to_type' => true,
                ];
                if (isset($item->__MODEL)) {
                    $items2 = $expected_object_type_model->get_items_by_knot2($item->id, $item->__MODEL, $params);
                } else {
                    $items2 = $expected_object_type_model->get_items_by_knot2($item->id, $this->table, $params);
                }
                @$items[$k]->{$expected_object_type} = $items2;
            } #else { var_dump($item->id);}
        }
    }

    /**
     * @param int    $my_id       first-object-id
     * @param int    $object_id   id of second-object
     * @param string $object_type type of second-object
     *
     * @return mixed return of 'insert_knot()'-Methods
     */
    public function assign_to_object($my_id, $object_id, $object_type) {
//	    var_dump(array(
//	        $my_id,
//            $object_id,
//            "ot" => $object_type,
//            $this->linked_by_knot,
//            in_array($object_type, array_values($this->linked_by_knot)),
//        ));
        if ($object_type == "dogtag") {
            $this->load->model("cms/dogtag_knot_model");
            return $this->dogtag_knot_model->insert_knot(array($this->table => $my_id, $object_type => $object_id));
        } elseif (in_array($object_type, $this->linked_by_knot)) {
            $this->load->model("cms/knot_model");
            $values = [];
            $map = [$this->table => $my_id, "$object_type" => $object_id];
            if (in_array($object_type, ['picture', 'old_picture'])
                || is_a($this, "Picture_model")
                || is_a($this, 'Old_picture_model')
                || is_a($this, 'Dms_picture_model')
            ) {
                $knot_model = ci()->picture_knot_model();
                $values['sort'] = 'last';
            } else
                $knot_model = ci()->knot_model();
            return $knot_model->insert_knot($map, $values);
        } else
            MY_Controller::get_instance()->dump("Fehler: " . __CLASS__ . ":" . __METHOD__ . " Verknüpfung von $this->table zu $object_type nicht erlaubt!");
//			throw new Exception(__CLASS__.":".__METHOD__);
    }


    /**
     * Only to delete content-element-knots
     *
     * @param array               $map
     * @param string|null         $table
     * @param MY_DB_query_builder $db
     *
     * @return bool
     */
    public function delete_knot(array $map, string $table = null, $db = null): bool {
        if (!is_array($map))
            return false;
        if (count($map) != 2 && (count(array_keys($map)) == count(array_values($map))))
            return false;
        if (is_null($table))
            $table = $this->table;
        if (is_null($db))
            $db = $this->db();
        ksort($map);
        $keys = array_keys($map);
        $vals = array_values($map);
        if (($keys[0] == "content" && $keys[1] == "element") ||
            ($keys[0] == "content_id" && $keys[1] == "element_id")
        ) {
//            $this->db()->reset_query();
            $db->where('content_id', $vals[0]);
            $db->where('element_id', $vals[1]);
            return $db->delete($table);
        } elseif (($keys[0] == "dogtag" && $table == "dogtag_knots") ||
            ($keys[0] == "dogtag_id" && $table == "dogtag_knots")
        ) {
            $db->where('dogtag_id', $vals[0]);
            $db->where('object_type', $keys[1]);
            $db->where('object_id', $vals[1]);
            return $db->delete($table);
        } else {
            $db->where('object1_id', $vals[0]);
            $db->where('object1_type', $keys[0]);
            $db->where('object2_id', $vals[1]);
            $db->where('object2_type', $keys[1]);
            return $db->delete($table);
        }

    }

    /**
     * @param array $map
     *
     * @return Content_element_knots_model|Dogtag_knot_model|Picture_knot_model|Knot_model
     */
    public function get_knot_model(array $map) {
        ksort($map);
        list($object1_type, $object2_type) = array_keys($map);
        if ($object1_type == "dogtag" || $object2_type == "dogtag") {
            $knot_model = ci()->dogtag_knot_model();
        } elseif ($object1_type == "content" && $object2_type == "element") {
            $knot_model = ci()->content_element_knots_model();
        } elseif (in_array($object1_type,["picture", "old_picture"]) || in_array($object2_type, ["picture", "old_picture"])) {
            $knot_model = ci()->picture_knot_model();
        } elseif (ci()->knot_model()->is_valid_array($map)) {
            $knot_model = ci()->knot_model();
        }
        if (isset($knot_model))
            return $knot_model;
    }
}