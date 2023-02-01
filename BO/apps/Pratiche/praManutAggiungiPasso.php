<?php

/**
 *  Utilità per aggiungere un passo a tutti procediemnti
 * partendo da un passo sorgente
 *
 *
 * @category   Library
 * @package    /apps/Pratiche
 * @author     Andrea Bufarini
 * @author     Simone Franchi
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.05.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReader.interface.php';

//include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReaderMonologFile.class.php';


function praManutAggiungiPasso() {
    $praManutAggiungiPasso = new praManutAggiungiPasso();
    $praManutAggiungiPasso->parseEvent();
    return;
}

class praManutAggiungiPasso extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praManutAggiungiPasso";
    public $divRic = "praManutAggiungiPasso_divRicerca";
    public $itekey;
    public $logger;
    private $logFile;

    function __construct() {
        parent::__construct();
        try {
            //
            // carico le librerie
            //
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
        $this->itekey = App::$utente->getKey($this->nameForm . '_itekey');
        $this->logFile = App::$utente->getKey($this->nameForm . '_logFile');
        $this->logger = new itaPHPLogger(__CLASS__, false);
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_itekey', $this->itekey);
            App::$utente->setKey($this->nameForm . '_logFile', $this->logFile);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_LogVisualizza':

                        ItaLib::openDialog('utiLogViewerText', 'true');
                        $objModel = itaModel::getInstance('utiLogViewerText');
                        $objModel->setLogFile($this->logFile);
                        $objModel->setOptions(itaPHPLogReaderInterface::LOG_ORDER_ASC, 0, 10000);

                        $objModel->setEvent('openform');
                        $objModel->parseEvent();

                        break;
                    case $this->nameForm . '_LogScarica':

                        if (!file_exists($this->logFile)) {
                            Out::msgInfo("Attenzione", "Il file di Log " . $this->logFile . " non è stato trovato");
                            break;
                        }

                        Out::openDocument(
                                utiDownload::getUrl(
                                        'exportLogPassi_' . date('Ymd') . '.txt', $this->logFile, true
                                )
                        );

                        break;
                    case $this->nameForm . '_Conferma':

                        if (!$this->itekey) {
                            Out::msgInfo("Errore", "Scegliere un passo sorgente");
                            break;
                        }

                        if (!$this->controllo()) {
                            break;
                        }
                        if (!$this->controlloCustom($_POST[$this->nameForm . "_customClassPre"], $_POST[$this->nameForm . "_customClassPost"])) {
                            break;
                        }

                        $itepas_rec = $this->praLib->GetItepas($this->itekey, "itekey");
                        Out::msgQuestion("ATTENZIONE!", "Confermi l'aggiunta del passo: <b>" . $itepas_rec['ITEDES'] . "</b> in tutti i procedimenti amministrativi?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAggiunta', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAggiunta', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAggiunta':


                        if (!$this->aggiungiPasso()) {
                            break;
                        }
                        Out::msgInfo("Attenzione", "Elaborazione Terminata");

                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti", "returnAnapra");
//                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti");
                        break;
                    case $this->nameForm . '_Passo_butt':
                        if ($_POST[$this->nameForm . "_Procedimento"]) {
                            $where = "WHERE ITECOD = " . $_POST[$this->nameForm . "_Procedimento"];
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, "", '', 'asc');
                        } else {
                            Out::msgInfo("Ricerca passi", "Scegliere il procediemnto di riferimento");
                        }
                        break;
                    case $this->nameForm . '_ITECLT_butt':
                        praRic::praRicPraclt($this->nameForm, "RICERCA Tipo Passo");
                        break;
                    case $this->nameForm . '_ImportPassi':
                        if (!$this->controllo()) {
                            break;
                        }
                        if (!$this->controlloCustom($_POST[$this->nameForm . "_customClassPre"], $_POST[$this->nameForm . "_customClassPost"])) {
                            break;
                        }

                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadXML";

                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura model fallita");
                            break;
                        }
                        itaLib::openForm($model);
                        //$formObj->setReturnModel($this->nameForm);
                        //$formObj->setReturnEvent('returnPraIteevt');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();


//                        $_POST['event'] = 'openform';
//                        $_POST[$model . '_returnModel'] = $this->nameForm;
//                        $_POST[$model . '_returnEvent'] = "returnUploadXML";
//                        itaLib::openForm($model);
//                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//                        $model();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Procedimento':
                        if ($_POST[$this->nameForm . '_Procedimento']) {
                            $codice = $_POST[$this->nameForm . '_Procedimento'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anapra_rec = $this->praLib->GetAnapra($codice);
                            if ($Anapra_rec) {
                                Out::valore($this->nameForm . '_Procedimento', $Anapra_rec['PRANUM']);
                                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                            } else {
                                Out::valore($this->nameForm . '_DesProcedimento', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_ITECLT':
                        $codice = $_POST[$this->nameForm . '_ITECLT'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            Out::valore($this->nameForm . '_ITECLT', $Praclt_rec['CLTCOD']);
                            Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                        }
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
            case "returnItepas";
                $Itepas_rec = $this->praLib->GetItepas($_POST["retKey"], 'rowid');
                Out::valore($this->nameForm . '_Passo', $Itepas_rec['ITESEQ']);
                Out::valore($this->nameForm . '_DesPasso', $Itepas_rec['ITEDES']);
                Out::valore($this->nameForm . '_ITEPAS[ITEKEY]', $Itepas_rec['ITEKEY']);
                $this->itekey = $Itepas_rec['ITEKEY'];
                break;
            case "returnAnapra";
//                Out::msgInfo("POST", print_r($_POST,true));
                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['PRANUM']);
//                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['ROWID'], 'rowid');
                Out::valore($this->nameForm . '_Procedimento', $Anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                break;
            case "returnPraclt":
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                if ($Praclt_rec) {
                    Out::valore($this->nameForm . '_ITECLT', $Praclt_rec['CLTCOD']);
                    Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
                }
                break;

            case 'returnUploadXML':
                $XMLpassi = $_POST['uploadedFile'];

                if (!file_exists($XMLpassi)) {
                    Out::msgInfo("Errore", "Il file XML non è stato trovato");
                    break;
                }
                if (pathinfo($XMLpassi, PATHINFO_EXTENSION) != "xml") {
                    Out::msgInfo("Errore", "Il file caricato non è un file XML");
                    break;
                }

                //TODO: Controllo sulla struttura del file XML
//                $classPre = $this->formData[$this->nameForm . "_customClassPre"];


                $this->riportaPassiXML($XMLpassi);

//                $classPost = $this->formData[$this->nameForm . "_customClassPost"];
//
//                $this->customClassPost($classPost);
//                
                Out::msgInfo("Attenzione", "Elaborazione Terminata");


                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        App::$utente->removeKey($this->nameForm . '_itekey');
        App::$utente->removeKey($this->nameForm . '_logFile');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        $this->itekey = "";
        $this->logFile = "";
        $this->Nascondi();
        Out::show($this->nameForm . '_Conferma');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Procedimento');

        Out::html($this->nameForm . "_tipoRichiesta", "");
        Out::select($this->nameForm . '_tipoRichiesta', '1', $richiesta['ID'], '1', $richiesta['DESCVARIAZIONE']);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Conferma');
    }

    private function controlloCustom($customClassPre, $customClassPost) {

        if (!$this->controlloCustomGenerico($customClassPre, "PRE")) {
            return false;
        }

        if (!$this->controlloCustomGenerico($customClassPost, "POST")) {
            return false;
        }

        return true;
    }

    private function controlloCustomGenerico($customClass, $tipo = 'POST') {

        if ($customClass) {
            $posSep = strpos($customClass, '/');

            if (!$posSep) {
                Out::msgInfo("Attenzione", "La Custom Class $tipo Elaborazione inserita non ha la corretta sintassi. </br>La sintassi corretta è nomeclasse/nomemetodo");
                return false;
            }

            $arrayValori = explode('/', $customClass);
            if (!$arrayValori) {
                Out::msgInfo("Attenzione", "La Custom Class $tipo Elaborazione inserita non ha la corretta sintassi. </br>La sintassi corretta è nomeclasse/nomemetodo");
                return false;
            }
            $nomeClasse = $arrayValori[0];
            $nomeMetodo = $arrayValori[1];
            $nomeClasseReq = $nomeClasse . ".class.php";

            if (!file_exists(ITA_BASE_PATH . '/apps/Pratiche/customClass/migration/' . $nomeClasseReq)) {
                Out::msgStop("Error", "Classe $nomeClasseReq non trovata");
                return false;
            }

            include_once ITA_BASE_PATH . '/apps/Pratiche/customClass/migration/' . $nomeClasseReq;

            if (!class_exists($nomeClasse)) {
                Out::msgStop("Error", "Classe $nomeClasse non trovata");
                return false;
            }

            if (!method_exists($nomeClasse, $nomeMetodo)) {
                Out::msgStop("Error", "Metodo $nomeMetodo non trovato");
                return false;
            }
        }

        return true;
    }

    private function controllo() {
        $insertTo = $_POST[$this->nameForm . "_InserTo"];
        $cltCod = $_POST[$this->nameForm . "_ITECLT"];
        $tipoIns = $_POST[$this->nameForm . '_TipoInserimento'];

        if (!$insertTo && !$cltCod) {
            Out::msgInfo("Errore", "Scegliere un tipo di destinazione dove aggiungere il passo");
            return false;
        }

        if (!$tipoIns) {
            Out::msgInfo("Errore", "Scegliere un tipo di inserimento");
            return false;
        }


        return true;
    }

    private function aggiungiPasso() {
        $praLibPasso = new praLibPasso();

        $insertTo = $_POST[$this->nameForm . "_InserTo"];
        $cltCod = $_POST[$this->nameForm . "_ITECLT"];
        $tipoIns = $_POST[$this->nameForm . '_TipoInserimento'];

        /*
         * Trovo il passo da duplicare
         */
        $Itepas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEPAS WHERE ITEKEY ='$this->itekey'", false);
        if (!$Itepas_rec) {
            Out::msgStop("Attenzione!!", "Passo sorgente non trovato");
            return false;
        }

        /*
         * Controllo preliminare Espressioni
         */
        $intItecod = (string) intval($Itepas_rec['ITECOD']);
        if (strpos($Itepas_rec['ITEOBE'], $intItecod) !== false) {
            Out::msgError("Attenzione", "Nell'espressione <b>Obbligatorio Se</b> del passo Sorgente, sembra esserci <br>una condizione specifica per il procedimento <b>" . $Itepas_rec['ITECOD'] . "</b>.<br>VERIFICARE");
            return false;
        }
        if (strpos($Itepas_rec['ITEATE'], $intItecod) !== false) {
            Out::msgError("Attenzione", "Nell'espressione <b>Attivo Se</b> del passo Sorgente, sembra esserci <br>una condizione specifica per il procedimento <b>" . $Itepas_rec['ITECOD'] . "</b>.<br>VERIFICARE");
            return false;
        }

        /*
         * Trovo i dati aggiuntivi di quel passo da duplicare
         */
        $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEDAG WHERE ITECOD ='" . $Itepas_rec['ITECOD'] . "' AND ITEKEY='" . $Itepas_rec['ITEKEY'] . "'", true);

        /*
         * Mi trovo tutti i procedimenti degli sportelli on-line
         */
        //$sql = "SELECT * FROM ANAPRA WHERE ANAPRA.PRANUM<>'" . $Itepas_rec['ITECOD'] . "' AND (PRANUM='000824' || PRANUM='000825') ";
        $sql = "SELECT * FROM ANAPRA WHERE ANAPRA.PRANUM<>'" . $Itepas_rec['ITECOD'] . "' AND ANAPRA.PRATPR = 'ONLINE'   AND PRAAVA='' AND PRAOFFLINE = 0 ORDER BY PRANUM ";
        $Anapra_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        /*
         * INIZIO GESTIONE FILE DI LOG
         * Crea cartella di appoggio per la sessione corrente  itaLib::getAppsTempPath()
         */
        $pathFile = itaLib::createAppsTempPath('aggiungiPassi');
        $this->logFile = $pathFile . "/elaborazione-" . date('Ymd') . "-" . date('Hi') . '.txt';

        $this->logger->pushFile($this->logFile);
        $this->logger->info('Inizio Aggiunta passi alle ore ' . date('H:i:s'));

        $i = 0;
        $tot = count($Anapra_tab);
        foreach ($Anapra_tab as $Anapra_rec) {
            $itepas_appoggio = $Itepas_rec;
            $itepas_appoggio["ITECOD"] = $Anapra_rec['PRANUM'];
            $itepas_appoggio["ROWID"] = 0;

            $itepas_rec_dest = $this->getSequenzaCorrente($insertTo, $cltCod, $Anapra_rec['PRANUM']);
            if (!$itepas_rec_dest) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovato passo di riferimento per l'aggiunta");
                continue;
            }

            $seqAtt = $itepas_rec_dest['ITESEQ'];
            if ($seqAtt < 1) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovata sequenza passo di riferimento per l'aggiunta");
                continue;
            }

            /*
             * Calcolo la sequenza di Inserimento
             */
            $seqIns = $this->getSequenzaInsert($tipoIns, $seqAtt);
            if ($seqIns < 1) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovato numero sequenza di inserimento ");
                continue;
            }

            $itepas_appoggio["ITESEQ"] = $seqIns;
            $itepas_appoggio["ITEKEY"] = $this->praLib->keyGenerator($itepas_appoggio["ITECOD"]);
            $itepas_appoggio["ITEATE"] = $praLibPasso->sistemaITEATE($itepas_appoggio["ITEATE"], $itepas_appoggio["ITEKEY"]);

            $insert_Info = "Inserisco passo " . $Itepas_rec['ITECOD'] . " - " . $Itepas_rec['ITEDES'] . " su procedimento: " . $Anapra_rec['PRANUM'];
            if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $itepas_appoggio, $insert_Info)) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non effettuato l'aggiunta del passo");
                continue;
            }

            /*
             * Preparo i nuovi record dei dati aggiuntivi e li inserisco
             */
            foreach ($Itedag_tab as $Itedag_rec) {
                $itedag_appoggio = $Itedag_rec;
                $itedag_appoggio["ROWID"] = 0;
                $itedag_appoggio["ITECOD"] = $Anapra_rec['PRANUM'];
                $itedag_appoggio["ITEKEY"] = $itepas_appoggio["ITEKEY"];
                $insert_Info = "Inserisco campi aggiuntivi su procedimento: " . $Anapra_rec['PRANUM'];
                if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $itedag_appoggio, $insert_Info)) {
                    $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non effettuato il caricamento dei campi aggiuntivi");
                    break;
                }
            }

            if ($tipoIns == 'S') {
                // Caso di sostituzione - Si cancella il passo con ITESEQ = $seqAtt e i dati aggiuntivi associati.
                if (!$this->cancellaPasso($Anapra_rec['PRANUM'], $itepas_rec_dest['ROWID'])) {
                    // Il Log è gestito nel metodo cancellaPasso
                    continue;
                }
            }


            /*
             * Rioridno i passi dopo la procedura
             */
            $this->praLib->ordinaPassiProc($Anapra_rec['PRANUM']);

            $this->logger->info("Procedimento " . $Anapra_rec['PRANUM'] . " sistemato CORRETTAMENTE");
            $i = $i + 1;
        }

        $this->logger->info('Elaborazione Aggiunta passi terminata alle ore ' . date('H:i:s'));
        $this->logger->info("Elaborazione Terminata. Sono stati sistemati $i procedimenti su un totale di $tot.");

        return true;
    }

    /**
     * 
     * @param type $insertTo  --> Ricerca per Sequenza passo
     * @param type $cltCod --> Ricerca per Tipo di Passo
     * @param type $pranum --> Numero del procedimento 
     * @return type
     */
    private function getSequenzaCorrente($insertTo, $cltCod, $pranum) {
        $itepas_rec = array();
        if ($insertTo) {
            // Cerco il passo (ITEPAS) che contiene la sequanza inserita
            $itepas_rec = $this->praLib->GetItepas($pranum, 'codice', false, $where = " AND ITESEQ = " . $insertTo);
        } else if ($cltCod) {
            // Cerco il passo (ITEPAS) che contiene il codice Passo inserito
            $itepas_rec = $this->praLib->GetItepas($pranum, 'codice', false, $where = " AND ITECLT = '" . $cltCod . "'");
        }

        if ($itepas_rec) {
            return $itepas_rec;
        }
    }

    private function getSequenzaInsert($tipoIns, $seqAtt) {
        $sequenza = 0;

        if ($tipoIns == 'P') {
            $sequenza = $seqAtt - 5;
        } else if ($tipoIns == 'D') {
            $sequenza = $seqAtt + 5;
        } else if ($tipoIns == 'S') {
            $sequenza = $seqAtt;
        }


        return $sequenza;
    }

    private function cancellaPasso($pranum, $IdItepasCanc) {

        $anapra_rec = $this->praLib->GetAnapra($pranum);
        if (!$anapra_rec) {
            $this->logger->error("Procedimento " . $pranum . " non effettuata la cancellazione del passo con id " . $IdItepasCanc . " perchè non trovato il procedimento");

            return false;
        }

        $itepas_tab = $this->praLib->GetItepas($pranum, 'codice', true);
        if (!$itepas_tab) {
            $this->logger->error("Procedimento " . $pranum . " non effettuata la cancellazione del passo con id " . $IdItepasCanc . " perchè non trovati i passi del procedimento");

            return false;
        }

        $itapas_canc = $this->praLib->GetItepas($IdItepasCanc, 'rowid');
        if (!$itapas_canc) {
            $this->logger->error("Procedimento " . $pranum . " non effettuata la cancellazione del passo con id " . $IdItepasCanc . " perchè non trovato");
            return false;
        }
        $arrayPassiSel = array($itapas_canc);


        $praLibPasso = new praLibPasso();
        $praLibPasso->cancellaPassi($arrayPassiSel, $anapra_rec['ROWID'], $pranum, $itepas_tab);

        if ($praLibPasso->getErrCode()) {
            $this->logger->error("Procedimento " . $pranum . " non effettuata la cancellazione del passo con id " . $IdItepasCanc . " per il seguente errore: " . $praLibPasso->getErrMessage());
            return false;
        }

        return true;
    }

    private function riportaPassiXML($XMLpassi) {
        $praLibPasso = new praLibPasso();


        $insertTo = $this->formData[$this->nameForm . "_InserTo"];
        $cltCod = $this->formData[$this->nameForm . "_ITECLT"];
        $tipoIns = $this->formData[$this->nameForm . '_TipoInserimento'];
        $sistemaCondizioni = $this->formData[$this->nameForm . '_sistemaCondizioni'];

        /*
         * Mi trovo tutti i procedimenti degli sportelli on-line
         */
        //$sql = "SELECT * FROM ANAPRA WHERE ANAPRA.PRANUM = '000824' || PRANUM='000825'";
        $sql = "SELECT * FROM ANAPRA WHERE ANAPRA.PRATPR = 'ONLINE'  AND PRAAVA='' AND PRAOFFLINE = 0  ORDER BY PRANUM ";
        $Anapra_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if (!$Anapra_tab) {
            Out::msgInfo("Attenzione!!", "Procedimenti non trovati");
            return;
        }

        /*
         * INIZIO GESTIONE FILE DI LOG
         * Crea cartella di appoggio per la sessione corrente  itaLib::getAppsTempPath()
         */
        $pathFile = itaLib::createAppsTempPath('aggiungiPassiXML');
        $this->logFile = $pathFile . "/elaborazione-" . date('Ymd') . "-" . date('Hi') . '.txt';

        $this->logger->pushFile($this->logFile);
        $this->logger->info('Inizio Aggiunta passi da XML alle ore ' . date('H:i:s'));

        $i = 0;
        $tot = count($Anapra_tab);
        foreach ($Anapra_tab as $Anapra_rec) {

            $classPre = $this->formData[$this->nameForm . "_customClassPre"];

            $retPre = $this->runCustomClass($classPre, $Anapra_rec['PRANUM']);
            if (!$retPre) {
                $this->logger->error("Esecuzione PreElaborazione procedimento n. " . $Anapra_rec['PRANUM'] . " FALLITA. ");
            }

            $itepas_rec_dest = $this->getSequenzaCorrente($insertTo, $cltCod, $Anapra_rec['PRANUM']);
            if (!$itepas_rec_dest) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovato passo di riferimento per l'aggiunta");
                continue;
            }

            $seqAtt = $itepas_rec_dest['ITESEQ'];
            if ($seqAtt < 1) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovata sequenza passo di riferimento per l'aggiunta");
                continue;
            }

            /*
             * Calcolo la sequenza di Inserimento
             */
            $seqIns = $this->getSequenzaInsert($tipoIns, $seqAtt);
            if ($seqIns < 1) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non trovato numero sequenza di inserimento ");
                continue;
            }

            if (!$praLibPasso->importaPassiXML($Anapra_rec['PRANUM'], $XMLpassi, $seqIns)) {
                $this->logger->error("Procedimento " . $Anapra_rec['PRANUM'] . " non effettuato il caricamento. " . $praLibPasso->getErrMessage());
            } else {
                $this->logger->info("Procedimento " . $Anapra_rec['PRANUM'] . " sistemato CORRETTAMENTE");
                $i = $i + 1;
            }

            $arrIdPassiInseriti = $praLibPasso->getArrIdPassiImportati();

            if ($tipoIns == 'S') {
                // Caso di sostituzione - Si cancella il passo con ITESEQ = $seqAtt e i dati aggiuntivi associati.
                if (!$this->cancellaPasso($Anapra_rec['PRANUM'], $itepas_rec_dest['ROWID'])) {
                    // Il Log è gestito nel metodo cancellaPasso
                    continue;
                }
            }


            /*
             * Riordino i passi dopo la procedura
             */
            $this->praLib->ordinaPassiProc($Anapra_rec['PRANUM']);


            $classPost = $this->formData[$this->nameForm . "_customClassPost"];

            $ret = $this->runCustomClass($classPost, $Anapra_rec['PRANUM']);
            if (!$ret) {
                $this->logger->error("Salto non trovato per il Procedimento " . $Anapra_rec['PRANUM'] . ". Sistemare Manualmente.");
            }

            /*
             * Se c'è il Flag Sistemo le Condizioni
             */
            if ($sistemaCondizioni == 1) {
                foreach ($arrIdPassiInseriti as $rowidPasso) {
                    $itepas_rec_aggiunto = $this->praLib->GetItepas($rowidPasso, 'rowid');
                    if (!$itepas_rec_aggiunto) {
                        $this->logger->error("Record ITEPAS con rowid $rowidPasso non trovato per il Procedimento " . $Anapra_rec['PRANUM']);
                        return false;
                    }
                    $itepas_rec_aggiunto['ITEATE'] = $itepas_rec_dest['ITEATE'];
                    $itepas_rec_aggiunto['ITEOBE'] = $itepas_rec_dest['ITEOBE'];
                    $nrow = ItaDB::DBUpdate($this->PRAM_DB, "ITEPAS", "ROWID", $itepas_rec_aggiunto);
                    if ($nrow == -1) {
                        $this->logger->error("Aggiornamento Record ITEPAS con rowid $rowidPasso Fallito per il Procedimento " . $Anapra_rec['PRANUM']);
                        return false;
                    }
                }
            }
        }

        $this->logger->info('Elaborazione Aggiunta passi terminata alle ore ' . date('H:i:s'));
        $this->logger->info("Elaborazione Terminata. Sono stati sistemati $i procedimenti su un totale di $tot.");
    }

    private function runCustomClass($customClass, $pranum) {

        if (!$customClass) {
            return true;
        }

        $arrayValori = explode('/', $customClass);
        if (!$arrayValori) {
            return false;
        }

        $nomeClasse = $arrayValori[0];
        $nomeMetodo = $arrayValori[1];
        $nomeClasseReq = $nomeClasse . ".class.php";



        if (file_exists(ITA_BASE_PATH . '/apps/Pratiche/customClass/migration/' . $nomeClasseReq)) {
            include_once ITA_BASE_PATH . '/apps/Pratiche/customClass/migration/' . $nomeClasseReq;
        }

        if (!class_exists($nomeClasse)) {
            Out::msgStop("Error", "Classe $nomeClasse non trovata");
            return false;
        }

        if (!method_exists($nomeClasse, $nomeMetodo)) {
            Out::msgStop("Error", "Metodo $nomeMetodo non trovato");
            return false;
        }

        $classe = new $nomeClasse();
        return $classe->$nomeMetodo($pranum);
    }

}
