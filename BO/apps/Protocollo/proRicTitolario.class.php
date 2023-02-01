<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    06.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';

class proRicTitolario {

    /**
     * Ricerca su Archivio Categorie
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    static function proRicVersioni($returnModel, $where = '', $returnEvent = '') {
        $sql = "SELECT * FROM AACVERS WHERE DATAFINE = '' ";
        $sql.=$where;
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco Versioni',
            "width" => '550',
            "height" => '400',
            "sortname" => 'DATAINIZ',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "readerId" => 'VERSIONE_T',
            "colNames" => array(
                "Versione",
                "Descrizione",
                "Data Inizio"
            ),
            "colModel" => array(
                array("name" => 'VERSIONE_T', "width" => 100),
                array("name" => 'DESCRI', "width" => 300),
                array("name" => 'DATAINIZ', "width" => 100, "formatter" => "eqdate")
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
        $_POST['returnEvent'] = 'returnRicVersione' . $returnEvent;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function proRicTitolarioVersione($PROT_DB, $prolib, $returnModel, $versione_t, $where = array(), $returnEvent = 'returnTitolario', $visLivello = '3', $scegliversione = false) {
        itaLib::openDialog('proTitolario');
        /* @var $proTitolario proTitolario */
        $proTitolario = itaModel::getInstance('proTitolario');
        $proTitolario->setEvent('openform');
        $proTitolario->setReturnEvent($returnEvent);
        $proTitolario->setReturnModel($returnModel);
        $proTitolario->setWhere($where);
        $proTitolario->setVersione($versione_t);
        $proTitolario->setVisLivello($visLivello);
        if($scegliversione === true){
            $proTitolario->setScegliVersione('1');
        }
        $proTitolario->parseEvent();
    }

}

?>