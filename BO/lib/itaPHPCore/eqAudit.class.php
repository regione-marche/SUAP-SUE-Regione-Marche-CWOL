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
    protected $DBPARA;

    function __construct() {
        try {
            $this->DBPARA = ItaDB::DBOpen('DBPARA', false);
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

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
    function logEqEvent($model, $parms = array()) {
        switch ($parms['Operazione']) {
            case '01':
                $parms['Estremi'] = (!key_exists('Estremi', $parms)) ? '' : $parms['Estremi'];
                if (!$parms['Estremi']) {
                    $parms['Estremi'] = "Accesso al Model: " . get_class($model);
                }
                break;
            default:
                break;
        }
        $Operaz_rec['OPEUID'] = App::$utente->getKey('idUtente');
        $Operaz_rec['OPELOG'] = App::$utente->getKey('nomeUtente');
        $Operaz_rec['OPEIIP'] = $_SERVER['REMOTE_ADDR'];
        $Operaz_rec['OPEDBA'] = (key_exists('DB', $parms)) ? $parms['DB'] : '';
        $Operaz_rec['OPEDSE'] = (key_exists('DSet', $parms)) ? $parms['DSet'] : '';
        $Operaz_rec['OPEPRG'] = get_class($model);
        $Operaz_rec['OPEOPE'] = $parms['Operazione'];
        $Operaz_rec['OPEDAT'] = date('Ymd');
        $Operaz_rec['OPETIM'] = date('His');
        $Operaz_rec['OPEEST'] = $parms['Estremi'];
        $Operaz_rec['OPEDIT'] = App::$utente->getKey('ditta');
        $Operaz_rec['OPEKEY'] = (isset($parms['Key'])) ? $parms['Key'] : '';
        $Operaz_rec['OPESPIDCODE'] = (isset($parms['SpidCode'])) ? $parms['SpidCode'] : '';
        try {
            $inserted = ItaDB::DBInsert($this->DBPARA, "OPERAZ", 'ROWID', $Operaz_rec);
        } catch (Exception $e) {
            Out::msgStop("Errore in Inserimento log eventi", $e->getMessage(), '600', '600');
        }
    }

}
