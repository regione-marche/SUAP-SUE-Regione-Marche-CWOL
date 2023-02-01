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

function praCondizioni() {
    $praCondizioni = new praCondizioni();
    $praCondizioni->parseEvent();
    return;
}

class praCondizioni extends itaModel {

    public $nameForm = 'praCondizioni';
    public $gridEspressione = 'praCondizioni_gridEspressione';
    private $codiceProcedimento;
    private $codicePasso;
    private $arrayEspressioni;
    private $campoCondizione;
    private $praLib;
    private $condizioni = array(
        'uguale' => '==',
        'diverso' => '!=',
        'maggiore' => '>',
        'minore' => '<',
        'maggiore-uguale' => '>=',
        'minore-uguale' => '<='
    );

    function __construct() {
        parent::__construct();

        $this->praLib = new praLib;
        $this->arrayEspressioni = App::$utente->getKey($this->nameForm . '_arrayEspressioni') ?: array();
        $this->campoCondizione = App::$utente->getKey($this->nameForm . '_campoCondizione');

        $this->codiceProcedimento = App::$utente->getKey($this->nameForm . '_codiceProcedimento');
        $this->codicePasso = App::$utente->getKey($this->nameForm . '_codicePasso');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_arrayEspressioni', $this->arrayEspressioni);
            App::$utente->setKey($this->nameForm . '_campoCondizione', $this->campoCondizione);

            App::$utente->setKey($this->nameForm . '_codiceProcedimento', $this->codiceProcedimento);
            App::$utente->setKey($this->nameForm . '_codicePasso', $this->codicePasso);
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);

        App::$utente->removeKey($this->nameForm . '_arrayEspressioni');
        App::$utente->removeKey($this->nameForm . '_campoCondizione');

        App::$utente->removeKey($this->nameForm . '_codiceProcedimento');
        App::$utente->removeKey($this->nameForm . '_codicePasso');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function setArrayEspressioni($arrayEspressioni) {
        $this->arrayEspressioni = $arrayEspressioni;
    }

    public function setCodiceProcedimento($codiceProcedimento) {
        $this->codiceProcedimento = $codiceProcedimento;
    }

    public function setCodicePasso($codicePasso) {
        $this->codicePasso = $codicePasso;
    }

