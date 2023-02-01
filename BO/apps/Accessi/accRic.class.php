<?php

class accRic {

    static function accRicGru($returnModel, $retid = '', $where = '') {
        $sql = "SELECT ROWID, GRUCOD, GRUDES FROM GRUPPI";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione");
        $colonneModel = array(
            array("name" => 'GRUCOD', "width" => 80),
            array("name" => 'GRUDES', "width" => 400));
        $gridOptions = array(
            "Caption" => 'Elenco Gruppi',
            "width" => '500',
            "height" => "'80%'",
            "sortname" => 'GRUDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'ITW',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returngru';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function accRicUtenti($returnModel, $retid = '', $where = '') {
        $sql = "SELECT ROWID, UTELOG FROM UTENTI";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array("Utente");
        $colonneModel = array(array("name" => 'UTELOG', "width" => 480));
        $gridOptions = array(
            "Caption" => 'Elenco Utenti',
            "width" => '500',
            "height" => "'80%'",
            "sortname" => 'UTELOG',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'ITW',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('UTELOG');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnutenti';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function accRicAnamed($returnModel, $where = '') {
        $sql = "SELECT * FROM ANAMED";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';

//        $gridOptions = array(
//            "Caption" => 'Elenco Mittenti Destinatari',
//            "filterToolbar" => 'true',
//            "width" => '550',
//            "height" => "400",
//            "sortname" => 'MEDNOM',
//            "rowList" => '[]',
//            "pginput" => 'false',
//            "pgbuttons" => 'false',
//            "rowNum" => '99999',
//            "colNames" => array(
//                "Codice",
//                "Nominativo",
//            ),
//            "colModel" => array(
//                array("name" => 'MEDCOD', "width" => 100),
//                array("name" => 'MEDNOM', "width" => 400),
//            ),
//            "dataSource" => array(
//                'sqlDB' => 'PROT',
//                'sqlQuery' => $sql
//            )
//        );

        $gridOptions = array(
            "Caption" => 'Elenco Mittenti Destinatari',
            "width" => '880',
            "height" => '400',
            "sortname" => 'MEDNOM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo",
                "E-mail",
                "Indirizzo",
                "Città",
                "PR"
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 60),
                array("name" => 'MEDNOM', "width" => 250),
                array("name" => 'MEDEMA', "width" => 200),
                array("name" => 'MEDIND', "width" => 200),
                array("name" => 'MEDCIT', "width" => 100),
                array("name" => 'MEDPRO', "width" => 30)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('MEDCOD', 'MEDNOM', 'MEDEMA', 'MEDIND', 'MEDCIT', 'MEDPRO');

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanamed';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}
