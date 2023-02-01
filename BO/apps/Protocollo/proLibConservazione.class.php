<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo Conservazione DigiP Marche
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    16.10.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDigiPMarche.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class proLibConservazione extends itaModel {

    private $errCode;
    private $errMessage;
    private $retStatus;
    private $retEsito;
    public $PROT_DB;
    private $proLib;
    private $proLibTabDag;
    private $countProtConservati;
    private $msgInfoConservazione = array();

    const FONTE_DATI_ESITO_CONSERVAZIONE = 'ESITO_CONSERVAZIONE';
    const FONTE_DATI_STORICO_ESITO_CONSERVAZIONE = 'STORICO_ESITO_CONSER';
    const CHIAVE_ESITO_CONSERVATORE = 'Conservatore';
    const CHIAVE_ESITO_VERSIONE = 'Versione';
    const CHIAVE_ESITO_DATAVERSAMENTO = 'DataVersamento';
    const CHIAVE_ESITO_UTENTEVERSAMENTO = 'UtenteVersamento';
    const CHIAVE_ESITO_ESITO = 'Esito';
    const CHIAVE_ESITO_FILE = 'FileEsito';
    const CHIAVE_ESITO_FILE_RICHIESTA = 'FileRichiesta';
    const CHIAVE_ESITO_CODICEERRORE = 'CodiceErrore';
    const CHIAVE_ESITO_MESSAGGIOERRORE = 'MessaggioErrore';
    const CHIAVE_ESITO_CHIAVEVERSAMENTO = 'ChiaveVersamento';
    const CHIAVE_ESITO_IDVERSAMENTO = 'IdSIP';
    const ESITO_POSTITIVO = 'POSITIVO';
    const ESITO_WARNING = 'WARNING';
    const ESITO_NEGATIVO = 'NEGATIVO';
    const ESITO_BLOCCATO = 'BLOCCATO';
    const ESITO_SOSPESO = 'SOSPESO';
    const ESITO_DAVERIFICARE = 'DA_VERIFICARE';
    const ERR_CODE_SUCCESS = 0;
    const ERR_CODE_FATAL = -1;
    const ERR_CODE_QUESTION = -2;
    const ERR_CODE_INFO = -3;
    const ERR_CODE_WARNING = -4;
    const TIPO_CONSERVAZIONE_NESSUNA = '';
    const TIPO_CONSERVAZIONE_DIGIP_MARCHE = 'DIGIP_MARCHE';
    const TIPO_CONSERVAZIONE_ASMEZDOC = 'ASMEZDOC';
    const TIPO_CONSERVAZIONE_NAMIRIAL = 'NAMIRIAL';
    // Costanti dei motivi.
    const MOTIVO_VERSAMENTO = 'VERSAMENTO';
    const MOTIVO_MODIFICA = 'MODIFICA';
    const MOTIVO_ANNULLAMENTO = 'ANNULLAMENTO';
    /*
     * Costanti Esiti Conservazione
     */
    const CONSER_ESITO_POSTITIVO = 'POSITIVO';
    const CONSER_ESITO_NEGATIVO = 'NEGATIVO';
    const CONSER_ESITO_NON_VERIFICATO = '';

    /*
     * Chiavi Unita Documentaria
     */
    const K_UNIT_REGISTRO = 'REGISTRO';
    const K_UNIT_PROT = 'PROTOCOLLO';
    const K_UNIT_PROT_INTERNO = 'PROTOCOLLO_INTERNO';
    const K_UNIT_PROT_AGG = 'PROTOCOLLO_AGGIORNATO';
    const K_UNIT_PROT_INTERNO_AGG = 'PROTOCOLLO_INTERNO_AGGIORNATO';
    const K_UNIT_PROT_ANN = 'PROTOCOLLO_ANNULLATO';
    const K_UNIT_PROT_INTERNO_ANN = 'PROTOCOLLO_INTERNO_ANNULLATO';
    // Chiavi Controlli Anomalie
    const CTR_NON_CONSERVATO = 'GIORNI_MANCANTI';
    const CTR_VARIATO = 'VARIATO';
    const CTR_NON_CONSERVABILE = 'NON_CONSERVABILE';

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibTabDag = new proLibTabDag();
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

    function getRetEsito() {
        return $this->retEsito;
    }

    function setRetEsito($retEsito) {
        $this->retEsito = $retEsito;
    }

    public function getRetStatus() {
        return $this->retStatus;
    }

    public function setRetStatus($retStatus) {
        $this->retStatus = $retStatus;
    }

    public function getCountProtConservati() {
        return $this->countProtConservati;
    }

    public function getMsgInfoConservazione() {
        return $this->msgInfoConservazione;
    }

    /*
     * Elenco Descrittivo Controlli Esito 
     */

    public static $ElencoDescrCtrEsito = array(
        self::ESITO_POSTITIVO => 'Versato',
        self::CTR_NON_CONSERVATO => 'Non Versato',
        self::ESITO_NEGATIVO => 'Errore in Versamento',
        self::ESITO_WARNING => 'Versato con Anomalie',
        self::CTR_VARIATO => 'Protocollo Variato',
        self::CTR_NON_CONSERVABILE => 'Protocollo non Conservabile',
        self::ESITO_BLOCCATO => 'Protocollo Bloccato',
        self::ESITO_DAVERIFICARE => 'Versato. Conservazione da verificare.',
        self::ESITO_SOSPESO => 'In Sospeso'
    );

    /*
     * Colori Controlli Esito
     */
    public static $ElencoColoriCtrEsito = array(
        self::ESITO_POSTITIVO => '#32CD32',
        self::CTR_NON_CONSERVATO => '#191970',
        self::ESITO_NEGATIVO => '#FF0000',
        self::ESITO_WARNING => '#FFFF00',
        self::CTR_VARIATO => '#ff8c00',
        self::CTR_NON_CONSERVABILE => '#3399ff',
        self::ESITO_BLOCCATO => '#993399',
        self::ESITO_SOSPESO => '#009999',
    );


    /*
     * Elenco Descrittivo Controlli Esito Conservazione
     */
    public static $ElencoDescrConserEsito = array(
        self::CONSER_ESITO_POSTITIVO => 'Conservato',
        self::CONSER_ESITO_NEGATIVO => 'Errore in Conservazione',
        self::CONSER_ESITO_NON_VERIFICATO => 'Non Verificato'
    );

    /*
     * Colori Controlli Esito Conservazione
     */
    public static $ElencoColoriConserEsito = array(
        self::CONSER_ESITO_POSTITIVO => '#32CD32',
        self::CONSER_ESITO_NEGATIVO => '#FF0000',
        self::CONSER_ESITO_NON_VERIFICATO => '#191970'
    );

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
     * Ritorna i dati esito conservazione legati ad un record di ANAPRO "FONTE=ESITO_CONSERVAZIONE"
     * 
     * @param integer $rowid_anapro id del record Anapro
     * @return array Elementi dell'esito
     */
    public function GetEsitoConservazione($rowid_anapro, $chiave = '') {
        $arrMetadati = $this->proLibTabDag->GetFonteTabdag("ANAPRO", $rowid_anapro, self::FONTE_DATI_ESITO_CONSERVAZIONE, 0);
        $arrEsito = array();
        foreach ($arrMetadati[0] as $key => $value) {
            $arrEsito[$value[
                    'TDAGCHIAVE']] = $value['TDAGVAL'];
        }
        return $arrEsito;
    }

    public function GetEsitoConservazioneValore($rowid_anapro, $chiave) {
        $valore = '';
        $Tabdag_rec = $this->proLibTabDag->GetTabdag("ANAPRO", 'chiave', $rowid_anapro, $chiave, 0, false, '', self::FONTE_DATI_ESITO_CONSERVAZIONE);
        if ($Tabdag_rec) {


            $valore = $Tabdag_rec['TDAGVAL'];
        }
        return $valore;
    }

    public function CancellaEsitoConservazione($rowid_anapro) {
        if (!$rowid_anapro) {
            $this->setErrCode(self::ERR_CODE_FATAL);
            $this->setErrMessage("Indice Documento non fornito.<br> " . $e->getMessage());
            return false;
        } $Tabdag_tab = $this->proLibTabDag->GetFonteTabdag("ANAPRO", $rowid_anapro, self::FONTE_DATI_ESITO_CONSERVAZIONE, 0);
        foreach ($Tabdag_tab[0] as $Tabdag_rec) {
            try {
                ItaDB::DBDelete($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $Tabdag_rec['ROWID']);
            } catch (Exception $e) {
                $this->setErrCode(self::ERR_CODE_FATAL);
                $this->setErrMessage("Errore in cancellazione TABDAG.<br> " . $e->getMessage());
                return false;
            }
        }
        return;
    }

    /**
     * Storicizza precedenti esiti negativi di versamento nella fonte dati STORICO_ESITO_CONSERVAZIONE
     * 
     * @param type $rowid_anapro
     */
    public function StoricizzaEsitoConservazione($rowid_anapro) {
        $arrEsito = $this->GetEsitoConservazione($rowid_anapro);
        if ($arrEsito) {
            // Storicizzo Nuova Tabella:
            if (!$this->StoricizzaProconser($rowid_anapro)) {
                return false;
            }

            $Nprog = $this->proLibTabDag->GetProssimoProgFonte("ANAPRO", $rowid_anapro, self::FONTE_DATI_STORICO_ESITO_CONSERVAZIONE);
            if ($Nprog === false) {
                $this->setErrCode(self::ERR_CODE_FATAL);
                $this->setErrMessage("Errore prenotazione progressivo dataset storico");
                return false;
            } $arrMetadati = $this->proLibTabDag->GetFonteTabdag("ANAPRO", $rowid_anapro, self::FONTE_DATI_STORICO_ESITO_CONSERVAZIONE, $Nprog);
            if ($arrMetadati) {
                $this->setErrCode(self::ERR_CODE_FATAL);
                $this->setErrMessage("Errore progressivo dataset storico già esistente");
                return false;
            } if (!$this->proLibTabDag->SalvataggioFonteTabdag("ANAPRO", $rowid_anapro, self::FONTE_DATI_STORICO_ESITO_CONSERVAZIONE, $arrEsito, $Nprog, false)) {
                $this->setErrCode(self::ERR_CODE_FATAL);
                $this->setErrMessage("Storicizzazione Esito: " . $this->proLibTabDag->getErrMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Storicizzo la tabella PROCONSER
     * 
     * @param type $rowid_anapro
     */
    public function StoricizzaProconser($rowid_anapro) {
        $sql = "SELECT * FROM PROCONSER WHERE ROWID_ANAPRO = $rowid_anapro AND FLSTORICO = 0";
        $Proconser_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        /* Se storico non è presente: non c'è da storicizzare nulla */
        if (!$Proconser_rec) {
            return true;
        }
        try {
            $Proconser_rec['FLSTORICO'] = 1;
            ItaDB::DBUpdate($this->getPROTDB(), 'PROCONSER', 'ROWID', $Proconser_rec);
            return true;
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento PROCONSER.<br> " . $e->getMessage());
            return false;
        }
    }

    /**
     * 
     * Conservazione registro giornaliero di protocollo
     * 
     * @param int $rowid ROWID di ANAPRO da conservare
     * @param array $param Array con parametri vari
     *              $param['TIPOCONSERVAZIONE'] = tipo di conservatore 
     *              $param['NOMEFILEESITO']     = nome logico del file esito che viene allegato 
     *                                            al documento formale del registro
     * @return boolean
     */
    public function conservaAnapro($rowid, $param = array()) {

        $this->setErrCode(self::ERR_CODE_SUCCESS);
        $this->setErrMessage("");
        $this->setRetStatus("");
        $this->setRetEsito(null);

        if ($rowid == '' || $rowid == 'null') {
            $this->setErrCode(self::ERR_CODE_FATAL);
            $this->setErrMessage("Occorre selezionare il documento da conservare.");
            return false;
        }

        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        if (!$Anapro_rec) {
            $this->setErrCode(self::ERR_CODE_FATAL);
            $this->setErrMessage("Documento da versare non accessibile.");
            return false;
        }

        $fl_esito_presente = false;
        $esito_rec = $this->GetEsitoConservazione($rowid);
        if ($esito_rec) {
            $fl_esito_presente = true;
            if ($esito_rec['Esito'] == "NEGATIVO") {
                $this->setErrCode(self::ERR_CODE_QUESTION);
                $this->setErrMessage("Tentativo di versamento già effettuato:<br/><br/>Esito: {$esito_rec['Esito'] }<br/>Messaggio: {$esito_rec['MessaggioErrore']}");
                //return false;
            } elseif ($esito_rec['Esito'] == "WARNING") {
                $this->setErrCode(self::ERR_CODE_INFO);
                $this->setErrMessage("Documento gia versato con esito:<br>{$esito_rec['Esito']}<br>{$esito_rec['MessaggioErrore']}");
                return false;
            } elseif ($esito_rec['Esito'] == "POSITIVO") {
                $this->setErrCode(self::ERR_CODE_INFO);
                $this->setErrMessage("Versamento in conservazione", "Documento gia versato con esito:<br>{$esito_rec['Esito']}<br>{$esito_rec['MessaggioErrore']}");
                return false;
            }
        }

        switch ($param['TIPOCONSERVAZIONE']) {
            case self::TIPO_CONSERVAZIONE_DIGIP_MARCHE:
            case self::TIPO_CONSERVAZIONE_ASMEZDOC:
            case self::TIPO_CONSERVAZIONE_NAMIRIAL:

                $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                if (!$Anapro_rec) {
                    Out::msgStop("Attenzione", "Protocollo inesistente" . $rowid);
                    return;
                }
                /*
                 * Istanzio il Manager
                 */
                $ObjManager = proConservazioneManagerFactory::getManager();
                if (!$ObjManager) {
                    Out::msgStop('Attenzione', 'Errore in istanza Manager.');
                    return;
                }
                /*
                 * Lettura unità documentaria
                 */
                $UnitaDoc = $this->GetUnitaDocumentaria($Anapro_rec);

                /*
                 * Setto Chiavi Anapro
                 */
                $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
                $ObjManager->setUnitaDocumentaria($UnitaDoc);
                /*
                 *  Lancio la conservazione
                 */
                if (!$ObjManager->conservaAnapro()) {
                    $this->setErrCode(self::ERR_CODE_FATAL);
                    $this->setErrMessage('Errore in conservazione. ' . $ObjManager->getErrMessage());
                    return false;
                } else {
                    $this->setRetEsito($ObjManager->getRetEsito());
                    return true;
                }
                break;

            case 'TIPO_CONSERVAZIONE_ASMEZDOCOLD':
                include_once ITA_BASE_PATH . '/apps/Protocollo/proAsmezDoc.class.php';
                $driverVersamento = new proAsmezDoc();
                $ParametriAsmezDoc = $driverVersamento->GetParametri();
                if (!$ParametriAsmezDoc['WSWEBPROTREGENDPOINT']) {
                    $this->setErrCode(self::ERR_CODE_FATAL);
                    $this->setErrMessage("Non è possibile procedere. Parametri ASMEZ 10 Conservazione non definiti.");
                    return false;
                }
                $ret = $driverVersamento->versaAsmezDocWebProtReg($rowid);
                if ($ret) {
                    $xmlResp = $driverVersamento->getXmlResponso();
                    $xmlRichiesta = $driverVersamento->getXmlRichiesta();
                }
                break;
            default:
                $this->setErrCode(self::ERR_CODE_FATAL);
                $this->setErrMessage("Tipo di conservazione: {$param['TIPOCONSERVAZIONE']} non previsto.");
                return false;
        }
        // Rimasto solo per ASMEZ: da allineare.
        if ($ret) {

            /**
             * Storicizzo il precedente esito conservazione se presente
             */
            if ($fl_esito_presente) {
                if (!$this->StoricizzaEsitoConservazione($rowid)) {
                    $this->setErrCode(self::ERR_CODE_WARNING);
                }
            }

            /*
             * Salvo gli allegati
             */
            $proLibAllegati = new proLibAllegati();
            $subPath = "proConservazione-work-" . md5(microtime());
            $tempPath = itaLib::createAppsTempPath($subPath);
            /*
             *  Salvo L'allegato richiesta di servizio (xml)
             */
            $AllegatoDiServizio = array();
            $errSalvaRichiesta = false;
            $randNameRichiesta = md5(rand() * time()) . ".xml";
            $DestinoXmlRichiesta = $tempPath . "/" . $randNameRichiesta;
            if (file_put_contents($DestinoXmlRichiesta, $xmlRichiesta)) {
                $datetime_esito = date('Ymd_His');
                $NomeFileRichiesta = $FileInfo = "RICHIESTA_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml";
                $AllegatoDiServizio[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $DestinoXmlRichiesta,
                    'FILENAME' => $randNameRichiesta,
                    'FILEINFO' => $FileInfo,
                    'DOCTIPO' => 'ALLEGATO',
                    'DAMAIL' => '',
                    'DOCNAME' => $NomeFileRichiesta,
                    'DOCIDMAIL' => '',
                    'DOCFDT' => date('Ymd'),
                    'DOCRELEASE' => '1',
                    'DOCSERVIZIO' => 1,
                );
                $risultato = $proLibAllegati->GestioneAllegati($this, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
                if (!$risultato) {
                    $this->setErrCode(self::ERR_CODE_WARNING);
                    $this->setErrMessage($proLibAllegati->getErrMessage());
                    $errSalvaRichiesta = true;
                }
            } else {
                $this->setErrCode(self::ERR_CODE_WARNING);
                $this->setErrMessage("Errore in salvataggio contenuto file xml.");
                $errSalvaRichiesta = true;
            }


            /*
             *  Salvo L'allegato esito di servizio (xml)
             */
            $AllegatoDiServizio = array();
            $errSalva = false;
            $randName = md5(rand() * time()) . ".xml";
            $DestinoXml = $tempPath . "/" . $randName;
            if (file_put_contents($DestinoXml, $xmlResp)) {
                $datetime_esito = date('Ymd_His');
                if ($param['NOMEFILEESITO']) {
                    $NomeFile = $FileInfo = $param['NOMEFILEESITO'];
                } else {
                    $NomeFile = $FileInfo = "ESITO_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml";
                }
                $AllegatoDiServizio[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $DestinoXml,
                    'FILENAME' => $randName,
                    'FILEINFO' => $FileInfo,
                    'DOCTIPO' => 'ALLEGATO',
                    'DAMAIL' => '',
                    'DOCNAME' => $NomeFile,
                    'DOCIDMAIL' => '',
                    'DOCFDT' => date('Ymd'),
                    'DOCRELEASE' => '1',
                    'DOCSERVIZIO' => 1,
                );
                /*
                 * Salvataggio XML risultato:
                 * 
                 */
                $risultato = $proLibAllegati->GestioneAllegati($this, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
                if (!$risultato) {
                    $this->setErrCode(self::ERR_CODE_WARNING);
                    $this->setErrMessage($proLibAllegati->getErrMessage());
                    $errSalva = true;
                }
            } else {
                $this->setErrCode(self::ERR_CODE_WARNING);
                $this->setErrMessage("Errore in salvataggio contenuto file xml.");
                $errSalva = true;
            }



            /*
             *  Se da errore il salvataggio file
             *  provo comunque a salvare i metadati:
             */
            $arrTabdag = $driverVersamento->getDatiMinimiEsitoVersamento();
            /* Aggiungo il nome file esito relativo */
            $arrTabdag[self::CHIAVE_ESITO_FILE] = $NomeFile;
            $arrTabdag[self::CHIAVE_ESITO_FILE_RICHIESTA] = $NomeFileRichiesta;
            $arrTabdag[self::CHIAVE_ESITO_UTENTEVERSAMENTO] = App::$utente->getKey('nomeUtente');

            if (!$this->SalvaMetadatiConservazione($Anapro_rec, $arrTabdag)) {
                $this->setErrCode(proLibConservazione::ESITO_WARNING);
                $this->setErrMessage('Salvataggio dati aggiuntivi Esito fallito. ');
                $this->setRetEsito($driverVersamento->getDatiMinimiEsitoVersamento());
                return true;
            }

            if (!$this->proLibTabDag->SalvataggioFonteTabdag("ANAPRO", $rowid, self::FONTE_DATI_ESITO_CONSERVAZIONE, $arrTabdag, 0, true)) {
                $this->setErrCode(self::ESITO_WARNING);
                $this->setErrMessage('Salvataggio dati aggiuntivi Esito fallito: ' . $this->proLibTabDag->getErrMessage());
            }
            $this->setRetEsito($driverVersamento->getDatiMinimiEsitoVersamento());
        } else {
            $this->setErrCode(self::ERR_CODE_FATAL);
            $this->setErrMessage('Errore in riversamento:' . $driverVersamento->getErrMessage());
            return false;
        }
        return true;
    }

    public function CheckProtocolloConservabile($Anapro_rec) {
        /*
         * 1. Controllo allegati presenti e conservabili
         */
        $Anadoc_tab = proConservazioneManagerHelper::GetAnadocDaConservare($Anapro_rec);
        if (!$Anadoc_tab) {
            $this->setErrCode(-1);
            //$AllegatiNoCons = proConservazioneManagerHelper::getAllegatiNonConservabili();
            if (!proConservazioneManagerHelper::getAllegatiNonConservabili()) {
                $this->setErrMessage("Nessun allegato presente.");
            } else {
                $this->setErrMessage("Nessun allegato conservabile presente. Uno o più allegati non rispettano i formati conservabili.");
            }
            return false;
        }

        /*
         * 2. Controllo File: Esistenza e Impronta
         */
        $proLibAllegati = new proLibAllegati();
//        foreach ($Anadoc_tab as $Anadoc_rec) {
//            $HasFile = $proLibAllegati->GetHashDocAllegato($Anadoc_rec['ROWID']);
//            if (!$HasFile) {
//                $this->setErrMessage("Il file di un allegato non è stato trovato.");
//                return false;
//            }
//            if ($Anadoc_rec['DOCSHA2'] != $HasFile) {
//                $this->setErrMessage("Impronta del file non corrispondente.");
//                return false;
//            }
//        }

        /*
         * 3. Controllo se è possibile conservare prot con allegati non conservabili
         */
        $anaent_53 = $this->proLib->GetAnaent('53');
        if (!$anaent_53['ENTDE6']) {
            if (proConservazioneManagerHelper::getAllegatiNonConservabili()) {
                $this->setErrCode(-1);
                $this->setErrMessage("Uno o più allegati non rispettano i formati conservabili.");
                return false;
            }
        }

        /*
         * 4. Controllo Protocollo Annullato: e mai conservato.
         */
        if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            if (!$this->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Protocollo annullato, non conservabile.");
                return false;
            }
        }


        /*
         * 5. Controllo doc alla firma
         */
        foreach ($Anadoc_tab as $Anadoc_rec) {
            $docfirma_check = $proLibAllegati->GetDocfirma($Anadoc_rec['ROWID'], 'rowidanadoc');
            if ($docfirma_check) {
                if (!$docfirma_check['FIRDATA'] && !$docfirma_check['FIRANN']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("É presente almeno un documento alla firma che deve essere ancora firmato.");
                    return false;
                }
            }
        }

        /*
         * 6. Protocollo Bloccato
         */
        $Proconser_rec = $this->CheckProtocolloBloccato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if ($Proconser_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il protocollo è bloccato e non conservabile.");
            return false;
        }



        /*
         * 7. Controllo Notifica Eccezioni presenti:
         * SOSPESO: valutare se può andare in conservazione anche con 1 sola anomalia.

          include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
          $Mail_tab = proConservazioneManagerHelper::GetElencoMailProt($Anapro_rec);
          foreach ($Mail_tab as $Mail_rec) {
          switch ($Mail_rec['PECTIPO']) {
          case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
          case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
          case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
          case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
          case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:
          $this->setErrCode(-1);
          $this->setErrMessage("É presente almeno una anomalia per le pec inivate ai destinatari.");
          return false;
          default:
          break;
          }
          }
         */


        /*
         * 3. Controllo se protocollo già conservato
         *     - Possibile chiamare una funzione per verificare variazioni al protocollo.
         */


        /*
         * 4 Può controllare il numero di 
         *   tentativi automatici effettuati e se deve continuare. [?]
         */

        /*
         * 5. Controllo se è una partenza e
         *    non sono state ricevute le cosegne
         */


        /*
         * 6. Protocollo non fascicolato
         * !! Qui possibilità di aggiungere un 
         *    parametro per renderlo oblitagorio o meno !!
         */


        /*
         * 7. Protocollo Fascicolato, ma con fascicolo aperto.
         *  Secondario, implementare un parametro.
         */

        /*
         * 8. Controlla se il protocollo 
         *    rientra nei limiti impostati nei parametri.
         */

        /*
         * 9. Controllo Allegati Stesso hash
         *  Principale sempre, altrimenti il primo.
         */

        return true;
    }

    public function CheckProtocolloVariato() {
        /*
         * Può utilizzare array "BaseDati" per 
         * verificare i campi chiave e le eventuali differenze.
         * 
         * 
         * Modifiche che attivano la modifica della conservazione:
         * 
         * destinatari esterni
         * oggetto modificabile
         * Titolario Modificabile
         * Firmatario modificabile
         * dest interni modificabili
         * 
         */
    }

    /**
     * Funzione per controllo se un protocollo risulta versato.
     *  Ritorna l'ultimo versamento positivo.[che sia storico o no]
     * @param type $Anapro_rec
     */
    public function CheckProtocolloVersato($Pronum, $Propar) {
        /*
         * Controllo se c'è stato almeno un versamento positivo.
         * Anche se sono presenti modifiche successive, comunque il protocollo è conservato.
         *      Prevedere Motivo: Annullamento e Modifica ?
         */
        $sql = "SELECT * FROM PROCONSER "
                . "WHERE PRONUM = " . $Pronum . " AND PROPAR =  '" . $Propar . "' "
                . " AND MOTIVOVERSAMENTO = '" . self::MOTIVO_VERSAMENTO . "' "
                . " AND (ESITOVERSAMENTO = '" . self::ESITO_POSTITIVO . "' OR ESITOVERSAMENTO = '" . self::ESITO_WARNING . "' )"
                . " AND FLSTORICO = 0 ORDER BY PROGVERSAMENTO DESC ";
        $ProConser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($ProConser_rec) {
            return $ProConser_rec;
        }
        return false;
    }

    /**
     * Controllo se protocollo è bloccato.
     * @param type $Anapro_rec
     */
    public function CheckProtocolloBloccato($Pronum, $Propar) {
        /*
         * Controllo se c'è stato almeno un versamento positivo.
         * Anche se sono presenti modifiche successive, comunque il protocollo è conservato.
         *      Prevedere Motivo: Annullamento e Modifica ?
         */
        $sql = "SELECT * FROM PROCONSER "
                . "WHERE PRONUM = " . $Pronum . " AND PROPAR =  '" . $Propar . "' "
                . " AND ESITOVERSAMENTO = '" . self::ESITO_BLOCCATO . "'"
                . " AND FLSTORICO = 0 ORDER BY PROGVERSAMENTO DESC ";
        $ProConser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($ProConser_rec) {
            return $ProConser_rec;
        }
        return false;
    }

    /*
     * Controllo se il protocollo è da aggiornare
     */

    public function CheckProtocolloDaAggiornare($Pronum, $Propar) {
        $updTipo = array(self::K_UNIT_PROT_AGG, self::K_UNIT_PROT_INTERNO_AGG);
        $where = " AND PROUPDATECONSER.PRONUM = $Pronum AND PROUPDATECONSER.PROPAR = '$Propar' ";
        $sql = $this->GetSqlElencoProtocolliVariati($updTipo, $where, true);
        $ProUpdateConser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($ProUpdateConser_rec) {
            return $ProUpdateConser_rec;
        }
        return false;
    }

    /**
     * Funzione per controllo se un protocollo risulta versato e sospeso
     *  Ritorna l'ultimo versamento positivo.[che sia storico o no]
     * @param type $Anapro_rec
     */
    public function CheckProtocolloVersatoSospeso($Pronum, $Propar) {
        /*
         * Controllo se c'è stato almeno un versamento sospeso. 
         */
        $sql = "SELECT * FROM PROCONSER "
                . "WHERE PRONUM = " . $Pronum . " AND PROPAR =  '" . $Propar . "' "
                . " AND MOTIVOVERSAMENTO = '" . self::MOTIVO_VERSAMENTO . "' "
                . " AND (ESITOVERSAMENTO = '" . self::ESITO_SOSPESO . "' OR ESITOVERSAMENTO = '" . self::ESITO_WARNING . "' )"
                . " AND FLSTORICO = 0 ORDER BY PROGVERSAMENTO DESC ";
        $ProConser_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($ProConser_rec) {
            return $ProConser_rec;
        }
        return false;
    }

    public function GetProConser($Codice, $TipoRic = 'codice', $TipoProt = '', $multi = false) {
        switch ($TipoRic) {
            case 'codice':
                $sql = "SELECT * FROM PROCONSER WHERE PRONUM = " . $Codice . " AND PROPAR =  '" . $TipoProt . "' ";
                $sql.= "AND FLSTORICO = 0 ";
                break;

            default:
                $sql = "SELECT * FROM PROCONSER WHERE ROWID = " . $Codice;
                break;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetProgressivoConservazione($Anapro_rec) {
        $sql = "SELECT MAX(PROGVERSAMENTO) AS MAXPROG FROM PROCONSER WHERE PRONUM = " . $Anapro_rec['PRONUM'] . " AND PROPAR =  '" . $Anapro_rec['PROPAR'] . "' ";
        $ProconserMax_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        $Progressivo = $ProconserMax_rec['MAXPROG'] + 1;
        return $Progressivo;
    }

    public function SalvaMetadatiConservazione($Anapro_rec, $arrTabdag = array()) {
        // PREVEDERE STORICIZZAZIONE FL0
        // StoricizzaProconser(); Già prevista.
        $ProConser_rec = array();
        $ProConser_rec['PRONUM'] = $Anapro_rec['PRONUM'];
        $ProConser_rec['PROPAR'] = $Anapro_rec['PROPAR'];
        $ProConser_rec['ROWID_ANAPRO'] = $Anapro_rec['ROWID'];
        /*
         * Estrazione Fonte Dati e Valori
         */
        $ProConser_rec['PROGVERSAMENTO'] = $this->GetProgressivoConservazione($Anapro_rec);
        $ProConser_rec['DATAVERSAMENTO'] = date("Ymd", strtotime($arrTabdag[prolibConservazione::CHIAVE_ESITO_DATAVERSAMENTO]));
        $ProConser_rec['MOTIVOVERSAMENTO'] = proLibConservazione::MOTIVO_VERSAMENTO;
        $ProConser_rec['ESITOVERSAMENTO'] = $arrTabdag[proLibConservazione::CHIAVE_ESITO_ESITO]; //Corretto? o da rielaborare?
        $ProConser_rec['DOCVERSAMENTO'] = $arrTabdag[proLibConservazione::CHIAVE_ESITO_FILE_RICHIESTA];
        $ProConser_rec['DOCESITO'] = $arrTabdag[proLibConservazione::CHIAVE_ESITO_FILE];
        $ProConser_rec['COD_UNITA_DOCUMENTARIA'] = 'STRG';
        $ProConser_rec['CONSERVATORE'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_CONSERVATORE];
        $ProConser_rec['VERSIONE'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_VERSIONE];
        $ProConser_rec['CODICEERRORE'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_CODICEERRORE];
        $ProConser_rec['MESSAGGIOERRORE'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE];
        $ProConser_rec['CHIAVEVERSAMENTO'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO];
        $ProConser_rec['UUIDSIP'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_IDVERSAMENTO];
        $ProConser_rec['UTENTEVERSAMENTO'] = $arrTabdag[prolibConservazione::CHIAVE_ESITO_UTENTEVERSAMENTO];
        $ProConser_rec['FLSTORICO'] = 0;

        // Controllo se è già presente il record?
        try {
            ItaDB::DBInsert($this->PROT_DB, 'PROCONSER', 'ROWID', $ProConser_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento PROCONSER." . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getMenuFunzioniConservazione($nameForm, $rowidAnapro = '') {
        $arrBottoni = array();
        // Lettura parametri, conservazione almento 1 attiva.
//        if ($ParametriRegistro['TIPOCONSERVAZIONE'] != '') {

        $Anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
        $ProConser_rec = $this->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $ProConserBloccato_rec = $this->CheckProtocolloBloccato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        // Controllo se è gia satato versato
        if (!$ProConser_rec && !$ProConserBloccato_rec) {
            $arrBottoni['Riversa in Conservazione'] = array('id' => $nameForm . '_Conserva', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-arrow-green-dx-32x32'", 'model' => $nameForm);
        }
        // Controllo se da aggiornare
        if ($this->CheckProtocolloDaAggiornare($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
            $arrBottoni['Aggiorna Conservazione'] = array('id' => $nameForm . '_AggConserva', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-rotate-right-32x32'", 'model' => $nameForm);
        }
        // Provvisorio: controlla l'ultimo rdv non storicizzato.
        //$arrBottoni['Controlla Ultimo RDV'] = array('id' => $nameForm . '_ControllaRDV', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-publish-32x32'", 'model' => $nameForm);
//        }
        if ($ProConser_rec['ESITOVERSAMENTO'] == self::ESITO_NEGATIVO || $ProConser_rec['ESITOCONSERVAZIONE'] == self::CONSER_ESITO_NEGATIVO || $ProConserBloccato_rec) {
            $arrBottoni['Sblocca Conservazione'] = array('id' => $nameForm . '_SbloccaConservazione', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-unlock-32x32'", 'model' => $nameForm);
        }
        /*
         * Controllo se non è già bloccato
         * Se non ha un esito di conservazione o versamento positivo
         */
        if ((!$ProConserBloccato_rec) && ($ProConser_rec['ESITOVERSAMENTO'] != self::ESITO_POSTITIVO || $ProConser_rec['ESITOCONSERVAZIONE'] != self::CONSER_ESITO_POSTITIVO)) {
            $arrBottoni['Blocca Conservazione Protocollo'] = array('id' => $nameForm . '_BloccaConservazioneProt', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-divieto-32x32'", 'model' => $nameForm);
        }

        return $arrBottoni;
    }

    public function getSqlBaseConservazione() {
        $sql = "SELECT  PROCONSER.ROWID_ANAPRO,
                        PROCONSER.PROGVERSAMENTO,
                        PROCONSER.DATAVERSAMENTO,
                        PROCONSER.ORAVERSAMENTO,
                        PROCONSER.MOTIVOVERSAMENTO,
                        PROCONSER.ESITOVERSAMENTO,
                        PROCONSER.DOCVERSAMENTO,
                        PROCONSER.DOCESITO,
                        PROCONSER.COD_UNITA_DOCUMENTARIA,
                        PROCONSER.CONSERVATORE,
                        PROCONSER.VERSIONE,
                        PROCONSER.CODICEERRORE,
                        PROCONSER.MESSAGGIOERRORE,
                        PROCONSER.CHIAVEVERSAMENTO,
                        PROCONSER.UUIDSIP,
                        PROCONSER.UTENTEVERSAMENTO,
                        PROCONSER.FLSTORICO,
                        PROCONSER.ESITOCONSERVAZIONE,
                        PROCONSER.CODICEESITOCONSERVAZIONE,
                        PROCONSER.MESSAGGIOCONSERVAZIONE,
                        PROCONSER.NOTECONSER,
                        PROCONSER.DOCRDV,
                        ANAPRO.ROWID,
                        ANAPRO.PRONUM,
                        ANAPRO.PROPAR,
                        ANAPRO.PROSTATOPROT,
                        ANAPRO.PROFASKEY,
                        ANAPRO.PROCODTIPODOC,
                        ANAOGG.OGGOGG,
                        (SELECT COUNT(PROCONSERCONT.ROWID) FROM PROCONSER PROCONSERCONT WHERE PROCONSERCONT.PRONUM=PROCONSER.PRONUM AND PROCONSERCONT.PROPAR = PROCONSER.PROPAR) AS TENTATIVI,
                        (SELECT COUNT(PROCONSERCONTNEG.ROWID) FROM PROCONSER PROCONSERCONTNEG WHERE PROCONSERCONTNEG.PRONUM=PROCONSER.PRONUM AND PROCONSERCONTNEG.PROPAR = PROCONSER.PROPAR AND PROCONSERCONTNEG.ESITOVERSAMENTO =  '" . self::ESITO_NEGATIVO . "' ) AS TENTATIVI_NEGATIVI,
                        PROUPDATECONSER.DATAVARIAZIONE,
                        PROUPDATECONSER.ORAVARIAZIONE,
                        PROUPDATECONSER.UPDATETIPO
                        FROM ANAPRO ";
        $sql.=" LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR = ANAOGG.OGGPAR ";
        $sql.=" LEFT OUTER JOIN PROCONSER PROCONSER ON ANAPRO.PRONUM=PROCONSER.PRONUM AND ANAPRO.PROPAR = PROCONSER.PROPAR ";
        $sql.=" AND PROCONSER.MOTIVOVERSAMENTO = '" . self::MOTIVO_VERSAMENTO . "' AND PROCONSER.FLSTORICO = 0 ";
        $sql.=" LEFT OUTER JOIN PROUPDATECONSER PROUPDATECONSER ON ANAPRO.PRONUM=PROUPDATECONSER.PRONUM AND ANAPRO.PROPAR = PROUPDATECONSER.PROPAR AND FLESEGUITO = 0 ";
        return $sql;
    }

    public function getSqlProtocolliConservazione($EscludiRegistri = true, $EscludiSuapDoc = true, $EscludiPraAmm = true) {
        $anaent_41 = $this->proLib->GetAnaent('41');
        $anaent_59 = $this->proLib->GetAnaent('59');
        $sql = $this->getSqlBaseConservazione();
        //WHERE I
        $whereSuapDoc = '';
        if (!$EscludiSuapDoc && $anaent_59['ENTDE1'] != '') {
            $whereSuapDoc = " OR (ANAPRO.PROPAR = 'I' AND ANAPRO.PROCODTIPODOC = '" . $anaent_59['ENTDE1'] . "' ) ";
        }
        $wherePraAmm = '';
        if (!$EscludiPraAmm && $anaent_59['ENTDE1'] != '') {
            $wherePraAmm = " OR (ANAPRO.PROPAR = 'F' AND ANAPRO.PROCODTIPODOC = '" . $anaent_59['ENTDE1'] . "' ) ";
        }
        $sql.=" WHERE (ANAPRO.PROPAR = 'A' OR ANAPRO.PROPAR = 'P' OR ANAPRO.PROPAR = 'C' $whereSuapDoc $wherePraAmm ) ";
        if ($anaent_41['ENTVAL'] && $EscludiRegistri) {
            $sql.=" AND ANAPRO.PROCODTIPODOC <> '" . $anaent_41['ENTVAL'] . "' ";
        }

        return $sql;
    }

    /**
     * Restituisce quale unita' documentaria serve all'anapro
     * @param type $Anapro_rec
     * @return type
     */
    public function GetUnitaDocumentaria($Anapro_rec) {
        $UnitaDoc = false;
        $anaent_41 = $this->proLib->GetAnaent('41');
        $anaent_59 = $this->proLib->GetAnaent('59');
        $Proconser_rec = $this->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);

        if ($Anapro_rec['PROPAR'] == 'C') {
            if ($anaent_41['ENTVAL'] && $Anapro_rec['PROCODTIPODOC'] == $anaent_41['ENTVAL']) {
                $UnitaDoc = proConservazioneManagerHelper::K_UNIT_REGISTRO;
            } else {
                $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_INTERNO;
                //if($Proconser_rec){
                if ($this->CheckProtocolloDaAggiornare($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                    $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG;
                }
            }
        } else {
            if ($Anapro_rec['PROPAR'] == 'A' || $Anapro_rec['PROPAR'] == 'P') {
                $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT;
                // if ($Proconser_rec) {
                if ($this->CheckProtocolloDaAggiornare($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                    $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_AGG;
                }
            } else {
                if ($Anapro_rec['PROPAR'] == 'I' && $anaent_59['ENTDE1'] != '' && $anaent_59['ENTDE1'] == $Anapro_rec['PROCODTIPODOC']) {
                    $UnitaDoc = proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO;
                    // Previsto caso di passo aggiornato, ma verificare se servirà effettivamente.
                    if ($this->CheckProtocolloDaAggiornare($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                        $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_AGG;
                    }
                }
                // Da prevedere.
            }
        }

        /*
         * Controllo se deve essere eseguito prima un aggiornamento.
         */
        $ProUpdateConser = $this->GetProUpdateConser($Anapro_rec['PRONUM'], 'updatetipo', $Anapro_rec['PROPAR'], $UnitaDoc);
        if ($ProUpdateConser) {
            return $UnitaDoc;
        }
        /*
         *  Controllo se è protocollo annullato.
         */
        if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            switch ($Anapro_rec['PROPAR']) {
                case 'A':
                case 'P':
                    $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_ANN;
                    break;
                case 'C':
                    $UnitaDoc = proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN;
                    break;
            }
        }
        return $UnitaDoc;
    }

    public function ConservazioneProtocolliAuto($send = false, $forzaConservazione = false) {

        /*
         * Reset elenchi warning ed errori
         */
        $elenco_errori = array();
        $elenco_warning = array();
        $elenco_negativo = array();
        $elenco_positivo = array();
        $countProtMaiConservati = 0;
        $eqAudit = new eqAudit();

        $anaent_53 = $this->proLib->GetAnaent('53');
        $anaent_52 = $this->proLib->GetAnaent('52');

        /*
         * Sql di base dei Protocolli da Conservare
         * Estraggo A P C e F (Pratiche Amministrative)
         */
        $sql = $this->getSqlProtocolliConservazione();
        $whereBase = '';

        /*
         * Data Limite Impostata
         */
        if ($anaent_53['ENTDE2']) {
            $whereBase.=" AND ANAPRO.PRODAR >= " . $anaent_53['ENTDE2'] . " ";
        }
        /*
         * Giorni Differiti Impostati:
         */
        if ($anaent_53['ENTDE1']) {
            $Oggi = date('Ymd');
            $DataLimiteDiff = itaDate::subtractDays($Oggi, $anaent_53['ENTDE1']);
            $whereBase.=" AND ANAPRO.PRODAR <= " . $DataLimiteDiff . " ";
        }


        /*
         * Mai Conservati
         * Con Esito Negativo
         * Con Esito Sconosciuto (Si verificherà?)
         */
        $whereBase.=" AND 
                (  
                    ( PROCONSER.PRONUM IS NULL ) 
                  OR
                    ( PROCONSER.ESITOVERSAMENTO =  '" . self::ESITO_NEGATIVO . "' AND PROCONSER.PRONUM IS NOT NULL ) 
                 OR 
                  ( PROCONSER.ESITOVERSAMENTO =  '' AND PROCONSER.PRONUM IS NOT NULL ) 
                )";
        /*
         * Per ora Esclusione degli Annullati..[dovrà contemplarlo in conservaAnapro]
         */
        $whereBase.= " AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
        $sql.=$whereBase;
        $sql.=" ORDER BY ANAPRO.ROWID ASC ";

        /*
         * Elaborazione Query Principale.
         */
        $sql = " SELECT * FROM ($sql) A ";
        /*
         * Prevedere Numero di Tentativi.
         */
        $Tentativi = 2;
        if ($Tentativi) {
            $sql.=" WHERE TENTATIVI_NEGATIVI < " . $Tentativi . " ";
        }
        /*
         * Ordinamento per ROWID ANAPRO
         */

        /*
         * Controllo limite di protocollo elaborabili.
         */
        if ($anaent_53['ENTDE3']) {
            $sql.= " LIMIT " . $anaent_53['ENTDE3'];
        } else {
            // Necessario Limite di Default?
            //$sql.= " LIMIT 5 ";
        }

        try {
            $Anapro_da_conservare_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            $Audit = 'Errore nella Query di selezione. ' . $exc->getMessage();
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }

        /*
         * Estrazione delle 'F'
         * Per le F estraggo le relative "I" e le aggiungo.
         */
        $sqlPram = $this->getSqlProtocolliConservazione(true, true, false);
        $sqlraPram.=" AND ANAPRO.PROPAR = 'I' AND ANAPRO.PROFASKEY = '" . $Anapro_da_conservare_rec['PROFASKEY'] . "' ";
        $sqlDocPram.=$whereBase;

//        
//
//        //$whereBase 
//        foreach ($Anapro_da_conservare_tab as $key => $Anapro_da_conservare_rec) {
//            //Estrazione query delle "I" del fascicolo. Un setto il fascicolo xk non va conservato//oppure tengo conto in elabora:
//            // Flag per cambio fascicolo o tipo di protocollo
//            if ($Anapro_da_conservare_rec['PROPAR'] == 'F') {
//                $sqlDocPram = $this->getSqlProtocolliConservazione(true, false, true);
//                $sqlDocPram.=" AND ANAPRO.PROPAR = 'I' AND ANAPRO.PROFASKEY = '" . $Anapro_da_conservare_rec['PROFASKEY'] . "' ";
//                $sqlDocPram.=$whereBase;
//                try {
//                    $AnaproPram_da_conservare_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlDocPram, true);
//                    //
//                } catch (Exception $exc) {
//                    $Audit = 'Errore nella Query di selezione Anapro Pram. ' . $exc->getMessage();
//                    $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
//                }
//                if ($AnaproPram_da_conservare_tab) {
//                    unset($AnaproPram_da_conservare_tab[$key]);
//                    foreach ($AnaproPram_da_conservare_tab as $AnaproPram_da_conservare_rec) {
//                        //$Anapro_da_conservare_tab
//                    }
//                }
//            }
//        }




        $countProtMaiConservati = count($Anapro_da_conservare_tab);
        /*
         * Estrazione Prot Aggiornati Variati
         */
        $updTipo = array(self::K_UNIT_PROT_AGG, self::K_UNIT_PROT_INTERNO_AGG);
        $sqlAggiornati = $this->GetSqlElencoProtocolliVariati($updTipo);
        $AnaproAggiornati_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlAggiornati, true);
        foreach ($AnaproAggiornati_tab as $AnaproAggiornati_rec) {
            $Anapro_da_conservare_tab[] = $AnaproAggiornati_rec;
        }
        /*
         * Estrazione Prot Annullati.
         */
        $updTipo = array(self::K_UNIT_PROT_ANN, self::K_UNIT_PROT_INTERNO_ANN);
        $sqlAnnullati = $this->GetSqlElencoProtocolliVariati($updTipo);
        $AnaproAnnullati_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlAnnullati, true);
        foreach ($AnaproAnnullati_tab as $AnaproAnnullati_rec) {
            $Anapro_da_conservare_tab[] = $AnaproAnnullati_rec;
        }






        $Audit = 'Inizio chiamata conservazione Protocolli auto. Totale Anapro da conservare: ' . count($Anapro_da_conservare_tab);
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        /*
         * Istanzio il Manager
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            $Audit = 'IMPOSSIBILE ISTANZIARE IL MANAGER. ';
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            //
            $elenco_errori[] = " [ERRORE][IMPOSSIBILE ISTANZIARE IL MANAGER.] ";
            // ERRORE GRAVE!.
            return false;
        }

//        $vv = 0;
        $this->countProtConservati = 0;
        foreach ($Anapro_da_conservare_tab as $Anapro_da_conservare_rec) {
            /*
             * Controllo se parametrizzata una fascia oraria.
             */
            if ($anaent_52['ENTDE4'] && $anaent_52['ENTDE5']) {
                $OraAttuale = date('H:i:s');
                $InterrompiFuoriOrario = true;
                if ($OraAttuale >= $anaent_52['ENTDE4'] && $OraAttuale <= $anaent_52['ENTDE5']) {
                    $InterrompiFuoriOrario = false;
                }
                if ($InterrompiFuoriOrario) {
                    $Audit = 'Conservazione interrota: conservazione fuori fascia oraria ' . $anaent_52['ENTDE4'] . ' - ' . $anaent_52['ENTDE5'];
                    $this->msgInfoConservazione[] = $Audit;
                    $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                    break;
                }
            }
            /*
             * Inizio Elaborazione Protocollo
             */
            $Audit = 'Iniziata Conservazione Protocollo: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            /*
             * Rilettura ANAPRO_REC
             */
            $Anapro_rec = $this->proLib->GetAnapro($Anapro_da_conservare_rec['ROWID'], 'rowid');
            /*
             * Lettura unità documentaria
             */
            $UnitaDoc = $this->GetUnitaDocumentaria($Anapro_rec);

            /*
             * Controllo Protocollo Conservabile:
             */
            if (!$this->CheckProtocolloConservabile($Anapro_rec)) {
                $elenco_warning[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO WARNING][Protocollo non conservabile] ";
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Protocollo non conservabile.'));
                continue;
            }


            /*
             * Setto Chiavi Anapro
             */
            $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
            $ObjManager->setAnapro_rec($Anapro_rec);
            $ObjManager->setUnitaDocumentaria($UnitaDoc);
            // Set Versamento

            /*
             *  Lancio la conservazione
             */
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Lancio Conserva'));
            if (!$ObjManager->conservaAnapro()) {
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Errore in Conserva'));
                $AuditEsito = 'Chiamata conservazione Protocollo conclusa con errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' - Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
                $AuditEsito.= '. Esito: ' . $ObjManager->getErrCode() . ' ' . $ObjManager->getErrMessage();
                //
                $Errore = $ObjManager->getRetStatus();
                //
                $elenco_errori[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ERRORE][" . $ObjManager->getErrMessage() . $Errore . "] ";
            } else {
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Esito in Conserva'));
                switch ($ObjManager->getRetEsito()) {
                    case self::ESITO_POSTITIVO:
                        $elenco_positivo[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO POSITIVO][] ";
                        break;
                    case self::ESITO_WARNING:
                        $elenco_warning[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO WARNING][" . $ObjManager->getErrMessage() . "] ";
                        break;
                    case self::ESITO_NEGATIVO:
                        $Errore = $ObjManager->getRetStatus();
                        $elenco_errori[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO ERRORE][" . $ObjManager->getErrMessage() . $Errore . "] ";
                        break;

                    default:
                        $elenco_errori[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ERRORE][ESITO NON RICONOSCIUTO. " . $ObjManager->getErrMessage() . "] ";
                        break;
                }
            }
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Terminato Conserva'));

            $esitoMsg = $ObjManager->getRetEsito();
            $AuditEsito = 'Chiamata conservazione Protocollo conclusa senza errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' ';
            $AuditEsito.='- Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"] . '. Esito: ' . $esitoMsg;

//            /*
//             * Log Degli Eventi
//             */
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $AuditEsito));

//            $vv = $vv + 1;
//            /*
//             * PER LIMITARE MASSIMO 5 CONSERVAZIONI (FASE DI RECUPERO VECCHI REGISTRI DA CONSERVARE IN PRE-PRODUZIONE E/O PRODUZIONE
//             */
//            if ($vv === 5) {
//                break;
//            }

            $this->countProtConservati++;
        }

        $ObjManager = null;
        /* Terminato conservaauto */
        $MessaggioAudit = 'Terminata chiamata conservazione protocolli auto.';
        $MessaggioAudit.=' Errori: ' . count($elenco_errori) . '.';
        $MessaggioAudit.=' Negativo: ' . count($elenco_negativo) . '.';
        $MessaggioAudit.=' Warning: ' . count($elenco_warning) . '.';
        $MessaggioAudit.=' Positivi: ' . count($elenco_positivo) . '.';
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $MessaggioAudit));

        $Elenco = array(
            'STATUS' => true,
            'DA_CONSERVARE' => $Anapro_da_conservare_tab,
            'ERRORI' => $elenco_errori,
            'NEGATIVO' => $elenco_negativo,
            'WARNING' => $elenco_warning,
            'POSITIVO' => $elenco_positivo,
            'DA_CONS_MAICONSERVATI' => $countProtMaiConservati,
            'DA_CONS_VARIATI' => count($AnaproAggiornati_tab),
            'DA_CONS_ANNULLATI' => count($AnaproAnnullati_tab)
        );
        // Out::msgInfo('Elenco', print_r($Elenco, true));
        return $Elenco;
    }

    // Si potrebbe inserire in unica libreria (proLib) con messaggi più dinamici.
    public function SendMailErrore($Errore, $subject = null, $bodyHeader = null) {
        $Account = '';
        $devLib = new devLib();
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        $anaent_2 = $this->proLib->GetAnaent('2');
        if ($subject === null) {
            $subject = 'Errore in Conservazione Protocolli..';
        }
        if ($bodyHeader === null) {
            $bodyHeader.= ' si è verificato un errore durante la procedura automatica di Conservazione dei Protocolli.<br>';
            $bodyHeader.= 'Errore riscontrato:<br>';
        }
        $body = 'PROTOCOLLO ' . $anaent_2['ENTDE1'] . ' - ENTE ' . App::$utente->getKey('ditta') . '<br>';
        $body.= 'In data ' . date('d/m/Y') . ' alle ore ' . date('H:i:s') . "<br/>";
        $body.= $bodyHeader;
        $body.=$Errore;
        $anaent_26 = $this->proLib->GetAnaent('26');
        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($ItaEngine_mail_rec) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
        }
        if (!$Account) {
            if ($anaent_37) {
                $Account = $anaent_37['ENTDE2'];
            } else if ($anaent_26) {
                $Account = $anaent_26['ENTDE4'];
            }
            if (!$Account) {
                $this->setErrCode(-1);
                $this->setErrMessage('Nessun account di invio configurato.');
                return false;
            }
        }

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($Account);

        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile accedere alle funzioni dell\'account');
            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile creare un nuovo messaggio in uscita');
            return false;
        }
        $anaent_43 = $this->proLib->GetAnaent('43');
        $ElencoEmail = unserialize($anaent_43['ENTVAL']);
        if (!$ElencoEmail) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessuna Destinatario configurato per ivio della mail.');
            return false;
        }
        $mailDest = '';
        foreach ($ElencoEmail as $Mail) {
            $mailDest = $Mail['EMAIL'];
            $outgoingMessage->setSubject($subject);
            $outgoingMessage->setBody($body);
            $outgoingMessage->setEmail($mailDest);
            $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
            if ($mailSent) {
                continue;
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage($emlMailBox->getLastMessage());
                return false;
            }
        }
        return true;
    }

    public function CheckConservazioneBaseDatiVariata($rowid = '') {
        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo inesistente" . $rowid);
            return false;
        }
        /*
         * Controllo se protocollo conservato
         */
        if (!$this->GetProConser($Anapro_rec['PRONUM'], 'codice', $Anapro_rec['PROPAR'])) {
            // Nessuna conservazione effettuata.
            return true;
        }


        /*
         * Istanzio il Manager
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in istanza Manager.");
            return false;
        }
        /*
         * Setto Chiavi Anapro
         */
        $Proconser_rec = $this->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $UnitaDoc = $this->GetUnitaDocumentaria($Anapro_rec);
        $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
        $ObjManager->setUnitaDocumentaria($UnitaDoc);
        $ObjManager->setDataVersamento($Proconser_rec['DATAVERSAMENTO']);
        $ObjManager->setOraVersamento($Proconser_rec['ORAVERSAMENTO']);
        /*
         * Estrazione Base dati Conservata
         */
        $baseDatiConservata = $ObjManager->getBaseDati();
        if (!$baseDatiConservata) {
            //Out::msginfo('BaseDati Conservata', print_r($ObjManager->getErrMessage(), true));
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in istanza Base Dati Conservati. " . $ObjManager->getErrMessage());
            return false;
        } else {
            //Out::msginfo('BaseDati Conservata:', print_r($baseDatiConservata, true));
        }
        /*
         * Estrazione base dati attuale
         */
        $ObjManager->setDataVersamento(null);
        $ObjManager->setOraVersamento(null);
        $baseDatiAttuale = $ObjManager->getBaseDati();
        if (!$baseDatiAttuale) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in istanza Base Dati Attuali. " . $ObjManager->getErrMessage());
            return false;
        } else {
            //Out::msginfo('BaseDati Attuale', print_r($baseDatiAttuale, true));
        }
        /*
         * Setto Default 
         */
        $UnitaTipo = self::K_UNIT_PROT_AGG;
        /*
         * Chiave di controllo:
         * Caricare tramite archivio o altro(?)
         */

        $ChiaviDiControllo = array();
        $ChiaviDiControllo[] = proConservazioneManagerHelper::K_OGGETTO;
        $ChiaviDiControllo[] = proConservazioneManagerHelper::K_CLASSIFICAZIONE;
        $ChiaviDiControllo[] = proConservazioneManagerHelper::K_MITTENTEDESTINATARI;
        $ChiaviDiControllo[] = proConservazioneManagerHelper::K_DATAANNULLAMENTO;

        /*
         * Controllo se c'è una variazione:
         */
        $VariazionePresente = false;
        foreach ($ChiaviDiControllo as $key => $Chiave) {
            if ($baseDatiConservata[$Chiave] != $baseDatiAttuale[$Chiave]) {
                $VariazionePresente = true;
                break;
            }
        }
        /*
         * Protocollo annullato?
         */
        if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            $UnitaTipo = self::K_UNIT_PROT_ANN;
        }

        /*
         * Controllo dati fascicoli variati:
         */

//        $baseDatiConservata[self::K_FASCICOLOPRINCIPALE];
//        $baseDatiConservata[self::K_FASCICOLI];
        /*
         * Carico Riga di Variazione:
         *  verificando che non sia già presente.
         */
        if ($VariazionePresente) {
            // Qui è corretto che cerchi solo gli "UPDATE" perchè è stato effettuato un aggiornamento, ma poi un successivo annullamento. 
            $ProUpdateConse_rec = $this->GetProUpdateConser($Anapro_rec['PRONUM'], 'updatetipo', $Anapro_rec['PROPAR'], $UnitaTipo);
            if ($ProUpdateConse_rec) {
                // Serve aggiornare data di variazione?
                try {
                    $ProUpdateConser_rec['DATAVARIAZIONE'] = $Anapro_rec['PRORDA'];
                    $ProUpdateConser_rec['ORAVARIAZIONE'] = $Anapro_rec['PROROR'];
                    $ProUpdateConser_rec['ROWID_ANAPRO'] = $Anapro_rec['ROWID'];
                    ItaDB::DBUpdate($this->getPROTDB(), 'PROUPDATECONSER', 'ROWID', $ProUpdateConser_rec);
                    return true;
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in aggiornamento PROUPDATECONSER.<br> " . $e->getMessage());
                    return false;
                }
            } else {
                if (!$this->InserisciProUpdateConser($Anapro_rec, $UnitaTipo)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function GetProUpdateConser($Codice, $TipoRic = 'codice', $TipoProt = '', $updTipo = '', $multi = false) {
        switch ($TipoRic) {
            case 'codice':
                $sql = "SELECT * FROM PROUPDATECONSER WHERE PRONUM = " . $Codice . " AND PROPAR =  '" . $TipoProt . "' ";
                $sql.= "AND FLESEGUITO = 0 ";
                break;

            case 'updatetipo':
                $sql = "SELECT * FROM PROUPDATECONSER WHERE PRONUM = " . $Codice . " AND PROPAR =  '" . $TipoProt . "' ";
                $sql.= " AND UPDATETIPO = '$updTipo' AND FLESEGUITO = 0 ";
                break;

            default:
                $sql = "SELECT * FROM PROUPDATECONSER WHERE ROWID = " . $Codice;
                break;
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    /**
     * Estrazione di tutti i protocolli variati:
     * @param type $updTipo
     * @return type
     */
    public function GetSqlElencoProtocolliVariati($updTipo = array(), $where = '', $soloConservabili = true) {
        $sql = "SELECT ANAPRO.* "
                . " FROM PROUPDATECONSER "
                . " LEFT OUTER JOIN ANAPRO ANAPRO ON PROUPDATECONSER.PRONUM=ANAPRO.PRONUM AND PROUPDATECONSER.PROPAR = ANAPRO.PROPAR "
                . " LEFT OUTER JOIN PROCONSER PROCONSER ON PROUPDATECONSER.PRONUM=PROCONSER.PRONUM AND PROUPDATECONSER.PROPAR = PROCONSER.PROPAR AND PROCONSER.FLSTORICO = 0 "
                . "WHERE FLESEGUITO = 0 ";
        if ($soloConservabili) {
            $sql.=" AND PROCONSER.ESITOCONSERVAZIONE = '" . self::CONSER_ESITO_POSTITIVO . "' ";
        }
        $sql.=$where;
        if ($updTipo) {
            if (is_array($updTipo)) {
                $sql.=" AND ( ";
                foreach ($updTipo as $tipo) {
                    $sql.=" UPDATETIPO = '$tipo' OR ";
                }
                $sql = substr($sql, 0, -3) . " ) ";
            } else {
                $sql.=" AND UPDATETIPO = '$updTipo' ";
            }
        }
        return $sql;
    }

    public function InserisciProUpdateConser($Anapro_rec, $UpdateTipo = '') {
        // PREVEDERE STORICIZZAZIONE FL0
        // StoricizzaProconser(); Già prevista.
        $ProUpdateConser_rec = array();
        if (!$UpdateTipo) {
            $UpdateTipo = self::K_UNIT_PROT_AGG;
        }
        $ProUpdateConser_rec['PRONUM'] = $Anapro_rec['PRONUM'];
        $ProUpdateConser_rec['PROPAR'] = $Anapro_rec['PROPAR'];
        $ProUpdateConser_rec['ROWID_ANAPRO'] = $Anapro_rec['ROWID'];
        $ProUpdateConser_rec['DATAVARIAZIONE'] = $Anapro_rec['PRORDA'];
        $ProUpdateConser_rec['ORAVARIAZIONE'] = $Anapro_rec['PROROR'];
        $ProUpdateConser_rec['UPDATETIPO'] = $UpdateTipo;
        $ProUpdateConser_rec['FLESEGUITO'] = 0;
        // Controllo se è già presente il record?
        try {
            ItaDB::DBInsert($this->PROT_DB, 'PROUPDATECONSER', 'ROWID', $ProUpdateConser_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento PROUPDATE." . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getTentativiConservazione($Pronum, $Propar, $soloNegativi = false) {
        $sql = "SELECT COUNT(ROWID) FROM PROCONSER  WHERE PRONUM = $Pronum AND PROPAR = '$Propar' ";
        if ($soloNegativi) {
            $sql.=" AND ESITOVERSAMENTO =  '" . self::ESITO_NEGATIVO . "' ";
        }
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        /*
         * Qui controllo dei tenativi negativi?
          $Tentativi = 3;
          if ($this->getTentativiConservazione($Anapro_da_conservare_rec['PRONUM'], $Anapro_da_conservare_rec['PROPAR'], true) > $Tentativi) {
          $Audit = 'Protocollo ' . $Anapro_da_conservare_rec['PRONUM'] . '/' . $Anapro_da_conservare_rec['PROPAR'] . " già provato a conservare $Tentativi volte. La conservazione dovrà avvenire manualmente.";
          $this->msgInfoConservazione[] = $Audit;
          $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
          continue;
          }
         * 
         */
    }

    public function ControllaRDVProconserAuto() {
        $elenco_errori = array();
        $elenco_negativo = array();
        $elenco_positivo = array();
        $countProconserEstratti = 0;
        $eqAudit = new eqAudit();

        $anaent_53 = $this->proLib->GetAnaent('53');
        $sql = "SELECT * "
                . " FROM PROCONSER"
                . " LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=PROCONSER.PRONUM AND ANAPRO.PROPAR = PROCONSER.PROPAR"
                . " WHERE FLSTORICO = 0 AND "
                . " ESITOVERSAMENTO = '" . self::ESITO_POSTITIVO . "' "
                . " AND DOCRDV = '' ";
        /*
         *  DATA LIMITE:
         */
        if ($anaent_53['ENTDE2']) {
            $sql.=" AND ANAPRO.PRODAR >= " . $anaent_53['ENTDE2'] . " ";
        }

        try {
            $Proconser_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            $Audit = 'Errore nella query di selezione proconser. ' . $exc->getMessage();
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }
        /*
         * Apertura Oggetto Conservatore
         */
        $ObjManager = proConservazioneManagerFactory::getManager();
        if (!$ObjManager) {
            $Audit = 'IMPOSSIBILE ISTANZIARE IL MANAGER. ';
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            $elenco_errori[] = " [ERRORE][IMPOSSIBILE ISTANZIARE IL MANAGER.] ";
            return false;
        }

        $countProconserEstratti = count($Proconser_tab);

        foreach ($Proconser_tab as $Proconser_rec) {
            /*
             * Inizio Elaborazione Protocollo
             */
            $Audit = 'Iniziata Elaborazione RDV Protocoollo: ' . $Proconser_rec['PRONUM'] . "/" . $Proconser_rec["PROPAR"];
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

            $UnitaDoc = $this->GetUnitaDocumentaria($Proconser_rec);
            /*
             * Setto Chiavi Anapro
             */
            $ObjManager->setRowidAnapro($Proconser_rec['ROWID']);
            $ObjManager->setAnapro_rec($Proconser_rec);
            $ObjManager->setUnitaDocumentaria($UnitaDoc);
            //
            if (!$ObjManager->parseXmlRDV($Proconser_rec['UUIDSIP'], $Proconser_rec['PENDINGUUID'])) {
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Errore in Conserva'));
                $AuditEsito = 'Chiamata RDV Protocollo conclusa con errori Prot. rowid: ' . $Proconser_rec['ROWID_ANAPRO'] . ' - Numero: ' . $Proconser_rec['PRONUM'] . "/" . $Proconser_rec["PROPAR"];
                $AuditEsito.= '. Esito: ' . $ObjManager->getErrCode() . ' ' . $ObjManager->getErrMessage();
                $elenco_errori[] = $Proconser_rec["PRONUM"] . "/" . $Proconser_rec["PROPAR"] . " [ERRORE][" . $ObjManager->getErrMessage() . "] ";
            } else {

                switch ($ObjManager->getRetEsito()) {
                    case self::CONSER_ESITO_POSTITIVO:
                        $elenco_positivo[] = $Proconser_rec["PRONUM"] . "/" . $Proconser_rec["PROPAR"] . " - $UnitaDoc [ESITO POSITIVO][] ";
                        break;

                    default:
                    case self::CONSER_ESITO_NEGATIVO:
                        $Errore = $ObjManager->getRetStatus();
                        $elenco_negativo[] = $Proconser_rec["PRONUM"] . "/" . $Proconser_rec["PROPAR"] . " - $UnitaDoc [ESITO ERRORE][" . $ObjManager->getErrMessage() . $Errore . "] ";
                        break;
                }
            }
            $Audit = 'Terminata elaborazione RDV.';
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }

        $ObjManager = null;

        /* Terminato conservaauto */
        $MessaggioAudit = 'Terminata chiamata controllo RDV protocolli auto.';
        $MessaggioAudit.=' Errori: ' . count($elenco_errori) . '.';
        $MessaggioAudit.=' Negativo: ' . count($elenco_negativo) . '.';
        $MessaggioAudit.=' Positivi: ' . count($elenco_positivo) . '.';
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $MessaggioAudit));

        $Elenco = array(
            'STATUS' => true,
            'DA_VERIFICARE' => $countProconserEstratti,
            'ERRORI' => $elenco_errori,
            'NEGATIVO' => $elenco_negativo,
            'POSITIVO' => $elenco_positivo
        );

        return $Elenco;
    }

    public function ConservazioneFascicoliAuto($send = false, $forzaConservazione = false) {
        /*
         * Reset elenchi warning ed errori
         */
        $elenco_errori = array();
        $elenco_warning = array();
        $elenco_negativo = array();
        $elenco_positivo = array();
        $countProtMaiConservati = 0;
        $eqAudit = new eqAudit();

        $anaent_53 = $this->proLib->GetAnaent('53');
        $anaent_52 = $this->proLib->GetAnaent('52');
        /*
         * Sql di base dei Protocolli da Conservare
         * Estraggo SOLO le F
         */
        $sql = $this->getSqlProtocolliConservazione(true, true, false);
        $sql.= " AND ANAPRO.PROPAR = 'F' "; //Estrazione solo delle F.
        /*
         * Data Limite Impostata
         */
        if ($anaent_53['ENTDE2']) {
            $whereBase.=" AND ANAPRO.PRODAR >= " . $anaent_53['ENTDE2'] . " ";
        }
        /*
         * Giorni Differiti Impostati:
         */
        if ($anaent_53['ENTDE1']) {
            $Oggi = date('Ymd');
            $DataLimiteDiff = itaDate::subtractDays($Oggi, $anaent_53['ENTDE1']);
            $whereBase.=" AND ANAPRO.PRODAR <= " . $DataLimiteDiff . " ";
        }
        /*
         * Mai Conservati
         * Con Esito Negativo
         * Con Esito Sconosciuto (Si verificherà?)
         */
        $whereBase.=" AND 
                (  
                    ( PROCONSER.PRONUM IS NULL OR PROCONSER.ESITOVERSAMENTO =  '" . self::ESITO_SOSPESO . "' ) 
                  OR
                    ( PROCONSER.ESITOVERSAMENTO =  '" . self::ESITO_NEGATIVO . "' AND PROCONSER.PRONUM IS NOT NULL ) 
                 OR 
                  ( PROCONSER.ESITOVERSAMENTO =  '' AND PROCONSER.PRONUM IS NOT NULL ) 
                )";
        /*
         * Per ora Esclusione degli Annullati..[dovrà contemplarlo in conservaAnapro]
         */
        $whereBase.= " AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
        $sql.=$whereBase;
        $sql.=" ORDER BY ANAPRO.ROWID ASC ";

        /*
         * Elaborazione Query Principale.
         */
        $sql = " SELECT * FROM ($sql) A ";
        /*
         * Prevedere Numero di Tentativi.
         */
        $Tentativi = 2;
        if ($Tentativi) {
            $sql.=" WHERE TENTATIVI_NEGATIVI < " . $Tentativi . " ";
        }
        /*
         * Ordinamento per ROWID ANAPRO
         */
        /*
         * Controllo limite di protocollo elaborabili.
         */
        if ($anaent_53['ENTDE3']) {
            $sql.= " LIMIT " . $anaent_53['ENTDE3'];
        }
        // Per test: prendere soli 2 fascicoli precisi.
        // $sql.=" AND ( PROFASKEY = '00010002.2019.000035' OR  PROFASKEY  = '00010002.2019.000036' ) ";
        try {
            $Anadoc_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            $Audit = 'Errore nella Query di selezione. ' . $exc->getMessage();
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }

        $Anapro_da_conservare_tab = array();
        foreach ($Anadoc_tab as $key => $Anapro_da_conservare_rec) {
            $sqlDocPram = $this->getSqlProtocolliConservazione(true, false, true);
            $sqlDocPram.=" AND ANAPRO.PROPAR = 'I' AND ANAPRO.PROFASKEY = '" . $Anapro_da_conservare_rec['PROFASKEY'] . "' ";
            $sqlDocPram.=$whereBase;

            try {
                $AnaproPram_da_conservare_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlDocPram, true);
                //
            } catch (Exception $exc) {
                $Audit = 'Errore nella Query di selezione Anapro Pram. ' . $exc->getMessage();
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            }
            if ($AnaproPram_da_conservare_tab) {
                foreach ($AnaproPram_da_conservare_tab as $AnaproPram_da_conservare_rec) {
                    $Anapro_da_conservare_tab[] = $AnaproPram_da_conservare_rec;
                }
            }
        }

        /*
         * Fascicoli Variati o da Annullare, verificare che cosa bisogna fare.
         */
        $Audit = 'Inizio chiamata conservazione Fascicoli auto. Totale Anapro da conservare: ' . count($Anapro_da_conservare_tab);
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
//        $vv = 0;
        $this->countProtConservati = 0;
        $FascicoloInElaborazione = '';
        foreach ($Anapro_da_conservare_tab as $Anapro_da_conservare_rec) {
            /*
             * Controllo Fascia oraria viene eseguito se:
             *  - è il primo giro 
             *  - al cambio di un fascicolo
             */
            if ($FascicoloInElaborazione == '' || ($FascicoloInElaborazione && $FascicoloInElaborazione != $Anapro_da_conservare_rec['PROFASKEY'])) {
                if ($anaent_52['ENTDE4'] && $anaent_52['ENTDE5']) {
                    $OraAttuale = date('H:i:s');
                    $InterrompiFuoriOrario = true;
                    if ($OraAttuale >= $anaent_52['ENTDE4'] && $OraAttuale <= $anaent_52['ENTDE5']) {
                        $InterrompiFuoriOrario = false;
                    }
                    if ($InterrompiFuoriOrario) {
                        $Audit = 'Conservazione interrota: conservazione fuori fascia oraria ' . $anaent_52['ENTDE4'] . ' - ' . $anaent_52['ENTDE5'];
                        $this->msgInfoConservazione[] = $Audit;
                        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                        break;
                    }
                }
            }
            if ($FascicoloInElaborazione == '') {
                $FascicoloInElaborazione = $Anapro_da_conservare_rec['PROFASKEY'];
            }
            if ($FascicoloInElaborazione != $Anapro_da_conservare_rec['PROFASKEY']) {
                $this->SalvaEsitiConservazioneFascicolo($FascicoloInElaborazione);
                $FascicoloInElaborazione = $Anapro_da_conservare_rec['PROFASKEY'];
            }
            /*
             * Inizio Elaborazione Protocollo
             */
            $Audit = 'Iniziata Conservazione Fascicoli: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            /*
             * Rilettura ANAPRO_REC
             */
            $Anapro_rec = $this->proLib->GetAnapro($Anapro_da_conservare_rec['ROWID'], 'rowid');
            /*
             * Lettura unità documentaria
             */
            $UnitaDoc = $this->GetUnitaDocumentaria($Anapro_rec);
            /*
             * Controllo Protocollo Conservabile:
             */
            if (!$this->CheckProtocolloConservabile($Anapro_rec)) {
                $elenco_warning[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO WARNING][Protocollo non conservabile] ";
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Protocollo non conservabile.'));
                continue;
            }
            /*
             * Istanzio il Manager
             */
            $ObjManager = proConservazioneManagerFactory::getManager();
            if (!$ObjManager) {
                $Audit = 'IMPOSSIBILE ISTANZIARE IL MANAGER. ';
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                //
                $elenco_errori[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ERRORE][IMPOSSIBILE ISTANZIARE IL MANAGER.] ";
                // ERRORE GRAVE!.
                return false;
            }
            /*
             * Setto Chiavi Anapro
             */
            $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
            $ObjManager->setAnapro_rec($Anapro_rec);
            $ObjManager->setUnitaDocumentaria($UnitaDoc);
            // Set Versamento

            /*
             *  Lancio la conservazione
             */
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Lancio Conserva'));
            if (!$ObjManager->conservaAnapro()) {
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Errore in Conserva'));
                $AuditEsito = 'Chiamata conservazione Protocollo conclusa con errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' - Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
                $AuditEsito.= '. Esito: ' . $ObjManager->getErrCode() . ' ' . $ObjManager->getErrMessage();
                //
                $Errore = $ObjManager->getRetStatus();
                //
                $elenco_errori[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ERRORE][" . $ObjManager->getErrMessage() . $Errore . "] ";
            } else {
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Esito in Conserva'));
                switch ($ObjManager->getRetEsito()) {
                    case self::ESITO_POSTITIVO:
                        $elenco_positivo[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO POSITIVO][] ";
                        break;
                    case self::ESITO_WARNING:
                        $elenco_warning[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO WARNING][" . $ObjManager->getErrMessage() . "] ";
                        break;
                    case self::ESITO_NEGATIVO:
                        $Errore = $ObjManager->getRetStatus();
                        $elenco_errori[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ESITO ERRORE][" . $ObjManager->getErrMessage() . $Errore . "] ";
                        break;

                    default:
                        $elenco_errori[] = $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " - $UnitaDoc [ERRORE][ESITO NON RICONOSCIUTO. " . $ObjManager->getErrMessage() . "] ";
                        break;
                }
            }
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => 'Terminato Conserva'));

            $esitoMsg = $ObjManager->getRetEsito();
            $AuditEsito = 'Chiamata conservazione Fascicoli conclusa senza errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' ';
            $AuditEsito.='- Numero: ' . $Anapro_da_conservare_rec['PROFASKEY'] . ' - ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"] . '. Esito: ' . $esitoMsg;
//            /*
//             * Log Degli Eventi
//             */
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $AuditEsito));

            $ObjManager = null;
            $this->countProtConservati++;
        }
        /*
         * Salvo esito anche per ultimo:
         */
        $this->SalvaEsitiConservazioneFascicolo($Anapro_da_conservare_rec['PROFASKEY']);

        /* Terminato conservaauto */
        $MessaggioAudit = 'Terminata chiamata conservazione Fascicoli auto.';
        $MessaggioAudit.=' Errori: ' . count($elenco_errori) . '.';
        $MessaggioAudit.=' Negativo: ' . count($elenco_negativo) . '.';
        $MessaggioAudit.=' Warning: ' . count($elenco_warning) . '.';
        $MessaggioAudit.=' Positivi: ' . count($elenco_positivo) . '.';
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $MessaggioAudit));

        $Elenco = array(
            'STATUS' => true,
            'DA_CONSERVARE' => $Anapro_da_conservare_tab,
            'ERRORI' => $elenco_errori,
            'NEGATIVO' => $elenco_negativo,
            'WARNING' => $elenco_warning,
            'POSITIVO' => $elenco_positivo,
            'DA_CONS_MAICONSERVATI' => $countProtMaiConservati,
            'DA_CONS_VARIATI' => count($AnaproAggiornati_tab),
            'DA_CONS_ANNULLATI' => count($AnaproAnnullati_tab)
        );
        // Out::msgInfo('Elenco', print_r($Elenco, true));
        return $Elenco;
    }

    public function SalvaEsitiConservazioneFascicolo($proFasKey) {
        $eqAudit = new eqAudit();
        /*
         * Estrazione di tutti gli elelmenti collegati direttamente la fascicolo
         */
        $Anapro_F_rec = $this->proLib->GetAnapro($proFasKey, 'fascicolo');
        if (!$Anapro_F_rec) {
            $Audit = 'Anapro Fascicolo: ' . $proFasKey . ' non trovato.';
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            return false;
        }
        // Se non è un fascicolo/pratica amministrativa non serve salvarlo
        $anaent_59 = $this->proLib->GetAnaent('59');
        if ($Anapro_F_rec['PROCODTIPODOC'] != $anaent_59['ENTDE1']) {
            return true;
        }
        // Se già presente non serve salvarlo
        $ProConser_rec = $this->GetProConser($Anapro_F_rec['PRONUM'], 'codice', $Anapro_F_rec['PROPAR']);
        if ($ProConser_rec) {
            return true;
        }
        /*
         * Verificare storicizza proconser.
         */
        //SALVO I METADATI
        $ProConser_rec = array();
        $ProConser_rec['PRONUM'] = $Anapro_F_rec['PRONUM'];
        $ProConser_rec['PROPAR'] = $Anapro_F_rec['PROPAR'];
        $ProConser_rec['ROWID_ANAPRO'] = $Anapro_F_rec['ROWID'];
        $ProConser_rec['PROGVERSAMENTO'] = $this->GetProgressivoConservazione($Anapro_F_rec);
        $ProConser_rec['DATAVERSAMENTO'] = date("Ymd");
        $ProConser_rec['ORAVERSAMENTO'] = date("H:i:s");
        $ProConser_rec['MOTIVOVERSAMENTO'] = self::MOTIVO_VERSAMENTO;
        $ProConser_rec['ESITOVERSAMENTO'] = self::ESITO_SOSPESO;
        $ProConser_rec['DOCVERSAMENTO'] = '';
        $ProConser_rec['DOCESITO'] = '';
        $ProConser_rec['COD_UNITA_DOCUMENTARIA'] = ''; //LETTURA PARAMETRO.
        $ProConser_rec['CONSERVATORE'] = '';
        $ProConser_rec['VERSIONE'] = '';
        $ProConser_rec['CODICEERRORE'] = '';
        $ProConser_rec['MESSAGGIOERRORE'] = '';
        $ProConser_rec['CHIAVEVERSAMENTO'] = '';
        $ProConser_rec['UUIDSIP'] = '';
        $ProConser_rec['UTENTEVERSAMENTO'] = '';
        $ProConser_rec['FLSTORICO'] = 0;
        $ProConser_rec['NOTECONSER'] = '';
        // Controllo se è già presente il record?
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROCONSER', 'ROWID', $ProConser_rec);
        } catch (Exception $e) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in inserimento PROCONSER." . $e->getMessage();
            return false;
        }
    }

}

?>
