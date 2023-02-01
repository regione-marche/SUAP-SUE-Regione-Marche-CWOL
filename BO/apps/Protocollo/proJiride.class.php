<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    30.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPJiride/itaJirideClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPJiride/itaMittenteDestinatario.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proJiride extends proWsClient {

    private $MezzoInvio;

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($jirideClient) {

        if ($this->arrConfigParams) {
            $uri = "";
            $wsdl = "";
            $ns = "";
            $utente = "";
            $ruolo = "";
            $username = "";
            $password = "";
            $timeout = "";
            $aggiornaAnagrafiche = "";
            $CodiceAmministrazione = "";
            $CodiceAOO = "";
            $TipoNumeroDocumento = "";
            $this->MezzoInvio = "";
        } else {
            $keyConfigParam = proWsClientHelper::CLASS_PARAM_PROTOCOLLO_JIRIDE;
            if ($this->keyConfigParam) {
                $keyConfigParam = $this->keyConfigParam;
            }
            $devLib = new devLib();
            $uri = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEENDPOINT', false);
            $wsdl = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDEWSDL', false);
            $ns = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSIRIDENAMESPACE', false);
            $utente = $devLib->getEnv_config($keyConfigParam, 'codice', 'UTENTEWS', false);
            $ruolo = $devLib->getEnv_config($keyConfigParam, 'codice', 'RUOLOWS', false);
            $username = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'USERNAME', false);
            $password = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'PASSWORD', false);
            $timeout = $devLib->getEnv_config($keyConfigParam, 'codice', 'WSJIRIDETIMEOUT', false);
            $aggiornaAnagrafiche = $devLib->getEnv_config($keyConfigParam, 'codice', 'AGGIORNAANAGRAFICHEWS', false);
            $CodiceAmministrazione = $devLib->getEnv_config($keyConfigParam, 'codice', 'CODICEAMMINISTRAZIONEWS', false);
            $CodiceAOO = $devLib->getEnv_config($keyConfigParam, 'codice', 'CODICEAOOWS', false);
            $TipoNumeroDocumento = $devLib->getEnv_config($keyConfigParam, 'codice', 'TIPONUMERODOCUMENTO', false);
            $MezzoInvio = $devLib->getEnv_config($keyConfigParam, 'codice', 'MEZZOINVIO', false);
            $this->MezzoInvio = $MezzoInvio['CONFIG'];
        }
        $jirideClient->setWebservices_uri($uri['CONFIG']);
        $jirideClient->setWebservices_wsdl($wsdl['CONFIG']);
        $jirideClient->setNameSpaces();
        $jirideClient->setNamespace($ns['CONFIG']);
        $jirideClient->setUtente($utente['CONFIG']);
        $jirideClient->setRuolo($ruolo['CONFIG']);
        $jirideClient->setUsername($username['CONFIG']);
        $jirideClient->setPassword($password['CONFIG']);
        $jirideClient->setTimeout($timeout['CONFIG']);
        $jirideClient->setAggiornaAnagrafiche($aggiornaAnagrafiche['CONFIG']);
        $jirideClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);
        $jirideClient->setCodiceAOO($CodiceAOO['CONFIG']);
        $jirideClient->setTipoNumeroDocumento($TipoNumeroDocumento['CONFIG']);
    }

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo")
     * @return type
     */
    public function LeggiProtocollo($param) {
        $irideClient = new itaJirideClient();
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
                'TipoProtocollo' => array('value' => 'Jiride', 'status' => true, 'msg' => $risultato['Origine']),
                'proNum' => array('value' => $risultato['NumeroProtocollo'], 'status' => true, 'msg' => ''),
                'IdDocumento' => array('value' => $risultato['IdDocumento'], 'status' => true, 'msg' => ''),
                'Data' => array('value' => $risultato['DataProtocollo'], 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $risultato['AnnoProtocollo'], 'status' => true, 'msg' => '')
            );
            //DATI NORMALIZZATI PER RICERCA PROTOCOLLO
            $Allegati = $risultato['Allegati']['Allegato'];
            if ($Allegati) {
                if (!$Allegati[0]) {
                    $Allegati = array($Allegati);
                }
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

            foreach ($Allegati as $Allegato) {
                if ($Allegato['Commento']) {
                    $DocumentiAllegati[] = $Allegato['Commento'];
                } else {
                    $DocumentiAllegati[] = $Allegato['NomeAllegato'];
                }
            }
            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'Jiride',
                'NumeroProtocollo' => $risultato['NumeroProtocollo'],
                'Data' => $risultato['DataProtocollo'],
                'DocNumber' => $risultato['IdDocumento'],
                'Segnatura' => '',
                'Anno' => $risultato['AnnoProtocollo'],
                'Classifica' => $risultato['Classifica'] . " - " . $risultato['Classifica_Descrizione'],
                'Oggetto' => $risultato['Oggetto'],
                'Origine' => $risultato['Origine'],
                'DocumentiAllegati' => $DocumentiAllegati,
                'MittentiDestinatari' => $mittDest,
                'NumeroFascicolo' => $risultato['IdPratica'],
                'Allegati' => $arrayDoc,
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
        $irideClient = new itaJirideClient();
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
        if (isset($risultato['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca anagrafica: <br>" . $risultato['Errore'] . "";
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
        $irideClient = new itaJirideClient();
        $this->setClientConfig($irideClient);

        /*
         * Ora la passiamo vuota per aggiormanto Maggioli del 15/02/2018
         */
        $DataRicezione = ""; //$elementi['dati']['DataArrivo']; //formato 20140109
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
        $param['Oggetto'] = strtoupper(utf8_encode($elementi['dati']['Oggetto']));
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

        /*
         * Trasformo in maiuscolo tutti i valori dei campi dei Mitt/Dest
         */
        foreach ($MittDestSource_tab as $key => $MittDestSource_rec) {
            foreach ($MittDestSource_rec as $campo => $valore) {
                $MittDestSource_tab[$key][$campo] = strtoupper($valore);
            }
        }

        $MittDest = array();
        foreach ($MittDestSource_tab as $chiave => $MittDestSource_rec) {
            if ($MittDestSource_rec['Denominazione']) {
                $MD = new itaMittenteDestinatario();
                $Denominazione = utf8_encode($MittDestSource_rec['Denominazione']);
                $Indirizzo = utf8_encode($MittDestSource_rec['Indirizzo']);
                $cap = $MittDestSource_rec['CAP'];
                $citta = $MittDestSource_rec['Citta'];
                $prov = $MittDestSource_rec['Provincia'];
                $email = $MittDestSource_rec['Email'];
                $cf = $MittDestSource_rec['CF'];
                $MD->setCodiceFiscale($cf);
//
//              MODALITA NON PIU' USATA Si ACCETTA EVENTUALE  CF VUOTO  
//              ASCOLI A RIEMPITO I CODICI FISCALi VUOTI CON LA STRINGA "ID:<CODICE DESTINATARIO>"
//                                                
//                if ($cf) {
//                    $MD->setCodiceFiscale($cf);
//                } else {
//                    $irideClient->setAggiornaAnagrafiche("N");
//                }

                if ($Denominazione) {
                    $MD->setCognomeNome($Denominazione);
                }
                if ($Indirizzo) {
                    $MD->setIndirizzo($Indirizzo);
                }
                if ($citta) {
                    $MD->setLocalita($citta);
                }
                if ($email) {
                    $MD->setRecapiti(
                            array(
                                "Recapito" => array(
                                    "TipoRecapito" => "EMAIL",
                                    "ValoreRecapito" => $email)
                            )
                    );
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
                        $MD->setMezzo($this->MezzoInvio);
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
        $parametro = $irideClient->getTipoNumeroDocumento();
        switch ($parametro) {
            case 'P':
                $param['NumeroDocumento'] = $elementi['dati']['NumeroPratica'];
                break;
            case 'R':
                $param['NumeroDocumento'] = $elementi['dati']['NumeroRichiestaFormatted'];
                break;

            default:
                break;
        }

        if (isset($elementi['dati']['InCaricoA']) && $elementi['dati']['InCaricoA'] != '') {
            $param['InCaricoA'] = $elementi['dati']['InCaricoA'];
        }
//        if ($origine == "P" || $origine == "I") {
//            if (isset($elementi['dati']['MittenteInterno']) && $elementi['dati']['MittenteInterno'] != '') {
//                $param['MittenteInterno'] = $elementi['dati']['MittenteInterno'];
//            }
//        }
        $param['MittenteInterno'] = $irideClient->getRuolo();
        /*
         * ALLEGATI
         */

        $Allegati = array();
        if (isset($elementi['dati']['DocumentoPrincipale'])) {
            $allegato = array();
            $allegato['TipoFile'] = strtoupper($elementi['dati']['DocumentoPrincipale']['estensione']);
            $allegato['Image'] = $elementi['dati']['DocumentoPrincipale']['stream'];
            $allegato['Commento'] = strtoupper(utf8_encode($elementi['dati']['DocumentoPrincipale']['descrizione']));
            $allegato['Schema'] = '';
            $allegato['NomeAllegato'] = strtoupper(utf8_encode($elementi['dati']['DocumentoPrincipale']['nomeFile']));
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
        $retXml = $xmlObj->setXmlFromString($risultato);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Inserisci Protocollo: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Inserisci Protocollo: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
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
        }

        $keyConfigParam = proWsClientHelper::CLASS_PARAM_PROTOCOLLO_JIRIDE;
        if ($this->keyConfigParam) {
            $keyConfigParam = $this->keyConfigParam;
        }

        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Protocollazione avvenuta con successo!";
        $ritorno["RetValue"] = array(
            'DatiProtocollazione' => array(
                'TipoProtocollo' => array('value' => 'Jiride', 'status' => true, 'msg' => $Messaggio),
                'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                'Data' => array('value' => $DataProt, 'status' => true, 'msg' => ''),
                'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''),
                'Aggregato' => array('value' => $elementi['dati']['Aggregato']['Codice'], 'status' => true, 'msg' => ''),
                'CodAmm' => array('value' => $elementi['dati']['Aggregato']['CodAmm'], 'status' => true, 'msg' => ''),
                'CodAoo' => array('value' => $elementi['dati']['Aggregato']['CodAoo'], 'status' => true, 'msg' => ''),
                'codiceIstanza' => array('value' => $keyConfigParam, 'status' => true, 'msg' => ''),
            )
        );

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
        $param['arrayDoc']['Allegati'] = $arrayDoc;
        $param['arrayDoc']['Ricevute'] = $elementi['dati']['DocumentiRicevute'];


        $param['DocNumber'] = $DocNumber;
        $param['AnnoProtocollo'] = $Anno;
        $param['NumeroProtocollo'] = $proNum;
//        $err_allegati = array();
//        $err_n = 0;
//        foreach ($arrayDoc as $documento) {
//            $allegato = array();
//            $allegato['TipoFile'] = $documento['estensione'];
//            $allegato['Image'] = $documento['stream'];
//            $allegato['Commento'] = utf8_encode($documento['descrizione']);
//            $allegato['Schema'] = ''; //ci può andare l'md5
//            $allegato['NomeAllegato'] = utf8_encode($documento['nomeFile']);
//            $allegato['TipoAllegato'] = ''; //qui il tipo di codifica dello schema: md5,sha...
//            //chiamata al metodo
//            if (!$DocNumber) {
//                $DocNumber = "";
//            }
//            $param['idDoc'] = $DocNumber;
//            $param['annoProt'] = date('Y');
//            $param['numProt'] = $proNum;
//
//            $Allegati = array();
//            $Allegati[] = $allegato;
//            if ($Allegati) {
//                $param['Allegati'] = $Allegati;
//            }
        //$ret = $irideClient->ws_AggiungiAllegati2($param);
        //$ret = $irideClient->ws_AggiungiAllegati($param); //vecchio metodo
//            $ret = $irideClient->ws_AggiungiAllegatiString($param);
//            if (!$ret) {
//                if ($irideClient->getFault()) {
//                    $msg = $irideClient->getFault();
//                } elseif ($irideClient->getError()) {
//                    $msg = $irideClient->getError();
//                }
//                $err_allegati[$err_n] = $allegato['NomeAllegato'];
//                $err_n++;
//            }
//        }
        //gestione messaggio in caso di errori
        if ($param['arrayDoc']['Allegati'] || $param['arrayDoc']['Ricevute']) {
            $retAll = $this->AggiungiAllegati($param);
            if ($retAll['Status'] == "-1") {
                $ritorno["Status"] = $retAll['Status'];
                $ritorno["Message"] = $retAll['Message'];
                $ritorno["RetValue"] = $retAll['RetValue'];
            }
        }
//        if ($err_n > 0) {
//            $err_str = '';
//            foreach ($err_allegati as $err_nome) {
//                $err_str .= $err_nome . '\n';
//            }
//            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n. " . $param['numProt'] . " del " . $param['annoProt'] . "\n
//                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
//        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param array('NumeroProtocollo', 'AnnoProtocollo', 'Allegati')
     */
    public function AggiungiAllegati($param) {
        $irideClient = new itaJirideClient();
        $this->setClientConfig($irideClient);
        //
        $Allegati = array();
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

        /*
         * Prendo l'allegato principale
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

            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
        }


        /*
         * Mi scorro gli allegati e li metto in un nuovo array $param_1['Allegati']
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

            //$Allegati = array();
            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
            //$ret = $irideClient->ws_AggiungiAllegati2($param_1); //vecchio metodo
//            $ret = $irideClient->ws_AggiungiAllegatiString($param_1);
//            if (!$ret) {
//                if ($irideClient->getFault()) {
//                    $msg = $irideClient->getFault();
//                } elseif ($irideClient->getError()) {
//                    $msg = $irideClient->getError();
//                }
//                $err_allegati[] = $msg;
//            } else {
//                $result = $irideClient->getResult();
//                if ($result['Errore'] <> '') {
//                    $err_allegati[] = htmlspecialchars($result['Errore']);
//                }
//            }
        }

        /*
         * Mi scorro le ricevute di accettazione e consegna e li metto in un nuovo array $param_1['Allegati']
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

            $Allegati[] = $allegato;
            if ($Allegati) {
                $param_1['Allegati'] = $Allegati;
            }
        }

        $ret = $irideClient->ws_AggiungiAllegatiString($param_1);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
            }
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $msg;
            $ritorno["RetValue"] = false;
        } else {
            $result = $irideClient->getResult();

            //
            //Elaboro Xml d'usicta
            //
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
            $xmlObj = new itaXML;
            $retXml = $xmlObj->setXmlFromString($result);

            //gestione messaggio in caso di errori
            if (!$retXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "File XML Aggiungi Allegati: Impossibile leggere il testo nell'xml";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            if (!$arrayXml) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Lettura XML Aggiungi Allegati: Impossibile estrarre i dati";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }

            if ($arrayXml['Errore'][0]['@textNode']) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = $arrayXml['Errore'][0]['@textNode'];
                $ritorno["RetValue"] = false;
            } else {
                $ritorno["Status"] = "0";
                $ritorno["Message"] = $arrayXml['Messaggio'][0]['@textNode'];
                $ritorno["RetValue"] = true;
            }
        }
        return $ritorno;
    }

    public function InserisciDocumentoEAnagrafiche($elementi, $origine = "A") {
        $irideClient = new itaJirideClient();
        $this->setClientConfig($irideClient);

        $DataRicezione = $elementi['dati']['DataArrivo']; //formato 20140109
        $param['Data'] = date("d/m/Y");
        //classificazione
        $classificazione = $elementi['dati']['Classificazione'];
        if ($classificazione) {
            $param['Classifica'] = $classificazione;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Classificazione non trovata. La procedura sarà interrotta";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

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
                $Indirizzo = utf8_encode($MittDestSource_rec['Indirizzo']);
                $cap = $MittDestSource_rec['CAP'];
                $citta = $MittDestSource_rec['Citta'];
                $prov = $MittDestSource_rec['Provincia'];
                $email = $MittDestSource_rec['Email'];
                $cf = $MittDestSource_rec['CF'];
                $MD->setCodiceFiscale($cf);

                if ($Denominazione) {
                    $MD->setCognomeNome($Denominazione);
                }
                if ($Indirizzo) {
                    $MD->setIndirizzo($Indirizzo);
                }
                if ($citta) {
                    $MD->setLocalita($citta);
                }
                if ($email) {
                    $MD->setRecapiti(
                            array(
                                "Recapito" => array(
                                    "TipoRecapito" => "EMAIL",
                                    "ValoreRecapito" => $email)
                            )
                    );
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
                        $MD->setMezzo($this->MezzoInvio);
                        $MD->setDataInvio_DataProt(date("d/m/Y"));
                        break;

                    default:
                        break;
                }
                if ($chiave == 0) {
                    $MD->setTipoSogg("S"); //il primo soggetto lo indico come principale
                }
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

        if (isset($elementi['dati']['InCaricoA']) && $elementi['dati']['InCaricoA'] != '') {
            $param['InCaricoA'] = $elementi['dati']['InCaricoA'];
        }
//        if ($origine == "P" || $origine == "I") {
//            if (isset($elementi['dati']['MittenteInterno']) && $elementi['dati']['MittenteInterno'] != '') {
//                $param['MittenteInterno'] = $elementi['dati']['MittenteInterno'];
//            }
//        }
        $param['MittenteInterno'] = $irideClient->getRuolo();

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
        if (isset($elementi['dati']['DocumentiAllegati'])) {
            foreach ($elementi['dati']['DocumentiAllegati'] as $alle) {
                $allegato = array();
                $allegato['TipoFile'] = $alle['estensione'];
                $allegato['Image'] = $alle['stream'];
                $allegato['Commento'] = utf8_encode($alle['descrizione']);
                $allegato['Schema'] = '';
                $allegato['NomeAllegato'] = utf8_encode($alle['nomeFile']);
                $allegato['TipoAllegato'] = '';
                $Allegati[] = $allegato;
            }
        }
        if (count($Allegati) > 0) {
            $param['NumeroAllegati'] = count($Allegati);
            $param['Allegati'] = $Allegati;
        }


        $ret = $irideClient->ws_InserisciDocumentoEAnagraficheString($param);

        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Metti alla Firma: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Metti alla Firma: <br>$msg";
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
        $retXml = $xmlObj->setXmlFromString($risultato);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Metti alla Firma: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Metti alla Firma: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        if ($arrayXml['Errore'][0]['@textNode']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di messa alla firma del documento: <br>" . $arrayXml['Errore'][0]['@textNode'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }




        $Documenti = array();
        foreach ($arrayXml as $elemento => $value) {
            if ($elemento == "IdDocumento") {
                $DocNumber = $value[0]["@textNode"];
            }
            if ($elemento == "AnnoProtocollo") {
                $Anno = $value[0]["@textNode"];
            }
            if ($elemento == "Messaggio") {
                $Messaggio = $value[0]["@textNode"];
            }
            if ($elemento == "Errore") {
                $Errore = $value[0]["@textNode"];
            }
            if ($elemento == "Allegati") {
                foreach ($arrayXml['Allegati'][0]['Allegato'] as $allegato) {
                    $Documenti[]['Serial'] = $allegato['Serial'][0]["@textNode"];
                }
            }
        }
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Invio al protocollo per la firma avvenuto con successo!";
        $ritorno["RetValue"] = array(
            'DatiProtocollazione' => array(
                'TipoProtocollo' => array('value' => 'Jiride', 'status' => true, 'msg' => $Messaggio, 'err' => $Errore),
                'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''),
                'DataDoc' => array('value' => date("Y-m-d"), 'status' => true, 'msg' => ''),
                'Allegati' => $Documenti,
            )
        );
        return $ritorno;
    }

    public function CreaCopie($elementi) {
        $param = array();
        $irideClient = new itaJirideClient();
        $this->setClientConfig($irideClient);

        $param['IdDocumento'] = $elementi['dati']['idDocumento'];
        //
        $UODestinatarie = array();
        foreach ($elementi['dati']['UODestinatarie'] as $UODest) {
            $UODestinataria = array();
            $UODestinataria['Carico'] = $UODest['Carico'];
            $UODestinataria['TipoUO'] = $UODest['TipoUO']; //Non Usato
            $UODestinataria['Data'] = $UODest['Data']; //Non Usato
            $UODestinataria['NumeroCopie'] = $UODest['NumeroCopie']; //Non Usato
            $UODestinataria['TipoAssegnazione'] = $UODest['TipoAssegnazione'];
            $UODestinatarie[] = $UODestinataria;
        }
        $param['UODestinatarie'] = $UODestinatarie;

        $ret = $irideClient->ws_CreaCopieString($param);

        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Crea Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di Crea Copia: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $irideClient->getResult();

        //
        //Elaboro Xml d'uscita
        //
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($risultato);
        if (!$retXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File XML Crea Copia: Impossibile leggere il testo nell'xml";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura XML Crea Copia: Impossibile estrarre i dati";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        foreach ($arrayXml as $elemento => $value) {
            if ($elemento == "IdDocumento") {
                $DocNumber = $value[0]["@textNode"];
            }
            if ($elemento == "AnnoProtocollo") {
                $Anno = $value[0]["@textNode"];
            }
            if ($elemento == "DataProtocollo") {
                $DataProt = $value[0]["@textNode"];
            }
            if ($elemento == "Messaggio") {
                $Messaggio = $value[0]["@textNode"];
            }
            if ($elemento == "Errore") {
                $Errore = $value[0]["@textNode"];
            }
        }

        $ritorno = array();
        if (!$Errore) {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = $Messaggio;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $Messaggio;
            $ritorno["Error"] = $Errore;
        }

        return $ritorno;
    }

    function LeggiDocumento($param) {
        $irideClient = new itaJirideClient();
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

        //
        //Elaboro Xml d'uscita
        //
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
                'TipoProtocollo' => 'Jiride',
                'DataDoc' => $arrayXml['DataDocumento'][0]['@textNode'],
                'DocNumber' => $arrayXml['IdDocumento'][0]['@textNode'],
                'NumeroFascicolo' => $arrayXml['IdPratica'][0]['@textNode'],
                'AnnoFascicolo' => $arrayXml['AnnoPratica'][0]['@textNode'],
                'Classifica' => $arrayXml['Classifica'][0]['@textNode'] . " - " . $arrayXml['Classifica_Descrizione'][0]['@textNode'],
                'Oggetto' => $arrayXml['Oggetto'][0]['@textNode'],
                'InCaricoA' => $arrayXml['InCaricoA'][0]['@textNode'] . " - " . $arrayXml['InCaricoA_Descrizione'][0]['@textNode'],
                'DocumentiAllegati' => $DocumentiAllegati
            );
        }
        return $ritorno;
    }

    public function leggiCopie() {
        return true;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_JIRIDE;
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

    public function inserisciDocumentoInterno($elementi, $tipo = "A") {
        return $this->InserisciDocumentoEAnagrafiche($elementi, $tipo);
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}
