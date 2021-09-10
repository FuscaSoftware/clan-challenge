<?php

MY_Controller::get_instance()->load->library("fusca/super_lib");

/**
 * Description of Bootstrap_lib
 *
 * @author sebra
 */
class Bootstrap_lib extends Super_lib
{

    /**
     * Alias
     * @deprecated 2016-07
     * @param Bootstrap_Message_Object $message
     * @return string (html-element)
     */
    public function showMessage($message) {
        return $this->show_message($message);
    }

    /**
     * Alias
     * @deprecated 2016-07
     * @param array $messages
     * @param string $context
     * @return string (html-element)
     */
    public function showMessages($messages = null, $context = "default") {
        return $this->show_messages($messages, $context);
    }

    /**
     * @param $message Bootstrap_Message_Object
     * @return string (html-element)
     */
    public function show_message($message) {
        $id = (@$message->id) ? $message->id : 'message_' . sha1(serialize($message) . microtime(false));
        $openTag = '<div id="' . $id . '" class="alert alert-' . $message->type . ' alert-dismissible" role="alert" data-time_out_ms="' . $message->time_out_ms . '">';
        $closeButton = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        $messageTitle = (@$message->title) ? '<strong>' . $message->title . '</strong> ' : "";
        $messageScript = ($message->time_out_ms) ? '<script>$(function(){window.setTimeout(function(){$("#' . $id . '").fadeOut(1500, function(){$("#' . $id . '").remove();});}, $("#' . $id . '").data("time_out_ms"));});</script>' : "";
        $closeTag = '</div>';
        return $openTag . $closeButton . $messageTitle . $message->text . $messageScript . $closeTag;
    }

    /**
     * @param array|null $messages
     * @param string|null $context [optional]
     * @return string (html-element)
     */
    public function show_messages($messages = null, $context = null) {
        $html =[];
        if (is_null($messages))
            $messages = [];
        if (is_null($context))
            $context = "default";
//		var_dump($GLOBALS['messages']);
        if (isset($GLOBALS['messages'])) {
            $messages = array_merge($messages, $GLOBALS['messages']);
        }
        if (isset($_SESSION['messages'])) {
            $messages = array_merge($messages, $_SESSION['messages']);
            unset($_SESSION['messages']);
        }
        if (isset($messages[$context])) {
            foreach ($messages[$context] as $message) {
                $html[] = $this->show_message($message);
            }
        }
        return implode("\n", $html);
    }

    public function show_validation_errors($prefix = '', $suffix = '') {
        if (FALSE === ($OBJ = &_get_validation_object())) {
            return '';
        }
        $text = $OBJ->error_string($prefix, $suffix);
        if (!empty($text)) {
            $message = (object)array("text" => $text, "type" => "danger");
            return $this->show_message($message);
        }
    }

    /**
     * @deprecated 2016-06 use show_filed2 (Hint: it's static!)
     * @param string $field
     * @param array $fieldInfo ['type' => checkbox|textarea|hidden|text]
     * @param string $modelname
     * @param string $value
     * @param string $class
     * @return string (html-element)
     */
    public function show_field($field, $fieldInfo, $modelname, $value, $class = "") {
        $html = [];
        switch ($fieldInfo['type']) {
            case "checkbox":
                $data['name'] = "data[" . $modelname . "][" . $field . "]";
                $data['content'] = $value;
                $data['value'] = $value;
                $html[] = $this->controller->get_view("boxes/checkbox", $data);
                break;
            case "textarea":
                $data['name'] = "data[" . $modelname . "][" . $field . "]";
                $data['content'] = $value;
                $data['value'] = $value;
                $html[] = $this->controller->get_view("boxes/textarea", $data);
                break;
            case "hidden":
                $data['type'] = "hidden";
            case "text":
                $data['type'] = "text";
            default:
                $data['name'] = "data[" . $modelname . "][" . $field . "]";
                $data['value'] = $value;
                $data['class'] = $class;
                $html[] = $this->controller->get_view("boxes/input", $data);
                break;
        }
        return implode("", $html);
    }

