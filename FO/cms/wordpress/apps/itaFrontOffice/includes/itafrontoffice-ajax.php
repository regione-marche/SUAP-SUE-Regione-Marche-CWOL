<?php

class Itafrontoffice_Ajax {

    static private $activated = false;
    static private $callbacks = array();

    public function __construct() {
        add_action('wp_ajax_nopriv_itafrontoffice_ajax', array($this, 'ajax_callback'));
        add_action('wp_ajax_itafrontoffice_ajax', array($this, 'ajax_callback'));
    }

    public function ajax_callback() {
        $model = frontOfficeApp::$cmsHost->getRequest('model');
        $attrs = unserialize(itaCrypt::decrypt(frontOfficeApp::$cmsHost->getRequest('data')));

        if (isset(self::$callbacks[$model]) && is_callable(self::$callbacks[$model])) {
            $htmlResult = call_user_func(self::$callbacks[$model], $attrs);
            if ($htmlResult) {
                output::ajaxResponseHtml($htmlResult);
            }
        }

        output::ajaxSendResponse();
        exit;
    }

    static public function register($function) {
        $model = is_array($function) ? $function[1] : (string) $function;
        self::$callbacks[$model] = $function;
    }

    static public function active($function, $attrs) {
        $model = is_array($function) ? $function[1] : (string) $function;
        $data = itaCrypt::encrypt(serialize($attrs));

        if (self::$activated) {
            return false;
        }

        if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return false;
        }

        echo <<<SCRIPT
<script>
    ajax.action = 'itafrontoffice_ajax';
    ajax.model = '$model';
    ajax.data = '$data';
</script>
SCRIPT;

        self::$activated = true;
    }

}

new Itafrontoffice_Ajax();
