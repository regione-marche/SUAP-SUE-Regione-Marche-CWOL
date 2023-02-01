<?php

/* * 
 *
 * Utiity ricerche fascicoli elettronici
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    14.10.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';

class praRic {

    static function ricComboFunzioni_base($idCombo) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
        $Funzione_passi_tab = praFunzionePassi::$FUNZIONI_BASE;
        Out::select($idCombo, 1, "", "", "Nessuna Funzione Specifica");
        $i = 0;

        foreach ($Funzione_passi_tab as $key => $Funzione_passi_rec) {
            Out::select($idCombo, 1, $key, "", $Funzione_passi_rec['DESCRIZIONE']);
        }
    }

    static function ricComboFunzioni_front_office($idCombo) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
        $Funzione_passi_tab = praFunzionePassi::$FUNZIONI_FRONT_OFFICE;
        Out::select($idCombo, 1, "", "", "Nessuna Funzione Specifica");
        $i = 0;

        foreach ($Funzione_passi_tab as $key => $Funzione_passi_rec) {
            Out::select($idCombo, 1, $key, "", $Funzione_passi_rec['DESCRIZIONE']);
        }
    }

    static function ricAccountSportelli($returnModel, $returnEvent = 'returnAccountSportelli', $returnId = '') {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        /* @var $PRAM_DB ITA_DB */
        $PRAM_DB = $praLib->getPRAMDB();
        $sql = "
            SELECT * FROM (
                SELECT
                    " . $PRAM_DB->strConcat("'S-'", "ANATSP.ROWID") . " AS ROWID,
                    ANATSP.TSPPEC AS TSPPEC,
                    ANATSP.TSPDES AS TSPDES 
                FROM 
                    ANATSP 
                WHERE TSPPEC <>'' 
                UNION 
                SELECT
                    " . $PRAM_DB->strConcat("'A-'", " ANASPA.ROWID") . " AS ROWID,
                    ANASPA.SPAPEC AS TSPPEC,
                    ANASPA.SPADES AS TSPDES 
                FROM 
                    ANASPA ANASPA
                WHERE SPAPEC <>''
                ) SPORTELLI GROUP BY SPORTELLI.TSPPEC
            ";
        $anatsp_tab = $praLib->getGenericTab($sql);
        $matrice = array();
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        foreach ($anatsp_tab as $anatsp_rec) {
            $mail_account = $emlLib->getMailAccount($anatsp_rec['TSPPEC']);
            if ($mail_account) {
                $matrice[$anatsp_rec['ROWID']] = $anatsp_rec;
            }
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Account Email sportelli On-line',
            "width" => '550',
            "height" => '470',
            "sortname" => 'TSPPEC',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $matrice,
            "colNames" => array(
                "Sportello",
                "Indirizzo Email"
            ),
            "colModel" => array(
                array("name" => 'TSPDES', "width" => 200),
                array("name" => 'TSPPEC', "width" => 300)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnClosePortlet'] = true;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricEnteMaster($returnModel, $returnEvent = 'returnEnteMaster') {

        $enti = App::getEnti();
        $arrayTable = array();
        foreach ($enti as $keyEnte => $propsEnte) {
            if (App::$utente->getKey('ditta') != $propsEnte['codice']) {
                $arrayTable[] = array();
                $arrayTable[count($arrayTable) - 1]['CODICE'] = $propsEnte['codice'];
                $arrayTable[count($arrayTable) - 1]['DESCRIZIONE'] = $keyEnte;
            }
        }
        $model = 'utiRicDiag';
        $colonneNome = array(
            "CODICE",
            "DESCRIZIONE",
        );
        $colonneModel = array(
            array("name" => 'CODICE', "width" => 100),
            array("name" => 'DESCRIZIONE', "width" => 300)
        );
        $gridOptions = array(
            "Caption" => 'Enti Disponibili',
            "width" => '500',
            "height" => '470',
            "rowNum" => '200',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "arrayTable" => $arrayTable
        );


        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = "enteMaster";
        $_POST['returnKey'] = 'retKey';
        $_POST['returnColumn'] = 'CODICE';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     *
     * @param type $matrice
     * @param type $returnModel
     * @param type $returnEvent
     * @param type $adjacency
     */
    static function ricVariabili($matrice, $returnModel, $returnEvent = 'returnVariabili', $adjacency = false) {
        if ($adjacency == true) {
            $model = 'utiRicDiag';
            $gridOptions = array(
                "Caption" => 'Elenco Variabili',
                "width" => '650',
                "height" => '430',
                "sortname" => "valore",
                "sortorder" => "desc",
                "rowNum" => '10000',
                "rowList" => '[]',
                "treeGrid" => 'true',
                "ExpCol" => 'descrizione',
                "arrayTable" => $matrice,
                "colNames" => array(
                    "varidx",
                    "Descrizione",
                    "Variabile"
                ),
                "colModel" => array(
                    array("name" => 'varidx', "width" => 200, "hidden" => "true", "key" => "true"),
                    array("name" => 'descrizione', "width" => 400),
                    array("name" => 'markupkey', "width" => 200)
                )
            );
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = $returnModel;
            $_POST['returnEvent'] = $returnEvent;
            if ($returnEvent != '')
                $_POST['returnEvent'] = $returnEvent;
            $_POST['retid'] = '';
            $_POST['returnKey'] = 'retKey';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        } else {
            $model = 'utiRicDiag';
            $gridOptions = array(
                "Caption" => 'Elenco Variabili',
                "width" => '450',
                "height" => '430',
                "sortname" => "valore",
                "sortorder" => "desc",
                "rowNum" => '200',
                "rowList" => '[]',
                "arrayTable" => $matrice,
                "colNames" => array(
                    "varidx",
                    "descrizione"
                ),
                "colModel" => array(
                    array("name" => 'varidx', "width" => 200),
                    array("name" => 'descrizione', "width" => 200)
                )
            );

            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = $returnModel;
            $_POST['returnEvent'] = $returnEvent;
            if ($returnEvent != '')
                $_POST['returnEvent'] = $returnEvent;
            $_POST['retid'] = '';
            $_POST['returnKey'] = 'retKey';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        }
    }

    static function ricAllegatiWs($PRAMDB, $matrice, $returnModel, $returnEvent = 'returnAllegatiWs', $infoDetail = "") {
        $sql = "SELECT * FROM PASDOC WHERE (ROWID = -1";
        foreach ($matrice as $record) {
            $sql .= " OR ROWID = " . $record;
        }
        $sql .= ")";
        $AllegatiProtocollo = ItaDB::DBSQLSelect($PRAMDB, $sql, true);

        //Ciclo per cambiare estensioni ai pdf e p7m dei testi base sulla tabella
        $praLib = new praLib();
        $tab_allegati = array();
        foreach ($AllegatiProtocollo as $key => $allegato) {
            if ($allegato['PASCLA'] == "TESTOBASE") {
                $passoPath = $praLib->SetDirectoryPratiche(substr($allegato['PASKEY'], 0, 4), $allegato['PASKEY'], "PASSO", false);
                if (file_exists($passoPath . "/" . pathinfo($allegato['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                    $AllegatiProtocollo[$key]['PASNAME'] = pathinfo(strip_tags($allegato['PASNAME']), PATHINFO_FILENAME) . ".pdf.p7m.p7m";
                } elseif (file_exists($passoPath . "/" . pathinfo($allegato['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                    $AllegatiProtocollo[$key]['PASNAME'] = pathinfo(strip_tags($allegato['PASNAME']), PATHINFO_FILENAME) . ".pdf.p7m";
                } else if (file_exists($passoPath . "/" . pathinfo($allegato['PASFIL'], PATHINFO_FILENAME) . ".pdf")) {
                    $AllegatiProtocollo[$key]['PASNAME'] = pathinfo(strip_tags($allegato['PASNAME']), PATHINFO_FILENAME) . ".pdf";
                }
            }
            $tab_allegati["_" . $allegato['ROWID']]['ROWID'] = $AllegatiProtocollo[$key]['ROWID'];
            $tab_allegati["_" . $allegato['ROWID']]['PASNAME'] = $AllegatiProtocollo[$key]['PASNAME'];
            $tab_allegati["_" . $allegato['ROWID']]['PASNOT'] = $AllegatiProtocollo[$key]['PASNOT'];
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Allegati',
            "width" => '650',
            "height" => '350',
            "rowNum" => '10000000',
            "rowList" => '[]',
            "navGrid" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'true',
            "sortablerows" => 'true',
            "arrayTable" => $tab_allegati,
            "colNames" => array(
                "Nome",
                "Note"
            ),
            "colModel" => array(
                array("name" => 'PASNAME', "width" => 250, "sortable" => "false"),
                array("name" => 'PASNOT', "width" => 340, "sortable" => "false")
            ),
//            "dataSource" => array(
//                'sqlDB' => 'PRAM',
//                'sqlQuery' => $sql
//            ),
            "filterToolbar" => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '') {
            $_POST['returnEvent'] = $returnEvent;
        }
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnapra($returnModel, $titolo, $retid, $where = '', $dbSuffix = "", $flValidi = false) {
        //$sql = "SELECT * FROM ANAPRA";
        if ($dbSuffix) {
            $praLibMaster = new praLib($dbSuffix);
            $PRAM_DB = $praLibMaster->getPRAMDB();
        } else {
            $praLib = new praLib();
            $PRAM_DB = $praLib->getPRAMDB();
        }

        /*
         * Cambiate le Join nella query. Ora prendono la classificazione da ITEEVT e non da ANAPRA
         */
        $sql = "SELECT
                  SETDES,
                  ATTDES,
                  TIPDES,
                  TSPDES,
                  EVTDESCR,
                  ITEEVT.ROWID AS ID_ITEEVT,
                  ANAPRA.ROWID AS ID_ANAPRA," .
                $PRAM_DB->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS DESCPROC,
                  ANAPRA.PRANUM,
                  ANAPRA.PRADVA,
                  ANAPRA.PRAAVA
                FROM 
                  ANAPRA ANAPRA
                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
                LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
                LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
                WHERE 1=1 
               ";

        if ($flValidi) {
            $oggi = date('Ymd');
            $sql .= " AND (PRADVA<=$oggi OR PRADVA='') AND (PRAAVA>=$oggi OR PRAAVA='')";
        }

        if ($where != '')
            $sql .= ' AND (' . $where . ')';

        if ($dbSuffix) {
            $Anapra_tab = $praLibMaster->getGenericTab($sql);
        } else {
            $Anapra_tab = $praLib->getGenericTab($sql);
        }


        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Evento",
            "Valido dal",
            "al",
            "Sportello",
            "Tipologia",
            "Settore",
            "Attività",
        );
        $colonneModel = array(
            array("name" => 'PRANUM', "width" => 70),
            array("name" => 'DESCPROC', "width" => 350, "classes" => "'ita-Wordwrap'"),
            array("name" => 'EVTDESCR', "width" => 100),
            array("name" => 'PRADVA', "width" => 70, "formatter" => "eqdate"),
            array("name" => 'PRAAVA', "width" => 70, "formatter" => "eqdate"),
            array("name" => 'TSPDES', "width" => 160),
            array("name" => 'TIPDES', "width" => 200),
            array("name" => 'SETDES', "width" => 130),
            array("name" => 'ATTDES', "width" => 200),
        );

//        $dataSource = array(
//            'sqlDB' => 'PRAM',
//            'sqlQuery' => $sql);
//
//        if ($dbSuffix) {
//            $dataSource = array(
//                'sqlDB' => 'PRAM',
//                'dbSuffix' => $dbSuffix,
//                'sqlQuery' => $sql);
//        }


        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '1400',
            "height" => '400',
            "sortname" => 'PRANUM',
            "sortorder" => 'asc',
            "rowNum" => '25',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "arrayTable" => $Anapra_tab,
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
//            "dataSource" => $dataSource
        );

        $filterName = array('PRANUM', 'DESCPROC', 'SETDES', 'ATTDES', 'TIPDES', 'TSPDES');
        $orderAlias = array(
            array("alias" => "DESCPROC", "campo" => "PRADES__1"),
        );


        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['orderAlias'] = $orderAlias;
        $_POST['returnEvent'] = 'returnAnapra';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnapraMulti($returnModel, $retid, $where = '', $dbSuffix = "", $aggregato = "") {
        if ($aggregato) {
            $joinAgg = "INNER JOIN PROGES PROGES ON ANAPRA.PRANUM=PROGES.GESPRO AND PROGES.GESSPA=$aggregato";
        }
        $sql = "SELECT DISTINCT ITEEVT.ROWID,EVTDESCR, SETDES, ATTDES, TIPDES, TSPDES, ANAPRA.PRADES__1, ANAPRA.PRANUM FROM ANAPRA ANAPRA
                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
                LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
                LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
                $joinAgg ";
//        $sql = "SELECT DISTINCT ANAPRA.ROWID,SETDES, ATTDES, TIPDES, TSPDES, ANAPRA.PRADES__1, ANAPRA.PRANUM FROM ANAPRA ANAPRA
//                LEFT OUTER JOIN ANASET ANASET ON ANAPRA.PRASTT=ANASET.SETCOD
//                LEFT OUTER JOIN ANAATT ANAATT ON ANAPRA.PRAATT=ANAATT.ATTCOD
//                LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD
//                LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
//                $joinAgg ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Evento",
            "Sportello",
            "Tipologia",
            "Settore",
            "Attività",
        );
        $colonneModel = array(
            array("name" => 'PRANUM', "width" => 70),
            array("name" => 'PRADES__1', "width" => 350),
            array("name" => 'EVTDESCR', "width" => 100),
            array("name" => 'TSPDES', "width" => 160),
            array("name" => 'TIPDES', "width" => 200),
            array("name" => 'SETDES', "width" => 130),
            array("name" => 'ATTDES', "width" => 200),
        );

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }


        $gridOptions = array(
            "Caption" => "Ricerca Procedimenti",
            "width" => '1300',
            "height" => '470',
            "sortname" => 'PRANUM',
            "rowNum" => '9999999',
            "multiselect" => 'true',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => $dataSource
        );

        $filterName = array('PRANUM', 'PRADES__1', 'SETDES', 'ATTDES', 'TIPDES', 'TSPDES');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnapra';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaset($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANASET";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
        );
        $colonneModel = array(
            array("name" => 'SETCOD', "width" => 100),
            array("name" => 'SETDES', "width" => 300)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Settori...',
            "width" => '500',
            "height" => '470',
            "sortname" => 'SETDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $filterName = array('SETDES');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaset';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaddo($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANADDO";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Denominazione",
            "Codice Fiscale",
            "E-mail",
        );
        $colonneModel = array(
            array("name" => 'DDONOM', "width" => 250),
            array("name" => 'DDOFIS', "width" => 200),
            array("name" => 'DDOEMA', "width" => 200)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Destinazioni',
            "width" => '670',
            "height" => '470',
            "sortname" => 'DDONOM',
            "rowNum" => '20',
            "rowList" => '[]',
            "multiselect" => 'true',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaddo';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicProges($returnModel, $where = '', $retid = '', $infoDetail = 'Seleziona la pratica:', $filtraProtocolloP = "", $ditta = "") {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
        if ($filtraProtocolloP) {
            $sql = "SELECT 
                        PROGES.ROWID AS ROWID, PROGES.GESNUM,PROGES.SERIECODICE,PROGES.SERIEANNO,PROGES.SERIEPROGRESSIVO,PROGES.GESPRA,PROGES.GESNPR,PROGES.GESPRA,PROGES.GESOGG,PROGES.GESDRE,PROGES.GESPRO,PROGES.GESTSP,PROGES.GESSPA,
                        ANAPRA.PRADES__1,
                        (SELECT COUNT(*) FROM PRACOM WHERE PRACOM.COMNUM=PROGES.GESNUM AND PRACOM.COMPRT='$filtraProtocolloP' AND PRACOM.COMTIP='P') AS FL_PRT_PAR,
                        (SELECT COUNT(*) FROM PRACOM WHERE PRACOM.COMNUM=PROGES.GESNUM AND PRACOM.COMPRT='$filtraProtocolloP' AND PRACOM.COMTIP='A') AS FL_PRT_ARR
                    FROM
                        PROGES PROGES
                    LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                    LEFT OUTER JOIN PRACOM PRACOM ON PROGES.GESNUM=PRACOM.COMNUM 
                    WHERE
                        "//COMTIP='P' AND COMPRT='$filtraProtocolloP' ";
                    . "COMPRT='$filtraProtocolloP' OR GESNPR='$filtraProtocolloP'";
            if ($where != '')
                $sql .= ' ' . $where;
            $sql .= " GROUP BY PROGES.ROWID ";
            $sql .= " ORDER BY PROGES.GESNUM DESC";
        } else {
            $sql = "SELECT 
                    PROGES.ROWID AS ROWID, PROGES.GESNUM,PROGES.GESPRA,PROGES.GESNPR,PROGES.GESPRA,PROGES.GESOGG,PROGES.GESDRE,PROGES.GESPRO,PROGES.GESTSP,PROGES.GESSPA,
                    ANAPRA.PRADES__1
            FROM PROGES PROGES
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            WHERE 1 ";
            if ($where != '')
                $sql = $sql . ' ' . $where;
            $sql = $sql . " ORDER BY GESNUM DESC";
        }

        $praLib = new praLib();
        if ($ditta) {
            $praLib = new praLib($ditta);
        }
        // Out::msgInfo("sql555",$sql);
        $Result_tab = $praLib->getGenericTab($sql);
        foreach ($Result_tab as $key => $Result_rec) {
            $praLib = new praLib();
            $Serie_rec = $praLib->ElaboraProgesSerie($Result_rec['GESNUM'], $Result_rec['SERIECODICE'], $Result_rec['SERIEANNO'], $Result_rec['SERIEPROGRESSIVO']);
            //$Result_tab[$key]["PRATICA"] = "<div><b>" . intval(substr($Result_rec['GESNUM'], 4, 6)) . "/" . substr($Result_rec['GESNUM'], 0, 4) . "</b></div>";
            $Result_tab[$key]["PRATICA"] = "<div><b>" . $Serie_rec . "</b></div>";
            $gespra = "<div> </div>";
            if ($Result_rec['GESPRA']) {
                $gespra = "<div style=\"background-color:DodgerBlue;color:white;\"><b>" . intval(substr($Result_rec['GESPRA'], 4, 6)) . "/" . substr($Result_rec['GESPRA'], 0, 4) . "</b></div>";
            }
            $gesnpr = "<div> </div>";
            if ($Result_rec['GESNPR'] != 0) {
                $gesnpr = "<div style=\"color:DodgerBlue;\"><b>" . intval(substr($Result_rec['GESNPR'], 4, 6)) . "/" . substr($Result_rec['GESNPR'], 0, 4) . "</b></div>";
            }
            $Result_tab[$key]["PRATICA"] .= $gespra . $gesnpr;
            //
            $Result_tab[$key]['DATAREG'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\">" . substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4) . "</div>";
            //
            $Anades_rec = $praLib->GetAnades($Result_rec['GESNUM'], "ruolo", false, praRuolo::getSystemSubjectCode("ESIBENTE"));
            if ($Anades_rec) {
                $Result_tab[$key]["INTESTATARIO"] = "<div style =\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div></div>";
            } else {
                $Anades_rec = $praLib->GetAnades($Result_rec['GESNUM'], "ruolo", false);
                $Result_tab[$key]["INTESTATARIO"] = "<div style =\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $Anades_rec['DESNOM'] . "</div><div>" . $Anades_rec['DESTEL'] . "</div></div>";
            }
            //$Result_tab[$key]["DESNOM"] = $Anades_rec['DESNOM'];
            //
            $datiInsProd = $praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab[$key]['IMPRESA'] = "<div class=\"ita-Wordwrap\"><div>" . $datiInsProd['IMPRESA'] . "</div><div>" . $datiInsProd['FISCALE'] . "</div></div>";
            //
            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab[$key]['DESCPROC'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\"><div>" . $anaset_rec['SETDES'] . "</div><div>" . $anaatt_rec['ATTDES'] . "</div><div>" . $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'] . "</div></div>";
            }
            //
            $Result_tab[$key]['GESOGG'] = "<div style=\"height:65px;overflow:auto;\" class=\"ita-Wordwrap\">" . $Result_rec['GESOGG'] . "</div>";
            //
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $praLib->GetAnaspa($Result_rec['GESSPA']);
                $aggregato = $anaspa_rec['SPADES'];
            }
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab[$key]["SPORTELLO"] = "<div class=\"ita-Wordwrap\">" . $anatsp_rec['TSPDES'] . "</div><div>$aggregato</div>";
            }
        }

        $model = 'utiRicDiag';
        $colonneNome = array(
            "Pratica N.<br>Richiesta N.<br>Protocollo N.",
            "Data",
            "Intestatario",
            "Dati Impresa",
            "Procedimento",
            "Oggetto",
            "Sportello on-line/<br>Aggregato",
        );
        $colonneModel = array(
            array("name" => 'PRATICA', "width" => 150),
            array("name" => 'DATAREG', "width" => 80),
            array("name" => 'INTESTATARIO', "width" => 130),
            array("name" => 'IMPRESA', "width" => 130),
            array("name" => 'DESCPROC', "width" => 250),
            array("name" => 'GESOGG', "width" => 200),
            array("name" => 'SPORTELLO', "width" => 150),
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Pratiche',
            "width" => '1200',
            "height" => '470',
            "filterToolbar" => 'true',
            "sortname" => 'GESNUM',
            "sortorder" => "desc",
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $Result_tab,
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
        );
        $filterName = array('PRATICA', 'GESDRE', 'INTESTATARIO', "IMPRESA", 'DESCPROC', "GESOGG", "SPORTELLO");
//        $orderAlias = array(
//            "PRATICA" => "GESNUM",
//            "DATAREG" => "GESDRE",
//            "INTESTATARIO" => "DESNOM",
//            "DESCPROC" => "PRADES__1"
//        );
        $orderAlias = array(
            array("alias" => "PRATICA", "campo" => "GESNUM"),
            array("alias" => "DATAREG", "campo" => "GESDRE"),
            array("alias" => "INTESTATARIO", "campo" => "DESNOM"),
            array("alias" => "DESCPROC", "campo" => "PRADES__1"),
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnProges';
        $_POST['filterName'] = $filterName;
        $_POST['orderAlias'] = $orderAlias;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praPassiAntecedenti($returnModel, $propas_rec, $PRAM_DB) {
        $keypasso = $propas_rec['PROPAK'];
        $keypasso_radice = $propas_rec['PROKPRE'];
        $presenti = array();
        while ($keypasso_radice != 0 && !array_key_exists($keypasso_radice, $presenti)) {
            $presenti[$keypasso_radice] = $keypasso_radice;
            $propas_rec = self::checkPROPAK($keypasso_radice, $PRAM_DB);
            if (!$propas_rec) {
                $presenti[$keypasso_radice] = $keypasso_radice;
                break;
            }
            if ($propas_rec['PROKPRE']) {
                $keypasso_radice = $propas_rec['PROKPRE'];
            }
        }
        if ($keypasso_radice == "") {
            $keypasso_radice = $keypasso;
            $propas_rec = self::checkPROPAK($keypasso_radice, $PRAM_DB);
        }
        $presenti = array();
        $rowid = $propas_rec['ROWID'];
        $matriceSelezionatiTmp = $propas_rec;
        $matriceSelezionati = self::CaricaGrigliaPassiAntecedenti($keypasso, $PRAM_DB, $matriceSelezionatiTmp, $keypasso_radice, $presenti, $rowid);

        foreach ($matriceSelezionati as $key => $passo) {
            if ($passo['PROPAK'] === $keypasso) {
                $matriceSelezionati[$key]['PROGRESSIVO'] = '<span style="color:red;">' . $passo['PROGRESSIVO'] . '</span>';
                break;
            }
        }

        $praPerms = new praPerms();
        $matriceSelezionatiDef = $praPerms->filtraPassiView($matriceSelezionati);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Pratiche',
            "width" => '750',
            "height" => '430',
            "sortname" => "GESNUM",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'PROGRESSIVO',
            "arrayTable" => $matriceSelezionatiDef,
            "colNames" => array(
                "ID",
                "Sequenza",
                "Descrizione",
                "Responsabile"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROGRESSIVO', "width" => 150),
                array("name" => 'PRODPA', "width" => 300),
                array("name" => 'RESPONSABILE', "width" => 150)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnPassiAntecedenti';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAntecedente($returnModel, $returnEvent, $PRAM_DB, $proges_rec) {
        $pronum = $proges_rec['GESNUM'];
        $pronum_radice = $proges_rec['GESPRE'];
        //$tipo=$proges_rec['PROPAR'];
        $presenti = array();
        while ($pronum_radice != 0 && !array_key_exists($pronum_radice, $presenti)) {
            $presenti[$pronum_radice] = $pronum_radice;
            $proges_rec = self::checkGESNUM($pronum_radice, $PRAM_DB, $tipo);
            if (!$proges_rec) {
                $presenti[$pronum_radice] = $pronum_radice;
                break;
            }
            //$proges_rec=$anapro_ver;
            if ($proges_rec['GESPRE'])
                $pronum_radice = $proges_rec['GESPRE'];
        }
        if ($pronum_radice == "") {
            $pronum_radice = $pronum;
            $proges_rec = self::checkGESNUM($pronum_radice, $PRAM_DB, $tipo);
        }
        $presenti = array();
        $rowid = $proges_rec['ROWID'];
        $matriceSelezionati = $proges_rec;
        $matriceSelezionati = self::CaricaGrigliaAntecedenti($pronum, $PRAM_DB, $matriceSelezionati, $pronum_radice, $presenti, $tipo, $rowid);

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Pratiche',
            "width" => '860',
            "height" => '430',
            "sortname" => "GESNUM",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'PROGRESSIVO',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "ID",
                "N. Pratica",
                "Procedimento",
                "Data Regis.",
                "Intestatario",
                "Dati Impresa"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROGRESSIVO', "width" => 150),
                array("name" => 'PRADES', "width" => 300),
                array("name" => 'GESDRE', "formatter" => "eqdate", "width" => 80),
                array("name" => 'DESNOM', "width" => 150),
                array("name" => 'IMPRESA', "width" => 150)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnLegame';
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    protected static function checkGESPRE($pronum, $PRAM_DB, $tipo, $multi = false) {
        $sql = "SELECT 
                    PROGES.ROWID AS ROWID,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESNUM AS GESNUM,
                    PROGES.GESPRE,
                    ANAPRA.PRADES__1 AS PRADES,
                    DESNOM
                FROM 
                    PROGES PROGES 
                LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM
                WHERE
                    GESPRE BETWEEN $pronum AND $pronum
                GROUP BY 
                    ROWID";
        $proges_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
        if ($proges_rec) {
            $praLib = new praLib;
            if ($multi == false) {
                $impresa = $praLib->DatiImpresa($proges_rec['GESNUM']);
                $proges_rec['IMPRESA'] = $impresa['IMPRESA'];
            } else {
                foreach ($proges_rec as $key => $value) {
                    $impresa = $praLib->DatiImpresa($proges_rec[$key]['GESNUM']);
                    $proges_rec[$key]['IMPRESA'] = $impresa['IMPRESA'];
                }
            }
        }
        return $proges_rec;
    }

    protected static function checkGESNUM($pronum, $PRAM_DB, $tipo, $multi = false) {
        $sql = "SELECT 
                    PROGES.ROWID AS ROWID,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESNUM AS GESNUM,
                    PROGES.GESPRE,
                    ANAPRA.PRADES__1 AS PRADES,
                    DESNOM
                FROM 
                    PROGES PROGES
                LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
                LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM AND (ANADES.DESRUO='0001' OR ANADES.DESRUO='')
                WHERE
                    GESNUM BETWEEN $pronum AND $pronum
                GROUP BY
                    ROWID";
        $proges_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
        if ($proges_rec) {
            $praLib = new praLib;
            if ($multi == false) {
                $impresa = $praLib->DatiImpresa($pronum);
                $proges_rec['IMPRESA'] = $impresa['IMPRESA'];
            } else {
                foreach ($proges_rec as $key => $value) {
                    $impresa = $praLib->DatiImpresa($pronum);
                    $proges_rec[$key]['IMPRESA'] = $impresa['IMPRESA'];
                }
            }
        }
        return $proges_rec;
    }

    protected static function checkPROKPRE($keypasso, $PRAM_DB, $multi = false) {
        $sql = "SELECT 
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROKPRE AS PROKPRE,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSEQ AS PROSEQ," .
                $PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM 
                    PROPAS
                LEFT OUTER JOIN ANANOM ANANOM ON PROPAS.PRORPA=ANANOM.NOMRES
                WHERE
                    PROKPRE = '$keypasso'
                GROUP BY 
                    ROWID";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    protected static function checkPROPAK($keypasso, $PRAM_DB, $multi = false) {
        if (!$keypasso) {
            return false;
        }
        $sql = "SELECT 
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROKPRE AS PROKPRE,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSEQ AS PROSEQ," .
                $PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM 
                    PROPAS
                LEFT OUTER JOIN ANANOM ANANOM ON PROPAS.PRORPA=ANANOM.NOMRES
                WHERE
                    PROPAK = '$keypasso'
                GROUP BY
                    ROWID";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    protected static function CaricaGrigliaAntecedenti($pronum, $PRAM_DB, $proges_rec, $gespre_attuale, $presenti, $tipo, $rowid) {
        $inc = 1;
        //$proges_rec['PROGRESSIVO'] = substr($proges_rec['GESNUM'], 4) . '/' . substr($proges_rec['GESNUM'], 0, 4);
        $praLib = new praLib();
        $proges_rec['PROGRESSIVO'] = $praLib->ElaboraProgesSerie($proges_rec['GESNUM']);
        if ($proges_rec['GESNUM'] == $pronum) {
            $proges_rec['PROGRESSIVO'] = '<span style="color:red;">' . $proges_rec['PROGRESSIVO'] . '</span>';
        }
        $matriceSelezionati[$inc] = $proges_rec;
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $save_count = count($matriceSelezionati);
        if ($gespre_attuale > 1000000000) {
            $matriceSelezionati = self::caricaTreeAntecedenti($pronum, $PRAM_DB, $matriceSelezionati, $gespre_attuale, $presenti, 1, $tipo, $rowid);
        }
        if ($save_count == count($matriceSelezionati)) {
            $matriceSelezionati[$inc]['isLeaf'] = 'true';
        }
        return $matriceSelezionati;
    }

    protected static function CaricaGrigliaPassiAntecedenti($keypasso, $PRAM_DB, $propas_rec, $keypasso_attuale, $presenti, $rowid) {
        $inc = 1;
        $propas_rec['PROGRESSIVO'] = $propas_rec['PROSEQ'];
        $matriceSelezionati[$inc] = $propas_rec;
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $save_count = count($matriceSelezionati);
        if ($keypasso_attuale > 1000000000) {
            $matriceSelezionati = self::caricaTreePassiAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $keypasso_attuale, $presenti, 1, $rowid);
        }
        if ($save_count == count($matriceSelezionati)) {
            $matriceSelezionati[$inc]['isLeaf'] = 'true';
        }
        return $matriceSelezionati;
    }

    protected static function caricaTreeAntecedenti($pronum, $PRAM_DB, $matriceSelezionati, $gespre_attuale, $presenti, $level, $tipo, $rowid) {
        $praLib = new praLib();
        if (array_key_exists($gespre_attuale, $presenti))
            return $matriceSelezionati;
        $presenti[$gespre_attuale] = $gespre_attuale;
        $proges_tab = self::checkGESPRE($gespre_attuale, $PRAM_DB, $tipo, true);
        if (count($proges_tab) > 0) {
            for ($i = 0; $i < count($proges_tab); $i++) {
                $pronum_appoggio = $proges_tab[$i]['GESNUM'];
                //$proges_tab[$i]['PROGRESSIVO'] = substr($proges_tab[$i]['GESNUM'], 4) . '/' . substr($proges_tab[$i]['GESNUM'], 0, 4);
                $proges_tab[$i]['PROGRESSIVO'] = $praLib->ElaboraProgesSerie($proges_tab[$i]['GESNUM']);
                if ($proges_tab[$i]['GESNUM'] == $pronum) {
                    $proges_tab[$i]['PROGRESSIVO'] = '<span style="color:red;">' . $proges_tab[$i]['PROGRESSIVO'] . '</span>';
                }
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc] = $proges_tab[$i];
                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $rowid;
                $matriceSelezionati[$inc]['isLeaf'] = 'false';
                $matriceSelezionati[$inc]['expanded'] = 'true';
                $matriceSelezionati[$inc]['loaded'] = 'true';
                $save_count = count($matriceSelezionati);
                $matriceSelezionati = self::caricaTreeAntecedenti($pronum, $PRAM_DB, $matriceSelezionati, $pronum_appoggio, $presenti, $level + 1, $tipo, $proges_tab[$i]['ROWID']);
                if ($save_count == count($matriceSelezionati)) {
                    $matriceSelezionati[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $matriceSelezionati;
    }

    protected static function caricaTreePassiAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $keypasso_attuale, $presenti, $level, $rowid) {
//        if (array_key_exists($keypasso_attuale, $presenti)) {
//            return $matriceSelezionati;
//        }
        $presenti[$keypasso_attuale] = $keypasso_attuale;
        $propas_tab = self::checkPROKPRE($keypasso_attuale, $PRAM_DB, true);
        if (count($propas_tab) > 0) {
            for ($i = 0; $i < count($propas_tab); $i++) {
                $propak_appoggio = $propas_tab[$i]['PROPAK'];
                $propas_tab[$i]['PROGRESSIVO'] = $propas_tab[$i]['PROSEQ'];
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc] = $propas_tab[$i];
                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $rowid;
                $matriceSelezionati[$inc]['isLeaf'] = 'false';
                $matriceSelezionati[$inc]['expanded'] = 'true';
                $matriceSelezionati[$inc]['loaded'] = 'true';
                $save_count = count($matriceSelezionati);
                $matriceSelezionati = self::caricaTreePassiAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $propak_appoggio, $presenti, $level + 1, $propas_tab[$i]['ROWID']);
                if ($save_count == count($matriceSelezionati)) {
                    $matriceSelezionati[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $matriceSelezionati;
    }

    static function praRicTestiBase($matriceSelezionati, $returnModel, $returnEvent = 'returnTestiBase', $titolo = 'Ricerca Testi Base') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '450',
            "height" => '430',
            "sortname" => "FILENAME",
            "sortorder" => "desc",
            "filterToolbar" => 'true',
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "FILENAME"
            ),
            "colModel" => array(
                array("name" => 'FILENAME', "width" => 200)
            )
        );
        $filterName = array('FILENAME');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['filterName'] = $filterName;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicProtocolliPresenti($protocolliPresenti, $returnModel) {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Protocollazioni Presenti",
            "width" => '650',
            "height" => '430',
            "sortname" => "DESC",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $protocolliPresenti,
            "colNames" => array(
                "Tipo Protocollazione",
                "N. Protocollo",
                "Data. Protocollo"
            ),
            "colModel" => array(
                array("name" => 'DESC', "width" => 250),
                array("name" => 'NUMERO', "width" => 150),
                array("name" => 'DATA', "width" => 150, "formatter" => "eqdate")
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = "returnProtPresenti";
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnatsp($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANATSP";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
        );
        $colonneModel = array(
            array("name" => 'TSPCOD', "width" => 100),
            array("name" => 'TSPDES', "width" => 300)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Sportelli on-line',
            "width" => '500',
            "height" => '470',
            "sortname" => 'TSPDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        if (is_array($retid)) {
            $_POST['returnEvent'] = 'returnAnatsp';
        } else {
            $_POST['returnEvent'] = 'returnAnatsp' . $retid;
        }
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnacla($returnModel, $where = '', $retid = '') {
        $sql = "SELECT
                  ANACLA.ROWID AS ROWID,
                  ANACLA.CLACOD AS CLACOD,
                  ANACLA.CLADES AS CLADES,
                  ANATSP.TSPDES AS TSPDES
                FROM 
                  ANACLA
                LEFT OUTER JOIN
                  ANATSP  
                ON
                  ANACLA.CLASPO=ANATSP.TSPCOD 
                WHERE 1 ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Sportello"
        );
        $colonneModel = array(
            array("name" => 'CLACOD', "width" => 100),
            array("name" => 'CLADES', "width" => 300),
            array("name" => 'TSPDES', "width" => 200)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Classificazioni',
            "width" => '650',
            "height" => '470',
            "sortname" => 'CLADES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        //
        $filterName = array('CLADES', 'TSPDES');
        //
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnacla';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaspa($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANASPA";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
        );
        $colonneModel = array(
            array("name" => 'SPACOD', "width" => 100),
            array("name" => 'SPADES', "width" => 300)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Sportelli Aggregati',
            "width" => '500',
            "height" => '470',
            "sortname" => 'SPADES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('SPACOD', 'SPADES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnaspa' . $retid;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnareq($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANAREQ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Tipo",
            "Area",
            "Allegato",
        );
        $colonneModel = array(
            array("name" => 'REQCOD', "width" => 50),
            array("name" => 'REQDES', "width" => 270),
            array("name" => 'REQTIPO', "width" => 75),
            array("name" => 'REQAREA', "width" => 60),
            array("name" => 'REQFIL', "width" => 90)
        );

        $filterName = array('REQDES', 'REQTIPO', 'REQAREA');

        $gridOptions = array(
            "Caption" => 'Ricerca Requisiti',
            "width" => '580',
            "height" => '470',
            "sortname" => 'REQDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnareq';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function GetExplodedZip($returnModel, $arrayZip, $fileZip, $tree = true) {
        $model = 'utiRicDiag';
        if ($tree) {
            $gridOptions = array(
                "Caption" => "File dell\'archivio " . $fileZip,
                "width" => '650',
                "height" => '430',
                "rowNum" => '200',
                "rowList" => '[]',
                "treeGrid" => 'true',
                "ExpCol" => 'NAME',
                "arrayTable" => $arrayZip,
                "colNames" => array(
                    "seq",
                    "Nome File"
                ),
                "colModel" => array(
                    array("name" => 'SEQ', "width" => 200, "hidden" => "true", "key" => "true"),
                    array("name" => 'NAME', "width" => 600)
                )
            );
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = $returnModel;
            $_POST['returnEvent'] = "returnExplodeZip";
            $_POST['retid'] = '';
            $_POST['returnKey'] = 'retKey';
        } else {
            $gridOptions = array(
                "Caption" => "File dell\'archivio " . $fileZip,
                "width" => '650',
                "height" => '430',
                "rowNum" => '200000',
                "rowList" => '[]',
                "multiselect" => 'true',
                "multiselectReturnRowData" => 'true',
                "arrayTable" => $arrayZip,
                "colNames" => array(
                    "Nome File"
                ),
                "colModel" => array(
                    array("name" => 'NAME', "width" => 600)
                )
            );
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = $returnModel;
            $_POST['returnEvent'] = "returnExplodeZipPlain";
            $_POST['retid'] = '';
            $_POST['returnKey'] = 'retKey';
        }
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnanor($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANANOR";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Tipo",
            "Ente",
            "Allegato",
        );
        $colonneModel = array(
            array("name" => 'NORCOD', "width" => 50),
            array("name" => 'NORDES', "width" => 270),
            array("name" => 'NORTIP', "width" => 75),
            array("name" => 'NORENT', "width" => 140),
            array("name" => 'NORFIL', "width" => 100)
        );

        $filterName = array('NORDES', 'NORTIP', 'NORENT');

        $gridOptions = array(
            "Caption" => 'Ricerca Normative',
            "width" => '660',
            "height" => '470',
            "sortname" => 'NORDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnanor';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnadis($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANADIS";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Tipo",
            "Allegato",
        );
        $colonneModel = array(
            array("name" => 'DISCOD', "width" => 50),
            array("name" => 'DISDES', "width" => 270),
            array("name" => 'DISTIP', "width" => 75),
            array("name" => 'DISFIL', "width" => 100)
        );

        $filterName = array('DISDES', 'DISTIP', 'DISENT');

        $gridOptions = array(
            "Caption" => 'Ricerca Discipline Sanzionatorie',
            "width" => '60',
            "height" => '470',
            "sortname" => 'DISDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnadis';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaatt($returnModel, $where = '', $retid = '') {
        $sql = "SELECT
                    ANAATT.ROWID AS ROWID,
                    ANAATT.ATTCOD AS ATTCOD,
                    ANAATT.ATTDES AS ATTDES,
                    ANAATT.ATTSET AS ATTSET,
                    ANASET.SETDES AS SETDES
                FROM
                    ANAATT ANAATT
                LEFT OUTER JOIN ANASET ANASET ON ANASET.SETCOD=ANAATT.ATTSET
                WHERE 1=1 ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Attivita",
            "Descrizione",
            "Settore",
            "Descrizione"
        );
        $colonneModel = array(
            array("name" => 'ATTCOD', "width" => 50),
            array("name" => 'ATTDES', "width" => 300),
            array("name" => 'ATTSET', "width" => 50),
            array("name" => 'SETDES', "width" => 300)
        );

        $filterName = array('ATTCOD', 'ATTDES', 'ATTSET', 'SETDES');

        $gridOptions = array(
            "Caption" => 'Ricerca Attivita',
            "width" => '750',
            "height" => '470',
            "sortname" => 'ATTDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAnaatt';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaarc($returnModel, $titolo, $retid, $where = '') {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $sql = "SELECT ROWID, " . $PRAM_DB->subString('ARCCOD', 3, 6) . " AS ARCCOD, ARCDES FROM ANAARC";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione");
        $colonneModel = array(
            array("name" => 'ARCCOD', "width" => 100),
            array("name" => 'ARCDES', "width" => 300));
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '470',
            "sortname" => 'ARCDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaarc';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicComana($returnModel, $titolo, $retid) {
        $sql = "SELECT ROWID, ANAFI1__1 AS ANAFI1__1, ANADES FROM COMANA";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione");
        $colonneModel = array(
            array("name" => 'ANAFI1__1', "width" => 100),
            array("name" => 'ANADES', "width" => 300));
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '470',
            "sortname" => 'ANADES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'COMM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnComana';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnatip($PRAM_DB, $returnModel, $titolo, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANATIP";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione");
        $colonneModel = array(
            array("name" => 'TIPCOD', "width" => 150),
            array("name" => 'TIPDES', "width" => 350));
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '470',
            "sortname" => 'TIPDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnatip';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnanom($PRAM_DB, $returnModel, $titolo, $where = '', $retid = '', $flAdd = false, $extraData = null, $infoDetail = "", $returnClose = false) {
        if ($nome == "") {
            $sql = "SELECT ROWID AS ROWID, NOMRES AS NOMRES, " .
                    $PRAM_DB->strConcat("NOMCOG", "' '", "NOMNOM") . " AS NOMCOG
                FROM ANANOM";
        }

        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Cognome e Nome");
        $colonneModel = array(
            array("name" => 'NOMRES', "width" => 100),
            array("name" => 'NOMCOG', "width" => 300));
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '470',
            //"sortname" => 'NOMCOG',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "sortname" => 'NOMRES',
            "rowNum" => '99999',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUnires';
        $_POST['returnClosePortlet'] = $returnClose;
        if ($flAdd) {
            //$_POST['keynavButtonAdd'] = 'praDipe';
            $_POST['keynavButtonAdd'] = 'returnPadre';
        }
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['extraData'] = $extraData;
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     *
     * @param type $returnModel
     * @param type $returnEvent
     * @param type $retid
     * @param type $where
     * @param type $flAdd
     * @param type $extraData
     */
    static function praRicPraidc($returnModel, $returnEvent, $retid = '', $where = '', $flAdd = false, $extraData = null) {
        $sql = "SELECT * FROM PRAIDC";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Campi Aggiuntivi',
            "width" => '800',
            "height" => '470',
            "sortname" => 'IDCKEY',
            "rowNum" => '999999',
            "pginput" => 'false',
            "filterToolbar" => 'true',
            "pgbuttons" => 'false',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tipo",
            ),
            "colModel" => array(
                array("name" => 'IDCKEY', "width" => 300),
                array("name" => 'IDCDES', "width" => 300),
                array("name" => 'IDCTIP', "width" => 150),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $filterName = array("IDCKEY", "IDCDES", "IDCTIP");

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($flAdd) {
            $_POST['keynavButtonAdd'] = 'praDati';
        }
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['extraData'] = $extraData;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnapco($returnModel, $retid = '', $where = '') {
        $sql = "SELECT * FROM ANAPCO";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Procedure di Controllo',
            "width" => '550',
            "height" => '470',
            "sortname" => 'PCODES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tipo"
            ),
            "colModel" => array(
                array("name" => 'PCOCOD', "width" => 150),
                array("name" => 'PCODES', "width" => 300),
                array("name" => 'PCOTIP', "width" => 50)
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnapco';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnades($returnModel, $retid = '', $where = '', $multiselect = "false") {
        $sql = "SELECT
                   ANADES.DESFIS AS DESFIS,
                   ANADES.DESPIVA AS DESPIVA,
                   ANADES.ROWID AS ROWID,
                   ANADES.DESNOM AS DESNOM,
                   ANADES.DESCIT AS DESCIT,
                   ANARUO.RUODES AS RUODES
                FROM 
                  ANADES 
                LEFT OUTER JOIN
                  ANARUO 
                ON 
                  ANADES.DESRUO=ANARUO.RUOCOD
                $where GROUP BY DESFIS,DESPIVA,DESRUO
                    ";

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Ricerca Intestatari',
            "width" => '730',
            "height" => '470',
            "multiselect" => $multiselect,
            "sortname" => 'DESNOM',
            "sortorder" => 'desc',
            "filterToolbar" => 'true',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Intestatario",
                "Ruolo",
                "P. Iva",
                "Codice Fiscale",
                "Comune",
            ),
            "colModel" => array(
                array("name" => 'DESNOM', "width" => 200),
                array("name" => 'RUODES', "width" => 100),
                array("name" => 'DESPIVA', "width" => 100),
                array("name" => 'DESFIS', "width" => 150),
                array("name" => 'DESCIT', "width" => 150),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $filterName = array('DESNOM', 'DESPIVA', 'DESFIS', 'DESCIT');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnades';
        $_POST['filterName'] = $filterName;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicRuoli($returnModel, $retid = '', $where = '') {
        $sql = "SELECT * FROM ANARUO";

        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Ricerca Ruoli',
            "width" => '400',
            "height" => '470',
            "sortname" => 'RUODES',
            "sortorder" => 'desc',
            "filterToolbar" => 'true',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'RUOCOD', "width" => 70),
                array("name" => 'RUODES', "width" => 250),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('RUOCOD', 'RUODES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaruo';
        $_POST['filterName'] = $filterName;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnauni($returnModel, $titolo, $retid) {
        $sql = "SELECT * FROM ANAUNI WHERE UNISET<> '' AND UNISER='' AND UNIAPE=''";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Settori Pianta Organica',
            "width" => '550',
            "height" => '470',
            "sortname" => 'UNISET',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'UNISET', "width" => 200),
                array("name" => 'UNIDES', "width" => 300),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUniset';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaSer($PRAM_DB, $returnModel, $titolo, $where, $retid) {
        $sql = "SELECT  UNIDES, ROWID, " .
                $PRAM_DB->strConcat("UNISET", "' - '", "UNISER") . " AS SETSER
             FROM ANAUNI WHERE UNISET<> '' AND UNISER<>'' AND UNIOPE='' AND UNIAPE='' ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Servizi Pianta Organica',
            "width" => '550',
            "height" => '470',
            "sortname" => 'UNISET',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Settore/Servizio",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'SETSER', "width" => 200),
                array("name" => 'UNIDES', "width" => 300),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUniser';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnaOpe($PRAM_DB, $returnModel, $titolo, $where, $retid) {
        $sql = "SELECT  UNIDES, ROWID, " .
                $PRAM_DB->strConcat("UNISET", "' - '", "UNISER", "' - '", "UNIOPE") . " AS CODSSU
            FROM ANAUNI WHERE UNISET<> '' AND UNIOPE<>'' AND UNISER<>'' AND UNIADD='' AND UNIAPE=''";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Unità Operative',
            "width" => '550',
            "height" => '470',
            "sortname" => 'UNISET',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Settore/Servizio/Unità",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'CODSSU', "width" => 200),
                array("name" => 'UNIDES', "width" => 300),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUniope';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicPraclt($returnModel, $titolo = "", $retid = '', $where = "", $infoDetail = "", $returnClose = false) {
        $sql = "SELECT * FROM PRACLT ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        } else {
            $sql = $sql . " WHERE CLTOPE=''";
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Tipi Passo",
            "width" => '550',
            "height" => '470',
            "sortname" => 'CLTDES',
            "filterToolbar" => 'true',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'CLTCOD', "width" => 200),
                array("name" => 'CLTDES', "width" => 300),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $filterName = array('CLTDES');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['filterName'] = $filterName;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnClosePortlet'] = $returnClose;
        $_POST['returnEvent'] = 'returnPraclt';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricImmProcedimenti($matriceSelezionati, $returnModel, $returnEvent = 'returnImmagine', $titolo = 'Elenco Immagini Procedimento') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '430',
            "sortname" => "FILENAME",
            "sortorder" => "desc",
            "filterToolbar" => 'true',
            "rowNum" => '2000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "pgbuttons" => 'false',
            "pginput" => 'false',
            "colNames" => array(
                "FILENAME"
            ),
            "colModel" => array(
                array("name" => 'FILENAME', "width" => 270)
            )
        );
        $filterName = array('FILENAME');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['filterName'] = $filterName;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicMittente($matriceSelezionati, $returnModel, $retid = "", $returnEvent = 'returnMittente') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Ricerca Mittenti",
            "width" => '700',
            "height" => '430',
            "sortname" => "DENOMINAZIONE",
            "sortorder" => "desc",
            "filterToolbar" => 'true',
            "rowNum" => '2000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "pgbuttons" => 'false',
            "pginput" => 'false',
            "colNames" => array(
                "Nome",
                "Mail",
                "Comune",
                "Indirizzo",
                "Provincia",
                "Cap"
            ),
            "colModel" => array(
                array("name" => 'DENOMINAZIONE', "width" => 200),
                array("name" => 'EMAIL', "width" => 200),
                array("name" => 'COMUNE', "width" => 70),
                array("name" => 'INDIRIZZO', "width" => 100),
                array("name" => 'PROVINCIA', "width" => 50),
                array("name" => 'CAP', "width" => 50),
            )
        );
        $filterName = array('FILENAME');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['filterName'] = $filterName;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicItepas($returnModel, $from, $where = '', $retid = '', $titolo = '', $sortOrder = "desc", $dbSuffix = "") {
        $sql = "SELECT * FROM $from";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        switch ($from) {
            case 'ITEPAS':
            case 'RICITE':
                $sortName = 'ITESEQ';
                $colModel1 = 'ITESEQ';
                $colModel2 = 'ITEDES';
                break;
            case 'PROPAS':
                $sortName = 'PROSEQ';
                $colModel1 = 'PROSEQ';
                $colModel2 = 'PRODPA';
                break;
        }
        $model = 'utiRicDiag';
        if ($titolo == '') {
            $titolo = 'Passi Disponibili';
        }

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }


        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '550',
            "height" => '400',
            "sortname" => $sortName,
            "sortorder" => $sortOrder,
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Seq.",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => $colModel1, "width" => 50),
                array("name" => $colModel2, "width" => 400)
            ),
            "dataSource" => $dataSource
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnItepas';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicRequisiti($returnModel, $where = '', $dbSuffix = "") {
        $sql = "SELECT
                  ITEREQ.ROWID, ITEPRA,
                  ANAREQ.REQCOD, REQAREA, REQFIL, REQTIPO, REQDES
                FROM ITEREQ ITEREQ
                  LEFT OUTER JOIN ANAREQ ANAREQ ON ITEREQ.REQCOD = ANAREQ.REQCOD";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }


        $gridOptions = array(
            "Caption" => "Requisiti procedimento Master",
            "width" => '800',
            "height" => '400',
            "sortname" => "REQCOD",
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tipo",
                "Area",
                "Allegato"
            ),
            "colModel" => array(
                array("name" => "REQCOD", "width" => 50),
                array("name" => "REQDES", "width" => 350),
                array("name" => "REQTIPO", "width" => 100),
                array("name" => "REQAREA", "width" => 100),
                array("name" => "REQFIL", "width" => 150)
            ),
            "dataSource" => $dataSource
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnItereq';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicNormative($returnModel, $where = '', $dbSuffix = "") {
        $sql = "SELECT
                  ITEPRA, ITENOR.ROWID,
                  ANANOR.NORCOD, NORFIL, NORTIP, NORDES, NORENT
               FROM ITENOR ITENOR
                 LEFT OUTER JOIN ANANOR ANANOR ON ITENOR.NORCOD = ANANOR.NORCOD";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }

        $gridOptions = array(
            "Caption" => "Normative procedimento Master",
            "width" => '800',
            "height" => '400',
            "sortname" => "NORCOD",
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tipo",
                "Ente",
                "Allegato"
            ),
            "colModel" => array(
                array("name" => "NORCOD", "width" => 50),
                array("name" => "NORDES", "width" => 350),
                array("name" => "NORTIP", "width" => 100),
                array("name" => "NORENT", "width" => 100),
                array("name" => "NORFIL", "width" => 150)
            ),
            "dataSource" => $dataSource
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnItenor';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicDiscipline($returnModel, $where = '', $dbSuffix = "") {
        $sql = "SELECT
                  ITEPRA, ITEDIS.ROWID,
                  ANADIS.DISCOD, DISFIL, DISTIP, DISDES
               FROM ITEDIS ITEDIS
                 LEFT OUTER JOIN ANADIS ANADIS ON ITEDIS.DISCOD = ANADIS.DISCOD";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }

        $gridOptions = array(
            "Caption" => "Discipline Sanzionatorie procedimento Master",
            "width" => '800',
            "height" => '400',
            "sortname" => "DISCOD",
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tipo",
                "Allegato"
            ),
            "colModel" => array(
                array("name" => "DISCOD", "width" => 50),
                array("name" => "DISDES", "width" => 350),
                array("name" => "DISTIP", "width" => 100),
                array("name" => "DISFIL", "width" => 150)
            ),
            "dataSource" => $dataSource
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnItedis';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicPropas($returnModel, $where, $retid = '', $infoDetail = "Seleziona il passo:", $add = false, $filtraProtocolloP = "", $dbSuffix = "") {
        //$sql = "SELECT * FROM PROPAS ";
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        if ($filtraProtocolloP) {
            $sql = "SELECT 
                    PROPAS.ROWID,
                    PROPAS.PROSEQ,
                    PROPAS.PRODPA,
                    (SELECT " . $PRAM_DB->strConcat('SUBSTR(PRACOM.COMPRT, 5)', "'  '", $PRAM_DB->subString('PRACOM.COMPRT', 1, 4)) . "  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='P') AS PROTOCOLLOP,
                    (SELECT " . $PRAM_DB->strConcat('SUBSTR(PRACOM.COMPRT, 5)', "'  '", $PRAM_DB->subString('PRACOM.COMPRT', 1, 4)) . "  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='A') AS PROTOCOLLOA
                 FROM PROPAS 
                 LEFT OUTER JOIN PRACOM PRACOM ON PROPAS.PROPAK=PRACOM.COMPAK
                 LEFT OUTER JOIN PRACLT PRACLT ON PROPAS.PROCLT=PRACLT.CLTCOD
                 WHERE (PRACLT.CLTOPE IS NULL OR PRACLT.CLTOPE='') AND PRACOM.COMPRT='$filtraProtocolloP' ";
            if ($where != '')
                $sql .= ' ' . $where;
//             (SELECT CONCAT(SUBSTR(PRACOM.COMPRT, 5), '  ', SUBSTR(PRACOM.COMPRT, 1, 4))  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='P') AS PROTOCOLLOP,
//             (SELECT CONCAT(SUBSTR(PRACOM.COMPRT, 5), '  ', SUBSTR(PRACOM.COMPRT, 1, 4))  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='A') AS PROTOCOLLOA
        } else {
            $sql = "SELECT 
                    PROPAS.ROWID,
                    PROPAS.PROSEQ,
                    PROPAS.PRODPA,
                    (SELECT " . $PRAM_DB->strConcat('SUBSTR(PRACOM.COMPRT, 5)', "'  '", $PRAM_DB->subString('PRACOM.COMPRT', 1, 4)) . "  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='P') AS PROTOCOLLOP,
                    (SELECT " . $PRAM_DB->strConcat('SUBSTR(PRACOM.COMPRT, 5)', "'  '", $PRAM_DB->subString('PRACOM.COMPRT', 1, 4)) . "  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='A') AS PROTOCOLLOA
                 FROM PROPAS 
                 LEFT OUTER JOIN PRACLT PRACLT ON PROPAS.PROCLT=PRACLT.CLTCOD                 
                 WHERE (PRACLT.CLTOPE IS NULL OR PRACLT.CLTOPE='')";
            if ($where != '')
                $sql .= ' ' . $where;
//                    (SELECT CONCAT(SUBSTR(PRACOM.COMPRT, 5), '  ', SUBSTR(PRACOM.COMPRT, 1, 4))  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='P') AS PROTOCOLLOP,
//                    (SELECT CONCAT(SUBSTR(PRACOM.COMPRT, 5), '  ', SUBSTR(PRACOM.COMPRT, 1, 4))  FROM PRACOM WHERE PRACOM.COMPAK=PROPAS.PROPAK AND PRACOM.COMTIP='A') AS PROTOCOLLOA
        }

        $dataSource = array(
            'sqlDB' => 'PRAM',
            'sqlQuery' => $sql);

        if ($dbSuffix) {
            $dataSource = array(
                'sqlDB' => 'PRAM',
                'dbSuffix' => $dbSuffix,
                'sqlQuery' => $sql);
        }


        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Passi Disponibili',
            "width" => '700',
            "height" => '470',
            "sortname" => 'PROSEQ',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Seq.",
                "Descrizione",
                "Protocollo<br>in Partenza",
                "Protocollo<br>in Arrivo"
            ),
            "colModel" => array(
                array("name" => 'PROSEQ', "width" => 50),
                array("name" => 'PRODPA', "width" => 400),
                array("name" => 'PROTOCOLLOP', "width" => 100),
                array("name" => 'PROTOCOLLOA', "width" => 100)
            ),
            "dataSource" => $dataSource,
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        if ($add == true) {
            $_POST['keynavButtonAdd'] = 'returnPadre';
        }
        $_POST['returnEvent'] = 'returnPropas' . $retid;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnastp($returnModel, $where, $retid = '', $infoDetail = "Seleziona il passo:") {
        $sql = "SELECT * FROM ANASTP ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Archivio stati',
            "width" => '550',
            "height" => '470',
            "sortname" => 'STPDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Stato"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 50),
                array("name" => 'STPDES', "width" => 300),
                array("name" => 'STPFLAG', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnastp';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        // itaLib::openForm($model,true,true,'desktopBody',$returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praComponiPDF($matriceSelezionati, $returnModel, $returnEvent = 'returnPraPDFComposer') {
        $model = 'praPDFComposer';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnModel'] = $returnModel;
        $_POST['PDFList'] = $matriceSelezionati;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praElencoAllegatiProc($matriceSelezionati, $returnModel, $returnEvent = '', $infoDetail = "") {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Allegati',
            "width" => '940',
            "height" => '430',
            "filterToolbar" => 'true',
            "multiselect" => 'true',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '1000',
            "rowList" => '[]',
            "readerId" => "RIDORIG",
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Tipo",
                "Nome File",
                "File Info",
                "Stato",
                "Riservato"
            ),
            "colModel" => array(
                array("name" => 'TIPO', "width" => 250),
                array("name" => 'FILEVISUA', "width" => 200),
                array("name" => 'FILEINFO', "width" => 300),
                array("name" => 'STATO', "width" => 80),
                array("name" => 'RISERVATO', "width" => 50, 'search' => 'false')
            )
        );

        $filterName = array('TIPO', 'FILEVISUA', 'FILEINFO', "STATO");

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnAllegatiProc';
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['msgDetail'] = $infoDetail;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicFtpFile($returnModel, $matriceSelezionati) {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco file nel server FTP',
            "width" => '750',
            "height" => '430',
            "filterToolbar" => 'true',
            "multiselect" => 'true',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Nome File"
            ),
            "colModel" => array(
                array("name" => 'FILENAME', "width" => 500)
            )
        );

        $filterName = array('FILENAME');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnFtpFile';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praPassiSelezionati($matriceSelezionati, $returnModel, $retid, $caption, $from = "ITEPAS", $multiselect = "true", $returnEvent = "returnPassiSel") {
        $model = 'utiRicDiag';
        switch ($from) {
            case 'ITEPAS':
                $sortName = 'ITESEQ';
                $colModel1 = 'ITESEQ';
                $colModel2 = 'ITEDES';
                $colModel4 = 'ITEGIO';
                $colModel5 = 'ITEDES';
                $colModel6 = 'ITEPUB';
                $colModel7 = 'ITEOBL';
                $colModel8 = 'ITEQST';
                $antecedente = 'ITEKPRE';
                break;
            case 'PROPAS':
                $sortName = 'PROSEQ';
                $colModel1 = 'PROSEQ';
                $colModel2 = 'PRODTP';
                $colModel4 = 'PROGIO';
                $colModel5 = 'PRODPA';
                $colModel6 = 'PROPUB';
                $colModel7 = 'PROPUB';
                $colModel8 = 'PROQST';
                $antecedente = 'PROKPRE';
                break;
        }

        /*
         * Mi scorro i passi e tolgo dall'array quelli con antecedenti
         * perchè non devono essere visti nella Ric
         */
        foreach ($matriceSelezionati as $key => $passo) {
            if ($passo[$antecedente]) {
                unset($matriceSelezionati[$key]);
            }
        }



        $gridOptions = array(
            "Caption" => $caption,
            "width" => '800',
            "height" => '430',
            "multiselect" => $multiselect,
            "readerId" => "ROWID",
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '1000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Seq",
                "Tipo Passo",
                "Responsabile",
                "Giorni",
                "Descrizione",
                "S.",
                "O.",
                "D.",
                ""
            ),
            "colModel" => array(
                array("name" => $colModel1, "width" => 30),
                array("name" => $colModel2, "width" => 200),
                array("name" => 'RESPONSABILE', "width" => 100),
                array("name" => $colModel4, "width" => 30),
                array("name" => $colModel5, "width" => 200),
                array("name" => $colModel6, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => $colModel7, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => $colModel8, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => 'VAI', "width" => 30)
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent; //'returnPassiSel';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praCampiPdf($filePdf, $returnModel, $returnEvent = 'returnCampiPdf', $multiselect = "false", $nomeFile = "") {
        $praLib = new praLib();
        //
        // Dal file pdf creao una array table con codice campo e valore rilevato
        //
        if ($praLib->CreaFileInfo($filePdf)) {
            $outputFile = pathinfo($filePdf, PATHINFO_FILENAME);
            $outputPath = itaLib::getAppsTempPath();
            $fileInfo['DATAFILE'] = $outputPath . '/' . $outputFile . '.info';
            $arrayInfo = $praLib->DecodeFileInfo($fileInfo);
            $i = 0;
            $arrayTable = array();
            foreach ($arrayInfo as $key => $value) {
                $arrayTable[$value['taborder']]['Campo'] = $key;
                $arrayTable[$value['taborder']]['Valore'] = $value['value'];
//                $arrayTable[$i]['Campo']=$key;
//                $arrayTable[$i]['Valore']=$value['value'];
                $i += 1;
            }
            if (!$arrayTable) {
                Out::msgInfo("Caricamento Dati Aggiuntivi", "Campi modulo del file " . pathinfo($filePdf, PATHINFO_BASENAME) . " non trovati");
                return false;
            }
            ksort($arrayTable);
            //
            // preparo la chiamata a utiRicDiag
            //
            $model = 'utiRicDiag';
            $gridOptions = array(
                "Caption" => 'Elenco Campi del File: ' . $outputFile . '.pdf',
                "width" => '750',
                "height" => '430',
                "rowNum" => '2000',
                "sortname" => "Campo",
                "multiselect" => $multiselect,
                "sortorder" => "asc",
                "rowList" => '[]',
                "arrayTable" => $arrayTable,
                "pgbuttons" => 'false',
                "pginput" => 'false',
                "colNames" => array(
                    "Campo",
                    "Valore Default"
                ),
                "colModel" => array(
                    array("name" => 'Campo', "width" => 400),
                    array("name" => 'Valore', "width" => 320),
                )
            );
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['gridOptions'] = $gridOptions;
            $_POST['returnModel'] = $returnModel;
            $_POST['returnEvent'] = $returnEvent;
            if ($returnEvent != '')
                $_POST['returnEvent'] = $returnEvent;
            $_POST['retid'] = '';
            $_POST['returnKey'] = 'retKey';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        }
        return array("arrayTable" => $arrayTable, "fileName" => $outputFile . '.info', "pdfName" => $nomeFile);
    }

    static function praRubricaWS($matriceSelezionati, $returnModel, $returnEvent = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Lista Anagrafiche trovate',
            "width" => '750',
            "height" => '430',
            "filterToolbar" => 'false',
            "multiselect" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '2000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Codice",
                "Ragione Sociale",
                "Nome",
                "Cognome",
                "Indirizzo",
                "Cap",
                "Ciità",
                "Prov.",
                "Cod. Fis.",
                "P. Iva"
            ),
            "colModel" => array(
                array("name" => 'codice', "width" => 45),
                array("name" => 'ragioneSociale', "width" => 200),
                array("name" => 'nome', "width" => 120),
                array("name" => 'cognome', "width" => 120),
                array("name" => 'indirizzo', "width" => 150),
                array("name" => 'cap', "width" => 40),
                array("name" => 'citta', "width" => 120),
                array("name" => 'provincia', "width" => 40),
                array("name" => 'codiceFiscale', "width" => 100),
                array("name" => 'partitaIva', "width" => 100),
            )
        );
        //$filterName = array('TIPO', 'FILENAME', 'FILEINFO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
//        $_POST['filterName'] = $filterName;
        if ($returnEvent != '') {
            $_POST['returnEvent'] = $returnEvent;
        } else {
            $_POST['returnEvent'] = 'returnRubricaWS';
        }
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicSoggetti($matriceSelezionati, $returnModel, $returnEvent = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Scegli il Soggetto con cui protocollare la  pratica ',
            "width" => '950',
            "height" => '430',
            "filterToolbar" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '2000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Nominativo",
                "Ruolo",
                "Fiscale",
                "Indirizzo",
                "Città",
                "E-mail"
            ),
            "colModel" => array(
                array("name" => 'DESNOM', "width" => 200),
                array("name" => "RUOLO", "width" => 100),
                array("name" => 'DESFIS', "width" => 150),
                array("name" => 'DESIND', "width" => 150),
                array("name" => 'DESCIT', "width" => 150),
                array("name" => 'DESEMA', "width" => 150),
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        if ($returnEvent != '') {
            $_POST['returnEvent'] = $returnEvent;
        } else {
            $_POST['returnEvent'] = 'returnSoggettiPra';
        }
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAllegatiToTali($matriceSelezionati, $returnModel, $returnEvent = 'returnTotAllegati') {
        $praLib = new praLib();
        $Filent_rec = $praLib->GetFilent(15);
        foreach ($matriceSelezionati as $key => $allegato) {
            $protocollo = "";
            $propas_rec = array();
            $matriceSelezionati[$key]['DESCSTATO'] = $praLib->GetStatoAllegati($allegato['PASSTA']);
            if (strlen($allegato['PASKEY']) > 10) {
                $propas_rec = $praLib->GetPropas($allegato['PASKEY'], "propak");
                if ($Filent_rec['FILVAL'] == 1 && $propas_rec['PROPUB'] == 1) {
                    unset($matriceSelezionati[$key]);
                    continue;
                }
                $matriceSelezionati[$key]['PASSO'] = $propas_rec['PROSEQ'] . " - " . $propas_rec['PRODPA'];
                //
                $pracomP_rec = $praLib->GetPracomP($allegato['PASKEY']);
                if ($pracomP_rec['COMPRT']) {
                    $dataPrtP = substr($pracomP_rec['COMDPR'], 6, 2) . "/" . substr($pracomP_rec['COMDPR'], 4, 2) . "/" . substr($pracomP_rec['COMDPR'], 0, 4);
                    $numProtocolloP = substr($pracomP_rec['COMPRT'], 4, 6) . "/" . substr($pracomP_rec['COMPRT'], 0, 4);
                    $protocollo .= "<b>In Partenza:</b> $numProtocolloP del <br>$dataPrtP<br>";
                }
                if ($pracomP_rec['COMIDDOC']) {
                    $dataDoc = substr($pracomP_rec['COMDATADOC'], 6, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 4, 2) . "/" . substr($pracomP_rec['COMDATADOC'], 0, 4);
                    $numDoc = $pracomP_rec['COMIDDOC'];
                    $protocollo .= "<b>In Partenza:</b> $numDoc del <br>$dataDoc<br>";
                }
                $pracomA_rec = $praLib->GetPracomA($allegato['PASKEY']);
                if ($pracomA_rec['COMPRT']) {
                    $dataPrtA = substr($pracomA_rec['COMDPR'], 6, 2) . "/" . substr($pracomA_rec['COMDPR'], 4, 2) . "/" . substr($pracomA_rec['COMDPR'], 0, 4);
                    $numProtocolloA = substr($pracomA_rec['COMPRT'], 4, 6) . "/" . substr($pracomA_rec['COMPRT'], 0, 4);
                    $protocollo .= "<b>In Arrivo:</b> $numProtocolloA del <br>$dataPrtA";
                }
                $matriceSelezionati[$key]['PROTOCOLLO'] = $protocollo;
            } else {
                $matriceSelezionati[$key]['PASSO'] = "Allegato Generale Pratica " . $allegato['PASKEY'];
                $proges_rec = $praLib->GetProges($allegato['PASKEY']);
                if ($proges_rec['GESNPR']) {
                    $meta = unserialize($proges_rec['GESMETA']);
                    if ($meta['DatiProtocollazione']['Data']['value']) {
                        $dataPrt = "del " . $meta['DatiProtocollazione']['Data']['value'];
                    }
                    $matriceSelezionati[$key]['PROTOCOLLO'] = $proges_rec['GESNPR'] . " $dataPrt<br>";
                }
            }
            if ($allegato['PASEVI'] == 1) {
                if ($allegato['PASNAME']) {
                    $matriceSelezionati[$key]['PASNAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $allegato['PASNAME'] . "</p>";
                } else {
                    $matriceSelezionati[$key]['PASNAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $allegato['PASFIL'] . "</p>";
                }
            }
            if ($allegato['PASLOCK'] == 1) {
                $matriceSelezionati[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-16x16\">Sblocca Allegato</span>";
            } else {
                $matriceSelezionati[$key]['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-16x16\">Blocca Allegato</span>";
            }
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Visualizzazione dettagliata del file",
            "width" => '1400',
            "height" => '430',
            "sortname" => "ROWID",
            "sortorder" => "desc",
            "filterToolbar" => 'true',
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Nome File",
                "",
                "N. Protocollo/<br>Id Documento",
                "Passo",
                "Note",
                "Stato",
                "Provenienza"
            ),
            "colModel" => array(
                array("name" => 'PASNAME', "width" => 300),
                array("name" => 'LOCK', "width" => 20),
                array("name" => 'PROTOCOLLO', "width" => 200),
                array("name" => 'PASSO', "width" => 300),
                array("name" => 'PASNOT', "width" => 320),
                array("name" => 'DESCSTATO', "width" => 100),
                array("name" => 'PASCLA', "width" => 150),
            )
        );
        $filterName = array('PASNAME', "PASNOT");

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['filterName'] = $filterName;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicDatiAggiuntivi($numProc, $campo, $returnModel, $returnEvent = 'returnDatiAgg') {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $sql = "SELECT 
                    ITEDAG.*, 
                    ITEPAS.ITESEQ,
                    ITEPAS.ITEDES
                FROM 
                    ITEDAG ITEDAG
                LEFT OUTER JOIN 
                    ITEPAS ITEPAS
                ON
                    ITEDAG.ITEKEY=ITEPAS.ITEKEY
                WHERE 
                    ITEDAG.ITECOD = '$numProc' AND 
                    (" . $PRAM_DB->strLower('ITEDAG.ITDKEY') . " LIKE '%$campo%' OR " . $PRAM_DB->strLower('ITEDAG.ITDALIAS') . " LIKE '%$campo%') 
                ";


        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Ricerca Dati Aggiuntivi",
            "width" => '950',
            "height" => '430',
            "sortname" => "ITESEQ",
            "rowNum" => '200',
            "rowList" => '[]',
            "colNames" => array(
                "Nome Campo",
                "Seq. Passo",
                "Passo",
            ),
            "colModel" => array(
                array("name" => 'ITDKEY', "width" => 200),
                array("name" => 'ITESEQ', "width" => 70),
                array("name" => 'ITEDES', "width" => 300),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            ),
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praDatiSelezionati($matriceSelezionati, $returnModel, $retid, $caption = 'Ricerca dati aggiuntivi', $multiselect = true, $infoDetail = '', $returnEvent = 'returnDatiSel') {
        $praLib = new praLib();
        $model = 'utiRicDiag';
        foreach ($matriceSelezionati as $key => $dato) {
            $matriceSelezionati[$key]['CONTROLLO'] = $praLib->DecodificaControllo($dato['ITDCTR']);
        }

        array_multisort(array_column($matriceSelezionati, 'ITDSEQ'), SORT_ASC, $matriceSelezionati);

        $gridOptions = array(
            "Caption" => $caption,
            "width" => '800',
            "height" => '400',
            "rowNum" => '300',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            'readerId' => 'ROWID',
            'navButtonEdit' => 'false',
            "colNames" => array(
                "Seq",
                "Nome Campo",
                "Campo Pdf",
                "Controllo",
            ),
            "colModel" => array(
                array("name" => "ITDSEQ", "width" => 50),
                array("name" => "ITDKEY", "width" => 200),
                array("name" => 'ITDALIAS', "width" => 200),
                array("name" => "CONTROLLO", "width" => 300),
            )
        );

        if ($multiselect) {
            $gridOptions['multiselect'] = 'true';
        }

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        if ($infoDetail != '') {
            $_POST['msgDetail'] = $infoDetail;
        }
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnahelp($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANAHELP";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Formato",
        );
        $colonneModel = array(
            array("name" => 'HELPCOD', "width" => 100),
            array("name" => 'HELPDES', "width" => 300),
            array("name" => 'HELPFORMATO', "width" => 300)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Help',
            "width" => '750',
            "height" => '470',
            "sortname" => 'HELPDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnahelp';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricAnaeventi($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANAEVENTI";
        if ($where != '')
            $sql = $sql . ' ' . $where;

        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Segnalazione Comunica"
        );

        $colonneModel = array(
            array("name" => 'EVTCOD', "width" => 100),
            array("name" => 'EVTDESCR', "width" => 300),
            array("name" => 'EVTSEGCOMUNICA', "width" => 300)
        );

        $gridOptions = array(
            "Caption" => 'Ricerca Eventi',
            "width" => '610',
            "height" => '470',
            "sortname" => 'EVTCOD',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaeventi';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricIteevt($returnModel, $where = '', $retid = '') {
        $sql = "SELECT
                    ITEEVT.*,
                    EVTSEGCOMUNICA,
                    TSPDES,
                    SETDES,
                    ATTDES
                FROM
                    ITEEVT
                LEFT OUTER JOIN ANAEVENTI ON ANAEVENTI.EVTCOD = ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ON ANATSP.TSPCOD = ITEEVT.IEVTSP
                LEFT OUTER JOIN ANASET ON ANASET.SETCOD = ITEEVT.IEVSTT
                LEFT OUTER JOIN ANAATT ON ANAATT.ATTCOD = ITEEVT.IEVATT
                
                ";
//        $sql = "SELECT
//                    ANAEVENTI.*
//                FROM
//                    ITEEVT
//                LEFT OUTER JOIN
//                    ANAEVENTI
//                ON
//                    ANAEVENTI.EVTCOD = ITEEVT.IEVCOD";

        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Segnalazione<br>Comunica",
            "Sportello",
            "Settore",
            "Attività",
        );

        $colonneModel = array(
            array("name" => 'IEVCOD', "width" => 60),
            array("name" => 'IEVDESCR', "width" => 150),
            array("name" => 'EVTSEGCOMUNICA', "width" => 100),
            array("name" => 'TSPDES', "width" => 100),
            array("name" => 'SETDES', "width" => 220),
            array("name" => 'ATTDES', "width" => 220),
//            array("name" => 'EVTCOD', "width" => 60),
//            array("name" => 'EVTDESCR', "width" => 200),
//            array("name" => 'EVTSEGCOMUNICA', "width" => 100),
        );

        $gridOptions = array(
            "Caption" => 'Ricerca Eventi',
            "width" => '910',
            "height" => '470',
            "sortname" => 'EVTCOD',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnIteevt';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricAnatipimpo($returnModel, $returnId = '', $where = '') {
        $sql = "SELECT * FROM ANATIPIMPO";

        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione"
        );

        $colonneModel = array(
            array("name" => 'CODTIPOIMPO', "width" => 50),
            array("name" => 'DESCTIPOIMPO', "width" => 300)
        );

        $gridOptions = array(
            "Caption" => 'Ricerca Tipi Importo',
            "width" => '365',
            "height" => '350',
            "sortname" => 'CODTIPOIMPO',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'retRicAnatipimpo';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricAnaquiet($returnModel, $returnId = '', $where = '') {
        $sql = "SELECT * FROM ANAQUIET";

        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Codifica",
            "Identificazione"
        );

        $colonneModel = array(
            array("name" => 'CODQUIET', "width" => 40),
            array("name" => 'QUIETANZATIPO', "width" => 260),
            array("name" => 'IDENTIFICAZIONETIPO', "width" => 80),
            array("name" => 'IDENTIFICAZIONE', "width" => 150)
        );

        $gridOptions = array(
            "Caption" => 'Ricerca Tipologia Quietanza',
            "width" => '550',
            "height" => '450',
            "sortname" => 'CODQUIET',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'retRicAnaquiet';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnadoctipreg($returnModel, $returnId = '', $where = '') {
        $sql = "SELECT * FROM ANADOCTIPREG ";

        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
        );

        $colonneModel = array(
            array("name" => 'CODDOCREG', "width" => 40),
            array("name" => 'DESDOCREG', "width" => 360),
        );

        $gridOptions = array(
            "Caption" => 'Ricerca Tipologie Progressivi',
            "width" => '520',
            "height" => '450',
            "sortname" => 'CODDOCREG',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnadoctipreg';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicFiereSogg($matriceSelezionati, $returnModel, $returnEvent = 'returnFiereSogg') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Ricerca Fiere",
            "width" => '1050',
            "height" => '430',
            "sortname" => "DENOMINAZIONE",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Denominazione",
                "Ente",
                "Codice Fiscale",
                "Partiva Iva",
                "Comune",
                "Cap",
                "Prov",
                "Indirizzo",
                "Civico",
            ),
            "colModel" => array(
                array("name" => 'DENOMINAZIONE', "width" => 190),
                array("name" => 'ENTE', "width" => 100),
                array("name" => 'CODICEFISCALE', "width" => 150),
                array("name" => 'PIVA', "width" => 90),
                array("name" => 'COMUNE', "width" => 150),
                array("name" => 'CAP', "width" => 50),
                array("name" => 'PROVINCIA', "width" => 50),
                array("name" => 'INDIRIZZO', "width" => 200),
                array("name" => 'NUMEROCIVICO', "width" => 20),
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicVieBase($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='VIE'";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Vie',
            "width" => '560',
            "height" => '420',
            "sortname" => 'ANADES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'ANACOD', "width" => 70),
                array("name" => 'ANADES', "width" => 330)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('ANACOD', 'ANADES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnVie' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicItedag($returnModel) {
        $sql = "SELECT
                    *
                FROM 
                    ITEDAG
                GROUP BY ITDKEY
                ";

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Ricerca Dati Aggiuntivi",
            "width" => '750',
            "height" => '430',
            "sortname" => "ITDSEQ",
            "rowNum" => '200',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Nome Campo",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'ITDKEY', "width" => 300),
                array("name" => 'ITDDES', "width" => 400),
            ),
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            ),
        );

        $filterName = array('ITDKEY', 'ITDDES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = "returnAggiuntivi";
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    //
    //
    //
    
    
    static function praItepasAntecedenti($returnModel, $itepas_rec, $PRAM_DB) {
        $keypasso = $itepas_rec['ITEKEY'];
        $keypasso_radice = $itepas_rec['ITEKPRE'];
        $presenti = array();
        while ($keypasso_radice != 0 && !array_key_exists($keypasso_radice, $presenti)) {
            $presenti[$keypasso_radice] = $keypasso_radice;
            $itepas_rec = self::checkITEKEY($keypasso_radice, $PRAM_DB);
            if (!$itepas_rec) {
                $presenti[$keypasso_radice] = $keypasso_radice;
                break;
            }
            if ($itepas_rec['ITEKPRE']) {
                $keypasso_radice = $itepas_rec['ITEKPRE'];
            }
        }
        if ($keypasso_radice == "") {
            $keypasso_radice = $keypasso;
            $itepas_rec = self::checkITEKEY($keypasso_radice, $PRAM_DB);
        }
        $presenti = array();
        $rowid = $itepas_rec['ROWID'];
        $matriceSelezionatiTmp = $itepas_rec;
        $matriceSelezionati = self::CaricaGrigliaItepasAntecedenti($keypasso, $PRAM_DB, $matriceSelezionatiTmp, $keypasso_radice, $presenti, $rowid);

        foreach ($matriceSelezionati as $key => $passo) {
            if ($passo['ITEKEY'] === $keypasso) {
                $matriceSelezionati[$key]['PROGRESSIVO'] = '<span style="color:red;">' . $passo['PROGRESSIVO'] . '</span>';
                break;
            }
        }
        $praPerms = new praPerms();
        $matriceSelezionatiDef = $praPerms->filtraPassiView($matriceSelezionati);
        if ($matriceSelezionatiDef[1]['PROGRESSIVO'] == "") {
            return false;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Passi',
            "width" => '750',
            "height" => '430',
            "sortname" => "ITECOD",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'PROGRESSIVO',
            "arrayTable" => $matriceSelezionatiDef,
            "colNames" => array(
                "ID",
                "Sequenza",
                "Descrizione",
                "Responsabile"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROGRESSIVO', "width" => 150),
                array("name" => 'ITEDES', "width" => 300),
                array("name" => 'RESPONSABILE', "width" => 150)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnItepasAntecedenti';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        return true;
    }

    protected static function checkITEKEY($keypasso, $PRAM_DB, $multi = false) {
        if (!$keypasso) {
            return false;
        }
        $itecod = substr($keypasso, 0, 6);
        $sql = "SELECT 
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEKPRE AS ITEKPRE,
                    ITEPAS.ITEKEY AS ITEKEY,
                    ITEPAS.ITESEQ AS ITESEQ," .
                $PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM 
                    ITEPAS
                LEFT OUTER JOIN ANANOM ANANOM ON ITEPAS.ITERES=ANANOM.NOMRES
                WHERE
                    ITEKEY = '$keypasso' AND ITECOD = '$itecod'
                GROUP BY
                    ROWID";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    protected static function CaricaGrigliaItepasAntecedenti($keypasso, $PRAM_DB, $propas_rec, $keypasso_attuale, $presenti, $rowid) {
        $inc = 1;
        $propas_rec['PROGRESSIVO'] = $propas_rec['ITESEQ'];
        $matriceSelezionati[$inc] = $propas_rec;
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $save_count = count($matriceSelezionati);
        if ($keypasso_attuale > 1000000000) {
            $matriceSelezionati = self::caricaTreeItepasAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $keypasso_attuale, $presenti, 1, $rowid);
        }
        if ($save_count == count($matriceSelezionati)) {
            $matriceSelezionati[$inc]['isLeaf'] = 'true';
        }
        return $matriceSelezionati;
    }

    protected static function caricaTreeItepasAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $keypasso_attuale, $presenti, $level, $rowid) {
        $presenti[$keypasso_attuale] = $keypasso_attuale;
        $propas_tab = self::checkITEKPRE($keypasso_attuale, $PRAM_DB, true);
        if (count($propas_tab) > 0) {
            for ($i = 0; $i < count($propas_tab); $i++) {
                $propak_appoggio = $propas_tab[$i]['ITEKEY'];
                $propas_tab[$i]['PROGRESSIVO'] = $propas_tab[$i]['ITESEQ'];
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc] = $propas_tab[$i];
                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $rowid;
                $matriceSelezionati[$inc]['isLeaf'] = 'false';
                $matriceSelezionati[$inc]['expanded'] = 'true';
                $matriceSelezionati[$inc]['loaded'] = 'true';
                $save_count = count($matriceSelezionati);
                $matriceSelezionati = self::caricaTreeItepasAntecedenti($keypasso, $PRAM_DB, $matriceSelezionati, $propak_appoggio, $presenti, $level + 1, $propas_tab[$i]['ROWID']);
                if ($save_count == count($matriceSelezionati)) {
                    $matriceSelezionati[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $matriceSelezionati;
    }

    protected static function checkITEKPRE($keypasso, $PRAM_DB, $multi = false) {
        $itecod = substr($keypasso, 0, 6);
        $sql = "SELECT 
                    ITEPAS.ROWID AS ROWID,
                    ITEPAS.ITEDES AS ITEDES,
                    ITEPAS.ITEKPRE AS ITEKPRE,
                    ITEPAS.ITEKEY AS ITEKEY,
                    ITEPAS.ITESEQ AS ITESEQ," .
                $PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE
                FROM 
                    ITEPAS
                LEFT OUTER JOIN ANANOM ANANOM ON ITEPAS.ITERES=ANANOM.NOMRES
                WHERE
                    ITEKPRE = '$keypasso' AND ITECOD = '$itecod'
                GROUP BY 
                    ROWID";
        return ItaDB::DBSQLSelect($PRAM_DB, $sql, $multi);
    }

    static function praPassiNonAperti($matriceSelezionati, $returnModel, $retid, $caption, $from = "PROPAS", $multiselect = "false") {
        $model = 'utiRicDiag';
        switch ($from) {
            case 'PROPAS':
                $sortName = 'PROSEQ';
                $colModel1 = 'PROSEQ';
                $colModel2 = 'PRODTP';
                $colModel4 = 'PROGIO';
                $colModel5 = 'PRODPA';
                $colModel6 = 'PROPUB';
                $colModel7 = 'PROPUB';
                $colModel8 = 'PROQST';
                $antecedente = 'PROKPRE';
                break;
        }

        foreach ($matriceSelezionati as $key => $passo) {
            if ($passo['PROINI'] || $passo['PROFIN']) {
                unset($matriceSelezionati[$key]);
            }
        }

        $gridOptions = array(
            "Caption" => $caption,
            "width" => '800',
            "height" => '430',
            "multiselect" => $multiselect,
            "readerId" => "ROWID",
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '300',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Seq",
                "Descrizione",
                "Responsabile",
                "Tipo Passo",
                "Giorni",
                "S.",
                "O.",
                "D.",
                ""
            ),
            "colModel" => array(
                array("name" => $colModel1, "width" => 30),
                array("name" => $colModel5, "width" => 200),
                array("name" => 'RESPONSABILE', "width" => 100),
                array("name" => $colModel2, "width" => 200),
                array("name" => $colModel4, "width" => 30),
                array("name" => $colModel6, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => $colModel7, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => $colModel8, "width" => 30, "formatter" => "'checkbox'"),
                array("name" => 'VAI', "width" => 30)
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnPassiSel';
        $_POST['retid'] = 'passiAperti';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicProgesByArray($returnModel, $Result_tab) {
        $praLib = new praLib();
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Pratica N.<br>Richiesta N.<br>Protocollo N.",
            "Data<br>Registrazione",
            "Intestatario",
            "Dati Impresa",
            "Procedimento",
            "Oggetto",
            "Sportello on-line/<br>Aggregato",
        );
        $colonneModel = array(
            array("name" => 'GESNUM', "width" => 150),
            array("name" => 'GESDRE', "width" => 80, "formatter" => "eqdate"),
            array("name" => 'DESNOM', "width" => 130),
            array("name" => 'IMPRESA', "width" => 130),
            array("name" => 'DESCPROC', "width" => 250),
            array("name" => 'GESOGG', "width" => 200),
            array("name" => 'SPORTELLO', "width" => 150),
        );

        $arrayOrd = $praLib->GetOrdinamentoGridGest("GESNUM", "desc");
        $ordinamento = $arrayOrd['sidx'];
        $sord = $arrayOrd['sord'];
        //
        $gridOptions = array(
            "Caption" => 'Ricerca Pratiche',
            "width" => '1200',
            "height" => '470',
            "multiselect" => 'true',
            'multiselectReturnRowData' => 'true',
            "sortname" => $ordinamento,
            "sortorder" => $sord,
            "rowNum" => '20',
            "readerId" => 'ROWID',
            "rowList" => '[]',
            "arrayTable" => array_values($Result_tab),
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnProgesByArray';
        //$_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        //$_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicAnavar($returnModel, $where = '', $retid = '') {
        $sql = "SELECT * FROM ANAVAR";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Codice DOCX",
        );
        $colonneModel = array(
            array("name" => 'VARCOD', "width" => 150),
            array("name" => 'VARDES', "width" => 270),
            array("name" => 'VAREXPDOCX', "width" => 150),
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Variabili Complesse',
            "width" => '620',
            "height" => '470',
            "sortname" => 'VARDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "multiselect" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );

        $filterName = array('VARCOD', 'VARDES');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnavar';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function praRicFileFromArray($arrayFile, $returnModel, $retid = '') {
        $model = 'utiRicDiag';

        $colonneNome = array(
            "Nome File",
            "Dimensione",
        );
        $colonneModel = array(
            array("name" => 'IMGZIP', "width" => 410),
            array("name" => 'SIZE', "width" => 100),
        );
        $gridOptions = array(
            "Caption" => 'Elenco File',
            "width" => '520',
            "height" => '370',
            "sortname" => 'FILENAME',
            "rowNum" => '20',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "arrayTable" => $arrayFile,
        );

        $filterName = array('FILENAME');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnFileFromArray';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>