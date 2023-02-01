<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/lib/itaPHPCityWare/itaLibCity.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function utiCapContab() {
    $utiCapContab = new utiCapContab();
    $utiCapContab->parseEvent();
    return;
}

class utiCapContab extends itaModel {

    const CLASSE_PARAMETRI = 'VIS_CONTABILITA';

    public $CITYWARE_DB;
    public $nameForm = "utiCapContab";
    public $itaLibCity;
    public $devLib;
    public $gridElencoCapitoli = "utiCapContab_gridElencoCapitoli";
    public $capitoli;
    public $modoApertura;
    public $entrataSpesa;

    function __construct() {
        parent::__construct();
        $this->itaLibCity = new itaLibCity();
        $this->devLib = new devLib();
        $this->CITYWARE_DB = $this->itaLibCity->getCITYWARE();
        $this->capitoli = App::$utente->getKey($this->nameForm . '_capitoli');
        $this->modoApertura = App::$utente->getKey($this->nameForm . '_modoApertura');
        $this->entrataSpesa = App::$utente->getKey($this->nameForm . '_entrataSpesa');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_capitoli', $this->capitoli);
            App::$utente->setKey($this->nameForm . '_modoApertura', $this->modoApertura);
            App::$utente->setKey($this->nameForm . '_entrataSpesa', $this->entrataSpesa);
        }
    }

    public function setModoApertura($modoApertura) {
        $this->modoApertura = $modoApertura;
    }

    public function setEntrataSpesa($entrataSpesa) {
        $this->entrataSpesa = $entrataSpesa;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Nascondi();
                $this->CreaCombo();
                $this->MettiArticolo();
                $this->EntrataSpesa();
                Out::show($this->nameForm . '_Elenca');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        if ($_POST[$this->nameForm . '_ANNO'] == '') {
                            Out::msgInfo('ATTENZIONE', 'Indicare l\'anno esercizio.');
                            return;
                        }
                        $this->elencaCapitoli();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->Nascondi();
                        Out::show($this->nameForm . '_divGestione');
                        Out::hide($this->nameForm . '_divRisultato');
                        TableView::clearGrid($this->gridElencoCapitoli);
                        Out::show($this->nameForm . '_Elenca');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridElencoCapitoli:
                        $this->caricaGriglia($this->gridElencoCapitoli, $this->capitoli, 2);
                        break;
                }
                break;
            case 'dbClickRow':
                $indice = $_POST['rowid'];
                if ($indice >= 0) {
                    if ($this->modoApertura == 'ricerca') {
                        $model = $this->returnModel;
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['capitolo'] = $this->capitoli[$indice];
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->returnToParent();
                    }
//                    else {
//                        $model = 'utiCapSituazione';
//                        itaLib::openForm($model);
//                        $formObj = itaModel::getInstance($model);
//                        $formObj->setReturnModel($this->nameForm);
//                        $formObj->setCapitolo($this->capitoli[$indice]);
//                        $formObj->setEvent('openform');
//                        $formObj->parseEvent();
//                    }
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridElencoCapitoli:
                        switch ($_POST['colName']) {
                            case 'IMPEGNI':
                                $indice = $_POST['rowid'];
                                if ($indice >= 0) {
                                    $model = 'utiCapImpegni';
                                    itaLib::openForm($model);
                                    $formObj = itaModel::getInstance($model);
                                    $formObj->setReturnModel($this->nameForm);
                                    $formObj->setCapitolo($this->capitoli[$indice]);
                                    $formObj->setEvent('openform');
                                    $formObj->parseEvent();
                                }
                                break;
                            case 'SITUAZIONE':
                                $indice = $_POST['rowid'];
                                if ($indice >= 0) {
                                    $model = 'utiCapSituazione';
                                    itaLib::openForm($model);
                                    $formObj = itaModel::getInstance($model);
                                    $formObj->setReturnModel($this->nameForm);
                                    $formObj->setCapitolo($this->capitoli[$indice]);
                                    $formObj->setEvent('openform');
                                    $formObj->parseEvent();
                                }
                                break;
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_capitoli');
        App::$utente->removeKey($this->nameForm . '_modoApertura');
        App::$utente->removeKey($this->nameForm . '_entrataSpesa');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divRisultato');
        if ($this->entrataSpesa) {
            Out::hide($this->nameForm . '_ES_field');
        } else {
            Out::show($this->nameForm . '_ES_field');
        }
//        if ($this->modoApertura == 'ricerca') {
//            TableView::hideCol($this->gridElencoCapitoli, 'IMPEGNI');
//        }
    }

    public function CreaCombo() {
        $AnnoCorr = date('Y');
        $AnnoCorr = $AnnoCorr + 2;
        $AnnoIniz = $AnnoCorr - 5;
        Out::select($this->nameForm . '_ANNO', 1, "", "", "");
        for ($i = $AnnoIniz; $i <= $AnnoCorr; $i++) {
            if ($i == date('Y')) {
                Out::select($this->nameForm . '_ANNO', 1, $i, "1", $i);
            } else {
                Out::select($this->nameForm . '_ANNO', 1, $i, "0", $i);
            }
        }
    }

    public function GetParametri() {
        $Parametri = array();
        $EnvParametri = $this->devLib->getEnv_config(self::CLASSE_PARAMETRI, 'codice', '', true);
        foreach ($EnvParametri as $key => $Parametro) {
            $Parametri[$Parametro['CHIAVE']] = $Parametro['CONFIG'];
        }
        return $Parametri;
    }

    public function elencaCapitoli() {
        $codMec = $_POST[$this->nameForm . '_CODMEC'];
        $codCap = $_POST[$this->nameForm . '_CODCAP'];
        $articolo = $_POST[$this->nameForm . '_ARTICOLO'];
        $desCap = $_POST[$this->nameForm . '_DESCAP'];
        $anno = $_POST[$this->nameForm . '_ANNO'];
        $entrataSpesa = $_POST[$this->nameForm . '_ES'];

        $Parametri = $this->GetParametri();
        switch ($Parametri['VIS_CONT_CITYWARE']) {
            case '0':
                $modoAccesso = '3';
                break;
            case '1':
                $modoAccesso = '0';
                break;
            case '':
            default:
                $modoAccesso = '';
                break;
        }
        $sql = $this->itaLibCity->creaSqlContabilita($anno, $modoAccesso);

        if ($codMec != '') {
            $sql.=" AND CODMECCAN = '$codMec'";
        }
        if ($codCap != '') {
            $sql.=" AND CODVOCEBIL = '$codCap$articolo' ";
        }
        if ($desCap != '') {
            $sql.=" AND FBA_BILAD.DES_BILAV LIKE '%$desCap%'";
        }
        if ($this->entrataSpesa) {
            $sql.=" AND FBA_BILAD.E_S = '$this->entrataSpesa'";
        } else {
            if ($entrataSpesa) {
                $sql.=" AND FBA_BILAD.E_S = '$entrataSpesa'";
            }
        }
//        $sql.= " ORDER BY CAPITOLI.E_S, CAPITOLI.ESERCIZIO, CAPITOLI.CAPITOLO";
//        Out::msgInfo('', $sql);

        $this->capitoli = ItaDB::DBSQLSelect($this->CITYWARE_DB, $sql, true);
//Out::msgInfo('',print_r($this->capitoli,true));
        if ($this->capitoli) {
            $this->colonnaImpegni();
            $this->colonnaSituazione();
            $this->colonnaCapitoli();
            $this->colonnaPianoFinanziario();
            $this->caricaGriglia($this->gridElencoCapitoli, $this->capitoli);
            $this->Nascondi();
            Out::show($this->nameForm . '_AltraRicerca');
            Out::hide($this->nameForm . '_divGestione');
            Out::show($this->nameForm . '_divRisultato');
        } else {
            Out::msgInfo('AVVISO', 'Non trovati capitoli da visualizzare.');
        }
    }

    public function CaricaGriglia($griglia, $dati, $tipo = '1', $pageRows = '22') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $dati,
            'rowIndex' => 'idx')
        );
        if ($_POST['page']) {
            $pageRows = $_POST['rows'];
        }
        $ita_grid01->setPageRows($pageRows);
        if ($tipo != '1') {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setSortIndex($_POST['sidx']);
            $ita_grid01->setSortOrder($_POST['sord']);
        } else {
            $ita_grid01->setPageNum(1);
        }
        TableView::enableEvents($griglia);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($griglia);
        }
    }

    public function colonnaImpegni() {
        foreach ($this->capitoli as $key => $capitolo) {
            $this->capitoli[$key]['IMPEGNI'] = '<span title="Vedi Impegni" class="ita-icon ita-icon-cerca-doc-24x24"></span>';
        }
    }

    public function colonnaSituazione() {
        foreach ($this->capitoli as $key => $capitolo) {
            $this->capitoli[$key]['SITUAZIONE'] = '<span title="Vedi Impegni" class="ita-icon ita-icon-cerca-24x24"></span>';
        }
    }

    public function colonnaCapitoli() {
        foreach ($this->capitoli as $key => $capitolo) {
            $this->capitoli[$key]['CODVOCEBIL'] = substr($capitolo['CODVOCEBIL'], 0, -2) . '.' . substr($capitolo['CODVOCEBIL'], -2);
        }
    }

    public function colonnaPianoFinanziario() {
        foreach ($this->capitoli as $key => $capitolo) {
            $ALiv1 = str_pad($capitolo['COD_LIV1'], 1, '0', STR_PAD_LEFT);
            $ALiv2 = str_pad($capitolo['COD_LIV2'], 2, '0', STR_PAD_LEFT);
            $ALiv3 = str_pad($capitolo['COD_LIV3'], 2, '0', STR_PAD_LEFT);
            $ALiv4 = str_pad($capitolo['COD_LIV4'], 2, '0', STR_PAD_LEFT);
            $ALiv5 = str_pad($capitolo['COD_LIV5'], 3, '0', STR_PAD_LEFT);
            $this->capitoli[$key]['PIANOFIN'] = $ALiv1 . '.' . $ALiv2 . '.' . $ALiv3 . '.' . $ALiv4 . '.' . $ALiv5;
        }
    }

    public function MettiArticolo() {
        Out::valore($this->nameForm . '_ARTICOLO', '00');
    }

    public function EntrataSpesa() {
        Out::select($this->nameForm . '_ES', 1, "", "", "");
        Out::select($this->nameForm . '_ES', 1, "E", "", "Entrata");
        Out::select($this->nameForm . '_ES', 1, "S", "0", "Spesa");
    }

}

?>
