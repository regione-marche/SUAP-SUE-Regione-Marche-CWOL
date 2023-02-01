<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praRicPasso {

    static function praRicProges($returnModel, $where = '', $retid = '', $infoDetail = 'Seleziona la pratica:') {
//        $praLib = new praLib();
//        $PRAM_DB = $praLib->getPRAMDB();
        $sql = "SELECT PROGES.ROWID,PROGES.GESNUM,PROGES.GESDRE,PROGES.GESPRO,ANADES.DESNOM,ANAPRA.PRADES__1,ANASPA.SPADES,ANATSP.TSPDES
            FROM PROGES PROGES
            LEFT OUTER JOIN ANADES ANADES ON PROGES.GESNUM=ANADES.DESNUM
            LEFT OUTER JOIN ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM
            LEFT OUTER JOIN ANASPA ANASPA ON PROGES.GESSPA=ANASPA.SPACOD
            LEFT OUTER JOIN ANATSP ANATSP ON PROGES.GESTSP=ANATSP.TSPCOD
            WHERE GESDCH=''";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        
        $model = 'utiRicDiag';
        $colonneNome = array(
            "N. Pratica",
            "Data",
            "Intestatario",
            "Procedimento",
            "Aggregato",
            "Sportello",
        );
        $colonneModel = array(
            array("name" => 'GESNUM', "width" => 80),
            array("name" => 'GESDRE', "formatter" => "eqdate", "width" => 80),
            array("name" => 'DESNOM', "width" => 130),
            array("name" => 'PRADES__1', "width" => 250),
            array("name" => 'SPADES', "width" => 100),
            array("name" => 'TSPDES', "width" => 150),
        );
        $gridOptions = array(
            "Caption" => 'Ricerca Pratiche',
            "width" => '850',
            "height" => '470',
            "filterToolbar" => 'true',
            "sortname" => 'GESNUM',
            "sortorder" => "desc",
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('GESNUM', 'GESDRE', 'DESNOM', 'PRADES__1', 'SPADES', 'TSPDES');
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

    static function praRicPropas($returnModel, $where = '', $retid = '', $infoDetail = "Seleziona il passo:") {
        $sql = "SELECT * FROM PROPAS WHERE PROFIN='' AND PROPUB=''";
//        $sql = "SELECT PROPAS.ROWID AS ROWID, PROSEQ, PRODPA FROM PROPAS LEFT OUTER JOIN PRACOM ON PROPAK = COMPAK 
//            WHERE PROFIN='' AND PROPUB='' AND COMTIP<>'A'";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Passi Disponibili',
            "width" => '550',
            "height" => '470',
            "sortname" => 'PROSEQ',
            "rowNum" => '20',
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
                'sqlDB' => 'PRAM',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['keynavButtonAdd'] = 'returnPadre';
        $_POST['returnEvent'] = 'returnPropas';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        // itaLib::openForm($model,true,true,'desktopBody',$returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>