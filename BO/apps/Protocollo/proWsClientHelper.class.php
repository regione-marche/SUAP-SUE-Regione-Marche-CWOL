<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

/**
 *
 * HELPER PER GESTIONE CHIAMATE A PROTOCOLLI DI TERZE PARTI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Protocollo
 * @author     Luca Cardinali <l.cardinali@apra.it>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    13.11.2017
 * @link
 * @see
 * @since
 * */
class proWsClientHelper {

    const CLIENT_AUTOFACTORY = "auto";
    const CLIENT_MANUAL = "Manuale";
    const CLIENT_ITALSOFT = "Italsoft";
    const CLIENT_ITALSOFT_REMOTO = "Italsoft-remoto";
    const CLIENT_ITALSOFT_REMOTO_ALLE = "Italsoft-remoto-allegati";
    const CLIENT_PALEO = "Paleo";
    const CLIENT_PALEO4 = "Paleo4";
    const CLIENT_PALEO41 = "Paleo41";
    const CLIENT_INFOR = "Infor";
    const CLIENT_WSPU = "WSPU";
    const CLIENT_IRIDE = "Iride";
    const CLIENT_JIRIDE = "Jiride";
    const CLIENT_HYPERSIC = "HyperSIC";
    const CLIENT_ELIOS = "E-Lios";
    const CLIENT_ITALPROT = "Italsoft-ws";
    const CLIENT_SICI = "Sici";
    const CLIENT_LEONARDO = "Leonardo";
    const CLIENT_KIBERNETES = "Kibernetes";
    const CLIENT_CIVILIANEXT = "CiviliaNext";
    const CLIENT_DATAGRAPH = "Datagraph";
    const CLIENT_FOLIUM = "Folium";
    const CLASS_PARAM_PROTOCOLLO_PALEO = "PALEOWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_PALEO4 = "PALEO4WSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_PALEO41 = "PALEO4WSCONNECTION";
    const CLASS_PARAM_MAIL_PALEO4 = "PALEO4WSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_INFOR = "INFORJPROTOCOLLOWS";
    const CLASS_PARAM_PROTOCOLLO_WSPU = "HWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_JIRIDE = "JIRIDEWSCONNECTION";
    const CLASS_PARAM_FASCICOLAZIONE_JIRIDE = "JIRIDEWSFASCICOLAZIONE";
    const CLASS_PARAM_MAIL_JIRIDE = "JIRIDEWSMAIL";
    const CLASS_PARAM_PROTOCOLLO_IRIDE = "IRIDEWSCONNECTION";
    const CLASS_PARAM_FASCICOLAZIONE_IRIDE = "IRIDEWSFASCICOLAZIONE";
    const CLASS_PARAM_PROTOCOLLO_HYPERSIC = "HYPERSICWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_ELIOS = "ELIOSWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_ITALPROT = "ITALSOFTPROTWS";
    const CLASS_PARAM_PROTOCOLLO_SICI = "SICIWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_LEONARDO = "LEONARDOSWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_KIBERNETES = "KIBERNETESWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_CIVILIANEXT = "CIVILIANEXTWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_DATAGRAPH = "DATAGRAPHWSCONNECTION";
    const CLASS_PARAM_PROTOCOLLO_FOLIUM = "FOLIUMWSCONNECTION";

    public static function getElencoProtocolliRemoti() {
        return array(
            self::CLIENT_MANUAL,
            self::CLIENT_ITALSOFT,
            self::CLIENT_ITALSOFT_REMOTO,
            self::CLIENT_ITALSOFT_REMOTO_ALLE,
            self::CLIENT_PALEO,
            self::CLIENT_PALEO4,
            self::CLIENT_PALEO41,
            self::CLIENT_INFOR,
            self::CLIENT_WSPU,
            self::CLIENT_IRIDE,
            self::CLIENT_JIRIDE,
            self::CLIENT_HYPERSIC,
            self::CLIENT_ELIOS,
            self::CLIENT_ITALPROT,
            self::CLIENT_SICI,
            self::CLIENT_LEONARDO,
            self::CLIENT_KIBERNETES,
            self::CLIENT_CIVILIANEXT,
            self::CLIENT_DATAGRAPH,
            self::CLIENT_FOLIUM
        );
    }

