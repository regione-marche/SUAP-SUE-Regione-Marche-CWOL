<?php

/**
 *
 * GESTIONE Soggetti
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    26.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praSyncDettaglio() {
    $praSyncDettaglio = new praSyncDettaglio();
    $praSyncDettaglio->parseEvent();
    return;
}

class praSyncDettaglio extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praSyncDettaglio";
    public $divDettaglio = "praSyncDettaglio_divDettaglio";
    public $praLib;
    public $returnModel;
    public $returnEvent;
    public $retSimple = array();

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->retSimple = App::$utente->getKey($this->nameForm . '_retSimple');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_retSimple', $this->retSimple);
        }
    }

    public function setSimpleRet($retSimple) {
        $this->retSimple = $retSimple;
    }

    public function getSimpleRet() {
        return $this->retSimple;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::html($this->nameForm . "_divInfo", $this->retSimple['htmlInfo']);
                Out::html($this->nameForm . "_divControlli", $this->retSimple['htmlControlli']);
                Out::html($this->nameForm . "_divMsg", $this->retSimple['htmlErr']);
                break;
            case 'onClick':
                if (strpos($_POST['id'], "VediPassi_") !== false) {
                    $passi = $this->retSimple['retValue']['arrayTesti']["Itepas_tab_ctrIitewrd"][substr($_POST['id'], 24)];
                    if ($passi) {
                        $table = '<table id="tableItewrd">';
                        $table .= "<tr>";
                        $table .= '<th>Procedimento</th>';
                        $table .= '<th>Passo</th>';
                        $table .= "</tr>";
                        $table .= "<tbody>";
                        foreach ($passi as $Itepas_rec) {
                            $table .= "<tr>";
                            $table .= "<td>";
                            $Anapra_rec = $this->praLib->GetAnapra($Itepas_rec['ITECOD']);
                            $table .= $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];
                            $table .= "</td>";
                            $table .= "<td>";
                            $table .= $Itepas_rec['ITESEQ'] . " - " . $Itepas_rec['ITEDES'];
                            $table .= "</td>";
                            $table .= "</tr>";
                        }
                        $table .= '</tbody>';
                        $table .= '</table>';
                        Out::msgInfo("Elenco Passi", "<br>$table");
                        Out::codice('tableToGrid("#tableItewrd", {});');
                    }
                    break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_retSimple');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['model'] = $this->returnModel;
        $_POST['soggetto'] = $this->soggetto;
        $_POST['rowid'] = $this->rowid;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

}

?>