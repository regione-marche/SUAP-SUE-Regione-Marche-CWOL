<?php

/**
 *  Browser per Forms
 *
 *
 * @category   Library
 * @package    /apps/Generator
 * @author     Carlo Iesari <carlo@iesari.em>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    30.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';

function proArchivioTitolari() {
    $proArchivioTitolari = new proArchivioTitolari();
    $proArchivioTitolari->parseEvent();
    return;
}

class proArchivioTitolari extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibTitolario;
    public $nameForm = "proArchivioTitolari";
    public $buttonBar = "proArchivioTitolari_buttonBar";
    public $gridTitolario = "proArchivioTitolari_gridTitolario";
    public $gridVersioni = "proArchivioTitolari_gridVersioni";
    public $Versione;
    private $Titolario = array();
    public $Where = array();

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibTitolario = new proLibTitolario();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->Titolario = App::$utente->getKey($this->nameForm . '_Titolario');
        $this->Where = App::$utente->getKey($this->nameForm . '_Where');
        $this->Versione = App::$utente->getKey($this->nameForm . '_Versione');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_Titolario', $this->Titolario);
            App::$utente->setKey($this->nameForm . '_Versione', $this->Versione);
        }
    }

    public function getWhere() {
        return $this->Where;
    }

    public function setWhere($Where) {
        $this->Where = $Where;
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;

            case 'addGridRow':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridTitolario:
                        $this->CaricaTitolario();
                        $this->CaricaGrigliaTitolario();

                        break;
                    case $this->gridVersioni:
                        $this->CaricaElencoVersioni();
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridVersioni:
                        // Test Titolario.
                        App::log($_POST['rowid']);
                        $Versione_T = $_POST['rowid'];
                        $this->GestioneVersione($Versione_T);
                        break;
                    case $this->gridTitolario:
                        // Test Titolario.
                        App::log($_POST);
                        $chiave = $_POST['rowid'];
                        $Prog_Titp = $this->Titolario[$chiave]['CHIAVE'];
                        $this->GestioneVoceTitolario($Prog_Titp);
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridVersioni:
                        switch ($_POST['colName']) {
                            case 'DETTAGLIO_T':
                                TableView::clearGrid($this->gridTitolario);
                                TableView::clearToolbar($this->gridTitolario);
                                $Versione_t = $_POST['rowid'];
                                $this->Versione = $Versione_t;
                                $this->ElencaTitolario();
                                break;
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->ElencaVersioni();
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_TornaElenco':
                        $this->Nascondi();
                        Out::hide($this->nameForm . '_divRicerca');
                        Out::show($this->nameForm . '_divRisultato');

                        Out::show($this->nameForm . '_divGridVersione');
                        Out::hide($this->nameForm . '_divGridTitolario');

                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_NuovaVersione');
                        break;

                    case $this->nameForm . '_NuovaVersione':
                        $this->GestioneVersione();
                        break;

                    case $this->nameForm . '_NuovoTitolario':
                        $this->GestioneVoceTitolario();
                        break;
                    case $this->nameForm . '_CopiaTitolario':
                        $this->ChiediVersioneDestinazione();
                        break;
                    case $this->nameForm . '_CopiaNuovaVersione_butt':
                        $where = " AND VERSIONE_T <> {$this->Versione} ";
                        proRicTitolario::proRicVersioni($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_ConfermaCopiaTotolario':
                        if (!$_POST[$this->nameForm . '_CopiaNuovaVersione']) {
                            Out::msgInfo('Attenzione', 'Occorre indicare la versione in cui copiare il Titolario.');
                            $this->ChiediVersioneDestinazione();
                            break;
                        }
                        $this->CopiaVersioniTitolario($_POST[$this->nameForm . '_CopiaNuovaVersione']);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnVersione':
                $this->ElencaVersioni();
                break;
            case 'returnTitolario':
                $this->CaricaTitolario();
                TableView::enableEvents($this->gridTitolario);
                TableView::reload($this->gridTitolario);
                break;
            case 'returnRicVersione':
                $progKey = $_POST['retKey'];
                Out::valore($this->nameForm . '_CopiaNuovaVersione', $progKey);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_Titolario');
        App::$utente->removeKey($this->nameForm . '_Where');
        App::$utente->removeKey($this->nameForm . '_Versione');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_NuovoTitolario');
        Out::hide($this->nameForm . '_NuovaVersione');
        Out::hide($this->nameForm . '_CopiaTitolario');
    }

    public function OpenRicerca() {
        $this->Nascondi();
        $this->Versione = '';
        Out::show($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        TableView::clearGrid($this->gridTitolario);
        Out::clearFields($this->nameForm . '_divRicerca');

        Out::show($this->nameForm . '_Elenca');
    }

    public function ElencaVersioni() {
        $this->Nascondi();
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_NuovaVersione');
        /* Visualizzo div Versione */
        Out::show($this->nameForm . '_divGridVersione');
        Out::hide($this->nameForm . '_divGridTitolario');
        $this->CaricaElencoVersioni();
    }

    public function GetSqlVersioni() {
        $sql = "SELECT * 
                    FROM AAC_VERS 
                WHERE FLAG_DIS <> 1 ";
        // where AND DATAFINE = ''
        return $sql;
    }

    public function CaricaElencoVersioni() {
        TableView::clearGrid($this->gridVersioni);
        $sql = $this->GetSqlVersioni();
        $ita_grid01 = new TableView($this->gridVersioni, array('sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));

        $ita_grid01->setPageNum('1');
        $ita_grid01->setPageRows('10000');
        $ordinamento = $_POST['sidx'];
        $ita_grid01->setSortIndex($ordinamento);
        $ita_grid01->setSortOrder($_POST['sord']);

        $Result_tab = $this->elaboraRecords($ita_grid01->getDataArray());
        $ita_grid01->getDataPageFromArray('json', $Result_tab);
        TableView::enableEvents($this->gridVersioni);
    }

    private function elaboraRecords($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            $result_tab[$key]['DETTAGLIO_T'] = '<span class="ita-icon ita-icon-cerca-24x24"></span>';
        }
        return $result_tab;
    }

    public function ElencaTitolario() {
        TableView::enableEvents($this->gridTitolario);
        TableView::reload($this->gridTitolario);
        $this->Nascondi();
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_TornaElenco');
        Out::show($this->nameForm . '_NuovoTitolario');
        Out::show($this->nameForm . '_CopiaTitolario');

        /* Visualizzo div Titolario */
        Out::hide($this->nameForm . '_divGridVersione');
        Out::show($this->nameForm . '_divGridTitolario');
    }

    public function GetSqlLivello($Versione, $Prog_titpp) {
        $sql = "SELECT * FROM ATD_TITOPR 
                            WHERE VERSIONE_T = $Versione AND 
                            PROG_TITPP = $Prog_titpp 
                        ORDER BY TITP_CATEG,TITP_CLASS,TITP_FASCI ASC ";

        return $sql;
    }

    public function CaricaTitolario() {
        if (!$this->Versione) {
            Out::msgStop("Attenzione", "Nessuna versione di titolario scelta.");
            return false;
        }

        $this->Titolario = array();
        $filter = $_POST['_search'] == true ? $_POST['CLASSIFICAZIONE'] : false;
        $this->Titolario = $this->proLibTitolario->GetTreeTitolario($this->Versione, $filter);
    }

    private function GestioneVersione($Versione_T = '') {
        $modelDaAprire = 'proGestVersioneTit';
        itaLib::openDialog($modelDaAprire);
        $modelAtto = itaModel::getInstance($modelDaAprire);
        $modelAtto->setEvent('openform');
        $modelAtto->setReturnModel($this->nameForm);
        $modelAtto->setReturnEvent('returnVersione');
        if ($Versione_T) {
            $modelAtto->setVersione_T($Versione_T);
        }
        $modelAtto->parseEvent();
    }

    private function GestioneVoceTitolario($Prog_Titp = '') {
        $modelDaAprire = 'proGestTitolario';
        itaLib::openDialog($modelDaAprire);
        $modelAtto = itaModel::getInstance($modelDaAprire);
        $modelAtto->setEvent('openform');
        $modelAtto->setReturnModel($this->nameForm);
        $modelAtto->setReturnEvent('returnTitolario');
        if ($Prog_Titp) {
            $modelAtto->setProg_Titp($Prog_Titp);
        }
        $modelAtto->setVersione_T($this->Versione);

        $modelAtto->parseEvent();
    }

    public function CaricaGrigliaTitolario() {
        TableView::clearGrid($this->gridTitolario);
        $gridScheda = new TableView($this->gridTitolario, array('arrayTable' => $this->Titolario, 'rowIndex' => 'idx'));
        $gridScheda->setPageNum($_POST['page']);
        $gridScheda->setPageRows($_POST['rows']);
        $gridScheda->setSortIndex($_POST['sidx']);
        $gridScheda->setSortOrder($_POST['sord']);
        $gridScheda->getDataPage('json');
    }

    public function ChiediVersioneDestinazione() {
        $valori[] = array(
            'label' => array(
                'value' => "Seleziona la versione in cui copiare il Titolario.",
                'style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_CopiaNuovaVersione',
            'name' => $this->nameForm . '_CopiaNuovaVersione',
            'type' => 'text',
            'size' => '8',
            'value' => '',
            'class' => 'ita-edit-lookup'
        );
        Out::msgInput(
                'Copia Titolario', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaCopiaTotolario', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaCopiaTotolario', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '500', true
        );
    }

    public function CopiaVersioniTitolario($VersioneDestino = '') {
        if (!$VersioneDestino) {
            Out::msgStop("Attenzione", "Occorre indicare la Versione in cui copiare il Titolario Corrente.");
            return false;
        }
        $Aac_ver_new = $this->proLibTitolario->GetVersione($VersioneDestino);
        if (!$Aac_ver_new) {
            Out::msgStop("Attenzione", "Versione selezionata inesistente.");
            return false;
        }
        $sql = "SELECT * FROM ATD_TITOPR 
                            WHERE VERSIONE_T = $VersioneDestino ";
        $CheckTitolario = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($CheckTitolario) {
            Out::msgStop("Attenzione", "La versione in cui copiare il Titolario Corrente deve essere vuota.");
            return false;
        }
        // Controllo che la versione di partenza abbia almeno qualcosa da copiare...
        $sql = "SELECT * FROM ATD_TITOPR 
                            WHERE VERSIONE_T = {$this->Versione} AND DATACESS = '' ";
        $CheckCurrTitolario = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if (!$CheckCurrTitolario) {
            Out::msgStop("Attenzione", "La nessun Titolario Valido trovato da Copiare nella nuova versione.");
            return false;
        }

        if (!$this->proLibTitolario->CopiaTitolario($this->Versione, $VersioneDestino, true)) {
            Out::msgStop("Attenzione", "Errore in copia Titolario." . $this->proLibTitolario->getErrMessage());
            return false;
        }

        Out::msgInfo("Copia Titolario", "Copia del titolario avvenuta con successo nella Versione: $VersioneDestino - {$Aac_ver_new['DESCRI']}");
    }

    public function OldGetTreeTitolario($Versione, $filter = '', $expandedForce = '', $where = '') {
        $filter = $_POST['_search'] == true ? $_POST['CLASSIFICAZIONE'] : false;
        $expanded = $filter ? 'true' : 'false';

        if ($where) {
            $expanded = 'true';
        }
        if ($expandedForce) {
            $expanded = $expandedForce;
        }

        $i = 1;
        $matrice = array();
        $parent = $i;

        $sql = "SELECT * FROM AACVERS WHERE VERSIONE_T = " . $Versione;
        $Versione_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        if (!$Versione_rec) {
            return array();
        }

        $sqlCat = $this->GetSqlLivelloFromNodo($Versione_rec['VERSIONE_T'], 0);
        $anacat_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlCat, true);

        foreach ($anacat_tab as $k => $categoria) {
            $FiglioLiv2 = false;
            $parent = ++$i;

            $parent = ++$i;
            $sqlCla = $this->GetSqlLivelloFromNodo($Versione_rec['VERSIONE_T'], $categoria['PROG_TITP']);

            $anacla_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlCla, true);
            if ($anacla_tab) {
                foreach ($anacla_tab as $classe) {
                    $FiglioLiv3 = false;
                    $iPadre = $i++;

                    $iPadre = ++$i;
                    $parent2 = ++$i;
                    $sqlFas = $this->GetSqlLivelloFromNodo($Versione_rec['VERSIONE_T'], $classe['PROG_TITP']);

                    $anafas_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlFas, true);
                    if ($anafas_tab) {
                        foreach ($anafas_tab as $fasicolo) {
                            /* Ricerco il fascicolo */
                            $DescrizioneFascicolo = $fasicolo['TITP_CATEG'] . '.' . $fasicolo['TITP_CLASS'] . '.' . $fasicolo['TITP_FASCI'] . ' ' . $fasicolo['DES_TITP'];
                            if ($filter && strpos(strtolower($DescrizioneFascicolo), strtolower($filter)) === false) {
                                continue;
                            }
                            $FiglioLiv3 = true;
                            $matrice[++$i] = array(
                                'level' => 2,
                                'parent' => $parent2,
                                'isLeaf' => 'true',
                                'loaded' => 'true',
                                'expanded' => $expanded,
                                'INDICE' => $i,
                                'CLASSIFICAZIONE' => $DescrizioneFascicolo,
                                'VERSIONE' => $Versione_rec['DESCRI_B'],
                                'DATAFINE' => $fasicolo['DATACESS'],
                                'CHIAVE' => $fasicolo['PROG_TITP']
                            );
                        }
                    }
                    /* Ricerco la classe */
                    $DescrizioneClasse = $classe['TITP_CATEG'] . '.' . $classe['TITP_CLASS'] . ' ' . $classe['DES_TITP'];
                    $DescrTrovataClasse = strpos(strtolower($DescrizioneClasse), strtolower($filter));
                    if ($filter && $DescrTrovataClasse === false && $FiglioLiv3 == false) {
                        continue;
                    }
                    $FiglioLiv2 = true;
                    if (!$filter || ($filter && $iPadre > $parent) || ($DescrTrovataClasse)) {
                        $matrice[++$iPadre] = array(
                            'level' => 1,
                            'parent' => $parent,
                            'isLeaf' => $FiglioLiv3 ? 'false' : 'true',
                            'loaded' => 'true',
                            'expanded' => $expanded,
                            'INDICE' => $iPadre,
                            'CLASSIFICAZIONE' => $DescrizioneClasse,
                            'VERSIONE' => $Versione_rec['DESCRI_B'],
                            'DATAFINE' => $classe['DATACESS'],
                            'CHIAVE' => $classe['PROG_TITP']
                        );
                    }
                }
            }

            $DescrizioneCategoria = '  ' . $categoria['TITP_CATEG'] . ' ' . $categoria['DES_TITP'];
            $DescTrovata = strpos(strtolower($DescrizioneCategoria), strtolower($filter));

            if ($filter && $DescTrovata === false && $FiglioLiv2 == false) {
                continue;
            }
            if (!$filter || ($filter && $i > $parent) || ($DescTrovata)) {
                $matrice[$parent] = array(
                    'level' => 0,
                    'parent' => null,
                    'isLeaf' => 'false',
                    'loaded' => 'true',
                    'expanded' => $expanded,
                    'INDICE' => $parent,
                    'CLASSIFICAZIONE' => $DescrizioneCategoria,
                    'VERSIONE' => $Versione_rec['DESCRI_B'],
                    'DATAFINE' => $categoria['DATACESS'],
                    'CHIAVE' => $categoria['PROG_TITP']
                );
            }
        }
        /*
         * Sorto per chiave per visualizzare bene la griglia
         */
        ksort($matrice);
        return $matrice;
    }

}

?>
