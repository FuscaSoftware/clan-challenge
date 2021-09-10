<?php

/**
 * Description of super_lib
 *
 * @author sebra
 */
class Super_lib
{
    /** @var MY_Controller $ci */
    protected $ci;

    public function __construct() {
        $this->ci = $this->controller = $this->CI = MY_Controller::get_instance();
    }

    public function __call($name, $arguments) {
        if (method_exists($this, $name))
            return call_user_func_array(array($this, $name), $arguments);
        if (isset($this->ci) && method_exists($this->ci, $name))
            return call_user_func_array(array($this->ci, $name), $arguments);
        else
            throw new RuntimeException("Methode unknown! $name() in " . get_class($this) . "'-Class.");

    }

    public function __get($name) {
        if (isset($this->$name))
            return $this->$name;
        if (isset($this->ci))
            return $this->ci->$name;
    }

//	public function __set($name, $value) {
//		if ($this->ci)
//			$this->ci->$name = $value;
//	}
    public function get_view($view, $data = null) {
        return ci()->get_view($view, $data);
    }
}
