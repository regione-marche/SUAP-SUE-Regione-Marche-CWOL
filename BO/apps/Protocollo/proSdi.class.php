<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    02.03.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php'; //***@Alfresco
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

class proSdi {

    const TIPOMESS_RC = 'RC';
    const TIPOMESS_NS = 'NS';
    const TIPOMESS_MC = 'MC';
    const TIPOMESS_NE = 'NE';
    const TIPOMESS_MT = 'MT';
    const TIPOMESS_EC = 'EC';
    const TIPOMESS_SE = 'SE';
    const TIPOMESS_DT = 'DT';
    const TIPOMESS_AT = 'AT';
    const VERSIONE_STILE_MSG = '1.0';
    const StileFattura = 'fatturapa_v1.1.xsl';
    const FATTURA_XSD = "fatturapa_v1.1.xsd";
    const MESSAGGIOSDI_XSD = "MessaggiTypes_v1.1.xsd";
    // nuove costanti fatture. **NewVFattura
    const FATTURA_VERSIONE11 = '1.1';
    const FATTURA_VERSIONE12 = 'FPA12';
    const FATTURA_VERSIONE12_SPACC = '1.2';
    const FATTURA_VERSIONE12_FPR12 = 'FPR12';
    const FATTURA_RIEPILOGO_RIFPA12 = 'RIFPA12';//Riepilogo Dati fattura

    public static $ElencoTipiMessaggio = array(
        self::TIPOMESS_RC => 'Ricevuta di consegna',
        self::TIPOMESS_NS => 'Notifica di scarto',
        self::TIPOMESS_MC => 'Notifica di mancata consegna',
        self::TIPOMESS_NE => 'Notifica esito cedente / prestatore',
        self::TIPOMESS_MT => 'File dei metadati',
        self::TIPOMESS_EC => 'Notifica di esito cessionario / committente',
        self::TIPOMESS_SE => 'Notifica di scarto esito cessionario / committente',
        self::TIPOMESS_DT => 'Notifica decorrenza termini',
        self::TIPOMESS_AT => 'Attestazione di avvenuta trasmissione della fattura con impossibilità di recapito'
    );
    public static $ElencoStiliMessaggio = array(
        self::TIPOMESS_RC => 'RC_v1.0.xsl',
        self::TIPOMESS_NS => 'NS_v1.0.xsl',
        self::TIPOMESS_MC => 'MC_v1.0.xsl',
        self::TIPOMESS_NE => 'NE_v1.0.xsl',
        self::TIPOMESS_MT => 'MT_v1.1.xsl', // self::TIPOMESS_MT => 'MT_v1.0.xsl',
        self::TIPOMESS_EC => 'EC_v1.0.xsl',
        self::TIPOMESS_SE => 'SE_v1.0.xsl',
        self::TIPOMESS_DT => 'DT_v1.0.xsl',
        self::TIPOMESS_AT => 'AT_v1.1.xsl'
    );
    // Nuove costanti per stili **NewVFattura
    public static $ElencoStiliFattura = array(
        self::FATTURA_VERSIONE11 => 'fatturapa_v1.1.xsl',
        self::FATTURA_VERSIONE12 => 'fatturapa_v1.2.xsl',
        self::FATTURA_VERSIONE12_SPACC => 'fatturapa_v1.2.xsl',
        self::FATTURA_VERSIONE12_FPR12 => 'fatturapa_v1.2.xsl',
        self::FATTURA_RIEPILOGO_RIFPA12 => 'riepilogo_fatturapa_v1.2.xsl'
    );
    public static $ElencoXSDFattura = array(
        self::FATTURA_VERSIONE11 => 'fatturapa_v1.1.xsd',
        self::FATTURA_VERSIONE12 => 'fatturapa_v1.2.xsd',
        self::FATTURA_VERSIONE12_SPACC => 'fatturapa_v1.2.xsd',
        self::FATTURA_VERSIONE12_FPR12 => 'fatturapa_v1.2.xsd',
        self::FATTURA_RIEPILOGO_RIFPA12 => 'fatturapa_v1.2.xsd'
    );
    private $ErrCode;
    private $ErrMessage;
    private $FileDaControllare;
    private $NomeFileDaControllare;
    private $Struttura = array();
    private $emlMessage = array();
    private $TipoMessaggio;
    private $NomeFileFattura;
    private $ProgressivoUnivocoMessaggio;
    private $NomeFileMessaggio;
    private $CodUnivocoFile;
    private $FilePathMessaggio;
    private $FilePathFattura;
    private $MessaggioSdi;
    private $FatturaPA;
    private $LottoFatturePA;
    private $ZipFatturaPA;
    //Array Xml:
    private $XmlMessaggio = array();
    private $XmlFattura = array();
    //Variabili Appogggoio strutture xml
    private $FatturaElettronicaHeader;
    private $DatiGeneraliDocumento;
    private $DatiOrdineAcquisto;
    private $DatiContratto;
    private $DatiBeniServizi;
    private $DatiConvenzione;
    private $DatiTrasporto;
    private $DatiCig;
    private $DatiPagamento;
    //Array Estratti
    private $EstrattoMessaggio = array();
    private $EstrattoFattura = array();
    private $tempPath;
    private $FileFatturaUnivoco;
    private $MittenteMail;
    private $EstrattoAllegatiFattura = array();
    private $AllegatiDocumento = array();
    private $ElaboraSoloMessaggio;
    private $ParseAllegati;
    private $WarningMessages = array();
    // Nuova variabile per site **NewVFattura
    private $VersioneFattura;

    public function getMittenteMail() {
        return $this->MittenteMail;
    }

    private function setMittenteMail($MittenteMail) {
        $this->MittenteMail = $MittenteMail;
    }

    public function getFileFatturaUnivoco() {
        return $this->FileFatturaUnivoco;
    }

    private function setFileFatturaUnivoco($FileFatturaUnivoco) {
        $this->FileFatturaUnivoco = $FileFatturaUnivoco;
    }

    public function getFilePathFattura() {
        return $this->FilePathFattura;
    }

    public function getEstrattoMessaggio() {
        return $this->EstrattoMessaggio;
    }

    public function getFilePathMessaggio() {
        return $this->FilePathMessaggio;
    }

    public function getEstrattoFattura() {
        return $this->EstrattoFattura;
    }

    public function getEstrattoAllegatiFattura() {
        return $this->EstrattoAllegatiFattura;
    }

    public function getCodUnivocoFile() {
        return $this->CodUnivocoFile;
    }

    private function setCodUnivocoFile($CodUnivocoFile) {
        $this->CodUnivocoFile = $CodUnivocoFile;
    }

    public function getXmlMessaggio() {
        return $this->XmlMessaggio;
    }

