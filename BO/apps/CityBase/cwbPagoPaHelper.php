<?php

/**
 *
 * Classe per la gestione dell'intermediario E-Fil (PagoPa)
 * 
 */
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class cwbPagoPaHelper {

    public static $current_url;
    public static $timeout;
    public static $debug_level;

    static function getCurrent_url() {
        return self::$current_url;
    }

    static function setCurrent_url($current_url) {
        self::$current_url = $current_url;
    }

    static function getTimeout() {
        return self::$timeout;
    }

    static function getDebug_level() {
        return self::$debug_level;
    }

    static function setTimeout($timeout) {
        self::$timeout = $timeout;
    }

    static function setDebug_level($debug_level) {
        self::$debug_level = $debug_level;
    }

    public static $mappingIntermediari = array(
        1 => array('CONTROLLER_IMPL' => 'pagoPAEfillImpl', 'DESCR' => 'Efill'), // efill 
        2 => array('CONTROLLER_IMPL' => 'pagoPANextStepImpl', 'DESCR' => 'NextStep'), // nextstep
        3 => array('CONTROLLER_IMPL' => 'pagoPAEfillZZImpl', 'DESCR' => 'Efill - ZZ'), // efillzz
        4 => array('CONTROLLER_IMPL' => 'pagoPAMPayImpl', 'DESCR' => 'MPay'), // mpay
        5 => array('CONTROLLER_IMPL' => 'pagoPAAltoAdigeRiscoImpl', 'DESCR' => 'Altro Adige Riscossioni') // mpay
    );
    public static $mappingServizi = array(
        '71S0' => array('DESCR' => 'Codice della strada', 'CODTIPSCAD' => 71, 'SUBTIPSCAD' => 0, 'CODMODULO' => 'LCS'),
        '72S0' => array('DESCR' => 'Zona a traffico limitato', 'CODTIPSCAD' => 72, 'SUBTIPSCAD' => 0, 'CODMODULO' => 'LZL'),
        '73S0' => array('DESCR' => 'Fiere', 'CODTIPSCAD' => 73, 'SUBTIPSCAD' => 0, 'CODMODULO' => 'LFI'),
        '74S0' => array('DESCR' => 'Procedimenti online', 'CODTIPSCAD' => 74, 'SUBTIPSCAD' => 0, 'CODMODULO' => 'LPO')
    );

    public static function getSoggettoPagoPa($progsogg, $progsoggex) {
        if ($progsogg) {
            $libDB_BTA = new cwbLibDB_BTA();
            return $libDB_BTA->leggiBtaSoggPPAChiave($progsogg);
        } else if ($progsoggex) {
            $libDB_BGE = new cwbLibDB_BGE();
            return $libDB_BGE->leggiBgeAgidSoggetti(array('PROGSOGG' => $progsoggex), false);
        }
        return false;
    }

    public static function getCodiceIntermediario($codtipscad, $subtipscad, $annoemi = null, $numemi = null, $idbol_sere = null) {
        $data = cwbPagoPaHelper::getServizioIntermediario($codtipscad, $subtipscad, $annoemi, $numemi, $idbol_sere);
        return $data['INTERMEDIARIO'];
    }

    public static function getIntermediarioDaRifChiaveEsterna($chiaveEsterna) {
        $filtri['PROGCITYSC'] = $chiaveEsterna;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiGetIntermediarioDaRifChiaveEsterna($filtri);
    }

    public static function getCodiceIntermediarioDaIdRuolo($idRuolo) {
        $filtri['IDRUOLO'] = $idRuolo;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiGetIntermediarioDaIdRuolo($filtri);
    }

    public static function getIntermediarioDaCodRiferimento($codRiferimento) {
        $filtri['CODRIFERIMENTO'] = $codRiferimento;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiGetIntermediarioDaRifChiaveEsterna($filtri);
    }

    public static function getServizioIntermediario($codtipscad, $subtipscad, $annoemi = null, $numemi = null, $idbol_sere = null) {
        $filtri['CODTIPSCAD'] = $codtipscad;
        $filtri['SUBTIPSCAD'] = $subtipscad;
        $filtri['ANNOEMI'] = $annoemi;
        $filtri['NUMEMI'] = $numemi;
        $filtri['IDBOL_SERE'] = $idbol_sere;
        $libDB_BTA = new cwbLibDB_BTA();
        $res = $libDB_BTA->leggiGetIntermediario($filtri);
        return $res[0];
    }

    public static function getCodiceIntermediarioDaEmissione($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca = null) {
        $filtri['CODTIPSCAD'] = $codtipscad;
        $filtri['SUBTIPSCAD'] = $subtipscad;
        $filtri['PROGCITYSC'] = $progcitysc;
        $filtri['PROGCITYSCA'] = $progcitysca;
        $filtri['ANNORIF'] = $annorif;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiGetCodiceIntermediarioDaEmissione($filtri);
    }

    public static function getIntermediarioDaIUV($IUV) {
        $filtri['IUV'] = $IUV;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiGetIntermediarioDaIUV($filtri);
    }

    public static function getInfoGetIntermediarioDaIUV($IUV) {
        $filtri['IUV'] = $IUV;
        $libDB_BTA = new cwbLibDB_BTA();
        return $libDB_BTA->leggiInfoGetIntermediarioDaIUV($filtri);
    }

    public static function recuperaIUV($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata) {
        $filtri['CODTIPSCAD'] = $codtipscad;
        $filtri['SUBTIPSCAD'] = $subtipscad;
        $filtri['ANNORIF'] = $annorif;
        $filtri['PROGCITYSC'] = $progcitysc;
        $filtri['NUMRATA'] = $numrata;
        $libDB_BGE = new cwbLibDB_BGE();
        return $libDB_BGE->leggiRecuperaIUV($filtri);
    }

    public static function sincronizzaNodo() {
        $intermediari = cwbPagoPaHelper::intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = cwbPagoPaHelper::getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->sincronizzazioneNodo();
            }
        }
    }

    public static function intermediario() {
        $libDB_BTA = new cwbLibDB_BTA();
        $intermediari = $libDB_BTA->leggiBtaServrendIntermediari();
        return $intermediari;
    }

    private static function getPagoPa($type) {
        try {
            $pagoPa = new itaPagoPa($type);
        } catch (Exception $ex) {
            Out::msgStop("ERRORE", $ex->getMessage());
            return null;
        }
        return $pagoPa;
    }

    public static function trovaProgCityscDaProgCitysca($progCitysca, $codtipscad, $subtipscad) {
        $progcitysc = null;
        if (!$progCitysca || !$codtipscad) {
            return false;
        }
        // lo cerco nei nostri archivi, per vedere a quale progcitysc corrisponde.
        // se non ne trovo nessuno, lo calcolo facendo max+1 a parità di CODTIPSCAD e SUBTIPSCAD
        $cwbLibDB_BWE = new cwbLibDB_BWE();
        $filtri = array(
            'PROGCITYSCA' => $progCitysca,
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad
        );
        $penden = $cwbLibDB_BWE->leggiBwePenden($filtri, false, $sqlParams, false);
        if ($penden) {
            // se esiste già un progcitysc associato a questo progcitysca ritorno quello
            $progcitysc = $penden['PROGCITYSC'];
        } else {
            // se non esiste un progcitysca nel db calcolo una max + 1 di progcitysc
            $where = ' CODTIPSCAD=' . $codtipscad . ' AND SUBTIPSCAD=' . $subtipscad;
            $progcitysc = cwbLibCalcoli::trovaProgressivo("PROGCITYSC", "BWE_PENDEN", $where);
        }

        return $progcitysc;
    }

    public static function getIntermediario($params, $metodo = null) {
        if ($params['TipoIntermediario']) {
            return $params['TipoIntermediario'];
        }

        if ($params['CodiceIdentificativo']) {
            $interm = cwbPagoPaHelper::getIntermediarioDaIUV($params['CodiceIdentificativo']);

            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            } else {
                // se c'è il CodiceIdentificativo ma non lo trova in tabella come iuv, lo cerco come codriferimento
                // perchè per qualche intermediario (tipo efilzz) lo iuv è il codriferimento
                $params['CodRiferimento'] = $params['CodiceIdentificativo'];
            }
        }

        if ($params['CodRiferimento']) {
            $interm = cwbPagoPaHelper::getIntermediarioDaCodRiferimento($params['CodRiferimento']);

            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            }
        }

        if ($params['AnnoEmi'] && $params['NumEmi'] && $params['IdBolSere']) {
            $interm = cwbPagoPaHelper::getCodiceIntermediario(null, null, $params['AnnoEmi'], $params['NumEmi'], $params['IdBolSere']);

            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            }
        }

        if ($params['CodTipScad'] && $params['SubTipScad'] !== null && ($params['ProgCitySc'] !== null || $params['ProgCitySca'] !== null) && $params['AnnoRif'] !== null) {
            $interm = cwbPagoPaHelper::getCodiceIntermediarioDaEmissione($params['CodTipScad'], $params['SubTipScad'], $params['ProgCitySc'], $params['AnnoRif'], $params['ProgCitySca']);

            if ($interm['INTERMEDIARIO']) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm['INTERMEDIARIO'];
            }
        }

        if ($params['CodTipScad'] && $params['SubTipScad'] !== null) {
            $interm = cwbPagoPaHelper::getCodiceIntermediario($params['CodTipScad'], $params['SubTipScad']);

            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            }
        }

        if ($params['IdRuolo']) {
            $interm = cwbPagoPaHelper::getCodiceIntermediarioDaIdRuolo($params['IdRuolo']);

            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            }
        }

        if ($metodo === 'ricercaPosizioniDaInfoAggiuntive' && $params) {
            // prendo l'intermediario della prima scadenza
            $scadenza = cwbPagoPaHelper::getScadenzaPerInfoAggiuntive($params, false);
            if ($scadenza['IUV']) {
                $interm = cwbPagoPaHelper::getIntermediarioDaIUV($scadenza['IUV']);
            } else if ($scadenza['CODRIFERIMENTO']) {
                $interm = cwbPagoPaHelper::getIntermediarioDaCodRiferimento($scadenza['CODRIFERIMENTO']);
            }
            if ($interm) {
                // se lo trova lo ritorna sennò prova a cercarlo sugli if sotto
                return $interm;
            }
        }

        return false;
    }

    public static function getScadenzaPerInfoAggiuntive($arrayFiltri, $multipla) {
        if ($arrayFiltri[0] || !$arrayFiltri) {
            return false;
        } else {
            $libDB_BGE = new cwbLibDB_BGE();
            return $libDB_BGE->leggiBgeAgidScadenzeDatiAggiuntivi($arrayFiltri, $multipla);
        }
    }

    /**
     * funzione statica per prendere token di ItaEngine
     * 
     * @param type $UserName
     * @param type $Password
     * @param type $DomainCode
     * 
     * return $token String
     */
    public static function GetItaEngineContextToken($UserName, $Password, $DomainCode) {
        $parametri_call = array(
            'UserName' => $UserName,
            'Password' => $Password,
            'DomainCode' => $DomainCode
        );
        $headers = array();
        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout(self::$timeout);
        $itaRestClient->setDebugLevel(self::$debug_level);

        if (!$itaRestClient->get(self::$current_url, $parametri_call, $headers)) {
            return $itaRestClient->getErrMessage();
        }
        return $itaRestClient->getResult();
    }

    /**
     * funzione statica per distruggere il token di ItaEngine
     * 
     * @param type $token
     * 
     * return true|false
     */
    public static function DestroyItaEngineContextToken($token) {
        $parametri_call = array(
            'TokenKey' => $token
        );
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la pubblicazione di una posizione
     * 
     * @param type $token - token di accesso
     * @param type $modo - CHIAVEPENDENZA|XML
     * @param type $parametri
     * se modo = CHIAVEPENDENZA: CodTipScad, SubTipScad, ProgCitySc = '', AnnoRif = ''
     * se modo = XML: CodTipScad, SubTipScad, XML
     */
    public static function pubblicaPosizione($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'XML':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'Pendenza' => base64_encode($parametri['Pendenza'])
                );
                break;

            default:
                return false;
                break;
        }
