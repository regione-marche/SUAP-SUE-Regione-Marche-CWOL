<?php

/**
 *  Gestione Anagrafica Parametri
 *
 *
 * @category
 * @package    /apps/Sviluppo
 * @author     Carlo Iesari
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    29.07.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Sviluppo/devLib.class.php';

function devAnaParametriIni() {
    $devAnaParametriIni = new devAnaParametriIni();
    $devAnaParametriIni->parseEvent();
    return;
}

class devAnaParametriIni extends itaModel {

    public $nameForm = "devAnaParametriIni";
    public $gridClassi = "devAnaParametriIni_gridClassi";
    public $gridParametri = "devAnaParametriIni_gridParametri";
    public $ITALSOFT_DB;
    public $classi = array();
    public $parametri = array();
    public $ita_config_rowid;
    public $workDate;
    public $devLib;

    function __construct() {
        parent::__construct();
        $this->devLib = new devLib();
        $this->ITALSOFT_DB = $this->devLib->getITALSOFTDB();
        $this->classi = App::$utente->getKey($this->nameForm . '_classi');
        $this->parametri = App::$utente->getKey($this->nameForm . '_parametri');
        $this->ita_config_rowid = App::$utente->getKey($this->nameForm . '_ita_config_rowid');
        $data = App::$utente->getKey('DataLavoro');
        if ($data != '') {
            $this->workDate = $data;
        } else {
            $this->workDate = date('Ymd');
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_classi', $this->classi);
            App::$utente->setKey($this->nameForm . '_parametri', $this->parametri);
            App::$utente->setKey($this->nameForm . '_ita_config_rowid', $this->ita_config_rowid);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm('devAnaParametri', '', true, 'desktopBody', '', 'auto', '', $this->nameForm);
                TableView::disableEvents($this->gridClassi);
                TableView::clearGrid($this->gridClassi);
                TableView::disableEvents($this->gridParametri);
                TableView::clearGrid($this->gridParametri);
                Out::block($this->nameForm . '_divPar', '#000000', '0.08');
                Out::unBlock($this->nameForm . '_divCla');
                $this->caricaClassi();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        Out::unBlock($this->nameForm . '_divPar');
                        Out::block($this->nameForm . '_divCla', '#000000', '0.08');
                        $this->caricaParametri($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        unlink(ITA_BASE_PATH . "/apps/Ambiente/resources/{$this->classi[$_POST['rowid']]['CLASSE']}.ini");
                        $this->caricaClassi();
                        break;
                    case $this->gridParametri:
                        $parametri = unserialize($this->classi[$this->ita_config_rowid]['PARAMETRI']);
                        unset($parametri[$_POST['rowid']]);
                        $this->classi[$this->ita_config_rowid]['PARAMETRI'] = serialize($parametri);
                        $this->classi[$this->ita_config_rowid]['DATAMOD'] = $this->workDate;
                        $this->devLib->setIta_config_ini($this->classi[$this->ita_config_rowid]);
                        $this->caricaParametri($this->ita_config_rowid);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        Out::unBlock($this->nameForm . '_divPar');
                        Out::unBlock($this->nameForm . '_divCla');

                        Out::msgInput('Aggiunta Classe', array(
                            'label' => array(
                                'value' => 'Classe',
                                'style' => 'display: block;'
                            ), 'id' => $this->nameForm . '_classe',
                            'name' => $this->nameForm . '_classe',
                            'type' => 'text'
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaClasse',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm . "_workSpace");
                        break;
                    case $this->gridParametri:
                        Out::unBlock($this->nameForm . '_divPar');
                        Out::unBlock($this->nameForm . '_divCla');

                        Out::msgInput('Aggiunta Parametro', array(
                            'label' => array(
                                'value' => 'Chiave',
                                'style' => 'display: block;'
                            ), 'id' => $this->nameForm . '_chiave',
                            'name' => $this->nameForm . '_chiave',
                            'type' => 'text'
                                ), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaParametro',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm . "_workSpace");
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        if ($_POST['cellname'] == 'CLASSE') {
                            $old_file = $this->classi[$_POST['rowid']]['CLASSE'];
                        }

                        $this->classi[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $this->classi[$_POST['rowid']]['DATAMOD'] = $this->workDate;
                        $this->devLib->setIta_config_ini($this->classi[$_POST['rowid']]);

                        if ($_POST['cellname'] == 'CLASSE') {
                            unlink(ITA_BASE_PATH . "/apps/Ambiente/resources/$old_file.ini");
                        }

                        $this->caricaClassi();
                        break;
                    case $this->gridParametri:
                        $parametri = unserialize($this->classi[$this->ita_config_rowid]['PARAMETRI']);
                        $parametri[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $this->classi[$this->ita_config_rowid]['PARAMETRI'] = serialize($parametri);
                        $this->classi[$this->ita_config_rowid]['DATAMOD'] = $this->workDate;
                        $this->devLib->setIta_config_ini($this->classi[$this->ita_config_rowid]);
                        $this->caricaParametri($this->ita_config_rowid);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        Out::block($this->nameForm . '_divPar', '#000000', '0.08');
                        Out::unBlock($this->nameForm . '_divCla');
                        $this->ita_config_rowid = '';
                        $this->parametri = '';
                        TableView::disableEvents($this->gridParametri);
                        TableView::clearGrid($this->gridParametri);
                        $this->caricaClassi();
                        break;

                    case $this->nameForm . '_ConfermaClasse':
                        Out::block($this->nameForm . '_divPar', '#000000', '0.08');
                        Out::unBlock($this->nameForm . '_divCla');

                        if (!$_POST[$this->nameForm . '_classe']) {
                            break;
                        }

                        $this->devLib->setIta_config_ini(array(
                            'CLASSE' => $_POST[$this->nameForm . '_classe'],
                            'DATACREA' => $this->workDate
                        ));

                        $this->caricaClassi();
                        break;

                    case $this->nameForm . '_ConfermaParametro':
                        Out::block($this->nameForm . '_divCla', '#000000', '0.08');
                        Out::unBlock($this->nameForm . '_divPar');

                        if (!$_POST[$this->nameForm . '_chiave']) {
                            break;
                        }

                        $parametri = unserialize($this->classi[$this->ita_config_rowid]['PARAMETRI']);
                        $parametri[] = array('CHIAVE' => $_POST[$this->nameForm . '_chiave']);
                        $this->classi[$this->ita_config_rowid]['PARAMETRI'] = serialize($parametri);
                        $this->classi[$this->ita_config_rowid]['DATAMOD'] = $this->workDate;
                        $this->devLib->setIta_config_ini($this->classi[$this->ita_config_rowid]);

                        $this->caricaParametri($this->ita_config_rowid);
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_classi');
        App::$utente->removeKey($this->nameForm . '_parametri');
        App::$utente->removeKey($this->nameForm . '_ita_config_rowid');
        Out::closeDialog($this->nameForm);
    }

    function caricaClassi() {
        $this->ita_config_rowid = '';
        $this->classi = $this->devLib->getIta_config_ini('', 'tutti');
        $this->CaricaGriglia($this->gridClassi, $this->classi);
    }

    function caricaParametri($codice) {
        $this->ita_config_rowid = $codice;
        $this->parametri = unserialize($this->classi[$codice]['PARAMETRI']);
        $this->CaricaGriglia($this->gridParametri, $this->parametri);
    }

    function caricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    //                Out::msgInput(
//                        'Scegli gli elementi da duplicare',
//                        array(
//                            array(
//                                'label' => array('value'=>'Allegati','style'=>'width:100px;display:block;float: left;padding: 0 5px 0 0;text-align: right;'),
//                                'id' => $this->nameForm . '_duplicaAllegati',
//                                'name' => $this->nameForm . '_duplicaAllegati',
//                                'type'  => 'text',
//                                'style' => 'margin:2px;'
//                            ),
//                            array(
//                                'label' => array('value'=>'XXXXXXXXXX','style'=>'width:100px;display:block;float: left;padding: 0 5px 0 0;text-align: right;'),
//                                'id' => $this->nameForm . '_duplicaDati',
//                                'name' => $this->nameForm . '_duplicaDati',
//                                'type'  => 'text',
//                                'style' => 'margin:2px;'
//                            ),
//                        ),
//                        array(
//                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaInput', 'model' => $this->nameForm, 'shortCut' => "f5")
//                        ),
//                        $this->nameForm."_divGestione"
//                );
}

?>
