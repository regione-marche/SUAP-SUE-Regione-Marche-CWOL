<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    06.02.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPIride/itaIrideClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPIride/itaMittenteDestinatario.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proIride extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($irideClient, $dati) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDEENDPOINT', false);
        $irideClient->setWebservices_uri($uri['CONFIG']);
        $wsdl = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDEWSDL', false);
        $irideClient->setWebservices_wsdl($wsdl['CONFIG']);
        $irideClient->setNameSpaces();
        $ns = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDENAMESPACE', false);
        $irideClient->setNamespace($ns['CONFIG']);
        $utente = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'UTENTE', false);
        $irideClient->setUtente($utente['CONFIG']);
        $ruoloParam = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'RUOLO', false);
        $ruolo = $ruoloParam['CONFIG'];
        if ($dati['Ruolo']) {
            $ruolo = $dati['Ruolo'];
        }
        $irideClient->setRuolo($ruolo);
        $username = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'USERNAME', false);
        $irideClient->setUsername($username['CONFIG']);
        $password = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'PASSWORD', false);
        $irideClient->setPassword($password['CONFIG']);
        $timeout = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDETIMEOUT', false);
        $irideClient->setTimeout($timeout['CONFIG']);
        $aggiornaAnagrafiche = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'AGGIORNAANAGRAFICHE', false);
        $irideClient->setAggiornaAnagrafiche($aggiornaAnagrafiche['CONFIG']);
        $CodiceAmministrazione = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'CODICEAMMINISTRAZIONE', false);
        $irideClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);
        $CodiceAOO = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'CODICEAOO', false);
        $irideClient->setCodiceAOO($CodiceAOO['CONFIG']);
        $NomeObbligatorio = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'NOMEOBBLIGATORIO', false);
        $irideClient->setNomeObbligatorio($NomeObbligatorio['CONFIG']);
    }

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo")
     * @return type
     */
    function LeggiProtocollo($param) {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        $ret = $irideClient->ws_LeggiProtocollo($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($irideClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($irideClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($irideClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $irideClient->getResult();

        //gestione del messaggio d'errore
        if (isset($risultato['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca protocollo: <br>" . $risultato['Errore'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";

            /*
             * Estrapolo i soggetti
             */
            if (!isset($risultato['MittentiDestinatari']['MittenteDestinatario'][0])) {
                $risultato['MittentiDestinatari']['MittenteDestinatario'] = array($risultato['MittentiDestinatari']['MittenteDestinatario']);
            }

            $mittDest = array();
            foreach ($risultato['MittentiDestinatari']['MittenteDestinatario'] as $key => $nominativo) {
                if ($nominativo['IdSoggetto'] != 0) {
                    $paramA = $soggetto_rec = array();
                    $paramA['IdSoggetto'] = $nominativo['IdSoggetto'];
                    $ritorno = $this->LeggiAnagrafica($paramA);
                    if ($ritorno['Status'] == "0") {
                        $anagrafica_rec = $ritorno['RetValue']['Dati'];
                        if (isset($anagrafica_rec['Errore'])) {
                            $ritorno["Status"] = "-1";
                            $ritorno["Message"] = "Rilevato un errore in fase di ricerca soggetto: <br>" . $anagrafica_rec['Errore'];
                            $ritorno["RetValue"] = false;
                            return $ritorno;
                        }
                        //
                        $arrInd = explode(" ", $anagrafica_rec['IndirizzoVia']);
                        $civico = end(array_splice($arrInd, -1));
                        $indirizzo = implode(" ", $arrInd);
                        //
                        if ($anagrafica_rec['PersonaGiuridica'] == "false") {
                            $fiscale = $anagrafica_rec['CodiceFiscale'];
                            $desnom = $anagrafica_rec['Cognome'] . " " . $anagrafica_rec['Nome'];
                            $soggetto_rec['FormaGiuridica'] = "FI";
                            $soggetto_rec['Ruolo'] = praRuolo::getSystemSubjectCode("ESIBENTE");
                        } elseif ($anagrafica_rec['PersonaGiuridica'] == "true") {
                            $desnom = $anagrafica_rec['CognomeNome'];
                            $fiscale = $anagrafica_rec['PartitaIVA'];
                            $soggetto_rec['RagioneSociale'] = $anagrafica_rec['CognomeNome'];
                            $soggetto_rec['FormaGiuridica'] = "GI";
                            $soggetto_rec['Ruolo'] = praRuolo::getSystemSubjectCode("IMPRESA");
                        }
                        $soggetto_rec['Denominazione'] = $desnom;
                        $soggetto_rec['Nome'] = $anagrafica_rec['Nome'];
                        $soggetto_rec['Cognome'] = $anagrafica_rec['Cognome'];
                        $soggetto_rec['IdSoggetto'] = $anagrafica_rec['IdSoggetto'];
                        $soggetto_rec['CodiceFiscale'] = $fiscale;
                        if ($fiscale == "") {
                            $soggetto_rec['CodiceFiscale'] = $anagrafica_rec['CodiceFiscale'];
                        }
                        $soggetto_rec['Sesso'] = $anagrafica_rec['Sesso'];
                        $soggetto_rec['DescrizioneComuneDiNascita'] = $anagrafica_rec['DescrizioneComuneDiNascita'];
                        $soggetto_rec['DescrizioneNazionalita'] = $anagrafica_rec['DescrizioneNazionalita']; //non è detto che la corrispondenza sia esatta!
                        $soggetto_rec['DataDiNascita'] = substr($anagrafica_rec['DataDiNascita'], 6, 4) . substr($anagrafica_rec['DataDiNascita'], 3, 2) . substr($anagrafica_rec['DataDiNascita'], 0, 2);
                        $soggetto_rec['Email'] = $anagrafica_rec['Email'];
                        $soggetto_rec['Telefono'] = $anagrafica_rec['TelefonoFax']; //campo unico
                        $soggetto_rec['Indirizzo'] = $indirizzo;
                        $soggetto_rec['Civico'] = $civico;
                        $soggetto_rec['CapComuneDiResidenza'] = $anagrafica_rec['CapComuneDiResidenza'];
                        $soggetto_rec['DescrizioneComuneDiResidenza'] = $anagrafica_rec['DescrizioneComuneDiResidenza'];
                        $soggetto_rec['Nazionalita'] = $anagrafica_rec['Nazionalita'];
                        $soggetto_rec['NaturaGiuridica'] = $anagrafica_rec['NaturaGiuridica'];
                        if ($key != 0) {
                            $soggetto_rec['Ruolo'] = praRuolo::getSystemSubjectCode("DICHIARANTE");
                        }
                        $mittDest[] = $soggetto_rec;
                    }
                }
            }
            if ($risultato['MittenteInterno_Descrizione']) {
                $soggetto_rec = array();
                $soggetto_rec['Denominazione'] = $risultato['MittenteInterno_Descrizione'];
                $soggetto_rec['Ruolo'] = praRuolo::getSystemSubjectCode("DICHIARANTE");
                $mittDest[] = $soggetto_rec;
            }


            //DATI COMPLETI DI LETTURA DEL PROTOCOLLO
            $ritorno["RetValue"]['Dati'] = $risultato;
            //DATI PER SALVATAGGIO NEI METADATI
            $ritorno["RetValue"]['DatiProtocollazione'] = array(
                'TipoProtocollo' => array('value' => 'Iride', 'status' => true, 'msg' => $risultato['Origine']),
                'proNum' => array('value' => $risultato['NumeroProtocollo'], 'status' => true, 'msg' => ''),
                'IdDocumento' => array('value' => $risultato['IdDocumento'], 'status' => true, 'msg' => ''),
                'Data' => array('value' => $risultato['DataProtocollo'], 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $risultato['AnnoProtocollo'], 'status' => true, 'msg' => '')
            );
            //DATI NORMALIZZATI PER RICERCA PROTOCOLLO
            $Allegati = $risultato['Allegati']['Allegato'];
            if (!$Allegati[0]) {
                $Allegati = array($Allegati);
            }
            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['Commento'];
            }

            /*
             * Array Allegati per importazione da protocollo
             */
            $arrayDoc = array();
            foreach ($Allegati as $key => $Allegato) {
                $arrayDoc[$key]['Stream'] = $Allegato['Image'];
                $ext = $Allegato['TipoFile'];
                if ($ext == "p7m") {
                    $ext = $Allegato['SottoEstensione'] . ".$ext";
                }
                $arrayDoc[$key]['Estensione'] = $ext;
                $arrayDoc[$key]['SottoEstensione'] = $Allegato['SottoEstensione'];
                $arrayDoc[$key]['NomeFile'] = $Allegato['NomeAllegato'];
                $arrayDoc[$key]['Note'] = $Allegato['Commento'];
            }

            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'Iride',
                'NumeroProtocollo' => $risultato['NumeroProtocollo'],
                'Data' => $risultato['DataProtocollo'],
                'DocNumber' => $risultato['IdDocumento'],
                'Segnatura' => '',
                'Anno' => $risultato['AnnoProtocollo'],
                'Classifica' => $risultato['Classifica'] . " - " . $risultato['Classifica_Descrizione'],
                'Oggetto' => $risultato['Oggetto'],
                'Origine' => $risultato['Origine'],
                'NumeroFascicolo' => $risultato['IdPratica'],
                'DocumentiAllegati' => $DocumentiAllegati,
                'MittentiDestinatari' => $mittDest,
                'Allegati' => $arrayDoc,
            );
        }
        return $ritorno;
    }

    function LeggiDocumento($param) {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        $ret = $irideClient->ws_LeggiDocumentoString($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($irideClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($irideClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($irideClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $irideClient->getResult();

        /*
         * Elaboro Xml d'uscita
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_decode($risultato));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Leggi Documento: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Leggi Documento: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        //gestione del messaggio d'errore
        if ($arrayXml['Errore'][0]['@textNode']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca del documento: <br>" . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";

            /*
             * DATI NORMALIZZATI PER RICERCA PROTOCOLLO
             */
            $Allegati = $arrayXml['Allegati'][0]['Allegato'];
            if (!$Allegati[0]) {
                $Allegati = array($Allegati);
            }
            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['Commento'][0]['@textNode'];
            }
            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'NumeroProtocollo' => $arrayXml['NumeroProtocollo'][0]['@textNode'],
                'Anno' => $arrayXml['AnnoProtocollo'][0]['@textNode'],
                'Data' => $arrayXml['DataProtocollo'][0]['@textNode'],
                'TipoProtocollo' => 'Iride',
                'IterAttivo' => $arrayXml['IterAttivo'][0]['@textNode'],
                'DataDoc' => $arrayXml['DataDocumento'][0]['@textNode'],
                'DocNumber' => $arrayXml['IdDocumento'][0]['@textNode'],
                'Classifica' => $arrayXml['Classifica'][0]['@textNode'] . " - " . $arrayXml['Classifica_Descrizione'][0]['@textNode'],
                'Oggetto' => $arrayXml['Oggetto'][0]['@textNode'],
                //'InCaricoA' => $arrayXml['InCaricoA'][0]['@textNode'] . " - " . $arrayXml['InCaricoA_Descrizione'][0]['@textNode'],
                'InCaricoA' => $arrayXml['InCaricoA'][0]['@textNode'],
                'InCaricoA_Descrizione' => $arrayXml['InCaricoA_Descrizione'][0]['@textNode'],
                'DocumentiAllegati' => $DocumentiAllegati
            );
        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param = array("IdSoggetto", "CodiceFiscale")
     * @return boolean
     */
    function LeggiAnagrafica($param) {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        $ret = $irideClient->ws_LeggiAnagrafica($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($irideClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($irideClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($irideClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $irideClient->getResult();
        /*
         * NORMALIZZARE -> torna string XML
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($risultato);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore: lettura dati anagrafici del soggetto " . $param['IdSoggetto'] . " non riuscita";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        $arrayNorm = array();
        foreach ($arrayXml as $chiave => $value) {
            $arrayNorm[$chiave] = $value[0]['@textNode'];
        }

        //gestione del messaggio d'errore
        if (isset($arrayXml['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca anagrafica: <br>" . $arrayXml['Errore'][0]['@textNode'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI COMPLETI DI LETTURA DEL NOMINATIVO
            $ritorno["RetValue"]['Dati'] = $arrayNorm;
        }
        return $ritorno;
    }

    //TODO mettere privato
    public function InserisciProtocollo($elementi, $origine = "A") {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient, $elementi['dati']);

        $DataRicezione = $elementi['dati']['DataArrivo']; //formato 20140109
        //$param['Data'] = substr($DataRicezione, 6, 2) . "/". substr($DataRicezione, 4, 2) . "/" . substr($DataRicezione, 0, 4); //formato 09/01/2014
        $param['Data'] = date("d/m/Y");
        //classificazione
        $classificazione = $elementi['dati']['Classificazione'];
//        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'] != '') {
//            //controllo esistenza di protocollo collegato
//            $numeroProtocollo = $elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'];
//            $anno = $elementi['dati']['MetaDati']['DatiProtocollazione']['Anno']['value'];
//            $param_l = array(
//                "NumeroProtocollo" => $numeroProtocollo,
//                "AnnoProtocollo" => $anno
//            );
//            $ritorno = $this->LeggiProtocollo($param_l);
//            $risultato = $irideClient->getResult();
//            if (isset($risultato['Errore'])) {
//                $ritorno["Status"] = "-1";
//                $ritorno["Message"] = "Rilevato un errore in fase di ricerca protocollo: <br>" . $risultato['Errore'] . "";
//                $ritorno["RetValue"] = false;
//            }
//            if ($ritorno['Status'] == "0") {
//                if ($ritorno['RetValue']['DatiProtocollo']) {
//                    $param['Classifica'] = $ritorno['RetValue']['Dati']['Classifica'];
//                }
//            } else {
//                return $ritorno;
//            }
//        } else {
        if ($classificazione) {
            $param['Classifica'] = $classificazione;
        } else {
            Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
            return;
        }
//        }
        //Per ora il TipoDocumento viene preso dai parametri -> recupero TipoDocumento in maniera dinamica?
        if (isset($elementi['dati']['TipoDocumento'])) {
            $param['TipoDocumento'] = $elementi['dati']['TipoDocumento'];
        } else {
            $param['TipoDocumento'] = $irideClient->getTipoDocumento();
        }
        $param['Oggetto'] = utf8_encode($elementi['dati']['Oggetto']);
        $param['Origine'] = $origine;

        /*
         * MITTENTI DESTINATARI
         */
        $MittDestSource_tab = array();
        switch ($origine) {
            case "A":
                $MittDestSource_tab[] = $elementi['dati']['MittDest'];
                break;
            case "P":
                $MittDestSource_tab = $elementi['dati']['destinatari'];
                break;
        }

        $MittDest = array();
        foreach ($MittDestSource_tab as $chiave => $MittDestSource_rec) {
            if ($MittDestSource_rec['Denominazione']) {
                $MD = new itaMittenteDestinatario();
                $Denominazione = utf8_encode($MittDestSource_rec['Denominazione']);
                $Nome = utf8_encode($MittDestSource_rec['Nome']);
                $Cognome = utf8_encode($MittDestSource_rec['Cognome']);
                $Indirizzo = utf8_encode($MittDestSource_rec['Indirizzo']);
                $cap = $MittDestSource_rec['CAP'];
                $citta = $MittDestSource_rec['Citta'];
                $prov = $MittDestSource_rec['Provincia'];
                $email = $MittDestSource_rec['Email'];
                $cf = $MittDestSource_rec['CF'];
                $MD->setCodiceFiscale($cf);
//
//              MODALITA NON PIU' USATA 
//              Si ACCETTA EVENTUALE  CF VUOTO  
//              ASCOLI A RIEMPITO I CODICI FISCALi VUOTI CON LA STRINGA "ID:<CODICE DESTINATARIO>"
//                                                
//                if ($cf) {
//                    $MD->setCodiceFiscale($cf);
//                } else {
//                    $irideClient->setAggiornaAnagrafiche("N");
//                }

                if ($irideClient->getNomeObbligatorio() === 'S') {
                    if ($Nome) {
                        $MD->setCognomeNome($Cognome);
                        $MD->setNome($Nome);
                    } else if ($Denominazione) {
                        $MD->setCognomeNome($Denominazione);
                        $MD->setNome('_');
                    }
                } else {
                    if ($Denominazione) {
                        $MD->setCognomeNome($Denominazione);
                    }
                }

                if ($Indirizzo) {
                    $MD->setIndirizzo($Indirizzo);
                }
                if ($citta) {
                    $MD->setLocalita($citta);
                }
                switch ($origine) {
                    case "A":
                        if (isset($elementi['dati']['dataProtocolloMittente'])) {
                            $MD->setDataInvio_DataProt(date("d/m/Y", strtotime($elementi['dati']['dataProtocolloMittente'])));
                        }
                        if (isset($elementi['dati']['numeroProtocolloMittente'])) {
                            $MD->setSpese_NProt($elementi['dati']['numeroProtocolloMittente']);
                        }
                        if ($DataRicezione) {
                            $MD->setDataRicevimento($DataRicezione);
                        }
                        break;
                    case "I":
                    case "P":
                        $MD->setDataInvio_DataProt(date("d/m/Y"));
                        break;

                    default:
                        break;
                }
                if ($chiave == 0) {
                    $MD->setTipoSogg("S"); //il primo soggetto lo indico come principale
                }
                //$MD->setTipoPersona($_POST[$this->nameForm . '_MD' . $n . '_TipoPersona']); //prenderà quello di default del WS
                $MittDest[] = $MD;
            }
        }

        if ($MittDest) {
            $param['MittentiDestinatari'] = $MittDest;
        }
        if ($origine == "P" && !$MittDest) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Destinatari non presenti.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

//        $param['AggiornaAnagrafiche'] = "F"; //F -> nuova anagrafica inserita se non è valorizzato il CF o se il CF non viene trovato
//        if ($_POST[$this->nameForm . '_IP_AnnoPratica'] != '')
//            $param['AnnoPratica'] = $_POST[$this->nameForm . '_IP_AnnoPratica'];
//        if ($_POST[$this->nameForm . '_IP_NumeroPratica'] != '')
//            $param['NumeroPratica'] = $_POST[$this->nameForm . '_IP_NumeroPratica'];
//        if ($_POST[$this->nameForm . '_IP_DataDocumento'] != '')
//            $param['DataDocumento'] = $_POST[$this->nameForm . '_IP_DataDocumento'];
//        if ($_POST[$this->nameForm . '_IP_NumeroDocumento'] != '')
//            $param['NumeroDocumento'] = $_POST[$this->nameForm . '_IP_NumeroDocumento'];
//        if ($_POST[$this->nameForm . '_IP_DataEvid'] != '')
//            $param['DataEvid'] = $_POST[$this->nameForm . '_IP_DataEvid'];
        //$param['InCaricoA'] = "WEB_SERVICES"; //FISSO AL MOMENTO PER TEST!
        if (isset($elementi['dati']['InCaricoA']) && $elementi['dati']['InCaricoA'] != '') {
            $param['InCaricoA'] = $elementi['dati']['InCaricoA'];
        }
        if ($origine == "P" || $origine == "I") {
            if (isset($elementi['dati']['MittenteInterno']) && $elementi['dati']['MittenteInterno'] != '') {
                $param['MittenteInterno'] = $elementi['dati']['MittenteInterno'];
            }
        }
        /*
         * ALLEGATI
         */
        $Allegati = array();
        if (isset($elementi['dati']['DocumentoPrincipale'])) {
            $allegato = array();
            $allegato['TipoFile'] = $elementi['dati']['DocumentoPrincipale']['estensione'];
            $allegato['Image'] = $elementi['dati']['DocumentoPrincipale']['stream'];
            $allegato['Commento'] = utf8_encode($elementi['dati']['DocumentoPrincipale']['descrizione']);
            $allegato['Schema'] = '';
            $allegato['NomeAllegato'] = utf8_encode($elementi['dati']['DocumentoPrincipale']['nomeFile']);
            $allegato['TipoAllegato'] = '';
            $Allegati[] = $allegato;
        }
        if (count($Allegati) > 0) {
            $param['NumeroAllegati'] = count($Allegati);
            $param['Allegati'] = $Allegati;
        }

        //$ret = $irideClient->ws_InserisciProtocollo($param);
        $ret = $irideClient->ws_InserisciProtocolloString($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $irideClient->getResult();

        //
        //Elaboro Xml d'usicta
        //
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($risultato));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $msg = "Protocollazione avvenuta con successo!";
        $status = "0";
        foreach ($arrayXml as $elemento => $value) {
            if ($elemento == "IdDocumento") {
                $DocNumber = $value[0]["@textNode"];
            }
            if ($elemento == "Messaggio") {
                $Messaggio = $value[0]["@textNode"];
            }
            if ($elemento == "NumeroProtocollo") {
                $proNum = $value[0]["@textNode"];
            }
            if ($elemento == "AnnoProtocollo") {
                $Anno = $value[0]["@textNode"];
            }
            if ($elemento == "DataProtocollo") {
                $DataProt = $value[0]["@textNode"];
            }
            if ($elemento == "Errore") {
                $msg = $value[0]["@textNode"];
                $status = "-1";
            }
        }
        $ritorno = array();
        $ritorno["Status"] = $status;
        $ritorno["Message"] = $msg;
        $ritorno["RetValue"] = array(
            'DatiProtocollazione' => array(
                'TipoProtocollo' => array('value' => 'Iride', 'status' => true, 'msg' => $Messaggio),
                'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                'Data' => array('value' => $DataProt, 'status' => true, 'msg' => ''),
                'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
            )
        );

        if ($status != '0') {
            return $ritorno;
        }

        //controllo fault
//        if ($risultato['Fault']) {
//            $msg = $irideClient->getFault();
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }
//
//        //gestione del messaggio d'errore
//        //if (isset($risultato['InserisciProtocolloEAnagraficheResult']['Errore']) && $risultato['InserisciProtocolloEAnagraficheResult']['Errore'] != '') {
//        if (isset($risultato['Errore']) && $risultato['Errore'] != '') {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>" . $risultato['Messaggio'] . "";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        } else {
//            $Data = $risultato['DataProtocollo']; //è nel formato gg/mm/aaaa???? CONTROLLARE FORMATO DI RITORNO
//            $DataProt = substr($Data, 0, 4) . substr($Data, 5, 2) . substr($Data, 8, 2);
//            $proNum = $risultato['NumeroProtocollo'];
//            $DocNumber = $risultato['IdDocumento'];
//            $Anno = substr($Data, 0, 4);
//            $ritorno = array();
//            $ritorno["Status"] = "0";
//            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
//            $ritorno["RetValue"] = array(
//                'DatiProtocollazione' => array(
//                    'TipoProtocollo' => array('value' => 'Iride', 'status' => true, 'msg' => $risultato['Messaggio']),
//                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
//                    'Data' => array('value' => $DataProt, 'status' => true, 'msg' => ''),
//                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
//                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
//                )
//            );
//        }

        /*
         * ALTRI ALLEGATI INSERITI CON AggiungiAllegati2
         */
        $arrayDoc = $elementi['dati']['DocumentiAllegati'];
        $err_allegati = array();
        $err_n = 0;
        foreach ($arrayDoc as $documento) {
            $allegato = array();
            $allegato['TipoFile'] = $documento['estensione'];
            $allegato['Image'] = $documento['stream'];
            $allegato['Commento'] = utf8_encode($documento['descrizione']);
            $allegato['Schema'] = ''; //ci può andare l'md5
            $allegato['NomeAllegato'] = utf8_encode($documento['nomeFile']);
            $allegato['TipoAllegato'] = ''; //qui il tipo di codifica dello schema: md5,sha...
            //chiamata al metodo
            if (!$DocNumber) {
                $DocNumber = "";
            }
            $param['idDoc'] = $DocNumber;
            $param['annoProt'] = date('Y');
            $param['numProt'] = $proNum;

            $Allegati = array();
            $Allegati[] = $allegato;
            if ($Allegati) {
                $param['Allegati'] = $Allegati;
            }
            //$ret = $irideClient->ws_AggiungiAllegati2($param);
            $ret = $irideClient->ws_AggiungiAllegati($param); //vecchio metodo
            if (!$ret) {
                if ($irideClient->getFault()) {
                    $msg = $irideClient->getFault();
                } elseif ($irideClient->getError()) {
                    $msg = $irideClient->getError();
                }
                $err_allegati[$err_n] = $allegato['NomeAllegato'];
                $err_n++;
            }
            if ($irideClient->getResult() != "0") {
                $err_allegati[$err_n] = $irideClient->getResult();
                $err_n++;
            }
        }
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '\n';
            }
            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n. " . $param['numProt'] . " del " . $param['annoProt'] . "\n
                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param array('NumeroProtocollo', 'AnnoProtocollo', 'Allegati')
     */
    public function AggiungiAllegati($param) {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Documenti allegati con successo!";
        $ritorno["RetValue"] = true;
        if (!$param['arrayDoc'] && !$param['arrayDocRicevute']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Nessun documento da allegare";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $err_allegati = array();

        /*
         * Protocollo il principale
         */
        if (isset($param['arrayDoc']['Principale'])) {
            $allegato = array();
            $allegato['TipoFile'] = $param['arrayDoc']['Principale']['estensione'];
            $allegato['Image'] = $param['arrayDoc']['Principale']['stream'];
            $allegato['Commento'] = utf8_encode($param['arrayDoc']['Principale']['descrizione']);
            $allegato['Schema'] = ''; //ci può andare l'md5
            $allegato['NomeAllegato'] = utf8_encode($param['arrayDoc']['Principale']['nomeFile']);
            $allegato['TipoAllegato'] = ''; //qui il tipo di codifica dello schema: md5,sha...
            //chiamata al metodo
            $param_1 = array();
            $param_1['idDoc'] = $param['DocNumber'];
            $param_1['annoProt'] = $param['AnnoProtocollo'];
            $param_1['numProt'] = $param['NumeroProtocollo'];

            $Allegati = array();
            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
            $ret = $irideClient->ws_AggiungiAllegati2($param_1); //vecchio metodo
            if (!$ret) {
                if ($irideClient->getFault()) {
                    $msg = $irideClient->getFault();
                } elseif ($irideClient->getError()) {
                    $msg = $irideClient->getError();
                }
                $err_allegati[] = $msg;
            } else {
                $result = $irideClient->getResult();
                if ($result['Errore']) {
                    //$err_allegati[] = utf8_decode(htmlspecialchars($result['Errore'], ENT_COMPAT, 'UTF-8'));
                    $err_allegati[] = $result['Errore'];
                }
            }
        }

        /*
         * Mi scorro gli allegati da protocollare
         */
        foreach ($param['arrayDoc']['Allegati'] as $documento) {
            $allegato = array();
            $allegato['TipoFile'] = $documento['estensione'];
            $allegato['Image'] = $documento['stream'];
            $allegato['Commento'] = utf8_encode($documento['descrizione']);
            $allegato['Schema'] = ''; //ci può andare l'md5
            $allegato['NomeAllegato'] = utf8_encode($documento['nomeFile']);
            $allegato['TipoAllegato'] = ''; //qui il tipo di codifica dello schema: md5,sha...
            //chiamata al metodo
            $param_1 = array();
            $param_1['idDoc'] = $param['DocNumber'];
            $param_1['annoProt'] = $param['AnnoProtocollo'];
            $param_1['numProt'] = $param['NumeroProtocollo'];

            $Allegati = array();
            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
            $ret = $irideClient->ws_AggiungiAllegati2($param_1); //vecchio metodo
            if (!$ret) {
                if ($irideClient->getFault()) {
                    $msg = $irideClient->getFault();
                } elseif ($irideClient->getError()) {
                    $msg = $irideClient->getError();
                }
                $err_allegati[] = $msg;
            } else {
                $result = $irideClient->getResult();
                if ($result['Errore']) {
                    //$err_allegati[] = utf8_decode(htmlspecialchars($result['Errore'], ENT_COMPAT, 'UTF-8'));
                    $err_allegati[] = $result['Errore'];
                }
            }
        }

        /*
         * Mi scorro le ricevute delle pec in partenza
         */

        foreach ($param['arrayDocRicevute']['Ricevute'] as $documento) {
            $allegato = array();
            $allegato['TipoFile'] = $documento['estensione'];
            $allegato['Image'] = $documento['stream'];
            $allegato['Commento'] = utf8_encode($documento['descrizione']);
            $allegato['Schema'] = ''; //ci può andare l'md5
            $allegato['NomeAllegato'] = utf8_encode($documento['nomeFile']);
            $allegato['TipoAllegato'] = ''; //qui il tipo di codifica dello schema: md5,sha...
            //chiamata al metodo
            $param_1 = array();
            $param_1['idDoc'] = $param['DocNumber'];
            $param_1['annoProt'] = $param['AnnoProtocollo'];
            $param_1['numProt'] = $param['NumeroProtocollo'];

            $Allegati = array();
            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
            $ret = $irideClient->ws_AggiungiAllegati2($param_1); //vecchio metodo
            if (!$ret) {
                if ($irideClient->getFault()) {
                    $msg = $irideClient->getFault();
                } elseif ($irideClient->getError()) {
                    $msg = $irideClient->getError();
                }
                $err_allegati[] = $msg;
            } else {
                $result = $irideClient->getResult();
                if ($result['Errore']) {
                    //$err_allegati[] = htmlspecialchars($result['Errore']);
                    $err_allegati[] = $result['Errore'];
                }
            }
        }
        //gestione messaggio in caso di errori
        if (count($err_allegati) > 0) {
            $ritorno["Status"] = "-1";
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '<br>';
            }
            $ritorno["Message"] = $err_str;
            $ritorno["RetValue"] = false;
//            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n. " . $param_1['numProt'] . " del " . $param_1['annoProt'] . "\n
//                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
        }
        return $ritorno;
    }

    public function CreaCopie($elementi) {
        $ritorno = array();
        //
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        //
        $param = array();
        $param['NumProt'] = $elementi['ProNum'];
        $param['AnnoProt'] = $elementi['Anno'];
        $param['FascConOrig'] = "N";
        //$param['InCaricoA'] = $elementi['InCaricoA'];
        if (strpos($elementi['InCaricoA'], "|") !== false) {
            $arrDest = explode("|", $elementi['dati']['InCaricoA']);
            foreach ($arrDest as $keyUniOpe => $uniOpe) {
                $param['UoDestinatari'][$keyUniOpe]['Carico'] = $uniOpe;
                $param['UoDestinatari'][$keyUniOpe]['TipoUO'] = "UO";
                $param['UoDestinatari'][$keyUniOpe]['Data'] = "";
                $param['UoDestinatari'][$keyUniOpe]['NumeroCopie'] = "";
            }
        } else {
            $param['UoDestinatari'][0]['Carico'] = $elementi['InCaricoA'];
            $param['UoDestinatari'][0]['TipoUO'] = "UO";
            $param['UoDestinatari'][0]['Data'] = "";
            $param['UoDestinatari'][0]['NumeroCopie'] = "";
        }

        /*
         * Lancio il ws crea copie
         */
        $ret = $irideClient->ws_CreaCopieString($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di Creazione Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Creazione Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $irideClient->getResult();

        /*
         * Elaboro Xml d'usicta
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($risultato));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (isset($arrayXml['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile creare la copia: " . $arrayXml['Messaggio'][0]['@textNode'] . "-" . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "CreaCopia avvenuta con successo!";
        }

        if (isset($arrayXml['CopieCreate'][0]['CopiaCreata'])) {
            $ritorno['RetValue']['DatiCopia'] = array(
                "idCopia" => $arrayXml['CopieCreate'][0]['CopiaCreata'][0]['IdDocumentoCopia'][0]['@textNode'],
                "Carico" => $arrayXml['CopieCreate'][0]['CopiaCreata'][0]['Carico'][0]['@textNode'],
            );
        } else {
            $ritorno["Message"] = $arrayXml['Messaggio'][0]['@textNode'] . " per il documento numero " . $arrayXml['IdDocumentoSorgente'][0]['@textNode'];
        }
        return $ritorno;
    }

    public function LeggiCopie($elementi) {
        $ritorno = array();
        //
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        //
        $param = array();
        $param['NumProt'] = $elementi['ProNum'];
        $param['AnnoProt'] = $elementi['Anno'];

        /*
         * Lancio il ws crea copie
         */
        $ret = $irideClient->ws_LeggiCopie($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di lettura Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di lettura Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $irideClient->getResult();

        /*
         * Elaboro Xml d'usicta
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($risultato));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (isset($arrayXml['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile leggere la copia: " . $arrayXml['Messaggio'][0]['@textNode'] . "-" . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "LggiCopia avvenuta con successo!";
        }

        if (isset($arrayXml['Copie'][0]['Copia'])) {
            foreach ($arrayXml['Copie'][0]['Copia'] as $key => $copia) {
                $ritorno['RetValue']['DatiCopia'][$key] = array(
                    "idCopia" => $copia['IdDocumentoCopia'][0]['@textNode'],
                    "FascicoloAnno" => $copia['FascicoloAnno'][0]['@textNode'],
                    "FascicoloNumero" => $copia['FascicoloNumero'][0]['@textNode'],
                    "Carico" => $copia['Carico'][0]['@textNode'],
                );
            }
        }
        return $ritorno;
    }

    public function AnnullaDocumento($elementi) {
        $ritorno = array();
        //
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);
        //
        $param = array();
        $param['idPratica'] = $elementi['idDocumento'];
        $param['Motivazione'] = $elementi['Motivazione'];
        $param['NumProt'] = $elementi['ProNum'];
        $param['AnnoProt'] = $elementi['Anno'];

        /*
         * Lancio il ws crea copie
         */
        $ret = $irideClient->ws_AnnullaDocumento($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di Annulla Documento: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Annulla Documento: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $irideClient->getResult();

        /*
         * Elaboro Xml d'usicta
         */
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($risultato));
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if (isset($arrayXml['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile annullare il documento: " . $arrayXml['Messaggio'][0]['@textNode'] . "-" . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $ritorno["Status"] = "0";
            $ritorno["RetValue"] = true;
            if ($arrayXml['Esito'][0]['@textNode'] == "false") {
                $ritorno["Status"] = "-1";
                $ritorno["RetValue"] = false;
            }
            $ritorno["Message"] = $arrayXml['Messaggio'][0]['@textNode'];
            return $ritorno;
        }
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_IRIDE;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->InserisciProtocollo($elementi, "P");
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->InserisciProtocollo($elementi, "A");
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->InserisciDocumentoEAnagrafiche($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}

?>