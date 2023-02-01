<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni 
 * @author     Tania Angeloni 
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    12.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/eqAudit.class.php';

class praLibPasso {

    /**
     * Libreria di funzioni Generiche e Utility per Gestione passo b.o. Pratiche
     *
     */
    const PANEL_DATI_PRINCIPALI = "0";
    const PANEL_DESTINATARI = "1";
    const PANEL_ALLEGATI = "2";
    const PANEL_COMUNICAZIONE = "3";
    const PANEL_NOTE = "4";
    const PANEL_DATI_AGGIUNTIVI = "5";
    const PANEL_ARTICOLO = "6";
    const PANEL_ASSEGNAZIONI = "7";
    const PANEL_DOCINTEGRATIVI = "8";
    const PANEL_FIELD_DESCRIZIONE = "DESCRIZIONE";
    const PANEL_FIELD_FILE_XML = "FILE_XML";
    const PANEL_FIELD_SUB_FORM = "SUB_FORM";
    const PANEL_ID_ELEMENT = "ID_ELEMENT";
    const PANEL_ID_FLAG = "ID_FLAG";
    const PANEL_FIELD_DEF_SEQ = "EF_SEQ";
    const PANEL_FIELD_DEF_STATO = "DEF_STATO";
    const PANEL_PROPR_STATO = "STATO";