    /**
     * @param array $attributes
     * [ fieldinfo => [type] ]
     * possible keys: name,class,content,disabled,value,onchange,label_text_before,label_text_after,rows,placeholder,type
     * @return string html
     */
    public static function show_field2(array $attributes) {
        $html = [];
        $data['id'] = @$attributes['id'];
        $data['onchange'] = @$attributes['onchange'];
        switch (@$attributes['field_info']['type']) {
            case "link":
                $html[] = '<a target="_blank" href="' . $attributes['value'] . '">'. $attributes['value'] . '</a>';
                break;
            case "custom":
                $html[] = ci()->get_view($attributes['view']);
                break;
            case "tinyint":
            case "checkbox":
                $data['name'] = $attributes['name'];
                $data['class'] = @$attributes['class'];
                $data['device'] = @$attributes['device'];
                $data['content'] = $attributes['value'];
                $data['value'] = $attributes['value'];
//                $data['onchange'] = @$attributes['onchange'];
                $data['label_text_before'] = @$attributes['label_text_before'];
                $data['label_text_after'] = @$attributes['label_text_after'];
                $html[] = ci()->get_view("boxes/checkbox", $data);
                break;
            case "select":
            case "enum":
                if (!@$attributes["items"])
                    $attributes["items"] = $attributes['field_info']['values'];
                $html[] = self::show_select($attributes, null, @$attributes['fieldname']);
                break;
            case "text":
            case "mediumtext":
            case "longtext":
            case "textarea":
                $data['name'] = $attributes['name'];
                $data['content'] = $attributes['value'];
                $data['value'] = $attributes['value'];
                $data['class'] = @$attributes['class'];
                $data['rows'] = @$attributes['rows'];
                $data['placeholder'] = @$attributes['placeholder'];
                $data['onkeyup'] = @$attributes['onkeyup'];
                $data['readonly'] = @$attributes['field_info']['readonly'];
                $html[] = ci()->get_view("boxes/textarea", $data);
                break;
            case "timestamp":
                $data['name'] = $attributes['name'];
                $data['value'] = $attributes['value'];
                $data['class'] = @$attributes['class'];
                $html[] = ci()->get_view("boxes/timestamp", $data);
                break;
            case "hidden":
                $data['type'] = "hidden";
            case "varchar":
            case "input":
                $data['type'] = (@$data['type']) ? $data['type'] : "text";
                $data['class'] = @$attributes['class'];
                $data['placeholder'] = @$attributes['placeholder'];
                $data['onkeyup'] = @$attributes['onkeyup'];
            default:
                $data['name'] = $attributes['name'];
                $data['value'] = $attributes['value'];
                $data['class'] = @$attributes['class'];
                $data['placeholder'] = @$attributes['placeholder'];
                $html[] = ci()->get_view("boxes/input", $data);
                break;
        }
        return implode("", $html);
    }

    /**
     *
     * @param array $attributes array(
     *                                items => array()
     *                                name => string
     *                                )
     * @param array $items array of item objects
     * @param string $fieldname key to get label out of the item-object
     * @return string
     */
    public static function show_select($attributes, $items = [], $fieldname = "name") {
        $data['items'] = ($items) ? $items : $attributes['items'];
        $data['fieldname'] = ($fieldname) ? $fieldname : @$attributes['fieldname'];
        $data['class'] = (@$attributes['class']) ? $attributes['class'] : "";
        $data['onchange'] = @$attributes['onchange'];
        $data = array_merge($attributes, $data);
        $data['disabled'] = (@$data['disabled']) ? true : false;
        $output[] = ci()->get_view("boxes/select", $data);
        return implode("", $output);
    }

