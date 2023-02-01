<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DATI A SITO FRONT OFFICE DI CART TOSCANA
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi 
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    25.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
//include_once ITA_BASE_PATH . './apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCart.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCart/itaCartServiceClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaUUID.class.php');


/**
 * Description of praFrontOfficeCartWs
 *
 * @author Simone Franchi
 */
class praFrontOfficeCartWs extends praFrontOfficeManager {

    private $praLibCart;

    function __construct() {
        parent::__construct();
    }

    /**
     *
     * @return boolean
     */
    public function scaricaPraticheNuove() {
        $this->retStatus = array(
            'Status' => true,
            'Lette' => 0,
            'Scaricate' => 0,
            'Errori' => 0,
            'Messages' => array()
        );

        // Scarico pratiche dal Cart con riempimento tabelle cart_*
        $arrayXml = $this->getElencoStimoliNuovi();

        if ($arrayXml) {

            if (is_array($arrayXml)) {
//                $this->retStatus['Lette'] = count($arrayXml);
                // Ci sono nuovi stimoli da rileggere
                foreach ($arrayXml as $idStimolo) {
                    //$frontOffice->leggistimolo($idStimolo);  
                    if (!$this->leggistimolo($idStimolo)) {
//                        $this->retStatus['Errori'] += 1;
//                        $this->retStatus['Status'] = false;
//                        $this->retStatus['Messages'][] = $this->getErrMessage();
                    } else {
//                        $this->retStatus['Scaricate'] += 1;
                    }
                }
            } else {
                if (is_string($arrayXml)) {
                    $this->retStatus['Lette'] = 1;
                    // C'è un nuovo stimolo da rileggere
                    $idStimolo = $arrayXml;
                    if (!$this->leggistimolo($idStimolo)) {
                        //if (!$frontOffice->leggistimolo($idStimolo)) {
//                        $this->retStatus['Errori'] += 1;
//                        $this->retStatus['Status'] = false;
//                        $this->retStatus['Messages'][] = $this->getErrMessage();
                    } else {
//                        $this->retStatus['Scaricate'] += 1;
                    }
                }
            }
        } else {
//            $this->retStatus['Errori'] += 1;
//            $this->retStatus['Status'] = false;
//            $this->retStatus['Messages'][] = $this->getErrMessage();
        }

        // Scorre tebelle cart_stimolo non riportate in PRAFOLIST e le elabora per il riporto.
        $this->riportaStimoliInPrafolist();

        return true;
    }

    private function riportaStimoliInPrafolist() {
        $tuttoOk = true;
        $this->praLibCart = new praLibCart();

        $sql = "SELECT * FROM CART_STIMOLO "
                . " WHERE CART_STIMOLO.PRAFOLISTROWID = 0 ";

        $cart_stimolo_tab = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, true);

        if (!$cart_stimolo_tab) {
            return;
        }

        //Out::msgInfo("REcord CART_STIMOLO", print_r($cart_stimolo_tab,true));

        foreach ($cart_stimolo_tab as $cart_stimolo_rec) {

            switch ($cart_stimolo_rec['TIPOSTIMOLO']) {
                case "presentazionePratica":
                    $this->retStatus['Lette'] += 1;

                    if (!$this->praFoListPresentazionePratica($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "valutazioneIntegrazione":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "valutazioneConformazione":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "invioIntegrazioni":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "invioConformazioni":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "esitoNegativo":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "comunicazione":
                    $this->retStatus['Lette'] += 1;
                    if (!$this->praFoListStimolo($cart_stimolo_rec)) {
                        $this->retStatus['Errori'] += 1;
                        $this->retStatus['Status'] = false;
                        $this->retStatus['Messages'][] = $this->getErrMessage();

                        return false;
                    } else {
                        $this->retStatus['Scaricate'] += 1;
                    }
                    break;
                case "pubblicaRicevuta":
                    if (!$this->praFoListRicevuta($cart_stimolo_rec)) {
                        return false;
                    }
                    break;
                default:  // Altri casi non 
                    $tuttoOk = false;
                    break;
            }
        }

        return $tuttoOk;
    }

    public function getElencoStimoliNuovi() {
        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);

        $retCall = $cartClient->ws_getAllMessagesId();
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage($cartClient->getFault() . " " . $cartClient->getError);
            return false;
        }

        $retXml = $cartClient->getResult();
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in lettura XML');
            return false;
        }

        //Out::msgInfo("Riposta getIdMessagesId", print_r($retXml, true));

        return $retXml;
    }

    public function leggistimolo($idStimolo) {
        $tuttoOk = true;
        $this->praLibCart = new praLibCart();

        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);

        $param = array(
            'idEGov' => $idStimolo
        );

        $retCall = $cartClient->ws_getMessage($param);
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore lettura dati da CART " . $cartClient->getFault() . " " . $cartClient->getError);
            return false;
        }
        // La risposta è da nel tab <messagge> ed è in base64
        $result = $cartClient->getResult();

        $messaggio = base64_decode($result['message']);

        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($messaggio);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore lettura dati da CART - Non è stato possibile comprendere il messaggio ");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());


        $corpo = $arrayXml['SOAP-ENV:Body'][0];
        //Out::msgInfo("Corpo del messaggio", print_r($corpo,true));

        $confermaRicezione = $corpo['confermaRicezioneReq'][0];

        if ($confermaRicezione) {   // (in_array('confermaRicezioneReq', $corpo)) {
            $tuttoOk = $this->analizzaConfermaRicezione($corpo);
        } else {
            $tuttoOk = $this->analizzaStimoloLetto($corpo);
        }

        return $tuttoOk;
    }

    private function analizzaConfermaRicezione($corpo) {
        $esito = "OK";

        $cart_stimolo_rec = array();
        $cart_stimolo_rec['IDMESSAGGIO'] = $corpo['confermaRicezioneReq'][0]['idMessaggio'][0][itaXML::textNode];

        $cart_stimolo_rec['MITTENTE_TIPO'] = $corpo['confermaRicezioneReq'][0]['mittente'][0]['tipologia'][0][itaXML::textNode];
        $cart_stimolo_rec['MITTENTE_ENTE'] = $corpo['confermaRicezioneReq'][0]['mittente'][0]['ente'][0][itaXML::textNode];
        $cart_stimolo_rec['DATACAR'] = $corpo['confermaRicezioneReq'][0]['ricezione'][0][itaXML::textNode];
        $cart_stimolo_rec['DATA'] = $corpo['confermaRicezioneReq'][0]['ricezione'][0][itaXML::textNode];

        if (array_key_exists("errore", $corpo['confermaRicezioneReq'][0])) {
            $esito = $corpo['confermaRicezioneReq'][0]['errore'][0][itaXML::attribute]['codiceErrore'];
            $cart_stimolo_rec['CODERRORE'] = $esito;
            $cart_stimolo_rec['DESCERRORE'] = $corpo['confermaRicezioneReq'][0]['errore'][0]['note'][0][itaXML::textNode];
        }


        // Out::msgInfo("confermaRicezione", print_r($cart_stimolo_rec,true));
        // Si aggiornano campi DATACONFRICEZIONE,  DATARICEZIONE, ESITORICEZIONE, MSGRICEZIONE del record CART_INVIO associato
        $cart_invio_rec = $this->praLibCart->getCart_invio($cart_stimolo_rec['IDMESSAGGIO']);
        if ($cart_invio_rec) {

            $cart_invio_rec['DATACONFRICEZIONE'] = $cart_stimolo_rec['DATACAR'];
            $cart_invio_rec['DATARICEZIONE'] = $cart_stimolo_rec['DATA'];
            $cart_invio_rec['ESITORICEZIONE'] = $esito;
            $cart_invio_rec['MSGRICEZIONE'] = json_encode($corpo);
            //$cart_invio_rec['MSGRICEZIONE'] = $cart_stimolo_rec['DESCERRORE'];

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_INVIO', 'ROW_ID', $cart_invio_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento conferma ricezione in CART_INVIO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Conferma Ricezione non ritrovata per il messaggio  = " . $cart_stimolo_rec['IDMESSAGGIO'] . ".");
            return false;
        }

        return true;
    }

    private function analizzaStimoloLetto($corpo) {
        // Aggiungo record nella tabella cart_stimolo

        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);


        $idMessaggio = $corpo['riceviStimoloAsync'][0]['idMessaggio'][0][itaXML::textNode];
        $mittente_tipologia = $corpo['riceviStimoloAsync'][0]['stimolo'][0]['mittente'][0]['tipologia'][0][itaXML::textNode];
        $mittente_ente = $corpo['riceviStimoloAsync'][0]['stimolo'][0]['mittente'][0]['ente'][0][itaXML::textNode];
        $stimolo_data = $corpo['riceviStimoloAsync'][0]['stimolo'][0]['data'][0][itaXML::textNode];
        $stimolo_idPratica = $corpo['riceviStimoloAsync'][0]['stimolo'][0]['idPratica'][0][itaXML::textNode];

        $attributiXml = $corpo['riceviStimoloAsync'][0]['stimolo'][0]['attributiSpecifici'][0];
        $attributiSpecifici = "";

        //Out::msgInfo("Ente", "Ente messaggio: " . $mittente_ente . " Ente Configurato: " . $cartClient->getCodEnte());

        if ($cartClient->getCodEnte() && $mittente_ente != $cartClient->getCodEnte()) {
            // Se il destinatario del messaggio non è l'ente configurato, si scarta.
            // Vale soprattuto per UNIONE dei COMUNI
            return;
        }

        //Out::msgInfo("XML Attributi Speciali", print_r($attributiXml,true));

        $valore = "presentazionePratica";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "valutazioneIntegrazione";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "valutazioneConformazione";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "invioIntegrazioni";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "invioConformazioni";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "esitoNegativo";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "comunicazione";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }
        $valore = "pubblicaRicevuta";
        if (array_key_exists($valore, $attributiXml) && $attributiXml[$valore] != "") {
            $attributiSpecifici = $valore;
        }

        //if (array_key_exists( "pubblicaRicevuta", $attributiXml ) && $attributiXml["pubblicaRicevuta"] != "") $attributiSpecifici = "pubblicaRicevuta";
