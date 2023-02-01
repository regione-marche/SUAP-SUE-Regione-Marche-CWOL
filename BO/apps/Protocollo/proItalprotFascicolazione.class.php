<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version     04.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotFascicoliClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFascicolazione.class.php';

class proItalprotFascicolazione extends proWsClientFascicolazione {

    /**
     * Libreria di funzioni Generiche e Utility per Fascicolazione con Protocollo italsoft
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($italprotWsClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSFASCICOLOENDPOINT', false);
        $italprotWsClient->setWebservices_uri($uri['CONFIG']);

        $wsdl = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSFASCICOLOWSDL', false);
        $italprotWsClient->setWebservices_wsdl($wsdl['CONFIG']);

        $ditta = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSDOMAINCODE', false);
        $italprotWsClient->setDomain($ditta['CONFIG']);

        $utente = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSUSER', false);
        $italprotWsClient->setUsername($utente['CONFIG']);

        $ruolo = $devLib->getEnv_config('ITALSOFTPROTWS', 'codice', 'PROWSPASSWD', false);
        $italprotWsClient->setpassword($ruolo['CONFIG']);
        // set Max execution serve?
    }

    public function CreaFascicolo($elementi) {
        $itaItalprotFascicoliClient = new itaItalprotFascicoliClient();
        $this->setClientConfig($itaItalprotFascicoliClient);

        /*
         * Reperisco il token
         */
        $param = array();
        $param['userName'] = $itaItalprotFascicoliClient->getUsername();
        $param['userPassword'] = $itaItalprotFascicoliClient->getpassword();
        $param['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $itaItalprotFascicoliClient->getResult();


        /*
         * Definizione parametri per creazione fascicolo.
         */
        if ($elementi['dati']['InCaricoA']) {
//            $arrInCarico = explode(".", $elementi['dati']['InCaricoA']);
//            $codiceUfficio = str_pad($arrInCarico[0], 4, "0", STR_PAD_LEFT);
//            $codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);

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

        }

        $param = array();
        $datiFascicolo = array();
        $datiFascicolo['ufficioOperatore'] = ""; //$elementi['dati']['Fascicolazione']['ufficioOperatore'];
        $datiFascicolo['titolario'] = $elementi['dati']['Classificazione'];
        $datiFascicolo['descrizione'] = $elementi['dati']['Fascicolazione']['Oggetto'];
        $datiFascicolo['natura'] = "2"; //$elementi['Natura'];
        $datiFascicolo['responsabile'] = $codiceDest;
        $datiFascicolo['ufficioResponsabile'] = $codiceUfficio;
        $datiFascicolo['codiceSerie'] = ""; //$elementi['CodiceSerie'];
        $datiFascicolo['progressivoSerie'] = $elementi['dati']['NumeroFascElet']; //$elementi['ProgSerie'];
        $param['token'] = $token;
        $param['datiFascicolo'] = $datiFascicolo;

        //
        $ret = $itaItalprotFascicoliClient->ws_CreaFascicolo($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di creazione nuovo fascicolo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaItalprotFascicoliClient->getResult();
        /* Verifico se il risultato è un errore. */
        $messageResult = $risultato['messageResult'];
        if ($messageResult['tipoRisultato'] == 'Error') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $messageResult['descrizione'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /*
         * Gestione risultato di ritorno.
         */
        $ritorno["Status"] = "0";
        $ritorno["Message"] = $risultato['messageResult']['descrizione'];
        $ritorno["codiceFascicolo"] = $risultato['datiFascicolo']['codiceFascicolo'];
        $ritorno["datiFascicolo"] = $risultato['datiFascicolo'];
        $ritorno["RetValue"] = true;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }

        return $ritorno;
    }

    //function FascicolaDocumento($elementi, $TipoProt = "A") {
    function FascicolaDocumento($elementi) {
        $itaItalprotFascicoliClient = new itaItalprotFascicoliClient();
        $this->setClientConfig($itaItalprotFascicoliClient);

        /*
         * Reperisco il token
         */
        $param = array();
        $param['userName'] = $itaItalprotFascicoliClient->getUsername();
        $param['userPassword'] = $itaItalprotFascicoliClient->getpassword();
        $param['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $itaItalprotFascicoliClient->getResult();
        //
        $param = array();
        $param['token'] = $token;
        $param['annoProtocollo'] = $elementi['dati']['Fascicolazione']['Anno'];
        $param['numeroProtocollo'] = $elementi['dati']['Fascicolazione']['Numero'];
        $param['tipoProtocollo'] = $elementi['tipo'];
        //$param['tipoProtocollo'] = $TipoProt;
        $param['codiceFascicolo'] = $elementi['dati']['Fascicolazione']['CodiceFascicolo'];
        $param['codiceSottoFascicolo'] = $elementi['CodiceSottofascicolo'];
        //
        $ret = $itaItalprotFascicoliClient->ws_FascicolaProtocollo($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di fascicolazione del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di fascicolazione del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaItalprotFascicoliClient->getResult();
        /* Verifico se il risultato è un errore. */
        $messageResult = $risultato['messageResult'];
        if ($messageResult['tipoRisultato'] == 'Error') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $messageResult['descrizione'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $messageResult['descrizione'];
        $ritorno["DatiFascicolazione"] = $risultato['retDatiFascicolo'];
        $ritorno["RetValue"] = true; // Tornare Fascicolo e sottofascicolo?

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }

        return $ritorno;
    }

    function GetFascicoliProtocollo($elementi) {
        $itaItalprotFascicoliClient = new itaItalprotFascicoliClient();
        $this->setClientConfig($itaItalprotFascicoliClient);
        //
        /*
         * Reperisco il token
         */
        $param = array();
        $param['userName'] = $itaItalprotFascicoliClient->getUsername();
        $param['userPassword'] = $itaItalprotFascicoliClient->getpassword();
        $param['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $itaItalprotFascicoliClient->getResult();
        //

        $param = array();
        $param['token'] = $token;
        $param['annoProtocollo'] = $elementi['dati']['AnnoAntecedente'];
        $param['numeroProtocollo'] = $elementi['dati']['NumeroAntecedente'];
        $param['tipoProtocollo'] = $elementi['dati']['TipoAntecedente'];
        //
        $ret = $itaItalprotFascicoliClient->ws_GetFascicoliProtocollo($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase estrazione fascicoli del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase estrazione fascicoli del protocollo:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaItalprotFascicoliClient->getResult();
        /* Verifico se il risultato è un errore. */
        $messageResult = $risultato['messageResult'];
        if ($messageResult['tipoRisultato'] == 'Error') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $messageResult['descrizione'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $ritorno["Status"] = "0";
        $ritorno["Message"] = 'Elenco dei fascicoli in cui si trova il protocollo.';
        $ritorno["ElencoFascicoli"] = $risultato['ElencoFascicoli'];
        $ritorno["RetValue"] = true;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }

        return $ritorno;
    }

    function GetElencoFascicoli($elementi) {
        $itaItalprotFascicoliClient = new itaItalprotFascicoliClient();
        $this->setClientConfig($itaItalprotFascicoliClient);

        /*
         * Reperisco il token
         */
        $param = array();
        $param['userName'] = $itaItalprotFascicoliClient->getUsername();
        $param['userPassword'] = $itaItalprotFascicoliClient->getpassword();
        $param['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $itaItalprotFascicoliClient->getResult();

        /*
         * reperito il token, chiamo il metodo
         */
        $param = array();
        $param['token'] = $token;

        /*
         * Costruisco l'array dei parametri di ricerca
         */
        $arrayParamRicerca = array();
        foreach ($elementi as $key => $elemento) {
            $arrayParamRicerca['parametroRicerca'][$key] = $elemento;
        }
        $param['arrayParamRicerca'] = $arrayParamRicerca;
        //
        $ret = $itaItalprotFascicoliClient->ws_GetElencoFascicoli($param);
        if (!$ret) {
            if ($itaItalprotFascicoliClient->getFault()) {
                $msg = $itaItalprotFascicoliClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase estrazione elenco fascicoli:<br>$msg";
                $ritorno["RetValue"] = false;
            } elseif ($itaItalprotFascicoliClient->getError()) {
                $msg = $itaItalprotFascicoliClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase estrazione elenco fascicoli:<br>$msg";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $itaItalprotFascicoliClient->getResult();
        //
        $ritorno["Status"] = "0";
        $ritorno["Message"] = 'Elenco dei fascicoli in cui si trova il protocollo.';
        $ritorno["ElencoFascicoli"] = $risultato['ElencoFascicoli'];
        $ritorno["RetValue"] = true;

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $itaItalprotFascicoliClient->getDomain();
        $ret = $itaItalprotFascicoliClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }
        return $ritorno;
    }

    /**
     * 
     * @param string $codiceFascicolo
     * @return array $risultato {
     *     [description]
     *
     *     @option string  "Status" [stringa che vale 0 o 1 in base all'esito positivo o negativo dell'operazione]
     *     @option string  "Message" [messaggio esito operazione]
     *     @option boolean "RetValue" [flag che vale true o false in base all'esito positivo o negativo dell'operazione]
     *     @option boolean "fascicola" [flag che indica se fascicolare oppure no]
     *     @option string  "DataChiusuraFascicolo" [data chiusura del fascicolo]
     * }
     */
    function checkFascicolo($codiceFascicolo) {
        /*
         * Verifica data chiusura fascicolo
         */
        $paramElenco = array(
            array(
                "chiave" => "CODICEFASCICOLO",
                "valore" => $codiceFascicolo,
            ),
        );
        $risultatoElenco = $this->GetElencoFascicoli($paramElenco);
        $risultatoElenco['fascicola'] = false;
        if ($risultatoElenco['Status'] == "0") {
            if ($risultatoElenco['ElencoFascicoli'][0]['dataChiusuraFascicolo'] == "") {
                $risultatoElenco['fascicola'] = true;
            } else {
                $risultatoElenco['Message'] = "Fascicolazione non avvenuta. Il fascicolo n. $codiceFascicolo risulta chiuso in data " . $risultatoElenco['ElencoFascicoli'][0]['dataChiusuraFascicolo'];
            }
        }
        return $risultatoElenco;
    }

    /**
     * 
     * @param array $elementi array contenente tutti i dati di protocollazione
     * @return string codice fascicolo
     */
    function getCodiceFascicolo($elementi) {
        $risultato = $this->GetFascicoliProtocollo($elementi);
        if ($risultato['Status'] == "-1") {
            return $risultato;
        }
        /*
         * Fascicolo il documento (Inserisco il protocollo nel fascicolo).
         * Se c'è più di un fascicolo, prendo il primo con lo stesso titolario
         */
        $codFascicolo = $risultato['ElencoFascicoli'][0]['codiceFascicolo'];
        if (count($risultato['ElencoFascicoli']) > 1) {
            $arrClassificazione = explode(".", $elementi['dati']['Classificazione']);
            $classEstesa = str_pad($arrClassificazione[0], 4, "0", STR_PAD_LEFT) . str_pad($arrClassificazione[1], 4, "0", STR_PAD_LEFT);
            foreach ($risultato['ElencoFascicoli'] as $fascicolo) {
                if ($classEstesa == $fascicolo['titolario']) {
                    $codFascicolo = $fascicolo['codiceFascicolo'];
                    break;
                }
            }
        }


        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Lettura codice fascicolo terminata con successo";
        $ritorno["RetValue"] = true;
        $ritorno["CodiceFascicolo"] = $codFascicolo;
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLASS_PARAM_PROTOCOLLO_ITALPROT;
    }

}
