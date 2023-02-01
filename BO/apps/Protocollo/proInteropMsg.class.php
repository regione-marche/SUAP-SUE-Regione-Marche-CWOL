<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    30.10.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

class proInteropMsg {

    const VERSIONE_SEGNATURA_ATTUALE = "aaaa-mm-gg";
    const SEGNATURA_DTD = "Segnatura2013.dtd";
    const TIPOMSG_SEGNATURA = 'Segnatura';
    const TIPOMSG_CONFERMA = 'Conferma';
    const TIPOMSG_ANNULLAMENTO = 'Annullamento';
    const TIPOMSG_AGGIORNAMENTO = 'Aggiornamento';
    const TIPOMSG_ECCEZIONE = 'Eccezione';
    //Esiti validazione
    const VALID_XML = "0";
    const NOVALID_XML = "-2";
    // Versioni Fatture:
    const SEGNATURAV_20010507 = "2001-05-07";
    const SEGNATURAV_20090331 = "2009-03-31";
    const SEGNATURAV_2009203 = "2009-12-03";
    const SEGNATURAV_2013 = "aaaa-mm-gg";

    public $proLib;
    public $ErrCode;
    public $ErrMessage;
    private $tempPath;
    private $tipoMessaggio;
    private $versoTrasmissione;
    private $pathFileMessaggio;
    private $anapro_record;
    private $segnaturaParent;
    /* Aggiuntive */
    private $esitoValidation;
    private $descrValidation;
    private $identificatore = array();
    private $datiSegnatura = array();
    private $VersioneSegnatura;
    private $infoValidazione = '';


    /*
     * Tipologie Elementi Segnatura
     */
    public static $ElementiSegnatura = array(
        self::TIPOMSG_SEGNATURA => 'Segnatura',
        self::TIPOMSG_CONFERMA => 'ConfermaRicezione',
        self::TIPOMSG_ECCEZIONE => 'NotificaEccezione',
        self::TIPOMSG_AGGIORNAMENTO => 'AggiornamentoConferma',
        self::TIPOMSG_ANNULLAMENTO => 'AnnullamentoProtocollazione'
    );

    /*
     * Elenco File per Check Versione Segnatura.
     */
    public static $FileCheckVersioniSegnatura = array(
        self::SEGNATURAV_20010507 => 'Segnatura-2001-05-07.dtd',
        self::SEGNATURAV_20090331 => 'Segnatura-2009-03-31.dtd',
        self::SEGNATURAV_2009203 => 'Segnatura-2009-12-03.dtd',
        self::SEGNATURAV_2013 => 'Segnatura2013.xsd'
    );

    public function GetPathResourceSegnature() {
        return ITA_BASE_PATH . '/apps/Protocollo/resources/segnature/';
    }

    public function getErrCode() {
        return $this->ErrCode;
    }

    private function setErrCode($ErrCode) {
        $this->ErrCode = $ErrCode;
    }

    public function getErrMessage() {
        return $this->ErrMessage;
    }

    private function setErrMessage($ErrMessage) {
        $this->ErrMessage = $ErrMessage;
    }

    public function getAnapro_record() {
        return $this->anapro_record;
    }

    public function setAnapro_record($anapro_record) {
        $this->anapro_record = $anapro_record;
    }

    public function getTipoMessaggio() {
        return $this->tipoMessaggio;
    }

    public function getVersoTrasmissione() {
        return $this->versoTrasmissione;
    }

    public function getPathFileMessaggio() {
        return $this->pathFileMessaggio;
    }

    public function setTipoMessaggio($tipoMessaggio) {
        $this->tipoMessaggio = $tipoMessaggio;
    }

    public function setVersoTrasmissione($versoTrasmissione) {
        $this->versoTrasmissione = $versoTrasmissione;
    }

    public function getSegnaturaParent() {
        return $this->segnaturaParent;
    }

    public function setSegnaturaParent($segnaturaParent) {
        $this->segnaturaParent = $segnaturaParent;
    }

    public function getIdentificatore() {
        return $this->identificatore;
    }

    public function getDatiSegnatura() {
        return $this->datiSegnatura;
    }

    public function getEsitoValidation() {
        return $this->esitoValidation;
    }

    public function getDescrValidation() {
        return $this->descrValidation;
    }

    public function getVersioneSegnatura() {
        return $this->VersioneSegnatura;
    }

    public function setVersioneSegnatura($VersioneSegnatura) {
        $this->VersioneSegnatura = $VersioneSegnatura;
    }

    function getInfoValidazione() {
        return $this->infoValidazione;
    }

