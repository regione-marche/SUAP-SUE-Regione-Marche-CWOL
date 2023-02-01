<?php

/**
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    05.11.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlDate.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';

class praMessage extends emlMessage {
    /**
     * Libreria di funzioni Generiche per Email delle Pratiche estesa a emlMessage.class
     */

    /**
     * Analizza un messaggio mail per il suap
     * @param emlMessage $emlMessage
     */
    private function analizeMessage($emlMessage) {
        $praLib = new praLib();
        $ret = array(
            "isGeneric" => true,
            "isIntegration" => false,
            "isFrontOffice" => false,
            "isComunica" => false,
            "isRicevuta" => false,
            "isAnnullamento" => false,
            "isParere" => false,
            "infoRicevuta" => array(),
            "infoComunica" => array(),
            "infoFrontOffice" => array(),
            "Status" => 0);

        $originalMessage = $emlMessage;
        if ($emlMessage->isPEC() && $emlMessage->getCertificazione('tipo') == emlMessage::PEC_TIPO_POSTA_CERTIFICATA) {
            if ($emlMessage->getMessaggioOriginaleObj() !== false) {
                $emlMessage = $emlMessage->getMessaggioOriginaleObj();
            }
        }

        //
        // Controllo Comunica Infocamere
        //
        $allegati = $emlMessage->getAttachments();
        $tipo = "Generic";
        foreach ($allegati as $allegato) {
            if (strpos(strtoupper($allegato['FileName']), ".SUAP.XML") !== false) {
                $tipo = "Comunica";
                break;
            }
            if (strpos(strtoupper($allegato['FileName']), "XMLINFO") !== false || strpos(strtoupper($allegato['FileName']), "XMLANN") !== false) {
                $tipo = "FrontOffice";
                break;
            }
        }
        switch ($tipo) {
            case "Generic":
                if ($originalMessage->isPEC()) {
                    switch ($originalMessage->getCertificazione('tipo')) {
                        case emlMessage::PEC_TIPO_ACCETTAZIONE:
                        case emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA:
                            $ret['isRicevuta'] = true;
                            $ret['infoRicevuta']['tipoRicevuta'] = $originalMessage->getCertificazione('tipo');
                            $ret['infoRicevuta']['msgIdRicevuta'] = $originalMessage->getCertificazione('msgid');
                            break;
                        default:
                            break;
                    }
                }
                break;
            case "FrontOffice":
                $fileXML = $allegato['DataFile'];
                $xmlObj = new QXML;
                $xmlObj->setXmlFromFile($fileXML);
                $arrayXml = $xmlObj->getArray();
                $Proric_rec_xml = $arrayXml['ROOT']['PRORIC'];
                foreach ($Proric_rec_xml as $key => $value) {
                    $Proric_rec[$key] = $value['@textNode'];
                }
                foreach ($Proric_rec as $key => $campo) {
                    if ($key == 'RICNUM') {
                        $chiave = $campo;
                        break;
                    }
                }
                if ($chiave) {
                    if (strlen($chiave) != 10) {
                        $ret['isFrontOffice'] = false;
                        $ret['infoFrontOffice']['PRORIC'] = "N/A";
                    } else {
                        $ret['isGeneric'] = false;
                        $ret['isFrontOffice'] = true;
                        $ret['infoFrontOffice']['PRORIC'] = $Proric_rec;
                        if ($Proric_rec['RICRPA']) {
                            $ret['isIntegration'] = true;
                        }
                        if ($Proric_rec['PROPAK']) {
                            $ret['isParere'] = true;
                        }
                    }
                } else {

                    $motivo = $arrayXml['ROOT']['MOTIVO']['@textNode'];
                    if ($motivo) {
                        $ret['isAnnullamento'] = true;
                        $ret['isFrontOffice'] = true;
                        $ret['isGeneric'] = false;
                        $ret['infoFrontOffice']['MOTIVO'] = $motivo;
                        $ret['infoFrontOffice']['RICNUM'] = $arrayXml['ROOT']['RICNUM']['@textNode'];
                        $ret['infoFrontOffice']['RICRPA'] = $arrayXml['ROOT']['RICRPA']['@textNode'];
                        $ret['infoFrontOffice']['DATA'] = $arrayXml['ROOT']['DATA']['@textNode'];
                    }
                }
                break;
            case "Comunica":
                foreach ($allegati as $allegato) {
                    if (strpos(strtoupper($allegato['FileName']), ".SUAP.XML") !== false) {
                        $xmlSUAP = $allegato['FileName'];
                        $xmlObj = new QXML;
                        $xmlObj->setXmlFromFile($allegato['DataFile']);
                        $arrayXml = $xmlObj->getArray();
                        $praticaOriginale = $arrayXml['riepilogo-pratica-suap']['intestazione']['codice-pratica']['@textNode'];
                        if ($praticaOriginale) {

                            /*
                             * In base al Controllo FO nuovo o vecchio, mi ricavo il Proric_rec
                             */
                            $Filent_Rec = $praLib->GetFilent(42);
                            if ($Filent_Rec['FILVAL'] == 1) {
                                $prafolist_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRAFOLIST WHERE FOCODICEPRATICASW = '$praticaOriginale'", false);
                                if ($prafolist_rec) {
                                    $metadati = unserialize($prafolist_rec['FOMETADATA']);
                                    $Proric_rec = $metadati['PRORIC_REC'];
                                }
                            } else {
                                $Proric_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRORIC WHERE CODICEPRATICASW = '$praticaOriginale'", false);
                            }
                        }

                        $ret['isComunica'] = true;
                        $ret['infoComunica']['xmlSuap'] = $xmlSUAP;
                        $ret['infoComunica']['documento-allegato'] = $arrayXml['riepilogo-pratica-suap']['struttura']['modulo']['documento-allegato'];
                        $ret['infoComunica']['codice-pratica'] = $praticaOriginale;
                        if ($Proric_rec) {
                            $ret['isFrontOffice'] = true;
                            $ret['infoFrontOffice']['PRORIC'] = $Proric_rec;
                            $ret['isGeneric'] = false;
                        } else {
                            $ret['infoFrontOffice']['PRORIC'] = "N/A";
                        }
                        break;
                    }
                }
                break;
        }
        return $ret;
    }

    /**
     * ANALIZZA MAIL E SALVA RECORD SU PRAMAIL
     * @param type $emlMessage
     * @param type $idMail
     * @param type $tipoMail
     * @return boolean
     */
    public function savePramailRecord($emlMessage, $idMail, $tipoMail, $rowidarchivio = null) {
        $praLib = new praLib();
        $praMail_rec = array(
            'IDMAIL' => $idMail,
            'TIPOMAIL' => $tipoMail,
            'MAILSTATO' => 'ATTIVA',
            'ISGENERIC' => '',
            'ISINTEGRATION' => '',
            'ISFRONTOFFICE' => '',
            'ISCOMUNICA' => '',
            'ISRICEVUTA' => '',
            'ISANNULLAMENTO' => '',
            'TIPORICEVUTA' => '',
            'MSGIDRICEVUTA' => '',
            'INFOCOMUNICA' => '',
            'INFOFRONTOFFICE' => '',
            'ANALISIMAIL' => '',
            'RICNUM' => '',
            'COMPAK' => '',
            'COMIDMAIL' => '',
            'ISPARERE' => '',
            'CODICEPRATICASW' => '',
        );
        if ($idMail != '') {
            //
            // Analizzo il contenuto del messaggio
            //
            $risultato = $this->analizeMessage($emlMessage);
            if (!$risultato) {
                return false;
            }
            $praMail_rec['ISGENERIC'] = $risultato['isGeneric'];
            $praMail_rec['ISINTEGRATION'] = $risultato['isIntegration'];
            $praMail_rec['ISFRONTOFFICE'] = $risultato['isFrontOffice'];
            $praMail_rec['ISCOMUNICA'] = $risultato['isComunica'];
            $praMail_rec['INFOCOMUNICA'] = serialize($risultato['infoComunica']);
            $praMail_rec['INFOFRONTOFFICE'] = serialize($risultato['infoFrontOffice']);
            $praMail_rec['ISRICEVUTA'] = $risultato['isRicevuta'];
            $praMail_rec['ISANNULLAMENTO'] = $risultato['isAnnullamento'];
            $praMail_rec['ISPARERE'] = $risultato['isParere'];
            $praMail_rec['ANALISIMAIL'] = serialize($risultato);
            if ($risultato['isComunica'] === true) {
                $praMail_rec['CODICEPRATICASW'] = $risultato['infoComunica']['codice-pratica'];
            }
            if ($risultato['isFrontOffice'] === true) {
                $praMail_rec['RICNUM'] = $risultato['infoFrontOffice']['PRORIC']['RICNUM'];
                $variante = false;
                if ($risultato['infoFrontOffice']['PRORIC']['RICPC'] == "1") {
                    $variante = true;
                }
                if ($risultato['isIntegration'] && !$variante) {
                    $Proges_rec = $praLib->GetProges($risultato['infoFrontOffice']['PRORIC']['RICRPA'], "richiesta");
                    if ($Proges_rec) {
                        $praMail_rec['GESNUM'] = $Proges_rec['GESNUM'];
                    }
                } else if ($risultato['isAnnullamento']) {
                    $praMail_rec['RICNUM'] = $risultato['infoFrontOffice']['RICNUM'];
                } else if ($risultato['isParere']) {
                    $praMail_rec['GESNUM'] = substr($risultato['infoFrontOffice']['PRORIC']['PROPAK'], 0, 10);
                    $praMail_rec['RICNUM'] = $risultato['infoFrontOffice']['PRORIC']['RICNUM'];
                }
            }
            if ($risultato['isRicevuta'] === true) {
                $praMail_rec['TIPORICEVUTA'] = $risultato['infoRicevuta']['tipoRicevuta'];
                $praMail_rec['MSGIDRICEVUTA'] = $risultato['infoRicevuta']['msgIdRicevuta'];
                $praMitDest_rec = $this->getPraMitDestByMsgid($risultato['infoRicevuta']['msgIdRicevuta']);
                $praMail_rec['GESNUM'] = substr($praMitDest_rec['KEYPASSO'], 0, 10);
                $praMail_rec['COMPAK'] = $praMitDest_rec['KEYPASSO'];
                $praMail_rec['COMIDMAIL'] = $praMitDest_rec['IDMAIL'];
            }
            if ($rowidarchivio) {
                $praMail_rec['ROWIDARCHIVIO'] = $rowidarchivio;
            }
            $nrow = ItaDB::DBInsert($praLib->getPRAMDB(), "PRAMAIL", 'ROWID', $praMail_rec);
            if ($nrow != 1) {
                return false;
            }
        } else {
            return false;
        }
        return $praMail_rec;
    }

    public function importAttachmentsFromMail($emlMessage) {
        $messaggioOriginale = $emlMessage->getMessaggioOriginaleObj();
        if ($messaggioOriginale) {
            $emlMessage = $messaggioOriginale;
        }
        $allegatiMail = $emlMessage->getAttachments();
        $allegati = array();
        foreach ($allegatiMail as $allegato) {
            $allegati[] = array(
                'FILENAME' => $allegato['FileName'],
                'DATAFILE' => $allegato['DataFile']
            );
        }
        return $allegati;
    }

    public function getDatiMail($idMail, $currMessage) {
        $importAllegatiFromMail = false;
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $filent_rec = $praLib->GetFilent(32);
        if ($filent_rec["FILVAL"] == "1") {
            $importAllegatiFromMail = true;
        }
        $praMail_rec = $praLib->getPraMail($idMail);
        $datiMail = array();
        if ($praMail_rec['ISGENERIC']) {
            $datiMail = $this->getDatiMailGenerica($currMessage);
        } else if ($praMail_rec['ISCOMUNICA']) {
            $datiMail = $this->getDatiComunica($currMessage, $praMail_rec);
        } else if ($praMail_rec['ISFRONTOFFICE']) {
            $datiMail = $this->getDatiFrontOffice($praMail_rec);
            if ($importAllegatiFromMail == true) {
                $datiMail['Dati']['ELENCOALLEGATI'] = $this->importAttachmentsFromMail($currMessage);
            }
        }
        $datiMail['PRAMAIL'] = $praMail_rec;
        $datiMail['Dati']['FILENAME'] = $currMessage->getEmlFile();
        $datiMail['Dati']['IDMAIL'] = $idMail;
        return $datiMail;
    }

    private function getDatiComunica($emlMessage, $praMail_rec) {
        $praLib = new praLib();
        $datiComunica = array();
        if ($emlMessage->isPEC()) {
            $emlMessage = $emlMessage->getMessaggioOriginaleObj();
        }
        $allegatitmp = $emlMessage->getAttachments();
        $allegatiComunica = array();
        foreach ($allegatitmp as $allegatotmp) {
            $allegatiComunica[] = array(
                'FILENAME' => $allegatotmp['FileName'],
                'FILEINFO' => $allegatotmp['FileName'],
                'FILEPATH' => $allegatotmp['DataFile'],
                'NOTE' => $allegatotmp['FileName']
            );
        }
        $allegati = unserialize($praMail_rec['INFOCOMUNICA']);
        $allDoc = $allegati['documento-allegato'];
        foreach ($allDoc as $allegato) {
            if (isset($allegato['@attributes']['nome-file'])) {
                $nome_file = $allegato['@attributes']['nome-file'];
                foreach ($allegatiComunica as $key => $allegatoComunica) {
                    if ($nome_file == $allegatoComunica['FILENAME']) {
                        //$allegatiComunica[$key]['FILEINFO'] = $allegato['descrizione']['@textNode'];
                        $allegatiComunica[$key]['NOTE'] = $allegato['descrizione']['@textNode'];
                    }
                }
            }
        }
        if ($praMail_rec['ISFRONTOFFICE']) {
            $datiComunica = $this->getDatiFrontOffice($praMail_rec);
        }

        $prafolist_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRAFOLIST WHERE FOCODICEPRATICASW = '" . $praMail_rec['CODICEPRATICASW'] . "'", false);
        if ($prafolist_rec) {
            $datiComunica['Dati']['PRAFOLIST_REC'] = $prafolist_rec;
        }

        $datiComunica['Dati']['ALLEGATICOMUNICA'] = $allegatiComunica;
        $datiComunica['Status'] = 0;
        $datiComunica['Message'] = '';
        return $datiComunica;
    }

    private function getDatiMailGenerica($emlMessage) {
        $datiMailGenerica = array();
        $messaggioOriginale = $emlMessage->getMessaggioOriginaleObj();
        $strutturaMail = $emlMessage->getStruct();
        if ($messaggioOriginale) {
            $emlMessage = $messaggioOriginale;
        }
        $allegatiMail = $emlMessage->getAttachments();
        $allegati = array();
        foreach ($allegatiMail as $allegato) {
            $allegati[] = array(
                'FILENAME' => $allegato['FileName'],
                'DATAFILE' => $allegato['DataFile']
            );
        }
        $datiMailGenerica['Dati']['ELENCOALLEGATI'] = $allegati;
        $strutturaMailOrig = $emlMessage->getStruct();
        //
        if (!$strutturaMailOrig['Date']) {
            $decodedDate = utiEmailDate::eDate2Date($strutturaMail['Date']);
//            $decodedDate = emlDate::eDate2Date($strutturaMail['Date']);
//            $datiMailGenerica['Dati']['DATA'] = date("Ymd", strtotime($strutturaMail['Date']));
//            $datiMailGenerica['Dati']['ORA'] = date("H:i:s", strtotime($strutturaMail['Date']));
        } else {
            $decodedDate = utiEmailDate::eDate2Date($strutturaMailOrig['Date']);
//            $decodedDate = emlDate::eDate2Date($strutturaMailOrig['Date']);
//            $datiMailGenerica['Dati']['DATA'] = date("Ymd", strtotime($strutturaMailOrig['Date']));
//            $datiMailGenerica['Dati']['ORA'] = date("H:i:s", strtotime($strutturaMailOrig['Date']));
        }
        $datiMailGenerica['Dati']['DATA'] = $decodedDate['date'];
        $datiMailGenerica['Dati']['ORA'] = $decodedDate['time'];
        //
        $datiMailGenerica['Status'] = 0;
        $datiMailGenerica['Message'] = '';
        return $datiMailGenerica;
    }

    private function getDatiFrontOffice($pramail_rec) {
        $datiFrontOffice = array();
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $infoFO = unserialize($pramail_rec['INFOFRONTOFFICE']);
        //@FIXME @andrea bufarini METTERE SUL XMLINFO NEL FO TUTTI I CAMPI CON UN FOREACH ALTRIMENTI DOVREMMO FARE SEMPRE UNA GET
        
        //$Proric_rec = $infoFO['PRORIC'];
        $Proric_rec = $praLib->GetProric($infoFO['PRORIC']['RICNUM']);
        if (!$Proric_rec) {
            $datiFrontOffice['Status'] = -1;
            $datiFrontOffice['Message'] = "Richiesta Online non trovata.";
            return $datiFrontOffice;
        }
        if (!Config::getPath('general.itaCms')) {
            $datiFrontOffice['Status'] = -1;
            $datiFrontOffice['Message'] = "Configurazione percorso Front Office non presente. Configuare!";
            return $datiFrontOffice;
        }
        $pathAllegatiRichieste = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
        if (!is_dir($pathAllegatiRichieste)) {
            $datiFrontOffice['Status'] = -1;
            $datiFrontOffice['Message'] = "Percorso Front Office: $pathAllegatiRichieste inesistente. Controllare!";
            return $datiFrontOffice;
        }

//            $this->CaricaAllegati($pathAllegatiRichieste . "attachments/" . $Proric_rec['RICNUM']);

        if ($Proric_rec['RICUUID']) {
            $prafolist_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRAFOLIST WHERE FOUUIDRICHIESTA = '" . $Proric_rec['RICUUID'] . "'", false);
            if ($prafolist_rec) {
                $datiFrontOffice['Dati']['PRAFOLIST_REC'] = $prafolist_rec;
            }
        }

        $allegatiCartella = $this->GetFileList($pathAllegatiRichieste . "attachments/" . $Proric_rec['RICNUM']);
        if ($allegatiCartella) {

            //
            //Rimuovo dall'array gli allegati che non verranno importatati (
            //
            foreach ($allegatiCartella as $key1 => $allegato) {
                if (strpos("|info|html|txt|", "|" . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION) . "|") !== false) {
                    unset($allegatiCartella[$key1]);
                }
                if (strpos($allegato['FILENAME'], "rapporto") != false) {
                    unset($allegatiCartella[$key1]);
                }
            }
        }
        $listAllegati = $allegatiCartella;
        $Ricdoc_tab = $praLib->GetRicdoc($Proric_rec['RICNUM'], "codice", true);
        if ($Ricdoc_tab) {
            foreach ($listAllegati as $allegato) {
                if (strtolower(pathinfo($allegato['FILEINFO'], PATHINFO_EXTENSION)) == "xml") {
                    $allegatiFrontOffice[] = array(
                        'DATAFILE' => $allegato['DATAFILE'],
                        'FILENAME' => $allegato['FILENAME'],
                        'FILEINFO' => $allegato['FILEINFO']
                    );
                }
                foreach ($Ricdoc_tab as $Ricdoc_rec) {
                    if ($Ricdoc_rec['DOCUPL'] == $allegato['FILEINFO']) {
                        $allegatiFrontOffice[] = array(
                            'DATAFILE' => $allegato['DATAFILE'],
                            'FILENAME' => $Ricdoc_rec['DOCNAME'],
                            'FILEINFO' => $Ricdoc_rec['DOCUPL']
                        );
                        break;
                    }
                }
            }
        } else {
            $allegatiFrontOffice = $listAllegati;
        }

        $datiFrontOffice['Status'] = 0;
        $datiFrontOffice['Message'] = '';
        $datiFrontOffice['Dati']['PRORIC_REC'] = $Proric_rec;
        $datiFrontOffice['Dati']['ELENCOALLEGATI'] = $allegatiFrontOffice;
        $datiFrontOffice['Dati']['FILENAME'] = '';
        $datiFrontOffice['Dati']['OGGETTO'] = '';
        $datiFrontOffice['Dati']['MITTENTE'] = '';
        $datiFrontOffice['Dati']['DATA'] = '';
        $datiFrontOffice['Dati']['ORA'] = '';
        return $datiFrontOffice;
    }

    private function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'DATAFILE' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