//        Out::msgInfo("Dati Letti", "idMessaggio = " . print_r($idMessaggio,true) .
//                "<BR> Mittente Tipologia = " . $mittente_tipologia . 
//                "<BR> Mittente Ente = " . $mittente_ente .
//                "<BR> Data stimolo = " . $stimolo_data .
//                "<BR> Id Pratica = " . $stimolo_idPratica . 
//                "<BR> Tipo di Stimolo = " . $attributiSpecifici);
        // Aggiungo record nella tabella cart_stimolo
        $praLibCart = new praLibCart();

        $cart_stimolo_rec = $praLibCart->getCart_stimolo($idMessaggio);
        if (!$cart_stimolo_rec) {

//            $IdTab = $praLibCart->getLastId('CART_STIMOLO');
//            $cart_stimolo_rec['ID'] = $IdTab;

            $cart_stimolo_rec['IDMESSAGGIO'] = $idMessaggio;
            $cart_stimolo_rec['MITTENTE_TIPO'] = $mittente_tipologia;
            $cart_stimolo_rec['MITTENTE_ENTE'] = $mittente_ente;
            $cart_stimolo_rec['DATACAR'] = $stimolo_data;
            $cart_stimolo_rec['IDPRATICA'] = $stimolo_idPratica;
            $cart_stimolo_rec['TIPOSTIMOLO'] = $attributiSpecifici;
            $cart_stimolo_rec['LETTOTUTTO'] = 0;
            $cart_stimolo_rec['DATA'] = $stimolo_data;
            $cart_stimolo_rec['CONFERMARICEZIONE'] = 0;
            $cart_stimolo_rec['METADATI'] = json_encode($corpo);
            //$cart_stimolo_rec['IDTABRIF'] = -1;

            try {
                $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROWID', $cart_stimolo_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento su cart_stimolo non avvenuto.");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'inserimento su cart_stimolo.");
                return false;
            }
        }


        $cart_stimolo_rec = $praLibCart->getCart_stimolo($idMessaggio);
        if (!$cart_stimolo_rec) {
            return false;
        }

        //Se CART_STIMOLO.LETTOTUTTO = 0 -> Si prosegue con la lettura
        if ($cart_stimolo_rec['LETTOTUTTO'] == 0) {

            switch ($attributiSpecifici) {
                case "presentazionePratica":
                    if (!$this->presentazionePratica($attributiXml, $idMessaggio)) {
                        return false;
                    }
                    break;
                case "valutazioneIntegrazione":
                    if (!$this->valutazioneIntegraConforma($attributiXml, $idMessaggio, true)) {
                        return false;
                    }
                    break;
                case "valutazioneConformazione":
                    if (!$this->valutazioneIntegraConforma($attributiXml, $idMessaggio, false)) {
                        return false;
                    }
                    break;
                case "invioIntegrazioni":
                    if (!$this->invioIntegraConforma($attributiXml, $idMessaggio, $attributiSpecifici)) {
                        return false;
                    }
                    break;
                case "invioConformazioni":
                    if (!$this->invioIntegraConforma($attributiXml, $idMessaggio, $attributiSpecifici)) {
                        return false;
                    }
                    break;
                case "esitoNegativo":
                    if (!$this->esitoNegativo($attributiXml, $idMessaggio)) {
                        return false;
                    }
                    break;
                case "comunicazione":
                    if (!$this->comunicazione($attributiXml, $idMessaggio)) {
                        return false;
                    }
                    break;
                case "pubblicaRicevuta":
                    if (!$this->pubblicaRicevuta($attributiXml['pubblicaRicevuta'][0]['ricevuta'][0], $idMessaggio)) {
                        return false;
                    }
                    break;
            }

            if (!$this->leggiAllegatiStimolo($attributiXml, $idMessaggio)) {
                return false;
            }


            //SI SCARICANO GLI EVENTUALI ALLEGATI PRESENTI NELLO STIMOLO RILETTO (tabella CART_STIMOLOFILE).
            //Se tutti gli allegati sono riletti correttamente, si imposta CART_STIMOLO.LETTOTUTTO = 1;
            if ($this->salvaAllegatiStimoli($idMessaggio)) {
                $cart_stimolo_rec = $praLibCart->getCart_stimolo($idMessaggio);
                if (!$cart_stimolo_rec) {
                    return false;
                }

                // Si imposta CART_STIMOLO.LETTOTUTTO = 1.
                $cart_stimolo_rec['LETTOTUTTO'] = 1;

                $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Aggiornamento CART_STIMOLO.LETTOTUTTO = 1 per  IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

                    return false;
                }
            }
        }

        $cart_stimolo_rec = $praLibCart->getCart_stimolo($idMessaggio);
        if (!$cart_stimolo_rec) {
            return false;
        }

        // Se letto tutto e CART_STIMOLO.CONFERMARICEZIONE == 0, c'è da inviare messaggio di conferma ricezione.
        if ($cart_stimolo_rec['LETTOTUTTO'] == 1 && $cart_stimolo_rec['CONFERMARICEZIONE'] == 0) {

            $this->confermaRicezioneStimoli($cart_stimolo_rec);
        }

        return true;
    }

    private function confermaRicezioneStimoli($cart_stimolo_rec) {

        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);

        $param = "<proc:mittente>" .
                "<proc:tipologia>SUAP</proc:tipologia>" .
                "<proc:ente>" . $cart_stimolo_rec['MITTENTE_ENTE'] . "</proc:ente>" .
                "</proc:mittente>" .
                "<proc:idMessaggio>" . $cart_stimolo_rec['IDMESSAGGIO'] . "</proc:idMessaggio>" .
                "<proc:ricezione>" . $this->praLibCart->getDatetimeNow() . "</proc:ricezione>";

        //Out::msgInfo("Messaggio che si invia per confermaRicezioneReq ", print_r($param, true));


        $retCall = $cartClient->ws_fruizione($param, 'confermaRicezioneReq');
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'invio della Conferma ricezione  della pratica  " . $cart_stimolo_rec['IDPRATICA']);
        }
        $result = $cartClient->getResult();

        //Out::msgInfo("Risultato confermaRicezioneReq ", print_r($result, true));
        // Vedere se $result è un array
        if (is_array($result)) {
            $esito = $result['esito'];
            $msgErrore = "";
            if ($result['!msgErrore']) {
                $msgErrore = $result['!msgErrore'];
            }
        } else {
            $esito = $result;
        }

        if ($esito == 'OK') {
            $cart_stimolo_rec['CONFERMARICEZIONE'] = 1;

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento CART_STIMOLO.CONFERMARICEZIONE = 1 per  IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

                return false;
            }
        }
    }

    private function salvaAllegatiStimoli($idMsgCartStimolo) {
        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
        if (!$cart_stimolo_rec) {
            return false;
        }

        $cart_stimoloFile_tab = $this->praLibCart->getCart_stimoloFile($idMsgCartStimolo, 'idMessaggioCart', true);
        if (!$cart_stimoloFile_tab) {
            return false;
        }

        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);

        $dir = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');
        // $dir = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDPRATICA'], 'STIMOLO');


        foreach ($cart_stimoloFile_tab as $file_rec) {

            if ($file_rec['NOMEFILE']) {

                $fileDest = $dir . "/" . $file_rec['NOMEFILE'];
                // Se file già presente, si passa al successivo
                if (file_exists($fileDest)) {
                    continue;
                }

                $param = "<proc:mittente>" .
                        "<proc:tipologia>SUAP</proc:tipologia>" .
                        "<proc:ente>" . $cart_stimolo_rec['MITTENTE_ENTE'] . "</proc:ente>" .
                        "</proc:mittente>" .
                        "<proc:idMessaggio>" . $idMsgCartStimolo . "</proc:idMessaggio>" .
                        "<proc:contentID>" . $file_rec['IDFILE'] . "</proc:contentID>";

                $retCall = $cartClient->ws_fruizione($param, 'richiediAllegatoReq');
                if (!$retCall) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nel rileggere il file " . $file_rec['NOMEFILE'] . " della pratica  " . $cart_stimolo_rec['IDPRATICA']);
                }
                $result = $cartClient->getResult();
                $allegati = $cartClient->getAttachments();

                if ($allegati) {
                    file_put_contents($fileDest, $allegati[0]['data']);
                    //Out::msgInfo("Allegati", print_r($allegati, true));
                }

                //Out::msgInfo("Risultato RichiediAllegatoReq ", print_r($result, true));
            }
        }

        // Si controlla se tutti gli allegati sono realmente presenti nel filesystem,
        // Se SI, si imposta CART_STIMOLO.LETTOTUTTO = 1;
        $lettoTutto = true;
        foreach ($cart_stimoloFile_tab as $file_rec) {

            $fileDest = $dir . "/" . $file_rec['NOMEFILE'];
            // Se file già presente, si passa al successivo
            if (!file_exists($fileDest)) {
                $lettoTutto = false;
                break;
            }
        }


        return $lettoTutto;
    }

    private function esitoNegativo($attributiXml, $idMsgCartStimolo) {
        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
        if (!$cart_stimolo_rec) {
            return false;
        }

        $cart_stimolo_rec['CODICEENDO'] = $attributiXml['esitoNegativo'][0][itaXML::attribute]['identificativoModulo'];

        $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

        if ($nRows == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento CODICEENDO in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

            return false;
        }

        if ($attributiXml['esitoNegativo'][0]['esitoNegativoPerEndoprocedimento']) {
            foreach ($attributiXml['esitoNegativo'][0]['esitoNegativoPerEndoprocedimento'] as $esitoNegativo) {

                $idEndo = $esitoNegativo['idEndoprocedimento'][0][itaXML::textNode];

                //Si Legge comunicazioneFormaleType
                if (!$this->salvaComunicazioneFormale($esitoNegativo['comunicazione-formale'][0], $idMsgCartStimolo, $idEndo)) {
                    return false;
                }
            }
        }

        return true;
    }

    private function valutazioneIntegraConforma($attributiXml, $idMsgCartStimolo, $tipoXml) {

        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
        if (!$cart_stimolo_rec) {
            return false;
        }


        $valutazione = "valutazione" . $tipoXml;
        $richiesta = "richiesta" . $tipoXml;

        $richiestaDocumentazioneXml = $attributiXml[$valutazione][0][$richiesta][0];

        $cart_stimolo_rec['NUMGIORNICAR'] = $richiestaDocumentazioneXml['rispostaAttesaIn'][0][itaXML::attribute]['giorni'];
        $cart_stimolo_rec['NUMGIORNI'] = $this->praLibCart->getDurationGiorni($cart_stimolo_rec['NUMGIORNICAR']);

        $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

        if ($nRows == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento numero giorni in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

            return false;
        }

        //Si Legge moduloRichiestaType
        foreach ($richiestaDocumentazioneXml['moduloRichiesto'] as $moduloRichiestoXml) {

            if (!$this->salvaAllegatoRichiestoType($moduloRichiestoXml, $idMsgCartStimolo)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura del modulo richiesto per IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");

                return false;
            }
        }

        //Si Legge comunicazioneFormaleType
        if (!$this->salvaComunicazioneFormale($moduloRichiestoXml, $idMsgCartStimolo, 'comunicazione')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura della comunicazione formale per IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");

            return false;
        }

        return true;
    }

    private function salvaComunicazioneFormale($moduloRichiestoXml, $idMsgCartStimolo, $usoModello) {

        if ($moduloRichiestoXml['messaggio'][0]) {

            $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
            if (!$cart_stimolo_rec) {
                return false;
            }

            $cart_stimolo_rec['MESSAGGIO'] = $moduloRichiestoXml['messaggio'][0][itaXML::textNode];

            //Out::msgInfo("CART_STIMOLO record", print_r($cart_stimolo_rec, true));

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento campo messaggio in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

                return false;
            }
        }

        //Si legge comunicazionePDF
        if (!$this->salvaAllegatoStimolo($moduloRichiestoXml['comunicazionePDF'][0], $idMsgCartStimolo, 'allegatoPDFType', $usoModello, "comunicazionePDF")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura allegato comunicazionePDF con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallita.");

            return false;
        }

        //Si leggono eventuali comunicazioniSecondarie
        if ($moduloRichiestoXml['comunicazioniSecondarie']) {
            foreach ($moduloRichiestoXml['comunicazioniSecondarie'] as $comunicazioneScondaria) {

                if (!$this->salvaAllegatoStimolo($comunicazioneScondaria, $idMsgCartStimolo, 'allegatoType', $usoModello, "comunicazioniSecondarie")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Lettura allegato comunicazione secondaria con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallita.");

                    return false;
                }
            }
        }
        return true;
    }

    private function salvaAllegatoRichiestoType($moduloRichiestoXml, $idMsgCartStimolo) {
        $identificativoModulo = $moduloRichiestoXml[itaXML::attribute]['identificativoModulo'];
        $enteRichiedente = $moduloRichiestoXml[itaXML::attribute]['enteRichiedente'];

        if ($moduloRichiestoXml['allegato']) {

            // Scorrere gli allegati
            foreach ($moduloRichiestoXml['allegato'] as $allegato) {

                $firmato = $allegato[itaXML::attribute]['firmato'];

                if ($allegato['link'][0]) {
                    $cart_stimoloFile_rec = array();

                    $cart_stimoloFile_rec['LINK'] = $allegato['link'][0][itaXML::textNode];
                    $cart_stimoloFile_rec['USOMODELLO'] = $identificativoModulo;
                    $cart_stimoloFile_rec['TIPOFILE'] = "moduloRichiesto";
                    $cart_stimoloFile_rec['ENTE'] = $enteRichiedente;
                    $cart_stimoloFile_rec['IDMSGCARTSTIMOLO'] = $idMsgCartStimolo;
                    $cart_stimoloFile_rec['FIRMATO'] = $firmato;

                    $arrayDestinatari = array();
                    if ($enteRichiedente) {
                        $arrayDestinatari[0] = $enteRichiedente;
                    }

                    // Si crea record CART_STIMOLOFILE
                    $condizione = " WHERE CART_STIMOLOFILE.IDMSGCARTSTIMOLO LIKE '" . $cart_stimoloFile_rec['IDMSGCARTSTIMOLO'] . "'"
                            . "AND CART_STIMOLOFILE.LINK = '" . $cart_stimoloFile_rec['LINK'] . "'";
                    $presente_rec = $praLibCart->getRecordCart("CART_STIMOLOFILE", $condizione);
                    if (!$presente_rec) {

                        try {
                            $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_STIMOLOFILE', 'ROWID', $cart_stimoloFile_rec);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Inserimento su cart_stimolofile non avvenuto.");
                                return false;
                            }
                        } catch (Exception $e) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Errore nell'inserimento su cart_stimolofile.");
                            return false;
                        }

                        if (!empty($arrayDestinatari)) {

                            foreach ($arrayDestinatari as $destinatario) {

                                $cart_stimoloFileDest = array();
                                $cart_stimoloFileDest['DESTINATARIO'] = $destinatario;
                                $cart_stimoloFileDest['IDMSGCARTSTIMOLO'] = $idMsgCartStimolo;
                                $cart_stimoloFileDest['IDFILE'] = $cart_stimoloFile_rec['IDFILE'];

                                // Si crea record CART_STIMOLOFILE_DEST
                                $condizione = " WHERE CART_STIMOLOFILE_DEST.IDMSGCARTSTIMOLO LIKE '" . $cart_stimoloFileDest['IDMSGCARTSTIMOLO'] . "'"
                                        . " AND CART_STIMOLOFILE_DEST.IDFILE = '" . $cart_stimoloFileDest['IDFILE'] . "'"
                                        . " AND CART_STIMOLOFILE_DEST.DESTINATARIO = '" . $cart_stimoloFileDest['DESTINATARIO'] . "'";
                                $result_rec = $praLibCart->getRecordCart("CART_STIMOLOFILE", $condizione);
                                if (!$result_rec) {

                                    try {
                                        $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_STIMOLOFILE_DEST', 'ROWID', $cart_stimoloFileDest);
                                        if ($nRows == -1) {
                                            $this->setErrCode(-1);
                                            $this->setErrMessage("Inserimento su cart_stimolofile_dest non avvenuto.");
                                            return false;
                                        }
                                    } catch (Exception $e) {
                                        $this->setErrCode(-1);
                                        $this->setErrMessage("Errore nell'inserimento su cart_stimolofile_dest.");
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $destinatari = array($enteRichiedente);
                    if (!$this->salvaAllegatoStimolo($allegato['attach'][0], $idMsgCartStimolo, "allegatoType", $identificativoModulo, "moduloRichiesto", $destinatari, $enteRichiedente)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nella lettura dell'allegato");
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function invioIntegraConforma($attributiXml, $idMsgCartStimolo, $tipoXml) {
        if ($tipoXml == 'invioIntegrazioni') {
            $proposta = 0;
            if ($attributiXml['invioIntegrazioni'][0]['proposta'][0][itaXML::textNode] == 'true') {
                $proposta = 1;
            }

            //Out::msgInfo("Tag Proposta", $attributiXml['invioIntegrazioni'][0]['proposta'][0][itaXML::textNode]);
            //Out::msgInfo("Flag Proposta", $proposta);

            $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
            if (!$cart_stimolo_rec) {
                return false;
            }

            $cart_stimolo_rec['PROPOSTA'] = $proposta;

            //Out::msgInfo("CART_STIMOLO record", print_r($cart_stimolo_rec, true));

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento tipo procedimento in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

                return false;
            }
        }

        // Leggo i moduliCompilati
        if ($attributiXml[$tipoXml][0]['moduloCompilato']) {
            foreach ($attributiXml[$tipoXml][0]['moduloCompilato'] as $moduloCompilatoXml) {

                if (!$this->leggiModuloCompilato($moduloCompilatoXml, $idMsgCartStimolo)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Lettura modulo compilato  con IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");

                    return false;
                }
            }
        }
        return true;
    }

    private function pubblicaRicevuta($attributiXml, $idMsgCartStimolo) {
        return $this->salvaAllegatoStimolo($attributiXml, $idMsgCartStimolo, 'allegatoPDFType', 'ricevuta', 'ricevuta');
    }

    private function comunicazione($attributiXml, $idMsgCartStimolo) {
        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
        if (!$cart_stimolo_rec) {
            return false;
        }

        $cart_stimolo_rec['DESTINATARIO'] = $attributiXml['comunicazione'][0]['destinatario'][0][itaXML::textNode];
        $cart_stimolo_rec['OGGETTO'] = $attributiXml['comunicazione'][0]['oggetto'][0][itaXML::textNode];
        if (array_key_exists('corpo', $attributiXml)) {
            //if (array_key_exists( 'corpo', $attributiXml['comunicazione'][0] ) ) {   
            //if ($attributiXml['comunicazione'][0]['corpo'][0] ) {   
            $cart_stimolo_rec['MESSAGGIO'] = $attributiXml['comunicazione'][0]['corpo'][0][itaXML::textNode];
        }
        //if (array_key_exists( 'attesaRisposta', $attributiXml[0] ) ) {   
        if ($attributiXml['comunicazione'][0]['attesaRisposta'][0]) {

            $cart_stimolo_rec['NUMGIORNICAR'] = $attributiXml['comunicazione'][0]['attesaRisposta'][0][itaXML::attribute]['giorni'];

            $cart_stimolo_rec['NUMGIORNI'] = $this->praLibCart->getDurationGiorni($cart_stimolo_rec['NUMGIORNICAR']);
        }

        //Out::msgInfo("CART_STIMOLO record", print_r($cart_stimolo_rec, true));

        $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

        if ($nRows == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento tipo procedimento in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

            return false;
        }


        if ($attributiXml['comunicazione'][0]['allegato']) {
            // Scorro allegati aggiuntivi se presenti dopo lo stimolo 
            foreach ($attributiXml['comunicazione'][0]['allegato'] as $allegatoXml) {

                if (!$this->salvaAllegatoStimolo($allegatoXml, $idMsgCartStimolo, 'allegatoType')) {
                    return false;
                }
            }
        }

        return true;
    }

    private function presentazionePratica($attributiXml, $idMsgCartStimolo) {
        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($idMsgCartStimolo);
        if (!$cart_stimolo_rec) {
            return false;
        }

        $cart_stimolo_rec['TIPOPROCEDIMENTO'] = $attributiXml['presentazionePratica'][0]['tipoProcedimento'][0][itaXML::textNode];

        //Out::msgInfo("CART_STIMOLO record", print_r($cart_stimolo_rec, true));

        $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);

        if ($nRows == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento tipo procedimento in CART_STIMOLO con IDMESSAGGIO = " . $cart_stimolo_rec['IDMESSAGGIO'] . " fallito.");

            return false;
        }

        if (!$this->salvaAllegatoStimolo($attributiXml['presentazionePratica'][0]['copertina-xml'][0], $idMsgCartStimolo, 'allegatoXMLType', "copertina-xml", "copertina-xml")) {
            return false;
        }
        if (!$this->salvaAllegatoStimolo($attributiXml['presentazionePratica'][0]['copertina-pdf'][0], $idMsgCartStimolo, 'allegatoPDFType', "copertina-pdf", "copertina-pdf")) {
            return false;
        }

        // Leggo i moduloCompilato
        foreach ($attributiXml['presentazionePratica'][0]['moduloCompilato'] as $moduloCompilatoXml) {

            if (!$this->leggiModuloCompilato($moduloCompilatoXml, $idMsgCartStimolo)) {
                return false;
            }
        }

        return true;
    }

    private function leggiModuloCompilato($moduloCompilatoXml, $idMsgCartStimolo) {
        //Cerco eventuali destinatari presenti
        $arrayDestinatari = array();

        // Scorro allegati aggiuntivi se presenti dopo lo stimolo 
        if ($moduloCompilatoXml['destinatari']) {
            foreach ($moduloCompilatoXml['destinatari'] as $destinatariXml) {

                $arrayDestinatari[] = $destinatariXml['ente'][0][itaXML::textNode];
            }
        }


        $identificativoModulo = $moduloCompilatoXml[itaXML::attribute]['identificativoModulo'];

        if (array_key_exists('modulo-engine', $moduloCompilatoXml) && $moduloCompilatoXml['modulo-engine'] != "") {
            if (!$this->salvaAllegatoStimolo($moduloCompilatoXml['modulo-engine'][0], $idMsgCartStimolo, 'allegatoGenericoType', $identificativoModulo, 'modulo-engine', $arrayDestinatari)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura allegato modulo-engine con IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");
                return false;
            }
        }

        if (!$this->salvaAllegatoStimolo($moduloCompilatoXml['modello-MDA'][0], $idMsgCartStimolo, 'allegatoXMLType', $identificativoModulo, 'modello-MDA', $arrayDestinatari)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura allegato modello-MDA con IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");
            return false;
        }

        if (!$this->salvaAllegatoStimolo($moduloCompilatoXml['modello-PDF'][0], $idMsgCartStimolo, 'allegatoPDFType', $identificativoModulo, 'modello-PDF', $arrayDestinatari)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura allegato modello-PDF con IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");
            return false;
        }

        if ($moduloCompilatoXml['allegato']) {
            foreach ($moduloCompilatoXml['allegato'] as $allegatoXml) {

                if (!$this->salvaAllegatoStimolo($allegatoXml, $idMsgCartStimolo, 'allegatoInModuloType', $identificativoModulo, 'allegato', $arrayDestinatari)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Lettura allegato con IDMESSAGGIO = " . $idMsgCartStimolo . " fallita.");
                    return false;
                }
            }
        }
        return true;
    }

    private function leggiAllegatiStimolo($attributiXml, $idMsgCartStimolo) {

        // Scorro allegati aggiuntivi se presenti dopo lo stimolo 
        if ($attributiXml['allegati']) {
            foreach ($attributiXml['allegati'] as $allegatoXml) {
                if (!$this->salvaAllegatoStimolo($allegatoXml, $idMsgCartStimolo, 'allegatoGenericoType'))
                    return false;
                //$praLibCart->salvaAllegatoStimolo($allegatoXml, $idCart_stimolo, 'allegatoGenericoType');
            }
        }

        return true;
    }

    public function salvaAllegatoStimolo($allegatoXml, $idMsgCartStimolo, $tipoAllegato = 'allegatoType', $usoModello = '', $tipoFile = '', $arrayDestinatari = array(), $enteRichiedente = '') {
        $cart_stimoloFile_rec = array();
        $praLibCart = new praLibCart();

        if (array_key_exists("hashedFile", $allegatoXml)) {
            $cart_stimoloFile_rec['IDFILE'] = $allegatoXml['hashedFile'][0]['contentID'][0][itaXML::textNode];
            $cart_stimoloFile_rec['HASHFILE'] = $allegatoXml['hashedFile'][0]['hash'][0][itaXML::textNode];
        } else {
            if (array_key_exists("contentID", $allegatoXml[0])) {
                $cart_stimoloFile_rec['IDFILE'] = $allegatoXml['contentID'][0][itaXML::textNode];
            }
        }



        if ($allegatoXml['contentType']) {
//        if (array_key_exists( "contentType", $allegatoXml ) ){
            $cart_stimoloFile_rec['TIPOCONTENUTO'] = $allegatoXml['contentType'][0][itaXML::textNode];
        }

        if ($allegatoXml['fileSize']) {
            $cart_stimoloFile_rec['DIMENSIONE'] = $allegatoXml['fileSize'][0][itaXML::textNode];
        }

        $cart_stimoloFile_rec['NOMEFILE'] = $allegatoXml['nomefileOriginale'][0][itaXML::textNode];

        if ($allegatoXml['codice']) {
//        if (array_key_exists( "contentType", $allegatoXml[0] ) ){
            $cart_stimoloFile_rec['CODICE'] = $allegatoXml['codice'][0][itaXML::textNode];
        }

        $cart_stimoloFile_rec['IDSEMANTICO'] = $allegatoXml['idSemantico'][0][itaXML::textNode];


        $cart_stimoloFile_rec['USOMODELLO'] = $usoModello;
        $cart_stimoloFile_rec['TIPOFILE'] = $tipoFile;
        $cart_stimoloFile_rec['ENTE'] = $enteRichiedente;
        $cart_stimoloFile_rec['IDMSGCARTSTIMOLO'] = $idMsgCartStimolo;



        if ($tipoAllegato == 'allegatoGenericoType') {

            if ($allegatoXml['note']) {
                $cart_stimoloFile_rec['NOTE'] = $allegatoXml['note'][0][itaXML::textNode];
            }

            //Sistemare i destinatari, se presenti
            if ($allegatoXml['destinatari']) {
                $arrayDestinatari = array();

                // Scorro allegati aggiuntivi se presenti dopo lo stimolo 
                foreach ($allegatoXml['destinatari'] as $destinatariXml) {

                    $arrayDestinatari[] = $destinatariXml[0][itaXML::textNode];
                }
            }
        }

        //Out::msgInfo("Allegato XML", print_r($allegatoXml,true));
        //Out::msgInfo("REcord CARTSTIMOLOFILE", print_r($cart_stimoloFile_rec,true));
        // Si crea record CART_STIMOLOFILE
        $condizione = " WHERE CART_STIMOLOFILE.IDMSGCARTSTIMOLO LIKE '" . $cart_stimoloFile_rec['IDMSGCARTSTIMOLO'] . "'"
                . "AND CART_STIMOLOFILE.IDFILE = '" . $cart_stimoloFile_rec['IDFILE'] . "'";
        $presente_rec = $praLibCart->getRecordCart("CART_STIMOLOFILE", $condizione);
        if (!$presente_rec) {

//            $IdTab = $praLibCart->getLastId('CART_STIMOLOFILE');
//            $cart_stimoloFile_rec['ID'] = $IdTab;

            try {
                $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_STIMOLOFILE', 'ROWID', $cart_stimoloFile_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento su cart_stimolofile non avvenuto.");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'inserimento su cart_stimolofile.");
                return false;
            }

            if (!empty($arrayDestinatari)) {

                foreach ($arrayDestinatari as $destinatario) {

                    $cart_stimoloFileDest = array();
                    $cart_stimoloFileDest['DESTINATARIO'] = $destinatario;
                    $cart_stimoloFileDest['IDMSGCARTSTIMOLO'] = $idMsgCartStimolo;
                    $cart_stimoloFileDest['IDFILE'] = $cart_stimoloFile_rec['IDFILE'];

                    // Si crea record CART_STIMOLOFILE_DEST
                    $condizione = " WHERE CART_STIMOLOFILE_DEST.IDMSGCARTSTIMOLO LIKE '" . $cart_stimoloFileDest['IDMSGCARTSTIMOLO'] . "'"
                            . " AND CART_STIMOLOFILE_DEST.IDFILE = '" . $cart_stimoloFileDest['IDFILE'] . "'"
                            . " AND CART_STIMOLOFILE_DEST.DESTINATARIO = '" . $cart_stimoloFileDest['DESTINATARIO'] . "'";
                    $result_rec = $praLibCart->getRecordCart("CART_STIMOLOFILE_DEST", $condizione);
                    if (!$result_rec) {

                        try {
                            $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_STIMOLOFILE_DEST', 'ROWID', $cart_stimoloFileDest);
                            if ($nRows == -1) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Inserimento su cart_stimolofile_dest non avvenuto.");
                                return false;
                            }
                        } catch (Exception $e) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Errore nell'inserimento su cart_stimolofile_dest.");
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    private function setClientConfig($cartClient) {
        $config_tab = array();
        $devLib = new devLib();
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGENDPOINT', false);
        $cartClient->setErog_uri($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGUSER', false);
        $cartClient->setErog_username($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGPASSWD', false);
        $cartClient->setErog_password($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGNAMESPACE', false);
        $cartClient->setErog_namespacePrefix($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGTIMEOUT', false);
        $cartClient->setErog_timeout($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIENDPOINT', false);
        $cartClient->setFrui_uri($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIUSER', false);
        $cartClient->setFrui_username($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIPASSWD', false);
        $cartClient->setFrui_password($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUINAMESPACE', false);
        $cartClient->setFrui_namespacePrefix($config_val['CONFIG']);
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUITIMEOUT', false);
        $cartClient->setFrui_timeout($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIPDELEGATA', false);
        $cartClient->setPdelegata($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTCODENTE', false);
        $cartClient->setCodEnte($config_val['CONFIG']);
    }

    private function praFoListPresentazionePratica($cart_stimolo_rec) {
        // Riporta CART_STIMOLO di Presentazione Pratica in PROFILOST - PRAFOFILES
        $pathFile = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

        $lista = glob($pathFile . "/*.*");
        if ($lista === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nella directory $pathFile non sono stati ritrovati gli allegati" . $this->getErrMessage());
            return false;
        }

        $retIndex = null;
        foreach ($lista as $indice => $file) {
            if (strpos($file, 'COPERTINA-') !== false) {
                $retIndex = $indice;
                break;
            }
        }

        if ($retIndex === null) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non trovato il file con la Copertina xml per la pratica $IdPratica" . $this->getErrMessage());
            return false;
        }

        $file = $lista[$retIndex];

        $ItaXmlObj = new itaXML;
        if (!$ItaXmlObj->setXmlFromFile($file)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non trovato il file con la Copertina xml: $file ");
            return false;
        }

        $arrCopertina = $ItaXmlObj->toArray($ItaXmlObj->asObject());
        if (!$arrCopertina) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura Dati copertina non riuscita per il file: $file ");
            return false;
        }

        $descProcedimento = '';
        for ($num = 0; $num < count($arrCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0]['ns2:idEndoprocedimento']); ++$num) {

            $codProc = $arrCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0]['ns2:idEndoprocedimento'][$num][itaXml::textNode];
            if ($codProc) {

                $descProcedimento = $descProcedimento . $codProc;

                // Cerco Descrizione del procedimento nella tabella CART_ATTIVITA
                $descProc = "";
                $cart_attivita_rec = $this->praLibCart->getCart_attivita($codProc);
                if ($cart_attivita_rec) {
                    $descProc = $cart_attivita_rec['DESCATTIVITA'];
                    $descProcedimento = $descProcedimento . " -> " . $descProc;
                }
                $descProcedimento = $descProcedimento . "; ";
            }
        }

        $tipoProcedimento = $arrCopertina['oggettoComunicazione'][0]['tipoProcedimento'][0][itaXml::textNode];
        $azione = $arrCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];

        $descProcedimento = $descProcedimento .
                " " . $azione .
                " - Procedimento " . $tipoProcedimento;

        $oraArrivo = $this->getOra($arrCopertina['oggettoComunicazione'][0]['dataPresentazione'][0][itaXml::textNode]);
        $dataArrivo = $this->getData($arrCopertina['oggettoComunicazione'][0]['dataPresentazione'][0][itaXml::textNode]);

        $sportelloSuap = $arrCopertina['oggettoComunicazione'][0]['sportelloSuap'][0]['codice'][0][itaXml::textNode];

        $dataProtocollo = $cart_stimolo_rec['DATAPROTOCOLLO'];
        $oraProtocollo = '';
        $numeroProtocollo = $cart_stimolo_rec['NUMPROTOCOLLO'];
        ;


        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
            'FOIDPRATICA' => $cart_stimolo_rec['IDPRATICA'],
            'FOTIPOSTIMOLO' => praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA],
            'FOPRASPACATA' => $sportelloSuap,
            'FOPRADESC' => $descProcedimento,
            'FOPRADATA' => $dataArrivo,
            'FOPRAORA' => $oraArrivo,
            'FOPROTDATA' => $dataProtocollo,
            'FOPROTORA' => $oraProtocollo,
            'FOPROTNUM' => $numeroProtocollo,
            'FOESIBENTE' => $arrCopertina['presentatore'][0]['cognome'][0][itaXml::textNode] . " " .
            $arrCopertina['presentatore'][0]['nome'][0][itaXml::textNode],
            'FODICHIARANTE' => $arrCopertina['richiedente'][0]['cognome'][0][itaXml::textNode] . " " .
            $arrCopertina['richiedente'][0]['nome'][0][itaXml::textNode],
            'FODICHIARANTECF' => $arrCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode],
            'FODICHIARANTEQUALIFICA' => $arrCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode],
            'FOALTRORIFERIMENTODESC' => "Denominazione Impresa",
            'FOALTRORIFERIMENTO' => $arrCopertina['impresa'][0]['denominazione'][0][itaXml::textNode],
            'FOALTRORIFERIMENTOIND' => $arrCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode],
            'FOALTRORIFERIMENTOCAP' => $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode],
            'FOMETADATA' => json_encode($arrCopertina)
        );


        // Si salvano gli allegati in PRAFOFILES
        $praFoFiles_tab = array();
        $cart_stimoloFile_tab = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);
        if ($cart_stimoloFile_tab) {
            $dirCart = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

            foreach ($cart_stimoloFile_tab as $cart_stimoloFile_rec) {
                $file = $dirCart . "/" . $cart_stimoloFile_rec['NOMEFILE'];
                // Copiare i file dalla Directory CART alla Direcotry PRAFOLIST 
                $praFoFiles_rec = array(
                    'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
                    'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
                    'FILESHA2' => $cart_stimoloFile_rec['HASHFILE'],
                    'FILEID' => $cart_stimoloFile_rec['IDFILE'],
                    'FILENAME' => $cart_stimoloFile_rec['NOMEFILE'],
                    'FILEFIL' => itaLib::getRandBaseName() . '.' . pathinfo($file, PATHINFO_EXTENSION),
                    'TMP_SOURCEFILE' => $file,
                );
                $praFoFiles_tab[] = $praFoFiles_rec;
            }
        }

        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => $praFoFiles_tab,
        );

        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            return false;
        }

        // Aggiorno campo CART_STIMOLO.PRAFOLISTROWID 
        //$praLib = new praLib();

        $Codice = Array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
            'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
        );

        $praFoList_rec = $this->praLib->GetPrafolist($Codice, 'key', false);
        if ($praFoList_rec) {

            $cart_stimolo_rec['PRAFOLISTROWID'] = $praFoList_rec['ROW_ID'];

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento campo PRAFOLISTROWID in CART_SIMOLO fallito.");

                return false;
            }
        }


        return true;
    }

    private function praFoListStimolo($cart_stimolo_rec) {
        // Riporta CART_STIMOLO di uno Stimolo che non sia Presentazione Pratica in PROFILOST - PRAFOFILES
        $pathFile = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

        $lista = glob($pathFile . "/*.*");
        if ($lista === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nella directory $pathFile non sono stati ritrovati gli allegati" . $this->getErrMessage());
            return false;
        }

        //Trovo CART_STIMOLO della pratica collegato
        $sql = "SELECT * FROM CART_STIMOLO "
                . " WHERE CART_STIMOLO.IDPRATICA = '" . $cart_stimolo_rec['IDPRATICA'] . "' "
                . " AND CART_STIMOLO.TIPOSTIMOLO = 'presentazionePratica'";

        $cart_stimoloPrinc_rec = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, false);

        if (!$cart_stimoloPrinc_rec) {
            return;
        }

        $codice = $cart_stimoloPrinc_rec['PRAFOLISTROWID'];
        // Trovo PRAFOLIST collegato alla presentazione pratica trovata
        $praFoListPrinc_rec = $this->praLib->GetPrafolist($codice);
        if (!$praFoListPrinc_rec) {
            return;
        }


        $dataProtocollo = $cart_stimolo_rec['DATAPROTOCOLLO'];
        $oraProtocollo = '';
        $numeroProtocollo = $cart_stimolo_rec['NUMPROTOCOLLO'];
        ;

        $oraArrivo = $this->getOra($cart_stimolo_rec['DATACAR']);
        $dataArrivo = $this->getData($cart_stimolo_rec['DATACAR']);


        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
            'FOIDPRATICA' => $cart_stimolo_rec['IDPRATICA'],
            'FOTIPOSTIMOLO' => $cart_stimolo_rec['TIPOSTIMOLO'],
            'FOPRASPACATA' => $praFoListPrinc_rec['FOPRASPACATA'],
            'FOPRADESC' => $praFoListPrinc_rec['FOPRADESC'],
            'FOPRADATA' => $dataArrivo,
            'FOPRAORA' => $oraArrivo,
            'FOPROTDATA' => $dataProtocollo,
            'FOPROTORA' => $oraProtocollo,
            'FOPROTNUM' => $numeroProtocollo,
            'FOESIBENTE' => $praFoListPrinc_rec['FOESIBENTE'],
            'FODICHIARANTE' => $praFoListPrinc_rec['FODICHIARANTE'],
            'FODICHIARANTECF' => $praFoListPrinc_rec['FODICHIARANTECF'],
            'FODICHIARANTEQUALIFICA' => $praFoListPrinc_rec['FODICHIARANTEQUALIFICA'],
            'FOALTRORIFERIMENTODESC' => $praFoListPrinc_rec['FOALTRORIFERIMENTODESC'],
            'FOALTRORIFERIMENTO' => $praFoListPrinc_rec['FOALTRORIFERIMENTO'],
            'FOALTRORIFERIMENTOIND' => $praFoListPrinc_rec['FOALTRORIFERIMENTOIND'],
            'FOALTRORIFERIMENTOCAP' => $praFoListPrinc_rec['FOALTRORIFERIMENTOCAP'],
            'FOMETADATA' => $cart_stimolo_rec['METADATI'],
        );

        // Si salvano gli allegati in PRAFOFILES
        $praFoFiles_tab = array();
        $cart_stimoloFile_tab = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);
        if ($cart_stimoloFile_tab) {
            $dirCart = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

            foreach ($cart_stimoloFile_tab as $cart_stimoloFile_rec) {
                $file = $dirCart . "/" . $cart_stimoloFile_rec['NOMEFILE'];
                // Copiare i file dalla Directory CART alla Direcotry PRAFOLIST 
                $praFoFiles_rec = array(
                    'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
                    'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
                    'FILESHA2' => $cart_stimoloFile_rec['HASHFILE'],
                    'FILEID' => $cart_stimoloFile_rec['IDFILE'],
                    'FILENAME' => $cart_stimoloFile_rec['NOMEFILE'],
                    'FILEFIL' => itaLib::getRandBaseName() . '.' . pathinfo($file, PATHINFO_EXTENSION),
                    'TMP_SOURCEFILE' => $file,
                );
                $praFoFiles_tab[] = $praFoFiles_rec;
            }
        }

        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => $praFoFiles_tab,
        );

        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            return false;
        }

        // Aggiorno campo CART_STIMOLO.PRAFOLISTROWID 
        $praLib = new praLib();

        $Codice = Array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
            'FOPRAKEY' => $cart_stimolo_rec['IDMESSAGGIO'],
        );

        $praFoList_rec = $this->praLib->GetPrafolist($Codice, 'key', false);
        if ($praFoList_rec) {

            $cart_stimolo_rec['PRAFOLISTROWID'] = $praFoList_rec['ROW_ID'];

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento campo PRAFOLISTROWID in CART_SIMOLO fallito.");

                return false;
            }
        }


        return true;
    }

    private function praFoListRicevuta($cart_stimolo_rec) {
        //$praLib = new praLib();
        //Riporto la Ricevuta nel PRAFOLIST collegato a PresentazionePratica
        $pathFile = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

        $lista = glob($pathFile . "/*.*");
        if ($lista === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nella directory $pathFile non sono stati ritrovati gli allegati" . $this->getErrMessage());
            return false;
        }

        //Trovo CART_STIMOLO della pratica collegato
        $sql = "SELECT * FROM CART_STIMOLO "
                . " WHERE CART_STIMOLO.IDPRATICA = '" . $cart_stimolo_rec['IDPRATICA'] . "' "
                . " AND CART_STIMOLO.TIPOSTIMOLO = 'presentazionePratica'";

        $cart_stimoloPrinc_rec = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, false);

        if (!$cart_stimoloPrinc_rec) {
            return;
        }

        $codice = $cart_stimoloPrinc_rec['PRAFOLISTROWID'];

        if (!$codice) {
            return false;
        }

        // Trovo PRAFOLIST collegato alla presentazione pratica trovata
        $praFoListPrinc_rec = $this->praLib->GetPrafolist($codice);
