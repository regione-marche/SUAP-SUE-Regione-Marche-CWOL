<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segDocumentale.php';// Non utilizzato

class proLibIndice {

    public $segLib;
    public $proLib;
    public $PROTDB;
    public $SEGRDB;
    private $errCode;
    private $errMessage;

    function __construct() {
        $this->proLib = new proLib();
        $this->segLib = new segLib();
    }

    public function getSegLib() {
        return $this->segLib;
    }

    public function setSegLib($segLib) {
        $this->segLib = $segLib;
    }

    public function getProLib() {
        return $this->proLib;
    }

    public function setProLib($proLib) {
        $this->proLib = $proLib;
    }

    public function getSEGRDB() {
        return $this->SEGRDB;
    }

    public function setSEGRDB($SEGRDB) {
        $this->SEGRDB = $SEGRDB;
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

    /**
     * Nuova funzione per creare un Indice
     * @param itaModel $model
     * @param <type> $Indice_rec
     * @return <type>
     */
    public function creaIndice($model, $Indice_rec, $DatiProtocollo, $DatiRegistro = null) {
        $this->errCode = 0;
        /*
         * Contriolli
         */
        if (!$DatiProtocollo['PROUOF']) {
            $this->errCode = -1;
            $this->setErrMessage("Ufficio di appartenenza mancante.");
            return false;
        }
        if (!$Indice_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Riferimento Record Indice mancante.");
            return false;
        }
        if ($Indice_rec['ROWID']) {
            $this->errCode = -1;
            $this->setErrMessage("Metadati INDICE.ROWID presente: inserimento impossibile");
            return false;
        }

        if ($Indice_rec['IDELIB']) {
            $Indice_check_rec = $this->segLib->GetIndice($Indice_rec['IDELIB'], 'codice');
            if ($Indice_check_rec) {
                $this->errCode = -1;
                $this->setErrMessage("Metadati INDICE gia inserito: inserimento impossibile");
                return false;
            }
        }
        /*
         * Blocca la prenotazione per progressivo Anapro di Tipo I
         * 
         */
        $retLock = ItaDB::DBLock($this->segLib->getSEGRDB(), "INDICE", "", "", 20);
        if ($retLock['status'] !== 0) {
            $this->errCode = -1;
            $this->setErrMessage("Errore Blocco Inserimento archivio INDICE");
            return false;
        }

        $insert_Info = 'Oggetto: ' . $Indice_rec['IDELIB'] . " " . $Indice_rec['IOGGETTO'];
        if (!$model->insertRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $insert_Info)) {
            $this->errCode = -1;
            $this->setErrMessage("Inserimento INDICE {$Indice_rec['IDELIB']} fallito.");
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }
        $Indice_rec['ROWID'] = $model->getLastInsertId();

        $rowid_anapro = $this->getNewIndiceAnapro($model, $Indice_rec, $DatiProtocollo, $DatiRegistro);
        if (!$rowid_anapro) {
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }
        $anapro_I_rec = $this->proLib->GetAnapro($rowid_anapro, 'rowid');
        if (!$anapro_I_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Errore creazione istanza documentale.");
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }
        $Indice_rec['INDPRO'] = $anapro_I_rec['PRONUM'];
        $Indice_rec['INDPAR'] = $anapro_I_rec['PROPAR'];
        $update_Info = 'Oggetto: ' . $Indice_rec['IDELIB'] . " " . $Indice_rec['IOGGETTO'];
        if (!$model->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
            $this->setErrMessage("Inserimento INDICE {$Indice_rec['IDELIB']} fallito.");
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }

        ItaDB::DBUnLock($retLock['lockID']);
        return $Indice_rec;
    }

