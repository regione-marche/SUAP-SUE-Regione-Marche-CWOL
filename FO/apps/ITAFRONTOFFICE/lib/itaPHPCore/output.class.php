<?php

/**
 * Classe per la generazione dell'output HTML.
 *
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author Carlo Iesari <carlo.iesari@italsoft.eu>
 */
class output {

    static public $html_out = '';
    static public $ajax_out = array();
    static private $html;

    static public function appendHtml($html) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function prependHtml($html) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function openTag($tag, $attrs = array()) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function closeTag($tag) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addForm($action = false, $method = 'GET', $attrs = array(), $ajax = false) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addHidden($id, $value) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addMsgInfo($title, $msg) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addAlert($message, $title = '', $type = 'info') {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addBr($repeat = 1) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addButton($text, $href = false, $style = 'default', $ajax = array(), $closeCurrentDialog = false) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addImage($src, $size = 'auto', $title = '', $href = '', $target = '') {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addInput($type = 'text', $label = '', $attrs = array(), $options = array()) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addLink($text, $href = false, $ajax = array()) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addSubmit($text, $name = '') {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addTable($array, $options = array(), $attrs = array()) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    static public function addTreeView($array, $mode = html::TREEVIEW_NORMAL) {
        self::callHtmlMethod(__FUNCTION__, func_get_args());
    }

    /**
     * 
     * @param type $html HTML in UTF-8.
     * @param type $id
     * @param type $method
     */
    static public function responseHtml($html, $id = null, $method = null) {
        self::ajaxResponseHtml($html, $id, $method);
    }

    /**
     * 
     * @param type $html HTML in UTF-8.
     * @param type $id
     * @param type $method
     */
    static public function ajaxResponseHtml($html, $id = null, $method = null) {
        if (!isset(self::$ajax_out['html'])) {
            self::$ajax_out['html'] = array();
        }

        self::$ajax_out['html'][] = array(
            'html' => utf8_decode($html),
            'id' => $id,
            'method' => $method
        );
    }

    static public function responseCloseCurrentDialog() {
        if (!isset(self::$ajax_out['closeCurrentDialog'])) {
            self::$ajax_out['closeCurrentDialog'] = array();
        }

        self::$ajax_out['closeCurrentDialog'][] = true;
    }

    static public function responseRedirect($uri) {
        self::$ajax_out['redirect'] = $uri;
    }

    static public function responseEnableField($id) {
        if (!isset(self::$ajax_out['enableField'])) {
            self::$ajax_out['enableField'] = array();
        }

        self::$ajax_out['enableField'][] = $id;
    }

    static public function responseDisableField($id) {
        if (!isset(self::$ajax_out['disableField'])) {
            self::$ajax_out['disableField'] = array();
        }

        self::$ajax_out['disableField'][] = $id;
    }

    /**
     * 
     * @param type $html HTML in UTF-8.
     * @param type $params
     */
    static public function responseDialog($html, $params) {
        self::ajaxResponseDialog($html, $params);
    }

    /**
     * 
     * @param type $html HTML in UTF-8.
     * @param type $params
     */
    static public function ajaxResponseDialog($html, $params) {
        $dialog = array_merge(array('html' => utf8_decode($html)), $params);
        self::$ajax_out['dialog'] = $dialog;
    }

    static public function responseValues($data) {
        self::ajaxResponseValues($data);
    }

    static public function ajaxResponseValues($data) {
        self::$ajax_out['values'] = $data;
    }

    static public function responseValuesRaccolta($data) {
        self::ajaxResponseValuesRaccolta($data);
    }

    static public function ajaxResponseValuesRaccolta($data) {
        $raccolta = array();

        foreach ($data as $k => $v) {
            $raccolta["raccolta[$k]"] = $v;
        }

        self::ajaxResponseValues($raccolta);
    }

    static public function responseFocus($id) {
        self::ajaxResponseFocus($id);
    }

    static public function ajaxResponseFocus($id) {
        self::$ajax_out['focus'] = $id;
    }

    static public function responseShow($id) {
        self::ajaxResponseShow($id);
    }

    static public function ajaxResponseShow($id) {
        if (!isset(self::$ajax_out['show'])) {
            self::$ajax_out['show'] = array();
        }

        self::$ajax_out['show'][] = $id;
    }

    static public function responseHide($id) {
        self::ajaxResponseHide($id);
    }

    static public function ajaxResponseHide($id) {
        if (!isset(self::$ajax_out['hide'])) {
            self::$ajax_out['hide'] = array();
        }

        self::$ajax_out['hide'][] = $id;
    }

    static public function responseTableData($tableId, $arrayData) {
        self::ajaxResponseTableData($tableId, $arrayData);
    }

    static public function ajaxResponseTableData($tableId, $arrayData) {
        if (!isset(self::$ajax_out['tableData'])) {
            self::$ajax_out['tableData'] = array();
        }

        foreach ($arrayData as &$record) {
            foreach ($record as &$value) {
                $value = utf8_encode($value);
            }
        }

        self::$ajax_out['tableData'][$tableId] = $arrayData;
    }

    static public function ajaxSendResponse() {
        $jsonString = json_encode(frontOfficeLib::utf8_encode_recursive(self::$ajax_out));

        $ob_level = ob_get_level();
        for ($i = $ob_level; $i > 0; $i--) {
            @ob_end_clean();
        }

        echo $jsonString;
        exit;
    }

    static public function sendResponse() {
        if (!count(self::$ajax_out)) {
            return;
        }

        $jsonString = json_encode(frontOfficeLib::utf8_encode_recursive(self::$ajax_out));

        echo <<<JS
<script>
jQuery(document).ready(function () {
    setTimeout(function() {
        itaFrontOffice.ajaxParseResponse($jsonString);
    }, 100);
});
</script>
JS;
    }

    static private function callHtmlMethod($name, $arguments) {
        /*
         * Istanzio la classe html se non presente.
         */
        if (is_null(self::$html)) {
            self::$html = new html();
        }

        /*
         * Verifico che il metodo chiamato sia presente nella classe html.
         */
        if (method_exists(self::$html, $name)) {
            call_user_func_array(array(self::$html, $name), $arguments);
            self::$html_out .= self::$html->getHtml();
            self::$html->setHtml('');
        }
    }

}
