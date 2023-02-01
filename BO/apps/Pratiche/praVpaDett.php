<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praVpaDett() {
    $praVpaDett = new praVpaDett();
    $praVpaDett->parseEvent();
    return;
}

class praVpaDett extends itaModel {

    public $nameForm = 'praVpaDett';
    private $praLib;
    private $ITECOD;
    private $ITEKEY;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib;
        $this->ITECOD = App::$utente->getKey($this->nameForm . '_ITECOD');
        $this->ITEKEY = App::$utente->getKey($this->nameForm . '_ITEKEY');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ITECOD', $this->ITECOD);
            App::$utente->setKey($this->nameForm . '_ITEKEY', $this->ITEKEY);
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_ITECOD');
        App::$utente->removeKey($this->nameForm . '_ITEKEY');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function setITECOD($ITECOD) {
        $this->ITECOD = $ITECOD;
    }

    public function setITEKEY($ITEKEY) {
        $this->ITEKEY = $ITEKEY;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openDettaglio();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ITEVPADETT_SEQ_butt':
                        $where = " WHERE ITECOD = '{$this->ITECOD}' AND ITEKEY <> '{$this->ITEKEY}'";
                        praRic::praRicItepas($this->nameForm, 'ITEPAS', $where, '', '' , 'ASC');
                        break;

                    case $this->nameForm . '_ApriControllo':
                        $model = 'praCondizioni';
                        itaLib::openForm($model);
                        $praCondizioni = itaModel::getInstance($model);
                        $praCondizioni->setEvent('openform');
                        $praCondizioni->setReturnModel($this->nameForm);

                        if ($_POST[$this->nameForm . '_ITEVPADETT']['ITEEXPRVPA'] && unserialize($_POST[$this->nameForm . '_ITEVPADETT']['ITEEXPRVPA'])) {
                            $praCondizioni->setArrayEspressioni(unserialize($_POST[$this->nameForm . '_ITEVPADETT']['ITEEXPRVPA']));
                        }

                        $praCondizioni->setCodiceProcedimento($this->ITECOD);
                        $praCondizioni->setCodicePasso($this->ITEKEY);

                        $praCondizioni->parseEvent();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        if ($this->returnModel) {
                            $itevpadett_rec = $_POST[$this->nameForm . '_ITEVPADETT'];
                            $itevpadett_rec['ITECOD'] = $this->ITECOD;
                            $itevpadett_rec['ITEKEY'] = $this->ITEKEY;

                            $returnModelObj = itaModel::getInstance($this->returnModel);
                            $_POST['returnVpaDett'] = $itevpadett_rec;
                            $returnModelObj->setEvent('returnPraVpaDett');
                            $returnModelObj->parseEvent();
                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        if ($this->returnModel) {
                            $itevpadett_rec = $_POST[$this->nameForm . '_ITEVPADETT'];
                            $itevpadett_rec['ITECOD'] = $this->ITECOD;
                            $itevpadett_rec['ITEKEY'] = $this->ITEKEY;

                            $returnModelObj = itaModel::getInstance($this->returnModel);
                            $_POST['returnId'] = $this->returnId;
                            $_POST['returnVpaDett'] = $itevpadett_rec;
                            $returnModelObj->setEvent('returnPraVpaDett');
                            $returnModelObj->parseEvent();
                        }

                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_CancellaControllo':
                        Out::valore($this->nameForm . '_ITEVPADETT[ITEEXPRVPA]', '');
                        Out::valore($this->nameForm . '_Espressione', '');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnItepas':
                $this->decodVpaItekey($_POST['retKey'], 'rowid');
                break;

            case 'returnPraCondizioni':
                Out::valore($this->nameForm . '_ITEVPADETT[ITEEXPRVPA]', $_POST['returnCondizione']);
                Out::valore($this->nameForm . '_Espressione', trim($this->praLib->DecodificaControllo($_POST['returnCondizione']), "\n"));
                break;
        }
    }

    public function workSpace() {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        if (func_num_args()) {
            foreach (func_get_args() as $div) {
                Out::show($this->nameForm . "_$div");
            }
        }
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Aggiungi');

        if (func_num_args()) {
            foreach (func_get_args() as $button) {
                Out::show($this->nameForm . "_$button");
            }
        }
    }

    public function openDettaglio($vpaDettRec = null) {
        $this->workSpace('divDettaglio');

        if ($vpaDettRec) {
            $this->buttonBar('Aggiorna', 'Cancella');

            Out::valori($vpaDettRec, $this->nameForm . '_ITEVPADETT');
            Out::valore($this->nameForm . '_Espressione', trim($this->praLib->DecodificaControllo($vpaDettRec['ITEEXPRVPA']), "\n"));
            $this->decodVpaItekey($vpaDettRec['ITEVPA'], 'itekey');
        } else {
            $this->buttonBar('Aggiungi');
        }
    }

    private function decodVpaItekey($chiave, $tipo) {
        $ricite_rec = $this->praLib->GetItepas($chiave, $tipo);
        Out::valore($this->nameForm . '_ITEVPADETT[ITEVPA]', $ricite_rec['ITEKEY']);
        Out::valore($this->nameForm . '_ITEVPADETT_SEQ', $ricite_rec['ITESEQ']);
        Out::valore($this->nameForm . '_ITEVPADETT_DESC', $ricite_rec['ITEDES']);
    }

}