//        $praFoListPrinc_rec = $praLib->GetPrafolist($codice);
        if (!$praFoListPrinc_rec) {
            return false;
        }

        // Si salvano gli allegati in PRAFOFILES
        $praFoFiles_tab = array();
        $cart_stimoloFile_tab = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);
        if ($cart_stimoloFile_tab) {
            $dirCart = $this->praLibCart->SetDirectoryCart($cart_stimolo_rec['IDMESSAGGIO'], 'STIMOLO');

            foreach ($cart_stimoloFile_tab as $cart_stimoloFile_rec) {
                $file = $dirCart . "/" . $cart_stimoloFile_rec['NOMEFILE'];
                // Copiare i file dalla Directory CART alla Direcotry PRAFOLIST 
                $praFoFiles_rec = array(
                    'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
                    'FOPRAKEY' => $cart_stimoloPrinc_rec['IDMESSAGGIO'],
                    'FILESHA2' => $cart_stimoloFile_rec['HASHFILE'],
                    'FILEID' => $cart_stimoloFile_rec['IDFILE'],
                    'FILENAME' => $cart_stimoloFile_rec['NOMEFILE'],
                    'FILEFIL' => itaLib::getRandBaseName() . '.' . pathinfo($file, PATHINFO_EXTENSION),
                    'TMP_SOURCEFILE' => $file,
                );
                $praFoFiles_tab[] = $praFoFiles_rec;
            }
        }

        // Si riportano gli allegati di $praFoFiles_tab in PRAFOFILES collegato a $praFoListPrinc_rec
        if (!$this->salvaAllegatiRicevuta($praFoFiles_tab, $praFoListPrinc_rec)) {
            return false;
        }

        // Aggiorno campo CART_STIMOLO.PRAFOLISTROWID 

        $Codice = Array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_CART_WS,
            'FOPRAKEY' => $cart_stimoloPrinc_rec['IDMESSAGGIO'],
        );

        $praFoList_rec = $this->praLib->GetPrafolist($Codice, 'key', false);
        if ($praFoList_rec) {

            $cart_stimolo_rec['PRAFOLISTROWID'] = $praFoList_rec['ROW_ID'];

            $nRows = ItaDB::DBUpdate($this->praLibCart->getITALWEB(), 'CART_STIMOLO', 'ROW_ID', $cart_stimolo_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento campo PRAFOLISTROWID in CART_SIMOLO fallito.");

                return false;
            }
        }
    }

    private function salvaAllegatiRicevuta($praFoFiles_tab, $praFoList_rec) {
        //$praLib = new praLib();

        $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);

        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            //Controllo che la Ricevuta non sia già presente in PRAFOFILES
            $sql = "SELECT * FROM PRAFOFILES WHERE FOTIPO = '" . $praFoFiles_rec['FOTIPO'] . "' "
                    . "AND PRAFOFILES.FOPRAKEY = '" . $praFoFiles_rec['FOPRAKEY'] . "' "
                    . "AND PRAFOFILES.FILENAME = '" . $praFoFiles_rec['FILENAME'] . "'";
            $filePresente_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
            if ($filePresente_rec) {
                continue;
            }


            $srcFile = $praFoFiles_rec['TMP_SOURCEFILE'];
            unset($praFoFiles_rec['TMP_SOURCEFILE']);
            // Elimina elemento dall'array
            // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
            $dir = $this->praLib->SetDirectoryPratiche($anno, $praFoFiles_rec['FOPRAKEY'], $praFoFiles_rec['FOTIPO']);
            $destFile = $dir . "/" . $praFoFiles_rec['FILEFIL'];

            if (!copy($srcFile, $destFile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia di $srcFile su $destFile non avvenuto.");
                return false;
            }

            try {
                $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAFOFILES', 'ROWID', $praFoFiles_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento su PRAFOFILES non avvenuto.");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in Inserimento su PRAFOFILES " . $e->getMessage());
                return false;
            }
            // Elimina il file dalla cartella temporanea
//            if ($praFoList_rec['FOTIPO'] != praFrontOfficeManager::TYPE_FO_CART_WS){
//                unlink($srcFile);
//            }
        }

        return true;
    }

    public function getDescrizioneGeneraleRichiestaFo($prafolist_rec, $nameForm = '') {
        $descrizione = "";
        if ($prafolist_rec['FOTIPOSTIMOLO'] == praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]) {

            $descrizione = $this->getDescrizionePratica($prafolist_rec);
        } else {
            $descrizione = $this->getDescrizioneStimoloGen($prafolist_rec);
        }


        return $descrizione;
    }

    public function getDescrizioneStimoloGen($prafolist_rec) {
        $this->praLibCart = new praLibCart();
        $proLibSerie = new proLibSerie();


        $sql = "SELECT * FROM PRAFOLIST WHERE PRAFOLIST.FOIDPRATICA = '" . $prafolist_rec['FOIDPRATICA'] . "'"
                . " AND PRAFOLIST.FOTIPO = '" . $prafolist_rec['FOTIPO'] . "'"
                . " AND PRAFOLIST.FOTIPOSTIMOLO = '" . praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA] . "'";

        $prafolistPrinc_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        $sql = "SELECT * FROM CART_STIMOLO WHERE CART_STIMOLO.PRAFOLISTROWID = " . $prafolist_rec['ROW_ID'];

        $cart_stimolo_rec = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, false);

        $descrizione = '<div style="padding:5px;">';
        $descrizione .= "Per la richiesta " . $prafolist_rec['FOIDPRATICA'] . " è arrivato un messaggio di <b>" . $prafolist_rec['FOTIPOSTIMOLO'] . "</b> <br/>";


        $proges_rec = $this->praLib->GetProges($prafolist_rec['FOIDPRATICA'], 'geskey', false);
        if ($proges_rec) {
            $Numero_procedimento = substr($proges_rec['GESNUM'], 4, 6) . " / " . substr($proges_rec['GESNUM'], 0, 4);
            $serie_rec = $proLibSerie->GetSerie($proges_rec['SERIECODICE'], 'codice');
            $Numero_serie = $serie_rec['SIGLA'] . " / " . $proges_rec['SERIEPROGRESSIVO'] . " / " . $proges_rec['SERIEANNO'];

            $descrizione .= "La richiesta " . $prafolist_rec['FOIDPRATICA'] . " è gestita con il fascicolo numero <b>" . $Numero_serie . "</b> (Identificativo: " . $Numero_procedimento . ") <br/> <br/>";
        } else {
            $descrizione .= "La richiesta " . $prafolist_rec['FOIDPRATICA'] . " <b> NON </b> è stata ancora gestita  <br/> <br/>";
        }

        $dettaglio = "DATI DEL MESSAGGIO <br/>";
        if ($cart_stimolo_rec) {
            if ($cart_stimolo_rec['MITTENTE_TIPO'] == "FACCT") {
                $dettaglio .= "Il messaggio è arrivato da parte del Richiedente <br/>";
            } else {
                $dettaglio .= "Il messaggio è arrivato da parte dei " . $cart_stimolo_rec['MITTENTE_TIPO'] . "<br/>";
            }


            // Devo vedere il tipo di stimolo e prendere informazioni da visualizzare
            switch ($cart_stimolo_rec['TIPOSTIMOLO']) {
                case "valutazioneIntegrazione":
                    if ($cart_stimolo_rec['NUMGIORNI'] > 0) {
                        $dettaglio .= "La risposta è attesa in " . $cart_stimolo_rec['NUMGIORNI'] . "giorni";
                    }
                    break;
                case "valutazioneConformazione":
                    if ($cart_stimolo_rec['NUMGIORNI'] > 0) {
                        $dettaglio .= "La risposta è attesa in " . $cart_stimolo_rec['NUMGIORNI'] . "giorni";
                    }
                    break;
                case "invioIntegrazioni":

                    break;
                case "invioConformazioni":

                    break;
                case "esitoNegativo":

                    break;
                case "comunicazione":
                    $dettaglio .= "Oggetto Messaggio: " . $cart_stimolo_rec['OGGETTO'];
                    if ($cart_stimolo_rec['MESSAGGIO']) {
                        $dettaglio .= "<br/> Corpo del Messaggio: " . $cart_stimolo_rec['MESSAGGIO'];
                    }
                    break;
            }
        }


        $descrizione .= $dettaglio;

        if ($prafolistPrinc_rec) {
            $arrCopertina = json_decode($prafolistPrinc_rec['FOMETADATA'], true);

            $descrizione .= "<br/><br/> Di seguito si riporta il riepilogo del procedimento a cui è riferito il messaggio arrivato: <br/> <br/> " . $prafolist_rec['FOPRADESC'] . "<br/><br/>"
                    . "DATI RICHIEDENTE <br/>"
                    . "Qualifica: " . $arrCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode] . "<br/>"
                    . "Nominativo: " . $arrCopertina['richiedente'][0]['cognome'][0][itaXml::textNode]
                    . " " . $arrCopertina['richiedente'][0]['nome'][0][itaXml::textNode] . "<br/>"
                    . "Codice Fiscale: " . $arrCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode] . "<br/>"
                    . "Cittadinanza: " . $arrCopertina['richiedente'][0]['cittadinanza'][0][itaXml::textNode] . "<br/>"
                    . "Nato a: " . $arrCopertina['richiedente'][0]['luogo-nascita'][0][itaXml::textNode]
                    . " il " . $arrCopertina['richiedente'][0]['data-nascita'][0][itaXml::textNode] . "<br/>"
            ;

            //Visualizzo la REsidenza se in Italia
            if ($arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]) {
                $descrizione = $descrizione . "Residente in: " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]
                        . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['comune'][0][itaXml::textNode]
                        . " (" . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
                ;
            }

            $descrizione = $descrizione . "<br/>"
                    . "DATI IMPRESA <br/>"
                    . "Ragione Sociale: " . $arrCopertina['impresa'][0]['denominazione'][0][itaXml::textNode] . "<br/>"
                    . "Forma Giuridica: " . $arrCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] . "<br/>"
            ;

            //Visualizzo la Sede Legale
            if ($arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]) {
                $descrizione = $descrizione . "Sede in: " . $arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]
                        . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['impresa'][0]['sede-legale'][0]['comune'][0][itaXml::textNode]
                        . " (" . $arrCopertina['impresa'][0]['sede-legale'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
                ;
            }

            $descrizione = $descrizione . "<br/>"
                    . "DATI ATTIVITA' <br/>"
                    . "Indirizzo: " . $arrCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode] . "<br/>"
                    . "Comune: " . $arrCopertina['impiantoProduttivo'][0]['comune'][0][itaXml::textNode]
                    . " (" . $arrCopertina['impiantoProduttivo'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
                    . " Cap: " . $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode] . "<br/>"
                    . "<br/> "
                    . "DATI CATASTALI <br/>"
                    . "Categoria: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp;  Foglio: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['foglio'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp;  Numero: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['numero'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp;  Subalterno: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['subalterno'][0][itaXml::textNode]
                    . "<br/> <br/> "
                    . "RECAPITI <br/>"
                    . "Pec: " . $arrCopertina['recapiti'][0]['pec'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp;  Telefono: " . $arrCopertina['recapiti'][0]['telefono'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp;  Cellulare: " . $arrCopertina['recapiti'][0]['cellulare'][0][itaXml::textNode];
        }

        $descrizione .= '</div>';
        return $descrizione;
    }

    private function getIdEndo($endoVar) {
        $idEndo = "";

        $pos = strpos($endoVar, "/");
        if ($pos) {
            $idEndo = substr($endoVar, 0, $pos);
        }

        return $idEndo;
    }

    public function getDescrizionePratica($prafolist_rec) {
        $praLibCart = new praLibCart();

        $decodeEndo = $this->decodeEndoProcedimenti($prafolist_rec);
        $htmEndoProc = '<div style="width:800px;">';
        //Out::msginfo('ret', print_r($decodeEndo, true));
        foreach ($decodeEndo as $key => $EndoProcedimento) {

            $idEndo = $this->getIdEndo($key);

            $descProc = "";
            $cart_attivita_rec = $praLibCart->getCart_attivita($idEndo);
            if ($cart_attivita_rec) {
                $descProc = $cart_attivita_rec['DESCATTIVITA'];
            }

            $keyProcedimentoStar = str_replace('/', '-', $key);
            if ($EndoProcedimento) {
                $keyProcedimentoProt = $EndoProcedimento['FODESTPRO'];
                if ($descProc) {
                    $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:green; color:white;">Procedimento ' . $key . ' (' . $descProc . ' ) associato a ' . $keyProcedimentoProt;
                } else {
                    $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:green; color:white;">Procedimento ' . $key . ' associato a ' . $keyProcedimentoProt;
                }
                $htmEndoProc .= ' <a href="#" id="' . $keyProcedimentoStar . '" class="ita-hyperlink {event:\'DettaglioProcedimentoCart\'}"><span title="Dettaglio Procedimento" class="ita-icon ita-icon-cerca-24x24" style="display:inline-block; vertical-align:top;"></span></a></div>';
            } else {
                if ($descProc) {
                    $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:red; color:white;">Procedimento ' . $key . ' (' . $descProc . ' ) non associato. ';
                } else {
                    $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:red; color:white;">Procedimento ' . $key . ' non associato. ';
                }
                $htmEndoProc .= '<a href="#" id="' . $keyProcedimentoStar . '" class="ita-hyperlink {event:\'AssociaProcedimentiCart\'}"><span title="Associa Procedimento" class="ita-icon ita-icon-edit-24x24" style="display:inline-block; vertical-align:top;"></span></a></div>';
            }
        }
        $htmEndoProc .= "</div>";

        $arrCopertina = json_decode($prafolist_rec['FOMETADATA'], true);
        $descrizione = '<div style="padding:5px;">';
        $descrizione .= "La richiesta: " . $arrCopertina['idDomanda'][0][itaXml::textNode]
                . " è stata ricevuta. <br/> "
                . "Di seguito si riporta il riepilogo del procedimento attivato: <br/> <br/> $htmEndoProc <br/>"
                . "DATI RICHIEDENTE <br/>"
                . "Qualifica: " . $arrCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode] . "<br/>"
                . "Nominativo: " . $arrCopertina['richiedente'][0]['cognome'][0][itaXml::textNode]
                . " " . $arrCopertina['richiedente'][0]['nome'][0][itaXml::textNode] . "<br/>"
                . "Codice Fiscale: " . $arrCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode] . "<br/>"
                . "Cittadinanza: " . $arrCopertina['richiedente'][0]['cittadinanza'][0][itaXml::textNode] . "<br/>"
                . "Nato a: " . $arrCopertina['richiedente'][0]['luogo-nascita'][0][itaXml::textNode]
                . " il " . $arrCopertina['richiedente'][0]['data-nascita'][0][itaXml::textNode] . "<br/>"
        ;

