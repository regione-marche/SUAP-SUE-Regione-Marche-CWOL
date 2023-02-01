<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    19.06.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proOrgLayout.class.php';
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');

class proSoggetto {

    public $PROT_DB;
    public $proLib;
    public $soggetto;
    public $orgKey;
    public $orgKeyLayout;
    private $lastExitCode;
    private $lastMessage;

    /**
     * 
     * @param type $proLib
     * @param type $codProt
     * @return boolean|\praAggiuntivi
     */
    public static function getInstance($proLib, $codiceSoggetto = '', $ufficio = '') {
        try {
            $obj = new proSoggetto();
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        if (!$obj->caricaSoggetto($codiceSoggetto, $ufficio)) {
            return false;
        }
        return $obj;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function getSoggetto() {
        return $this->soggetto;
    }

    public function getOrgKey() {
        return $this->orgKey;
    }

    public function getOrgKeyLayout() {
        return $this->orgKeyLayout;
    }

    public function caricaSoggetto($codiceSoggetto, $ufficio) {
        $sql = "
            SELECT
                ANAUFF.UFFCOD AS CODICEUFFICIO,
                ANAUFF.UFFDES AS DESCRIZIONEUFFICIO,
                ANAMED.MEDCOD AS CODICESOGGETTO,
                ANAMED.MEDNOM AS DESCRIZIONESOGGETTO,
                ANAUFF.UFFRES AS CODICERESPONSABILEUFFICIO,
                UFFDES.UFFSCA AS SCARICA,
                UFFDES.UFFPROTECT AS LIVELLOPROTEZIONE,
                UFFDES.UFFFI1__1 AS GESTISCI,
                ANASERVIZI.SERCOD AS CODICESERVIZIO,
                ANASERVIZI.SERDES AS SERVIZIO,
                UFFDES.UFFFI1__2 AS CODICERUOLO,                
                ANARUOLI.RUODES AS RUOLO
            FROM
                UFFDES UFFDES
            LEFT OUTER JOIN
                ANAUFF ANAUFF ON UFFDES.UFFCOD = ANAUFF.UFFCOD
            LEFT OUTER JOIN
                ANAMED ANAMED ON UFFDES.UFFKEY=ANAMED.MEDCOD
            LEFT OUTER JOIN
                ANASERVIZI ANASERVIZI ON ANAUFF.UFFSER=ANASERVIZI.SERCOD
            LEFT OUTER JOIN
                ANARUOLI ANARUOLI ON UFFDES.UFFFI1__2 = ANARUOLI.RUOCOD
            WHERE UFFDES.UFFCOD='$ufficio' AND UFFDES.UFFKEY='$codiceSoggetto'";
        try {
            $this->soggetto = itaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
            if ($this->soggetto) {
                if ($this->soggetto['LIVELLOPROTEZIONE'] == 0) {
                    $this->soggetto['LIVELLOPROTEZIONE'] = 1;
                }
            }
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }

        $this->caricaOrgkey($this->soggetto);
        return true;
    }

    public function caricaOrgkey($soggetto) {
        $arrKey = array();
        foreach (proOrgLayout::caricaLayout() as $orgNode) {
            switch ($orgNode) {
                case "ENTE" :
                    $arrKey[] = $this->proLib->getAOOAmministrazione();
                    break;
                case "SETTORE" :
                    $arrKey[] = $soggetto['CODICESERVIZIO'];
                    break;
                case "UFFICIO" :
                    $arrKey[] = $soggetto['CODICEUFFICIO'];
                    break;
                case "SOGGETTO" :
                    $arrKey[] = $soggetto['CODICESOGGETTO'];
                    break;
            }
        }
        $this->orgKey = implode(".", $arrKey);
        $this->orgKeyLayout = implode(".", proOrgLayout::caricaLayout());
        return true;
    }

    public function getCodiceSoggetto() {
        return $this->soggetto['CODICESOGGETTO'];
    }

    public function getCodiceUfficio() {
        return $this->soggetto['CODICEUFFICIO'];
    }

    public function getCodiceResponsabileUfficio() {
        return $this->soggetto['CODICERESPONSABILEUFFICIO'];
    }

    public static function getCodiceSoggettoFromIdUtente($idUtente = null) {
        if ($idUtente === null) {
            $idUtente = App::$utente->getKey('idUtente');
        }
        include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
        $accLib = new accLib();
        $utenti_rec = $accLib->GetUtenti($idUtente);
        if (!$utenti_rec)
            return false;
        if (!$utenti_rec['UTEANA__1'])
            return false;
        return $utenti_rec['UTEANA__1'];
    }

    public static function getCodiceSoggettoFromNomeUtente($nomeUtente = null) {
        if ($nomeUtente === null) {
            $nomeUtente = App::$utente->getKey('nomeUtente');
        }
        include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
        $accLib = new accLib();
        $utenti_rec = $accLib->GetUtenti($nomeUtente, 'utelog');
        if (!$utenti_rec)
            return false;
        return self::getCodiceSoggettoFromIdUtente($utenti_rec['UTECOD']);
    }

    public static function getCodiceResponsabileAssegnazione() {
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
        $praLib = new praLib();
        $Ananom_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM ANANOM WHERE NOMABILITAASS=1", true);
        foreach ($Ananom_tab as $Ananom_rec) {
            if ($Ananom_rec['NOMRESPASS'] == 1) {
                return $Ananom_rec['NOMRES'];
            }
        }
    }

    public static function getCodiceUltimoResponsabile($pratica) {
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
        $praLib = new praLib();
        $Propas_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT PRORPA FROM PROPAS WHERE ROWID=(SELECT MAX(ROWID) FROM PROPAS WHERE PRONUM = '$pratica' AND PROOPE<>'')", false);
        return $Propas_rec['PRORPA'];
    }

    public static function getProfileFromNomeUtente($nomeUtente = null) {
        if ($nomeUtente === null) {
            $nomeUtente = App::$utente->getKey('nomeUtente');
        }
        include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
        $accLib = new accLib();
        $utenti_rec = $accLib->GetUtenti($nomeUtente, 'utelog');
        if (!$utenti_rec)
            return false;
        return self::getProfileFromIdUtente($utenti_rec['UTECOD']);
    }

    public static function getProfileFromIdUtente($idUtente = null) {
        if ($idUtente === null) {
            $idUtente = App::$utente->getKey('idUtente');
        }
        $profile = array();
        include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
        $accLib = new accLib();
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        $proLib = new proLib();
        $utenti_rec = $accLib->GetUtenti($idUtente);
        if (!$utenti_rec) {
            return false;
        }

        /*
         * Controlli particolari sui 2 parametri di segreteria?
         * Altrimenti get parametro segr assegnato direttamente.
         */
        $SegreteriaAbilitata = $accLib->GetSegrAbilitati($idUtente);
        $VisibilitaSegreteria = $accLib->GetSegrVisibilita($idUtente);

        $FasicoloAbilitata = $accLib->GetFascicoloAbilitati($idUtente);
        $VisibilitaFascicolo = $accLib->GetFascicoloVisibilita($idUtente);

        $VisibilitaOggRiservati = $accLib->GetProtocolloOggRis($idUtente);
        $ParamStampaEticUtente = $accLib->GetProtocolloEticUte($idUtente);


        $profile['UTELOG'] = $utenti_rec['UTELOG'];
        $profile['COD_SOGGETTO'] = $utenti_rec['UTEANA__1'];
        $profile['COD_ANANOM'] = $utenti_rec['UTEANA__3'];
        $profile['PROT_ABILITATI'] = $utenti_rec['UTEANA__4'];
        $profile['VIS_PROTOCOLLO'] = $utenti_rec['UTEANA__10'];
        $profile['VIS_RISERVATI'] = (int) $utenti_rec['UTEANA__9'];
        $profile['VIS_TSO'] = (int) $utenti_rec['UTEANA__8'];
        $profile['LIV_PROTEZIONE'] = (int) $utenti_rec['UTEFIL__3'];
        $profile['BLOC_TITOLARIO'] = (int) $utenti_rec['UTEANA__6'];
        $profile['NO_RISERVATO'] = (int) $utenti_rec['UTEANA__7'];
        $profile['OK_ANNULLA'] = (int) $utenti_rec['UTEFIA__2'];
        $profile['OGG_UTENTE'] = array();
        $profile['SEGR_ABILITATI'] = $SegreteriaAbilitata;
        $profile['VIS_SEGRETERIA'] = $VisibilitaSegreteria;
        $profile['FASCICOLO_ABILITATI'] = $FasicoloAbilitata;
        $profile['VIS_FASCICOLO'] = $VisibilitaFascicolo;
        if (!$profile['VIS_FASCICOLO']) {
            $profile['VIS_FASCICOLO'] = $profile['VIS_PROTOCOLLO'];
        }
        $profile['OGGRIS_VISIBILITA'] = (int) $VisibilitaOggRiservati;
        if (!$profile['OGGRIS_VISIBILITA']) {
            $profile['OGGRIS_VISIBILITA'] = 0;
        }

        $profile['PRNT_ETICUTE'] = $ParamStampaEticUtente;
        $Oggutenti_tab = $proLib->getOggUtenti($utenti_rec['UTELOG']);
        foreach ($Oggutenti_tab as $Oggutenti_rec) {
            $profile['OGG_UTENTE'][] = $Oggutenti_rec;
        }

        $profile['SERIE_SOGGETTO'] = array();
        if (!$profile['COD_SOGGETTO']) {
            $Medserie_tab = $proLib->GetSerieSoggetto($profile['COD_SOGGETTO']);
            foreach ($Medserie_tab as $Medserie_rec) {
                $profile['SERIE_SOGGETTO'][] = $Medserie_rec['SERIECODICE'];
            }
        }

        return $profile;
    }

    /**
     * Inserisce ufficio/ruolo valido per l visibilita del soggetto 
     * 
     * @param type $ruolo
     * @param type $arrayVisibilita
     * @return type
     * 
     */
    public static function insertVisNodeDown($ruolo, &$arrayVisibilita, $ruoliUtenteProprietario) {
        if ($ruolo['TIPOUFFICIO'] == 'U') {
            return;
        }
        $key = $ruolo['CODICEUFFICIO'];
        $ruoProp = '';
        if ($ruoliUtenteProprietario[$key]) {
            $ruoProp = '1';
        }
        if (!array_key_exists($key, $arrayVisibilita)) {
            $arrayVisibilita[$key] = array(
                "CODICEUFFICIO" => $key,
                "DESCRIZIONEUFFICIO" => $ruolo['DESCRIZIONEUFFICIO'],
                "TIPOUFFICIO" => $ruolo['TIPOUFFICIO'],
                "LIVELLOVIS" => $ruolo['LIVELLOVIS'],
                "LIVELLOVIS_ORIG" => $ruolo['LIVELLOVIS_ORIG'],
                "UFFPROTECT" => $ruolo['UFFPROTECT'],
                "PROPRIETARIO" => $ruoProp
            );
        }
//        }else {
//            if ($ruolo['LIVELLOVIS'] < $arrayVisibilita[$key]['LIVELLOVIS']) {
//                $arrayVisibilita[$key]['LIVELLOVIS'] = $ruolo['LIVELLOVIS'];
//            }
//        }
    }

    public static function insertVisNodeUp($ruolo, &$arrayRoots) {
        if ($ruolo['TIPOUFFICIO'] == 'R' && $ruolo['CODICE_PADRE']) {
            $key = $ruolo['CODICE_PADRE'];
            if (!array_key_exists($key, $arrayRoots)) {
                $arrayRoots[$key] = array(
                    "CODICEUFFICIO" => $key,
                    "DESCRIZIONEUFFICIO" => $ruolo['DESCRIZIONEUFFICIO_PADRE'],
                    "TIPOUFFICIO" => $ruolo['TIPOUFFICIO_PADRE'],
                    "LIVELLOVIS" => $ruolo['LIVELLOVIS'],
                    "LIVELLOVIS_ORIG" => $ruolo['LIVELLOVIS_ORIG'],
                    "UFFPROTECT" => $ruolo['UFFPROTECT']
                );
            }
        } else {
            $key = $ruolo['CODICEUFFICIO'];
            if (!array_key_exists($key, $arrayRoots)) {
                $arrayRoots[$key] = array(
                    "CODICEUFFICIO" => $key,
                    "DESCRIZIONEUFFICIO" => $ruolo['DESCRIZIONEUFFICIO'],
                    "TIPOUFFICIO" => $ruolo['TIPOUFFICIO'],
                    "LIVELLOVIS" => $ruolo['LIVELLOVIS'],
                    "LIVELLOVIS_ORIG" => $ruolo['LIVELLOVIS_ORIG'],
                    "UFFPROTECT" => $ruolo['UFFPROTECT']
                );
            }
        }
    }

    /**
     * 
     * Estrazione delle unita operative o ruoli figli
     * 
     * @param type $parent
     * @param type $livelloVisibilita
     * @param type $arrayStruttura
     * @param type $arrayVisibilita
     * @return boolean
     */
    public static function explodeVisNode($parent, $livelloVisibilita, &$arrayStruttura, &$arrayVisibilita, $ruoliUtenteProprietario) {
        $proLib = new proLib();
        $key = $parent['CODICEUFFICIO'];
        $sql = "
            SELECT
                ANAUFF.UFFCOD AS CODICEUFFICIO,
                ANAUFF.UFFDES AS DESCRIZIONEUFFICIO,
                ANAUFF.CODICE_PADRE AS CODICE_PADRE,
                ANAUFF.TIPOUFFICIO AS TIPOUFFICIO,
                ANAUFF.LIVELLOVIS AS LIVELLOVIS,
                ANAUFF.LIVELLOVIS AS LIVELLOVIS_ORIG,
                ANAUFF.UFFDES AS DESCRIZIONEUFFICIO,
                ANAUFF.TIPOUFFICIO AS TIPOUFFICIO
            FROM
                ANAUFF ANAUFF
            WHERE 
                ANAUFF.CODICE_PADRE='$key'";

        try {
            $childs = itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            return false;
        }

        if (!$childs) {
            return;
        }

        foreach ($childs as $key => $node) {
            if ($livelloVisibilita < $node['LIVELLOVIS']) {
                self::insertVisNodeDown($node, $arrayVisibilita, $ruoliUtenteProprietario);
            }
            self::explodeVisNode($node, $livelloVisibilita, $arrayStruttura, $arrayVisibilita, $ruoliUtenteProprietario);
        }
    }

    /**
     * Visibilita da organigramma per ufficio di tipo ruolo
     * @param type $codiceSoggetto
     * @param type $extraParam
     * @return boolean|array
     */
    public static function getVisibilitaRuoliFromCodiceSoggetto($codiceSoggetto, $extraParam = array()) {

        $proLib = new proLib();
        $arrayRoots = array();
        $arrayStruttura = array();
        $arrayVisibilita = array();

        if ($extraParam['FILTRA_RUOLO']) {
            $filtroRuolo = " AND UFFDES.UFFCOD='{$extraParam['FILTRA_RUOLO']}'";
        }

        $sql = $sqlprop = "
            SELECT
                ANAUFF.UFFCOD AS CODICEUFFICIO,
                ANAUFF.UFFDES AS DESCRIZIONEUFFICIO,
                ANAUFF.CODICE_PADRE AS CODICE_PADRE,
                ANAUFF.TIPOUFFICIO AS TIPOUFFICIO,
                ANAUFF.LIVELLOVIS AS LIVELLOVIS,
                ANAUFF.LIVELLOVIS AS LIVELLOVIS_ORIG,
                '1' AS PROPRIETARIO,
                UFFDES.UFFPROTECT AS LIVELLOPROTEZIONE,                
                ANAUFF_PADRE.LIVELLOVIS AS LIVELLOVIS_PADRE,
                ANAUFF_PADRE.UFFDES AS DESCRIZIONEUFFICIO_PADRE,
                ANAUFF_PADRE.TIPOUFFICIO AS TIPOUFFICIO_PADRE
            FROM
                UFFDES UFFDES
            LEFT OUTER JOIN
                ANAUFF ANAUFF ON UFFDES.UFFCOD = ANAUFF.UFFCOD
            LEFT OUTER JOIN
                ANAUFF ANAUFF_PADRE ON ANAUFF_PADRE.UFFCOD = ANAUFF.CODICE_PADRE
            WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codiceSoggetto' ";
        $sql .= " $filtroRuolo";

        try {
            $ruoliProprietario = itaDB::DBSQLSelect($proLib->getPROTDB(), $sqlprop, true);
            $ruoli = itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            return false;
        }
        /*
         * Salvo chiavi ruoli di cui l'utente è proprietario
         */
        $ruoliUtenteProprietario = array();
        foreach ($ruoliProprietario as $ruolo) {
            $ruoliUtenteProprietario[$ruolo['CODICEUFFICIO']] = $ruolo['PROPRIETARIO'];
        }


        /*
         * Caricamento Unità operativa/ruolo proprietario
         * 
         */
        foreach ($ruoli as $ruolo) {
            self::insertVisNodeDown($ruolo, $arrayVisibilita, $ruoliUtenteProprietario);
        }

        /*
         * Caricamento Unità operativa di partenza
         * 
         */
        foreach ($ruoli as $ruolo) {
            self::insertVisNodeUp($ruolo, $arrayRoots);
        }

        /*
         * Sviluppo delle unità operative/ ruolo figli
         * 
         */
        foreach ($arrayRoots as $root) {
            /*
             * Inserico la root da esplodere
             */
            self::insertVisNodeDown($root, $arrayVisibilita, $ruoliUtenteProprietario);

            self::explodeVisNode($root, $root['LIVELLOVIS'], $arrayStruttura, $arrayVisibilita, $ruoliUtenteProprietario);
        }
        return $arrayVisibilita;
    }

    public static function getRuoliFromCodiceSoggetto($codiceSoggetto) {
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        $proLib = new proLib();
        /**
         *  aggiunto filtro cessazione ruolo 09/01/2015
         */
        $sql = "
            SELECT
                ANAUFF.UFFCOD AS CODICEUFFICIO,
                ANAUFF.UFFDES AS DESCRIZIONEUFFICIO,
                ANAUFF.UFFSEGSER AS UFFSEGSER,
                ANAUFF.UFFSEGCLA AS UFFSEGCLA,
                ANARUOLI.RUOCOD AS RUOCOD,                
                ANARUOLI.RUODES AS RUOLO,
                UFFDES.UFFPROTECT AS LIVELLOPROTEZIONE
            FROM
                UFFDES UFFDES
            LEFT OUTER JOIN
                ANAUFF ANAUFF ON UFFDES.UFFCOD = ANAUFF.UFFCOD
            LEFT OUTER JOIN
                ANARUOLI ANARUOLI ON UFFDES.UFFFI1__2 = ANARUOLI.RUOCOD
            WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codiceSoggetto'";
        try {
            $ruoli = itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }

        return $ruoli;
    }

    public static function getSecureWhereIndiceFromIdUtente($proLib, $tipo = '') {
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        $proLib = new proLib();
        $codiceSoggetto = self::getCodiceSoggettoFromIdUtente();
        $orgLayout = proOrgLayout::caricaLayout();
        $ruoli = self::getRuoliFromCodiceSoggetto($codiceSoggetto);
        $profilo = self::getProfileFromIdUtente();

        /*
         * Semplificare come su protocollo
         * 
         */
        $query_arr = array();
        switch ($profilo['VIS_SEGRETERIA']) {
            case 'ENTE':
                $query_arr[] = "1=1";
//                $query_arr[] = "(ARCITE.ITEORGKEY LIKE '" . $proLib->getAOOAmministrazione() . ".%') ";
                break;
            case 'UFFICIO':
                foreach ($ruoli as $ruolo) {
                    $query_arr[] = "(ARCITE.ITEUFF = '" . $ruolo['CODICEUFFICIO'] . "') ";
                }
                break;
            case 'SOGGETTO':
                $query_arr[] = "(ARCITE.ITEDES = '" . $codiceSoggetto . "') ";
                break;
        }

        $codizioneRuolo = '';
        if ($query_arr) {
            $codizioneRuolo = " AND (" . implode(" OR ", $query_arr) . ") ";
        } else {
// TODO: Condizionare se visibile per AOO --> visualizza tutto
// altrimenti non visualizza nulla            
            return " 1=0 ";
        }
        $where_profilo = " ARCITE.ITENODO<>'MAN' AND ARCITE.ITENODO<>'ANN' AND  (((ARCITE.ITENODO='ASX' OR ARCITE.ITENODO='MIT' OR ARCITE.ITENODO='ASS' OR ARCITE.ITENODO='INS' OR ARCITE.ITENODO='TRX') $codizioneRuolo OR (ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto'))";
        $where_profilo .= " AND (ANAPRO.PRORISERVA <> '1' OR ((ARCITE.ITENODO='INS' OR ARCITE.ITENODO='ASS' OR ARCITE.ITENODO='TRX') AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1))";
        /**
         * da testare
         */
//$where_profilo .= " AND (ANAPRO.PROSECURE <= " . $profilo['LIV_PROTEZIONE'] . " OR ARCITE.ITEDES='$codiceSoggetto')";        
//        $where_profilo .= " AND (ANAPRO.PRORISERVA <> '1' OR (ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1) OR (ARCITE.ITENODO='ASS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1))";
        $where_profilo .= ")";
//        App::log('$where_profilo');
//        App::log($where_profilo);
        return $where_profilo;
    }

    public static function getSecureWhereFromIdUtente($proLib, $tipo = '', $extraParam = array()) {
        $cond_trx = '';
        $chiave_vis = "VIS_PROTOCOLLO";
        if ($tipo == 'fascicolo') {
            $chiave_vis = 'VIS_FASCICOLO';
        }
        include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
        $proLib = new proLib();
        $anaent_33 = $proLib->GetAnaent('33');
        if (!$anaent_33) {
            $tipoSecure = "02";
        } else {
            $tipoSecure = $anaent_33['ENTDE2'];
        }
        if ($tipoSecure != "01" && $tipoSecure != "02") {
            $tipoSecure = "02";
        }

        /* Lettura Parametro per Accesso A Protocolli Trasmessi. (TRX) */
        $anaent_48 = $proLib->GetAnaent('48');

        $codiceSoggetto = self::getCodiceSoggettoFromIdUtente();
        $ruoli = self::getVisibilitaRuoliFromCodiceSoggetto($codiceSoggetto, $extraParam);
        //Out::msgInfo("ruoli", print_r($ruoli, true));

        $profilo = self::getProfileFromIdUtente();
        $filtroRuolo = '';
        if (isset($extraParam['FILTRA_RUOLO']) && $extraParam['FILTRA_RUOLO'] != '') {
            $filtroRuolo = " AND ITEUFF = '" . $extraParam['FILTRA_RUOLO'] . "' ";
        }
        if ($tipoSecure == "02") {
            $query_arr = array();
            switch ($profilo[$chiave_vis]) {
                case 'ENTE':
                    $query_arr[] = "( ITEANNULLATO <> 1 )"; // Tutti, purchè gli iter siano attivi e non annullati
                    break;
                case 'SETTORE':
                case 'UFFICIO':
                    /*
                     *  Tutti quelli degli uffici di appartenenza,
                     *  purchè siano iter attivi e non annullati
                     */
                    foreach ($ruoli as $ruolo) {
                        if (!$ruolo['LIVELLOPROTEZIONE']) {
                            $ruolo['LIVELLOPROTEZIONE'] = 1;
                        }
                        $privacyFiltro = '';
                        if ($ruolo['PROPRIETARIO'] != '1') {
                            $privacyFiltro = " AND ITEPRIVACY<>1";
                        }
                        $query_arr[] = "( ARCITE.ITEUFF='{$ruolo['CODICEUFFICIO']}' AND ITEANNULLATO <> 1 $privacyFiltro )";
                    }
                    $query_arr[] = "( ARCITE.ITEUFF='' AND ARCITE.ITEDES='$codiceSoggetto'  AND ITEANNULLATO <> 1 )";
                    if (!isset($extraParam['FILTRA_RUOLO']) || $extraParam['FILTRA_RUOLO'] == '') {
                        $query_arr[] = "( ARCITE.ITEDES='$codiceSoggetto'  AND ITEANNULLATO <> 1 )";
                    }
                    break;

                case 'SOGGETTO':
                default:
                    $query_arr[] = "( ARCITE.ITEDES='$codiceSoggetto'  AND ITEANNULLATO <> 1 )"; // solo quelli del soggetto. purchè siano iter attivi e non annullati
                    break;
            }

            $codizioneRuolo = '';
            if ($query_arr) {
                $codizioneRuolo = " AND (" . implode(" OR ", $query_arr) . ") ";
            } else {
                return " 1=0 ";
            }

            if ($tipo == 'fascicolo') {
                $cond_trx = " OR ARCITE.ITENODO='TRX'";
                $cond_trx .= " OR ARCITE.ITENODO='ASF'";
            }
            $cond_trx .= " OR ARCITE.ITENODO='ASF' ";
            $cond_trx_riservato = '';
            if ($tipo == 'vedi_trasmessi' || $anaent_48['ENTDE5']) {
                $cond_trx .= " OR ARCITE.ITENODO='TRX'";
                $cond_trx_riservato = " OR (ARCITE.ITENODO='TRX' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1 $filtroRuolo ) ";
            }
            $where_profilo = " ARCITE.ITENODO<>'MAN' AND ARCITE.ITENODO<>'ANN' AND  (((ARCITE.ITENODO='ASX' OR ARCITE.ITENODO='MIT' OR ARCITE.ITENODO='ASS' OR ARCITE.ITENODO='INS'$cond_trx) $codizioneRuolo OR (ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' $filtroRuolo))";

            /*
             *  Se abilitata visibilità oggetti riservati, e può vedere oggetti protocolli riservati..
             */
            if ($profilo['OGGRIS_VISIBILITA'] === 1 && $extraParam['VEDI_OGGRISERVATI'] === 1) {
                $profilo['VIS_RISERVATI'] = 1;
            }

            /*
             * 
             */
            if ($profilo['VIS_RISERVATI'] !== 1) {
                $where_profilo .= "AND 
                    (ANAPRO.PRORISERVA <> '1' 
                    OR (ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1 $filtroRuolo) 
                    OR (ARCITE.ITENODO='ASS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1 $filtroRuolo) 
                    OR (ARCITE.ITENODO='MIT' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1 $filtroRuolo)
                    $cond_trx_riservato
                )";
            }
            /*
             * 
             * 
             */
            if ($tipo == 'fascicolo') {
                $where_profilo .= " AND ITEANNULLATO <> 1 ";
            }

            /*
             *  Scadenziario
             */
            if ($tipo == 'vedi_trasmessi' || $anaent_48['ENTDE5']) {
                $where_profilo .= " AND ITEANNULLATO <> 1 ";
            }

            /*
             * TSO 
             */
            if ($profilo['VIS_TSO'] !== 1) {
                $where_profilo .= " AND 
                    (ANAPRO.PROTSO = 0 OR ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' $filtroRuolo)";
            }
            $where_profilo .= ")";
        } else {
            if ($ruoli) {
                $where_profilo = " (UFFPRO.UFFCOD <> UFFPRO.UFFCOD";
                foreach ($ruoli as $ruoloSogg) {
                    $ufficio = $ruoloSogg['CODICEUFFICIO'];
                    if (trim($ufficio) != '') {
                        $where_profilo .= " OR UFFPRO.UFFCOD = '$ufficio'";
                    }
                }
                if ($profilo[$chiave_vis] == "ENTE") {
                    $where_profilo .= " OR 1=1";
                }

                if ($profilo['VIS_RISERVATI'] !== 1) {
                    $where_profilo .= " AND (ANAPRO.PRORISERVA <> '1' OR (ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1) OR (ARCITE.ITENODO='ASS' AND ARCITE.ITEDES='$codiceSoggetto' AND ITEANNULLATO <> 1))";
                }

                if ($profilo['VIS_TSO'] !== 1) {
                    $where_profilo .= " AND (ANAPRO.PROTSO = 0 OR ARCITE.ITENODO='INS' AND ARCITE.ITEDES='$codiceSoggetto' $filtroRuolo )";
                }
                $where_profilo .= " OR PROUTE='" . $profilo['UTELOG'] . "')";
            } else {
                $where_profilo .= " 1=0 OR PROUTE='" . $profilo['UTELOG'] . "'";
            }
        }

        return $where_profilo;
    }

    /**
     * 
     * @param type $proLib
     * @param type $tipo    Tipologia di verifica permesso:
     *                      codice      = seleziona un elemento singolo per numero e tipo prot e controlla le regole arcite.
     *                      segnatura   = seleziona un elemento singolo per segnatura e controlla le regole arcite.
     *                      fascicolo   = seleziona tutti i sotto elementi di un elemento e per ognuno controlla le regole di arcite.  
     * @param type $codice
     * @param type $tipoProt
     * @return array o false 
     *         se tipo = fascicolo ritorna tabella anapro visibili
     *         altrimenti ritorna record anapro visibile     
     */
    public static function getSecureAnaproFromIdUtente($proLib, $tipo = 'codice', $codice = '', $tipoProt = '') {

        $multi = false;
        $tipoWhere = '';
        $sql = "
            SELECT
                ANAPRO.*
            FROM
                ANAPRO ANAPRO
             LEFT OUTER JOIN UFFPRO UFFPRO ON ANAPRO.PRONUM=UFFPRO.PRONUM AND ANAPRO.PROPAR=UFFPRO.UFFPAR
             LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR";
        if ($tipo == 'codice') {
            $sql .= " WHERE ANAPRO.PRONUM = '$codice'";
            if ($tipoProt != 'X') {
                $sql .= " AND (ANAPRO.PROPAR = '$tipoProt' )"; // OR ANAPRO.PROPAR = '" . $tipoProt . "A') ";
            } else {
                $sql .= " AND (ANAPRO.PROPAR = 'P' OR ANAPRO.PROPAR = 'A')";
            }
        } elseif ($tipo == 'segnatura') {
            $sql .= " WHERE ANAPRO.PROSEG = '$codice'";
        } elseif ($tipo == 'fascicolo') {
            $multi = true;
            $tipoWhere = 'fascicolo';
            if ($tipoProt) {
                $sql .= " WHERE ANAPRO.PRONUM = '$codice' AND ";
            } else {
                $sql .= " WHERE ANAPRO.PROFASKEY='$codice' AND ";
            }
            $sql .= "(ANAPRO.PROPAR='F' OR ANAPRO.PROPAR='N' OR ANAPRO.PROPAR='T' OR ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C' )";
        } else {
            $sql .= " WHERE ANAPRO.ROWID='$codice'";
        }

        $sql .= " AND " . self::getSecureWhereFromIdUtente($proLib, $tipoWhere);
        $sql .= " GROUP BY ANAPRO.ROWID";
        return ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, $multi);
    }

    public static function getIterStato($prot, $tipoProt, $utecod = '') {
        if (!$utecod) {
            $utecod = self::getCodiceSoggettoFromNomeUtente();
        }
        $proLib = new proLib();

        $retIter = array();
        $retIter['GESTIONE'] = false;
        $retIter['VISIONE'] = false;
        $retIter['RESPONSABILE'] = false;
        /*
         * Prendo gli arcite del protocollo con destinatari l'utente.
         */
        $sql = "SELECT * FROM ARCITE ";
        $sql .= " WHERE ITEPRO = $prot AND ITEPAR = '$tipoProt' AND ITEDES = '$utecod' AND ITEANNULLATO <> 1 ";
        $Arcite_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        App::log($Arcite_tab);
        /*
         * Se trova qualcosa ha almeno la visione del protocollo.
         */
        if ($Arcite_tab) {
            $retIter['VISIONE'] = true;
        }
        foreach ($Arcite_tab as $Arcite_rec) {
            if ($Arcite_rec['ITEGES'] == 1) {
                $retIter['GESTIONE'] = true;
                break;
            }
        }
        /*
         * Controllo se l'utente è il responsabile/firmatario del protocollo (P/C):
         */
        $sql = "SELECT * FROM ANADES WHERE DESNUM = $prot AND DESPAR = '$tipoProt' AND DESTIPO = 'M' AND DESCOD = '$utecod' ";
        $AnadesResp_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        if ($AnadesResp_rec) {
            $retIter['RESPONSABILE'] = true;
        }

        return $retIter;
    }

}