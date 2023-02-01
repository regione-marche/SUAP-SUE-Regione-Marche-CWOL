<?php
require_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceHelper.class.php';

/**
 * Model Service Factory
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class itaModelServiceFactory {
    public static function newModelService($modelServiceName, $silent = false, $startedTransaction = false) {
        $appRoute = App::getPath('appRoute.' . substr($modelServiceName, 0, 3));
        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/services/' . $modelServiceName . 'Service.php';

        // Se non presente il modelService specifico, prende quello generico
        if (file_exists($modelSrc)) {
            require_once $modelSrc;
            $model = $modelServiceName . 'Service';
        } else {
            require_once(ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
            $model = 'itaModelService';
        }

        try {
            $instance = new $model();
            $instance->setModelName($modelServiceName);
            $instance->setSilent($silent);
            $instance->setStartedTransaction($startedTransaction);
            $instance->setEqAudit(new eqAudit());
            
            self::setProperties($instance, $modelServiceName);
        } catch (Exception $e) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore creazione ModelService: $modelServiceName");
        }

        return $instance;
    }

    private static function setProperties(&$modelService, $modelServiceName){
        $helper = new cwbModelServiceHelper();
        switch(substr($modelServiceName, 0, 2)){
            case 'cw':
                $modelService->setBehaviors($helper->initBehaviors());
                require_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
                $modelService->setModelHelper('cwbModelHelper');
                break;
            default:
                switch(substr($modelServiceName, 0, 3)){
                    case 'cep':
                        $modelService->setBehaviors($helper->initBehaviors(array('AUDIT')));
                        break;
                }
                require_once ITA_LIB_PATH . '/itaPHPCore/itaModelHelper.class.php';
                $modelService->setModelHelper('itaModelHelper');
                break;
        }
    }
}