//Visualizzo la REsidenza se in Italia
        if ($arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]) {
            $descrizione = $descrizione . "Residente in: " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['comune'][0][itaXml::textNode]
                    . " (" . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
            ;
        }

        $descrizione = $descrizione . "<br/>"
                . "DATI IMPRESA <br/>"
                . "Ragione Sociale: " . $arrCopertina['impresa'][0]['denominazione'][0][itaXml::textNode] . "<br/>"
                . "Forma Giuridica: " . $arrCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] . "<br/>"
        ;

//Visualizzo la Sede Legale
        if ($arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]) {
            $descrizione = $descrizione . "Sede in: " . $arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['impresa'][0]['sede-legale'][0]['comune'][0][itaXml::textNode]
                    . " (" . $arrCopertina['impresa'][0]['sede-legale'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
            ;
        }

        $descrizione = $descrizione . "<br/>"
                . "DATI ATTIVITA' <br/>"
                . "Indirizzo: " . $arrCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode] . "<br/>"
                . "Comune: " . $arrCopertina['impiantoProduttivo'][0]['comune'][0][itaXml::textNode]
                . " (" . $arrCopertina['impiantoProduttivo'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
                . " Cap: " . $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode] . "<br/>"
                . "<br/> "
                . "DATI CATASTALI <br/>"
                . "Categoria: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Foglio: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['foglio'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Numero: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['numero'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Subalterno: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['subalterno'][0][itaXml::textNode]
                . "<br/> <br/> "
                . "RECAPITI <br/>"
                . "Pec: " . $arrCopertina['recapiti'][0]['pec'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Telefono: " . $arrCopertina['recapiti'][0]['telefono'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Cellulare: " . $arrCopertina['recapiti'][0]['cellulare'][0][itaXml::textNode];
        $descrizione .= '</div>';
        return $descrizione;
    }

    public function decodeEndoProcedimenti($prafolist_rec) {
        $arrayCopertina = json_decode($prafolist_rec['FOMETADATA'], true);
        $retDecode = array();
        $variazione = $arrayCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];
        foreach ($arrayCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0]['ns2:idEndoprocedimento'] as $arrayEndo) {
            $idEndo = $arrayEndo[itaXml::textNode];

            $idEndo = str_replace(" ", "_", $idEndo);

            $indice = $idEndo;
            $indice = $indice . "/" . $variazione;

            $retDecode[$indice] = $this->decodificaEndo($idEndo, $variazione);
//            $retDecode[$idEndo . "/" . $variazione] = $this->decodificaEndo($idEndo, $variazione);
        }
        return $retDecode;
    }

    private function decodificaEndo($idEndo, $variazione) {
        $endo1 = str_replace(" ", "_", $idEndo);
        $decodifica = $endo1 . "/" . $variazione;

        $sql = "SELECT * FROM PRAFODECODE WHERE FOSRCKEY = '$decodifica' "
                . "AND PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_CART_WS . "'";
        $praFoDecode_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        if (!$praFoDecode_rec) {
            return null;
        } else {
            return $praFoDecode_rec;
        }
    }

    public function checkFoAcqPreconditions($param) {
        $prafolist_rec = $param['prafolist_rec'];

        if (!$prafolist_rec) {
            self::$lasErrCode = -1;
            self::$lasErrMessage = 'Lettura della pratica non avvenuto.';
            return false;
        }

        /**
         * Nel caso non sia nuova pratica, verifico che la pratica sia stata riportata nei Fascicoli Eleetronici
         */
        if ($prafolist_rec['FOTIPOSTIMOLO'] != praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]) {
            $errore = true;
            $sql = "SELECT * FROM PRAFOLIST WHERE FOIDPRATICA = '" . $prafolist_rec['FOIDPRATICA']
                    . "' AND FOTIPO = '" . $prafolist_rec['FOTIPO']
                    . "' AND FOTIPOSTIMOLO = '" . praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]
                    . "'";
            $praFoListPratica_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
            if ($praFoListPratica_rec) {
                if ($praFoListPratica_rec['FOGESNUM'] <> '') {
                    $errore = false;
                }
            }

            if ($errore) {
                self::$lasErrCode = -1;
                self::$lasErrMessage = 'Riportare la pratica ' . $prafolist_rec['FOIDPRATICA'] . ' prima di gestire gli altri messaggi.';

                Out::msgInfo("Attenzione", praFrontOfficeManager::$lasErrMessage);

                return false;
            }
        }

        /**
         *
         * Verifico se i codici procedimento cart sono correttamente Mappati su cwol
         */
        $retDecode = $this->decodeEndoProcedimenti($prafolist_rec);

        //TODO
        //Ciclo su $retDecode nel caso non ci sia record id PRAFODECODE
        if (in_array('', $retDecode)) {

            $indice = array_search('', $retDecode);

            $model = 'praFoDecodeGest';
            itaLib::openDialog($model);
            /* @var $modelObj praAssegnaPraticaSimple */
            $modelObj = itaModel::getInstance($model);
            $modelObj->setReturnModel($param['returnModel']);
            $modelObj->setReturnEvent('onClick');
            $modelObj->setReturnId($param['returnId']);
            $modelObj->setEvent('openform');
            $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_CART_WS);
            //Da valorizzare, prendendola da $retDecode, quando si gestisce
            $modelObj->setChiaveFo($indice);
            $modelObj->setDialog(true);
            $modelObj->parseEvent();
        } else {
            return true;
        }
    }

    public function getAllegatiRichiestaFo($prafolist_rec, $allegatiInfocamere) {

        $sql = "SELECT * FROM PRAFOFILES WHERE FOTIPO = '" . addslashes($prafolist_rec['FOTIPO']) . "'"
                . " AND FOPRAKEY = '" . addslashes($prafolist_rec['FOPRAKEY']) . "'";

        $praFoFiles_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        if (!$praFoFiles_tab) {
            return null;
        } else {
            return $praFoFiles_tab;
        }
    }

    public function getDataModelAcq($praFoList_rec, $dati = array()) {
        $datamodelArray = array();

        if ($praFoList_rec['FOTIPOSTIMOLO'] != praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]) {
            // Stimolo collegato ad una pratica già riportata
            $datamodelArray = $this->getDataModelAcqStimolo($praFoList_rec);
        } else {
            // Nuova pratica
            $datamodelArray = $this->getDataModelAcqNuovaPratica($praFoList_rec);
        }

        return $datamodelArray;
    }

    public function getDataModelAcqNuovaPratica($praFoList_rec) {
        $datamodelArray = array();
        $this->praLib = new praLib();


        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);

        $variazione = $arrayCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];

        $procPrincipale = array();
        $procSecondari = array();
        foreach ($arrayCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0] ['ns2:idEndoprocedimento'] as $endoProcedimenti) {

            $endo = $endoProcedimenti[itaXml::textNode];
            $endo1 = str_replace(" ", "_", $endo);

            $procAppoggio = array(
                'CODICE' => $endo,
                'ENDO1' => $endo1,
                'XMLINFO' => "XMLINFO_" . $endo1 . ".xml"
            );


            if (empty($procPrincipale)) {
                $procPrincipale = $procAppoggio;
            } else {
                array_push($procSecondari, $procAppoggio);
            }
        }

        if (!isset($procPrincipale)) {
            //@TODO - Caricamento non può essere fatto
            return false;
        }

        //Out::msgInfo("Procedimento Principale", print_r($procPrincipale, true));
        //Out::msgInfo("Procedimenti Secondari", print_r($procSecondari, true));

        $sql = "SELECT * FROM PRAFODECODE "
                . " WHERE PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_CART_WS . "' "
                . " AND PRAFODECODE.FOSRCKEY = '" . $procPrincipale['ENDO1'] . "/" . $variazione . "'";

        $praFoDecode_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$praFoDecode_rec) {
            return false;
        }

        $sqlIteevt = "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $praFoDecode_rec['FODESTPRO'] .
                "' AND IEVCOD = '" . $praFoDecode_rec['FODESTEVCOD'] .
                "' AND IEVTSP = " . $praFoDecode_rec['FODESTTSP'];
        $iteevt_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sqlIteevt, false);

        $arrayProRic = $this->getProric_rec($praFoList_rec, $praFoDecode_rec);


        /*
         * Riempire $datamodelArray con PROGES_REC e ANADES_REC
         * Vedi documento XMind
         */
        $arrayProges_rec = $this->getProges_rec($praFoList_rec, $praFoDecode_rec);


        // Carico vettore $$arrayAnades_rec con i dati di ANADES_REC
        $arrayAnades_rec = $this->getAnades_rec($praFoList_rec);


        /**
         * Sistema XMLINFO per pratiche Secondarie (Arcorpate) e per la principale
         */
        // Crea cartella di appoggio per la sessione corrente
        $pathFile = itaLib::createAppsTempPath('tmp' . $praFoList_rec['FOPRAKEY']);


        $indice = 0;

        foreach ($procSecondari as $app) {

            $indice ++;
            if ($indice > 99) {
                $progressivo = $indice;
            } else if ($indice > 9) {
                $progressivo = "0" . $indice;
            } else {
                $progressivo = "00" . $indice;
            }


            $sql = "SELECT * FROM PRAFODECODE "
                    . " WHERE PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_CART_WS . "' "
                    . " AND PRAFODECODE.FOSRCKEY = '" . $app['ENDO1'] . "/" . $variazione . "'";

            $praFoDecodeSec_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

            if ($praFoDecodeSec_rec) {

                $arrayxmlInfo = $this->getXmlInfo($praFoList_rec, $praFoDecodeSec_rec, $procSecondari, false, $progressivo, $app['CODICE']);

                $file = $pathFile . "/" . $app['XMLINFO'];

                $ItaXmlObj = new itaXML;

                $ItaXmlObj->noCDATA();

                $ItaXmlObj->toXML($arrayxmlInfo);
                $xmlInfoString = $ItaXmlObj->getXml();

                //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
                // Salvare il contenuto di questa stringa in un file
                file_put_contents($file, $xmlInfoString);
            }
        }

        /**
         * Genero XMLINFO per la pratica principale
         */
        $arrayxmlInfo = $this->getXmlInfo($praFoList_rec, $praFoDecode_rec, $procSecondari);

        $file = $pathFile . "/XMLINFO.xml";

        //Out::msgInfo("Directory XMLINF", $pathFile);

        $ItaXmlObj = new itaXML;

        $ItaXmlObj->noCDATA();

        $ItaXmlObj->toXML($arrayxmlInfo);
        $xmlInfoString = $ItaXmlObj->getXml();

        //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
        // Salvare il contenuto di questa stringa in un file
        file_put_contents($file, $xmlInfoString);

