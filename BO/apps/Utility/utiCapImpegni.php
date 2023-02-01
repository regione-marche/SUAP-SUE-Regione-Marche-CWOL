<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/lib/itaPHPCityWare/itaLibCity.class.php';

function utiCapImpegni() {
    $utiCapImpegni = new utiCapImpegni();
    $utiCapImpegni->parseEvent();
    return;
}

class utiCapImpegni extends itaModel {

    public $CITYWARE_DB;
    public $nameForm = "utiCapImpegni";
    public $divRis = "utiCapImpegni_divRisultato";
    public $gridImpegni = "utiCapImpegni_gridImpegni";
    public $capitolo;
    public $itaLibCity;

    function __construct() {
        parent::__construct();
        $this->itaLibCity = new itaLibCity();
        $this->CITYWARE_DB = $this->itaLibCity->getCITYWARE();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function setCapitolo($capitolo) {
        $this->capitolo = $capitolo;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->divGes);
                $this->visualizzaImpegni();
                break;
            case 'onClick':
                break;
            case 'dbClickRow':

                break;
            case 'cellSelect':

                break;
        }
    }

    public function close() {
        parent::close();

        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function visualizzaImpegni() {
        $sql = $this->itaLibCity->creaSqlImpegni($this->capitolo['ANNO_ESE'], $this->capitolo['PROGKEYVB']);
        $impegni = ItaDB::DBSQLSelect($this->CITYWARE_DB, $sql, true);
//Out::msgInfo('',print_r($impegni,true));
        if ($impegni) {
            $impegni = $this->elaboraRecord($impegni);
            $this->caricaGriglia($this->gridImpegni, $impegni);
        } else {
            Out::msgInfo('AVVISO', 'Non trovati impegni da visualizzare.');
        }
    }

    public function elaboraRecord($impegni) {
        foreach ($impegni as $key => $impegno) {
            switch ($impegno['PROVEN_IMP']) {
                case 0:
                    $prov = 'CP';
                    break;
                case 1:
                    $prov = 'FP';
                    break;
                case 2:
                    $prov = 'DC';
                    break;
                case 3:
                    $prov = 'RE';
                    break;
                case 4:
                    $prov = 'RP';
                    break;
                default:
                    $prov = '';
                    break;
            }
            $impegni[$key]['CAPITOLO'] = "<p style=\"height:20px; color: red; font-size:10px; \">" . $impegno['ANNO_ESE'] . '/' . trim($impegno['N_IMPEG']) . "      " . $prov . "</p>
                                          <p style=\"height:20px; font-size:10px; text-align:right; \">" . $impegno['CODMECCAN'] . '/' . $impegno['CODVOCEBIL'] . "</p>";
            $impegni[$key]['IMIMP_ORIG'] = "<p style=\"color: blue; text-align: right;\">" . $this->itaLibCity->importoEuro($impegno['IMIMP_ORIG']) . "</p>";
            $impegni[$key]['IMIMP_DISP'] = "<p style=\"color: blue; text-align: right;\">" . $this->itaLibCity->importoEuro($impegno['IMIMP_DISP']) . "</p>";
            $impegni[$key]['IMIMP_DISL'] = "<p style=\"color: blue; text-align: right;\">" . $this->itaLibCity->importoEuro($impegno['IMIMP_DISL']) . "</p>";
            $impegni[$key]['IMIMP_IMPO'] = "<p style=\"color: blue; text-align: right;\">" . $this->itaLibCity->importoEuro($impegno['IMIMP_IMPO']) . "</p>";
            $impegni[$key]['CLASS_IMP'] = "<p style=\"text-align: center;\">" . $impegno['CLASS_IMP'] . "</p>";
            $impegni[$key]['DATADELIB'] = substr($impegno['DATADELIB'], 8,2) . '/' . substr($impegno['DATADELIB'], 5,2) . '/' . substr($impegno['DATADELIB'], 0,4);
            $impegni[$key]['DES_IMP'] = "<div style =\"height:40px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $impegno['DES_IMP'] . "</div></div>";
            //
            $pagato = 0;
            $sqlPagato = $this->itaLibCity->creaSqlPagatoPerImpegno($impegno['ANNO_ESE'], $impegno['PROGIMPACC']);
            $pagamenti = ItaDB::DBSQLSelect($this->CITYWARE_DB, $sqlPagato, true);
            foreach ($pagamenti as $pagamento) {
                $pagato+= $pagamento['IMIMP_TPAG'];
            }
            $impegni[$key]['PAGATO'] = "<p style=\"color: blue; text-align: right;\">" . $this->itaLibCity->importoEuro($pagato) . "</p>";
        }
        return $impegni;
    }

    public function CaricaGriglia($griglia, $dati, $sidx = '', $sord = '') {
        $ita_grid01 = new TableView(
                        $griglia, array('arrayTable' => $dati,
                    'rowIndex' => 'idx')
        );
        if ($sidx != '') {
            $ita_grid01->setSortIndex($sidx);
        }
        if ($sord != '') {
            $ita_grid01->setSortOrder($sord);
        }
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($griglia);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($griglia);
        }
    }


}

?>
