<?php

require_once 'itaDocumentMapping.class.php';
require_once 'itaDocumentMappingDummy.class.php';

class itaDocumentMappingFactory {

    public static $mappingSyntaxes = array(
        'MAGGIOLI' => 'itaDocumentMappingMaggioli'
    );

    public static function getMappingSyntaxes() {
        return self::$mappingSyntaxes;
    }

    public static function getMapping($syntax = null) {
        if (!$syntax) {
            return new itaDocumentMappingDummy;
        }

        if (!isset(self::$mappingSyntaxes[$syntax])) {
            throw new ItaException('Sintassi non valida');
        }

        $className = self::$mappingSyntaxes[$syntax];
        require_once "$className.class.php";
        return new $className;
    }

}
