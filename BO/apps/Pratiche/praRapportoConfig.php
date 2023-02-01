<?php

/**
 *
 *
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    21.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praRapportoConfig() {
    $praRapportoConfig = new praRapportoConfig();
    $praRapportoConfig->parseEvent();
    return;
}

class praRapportoConfig extends itaModel {

    private $PRAM_DB;
    private $praLib;
    public $nameForm = "praRapportoConfig";
    public $gridPassi = "praRapportoConfig_gridPassi";
    public $procedimento;
    private $passi;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->procedimento = App::$utente->getKey($this->nameForm . '_procedimento');
        $this->passi = App::$utente->getKey($this->nameForm . '_passi');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_procedimento', $this->procedimento);
            App::$utente->setKey($this->nameForm . '_passi', $this->passi);
        }
    }

    public function getProcedimento() {
        return $this->procedimento;
    }

    public function setProcedimento($procedimento) {
        $this->procedimento = $procedimento;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (!$this->ordinaPassiComp($this->procedimento)) {
                    Out::msgStop("Gestione Composizione Rapporto", "Caricamento sequenza fallito o nessun passo da configurare.");
                    break;
                }
                $this->caricaPassi($this->procedimento);
                break;
            case 'sortRowUpdate':
                // Posizione Originale (inizio drag)
                $idxStart = $_POST['startRowIndex'] - 1;
                // Posizione  Finale (stop drag o drop)
                $idxStop = $_POST['stopRowIndex'] - 1;
                $Itepas_start_rec = $this->praLib->GetItepas($_POST['rowid'], 'rowid');
                $Itepas_stop_rec = $this->praLib->GetItepas($this->passi[$idxStop]['ROWID'], 'rowid');
                if ($idxStart < $idxStop) {
                    $Itepas_start_rec['ITECOMPSEQ'] = $Itepas_stop_rec['ITECOMPSEQ'] + 1;
                } else {
                    $Itepas_start_rec['ITECOMPSEQ'] = $Itepas_stop_rec['ITECOMPSEQ'] - 1;
                }
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $Itepas_start_rec);
                    if ($nrow == -1) {
                        Out::msgStop("Errore", "Errore aggiornamento sequenza.");
                        break;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    break;
                }
                $this->ordinaPassiComp($this->procedimento);
                $this->caricaPassi($this->procedimento);
                TableView::setSelection($this->gridPassi, $_POST['rowid']);
                Out::msgBlock("", 1000, true, "Sequenza riconfigurata.");
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_reset':
                        $this->reset($this->procedimento);
                        $this->ordinaPassiComp($this->procedimento);
                        $this->caricaPassi($this->procedimento);
                        Out::msgBlock("", 1000, true, "Sequenza riconfigurata.");
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'afterSaveCell':
                $colonna = $_POST['cellname'];
                $rowid = $_POST['rowid'];
                $valore = $_POST['value'];
                $Itepas_rec = $this->praLib->GetItepas($rowid, 'rowid');
                $Itepas_rec['ITECOMPFLAG'] = $valore;
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $Itepas_rec);
                    if ($nrow == -1) {
                        Out::msgStop("Errore", "Aggiornamento tipo composizione fallito.");
                        break;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    break;
                }
                $this->ordinaPassiComp($this->procedimento);
                $this->caricaPassi($this->procedimento);
                TableView::setSelection($this->gridPassi, $_POST['rowid']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_procedimento');
        App::$utente->removeKey($this->nameForm . '_passi');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function caricaPassi($procedimento) {
        $sql = "SELECT
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITESEQ AS ITESEQ,
                    ITEPAS.ITECOMPSEQ AS ITECOMPSEQ,
                    ITEPAS.ITECOMPFLAG AS ITECOMPFLAG,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEDAT AS ITEDAT,
                    ITEPAS.ITEUPL AS ITEUPL,
                    ITEPAS.ITEMLT AS ITEMLT
                FROM ITEPAS 
                WHERE (ITEPAS.ITEIDR<>0 OR (ITEPAS.ITEDRR<>0 AND ITEPAS.ITEWRD<>'')) AND ITEPAS.ITECOD = '" . $procedimento . "' ORDER BY ITECOMPSEQ, ITESEQ";
        $this->passi = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        if ($this->passi) {
            foreach ($this->passi as $key => $passo) {
                if($passo['ITEDAT']){
                    $this->passi[$key]['ITECOMPSEQ'] = "<div style=\"display:inline-block;\">" . $passo['ITECOMPSEQ'] . "</div><div style=\"display:inline-block;\" class=\"ita-icon ita-icon-forms-24x24\"></div>";
                }elseif($passo['ITEUPL'] || $passo['ITEMLT']){
                    $this->passi[$key]['ITECOMPSEQ'] = "<div style=\"display:inline-block;\">" . $passo['ITECOMPSEQ'] . "</div><div style=\"display:inline-block;\" class=\"ita-icon ita-icon-pdf-24x24\"></div>";
                }else{
                    $this->passi[$key]['ITECOMPSEQ'] = "<div style=\"display:inline-block;\">" . $passo['ITECOMPSEQ'] . "</div><div style=\"display:inline-block;\" class=\"ita-icon ita-icon-document-24x24\"></div>";                    
                }
                if ($key > 0) {
                    $flag = $this->decodeFlag($passo['ITECOMPFLAG']);
                } else {
                    $flag = "";
                }
                $this->passi[$key]['ITECOMPFLAG'] = $flag;
            }
        }
        $this->CaricaGriglia($this->gridPassi, $this->passi, '1');
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1') {
        $ita_grid01 = new TableView(
                        $_griglia, array('arrayTable' => $_appoggio,
                    'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($_appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    function ordinaPassiComp($procedimento) {
        if ($procedimento) {
            $new_seq = 0;
            $Itepas_tab = $this->praLib->GetItepas($procedimento, 'codice', true, " AND (ITEIDR<>0 OR (ITEDRR<>0 AND ITEWRD<>'')) ORDER BY ITECOMPSEQ,ITESEQ");
            if (!$Itepas_tab) {
                return false;
            }
            foreach ($Itepas_tab as $Itepas_rec) {
                $new_seq +=10;
                $Itepas_rec['ITECOMPSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $Itepas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
            return true;
        }
    }

    function reset($procedimento) {
        if ($procedimento) {
            $Itepas_tab = $this->praLib->GetItepas($procedimento, 'codice', true);
            if (!$Itepas_tab) {
                return false;
            }
            foreach ($Itepas_tab as $Itepas_rec) {
                $Itepas_rec['ITECOMPSEQ'] = 0;
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $Itepas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
            return true;
        }
    }

    static public function decodeFlag($flag = '') {
        $val = '';
        switch ($flag) {
            case '':
            case 'A':
                $val = 'Accoda';
                break;
            case 'S':
                $val = 'Sovrapponi';
                break;
            default:
                $val = 'Accoda';
                break;
        }
        return $val;
    }

}

?>