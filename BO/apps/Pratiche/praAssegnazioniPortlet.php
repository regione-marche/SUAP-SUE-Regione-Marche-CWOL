<?php

/**
 *
 * VISUALIZZAZIONE DEI PASSI DA PRENDERE IN CARICO, CHIUSI, APERTI
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    16.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function praAssegnazioniPortlet() {
    $praAssegnazioniPortlet = new praAssegnazioniPortlet();
    $praAssegnazioniPortlet->parseEvent();
    return;
}

class praAssegnazioniPortlet extends itaModel {

    public $PRAM_DB;
    public $PROT_DB;
    public $ITALWEB_DB;
    public $proLib;
    public $praLib;
    public $praPerms;
    public $utiEnte;
    public $accLib;
    public $rowidAppoggio;
    public $rowidDipendente;
    public $rowidTipoPasso;
    public $nameForm = "praAssegnazioniPortlet";
    public $gridPassi = "praAssegnazioniPortlet_gridPassiAss";

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->proLib = new proLib();
        $this->utiEnte = new utiEnte();
        $this->praPerms = new praPerms();
        $this->accLib = new accLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITALWEB_DB = $this->utiEnte->getITALWEB_DB();
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->rowidDipendente = App::$utente->getKey($this->nameForm . '_rowidDipendente');
        $this->rowidTipoPasso = App::$utente->getKey($this->nameForm . '_rowidTipoPasso');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_rowidDipendente', $this->rowidDipendente);
            App::$utente->setKey($this->nameForm . '_rowidTipoPasso', $this->rowidTipoPasso);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                Out::delContainer($_POST['context'] . "-wait");
                itaLib::openForm($this->nameForm, '', true, $container = $_POST['context'] . "-content");
                Out::attributo($this->nameForm . "_InCarico", "checked", "0", "checked");
                $this->CaricaPassi();
                break;
            case 'dbClickRow':
                $this->rowidAppoggio = $_POST['rowid'];
                $propas_rec = $this->praLib->GetPropas($_POST['rowid'], "rowid");
                $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
                if (!$ret) {
                    if ($Utenti_rec['UTEANA__3'] != $propas_rec['PRORPA']) {
                        Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti");
                        break;
                    }
                }
                $bottoni = array();
                $bottoni['Vai alla Pratica'] = array('id' => $this->nameForm . '_ApriPratica', 'model' => $this->nameForm, 'shortCut' => "f8");
                $profilo = proSoggetto::getProfileFromIdUtente();
                $funzioniAssegnazione = praFunzionePassi::getFunzioniAssegnazione($propas_rec['PRONUM'], $propas_rec['ROWID'], $profilo);
                if ($funzioniAssegnazione['ASSEGNA'])
                    $bottoni['Assegna Pratica'] = array('id' => $this->nameForm . '_AssegnaPratica', 'model' => $this->nameForm);
                Out::show($this->nameForm . "_AssegnaPraticaButt");
                if ($funzioniAssegnazione['RESTITUISCI'])
                    $bottoni['Restituisci Pratica'] = array('id' => $this->nameForm . '_Restituisci', 'model' => $this->nameForm);
                if ($funzioniAssegnazione['PRENDIINCARICO'])
                    $bottoni['Prendi in Carico'] = array('id' => $this->nameForm . '_PrendiInCarico', 'model' => $this->nameForm);
                if ($funzioniAssegnazione['RIAPRI'])
                    $bottoni['Annulla Presa In Carico'] = array('id' => $this->nameForm . '_Riapri', 'model' => $this->nameForm);
                $Serie_rec = $this->praLib->ElaboraProgesSerie($proges_rec['GESNUM']);
                $messaggio = "<br><br>Pratica Numero: {$Serie_rec}<br><br>{$proges_rec['GESOGG']}";
                Out::msgQuestion("Gestisci Assegnazione:", $messaggio, $bottoni);
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql($_POST[$this->nameForm . '_TipiPasso']);
                $ita_grid01 = new TableView($this->gridPassi, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('PRONUM');
                $ita_grid01->exportXLS('', 'passi_da_prendere_in_carico.xls');
                break;
            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                $utente = "";
                if (substr($_POST[$this->nameForm . '_TipiPasso'], 0, 2) == "M_") {
                    $utente = App::$utente->getKey('nomeUtente');
                }
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array(
                    "Sql" => $this->CreaSql($_POST[$this->nameForm . '_TipiPasso']),
                    "Ente" => $ParametriEnte_rec['DENOMINAZIONE'],
                    "Utente" => App::$utente->getKey('nomeUtente')
                );
                $itaJR->runSQLReportPDF($this->PRAM_DB, 'praPassiInCarico', $parameters);
                break;
            case 'onClickTablePager':
                $this->praLib->GetLegendaColoriPratiche($this->nameForm . '_daPortale', $this->nameForm . '_daPec', $this->nameForm . '_Altro');
                //
                $ordinamento = $_POST['sidx'];
                if ($ordinamento == 'NUMERO') {
                    $ordinamento = 'PRONUM';
                }
                if ($ordinamento == 'RESPONSABILE') {
                    $ordinamento = 'RESPONSABILE';
                }
                $sql = $this->CreaSql($_POST[$this->nameForm . '_TipiPasso']);
                if (!$sql) {
                    break;
                }
                TableView::disableEvents($this->gridPassi);
                TableView::clearGrid($this->gridPassi);
                $ita_grid01 = new TableView($this->gridPassi, array(
                    'sqlDB' => $this->PRAM_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($ordinamento);
                $ita_grid01->setSortOrder($_POST['sord']);
                $Result_tab = $this->elaboraRecord($ita_grid01->getDataArray());
                $ita_grid01->getDataPageFromArray('json', $Result_tab);
                TableView::enableEvents($this->gridPassi);

                $filent_rec = $this->praLib->GetFilent(24);
                foreach ($ita_grid01->getDataArray() as $proges_rec) {
                    $color = $this->praLib->GetColorPortlet($filent_rec, $proges_rec);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "NUMERO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px','background-color':'$color'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "GESDRI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROSEQ", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROINI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROFIN", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "DATIAGGIUNTIVI", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "RESPONSABILE", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PRODTP", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PRODPA", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                    TableView::setCellValue($this->gridPassi, $proges_rec['ROWID'], "PROGIO", "", "{'vertical-align':'top','padding-top':'2px','padding-right':'2px'}", "", false);
                }

                break;
            case 'onClick';
                switch ($_POST['id']) {
                    case $this->nameForm . "_ApriPasso":
                        $model = 'praPasso';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $this->rowidAppoggio;
                        $_POST['modo'] = "edit";
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnPraPermessiPasso';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . "_ApriPratica":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                        $model = 'praGestElenco';
                        itaLib::openForm($model);
                        /* @var $objModel praGest */
                        $objModel = itaModel::getInstance($model);
                        $_POST = array();
                        $objModel->setEvent('openform');
                        $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                        $_POST['perms'] = $this->perms;
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . "_Riapri":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        //
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(true);
                        $modelObj->setPratica($propas_rec['PRONUM']);
                        $modelObj->setRowidAppoggio($this->rowidAppoggio);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->annullaInCarico();
                        break;
                    case $this->nameForm . "_PrendiInCarico":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        //
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(true);
                        $modelObj->setPratica($propas_rec['PRONUM']);
                        $modelObj->setRowidAppoggio($this->rowidAppoggio);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraPrendiInCaricoPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->prendiInCarico();
                        break;
                    case $this->nameForm . "_Restituisci":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        //
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(true);
                        $modelObj->setPratica($propas_rec['PRONUM']);
                        $modelObj->setRowidAppoggio($this->rowidAppoggio);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->restituisciPratica();
                        break;
                    case $this->nameForm . "_AssegnaPratica":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        //
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);

                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(true);
                        $modelObj->setPratica($propas_rec['PRONUM']);
                        $modelObj->setRowidAppoggio($this->rowidAppoggio);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->assegnaPratica();
                        break;
                    case $this->nameForm . "_FiltraAss":
                        $this->CaricaPassi();
                        break;
                }
                break;
            case 'returnPraAssegnaPratica':
                $this->CaricaPassi();
                break;
            case 'returnPraPrendiInCaricoPratica':
                $this->CaricaPassi();
                $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
                $model = 'praGestElenco';
                itaLib::openForm($model);
                /* @var $objModel praGest */
                $objModel = itaModel::getInstance($model);
                $_POST = array();
                $objModel->setEvent('openform');
                $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
                $_POST['perms'] = $this->perms;
                $objModel->parseEvent();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_rowidDipendente');
        App::$utente->removeKey($this->nameForm . '_rowidTipoPasso');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $this->close = true;
        if ($close)
            $this->close();
    }

    public function CaricaPassi() {
        TableView::enableEvents($this->gridPassi);
        TableView::reload($this->gridPassi);
        Out::setGridCaption($this->gridPassi, "Passi da prendere in carico per l'utente " . App::$utente->getKey('nomeUtente'));
    }

    public function CreaSql($tipoPasso) {
        $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
        $wherePassi = '';
        switch ($tipoPasso) {
            case "IC":
                $wherePassi = " AND PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROPAS.PROOPE <> '' AND PROPAS.PROINI = '' AND PROPAS.PROFIN = '' ";
                break;
            case "A":
                $wherePassi = " AND PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROPAS.PROOPE <> '' AND PROPAS.PROINI <> '' AND PROPAS.PROFIN = '' ";
                break;
            case "C":
                $wherePassi = " AND PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROPAS.PROOPE <> '' AND PROPAS.PROFIN <> '' ";
                break;
        }

        //Filtri per ricerca tabella
//        if ($_POST['NUMERO']) {
//            $pratica = str_repeat("0", 6 - strlen(trim($_POST['NUMERO']))) . trim($_POST['NUMERO']);
//            $whereNumero = " AND PRONUM LIKE '%$pratica%'";
//        }
        if ($_POST['NUMERO']) {
            $whereNumero = " AND SERIE LIKE '%" . addslashes($_POST['NUMERO']) . "%'";
        }
        if ($_POST['PRODPA']) {
            $descPasso = strtolower($this->formData['PRODPA']);
            $whereDescPasso = " AND " . $this->PRAM_DB->strLower('PRODPA') . " LIKE '%$descPasso%'";
        }
        if ($_POST['PRODTP']) {
            $tipoPasso = strtolower($this->formData['PRODTP']);
            $whereTipoPasso = " AND " . $this->PRAM_DB->strLower('PRODTP') . " LIKE '%$tipoPasso%'";
        }
        if ($_POST['PROANN']) {
            $annotazioni = strtolower($this->formData['PROANN']);
            $whereAnnotazioni = " AND " . $this->PRAM_DB->strLower('PROANN') . " LIKE '%$annotazioni%'";
        }
        if ($_POST['PROSEQ']) {
            $whereSeq = " AND PROSEQ = " . $this->formData['PROSEQ'];
        }
        if ($_POST['RESPONSABILE']) {
            $responsabile = strtolower($_POST['RESPONSABILE']);
            $whereResp = " AND (" . $this->PRAM_DB->strLower('ANANOM.NOMCOG') . " LIKE '%$responsabile%' OR " . $this->PRAM_DB->strLower('ANANOM.NOMNOM') . " LIKE '%$responsabile%')";
        }
        if ($_POST['PROSEQ']) {
            $whereProseq = " AND PROSEQ = " . $_POST['PROSEQ'];
        }

        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportello();
        $annoSeguente = date('Y') - 1;

        //Per Senigallia
        $annoAncoraSeguente = date('Y') - 2;

        $sql = " SELECT * FROM ( 
                   SELECT  PROPAS.ROWID,
                            PROGES.GESNUM AS GESNUM," .
                $this->PRAM_DB->strConcat($this->PROT_DB->getDB() . '.ANASERIEARC.DESCRIZIONE', "'/'", "PROGES.SERIEPROGRESSIVO", "'/'", "PROGES.SERIEANNO") . " AS SERIE,
                            PROGES.GESTSP AS GESTSP,
                            PROGES.GESSTT AS GESSTT,
                            PROGES.GESATT AS GESATT,
                            PROGES.GESSPA AS GESSPA, 
                            PROGES.GESDRE AS GESDRE, 
                            PROGES.GESDRI AS GESDRI, 
                            PROGES.GESPRO AS GESPRO, 
                            PROGES.GESDCH AS GESDCH, 
                            PROGES.GESPRA AS GESPRA,
                            PROPAS.PRONUM AS PRONUM, 
                            PROPAS.PROSEQ AS PROSEQ, 
                            PROPAS.PRORIS AS PRORIS, 
                            PROPAS.PROGIO AS PROGIO, 
                            PROPAS.PROTPA AS PROTPA, 
                            PROPAS.PRODTP AS PRODTP, 
                            PROPAS.PRODPA AS PRODPA, 
                            PROPAS.PROFIN AS PROFIN, 
                            PROPAS.PROVPA AS PROVPA, 
                            PROPAS.PROPUB AS PROPUB,
                            PROPAS.PROVPN AS PROVPN, 
                            PROPAS.PROPAK AS PROPAK, 
                            PROPAS.PROCTR AS PROCTR, 
                            PROPAS.PROALL AS PROALL, 
                            PROPAS.PRORPA AS PRORPA, 
                            PROPAS.PROANN AS PROANN, 
                            PROPAS.PROVISIBILITA AS PROVISIBILITA, 
                            PROPAS.PROUTEADD AS PROUTEADD, 
                            PROPAS.PROINI AS PROINI," .
                //ANADESIMPRESA.DESNOM AS ANADES_IMPRESA,
                $this->PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                    FROM 
                            PROPAS 
                    LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA 
                    LEFT OUTER JOIN PROGES ON PROGES.GESNUM=PROPAS.PRONUM
                    LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = " . $this->PRAM_DB->getDB() . ".PROGES.SERIECODICE 
                    WHERE
                        (" . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '" . date('Y') . "' OR " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '$annoSeguente' OR " . $this->PRAM_DB->subString('PRONUM', 1, 4) . " = '$annoAncoraSeguente') 
                        AND PROGES.GESDCH = ''
                $wherePassi
                $whereVisibilita
                $whereDescPasso $whereTipoPasso $whereSeq $whereResp $whereAnnotazioni
                OR (PROPAS.PRORPA = '{$Utenti_rec['UTEANA__3']}' AND PROPAS.PROOPE <> '' AND PROPAS.PROFIN = '' AND PROGES.GESDCH = '' )
                    ) T 
           WHERE 1 $whereNumero $whereProseq
                ";
        //Out::msgInfo("sql", $sql);
        return $sql;
    }

    public function elaboraRecord($passi_view) {
        if ($passi_view) {
            $count = count($passi_view);
            $arrayPassiResult = array();
            for ($keyPasso = 0; $keyPasso < $count; $keyPasso++) {
                $value = $passi_view[$keyPasso];
                if ($value['PROFIN']) {
                    $passi_view[$keyPasso]['STATOPASSO'] = '<span class="ita-icon ita-icon-check-green-24x24">Passo Aperto</span>';
                } elseif ($value['PROINI']) {
                    $passi_view[$keyPasso]['STATOPASSO'] = '<span class="ita-icon ita-icon-check-red-24x24">Passo Chiuso</span>';
                }
                //  $passi_view[$keyPasso]['NUMERO'] = substr($value['PRONUM'], 4, 6) . "/" . substr($value['PRONUM'], 0, 4);
                $passi_view[$keyPasso]['NUMERO'] = $value['SERIE'];

                $passi_view[$keyPasso]['STATOCOM'] = $this->praLib->GetIconStatoCom($value['PROPAK']);

                $datiInsProd = $this->praLib->DatiImpresa($value['PRONUM']);

                if ($_POST['DATIAGGIUNTIVI'] && stripos($datiInsProd['IMPRESA'], $_POST['DATIAGGIUNTIVI']) === false && stripos($datiInsProd['FISCALE'], $_POST['DATIAGGIUNTIVI']) === false) {
                    unset($passi_view[$keyPasso]);
                    continue;
                }
                $passi_view[$keyPasso]['DATIAGGIUNTIVI'] = "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";

                $anapra_rec = $this->praLib->GetAnapra($value['GESPRO']);
                $anaset_rec = $this->praLib->GetAnaset($value['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($value['GESATT']);
                $passi_view[$keyPasso]['PROCEDIMENTO'] = "<div style=\"height:50px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div><div>" . $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . "</div></div>";
            }
        }
        return $passi_view;
    }

}

?>