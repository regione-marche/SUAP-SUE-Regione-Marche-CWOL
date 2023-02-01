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
 * @version    17.10.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proInteropMsg.class.php';

class proMail extends itaModel {

    public $emlLib;
    public $proLib;
    public $proLibMail;
    public $ErrCode;
    public $ErrMessage;
    public $lastMessage;
    private $currMessage;
    private $currObjSdi;
    private $datiMail;
    private $retDecode;
    private $allegatiMail;
    private $fileSegnatura = '';
    private $tempPath;
    private $datiSegnatura = array();
    private $elementiProt = array();
    private $resultProt = array();

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

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function setLastMessage($lastMessage) {
        $this->lastMessage = $lastMessage;
    }

    public function getCurrMessage() {
        return $this->currMessage;
    }

    public function setCurrMessage($currMessage) {
        $this->currMessage = $currMessage;
    }

    public function getDatiMail() {
        return $this->datiMail;
    }

    public function setDatiMail($datiMail) {
        $this->datiMail = $datiMail;
    }

    public function getCurrObjSdi() {
        return $this->currObjSdi;
    }

    public function setCurrObjSdi($currObjSdi) {
        $this->currObjSdi = $currObjSdi;
    }

    public function getAllegatiMail() {
        return $this->allegatiMail;
    }

    public function setAllegatiMail($allegatiMail) {
        $this->allegatiMail = $allegatiMail;
    }

    public function getFileSegnatura() {
        return $this->fileSegnatura;
    }

    public function setFileSegnatura($fileSegnatura) {
        $this->fileSegnatura = $fileSegnatura;
    }

    public function getDatiSegnatura() {
        return $this->datiSegnatura;
    }

    public function setDatiSegnatura($datiSegnatura) {
        $this->datiSegnatura = $datiSegnatura;
    }

    public function getElementiProt() {
        return $this->elementiProt;
    }

    public function setElementiProt($elementiProt) {
        $this->elementiProt = $elementiProt;
    }

    public function getResultProt() {
        return $this->resultProt;
    }

    public function getRetDecode() {
        return $this->retDecode;
    }

    public function isInteroperabile() {
        if ($this->datiMail['INTEROPERABILE'] > 0) {
            return true;
        }
        return false;
    }

    public static function getInstance($idMail, $ExtraParam = array()) {
        if (!$idMail) {
            return false;
        }
        try {
            $obj = new proMail();
            $obj->emlLib = new emlLib();
            $obj->proLib = new proLib();
            $obj->proLibMail = new proLibMail();
            // Per ora contemplate solo pec con segnatura.
            if (!$obj->CaricaDatiMail($idMail)) {
                return $obj;
            }
        } catch (Exception $e) {
            return false;
        }

        return $obj;
    }

