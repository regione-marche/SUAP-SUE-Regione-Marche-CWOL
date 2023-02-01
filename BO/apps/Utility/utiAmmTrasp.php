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
include_once ITA_BASE_PATH . '/apps/Bdap/bdaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Bdap/bdaAmtDBLib.class.php';

function utiAmmTrasp() {
    $utiAmmTrasp = new utiAmmTrasp();
    $utiAmmTrasp->parseEvent();
    return;
}

class utiAmmTrasp extends itaModel {

    public $bdaLib;
    public $nameForm = "utiAmmTrasp";
    public $buttonBar = "utiAmmTrasp_buttonBar";
    public $gridTitolario = "utiAmmTrasp_gridTitolario";
    private $Titolario = array();
    public $Where = array();
    public $returnEvent;
    public $returnModel;
    public $bdaLB;

    function __construct() {
        parent::__construct();
        $this->bdaLib = new bdaLib();
        $this->bdaLB = new bdaAmtDBLib();
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
                if ($_POST[$this->nameForm . "_returnModel"])
                    $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                if ($_POST[$this->nameForm . "_returnEvent"])
                    $this->returnEvent = $_POST[$this->nameForm . "_returnEvent"];
                $Permessi = $this->bdaLB->leggiParametri();
                if ($Permessi == 1) {
                    TableView::showCol($this->gridTitolario, 'PERMESSI');
                } else {
                    TableView::hideCol($this->gridTitolario, 'PERMESSI');
                }
                $this->Elenca();
                break;

            case 'addGridRow':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridTitolario:
                        $this->CaricaTitolario($_POST['VOCE']);
                        TableView::clearGrid($this->gridTitolario);
                        $gridScheda = new TableView($this->gridTitolario, array('arrayTable' => $this->Titolario, 'rowIndex' => 'ID'));
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
                        $Trasparente = $this->Titolario[$_POST['rowid']];
                        $_POST = array();
                        $_POST['event'] = $this->returnEvent;
                        $_POST['model'] = $this->returnModel;
                        $_POST['amt_cod'] = $Trasparente['CODICE'];
                        $_POST['amt_voce'] = $Trasparente['VOCE'];
                        $_POST['indice'] = $this->Indice;
//                        $phpURL = App::getConf('modelBackEnd.php');

                        $returnModel = $this->returnModel;
                        $returnModelOrig = $returnModel;
                        if (is_array($returnModel)) {
                            $returnModelOrig = $returnModel['nameFormOrig'];
                            $returnModel = $returnModel['nameForm'];
                        }
                        $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->parseEvent();
                        $this->close();
//                        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
//                        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
//                        $returnModel = $this->returnModel;
//                        $returnModel();
//                        $this->returnToParent();
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

    public function CaricaTitolario($Where) {
        $this->Titolario = array();
        $filter = $_POST['_search'] == true ? $_POST['DESCRIZIONE'] : false;
        $this->Titolario = $this->bdaLib->bdaRicAmmTrasp($Where);
        $Permessi = $this->bdaLB->leggiParametri();
        if ($Permessi == 1) {
            foreach ($this->Titolario as $key => $Reg) {
                $this->Titolario[$key]['PERMESSI'] = $this->bdaLB->ControllaPermessi($Reg['ID']);
                if ($this->Titolario[$key]['PERMESSI'] == '') {
                    unset($this->Titolario[$key]);
                }
            }
        }
    }

}

?>
