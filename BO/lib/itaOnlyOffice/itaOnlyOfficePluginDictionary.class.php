<?php

require_once ITA_LIB_PATH . '/itaOnlyOffice/itaOnlyOfficePlugin.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaDocEditor.class.php';
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

/**
 * Plugin Dizionario
 * @author m.biagioli
 */
class itaOnlyOfficePluginDictionary implements itaOnlyOfficePlugin {
    
    private $docEditor;
    private $pluginParams;
    
    public function __construct() {
        $this->docEditor = new itaDocEditor();
        $this->loadPluginParams();
    }
    
    public function getResource($params) {
        $docEditorParams = $this->docEditor->getDocEditorParams();       
        $pluginRoot = $docEditorParams['DOCEDIT_PLUGIN_RESOURCES'];        
        $pathInfo = $_SERVER['PATH_INFO'];
        
        if (strpos($pathInfo, "config.json") !== false) {
            header('Accept-Ranges: bytes');
            $etag = uniqid();
            header("Etag: $etag");

            $data = array();
            $data['guid'] = "asc.{17654ac8-4cb5-4740-94d5-1f53a3e3b303}";
            $data['name'] = "Dictionary";
            $data['variations'] = array();
            $data['variations'][] = array(
                "description" => 'Dictionary',
                "url" => "index.php?type=dictionary&domain=" . $params['domain'] . "&token=" . $params['token'] . "&resourceid=" . $params['resourceid'] . "&classificazione=" . $params['classificazione'],
                "icons" => array("icon.png?" . $_SERVER['QUERY_STRING'], "icon.png?" . $_SERVER['QUERY_STRING']),
                "isViewer" => true,
                "EditorsSupport" => array("word"),
                "isVisual" => true,
                "isModal" => true,
                "isInsideMode" => false,
                "initDataType" => "text",
                "initData" => "",
                "isUpdateOleOnResize" => true,
                "buttons" => array(),
                "size" => array(850, 700)
            );
            echo json_encode($data);            
        } else if (strpos($pathInfo, "index.php") !== false) {            
            header('Content-type: text/html');
            echo file_get_contents($pluginRoot . "/dictionary/index.php?" . $_SERVER['QUERY_STRING'] . "&pluginroot=" . base64_encode($pluginRoot) . '&endpoint=' . base64_encode($this->pluginParams['OO_PLUGIN_DICT_ENDPOINT']));
        } else if (strpos($pathInfo, "icon.png") !== false) {
            header('Content-type: application/png');
            echo file_get_contents($pluginRoot . "/dictionary/icon.png");
        }       
    }
    
    private function loadPluginParams() {
        $devLib = new devLib();
        $this->pluginParams = array();
        $configData = $devLib->getEnv_config('OO_PLUGIN_DICT', 'codice', 'OO_PLUGIN_DICT_ENDPOINT', false);
        $this->pluginParams['OO_PLUGIN_DICT_ENDPOINT'] = $configData['CONFIG'];        
    }
    
}