    function setInfoValidazione($infoValidazione) {
        $this->infoValidazione = $infoValidazione;
    }

    /**
     * 
     * @param type $file
     * @return boolean
     */
    public static function getInteropInstanceEntrata($file, $realNameFile = '') {
        $obj = new proInteropMsg();
        if (!$file) {
            $obj->setErrCode(-1);
            $obj->setErrMessage('Parametri necessari mancanti.');
            return $obj;
        }
        $obj->proLib = new proLib();
        try {
            $obj->GetTempPath();
            $obj->AnalizzaFile($file, $realNameFile);
        } catch (Exception $e) {
            return false;
        }
        return $obj;
    }

    /**
     * 
     * @param type $chiaveProtocollo:
     *       Array['PRONUM']
     *            ['PROPAR']
     * @param type $tipoMessaggio
     * @return boolean
     */
    public static function getInteropInstanceUscita($chiaveProtocollo, $tipoMessaggio) {
        $obj = new proInteropMsg();
        if (!$chiaveProtocollo || !$tipoMessaggio) {
            $obj->setErrCode(-1);
            $obj->setErrMessage('Parametri necessari mancanti.');
            return $obj;
        }
        $obj->proLib = new proLib();

        $Anapro_rec = $obj->proLib->GetAnapro($chiaveProtocollo['PRONUM'], 'codice', $chiaveProtocollo['PROPAR']);
        if (!$Anapro_rec) {
            $obj->setErrCode(-1);
            $obj->setErrMessage('Errore in lettura anapro');
            return $obj;
        }
        $obj->setAnapro_record($Anapro_rec);
        $obj->setTipoMessaggio($tipoMessaggio);
        try {
            $obj->GetTempPath();
            $obj->GetFileInteroperabileXML();
        } catch (Exception $e) {
            return false;
        }

        return $obj;
    }