    public static function show_button($attributes, $items = [], $fieldname = "name") {
        $data['items'] = ($items) ? $items : @$attributes['items'];
        $data['item'] = (@$item) ? $item : @$attributes['item'];
        $data['icon'] = (@$icon) ? $icon : @$attributes['icon'];
        $data['fieldname'] = ($fieldname) ? $fieldname : @$attributes['fieldname'];
        $data['class'] = (@$attributes['class']) ? $attributes['class'] : "";
        $data['onchange'] = @$attributes['onchange'];
        $data = array_merge($attributes, $data);
        $data['disabled'] = (@$data['disabled']) ? true : false;
        $output[] = ci()->get_view("boxes/button", $data);
        return implode("", $output);
    }

    public function show_actions() {

    }

//	public function show_elements($element_items, $element_types = null) {
//		$output = array();
////		if ( empty($element_items) ) {
////			$element_items[] = (object) array("id" => 0, "text" => "");
////		}
//		foreach ( $element_items as $element ) {
//			$data['item'] = $item = $element;
//            $data['input_id'] = $item_id = "data_".$this->CI->element_model->table."_".$item->id."_text";
//            $data['box_element'] = array(
////                'dom_class' => "well well-sm cms_element_item element_id_$item->id ui-state-default",
//                'dom_class' => "well well-sm cms_element_item element_id_$item->id ",
//                'dom_id' => 'element_id_'.$item->id,
//                'dom_title' => "Element ID: $item->id",
//                'dom_data' => array('input_id' => $item_id),
//            );
//			if ( in_array(strtolower($element->element_type_name), array("text", "kommentar")) )
//				$output[] = $this->controller->get_view("cms/element/element_text_view", $data);
//			if ( in_array(strtolower($element->element_type_name), array("zwischenüberschrift")) )
//				$output[] = $this->controller->get_view("cms/element/element_input_view", $data);
//			if ( in_array(strtolower($element->element_type_name), array("bild")) )
//				$output[] = $this->controller->get_view("cms/element/element_picture_view", $data);
//            if ( in_array(strtolower($element->element_type_name), array("altes bild")) )
//                $output[] = $this->controller->get_view("cms/element/element_old_picture_view", $data);
//			if ( in_array(strtolower($element->element_type_name), array("bildergalerie")) )
//				$output[] = $this->controller->get_view("cms/element/element_gallery_view", $data);
//			if ( in_array(strtolower($element->element_type_name), array("text (ckeditor)")) )
//				$output[] = $this->controller->get_view("cms/element/cke1_text_view", $data);
//            if ( in_array(strtolower($element->element_type_name), array("video-modal")) )
//                $output[] = $this->controller->get_view("cms/element/element_video-modal_view", $data);
//		}
//		return implode("", $output);
//	}
//
//	public function show_add_element($content_id, $element_types) {
//		$data['element_types'] = $element_types;
//		$data['content_id'] = $content_id;
//		$output[] = $this->controller->get_view("cms/add_element", $data);
//		return implode("", $output);
//	}

    public function show_save($attributes = null) {
        //$data = $attributes['data'];
        $data = $attributes;

        $output[] = $this->controller->get_view("cms/save", $data);
        return implode("", $output);
    }

    public function show_fake_form_open($action = '', $attributes = array(), $hidden = array()) {
        return "<div data-action=\"" . $this->url($action) . "\" " . _attributes_to_string($attributes) . ">";
    }

    public function show_fake_form_close($extra = '') {
        return "</div>";
    }

    public function url($action) {
        return get_instance()->config->site_url($action);
    }

