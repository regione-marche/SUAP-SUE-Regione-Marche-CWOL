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

function praManutCamCom() {
    $praManutCamCom = new praManutCamCom();
    $praManutCamCom->parseEvent();
    return;
}

class praManutCamCom extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praManutCamCom";
    public $divRic = "praManutCamCom_divRicerca";
    public $divRis = "praManutCamCom_divRisultato";
    public $itekey;
    public $passi;
    public $passiSel;
    public $gridProcedimenti = "praManutCamCom_gridProcedimenti";
    public $procedimenti;
    public $fileLog;

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
        $this->passi = App::$utente->getKey($this->nameForm . '_passi');
        $this->passiSel = App::$utente->getKey($this->nameForm . '_passiSel');
        $this->procedimenti = App::$utente->getKey($this->nameForm . '_procedimenti');
        $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_itekey', $this->itekey);
            App::$utente->setKey($this->nameForm . '_passi', $this->passi);
            App::$utente->setKey($this->nameForm . '_passiSel', $this->passiSel);
            App::$utente->setKey($this->nameForm . '_procedimenti', $this->procedimenti);
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praManutCamCom_" . time() . ".log";
                $this->scriviLog("Avvio Programma Inserimento passi per Camera di Commercio");
                $this->OpenRicerca();
                
                $html = "<br>Il file di log è nella cartella " . $this->fileLog . "</br>";
                Out::html($this->nameForm . "_divFileLog", $html, 'append');
                
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {

                    case $this->nameForm . '_Conferma':
                        $result = $this->controllaTipiPasso();
                        if ($result === false) {
                            Out::msgInfo('AVVISO', 'Inserisci i tipi passo 73 e 74');
                            Out::hide($this->nameForm . '_Conferma');
                            Out::show($this->nameForm . '_InserisciTipiPasso');
                            break;
                        } else {
                            Out::msgInfo('ATTENZIONE','I tipi passo 73 e 74 sono già anagrafati. CONTROLLARE.');
                        }
                        if (count($this->passiSel) == 0) {
                            Out::msgStop('ATTENZIONE', 'Indicare i passi da Inserire.');
                            break;
                        }
                        if ($_POST[$this->nameForm . "_PartiDa"] == 0 || ($_POST[$this->nameForm . "_PartiDa"] == 2 && $_POST[$this->nameForm . "_QuantiPassiDopo"] == '')) {
                            Out::msgStop('ATTENZIONE', 'Indicare da dove inserire i passi.');
                            break;
                        }
                        $result = $this->controllaPresenzaTipiPassi();
                        if ($result === true) {
                            Out::msgStop('ATTENZIONE', 'Sono già presenti passi con TIPO PASSO 000073 o 000074.');
                            break;
                        }
                        $result = $this->controllaProcedimenti();
                        $this->procedimenti = $result['PROCEDIMENTI'];
                        $controllo = $result['CONTROLLO'];
                        if ($controllo === false) {
                            Out::msgStop('Controllo Procedimenti', 'RISCONTRATI ERRORI - CONTROLLARE');
                        } else {
                            Out::msgInfo('Controllo Procedimenti', 'CONTROLLO OK');
                        }
                        TableView::clearGrid($this->gridProcedimenti);
                        $ita_grid01 = new TableView(
                                $this->gridProcedimenti, array('arrayTable' => $this->procedimenti,
                            'rowIndex' => 'idx')
                        );
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(20000);
                        $ita_grid01->setSortOrder('ITECOD');
                        $ita_grid01->getDataPage('json');
                        Out::hide($this->divRic, '');
                        Out::show($this->divRis, '');
                        $this->Nascondi();
                        Out::show($this->nameForm . '_InserisciPassi');
                        Out::show($this->nameForm . '_AltraRichiesta');
                        break;

                    case $this->nameForm . '_InserisciTipiPasso':
                        $result = $this->inserisciTipiPasso();
                        if ($result === true) {
                            Out::hide($this->nameForm . '_InserisciTipiPasso');
                            Out::show($this->nameForm . '_Conferma');
                        }
                        break;

                    case $this->nameForm . '_AltraRichiesta':
                        $this->OpenRicerca();
                        $this->passi = array();
                        $this->passiSel = array();
                        break;

                    case $this->nameForm . '_InserisciPassi':

//                        Out::msgInfo('',print_r($this->procedimenti,true));
//                        break;

                        $gap = $_POST[$this->nameForm . "_QuantiPassiDopo"];
                        //
                        //  PREPARAZIONE ITEPAS E ITEDAG DA INSERIRE
                        //
                        $itepasNew = array();
                        $itepasDag = array();
                        foreach ($this->passiSel as $sel) {
                            $itepas = $this->praLib->GetItepas($sel['ITEKEY'], 'itekey');
                            $itepasNew[] = $itepas;
                            $itedag = $this->praLib->GetItedag($sel['ITEKEY'], 'itekey', true);
                            $itepasDag[] = $itedag;
                        }
                        //
                        $i = 0;
                        foreach ($this->procedimenti as $key => $procedimento) {
                            if ($procedimento['CONTROLLO'] == '') {
                                $i ++;
                                $sql = "SELECT * FROM ITEPAS WHERE ITECOD='" . $procedimento['ITECOD'] . "' ORDER BY ITESEQ";
                                $itepasTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                                $newSI = '';
                                foreach ($itepasTab as $itepasRec) {
                                    if ($itepasRec['ITEKEY'] == $procedimento['ITEKEY']) {
                                        switch ($_POST[$this->nameForm . "_PartiDa"]) {
                                            case 1:
                                                $seqIns = $itepasRec['ITESEQ'];
                                                break;
                                            case 2:
                                                $seqIns = $itepasRec['ITESEQ'] + (10 * $gap);
                                                break;
                                        }
                                        foreach ($itepasNew as $key => $new) {
                                            $itepas_appoggio = $new;
                                            $seqIns ++;
                                            $itepas_appoggio["ITECOD"] = $procedimento['ITECOD'];
                                            $itepas_appoggio["ROWID"] = 0;
                                            $itepas_appoggio["ITESEQ"] = $seqIns;
                                            $itepas_appoggio["ITEKEY"] = $this->praLib->keyGenerator($itepas_appoggio["ITECOD"]);
                                            if ($itepas_appoggio["ITECTP"] != '') {
                                                $itepas_appoggio["ITECTP"] = $newSI;    // RIFERIMENTO ITEKEY PASSO DOWNLOAD PER CONTROLLO CAMPI
                                            }
                                            $insert_Info = "Inserisco passo del procedimento " . $new['ITECOD'] . " - " . $new['ITEDES'] . " su procedimento: " . $procedimento['ITECOD'];
                                            if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $itepas_appoggio, $insert_Info)) {

                                                $this->scriviLog("PROCEDIMENTO " . $itepas_appoggio["ITECOD"] . ": ERRORE INSERIMENTO PASSO TIPO " . $itepas_appoggio["ITECLT"]);

                                                Out::msgStop("Inserimento data set", "Inserimento passo fallito");
                                                break;
                                            }

                                            $this->scriviLog("PROCEDIMENTO " . $itepas_appoggio["ITECOD"] . ": INSERITO PASSO TIPO " . $itepas_appoggio["ITECLT"]);

                                            if ($newSI == '') {
                                                $newSI = $itepas_appoggio["ITEKEY"];    // RIFERIMENTO ITEKEY RISPOSTA SI
                                            }
                                            //
                                            //  INSERISCO CAMPI AGGIUNTIVI DEL PASSO
                                            //
                                            $Itedag_tab = $itepasDag[$key];
                                            foreach ($Itedag_tab as $Itedag_rec) {
                                                $itedag_appoggio = $Itedag_rec;
                                                $itedag_appoggio["ROWID"] = 0;
                                                $itedag_appoggio["ITECOD"] = $procedimento['ITECOD'];
                                                $itedag_appoggio["ITEKEY"] = $itepas_appoggio["ITEKEY"];
                                                $insert_Info = "Inserisco campi aggiuntivi su procedimento: " . $Anapra_rec['PRANUM'];
                                                if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $itedag_appoggio, $insert_Info)) {

                                                    $this->scriviLog("PROCEDIMENTO " . $itepas_appoggio["ITECOD"] . ": ERRORE INSERIMENTO CAMPI AGGIUNTIVI ");

                                                    Out::msgStop("Inserimento data set", "Inserimento dati aggiuntivi fallito");
                                                    break;
                                                }

                                                $this->scriviLog("PROCEDIMENTO " . $itepas_appoggio["ITECOD"] . ": INSERITI CAMPI AGGIUNTIVI ");
                                            }
                                        }
                                        //
                                        //  RIORDINO I PASSI
                                        //
                                        $this->praLib->ordinaPassiProc($procedimento['ITECOD']);

                                        $this->scriviLog("PROCEDIMENTO " . $procedimento["ITECOD"] . ": RIORDINO SEQUENZA DEI PASSI");

                                        //
                                        //  SISTEMO ITEVPA     
                                        //
                                        $itepasRec['ITEVPA'] = $newSI;
                                        $update_Info = 'Oggetto: Sostituzione ITEvpa su procedimento ' . $itepasRec['ITECOD'] . ' sequenza ' . $itepasRec['ITESEQ'];
                                        if (!$this->updateRecord($this->PRAM_DB, 'ITEPAS', $itepasRec, $update_Info)) {

                                            $this->scriviLog("PROCEDIMENTO " . $itepasRec['ITECOD'] . ": ERRORE AGGIORNAMENTO SALTO DOMANDA");

                                            Out::msgStop('ATTENZIONE', 'Errore in aggiornamento del procedimento n. ' . $itepasRec['ITECOD'] . ' sequenza ' . $itepasRec['ITESEQ']);
                                            break;
                                        }

                                        $this->scriviLog("PROCEDIMENTO " . $itepasRec['ITECOD'] . ": AGGIORNATO SALTO DOMANDA");
                                        
                                        break;
                                    }
                                }
                            }
                        }

                        $this->scriviLog("Inserimento Terminato.");

                        Out::msgInfo("Inserimento passi", "Inserito passi su $i procedimenti.");
                        Out::hide($this->nameForm . '_InserisciPassi');
                        break;

                    case $this->nameForm . '_Procedimento_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca Procedimenti");
                        break;
                    case $this->nameForm . '_PassoRic_butt':
                        praRic::praRicPraclt($this->nameForm, "Ricerca Tipo Passo");
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
                                $this->cercaPassi($Anapra_rec['PRANUM']);
                            } else {
                                Out::valore($this->nameForm . '_DesProcedimento', "");
                            }
                        }
                        break;
                    case $this->nameForm . '_PassoRic':
                        if ($_POST[$this->nameForm . '_PassoRic']) {
                            $codice = $_POST[$this->nameForm . '_PassoRic'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Praclt_rec = $this->praLib->GetPraclt($codice, 'codice');
                            if ($Praclt_rec) {
                                Out::valore($this->nameForm . '_PassoRic', $Praclt_rec['CLTCOD']);
                                Out::valore($this->nameForm . '_DesPassoRic', $Praclt_rec['CLTDES']);
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
            case "returnAnapra";
                $Anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['PRANUM'], 'codice');
                Out::valore($this->nameForm . '_Procedimento', $Anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_DesProcedimento', $Anapra_rec['PRADES__1']);
                $this->cercaPassi($Anapra_rec['PRANUM']);
                break;
            case 'returnPassiSel':
                $this->passiSel = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($selRows as $rowid) {
                    foreach ($this->passi as $keyPasso => $passo) {
                        if ($passo["ROWID"] == $rowid) {
                            $this->passiSel[] = $this->passi[$keyPasso];
                        }
                    }
                }
                foreach ($this->passiSel as $sel) {
                    $riep .= $sel ['ITESEQ'] . ' ' . $sel ['ITEDES'] . "\n\r";
                }
                Out::valore($this->nameForm . '_RiepilogoPassi', $riep);
                break;
            case "returnPraclt";
                $Praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                Out::valore($this->nameForm . '_PassoRic', $Praclt_rec['CLTCOD']);
                Out::valore($this->nameForm . '_DesPassoRic', $Praclt_rec['CLTDES']);
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_itekey');
        App::$utente->removeKey($this->nameForm . '_passi');
        App::$utente->removeKey($this->nameForm . '_passiSel');
        App::$utente->removeKey($this->nameForm . '_procedimenti');
        App::$utente->removeKey($this->nameForm . '_fileLog');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::clearFields($this->nameForm, $this->divRic);
        $this->itekey = "";
        $this->Nascondi();
        Out::show($this->nameForm . '_Conferma');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Procedimento');
        $this->passi = array();
        $this->passiSel = array();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_InserisciPassi');
        Out::hide($this->nameForm . '_AltraRichiesta');
        Out::hide($this->nameForm . '_InserisciTipiPasso');
    }

    public function cercaPassi($pranum) {
        $this->caricaPassi($pranum);
        praRic::praPassiSelezionati($this->passi, $this->nameForm, "exp", "Esportazione Passi");
    }

    public function caricaPassi($procedimento) {
        $sql = "SELECT
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITESEQ AS ITESEQ,
                    ITEPAS.ITEGIO AS ITEGIO,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEPUB AS ITEPUB,
                    ITEPAS.ITEOBL AS ITEOBL,
                    ITEPAS.ITECTP AS ITECTP,
                    ITEPAS.ITEQST AS ITEQST,
                    ITEPAS.ITEVPA AS ITEVPA,
                    ITEPAS.ITEVPN AS ITEVPN,
                    ITEPAS.ITEKEY AS ITEKEY,
                    ITEPAS.ITEKPRE AS ITEKPRE,
                    ITEPAS.TEMPLATEKEY AS TEMPLATEKEY,
                    PRACLT.CLTDES AS CLTDES," .
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM ITEPAS LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=ITEPAS.ITERES
                LEFT OUTER JOIN PRACLT ON PRACLT.CLTCOD=ITEPAS.ITECLT
                WHERE ITEPAS.ITECOD = '" . $procedimento . "' ORDER BY ITESEQ";
        $this->passi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if ($this->passi) {
            foreach ($this->passi as $key => $passo) {
                if ($passo['ITEVPA'] != '' || $passo['ITEVPN'] != '') {
                    $this->passi[$key]['VAI'] = '<span class="ita-icon ita-icon-arrow-green-dx-16x16">true</span>';
                }
                if ($passo['ITECTP'] != 0) {
                    $Itepas_rec = $this->praLib->GetItepas($passo['ITECTP'], "itekey");
                    $this->passi[$key]['CONTROLLO'] = $Itepas_rec['ITESEQ'];
                }
                if ($passo['TEMPLATEKEY']) {
                    $this->passi[$key]['TEMPLATE'] = "<span class=\"ita-icon ita-icon-open-folder-24x24\">Apri Gestione Passo Template</span>";
                }
                $this->passi[$key]['ORDERANT'] = str_pad($passo['ITESEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['PROKPRE'], 4, "0", STR_PAD_LEFT);
                if ($passo['ITEKPRE']) {
                    $itepas_recAnt = $this->praLib->GetItepas($passo['ITEKPRE'], "itekey");
                    $this->passi[$key]['ORDERANT'] = str_pad($itepas_recAnt['ITESEQ'], 4, "0", STR_PAD_LEFT) . str_pad($passo['ITESEQ'], 4, "0", STR_PAD_LEFT);
                    $this->passi[$key]['SEQANT'] = $this->passi[$key]['ITESEQ'];
                    $this->passi[$key]['ITESEQ'] = '';
                }
            }
        }
        $passi = $this->array_sort($this->passi, "ORDERANT");
        $this->passi = $passi;
        return;
    }

    public function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }
            switch ($order) {
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
                default:
                    asort($sortable_array);
                    break;
            }
            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }
        return $new_array;
    }

    public function creaSql() {
        $sql = "SELECT ITECOD, PRADES__1, ITECLT, ITEDES, ITESEQ, ITEKEY
                FROM `ITEPAS`
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM=ITEPAS.ITECOD
                WHERE ITECLT = '" . $_POST[$this->nameForm . "_PassoRic"] . "'
                      AND PRAAVA = '' AND PRAOFFLINE = 0 AND ITECOD <> '" . $_POST[$this->nameForm . "_Procedimento"] . "'
                GROUP BY ITECOD";
        return $sql;
    }

    public function controllaProcedimenti() {
        $controllo = true;
        $procedimenti = ItaDB::DBSQLSelect($this->PRAM_DB, $this->creaSql(), true);
        foreach ($procedimenti as $key => $procedimento) {
            $trovataDomanda = $trovataInvioMail = $trovataInvioCamCom = 0;
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD='" . $procedimento['ITECOD'] . "' ORDER BY ITESEQ";
            $itepasTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            foreach ($itepasTab as $keyPas => $itepasRec) {
                if ($itepasRec['ITEKEY'] == $procedimento['ITEKEY']) {
                    $trovataDomanda = 1;
                    $itekeyDomandaSI = $itepasRec['ITEVPA'];
                    break;
                }
            }
            $keyPas ++;
            $itepasRec = $itepasTab[$keyPas];
            if ($itepasRec['ITECLT'] == '000004') {     // PASSO INVIO MAIL (RISPOSTA NO)
                $trovataInvioMail = 1;
            }
            $keyPas ++;
            $itepasRec = $itepasTab[$keyPas];
            if ($itepasRec['ITECLT'] == '000098' && $itepasRec['ITEKEY'] == $itekeyDomandaSI) {     // PASSO INVIO CAMERA COMMERCIO (RISPOSTA SI)
                $trovataInvioCamCom = 1;
            }
            if (($trovataDomanda + $trovataInvioMail + $trovataInvioCamCom) == 3) {
                $procedimenti[$key]['CONTROLLO'] = '';
                $procedimenti[$key]['DOMANDA'] = $trovataDomanda;
                $procedimenti[$key]['MAIL'] = $trovataInvioMail;
                $procedimenti[$key]['CAMCOM'] = $trovataInvioCamCom;
            } else {
                $procedimenti[$key]['CONTROLLO'] = 'NO';
                $procedimenti[$key]['DOMANDA'] = $trovataDomanda;
                $procedimenti[$key]['MAIL'] = $trovataInvioMail;
                $procedimenti[$key]['CAMCOM'] = $trovataInvioCamCom;
                $controllo = false;
            }
        }
        $result['PROCEDIMENTI'] = $procedimenti;
        $result['CONTROLLO'] = $controllo;
        return $result;
    }

    public function controllaPresenzaTipiPassi() {
        $sql = "SELECT ITECOD, PRADES__1, ITECLT, ITEDES, ITESEQ, ITEKEY
                FROM `ITEPAS`
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM=ITEPAS.ITECOD
                WHERE (ITECLT = '000073' OR ITECLT = '000074') 
                      AND PRAAVA = '' AND PRAOFFLINE = 0 AND ITECOD <> '" . $_POST[$this->nameForm . "_Procedimento"] . "'
                GROUP BY ITECOD";

        $passiPresenti = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($passiPresenti) {
            return true;
        } else {
            return false;
        }
    }

    public function controllaTipiPasso() {
        $Praclt_73 = $this->praLib->GetPraclt('000073', 'codice');
        $Praclt_74 = $this->praLib->GetPraclt('000074', 'codice');
        if (!$Praclt_73 || !$Praclt_74) {
            return false;
        } else {
            return true;
        }
    }

    public function inserisciTipiPasso() {
        $Praclt_73 = $this->praLib->GetPraclt('000073', 'codice');
        if (!$Praclt_73) {
            $praclt = array();
            $praclt['CLTCOD'] = '000073';
            $praclt['CLTDES'] = 'Scarica distinta pratica Camera di Commercio';
            $praclt['CLTINSEDITOR'] = 'Italsoft srl';
            $praclt['CLTINSDATE'] = '20170828';
            $praclt['CLTINSTIME'] = '12:58:56';
            $praclt['CLTUPDEDITOR'] = 'Italsoft srl';
            $praclt['CLTUPDDATE'] = '20170828';
            $praclt['CLTUPDTIME'] = '13:17:10';
            $insert_Info = "Inserisco anagrafica tipo passo 000073";
            if (!$this->insertRecord($this->PRAM_DB, 'PRACLT', $praclt, $insert_Info)) {
                Out::msgStop("Inserimento data set", "Inserimento anagrafica tipo passo fallito");
                return false;
            }
        }
        $Praclt_74 = $this->praLib->GetPraclt('000074', 'codice');
        if (!$Praclt_74) {
            $praclt = array();
            $praclt['CLTCOD'] = '000074';
            $praclt['CLTDES'] = 'Allega distinta pratica Camera di Commercio Firmata';
            $praclt['CLTINSEDITOR'] = 'Italsoft srl';
            $praclt['CLTINSDATE'] = '20170828';
            $praclt['CLTINSTIME'] = '12:59:45';
            $praclt['CLTUPDEDITOR'] = 'Italsoft srl';
            $praclt['CLTUPDDATE'] = '20170828';
            $praclt['CLTUPDTIME'] = '13:17:21';
            $insert_Info = "Inserisco anagrafica tipo passo 000074";
            if (!$this->insertRecord($this->PRAM_DB, 'PRACLT', $praclt, $insert_Info)) {
                Out::msgStop("Inserimento data set", "Inserimento anagrafica tipo passo fallito");
                return false;
            }
        }
        return true;
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
