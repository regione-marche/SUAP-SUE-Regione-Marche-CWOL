<?php

class docRic {

    static function docRicDocumentiAdv($returnModel, $classificazione, $funzione, $where = '', $returnEvent = 'returnDocumenti') {
        $sql = "
            SELECT
                ROWID,
                CODICE,
                OGGETTO,
                CLASSIFICAZIONE,
                TIPO
            FROM
                DOC_DOCUMENTI
            WHERE 1=1 AND CLASSIFICAZIONE = '$classificazione' AND FUNZIONE = '$funzione'
            ";

        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Tipo");
        $colonneModel = array(
            array("name" => 'CODICE', "width" => 150),
            array("name" => 'OGGETTO', "width" => 400),
            array("name" => 'TIPO', "width" => 150));
        $gridOptions = array(
            "Caption" => 'Elenco Documenti',
            "width" => '720',
            "height" => '500',
            "sortname" => 'OGGETTO',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "filterToolbar" => 'true',
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'OGGETTO', 'TIPO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function docRicDocumenti($returnModel, $where = '', $dove = '') {
        $sql = "SELECT ROWID, CODICE, OGGETTO, CLASSIFICAZIONE, TIPO FROM DOC_DOCUMENTI";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Tipo");
        $colonneModel = array(
            array("name" => 'CODICE', "width" => 150),
            array("name" => 'OGGETTO', "width" => 400),
        array("name" => 'TIPO', "width" => 150, "search" => "true", "stype" => "'select'", 'editoptions' => "{ value: {} }"));
        $gridOptions = array(
            "Caption" => 'Elenco Documenti',
            "width" => '720',
            "height" => '500',
            "sortname" => 'OGGETTO',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "filterToolbar" => 'true',
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'OGGETTO', 'TIPO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnDocumenti' . $dove;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $utiRic = itaModel::getInstance($model);
        $utiRic->parseEvent();
        
        /*
         * Imposto la select con i tipi di docDocumenti
         */
        include_once ITA_BASE_PATH . '/apps/Documenti/docDocumenti.php';
        $docDocumenti = new docDocumenti();
        $tipiDoc = $docDocumenti->arrTipi;
        TableView::tableSetFilterSelect($utiRic->nameGrid, "TIPO", 1, "", 0, "");
        foreach ($tipiDoc as $key => $value) {
            TableView::tableSetFilterSelect($utiRic->nameGrid, "TIPO", 1, $key, 0, $value);
        }
    }
    
    static function docRicDocumentiStorico($returnModel, $classificazione = '', $codice = '', $returnEvent = 'returnDocumentiStorico') {
        if (!$codice){
            return false;
        }
        $sql = "SELECT ROWID, CODICE, OGGETTO, CLASSIFICAZIONE, TIPO, DATAREV, NUMREV FROM DOC_STORICO "
                . " WHERE CLASSIFICAZIONE = '" . addslashes($classificazione) . "' AND CODICE = '" . addslashes($codice) . "'";
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Data Rev.",
            "Num.",
            "Codice",
            "Descrizione",
            "Tipo"
            );
        $colonneModel = array(
            array("name" => 'DATAREV', "formatter" => "eqdate", "width" => 70),
            array("name" => 'NUMREV', "width" => 30),
            array("name" => 'CODICE', "width" => 150),
            array("name" => 'OGGETTO', "width" => 300),
            array("name" => 'TIPO', "width" => 150)
            );
        $gridOptions = array(
            "Caption" => 'Elenco Versioni',
            "width" => '720',
            "height" => '400',
            "sortname" => 'NUMREV',
            "sortorder" => 'desc',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "filterToolbar" => 'true',
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'OGGETTO', 'TIPO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function docRicLayout($returnModel, $classificazione, $baseRow = true, $where = '', $dove = '') {

        $sql = "SELECT ROWID, CODICE, OGGETTO FROM DOC_DOCUMENTI WHERE TIPO = 'XLAYOUT'";
        if ($classificazione != "") {
            $codice = $_POST[$this->nameForm . '_classificazione'];
            $sql .= " AND CLASSIFICAZIONE = '" . $codice . "'";
        }
//                        $Doc_documenti_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sqlCombo, true);
//                        if ($Doc_documenti_tab) {
//                            $layout_tab = array("ROWID" => 'BASE', "CODICE" => "BASE", "OGGETTO" => "Modello pagina del testo base.");
//                            $layout_tab = array_merge($layout_tab, $Doc_documenti_tab);
//                            
//                        }
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione");
        $colonneModel = array(
            array("name" => 'CODICE', "width" => 150),
            array("name" => 'OGGETTO', "width" => 400));
        $gridOptions = array(
            "Caption" => 'Elenco Modelli pagina',
            "width" => '720',
            "height" => '500',
            "sortname" => 'OGGETTO',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "filterToolbar" => 'false',
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        //$filterName = array('CODICE', 'OGGETTO', 'TIPO');
        $_POST = array();
        $_POST['event'] = 'openform';
        //$_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnXlayout' . $dove;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricVariabili($matrice, $returnModel, $returnEvent = 'returnVariabili', $adjacency = false) {
        //
        //Se variabili anavar e tipo html tolgo il valore dalla griglia
        //
        foreach ($matrice as $key => $variabile) {
            if (strpos($variabile['chiave'], "PRAANAVAR.") !== false && $variabile['type'] == "01") {
                $matrice[$key]['valore'] = "";
            }
        }
        //

        if ($adjacency == true) {
            $model = 'utiRicDiag';
            $gridOptions = array(
                "Caption" => 'Elenco Variabili',
                "width" => '850',
                "height" => '430',
                "sortname" => "valore",
                "sortorder" => "desc",
                "rowNum" => '12000',
                "rowList" => '[]',
                "treeGrid" => 'true',
                "ExpCol" => 'descrizione',
                "arrayTable" => $matrice,
                "colNames" => array(
                    "varidx",
                    "Descrizione",
                    "Variabile",
                    "Valore"
                ),
                "colModel" => array(
                    array("name" => 'varidx', "width" => 200, "hidden" => "true", "key" => "true"),
                    array("name" => 'descrizione', "width" => 400),
                    array("name" => 'markupkey', "width" => 200),
                    array("name" => 'valore', "width" => 200, "formatter" => "eqdate")
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
                    array("name" => 'descrizione', "width" => 200),
                    array("name" => 'valore', "width" => 200)
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

}

?>