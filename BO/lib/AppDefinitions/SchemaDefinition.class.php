<?php

class SchemasDefinition {

    /**
     *
     * Schema di istanza cityware.online
     * @var array 
     */
    static public $commonSchemas = array(
        'ITALWEBDB' => array(
            'DESCRIZIONE' => 'ARCHIVIO DEI DOMINI / ENTI / ORGANIZZAZIONI E TABELLE COLLEGATE',
            'OBBLIGATORIO' => true
        ),
        'COMUNI' => array(
            'DESCRIZIONE' => 'ARCHIVIO DEI COMUNI ITALIANI E STATI ESTERI',
            'OBBLIGATORIO' => true
        ),
        'DBPARA' => array(
            'DESCRIZIONE' => 'ARCHVIO DEI LOG  DELLE OPERAZIONI SUL SISTEMA',
            'OBBLIGATORIO' => true
        ),
    );

    /**
     * Schema di dominio/ente/organizzazione/ditta cityware.online
     * @var array 
     */
    static public $tenantSchemas = array(
        'ITW' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'ITALWEB' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'ACCERT' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'BDAP' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'CATA' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'CDSRUOLI' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'CEIM' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'CEP' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'CODSTRADA' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'COMM' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'EELL' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'GAFIERE' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'GAPACE' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'GASIN' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'GEPR' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'ICI' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'ISOLA' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'ITAFRONTOFFICE' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'MACERIE' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'PRAM' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'PROT' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'RAST' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'SEGR' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => true
        ),
        'TRIB' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        ),
        'MIS' => array(
            'DESCRIZIONE' => 'MODULO INTEROPERABILITA SISMA MARCHE',
            'OBBLIGATORIO' => false
        )
    );

    /**
     * Schema di utilità per lo sviluppo
     * @var type 
     */
    static public $developerSchemas = array(
        'GENERATOR' => array(
            'DESCRIZIONE' => '',
            'OBBLIGATORIO' => false
        )
    );

    public static function getCommonSchemas() {
        return self::$commonSchemas;
    }

    public static function getTenantSchemas() {
        return self::$tenantSchemas;
    }

    public static function getDeveloperSchemas() {
        return self::$developerSchemas;
    }

}
