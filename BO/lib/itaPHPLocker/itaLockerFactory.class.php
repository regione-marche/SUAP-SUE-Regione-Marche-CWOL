<?php

/**
 * Factory per la gestione del Locker
 * @author l.pergolini
 */
class itaLockerFactory {

    private static $recordLockTypes = array(
        'cityware' => 'itaLockerManagerCityware', //Locker cityware bwe_reclck
        'default' => 'itaLockerManagerDefault'    //Locker mysql tabella lock
    );

    public static function getLockerManager($type = nulL) {
        if (!$type) {
          $type = "default";
        }
        try {
            $className = self::$recordLockTypes[$type];
            require_once(ITA_BASE_PATH . "/lib/itaPHPLocker/$className.class.php");       
            $instance = new $className();
        } catch (Exception $ex) {
            return false;
        }
        return $instance;
    }

}