    public function getXmlFattura() {
        return $this->XmlFattura;
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

    public function isMessaggioSdi() {
        return $this->MessaggioSdi;
    }

    public function isZipFatturaPA() {
        return $this->ZipFatturaPA;
    }

    public function isFatturaPA() {
        return $this->FatturaPA;
    }

    public function getNomeFileMessaggio() {
        return $this->NomeFileMessaggio;
    }

    private function setNomeFileMessaggio($NomeFileMessaggio) {
        $this->NomeFileMessaggio = $NomeFileMessaggio;
    }

    public function getProgressivoUnivocoMessaggio() {
        return $this->ProgressivoUnivocoMessaggio;
    }

    private function setProgressivoUnivocoMessaggio($ProgressivoUnivocoMessaggio) {
        $this->ProgressivoUnivocoMessaggio = $ProgressivoUnivocoMessaggio;
    }

    public function getNomeFileFattura() {
        return $this->NomeFileFattura;
    }

    private function setNomeFileFattura($NomeFileFattura) {
        $this->NomeFileFattura = $NomeFileFattura;
    }

    private function setStruttura($Struttura) {
        $this->Struttura = $Struttura;
    }

    private function setEmlMessage($emlMessage) {
        $this->emlMessage = $emlMessage;
    }

    public function getTipoMessaggio() {
        return $this->TipoMessaggio;
    }

    private function setTipoMessaggio($TipoMessaggio) {
        $this->TipoMessaggio = $TipoMessaggio;
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function getFileDaControllare() {
        return $this->FileDaControllare;
    }

    private function setFileDaControllare($FileDaControllare) {
        $this->FileDaControllare = $FileDaControllare;
    }

    public function getNomeFileDaControllare() {
        return $this->NomeFileDaControllare;
    }

    private function setNomeFileDaControllare($NomeFileDaControllare) {
        $this->NomeFileDaControllare = $NomeFileDaControllare;
    }

    public function getWarningMessages() {
        return $this->WarningMessages;
    }

    // nuova funzione per stile **NewVFattura
    public function setVersioneFattura($VersioneFattura) {
        $this->VersioneFattura = $VersioneFattura;
    }

    public function getVersioneFattura() {
        return $this->VersioneFattura;
    }

    function getDatiBeniServizi() {
        return $this->DatiBeniServizi;
    }
    function getDatiPagamento() {
        return $this->DatiPagamento;
    }

    function setDatiBeniServizi($DatiBeniServizi) {
        $this->DatiBeniServizi = $DatiBeniServizi;
    }

    public static function getInstance($DatiMail, $ExtraParam = array()) {
        //@TODO Verificare i vari return
        if (!$DatiMail) {
            return false;
        }
        try {
            $obj = new proSdi();
        } catch (Exception $e) {
            return false;
        }
        $obj->ElaboraSoloMessaggio = '';
        if ($ExtraParam['ELABORASOLOMESSAGGIO']) {
            $obj->ElaboraSoloMessaggio = true;
        }
        $obj->ParseAllegati = false;
        if ($ExtraParam['PARSEALLEGATI']) {
            $obj->ParseAllegati = true;
        }

        $tempPath = self::createTempPath();
        if (!$tempPath) {
            $obj->setErrCode(-1);
            $obj->setErrMessage('Errore nella creazione della cartella temporanea.');
            return $obj;
        }
        $obj->tempPath = $tempPath;
        if (get_class($DatiMail) === 'emlMessage') {
            $obj->emlMessage = $DatiMail;
            if (!$DatiMail->parseEmlFileDeep()) {
                $obj->setErrCode(-1);
                $obj->setErrMessage('Errore nella lettura del messaggio eml.');
                return $obj;
            }

            $obj->Struttura = $DatiMail->getStruct();
            if (!$obj->ControllaMail()) {
                return $obj;
            }
        } else if (is_array($DatiMail)) {
            if ($DatiMail['LOCAL_FILEPATH'] && $DatiMail['LOCAL_FILENAME']) {
                if (substr($DatiMail['LOCAL_FILEPATH'], 0, 8) == 'DOCUUID:') {
                    $itaDocumentale = new itaDocumentale('ALFCITY');
                    $ResultQery = $itaDocumentale->queryByUUID(substr($DatiMail['LOCAL_FILEPATH'], 7));
                    if (!$ResultQery) {
                        $obj->setErrCode(-1);
                        $obj->setErrMessage('Il file che si sta cercando di aprire non esiste.<br>');
                        return $obj;
                    }
                } else {
                    if (!file_exists($DatiMail['LOCAL_FILEPATH'])) {
                        $obj->setErrCode(-1);
                        $obj->setErrMessage('Il file che si sta cercando di aprire non esiste.<br>' . $DatiMail['LOCAL_FILEPATH']);
                        return $obj;
                    }
                }
                $obj->setFileDaControllare($DatiMail['LOCAL_FILEPATH']);
                if (!$DatiMail['LOCAL_FILENAME']) {
                    $obj->setErrCode(-1);
                    $obj->setErrMessage('Errore, Nome File Mancante.');
                    return $obj;
                }
                $obj->setNomeFileDaControllare($DatiMail['LOCAL_FILENAME']);
                $obj->setFileFatturaUnivoco($DatiMail['LOCAL_FILENAME']);
                if (!$obj->ControllaDaFile()) {
                    return $obj;
                }
            } else {
                $obj->Struttura = $DatiMail;
                if (!$obj->ControllaMail()) {
                    return $obj;
                }
            }
        }
        /* else if (is_string($DatiMail)) {
          if (file_exists($DatiMail)) {
          $obj->setFileDaControllare($DatiMail);
          if (!$obj->ControllaDaFile()) {
          return $obj;
          }
          } else {
          $obj->setErrCode(-1);
          $obj->setErrMessage('Il file che si sta cercando di aprire non esiste.<br>' . $DatiMail);
          return $obj;
          }
          }
         * 
         */
        if (!$obj->ControllaFiles()) {
            return $obj;
        }
        return $obj;
    }

    public function ControllaFiles() {
        $this->LottoFatturePA = false;

        $this->EstrattoMessaggio = array();
        $this->EstrattoFattura = array();
        // Carico Dati Fattura se c'è
        if ($this->isFatturaPA()) {
            if (!$this->FilePathFattura) {
                $this->setErrCode(-1);
                $this->setErrMessage('Non sono presenti file fattura.');
                return false;
            }
            foreach ($this->FilePathFattura as $FileFattura) {
                if (!$this->CaricaEstrattoFattura($FileFattura)) {
                    return false;
                }
            }
        }
        if ($this->isMessaggioSdi()) {
            // Carico Dati Messaggio.
            if (!$this->GetDatiMessaggioXml()) {
                return false;
            }
            $this->CaricaEstrattoMessaggio();
        }
        return true;
    }

    public function ControllaMail() {
        $this->MessaggioSdi = false;
        $this->ZipFatturaPA = false;
        $this->FatturaPA = false;
        $this->FilePathFattura = array();
        $this->MittenteMail = '';

        //Se è una mail normale non devo considerarla.
        if ($this->Struttura['ita_PEC_info'] == 'N/A') {
            return false;
        }
        //$MessaggioOriginale = $this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile'];
        //Se non ha allegati non devo considerarla.
        if (!isset($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']['Attachments'])) {
            return false;
        }
//        Out::msgInfo('mail', print_r($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile'], true));
//        if (!$this->CheckAnomalieAllegati($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile'])) {
//            return false;
//        }
        //Controllo gli Allegati Del Messaggio Originale ... ?
        $MatchPattern = $this->ControllaAllegatiMessaggio($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']);
        if ($MatchPattern) {
            if (isset($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']['FromAddress'])) {
                $this->setMittenteMail($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']['FromAddress']);
            }
            //Copio il file messaggio nella cartella temporanea di lavoro sdi
            $DestinazioneFileMessaggio = $this->getTempPath() . '/' . $MatchPattern['NomeFileMessaggio'];
            if (!@copy($this->FilePathMessaggio, $DestinazioneFileMessaggio)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella copia del file .");
                return false;
            }
            $this->FilePathMessaggio = $DestinazioneFileMessaggio;
            $this->setTipoMessaggio($MatchPattern['TipoMessaggio']);
            $this->setNomeFileFattura($MatchPattern['CodUnivocoFile']);
            $this->setProgressivoUnivocoMessaggio($MatchPattern['ProgUnivoco']);
            $this->setNomeFileMessaggio($MatchPattern['NomeFileMessaggio']);
            $this->setCodUnivocoFile($MatchPattern['CodUnivocoFile']);
            $this->MessaggioSdi = true;

            if ($this->ElaboraSoloMessaggio) {
                return true;
            }
            if ($this->TipoMessaggio == self::TIPOMESS_MT) {
                // Controllo Presenza File FatturaPA
                $retAllegato = $this->ControllaPresenzaAllegato($this->NomeFileFattura . '.xml');
                $retAllegatoP7m = $this->ControllaPresenzaAllegato($this->NomeFileFattura . '.xml.p7m');
                $retAllegatoZip = $this->ControllaPresenzaAllegato($this->NomeFileFattura . '.zip');
                //DA CONTEMPLARE LO ZIP
                if (!$retAllegato && !$retAllegatoP7m && !$retAllegatoZip) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File FatturaPa non presente.");
                    return false;
                }
                // Controllo se invece è presente il zip e lo tratto in modo diverso.
                if ($retAllegatoZip) {
                    if (!$this->AssegnaFatturaDaZip($retAllegatoZip['DataFile'])) {
                        return false;
                    }
                    $this->setFileFatturaUnivoco(pathinfo($retAllegatoZip['FileName'], PATHINFO_BASENAME));
                } elseif ($retAllegatoP7m) {
                    if (!$this->AssegnaFatturaDaP7m($retAllegatoP7m['DataFile'])) {
                        return false;
                    }
                    $this->setFileFatturaUnivoco(pathinfo($retAllegatoP7m['FileName'], PATHINFO_BASENAME));
                } else { // Altrimenti uso l'xml
                    if (!$this->AssegnaFatturaDaXml($retAllegato['DataFile'])) {
                        return false;
                    }
                    $this->setFileFatturaUnivoco(pathinfo($retAllegato['FileName'], PATHINFO_BASENAME));
                }
            } else if ($this->TipoMessaggio == self::TIPOMESS_AT) {
                $ext = pathinfo($this->FilePathMessaggio, PATHINFO_EXTENSION);
                if (strtolower($ext) != 'zip') {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Il file AT ricevuto deve essere un file zip.");
                    return false;
                }
                if (!$this->AssegnaMessaggioDaZip($this->FilePathMessaggio)) {
                    return false;
                }
                $this->FilePathMessaggio = $this->getTempPath() . '/' . pathinfo($this->FilePathMessaggio, PATHINFO_FILENAME) . '.xml';
            }
        }
        return true;
    }

    private function ApriFatturaDaZip($File) {
        // Creo la cartella temporanea
        $subPath = "zip-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        // 
        $ret = itaZip::Unzip($File, $tempPath);
        if ($ret != 1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Estrazione file fallita.");
            return false;
        }
        if (!is_dir($tempPath)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Cartella " . pathinfo($File, PATHINFO_FILENAME) . " non trovata.");
            return false;
        }

        return $tempPath;
    }

    private function ApriFatturaDaP7m($File) {
        $FileFirmato = $this->PrendiFileP7m($File);
        if (!$FileFirmato) {
            return false;
        }
        /*
         *  -- Funzione utilizzata SOLO per Emergenze. 
          $p7m = sdiP7mRemoto::getInstance($FileFirmato);
          if (!$p7m) {
          $remoteErrCode = -1;
          $remoteErrMessage = "Verifica File Firmato Fallita";
          //            $this->setErrCode(-1);
          //            $this->setErrMessage("Verifica File Firmato Fallita");
          //            return false;
          }
          if ($remoteErrCode !== -1) {
          if (!file_exists($p7m->getContentFileName())) {
          $remoteErrCode = -1;
          $remoteErrMessage = "Errore nella estrazione file dal p7m.";
          //            $this->setErrCode(-1);
          //            $this->setErrMessage("Errore nella estrazione file dal p7m.");
          //                return false;
          } else {
          return $p7m;
          }
          }

         */

        $p7m = itaP7m::getP7mInstance($FileFirmato);
        if (!$p7m) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica File Firmato Fallita");
            return false;
        }
        //Se la verifica ha errori, continua comunque?
        if (!$p7m->isFileVerifyPassed()) {
            //            $this->setErrCode(-1);
            //            $this->setErrMessage("Verifica File Firmato con errori.");
        }
        if (!file_exists($p7m->getContentFileName())) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella estrazione file dal p7m.");
            return false;
        }
        return $p7m;
    }

    private function ControllaAllegatiMessaggio() {
        $MatchPattern = false;
        foreach ($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']['Attachments'] as $key => $allegato) {
            if (!$allegato['FileName']) {
                continue;
            }
            $ext = pathinfo($allegato['FileName'], PATHINFO_EXTENSION);
            if (strtolower($ext) != 'xml' && strtolower($ext) != 'zip') {
                continue;
            }
            // Controllo il pattern allegato:
            $MatchPattern = $this->ControllaPatternAllegato($allegato);
            if ($MatchPattern) {
                $this->FilePathMessaggio = $allegato['DataFile'];
                break;
            }
        }
        return $MatchPattern;
    }

    private function ControllaPatternAllegato($allegato) {
        $Match = array();
        $ElementiNomeFile = self::GetElementiNomeFile($allegato['FileName']);
        // Ctr.1 Se il conteggio delle chiavi è 4, probabilmente è un Notifica FatturaPA. 
        if ($ElementiNomeFile['NumeroElementi'] == 4) {
            $TipoMessaggio = $ElementiNomeFile['TipoMessaggio'];
            // Si potrebbe controllare anche la seconda parte del CodUnivocoFile? (massimo 5 caratteri)
            // Ctr.2 Se la lunghezza del "Tipo Messaggio" è 2 e lo trovo nell'ElencoTipiMessaggio:
            if (strlen($TipoMessaggio) == 2 && self::$ElencoTipiMessaggio[$TipoMessaggio]) {
                // Ctr.3 Se il progressivo univoco è di 3 (o minore?) 
                if (strlen($ElementiNomeFile['ProgUnivoco']) == 3) {
                    $Match = $ElementiNomeFile;
                }
            }
        }
        return $Match;
    }

    public static function GetElementiNomeFile($NomeFile) {
        // Formato: XXXXXXXXXXXXX_XXXXX_XX_XXX.xml
        $ElementiNomeFile = explode('_', $NomeFile);
        $NumeroElementi = count($ElementiNomeFile);
        list($CodUnivocoP1, $CodUnivocoP2, $TipoMessaggio) = $ElementiNomeFile;
        list($ProgUnivoco, $extNomeFile) = explode('.', $ElementiNomeFile[3]);
        $Elementi = array(
            'NomeFileMessaggio' => $NomeFile,
            'NumeroElementi' => $NumeroElementi,
            'CodUnivocoFile' => $CodUnivocoP1 . '_' . $CodUnivocoP2,
            'CodUnivocoFileP1' => $CodUnivocoP1,
            'CodUnivocoFileP2' => $CodUnivocoP2,
            'TipoMessaggio' => $TipoMessaggio,
            'ProgUnivoco' => $ProgUnivoco
        );
        return $Elementi;
    }

    private function ControllaPresenzaAllegato($FileRicercato) {
        foreach ($this->Struttura['ita_PEC_info']['messaggio_originale']['ParsedFile']['Attachments'] as $key => $Allegato) {
            if (strtolower($Allegato['FileName']) == strtolower($FileRicercato)) {
                return $Allegato;
            }
        }
        return false;
    }

    private function GetDatiMessaggioXml() {
        $XmlCont = file_get_contents($this->FilePathMessaggio);
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($XmlCont);

        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML . Impossibile leggere il testo nel Messaggio xml.");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML. Impossibile estrarre i dati Messaggio xml.");
            return false;
        }
        // Inserisco PREALB
        $this->XmlMessaggio = $arrayXml;
        return true;
    }

    private function ProteggiHeaderXmlFattura($FileFattura) {
        $contenuto = file_get_contents($FileFattura);
        $ArrProtect[] = array('CTR' => '"http://www.w3.org/2000/09/xmldsig "', 'REPLACE' => '"http://www.w3.org/2000/09/xmldsig#"');
        $ArrProtect[] = array('CTR' => '"http://www.w3.org/2000/09/xmldsig"', 'REPLACE' => '"http://www.w3.org/2000/09/xmldsig#"');
        foreach ($ArrProtect as $Protect) {
            $contenuto = str_replace($Protect['CTR'], $Protect['REPLACE'], $contenuto);
        }
        /*
         * Protezione caratteri speciali all'interno degli url.
         */
        preg_match('/(.*?)<FatturaElettronicaHeader>/s', $contenuto, $matches);
        $strCheck = $matches[1];
        preg_match_all('/"(https?:\/\/.*?)"/', $strCheck, $matches);
        foreach ($matches[1] as $value) {
            $encodedValue = str_replace(' ', '%20', $value);
            $contenuto = str_replace($value, $encodedValue, $contenuto);
        }
        file_put_contents($FileFattura, $contenuto);
    }

    // Nuove funzioni per stile:
    private function GetDatiFatturaXml($FileFattura) {
        // Inizializzo le variabili 

        $this->DatiGeneraliDocumento = array();
        $this->DatiOrdineAcquisto = array();
        $this->DatiBeniServizi = array();
        $this->DatiContratto = array();
        $this->DatiConvenzione = array();
        $this->DatiTrasporto = array();
        $this->DatiCig = array();
        $this->DatiPagamento = array();
        $this->FatturaElettronicaHeader = array();
        $ItaXmlObj = new itaXML;
        $ItaXmlObjAllegati = new itaXML;
        $xml = new XMLReader();
        $xmlObj = null;
        $xmlObjAllegati = null;

        /*
         * Controlla url xmldsig# senza # perche da problemi da attivare dopo test a corridonia
         */
        $this->ProteggiHeaderXmlFattura($FileFattura);
        /*
         * Controlloe EFAS tag "Causale_1"
         */
        $this->SostituzioneTagCausale($FileFattura);

        $retOpen = $xml->open($FileFattura, 'UTF-8');
        if (!$retOpen) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il testo nella Fattura xml");
            return false;
        }
        /* Nuovo Nodo per versione fattura **NewVFattura */
        if (!$this->seekNodeFatturaElettronica($xml, 'FatturaElettronica')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il tag FatturaElettronica nel file $FileFattura.");
            $xml->close();
            return false;
        }
        $this->VersioneFattura = '1.1';
        $VersioneFattura = $xml->getAttribute('versione');
        if ($VersioneFattura) {
            $this->VersioneFattura = $VersioneFattura;
        }
        /* Fine nodo per versione fattura */

        if (!$this->seekNode($xml, 'FatturaElettronicaHeader')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il tag FatturaElettronicaHeader nel file $FileFattura.");
            $xml->close();
            return false;
        }
        $xmlString = $xml->readOuterXml();
        $retXml = $ItaXmlObj->setXmlFromString($xmlString);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML . Impossibile leggere Header nell'xml Fattura.");
            return false;
        }
        $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML. Impossibile estrarre i dati Fattura xml.");
            return false;
        }
        $this->FatturaElettronicaHeader = $arrayXml;
        $xml->close();


        while (true) {
            $retStr = $this->scorriFileXml($xmlObj, "FatturaElettronicaBody", $FileFattura);
            if ($retStr === false) {
                break; //****
            }

            /*
             * DatiGeneraliDocumento
             */

            /* @var $retNode XMLReader */
            $nodeReader = new XMLReader();
            $nodeReader->XML($xmlObj->readOuterXml(), "UTF-8"); //modificata qui
            $nodeReader = $this->seekNode($nodeReader, 'DatiGeneraliDocumento');
            if ($nodeReader === false) {
                $this->setErrCode(-1);
                $this->setErrMessage('Impossibile trovare il Nodo DatiGeneraliDocumento');
                return false;
            }
            $retStrDatiGenerali = $nodeReader->readOuterXml();
            $retXml = $ItaXmlObj->setXmlFromString($retStrDatiGenerali);
            if (!$retXml) {
                $this->setErrCode(-1);
                $this->setErrMessage("File XML . Impossibile leggere Dati Body nell'xml Fattura.");
                return false;
            }
            $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
            if (!$arrayXml) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura XML. Impossibile estrarre il Body dai dati Fattura xml.");
                return false;
            }
            $this->DatiGeneraliDocumento[] = $arrayXml;
            $current = count($this->DatiGeneraliDocumento) - 1;

            $arrayDatiBeniServizi = $this->nodeToItaXml($xmlObj, 'DatiBeniServizi');
            if ($arrayDatiBeniServizi === false) {
                return false;
            }
            $this->DatiBeniServizi[$current] = $arrayDatiBeniServizi;

            $arrayXmlOrdineAcquisto = $this->nodeToItaXml($xmlObj, 'DatiOrdineAcquisto');
            if ($arrayXmlOrdineAcquisto === false) {
                return false;
            }
            $this->DatiOrdineAcquisto[$current] = $arrayXmlOrdineAcquisto;

            $arrayXmlContratto = $this->nodeToItaXml($xmlObj, 'DatiContratto');
            if ($arrayXmlOrdineAcquisto === false) {
                return false;
            }
            $this->DatiContratto[$current] = $arrayXmlContratto;

            $arrayXmlConvenzione = $this->nodeToItaXml($xmlObj, 'DatiConvenzione');
            if ($arrayXmlConvenzione === false) {
                return false;
            }
            $this->DatiConvenzione[$current] = $arrayXmlConvenzione;

            if ($this->ParseAllegati) {
                //Lettura Allegati
                while (true) {
                    $retStrAllegati = $this->scorriFileXml($xmlObjAllegati, "Allegati", $xmlObj->readOuterXml(), 'string', false);
                    if ($retStrAllegati === false) {
                        break; //****
                    }
                    $retXmlAllegati = $ItaXmlObjAllegati->setXmlFromString($retStrAllegati);
                    if (!$retXmlAllegati) {
                        //$this->setErrCode(-9);
                        //$this->setErrMessage("Impossibile leggere gli allegati nella fattura, pertanto non verranno visualizzati.");
                        $this->WarningMessages[] = "Impossibile leggere gli allegati nella fattura, pertanto non verranno visualizzati.";
                        $nodeReader->close();
                        return true; //oppure continua?
                    }
                    $arrayXmlAllegati = $ItaXmlObjAllegati->toArray($ItaXmlObjAllegati->asObject());
                    if (!$arrayXmlAllegati) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Lettura XML Allegati Fallita. Impossibile estrarre Allegato dai dati Fattura xml.");
                        return false;
                    }
                    $this->AllegatiDocumento[$current][] = $arrayXmlAllegati;
                }
            }
            // DatiCig: DatiContratto o DatiOrdineAcquisto
            /* @var $retNode XMLReader */
            $nodeReader = new XMLReader();
            $nodeReader->XML($xmlObj->readOuterXml(), "UTF-8"); //modificata qui
            $nodeReader = $this->seekNode($nodeReader, 'DatiContratto');
            if ($nodeReader === false) {
                $nodeReader = new XMLReader();
                $nodeReader->XML($xmlObj->readOuterXml(), "UTF-8"); //modificata qui
                $nodeReader = $this->seekNode($nodeReader, 'DatiOrdineAcquisto');
            }
            if ($nodeReader !== false) {
                $retStrDatiCig = $nodeReader->readOuterXml();
                $retXml = $ItaXmlObj->setXmlFromString($retStrDatiCig);
                if (!$retXml) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File XML . Impossibile leggere Dati Body nell'xml Fattura, Dati Contrattoo.");
                    return false;
                }
                $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
                if (!$arrayXml) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Lettura XML. Impossibile estrarre il Body dai dati Fattura xml Dati Contratto.");
                    return false;
                }
                $this->DatiCig[] = $arrayXml;
            }


            // Dati Pagamento 
            /* @var $retNode XMLReader */
            $nodeReader = new XMLReader();
            $nodeReader->XML($xmlObj->readOuterXml(), "UTF-8"); //modificata qui
            $nodeReader = $this->seekNode($nodeReader, 'DatiPagamento');
          
            if ($nodeReader !== false) {
                $retStrDatiPagamento = $nodeReader->readOuterXml();
                $retXml = $ItaXmlObj->setXmlFromString($retStrDatiPagamento);
                if (!$retXml) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File XML . Impossibile leggere Dati Body nell'xml Fattura, DatiPagamento.");
                    return false;
                }
                $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
                if (!$arrayXml) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Lettura XML. Impossibile estrarre il Body dai dati Fattura xml DatiPagamento.");
                    return false;
                }
                $this->DatiPagamento[] = $arrayXml;
                $nodeReader->close();
            }
        }

        return true;
    }

    private function seekNode($xml, $nodeName) {
        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $nodeName) {
                return $xml;
            }
        }
        return false;
    }