    public function GetTempPath() {
        $randPath = itaLib::getRandBaseName();
        $this->tempPath = itaLib::getAppsTempPath("proInteropMsg-$randPath");
        if (!@is_dir($this->tempPath)) {
            if (!itaLib::createAppsTempPath("proInteropMsg-$randPath")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }
        return $this->tempPath;
    }

    public function CleanTempPath() {
        // Segnatura parent potrebbe essere controllato qui e pulito da qui.
        return itaLib::deleteDirRecursive($this->tempPath);
    }

    private function AnalizzaFile($file, $realNameFile = '') {
        /*
         * Ricavo il nome File:
         */
        $nomeFile = pathinfo($file, PATHINFO_BASENAME);
        if ($realNameFile) {
            $nomeFile = $realNameFile;
        }

        /*
         * Copio il file
         */
        $fileDest = $this->tempPath . '/' . $nomeFile;
        if (!@copy($file, $fileDest)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
            return false;
        }
        /*
         * Setto il pathFileMessaggio
         */
        $this->pathFileMessaggio = $fileDest;

        // @TODO strtolower serve?
        switch (strtolower($nomeFile)) {
            case strtolower(self::TIPOMSG_SEGNATURA) . '.xml':
                $this->tipoMessaggio = self::TIPOMSG_SEGNATURA;
                if (!$this->parseSegnaturaXML()) {
                    return false;
                }
                break;
            case strtolower(self::TIPOMSG_CONFERMA) . '.xml':
                $this->tipoMessaggio = self::TIPOMSG_CONFERMA;
                if (!$this->parseConfermaRicezioneXML()) {
                    return false;
                }
                break;

            case strtolower(self::TIPOMSG_AGGIORNAMENTO) . '.xml':
            case strtolower(self::TIPOMSG_ANNULLAMENTO) . '.xml':
            case strtolower(self::TIPOMSG_ECCEZIONE) . '.xml':
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Tipologia file interoperabile non riconosciuta.");
                return false;
                break;
        }
        return true;
    }

    private function parseSegnaturaXML() {
        /*
         * Valido la segnatura:
         */
        //  if (!$this->validateXML()) {
        /*
         * Non occore bloccare: esitoValidation è sempre disponibile per il controllo.
         * return false;
         */
        //}

        /*
         * Lettura Array XML
         */
        $ArrXml = $this->getXMLArray();
        if (!$ArrXml) {
            return false;
        }
        $ArrSegnatura = $ArrXml['Segnatura'];
        /*
         *  Setto identificatore.
         */
        $ArrIdentificatore = $ArrSegnatura['Intestazione']['Identificatore'];
        if (isset($ArrIdentificatore['CodiceAmministrazione']['@textNode'])) {
            $this->identificatore['CodiceAmministrazione'] = $ArrIdentificatore['CodiceAmministrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['CodiceAOO']['@textNode'])) {
            $this->identificatore['CodiceAOO'] = $ArrIdentificatore['CodiceAOO']['@textNode'];
        }
        if (isset($ArrIdentificatore['NumeroRegistrazione']['@textNode'])) {
            $this->identificatore['NumeroRegistrazione'] = $ArrIdentificatore['NumeroRegistrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['DataRegistrazione']['@textNode'])) {
            $this->identificatore['DataRegistrazione'] = $ArrIdentificatore['DataRegistrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['CodiceRegistro']['@textNode'])) {
            $this->identificatore['CodiceRegistro'] = $ArrIdentificatore['CodiceRegistro']['@textNode'];
        }


        /*
         * Dati Segnatura in formato array associativo
         */
        $numProtocMit = $ArrSegnatura['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'];
        if ($ArrSegnatura['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] != '') {
            $dataProtocMit = date('Ymd', strtotime($ArrSegnatura['Intestazione']['Identificatore']['DataRegistrazione']['@textNode']));
        }
        $denominazione = $ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'];
        $indirizzo = $ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'];
        if ($ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Comune']['@textNode'] != '') {
            $citta = $ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Comune']['@textNode'];
        }
        if ($ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['CAP']['@textNode'] != '') {
            $cap = $ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['CAP']['@textNode'];
        }
        if ($ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Provincia']['@textNode'] != '') {
            $provincia = $ArrSegnatura['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Provincia']['@textNode'];
        }
        $oggetto = $ArrSegnatura['Intestazione']['Oggetto']['@textNode'];

        $this->datiSegnatura = array();
        $this->datiSegnatura['PROT_MITTENTE'] = $numProtocMit;
        $this->datiSegnatura['DATAPROT_MITTENTE'] = $dataProtocMit;
        $this->datiSegnatura['MITTENTE']['DENOMINAZIONE'] = $denominazione;
        $this->datiSegnatura['MITTENTE']['INDIRIZZO'] = $indirizzo;
        $this->datiSegnatura['MITTENTE']['CITTA'] = $citta;
        $this->datiSegnatura['MITTENTE']['PROVINCIA'] = $provincia;
        $this->datiSegnatura['MITTENTE']['CAP'] = $cap;
        $this->datiSegnatura['OGGETTO'] = $oggetto;
        if (isset($ArrSegnatura['Intestazione']['Destinazione']['@attributes']['confermaRicezione'])) {
            $this->datiSegnatura['RICHIESTA_CONFERMA'] = strtolower($ArrSegnatura['Intestazione']['Destinazione']['@attributes']['confermaRicezione']);
        }
        $this->datiSegnatura['MAIL_CONFERMA'] = $ArrSegnatura['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'];
        $this->datiSegnatura['ALLEGATO_PRINCIPALE'] = '';
        /*
         * Imposto Nome Allegato Principale
         */
        if (isset($ArrSegnatura['Descrizione']['Documento'])) {
            if (isset($ArrSegnatura['Descrizione']['Documento']['@attributes']['nome'])) {
                $this->datiSegnatura['ALLEGATO_PRINCIPALE'] = $ArrSegnatura['Descrizione']['Documento']['@attributes']['nome'];
            }
        }
        /*
         * Dati Allegati
         */
        $Allegato = array();
        // Allegato principale
        if (isset($ArrSegnatura['Descrizione']['Documento']['CollocazioneTelematica']['@textNode'])) {
            $Allegato['NOME'] = $ArrSegnatura['Descrizione']['Documento']['@attributes']['nome'];
            $Allegato['COLLOCAZIONETELEMATICA'] = $ArrSegnatura['Descrizione']['Documento']['CollocazioneTelematica']['@textNode'];
            if (isset($ArrSegnatura['Descrizione']['Documento']['Impronta']['@textNode'])) {
                $Allegato['IMPRONTA'] = $ArrSegnatura['Descrizione']['Documento']['Impronta']['@textNode'];
            }
            if (isset($ArrSegnatura['Descrizione']['Documento']['Impronta']['@attributes']['algoritmo'])) {
                $Allegato['ALGORITMO'] = $ArrSegnatura['Descrizione']['Documento']['Impronta']['@attributes']['algoritmo'];
            }
            $this->datiSegnatura['PRINCIPALE_TELEMATICO'] = $Allegato;
        }
        // Scorro gli allegati 
        if (isset($ArrSegnatura['Descrizione']['Allegati']['Documento'])) {
            if (isset($ArrSegnatura['Descrizione']['Allegati']['Documento'][0])) {
                $AllegatiSegnatura = $ArrSegnatura['Descrizione']['Allegati']['Documento'];
            } else {
                $AllegatiSegnatura = $ArrSegnatura['Descrizione']['Allegati'];
            }
            foreach ($AllegatiSegnatura as $AllegatoSegn) {
                $Allegato = array();
                if (isset($AllegatoSegn['CollocazioneTelematica']['@textNode'])) {
                    $Allegato['NOME'] = $AllegatoSegn['@attributes']['nome'];
                    $Allegato['COLLOCAZIONETELEMATICA'] = $AllegatoSegn['CollocazioneTelematica']['@textNode'];
                    if (isset($AllegatoSegn['Impronta']['@textNode'])) {
                        $Allegato['IMPRONTA'] = $AllegatoSegn['Impronta']['@textNode'];
                    }
                    if (isset($AllegatoSegn['Impronta']['@attributes']['algoritmo'])) {
                        $Allegato['ALGORITMO'] = $AllegatoSegn['Impronta']['@attributes']['algoritmo'];
                    }
                    $this->datiSegnatura['ALLEGATI_TELEMATICI'][] = $Allegato;
                }
            }
        }
        return true;
    }

    private function parseConfermaRicezioneXML() {
        /*
         * Valido la segnatura:
         */
        //if (!$this->validateXML()) {
        /*
         * Non occore bloccare: esitoValidation è sempre disponibile per il controllo.
         * return false;
         */
        //}

        /*
         * Lettura Array XML
         */
        $ArrXml = $this->getXMLArray();
        if (!$ArrXml) {
            return false;
        }
        $ArrSegnatura = $ArrXml['ConfermaRicezione'];
        /*
         *  Setto identificatore.
         */
        $ArrIdentificatore = $ArrSegnatura['MessaggioRicevuto']['Identificatore'];
        if (isset($ArrIdentificatore['CodiceAmministrazione']['@textNode'])) {
            $this->identificatore['CodiceAmministrazione'] = $ArrIdentificatore['CodiceAmministrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['CodiceAOO']['@textNode'])) {
            $this->identificatore['CodiceAOO'] = $ArrIdentificatore['CodiceAOO']['@textNode'];
        }
        if (isset($ArrIdentificatore['NumeroRegistrazione']['@textNode'])) {
            $this->identificatore['NumeroRegistrazione'] = $ArrIdentificatore['NumeroRegistrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['DataRegistrazione']['@textNode'])) {
            $this->identificatore['DataRegistrazione'] = $ArrIdentificatore['DataRegistrazione']['@textNode'];
        }
        if (isset($ArrIdentificatore['CodiceRegistro']['@textNode'])) {
            $this->identificatore['CodiceRegistro'] = $ArrIdentificatore['CodiceRegistro']['@textNode'];
        }

        /*
         * Segnatura Parent:
         */
        $NumeroProt = str_pad(intval($this->identificatore['NumeroRegistrazione']), 6, '0', STR_PAD_LEFT);
        $DataProt = date('Ymd', strtotime($this->identificatore['DataRegistrazione']));
        $AnnoProt = substr($this->identificatore['DataRegistrazione'], 0, 4);
        /*
         * Codice Registro potrebbe essere importante valorizzarlo:
         *  Potrebbe tornare anche una C
         */
        $Anapro_rec = $this->proLib->getAnapro($AnnoProt . $NumeroProt, 'codice', 'P');
        // @TODO Contemplo anche arrivo? Occorre confrontare la data? Documento Formale esclusi?
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Segnatura parent non trovata.");
            return false;
        }


        $ChiaveProtocollo = array('PRONUM' => $Anapro_rec['PRONUM'], 'PROPAR' => $Anapro_rec['PROPAR']);
        $this->segnaturaParent = self::getInteropInstanceUscita($ChiaveProtocollo, self::TIPOMSG_SEGNATURA);
        if ($this->segnaturaParent->getErrCode() == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura segnatura parent. Errore: " . $this->segnaturaParent->getErrMessage());
            return false;
        }

        return true;
    }

    public function getXMLArray() {
        /*
         * Lettura del file XML
         */
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($this->pathFileMessaggio);
        $ArrayXml = $xmlObj->getArray();
        if (!$ArrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura XML Array.");
            return false;
        }
        return $ArrayXml;
    }

    private function validateXML() {
        $xml = file_get_contents($this->pathFileMessaggio);
        if ($xml === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura contenuto xml da validare.");
            return false;
        }
        $this->VersioneSegnatura = $this->PrendiVersioneSegnatura($this->pathFileMessaggio);
        if (!$this->VersioneSegnatura) {
            foreach (self::$FileCheckVersioniSegnatura as $KeyVersione => $FileCheck) {
                $this->VersioneSegnatura = $KeyVersione;
                try {
                    $retValida = $this->ValidaSegnatura();
                    if ($retValida) {
                        return $retValida;
                        break;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Generale:" . $exc->getMessage());
                    return false;
                }
            }
            return false;
        }
        try {
            return $this->ValidaSegnatura();
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Generale:" . $exc->getMessage());
            return false;
        }
    }

    private function elaboraErroreXml() {
        $errors = libxml_get_errors();
        $retErrore = '';
        foreach ($errors as $error) {
            $retErrore.="\n Errore " . $error->code;
            $retErrore.=': ' . trim($error->message) . "\n Riga: $error->line" . ", Colonna: $error->column";
//            $retErrore.= print_r($error, true);
        }
        libxml_clear_errors();
        return $retErrore;
    }

    public function getXMLString() {
        return file_get_contents($this->pathFileMessaggio);
    }

    public function getXmlFile() {
        return $this->pathFileMessaggio;
    }

    public function GetFileInteroperabileXML() {
        switch ($this->tipoMessaggio) {
            case self::TIPOMSG_SEGNATURA:
                $this->tipoMessaggio = self::TIPOMSG_SEGNATURA;
                if (!$this->getFileSegnaturaXML()) {
                    return false;
                }
                break;
            case self::TIPOMSG_CONFERMA:
                $this->tipoMessaggio = self::TIPOMSG_CONFERMA;
                if (!$this->getFileConfermaRicezioneXML()) {
                    return false;
                }
                break;

            case self::TIPOMSG_AGGIORNAMENTO:
            case self::TIPOMSG_ANNULLAMENTO:
            case self::TIPOMSG_ECCEZIONE:
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Tipologia file interoperabile non riconosciuta.");
                return false;
                break;
        }
        /*
         * Validazione XML
         */
        //if (!$this->validateXML()) {
        //     return false;
        // }


        return true;
    }

    private function getFileSegnaturaXML() {
        $fileXml = $this->getSegnaturaXML();
        if (!$fileXml) {
            return false;
        }

        $File = fopen($this->pathFileMessaggio, "w+");
        fwrite($File, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");
        fwrite($File, '<Segnatura versione="' . self::VERSIONE_SEGNATURA_ATTUALE . '">');
        fwrite($File, $fileXml);
        fwrite($File, "</Segnatura>");
        fclose($File);

        return true;
    }

    private function getSegnaturaXML() {
        $anapro_rec = $this->anapro_record;
        /*
         * Lettura Parametri
         */
        $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], "D");
        $anamed_dest = $this->proLib->GetAnamed($anapro_rec['PROCON'], 'codice');
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $anaent_2 = $this->proLib->GetAnaent('2');
        $anaent_26 = $this->proLib->GetAnaent('26');
        $anaent_28 = $this->proLib->GetAnaent('28');
        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_39 = $this->proLib->GetAnaent('39');
        $anaent_55 = $this->proLib->GetAnaent('55');
        /*
         * Valorizzazione dati
         */
        $dataReg = date('Y-m-d', strtotime($anapro_rec['PRODAR']));
        $confermaRicezione = array();
        if ($anaent_28['ENTDE6'] == '1') {
            $confermaRicezione = array('confermaRicezione' => 'si');
        }
        $tipoIndirizzoTelematico = array("tipo" => "smtp");
        $xmlArray = array();
        /*
         * Intestazione
         */
        $CodiceRegistro = '';
        if ($anapro_rec == 'C') {
            $CodiceRegistro = $this->proLib->GetCodiceRegistroDocFormali();
        } else {
            $CodiceRegistro = $this->proLib->GetCodiceRegistroProtocollo();
        }
        $xmlArray['Intestazione']['Identificatore']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
        $xmlArray['Intestazione']['Identificatore']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];
        $NumeroRegistrazione = str_pad(substr($anapro_rec['PRONUM'], 4), 7, '0', STR_PAD_LEFT);
        $xmlArray['Intestazione']['Identificatore']['CodiceRegistro']['@textNode'] = $CodiceRegistro;
        $xmlArray['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'] = $NumeroRegistrazione;
        $xmlArray['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] = $dataReg;
        if ($anapro_rec['PROPAR'] == 'P' || $anapro_rec['PROPAR'] == 'C') {
            /*
             * Origine
             */
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anaent_2['ENTDE2'] . ' ' . $anaent_2['ENTDE3'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];

            /*
             * Destinazione
             */
            $destinazione = array();
            $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
            if (!$tipoIndirizzoTelematicoDest['tipo']) {
                $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
            }
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
            $destinazione['IndirizzoTelematico']['@textNode'] = "";
            $destinazione['@attributes'] = $confermaRicezione;
            if ($anamed_dest['MEDCODAOO'] != '') {
                $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_dest['MEDDENAOO'];
                $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_dest['MEDCODAOO'];
                $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
            }
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;

            foreach ($anades_tab as $key => $anades_rec) {
                if ($anades_rec['DESCOD'] != '') {
                    $anamed_rec = $this->proLib->GetAnamed($anades_rec['DESCOD'], 'codice');
                    $destinazione = array();
                    $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
                    if (!$tipoIndirizzoTelematicoDest['tipo']) {
                        $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
                    }
                    $destinazione['@attributes'] = $confermaRicezione;
                    $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                    $destinazione['IndirizzoTelematico']['@textNode'] = "";
                    if ($anamed_rec['MEDCODAOO'] != '') {
                        $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_rec['MEDDENAOO'];
                        $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_rec['MEDCODAOO'];
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                    }
                    $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
                }
            }

            /*
             * Risposta
             */
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
        } else if ($anapro_rec['PROPAR'] == 'A') {
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = htmlentities($anapro_rec['PRONOM'], ENT_COMPAT, 'UTF-8');
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anapro_rec['PROIND'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = '';
            /*
             * Destinazione
             */
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $destinazione['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            /*
             * Caso di EFAA in Arrivo e Parametro per estrarre dati fattura
             */
            if ($anaent_55['ENTVAL'] && $anaent_39['ENTDE4'] == 'SEGNATURA') {
                if ($anaent_38['ENTDE1'] != '' && $anaent_38['ENTDE1'] == $anapro_rec['PROCODTIPODOC']) {
                    // Cig
                    include_once ITA_BASE_PATH . '/apps/Protocollo/proHalley.class.php';
                    $proHalley = new proHalley();
                    $DatiFatture = $proHalley->EstraiDatiFatture($anapro_rec);
                    $DatiFattura = array();
                    foreach ($DatiFatture['CIGFATTURA'] as $key => $CigFattura) {
                        if ($CigFattura) {
                            $DatiFattura['CIGFattura']['CIG']['@textNode'] = 'FAT:' . $key . 'CIG:' . $CigFattura . '@';
                        }
                    }
                    foreach ($DatiFatture['CUPFattura'] as $key => $CupFattura) {
                        if ($CupFattura) {
                            $DatiFattura['CUPFattura']['CUP']['@textNode'] = 'FAT:' . $key . 'CUP:' . $CupFattura. '@';
                        }
                    }
                    $DatiFattura['OggettoFattura']['@textNode'] = $DatiFatture['OGGETTOFATTURAPRIMARIGA'];
                    $dataScadenza = date('Y-m-d', strtotime($DatiFatture['DATASCADENZAPAGA']));
                    $DatiFattura['DataScadenzaPagamento']['@textNode'] = $dataScadenza;
                    $xmlArray['Intestazione']['DatiFattura'] = $DatiFattura;
                }
            }
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
        }
        /*
         * Oggetto
         */
        $xmlArray['Intestazione']['Oggetto']['@textNode'] = $anaogg_rec['OGGOGG'];
        $xmlArray['Intestazione']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);

        /*
         * Descrizione
         */
        $anadoc_tab = $this->proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], ' AND DOCSERVIZIO=0  ORDER BY DOCTIPO');
        if ($anadoc_tab) {
            $descrizione = array();
            $principaleTrovato = false;
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $principaleTrovato = true;
                }
            }
            if ($principaleTrovato === false) {
                $this->setErrCode(-1);
                $this->setErrMessage('Non è presente nessun Allegato principale. Selezionarne uno per poter continuare.');
                return false;
            }
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $descrizione['Documento']['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
                    $descrizione['Documento']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);
                } else {
                    $documento = array();
                    $documento['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
                    $documento['TipoDocumento']['@textNode'] = '';
                    $descrizione['Allegati']['Documento'][] = $documento;
                }
            }
            $xmlArray['Descrizione'] = $descrizione;
        } else {
            $xmlArray['Descrizione']['TestoDelMessaggio']['@textNode'] = '';
        }
        /*
         * Setto File Segnatura
         */
        $this->pathFileMessaggio = $this->tempPath . '/' . self::TIPOMSG_SEGNATURA . '.xml';
        /*
         *  Array To XML
         */
        $xmlObj = new QXML;
        $rootTag = "";
        $xmlObj->noCDATA();
        $xmlObj->noAddslashesattr();
        $xmlObj->toXML($xmlArray, $rootTag);
        return $xmlObj->getXml();
    }

    public function getFileConfermaRicezioneXML() {
        $fileXml = $this->getConfermaRicezioneXML();
        if (!$fileXml) {
            return false;
        }
        $File = fopen($this->pathFileMessaggio, "w+");
        fwrite($File, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");
        fwrite($File, '<ConfermaRicezione versione="' . self::VERSIONE_SEGNATURA_ATTUALE . '">');
        fwrite($File, $fileXml);
        fwrite($File, "</ConfermaRicezione>");
        fclose($File);
        return true;
    }

    public function getConfermaRicezioneXML() {
        /*
         * Setto Identificatore
         */
        $anaent_26 = $this->proLib->GetAnaent('26');
        $anaent_44 = $this->proLib->GetAnaent('44');
        /*
         * Valorizzazione dati
         */
        $dataReg = date('Y-m-d', strtotime($this->anapro_record['PRODAR']));
        $NumeroRegistrazione = str_pad(substr($this->anapro_record['PRONUM'], 4), 7, '0', STR_PAD_LEFT);

        $this->identificatore = array();
        $this->identificatore['CodiceAmministrazione'] = $anaent_26['ENTDE1'];
        $this->identificatore['CodiceAOO'] = $anaent_26['ENTDE2'];
        $this->identificatore['CodiceRegistro'] = $anaent_44['ENTDE1'];
        $this->identificatore['NumeroRegistrazione'] = $NumeroRegistrazione;
        $this->identificatore['DataRegistrazione'] = $dataReg;

        /*
         * Ricavo XML Segnatura del protocollo.
         */
        $proLibAllegati = new proLibAllegati();
        $DocPath = array();
        $Anadoc_tab = $this->proLib->GetAnadoc($this->anapro_record['PRONUM'], 'codice', true, $this->anapro_record['PROPAR']);
        foreach ($Anadoc_tab as $Anadoc_rec) {
            if (strtolower($Anadoc_rec['DOCNAME']) == strtolower(self::TIPOMSG_SEGNATURA . '.xml')) {
                $DocPath = $proLibAllegati->GetDocPath($Anadoc_rec['ROWID'], false, false, true);
                if (!$DocPath) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in lettura file Segnatura. Errore: " . $proLibAllegati->getErrMessage());
                    return false;
                }
                break;
            }
        }
        if (!$DocPath) {
            $this->setErrCode(-1);
            $this->setErrMessage("File Segnatura.xml non trovato nel protocollo.");
            return false;
        }
        /*
         * Lettura Segnatura Parent.
         */

        $FileDest = $this->tempPath . '/Segnatura.xml';
        $FileSegnatura = $proLibAllegati->CopiaDocAllegato($Anadoc_rec['ROWID'], $FileDest);
        $this->segnaturaParent = self::getInteropInstanceEntrata($FileSegnatura);
        if ($this->segnaturaParent->getErrCode() == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura segnatura parent. Errore: " . $this->segnaturaParent->getErrMessage());
            return false;
        }

        /*
         * Scrittura Array
         */
        $xmlArray = array();
        /*
         * Conferma Ricevuta Identificatore
         */
        $xmlArray['Identificatore']['CodiceAmministrazione']['@textNode'] = $this->identificatore['CodiceAmministrazione'];
        $xmlArray['Identificatore']['CodiceAOO']['@textNode'] = $this->identificatore['CodiceAOO'];
        $xmlArray['Identificatore']['CodiceRegistro']['@textNode'] = $this->identificatore['CodiceRegistro'];
        $xmlArray['Identificatore']['NumeroRegistrazione']['@textNode'] = $this->identificatore['NumeroRegistrazione'];
        $xmlArray['Identificatore']['DataRegistrazione']['@textNode'] = $this->identificatore['DataRegistrazione'];
        /*
         * MessaggioRicevuto
         */
        $IdentificatoreParent = $this->segnaturaParent->getIdentificatore();
        $xmlArray['MessaggioRicevuto']['Identificatore']['CodiceAmministrazione']['@textNode'] = $IdentificatoreParent['CodiceAmministrazione'];
        $xmlArray['MessaggioRicevuto']['Identificatore']['CodiceAOO']['@textNode'] = $IdentificatoreParent['CodiceAOO'];
        if (isset($IdentificatoreParent['CodiceRegistro']) && $IdentificatoreParent['CodiceRegistro']) {
            $xmlArray['MessaggioRicevuto']['Identificatore']['CodiceRegistro']['@textNode'] = $IdentificatoreParent['CodiceRegistro'] . '';
        }
        $xmlArray['MessaggioRicevuto']['Identificatore']['NumeroRegistrazione']['@textNode'] = $IdentificatoreParent['NumeroRegistrazione'];
        $xmlArray['MessaggioRicevuto']['Identificatore']['DataRegistrazione']['@textNode'] = $IdentificatoreParent['DataRegistrazione'];



        /*
         *  Array To XML
         */
        $this->pathFileMessaggio = $this->tempPath . '/' . self::TIPOMSG_CONFERMA . '.xml';
        $xmlObj = new QXML;
        $rootTag = "";
        $xmlObj->noCDATA();
        $xmlObj->noAddslashesattr();
        $xmlObj->toXML($xmlArray, $rootTag);
        return $xmlObj->getXml();
    }

    public function PrendiVersioneSegnatura($FileSegnatura) {
        /*  Setto un Default 2013 */
        $this->VersioneSegnatura = '';
        $ItaXmlObj = new itaXML;
        $xml = new XMLReader();
        $xmlObj = null;

        $retOpen = $xml->open($FileSegnatura, 'UTF-8');
        if (!$retOpen) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il testo nella Segnatura xml");
            return false;
        }
        /*
         * Nodo per versione segnatura */
        if (!$this->seekNodeSegnatura($xml, self::$ElementiSegnatura[$this->tipoMessaggio])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il tag " . self::$ElementiSegnatura[$this->tipoMessaggio] . " nel file $FileSegnatura.");
            $xml->close();
            return false;
        }
        $VersioneSegnatura = $xml->getAttribute('versione');
        if ($VersioneSegnatura) {
            $this->VersioneSegnatura = $VersioneSegnatura;
        }
        $xml->close();

        return $this->VersioneSegnatura;
    }

    private function seekNodeSegnatura($xml, $nodeName) {
        while ($xml->read()) {
            list($prenome, $nome) = explode(':', $xml->name);
            if ($xml->nodeType == XMLReader::ELEMENT && ($nome == $nodeName || $xml->name == $nodeName)) {
                return $xml;
            }
        }
        return false;
    }

    public function ValidaSegnatura() {

        $FilePerValidazione = self::$FileCheckVersioniSegnatura[$this->VersioneSegnatura];
        $extFile = pathinfo($FilePerValidazione, PATHINFO_EXTENSION);
        $FilePathValidazione = $this->GetPathResourceSegnature() . $FilePerValidazione;

        switch ($extFile) {
            case 'dtd':
                /* Validazione tramite dtd: */
                $xml = file_get_contents($this->pathFileMessaggio);
                if ($xml === false) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in lettura contenuto xml da validare.");
                    return false;
                }

                $root = self::$ElementiSegnatura[$this->tipoMessaggio];
                $old = new DOMDocument;
                if (!$old->loadXML($xml)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in caricamento xml da validare.");
                    return false;
                }

                $creator = new DOMImplementation;
                $doctype = $creator->createDocumentType($root, null, $FilePathValidazione);
                $new = $creator->createDocument(null, null, $doctype);
                $new->encoding = "ISO-8859-1";
                $oldNode = $old->getElementsByTagName($root)->item(0);
                $newNode = $new->importNode($oldNode, true);
                $new->appendChild($newNode);
                if ($new->validate()) {
                    $this->setErrCode('');
                    $this->esitoValidation = self::VALID_XML;
                    $this->descrValidation = "File di tipo " . $this->tipoMessaggio . " valido.";
                    return true;
                } else {
                    $this->esitoValidation = self::NOVALID_XML;
                    $retErrore = $this->elaboraErroreXml();
                    $this->descrValidation = "File di tipo " . $this->tipoMessaggio . " NON valido." . $retErrore;
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->descrValidation);
                    return false;
                }
                break;

            default:
            case 'xsd':
                /* Validazione tramite xsd */
                libxml_use_internal_errors(true);
                $xml = new DOMDocument();
                $xml->load($this->pathFileMessaggio);
                if (!$xml->schemaValidate($FilePathValidazione)) {
                    $this->esitoValidation = self::NOVALID_XML;
                    $retErrore = $this->elaboraErroreXml();
                    $this->descrValidation = "File di tipo " . $this->tipoMessaggio . " NON valido." . $retErrore;
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->descrValidation);
                    return false;
                } else {
                    $this->setErrCode('');
                    $this->esitoValidation = self::VALID_XML;
                    $this->descrValidation = "File di tipo " . $this->tipoMessaggio . " valido.";
                    return true;
                }
                break;
        }
    }

}

?>
