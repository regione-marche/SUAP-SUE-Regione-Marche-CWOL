<?php

/**
 *  Programma Popolamento sue fabriano LAMA Edilizia
 *
 *
 * @category   programma correzione dati post importazione Fabriano LAMA DB
 * @package    
 * @author     Tania Angeloni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    12.01.2018
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praLAMACorr() {
    $intSuapSbt = new praLAMACorr();
    $intSuapSbt->parseEvent();
    return;
}

class praLAMACorr extends itaModel {

    static $SUBJECT_BASE_LETTERA = array(
        "LAMAPE" => "1",   // 64001  rec 
        "LAMAUC" => "2",  // 34085 REC
        "LAMASAEP" => "3",  // 3883
        "LAMAAMB" => "4",  // 1199
        "LAMASU01" => "5", //121
        "LAMACE" => "6", //2888 
        "PRATICHE-EDILZIE" => "7",
    );
    public $praLib;
    public $PRAM_DB;
    public $LAMA_DB;
    public $LAMA2_DB;
    public $fileLog;
    public $nameForm;
    public $Id;
    public $Pratica;
    public $gesnum_anno = array();
    public $relazioni_2 = array();

    const RESPONSABILE_DEFAULT = '000029';

    function __construct() {
        parent::__construct();
        try {
            /*
             * carico le librerie
             * 
             */
            $this->nameForm = 'praLAMACorr';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');

            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->LAMA_DB = ItaDB::DBOpen('LAMA', "");
            $this->LAMA2_DB = ItaDB::DBOpen('LAMA2', "");
            $this->Id = App::$utente->getKey($this->nameForm . '_Id');
            $this->Pratica = App::$utente->getKey($this->nameForm . '_Pratica');
            $this->gesnum_anno = App::$utente->getKey($this->nameForm . '_gesnum_anno');
            $this->relazioni_2 = App::$utente->getKey($this->nameForm . '_relazioni_2');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
            App::$utente->setKey($this->nameForm . '_Id', $this->Id);
            App::$utente->setKey($this->nameForm . '_Pratica', $this->Pratica);
            App::$utente->setKey($this->nameForm . '_gesnum_anno', $this->gesnum_anno);
            App::$utente->setKey($this->nameForm . '_relazioni_2', $this->relazioni_2);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praLAMACorr_" . time() . ".log";
                $this->scriviLog("Avvio Programma praLAMA Correzioni");
                //Tipo_Correzione
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "vuoto", "0", "");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CREAPASSO", "0", "Crea passo - Campo DataRil Data rilascio PDC");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1", "0", "Dati catastali CATURB e lettera civico pratiche PE part.1");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1_1", "0", "Dati catastali CATURB e lettera civico pratiche PE part.2");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1_2", "0", "Dati catastali CATURB e lettera civico pratiche PE part.3");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1_3", "0", "Dati catastali CATURB e lettera civico pratiche PE part.4");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1_4", "0", "Dati catastali CATURB e lettera civico pratiche PE part.5");
                Out::select($this->nameForm . '_Tipo_CorrezionePE', 1, "CATURB_1_5", "0", "Dati catastali CATURB e lettera civico pratiche PE part.6");

                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "Vuoto", "0", "");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_2", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 2 UC part.1");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_2_1", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 2 UC part.2");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_2_2", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 2 UC part.3");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_3", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 3 SAEP");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_4", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 4 AMB");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_5", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 5 SU01");
                Out::select($this->nameForm . '_Tipo_CorrezioneNNPE', 1, "CATURB_6", "0", "Dati catastali CATURB e lettera civico pratiche NNPE 6 CE");

                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_CorreggiPE':
                        $modulo = $_POST[$this->nameForm . '_Tipo_CorrezionePE'];
                        switch ($modulo) {
                            case "CATURB_1":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',1);
                               // $this->territori($from, $serie, $modulo, $limit);
                                break;
                            case "CATURB_1_1":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',2);
                                break;
                            case "CATURB_1_2":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',3);
                                break;
                            case "CATURB_1_3":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',4);
                                break;
                            case "CATURB_1_4":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',5);
                                break;
                            case "CATURB_1_5":
                                $this->territori('TERRITORIO_PE', '1','LAMAPE',6);
                                break;
                            case "CREAPASSO":
                                $this->creapasso();
                                break;

                            default:
                                Out::msgInfo("Attenzione", "Attenzione selezionare azione corrispondente a serie PE");
                                break;
                        }
                        break;
                    case $this->nameForm . '_CorreggiNNPE':
                        $modulo = $_POST[$this->nameForm . '_Tipo_CorrezioneNNPE'];
                        switch ($modulo) {
                            case "CATURB_2":
                                $this->territori('TERRITORIO_NN_PE', '2','LAMAUC',10);
                                break;
                            case "CATURB_2_1":
                                $this->territori('TERRITORIO_NN_PE', '2','LAMAUC',11);
                                break;
                            case "CATURB_2_2":
                                $this->territori('TERRITORIO_NN_PE', '2','LAMAUC',12);
                                break;
                            case "CATURB_3":
                                $this->territori('TERRITORIO_NN_PE', '3','LAMASAEP');
                                break;
                            case "CATURB_4":
                                $this->territori('TERRITORIO_NN_PE', '4','LAMAAMB');
                                break;
                            case "CATURB_5":
                                $this->territori('TERRITORIO_NN_PE', '5','LAMASU01');
                                break;
                            case "CATURB_6":
                                $this->territori('TERRITORIO_NN_PE', '6','LAMACE');
                                break;
                            default:
                                Out::msgInfo("Attenzione", "Attenzione selezionare azione corrispondente a serie NN PE");
                                break;
                        }
                        break;
                    case $this->nameForm . '_vediLog':
                        $FileLog = 'LOG_IMPORTAZIONE_' . date('His') . '.log';
                        Out::openDocument(utiDownload::getUrl($FileLog, $this->fileLog));
                        break;
                }

                break;

            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    private function scriviLog($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function ricavagesnum($modulo, $anno, $numero, $and = '', $returnrowid = false) {
        $sql_gesnum = "SELECT GESNUM FROM PROGES WHERE SERIECODICE = '$modulo' AND SERIEANNO ='$anno' AND SERIEPROGRESSIVO ='$numero'" . $and;
        // Out::msgInfo("pppp", $proges_rec['GESNUM']);
        if ($returnrowid == true) {
            $sql_gesnum = "SELECT ROWID, GESNUM FROM PROGES WHERE SERIECODICE = '$modulo' AND SERIEANNO ='$anno' AND SERIEPROGRESSIVO ='$numero'" . $and;
        }
        $proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_gesnum, false);
        if (empty($proges_rec['GESNUM'])) {
            // $this->scriviLog($sql_gesnum);
            $this->scriviLog("NON TROVATO GESNUM PER SERIE " . $modulo . " ANNO" . $anno . " NUMERO" . $numero);
            return false;
        }
        if ($returnrowid == true) {
            return $proges_rec;   // per ritornare anhe il rowid per update proges ad aggiorna passo
        }
        return $proges_rec['GESNUM'];
    }

    private function territori($from, $serie,$modulo='',$limit='') {
      // Out::msgInfo($from, $modulo);
     
        // $sql = "SELECT * FROM $from WHERE NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico' ORDER BY Anno ASC LIMIT 5";
        // totale record da elaborare ---> 64001 
        if($from == 'TERRITORIO_NN_PE' && $modulo=='LAMAUC' && $limit == 10){
         //SELECT NomeObj,IdObj,DescrMax FROM TERRITORIO_NN_PE WHERE Modulo = 'LAMAUC' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico')AND Anno ='1997' AND NPrat < '40000' ORDER BY Anno
         $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Modulo = '$modulo' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico')AND Anno ='1997' AND NPrat < '40000' ORDER BY Anno";
        } elseif($from == 'TERRITORIO_NN_PE' && $modulo=='LAMAUC' && $limit == 11){
         $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Modulo = '$modulo' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico')AND Anno ='1997' AND NPrat >= '40000' ORDER BY Anno";
        } elseif($from == 'TERRITORIO_NN_PE' && $modulo=='LAMAUC' && $limit == 12){
         $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Modulo = '$modulo' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico')AND Anno <> '1997' ORDER BY Anno";
        } elseif($from == 'TERRITORIO_NN_PE'){
         $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Modulo = '$modulo' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno";
        }elseif($from == 'TERRITORIO_PE' && $limit == 1){
            //SELECT NomeObj,IdObj,DescrMax FROM TERRITORIO_PE WHERE Anno < '1998' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC 
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno < '1998' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
       //LIMIT 10000
          }elseif($from == 'TERRITORIO_PE' && $limit == 2){
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno >= '1998' AND  Anno < '2002' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
          }elseif($from == 'TERRITORIO_PE' && $limit == 3){
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno >= '2002' AND  Anno < '2007' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
          }elseif($from == 'TERRITORIO_PE' && $limit == 4){
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno >= '2007' AND  Anno < '2011' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
          }elseif($from == 'TERRITORIO_PE' && $limit == 5){
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno >= '2011' AND  Anno < '2015' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
          }elseif($from == 'TERRITORIO_PE' && $limit == 6){
          $sql = "SELECT Anno,NPrat,NomeObj,IdObj,DescrMax FROM $from WHERE Anno >= '2015' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY Anno ASC";
          }
//         Out::msgInfo("sql", $sql);
//          return;
        $ter_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
       $i=0;
        foreach ($ter_tab as $ter_rec) {
            $i++;
            $gesnum = $this->ricavagesnum($serie, $ter_rec['Anno'], $ter_rec['NPrat']);

            if ($ter_rec['NomeObj'] == 'CatUrb') {
                $this->importadaticatastali($ter_rec['IdObj'], 'CATURB', $gesnum); // importa ed elabora i dati catastali FABBRICATO
                continue;
            }
            if ($ter_rec['NomeObj'] == 'AreaCirc') {
                $localita_tab[$ter_rec['IdObj']] = array(
                    'DESNUM' => $gesnum,
                    'DESIND' => $ter_rec['DescrMax'],
                    //'DESCIV' => $this->civico_AreaCic($via_rec['IdObj']),
                    'DESCIV' => '',
                    'DESRUO' => '0014'
                );
                $INDIRIZZO = $localita_tab[$ter_rec['IdObj']]['DESIND'];
            }

            if ($ter_rec['NomeObj'] == 'Civico') {
                $return_civico = $this->civico_AreaCic($ter_rec['IdObj']);
                //Out::msgInfo("return civico",print_r($return_civico,true));
                if (!$return_civico['EspCiv']) {
                    continue;          // se nel civico non è presente la lettera 
                }
                $localita_tab[$return_civico['IdACirc']]['DESCIV'] = $return_civico['NumCiv'] . $return_civico['EspCiv'];
                $anades_tab[] = $localita_tab[$return_civico['IdACirc']];
            } else {

                continue;
                // $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_tab[$ter_rec['IdObj']], $insert_Info); // fai l'insert diretto
            }

            //Out::msgInfo("da inserire rec via 2", print_r($localita_tab,true));
            foreach ($anades_tab as $anades_rec) {
                $sql_civ = "SELECT ROWID FROM ANADES WHERE DESRUO ='0014' AND DESNUM ='$gesnum' AND DESIND = '" . addslashes($INDIRIZZO) . "' AND DESCIV = '" . $return_civico['NumCiv'] . "'";
                $civ_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_civ, false);
                // Out::msgInfo("PRELEVO ROWDI", print_r($sql_civ,true)); 
                // Out::msgInfo("PRELEVO ROWDI", print_r($civ_rec,true)); 
                if ($civ_rec) {
                    $civico = array(
                        'ROWID' => $civ_rec['ROWID'],
                        'DESCIV' => $return_civico['NumCiv'] . $return_civico['EspCiv']
                    );
                    // Out::msgInfo("AGGIORNO CIVICO", print_r($civico, true));
                    $this->updateRecord($this->PRAM_DB, 'ANADES', $civico, $update_Info);  // AGGIORNO IL CIVICO
                }
            }
        }
        Out::msgInfo("ALLINEAMENTO TERMINATO", "ALLINEAMENTO TERMINATO cicli $i");
    }

    private function importadaticatastali($id, $from, $gesnum) {
        $sql = "SELECT FogCat, ParCat, SubCat FROM $from WHERE Id = '$id'";
        $catastali_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        foreach ($catastali_tab as $catastali_rec) {
            $daticatastali = array(
                'PRONUM' => $gesnum,
                'FOGLIO' => str_pad($catastali_rec['FogCat'], 4, "0", STR_PAD_LEFT),
                'PARTICELLA' => str_pad($catastali_rec['ParCat'], 5, "0", STR_PAD_LEFT),
                'SUBALTERNO' => '',
                'NOTE' => "F: " . $catastali_rec['FogCat'] . " P: " . $catastali_rec['ParCat']
            );

            if ($catastali_rec['SubCat']) {
                $daticatastali['SUBALTERNO'] = str_pad($catastali_rec['SubCat'], 4, "0", STR_PAD_LEFT);
                $daticatastali['NOTE'] .= " S: " . $catastali_rec['SubCat'];
            }
            if ($from == 'CATTER') {
                $daticatastali['TIPO'] = 'T';
            } elseif ($from == 'CATURB') {
                $daticatastali['TIPO'] = 'F';
            }
//             Out::msgInfo("catastali rec", print_r($daticatastali, true));
//              return;
            //  CONTROLLO SE IL RECOR è GIA PRESNETE ALTRIMENTI FACCIO L'INSERT
            $sql_praimm = "SELECT ROWID FROM PRAIMM WHERE PRONUM ='$gesnum' AND FOGLIO = '" . $daticatastali['FOGLIO'] . "' AND PARTICELLA = '" . $daticatastali['PARTICELLA'] . "' AND SUBALTERNO = '" . $daticatastali['SUBALTERNO'] . "'";
            //Out::msgInfo("sql catastali", $sql_praimm);
            $rowid_praimm = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_praimm, false);
            // Out::msgInfo("sql catastali", print_r($rowid_praimm['ROWID'],true));
            if (empty($rowid_praimm['ROWID'])) {
                try {
                    // Out::msgInfo("dati catastale query", print_r($daticatastali,true));
                    ItaDB::DBInsert($this->PRAM_DB, 'PRAIMM', 'ROWID', $daticatastali);
                } catch (Exception $ex) {
                    $testo = "Fatal: Errore in inserimento Record Dati Catastali per richiesta" . $gesnum . " " . $ex->getMessage();
                    $this->scriviLog($testo);
                }
                return true;
            }
        }
        $this->scriviLog("CATURB- GIA' PRESNETE: " . $daticatastali['PRONUM'] . " FOGLIO " . $daticatastali['FOGLIO'] . " PARTICELLA " . $daticatastali['PARTICELLA'] . " SUBALTERNO " . $daticatastali['SUBALTERNO']);
        // scrivi log dato già presente
        return true;
    }

    private function civico_AreaCic($id) {

        $sql = "SELECT IdACirc, NumCiv, EspCiv
                FROM CIVICO 
                WHERE Id = '$id' AND NumCiv <> 0";
        //Out::msgInfo("sql", $sql);
        return ItaDB::DBSQLSelect($this->LAMA_DB, $sql, false);
    }

    private function creapasso() {

        //   PER LE PRATICHE PE 
        //TAB. ELENCO PRATICHE se DATARIL non è vuota e diversa da 99991231 creiamo un passo chiamata RILASCIO PERMESSO DI COSTRUIRE    
        // TOT RECORD DA CICLARE    (  9850  )
        $sql_base = "SELECT * 
                     FROM ELENCO_PRATICHE_PE
                     WHERE DataRil <> '' AND DataRil <> '0' AND DataRil <> '-1' AND DataRil <> '99991231'
                     ORDER BY Anno";
//WHERE IdPrati = '178858'
        $lama_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_base, true);
        $index = 0;
        $i = 0;
        foreach ($lama_tab as $lama_rec) {
            $i++;
            $index = $index + 1;
            $and = " AND GESDCH = '" . $lama_rec['DataRil'] . "'";
            $result = $this->ricavagesnum('1', $lama_rec['Anno'], $lama_rec['NPrat'], $and, true);
            $gesnum = $result['GESNUM'];
            if (empty($gesnum)) {
                $this->scriviLog("nessun gesnum ricavato " . $sql_base);
                continue;
            }

            $this->insertpasso($gesnum, $lama_rec['DataRil'], $index, $result['ROWID']);
        }
        Out::msgInfo("ok", "Procedura terminata $i record elaborati");
    }

    private function insertpasso($gesnum, $DataRil, $index, $proges_rowid) {
//   dbo_RD_Eve_Descr   TIPO PASSO
        if ($dati['DeCod'] == 'Eliminata') {
            return false; // NON CARICA I PASSI ELIMINATI
        }
        $seq = 0;
        $propak = $this->praLib->PropakGenerator($gesnum, $index);
        $desc_data = substr($DataRil, -2) . '/' . substr($DataRil, 4, -2) . '/' . substr($DataRil, 0, 4);
        $passo = array(
            'PRONUM' => $gesnum,
            'PROSEQ' => '0',
            'PRORPA' => self::RESPONSABILE_DEFAULT,
            'PROANN' => '',
            'PRODPA' => 'RILASCIO PERMESSO DI COSTRUIRE DEL ' . $desc_data, // nome passo
            'PROINI' => $DataRil, // data apertura passo
            'PROFIN' => $DataRil, // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => '700001', // tipo passo
            'PRODTP' => 'PERMESSO DI COSTRUIRE', // descrizione tipo passo  
        );


        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);

            $this->ordinaPassi($gesnum);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo PDC per richiesta" . $gesnum . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        $this->ordinaPassi($gesnum);
        $this->cleanGesdhc($proges_rowid);
        return true;
    }

    private function ordinaPassi($Pratica) {
        $Propas_tab = $this->praLib->getPropasTab($Pratica, 'PROINI ASC');
        $new_seq = 0;
        if ($Propas_tab) {
            foreach ($Propas_tab as $Propas_rec) {
                $new_seq += 10;
                $Propas_rec['PROSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->PRAM_DB, "PROPAS", "ROWID", $Propas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($exc->getMessage());
                    return false;
                }
            }
        }
    }

    private function cleanGesdhc($rowid) {
        $fascicolo = array(
            "ROWID" => $rowid,
            "GESDCH" => ''
        );

        $this->updateRecord($this->PRAM_DB, 'PROGES', $fascicolo, $update_Info);  // PULISCO DATA CHIUSURA FASCICOLO CHE è STAT AMESSA SU PASSO APPOSITO
    }

}

/*
  TRUNCATE `PROGES`;
  TRUNCATE `PROPAS`;
  TRUNCATE `PRACOM`;
  TRUNCATE `ANADES`;
  TRUNCATE `PASDOC`;
  TRUNCATE `PRAIMM`;
  TRUNCATE `PRODAG`;
  TRUNCATE `PROIMPO`;
  TRUNCATE `NOTE`;
  TRUNCATE `NOTECLAS`;
  TRUNCATE `PROCONCILIAZIONE`;
 * 
 */
?>
