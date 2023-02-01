<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaNumeratori.class.php';

function cwbBtaNrdAnno() {
    $cwbBtaNrdAnno = new cwbBtaNrdAn();
    $cwbBtaNrdAnno->parseEvent();
    return;
}

class cwbBtaNrdAnno extends itaFrontControllerCW {

    private $libNumeratore;
    
    function postItaFrontControllerCostruct() {
        $this->libDB = new cwbLibDB_BTA();
        $this->TABLE_NAME = 'BTA_NRD_AN';
        $this->libNumeratore = new cwbBtaNumeratori();
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANNO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_ANNO'], $this->nameForm . '_ANNO');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ok':
                        $this->apriAnno();
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . '_ANNO');
    }

    protected function apriAnno() {
//        $this->SQL = "SELECT SETT_IVA FROM FTA_SETIVA WHERE FLAG_DIS=0 ORDER BY SETT_IVA";
//        $settori = ItaDB::DBSQLSelect($this->MAIN_DB, $this->SQL, true);
        $ftaLib = new cwfLibDB_FTA();
        $filtri = array(
            'FLAG_DIS' => 0
        );
        $settori = $ftaLib->leggiFtaSetiva($filtri);
        
        $totsett = count($settori); // conto settori
        $numeratori = $this->libDB->leggiBtaNrd();
        $totele = count($numeratori); // conto numeratori presenti
        for ($i = 0; $i <= $totele; ++$i) {
            //Controllo se il numeratore è già stato aperto
//            $this->SQL = "SELECT COUNT (*) AS CONTA FROM BTA_NRD_AN"
//                    . " WHERE COD_NR_D='" . $numeratori[$i]['COD_NR_D'] . "' AND ANNOEMI=" . $_POST[$this->nameForm . '_ANNO'] . " AND SETT_IVA='" . $numeratori[$i]['SETT_IVA'] . "'";
//            $conta = ItaDB::DBSQLSelect($this->MAIN_DB, $this->SQL, false);
            
            $conta = $this->libNumeratore->countNumeratore($_POST[$this->nameForm . '_ANNO'], $numeratori[$i]['COD_NR_D'], $numeratori[$i]['SETT_IVA']);
            
            if ($conta['CONTA'] == 0) {
                //Se la numerazione è fiscale, e se non è presente il settore iva default,
                //viene creato un record x ogni settore iva
                if ($numeratori[$i]['F_TP_NR_D'] = 1 && $numeratori[$i]['SETT_IVA'] = '00') {
                    for ($s = 0; $s <= $totsett; ++$s) {

//                        $this->SQL = "SELECT * FROM BTA_NRD_AN"
//                                        . " WHERE COD_NR_D='".$numeratori[$i]['COD_NR_D']."' AND ANNOEMI=".($_POST[$this->nameForm. '_ANNO']-1)." AND SETT_IVA='".$settori[$s]['SETT_IVA']."'"; 
                        //    $nrd_ann_prec = ItaDB::DBSQLSelect($this->MAIN_DB, $this->SQL, true);
                        $nrd_ann_prec = $this->libNumeratore->leggiNumeratore(($_POST[$this->nameForm . '_ANNO'] - 1), $numeratori[$i]['COD_NR_D'], $settori[$s]['SETT_IVA']);

                        $this->CURRENT_RECORD = array(
                            'COD_NR_D' => $numeratori[$i]['COD_NR_D'],
                            'ANNOEMI' => $_POST[$this->nameForm . '_ANNO'],
                            'SETT_IVA' => $settori[$s]['SETT_IVA'],
                            'NUMULTDOC' => 0,
                            'NUMDOCBARA' => ($nrd_ann_prec['ANNOEMI'] > 0 ? $nrd_ann_prec['ANNOEMI'] : $numeratori['ANNOEMI']),
                        );
                        if (!$this->CURRENT_RECORD['COD_NR_D']) {
                            continue;
                        }
                        $this->RECORD_INFO = 'Oggetto: ' . $this->CURRENT_RECORD['COD_NR_D'] . " " . $this->CURRENT_RECORD['SETT_IVA'];
                        $this->insertRecord($this->MAIN_DB, $this->TABLE_NAME, $this->CURRENT_RECORD, $this->RECORD_INFO);
                    }
                } else { // numeratore non fiscale                        
//                    $this->SQL = "SELECT * FROM BTA_NRD_AN"
//                        . " WHERE COD_NR_D='".$numeratori[$i]['COD_NR_D']."' AND ANNOEMI=".($_POST[$this->nameForm. '_ANNO']-1)." AND SETT_IVA='".$numeratori[$i]['SETT_IVA']."'"; 
                    //      $nrd_ann_prec = ItaDB::DBSQLSelect($this->MAIN_DB, $this->SQL, true);
                    $nrd_ann_prec = $this->libNumeratore->leggiNumeratore(($_POST[$this->nameForm . '_ANNO'] - 1), $numeratori[$i]['COD_NR_D'], $numeratori[$i]['SETT_IVA']);

                    $this->CURRENT_RECORD = array(
                        'COD_NR_D' => $numeratori[$i]['COD_NR_D'],
                        'ANNOEMI' => $_POST[$this->nameForm . '_ANNO'],
                        'SETT_IVA' => $numeratori[$i]['SETT_IVA'],
                        'NUMULTDOC' => 0,
                        'NUMDOCBARA' => ($nrd_ann_prec['ANNOEMI'] > 0 ? $nrd_ann_prec['ANNOEMI'] : $numeratori['ANNOEMI']),
                    );
                    if (!$this->CURRENT_RECORD['COD_NR_D']) {
                        continue;
                    }
                    $this->RECORD_INFO = 'Oggetto: ' . $this->CURRENT_RECORD['COD_NR_D'] . " " . $this->CURRENT_RECORD['SETT_IVA'];
                    $this->insertRecord($this->MAIN_DB, $this->TABLE_NAME, $this->CURRENT_RECORD, $this->RECORD_INFO);
                }
            }
        }
    }

}

?>