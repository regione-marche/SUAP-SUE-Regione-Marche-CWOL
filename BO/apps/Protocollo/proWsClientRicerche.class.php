<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';

/**
 *
 * HELPER PER GESTIONE CHIAMATE A PROTOCOLLI DI TERZE PARTI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Protocollo
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    13.03.2018
 * @link
 * @see
 * @since
 * */
class proWsClientRicerche {

    static function lanciaRicercaFascicoliWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstanceRicerche($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver Ricerche";
            $ret["RetValue"] = false;
            return $ret;
        }

        return $proObject->GetElencoFascicoli($elementi);
    }

    static function lanciaRicercaTitolarioWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstanceRicerche($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver Ricerche";
            $ret["RetValue"] = false;
            return $ret;
        }

        return $proObject->GetElencoTitolario($elementi);
    }

    static function lanciaRicercaOperatoriWS($elementi) {
        /*
         * Inizializzo il driver
         */
        $proObject = proWsClientFactory::getInstanceRicerche($elementi['TipoProtocollo']);
        if (!$proObject) {
            $ret["Status"] = "-1";
            $ret["Message"] = "Errore inizializzazione driver Ricerche";
            $ret["RetValue"] = false;
            return $ret;
        }

        return $proObject->GetElencoOperatori($elementi);
    }

    static function ricercaFascicoliWS($arrayFascicoli, $returnModel, $returnId = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Ricerca Fascicoli',
            "width" => '750',
            "height" => '470',
            "sortname" => 'ID',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $arrayFascicoli,
            "colNames" => array(
                "Id",
                "Numero",
                "Anno",
                "Data",
                "Oggetto",
                "Classifica",
            ),
            "colModel" => array(
                array("name" => 'ID', "width" => 50),
                array("name" => 'NUMERO', "width" => 80),
                array("name" => 'ANNO', "width" => 50),
                array("name" => 'DATA', "width" => 80),
                array("name" => 'OGGETTO', "width" => 250),
                array("name" => 'CLASSIFICA', "width" => 80),
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnClosePortlet'] = true;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnRicercaFascicoliWs';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricercaTitolariWS($arrayTitolari, $returnModel, $returnId = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Ricerca Titolari',
            "width" => '450',
            "height" => '370',
            "sortname" => 'ID',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $arrayTitolari,
            "colNames" => array(
                "Codice",
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 70),
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnClosePortlet'] = true;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnRicercaTitolarioWs';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    static function ricercaOpearatoriWS($arrayOperatori, $returnModel, $returnId = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Ricerca Operatori',
            "width" => '450',
            "height" => '370',
            "sortname" => 'ID',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $arrayOperatori,
            "colNames" => array(
                "Codice",
                "Descrizione",
            ),
            "colModel" => array(
                array("name" => 'CODICE', "width" => 100),
                array("name" => 'DESCRIZIONE', "width" => 300),
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['returnClosePortlet'] = true;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnRicercaOperatoriWs';
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>