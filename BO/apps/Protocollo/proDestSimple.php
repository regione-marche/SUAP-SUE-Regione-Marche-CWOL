<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proDestSimple() {
    $proDestSimple = new proDestSimple();
    $proDestSimple->parseEvent();
    return;
}

class proDestSimple extends itaModel {

    const IDX_DIRETTI_DA = 1;
    const IDX_DIRETTI_A = 14;
    const IDX_CONOSCENZA_DA = 15;
    const IDX_CONOSCENZA_A = 29;

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proDestSimple";
    public $divGes = "proDestSimple_divGestione";
    public $destinatari;
    public $prova;
    public $arrayCombinazione;
    public $tipoProt;
    public $numProt;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->arrayCombinazione = App::$utente->getKey($this->nameForm . '_arrayCombinazione');
            $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
            $this->numProt = App::$utente->getKey($this->nameForm . '_numProt');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function setDestinatari($destinatari) {
        $this->destinatari = $destinatari;
    }

    public function setTipoProt($tipoProt) {
        $this->tipoProt = $tipoProt;
    }

    public function setNumProt($numProt) {
        $this->numProt = $numProt;
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
        App::$utente->setKey($this->nameForm . '_arrayCombinazione', $this->arrayCombinazione);
        App::$utente->setKey($this->nameForm . '_tipoProt', $this->tipoProt);
        App::$utente->setKey($this->nameForm . '_numProt', $this->numProt);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openDestinatari':
                itaLib::openForm($this->nameForm);
            case 'openform':
                $this->arrayCombinazione = array();
                if ($this->destinatari != '') {
                    $idxDiretti = self::IDX_DIRETTI_DA;
                    $idxConoscenza = self::IDX_CONOSCENZA_DA;
                    foreach ($this->destinatari['ALTRIDEST'] as $key => $Destinatario) {
                        if ($this->destinatari['ALTRIDEST'][$key]['DESCONOSCENZA'] == 1) {
                            if ($idxConoscenza > self::IDX_CONOSCENZA_A) {
                                Out::msgInfo("Attenzione", "Superato il numero massimo di destinatari gestibili per conoscenza.<br> Gestire in modo classico.");
                                $this->close();
                                break;
                            }
                            Out::valore($this->nameForm . '_dest_' . $idxConoscenza, $Destinatario['DESNOM']);
                            $this->arrayCombinazione[$idxConoscenza] = $key;
                            $idxConoscenza++;
                        } else {
                            if ($idxDiretti > self::IDX_DIRETTI_A) {
                                Out::msgInfo("Attenzione", "Superato il numero massimo di destinatari gestibili.<br> Gestire in modo classico.");
                                $this->close();
                                break;
                            }
                            Out::valore($this->nameForm . '_dest_' . $idxDiretti, $Destinatario['DESNOM']);
                            $this->arrayCombinazione[$idxDiretti] = $key;
                            $idxDiretti++;
                        }
                    }
                    Out::valore($this->nameForm . '_dest_pr', $this->destinatari['PRINCIPALE']['PRONOM']);
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'printTableToHTML':
            case 'onBlur':
                switch ($_POST['id']) {
                    default:
                        if ($_POST[$_POST['id']] == '') {
                            Out::setFocus('', $this->nameForm . '_dest_15');
                        }
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->destinatari['PRINCIPALE'] = array(
                            'PRONOM' => $_POST[$this->nameForm . '_dest_pr']
                        );
                        for ($idxDest = self::IDX_DIRETTI_DA; $idxDest <= self::IDX_CONOSCENZA_A; $idxDest++) {
                            /*
                             * Elimino la voce se cancellato il nominativo
                             */
                            if ($_POST[$this->nameForm . '_dest_' . $idxDest] == '' && array_key_exists($idxDest, $this->arrayCombinazione)) {
                                unset($this->destinatari['ALTRIDEST'][$this->arrayCombinazione[$idxDest]]);
                                continue;
                            }
                            /*
                             * Salvo le voci
                             */
                            if ($_POST[$this->nameForm . '_dest_' . $idxDest] != '') {
                                if ($idxDest >= self::IDX_DIRETTI_DA && $idxDest <= self::IDX_DIRETTI_A) {
                                    $conoscenza = 0;
                                    $conoscenza_html = '';
                                } elseif ($idxDest >= self::IDX_CONOSCENZA_DA && $idxDest <= self::IDX_CONOSCENZA_A) {
                                    $conoscenza = 1;
                                    $conoscenza_html = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                                }
                                /*
                                 * Da modificare
                                 */
                                if (array_key_exists($idxDest, $this->arrayCombinazione)) {

                                    $this->destinatari['ALTRIDEST'][$this->arrayCombinazione[$idxDest]]['DESNOM'] = $_POST[$this->nameForm . '_dest_' . $idxDest];
                                    $this->destinatari['ALTRIDEST'][$this->arrayCombinazione[$idxDest]]['DESCONOSCENZA'] = $conoscenza;
                                    $this->destinatari['ALTRIDEST'][$this->arrayCombinazione[$idxDest]]['CONOSCENZA'] = $conoscenza_html;

                                    /*
                                     * Nuovo 
                                     */
                                } else {
                                    $destinatario = array();
                                    $destinatario['PRONOM'] = $destinatario['DESNOM'] = $_POST[$this->nameForm . '_dest_' . $idxDest];
                                    $destinatario['PROPAR'] = $destinatario['DESPAR'] = $this->tipoProt;
                                    $destinatario['PROIND'] = $destinatario['DESIND'] = "";
                                    $destinatario['PROCAP'] = $destinatario['DESCAP'] = "";
                                    $destinatario['PROCIT'] = $destinatario['DESCIT'] = "";
                                    $destinatario['PROPRO'] = $destinatario['DESPRO'] = "";
                                    $destinatario['PRODAR'] = $destinatario['DESDAT'] = "";
                                    $destinatario['DESDAA'] = "";
                                    $destinatario['DESDUF'] = "";
                                    $destinatario['DESANN'] = "";
                                    $destinatario['DESMAIL'] = "";
                                    $destinatario['DESCUF'] = '';
                                    $destinatario['DESGES'] = 1;
                                    $destinatario['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                                    $destinatario['DESCONOSCENZA'] = $conoscenza;
                                    $destinatario['CONOSCENZA'] = $conoscenza_html;
                                    array_push($this->destinatari['ALTRIDEST'], $destinatario);
                                }
                            }
                        }
                        $this->returnToParent();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_arrayCombinazione');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_numProt');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        /* @var $ObjReturn proArri */
        $returnModel = $this->returnModel;
        $returnModelOrig = $returnModel;
        if (is_array($returnModel)) {
            $returnModelOrig = $returnModel['nameFormOrig'];
            $returnModel = $returnModel['nameForm'];
        }
        $ObjReturn = itaModel::getInstance($returnModelOrig, $returnModel);
        $ObjReturn->setEvent($this->returnEvent);
        $ObjReturn->setElementId($this->returnId);
        $_POST['destSimpleDestinatari'] = $this->destinatari;
        $ObjReturn->parseEvent();
        if ($close)
            $this->close();
    }

}

?>