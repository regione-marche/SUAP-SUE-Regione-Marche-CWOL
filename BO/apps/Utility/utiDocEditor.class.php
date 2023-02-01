<?php
include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

class utiDocEditor {
    
   /**
    * 
    * @param type $dataArray
    * @return string
    */
    public static function getUrl($dataArray) {
        $editKey = uniqid(App::$utente->getKey('TOKEN'));
        App::$utente->setKey($editKey . "_DATAARRAY", $dataArray);

        $test = '';
        if (App::$utente->getKey('environment_test')) {
            $test = '&test=' . App::$utente->getKey('environment_test');
        }

        $url = "docEditor.php?TOKEN=" . App::$utente->getKey('TOKEN') . $test . "&key=" . $editKey ;
        return $url;
    }

}
