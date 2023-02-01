<?php

/**
 *  Programma Popolamento passo dati catastali Form
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

function praPopolaPassoCatastoForm() {
    $praPopolaPassoCatastoForm = new praPopolaPassoCatastoForm();
    $praPopolaPassoCatastoForm->parseEvent();
    return;
}

class praPopolaPassoCatastoForm extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $rowidSorg;
    public $Anapra_tab;
    public $nameForm = "praPopolaPassoCatastoForm";
    public $divRic = "praPopolaPassoCatastoForm_divRicerca";
    public $divRis = "praPopolaPassoCatastoForm_divRisultato";
    public $divGes = "praPopolaPassoCatastoForm_divGestione";
    public $gridPraclt = "praPopolaPassoCatastoForm_gridPraclt";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->rowidSorg = App::$utente->getKey($this->nameForm . '_rowidSorg');
            $this->Anapra_tab = App::$utente->getKey($this->nameForm . '_Anapra_tab');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_rowidSorg', $this->rowidSorg);
        App::$utente->setKey($this->nameForm . '_Anapra_tab', $this->Anapra_tab);
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                $this->Nascondi();
                $esito = $this->trovaCodiceSorgente();
                if ($esito === false) {
                    Out::msgInfo('ATTENZIONE', 'Non trovati procedimenti con passo form dati catastali.');
                }
                $this->trovaCodiceTipoPasso();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Test':
                        $Itepas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEPAS WHERE ROWID = $this->rowidSorg", false);
                        if (!$Itepas_rec) {
                            Out::msgStop('ATTENZIONE', 'Lettura passo sorgente fallita.');
                            $this->OpenRicerca();
                            $this->Nascondi();
                            break;
                        }
                        $codice_sorg = $Itepas_rec['ITECOD'];
                        $sql = "SELECT
                                      ANAPRA.PRANUM,
                                      ANAPRA.PRADES__1,
                                      ITEPAS.ITEDES,
                                      ITEPAS.ITECLT
                                FROM ANAPRA
                                      LEFT OUTER JOIN ITEPAS ON ANAPRA.PRANUM = ITEPAS.ITECOD
                                WHERE
                                      ITEPAS.ITECLT = '000009' AND ANAPRA.PRAAVA=''
                                ORDER BY PRANUM";
                        $Anapra_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        $quantiAnapraTotali = count($Anapra_tab);
                        $result = $this->controllaProcedimenti($Anapra_tab);
                        $this->Anapra_tab = $result['TABELLA'];
                        $scartati = $result['SCARTATI'];
                        $this->popolaTabella($this->Anapra_tab);
                        Out::hide($this->divGes);
                        Out::show($this->divRis);
                        Out::hide($this->divRic);

                        Out::addClass($this->nameForm . '_divTotQuantita', 'ita-box-highlight ui-state-highlight ');
                        $contenuto = "<span style=\"color: green; position:absolute; left:2px; top:3px; text-shadow: 1px 1px 1px #000; \"> <font size=\"3px\">Trovati</font></span><br><br><font size=\"3px\"><b>$quantiAnapraTotali</b></font><br><br>";
                        Out::html($this->nameForm . '_divTotQuantita', $contenuto);

                        Out::addClass($this->nameForm . '_divTotScartati', 'ita-box-highlight ui-state-highlight ');
                        $contenuto = "<span style=\"color: red; position:absolute; left:2px; top:3px; text-shadow: 1px 1px 1px #000; \"> <font size=\"3px\">Scartati</font></span><br><br><font size=\"3px\"><b>$scartati</b></font><br><br>";
                        Out::html($this->nameForm . '_divTotScartati', $contenuto);

                        Out::addClass($this->nameForm . '_divTotElaborare', 'ita-box-highlight ui-state-highlight ');
                        $contenuto = "<span style=\"color: orange; position:absolute; left:2px; top:3px; text-shadow: 1px 1px 1px #000; \"> <font size=\"3px\">Da Elaborare</font></span><br><br><font size=\"3px\"><b>" . ($quantiAnapraTotali - $scartati) . "</b></font><br><br>";
                        Out::html($this->nameForm . '_divTotElaborare', $contenuto);

                        Out::hide($this->nameForm . '_Test');
                        Out::show($this->nameForm . '_Inserisci');

//                        Out::msgInfo('', print_r($this->Anapra_tab, true));

                        break;

                    case $this->nameForm . '_butt_aggiungi':
                        break;
                        $sql = "SELECT * FROM PRACLT WHERE CLTCOD = '000024'";
                        $praclt = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if (!$praclt) {
                            $praclt = array();
                            $praclt['CLTCOD'] = "000024";
                            $praclt['CLTDES'] = "Inserimento dati catastali tramite form";
                            try {
                                ItaDB::DBInsert($this->PRAM_DB, 'PRACLT', 'ROWID', $praclt);
                            } catch (Exception $exc) {
                                Out::msgStop("Attenzione", "Errore in inserimento su PRACLT");
                                break;
                            }
                        } else {
                            $praclt['CLTDES'] = "Inserimento dati catastali tramite form";
                            try {
                                ItaDB::DBUpdate($this->PRAM_DB, 'PRACLT', 'ROWID', $praclt);
                            } catch (Exception $exc) {
                                Out::msgStop("Errore", "Errore in aggiornamento su PRACLT");
                                break;
                            }
                        }
                        $sql = "SELECT * FROM PRACLT WHERE CLTCOD = '000024'";
                        $praclt = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        Out::valore($this->nameForm . '_CLTCOD', $praclt['CLTCOD']);
                        Out::valore($this->nameForm . '_CLTDES', $praclt['CLTDES']);
                        break;

                    case $this->nameForm . '_Inserisci':
                        break;
                        //
                        // Trovo il passo da duplicare
                        //
                        $Itepas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEPAS WHERE ROWID = $this->rowidSorg", false);
                        if (!$Itepas_rec) {
                            Out::msgStop('ATTENZIONE', 'Lettura passo sorgente fallita.');
                            $this->OpenRicerca();
                            $this->Nascondi();
                            break;
                        }
                        //
                        // Trovo i dati aggiuntivi del passo da duplicare
                        //
                        $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEDAG WHERE ITECOD ='" . $Itepas_rec['ITECOD'] . "' AND ITEKEY='" . $Itepas_rec['ITEKEY'] . "'", true);
                        if (!$Itedag_tab) {
                            Out::msgStop("Attenzione!!", "dati aggiuntivi passo dati catastali form sorgente non trovati");
                            break;
                        }
//                        Out::msgInfo('passo sorgente',print_r($Itepas_rec,true));
//                        Out::msgInfo('dati aggiuntivi',print_r($Itedag_tab,true));
                        $i = 0;
                        foreach ($this->Anapra_tab as $Anapra_rec) {
                            if ($Anapra_rec['SCARTATO'] == 0) {
                                $i = $i + 1;
                                $itepas_appoggio = $Itepas_rec;
                                $itepas_appoggio["ITECOD"] = $Anapra_rec['PRANUM'];
                                $itepas_appoggio["ROWID"] = 0;
                                $itepas_appoggio["ITESEQ"] = $Anapra_rec['SEQUENZA'];
                                $itepas_appoggio["ITEKEY"] = $this->praLib->keyGenerator($itepas_appoggio["ITECOD"]);
                                $itepas_appoggio["ITECOMPSEQ"] = 9999;
                                $insert_Info = "Inserisco passo dati castatali su procedimento: " . $Anapra_rec['PRANUM'];
                                if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $itepas_appoggio, $insert_Info)) {
                                    Out::msgStop("Inserimento ITEPAS", "Inserimento passo dati catastali fallita");
                                    break 2;
                                }
                                //
                                // Preparo i nuovi record dei dati aggiuntivi e li inserisco
                                //
                                foreach ($Itedag_tab as $Itedag_rec) {
                                    $itedag_appoggio = $Itedag_rec;
                                    $itedag_appoggio["ROWID"] = 0;
                                    $itedag_appoggio["ITECOD"] = $Anapra_rec['PRANUM'];
                                    $itedag_appoggio["ITEKEY"] = $itepas_appoggio["ITEKEY"];
                                    $insert_Info = "Inserisco campi aggiuntivi dati castatali su procedimento: " . $Anapra_rec['PRANUM'];
                                    if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $itedag_appoggio, $insert_Info)) {
                                        Out::msgStop("Inserimento ITEDAG", "Inserimento dati aggiuntivi dati catastali fallita");
                                        break 3;
                                    }
                                }
                                //
                                // Riordino i passi dopo la procedura
                                //
                                $this->praLib->ordinaPassiProc($Anapra_rec['PRANUM']);
                                $this->ordinaPassiComp($Anapra_rec['PRANUM']);
//                                if ($i == 1) {
//                                    break;
//                                }
                            }
                        }
                        //
                        //  Spengo i tipi passo
                        //
                        $sql = "SELECT * FROM PRACLT WHERE CLTCOD = '000009'";
                        $praclt = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $praclt['CLTOFF'] = 1;
                        try {
                            ItaDB::DBUpdate($this->PRAM_DB, 'PRACLT', 'ROWID', $praclt);
                        } catch (Exception $exc) {
                            Out::msgStop("Errore", "Errore in aggiornamento su PRACLT");
                            break;
                        }
                        $sql = "SELECT * FROM PRACLT WHERE CLTCOD = '000010'";
                        $praclt = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $praclt['CLTOFF'] = 1;
                        try {
                            ItaDB::DBUpdate($this->PRAM_DB, 'PRACLT', 'ROWID', $praclt);
                        } catch (Exception $exc) {
                            Out::msgStop("Errore", "Errore in aggiornamento su PRACLT");
                            break;
                        }
                        //
                        Out::msgInfo("Inserimento passo Dati Catastali Form", "Inserito passo in $i procedimenti. Ho spento il tipo passo 000009 e 000010.");
                        Out::hide($this->nameForm . '_Inserisci');
                        break;

                    case $this->nameForm . '_Ricalcola':
                        break;
                        $sql = "SELECT
                                      ANAPRA.PRANUM,
                                      ANAPRA.PRADES__1,
                                      ITEPAS.ITEDES,
                                      ITEPAS.ITECLT,
                                      ITEPAS.ITEKEY
                                FROM ANAPRA
                                      LEFT OUTER JOIN ITEPAS ON ANAPRA.PRANUM = ITEPAS.ITECOD
                                WHERE
                                      ITEPAS.ITECLT = '000024' AND ANAPRA.PRAAVA=''
                                ORDER BY PRANUM";
                        $Anapra_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        foreach ($Anapra_tab as $Anapra_rec) {
                            $pranum = $Anapra_rec['PRANUM'];
                            $sql = "SELECT MAX(ITECOMPSEQ) AS MAXITECOMSEQ FROM ITEPAS WHERE ITECOD = '$pranum'";
                            $maxValue = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            $itepasRec = $this->praLib->GetItepas($Anapra_rec['ITEKEY'], 'itekey');
                            $itepasRec['ITECOMPSEQ'] = $maxValue['MAXITECOMSEQ'] + 10;
                            try {
                                ItaDB::DBUpdate($this->PRAM_DB, 'ITEPAS', 'ROWID', $itepasRec);
                            } catch (Exception $exc) {
                                Out::msgStop("Errore", "Errore in aggiornamento su ITEPAS");
                                break;
                            }
                        }
                        break;
                }
                break;

            case 'scegliPasso':
                $selRows = $_POST['rowData']['ROWID'];
                if ($selRows) {
                    $this->rowidSorg = $selRows;
                    $Itepas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEPAS WHERE ROWID =$this->rowidSorg", false);
                    $codice = $Itepas_rec['ITECOD'];
                    $Anapra_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAPRA WHERE PRANUM = '$codice'", false);
                    Out::valore($this->nameForm . '_PRANUM', $Anapra_rec['PRANUM']);
                    Out::valore($this->nameForm . '_PRADES__1', $Anapra_rec['PRADES__1']);
                    Out::show($this->nameForm . '_Test');
                } else {
                    Out::msgInfo('ATTENZIONE', 'Non trovato passo sorgente.');
                }
                break;

            case 'close-portlet':
                App::$utente->removeKey($this->nameForm . '_rowidSorg');
                App::$utente->removeKey($this->nameForm . '_Anapra_tab');
                $this->returnToParent();
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Test');
        Out::hide($this->nameForm . '_Inserisci');
        Out::hide($this->nameForm . '_Ricalcola');
    }

    public function OpenRicerca() {
        Out::hide($this->divGes);
        Out::hide($this->divRis);
        Out::show($this->divRic);
    }

    public function trovaCodiceSorgente() {
        $sql = "SELECT 
                    ITEPAS.ROWID,
                    ITEPAS.ITECLT,
                    ITEPAS.ITEDES,
                    ITEPAS.ITECOD,
                    ANAPRA.PRANUM,
                    ANAPRA.PRADES__1
                FROM ITEPAS 
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM = ITEPAS.ITECOD
                WHERE ITEPAS.ITECLT = '000024' ORDER BY ANAPRA.PRANUM";
        $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Itepas_tab) {
            $model = 'utiRicDiag';
            $gridOptions = array(
                "Caption" => 'Procedimenti con passo form dati catastali',
                "width" => '520',
                "height" => '500',
                "multiselect" => 'false',
                "pginput" => 'false',
                "pgbuttons" => 'false',
                "rowNum" => '200',
                "rowList" => '[]',
                "arrayTable" => $Itepas_tab,
                "colNames" => array(
                    "Codice",
                    "procedimento"
                ),
                "colModel" => array(
                    array("name" => 'ITECOD', "width" => 100),
                    array("name" => 'PRADES__1', "width" => 400)
                )
            );
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = 'praPopolaPassoCatastoForm';
            $_POST['returnEvent'] = 'scegliPasso';
            $_POST['retid'] = 'rowid';
            $_POST['returnKey'] = 'retKey';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        } else {
            return false;
        }
    }

    public function popolaTabella($Anapra_tab) {
        $ita_grid01 = new TableView(
                $this->gridPraclt, array('arrayTable' => $Anapra_tab,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridPraclt);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridPraclt);
        }
    }

    public function trovaCodiceTipoPasso() {
        $sql = "SELECT * FROM PRACLT WHERE CLTCOD = '000024'";
        $Praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($Praclt_rec) {
            Out::valore($this->nameForm . '_CLTCOD', $Praclt_rec['CLTCOD']);
            Out::valore($this->nameForm . '_CLTDES', $Praclt_rec['CLTDES']);
            Out::hide($this->nameForm . '_butt_aggiungi');
        } else {
            Out::valore($this->nameForm . '_CLTCOD', '000024');
            Out::valore($this->nameForm . '_CLTDES', 'Inserimento dati catastali tramite form');
            Out::show($this->nameForm . '_butt_aggiungi');
        }
    }

    public function controllaProcedimenti($Anapra_tab) {
        $scartati = 0;
        foreach ($Anapra_tab as $key => $Anapra_rec) {
            //$sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $Anapra_rec['PRANUM'] . "' AND ITECLT = '000024'";
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $Anapra_rec['PRANUM'] . "'";
            $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            $passo_24 = false;
            $passo_9 = 0;
            foreach ($Itepas_tab as $Itepas_rec) {
                if ($Itepas_rec['ITECLT'] == "000024") {
                    $passo_24 = true;
                }
                if ($Itepas_rec['ITECLT'] == "000009") {
                    $passo_9 = $Itepas_rec['ITESEQ'] - 5;
                }
            }
            if ($passo_24 == true) {
                $scartati++;
                $Anapra_tab[$key]['SCARTATO'] = 1;
                $Anapra_tab[$key]['STATO'] = '<div class="ita-html"><span style="width:20px;" title="Scartato " class="ita-tooltip">' . "<p align = \"center\"><span class=\"ita-icon ita-icon-bullet-red-16x16 \" style=\"height:10px;width:10px;background-size:100%;vertical-align:bottom;margin-left:1px;display:inline-block;\" ></span></p>" . '</span></div>';
                $Anapra_tab[$key]['SEQUENZA'] = 0;
            } else {
                $Anapra_tab[$key]['SCARTATO'] = 0;
                $Anapra_tab[$key]['STATO'] = '<div class="ita-html"><span style="width:20px;" title="Da elaborare " class="ita-tooltip">' . "<p align = \"center\"><span class=\"ita-icon ita-icon-bullet-green-16x16 \" style=\"height:10px;width:10px;background-size:100%;vertical-align:bottom;margin-left:1px;display:inline-block;\" ></span></p>" . '</span></div>';
                $Anapra_tab[$key]['SEQUENZA'] = $passo_9;
            }
        }
        $result = array();
        $result['SCARTATI'] = $scartati;
        $result['TABELLA'] = $Anapra_tab;
        return $result;
    }

    function ordinaPassiComp($procedimento) {
        if ($procedimento) {
            $new_seq = 0;
            $Itepas_tab = $this->praLib->GetItepas($procedimento, 'codice', true, " AND ITEIDR<>0 ORDER BY ITECOMPSEQ,ITESEQ");
            if (!$Itepas_tab) {
                return false;
            }
            foreach ($Itepas_tab as $Itepas_rec) {
                $new_seq +=10;
                $Itepas_rec['ITECOMPSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->PRAM_DB, "ITEPAS", "ROWID", $Itepas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
            return true;
        }
    }

}

?>