//Out::msgInfo("File XMLINFO ", print_r($file, true));
//return false;
//
//Out::msgInfo("Array XMLINFO ", print_r($arrayxmlInfo, true));
//
//return false;

        $arrayAllegati = $this->getArrayAllegati($praFoList_rec, $procSecondari);


        $datamodel = array(
            'tipoInserimento' => "PRAFOLIST",
            'tipoReg' => "consulta",
            'PRAFOLIST_REC' => $praFoList_rec,
            'PRORIC_REC' => $arrayProRic,
            'PROGES_REC' => $arrayProges_rec,
            'ANADES_REC' => $arrayAnades_rec,
            'ALLEGATI' => $arrayAllegati,
            'ALLEGATIACCORPATE' => $this->getAllegatiAccorpati($praFoList_rec, $procSecondari),
            'XMLINFO' => $file,
            'esterna' => true,
            'starweb' => false,
            'escludiPassiFO' => true,
            'progressivoDaRichiesta' => false
        );




        //Out::msgInfo("Data Model ", print_r($datamodel, true));

        array_push($datamodelArray, $datamodel);
        unset($datamodel);  // Svuota il vettore


        return $datamodelArray;
    }

    private function getProges_rec($praFoList_rec, $praFoDecode_rec) {

        $gesDre = date("Ymd");
        $gesDri = $praFoList_rec['FOPRADATA'];
        $gesOra = $praFoList_rec['FOPRAORA'];
        $gesPra = "";
// $gesPra = $praFoList_rec['FOPRAKEY'] // Codice pratica per Regione Toscana è 'NGRLCN51D03Z600C-22062018-1208'
        $gesPro = $praFoDecode_rec['FODESTPRO'];    // ANAPRA.PRANUM  "000001";
        $gesTsp = $praFoDecode_rec['FODESTTSP'];   // PRAFODECODE.FODESTTSP  (1)
        $gesSpa = "0";
        $gesEve = $praFoDecode_rec['FODESTEVCOD']; // PRAFODECODE.FODESTEVCOD
//$gesEve = "6";
        $gesSeg = "ALTRO";


        $gesRes = "000001";   // ANAPRA.PRARES
        $anapra_rec = $this->praLib->GetAnapra($praFoDecode_rec['FODESTPRO']);

        if ($anapra_rec) {
            $gesRes = $anapra_rec['PRARES'];   // ANAPRA.PRARES
        }



        $arrayProges = array(
            'GESDRE' => $gesDre,
            'GESDRI' => $gesDri,
            'GESORA' => $gesOra,
            'GESPRA' => $gesPra,
            'GESPRO' => $gesPro,
            'GESRES' => $gesRes,
            'GESTSP' => $gesTsp,
            'GESSPA' => $gesSpa,
            'GESEVE' => $gesEve,
            'GESSEG' => $gesSeg,
            'GESDCH' => ''
        );

        return $arrayProges;
    }

    private function getAnades_rec($praFoList_rec) {

        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);


        $desRuo = "0001";  // Codice Esibente
        $desNom = $praFoList_rec['FOESIBENTE'];
        $desInd = "";
        $desCap = "";
        $desCit = "";
        $desPro = "";
        $desEma = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];
        $desFis = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];


        $arrayAnades = array(
            'DESRUO' => $desRuo,
            'DESNOM' => $desNom,
            'DESIND' => $desInd,
            'DESCAP' => $desCap,
            'DESCIT' => $desCit,
            'DESPRO' => $desPro,
            'DESEMA' => $desEma,
            'DESFIS' => $desFis
        );

        return $arrayAnades;
    }

    private function getProric_rec($praFoList_rec, $praFoDecode_rec, $principale = 'true', $progressivo = '001') {
        //Out::msgInfo("Principale", $principale . $progressivo);
        if ($principale) {
            $ricnum = $praFoList_rec['FOIDPRATICA'];   // Numero della Richiesta
            //$ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta
        } else {
            $ricnum = $praFoList_rec['FOIDPRATICA'] . "_" . $progressivo;   // Numero della Richiesta
        }
        $ricpro = $praFoDecode_rec['FODESTPRO'];  // "000001";  // Numero procedimento (ANAPRA.PRANUM)
        $riceve = $praFoDecode_rec['FODESTEVCOD'];   // Codice Evento  (6)
        $rictsp = $praFoDecode_rec['FODESTTSP'];  //Codice Sportello ("1")
        $ricstt = $praFoDecode_rec['FODESTSTT'];   // Codice Settore ("1")
        $ricatt = $praFoDecode_rec['FODESTATT'];    // Codice Attivita  ("1")
        $ricseg = "0";   // Tipo Segnalazione  ("0")

        $ricres = "000001";  // Codice Responsabile
        $anapra_rec = $this->praLib->GetAnapra($praFoDecode_rec['FODESTPRO']);

        if ($anapra_rec) {
            $ricres = $anapra_rec['PRARES'];   // ANAPRA.PRARES
        }

        $ricrun = '';
        if (!$principale) {
            $ricrun = $praFoList_rec['FOIDPRATICA'];
        }


        $arrayProRic = array(
            'RICNUM' => $ricnum,
            'RICKEY' => $ricnum,
            'RICPRO' => $ricpro,
            'RICRES' => $ricres,
            'RICEVE' => $riceve,
            'RICTSP' => $rictsp,
            'RICSTT' => $ricstt,
            'RICATT' => $ricatt,
            'RICSEG' => $ricseg,
            'RICRUN' => $ricrun,
//            'RICCOG' => "Rossi",
//            'RICNOM' => "Valentino",
//            'RICEMA' => "ppp@mail.it",
//            'RICFIS' => "frfgtg67y89y789o",
            'RICDRE' => date('Ymd'),
            'RICDAT' => $praFoList_rec['FOPRADATA'],
            'RICTIM' => $praFoList_rec['FOPRAORA'],
            'RICNPR' => substr($praFoList_rec['FOPROTDATA'], 0, 4) . $praFoList_rec['FOPROTNUM'],
            'RICDPR' => $praFoList_rec['FOPROTDATA'],
        );


        return $arrayProRic;
    }

    private function getXmlInfo($praFoList_rec, $praFoDecode_rec, $procSecondari, $principale = 'true', $progressivo = '001', $codEndoSecondario = '') {
        // TODO: Passare anche eventuale procedimentoSecondario corrente, se non validato si mette vuoto

        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);

        $arrayProRic = $this->getProric_rec($praFoList_rec, $praFoDecode_rec, $principale, $progressivo);

        if ($principale) {
            $ricnum = $praFoList_rec['FOIDPRATICA'];   // Numero della Richiesta
            //$ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta
        } else {
            $ricnum = $praFoList_rec['FOIDPRATICA'] . "_" . $progressivo;   // Numero della Richiesta
        }

        $arrayRicDoc = array();
        $praLib = new praLib();
        if ($principale) {

            // Riporto RICDOC - Leggo record di PRAFOFILES
            $sql = "SELECT * FROM PRAFOFILES "
                    . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                    . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

            $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);


            foreach ($praFoFiles_tab as $praFoFiles_rec) {

                $allegatoAccorpato = $this->isAllegatoPrincipale($praFoFiles_rec, $procSecondari);
                if ($allegatoAccorpato) {
                    $iteKey = $ricnum;
                    $fileName = $praFoFiles_rec['FILENAME'];
                    $docSha2 = $praFoFiles_rec['FILESHA2']; // hash_file('sha256', $filename);   
                    $docPrt = "";

                    $allegato = array(
                        'ITEKEY' => $iteKey,
                        'DOCNAME' => $fileName,
                        'DOCSHA2' => $docSha2,
                        'DOCPRT' => $docPrt
                    );

                    //array_push($arrayRicDoc, $allegato);

                    $recordRicDoc[] = array(
                        'RECORD' => array_map(array($this, 'encodeForXml'), $allegato)
                    );

                    unset($allegato);  // Svuota il vettore
                }
            }
            $arrayRicDoc[] = array($recordRicDoc);
        } else {
            if ($codEndoSecondario) {
                $iteKeyAccorpata = $praLib->keyGenerator($praFoDecode_rec['FODESTPRO']);

                $ricIte = array(
                    'RICNUM' => $ricnum,
                    'ITECOD' => $praFoDecode_rec['FODESTPRO'],
                    'ITEDES' => "Passo Richiesta Accorpata",
                    'ITEKEY' => $iteKeyAccorpata,
                    'ITEPUB' => 1
                );

                $recordRicIte[] = array(
                    'RECORD' => array_map(array($this, 'encodeForXml'), $ricIte)
                );

                $arrayRicIte[] = array($recordRicIte);


                // Si crea $arrayRicDoc con gli allegati associati al procedimento accorpato
                // Riporto RICDOC - Leggo record di PRAFOFILES
                $sql = "SELECT * FROM PRAFOFILES "
                        . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                        . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

                $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);


                foreach ($praFoFiles_tab as $praFoFiles_rec) {

                    $allegatoAccorpato = $this->isAllegatoAccorpato($praFoFiles_rec, $codEndoSecondario);

                    if ($allegatoAccorpato) {
                        $iteKey = $ricnum;
                        $fileName = $praFoFiles_rec['FILENAME'];
                        $docSha2 = $praFoFiles_rec['FILESHA2']; // hash_file('sha256', $filename);   
                        $docPrt = "";

                        $allegato = array(
                            'ITEKEY' => $iteKeyAccorpata, // $iteKey,
                            'DOCNAME' => $fileName,
                            'DOCSHA2' => $docSha2,
                            'DOCPRT' => $docPrt
                        );

                        //array_push($arrayRicDoc, $allegato);

                        $recordRicDoc[] = array(
                            'RECORD' => array_map(array($this, 'encodeForXml'), $allegato)
                        );

                        unset($allegato);  // Svuota il vettore
                    }
                }
                $arrayRicDoc[] = array($recordRicDoc);
            }
        }


// Riporto RICDAG
        $arrayRicDag = array();
        $recordRicDag = array();

// Simulo caricamento di RICDAG
//ESIBENTE
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_NOME", $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_COGNOME", $arrayCopertina['presentatore'][0]['cognome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_CODICEFISCALE_CFI", $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode]);

//DICHIARANTE
//$recordRicDag[] = $this->getRicDag("DICHIARANTE_COGNOME_NOME", $praFoList_rec['FODICHIARANTE']);
//$recordRicDag[] = $this->getRicDag("DICHIARANTE_CODICEFISCALE_CFI", $praFoList_rec['FODICHIARANTECF']);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NOME", $arrayCopertina['richiedente'][0]['nome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_COGNOME", $arrayCopertina['richiedente'][0]['cognome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CODICEFISCALE_CFI", $arrayCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NASCITACOMUNE", $arrayCopertina['richiedente'][0]['luogo-nascita'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NASCITADATA_DATA", $arrayCopertina['richiedente'][0]['data-nascita'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CITTADINANZA", $arrayCopertina['richiedente'][0]['cittadinanza'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_QUALIFICA", $arrayCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZACOMUNE", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZAPROVINCIA_PV", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['provincia'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZAVIA", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZACAP_CAP", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['cap'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_PEC", $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CELLULARE", $arrayCopertina['recapiti'][0]['cellulare'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_TELEFONO", $arrayCopertina['recapiti'][0]['telefono'][0][itaXml::textNode]);

// IMPRESA
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_RAGIONESOCIALE", $arrayCopertina['impresa'][0]['denominazione'][0][itaXml::textNode]);

        if ($arrayCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] === "Legale Rappresentante") {
            $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NATURALEGA_RADIO", "R");
        } else if ($arrayCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] === "Titolare") {
            $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NATURALEGA_RADIO", "T");
        }

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_CODICEFISCALE_CFI", $arrayCopertina['impresa'][0]['codice-fiscale'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDECOMUNE", $arrayCopertina['impresa'][0]['sede-legale'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGPROVINCIA_PV", $arrayCopertina['impresa'][0]['sede-legale'][0]['provincia'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGVIA", $arrayCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGCAP", $arrayCopertina['impresa'][0]['sede-legale'][0]['cap'][0][itaXml::textNode]);

// INSEDIAMENTOLOCALE
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INSEDIAMENTOLOCALE_VIA", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IC_INDIR_INS_PROD", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_VIA", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_LOCALITA", $arrayCopertina['impiantoProduttivo'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_CAP", $arrayCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_PROVINCIA", $arrayCopertina['impiantoProduttivo'][0]['provincia'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_CATEGORIA", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_SEZIONE", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_FOGLIO", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['foglio'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_PARTICELLA", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['numero'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_SUBALTERNO", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['subalterno'][0][itaXml::textNode]);

        $arrayRicDag[] = array($recordRicDag);


        // Riporto le RICHIESTE_ACCORPATE, solo se è Procedimento Principale
        $arrayRichieste_Accorpate = $richieste_Accorpate = array();
        if ($principale) {

            $xmlInfo = array();

            foreach ($procSecondari as $key => $app) {

                $xmlInfo = array(
                    "XMLINFO" => $app['XMLINFO']
                );

                $richieste_Accorpate[] = array_map(array($this, 'encodeForXml'), $xmlInfo);
            }
            $arrayRichieste_Accorpate[] = array($richieste_Accorpate);
        }



        $arrayProRic = array_map(array($this, 'encodeForXml'), $arrayProRic);

        //       $arrayRichieste_Accorpate = array_map(array($this, 'encodeForXml'), $arrayRichieste_Accorpate);
        //Out::msgInfo("Richieste Acc", print_r($arrayRichieste_Accorpate, true));

        $root = array(
            'PRORIC' => array($arrayProRic),
            'RICITE' => $arrayRicIte,
            'RICDOC' => $arrayRicDoc,
            'RICDAG' => $arrayRicDag,
            'RICHIESTE_ACCORPATE' => $arrayRichieste_Accorpate // array()
        );



//array_walk_recursive($root, array($this,'encodeForXml'));

        $arrayXmlInfo = array(
            'ROOT' => $root
        );

        return $arrayXmlInfo;
    }

    private function isAllegatoAccorpato($praFoFiles_rec, $codEndoSecondario) {
        $this->praLibCart = new praLibCart();

        $trovato = false;
        // Si ricerca il record del documento in CART_STIMOLOFILE e si vede se il campo USOMODELLO è uguale a $codEndoSecondario
        $codice = array();
        $codice['IDCARTSTIMOLO'] = $praFoFiles_rec['FOPRAKEY'];
        $codice['FILENAME'] = $praFoFiles_rec['FILENAME'];

        $cart_stimoloFile_res = $this->praLibCart->getCart_stimoloFile($codice, 'file', false);
        if ($cart_stimoloFile_res) {
            if ($cart_stimoloFile_res['USOMODELLO'] == $codEndoSecondario) {
                $trovato = true;
            }
        }

        return $trovato;
    }

    private function isAllegatoPrincipale($praFoFiles_rec, $procSecondari) {
        $this->praLibCart = new praLibCart();
        $trovato = true;

        // Si ricerca il record del documento in CART_STIMOLOFILE e si vede se il campo USOMODELLO è uguale ad uno dei procedimenti secondari  ($procSecondari)
        $codice = array();
        $codice['IDCARTSTIMOLO'] = $praFoFiles_rec['FOPRAKEY'];
        $codice['FILENAME'] = $praFoFiles_rec['FILENAME'];

        $cart_stimoloFile_res = $this->praLibCart->getCart_stimoloFile($codice, 'file', false);
        if ($cart_stimoloFile_res) {

            foreach ($procSecondari as $procSec) {

                if ($procSec['CODICE'] == $cart_stimoloFile_res['USOMODELLO']) {
                    $trovato = false;
                    break;
                }
            }
        }

        return $trovato;
    }

    private function getAllegatiAccorpati($praFoList_rec, $procSecondari) {
        $this->praLibCart = new praLibCart();
        $Allegati = array();
        $praLib = new praLib();
        $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);
        $dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);


        // Riporto RICDOC - Leggo record di PRAFOFILES
        $sql = "SELECT * FROM PRAFOFILES "
                . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

        $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
        $key = 0;
        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            // Si ricerca il record del documento in CART_STIMOLOFILE e si vede se il campo USOMODELLO è uguale ad uno dei procedimenti secondari  ($procSecondari)
            $codice = array();
            $codice['IDCARTSTIMOLO'] = $praFoFiles_rec['FOPRAKEY'];
            $codice['FILENAME'] = $praFoFiles_rec['FILENAME'];

            $cart_stimoloFile_res = $this->praLibCart->getCart_stimoloFile($codice, 'file', false);
            if ($cart_stimoloFile_res) {
                foreach ($procSecondari as $procSec) {
                    if ($procSec['CODICE'] == $cart_stimoloFile_res['USOMODELLO']) {
                        $Allegati[$procSec['ENDO1']][$key]['ID'] = $praFoFiles_rec['ROW_ID'];
                        $Allegati[$procSec['ENDO1']][$key]['DATAFILE'] = $dir . "/" . $praFoFiles_rec['FILEFIL'];
                        $Allegati[$procSec['ENDO1']][$key]['FILENAME'] = $praFoFiles_rec['FILENAME'];
                        $Allegati[$procSec['ENDO1']][$key]['FILEINFO'] = $praFoFiles_rec['FILENAME'];
                        $Allegati[$procSec['ENDO1']][$key]['PRAFOFILES_ROW_ID'] = $praFoFiles_rec['ROW_ID'];
                        $key++;
                        break;
                    }
                }
            }
        }

        return $Allegati;
    }

    private function getArrayAllegati($praFoList_rec, $procSecondari = '') {
        $arrayAllegati = array();

        $praLib = new praLib();

        $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);

        // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
        $dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);


        // Leggo record di PRAFOFILES
        $sql = "SELECT * FROM PRAFOFILES"
                . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

        $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);

        foreach ($praFoFiles_tab as $praFoFiles_rec) {
            $allegatoPrincipale = true;
            if ($procSecondari) {
                $allegatoPrincipale = $this->isAllegatoPrincipale($praFoFiles_rec, $procSecondari);
            }

            if ($allegatoPrincipale) {

                $id = $praFoFiles_rec['ROW_ID'];
                $dataFile = $dir . "/" . $praFoFiles_rec['FILEFIL'];
                $fileName = $praFoFiles_rec['FILENAME'];
                $fileInfo = "";
                $allegato = array(
                    'ID' => $id,
                    'DATAFILE' => $dataFile,
                    'FILENAME' => $fileName,
                    'FILEINFO' => $fileInfo,
                    'PRAFOFILES_ROW_ID' => $praFoFiles_rec['ROW_ID']
                );

                array_push($arrayAllegati, $allegato);
                unset($allegato);  // Svuota il vettore
            }
        }

        return $arrayAllegati;
    }

    private function encodeForXml($value) {
        return array(itaXML::textNode => htmlspecialchars(utf8_encode($value)));
    }

    private function getRicDag($codProc, $nomeCampo, $valore) {

        $campo = $this->getArrayCampo($codProc, $nomeCampo, $valore);
        //$campo = $this->getArrayCampo("DICHIARANTE_COGNOME_NOME", $praFoList_rec['FODICHIARANTE']);
        $recordRicDag[] = array(
            'RECORD' => array_map(array($this, 'encodeForXml'), $campo)
        );

        return $recordRicDag;
    }

    private function getArrayCampo($codProc, $dagKey, $ricDat) {

        $campo = array(
            'ITECOD' => $codProc,
            'ITEKEY' => $codProc,
            'DAGKEY' => $dagKey,
            'RICDAT' => $ricDat
        );

        return $campo;
    }

    public function getDataModelAcqStimolo($praFoList_rec) {
        $datamodelArray = array();
        $this->praLib = new praLib();
        $this->praLibCart = new praLibCart();


        //Trovo il fascicolo (PROGES) collegato allo stimolo da riportare
        $sql = "SELECT * FROM PRAFOLIST WHERE PRAFOLIST.FOIDPRATICA = '" . $praFoList_rec['FOIDPRATICA'] . "'"
                . " AND PRAFOLIST.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "'"
                . " AND PRAFOLIST.FOTIPOSTIMOLO = '" . praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA] . "'";

        $prafolistPrinc_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$prafolistPrinc_rec) {
            return $datamodelArray;
        }

        $sql = "SELECT * FROM PROGES WHERE PROGES.GESNUM = " . $prafolistPrinc_rec['FOGESNUM'];

        $proges_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$proges_rec) {
            return $datamodelArray;
        }


        // Riempire $datamodelArray con PROGES_REC e ANADES_REC
        // Vedi documento XMind

        $arrayProges_rec = $this->getProges_recStimolo($praFoList_rec, $proges_rec);


        // Carico vettore $$arrayAnades_rec con i dati di ANADES_REC
        $arrayAnades_rec = $this->getAnadesStimolo_rec($praFoList_rec, $prafolistPrinc_rec);

        // carico il vettore $arrayProRic_rec
        $arrayProRic_rec = $this->getProRic_recStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        // Crea cartella di appoggio per la sessione corrente
        $pathFile = itaLib::createAppsTempPath('tmp' . $praFoList_rec['FOPRAKEY']);

        // Genero XMLINFO per la pratica principale
        $arrayxmlInfo = $this->getXmlInfoStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        $file = $pathFile . "/XMLINFO.xml";


        $ItaXmlObj = new itaXML;

        $ItaXmlObj->noCDATA();

        $ItaXmlObj->toXML($arrayxmlInfo);
        $xmlInfoString = $ItaXmlObj->getXml();

        //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
        // Salvare il contenuto di questa stringa in un file
        file_put_contents($file, $xmlInfoString);



