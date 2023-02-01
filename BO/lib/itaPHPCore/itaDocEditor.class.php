<?php

require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
require_once ITA_BASE_PATH . '/apps/Utility/utiDocEditor.class.php';

/**
 * Funzionalità Document Editor
 *
 * @author m.biagioli
 */
class itaDocEditor {

    const DOCX_FILETYPE = 'docx';
    const DOCX_DOCTYPE = 'text';
    const EDITOR_WIDTH = '100%';
    const EDITOR_HEIGHT = '100%';
    const EDITOR_ZOOM = '100';
    const EDITOR_TYPE_DESKTOP = 'desktop';
    const EDITOR_DEFAULT_LANG = 'it-IT';
    const EDITOR_DEFAULT_LOCALE = 'it';
    const EDITOR_CUSTOM_ABOUT = true;
    const EDITOR_COMPACT_TOOLBAR = false;
    const REST_SERVICE_GET_DOCUMENT = '/getDocument';
    const REST_SERVICE_STORE_DOCUMENT = '/storeDocument';
    const EDITOR_MODE_EDIT = 'edit';
    const EDITOR_MODE_VIEW = 'view';
    const VKEY_LEN = 20;
    
    // Parametri editor
    private $docEditorParams;
    
    // Proprietà documento
    private $vKey;
    private $documentTitle;
    private $permDownload;
    private $permEdit = true;
    private $permRename = false;
    private $permComments = true;
    private $permPrint = true;
    private $permCanCoAuthoring = true;
    private $permCanRequestEditRights = true;
    private $permChat = true;
    private $editorMode;
    private $resourcePath;
    private $created;
    private $author;
    private $plugins = array();
    
    public function __construct() {
        $this->docEditorParams = $this->loadDocEditorParams();
    }
    
    /**
     * Crea nuovo oggetto itaDocEditor
     * @param array $params Parametri creazione oggetto
     *              - resourceRowid
     *              - filePath
     *              - fileName
     * @return \itaDocEditor
     */
    public static function newDocEditor($params) {
        $itaDocEditor = new itaDocEditor();        
        $itaDocEditor->setVKey($itaDocEditor->createVKey($params['resourceRowid'], $params['filePath']));
        $itaDocEditor->setDocumentTitle($params['fileName']);        
        $itaDocEditor->setEditorMode(itaDocEditor::EDITOR_MODE_EDIT);
        $itaDocEditor->setResourcePath(base64_encode($params['filePath']));
        $itaDocEditor->setCreated(date('d.m.Y'));
        $itaDocEditor->setAuthor(App::$utente->getKey('nomeUtente'));
        return $itaDocEditor;
    }
    
