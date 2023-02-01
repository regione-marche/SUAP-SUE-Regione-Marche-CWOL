<?php

/**
 *
 * Riapilogo Pagamenti
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    04.08.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function praConciliazioneRiep() {
    $praConciliazioneRiep = new praConciliazioneRiep();
    $praConciliazioneRiep->parseEvent();
    return;
}

class praConciliazioneRiep extends itaModel {

    public $praLib;
    public $proLib;
    public $utiEnte;
    public $PRAM_DB;
    public $PROT_DB;
    public $nameForm = "praConciliazioneRiep";
    public $gridRiepilogo = "praConciliazioneRiep_gridRiepilogo";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
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
                $this->openRicerca();
                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridRiepilogo:
                        $gridScheda = new TableView($this->gridRiepilogo, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $this->CreaSql()
                        ));
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        //headers xls
                        $sql = $this->CreaSql();
                        $test_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $headers = array();
                        foreach ($test_rec as $campo => $value) {
                            switch ($campo) {
                                case 'IMPORTO':
                                case 'SOMMAPAGATA':
                                    $headers[$campo] = 'euro';
                                    break;

                                default:
                                    $headers[$campo] = 'string';
                                    break;
                            }
                        }
                        $gridScheda->setXLSHeaders($headers);
                        $gridScheda->exportXLS('', 'Riepilogo_Pagamenti.xls');

                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridRiepilogo:
                        $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql() . " ORDER BY PROGES.GESNUM ASC", "Ente" => $ParametriEnte_rec['ENTE']);
                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praConciliazioneRiep', $parameters);
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRiepilogo:
                        TableView::clearGrid($_POST['id']);

                        $sql = $this->CreaSql();

                        $gridScheda = new TableView($_POST['id'], array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql
                        ));

                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPageFromArray('json', $this->ElaboraRecords($gridScheda->getDataArray()));
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        Out::clearFields($this->nameForm . '_divRicerca');
                        Out::setFocus($this->nameForm, $this->nameForm . '_DaNumeroPratica');
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_Procedimento_butt':
                        $this->dataRegAppoggio = "";
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_Procedimento');
                        break;

                    case $this->nameForm . '_Settore_butt':
                        praRic:: praRicAnaset($this->nameForm);
                        break;

                    case $this->nameForm . '_Attivita_butt':
                        if ($_POST[$this->nameForm . '_Settore']) {
                            $where = "AND ATTSET = '" . $_POST[$this->nameForm . '_Settore'] . "'";
                        }

                        praRic::praRicAnaatt($this->nameForm, $where);
                        break;

                    case $this->nameForm . '_Sportello_butt':
                        praRic:: praRicAnatsp($this->nameForm, "", "2");
                        break;

                    case $this->nameForm . '_Aggregato_butt':
                        praRic:: praRicAnaspa($this->nameForm, '', "1");
                        break;

                    case $this->nameForm . "_Evento_butt":
                        praRic::ricAnaeventi($this->nameForm, "", "RIC");
                        break;

                    case $this->nameForm . '_TipoImporto_butt':
                        praRic::ricAnatipimpo($this->nameForm);
                        break;

                    case $this->nameForm . '_TipoQuietanza_butt':
                        praRic::ricAnaquiet($this->nameForm);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DaNumeroPratica':
                    case $this->nameForm . '_ANumeroPratica':
                    case $this->nameForm . '_DaNumeroRichiesta':
                    case $this->nameForm . '_ANumeroRichiesta':
                        if ($_POST[$_POST['id']]) {
                            Out::valore($_POST['id'], str_pad($_POST[$_POST['id']], 6, '0', STR_PAD_LEFT));
                        }
                        break;

                    case $this->nameForm . '_Procedimento':
                        if ($_POST[$this->nameForm . '_Procedimento']) {
                            $codice = str_pad($_POST[$this->nameForm . '_Procedimento'], 6, '0', STR_PAD_LEFT);
                            $anapra_rec = $this->praLib->GetAnapra($codice);
                            Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                            Out::valore($this->nameForm . '_CodificaProcedimento', $anapra_rec['DESCPROC']);
                        }
                        break;

                    case $this->nameForm . '_Settore':
                        if ($_POST[$this->nameForm . '_Settore']) {
                            $codice = intval($_POST[$this->nameForm . '_Settore']);
                            $anaset_rec = $this->praLib->GetAnaset($codice);
                            Out::valore($this->nameForm . '_Settore', $anaset_rec['SETCOD']);
                            Out::valore($this->nameForm . '_CodificaSettore', $anaset_rec['SETDES']);
                        }
                        break;

                    case $this->nameForm . '_Attivita':
                        if ($_POST[$this->nameForm . '_Attivita']) {
                            $codice = intval($_POST[$this->nameForm . '_Attivita']);
                            $anaatt_rec = $this->praLib->GetAnaatt($codice);
                            Out::valore($this->nameForm . '_Attivita', $anaatt_rec['ATTCOD']);
                            Out::valore($this->nameForm . '_CodificaAttivita', $anaatt_rec['ATTDES']);
                        }
                        break;

                    case $this->nameForm . '_Sportello':
                        if ($_POST[$this->nameForm . '_Sportello']) {
                            $codice = intval($_POST[$this->nameForm . '_Sportello']);
                            $anatsp_rec = $this->praLib->GetAnatsp($codice);
                            Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPCOD']);
                            Out::valore($this->nameForm . '_CodificaSportello', $anatsp_rec['TSPDES']);
                        }
                        break;

                    case $this->nameForm . '_Aggregato':
                        if ($_POST[$this->nameForm . '_Aggregato']) {
                            $codice = intval($_POST[$this->nameForm . '_Aggregato']);
                            $anaspa_rec = $this->praLib->GetAnaspa($codice);
                            Out::valore($this->nameForm . '_Aggregato', $anaspa_rec['SPACOD']);
                            Out::valore($this->nameForm . '_CodificaAggregato', $anaspa_rec['SPADES']);
                        }
                        break;

                    case $this->nameForm . '_Evento':
                        if ($_POST[$this->nameForm . '_Evento']) {
                            $codice = str_pad($_POST[$this->nameForm . '_Evento'], 6, '0', STR_PAD_LEFT);
                            $anaeventi_rec = $this->praLib->GetAnaeventi($codice);
                            Out::valore($this->nameForm . '_Evento', $anaeventi_rec['EVTCOD']);
                            Out::valore($this->nameForm . '_CodificaEvento', $anaeventi_rec['EVTDESCR']);
                        }
                        break;

                    case $this->nameForm . '_TipoImporto':
                        if ($_POST[$this->nameForm . '_TipoImporto']) {
                            $codice = intval($_POST[$this->nameForm . '_TipoImporto']);
                            $anatipimpo_rec = $this->praLib->GetAnatipimpo($codice);
                            Out::valore($this->nameForm . '_TipoImporto', $anatipimpo_rec['CODTIPOIMPO']);
                            Out::valore($this->nameForm . '_CodificaTipoImporto', $anatipimpo_rec['DESCTIPOIMPO']);
                        }
                        break;

                    case $this->nameForm . '_TipoQuietanza':
                        if ($_POST[$this->nameForm . '_TipoQuietanza']) {
                            $codice = intval($_POST[$this->nameForm . '_TipoQuietanza']);
                            $anaquiet_rec = $this->praLib->GetAnaquiet($codice);
                            Out::valore($this->nameForm . '_TipoQuietanza', $anaquiet_rec['CODQUIET']);
                            Out::valore($this->nameForm . '_CodificaTipoQuietanza', $anaquiet_rec['QUIETANZATIPO']);
                        }
                        break;
                }
                break;

            case 'returnAnapra':
                // Procedimento
                Out::valore($this->nameForm . '_Procedimento', $_POST['rowData']['PRANUM']);
                Out::valore($this->nameForm . '_CodificaProcedimento', $_POST['rowData']['DESCPROC']);
                break;

            case 'returnAnaset':
                // Settore
                $anaset_rec = $this->praLib->GetAnaset($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Settore', $anaset_rec['SETCOD']);
                Out::valore($this->nameForm . '_CodificaSettore', $anaset_rec['SETDES']);
                break;

            case 'returnAnaatt':
                // Attività
                $anaatt_rec = $this->praLib->GetAnaatt($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Attivita', $anaatt_rec['ATTCOD']);
                Out::valore($this->nameForm . '_CodificaAttivita', $anaatt_rec['ATTDES']);
                break;

            case 'returnAnatsp2':
                // Sportello
                $anatsp_rec = $this->praLib->GetAnatsp($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Sportello', $anatsp_rec['TSPCOD']);
                Out::valore($this->nameForm . '_CodificaSportello', $anatsp_rec['TSPDES']);
                break;

            case 'returnAnaspa1':
                // Aggregato
                $anaspa_rec = $this->praLib->GetAnaspa($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Aggregato', $anaspa_rec['SPACOD']);
                Out::valore($this->nameForm . '_CodificaAggregato', $anaspa_rec['SPADES']);
                break;

            case 'returnAnaeventi':
                // Evento
                $anaeventi_rec = $this->praLib->GetAnaeventi($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Evento', $anaeventi_rec['EVTCOD']);
                Out::valore($this->nameForm . '_CodificaEvento', $anaeventi_rec['EVTDESCR']);
                break;

            case 'retRicAnatipimpo':
                // Tipo Importo
                $anatipimpo_rec = $this->praLib->GetAnatipimpo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_TipoImporto', $anatipimpo_rec['CODTIPOIMPO']);
                Out::valore($this->nameForm . '_CodificaTipoImporto', $anatipimpo_rec['DESCTIPOIMPO']);
                break;

            case 'retRicAnaquiet':
                // Tipo Quietanza
                $anaquiet_rec = $this->praLib->GetAnaquiet($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_TipoQuietanza', $anaquiet_rec['CODQUIET']);
                Out::valore($this->nameForm . '_CodificaTipoQuietanza', $anaquiet_rec['QUIETANZATIPO']);
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

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_SvuotaRicerca');
        Out::hide($this->nameForm . '_AltraRicerca');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openRicerca() {
        $this->mostraForm('divRicerca');
        $this->mostraButtonBar(array('Elenca', 'SvuotaRicerca'));

        Out::hide($this->nameForm . '_divTotali');

        Out::setFocus($this->nameForm, $this->nameForm . '_DaNumeroPratica');

        TableView::clearGrid($this->gridRiepilogo);
        TableView::disableEvents($this->gridRiepilogo);
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('AltraRicerca'));

        Out::show($this->nameForm . '_divTotali');

        Out::clearFields($this->nameForm . '_divRisultato');

        Out::setFocus($this->nameForm, $this->gridRiepilogo);

        TableView::enableEvents($this->gridRiepilogo);
        TableView::reload($this->gridRiepilogo);
    }

    public function CreaSql() {
        $DaNumeroPratica = $_POST[$this->nameForm . '_DaNumeroPratica'];
        $ANumeroPratica = $_POST[$this->nameForm . '_ANumeroPratica'];
        $AnnoPratica = $_POST[$this->nameForm . '_AnnoPratica'];

        $DaNumeroSeriePratica = $_POST[$this->nameForm . '_DaNumeroSeriePratica'];
        $ANumeroSeriePratica = $_POST[$this->nameForm . '_ANumeroSeriePratica'];
        $AnnoSeriePratica = $_POST[$this->nameForm . '_AnnoSeriePratica'];

        $DaNumeroRichiesta = $_POST[$this->nameForm . '_DaNumeroRichiesta'];
        $ANumeroRichiesta = $_POST[$this->nameForm . '_ANumeroRichiesta'];
        $AnnoRichiesta = $_POST[$this->nameForm . '_AnnoRichiesta'];

        $DaDataRegistrazione = $_POST[$this->nameForm . '_DaDataRegistrazione'];
        $ADataRegistrazione = $_POST[$this->nameForm . '_ADataRegistrazione'];

        $DaDataChiusura = $_POST[$this->nameForm . '_DaDataChiusura'];
        $ADataChiusura = $_POST[$this->nameForm . '_ADataChiusura'];

        $Procedimento = $_POST[$this->nameForm . '_Procedimento'];
        $Settore = $_POST[$this->nameForm . '_Settore'];
        $Attivita = $_POST[$this->nameForm . '_Attivita'];
        $Sportello = $_POST[$this->nameForm . '_Sportello'];
        $Aggregato = $_POST[$this->nameForm . '_Aggregato'];
        $Evento = $_POST[$this->nameForm . '_Evento'];
        $TipoImporto = $_POST[$this->nameForm . '_TipoImporto'];
        $TipoQuietanza = $_POST[$this->nameForm . '_TipoQuietanza'];

        $DataQuietanza = $_POST[$this->nameForm . '_DataQuietanza'];
        $DataInserimento = $_POST[$this->nameForm . '_DataInserimento'];

        $sql = "SELECT " .
                $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.DESCRIZIONE', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'", "PROGES.SERIEANNO") . " AS SERIE,

                    PROGES.GESNUM,
                    PROGES.GESPRA,
                    PROGES.GESDRE,
                    PROGES.GESDCH,
                    ANAPRA.PRADES__1,
                    ANASET.SETDES,
                    ANAATT.ATTDES,
                    ANATSP.TSPDES,
                    ANASPA.SPADES,
                    ANAEVENTI.EVTDESCR,
                    PROIMPO.IMPORTO,
                    " . $this->PRAM_DB->coalesce('PROCONCILIAZIONE.SOMMAPAGATA', 0) . " AS SOMMAPAGATA,
                    ANATIPIMPO.DESCTIPOIMPO,
                    ANAQUIET.QUIETANZATIPO,
                    PROCONCILIAZIONE.DATAQUIETANZA,
                    PROCONCILIAZIONE.DATAINSERIMENTO
                FROM
                    PROIMPO
                LEFT OUTER JOIN PROGES           ON PROGES.GESNUM             = PROIMPO.IMPONUM
                LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
                LEFT OUTER JOIN ANAPRA           ON ANAPRA.PRANUM             = PROGES.GESPRO
                LEFT OUTER JOIN ANASET           ON ANASET.SETCOD             = PROGES.GESSTT
                LEFT OUTER JOIN ANAATT           ON ANAATT.ATTCOD             = PROGES.GESATT
                LEFT OUTER JOIN ANATSP           ON ANATSP.TSPCOD             = PROGES.GESTSP
                LEFT OUTER JOIN ANASPA           ON ANASPA.SPACOD             = PROGES.GESSPA
                LEFT OUTER JOIN ANAEVENTI        ON ANAEVENTI.EVTCOD          = PROGES.GESEVE
                LEFT OUTER JOIN ANATIPIMPO       ON ANATIPIMPO.CODTIPOIMPO    = PROIMPO.IMPOCOD
                LEFT OUTER JOIN PROCONCILIAZIONE ON PROCONCILIAZIONE.IMPOPROG = PROIMPO.IMPOPROG AND PROCONCILIAZIONE.IMPONUM = PROIMPO.IMPONUM
                LEFT OUTER JOIN ANAQUIET         ON ANAQUIET.CODQUIET         = PROCONCILIAZIONE.QUIETANZA
                WHERE
                    1";

        if ($DaNumeroPratica && $AnnoPratica) {
            $DaNumeroPratica = str_pad($DaNumeroPratica, 6, '0', STR_PAD_LEFT);
            $sql .= " AND PROGES.GESNUM >= " . $AnnoPratica . $DaNumeroPratica;
        }

        if ($ANumeroPratica && $AnnoPratica) {
            $ANumeroPratica = str_pad($ANumeroPratica, 6, '0', STR_PAD_LEFT);
            $sql .= " AND PROGES.GESNUM <= " . $AnnoPratica . $ANumeroPratica;
        }

        if ($AnnoPratica && !$DaNumeroPratica && !$ANumeroPratica) {
            $sql .= " AND PROGES.GESNUM LIKE '" . $AnnoPratica . "%'";
        }
        if ($DaNumeroSeriePratica && !$ANumeroSeriePratica) {
            $sql .= " AND PROGES.SERIEPROGRESSIVO = '" . $DaNumeroSeriePratica . "'";
        }
        if ($DaNumeroSeriePratica && $ANumeroSeriePratica) {
            $sql .= " AND PROGES.SERIEPROGRESSIVO BETWEEN  '" . $DaNumeroSeriePratica . "' AND '" . $ANumeroSeriePratica . "'";
        }
        if ($AnnoSeriePratica) {
            $sql .= " AND PROGES.SERIEANNO = '" . $AnnoSeriePratica . "'";
        }

        if ($DaNumeroRichiesta && $AnnoRichiesta) {
            $DaNumeroRichiesta = str_pad($DaNumeroRichiesta, 6, '0', STR_PAD_LEFT);
            $sql .= " AND PROGES.GESPRA >= " . $AnnoRichiesta . $DaNumeroRichiesta;
        }

        if ($ANumeroRichiesta && $AnnoRichiesta) {
            $ANumeroRichiesta = str_pad($ANumeroRichiesta, 6, '0', STR_PAD_LEFT);
            $sql .= " AND PROGES.GESPRA <= " . $AnnoRichiesta . $ANumeroRichiesta;
        }

        if ($AnnoRichiesta && !$DaNumeroRichiesta && !$ANumeroRichiesta) {
            $sql .= " AND PROGES.GESPRA LIKE '" . $AnnoPratica . "%'";
        }

        if ($DaDataRegistrazione) {
            $sql .= " AND PROGES.GESDRE >= " . $DaDataRegistrazione;
        }

        if ($ADataRegistrazione) {
            $sql .= " AND PROGES.GESDRE <= " . $ADataRegistrazione;
        }

        if ($DaDataChiusura) {
            $sql .= " AND PROGES.GESDCH >= " . $DaDataChiusura;
        }

        if ($ADataChiusura) {
            $sql .= " AND PROGES.GESDCH <= " . $ADataChiusura;
        }

        if ($Procedimento) {
            $sql .= " AND ANAPRA.PRANUM = '" . $Procedimento . "'";
        }

        if ($Settore) {
            $sql .= " AND ANASET.SETCOD = '" . $Settore . "'";
        }

        if ($Attivita) {
            $sql .= " AND ANAATT.ATTCOD = '" . $Attivita . "'";
        }

        if ($Sportello) {
            $sql .= " AND ANATSP.TSPCOD = '" . $Sportello . "'";
        }

        if ($Aggregato) {
            $sql .= " AND ANASPA.SPACOD = '" . $Aggregato . "'";
        }

        if ($Evento) {
            $sql .= " AND ANAEVENTI.EVTCOD = '" . $Evento . "'";
        }

        if ($TipoImporto) {
            $sql .= " AND ANATIPIMPO.CODTIPOIMPO = '" . $TipoImporto . "'";
        }

        if ($TipoQuietanza) {
            $sql .= " AND ANAQUIET.CODQUIET = '" . $TipoQuietanza . "'";
        }

        if ($DataQuietanza) {
            $sql .= " AND PROCONCILIAZIONE.DATAQUIETANZA = '" . $DataQuietanza . "'";
        }

        if ($DataInserimento) {
            $sql .= " AND PROCONCILIAZIONE.DATAINSERIMENTO = '" . $DataInserimento . "'";
        }

        if ($_POST['_search'] == 'true') {
            if ($_POST['SERIE']) {
                $sql .= " AND SERIEPROGRESSIVO LIKE '%" . addslashes($_POST['SERIE']) . "%'";
            }
            if ($_POST['GESNUM']) {
                $sql .= " AND GESNUM LIKE '%" . addslashes($_POST['GESNUM']) . "%'";
            }

            if ($_POST['GESPRA']) {
                $sql .= " AND PROGES.GESPRA LIKE '%" . addslashes($_POST['GESPRA']) . "%'";
            }

            if ($_POST['GESDRE']) {
                $sql .= " AND PROGES.GESDRE LIKE '%" . addslashes($_POST['GESDRE']) . "%'";
            }

            if ($_POST['GESDCH']) {
                $sql .= " AND PROGES.GESDCH LIKE '%" . addslashes($_POST['GESDCH']) . "%'";
            }

            if ($_POST['TSPDES']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANATSP.TSPDES') . " LIKE '%" . strtoupper(addslashes($_POST['TSPDES'])) . "%'";
            }

            if ($_POST['SPADES']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANASPA.SPADES') . " LIKE '%" . strtoupper(addslashes($_POST['SPADES'])) . "%'";
            }

            if ($_POST['SETDES']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANASET.SETDES') . " LIKE '%" . strtoupper(addslashes($_POST['SETDES'])) . "%'";
            }

            if ($_POST['ATTDES']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANAATT.ATTDES') . " LIKE '%" . strtoupper(addslashes($_POST['ATTDES'])) . "%'";
            }

            if ($_POST['PRADES__1']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANAPRA.PRADES__1') . " LIKE '%" . strtoupper(addslashes($_POST['PRADES__1'])) . "%'";
            }

            if ($_POST['EVTDESCR']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANAEVENTI.EVTDESCR') . " LIKE '%" . strtoupper(addslashes($_POST['EVTDESCR'])) . "%'";
            }

            if ($_POST['IMPORTO']) {
                $sql .= " AND PROIMPO.IMPORTO LIKE '%" . addslashes($_POST['IMPORTO']) . "%'";
            }

            if ($_POST['PAGATO']) {
                $sql .= " AND PROIMPO.PAGATO LIKE '%" . addslashes($_POST['PAGATO']) . "%'";
            }

            if ($_POST['DESCTIPOIMPO']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANATIPIMPO.DESCTIPOIMPO') . " LIKE '%" . strtoupper(addslashes($_POST['DESCTIPOIMPO'])) . "%'";
            }

            if ($_POST['QUIETANZATIPO']) {
                $sql .= " AND " . $this->PRAM_DB->strUpper('ANAQUIET.QUIETANZATIPO') . " LIKE '%" . strtoupper(addslashes($_POST['QUIETANZATIPO'])) . "%'";
            }

            if ($_POST['DATAQUIETANZA']) {
                $sql .= " AND PROCONCILIAZIONE.DATAQUIETANZA LIKE '%" . addslashes($_POST['DATAQUIETANZA']) . "%'";
            }

            if ($_POST['DATAINSERIMENTO']) {
                $sql .= " AND PROCONCILIAZIONE.DATAINSERIMENTO LIKE '%" . addslashes($_POST['DATAINSERIMENTO']) . "%'";
            }
        }

        App::log($sql);

        return $sql;
    }

    public function ElaboraRecords($records_tab) {
        $sommaImporto = 0;
        $sommaPagato = 0;

        foreach ($records_tab as &$records_rec) {
            $records_rec['GESNUM'] = substr($records_rec['GESNUM'], 4, 10) . '/' . substr($records_rec['GESNUM'], 0, 4);
            $records_rec['GESPRA'] = $records_rec['GESPRA'] ? substr($records_rec['GESPRA'], 4, 10) . '/' . substr($records_rec['GESPRA'], 0, 4) : '';

            $records_rec['PRADES__1'] = '<span style="font-size: .9em;">' . $records_rec['PRADES__1'] . '</span>';
            $records_rec['SETDES'] = '<span style="font-size: .9em;">' . $records_rec['SETDES'] . '</span>';
            $records_rec['ATTDES'] = '<span style="font-size: .9em;">' . $records_rec['ATTDES'] . '</span>';
            $records_rec['TSPDES'] = '<span style="font-size: .9em;">' . $records_rec['TSPDES'] . '</span>';

            $sommaImporto += $records_rec['IMPORTO'];
            $sommaPagato += $records_rec['SOMMAPAGATA'];
        }

        Out::valore($this->nameForm . '_sommaImporto', $sommaImporto);
        Out::valore($this->nameForm . '_sommaPagato', $sommaPagato);
        Out::valore($this->nameForm . '_sommaDifferenza', $sommaImporto - $sommaPagato);

        return $records_tab;
    }

}