//    private function getPracomByMsgid($msgId) {
//        if ($msgId) {
//            $praLib = new praLib();
//            // TODO: DA SISTEMARE L'ID SENZA < e >
//            //$msgIdPulito = substr($msgId, 1, -1);
//            $sql1 = "SELECT * FROM MAIL_ARCHIVIO WHERE MSGID = '$msgId'";
//            $mailArchivio_rec = ItaDB::DBSQLSelect(emlLib::getITALWEB(), $sql1, false);
//            if ($mailArchivio_rec) {
//                $sql2 = "SELECT * FROM PRACOM WHERE COMIDMAIL = '" . $mailArchivio_rec['IDMAIL'] . "'";
//                $pracom_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql2, false);
//                return $pracom_rec;
//            } else {
//                return false;
//            }
//        } else {
//            return false;
//        }
//    }

    private function getPraMitDestByMsgid($msgId) {
        if ($msgId) {
            $praLib = new praLib();
            // TODO: DA SISTEMARE L'ID SENZA < e >
            //$msgIdPulito = substr($msgId, 1, -1);
            $sql1 = "SELECT * FROM MAIL_ARCHIVIO WHERE MSGID = '$msgId'";
            $mailArchivio_rec = ItaDB::DBSQLSelect(emlLib::getITALWEB(), $sql1, false);
            if ($mailArchivio_rec) {
                $sql2 = "SELECT * FROM PRAMITDEST WHERE IDMAIL = '" . $mailArchivio_rec['IDMAIL'] . "'";
                $praMitDest_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql2, false);
                return $praMitDest_rec;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function setClasseMail($idMail, $gesnum) {
        $ret = array();
        $ret['Status'] = "0";
        $ret['Message'] = "Mail marcata con successo";
        $ret['RetValue'] = true;
        //
        $praLib = new praLib();
        $praMail_rec = $praLib->getPraMail($idMail);
        if (!$praMail_rec) {
            return false;
        }
        $emlLib = new emlLib();
        switch ($praMail_rec['TIPOMAIL']) {
            case 'KEYMAIL':
                $mailArchivio_rec = $emlLib->getMailArchivio($idMail);
                if (!$mailArchivio_rec) {
                    $ret['Status'] = "-1";
                    $ret['Message'] = "Record su MAIL_ARCHIVIO non trovato";
                    $ret['RetValue'] = true;
                    return $ret;
                }
                include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
                $emlDbMailBox = new emlDbMailBox();
                $risultato = $emlDbMailBox->updateClassForRowId($mailArchivio_rec['ROWID'], '@SPORTELLO_CARICATO@');
                if ($risultato === false) {
                    $ret['Status'] = "-1";
                    $ret['Message'] = "Impossibile aggiornare la classe della mail";
                    $ret['RetValue'] = true;
                    return $ret;
                }
                $praMail_rec['MAILSTATO'] = 'CARICATA';
                $praMail_rec['GESNUM'] = $gesnum;
                //
                $nupd = ItaDB::DBUpdate($praLib->getPRAMDB(), 'PRAMAIL', 'ROWID', $praMail_rec);
                if ($nupd == -1) {
                    $ret['Status'] = "-1";
                    $ret['Message'] = "Impossibile aggiornare il record della mail";
                    $ret['RetValue'] = true;
                    return $ret;
                }

                /*
                 * Cancello la mail sul server dopo che il set classificazione è andato a buon fine
                 */
                $filent_rec = $praLib->GetFilent(23);
                if ($filent_rec["FILVAL"] == 1) {
                    if (!$praLib->DeleteMailFromServer($idMail)) {
                        $ret['Status'] = "-1";
                        $ret['Message'] = "Impossibile cancellare la mail dal server.<br>" . $praLib->getErrCode() . " - " . $praLib->getErrMessage();
                        $ret['RetValue'] = true;
                        return $ret;
                    }
                }
                break;
            case 'KEYUPL':
//CANCELLAZIONE SOSPESA PER POTER RIUTILIZZARE PIU VOLTE LA STESA MAIL
//                $fileName = $praLib->SetDirectoryPratiche('', '', 'PEC') . $idMail . '.eml';
//                $structName = $praLib->SetDirectoryPratiche('', '', 'PEC') . $idMail . '.info';
//                if (is_file($fileName)) {
//                    if (!@unlink($fileName)) {
//                        Out::msgStop("Nuovo Caricamento.", "File:" . $fileName . " non Eliminato");
//                        return false;
//                    }
//                }
//                if (is_file($structName)) {
//                    @unlink($structName);
//                }
//
//                $delete_Info = 'Oggetto: cancellazione stato Email ' . $praMail_rec['IDMAIL'] . ' - ' . $praMail_rec['RICNUM'];
//                if (!$this->deleteRecord($this->PRAM_DB, 'PRAMAIL', $praMail_rec['ROWID'], $delete_Info)) {
//                    return false;
//                }
                break;
        }
        return $ret;
    }

}

?>