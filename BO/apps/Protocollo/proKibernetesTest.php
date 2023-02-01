<?php

/**
 *
 * 
 *
 * PHP Version 5
 *
 * @category
 * @package    Protocollo
 * @author     Alessandro Mucci <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    18.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesProtClient.class.php');

function proKibernetesTest() {
    $proKibernetesTest = new proKibernetesTest();
    $proKibernetesTest->parseEvent();
    return;
}

class proKibernetesTest extends itaModel {

    public $nameForm = "proKibernetesTest";
    public $itaKibernetesSdiLayerParam;
    public $StreamDocumento1Agg;
    public $StreamDocumento2Agg;

    function __construct() {
        parent::__construct();
        $this->StreamDocumento1Agg = App::$utente->getKey($this->nameForm . '_StreamDocumento1Agg');
        $this->StreamDocumento2Agg = App::$utente->getKey($this->nameForm . '_StreamDocumento2Agg');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_StreamDocumento1Agg', $this->StreamDocumento1Agg);
            App::$utente->setKey($this->nameForm . '_StreamDocumento2Agg', $this->StreamDocumento2Agg);
        }
    }

    private function setClientConfig($WSClient) {
        $config = $_POST[$this->nameForm . '_CONFIG'];
        $WSClient->setWebservices_uri($config['WSKIBERNETESENDPOINT']);
        $WSClient->setWebservices_wsdl($config['WSKIBERNETESWSDL']);
        $WSClient->setUsername($config['WSKIBERNETESUSER']);
        $WSClient->setPassword($config['WSKIBERNETESPASSWORD']);
        $WSClient->setCodiceUOPar($config['WSKIBERNETESCODICEUOPAR']);
        $WSClient->setFunzionarioPar($config['WSKIBERNETESFUNZIONARIOPAR']);
        $WSClient->setCodiceUOArr($config['WSKIBERNETESCODICEUOARR']);
        $WSClient->setFunzionarioArr($config['WSKIBERNETESFUNZIONARIOARR']);
        $WSClient->setNamespace();
        $WSClient->setNamespaces();
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
                $uri = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESENDPOINT', false);
                $wsdl = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESWSDL', false);
                $username = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESUSER', false);
                $password = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESPASSWORD', false);
                $codiceUOPar = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESCODICEUOPAR', false);
                $funzionarioPar = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESFUNZIONARIOPAR', false);
                $codiceUOArr = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESCODICEUOARR', false);
                $funzionarioArr = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESFUNZIONARIOARR', false);
                //
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESENDPOINT]", $uri['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESWSDL]", $wsdl['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESUSER]", $username['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESPASSWORD]", $password['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESCODICEUOPAR]", $codiceUOPar['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESFUNZIONARIOPAR]", $funzionarioPar['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESCODICEUOARR]", $codiceUOArr['CONFIG']);
                Out::valore($this->nameForm . "_CONFIG[WSKIBERNETESFUNZIONARIOARR]", $funzionarioArr['CONFIG']);
                //
                Out::hide($this->nameForm . "_ProtArrivo_UO_field");
                Out::hide($this->nameForm . "_ProtArrivo_DestinatarioUO_field");
                Out::hide($this->nameForm . "_ProtArrivo_Funzionario_field");
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_callProtocollaArrivo':
                        $WSClient = new itaKibernetesProtClient();
                        $this->setClientConfig($WSClient);
                        $param = array();
                        $param['Istat'] = $_POST[$this->nameForm . "_ProtArrivo_Istat"];
                        $param['Mittente'] = $_POST[$this->nameForm . "_ProtArrivo_Mittente"];
                        $param['Indirizzo'] = $_POST[$this->nameForm . "_ProtArrivo_Indirizzo"];
                        $param['Oggetto'] = $_POST[$this->nameForm . "_ProtArrivo_Oggetto"];
                        $param['FunzionarioDest'] = $_POST[$this->nameForm . "_ProtArrivo_FunzionarioDest"];
                        $param['AnnoPrec'] = $_POST[$this->nameForm . "_ProtArrivo_AnnoPrec"];
                        $param['ProtPrec'] = $_POST[$this->nameForm . "_ProtArrivo_ProtPrec"];
                        $param['FunzionarioDestSecCC'] = "false";
                        $ret = $WSClient->ws_Set4ProtocolloEntrata($param);
                        if (!$ret) {
                            if ($WSClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
                            } elseif ($WSClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $ret = $WSClient->getResult();
                        $risultato = print_r($ret, true);
                        Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->nameForm . '_Allegato1Agg_Image':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $randName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->nameForm . "_Allegato1Agg_NomeFile", $origFile);
                        Out::valore($this->nameForm . "_Allegato1Agg_Descrizione", $origFile);

                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumento1Agg = $base64File;
                        break;
                    case $this->nameForm . '_Allegato2Agg_Image':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $randName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->nameForm . "_Allegato2Agg_NomeFile", $origFile);
                        Out::valore($this->nameForm . "_Allegato2Agg_Descrizione", $origFile);

                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumento2Agg = $base64File;
                        break;
                    case $this->nameForm . '_callAggiungiAllegati':
                        $WSClient = new itaKibernetesProtClient();
                        $this->setClientConfig($WSClient);
                        $Allegati = array();
                        //
                        $principale1 = $_POST[$this->nameForm . "_Allegato1Agg_Principale"];
                        $Allegati[1]['Istat'] = $_POST[$this->nameForm . "_AddAllegati_Istat"];
                        $Allegati[1]['Numero'] = $_POST[$this->nameForm . "_AddAllegati_Numero"];
                        $Allegati[1]['Anno'] = $_POST[$this->nameForm . "_AddAllegati_Anno"];
                        $Allegati[1]['Image'] = $this->StreamDocumento1Agg;
                        $Allegati[1]['Filename'] = $_POST[$this->nameForm . "_Allegato1Agg_NomeFile"];
                        $Allegati[1]['Descrizione'] = $_POST[$this->nameForm . "_Allegato1Agg_Descrizione"];
                        $Allegati[1]['Principale'] = $principale1 ? "true" : "false";
                        //
                        $principale2 = $_POST[$this->nameForm . "_Allegato2Agg_Principale"];
                        $Allegati[2]['Istat'] = $_POST[$this->nameForm . "_AddAllegati_Istat"];
                        $Allegati[2]['Numero'] = $_POST[$this->nameForm . "_AddAllegati_Numero"];
                        $Allegati[2]['Anno'] = $_POST[$this->nameForm . "_AddAllegati_Anno"];
                        $Allegati[2]['Image'] = $this->StreamDocumento2Agg;
                        $Allegati[2]['Filename'] = $_POST[$this->nameForm . "_Allegato2Agg_NomeFile"];
                        $Allegati[2]['Descrizione'] = $_POST[$this->nameForm . "_Allegato2Agg_Descrizione"];
                        $Allegati[2]['Principale'] = $principale2 ? "true" : "false";
                        //
                        foreach ($Allegati as $key => $allegato) {
                            $ret = $WSClient->ws_SetAllegato4Protocollo($allegato);
                            if (!$ret) {
                                if ($WSClient->getFault()) {
                                    Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
                                } elseif ($WSClient->getError()) {
                                    Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
                                }
                                break;
                            }
                            $ret = $WSClient->getResult();
                            $risultato = print_r($ret, true);
                            Out::msgInfo("Allegato $key", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
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
        App::$utente->removeKey($this->nameForm . '_StreamDocumento1Agg');
        App::$utente->removeKey($this->nameForm . '_StreamDocumento2Agg');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