    public function setCampoCondizione($campoCondizione) {
        $this->campoCondizione = $campoCondizione;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (in_array($this->campoCondizione, array('ITDCTR'))) {
                    Out::hide($this->nameForm . '_dictCampo1');
                    Out::hide($this->nameForm . '_dictCampo2');
                }

                $this->switchTipologiaCondizione('S');

                Out::select($this->nameForm . '_TipologiaCondizione', 1, 'S', 1, 'Espressione semplice');
                Out::select($this->nameForm . '_TipologiaCondizione', 1, 'C', 0, 'Espressione da codice');
                Out::select($this->nameForm . '_TipologiaCondizione', 1, 'T', 0, 'Template Smarty');

                Out::select($this->nameForm . '_tipCampo1', 1, 'V', 0, 'Valore');
                Out::select($this->nameForm . '_tipCampo1', 1, 'D', 1, 'Campo');
                Out::select($this->nameForm . '_tipCampo1', 1, 'C', 0, 'Codice');

                Out::select($this->nameForm . '_tipCampo2', 1, 'V', 1, 'Valore');
                Out::select($this->nameForm . '_tipCampo2', 1, 'D', 0, 'Campo');
                Out::select($this->nameForm . '_tipCampo2', 1, 'C', 0, 'Codice');

                Out::required($this->nameForm . '_Campi', true, false);
                Out::required($this->nameForm . '_Codice', true, false);

                $this->praLib->creaComboCondizioni($this->nameForm . '_Condizione');

                $this->caricaGriglia();
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TipologiaCondizione':
                        $this->switchTipologiaCondizione($_POST[$_POST['id']]);
                        break;

                    case $this->nameForm . '_tipCampo1':
                    case $this->nameForm . '_tipCampo2':
                        $this->switchTipologiaCampo(substr($_POST['id'], -1), $_POST[$_POST['id']]);
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridEspressione:
                        unset($this->arrayEspressioni[$_POST['rowid']]);

                        $this->arrayEspressioni = array_values($this->arrayEspressioni);

                        $this->caricaGriglia();
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridEspressione:
                        if ($_POST['cellname'] === 'OPERATORE' && $_POST['rowid'] == 0) {
                            $this->caricaGriglia();
                            break;
                        }

                        $this->arrayEspressioni[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        $this->caricaGriglia();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CreaCtr':
                        if ($_POST[$this->nameForm . "_tipCampo1"] !== 'V' && !$this->getValoreCampo('1')) {
                            Out::msgStop('Errore', 'Inserire un valore.');
                            break;
                        }

                        if ($_POST[$this->nameForm . '_TipologiaCondizione'] == 'S' && $_POST[$this->nameForm . "_tipCampo2"] !== 'V' && !$this->getValoreCampo('2')) {
                            Out::msgStop('Errore', 'Inserire un valore.');
                            break;
                        }

                        Out::valore($this->nameForm . '_Valore1', '');
                        Out::valore($this->nameForm . '_Campo1', '');
                        Out::valore($this->nameForm . '_Codice1', '');
                        Out::valore($this->nameForm . '_Condizione', 'uguale');
                        Out::valore($this->nameForm . '_Valore2', '');
                        Out::valore($this->nameForm . '_Campo2', '');
                        Out::valore($this->nameForm . '_Codice2', '');

                        $this->arrayEspressioni[] = $this->creaCondizione();
                        $this->caricaGriglia();
                        break;

                    case $this->nameForm . '_SalvaCtr':
                        if ($this->getValoreCampo('1') && $this->getValoreCampo('2')) {
                            $this->arrayEspressioni[] = $this->creaCondizione();
                        }

                        foreach ($this->arrayEspressioni as $i => $expr) {
                            if ($expr['TIPOCAMPO'] === 'C' || $expr['TIPOCAMPO'] === 'T') {
                                $this->arrayEspressioni[$i]['CAMPO'] = $string = preg_replace('/\s+/', ' ', $expr['CAMPO']);
                            }
                        }

                        $_POST = array();
                        $_POST['returnCondizione'] = serialize($this->arrayEspressioni);

                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent ?: 'returnPraCondizioni');
                        $returnObj->parseEvent();

                        Out::closeCurrentDialog();
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Campo1_butt':
                    case $this->nameForm . '_Campo2_butt':
                        $n = substr($_POST['id'], -6, 1);

                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->codiceProcedimento);
                        $praLibVar->setChiavePasso($this->codicePasso);
                        $arrayLegenda = $praLibVar->getLegendaDatiAggiuntiviPasso('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili' . $n, true);
                        break;

                    case $this->nameForm . '_dictCampo1':
                    case $this->nameForm . '_dictCampo2':
                        $n = substr($_POST['id'], -1, 1);

                        $praLibVar = new praLibVariabili();
                        $praLibVar->setFrontOfficeFlag(true);
                        $praLibVar->setCodiceProcedimento($this->codiceProcedimento);
                        $praLibVar->setChiavePasso($this->codicePasso);
                        $arrayLegenda = $praLibVar->getLegendaProcedimento('adjacency', 'none');
                        praRic::ricVariabili($arrayLegenda, $this->nameForm, 'returnVariabili' . $n, true);
                        break;

                    case $this->nameForm . '_AnnullaCtr':
                        Out::closeCurrentDialog();
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnVariabili1':
                $variabile = $_POST['rowData']['markupkey'];

                if (in_array($this->campoCondizione, array('ITDCTR'))) {
                    $variabile = str_replace('PRAAGGIUNTIVI.', '', $variabile);
                }

                Out::valore($this->nameForm . '_Campo1', $variabile);
                break;

            case 'returnVariabili2':
                $variabile = $_POST['rowData']['markupkey'];

