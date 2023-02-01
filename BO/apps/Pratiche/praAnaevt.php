<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function praAnaevt() {
    $praAnaevt = new praAnaevt();
    $praAnaevt->parseEvent();
    return;
}

class praAnaevt extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praAnaevt";
    public $gridAnaeventi = "praAnaevt_gridAnaeventi";
    private $evtsegcomunica = array(
        '' => 'Nessuna',
        'ALTRO' => 'Altro',
        'APERTURA' => 'Apertura',
        'CESSAZIONE' => 'Cessazione',
        'MODIFICHE' => 'Modifiche',
        'SUBENTRO' => 'Subentro',
        'TRASFORMAZIONE' => 'Trasformazione'
    );

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->utiEnte = new utiEnte();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaSelect();
                $this->OpenRicerca();
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praAnaevt', $parameters);
                break;

            case 'dbClickRow':
            case 'editGridRow':
                $rowid = $_POST['rowid'];
                switch ($_POST['id']) {
                    case $this->gridAnaeventi:
                        $this->OpenGestione($rowid);
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnaeventi:
                        $this->OpenGestione();
                        break;
                }
                break;

            case 'delGridRow':
                $rowid = $_POST['rowid'];
                switch ($_POST['id']) {
                    case $this->gridAnaeventi:
                        $this->OpenGestione($rowid);

                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                                )
                        );
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnaeventi:
                        TableView::clearGrid($this->gridAnaeventi);
                        $gridScheda = new TableView($this->gridAnaeventi, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $this->CreaSql()
                        ));
                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        if (!$gridScheda->getDataPageFromArray('json', $this->ElaboraRecords($gridScheda->getDataArray()))) {
                            Out::msgStop("Selezione", "Nessun record trovato");
                            $this->OpenRicerca();
                        }
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        if ($codice != '') {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $Anaeventi_rec = $this->praLib->GetAnaeventi($codice);
                            if ($Anaeventi_rec) {
                                $this->OpenGestione($Anaeventi_rec['ROWID']);
                            }
                        }
                        break;

                    case $this->nameForm . '_ANAEVENTI[EVTCOD]':
                        $codice = $_POST[$this->nameForm . '_ANAEVENTI']['EVTCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAEVENTI[EVTCOD]', $codice);
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->OpenGestione();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANAEVENTI']['EVTCOD'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $_POST[$this->nameForm . '_ANAEVENTI']['EVTCOD'] = $codice;
                        try {   // Effettuo la FIND
                            $Anaeventi_rec = $this->praLib->GetAnaeventi($codice);
                            if (!$Anaeventi_rec) {
                                $Anaeventi_rec = $_POST[$this->nameForm . '_ANAEVENTI'];
                                $Anaeventi_rec = $this->praLib->SetMarcaturaEventoProcedimento($Anaeventi_rec, true);
                                $insert_Info = 'Oggetto: ' . $Anaeventi_rec['EVTCOD'] . " " . $Anaeventi_rec['EVTDESCR'];
                                if ($this->insertRecord($this->PRAM_DB, 'ANAEVENTI', $Anaeventi_rec, $insert_Info)) {
                                    $this->OpenRicerca();
                                }
                            } else {
                                Out::msgInfo("Codice già presente", "Inserire un nuovo codice.");
                                Out::setFocus('', $this->nameForm . '_ANAEVENTI[EVTCOD]');
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Inserimento su Eventi Procedimento.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $Anaeventi_rec = $_POST[$this->nameForm . '_ANAEVENTI'];
                        //
                        $iteevt_tab = $this->praLib->GetIteevt($Anaeventi_rec['EVTCOD'], "evento", true);
                        if ($iteevt_tab) {
                            $proc = "";
                            $Anaeventi_recCtr = $this->praLib->GetAnaeventi($Anaeventi_rec['EVTCOD']);
                            if ($Anaeventi_recCtr['EVTSEGCOMUNICA'] != $Anaeventi_rec['EVTSEGCOMUNICA']) {
                                foreach ($iteevt_tab as $key => $iteevt_rec) {
                                    $anapra_rec = $this->praLib->GetAnapra($iteevt_rec['ITEPRA']);
                                    $proc .= $anapra_rec['PRANUM'] . " - " . $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . "<br>";
                                }
                                Out::msgStop("Attenzione", "<br>Impossibile modificare il Tipo Segnalazione Comunica perchè l'evento è già utilizzato nei seguenti procedimenti:<br><b>$proc</b>");
                                break;
                            }
                        }
                        //
                        $codice = $Anaeventi_rec['EVTCOD'];
                        $Anaeventi_rec['EVTCOD'] = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $Anaeventi_rec = $this->praLib->SetMarcaturaEventoProcedimento($Anaeventi_rec);
                        $update_Info = 'Oggetto: ' . $Anaeventi_rec['EVTCOD'] . " " . $Anaeventi_rec['EVTDESCR'];
                        if ($this->updateRecord($this->PRAM_DB, 'ANAEVENTI', $Anaeventi_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array(
                                'id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f8"
                            ),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm,
                                'shortCut' => "f5"
                            )
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anaeventi_rec = $_POST[$this->nameForm . '_ANAEVENTI'];
                        //
                        $iteevt_tab = $this->praLib->GetIteevt($Anaeventi_rec['EVTCOD'], "evento", true);
                        if ($iteevt_tab) {
                            $proc = "";
                            foreach ($iteevt_tab as $key => $iteevt_rec) {
                                $anapra_rec = $this->praLib->GetAnapra($iteevt_rec['ITEPRA']);
                                $proc .= $anapra_rec['PRANUM'] . " - " . $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . "<br>";
                            }
                            Out::msgStop("Attenzione", "<br>Impossibile cancellare il seguente evento perchè già utilizzato nei seguenti procedimenti:<br><b>$proc</b>");
                            break;
                        }
                        //
                        try {
                            $delete_Info = 'Oggetto: ' . $Anaeventi_rec['EVTCOD'] . " " . $Anaeventi_rec['EVTDESCR'];
                            if ($this->deleteRecord($this->PRAM_DB, 'ANAEVENTI', $Anaeventi_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su Eventi Procedimento.", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_Elenca':
                        $this->OpenRisultato();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_TornaElenco':
                        $this->OpenRisultato();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function CreaSelect() {
        foreach (praLib::$TIPO_SEGNALAZIONE as $k => $v) {
            Out::select($this->nameForm . '_Segnalazione', '1', ( $k === '' ? '*' : $k), '0', $v);
            Out::select($this->nameForm . '_ANAEVENTI[EVTSEGCOMUNICA]', '1', $k, '0', $v);
        }
    }

    public function CreaSql() {
        $sql = "SELECT * FROM ANAEVENTI WHERE 1 = 1";

        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND EVTCOD = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }

        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND EVTDESCR LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }

        if ($_POST[$this->nameForm . '_Segnalazione'] != "") {
            if ($_POST[$this->nameForm . '_Segnalazione'] == '*')
                $_POST[$this->nameForm . '_Segnalazione'] = '';
            $sql .= " AND EVTSEGCOMUNICA = '" . $_POST[$this->nameForm . '_Segnalazione'] . "'";
        }

        return $sql;
    }

    public function ElaboraRecords($records) {
        foreach ($records as $k => $record) {
            $records[$k]['EVTSEGCOMUNICA'] = praLib::$TIPO_SEGNALAZIONE[$record['EVTSEGCOMUNICA']];
        }

        return $records;
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_TornaElenco');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function OpenRicerca() {
        $this->mostraForm('divRicerca');
        Out::clearFields($this->nameForm);
        $this->mostraButtonBar(array('Nuovo', 'Elenca'));

        TableView::disableEvents($this->gridAnaeventi);
        TableView::clearGrid($this->gridAnaeventi);

        Out::setFocus($this->nameForm, $this->nameForm . '_Codice');
    }

    public function OpenRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('Nuovo', 'AltraRicerca'));

        TableView::enableEvents($this->gridAnaeventi);
        TableView::reload($this->gridAnaeventi);
    }

    public function OpenGestione($rowid = null) {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');
        Out::valore($this->nameForm . '_ANAEVENTI[EVTSEGCOMUNICA]', '');

        if (!$rowid) {
            /*
             * Nuovo
             */
            Out::attributo($this->nameForm . '_ANAEVENTI[EVTCOD]', 'readonly', '1');
            Out::setFocus($this->nameForm, $this->nameForm . '_ANAEVENTI[EVTCOD]');
            $this->mostraButtonBar(array('Aggiungi', 'AltraRicerca'));
            Out::html($this->nameForm . "_Editore", '&nbsp;');
        } else {
            /*
             * Dettaglio
             */
            Out::attributo($this->nameForm . '_ANAEVENTI[EVTCOD]', 'readonly', '0');
            Out::setFocus($this->nameForm, $this->nameForm . '_ANAEVENTI[EVTDESCR]');
            $this->mostraButtonBar(array('Aggiorna', 'Cancella', 'AltraRicerca', 'TornaElenco'));
            $Anaeventi_rec = $this->praLib->GetAnaeventi($rowid, 'rowid');
            $open_Info = 'Oggetto: ' . $Anaeventi_rec['EVTCOD'] . " " . $Anaeventi_rec['EVTDESCR'];
            $this->openRecord($this->PRAM_DB, 'ANAEVENTI', $open_Info);
            Out::valori($Anaeventi_rec, $this->nameForm . '_ANAEVENTI');
            $this->visualizzaMarcatura($Anaeventi_rec);
        }
    }

    public function visualizzaMarcatura($Anaeventi_rec) {
        Out::html($this->nameForm . '_Editore', '  Autore: <span style="font-weight:bold;color:darkgreen;">' . $Anaeventi_rec['EVTUPDEDITOR'] . '</span> Versione del: <span style="color:darkgreen;">' . date("d/m/Y", strtotime($Anaeventi_rec['EVTUPDDATE'])) . ' ' . $Anaeventi_rec['EVTUPDTIME'] . '  </span>');
    }

}
