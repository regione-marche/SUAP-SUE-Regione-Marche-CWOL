<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_LIB_PATH . '/itaPHPLinkNext/itaLinkNextClient.class.php';

function cwbBgeAgidConfNss() {
    $cwbBgeAgidConfNss = new cwbBgeAgidConfNss();
    $cwbBgeAgidConfNss->parseEvent();
    return;
}

class cwbBgeAgidConfNss extends cwbBpaGenTab {
    private $confNSS;

    function initVars() {
        $this->libDB = new cwbLibDB_BGE();

        $this->skipAuth = true;
    }

    protected function preConstruct() {
        $this->confNSS = cwbParGen::getFormSessionVar($this->nameForm, '_confNSS');
    }

    protected function postDestruct() {
        cwbParGen::setFormSessionVar($this->nameForm, '_confNSS', $this->confNSS);
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TestConnection':
                        $this->testConnection();
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        $configurazione = $this->libDB->leggiBgeAgidConfNss();
        if(!$configurazione){
            $this->setVisControlli(true, false, false, false, true, false, false, false, false, false);
            Out::valore($this->nameForm . '_BGE_AGID_CONF_NSS[GENERAIUV]', 0);
            Out::valore($this->nameForm . '_BGE_AGID_CONF_NSS[PROGKEYTAB]',1);
        }
        else{
            Out::valori($configurazione, $this->nameForm . '_' . $this->TABLE_NAME);
            $this->setVisControlli(true, false, false, false, false, true, false, false, false, false);
        }
        Out::setFocus("", $this->nameForm . '_BGE_AGID_CONF_NSS[URL]');
        Out::hide($this->nameForm . '_BGE_AGID_CONF_NSS[GENERAIUV]_field');
    }

    private function testConnection() {
        $error = false;

        // Crea Web Service Client
        $wsClient = $this->getWsClient();
        
        // Effettua login
        $param = array();
        $ret = $wsClient->ws_Login($param);
        if (!$ret) {
            $esito = false;
            if ($wsClient->getFault()) {                
                $messaggio = $wsClient->getFault();                
            } elseif ($wsClient->getError()) {
                $messaggio = $wsClient->getError();                      
            }
            Out::msgStop("Connessione fallita", $messaggio);
        }
        else{
            Out::msgInfo("Connessione riuscita", "La connessione all'intermediario Next Step Solution è avvenuta con successo");
        }
    }
    
    private function getWsClient() {
        $this->confNSS = $this->libDB->leggiBgeAgidConfNss();
        
        $wsClient = new itaLinkNextClient();
        $wsClient->setWebservices_uri($this->confNSS['URL']);
        $wsClient->setWebservices_wsdl($this->confNSS['URL'].'?wsdl');
//        $wsClient->setNamespace($ns['CONFIG']);
        $wsClient->setNameSpaces(array("foh" => "http://entranext.it/fohead", "ent" => "http://entranext.it/"));
        $wsClient->setUsername($this->confNSS['USERNAME']);
        $wsClient->setPassword($this->confNSS['PASSWORD']);
//        $wsClient->setToken($token);
        $wsClient->setIdConnettore($this->confNSS['IDCONNETTORE']);
        $wsClient->setIdAccesso($this->confNSS['IDENTIFICATIVO']);
        $wsClient->setCFEnte($this->confNSS['CODFISCENTE']);
        $wsClient->setGestionePDFACaricoDelFornitore(true);
        
        return $wsClient;
    }

    protected function postAggiorna() {
        $this->setVisControlli(true, false, false, false, false, true, false, false, false, false);
        Out::setFocus("", $this->nameForm . '_BGE_AGID_CONF_NSS[URL]');
    }
}

?>