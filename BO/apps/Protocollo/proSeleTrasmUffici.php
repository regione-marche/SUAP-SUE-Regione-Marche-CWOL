<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    04.04.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proSeleTrasmUffici() {
    $proSeleTrasmUffici = new proSeleTrasmUffici();
    $proSeleTrasmUffici->parseEvent();
    return;
}

class proSeleTrasmUffici extends itaModel {

    public $PROT_DB;
    public $nameForm = "proSeleTrasmUffici";
    public $divGridUffici = "proSeleTrasmUffici_divGrigliaUffici";
    public $gridUffici = "proSeleTrasmUffici_gridUffici";
    public $divGridUfficiTree = "proSeleTrasmUffici_divGriglaUfficiTree";
    public $gridUfficiTree = "proSeleTrasmUffici_gridUfficiTree";
    public $proLib;
    public $proLibAllegati;
    public $ArrUffici = array();
    private $arrayData;
    private $treeView;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ArrUffici = App::$utente->getKey($this->nameForm . '_ArrUffici');
        $this->arrayData = App::$utente->getKey($this->nameForm . '_arrayData');
        $this->treeView = App::$utente->getKey($this->nameForm . '_treeView');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ArrUffici', $this->ArrUffici);
            App::$utente->setKey($this->nameForm . '_arrayData', $this->arrayData);
            App::$utente->setKey($this->nameForm . '_treeView', $this->treeView);
        }
    }

    function getArrUffici() {
        return $this->ArrUffici;
    }

    function setArrUffici($ArrUffici) {
        $this->ArrUffici = $ArrUffici;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openform':
                $this->OpenForm();
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_AssegnaPersona':
                        $this->ReturnAssegnazioni('Persona');
                        break;
                    case $this->nameForm . '_AssegnaUfficio':
                        $this->ReturnAssegnazioni('Ufficio');
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridUffici:
                        $sql = $this->CreaSql();
                        $this->ArrUffici = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
                        $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
                        break;
                    case $this->gridUfficiTree:
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_ArrUffici');
        App::$utente->removeKey($this->nameForm . '_arrayData');
        App::$utente->removeKey($this->nameForm . '_treeView');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function CreaSql() {
        $sql = "SELECT * FROM ANAUFF WHERE ANAUFF.UFFANN = 0 ";
        if ($_POST['_search']) {
            if ($_POST['UFFDES']) {
                $sql .= " AND " . $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper($_POST['UFFDES'])) . "%' ";
            }

            if ($_POST['UFFCOD']) {
                $sql .= " AND UFFCOD LIKE '%" . $_POST['UFFCOD'] . "%' ";
            }
        }
        return $sql;
    }

    public function OpenForm() {
        Out::hide($this->nameForm . '_AssegnaUfficio');
        $this->treeView = false;
        $anaent_57_rec = $this->proLib->GetAnaent('57');
        if ($anaent_57_rec['ENTDE4']) {
            $this->treeView = true;
        }

        $anaent_58_rec = $this->proLib->GetAnaent('58');
        if ($anaent_58_rec['ENTDE4']) {
            Out::show($this->nameForm . '_AssegnaUfficio');
        }

        if (!$this->treeView) {
            $sql = $this->CreaSql();
            $this->ArrUffici = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
            $this->CaricaGriglia($this->gridUffici, $this->ArrUffici);
            Out::hide($this->divGridUfficiTree);
            Out::show($this->divGridUffici);
        } else {
            Out::show($this->divGridUfficiTree);
            Out::hide($this->divGridUffici);
            $this->CaricaTree();
        }
    }

    public function CaricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows('10000');
        if ($_POST['sidx']) {
            $ita_grid01->setSortIndex($_POST['sidx']);
        } else {
            $ita_grid01->setSortIndex('CODUFF');
        }
        if ($_POST['sord']) {
            $ita_grid01->setSortOrder($_POST['sord']);
        } else {
            $ita_grid01->setSortOrder('asc');
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
    }

    private function CaricaTree() {
        $this->getArrayGridUffici();

        $ita_grid = new TableView($this->gridUfficiTree, array(
            'arrayTable' => $this->arrayData,
            'rowIndex' => 'idx'
        ));

        $ita_grid->setPageRows(10000);
        $ita_grid->getDataPage('json');
        TableView::enableEvents($this->gridUfficiTree);
    }

    private function getArrayGridUffici() {
        $this->arrayData = array();
        $sql = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='' AND UFFANN=0";
        $Uffici_padre_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        if ($Uffici_padre_tab) {
            /*
             * 
             * Costruzione Albero classificazione documenti
             */
            $i = 0;
            $this->caricaFigli('', $i, 0);
        }
    }

    private function caricaFigli($cod_padre, &$i, $level_padre) {
        $sqlUffici = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='$cod_padre' AND UFFANN=0 AND TIPOUFFICIO<>'R' ORDER BY UFFCOD";
        $Uffici_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlUffici, true);

        $sqlRuoli = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='$cod_padre' AND UFFANN=0 AND TIPOUFFICIO='R'  ORDER BY LIVELLOVIS, UFFCOD";
        $Ruoli_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlRuoli, true);

        $id_parent = $i;
        $expanded = 'true';
        if ($level_padre > 0) {
            $expanded = 'false';
        }
        foreach ($Ruoli_figli_tab as $Ruoli_figli_rec) {
            $i++;
            $curr_idx = $i;

            $descrRuolo = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-group" title="Ruolo"></span>';
            $descrRuolo .= $Ruoli_figli_rec['UFFDES'] . ' <span style="color:darkred;font-weight:bold;"> [' . $Ruoli_figli_rec['LIVELLOVIS'] . ']</span>';

            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => $expanded,
                'INDICE' => $i,
                'UFFCOD' => $Ruoli_figli_rec['UFFCOD'],
                'UFFDES' => '<div>' . $descrRuolo . '</div>',
                'ROWID' => $Ruoli_figli_rec['ROWID'],
                'CODICE_PADRE' => $Ruoli_figli_rec['CODICE_PADRE']
            );

            $this->caricaFigli($Ruoli_figli_rec['UFFCOD'], $i, $level_padre + 1);
            if ($i > $curr_idx) {
                $this->arrayData[$curr_idx]['isLeaf'] = false;
            }
        }

        foreach ($Uffici_figli_tab as $Uffici_figli_rec) {
            $i++;
            $curr_idx = $i;

            $descrUfficio = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-home" title="Ufficio"></span>';
            $descrUfficio .= $Uffici_figli_rec['UFFDES'];

            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => $expanded,
                'INDICE' => $i,
                'UFFCOD' => $Uffici_figli_rec['UFFCOD'],
                'UFFDES' => '<div>' . $descrUfficio . '</div>',
                'ROWID' => $Uffici_figli_rec['ROWID'],
                'CODICE_PADRE' => $Uffici_figli_rec['CODICE_PADRE']
            );

            $this->caricaFigli($Uffici_figli_rec['UFFCOD'], $i, $level_padre + 1);
            if ($i > $curr_idx) {
                $this->arrayData[$curr_idx]['isLeaf'] = false;
            }
        }
    }

    private function ReturnAssegnazioni($TipoAssegnazione = 'Persona') {
        if ($this->treeView) {
            $codiciUffici = array($this->arrayData[$_POST[$this->gridUfficiTree]['gridParam']['selarrrow']]['ROWID']);
        } else {
            $codiciUffici = split(",", $_POST[$this->gridUffici]['gridParam']['selarrrow']);
        }

        $arrUffici = array();
        foreach ($codiciUffici as $codice) {
            $Anauff_rec = $this->proLib->GetAnauff($codice, 'rowid');
            $arrUffici[] = $Anauff_rec;
        }
        $_POST = array();
        $_POST['retUffici'] = $arrUffici;
        $_POST['tipoSelezione'] = $TipoAssegnazione;

        $returnModel = $this->returnModel;
        $returnModelOrig = $returnModel;
        if (is_array($returnModel)) {
            $returnModelOrig = $returnModel['nameFormOrig'];
            $returnModel = $returnModel['nameForm'];
        }
        $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
        $returnObj->setEvent('returnFromSeleTrasmUfficio');
        $returnObj->parseEvent();
        $this->returnToParent();
    }

}
