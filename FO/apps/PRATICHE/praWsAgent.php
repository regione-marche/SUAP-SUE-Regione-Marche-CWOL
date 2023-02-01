<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    20.06.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php');
require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDati.class.php');
require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibMail.class.php');
require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibProtocolla.class.php');

class praWsAgent {

    public $PRAM_DB;
    public $PRAM_DB_R;
    public $ITW_DB;
    public $praLib;
    public $praLibDati;
    public $errCode;
    public $errMessage;

    function __construct() {
        try {
            $this->praLib = new praLib();
            $this->praLibDati = praLibDati::getInstance($this->praLib);
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->PRAM_DB_R = $this->praLib->GetPramMaster($this->PRAM_DB);
            $this->ITW_DB = ItaDB::DBOpen('ITW', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        
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

    public function InsertDocumentoRichiesta($nomeFile, $stream, $impronta = false) {
        /*
         * Verifico i parametri
         */

        if (!$nomeFile) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nome file obbligatorio.');
            return false;
        }

        /*
         * Controllo Caratteri Speciali Windows
         */

        $match = preg_match('/[*\\\\:<>?|"]/', $nomeFile, $retMatch = null);
        if ($match) {
            $this->setErrCode(-1);
            $this->setErrMessage('Carattere speciale non accettato nel nome del file: "' . $retMatch[0] . '".');
            return false;
        }

        /*
         * Verifico l'impronta
         */

        if ($impronta) {
            $sha256 = hash('sha256', base64_decode($stream));
            if ($impronta != $sha256) {
                $this->setErrCode(-1);
                $this->setErrMessage('Il contenuto del file non corrisponde con l\'impronta.');
                return false;
            }
        }

        /*
         * Salvo lo stream del file su una cartella temporanea
         */

        $praLibAllegati = praLibAllegati::getInstance($this->praLib);
        $ext = $praLibAllegati->GetExtP7MFile($nomeFile);

        $idUnivocoFile = date('Ymd') . '_' . md5(uniqid(microtime(true))) . '.' . $ext;

        if (!is_dir(ITA_FRONTOFFICE_TEMP)) {
            if (!mkdir(ITA_FRONTOFFICE_TEMP, 0755, true)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore nella creazione della path temporanea "' . ITA_FRONTOFFICE_TEMP . '".');
                return false;
            }
        }

        $filePathDest = rtrim(ITA_FRONTOFFICE_TEMP, '/') . '/' . $idUnivocoFile;

        if (file_put_contents($filePathDest, base64_decode($stream)) === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Scrittura del file "' . $filePathDest . '" non riuscita.');
            return false;
        }

        $retDocumento = array();
        $retDocumento['id'] = $idUnivocoFile;
        $retDocumento['hash'] = hash_file('sha256', $filePathDest);
        return $retDocumento;
    }

    public function PutRichiesta($datiRichiesta, $datiAggiuntivi, $allegati, $codiceUtente) {
        /*
         * Verifica dei dati obbligatori
         */

        if (!$datiRichiesta['codiceProcedimento']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Dato richiesta "codiceProcedimento" obbligatorio.');
            return false;
        }

        if (!$datiRichiesta['esibente'] || !$datiRichiesta['esibente']['codiceFiscale'] || !$datiRichiesta['esibente']['nome'] || !$datiRichiesta['esibente']['cognome']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Dato richiesta "esibente" obbligatorio.');
            return false;
        }

        /*
         * Correzione degli array
         */

        if (!$datiAggiuntivi) {
            $datiAggiuntivi = array();
        } elseif (!isset($datiAggiuntivi[0])) {
            $datiAggiuntivi = array($datiAggiuntivi);
        }

        if (!$allegati) {
            $allegati = array();
        } elseif (!isset($allegati[0])) {
            $allegati = array($allegati);
        }

        /*
         * Prima di fare gli inserimenti verifico gli allegati
         */

        $tempPathAllegati = rtrim(ITA_FRONTOFFICE_TEMP, '/');

        foreach ($allegati as $allegato) {
            $filePathUpload = $tempPathAllegati . '/' . $allegato['id'];

            if (!file_exists($filePathUpload)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Allegato $filePathUpload '{$allegato['id']}' non presente.");
                return false;
            }

            if ($allegato['hash'] !== hash_file('sha256', $filePathUpload)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Verifica SHA2 allegato '{$allegato['id']}' fallita.");
                return false;
            }
        }

        /*
         * Verifica procedimento/evento/sportello
         */

        $codiceProcedimento = str_pad($datiRichiesta['codiceProcedimento'], 6, '0', STR_PAD_LEFT);

        $anapra_rec = $this->praLib->GetAnapra($codiceProcedimento, 'codice', $this->PRAM_DB);
        if (!$anapra_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Procedimento '$codiceProcedimento' inesistente.");
            return false;
        }

        if ($datiRichiesta['codiceSportello'] && $datiRichiesta['codiceEvento']) {
            /*
             * Se sono valorizzati i codici evento e sportello, cerco il record
             * ITEEVT per quella combinazione.
             */

            $codiceEvento = str_pad($datiRichiesta['codiceEvento'], 6, '0', STR_PAD_LEFT);

            $anaeventi_rec = $this->praLib->GetAnaeventi($codiceEvento, 'codice', $this->PRAM_DB);
            if (!$anaeventi_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Evento '$codiceEvento' inesistente.");
                return false;
            }

            $anatsp = $this->praLib->GetAnatsp($datiRichiesta['codiceSportello'], 'codice', $this->PRAM_DB);
            if (!$anatsp) {
                $this->setErrCode(-1);
                $this->setErrMessage("Sportello '{$datiRichiesta['codiceSportello']}' inesistente.");
                return false;
            }

            $sql_iteevt_rec = sprintf('SELECT
                                       IEVTIP, IEVSTT, IEVATT, IEVDESCR
                                   FROM ITEEVT
                                   WHERE
                                       ITEPRA = "%1$s" AND
                                       IEVCOD = "%2$s" AND
                                       IEVTSP = "%3$s" AND
                                       ( IEVDVA = "" OR IEVDVA < "%4$s" ) AND
                                       ( IEVAVA = "" OR IEVAVA > "%4$s" )', $codiceProcedimento, $codiceEvento, $datiRichiesta['codiceSportello'], date('Ymd'));

            $iteevt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_iteevt_rec, false);
            if (!$iteevt_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Procedimento '$codiceProcedimento' per evento '$codiceEvento' e sportello '{$datiRichiesta['codiceSportello']}' inesistente.");
                return false;
            }
        } else {
            /*
             * Se non sono presenti i codici evento e sportello, verifico
             * i record su ITEEVT. Se ne è presente soltanto uno, lo prendo
             * automaticamente.
             */

            $sql_iteevt_rec = sprintf('SELECT
                                           IEVTIP, IEVSTT, IEVATT, IEVDESCR
                                       FROM ITEEVT
                                       WHERE
                                           ITEPRA = "%1$s" AND
                                           ( IEVDVA = "" OR IEVDVA < "%2$s" ) AND
                                           ( IEVAVA = "" OR IEVAVA > "%2$s" )', $codiceProcedimento, date('Ymd'));

            $iteevt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_iteevt_rec, false);
            if (!$iteevt_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Dati richiesta "codiceSportello" e "codiceEvento" mancanti.');
                return false;
            }
        }

        /*
         * Verifica sportello aggregato
         */

        $codiceAggregato = false;

        if ($datiRichiesta['codiceAggregato']) {
            $anaspa_rec = $this->praLib->GetAnaspa($datiRichiesta['codiceAggregato'], 'codice', $this->PRAM_DB);

            if (!$anaspa_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggregato '{$datiRichiesta['codiceAggregato']}' inesistente.");
                return false;
            }

            $codiceAggregato = $anaspa_rec['SPACOD'];
        }

        /*
         * Preparazione dati richiesta
         */

        $annoRichiesta = date('Y');

        $ricnum_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(RICNUM) AS RICNUM FROM PRORIC WHERE RICNUM LIKE '$annoRichiesta%'", false);
        if ($ricnum_rec['RICNUM'] == null) {
            $numeroRichiesta = $annoRichiesta . "000001";
        } else {
            $numeroRichiesta = $ricnum_rec['RICNUM'] + 1;
        }

        $eventoComunica = $anaeventi_rec['EVTSEGCOMUNICA'];
        if ($eventoComunica === '') {
            $eventoComunica = $anapra_rec['PRASEG'];
        }



        /*
         * Crea cartelle di lavoro per la pratica
         * 
         * @TODO: Possibile centralizzare anche per praLib->regProcedimento
         * 
         */
        if (!$attachFolder = $this->praLib->getCartellaAttachmentPratiche($numeroRichiesta)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella allegati <b>$attachFolder</b> fallita Pratica N. " . $numeroRichiesta);
            return false;
        }
        if (!$repositoryFolderTesti = $this->praLib->getCartellaRepositoryPratiche($numeroRichiesta . "/testiAssociati")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella <b>testAssociati</b> fallita Pratica N. " . $numeroRichiesta);
            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0005', "Creazione cartella <b>$repositoryFolderTesti</b> fallita Pratica N. " . $Ricnum, __CLASS__);
            return false;
        }
        if (!$repositoryFolderImg = $this->praLib->getCartellaRepositoryPratiche($numeroRichiesta . "/immagini")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella <b>Immagini</b> fallita Pratica N. " . $numeroRichiesta);
            return false;
        }
        if (!$repositoryFolderMail = $this->praLib->getCartellaRepositoryPratiche($numeroRichiesta . "/mail")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella <b>mail</b> fallita Pratica N. " . $numeroRichiesta);
            return false;
        }
        if (!$tempFolder = $this->praLib->getCartellaTemporaryPratiche($numeroRichiesta)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . $numeroRichiesta);
            return false;
        }
        if (!$logFolder = $this->praLib->getCartellaLog()) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione cartella <b>$logFolder</b> fallita Pratica N. " . $numeroRichiesta);
            return false;
        }


        /*
         * Utilizzo il data base master se il procedimento è slave PRASLAVE=1
         * 
         * @TODO: Anche questo centralizzabile per praLib->regProxedimento
         */

        $repositoryUrl = "";
        $repositoryUrlMail = ITA_MASTER_REPOSITORY;
        $tipoEnte = $this->praLib->GetTipoEnte($this->PRAM_DB);
        if ($Anapra_rec['PRASLAVE'] != 1) {
            $this->PRAM_DB_R = $this->PRAM_DB;
            $repositoryUrl = "";
        } elseif ($Anapra_rec['PRASLAVE'] == 1 && $this->PRAM_DB_R !== $this->PRAM_DB && ITA_MASTER_REPOSITORY) {
            $repositoryUrl = ITA_MASTER_REPOSITORY;
        }
        $sourceDocument = ITA_DOC_DOCUMENTI;

        $Filent_rec = $this->praLib->GetFilent(1, $this->PRAM_DB);
        if ($Filent_rec['FILCOD'] == 1 || $tipoEnte == "M") {
            $repositoryUrlMail = ITA_PROC_REPOSITORY;
        }

        $repConnectorMailXml = new praRep($repositoryUrlMail);
        if (!$repConnectorMailXml->getFile('mail.xml', $repositoryFolderMail . "/mail.xml", false)) {
            $this->setErrCode(-1);
            $this->setErrMessage($repConnectorMailXml->getErrorMessage() . " Pratica N. " . $numeroRichiesta);
            return false;
        }


        /*
         * Creazione cartella allegati
         */


        /*
          $filePathAllegati = rtrim(ITA_PRAT_ATTACHMENT, '/') . "/$numeroRichiesta/";
          if (!is_dir($filePathAllegati) && !mkdir($filePathAllegati, 0777, true)) {
          $this->setErrCode(-1);
          $this->setErrMessage("Errore creazione cartella allegati '$filePathAllegati'.");
          return false;
          }

         */

        /*
         * Inserimento richiesta
         */

        $proric_rec = array();
        $proric_rec['RICNUM'] = $numeroRichiesta;
        $proric_rec['RICPRO'] = $codiceProcedimento;
        $proric_rec['RICRES'] = $anapra_rec['PRARES'];
        $proric_rec['RICSET'] = $anapra_rec['PRASET'];
        $proric_rec['RICSER'] = $anapra_rec['PRASER'];
        $proric_rec['RICOPE'] = $anapra_rec['PRAOPE'];
        $proric_rec['RICFIS'] = $datiRichiesta['esibente']['codiceFiscale'];
        $proric_rec['RICDRE'] = date('Ymd');
        $proric_rec['RICORE'] = date('H:i:s');
        $proric_rec['RICSOG'] = '';
        $proric_rec['RICCOG'] = $datiRichiesta['esibente']['cognome'];
        $proric_rec['RICNOM'] = $datiRichiesta['esibente']['nome'];
        $proric_rec['RICVIA'] = '';
        $proric_rec['RICCOM'] = '';
        $proric_rec['RICCAP'] = '';
        $proric_rec['RICPRV'] = '';
        $proric_rec['RICSTA'] = '99';
        $proric_rec['RICDAT'] = date('Ymd');
        $proric_rec['RICTIM'] = date('H:i:s');
        $proric_rec['RICEMA'] = '';
        $proric_rec['RICTSP'] = $datiRichiesta['codiceSportello'];
        $proric_rec['RICNAZ'] = '';
        $proric_rec['RICNAS'] = '';
        $proric_rec['RICEVE'] = $codiceEvento;
        $proric_rec['RICSEG'] = $eventoComunica;
        $proric_rec['RICTIP'] = $iteevt_rec['IEVTIP'];
        $proric_rec['RICSTT'] = $iteevt_rec['IEVSTT'];
        $proric_rec['RICATT'] = $iteevt_rec['IEVATT'];
        $proric_rec['RICDESCR'] = $iteevt_rec['IEVDESCR'];

        if ($codiceAggregato) {
            $proric_rec['RICSPA'] = $codiceAggregato;
        }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRORIC', 'ROWID', $proric_rec);

            eqAudit::logEqEvent($this, array('Estremi' => "Inserimento Richiesta $numeroRichiesta", 'DSet' => 'PRORIC', 'Operazione' => eqAudit::OP_INS_RECORD, 'DB' => $this->PRAM_DB));
        } catch (Exception $e) {
            eqAudit::logEqEvent($this, array('Estremi' => "Inserimento Richiesta $numeroRichiesta", 'DSet' => 'PRORIC', 'Operazione' => eqAudit::OP_INS_RECORD_FAILED, 'DB' => $this->PRAM_DB));

            $this->setErrCode(-1);
            $this->setErrMessage("Errore inserimento Richiesta $numeroRichiesta: " . $e->getMessage());
            return false;
        }

