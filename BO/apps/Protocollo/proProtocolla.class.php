<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    07.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';

class proProtocolla extends itaModel {

    private $PROT_DB;
    private $status;
    private $message;
    private $title;
    private $proLib;
    private $proLibSdi;
    private $risultatoRitorno;
    private $anaproCreato = array();

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibSdi = new proLibSdi();
        $this->PROT_DB = $this->proLib->getPROTDB();
    }

    function getmessage() {
        return $this->message;
    }

    function getTitle() {
        return $this->title;
    }

    public function getRisultatoRitorno() {
        return $this->risultatoRitorno;
    }

    public function setRisultatoRitorno($risultatoRitorno) {
        $this->risultatoRitorno = $risultatoRitorno;
    }

    public function getAnaproCreato() {
        return $this->anaproCreato;
    }

    private function lockAnaent($rowid) {
        $retLock = ItaDB::DBLock($this->PROT_DB, "ANAENT", $rowid, "", 20);
        if ($retLock['status'] != 0) {
            //Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVI non Riuscito.');
            return false;
        }
        return $retLock;
    }

    private function unlockAnaent($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            //Out::msgStop('Errore', 'Sblocco Tabella PROGRESSIVI non Riuscito.');
        }
    }

    private function lockAnaproI() {
        $retLock = ItaDB::DBLock($this->PROT_DB, "ANAPRO", 'I', "", 20);
        if ($retLock['status'] != 0) {
            //Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVI non Riuscito.');
            return false;
        }
        return $retLock;
    }

    private function unlockAnaproI($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            
        }
    }

    function PrenotaDocumentoFascicolo($modo, $workYear) {
        $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE PRONUM LIKE  '$workYear%' AND PROPAR ='F'";
        try {
            $maxRepertorio_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if (!$maxRepertorio_rec['PRONUM']) {
                $codice = $workYear . "000001";
            } else {
                $codice = $maxRepertorio_rec['PRONUM'] + 1;
            }
        } catch (Exception $exc) {
            return false;
        }
        return $codice;
    }

    function PrenotaDocumentoIndice($modo, $workYear) {
        $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE PRONUM LIKE  '$workYear%' AND (PROPAR='I' OR PROPAR='IA')"; // Lasciato apposta per segreteria non ancora aggiornata.
        try {
            $maxRepertorio_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if (!$maxRepertorio_rec['PRONUM']) {
                $codice = $workYear . "000001";
            } else {
                $codice = $maxRepertorio_rec['PRONUM'] + 1;
            }
        } catch (Exception $exc) {
            return false;
        }
        return $codice;
    }

    function PrenotaDocumentoAzione($modo, $workYear, $tipo) {
        $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE PRONUM LIKE  '$workYear%' AND PROPAR ='$tipo'";
        try {
            $maxProgressivo_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if (!$maxProgressivo_rec['PRONUM']) {
                $codice = $workYear . "000001";
            } else {
                $codice = $maxProgressivo_rec['PRONUM'] + 1;
            }
        } catch (Exception $exc) {
            return false;
        }
        return $codice;
    }

    public function aggiungiAllegati($tipo, $motivo, $dati, $idPronum) {
        $anaproNew_rec = $this->proLib->GetAnapro($idPronum, 'codice', $dati->tipoProt);
        if (!$anaproNew_rec) {
            return false;
        }
        /* Controllo allegato al protocollo */
        if ($dati->proArriAlle) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
            $proLibAllegati = new proLibAllegati();
            if (!$proLibAllegati->ControlloAllegatiPreProtocollo($dati->proArriAlle)) {
                $this->title = "Registra Protocollo";
                $this->message = " - Errore allegati. " . $proLibAllegati->getErrMessage();
                return false;
            }
        }

        $gestiti = $this->GestioneAllegati($anaproNew_rec['PRONUM'], $dati, $anaproNew_rec);
        if (!$gestiti)
            return false;
        return $anaproNew_rec['ROWID'];
    }

    public function registraPro($tipo, $motivo, $dati) {
        $this->status = "";
        $this->message = "";
        $this->title = "";
        $modificato = false;
        $anaogg_rec = array();
        $anapro_rec = $dati->anapro_rec;
        /* Controllo degli eventuali allegati al protocollo */
        if ($dati->proArriAlle) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
            $proLibAllegati = new proLibAllegati();
            if (!$proLibAllegati->ControlloAllegatiPreProtocollo($dati->proArriAlle)) {
                $this->title = "Registra Protocollo";
                $this->message = "Errore riscontrato negli allegati. " . $proLibAllegati->getErrMessage();
                return false;
            }
        }

        if ($tipo == "Aggiungi") {
            if ($anapro_rec['PROORA'] == '') {
                $anapro_rec['PROORA'] = date('H:i:s');
            }
            if ($dati->tipoProt == 'C') {
                /* Se parametro Doc Formali Unico Progressivo Attivo :
                 * Blocco il progressivo generale (A/P)-> ANAENT(1)
                 * Altrimenti ANAENT(23) prog a parte per i Doc Formali
                 */
                $anaent_48 = $this->proLib->GetAnaent('48');
                if ($anaent_48['ENTDE4']) {
                    $retLock = $this->lockAnaent("1");
                    if (!$retLock) {
                        $this->title = "Registra Protocollo";
                        $this->message = "Impossibile accedere in modo esclusivo alla tabella progressivi";
                        return false;
                    }
                } else {
                    $retLock = $this->lockAnaent("23");
                    if (!$retLock) {
                        $this->title = "Registra Protocollo";
                        $this->message = "Impossibile accedere in modo esclusivo alla tabella progressivi";
                        return false;
                    }
                }
                $risultato = $this->proLib->prenotaProtocollo($anapro_rec['PRODAR'], "LEGGI", $dati->workYear, 'C');
                $newPronum = $risultato['pronum'];
                if ($newPronum == "Error") {
                    $this->title = $risultato['errTitolo'];
                    $this->message = $risultato['errMsg'];
                    $this->unlockAnaent($retLock);
                    return false;
                }
            } else if ($dati->tipoProt == 'I') {
                $retLock = $this->lockAnaproI();
                if (!$retLock) {
                    $this->title = "Registra Protocollo Anapro I";
                    $this->message = "Impossibile accedere in modo esclusivo alla tabella progressivi";
                    return false;
                }
                $newPronum = $this->PrenotaDocumentoIndice('LEGGI', date('Y'));
                if ($newPronum == "Error") {
                    $this->unlockAnaproI($retLock);
                    return false;
                }
            } else {
                $retLock = $this->lockAnaent("1");
                if (!$retLock) {
                    $this->title = "Registra Protocollo";
                    $this->message = "Impossibile accedere in modo esclusivo alla tabella progressivi";
                    return false;
                }
                $risultato = $this->proLib->prenotaProtocollo($anapro_rec['PRODAR'], "LEGGI", $dati->workYear);
                $newPronum = $risultato['pronum'];
                if ($newPronum == "Error") {
                    $this->title = $risultato['errTitolo'];
                    $this->message = $risultato['errMsg'];
                    $this->unlockAnaent($retLock);
                    return false;
                }
            }
            if ($newPronum == "Error") {
                $this->unlockAnaent($retLock);
                return false;
            }
            $anapro_rec['PRONUM'] = $newPronum;
            $anapro_rec['PROPAR'] = $dati->tipoProt;
        } else {
            if ($dati->tipoProt == 'A' || $dati->tipoProt == 'C') {
                $anaproEsistente_rec = $this->proLib->GetAnapro($anapro_rec['ROWID'], 'rowid');
                $arcite_tab = $this->proLib->GetArcite($anaproEsistente_rec['PRONUM'], 'codice', true, $dati->tipoProt);
                if ($arcite_tab) {
                    foreach ($arcite_tab as $arcite_rec) {
                        if ($arcite_rec['ITEDLE'] != '') {
                            //$modificato = true;
                        }
                    }
                }
                if ($modificato) {
                    if ($dati->tipoProt == 'A') {
                        $this->title = "ATTENZIONE!";
                        $this->message = "Il Protocollo non può essere modificato perché è stato Gestito.<br><br>Inserimento Interrotto!";
                    } else if ($dati->tipoProt == 'C') {
                        $this->title = "ATTENZIONE!";
                        $this->message = "Il Documento Formale non può essere modificata perché è stata Gestita.<br><br>Inserimento Interrotto!";
                    }
                    return "Error";
                }
            }
            $this->registraSave($motivo, $anapro_rec['ROWID']);
        }
        $anapro_rec['PROCCA'] = $anapro_rec['PROCAT'] . $dati->Clacod;
        $anapro_rec['PROCCF'] = $anapro_rec['PROCCA'] . $dati->Fascod;
        $anapro_rec['PROCHI'] = $anapro_rec['PROCCF'] . $anapro_rec['PROARG'];
        $anapro_rec['PRONRA'] = $anapro_rec['PRODAR']; // INTEGRARE CON OLD_DAT
        if ($anapro_rec['PRODAA'] == '') {
            $anapro_rec['PRODAA'] = $anapro_rec['PRODAR'];
        }
        $anapro_rec['PROAGG'] = $anaproEsistente_rec['PRONUM'];
        $anapro_rec['PROPRE'] = $dati->Propre2 * 1000000 + $dati->Propre1;
        $anapro_rec['PROPARPRE'] = $dati->Parpre;
        if ($anapro_rec['PROPRE'] != '') {
            if ($dati->tipoProt == 'C') {
                $anaproPrec_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', 'C');
            } else {
                $anaproPrec_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', '', " (PROPAR='A' OR PROPAR='P')"); // Qui escludere annullato?
            }
            $anapro_rec['PROAGG'] = $anaproPrec_rec['PROAGG'];
        }
        if (!$dati->utenteWs) {
            $anapro_rec['PROUTE'] = App::$utente->getKey('nomeUtente');
        } else {
            $anapro_rec['PROUTE'] = $dati->utenteWs;
        }
        if (!$dati->Prouof) {
            include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
            $accLib = new accLib();
            $utenti_rec = $accLib->GetUtenti($anapro_rec['PROUTE'], 'utelog');
            $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
            if ($anamed_rec) {
                $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
                foreach ($uffdes_tab as $uffdes_rec) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                    if ($anauff_rec['UFFANN'] == 0) {
                        $dati->Prouof = $anauff_rec['UFFCOD'];
                        break;
                    }
                }
            }
        }
        $anapro_rec['PROUOF'] = $dati->Prouof;

        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        $anapro_rec['PRONOM'] = str_replace('"', '', $anapro_rec['PRONOM']);
        $anapro_rec['PROIND'] = str_replace('"', '', $anapro_rec['PROIND']);

        /* Resgistrazione Principale */
        try {
            if ($tipo == "Aggiungi") {
                $anaent_rec = $this->proLib->GetAnaent('26');
                include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
                $segnatura = proSegnatura::getStringaSegnatura($anapro_rec);
                $anapro_rec['PROSEG'] = $segnatura;
                $anapro_rec['PROLOG'] = "999" . substr(App::$utente->getKey('nomeUtente'), 0, 7) . date('d/m/y');
                /*
                 * Versione titolario corrente
                 */
                $versione_t = $this->proLib->GetTitolarioCorrente();
                $anapro_rec['VERSIONE_T'] = $versione_t;
                $insert_Info = 'Oggetto Protocollo: ' . $anapro_rec['PRONUM'] . " " . $anapro_rec['PRODAR'];
                if (!$this->insertRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $insert_Info)) {
                    $this->title = 'ERRORE';
                    $this->message = 'Inserimento protocollo: ' . $anapro_rec['PRONUM'] . ' / ' . $anapro_rec['PROPAR'] . ' non riuscito.';
                    $this->unlockAnaent($retLock);
                    return false;
                }
                $anaproNew_rec = $this->proLib->GetAnapro($anapro_rec['PRONUM'], 'codice', $dati->tipoProt);
                if ($dati->tipoProt == 'C') {
                    $risultato = $this->proLib->prenotaProtocollo('', "AGGIORNA", $dati->workYear, 'C');
                    $aggPronum = $risultato['pronum'];
                    if ($aggPronum == "Error") {
                        $this->title = $risultato['errTitolo'];
                        $this->message = $risultato['errMsg'];
                    }
                } else if ($dati->tipoProt == 'I') {
                    $aggPronum = $newPronum;
                    /*
                     * Sblocco Record Fittizio di ANAPRO I
                     */
                    $this->unlockAnaproI($retLock);
                } else {
                    $risultato = $this->proLib->prenotaProtocollo('', "AGGIORNA", $dati->workYear);
                    $aggPronum = $risultato['pronum'];
                    if ($aggPronum == "Error") {
                        $this->title = $risultato['errTitolo'];
                        $this->message = $risultato['errMsg'];
                    }
                }
                $this->unlockAnaent($retLock);
                if ($aggPronum == "Error")
                    return false;
            } else {
                $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
                if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
                    $this->title = 'ERRORE';
                    $this->message = 'Aggiornamento protocollo: ' . $anapro_rec['PRONUM'] . ' / ' . $anapro_rec['PROPAR'] . ' non riuscito.';
                    $this->unlockAnaent($retLock);
                    return false;
                }
                $anaproNew_rec = $this->proLib->GetAnapro($anapro_rec['ROWID'], 'rowid');
            }
        } catch (Exception $e) {
            $this->title = "Errore";
            $this->message = $e->getMessage();
            $this->unlockAnaent($retLock);
            return false;
        }
        $this->anaproCreato = $anaproNew_rec;

        /* Salvo Oggetto */
        $dati->Oggetto = str_replace('"', '', $dati->Oggetto);
        $anaogg_rec['OGGNUM'] = $anaproNew_rec['PRONUM'];
        $ogg_1 = substr($dati->Oggetto, 0, 50);
        $anaogg_rec['OGGDE1'] = trim($ogg_1) . str_repeat(" ", 50 - strlen(trim($ogg_1))) . $dati->tipoProt;
        $ogg_2 = substr($dati->Oggetto, 50, 49);
        $anaogg_rec['OGGDE2'] = trim($ogg_2) . str_repeat(" ", 49 - strlen(trim($ogg_2)));
        $ogg_3 = substr($dati->Oggetto, 99, 47);
        $anaogg_rec['OGGDE3'] = trim($ogg_3) . str_repeat(" ", 47 - strlen(trim($ogg_3)));
        $anaogg_rec['OGGOGG'] = $dati->Oggetto;
        $anaogg_rec['OGGPAR'] = $dati->tipoProt;
        /*
         * CANCELLO E INSERISCO LA TABELLA OGGETTI
         */
        $anaogg_old = $this->proLib->GetAnaogg($anaproNew_rec['PRONUM'], $dati->tipoProt);
        if ($anaogg_old) {
            $delete_Info = 'Oggetto ANAOGG: ' . $anaogg_old['OGGNUM'] . " " . $anaogg_old['OGGOGG'];
            if (!$this->deleteRecord($this->PROT_DB, 'ANAOGG', $anaogg_old['ROWID'], $delete_Info, 'ROWID', false)) {
                return false;
            }
        }
        $insert_Info = 'Inserimento: ' . $anaogg_rec['OGGNUM'] . ' ' . $anaogg_rec['OGGDE1'];
        if (!$this->insertRecord($this->PROT_DB, 'ANAOGG', $anaogg_rec, $insert_Info)) {
            return false;
        }
        /* Salvo Oggetto */
        $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
        $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
        $ananom_rec['NOMPAR'] = $dati->tipoProt;
        //$ananom_rec['NOMPAR'] = $this->tipoProt;
        $ananom_tab = $this->proLib->GetAnanom($anaproNew_rec['PRONUM'], true, $dati->tipoProt);
        if ($ananom_tab) {
            foreach ($ananom_tab as $key => $ananom_newRec) {
                $delete_Info = 'Oggetto ANANOM: ' . $ananom_newRec['NOMNUM'] . " " . $ananom_newRec['NOMNOM'];
                if (!$this->deleteRecord($this->PROT_DB, 'ANANOM', $ananom_newRec['ROWID'], $delete_Info)) {
                    return false;
                }
            }
        }
        $insert_Info = 'Inserimento: ' . $ananom_rec['NOMNUM'] . ' ' . $ananom_rec['NOMNOM'];
        if (!$this->insertRecord($this->PROT_DB, 'ANANOM', $ananom_rec, $insert_Info)) {
            return false;
        }
        /* Salvo Destinatari */
        try {
            $anades_tab = $this->proLib->GetAnades($anaproNew_rec['PRONUM'], 'codice', true, $dati->tipoProt);
            if ($anades_tab) {
                foreach ($anades_tab as $key => $anades_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
            }
            $anaspe_tab = $this->proLib->GetAnaspe($anaproNew_rec['PRONUM'], 'codice', true, $dati->tipoProt);
            if ($anaspe_tab) {
                foreach ($anaspe_tab as $key => $anaspe_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'ANASPE', $anaspe_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
            }

            if ($dati->tipoProt == 'P' || $dati->tipoProt == 'C' || $dati->tipoProt == 'I') {
                /* Salvo Firmatario */
                $anades_partenza = array();
                $anades_partenza['DESNUM'] = $anaproNew_rec['PRONUM'];
                $anades_partenza['DESPAR'] = $dati->tipoProt;
                $anades_partenza['DESTIPO'] = "M";
                $anades_partenza['DESCOD'] = $dati->FirmatarioDescod;
                $anades_partenza['DESCUF'] = $dati->FirmatarioUfficio;
                $anades_partenza['DESNOM'] = $dati->FirmatarioDesnom;
                $anades_partenza['DESCONOSCENZA'] = 0;
                $insert_Info = 'Inserimento: firmatario ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_partenza, $insert_Info)) {
                    return false;
                }
            }


            if ($dati->proAltriDest) {
                foreach ($dati->proAltriDest as $key => $record) {
                    $anades_rec = array();
                    $anades_rec['DESNUM'] = $anaproNew_rec['PRONUM'];
                    $anades_rec['DESPAR'] = $record['DESPAR'];
                    $anades_rec['DESTIPO'] = $record['DESTIPO'];
                    $anades_rec['DESCOD'] = $record['DESCOD'];
                    $anades_rec['DESNOM'] = $record['DESNOM'];
                    $anades_rec['DESIND'] = $record['DESIND'];
                    $anades_rec['DESCAP'] = $record['DESCAP'];
                    $anades_rec['DESCIT'] = $record['DESCIT'];
                    $anades_rec['DESPRO'] = $record['DESPRO'];
                    $anades_rec['DESDAT'] = $record['DESDAT'];
                    $anades_rec['DESDAA'] = $record['DESDAA'];
                    $anades_rec['DESDUF'] = $record['DESDUF'];
                    $anades_rec['DESANN'] = $record['DESANN'];
                    $anades_rec['DESCUF'] = $record['DESCUF'];
                    $anades_rec['DESGES'] = $record['DESGES'];
                    $anades_rec['DESRES'] = $record['DESRES'];
                    $anades_rec['DESMAIL'] = $record['DESMAIL'];
                    $anades_rec['DESTSP'] = $record['DESTSP'];
                    $anades_rec['DESFIS'] = $record['DESFIS'];
                    $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                    $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                    if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                        return false;
                    }
                }
            }



            if ($dati->proArriDest) {
                foreach ($dati->proArriDest as $key => $record) {
                    $anades_rec = array();
                    $anades_rec['DESNUM'] = $anaproNew_rec['PRONUM'];
                    $anades_rec['DESPAR'] = $record['DESPAR'];
                    $anades_rec['DESTIPO'] = $record['DESTIPO'];
                    $anades_rec['DESCOD'] = $record['DESCOD'];
                    $anades_rec['DESNOM'] = $record['DESNOM'];
                    $anades_rec['DESIND'] = $record['DESIND'];
                    $anades_rec['DESCAP'] = $record['DESCAP'];
                    $anades_rec['DESCIT'] = $record['DESCIT'];
                    $anades_rec['DESPRO'] = $record['DESPRO'];
                    $anades_rec['DESDAT'] = $record['DESDAT'];
                    $anades_rec['DESDAA'] = $record['DESDAA'];
                    $anades_rec['DESDUF'] = $record['DESDUF'];
                    $anades_rec['DESANN'] = $record['DESANN'];
                    $anades_rec['DESCUF'] = $record['DESCUF'];
                    $anades_rec['DESGES'] = $record['DESGES'];
                    $anades_rec['DESRES'] = $record['DESRES'];
                    $anades_rec['DESFIS'] = $record['DESFIS'];
                    $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                    $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                    if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                        return false;
                    }
                    if ($record['PROPAR'] != '' && $record['PROTSP'] != '') {
                        $anaspe_rec = array();
                        $anaspe_rec['PRONUM'] = $anaproNew_rec['PRONUM'];
                        $anaspe_rec['PROPAR'] = $record['PROPAR'];
                        $anaspe_rec['PRONOM'] = $record['PRONOM'];
                        $anaspe_rec['PROIND'] = $record['PROIND'];
                        $anaspe_rec['PROCAP'] = $record['PROCAP'];
                        $anaspe_rec['PROCIT'] = $record['PROCIT'];
                        $anaspe_rec['PROPRO'] = $record['PROPRO'];
                        $anaspe_rec['PRODAR'] = $record['PRODAR'];
                        $anaspe_rec['PRODRA'] = $record['PRODRA'];
                        $anaspe_rec['PROPES'] = $record['PROPES'];
                        $anaspe_rec['PROIRA'] = $record['PROIRA'];
                        $anaspe_rec['PRODER'] = $record['PRODER'];
                        $anaspe_rec['PROTSP'] = $record['PROTSP'];
                        $anaspe_rec['PROGRA'] = $record['PROGRA'];
                        $anaspe_rec['PROQTA'] = $record['PROQTA'];
                        $anaspe_rec['PRONUR'] = $record['PRONUR'];
                        $anaspe_rec['PRODER'] = $record['PRODER'];
                        $insert_Info = 'Inserimento: ' . $anaspe_rec['PRONUM'] . ' ' . $anaspe_rec['PRONOM'];
                        if (!$this->insertRecord($this->PROT_DB, 'ANASPE', $anaspe_rec, $insert_Info)) {
                            return false;
                        }
                    }
                }
            }
            $anaent_3 = $this->proLib->GetAnaent('3');
            if ($anaent_3['ENTDE1'] == 1) {
                $promitagg_tab = $this->proLib->getPromitagg($anaproNew_rec['PRONUM'], 'codice', true, $anaproNew_rec['PROPAR']);
                foreach ($promitagg_tab as $promitagg_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'PROMITAGG', $promitagg_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
                if ($dati->mittentiAggiuntivi) {
                    foreach ($dati->mittentiAggiuntivi as $mittenteAgg) {
                        $mittenteAgg['PRONUM'] = $anaproNew_rec['PRONUM'];
                        $mittenteAgg['PROPAR'] = $anaproNew_rec['PROPAR'];
                        $insert_Info = 'Inserimento: ' . $mittenteAgg['PRONUM'] . ' ' . $mittenteAgg['PRONOM'];
                        if (!$this->insertRecord($this->PROT_DB, 'PROMITAGG', $mittenteAgg, $insert_Info)) {
                            return false;
                        }
                    }
                }
            }
            $uffpro_tab = $this->proLib->GetUffpro($anaproNew_rec['PRONUM'], 'codice', true, $anaproNew_rec['PROPAR']);
            foreach ($uffpro_tab as $key => $uffpro_rec) {
                if (!$this->deleteRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
            foreach ($dati->proArriUff as $key => $record) {
                $uffpro_rec = array();
                $uffpro_rec['PRONUM'] = $anaproNew_rec['PRONUM'];
                $uffpro_rec['UFFPAR'] = $anaproNew_rec['PROPAR'];
                $uffpro_rec['UFFCOD'] = $record['UFFCOD'];
                $uffpro_rec['UFFFI1'] = $record['UFFFI1'];
                $insert_Info = 'Inserimento: ' . $uffpro_rec['PRONUM'] . ' ' . $uffpro_rec['UFFCOD'];
                if (!$this->insertRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec, $insert_Info)) {
                    return false;
                }
            }
            if ($dati->anapro_rec['PROTSP']) {
                /*
                 * @BugFixed: ma serve?
                 */
                $anatspCtr_rec = $this->DecodAnatsp($dati->anapro_rec['PROTSP']);
                $anaspe_rec = array();
                $anaspe_rec['PRONUM'] = $anaproNew_rec['PRONUM'];
                $anaspe_rec['PROPAR'] = $dati->tipoProt;
                $anaspe_rec['PRONOM'] = $dati->anapro_rec['PRONOM'];
                $anaspe_rec['PROIND'] = $dati->anapro_rec['PROIND'];
                $anaspe_rec['PROCAP'] = $dati->anapro_rec['PROCAP'];
                $anaspe_rec['PROCIT'] = $dati->anapro_rec['PROCIT'];
                $anaspe_rec['PROPRO'] = $dati->anapro_rec['PROPRO'];
                $anaspe_rec['PRODAR'] = $anaproNew_rec['PRODAR'];
                $anaspe_rec['PRODRA'] = $dati->Prodra;
                $anaspe_rec['PROPES'] = $anatspCtr_rec['tsppes'];
                $anaspe_rec['PROIRA'] = $anatspCtr_rec['tsptar'];
                $anaspe_rec['PROTSP'] = $dati->anapro_rec['PROTSP'];
//                $anaspe_rec['PROGRA'] = $_POST[$this->nameForm . '_Progra'];
//                $anaspe_rec['PROQTA'] = $_POST[$this->nameForm . '_Proqta'];
                $anaspe_rec['PRONUR'] = $dati->Pronur;
                $anaspe_rec['PRODER'] = $anatspCtr_rec['TSPDES'];
                $insert_Info = 'Inserimento: ' . $anaspe_rec['PRONUM'] . ' ' . $anaspe_rec['PRONOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANASPE', $anaspe_rec, $insert_Info)) {
                    return false;
                }
            }
            if ($dati->Risrde != "" || $dati->Risrda != "") {
                $anaris_tab = $this->proLib->GetAnaris($anaproNew_rec['PRONUM'], 'codice', true, $dati->tipoProt);
                foreach ($anaris_tab as $key => $anaris_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'ANARIS', $anaris_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
                $anaris_rec = array();
                $anaris_rec['RISNUM'] = $anaproNew_rec['PRONUM'];
                $anaris_rec['RISRDE'] = $dati->Risrde;
                $anaris_rec['RISRDA'] = $dati->Risrda;
                $anaris_rec['RISPAR'] = $dati->tipoProt;
                $insert_Info = 'Inserimento: ' . $anaris_rec['PRONUM'] . ' ' . $anaris_rec['PRONOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANARIS', $anaris_rec, $insert_Info)) {
                    return false;
                }
            }
            /*
             * si da visibilità al protocollo
             */
            $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
            $iter->sincIterProtocollo();

            if ($dati->tipoProt != 'I') {
                $gestiti = $this->GestioneAllegati($anaproNew_rec['PRONUM'], $dati, $anaproNew_rec);
                if (!$gestiti) {
                    return false;
                }
            }

            if (is_array($dati->fileDaPEC)) {
                $this->inserisciPromail($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $dati->fileDaPEC);
                $risultato = $this->setClasseMail($dati->fileDaPEC);
                if ($risultato === false) {
                    return false;
                }
            }
        } catch (Exception $e) {
            $this->title = "Errore";
            $this->message = "$e->getMessage()";
            //Out::msgStop("Errore", $e->getMessage());
            return false;
        }
        return $anaproNew_rec['ROWID'];
    }

    private function DecodAnatsp($codTsp, $tipo = 'codice') {
        $anatsp_rec = $this->proLib->GetAnatsp($codTsp, $tipo);
        return $anatsp_rec;
    }

    public function GestioneAllegati($numeroProtocollo, $dati, $anaproNew_rec) {

        if ($dati->tipoProt == 'I') {

            include_once ITA_BASE_PATH . '/apps/Segreteria/segLibAllegati.class.php';
            $segLibAllegati = new segLibAllegati();

            include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
            $segLib = new segLib();

            $indiceRec = $dati->indiceRec;
            if (!$indiceRec) {
                $indiceRec = $segLib->GetIndice($anaproNew_rec['PRONUM'], 'anapro', false, $anaproNew_rec['PROPAR']);
                if (!$indiceRec) {
                    $this->title = "Errore in lettura di Indice per PRONUM = " . $anaproNew_rec['PRONUM'];
                    $this->message = $segLibAllegati->getErrMessage();
                    return false;
                }
            } else {
                // Rilettura di Indice:
                $indiceRec = $segLib->GetIndice($anaproNew_rec['PRONUM'], 'anapro', false, $anaproNew_rec['PROPAR']);
            }



            $retGestisci = $segLibAllegati->SalvaAllegati($this, $indiceRec, $dati->proArriAlle);
            if (!$retGestisci) {
                $this->title = "Errore Archiviazione File";
                $this->message = $segLibAllegati->getErrMessage();
                return false;
            }

            $retRitorno = array('ROWIDAGGIUNTI' => $retGestisci);

            $this->setRisultatoRitorno($retRitorno);
        } else {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
            $proLibAllegati = new proLibAllegati();
            $retGestisci = $proLibAllegati->GestioneAllegati($this, $numeroProtocollo, $dati->tipoProt, $dati->proArriAlle, $anaproNew_rec['PROCON'], $anaproNew_rec['PRONOM']);
            if (!$retGestisci) {
                $this->title = "Errore Archiviazione File";
                $this->message = $proLibAllegati->getErrMessage();
                return false;
            }
            $this->setRisultatoRitorno($proLibAllegati->getRisultatoRitorno());
        }
        return $retGestisci;



        // Centralizzato su proLibAllegati, precedentemente utilizzava una funzione interna.
    }

    function setClasseMail($fileDaPEC) {
        if ($fileDaPEC['TYPE'] == 'MAILBOX') {
            include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
            $emlDbMailBox = new emlDbMailBox();
            $risultato = $emlDbMailBox->updateClassForRowId($fileDaPEC['ROWID'], '@PROTOCOLLATO@');
            if ($risultato === false) {
                $this->title = "Nuovo Protocollo";
                $this->message = "Mail non archiviata: " . $emlDbMailBox->getLastMessage();
                return false;
                //App::log($emlDbMailBox->getLastMessage());
            }
        } else if ($fileDaPEC['TYPE'] == 'LOCALE') {
            if (is_file($fileDaPEC['FILENAME'])) {
                if (!@unlink($fileDaPEC['FILENAME'])) {
                    $this->title = "Nuovo Protocollo";
                    $this->message = "File:" . $fileDaPEC['FILENAME'] . " non Eliminato";
                    return false;
                }
            }
            $risultato = true;
        }
        return $risultato;
    }

    function inserisciPromail($pronum, $propar, $fileDaPEC) {
        if ($fileDaPEC['TYPE'] == 'MAILBOX') {
            include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
            $emlLib = new emlLib();
            $mail_rec = $emlLib->getMailArchivio($fileDaPEC['ROWID'], 'rowid');
            if ($mail_rec) {
                $promail_rec = array(
                    'PRONUM' => $pronum,
                    'PROPAR' => $propar,
                    'IDMAIL' => $mail_rec['IDMAIL'],
                    'SENDREC' => $mail_rec['SENDREC']
                );
                $insert_Info = 'Inserimento: ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'];
                $this->insertRecord($this->PROT_DB, 'PROMAIL', $promail_rec, $insert_Info);
            } else {
                return false;
            }
        }
        return true;
    }

    function saveOggetto($pronum, $propar, $oggetto) {
        $anaogg_rec['OGGNUM'] = $pronum;
        $anaogg_rec['OGGPAR'] = $propar;
        $oggetto = str_replace('"', '', $oggetto);
        $anaogg_rec['OGGDE1'] = str_pad(substr($oggetto, 0, 50), 50, " ", STR_PAD_RIGHT) . $propar;
        $anaogg_rec['OGGDE2'] = str_pad(substr($oggetto, 49, 49), 49, " ", STR_PAD_RIGHT);
        $anaogg_rec['OGGDE3'] = str_pad(substr($oggetto, 99, 47), 47, " ", STR_PAD_RIGHT);
        $anaogg_rec['OGGOGG'] = $oggetto;

        /*
         * CANCELLO E INSERISCO LA TABELLA OGGETTI
         */
        $anaogg_old = $this->proLib->GetAnaogg($pronum, $propar);
        if ($anaogg_old) {
            $delete_Info = 'Oggetto ANAOGG: ' . $anaogg_old['OGGNUM'] . " " . $anaogg_old['OGGOGG'];
            if (!$this->deleteRecord($this->PROT_DB, 'ANAOGG', $anaogg_old['ROWID'], $delete_Info, 'ROWID', false)) {
                return false;
            }
        }
        /* Salvo Oggetto */
        $insert_Info = 'Inserimento: ' . $anaogg_rec['OGGNUM'] . ' ' . $anaogg_rec['OGGDE1'];
        if (!$this->insertRecord($this->PROT_DB, 'ANAOGG', $anaogg_rec, $insert_Info)) {
            return false;
        }
        return true;
    }

    function registraSave($motivo, $rowid, $tipo = 'rowid') {
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anapro_rec = $this->proLib->GetAnapro($rowid, $tipo);
        $codice = $anapro_rec['PRONUM'];
        $tipoProt = $anapro_rec['PROPAR'];
        $anapro_rec['SAVEDATA'] = $savedata;
        $anapro_rec['SAVEORA'] = $saveora;
        $anapro_rec['SAVEUTENTE'] = $saveutente;
        $anapro_rec['SAVEMOTIVAZIONE'] = $motivo;
        unset($anapro_rec['ROWID']);
        $this->insertRecord($this->PROT_DB, 'ANAPROSAVE', $anapro_rec, '', 'ROWID', false);

        $anaogg_rec = $this->proLib->GetAnaogg($codice, $anapro_rec['PROPAR']);
        if ($anaogg_rec) {
            $anaogg_rec['SAVEDATA'] = $savedata;
            $anaogg_rec['SAVEORA'] = $saveora;
            $anaogg_rec['SAVEUTENTE'] = $saveutente;
            unset($anaogg_rec['ROWID']);
            $this->insertRecord($this->PROT_DB, 'ANAOGGSAVE', $anaogg_rec, '', 'ROWID', false);
        }

        $ananom_tab = $this->proLib->GetAnanom($codice, true, $tipoProt);
        if ($ananom_tab) {
            foreach ($ananom_tab as $key => $ananom_newRec) {
                $ananom_newRec['SAVEDATA'] = $savedata;
                $ananom_newRec['SAVEORA'] = $saveora;
                $ananom_newRec['SAVEUTENTE'] = $saveutente;
                unset($ananom_newRec['ROWID']);
                $this->insertRecord($this->PROT_DB, 'ANANOMSAVE', $ananom_newRec, '', 'ROWID', false);
            }
        }

        $anades_tab = $this->proLib->GetAnades($codice, 'codice', true, $tipoProt);
        if ($anades_tab) {
            foreach ($anades_tab as $key => $anades_rec) {
                $anades_rec['SAVEDATA'] = $savedata;
                $anades_rec['SAVEORA'] = $saveora;
                $anades_rec['SAVEUTENTE'] = $saveutente;
                unset($anades_rec['ROWID']);
                $this->insertRecord($this->PROT_DB, 'ANADESSAVE', $anades_rec, '', 'ROWID', false);
            }
        }

        $anaspe_tab = $this->proLib->GetAnaspe($codice, 'codice', true, $tipoProt);
        if ($anaspe_tab) {
            foreach ($anaspe_tab as $key => $anaspe_rec) {
                $anaspe_rec['SAVEDATA'] = $savedata;
                $anaspe_rec['SAVEORA'] = $saveora;
                $anaspe_rec['SAVEUTENTE'] = $saveutente;
                unset($anaspe_rec['ROWID']);
                $this->insertRecord($this->PROT_DB, 'ANASPESAVE', $anaspe_rec, '', 'ROWID', false);
            }
        }

        $uffpro_tab = $this->proLib->GetUffpro($codice, 'codice', true, $tipoProt);
        foreach ($uffpro_tab as $key => $uffpro_rec) {
            $uffpro_rec['SAVEDATA'] = $savedata;
            $uffpro_rec['SAVEORA'] = $saveora;
            $uffpro_rec['SAVEUTENTE'] = $saveutente;
            unset($uffpro_rec['ROWID']);
            $this->insertRecord($this->PROT_DB, 'UFFPROSAVE', $uffpro_rec, '', 'ROWID', false);
        }

        $anaris_tab = $this->proLib->GetAnaris($codice, 'codice', true, $tipoProt);
        foreach ($anaris_tab as $key => $anaris_rec) {
            $anaris_rec['SAVEDATA'] = $savedata;
            $anaris_rec['SAVEORA'] = $saveora;
            $anaris_rec['SAVEUTENTE'] = $saveutente;
            unset($anaris_rec['ROWID']);
            $this->insertRecord($this->PROT_DB, 'ANARISSAVE', $anaris_rec, '', 'ROWID', false);
        }

        $anadoc_tab = $this->proLib->GetAnadoc($codice, 'codice', true, $tipoProt);
        foreach ($anadoc_tab as $key => $anadoc_rec) {
            $anadoc_rec['SAVEDATA'] = $savedata;
            $anadoc_rec['SAVEORA'] = $saveora;
            $anadoc_rec['SAVEUTENTE'] = $saveutente;
            unset($anadoc_rec['ROWID']);
            $this->insertRecord($this->PROT_DB, 'ANADOCSAVE', $anadoc_rec, '', 'ROWID', false);
        }

        $anaent_3 = $this->proLib->GetAnaent('3');
        if ($anaent_3['ENTDE1'] == 1) {
            $promitagg_tab = $this->proLib->getPromitagg($codice, 'codice', true, $tipoProt);
            foreach ($promitagg_tab as $promitagg_rec) {
                $promitagg_rec['SAVEDATA'] = $savedata;
                $promitagg_rec['SAVEORA'] = $saveora;
                $promitagg_rec['SAVEUTENTE'] = $saveutente;
                unset($promitagg_rec['ROWID']);
                $this->insertRecord($this->PROT_DB, 'PROMITAGGSAVE', $promitagg_rec, '', 'ROWID', false);
            }
        }
    }

    public function SegnaAllegato($anapro_rec, $anadoc_rowid, $PosMarcat = array()) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
        $proLibAllegati = new proLibAllegati();
        // leggo anadoc
        $anadoc_rec = $this->proLib->GetAnadoc($anadoc_rowid, 'rowid');
        if (!$anadoc_rec) {
            $this->title = "Registra Segna Allegato";
            $this->message = "Anadoc non trovato. Marcatura del documento impossibile.";
            return false;
        }
        // Preparo path e file
