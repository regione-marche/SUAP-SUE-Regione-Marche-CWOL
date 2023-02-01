<?php

/**
 *
 * GESTIONE FASCICOLI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    28.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proFasGest() {
    $proFasGest = new proFasGest();
    $proFasGest->parseEvent();
    return;
}

class proFasGest extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proFasGest";
    public $divGes = "proFasGest_divGestione";
    public $divRis = "proFasGest_divRisultato";
    public $divRic = "proFasGest_divRicerca";
    public $gridFasGest = "proFasGest_gridFasGest";

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridFasGest:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
//                        Out::hide($this->divRic);
//                        Out::show($this->divGes);
//
//                        itaLib::openForm('proFasExplorer', false, true, $this->nameForm . "_divDettaglio", "", "dialog");
//                        break;
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridFasGest,
                                        array(
                                            'sqlDB' => $this->PROT_DB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridFasGest]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('ORGCCF');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
//                            Out::hide($this->gridFasGest . '_FASCOD');
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::setFocus('', $this->nameForm . '_AltraRicerca');
                            TableView::enableEvents($this->gridFasGest);
                        }

                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Catcod_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        if ($_POST[$this->nameForm . '_Catcod']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_Catcod'] . "'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_Catcod'] . "'";
                            if ($_POST[$this->nameForm . '_Clacod']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . "'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stato':
                        switch ($_POST[$this->nameForm . '_Stato']) {
                            case "A":
                                Out::show($this->nameForm . '_DaData_field');
                                Out::show($this->nameForm . '_AData_field');
                                break;
                            case "C":
                                Out::show($this->nameForm . '_DaData_field');
                                Out::show($this->nameForm . '_AData_field');
                                break;
                            default:
                                Out::hide($this->nameForm . '_DaData_field');
                                Out::hide($this->nameForm . '_AData_field');
                                break;
                        }
                        break;
                    case $this->nameForm . '_Catcod':
                        $codice = $_POST[$this->nameForm . '_Catcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->DecodAnacat($codice);
                        break;
                    case $this->nameForm . '_Clacod':
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        } else {
                            $codice = $_POST[$this->nameForm . '_Catcod'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacat($codice);
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnafas($codice1 . $codice2 . $codice3, 'fasccf');
                        } else {
                            $codice = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = $_POST[$this->nameForm . '_Catcod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnacla($codice1 . $codice2);
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Orgcod':
                        $codice = $_POST[$this->nameForm . '_Orgcod'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_Orgcod', $codice);
                            $anaorg_rec = $this->proLib->GetAnaorg($codice, 'codice', $_POST[$this->nameForm . '_Catcod'] . $_POST[$this->nameForm . '_Clacod'] . $_POST[$this->nameForm . '_Fascod'], $_POST[$this->nameForm . '_Anno']);
                            App::log('$anaorg_rec');
                            App::log($anaorg_rec);

                            if ($anaorg_rec) {
                                $this->Dettaglio($anaorg_rec['ROWID']);
                            }
                        }
                        break;
                }
                break;
            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolario($rowid, $tipoArc);
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_Stato', 1, "T", "1", "Tutti");
        Out::select($this->nameForm . '_Stato', 1, "A", "0", "Aperti");
        Out::select($this->nameForm . '_Stato', 1, "C", "0", "Chiusi");
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::valore($this->nameForm . '_Stato', "A");
        Out::setFocus('', $this->nameForm . '_Catcod');
        TableView::disableEvents($this->gridFasGest);
        TableView::clearGrid($this->gridFasGest);
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANASERIEARC[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ANASERIEARC[TIPOPROGRESSIVO]', 'ANNUALE');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divBarra');

        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Catcod_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_TitolarioDecod');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Fascod_field');
        }
    }

    function CreaSql() {
        // Imposto il filtro di ricerca
        $where = "WHERE ORGCOD=ORGCOD";
        if ($_POST[$this->nameForm . '_Orgcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Orgcod'];
            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCOD='$codice'";
        }
        if ($_POST[$this->nameForm . '_Orgdes'] != "") {
            $valore = addslashes(trim($_POST[$this->nameForm . '_Orgdes']));
            $where .= " AND ".$this->PROT_DB->strUpper('ORGDES')." LIKE '%" . strtoupper($valore) . "%'";
        }
        if ($_POST[$this->nameForm . '_Catcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Catcod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '" . $codice . "%'";
        }
        if ($_POST[$this->nameForm . '_Clacod'] != "") {
            $codice = $_POST[$this->nameForm . '_Clacod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '____" . $codice . "%'";
        }
        if ($_POST[$this->nameForm . '_Fascod'] != "") {
            $codice = $_POST[$this->nameForm . '_Fascod'];
            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
            $where .= " AND ORGCCF LIKE '________" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_Anno'] != "") {
            $where .= " AND ORGANN='" . $_POST[$this->nameForm . '_Anno'] . "'";
        }
        if ($_POST[$this->nameForm . '_Validita'] != "") {
            $where .= " AND ORGDAT<='" . $_POST[$this->nameForm . '_Validita'] . "'";
        }
        $Da_data = $_POST[$this->nameForm . '_DaData'];
        $a_data = $_POST[$this->nameForm . '_AData'];
        switch ($_POST[$this->nameForm . '_Stato']) {
            case "A":
                $where .= " AND (ORGAPE<>0 AND ORGDAT=0)";
                if ($Da_data && $a_data) {
                    $where .= " AND (ORGAPE BETWEEN $Da_data AND $a_data)";
                }
                break;
            case "C":
                $where .= " AND (ORGAPE<>0 AND ORGDAT<>0)";
                if ($Da_data && $a_data) {
                    $where .= " AND (ORGDAT BETWEEN $Da_data AND $a_data)";
                }
                break;
        }
        $sql = "
            SELECT
            ANAORG.ROWID AS ROWID,
            ANAORG.ORGCOD AS ORGCOD,
            ANAORG.ORGANN AS ORGANN,
            ANAORG.ORGDES AS ORGDES,
            ANAORG.ORGDAT AS ORGDAT,
            ANAORG.ORGUOF AS ORGUOF,
            ANAFAS.FASCOD AS FASCOD,
            ANACLA.CLACOD AS CLACOD,
            ANACAT.CATCOD AS CATCOD
        FROM
            ANAORG ANAORG
        LEFT OUTER JOIN ANAFAS ANAFAS 
        ON ANAORG.ORGCCF = ANAFAS.FASCCF
        LEFT OUTER JOIN ANACLA ANACLA 
        ON ".$this->PROT_DB->subString('ANAORG.ORGCCF',1,8)." = ANACLA.CLACCA
        LEFT OUTER JOIN ANACAT ANACAT 
        ON ".$this->PROT_DB->subString('ANAORG.ORGCCF',1,4)." = ANACAT.CATCOD  $where";
        App::log($sql);
        return $sql;
    }

    public function Dettaglio($rowid) {
//        $anaorg_rec = $this->proLib->GetAnaorg($rowid, 'rowid');
//        $open_Info = 'Oggetto: ' . $anaorg_rec['CODICE'] . " " . $anaorg_rec['DESCRIZIONE'];
//        $this->openRecord($this->PROT_DB, 'ANAORG', $open_Info);
//        $this->Nascondi();
//        Out::show($this->nameForm . '_AltraRicerca');
//        Out::hide($this->divRic);
//        Out::hide($this->divRis);
//        Out::show($this->divGes);
        Out::setFocus('', $this->nameForm . '_AltraRicerca');
        TableView::disableEvents($this->gridFasGest);

//        $rowapp = $anaorg_rec['ROWID'];
        $_POST = array();
//        $_POST['chiave'] = $rowapp;
        $_POST['chiave'] = $rowid;
        $_POST['tipoChiave'] = 'rowidAnaorg';
        $_POST['returnModel'] = 'returnProFasGest';
        $model = 'proFasExplorer';
        itaLib::openForm('proFasExplorer'); //, false, true, $this->nameForm . "_divHost", "", "dialog");
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setEvent('openform');
        $formObj->parseEvent();
        $this->OpenRicerca();
    }

    private function elaboraRecords($result_tab) {
        return $result_tab;
    }

    function decodTitolario($rowid, $tipoArc) {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_Catcod', $cat);
        Out::valore($this->nameForm . '_Clacod', $cla);
        Out::valore($this->nameForm . '_Fascod', $fas);
        Out::valore($this->nameForm . '_TitolarioDecod', $des);
    }

    function DecodAnacat($codice, $tipo = 'codice') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_Catcod', '');
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anacat_rec;
    }

    function DecodAnacla($codice, $tipo = 'codice') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anacla_rec;
    }

    function DecodAnafas($codice, $tipo = 'codice') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anafas_rec;
    }

}

?>