    public static $PANEL_LIST = array(
        self::PANEL_DATI_PRINCIPALI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Dati Principali",
            self::PANEL_FIELD_FILE_XML => "",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoDatiPrincipali",
            self::PANEL_ID_ELEMENT => "paneDati",
            self::PANEL_FIELD_DEF_SEQ => "00",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_DESTINATARI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Destinatari",
            self::PANEL_FIELD_FILE_XML => "praPanelDestinatari.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoDestinatari",
            self::PANEL_ID_ELEMENT => "paneDestinatari",
            self::PANEL_FIELD_DEF_SEQ => "10",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ALLEGATI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Allegati",
            self::PANEL_FIELD_FILE_XML => "praPanelAllegati.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoCaratteristiche",
            self::PANEL_ID_ELEMENT => "paneCaratteristiche",
            self::PANEL_FIELD_DEF_SEQ => "20",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_COMUNICAZIONE => array(
            self::PANEL_FIELD_DESCRIZIONE => "Comunicazioni",
            self::PANEL_FIELD_FILE_XML => "praPanelComunicazioni.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoComunicazione",
            self::PANEL_ID_ELEMENT => "paneCom",
            self::PANEL_FIELD_DEF_SEQ => "30",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_NOTE => array(
            self::PANEL_FIELD_DESCRIZIONE => "Note",
            self::PANEL_FIELD_FILE_XML => "praPanelNote.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoNote",
            self::PANEL_ID_ELEMENT => "paneNote",
            self::PANEL_FIELD_DEF_SEQ => "40",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_DATI_AGGIUNTIVI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Dati Aggiuntivi",
            self::PANEL_FIELD_FILE_XML => "praPanelDatiAggiuntivi.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoDatiAggiuntivi",
            self::PANEL_ID_ELEMENT => "paneDatiAggiuntivi",
            self::PANEL_FIELD_DEF_SEQ => "50",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ARTICOLO => array(
            self::PANEL_FIELD_DESCRIZIONE => "Articolo",
            self::PANEL_FIELD_FILE_XML => "praArticolo.xml",
            self::PANEL_FIELD_SUB_FORM => "praSubPassoArticoli",
            self::PANEL_ID_ELEMENT => "paneArticoli",
            self::PANEL_ID_FLAG => "PROPAS[PROPART]",
            self::PANEL_FIELD_DEF_SEQ => "60",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_ASSEGNAZIONI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Assegnazioni",
            self::PANEL_FIELD_FILE_XML => "praTabAssegnazioniPassi.xml",
            //self::PANEL_FIELD_SUB_FORM => "proSubTrasmissioni",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_ID_ELEMENT => "paneAssegnazioniPassi",
            self::PANEL_FIELD_DEF_SEQ => "70",
            self::PANEL_FIELD_DEF_STATO => "1"
        ),
        self::PANEL_DOCINTEGRATIVI => array(
            self::PANEL_FIELD_DESCRIZIONE => "Doc. Integrativi",
            self::PANEL_FIELD_FILE_XML => "praPanelDocIntegrativi.xml",
            self::PANEL_FIELD_SUB_FORM => "",
            self::PANEL_FIELD_DEF_SEQ => "80",
            self::PANEL_FIELD_DEF_STATO => "0"
        )
    );
    public $praLib;
    public $arrIdPassiImportati = array();
    private $errCode;
    private $errMessage;

    function __construct() {
        $this->praLib = new praLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
        //json_decode($json, $assoc);
        //json_encode();
    }

    function getArrIdPassiImportati() {
        return $this->arrIdPassiImportati;
    }

    function setArrIdPassiImportati($arrIdPassiImportati) {
        $this->arrIdPassiImportati = $arrIdPassiImportati;
    }

    public function setStatiTabPasso($propas_rec) {
        $valueAnagrafica = self::$PANEL_LIST;
        if ($propas_rec['PROCLT']) {
            $Praclt_rec = $this->praLib->GetPraclt($propas_rec['PROCLT']);
            if ($Praclt_rec['CLTMETAPANEL']) {
                $valueAnagrafica = $this->praLib->decodParametriPasso($Praclt_rec['CLTMETAPANEL'], 'Anagrafica');
            }
        }

        $arrayStatiTab = array();
        foreach ($valueAnagrafica as $key => $panel) {
            if ($panel['DEF_STATO'] == 1) {
                $arrayStatiTab[$key]['Stato'] = "Show";
            } else {
                $arrayStatiTab[$key]['Stato'] = "Hide";
            }
            $arrayStatiTab[$key]['FileXml'] = $panel['FILE_XML'];
            $arrayStatiTab[$key]['Id'] = $panel['ID_ELEMENT'];
            $arrayStatiTab[$key]['IdFlag'] = $panel['ID_FLAG'];
        }

        /*
         * Se non c'è il parametro disabilito il Tab Assegnazioni
         */
        if ($arrayStatiTab[self::PANEL_ASSEGNAZIONI]['Stato'] == "Show") {
            $flagAss = $this->getFlagAssegnazionePasso();
            $arrayStatiTab[self::PANEL_ASSEGNAZIONI]['Stato'] = "Show";
            if (!$flagAss) {
                $arrayStatiTab[self::PANEL_ASSEGNAZIONI]['Stato'] = "Hide";
            }
        }


        /*
         * Se passo FO disabilito Tab destinatari
         */
        if ($arrayStatiTab[self::PANEL_DESTINATARI]['Stato'] == "Show") {
            if ($propas_rec['PROCOM'] == 1 || $propas_rec['PROPUB'] == 0) {
                $arrayStatiTab[self::PANEL_DESTINATARI]['Stato'] = "Show";
            } else if ($propas_rec['PROPUB'] == 1) {
                $arrayStatiTab[self::PANEL_DESTINATARI]['Stato'] = "Hide";
            }
        }

        /*
         * Se passo FO disabilito Tab Comunicazioni
         */
        if ($arrayStatiTab[self::PANEL_COMUNICAZIONE]['Stato'] == "Show") {
            if ($propas_rec['PROCOM'] == 1 || $propas_rec['PROPUB'] == 0) {
                $arrayStatiTab[self::PANEL_COMUNICAZIONE]['Stato'] = "Show";
            } else if ($propas_rec['PROPUB'] == 1) {
                $arrayStatiTab[self::PANEL_COMUNICAZIONE]['Stato'] = "Hide";
            }
        }

        /*
         * Se c'è il flag articolo Abilito il tab
         */
        if ($arrayStatiTab[self::PANEL_ARTICOLO]['Stato'] == "Show") {
            if ($propas_rec['PROPART'] == 1) {
                $arrayStatiTab[self::PANEL_ARTICOLO]['Stato'] = "Show";
            } else {
                $arrayStatiTab[self::PANEL_ARTICOLO]['Stato'] = "Hide";
            }
            $arrayStatiTab[self::PANEL_ARTICOLO]['Flag'] = "On";
        } else {
            $arrayStatiTab[self::PANEL_ARTICOLO]['Flag'] = "Off";
        }
        return $arrayStatiTab;
    }

    public function getFlagAssegnazionePasso() {
        $flagAssegnazioniPasso = false;
        $Filent_Rec_TabAss = $this->praLib->GetFilent(41);
        if ($Filent_Rec_TabAss['FILVAL'] == 1) {
            $flagAssegnazioniPasso = true;
        }
        return $flagAssegnazioniPasso;
    }

    public function getFunzionePassoBO($propas_rec) {
        $returnFunzione = array('FUNZIONE' => false, 'DATA' => array());

        $praclt_rec = $this->praLib->GetPraclt($propas_rec['PROCLT']);

        if ($praclt_rec['CLTOPE']) {
            $returnFunzione['FUNZIONE'] = $praclt_rec['CLTOPE'];
        }

        if (!$praclt_rec['CLTMETA']) {
            return $returnFunzione;
        }

        $metaValue = unserialize($praclt_rec['CLTMETA']);
        if (!$metaValue['METAOPE']) {
            return $returnFunzione;
        }

        $returnFunzione['DATA'] = $metaValue['METAOPE'];

        return $returnFunzione;
    }

    public function getFunzionePassoFO($propas_rec) {
        $returnFunzione = array('FUNZIONE' => false, 'DATA' => array());

        $praclt_rec = $this->praLib->GetPraclt($propas_rec['PROCLT']);

        if ($praclt_rec['CLTOPEFO']) {
            $returnFunzione['FUNZIONE'] = $praclt_rec['CLTOPEFO'];
        }

        if (!$praclt_rec['CLTMETA']) {
            return $returnFunzione;
        }

        $metaValue = unserialize($praclt_rec['CLTMETA']);
        if (!$metaValue['METAOPEFO']) {
            return $returnFunzione;
        }

        $returnFunzione['DATA'] = $metaValue['METAOPEFO'];

        return $returnFunzione;
    }

    /**
     * Aggiorna la tabella PROPASFATTI 
     * @param array $dati array nella forma
     *              array(
     *                  'PASSO_ORIGINE' => $idPassoOrigine,             //PROPAK del passo di origine
     *                  'PASSO_DESTINAZIONE => $idPassoDestinazione,    //PROPAK del passo di destinazione
     *                  'EXTRA_PARAMS' => array()                       //facoltativo
     *              )
     * @return boolean
     */
    public function aggiornaPassiFatti($dati){
        $passoDestinazione = $this->praLib->GetPropas($dati['PASSO_DESTINAZIONE'], 'propak');

        $propasFatti_rec = Array();
        $propasFatti_rec['PRONUM'] = $passoDestinazione['PRONUM'];
        $propasFatti_rec['PROPRO'] = $passoDestinazione['PROPRO'];
        $propasFatti_rec['PROPAK'] = $dati['PASSO_ORIGINE'];
        $propasFatti_rec['PROSPA'] = $dati['PASSO_DESTINAZIONE'];
        
        if (!ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PROPASFATTI', 'ROWID', $propasFatti_rec)){
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in inserimento su PROPASFATTI');
            return false;
        }
        return true;
        
//        $propasFatti_rec = $this->praLib->GetPropasFatti($dati['PASSO_ORIGINE'], 'propak');
//        if (!$propasFatti_rec) {
//            $passoAttuale = $this->praLib->GetPropas($dati['PASSO_ORIGINE'], 'propak');
//            
//            $propasFatti_rec = Array();
//            $propasFatti_rec['PRONUM'] = $passoAttuale['PRONUM'];
//            $propasFatti_rec['PROPRO'] = $passoAttuale['PROPRO'];
//            $propasFatti_rec['PROPAK'] = $passoAttuale['PROPAK'];
//            $propasFatti_rec['PROSPA'] = $dati['PASSO_DESTINAZIONE'];
//
////            $insert_info = 'Inserito record PROPASFATTI con PROPAK: ' . $propasFatti_rec['PROPAK'];
//
//            if (!ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PROPASFATTI', 'ROWID', $propasFatti_rec)){
//                $this->setErrCode(-1);
//                $this->setErrMessage('Errore in inserimento su PROPASFATTI');
//                return false;
//            }
//        }
//        else {
//            $propasFatti_rec['PROSPA'] = $dati['PASSO_DESTINAZIONE'];
//
////            $update_info = 'Salva passo fatto: ' . $propasFatti_rec['PROPAK'] . ' con passo successivo' . $propasFatti_rec['PROSPA'];
//
////            if (!$this->updateRecord($this->praLib->getPRAMDB(), 'PROPASFATTI', $propasFatti_rec, $update_info, 'ROW_ID')) {
//            if(!ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PROPASFATTI', 'ROWID', $propasFatti_rec)){
//                $this->setErrCode(-1);
//                $this->setErrMessage('Errore in aggiornamento su PROPASFATTI');
//                return false;
//            }
//        }
//        return true;
    }

     /* 
     * @param type $arrayPassi --> Array con elenco dei passi da cancellare [$this->passiSel]
     * @param type $idAnapra  --> ROWID della tabelle ANAPRA [$_POST[$this->nameForm . '_ANAPRA']['ROWID']]
     * @param type $pranum    --> Numero del procedimento Amministrativo
     * @param type $arrayPassiProc   --> Tutti i passi del procedimento corrente 
     * 
     * @return boolean
     */
    public function cancellaPassi($arrayPassi, $idAnapra, $pranum, $arrayPassiProc) {

//        $delete_Info = 'Oggetto: Cancellazione passi selezionati procedimento ' . $this->currPranum;
//        Out::msgInfo("Passi da cancellare", print_r($arrayPassi, true));
//        return;
        foreach ($arrayPassi as $key => $cancPasso) {

            /*
             * Cancello dati aggiuntivi
             */
            if (!$this->deleteRecordItedag($cancPasso['ITEKEY'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in cancellazione dati agg passo " . $cancPasso['ITEKEY']);
                return false;
            }

            /*
             * Cancello destinatari
             */
            if (!$this->deleteRecordItedest($cancPasso['ITEKEY'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in cancellazione destinatari passo " . $cancPasso['ITEKEY']);
                return false;
            }

            /*
             * Cancello le Azioni del Passo
             */
            if (!$this->deleteRecordPraazioni($cancPasso['ITEKEY'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore cancellazione azione passo " . $cancPasso['ITEKEY']);
                return false;
            }

            /*
             * Cancello i controlli (ITECONTROLLI) del Passo
             */
            if (!$this->deleteRecordIteControlli($cancPasso['ITEKEY'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in cancellazione controlli del passo " . $cancPasso['ITEKEY']);
                return false;
            }

            /*
             * Cancello passo (ITEPAS)
             */
            $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEPAS', 'ROWID', $cancPasso['ROWID']);
            if ($nrow == 0) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella cancellazione del passo " . $cancPasso['ITEKEY']);
                return false;
            }
        }

        $this->praLib->ordinaPassiProc($pranum);


        $errCanc = $this->controlloPassidaCancellare($arrayPassi, $arrayPassiProc);

        if ($errCanc) {
            foreach ($errCanc as $value) {
                $itepas_rec = $this->praLib->GetItepas($value['PASSO_ROWID'], 'rowid');
                $itepas_rec['ITEVPA'] = '';
                $itepas_rec['ITEVPN'] = '';

//                $update_Info = "Oggetto: Cancellazione passo selezionato con chiave " . $itepas_rec['ITEKEY'];
                $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $itepas_rec);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nel sistemare il collegamento tra i passi");

                    return false;
                }
            }
        }



        $Anapra_rec_tmp = array('ROWID' => $idAnapra);
        $Anapra_rec = $this->praLib->SetMarcaturaProcedimento($Anapra_rec_tmp);
//        $update_Info = 'Oggetto: Aggiornamento marcatura procedimento n. ' . $this->currPranum;
        $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ANAPRA", "ROWID", $Anapra_rec);
        if ($nrow == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento Marcatura Procedimento ");

            return false;
        }
    }

    private function deleteRecordItedag($codice) {
        $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $codice . "'";
        $itedag_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        if ($itedag_tab) {
            foreach ($itedag_tab as $itedag_rec) {
//                $delete_Info = "Oggetto: Cancellazione dato aggiuntivo " . $itedag_rec['ITDKEY'] . " - Chiave: " . $itedag_rec['ITEKEY'];
                $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEDAG', 'ROWID', $itedag_rec['ROWID']);
                if ($nrow == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function deleteRecordPraazioni($codice) {
        $sql = "SELECT * FROM PRAAZIONI WHERE ITEKEY = '" . $codice . "'";
        $praazioni_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        if ($praazioni_tab) {
            foreach ($praazioni_tab as $praazioni_rec) {
//                $delete_Info = "Oggetto: Cancellazione Azione " . $praazioni_rec['CODICEAZIONE'] . " - Chiave: " . $praazioni_rec['ITEKEY'];
                $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'PRAAZIONI', 'ROWID', $praazioni_rec['ROWID']);
                if ($nrow == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    public function deleteRecordItedest($codice, $tipo = "codice") {
        $itedest_tab = $this->praLib->GetItedest($codice, $tipo);
        if ($itedest_tab) {
            foreach ($itedest_tab as $itedest_rec) {
//                $delete_Info = "Oggetto: Cancellazione detinatari " . $itedest_rec['ITEKEY'];
                $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEDEST', 'ROW_ID', $itedest_rec['ROW_ID']);
                if ($nrow == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function deleteRecordIteControlli($codice) {
        $Itecontrolli_tab = $this->praLib->GetItecontrolli($codice);
        if ($Itecontrolli_tab) {

            foreach ($Itecontrolli_tab as $Itecontrolli_rec) {
                $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITECONTROLLI', 'ROWID', $Itecontrolli_rec['ROWID']);
                if ($nrow == 0) {
                    return false;
                }
            }
        }

        return true;
    }

    public function controlloPassidaCancellare($arrayPassi, $arrayPassiProc) {
        $errCanc = array();
        foreach ($arrayPassi as $key => $cancPasso) {
// per ogni passo da cancellare ....
            foreach ($arrayPassiProc as $key => $passo) {    // per ogni passo presente in tabella .....
                if ($cancPasso['ITEKEY'] == $passo['ITEVPA'] || $cancPasso['ITEKEY'] == $passo['ITEVPN']) {     // se ITEKEY da cancellare è presente in una domanda ....
                    $daCancellare = 0;
                    foreach ($arrayPassi as $key => $ctrCancPasso) {    // controllo se l'ITEKEY della domanda è tra i passi da cancellare ....
                        if ($passo['ITEKEY'] == $ctrCancPasso['ITEKEY']) {
                            $daCancellare = 1;
                        }
                    }
                    if ($daCancellare == 0) {       // se non è nell'elenco dei cancellandi ....  segnalazione
                        $errCanc[] = array(
                            'PASSO_ROWID' => $passo['ROWID'],
                            'PASSO_CANC' => $cancPasso['ITESEQ'],
                            'PASSO_DOMA' => $passo['ITESEQ']
                        );
                    }
                }
            }
        }
        return $errCanc;
    }

    /**
     * 
     * @param type $pranum  --> Numero del procedimento
     * @param type $XMLpassi  
     * @param type $partiDa
     */
    public function importaPassiXML($pranum, $XMLpassi, $partiDa) {
        $eqAudit = new eqAudit();
        $this->arrIdPassiImportati = array();
        if (file_exists($XMLpassi)) {
            if (pathinfo($XMLpassi, PATHINFO_EXTENSION) == "xml") {
                $xmlObj = new QXML;
                $xmlObj->setXmlFromFile($XMLpassi);
                $arrayXml = $xmlObj->toArray($xmlObj->asObject());
                if ($arrayXml['ITEPAS']) {
                    if ($this->insertRecordItepas($pranum, $partiDa)) {
//  Separo i due array
                        $arrayItepas = $arrayXml['ITEPAS'];
                        $arrayItedag = $arrayXml['ITEDAG'];
                        $arrayPraazioni = $arrayXml['PRAAZIONI'];
                        $arrayItecontrolli = $arrayXml['ITECONTROLLI'];
                        $arrayItedest = $arrayXml['ITEDEST'];

                        $tmp_arrayItepas = $arrayItepas;
                        if (!$tmp_arrayItepas[0]) {
                            $arrayItepas = array();
                            $arrayItepas[0] = $tmp_arrayItepas;
                        }

                        $tmp_arrayItedag = $arrayItedag;
                        if (!$tmp_arrayItedag[0]) {
                            $arrayItedag = array();
                            $arrayItedag[0] = $tmp_arrayItedag;
                        }

                        $tmp_arrayPraazioni = $arrayPraazioni;
                        if (!$tmp_arrayPraazioni[0]) {
                            $arrayPraazioni = array();
                            $arrayPraazioni[0] = $tmp_arrayPraazioni;
                        }

                        $tmp_arrayItecontrolli = $arrayItecontrolli;
                        if (!$tmp_arrayItecontrolli[0]) {
                            $arrayItecontrolli = array();
                            $arrayItecontrolli[0] = $tmp_arrayItecontrolli;
                        }

                        $tmp_arrayItedest = $arrayItedest;
                        if (!$tmp_arrayItedest[0]) {
                            $arrayItedest = array();
                            $arrayItedest[0] = $tmp_arrayItedest;
                        }


                        if ($arrayItepas[0]) {
//  Sostituisco il veccio valore di ITECOD con il nuovo codice
                            foreach ($arrayItepas as $key => $passo) {
                                foreach ($passo as $key1 => $campo) {
                                    $arrayItepas[$key]['ITECOD']['@textNode'] = $pranum;
                                }
                            }
                            foreach ($arrayItedag as $key => $passo) {
                                foreach ($passo as $key1 => $campo) {
                                    $arrayItedag[$key]['ITECOD']['@textNode'] = $pranum;
                                }
                            }
                            foreach ($arrayPraazioni as $key => $azione) {
                                foreach ($azione as $key1 => $campo) {
                                    $arrayPraazioni[$key]['PRANUM']['@textNode'] = $pranum;
                                }
                            }
                            foreach ($arrayItedest as $key => $dest) {
                                foreach ($dest as $key1 => $campo) {
                                    $arrayItedest[$key]['ITECOD']['@textNode'] = $pranum;
                                }
                            }

//  Calcolo per ogni ITEPAS la nuova chiave del passo (ITEKEY) ed assegno nuova sequenza
                            $salvaItekey = array();
                            $indice = 0;
                            foreach ($arrayItepas as $key => $itepas) {
                                $oldItekey = $itepas['ITEKEY']['@textNode'];
                                $salvaItekey[$indice]['OLD'] = $oldItekey;
                                $itepas['ITEKEY']['@textNode'] = $this->praLib->keyGenerator($pranum);
                                $salvaItekey[$indice]['NEW'] = $itepas['ITEKEY']['@textNode'];
                                $indice = $indice + 1;
                                $partiDa = $partiDa + 10;
                                $itepas['ITESEQ']['@textNode'] = $partiDa;
                                $arrayItepas[$key] = $itepas;
//  Sostituisco per ogni ITEDAG il vecchio ITEKEY con quello nuovo del passo
                                foreach ($arrayItedag as $key1 => $itedag) {
                                    if ($itedag['ITEKEY']['@textNode'] == $oldItekey) {
                                        $itedag['ITEKEY']['@textNode'] = $arrayItepas[$key]['ITEKEY']['@textNode'];
                                        $arrayItedag[$key1] = $itedag;
                                    }
                                }
//  Sostituisco per ogni PRAAZIONE il vecchio ITEKEY con quello nuovo del passo
                                foreach ($arrayPraazioni as $key1 => $azione) {
                                    if ($azione['ITEKEY']['@textNode'] == $oldItekey) {
                                        $azione['ITEKEY']['@textNode'] = $arrayItepas[$key]['ITEKEY']['@textNode'];
                                        $arrayPraazioni[$key1] = $azione;
                                    }
                                }
//  Sostituisco per ogni ITECONTROLLI il vecchio ITEKEY con quello nuovo del passo
                                foreach ($arrayItecontrolli as $key1 => $controllo) {
                                    if ($controllo['ITEKEY']['@textNode'] == $oldItekey) {
                                        $controllo['ITEKEY']['@textNode'] = $arrayItepas[$key]['ITEKEY']['@textNode'];
                                        $arrayItecontrolli[$key1] = $controllo;
                                    }
                                }
//  Sostituisco per ogni ITEDEST il vecchio ITEKEY con quello nuovo del passo
                                foreach ($arrayItedest as $key1 => $dest) {
                                    if ($dest['ITEKEY']['@textNode'] == $oldItekey) {
                                        $dest['ITEKEY']['@textNode'] = $arrayItepas[$key]['ITEKEY']['@textNode'];
                                        $arrayItedest[$key1] = $dest;
                                    }
                                }
                            }
                            foreach ($arrayItepas as $key => $itepas) {
                                if ($itepas['ITEVPA']['@textNode']) {
                                    foreach ($salvaItekey as $oldItekey) {
                                        if ($oldItekey['OLD'] == $itepas['ITEVPA']['@textNode']) {
                                            $arrayItepas[$key]['ITEVPA']['@textNode'] = $oldItekey['NEW'];
                                            break;
                                        }
                                    }
                                }
                                if ($itepas['ITEVPN']['@textNode']) {
                                    foreach ($salvaItekey as $oldItekey) {
                                        if ($oldItekey['OLD'] == $itepas['ITEVPN']['@textNode']) {
                                            $arrayItepas[$key]['ITEVPN']['@textNode'] = $oldItekey['NEW'];
                                            break;
                                        }
                                    }
                                }
                                if ($itepas['ITECTP']['@textNode']) {
                                    foreach ($salvaItekey as $oldItekey) {
                                        if ($oldItekey['OLD'] == $itepas['ITECTP']['@textNode']) {
                                            $arrayItepas[$key]['ITECTP']['@textNode'] = $oldItekey['NEW'];
                                            break;
                                        }
                                    }
                                }
                                $arrayItepas[$key]["ITEATE"]['@textNode'] = $this->sistemaITEATE($itepas['ITEATE']['@textNode'], $itepas['ITEKEY']['@textNode']);
                            }
//  Registro su ITEPAS
                            $insert_Info = 'Oggetto: Importazione passi della procedimento' . $pranum;
                            foreach ($arrayItepas as $itepasRec) {
                                $rec = array();
                                foreach ($itepasRec as $key => $value) {
                                    if (strpos($key, '@') === false) {      // escludo gli attributi del ramo
                                        $rec[$key] = $value['@textNode'];
                                    }
                                }
                                $rec['ROWID'] = 0;
                                $Itepas_rec_utf8_decode = itaLib::utf8_decode_array($rec);

                                $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ITEPAS', 'ROWID', $Itepas_rec_utf8_decode);
                                if ($nRows == -1) {
                                    $this->setErrCode(-1);
                                    $this->setErrMessage("Errore nell'importazione dei passi");
                                    return false;
                                }

                                $this->arrIdPassiImportati[] = $this->praLib->getPRAMDB()->getLastId();
//                                if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $Itepas_rec_utf8_decode, $insert_Info)) {
//                                    Out::msgStop("Errore", 'Importazione passi non terminata.');
//                                    break;
//                                }
                            }

                            $eqAudit->logEqEvent($this, array(
                                'Operazione' => eqAudit::OP_INS_RECORD,
                                'DB' => $this->praLib->getPRAMDB()->getDB(),
                                'DSet' => 'ITEPAS',
                                'Estremi' => "Inserimento Passi per il procedimento " . $pranum,
                            ));
                        }
//  Registro su ITEDAG
                        $insert_Info = 'Oggetto: Importazione dati aggiuntivi del procediemnto ' . $pranum;
                        foreach ($arrayItedag as $itedagRec) {
                            $rec = array();
                            foreach ($itedagRec as $key => $value) {
                                if (strpos($key, '@') === false) {      // escludo gli attributi del ramo
                                    $rec[$key] = $value['@textNode'];
                                }
                            }
                            $rec['ROWID'] = 0;
                            $Itedag_rec_utf8_decode = itaLib::utf8_decode_array($rec);

                            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ITEDAG', 'ROWID', $Itedag_rec_utf8_decode);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Errore nell'importazione dei dati aggiuntivi.");
                                return false;
                            }

//                            if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $Itedag_rec_utf8_decode, $insert_Info)) {
//                                Out::msgStop("Errore", 'Importazione passi non terminata.');
//                                break;
//                            }
                        }
//  Registro su PRAAZIONI
                        $insert_Info = 'Oggetto: Importazione Azioni del procediemnto ' . $pranum;
                        foreach ($arrayPraazioni as $azione) {
                            $rec = array();
                            foreach ($azione as $key => $value) {
                                if (strpos($key, '@') === false) {      // escludo gli attributi del ramo
                                    $rec[$key] = $value['@textNode'];
                                }
                            }
                            $rec['ROWID'] = 0;
                            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAAZIONI', 'ROWID', $rec);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Errore nell'imporazione delle azioni");
                                return false;
                            }

//                            if (!$this->insertRecord($this->PRAM_DB, 'PRAAZIONI', $rec, $insert_Info)) {
//                                Out::msgStop("Errore", "Importazione passi non terminata. Errore nell'importazione delle azioni");
//                                break;
//                            }
                        }
//  Registro su ITECONTROLLI
                        $insert_Info = 'Oggetto: Importazione Controlli del procedimento ' . $pranum;
                        foreach ($arrayItecontrolli as $controllo) {
                            $rec = array();
                            foreach ($controllo as $key => $value) {
                                if (strpos($key, '@') === false) {      // escludo gli attributi del ramo
                                    $rec[$key] = $value['@textNode'];
                                }
                            }
                            $rec['ROWID'] = 0;

                            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ITECONTROLLI', 'ROWID', $rec);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Errore nell'importazione dei controlli");
                                return false;
                            }

//                            
//                            if (!$this->insertRecord($this->PRAM_DB, 'ITECONTROLLI', $rec, $insert_Info)) {
//                                Out::msgStop("Errore", "Importazione passi non terminata. Errore nell'importazione dei controlli");
//                                break;
//                            }
                        }

//  Registro su ITEDEST
                        $insert_Info = 'Oggetto: Importazione destinatari del procedimento ' . $pranum;
                        foreach ($arrayItedest as $controllo) {
                            $rec = array();
                            foreach ($controllo as $key => $value) {
                                if (strpos($key, '@') === false) {      // escludo gli attributi del ramo
                                    $rec[$key] = $value['@textNode'];
                                }
                            }
                            $rec['ROW_ID'] = 0;

                            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ITEDEST', 'ROW_ID', $rec);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Errore nell'importazione dei destinatari");
                                return false;
                            }


//                            if (!$this->insertRecord($this->PRAM_DB, 'ITEDEST', $rec, $insert_Info)) {
//                                Out::msgStop("Errore", "Importazione passi non terminata. Errore nell'importazione dei destinatari");
//                                break;
//                            }
                        }
                        $this->praLib->ordinaPassiProc($pranum);
//                        $this->caricaPassi($pranum);
                    } else {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Procedura di importazione passi interrotta per errore nello spostamento sequenza.");
                        return false;
                    }
                } else {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File di importazione passi non è conforme.");
                    return false;
                }
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage("File di importazione passi non è un xml.");
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Procedura di importazione passi interrotta per mancanza del file.");
            return false;
        }

        return true;
    }

    private function insertRecordItepas($pranum, $partiDa) {
        if (!$partiDa == 0) {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $pranum . "' AND ITESEQ > '" . $partiDa . "' ORDER BY ITESEQ";
            $itepas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            if ($itepas_tab) {
                foreach ($itepas_tab as $itepas_rec) {
                    $itepas_rec['ITESEQ'] = $itepas_rec['ITESEQ'] + 500;
//                    $update_Info = 'Oggetto: Aggiornamento passo con chiave ' . $itepas_rec['ITEKEY'];
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $itepas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function sistemaITEATE($iteate, $newItekey) {
        if ($iteate) {
            $arrIteate = unserialize($iteate);
            //if ($arrIteate[0]['CAMPO'] == "LISTINO.TARIFFA_$oldItekey") {
            if (strpos($arrIteate[0]['CAMPO'], "LISTINO.TARIFFA_") !== false) {
                $arrIteate[0]['CAMPO'] = "LISTINO.TARIFFA_$newItekey";
                return serialize($arrIteate);
            } else {
                return $iteate;
            }
        }
    }

}
