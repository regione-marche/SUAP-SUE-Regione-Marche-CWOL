<?php

/**
 *
 * Archivio Vie
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    03.05.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function basVie() {
    $basVie = new basVie();
    $basVie->parseEvent();
    return;
}

class basVie extends itaModel {

    const VIE_CATEGORIA = "VIE";
    const VIEFO_CATEGORIA = "VIEFO";
    const VIEEVENTI_CATEGORIA = "VIEEVT";

    public static $LIST_VIE_CATEGORIE = array(
        array(
            "CATEGORIA" => self::VIE_CATEGORIA,
            "DESCRIZIONE" => "Vie BO",
        ),
        array(
            "CATEGORIA" => self::VIEFO_CATEGORIA,
            "DESCRIZIONE" => "Vie FO",
        ),
        array(
            "CATEGORIA" => self::VIEEVENTI_CATEGORIA,
            "DESCRIZIONE" => "Vie Eventi",
        ),
    );
    public $BASE_DB;
    public $basLib;
    public $nameForm = "basVie";
    public $divGes = "basVie_divGestione";
    public $divRis = "basVie_divRisultato";
    public $divRic = "basVie_divRicerca";
    public $gridVie = "basVie_gridVie";
    private $gridFilters = array();
    private $anacat = '';
    private $backupDownload = false;
    private $parametriCSV = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->basLib = new basLib();
            $this->BASE_DB = $this->basLib->getBASEDB();
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->anacat = App::$utente->getKey($this->nameForm . '_anacat');
            $this->backupDownload = App::$utente->getKey($this->nameForm . '_backupDownload');
            $this->parametriCSV = App::$utente->getKey($this->nameForm . '_parametriCSV');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_anacat', $this->anacat);
            App::$utente->setKey($this->nameForm . '_backupDownload', $this->backupDownload);
            App::$utente->setKey($this->nameForm . '_parametriCSV', $this->parametriCSV);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();

                if ($_POST['Anacat']) {
                    $this->anacat = $_POST['Anacat'];
                } else {
                    $this->anacat = self::VIE_CATEGORIA;
                }
                Out::setAppTitle($this->nameForm, 'Stradario');
                Out::valore($this->nameForm . '_curr_anacat_ric', $this->anacat);
                Out::valore($this->nameForm . '_curr_anacat_gest', $this->anacat);

                $this->OpenRicerca();

                $result = $this->ctrVieFo();
                if ($result['VIEFO'] > 0) {
                    Out::show($this->nameForm . '_ImportaVieFO');
                }

                if ($this->anacat == self::VIE_CATEGORIA) {
                    Out::hide($this->nameForm . '_curr_anacat_ric_butt');
                    Out::hide($this->nameForm . '_curr_anacat_gest_butt');
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridVie:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridVie:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridVie, array(
                    'sqlDB' => $this->BASE_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('ANADES');
                $ita_grid01->exportXLS('', 'vie.xls');
                break;

            case 'printTableToHTML':
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . ' ORDER BY ANADES', "Ente" => '', 'Titolo' => 'Archivio Vie', 'Colonna3' => 'Zona');
                $itaJR->runSQLReportPDF($this->BASE_DB, 'basVie', $parameters);
                break;

            case 'onClickTablePager':
                TableView::clearGrid($this->gridVie);
                $this->setGridFilters();
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->BASE_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $ita_grid01->getDataPage('json');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        TableView::clearGrid($this->gridVie);
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridVie, array(
                            'sqlDB' => $this->BASE_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridVie]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('ANADES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridVie);
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_ANA_COMUNE[ANACOD]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Progressivo');
                        Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANACOD]');
                        break;

                    case $this->nameForm . '_Progressivo':
                        for ($i = 1; $i <= 999999; $i++) {
                            $progressivo = str_pad($i, 6, "0", STR_PAD_LEFT);
                            $vie_rec = $this->basLib->getComana($this->anacat, 'codice', $progressivo);
                            if (!$vie_rec) {
                                Out::valore($this->nameForm . '_ANA_COMUNE[ANACOD]', $progressivo);
                                Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANADES]');
                                break;
                            }
                        }
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_ANA_COMUNE']['ANACOD'];
                        $vie_rec = $this->basLib->getComana($this->anacat, 'codice', $codice);
                        if (!$vie_rec) {
                            $vie_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                            $vie_rec['ANACAT'] = $this->anacat;
                            $insert_Info = 'Oggetto: ' . $vie_rec['ANACOD'] . " " . $vie_rec['ANADES'];
                            if ($this->insertRecord($this->BASE_DB, 'ANA_COMUNE', $vie_rec, $insert_Info)) {
                                $this->OpenRicerca();
                            }
                        } else {
                            Out::msgInfo("Codice gi� presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANACOD]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $vie_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                        $update_Info = 'Oggetto: ' . $vie_rec['ANACOD'] . " " . $vie_rec['ANADES'];
                        if ($this->updateRecord($this->BASE_DB, 'ANA_COMUNE', $vie_rec, $update_Info)) {
                            $this->OpenRicerca();
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $vie_rec = $_POST[$this->nameForm . '_ANA_COMUNE'];
                        $delete_Info = 'Oggetto: ' . $vie_rec['ANACOD'] . " " . $vie_rec['ANADES'];
                        if ($this->deleteRecord($this->BASE_DB, 'ANA_COMUNE', $vie_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;

                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridVie);
                        break;

                    case $this->nameForm . '_DownloadCSV':
                        $fileData = $this->exportCSV();

                        if (!is_dir(itaLib::getAppsTempPath())) {
                            itaLib::createAppsTempPath();
                        }

                        $fileName = $this->nameForm . '_downloadCSV_' . date('YmdHis') . '.csv';
                        $filePath = itaLib::getAppsTempPath() . "/" . $fileName;

                        file_put_contents($filePath, $fileData);

                        Out::openDocument(utiDownload::getUrl($fileName, $filePath));
                        break;

                    case $this->nameForm . '_BackupImportaCSV':
                        if ($this->anacat !== 'VIEFO') {
                            break;
                        }

                        $this->backupDownload = true;

                        $fileData = $this->exportCSV();

                        if (!is_dir(itaLib::getAppsTempPath())) {
                            itaLib::createAppsTempPath();
                        }

                        $fileName = $this->nameForm . '_exportCSV_' . date('YmdHis') . '.csv';
                        $filePath = itaLib::getAppsTempPath() . "/" . $fileName;

                        file_put_contents($filePath, $fileData);

                        Out::openDocument(utiDownload::getUrl($fileName, $filePath));

                    /*
                     * Continuo riaprendo la msgQuestion
                     */

                    case $this->nameForm . '_ImportaCSV':
                        if ($this->anacat !== 'VIEFO') {
                            break;
                        }

                        Out::msgQuestion("Importa CSV", "<b>Attenzione</b><br><br>L'importazione dei dati tramite CSV comporta l'azzeramento dei dati attualmente caricati.<br>E' consigliato effettuare il download del backup dei dati.", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaImportaCSV', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5 - Prosegui' => array('id' => $this->nameForm . '_ProseguiImportaCSV', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'Download Backup' => array('id' => $this->nameForm . '_BackupImportaCSV', 'model' => $this->nameForm)
                        ));
                        break;

                    case $this->nameForm . '_ProseguiImportaCSV':
                        if ($this->anacat !== 'VIEFO') {
                            break;
                        }

                        if (!$this->backupDownload) {
                            Out::msgInfo("Attenzione", "Si sta proseguendo senza aver effettuato il download di backup");
                        }

                        $this->parametriCSV = array();

                        $fields = array(array(
                                'label' => array(
                                    'value' => "Delimitatore",
                                    'style' => 'width: 160px; display: block; float: left; padding: 0 5px 0 0; text-align: right; margin-top: 10px;'
                                ),
                                'id' => $this->nameForm . '_ImportaDelimitatore',
                                'name' => $this->nameForm . '_ImportaDelimitatore',
                                'type' => 'text',
                                'value' => '&quot;',
                                'style' => 'margin-left: 10px; width: 120px; margin-top: 10px;'
                            ), array(
                                'label' => array(
                                    'value' => "Separatore",
                                    'style' => 'width: 160px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                                ),
                                'id' => $this->nameForm . '_ImportaSeparatore',
                                'name' => $this->nameForm . '_ImportaSeparatore',
                                'type' => 'text',
                                'value' => ',',
                                'style' => 'margin-left: 10px; width: 120px;'
                            ), array(
                                'label' => array(
                                    'value' => "Escludi campo Codice",
                                    'style' => 'width: 160px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                                ),
                                'id' => $this->nameForm . '_ImportaEscludi',
                                'name' => $this->nameForm . '_ImportaEscludi',
                                'type' => 'checkbox',
                                'style' => 'margin-left: 10px;'
                            )
                        );

                        Out::msgInput('Importa CSV', $fields, array(
                            'F5 - Prosegui' => array(
                                'id' => $this->nameForm . '_DatiImportaCSV',
                                'model' => $this->nameForm,
                                'class' => 'ita-button',
                                'shortCut' => "f5"
                            )), $this->nameForm . "_workSpace", 'auto', 'auto', true, 'Il CSV deve contenere i campi Codice (se non escluso) e Descrizione');
                        break;

                    case $this->nameForm . '_DatiImportaCSV':
                        if ($this->anacat !== 'VIEFO') {
                            break;
                        }

                        $this->parametriCSV = array(
                            'Delimitatore' => $_POST[$this->nameForm . '_ImportaDelimitatore'],
                            'Separatore' => $_POST[$this->nameForm . '_ImportaSeparatore'],
                            'Escludi' => $_POST[$this->nameForm . '_ImportaEscludi']
                        );

                        $this->backupDownload = false;

                        $model = "utiUploadDiag";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnUploadCSV');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->nameForm . '_ImportaVieFO':
                        $result = $this->ctrVieFo();
                        $msg = 'Sono presenti ' . $result['VIE'] . ' indirizzi con categoria VIE e ' . $result['VIEFO'] . ' indirizzi con categoria VIEFO<br><br>'
                                . 'Se confermi l\'importazione gli indirizzi con categoria VIE verranno cancellate e sostituite con quelle di categoria VIEFO';
                        Out::msgQuestion("ATTENZIONE", $msg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImportazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaVieFO', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaVieFO':
                        $result = $this->importaVieFO();
                        if ($result !== true) {
                            Out::msgStop('ATTENZIONE', 'Importazione NON terminata.');
                        } else {
                            Out::msgInfo('AVVISO', 'Importazione terminata.');
                        }
                        break;
                    case $this->nameForm . '_curr_anacat_ric_butt':
                        basRic::basRicVieAnacat(self::$LIST_VIE_CATEGORIE, $this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Codice':
                        $codice = $_POST[$this->nameForm . '_Codice'];
                        if ($codice != '') {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            $vie_rec = $this->basLib->getComana($this->anacat, 'codice', $codice);
                            if ($vie_rec) {
                                $this->Dettaglio($vie_rec['ROWID']);
                            }
                        }
                        break;

                    case $this->nameForm . '_ANA_COMUNE[ANACOD]':
                        $codice = $_POST[$this->nameForm . '_ANA_COMUNE']['ANACOD'];
                        if ($codice != '') {
                            $codice = str_pad($codice, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANA_COMUNE[ANACOD]', $codice);
                        }
                        break;
                }
                break;

            case 'returnUploadCSV':
                if ($this->anacat !== 'VIEFO') {
                    break;
                }

                if (!$this->importCSV($_POST['uploadedFile'])) {
                    Out::msgStop("Errore", "Errore in importazione: " . $this->impError);
                }

                Out::msgInfo("Importa CSV", "Importazione avvenuta con successo");
                break;
            case 'returnCategorieVie':
                $this->anacat = $_POST['rowData']['CATEGORIA'];
                Out::valore($this->nameForm . '_curr_anacat_ric', $this->anacat);
                break;
        }
    }

    public function close() {
        $this->anacat = App::$utente->removeKey($this->nameForm . '_anacat');
        $this->backupDownload = App::$utente->removeKey($this->nameForm . '_backupDownload');
        $this->parametriCSV = App::$utente->removeKey($this->nameForm . '_parametriCSV');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function CreaCombo() {
        $zone_tab = $this->basLib->GetComana('ZONE');
        Out::select($this->nameForm . '_ANA_COMUNE[ANAFI1__1]', 1, '', "1", '');
        foreach ($zone_tab as $key => $zone_rec) {
            Out::select($this->nameForm . '_ANA_COMUNE[ANAFI1__1]', 1, $zone_rec['ANADES'], "1", $zone_rec['ANADES']);
        }
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_DownloadCSV');
        Out::hide($this->nameForm . '_ImportaVieFO');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');

        if ($this->anacat === 'VIEFO') {
            Out::show($this->nameForm . '_ImportaCSV');
        }
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridVie);
        TableView::clearGrid($this->gridVie);
        TableView::clearToolbar($this->gridVie);
        Out::valore($this->nameForm . '_curr_anacat_ric', $this->anacat);
        Out::valore($this->nameForm . '_curr_anacat_gest', $this->anacat);
        Out::attributo($this->nameForm . '_curr_anacat_ric', 'readonly', '0');
        Out::attributo($this->nameForm . '_curr_anacat_gest', 'readonly', '0');
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_ImportaCSV');
        Out::hide($this->nameForm . '_DownloadCSV');
    }

    function CreaSql() {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='" . $this->anacat . "'";

        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND ANACOD = '" . $_POST[$this->nameForm . '_Codice'] . "'";
        }

        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND ANADES LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }

        $Msg_Err = false;

        if ($this->gridFilters) {
            foreach ($this->gridFilters as $key => $value) {
                switch ($key) {
                    case 'ANACOD':
                    case 'ANADES':
                    case 'ANAFI1__1':
                        $value = str_replace("'", "\'", $value);
                        $sql .= " AND " . $this->BASE_DB->strUpper($key) . " LIKE '%" . strtoupper($value) . "%' ";
                        break;

                    default:
                        if (is_numeric($value)) {
                            $sql .= " AND $key = $value";
                        } else {
                            if ($Msg_Err == false) {
                                $Msg_Err = true;
                                Out::msgInfo("Attenzione", "Sono accettati solo valori numerici.");
                            }
                        }
                        break;
                }
            }
        }

        return $sql;
    }

    function Dettaglio($indice) {
        $vie_rec = $this->basLib->getComana($indice, 'rowid');
        $open_Info = 'Oggetto: ' . $vie_rec['ANACOD'] . " " . $vie_rec['ANADES'];
        $this->openRecord($this->BASE_DB, 'COMANA', $open_Info);
        $this->Nascondi();
        Out::valori($vie_rec, $this->nameForm . '_ANA_COMUNE');
        Out::valore($this->nameForm . '_curr_anacat_gest', $vie_rec['ANACAT']);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::attributo($this->nameForm . '_ANA_COMUNE[ANACOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANA_COMUNE[ANADES]');
        TableView::disableEvents($this->gridVie);
    }

    private function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['ANACOD'] != '') {
            $this->gridFilters['ANACOD'] = $_POST['ANACOD'];
        }
        if ($_POST['ANADES'] != '') {
            $this->gridFilters['ANADES'] = $_POST['ANADES'];
        }
        if ($_POST['ANAFI1__1'] != '') {
            $this->gridFilters['ANAFI1__1'] = $_POST['ANAFI1__1'];
        }
    }

    private function exportCSV($delimitator = '"', $separator = ',') {
        $anacat = $this->anacat ? $this->anacat : $this->anacat;
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT = '" . $anacat . "' ORDER BY ANACOD + 0 ASC";
        $ana_comune_tab = ItaDB::DBSQLSelect($this->BASE_DB, $sql, true);

        foreach ($ana_comune_tab as $ana_comune_rec) {
            $csv = (isset($csv) ? $csv . "\n" : '' ) . "{$delimitator}" . str_replace($delimitator, '\\' . $delimitator, $ana_comune_rec['ANACOD']) . "{$delimitator}{$separator}{$delimitator}" . str_replace($delimitator, '\\' . $delimitator, $ana_comune_rec['ANADES']) . "{$delimitator}";
        }

        return $csv;
    }

    private function importCSV($file) {
        if (!file_exists($file)) {
            $this->impError = 'file non trovato';
            return false;
        }

        $handle = fopen($file, 'r');

        if (!$handle) {
            $this->impError = 'impossibile aprire il file';
            return false;
        }

        $ana_comune_imp_tab = array();

        if (!$this->parametriCSV['Delimitatore']) {
            /*
             * Se vuoto fgetcsv non funziona, ma se non c'� delimitatore sul CSV
             * e si imposta '"' fgetcsv legge ugualmente i valori
             */

            $this->parametriCSV['Delimitatore'] = '"';
        }

        /*
         * Utilizzato solo se escluso il campo Codice nel CSV
         */

        $progressivo = 1;

        while (($data = fgetcsv($handle, 0, $this->parametriCSV['Separatore'], $this->parametriCSV['Delimitatore'])) !== false) {
            if ($this->parametriCSV['Escludi'] == '1') {
                if (count($data) !== 1) {
                    $this->impError = 'file non valido';
                    return false;
                }

                $ana_comune_imp_tab[] = array(
                    'ANACAT' => $this->anacat,
                    'ANACOD' => str_pad($progressivo, 6, '0', STR_PAD_LEFT),
                    'ANADES' => $data[0]
                );

                $progressivo++;
            } else {
                if (count($data) !== 2) {
                    $this->impError = 'file non valido';
                    return false;
                }

                $ana_comune_imp_tab[] = array(
                    'ANACAT' => $this->anacat,
                    'ANACOD' => str_pad($data[0], 6, '0', STR_PAD_LEFT),
                    'ANADES' => $data[1]
                );
            }
        }

        if (!count($ana_comune_imp_tab)) {
            $this->impError = 'file non valido';
            return false;
        }

        $tab_keys = array();

        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT = '" . $this->anacat . "'";
        $ana_comune_tab = ItaDB::DBSQLSelect($this->BASE_DB, $sql, true);

        foreach ($ana_comune_tab as $ana_comune_rec) {
            try {
                ItaDB::DBDelete($this->BASE_DB, 'ANA_COMUNE', 'ROWID', $ana_comune_rec['ROWID']);
            } catch (Exception $e) {
                $this->impError = $e->getMessage();
                return false;
            }

            $tab_keys[$ana_comune_rec['ANACOD']] = $ana_comune_rec;
        }

        foreach ($ana_comune_imp_tab as $ana_comune_imp_rec) {
            $to_insert_rec = $ana_comune_imp_rec;

            if ($tab_keys[$ana_comune_imp_rec['ANACOD']]) {
                $to_insert_rec = $tab_keys[$ana_comune_imp_rec['ANACOD']];
                unset($to_insert_rec['ROWID']);
            }

            try {
                ItaDB::DBInsert($this->BASE_DB, 'ANA_COMUNE', 'ROWID', $to_insert_rec);
            } catch (Exception $e) {
                $this->impError = $e->getMessage();
                return false;
            }
        }

        return true;
    }

    private function ctrVieFo() {
        $result['VIEFO'] = $result['VIE'] = 0;
        $ITALWEB_DB = ItaDb::DBOpen('ITALWEB');
        $sqlTest = "SELECT ROWID FROM ANA_COMUNE WHERE ANACAT='" . self::VIEFO_CATEGORIA . "'";
        $vieFoTab = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlTest, true);
        if ($vieFoTab) {
            $result['VIEFO'] = count($vieFoTab);
        }
        $sqlTest = "SELECT ROWID FROM ANA_COMUNE WHERE ANACAT='" . self::VIE_CATEGORIA . "'";
        $vieTab = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlTest, true);
        if ($vieTab) {
            $result['VIE'] = count($vieTab);
        }
        return $result;
    }

    private function importaVieFO() {
        $ITALWEB_DB = ItaDb::DBOpen('ITALWEB');
        $sqlTest = "SELECT ROWID FROM ANA_COMUNE WHERE ANACAT='" . self::VIE_CATEGORIA . "'";
        $vieTab = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlTest, true);
        if ($vieTab) {
            foreach ($vieTab as $vie) {
                try {
                    ItaDB::DBDelete($ITALWEB_DB, 'ANA_COMUNE', 'ROWID', $vie['ROWID']);
                } catch (Exception $exc) {
                    Out::msgStop("Errore", "Errore in Cancellazione ANA_COMUNE - ROWID " . $vie['ROWID'] . ": " . $exc->getMessage());
                    return false;
                }
            }
        }
        $sqlTest = "SELECT * FROM ANA_COMUNE WHERE ANACAT='" . self::VIEFO_CATEGORIA . "'";
        $vieTab = ItaDB::DBSQLSelect($ITALWEB_DB, $sqlTest, true);
        foreach ($vieTab as $vie) {
            $vie['ROWID'] = 0;
            $vie['ANACAT'] = self::VIE_CATEGORIA;
            try {
                ItaDB::DBInsert($ITALWEB_DB, 'ANA_COMUNE', 'ROWID', $vie);
            } catch (Exception $exc) {
                Out::msgStop("Errore", "Errore in Registrazione ANA_COMUNE - Cod.Via " . $vie['ANACOD'] . ": " . $exc->getMessage());
                return false;
            }
        }
        return true;
    }

}

?>