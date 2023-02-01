<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

function proAnafas() {
    $proAnafas = new proAnafas();
    $proAnafas->parseEvent();
    return;
}

class proAnafas extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSerie;
    public $nameForm = "proAnafas";
    public $divGes = "proAnafas_divGestione";
    public $divRis = "proAnafas_divRisultato";
    public $divRic = "proAnafas_divRicerca";
    public $gridAnafas = "proAnafas_gridAnafas";
    public $gridSerie = "proAnafas_gridSerie";
    public $RowidVersione_T;
    public $RowidDettaglio;
    public $RowidPadre;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibTitolario = new proLibTitolario();
            $this->proLibSerie = new proLibSerie();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->RowidVersione_T = App::$utente->getKey($this->nameForm . '_RowidVersione_T');
            $this->RowidDettaglio = App::$utente->getKey($this->nameForm . '_RowidDettaglio');
            $this->RowidPadre = App::$utente->getKey($this->nameForm . '_RowidPadre');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_RowidVersione_T', $this->RowidVersione_T);
            App::$utente->setKey($this->nameForm . '_RowidDettaglio', $this->RowidDettaglio);
            App::$utente->setKey($this->nameForm . '_RowidPadre', $this->RowidPadre);
        }
    }

    public function getRowidVersione_T() {
        return $this->RowidVersione_T;
    }

    public function setRowidVersione_T($RowidVersione_T) {
        $this->RowidVersione_T = $RowidVersione_T;
    }

    public function getRowidDettaglio() {
        return $this->RowidDettaglio;
    }

    public function setRowidDettaglio($RowidDettaglio) {
        $this->RowidDettaglio = $RowidDettaglio;
    }

    public function setRowidPadre($RowidPadre) {
        $this->RowidPadre = $RowidPadre;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                TableView::disableEvents($this->gridAnafas);
                break;
            case 'DaTitolario':
                // Rimozione elementi inutilizzati.
                Out::removeElement($this->divRic, '', 0);
                Out::removeElement($this->divRis, '', 0);
                Out::show($this->divGes, '', 0);
                Out::removeElement($this->nameForm . '_Progressivo');
                Out::removeElement($this->nameForm . '_AltraRicerca');
                Out::removeElement($this->nameForm . '_Cancella');
                Out::removeElement($this->nameForm . '_Elenca');
                Out::removeElement($this->nameForm . '_StampaElenco');
                Out::removeElement($this->nameForm . '_Nuovo');

                // Div Dati in Gestione Serie
                $generator = new itaGenerator();
                $retHtml = $generator->getModelHTML('proConnSerieArc', false, $this->nameForm, true);
                Out::addContainer($this->nameForm . '_divGestione', $this->nameForm . '_divGestSerie', '');
                Out::html($this->nameForm . '_divGestSerie', $retHtml);
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridSerie:
                        proRic::proRicSerieArc($this->nameForm, '');
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnafas:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnafas:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );

                        break;
                    case $this->gridSerie:
                        Out::msgQuestion("Attenzione.", "Confermi di voler cancellare la connessione alla Serie selezionata?", array(
                            'F9-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaSerie',
                                'model' => $this->nameForm, 'shortCut' => "f9"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaSerie',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnafas, array('sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('FASDES');
                $ita_grid01->exportXLS('', 'Anafas.xls');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridSerie:
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        $Anafas_rec = $this->proLib->GetAnafas($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anafas_rec['FASCCF'], $Versione_rec['VERSIONE_T']);
                        break;
                    case $this->gridAnafas:
                        $ordinamento = $_POST['sidx'];
//                if ($_POST['sidx']=='FASDES') {
//                    $ordinamento='FASDES';
//                }
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridAnafas, array('sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($ordinamento);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnafas', $parameters);
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CATCOD':
                        $codice = $_POST[$this->nameForm . '_CATCOD'];
                        if (trim($codice) != "") {
                            $codice = str_pad(trim($codice), 4, '0', STR_PAD_LEFT);
                            $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                            if (!$Versione_rec) {
                                Out::msgInfo("Attenzione", "Versione non trovata.");
                                break;
                            }
                            $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $codice, 'codice');
                            if ($Anacat_rec) {
                                Out::valore($this->nameForm . '_CATCOD', $codice);
                                Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
                            } else {
                                Out::msgInfo("Attenzione", "Categoria non trovata.");
                                Out::valore($this->nameForm . '_CATCOD', '');
                                Out::valore($this->nameForm . '_CATDES', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_CATDES', "");
                        }
                        break;
                    case $this->nameForm . '_CLACOD':
                        $codice = $_POST[$this->nameForm . '_CLACOD'];
                        if (trim($codice) != "") {
                            $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                            if (!$Versione_rec) {
                                Out::msgInfo("Attenzione", "Versione non trovata.");
                                break;
                            }
                            $codiceCla = str_pad(trim($codice), 4, '0', STR_PAD_LEFT);
                            $codiceCat = str_pad(trim($_POST[$this->nameForm . '_CATCOD']), 4, '0', STR_PAD_LEFT);
                            $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $codiceCat . $codiceCla, 'codice');
                            if ($Anacla_rec) {
                                Out::valore($this->nameForm . '_CLACOD', $codiceCla);
                                Out::valore($this->nameForm . '_CLADES', $Anacla_rec['CLADE1'] . $Anacla_rec['CLADE2']);
                            } else {
                                Out::msgInfo("Attenzione", "Classe non trovata per la Categoria indicata.");
                                Out::valore($this->nameForm . '_CLACOD', '');
                                Out::valore($this->nameForm . '_CLADES', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_CLADES', "");
                        }
                        break;
                    case $this->nameForm . '_ANAFAS[FASCOD]':
                        $codice = $_POST[$this->nameForm . '_ANAFAS']['FASCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAFAS[FASCOD]', $codice);
                        }
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Storico':
                        if ($_POST[$this->nameForm . '_Storico'] == 0) {
                            Out::valore($this->nameForm . '_Valido', '');
                        }
                        break;
                }
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancellaSerie':
                        $rowid = $_POST[$this->gridSerie]['gridParam']['selarrrow'];
                        if (!$this->proLibSerie->CancellaSerieTitolario($rowid)) {
                            Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                            break;
                        }
                        Out::msgBlock('', 2000, false, "Collegamento alla Serie cancellato correttamente.");
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        $Anafas_rec = $this->proLib->GetAnafas($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anafas_rec['FASCCF'], $Versione_rec['VERSIONE_T']);
                        break;

                    case $this->nameForm . '_CATCOD_butt':
                        $where = array();
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $Versione_rec['VERSIONE_T'], $where, 'returnTitolario', 1);
                        break;

                    case $this->nameForm . '_CLACOD_butt':
                        $where = array();
                        if ($_POST[$this->nameForm . '_CATCOD']) {
                            $codice = $_POST[$this->nameForm . '_CATCOD'];
                            $codice = str_pad(trim($codice), 4, '0', STR_PAD_LEFT);
                            $where['ANACLA'] = " AND CLACAT = '$codice' ";
                        }
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $Versione_rec['VERSIONE_T'], $where, 'returnTitolario', 2);
                        break;
                    case $this->nameForm . '_Progressivo':
                        if ($_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] != '') {
                            for ($i = 1; $i <= 9999; $i++) {
                                $codice = str_repeat("0", 4 - strlen(trim($i))) . trim($i);
                                $anafas_rec = $this->proLib->GetAnafas('', $_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] . $codice);
                                if (!$anafas_rec) {
                                    Out::valore($this->nameForm . '_ANAFAS[FASCOD]', $codice);
                                    Out::setFocus('', $this->nameForm . '_ANAFAS[FASDES]');
                                    break;
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        try {
                            $codice = $_POST[$this->nameForm . '_CATCOD'] . $_POST[$this->nameForm . '_CLACOD'] . $_POST[$this->nameForm . '_ANAFAS']['FASCOD'];
                            $sql = "SELECT ORGCCF FROM ANAORG WHERE ORGDAT" . $this->PROT_DB->isBlank() . " AND ORGCCF LIKE '" . $codice . "%'";
                            $Anaorg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT PROCAT FROM ANAPRO WHERE PROCAT = '" . $codice . "'";
                            $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            // Controllo se è usato in altre anagrafiche e nelle procedure
                            if ($Anaorg_tab != null || $Anapro_tab != null) {
                                Out::msgStop("Attenzione!", 'Impossibile cancellare la Sottoclasse perché è assegnata ad altre Anagrafiche o Procedure.');
                            } else {
                                $delete_Info = 'Oggetto: ' . $_POST[$this->nameForm . '_ANAFAS']['FASCOD'] . " " . $_POST[$this->nameForm . '_FASDES'];
                                if ($this->deleteRecord($this->PROT_DB, 'ANAFAS', $_POST[$this->nameForm . '_ANAFAS']['ROWID'], $delete_Info)) {
                                    $this->OpenRicerca();
                                }
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA SOTTOCLASSI", $e->getMessage());
                        }
                        break;






                    case $this->nameForm . '_CancellaCodicePrec':
                        Out::msgQuestion("Cancellazione", "Confermi la <b>cancellazione definitiva</b> del collegamento con la SottoClasse precedente?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnCancPre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfCancPre', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_FASCOD_PREC_butt':
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        if (!$Versione_rec) {
                            Out::msgStop("Attenzione", "Versione Titolario indicata non trovata.");
                            $this->close();
                            return false;
                        }
                        $titolarioPrecedente = $this->proLibTitolario->GetTitolarioPrecedente($Versione_rec['VERSIONE_T']);
                        if ($titolarioPrecedente === '') {
                            Out::msgInfo("Attenzione", "Non esiste alcun titolario precedente a questo.");
                            break;
                        }
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $titolarioPrecedente, array(), 'returnTitolarioPrec', 3, true);
                        break;

                    case $this->nameForm . '_ConfCancPre':
                        Out::valore($this->nameForm . '_CATCOD_PREC', '');
                        Out::valore($this->nameForm . '_CLACOD_PREC', '');
                        Out::valore($this->nameForm . '_FASCOD_PREC', '');
                        Out::valore($this->nameForm . '_FASDES_PREC', '');
                        Out::valore($this->nameForm . '_VERSIONE_PREC', '');
                        Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', '');

                        $rowidPre = $_POST[$this->nameForm . '_ANAFASPREC']['ROWID'];
                        if ($rowidPre) {
                            $TitolarioSucc = array();
                            $TitolarioSucc['VERSIONE_T'] = '';
                            $TitolarioSucc['SOTTOCLASSE'] = '';
                            if (!$this->proLibTitolario->AggiornaTitolarioSucc($rowidPre, 'SOTTOCLASSE', $TitolarioSucc)) {
                                Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                                break;
                            }
                            Out::valore($this->nameForm . '_ANAFASPREC[ROWID]', '');
                        }
                        Out::show($this->nameForm . '_FASCOD_PREC_butt');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnTitolario':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_CATCOD', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_CATDES', $retTitolario['CATDES']);
                Out::valore($this->nameForm . '_CLACOD', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_CLADES', $retTitolario['CLADES']);
                break;

            case 'returnTitolarioPrec':
                $retTitolario = $_POST['rowData'];
                if (!$retTitolario['CATCOD'] || !$retTitolario['CLACOD'] || !$retTitolario['FASCOD']) {
                    Out::msgStop("Attenzione", "Selezionare una sottoclasse.");
                    break;
                }
                Out::valore($this->nameForm . '_CATCOD_PREC', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_CLACOD_PREC', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_FASCOD_PREC', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_FASDES_PREC', $retTitolario['DECOD_DESCR']);
                Out::valore($this->nameForm . '_VERSIONE_PREC', $retTitolario['VERSIONE_T']);
                Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $retTitolario['VERSIONE']);
                break;


            case 'returnSerieArc':
                App::log('ritorno');
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                    $Anafas_rec = $this->proLib->GetAnafas($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                    if (!$this->proLibSerie->AggiungiSerieATitolario($AnaserieArc_rec['CODICE'], $Anafas_rec['FASCCF'], $Versione_rec['VERSIONE_T'])) {
                        Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                        break;
                    }
                    $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anafas_rec['FASCCF'], $Versione_rec['VERSIONE_T']);
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_RowidVersione_T');
        App::$utente->removeKey($this->nameForm . '_RowidDettaglio');
        App::$utente->removeKey($this->nameForm . '_RowidPadre');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function OpenRicerca() {
        Out::hide($this->divRis, '', 0);
        Out::show($this->divRic, '', 200);
        Out::hide($this->divGes, '', 200);
        TableView::disableEvents($this->gridAnafas);
        TableView::clearGrid($this->gridAnafas);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Catcod');
    }

    function AzzeraVariabili() {
//        Out::valore($this->nameForm.'_ANAFAS[ROWID]','');
//        Out::valore($this->nameForm.'_Catcod','');
//        Out::valore($this->nameForm.'_Catdes','');
//        Out::valore($this->nameForm.'_Clacod','');
//        Out::valore($this->nameForm.'_Clades','');
//        Out::valore($this->nameForm.'_Fascod','');
//        Out::valore($this->nameForm.'_Fasdes','');
//        Out::valore($this->nameForm.'_Storico',0);
//        Out::valore($this->nameForm.'_Valido','');
//        Out::valore($this->nameForm.'_CATCOD','');
//        Out::valore($this->nameForm.'_CATDES','');
//        Out::valore($this->nameForm.'_CLACOD','');
//        Out::valore($this->nameForm.'_CLADES','');
//        Out::valore($this->nameForm.'_ANAFAS[FASCOD]','');
//        Out::valore($this->nameForm.'_FASDES','');
//        Out::valore($this->nameForm.'_ANAFAS[FASDAT]','');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divGestSerie');
    }

    public function Dettaglio() {
        if (!$this->RowidDettaglio) {
            Out::msgStop("Attenzione", "Rowid SottoClasse Mancante.");
            $this->close();
            return;
        }
        /* Decodifico il Titolario */
        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgStop("Attenzione", "Versione Titolario indicata non trovata.");
            $this->close();
            return false;
        }

        Out::clearFields($this->nameForm);
        Out::valore($this->nameForm . '_ANAFAS[VERSIONE_T]', $Versione_rec['VERSIONE_T']);
        Out::valore($this->nameForm . '_DESC_TITOLARIO', $Versione_rec['DESCRI']);

        /* Decodifico i dati */
        $Anafas_rec = $this->proLib->GetAnafas($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
        // Controllo Anacla.
        if (!$Anafas_rec) {
            Out::msgStop("Attenzione", "SottoClasse non trovata.");
            $this->close();
            return false;
        }
        $open_Info = 'Dettaglio Oggetto: ' . $Versione_rec['VERSIONE_T'] . " " . $Anafas_rec['CATCOD'] . " " . $Anafas_rec['CATDES'];
        $this->openRecord($this->PROT_DB, 'ANAFAS', $open_Info);
        /* Valorizzazione campi */
        $this->Nascondi();
        Out::valori($Anafas_rec, $this->nameForm . '_ANAFAS');
        /* Decodifico Classe e Categoria */
        $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $Anafas_rec['FASCCA'], 'codice');
        if ($Anacla_rec) {
            Out::valore($this->nameForm . '_CLACOD', $Anacla_rec['CLACOD']);
            Out::valore($this->nameForm . '_CLADES', $Anacla_rec['CLADE1'] . $Anacla_rec['CLADE2']);
            // Decodifico Categoria
            $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $Anacla_rec['CLACAT'], 'codice');
            if ($Anacat_rec) {
                Out::valore($this->nameForm . '_CATCOD', $Anacat_rec['CATCOD']);
                Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
            }
        }
        /* Decodifico Collegamento SottoClasse precedente */
        Out::show($this->nameForm . '_FASCOD_PREC_butt');
        $AnafasPrec_rec = $this->proLibTitolario->GetCollegamentoTitolarioSuccessivo($Versione_rec['VERSIONE_T'], '', '', $Anafas_rec['FASCCF']);
        if ($AnafasPrec_rec) {
            Out::valore($this->nameForm . '_ANAFASPREC[ROWID]', $AnafasPrec_rec['ROWID']);
            Out::valore($this->nameForm . '_CATCOD_PREC', substr($AnafasPrec_rec['FASCCF'], 0, 4));
            Out::valore($this->nameForm . '_CLACOD_PREC', substr($AnafasPrec_rec['FASCCF'], 4, 4));
            Out::valore($this->nameForm . '_FASCOD_PREC', $AnafasPrec_rec['FASCOD']);
            Out::valore($this->nameForm . '_FASDES_PREC', $AnafasPrec_rec['FASDES']);
            Out::valore($this->nameForm . '_VERSIONE_PREC', $AnafasPrec_rec['VERSIONE_T']);
            $VersionePre_rec = $this->proLibTitolario->GetVersione($AnafasPrec_rec['VERSIONE_T'], 'codice');
            Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $VersionePre_rec['DESCRI_B']);
            Out::hide($this->nameForm . '_FASCOD_PREC_butt');
        }

        Out::show($this->nameForm . '_Aggiorna');

        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        // Categoria e classe non più modificabili 
        Out::hide($this->nameForm . '_CATCOD_butt');
        Out::hide($this->nameForm . '_CLACOD_butt');
        Out::attributo($this->nameForm . '_CATCOD', 'readonly', '0');
        Out::attributo($this->nameForm . '_CLACOD', 'readonly', '0');
        Out::attributo($this->nameForm . '_ANAFAS[FASCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANAFAS[FASDES]');

        Out::show($this->nameForm . '_divGestSerie');
        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anafas_rec['FASCCF'], $Versione_rec['VERSIONE_T']);

        TableView::disableEvents($this->gridAnafas);
    }

    public function Nuovo() {
        if (!$this->RowidVersione_T) {
            Out::msgStop("Attenzione", "Versione Titolario mancante.");
            $this->close();
            return false;
        }
        /* Pulisco i campi della form */
        Out::clearFields($this->nameForm);
        /* Decodifico il Titolario */
        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgStop("Attenzione", "Versione Titolario indicata non trovata.");
            $this->close();
            return false;
        }
        Out::valore($this->nameForm . '_ANAFAS[VERSIONE_T]', $Versione_rec['VERSIONE_T']);
        Out::valore($this->nameForm . '_DESC_TITOLARIO', $Versione_rec['DESCRI']);
        // Decodifico la classe padre.
        if ($this->RowidPadre) {
            // Decodifico Classe
            $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $this->RowidPadre, 'rowid');
            if ($Anacla_rec) {
                Out::valore($this->nameForm . '_CLACOD', $Anacla_rec['CLACOD']);
                Out::valore($this->nameForm . '_CLADES', $Anacla_rec['CLADE1'] . $Anacla_rec['CLADE2']);
                // Decodifico Categoria
                $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $Anacla_rec['CLACAT'], 'codice');
                if ($Anacat_rec) {
                    Out::valore($this->nameForm . '_CATCOD', $Anacat_rec['CATCOD']);
                    Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
                }
            }
        }
        /* Nascondo campi div inutilizzati */
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        /* Visualizzo campi utilizzati */

        Out::attributo($this->nameForm . '_CATCOD', 'readonly', '1');
        Out::attributo($this->nameForm . '_CLACOD', 'readonly', '1');
        Out::attributo($this->nameForm . '_ANAFAS[FASCOD]', 'readonly', '1');
        Out::show($this->nameForm . '_CATCOD_butt');
        Out::show($this->nameForm . '_CLACOD_butt');
        Out::show($this->nameForm . '_Aggiungi');
        Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');

        Out::setFocus('', $this->nameForm . '_ANAFAS[FASCOD]');
    }

    public function Aggiungi() {
        /*
         * Controllo dati obbligatori
         */
        if (!trim($_POST[$this->nameForm . '_CATCOD'])) {
            Out::msgInfo("Attenzione", "Codice Categoria obbligatoria.");
            return;
        }
        if (!trim($_POST[$this->nameForm . '_CLACOD'])) {
            Out::msgInfo("Attenzione", "Codice Classe obbligatoria.");
            return;
        }

        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        /*
         * Controllo codice categoria e classe
         */
        $codiceCat = $_POST[$this->nameForm . '_CATCOD'];
        $codiceCat = str_pad(trim($codiceCat), 4, '0', STR_PAD_LEFT);
        $AnacatTest_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $codiceCat, 'codice');
        if (!$AnacatTest_rec) {
            Out::msgInfo("Attenzione", "Categoria inesistente.");
            return;
        }
        $codiceCla = $_POST[$this->nameForm . '_CLACOD'];
        $codiceCla = str_pad(trim($codiceCla), 4, '0', STR_PAD_LEFT);
        $AnaclaTest_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $codiceCat . $codiceCla, 'codice');
        if (!$AnaclaTest_rec) {
            Out::msgInfo("Attenzione", "Classe inesistente.");
            return;
        }

        $codice = $_POST[$this->nameForm . '_ANAFAS']['FASCOD'];
        /* Prenota per nuovo progressivo */
        $retLock = '';
        if (!trim($codice)) {
            $retLock = ItaDB::DBLock($this->PROT_DB, "ANAFAS", "", "", 20);
            if ($retLock['status'] != 0) {
                Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVO ANAFAS non riuscito.');
                return;
            }
            /* Prenoto numero versione ed escludo il 100: da assegnare. */
            $sqlMax = "SELECT MAX(FASCOD) AS MAX_FASCOD 
                                FROM ANAFAS 
                        WHERE VERSIONE_T = " . $Versione_rec['VERSIONE_T'] . " 
                        AND FASCCA = '" . $codiceCat . $codiceCla . "' ";
            $MaxAnafas_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sqlMax, false);
            $MaxFas = 1;
            if ($MaxAnafas_rec) {
                $MaxFas = $MaxAnafas_rec['MAX_FASCOD'] + 1;
            }
            $codice = $MaxFas;
        } else {
            /* Controllo codice numerico */
            if (!is_numeric(trim($codice))) {
                Out::msgInfo("Attenzione", "Inserire un codice numerico.");
                return;
            }
        }
        /* Allineo il codice a 4  */
        $codice = str_pad(trim($codice), 4, '0', STR_PAD_LEFT);
        /* Lettura dei dati dal post */
        $Anafas_rec = $_POST[$this->nameForm . '_ANAFAS'];
        $Anafas_rec['FASCOD'] = $codice;
        $Anafas_rec['FASCCA'] = $codiceCat . $codiceCla;
        $Anafas_rec['FASCCF'] = $codiceCat . $codiceCla . $codice;

        $AbafasTest_rec = $this->proLib->GetAnafas($Versione_rec['VERSIONE_T'], $Anafas_rec['FASCCF'], 'fasccf', false);
        if (!$AbafasTest_rec) {
            $insert_Info = 'Oggetto: ' . $Anafas_rec['FASCOD'] . " " . $Anafas_rec['FASDES'];
            if ($this->insertRecord($this->PROT_DB, 'ANAFAS', $Anafas_rec, $insert_Info)) {
                if ($retLock) {
                    /* Sblocco la tabella se bloccata. */
                    $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
                    if ($retUnlock['status'] != 0) {
                        Out::msgStop('Errore', 'Sblocco Tabella ANAFAS non Riuscito.');
                        return;
                    }
                }
                /* Ritorno al chiamante */
                $returnObj = itaModel::getInstance($this->returnModel);
                $returnObj->setEvent($this->returnEvent);
                $returnObj->parseEvent();
                $this->returnToParent();
                return;
            } else {
                Out::msgStop("Errore di Inserimento su ANAGRAFICA SOTTOCLASSE.");
            }
        } else {
            Out::msgInfo("Codice già presente", "Combinazione Categoria, Classe e Sottoclasse già  presente. Modificare i valori.");
            Out::setFocus('', $this->nameForm . '_ANAFAS[FASCOD]');
        }
        /* Sblocco la tabella se bloccata ed ha dato un errore. */
        if ($retLock) {
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
            if ($retUnlock['status'] != 0) {
                Out::msgStop('Errore', 'Sblocco Tabella ANACAT non Riuscito.');
                return;
            }
        }
    }

    public function Aggiorna() {
        $Versione = $_POST[$this->nameForm . '_ANAFAS']['VERSIONE_T'];
        $Versione_rec = $this->proLibTitolario->GetVersione($Versione, 'codice');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        $Anafas_rec = $_POST[$this->nameForm . '_ANAFAS'];
        $update_Info = 'Oggetto: ' . $Anafas_rec['FASCOD'] . " " . $Anafas_rec['FASDES'];
        if ($this->updateRecord($this->PROT_DB, 'ANAFAS', $Anafas_rec, $update_Info)) {
            // Aggiornamento Titolario Precedente:
            if (!$this->AggiornaSottoClasseSucc($Anafas_rec)) {
                return false;
            }
            /* Ritorno al chiamante */
            $returnObj = itaModel::getInstance($this->returnModel);
            $returnObj->setEvent($this->returnEvent);
            $returnObj->parseEvent();
            $this->returnToParent();
        } else {
            Out::msgStop("Attenzione", "Errore in aggiornamento SottoClasse.");
        }
    }

    public function AggiornaSottoClasseSucc($Anafas_rec) {
        /*
         * Se indicato codice categoriaf
         * Aggiorno solo se non è indicato un rowid. altrimenti
         */
        $CatCod_pre = $_POST[$this->nameForm . '_CATCOD_PREC'];
        $ClaCod_pre = $_POST[$this->nameForm . '_CLACOD_PREC'];
        $FasCod_pre = $_POST[$this->nameForm . '_FASCOD_PREC'];
        if ($CatCod_pre && $ClaCod_pre && $FasCod_pre) {
            $VersionePre = $_POST[$this->nameForm . '_VERSIONE_PREC'];
            $AnafasPre_rec = $this->proLib->GetAnafas($VersionePre, $CatCod_pre . $ClaCod_pre . $FasCod_pre, 'fasccf');
            if ($AnafasPre_rec) {
                /* Rileggo Anafas */
                $Anafas_rec = $this->proLib->GetAnafas('', $Anafas_rec['ROWID'], 'rowid');
                /* Aggiorno */
                $TitolarioSucc = array();
                $TitolarioSucc['VERSIONE_T'] = $Anafas_rec['VERSIONE_T'];
                $TitolarioSucc['SOTTOCLASSE'] = $Anafas_rec['FASCCF'];
                if (!$this->proLibTitolario->AggiornaTitolarioSucc($AnafasPre_rec['ROWID'], 'SOTTOCLASSE', $TitolarioSucc)) {
                    Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                    return false;
                }
            }
        }
        return true;
    }

}

?>
