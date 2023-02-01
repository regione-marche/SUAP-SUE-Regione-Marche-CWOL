<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Bufarini Andrea<andrea.bufarini@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    19.12.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class basRic {

    /**
     * Libreria di funzioni Generiche e Utility per Ricerca vie, comuni, ecc
     *
     */
    static function basRicVia($returnModel, $where = '', $returnEvent = 'returnAnavia', $retid = "") {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='VIE'";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Vie',
            "width" => '550',
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
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function basRicZona($returnModel, $where = '', $returnEvent = 'returnAnazone', $retid = "") {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='ZONE'";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Zone',
            "width" => '550',
            "height" => '420',
            "sortname" => 'ANADES',
            "rowNum" => '20',
            "filterToolbar" => 'false',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Tariffa"
            ),
            "colModel" => array(
                array("name" => 'ANACOD', "width" => 70),
                array("name" => 'ANADES', "width" => 300),
                array("name" => 'ANAFI2__1', "width" => 40)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        //$filterName = array('ANACOD','ANADES');
        $_POST = array();
        $_POST['event'] = 'openform';
        //$_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function basRicArea($returnModel, $where = '', $returnEvent = 'returnArea', $retid = "") {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT='AREE' GROUP BY ANACOD";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Aree',
            "width" => '550',
            "height" => '420',
            "sortname" => 'ANADES',
            "rowNum" => '20',
            "filterToolbar" => 'false',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'ANACOD', "width" => 70),
                array("name" => 'ANADES', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        //$filterName = array('ANACOD','ANADES');
        $_POST = array();
        $_POST['event'] = 'openform';
        //$_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function basRicAutomezzi($returnModel, $returnEvent = 'returnAutomezzi', $retid = "") {
        $sql = "SELECT * FROM ANA_AUTOMEZZI ";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Automezzi',
            "width" => '550',
            "height" => '420',
            "sortname" => 'AUTO',
            "rowNum" => '20',
            "filterToolbar" => 'false',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Targa"
            ),
            "colModel" => array(
                array("name" => 'AUTO', "width" => 100),
                array("name" => 'DESCAUTO', "width" => 300),
                array("name" => 'TARGA', "width" => 90)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        //$filterName = array('ANACOD','ANADES');
        $_POST = array();
        $_POST['event'] = 'openform';
        //$_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function basRicNazioni($returnModel, $ricerca = '', $returnEvent = 'returnNazioni') {
        $sql = "SELECT * FROM NAZIONI";
        if ($ricerca != '') {
            $sql = $sql . ' ' . $ricerca;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Nazioni',
            "width" => '400',
            "height" => '420',
            "sortname" => 'DESCRIZIONE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice ONU",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICEONU', "width" => 80),
                array("name" => 'DESCRIZIONE', "width" => 250)
            ),
            "dataSource" => array(
                'sqlDB' => 'COMUNI',
                'sqlQuery' => $sql,
                'dbSuffix' => ""
            )
        );
        $filterName = array('CODICEONU', 'DESCRIZIONE');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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

    static function basRicAreeLingua($returnModel, $ricerca = '', $returnEvent = 'returnAreaLingua') {
        $sql = "SELECT * FROM AREELINGUISTICHE";
        if ($ricerca != '') {
            $sql = $sql . ' ' . $ricerca;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "readerId" => 'ROW_ID',
            "Caption" => 'Elenco Lingue',
            "width" => '400',
            "height" => '420',
            "sortname" => 'DESCRIZIONE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 80),
                array("name" => 'DESCRIZIONE', "width" => 250)
            ),
            "dataSource" => array(
                'sqlDB' => 'COMUNI',
                'sqlQuery' => $sql,
                'dbSuffix' => ""
            )
        );
        $filterName = array('CODICEONU', 'DESCRIZIONE');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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

    static function basRicVieAnacat($matriceSelezionati, $returnModel, $retid = "") {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Ricerca Catagorie Vie",
            "width" => '450',
            "height" => '430',
            "sortname" => "CATEGORIA",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Categoria",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'CATEGORIA', "width" => 100),
                array("name" => 'DESCRIZIONE', "width" => 250)
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = "returnCategorieVie";
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function basRicRuoli($returnModel, $ricerca = '', $returnEvent = 'returnAnaruo') {
        $sql = "SELECT * FROM ANA_RUOLI";
        if ($ricerca != '') {
            $sql = $sql . ' ' . $ricerca;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            // "readerId" => 'ROW_ID',
            "Caption" => 'Ricerca Ruoli',
            "width" => '400',
            "height" => '420',
            "sortname" => 'RUODES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'RUOCOD', "width" => 80),
                array("name" => 'RUODES', "width" => 250)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql,
            // 'dbSuffix' => ""
            )
        );
        $filterName = array('RUOCOD', 'RUODES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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

    static function basRicAnaSoggetti($returnModel, $ricerca = '', $returnEvent = 'returnAnaSoggetti') {
        $sql = "SELECT * FROM ANA_SOGGETTI";
        if ($ricerca != '') {
            $sql = $sql . ' ' . $ricerca;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            // "readerId" => 'ROW_ID',
            "Caption" => 'Ricerca Anagrafica Soggetti',
            "width" => '950',
            "height" => '420',
            "sortname" => 'COGNOME',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Cognome",
                "Nome",
                "Codice Fiscale",
                "Partita Iva"
            ),
            "colModel" => array(
                array("name" => 'COGNOME', "width" => 300),
                array("name" => 'NOME', "width" => 200),
                array("name" => 'CF', "width" => 200),
                array("name" => 'PIVA', "width" => 200)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql,
            // 'dbSuffix' => ""
            )
        );
        $filterName = array('COGNOME', 'NOME', 'CF','PIVA');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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

    static function basRicComuni($returnModel, $ricerca = '', $returnEvent = 'returnComune') {
        $sql = "SELECT * FROM COMUNI";
        if ($ricerca != '') {
            $sql = $sql . ' ' . $ricerca;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Comuni',
            "width" => '300',
            "height" => '420',
            "sortname" => 'COMUNE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'COMUNE', "width" => 250)
            ),
            "dataSource" => array(
                'sqlDB' => 'COMUNI',
                'sqlQuery' => $sql,
                'dbSuffix' => ""
            )
        );
        $filterName = array('COMUNE');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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

}