//**NewVFattura
    private function seekNodeFatturaElettronica($xml, $nodeName) {
        while ($xml->read()) {
            list($prenome, $nome) = explode(':', $xml->name);
            if ($xml->nodeType == XMLReader::ELEMENT && ($nome == $nodeName || $xml->name == $nodeName)) {
                return $xml;
            }
        }
        return false;
    }

    public function scorriFileXml(&$xml, $nodeName, $FileFattura, $src = 'file', $obbligatorio = true) {
        if (!$xml) {
            $xml = new XMLReader();
            if ($src == 'file') {
                if (!$xml->open($FileFattura, 'UTF-8')) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File Fattura: $FileFattura. Impossibile Aprire il file.");
                    return false;
                }
            } else {
                $xml = new XMLReader();
                if (!$xml->XML($FileFattura, "UTF-8")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile caricare la stringa XML.");
                    return false;
                }
            }
            if (!$this->seekNode($xml, $nodeName)) {
                if ($obbligatorio == true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File Fattura: $FileFattura. Impossibile Aprire l'elemento $nodeName.");
                    $xml->close();
                    return false;
                } else {
                    return false;
                }
            }
            $xmlString = $xml->readOuterXml();
            return $xmlString;
        } else {
            if (!$xml->next($nodeName)) {
                return false;
            }
            $xmlString = $xml->readOuterXml();
            return $xmlString;
        }
    }

    private function CaricaEstrattoMessaggio() {
        $Elementi = $this->GetElementiNomeFile(pathinfo($this->FilePathMessaggio, PATHINFO_BASENAME));
        $this->EstrattoMessaggio['NomeFileMessaggio'] = pathinfo($this->FilePathMessaggio, PATHINFO_BASENAME);
        $this->EstrattoMessaggio['CodUnivocoFile'] = $Elementi['CodUnivocoFile'];
        $this->EstrattoMessaggio['Tipo'] = $Elementi['TipoMessaggio'];
        $this->EstrattoMessaggio['ProgUnivoco'] = $Elementi['ProgUnivoco'];
        $this->EstrattoMessaggio['TipoMessaggio'] = self::$ElencoTipiMessaggio[$this->getTipoMessaggio()];
        foreach ($this->XmlMessaggio as $key => $ArrDati) {
            switch ($key) {
                case 'RiferimentoFattura':
                    if (isset($ArrDati[0]['NumeroFattura'][0]['@textNode'])) {
                        $this->EstrattoMessaggio['NumeroFattura'] = $ArrDati[0]['NumeroFattura'][0]['@textNode'];
                    }
                    if (isset($ArrDati[0]['AnnoFattura'][0]['@textNode'])) {
                        $this->EstrattoMessaggio['AnnoFattura'] = $ArrDati[0]['AnnoFattura'][0]['@textNode'];
                    }
                    if (isset($ArrDati[0]['PosizioneFattura'][0]['@textNode'])) {
                        $this->EstrattoMessaggio['PosizioneFattura'] = $ArrDati[0]['PosizioneFattura'][0]['@textNode'];
                    }
                    break;
                case 'Destinatario':
                    if (isset($ArrDati[0]['Codice'][0]['@textNode'])) {
                        $this->EstrattoMessaggio['DestinatarioCodice'] = $ArrDati[0]['Codice'][0]['@textNode'];
                    }
                    if (isset($ArrDati[0]['Descrizione'][0]['@textNode'])) {
                        $this->EstrattoMessaggio['DestinatarioDescrizione'] = $ArrDati[0]['Descrizione'][0]['@textNode'];
                    }
                    break;
                case 'ListaErrori':
                    //Se c'è almeno un errore
                    if (isset($ArrDati[0]['Errore'][0]['Codice'])) {
                        foreach ($ArrDati[0]['Errore'] as $k => $ArrValore) {
//                            $Codice = $ArrValore[0]['@textNode'];
//                            $DescrErrore = $ArrDati[0]['Errore'][0]['@textNode'][0]['Codice'][$k]
                            $this->EstrattoMessaggio['Errore_' . $k] = $ArrValore['Codice'][0]['@textNode'] . ' - ' . $ArrValore['Descrizione'][0]['@textNode'];
                        }
                    }
                    break;

                case 'EsitoCommittente':
                    if (isset($ArrDati[0]['Esito'][0])) {
                        foreach ($ArrDati[0] as $key_esito => $valore) {
                            if ($key_esito == '@attributes' || $key_esito == '@textNode') {
                                continue;
                            }
                            if ($key_esito == 'RiferimentoFattura') {
                                if (isset($valore[0]['NumeroFattura'])) {
                                    $this->EstrattoMessaggio['EsitoCommittente_NumeroFattura'] = $valore[0]['NumeroFattura'][0]['@textNode'];
                                }
                                if (isset($valore[0]['AnnoFattura'])) {
                                    $this->EstrattoMessaggio['EsitoCommittente_AnnoFattura'] = $valore[0]['AnnoFattura'][0]['@textNode'];
                                }
                                if (isset($valore[0]['PosizioneFattura'])) {
                                    $this->EstrattoMessaggio['EsitoCommittente_PosizioneFattura'] = $valore[0]['PosizioneFattura'][0]['@textNode'];
                                }
                            } else {
                                if (isset($valore[0]['@textNode'])) {
                                    $this->EstrattoMessaggio['EsitoCommittente_' . $key_esito] = $valore[0]['@textNode'];
                                }
                            }
                        }
                    }
                    break;
                default:
                    $this->EstrattoMessaggio[$key] = $ArrDati[0]['@textNode'];
                    break;
            }
        }
        App::log($this->EstrattoMessaggio);
    }

    //Predisporre una funzione per caricamento di più fatture..
    private function CaricaEstrattoFattura($FileFattura) {
        $retFattura = $this->GetDatiFatturaXml($FileFattura);
        if (!$retFattura) {
            return false;
        }
//        // HEADER 
        $EstrattoFattura = array();
        $Fornitore = array();
        $EstrattoFattura['FileFatturaUnivoco'] = $this->getFileFatturaUnivoco();
//        Out::msgInfo('header', print_r($this->FatturaElettronicaHeader, true));

        $Fornitore['IdCodice'] = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['IdFiscaleIVA'][0]['IdCodice'][0]['@textNode'];
        if ($this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['Anagrafica'][0]['Denominazione'][0]['@textNode']) {
            $Fornitore['Denominazione'] = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['Anagrafica'][0]['Denominazione'][0]['@textNode'];
        } else {
            $Cognome = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['Anagrafica'][0]['Cognome'][0]['@textNode'];
            $Nome = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['Anagrafica'][0]['Nome'][0]['@textNode'];
            $Fornitore['Denominazione'] = $Cognome . ' ' . $Nome;
            $Fornitore['Cognome'] = $Cognome;
            $Fornitore['Nome'] = $Nome;
        }
        // QUA IDFISCALEIVA (IDPAESE+IDCODICE), CODICE FISCALE, Codice Destinatario
        $IdPaese = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['IdFiscaleIVA'][0]['IdPaese'][0]['@textNode'];
        $Fornitore['IdFiscaleIVA'] = $IdPaese . $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['CodiceFiscale'][0]['IdCodice'][0]['@textNode'];
        if ($this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['CodiceFiscale']) {
            $Fornitore['CodiceFiscale'] = $this->FatturaElettronicaHeader['CedentePrestatore'][0]['DatiAnagrafici'][0]['CodiceFiscale'][0]['@textNode'];
        }
        $CodiceDestinatario = $this->FatturaElettronicaHeader['DatiTrasmissione'][0]['CodiceDestinatario'][0]['@textNode'];

        if (isset($this->FatturaElettronicaHeader['CedentePrestatore'][0]['Sede'][0])) {
            foreach ($this->FatturaElettronicaHeader['CedentePrestatore'][0]['Sede'][0] as $key => $valorenodo) {
                if (isset($valorenodo[0]['@textNode'])) {
                    $Fornitore[$key] = $valorenodo[0]['@textNode'];
                }
            }
        }
        $EstrattoFattura['Header']['Fornitore'] = $Fornitore;
        // BODY 
        foreach ($this->DatiGeneraliDocumento as $key => $DatoGeneraleDocumento) {
            $Body = array();
            $Body['CodiceDestinatario'] = $CodiceDestinatario;
            $Body['TipoFattura'] = $DatoGeneraleDocumento['TipoDocumento'][0]['@textNode'];
            $Body['DataFattura'] = $DatoGeneraleDocumento['Data'][0]['@textNode'];
            $Body['NumeroFattura'] = $DatoGeneraleDocumento['Numero'][0]['@textNode'];
            $Causali = $DatoGeneraleDocumento['Causale'];
            $Oggetto = '';
            foreach ($Causali as $Causale) {
                $Oggetto .= $Causale['@textNode'];
            }
            $Body['Oggetto'] = $Oggetto;

            $cig = '';
            $cig = ($cig == '') ? $this->DatiOrdineAcquisto[$key]['CodiceCIG'][0]['@textNode'] : $cig;
            $cig = ($cig == '') ? $this->DatiContratto[$key]['CodiceCIG'][0]['@textNode'] : $cig;
            $cig = ($cig == '') ? $this->DatiConvenzione[$key]['CodiceCIG'][0]['@textNode'] : $cig;
            $Body['CIG'] = $cig;

            $cup = '';
            $cup = ($cup == '') ? $this->DatiOrdineAcquisto[$key]['CodicCUP'][0]['@textNode'] : $cup;
            $cup = ($cup == '') ? $this->DatiContratto[$key]['CodiceCUP'][0]['@textNode'] : $cup;
            $cup = ($cup == '') ? $this->DatiConvenzione[$key]['CodiceCUP'][0]['@textNode'] : $cup;
            $Body['CUP'] = $cup;
            $Body['DescrizioneBeni'] = array();

            foreach ($this->DatiBeniServizi[$key]['DettaglioLinee'] as $keyBene => $DettaglioLinea) {
                $BodyDescBeni = array(
                    'NumeroLinea' => $DettaglioLinea['NumeroLinea']['0'][itaXML::textNode],
                    'Descrizione' => $DettaglioLinea['Descrizione']['0'][itaXML::textNode]
                );
                $Body['DescrizioneBeni'][$keyBene] = $BodyDescBeni;
            }

//          Dati CIG e Pagamento
//            foreach ($this->DatiCig as $key => $DatoCig) {
//                if (isset($DatoCig['CodiceCIG'])) {
//                    $Body['CIG'] = $DatoCig['CodiceCIG'][0]['@textNode'];
//                }
//            }

            foreach ($this->DatiPagamento as $key => $DatoPagamento) {
                if (isset($DatoPagamento['DettaglioPagamento']) && isset($DatoPagamento['DettaglioPagamento'][0]['ImportoPagamento'])) {
                    $Body['Importo'] = $DatoPagamento['DettaglioPagamento'][0]['ImportoPagamento'][0]['@textNode'];
                }
            }
            // Numero Allegati.
            if ($this->AllegatiDocumento[$key]) {
                $Body['Numero_Allegati'] = count($this->AllegatiDocumento[$key]);
            }
            $EstrattoFattura['Body'][] = $Body;
        }
        // Nuovo header
        $this->EstrattoFattura[] = $EstrattoFattura;
        $currKeyEstratto = count($this->EstrattoFattura) - 1;
        if ($this->AllegatiDocumento[$key]) {
            $EstrattoAllegatiFattura = $this->CaricaEstrattoAllegatiFattura($currKeyEstratto);
            if (!$EstrattoAllegatiFattura) {
                return false;
            }
            $this->EstrattoAllegatiFattura[$currKeyEstratto] = $EstrattoAllegatiFattura;
        }
        return true;

        //
    }

    private function CaricaEstrattoAllegatiFattura($KeyDocumento) {
        $AllegatiFattura = array();
        foreach ($this->AllegatiDocumento[$KeyDocumento] as $Allegato) {
            $AllegatoFattura = array();
            $AllegatoFattura['NomeAttachment'] = $Allegato['NomeAttachment'][0]['@textNode'];
            $AllegatoFattura['DescrizioneAttachment'] = isset($Allegato['DescrizioneAttachment']) ? $Allegato['DescrizioneAttachment'][0]['@textNode'] : '';
            if (isset($Allegato['FormatoAttachment'])) {
                $ext = $Allegato['FormatoAttachment'][0]['@textNode'];
            } else {
                $ext = pathinfo($AllegatoFattura['NomeAttachment'], PATHINFO_EXTENSION);
            }
            $AllegatoFattura['FormatoAttachment'] = $ext;
            $nomeFile = md5(microtime()) . '.' . $ext;
            $destFile = $this->getTempPath() . '/' . $nomeFile;
            if (!@file_put_contents($destFile, base64_decode($Allegato['Attachment'][0]['@textNode']))) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in salvataggio del file allegato.");
                return false;
            }
            $AllegatoFattura['FilePathAllegato'] = $destFile;
            $AllegatiFattura[] = $AllegatoFattura;
            $AllegatoFattura['AlgoritmoAttachment'] = isset($Allegato['AlgoritmoAttachment']) ? $Allegato['AlgoritmoAttachment'][0]['@textNode'] : '';
        }
        return $AllegatiFattura;
    }

    private function PrendiFileP7m($FileFirmato) {
        if (pathinfo($FileFirmato, PATHINFO_EXTENSION) != 'p7m') {
            $filepath = pathinfo($FileFirmato, PATHINFO_DIRNAME);
            $P7Mfile = pathinfo($FileFirmato, PATHINFO_FILENAME) . ".xml.p7m";
            if (!@copy($FileFirmato, $filepath . "/" . $P7Mfile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nel reperire il file p7m.");
                return false;
            }
            $FileFirmato = $filepath . "/" . $P7Mfile;
        }
        return $FileFirmato;
    }

    private static function createTempPath() {
        $subPath = "sdi-work-" . md5(microtime());
        $tempPath = itaLib::getAppsTempPath($subPath);
        itaLib::deleteDirRecursive($tempPath);
        $tempPath = itaLib::createAppsTempPath($subPath);
        return $tempPath;
    }

    public function cleanData() {
        return itaLib::deleteDirRecursive($this->getTempPath());
    }

    private function PrendiFileDaZip($PathZip) {
        $Files = glob($PathZip . '/*');
        foreach ($Files as $File) {
            $ext = pathinfo($File, PATHINFO_EXTENSION);
            if (strtolower($ext) == 'p7m') {
                $p7m = $this->ApriFatturaDaP7m($File);
                if (!$p7m) {
                    return false;
                }
                $basename = pathinfo($p7m->getContentFileName(), PATHINFO_BASENAME);
                if (!@copy($p7m->getContentFileName(), $this->getTempPath() . '/' . $basename)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella copia del file xml da p7m.");
                    return false;
                }
                $this->FilePathFattura[] = $this->getTempPath() . '/' . $basename;
                $p7m->cleanData();
            } else { // Altrimenti uso l'xml
                $filename = pathinfo($File, PATHINFO_FILENAME);
                if (!@copy($File, $this->getTempPath() . '/' . $filename . '.xml')) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella copia del file xml.");
                    return false;
                }
                $this->FilePathFattura[] = $this->getTempPath() . '/' . $filename . '.xml';
            }
        }
        return true;
    }

    private function AssegnaFatturaDaZip($File) {
        $PathZip = $this->ApriFatturaDaZip($File);
        if (!$PathZip) {
            return false;
        }
        $ret = $this->PrendiFileDaZip($PathZip);
        if (!$ret) {
            itaLib::deleteDirRecursive($PathZip);
            return false;
        }
        //Pulisco cartella zip
        itaLib::deleteDirRecursive($PathZip);
        $this->ZipFatturaPA = true;
        $this->FatturaPA = true;
        return true;
    }

    private function AssegnaMessaggioDaZip($File) {
        $PathZip = $this->ApriFatturaDaZip($File);
        if (!$PathZip) {
            return false;
        }
        $ret = $this->PrendiFileDaZip($PathZip);
        if (!$ret) {
            itaLib::deleteDirRecursive($PathZip);
            return false;
        }
        //Pulisco cartella zip
        itaLib::deleteDirRecursive($PathZip);
        return true;
    }

    private function AssegnaFatturaDaP7m($File) {
        $p7m = $this->ApriFatturaDaP7m($File);
        if (!$p7m) {
            return false;
        }
        if (!@copy($p7m->getContentFileName(), $this->getTempPath() . '/' . $this->NomeFileFattura . '.xml')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella copia del file xml da p7m.");
            $p7m->cleanData();
            return false;
        }
        $p7m->cleanData();
        $this->FilePathFattura[] = $this->getTempPath() . '/' . $this->NomeFileFattura . '.xml';
        $this->FatturaPA = true;
        return true;
    }

    private function AssegnaFatturaDaXml($File) {
        // Se filepath destino coincide non serve copiarlo.
        $FileTemporaneo = $this->getTempPath() . '/' . $this->NomeFileFattura . '.xml';
        if ($File != $FileTemporaneo) {
            if (!@copy($File, $FileTemporaneo)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella copia del file xml.");
                return false;
            }
        }
        $this->FilePathFattura[] = $this->getTempPath() . '/' . $this->NomeFileFattura . '.xml';
        $this->FatturaPA = true;
        return true;
    }

    private function ControllaDaFile() {
        //$this->FileDaControllare usare "Struttura"
        $this->MessaggioSdi = false;
        $this->ZipFatturaPA = false;
        $proLibAllegati = new proLibAllegati();
        // Controllo se il file che mi sta passando è Fattura o MessaggioSDI
        // Controllo prima se è un messaggio SDI
        //$basename = pathinfo($this->FileDaControllare, PATHINFO_BASENAME);
        $allegato = array('FileName' => $this->NomeFileDaControllare);
        $MatchPattern = $this->ControllaPatternAllegato($allegato);
        if ($MatchPattern) {
            //Copio il file messaggio nella cartella temporanea di lavoro sdi
            $DestinazioneFileMessaggio = $this->getTempPath() . '/' . $MatchPattern['NomeFileMessaggio'];
            // Controllo se file da controllare deriva da DOCUUID:
            if (substr($this->FileDaControllare, 0, 8) == 'DOCUUID:') {
                $ContenutoFile = $proLibAllegati->GetUUIDBinary(substr($this->FileDaControllare, 7));
                if (!$ContenutoFile) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella lettura del file ." . $proLibAllegati->getErrMessage());
                    return false;
                }
                if (!file_put_contents($DestinazioneFileMessaggio, $ContenutoFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella copia del file .");
                    return false;
                }
            } else {
                if (!@copy($this->FileDaControllare, $DestinazioneFileMessaggio)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella copia del file .");
                    return false;
                }
            }
            $this->FilePathMessaggio = $DestinazioneFileMessaggio;
            $this->setTipoMessaggio($MatchPattern['TipoMessaggio']);
            $this->setNomeFileFattura($MatchPattern['CodUnivocoFile']); //Qui Non serve..
            $this->setProgressivoUnivocoMessaggio($MatchPattern['ProgUnivoco']);
            $this->setNomeFileMessaggio($MatchPattern['NomeFileMessaggio']);
            $this->setCodUnivocoFile($MatchPattern['CodUnivocoFile']);
            $this->MessaggioSdi = true;
            // Nuovo Controllo per Messaggio AT
            if ($this->TipoMessaggio == self::TIPOMESS_AT) {
                $ext = pathinfo($this->FilePathMessaggio, PATHINFO_EXTENSION);
                if (strtolower($ext) != 'zip') {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Il file AT ricevuto deve essere un file zip.");
                    return false;
                }
                if (!$this->AssegnaMessaggioDaZip($this->FilePathMessaggio)) {
                    return false;
                }
                $this->FilePathMessaggio = $this->getTempPath() . '/' . pathinfo($this->FilePathMessaggio, PATHINFO_FILENAME) . '.xml';
            }
        }
        // Se non è un messaggio Sdi è una Fattura Elettronica:
        if (!$this->isMessaggioSdi()) {
            if (substr($this->FileDaControllare, 0, 8) == 'DOCUUID:') {
                $DestinazioneFile = $this->getTempPath() . '/' . $this->NomeFileDaControllare;
                $ContenutoFile = $proLibAllegati->GetUUIDBinary(substr($this->FileDaControllare, 7));
                if (!$ContenutoFile) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella lettura del file ." . $proLibAllegati->getErrMessage());
                    return false;
                }
                if (!file_put_contents($DestinazioneFile, $ContenutoFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nella copia del file .");
                    return false;
                }
                $this->FileDaControllare = $DestinazioneFile;
            }

            $filename = pathinfo($this->FileDaControllare, PATHINFO_FILENAME);
            $ext = pathinfo($this->FileDaControllare, PATHINFO_EXTENSION);
            switch (strtolower($ext)) {
                case 'zip':
                    $this->setNomeFileFattura($filename);
                    $this->AssegnaFatturaDaZip($this->FileDaControllare);
                    break;
                case 'p7m':
                    $this->setNomeFileFattura(pathinfo($filename, PATHINFO_FILENAME));
                    $this->AssegnaFatturaDaP7m($this->FileDaControllare);
                    break;
                case 'xml':
                    $this->setNomeFileFattura($filename);
                    $this->AssegnaFatturaDaXml($this->FileDaControllare);
                    break;
                default:
                    $this->setErrCode(-1);
                    $this->setErrMessage("Formato $ext non ammesso nella fatturazione elettronica.");
                    return false;
                    break;
            }
        }
        return true;
    }

    public function ControllaValiditaXml() {
        if ($this->isMessaggioSdi()) {
            $validitaMessagggio = $this->ControllaValiditaMessaggioSdi($this->FilePathMessaggio);
            if (!$validitaMessagggio) {
                $this->setErrCode(-1);
                $this->setErrMessage("Xml del Messaggio SDI non valido.");
                return false;
            }
        }
        if ($this->isFatturaPA()) {
            //foreach fattura un controllo validità. In teoria è sempre solo una.
            foreach ($this->FilePathFattura as $FileFattura) {
                $validitaFattura = $this->ControllaValiditaFatturaSdi($FileFattura);
                if (!$validitaFattura) {
                    $this->setErrCode(-1);
                    $basename = pathinfo($FileFattura, PATHINFO_BASENAME);
                    $this->setErrMessage("Xml del file fattura $basename non valido.");
                    return false;
                }
            }
        }
        return true;
    }

    // Possibile usare direttamente questa? Valutare..
    private function ControllaValiditaConXsd($FileXml, $FileXsd) {
        $doc = new DOMDocument();
        $doc->load($FileXml);
        return $doc->schemaValidate($FileXsd);
    }

    private function ControllaValiditaMessaggioSdi($PathMessaggio) {
        libxml_use_internal_errors(true);

        $path_xsd = ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . self::MESSAGGIOSDI_XSD;
        $doc = new DOMDocument();
        $doc->load($PathMessaggio);
        libxml_disable_entity_loader(true);
        $ret = $doc->schemaValidate(file_get_contents($path_xsd));
        if ($ret === false) {
            $errors = libxml_get_errors();
            App::log($errors);
            libxml_clear_errors();
        }
        libxml_use_internal_errors(false);
        return $ret;
    }

    private function ControllaValiditaFatturaSdi($PathFattura) {
        $path_xsd = ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . self::FATTURA_XSD;
        $doc = new DOMDocument();
        $doc->load($PathFattura);
        return $doc->schemaValidate($path_xsd);
    }

    public function GetFileXmlFattureLotti($Filexml, $ExtraParam = array()) {
        $tempPath = self::createTempPath();
        if (!$tempPath) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella creazione della cartella temporanea.');
            return false;
        }
        $xml = file_get_contents($Filexml);
        /* @var $dom DOMDocument */
        $dom = DOMDocument::loadXML($xml);
        $FileXmlFatture = array();

        $ArrBody = array();
        foreach ($dom->getElementsByTagName('FatturaElettronicaBody') as $elemento) {
            $Numero = $elemento->getElementsByTagName('Numero')->item(0);
            $NumeroFattura = $Numero->nodeValue;
            $ArrBody[$NumeroFattura] = $elemento->cloneNode(true);
        }

        foreach ($ArrBody as $key => $FattBody) {
            $templateDom = DOMDocument::loadXML($dom->saveXML());
            $templateDom->preserveWhiteSpace = false;
            $templateDom->formatOutput = true;
            $Fattura = $templateDom->getElementsByTagName('FatturaElettronica')->item(0);
            while ($Fattura->getElementsByTagName('FatturaElettronicaBody')->length > 0) {
                $Fattura->removeChild($Fattura->getElementsByTagName('FatturaElettronicaBody')->item(0));
            }
            //$newBody = $templateDom->importNode($FattBody, true);
            $templateDom->getElementsByTagName('FatturaElettronica')->item(0)->appendChild($templateDom->importNode($FattBody, true));

            $contXml = $templateDom->saveXML();
            $keyTmp = str_replace('/', '-', $key); // Soluzione migliolre? @TODO - Non servirebbe key
            $filenameLotto = $tempPath . '/' . md5(microtime()) . '_' . $keyTmp . '.xml';
            file_put_contents($filenameLotto, $contXml);
            $FileXmlFatture[$key] = $filenameLotto;
        }
        return $FileXmlFatture;
    }

    // @TODO DA VEDERE - ALLE!
    public function CheckAnomalieAllegati($parsedFile) {
        $elementi = $parsedFile['Attachments'];
        foreach ($elementi as $elemento) {
            if ($elemento['FileName'] === '') {
                $this->setErrCode(-1);
                $this->setErrMessage('Uno o più file allegati non risultano leggibili.');
                return false;
            }
        }
        return true;
    }

    public function SostituzioneTagCausale($FileFattura) {
        $contenuto = file_get_contents($FileFattura);
        $re = '/(<\/?Causale)_\d+>/';
        $subst = '$1>';
        $contenuto = preg_replace($re, $subst, $contenuto);
        file_put_contents($FileFattura, $contenuto);
    }

    private function nodeToItaXml($xmlObj, $nodeName) {
        /* @var $retNode XMLReader */
        $nodeReader = new XMLReader();
        $nodeReader->XML($xmlObj->readOuterXml(), "UTF-8"); //modificata qui
        $nodeReader = $this->seekNode($nodeReader, $nodeName);
        if ($nodeReader === false) {
            return array();
        }
        $retStrNode = $nodeReader->readOuterXml();
        $ItaXmlObj = new itaXML;
        $retXml = $ItaXmlObj->setXmlFromString($retStrNode);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML . Impossibile leggere Dati $nodeName");
            return false;
        }
        $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML. Impossibile estrarre il $nodeName.");
            return false;
        }

        return $arrayXml;
    }

}

