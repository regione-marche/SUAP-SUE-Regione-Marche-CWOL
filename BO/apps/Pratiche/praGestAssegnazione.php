<?php

/**
 *
 * Gestione Dati Assegnazione
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    09.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function praGestAssegnazione() {
    $praGestAssegnazione = new praGestAssegnazione();
    $praGestAssegnazione->parseEvent();
    return;
}

class praGestAssegnazione extends itaModel {

    public $nameForm = "praGestAssegnazione";
    public $rowidPasso;
    public $praLib;
    public $returnModel;
    public $returnEvent;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->rowidPasso = App::$utente->getKey($this->nameForm . '_rowidPasso');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidPasso', $this->rowidPasso);
        }
    }

    public function getRowidPasso() {
        return $this->rowidPasso;
    }

    public function setRowidPasso($rowidPasso) {
        $this->rowidPasso = $rowidPasso;
    }

    public function setReturnModel($returnModel) {
        $this->returnModel = $returnModel;
    }

    public function setReturnEvent($returnEvent) {
        $this->returnEvent = $returnEvent;
    }

    public function parseEvent() {
        // OVERRIDE DEGLI EVENTI
        switch ($_POST['event']) {
            case 'openform':
                $propas_rec = $this->praLib->GetPropas($this->rowidPasso, "rowid");
                $profilo = proSoggetto::getProfileFromIdUtente();
                $funzioniAssegnazione = praFunzionePassi::getFunzioniAssegnazione($propas_rec['PRONUM'], $propas_rec['ROWID'], $profilo);
                if (!$funzioniAssegnazione['RIAPRI']) {
                    Out::hide($this->nameForm . "_btnRiapri");
                }
                Out::valori($propas_rec, $this->nameForm . '_PROPAS');
                $ananom_rec = $this->praLib->GetAnanom($propas_rec['PRORPA']);
                Out::valore($this->nameForm . '_RESPONSABILE', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PRORPA]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PRORPA'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $ananom_rec = $this->praLib->getAnanom($codice);
                            Out::valore($this->nameForm . '_PROPAS[PRORPA]', $ananom_rec['NOMRES']);
                            Out::valore($this->nameForm . '_RESPONSABILE', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PROCLT'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $praclt_rec = $this->praLib->getPraclt($codice);
                            Out::valore($this->nameForm . '_PROPAS[PROCLT]', $codice);
                            Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . "_Aggiorna":
                        $praclt_rec = $this->praLib->getPraclt($_POST[$this->nameForm . "_PROPAS"]['PROCLT']);
                        if (!$praclt_rec) {
                            Out::msgStop("", "Passo insesistente");
                            break;
                        }
                        if (!$praclt_rec['CLTOPE'] === '') {
                            Out::msgStop("", "Tipo Passo non valido");
                            break;
                        }

                        $propas_rec = $_POST[$this->nameForm . "_PROPAS"];
                        $propas_rec['PROOPE'] = $praclt_rec['CLTOPE'];
                        $update_Info = "Oggetto: Aggiornamento Passo " . $propas_rec['PRODPA'] . " Pratica n. " . $propas_rec['PRONUM'];
                        if (!$this->updateRecord($this->praLib->getPRAMDB(), 'PROPAS', $propas_rec, $update_Info)) {
                            Out::msgStop("Errore", "Errore in aggiornamento su PROPAS");
                            break;
                        }
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_btnRiapri':
                        $propas_rec = $this->praLib->GetPropas($this->rowidPasso, "rowid");
                        $model = 'praAssegnaPraticaSimple';
                        itaLib::openForm($model);
                        /* @var $modelObj praAssegnaPraticaSimple */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setDaPortlet(false);
                        $modelObj->setPratica($propas_rec['PRONUM']);
                        $modelObj->setRowidAppoggio($this->rowidPasso);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraAssegnaPratica');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        $modelObj->annullaInCarico();
                        break;
                    case $this->nameForm . '_PROPAS[PRORPA]_butt':
                        $propas_rec = $this->praLib->GetPropas($this->rowidPasso, "rowid");
                        praRic::praRicAnanom($this->praLib->getPRAMDB(), $this->nameForm, "Soggetto a cui assegnare la pratica: " . $propas_rec['PRONUM'], " WHERE NOMABILITAASS = 1 ", $this->nameForm . "_AssegnaPratica", false, null, "", true);
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]_butt':
                        $where = " WHERE CLTOPE<>''";
                        praRic::praRicPraclt($this->nameForm, "RICERCA Tipo Passo", '', $where);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case "returnUnires":
                $ananom_rec = $this->praLib->getAnanom($_POST['retKey'], "rowid");
                Out::valore($this->nameForm . '_PROPAS[PRORPA]', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_RESPONSABILE', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case "returnPraclt":
                $praclt_rec = $this->praLib->getPraclt($_POST['retKey'], "rowid");
                Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
                Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
                break;
            case "returnPraAssegnaPratica":
                $this->returnToParent();
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_rowidPasso');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        $this->close = true;
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $propas_rec = $this->praLib->GetPropas($this->rowidPasso, "rowid");
        $_POST = array();
        $_POST['gesnum'] = $propas_rec['PRONUM'];
        $objModel = itaModel::getInstance($this->returnModel);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();
        if ($close)
            $this->close();
    }

}

?>