    public static function getClientMailParamClass($protocollo) {
        switch ($protocollo) {
            case self::CLIENT_JIRIDE:
                return self::CLASS_PARAM_MAIL_JIRIDE;
            case self::CLIENT_PALEO4:
                return self::CLASS_PARAM_MAIL_PALEO4;
        }
    }

    public static function getClientFascicolazioneParamClass($protocollo) {
        switch ($protocollo) {
            case self::CLIENT_IRIDE:
                return self::CLASS_PARAM_FASCICOLAZIONE_IRIDE;
            case self::CLIENT_JIRIDE:
                return self::CLASS_PARAM_FASCICOLAZIONE_JIRIDE;
        }
    }

    public static function getClientProtocolloParamClass($protocollo) {
        switch ($protocollo) {
            case self::CLIENT_PALEO:
                return self::CLASS_PARAM_PROTOCOLLO_PALEO;
            case self::CLIENT_PALEO4:
                return self::CLASS_PARAM_PROTOCOLLO_PALEO4;
            case self::CLIENT_PALEO41:
                return self::CLASS_PARAM_PROTOCOLLO_PALEO41;
            case self::CLIENT_INFOR:
                return self::CLASS_PARAM_PROTOCOLLO_INFOR;
            case self::CLIENT_WSPU:
                return self::CLASS_PARAM_PROTOCOLLO_WSPU;
            case self::CLIENT_IRIDE:
                return self::CLASS_PARAM_PROTOCOLLO_IRIDE;
            case self::CLIENT_JIRIDE:
                return self::CLASS_PARAM_PROTOCOLLO_JIRIDE;
            case self::CLIENT_HYPERSIC:
                return self::CLASS_PARAM_PROTOCOLLO_HYPERSIC;
            case self::CLIENT_ELIOS:
                return self::CLASS_PARAM_PROTOCOLLO_ELIOS;
            case self::CLIENT_ITALPROT:
                return self::CLASS_PARAM_PROTOCOLLO_ITALPROT;
            case self::CLIENT_SICI:
                return self::CLASS_PARAM_PROTOCOLLO_SICI;
            case self::CLIENT_LEONARDO:
                return self::CLASS_PARAM_PROTOCOLLO_LEONARDO;
            case self::CLIENT_KIBERNETES:
                return self::CLASS_PARAM_PROTOCOLLO_KIBERNETES;
            case self::CLIENT_CIVILIANEXT:
                return self::CLASS_PARAM_PROTOCOLLO_CIVILIANEXT;
            case self::CLIENT_DATAGRAPH:
                return self::CLASS_PARAM_PROTOCOLLO_DATAGRAPH;
            case self::CLIENT_FOLIUM:
                return self::CLASS_PARAM_PROTOCOLLO_FOLIUM;
            default:
                break;
        }
    }

