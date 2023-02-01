<?php

/**
 *
 * DOCUMENTI BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    Documenti
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function docOpenCMD() {
    $docOpenCMD = new docOpenCMD();
    $docOpenCMD->parseEvent();
    return;
}

class docOpenCMD extends itaModel {

    public $ITALWEB;
    public $docLib;
    public $devLib;
    public $utiEnte;
    public $nameForm = "docOpenCMD";
    public $divGes = "docOpenCMD_divGestione";
    public $gridComandi = "docOpenCMD_gridComandi";
    private $comandi = array();

    function __construct() {
        parent::__construct();
        $this->comandi = App::$utente->getKey($this->nameForm . '_comandi');
        // Apro il DB
        try {
            $this->docLib = new docLib();
            $this->devLib = new devLib();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB = $this->docLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_comandi', $this->comandi);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CaricaComandi();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridComandi:
                        Out::msgQuestion("Cancellazione", "Confermi di voler cancellare definitivamente il comando?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridComandi:
                        $this->comandi[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $this->CaricaTabellaComandi();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridComandi:
                        $this->AggiungiComando();
                        break;
                }
                break;

            case 'exportTableToExcel':
                break;
            case 'printTableToHTML':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        $this->CaricaComandi();
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $rowid = $_POST[$this->gridComandi]['gridParam']['selarrrow'];
                        $Comando = $this->comandi[$rowid];
                        // QUI CONTROLLO UTILIZZO.
                        if ($Comando['ROWID']) {
                            $delete_Info = " Cancellazione Comando: " . $Comando['CHIAVE'] . ' ' . $Comando['COMANDO'];
                            if (!$this->deleteRecord($this->ITALWEB, 'ENV_CONFIG', $Comando['ROWID'], $delete_Info, 'ROWID')) {
                                Out::msgStop("Attenzione", "Errore nella cancellazione del Comando.");
                                $this->CaricaTabellaComandi();
                                return false;
                            }
                        }
                        unset($this->comandi[$rowid]);
                        $this->CaricaTabellaComandi();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_comandi');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function CaricaComandi() {
        $this->comandi = $this->devLib->getEnv_config('OPENDOCCMD', 'codice', '', true);
        foreach ($this->comandi as $key => $comando) {
            $configUnser = unserialize($comando['CONFIG']);
            $this->comandi[$key]['COMANDO'] = $configUnser['COMANDO'];
            $this->comandi[$key]['DESCRIZIONE'] = $configUnser['DESCRIZIONE'];
        }
        $this->CaricaTabellaComandi();
    }

    public function CaricaTabellaComandi() {
        $this->caricaGriglia($this->gridComandi, $this->comandi);
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

    public function AggiungiComando() {
        $ComandiChiave = array();
        foreach ($this->comandi as $comando) {
            $ComandiChiave[] = substr($comando['CHIAVE'], 7);
        }
        $MadChiave = max($ComandiChiave);
        App::log($MadChiave);
        $chiave = 'CMDRUN_' . ($MadChiave + 1);

        $newcomando = array();
        $newcomando['ROWID'] = '0';
        $newcomando['CHIAVE'] = $chiave;
        $newcomando['COMANDO'] = '';
        $newcomando['DESCRIZIONE'] = '';

        $this->comandi[] = $newcomando;
        App::log($this->comandi);
        $this->CaricaTabellaComandi();
    }

    public function Aggiorna() {
        foreach ($this->comandi as $comando) {
            $env_config_rec = array();
            $CampoConfig = array();
            $CampoConfig['COMANDO'] = $comando['COMANDO'];
            $CampoConfig['DESCRIZIONE'] = $comando['DESCRIZIONE'];

            $env_config_rec['ROWID'] = $comando['ROWID'];
            $env_config_rec['CHIAVE'] = $comando['CHIAVE'];
            $env_config_rec['CLASSE'] = 'OPENDOCCMD';
            $env_config_rec['CONFIG'] = serialize($CampoConfig);

            if ($env_config_rec['ROWID'] != '0') {
                try {
                    ItaDB::DBUpdate($this->ITALWEB, 'ENV_CONFIG', 'ROWID', $env_config_rec);
                } catch (Exception $exc) {
                    Out::msgStop('Attenzione', "Inserimento Comando Fallito." . $exc->getMessage());
                    return false;
                }
            } else {
                try {
                    ItaDB::DBInsert($this->ITALWEB, 'ENV_CONFIG', 'ROWID', $env_config_rec);
                } catch (Exception $exc) {
                    Out::msgStop('Attenzione', "Inserimento Comando Fallito." . $exc->getMessage());
                    return false;
                }
            }
        }
        Out::msgBlock('', 3000, true, "Dati Aggiornati");
    }

}

?>