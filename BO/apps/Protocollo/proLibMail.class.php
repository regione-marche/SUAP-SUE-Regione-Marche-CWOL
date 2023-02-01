<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    18.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proInteropMsg.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class proLibMail {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $proLib;
    public $emlLib;
    public $accLib;
    public $eqAudit;
    public $proLibAllegati;
    private $errCode;
    private $errMessage;
    private $currObjSdi;
    private $currMessage;
    private $retDestMap = array();
    private $mailProtocollate = array();
    private $mailInteropAssegnate = array();
    private $mailErrore = array();
    private $mailAvviso = array();

    const DA_PROTOCOLLARE = '@DA_PROTOCOLLARE@';
    const PROTOCOLLATO = '@PROTOCOLLATO@';
    const INOLTRO_PROTOCOLLO = '@INOLTRO_PROTOCOLLO@';
    const IN_PROTOCOLLAZIONE = '@IN_PROTOCOLLAZIONE@';

    public static $ElencoClassProtocollabili = array(
        self::DA_PROTOCOLLARE,
        self::IN_PROTOCOLLAZIONE
    );

    function __construct() {
        $this->proLib = new proLib();
        $this->emlLib = new emlLib();
        $this->accLib = new accLib();
        $this->eqAudit = new eqAudit();
        $this->proLibAllegati = new proLibAllegati();
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

    function getCurrObjSdi() {
        return $this->currObjSdi;
    }

    function getCurrMessage() {
        return $this->currMessage;
    }

    function setCurrObjSdi($currObjSdi) {
        $this->currObjSdi = $currObjSdi;
    }

    function setCurrMessage($currMessage) {
        $this->currMessage = $currMessage;
    }

    public function getRetDestMap() {
        return $this->retDestMap;
    }

    public function getMailProtocollate() {
        return $this->mailProtocollate;
    }

    public function setMailProtocollate($mailProtocollate) {
        $this->mailProtocollate = $mailProtocollate;
    }

    public function getMailInteropAssegnate() {
        return $this->mailInteropAssegnate;
    }

    public function setMailInteropAssegnate($mailInteropAssegnate) {
        $this->mailInteropAssegnate = $mailInteropAssegnate;
    }

    public function getMailErrore() {
        return $this->mailErrore;
    }

    public function setMailErrore($mailErrore) {
        $this->mailErrore = $mailErrore;
    }

    public function getMailAvviso() {
        return $this->mailAvviso;
    }

    public function setMailAvviso($mailAvviso) {
        $this->mailAvviso = $mailAvviso;
    }

    public function inviaMailDestinatari($nameForm, $proArriDest, $proAltriDestinatari, $proArriAlle, $chiave, $tipo = 'rowid', $propar = '', $obligoInviomail = false) {
//        // Controllo invio mail.. - da testare
//        if ($this->proLibAllegati->CheckAllegatiAllaFirma($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'])) {
//            $this->errCode = -1;
//            $this->setErrMessage("Sono presenti documenti alla firma, occorre firmarli prima di poter procedere.");
//            return false;
//        }


        $anapro_rec = $this->proLib->GetAnapro($chiave, $tipo, $propar);
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $anaent_32 = $this->proLib->GetAnaent('32');
        $check_p7m = ($anaent_32['ENTDE6'] == 1) ? true : false;
        switch ($anapro_rec['PROPAR']) {
            case "P":
            case "C":
                $allegati = $this->proLibAllegati->checkPresenzaAllegati($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $check_p7m);
                if ($allegati == 0) {
                    $messaggio = ($check_p7m == true ) ? "firmati (p7m)." : ".";
//                    Out::msgStop("Invio mail protocolo a Destinatari", "L'invio non è possibile in mancaza di allegati " . $messaggio);
                    $this->errCode = -1;
                    $this->setErrMessage("Invio mail protocolo a Destinatari. L'invio non è possibile in mancaza di allegati " . $messaggio);
                    return false;
                }
                break;
        }

        $anaent_rec = $this->proLib->GetAnaent('27');
        if ($anaent_rec['ENTDE5'] == '') {
            $utelog = App::$utente->getKey('nomeUtente');
            $utenti_rec = $this->accLib->GetUtenti($utelog, 'utelog');
            $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
            if ($richut_rec['RICMAI'] == '') {
                return false;
            }
        }
        $destMap = $this->checkInvioAvvenuto($anapro_rec, $proArriDest, $proAltriDestinatari);
        if ($destMap) {
            $allegati = array();
            foreach ($proArriAlle as $allegato) {
                if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0) {
                    $allegati[] = $allegato;
                }
            }
            $anaent_rec = $this->proLib->GetAnaent('35');
            if ($anaent_rec['ENTDE5'] != '1') {
                $segnatura = $this->proLibAllegati->ScriviFileXML($anapro_rec['ROWID']);
                if ($segnatura['stato'] == '-1') {
                    $this->errCode = -1;
                    $this->setErrMessage("Invio mail protocolo a Destinatari. " . $segnatura['messaggio']);
                    return false;
                } else if ($segnatura['stato'] == '-2') {
                    $this->errCode = 2;
                    $this->setErrMessage("Invio mail protocolo a Destinatari. " . $segnatura['messaggio']);
//                Out::msgInfo("Invio mail protocolo a Destinatari", $segnatura['messaggio']);
                } else {
                    $allegati[] = array('FILEPATH' => $segnatura, 'FILENAME' => 'Segnatura.xml', 'FILEINFO' => 'Segnatura.xml');
                }
            }
            $tipoProtoc = "PROTOCOLLO IN ARRIVO";
            if ($anapro_rec['PROPAR'] == 'P') {
                $tipoProtoc = "PROTOCOLLO IN PARTENZA";
            } else if ($anapro_rec['PROPAR'] == 'C') {
                $tipoProtoc = "DOC.FORMALE";
            }

            /*
             * Elementi Mail
             */
            $OggMail = $CorpoMail = '';
            $ElementiMail = $this->GetElementiTemplateMail($anapro_rec, 2, true);
            $OggMail = $ElementiMail['OGGETTOMAIL'];
            $CorpoMail = $ElementiMail['BODYMAIL'];

            if (!$OggMail) {
                $OggMail = "$tipoProtoc - " . $anapro_rec['PROSEG'];
            }
            if (!$CorpoMail) {
                $CorpoMail = $anaogg_rec['OGGOGG'];
            }
            /* Valori Mail */
            $valori = array(
                'Destinatari' => $destMap,
                'Oggetto' => $OggMail,
                'Corpo' => $CorpoMail
            );

            $DaMail = $this->proLib->GetElencoDaMail('send', $anapro_rec['PROUOF']);
            $model = 'utiGestMail';
            $_POST = array();
            $_POST['tipo'] = 'protocollo';
            $_POST['valori'] = $valori;
            $_POST['allegati'] = $allegati;
            $_POST['returnModel'] = $nameForm;
            $_POST['returnEvent'] = 'returnMail';
            $_POST['returnEventOnClose'] = 'true';
            $_POST['event'] = 'openform';
            $_POST['obbligoInvioMail'] = $obligoInviomail;
            $_POST['ElencoDaMail'] = $DaMail;
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        } else {
            $this->errCode = -3;
            $this->setErrMessage("Non esistono indirizzi PEC/Email a cui inviare la notifica.");
        }
        return false;
    }

    public function checkInvioAvvenuto($anapro_rec, $proArriDest, $proAltriDestinatari, $status = false) {
        $destMap = array();
        $statusMap = array();
        $statusMap['COUNTINTERNI'] = 0;
        $statusMap['SENTINTERNI'] = 0;
        // Destinatario interni:
        $anaent_26 = $this->proLib->GetAnaent('26');
        $SeleMan = false;
        if ($anaent_26['ENTDE6']) {
            $SeleMan = true;
        }
        $SelezioneDinamica = '';
        $anaent_48 = $this->proLib->GetAnaent('48');
        if ($anaent_48['ENTVAL']) {
            $SelezioneDinamica = true;
        }

        foreach ($proArriDest as $i => $destinatario) {
            if ($destinatario['DESMAIL'] != '' && $destinatario['ROWID'] != '') {
                $destMap_rec = array();
                $destMap_rec['MAIL'] = $destinatario['DESMAIL'];
                $destMap_rec['NOME'] = $destinatario['DESNOM'];
                $destMap_rec['TIPO'] = "proArridest";
                if ($SelezioneDinamica) {
                    if ($destinatario['DESINV'] == 1) {
                        $destMap_rec['SELEMAN'] = $SeleMan;
                    } else {
                        // Se non è selezionato lo salto come destinatario
                        continue;
                    }
                } else {
                    $destMap_rec['SELEMAN'] = $SeleMan;
                }
                $destMap_rec['indice'] = $i;
                $destMap[] = $destMap_rec;
                $statusMap['COUNTINTERNI'] ++;
                if (!$destinatario['DESIDMAIL']) {
                    $statusMap['SENTINTERNI'] ++;
                }
            }
        }
        $statusMap['COUNTESTERNI'] = 0;
        $statusMap['SENTESTERNI'] = 0;
        foreach ($proAltriDestinatari as $i => $destinatario) {
            if ($destinatario['DESMAIL'] != '' && $destinatario['ROWID'] != '') {
                $statusMap['COUNTESTERNI'] ++;
                if (!$destinatario['DESIDMAIL']) {
                    $destMap_rec = array();
                    $destMap_rec['MAIL'] = $destinatario['DESMAIL'];
                    $destMap_rec['NOME'] = $destinatario['DESNOM'];
                    $destMap_rec['TIPO'] = "proAltriDestinatari";
                    $destMap_rec['indice'] = $i;
                    $destMap[] = $destMap_rec;
                    $statusMap['SENTESTERNI'] ++;
                }
            }
        }
        if ($anapro_rec['PROPAR'] == 'P') {
            if ($anapro_rec['PROMAIL'] != '') {
                $statusMap['COUNTESTERNI'] ++;
                if (!$anapro_rec['PROIDMAILDEST']) {
                    $statusMap['SENTESTERNI'] ++;
                    $destMap_rec = array();
                    $destMap_rec['MAIL'] = $anapro_rec['PROMAIL'];
                    $destMap_rec['NOME'] = $anapro_rec['PRONOM'];
                    $destMap_rec['TIPO'] = "proArri-P";
                    $destMap_rec['indice'] = "";
                    $destMap[] = $destMap_rec;
                }
            }
        }
        if ($status === true) {
            return $statusMap;
        } else {
            return $destMap;
        }
    }

    public function servizioInvioMail($model, $valori, $pronum, $propar, $mittentiAggiuntivi, $proArriDest, $proAltriDestinatari, $ForzaDaMail = '') {
        /* @var $emlMailBox emlMailBox */

        $this->retDestMap = array();

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $anaent_26 = $this->proLib->GetAnaent('26');
        if ($anaent_26) {
            $accountSMTP = $anaent_26['ENTDE4'];
            $ricevutaPECBreve = $anaent_26['ENTVAL'];
        }
        if ($accountSMTP != '') {
            $emlMailBox = emlMailBox::getInstance($accountSMTP);
        } else {
            $emlMailBox = emlMailBox::getUserAccountInstance();
        }
        if (!$emlMailBox) {
            $this->setErrMessage("Accesso ai parametri di invio fallito.");
            return false;
        }
        // 
        // MailBox Alternativa per Destinatari interni
        //
        $anaent_37 = $this->proLib->GetAnaent('37');
        $anaent_56 = $this->proLib->GetAnaent('56');
        if ($anaent_37['ENTDE2']) {
            $accountSMTPAlt = $anaent_37['ENTDE2'];
        }
        if ($accountSMTPAlt != '') {
            $emlMailBoxAlt = emlMailBox::getInstance($accountSMTPAlt);
        } else {
            $emlMailBoxAlt = $emlMailBox;
        }





        /*
         * Verifico mail predefinita per Fatture e Comunicazioni SDI in Partenza
         */
        $anaent_45 = $this->proLib->GetAnaent('45');
        if ($anaent_45['ENTDE4']) {
            $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
            $anaent_38 = $this->proLib->GetAnaent('38');
            if ($anapro_rec['PROCODTIPODOC'] && ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4'] || $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE2'])) {
                $emlMailBox = emlMailBox::getInstance($anaent_45['ENTDE4']);
            }
        }

        /* Forzatura Account Da Mail */
        if ($ForzaDaMail) {
            $emlMailBox = emlMailBox::getInstance($ForzaDaMail);
            /*
             *  Se il parametro per forzare a usare sempre l'alternativa non è attivo
             *  ed è stata forzata una mail, anche per le interne deve essere usata la forzatura.
             */
            if (!$anaent_56['ENTDE6']) {
                $emlMailBoxAlt = $emlMailBox;
            }
        } else {
            /*
             * Controllo se utente abilitato ad utilizzare la mail default selezionata.
             * Altrimenti cerco la prima impostata ed autorizzata.
             * Se deve utilizzarne un'altra, lo seleziona durante l'invio.
             */
            $EmailUtente = $this->proLib->GetElencoDaMail('send');
            if ($EmailUtente) {
                $Abilitato = false;
                $Account = $emlMailBox->getAccount();
                foreach ($EmailUtente as $Mail) {
                    if ($Mail == $Account) {
                        $Abilitato = true;
                        break;
                    }
                }
                if (!$Abilitato) {
                    // Prendo prima mail autorizzata:
                    if (!$EmailUtente[0]) {
                        $this->setErrMessage("Accesso ai parametri di invio fallito.");
                        return false;
                    }
                    $emlMailBox = emlMailBox::getInstance($EmailUtente[0]);
                    if (!$emlMailBox) {
                        $this->setErrMessage("Accesso ai parametri di invio fallito.");
                        return false;
                    }
                }
            }
        }

        /*
         *  Se è parametrizzato, ricevuta breve
         *  anche quando viene forzato il mittente mail.
         */
        if ($ricevutaPECBreve) {
            $emlMailBox->setPECRicvutaBreve();
        }


        foreach ($valori['destMap'] as $destMap_rec) {
            $destinatarioMail = $destMap_rec['MAIL'];
            if ($destMap_rec['TIPO'] == 'proArridest') {
                $emlMailBoxWork = $emlMailBoxAlt;
            } else {
                $emlMailBoxWork = $emlMailBox;
            }
            $LogEvent = 'Invio mail al destinatario: ' . $destinatarioMail;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $LogEvent));

            /* @var $outgoingMessage emlOutgoingMessage */
            $outgoingMessage = $emlMailBoxWork->newEmlOutgoingMessage();
            $outgoingMessage->setSubject($valori['Oggetto']);
            $outgoingMessage->setBody($valori['Corpo']);
            $outgoingMessage->setEmail($destinatarioMail);
            $outgoingMessage->setAttachments($valori['allegati']);
            $mailArchivio_rec = $emlMailBoxWork->sendMessage($outgoingMessage);

            if ($mailArchivio_rec) {
                $destMap_rec['MAILARCHIVIO'] = $mailArchivio_rec;
                $this->retDestMap[] = $destMap_rec;

                $promail_rec = array();
                $promail_rec['PRONUM'] = $pronum;
                $promail_rec['PROPAR'] = $propar;
                $promail_rec['IDMAIL'] = $mailArchivio_rec['IDMAIL'];
                $promail_rec['SENDREC'] = $mailArchivio_rec['SENDREC'];
//                $insert_Info = 'Oggetto Protocollo: ' . $promail_rec['PRONUM'] . " " . $promail_rec['IDMAIL'];
                $insert_Info = 'Oggetto servizioInvioMail: ' . $promail_rec['PRONUM'] . " " . $promail_rec['IDMAIL'];
                $model->insertRecord($this->proLib->getPROTDB(), 'PROMAIL', $promail_rec, $insert_Info);
                $proIdMail = $mailArchivio_rec['IDMAIL'];
                switch ($destMap_rec['TIPO']) {
                    case 'mittentiAggiuntivi':
                        $mittentiAggiuntivi[$destMap_rec['indice']]['PROIDMAILDEST'] = $proIdMail;
                        $promitagg_rec = $this->proLib->getPromitagg($mittentiAggiuntivi[$destMap_rec['indice']]['ROWID'], 'rowid');
                        $promitagg_rec['PROIDMAILDEST'] = $proIdMail;
                        $model->updateRecord($this->proLib->getPROTDB(), 'PROMITAGG', $promitagg_rec, '', 'ROWID', false);
                        break;
                    case 'proArridest':
                        $proArriDest[$destMap_rec['indice']]['DESIDMAIL'] = $proIdMail;
                        $anades_rec = $this->proLib->GetAnades($proArriDest[$destMap_rec['indice']]['ROWID'], 'rowid');
                        $anades_rec['DESIDMAIL'] = $proIdMail;
                        $model->updateRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, '', 'ROWID', false);
                        break;
                    case 'proAltriDestinatari':
                        $proAltriDestinatari[$destMap_rec['indice']]['DESIDMAIL'] = $proIdMail;
                        $anades_rec = $this->proLib->GetAnades($proAltriDestinatari[$destMap_rec['indice']]['ROWID'], 'rowid');
                        $anades_rec['DESIDMAIL'] = $proIdMail;
                        $model->updateRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, '', 'ROWID', false);
                        break;
                    case 'proArri-A':
                    case 'proArri-P':
                        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
                        $anapro_rec['PROIDMAILDEST'] = $proIdMail;
                        $model->updateRecord($this->proLib->getPROTDB(), 'ANAPRO', $anapro_rec, '', 'ROWID', false);
                        break;
                }
                $LogEvent = 'Mail inviata con successo al destinatario: ' . $destinatarioMail;
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $LogEvent));
            } else {
                if ($emlMailBoxWork->getLastMessage()) {
                    $this->setErrMessage($emlMailBoxWork->getLastMessage());
                } else {
                    $this->setErrMessage("Errore in invio Mail");
                }
                $LogEvent = 'Errore in invio: ' . $this->errMessage;
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $LogEvent));
                return false;
            }
        }
        return array('mittentiAggiuntivi' => $mittentiAggiuntivi, 'proArridest' => $proArriDest, 'proAltriDestinatari' => $proAltriDestinatari);
    }

    public function leggiMailDaProtocollare() {
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='@DA_PROTOCOLLARE@'";
        return $this->emlLib->getGenericTab($sql);
    }

    public function assegnaRicevute() {
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE CLASS ='" . self::DA_PROTOCOLLARE . "'";
        $mail_tab = $this->emlLib->getGenericTab($sql);
        $assegnate = 0;
        foreach ($mail_tab as $mail_rec) {
            if (!$mail_rec['IDMAILPADRE']) {
                continue;
            }
            $retDecode = $this->getStruttura($mail_rec['ROWID']);
            $risultato = false;
            $promail_rec = $this->proLib->getPromail(" IDMAIL='" . $mail_rec['IDMAILPADRE'] . "'");
            if ($promail_rec) {
                $anapro_rec = $this->proLib->GetAnapro($promail_rec['PRONUM'], 'codice', $promail_rec['PROPAR']);
                if (!$anapro_rec) {
                    //gestire errore
                }
                $risultato = $this->assegnaEmlToProtocollo($anapro_rec, $mail_rec);
                if ($risultato !== false) {
                    $assegnate = $assegnate + 1;
                }
                continue;
            }
            //Come nei filtri..
            if ($mail_rec['PECTIPO'] == emlMessage::PEC_TIPO_ACCETTAZIONE || $mail_rec['PECTIPO'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                $MailArchivioPadre_rec = $this->emlLib->getMailArchivio($mail_rec['IDMAILPADRE'], "id");
                if ($MailArchivioPadre_rec['CLASS'] == self::INOLTRO_PROTOCOLLO) {
                    $emlDbMailBox = new emlDbMailBox();
                    $risultato = $emlDbMailBox->updateClassForRowId($mail_rec['ROWID'], $MailArchivioPadre_rec['CLASS']);
                    if ($risultato !== false) {
                        $assegnate = $assegnate + 1;
                    }
                }
            }
        }
        return $assegnate;
    }

    public function getStruttura($rowid) {
        $this->clearCurrMessage();
        $currMailBox = new emlMailBox();
        $this->currMessage = $currMailBox->getMessageFromDb($rowid);
        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();
        $ExtraParam = array();
        $ExtraParam['PARSEALLEGATI'] = true;
        $this->currObjSdi = proSdi::getInstance($retDecode, $ExtraParam);
        return $retDecode;
    }

    public function assegnaEmlToProtocollo($datiSegnatura, $elemento, $elementoLocale = null, $dettagliFile = array()) {
        $Anaent49_rec = $this->proLib->GetAnaent('49');

        if ($datiSegnatura) {
            if (is_object($this->currMessage)) {
                if (!isset($elementoLocale)) {
                    $nomefile = $elemento['PECTIPO'];
                } else {
                    $nomefile = $dettagliFile[$elementoLocale]['PECTIPO'];
                }
                if ($nomefile == '') {
                    $nomefile = 'Email assegnata al protocollo';
                }
                $emailOriginale = $this->currMessage->getEmlFile();
                $elementi = array();
                $elementi['dati'] = $datiSegnatura;
                $elementi['allegati'][] = array('DATAFILE' => $nomefile . '.eml', 'FILE' => $emailOriginale, 'DOCIDMAIL' => $elemento['IDMAIL']);
                $risultato = true;
                // Se è attivo alfresco, non serve risalvarsi la mail.
                // if (!$Anaent49_rec['ENTDE1']) {
                $model = 'proItalsoft.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proItalsoft = new proItalsoft();
                $risultato = $proItalsoft->aggiungiAllegatiProtocollo($elementi);
                //}
                if ($risultato) {
                    if (!isset($elementoLocale)) {
                        $emlDbMailBox = new emlDbMailBox();
                        $risultatoDb = $emlDbMailBox->updateClassForRowId($elemento['ROWID'], self::PROTOCOLLATO);
                        if ($risultatoDb === false) {
                            return false;
                        }
                        $promail_rec = array(
                            'PRONUM' => $datiSegnatura['PRONUM'],
                            'PROPAR' => $datiSegnatura['PROPAR'],
                            'IDMAIL' => $elemento['IDMAIL'],
                            'SENDREC' => $elemento['SENDREC']
                        );
                        $insert_Info = 'Inserimento: ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'];
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $insert_Info));
                        try {
                            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROMAIL', 'ROWID', $promail_rec);
                        } catch (Exception $exc) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Errore in inserimento. " . $exc->getMessage());
                            return false;
                        }
                    } else {
                        $fileLocale = $elementi['allegati'][0]['FILE'];
                        if (is_file($fileLocale)) {
                            if (!@unlink($fileLocale)) {
                                $this->setErrCode(-2);
                                $this->setErrMessage("File:" . $fileLocale . " non Eliminato");
                            }
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    private function clearCurrMessage($clearSdi = true) {
        if ($this->currMessage != null) {
            $this->currMessage->cleanData();
            $this->currMessage = null;
        }

        if ($this->currObjSdi != null) {
            if ($clearSdi) {
                $this->currObjSdi->cleanData();
            }
            $this->currObjSdi = null;
        }
    }

    public function GetElencoNotifichePecProt($pronum, $propar) {
        $retArrNotifiche = array();
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $ITALWEB_DB = $emlLib->getITALWEB();
        $Italweb = $ITALWEB_DB->getDB();
        $sql = "SELECT * "
                . " FROM PROMAIL "
                . " LEFT OUTER JOIN $Italweb.MAIL_ARCHIVIO MAIL_ARCHIVIO ON PROMAIL.IDMAIL = $Italweb.MAIL_ARCHIVIO.IDMAIL "
                . " WHERE PRONUM = $pronum AND PROPAR = '$propar' AND MAIL_ARCHIVIO.SENDREC = 'R' AND PROMAIL.IDMAIL <> '' ";
        $MailArchivio_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);

        $ElencoIDMail = $this->GetElencoIdMailDest($pronum, $propar);

        $retArrNotifiche['NOTIFICHE'] = $MailArchivio_tab;
        $countAnomalie = $countNonAnomalie = 0;

        foreach ($MailArchivio_tab as $key => $MailArchivio_rec) {
            switch ($MailArchivio_rec['PECTIPO']) {
                case emlMessage::PEC_TIPO_PRESA_IN_CARICO:
                case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:

                    $idMailPadre = $MailArchivio_rec['IDMAILPADRE'];
                    if ($ElencoIDMail[$idMailPadre]) {
                        $retArrNotifiche['INDICE_ANOMALIE'][] = $key;
                        $countAnomalie++;
                    }
                    break;

                case emlMessage::PEC_TIPO_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                    $retArrNotifiche['INDICE_NON_ANOMALIE'][] = $key;
                    $countNonAnomalie++;
                    break;

                default:
                    break;
            }
        }

        $retArrNotifiche['COUNT_NOTIFICHE'] = count($MailArchivio_tab);
        $retArrNotifiche['COUNT_ANOMALIE'] = $countAnomalie;
        $retArrNotifiche['COUNT_NON_ANOMALIE'] = $countNonAnomalie;

        return $retArrNotifiche;
    }

    public function GetElencoIdMailDest($pronum, $propar) {
        $ElencoIDMail = array();
        $sql = "SELECT * FROM ANADES WHERE DESIDMAIL <> '' AND DESNUM = $pronum AND DESPAR = '$propar' ";
        $Anades_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        foreach ($Anades_tab as $Anades_rec) {
            $ElencoIDMail[$Anades_rec['DESIDMAIL']] = $Anades_rec['DESIDMAIL'];
        }
        $sql = "SELECT * FROM ANAPRO WHERE PRONUM = $pronum AND PROPAR = '$propar' ";
        $Anapro_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Anapro_rec['PROIDMAILDEST']) {
            $ElencoIDMail[$Anapro_rec['PROIDMAILDEST']] = $Anapro_rec['PROIDMAILDEST'];
        }

        return $ElencoIDMail;
    }

    public function NotificaMailDestinatariProtocollo($model, $Pronum, $Propar, $ForzaMail = '', $oggettoCustom = '', $bodyCustom = '') {
        $DestMap = array();
        $retNotifica = array();
        /*
         * Lettura del protocollo: con visibilità?
         */
        $Anapro_rec = $this->proLib->GetAnapro($Pronum, 'codice', $Propar);
        //$Anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $Pronum, $Propar);
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo non trovato.");
            $retNotifica['STATO'] = 0;
            return $retNotifica;
        }
        $Anaogg_rec = $this->proLib->GetAnaogg($Pronum, $Propar);
        /*
         * Carico Altri Destinatari: se mail non inviata
         */
        $AltriDestinatari = $this->proLib->caricaAltriDestinatari($Pronum, $Propar, false);
        foreach ($AltriDestinatari as $i => $destinatario) {
            if ($destinatario['DESMAIL'] != '' && $destinatario['ROWID'] != '') {
                if (!$destinatario['DESIDMAIL']) {
                    $destMap_rec = array();
                    $destMap_rec['MAIL'] = $destinatario['DESMAIL'];
                    $destMap_rec['NOME'] = $destinatario['DESNOM'];
                    $destMap_rec['TIPO'] = "proAltriDestinatari";
                    $destMap_rec['indice'] = $i;
                    $DestMap[] = $destMap_rec;
                }
            }
        }
        /*
         * Carico Destinatario Principale: se mail non inviata.
         */
        if ($Anapro_rec['PROMAIL'] != '') {
            if (!$Anapro_rec['PROIDMAILDEST']) {
                $destMap_rec = array();
                $destMap_rec['MAIL'] = $Anapro_rec['PROMAIL'];
                $destMap_rec['NOME'] = $Anapro_rec['PRONOM'];
                $destMap_rec['TIPO'] = "proArri-P";
                $destMap_rec['indice'] = "";
                $DestMap[] = $destMap_rec;
            }
        }
        /*
         * Preparo Array Allegati.
         */
        $proArriAlle = $this->proLib->caricaAllegatiProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $allegati = array();
        foreach ($proArriAlle as $allegato) {
            if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0) {
                // Sovrascrivo la path. Attenzione, potrebbero occupare molto spazio le copie prima dell'invio.
                $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], '', true);
                $allegato['FILEPATH'] = $CopyPathFile;
                $allegati[] = $allegato;
            }
        }
        /*
         * Controllo se occorre inviare anche la segnatura.
         */
        $anaent_rec = $this->proLib->GetAnaent('35');
        if ($anaent_rec['ENTDE5'] != '1') {
            if ($Propar == 'P' || $Propar == 'C') {
                $anaent_38 = $this->proLib->GetAnaent('38');
                /*
                 * Per le fatture elettroniche non deve esserci la Sengatura.
                 */
                if ($Anapro_rec['PROCODTIPODOC'] &&
                        ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4'] ||
                        $Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE2'] ||
                        $Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] ||
                        $Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'])) {
                    
                } else {
                    $segnatura = $this->proLibAllegati->ScriviFileXML($Anapro_rec['ROWID']);
                    if ($segnatura['stato'] == '-1') {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore creazione segnatura. " . $segnatura['messaggio']);
                        $retNotifica['STATO'] = 0;
                        return $retNotifica;
                    } else if ($segnatura['stato'] == '-2') {
                        $retNotifica['ANOMALIA'] = 'Creazione Segnatura. ' . $segnatura['messaggio'];
                    } else {
                        $allegati[] = array('FILEPATH' => $segnatura, 'FILENAME' => 'Segnatura.xml', 'FILEINFO' => 'Segnatura.xml');
                    }
                }
            }
        }
        /*
         * Setto parametri per invio mail:
         */
        $tipoProtoc = "PROTOCOLLO IN ARRIVO";
        if ($Propar == 'P') {
            $tipoProtoc = "PROTOCOLLO IN PARTENZA";
        } else if ($Propar == 'C') {
            $tipoProtoc = "DOC.FORMALE";
        }
        $Oggetto = "$tipoProtoc - " . $Anapro_rec['PROSEG'];
        if ($oggettoCustom) {
            $Oggetto = $oggettoCustom;
        }

        $CorpoMail = $Anaogg_rec['OGGOGG'];
        if ($bodyCustom) {
            $CorpoMail = $bodyCustom;
        }

        $valori = array(
            'destMap' => $DestMap,
            'Oggetto' => $Oggetto,
            'Corpo' => $CorpoMail,
            'allegati' => $allegati
        );
        if (!$DestMap) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non esistono indirizzi PEC/Email a cui inviare la notifica. ");
            $retNotifica['STATO'] = 2;
            return $retNotifica;
        }
        //Forza mail per ora non contemplato.
        // Spacchettamenteo fattura per ora non contemplato..
        $result = $this->servizioInvioMail($model, $valori, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], array(), array(), $AltriDestinatari, $ForzaMail);
        if (!$result) {
            $retNotifica['STATO'] = 0;
            return $retNotifica;
        }

        /*
         * Invio Avvenuto:
         */
        $retNotifica['STATO'] = 1;
        return $retNotifica;
    }

    public function GetElementiTemplateMail($anapro_rec, $rigaTemplate, $compila = false) {

        $anaent_29 = $this->proLib->GetAnaent('29');
        $anaent_53 = $this->proLib->GetAnaent('53');

        $ElencoTemplateMail = unserialize($anaent_53['ENTVAL']);

        /*
         * Contenuti
         */
        $ContenutoBody = $ContenutoOggetto = '';
        if ($ElencoTemplateMail[$rigaTemplate]) {
            /* Contenuto Body */
            $ContenutoBody = $ElencoTemplateMail[$rigaTemplate]['BODYMAIL'];
            if ($compila == true) {
                $ContenutoBody = $this->SostituisciContenutoVariabili($anapro_rec, $ContenutoBody);
            }
            /* Contenuto Oggetto */
            $ContenutoOggetto = $ElencoTemplateMail[$rigaTemplate]['OGGETTOMAIL'];
            if ($compila == true) {
                $ContenutoOggetto = $this->SostituisciContenutoVariabili($anapro_rec, $ContenutoOggetto);
            }
        }

        $ArrElementi = array();
        $ArrElementi['OGGETTOMAIL'] = $ContenutoOggetto;
        $ArrElementi['BODYMAIL'] = $ContenutoBody;

        return $ArrElementi;
    }

    public function SostituisciContenutoVariabili($anapro_rec, $Contenuto) {
        $proLibVar = new proLibVariabili();
        $proLibVar->setAnapro_rec($anapro_rec);
        $proLibVar->setCodiceProtocollo($anapro_rec['PRONUM']);
        $proLibVar->setTipoProtocollo($anapro_rec['PROPAR']);
        $dictionaryValues = $proLibVar->getVariabiliProtocollo()->getAllData();
        $wsep = '';
        foreach ($dictionaryValues as $key => $valore) {
            $search = '@{$' . $key . '}@';
            if ($valore) {
                if (strpos($Contenuto, $search) === 0) {
                    $wsep = '';
                }
            } else {
                $wsep = '';
            }
            $replacement = $wsep . $valore;
            $Contenuto = str_replace($search, $replacement, $Contenuto);
        }

        if (strpos($Contenuto, '@{$') !== false) {
            return false;
        }
        if (strpos($Contenuto, '}@') !== false) {
            return false;
        }
        return $Contenuto;
    }

    public function GetMailInteroperabili() {
        $oggi = date('Ymd');
        $ITALWEB_DB = $this->emlLib->getITALWEB();
        $sql = "SELECT *,";
        $sql .= $ITALWEB_DB->dateDiff(
                        $ITALWEB_DB->coalesce("'$oggi'"), 'MSGDATE'
                ) . " AS GIORNI ";
        $sql .= " FROM MAIL_ARCHIVIO WHERE " .
                " CLASS ='@DA_PROTOCOLLARE@' AND INTEROPERABILE > 0 ";
        return ItaDB::DBSQLSelect($this->emlLib->getITALWEB(), $sql, true);
    }

    public function ElaboraMailInteroperabili() {
        $AccountMail = $this->proLib->GetParametriAccountMail();
        $this->mailProtocollate = array();
        $this->mailInteropAssegnate = array();
        $this->mailErrore = array();
        $MailArchivio_tab = $this->GetMailInteroperabili();
        foreach ($MailArchivio_tab as $MailArchivio_rec) {
            /*
             * Controllo Pec Tipo da Escludere:
             */
            switch ($MailArchivio_rec['PECTIPO']) {
                case emlMessage::PEC_TIPO_PRESA_IN_CARICO:
                case emlMessage::PEC_TIPO_NON_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_PREAVVISO_ERRORE_CONSEGNA:
                case emlMessage::PEC_TIPO_RILEVAZIONE_VIRUS:
                case emlMessage::PEC_TIPO_ACCETTAZIONE:
                case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                    continue;
                    break;
                default :
                    break;
            }
            /*
             * Controllo se account 
             * Abilitato a protocollazione.
             */
            $MailAcc = $MailArchivio_rec['ACCOUNT'];
            if ($AccountMail[$MailAcc]['PROT_AUTO'] == true) {
                /*
                 * Controllo tipo interoperabilita
                 *  "Segnatura" protocollabile
                 */
                $MailAnalizzata = array(
                    'Mittente' => $MailArchivio_rec['FROMADDR'],
                    'Oggetto' => $MailArchivio_rec['SUBJECT'],
                    'Data' => $MailArchivio_rec['MSGDATE']);
                switch ($MailArchivio_rec['TIPOINTEROPERABILE']) {
                    case proInteropMsg::TIPOMSG_SEGNATURA:
                        /*
                         * Provvisoriamente commentato: Per ora interoperabile la conferma prot.
                          $retMail = $this->ProtocollaMail($MailArchivio_rec['IDMAIL']);
                          if (!$retMail) {
                          $this->mailErrore[$MailArchivio_rec['IDMAIL']] = array_merge($MailAnalizzata, array('Errore' => $this->errMessage));
                          } else {
                          $this->mailProtocollate[$MailArchivio_rec['IDMAIL']] = $retMail;
                          }
                         * 
                         */
                        break;

                    case proInteropMsg::TIPOMSG_CONFERMA:
                        $retAssegna = $this->AssegnaInteroperabileAProtocollo($MailArchivio_rec);
                        if (!$retAssegna) {
                            $this->mailErrore[$MailArchivio_rec['IDMAIL']] = array_merge($MailAnalizzata, array('Errore' => $this->errMessage));
                        } else {
                            $this->mailInteropAssegnate[$MailArchivio_rec['IDMAIL']] = $retAssegna;
                        }
                        break;

                    case proInteropMsg::TIPOMSG_ECCEZIONE:
                    case proInteropMsg::TIPOMSG_AGGIORNAMENTO:
                    case proInteropMsg::TIPOMSG_ANNULLAMENTO:
                        // Assegna eml.
                        break;
                }
            }
        }
        return true;
    }

    /*
     * Funzione di prova..
     */

    public function ProtocollaMail($idMail) {
        //TEST!
        include_once ITA_BASE_PATH . '/apps/Protocollo/proMail.class.php';
        $currObjMail = proMail::getInstance($idMail);
        if (!$currObjMail) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Protocollazione Mail. Oggetto non istanziato.');
            return false;
        }
        $erCode = $currObjMail->getErrCode();
        if ($erCode == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Elaborazione Mail. ' . $currObjMail->getErrMessage());
            return false;
        }
        /*
         * Protocollo la Mail
         */
        $this->setClasseMail($idMail, self::IN_PROTOCOLLAZIONE);
        if (!$currObjMail->ProtocollaMail()) {
            $resultProt = $currObjMail->getResultProt();
            /**
             * Se non ha creato un ANAPRO
             * è riprotocollabile.
             */
            if (!$resultProt['ANAPRO_CREATO']) {
                $this->setClasseMail($idMail, self::DA_PROTOCOLLARE);
            }
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Protocollazione Mail. ' . $currObjMail->getErrMessage());
            return false;
        }
        $resultProt = $currObjMail->getResultProt();
        /*
         * Invia Conferma Ricezione:
         */
        $Anapro_rec = $this->proLib->GetAnapro($resultProt['rowidProtocollo'], 'rowid');
        if (!$this->CheckInvioConfermaRicezione($Anapro_rec, $currObjMail->getDatiSegnatura())) {
            $resultProt['Avviso'] = $this->getErrMessage();
        }
        $currObjMail->cleanData();
        return $resultProt;
    }

    public function collegaAllegatiMail($rowidMail, $pronum, $propar) {
        $mail_rec = $this->emlLib->getMailArchivio($rowidMail, 'rowid');
        if ($mail_rec) {
            $anadoc_tab = $this->proLib->GetAnadoc($pronum, 'codice', true, $propar);
            foreach ($anadoc_tab as $anadoc_rec) {
                try {
                    $anadoc_rec['DOCIDMAIL'] = $mail_rec['IDMAIL'];
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $anadoc_rec);
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in aggiornamento ANADOC. " . $exc->getMessage());
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $pronum
     * @param type $propar
     * @return boolean
     */
    public function InviaConfermaRicezione($pronum, $propar, $inviaFileConferma = true) {
        /*
         * Leggo il Protocollo
         */
        $SegnaturaXml = array();
        $FileConferma = '';
        $Anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo $pronum $propar non trvoato.");
            return false;
        }
        /*
         * Preparazione File
         */
        if ($inviaFileConferma) {
            $chiaveProtocollo = array('PRONUM' => $Anapro_rec['PRONUM'], 'PROPAR' => $Anapro_rec['PROPAR']);
            $InteropMsg = proInteropMsg::getInteropInstanceUscita($chiaveProtocollo, proInteropMsg::TIPOMSG_CONFERMA);
            if ($InteropMsg->getErrCode() == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura File. " . $InteropMsg->getErrMessage());
                return false;
            }

            $FileConferma = $InteropMsg->getPathFileMessaggio();
            $basename = pathinfo($FileConferma, PATHINFO_BASENAME);
            // Lettura parametri da parent.
            $InteropMsgParent = $InteropMsg->getSegnaturaParent();
            if (!$InteropMsgParent) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura messaggio segnatura interoperabile.");
                return false;
            }

            /*
             *  Leggo Array XML:
             */
            $ArrayXml = $InteropMsgParent->getXMLArray();
            $SegnaturaXml = $ArrayXml['Segnatura'];
        }
        /*
         *  Leggo account e destinatario.
         *  Default da Mail Archivio.
         */
        $MailArchivioProt_rec = $this->proLib->GetMailArchivioProtocollo($pronum, $propar);
        $Account = $MailArchivioProt_rec['ACCOUNT'];

        $Destinatario = $MailArchivioProt_rec['FROMADDR'];
        $metadata = unserialize($MailArchivioProt_rec["METADATA"]);
        if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
            if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                $Destinatario = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'];
            }
        }

        if (isset($SegnaturaXml['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode']) && $SegnaturaXml['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'] != '') {
            $Destinatario = $SegnaturaXml['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'];
        }
        /*
         * Lettura oggetto e corpo mail.
         */
        $ElementiMail = $this->GetElementiTemplateMail($Anapro_rec, 1, true);
        $OggMail = 'Conferma di Ricezione';
        $CorpoMail = 'Messaggio di conferma di ricezione email.';
        if ($ElementiMail['OGGETTOMAIL']) {
            $OggMail = $ElementiMail['OGGETTOMAIL'];
        }
        if ($ElementiMail['BODYMAIL']) {
            $CorpoMail = $ElementiMail['BODYMAIL'];
        }
        $allegati = array();
        if ($FileConferma) {
            $allegati[] = array('FILEPATH' => $FileConferma, 'FILENAME' => $basename, 'FILEINFO' => $basename);
        }
        /*
         * Preparo Dati Mail
         */
        $DatiMail = array();
        $DatiMail['Oggetto'] = $OggMail;
        $DatiMail['Corpo'] = $CorpoMail;
        $DatiMail['Destinatario'] = $Destinatario;
        $DatiMail['allegati'] = $allegati;
        /*
         * Invio la Mail
         */
        $mailArchivio_rec = $this->InvioMail($Account, $DatiMail);
        if (!$mailArchivio_rec) {
            return false;
        }
        /*
         * Preparazione Record Promail
         */
        $promail_rec = array();
        $promail_rec['PRONUM'] = $Anapro_rec['PRONUM'];
        $promail_rec['PROPAR'] = $Anapro_rec['PROPAR'];
        $promail_rec['IDMAIL'] = $mailArchivio_rec['IDMAIL'];
        $promail_rec['SENDREC'] = $mailArchivio_rec['SENDREC'];
        /*
         * Aggiorno Info Su Protocollo:
         */
        try {
            /* Inserimento su Promail: solo se non è interoperabile.
             * Perchè se è interoperabile PROMAIL viene valorizzato 
             * tramite "AssegnaInteroperabileAProtocollo"
             */
            if (!$inviaFileConferma) {
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROMAIL', 'ROWID', $promail_rec);
            }
            /* Collegamento Mail con ANAPRO */
            $Anapro_rec['PROIDMAILDEST'] = $mailArchivio_rec['IDMAIL'];
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $Anapro_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento ANAPRO. " . $exc->getMessage());
            return false;
        }
        if ($inviaFileConferma) {
            if (!$this->AssegnaInteroperabileAProtocollo($mailArchivio_rec, $pronum, $propar)) {
                return false;
            }
            $InteropMsg->CleanTempPath();
        }
        return true;
    }

    /**
     * 
     * @param type $Account
     * @param type $DatiMail
     *     'Oggetto'=> 'Oggetto della Mail'
     *     'Corpo' => 'Corpo della Mail'
     *     'Destinatario' => 'Mail Destinatario'
     *     'allegati' => array( array('FILEPATH' => '', 'FILENAME' => '', 'FILEINFO' => '') )
     * @return boolean
     */
    public function InvioMail($Account = '', $DatiMail = array()) {
        if (!$Account) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro Account Mail mancante.");
            return false;
        }
        $emlMailBox = $emlMailBoxAlt = emlMailBox::getInstance($Account);
        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in istanza Account Mail: $Account.");
            return false;
        }

        $anaent_26 = $this->proLib->GetAnaent('26');
        if ($anaent_26) {
            $ricevutaPECBreve = $anaent_26['ENTVAL'];
        }
        if ($ricevutaPECBreve) {
            $emlMailBox->setPECRicvutaBreve();
        }

        if (!$DatiMail['Destinatario'] || !$DatiMail['Oggetto'] || !$DatiMail['Corpo']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametri Mail mancanti.");
            return false;
        }
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        $outgoingMessage->setSubject($DatiMail['Oggetto']);
        $outgoingMessage->setBody($DatiMail['Corpo']);
        $outgoingMessage->setEmail($DatiMail['Destinatario']);
        $outgoingMessage->setAttachments($DatiMail['allegati']);
        $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);
        if (!$mailArchivio_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage($emlMailBox->getLastMessage());
            return false;
        }

        return $mailArchivio_rec;
    }

    /**
     * 
     * @param type $datiMail
     * @return boolean
     */
    public function AssegnaInteroperabileAProtocollo($datiMail, $pronum = '', $propar = '') {
        if (!$datiMail['TIPOINTEROPERABILE']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Tipologia di Interoperabilità non riconosciuta.');
            return false;
        }
        /*
         * Lettura Oggetto Mail
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proMail.class.php';
        $currObjMail = proMail::getInstance($datiMail['IDMAIL']);
        if (!$currObjMail) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Assegnazione Mail a Protocollo. Oggetto non istanziato.');
            return false;
        }
        $AllegatiMail = $currObjMail->getAllegatiMail();
        $FilePath = '';
        $NomeFile = $datiMail['TIPOINTEROPERABILE'] . '.xml';
        foreach ($AllegatiMail as $Allegato) {
            if (strtolower($Allegato['DATAFILE']) == strtolower($NomeFile)) {
                $FilePath = $Allegato['FILE'];
                break;
            }
        }
        if (!$FilePath) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Assegnazione Interoperabile a Protocollo. File xml non trovato.');
            return false;
        }
        /*
         * Se non è stato indicato il protocollo:
         * Lo ricavo tramite oggetto interoperabile
         */
        if (!$pronum || !$propar) {
            $proInteropMsg = proInteropMsg::getInteropInstanceEntrata($FilePath, $NomeFile);
            /*
             * Controllo Errori.
             */
            if ($proInteropMsg->getErrCode() == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura File. " . $proInteropMsg->getErrMessage());
                return false;
            }
            $InteropMsgParent = $proInteropMsg->getSegnaturaParent();
            if (!$InteropMsgParent) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura messaggio segnatura interoperabile.");
                return false;
            }
            if ($InteropMsgParent->getErrCode() == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura File Parent. " . $proInteropMsg->getErrMessage());
                return false;
            }
            /*
             * Ricavo Anapro Parent
             */
            $Anapro_Rec = $InteropMsgParent->getAnapro_record();
        } else {
            /*
             * Altrimenti è già stato indicato il protocollo.
             */
            $Anapro_Rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        }
        if (!$Anapro_Rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiunta allegato a protocollo. Protocollo inesistente. ");
            return false;
        }
        $chiaveProtocollo = array('PRONUM' => $Anapro_Rec['PRONUM'], 'PROPAR' => $Anapro_Rec['PROPAR']);

        $elementi = array();
        $elementi['tipo'] = $Anapro_Rec['PROPAR'];
        $elementi['dati'] = array();

        $allegato = array();
        $allegato['Documento']['Nome'] = $NomeFile;
        $allegato['Documento']['Stream'] = $base64 = base64_encode(file_get_contents($FilePath));
        $allegato['Documento']['Descrizione'] = $NomeFile;
        $allegato['Documento']['DocServizio'] = 1;
        $allegato['Documento']['DocIDMail'] = $datiMail['IDMAIL'];
        $allegato['Documento']['ForzaTipoDoc'] = 'INTEROP_MSG';

        $elementi['allegati']['Allegati'][] = $allegato;
        /*
         * Istanza Oggetto ProtoDatiProtocollo
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proDatiProtocollo.class.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Utilizzo il protocollatore per aggiungere l'allegato
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proProtocolla.class.php';
        $proProtocolla = new proProtocolla();

        $motivo = 'Aggiunta allegato di servizio: Interoperabile. ';
        $addAllegato = $proProtocolla->aggiungiAllegati($Anapro_Rec['PROPAR'], $motivo, $proDatiProtocollo, $Anapro_Rec['PRONUM']);
        if (!$addAllegato) {
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        $retRitorno = $proProtocolla->getRisultatoRitorno();
        $rowdAllegato = $retRitorno['ROWIDAGGIUNTI'][0];
        /*
         * Assegno il Mail al protocollo.
         */
        if (!$this->AssegnaMailAProtocollo($chiaveProtocollo, $datiMail['IDMAIL'])) {
            return false;
        }
        if ($proInteropMsg) {
            $proInteropMsg->CleanTempPath();
        }
        return $rowdAllegato;
    }

    /**
     * 
     * @param type $chiaveProtocollo
     * @param type $idMail
     * @return boolean
     */
    public function AssegnaMailAProtocollo($chiaveProtocollo = array(), $idMail = '') {
        if (!$chiaveProtocollo['PRONUM'] || !$chiaveProtocollo['PROPAR'] || !$idMail) {
            $this->setErrCode(-1);
            $this->setErrMessage('Parametri per assegnazione mancanti.');
            return false;
        }
        /*
         * Lettura MailArchivio.
         */
        $datiMail = $this->emlLib->getMailArchivio($idMail, 'id');
        $emlDbMailBox = new emlDbMailBox();
        $risultatoDb = $emlDbMailBox->updateClassForRowId($datiMail['ROWID'], self::PROTOCOLLATO);
        if ($risultatoDb === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento classe mail. " . $emlDbMailBox->getLastMessage());
            return false;
        }
        $promail_rec = array(
            'PRONUM' => $chiaveProtocollo['PRONUM'],
            'PROPAR' => $chiaveProtocollo['PROPAR'],
            'IDMAIL' => $datiMail['IDMAIL'],
            'SENDREC' => $datiMail['SENDREC']
        );
        $insert_Info = 'Inserimento Eml Mail: ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'];
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $insert_Info));
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'PROMAIL', 'ROWID', $promail_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento PROMAIL. " . $exc->getMessage());
            return false;
        }
        return true;
    }

    /*
     * FUNZIONE DI PROVA DA RIMUOVERE!
     */

    public function TestSegnature($Anapro_record, $proArriAlle) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proInteropMsg.class.php';

        if ($Anapro_record['PROPAR'] == 'A') {
            // !!! TEST DA RIMUOVERE !!
            /*
             * TEST CONFERMA RICEZIONE
             */
            $chiaveProtocollo = array();
            $chiaveProtocollo['PRONUM'] = $Anapro_record['PRONUM'];
            $chiaveProtocollo['PROPAR'] = $Anapro_record['PROPAR'];
            $InteropMsg = proInteropMsg::getInteropInstanceUscita($chiaveProtocollo, proInteropMsg::TIPOMSG_CONFERMA);
            $basename = pathinfo($InteropMsg->getPathFileMessaggio(), PATHINFO_BASENAME);
            Out::openDocument(utiDownload::getUrl($basename, $InteropMsg->getPathFileMessaggio()));
            //                        Out::msgInfo('interop', print_r($InteropMsg, true));

            /*
             * 
             * TEST SEGNATURA DA ANALIZZARE
             * */
            foreach ($proArriAlle as $anadoc_rec) {
                if ($anadoc_rec['DOCNAME'] == 'Segnatura.xml') {
                    break;
                }
            }

            $randPath = itaLib::getRandBaseName();
            $pathTmp = itaLib::getAppsTempPath("proTestSegnature-$randPath");
            if (!@is_dir($pathTmp)) {
                if (!itaLib::createAppsTempPath("proTestSegnature-$randPath")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita.");
                    return false;
                }
            }

            $FileDest = $pathTmp . '/Segnatura.xml';
            $FileSegnatura = $this->proLibAllegati->CopiaDocAllegato($anadoc_rec['ROWID'], $FileDest);
            $InteropMsg = proInteropMsg::getInteropInstanceEntrata($FileSegnatura);
            if ($InteropMsg->getErrCode() == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura File. " . $InteropMsg->getErrMessage());
                return false;
            }

            $Arrayxml = $InteropMsg->getXMLArray();
            Out::msgInfo('arrayxml', print_r($Arrayxml, true));
            /// !! FINE TEST DA RIMUOVERE 
        } else {
            $chiaveProtocollo = array();
            $chiaveProtocollo['PRONUM'] = $Anapro_record['PRONUM'];
            $chiaveProtocollo['PROPAR'] = $Anapro_record['PROPAR'];
            $InteropMsg = proInteropMsg::getInteropInstanceUscita($chiaveProtocollo, proInteropMsg::TIPOMSG_SEGNATURA);
            $basename = pathinfo($InteropMsg->getPathFileMessaggio(), PATHINFO_BASENAME);
            Out::openDocument(utiDownload::getUrl($basename, $InteropMsg->getPathFileMessaggio()));
        }
    }

    public function CheckInvioConfermaRicezione($Anapro_rec, $datiSegnatura = array()) {
        $anaent_54 = $this->proLib->GetAnaent('54');
        if ($anaent_54['ENTDE4'] == '') {
            return true;
        }
        if ($datiSegnatura['RICHIESTA_CONFERMA'] == 'si' || $anaent_54['ENTDE4'] == '1') {
            if (!$this->InviaConfermaRicezione($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                $this->setErrMessage("Errore in invio Conferma Ricezione: " . $this->getErrMessage());
                return false;
            }
        }
        return true;
    }

    public function setClasseMail($idMail, $Stato = '') {
        if (!$Stato) {
            $this->setErrCode(-1);
            $this->setErrMessage('Stato mail non può essere vuoto.');
            return false;
        }
        $mail_rec = $this->emlLib->getMailArchivio($idMail, 'id');
        $emlDbMailBox = new emlDbMailBox();
        if (!$emlDbMailBox->updateClassForRowId($mail_rec['ROWID'], $Stato)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in aggiornamento stato Mail. ' . $emlDbMailBox->getLastMessage());
            return false;
        }
        return true;
    }

    public function lockMail($rowidMail) {
        $retLock = ItaDB::DBLock($this->emlLib->getITALWEB(), "MAIL_ARCHIVIO", $rowidMail, "", 20);
        if ($retLock['status'] != 0) {
            $this->setErrCode(-1);
            $this->setErrMessage("La PEC/Mail risulta già protocollazione da un altro utente.<br>Impossibile accedere in modo esclusivo al messaggio.");
            return false;
        }
        return $retLock;
    }

    public function lockMailVisualizzazione($rowidMail) {
        $retLock = ItaDB::DBLock($this->emlLib->getITALWEB(), "MAIL_ARCHIVIO_VISUALIZZAZIONE", $rowidMail);
        if ($retLock['status'] != 0) {
            $this->setErrCode(-1);
            $this->setErrMessage("La PEC/Mail è stata aperta in visualizzazione/protocollazione da un altro utente.");
            return false;
        }
        return $retLock;
    }

    public function unlockMail($retLock) {
        if (empty($retLock)) {
            return false;
        }
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            $this->setErrMessage($this->getErrMessage() . ' - Sblocco della PEC/Mail non riuscito.');
            return false;
        }
        return true;
    }

    public function checkMailProtocollata($idMail) {
        // Controllo su ANAPRO
        $sql = "SELECT * FROM ANAPRO WHERE PROIDMAIL = '$idMail' AND PROIDMAIL <> '' ";
        $Anapro_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Anapro_rec) {
            // Se il protocollo è annullato posso riprotocollarla.
            if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                return false;
            }
            return $Anapro_rec;
        }
        // Controllo su andadoc
        $sql = "SELECT * FROM ANADOC WHERE DOCIDMAIL = '$idMail' AND DOCIDMAIL <> ''";
        $Anadoc_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Anadoc_rec) {
            $Anapro_rec = $this->proLib->GetAnapro($Anadoc_rec['DOCNUM'], 'codice', $Anadoc_rec['DOCPAR']);
            return $Anapro_rec;
        }

        return false;
    }

    public function GetElencoNotifiche($idMailPadre, $escludiAccCons = false, $escludiPadre = false) {
        $emlLib = new emlLib();
        $ElencoMail = array();
        /*
         *  Lettura Mail Padre
         */
        $mailPadreArchivio_rec = $emlLib->getMailArchivio($idMailPadre, 'id');
        $ElencoMail[] = $mailPadreArchivio_rec;
        /*
         * Estrazione Figli della mail Padre
         */
        $mailArchivio_tab = $emlLib->getMailArchivio($idMailPadre, 'idmailpadre');
        foreach ($mailArchivio_tab as $key => $mailArchivio_rec) {
            if ($escludiAccCons) {
                if ($mailArchivio_rec['PECTIPO'] == emlMessage::PEC_TIPO_ACCETTAZIONE || $mailArchivio_rec['PECTIPO'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                    continue;
                }
            }
            $ElencoMail[] = $mailArchivio_rec;
        }
        return $ElencoMail;
    }

    public function GetBase64NotificaProtocollo($rowidEmail, $pronum, $propar) {
        $emlLib = new emlLib();
        $mail = $emlLib->getMailArchivio($rowidEmail, 'rowid');
        if (!$mail) {
            $this->setErrCode(-1);
            $this->setErrMessage("Mail non trovata in archivio mail.");
            return false;
        }
        $CheckIDMail = $CheckIDMailPadre = false;
        $CheckIDMail = $this->CheckIdMailProtocollo($mail['IDMAIL'], $pronum, $propar);
        if ($mail['IDMAILPADRE']) {
            $CheckIDMailPadre = $this->CheckIdMailProtocollo($mail['IDMAILPADRE'], $pronum, $propar);
        }

        if (!$CheckIDMail && !$CheckIDMailPadre) {
            $this->setErrCode(-1);
            $this->setErrMessage("La notifica mail non appartiene al protocollo selezionato.");
            return false;
        }

        /*
         * Ritorno il file:
         */
        return base64_encode(file_get_contents($emlLib->SetDirectory($mail['ACCOUNT']) . $mail['DATAFILE']));
    }

    public function CheckIdMailProtocollo($idMail, $pronum, $propar) {
        /*
         * Controllo se è un idmail di questo protocollo
         */
        $sql = "SELECT ROWID FROM ANAPRO WHERE PRONUM =$pronum AND PROPAR = '$propar' AND PROIDMAILDEST = '" . $idMail . "' ";
        $Anapro_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Anapro_rec) {
            return true;
        }
        $sql = "SELECT ROWID FROM ANADES WHERE DESNUM =$pronum AND DESPAR = '$propar' AND DESIDMAIL = '" . $idMail . "' ";
        $Anades_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Anades_rec) {
            return true;
        }
        $sql = "SELECT ROWID FROM PROMITAGG WHERE PRONUM =$pronum AND PROPAR = '$propar' AND PROIDMAILDEST = '" . $idMail . "' ";
        $Promittagg_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($Promittagg_rec) {
            return true;
        }
        return false;
    }

    public function SendMailErrore($Errore, $subject = null, $bodyHeader = null) {
        $Account = '';
        $devLib = new devLib();
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        //$ItaEngine_mail = $devLib->getEnv_config_global_ini('ITAENGINE_EMAIL');
        $anaent_2 = $this->proLib->GetAnaent('2');
        if ($subject === null) {
            $subject = 'Errore.';
        }
        if ($bodyHeader === null) {
            $bodyHeader.= ' si è verificato un errore durante la procedura automatica.<br>';
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

    public function StampaMailDiConsegnaProtocollo($pronum, $propar) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        $docLib = new docLib();

        $randPath = itaLib::getRandBaseName();
        $pathTmp = itaLib::getAppsTempPath("proStampaMail-$randPath");
        if (!@is_dir($pathTmp)) {
            if (!itaLib::createAppsTempPath("proStampaMail-$randPath")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }

        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if (!$anapro_rec) {
            return false;
        }
        $ElencoIdMail = array();
        if ($anapro_rec['PROIDMAILDEST']) {
            /*
             * Estrazione Figli
             */
            $ChildMail_tab = $this->emlLib->getMailArchivio($anapro_rec['PROIDMAILDEST'], 'idmailpadre');
            foreach ($ChildMail_tab as $ChildMail_rec) {
                if ($ChildMail_rec['PECTIPO'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                    $IdMailProt = $ChildMail_rec['IDMAIL'];
                    $ElencoIdMail[$IdMailProt] = $anapro_rec['PROMAIL'];
                    break;
                }
            }
        }

        $Anades_tab = $this->proLib->GetAnades($pronum, 'codice', true, $propar);
        foreach ($Anades_tab as $Anades_rec) {
            if ($Anades_rec['DESIDMAIL']) {
                $ChildMail_tab = $this->emlLib->getMailArchivio($Anades_rec['DESIDMAIL'], 'idmailpadre');
                foreach ($ChildMail_tab as $ChildMail_rec) {
                    if ($ChildMail_rec['PECTIPO'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                        $IdMailProt = $ChildMail_rec['IDMAIL'];
                        $ElencoIdMail[$IdMailProt] = $Anades_rec['DESMAIL'];
                        break;
                    }
                }
            }
        }
        //Contemplare caso in cui non è presente?
        //Scorro Mail:
        $i = 0;
        $ElencoAllegati = array();
        foreach ($ElencoIdMail as $IDMail => $Mail) {
            $i++;
            $mail_rec = $this->emlLib->getMailArchivio($IDMail, 'id');

            $this->clearCurrMessage();
            $currMailBox = new emlMailBox();
            $this->currMessage = $currMailBox->getMessageFromDb($mail_rec['ROWID']);
            $this->currMessage->parseEmlFileDeep();
            $retDecode = $this->currMessage->getStruct();
            $arrayMsgOriginale = $this->GetDatiStampaEmailOriginale($retDecode);
            if (is_array($retDecode['ita_PEC_info']['messaggio_originale'])) {
                $DatiMsgOriginale = $this->GetDatiStampaEmailOriginale($retDecode['ita_PEC_info']['messaggio_originale']['ParsedFile']);
            }


            if (file_exists($arrayMsgOriginale['Corpo'])) {
                $corpo = file_get_contents($arrayMsgOriginale['Corpo']);
            } else {
                $corpo = "<h1>Corpo Mail non visualizzabile......</h1>";
            }
            $parameters = array("AllegatiOrig" => $DatiMsgOriginale['Allegati'], "Allegati" => $arrayMsgOriginale['Allegati'], "Destinatario" => $arrayMsgOriginale['Destinatario'], "Mittente" => $arrayMsgOriginale['Mittente'], "Oggetto" => $arrayMsgOriginale['Oggetto'], "Data" => $arrayMsgOriginale['Data'], "Corpo" => $corpo);
            $report = 'emlViewer';
            $HTMLName = $this->getHtmlRicevuta($report, $parameters);
            $nomeFile = 'Ricevuta_di_Consegna_' . $Mail . '.pdf';
            $OutPutFile = $pathTmp . '/' . $nomeFile;

            $RetFile = $docLib->Xhtml2Pdf(file_get_contents($HTMLName), array(), $OutPutFile, false);
            if (!$RetFile) {
                $this->setErrCode(-1);
                $this->setErrMessage($docLib->getErrMessage());
                return false;
            }
            $ElencoAllegati[$nomeFile] = $OutPutFile;
        }


        /*
         * Se il file zip esiste, lo cancello
         */

        $NomeFileZip = "Mail_Prot_" . $pronum . '-' . $propar . ".zip";
        $fileZip = $pathTmp . '/' . $NomeFileZip;
        if (file_exists($fileZip)) {
            if (!@unlink($fileZip)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in cancellazione file ZIP.');
                return false;
            }
        }
        /*
         * Creo il file zip
         */
        $archiv = new ZipArchive();
        if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Creazione file ZIP.');
            return false;
        }
        /*
         * Carica Allegati:
         */
        foreach ($ElencoAllegati as $NomeFile => $elemento) {
            $archiv->addFromString($NomeFile, file_get_contents($elemento));
        }
        $archiv->close();

        Out::openDocument(utiDownload::getUrl($NomeFileZip, $fileZip));
        return true;
    }

    function GetDatiStampaEmailOriginale($messaggioOriginale) {
        $arrayMsgOriginale = array();
        foreach ($messaggioOriginale['From'] as $address) {
            $arrayMsgOriginale['Mittente'] = $address['address'];
        }
        foreach ($messaggioOriginale['To'] as $address) {
            $arrayMsgOriginale['Destinatario'] = $address['address'];
        }
        foreach ($messaggioOriginale['Attachments'] as $allegato) {
            $arrayMsgOriginale['Allegati'] .= "- " . $allegato['FileName'] . "<br>";
        }
        $arrayMsgOriginale['Oggetto'] = $messaggioOriginale['Subject'];
        $arrayMsgOriginale['Data'] = date('Ymd', strtotime($messaggioOriginale['Date']));

        $datafile = '';
        if (isset($messaggioOriginale['DataFile'])) {
            $datafile = $messaggioOriginale['DataFile'];
        } else {
            foreach ($messaggioOriginale['Alternative'] as $value) {
                $datafile = $value['DataFile'];
            }
        }
        $arrayMsgOriginale['Corpo'] = $datafile;
        return $arrayMsgOriginale;
    }

    function getHtmlRicevuta($report, $record = array()) {

        $record['Corpo'] = $this->ProteggiTagTestoMail($record['Corpo']);
        if (App::$utente->getKey('nomeUtente') == 'italsoft') {
            //$record['Corpo']='';	
        }
        $html = '
              <br>
              <div style="width:210mm;">
              <div style="display:inline-block; width: 80%; margin: auto;"><h2 style="text-align:center;">RIEPILOGO MAIL</h2></div><br>
              <div style="display:inline-block; width: 15%;">' . date('d/m/Y') . '</div>
              <p><b>Mittente:</b> ' . $record['Mittente'] . '</p>
              <p><b>Destinatario:</b> ' . $record['Destinatario'] . '</p>
              <p><b>Oggetto:</b> ' . $record['Oggetto'] . '</p>
              <p><b>Data:</b> ' . date('d/m/Y', strtotime($record['Data'])) . '</p>
              <br><br>
              <div>' . $record['Corpo'] . '</div>
              <br><br>
              <p><b>Allegati:</b></p>
              <pre>' . print_r($record['Allegati'], true) . '</pre>
              <br>
              <p><b>Allegati Mail Originale:</b></p>
              <pre>' . print_r($record['AllegatiOrig'], true) . '</pre>
              </div>
  ';

        $patterns = array();
        $replacements = array();
        $patterns[] = preg_quote('/<hr>/');
        $replacements[] = '<hr />';
        $html = preg_replace($patterns, $replacements, $html);

        $HTMLName = itaLib::createAppsTempPath('eml-Mail-stmp') . '/' . App::$utente->getKey('TOKEN') . "-" . $report . ".html";
        $ptr = fopen($HTMLName, 'wb');
        fwrite($ptr, $html);
        fclose($ptr);
        return $HTMLName;
    }

    public function ProteggiTagTestoMail($TestoMail) {
        //return $TestoMail;

        $arrayTag = array('head', 'html', 'div');
        foreach ($arrayTag as $tag) {
            $countOpen = substr_count($TestoMail, "<$tag>");
            $countClose = substr_count($TestoMail, "</$tag>");
            if ($countOpen != $countClose) {
                $diff = $countOpen - $countClose;
                if ($diff > 0) {
                    $TestoMail = str_replace("<$tag>", '', $TestoMail, $diff);
                } else {
                    $TestoMail = str_replace("</$tag>", '', $TestoMail, abs($diff));
                }
            }
        }

        return $TestoMail;
    }

    public function CheckStatoMail($IdMail = '') {
        $MailArchivio_rec = $this->emlLib->getMailArchivio($IdMail);
        switch ($MailArchivio_rec['STATOEML']) {
            case 2:
                $Data = date('d/m/Y', strtotime($MailArchivio_rec['MSGDATE']));
                $Oggetto = $MailArchivio_rec['SUBJECT'];
                $Msg.= "Questa mail è stata <b>Archiviata</b>. <br><br>";
                $Msg.= "<b>Data Mail:</b> $Data<br>";
                $Msg.= "<b>Destinatario:</b> {$MailArchivio_rec['TOADDR']}<br>";
                $Msg.= "<b>Oggetto:</b> $Oggetto<br>";
                $this->mailAvviso = $Msg;
                return false;
                break;
            case 1:
                break;
            case 0:
            default:
                break;
        }
        return true;
    }

}

?>