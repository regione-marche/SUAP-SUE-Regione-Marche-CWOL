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

function praManutCambiaPDF() {
    $praManutCambiaPDF = new praManutCambiaPDF();
    $praManutCambiaPDF->parseEvent();
    return;
}

class praManutCambiaPDF extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praManutCambiaPDF";
    public $divRic = "praManutCambiaPDF_divRicerca";
    public $divRis = "praManutCambiaPDF_divRisultato";
    public $itekey;
    public $gridProcedimenti = "praManutCambiaPDF_gridProcedimenti";
    public $fileLog;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
        $this->itekey = App::$utente->getKey($this->nameForm . '_itekey');
        $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_itekey', $this->itekey);
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praManutCampiAggiuntivi_" . time() . ".log";
                $this->scriviLog("Avvio Programma Inserimento passi per Camera di Commercio");
                $this->OpenRicerca();

                $html = "<br>Il file di log è nella cartella " . $this->fileLog . "</br>";
                Out::html($this->nameForm . "_divFileLog", $html, 'append');

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        TableView::clearGrid($this->gridProcedimenti);
                        $ita_grid01 = new TableView($this->gridProcedimenti, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $this->creaSql()));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(20000);
                        $ita_grid01->setSortIndex('ITECOD');
                        $ita_grid01->getDataPage('json');
                        Out::hide($this->divRic, '');
                        Out::show($this->divRis, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Sostituisci');
                        Out::show($this->nameForm . '_AltraRichiesta');
                        break;
                    case $this->nameForm . '_AltraRichiesta':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Sostituisci':
                        $itepas_new = $this->praLib->GetItepas($this->itekey, "itekey");
                        $itedag_new = $this->praLib->GetItedag($this->itekey, 'itekey', true, '');
                        $itepasTab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->creaSql(), true);
                        $aggiornati = 0;
                        foreach ($itepasTab as $itepas) {
                            $itepasDaModificare = $this->praLib->GetItepas($itepas['ITEKEY'], 'itekey', false, '');
                            if ($itepasDaModificare) {
                                if ($_POST[$this->nameForm . "_NewPDF"] != '') {
                                    $itepasDaModificare['ITEWRD'] = $itepas_new['ITEWRD'];
                                    $update_Info = 'Oggetto: Sostituzione file pdf su procedimento ' . $itepasDaModificare['ITECOD'] . ' sequenza ' . $itepasDaModificare['ITESEQ'];
                                    if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepasDaModificare, $update_Info)) {

                                        $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": ERRORE AGGIORNAMENTO ITEWRD");

                                        Out::msgStop('ATTENZIONE', 'Errore in aggiornamento del procedimento n. ' . $itepasDaModificare['ITECOD'] . ' sequenza ' . $itepasDaModificare['ITESEQ']);
                                        break;
                                    }

                                    $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": AGGIORNATO ITEWRD");
                                }
                                //
                                //  cancello ITEDAG
                                //
                                $itedagDaCancellare = $this->praLib->GetItedag($itepasDaModificare['ITEKEY'], 'itekey', true, '');
                                if ($itedagDaCancellare) {
                                    foreach ($itedagDaCancellare as $itedag) {
                                        $delete_Info = 'Oggetto: Cancellazione ITEDAG del procedimento ' . $itedag['ITECOD'] . ' sequenza ' . $itedag['ITDSEQ'];
                                        if (!$this->deleteRecord($this->PRAM_DB, 'ITEDAG', $itedag['ROWID'], $delete_Info)) {

                                            $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": ERRORE DI CANCELLAZIONE CAMPI AGGIUNTIVI");

                                            Out::msgStop('ATTENZIONE', 'Errore in cancellazione ITEDAG del procedimento n. ' . $itedag['ITECOD'] . ' sequenza ' . $itedag['ITDSEQ']);
                                            break;
                                        }
                                    }
                                    
                                    $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": CANCELLATI CAMPI AGGIUNTIVI");
                                    
                                }
                                //
                                //  registro ITEDAG
                                //
                                foreach ($itedag_new as $itedag) {
                                    $itedag['ITECOD'] = $itepasDaModificare['ITECOD'];
                                    $itedag['ITEKEY'] = $itepasDaModificare['ITEKEY'];
                                    $itedag['ROWID'] = 0;
                                    $insert_Info = "Inserisco campi aggiuntivi su procedimento: " . $itepasDaModificare['ITECOD'];
                                    if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $itedag, $insert_Info)) {
                                        
                                        $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": ERRORE DI REGISTRAZIONE CAMPI AGGIUNTIVI");
                                        
                                        Out::msgStop('Inserimento data set', 'Inserimento dati aggiuntivi fallito del procedimento n. ' . $itedag['ITECOD'] . ' sequenza ' . $itedag['ITDSEQ']);
                                        break;
                                    }
                                }
                                
                                $this->scriviLog("PROCEDIMENTO " . $itepasDaModificare['ITECOD'] . ": REGISTRATI CAMPI AGGIUNTIVI");
                                
                                $aggiornati++;
                            } else {
                                Out::msgStop('ATTENZIONE', 'Procedimento n. ' . $itepas['ITECOD'] . ' sequenza ' . $itepas['ITESEQ'] . ' non trovato');
                                break;
                            }
                        }
                        
                        $this->scriviLog("Inserimento Terminato.");
                        
                        Out::msgInfo('ELABORAZIONE TERMINATA', 'Aggiornati n. ' . $aggiornati . ' procedimenti.');
                        Out::hide($this->nameForm . '_Sostituisci');
                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti");
                        break;
                    case $this->nameForm . '_TipoPasso_butt':
                        praRic::praRicPraclt($this->nameForm, "Ricerca Tipo Passo");
                        break;
                    case $this->nameForm . '_Passo_butt':
                        if ($_POST[$this->nameForm . "_Procedimento"]) {
                            $where = "WHERE ITECOD = " . $_POST[$this->nameForm . "_Procedimento"];
                            praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, "", '', 'asc');
                        } else {
                            Out::msgInfo("Ricerca passi", "Scegliere il procediemnto di riferimento");
                        }
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
                    case $this->nameForm . '_TipoPasso':
                        if ($_POST[$this->nameForm . '_TipoPasso']) {
                            $codice = $_POST[$this->nameForm . '_TipoPasso'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice, 'codice');
                            if ($Praclt_rec) {
                                Out::valore($this->nameForm . '_TipoPasso', $Praclt_rec['CLTCOD']);
                                Out::valore($this->nameForm . '_DesTipoPasso', $Praclt_rec['CLTDES']);
                            }
                        } else {
                            Out::valore($this->nameForm . '_TipoPasso', '');
                            Out::valore($this->nameForm . '_DesTipoPasso', '');
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
                Out::valore($this->nameForm . '_TipoPasso', $Itepas_rec['ITECLT']);
                $Praclt_rec = $this->praLib->GetPraclt($Itepas_rec['ITECLT'], 'codice');
                Out::valore($this->nameForm . '_DesTipoPasso', $Praclt_rec['CLTDES']);
                Out::valore($this->nameForm . '_ITEPAS[ITEKEY]', $Itepas_rec['ITEKEY']);
                Out::valore($this->nameForm . '_Descrizione', $Itepas_rec['ITEDES']);
                Out::valore($this->nameForm . '_NewPDF', $Itepas_rec['ITEWRD']);

                $this->itekey = $Itepas_rec['ITEKEY'];
                break;
            case "returnAnapra";
                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['PRANUM'], 'codice');
                Out::valore($this->nameForm . '_Procedimento', $Anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                break;
            case "returnPraclt";
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                Out::valore($this->nameForm . '_TipoPasso', $Praclt_rec['CLTCOD']);
                Out::valore($this->nameForm . '_DesTipoPasso', $Praclt_rec['CLTDES']);
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_itekey');
        App::$utente->removeKey($this->nameForm . '_fileLog');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::clearFields($this->nameForm, $this->divRic);
        TableView::clearGrid($this->gridProcedimenti);
        $this->itekey = "";
        $this->Nascondi();
        Out::show($this->nameForm . '_Conferma');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Procedimento');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Sostituisci');
        Out::hide($this->nameForm . '_AltraRichiesta');
    }

    public function creaSql() {
        $sql = "SELECT ITECOD, PRADES__1, ITECLT, ITEDES, ITESEQ, ITEKEY
                FROM `ITEPAS`
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM=ITEPAS.ITECOD
                WHERE 1 ";
        if ($_POST[$this->nameForm . "_OldPDF"] != '') {
            $sql .= " AND ".$this->PRAM_DB->strLower('ITEWRD')."='" . strtolower($_POST[$this->nameForm . "_OldPDF"]) . "'";
        }
        if ($_POST[$this->nameForm . "_TipoPasso"] != '') {
            $sql .= " AND ITECLT = '" . $_POST[$this->nameForm . "_TipoPasso"] . "'";
        }
        if ($_POST[$this->nameForm . "_Descrizione"] != '') {

            $sql .= " AND ".$this->PRAM_DB->strLower('ITEDES')." LIKE '" . strtolower($_POST[$this->nameForm . "_Descrizione"]) . "'";
        }
        $sql .= " AND PRAAVA = '' AND PRAOFFLINE = 0 AND ITECOD <> '" . $_POST[$this->nameForm . "_Procedimento"] . "'
                GROUP BY ITECOD";
        return $sql;
    }

    private function scriviLog($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

}

?>