//        $protPath = $this->proLib->SetDirectory($anapro_rec['PRONUM'],  $anapro_rec['PROPAR'] );
//        $destFile = $protPath . "/" . $anadoc_rec['DOCFIL'];
        $DocPath = $proLibAllegati->GetDocPath($anadoc_rec['ROWID'], false, false, true);
        $retSegna = $proLibAllegati->SegnaPDF($this, $DocPath['DOCPATH'], $anapro_rec, $anadoc_rowid, $PosMarcat);
//        $retSegna = $proLibAllegati->SegnaPDF($this, $destFile, $anapro_rec, $anadoc_rowid, $PosMarcat);
        if (!$retSegna) {
            $this->title = "Registra Segna Allegato";
            $this->message = $proLibAllegati->getErrMessage();
            return false;
        }

        return true;
    }

    public function CtrMarcaturaAllegati($Anapro_rec) {
        /*
         * Verifico se parametro per marcatura automatica 
         * Ed estraggo allegati da marcare 
         */
        $Anadoc_tab = $this->proLib->getAnadoc($Anapro_rec['PRONUM'], 'protocollo', true, $Anapro_rec['PROPAR']);
        $anaent_34 = $this->proLib->GetAnaent('34');
        if ($anaent_34['ENTDE6'] == '2') {
            foreach ($Anadoc_tab as $Anadoc_rec) {
                if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'pdf') {
                    if (!$this->SegnaAllegato($Anapro_rec, $Anadoc_rec['ROWID'])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function PrenotaDocumentoAnapro($workYear) {
        $sql = "SELECT MAX(PRONUM) AS PRONUM FROM ANAPRO WHERE PRONUM LIKE  '$workYear%' AND PROPAR='W' ";
        try {
            $maxRepertorio_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if (!$maxRepertorio_rec['PRONUM']) {
                $codice = $workYear . "000001";
            } else {
                $codice = $maxRepertorio_rec['PRONUM'] + 1;
            }
        } catch (Exception $exc) {
            return false;
        }
        return $codice;
    }

}

?>