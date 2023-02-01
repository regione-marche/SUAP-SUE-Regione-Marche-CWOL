<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praIteObb() {
    $praIteObb = new praIteObb();
    $praIteObb->parseEvent();
    return;
}

class praIteObb extends itaModel {

    public $praLib;
    public $nameForm = "praIteObb";
    public $openData;
    public $rigaSel;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->openData = App::$utente->getKey($this->nameForm . '_openData');
            $this->rigaSel = App::$utente->getKey($this->nameForm . '_rigaSel');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_openData', $this->openData);
            App::$utente->setKey($this->nameForm . '_rigaSel', $this->rigaSel);
        }
    }

    public function setOpenData($openData) {
        $this->openData = $openData;
    }

    public function setRigaSel($rigaSel) {
        $this->rigaSel = $rigaSel;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenGestione();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                    case $this->nameForm . '_Aggiorna':
                        $this->openData['OBBEVCOD'] = $_POST[$this->nameForm . '_ITEPRAOBB']['OBBEVCOD'];
                        $this->openData['OBBSUBPRA'] = $_POST[$this->nameForm . '_ITEPRAOBB']['OBBSUBPRA'];
                        $this->openData['OBBSUBEVCOD'] = $_POST[$this->nameForm . '_ITEPRAOBB']['OBBSUBEVCOD'];
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ITEPRAOBB[OBBEVCOD]_butt':
                        praRic::ricIteevt($this->nameForm, $where = ' WHERE ITEPRA = ' . $_POST[$this->nameForm . '_ITEPRAOBB']['OBBPRA'], '1');
                        break;
                    
                    case $this->nameForm . '_ITEPRAOBB[OBBSUBPRA]_butt':
                        $where = " PRANUM <> '" . $_POST[$this->nameForm . '_ITEPRAOBB']['OBBPRA'] . "'";
                        praRic::praRicAnapra($this->nameForm, '', '', $where, App::$utente->getKey('ditta'));
                        break;
                    
                    case $this->nameForm . '_ITEPRAOBB[OBBSUBEVCOD]_butt':
                        praRic::ricIteevt($this->nameForm, $where = ' WHERE ITEPRA = ' . $_POST[$this->nameForm . '_ITEPRAOBB']['OBBSUBPRA'], '2');
                        break;
                    
                    case $this->nameForm . '_ApriAttivaExpr':
                        $model = 'praPassoProcExpr';
                        $_POST = array();
                        $_POST['dati'] = array(
                            'TITOLO' => 'Espressione di obbligatorietà procedimento:',
                            'SOTTOTITOLO' => 'Procedimento abbligatorio se',
                            'SPEGNIDUPLICA' => true,
                            'ITEKEY' => '',
                            'ITECOD' => $this->openData['OBBPRA'],
                            'ITEATE' => $this->openData['OBBEXPRCTR'],
                            'TABELLA' => "ITEDAG"
                        );
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST['tipo'] = "ITEATE";
                        $_POST['ITEKEY'] = '';
                        $_POST['ITECOD'] = $this->openData['OBBPRA'];
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnAttivaExpr';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case "returnIteevt":
                $iteevt_rec = $this->praLib->GetIteevt($_POST['retKey'], 'rowid');
                switch ($_POST['retid']) {
                    case '1':
                        Out::valore($this->nameForm . '_ITEPRAOBB[OBBEVCOD]', $iteevt_rec['IEVCOD']);
                        $anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD'], 'codice');
                        Out::valore($this->nameForm . '_EventoPadre', $anaeventi_rec['EVTDESCR']);
                        break;
                    case '2':
                        Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBEVCOD]', $iteevt_rec['IEVCOD']);
                        $anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD'], 'codice');
                        Out::valore($this->nameForm . '_EventoProcedObbilgatorio', $anaeventi_rec['EVTDESCR']);
                        break;
                }
                break;
            
            case 'returnAttivaExpr' :
                $this->openData['OBBEXPRCTR'] = $_POST['dati']['ITEATE'];
                Out::valore($this->nameForm . '_AttivaEspressione', $this->praLib->DecodificaControllo($_POST['dati']['ITEATE']));
                break;
            
            case 'returnAnapra':
                $anapra_rec = $this->praLib->GetAnapra($_POST['rowData']['PRANUM'], 'codice');
                Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBPRA]', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_ProcedimentoObbligatorio', $anapra_rec['PRADES__1']);
                Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBEVCOD]', '');
                Out::valore($this->nameForm . '_EventoProcedObbilgatorio', '');
                $rowIDevento = $_POST['rowData']['ID_ITEEVT'];
                $desEvento = $_POST['rowData']['EVTDESCR'];
                $iteevt = $this->praLib->GetIteevt($rowIDevento, 'rowid');
                Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBEVCOD]', $iteevt['IEVCOD']);
                Out::valore($this->nameForm . '_EventoProcedObbilgatorio', $desEvento);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_openData');
        App::$utente->removeKey($this->nameForm . '_rigaSel');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['model'] = $this->returnModel;
        $_POST['obbligatorio'] = $this->openData;
        $_POST['rigaSel'] = $this->rigaSel;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function OpenGestione() {
        $this->mostraForm('divGestione');
        Out::clearFields($this->nameForm . '_divGestione');
        Out::setFocus($this->nameForm, $this->nameForm . '_ITEEVT[IEVDESCR]');
        if ($this->openData) {
            $this->mostraButtonBar(array('Aggiorna'));
            Out::valore($this->nameForm . '_ITEPRAOBB[OBBPRA]', $this->openData['OBBPRA']);
            Out::valore($this->nameForm . '_ITEPRAOBB[OBBEVCOD]', $this->openData['OBBEVCOD']);
            Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBPRA]', $this->openData['OBBSUBPRA']);
            Out::valore($this->nameForm . '_ITEPRAOBB[OBBSUBEVCOD]', $this->openData['OBBSUBEVCOD']);
            Out::valore($this->nameForm . '_ITEPRAOBB[ROWID]', $this->openData['ROWID']);
            $AnapraPadre = $this->praLib->GetAnapra($this->openData['OBBPRA']);
            $AnaeventiPadre = $this->praLib->GetAnaeventi($this->openData['OBBEVCOD']);
            $AnapraObb = $this->praLib->GetAnapra($this->openData['OBBSUBPRA']);
            $AnaeventiObb = $this->praLib->GetAnaeventi($this->openData['OBBSUBEVCOD']);
            Out::valore($this->nameForm . '_DescrizionePadre', $AnapraPadre['PRADES__1']);
            Out::valore($this->nameForm . '_EventoPadre', $AnaeventiPadre['EVTDESCR']);
            Out::valore($this->nameForm . '_ProcedimentoObbligatorio', $AnapraObb['PRADES__1']);
            Out::valore($this->nameForm . '_EventoProcedObbilgatorio', $AnaeventiObb['EVTDESCR']);
            Out::valore($this->nameForm . '_AttivaEspressione', $this->praLib->DecodificaControllo($this->openData['OBBEXPRCTR']));
            
        }
    }

}
