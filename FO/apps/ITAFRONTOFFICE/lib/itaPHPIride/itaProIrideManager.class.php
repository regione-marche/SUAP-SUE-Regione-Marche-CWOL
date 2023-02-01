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
require_once(ITA_LIB_PATH . '/itaPHPIride/itaIrideClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPIride/itaMittenteDestinatario.class.php');

class itaProIrideManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaProIrideManager();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($irideClient) {
        $irideClient->setWebservices_uri($this->clientParam['WSIRIDEENDPOINT']);
        $irideClient->setWebservices_wsdl($this->clientParam['WSIRIDEWSDL']);
        $irideClient->setNameSpaces();
        $irideClient->setNamespace($this->clientParam['WSIRIDENAMESPACE']);
        $irideClient->setUtente($this->clientParam['UTENTE']);
        $irideClient->setRuolo($this->clientParam['RUOLO']);
        $irideClient->setUsername($this->clientParam['USERNAME']);
        $irideClient->setPassword($this->clientParam['PASSWORD']);
        $irideClient->setTimeout($this->clientParam['WSIRIDETIMEOUT']);
        //$irideClient->setAggiornaAnagrafiche($this->clientParam['AGGIORNAANAGRAFICHE']);
        $irideClient->setAggiornaAnagrafiche("F");
        $irideClient->setCodiceAmministrazione($this->clientParam['CODICEAMMINISTRAZIONE']);
        $irideClient->setCodiceAOO($this->clientParam['CODICEAOO']);
//        $irideClient->setWebservices_uri($this->clientParam['uri']);
//        $irideClient->setWebservices_wsdl($this->clientParam['wsdl']);
//        $irideClient->setNameSpaces();
//        $irideClient->setNamespace($this->clientParam['ns']);
//        $irideClient->setUtente($this->clientParam['utente']);
//        $irideClient->setRuolo($this->clientParam['ruolo']);
//        $irideClient->setUsername($this->clientParam['username']);
//        $irideClient->setPassword($this->clientParam['password']);
//        $irideClient->setTimeout($this->clientParam['timeout']);
//        $irideClient->setAggiornaAnagrafiche($this->clientParam['aggiornAnagrafiche']);
//        $CodiceAmministrazione = $devLib->getEnv_config($this->clientParam['codiceamministrazione']);
//        $irideClient->setCodiceAmministrazione($this->clientParam['codiceamministrazione']);
//        $irideClient->setCodiceAOO($this->clientParam['codiceaoo']);
    }

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo")
     * @return type
     */
    //function LeggiProtocollo($param) {
    function LeggiProtocollo($elementi) {
        $param = array();
        $param['AnnoProtocollo'] = substr($elementi['dati']['dataProtocolloAntecedente'], 0, 4);
        $param['NumeroProtocollo'] = $elementi['dati']['numeroProtocolloAntecedente'];
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
            $ritorno["RetValue"]['DatiProtocollo'] = array(
                'TipoProtocollo' => 'Iride',
                'NumeroProtocollo' => $risultato['NumeroProtocollo'],
                'Data' => $risultato['DataProtocollo'],
                'DocNumber' => $risultato['IdDocumento'],
                'idFascicolo' => $risultato['NumeroPratica'],
                'Segnatura' => '',
                'Anno' => $risultato['AnnoProtocollo'],
                'Classifica' => $risultato['Classifica'] . " - " . $risultato['Classifica_Descrizione'],
                'Oggetto' => $risultato['Oggetto'],
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
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
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

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi, $origine = "A") {
        $irideClient = new itaIrideClient();
        $this->setClientConfig($irideClient);

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
            //Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
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
                    if ($this->clientParam['DENOM_DEST_SEP'] == "Si") {
                        $MD->setNome($Nome);
                    }
                }

                /*
                 * TODO: Mdalita confoigurabile per i protocolli iride/jiride che contemplato le denominazioni spezzat per cognome e nome
                 * 
                  if ($Denominazione) {
                  $MD->setCognomeNome($Cognome);
                  $MD->setNome($Nome);
                  }
                 */


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

        $ret = $irideClient->ws_InserisciProtocolloString($param);
        if (!$ret) {
            if ($irideClient->getFault()) {
                $msg = $irideClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($irideClient->getError()) {
                $msg = $irideClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $irideClient->getResult();

        //
        //Elaboro Xml d'usicta
        //
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($risultato);
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
                $err_allegati[$err_n] = $allegato['NomeAllegato'];
                $err_n++;
            }
        }
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '<br>';
            }
            $ritorno['strNoProt'] = $err_str . "<br>" . $irideClient->getResult();
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
        if (!$param['arrayDoc']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Nessun documento da allegare";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $err_allegati = array();
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
                if ($result['Errore'] <> '') {
                    $err_allegati[] = htmlspecialchars($result['Errore']);
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
            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n. " . $param_1['numProt'] . " del " . $param_1['annoProt'] . "\n
                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
        }
        return $ritorno;
    }

}

?>