<?php

function utiFSDiag() {
    $utiFSDiag = new utiFSDiag();
    $utiFSDiag->parseEvent();
    return;
}

class utiFSDiag extends itaModel {

    /**
     * 
     * @param type $albero
     * @param type $returnModel
     * @param type $retid
     * @param type $caption
     * @param type $edit
     */
    static function utiRicFolder($albero, $returnModel, $returnEvent = 'returnFolder', $retid, $caption = 'Selezionare la cartella', $edit = false) {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $caption,
            "width" => '500',
            "height" => '400',
            "multiselect" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '10000000',
            "rowList" => '[]',
            "navGrid" => 'true',
            "navButtonAdd" => "true",
            "navButtonEdit" => "false",
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "arrayTable" => $albero,
            "treeGrid" => true,
            "treeGridModel" => 'adjacency',
            "colNames" => array(
                "Cartella",
                "N. File",
            ),
            "colModel" => array(
                array("name" => 'CARTELLA', "width" => 300),
                array("name" => 'NUMFILE', "width" => 50)
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        //itaLib::openForm($model);
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>
