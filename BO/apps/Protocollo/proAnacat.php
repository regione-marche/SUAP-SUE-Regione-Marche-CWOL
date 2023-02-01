<?php

/* * 
 *
 * 
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    29.04.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';

function proAnacat() {
    $proAnacat = new proAnacat();
    $proAnacat->parseEvent();
    return;
}

class proAnacat extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSerie;
    public $proLibTitolario;
    public $nameForm = "proAnacat";
    public $divGes = "proAnacat_divGestione";
    public $divRis = "proAnacat_divRisultato";
    public $divRic = "proAnacat_divRicerca";
    public $gridAnacat = "proAnacat_gridAnacat";
    public $gridSerie = "proAnacat_gridSerie";
    public $RowidVersione_T;
    public $RowidDettaglio;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibTitolario = new proLibTitolario();
            $this->proLibSerie = new proLibSerie();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->RowidVersione_T = App::$utente->getKey($this->nameForm . '_RowidVersione_T');
            $this->RowidDettaglio = App::$utente->getKey($this->nameForm . '_RowidDettaglio');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_RowidVersione_T', $this->RowidVersione_T);
            App::$utente->setKey($this->nameForm . '_RowidDettaglio', $this->RowidDettaglio);
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

    public function parseEvent() {

        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
            case 'DaTitolario':
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

            case 'printTableToHTML':
                $Anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                $itaJR->runSQLReport($this->PROT_DB, 'proAnacat', "PDF", $parameters);
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridSerie:
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacat_rec['CATCOD'], $Versione_rec['VERSIONE_T']);
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
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancellaSerie':
                        $rowid = $_POST[$this->gridSerie]['gridParam']['selarrrow'];
                        if (!$this->proLibSerie->CancellaSerieTitolario($rowid)) {
                            Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                            break;
                        }
                        Out::msgBlock('', 2000, false, "Collegamento alla Serie cancellato correttamente.");
                        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                        $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacat_rec['CATCOD'], $Versione_rec['VERSIONE_T']);
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;
                    case $this->nameForm . '_Progressivo':
                        for ($i = 1; $i <= 9999; $i++) {
                            $codice = str_repeat("0", 4 - strlen(trim($i))) . trim($i);
                            $anacat_rec = $this->proLib->GetAnacat('', $codice);
                            if (!$anacat_rec) {
                                Out::valore($this->nameForm . '_ANACAT[CATCOD]', $codice);
                                Out::setFocus('', $this->nameForm . '_ANACAT[CATDES]');
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        if ($this->perms['noDelete']) {
                            Out::msgInfo("Attenzione", "Cancellazione Non Abilitata.");
                            break;
                        }
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $Anacat_rec = $_POST[$this->nameForm . '_ANACAT'];
                        try {
                            $codice = $_POST[$this->nameForm . '_ANACAT']['CATCOD'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $sql = "SELECT CLACCA FROM ANACLA WHERE CLADAT" . $this->PROT_DB->isBlank() . " AND CLACCA LIKE '" . $codice . "%'";
                            $Anacla_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT FASCCF FROM ANAFAS WHERE FASDAT" . $this->PROT_DB->isBlank() . " AND FASCCF LIKE '" . $codice . "%'";
                            $Anafas_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT ORGCCF FROM ANAORG WHERE ORGDAT" . $this->PROT_DB->isBlank() . " AND ORGCCF LIKE '" . $codice . "%'";
                            $Anaorg_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            $sql = "SELECT PROCAT FROM ANAPRO WHERE PROCAT = '" . $codice . "'";
                            $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            // Controllo se è usato in altre anagrafiche e nelle procedure
                            if ($Anacla_tab != null || $Anafas_tab != null || $Anaorg_tab != null || $Anapro_tab != null) {
                                Out::msgStop("Attenzione!", 'Impossibile cancellare la Categoria perché è assegnata ad altre Anagrafiche o Procedure.');
                            } else {
                                $delete_Info = 'Oggetto: ' . $Anacat_rec['CATCOD'] . " " . $Anacat_rec['CATDES'];
                                if ($this->deleteRecord($this->PROT_DB, 'ANACAT', $Anacat_rec['ROWID'], $delete_Info)) {
                                    $this->OpenRicerca();
                                }
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su ANAGRAFICA CATEGORIE", $e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_CATCOD_PREC_butt':
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
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $titolarioPrecedente, array(), 'returnTitolarioPrec', 1, true);
                        break;

                    case $this->nameForm . '_CancellaCodicePrec':
                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione del collegamento con la Categoria precedente?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnCancPre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfCancPre', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfCancPre':
                        Out::valore($this->nameForm . '_CATCOD_PREC', '');
                        Out::valore($this->nameForm . '_CATDES_PREC', '');
                        Out::valore($this->nameForm . '_VERSIONE_PREC', '');
                        Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', '');

                        $rowidPre = $_POST[$this->nameForm . '_ANACATPREC']['ROWID'];
                        if ($rowidPre) {
                            $TitolarioSucc = array();
                            $TitolarioSucc['VERSIONE_T'] = '';
                            $TitolarioSucc['CATEGORIA'] = '';
                            if (!$this->proLibTitolario->AggiornaTitolarioSucc($rowidPre, 'CATEGORIA', $TitolarioSucc)) {
                                Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                                break;
                            }
                            Out::valore($this->nameForm . '_ANACATPREC[ROWID]', '');
                        }
                        Out::show($this->nameForm . '_CATCOD_PREC_butt');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANACAT[CATCOD]':
                        $codice = $_POST[$this->nameForm . '_ANACAT']['CATCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANACAT[CATCOD]', $codice);
                        }
                        break;
                }
                break;

            case 'returnTitolarioPrec':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_CATCOD_PREC', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_CATDES_PREC', $retTitolario['DECOD_DESCR']);
                Out::valore($this->nameForm . '_VERSIONE_PREC', $retTitolario['VERSIONE_T']);
                Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $retTitolario['VERSIONE']);
                break;

            case 'returnSerieArc':
                App::log('ritorno');
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
                    $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
                    if (!$this->proLibSerie->AggiungiSerieATitolario($AnaserieArc_rec['CODICE'], $Anacat_rec['CATCOD'], $Versione_rec['VERSIONE_T'])) {
                        Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                        break;
                    }
                    $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacat_rec['CATCOD'], $Versione_rec['VERSIONE_T']);
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_perms');
        App::$utente->removeKey($this->nameForm . '_RowidVersione_T');
        App::$utente->removeKey($this->nameForm . '_RowidDettaglio');
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
        // Controllo Rowid Dettaglio...
        if (!$this->RowidDettaglio) {
            Out::msgStop("Attenzione", "Rowid Categoria Mancante.");
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
        $Anacat_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $this->RowidDettaglio, 'rowid');
        $open_Info = 'Dettaglio Oggetto: ' . $Versione_rec['VERSIONE_T'] . " " . $Anacat_rec['CATCOD'] . " " . $Anacat_rec['CATDES'];
        $this->openRecord($this->PROT_DB, 'ANACAT', $open_Info);

        $this->Nascondi();
        Out::valori($Anacat_rec, $this->nameForm . '_ANACAT');
        Out::show($this->nameForm . '_Aggiorna');

        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);
        // Categoria non più modificabile
        Out::attributo($this->nameForm . '_ANACAT[CATCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANACAT[CATDES]');

        /* Collegamento categoria precedente */
        Out::show($this->nameForm . '_CATCOD_PREC_butt');
        $AnacatPrec_rec = $this->proLibTitolario->GetCollegamentoTitolarioSuccessivo($Versione_rec['VERSIONE_T'], $Anacat_rec['CATCOD']);
        if ($AnacatPrec_rec) {
            Out::valore($this->nameForm . '_ANACATPREC[ROWID]', $AnacatPrec_rec['ROWID']);
            Out::valore($this->nameForm . '_CATCOD_PREC', $AnacatPrec_rec['CATCOD']);
            Out::valore($this->nameForm . '_CATDES_PREC', $AnacatPrec_rec['CATDES']);
            Out::valore($this->nameForm . '_VERSIONE_PREC', $AnacatPrec_rec['VERSIONE_T']);
            $VersionePre_rec = $this->proLibTitolario->GetVersione($AnacatPrec_rec['VERSIONE_T'], 'codice');
            Out::valore($this->nameForm . '_DESC_TITOLARIO_PREC', $VersionePre_rec['DESCRI_B']);
            Out::hide($this->nameForm . '_CATCOD_PREC_butt');
        }
        Out::show($this->nameForm . '_divGestSerie');
        $this->proLibSerie->CaricaGrigliaSerie($this->gridSerie, $Anacat_rec['CATCOD'], $Versione_rec['VERSIONE_T']);
        TableView::disableEvents($this->gridAnacat);
    }

    public function Nuovo() {
        if (!$this->RowidVersione_T) {
            Out::msgStop("Attenzione", "Versione Titolario mancante.");
            $this->close();
            return false;
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

        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();

        Out::attributo($this->nameForm . '_ANACAT[CATCOD]', 'readonly', '1');
        Out::show($this->nameForm . '_Aggiungi');
        Out::setFocus('', $this->nameForm . '_ANACAT[CATCOD]');
    }

    public function Aggiungi() {
        if ($this->perms['noEdit']) {
            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
            return;
        }
        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        $codice = $_POST[$this->nameForm . '_ANACAT']['CATCOD'];
        /* Prenota per nuovo progressivo */
        $retLock = '';
        if (!trim($codice)) {
            $retLock = ItaDB::DBLock($this->PROT_DB, "ANACAT", "", "", 20);
            if ($retLock['status'] != 0) {
                Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVO ANACAT non riuscito.');
                return;
            }
            /* Prenoto numero versione ed escludo il 100: da assegnare. */
            $sqlMax = "SELECT MAX(CATCOD) AS MAX_CATCOD 
                                FROM ANACAT 
                        WHERE VERSIONE_T = " . $Versione_rec['VERSIONE_T'] . " 
                        AND CATCOD <> '0100' ";
            $MaxAac_vers_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sqlMax, false);
            $MaxCat = 1;
            if ($MaxAac_vers_rec) {
                $MaxCat = $MaxAac_vers_rec['MAX_CATCOD'] + 1;
            }
            $codice = $MaxCat;
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
        $Anacat_rec = $_POST[$this->nameForm . '_ANACAT'];
        $Anacat_rec['CATCOD'] = $codice;
        /* Test Anacat ESISTENTE e li prendo tutti. */
        $AnacatTest_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $codice, 'codice', false);
        if (!$AnacatTest_rec) {
            $insert_Info = 'Oggetto: ' . $Versione_rec['VERSIONE_T'] . " " . $Anacat_rec['CATCOD'] . " " . $Anacat_rec['CATDES'];
            if ($this->insertRecord($this->PROT_DB, 'ANACAT', $Anacat_rec, $insert_Info)) {
                if ($retLock) {
                    /* Sblocco la tabella se bloccata. */
                    $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
                    if ($retUnlock['status'] != 0) {
                        Out::msgStop('Errore', 'Sblocco Tabella ANACAT non Riuscito.');
                        return;
                    }
                }
                if (!$this->AggiornaCategoriaSucc($Anacat_rec)) {
                    return false;
                }
                /* Ritorno al chiamante */
                $returnObj = itaModel::getInstance($this->returnModel);
                $returnObj->setEvent($this->returnEvent);
                $returnObj->parseEvent();
                $this->returnToParent();
                return;
            } else {
                Out::msgStop("Errore di Inserimento su ANAGRAFICA CATEGORIE.");
            }
        } else {
            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
            Out::setFocus('', $this->nameForm . '_ANACAT[CATCOD]');
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
        if ($this->perms['noEdit']) {
            Out::msgInfo("Attenzione", "Gestione Non Abilitata.");
            return;
        }
        $Versione_rec = $this->proLibTitolario->GetVersione($this->RowidVersione_T, 'rowid');
        if (!$Versione_rec) {
            Out::msgInfo("Attenzione", "Versione non trovata.");
            return;
        }
        /* Lettura dei dati dal post */
        $Anacat_rec = $_POST[$this->nameForm . '_ANACAT'];
        $AnacatOrig_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $Anacat_rec['ROWID'], 'rowid');
        // Controllo se Data fine rimossa e stesso codice presente
        if ($Anacat_rec['CATDAT'] == '' && $AnacatOrig_rec['CATDAT'] != '') {
            $AnacatTest_rec = $this->proLib->GetAnacat($Versione_rec['VERSIONE_T'], $Anacat_rec['CATCOD'], 'codice');
            if ($AnacatTest_rec) {
                Out::msgStop("Attenzione", "Categoria valida già presente con lo stesso codice.");
                return;
            }
        }

        $update_Info = 'Oggetto: ' . $Anacat_rec['VERSIONE_T'] . " " . $Anacat_rec['CATCOD'] . " " . $Anacat_rec['CATDES'];
        if ($this->updateRecord($this->PROT_DB, 'ANACAT', $Anacat_rec, $update_Info)) {
            // Aggiornamento Titolario Precedente:
            if (!$this->AggiornaCategoriaSucc($Anacat_rec)) {
                return false;
            }
            /* Ritorno al chiamante */
            $returnObj = itaModel::getInstance($this->returnModel);
            $returnObj->setEvent($this->returnEvent);
            $returnObj->parseEvent();
            $this->returnToParent();
        } else {
            Out::msgStop("Attenzione", "Errore in aggiornamento CATEGORIA.");
        }
    }

    public function AggiornaCategoriaSucc($Anacat_rec) {
        /*
         * Se indicato codice categoria
         * Aggiorno solo se non è indicato un rowid. altrimenti
         */
        $CatCod_pre = $_POST[$this->nameForm . '_CATCOD_PREC'];
        if ($CatCod_pre) {
            $VersionePre = $_POST[$this->nameForm . '_VERSIONE_PREC'];
            $AnacatPre_rec = $this->proLib->GetAnacat($VersionePre, $CatCod_pre, 'codice');
            if ($AnacatPre_rec) {
                /* Aggiorno s */
                $TitolarioSucc = array();
                $TitolarioSucc['VERSIONE_T'] = $Anacat_rec['VERSIONE_T'];
                $TitolarioSucc['CATEGORIA'] = $Anacat_rec['CATCOD'];
                if (!$this->proLibTitolario->AggiornaTitolarioSucc($AnacatPre_rec['ROWID'], 'CATEGORIA', $TitolarioSucc)) {
                    Out::msgStop("Attenzione", $this->proLibTitolario->getErrMessage());
                    return false;
                }
            }
        }
        return true;
    }

}

?>