                if (in_array($this->campoCondizione, array('ITDCTR'))) {
                    $variabile = str_replace('PRAAGGIUNTIVI.', '', $variabile);
                }

                Out::valore($this->nameForm . '_Campo2', $variabile);
                break;
        }
    }

    private function caricaGriglia() {
        TableView::clearGrid($this->gridEspressione);

        if (isset($this->arrayEspressioni[0])) {
            $this->arrayEspressioni[0]['OPERATORE'] = '';
        }

        $gridEspressione = new TableView($this->gridEspressione, array(
            'arrayTable' => $this->arrayEspressioni,
            'rowIndex' => 'idx'
        ));

        if (count($this->arrayEspressioni) > 0) {
            Out::show($this->nameForm . '_divRadio');
        } else {
            Out::hide($this->nameForm . '_divRadio');
        }

        $gridEspressione->setPageRows(9999);

        return $gridEspressione->getDataPage('json');
    }

    private function switchTipologiaCondizione($v) {
        switch ($v) {
            case 'S':
                Out::show($this->nameForm . '_Condizione_field');
                Out::show($this->nameForm . '_divCampo2');

                $this->switchTipologiaCampo('1', 'D');
                $this->switchTipologiaCampo('2', 'V');
                Out::enableField($this->nameForm . '_tipCampo1');
                break;

            case 'T':
            case 'C':
                Out::hide($this->nameForm . '_Condizione_field');
                Out::hide($this->nameForm . '_divCampo2');

                $this->switchTipologiaCampo('1', 'C');
                Out::disableField($this->nameForm . '_tipCampo1');
                break;
        }
    }

    private function switchTipologiaCampo($n, $v) {
        switch ($v) {
            case 'V':
                Out::show($this->nameForm . '_Valore' . $n . '_field');
                Out::hide($this->nameForm . '_divEditCampo' . $n);
                Out::hide($this->nameForm . '_divCodice' . $n);
                break;

            case 'D':
                Out::hide($this->nameForm . '_Valore' . $n . '_field');
                Out::show($this->nameForm . '_divEditCampo' . $n);
                Out::hide($this->nameForm . '_divCodice' . $n);
                break;

            case 'C':
                Out::hide($this->nameForm . '_Valore' . $n . '_field');
                Out::hide($this->nameForm . '_divEditCampo' . $n);
                Out::show($this->nameForm . '_divCodice' . $n);
                break;
        }

        Out::valore($this->nameForm . '_tipCampo' . $n, $v);
    }

    private function getValoreCampo($n) {
        switch ($_POST[$this->nameForm . "_tipCampo$n"]) {
            case 'V':
                return $_POST[$this->nameForm . "_Valore$n"];

            case 'D':
                return $_POST[$this->nameForm . "_Campo$n"];

            default:
            case 'C':
                return $_POST[$this->nameForm . "_Codice$n"];
        }
    }

    private function creaCondizione() {
        $operatore = count($this->arrayEspressioni) > 0 ? $_POST[$this->nameForm . '_Operatore'] : '';

        switch ($_POST[$this->nameForm . '_TipologiaCondizione']) {
            case 'S':
                return array(
                    'TIPOCAMPO' => $_POST[$this->nameForm . '_tipCampo1'],
                    'CAMPO' => $this->getValoreCampo('1'),
                    'CONDIZIONE' => $this->condizioni[$_POST[$this->nameForm . '_Condizione']],
                    'TIPOVALORE' => $_POST[$this->nameForm . '_tipCampo2'],
                    'VALORE' => $this->getValoreCampo('2'),
                    'OPERATORE' => $operatore
                );

            case 'T':
            case 'C':
                return array(
                    'TIPOCAMPO' => $_POST[$this->nameForm . '_TipologiaCondizione'],
                    'CAMPO' => $this->getValoreCampo('1'),
                    'CONDIZIONE' => '',
                    'TIPOVALORE' => '',
                    'VALORE' => '',
                    'OPERATORE' => $operatore
                );
        }
    }

}
