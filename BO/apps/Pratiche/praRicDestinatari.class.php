<?php

/* * 
 *
 * Utiity ricerche destinatari
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    28/09/2017
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praRicDestinatari {

    static function CercaDaAnagrafe() {
        $_POST = array();
        $model = 'utiVediAnel';
        $_POST['event'] = 'openform';
        $_POST['Ricerca'] = 1;
        $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE';
        itaLib::openDialog($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>