    public function GetTempPath() {
        $randPath = itaLib::getRandBaseName();
        $percorsoTmp = itaLib::getAppsTempPath("proGestMail-$randPath");
        if (!@is_dir($percorsoTmp)) {
            if (!itaLib::createAppsTempPath("proGestMail-$randPath")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }
        $this->tempPath = $percorsoTmp;
        return $this->tempPath;
    }

    public function cleanData() {
        return itaLib::deleteDirRecursive($this->tempPath);
    }

    public function CaricaDatiMail($idMail) {
        /*
         * emlLib: Lettura di Mail_Archivio
         */
        $MailArchivio_rec = $this->emlLib->getMailArchivio($idMail, 'id');
        if (!$MailArchivio_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Mail non trovata in archivio. ID: ' . $idMail);
            return false;
        }
        $this->datiMail = $MailArchivio_rec;

        /*
         * Prendo la struttura Mail:
         */
        $retDecode = $this->proLibMail->getStruttura($MailArchivio_rec['ROWID']);
        $this->currMessage = $this->proLibMail->getCurrMessage();
        $this->currObjSdi = $this->proLibMail->getCurrObjSdi();
        if (is_object($this->currMessage)) {
            $retDecode = $this->currMessage->getStruct();
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel processo di protocollazione.<br>Contattare l'assistenza tecnica.");
            return false;
        }

        $this->retDecode = $retDecode;

        /*
         * CreazioneAmbiente temporaneo.
         */
        if (!$this->GetTempPath()) {
            return false;
        }
        $percorsoTmp = $this->tempPath;
        /*
         * Controllo se è una PEC.
         */
        if ($retDecode['ita_PEC_info'] != 'N/A') {
            /*
             * Ricavo Messaggio Originale
             */
            $messaggioOriginale = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile'];
            $addressFrom = '';
            foreach ($messaggioOriginale['From'] as $address) {
                $addressFrom = $address['address'];
            }
            /*
             * Carica Allegati:
             */
            $elencoAllegati = $this->caricaElencoAllegati($retDecode);
            $elencoAllegatiOrig = $this->caricaElencoAllegati($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);

            $this->datiMail['FROMADDR'] = $addressFrom;
            $parametri = $this->proLib->getParametriPosta('pec');
            $allegati = array();
            /*
             * Se parametro attivo:
             * Aggiungo agli allegato il file Busta.
             */
            if ($parametri['Busta'] == '1') {
                switch ($retDecode['Type']) {
                    case 'text':
                        $estensione = '.txt';
                        break;
                    case 'html':
                        $estensione = '.html';
                        break;
                    default:
                        $estensione = '';
                        break;
                }
                $randName = md5(rand() * time()) . $estensione;
                @chmod($retDecode['DataFile'], 0777);
                if (!@copy($retDecode['DataFile'], $percorsoTmp . "/" . $randName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Copia della Email temporanea fallita.");
                    return false;
                }
                //$busta = array(array('DATAFILE' => 'Busta' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLPEC'));
//                $allegati = $this->unisciArray($allegati, $busta);
                $allegati[] = array('DATAFILE' => 'Busta' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLPEC');
            }
            // Verifico se c'è postacert nella PEC
            $elementoKey = $this->trovaPostaCertInAllegati($elencoAllegati);
            if ($elementoKey >= 0) {
                /*
                 *  Se lo trovo ed è attivo "MessaggioOriginale" nei parametri PEC:
                 */
                if ($parametri['MessaggioOriginale'] == '1') {
                    $parametriOri = $this->proLib->getParametriPosta();
                    /*
                     * Se nei parametri mail normale è attivo "Messaggio Originale": lo aggiungo come allegato.
                     */
                    if ($parametriOri['MessaggioOriginale'] == '1') {// $elementoKey != ''
                        $randName = md5(rand() * time()) . ".eml";
                        @chmod($elencoAllegati[$elementoKey]['FILE'], 0777);
                        if (!@copy($elencoAllegati[$elementoKey]['FILE'], $percorsoTmp . "/" . $randName)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Copia della Email temporanea fallita: Archivio File.");
                            return false;
                        }
//                        $MessaggioOriginale = array(array('DATAFILE' => 'postacert.eml', 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLORIGINALE'));
//                        $allegati = $this->unisciArray($allegati, $MessaggioOriginale);
                        $allegati[] = array('DATAFILE' => 'postacert.eml', 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLORIGINALE');
                    }
                    /*
                     * Se nei parametri mail normale è attivo "Allegati" e ci sono allegati nel messaggio originale:
                     */
                    if ($parametriOri['Allegati'] == '1' && count($elencoAllegatiOrig) > 0) {
                        $allegatiTmp = array();
                        foreach ($elencoAllegatiOrig as $allegatoOrig) {
                            $randName = md5(rand() * time()) . "." . pathinfo($allegatoOrig['DATAFILE'], PATHINFO_EXTENSION);
                            @chmod($allegatoOrig['FILE'], 0777);
                            if (!@copy($allegatoOrig['FILE'], $percorsoTmp . "/" . $randName)) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Copia della Email temporanea fallita. Archivio allegati da mail originale.");
                                return false;
                            }
                            $allegatiTmp[] = array('DATAFILE' => $allegatoOrig['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                        }
                        $allegati = $this->unisciArray($allegati, $allegatiTmp);
                    }
                    /*
                     *  Se nei parametri mail normale è attivo "Corpo" allora salvo il corpo come messaggio originale (se c'è).
                     */
                    if ($parametriOri['Corpo'] == '1') {
                        if (isset($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['DataFile'])) {
                            $messOrigPath = $retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['DataFile'];
                            switch ($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']['Type']) {
                                case 'text':
                                    $estensione = '.txt';
                                    break;
                                case 'html':
                                    $estensione = '.html';
                                    break;
                                default:
                                    $estensione = '.html'; //Pensare ad un default.
                                    break;
                            }
                            $randName = md5(rand() * time()) . $estensione;
                            @chmod($messOrigPath, 0777);
                            if (!@copy($messOrigPath, $percorsoTmp . "/" . $randName)) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Copia della Email temporanea fallita. Archivio file Corpo mail.");
                                return false;
                            }
//                            $messaggioOriginale = array(array('DATAFILE' => 'MessaggioOriginale' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'CORPOEML')); //@TODO NON é CORPOPEC...?
//                            $allegati = $this->unisciArray($allegati, $messaggioOriginale);
                            $allegati[] = array('DATAFILE' => 'MessaggioOriginale' . $estensione, 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'CORPOEML');
                        }
                    }
                }
            }
            /*
             * Se da parametri pec ho attivo "Allegati":
             */
            if ($parametri['Allegati'] == '1') {
                $elencoAllegTmp = $elencoAllegati;
                /*
                 *  Se tramite "trovaPostaCertInAllegati" ho trovato la "postacert",
                 *   lo rimuovo dall'elenco allegati.
                 */
                if ($elementoKey !== false) {
                    unset($elencoAllegTmp[$elementoKey]);
                }
                $allegatiTmp = array();
                /*
                 *  Scorro ogni allegato della PEC e salvo.
                 *   Probabilmente è sempre e solo: daticert.xml
                 */
                foreach ($elencoAllegTmp as $allegTmp) {
                    $randName = md5(rand() * time()) . "." . pathinfo($allegTmp['DATAFILE'], PATHINFO_EXTENSION);
                    @chmod($allegTmp['FILE'], 0777);
                    if (!@copy($allegTmp['FILE'], $percorsoTmp . "/" . $randName)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia della Email temporanea fallita. Archiviazione daticert.");
                        return false;
                    }
                    $allegatiTmp[] = array('DATAFILE' => $allegTmp['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'DATICERTPEC');
                }
                /*
                 *  Se è presente la segnatura, la salvo come allegato normale.
                 */
                if ($retDecode['Signature']) {
                    $randName = md5(rand() * time()) . "." . pathinfo($retDecode['Signature']['FileName'], PATHINFO_EXTENSION);
                    @chmod($retDecode['Signature']['DataFile'], 0777);
                    if (!@copy($retDecode['Signature']['DataFile'], $percorsoTmp . "/" . $randName)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia della Email temporanea fallita. Archiviazione Segnatura.");
                        return false;
                    }
                    $allegatiTmp[] = array('DATAFILE' => $retDecode['Signature']['FileName'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                }
                $allegati = $this->unisciArray($allegati, $allegatiTmp);
            }
            /*
             * Assegno Variabili
             */
            foreach ($allegati as $allegato) {
                if (strtolower($allegato['DATAFILE']) == 'segnatura.xml') {
                    $this->fileSegnatura = $allegato['FILE'];
                }
            }
            $this->allegatiMail = $allegati;
        } else {
            /*
             * Caricamento Mail Normale
             */
            $parametri = $this->proLib->getParametriPosta();
            $elencoAllegati = $this->caricaElencoAllegati($retDecode);

            $allegati = array();
            // Se nei parametri normali mail è attivo "Messaggio Originale", allora salvo l'eml.
            if ($parametri['MessaggioOriginale'] == '1') {
                $emailOriginale = $this->currMessage->getEmlFile();
                $randName = md5(rand() * time()) . ".eml";
                @chmod($emailOriginale, 0777);
                if (!@copy($emailOriginale, $percorsoTmp . "/" . $randName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Copia della Email temporanea fallita. Archiviazione Mail Originale Normale");
                    return false;
                }
                $messaggio = array(array('DATAFILE' => 'MessaggioOriginale.eml', 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'EMLORIGINALE'));
                $allegati = $this->unisciArray($allegati, $messaggio);
            }
            // Se nei parametri normali è attivo "Allegati", li scorro e li salvo.
            if ($parametri['Allegati'] == '1' && count($elencoAllegati) > 0) {
                $allegatiTmp = array();
                foreach ($elencoAllegati as $allegTmp) {
                    $randName = md5(rand() * time()) . "." . pathinfo($allegTmp['DATAFILE'], PATHINFO_EXTENSION);
                    @chmod($allegTmp['FILE'], 0777);
                    if (!@copy($allegTmp['FILE'], $percorsoTmp . "/" . $randName)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia della Email temporanea fallita. Archiviazione Allegati mail normale.");
                        break;
                    }
                    $allegatiTmp[] = array('DATAFILE' => $allegTmp['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                }

                $allegati = $this->unisciArray($allegati, $allegatiTmp);
            }
            //
            // Se nei parametri normali è attivo "Corpo" salvo anche il corpo della mail.
            //
            if ($parametri['Corpo'] == '1') {
                $messOrigPath = $retDecode['DataFile'];
                switch ($retDecode['Type']) {
                    case 'text':
                        $estensione = '.txt';
                        break;
                    case 'html':
                        $estensione = '.html';
                        break;
                    default:
                        $estensione = '';
                        break;
                }
                $randName = md5(rand() * time()) . $estensione;
                @chmod($messOrigPath, 0777);
                if (!@copy($messOrigPath, $percorsoTmp . "/" . $randName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Copia della Email temporanea fallita. Archiviazione Corpo Mail Normale");
                    return false;
                }
                $messaggioOriginale = array(array('DATAFILE' => 'Corpo' . $estensione, 'FILE' => $percorsoTmp . '/' . $randName, 'DATATIPO' => 'CORPOEML'));
                $allegati = $this->unisciArray($allegati, $messaggioOriginale);
            }
            foreach ($allegati as $allegato) {
                if (strtolower($allegato['DATAFILE']) == 'segnatura.xml') {
                    $this->fileSegnatura = $allegato['FILE'];
                }
            }
            $this->allegatiMail = $allegati;
        }
        return true;
    }

    private function trovaPostaCertInAllegati($elencoAllegati) {
        $elementoKey = null;
        foreach ($elencoAllegati as $key => $value) {
            if ($value['DATAFILE'] == 'postacert.eml') {
                $elementoKey = $key;
            }
        }
        return $elementoKey;
    }

    /*
     * Sarebbe array merge..
     */

    private function unisciArray($primario, $secondario) {
        foreach ($secondario as $value) {
            $primario[] = $value;
        }
        return $primario;
    }

    private function caricaElencoAllegati($retDecode) {
        $allegati = array();
        $elementi = $retDecode['Attachments'];
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                /*
                 * 24/03/2016 Possibile work qruon per errore su filename da risolvere alla fonte
                 */
                if ($elemento['FileName'] === '') {
                    $elemento['FileName'] = 'Allegato_' . md5(microtime());
                    usleep(10);
                }
                if ($elemento['FileName']) {
                    $vsign = "";
                    $icon = utiIcons::getExtensionIconClass($elemento['FileName'], 32);
                    $sizefile = $this->emlLib->formatFileSize(filesize($elemento['DataFile']));
                    $ext = pathinfo($elemento['FileName'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == "p7m") {
                        $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                    }

                    $allegati[] = array(
                        'ROWID' => $incr,
                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile'],
                        'FileSize' => $sizefile,
                        'VSIGN' => $vsign
                    );
                    $incr++;
                }
            }
        }
        return $allegati;
    }

    private function CaricaElementiDaMail() {
        /*
         * Assegno Dati Segnatura:
         * !!!!!!!!  Per ora obbligatoria. !!!!!!!!!
         * Assegnazione potrebbe essere fatta in un altro momento.
         */

        if ($this->datiMail['TIPOINTEROPERABILE'] != proInteropMsg::TIPOMSG_SEGNATURA) {
            return true;
        }


        if (!$this->fileSegnatura) {
            return false;
        }

        if (!$this->assegnaDatiXml($this->fileSegnatura)) {
            return false;
        }
        /**
         * Assegno meta-elementi per oggetto dati protocollo
         */
        $this->elementiProt = array();
        /**
         * Dati principali
         */
        $this->elementiProt['tipo'] = 'A';
        $this->elementiProt['dati'] = array();
        $this->elementiProt['dati']['TipoDocumento'] = '';
        $this->elementiProt['dati']['Oggetto'] = $this->datiMail['SUBJECT']; // Può essere letto dalla segnatura.
        $this->elementiProt['dati']['NumeroAntecedente'] = '';
        $this->elementiProt['dati']['AnnoAntecedente'] = '';
        $this->elementiProt['dati']['TipoAntecedente'] = '';
        $this->elementiProt['dati']['ProtocolloMittente']['Numero'] = $this->datiSegnatura['PROT_MITTENTE'];
        $this->elementiProt['dati']['ProtocolloMittente']['Data'] = $this->datiSegnatura['DATAPROT_MITTENTE'];
        $this->elementiProt['dati']['DataArrivo'] = substr($this->datiMail['MSGDATE'], 0, 8);
        $this->elementiProt['dati']['ProtocolloEmergenza'] = '';
        $this->elementiProt['dati']['Prostatoprot'] = prolib::PROSTATO_INCOMPLETO;
        $this->elementiProt['rowidMail'] = $this->datiMail['ROWID'];
        $this->elementiProt['ProIdMail'] = $this->datiMail['IDMAIL'];
        /*
         * Dati Mittente:
         */
        $this->elementiProt['dati']['Mittenti']['Email'] = $this->datiMail['FROMADDR'];
        if ($this->datiSegnatura) {
            $this->elementiProt['dati']['Mittenti']['CodiceMittDest'] = '';
            $this->elementiProt['dati']['Mittenti']['Denominazione'] = $this->datiSegnatura['MITTENTE']['DENOMINAZIONE'];
            $this->elementiProt['dati']['Mittenti']['Indirizzo'] = $this->datiSegnatura['MITTENTE']['INDIRIZZO'];
            $this->elementiProt['dati']['Mittenti']['CAP'] = $this->datiSegnatura['MITTENTE']['CAP'];
            $this->elementiProt['dati']['Mittenti']['Citta'] = $this->datiSegnatura['MITTENTE']['CITTA'];
            $this->elementiProt['dati']['Mittenti']['Provincia'] = $this->datiSegnatura['MITTENTE']['PROVINCIA'];
        } else {
            $sql = "SELECT * FROM ANAMED WHERE MEDEMA='" . $this->datiMail['FROMADDR'] . "' AND MEDANN=0";
            $anamed_rec = $this->proLib->getGenericTab($sql, false);
            if ($anamed_rec) {
                $this->elementiProt['dati']['Mittenti']['CodiceMittDest'] = $anamed_rec['MEDCOD'];
                $this->elementiProt['dati']['Mittenti']['Denominazione'] = $anamed_rec['MEDNOM'];
                $this->elementiProt['dati']['Mittenti']['Indirizzo'] = $anamed_rec['MEDIND'];
                $this->elementiProt['dati']['Mittenti']['CAP'] = $anamed_rec['MEDCAP'];
                $this->elementiProt['dati']['Mittenti']['Citta'] = $anamed_rec['MEDCIT'];
                $this->elementiProt['dati']['Mittenti']['Provincia'] = $anamed_rec['MEDPRO'];
                $this->elementiProt['dati']['Mittenti']['Email'] = $anamed_rec['MEDEMA'];
            }
        }

        /*
         * Titolario da ricavare in qualche altro modo?
         */
        $Titolario = '';
        $anaent_54 = $this->proLib->GetAnaent('54');
        if ($anaent_54['ENTDE1']) {
            $Titolario = $anaent_54['ENTDE1'];
            if ($anaent_54['ENTDE2']) {
                $Titolario .= '.' . $anaent_54['ENTDE2'];
                if ($anaent_54['ENTDE3']) {
                    $Titolario .= '.' . $anaent_54['ENTDE3'];
                }
            }
        }
        $this->elementiProt['dati']['Classificazione'] = $Titolario;
        /**
         * Dati Spedizione/tipo posta
         */
        $this->elementiProt['dati']['TipoSpedizione'] = 'PEC';
        $this->elementiProt['dati']['Spedizioni']['TipoSpedizione'] = '';
        $this->elementiProt['dati']['Spedizioni']['NumeroRaccomandata'] = '';
        $this->elementiProt['dati']['Spedizioni']['Grammi'] = '';
        $this->elementiProt['dati']['Spedizioni']['Quantita'] = '';
        $this->elementiProt['dati']['Spedizioni']['DataSpedizione'] = '';

        /*
         * Trasmissioni interne dall'oggetto.
         * PER ORA NON PRESENTI..
         */
        $trasmissioni = array();
        foreach ($trasmissioni as $key => $trasmissione) {
            $anamed_rec = $this->proLib->GetAnamed($trasmissione['codiceDestinatario']);
            if (!$anamed_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Codice " . $trasmissione['codiceDestinatario'] . " non trovato nell'anagrafica dei mittenti.");
                return false;
            }
            $this->elementiProt['destinatari'][$key]['CodiceDestinatario'] = $trasmissione['codiceDestinatario'];
            $this->elementiProt['destinatari'][$key]['Denominazione'] = $anamed_rec['MEDNOM'];
            $this->elementiProt['destinatari'][$key]['Indirizzo'] = $anamed_rec['MEDIND'];
            $this->elementiProt['destinatari'][$key]['CAP'] = $anamed_rec['MEDCAP'];
            $this->elementiProt['destinatari'][$key]['Citta'] = $anamed_rec['MEDCIT'];
            $this->elementiProt['destinatari'][$key]['Provincia'] = $anamed_rec['MEDPRO'];
            $this->elementiProt['destinatari'][$key]['Annotazioni'] = $trasmissione['oggettoTrasmissione'];
            $this->elementiProt['destinatari'][$key]['Email'] = $anamed_rec['MEDEMA'];
            $this->elementiProt['destinatari'][$key]['Ufficio'] = $trasmissione['codiceUfficio'];
            $this->elementiProt['destinatari'][$key]['Responsabile'] = $trasmissione['responsabile'];
            $this->elementiProt['destinatari'][$key]['Gestione'] = $trasmissione['gestione'];
        }

        /*
         * Caricamento Allegati.
         */

        $keyPrincipale = self::GetKeyAllegatoPrincipale($this->allegatiMail);
        $AltriAllegati = $this->allegatiMail;
        if ($keyPrincipale !== null) {
            $allegatoPrincipale = $this->allegatiMail[$keyPrincipale];
            unset($AltriAllegati[$keyPrincipale]);
        } else {
            $allegatoPrincipale = $this->allegatiMail[0];
            unset($AltriAllegati[0]);
        }

        /*
         * allegato
         */
        $FileAllegato = $allegatoPrincipale['FILE'];

        $fh = fopen($FileAllegato, 'rb');
        if (!$fh) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
            return false;
        }
        $binary = fread($fh, filesize($FileAllegato));
        fclose($fh);
        $binary = base64_encode($binary);
        $NomeFile = $allegatoPrincipale['DATAFILE'];

        $this->elementiProt['allegati']['Principale'] = array();
        $this->elementiProt['allegati']['Principale']['Nome'] = $NomeFile;
        $this->elementiProt['allegati']['Principale']['Stream'] = $binary;
        $this->elementiProt['allegati']['Principale']['Descrizione'] = $NomeFile;
        $this->elementiProt['allegati']['Principale']['DocIDMail'] = $this->datiMail['IDMAIL'];
        $binary = '';

        /*
         * Controllo se ci sono altri allegati:
         */
        if ($AltriAllegati) {
            foreach ($AltriAllegati as $AllegatoMail) {
                $fh = fopen($AllegatoMail['FILE'], 'rb');
                if (!$fh) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
                    return false;
                }
                $binary = fread($fh, filesize($AllegatoMail['FILE']));
                fclose($fh);
                $binary = base64_encode($binary);
                $elementoAllegato = array();
                $elementoAllegato['Documento']['Nome'] = $AllegatoMail['DATAFILE'];
                $elementoAllegato['Documento']['Stream'] = $binary;
                $elementoAllegato['Documento']['Descrizione'] = $AllegatoMail['DATAFILE'];
                $elementoAllegato['Documento']['DocIDMail'] = $this->datiMail['IDMAIL'];
                $this->elementiProt['allegati']['Allegati'][] = $elementoAllegato;
            }
        }
        /*
         * Ricordarsi "SEGNATURA AUTOMATICA MAIL PDF".
         * Se attivo Prametro e se ext. PDF.
         */
        return true;
    }

    private function assegnaDatiXml($file) {
        // @TODO devo copiare? Copio il file in segnatura.
        if (!@copy($file, $this->tempPath . "/Segnatura.xml")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia della Email temporanea fallita. Copia Allegato Inoltro.");
            return false;
        }
        $InteropMsg = proInteropMsg::getInteropInstanceEntrata($this->tempPath . "/Segnatura.xml");
        if ($InteropMsg->getErrCode() == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in lettura File Interoperabile: " . $InteropMsg->getErrMessage());
            return false;
        }

        $this->datiSegnatura = $InteropMsg->getDatiSegnatura();
        if (!$this->datiSegnatura) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in Lettura dati Segnatura: " . $InteropMsg->getErrMessage());
            return false;
        }

        return true;
    }

    private function leggiXml($file) {
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($file);
        $arrayXml = $xmlObj->getArray();
        return $arrayXml;
    }

    public function ControlliPermessiProt() {
        $profilo = proSoggetto::getProfileFromIdUtente();
        if (!$profilo) {
            return false;
        }

        /**
         * Ufficio Operatore di protocollazione
         *
         */
        $codiceOperatore = proSoggetto::getCodiceSoggettoFromIdUtente();
        if (!$codiceOperatore) {
            $this->setErrCode(-1);
            $this->setErrMessage("Utente senza profilo protocollazione.");
            return false;
        }

        /*
         * Controllo permessi di scrittura:
         */
        //@TODO: Centralizzare proLibAllegati
        $AnnoCheck = date('Y');
        if (!$this->proLib->CheckDirectory($AnnoCheck . '000000', 'A')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non si dispone dei permessi necessari di scrittura nella cartella di protocollazione.");
            return false;
        }
        return true;
    }

    public function ProtocollaMail() {
        if (!$this->ControlliPermessiProt()) {
            return false;
        }
        /**
         * Istanza Oggetto ProtoDatiProtocollo
         */
        /*
         * Solo se interoperabile:
         * - Provo caricamento PEC
         * - Protocollazione PEC
         */
        if ($this->isInteroperabile()) {
            if (!$this->CaricaElementiDaMail()) {
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("tentativo di protocollazione mail non interoperabile");
            return false;
        }

        include_once ITA_BASE_PATH . '/apps/Protocollo/proDatiProtocollo.class.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($this->elementiProt);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Forzo i dati del protocollo come incompleti
         */
        $proDatiProtocollo->setStatoProtIncompleto();

        /**
         * Attiva Controlli su proDatiProtocollo
         */
        if (!$proDatiProtocollo->controllaDati()) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Lancia il protocollatore con i dati impostati
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proProtocolla.class.php';
        $proProtocolla = new proProtocolla();
        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
        if ($ret_id === false) {
            $this->resultProt['ANAPRO_CREATO'] = $proProtocolla->getAnaproCreato();
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');

        $msgCtrMarc = 'Controlli marcatura allegati effettuati correttamente.';
        if (!$proProtocolla->CtrMarcaturaAllegati($Anapro_rec)) {
            $msgCtrMarc = 'Errore nella marcatura allegati. ' . $proProtocolla->getmessage();
        }

        $this->resultProt = array();
        $this->resultProt['rowidProtocollo'] = $Anapro_rec['ROWID'];
        $this->resultProt['annoProtocollo'] = substr($Anapro_rec['PRONUM'], 0, 4);
        $this->resultProt['numeroProtocollo'] = substr($Anapro_rec['PRONUM'], 4);
        $this->resultProt['tipoProtocollo'] = $Anapro_rec['PROPAR'];
        $this->resultProt['dataProtocollo'] = $Anapro_rec['PRODAR'];
        $this->resultProt['segnatura'] = $Anapro_rec['PROSEG'];
        $this->resultProt['marcatura_allegati'] = $msgCtrMarc;
        return $this->resultProt;
    }

    public function setClassMailProtocollo($class = '@PROTOCOLLATO@') {
        /*
         * Setto Mail protocollata:
         */
        $datiMail = $this->getDatiMail();
        $emlDbMailBox = new emlDbMailBox();
        $risultato = $emlDbMailBox->updateClassForRowId($datiMail['ROWID'], $class);
        if ($risultato === false) {
            $this->setLastMessage('Classe mail non aggiornata. ' . $emlDbMailBox->getLastMessage());
            return false;
        }
        return true;
    }

    public function AnalizzaMailInoltro() {

        /*
         * Una volta analizzato il messaggio:
         * Verifico se è una mail Parametrizzata nell'elenco delle mail 
         * da Protocollare da Inoltro Mail.
         * ?? Serve controllo solo mail Normali e esclude PEC ??
         */
        $anaent_52 = $this->proLib->GetAnaent('52');
        $ElencoMailInoltro = unserialize($anaent_52['ENTVAL']);
        $percorsoTmp = $this->tempPath;
        $parametri = $this->proLib->getParametriPosta('pec');

        if ($ElencoMailInoltro) {
            $MailInoltro_rec = array();
            // Valorizzo array per controllo mail in elenco.
            foreach ($ElencoMailInoltro as $Mail) {
                $MailInoltro_rec[] = $Mail['EMAIL'];
            }
            // Se è in elenco, mail speciale da inoltro.
            $FileEmlAllegato = '';
            if (in_array($this->datiMail['ACCOUNT'], $MailInoltro_rec)) {
                // Cerco il file eml allegato:
                foreach ($this->allegatiMail as $allegato) {
                    $ext = strtolower(pathinfo($allegato['DATAFILE'], PATHINFO_EXTENSION));
                    if ($ext == 'eml') {
                        $FileEmlAllegato = $allegato['FILE'];
                        break;
                    }
                }
                // Controllo se non ho trovato il file eml da inoltro.
                if (!$FileEmlAllegato) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Mail da protocollatre da Inoltro, ma file eml non presente.');
                    return false;
                }
                // Analisi del messaggio locale
                $InoltroMessage = new emlMessage();
                $InoltroMessage->setEmlFile($FileEmlAllegato);
                $InoltroMessage->parseEmlFileDeep();
                $retDecodeInoltro = $InoltroMessage->getStruct();
                /*
                 * Scambio i dati interni di protocollazione, oggetto e mittente
                 */
                $this->datiMail['SUBJECT'] = $retDecodeInoltro['Subject'];
                $this->datiMail['FROMADDR'] = $retDecodeInoltro['FromAddress'];
                $decodedDate = emlDate::eDate2Date($retDecodeInoltro['Date']);
                $this->datiMail['MSGDATE'] = $decodedDate['date'] . " " . $decodedDate['time'];
                $allegati = array();
                /*
                 * Corpo
                 */
                $allegatiTmpInoltro = array();
                $elencoAllegatiInoltro = $this->caricaElencoAllegati($retDecodeInoltro);
                if ($parametri['Allegati'] == '1' && count($elencoAllegatiInoltro) > 0) {
                    $allegatiTmpInoltro = array();
                    foreach ($elencoAllegatiInoltro as $allegTmpInoltro) {
                        $randName = md5(rand() * time()) . "." . pathinfo($allegTmpInoltro['DATAFILE'], PATHINFO_EXTENSION);
                        @chmod($allegTmpInoltro['FILE'], 0777);
                        if (!@copy($allegTmpInoltro['FILE'], $percorsoTmp . "/" . $randName)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Copia della Email temporanea fallita. Copia Allegato Inoltro.");
                            return false;
                        }
                        $allegatiTmpInoltro[] = array('DATAFILE' => $allegTmpInoltro['DATAFILE'], 'FILE' => $percorsoTmp . "/" . $randName, 'DATATIPO' => 'ALLEGATO');
                    }

                    $allegati = $this->unisciArray($allegati, $allegatiTmpInoltro);
                }

                if ($parametri['Corpo'] == '1') {
                    $messOrigPathInoltro = $retDecodeInoltro['DataFile'];
                    switch ($retDecodeInoltro['Type']) {
                        case 'text':
                            $estensione = '.txt';
                            break;
                        case 'html':
                            $estensione = '.html';
                            break;
                        default:
                            $estensione = '';
                            break;
                    }
                    $randName = md5(rand() * time()) . $estensione;
                    @chmod($messOrigPath, 0777);
                    if (!@copy($messOrigPathInoltro, $percorsoTmp . "/" . $randName)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia della Email temporanea fallita. Corpo Mail Inoltro.");
                        return false;
                    }
                    $messaggioOriginaleInoltro = array(array('DATAFILE' => 'Corpo' . $estensione, 'FILE' => $percorsoTmp . '/' . $randName, 'DATATIPO' => 'CORPOEML'));
                    $allegati = $this->unisciArray($allegati, $messaggioOriginaleInoltro);
                }

                $this->allegatiMail = $allegati;
            }
        }
        return true;
    }

    public static function GetKeyAllegatoPrincipale($allegatiMail) {
        $keyPrincipale = null;
        while (true) {
            foreach ($allegatiMail as $key => $elemento) {
                if ($elemento['DATATIPO'] == 'EMLPEC') {
                    $keyPrincipale = $key;
                    break;
                }
            }
            if ($keyPrincipale !== null) {
                break;
            }
            /*
             * Cerca EMLORIGINALE
             */
            foreach ($allegatiMail as $key => $elemento) {
                if ($elemento['DATATIPO'] == 'EMLORIGINALE') {
                    $keyPrincipale = $key;
                    break;
                }
            }
            if ($keyPrincipale !== null) {
                break;
            }

            /*
             * Cerca ALLEGATO
             */
            foreach ($allegatiMail as $key => $elemento) {
                if ($elemento['DATATIPO'] == 'ALLEGATO') {
                    $keyPrincipale = $key;
                    break;
                }
            }
            if ($keyPrincipale !== null) {
                break;
            }
            /*
             * Cerca CORPOEML
             */
            foreach ($allegatiMail as $key => $elemento) {
                if ($elemento['DATATIPO'] == 'CORPOEML') {
                    $keyPrincipale = $key;
                    break;
                }
            }
            break;
        }

        return $keyPrincipale;
    }

}

?>
