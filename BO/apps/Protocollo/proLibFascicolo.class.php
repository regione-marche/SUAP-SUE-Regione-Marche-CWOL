<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of proFascicolo
 *
 * @author michele
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabiliFascicolo.class.php';

class proLibFascicolo {

    public $proLib;
    public $proLibAllegati;
    public $proLibPratica;
    public $proLibSerie;
    private $errCode;
    private $errMessage;
    public $repertoriofascicoli = "0000000001";

    const PERMFASC_VISIBILITA_ARCHIVISTA = 'VISIBILITA_ARCHIVISTA'; // serve?
    const PERMFASC_VISIBILITA_COMPLETA = 'VISIBILITA_COMPLETA';
    const PERMFASC_APERTURA_CHIUSURA = 'APERTURA_CHIUSURA';
    const PERMFASC_CREAZIONE = 'CREAZIONE';
    const PERMFASC_GESTIONE_PROTOCOLLI = 'GESTIONE_PROTOCOLLI';
    const PERMFASC_GESTIONE_DOCUMENTI = 'GESTIONE_DOCUMENTI';
    const PERMFASC_GESTIONE_SOTTOFASCICOLI = 'GESTIONE_SOTTOFASCICOLI';
    const PERMFASC_SCRIVE_NOTE = 'SCRIVE_NOTE';
    const PERMFASC_MODIFICA_DATI = 'MODIFICA_DATI';
    const PERMFASC_GESTIONE_VISIBILITA = 'GESTIONE_VISIBILITA';
    const PERMFASC_RIAPRI_FASCICOLO = 'RIAPRI_FASCICOLO';

// METODI CONVALIDATI
// DA UTILIZZARE    

    function __construct() {
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibPratica = new proLibPratica();
        $this->proLibSerie = new proLibSerie();
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
    }

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    /**
     * 
     * @param type $versione
     * @param type $titolario
     * @param type $anno
     * @param type $uniOpe
     * @param type $descrizione
     * @param type $progressivo
     * @param type $Serie_rec
     * @param type $DatiAnaorg 
     *  Campi di ANAORG di riferimento
     * @return boolean
     */
    public function prenotaAnaorg($versione, $titolario, $anno, $uniOpe, $descrizione, $progressivo = '', $Serie_rec = array(), $DatiAnaorg = array()) {
        $prenotaModo = 0;
        $anaent_34 = $this->proLib->GetAnaent('34');
        if ($anaent_34) {
            if ($anaent_34['ENTDE2'] == 0 || $anaent_34['ENTDE2'] == 1) {
                $prenotaModo = intval($anaent_34['ENTDE2']);
            }
        }
        if ($prenotaModo == 0) {
            $chiaveRicerca = $titolario . "." . $anno . ".";
        } else {
            $chiaveRicerca = $titolario . ".";
        }
        if (!$progressivo) {
            $sql = "SELECT MAX(ORGCOD) AS ULTIMO FROM ANAORG WHERE ORGKEY<>'' AND ORGKEY LIKE '$chiaveRicerca%'";
            $Anaorg_max = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
            $progressivo = str_pad((int) $Anaorg_max['ULTIMO'] + 1, 6, "0", STR_PAD_LEFT);
        }

        $Anaorg_rec = array();
        $Anaorg_rec['ORGCOD'] = $progressivo;
        $Anaorg_rec['ORGDES'] = $descrizione;
        $Anaorg_rec['ORGAPE'] = date('Ymd');
        $Anaorg_rec['VERSIONE_T'] = $versione;
        $Anaorg_rec['ORGCCF'] = $titolario;
        $Anaorg_rec['ORGUOF'] = $uniOpe;
        $Anaorg_rec['ORGANN'] = $anno;
        $Anaorg_rec['ORGKEY'] = $titolario . "." . $anno . "." . $progressivo;
        if ($DatiAnaorg) {
            $Anaorg_rec['NATFAS'] = $DatiAnaorg['NATFAS'];
        }
        // Chiave fascicolo collegato.
        $Anaorg_rec['ORGKEYPRE'] = $DatiAnaorg['ORGKEYPRE'];
        if ($DatiAnaorg['GESNUMFASC']) {
            $Anaorg_rec['GESNUMFASC'] = $DatiAnaorg['GESNUMFASC'];
        }


        // Controllo se occorre valorizzare la serie
        if ($Serie_rec['CODICE']) {
            // Se parametro forza il progressivo, lo controllo e creo
            if ($Serie_rec['FORZA_PROGSERIE']) {
                $ProgressivoSerie = $Serie_rec['PROGSERIE'];
                if (!$this->proLibSerie->ControlloProgressivoSerie($Serie_rec['CODICE'], $titolario, $versione, $Anaorg_rec, $Serie_rec['PROGSERIE'])) {
                    $this->errCode = -1;
                    $this->setErrMessage("Inserimento fascicolo fallito: è stato riscontrato un errore in controllo serie/titolario. <br>" . $this->proLibSerie->getErrMessage());
                    return false;
                }
            } else {
                $ProgressivoSerie = $this->proLibSerie->PrenotaProgressivoSerie($Serie_rec['CODICE'], $titolario, $versione, $Anaorg_rec, $Serie_rec['PROGSERIE']);
                if (!$ProgressivoSerie) {
                    $this->errCode = -1;
                    $this->setErrMessage("Inserimento fascicolo fallito: è stato riscontrato un errore in aggiunta serie. <br>" . $this->proLibSerie->getErrMessage());
                    return false;
                }
            }
            $Anaorg_rec['CODSERIE'] = $Serie_rec['CODICE'];
            $Anaorg_rec['PROGSERIE'] = $ProgressivoSerie;
        }

        $Anaorg_rec['ORGSEG'] = $DatiAnaorg['ORGSEG'];
        // Ricalcolo sengatura solo se non è stata passata da parametri:
        if (!$DatiAnaorg['ORGSEG']) {
            $segnatura = $Segnatura = $this->proLibSerie->GetSegnaturaFascicolo($Anaorg_rec);
            $Anaorg_rec['ORGSEG'] = $segnatura;
        }
        // Inserita la possibilità di inserire data inserimento dinamicamente.
        if ($DatiAnaorg['ORGAPE']) {
            $Anaorg_rec['ORGAPE'] = $DatiAnaorg['ORGAPE'];
        }
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAORG', 'ROWID', $Anaorg_rec);
            $rowid_Anaorg = $this->proLib->getPROTDB()->getLastId();
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->setErrMessage("Inserimento ANAORG fallito:" . $exc->getMessage());
            return false;
        }

