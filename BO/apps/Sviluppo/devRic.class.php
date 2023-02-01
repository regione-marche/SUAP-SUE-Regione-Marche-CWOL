<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class devRic {

    static function devElencoReportArray($returnModel, $matrice = '', $returnEvent = 'returnElencoReport') {
        // Possibilità di usare una Ric
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Stampe',
            "width" => '350',
            "height" => '350',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $matrice,
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 200, "hidden" => 'true'),
                array("name" => 'DESCRIZIONE', "width" => 330)
            ),
            "pgbuttons" => 'false',
            "pginput" => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function devElencoReport($returnModel, $post, $where = '', $returnEvent = '') {
        include_once './apps/Sviluppo/devLib.class.php';
        $devlib = new devLib();
        $matrice = array();
        $ita_gestreport_tab = $devlib->getGenericTab("SELECT * FROM ita_gestreport " . $where, 'ITALSOFTDB');
        foreach ($ita_gestreport_tab as $key => $ita_gestreport_rec) {
            $ita_gestreport_rec['TIPO'] = 'ita_gestreport';
            $matrice[] = $ita_gestreport_rec;
        }
        $REP_GEST_tab = $devlib->getGenericTab("SELECT * FROM REP_GEST " . $where, 'ITALWEB');
        foreach ($REP_GEST_tab as $key => $REP_GEST_rec) {
            $REP_GEST_rec['TIPO'] = 'REP_GEST';
            $matrice[] = $REP_GEST_rec;
        }
        if ($matrice) {
            $matrice = $devlib->array_sort($matrice, 'SEQUENZA');
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Report di Stampa',
            "width" => '510',
            "height" => '420',
            "rowNum" => '9000',
            "rowList" => '[]',
            "arrayTable" => $matrice,
            "colNames" => array(
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'DESCRIZIONE', "width" => 500)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnElencoReport' . $returnEvent;
        $_POST['retid'] = $post;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>