    /**
     *
     * @param int|object $id_or_item
     * @param array $data
     * @param MY_Model $model
     * @param array $params
     * @return string
     */
    public function edit_box($id_or_item, &$data, &$model, $params = array()) {
//		$controllername = ci()->get_full_controller_name();//TODO
        $controllername = $model->full_controller_name; //TODO

        if (is_object($id_or_item)) {
            $item = $id_or_item;
            $id = $item->id;
            $foreign_keys = @$item->foreign_keys;
        } else {
            $foreign_keys = $this->input->post_get("foreign_keys");
            $id = $id_or_item;
            if (!$id || $id == 0) {
                $item = $model->get_empty_item();
                $item->id = 0;
                $this->controller->controller_helper_lib->assign_foreign_keys_to_item($item, $this, $foreign_keys);
            } elseif (is_numeric($id)) {
                $item = $model->get_item_by_id($id);
                $this->controller->controller_helper_lib->assign_foreign_keys_to_item($item, $this, $foreign_keys);
            } else {
                ci()->message("$model->item_label mit ID: $id ist ungültig.");
            }
        }

        if (!$item) {
            ci()->message("$model->item_label mit ID: $id nicht gefunden.");
        }
        $data['box_edit']['item'] = $item;

        $data['box_edit']['title'] = (isset($params['title'])) ? $params['title'] : "$model->item_label anlegen";
        $data['box_edit']['form']['target'] = "$controllername/save";
        $data['box_edit']['form']['backend_fields'] = $model->backend_fields;


        foreach ($data['box_edit']['form']['backend_fields'] as $field_name => $fieldInfo) {
            if (!empty($data['box_edit']['form']['backend_fields'][$field_name]['values'])) {
                foreach ($data['box_edit']['form']['backend_fields'][$field_name]['values'] as $k => $value) {
                    #var_dump($value);
                    /* Selektiere Pulldown-Menüs nach nicht verwendbaren Typen */
                    if (!empty($value->context) && !empty($foreign_keys))
                        if (stristr($value->context, key($foreign_keys)) === FALSE)
                            unset($data['box_edit']['form']['backend_fields'][$field_name]['values'][$k]);
                }
            }
            /* create case */
            if ($item->id == 0 && @$data['box_edit']['form']['backend_fields'][$field_name]['hide_for_create'])
                unset($data['box_edit']['form']['backend_fields'][$field_name]);
            /* edit case */
            elseif ($item->id > 0 && @$data['box_edit']['form']['backend_fields'][$field_name]['hide_for_edit'])
                unset($data['box_edit']['form']['backend_fields'][$field_name]);
        }

        $data['box_edit']['itemtitle'] = isset($model->item_label) ? $model->item_label : "Inhalt";
        $data['box_edit']['modelname'] = $model->table;
        $data['box_edit']['controllername'] = $model->full_controller_name;
        return ci()->get_view("boxes/edit", $data);
    }


    /**
     * @param Item $item
     * @param MY_Model $model
     * @param array $list_fields_2D
     * @param string $modelname
     * @param string $controllername
     * @param array $foreign_keys
     * @param int $active_id
     * @param string $list_row_view
     * @param array $box_index other params like $box_index['hide_link_button']
     * @return mixed
     */
    public function show_item_row($item, $model, $list_fields_2D, $modelname, $controllername, $foreign_keys = array(), $active_id = 0, $list_row_view = "cms/list_row_view", $box_index = array()) {
        $items = [$item];
        if (is_array($foreign_keys))
            foreach ($foreign_keys as $k => $v)
                $model->get_related($k, $items);

        $data['box_index'] = array(
            "modelname" => $modelname,
            "controllername" => $controllername,
            "active_id" => $active_id,
            "item" => $item
        );
        if (!empty($box_index))
            $data['box_index'] = array_merge($box_index, $data['box_index']);
//		$data['box_index']['foreign_keys'][$object_type] = $object_id;
        $data['box_index']['foreign_keys'] = $foreign_keys;
//		$data['box_index']['list_fields_2D'] = $data['list_fields_2D'] = $list_fields_2D = $model->link_list_fields_2D;
//		$data['list_fields'] = $model->list_fields;
        $data['box_index']['list_fields_2D'] = $data['list_fields_2D'] = $list_fields_2D = $list_fields_2D;
        $data['box_index']['list_fields'] = $data['list_fields'] = $list_fields = array_keys($list_fields_2D);
        $data['box_index']['active_id'] = $active_id;
        $data['box_index']['hide_link_button'] = @$box_index['hide_link_button'];
        $data['item'] = $item;
        $data['action_view'] = "cms/$modelname/actions_view";
        if (is_null($list_row_view))
            $list_row_view = "cms/list_row_view";
        return ci()->get_view($list_row_view, $data);
    }

