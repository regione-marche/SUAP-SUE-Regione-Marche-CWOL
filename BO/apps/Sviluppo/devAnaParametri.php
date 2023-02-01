<?php

/**
 *  Gestione Anagrafica Parametri
 *
 *
 * @category
 * @package    /apps/Sviluppo
 * @author     Marco Camilletti
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    26.03.2012
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Sviluppo/devLib.class.php';

function devAnaParametri() {
    $devAnaParametri = new devAnaParametri();
    $devAnaParametri->parseEvent();
    return;
}

class devAnaParametri extends itaModel {

    public $nameForm = "devAnaParametri";
    public $gridClassi = "devAnaParametri_gridClassi";
    public $gridParametri = "devAnaParametri_gridParametri";
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
                        $this->caricaParametri($this->classi[$_POST['rowid']]['ROWID']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        $this->deleteRecord($this->ITALSOFT_DB, 'ita_config', $this->classi[$_POST['rowid']]['ROWID'], '', 'ROWID', false);
                        $this->caricaClassi();
                        break;
                    case $this->gridParametri:
                        $config_rec = $this->devLib->getIta_config($this->ita_config_rowid, 'rowid');
                        $parametri = unserialize($config_rec['PARAMETRI']);
                        unset($parametri[$_POST['rowid']]);
                        $config_rec['PARAMETRI'] = serialize($parametri);
                        $config_rec['DATAMOD'] = $this->workDate;
                        $this->updateRecord($this->ITALSOFT_DB, 'ita_config', $config_rec, '', 'ROWID', false);
                        $this->caricaParametri($this->ita_config_rowid);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridClassi:
                        $classi_rec = array('DATACREA' => $this->workDate);
                        $this->insertRecord($this->ITALSOFT_DB, 'ita_config', $classi_rec, '', 'ROWID', false);
                        $this->caricaClassi();
                        break;
                    case $this->gridParametri:
                        $config_rec = $this->devLib->getIta_config($this->ita_config_rowid, 'rowid');
                        $parametri = unserialize($config_rec['PARAMETRI']);
                        $parametri[] = array('CHIAVE' => '', 'DESC' => '', 'DEFAULT' => '');
                        $config_rec['PARAMETRI'] = serialize($parametri);
                        $config_rec['DATAMOD'] = $this->workDate;
                        $this->updateRecord($this->ITALSOFT_DB, 'ita_config', $config_rec, '', 'ROWID', false);
                        $this->caricaParametri($this->ita_config_rowid);
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridClassi:
//                        switch ($_POST['cellname']) {
//                            case 'CLASSE':
//                                break;
//                            case 'DESCRIZIONE':
//                                break;
//                        }
                        $this->classi[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $this->classi[$_POST['rowid']]['DATAMOD'] = $this->workDate;
                        $this->updateRecord($this->ITALSOFT_DB, 'ita_config', $this->classi[$_POST['rowid']], '', 'ROWID', false);
                        $this->caricaClassi();
                        break;
                    case $this->gridParametri:
                        $config_rec = $this->devLib->getIta_config($this->ita_config_rowid, 'rowid');
                        $parametri = unserialize($config_rec['PARAMETRI']);
                        $parametri[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $config_rec['PARAMETRI'] = serialize($parametri);
                        $config_rec['DATAMOD'] = $this->workDate;
                        $this->updateRecord($this->ITALSOFT_DB, 'ita_config', $config_rec, '', 'ROWID', false);
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

                    case $this->nameForm . '_Compila':
                        $this->compilaParametri();
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
        $this->classi = $this->devLib->getIta_config('', 'tutti');
        $this->CaricaGriglia($this->gridClassi, $this->classi);
    }

    function caricaParametri($codice) {
        $this->ita_config_rowid = $codice;
        $config_rec = $this->devLib->getIta_config($codice, 'rowid');
        $this->parametri = unserialize($config_rec['PARAMETRI']);
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

    private function compilaParametri() {
        $ita_config_tab = $this->devLib->getIta_config('', 'tutti');

        $ini_files = glob(ITA_BASE_PATH . "/apps/Ambiente/resources/*.ini");

        foreach ($ita_config_tab as $ita_config_rec) {
            $ini = array(
                'Config' => array(
                    'DESCRIZIONE' => $ita_config_rec['DESCRIZIONE'],
                    'DATACREA' => $ita_config_rec['DATACREA'],
                    'DATAMOD' => $ita_config_rec['DATAMOD'],
                    'DATAANN' => $ita_config_rec['DATAANN']
                )
            );

            $parametri_tab = unserialize($ita_config_rec['PARAMETRI']);

            foreach ($parametri_tab as $parametri_rec) {
                $ini[$parametri_rec['CHIAVE']] = array(
                    'DESC' => $parametri_rec['DESC'],
                    'DEFAULT' => $parametri_rec['DEFAULT']
                );
            }

            foreach ($ini_files as $ini_key => $ini_file) {
                if ($ita_config_rec['CLASSE'] === basename($ini_file, '.ini')) {
                    unset($ini_files[$ini_key]);
                }
            }

            itaLib::writeIniFile(ITA_BASE_PATH . "/apps/Ambiente/resources/{$ita_config_rec['CLASSE']}.ini", $ini);

            $c++;
        }

        if (count($ini_files)) {
            Out::msgStop("Attenzione", "\nSono presenti più file .ini sotto \"/Ambiente/resources\" "
                    . "rispetto al numero di Classi.\nVerificare i seguenti file:\n * " . implode("\n * ", $ini_files));
        }

        Out::msgInfo("Compilazione", "Compilazione avvenuta con successo");
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