    /**
     * Carica parametri DocumentEditor
     * @return array
     */
    public function loadDocEditorParams() {
        $devLib = new devLib();

        $docEditorParams = array();

        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_OO_BASE_URL', false);
        $docEditorParams['DOCEDIT_OO_BASE_URL'] = $configData['CONFIG'];

        $docEditorParams['DOCEDIT_CONVERTER_URL'] = $docEditorParams['DOCEDIT_OO_BASE_URL'] . "/ConvertService.ashx";
        $docEditorParams['DOCEDIT_API_URL'] = $docEditorParams['DOCEDIT_OO_BASE_URL'] . "/web-apps/apps/api/documents/api.js";
        $docEditorParams['DOCEDIT_PRELOADER_URL'] = $docEditorParams['DOCEDIT_OO_BASE_URL'] . "/web-apps/apps/api/documents/cache-scripts.html";

        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_REST_BASE_URL_STORAGE', false);
        $RESTBaseURLStorage = $configData['CONFIG'];
        $docEditorParams['DOCEDIT_RESTURL_GETDOCUMENT'] = $RESTBaseURLStorage . self::REST_SERVICE_GET_DOCUMENT;
        $docEditorParams['DOCEDIT_RESTURL_STOREDOCUMENT'] = $RESTBaseURLStorage . self::REST_SERVICE_STORE_DOCUMENT;

        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_REST_BASE_URL_PLUGIN', false);
        $docEditorParams['DOCEDIT_RESTURL_PLUGIN'] = $configData['CONFIG'];
        
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_PLUGIN_RESOURCES', false);
        $docEditorParams['DOCEDIT_PLUGIN_RESOURCES'] = $configData['CONFIG'];
        
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_NAME', false);
        $docEditorParams['DOCEDIT_CUST_NAME'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_ADDRESS', false);
        $docEditorParams['DOCEDIT_CUST_ADDRESS'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_MAIL', false);
        $docEditorParams['DOCEDIT_CUST_MAIL'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_WWW', false);
        $docEditorParams['DOCEDIT_CUST_WWW'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_INFO', false);
        $docEditorParams['DOCEDIT_CUST_INFO'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_LOGO_URL', false);
        $docEditorParams['DOCEDIT_CUST_LOGO_URL'] = $configData['CONFIG'];
        $configData = $devLib->getEnv_config('DOCUMENT_EDITOR', 'codice', 'DOCEDIT_CUST_FEED_URL', false);
        $docEditorParams['DOCEDIT_CUST_FEED_URL'] = $configData['CONFIG'];

        return $docEditorParams;
    }
    
    /**
     * Aggiunge un plugin
     * @param string $pluginType Tipo plugin
     * @param string @pluginParams Parametri plugin
     */
    public function addPlugin($pluginType, $pluginParams) {
        $strPlugin = $this->docEditorParams['DOCEDIT_RESTURL_PLUGIN'] . '/getConfig/config.json?type=' . $pluginType;
        $strPluginQuery = http_build_query($pluginParams);
        if (strlen($strPluginQuery) > 0) {
            $strPlugin .= '&' . $strPluginQuery;
        }
        $this->plugins[] = $strPlugin;              
    }
        
    /**
     * Apre Editor Docx
     */
    public function openEditor() {        
        Out::openDocument(utiDocEditor::getUrl($this->createDataArrayDocx()));
    }
    
    /**
     * Creazione VKey
     * @param string $resourceRowid Resource ROWID
     * @param string $filePath Percorso fisico del file
     * @return string VKey
     */
    public function createVKey($resourceRowid, $filePath) {
        return substr(md5($resourceRowid . '-' . hash_file('sha256', $filePath)), 1, self::VKEY_LEN);
    }
    
    private function createDataArrayDocx() {
        $dataArray = array();

        $dataArray['width'] = self::EDITOR_WIDTH;
        $dataArray['height'] = self::EDITOR_HEIGHT;
        $dataArray['type'] = self::EDITOR_TYPE_DESKTOP;
        $dataArray['documentType'] = self::DOCX_DOCTYPE;

        $dataArray['document'] = array();
        $dataArray['document']['title'] = $this->getDocumentTitle();
        $dataArray['document']['url'] = $this->docEditorParams['DOCEDIT_RESTURL_GETDOCUMENT'] . "?resourceid=" . $this->getResourcePath();
        $dataArray['document']['fileType'] = self::DOCX_FILETYPE;
        $dataArray['document']['key'] = $this->getVKey();
        $dataArray['document']['info'] = array();
        $dataArray['document']['info']['author'] = $this->getAuthor();
        $dataArray['document']['info']['created'] = $this->getCreated();
        $dataArray['document']['permissions'] = array();
        $dataArray['document']['permissions']['download'] = $this->getPermDownload();
        $dataArray['document']['permissions']['edit'] = $this->getPermEdit();
        $dataArray['document']['permissions']['rename'] = $this->getPermRename();
        $dataArray['document']['permissions']['comment'] = $this->getPermComments();
        $dataArray['document']['permissions']['print'] = $this->getPermPrint();

        $dataArray['editorConfig'] = array();
        $dataArray['editorConfig']['mode'] = $this->getEditorMode();
        $dataArray['editorConfig']['lang'] = self::EDITOR_DEFAULT_LANG;
        $dataArray['editorConfig']['location'] = self::EDITOR_DEFAULT_LOCALE;
        $dataArray['editorConfig']['canCoAuthoring'] = $this->getPermCanCoAuthoring();
        $dataArray['editorConfig']['canRequestEditRights'] = $this->getPermCanRequestEditRights();
        $dataArray['editorConfig']['callbackUrl'] = $this->docEditorParams['DOCEDIT_RESTURL_STOREDOCUMENT'] . "?resourceid=" . $this->getResourcePath();
        $dataArray['editorConfig']['user'] = array();
        $dataArray['editorConfig']['user']['name'] = App::$utente->getKey('nomeUtente');
        $dataArray['editorConfig']['user']['id'] = App::$utente->getKey('idUtente');
        $dataArray['editorConfig']['customization'] = array();
        $dataArray['editorConfig']['customization']['about'] = self::EDITOR_CUSTOM_ABOUT;
        $dataArray['editorConfig']['customization']['customer'] = array();
        $dataArray['editorConfig']['customization']['customer']['name'] = $this->docEditorParams['DOCEDIT_CUST_NAME'];
        $dataArray['editorConfig']['customization']['customer']['address'] = $this->docEditorParams['DOCEDIT_CUST_ADDRESS'];
        $dataArray['editorConfig']['customization']['customer']['mail'] = $this->docEditorParams['DOCEDIT_CUST_MAIL'];
        $dataArray['editorConfig']['customization']['customer']['www'] = $this->docEditorParams['DOCEDIT_CUST_WWW'];
        $dataArray['editorConfig']['customization']['customer']['info'] = $this->docEditorParams['DOCEDIT_CUST_INFO'];
        $dataArray['editorConfig']['customization']['customer']['logo'] = $this->docEditorParams['DOCEDIT_CUST_LOGO_URL'];
        $dataArray['editorConfig']['customization']['feedback'] = array();
        $dataArray['editorConfig']['customization']['feedback']['url'] = $this->docEditorParams['DOCEDIT_CUST_FEED_URL'];
        $dataArray['editorConfig']['customization']['feedback']['visible'] = (strlen(trim($this->docEditorParams['DOCEDIT_CUST_FEED_URL'])) > 0);
        $dataArray['editorConfig']['customization']['logo'] = array();
        $dataArray['editorConfig']['customization']['logo']['image'] = $this->docEditorParams['DOCEDIT_CUST_LOGO_URL'];
        $dataArray['editorConfig']['customization']['logo']['url'] = $this->docEditorParams['DOCEDIT_CUST_WWW'];
        $dataArray['editorConfig']['customization']['chat'] = $this->getPermChat();
        $dataArray['editorConfig']['customization']['comments'] = $this->getPermComments();
        $dataArray['editorConfig']['customization']['zoom'] = self::EDITOR_ZOOM;
        $dataArray['editorConfig']['customization']['compactToolbar'] = self::EDITOR_COMPACT_TOOLBAR;
        $dataArray['editorConfig']['plugins'] = array();
        $dataArray['editorConfig']['plugins']['pluginsData'] = $this->getPlugins();

        $dataArray['events'] = array();
        $dataArray['events']['onReady'] = 'onReady';
        $dataArray['events']['onDocumentStateChange'] = 'onDocumentStateChange';
        if ($this->getPermCanRequestEditRights()) {
            $dataArray['events']['onRequestEditRights'] = 'onRequestEditRights';
        }
        $dataArray['events']['onError'] = 'onError';
        $dataArray['events']['onOutdatedVersion'] = 'onOutdatedVersion';

        return $dataArray;
    }
    
    public function getVKey() {
        return $this->vKey;
    }

    public function getDocumentTitle() {
        return $this->documentTitle;
    }

    public function getPermDownload() {
        return $this->permDownload;
    }

    public function getPermEdit() {
        return $this->permEdit;
    }

    public function getPermRename() {
        return $this->permRename;
    }

    public function getPermComments() {
        return $this->permComments;
    }

    public function getPermPrint() {
        return $this->permPrint;
    }

    public function getPermCanCoAuthoring() {
        return $this->permCanCoAuthoring;
    }

    public function getPermCanRequestEditRights() {
        return $this->permCanRequestEditRights;
    }

    public function getPermChat() {
        return $this->permChat;
    }

    public function getEditorMode() {
        return $this->editorMode;
    }

    public function getResourcePath() {
        return $this->resourcePath;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setVKey($vKey) {
        $this->vKey = $vKey;
    }

    public function setDocumentTitle($documentTitle) {
        $this->documentTitle = $documentTitle;
    }

    public function setPermDownload($permDownload) {
        $this->permDownload = $permDownload;
    }

    public function setPermEdit($permEdit) {
        $this->permEdit = $permEdit;
    }

    public function setPermRename($permRename) {
        $this->permRename = $permRename;
    }

    public function setPermComments($permComments) {
        $this->permComments = $permComments;
    }

    public function setPermPrint($permPrint) {
        $this->permPrint = $permPrint;
    }

    public function setPermCanCoAuthoring($permCanCoAuthoring) {
        $this->permCanCoAuthoring = $permCanCoAuthoring;
    }

    public function setPermCanRequestEditRights($permCanRequestEditRights) {
        $this->permCanRequestEditRights = $permCanRequestEditRights;
    }

    public function setPermChat($permChat) {
        $this->permChat = $permChat;
    }

    public function setEditorMode($editorMode) {
        $this->editorMode = $editorMode;
    }

    public function setResourcePath($resourcePath) {
        $this->resourcePath = $resourcePath;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }
    
    public function getPlugins() {
        return $this->plugins;
    }
    
    public function getDocEditorParams() {
        return $this->docEditorParams;
    }
    
}
