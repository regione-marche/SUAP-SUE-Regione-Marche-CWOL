<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    04.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPAdrier/itaAdrierClient.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class itaAdrierManager {

    private $errMessage;

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    /**
     * funzione standard per la ricerca dei dati minimi della ditta a partire dal CF
     * 
     * @param string $cf codice fiscale
     * @return array $ditta (Array normalizzato)
     */
    public function RicercaImpresePerCodiceFiscale($cf) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['codice_fiscale'] = $cf;
        $ret = $WSClient->ws_RicercaImpresePerCodiceFiscale($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("CF non trovato");
            return false;
        }

        return $this->NormalizzaArrayDitta($ret);
    }

    /**
     * funzione standard per la ricerca dei dati minimi della ditta a partire dal CF
     * cerca solo le ditte non cessate
     * 
     * @param string $cf codice fiscale
     * @return array $ditta (Array normalizzato)
     */
    public function RicercaImpreseNonCessatePerCodiceFiscale($cf) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['codice_fiscale'] = $cf;
        $ret = $WSClient->ws_RicercaImpreseNonCessatePerCodiceFiscale($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("CF non trovato");
            return false;
        }

        return $this->NormalizzaArrayDitta($ret);
    }

    /**
     * funzione standard per la ricerca dei dati minimi della ditta a partire dalla denominazione
     * 
     * @param string $cf codice fiscale
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta
     */
    public function RicercaImpresePerDenominazione($cf) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['codice_fiscale'] = $cf;
        $ret = $WSClient->ws_RicercaImpresePerDenominazione($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("CF non trovato");
            return false;
        }

        return $ret;
    }

    /**
     * funzione standard per la ricerca dei dati minimi della ditta a partire dalla denominazione
     * cerca solo le ditte non cessate
     * 
     * @param string $cf codice fiscale
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta
     */
    public function RicercaImpreseNonCessatePerDenominazione($cf) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['codice_fiscale'] = $cf;
        $ret = $WSClient->ws_RicercaImpreseNonCessatePerDenominazione($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("CF non trovato");
            return false;
        }

        return $ret;
    }

    /**
     * funzione per la ricerca dei dati minimi della ditta a partire dal n. REA e provincia CCIAA
     * cerca solo le ditte non cessate
     * 
     * @param string $sgl_prv_sede provincia CCIAA
     * @param string $n_rea_sede n. REA
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta, xml è esattamente quello restituito da PortaCAD, non rielaborato quindi da ADRI Gate
     */
    public function RicercaImpreseNRea($sgl_prv_sede, $n_rea_sede) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['sgl_prv_sede'] = $sgl_prv_sede;
        $param['n_rea_sede'] = $n_rea_sede;
        $ret = $WSClient->RicercaImpreseNRea($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $ret;
    }

    /**
     * funzione per la ricerca dei dati dettagliati della ditta a partire dal n. REA e provincia CCIAA
     * 
     * @param string $sgl_prv_sede provincia CCIAA
     * @param string $n_rea_sede n. REA
     * @return array $ditta (Array normalizzato)
     */
    public function DettaglioCompletoImpresa($sgl_prv_sede, $n_rea_sede) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['sgl_prv_sede'] = $sgl_prv_sede;
        $param['n_rea_sede'] = $n_rea_sede;
        $ret = $WSClient->ws_DettaglioCompletoImpresa($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $this->NormalizzaArrayDitta($ret);
    }

    /**
     * funzione per la ricerca dei dati dettagliati della ditta a partire dal n. REA e provincia CCIAA
     * 
     * @param string $sgl_prv_sede provincia CCIAA
     * @param string $n_rea_sede n. REA
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta, xml è esattamente quello restituito da PortaCAD, non rielaborato quindi da ADRI Gate
     */
    public function DettaglioImpresaNRea($sgl_prv_sede, $n_rea_sede) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['sgl_prv_sede'] = $sgl_prv_sede;
        $param['n_rea_sede'] = $n_rea_sede;
        $ret = $WSClient->DettaglioImpresaNRea($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $ret;
    }

    /**
     * funzione per la ricerca dei dati minimi della ditta a partire dal n. REA e provincia CCIAA
     * non comprende i dati relativi agli elenchi di persone e i dati delle localizzazioni diverse dalla sede
     * 
     * @param string $sgl_prv_sede provincia CCIAA
     * @param string $n_rea_sede n. REA
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta
     */
    public function DettaglioRidottoImpresa($sgl_prv_sede, $n_rea_sede) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['sgl_prv_sede'] = $sgl_prv_sede;
        $param['n_rea_sede'] = $n_rea_sede;
        $ret = $WSClient->DettaglioRidottoImpresa($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $ret;
    }

    /**
     * Consente di ottenere i dati delle persone aventi delle cariche nella sede dell'impresa specificando il numero REA e la sigla della provincia
     * 
     * @param string $fk_pri_cciaa_regz provincia CCIAA
     * @param string $fk_pri_n_rea n. REA
     * @return array $ret il formato dei dati è un array elaborato con itaXML dall'xml di risposta
     */
    public function DettaglioPersoneConCarica($fk_pri_cciaa_regz, $fk_pri_n_rea) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);
        $param = array();
        $param['fk_pri_cciaa_regz'] = $fk_pri_cciaa_regz;
        $param['fk_pri_n_rea'] = $fk_pri_n_rea;
        $ret = $WSClient->DettaglioPersoneConCarica($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $ret;
    }

    /**
     * funzione per la ricerca dei dati dettagliati della ditta a partire dal codice fiscale.
     * Viene prima eseguita la chiamata a RicercaImpresePerCodiceFiscale(), recuperati i valori REA e fatta la chiamata a DettaglioCompletoImpresa()
     * 
     * @param string $cf codice fiscale
     * @return array $ditta (Array normalizzato)
     */
    public function DettaglioCompletoImpresaDaCF($cf) {
        $WSClient = new itaAdrierClient();
        $this->setClientConfig($WSClient);

        $param = array();
        $param['codice_fiscale'] = $cf;
        $ret = $WSClient->ws_RicercaImpresePerCodiceFiscale($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("CF non trovato");
            return false;
        }
        //cerco il dettaglio ditta
        $LISTA = $ret['DATI'][0]['LISTA_IMPRESE'][0]['ESTREMI_IMPRESA'];
        foreach ($LISTA as $IMPRESA) {
            $sgl_prv_sede = $IMPRESA['DATI_ISCRIZIONE_REA'][0]['CCIAA'][0]['@textNode'];
            $n_rea_sede = $IMPRESA['DATI_ISCRIZIONE_REA'][0]['NREA'][0]['@textNode'];
            if ($sgl_prv_sede != '' && $n_rea_sede != ''){
                break;
            }
        }
        $param = array();
        $param['sgl_prv_sede'] = $sgl_prv_sede;
        $param['n_rea_sede'] = $n_rea_sede;
        $ret = $WSClient->ws_DettaglioCompletoImpresa($param);
        if (!$ret) {
            if ($WSClient->getFault()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getFault(), true) . '</pre>');
            } elseif ($WSClient->getError()) {
                $this->setErrMessage('<pre style="font-size:1.5em">' . print_r($WSClient->getError(), true) . '</pre>');
            }
            return false;
        }
        $ret_xml = $WSClient->getResult();
        //trasformo xml in array
        $xmlObj = new itaXML;
        $xmlObj->setTrimBlanks(false);
        $xmlObj->setXmlFromString($ret_xml);
        $ret = $xmlObj->toArray($xmlObj->asObject());
        if ($ret['HEADER'][0]['ESITO'][0]['@textNode'] != 'OK') {
            $this->setErrMessage("Ditta non trovata per n. REA $n_rea_sede, prov $sgl_prv_sede");
            return false;
        }

        return $this->NormalizzaArrayDitta($ret);
