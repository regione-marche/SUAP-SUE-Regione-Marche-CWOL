<?php

/**
 * LIBEREIA PER AUDIT DB FRONT OFFICE
 *
 * @author Andrea
 * @author Simone
 * 
 */
class praLibAudit {

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
    
    protected $PRAM_DB;

    function __construct() {
        try {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', ITA_DB_SUFFIX);
        } catch (Exception $e) {
            //App::log($e->getMessage());
            
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
    function logEqEvent($params = array()) {
        $Operaz_rec['OPEFIS'] = $params['RICFIS'];
        $Operaz_rec['ROWID_PRORIC'] = $params['ROWID_PRORIC'];
        $Operaz_rec['ROWID_PASSO'] = $params['ROWID_PASSO'];
        $Operaz_rec['RICOPEOPE'] = $params['Operazione'];
        $Operaz_rec['RICOPEKEY'] = (isset($params['Key'])) ? $params['Key'] : '';
        $Operaz_rec['RICOPEEST'] = $params['Estremi'];;
        $Operaz_rec['RICOPEMETA'] = $params['Metadati'];
        $Operaz_rec['RICOPEIP'] = $_SERVER['REMOTE_ADDR'];
        $Operaz_rec['RICOPEDAT'] = date('Ymd');
        $Operaz_rec['RICOPETIM'] = date('His');
        $Operaz_rec['RICOPESPIDCODE'] = (isset($params['SpidCode'])) ? $params['SpidCode'] : '';

        try {
            ItaDB::DBInsert($this->PRAM_DB, "RICOPERAZ", 'ROW_ID', $Operaz_rec);
        } catch (Exception $e) {
            //Out::msgStop("Errore in Inserimento log eventi", $e->getMessage(), '600', '600');
        }
    }

}
