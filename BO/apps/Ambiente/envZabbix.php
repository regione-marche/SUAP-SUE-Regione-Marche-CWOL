<?php
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function envZabbix() {
    $envZabbix = new envZabbix();
    $envZabbix->parseEvent();
    return;
}

class envZabbix extends itaModel {
    
    const MODO_APERTURA_DIALOG = 0;
    const MODO_APERTURA_IFRAME = 1;
    
    public $nameForm = "envZabbix";
    
    private $zabbixConf;
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':                
                $this->openZabbix();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'returnFromZabbix':
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function close() {
        parent::close();
        itaLib::closeForm($this->nameForm);
    }    
    
    private function openZabbix() {        
        $this->readConfig();
        if (!$this->zabbixConf['TEMPLATE_URL']) {
            Out::msgStop('Errore', 'Parametro ZABBIX_TEMPLATE_URL non impostato');
            $this->close();
            return;
        }
        if (!$this->zabbixConf['HOST']) {
            Out::msgStop('Errore', 'Parametro ZABBIX_HOST non impostato');
            $this->close();
            return;
        }
        if (!$this->zabbixConf['USERNAME']) {
            Out::msgStop('Errore', 'Parametro ZABBIX_USERNAME non impostato');
            $this->close();
            return;
        }
        if (!$this->zabbixConf['PASSWORD']) {
            Out::msgStop('Errore', 'Parametro ZABBIX_PASSWORD non impostato');
            $this->close();
            return;
        }
        
        if (isset($_POST['MODO_APERTURA'])) {
            $modoApertura = $_POST['MODO_APERTURA'];
        } else {
            $modoApertura = self::MODO_APERTURA_DIALOG;
        }
        
        switch($modoApertura) {
            case self::MODO_APERTURA_DIALOG:
                Out::openDocument($this->getZabbixUrl());
                break;
            case self::MODO_APERTURA_IFRAME:
                $model = 'utiIFrame';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['returnModel'] = $this->nameForm;
                $_POST['returnEvent'] = 'returnFromZabbix';
                $_POST['retid'] = '';
                $_POST['src_frame'] = $this->getZabbixUrl();
                $_POST['title'] = 'Zabbix Monitor';
                $_POST['returnKey'] = 'zabbix';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
        }                                
    }
    
    private function readConfig() {                
        $devLib = new devLib();
        $devLib->setITALWEB(ItaDB::DBOpen('ITALWEB', 'ditta'));
        $envConfig_rec = $devLib->getEnv_config('ZABBIX', 'codice', 'URL_TEMPLATE', false);
        $this->zabbixConf['TEMPLATE_URL'] = $envConfig_rec['CONFIG'];    
        $envConfig_rec = $devLib->getEnv_config('ZABBIX', 'codice', 'HOST_ADDR', false);
        $this->zabbixConf['HOST'] = $envConfig_rec['CONFIG'];    
        $envConfig_rec = $devLib->getEnv_config('ZABBIX', 'codice', 'USER', false);
        $this->zabbixConf['USERNAME'] = $envConfig_rec['CONFIG'];    
        $envConfig_rec = $devLib->getEnv_config('ZABBIX', 'codice', 'PASSWORD', false);
        $this->zabbixConf['PASSWORD'] = $envConfig_rec['CONFIG'];    
    }
    
    private function getZabbixUrl() {        
        // Template URL:  https://<host>/zabbix/index.php?name=<username>&password=<password>&enter=Sign in
        $url = $this->zabbixConf['TEMPLATE_URL'];
        $url = str_replace('<host>', $this->zabbixConf['HOST'], $url);
        $url = str_replace('<username>', $this->zabbixConf['USERNAME'], $url);
        $url = str_replace('<password>', $this->zabbixConf['PASSWORD'], $url);
        return $url;
    }

}
