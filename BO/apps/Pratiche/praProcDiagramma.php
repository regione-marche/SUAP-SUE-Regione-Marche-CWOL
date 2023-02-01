<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
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
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDiagramma.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/DiagramView.class.php';

function praProcDiagramma() {
    $praProcDiagramma = new praProcDiagramma();
    $praProcDiagramma->parseEvent();
    return;
}

class praProcDiagramma extends itaModel {

    public $nameForm = 'praProcDiagramma';
    public $formWorkflow = 'praProcDiagramma_divWorkflow';
    public $praLib;
    public $praLibDiagramma;
    public $PRAM_DB;
    private $pranum;
    private $diagram;
    private $viewInfo = array();
    private $groupsStatus = array();

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->formWorkflow = $this->nameForm . '_divWorkflow';
        $this->praLib = new praLib();
        $this->praLibDiagramma = new praLibDiagramma($this->formWorkflow, praLibDiagramma::DIAGRAMMA_PROCEDIMENTO);
        $this->PRAM_DB = ItaDB::DBOpen('PRAM');
        $this->pranum = App::$utente->getKey($this->nameForm . '_pranum');
        $this->diagram = App::$utente->getKey($this->nameForm . '_diagram');
        $this->viewInfo = App::$utente->getKey($this->nameForm . '_viewInfo');
        $this->groupsStatus = App::$utente->getKey($this->nameForm . '_groupsStatus');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_pranum', $this->pranum);
            App::$utente->setKey($this->nameForm . '_diagram', $this->diagram);
            App::$utente->setKey($this->nameForm . '_viewInfo', $this->viewInfo);
            App::$utente->setKey($this->nameForm . '_groupsStatus', $this->groupsStatus);
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_pranum');
        App::$utente->removeKey($this->nameForm . '_diagram');
        App::$utente->removeKey($this->nameForm . '_viewInfo');
        App::$utente->removeKey($this->nameForm . '_groupsStatus');
        Out::closeDialog($this->nameForm);
    }

    public function parseEvent() {
        parent::parseEvent();

        if (isset($_POST[$this->formWorkflow])) {
            $this->diagram = utf8_decode($_POST[$this->formWorkflow]['json']);
            $this->viewInfo = array('pan' => $_POST[$this->formWorkflow]['pan'], 'zoom' => $_POST[$this->formWorkflow]['zoom']);
        }

        switch ($this->event) {
            case 'openform':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->formWorkflow . '_ActionBar_Add':
                        $model = 'praPro';
                        /* @var $praPro praPro */
                        $praPro = itaModel::getInstance($model);
                        $praPro->setElementId($praPro->gridPassi);
                        $praPro->setEvent('addGridRow');
                        $praPro->parseEvent();
                        break;

                    case $this->formWorkflow . '_ActionBar_Edit':
                        $itekey = $_POST[$this->formWorkflow]['selectedNodes'][0];
                        $itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST['rowid'] = $itepas_rec['ROWID'];

                        $model = 'praPro';
                        /* @var $praPro praPro */
                        $praPro = itaModel::getInstance($model);
                        $praPro->setElementId($praPro->gridPassi);
                        $praPro->setEvent('editGridRow');
                        $praPro->parseEvent();
                        break;

                    case $this->formWorkflow . '_ActionBar_Delete':
                        $itekey = $_POST[$this->formWorkflow]['selectedNodes'][0];
                        $itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');
                        $_POST['rowid'] = $itepas_rec['ROWID'];

                        $model = 'praPro';
                        /* @var $praPro praPro */
                        $praPro = itaModel::getInstance($model);
                        $praPro->setElementId($praPro->gridPassi);
                        $praPro->setEvent('delGridRow');
                        $praPro->parseEvent();
                        break;

                    case $this->formWorkflow . '_ActionBar_ChangeColor':
                        if (!$_POST[$this->formWorkflow]['selectedNodes'][0]) {
                            break;
                        }

                        if ($_POST[$this->formWorkflow]['selectedNodes'][0] && $_POST[$this->formWorkflow]['selectedNodes'][1]) {
                            DiagramView::diagramSetPathColor($this->formWorkflow, $_POST[$this->formWorkflow]['selectedNodes'][0], $_POST[$this->formWorkflow]['selectedNodes'][1], '#' . substr(md5(rand()), 0, 6));
                        }

                        foreach ($_POST[$this->formWorkflow]['selectedNodes'] as $nodekey) {
                            DiagramView::diagramSetNodeBackgroundColor($this->formWorkflow, $nodekey, '#' . substr(md5(rand()), 0, 6));
                            DiagramView::diagramSetNodeBorderColor($this->formWorkflow, $nodekey, '#' . substr(md5(rand()), 0, 6));
                            DiagramView::diagramSetNodeTextColor($this->formWorkflow, $nodekey, '#' . substr(md5(rand()), 0, 6));
                        }
                        break;

                    case $this->formWorkflow . '_ActionBar_Groups':
                        $inputs = array();
                        $itediaggruppi_tab = $this->praLib->GetItediaggruppi($this->pranum, 'pranum');

                        if (!count($itediaggruppi_tab)) {
                            Out::msgInfo('Attenzione', 'Non ci sono gruppi da configurare.');
                            break;
                        }

                        foreach ($itediaggruppi_tab as $itediaggruppi_rec) {
                            $inputGruppo = array(
                                'label' => array('value' => $itediaggruppi_rec['DESCRIZIONE'], 'style' => 'float: right; margin-left: 10px;'),
                                'id' => $this->nameForm . '_GRUPPI[' . $itediaggruppi_rec['ROW_ID'] . ']',
                                'name' => $this->nameForm . '_GRUPPI[' . $itediaggruppi_rec['ROW_ID'] . ']',
                                'type' => 'checkbox'
                            );

                            /*
                             * @TODO Verificare lo stato del gruppo
                             */
                            if (!isset($this->groupsStatus[$itediaggruppi_rec['ROW_ID']]) || $this->groupsStatus[$itediaggruppi_rec['ROW_ID']]) {
                                $inputGruppo['checked'] = 'checked';
                            }

                            $inputs[] = $inputGruppo;
                        }

                        Out::msgInput('Configurazione gruppi', $inputs, array(
                            'Conferma' => array(
                                'id' => $this->nameForm . '_confermaConfigurazioneGruppi',
                                'model' => $this->nameForm
                            )
                            ), $this->nameForm);
                        break;

                    case $this->nameForm . '_confermaConfigurazioneGruppi':
                        $gruppiConfigurati = $_POST[$this->nameForm . '_GRUPPI'];

                        foreach ($gruppiConfigurati as $rowid => $checked) {
                            $this->groupsStatus[$rowid] = $checked == 1;
                        }

                        $this->elaboraVisualizzazioneDiagramma();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openGestione($Anapra_rec) {
        if ($this->pranum != $Anapra_rec['PRANUM']) {
            $this->viewInfo = array();
        }

        $this->pranum = $Anapra_rec['PRANUM'];
        $this->diagram = $Anapra_rec['PRADIAG'];

        if ($Anapra_rec['PRATPR'] !== 'ENDOPROCEDIMENTOWRKF') {
            $dv = new DiagramView($this->formWorkflow);
            $dv->clear();
            return false;
        }

        $this->groupsStatus = array();

        $itediaggruppi_tab = $this->praLib->GetItediaggruppi($Anapra_rec['PRANUM'], 'pranum');
        foreach ($itediaggruppi_tab as $itediaggruppi_rec) {
            $this->groupsStatus[$itediaggruppi_rec['ROW_ID']] = $itediaggruppi_rec['STATO'];
        }

        $this->caricaDiagramma();
    }

    public function aggiorna() {
        if (isset($_POST[$this->formWorkflow])) {
            $this->diagram = utf8_decode($_POST[$this->formWorkflow]['json']);
            $this->viewInfo = array('pan' => $_POST[$this->formWorkflow]['pan'], 'zoom' => $_POST[$this->formWorkflow]['zoom']);
        }

        $anapra_rec = $this->praLib->getAnapra($this->pranum);

        if ($anapra_rec['PRATPR'] == 'ENDOPROCEDIMENTOWRKF') {
            $json_value = $_POST[$this->formWorkflow]['json'];
            $anapra_rec['PRADIAG'] = utf8_decode($json_value);
            $this->diagram = $anapra_rec['PRADIAG'];
        } else {
            $anapra_rec['PRADIAG'] = '';
            $this->diagram = '';
        }

        if (!$this->updateRecord($this->PRAM_DB, 'ANAPRA', $anapra_rec, 'Aggiornamento workflow')) {
            Out::msgStop('Errore', 'Errore aggiornamento del workflow.');
            return false;
        }

        return true;
    }

    public function caricaDiagramma($pranum = null) {
        if (!$this->pranum) {
            return;
        }

        if ($pranum && $pranum != $this->pranum) {
            return;
        }

        $anapra_rec = $this->praLib->GetAnapra($this->pranum);
        if ($anapra_rec['PRATPR'] != 'ENDOPROCEDIMENTOWRKF') {
            return;
        }

        $this->diagram = $this->praLibDiagramma->generaDiagramma($this->pranum, $this->diagram);

        $impostaLayout = false;
        if (!$this->diagram) {
            $impostaLayout = true;
        }

        $dv = new DiagramView($this->formWorkflow);
        $dv->importJSON($this->diagram);

        if ($impostaLayout) {
            $dv->setLayout('Hierarchical');
        }

        if ($this->viewInfo['pan']) {
            $dv->setPan($this->viewInfo['pan']['x'], $this->viewInfo['pan']['y']);
        }

        if ($this->viewInfo['zoom']) {
            $dv->setZoom($this->viewInfo['zoom']);
        }

        $dv->clearSelection();

        $this->elaboraVisualizzazioneDiagramma();
    }

    public function elaboraVisualizzazioneDiagramma() {
        $this->praLibDiagramma->mostraNodi($this->pranum);

        if (is_array($this->groupsStatus)) {
            foreach ($this->groupsStatus as $id => $status) {
                if (!$status) {
                    $this->praLibDiagramma->nascondiNodiGruppo($id);
                }
            }
        }
    }

}