        /*
         * Inserimento dati aggiuntivi
         */

        $datiUtente = array(
            'ESIBENTE_NOME' => $datiRichiesta['esibente']['nome'],
            'ESIBENTE_COGNOME' => $datiRichiesta['esibente']['cognome'],
            'ESIBENTE_CODICEFISCALE_CFI' => $datiRichiesta['esibente']['codiceFiscale'],
            'nome' => $datiRichiesta['esibente']['nome'],
            'cognome' => $datiRichiesta['esibente']['cognome'],
            'fiscale' => $datiRichiesta['esibente']['codiceFiscale'],
            'username' => strtolower($datiRichiesta['esibente']['nome'] . '.' . $datiRichiesta['esibente']['cognome']),
            'ruolo' => 'ESIBENTE'
        );

        foreach ($datiAggiuntivi as $key => $datoAggiuntivo) {
            $dagset = $numeroRichiesta;
            if ($datoAggiuntivo['dataset']) {
                $dagset .= '_' . str_pad($datoAggiuntivo['dataset'], 2, '0', STR_PAD_LEFT);
            }

            $ricdag_rec = array();
            $ricdag_rec['DAGNUM'] = $numeroRichiesta;
            $ricdag_rec['ITECOD'] = $codiceProcedimento;
            $ricdag_rec['ITEKEY'] = $codiceProcedimento;
            $ricdag_rec['DAGSEQ'] = ($key + 1) * 10;
            $ricdag_rec['DAGKEY'] = $datoAggiuntivo['chiave'];
            $ricdag_rec['DAGSET'] = $dagset;
            $ricdag_rec['RICDAT'] = $datoAggiuntivo['valore'];

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);

