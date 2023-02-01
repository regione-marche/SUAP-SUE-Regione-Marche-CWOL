<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

/**
 *
 * LIBRERIA PER GESTIONE CHIAMATE A PROTOCOLLI DI TERZE PARTI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Protocollo
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    22.09.2017
 * @link
 * @see
 * @since
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

class proWsClientFactory {

    public static function getInstance($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }

        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_ITALSOFT_REMOTO:
            case proWsClientHelper::CLIENT_ITALSOFT_REMOTO_ALLE:
            case proWsClientHelper::CLIENT_MANUAL:
            case proWsClientHelper::CLIENT_ITALSOFT:
                return true;
            case proWsClientHelper::CLIENT_PALEO:
                $model = 'proPaleo.class';
                break;
            case proWsClientHelper::CLIENT_PALEO4:
                $model = 'proPaleo4.class';
                break;
            case proWsClientHelper::CLIENT_PALEO41:
                $model = 'proPaleo41.class';
                break;
            case proWsClientHelper::CLIENT_WSPU:
                $model = 'proHWS.class';
                break;
            case proWsClientHelper::CLIENT_INFOR:
                $model = 'proInforJProtocollo.class';
                break;
            case proWsClientHelper::CLIENT_IRIDE:
                $model = 'proIride.class';
                break;
            case proWsClientHelper::CLIENT_JIRIDE:
                $model = 'proJiride.class';
                break;
            case proWsClientHelper::CLIENT_HYPERSIC:
                $model = 'proHyperSIC.class';
                break;
            case proWsClientHelper::CLIENT_ELIOS:
                $model = 'proELios.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprot.class';
                break;
            case proWsClientHelper::CLIENT_SICI:
                $model = 'proSici.class';
                break;
            case proWsClientHelper::CLIENT_LEONARDO:
                $model = 'proLeonardo.class';
                break;
            case proWsClientHelper::CLIENT_KIBERNETES:
                $model = 'proKibernetesProt.class';
                break;
            case proWsClientHelper::CLIENT_CIVILIANEXT:
                $model = 'proCiviliaNext.class';
                break;
            case proWsClientHelper::CLIENT_DATAGRAPH:
                $model = 'proDatagraph.class';
                break;
            case proWsClientHelper::CLIENT_FOLIUM:
                $model = 'proFolium.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

        return new $classe();
    }

    public static function getInstanceFascicolazione($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }
        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_PALEO:
                $model = 'proPaleoFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_PALEO4:
                $model = 'proPaleo4Fascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_WSPU:
                $model = 'proHWSFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_INFOR:
                $model = 'proInforJProtocollo.class';
                break;
            case proWsClientHelper::CLIENT_IRIDE:
                $model = 'proIrideFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_JIRIDE:
                $model = 'proJirideFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_HYPERSIC:
                $model = 'proHyperSICFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_ELIOS:
                $model = 'proELiosFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprotFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_SICI:
                $model = 'proSiciFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_LEONARDO:
                $model = 'proLeonardoFascicolazione.class';
                break;
            case proWsClientHelper::CLIENT_KIBERNETES:
                $model = 'proKibernetesFascicolazione.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        if (file_exists(App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php')) {
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            return new $classe();
        } else {
            return true;
        }
    }

    public static function getInstanceInvioMail($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }
        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_JIRIDE:
                $model = 'proJirideMail.class';
                break;
            case proWsClientHelper::CLIENT_PALEO4:
                $model = 'proPaleo4.class';
                break;
            case proWsClientHelper::CLIENT_PALEO41:
                $model = 'proPaleo41.class';
                break;
            case proWsClientHelper::CLIENT_DATAGRAPH:
                $model = 'proDatagraph.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprot.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        if (file_exists(App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php')) {
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            return new $classe();
        } else {
            return true;
        }
    }

    public static function getInstanceVerificaInvio($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }
        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_JIRIDE:
                $model = 'proJirideMail.class';
                break;
            case proWsClientHelper::CLIENT_PALEO4:
                $model = 'proPaleo4.class';
                break;
            case proWsClientHelper::CLIENT_PALEO41:
                $model = 'proPaleo41.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprot.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        if (file_exists(App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php')) {
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            return new $classe();
        } else {
            return true;
        }
    }

    public static function getInstanceRicerche($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }
        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_IRIDE:
                $model = 'proIrideRicerche.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprot.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        return new $classe();
    }

    public static function getInstanceTabelle($driver = 'auto') {
        if ($driver == 'auto') {
            $driver = self::getAutoDriver();
        }
        if (!$driver) {
            return false;
        }

        switch ($driver) {
            case proWsClientHelper::CLIENT_IRIDE:
                $model = 'proIrideTabelle.class';
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
                $model = 'proItalprot.class';
                break;
            default:
                return false;
        }
        list($classe, $kip) = explode(".", $model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        return new $classe();
    }

    function getAutoDriver() {
        $driver = null;
        $utiEnte = new utiEnte();
        $PARMENTE_rec = $utiEnte->GetParametriEnte();
        if ($PARMENTE_rec) {
            $driver = $PARMENTE_rec['TIPOPROTOCOLLO'];
        }
        return $driver;
    }

}
