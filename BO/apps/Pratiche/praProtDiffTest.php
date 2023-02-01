<?php

/**
 *
 * TEST PROTOCOLLAZIONE DIFFERITA
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    10.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once(ITA_BASE_PATH . '/apps/Pratiche/praRemoteManager.class.php');

function praProtDiffTest() {
    $praProtDiffTest = new praProtDiffTest();
    $praProtDiffTest->parseEvent();
    return;
}

class praProtDiffTest extends itaModel {

    public $nameForm = "praProtDiffTest";
    public $praLib;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->nameForm, "", true, "desktopBody");
                Out::show($this->nameForm);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_callElaboraCoda':

                        $praRemoteManager = $this->getRemoteManager();
                        try {
                            $retElaboraCoda = $praRemoteManager->elaboraCoda();
                        } catch (Exception $e) {
                            Out::msgSop("Exception", $e->getMessage());
                            return;
                        }
                        Out::msgInfo("Risultato", print_r($retElaboraCoda, true));
                        break;
                    case $this->nameForm . '_callNotificaProtocollazione':


                        if ($_POST[$this->nameForm . "_numero_not"] == "" || $_POST[$this->nameForm . "_anno_not"] == "") {
                            Out::msgInfo("Attenzione", "Compilare entrambi i campi");
                            break;
                        }

                        $numero = str_pad($_POST[$this->nameForm . "_numero_not"], 6, "0", STR_PAD_LEFT);
                        $pratica = $_POST[$this->nameForm . "_anno_not"] . $numero;

//                        Out::msgInfo("Test", "Notifica Protocollazione Pratica $pratica");
//                        break;

                        $praRemoteManager = $this->getRemoteManager();
                        try {
                            $retNotifica = $praRemoteManager->notificaProtocollazioneRichiesta("2018000176");
                        } catch (Exception $e) {
                            Out::msgSop("Exception", $e->getMessage());
                            return;
                        }

                        if ($retNotifica['Status'] == "-1") {
                            Out::msgStop("Errore", $retNotifica['Message']);
                        }
                        Out::msgInfo("info invio Mail", print_r($retNotifica, true));
                        break;
                    case $this->nameForm . '_callGetArrayDati':
                        $praRemoteManager = $this->getRemoteManager();
                        if ($_POST[$this->nameForm . "_numero"] == "" || $_POST[$this->nameForm . "_anno"] == "") {
                            Out::msgInfo("Attenzione", "Compilare entrambi i campi");
                            break;
                        }
                        $numero = str_pad($_POST[$this->nameForm . "_numero"], 6, "0", STR_PAD_LEFT);
                        $richiesta = $_POST[$this->nameForm . "_anno"] . $numero;
                        //
                        $retDati = $praRemoteManager->getArrayDati($richiesta);
                        if ($retDati['Status'] == "-1") {
                            Out::msgStop("Errore lettura Dati", $retDati['Message']);
                            break;
                        }

                        $gesnum = $retDati['GESNUM'];
                        $propak = $retDati['PROPAK'];
                        if ($retDati['Acquisizione'] == false) {
                            $retAcquisisci = $praRemoteManager->acquisizioneRichiesta($retDati['DatiRichiesta']);
                            if ($retAcquisisci['Status'] == "-1") {
                                Out::msgStop("Errore acquisizione", $retAcquisisci['Message']);
                                break;
                            }
                            $gesnum = $retAcquisisci['GESNUM'];
                            $propak = $retAcquisisci['PROPAK'];
                        }

                        if ($gesnum == "") {
                            Out::msgStop("Errore", "Numero Pratica non trovato");
                            break;
                        }

                        if ($propak) {
                            $retProt = $praRemoteManager->protocollazionePasso($propak, $retDati['Protocollazione']);
                        } else {
                            $retProt = $praRemoteManager->protocollazionePratica($gesnum, $retDati['Protocollazione']);
                        }


//                        $retProt = $praRemoteManager->protocollazionePratica($retAcquisisci['GESNUM']);
                        if ($retProt['Status'] == "-1") {
                            Out::msgStop("Errore Protocollazione", $retProt['Message']);
                            break;
                        }
                        $numProt = $retProt['RetValue']['DatiProtocollazione']['proNum']['value'];
                        $annoProt = $retProt['RetValue']['DatiProtocollazione']['Anno']['value'];
                        Out::msgInfo("Richieste on-line", "Richiesta n. $richiesta acquisita correttamente col protocollo N. $numProt/$annoProt");
                        break;
                    case $this->nameForm . '_callGetCtrRichieste':
                        $praRemoteManager = $this->getRemoteManager();
                        $ret = $praRemoteManager->getElencoRichiesteAttesaProtocollazione();
                        if (!$ret['Richieste']) {
                            Out::msgInfo("Attenzione", "Non ci sono richieste on-line in attesa di protocollazione.");
                            break;
                        }
                        $msg = "";
                        foreach ($ret['Richieste'] as $Richieste_rec) {
                            $retDati = $praRemoteManager->getArrayDati($Richieste_rec['RICNUM']);
                            if ($retDati['Status'] == "-1") {
                                Out::msgStop("Errore lettura Dati", $retDati['Message']);
                                break;
                            }
                            $retAcquisisci = $praRemoteManager->acquisizioneRichiesta($retDati['DatiRichiesta']);
                            if ($retAcquisisci['Status'] == "-1") {
                                Out::msgStop("Errore acquisizione", $retAcquisisci['Message']);
                                break;
                            }
                            //$retProt = $praRemoteManager->protocollazionePratica($retAcquisisci['GESNUM']);
                            $retProt = $praRemoteManager->protocollazionePratica($retAcquisisci);
                            if ($retProt['Status'] == "-1") {
                                Out::msgStop("Errore Protocollazione", $retProt['Message']);
                                break;
                            }
                            $numProt = $retProt['RetValue']['DatiProtocollazione']['proNum']['value'];
                            $annoProt = $retProt['RetValue']['DatiProtocollazione']['Anno']['value'];
                            $msg .= "Richiesta n. " . $Richieste_rec['RICNUM'] . " acquisita correttamente col protocollo N. $numProt" . "/" . $annoProt . "<br>";
                        }
                        if ($msg) {
                            Out::msgInfo("Richieste on-line", $msg);
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

    private function getRemoteManager() {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        
        $filent_rec_34 = $praLib->GetFilent(34);
        $filent_rec_35 = $praLib->GetFilent(35);
        $filent_rec_36 = $praLib->GetFilent(36);
        $filent_rec_37 = $praLib->GetFilent(37);
        $filent_rec_38 = $praLib->GetFilent(38);
        
        $config['wsEndpoint'] = $filent_rec_37['FILVAL'];
        $config['wsWsdl'] = $filent_rec_38['FILVAL'];
        $config['wsNamespace'] = "";
        $praLib = null;
        $praRemoteManager = new praRemoteManager();
        $praRemoteManager->setDomain($filent_rec_36['FILVAL']);
        $praRemoteManager->setWsUser($filent_rec_34['FILVAL']);
        $praRemoteManager->setWsPassword($filent_rec_35['FILVAL']);
        $praRemoteManager->setClientConfig($config);
        return $praRemoteManager;
    }

}
