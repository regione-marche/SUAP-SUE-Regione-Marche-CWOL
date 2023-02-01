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
 * @version    23.11.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPJiride/itaWsPostaClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientMail.class.php';

class proJirideMail extends proWsClientMail {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Iride
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($itaWsPostaClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILENDPOINT', false);
        $itaWsPostaClient->setWebservices_uri($uri['CONFIG']);

        $wsdl = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILWSDL', false);
        $itaWsPostaClient->setWebservices_wsdl($wsdl['CONFIG']);

        $itaWsPostaClient->setNameSpaces();

        $utente = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILUTENTE', false);
        $itaWsPostaClient->setUtente($utente['CONFIG']);

        $ruolo = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILRUOLO', false);
        $itaWsPostaClient->setRuolo($ruolo['CONFIG']);

        $invioInterop = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILINVIOINTEROP', false);
        $itaWsPostaClient->setInvioInteroperabile($invioInterop['CONFIG']);

        $CodiceAmministrazione = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILCODICEAMM', false);
        $itaWsPostaClient->setCodiceAmministrazione($CodiceAmministrazione['CONFIG']);

        $CodiceAOO = $devLib->getEnv_config('JIRIDEWSMAIL', 'codice', 'WSJIRIDEMAILCODICEAOO', false);
        $itaWsPostaClient->setCodiceAOO($CodiceAOO['CONFIG']);
    }

    function InvioMail($elementi) {
        $itaWsPostaClient = new itaWsPostaClient();
        $this->setClientConfig($itaWsPostaClient);
        $param = $mailDest = array();
        $param['annoProt'] = $elementi['Anno'];
        $param['numProt'] = $elementi['proNum'];
        $param['oggettoMail'] = $elementi['Oggetto'];
        $param['testoMail'] = $elementi['Testo'];
        $param['mittenteMail'] = $elementi['Mittente'];
        foreach ($elementi['Destinatari'] as $destinatario) {
            $mailDest[] = $destinatario['MAIL'];
        }
        $param['destinatariMail'] = $mailDest;
        $ret = $itaWsPostaClient->ws_InviaMail($param);

        if (!$ret) {
            if ($itaWsPostaClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . $itaWsPostaClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($itaWsPostaClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . $itaWsPostaClient->getError();
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsPostaClient->getResult();
        //
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
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

        //gestione del messaggio d'errore
        if ($arrayXml['codice'][0]["@textNode"] == "-1") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di invio mail: <br>" . $arrayXml['descrizione'][0]["@textNode"] . "";
            $ritorno["RetValue"] = false;
        } else {
            $message = "La mail è stata inviata correttamente ai seguenti destinatari:<br><br>";
            foreach ($elementi['Destinatari'] as $destinatario) {
                $message .= "<b>" . $destinatario['MAIL'] . "</b><br>";
            }
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = $message;
            $ritorno["RetValue"] = true;
            $ritorno["idMail"] = $arrayXml['descrizione'][0]["@textNode"];
        }
        return $ritorno;
    }

    function VerificaInvio($elementi) {
        $itaWsPostaClient = new itaWsPostaClient();
        $this->setClientConfig($itaWsPostaClient);
        $param = array();
        $param['annoProt'] = $elementi['Anno'];
        $param['numProt'] = $elementi['proNum'];
        $param['docId'] = $elementi['DocNumber'];
        //
        $ret = $itaWsPostaClient->ws_VerificaInvio($param);
        if (!$ret) {
            if ($itaWsPostaClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . $itaWsPostaClient->getFault();
                $ritorno["RetValue"] = false;
            } elseif ($itaWsPostaClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . $itaWsPostaClient->getError();
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaWsPostaClient->getResult();
        //
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
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

        if (!$arrayXml['inviato'][0]['@textNode']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Indirizzo Casella di posta vuoto.<br>Invio non effettuato o ricevute non scaricate";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $message = "Verifica effettuata con successo";
        $ritorno["Status"] = "0";
        $ritorno["RetValue"] = true;
        $ritorno["Message"] = $message;
        $ritorno["account"] = $arrayXml['inviato'][0]['@textNode'];
        $ritorno["numAccettazioni"] = $arrayXml['numAccettazioni'][0]['@textNode'];
        $ritorno["numConsegne"] = $arrayXml['numConsegne'][0]['@textNode'];
        $ritorno["arrAccettazioni"] = $arrayXml['accettazioni'][0]['idRepAccettazione'];
        $ritorno["arrConsegne"] = $arrayXml['consegne'][0]['idRepConsegna'];
        return $ritorno;
    }

    function GetHtmlVerificaInvio($valore) {
        $html = "";
        $html .= "<span style=\"text-decoration:underline;\"><b>Casella:</b></span> " . $valore['account'] . "<br><br>";
        $html .= "<span style=\"text-decoration:underline;\"><b>Accettazioni:</b></span> " . $valore['numAccettazioni'];
        $html .= "<table id=\"tableVerificaInvio\">";
        foreach ($valore['arrAccettazioni'] as $accettazione) {
            $html .= "<tr>";
            $html .= "<td>ID Messaggio:</td>";
            $html .= "<td> " . $accettazione['@textNode'] . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        $html .= "<br>";
        $html .= "<span style=\"text-decoration:underline;\"><b>Consegne:</b></span> " . $valore['numConsegne'];
        $html .= "<table>";
        foreach ($valore['arrConsegne'] as $consegna) {
            $html .= "<tr>";
            $html .= "<td>ID Messaggio:</td>";
            $html .= "<td> " . $consegna['@textNode'] . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_JIRIDE;
    }

}

?>