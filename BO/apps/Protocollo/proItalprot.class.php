<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    24.06.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proItalprot extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($italsoftClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSPROTOCOLLOENDPOINT', false);
        $italsoftClient->setWebservices_uri($uri['CONFIG']);

        $wsdl = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSPROTOCOLLOWSDL', false);
        $italsoftClient->setWebservices_wsdl($wsdl['CONFIG']);

        $ditta = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSDOMAINCODE', false);
        $italsoftClient->setDomain($ditta['CONFIG']);

        $utente = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSUSER', false);
        $italsoftClient->setUsername($utente['CONFIG']);

        $ruolo = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSPASSWD', false);
        $italsoftClient->setpassword($ruolo['CONFIG']);
    }

    /**
     * 
     */
    function LeggiProtocollo($elementi) {
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $param = array();
        $param['userName'] = $ItalprotClient->getUsername();
        $param['userPassword'] = $ItalprotClient->getpassword();
        $param['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();
        //

        $param['token'] = $token;
        $param['numero'] = $elementi['NumeroProtocollo'];
        $param['anno'] = $elementi['AnnoProtocollo'];
        $param['tipo'] = $elementi['TipoProtocollo'];
        $param['segnatura'] = $elementi['Segnatura'];
        //
        $ret = $ItalprotClient->ws_getProtocollo($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $ItalprotClient->getResult();

        $TipoRisultato = $risultato['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di lettura del protocollo: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            //DATI NORMALIZZATI PER RICERCA PROTOCOLLO
            $Allegati = $risultato['items']['allegati'];
            if (!$Allegati[0]) {
                $Allegati = array($Allegati);
            }
            foreach ($Allegati as $Allegato) {
                $DocumentiAllegati[] = $Allegato['nomeFile'];
            }

            $arrMittDest = $arrayMitt = $arrayDest = array();

            foreach ($risultato['items']['mittenti'] as $keyMitt => $mittente) {
                $arrayMitt[$keyMitt]['Denominazione'] = $mittente['denominazione'];
                $arrayMitt[$keyMitt]['Indirizzo'] = $mittente['indirizzo'];
                $arrayMitt[$keyMitt]['CapComuneDiResidenza'] = $mittente['cap'];
                $arrayMitt[$keyMitt]['DescrizioneComuneDiResidenza'] = $mittente['citta'];
                $arrayMitt[$keyMitt]['ProvComuneDiResidenza'] = $mittente['prov'];
                $arrayMitt[$keyMitt]['Email'] = $mittente['email'];
            }
            foreach ($risultato['items']['destinatari'] as $keyDest => $destinatario) {
                $arrayDest[$keyDest]['Denominazione'] = $destinatario['denominazione'];
                $arrayDest[$keyDest]['Indirizzo'] = $destinatario['indirizzo'];
                $arrayDest[$keyDest]['CapComuneDiResidenza'] = $destinatario['cap'];
                $arrayDest[$keyDest]['DescrizioneComuneDiResidenza'] = $destinatario['citta'];
                $arrayDest[$keyDest]['ProvComuneDiResidenza'] = $destinatario['prov'];
                $arrayDest[$keyDest]['Email'] = $destinatario['email'];
            }
            $arrMittDest = array_merge($arrayMitt, $arrayDest);

            $arrayDoc = array();
            foreach ($risultato['items']['allegati'] as $keyAlle => $Allegato) {
                $param = array();
                $param['token'] = $token;
                $param['id'] = $Allegato['id'];
                $retAlle = $ItalprotClient->ws_getAllegato($param);
                if (!$retAlle) {
                    if ($ItalprotClient->getFault()) {
                        $msg = $ItalprotClient->getFault();
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "(Fault) Rilevato un fault in fase di lettura dell'allegato: <br>$msg";
                        $ritorno["RetValue"] = false;
                        return $ritorno;
                    } elseif ($ItalprotClient->getError()) {
                        $msg = $ItalprotClient->getFault();
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "(Error) Rilevato un errore in fase di lettura dell'allegato: <br>$msg";
                        $ritorno["RetValue"] = false;
                        return $ritorno;
                    }
                    return;
                }
                $risultatoAlle = $ItalprotClient->getResult();
                $arrayDoc[$keyAlle]['Stream'] = $risultatoAlle['allegato']['stream'];
                $arrayDoc[$keyAlle]['Estensione'] = $Allegato['estensione'];
                $arrayDoc[$keyAlle]['NomeFile'] = $Allegato['nomeFile'];
                $arrayDoc[$keyAlle]['Note'] = $Allegato['note'];
            }

            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Italsoft-ws', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $risultato['items']['numeroProtocollo'], 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $risultato['items']['dataProtocollo'], 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $risultato['items']['rowID'], 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $risultato['items']['segnatura'], 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $risultato['items']['annoProtocollo'], 'status' => true, 'msg' => '')
                )
            );

            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'Italsoft-ws',
                'NumeroProtocollo' => $risultato['items']['numeroProtocollo'],
                'Data' => $risultato['items']['dataProtocollo'],
                'DocNumber' => $risultato['items']['rowID'],
                'Segnatura' => $risultato['items']['segnatura'],
                'Origine' => $risultato['items']['tipoProtocollo'],
                'Anno' => $risultato['items']['annoProtocollo'],
                'Classifica' => $risultato['items']['classificazione'] . " - " . $risultato['items']['classificazione_Descrizione'],
                'Oggetto' => $risultato['items']['oggetto'],
                'CodiceFascicolo' => $risultato['items']['codiceFascicolo'],
                'DocumentiAllegati' => $DocumentiAllegati,
                'MittentiDestinatari' => $arrMittDest,
                'Allegati' => $arrayDoc,
            );
        }


        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    //@TODO:Rendere Privato
    public function InserisciProtocollo($elementi, $origine = "A") {
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $param = array();
        $param['userName'] = $ItalprotClient->getUsername();
        $param['userPassword'] = $ItalprotClient->getpassword();
        $param['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();

        //

        $param['token'] = $token;
        //
        $param['datiProtocollo'] = array();
        $param['datiProtocollo']['tipoProtocollo'] = $origine;
        $param['datiProtocollo']['tipoDocumento'] = $elementi['dati']['TipoDocumento'];

        $param['datiProtocollo']['ufficioOperatore'] = "";

        //
        //$param['datiProtocollo']['oggetto'] = utf8_decode($elementi['dati']['Oggetto']);
        $param['datiProtocollo']['oggetto'] = htmlspecialchars(utf8_encode($elementi['dati']['Oggetto']), ENT_COMPAT, 'UTF-8');

        $tipoProtAnt = $elementi['dati']['MetaDati']['DatiProtocollazione']['TipoProtocollo']['value'];
        if (isset($elementi['dati']['NumeroAntecedente']) && $elementi['dati']['NumeroAntecedente'] && $tipoProtAnt == $this->getClientType()) {
            $param['datiProtocollo']['numeroProtocolloAntecedente'] = $elementi['dati']['NumeroAntecedente'];
            $param['datiProtocollo']['annoProtocolloAntecedente'] = $elementi['dati']['AnnoAntecedente'];
            $param['datiProtocollo']['tipoProtocolloAntecedente'] = $elementi['dati']['TipoAntecedente']; //"A";
        }

        $param['datiProtocollo']['dataArrivo'] = $elementi['dati']['DataArrivo'];
        $param['datiProtocollo']['classificazione'] = $elementi['dati']['Classificazione'];

        /*
          if ($elementi['dati']['InCaricoA']) {
          $proLib = new proLib();
          if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
          $arrDest = explode("|", $elementi['dati']['InCaricoA']);
          $arrInCarico = explode(".", $arrDest[0]);
          } else {
          $arrInCarico = explode(".", $elementi['dati']['InCaricoA']);
          }

          $codiceUfficio = str_pad($arrInCarico[0], 4, "0", STR_PAD_LEFT);

          $codiceDest = $arrInCarico[1];
          if (is_numeric($arrInCarico[1])) {
          $codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
          }

          // controllo ufficio
          $Anauff_rec = $proLib->GetAnauff($codiceUfficio, 'codice');
          if (!$Anauff_rec) {
          $codiceUfficio = $arrInCarico[0];
          }
          }
         */

        if ($elementi['dati']['InCaricoA'] != "") {
            if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
                $arrTrasmissioni = explode("|", $elementi['dati']['InCaricoA']);
                foreach ($arrTrasmissioni as $keyTras => $trasmissione) {
                    list($codiceUfficio, $codiceDest) = explode(".", $trasmissione);
                    $trasmissione = array();
                    $trasmissione['codiceUfficio'] = $codiceUfficio;
                    if (is_numeric($codiceDest)) {
                        $codiceDest = str_pad($codiceDest, 6, "0", STR_PAD_LEFT);
                    }
                    $trasmissione['codiceDestinatario'] = $codiceDest;
                    $trasmissione['oggettoTrasmissione'] = "";
                    $trasmissione['gestione'] = 1;
                    $trasmissione['responsabile'] = 1;
                    $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][$keyTras] = $trasmissione;
                }
            } else {
                $arrInCarico = explode(".", $elementi['dati']['InCaricoA']);
                $codiceUfficio = str_pad($arrInCarico[0], 4, "0", STR_PAD_LEFT);
                //$codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
                $codiceDest = $arrInCarico[1];
                if (is_numeric($arrInCarico[1])) {
                    $codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
                }
                $trasmissione = array();
                $trasmissione['codiceUfficio'] = $codiceUfficio;
                $trasmissione['codiceDestinatario'] = $codiceDest;
                $trasmissione['oggettoTrasmissione'] = "";
                $trasmissione['gestione'] = 1;
                $trasmissione['responsabile'] = 1;
                $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][] = $trasmissione;
            }
        }

        $firmatarioUfficio = $codiceUfficio;
        $firmatarioDest = $codiceDest;
        if ($elementi['dati']['Firmatario']) {
            $arrFirmatario = explode(".", $elementi['dati']['Firmatario']);
            $firmatarioUfficio = $arrFirmatario[0];
            $firmatarioDest = $arrFirmatario[1];
        }

        if ($origine != "A") {
            $param['datiProtocollo']['destinatari']['mittenteDestinatario'] = array();
            foreach ($elementi['dati']['destinatari'] as $destinatario) {
                $mittDest = array();
                $mittDest['codice'] = "";
                $mittDest['denominazione'] = htmlspecialchars(utf8_encode($destinatario['Denominazione']), ENT_COMPAT, 'UTF-8'); //$destinatario['Denominazione'];
                $mittDest['indirizzo'] = $destinatario['Indirizzo'];
                $mittDest['cap'] = $destinatario['CAP'];
                $mittDest['citta'] = $destinatario['Citta'];
                $mittDest['email'] = $destinatario['Email'];
                $mittDest['ufficio'] = "";
                $param['datiProtocollo']['destinatari']['mittenteDestinatario'][] = $mittDest;
            }

            $param['datiProtocollo']['firmatari']['firmatario']['codice'] = $firmatarioDest;
            $param['datiProtocollo']['firmatari']['firmatario']['ufficio'] = $firmatarioUfficio;
        } else {
            if ($elementi['dati']['MittDest']['Denominazione'] != "") {
                $param['datiProtocollo']['mittenti']['mittenteDestinatario'] = array();
                $mittenteDestinatario = array();
                $mittenteDestinatario['codice'] = "";
                $mittenteDestinatario['denominazione'] = htmlspecialchars(utf8_encode($elementi['dati']['MittDest']['Denominazione']), ENT_COMPAT, 'UTF-8'); //$elementi['dati']['MittDest']['Denominazione'];
                $mittenteDestinatario['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
                $mittenteDestinatario['cap'] = $elementi['dati']['MittDest']['CAP'];
                $mittenteDestinatario['citta'] = $elementi['dati']['MittDest']['Citta'];
                $mittenteDestinatario['prov'] = $elementi['dati']['MittDest']['Provincia'];
                $mittenteDestinatario['codiceFiscale'] = $elementi['dati']['MittDest']['CF'];
                $mittenteDestinatario['partitaIva'] = "";
                $mittenteDestinatario['telefono'] = $elementi['dati']['MittDest']['Telefono'];
                $mittenteDestinatario['fax'] = "";
                $mittenteDestinatario['email'] = $elementi['dati']['MittDest']['Email'];
                $mittenteDestinatario['pec'] = $elementi['dati']['MittDest']['Email'];
                $mittenteDestinatario['ufficio'] = "";
                $param['datiProtocollo']['mittenti']['mittenteDestinatario'][] = $mittenteDestinatario;
            }
        }

        $param['datiProtocollo']['trasmissioniInterne']['trasmissione'] = array();
        if ($elementi['dati']['InCaricoA'] != "") {
            if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
                $arrTrasmissioni = explode("|", $elementi['dati']['InCaricoA']);
                foreach ($arrTrasmissioni as $keyTras => $trasmissione) {
                    list($ufficio, $codiceDest) = explode(".", $trasmissione);
                    $trasmissione = array();
                    $trasmissione['codiceUfficio'] = $ufficio;
                    $trasmissione['codiceDestinatario'] = $codiceDest;
                    $trasmissione['oggettoTrasmissione'] = "";
                    $trasmissione['gestione'] = 1;
                    $trasmissione['responsabile'] = 1;
                    $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][$keyTras] = $trasmissione;
                }
            } else {
                $trasmissione = array();
                $trasmissione['codiceUfficio'] = $codiceUfficio;
                $trasmissione['codiceDestinatario'] = $codiceDest;
                $trasmissione['oggettoTrasmissione'] = "";
                $trasmissione['gestione'] = 1;
                $trasmissione['responsabile'] = 1;
                $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][] = $trasmissione;
            }
        }

