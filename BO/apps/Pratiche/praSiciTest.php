<?php

/**
 *
 * TEST ELIOS WS-CLIENT
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    09.06.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPSici/itaSiciClient.class.php');

function praSiciTest() {
    $praSiciTest = new praSiciTest();
    $praSiciTest->parseEvent();
    return;
}

class praSiciTest extends itaModel {

    public $nameForm = "praSiciTest";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    private function setClientConfig($siciClient) {
        $config = $_POST[$this->nameForm . '_CONFIG'];
        $siciClient->setUri($config['wsEndpoint']);
        $siciClient->setWsdl($config['wsWsdl']);
        $siciClient->setApplicativo($config['wsEndpointApplicativo']);
        $siciClient->setEnte($config['wsWsdlEnte']);
        $siciClient->setPassword($config['wsPassword']);
        $siciClient->setCodiceAmm($config['wsCodiceAmministrazione']);
        $siciClient->setCodiceAOO($config['wsCodiceAOO']);
        $siciClient->setUtente($config['wsUser']);
        $siciClient->setNameSpaces($config['wsNameSpaces']);
        $siciClient->setNameSpace($config['wsNameSpaceSA']);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->nameForm, "", true, "desktopBody");
                Out::show($this->nameForm);
                //inizializzo i valori di configurazione della chiamata
                include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
                $devLib = new devLib();
                $uri = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSENDPOINT', false);
                $wsdl = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSWSDL', false);
                $applicativo = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSAPPLICATIVO', false);
                $ente = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSENTE', false);
                $password = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSPASSWORD', false);
                $codiceAmm = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODICEAMM', false);
                $codiceAOO = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODICEAOO', false);
                $utente = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSCODUTE', false);
                $nameSpaces = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSNAMESPACES', false);
                $namespace = $devLib->getEnv_config('SICIWSCONNECTION', 'codice', 'SICIWSNAMESPACE', false);
                //
                Out::valore($this->nameForm . "_CONFIG[wsEndpoint]", $uri['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsWsdl]", $wsdl['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsEndpointApplicativo]", $applicativo['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsWsdlEnte]", $ente['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsUser]", $utente['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsPassword]", $password['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsNameSpaces]", $nameSpaces['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsNameSpaceSA]", $namespace['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsCodiceAOO]", $codiceAOO['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsCodiceAmministrazione]", $codiceAOO['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[wsCodiceEnte]", $codiceAmm['CONFIG']);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_callLeggiProtocollo':
                        $siciClient = new itaSiciClient();
                        $this->setClientConfig($siciClient);
                        $param = array();
                        $param['AnnoProtocollo'] = $_POST[$this->nameForm . '_Anno'];
                        $param['NumeroProtocollo'] = $_POST[$this->nameForm . '_Numero'];
                        $ret = $siciClient->ws_LeggiProtocollo($param);
                        if (!$ret) {
                            if ($siciClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($siciClient->getFault(), true) . '</pre>');
                            } elseif ($siciClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($siciClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = $siciClient->getResult();
                        if ($risultato['Result'] == "true") {
                            include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
                            $xmlObj = new itaXML;
                            $retXml = $xmlObj->setXmlFromString(html_entity_decode($risultato['XML_RETURN']));
                            if (!$retXml) {
                                Out::msgStop("Leggi Protocollo", "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml");
                                break;
                            }
                            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                            if (!$arrayXml) {
                                Out::msgStop("Lettura XML Inserisci Protocollo: Impossibile estrarre i dati");
                                break;
                            }
                            $result = print_r($arrayXml, true);
                            Out::msgInfo("Leggi Protocollo Result", '<pre style="font-size:1.5em">' . $result . '</pre>');
                        } else {
                            Out::msgStop("Leggi Protocollo", $risultato['MSG_RETURN']);
                        }
                        break;
                    case $this->nameForm . '_callTitolario':
                        $siciClient = new itaSiciClient();
                        $this->setClientConfig($siciClient);
                        $ret = $siciClient->ws_Titolario();
                        if (!$ret) {
                            if ($siciClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($siciClient->getFault(), true) . '</pre>');
                            } elseif ($siciClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($siciClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = $siciClient->getResult();

                        if ($risultato['Result'] == "true") {
                            include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
                            $xmlObj = new itaXML;
                            $retXml = $xmlObj->setXmlFromString(html_entity_decode($risultato['XML_RETURN']));
                            if (!$retXml) {
                                Out::msgStop("Titolario", "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml");
                                break;
                            }
                            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                            if (!$arrayXml) {
                                Out::msgStop("Lettura XML Titolario: Impossibile estrarre i dati");
                                break;
                            }
                            $result = print_r($arrayXml, true);


                            $arrayTitolario = array();
                            $i = 0;
                            foreach ($arrayXml['Titolario'][0]['Categoria'] as $key => $categoria) {
                                foreach ($categoria['Classe'] as $key2 => $classe) {
                                    $arrayTitolario[$i]['Categoria'] = $categoria['Codice'][0]['@textNode'] . ":" . $categoria['Descrizione'][0]['@textNode'];
                                    $arrayTitolario[$i]['Classe'] = $classe['Codice'][0]['@textNode'] . ":" . $classe['Descrizione'][0]['@textNode'];
                                    $i++;
                                }
                            }
                            Out::msgInfo("Titolario Result", print_r($arrayTitolario, true));
                        } else {
                            Out::msgStop("Titolario", $risultato['MSG_RETURN']);
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
