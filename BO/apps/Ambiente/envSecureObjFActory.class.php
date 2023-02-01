<?php

class envSecureObjFactory {

    public static $lastErrorCode;
    public static $lastErrorMessage;

    public static function getLastErrorCode() {
        return self::$lastErrorCode;
    }

    public static function getLastErrorMessage() {
        return self::$lastErrorMessage;
    }

    public static function setLastErrorCode($lastErrorCode) {
        self::$lastErrorCode = $lastErrorCode;
    }

    public static function setLastErrorMessage($lastErrorMessage) {
        self::$lastErrorMessage = $lastErrorMessage;
    }

    public static function getEnvSecureObj($objClassName, $objId) {
        $objClassFileName = $objClassName . '.class';
        if (!itaModelHelper::requireAppFileByName($objClassFileName)) {
            self::setLastErrorCode(-1);
            self::setLastErrorMessage("Il file $objClassFileName non è disponibile.");
            return false;
        }

        try {
            $secObj = new $objClassName();
        } catch (Exception $exc) {
            self::setLastErrorCode(-1);
            self::setLastErrorMessage("Istanza classe $objClassName fallita.");
            return false;
        }
        $secObj->setObjId($objId);
        if(!$secObj->LoadSecObjdata()){
            self::setLastErrorCode(-1);
            self::setLastErrorMessage("Impossibile Caricare i dati di istanza per  classe: $objClassName id: ");
            return false;
        }
        return $secObj;
    }

}
