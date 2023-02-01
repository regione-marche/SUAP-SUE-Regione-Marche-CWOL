<?php

/**
 *
 * GESTIONE Note
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    06.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';

function proOpenProtDaNotifica() {
    $proOpenProtDaNotifica = new proOpenProtDaNotifica();
    $proOpenProtDaNotifica->parseEvent();
    return;
}

class proOpenProtDaNotifica extends itaModel {

    public $PROT_DB;
    public $nameForm = "proOpenProtDaNotifica";
    public $proLib;
    public $gridDestinatari = "proOpenProtDaNotifica_gridDestinatari";
    public $gridNote = "proOpenProtDaNotifica_gridNote";
    public $OpenRowid;
    public $OpenMode;
    public $TipoOpen;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->OpenRowid = App::$utente->getKey($this->nameForm . '_OpenRowid');
            $this->OpenMode = App::$utente->getKey($this->nameForm . '_OpenMode');
            $this->TipoOpen = App::$utente->getKey($this->nameForm . '_TipoOpen');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_OpenRowid', $this->OpenRowid);
            App::$utente->setKey($this->nameForm . '_OpenMode', $this->OpenMode);
            App::$utente->setKey($this->nameForm . '_TipoOpen', $this->TipoOpen);
        }
    }

    public function setModelParam($params) {
        $params = unserialize($params);
        foreach ($params as $func => $args) {
            call_user_func_array(array($this->nameForm, $func), $args);
        }
    }

    public function setOpenMode($openMode) {
        $this->OpenMode = $openMode;
    }

    public function setOpenRowid($openRowid) {
        $this->OpenRowid = $openRowid;
    }

    public function setTipoOpen($TipoOpen) {
        $this->TipoOpen = $TipoOpen;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
            case 'OpenProtocollo':
                $anapro_rec = $this->proLib->GetAnapro($this->OpenRowid, 'rowid');
                $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                if (!$anaproctr_rec) {
                    Out::msgStop("Accesso al protocollo", "Protocollo non accessibile.");
                    $this->close();
                    break;
                }
                $arcite_rec = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR']);
                if ($arcite_rec) {
                    $rowId = $arcite_rec['ROWID'];
                    $model = 'proGestIter';
                    $_POST = array();
                    $_POST['event'] = 'openform';
                    $_POST['tipoOpen'] = 'visualizzazione';
                    $_POST['rowidIter'] = $rowId;
                    $_POST['prmsEditNote'] = true;
                    itaLib::openForm($model);
                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                    $model();
                }
                $this->close();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'addGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_OpenRowid');
        App::$utente->removeKey($this->nameForm . '_OpenMode');
        App::$utente->removeKey($this->nameForm . '_TipoOpen');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

}

?>