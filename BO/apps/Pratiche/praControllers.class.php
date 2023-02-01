<?php

/**
 *
 * CLASSE PER PROCEDURRE DI CONTROLLO
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.09.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
class praControllers {

    private static function getControllersConfig() {
        $filename = $appRoute = App::getPath('appRoute.pra') . '/controllers.ini';
        return parse_ini_file($filename, true);
    }

    public static function getController($codice) {
        $config = self::getControllersConfig();
        if ($config) {
            return $config[$codice];
        } else {
            return false;
        }
    }

    public static function getControllerInstance($codice) {
        $config = self::getController($codice);
        $phpURL = App::getConf('modelBackEnd.php');
        $appRoute = App::getPath('appRoute.' . substr($config['class'], 0, 3));
        App::log($phpURL . '/' . $appRoute . '/' . $config['class'] . '.class.php');
        include_once $phpURL . '/' . $appRoute . '/' . $config['class'] . '.class.php';
        $controller = new $config['class']($config);
        return $controller;
    }

    public static function ricControllers($returnModel, $titolo, $retid) {
        $filename = $appRoute = App::getPath('appRoute.pra') . '/controllers.ini';
        $iniArr = self::getControllersConfig();
        $_appoggio = array();
        $i = 0;
        foreach ($iniArr as $key => $value) {
            $_appoggio[$i]['CODICE'] = $key;
            $_appoggio[$i]['DESCRIZIONE'] = $value['description'];
            $i+=1;
        }

        $model = 'utiRicDiag';
        $colonneNome = array(
            "Codice",
            "Descrizione Controller"
        );
        $colonneModel = array(
            array("name" => 'CODICE', "width" => 100),
            array("name" => 'DESCRIZIONE', "width" => 300),
        );
        $gridOptions = array(
            "Caption" => $titolo,
            "width" => '500',
            "height" => '470',
            "sortname" => 'CODICE',
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "arrayTable" => $_appoggio
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnControllers';
        $_POST['retid'] = $retid;
        $_POST['returnKey'] = 'retKey';
        $_POST['returnValue'] = 'CODICE';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>