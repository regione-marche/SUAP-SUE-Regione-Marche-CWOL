<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of eqAudit
 *
 * @author michele
 */
class eqAudit {

    const OP_FORM_OPEN = "01";
    const OP_OPEN_RECORD = "02";
    const OP_INS_RECORD = "04";
    const OP_DEL_RECORD = "05";
    const OP_UPD_RECORD = "06";
    const OP_INS_RECORD_FAILED = "07";
    const OP_DEL_RECORD_FAILED = "08";
    const OP_UPD_RECORD_FAILED = "09";
    const OP_GENERIC_ERROR = "10";
    const OP_GENERIC_WARNING = "11";
    const OP_MISC_AUDIT = "99";

    private static $DBPARA;
    private static $Op_descriptons = array(
        self::OP_FORM_OPEN => "Apertura Form",
        "02" => "Accesso ai dati",
        "03" => "",
        "04" => "Inserimento Dati",
        "05" => "Cancellazione Dati",
        "06" => "Aggiornamento Dati",
        "07" => "Mancato Inserimento Dati",
        "08" => "Mancata Cancellazione Dati",
        "09" => "Mancato Aggiornamento Dati",
        "10" => "Errore Generico",
        "11" => "Avviso Generico",
        "12" => "SQL Query",
        "13" => "Errore in esecuzione SQL Query",
        "99" => ""
    );

    /**
     * 
     * @param string $model model di riferimento dell'evento, blank se non disponibile
     * @param array $parms array di parametri
     * <br>
     * <pre>Array paramerti $param
     * array(
     *  'Operazione' => 'Codice Operazione', 
     *  'DB' => 'Data Base',
     *  'DSet' => 'Tabella',
     *  'Estremi' => 'Descrizione Operazione'
     * )
     * </pre>

     */
    static public function logEqEvent($model, $parms = array()) {
        if (!self::$DBPARA) {
            try {
                self::$DBPARA = ItaDB::DBOpen('DBPARA', '');
            } catch (Exception $e) {
                return false;
            }
        }

        switch ($parms['Operazione']) {
            case '01':
                $parms['Estremi'] = (!key_exists('Estremi', $parms)) ? '' : $parms['Estremi'];
                if (!$parms['Estremi']) {
                    $parms['Estremi'] = "Accesso al Model: " . $model;
                }
                break;
            default:
                break;
        }

        $Operaz_rec['OPEUID'] = frontOfficeApp::$cmsHost->getUserID();
        $Operaz_rec['OPELOG'] = frontOfficeApp::$cmsHost->getUserName();
        $Operaz_rec['OPEIIP'] = $_SERVER['REMOTE_ADDR'];
        $Operaz_rec['OPEDBA'] = (key_exists('DB', $parms)) ? $parms['DB'] : '';
        $Operaz_rec['OPEDSE'] = (key_exists('DSet', $parms)) ? $parms['DSet'] : '';
        $Operaz_rec['OPEPRG'] = substr($model, 0, 20);
        $Operaz_rec['OPEOPE'] = $parms['Operazione'];
        $Operaz_rec['OPEDAT'] = date('Ymd');
        $Operaz_rec['OPETIM'] = date('His');
        $Operaz_rec['OPEEST'] = 'FO - ' . $parms['Estremi'];
        $Operaz_rec['OPEDIT'] = frontOfficeApp::getEnte();
        $Operaz_rec['OPEKEY'] = (isset($parms['Key'])) ? $parms['Key'] : '';
        $Operaz_rec['OPESPIDCODE'] = '';

        if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
            require_once '/var/www/html/cwol-front/simplesamlphp/lib/_autoload.php';
            $as = new SimpleSAML_Auth_Simple('federatest-sp');
            $attributes = $as->getAttributes();
            if ($attributes['spidCode'][0]) {
                $Operaz_rec['OPESPIDCODE'] = $attributes['spidCode'][0];
            }
        }

        try {
            ItaDB::DBInsert(self::$DBPARA, 'OPERAZ', 'ROWID', $Operaz_rec);
        } catch (Exception $e) {
            return false;
        }
    }

}