    /**
     * Nuova funzione per modificare un Indice
     * @param itaModel $model
     * @param <type> $Indice_rec
     * @return <type>
     */
    public function modificaIndice($model, $Indice_rec, $DatiProtocollo) {
        $this->errCode = 0;
        //
        // Controlli
        //
        if (!$DatiProtocollo['PROUOF']) {
            $this->errCode = -1;
            $this->setErrMessage("Ufficio di appartenenza mancante.");
            return false;
        }
        if (!$Indice_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Riferimento Record Indice mancante.");
            return false;
        }
        if (!$Indice_rec['ROWID']) {
            $this->errCode = -1;
            $this->setErrMessage("Metadati INDICE.ROWID NON presente: modifica impossibile");
            return false;
        }

        if ($Indice_rec['IDELIB']) {
            $Indice_check_rec = $this->segLib->GetIndice($Indice_rec['IDELIB'], 'codice');
            if (!$Indice_check_rec) {
                $this->errCode = -1;
                $this->setErrMessage("Metadati INDICE NON inserito: modifica impossibile");
                return false;
            }
        } else {
            $this->errCode = -1;
            $this->setErrMessage("Metadati INDICE NON inserito: modifica impossibile");
            return false;
        }

        $update_Info = 'Oggetto: ' . $Indice_rec['IDELIB'] . " " . $Indice_rec['IOGGETTO'];
        if (!$model->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
            $this->errCode = -1;
            $this->setErrMessage("Aggiornamento INDICE {$Indice_rec['IDELIB']} fallito.");
            return false;
        }

        $Indice_mod = $this->segLib->GetIndice($Indice_rec['ROWID'], 'rowid');
        $anapro_rec = $this->proLib->GetAnapro($Indice_mod['INDPRO'], 'codice', $Indice_mod['INDPAR']);

        if ($anapro_rec) {
            $this->sincIndiceAnaproSave($model, $anapro_rec, $Indice_mod);
            $this->sincIndiceAnapro($model, $anapro_rec, $Indice_mod, $DatiProtocollo);
        } else {
            //
            // Blocca la prenotazione per progressivo Anapro di Tipo I
            //
            $retLock = ItaDB::DBLock($this->segLib->getSEGRDB(), "INDICE", "", "", 20);
            if ($retLock['status'] !== 0) {
                $this->errCode = -1;
                $this->setErrMessage("Errore Blocco Aggiornamento archivio INDICE ");
                return false;
            }

            $rowid_anapro = $this->getNewIndiceAnapro($model, $Indice_rec, $DatiProtocollo);
            if (!$rowid_anapro) {
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            $anapro_I_rec = $this->proLib->GetAnapro($rowid_anapro, 'rowid');
            if (!$anapro_I_rec) {
                $this->errCode = -1;
                $this->setErrMessage("Errore creazione istanza documentale.");
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            $Indice_rec['INDPRO'] = $anapro_I_rec['PRONUM'];
            $Indice_rec['INDPAR'] = $anapro_I_rec['PROPAR'];
            $update_Info = 'Oggetto: ' . $Indice_rec['IDELIB'] . " " . $Indice_rec['IOGGETTO'];
            if (!$model->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
                $this->setErrMessage("Inserimento INDICE {$Indice_rec['IDELIB']} fallito.");
                ItaDB::DBUnLock($retLock['lockID']);
                return false;
            }
            ItaDB::DBUnLock($retLock['lockID']);
        }

        return $Indice_rec;
    }

    public function getAnaproIndice($Idelib) {
        $Indice_check_rec = $this->segLib->GetIndice($Idelib, 'codice');
        if (!$Indice_check_rec) {
            return false;
        }
        if (!$Indice_check_rec['INDPRO'] || !$Indice_check_rec['INDPAR']) {
            return false;
        }
        $sql = "SELECT * FROM ANAPRO WHERE PROPAR='I' AND PRONUM='" . $Indice_check_rec['INDPRO'] . "'";
        $anaproIndice_rec = $this->proLib->getGenericTab($sql, false);
        if (!$anaproIndice_rec) {
            return false;
        }
        return $anaproIndice_rec;
    }

    public function getNewIndiceAnapro($model, $Indice_rec, $DatiProtocollo, $Datiregistro = null) {
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();

        $profilo = proSoggetto::getProfileFromIdUtente();
        if (!$profilo['COD_SOGGETTO']) {
            $this->errCode = -1;
            $this->setErrMessage("Profilo Utente non accessibile");
            return false;
        }
        $codice = $protObj->PrenotaDocumentoIndice('LEGGI', date('Y'));
        $anapro_new = array();
        $anapro_new['PRONUM'] = $codice;
        $anapro_new['PROPAR'] = 'I';
        $anapro_new['PRODAR'] = date('Ymd');
        $anapro_new['PROCAT'] = '';
        $anapro_new['PROCCA'] = '';
        $anapro_new['PROCCF'] = '';
        $anapro_new['PROARG'] = '';
        $anapro_new['PRORDA'] = date('Ymd');
        $anapro_new['PROROR'] = date('H:i:s');
        $anapro_new['PROORA'] = date('H:i:s');
        $NomeUtente = App::$utente->getKey('nomeUtente');
        if ($DatiProtocollo['PROUTE']) {
            $NomeUtente = $DatiProtocollo['PROUTE'];
        }
        $anapro_new['PROUTE'] = $NomeUtente;
        $anapro_new['PROUOF'] = $DatiProtocollo['PROUOF'];
        $anapro_new['PROLOG'] = "999" . substr($NomeUtente, 0, 7) . date('d/m/y');
        $anapro_new['PROFASKEY'] = "";
        $anapro_new['PROSECURE'] = $DatiProtocollo['PROSECURE'];
        $anapro_new['PROTSO'] = $DatiProtocollo['PROTSO'];
        $anapro_new['PRORISERVA'] = $DatiProtocollo['PRORISERVA'];
        // Versione Titolario:
        $anapro_new['VERSIONE_T'] = $DatiProtocollo['VERSIONE_T'];
        $anapro_new['PROCAT'] = $DatiProtocollo['PROCAT'];
        $anapro_new['PROCCA'] = $DatiProtocollo['PROCCA'];
        $anapro_new['PROCCF'] = $DatiProtocollo['PROCCF'];
        // Tipo documento se presente.
        $anapro_new['PROCODTIPODOC'] = $DatiProtocollo['PROCODTIPODOC'];

        $segnatura = proSegnatura::getStringaSegnatura($anapro_new);
        if (!$segnatura) {
            $this->errCode = -1;
            $this->setErrMessage("Errore nella codifica della segnatura dell'indice.");
            return false;
        }
        $anapro_new['PROSEG'] = $segnatura;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $anapro_new);
            $rowid = $this->proLib->getPROTDB()->getLastId();
            $anaproNew_rec = $this->proLib->GetAnapro($anapro_new['PRONUM'], 'codice', $anapro_new['PROPAR']);
            if (!$anaproNew_rec) {
                $this->errCode = -1;
                $this->setErrMessage("Errore nella creazione instanza documento indice.");
                return false;
            }
            /* @var $protObj proProtocolla */
            $risultato = $protObj->saveOggetto($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $Indice_rec['IOGGETTO']);
            if (!$risultato) {
                $this->errCode = -1;
                $this->setErrMessage("Errore in salvataggio descrizione indice");
                return false;
            }

            $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
            $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
            $ananom_rec['NOMPAR'] = $anaproNew_rec['PROPAR'];
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANANOM', 'ROWID', $ananom_rec);

            $this->registraFirmatario($model, $DatiProtocollo, $anaproNew_rec);
            //@TODO CONTROLLO ERRORI
            $this->salvaUfficiInterni($model, $DatiProtocollo, $anaproNew_rec);
            $this->salvaDestinatariInterni($model, $DatiProtocollo, $anaproNew_rec);
            $this->salvaAltriDestinatari($model, $DatiProtocollo, $anaproNew_rec);

            /*
             * 
             * Inserisco il nuovo Anapro nel registro prestabilito dalla sigla stabilita dai parametri
             * 
             */
            if (is_array($Datiregistro) && 
                    isset($Datiregistro['SIGLA']) && 
                    isset($Datiregistro['ANNO']) &&
                    $Datiregistro['SIGLA'] &&
                    $Datiregistro['ANNO']) {
                App::requireModel('proLibRegistro.class');
                $proLibRegistro = new proLibregistro();
                $proregistroarc_rec = $proLibRegistro->insertRegistroAnapro($Datiregistro, $anaproNew_rec);
                if (!$proregistroarc_rec) {
                    $this->errCode = -1;
                    $this->setErrMessage($proLibRegistro);
                    return false;
                }
            }

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
            $this->setErrMessage("Errore in creazione istanza documento Indice: " . $exc->getTraceAsString());
            return false;
        }
    }