    static function getCampiInput($driver) {
        switch ($driver) {
            case self::CLIENT_PALEO4:
            case self::CLIENT_PALEO41:
                $arr = array(
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Id Documento'),
                        'id' => 'praPasso_idDoc',
                        'name' => 'praPasso_idDoc',
                        'width' => '50',
                        'size' => '10',
                        'maxchars' => '30'),
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Segnatura'),
                        'id' => 'praPasso_Segnatura',
                        'name' => 'praPasso_Segnatura',
                        'width' => '50',
                        'size' => '30')
                );
                break;
            case self::CLIENT_ITALPROT:
                $arr = array(
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Tipo'),
                        'id' => 'praPasso_tipoProt',
                        'name' => 'praPasso_tipoProt',
                        'width' => '50',
                        'type' => 'select',
                        'class' => 'ita-select',
                        'options' => array(
                            array("", ""),
                            array("A", "Arrivo"),
                            array("P", "Partenza"),
                            array("C", "Interno"),
                        )
                    ),
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Numero'),
                        'id' => 'praPasso_nummeroProt',
                        'name' => 'praPasso_nummeroProt',
                        'width' => '50',
                        'size' => '10',
                        'maxchars' => '30'),
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Anno'),
                        'id' => 'praPasso_AnnoProtocollo',
                        'name' => 'praPasso_AnnoProtocollo',
                        'width' => '50',
                        'size' => '6',
                        'maxchars' => '4')
                );
                break;
            case self::CLIENT_IRIDE:
            case self::CLIENT_JIRIDE:
                $arr = array(
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Numero'),
                        'id' => 'praPasso_nummeroProt',
                        'name' => 'praPasso_nummeroProt',
                        'width' => '50',
                        'size' => '10',
                        'maxchars' => '30'),
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Anno'),
                        'id' => 'praPasso_AnnoProtocollo',
                        'name' => 'praPasso_AnnoProtocollo',
                        'width' => '50',
                        'size' => '6',
                        'maxchars' => '4')
                );
                break;
            case self::CLIENT_CIVILIANEXT:
                $arr = array(
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Numero'),
                        'id' => 'praPasso_nummeroProt',
                        'name' => 'praPasso_nummeroProt',
                        'width' => '50',
                        'size' => '10',
                        'maxchars' => '30'),
                    array(
                        'label' => array('style' => "width:120px;", 'value' => 'Data'),
                        'id' => 'praPasso_DataProtocollo',
                        'name' => 'praPasso_DataProtocollo',
                        'width' => '50',
                        'class' => 'ita-datepicker',
                        'size' => '8')
                );
                break;
        }
        return $arr;
    }

    static function getClassName($tipoIstanza, $tipoProt) {
        switch ($tipoIstanza) {
            case "PROTOCOLLO":
                $nameClass = self::getClientProtocolloParamClass($tipoProt);
                break;
            case "FASCICOLAZIONE":
                $nameClass = self::getClientFascicolazioneParamClass($tipoProt);
                break;
            case "MAIL":
                $nameClass = self::getClientMailParamClass($tipoProt);
                break;
        }
        return $nameClass;
    }

    static function lanciaProtocollazioneWS($elementi, $tipo = 'A') {

        /*
         * Istanzio il manager di protocollazione
         */
        $proObject = proWSClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        //$proObject->setKeyConfigParams($arrDatiProtAggr['MetadatiProtocollo']['CLASSIPARAMETRI'][$tipoProt]['KEYPARAMWSPROTOCOLLO']);
        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);
        if ($tipo == 'A') {
            return $proObject->inserisciProtocollazioneArrivo($elementi);
        } else {
            return $proObject->inserisciProtocollazionePartenza($elementi);
        }
    }

    static function lanciaDocumentoFormaleWS($elementi) {

        /*
         * Istanzio il manager di protocollazione
         */
        $proObject = proWSClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);
        return $proObject->inserisciDocumentoInterno($elementi);
    }

    static function lanciaMettiAllaFirmaWS($elementi) {

        /*
         * Istanzio il manager di protocollazione
         */
        $proObject = proWSClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);
        return $proObject->inserisciDocumentoInterno($elementi, $elementi['tipo']);
    }

    static function lanciaFascicolazioneWS($elementi) {
        $ret = array();

        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstanceFascicolazione($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver fascicolazione";
            $ret["RetValue"] = false;
            return $ret;
        }

        /*
         * Controllo che se non esiste il client della fascicolazione torna comunque true.
         * La fasciolazione è prevista insieme alla protocollazione
         */
        if ($proObject === true) {
            $ret["Status"] = "0";
            $ret["Message"] = "";
            $ret["RetValue"] = true;
            return $ret;
        }

        /*
         * setto il codice istanza per la fascicolazione
         */
        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSFASCICOLAZIONE']);

        /*
         * Verifica se valorizzato un fascicolo fisso
         */
        $fascicola = false;
        if ($elementi['dati']['Fascicolazione']['CodiceFascicolo'] == "") {
            $risultato = $proObject->CreaFascicolo($elementi);
            if ($risultato['Status'] == "-1") {
                return $risultato;
            }
            $fascicola = true;
            $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $risultato['datiFascicolo']['codiceFascicolo'];
            $elementi['dati']['Fascicolazione']['AnnoFascicolo'] = $risultato['datiFascicolo']['annoFascicolo'];
        } else {

            /*
             * Verifica data chiusura fascicolo
             */
            $risultatoChk = $proObject->checkFascicolo($elementi['dati']['Fascicolazione']['CodiceFascicolo']);
            if ($risultatoChk['Status'] == "-1") {
                return $risultatoChk;
            }
            $fascicola = $risultatoChk['fascicola'];
        }

        /*
         * Se fascicola è false faccio il return del result
         */
        if (!$fascicola) {
            return $risultatoChk;
        }

        /*
         * Fascicolo il documento (Inserisco il protocollo nel fascicolo) se il flag è true
         */
        $risultatoFascicola = $proObject->FascicolaDocumento($elementi);
        if ($risultatoFascicola['Status'] == "-1") {
            return $risultatoFascicola;
        }

        $ret["Status"] = "0";
        $ret["Message"] = "Fascicolazione avvenuta con successo nel fascicolo n. " . $elementi['dati']['Fascicolazione']['CodiceFascicolo']; //$msgFascicola;
        $ret["RetValue"] = true;
        $ret["IdFascicolo"] = $risultatoFascicola["IdFascicolo"];
        return $ret;
    }

    static function lanciaAggiungiAllegatiWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver Protocollazione";
            $ret["RetValue"] = false;
            return $ret;
        }

        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);

        return $proObject->AggiungiAllegati($elementi);
    }

    static function lanciaLeggiFascicoloWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstanceFascicolazione($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver fascicolazione";
            $ret["RetValue"] = false;
            return $ret;
        }
        return $proObject->LeggiFascicolo($elementi);
    }

    static function lanciaCreaCopiaWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver protocollazione";
            $ret["RetValue"] = false;
            return $ret;
        }

        return $proObject->CreaCopie($elementi);
    }

    static function lanciaLeggiCopiaWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver protocollazione";
            $ret["RetValue"] = false;
            return $ret;
        }
        return $proObject->LeggiCopie($elementi);
    }

    static function lanciaAnnullaDocumentoWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver protocollazione";
            $ret["RetValue"] = false;
            return $ret;
        }
        return $proObject->AnnullaDocumento($elementi);
    }

    static function lanciaLeggiDocumentoWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstance($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver protocollazione";
            $ret["RetValue"] = false;
            return $ret;
        }
        return $proObject->leggiDocumentoInterno($elementi);
    }

    static function lanciaInvioMailWS($elementi) {

        /*
         * Istanzio il manager di protocollazione
         */
        $proObject = proWSClientFactory::getInstanceInvioMail($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);
        return $proObject->InvioMail($elementi);
    }

    static function lanciaVerificaInvioWS($elementi) {

        /*
         * Istanzio il manager di protocollazione
         */
        $proObject = proWSClientFactory::getInstanceVerificaInvio($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore inizializzazione driver protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $proObject->setKeyConfigParams($elementi['MetaDatiProtocollo']['CLASSIPARAMETRI'][$elementi['TipoProtocollo']]['KEYPARAMWSPROTOCOLLO']);
        return $proObject->VerificaInvio($elementi);
    }

}

?>