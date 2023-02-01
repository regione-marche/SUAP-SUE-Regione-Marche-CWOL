<?php

/**
 *  Utilità per aggiungere un passo a tutti procediemnti
 * partendo da un passo sorgente
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Andrea Bufarini
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.05.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReader.interface.php';

function praManutModificaControlli() {
    $praManutModificaControlli = new praManutModificaControlli();
    $praManutModificaControlli->parseEvent();
    return;
}

class praManutModificaControlli extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praManutModificaControlli";
    public $divRic = "praManutModificaControlli_divRicerca";
    // public $divRis = "praManutCambiaPDF_divRisultato";
    //public $itekey;
    //public $gridProcedimenti = "praManutCambiaPDF_gridProcedimenti";
    public $fileLog;
    public $logger;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
        $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
        $this->logger = new itaPHPLogger(__CLASS__, false);
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        if ($_POST[$this->nameForm . '_Controllo'] == '' || $_POST[$this->nameForm . '_Operazione'] == '' || $_POST[$this->nameForm . '_AttivaEspressione'] == '' || $this->formData[$this->nameForm . '_TipoPasso'] == '') {
                            Out::msgInfo("Attenzione", "Compilare tutti i campi.");
                            break;
                        }

                        $tipoPasso = $this->formData[$this->nameForm . '_TipoPasso'];

                        /*
                         * Query per trovare i passi da elaborare
                         */
                        $sql = $this->CreaSql($tipoPasso);
                        $itepas_tab = $this->praLib->getGenericTab($sql);
                        if (!$itepas_tab) {
                            Out::msgInfo("Attenzione", "Passi non trovati.");
                            break;
                        }

                        $totPassi = count($itepas_tab);

                        switch ($this->formData[$this->nameForm . '_Operazione']) {
                            case "A":
                                $operazione = "Accodamento";
                                break;
                            case "S":
                                $operazione = "Sostituzione";
                                break;
                        }

                        Out::msgQuestion("ATTENZIONE!", "Sono stati trovati <b>$totPassi</b> passi con tipo: <b>$tipoPasso</b>.<br>Si vuol proseguire con l'operazione di <b>$operazione</b> per tutti i passi estratti?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaModifica', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaModifica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ApriAttivaExpr':
                        if ($_POST[$this->nameForm . '_Controllo'] == '') {
                            Out::msgInfo("Attenzione", "Selezionare il tipo di Controllo.");
                            break;
                        }

                        $tipoControllo = $this->formData[$this->nameForm . '_Controllo'];
                        $espressione = $this->formData[$this->nameForm . '_AttivaEspressioneNoDecod'];
                        $model = 'praPassoProcExpr';
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => "Espressione $tipoControllo da modificare",
                            $tipoControllo => $espressione,
                        );
                        $_POST['event'] = 'openform';
                        $_POST['tipo'] = $tipoControllo; //"ITEATE";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnAttivaExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_svuotaExpr':
                        Out::valore($this->nameForm . '_AttivaEspressione', "");
                        Out::valore($this->nameForm . '_AttivaEspressioneNoDecod', "");
                        break;