//        if (isset($elementi['dati']['DocumentoPrincipale'])) {
//            $param['datiProtocollo']['allegato']['tipoFile'] = "PRINCIPALE";
//            $param['datiProtocollo']['allegato']['nomeFile'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']), ENT_COMPAT, 'UTF-8');
//            $param['datiProtocollo']['allegato']['stream'] = $elementi['dati']['DocumentoPrincipale']['Stream'];
//            $param['datiProtocollo']['allegato']['note'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Descrizione']), ENT_COMPAT, 'UTF-8');
//        } else {
//            $param['datiProtocollo']['allegato']['tipoFile'] = "PRINCIPALE";
//            $param['datiProtocollo']['allegato']['nomeFile'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
//            $param['datiProtocollo']['allegato']['stream'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
//            $param['datiProtocollo']['allegato']['note'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Descrizione']), ENT_COMPAT, 'UTF-8');
//            unset($elementi['dati']['DocumentiAllegati'][0]);
//        }
//        if (isset($elementi['dati']['mettiAllaFirma']) && $elementi['dati']['mettiAllaFirma']) {
//            $param['datiProtocollo']['allegato']['mettiAllaFirma'] = $elementi['dati']['mettiAllaFirma'];
//        }

        /*
         * Inserisco l'allegato Principale
         */
        $docPrinc = array();
        if (isset($elementi['dati']['DocumentoPrincipale'])) {
            $docPrinc['tipoFile'] = "PRINCIPALE";
            $docPrinc['nomeFile'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']), ENT_COMPAT, 'UTF-8');
            $docPrinc['stream'] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $docPrinc['note'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentoPrincipale']['Descrizione']), ENT_COMPAT, 'UTF-8');
        } else {
            if (isset($elementi['dati']['DocumentiAllegati'][0])) {
                $docPrinc['tipoFile'] = "PRINCIPALE";
                $docPrinc['nomeFile'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
                $docPrinc['stream'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
                $docPrinc['note'] = htmlspecialchars(utf8_encode($elementi['dati']['DocumentiAllegati'][0]['Descrizione']), ENT_COMPAT, 'UTF-8');
                unset($elementi['dati']['DocumentiAllegati'][0]);
            }
        }

        /*
         * Inserisco il principale solo se c'è
         */
        if ($docPrinc) {
            $paramAlle = array();
            $paramAlle['token'] = $token;
            $paramAlle['nomeFile'] = htmlspecialchars(utf8_encode($docPrinc['nomeFile']), ENT_COMPAT, 'UTF-8'); //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $record['Documento']['Nome']);
            $paramAlle['stream'] = $docPrinc['stream'];
            $ret = $ItalprotClient->ws_insertDocumento($paramAlle);
            if (!$ret) {
                if ($ItalprotClient->getFault()) {
                    $msg = $ItalprotClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($ItalprotClient->getError()) {
                    $msg = $ItalprotClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
            $risultato = $ItalprotClient->getResult();
            $TipoRisultatoAll = $risultato['messageResult']['tipoRisultato'];
            $DescrizioneRisultatoAll = $risultato['messageResult']['descrizione'];
            if ($TipoRisultatoAll == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$DescrizioneRisultatoAll";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } else {
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['idunivoco'] = $risultato['allegatoPrecaricato']['idunivoco'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['hashfile'] = $risultato['allegatoPrecaricato']['hashfile'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['tipoFile'] = "";
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['nomeFile'] = htmlspecialchars(utf8_encode($docPrinc['nomeFile']), ENT_COMPAT, 'UTF-8');
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['note'] = htmlspecialchars(utf8_encode($docPrinc['note']), ENT_COMPAT, 'UTF-8');
                if (isset($elementi['dati']['mettiAllaFirma']) && $elementi['dati']['mettiAllaFirma']) {
                    $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['mettiAllaFirma'] = $elementi['dati']['mettiAllaFirma'];
                }
            }
        }

        /*
         * Inserisco Gli Allegati col nuovo metodo 
         */
        $arrayRowidAll = $elementi['dati']['pasdoc_rec'];
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $TipoRisultatoAll = $DescrizioneRisultatoAll = "";
        foreach ($DocAllegati as $keyAlle => $record) {
            $paramAlle = array();
            $paramAlle['token'] = $token;
            $paramAlle['nomeFile'] = htmlspecialchars(utf8_encode($record['Documento']['Nome']), ENT_COMPAT, 'UTF-8'); //preg_replace("/[^a-zA-Z0-9\s._-]/", "", $record['Documento']['Nome']);
            $paramAlle['stream'] = $record['Documento']['Stream'];
            $ret = $ItalprotClient->ws_insertDocumento($paramAlle);
            if (!$ret) {
                if ($ItalprotClient->getFault()) {
                    $msg = $ItalprotClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato " . $record['Documento']['Stream'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($ItalprotClient->getError()) {
                    $msg = $ItalprotClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire l'allegato " . $record['Documento']['Stream'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
            $risultato = $ItalprotClient->getResult();
            $TipoRisultatoAll = $risultato['messageResult']['tipoRisultato'];
            $DescrizioneRisultatoAll = $risultato['messageResult']['descrizione'];
            if ($TipoRisultatoAll == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$DescrizioneRisultatoAll";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } else {
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['idunivoco'] = $risultato['allegatoPrecaricato']['idunivoco'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['hashfile'] = $risultato['allegatoPrecaricato']['hashfile'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['tipoFile'] = "ALLEGATO";
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['tipo'] = $origine;
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['nomeFile'] = htmlspecialchars(utf8_encode($record['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['note'] = htmlspecialchars(utf8_encode($record['Descrizione']), ENT_COMPAT, 'UTF-8');
                if (isset($elementi['dati']['mettiAllaFirma']) && $elementi['dati']['mettiAllaFirma']) {
                    $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle + 1]['mettiAllaFirma'] = $elementi['dati']['mettiAllaFirma'];
                }
            }
        }

        /*
         * Inserisco il protocollo
         */
        $ret = $ItalprotClient->ws_putProtocollo($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $ItalprotClient->getResult();

        $TipoRisultato = $risultato['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $Data = $risultato['datiProtocollo']['dataProtocollo']; //è nel formato 20160101
            $proNum = $risultato['datiProtocollo']['numeroProtocollo'];
            $rowidProtocollo = $risultato['datiProtocollo']['rowidProtocollo'];
            $Segnatura = $risultato['datiProtocollo']['segnatura'];
            $Anno = $risultato['datiProtocollo']['annoProtocollo'];
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Italsoft-ws', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $rowidProtocollo, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        }

        //
        //Aggiungo gli allegati uno ad uno 
        //
//        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
//        $arrayRowidAll = $elementi['dati']['pasdoc_rec'];
//        $err_allegati = array();
//        $err_n = 0;
//        foreach ($DocAllegati as $keyDoc => $record) {
//            $param = array();
//            $param['token'] = $token;
//            $param['anno'] = $Anno;
//            $param['numero'] = $proNum;
//            $param['tipo'] = $origine;
//            $param['tipoFile'] = "ALLEGATO";
//            $param['nomeFile'] = htmlspecialchars(utf8_encode($record['Documento']['Nome']), ENT_COMPAT, 'UTF-8');
//            $param['note'] = htmlspecialchars(utf8_encode($record['Descrizione']), ENT_COMPAT, 'UTF-8');
//            $param['stream'] = $record['Documento']['Stream'];
//            if (isset($elementi['dati']['mettiAllaFirma']) && $elementi['dati']['mettiAllaFirma']) {
//                $param['mettiAllaFirma'] = $elementi['dati']['mettiAllaFirma'];
//            }
//
//            $ret = $ItalprotClient->ws_putAllegato($param);
//            if (!$ret) {
//                if ($ItalprotClient->getFault()) {
//                    $msg = $ItalprotClient->getFault();
//                    $errString = "<div>- Fault durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->$msg</div>";
//                } elseif ($ItalprotClient->getError()) {
//                    $msg = $ItalprotClient->getError();
//                    $errString = "<div>- Errore durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->$msg</div>";
//                }
//                $err_allegati[$err_n] = $errString;
//                $err_n++;
//                unset($arrayRowidAll[$keyDoc]);
//            }
//            $risultato = $ItalprotClient->getResult();
//            $TipoRisultato = $risultato['messageResult']['tipoRisultato'];
//            $DescrizioneRisultato = $risultato['messageResult']['descrizione'];
//            //gestione del messaggio d'errore
//            if ($TipoRisultato == "Error") {
//                $errString = "<span>- File " . $param['nomeFile'] . " - $DescrizioneRisultato</span><br>";
//                $err_allegati[$err_n] = $errString;
//                $err_n++;
//                unset($arrayRowidAll[$keyDoc]);
//            }
//        }
//
//        //gestione messaggio in caso di errori
//        if ($err_n > 0) {
//            $err_str = '';
//            foreach ($err_allegati as $err_nome) {
//                $err_str .= $err_nome . '<br>';
//            }
//        }
//        $ritorno['errString'] = $err_str;
        $ritorno['rowidAllegati'] = $arrayRowidAll;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    /**
     * 
     */
    public function AggiungiAllegati($elementi) {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Documenti allegati con successo!";
        $ritorno["RetValue"] = true;
        //
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $param = array();
        $param['userName'] = $ItalprotClient->getUsername();
        $param['userPassword'] = $ItalprotClient->getpassword();
        $param['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();

        //

        $err_allegati = array();
        $err_n = 0;
        $ret_allegati = array();
        $ret_n = 0;

        /*
         * Prendo il Principale
         */
        if (isset($elementi['arrayDoc']['Principale'])) {
            $param['token'] = $token;
            $param['numero'] = $elementi['NumeroProtocollo'];
            $param['anno'] = $elementi['AnnoProtocollo'];
            $param['tipo'] = $elementi['tipo'];
            $param['tipoFile'] = "ALLEGATO";
            $param['estensione'] = pathinfo($elementi['arrayDoc']['Principale']['Nome'], PATHINFO_EXTENSION);
            $param['stream'] = $elementi['arrayDoc']['Principale']['Stream'];
            $param['note'] = utf8_encode($elementi['arrayDoc']['Principale']['Descrizione']);
            $param['nomeFile'] = utf8_encode($elementi['arrayDoc']['Principale']['Nome']);
            $param['marcaDocumento'] = "";
            $param['mettiAllaFirma'] = "";
            //
            $ret = $ItalprotClient->ws_putAllegato($param);
            if (!$ret) {
                /*
                 * Errori e Fault già intercettati nel ws
                 */
            } else {
                $result = $ItalprotClient->getResult();
                if ($result['messageResult']['tipoRisultato'] == "Error") {
                    $err_allegati[$err_n] = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $elementi['arrayDoc']['Principale']['Nome'] . "</b>--->" . $result['messageResult']['descrizione'] . "</div>";
                    $err_n++;
                } elseif ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Info") {
                    $ret_allegati[$ret_n] = "<div>- Aggiunto correttamente l'allegato: <b>" . $elementi['arrayDoc']['Principale']['Nome'] . "</b> al prot. num. " . $param['numero'] . " anno " . $param['anno'] . "</div>";
                    $ret_n++;
                }
            }
        }



        /*
         * Scorro gli Allegati
         */
        foreach ($elementi['arrayDoc']['Allegati'] as $documento) {
            $param['token'] = $token;
            $param['numero'] = $elementi['NumeroProtocollo'];
            $param['anno'] = $elementi['AnnoProtocollo'];
            $param['tipo'] = $elementi['tipo'];
            $param['tipoFile'] = "ALLEGATO";
            $param['estensione'] = pathinfo($documento['Documento']['Nome'], PATHINFO_EXTENSION);
            $param['stream'] = $documento['Documento']['Stream'];
            $param['note'] = utf8_encode($documento['Descrizione']);
            $param['nomeFile'] = utf8_encode($documento['Documento']['Nome']);
            $param['marcaDocumento'] = "";
            $param['mettiAllaFirma'] = "";
            //
            $ret = $ItalprotClient->ws_putAllegato($param);
            if (!$ret) {
                /*
                 * Errori e Fault già intercettati nel ws
                 */
            } else {
                $result = $ItalprotClient->getResult();
                if ($result['messageResult']['tipoRisultato'] == "Error") {
                    $err_allegati[$err_n] = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $documento['Documento']['Nome'] . "</b>--->" . $result['messageResult']['descrizione'] . "</div>";
                    $err_n++;
                } elseif ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Info") {
                    $ret_allegati[$ret_n] = "<div>- Aggiunto correttamente l'allegato: <b>" . $documento['Documento']['Nome'] . "</b> al prot. num. " . $param['numero'] . " anno " . $param['anno'] . "</div>";
                    $ret_n++;
                }
            }
        }
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $ritorno["Status"] = "-1";
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '<br>';
            }
            $ritorno["Message"] = $err_str;
            $ritorno["ErrDetails"] = $err_allegati;
            $ritorno["RetValue"] = false;
        }
        $ritorno["RetDetails"] = $ret_allegati;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    function InvioMail($elementi) {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Mail inviata con Successo.";
        $ritorno["RetValue"] = true;
        //
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $paramToken = array();
        $paramToken['userName'] = $ItalprotClient->getUsername();
        $paramToken['userPassword'] = $ItalprotClient->getpassword();
        $paramToken['domainCode'] = $ItalprotClient->getDomain();
        $retToken = $ItalprotClient->ws_GetItaEngineContextToken($paramToken);
        if (!$retToken) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();

        $param = array();
        $param['token'] = $token;
        $param['anno'] = $elementi['Anno'];
        $param['numero'] = $elementi['proNum'];
        $param['segnatura'] = $elementi['Segnatura'];
        $param['oggettoCustom'] = htmlspecialchars(utf8_encode($elementi['Oggetto']), ENT_COMPAT, 'UTF-8');
        $param['bodyCustom'] = base64_encode($elementi['Testo']);
        $param['tipo'] = "P";

        $retInvio = $ItalprotClient->ws_NotificaMailProtocollo($param);
        if (!$retInvio) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile inviare la mail: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile inviare la mail: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $ItalprotClient->getResult();

        if ($risultato['messageResult']['tipoRisultato'] == 'Info') {
            switch ($risultato['statoNotifica']['stato']) {
                case "0":
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = $risultato['messageResult']['descrizione'];
                    $ritorno["RetValue"] = false;
                    break;
                case "1":
                    $ret = $ItalprotClient->ws_getProtocollo($param);
                    if (!$ret) {
                        if ($ItalprotClient->getFault()) {
                            $msg = $ItalprotClient->getFault();
                            $ritorno["Status"] = "-1";
                            $ritorno["Message"] = "(Fault) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                            $ritorno["RetValue"] = false;
                            return $ritorno;
                        } elseif ($ItalprotClient->getError()) {
                            $msg = $ItalprotClient->getFault();
                            $ritorno["Status"] = "-1";
                            $ritorno["Message"] = "(Error) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                            $ritorno["RetValue"] = false;
                            return $ritorno;
                        }
                        return;
                    }
                    $risultatoGetPrt = $ItalprotClient->getResult();
                    $destinatari = $risultatoGetPrt['items']['destinatari'];
                    foreach ($destinatari as $destinatario) {
                        if (is_array($destinatario['notificheMail'])) {
                            $ritorno["idMail"] = $destinatario['notificheMail'][0]['rowidmail'];
                            break;
                        }
                    }
                case "2":
                    $ritorno["Status"] = "0";
                    $ritorno["Message"] = $risultato['messageResult']['descrizione'];
                    $ritorno["RetValue"] = true;
                    break;
            }
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['messageResult']['descrizione'];
            $ritorno["RetValue"] = false;
        }

//        $ritorno["Status"] = "-1";
//        $ritorno["Message"] = "LOG";
//        $ritorno["RetValue"] = false;
//        return $ritorno;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $retDestroy = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$retDestroy) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    function VerificaInvio($elementi) {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Mail inviata con Successo.";
        $ritorno["RetValue"] = true;
        //
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $paramToken = array();
        $paramToken['userName'] = $ItalprotClient->getUsername();
        $paramToken['userPassword'] = $ItalprotClient->getpassword();
        $paramToken['domainCode'] = $ItalprotClient->getDomain();
        $retToken = $ItalprotClient->ws_GetItaEngineContextToken($paramToken);
        if (!$retToken) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();

        $arrayDest = array();

        /*
         * Get Protocollo
         */
        $param = array();
        $param['token'] = $token;
        $param['anno'] = $elementi['Anno'];
        $param['numero'] = $elementi['proNum'];
        $param['segnatura'] = $elementi['Segnatura'];
        $param['tipo'] = "P";
        $ret = $ItalprotClient->ws_getProtocollo($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di lettura del protocollo: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultatoGetPrt = $ItalprotClient->getResult();
        //
        $destinatari = $risultatoGetPrt['items']['destinatari'];
        foreach ($destinatari as $key => $destinatario) {
            $arrayDest[$key]['Email'] = $destinatario['email'];
            if (is_array($destinatario['notificheMail'])) {
                $notificheMail = $destinatario['notificheMail'];
                foreach ($notificheMail as $notifica) {
                    switch ($notifica['tipomail']) {
                        case "messaggio":
                            if ($notifica['rowidmail']) {
                                $arrayDest[$key]['Stato'][] = "Spedito";
                            }
                            break;
                        case "avvenuta-consegna":
                            if ($notifica['rowidmail']) {
                                $arrayDest[$key]['Stato'][] = "Consegnato";
                            }

                            break;
                        case "accettazione":
                            if ($notifica['rowidmail']) {
                                $arrayDest[$key]['Stato'][] = "Accettato";
                            }
                            break;
                        case "errore-consegna":
                            if ($notifica['rowidmail']) {
                                $arrayDest[$key]['Stato'][] = "NonConsegnato";
                            }
                            break;
                        case "errore-accettazione":
                            if ($notifica['rowidmail']) {
                                $arrayDest[$key]['Stato'][] = "NonAccettato";
                            }
                            break;

                        default:
                            break;
                    }
                }
            }
        }
        //Out::msgInfo("", print_r($arrayDest, true));
        $ritorno["Destinatari"] = $arrayDest;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $retDestroy = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$retDestroy) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    public function GetHtmlVerificaInvio($valore) {
        $html = '<table id="tableVerificaInvio">';
        $html .= "<tr>";
        $html .= '<th>Destinatario</th>';
        $html .= '<th>Spedito</th>';
        $html .= '<th>Accettato</th>';
        $html .= '<th>Consegnato</th>';
        $html .= '<th>NonAccettato</th>';
        $html .= '<th>NonConsegnato</th>';
        $html .= "</tr>";
        $html .= "<tbody>";
        foreach ($valore['Destinatari'] as $destinatario) {
            $html .= "<tr>";
            $html .= "<td>" . $destinatario['Email'] . "</td>";
            $classSpedito = $classAccettato = $classConsegnato = $classNonAccettato = $classNonConsegnato = "class=\"ui-icon ui-icon-closethick\"";
            foreach ($destinatario['Stato'] as $stato) {
                switch ($stato) {
                    case "Spedito":
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "Accettato":
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "Consegnato":
                        $classConsegnato = "class=\"ui-icon ui-icon-check\"";
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "NonAccettato":
                        $classNonAccettato = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "NonConsegnato":
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classNonConsegnato = "class=\"ui-icon ui-icon-check\"";
                        break;
                }
            }
            $html .= "<td><span $classSpedito></span></td>";
            $html .= "<td><span $classAccettato></span></td>";
            $html .= "<td><span $classConsegnato></span></td>";
            $html .= "<td><span $classNonAccettato></span></td>";
            $html .= "<td><span $classNonConsegnato></span></td>";
            $html .= "</tr>";
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_ITALPROT;
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
        return $this->InserisciProtocollo($elementi, "C");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}

?>