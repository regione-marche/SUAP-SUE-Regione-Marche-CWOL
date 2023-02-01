<?php

/* * 
 *
 * RICERCHE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    02.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

class proRic {

    /**
     * Ricerca su Archivio Categorie
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    static function proRicCat($returnModel, $where = '', $visData = false, $datiRitorno = array()) {

        $sql = "SELECT ROWID, CATCOD, CATDES, CATDAT FROM ANACAT";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        if ($visData == true) {
            $colonneNome = array(
                "Codice",
                "Descrizione",
                "Data Registrazione");
            $colonneModel = array(
                array("name" => 'CATCOD', "width" => 100),
                array("name" => 'CATDES', "width" => 300),
                array("name" => 'CATDAT', "formatter" => "eqdate", "width" => 100));
        } else {
            $colonneNome = array(
                "Codice",
                "Descrizione");
            $colonneModel = array(
                array("name" => 'CATCOD', "width" => 100),
                array("name" => 'CATDES', "width" => 300));
        }
        $gridOptions = array(
            "Caption" => 'Elenco Categorie',
            "width" => '500',
            "height" => '400',
            "sortname" => 'CATDES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CATCOD', 'CATDES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['where'] = $where;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returncat';
        $_POST['retid'] = $datiRitorno;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicCla($returnModel, $where = '', $datiRitorno = array()) {
        $sql = "SELECT * FROM ANACLA";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Classi',
            "width" => '500',
            "height" => '400',
            "sortname" => 'CLADE1',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CLACOD', "width" => 100),
                array("name" => 'CLADE1', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $_POST = array();
        $filterName = array('CLACOD', 'CLADE1');
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returncla';
        $_POST['retid'] = $datiRitorno;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicFas($returnModel, $where = '', $datiRitorno = array()) {
        $sql = "SELECT * FROM ANAFAS";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Sotto Classi',
            "width" => '550',
            "height" => '400',
            "sortname" => 'FASDE1',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'FASCOD', "width" => 100),
                array("name" => 'FASDE1', "width" => 400)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('FASCOD', 'FASDE1');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnfas';
        $_POST['retid'] = $datiRitorno;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicOrg($returnModel, $where = '', $aggiungi = '', $datiRitorno = array()) {
        $sql = "SELECT * FROM ANAORG";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Fascicoli',
            "width" => '550',
            "height" => '400',
            "sortname" => 'ORGDES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Titolario",
                "Anno",
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'ORGCCF', "width" => 100),
                array("name" => 'ORGANN', "width" => 60),
                array("name" => 'ORGCOD', "width" => 80),
                array("name" => 'ORGDES', "width" => 250),
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('ORGCCF', 'ORGCOD', 'ORGDES', 'ORGANN');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnorg';
        $_POST['retid'] = $datiRitorno;
        $_POST['returnKey'] = 'retKey';
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicOrgFas($returnModel, $where = '', $aggiungi = '', $datiRitorno = array()) {
        $sql = "SELECT ANAORG.*, PROGES.GESOGG AS GESOGG 
            FROM ANAORG LEFT OUTER JOIN PROGES
            ON ANAORG.ORGKEY = PROGES.GESKEY";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Fascicoli',
            "width" => '770',
            "height" => '400',
            "sortname" => 'ORGDES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Titolario",
                "Anno",
                "Codice",
                "Segnatura",
                "Fascicolo"
            ),
            "colModel" => array(
                array("name" => 'ORGCCF', "width" => 100),
                array("name" => 'ORGANN', "width" => 60),
                array("name" => 'ORGCOD', "width" => 80),
                array("name" => 'ORGSEG', "width" => 170),
                array("name" => 'GESOGG', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('ORGCCF', 'ORGCOD', 'ORGDES', 'ORGANN');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnorgfas';
        $_POST['retid'] = $datiRitorno;
        $_POST['returnKey'] = 'retKey';
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAlberoFascicolo($returnModel, $matriceSelezionati, $retid = '', $infoDetail = "Contenuto del Fascicolo:", $add = false) {

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elementi Disponibili',
            "width" => '600',
            "height" => '400',
            "sortname" => 'ORGNODEICO',
            "sortorder" => "desc",
            "rowNum" => '2000',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'ORGNODEICO',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "Numero",
                "",
                "Descrizione Documento"
            ),
            "colModel" => array(
                array("name" => 'ORGNODEKEY', "width" => 1, "hidden" => "true", "key" => "true"),
                array("name" => 'ORGNODEICO', "width" => 150),
                array("name" => 'NOTE', "width" => 430)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        if ($add == true) {
            $_POST['keynavButtonAdd'] = 'returnPadre';
        }
        $_POST['returnEvent'] = 'returnAlberoFascicolo';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicOgg($returnModel, $where = '', $aggiungi = '', $dati = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANADOG $where";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Oggetti',
            "width" => '550',
            "height" => '400',
            "sortname" => 'DOGDEX',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'DOGCOD', "width" => 100),
                array("name" => 'DOGDEX', "width" => 500)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('DOGCOD', 'DOGDEX');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returndog' . $returnEvent;
        $_POST['retid'] = $dati;
        $_POST['returnKey'] = 'retKey';
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicSoggetto($returnModel, $returnId = '', $returnEvent = 'returnRicSoggetto', $filtri = array(), $infoDetail = "") {
        $proLib = new proLib();
        $where = "
            LEFT OUTER JOIN UFFDES ON ANAMED.MEDCOD=UFFDES.UFFKEY
            LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD
            WHERE UFFCESVAL='' AND MEDUFF " . $proLib->getPROTDB()->isNotBlank();
        $where .= ($filtri['RUOLOUFFICIO']) ? "AND UFFFI1__2='{$filtri['RUOLOUFFICIO']}'" : "";
        $where .= ($filtri['UFFICIO']) ? "AND UFFDES.UFFCOD='{$filtri['UFFCOD']}'" : "";
        self::proRicAnamed($returnModel, $where, '', $returnId, $returnEvent, ' GROUP BY ANAMED.MEDCOD');
    }

    static function proRicAnamed($returnModel, $where = '', $aggiungi = '', $returnId = '', $returnEvent = 'returnanamed', $groupby = '', $infoDetail = "") {
        $sql = "SELECT ANAMED.* FROM ANAMED";
        if ($where != '') {
            $sql .= ' ' . $where . ' AND ANAMED.MEDANN=0 ' . $groupby;
        } else {
            $sql .= ' WHERE ANAMED.MEDANN=0 ' . $groupby;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Mittenti Destinatari',
            "width" => '980',
            "height" => '400',
            "sortname" => 'MEDNOM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo",
                "Tag",
                "E-mail",
                "Indirizzo",
                "Città",
                "PR"
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 60),
                array("name" => 'MEDNOM', "width" => 250),
                array("name" => 'MEDTAG', "width" => 100),
                array("name" => 'MEDEMA', "width" => 200),
                array("name" => 'MEDIND', "width" => 200),
                array("name" => 'MEDCIT', "width" => 80),
                array("name" => 'MEDPRO', "width" => 30)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('MEDCOD', 'MEDNOM', 'MEDTAG', 'MEDEMA', 'MEDIND', 'MEDCIT', 'MEDPRO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnamedMulti($returnModel, $where = '', $aggiungi = '', $returnId = '', $returnEvent = 'returnanamedMulti', $groupby = '', $infoDetail = "") {
        $sql = "SELECT ANAMED.* FROM ANAMED";
        if ($where != '') {
            $sql .= ' ' . $where . ' AND ANAMED.MEDANN=0 ' . $groupby;
        } else {
            $sql .= ' WHERE ANAMED.MEDANN=0 ' . $groupby;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Mittenti Destinatari',
            "width" => '1200',
            "height" => '400',
            "sortname" => 'MEDNOM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "multiselect" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo",
                "Cod. Fiscale/<br>Partita Iva",
                "Tag",
                "E-mail",
                "Indirizzo",
                "Città",
                "PR"
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 60),
                array("name" => 'MEDNOM', "width" => 250),
                array("name" => 'MEDFIS', "width" => 120),
                array("name" => 'MEDTAG', "width" => 100),
                array("name" => 'MEDEMA', "width" => 200),
                array("name" => 'MEDIND', "width" => 200),
                array("name" => 'MEDCIT', "width" => 80),
                array("name" => 'MEDPRO', "width" => 30)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('MEDCOD', 'MEDNOM', 'MEDTAG', 'MEDEMA', 'MEDIND', 'MEDCIT', 'MEDPRO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicTabdagMail($returnModel, $classe, $rowidClasse, $where = '', $title = "Dati Aggiuntivi Destinatario", $returnId = '', $returnEvent = 'returnTabdag') {
        $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$classe' AND TDROWIDCLASSE=$rowidClasse AND (TDAGCHIAVE='EMAILPEC' OR TDAGCHIAVE='EMAIL')";
        if ($where != '') {
            $sql .= ' ' . $where;
        }
        $prolib = new proLib();
        $Tabdag_tab = ItaDB::DBSQLSelect($prolib->getPROTDB(), $sql, true);
        if (count($Tabdag_tab) == 1) {
            return $Tabdag_tab[0]['ROWID'];
        }


        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $title,
            "width" => '730',
            "height" => '400',
            "sortname" => 'TDAGSEQ',
            "rowNum" => '10000',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowList" => '[]',
            "colNames" => array(
                "Sequenza",
                "Valore",
                ""
            ),
            "colModel" => array(
                array("name" => 'TDAGSEQ', "width" => 100),
                array("name" => 'TDAGVAL', "width" => 400),
                array("name" => 'TDAGCHIAVE', "width" => 100)
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
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        return false;
    }

    /**
     * Dialog di ricerca dei componeti di un ufficio
     * 
     * @param type $returnModel     moodel di ritorno
     * @param type $codice          Codice ufficio
     * @param type $where           $where Aggiuntiva
     * @param type $returnId        id di ritorno
     * @param type $returnEvent     Evento di ritorno = "returnUfficiDestinatari" e si anngiunge la variabile $returnEvent
     */
    static function proRicUfficiDestinatari($returnModel, $codice, $where = '', $returnId = '', $returnEvent = '') {
        $sql = "SELECT
                    ANAMED.ROWID AS ROWID, 
                    ANAMED.MEDCOD AS MEDCOD, 
                    ANAMED.MEDNOM AS MEDNOM, 
                    ANAMED.MEDTAG AS MEDTAG, 
                    UFFDES.UFFKEY AS UFFKEY,
                    UFFDES.UFFCOD AS UFFCOD, 
                    UFFDES.UFFSCA AS UFFSCA
                FROM
                    ANAMED
                LEFT OUTER JOIN UFFDES
                ON ANAMED.MEDCOD = UFFDES.UFFKEY
                WHERE
                UFFDES.UFFCOD='" . $codice . "' AND
                UFFDES.UFFCESVAL=''"; // AND
        //AND ANAUFF.UFFANN=0";
        if ($where != '')
            $sql = $sql . ' ' . $where;

        App::log($sql);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Destinatari',
            "width" => '500',
            "height" => '400',
            "sortname" => 'MEDNOM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo",
                "Tag",
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 60),
                array("name" => 'MEDNOM', "width" => 330),
                array("name" => 'MEDTAG', "width" => 100)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('MEDCOD', 'MEDNOM', 'MEDTAG');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUfficiDestinatari' . $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicUfficiPerDestinatario($returnModel, $codice, $where = '', $returnId = '', $returnEvent = '', $msgDetail = '') {
        $sql = "SELECT ANAUFF.ROWID AS ROWID, ANAUFF.UFFCOD AS UFFCOD, ANAUFF.UFFDES AS UFFDES,
               (SELECT ANAUFFPADRE.UFFDES FROM ANAUFF ANAUFFPADRE WHERE ANAUFFPADRE.UFFCOD=ANAUFF.CODICE_PADRE) AS PADRE
                FROM ANAMED LEFT OUTER JOIN UFFDES ON ANAMED.MEDCOD = UFFDES.UFFKEY
                LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD = ANAUFF.UFFCOD
                WHERE UFFDES.UFFCESVAL='' AND ANAUFF.UFFANN=0 AND ANAMED.MEDCOD='" . $codice . "' $where ORDER BY UFFFI1__3 DESC, UFFSCA DESC, UFFFI1__1 DESC, UFFDES ASC";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Seleziona l Uffici di appartenenza",
            "width" => '630',
            "height" => '400',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo",
                "Ufficio Padre",
            ),
            "colModel" => array(
                array("name" => 'UFFCOD', "width" => 60),
                array("name" => 'UFFDES', "width" => 300),
                array("name" => 'PADRE', "width" => 280)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('UFFCOD', 'UFFDES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnUfficiPerDestinatario' . $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnauff($returnModel, $where = '', $returnEvent = 'returnanauff') {
//        $sql = "SELECT * FROM ANAUFF ";
        $sql = "SELECT * FROM ANAUFF WHERE ANAUFF.UFFANN=0 ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Uffici',
            "width" => '550',
            "height" => '400',
            "sortname" => 'UFFDES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Ufficio",
            ),
            "colModel" => array(
                array("name" => 'UFFCOD', "width" => 100),
                array("name" => 'UFFDES', "width" => 400),
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('UFFCOD', 'UFFDES');
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

    static function proRicAnaruoli($returnModel, $where = '', $retId = '', $returnEvent = 'returnanaruoli') {
        $sql = "SELECT * FROM ANARUOLI";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Ruoli',
            "width" => '550',
            "height" => '400',
            "sortname" => 'RUODES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Ruolo",
            ),
            "colModel" => array(
                array("name" => 'RUOCOD', "width" => 100),
                array("name" => 'RUODES', "width" => 400),
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
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnaservizi($returnModel, $where = '', $retId = '') {
        $sql = "SELECT * FROM ANASERVIZI";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Settori',
            "width" => '550',
            "height" => '400',
            "sortname" => 'SERDES',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Ruolo"
            ),
            "colModel" => array(
                array("name" => 'SERCOD', "width" => 100),
                array("name" => 'SERDES', "width" => 400)
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
        $_POST['returnEvent'] = 'returnanaservizi';
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnareparc($returnModel, $where = '', $retId = '') {
        $sql = "SELECT * FROM ANAREPARC";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Repertori',
            "width" => '550',
            "height" => '400',
            "sortname" => 'CODICE',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'DESCRIZIONE', "width" => 400)
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
        $_POST['returnEvent'] = 'returnanareparc';
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnatsp($returnModel, $where = '') {
        $sql = "SELECT * FROM ANATSP";
        if ($where != '')
            $sql = $sql . ' ' . $where;

        $prolib = new proLib();
        $Anatsp_tab = ItaDB::DBSQLSelect($prolib->getPROTDB(), $sql, true);

        $TabTipologie = array();
        foreach ($Anatsp_tab as $Anatsp_rec) {
            $key = $Anatsp_rec['ROWID'];
            $TabTipologie[$key]['TSPCOD'] = $Anatsp_rec['TSPCOD'];
            $TabTipologie[$key]['TSPDES'] = $Anatsp_rec['TSPDES'];
            switch ($Anatsp_rec['TSPTIPO']) {
                case '1':
                    $TabTipologie[$key]['TSPTIPO'] = 'Mail Normale';
                    break;
                case '2':
                    $TabTipologie[$key]['TSPTIPO'] = 'Pec';
                    break;
                case '':
                default:
                    $TabTipologie[$key]['TSPTIPO'] = 'Analogico';
                    break;
            }
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Tipologia Spedizione',
            "width" => '550',
            "height" => '400',
            "sortname" => 'TSPDES',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "arrayTable" => $TabTipologie,
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Spedizione",
                "Tipo"
            ),
            "colModel" => array(
                array("name" => 'TSPCOD', "width" => 100),
                array("name" => 'TSPDES', "width" => 300),
                array("name" => 'TSPTIPO', "width" => 100)
            ),
        );
        $filterName = array('TSPCOD', 'TSPDES');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanatsp';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicNumAntecedenti($returnModel, $where = '', $propar = '', $returnEvent = '', $datiPost = null) {
        $sql = "SELECT DISTINCT ANAPRO.ROWID AS ROWID, ANAPRO.PROPAR AS PROPAR, ANAPRO.PRONUM AS PRONUM, PRONOM, PRODAR, PRONPA, OGGOGG FROM ANAPRO ANAPRO
            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
            LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
            LEFT OUTER JOIN UFFPRO UFFPRO ON ANAPRO.PRONUM=UFFPRO.PRONUM AND ANAPRO.PROPAR=UFFPRO.UFFPAR"; // RIGA DA TOGLIERE DOPO LA SISTEMAZIONE DEI DATI DI CORRIDONIA
        $whereTipo = "";
        if ($where == '') {
            if ($propar != '') {
                $whereTipo = " AND ANAPRO.PROPAR BETWEEN '$propar' AND '$propar'";
            }
            $data = date('Ymd');
            $newdata = date('Ymd', strtotime('-30 day', strtotime($data)));
            $sql .= " WHERE ANAPRO.PRODAR BETWEEN
            '" . $newdata . "' AND '" . $data . "'" . $whereTipo;
        } else {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= " AND (ANAPRO.PROPAR = 'A' OR ANAPRO.PROPAR = 'P' OR ANAPRO.PROPAR = 'C' ) "; //Patch Gli Annullati non ha senso..// occorre continuare ad escluderli gli annullati?
        $proLib = new proLib();
        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($proLib);
        $sql .= " AND $where_profilo AND ANAPRO.PROTSO = 0";
//        App::log('$sql');
//        App::log($sql);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Protocolli',
            "width" => '700', //800
            "height" => '350', //470
            "sortname" => "PRONUM",
            "sortorder" => "desc",
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "A/P",
                "Protocollo",
                "Mitt/Dest",
                "Data Reg.",
                "Prot.Mitt.",
                "Oggetto"
            ),
            "colModel" => array(
                array("name" => 'PROPAR', "width" => 40),
                array("name" => 'PRONUM', "width" => 100),
                array("name" => 'PRONOM', "width" => 200),
                array("name" => 'PRODAR', "formatter" => "eqdate", "width" => 100),
                array("name" => 'PRONPA', "width" => 100),
                array("name" => 'OGGOGG', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('PROPAR', 'ANAPRO.PRONUM', 'PRONOM', 'PRODAR', 'PRONPA', 'OGGOGG');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnNumAnte';
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $datiPost;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicFascicolo($proLib, $returnModel, $returnEvent, $PROT_DB, $anapro_orig) {
        $orgkey = $anapro_orig['PROFASKEY'];

        $anaorg_rec = $proLib->GetAnaorg($orgkey, 'orgkey');
        if (!$anaorg_rec) {
            return;
        }
        $inc = 0;
        $matriceSelezionati[$inc]['IDX'] = "F@" . $anapro_orig['ROWID'];
        $matriceSelezionati[$inc]['TIPO'] = "FASCICOLO";
        $matriceSelezionati[$inc]['KEY'] = 'F-' . $anaorg_rec['ORGKEY'];
        $matriceSelezionati[$inc]['DESCRIZIONE'] = $anaorg_rec['ORGDES'];
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $level += 1;
        $padre = "F@" . $anapro_orig['ROWID'];
        $anapro_tab = ItaDB::DBSQLSelect($PROT_DB, "SELECT * FROM ANAPRO WHERE PROFASKEY = '$orgkey'", true);
        if ($anapro_tab) {
            foreach ($anapro_tab as $anapro_rec) {
                $anapro_rec['PROGRESSIVO'] = substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc]['IDX'] = $inc;
                if ($anapro_rec['PROPAR'] == "F") {
                    $anadoc_tab = $proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
                    $g = true;
                    $c = count($anadoc_tab);
                    $matriceSelezionati[$inc]['IDX'] = "A@" . $anapro_orig['ROWID'];
                    $matriceSelezionati[$inc]['TIPO'] = "ALLEGATI";
                    $matriceSelezionati[$inc]['KEY'] = 'Allegati generici';
                    $matriceSelezionati[$inc]['DESCRIZIONE'] = "Presenti: $c allegati";
                } else {
                    $g = false;
                    $matriceSelezionati[$inc]['IDX'] = "P@" . $anapro_rec['ROWID'];
                    $matriceSelezionati[$inc]['TIPO'] = "PROTOCOLLO";
                    $matriceSelezionati[$inc]['KEY'] = 'P - ' . $anapro_rec['PROPAR'] . "   " . substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
                    $matriceSelezionati[$inc]['DESCRIZIONE'] = "Protocollo del: {$anapro_rec['PRODAR']}";
                }


                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $padre;
                $matriceSelezionati[$inc]['isLeaf'] = 'true';
                $matriceSelezionati[$inc]['expanded'] = 'false';
                $matriceSelezionati[$inc]['loaded'] = 'false';
            }
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Fascicolo',
            "width" => '750',
            "height" => '400',
            "sortname" => "KEY",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'KEY',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "rowid",
                "COD.",
                "DESCRIZIONE"
            ),
            "colModel" => array(
                array("name" => 'IDX', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'KEY', "width" => 200),
                array("name" => 'DESCRIZIONE', "width" => 600)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnTreeFascicolo';
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proGetLegamiProt($proLib, $PROT_DB, $anapro_rec) {
        $pronum = $anapro_rec['PRONUM'];
        $pronum_radice = $anapro_rec['PROPRE'];
        $tipo_radice = $anapro_rec['PROPARPRE'];
        $tipo_orig = $anapro_rec['PROPAR'];
        $presenti = array();
        while ($pronum_radice != 0 && !array_key_exists($pronum_radice, $presenti)) {
            $presenti[$pronum_radice] = $pronum_radice;
            $anapro_ver = self::checkPRONUM($pronum_radice, $PROT_DB, $tipo_radice);
            if (!$anapro_ver) {
                break;
            }
            $anapro_rec = $anapro_ver;
            if ($anapro_rec['PROPRE'] != 0) {
                $pronum_radice = $anapro_rec['PROPRE'];
                $tipo_radice = $anapro_rec['PROPARPRE'];
            }
        }
        if ($pronum_radice == 0) {
            $pronum_radice = $pronum;
            $tipo_radice = $tipo_orig;
            $anapro_rec = self::checkPRONUM($pronum_radice, $PROT_DB, $tipo_radice);
        }
        $presentiB = array();
        $rowid = $anapro_rec['ROWID'];
        return self::CaricaGrigliaLegami($proLib, $pronum, $tipo_orig, $PROT_DB, $anapro_rec, $pronum_radice, $presentiB, $rowid);
    }

    static function proRicLegame($proLib, $returnModel, $returnEvent, $PROT_DB, $anapro_rec) {
        $matriceSelezionati = self::proGetLegamiProt($proLib, $PROT_DB, $anapro_rec);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Protocolli',
            "width" => '750',
            "height" => '400',
            "sortname" => "PRONUM",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'PROGRESSIVO',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "ID",
                "A/P",
                "Protocollo",
                "Oggetto",
                "Mitt/Dest",
                "Data Reg.",
                "Prot.Mitt."
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROPAR', "width" => 30),
                array("name" => 'PROGRESSIVO', "width" => 220),
                array("name" => 'OGGOGG', "width" => 300),
                array("name" => 'PRONOM', "width" => 290),
                array("name" => 'PRODAR', "formatter" => "eqdate", "width" => 80),
                array("name" => 'PRONPA', "width" => 70)
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

    protected static function CaricaGrigliaLegami($proLib, $pronum_selezionato, $propar_selezionato, $PROT_DB, $anapro_rec, $propre_attuale, $presenti, $rowid) {
        $inc = 1;
        if ($proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $anapro_rec['OGGOGG'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
            $anapro_rec['PRONOM'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
        }

        $anapro_rec['PROGRESSIVO'] = substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
        if ($anapro_rec['PRONUM'] == $pronum_selezionato && $anapro_rec['PROPAR'] == $propar_selezionato) {
            $anapro_rec['PROGRESSIVO'] = '<span style="color:red;">' . $anapro_rec['PROGRESSIVO'] . '</span>';
        }
        $matriceSelezionati[$inc] = $anapro_rec;
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $save_count = count($matriceSelezionati);
        if ($propre_attuale > 1000000000) {
            $matriceSelezionati = self::caricaTreeLegami($proLib, $pronum_selezionato, $propar_selezionato, $PROT_DB, $matriceSelezionati, $propre_attuale, $presenti, 1, $anapro_rec['PROPAR'], $rowid);
        }
        if ($save_count == count($matriceSelezionati)) {
            $matriceSelezionati[$inc]['isLeaf'] = 'true';
        }
        return $matriceSelezionati;
    }

    protected static function checkPROPRE($pronum, $PROT_DB, $tipo, $multi = false) {
        $sql = "SELECT ANAPRO.ROWID AS ROWID, ANAPRO.PROPAR AS PROPAR, ANAPRO.PRONOM AS PRONOM, ANAPRO.PRODAR AS PRODAR, ANAPRO.PRONPA AS PRONPA,
        ANAPRO.PRONUM AS PRONUM, ANAPRO.PROPRE, ANAPRO.PROPARPRE, ANAOGG.OGGOGG AS OGGOGG, ANAPRO.PRORISERVA AS PRORISERVA, ANAPRO.PROTSO AS PROTSO    
        FROM ANAPRO LEFT OUTER JOIN ANAOGG ON PRONUM=OGGNUM AND PROPAR=OGGPAR WHERE (PROPAR='A' OR PROPAR='P' OR PROPAR='C') 
        AND PROPRE BETWEEN $pronum AND $pronum AND PROPARPRE='" . $tipo . "'";
//        switch ($tipo) {
//            case 'C':
//            case 'AC':
//                $sql.=" AND PROPARPRE='$tipo'";
//                break;
//            default:
//                $sql.=" AND PROPARPRE<>'C' AND PROPARPRE<>'AC'";
//                break;
//        }
        $anapro_rec = ItaDB::DBSQLSelect($PROT_DB, $sql, $multi);
        return $anapro_rec;
    }

    protected static function checkPRONUM($pronum, $PROT_DB, $tipo, $multi = false) {
        $sql = "SELECT ANAPRO.ROWID AS ROWID, ANAPRO.PROPAR AS PROPAR, ANAPRO.PRONOM AS PRONOM, ANAPRO.PRODAR AS PRODAR, ANAPRO.PRONPA AS PRONPA,
        ANAPRO.PRONUM AS PRONUM, ANAPRO.PROPRE, ANAPRO.PROPARPRE, ANAOGG.OGGOGG AS OGGOGG, ANAPRO.PRORISERVA AS PRORISERVA, ANAPRO.PROTSO AS PROTSO
        FROM ANAPRO LEFT OUTER JOIN ANAOGG ON PRONUM=OGGNUM AND PROPAR=OGGPAR WHERE (PROPAR='A' OR PROPAR='P' OR PROPAR='C') 
        AND PRONUM BETWEEN $pronum AND $pronum AND PROPAR ='" . $tipo . "'";
//        switch ($tipo) {
//            case 'C':
//            case 'AC':
//                $sql.=" AND PROPAR='$tipo'";
//                break;
//            default:
//                $sql.=" AND (PROPAR='A' OR PROPAR='AA' OR PROPAR='P' OR PROPAR='AP')";
//                break;
//        }
        return ItaDB::DBSQLSelect($PROT_DB, $sql, $multi);
    }

    /**
     *  Carica ricorsivamente i vari nodi e foglie dell'albero
     *
     * @param type $pronum
     * @param type $PROT_DB   Database
     * @param type $matriceSelezionati
     * @param type $propre_attuale
     * @param array $presenti
     * @param type $level    Livello dell'albero in cui ci si trova
     * @param type $tipo
     * @param type $rowid
     * @return boolean
     */
    protected static function caricaTreeLegami($proLib, $pronum_selezionato, $propar_selezionato, $PROT_DB, $matriceSelezionati, $propre_attuale, $presenti, $level, $tipo, $rowid) {
        if (array_key_exists($propre_attuale, $presenti)) {
            return $matriceSelezionati;
        }
        $presenti[$propre_attuale] = $propre_attuale;
        $anapro_tab = self::checkPROPRE($propre_attuale, $PROT_DB, $tipo, true);
        if (count($anapro_tab) > 0) {
            for ($i = 0; $i < count($anapro_tab); $i++) {
                $pronum_appoggio = $anapro_tab[$i]['PRONUM'];
                $tipo = $anapro_tab[$i]['PROPAR'];
                if ($proLib->checkRiservatezzaProtocollo($anapro_tab[$i])) {
                    $anapro_tab[$i]['OGGOGG'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
                    $anapro_tab[$i]['PRONOM'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
                }
                $anapro_tab[$i]['PROGRESSIVO'] = substr($anapro_tab[$i]['PRONUM'], 4) . '/' . substr($anapro_tab[$i]['PRONUM'], 0, 4);
                if ($anapro_tab[$i]['PRONUM'] == $pronum_selezionato && $anapro_tab[$i]['PROPAR'] == $propar_selezionato) {
                    $anapro_tab[$i]['PROGRESSIVO'] = '<span style="color:red;">' . $anapro_tab[$i]['PROGRESSIVO'] . '</span>';
                }
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc] = $anapro_tab[$i];
                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $rowid;
                $matriceSelezionati[$inc]['isLeaf'] = 'false';
                $matriceSelezionati[$inc]['expanded'] = 'true';
                $matriceSelezionati[$inc]['loaded'] = 'true';
                $save_count = count($matriceSelezionati);
                $matriceSelezionati = self::caricaTreeLegami($proLib, $pronum_selezionato, $propar_selezionato, $PROT_DB, $matriceSelezionati, $pronum_appoggio, $presenti, $level + 1, $tipo, $anapro_tab[$i]['ROWID']);
                if ($save_count == count($matriceSelezionati)) {
                    $matriceSelezionati[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $matriceSelezionati;
    }

    static function regRicAnaatti($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANAATTI";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Natura Atti',
            "width" => '550',
            "height" => '400',
            "sortname" => 'ATTO',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Atto"
            ),
            "colModel" => array(
                array("name" => 'CODATTO', "width" => 100),
                array("name" => 'ATTO', "width" => 400)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODATTO', 'ATTO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanaatti' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function regRicAnadir($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANADIR";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Diritti',
            "width" => '550',
            "height" => '400',
            "sortname" => 'DIRITTI',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Diritto",
                "Importo"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'DIRITTI', "width" => 300),
                array("name" => 'IMPORTO', "width" => 100)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODATTO', 'DIRITTI', 'IMPORTO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanadir' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function regRicAnaspese($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANASPESE";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Spese',
            "width" => '550',
            "height" => '400',
            "sortname" => 'SPESE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Spese",
                "Importo"
            ),
            "colModel" => array(
                array("name" => 'CODSPE', "width" => 100),
                array("name" => 'SPESE', "width" => 300),
                array("name" => 'IMPORTO', "width" => 100)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODSPE', 'SPESE', 'IMPORTO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanaspese' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function regRicAnarice($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANARICE";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Riceventi',
            "width" => '550',
            "height" => '400',
            "sortname" => 'RICEVENTE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Ricevente",
                "Qualifica"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'RICEVENTE', "width" => 300),
                array("name" => 'QUALIFICA', "width" => 100)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'RICEVENTE', 'QUALIFICA');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanarice' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function regRicAnaqua($returnModel, $where = '', $aggiungi = '', $returnId = '', $returnEvent = 'returnanamed', $extraData = null) {
        $sql = "SELECT * FROM ANAQUA";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Qualifiche',
            "width" => '550',
            "height" => '400',
            "sortname" => 'QUALIFICA',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'QUALIFICA', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'QUALIFICA');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        $_POST['keynavButtonAdd'] = $aggiungi;
        $_POST['extraData'] = $extraData;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function regRicAnaesito($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM ANAESITO";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Esiti',
            "width" => '550',
            "height" => '400',
            "sortname" => 'ESITO',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'ESITO', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'ESITO');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnanaesito' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     * 
     * @param type $prolib
     * @param type $returnModel
     * @param type $returnEvent
     * funzione modificata per gestire multiselect invece di albero - Mario - 01.08.2013
     */
    static function proRicDestinatari($prolib, $returnModel, $returnEvent = 'returnDestinatari') {

        $anaent_58 = $prolib->GetAnaent('58');
        $order = 'UFFDES';
        $servizioHide = "true";
        $anaservizi_check = $prolib->getGenericTab("SELECT ROWID FROM ANASERVIZI", true);

        $groupBy = '';
        $FilterPref = '';
        if (!$anaent_58['ENTDE5']) {
            $groupBy = ' GROUP BY ANAMED.MEDCOD ';
            $FilterPref = ' AND UFFFI1__3 = 1 ';
        }

        if ($anaservizi_check) {
            $order = 'SERDES';
            $servizioHide = "false";
        }
        $order = 'MEDNOM ASC, UFFDES';
        $sql = " SELECT * FROM ( "
                . " SELECT 
            ANAUFF.ROWID AS ROWIDUFF,
            ANASERVIZI.ROWID AS ROWIDSER,
            ANAUFF.UFFDES AS UFFDES,
            ANAUFF.UFFCOD, 
            ANASERVIZI.SERDES AS SERDES,
            ANAMED.MEDNOM AS MEDNOM,
            ANAMED.ROWID AS ROWIDMED,
            ANAMED.MEDTAG AS MEDTAG,
            ANARUOLI.RUODES AS RUODES,
            " . $prolib->getPROTDB()->strConcat("'0-'", 'ANAUFF.ROWID', "'-'", 'ANAMED.ROWID', "'-0'") . " AS ROWID
            FROM ANAUFF
            LEFT OUTER JOIN ANASERVIZI ON ANAUFF.UFFSER = ANASERVIZI.SERCOD
            LEFT OUTER JOIN UFFDES ON UFFDES.UFFCOD = ANAUFF.UFFCOD
            LEFT OUTER JOIN ANAMED ON ANAMED.MEDCOD = UFFDES.UFFKEY
            LEFT OUTER JOIN ANARUOLI ON ANARUOLI.RUOCOD = UFFDES.UFFFI1__2
            WHERE ANAUFF.UFFANN<>1 AND ANAMED.MEDANN<>1 AND UFFDES.UFFCESVAL='' $FilterPref
            $groupBy
            ORDER BY UFFFI1__3 DESC) DESTINTERNI WHERE 1 
             ";

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Destinatari',
            "width" => '780',
            "height" => '350',
            "sortname" => $order,
            "sortorder" => 'asc',
            "rowNum" => '1000',
            "filterToolbar" => 'true',
            "multiselect" => 'true',
            "disableselectall" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Nominativo",
                "Tag",
                "Ufficio",
                "Ruolo",
                "Settore"
            ),
            "colModel" => array(
                array("name" => 'MEDNOM', "width" => 150),
                array("name" => 'MEDTAG', "width" => 100),
                array("name" => 'UFFDES', "width" => 150),
                array("name" => 'RUODES', "width" => 150),
                array("name" => 'SERDES', "width" => 150, "hidden" => $servizioHide),
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('SERDES', 'UFFDES', 'MEDNOM', 'RUODES', 'MEDTAG');
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

    static function proRicTitolarioFiltrato($returnModel, $prolib, $uffcod, $versione = '', $retId = '') {
        $ufftit_tab = $prolib->elaboraTitolarioUfftit($prolib->getUfftit($uffcod, 'uffcod', $versione));
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Titolario Abilitato",
            "width" => '650',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "arrayTable" => $ufftit_tab,
            "colNames" => array(
                "ID",
                "Titolario"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 110),
                array("name" => 'DESCRIZIONE', "width" => 500)
            )
        );
        $filterName = array('DESCRIZIONE');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnTitolarioFiltrato';
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicTitolario($PROT_DB, $prolib, $returnModel, $where = array(), $returnEvent = 'returnTitolario') {

        itaLib::openDialog('proTitolario');
        /* @var $proTitolario proTitolario */
        $proTitolario = itaModel::getInstance('proTitolario');
        $proTitolario->setEvent('openform');
        $proTitolario->setReturnEvent($returnEvent);
        $proTitolario->setReturnModel($returnModel);
        $proTitolario->setWhere($where);
        $proTitolario->parseEvent();
    }

    static function proRicAnagra($returnModel, $where = '', $returnId = '', $returnEvent = 'returnaAnagra') {
        $sql = "SELECT * FROM ANAGRA";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Cittadini',
            "width" => '830',
            "height" => '400',
            "sortname" => 'COGNOM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Cognome",
                "Nome",
                "Sesso",
                "Anno",
                "Mese",
                "Giorno",
            ),
            "colModel" => array(
                array("name" => 'CODTRI', "width" => 60),
                array("name" => 'COGNOM', "width" => 300),
                array("name" => 'NOME', "width" => 200),
                array("name" => 'SEX', "width" => 50),
                array("name" => 'AANAT', "width" => 50),
                array("name" => 'MMNAT', "width" => 50),
                array("name" => 'GGNAT', "width" => 50),
            ),
            "dataSource" => array(
                'sqlDB' => 'ANEL',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('COGNOM', 'NOME', 'SEX');
        //$filterName = array('CODTRI', 'COGNOM', 'NOME', 'SEX', 'AANAT', 'MMNAT', 'GGNAT');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicOggUtenti($returnModel, $where = '', $returnId = '', $returnEvent = 'returnaOggUtenti') {
        $sql = "SELECT OGGUTENTI.ROWID AS ROWID, OGGUTENTI.DOGCOD AS DOGCOD, ANADOG.DOGDEX
            FROM OGGUTENTI LEFT OUTER JOIN ANADOG ON OGGUTENTI.DOGCOD = ANADOG.DOGCOD";
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Seleziona un Oggetto',
            "width" => '630',
            "height" => '400',
            "sortname" => 'DOGDEX',
            "rowNum" => '15',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'DOGCOD', "width" => 60),
                array("name" => 'DOGDEX', "width" => 350)
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
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proAssegnaProtocolli($matriceSelezionati, $returnModel, $retid) {
        $colNames = array(
            "Mittente",
            "Data",
            "Oggetto",
            "Protocollo"
        );
        $colModel = array(
            array("name" => 'FROMADDR', "width" => 245),
            array("name" => 'DATAMESSAGGIO', "width" => 75),
            array("name" => 'SUBJECT', "width" => 400),
            array("name" => 'PROTOCOLLO', "width" => 80)
        );
        self::proMultiselectGeneric(
                $matriceSelezionati, $returnModel, $retid, 'Seleziona le Email da assegnare ai relativi Protocolli', $colNames, $colModel
        );
    }

    static function proMultiselectGeneric($matriceSelezionati, $returnModel, $retid, $caption, $colNames, $colModel, $msgDetail = '', $dim = array('width' => '800', 'height' => '400')) {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $caption,
            "width" => $dim['width'],
            "height" => $dim['height'],
            "multiselect" => 'true',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "filterToolbar" => 'true',
            "arrayTable" => $matriceSelezionati,
            "colNames" => $colNames,
            "colModel" => $colModel
        );
        $filterName = array();
        foreach ($colModel as $Colonna) {
            $filterName[] = $Colonna['name'];
        }

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = 'returnMultiselectGeneric';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnapra($returnModel, $titolo, $retid, $where = '', $msgDetail = '', $dbSuffix = '') {
        $sql = "SELECT SETDES, ATTDES, TIPDES, TSPDES, ANAPRA.ROWID, ANAPRA.PRADES__1, ANAPRA.PRANUM FROM ANAPRA ANAPRA
                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ANATSP ON ITEEVT.IEVTSP=ANATSP.TSPCOD
                LEFT OUTER JOIN ANATIP ANATIP ON ITEEVT.IEVTIP=ANATIP.TIPCOD 
                LEFT OUTER JOIN ANASET ANASET ON ITEEVT.IEVSTT=ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ANAATT ON ITEEVT.IEVATT=ANAATT.ATTCOD
               ";
//        $sql = "SELECT SETDES, ATTDES, TIPDES, TSPDES, ANAPRA.ROWID, ANAPRA.PRADES__1, ANAPRA.PRANUM FROM ANAPRA ANAPRA
//                LEFT OUTER JOIN ANASET ANASET ON ANAPRA.PRASTT=ANASET.SETCOD
//                LEFT OUTER JOIN ANAATT ANAATT ON ANAPRA.PRAATT=ANAATT.ATTCOD
//                LEFT OUTER JOIN ANATIP ANATIP ON ANAPRA.PRATIP=ANATIP.TIPCOD
//                LEFT OUTER JOIN ANATSP ANATSP ON ANAPRA.PRATSP=ANATSP.TSPCOD
//               ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
//        App::log($sql);
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione",
            "Settore",
            "Attività",
            "Tipologia"
        );
        $colonneModel = array(
            array("name" => 'PRANUM', "width" => 70),
            array("name" => 'PRADES__1', "width" => 400),
            array("name" => 'SETDES', "width" => 130),
            array("name" => 'ATTDES', "width" => 200),
            array("name" => 'TIPDES', "width" => 200)
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
            "Caption" => $titolo,
            "width" => '1040',
            "height" => '370',
            "sortname" => 'PRANUM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
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
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnaDoc($returnModel, $codice, $tipo, $tipoProt) {
        $proLib = new proLib();
        $Anadoc_tab = $proLib->GetAnadoc($codice, $tipo, true, $tipoProt);
        //Elabora records
        $Documenti_tab = array();
        foreach ($Anadoc_tab as $key => $Anadoc_rec) {
            $Documenti_tab[$Anadoc_rec['ROWID']]['EXT'] = '<span class="' . utiIcons::getExtensionIconClass($Anadoc_rec['DOCNAME'], 32) . '"></span>';
            $Documenti_tab[$Anadoc_rec['ROWID']]['DOCNAME'] = $Anadoc_rec['DOCNAME'];
            $Documenti_tab[$Anadoc_rec['ROWID']]['DOCNOT'] = $Anadoc_rec['DOCNOT'];
            $Documenti_tab[$Anadoc_rec['ROWID']]['ROWID'] = $Anadoc_rec['ROWID'];
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Documenti",
            "width" => '650',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'true',
            "rowNum" => '20000',
            "rowList" => '[]',
            "arrayTable" => $Documenti_tab,
            "colNames" => array(
                "",
                "Nome",
                "Note"
            ),
            "colModel" => array(
                array("name" => 'EXT', "width" => 50),
                array("name" => 'DOCNAME', "width" => 180),
                array("name" => 'DOCNOT', "width" => 400)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAnaDoc';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicIpa($returnModel, $returnEvent = 'returnIPA', $where = '') {
        $COMUNI_DB = itaDB::DBOpen('COMUNI', '');
        $sql = "SELECT * FROM AMMINISTRAZIONI " . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Indice delle pubbliche amministrazioni',
            "width" => '730',
            "height" => '400',
            "sortname" => 'DES_AMM',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Comune",
                "Prov."
            ),
            "colModel" => array(
                array("name" => 'COD_AMM', "width" => 60),
                array("name" => 'DES_AMM', "width" => 300),
                array("name" => 'COMUNE', "width" => 200),
                array("name" => 'PROVINCIA', "width" => 40)
            ),
            "dataSource" => array(
                'sqlDB' => 'COMUNI',
                'dbSuffix' => '',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('COD_AMM', 'DES_AMM', 'COMUNE', 'PROVINCIA');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        $_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnaTipoDoc($returnModel, $where = '', $returnEvent = 'returnAnaTipoDoc') {
        $sql = "SELECT * FROM ANATIPODOC ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Tipi Documento',
            "width" => '550',
            "height" => '400',
            "sortname" => 'CODICE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Ufficio",
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'DESCRIZIONE', "width" => 400),
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'DESCRIZIONE');
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

    /**
     * Dialog di ricerca dei componeti di un ufficio
     * 
     * @param type $returnModel     moodel di ritorno
     * @param type $codice          Codice responsabile
     * @param type $where           $where Aggiuntiva
     * @param type $returnId        id di ritorno
     * @param type $returnEvent     Evento di ritorno = "returnUfficiDestinatari" e si anngiunge la variabile $returnEvent
     */
    static function proRicDestinatariResponsabile($returnModel, $codice, $where = '', $returnId = '', $returnEvent = '', $delefunzione = 0) {
        $prolib = new proLib();
        $sql = $prolib->getSqlUnionDestinatariResponsabile($codice, false, $delefunzione);
        // Non è da usare così la where.
        if ($where != '')
            $sql = $sql . ' ' . $where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Destinatari',
            "width" => '450',
            "height" => '400',
            "sortname" => 'MEDNOM',
            "rowNum" => '1000',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Nominativo"
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 60),
                array("name" => 'MEDNOM', "width" => 330)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('MEDCOD', 'MEDNOM');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnDestinatariResponsabile' . $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicAnadocServizio($returnModel, $anapro_rec, $returnEvent = 'returnAnadocServizio', $where = '') {
        $sql = "SELECT * 
                    FROM ANADOC 
                WHERE DOCNUM = '{$anapro_rec['PRONUM']}' AND 
                DOCPAR = '{$anapro_rec['PROPAR']}' AND 
                DOCSERVIZIO <> 0 ";
        $sql .= $where;


        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Documenti di Servizio',
            "width" => '740',
            "height" => '300',
            "sortname" => 'ROWID DESC, DOCNAME',
            "rowNum" => '1000',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "pgbuttons" => 'false',
            "navButtonDel" => 'true',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Nome File",
                "Descrizione",
                "Data"
            ),
            "colModel" => array(
                array("name" => 'DOCNAME', "width" => 350),
                array("name" => 'DOCNOT', "width" => 300),
                array("name" => 'DOCFDT', "width" => 70, "formatter" => "eqdate")
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('DOCNAME', 'DOCNOT');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicProtoCollegato($returnModel, $Numero = '', $Anno = '', $returnEvent = 'returnRicProtoCollegato') {
        $Pronum1 = $Pronum2 = '';
        $Anno2 = $Anno;
        $Numero = str_pad($Numero, 6, '0', STR_PAD_LEFT);
        if ($Anno == '') {
            $Anno = date('Y');
            $Anno2 = $Anno - 1;
        }
        $Pronum1 = $Anno . $Numero;
        $Pronum2 = $Anno2 . $Numero;

        $prolib = new proLib();
        $PROTDB = $prolib->getPROTDB();
        $sql = "SELECT ANAPRO.*,ANAOGG.OGGOGG FROM ANAPRO ";
        $sql .= "LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR ";
        $sql .= " WHERE (PRONUM = '$Pronum1' OR PRONUM= '$Pronum2' ) AND 
            (   
                PROPAR ='C' OR PROPAR ='A' OR PROPAR ='P'
            ) ORDER BY PRONUM DESC";

        $Anapro_tab = ItaDB::DBSQLSelect($PROTDB, $sql, true);
        foreach ($Anapro_tab as $k => $Anapro_rec) {
            $CheckAnapro_rec = proSoggetto::getSecureAnaproFromIdUtente($prolib, 'codice', $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            if (!$CheckAnapro_rec) {
                unset($Anapro_tab[$k]);
                continue;
            }
            $Anapro_tab[$k]['ANNO'] = substr($Anapro_rec['PRONUM'], 0, 4);
            $Anapro_tab[$k]['NUMERO'] = substr($Anapro_rec['PRONUM'], 4);
            $Anapro_tab[$k]['TIPO'] = $Anapro_rec['PROPAR'];
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Protocolli',
            "width" => '410',
            "height" => '120',
            "sortname" => 'PRONUM',
            "sortorder" => "asc",
            "rowNum" => '2000',
            "rowList" => '[]',
            "arrayTable" => $Anapro_tab,
            "colNames" => array(
                "Numero",
                "Anno",
                "Tipo",
                "Oggetto"
            ),
            "colModel" => array(
                array("name" => 'NUMERO', "width" => 60),
                array("name" => 'ANNO', "width" => 40),
                array("name" => 'TIPO', "width" => 40),
                array("name" => 'OGGOGG', "width" => 240),
            )
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

    static function proSelectAllegati($matriceSelezionati, $returnModel, $returnEvent = 'returnMultiselectAllegati') {
        $colNames = array(
            "Nome File",
            "Descrizione",
            "Tipo",
            "Data",
            "Protocollo",
            "Tipo",
        );
        $colModel = array(
            array("name" => 'DOCNAME', "width" => 265),
            array("name" => 'FILEINFO', "width" => 265),
            array("name" => 'DOCTIPO', "width" => 100),
            array("name" => 'DOCFDT', "formatter" => "eqdate", "width" => 80),
            array("name" => 'PRONUM', "width" => 90),
            array("name" => 'PROPAR', "width" => 40)
        );
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Seleziona gli allegati da copiare",
            "width" => '880',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'true',
            "multiselectReturnRowData" => 'true',
            "rowNum" => '20000',
            "rowList" => '[]',
            "arrayTable" => $matriceSelezionati,
            "colNames" => $colNames,
            "colModel" => $colModel
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicFirmatario($returnModel, $where = '', $aggiungi = '', $returnId = '', $returnEvent = 'returnanamed', $groupby = '', $infoDetail = "") {
        $prolib = new proLib();
        $whereSql = "LEFT OUTER JOIN UFFDES ON ANAMED.MEDCOD=UFFDES.UFFKEY
                        LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD
                        WHERE MEDUFF " . $prolib->getPROTDB()->isNotBlank();
        $whereSql .= $where;
        $groupbySql = " GROUP BY ANAMED.MEDCOD " . $groupby;
        self::proRicAnamed($returnModel, $whereSql, $aggiungi, $returnId, $returnEvent, $groupbySql, $infoDetail);
    }

    static function proRicSerieArc($returnModel, $where = '', $returnEvent = 'returnSerieArc') {
        $sql = "SELECT * FROM ANASERIEARC ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Serie',
            "width" => '420',
            "height" => '200',
            "sortname" => 'CODICE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Sigla"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 80),
                array("name" => 'DESCRIZIONE', "width" => 200),
                array("name" => 'SIGLA', "width" => 130),
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('CODICE', 'DESCRIZIONE', 'SIGLA');
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

    static function proRicAnaRegistriArc($returnModel, $where = '', $returnEvent = 'returnAnaregistriArc') {
        $sql = "SELECT * FROM ANAREGISTRIARC ";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Registri',
            "width" => '420',
            "height" => '200',
            "sortname" => 'SIGLA',
            "rowNum" => '20',
            "readerId"=>'ROW_ID',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Sigla",
                "Descrizione"
            ),
            "colModel" => array(
                array("name" => 'SIGLA', "width" => 130),
                array("name" => 'DESCRIZIONE', "width" => 200)
            ),
            "dataSource" => array(
                'sqlDB' => 'PROT',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('SIGLA', 'DESCRIZIONE');
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

    static function proRicSeriePerTitolario($returnModel, $Titolario, $Versione_T, $returnEvent = 'returnSerieTitolario') {

        $proLibSerie = new proLibSerie();
        $appoggio_tab = $proLibSerie->GetElencoConnSerie($Titolario, $Versione_T);

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Serie per Titolario',
            "width" => '450',
            "height" => '200',
            "sortname" => 'CODICE',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice",
                "Descrizione",
                "Sigla"
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 80),
                array("name" => 'DESCRIZIONE', "width" => 220),
                array("name" => 'SIGLA', "width" => 130),
            ),
            "arrayTable" => $appoggio_tab
        );

        $filterName = array('CODICE', 'DESCRIZIONE', 'SIGLA');
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

    /* Tree Legami Fascicoli */

    static function proRicLegameFascicoli($proLib, $returnModel, $returnEvent, $PROT_DB, $anaorg_rec) {

        $orgkey = $anaorg_rec['ORGKEY'];
        $orgkey_radice = $anaorg_rec['ORGKEYPRE'];
        $presenti = array();

        while ($orgkey_radice != 0 && !array_key_exists($orgkey_radice, $presenti)) {
            $presenti[$orgkey_radice] = $orgkey_radice;
            $anapro_ver = self::checkANAORG($orgkey_radice, $PROT_DB);
            if (!$anapro_ver) {
                break;
            }
            $anapro_rec = $anapro_ver;
            if ($anapro_rec['ORGKEYPRE'] != 0) {
                $orgkey_radice = $anapro_rec['ORGKEYPRE'];
            }
        }
        if (!$orgkey_radice) {
            $orgkey_radice = $orgkey;
            $anapro_rec = self::checkANAORG($orgkey_radice, $PROT_DB);
        }
        $presentiB = array();
        $rowid = $anapro_rec['ROWID'];
        $matriceSelezionati = self::CaricaGrigliaLegamiFascicoli($proLib, $orgkey, $PROT_DB, $anapro_rec, $orgkey_radice, $presentiB, $rowid);
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Fascicoli Collegati',
            "width" => '750',
            "height" => '400',
            "sortname" => "PROGRESSIVO",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'PROGRESSIVO',
            "arrayTable" => $matriceSelezionati,
            "colNames" => array(
                "ID",
                "F",
                "Fascicolo",
                "Oggetto",
                "Responsabie",
                "Data Fasc."
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PROPAR', "width" => 30),
                array("name" => 'PROGRESSIVO', "width" => 220),
                array("name" => 'OGGOGG', "width" => 250),
                array("name" => 'RESPONSABILE', "width" => 150),
                array("name" => 'GESDRE', "formatter" => "eqdate", "width" => 80)
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

    protected static function CaricaGrigliaLegamiFascicoli($proLib, $orgkey_selezionato, $PROT_DB, $anapro_rec, $orgkey_attuale, $presenti, $rowid) {
        $inc = 1;
        if ($proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $anapro_rec['OGGOGG'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
            $anapro_rec['PRONOM'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
        }

        $anapro_rec['PROGRESSIVO'] = $anapro_rec['ORGKEY'];
        if ($anapro_rec['ORGKEY'] == $orgkey_selezionato) {
            $anapro_rec['PROGRESSIVO'] = '<span style="color:red;">' . $anapro_rec['ORGKEY'] . '</span>';
        }
        $matriceSelezionati[$inc] = $anapro_rec;
        $matriceSelezionati[$inc]['level'] = 0;
        $matriceSelezionati[$inc]['parent'] = NULL;
        $matriceSelezionati[$inc]['isLeaf'] = 'false';
        $matriceSelezionati[$inc]['expanded'] = 'true';
        $matriceSelezionati[$inc]['loaded'] = 'true';
        $save_count = count($matriceSelezionati);
        if ($orgkey_attuale) {
            $matriceSelezionati = self::caricaTreeLegamiFascicoli($proLib, $orgkey_selezionato, $PROT_DB, $matriceSelezionati, $orgkey_attuale, $presenti, 1, $rowid);
        }
        if ($save_count == count($matriceSelezionati)) {
            $matriceSelezionati[$inc]['isLeaf'] = 'true';
        }
        return $matriceSelezionati;
    }

    protected static function caricaTreeLegamiFascicoli($proLib, $orgkey_selezionato, $PROT_DB, $matriceSelezionati, $orgkey_attuale, $presenti, $level, $rowid) {
        if (array_key_exists($orgkey_attuale, $presenti)) {
            return $matriceSelezionati;
        }
        $presenti[$orgkey_attuale] = $orgkey_attuale;
        $anapro_tab = self::checkFASPRE($orgkey_attuale, $PROT_DB, true);
        if (count($anapro_tab) > 0) {
            for ($i = 0; $i < count($anapro_tab); $i++) {
                $orgkey_appoggio = $anapro_tab[$i]['ORGKEY'];
                if ($proLib->checkRiservatezzaProtocollo($anapro_tab[$i])) {
                    $anapro_tab[$i]['OGGOGG'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
                    $anapro_tab[$i]['PRONOM'] = "<p style=\"color:white;background:gray;\">RISERVATO</p>";
                }
                $anapro_tab[$i]['PROGRESSIVO'] = $anapro_tab[$i]['ORGKEY'];
                if ($anapro_tab[$i]['ORGKEY'] == $orgkey_selezionato) {
                    $anapro_tab[$i]['PROGRESSIVO'] = '<span style="color:red;">' . $anapro_tab[$i]['PROGRESSIVO'] . '</span>';
                }
                $inc = count($matriceSelezionati) + 1;
                $matriceSelezionati[$inc] = $anapro_tab[$i];
                $matriceSelezionati[$inc]['level'] = $level;
                $matriceSelezionati[$inc]['parent'] = $rowid;
                $matriceSelezionati[$inc]['isLeaf'] = 'false';
                $matriceSelezionati[$inc]['expanded'] = 'true';
                $matriceSelezionati[$inc]['loaded'] = 'true';
                $save_count = count($matriceSelezionati);
                $matriceSelezionati = self::caricaTreeLegamiFascicoli($proLib, $orgkey_selezionato, $PROT_DB, $matriceSelezionati, $orgkey_appoggio, $presenti, $level + 1, $anapro_tab[$i]['ROWID']);
                if ($save_count == count($matriceSelezionati)) {
                    $matriceSelezionati[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $matriceSelezionati;
    }

    protected static function checkFASPRE($orgkey, $PROT_DB, $multi = false) {
        $sql = "SELECT ANAPRO.ROWID AS ROWID, ANAPRO.PROPAR AS PROPAR, ANAPRO.PRONOM AS PRONOM, ANAPRO.PRODAR AS PRODAR, ANAPRO.PRONPA AS PRONPA,
        ANAPRO.PRONUM AS PRONUM, ANAPRO.PROPRE, ANAPRO.PROPARPRE, ANAOGG.OGGOGG AS OGGOGG, ANAPRO.PRORISERVA AS PRORISERVA, ANAPRO.PROTSO AS PROTSO,
        ANAORG.ORGKEY AS ORGKEY, ANAORG.ORGKEYPRE AS ORGKEYPRE, PROGES.GESDRE AS GESDRE, PROGES.GESRES AS GESRES, ANAMED.MEDNOM AS RESPONSABILE 
        FROM ANAORG 
        LEFT OUTER JOIN ANAPRO ON  ANAORG.ORGKEY=ANAPRO.PROFASKEY AND ANAPRO.PROPAR = 'F'
        LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY=PROGES.GESKEY
        LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
        LEFT OUTER JOIN ANAOGG ON PRONUM=OGGNUM AND PROPAR=OGGPAR WHERE PROPAR='F' 
        AND ANAORG.ORGKEYPRE = '$orgkey' ";
        $anapro_rec = ItaDB::DBSQLSelect($PROT_DB, $sql, $multi);
        return $anapro_rec;
    }

    protected static function checkANAORG($orgkey, $PROT_DB, $multi = false) {
        $sql = "SELECT ANAPRO.ROWID AS ROWID, ANAPRO.PROPAR AS PROPAR, ANAPRO.PRONOM AS PRONOM, ANAPRO.PRODAR AS PRODAR, ANAPRO.PRONPA AS PRONPA,
        ANAPRO.PRONUM AS PRONUM, ANAPRO.PROPRE, ANAPRO.PROPARPRE, ANAOGG.OGGOGG AS OGGOGG, ANAPRO.PRORISERVA AS PRORISERVA, ANAPRO.PROTSO AS PROTSO,
        ANAORG.ORGKEY AS ORGKEY, ANAORG.ORGKEYPRE AS ORGKEYPRE, PROGES.GESDRE AS GESDRE, PROGES.GESRES AS GESRES,ANAMED.MEDNOM AS RESPONSABILE 
        FROM ANAORG 
        LEFT OUTER JOIN ANAPRO ON ANAORG.ORGKEY=ANAPRO.PROFASKEY AND ANAPRO.PROPAR = 'F'
        LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY=PROGES.GESKEY
        LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
        LEFT OUTER JOIN ANAOGG ON PRONUM=OGGNUM AND PROPAR=OGGPAR WHERE PROPAR='F' 
        AND ANAORG.ORGKEY = '$orgkey' ";
        return ItaDB::DBSQLSelect($PROT_DB, $sql, $multi);
    }

    static function proRicEFASCollegati($returnModel, $Anapro_rec = array(), $returnEvent = 'returnEFAS') {

        $prolib = new proLib();
        $AnaproCollegati_tab = $prolib->CaricaElencoEFASCollegati($Anapro_rec);
        foreach ($AnaproCollegati_tab as $key => $AnaproCollegato) {
            $AnaproCollegati_tab[$key]['NUMERO'] = substr($AnaproCollegato['PRONUM'], 4) . '/' . substr($AnaproCollegato['PRONUM'], 0, 4);
            // Controllo se riscontrata?
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Fatture spacchettate',
            "width" => '720',
            "height" => '400',
            "sortname" => 'TSPDES',
            "rowNum" => '20000',
            "filterToolbar" => 'true',
            "arrayTable" => $AnaproCollegati_tab,
            "rowList" => '[]',
            "colNames" => array(
                "A/P",
                "Protocollo",
                "Oggetto",
                "Data",
                "Riscontro",
            ),
            "colModel" => array(
                array("name" => 'PROPAR', "width" => 30),
                array("name" => 'NUMERO', "width" => 110),
                array("name" => 'OGGOGG', "width" => 380),
                array("name" => 'PRODAR', "formatter" => "eqdate", "width" => 90),
                array("name" => 'INFORISCONTRO', "width" => 90)
            ),
        );
        $filterName = array('NUMERO', 'OGGOGG');
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

    static function proRicSoggettiConRuolo($returnModel, $ElencoRuoli = array()) {
        $proLib = new proLib();
        $MedRuoli_tab = $proLib->GetRuoliSoggetto('', $ElencoRuoli);

        // Aggiunta messaggio per elencare i ruoli?
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Soggetti ",
            "width" => '650',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'true',
            "rowNum" => '20000',
            "rowList" => '[]',
            "rowList" => '[]',
            "readerId" => 'ID',
            "arrayTable" => $MedRuoli_tab,
            "colNames" => array(
                "Codice",
                "Soggetto",
                "Ruolo",
                "Descrizione Ruolo"
            ),
            "colModel" => array(
                array("name" => 'MEDCOD', "width" => 80),
                array("name" => 'MEDNOM', "width" => 260),
                array("name" => 'RUOCOD', "width" => 80),
                array("name" => 'RUODES', "width" => 180)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnRuoliSoggetti';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicNotificheMail($returnModel, $idMailPadre = '') {
        include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $mailPadreArchivio_rec = $emlLib->getMailArchivio($idMailPadre, 'id');
        $Dest = $mailPadreArchivio_rec['TOADDR'];

        $mailArchivio_tab = $emlLib->getMailArchivio($idMailPadre, 'idmailpadre');
        foreach ($mailArchivio_tab as $key => $mailArchivio_rec) {
            $mailArchivio_tab[$key]['DATA'] = date("d/m/Y", strtotime(substr($mailArchivio_rec['MSGDATE'], 0, 8)));
            $mailArchivio_tab[$key]['ORA'] = date("H:i:s", strtotime(substr($mailArchivio_rec['MSGDATE'], 9)));
            if ($mailArchivio_rec['PECTIPO'] == emlMessage::PEC_TIPO_ACCETTAZIONE || $mailArchivio_rec['PECTIPO'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                unset($mailArchivio_tab[$key]);
            }
        }

        // Aggiunta messaggio per elencare i ruoli?
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Notifiche PEC",
            "width" => '750',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "rowList" => '[]',
            "readerId" => 'ROWID',
            "arrayTable" => $mailArchivio_tab,
            "colNames" => array(
                "Oggetto",
                "Data",
                "Ora",
                "Tipo PEC"
            ),
            "colModel" => array(
                array("name" => 'SUBJECT', "width" => 440),
                array("name" => 'DATA', "width" => 80),
                array("name" => 'ORA', "width" => 80),
                array("name" => 'PECTIPO', "width" => 120)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnNotifichePec';
        $_POST['msgDetail'] = 'Destinatario: ' . $Dest;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proMittDestProtocollo($returnModel, $pronum = '', $propar = '') {
        $proLib = new proLib();
        $Firmatari = array();
        $destinatari = array();
        $Mittenti = array();
        $MittDest = array();
        switch ($propar) {
            case 'A':
                $destipo = 'D';
                $DescElenco = 'Mittenti';
                $Mittenti = $proLib->getPromitagg($pronum, $propar, true);
                break;
            case 'P':
                $DescElenco = 'Destinatari';
                $destinatari = $proLib->GetAnades($pronum, 'codice', true, $propar, 'D');
                $Firmatari = $proLib->GetAnades($pronum, 'codice', true, $propar, 'M');
                break;
            case 'C':
                $destipo = 'T';
                $DescElenco = 'Firmatari';
                $Firmatari = $proLib->GetAnades($pronum, 'codice', true, $propar, 'M');
                break;
        }
        foreach ($destinatari as $destinatario) {
            $destinatario['PROVENIENZA'] = $destinatario['DESNOM'];
            $destinatario['MAIL'] = $destinatario['DESMAIL'];
            $destinatario['TIPO'] = 'DESTINATARIO';
            $MittDest[] = $destinatario;
        }
        foreach ($Firmatari as $firmatario_rec) {
            $firmatario_rec['PROVENIENZA'] = $firmatario_rec['DESNOM'];
            $firmatario_rec['MAIL'] = $firmatario_rec['DESMAIL'];
            $firmatario_rec['TIPO'] = 'FIRMATARIO';
            $MittDest[] = $firmatario_rec;
        }
        foreach ($Mittenti as $Mittente_rec) {
            $firmatario_rec['PROVENIENZA'] = $firmatario_rec['DESNOM'];
            $firmatario_rec['MAIL'] = $firmatario_rec['DESMAIL'];
            $firmatario_rec['TIPO'] = 'MITTENTE';
            $MittDest[] = $firmatario_rec;
        }
        if (!$MittDest) {
            return false;
        }

        // Aggiunta messaggio per elencare i ruoli?
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco $DescElenco ",
            "width" => '680',
            "height" => '400',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "rowList" => '[]',
            "readerId" => 'ROWID',
            "arrayTable" => $MittDest,
            "colNames" => array(
                "Soggetto",
                "Mail/Pec",
                "Tipo"
            ),
            "colModel" => array(
                array("name" => 'PROVENIENZA', "width" => 300),
                array("name" => 'MAIL', "width" => 210),
                array("name" => 'TIPO', "width" => 100)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnElencoMittDest';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        return true;
    }

    static function apriRicercaAnagrafeSoggettiUnici($progsogg = '', $campo = '', $return, $nameForm, $nameFormOrig = null) {
        $apro_lista = false;
        $postData = array(
            'soggOrigin_CLIFOR' => true // Per la sola Finanziaria
        );
        $externalFilter = array();
        $externalFilter['QUALIVEDO'] = array();
        $externalFilter['QUALIVEDO']['PERMANENTE'] = false;
        $externalFilter['QUALIVEDO']['VALORE'] = 4;
        if (!empty($progsogg)) {
            $externalFilter['PROGSOGG'] = array();
            $externalFilter['PROGSOGG']['PERMANENTE'] = false;
            $externalFilter['PROGSOGG']['VALORE'] = $progsogg;
            $apro_lista = true;
        }
        include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
        cwbLib::apriFinestraRicerca('cwbBtaSogg', $nameForm, $return, '', $apro_lista, $externalFilter, $nameFormOrig, '', $postData);
    }

    static function proRicElencoMail($returnModel, $retid = '') {
        $proLib = new proLib();
        $ElencoMail = $proLib->GetElencoMailProtocollo();

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elementi Disponibili',
            "width" => '450',
            "height" => '400',
            "sortname" => 'ORGNODEICO',
            "sortorder" => "desc",
            "rowNum" => '2000',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'MAILADDR',
            "arrayTable" => $ElencoMail,
            "colNames" => array(
                "Mail"
            ),
            "colModel" => array(
                array("name" => 'MAILADDR', "width" => 440)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAccountMail';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $infoDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicDelegheAttive($returnModel, $delegheAttive = array(), $codiceDest = '') {
        if (!$delegheAttive && $codiceDest) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php';
            $proLibDeleghe = new proLibDeleghe();
            $delegheAttive = $proLibDeleghe->getDelegheAttive($codiceDest, array(), proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);
        }
        $proLib = new proLib();

        foreach ($delegheAttive as $key => $Delega) {
            $Anamed_rec = $proLib->GetAnamed($Delega['DELESRCCOD']);
            $Anauff_rec = $proLib->GetAnauff($Delega['DELESRCUFF']);
            $delegheAttive[$key]['DELEGANTE'] = $Anamed_rec['MEDNOM'];
            $delegheAttive[$key]['UFFICIODELEGANTE'] = $Anauff_rec['UFFDES'];
        }


        // Aggiunta messaggio per elencare i ruoli?
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Elenco Deleghe Attive",
            "width" => '450',
            "height" => '300',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "multiselect" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "rowList" => '[]',
            "readerId" => 'ROWID',
            "arrayTable" => $delegheAttive,
            "colNames" => array(
                "Delegante",
                "Ufficio Delegante",
                "Data Inizio",
                "Data Fine"
            ),
            "colModel" => array(
                array("name" => 'DELEGANTE', "width" => 120),
                array("name" => 'UFFICIODELEGANTE', "width" => 120),
                array("name" => 'DELEINIVAL', "width" => 80),
                array("name" => 'DELEFINVAL', "width" => 80)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnDelegheAttive';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>