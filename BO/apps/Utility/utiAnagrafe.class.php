<?php

include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

class utiAnagrafe {

    function __construct() {
        
    }

    function __destruct() {
        
    }

    public static function getAnagrafeProvider() {
        $devLib = new devLib();
        $Anagrafe_parm_rec = $devLib->getEnv_config('CONNESSIONI', 'codice', 'ANAGRAFE', false);
        $provider = $Anagrafe_parm_rec['CONFIG'];
        switch ($provider) {
            case "WebService":
                $provider_type = "SolWS";
                break;
            case "Italsoft":
                $provider_type = "Italsoft";
                break;
            case "CityWare":
                $provider_type = "CityWare";
                break;
            case "CityWareOnLine":
                $provider_type = "CityWareOnLine";
                break;
            case "SolClient":
                $provider_type = "SolWS";
                break;
        }
        include_once(ITA_LIB_PATH . '/itaPHPAnagrafe/itaAnagrafe.class.php');
        $Anagrafe = itaAnagrafe::getProviderInstance($provider_type);
        return $Anagrafe;
    }
    
    static function ricAnagrafe($returnModel, $ricParm, $retid = '', $returnEvent = 'returntoform') {
        $dati = self::getAnagrafeProvider()->getCittadiniLista($ricParm);
        if (!$dati) {
            Out::msgInfo("Ricerca Soggetti Anagrafe.", "Nessun Record Trovato");
            return;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Anagrafiche',
            "width" => '550',
            "height" => '470',
            //"sortname" => 'NOME',
            "rowNum" => '10000000',
            "rowList" => '[]',
            "navGrid" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "colNames" => array(
                "Nominativo",
                "Nato il",
                "Codice Fiscale",
            ),
            "colModel" => array(
                array("name" => 'NOMINATIVO', "width" => 200),
                array("name" => 'DATANASCITA', "formatter" => "eqdate", "width" => 80),
                array("name" => 'CODICEFISCALE', "width" => 100),
            ),
            'arrayTable' => $dati,
            "filterToolbar" => 'false'
        );

        //$filterName = array('NOME', 'CODICEFISCALE');
        $_POST = array();
        $_POST['event'] = 'openform';
        //$_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
  	$model();
    }

    static function controllaAnagrafe($ricParm) {

        $dati = self::getAnagrafeProvider()->getCittadiniLista($ricParm);
        if (!$dati) {
            Out::msgInfo("Ricerca Soggetti Anagrafe.", "Nessun Record Trovato");
            return false;
        }
        try { // Apro il DB
            $ANEL_DB = ItaDB::DBOpen('ANEL');
        } catch (Exception $e) {
            App::log($e->getMessage());
            return false;
        }

        $Anagra_rec = $dati[0];
        if (!$Anagra_rec) {
            Out::msgStop('Errore di Selezione', 'Soggetto Non Residente o Codice Fiscale Errato.');
            return false;
        }
        if ($Anagra_rec['AIRE'] != '') {
            Out::msgStop('Errore di Selezione', 'Soggetto Non Residente Iscritto AIRE');
            Return false;
        }
        if ($Anagra_rec['DECESSO'] != '') {
            Out::msgStop('Errore di Selezione', 'Soggetto Deceduto');
            Return false;
        }
        if ($Anagra_rec['DATAEMIGRAZIONE'] != '') {
            if ($Anagra_rec['DATAEMIGRAZIONE'] > $Anagra_rec['DATAIMMIGRAZIONE']) {
                Out::msgStop('Errore di Selezione', 'Soggetto Non più Residente.');
                Return false;
            }
        }

        return True;
    }

