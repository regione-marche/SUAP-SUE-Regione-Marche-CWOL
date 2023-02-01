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

function proTitolario() {
    $proTitolario = new proTitolario();
    $proTitolario->parseEvent();
    return;
}

class proTitolario extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proTitolario";
    public $buttonBar = "proTitolario_buttonBar";
    public $gridTitolario = "proTitolario_gridTitolario";
    private $Titolario = array();
    public $Where = array();
    public $versione = '';
    public $visLivello = '3';
    public $scegliVersione = '0'; /* 1 o 0 */

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->Titolario = App::$utente->getKey($this->nameForm . '_Titolario');
        $this->Where = App::$utente->getKey($this->nameForm . '_Where');
        $this->versione = App::$utente->getKey($this->nameForm . '_versione');
        $this->visLivello = App::$utente->getKey($this->nameForm . '_visLivello');
        $this->scegliVersione = App::$utente->getKey($this->nameForm . '_scegliVersione');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_Titolario', $this->Titolario);
            App::$utente->setKey($this->nameForm . '_Where', $this->Where);
            App::$utente->setKey($this->nameForm . '_versione', $this->versione);
            App::$utente->setKey($this->nameForm . '_visLivello', $this->visLivello);
            App::$utente->setKey($this->nameForm . '_scegliVersione', $this->scegliVersione);
        }
    }

    function getVersione() {
        return $this->versione;
    }

    function setVersione($versione) {
        $this->versione = $versione;
    }

    public function getWhere() {
        return $this->Where;
    }

    public function setWhere($Where) {
        $this->Where = $Where;
    }

    public function getVisLivello() {
        return $this->visLivello;
    }

    public function setVisLivello($visLivello) {
        $this->visLivello = $visLivello;
    }

    public function getScegliVersione() {
        return $this->scegliVersione;
    }

    public function setScegliVersione($scegliVersione) {
        $this->scegliVersione = $scegliVersione;
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Elenca();
                Out::hide($this->nameForm . '_divSceltaVersione');
                if ($this->scegliVersione === '1') {
                    $this->CreaCombo();
                    Out::show($this->nameForm . '_divSceltaVersione');
                }
                break;

            case 'addGridRow':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridTitolario:
                        $this->CaricaTitolario();
                        TableView::clearGrid($this->gridTitolario);
                        $gridScheda = new TableView($this->gridTitolario, array('arrayTable' => $this->Titolario, 'rowIndex' => 'idx'));
                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPage('json');
                        break;
                }
                break;

            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridTitolario:
                        Out::closeDialog($this->nameForm);
                        $rowid = $_POST['rowid'];
                        $model = $this->returnModel;
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['retid'] = $this->returnID;
                        $_POST[$this->returnKey] = $rowid;
                        $_POST['rowData'] = $this->Titolario[$rowid];
                        $model();
                        //$this->close = true;
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CambiaVersione':
                        $versione = $_POST[$this->nameForm . '_CambiaVersione'];
                        $this->versione = $versione;
                        $this->Elenca();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_Titolario');
        App::$utente->removeKey($this->nameForm . '_Where');
        App::$utente->removeKey($this->nameForm . '_versione');
        App::$utente->removeKey($this->nameForm . '_visLivello');
        App::$utente->removeKey($this->nameForm . '_scegliVersione');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function Elenca() {
        TableView::enableEvents($this->gridTitolario);
        TableView::reload($this->gridTitolario);
    }

    public function CaricaTitolario() {
        if ($this->versione === '' || $this->versione === null) {
            $this->versione = $this->proLib->GetTitolarioCorrente();
        }
        $proLibTitolario = new proLibTitolario();
        /* Carico descrizione Versione */
        $Versione_rec = $proLibTitolario->GetVersione($this->versione, 'codice');
        $DescTitolario = "<span class=\"ui-icon ui-icon-info\" style = \"float:left; display:inline-block; margin-right:5px; margin-left:5px; \"></span> ";
        $DescTitolario.= 'Titolario valido dal: <b>' . date("d/m/Y", strtotime($Versione_rec['DATAINIZ'])) . '</b>';
        Out::html($this->nameForm . '_divDescTitolario', $DescTitolario);

        $this->Titolario = array();
        $filter = $_POST['_search'] == true ? $_POST['DESCRIZIONE'] : false;
        $this->Titolario = $proLibTitolario->GetTreeTitolario($this->versione, $filter, '', $this->Where, true, $this->visLivello);
    }

    public function CreaCombo() {
        $proLibTitolario = new proLibTitolario();
        Out::html($this->nameForm . '_CambiaVersione', '');
        $Versioni_tab = $proLibTitolario->GetVersioni();
        foreach ($Versioni_tab as $Versioni_rec) {
            Out::select($this->nameForm . '_CambiaVersione', 1, $Versioni_rec['VERSIONE_T'], "0", $Versioni_rec['VERSIONE_T'] . ' - ' . $Versioni_rec['DESCRI_B']);
        }
        if ($this->versione === '' || $this->versione === null) {
            $this->versione = $this->proLib->GetTitolarioCorrente();
        }
        Out::valore($this->nameForm . '_CambiaVersione', $this->versione);
    }

}

?>
