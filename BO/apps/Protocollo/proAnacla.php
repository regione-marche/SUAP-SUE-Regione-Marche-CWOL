<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

function proAnacla() {
    $proAnacla = new proAnacla();
    $proAnacla->parseEvent();
    return;
}

class proAnacla extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSerie;
    public $nameForm = "proAnacla";
    public $divGes = "proAnacla_divGestione";
    public $divRis = "proAnacla_divRisultato";
    public $divRic = "proAnacla_divRicerca";
    public $gridAnacla = "proAnacla_gridAnacla";
    public $gridSerie = "proAnacla_gridSerie";
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
//            App::log($this->PROT_DB);
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
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
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
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridSerie:
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacla_rec['CLACCA'], $Versione_rec['VERSIONE_T']);
                        break;
                }
                break;
            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnacla', $parameters);
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANACLA[CLACAT]':
                        $codice = $_POST[$this->nameForm . '_ANACLA']['CLACAT'];
                        if (trim($codice) != "") {
                            $codice = str_pad(trim($codice), 4, '0', STR_PAD_LEFT);
                            $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                            if (!$Versione_rec) {
                                Out::msgInfo("Attenzione", "Versione non trovata.");
                                break;
                            }
                            $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $codice, 'codice');
                            if ($Anacat_rec) {
                                Out::valore($this->nameForm . '_ANACLA[CLACAT]', $codice);
                                Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
                            } else {
                                Out::msgInfo("Attenzione", "Categoria non trovata.");
                                Out::valore($this->nameForm . '_ANACLA[CLACAT]', '');
                                Out::valore($this->nameForm . '_CATDES', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_CATDES', "");
                        }
                        break;
                    case $this->nameForm . '_ANACLA[CLACOD]':
                        $codice = $_POST[$this->nameForm . '_ANACLA']['CLACOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANACLA[CLACOD]', $codice);
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
                        $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacla_rec['CLACCA'], $Versione_rec['VERSIONE_T']);
                        break;

                    case $this->nameForm . '_ANACLA[CLACAT]_butt':
                        $where = array();
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $Versione_rec['VERSIONE_T'], $where, 'returnTitolario', 1);
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;
                    case $this->nameForm . '_Progressivo':
                        if ($_POST[$this->nameForm . '_ANACLA']['CLACAT'] != '') {
                            for ($i = 1; $i <= 9999; $i++) {
                                $codice = str_repeat("0", 4 - strlen(trim($i))) . trim($i);
                                $anacla_rec = $this->proLib->GetAnacla('', $_POST[$this->nameForm . '_ANACLA']['CLACAT'] . $codice);
                                if (!$anacla_rec) {
                                    Out::valore($this->nameForm . '_ANACLA[CLACOD]', $codice);
                                    Out::setFocus('', $this->nameForm . '_CLADES');
                                    break;
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
                        try {
                            $codice = $_POST[$this->nameForm . '_ANACLA']['CLACAT'] . $_POST[$this->nameForm . '_ANACLA']['CLACOD'];
                            $sql = "SELECT FASCCF FROM ANAFAS WHERE FASCCF LIKE '" . $codice . "%' OR FASCCA LIKE '" . $codice . "%'";
                            $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT ORGCCF FROM ANAORG WHERE  ORGCCF LIKE '" . $codice . "%'";
                            $Anaorg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT PROCAT FROM ANAPRO WHERE PROCAT = '" . $codice . "' OR PROCCF LIKE '" . $codice . "%'";
                            $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            // Controllo se è usato in altre anagrafiche e nelle procedure
                            //if ($Anafas_tab != null || $Anaorg_tab != null || $Anapro_tab != null) {
                            if ($Anafas_tab || $Anaorg_tab || $Anapro_tab) {
                                Out::msgStop("Attenzione!", 'Impossibile cancellare la Classe perché è assegnata ad altre Anagrafiche o Procedure.');
                            } else {
                                Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA CLASSI", $e->getMessage());
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $delete_Info = 'Oggetto: ' . $Anacla_rec['CLACOD'] . " " . $_POST[$this->nameForm . '_CLADES'];
                        if ($this->deleteRecord($this->PROT_DB, 'ANACLA', $Anacla_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_CancellaCodicePrec':
                        Out::msgQuestion("Cancellazione", "Confermi la <b>cancellazione definitiva</b> del collegamento con la Classe precedente?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnCancPre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfCancPre', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_CLACOD_PREC_butt':
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
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $titolarioPrecedente, array(), 'returnTitolarioPrec', 2, true);
                        break;

                    case $this->nameForm . '_ConfCancPre':
                        Out::valore($this->nameForm . '_CATCOD_PREC', '');
                        Out::valore($this->nameForm . '_CLACOD_PREC', '');
                        Out::valore($this->nameForm . '_CLADES_PREC', '');
                        Out::valore($this->nameForm . '_VERSIONE_PREC', '');
                        Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', '');

                        $rowidPre = $_POST[$this->nameForm . '_ANACLAPREC']['ROWID'];
                        if ($rowidPre) {
                            $TitolarioSucc = array();
                            $TitolarioSucc['VERSIONE_T'] = '';
                            $TitolarioSucc['CLASSE'] = '';
                            if (!$this->proLibTitolario->AggiornaTitolarioSucc($rowidPre, 'CLASSE', $TitolarioSucc)) {
                                Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                                break;
                            }
                            Out::valore($this->nameForm . '_ANACLAPREC[ROWID]', '');
                        }
                        Out::show($this->nameForm . '_CLACOD_PREC_butt');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnTitolario':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_ANACLA[CLACAT]', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_CATDES', $retTitolario['CATDES']);
                break;
            case 'returnTitolarioPrec':
                $retTitolario = $_POST['rowData'];
                if (!$retTitolario['CATCOD'] || !$retTitolario['CLACOD']) {
                    Out::msgStop("Attenzione", "Selezionare una classe, non una categoria.");
                    break;
                }
                Out::valore($this->nameForm . '_CATCOD_PREC', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_CLACOD_PREC', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_CLADES_PREC', $retTitolario['DECOD_DESCR']);
                Out::valore($this->nameForm . '_VERSIONE_PREC', $retTitolario['VERSIONE_T']);
                Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $retTitolario['VERSIONE']);
                break;

            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                    $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                    if (!$this->proLibSerie->AggiungiSerieATitolario($AnaserieArc_rec['CODICE'], $Anacla_rec['CLACCA'], $Versione_rec['VERSIONE_T'])) {
                        Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                        break;
                    }
                    $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacla_rec['CLACCA'], $Versione_rec['VERSIONE_T']);
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
            Out::msgStop("Attenzione", "Rowid Classe Mancante.");
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
        Out::valore($this->nameForm . '_ANACAT[VERSIONE_T]', $Versione_rec['VERSIONE_T']);
        Out::valore($this->nameForm . '_DESC_TITOLARIO', $Versione_rec['DESCRI']);

        /* Decodifico i dati */
        $Anacla_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
        // Controllo Anacla.
        if (!$Anacla_rec) {
            Out::msgStop("Attenzione", "Classe non trovata.");
            $this->close();
            return false;
        }
        $open_Info = 'Dettaglio Oggetto: ' . $Versione_rec['VERSIONE_T'] . " " . $Anacla_rec['CATCOD'] . " " . $Anacla_rec['CATDES'];
        $this->openRecord($this->PROT_DB, 'ANACLA', $open_Info);
        /* Valorizzazione campi */
        $this->Nascondi();
        Out::valori($Anacla_rec, $this->nameForm . '_ANACLA');
        Out::valore($this->nameForm . '_CLADES', trim($Anacla_rec['CLADE1']) . trim($Anacla_rec['CLADE2']));
        /* Decodifico la categoria */
        $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $Anacla_rec['CLACAT'], 'codice');
        if ($Anacat_rec) {
            Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
        }

        /* Decodifico Collegamento Classe precedente */
        Out::show($this->nameForm . '_CLACOD_PREC_butt');
        $AnaclaPrec_rec = $this->proLibTitolario->GetCollegamentoTitolarioSuccessivo($Versione_rec['VERSIONE_T'], '', $Anacla_rec['CLACCA']);
        if ($AnaclaPrec_rec) {
            Out::valore($this->nameForm . '_ANACLAPREC[ROWID]', $AnaclaPrec_rec['ROWID']);
            Out::valore($this->nameForm . '_CATCOD_PREC', $AnaclaPrec_rec['CLACAT']);
            Out::valore($this->nameForm . '_CLACOD_PREC', $AnaclaPrec_rec['CLACOD']);
            Out::valore($this->nameForm . '_CLADES_PREC', trim($AnaclaPrec_rec['CLADE1']) . trim($Anacla_rec['CLADE2']));
            Out::valore($this->nameForm . '_VERSIONE_PREC', $AnaclaPrec_rec['VERSIONE_T']);
            $VersionePre_rec = $this->proLibTitolario->GetVersione($AnaclaPrec_rec['VERSIONE_T'], 'codice');
            Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $VersionePre_rec['DESCRI_B']);
            Out::hide($this->nameForm . '_CLACOD_PREC_butt');
        }

        Out::show($this->nameForm . '_Aggiorna');

        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        // Categoria e classe non più modificabili 
        Out::hide($this->nameForm . '_ANACLA[CLACAT]_butt');
        Out::attributo($this->nameForm . '_ANACLA[CLACAT]', 'readonly', '0');
        Out::attributo($this->nameForm . '_ANACLA[CLACOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_CLADES');

        Out::show($this->nameForm . '_divGestSerie');
        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacla_rec['CLACCA'], $Versione_rec['VERSIONE_T']);
        TableView::disableEvents($this->gridAnacla);
    }

    function GetAnacla($_Cond, $_Codice) {
        $sql = "SELECT ROWID FROM ANACLA WHERE $_Cond AND CLACCA='$_Codice'";
        $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anacla_tab;
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

        Out::valore($this->nameForm . '_ANACLA[VERSIONE_T]', $Versione_rec['VERSIONE_T']);
        Out::valore($this->nameForm . '_DESC_TITOLARIO', $Versione_rec['DESCRI']);
        // Decodifico la classe padre.
        if ($this->RowidPadre) {
            $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $this->RowidPadre, 'rowid');
            if ($Anacat_rec) {
                Out::valore($this->nameForm . '_ANACLA[CLACAT]', $Anacat_rec['CATCOD']);
                Out::valore($this->nameForm . '_CATDES', $Anacat_rec['CATDES']);
            }
        }

        /* Nascondo campi div inutilizzati */
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        /* Visualizzo campi utilizzati */
        Out::attributo($this->nameForm . '_ANACLA[CLACAT]', 'readonly', '1');
        Out::attributo($this->nameForm . '_ANACLA[CLACOD]', 'readonly', '1');
        Out::show($this->nameForm . '_ANACLA[CLACAT]_butt');
        Out::show($this->nameForm . '_ANACLA[CLACAT]_butt');
        Out::show($this->nameForm . '_Aggiungi');
        Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');
    }

    public function Aggiungi() {
        /*
         * Controllo dati obbligatori
         */
        if (!trim($_POST[$this->nameForm . '_ANACLA']['CLACAT'])) {
            Out::msgInfo("Attenzione", "Codice Categoria obbligatoria.");
            return;
        }

        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        /*
         * Controllo codice categoria:
         */
        $codiceCat = $_POST[$this->nameForm . '_ANACLA']['CLACAT'];
        $codiceCat = str_pad(trim($codiceCat), 4, '0', STR_PAD_LEFT);
        $AnacatTest_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $codiceCat, 'codice');
        if (!$AnacatTest_rec) {
            Out::msgInfo("Attenzione", "Categoria inesistente.");
            return;
        }

        $codice = $_POST[$this->nameForm . '_ANACLA']['CLACOD'];
        /* Prenota per nuovo progressivo */
        $retLock = '';
        if (!trim($codice)) {
            $retLock = ItaDB::DBLock($this->PROT_DB, "ANACLA", "", "", 20);
            if ($retLock['status'] != 0) {
                Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVO ANACLA non riuscito.');
                return;
            }
            /* Prenoto numero versione ed escludo il 100: da assegnare. */
            $sqlMax = "SELECT MAX(CLACOD) AS MAX_CLACOD 
                                FROM ANACLA 
                        WHERE VERSIONE_T = " . $Versione_rec['VERSIONE_T'] . " 
                        AND CLACAT = '$codiceCat'
                        AND CLACOD <> '0100' ";
            $MaxAac_vers_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sqlMax, false);
            $MaxCla = 1;
            if ($MaxAac_vers_rec) {
                $MaxCla = $MaxAac_vers_rec['MAX_CLACOD'] + 1;
                // Se trovato titolario 100.
                if ($MaxAac_vers_rec['MAX_CLACOD'] == 100) {
                    $MaxAac_vers_rec['MAX_CLACOD'] ++;
                }
            }
            $codice = $MaxCla;
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
        $ClaDes = $_POST[$this->nameForm . '_CLADES'];
        $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
        $Anacla_rec['CLACOD'] = $codice;
        $Anacla_rec['CLACAT'] = $codiceCat;
        $Anacla_rec['CLACCA'] = $Anacla_rec['CLACAT'] . $codice;
        $Anacla_rec['CLADE1'] = substr($ClaDes, 0, 100);
        $Anacla_rec['CLADE2'] = substr($ClaDes, 100, 100);
        /* Test della classe-categoria esistente */
        $AnaclaTest_rec = $this->proLib->GetAnacla($Versione_rec['VERSIONE_T'], $Anacla_rec['CLACCA'], 'codice', false);
        if (!$AnaclaTest_rec) {
            $insert_Info = 'Oggetto: ' . $Versione_rec['VERSIONE_T'] . " " . $Anacla_rec['CLACOD'] . " " . $Anacla_rec['CLADE1'];
            if ($this->insertRecord($this->PROT_DB, 'ANACLA', $Anacla_rec, $insert_Info)) {
                if ($retLock) {
                    /* Sblocco la tabella se bloccata. */
                    $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
                    if ($retUnlock['status'] != 0) {
                        Out::msgStop('Errore', 'Sblocco Tabella ANACLA non Riuscito.');
                        return;
                    }
                }
                // Aggiornamento Titolario Precedente:
                if (!$this->AggiornaClasseSucc($Anacla_rec)) {
                    return false;
                }
                /* Ritorno al chiamante */
                $returnObj = itaModel::getInstance($this->returnModel);
                $returnObj->setEvent($this->returnEvent);
                $returnObj->parseEvent();
                $this->returnToParent();
                return;
            } else {
                Out::msgStop("Errore di Inserimento su ANAGRAFICA CLASSI.");
            }
        } else {
            Out::msgInfo("Codice già presente", "Inserire un nuovo codice Classe.");
            Out::setFocus('', $this->nameForm . '_ANACLA[CLACOD]');
        }
        /* Sblocco la tabella se bloccata e ha dato un errore. */
        if ($retLock) {
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
            if ($retUnlock['status'] != 0) {
                Out::msgStop('Errore', 'Sblocco Tabella ANACAT non Riuscito.');
                return;
            }
        }
    }

    public function Aggiorna() {
        $Versione = $_POST[$this->nameForm . '_ANACLA']['VERSIONE_T'];
        $Versione_rec = $this->proLibTitolario->GetVersione($Versione, 'codice');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        /* Lettura dei dati dal post */
        $Anacla_rec = $_POST[$this->nameForm . '_ANACLA'];
        $Anacla_rec['CLADE1'] = substr($_POST[$this->nameForm . '_CLADES'], 0, 100);
        $Anacla_rec['CLADE2'] = substr($_POST[$this->nameForm . '_CLADES'], 100, 100);
        /* Non serve controllare se presente un doppione, non può entrare */
        $update_Info = 'Oggetto: ' . $Anacla_rec['CLACOD'] . " " . $Anacla_rec['CLADE1'];
        if ($this->updateRecord($this->PROT_DB, 'ANACLA', $Anacla_rec, $update_Info)) {
            /* Ritorno al chiamante */
            // Aggiornamento Titolario Precedente:
            if (!$this->AggiornaClasseSucc($Anacla_rec)) {
                return false;
            }
            $returnObj = itaModel::getInstance($this->returnModel);
            $returnObj->setEvent($this->returnEvent);
            $returnObj->parseEvent();
            $this->returnToParent();
        } else {
            Out::msgStop("Attenzione", "Errore in aggiornamento CLASSE.");
        }
    }

    public function AggiornaClasseSucc($Anacla_rec) {
        /*
         * Se indicato codice categoria
         * Aggiorno solo se non è indicato un rowid. altrimenti
         */
        $CatCod_pre = $_POST[$this->nameForm . '_CATCOD_PREC'];
        $ClaCod_pre = $_POST[$this->nameForm . '_CLACOD_PREC'];
        if ($CatCod_pre && $ClaCod_pre) {
            $VersionePre = $_POST[$this->nameForm . '_VERSIONE_PREC'];
            $AnaclaPre_rec = $this->proLib->GetAnacla($VersionePre, $CatCod_pre . $ClaCod_pre, 'codice');
            if ($AnaclaPre_rec) {
                /* Aggiorno */
                $TitolarioSucc = array();
                $TitolarioSucc['VERSIONE_T'] = $Anacla_rec['VERSIONE_T'];
                $TitolarioSucc['CLASSE'] = $Anacla_rec['CLACAT'] . $Anacla_rec['CLACOD'];
                if (!$this->proLibTitolario->AggiornaTitolarioSucc($AnaclaPre_rec['ROWID'], 'CLASSE', $TitolarioSucc)) {
                    Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                    return false;
                }
            }
        }
        return true;
    }

}

?>