//Out::msgInfo("File XMLINFO che creo ", $file);
//Out::msgInfo("Array XMLINFO ", print_r($arrayxmlInfo, true));

        $arrayAllegati = $this->getArrayAllegati($praFoList_rec);

        $datamodel = array(
            'tipoInserimento' => "PRAFOLIST",
            'PRAFOLIST_REC' => $praFoList_rec,
            'PROGES_REC' => $arrayProges_rec,
            'ANADES_REC' => $arrayAnades_rec,
            'PRORIC_REC' => $arrayProRic_rec,
            'ALLEGATI' => $arrayAllegati,
            'XMLINFO' => $file,
            'esterna' => true,
            'starweb' => false,
            'escludiPassiFO' => true,
            'progressivoDaRichiesta' => false,
            'tipoReg' => "integrazione"
        );
        //Out::msgInfo("Data Model ", print_r($datamodel, true));

        array_push($datamodelArray, $datamodel);
        unset($datamodel);  // Svuota il vettore
        //Out::msgInfo("Data Model Array", print_r($datamodelArray, true));
//Out::msgInfo("Dentro praFoList2DataModelStarWs ", print_r($praFoList_rec['ROW_ID'], true));


        return $datamodelArray;
    }

    private function getProges_recStimolo($praFoList_rec, $proges_rec) {

        $gesDre = date("Ymd");
        $gesDri = $praFoList_rec['FOPRADATA'];
        $gesOra = $praFoList_rec['FOPRAORA'];
        $gesPra = "";
// $gesPra = $praFoList_rec['FOPRAKEY'] // Codice pratica per Regione Toscana è 'NGRLCN51D03Z600C-22062018-1208'
        $gesPro = $proges_rec['GESPRO'];    // ANAPRA.PRANUM  "000001";
        $gesTsp = $proges_rec['GESTSP'];   // PRAFODECODE.FODESTTSP  (1)
        $gesSpa = "0";
        $gesEve = $proges_rec['GESEVE'];
        $gesSeg = $proges_rec['GESSEG'];


        $gesRes = $proges_rec['GESRES'];


        $arrayProges = array(
            'GESDRE' => $gesDre,
            'GESDRI' => $gesDri,
            'GESORA' => $gesOra,
            'GESPRA' => $gesPra,
            'GESPRO' => $gesPro,
            'GESRES' => $gesRes,
            'GESTSP' => $gesTsp,
            'GESSPA' => $gesSpa,
            'GESEVE' => $gesEve,
            'GESSEG' => $gesSeg,
            'GESDCH' => ''
        );

        return $arrayProges;
    }

    private function getAnadesStimolo_rec($praFoList_rec, $praFoListPrinc_rec) {

        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);


        $desRuo = "0001";  // Codice Esibente
        $desNom = $praFoList_rec['FOESIBENTE'];
        $desInd = "";
        $desCap = "";
        $desCit = "";
        $desPro = "";
        $desEma = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];
        $desFis = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];


        $arrayAnades = array(
            'DESRUO' => $desRuo,
            'DESNOM' => $desNom,
            'DESIND' => $desInd,
            'DESCAP' => $desCap,
            'DESCIT' => $desCit,
            'DESPRO' => $desPro,
            'DESEMA' => $desEma,
            'DESFIS' => $desFis
        );

        return $arrayAnades;
    }

    private function getXmlInfoStimolo($praFoList_rec, $praFoListPrinc_rec, $proges_rec) {

        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);

        $arrayProRic_rec = $this->getProRic_recStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        $ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta

        /*
         * XML RICITE (Fittizio per caricamento allegati integrazione)
         */
        $arrayRicIte = array();
        $passo = array(
            'RICNUM' => $ricnum,
            'ITEKEY' => $ricnum,
        );
        $recordRicite[] = array(
            'RECORD' => array_map(array($this, 'encodeForXml'), $passo)
        );
        $arrayRicIte[] = array($recordRicite);



        $arrayRicDoc = array();

        $praLib = new praLib();

        // Riporto RICDOC - Leggo record di PRAFOFILES
        $sql = "SELECT * FROM PRAFOFILES "
                . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

        $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);


        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            $iteKey = $ricnum;
            $fileName = $praFoFiles_rec['FILENAME'];
            $docSha2 = $dir . "/" . $praFoFiles_rec['FILEFIL'];
            $docPrt = "";

            $allegato = array(
                'ITEKEY' => $iteKey,
                'DOCNAME' => $fileName,
                'DOCSHA2' => $docSha2,
                'DOCPRT' => $docPrt
            );

//array_push($arrayRicDoc, $allegato);

            $recordRicDoc[] = array(
                'RECORD' => array_map(array($this, 'encodeForXml'), $allegato)
            );

            unset($allegato);  // Svuota il vettore
        }
        $arrayRicDoc[] = array($recordRicDoc);

// Riporto RICDAG
        $arrayRicDag = array();
        $recordRicDag = array();

//ESIBENTE
//        $recordRicDag[] = $this->getRicDag($proges_rec['GESPRO'], "ESIBENTE_NOME", $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode]);
        // Riporto le RICHIESTE_ACCORPATE, solo se è Procedimento Principale
        $arrayRichieste_Accorpate = array();


        $arrayProRic = array_map(array($this, 'encodeForXml'), $arrayProRic);


        $root = array(
            'PRORIC' => array($arrayProRic),
            'RICITE' => $arrayRicIte,
            'RICDOC' => $arrayRicDoc,
            'RICDAG' => $arrayRicDag,
            'RICHIESTE_ACCORPATE' => $arrayRichieste_Accorpate // array()
        );