//        Out::msgInfo("parametri_call", print_r($parametri_call, true));
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per inserire un pagamento dentro la posizione
     * 
     * @param type $modo - CHIAVEPENDENZA|IUV
     * @param type $parametri
     */
    public static function eseguiPagamento($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'ProgCitySca' => $parametri['ProgCitySca'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'IUV':
                $parametri_call = array(
                    'CodiceIdentificativo' => $parametri['CodiceIdentificativo'],
                    'urlReturn' => $parametri['urlReturn']
                );
                break;

            default:
                return false;
                break;
        }
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la generazione del bollettino
     * 
     * @param type $parametri
     */
    public static function generaBollettino($token, $modo, $parametri) {
        switch ($modo) {
            case 'CHIAVEPENDENZA':
                $parametri_call = array(
                    'CodTipScad' => $parametri['CodTipScad'],
                    'SubTipScad' => $parametri['SubTipScad'],
                    'ProgCitySc' => $parametri['ProgCitySc'],
                    'ProgCitySca' => $parametri['ProgCitySca'],
                    'AnnoRif' => $parametri['AnnoRif']
                );
                break;
            case 'IUV':
                $parametri_call = array(
                    'CodiceIdentificativo' => $parametri['CodiceIdentificativo']
                );
                break;

            default:
                return false;
                break;
        }
        file_put_contents("C:/tmp/generaBollettino_helper.txt", print_r($parametri_call, true));
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    /**
     * funzione per la ricerca di una posizione
     * 
     * @param type $parametri CodiceIdentificativo => valore
     */
    public static function ricercaPosizioneIUV($token, $parametri) {
        $parametri_call = array(
            'CodiceIdentificativo' => $parametri['CodiceIdentificativo']
        );
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    public static function ricercaPosizioneChiaveEsterna($token, $parametri) {
        $parametri_call = array(
            'CodTipScad' => $parametri['CodTipScad'],
            'SubTipScad' => $parametri['SubTipScad'],
            'ProgCitySc' => $parametri['ProgCitySc'],
            'ProgCitySca' => $parametri['ProgCitySca'],
            'AnnoRif' => $parametri['AnnoRif'],
            'NumRata' => $parametri['NumRata']
        );
        return self::eseguiChiamataRest($token, $parametri_call);
    }

    public static function rettificaPosizione($token, $parametri) {
        return self::eseguiChiamataRest($token, $parametri);
    }

    public static function rimuoviPosizione($token, $parametri) {
        return self::eseguiChiamataRest($token, $parametri);
    }

    public static function ricercaPosizioniDaA($token, $parametri) {
        return self::eseguiChiamataRest($token, $parametri);
    }

    public static function eseguiChiamataRest($token, $parametri) {
        file_put_contents("C:/tmp/parametri.txt", print_r($parametri, true));
        $headers = array();
        if ($token) {
            $headers[] = 'X-ITA-TOKEN: ' . $token;
        } else {
            Out::msgStop("Errore", "token mancante");
        }
        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout(self::$timeout);
        $itaRestClient->setDebugLevel(self::$debug_level);
        if (!$itaRestClient->get(self::$current_url, $parametri, $headers)) {
            Out::msgStop("Errore", $itaRestClient->getErrMessage());
            return $itaRestClient->getErrMessage();
        }
        file_put_contents("C:/tmp/debug.txt", $itaRestClient->getDebug());
        return $itaRestClient->getResult();
    }

}
