<?php

/**
 *
 * ANAGRAFICA SETTORI COMMERCIALI
 *
 * PHP Version 5
 *
 * @category   
 * @package    Base
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    18.09.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
class basRuolo {

    static $SISTEM_SUBJECT_ROLES = array(
          "ESIBENTE" => array('RUOCOD' => "0001", 'RUODES' => 'Esibente')
        , "DICHIARANTE" => array('RUOCOD' => "0002", 'RUODES' => 'Dichiarante')
        , "SOCIO" => array('RUOCOD' => "0003", 'RUODES' => 'Socio')
        , "IMPRESA" => array('RUOCOD' => "0004", 'RUODES' => 'Impresa')
        , "IMPRESAINDIVIDUALE" => array('RUOCOD' => "0005", 'RUODES' => 'Impresa Individuale')
        , "TECNICO" => array('RUOCOD' => "0006", 'RUODES' => 'Tecnico')
        , "TECPRG" => array('RUOCOD' => "0007", 'RUODES' => 'Tecnico Progettista')
        , "DIRARC" => array('RUOCOD' => "0008", 'RUODES' => 'Tecnico direttore lavori opere architettoniche')
        , "DIRSTR" => array('RUOCOD' => "0010", 'RUODES' => 'Tecnico direttore lavori opere strutturali')
        , "TECSTR" => array('RUOCOD' => "0011", 'RUODES' => 'Tecnico progettista opere strutturali')
        , "IMPRESAESEC" => array('RUOCOD' => "0012", 'RUODES' => 'Impresa esecutrice dei lavori')
        , "ALTRISOGESEC" => array('RUOCOD' => "0013", 'RUODES' => 'Altri soggetti inidicati per le esecuzionide lavori')
        , "UNITALOCALE" => array('RUOCOD' => "0014", 'RUODES' => 'Unità Locale')
        , "DICHIARANTENIA" => array('RUOCOD' => "0015", 'RUODES' => 'Dichiarante NIA')
        , "ALTRISOGG" => array('RUOCOD' => "0016", 'RUODES' => 'Altri Soggetti')
        , "ALTRITECNICI" => array('RUOCOD' => "0017", 'RUODES' => 'Altri Tecnici')
        , "PROCURATORE" => array('RUOCOD' => "0018", 'RUODES' => 'Procuratore')
        , "RINUNCIATARIO" => array('RUOCOD' => "0019", 'RUODES' => 'Rinunciatario')
        , "NONSOTTOSCRITTORI" => array('RUOCOD' => "0020", 'RUODES' => 'Non Sottoscrittori')
        , "CURATORE" => array('RUOCOD' => "0021", 'RUODES' => 'Curatore')
        , "CONTRAENTE" => array('RUOCOD' => "0022", 'RUODES' => 'Contraente')
        , "CONTRAENTEFIRMATARIO" => array('RUOCOD' => "0023", 'RUODES' => 'Contraente Firmatario')
    );
    static $CONFIGURABLE_ROLES_FROM_CODE = "9000";
    static $CONFIGURABLE_ROLES_TO_CODE = "9999";
    static $SUBJECT_BASE_FIELDS = array(
        "NOME" => "DESNOME", //NEW *
        "COGNOME" => "DESCOGNOME", //NEW *    
        "COGNOME_NOME" => "DESNOM", //NEW *    
        "RAGIONESOCIALE" => "DESRAGSOC", //NEW *
        "SESSO_SEX" => "DESSESSO", //NEW *
        "NASCITACOMUNE" => "DESNASCIT", //NEW *  
        "NASCITAPROVINCIA_PV" => "DESNASPROV", //NEW *    
        "NASCITANAZIONE" => "DESNASNAZ", //NEW *       
        "NASCITADATA_DATA" => "DESNASDAT", //NEW *       
        "RESIDENZACOMUNE" => "DESCIT",
        "SEDECOMUNE" => "DESCIT",
        "RESIDENZALOCALITA" => "DESLOC",
        "SEDELOCALITA" => "DESLOC",
        "RESIDENZACAP_CAP" => "DESCAP",
        "SEDECAP" => "DESCAP",
        "RESIDENZAPROVINCIA_PV" => "DESPRO",
        "SEDEPROVINCIA_PV" => "DESPRO",
        "RESIDENZANAZIONE_NAZ" => "DESNAZ",
        "SEDENAZIONE_NAZ" => "DESNAZ",
        "RESIDENZAVIA" => "DESIND",
        "SEDEVIA" => "DESIND",
        "RESIDENZACIVICO" => "DESCIV", //NEW *
        "SEDECIVICO" => "DESCIV", //NEW *
        "SEDELEGCOMUNE" => "DESCIT",
        "SEDELEGPROVINCIA_PV" => "DESPRO",
        "SEDELEGVIA" => "DESIND",
        "SEDELEGCIVICO" => "DESCIV",
        "SEDELEGCAP" => "DESCAP",
        "SEDELEGTELEFONO" => "DESTEL",
        "SEDELEGCELLULARE" => "DESCEL",
        "SEDELEGFAX" => "DESFAX",
        "SEDELEGEMAIL" => "DESPEC",
        "CODICEFISCALE_CFI" => "DESFIS",
        "PARTITAIVA_PIVA" => "DESPIVA", //NEW *
        "TELEFONO" => "DESTEL", //NEW *
        "CELLULARE" => "DESTEL", //NEW *
        "FAX" => "DESFAX", //NEW *    
        "EMAIL" => "DESEMA",
        "PEC" => "DESPEC", //NEW *
        "NATURALEGA_RADIO" => "DESNATLEGALE", //NEW *
        "CMSUSER" => "DESCMSUSER", //NEW *
        "QUALIFICA" => "DESQUALIFICA",
        "NUMISCRIZIONE" => "DESNUMISCRIZIONE",
        "PROVISCRIZIONE" => "DESPROVISCRIZIONE",
        "ORDINEISCRIZIONE" => "DESORDISCRIZIONE",
        "RUOLOESTESO" => "DESRUOEXT"
    );
    static $SUBJECT_ROLE_FIELDS = array(
        "ESIBENTE" => array(),
        "DICHIARANTE" => array(),
        "SOCIO" => array(),
        "IMPRESA" => array(),
        "IMPRESAINDIVIDUALE" => array(),
        "TECNICO" => array()
    );

    static public function getSystemSubjectCode($role) {
        if (isset(self::$SISTEM_SUBJECT_ROLES[$role])) {
            $subjectInfo = self::$SISTEM_SUBJECT_ROLES[$role];
            return $subjectInfo['RUOCOD'];
        } else {
            return false;
        }
    }

    static public function getSystemSubjectRoleFields($ruocod) {
        foreach (self::$SISTEM_SUBJECT_ROLES as $prefix => $ruolo) {
            if ($ruocod == $ruolo['RUOCOD']) {
                return $prefix;
            }
        }
        return false;
    }

    /**
     * 
     * @param type $basLib Libreria di lavoro 
     */
    static public function initSistemSubjectRoles($basLib) {
        foreach (self::$SISTEM_SUBJECT_ROLES as $Ruolo) {
            $Anaruo_rec = $basLib->GetRuolo($Ruolo['RUOCOD']);
            if (!$Anaruo_rec) {
                $Anaruo_rec = $basLib->SetMarcaturaRuolo($Ruolo, true);
                try {
                    $nrow = ItaDB::DBInsert($basLib->getBASEDB(), "ANA_RUOLI", 'ROWID', $Anaruo_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 
     * @param type $role
     * @return boolean
     */
    static public function isConfigurable($role) {
        if ($role >= self::$CONFIGURABLE_ROLES_FROM_CODE && $role <= self::$CONFIGURABLE_ROLES_TO_CODE) {
            return true;
        }
        return false;
    }

}

?>
