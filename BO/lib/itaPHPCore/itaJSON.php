<?php

class itaJSON {

    private static function utf8_encode($array) {
        array_walk_recursive($array, function(&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return $array;
    }

    private static function utf8_decode($array) {
        array_walk_recursive($array, function(&$item, $key) {
            $item = utf8_decode($item);
        });
        return $array;
    }

    /**
     * Effeffuta encode in json dell'oggetto '$toEncode'
     * @param array $toEncode oggetto da codificare in json 
     * @return String
     */
    public static function json_encode($toEncode) {
        return json_encode(self::utf8_encode($toEncode));
    }

    /**
     * Effettua il decode dell'oggetto "$toDecode"
     * @param string $toDecode stringa da decodificare 
     * @return array
     */
public static function json_decode($toDecode) {
        return self::utf8_decode(json_decode($toDecode,true));
    }

}

?>