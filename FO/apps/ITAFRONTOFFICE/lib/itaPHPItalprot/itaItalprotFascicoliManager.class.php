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
require_once(ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotFascicoliClient.class.php');

class itaItalprotFascicoliManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaItalprotFascicoliManager();
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
     * Libreria di funzioni Generiche e Utility per Fascicolazione con Protocollo italsoft
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($italsoftClient) {
        $italsoftClient->setWebservices_uri($this->clientParam['WSFASCICOLOENDPOINT']);
        $italsoftClient->setWebservices_wsdl($this->clientParam['WSFASCICOLOWSDL']);
        $italsoftClient->setDomain($this->clientParam['WSITALSOFTDOMAIN']);
        $italsoftClient->setUsername($this->clientParam['WSITALSOFTUSERNAME']);
        $italsoftClient->setpassword($this->clientParam['WSITALSOFTPASSWORD']);
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
            $arrInCarico = explode(".", $elementi['dati']['InCaricoA']);
            $codiceUfficio = str_pad($arrInCarico[0], 4, "0", STR_PAD_LEFT);
            $codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
        }

        $param = array();
        $datiFascicolo = array();
        $datiFascicolo['ufficioOperatore'] = ""; //$elementi['dati']['Fascicolazione']['ufficioOperatore'];
        $datiFascicolo['titolario'] = $elementi['dati']['Classificazione'];
        $datiFascicolo['descrizione'] = htmlspecialchars(utf8_encode($elementi['dati']['Fascicolazione']['Oggetto']), ENT_COMPAT, 'UTF-8');
        $datiFascicolo['natura'] = "2"; //$elementi['Natura'];
        $datiFascicolo['responsabile'] = $codiceDest;
        $datiFascicolo['ufficioResponsabile'] = $codiceUfficio;
        $datiFascicolo['codiceSerie'] = ""; //$elementi['CodiceSerie'];
        $datiFascicolo['progressivoSerie'] = ""; //$elementi['dati']['NumRichiesta'];
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

    function FascicolaProtocollo($elementi, $TipoProt = "A") {
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
        $param['tipoProtocollo'] = $TipoProt;
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
        $param['annoProtocollo'] = $elementi['dati']['annoProtocolloAntecedente'];
        $param['numeroProtocollo'] = $elementi['dati']['numeroProtocolloAntecedente'];
        $param['tipoProtocollo'] = "A";
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

}

?>