/* Temporaneo creare un oggetto specifico esterno 
 * sbustamento e controllo da standardizzare 
 * su applicativo itaPHPVersign */

class sdiP7mRemoto {

    private $ContentFileName;
    private $tempPath;

    public static function getInstance($Filep7m) {
        include_once ITA_BASE_PATH . '/apps/RemoteSign/rsnVerifier.class.php';
        $verifier = new rsnVerifier();
        $verifier->setInputFilePath($Filep7m);
        $ret = $verifier->verify();
        if ($ret) {
            $resu = $verifier->getResult();
            $temp_path = itaLib::createAppsTempPath("rsnVerifier");
            if (!$temp_path) {
                return false;
            }
            $temp_content = $temp_path . "/" . md5(microtime());
            usleep(5);
            file_put_contents($temp_content, base64_decode($resu['binaryoutput']));
            if (!file_exists($temp_content)) {
                return false;
            }

            try {
                $obj = new sdiP7mRemoto();
            } catch (Exception $exc) {
                return false;
            }
            $obj->tempPath = $temp_path;
            $obj->ContentFileName = $temp_content;
            return $obj;
        } else {
            return false;
        }
    }

    public function getTempPath() {
        return $this->tempPath;
    }

    public function cleanData() {
        return @unlink($this->ContentFileName);
    }

    public function getContentFileName() {
        return $this->ContentFileName;
    }

    /*
     * Funzione per caricare dinamicamente la versione fattura. **NewVFattura
     */

    public function PrendiVersioneFattura($FileFattura) {
        $this->VersioneFattura = '1.1';
        $ItaXmlObj = new itaXML;
        $xml = new XMLReader();
        $xmlObj = null;
        /*
         * Controlla url xmldsig# senza # perche da problemi da attivare dopo test a corridonia
         */
        $this->ProteggiHeaderXmlFattura($FileFattura);

        $retOpen = $xml->open($FileFattura, 'UTF-8');
        if (!$retOpen) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il testo nella Fattura xml");
            return false;
        }
        /*
         * 
         * 
         * Nodo per versione fattura */
        if (!$this->seekNodeFatturaElettronica($xml, 'FatturaElettronica')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile leggere il tag FatturaElettronica nel file $FileFattura.");
            $xml->close();
            return false;
        }
        $VersioneFattura = $xml->getAttribute('versione');
        if ($VersioneFattura) {
            $this->VersioneFattura = $VersioneFattura;
        }
        $xml->close();

        return $this->VersioneFattura;
    }

}