    static function datiAnagrafe($ricParm) {

        $dati = self::getAnagrafeProvider()->getCittadiniLista($ricParm);
        if (!$dati) {
            Out::msgInfo("Ricerca Soggetti Anagrafe.", "Nessun Record Trovato");
            return false;
            ;
        }
        $Anagra_rec = $dati[0];
        if (!$Anagra_rec) {
            $datiAnagrafe['STATO'] = "NON TROVATO";
            $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non Residente o Codice Fiscale Errato.';
            return $datiAnagrafe;
        }
        if ($Anagra_rec['AIRE'] != '') {
            $datiAnagrafe['STATO'] = "AIRE";
            $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non Residente Iscritto AIRE';
            Return $datiAnagrafe;
        }
        if ($Anagra_rec['DECESSO'] != '' && $Anagra_rec['DECESSO'] != '00000000' ) {
            $datiAnagrafe['STATO'] = "DECEDUTO";
            $datiAnagrafe['MESSAGGIO'] = 'Soggetto Deceduto';
            $datiAnagrafe['DATADECESSO'] = substr($Anagra_rec['DATADECESSO'],6,2)."/".substr($Anagra_rec['DATADECESSO'],4,2)."/".substr($Anagra_rec['DATADECESSO'],0,4);
            Return $datiAnagrafe;
        }
        if ($Anagra_rec['DATAEMIGRAZIONE'] != '') {
            $datiAnagrafe['STATO'] = "EMIGRATO";
            $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non più Residente';
            $datiAnagrafe['DATAEMIGRAZIONE'] = substr($Anagra_rec['DATAEMIGRAZIONE'],6,2)."/".substr($Anagra_rec['DATAEMIGRAZIONE'],4,2)."/".substr($Anagra_rec['DATAEMIGRAZIONE'],0,4);

            Return $datiAnagrafe;
        }

//        try {
//            $ANEL_DB = ItaDB::DBOpen('ANEL');
//        } catch (Exception $e) {
//            App::log($e->getMessage());
//            return false;
//        }
//        $sql = "SELECT DISTINCT
//                    ANAGRA.ROWID AS ROWID,
//                    ANAGRA.GGMOR AS GGMOR,
//                    LAVORO.FISCAL AS FISCAL
//                    FROM ANAGRA LEFT OUTER JOIN LAVORO ON LAVORO.CODTRI=ANAGRA.CODTRI WHERE LAVORO.FISCAL = '" . $codiceFiscale . "'";
//        try {
//            $Anagra_rec = ItaDB::DBSQLSelect($ANEL_DB, $sql, false);
//            IF (!$Anagra_rec) {
//                $datiAnagrafe['STATO'] = "NON TROVATO";
//                $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non Residente o Codice Fiscale Errato.';
//                Return $datiAnagrafe;
//            }
//            if ($Anagra_rec['AUSILI'] <> '') {
//                $datiAnagrafe['STATO'] = "AIRE";
//                $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non Residente Iscritto AIRE';
//                Return $datiAnagrafe;
//            }
//            if ($Anagra_rec['GGMOR'] <> 0) {
//                $datiAnagrafe['STATO'] = "AIRE";
//                $datiAnagrafe['MESSAGGIO'] = 'Soggetto Deceduto';
//                $datiAnagrafe['DATADECESSO'] = str_pad($Anagra_rec['GGMOR'], 2, 0, STR_PAD_RIGHT) . "/" .
//                        str_pad($Anagra_rec['MMMOR'], 2, 0, STR_PAD_RIGHT) . "/" .
//                        str_pad($Anagra_rec['AAMOR'], 2, 0, STR_PAD_RIGHT);
//                Return $datiAnagrafe;
//            }
//        } catch (Exception $e) {
//            Out::msgStop('Errore DB', $e->getMessage());
//            return false;
//        }
//
//        $sql = "SELECT DISTINCT
//                    IMMIGR.ROWID AS ROWID,
//                    (IMMIGR.AAEMIG*10000+IMMIGR.MMEMIG*100+IMMIGR.GGEMIG) AS DATAEMIGRAZIONE,
//                    (IMMIGR.AAIMMI*10000+IMMIGR.MMIMMI*100+IMMIGR.GGIMMI) AS DATAIMMIGRAZIONE,
//                    LAVORO.FISCAL AS FISCAL
//                    FROM IMMIGR LEFT OUTER JOIN LAVORO ON LAVORO.CODTRI=IMMIGR.CODTRI WHERE AAEMIG<>0 AND LAVORO.FISCAL = '" . $codiceFiscale . "'";
//        try {
//            $Immigr_rec = ItaDB::DBSQLSelect($ANEL_DB, $sql, false);
//            if ($Immigr_rec['DATAEMIGRAZIONE'] > $Immigr_rec['DATAIMMIGRAZIONE']) {
//                $datiAnagrafe['STATO'] = "EMIGRATO";
//                $datiAnagrafe['MESSAGGIO'] = 'Soggetto Non più Residente';
//                $datiAnagrafe['DATAEMIGRAZIONE'] = str_pad($Anagra_rec['GGEMIG'], 2, 0, STR_PAD_RIGHT) . "/" .
//                        str_pad($Anagra_rec['MMEMIG'], 2, 0, STR_PAD_RIGHT) . "/" .
//                        str_pad($Anagra_rec['AAEMIG'], 2, 0, STR_PAD_RIGHT);
//                Return $datiAnagrafe;
//            }
//        } catch (Exception $e) {
//            Out::msgStop('Errore DB', $e->getMessage());
//            return false;
//        }
//
        $datiAnagrafe['STATO'] = "RESIDENTE";
        $datiAnagrafe['MESSAGGIO'] = "Soggetto Residente";

        return True;
    }

    static function datiCittadino($ricParm) {
        $dati = self::getAnagrafeProvider()->getCittadiniLista($ricParm);
        if (!$dati) {
            Out::msgInfo("Ricerca Soggetti Anagrafe.", "Nessun Record Trovato");
            return false;
            ;
        }
        $Anagra_rec = $dati[0];

        return $Anagra_rec;
    }
}

?>