//        Out::msgInfo("RicercaImpresePerCodiceFiscale Result", print_r($ret, true));
    }

    public function setClientConfig($WSClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('ADRIER', 'codice', 'ENDPOINT', false);
        $wsdl = $devLib->getEnv_config('ADRIER', 'codice', 'WSDL', false);
        $ns_adr = $devLib->getEnv_config('ADRIER', 'codice', 'ADRNAMESPACE', false);
        $user = $devLib->getEnv_config('ADRIER', 'codice', 'USERNAME', false);
        $password = $devLib->getEnv_config('ADRIER', 'codice', 'PASSWORD', false);

        $WSClient->setWebservices_uri($uri['CONFIG']);
        $WSClient->setWebservices_wsdl($wsdl['CONFIG']);
        $WSClient->setNamespaces(array("adr" => $ns_adr['CONFIG']));
        $WSClient->setUsername($user['CONFIG']);
        $WSClient->setPassword($password['CONFIG']);
    }

    public function NormalizzaArrayDitta($ret) {
        $ditta = array();
        if (isset($ret['DATI'][0]['LISTA_IMPRESE'][0])) {
            $ESTREMI_IMPRESA = $ret['DATI'][0]['LISTA_IMPRESE'][0]['ESTREMI_IMPRESA'][0];
        } else {
            $ESTREMI_IMPRESA = $ret['DATI'][0]['DATI_IMPRESA'][0]['ESTREMI_IMPRESA'][0];
        }

        $ditta['ESTREMI_IMPRESA'] = array();
        $ditta['ESTREMI_IMPRESA']['DENOMINAZIONE'] = $ESTREMI_IMPRESA['DENOMINAZIONE'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['CODICE_FISCALE'] = $ESTREMI_IMPRESA['CODICE_FISCALE'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['PARTITA_IVA'] = $ESTREMI_IMPRESA['PARTITA_IVA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['FORMA_GIURIDICA']['C_FORMA_GIURIDICA'] = $ESTREMI_IMPRESA['FORMA_GIURIDICA'][0]['C_FORMA_GIURIDICA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['FORMA_GIURIDICA']['DESCRIZIONE'] = $ESTREMI_IMPRESA['FORMA_GIURIDICA'][0]['DESCRIZIONE'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_RI']['DATA'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_RI'][0]['DATA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['NREA'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_REA'][0]['NREA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['CCIAA'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_REA'][0]['CCIAA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['FLAG_SEDE'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_REA'][0]['FLAG_SEDE'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['DATA'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_REA'][0]['DATA'][0]['@textNode'];
        $ditta['ESTREMI_IMPRESA']['DATI_ISCRIZIONE_REA']['C_FONTE'] = $ESTREMI_IMPRESA['DATI_ISCRIZIONE_REA'][0]['C_FONTE'][0]['@textNode'];
        if (isset($ret['DATI'][0]['DATI_IMPRESA'][0]['DURATA_SOCIETA'][0])) {
            $DURATA_SOCIETA = $ret['DATI'][0]['DATI_IMPRESA'][0]['DURATA_SOCIETA'][0];
            $ditta['DURATA_SOCIETA'] = array();
            $ditta['DURATA_SOCIETA']['DT_COSTITUZIONE'] = $DURATA_SOCIETA['DT_COSTITUZIONE'][0]['@textNode'];
            $ditta['DURATA_SOCIETA']['DT_TERMINE'] = $DURATA_SOCIETA['DT_TERMINE'][0]['@textNode'];
        }
        if (isset($ret['DATI'][0]['DATI_IMPRESA'][0]['INFORMAZIONI_SEDE'][0])) {
            $INFORMAZIONI_SEDE = $ret['DATI'][0]['DATI_IMPRESA'][0]['INFORMAZIONI_SEDE'][0];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['PROVINCIA'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['PROVINCIA'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['COMUNE'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['COMUNE'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['C_COMUNE'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['C_COMUNE'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['TOPONIMO'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['TOPONIMO'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['VIA'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['VIA'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['N_CIVICO'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['N_CIVICO'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['CAP'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['CAP'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INDIRIZZO']['INDIRIZZO_PEC'] = $INFORMAZIONI_SEDE['INDIRIZZO'][0]['INDIRIZZO_PEC'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['ATTIVITA'] = $INFORMAZIONI_SEDE['ATTIVITA'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['INFO_ATTIVITA']['DT_INIZIO_ATTIVITA'] = $INFORMAZIONI_SEDE['INFO_ATTIVITA'][0]['DT_INIZIO_ATTIVITA'][0]['@textNode'];
            $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'] = array();

            $ATTIVITA_ISTAT_tab = $INFORMAZIONI_SEDE['CODICE_ATECO_UL'][0]['ATTIVITA_ISTAT'];
            foreach ($ATTIVITA_ISTAT_tab as $k => $ATTIVITA_ISTAT) {
                $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][$k]['C_ATTIVITA'] = $ATTIVITA_ISTAT['C_ATTIVITA'][0]['@textNode'];
                $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][$k]['T_CODIFICA'] = $ATTIVITA_ISTAT['T_CODIFICA'][0]['@textNode'];
                $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][$k]['DESC_ATTIVITA'] = $ATTIVITA_ISTAT['DESC_ATTIVITA'][0]['@textNode'];
                $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][$k]['C_IMPORTANZA'] = $ATTIVITA_ISTAT['C_IMPORTANZA'][0]['@textNode'];
                $ditta['INFORMAZIONI_SEDE']['CODICE_ATECO_UL']['ATTIVITA_ISTAT'][$k]['DT_INIZIO_ATTIVITA'] = $ATTIVITA_ISTAT['DT_INIZIO_ATTIVITA'][0]['@textNode'];
            }
        }
        if (isset($ret['DATI'][0]['DATI_IMPRESA'][0]['PERSONE_SEDE'][0]['PERSONA'])) {
            $PERSONE_SEDE_tab = $ret['DATI'][0]['DATI_IMPRESA'][0]['PERSONE_SEDE'][0]['PERSONA'];
            foreach ($PERSONE_SEDE_tab as $k => $PERSONE_SEDE) {
                $ditta['PERSONE_SEDE']['PERSONA'][$k]['IDENTIFICATIVO']['PRESSO_CCIAA'] = $PERSONE_SEDE['IDENTIFICATIVO'][0]['PRESSO_CCIAA'][0]['@textNode'];
                $ditta['PERSONE_SEDE']['PERSONA'][$k]['IDENTIFICATIVO']['PRESSO_N_REA'] = $PERSONE_SEDE['IDENTIFICATIVO'][0]['PRESSO_N_REA'][0]['@textNode'];
                $ditta['PERSONE_SEDE']['PERSONA'][$k]['IDENTIFICATIVO']['PROGRESSIVO_PERSONA'] = $PERSONE_SEDE['IDENTIFICATIVO'][0]['PROGRESSIVO_PERSONA'][0]['@textNode'];
                $ditta['PERSONE_SEDE']['PERSONA'][$k]['IDENTIFICATIVO']['PROGRESSIVO_LOC'] = $PERSONE_SEDE['IDENTIFICATIVO'][0]['PROGRESSIVO_LOC'][0]['@textNode'];
                //PERSONA FISICA
                if (isset($PERSONE_SEDE['PERSONA_FISICA'][0])) {
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_FISICA']['COGNOME'] = $PERSONE_SEDE['PERSONA_FISICA'][0]['COGNOME'][0]['@textNode'];
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_FISICA']['NOME'] = $PERSONE_SEDE['PERSONA_FISICA'][0]['NOME'][0]['@textNode'];
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_FISICA']['ESTREMI_NASCITA']['DATA'] = $PERSONE_SEDE['PERSONA_FISICA'][0]['ESTREMI_NASCITA'][0]['DATA'][0]['@textNode'];
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_FISICA']['CODICE_FISCALE'] = $PERSONE_SEDE['PERSONA_FISICA'][0]['CODICE_FISCALE'][0]['@textNode'];
                }
                //PERSONA GIURIDICA
                if (isset($PERSONE_SEDE['PERSONA_GIURIDICA'][0])) {
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_GIURIDICA']['DENOMINAZIONE'] = $PERSONE_SEDE['PERSONA_GIURIDICA'][0]['DENOMINAZIONE'][0]['@textNode'];
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['PERSONA_GIURIDICA']['CODICE_FISCALE'] = $PERSONE_SEDE['PERSONA_GIURIDICA'][0]['CODICE_FISCALE'][0]['@textNode'];
                }
                $CARICHE_tab = $PERSONE_SEDE['CARICHE'][0]['CARICA'];
                foreach ($CARICHE_tab as $t => $CARICHE) {
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['CARICHE']['CARICA'][$t]['DESCRIZIONE'] = $CARICHE['DESCRIZIONE'][0]['@textNode'];
                    $ditta['PERSONE_SEDE']['PERSONA'][$k]['CARICHE']['CARICA'][$t]['C_CARICA'] = $CARICHE['C_CARICA'][0]['@textNode'];
                }
                $ditta['PERSONE_SEDE']['PERSONA'][$k]['RAPPRESENTANTE'] = $PERSONE_SEDE['RAPPRESENTANTE'][0]['@textNode'];
            }
        }

        return $ditta;
    }

}
