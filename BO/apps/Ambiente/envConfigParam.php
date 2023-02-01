<?php

/**
 *  Gestione Parametri
 *
 *
 * @category
 * @package    /apps/Ambiente
 * @author     Marco Camilletti
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    27.03.2012
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . "/apps/Generator/genLib.class.php";

function envConfigParam() {
    $envConfigParam = new envConfigParam();
    $envConfigParam->parseEvent();
    return;
}

class envConfigParam extends itaModel {

    public $nameForm = "envConfigParam";
    public $gridConfig = "envConfigParam_gridConfig";
    public $gridConfigMulti = "envConfigParam_gridConfigMulti";
    public $ITALSOFT_DB;
    public $ITALWEB;
    public $classi = array();
    public $istanze = array();
    public $currClasse;
    public $currIstanza;
    public $devLib;
    public $envLib;
    public $genLib;
    private $path;

    function __construct() {
        parent::__construct();
        $this->devLib = new devLib();
        $this->genLib = new genLib();
        $this->envLib = new envLib();
        $this->ITALSOFT_DB = $this->devLib->getITALSOFTDB();
        $this->ITALWEB = $this->devLib->getITALWEB();
        $this->classi = App::$utente->getKey($this->nameForm . '_classi');
        $this->istanze = App::$utente->getKey($this->nameForm . '_istanze');
        $this->currIstanza = App::$utente->getKey($this->nameForm . '_currIstanza');
        $this->currClasse = App::$utente->getKey($this->nameForm . '_currClasse');
        $this->path = ITA_BASE_PATH . '/apps/Ambiente/resources';
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_classi', $this->classi);
            App::$utente->setKey($this->nameForm . '_istanze', $this->istanze);
            App::$utente->setKey($this->nameForm . '_currIstanza', $this->currIstanza);
            App::$utente->setKey($this->nameForm . '_currClasse', $this->currClasse);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm . '_divDettaglio');
                Out::hide($this->nameForm . '_Torna');
                TableView::disableEvents($this->gridConfig);
                TableView::clearGrid($this->gridConfig);
                $this->caricaClassi();
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridConfig:
                        $this->classi = $this->devLib->getIta_config_ini('', 'tutti');
                        if ($_POST['_search'] == true) {
                            foreach ($this->classi as $k => $classe) {
                                if ($_POST['CLASSE'] && !(strpos(strtoupper($classe['CLASSE']), strtoupper($_POST['CLASSE'])) > -1)) {
                                    unset($this->classi[$k]);
                                }
                                if ($_POST['DESCRIZIONE'] && !(strpos(strtoupper($classe['DESCRIZIONE']), strtoupper($_POST['DESCRIZIONE'])) > -1)) {
                                    unset($this->classi[$k]);
                                }
                            }
                        }
                        $this->CaricaGriglia($this->gridConfig, $this->classi);
                        break;
                    case $this->gridConfigMulti:
                        $this->caricaIstanze();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridConfigMulti:
                        $classe = $this->classi[$_POST['rowid']]['CLASSE'];
                        $msg = "Sei sicuro di voler creare un Istanza per la Classe " . '<br>' . $this->currClasse['CLASSE'];
                        Out::msgQuestion("ATTENZIONE", $msg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_AggiungiIstanza', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridConfigMulti:
                        $this->currIstanza = $this->istanze[$_POST['rowid']]['CLASSE'];
                        $msg = "Sei sicuro di voler Eliminare " . $this->currIstanza;
                        Out::msgQuestion("ATTENZIONE", $msg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_Annulla', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_CancellaIstanza', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridConfigMulti:
                        $this->currIstanza = $this->istanze[$_POST['rowid']]['CLASSE'];
                        $this->caricaDialog($istanza);
                        break;
                    case $this->gridConfig:
                        $this->currClasse = $this->classi[$_POST['rowid']];
                        $this->currIstanza = $this->currClasse['CLASSE'];
                        if ($this->currClasse['MULTIISTANZA'] == 1) {   // verifica flag MULTIISTANZA
                            Out::show($this->nameForm . '_Torna');
                            Out::hide($this->nameForm . '_Semafori');
                            Out::hide($this->nameForm . '_ClassEventi');
                            Out::show($this->nameForm . '_divDettaglio');
                            Out::setGridCaption($this->nameForm . '_gridConfigMulti', 'Configurazione Parametri Multi Istanza - ' . $this->currClasse['DESCRIZIONE']);
                            Out::hide($this->nameForm . '_divGestione');
                            $this->caricaIstanze();
                            break;
                        }
                        $this->caricaDialog($istanza);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaAggiorna':
                        $this->Aggiorna();
                        if ($this->currClasse['MULTIISTANZA'] == 1) {
                            $this->caricaIstanze();
                            $this->currIstanza = null;
                        } else {
                            $this->currClasse = null;
                            $this->currIstanza = null;
                        }
                        break;

                    case $this->nameForm . '_CancellaIstanza':
                        if ($this->currIstanza == $this->currClasse['CLASSE']) {
                            Out::msgStop("ATTENZIONE", "NON E' POSSIBILE CANCELLARE LA CLASSE BASE");
                            break;
                        }
                        $this->CancellaIstanza($this->currIstanza);
                        $this->caricaIstanze();
                        break;

                    case $this->nameForm . '_Torna':
                        $this->currClasse = null;
                        $this->currIstanza = null;
                        Out::hide($this->nameForm . '_Torna');
                        Out::hide($this->nameForm . '_divDettaglio');
                        Out::show($this->nameForm . '_divGestione');
                        Out::show($this->nameForm . '_Semafori');
                        Out::show($this->nameForm . '_ClassEventi');
                        break;

                    case $this->nameForm . '_AggiungiIstanza':
                        $progressivo_name = $this->newNameIstanza($this->currClasse['CLASSE']);
                        if (!$progressivo_name) {
                            Out::msgStop("ATTENZIONE", "NON E' POSSIBILE GENERARE NOME PROGRESSIVO INSTANZA");
                            break;
                        }
                        $chiavi_rec = $this->currClasse['PARAMETRI'];
                        if (!$chiavi_rec) {
                            Out::msgStop("ATTENZIONE", "NON E' POSSIBILE IMPORTARE I PARAMETRI DELL' INSTANZA");
                            break;
                        }
                        $this->currIstanza = $progressivo_name;
                        $this->caricaDialog();
                        break;

                    case $this->nameForm . '_Semafori':
                        $model = 'envSemafori';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case $this->nameForm . '_ClassEventi':
                        $model = 'envClassEventi';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
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
        App::$utente->removeKey($this->nameForm . '_istanze');
        App::$utente->removeKey($this->nameForm . '_currClasse');
        App::$utente->removeKey($this->nameForm . '_currIstanza');
        Out::closeDialog($this->nameForm);
    }

    function caricaClassi() {
        $this->classi = $this->devLib->getIta_config_ini('', 'tutti');
        $this->CaricaGriglia($this->gridConfig, $this->classi);
    }

    function newNameIstanza($class) {
        /*
         * Prende ultimo nome della classe nell'array istanze
         * Ritorna Nome Progressivo 
         */
        $this->caricaIstanze();
       $ultimo= array_pop($this->istanze);
       $fine = explode("_", $ultimo['CLASSE']);
        $Tot = ++$fine[1];
        $pippo = str_pad($Tot, 3, 0, STR_PAD_LEFT);
        $progressivo_name = $class . '_' . $pippo;
        return $progressivo_name;
    }

    function caricaIstanze() {
        $Desc = $this->currClasse['DESCRIZIONE'];
        $this->istanze = $this->envLib->getIstanze($this->currClasse['CLASSE']);
        foreach ($this->istanze as $key => $istanza) {
            list($skip, $inc) = explode('_', $istanza['CLASSE']);
            $this->istanze[$key]['DESCRIZIONE_CODICE_CLASSE'] = $Desc . ' ' . $inc;
        }
        $this->CaricaGriglia($this->gridConfigMulti, $this->istanze);
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

    public function Aggiorna() {
        $parametri = unserialize($this->currClasse['PARAMETRI']);
        foreach ($parametri as $key => $value) {
            $envConfig_rec = $this->devLib->getEnv_config($this->currIstanza, 'codice', $value['CHIAVE'], false);
            if ($envConfig_rec) {
                $envConfig_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $value['CHIAVE']];
                $this->updateRecord($this->ITALWEB, 'ENV_CONFIG', $envConfig_rec, '', 'ROWID', false);
            } else {
                $envConfig_rec = array();
                $envConfig_rec['CHIAVE'] = $value['CHIAVE'];
                $envConfig_rec['CLASSE'] = $this->currIstanza;
                $envConfig_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $value['CHIAVE']];
                $this->insertRecord($this->ITALWEB, 'ENV_CONFIG', $envConfig_rec, '', 'ROWID', false);
            }
        }
        Out::closeCurrentDialog();
    }

    public function CancellaIstanza($istanza) {
        /*
         * Dato un nome Cancella l'istanza
         *  Cancella tutte le sue chiavi
         */
        $rowid_rec = ItaDB::DBSQLSelect($this->ITALWEB, "SELECT ROWID FROM ENV_CONFIG WHERE CLASSE = '" . $istanza . "'");
        if (!$rowid_rec) {
            Out::msgStop("ATTENZIONE", "NESSUN RISCONTRO TROVATO");
            return;
        }
        foreach ($rowid_rec as $rowid) {
            $this->deleteRecord($this->ITALWEB, 'ENV_CONFIG', $rowid['ROWID']);
        }
        $this->currIstanza = null;
    }

    private function caricaDialog($name = '') {
        /*
         * Dato il nome della classe o istanza Crea Dialog con i parametri contenuti nel file ini 
         */
        if (!$name) {
            $name = $this->currIstanza;
        }
        $parametri = unserialize($this->currClasse['PARAMETRI']);
        $valori = array();
        if ($this->currClasse['MULTIISTANZA'] != 1) {
            unset($parametri[0]);
        }
        foreach ($parametri as $key => $value) {
            $envConfig_rec = $this->devLib->getEnv_config($name, 'codice', $value['CHIAVE'], false);
            $input_type = 'text';
            if(isset($value['INPUT_TYPE'])){
                $input_type = $value['INPUT_TYPE'];
            }

            
            if ($envConfig_rec) {
                $valoreChiave = $envConfig_rec['CONFIG'];
            } else {
                $valoreChiave = $value['DEFAULT'];
            }
            $valori[] = array(
                'label' => array(
                    'value' => $value['DESC'],
                    'style' => 'width:250px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                ),
                'id' => $this->nameForm . '_' . $value['CHIAVE'],
                'name' => $this->nameForm . '_' . $value['CHIAVE'],
                'type' => $input_type,
                'style' => 'margin:2px;width:500px;',
                'value' => $valoreChiave,
                'class' => $value['REQUIRED'] ? 'required' : '',
            );
        }
        Out::msgInput(
                'Configurazione Parametri - ' . $name, $valori
                , array(
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAggiorna',
                'model' => $this->nameForm, 'class' => "ita-button-validate", 'shortCut' => "f5")
                ), $this->nameForm, 'auto', '830px'
        );
    }

}

?>
