<?php

/* * 
 *
 * Utiity ricerche Dati Archivio Selec
 *
 * PHP Version 5
 *
 * @category
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    03.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLibSelec.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';

class praRicSelec {
    
    public function praRicSelecAttivita($returnModel, $where = '', $returnEvent = 'returnCercaAttivita', $retid = '') {
        $sql = " SELECT SFATTIVITA.ID AS ROWID, SFATTIVITA.IDOPERATORI, SFATTIVITA.IDVIE, " .
            " SFOPERATORI.RAGSOC, VIE.DESCR, SFATTIVITA.NUMCIV1, " .
            " SFATTIVITA.NUMCIV2, SFATTIVITA.SUPCOMM, SFATTIVITA.IDTIPORIC, " .
            " SFATTIVITA.D_VARIAZIONE, SFATTIVITA.IDLETIPOATTIVITA, " .
            " SFATTIVITA.TIPOCATASTO, SFATTIVITA.CATASTO,  TIPORIC.DESCVARIAZIONE, ".
            " SFLEGESEMP.DESCR AS TIPOATTIVITA, SFLEGESEMP_1.DESCR AS SETTORE, " .
            " SFLEGESEMP_2.DESCR AS DESCR_USO, LEGEGENE.DESCR AS DESCR_VIA " .
            " FROM  SFATTIVITA " .
            " LEFT JOIN SFOPERATORI ON SFATTIVITA.IDOPERATORI = SFOPERATORI.ID ".
            " LEFT JOIN VIE ON SFATTIVITA.IDVIE = VIE.ID " .
            " LEFT JOIN LEGEGENE ON VIE.IDLGTIPOVIE = LEGEGENE.ID " .
            " LEFT JOIN TIPORIC ON SFATTIVITA.IDTIPORIC = TIPORIC.ID " .
            " LEFT JOIN SFLEGESEMP ON SFATTIVITA.IDLETIPUL = SFLEGESEMP.ID " .
            " LEFT JOIN SFLEGESEMP SFLEGESEMP_1 ON SFATTIVITA.IDLESETTORE = SFLEGESEMP_1.ID " .
            " LEFT JOIN SFLEGESEMP SFLEGESEMP_2 ON SFATTIVITA.IDLEDESTUSO = SFLEGESEMP_2.ID " ;
            //" WHERE DENOMINAZIONE LIKE UPPER('%" . $ricerca . "%')";                    
        
        
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Help Operatori ed Attività ',
            "width" => '550',
            "height" => '470',
            "sortname" => 'RAGSOC',
            "rowNum" => '10000000',
            "rowList" => '[]',
            "navGrid" => 'false',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "readerId" => 'ROWID',
            "colNames" => array(
                "Codice",
                "Ragione Sociale",
                "Tipo Via",
                "Indirizzo Attività",
                "N°Civ.",
                "Est. Civ.",
                "Sup. Comm.",
                "Tipo Attività",
                "Settore",
                "Ultima Variazione",
                "Data Variazione"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 30),
                array("name" => 'RAGSOC', "width" => 200),
                array("name" => 'DESCR_VIA', "width" => 50),
                array("name" => 'DESCR', "width" => 150),
                array("name" => 'NUMCIV1', "width" => 50),
                array("name" => 'NUMCIV2', "width" => 30),
                array("name" => 'SUPCOMM', "width" => 50),
                array("name" => 'TIPOATTIVITA', "width" => 100),
                array("name" => 'SETTORE', "width" => 100),
                array("name" => 'DESCVARIAZIONE', "width" => 100),
                array("name" => 'D_VARIAZIONE', "formatter" => "eqdate", "width" => 80)
            ),
            "dataSource" => array(
                'sqlDB' => 'SELEC',
                'sqlQuery' => $sql
            ),
            "filterToolbar" => 'true'
        );
        $filterName = array('ROWID', 'RAGSOC', 'DESCR_VIA', 'DESCR', 'NUMCIV1', 'NUMCIV2',
            'SUPCOMM', 'TIPOATTIVITA', 'SETTORE', 'DESCVARIAZIONE', 'D_VARIAZIONE');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
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
    
    
    
}