                eqAudit::logEqEvent($this, array('Estremi' => "Inserimento dato aggiuntivo '{$datoAggiuntivo['chiave']}' $numeroRichiesta", 'DSet' => 'RICDAG', 'Operazione' => eqAudit::OP_INS_RECORD, 'DB' => $this->PRAM_DB));
            } catch (Exception $e) {
                eqAudit::logEqEvent($this, array('Estremi' => "Inserimento dato aggiuntivo '{$datoAggiuntivo['chiave']}' $numeroRichiesta", 'DSet' => 'RICDAG', 'Operazione' => eqAudit::OP_INS_RECORD_FAILED, 'DB' => $this->PRAM_DB));

                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento Dato Aggiuntivo {$datoAggiuntivo['chiave']} $numeroRichiesta: " . $e->getMessage());
                return false;
            }

            if (strpos($datoAggiuntivo['chiave'], 'ESIBENTE_') === 0) {
                $datiUtente[$datoAggiuntivo['chiave']] = $datoAggiuntivo['valore'];
            }
        }

        frontOfficeApp::$cmsHost->setDatiUtente($datiUtente);

        /*
         * Inserimento allegati
         */

        foreach ($allegati as $allegato) {
            $tempPathAllegato = $tempPathAllegati . '/' . $allegato['id'];

            if (!$allegato['nomeFile']) {
                $allegato['nomeFile'] = $allegato['id'];
            }

            if ($allegato['nomeFile'] === 'body.txt') {
                $allegato['nomeFile'] = 'bodyOriginale.txt';
            }

            if (!rename($tempPathAllegato, $attachFolder . DIRECTORY_SEPARATOR . $allegato['id'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore spostamento file Allegato {$allegato['id']}.");
                return false;
            }

            $DOCMETA = array('PASNOT' => $allegato['note'], 'NOTE' => $allegato['noteAggiuntive']);

            $ricdoc_rec = array();
            $ricdoc_rec['DOCNUM'] = $numeroRichiesta;
            $ricdoc_rec['ITECOD'] = $codiceProcedimento;
            $ricdoc_rec['ITEKEY'] = $codiceProcedimento;
            $ricdoc_rec['DOCUPL'] = $allegato['id'];
            $ricdoc_rec['DOCNAME'] = $allegato['nomeFile'];
            $ricdoc_rec['DOCSHA2'] = $allegato['hash'];
            $ricdoc_rec['DOCMETA'] = serialize($DOCMETA);
            $ricdoc_rec['DOCPRI'] = isset($allegato['allegatoPrincipale']) && $allegato['allegatoPrincipale'] ? 1 : 0;

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'RICDOC', 'ROWID', $ricdoc_rec);

                eqAudit::logEqEvent($this, array('Estremi' => "Inserimento allegato '{$allegato['nomeFile']}' $numeroRichiesta", 'DSet' => 'RICDOC', 'Operazione' => eqAudit::OP_INS_RECORD, 'DB' => $this->PRAM_DB));
            } catch (Exception $e) {
                eqAudit::logEqEvent($this, array('Estremi' => "Inserimento allegato '{$allegato['nomeFile']}' $numeroRichiesta", 'DSet' => 'RICDOC', 'Operazione' => eqAudit::OP_INS_RECORD_FAILED, 'DB' => $this->PRAM_DB));

                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento Allegato {$allegato['id']}: " . $e->getMessage());
                return false;
            }
        }

        /*
         * Inizio inoltro richiesta
         */
        
        if (!$dati = $this->praLibDati->prendiDati($numeroRichiesta, '', '', true)) {
            $this->setErrCode($this->praLibDati->getErrCode());
            $this->setErrMessage($this->praLibDati->getErrMessage());
            return false;
        }

        if (!$this->praLib->elaboraOggettoRichiesta($dati)) {
            $this->setErrCode($this->praLib->getErrCode());
            $this->setErrMessage($this->praLib->getErrMessage());
            return false;
        }

        if (!$dati = $this->praLibDati->prendiDati($numeroRichiesta, '', '', true)) {
            $this->setErrCode($this->praLibDati->getErrCode());
            $this->setErrMessage($this->praLibDati->getErrMessage());
            return false;
        }

        $protocolloOttenuto = $differita = false;

        $parametriRichiesteWSSOAP = $this->GetParametriRichiesteWSSOAP();

        if ($parametriRichiesteWSSOAP['RICHIESTEWSSOAPBLOCCOPROT'] != 'Si') {
            $praLibProtocolla = new praLibProtocolla();

            $differita = $praLibProtocolla->checkProtocollazioneDifferita($dati);

            if (!$differita) {
                if ($praLibProtocolla->checkRichestaDaProtocollare($dati)) {
                    $protocollaResult = $praLibProtocolla->protocollaRichiesta($dati);

                    switch ($protocollaResult['RESULT']) {
                        case praLibProtocolla::RESULT_PROTOCOLLA_WARNING:
                            // @TODO ?
                            break;

                        case praLibProtocolla::RESULT_PROTOCOLLA_ERROR:
                            $this->setErrCode(-1);
                            $this->setErrMessage($protocollaResult['ERRORE']);
                            return false;
                    }

                    $protocolloOttenuto = $protocollaResult['PROTOCOLLATO'];

                    /*
                     * Ricarico dati dopo protocollazione
                     */

                    $dati = $this->praLibDati->prendiDati($numeroRichiesta, '', '', true);
                    if (!$dati) {
                        $this->setErrCode($this->praLibDati->getErrCode());
                        $this->setErrMessage($this->praLibDati->getErrMessage());
                        return false;
                    }


                    if (!$this->praLib->BloccaRichiesta($dati)) {
                        $this->setErrCode($this->praLib->getErrCode());
                        $this->setErrMessage($this->praLib->getErrMessage());
                        return false;
                    }

                    /*
                     * Ricarico i dati dopo blocco pratica
                     */

                    $dati = $this->praLibDati->prendiDati($numeroRichiesta, '', '', true);
                    if (!$dati) {
                        $this->setErrCode($this->praLibDati->getErrCode());
                        $this->setErrMessage($this->praLibDati->getErrMessage());
                        return false;
                    }
                }
            }
        }

        /*
         * Carico i testi parametri per il corpo delle mail decodificando le variabili dizionario
         */

        $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);
        if (!$arrayDatiMail) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato");
            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0048', "Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato", __CLASS__);
            return false;
        }

        $arrayDatiMail['errStringProt'] = $protocollaResult['RICHIESTA']['errString'];
        $arrayDatiMail['strNoProt'] = $protocollaResult['RICHIESTA']['strNoProt'];
        $arrayParamBloccoMail = $this->praLib->GetParametriBloccoMail($this->PRAM_DB);

        $txtBody = $dati['CartellaAllegati'] . "/body.txt";
        $FileBody = fopen($txtBody, "w+");
        if (!file_exists($txtBody)) {
            $this->setErrCode(-1);
            $this->setErrMessage("File " . $dati['CartellaAllegati'] . "/body.txt non trovato");
            return false;
        }

        fwrite($FileBody, $arrayDatiMail['bodyResponsabile']);
        fclose($FileBody);

        /*
         * Scrivo il file XMLINFO
         */

        if (!$this->praLib->CreaXMLINFO('', $dati)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione file XMLINFO fallita per la richiesta n. {$dati['Proric_rec']['RICNUM']}.");
            return false;
        }

        /*
         * Preparo gli allegati da inviare
         */

        $TotaleAllegati = $this->praLib->GetAllegatiInvioMail($dati, $arrayParamBloccoMail, $this->PRAM_DB);

        /*
         * Mail al responsabile
         */

        if ($parametriRichiesteWSSOAP['RICHIESTEWSSOAPBLOCCOMAIL'] != 'Si') {
            //@TODO: PORTARE A LIBRERIA tutta la fase di invio mail
            //@TODO: Modo dipende dal tipo di pratica non implementato da verificare
            $modo = '';
            if (!$dati['Ricite_rec'] || $dati['Ricite_rec']['ITEMRE'] == 0) {
                if ($arrayParamBloccoMail['bloccaMailResp'] == null || $arrayParamBloccoMail['bloccaMailResp'] == "No") {
                    $ErrorMail = $this->praLib->InvioMailResponsabile($dati, $TotaleAllegati, $this->PRAM_DB, $arrayDatiMail, $modo);
                    if ($protocolloOttenuto == false) {
                        if ($ErrorMail) {
                            $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al resposansabile comunale.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                            $this->setErrCode(-1);
                            $this->setErrMessage("Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail);
                            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                            return false;
                        }
                    }
                }
            }
        }

        /*
          if (!$dati['Ricite_rec'] || $dati['Ricite_rec']['ITEMRI'] == 0) {
          if ($arrayParamBloccoMail['bloccaMailRich'] == null || $arrayParamBloccoMail['bloccaMailRich'] == "No") {
          $mailRich = $this->praLib->GetMailRichiedente($modo, $dati['Ricdag_tab_totali']);
          foreach ($mailRich as $k => $mail) {
          if (!$mail) {
          unset($mailRich[$k]);
          }
          }
          $ErrorMail = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, $modo, $TotaleAllegati);
          }
          }
         */

        /*
         * Blocco la Richiesta
         */

        if (!$this->praLib->BloccaRichiesta($dati, $differita)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel finalizzare la richiesta n. {$dati['Proric_rec']['RICNUM']}.");
            return false;
        }

        return $numeroRichiesta;
    }

    public function GetParametriRichiesteWSSOAP() {
        $arrayParametri = array();
        $parametriRichiesteSOAP = $this->praLib->GetAnapar('RICHIESTEWSSOAP', 'codice', $this->PRAM_DB, true);

        foreach ($parametriRichiesteSOAP as $parametro) {
            $arrayParametri[$parametro['PARKEY']] = $parametro['PARVAL'];
        }

        return $arrayParametri;
    }

}
