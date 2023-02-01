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

App::requireModel('praLib.class');
App::requireModel('proLib.class');
App::requireModel('proLibPratica.class');

class proRicPratiche {

    static function proRicProges($returnModel, $where = '', $retid = '', $infoDetail = 'Seleziona la pratica:', $tabellaKey = 'PROGES') {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        $praLib = new praLib();
        $proLib = new proLib();
        $PROTDB = $proLib->getPROTDB();
        $sql = "
            SELECT
                $tabellaKey.ROWID AS ROWID,
                {$PROTDB->strConcat($PROTDB->subString('GESNUM', 15, 6), "'/'", $PROTDB->subString('GESNUM', 11, 4))} AS CODICEPRATICA,
                PROGES.GESPRA,
                PROGES.GESDRE,
                PROGES.GESPRO,
                ANAREPARC.DESCRIZIONE AS DESCRIZIONEREPERTORIO,
                {$praLib->getPRAMDB()->getDB()}.ANADES.DESNOM AS DESNOM,
                {$praLib->getPRAMDB()->getDB()}.ANAPRA.PRADES__1 AS PRADES__1
            FROM PROGES PROGES
            LEFT OUTER JOIN ANAREPARC ANAREPARC ON PROGES.GESREP=ANAREPARC.CODICE
            LEFT OUTER JOIN {$praLib->getPRAMDB()->getDB()}.ANADES ANADES ON PROGES.GESNUM={$praLib->getPRAMDB()->getDB()}.ANADES.DESNUM            
            LEFT OUTER JOIN {$praLib->getPRAMDB()->getDB()}.ANAPRA ANAPRA ON PROGES.GESPRO={$praLib->getPRAMDB()->getDB()}.ANAPRA.PRANUM
            WHERE 1 ";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $sql = $sql . " GROUP BY GESNUM";
//        App::log('$sql');
//        App::log($sql);
        $model = 'utiRicDiag';
        $colonneNome = array(
            "N. Pratica",
            "Repertorio",
            "Data",
            "Intestatario",
            "Procedimento"
        );
        $colonneModel = array(
            array("name" => 'CODICEPRATICA', "width" => 120),
            array("name" => 'DESCRIZIONEREPERTORIO', "width" => 280),
            array("name" => 'GESDRE', "formatter" => "eqdate", "width" => 80),
            array("name" => 'DESNOM', "width" => 130),
            array("name" => 'PRADES__1', "width" => 250)
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Pratiche',
            "width" => '850',
            "height" => '420',
            "filterToolbar" => 'true',
            "sortname" => 'GESNUM',
            "sortorder" => "desc",
            "rowNum" => '15',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('GESNUM', 'GESPRA', 'GESDRE', 'DESNOM', 'PRADES__1', 'SPADES', 'TSPDES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnProges';
        $_POST['filterName'] = $filterName;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicPropas($returnModel, $where, $retid = '', $infoDetail = "Elenco Azioni:", $add = false) {
        $sql = "SELECT * FROM PROPAS ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
//        App::log($sql);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Azioni Disponibili',
            "width" => '550',
            "height" => '420',
            "sortname" => 'PROSEQ',
            "rowNum" => '15',
            "rowList" => '[]',
            "colNames" => array(
                "Seq.",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'PROSEQ', "width" => 50),
                array("name" => 'PRODPA', "width" => 400)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        if ($add == true) {
            $_POST['keynavButtonAdd'] = 'returnPadre';
        }
        $_POST['returnEvent'] = 'returnPropas';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAntecedente($returnModel, $returnEvent, $PRAM_DB, $proges_rec) {
        $pronum = $proges_rec['GESNUM'];
        $pronum_radice = $proges_rec['GESPRE'];
        $presenti = array();
        while ($pronum_radice != 0 && !array_key_exists($pronum_radice, $presenti)) {
            $presenti[$pronum_radice] = $pronum_radice;
            $proges_rec = self::checkGESNUM($pronum_radice, $PRAM_DB, $tipo);
            if (!$proges_rec) {
                $presenti[$pronum_radice] = $pronum_radice;
                break;
            }
            //$proges_rec=$anapro_ver;
            if ($proges_rec['GESPRE'] != 0)
                $pronum_radice = $proges_rec['GESPRE'];
        }
        if ($pronum_radice == 0) {
            $pronum_radice = $pronum;
            $proges_rec = self::checkGESNUM($pronum_radice, $PRAM_DB, $tipo);
        }
        $presenti = array();
        $rowid = $proges_rec['ROWID'];
        $matriceSelezionati = self::CaricaGrigliaAntecedenti($pronum, $PRAM_DB, $proges_rec, $pronum_radice, $presenti, $tipo, $rowid);

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
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "ID",
                "N. Pratica",
                "Procedimento",
                "Data Regis.",
                "Intestatario"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROGRESSIVO', "width" => 150),
                array("name" => 'PRADES', "width" => 300),
                array("name" => 'GESDRE', "formatter" => "eqdate", "width" => 80),
                array("name" => 'DESNOM', "width" => 150)
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

    static function proPassiSelezionati($matriceSelezionati, $returnModel, $retid, $caption, $from = "ITEPAS") {
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
                break;
            case 'PROPAS':
                $sortName = 'PROSEQ';
                $colModel1 = 'PROSEQ';
                $colModel2 = 'PRODTP';
                $colModel4 = 'PROGIO';
                $colModel5 = 'PRODPA';
                $colModel6 = 'PROPUB';
                $colModel8 = 'PROQST';
                break;
        }

        $gridOptions = array(
            "Caption" => $caption,
            "width" => '800',
            "height" => '430',
            "multiselect" => 'true',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '300',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Seq",
                "Tipo Azione",
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
        $_POST['returnEvent'] = 'returnPassiSel';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>