        return $rowid_Anaorg;
    }

    /**
     * Nuova funzione per creare un fascicolo
     * @param <type> $model
     * @param <type> $datiFascicolazione
     * @param <type> $descrizione
     * @param <type> $codiceProcedimento
     * @return <type>
     */
    public function creaFascicolo($model, $datiFascicolazione, $descrizione, $codiceProcedimento = '') {
        $this->errCode = 0;
        //
        // Controlli
        //
        if (!$datiFascicolazione) {
            $this->errCode = -1;
            $this->setErrMessage("Riferimento Protocollo mancante.");
            return false;
        }
        /* Controllo dati obbligatori: Titolario, Oggetto, Responsabile */

        /* 1 Titolario: */
        if (!$datiFascicolazione['TITOLARIO'] || is_null($datiFascicolazione['TITOLARIO'])) {
            $this->errCode = -1;
            $this->setErrMessage('Il Titolario è obbligatorio. Non è possibile creare il fascicolo senza.');
            return false;
        }
        if ($datiFascicolazione['TITOLARIO'] == '0100' || $datiFascicolazione['TITOLARIO'] == '01000100') { // @TODO Tramite il parametro....
            $this->errCode = -1;
            $this->setErrMessage('Il titolario "DA ASSEGNARE":' . $datiFascicolazione['TITOLARIO'] . ' non può essere utilizzato in un fascicolo.');
            return false;
        }
        /* 2 Oggetto/Descrizione */
        if (!$descrizione) {
            $this->errCode = -1;
            $this->setErrMessage('Oggetto del fascicolo obbligatorio. Non è possibile creare il fascicolo senza.');
            return false;
        }
        /* 3 Responsabile e Ufficio */
        if (!$datiFascicolazione['RES'] || !$datiFascicolazione['UFF']) {
            $this->errCode = -1;
            $this->setErrMessage('Il Responsabile è obbligatorio per la creazione del nuovo fascicolo.');
            return false;
        }
        //
        // Blocca la prenotazione si usa anaorg come contatore
        //
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAORG", "", "", 20);
        if ($retLock['status'] !== 0) {
            $this->errCode = -1;
            $this->setErrMessage("Blocco tabella progressivi non riuscito per la prenotazione della Serie Archivistica.");
            return false;
        }
        //
        // Creo il record ANAORG
        //
        $versione = $datiFascicolazione['VERSIONE_T'];
        $titolario = $datiFascicolazione['TITOLARIO'];
        $uniOpe = $datiFascicolazione['GESPROUFF'];
        $Serie_rec = $datiFascicolazione['SERIE'];
        $DatiAnaorg = $datiFascicolazione['DATI_ANAORG'];
        $anno = date('Y');
        if ($datiFascicolazione['ANNO_FASCICOLO']) {
            $anno = $datiFascicolazione['ANNO_FASCICOLO'];
        }
        /* Verifico se occore Forzare il Progressivo: */
        $ForzaProgressivo = '';
        if ($datiFascicolazione['FORZA_PROGSERIE']) {
            $ForzaProgressivo = $datiFascicolazione['FORZA_PROGSERIE'];
        }
        if (!$datiFascicolazione['ROWIDANAORG']) {
            $rowid_anaorg = $this->prenotaAnaorg($versione, $titolario, $anno, $uniOpe, $descrizione, $ForzaProgressivo, $Serie_rec, $DatiAnaorg);
            if ($versione === '' || $versione === null) {
                $versione = $this->proLib->GetTitolarioCorrente();
            }
            if (!$rowid_anaorg) {
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            $fascicolo_rec = $this->proLib->getAnaorg($rowid_anaorg, 'rowid');
            //
            // Creo Anapro di tipo F
            //
            
            // CONTROLLARE SE GIA ESISTE ANAPRO PRE IL PROFASKEY???
            //
            $rowid_anapro = $this->getNewFascicoloAnapro($fascicolo_rec, $datiFascicolazione);
            if ($rowid_anapro === false) {
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            $anapro_F_rec = $this->proLib->GetAnapro($rowid_anapro, 'rowid');
        } else {
            $rowid_anaorg = $datiFascicolazione['ROWIDANAORG'];
            $fascicolo_rec = $this->proLib->getAnaorg($rowid_anaorg, 'rowid');
            if (!$fascicolo_rec) {
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            $anapro_F_rec = $this->getAnaproFascicolo($fascicolo_rec['ORGKEY']);
            if (!$anapro_F_rec) {
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
        }

        //
        // Creo ORGNODE
        //
        // CONTROLLARE SE GIA ESISTE NODO PRE IL PROFASKEY???
        //
        $Orgnode_rec = array();
        $Orgnode_rec['ORGKEY'] = $anapro_F_rec['PROFASKEY'];
        $Orgnode_rec['PRONUM'] = $anapro_F_rec['PRONUM'];
        $Orgnode_rec['PROPAR'] = $anapro_F_rec['PROPAR'];
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ORGNODE', 'ROWID', $Orgnode_rec);
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->setErrMessage("Creazione struttura Fascicolo Fallita: " . $exc->getMessage());
            return false;
        }
        //
        // Carico Proges e azione del fascicolo
        //
        // CONTROLLARE SE GIA ESISTE PROGEST PRE IL PROFASKEY???
        //
        $rowid_proges = $this->getNewFascicoloPratica($model, $datiFascicolazione['RES'], $fascicolo_rec, $codiceProcedimento, $datiFascicolazione['UFF'], $datiFascicolazione['GESPROUFF']);
        if ($rowid_proges === false) {
            ItaDB::DBUnLock($retLock['lockID']);
//            $this->errCode = -1;
//            $this->setErrMessage("Errore nella creazione del Procedimento Fascicolo.");
            return false;
        }
// FASCICOLI CREATI SENZA SERIE NON SI PUO' VERIFICARE:        
//        $Serie_rec = $datiFascicolazione['SERIE'];
//        if ($Serie_rec['CODICE'] && !$fascicolo_rec['CODSERIE']) {
//            $proges_rec = $this->proLibPratica->GetProges($rowid_proges, 'rowid');
//            if (!$this->proLibSerie->AggiungiSerieAFascicolo($Serie_rec['CODICE'], $titolario, $versione, $proges_rec['GESKEY'], $Serie_rec['PROGSERIE'])) {
//                $this->errCode = -1;
//                $this->setErrMessage("Fascicolo creato con successo ma è stato riscontrato un errore in aggiunta serie. <br>" . $this->proLibSerie->getErrMessage());
//                // Faccio continuare, il messaggio viene dato al ritorno nel caso in cui è valorizzato.
//            }
//        }
        ItaDB::DBUnLock($retLock['lockID']);
        return $rowid_proges;
    }

    public function creaSottoFascicolo($orgkey, $descrizione, $datiFascicolazione) {
        $fascicolo_rec = $this->proLib->GetAnaorg($orgkey, 'orgkey');
        if (!$fascicolo_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Fascicolo: $orgkey non trovato.");
            return false;
        }
        $rowid_AnaproAzione = $this->getNewOrgNodeAnapro($fascicolo_rec, null, $descrizione, $datiFascicolazione);
        if (!$rowid_AnaproAzione) {
            return false;
        }
        return $rowid_AnaproAzione;
    }

    public function creaAzione($orgkey, $descrizione, $datiFascicolazione) {
        $fascicolo_rec = $this->proLib->GetAnaorg($orgkey, 'orgkey');
        if (!$fascicolo_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Fascicolo: $orgkey non trovato.");
            return false;
        }
        $rowid = $this->getNewOrgTaskAnapro($fascicolo_rec, null, $descrizione, $datiFascicolazione);
        if (!$rowid) {
            return false;
        }
        return $rowid;
    }

    public function getAnaproFascicolo($profaskey) {
        $sql = "SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='" . $profaskey . "'";
        $anaproFascicolo_rec = $this->proLib->getGenericTab($sql, false);
        if (!$anaproFascicolo_rec) {
            return false;
        }
        return $anaproFascicolo_rec;
    }

    public function creaAzioneFascicolo($gesnum, $geskey, $responsabile, $uffResp, $gesprouff) {
        $anapro_F_rec = $this->getAnaproFascicolo($geskey);
        $propas_rec = array();
        $propas_rec['PRONUM'] = $gesnum;
        $propas_rec['PROSEQ'] = 1;

        $anaogg_rec = $this->proLib->GetAnaogg($anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR']);
        if ($anaogg_rec) {
            $propas_rec['PRODPA'] = $anaogg_rec['OGGOGG'];
        } else {
            $propas_rec['PRODPA'] = "Fascicolazione procedimento";
        }
        $propas_rec['PROPAK'] = $this->proLibPratica->PropakGenerator($gesnum);
        $propas_rec['PROUTEADD'] = $propas_rec['PROUTEEDIT'] = $propas_rec['PASPROUTE'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEADD'] = $propas_rec['PRODATEEDIT'] = date("Ymd");
        $propas_rec['PROORAADD'] = $propas_rec['PROORAEDIT'] = date("H:i:s");
        $propas_rec['PROVISIBILITA'] = "Aperto";
        $propas_rec['PRORPA'] = $responsabile;
        $propas_rec['PROUFFRES'] = $uffResp;
        $propas_rec['PROINI'] = date('Ymd');
        $propas_rec['PRONODE'] = 1;
        $propas_rec['PASPRO'] = $anapro_F_rec['PRONUM'];
        $propas_rec['PASPAR'] = $anapro_F_rec['PROPAR'];
        $propas_rec['PASPROUFF'] = $gesprouff;
        //
        // Collegare ad arcite
        //
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROPAS', 'ROWID', $propas_rec);
            $rowid = $this->proLib->getPROTDB()->getLastId();
            $this->proLibPratica->ordinaPassi($gesnum);
            $this->proLibPratica->sincronizzaStato($gesnum);
            return $rowid;
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->setErrMessage("Creazione azione fascicolo fallita:" . $exc->getMessage());
            return false;
        }
    }

    public function getDestinatariFascicolo($profaskey) {
        $sql = "SELECT * FROM ANAPRO WHERE PROFASKEY='" . $profaskey . "'";
        $anapro_tab = $this->proLib->getGenericTab($sql, true);
        if (!$anapro_tab) {
            return array();
        }
        $arrWhere = array();
        foreach ($anapro_tab as $anapro_rec) {
            $arrWhere[] = "(ITEPAR='{$anapro_rec["PROPAR"]}' AND ITEPRO={$anapro_rec["PRONUM"]})";
        }
        $where = implode(" OR ", $arrWhere);
        $arcite_tab = $this->proLib->getGenericTab("SELECT DISTINCT(ITEDES) AS ITEDES  FROM ARCITE WHERE $where");
        foreach ($arcite_tab as $arcite_rec) {
            $destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE MEDCOD='{$arcite_rec['ITEDES']}' AND MEDANN=0", false);
        }
        return $destinatari;
    }

    /**
     * Collego un ANAPRO ad un fascicolo/sottofascicolo
     * @param type $model       Model Responsabile
     * @param type $fascicolo   Codice Fascicolo (cat cla sottoclass.anno.progresssivo) orgkey
     * @param type $pronum      Protocollo
     * @param type $propar      Tipo Protocollo
     * @param type $propak      Chiave azione di riferimento che indica il contenitore (HOOK A PROPAS E ANAPRO)
     * @return boolean
     */
    public function insertDocumentoFascicolo($model, $fascicolo, $pronum, $propar, $pronumNode, $proparNode) {
        //
        // Controllo esistenza Anapro da inserire
        //
        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if (!$anapro_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Documento di riferimento mancante o inaccessibile.");
            return false;
        }
        //
        // controllo congruenza fascicolo/documento
        // Qui non serve più, è possibile inserire un protocollo in un fascicolo con titolario differente.
        //
        list($base_fascicolo, $skip) = explode('.', $fascicolo, 2);
//        if ($anapro_rec['PROCCF'] != $base_fascicolo) {
//            $this->errCode = -1;
//            $this->setErrMessage("Classificazione del documento incongruente con fascicolo di destinazione.");
//            return false;
//        }
        //
        // 1 - Controllo esistenza Anaorg (fascicolo)
        //
        $fascicolo_rec = $this->proLib->GetAnaorg($fascicolo, 'orgkey');
        if (!$fascicolo_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Fascicolo di riferimento mancante o inaccessibile.");
            return false;
        }

        //
        // 2 - Controllo esistenza ANAPRO del nodo fascicolo PROPAR = 'F'
        //
        $anapro_rec_f = $this->getAnaproFascicolo($fascicolo);
        if (!$anapro_rec_f) {
            $this->errCode = -1;
            $this->setErrMessage("ANAPRO fascicolo inesistente o inaccessibile.");
            return false;
        }

        //
        // 3 - Controllo esistenza Nodo di riferimento
        //
        if ($pronumNode && $proparNode) {
            $orgnode_rec = $this->proLib->GetOrgNode($pronumNode, 'codice', $proparNode);
        }
        if (!$orgnode_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Nodo di riferimento mancante.");
            return false;
        }

        //
        // 3.1 - Controllo esistenza ANAPRO per nodo padre di riferimento
        //
        if ($pronumNode && $proparNode) {
            $anapro_Node_rec = $this->proLib->GetAnapro($pronumNode, 'codice', $proparNode);
        }
        if (!$anapro_Node_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Documento padre di riferimento mancante o inaccessibile.");
            return false;
        }
        //
        // 4 - Controllo Presenza del protocollo nel fascicolo:
        //
         
         
         if ($this->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_rec_f['PROFASKEY'])) {
            $this->errCode = -1;
            $this->setErrMessage("Protocollo già presente nel fascicolo indicato.");
            return false;
        }

        //
        // 5 - Controllo esistenza PROGES repertorio pratica di riferimento
        //
        
        $proges_rec = $this->proLibPratica->GetProges($fascicolo, 'geskey');
        if (!$proges_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Repertorio pratica fascicolo inesistente o inaccessibile.");
            return false;
        }


        //
        // Gestione sottofascicolo
        //
        $baseSubKey = '';
        if ($propar == 'N') {
            switch ($anapro_Node_rec['PROPAR']) {
                case 'F':
                    $baseSubKey = $anapro_Node_rec['PROFASKEY'];
                    break;
                case "N":
                    if ($propar == "N") {
                        $baseSubKey = $anapro_Node_rec['PROSUBKEY'];
                    }
                    break;
                default :
                    $baseSubKey = '';
                    break;
            }

            if ($baseSubKey) {
                $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAPRO", "", "", 20);
                if ($retLock['status'] !== 0) {
                    $this->errCode = -1;
                    $this->setErrMessage("Errore in DBLock per l'inserimento del sottofascicolo.");
                    return false;
                }


                $sqlsubkey = "SELECT MAX(PROSUBKEY) AS ULTSUBKEY FROM ANAPRO WHERE PROSUBKEY LIKE '$baseSubKey-__' ORDER BY PROSUBKEY";
                $ultsubkey_rec = $this->proLib->getGenericTab($sqlsubkey, false);
                if (!$ultsubkey_rec) {
                    $ultkey = "00";
                } else {
                    list($skip, $ultkey) = explode("$baseSubKey-", $ultsubkey_rec['ULTSUBKEY']);
                }
                if ($ultkey === '') {
                    $this->errCode = -1;
                    $this->setErrMessage("Indice Sottofascicolo non accessibile.");
                    return false;
                }
                $ultKeyInt = intval($ultkey) + 1;
                $baseSubKey .= "-" . str_pad($ultKeyInt, 2, "0", STR_PAD_LEFT);
                $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
                if ($retUnlock == -1) {
                    $this->errCode = -1;
                    $this->setErrMessage("Errore in DBUnLock per l'inserimento del sottofascicolo.");
                }
            }
        }


        /*
         *  Collego anapro a chiave fascicolo principale solo se titolario corrisponde a profaskey
         * 
         *  Nuovo per protocolli su piu fascicoli
         * Se si tratta di una "N" sottofascicolo, comunque gli permetto di aggiornare i suoi dati.
         */
        if (($anapro_rec_f['PROCCF'] == $anapro_rec['PROCCF'] && $anapro_rec['PROFASKEY'] == '') || $anapro_rec['PROPAR'] == 'N') {
            $anapro_rec['PROARG'] = $fascicolo_rec['ORGCOD'];
            $anapro_rec['PROFASKEY'] = $fascicolo_rec['ORGKEY'];
            $anapro_rec['PROSUBKEY'] = $baseSubKey;
            $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAPRO', $anapro_rec, $update_Info)) {
                $this->errCode = -1;
                $this->setErrMessage("Aggiornamento documento fascicolato non avvenuta.");
                return false;
            }
        }
        //
        //
        // Collego protocollo ad azione se definita
        //
        $propas_rec = $this->proLibPratica->GetPropas($pronumNode, 'paspro', false, $proparNode);
        if (!$propas_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Azione di riferimento mancante.");
            return false;
        }

        // SERVE ANCORA ???
        if (!$this->insertPakdoc($model, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $propas_rec['PROPAK'])) {
            $this->errCode = -1;
            $this->setErrMessage("Collegamento ad azione pratica non avvenuto.");
            return false;
        }

        $sql2 = "SELECT MAX(CONNSEQ) AS ULTCONNSEQ FROM ORGCONN WHERE PRONUMPARENT='{$orgnode_rec['PRONUM']}' AND PROPARPARENT='{$orgnode_rec['PROPAR']}' AND ORGKEY='" . $fascicolo_rec['ORGKEY'] . "'";
        $utlconnseq_rec = $this->proLib->getGenericTab($sql2, false);
        if (!$utlconnseq_rec) {
            $ultconnseq = 10;
        } else {
            $ultconnseq = $utlconnseq_rec['ULTCONNSEQ'] + 10;
        }

        $nomeUtente = App::$utente->getKey('nomeUtente');
        $orgconn_rec = array();
        $orgconn_rec['ORGKEY'] = $fascicolo_rec['ORGKEY'];
        $orgconn_rec['PRONUM'] = $anapro_rec['PRONUM'];
        $orgconn_rec['PROPAR'] = $anapro_rec['PROPAR'];
        $orgconn_rec['PRONUMPARENT'] = $orgnode_rec['PRONUM'];
        $orgconn_rec['PROPARPARENT'] = $orgnode_rec['PROPAR'];
        $orgconn_rec['CONNSEQ'] = $ultconnseq;
        $orgconn_rec['CONNUTEINS'] = $nomeUtente;
        $orgconn_rec['CONNDATAINS'] = date('Ymd');
        $orgconn_rec['CONNORAINS'] = date('H:i:s');
        $orgconn_rec['CONNUTEMOD'] = $nomeUtente;
        $orgconn_rec['CONNDATAMOD'] = date('Ymd');
        $orgconn_rec['CONNORAMOD'] = date('H:i:s');
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ORGCONN', 'ROWID', $orgconn_rec);
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->setErrMessage("Collegamento Documento a struttura padre fallito. " . $exc->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Annullo il collegamento di un ANAPRO ad un fascicolo/sottofascicolo
     * @param type $model       Model Responsabile
     * @param type $fascicolo   Codice Fascicolo (cat cla sottoclass.anno.progresssivo) orgkey
     * @param type $pronum      Protocollo
     * @param type $propar      Tipo Protocollo
     * @param type $propak      Chiave azione di riferimento che indica il contenitore (HOOK A PROPAS E ANAPRO)
     * @return boolean
     */
    public function annullaDocumentoFascicolo($model, $fascicolo, $rowid_orgconn) {
        /*
         * INIZIO DEI CONTROLLI
         */

        $Orgconn_rec = $this->proLib->GetOrgConn($rowid_orgconn, 'rowid');
        if (!$Orgconn_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Errore, Connessione al fascicolo non trovata.");
            return false;
        }
        /*
         * Controllo esistenza Anapro da sbloccare
         */
        $anapro_rec = $this->proLib->GetAnapro($Orgconn_rec['PRONUM'], 'codice', $Orgconn_rec['PROPAR']);
        if (!$anapro_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Documento di riferimento mancante o inaccessibile.");
            return false;
        }
        //
        // controllo congruenza fascicolo/documento
        //
//        list($base_fascicolo, $skip) = explode('.', $fascicolo, 2);
//        if ($anapro_rec['PROCCF'] != $base_fascicolo) {
//            $this->errCode = -1;
//            $this->setErrMessage("Classificazione del documento incongruente con fascicolo di destinazione.");
//            return false;
//        }
        //
        // 1 - Controllo esistenza Anaorg (fascicolo)
        //
        $fascicolo_rec = $this->proLib->GetAnaorg($fascicolo, 'orgkey');
        if (!$fascicolo_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Fascicolo di riferimento mancante o inaccessibile.");
            return false;
        }

        //
        // 2 - Controllo esistenza ANAPRO del nodo fascicolo PROPAR = 'F'
        //
        $anapro_rec_f = $this->getAnaproFascicolo($fascicolo);
        if (!$anapro_rec_f) {
            $this->errCode = -1;
            $this->setErrMessage("ANAPRO fascicolo inesistente o inaccessibile.");
            return false;
        }

        /*
         * 4 - Controllo esistenza PROGES repertorio pratica di riferimento
         */
        $proges_rec = $this->proLibPratica->GetProges($fascicolo, 'geskey');
        if (!$proges_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Repertorio pratica fascicolo inesistente o inaccessibile.");
            return false;
        }
        /*
         * Gestione sottofascicolo RIMOSSA. Se serve si può copiare da insertDoc...
         * 
         *  FINE DEI CONTROLLI...
         */

        /*
         * Annullo l'orgconn 
         */
        $nomeUtente = App::$utente->getKey('nomeUtente');
        $Orgconn_rec['CONNUTEANN'] = $nomeUtente;
        $Orgconn_rec['CONNDATAANN'] = date('Ymd');
        $Orgconn_rec['CONNORAANN'] = date('H:i:s');
        $Orgconn_rec['CONNUTEMOD'] = $nomeUtente;
        $Orgconn_rec['CONNDATAMOD'] = date('Ymd');
        $Orgconn_rec['CONNORAMOD'] = date('H:i:s');

        $update_Info = 'Oggetto: Annullo ORGCONN. Riferimenti: ' . $Orgconn_rec['ORGKEY'] . ' - ' . $Orgconn_rec['PRONUM'] . " " . $Orgconn_rec['PROPAR'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'ORGCONN', $Orgconn_rec, $update_Info)) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in Annullamento documento su ORGCONN.");
            return false;
        }
        //
        // Annullo Collegamento anapro a chiave fascicolo
        // 
        // Nuovo se secondario non deve avvenire
        // 
        // solo se profaskey del anapro coincide con il  codice fascicolo di sgangio
        //
        if ($anapro_rec['PROFASKEY'] == $fascicolo) {
            $anapro_rec['PROARG'] = '';
            $anapro_rec['PROFASKEY'] = '';
            $anapro_rec['PROSUBKEY'] = '';
            $update_Info = 'Oggetto: Annullo Doc. - Anapro ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAPRO', $anapro_rec, $update_Info)) {
                $this->errCode = -1;
                $this->setErrMessage("Annullamento documento fascicolato su Anapro non avvenuta.");
                return false;
            }
        }
        //
        // Cerco azione di riferimento protocollo per cancellare PAKDOC
        // Annullo collegamento documento azione.
        //
        $propas_rec = $this->proLibPratica->GetPropas($Orgconn_rec['PRONUMPARENT'], 'paspro', false, $Orgconn_rec['PROPARPARENT']);
        if ($propas_rec) {
            if (!$this->cancellaPakdoc($model, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $propas_rec['PROPAK'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Crea il record di collegamento tra una azione di PROPAS e un protocollo di ANAPRO
     * @param type $model   Model responsabile delle registrazioni
     * @param type $pronum  Protocollo
     * @param type $propar  Tipo
     * @param type $propak  Chiave del passo
     * @return boolean
     */
    public function insertPakdoc($model, $pronum, $propar, $propak) {
        $pakdoc_rec = $this->proLibPratica->GetPakdoc(array("PRONUM" => $pronum, "PROPAR" => $propar, "PROPAK" => $propak), 'chiave', false);
        if ($pakdoc_rec) {
            $pakdoc_rec["PROPAK"] = $propak;
            $pakdoc_rec["PRONUM"] = $pronum;
            $pakdoc_rec["PROPAR"] = $propar;
            $update_info = "Aggiorno il Collegamento documento {$pronum} / {$propar} a Azione fascicolo {$propak}";
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'PAKDOC', $pakdoc_rec, $update_info)) {
                $this->errCode = -1;
                $this->setErrMessage("Errore aggiornamento collegamento documeto a Azione fascicolo");
                return false;
            }
        } else {
            $pakdoc_rec = array(
                "PROPAK" => $propak,
                "PRONUM" => $pronum,
                "PROPAR" => $propar
            );
            $insert_info = "Collegamento documento {$pronum} / {$pronum} a Azione fascicolo {$propak}";
            if (!$model->insertRecord($this->proLib->getPROTDB(), 'PAKDOC', $pakdoc_rec, $insert_info)) {
                $this->errCode = -1;
                $this->setErrMessage("Errore collegamento documeto a Azione fascicolo");
                return false;
            }
        }
        return true;
    }

    /**
     * Cancella il record di collegamento tra una azione di PROPAS e un protocollo di ANAPRO
     * @param type $model   Model responsabile delle registrazioni
     * @param type $pronum  Protocollo
     * @param type $propar  Tipo
     * @param type $propak  Chiave del passo
     * @return boolean
     */
    public function cancellaPakdoc($model, $pronum, $propar, $propak) {
        $pakdoc_rec = $this->proLibPratica->GetPakdoc(array("PRONUM" => $pronum, "PROPAR" => $propar, "PROPAK" => $propak), 'chiave', false);
        if ($pakdoc_rec) {
            $pakdoc_rec["PROPAK"] = $propak;
            $pakdoc_rec["PRONUM"] = $pronum;
            $pakdoc_rec["PROPAR"] = $propar;
            $delete_Info = "Cancello il Collegamento documento {$pronum} / {$propar} a Azione fascicolo {$propak}";
            if (!$model->deleteRecord($this->proLib->getPROTDB(), 'PAKDOC', $pakdoc_rec['ROWID'], $delete_Info)) {
                $this->errCode = -1;
                $this->setErrMessage("Errore in cancellazione collegamento documento con Azione fascicolo");
                return false;
            }
        } else {
            //Collegamento a Azione non trovata, dare errore?
            $this->errCode = -1;
            $this->setErrMessage("Collegamento ad azione non trovata.");
            return false;
        }
        return true;
    }

    public function getNewFascicoloPratica($model, $responsabile, $fascicolo_rec, $codiceProcedimento, $uffres, $gesprouff) {

//        $ProgressivoRepertorio = '';
//        $Anareparc_rec = $this->proLib->getAnareparc($repertoriofascicoli);
//        if ($Anareparc_rec) {
//            $ProgressivoRepertorio = $Anareparc_rec['PROGRESSIVO'] + 1;
//        } else {
//            return false;
//        }

        $proges_rec = array();
        $proges_rec['GESKEY'] = $fascicolo_rec['ORGKEY'];
        $proges_rec['GESREP'] = $this->repertoriofascicoli;
        $proges_rec['GESNPR'] = '';
        $proges_rec['GESPAR'] = '';
        $proges_rec['GESRES'] = $responsabile;
        $proges_rec['GESDRE'] = $fascicolo_rec['ORGAPE'];
        $proges_rec['GESDRI'] = date('Ymd');
        $proges_rec['GESORA'] = date('H:i:s');
        $proges_rec['GESPRO'] = $codiceProcedimento;
        $proges_rec['GESUFFRES'] = $uffres;
        $proges_rec['GESOGG'] = $fascicolo_rec['ORGDES'];
        $proges_rec['GESPROUTE'] = App::$utente->getKey('nomeUtente');
        $proges_rec['GESPROUFF'] = $gesprouff;
        $datiAggiungi = array(
            "PROGES_REC" => $proges_rec,
            "ANADES_REC" => array(),
            "tipoInserimento" => "FASCICOLAZIONE",
            "UFF" => $uffres,
            "RES" => $responsabile,
            "GESPROUFF" => $gesprouff
        );
        $retAggiungi = $this->aggiungiPratica($model, $datiAggiungi);
        if (!$retAggiungi) {
            return false;
        }
        return $retAggiungi;
    }

    public function aggiungiPratica($model, $dati) {
        //
        // Prenoto N.Pratica
        //
        $proges_rec = $dati["PROGES_REC"];

        $retLock = $this->proLibPratica->bloccaProgressivoPratica($proges_rec['GESREP']);
        if (!$retLock) {
            $this->errCode = -1;
            $this->setErrMessage("Accesso esclusivo al progressivo pratica fallito.");
            return false;
        }

        $procedimento = $this->proLibPratica->leggiProgressivoPratica(date('Y'), $proges_rec['GESREP']);
        if (!$procedimento) {
            $this->errCode = -1;
            $this->setErrMessage('Prenotazione Numero Pratica Fallito.');
            return false;
        }
        $procedimento = $proges_rec['GESREP'] . date('Y') . $procedimento;
        $Ctr_Proges_rec = $this->proLibPratica->GetProges($procedimento);
        if ($Ctr_Proges_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Il numero pratica $procedimento è già esistente.<br>Riallineare il progressivo all'ultimo numero di pratica");
            return false;
        }
        if ($dati["PROGES_REC"]['GESPRA']) {
            $CtrGespra_Proges_rec = $this->proLibPratica->GetProges($dati["PROGES_REC"]['GESPRA'], "richiesta");
            if ($CtrGespra_Proges_rec) {
                $this->errCode = -1;
                $this->setErrMessage("La richiesta on-line " . $dati["PROGES_REC"]['GESPRA'] . " risulta essere già caricata.");
                return false;
            }
        }
        //
        // Aggiungo testata
        //
        $proges_rec['GESNUM'] = $procedimento;
        $insert_Info = 'Oggetto : Inserisco pratica n. ' . $proges_rec['GESNUM'];
        if (!$model->insertRecord($this->proLib->getPROTDB(), 'PROGES', $proges_rec, $insert_Info)) {
            $this->errCode = -1;
            $this->setErrMessage("Inserimento Testata Pratica " . $proges_rec['GESNUM'] . " Fallito.");
            return false;
        }
        $progesNew_rec = $this->proLibPratica->GetProges($procedimento, 'codice');
        if (!$progesNew_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Inserimento Pratica Fallito.");
            return false;
        }
        if (!$this->proLibPratica->aggiornaProgressivoPratica($proges_rec['GESREP'], intval(substr($procedimento, 14, 6)))) {
            $this->proLibPratica->sbloccaProgressivoPratica($retLock);
            $this->errCode = -1;
            $this->setErrMessage("Aggiornamento progressivo Pratiche fallito.</br>Contattare l'assistenza Software");
            return false;
        }
        $this->proLibPratica->sbloccaProgressivoPratica($retLock);

        //$proges_new = $this->proLibPratica->GetProges($retAggiungi, 'rowid');
        $rowid_propas = $this->creaAzioneFascicolo($progesNew_rec['GESNUM'], $progesNew_rec['GESKEY'], $dati['RES'], $dati['UFF'], $dati['GESPROUFF']);
        if (!$rowid_propas) {
            return false;
        }

        //
        // Aggiunta records passi e delle tabelle collegate
        //
        switch ($dati['tipoInserimento']) {
            case "FASCICOLAZIONE":
            case "ANAGRAFICA":
                //
                // Aggiungo solo passi
                //
                if ($progesNew_rec['GESPRO']) {
                    if (!$this->proLibPratica->ribaltaPassi($this, $model, $procedimento, $dati)) {
                        $this->errCode = -1;
                        $this->setErrMessage("Caricamento automatico azioni procedimento fallita.");
                        return false;
                    }
                }
                break;
        }
        $this->proLibPratica->sincronizzaStato($procedimento);
        return $progesNew_rec['ROWID'];
    }

    public function getNewOrgTaskAnapro($fascicolo_rec, $anapro_rec = null, $descrizione = null, $datiFascicolazione = array()) {
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAORG", "", "", 20);
        if ($retLock['status'] !== 0) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in DBLock per l'inserimento del Azione.");
            return false;
        }
        $risultato = $this->getNewOrgAnaproEsegui($fascicolo_rec, $anapro_rec, $descrizione, "T", $datiFascicolazione);
        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock == -1) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in DBUnLock per l'inserimento del Azione.");
        }
        return $risultato;
    }

    public function getNewOrgNodeAnapro($fascicolo_rec, $anapro_rec = null, $descrizione = null, $datiFascicolazione = array()) {
        $retLock = ItaDB::DBLock($this->proLib->getPROTDB(), "ANAORG", "", "", 20);
        if ($retLock['status'] !== 0) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in DBLock per l'inserimento del SottoFascicolo.");
            return false;
        }
        $risultato = $this->getNewOrgAnaproEsegui($fascicolo_rec, $anapro_rec, $descrizione, "N", $datiFascicolazione);
        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock == -1) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in DBUnLock per l'inserimento del SottoFascicolo.");
        }
        return $risultato;
    }

    public function getNewOrgAnaproEsegui($fascicolo_rec, $anapro_rec = null, $descrizione = null, $tipo = '', $datiFascicolazione = array()) {
        if (!$tipo) {
            return false;
        }
        if (!$anapro_rec) {
            //
            // Se non fornito creo anapro per il nodo sottofascicolo
            //
            App::requireModel('proProtocolla.class');
            $protObj = new proProtocolla();

            /* Permesso utilizzo dinamico dell'utente */
            if ($datiFascicolazione['UTENTE_INS']) {
                $nomeUtente = $datiFascicolazione['UTENTE_INS'];
                $profilo = proSoggetto::getProfileFromNomeUtente($nomeUtente);
            } else {
                $nomeUtente = App::$utente->getKey('nomeUtente');
                $profilo = proSoggetto::getProfileFromIdUtente();
            }
            if (!$profilo['COD_SOGGETTO']) {
                $this->errCode = -1;
                $this->setErrMessage("Configurare il Profilo Utente con il Destinatario della Pianta Organica.");
                return false;
            }
            $OraFas = date('H:i:s');
            if ($datiFascicolazione['ORA_FASCICOLO']) {
                $OraFas = $datiFascicolazione['ORA_FASCICOLO'];
            }
            $DataFas = date('Ymd');
            if ($fascicolo_rec['ORGAPE']) {
                $DataFas = $fascicolo_rec['ORGAPE'];
            }

            $codice = $protObj->PrenotaDocumentoAzione('LEGGI', $fascicolo_rec['ORGANN'], $tipo);
            $anapro_new = array();
            $anapro_new['PRONUM'] = $codice;
            $anapro_new['PROPAR'] = $tipo;
            $anapro_new['PRODAR'] = $fascicolo_rec['ORGAPE'];
            $anapro_new['VERSIONE_T'] = $fascicolo_rec['VERSIONE_T'];
            $anapro_new['PROCAT'] = substr($fascicolo_rec['ORGCCF'], 0, 4);
            $anapro_new['PROCCA'] = substr($fascicolo_rec['ORGCCF'], 0, 8);
            $anapro_new['PROCCF'] = $fascicolo_rec['ORGCCF'];
            $anapro_new['PROARG'] = $fascicolo_rec['ORGCOD'];
            $anapro_new['PRORDA'] = $fascicolo_rec['ORGAPE'];
            $anapro_new['PROROR'] = $OraFas;
            $anapro_new['PROORA'] = $OraFas;
            $anapro_new['PROUOF'] = $datiFascicolazione['GESPROUFF'];
            $anapro_new['PROUTE'] = $nomeUtente;
            $anapro_new['PROLOG'] = "999" . substr(App::$utente->getKey('nomeUtente'), 0, 7) . date("d/m/Y", strtotime($DataFas));
            $anapro_new['PROFASKEY'] = $fascicolo_rec['ORGKEY'];

            $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
            $anapro_new['PROCON'] = $anamed_rec['MEDCOD'];
            $anapro_new['PRONOM'] = $anamed_rec['MEDNOM'];

            $segnatura = proSegnatura::getStringaSegnatura($anapro_new);
            if (!$segnatura) {
                $this->errCode = -1;
                $this->setErrMessage("Calcolo segnatura sottofascicolo fallita.");
                return false;
            }
            $anapro_new['PROSEG'] = $segnatura;
            try {
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $anapro_new);
                $rowid_anapro_Azione = $this->proLib->getPROTDB()->getLastId();
                $anaproNew_rec = $this->proLib->GetAnapro($anapro_new['PRONUM'], 'codice', $anapro_new['PROPAR']);
                if (!$anaproNew_rec) {
                    $this->errCode = -1;
                    $this->setErrMessage("Documento sotto-fascicolo inaccessibile");
                    return false;
                }
                $risultato = $protObj->saveOggetto($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $descrizione);
                if (!$risultato) {
                    $this->errCode = -1;
                    $this->setErrMessage("Aggiornamento oggetto sottofascicolo fallito.");
                    return false;
                }
                $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
                $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
                $ananom_rec['NOMPAR'] = $anaproNew_rec['PROPAR'];
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANANOM', 'ROWID', $ananom_rec);

                $this->registraResponsabile($datiFascicolazione, $anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR']);

                $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
                $iter->sincIterProtocollo();
            } catch (Exception $exc) {
                $this->errCode = -1;
                $this->setErrMessage("Inserimento documento sottofascicolo fallito: " . $exc->getMessage());
                return false;
            }
            $anapro_rec = $anaproNew_rec;
        }
        if ($tipo == "N" || $tipo == 'T') {
            //
            // Creo il nodo sotto fascicolo al nodo padre.
            //
            $Orgnode_rec = array();
            $Orgnode_rec['ORGKEY'] = $anapro_rec['PROFASKEY'];
            $Orgnode_rec['PRONUM'] = $anapro_rec['PRONUM'];
            $Orgnode_rec['PROPAR'] = $anapro_rec['PROPAR'];
            try {
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'ORGNODE', 'ROWID', $Orgnode_rec);
            } catch (Exception $exc) {
                $this->errCode = -1;
                $this->setErrMessage("COllegamento sottofascicolo al nodo padre fallito: " . $exc->getMessage());
                return false;
            }
        }
        return $rowid_anapro_Azione;
    }

    public function getNewFascicoloAnapro($fascicolo_rec, $datiFascicolazione) {
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();


        /* Permesso utilizzo dinamico dell'utente */
        if ($datiFascicolazione['UTENTE_INS']) {
            $nomeUtente = $datiFascicolazione['UTENTE_INS'];
            $profilo = proSoggetto::getProfileFromNomeUtente($nomeUtente);
        } else {
            $nomeUtente = App::$utente->getKey('nomeUtente');
            $profilo = proSoggetto::getProfileFromIdUtente();
        }
        if (!$profilo['COD_SOGGETTO']) {
            //Out::msgStop("Attenzione!", "Configurare il Profilo Utente con il Destinatario della Pianta Organica.");
            $this->errCode = -1;
            $this->setErrMessage("Profilo Utente non accessibile");
            return false;
        }
        /*
         * Orario fascicolo passato da datiFascicolazione
         */
        $OraFas = date('H:i:s');
        if ($datiFascicolazione['ORA_FASCICOLO']) {
            $OraFas = $datiFascicolazione['ORA_FASCICOLO'];
        }
        $DataFas = date('Ymd');
        if ($fascicolo_rec['ORGAPE']) {
            $DataFas = $fascicolo_rec['ORGAPE'];
        }
        $codice = $protObj->PrenotaDocumentoFascicolo('LEGGI', $fascicolo_rec['ORGANN']);
        $anapro_new = array();
        $anapro_new['PRONUM'] = $codice;
        $anapro_new['PROPAR'] = 'F';
        $anapro_new['PRODAR'] = $fascicolo_rec['ORGAPE'];
        $anapro_new['VERSIONE_T'] = $fascicolo_rec['VERSIONE_T'];
        $anapro_new['PROCAT'] = substr($fascicolo_rec['ORGCCF'], 0, 4);
        $anapro_new['PROCCA'] = substr($fascicolo_rec['ORGCCF'], 0, 8);
        $anapro_new['PROCCF'] = $fascicolo_rec['ORGCCF'];
        $anapro_new['PROARG'] = $fascicolo_rec['ORGCOD'];
        $anapro_new['PRORDA'] = $fascicolo_rec['ORGAPE'];
        $anapro_new['PROROR'] = $OraFas;
        $anapro_new['PROORA'] = $OraFas;
        $anapro_new['PROUTE'] = $nomeUtente;
        $anapro_new['PROUOF'] = $datiFascicolazione['GESPROUFF'];
        $anapro_new['PROLOG'] = "999" . substr($nomeUtente, 0, 7) . date("d/m/Y", strtotime($DataFas));
        $anapro_new['PROFASKEY'] = $fascicolo_rec['ORGKEY'];
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        $anapro_new['PROCON'] = $anamed_rec['MEDCOD'];
        $anapro_new['PRONOM'] = $anamed_rec['MEDNOM'];
        //
        if ($datiFascicolazione['PROCODTIPODOC']) {
            $anapro_new['PROCODTIPODOC'] = $datiFascicolazione['PROCODTIPODOC'];
        }

        $segnatura = proSegnatura::getStringaSegnatura($anapro_new);
        if (!$segnatura) {
            $this->errCode = -1;
            $this->setErrMessage("Errore nella codifica della segnatura del fascicolo.");
            return false;
        }
        $anapro_new['PROSEG'] = $segnatura;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $anapro_new);
            $rowid = $this->proLib->getPROTDB()->getLastId();
            $anaproNew_rec = $this->proLib->GetAnapro($anapro_new['PRONUM'], 'codice', $anapro_new['PROPAR']);
            if (!$anaproNew_rec) {
                $this->errCode = -1;
                $this->setErrMessage("Errore nella creazione instanza documento fascicolo.");
                return false;
            }
            /* @var $protObj proProtocolla */
            $risultato = $protObj->saveOggetto($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $fascicolo_rec['ORGDES']);
            if (!$risultato) {
                $this->errCode = -1;
                $this->setErrMessage("Errore in salvataggio descrizione fascicolo");
                return false;
            }

            $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
            $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
            $ananom_rec['NOMPAR'] = $anaproNew_rec['PROPAR'];
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANANOM', 'ROWID', $ananom_rec);
            $this->registraResponsabile($datiFascicolazione, $anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR']);
            $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
            if (!$iter) {
                $this->errCode = -1;
                $this->setErrMessage("Creazione Istanza Iter Fallita.");
                return false;
            }
            if (!$iter->sincIterProtocollo()) {
                $this->errCode = -1;
                $this->setErrMessage("Aggiornamento iter fallito.");
                return false;
            }



            return $rowid;
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in creazione istanza documento fascicolo: " . 'ditta: ' . App::$utente->getKey('ditta') . " - " . $exc->getTraceAsString());
            return false;
        }
    }

    public function AnnullaChiudiPratica($rowidStato, $tipo, $pratica) {
        if ($tipo == "ANNULLA") {
            $operaz = "Annullamento";
        } elseif ($tipo == "CHIUDI") {
            $operaz = "Chiusura";
        }
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $pratica;
        $Propas_rec['PROPAK'] = $this->proLibPratica->PropakGenerator($pratica);
        $Propas_rec['PRODPA'] = "$operaz pratica n. $pratica";
        $Propas_rec['PRODTP'] = "$operaz pratica";
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PROSTATO'] = $rowidStato;
        $Propas_rec['PROINI'] = $Propas_rec['PROFIN'] = date("Ymd");
        $Propas_rec['PROUTEADD'] = $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $Propas_rec['PRODATEADD'] = $Propas_rec['PRODATEEDIT'] = date("Ymd");
        $Propas_rec['PROORAADD'] = $Propas_rec['PROORAEDIT'] = date("H:i:s");
        $Propas_rec['PRORPA'] = proSoggetto::getCodiceSoggettoFromIdUtente();
        try {
            $nrow = ItaDB::DBInsert($this->proLib->getPROTDB(), "PROPAS", 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }
        $this->proLibPratica->ordinaPassi($pratica);
        $this->proLibPratica->sincronizzaStato($pratica);
        return true;
    }

    public function CaricaIter($fascicolo) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $sql = "
            SELECT
                ARCITE.ROWID,
                ARCITE.ITEPRO,
                ARCITE.ITEPAR,
                ARCITE.ITEDAT,
                ARCITE.ITEORA,
                ARCITE.ITEANN, 
                ARCITE.ITEANT,                 
                ARCITEMIT.ITEDES AS ITEDESMIT, 
                ARCITEMIT.ITEUFF AS ITEUFFMIT, 
                ARCITE.ITEDES, 
                ARCITE.ITEUFF, 
                ARCITE.ITENODO,
                ARCITE.ITEDLE,
                ARCITE.ITEFIN,
                ARCITE.ITETERMINE,
                ARCITE.ITEDATACC,
                ARCITE.ITENOTEACC,
                ARCITE.ITEDATRIF,
                ARCITE.ITEMOTIVO,
                ARCITE.ITEGES,
                ARCITE.ITESTATO,
                ARCITE.ITENTRAS,
                ANAPRO.PROFASKEY,
                ORGCONN.PRONUMPARENT,
                ORGCONN.PROPARPARENT,
                PAKDOC.PROPAK,
                PROPAS.PRODPA
            FROM ANAPRO
                LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
                LEFT OUTER JOIN ARCITE ARCITEMIT ON ARCITEMIT.ITEKEY=ARCITE.ITEPRE
                LEFT OUTER JOIN ORGCONN ORGCONN ON ORGCONN.PRONUM=ARCITE.ITEPRO AND ORGCONN.PROPAR=ARCITE.ITEPAR AND ORGCONN.CONNDATAANN = ''
                LEFT OUTER JOIN PAKDOC PAKDOC ON ANAPRO.PRONUM=PAKDOC.PRONUM AND ANAPRO.PROPAR=PAKDOC.PROPAR
                LEFT OUTER JOIN PROPAS PROPAS ON PAKDOC.PROPAK=PROPAS.PROPAK
            WHERE ARCITE.ITENODO <> 'MIT' AND ARCITE.ITENODO <> 'ANN' AND ANAPRO.PROFASKEY='$fascicolo'
            ORDER BY ARCITE.ITEDAT,ARCITE.ITEORA,ARCITE.ROWID";
        $iter_tab = $this->proLib->getGenericTab($sql);
        $arrayIter = array();
        if ($iter_tab) {
            foreach ($iter_tab as $iter_rec) {
                $key = $iter_rec['ROWID'];
//                switch ($iter_rec['PROPARPARENT']) {
//                    case "F":
//                        $parentTitle = "Fascicolo: " . $fascicolo;
//                        $parentIcon = '<span class="ita-tooltip ita-icon ita-icon-open-folder-24x24" title="' . htmlspecialchars($parentTitle) . '"   style="margin-right:2px;display:inline-block"></span>';
//                        $arrayIter[$key]['PARENT'] = '<div style="display:inline-block;" class="ita-html">' . $parentIcon . '</div>';
//                        break;
//                    case "N":
//                        $parentTitle = "Sotto-Fascicolo " . substr($iter_rec['PRONUMPARENT'], 5);
//                        $parentIcon = '<span class="ita-tooltip ita-icon ita-icon-sub-folder-24x24" title="' . htmlspecialchars($parentTitle) . '"   style="margin-right:6px;display:inline-block"></span>';
//                        $arrayIter[$key]['PARENT'] = '<div style="display:inline-block;" class="ita-html">' . $parentIcon . intval(substr($iter_rec['PRONUMPARENT'], 5)) . '</div>';
//                        break;
//                    default:
//                        $arrayIter[$key]['PARENT'] = '';
//                        break;
//                }
                switch ($iter_rec['ITEPAR']) {
                    case "A":
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-register-document-24x24" title="Arrivo"   style="margin-right:6px;display:inline-block"></span>' . substr($iter_rec['ITEPRO'], 4) . ' / ' . substr($iter_rec['ITEPRO'], 0, 4) . ' - ' . $iter_rec['ITEPAR'];
                        break;
                    case "P":
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-register-document-24x24" title="Partenza" style="margin-right:6px;display:inline-block"></span>' . substr($iter_rec['ITEPRO'], 4) . ' / ' . substr($iter_rec['ITEPRO'], 0, 4) . ' - ' . $iter_rec['ITEPAR'];
                        break;
                    case "C":
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-register-document-24x24" title="Comunicazione" style="margin-right:6px;display:inline-block"></span>' . substr($iter_rec['ITEPRO'], 4) . ' / ' . substr($iter_rec['ITEPRO'], 0, 4) . ' - ' . $iter_rec['ITEPAR'];
                        break;
                    case "F":
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-open-folder-24x24" title="Fascicolo" style="margin-right:6px;display:inline-block"></span>'; // . $iter_rec['PROFASKEY'];
                        break;
                    case "N":
                        $anapro_fas = $this->proLib->GetAnapro($iter_rec['ITEPRO'], 'codice', $iter_rec['ITEPAR']);
                        $subF = substr($anapro_fas['PROSUBKEY'], strpos($anapro_fas['PROSUBKEY'], '-') + 1);
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-sub-folder-24x24" title="Fascicolo" style="margin-right:6px;display:inline-block"></span>' . $subF;
                        break;
                    case "T":
                        $anapro_fas = $this->proLib->GetAnapro($iter_rec['ITEPRO'], 'codice', $iter_rec['ITEPAR']);
                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-edit-24x24" title="Azione" style="margin-right:6px;display:inline-block"></span>' . $subF;
//                        $arrayIter[$key]['DESCPROT'] = '<span class="ita-icon ita-icon-edit-24x24" title="Azione" style="margin-right:6px;display:inline-block"></span>' . substr($iter_rec['ITEPRO'], 4) . ' / ' . substr($iter_rec['ITEPRO'], 0, 4);
                        break;
                    default:
                        $arrayIter[$key]['DESCPROT'] = '';
                        break;
                }
                $arrayIter[$key]['ROWID'] = $iter_rec['ROWID'];
                $arrayIter[$key]['ITEDAT'] = $iter_rec['ITEDAT'];
                $arrayIter[$key]['ITEORA'] = date('H:i', strtotime($iter_rec['ITEORA']));
                if ($iter_rec['ITEDESMIT']) {//&& $iter_rec['ITEUFFMIT']) {
                    $style = 'style = "font-weight:900;color:#BE0000;"';
                    $anamed_rec = $this->proLib->GetAnamed($iter_rec['ITEDESMIT'], 'codice', 'no', false, true);
                    $arrayIter[$key]['ITERMITTENTE'] = "<p $style>{$anamed_rec['MEDNOM']}</p>";
                    $soggettoMit = proSoggetto::getInstance($this->proLib, $iter_rec['ITEDESMIT'], $iter_rec['ITEUFFMIT']);
                    if ($soggettoMit) {
                        $record = $soggettoMit->getSoggetto();
                        if ($record) {
                            $arrayIter[$key]['ITERMITTENTE'] = $record['DESCRIZIONESOGGETTO'];
//                            if ($record['RUOLO']) {
//                                $arrayIter[$key]['ITERMITTENTE'] .= ' - ' . $record['RUOLO'];
//                            }
//                            $arrayIter[$key]['ITERMITTENTE'] .= ' - ' . $record['DESCRIZIONEUFFICIO'];
//                            if ($record['SERVIZIO']) {
//                                $arrayIter[$key]['ITERMITTENTE'] .= ' - ' . $record['SERVIZIO'];
//                            }
                        }
                    }
                }

                if ($iter_rec['ITEDES']) {
                    $style = 'style = "font-weight:900;color:#BE0000;"';
                    $anamed_rec = $this->proLib->GetAnamed($iter_rec['ITEDES'], 'codice', 'no', false, true);
                    $arrayIter[$key]['ITERDESTINATARIO'] = "<p $style>{$anamed_rec['MEDNOM']}</p>";
                    $soggetto = proSoggetto::getInstance($this->proLib, $iter_rec['ITEDES'], $iter_rec['ITEUFF']);
                    if ($soggetto) {
                        $record = $soggetto->getSoggetto();
                        if ($record) {
                            $arrayIter[$key]['ITERDESTINATARIO'] = $record['DESCRIZIONESOGGETTO'];
//                            if ($record['RUOLO']) {
//                                $arrayIter[$key]['ITERDESTINATARIO'] .= ' - ' . $record['RUOLO'];
//                            }
//                            $arrayIter[$key]['ITERDESTINATARIO'] .= ' - ' . $record['DESCRIZIONEUFFICIO'];
//                            if ($record['SERVIZIO']) {
//                                $arrayIter[$key]['ITERDESTINATARIO'] .= ' - ' . $record['SERVIZIO'];
//                            }
                        }
                    }
                    //$styleMyIter="background:lightgreen;";
                    //$styleMyIterClosed="background:lightsalmon;";
                    if ($profilo['COD_SOGGETTO'] == $iter_rec['ITEDES']) {
                        if (!$iter_rec['ITEFIN']) {
                            $arrayIter[$key]['ITERDESTINATARIO'] = '<div style="height:100%;' . $styleMyIter . '"><span style="color:black;font-weight:bold;">' . $arrayIter[$key]['ITERDESTINATARIO'] . '</span>';
                        } else {
                            $arrayIter[$key]['ITERDESTINATARIO'] = '<div style="height:100%;' . $styleMyIterClosed . '"><span style="color:black;font-weight:bold;">' . $arrayIter[$key]['ITERDESTINATARIO'] . '</span>';
                        }
                    }
                }

                $endDate = ($iter_rec['ITEFIN']) ? $iter_rec['ITEFIN'] : date('Ymd');
                $beginDate = $iter_rec['ITEDAT'];
                $gg = $this->proLib->Diff_Date_toGiorni($beginDate, $endDate);
                $opacity = (($gg <= 40) ? $gg * 2.5 : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity);";
                $arrayIter[$key]['ITERGIORNI'] = '<div style="height:100%;padding-left:2px;font-weight:bold;color:black;' . $opacity . '"><br><span style="opacity:1.00;">' . $gg . '</span></div>';

                $itenodott = $this->GetDescrizioneNodo($iter_rec);
                $arrayIter[$key]['ITENODODESC'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $itenodott . '">' . $iter_rec['ITENODO'] . '</span></div>';
                switch ($iter_rec['ITENODO']) {
                    case "TRX":
                    case "ASF":
                    case "ASS":
                        $arrayIter[$key]['ITEANN'] = '<div class="ita-Wordwrap">' . $iter_rec['ITEANN'] . '</div>';
                        break;
                    default:
                        break;
                }
                $arrayIter[$key]['ITEDLE'] = $iter_rec['ITEDLE'];
                $arrayIter[$key]['ITERCHIUSO'] = $iter_rec['ITEFIN'];
                $arrayIter[$key]['ITETERMINE'] = $iter_rec['ITETERMINE'];
                $arrayIter[$key]['ITERACCRIF'] = '';
                $arrayIter[$key]['ITERMOTIVO'] = '';
                if ($iter_rec['ITEDATACC']) {
                    $arrayIter[$key]['ITERACCRIF'] = $iter_rec['ITEDATACC'];
                    $arrayIter[$key]['ITERMOTIVO'] = $iter_rec['ITENOTEACC'];
                }
                if ($iter_rec['ITEDATRIF']) {
                    $arrayIter[$key]['ITERACCRIF'] = $iter_rec['ITEDATRIF'];
                    $arrayIter[$key]['ITERMOTIVO'] = $iter_rec['ITEMOTIVO'];
                }
                $arrayIter[$key]['ITERGEST'] = 0;
                if ($iter_rec['ITEGES'] == 1) {
                    $arrayIter[$key]['ITERGEST'] = 1;
                }
                $arrayIter[$key]['ITERSTATO'] = '';
                if ($iter_rec['ITESTATO'] == '1' || $iter_rec['ITESTATO'] == '3') {
                    $rifToolTip = "<span style=\"color:red;font-weight:bold;\">RIFIUTATO:</span><br>" . $iter_rec['ITEMOTIVO'];
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span title="' . htmlspecialchars($rifToolTip) . '" class="ita-tooltip ita-icon ita-icon-divieto-24x24"></span></div>';
                } else if ($iter_rec['ITESUS'] != '') {
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span title="Inviato" class="ita-tooltip ita-icon ita-icon-check-grey-24x24"></span</div>>';
                } else if ($iter_rec['ITEFIN'] != '') {
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span title="Chiuso" class="ita-tooltip ita-icon ita-icon-check-red-24x24"></span</div>>';
                } elseif ($arrayIter[$key]['ITERACCRIF'] != '') {
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span title="Accettato" class="ita-tooltip ita-icon ita-icon-check-green-24x24"></span></div>';
                } elseif ($iter_rec['ITETERMINE'] <> '' && $iter_rec['ITETERMINE'] < date("Ymd")) {
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span class="ita-icon ita-icon-lock-24x24"></span>';
                } else if ($iter_rec['ITEDLE'] === '') {
                    $arrayIter[$key]['ITERSTATO'] = '<div class="ita-html"><span title="Da Visionare" class="ita-tooltip ita-icon "></span</div>>';
                }
                $arrayIter[$key]['ITENTRAS'] = $iter_rec['ITENTRAS'];
            }
        }
        return $arrayIter;
    }

    public function getAlberoFascicolo($numeroProcedimento, $where) {
        $proges_rec = $this->proLibPratica->GetProges($numeroProcedimento);
        $anapro_F_rec = $this->getAnaproFascicolo($proges_rec['GESKEY']);
        $anaogg_F_rec = $this->proLib->GetAnaogg($anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR']);
        $orgnode_rec = $this->proLib->GetOrgNode($anapro_F_rec['PRONUM'], 'codice', $anapro_F_rec['PROPAR']);

        $nodePronum = $orgnode_rec['PRONUM'];
        $nodePropar = $orgnode_rec['PROPAR'];

        $level = 0;
        $inc = 1;
        $documenti_tree = array();
        $documenti_tree[$inc]['level'] = $level;
        $documenti_tree[$inc]['parent'] = '';
        $documenti_tree[$inc]['isLeaf'] = 'false';
        $documenti_tree[$inc]['expanded'] = 'true';
        $documenti_tree[$inc]['loaded'] = 'true';
        $documenti_tree[$inc]['ORGNODEKEY'] = "PRO-" . $nodePronum . $nodePropar;
        $documenti_tree[$inc]['ORGNODEICO'] = '<div class="ita-html"><span style="height:16px;background-size:50%;margin:2px;" title="Fascicolo ' . $anapro_F_rec['PROFASKEY'] . '" class="ita-tooltip ita-icon ita-icon-open-folder-32x32">Fascicolo</span></div>';
        $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="Aggiungi Elementi al fascicolo" class="ita-tooltip ui-icon ui-icon-plus">Aggiungi Elementi al fascicolo</span></div>';
        $documenti_tree[$inc]['NOTE'] = $anaogg_F_rec['OGGOGG'];

        $matriceSelezionati = $this->CaricaElementi_tree($documenti_tree, $nodePronum, $nodePropar, $where, $level + 1);
        return $matriceSelezionati;
    }

    private function CaricaElementi_tree($documenti_tree, $nodePronum, $nodePropar, $where, $level) {
        $sql = "
            SELECT
                ORGCONN.PRONUM,
                ORGCONN.PROPAR,
                ANAPRO.PRODAR,
                ANAPRO.PROORA,
                ANAPRO.PRORISERVA,
                ANAPRO.PROTSO
            FROM
                ORGCONN
            LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
            WHERE
                ORGCONN.CONNDATAANN = '' AND 
                ORGCONN.PRONUMPARENT='{$nodePronum}' AND ORGCONN.PROPARPARENT='{$nodePropar}'";
        if ($where['PROTOCOLLI']) {
            $sql .= " AND (1<>1 {$where['PROTOCOLLI']} OR ANAPRO.PROPAR = 'F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T')";
            //$sql .= " AND (1=1 {$where['PROTOCOLLI']} OR ANAPRO.PROPAR = 'F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T')";
        }

        $sql .= " ORDER BY ORGCONN.CONNSEQ";
        $orgconn_tab = $this->proLib->getGenericTab($sql);
        if ($orgconn_tab) {
            foreach ($orgconn_tab as $orgconn_rec) {
                $anaogg_conn_rec = $this->proLib->GetAnaogg($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR']);
                $inc = count($documenti_tree) + 1;
                $documenti_tree[$inc]['level'] = $level;
                $documenti_tree[$inc]['parent'] = "PRO-" . $nodePronum . $nodePropar;
                $documenti_tree[$inc]['isLeaf'] = 'false';
                $documenti_tree[$inc]['expanded'] = 'true';
                $documenti_tree[$inc]['loaded'] = 'true';
                $documenti_tree[$inc]['ORGNODEKEY'] = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];
                switch ($orgconn_rec['PROPAR']) {
                    case "F":
                        $icon = "ita-icon-open-folder-32x32";
                        $tooltip = "Fascicolo";
                        $tooltipAzione = "Aggiungi Elementi al Fascicolo";
                        $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="' . $tooltipAzione . '" class="ita-tooltip ui-icon ui-icon-plus">' . $tooltipAzione . '</span></div>';
                        break;
                    case "N":
                        $icon = "ita-icon-sub-folder-32x32";
                        $tooltip = "Sotto-Fascicolo";
                        $tooltipAzione = "Aggiungi Elementi al Sotto-Fascicolo";
                        $documenti_tree[$inc]['ADDAZIONE'] = '<div class="ita-html"><span title="' . $tooltipAzione . '" class="ita-tooltip ui-icon ui-icon-plus">' . $tooltipAzione . '</span></div>';
                        break;
                    case "T":
                        $icon = "ita-icon-edit-32x32";
                        $tooltip = "Azione";
                        break;
                    case "A":
                    case "P":
                    case "C":
                        $icon = "ita-icon-register-document-32x32";
                        $tooltip = "Protocollo";
                        break;
                    default:
                        break;
                }
                $documenti_tree[$inc]['ORGNODEICO'] = '<div class="ita-html"><span style="height:16px;background-size:50%;margin:2px;" title="' . $tooltip . '" class="ita-tooltip ita-icon ' . $icon . '">' . $tooltip . '</span></div>';
                $documenti_tree[$inc]['NOTE'] = $anaogg_conn_rec['OGGOGG'];
                $documenti_tree = $this->CaricaElementi_tree($documenti_tree, $orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $where, $level + 1);
            }
        }
        return $documenti_tree;
    }

    public function registraResponsabile($datiFascicolazione, $pronum, $propar) {
        if ($datiFascicolazione['RES']) {

            $anades_check_esistenza = $this->proLib->GetAnades($pronum, 'codice', true, $propar, 'T', " AND DESCOD='{$datiFascicolazione['RES']}' AND DESCUF='{$datiFascicolazione['UFF']}'");
            if (!$anades_check_esistenza) {
                $anades_check = $this->proLib->GetAnades($pronum, 'codice', true, $propar, 'T');
                foreach ($anades_check as $anades_check_rec) {
                    //$anades_check_rec['SAVEDATA'] = date('Ymd');
                    //$anades_check_rec['SAVEORA'] = date('H:i:s');
                    //$anades_check_rec['SAVEUTENTE'] = App::$utente->getKey('nomeUtente');
                    //ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADESSAVE', 'ROWID', $anades_check_rec);
                    try {
                        ItaDB::DBDelete($this->proLib->getPROTDB(), 'ANADES', 'ROWID', $anades_check_rec['ROWID']);
                    } catch (Exception $exc) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Cancellazione ANADES fallita: " . $exc->getMessage());
                        return false;
                    }
                }

                $anamed_dest = $this->proLib->GetAnamed($datiFascicolazione['RES']);
                $anades_rec = array();
                $anades_rec['DESNUM'] = $pronum;
                $anades_rec['DESPAR'] = $propar;
                $anades_rec['DESTIPO'] = "T";
                $anades_rec['DESGES'] = "1";
                $anades_rec['DESCOD'] = $datiFascicolazione['RES'];
                $anades_rec['DESCUF'] = $datiFascicolazione['UFF'];
                $anades_rec['DESNOM'] = $anamed_dest['MEDNOM'];
                try {
                    ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADES', 'ROWID', $anades_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento ANADES fallito: " . $exc->getMessage());
                    return false;
                }
                if ($propar == 'F') {
                    $propas_rec = $this->proLibPratica->GetPropas($pronum, 'paspro', false, $propar);
                    if ($propas_rec) {
                        $propas_rec['PRORPA'] = $datiFascicolazione['RES'];
                        $propas_rec['PROUFFRES'] = $datiFascicolazione['UFF'];
                        try {
                            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'PROPAS', 'ROWID', $propas_rec);
                        } catch (Exception $exc) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Aggiornamento PROPAS fallito: " . $exc->getMessage());
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function chiudiFascicolo($model, $gesnum, $dataChiusura) {
        $proges_rec = $this->proLibPratica->GetProges($gesnum);
        $proges_rec['GESDCH'] = $dataChiusura;
        $proges_rec['GESCLOSE'] = "@forzato@";
        $update_Info = 'Oggetto : Chiusura Fascicolo Fascicolo: ' . $proges_rec['GESKEY'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'PROGES', $proges_rec, $update_Info)) {
            $this->errCode = -1;
            $this->errMessage = "Errore nella riapertura del fascicolo:" . $proges_rec['PROGES'];
            return false;
        }
        $this->proLibPratica->sincronizzaStato($gesnum);
        return true;
    }

    public function riapriFascicolo($model, $gesnum) {
        $proges_rec = $this->proLibPratica->GetProges($gesnum);
        $chiavePassoChiusura = $proges_rec['GESCLOSE'];
        $proges_rec['GESDCH'] = "";
        $proges_rec['GESCLOSE'] = "";
        $update_Info = 'Oggetto : Riapertura Fascicolo: ' . $proges_rec['GESKEY'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'PROGES', $proges_rec, $update_Info)) {
            $this->errCode = -1;
            $this->errMessage = "Errore nella riapertura del fascicolo:" . $proges_rec['PROGES'];
            return false;
        }

        /*
         * Se chiusura gestita con passo azione le operazioni continuano
         */
        if ($chiavePassoChiusura !== '@forzato@') {
            $propas_rec = $this->proLibPratica->GetPropas($proges_rec['GESCLOSE'], "propak");
            if (!$propas_rec) {
                $this->errCode = -1;
                $this->errMessage = "Errore nella selezione del passo chiusura " . $propas_rec['PROPAK'];
                return false;
            }
            $anaogg_rec = $this->proLib->GetAnaogg($propas_rec['PASPRO'], $propas_rec['PASPAR']);
            $propas_rec['PROFIN'] = '';
            $propas_rec['PROSTATO'] = '';
            $propas_rec['PRODPA'] = 'EX PASSO CHIUSURA';
            $update_Info = 'Oggetto : svuota il passo chiusura ' . $propas_rec['PROPAK'];
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'PROPAS', $propas_rec, $update_Info)) {
                $this->errCode = -1;
                $this->errMessage = "Errore nell'azzeramento dei valori del passo chiusura" . $propas_rec['PROPAK'];
                return false;
            }
            $anaogg_rec['OGGOGG'] = $propas_rec['PRODPA'];
            $update_Info = 'Oggetto : modifica descrizione anapro chiusura ' . $propas_rec['PASPRO'] . '/' . $propas_rec['PASPAR'];
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAOGG', $anaogg_rec, $update_Info)) {
                $this->errCode = -1;
                $this->errMessage = "Errore nella modifica descrizione anapro chiusura " . $propas_rec['PASPRO'] . '/' . $propas_rec['PASPAR'];
                return false;
            }
        }
        $this->proLibPratica->sincronizzaStato($gesnum);
        return true;
    }

    public function CaricaStrutturaIter($fascicolo) {
        $sql = "
            SELECT
                ARCITE.*,
                ANAPRO.PROFASKEY,
                ANAPRO.PROSUBKEY
            FROM ANAPRO
                LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
            WHERE ANAPRO.PROFASKEY='$fascicolo'
            GROUP BY ITEPRO,ITEPAR
            ORDER BY ARCITE.ITEDAT,ARCITE.ITEORA,ARCITE.ROWID";
        $iter_tab = $this->proLib->getGenericTab($sql);
        $arrayIter = array();
        foreach ($iter_tab as $iter_rec) {
            $annotazione1 = "";
            $annotazione2 = "";
            switch ($iter_rec['ITEPAR']) {
                case 'A':
                    $annotazione1 = "PROTOCOLLO IN ARRIVO";
                    $annotazione2 = (int) substr($iter_rec['ITEPRO'], 4) . '/' . substr($iter_rec['ITEPRO'], 0, 4);
                    break;
                case 'P':
                    $annotazione1 = "PROTOCOLLO IN PARTENZA";
                    $annotazione2 = (int) substr($iter_rec['ITEPRO'], 4) . '/' . substr($iter_rec['ITEPRO'], 0, 4);
                    break;
                case 'C':
                    $annotazione1 = "DOCUMENTO FORMALE";
                    $annotazione2 = (int) substr($iter_rec['ITEPRO'], 4) . '/' . substr($iter_rec['ITEPRO'], 0, 4);
                    break;
                case 'F':
                    $annotazione1 = "FASCICOLO";
                    $annotazione2 = $iter_rec['PROFASKEY'];
                    break;
                case 'N':
                    $annotazione1 = "SOTTOFASCICOLO";
                    $annotazione2 = substr($iter_rec['PROSUBKEY'], strpos($iter_rec['PROSUBKEY'], '-') + 1);
                    break;
                case 'T':
                    $annotazione1 = "AZIONE";
                    break;
            }


            $arrayIter[] = array(
                'ITERDATA' => '',
                'ITEDLE' => '',
                'ITERCHIUSO' => '',
                'ITETERMINE' => '',
                'ITERACCRIF' => '',
                'ITERDESTINATARIO' => '<span style="color:blue;font-size:1.3em;font-weight:bold;">' . $annotazione1 . '</span>',
                'ITERANNOTAZIONI' => '<span style="color:blue;font-size:1.3em;font-weight:bold;">' . $annotazione2 . '</span>',
            );
            $arrayIter = $this->caricaTreeIter($arrayIter, $iter_rec['ITEPRO'], $iter_rec['ITEPAR']);
        }
        return $arrayIter;
    }

    private function caricaTreeIter($arrayIter, $itepro, $itepar, $itekey = NULL, $level = 0) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($itekey == NULL) {
            $arcite_tab = $this->proLib->getGenericTab(
                    "SELECT * FROM ARCITE WHERE ITEPRO =$itepro AND ITEPRE='' AND ITEPAR='$itepar' ORDER BY ITEDAT,ITEDATORA,ITEFIN");
        } else {
            $arcite_tab = $this->proLib->getGenericTab(
                    "SELECT * FROM ARCITE WHERE ITEPRO =$itepro AND ITEPRE='$itekey' AND ITEPAR='$itepar' ORDER BY ITEDAT,ITEDATORA,ITEFIN");
        }
        if (count($arcite_tab) > 0) {
            for ($i = 0; $i < count($arcite_tab); $i++) {
                $style = "";
                $inc = count($arrayIter) + 1;
                if ($arcite_tab[$i]['ITEANNULLATO']) {
                    $style = ' style="background-color:gray;color:white;font-wheight:bold;" ';
                }
                $arrayIter[$inc]['ITEKEY'] = $arcite_tab[$i]['ITEKEY'];
                $arrayIter[$inc]['ITEPRE'] = $arcite_tab[$i]['ITEPRE'];
                $arrayIter[$inc]['ITERDATA'] = $arcite_tab[$i]['ITEDAT'];
                $arrayIter[$inc]['ITEORA'] = $arcite_tab[$i]['ITEORA'];
                $arrayIter[$inc]['NUMERO'] = (int) substr($itepro, 4) . '/' . substr($itepro, 0, 4) . ' - ' . $itepar;
                $arrayIter[$inc]['ITERGIORNI'] = '';
                $arrayIter[$inc]['ITERCODDEST'] = "<p $style>{$arcite_tab[$i]['ITEDES']}</p>";

                if ($arcite_tab[$i]['ITEDES']) {
                    $soggetto = proSoggetto::getInstance($this->proLib, $arcite_tab[$i]['ITEDES'], $arcite_tab[$i]['ITEUFF']);
                    if (!$soggetto) {
                        continue;
                    }
                    $record = $soggetto->getSoggetto();
                    $arrayIter[$inc]['ITERDESTINATARIO'] = $record['DESCRIZIONESOGGETTO'];
                    if ($record['RUOLO']) {
                        $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['RUOLO'];
                    }
                    $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['DESCRIZIONEUFFICIO'];
                    if ($record['SERVIZIO']) {
                        $arrayIter[$inc]['ITERDESTINATARIO'] .= ' - ' . $record['SERVIZIO'];
                    }
                    if (!$record) {
                        $style = 'style = "font-weight:900;color:#BE0000;"';
                        $anamed_rec = $this->proLib->GetAnamed($arcite_tab[$i]['ITEDES'], 'codice', 'no', false, true);
                        $arrayIter[$inc]['ITERDESTINATARIO'] = "<p $style>{$anamed_rec['MEDNOM']}</p>";
                    }
                } else {
                    $anauff_rec = $this->proLib->GetAnauff($arcite_tab[$i]['ITEUFF'], 'codice');
                    $divStile = "<div style= 'background:#B2F7DC;'>";
                    $arrayIter[$inc]['ITERDESTINATARIO'] = $divStile." " . $anauff_rec['UFFDES'] . ": TRASMISSIONE A UFFICIO</div>";
                }

                $annotazioni = $arcite_tab[$i]['ITEANN'] . $arcite_tab[$i]['ITEAN2'];
                $arrayIter[$inc]['ITERANNOTAZIONI'] = "<p $style>$annotazioni</p>";
                $arrayIter[$inc]['ITERANNOTAZIONI'] = '<div class="ita-Wordwrap" style="max-height:35px;overflow:auto;">' . $arrayIter[$inc]['ITERANNOTAZIONI'] . '</div>'; // max-height o height?
                $arrayIter[$inc]['ITEDLE'] = $arcite_tab[$i]['ITEDLE'];
                $arrayIter[$inc]['ITERCHIUSO'] = $arcite_tab[$i]['ITEFIN'];
                $arrayIter[$inc]['ITETERMINE'] = $arcite_tab[$i]['ITETERMINE'];
                $arrayIter[$inc]['ITERACCRIF'] = '';
                $arrayIter[$inc]['ITERMOTIVO'] = '';
                if ($arcite_tab[$i]['ITEDATACC']) {
                    $arrayIter[$inc]['ITERACCRIF'] = $arcite_tab[$i]['ITEDATACC'];
                    $arrayIter[$inc]['ITERMOTIVO'] = $arcite_tab[$i]['ITENOTEACC'];
                }
                if ($arcite_tab[$i]['ITEDATRIF']) {
                    $arrayIter[$inc]['ITERACCRIF'] = $arcite_tab[$i]['ITEDATRIF'];
                    $arrayIter[$inc]['ITERMOTIVO'] = $arcite_tab[$i]['ITEMOTIVO'];
                }
                $arrayIter[$inc]['ITERGEST'] = 0;
                if ($arcite_tab[$i]['ITEGES'] == 1) {
                    $arrayIter[$inc]['ITERGEST'] = 1;
                }
                $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-green-24x24"></span>';
                if ($arcite_tab[$i]['ITETERMINE'] <> '' && $arcite_tab[$i]['ITETERMINE'] < date("Ymd")) {
                    $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-lock-24x24"></span>';
                } else if ($arcite_tab[$i]['ITESTATO'] == '1' || $arcite_tab[$i]['ITESTATO'] == '3') {
                    $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-divieto-24x24"></span>';
                } else {
                    if ($arcite_tab[$i]['ITESUS'] != '') {
                        $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-grey-24x24"></span>';
                    } else {
                        if ($arcite_tab[$i]['ITEFIN'] != '' || $arcite_tab[$i]['ITEGES'] != '1') {
                            $arrayIter[$inc]['ITERSTATO'] = '<span class="ita-icon ita-icon-check-red-24x24"></span>';
//                        } else {
//                            $this->statoIter = 1;
                        }
                    }
                }

                if ($profilo['COD_SOGGETTO'] == $arcite_tab[$i]['ITEDES']) {
//                    if (!$iter_rec['ITEFIN']) {
//                        $arrayIter[$key]['ITERDESTINATARIO'] = '<div style="height:100%;' . $styleMyIter . '"><span style="color:black;font-weight:bold;">' . $arrayIter[$key]['ITERDESTINATARIO'] . '</span>';
//                    } else {
                    $arrayIter[$inc]['ITERDESTINATARIO'] = '<span style="color:black;font-weight:bold;">' . $arrayIter[$inc]['ITERDESTINATARIO'] . '</span>';
//                    }
                }

                $itenodott = $this->GetDescrizioneNodo($arcite_tab[$i]);
                $arrayIter[$inc]['ITERDESCR'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $itenodott . '">' . $arcite_tab[$i]['ITENODO'] . '</span></div>';

                $arrayIter[$inc]['level'] = $level;
                $arrayIter[$inc]['parent'] = $itekey;
                $arrayIter[$inc]['isLeaf'] = 'false';
                $arrayIter[$inc]['expanded'] = 'true';
                $arrayIter[$inc]['loaded'] = 'true';
                $save_count = count($arrayIter);
                $arrayIter = $this->caricaTreeIter($arrayIter, $itepro, $itepar, $arrayIter[$inc]['ITEKEY'], $level + 1);
                if ($save_count == count($arrayIter)) {
                    $arrayIter[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $arrayIter;
    }

    public function checkResponFascicolo($codice, $tipo = 'geskey', $utecod = '') {
        $proges_rec = $this->proLibPratica->GetProges($codice, $tipo);
        if (!$proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Fascicolo non trovato.");
            return false;
        }
        if (!$utecod) {
            $utecod = proSoggetto::getCodiceSoggettoFromNomeUtente();
        }
        if ($proges_rec['GESRES'] && $proges_rec['GESRES'] == $utecod) {
            return true;
        }
        return false;
    }

    public function SganciaDocumentoDaFascicolo($chiave) {
        $dockey = substr($chiave, 4);
        list($dockey, $rowidAnadoc) = explode("-", $dockey);
        $sql = "SELECT * FROM ANADOC WHERE ROWID = $rowidAnadoc ";
        $anadoc_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if (!$anadoc_rec && $anadoc_rec['DOCKEY'] == $dockey) {
            $this->setErrCode(-1);
            $this->setErrMessage("ANADOC non trovato.");
            return false;
        }

//        App::log($anadoc_rec);
//        return false;
        /*
         * Salvo ANADOCSAVE
         */
        $anadocsave_rec = $anadoc_rec;
        $anadocsave_rec['ROWID'] = '';
        $anadocsave_rec['SAVEDATA'] = date('Ymd');
        $anadocsave_rec['SAVEORA'] = date("H:i:s");
        $anadocsave_rec['SAVEUTENTE'] = App::$utente->getKey('nomeUtente');
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADOCSAVE', 'ROWID', $anadocsave_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserimento ANADOCSAVE fallito:" . $exc->getMessage());
            return false;
        }

        /*
         * Cancello il FILE
         */
        //       $allpath = $this->proLib->SetDirectory($anadoc_rec['DOCNUM'], substr($anadoc_rec['DOCPAR'], 0, 1));
//        $filepath = $allpath . "/" . $anadoc_rec['DOCFIL'];
//        if (!@unlink($filepath)) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("Errore in cancellazione file. $filepath");
//            return false;
//        }
        if (!$this->proLibAllegati->CancellaDocAllegato($anadoc_rec['ROWID'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in cancellazione file. " . $this->proLibAllegati->getErrMessage());
            return false;
        }
        /*
         * Cancello ANADOC
         */
        try {
            ItaDB::DBDelete($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $anadoc_rec['ROWID']);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Cancellazione ANADOC fallita:" . $exc->getMessage());
            return false;
        }

        return true;
    }

    public function OLDGetPermessiFascicoliOLD($idUtente = null, $codice = '', $tipo = 'gesnum', $pronumSottoFascicolo = '', $tipoSottoFascicolo = 'N') {
        if (!$idUtente) {
            $idUtente = null;
        }
        $profilo = proSoggetto::getProfileFromIdUtente($idUtente);
        if (!$profilo) {
            return false;
        }
        /*
         * Setto default di visibilita
         */
        $Permessi = array();
        $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = false; // serve?
        $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = false;
        $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = false;
        $Permessi[self::PERMFASC_CREAZIONE] = false;
        $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = false;
        $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = false;
        $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = false;
        $Permessi[self::PERMFASC_SCRIVE_NOTE] = false;
        $Permessi[self::PERMFASC_MODIFICA_DATI] = false;
        $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = false;
        $Permessi[self::PERMFASC_RIAPRI_FASCICOLO] = false;

        /* Abilitazione permessi a livello di utenti
         * andrebbe controllato se:
         * HA VISIBILITA PER ENTE: può abilitare tranquillamente per profilo
         * SETTORE?
         * HA VISIBILITA PER UFFICIO: deve controllare prima se fa parte dell'ufficio del fascicolo
         * HA VISIBILITA PER SOGGETTO: deve controllare prima se parte dell'ufficio e ha la visibilita sul fascicolo/sottofascicolo
         * GESUFFRES PER IL FASICOLO
         * PROUFFRES PER IL SOTTOFASCICOLO
         * 
         */

        /*
         * Controllo la gestione abilitata all'utente.
         */
        switch ($profilo['FASCICOLO_ABILITATI']) {
            // Archivistica
            case '1':
                $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = true;
                $Permessi[self::PERMFASC_RIAPRI_FASCICOLO] = true;
                $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = true;
                $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                $Permessi[self::PERMFASC_CREAZIONE] = true;
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                break;
            // Completa
            case '2':
                $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = true;
                $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                $Permessi[self::PERMFASC_CREAZIONE] = true;
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                break;
            // Movimentazione
            case '3':
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                break;
            // Consultazione
            default:
                break;
        }
        if ($pronumSottoFascicolo) {
            $ProPas_rec = $this->proLibPratica->GetPropas($pronumSottoFascicolo, 'paspro', false, $tipoSottoFascicolo);
            if ($ProPas_rec) {
                /*
                 * Controllo se può movimentare
                 */
                $AnaproSottFasc_rec = $this->proLib->GetAnapro($ProPas_rec['PASPRO'], 'codice', $ProPas_rec['PASPAR']);
                $RetCtrGestione = $this->CtrInGestione($AnaproSottFasc_rec['PRONUM'], $AnaproSottFasc_rec['PROPAR'], $profilo['COD_SOGGETTO']);
//                App::log($RetCtrGestione);
                if ($RetCtrGestione) {
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                }
                /*
                 * Controllo se è responsabile
                 */
                $RespUfficioSott = false;
                $RespSottoFasc = false;
                if ($ProPas_rec['PRORPA'] == $profilo['COD_SOGGETTO']) {
                    $RespSottoFasc = true;
                }
                $Anauff_rec = $this->proLib->GetAnauff($ProPas_rec['PROUFFRES'], 'codice');
                if ($Anauff_rec['UFFRES'] && $Anauff_rec['UFFRES'] == $profilo['COD_SOGGETTO']) {
                    $RespUfficioSott = true;
                }
                if ($RespSottoFasc || $RespUfficioSott) {
                    $Permessi[self::PERMFASC_CREAZIONE] = true;
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                }
            }
        }
        if ($codice) {
            $proges_rec = $this->proLib->GetProges($codice, $tipo);
            if ($proges_rec) {
                /*
                 * Controllo se gli è stato trasmesso in gestione il fascicolo
                 * Allora può movimentare.
                 */
                $AnaproFasc_rec = $this->proLib->GetAnapro($proges_rec['GESKEY'], 'fascicolo');
                $RetCtrGestione = $this->CtrInGestione($AnaproFasc_rec['PRONUM'], $AnaproFasc_rec['PROPAR'], $profilo['COD_SOGGETTO']);
                if ($RetCtrGestione) {
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    // Gestione visibilità serve?
                }
                /*
                 * Controllo se è responsabile
                 */
                $RespFascicolo = false;
                $RespUfficio = false;
                if ($proges_rec['GESRES'] == $profilo['COD_SOGGETTO']) {
                    $RespFascicolo = true;
                }
                $Anauff_rec = $this->proLib->GetAnauff($proges_rec['GESUFFRES'], 'codice');
                if ($Anauff_rec['UFFRES'] && $Anauff_rec['UFFRES'] == $profilo['COD_SOGGETTO']) {
                    $RespUfficio = true;
                }
                //Se Responsabile del Fascicolo o Ufficio assegno la completa.
                if ($RespFascicolo || $RespUfficio) {
                    $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = true;
                    $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                    $Permessi[self::PERMFASC_CREAZIONE] = true;
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                }
            }
        }

        return $Permessi;
    }

    public function GetPermessiFascicoli($idUtente = null, $codice = '', $tipo = 'gesnum', $pronumSottoFascicolo = '', $tipoSottoFascicolo = 'N') {
        if (!$idUtente) {
            $idUtente = null;
        }
        $profilo = proSoggetto::getProfileFromIdUtente($idUtente);
        if (!$profilo) {
            return false;
        }
        $codiceSoggetto = proSoggetto::getCodiceSoggettoFromIdUtente($idUtente);
        $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($codiceSoggetto);
        /*
         * Setto default di visibilita a false
         */
        $Permessi = array();
        $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = false;
        $Permessi[self::PERMFASC_RIAPRI_FASCICOLO] = false;
        $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = false;
        $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = false;
        $Permessi[self::PERMFASC_CREAZIONE] = false;
        $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = false;
        $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = false;
        $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = false;
        $Permessi[self::PERMFASC_SCRIVE_NOTE] = false;
        $Permessi[self::PERMFASC_MODIFICA_DATI] = false;
        $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = false;

        $proges_rec = array();
        $ProPas_rec = array();

        if ($codice) {
            $proges_rec = $this->proLib->GetProges($codice, $tipo);
        }
        if ($pronumSottoFascicolo) {
            $ProPas_rec = $this->proLibPratica->GetPropas($pronumSottoFascicolo, 'paspro', false, $tipoSottoFascicolo);
        }
        /*
         * Se getPermessi per determinato fascicolo o sottofascicolo. 
         * I permessi da profilo vanno letti:
         * Se l'utente ha visibilità per SETTORE/UFFICIO:
         *      Controllo se fa parte dell'ufficio del fascicolo o sottofascicolo.
         * Se l'utente ha visibilità per SOGGETTO:
         *      Controllo se ha un Arcite di tipo INS/TRX/ASS in Gestione non annullato
         *      del fascicolo o sottofascicolo.
         * Se ha visibilità per ENTE vanno letti sempre
         * 
         */
        $LeggiPermessiDaProfilo = true;
        if ($proges_rec || $ProPas_rec) {
            switch ($profilo['VIS_FASCICOLO']) {
                case 'SETTORE':
                case 'UFFICIO':
                    $LeggiPermessiDaProfilo = false;
                    foreach ($ruoli as $ruolo) {
                        if ($proges_rec) {
                            if ($ruolo['CODICEUFFICIO'] == $proges_rec['GESUFFRES']) {
                                $LeggiPermessiDaProfilo = true;
                                break;
                            }
                        }
                        if ($ProPas_rec) {
                            if ($ruolo['CODICEUFFICIO'] == $ProPas_rec['PROUFFRES']) {
                                $LeggiPermessiDaProfilo = true;
                                break;
                            }
                        }
                    }
                    break;
                case 'SOGGETTO':
                    $LeggiPermessiDaProfilo = false;
                    if ($proges_rec) {
                        $Anapro_rec = $this->proLib->GetAnapro($proges_rec['GESKEY'], 'fascicolo');
                        if ($this->CtrPerIlSoggetto($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $codiceSoggetto)) {
                            $LeggiPermessiDaProfilo = true;
                        }
                    }
                    if ($ProPas_rec) {
                        if ($this->CtrPerIlSoggetto($ProPas_rec['PASPRO'], $ProPas_rec['PASPAR'], $codiceSoggetto)) {
                            $LeggiPermessiDaProfilo = true;
                        }
                    }
                    break;

                default:
                    break;
            }
        }

        // Controllo ANAENT 58 e do "LeggIPermessiDaProfilo" o serve altro parametro: oppure risoluzione prima bug.
        $anaent_58 = $this->proLib->GetAnaent('58');
        $GestioneCompletaFascicoli = false;
        if ($anaent_58['ENTDE3']) {
            if ($profilo['FASCICOLO_ABILITATI'] == '1' || $profilo['FASCICOLO_ABILITATI'] == '2' || $profilo['FASCICOLO_ABILITATI'] == '3') {
                $GestioneCompletaFascicoli = true;
                $LeggiPermessiDaProfilo = true;
            }
        }

        /*
         * Controllo la gestione abilitata all'utente se è richiesto.
         */
        if ($LeggiPermessiDaProfilo) {
            switch ($profilo['FASCICOLO_ABILITATI']) {
                // Archivistica
                case '1':
                    $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = true;
                    $Permessi[self::PERMFASC_RIAPRI_FASCICOLO] = true;
                    $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = true;
                    $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                    $Permessi[self::PERMFASC_CREAZIONE] = true;
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                    break;
                // Completa
                case '2':
                    $Permessi[self::PERMFASC_VISIBILITA_COMPLETA] = true;
                    $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                    $Permessi[self::PERMFASC_CREAZIONE] = true;
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_SOTTOFASCICOLI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
                    break;
                // Movimentazione
                case '3':
                    $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                    $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                    break;
                // Consultazione
                default:
                    break;
            }
        }
        /* Se indicato il sottofascicolo */
        if ($ProPas_rec) {
            /*
             * Controllo se può movimentare
             */
            $AnaproSottFasc_rec = $this->proLib->GetAnapro($ProPas_rec['PASPRO'], 'codice', $ProPas_rec['PASPAR']);
            $RetCtrGestione = $this->CtrInGestione($AnaproSottFasc_rec['PRONUM'], $AnaproSottFasc_rec['PROPAR'], $profilo['COD_SOGGETTO']);
            if ($RetCtrGestione || $GestioneCompletaFascicoli) {
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
            }
            /*
             * Controllo se è responsabile
             */
            $RespUfficioSott = false;
            $RespSottoFasc = false;
            if ($ProPas_rec['PRORPA'] == $profilo['COD_SOGGETTO']) {
                $RespSottoFasc = true;
            }
            $Anauff_rec = $this->proLib->GetAnauff($ProPas_rec['PROUFFRES'], 'codice');
            if ($Anauff_rec['UFFRES'] && $Anauff_rec['UFFRES'] == $profilo['COD_SOGGETTO']) {
                $RespUfficioSott = true;
            }
            if ($RespSottoFasc || $RespUfficioSott) {
                $Permessi[self::PERMFASC_CREAZIONE] = true;
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
            }
        }
        /* Se indicato il fascicolo */
        if ($proges_rec) {
            /*
             * Controllo se gli è stato trasmesso in gestione il fascicolo
             * Allora può movimentare.
             */
            $AnaproFasc_rec = $this->proLib->GetAnapro($proges_rec['GESKEY'], 'fascicolo');
            $RetCtrGestione = $this->CtrInGestione($AnaproFasc_rec['PRONUM'], $AnaproFasc_rec['PROPAR'], $profilo['COD_SOGGETTO']);
            if ($RetCtrGestione || $GestioneCompletaFascicoli) {
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                // Gestione visibilità serve?
            }
            /*
             * Controllo se è responsabile
             */
            $RespFascicolo = false;
            $RespUfficio = false;
            if ($proges_rec['GESRES'] == $profilo['COD_SOGGETTO']) {
                $RespFascicolo = true;
            }
            $Anauff_rec = $this->proLib->GetAnauff($proges_rec['GESUFFRES'], 'codice');
            if ($Anauff_rec['UFFRES'] && $Anauff_rec['UFFRES'] == $profilo['COD_SOGGETTO']) {
                $RespUfficio = true;
            }
            //Se Responsabile del Fascicolo o Ufficio assegno la completa.
            if ($RespFascicolo || $RespUfficio) {
                $Permessi[self::PERMFASC_VISIBILITA_ARCHIVISTA] = true; // Non serve..?
                $Permessi[self::PERMFASC_APERTURA_CHIUSURA] = true;
                $Permessi[self::PERMFASC_CREAZIONE] = true;
                $Permessi[self::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                $Permessi[self::PERMFASC_GESTIONE_DOCUMENTI] = true;
                $Permessi[self::PERMFASC_SCRIVE_NOTE] = true;
                $Permessi[self::PERMFASC_MODIFICA_DATI] = true;
                $Permessi[self::PERMFASC_GESTIONE_VISIBILITA] = true;
            }
        }


        return $Permessi;
    }

    // Si potrebbe passare direttamente anapro del fascicolo o del sottofascicolo..
    public function CaricaAssegnazioniFascicolo($pronumFascicolo, $pronumSottoFascicolo = '') {
        if ($pronumSottoFascicolo) {
            $Codice = $pronumSottoFascicolo;
            $Tipo = 'N';
        } else {
            $Codice = $pronumFascicolo;
            $Tipo = 'F';
        }
        $Anapro_rec = $this->proLib->GetAnapro($Codice, 'codice', $Tipo);
        $arrayIter = array();
        if ($Anapro_rec) {
            $annotazione1 = "FASCICOLO";
            $annotazione2 = $Anapro_rec['PROFASKEY'];
            if ($pronumSottoFascicolo != '') {
                $annotazione1 = "SOTTOFASCICOLO";
                $annotazione2 = substr($Anapro_rec['PROSUBKEY'], strpos($Anapro_rec['PROSUBKEY'], '-') + 1);
            }
            $arrayIter[] = array(
                'ITERDATA' => '',
                'ITEDLE' => '',
                'ITERCHIUSO' => '',
                'ITETERMINE' => '',
                'ITERACCRIF' => '',
                'ITERDESTINATARIO' => '<span style="color:blue;font-size:1.3em;font-weight:bold;">' . $annotazione1 . '</span>',
                'ITERANNOTAZIONI' => '<span style="color:blue;font-size:1.3em;font-weight:bold;">' . $annotazione2 . '</span>',
            );
            $arrayIter = $this->caricaTreeIter($arrayIter, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        }
        return $arrayIter;
    }

    public function GetDescrizioneNodo($iter_rec) {
        $itenodott = '';
        switch ($iter_rec['ITENODO']) {
            case "TRX":
                $itenodott = "Trasmissione ";
                switch ($iter_rec['ITEPAR']) {
                    case "A":
                        $itenodott .= " Arrivo";
                        break;
                    case "P":
                        $itenodott .= " Partenza";
                        break;
                    case "C":
                        $itenodott .= " Documento Formale";
                        break;
                    case "F":
                        $itenodott .= " Fascicolo";
                        break;
                    case "N":
                        $itenodott .= " Sotto-Fascicolo";
                        break;
                    case "T":
                        $itenodott .= " Azione";
                        break;
                    default:
                        break;
                }

                break;
            case "ASF":
                $itenodott = "Assegnazione Fascicolo";
                break;
            case "ASS":
                $itenodott = "Assegnazione protocollo";
                break;
            case "INS":
                $itenodott = "Inserimento";
                switch ($iter_rec['ITEPAR']) {
                    case "A":
                        $itenodott .= " Arrivo";
                        break;
                    case "P":
                        $itenodott .= " Partenza";
                        break;
                    case "C":
                        $itenodott .= " Documento Formale";
                        break;
                    case "F":
                        $itenodott .= " Fascicolo";
                        break;
                    case "N":
                        $itenodott .= " Sotto-Fascicolo";
                        break;
                    case "T":
                        $itenodott .= " Azione";
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }
        return $itenodott;
    }

    public function SganciaSottofascicolo($model, $chiave, $fascicolo) {
        $pronum = substr($chiave, 4, 10);
        $propar = substr($chiave, 14, 2);
        if ($propar != 'N') {
            $this->errCode = -1;
            $this->setErrMessage("Errore, non stai gestendo un sottofascicolo.");
            return false;
        }
        //$propas_rec = $this->proLibPratica->GetPropas($pronum, 'paspro', false, $propar);
        $Anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if (!$Anapro_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Errore, Sottofascicolo non trovato.");
            return false;
        }
        $Orgconn_rec = $this->proLib->GetOrgConn($pronum, 'codice', $propar);
        if (!$Orgconn_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Errore, Connessione al fascicolo non trovata.");
            return false;
        }
        // Controllo se ha documenti/protocolli collegati:
        $Orgconn_tab = $this->GetOrgconParent($pronum, $propar);
        if ($Orgconn_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non è possibile sganciare il sottofascicolo perchè sono presenti elementi collegati.");
            return false;
        }
        /* Qui controllo per vedere documenti allegati */
        $Anadoc_tab = $this->proLib->GetAnadoc($pronum, 'protocollo', true, $propar);
        if ($Anadoc_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non è possibile sganciare il sottofascicolo perchè sono ancora presenti documenti collegati.");
            return false;
        }
        /* Qui controllo per vedere se ha iter attivi per il sottofascicolo */
        if ($propar == 'N') {
            $proges_rec = $this->proLibPratica->GetProges($fascicolo, 'geskey');
            $IterAperti = $this->ControllaFascicoloIterAperti($proges_rec['GESNUM'], $pronum);
            if ($IterAperti) {
                $Messaggio = 'Non è possibile sganciare il sottofascicolo:<br>';
                $Messaggio.=implode('<br>', $IterAperti);
                $this->setErrCode(-1);
                $this->setErrMessage($Messaggio);
                return false;
            }
        }

        /*
         * Annullo l'orgconn 
         */
        $nomeUtente = App::$utente->getKey('nomeUtente');
        $Orgconn_rec['CONNUTEANN'] = $nomeUtente;
        $Orgconn_rec['CONNDATAANN'] = date('Ymd');
        $Orgconn_rec['CONNORAANN'] = date('H:i:s');
        $Orgconn_rec['CONNUTEMOD'] = $nomeUtente;
        $Orgconn_rec['CONNDATAMOD'] = date('Ymd');
        $Orgconn_rec['CONNORAMOD'] = date('H:i:s');

        $update_Info = 'Oggetto: Annullo ORGCONN. Riferimenti: ' . $Orgconn_rec['ORGKEY'] . ' - ' . $Anapro_rec['PROSUBKEY'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'ORGCONN', $Orgconn_rec, $update_Info)) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in Annullamento documento su ORGCONN.");
            return false;
        }
        /*
         * Annullo PROPAS o cancello?
         */

        return true;
    }

    public function ControlloVisibilitaSoggetto($Anapro_rec, $Destinatario, $Ufficio) {
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $Anapro_rec['PRONUM'] . " AND 
                              ITEPAR = '" . $Anapro_rec['PROPAR'] . "' AND
                              ITEDES = '$Destinatario' ";
        if ($Ufficio) {
            $sql.=" AND ITEUFF = '$Ufficio' ";
        }
        return ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
    }

    public function ControllaFascicoloIterAperti($gesnum, $numSingoloSottofas = '') {
        $sql = "SELECT ARCITE.*,ANAPRO.*,ORGCONN.CONNDATAANN 
                     FROM PROGES PROGES
                LEFT OUTER JOIN 
                    ANAPRO ANAPRO
                ON
                    ANAPRO.PROFASKEY=PROGES.GESKEY AND
                        (ANAPRO.PROPAR='F' OR ANAPRO.PROPAR='N')
               LEFT OUTER JOIN
                    ARCITE ARCITE
                ON
                    ANAPRO.PRONUM=ARCITE.ITEPRO AND
                    ANAPRO.PROPAR=ARCITE.ITEPAR
              LEFT OUTER JOIN ORGCONN ORGCONN ON ARCITE.ITEPRO=ORGCONN.PRONUM AND ARCITE.ITEPAR=ORGCONN.PROPAR
            WHERE PROGES.GESNUM = '$gesnum' AND 
                  ARCITE.ITEFIN = '' AND 
                  ARCITE.ITEGES = '1' AND 
                  (ARCITE.ITENODO = 'ASS' OR ARCITE.ITENODO = 'TRX' )";
        if ($numSingoloSottofas) {
            $sql.=" AND ANAPRO.PRONUM = '$numSingoloSottofas'  AND ANAPRO.PROPAR = 'N' ";
        }
        $sql.=" ORDER BY ANAPRO.PROPAR ASC ";
        App::log('$sql sgancia');
        App::log($sql);
        $Arcite_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        $IterAperti = array();
        if ($Arcite_tab) {
            foreach ($Arcite_tab as $Arcite_rec) {
                /* Se il pronum cercato è annullato non è da considerare */
                if ($Arcite_rec['CONNDATAANN']) {
                    continue;
                }
                $Messaggio = '';
                $Anamed_rec = $this->proLib->GetAnamed($Arcite_rec['ITEDES'], 'codice');
                if ($Arcite_rec['ITEPAR'] == 'N') {
                    $Messaggio = 'Il Sottofascicolo n. ' . $Arcite_rec['PROSUBKEY'] . ' è ancora in gestione a ' . $Anamed_rec['MEDNOM'];
                } else {
                    $Messaggio = 'Il Fascicolo è ancora in gestione a ' . $Anamed_rec['MEDNOM'];
                }
                $IterAperti[] = $Messaggio;
            }
        }
        return $IterAperti;
    }

    public function CtrInGestione($pronum, $propar, $codDest) {
        $condASX = '';
        $anaent_58 = $this->proLib->GetAnaent('58');
        if ($anaent_58['ENTDE1']) {
            $condASX = " OR ITENODO = 'ASS' ";
        }
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $pronum . " AND 
                              ITEPAR = '" . $propar . "' AND
                              ITEDES = '$codDest' AND
                              ITEGES = '1' AND
                              (ITENODO = 'TRX' OR ITENODO = 'ASS' $condASX ) AND 
                              ITESTATO = '" . proIter::ITESTATO_INCARICO . "' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function GetOrgconParent($Pronumparent, $proparparent, $proParTipo = '') {
        $sql = "SELECT * FROM ORGCONN WHERE PRONUMPARENT='{$Pronumparent}' AND PROPARPARENT='{$proparparent}' AND CONNDATAANN = ''";
        if ($proParTipo) {
            $sql.=" AND PROPAR = '$proParTipo'";
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
    }

    public function CtrPerIlSoggetto($pronum, $propar, $codDest) {
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $pronum . " AND 
                              ITEPAR = '" . $propar . "' AND
                              ITEDES = '$codDest' AND
                              ITEGES = '1' AND
                             (ITENODO = 'TRX' OR ITENODO = 'ASS' OR ITENODO = 'INS') ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function ChkProtoInSottoFascicolo($ProNum, $ProPar) {
        $retSottoFas = array();
        $Orgconn_rec = $this->proLib->GetOrgConn($ProNum, 'codice', $ProPar);
        $OggSottoFas = $Sottofascicolo = '';
        if ($Orgconn_rec['PROPARPARENT'] == 'N') {
            $AnaproSottoFascicolo_rec = $this->proLib->GetAnapro($Orgconn_rec['PRONUMPARENT'], 'codice', $Orgconn_rec['PROPARPARENT']);
            $AnaOgg_rec = $this->proLib->GetAnaogg($Orgconn_rec['PRONUMPARENT'], $Orgconn_rec['PROPARPARENT']);
            $OggSottoFas = $AnaOgg_rec['OGGOGG'];
            $Sottofascicolo = str_replace($AnaproSottoFascicolo_rec['PROFASKEY'] . '-', '', $AnaproSottoFascicolo_rec['PROSUBKEY']);
            $retSottoFas['NUMERO'] = $Sottofascicolo;
            $retSottoFas['DESCRIZIONE'] = $OggSottoFas;
        }
        return $retSottoFas;
    }

    public function CtrInConsultazione($pronum, $propar, $codDest = '') {
        if (!$codDest) {
            $idUtente = null;
        }
        $profilo = proSoggetto::getProfileFromIdUtente($idUtente);
        if (!$profilo) {
            return false;
        }
        $codiceSoggetto = proSoggetto::getCodiceSoggettoFromIdUtente($idUtente);
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $pronum . " AND 
                              ITEPAR = '" . $propar . "' AND
                              ITEDES = '$codiceSoggetto' AND
                              ITENODO = 'ASF' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function CheckDocumentoFascicolo($paramChiave, $pronumSottoFascicolo = '') {
        $SoloConsulta = false;
        $retCheck = array();
        $retCheck['GESTIONE'] = false;
        $retCheck['CONSULTA'] = false;
        App::log('Lavoro con il ' . $pronumSottoFascicolo);
        /* 1 Qui Controllo se ho in gestione il tipo di documento */
        $permessiFascicolo = $this->GetPermessiFascicoli('', $paramChiave['GESNUM'], 'gesnum', $pronumSottoFascicolo);
        if (!$paramChiave['ROWID_ORGCONN']) {
            if ($paramChiave['TIPOCHIAVE'] == 'DOC-') {
                if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
                    $retCheck['GESTIONE'] = true;
                    $retCheck['CONSULTA'] = true;
                    $retCheck['PADRE'] = $pronumSottoFascicolo;
                    /* Faccio Ritornare anche gli stati permessi controllati. */
                    $retCheck[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] = true;
                    App::log('$retCheck1');
                    App::log($retCheck);
                    return $retCheck;
                }
            } else {
                if ($paramChiave['TIPOCHIAVE'] == 'PRO-' && $paramChiave['PROPAR'] == 'N') {
                    // Controllo se ho gestione sul sottofascicolo? // Attenzione, qui è corretto o dovrebbe gestire solo permessi sottofascicoli?
                    //$permessiSottoFasc = $this->proLibFascicolo->GetPermessiFascicoli('', $paramChiave['GESNUM'], 'gesnum', $paramChiave['PRONUM'], $paramChiave['PROPAR']);
                    if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] ||
                            $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] ||
                            $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_SOTTOFASCICOLI]) {
                        $retCheck['GESTIONE'] = true;
                        $retCheck['CONSULTA'] = true;
                        $retCheck['PADRE'] = $pronumSottoFascicolo;
                        App::log('$retCheck2');
                        App::log($retCheck);
                        /* Faccio Ritornare anche gli stati permessi controllati. */
                        $retCheck[proLibFascicolo::PERMFASC_GESTIONE_SOTTOFASCICOLI] = $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_SOTTOFASCICOLI];
                        $retCheck[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] = $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI];
                        $retCheck[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] = $permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI];
                        return $retCheck;
                    } else {
                        // Controllo se ho consultazione sul sottofascicolo?
                        $retCtrConsulta = $this->CtrInConsultazione($paramChiave['PRONUM'], 'N');
                        if ($retCtrConsulta) {
                            $SoloConsulta = true;
                        }
                    }
                }
            }
        } else {
            if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI]) {
                $retCheck['GESTIONE'] = true;
                $retCheck['CONSULTA'] = true;
                $retCheck['PADRE'] = $pronumSottoFascicolo;
                /* Faccio Ritornare anche gli stati permessi controllati. */
                $retCheck[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] = true;
                return $retCheck;
            }
            // Se è un protocollo, controllo se l'utente ha accesso ad esso. 
            $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $paramChiave['PRONUM'], $paramChiave['PROPAR']);
            if ($anapro_check) {
                $SoloConsulta = true;
            }
        }
        App::log('Sopra non e passato.');
        /* 2 Qui Controllo se ho in consultazione il sottofascicolo */

        if ($pronumSottoFascicolo) {
            $retCtrConsulta = $this->CtrInConsultazione($pronumSottoFascicolo, 'N');
            if ($retCtrConsulta) {
                $SoloConsulta = true;
            }
        }
        /* 3 Qui controllo se ho sottofascicolo padre in gestione/consultazione */
        // Se c'è un padre sottofascicolo, controllo se per la tipologia di rowidChiave inserito può gestirlo.
        // Controllare $pronumSottoFascicolo
        $sql = "SELECT * FROM ORGCONN WHERE PRONUM = '$pronumSottoFascicolo' AND PROPAR = 'N' AND PROPARPARENT = 'N' AND CONNDATAANN = '' ";
        $Orgconn_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Orgconn_rec) {
            $retCheck = $this->CheckDocumentoFascicolo($paramChiave, $Orgconn_rec['PRONUMPARENT']);
        }
        if ($SoloConsulta) {
            $retCheck['CONSULTA'] = true;
        }
        App::log('$retCheck5');
        App::log($retCheck);
        return $retCheck;
    }

    public function CheckPermessiDocumentoSottofascicolo($pronum, $propar, $gesNum) {
        $rowidChiave = 'PRO-' . $pronum . $propar;
        /* Predispongo dati chiave */
        $paramChiave = array();
        $paramChiave['GESNUM'] = $gesNum;
        $paramChiave['ROWIDCHIAVE'] = $rowidChiave; // Non servirebbe più
        $paramChiave['ROWID_ORGCONN'] = '';
        $paramChiave['PRONUM'] = $pronum;
        $paramChiave['PROPAR'] = $propar;
        $paramChiave['TIPOCHIAVE'] = 'PRO-';
        $retChk = $this->CheckDocumentoFascicolo($paramChiave, $pronum);
        return $retChk;
    }

    /* Se indicato PROFASKEY si ricerca il protocollo nel singolo fascicolo, 
     * altrimenti si verifica se il protocollo è presente 
     * in un qualsiasi fascicolo.
     */

    public function CheckProtocolloInFascicolo($Pronum, $Propar, $Profaskey = '') {
        $sql = "SELECT * FROM ORGCONN 
                    WHERE CONNDATAANN = '' AND 
                        PRONUM = $Pronum AND 
                        PROPAR = '$Propar' ";
        if ($Profaskey) {
            $sql.= "AND  ORGKEY = '$Profaskey' ";
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    /**
     * Estrae i Fascicoli a cui appartiene un protocollo alla data attuale
     * @param type $Pronum
     * @param type $Propar
     * @return string
     */
    public function EstraiFascicoliProtocollo($Pronum, $Propar) {
        /*
         * Estrazione Fascicoli
         */
        $sql = "SELECT ORGCONN.*,
                     ANAORG.ROWID AS ROWID_ANAORG,
                     ANAORG.ORGCOD,
                     ANAORG.ORGANN,
                     ANAORG.ORGDES,
                     ANAORG.ORGCCF,
                     ANAORG.ORGDES,
                     ANAOGG_FASCICOLO.OGGOGG OGGOGG_FASCICOLO,
                     ANAOGG_SOTTOFAS.OGGOGG AS OGGOGG_SOTTOFAS,
                     ANAPRO_SOTTOFAS.PROSUBKEY AS PROSUBKEY_SOTTOFAS
                FROM ORGCONN 
            LEFT OUTER JOIN ANAORG ON ORGCONN.ORGKEY = ANAORG.ORGKEY
            LEFT OUTER JOIN ANAOGG ANAOGG_FASCICOLO ON ORGCONN.PRONUMPARENT = ANAOGG_FASCICOLO.OGGNUM AND ORGCONN.PROPARPARENT=ANAOGG_FASCICOLO.OGGPAR
            LEFT OUTER JOIN ANAOGG ANAOGG_SOTTOFAS ON ORGCONN.PRONUMPARENT = ANAOGG_SOTTOFAS.OGGNUM AND ORGCONN.PROPARPARENT=ANAOGG_SOTTOFAS.OGGPAR
            LEFT OUTER JOIN ANAPRO ANAPRO_SOTTOFAS ON ORGCONN.PRONUMPARENT = ANAPRO_SOTTOFAS.PRONUM AND ORGCONN.PROPARPARENT=ANAPRO_SOTTOFAS.PROPAR
            WHERE CONNDATAANN = '' AND ORGCONN.PRONUM = $Pronum AND ORGCONN.PROPAR = '$Propar' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
    }

    /**
     * Restituisce un struttura array predisposta per la griglia standard 
     * di visualizzazione dei fascioli a cui appartiene un protocollo.
     * @param type $Pronum
     * @param type $Propar
     * @param type $Profaskey
     * @return array()
     */
    public function CaricaFascicoliProtocollo($Pronum, $Propar, $Profaskey = '') {
        if (!$Pronum || !$Propar) {
            return false;
        }
        $Fascicoli_tab = $this->EstraiFascicoliProtocollo($Pronum, $Propar, $Profaskey);
        return $this->ElaboraRecordFascicoliProtocollo($Fascicoli_tab, $Profaskey);
    }

    /**
     * Formatta l'array standard dei fascicoli per protocollo per la tabella jqgrid
     * @param type $Fascicoli_tab
     * @param type $Profaskey
     * @return array()
     */
    public function ElaboraRecordFascicoliProtocollo($Fascicoli_tab, $Profaskey) {
        $ElencoFascicoli = array();
        $kk = 0;
        Out::valore($this->nameForm . '_DescFascicoloPrincipale', '');
        foreach ($Fascicoli_tab as $Fascicoli_rec) {
            $kk++;
            $Fascicoli_rec['ROWID'] = $Fascicoli_rec['ROWID_ANAORG'];
            $DescFascicolo = '<div class="ita-html"><span class="ita-tooltip" title="' . $Fascicoli_rec['OGGOGG_FASCICOLO'] . '">';
            $DescFascicolo.= $Fascicoli_rec['ORGKEY'] . ' : ' . $Fascicoli_rec['OGGOGG_FASCICOLO'];
            $DescFascicolo.= '</span></div>';
            $Fascicoli_rec['FASCICOLO'] = $DescFascicolo;
            $Principale = false;
            if ($Profaskey != '' && $Fascicoli_rec['ORGKEY'] == $Profaskey) {
                $Principale = true;
                $Fascicoli_rec['ICON_FAS'] = "<div class=\"ita-html\"><span title = \"Fascicolo Principale\" class=\"ita-tooltip ita-icon ita-icon-star-yellow-16x16\" style = \"margin-left:5px; float:left;display:inline-block;\"></span></div>";
            }
            $OggSottoFas = '';
            if ($Fascicoli_rec['OGGOGG_SOTTOFAS']) {
                $OggSottoFas = "<b><u>Sottofascicolo:</u></b> " . $Fascicoli_rec['OGGOGG_SOTTOFAS'];
                $Sottofascicolo = str_replace($Fascicoli_rec['ORGKEY'] . '-', '', $Fascicoli_rec['PROSUBKEY_SOTTOFAS']);
                $Fascicoli_rec['SOTTOFAS'] = '<div class="ita-html"><span class="ita-tooltip" title="' . $OggSottoFas . '">' . $Sottofascicolo . '</span></div>';
                $Fascicoli_rec['CODICE_SOTTOFAS'] = $Fascicoli_rec['PROSUBKEY_SOTTOFAS'];
                $Fascicoli_rec['OGGETTO_SOTTOFAS'] = $Fascicoli_rec['OGGOGG_SOTTOFAS'];
            }
            if ($Principale == true) {
                $DescFasPrinc = $Fascicoli_rec['ORGKEY'] . ' : ' . $Fascicoli_rec['OGGOGG_FASCICOLO'];
                Out::valore($this->nameForm . '_DescFascicoloPrincipale', $DescFasPrinc);
                $ElencoFascicoli[0] = $Fascicoli_rec;
            } else {
                $ElencoFascicoli[$kk] = $Fascicoli_rec;
            }
        }
        ksort($ElencoFascicoli);
        return $ElencoFascicoli;
    }

    /**
     * 
     * @param type $Anapro_rowid
     * @return boolean
     */
    public function CtrProtPreFascicolato($Anapro_rowid) {
        /*
         * 1. Controllo se protocollo collegato è in un fascicolo.
         */
        $Anapro_rec = $this->proLib->GetAnapro($Anapro_rowid, 'rowid');
        // PROPRE PROPARPRE
        if ($Anapro_rec['PROPRE']) {
            $AnaproPre_rec = $this->proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
            $ElencoFascicoli = $this->GetElencoFascicoliMovimentabiliPerProtocollo($AnaproPre_rec);
            if ($ElencoFascicoli) {
                return $ElencoFascicoli;
            }
        }
        return false;
    }

    /**
     * 
     * @param type $Anapro_rowid
     * @return boolean
     */
    public function CtrProtFascicolato($Anapro_rowid) {
        /*
         * 1. Controllo se protocollo collegato è in un fascicolo.
         */
        $Anapro_rec = $this->proLib->GetAnapro($Anapro_rowid, 'rowid');
        $ElencoFascicoli = $this->GetElencoFascicoliMovimentabiliPerProtocollo($Anapro_rec);
        if ($ElencoFascicoli) {
            return $ElencoFascicoli;
        }

        return false;
    }

    /**
     * 
     * @param type $Anapro_rec
     * @return type
     */
    public function GetElencoFascicoliMovimentabiliPerProtocollo($Anapro_rec) {
        // Ritorno l'elenco dei fascicoli che l'untete può movimentare.
        $ElencoFascicoli = $this->CaricaFascicoliProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $Anapro_rec['PROFASKEY']);
        foreach ($ElencoFascicoli as $key => $Fascicolo) {
            $permessiFascicolo = $this->GetPermessiFascicoli('', $Fascicolo['ORGKEY'], 'geskey');
            if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI]) {
                unset($ElencoFascicoli[$key]);
            }
        }
        return $ElencoFascicoli;
    }

    /**
     * 
     * @param type $Anapro_rowid 
     * @param type $returnModel
     * @param type $returnEvent
     * @param type $Titolario
     */
    public function ApriSelezioneFascicoloFromProt($Anapro_rowid, $returnModel, $returnEvent = 'returnMultiSelezioneFascicolo', $AnaproCorrente_rec = array(), $Titolario = array()) {
        $Anapro_rec = $this->proLib->GetAnapro($Anapro_rowid, 'rowid');
        if ($Anapro_rec) {
            $ElencoFascicoli = $this->GetElencoFascicoliMovimentabiliPerProtocollo($Anapro_rec);
            if ($ElencoFascicoli) {
                $model = 'proMultiSeleFascicolo';
                itaLib::openForm($model);
                /* @var $proMultiSeleFascicolo proMultiSeleFascicolo */
                $proSeleFascicolo = itaModel::getInstance($model);
                $proSeleFascicolo->setEvent('openform');
                $proSeleFascicolo->setReturnModel($returnModel);
                $proSeleFascicolo->setReturnEvent($returnEvent);
                $proSeleFascicolo->setTitolario($Titolario);
                $proSeleFascicolo->setAnapro_rec($AnaproCorrente_rec);
                $proSeleFascicolo->setElencoFascicoli($ElencoFascicoli);
                $proSeleFascicolo->parseEvent();
            }
        }
    }

    public function FascicolaProtInElencoFascicoli($model, $anapro_rec, $ElencoFascicoli) {
        $proLib = new proLib();
        if (!$anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Anapro mancante. Non è possibile procedere alla fascicolazione.');
            return false;
        }

        foreach ($ElencoFascicoli as $Fascicolo_rec) {
            $Anaorg_rec = $proLib->GetAnaorg($Fascicolo_rec['ROWID'], 'rowid');
            $AnaproFascicolo_rec = $proLib->GetAnapro($Anaorg_rec['ORGKEY'], 'fascicolo');
//            $Orgconn_rec = $proLib->GetOrgConn($anapro_rec['PRONUM'], 'codice', $anapro_rec['PROPAR']);
            $PronumPar = $AnaproFascicolo_rec['PRONUM'];
            $ProparPar = $AnaproFascicolo_rec['PROPAR'];
            /* Se è indicato il parent lo metto allo stesso livello */
            if ($Fascicolo_rec['PRONUMPARENT']) {
                $PronumPar = $Fascicolo_rec['PRONUMPARENT'];
                $ProparPar = $Fascicolo_rec['PROPARPARENT'];
            }
            if (!$this->insertDocumentoFascicolo($model, $Anaorg_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $PronumPar, $ProparPar)) {
                return false;
            }
        }
        return true;
    }

    public function GetSottofascicolo($faskey, $proSubKey = '', $multi = false) {
        $sql = "SELECT ANAPRO.* 
                        FROM ANAPRO
                        LEFT OUTER JOIN ORGCONN ORGCONN ON ORGCONN.PRONUM=ANAPRO.PRONUM AND ORGCONN.PROPAR=ANAPRO.PROPAR AND ORGCONN.CONNDATAANN = '' 
                    WHERE ANAPRO.PROFASKEY='$faskey' AND ANAPRO.PROPAR = 'N' ";
        if ($proSubKey) {
            $sql.=" AND ANAPRO.PROSUBKEY = '$proSubKey' ";
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    public function CreaSqlVisibilitaFascicoli($ParamRicerca = array()) {
        $sql = "SELECT ANAORG.*, 
                       PROGES.GESOGG AS GESOGG ,
                       PROGES.GESKEY AS GESKEY,
                       PROGES.GESRES AS GESRES,
                       ANAMED.MEDNOM AS NOME_RESPONSABILE,
                       (SELECT COUNT(ROWID) FROM ORGCONN ORGNEWCONN WHERE ORGNEWCONN.PRONUMPARENT=ANAPRO.PRONUM AND ORGNEWCONN.PROPARPARENT=ANAPRO.PROPAR AND ORGNEWCONN.PROPAR='N' AND ORGNEWCONN.CONNDATAANN = '') AS SOTTOFASCICOLI_FAS
                    FROM ANAORG 
                    LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY
                    LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
                    LEFT OUTER JOIN ANAPRO ANAPRO ON ANAORG.ORGKEY=ANAPRO.PROFASKEY AND ANAPRO.PROPAR = 'F'
                    LEFT OUTER JOIN ANAPRO ANAPROVISIBILITA ON ANAPROVISIBILITA.PROFASKEY=PROGES.GESKEY AND (ANAPROVISIBILITA.PROPAR='F' OR ANAPROVISIBILITA.PROPAR='N')                    
                    LEFT OUTER JOIN ARCITE ARCITE ON ANAPROVISIBILITA.PRONUM=ARCITE.ITEPRO AND ANAPROVISIBILITA.PROPAR=ARCITE.ITEPAR                    
                    ";
        $sql.= " WHERE
                    ORGDAT='' ";
        //AND GESDCH = '' ";// Non più necessaria.

        if ($ParamRicerca['TITOLARIO']) {
            $Titolario = $ParamRicerca['TITOLARIO'];
            $sql.= "AND ORGCCF='{$Titolario}'";
        }

        if ($ParamRicerca['ANNO']) {
            $sql.=" AND ORGANN = '" . $ParamRicerca['ANNO'] . "' ";
        }
        if ($ParamRicerca['CODICE']) {
            // Allineo il codice almeno a 6
            $Codice = str_pad($ParamRicerca['CODICE'], 6, '0', STR_PAD_LEFT);
            $sql.=" AND ANAORG.ORGKEY LIKE '%" . $Codice . "' ";
        }
        if ($ParamRicerca['OGGETTO']) {
            $sql.=" AND " . $this->getPROTDB()->strUpper('PROGES.GESOGG') . " LIKE '%" . addslashes(strtoupper($ParamRicerca['OGGETTO'])) . "%' ";
        }
        if ($ParamRicerca['SERIE']) {
            $sql.=" AND ANAORG.CODSERIE = '" . $ParamRicerca['SERIE'] . "' ";
        }
        if ($ParamRicerca['PROGSERIE']) {
            $sql.=" AND ANAORG.PROGSERIE = '" . $ParamRicerca['PROGSERIE'] . "' ";
        }
        if ($ParamRicerca['SEGNATURA']) {
            $sql.=" AND ANAORG.ORGSEG = '" . $ParamRicerca['SEGNATURA'] . "' ";
        }
        if ($ParamRicerca['CODICEFASCICOLO']) {
            $sql.=" AND ANAORG.ORGKEY = '" . $ParamRicerca['CODICEFASCICOLO'] . "' ";
        }


        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');

        $sql .= " AND $where_profilo";

        $sql .= " GROUP BY ANAORG.ROWID";

        $sql = "SELECT * FROM (" . $sql . ") AS FASCICOLI WHERE 1 ";
//        if ($filters) {
//            foreach ($filters as $key => $value) {
//                if ($key == 'SOTTOFASCICOLI_FAS') {
//                    if ($value == 'S' || $value == 's') {
//                        $sql.=" AND $key > 0 ";
//                    } else {
//                        $sql.=" AND $key = 0 ";
//                    }
//                } else {
//                    $value = str_replace("'", "\'", $value);
//                    $sql.= " AND " . $this->PROT_DB->strupper($key) . " LIKE '%" . strtoupper($value) . "%' ";
//                }
//            }
//        }
        // qui andrebbe comunque controllata la visibilita su un fascicolo?
        // Dove controlla se può movimentare su fascicolo??? Elenco dei fascioli deve essere limitato?
        // USA GIA getSecureWhereFromIdUtente
        return $sql;
    }

    /**
     * Funzione per spostare i fascicoli da un protocollo all'altro.
     * @param type $sorgNum
     * @param type $sorgPar
     * @param type $destNum
     * @param type $destPar
     */
    public function SpostaFascicoli($model, $sorgNum, $sorgPar, $destNum, $destPar) {
        if (!$sorgNum || !$sorgPar) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati protocollo sorgente mancanti.");
            return false;
        }
        $AnaproSorg_rec = $this->proLib->GetAnapro($sorgNum, 'codice', $sorgPar);
        $ProFasKey = $AnaproSorg_rec['PROFASKEY'];
        if (!$destNum || !$destPar) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati protocollo destino mancanti.");
            return false;
        }
        $AnaproDest_rec = $this->proLib->GetAnapro($destNum, 'codice', $destPar);
        $FascicoliDocumentale = $this->EstraiFascicoliProtocollo($sorgNum, $sorgPar, '');
        $ElencoFascicoli = array();
        foreach ($FascicoliDocumentale as $FascicoloRec) {
            $ArrFascicolo = array();
            $ArrFascicolo['ROWID'] = $FascicoloRec['ROWID_ANAORG'];
            $ArrFascicolo['PRONUMPARENT'] = $FascicoloRec['PRONUMPARENT'];
            $ArrFascicolo['PROPARPARENT'] = $FascicoloRec['PROPARPARENT'];
            $ElencoFascicoli[] = $ArrFascicolo;
        }
        // Aggiungo in fascicolo
        if (!$this->FascicolaProtInElencoFascicoli($model, $AnaproDest_rec, $ElencoFascicoli)) {
            return false;
        }
        // Annullo fascicolo dal documento:
        foreach ($FascicoliDocumentale as $FascicoloRec) {
            if (!$this->annullaDocumentoFascicolo($model, $FascicoloRec['ORGKEY'], $FascicoloRec['ROWID'])) {
                return false;
            }
        }





//        
//        foreach ($ElencoFascicoli as $Fascicolo) {
//           $Orgconn_rec = array();
//            $Orgconn_rec['ROWID'] = $Fascicolo['ROWID'];
//            $Orgconn_rec['PRONUM'] = $destNum;
//            $Orgconn_rec['PROPAR'] = $destPar;
//            try {
//                ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ORGCONN', 'ROWID', $Orgconn_rec);
//            } catch (Exception $exc) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Aggiornamento ORGCONN fallito: " . $exc->getMessage());
//                return false;
//            }
//        }
//        // Aggiorno Fascicolo Principale
//        if ($ProFasKey) {
//            try {
//                $AnaproDest_rec['PROFASKEY'] = $ProFasKey;
//                ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $AnaproDest_rec);
//            } catch (Exception $exc) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Aggiornamento ORGCONN fallito: " . $exc->getMessage());
//                return false;
//            }
//        }


        return true;
    }

    public function creaFascicoloArchivistico($model, $datiFascicolazione, $descrizione, $codiceProcedimento = '') {
        /*
         * Controllo Parametri Obbligatori per Archivio Archivistico:
         */
        //Progressivo Serie
        $Serie_rec = $datiFascicolazione['SERIE'];
        if (!$Serie_rec['PROGSERIE']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Progressivo della serie mancante.");
            return false;
        }
        //Data Chiusura
        if (!$datiFascicolazione['DATACHIUSURA']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Data chiusura del fascicolo mancante.");
            return false;
        }
        // Allegati serve verificare? Almeno un allegato dovrebbe esserci nel fascicolo?
        $rowidFasciolo = $this->creaFascicolo($model, $datiFascicolazione, $descrizione, $codiceProcedimento);
        if (!$rowidFasciolo) {
            return false;
        }

        $Proges_rec = $this->proLib->GetProges($rowidFasciolo, 'rowid');
        $Proges_rec['GESKEY'];

        return $Proges_rec['GESKEY'];
    }

    public function AggiungiAllegatoAlFascicolo($model, $GesKey, $allegato) {
        /*
         * Lettura ANADOC
         */
        $AnaproF_rec = $this->getAnaproFascicolo($GesKey);
        if (!$AnaproF_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Anapro fascicolo con chiave ' . $GesKey . ' non trovato.');
            return false;
        }
        $iteKey = $this->proLib->IteKeyGenerator($AnaproF_rec['PRONUM'], '', date('Ymd'), $AnaproF_rec['PROPAR']);
        if (!$iteKey) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->proLib->getErrMessage());
            return false;
        }
        $randName = md5(rand() * time()) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
        /*
         * Inserimento ANADOC Semplice. 
         * QUI FUNZIONE FUTURA/ O INTEGRAZIONE CON proLibAllegati,
         * per passare chiavi referenziali ad altro applicativo.
         */
        $anadoc_rec = array();
        $anadoc_rec['DOCKEY'] = $iteKey;
        $anadoc_rec['DOCNUM'] = $AnaproF_rec['PRONUM'];
        $anadoc_rec['DOCPAR'] = $AnaproF_rec['PROPAR'];
        $anadoc_rec['DOCFIL'] = $randName;
        $anadoc_rec['DOCLNK'] = "allegato://" . $allegato['FILENAME'];
        $anadoc_rec['DOCUTE'] = 'DA PROTOCOLLO: ';
        $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
        $anadoc_rec['DOCTIPO'] = 'ALLEGATO';
        $anadoc_rec['DOCNAME'] = $allegato['FILENAME'];
        $anadoc_rec['DOCFDT'] = date('Ymd');
        $anadoc_rec['DOCNOTE'] = $allegato['DOCNOTE'];
        $anadoc_rec['DOCUTELOG'] = App::$utente->getKey('nomeUtente');
        $anadoc_rec['DOCDATADOC'] = date('Ymd');
        $anadoc_rec['DOCORADOC'] = date('H:i:s');

        try {
            $insert_Info = 'Inserimento: ' . $anadoc_rec['DOCKEY'] . ' ' . $anadoc_rec['DOCFIL'];
            if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec, $insert_Info)) {
                $this->errCode = -1;
                $this->setErrMessage("Archiviazione File. Errore Inserimento Record.");
                return false;
            }
            return $model->getLastInsertId();
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->setErrMessage("Archiviazione File. Errore Inserimento Record. " . $e->getMessage());
            return false;
        }
    }

    public function ApriFascicolo($nameform, $Geskey) {
        itaLib::openForm('proGestPratica');
        $modelAtto = itaModel::getInstance(proGestPratica);
        $modelAtto->setEvent('openform');
        $modelAtto->setReturnModel($nameform);
        $modelAtto->parseEvent();
        $modelAtto->Dettaglio($Geskey,  'geskey');
        return true;
    }

}

?>