//array_walk_recursive($root, array($this,'encodeForXml'));
//Out::msgInfo("Info", print_r($root, true));

        $arrayXmlInfo = array(
            'ROOT' => $root
        );

        return $arrayXmlInfo;
    }

    private function getProRic_recStimolo($praFoList_rec, $praFoListPrinc_rec, $proges_rec) {
        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);

        $esibenteNome = $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode];
        $esibenteCognome = $arrayCopertina['presentatore'][0]['cognome'][0][itaXml::textNode];
        $esibenteCodFisc = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];
        $esibenteMail = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];

        $ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta
        $ricpro = $proges_rec['GESPRO'];  // "000001";  // Numero procedimento (ANAPRA.PRANUM)
        $riceve = $proges_rec['GESEVE'];   // Codice Evento  (6)
        $rictsp = $proges_rec['GESTSP'];  //Codice Sportello ("1")
        $ricstt = $proges_rec['GESSTT'];   // Codice Settore ("1")
        $ricatt = $proges_rec['GESATT'];    // Codice Attivita  ("1")
        $ricseg = "0";   // Tipo Segnalazione  ("0")
        $ricres = $proges_rec['GESRES'];   // ANAPRA.PRARES


        $ricrun = '';  // Si valorizza solo per le pratiche accorpate

        $ricrpa = $praFoListPrinc_rec['FOIDPRATICA'];   // FOPRAKEY DELLA PRATICA PADRE


        $arrayProRic = array(
            'RICNUM' => $ricnum,
            'RICKEY' => $ricnum,
            'RICPRO' => $ricpro,
            'RICRES' => $ricres,
            'RICEVE' => $riceve,
            'RICTSP' => $rictsp,
            'RICSTT' => $ricstt,
            'RICATT' => $ricatt,
            'RICSEG' => $ricseg,
            'RICRUN' => $ricrun,
            'RICCOG' => $esibenteCognome, // Dati Esibente
            'RICNOM' => $esibenteNome,
            'RICEMA' => $esibenteMail,
            'RICFIS' => $esibenteCodFisc,
            'RICDRE' => date('Ymd'),
            'RICDAT' => $praFoList_rec['FOPRADATA'],
            'RICTIM' => $praFoList_rec['FOPRAORA'],
            'RICNPR' => substr($praFoList_rec['FOPROTDATA'], 0, 4) . $praFoList_rec['FOPROTNUM'],
            'RICDPR' => $praFoList_rec['FOPROTDATA'],
            'RICRPA' => $ricrpa    // FOPRAKEY DELLA PRATICA PADRE
        );

        return $arrayProRic;
    }

    public function apriPasso($praFoList_rec, $Propas_rec) {

        if ($praFoList_rec['FOTIPOSTIMOLO'] != praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_CART_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]) {

            $model = 'praPasso';
            $_POST = array();
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            //$_POST['perms'] = $this->perms;
            $_POST[$model . '_returnModel'] = '';
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent("openform");
            $objModel->parseEvent();

            return true;
        }
        return false;
    }

    public function getAllegato($prafolist_rec, $rowidAlle) {
        $sql = "SELECT * FROM PRAFOFILES WHERE ROW_ID = " . $rowidAlle;
        $prafofiles_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        //Out::msgInfo("PRAFOFILES", print_r($prafofiles_rec,true));

        $anno = substr($prafolist_rec['FOPRADATA'], 0, 4);
        $dir = $this->praLib->SetDirectoryPratiche($anno, $prafofiles_rec['FOPRAKEY'], $prafofiles_rec['FOTIPO']);

        //Out::msgInfo("Dir", $dir);


        return array('FILENAME' => $prafofiles_rec['FILENAME'], 'DATAFILE' => $dir . "/" . $prafofiles_rec['FILEFIL']);
    }

    public function caricaRichiestaFO($prafolist_rec) {
        $ret_esito = null;
        if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito)) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'] = "Errore di acquisizione: " . praFrontOfficeManager::$lasErrMessage;
            return false;
        }
        return $ret_esito;
    }

    public function GetAllegatiAccorpate($fileXML) {
        /*
         * Estrazione dati da XML
         *
         */
        $xmlObj = new QXML;
        $ret = $xmlObj->setXmlFromFile($fileXML);
        if ($ret == false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore apertura XML: $fileXML");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());

        $arrAlleAccorpate = array();
        $XML_RichiesteAccorpate_tab = array();
        $Accorpate_tab = $arrayXml['RICHIESTE_ACCORPATE']['XMLINFO'];
        if ($Accorpate_tab) {
            if (isset($Accorpate_tab[0])) {
                $XML_RichiesteAccorpate_tab = $Accorpate_tab;
            } else {
                $XML_RichiesteAccorpate_tab[0] = $Accorpate_tab;
            }

            foreach ($XML_RichiesteAccorpate_tab as $XML_RichiesteAccorpate_rec) {
                $numRichiesta = substr($XML_RichiesteAccorpate_rec["@textNode"], 8, 10);
                $arrAlleAccorpate[$numRichiesta] = $this->GetAllegatiPratica($numRichiesta);
                $arrAlleAccorpate[$numRichiesta] = "";
            }
        }
        return $arrAlleAccorpate;
    }

    public function invioCart($param){
        $this->praLib = new praLib();
        $this->praLibCart = new praLibCart();

        //Out::msgInfo("parametro", print_r($param, true));

//                    Struttura:
//                        $param = array(
//                            'GESNUM' => $this->currGesnum,
//                            'OPERATION' => 'inviaStimoloRequest',
//                            'TIPOSTIMOLO' => 'comunicazione',
//                            'ENTI' => $arrayEnti,
//                            'ALLEGATI' => $arrayAllegati,
//                            'OGGETTO' => 'Comunicazione Generico',
//                            'MESSAGGIO' => 'Corpo del messaggio'
//                        );
        
        
        $arrayEnti = $param['ENTI'];
        if (!$arrayEnti){
            Out::msgError("Errore", "Scegliere almeno un Ente di Destinazione");
            return;
        }
        
        $arrayAllegati = $param['ALLEGATI'];
        if ($arrayAllegati){
            // TODO: Controllare che estensione dei files siano quelle accettate dal CART
            $errore = false;

            foreach($arrayAllegati as $allegatoCart){

                if (!$this->controlloEstensioneAllegatoXCart($allegatoCart['FILENAME'])){
                    $errore = true;
                    break;
                }
                
            }
            
            if ($errore){
                Out::msgError("Errore", "Ci sono alcuni files allegati che hanno estensioni non accettati dal CART.</br>"
                        . "Le estensioni accettate sono: </br>"
                        . "pdf; pdf.p7m; xml; dwf; dwf.p7m; svg; svg.p7m; jpg; jpg.p7m </br>"
                        . "Allegare file con estensioni ammesse prima di poter procedere alla comunicazione con il CART. </br>");
                return;
                
            }
                
        }
        
        
        $codice = array();
        $codice['GESNUM'] = $param['GESNUM'];
        $codice['PROPAK'] = '';
        
        $praFoList_rec = $this->praLib->GetPrafolist($codice, 'gesnum', false);
        if (!$praFoList_rec) {
            Out::msgError("Errore", "Fascicolo corrente non è stato ricevuto dal CART");
            return;
        }
        
        $cart_stimolo_rec = $this->praLibCart->getCart_stimolo($praFoList_rec['FOPRAKEY']);
        if (!$cart_stimolo_rec) {
            Out::msgError("Errore", "Fascicolo corrente non è stato ricevuto dal CART");
            return ;
        }

        
        $cartClient = new itaCartServiceClient();

        // Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($cartClient);
        
        $esito = false;
        
        switch ($param['TIPOSTIMOLO']) {
            case "comunicazione":
                $esito = $this->invioComunicazioneGenerica($cartClient, $param, $cart_stimolo_rec);
                break;
            case "notifica":
                $esito = $this->invioNotifica($cartClient, $param, $cart_stimolo_rec, $praFoList_rec);
                break;
            case "richiestaIntegrazioni":
            case "richiestaConformazioni":
                $esito = $this->invioIntegrazione($cartClient, $param, $cart_stimolo_rec);
                break;
            case "diniego":
                $esito = $this->invioDiniego($cartClient, $param, $cart_stimolo_rec);
                break;
            case "inoltroIntegrazione":
            case "inoltroConformazione":
                $esito = $this->invioInoltro($cartClient, $param, $cart_stimolo_rec);
                break;
            
        }        
        
        if ($esito){
            Out::msgInfo("Invio CART", "La trasmissione al CART è avvenuta con successo !!!");
        }
        
        //praFrontOfficeCartWs::invioComunicazioneGenerica($tipoStimolo, $param);
        return ;
    }
    
    
    private function invioNotifica($cartClient, $param, $cart_stimolo_rec, $praFoList_rec) {
        $datetime = new DateTime();
        $idCart = '';
        
        $tipoStimolo = $param['OPERATION'];

        // Carico sul vettore $arrayAllegCart tutti gli allegati arrivati con la presentazione istanza
        $arrayAllegCart = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);  
        
        //Out::msgInfo("Allegati CART", print_r($arrayAllegCart, true));
        $standard = '';
        foreach($arrayAllegCart as $allegato){
            // Il campo USOMODELLO contiene la stringa "STANDARD"
            if (strpos($allegato['USOMODELLO'], 'STANDARD') !== false){
                $standard = $allegato['USOMODELLO'];
                break;
            }
        }

        if (!$standard){
            Out::msgError("Errore", "Non è stato ritrovato il tipo di STANDARD della pratica CART");
            return false;
        }

        
        
        //Out::msgInfo("Copertina", print_r($praFoList_rec, true));
        
        //$arrayCoperatina = json_decode($cart_stimolo_rec['METADATI'], true); 
        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);

        //Out::msgInfo("Copertina", print_r($arrayCopertina, true));
        
        $tipoRichiesta = $arrayCopertina['oggettoComunicazione'][0]['attivita'][0] ['ns2:idAttivitaBDR'][0][itaXml::textNode];
        if (!$tipoRichiesta){
            Out::msgError("Errore", "Non è stato ritrovato il tipo di PROCEDIMENTO SUAP della pratica CART");
            return false;
        }

        $arrayAllegati = $param['ALLEGATI'];
        if ($arrayAllegati){
            // Si scorrono gli allegati e se tra quelli caricati ci sono quelli della pratica originaria, si tolgono
            foreach($arrayAllegati as $key => $allegato){
                if (in_array($allegato['FILEORIG'], $arrayAllegCart) ){
                    unset($arrayAllegati[$key]);
                }
            }
        }

        //Out::msgInfo("Allegati dopo", print_r($arrayAllegati, true));

        $entiInvioFatto = '';
        foreach($arrayEnti as $ente){
            if ($this->notificaInviata($ente['DESTCART'], $cart_stimolo_rec['IDMESSAGGIO'])){
                $entiInvioFatto = $ente['DESTCART'] . "<br>";
            }
            
        }

        if ($entiInvioFatto){
                Out::msgInfo("Errore", "E' gia stata inviata la notifica ai seguenti Enti: <br>" .  $ente['DESTCART'] . ". <br>"
                        . "Per poterla procedere ad un nuovo invio, occorre prima annullare l'invio fatto, <br>"
                        . "inviando un messaggio di 'segnalazioneErrore' ");

                return false;
        }
        
        
        
        // Si scorrono tutti gli Enti Cart e si invia il messaggio
        $arrayEnti = $param['ENTI'];
        foreach($arrayEnti as $ente){
            
           // Out::msgInfo("Ente Invio", print_r($ente,true));
            
            if ($this->notificaInviata($ente['DESTCART'], $cart_stimolo_rec['IDMESSAGGIO'])){
                Out::msgInfo("Errore", "E' gia stata inviata la notifica all'Ente Terzo " .  $ente['DESTCART'] . ". <br>"
                        . "Per poterla inviare nuovamente, occorre prima annullare l'invio fatto, <br>"
                        . "inviando un messaggio di 'segnalazioneErrore' ");

                next;
            }
            
            $arrayCodiciEndo = $this->getCodiciEndo($ente['DESTCART'], $cart_stimolo_rec['IDMESSAGGIO']);
            
            $xmlInvio = $this->getXmlNotifica($param, $cart_stimolo_rec, $ente, $arrayAllegati, $datetime, $standard, $tipoRichiesta, $arrayCodiciEndo);
            

            //Out::msgInfo("Xml Invio", htmlspecialchars($xmlInvio));

            //Gestisce risposta invio notifica e salva l'invio in cart_invio
            
            
            $retCall = $cartClient->ws_fruizione($xmlInvio, $tipoStimolo, $arrayAllegati);
            if (!$retCall) {
                Out::msgStop("Errore Fruizione", $cartClient->getFault() . " " . $cartClient->getError);
                return false;
            }
            $result = $cartClient->getResult();

            // Rilegge eventuali allegati di risposta
            $allegati = $cartClient->getAttachments();
            if ($allegati){
    //            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
    //            Out::msgInfo("Allegati", print_r($allegati, true));
            }

//            Out::msgInfo("Risposta", print_r($result, true));

            if (!$this->salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                return false;
            }

        }

        return true;

    }

    private function invioComunicazioneGenerica($cartClient, $param, $cart_stimolo_rec) {
        $datetime = new DateTime();
        $idCart = '';
        
        $tipoStimolo = $param['OPERATION'];

        $arrayAllegati = $param['ALLEGATI'];
        if (!$arrayAllegati){
            Out::msgError("Errore", "Per effettuare l'invio del messaggio occorre OBBLIGATORIAMENTE allegare almeno un allegato");
            return false;
        }
        
        
        if ($param['OGGETTO'] == ''){
            Out::msgError("Errore", "Per effettuare l'invio del messaggio occorre OBBLIGATORIAMENTE inserire l'oggetto del messaggio");
            return false;
        } 
        
        if (strlen($param['OGGETTO']) > 500){
            Out::msgError("Errore", "Lunghezza massima consentita per Oggetto del Messaggio è di 500 caratteri");
            return false;
        }
        
        $arrayEnti = $param['ENTI'];
        // Se Ente di invio non è FACCT, bisogna aver inviato la Notifica all'Ente
        $entiInvioNonFatto = '';
        foreach($arrayEnti as $ente){
            if ($ente['DESTCART'] != 'FACCT'){
                if (!$this->notificaInviata($ente['DESTCART'], $cart_stimolo_rec['IDMESSAGGIO'])){
                    $entiInvioNonFatto = $ente['DESTCART'] . "<br>";
                }
            }
            
        }

        if ($entiInvioNonFatto){
                Out::msgInfo("Errore", "Prima di proseguire occorre inviare la notifica ai seguenti Enti: <br>" .  $ente['DESTCART'] . ". <br>");

                return false;
        }
        
        
        // Si scorrono tutti gli Enti Cart e si invia il messaggio
        foreach($arrayEnti as $ente){

            //TODO: Costruire $xmlInvio;
            $xmlInvio = $this->getXmlComunicazioneGenerica($param, $cart_stimolo_rec, $ente, $arrayAllegati, $datetime);

//            Out::msgInfo("Xml Invio", htmlspecialchars($xmlInvio));
//            return false;
            
            $retCall = $cartClient->ws_fruizione($xmlInvio, $tipoStimolo, $arrayAllegati);
            if (!$retCall) {
                Out::msgStop("Errore Fruizione", $cartClient->getFault() . " " . $cartClient->getError);
                return false;
            }
            $result = $cartClient->getResult();

            // Rilegge eventuali allegati di risposta. PEr questo messaggio non ci sono
            $allegati = $cartClient->getAttachments();
            if ($allegati){
    //            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
    //            Out::msgInfo("Allegati", print_r($allegati, true));
            }

            if (!$this->salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                return false;
            }


            //Out::msgInfo("Risultato Invio " . $tipoStimolo, print_r($cartClient->getResult(), true));
           //Out::msgInfo("Attachment", print_r($allegati, true));
        

/*
Array
(
    [idMessaggio] => SEM-WFA-1569517625080-1185915514b5d043d383df6c881394dd0bc987b6e179bd869ca3715dc8b49e771ca6c309a4
    [esito] => KO
    [!msgErrore] => it.toscana.regione.suap.wfa.packaging.NormalizzaException
)

 * Array
(
    [idMessaggio] => SEM-WFA-156951771778018518394252e59248a988e1c11d8b4cc400e5407c5b67eb52f4a899071b4c4393c22d9bef9
    [esito] => OK
)
  */      

        }
        
        return true;
        
    }

    private function getXmlComunicazioneGenerica($param, $cart_stimolo_rec, $ente, $arrayAllegati, $datetime){
        if (!$datetime){
            $datetime = new DateTime();
        }

//        Out::msgInfo("Data ", print_r($datetime, true));
//        Out::msgInfo("DataCART ", $this->praLibCart->getDatetimeNow($datetime));
//        Out::msgInfo("Data ", print_r(date_format($datetime, 'd/m/y'), true));
        
        $xmlInvio = '<proc:stimolo>
		<proc:mittente> 
                    <proc:tipologia>SUAP</proc:tipologia>
                    <proc:ente>' . $cart_stimolo_rec['MITTENTE_ENTE']  . '</proc:ente> '
                . '</proc:mittente>'
                . '<proc:data>' . $this->praLibCart->getDatetimeNow($datetime)  . '</proc:data>'
                . '<proc:idPratica>' . $cart_stimolo_rec['IDPRATICA']  . '</proc:idPratica> '
                . '<proc:attributiSpecifici>
			<proc:comunicazione>
                            <proc:destinatario>' . $ente['DESTCART'] . '</proc:destinatario>'
                . '<proc:oggetto>' . $param['OGGETTO'] . '</proc:oggetto> '
                . '<proc:corpo>' . $param['MESSAGGIO'] . '</proc:corpo> ';

//                            <proc:destinatario>FACCT</proc:destinatario>'

        
        //Riporta gli Allegati
        foreach ($arrayAllegati as $allegato){
            
            $xmlInvio = $xmlInvio . '<proc:allegato>'
                    . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>'
                    . '<proc:contentType>' . $allegato['CONTENTTYPE'] . '</proc:contentType>'
                    . '<proc:nomefileOriginale>'  . $allegato['FILENAME'] . '</proc:nomefileOriginale> '
                    . '</proc:allegato>' ;            
            
        }
        
        //Out::msgInfo("Parametri", print_r($param, true));
        
        if (is_numeric($param['ATTESA_GG'])){
            $giorni = "P" . $param['ATTESA_GG'] . "D";

            $xmlInvio = $xmlInvio . '<proc:attesaRisposta>' . $giorni . '</proc:attesaRisposta>';

/*
            if (is_numeric($param['ATTESA_GG'])){
                $giorni = "P" . $param['ATTESA_GG'] . "D";

                $xmlInvio = $xmlInvio . '<proc:attesaRisposta>' . $giorni . '</proc:attesaRisposta>';
            }
*/            
            
            
        }
        
        
        $xmlInvio = $xmlInvio . '</proc:comunicazione>
		</proc:attributiSpecifici>
	</proc:stimolo>';
        

        return $xmlInvio;
    }

    private function salvaCartInvio($idMessaggio, $tipoStimolo, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec){
        $esito = "OK";

        $cart_invio_rec = array();
        $cart_invio_rec['IDMSGCARTSTIMOLO'] = $cart_stimolo_rec['IDMESSAGGIO']; 
        $cart_invio_rec['IDMESSAGGIO'] = $idMessaggio;
        $cart_invio_rec['DATAINVIO'] = $this->praLibCart->getDatetimeNow($datetime);
        $cart_invio_rec['TIPOINVIO'] = $tipoStimolo;    
        $cart_invio_rec['DESTINATARIO'] = $ente['DESTCART'];    
        $cart_invio_rec['ESITO'] = $esito;   
        $cart_invio_rec['METADATI'] = $xmlInvio;   // json_encode($xmlInvio)

        $nRows = ItaDB::DBInsert($this->praLibCart->getITALWEB(), 'CART_INVIO', 'ROW_ID', $cart_invio_rec);
        if ($nRows == -1) {
            Out::msgStop("Errore", "Errore");
//            $this->setErrCode(-1);
//            $this->setErrMessage("Aggiornamento conferma ricezione in CART_INVIO con IDMESSAGGIO = " . $cart_invio_rec['IDMESSAGGIO'] . " fallito.");

            return false;
        }

        $this->salvaCartInvioFile($idMessaggio, $arrayAllegati);

        //Out::msgInfo("salvaCartInvio", print_r($cart_invio_rec,true));
        
        
        return true;
        
    }

    private function salvaCartInvioFile($idMessaggio, $arrayAllegati){

        $esito = "OK";

        if (!$arrayAllegati){
            return true;
        }
        
        foreach($arrayAllegati as $allegato){
            $cart_allegato = array();
            $cart_allegato['IDMSGCARTINVIO'] = $idMessaggio;
            $cart_allegato['NOMEFILE'] = $allegato['FILEORIG'];  
            $cart_allegato['HASHFILE'] = '';    
            $cart_allegato['CONTENTID'] = $allegato['CID'];
            $cart_allegato['CONTENTTYPE'] = $allegato['CONTENTTYPE'];   
            $cart_allegato['ESITO'] = $esito;   
            $cart_allegato['FILEFIL'] = $allegato['FILENAME'];   

            $nRows = ItaDB::DBInsert($this->praLibCart->getITALWEB(), 'CART_INVIOFILE', 'ROW_ID', $cart_allegato);
            if ($nRows == -1) {
    //            $this->setErrCode(-1);
    //            $this->setErrMessage("Aggiornamento conferma ricezione in CART_INVIO con IDMESSAGGIO = " . $cart_allegato['IDMESSAGGIO'] . " fallito.");

                return false;
            }
            
        }
        
        return true;
        
    }
    
    
    function AggiornaPartenza($dest, $param, $idCart = '') {
        $keyPasso = $param['KEYPASSO'];

        $pracom_recP = $this->praLib->GetPracomP($keyPasso);
        $dest['DATAINVIO'] = date("Ymd");
        $dest['ORAINVIO'] = date("H:i:s");
        if ($pracom_recP) {
            $dest['SCADENZARISCONTRO'] = $this->praLib->CalcolaDataScadenza($pracom_recP['COMGRS'], $dest['DATAINVIO']);
        }
        if ($idCart){
            $dest['IDMESSAGGIOCART'] = $idCart;
        }
        if ($dest['ROWID'] == 0) {
            $dest['TIPOCOM'] = 'D';
            $dest['KEYPASSO'] = $keyPasso;
            $dest['NOME'] = strip_tags($dest['NOME']);
//Valorizzo sempre il ROWIDPRACOM su PRAMITDEST con il ROWID unico di PRACOM PARTENZA

            if ($pracom_recP) {
                $dest['ROWIDPRACOM'] = $pracom_recP['ROWID'];
            }

            $insert_Info = "Oggetto: Inserimento destinatrio " . $dest['NOME'] . " del passo $keyPasso";
            if (!$this->insertRecord($this->praLib->PRAM_DB, 'PRAMITDEST', $dest, $insert_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore Inserimento destinatario " . $dest['NOME']);
                return false;
            }
        } else {
            unset($dest['FIRMATOCDS']);
            $update_Info = "Oggetto: Aggiorno destinatrio " . $dest['NOME'] . " del passo $keyPasso";
            if (!$this->updateRecord($this->praLib->PRAM_DB, 'PRAMITDEST', $dest, $update_Info)) {
                Out::msgStop("ATTENZIONE!", "Errore Aggiornamento destinatario " . $dest['NOME']);
                return false;
            }
        }
 
        return true;
    }


    private function notificaInviata($codEndo, $msgCartStimolo){
        $inviata = false;
        
        // Si controlla se è già stata fatta la notifica all'Ente scelto ($codEndo)
        $sql = "SELECT * FROM CART_INVIO WHERE CART_INVIO.IDMSGCARTSTIMOLO = '" . $msgCartStimolo . "' "
                . "AND CART_INVIO.DESTINATARIO = '" . $codEndo . "'"
                . "AND CART_INVIO.TIPOINVIO = 'notifica'";
        $cart_invio_tab = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, true);
        
        if ($cart_invio_tab) {
            
            foreach($cart_invio_tab as $cart_invio_rec){
                // Per ogni eventuale record di Notifica inviato per l'ente, controllo se c'è collegato
                // in CART_INVIO una SegnalazioneErrore collegata, se non c'è, la notifica non può essere reinviata
                $sql1 = "SELECT * FROM CART_INVIO WHERE CART_INVIO.IDMSGCARTSTIMOLO = '" . $msgCartStimolo . "' "
                        . "AND CART_INVIO.IDMSGANNULLATO = '" . $cart_invio_rec['IDMESSAGGIO'] . "'";
                
                $cart_annullato_rec = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql1, false);

                if (!$cart_annullato_rec){
                    $inviata = true;
                    break;
                }
            }
        }
        return $inviata;
    }

    private function getXmlNotifica($param, $cart_stimolo_rec, $ente, $arrayAllegati, $datetime, $standard, $tipoRichiesta, $arrayCodiciEndo){
        if (!$datetime){
            $datetime = new DateTime();
        }

        
        $xmlInvio = '<proc:stimolo>
		<proc:mittente> 
                    <proc:tipologia>SUAP</proc:tipologia>
                    <proc:ente>' . $cart_stimolo_rec['MITTENTE_ENTE']  . '</proc:ente> '
                . '</proc:mittente>'
                . '<proc:data>' . $this->praLibCart->getDatetimeNow($datetime)  . '</proc:data>'
                . '<proc:idPratica>' . $cart_stimolo_rec['IDPRATICA']  . '</proc:idPratica> '
                . '<proc:attributiSpecifici>
			<proc:notifica destinatario = "' .  $ente['DESTCART'] . '" > ';


        $xmlInvio = $xmlInvio . $this->getXmlModulo($standard, $cart_stimolo_rec['IDMESSAGGIO']);
        
        $xmlInvio = $xmlInvio . $this->getXmlModulo($tipoRichiesta, $cart_stimolo_rec['IDMESSAGGIO']);
        
        //TODO: Prendere i modelli dei vari Endo-procedimenti dell'ente Corrente (se ASL, tutti quelli con ASL%; se AMBRT tutti quelli con AMB% ecc...)
        if ($arrayCodiciEndo){
            foreach($arrayCodiciEndo as $codEndo){

                $xmlInvio = $xmlInvio . $this->getXmlModulo($codEndo, $cart_stimolo_rec['IDMESSAGGIO']);

                
            }
        }
        
        if ($arrayAllegati){
            //Riporta gli Allegati
            foreach ($arrayAllegati as $allegato){

                $xmlInvio = $xmlInvio . '<proc:allegatoAggiuntivo>';
                
                if ($allegato['HASFILE']){
                    $xmlInvio = $xmlInvio . '<proc:hashedFile> '
                        . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>'
                        . '<proc:hash>' . $allegato['HASHFILE'] . '</proc:hash>'
                    . '</proc:hashedFile>';
                }
                else {
                    $xmlInvio = $xmlInvio . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>';
                }
                
                $xmlInvio = $xmlInvio . '<proc:contentType>' . $allegato['CONTENTTYPE'] . '</proc:contentType>'
                        . '<proc:nomefileOriginale>'  . $allegato['FILENAME'] . '</proc:nomefileOriginale> '
                        . '</proc:allegatoAggiuntivo>' ;            

            }
        }

        $xmlInvio = $xmlInvio . '<proc:integrazione>' . $param['INTEGRAZIONE'] . ' </proc:integrazione>';
        
        //TODO: Prendere copertina-xml e copertina-pdf
        $xmlInvio = $xmlInvio . $this->getXmlModello('copertina-xml', $cart_stimolo_rec['IDMESSAGGIO'], 'copertina-xml');
        $xmlInvio = $xmlInvio . $this->getXmlModello('copertina-pdf', $cart_stimolo_rec['IDMESSAGGIO'], 'copertina-pdf');
        
        
        $xmlInvio = $xmlInvio . '</proc:notifica>
		</proc:attributiSpecifici>
	</proc:stimolo>';
        
/*        
        $xmlInvio = '<proc:stimolo>
	<proc:mittente><proc:tipologia>SUAP</proc:tipologia><proc:ente>13.13.1.M.000.051004</proc:ente></proc:mittente><proc:data>2019-10-01T11:58:05</proc:data><proc:idPratica>VNCSML70R20A564B-02092019-1611</proc:idPratica><proc:attributiSpecifici><proc:notifica destinatario="ASL"><proc:modulo identificativoModulo="STANDARD 2"><proc:modello-MDA><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93485</proc:contentID><proc:hash>d3626df944e4950b808c6d7f88085a48e4e816a0</proc:hash></proc:hashedFile><proc:contentType>text/plain</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.STANDARD_2.XML</proc:nomefileOriginale></proc:modello-MDA><proc:modello-PDF><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93484</proc:contentID><proc:hash>5d535ce8b5d235241a56b05a31720d534b7b3535</proc:hash></proc:hashedFile><proc:contentType>application/pkcs7-mime</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.STANDARD_2.PDF.P7M</proc:nomefileOriginale></proc:modello-PDF></proc:modulo><proc:modulo identificativoModulo="47.100R"><proc:modello-MDA><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93479</proc:contentID><proc:hash>1ad9274420d128e85b668457c57bed8810bd608e</proc:hash></proc:hashedFile><proc:contentType>text/plain</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.47.100R.XML</proc:nomefileOriginale></proc:modello-MDA><proc:modello-PDF><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93481</proc:contentID><proc:hash>008d84784e1ea76d3a7561020c4de770f9eb656f</proc:hash></proc:hashedFile><proc:contentType>application/pkcs7-mime</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.47.100R.PDF.P7M</proc:nomefileOriginale></proc:modello-PDF><proc:allegato><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93478</proc:contentID><proc:hash>0ac145492c0376409e1bdea36885c34c955a0928</proc:hash></proc:hashedFile><proc:contentType>application/pdf</proc:contentType><proc:nomefileOriginale>DIRITTI_ISTRUTTORIA_A851-2430_.pdf</proc:nomefileOriginale><proc:idSemantico></proc:idSemantico></proc:allegato><proc:allegato><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93480</proc:contentID><proc:hash>779ff13afe8e7e0b893961af4aae9982d3efe6fe</proc:hash></proc:hashedFile><proc:contentType>application/pdf</proc:contentType><proc:nomefileOriginale>CC1_A851-2431_.pdf</proc:nomefileOriginale><proc:idSemantico></proc:idSemantico></proc:allegato></proc:modulo><proc:modulo identificativoModulo="ASL 90"><proc:modello-MDA><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93486</proc:contentID><proc:hash>5c260821a3ef39f18fb1dca81fbdd5e94ca16eec</proc:hash></proc:hashedFile><proc:contentType>text/plain</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.ASL_90.XML</proc:nomefileOriginale></proc:modello-MDA><proc:modello-PDF><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93488</proc:contentID><proc:hash>d99c237062586eea1e140cc800e1160a4c866a5a</proc:hash></proc:hashedFile><proc:contentType>application/pkcs7-mime</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.MDA.ASL_90.PDF.P7M</proc:nomefileOriginale></proc:modello-PDF><proc:allegato><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93487</proc:contentID><proc:hash>36a097752f1ee532f504f4f335fdb9cf89cdc5ac</proc:hash></proc:hashedFile><proc:contentType>application/pdf</proc:contentType><proc:nomefileOriginale>PLANIMETRIANIGRO_A851-2432_.pdf</proc:nomefileOriginale><proc:idSemantico></proc:idSemantico></proc:allegato><proc:allegato><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93489</proc:contentID><proc:hash>fe6c9b536f7af8e1ec5074bd7f9c4fdc8164225e</proc:hash></proc:hashedFile><proc:contentType>application/pkcs7-mime</proc:contentType><proc:nomefileOriginale>AUA_DEMO_A851-2433_.pdf.p7m</proc:nomefileOriginale><proc:idSemantico></proc:idSemantico></proc:allegato></proc:modulo><proc:integrazione>true</proc:integrazione><proc:copertina-xml><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93483</proc:contentID><proc:hash>7e0ceda9dc4152294b42ccc68c09994dc000ece1</proc:hash></proc:hashedFile><proc:contentType>text/plain</proc:contentType><proc:nomefileOriginale>COPERTINA-VNCSML70R20A564B-02092019-1611.XML</proc:nomefileOriginale></proc:copertina-xml><proc:copertina-pdf><proc:hashedFile><proc:contentID>SEM-d7ea106701e81f8293d61b1c4363da57a0f15dddb0767506055d94eb4ff59dcb-HFnodo-certificazione-93482</proc:contentID><proc:hash>e40a5afb5ae4bd9fe999d15c53ba088937d68779</proc:hash></proc:hashedFile><proc:contentType>application/pkcs7-mime</proc:contentType><proc:nomefileOriginale>VNCSML70R20A564B-02092019-1611.SUAP.PDF.P7M</proc:nomefileOriginale></proc:copertina-pdf></proc:notifica></proc:attributiSpecifici>
</proc:stimolo>
';
*/        

        return $xmlInvio;
    }
    
    private function getXmlModulo($tipoModulo, $msgCartStimolo){
        $xmlInvio = '<proc:modulo identificativoModulo="' . $tipoModulo . '" > ';

        $xmlInvio = $xmlInvio . $this->getXmlModello($tipoModulo, $msgCartStimolo, 'modello-MDA');
        $xmlInvio = $xmlInvio . $this->getXmlModello($tipoModulo, $msgCartStimolo, 'modello-PDF');
        
        $xmlInvio = $xmlInvio . $this->getXmlAllegato($tipoModulo, $msgCartStimolo, 'Allegato');
        
        $xmlInvio = $xmlInvio . '</proc:modulo> ';
        
        return $xmlInvio;
    }

    private function getXmlModello($tipoModulo, $msgCartStimolo, $tipoAllegato){

        
        $codice = array();
        $codice['IDCARTSTIMOLO'] = $msgCartStimolo;
        $codice['TIPOFILE'] = $tipoAllegato;
        $codice['USOMODELLO'] = $tipoModulo;
        
        $cart_file = $this->praLibCart->getCart_stimoloFile($codice, 'modulo', false);
        if ($cart_file){
            
            $xmlInvio = $xmlInvio . '<proc:' . $tipoAllegato . '> '
                    . '<proc:hashedFile> '
                        . '<proc:contentID>' . $cart_file['IDFILE'] . '</proc:contentID>'
                        . '<proc:hash>' . $cart_file['HASHFILE'] . '</proc:hash>'
                    . '</proc:hashedFile>'
                    . '<proc:contentType>' . $cart_file['TIPOCONTENUTO'] . '</proc:contentType>'
                    . '<proc:nomefileOriginale>' . $cart_file['NOMEFILE'] . '</proc:nomefileOriginale>'
                    . '</proc:' . $tipoAllegato . '>';
            
        }
        
        return $xmlInvio;
    }

    private function getXmlAllegato($tipoModulo, $msgCartStimolo, $tipoAllegato){

        $codice = array();
        $codice['IDCARTSTIMOLO'] = $msgCartStimolo;
        $codice['TIPOFILE'] = $tipoAllegato;
        $codice['USOMODELLO'] = $tipoModulo;
        
        $cart_file_tab = $this->praLibCart->getCart_stimoloFile($codice, 'modulo', true);
        if ($cart_file_tab){
            
            foreach($cart_file_tab as $cart_file){
                
                $xmlInvio = $xmlInvio . '<proc:' . $tipoAllegato . '> '
                        . '<proc:hashedFile> '
                            . '<proc:contentID>' . $cart_file['IDFILE'] . '</proc:contentID>'
                            . '<proc:hash>' . $cart_file['HASHFILE'] . '</proc:hash>'
                        . '</proc:hashedFile>'
                        . '<proc:contentType>' . $cart_file['TIPOCONTENUTO'] . '</proc:contentType>'
                        . '<proc:nomefileOriginale>' . $cart_file['NOMEFILE'] . '</proc:nomefileOriginale>'
                        . '<proc:idSemantico/>'
                        . '</proc:' . $tipoAllegato . '>';
            }
            
        }
        
        return $xmlInvio;
    }

    private function getCodiciEndo($codEndo, $msgCartStimolo){
        $arrayCodiciEndo = array();
        
        $sql = "SELECT * FROM CART_STIMOLOFILE " 
                . "LEFT JOIN CART_STIMOLOFILE_DEST ON CART_STIMOLOFILE_DEST.IDFILE =  CART_STIMOLOFILE.IDFILE "
                . "WHERE CART_STIMOLOFILE.IDMSGCARTSTIMOLO = '" . $msgCartStimolo . "'"
                . "AND CART_STIMOLOFILE_DEST.DESTINATARIO = '" . $codEndo . "'";

        //out::msgInfo("Query", $sql);
        
        $cart_allegati_tab = ItaDB::DBSQLSelect($this->praLibCart->getITALWEB(), $sql, true);

        if ($cart_allegati_tab){
            foreach($cart_allegati_tab as $cart_allegato){
                
                $endoUso = $cart_allegato['USOMODELLO'];

                if (!in_array($endoUso, $arrayCodiciEndo)){
                    $arrayCodiciEndo[] = $endoUso;
                }
                
            }
        }
        
        return $arrayCodiciEndo;
    }

    
    private function invioIntegrazione($cartClient, $param, $cart_stimolo_rec) {
        $datetime = new DateTime();
        $idCart = '';
        
        $tipoStimolo = $param['OPERATION'];

        $arrayAllegati = $param['ALLEGATI'];
        if (!$arrayAllegati){
            Out::msgError("Errore", "Per effettuare l'invio del messaggio occorre OBBLIGATORIAMENTE allegare almeno un allegato");
            return false;
        }

        // Carico sul vettore $arrayAllegCart tutti gli allegati arrivati con la presentazione istanza
        $arrayAllegCart = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);  
        
        $standard = '';
        if ($arrayAllegCart){
            foreach($arrayAllegCart as $allegato){
                // Il campo USOMODELLO contiene la stringa "STANDARD"
                if (strpos($allegato['USOMODELLO'], 'STANDARD') !== false){
                    $standard = $allegato['USOMODELLO'];
                    break;
                }
            }
        }

        if (!$standard){
            Out::msgError("Errore", "Non è stato ritrovato il tipo di STANDARD della pratica CART");
            return false;
        }

        
        
        // Si scorrono tutti i destinatari e si invia il messaggio
        $arrayEnti = $param['ENTI'];
        foreach($arrayEnti as $ente){

            //TODO: Costruire $xmlInvio;
            $xmlInvio = $this->getXmlIntegrazione($param, $cart_stimolo_rec, $arrayAllegati, $datetime, $standard);

//            Out::msgInfo("Xml Invio", htmlspecialchars($xmlInvio));
//            return false;
            
            $retCall = $cartClient->ws_fruizione($xmlInvio, $tipoStimolo, $arrayAllegati);
            if (!$retCall) {
                Out::msgStop("Errore Fruizione", $cartClient->getFault() . " " . $cartClient->getError);
                return false;
            }
            $result = $cartClient->getResult();

            // Rilegge eventuali allegati di risposta. PEr questo messaggio non ci sono
            $allegati = $cartClient->getAttachments();
            if ($allegati){
    //            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
    //            Out::msgInfo("Allegati", print_r($allegati, true));
            }

            if (!$this->salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                return false;
            }

            //Out::msgInfo("Risultato Invio " . $tipoStimolo, print_r($cartClient->getResult(), true));
           //Out::msgInfo("Attachment", print_r($allegati, true));

        }
        
        return true;
        
    }

    private function getXmlIntegrazione($param, $cart_stimolo_rec, $arrayAllegati, $datetime, $standard){
        if (!$datetime){
            $datetime = new DateTime();
        }

	$suffissoIntegra = " - integrazione";
        if ($param['TIPOSTIMOLO'] == 'richiestaConformazioni'){
            $suffissoIntegra = " - conformazione";
        }
        
        
        $allegati = $arrayAllegati;
        $allegatoPrinc = $arrayAllegati[0];
        unset($allegati[0]);
        
        $identificativoModulo = $standard . $suffissoIntegra;  //"STANDARD 2 - integrazione"
        
//        Out::msgInfo("Data ", print_r($datetime, true));
//        Out::msgInfo("DataCART ", $this->praLibCart->getDatetimeNow($datetime));
//        Out::msgInfo("Data ", print_r(date_format($datetime, 'd/m/y'), true));
        
        $xmlInvio = '<proc:stimolo>
		<proc:mittente> 
                    <proc:tipologia>SUAP</proc:tipologia>
                    <proc:ente>' . $cart_stimolo_rec['MITTENTE_ENTE']  . '</proc:ente> '
                . '</proc:mittente>'
                . '<proc:data>' . $this->praLibCart->getDatetimeNow($datetime)  . '</proc:data>'
                . '<proc:idPratica>' . $cart_stimolo_rec['IDPRATICA']  . '</proc:idPratica> '
                . '<proc:attributiSpecifici>
			<proc:' .  $param['TIPOSTIMOLO'] . '>
                            <proc:rispostaAttesaIn>P' . $param['GGMAXINTEGRA'] .'D</proc:rispostaAttesaIn>
                            <proc:moduloRichiesto enteRichiedente="SUAP" identificativoModulo="' . $identificativoModulo . '"/>';
        
        $xmlInvio = $xmlInvio . $this->getXmlComunicazioneFormale($param, $allegatoPrinc, $allegati);

        
        if ($param['TIPOSTIMOLO'] == 'richiestaIntegrazioni'){
            $xmlInvio = $xmlInvio . '<proc:proposta>false</proc:proposta>';
        }
        
        $xmlInvio = $xmlInvio . '</proc:' .  $param['TIPOSTIMOLO'] . '>
	</proc:attributiSpecifici> 
        </proc:stimolo>';
        

        return $xmlInvio;
    }
    
    private function getXmlComunicazioneFormale($param, $allegatoPrinc, $allegati){

        $xmlInvio =  '<proc:comunicazione-formale>
                                <proc:messaggio>' .  $param['MSGINTEGRA'] . '</proc:messaggio>
                                <proc:comunicazionePDF>
                                <proc:contentID>' . $allegatoPrinc['CID'] . '</proc:contentID>'
                                . '<proc:contentType>' . $allegatoPrinc['CONTENTTYPE'] . '</proc:contentType>'
                                . '<proc:nomefileOriginale>'  . $allegatoPrinc['FILENAME'] . '</proc:nomefileOriginale> 
                                </proc:comunicazionePDF>';

        
        //Riporta gli Allegati
        foreach ($allegati as $allegato){
            
            $xmlInvio = $xmlInvio . '<proc:comunicazioniSecondarie>'
                    . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>'
                    . '<proc:contentType>' . $allegato['CONTENTTYPE'] . '</proc:contentType>'
                    . '<proc:nomefileOriginale>'  . $allegato['FILENAME'] . '</proc:nomefileOriginale> '
                    . '</proc:comunicazioniSecondarie>' ;            
            
        }

        $xmlInvio = $xmlInvio . '</proc:comunicazione-formale>';

        return $xmlInvio;
        
    }
    
    private function invioDiniego($cartClient, $param, $cart_stimolo_rec) {
        $datetime = new DateTime();
        $idCart = '';
        
        $tipoStimolo = $param['OPERATION'];

        $arrayAllegati = $param['ALLEGATI'];
        if (!$arrayAllegati){
            Out::msgError("Errore", "Per effettuare l'invio del messaggio occorre OBBLIGATORIAMENTE allegare almeno un allegato");
            return false;
        }
        
        
        // Si scorrono tutti i destinatari e si invia il messaggio
        $arrayEnti = $param['ENTI'];
        foreach($arrayEnti as $ente){

            //TODO: Costruire $xmlInvio;
            $xmlInvio = $this->getXmlDiniego($param, $cart_stimolo_rec, $arrayAllegati, $datetime);

//            Out::msgInfo("Xml Invio", htmlspecialchars($xmlInvio));
//            return false;
            
            $retCall = $cartClient->ws_fruizione($xmlInvio, $tipoStimolo, $arrayAllegati);
            if (!$retCall) {
                Out::msgStop("Errore Fruizione", $cartClient->getFault() . " " . $cartClient->getError);
                return false;
            }
            $result = $cartClient->getResult();

            // Rilegge eventuali allegati di risposta. PEr questo messaggio non ci sono
            $allegati = $cartClient->getAttachments();
            if ($allegati){
    //            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
    //            Out::msgInfo("Allegati", print_r($allegati, true));
            }

            if (!$this->salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                return false;
            }
            
        }
        
        return true;
        
    }

    private function getXmlDiniego($param, $cart_stimolo_rec, $arrayAllegati, $datetime){
        if (!$datetime){
            $datetime = new DateTime();
        }

        
        $allegati = $arrayAllegati;
        $allegatoPrinc = $arrayAllegati[0];
        unset($allegati[0]);
        
        
//        Out::msgInfo("Data ", print_r($datetime, true));
//        Out::msgInfo("DataCART ", $this->praLibCart->getDatetimeNow($datetime));
//        Out::msgInfo("Data ", print_r(date_format($datetime, 'd/m/y'), true));
        
        $xmlInvio = '<proc:stimolo>
		<proc:mittente> 
                    <proc:tipologia>SUAP</proc:tipologia>
                    <proc:ente>' . $cart_stimolo_rec['MITTENTE_ENTE']  . '</proc:ente> '
                . '</proc:mittente>'
                . '<proc:data>' . $this->praLibCart->getDatetimeNow($datetime)  . '</proc:data>'
                . '<proc:idPratica>' . $cart_stimolo_rec['IDPRATICA']  . '</proc:idPratica> '
                . '<proc:attributiSpecifici>
			<proc:' .  $param['TIPOSTIMOLO'] . '>';
        
        $xmlInvio = $xmlInvio . $this->getXmlComunicazioneFormale($param, $allegatoPrinc, $allegati);

        
        $xmlInvio = $xmlInvio . '</proc:' .  $param['TIPOSTIMOLO'] . '>
	</proc:attributiSpecifici> 
        </proc:stimolo>';
        

        return $xmlInvio;
    }


    private function invioInoltro($cartClient, $param, $cart_stimolo_rec) {
        $datetime = new DateTime();
        $idCart = '';

        $tipoStimolo = $param['OPERATION'];

//        $arrayAllegati = $param['ALLEGATI'];
//        if (!$arrayAllegati){
//            Out::msgError("Errore", "Per effettuare l'invio del messaggio occorre OBBLIGATORIAMENTE allegare almeno un allegato");
//            return false;
//        }

                // Carico sul vettore $arrayAllegCart tutti gli allegati arrivati con la presentazione istanza
        $arrayAllegCart = $this->praLibCart->getCart_stimoloFile($cart_stimolo_rec['IDMESSAGGIO'], 'idMessaggioCart', true);  
        
        $standard = '';
        if ($arrayAllegCart){
            foreach($arrayAllegCart as $allegato){
                // Il campo USOMODELLO contiene la stringa "STANDARD"
                if (strpos($allegato['USOMODELLO'], 'STANDARD') !== false){
                    $standard = $allegato['USOMODELLO'];
                    break;
                }
            }
        }

        if (!$standard){
            Out::msgError("Errore", "Non è stato ritrovato il tipo di STANDARD della pratica CART");
            return false;
        }

        
        // Si scorrono tutti i destinatari e si invia il messaggio
        $arrayEnti = $param['ENTI'];
        foreach($arrayEnti as $ente){

            //TODO: Costruire $xmlInvio;
            $xmlInvio = $this->getXmlInoltro($param, $cart_stimolo_rec, $arrayAllegati, $datetime, $ente, $standard);
            
            $retCall = $cartClient->ws_fruizione($xmlInvio, $tipoStimolo, $arrayAllegati);
            //$retCall = $cartClient->ws_fruizioneTest($xmlInvio, $tipoStimolo); 
            if (!$retCall) {
                Out::msgStop("Errore Fruizione", $cartClient->getFault() . " " . $cartClient->getError);
                return false;
            }
            $result = $cartClient->getResult();

//            Out::msgInfo("Risultato Invio " . $tipoStimolo, print_r($cartClient->getResult(), true));
            
            
            // Rilegge eventuali allegati di risposta. PEr questo messaggio non ci sono
            $allegati = $cartClient->getAttachments();
            if ($allegati){
    //            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
    //            Out::msgInfo("Allegati", print_r($allegati, true));
            }

            
            if (!$this->salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                return false;
            }

           //Out::msgInfo("Attachment", print_r($allegati, true));

        }
        
        return true;
        
    }

    private function getXmlInoltro($param, $cart_stimolo_rec, $arrayAllegati, $datetime, $ente, $standard){
        if (!$datetime){
            $datetime = new DateTime();
        }

        
        $allegati = $arrayAllegati;
        $allegatoPrinc = $arrayAllegati[0];
        unset($allegati[0]);
        
        
//        Out::msgInfo("Data ", print_r($datetime, true));
//        Out::msgInfo("DataCART ", $this->praLibCart->getDatetimeNow($datetime));
//        Out::msgInfo("Data ", print_r(date_format($datetime, 'd/m/y'), true));
        
        $xmlInvio = '<proc:stimolo>
		<proc:mittente> 
                    <proc:tipologia>SUAP</proc:tipologia>
                    <proc:ente>' . $cart_stimolo_rec['MITTENTE_ENTE']  . '</proc:ente> '
                . '</proc:mittente>'
                . '<proc:data>' . $this->praLibCart->getDatetimeNow($datetime)  . '</proc:data>'
                . '<proc:idPratica>' . $cart_stimolo_rec['IDPRATICA']  . '</proc:idPratica> '
                . '<proc:attributiSpecifici>
			<proc:' .  $param['TIPOSTIMOLO'] . ' destinatario="' . $ente['DESTCART'] . '">';
                            
        $xmlInvio = $xmlInvio . $this->getXmlModulo($standard, $cart_stimolo_rec['IDMESSAGGIO']);

        if ($arrayAllegati){
            //Riporta gli Allegati
            foreach ($arrayAllegati as $allegato){

                $xmlInvio = $xmlInvio . '<proc:allegatoAggiuntivo>';
                
                if ($allegato['HASFILE']){
                    $xmlInvio = $xmlInvio . '<proc:hashedFile> '
                        . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>'
                        . '<proc:hash>' . $allegato['HASHFILE'] . '</proc:hash>'
                    . '</proc:hashedFile>';
                }
                else {
                    $xmlInvio = $xmlInvio . '<proc:contentID>' . $allegato['CID'] . '</proc:contentID>';
                }
                
                $xmlInvio = $xmlInvio . '<proc:contentType>' . $allegato['CONTENTTYPE'] . '</proc:contentType>'
                        . '<proc:nomefileOriginale>'  . $allegato['FILENAME'] . '</proc:nomefileOriginale> '
                        . '</proc:allegatoAggiuntivo>' ;            

            }
        }
        
        
        $xmlInvio = $xmlInvio . '</proc:' .  $param['TIPOSTIMOLO'] . '>
	</proc:attributiSpecifici> 
        </proc:stimolo>';

        
        return $xmlInvio;
    }
    
    private function salvaInvio($result, $param, $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec){

            if ($result['esito'] == 'OK'){
                $idCart = $result[idMessaggio];
                //TODO: si salva la spedizone fatta in CART_INVIO e PRAMITDEST con IDmessaggio = $result[idMessaggio]
                if (!$this->salvaCartInvio($idCart, $param['TIPOSTIMOLO'], $arrayAllegati, $ente, $datetime, $xmlInvio, $cart_stimolo_rec)){
                    Out::msgStop("ERRORE", "Salvataggio in CART_INVIO con IDMESSAGGIO = " . $idCart . " fallito.");
                    return false;
                }
                
                if ($idCart){
                    // Aggiorna PRAMITDEST  
                    $this->AggiornaPartenza($ente, $param, $idCart);
                }
                
            }
            else {
                
                $errore = $result['esito'] . " - " . $result['!msgErrore'];
                
                Out::msgStop("ERRORE", "La comunicazione con il CART ha generato il seguente errore <br> " . $errore);
                return false;
            }

            return true;
        
    }

    private function controlloEstensioneAllegatoXCart($filename){
        $estOk = false;
        
        $length = strlen($filename);
        
        if (strcasecmp(substr($filename, $length - 3), "pdf") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 3), "xml") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 3), "dwf") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 3), "svg") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 3), "jpg") == 0 ) {
            $estOk = true;
        }
        
        
        if (strcasecmp(substr($filename, $length - 7), "pdf.p7m") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 7), "dwf.p7m") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 7), "svg.p7m") == 0 ) {
            $estOk = true;
        }
        if (strcasecmp(substr($filename, $length - 7), "jpg.p7m") == 0 ) {
            $estOk = true;
        }

  
        return $estOk;
    }

//    public function getAllegatoFirmato($prafolist_rec, $rowidAlle) {
//       
//        return $this->getAllegato($prafolist_rec, $rowidAlle);
//        
//    }
}