    public function sincIndiceAnapro($model, $anapro_rec, $Indice_rec, $DatiProtocollo) {
        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        $anapro_rec['PROORA'] = date('H:i:s');
        $anapro_rec['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anapro_rec['PROUOF'] = $DatiProtocollo['PROUOF'];
        $anapro_rec['PROSECURE'] = $DatiProtocollo['PROSECURE'];
        $anapro_rec['PROTSO'] = $DatiProtocollo['PROTSO'];
        $anapro_rec['PRORISERVA'] = $DatiProtocollo['PRORISERVA'];
        $anapro_rec['PROCAT'] = $DatiProtocollo['PROCAT'];
        $anapro_rec['PROCCA'] = $DatiProtocollo['PROCCA'];
        $anapro_rec['PROCCF'] = $DatiProtocollo['PROCCF'];
        $anapro_rec['PROCHI'] = $DatiProtocollo['PROCCHI'];

        $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $risultato = $protObj->saveOggetto($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $Indice_rec['IOGGETTO']);
        if (!$risultato) {
            $this->errCode = -1;
            $this->setErrMessage("Errore in salvataggio descrizione indice");
            return false;
        }

        $this->registraFirmatario($model, $DatiProtocollo, $anapro_rec);
        //@TODO CONTROLLO ERRORI
        $this->salvaUfficiInterni($model, $DatiProtocollo, $anapro_rec);
        $this->salvaDestinatariInterni($model, $DatiProtocollo, $anapro_rec);
        $this->salvaAltriDestinatari($model, $DatiProtocollo, $anapro_rec);

        $iter = proIter::getInstance($this->proLib, $anapro_rec);
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
        return true;
    }

    private function registraFirmatario($model, $DatiProtocollo, $anapro_rec) {
        $anades_rec = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
        if ($anades_rec) {
            if (!ItaDB::DBDelete($this->proLib->getPROTDB(), 'ANADES', 'ROWID', $anades_rec['ROWID'])) {
                return false;
            }
        }
        $firmatario = $DatiProtocollo['FIRMATARIO'];
        if ($firmatario) {
            $anades_rec = array();
            $anades_rec['DESNUM'] = $anapro_rec['PRONUM'];
            $anades_rec['DESPAR'] = $anapro_rec['PROPAR'];
            $anades_rec['DESCOD'] = $firmatario['DESCOD'];
            $anades_rec['DESNOM'] = $firmatario['DESNOM'];
            $anades_rec['DESCUF'] = $firmatario['DESCUF'];
            $anades_rec['DESTIPO'] = "M";
            $insert_Info = 'Inserimento Firmatario: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
            if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, $insert_Info)) {
                return false;
            }
        }
    }

    private function salvaUfficiInterni($model, $DatiProtocollo, $anapro_rec) {
        $uffpro_tab = $this->proLib->GetUffpro($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
        foreach ($uffpro_tab as $key => $uffpro_rec) {
            if (!$model->deleteRecord($this->proLib->getPROTDB(), 'UFFPRO', $uffpro_rec['ROWID'], '', 'ROWID', false)) {
                return false;
            }
        }
        foreach ($DatiProtocollo['UFFICI'] as $key => $record) {
            $uffpro_rec = array();
            $uffpro_rec['PRONUM'] = $anapro_rec['PRONUM'];
            $uffpro_rec['UFFPAR'] = $anapro_rec['PROPAR'];
            $uffpro_rec['UFFCOD'] = $record['UFFCOD'];
            $uffpro_rec['UFFFI1'] = $record['UFFFI1'];
            $insert_Info = 'Inserimento: ' . $uffpro_rec['PRONUM'] . ' ' . $uffpro_rec['UFFCOD'];
            if (!$model->insertRecord($this->proLib->getPROTDB(), 'UFFPRO', $uffpro_rec, $insert_Info)) {
                return false;
            }
        }
        return true;
    }

    private function salvaDestinatariInterni($model, $DatiProtocollo, $anapro_rec) {

        /* Salvo Destinatari */
        $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], 'T');
        if ($anades_tab) {
            foreach ($anades_tab as $key => $anades_rec) {
                if (!$model->deleteRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
        }

        if ($DatiProtocollo['DESTINATARIINTERNI']) {
            foreach ($DatiProtocollo['DESTINATARIINTERNI'] as $key => $record) {
                $anades_rec = array();
                $anades_rec['DESNUM'] = $anapro_rec['PRONUM'];
                $anades_rec['DESPAR'] = $anapro_rec['PROPAR'];
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
                $anades_rec['DESMAIL'] = $record['DESMAIL'];
                $anades_rec['DESSER'] = $record['DESSER'];
                $anades_rec['DESCUF'] = $record['DESCUF'];
                $anades_rec['DESGES'] = $record['DESGES'];
                $anades_rec['DESRES'] = $record['DESRES'];
                $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                $anades_rec['DESTERMINE'] = $record['TERMINE'];
                $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                $anades_rec['DESTIPO'] = "T";
                $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                $insert_Info = 'Inserimento Destinatario Interno: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, $insert_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function salvaAltriDestinatari($model, $DatiProtocollo, $anapro_rec) {
        /* Salvo Altri Destinatari */
        $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], 'D');
        if ($anades_tab) {
            foreach ($anades_tab as $key => $anades_rec) {
                if (!$model->deleteRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
        }

        if ($DatiProtocollo['ALTRIDESTINATARI']) {
            foreach ($DatiProtocollo['ALTRIDESTINATARI'] as $key => $record) {
                $anades_rec = array();
                $anades_rec['DESNUM'] = $anapro_rec['PRONUM'];
                $anades_rec['DESPAR'] = $anapro_rec['PROPAR'];
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
                $anades_rec['DESMAIL'] = $record['DESMAIL'];
                $anades_rec['DESSER'] = $record['DESSER'];
                $anades_rec['DESCUF'] = $record['DESCUF'];
                $anades_rec['DESGES'] = $record['DESGES'];
                $anades_rec['DESRES'] = $record['DESRES'];
                $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                $anades_rec['DESTERMINE'] = $record['TERMINE'];
                $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                $anades_rec['DESTSP'] = $record['DESTSP'];
                $anades_rec['DESTIPO'] = "D";
                $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                $anades_rec['DESRUO_EXT'] = $record['DESRUO_EXT'];
                $insert_Info = 'Inserimento Destinatario Interno: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, $insert_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function AnnullaRiattivaIndice($model, $Idelib, $motivo, $modo = '') {
        if (!$Idelib) {
            $this->errCode = -1;
            $this->setErrMessage("Codice Indice da annullare mancante.");
            return false;
        }
        $Indice_rec = $this->segLib->GetIndice($Idelib, 'codice');
        if (!$Indice_rec) {
            $this->errCode = -1;
            $this->setErrMessage("Indice da annullare mancante.");
            return false;
        }
        if (!$modo) {
            $this->errCode = -1;
            $this->setErrMessage("Modo non definito.");
            return false;
        }
        if ($modo == 'annulla') {
            $tipo = substr($Indice_rec['INDPAR'], 0, 1) . 'A';
            //Valorizzo le date annullamento e  utente
            $Indice_rec['DATAANNULLAMENTO'] = date('ymd');
            $Indice_rec['UTENTEANNULLAMENTO'] = App::$utente->getKey('nomeUtente');
            $update_Info = 'Oggetto: Annullamento Indice ' . $Indice_rec['IDELIB'];
        } else {
            $tipo = substr($Indice_rec['INDPAR'], 0, 1);
            //Valorizzo le date annullamento e  utente
            $Indice_rec['DATAANNULLAMENTO'] = '';
            $Indice_rec['UTENTEANNULLAMENTO'] = App::$utente->getKey('nomeUtente');
            $update_Info = 'Oggetto: Riattivazione Indice ' . $Indice_rec['IDELIB'];
        }
        /*
         *  Prendo ANAPRO
         */
        $sql = "SELECT * FROM ANAPRO WHERE PROPAR='" . $Indice_rec['INDPAR'] . "' AND PRONUM='" . $Indice_rec['INDPRO'] . "'";
        $anapro_rec = $this->proLib->getGenericTab($sql, false);

//        //Prendo ARCITE
//        $arcite_tab = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
//        if (count($arcite_tab) > 0) {
//            foreach ($arcite_tab as $arcite_rec) {
//                $arcite_rec['ITEPAR'] = $tipo;
//                $update_Info = 'Oggetto: ' . $arcite_rec['PRONUM'];
//                if (!$model->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
//                    return false;
//                }
//            }
//        }
//
//        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//        if ($anaogg_rec) {
//            $anaogg_rec['OGGPAR'] = $tipo;
//            $update_Info = 'Oggetto: ' . $anaogg_rec['OGGPAR'];
//            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAOGG', $anaogg_rec, $update_Info)) {
//                return false;
//            }
//        }
//        $ananom_rec = $this->proLib->GetAnanom($anapro_rec['PRONUM'], false, $anapro_rec['PROPAR']);
//        if ($ananom_rec) {
//            $ananom_rec['NOMPAR'] = $tipo;
//            $update_Info = 'Oggetto: ' . $ananom_rec['NOMPAR'];
//            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANANOM', $ananom_rec, $update_Info)) {
//                return false;
//            }
//        }
//        $anapro_rec['PROPAR'] = $tipo;
        $anapro_rec['PROSTATOPROT'] = proLib::PROSTATO_ANNULLATO;
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }
//        // Aggiorno Indice
//        $Indice_rec['INDPAR'] = $tipo;
//        if (!$model->updateRecord($this->segLib->getSEGRDB(), 'INDICE', $Indice_rec, $update_Info)) {
//            return false;
//        }

        $model->insertAudit($this->segLib->getSEGRDB(), 'INDICE', "Oggetto: $modo Indice $Idelib (Motivo: $motivo)");
        return true;
    }

    private function sincIndiceAnaproSave($model, $anapro_rec) {
        $motivo = 'Aggiornamento Indice.';
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave($motivo, $anapro_rec['ROWID'], 'rowid');
        return true;
    }

}

?>
