<?php

/**
 * User: sbraun
 * Date: 19.07.17
 * Time: 14:11
 */
trait MY_Controller_Links
{
    /**
     * @return MY_Controller
     */
    public static function &get_instance(): MY_Controller {
        return parent::get_instance();
    }
    /**
     * shorthand for get_instance();
     * @deprecated because never used and ci() is more easy
     * @return MY_Controller
     */
    public static function &_():MY_Controller {
        return self::get_instance();
    }

    /**
     * @param string $object_type like "content","element","package"
     * @return MY_Model instance of a Model e.g. return this->content_model
     */
    public function any_model(string $object_type): MY_Model {
        if (method_exists($this, $object_type."_model"))
            return $this->{$object_type."_model"}();#otherwise use call_user_func()
        elseif (is_object($this->{$object_type . "_model"}))
            return $this->{$object_type . "_model"};
        die("Please load " . $object_type . "_model first");
    }
    # hazel - libs
    public function booking_lib():Order_lib {$this->load->library("hazel/booking_lib");return $this->booking_lib;}
    # libs
    public function api_lib():Api_lib {$this->load->library("api_lib");return $this->api_lib;}
    public function bootstrap_lib():Bootstrap_lib{$this->load->library("bootstrap_lib");return $this->bootstrap_lib;}
    public function curl_lib():Curl_lib {$this->load->library("curl_lib");return $this->curl_lib;}
    public function dom_lib():Dom_lib {$this->load->library("fusca/dom_lib");return $this->dom_lib;}
    public function fake_api_lib():Fake_api_lib {$this->load->library("fake_api_lib");return $this->fake_api_lib;}
    public function google_analytics_lib():Google_analytics_lib{$this->load->library("google_analytics_lib");return $this->google_analytics_lib;}
    public function controller_helper_lib():Controller_helper_lib{$this->load->library("controller_helper_lib");return $this->controller_helper_lib;}
    public function dogtag_helper_lib():Dogtag_helper_lib{$this->load->library("dogtag_helper_lib");return $this->dogtag_helper_lib;}
    public function topic_helper_lib():Topic_helper_lib{$this->load->library("topic_helper_lib");return $this->topic_helper_lib;}
    public function sendmail_lib():Sendmail_lib{$this->load->library("sendmail_lib");return $this->sendmail_lib;}
    public function element_lib():Element_lib{$this->load->library("element_lib");return $this->element_lib;}
    public function media_lib():Media_lib{$this->load->library("media_lib");return $this->media_lib;}
    public function csv_lib():Csv_lib{$this->load->library("csv_lib");return $this->csv_lib;}
    public function emarsys():Emarsys{$this->load->library("emarsys");return $this->emarsys;}
    public function pdf_lib():Pdf_lib{$this->load->library("pdf_lib");return $this->pdf_lib;}
    # frontend-models
    public function package_api_model():Package_api_model {$this->load->model("package_api_model");return $this->package_api_model;}
    public function redirect_api_model():Redirect_api_model {$this->load->model("redirect_api_model");return $this->redirect_api_model;}
    public function metatag_api_model():Metatag_api_model {$this->load->model("metatag_api_model");return $this->metatag_api_model;}
    # cms-models
    public function api_model():Api_model{$this->load->model("api/api_model");return $this->api_model;}
    public function object_attribute_model():Object_attribute_model{$this->load->model("cms/object_attribute_model");return $this->object_attribute_model;}
    public function cached_json_model():Cached_json_model{$this->load->model("api/cached_json_model");return $this->cached_json_model;}
    public function contact_model():Contact_model{$this->load->model("cms/contact_model");return $this->contact_model;}
    public function content_model():Content_model{$this->load->model("cms/content_model");return $this->content_model;}
    public function content_type_model():Content_type_model{$this->load->model("cms/content_type_model");return $this->content_type_model;}
    public function content_element_knots_model():Content_element_knots_model{$this->load->model("cms/content_element_knots_model");return $this->content_element_knots_model;}
    public function dogtag_knot_model():Dogtag_knot_model{$this->load->model("cms/dogtag_knot_model");return $this->dogtag_knot_model;}
    public function dogtag_model():Dogtag_model{$this->load->model("cms/dogtag_model");return $this->dogtag_model;}
    public function element_model():Element_model{$this->load->model("cms/element_model");return $this->element_model;}
    public function element_type_model():Element_type_model{$this->load->model("cms/element_type_model");return $this->element_type_model;}
    public function event_model():Event_model{$this->load->model("cms/event_model");return $this->event_model;}
    public function gallery_model():Gallery_model{$this->load->model("cms/gallery_model");return $this->gallery_model;}
    public function hotel_attribute_model():Hotel_attribute_model{$this->load->model("cms/hotel_attribute_model");return $this->hotel_attribute_model;}
    public function hotel_service_model():Hotel_service_model{$this->load->model("cms/hotel_service_model");return $this->hotel_service_model;}
    public function knot_model():Knot_model{$this->load->model("cms/knot_model");return $this->knot_model;}
    public function picture_knot_model():Picture_knot_model{$this->load->model("cms/picture_knot_model");return $this->picture_knot_model;}
    public function location_model():Location_model{$this->load->model("cms/location_model");return $this->location_model;}
    public function offerer_model():Offerer_model{$this->load->model("cms/offerer_model");return $this->offerer_model;}
    public function old_picture_model():Old_picture_model{$this->load->model("cms/picture_model");$this->load->model("cms/old_picture_model");return $this->old_picture_model;}
    public function old_content_import_model():Old_content_import_model{$this->load->model("cms/old_content_import_model");return $this->old_content_import_model;}
    public function hotel_model():Hotel_model{$this->load->model("cms/hotel_model");return $this->hotel_model;}
    public function package_model():Package_model{$this->load->model("cms/package_model");return $this->package_model;}
    public function picture_model():Picture_model{$this->load->model("cms/picture_model");return $this->picture_model;}
    public function dms_picture_model():Dms_picture_model{$this->load->model("import/dms_picture_model");return $this->dms_picture_model;}
    public function producer_model():Producer_model{$this->load->model("cms/producer_model");return $this->producer_model;}
    public function redirect_model():Redirect_model{$this->load->model("cms/redirect_model");return $this->redirect_model;}
    public function remark_model():Remark_model{$this->load->model("cms/remark_model");return $this->remark_model;}
    public function staticpage_model():Staticpage_model{$this->load->model("cms/staticpage_model");return $this->staticpage_model;}
    public function topic_model():Topic_model{$this->load->model("cms/topic_model");return $this->topic_model;}
    public function metatag_model():Metatag_model{$this->load->model("cms/metatag_model");return $this->metatag_model;}
    public function notification_model():Notification_model{$this->load->model("cms/notification_model");return $this->notification_model;}
    public function notification_type_model():Notification_type_model{$this->load->model("cms/notification_type_model");return $this->notification_type_model;}
    public function notification_user_model():Notification_user_model{$this->load->model("cms/notification_user_model");return $this->notification_user_model;}
    public function publisher_model():Publisher_model{$this->load->model("export/publisher_model");return $this->publisher_model;}
    public function cache_manager_model():Cache_manager_model{$this->load->model("export/cache_manager_model");return $this->cache_manager_model;}
    public function cronjob_model():Cronjob_model{$this->load->model("com/cronjob_model");return $this->cronjob_model;}
    public function seo_keyword_log_model():Seo_keyword_log_model{$this->load->model("cms/seo_keyword_log_model");return $this->seo_keyword_log_model;}
    public function availability_model():Availability_model{$this->load->model("cms/availability_model");return $this->availability_model;}
    public function flag_model():Flag_model{$this->load->model("cms/flag_model");return $this->flag_model;}
    public function pdf_editor_model():Pdf_editor_model{$this->load->model("cms/pdf_editor_model");return $this->pdf_editor_model;}
    public function user_history_model():User_history_model{$this->load->model("history/user_history_model");return $this->user_history_model;}
    # marketing
    public function product_feed_model():Product_feed_model{$this->load->model("marketing/product_feed_model");return $this->product_feed_model;}
    # search
    public function search_model():Search_model{$this->load->model("search/search_model");return $this->search_model;}
    public function search_model_new():Search_model_old{$this->load->model("search/search_model_old");return $this->search_model_old;}
    # user
    public function user_model():User_model{$this->load->model("user/user_model");return $this->user_model;}
    #zombie
    public function zombie_fake_model():Zombie_fake_model{$this->load->model("zombie/zombie_fake_model");return $this->zombie_fake_model;}
    public function zombie_reporting_model():Zombie_reporting_model{$this->load->model("zombie/zombie_reporting_model");return $this->zombie_reporting_model;}
    #hazelnut
    public function hazelnut_fake_model():Hazelnut_fake_model{$this->load->model("hazelnut/hazelnut_fake_model");return $this->hazelnut_fake_model;}
    public function hazelnut_booking_fake_model():Hazelnut_booking_fake_model{$this->load->model("hazelnut/hazelnut_booking_fake_model");return $this->hazelnut_booking_fake_model;}
    #cimo
    public function cimo_fake_model():Cimo_fake_model{$this->load->model("cimo/cimo_fake_model");return $this->cimo_fake_model;}
    #trefzer
    public function trefzer_fake_model():Trefzer_fake_model{$this->load->model("trefzer/trefzer_fake_model");return $this->trefzer_fake_model;}
    #backup
    public function backup_fake_model():Backup_fake_model{$this->load->model("backup/backup_fake_model");return $this->backup_fake_model;}
    # loader
    public function loader():MY_Loader{return $this->load;}
}

function &ci(): MY_Controller {
    return MY_Controller::get_instance();
}
