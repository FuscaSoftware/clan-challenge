<?php

/**
 * Ajax Dom_lib to control DocumentObjectModel by ajax
 * User: sebra
 * Date: 25.05.18
 * Time: 11:29
 */
class Dom_lib extends Super_lib
{
    /** @var array $default_params Parameters */
    protected $default_params = [

    ];

    /** @var array $error */
    public $error;

    public function __construct() {

    }

//    public function add_class(array $view_data, $selector, $class) {
//        $view_data[]
//    }

    /**
     * This replaces the outer html
     *
     * @param string $selector
     * @param string $content
     *
     * @return string
     */
    public function replace(string $selector, string $content) {
//        return $this->html($selector, $content);
        return ci()->data['html']['replace'][$selector] = $content;
    }

    /**
     * This replaces the inner html
     * Bug: the outer html of the new element send by ajax gets lost
     *
     * @param string $selector
     * @param string $content
     *
     * @return string
     */
    public function html(string $selector, string $content) {
        return ci()->data['html']['html'][$selector] = $content;
    }

    /**
     * This inserts the inner html without loss
     *
     * @param string $selector
     * @param string $content
     *
     * @return string
     */
    public function html2(string $selector, string $content) {
        return ci()->data['html']['html2'][$selector] = $content;
    }

    public function remove(string $selector) {
        return ci()->data['html']['remove'][$selector] = '';
    }

    public function insert_before(string $selector, string $content) {
        return ci()->data['html']['insertBefore'][$selector] = $content;
    }

    public function insert_after(string $selector, string $content) {
        return ci()->data['html']['insertAfter'][$selector] = $content;
    }

    public function append(string $selector, string $content) {
        return ci()->data['html']['append'][$selector] = $content;
    }

	/**
	 * appendTo (does not work)
	 * @param string $selector
	 * @param string $content
	 *
	 * @return string
	 */
    public function appendTo(string $selector, string $content) {
        return ci()->data['html']['appendTo'][$selector] = $content;
    }

    public function prepend(string $selector, string $content) {
        return ci()->data['html']['prepend'][$selector] = $content;
    }

    public function value(string $selector, string $value) {
        return ci()->data['html']['value'][$selector] = $value;
    }

    public function show(string $selector, string $content) {
        return ci()->data['html']['show'][$selector] = $content;
    }

    public function hide(string $selector, string $content) {
        return ci()->data['html']['hide'][$selector] = $content;
    }

    public function addClass(string $selector, string $content) {
        return ci()->data['html']['addClass'][$selector] = $content;
    }

    public function removeClass(string $selector, string $content) {
        return ci()->data['html']['removeClass'][$selector] = $content;
    }

    public function callback(string $selector, string $content) {
        return ci()->data['html']['callback'][$selector] = $content;
    }

    public function attribute(string $selector, $key, $value) {
        return ci()->data['html']['attribute'][$selector] = ['key' => $key, 'value' => $value];
    }

    public function location($url) {
        return ci()->data['location'] = $url;
    }
    public function reload() {
        return ci()->data['reload'] = 1;
    }
}