//                    case $this->nameForm . '_AltraRichiesta':
//                        $this->OpenRicerca();
//                        break;
                    case $this->nameForm . '_ConfermaModifica':
                        $tipoPasso = $this->formData[$this->nameForm . '_TipoPasso'];
                        if ($tipoPasso == "") {
                            Out::msgInfo("Attenzione", "Tipo Passo non definito");
                            break;
                        }
                        $tipoControllo = $this->formData[$this->nameForm . '_Controllo'];
                        $operatore = $this->formData[$this->nameForm . '_Operatore'];

                        /*
                         * Query per trovare i passi da elaborare
                         */
                        $sql = $this->CreaSql($tipoPasso);
                        $itepas_tab = $this->praLib->getGenericTab($sql);
                        if (!$itepas_tab) {
                            Out::msgInfo("Attenzione", "Passi non trovati.");
                            break;
                        }

                        /*
                         * Inizializzo il File di LOG
                         */
                        $pathFile = itaLib::createAppsTempPath('ModificaControlli');
                        $this->fileLog = $pathFile . "/elaborazione-" . date('Ymd') . "-" . date('Hi') . '.txt';

                        /*
                         * Inizio Scrittura File di LOG
                         */
                        $this->logger->pushFile($this->fileLog);
                        $this->logger->info('Inizio Modifica Controlli alle ore ' . date('H:i:s'));

                        /*
                         * Inizio Elaborazione
                         */
                        $i = 0;
                        $espressione = $this->formData[$this->nameForm . '_AttivaEspressioneNoDecod'];
                        $totPassi = count($itepas_tab);
                        foreach ($itepas_tab as $itepas_rec) {
                            switch ($this->formData[$this->nameForm . '_Operazione']) {
                                case "A":

                                    /*
                                     * Se è presente il controllo:
                                     * - Riordino le chiavi dell'array delle nuove espressioni
                                     * - Assegno l'operatore con quello selezionato
                                     * - Unisco l'array delle vecchie exp con quelle nuove
                                     * - Serializzo il campo e lo assegno
                                     */
                                    if ($itepas_rec[$tipoControllo]) {
                                        $arrCampoOld = unserialize($itepas_rec[$tipoControllo]);
                                        $arrCampoNew = unserialize($espressione);
                                        array_values($arrCampoNew);
                                        $arrCampoNew[0]['OPERATORE'] = $operatore;
                                        $itepas_rec[$tipoControllo] = serialize(array_merge($arrCampoOld, $arrCampoNew));
                                    } else {
                                        $itepas_rec[$tipoControllo] = $espressione;
                                    }
                                    file_put_contents("C:/tmp/expAccoda.log", $itepas_rec['ITECOD'] . ":" . $tipoControllo . "-" . $itepas_rec[$tipoControllo] . "\n", FILE_APPEND);
                                    break;
                                case "S":
                                    $itepas_rec[$tipoControllo] = $espressione;
                                    file_put_contents("C:/tmp/expSost.log", $itepas_rec['ITECOD'] . ":" . $tipoControllo . "-" . $itepas_rec[$tipoControllo] . "\n", FILE_APPEND);
                                    break;
                            }

                            /*
                             * Aggiorno il Record con il controllo modificato
                             */
                            $update_Info = "Oggetto: Aggiornamento pratica: $this->currGesnum dopo cancellazione evento";
                            if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepas_rec, $update_Info)) {
                                $this->logger->error("Errore Aggiornamento Controllo $tipoControllo Passo " . $itepas_rec['ITESEQ'] . " proc " . $itepas_rec['ITECOD'] . ".");
                            }

                            /*
                             * Scrivo nel LOG il buon esito dell'operazione
                             */
                            $this->logger->info("Aggiornato Controllo $tipoControllo Passo " . $itepas_rec['ITESEQ'] . " proc " . $itepas_rec['ITECOD'] . ".");
                            $i++;
                        }

                        /*
                         * Fine Scrittura LOG
                         */
                        $this->logger->info('Fine Modifica Controlli alle ore ' . date('H:i:s'));

                        /*
                         * Visualizzazione bottoni LOG
                         */
                        Out::show($this->nameForm . '_LogVisualizza');
                        Out::show($this->nameForm . '_LogScarica');

                        /*
                         * Messaggio Finale
                         */
                        Out::msgInfo("Modifica Controlli", "Aggiornati correttamente $i passi su $totPassi.");
                        break;
                    case $this->nameForm . '_LogVisualizza':
                        ItaLib::openDialog('utiLogViewerText', 'true');
                        $objModel = itaModel::getInstance('utiLogViewerText');
                        $objModel->setLogFile($this->fileLog);
                        $objModel->setOptions(itaPHPLogReaderInterface::LOG_ORDER_ASC, 0, 10000);
                        $objModel->setEvent('openform');
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_LogScarica':
                        if (!file_exists($this->fileLog)) {
                            Out::msgInfo("Attenzione", "Il file di Log " . $this->fileLog . " non è stato trovato");
                            break;
                        }
                        Out::openDocument(
                                utiDownload::getUrl(
                                        'exportModificaControlli_' . date('Ymd') . '.txt', $this->fileLog, true
                                )
                        );
                        break;
                    case $this->nameForm . '_TipoPasso_butt':
                        praRic::praRicPraclt($this->nameForm, "RICERCA Tipo Passo");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TipoPasso':
                        $codice = $_POST[$this->nameForm . '_TipoPasso'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice);
                            Out::valore($this->nameForm . '_TipoPasso', $Praclt_rec['CLTCOD']);
                            Out::valore($this->nameForm . '_descPasso', $Praclt_rec['CLTDES']);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Operazione':
                        Out::hide($this->nameForm . '_divRadio');
                        $ope = $_POST[$this->nameForm . '_Operazione'];
                        if ($ope == 'A') {
                            Out::show($this->nameForm . '_divRadio');
                            Out::attributo($this->nameForm . "_FlagAnd", "checked", "0", "checked");
                        }
                        break;
                }
                break;
            case 'returnAttivaExpr' :
                $tipoControllo = $this->formData[$this->nameForm . '_Controllo'];
                Out::valore($this->nameForm . '_AttivaEspressione', $this->praLib->DecodificaControllo($_POST['dati'][$tipoControllo]));
                Out::valore($this->nameForm . '_AttivaEspressioneNoDecod', $_POST['dati']['ITEATE']);
                break;
            case "returnPraclt":
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                if ($Praclt_rec) {
                    Out::valore($this->nameForm . '_TipoPasso', $Praclt_rec['CLTCOD']);
                    Out::valore($this->nameForm . '_descPasso', $Praclt_rec['CLTDES']);
                }
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_fileLog');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        $this->fileLog = "";
        $this->Nascondi();
        Out::show($this->nameForm . '_Conferma');
        Out::show($this->nameForm);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_LogVisualizza');
        Out::hide($this->nameForm . '_LogScarica');
        Out::hide($this->nameForm . '_divRadio');
    }

    private function CreaCombo() {
        Out::html($this->nameForm . "_Controllo", "");
        Out::html($this->nameForm . "_Operazione", "");

        Out::select($this->nameForm . '_Controllo', 1, "", "1", "");
        Out::select($this->nameForm . '_Controllo', 1, "ITEATE", "0", "ITEATE (Attivo Se)");
        Out::select($this->nameForm . '_Controllo', 1, "ITEOBL", "0", "ITEOBL (Obbligatorio Se)");

        Out::select($this->nameForm . '_Operazione', 1, "", "1", "");
        Out::select($this->nameForm . '_Operazione', 1, "S", "0", "Sostituzione");
        Out::select($this->nameForm . '_Operazione', 1, "A", "0", "Accodamento");
    }

    private function CreaSql($tipoPasso) {
        $sql = "SELECT
                    ITEPAS.*
                FROM
                    ITEPAS
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM = ITEPAS.ITECOD
                WHERE
                    ITECLT = '$tipoPasso' AND
                    PRAOFFLINE = 0 AND
                    PRAAVA = ''
                ORDER BY
                    ITECOD, ITESEQ
                ";
        return $sql;
    }

}