    /**
     * @param string $object_type
     * @return string css-class for fa eg fa-bed
     */
    public static function get_object_icon(string $object_type): string {
        if ($object_type == "hotel")
            $item_icon = 'fa-bed';
        elseif ($object_type == "location")
            $item_icon = 'fa-map-marker';
        elseif ($object_type == "package")
            $item_icon = 'fa-shopping-bag';
        elseif ($object_type == "content")
            $item_icon = 'fa-newspaper-o';
        elseif ($object_type == "dogtag")
            $item_icon = 'fa-tag';
        elseif ($object_type == "producer")
            $item_icon = 'fa-child';
        elseif ($object_type == "topic")
            $item_icon = 'fa-sitemap';
        elseif ($object_type == "gallery")
            $item_icon = 'fa-picture-o';
        elseif ($object_type == "staticpage")
            $item_icon = 'fa-file-text';
        elseif ($object_type == "element")
            $item_icon = 'fa-file-text-o';
        elseif ($object_type == "redirect")
            $item_icon = 'fa-long-arrow-right';
        elseif ($object_type == "picture")
            $item_icon = 'fa-camera';
        else if ($object_type == 'notification') {
            $item_icon = 'fa-bell';
        }
        else
            $item_icon = '';
        return $item_icon;
    }

    public static function get_element_type_icon(int $element_type_id) {
        if ($element_type_id == 1) {
            $icon = "fa-align-left";
        } else if ($element_type_id == 2) {
            $icon = "fa-comment";
        } else if ($element_type_id == 3) {
            $icon = "fa-text-height";
        } else if ($element_type_id == 4) {
            $icon = "fa-camera";
        } else if ($element_type_id == 5) {
            $icon = "fa-table";
        } else if ($element_type_id == 6) {
            $icon = "fa-image";
        } else if ($element_type_id == 7) {
            $icon = "fa-camera-retro";
        } else if ($element_type_id == 10) {
            $icon = "fa-video-camera";
        } else if ($element_type_id == 11) {
            $icon = "fa-video-camera";
        } else if ($element_type_id == 12) {
            $icon = "fa-eur";
        } else if ($element_type_id == 13) {
            $icon = "fa-eur";
        } else if ($element_type_id == 14) {
            $icon = "fa-link";
        } else if ($element_type_id == 15) {
            $icon = "fa-male";
        } else if ($element_type_id == 16) {
            $icon = "fa-moon-o";
        } else if ($element_type_id == 17) {
            $icon = "fa-battery-full";
        } else if ($element_type_id == 18) {
            $icon = "fa-battery-2";
        } else if ($element_type_id == 19 || $element_type_id == 25) {
            $icon = "fa-text-width";
        } else if ($element_type_id == 20) {
            $icon = "fa-info";
        } else
            $icon = '';
        return $icon;
    }

}

class Bootstrap_Message_Object extends stdClass implements Serializable
{
    public $type;
    public $text;
    public $time_out_ms;
    public $title;

    /**
     * Bootstrap_Message_Object constructor.
     * @param string $text
     * @param string $type info|warning|danger|success
     * @param int $time_out_ms
     * @param string $title
     */
    public function __construct($text, $type, $time_out_ms = null, $title = "") {
        $this->text = $text;
        $this->type = $type;
        $this->time_out_ms = $time_out_ms;
        $this->title = $title;
    }

    public function serialize() {
        return serialize(get_object_vars($this));
    }

    public function unserialize($data) {
        if (method_exists('stdClass', 'unserialize'))
            return stdClass::unserialize($data);
        else {
            $values = unserialize($data);
            foreach ($values as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
