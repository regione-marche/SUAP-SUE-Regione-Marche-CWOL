<?php
/**
 * Permette di impostare quali voci del menu sono visibili da ogni gruppo di utenti
 *    (gestione dei permessi)
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Accattoli
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 */
function menAuthConfig() {
    $menAuthConfig = new menAuthConfig();
    $menAuthConfig->parseEvent();
    return;
}

class menAuthConfig extends itaModel {

    public $nameForm = "menAuthConfig";
    public $ITW_DB;
    public $ITALSOFT_DB;
    public $tree;
    public $tableId = 'menAuthConfig_gridPermessi';

    function __construct() {
        parent::__construct();
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $this->tree = App::$utente->getKey($this->nameForm . '_tree');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tree', $this->tree);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::hide($this->nameForm . '_applicativo');
                Out::hide($this->nameForm . '_applicativo_lbl');
                Out::hide($this->nameForm . '_griglia');
                $this->showGroupSelect();
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gruppo':
                        if ($_POST[$this->nameForm . '_gruppo'] == '') {
                            Out::hide($this->nameForm . '_applicativo');
                            Out::hide($this->nameForm . '_applicativo_lbl');
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }
                        $this->showMenuSelect();
                        Out::hide($this->nameForm . '_griglia');
                        Out::show($this->nameForm . '_applicativo');
                        Out::show($this->nameForm . '_applicativo_lbl');
                        break;
                    case $this->nameForm . '_applicativo':
                        if ($_POST[$this->nameForm . '_applicativo'] == '') {
                            Out::hide($this->nameForm . '_griglia');
                            break;
                        }
                        Out::show($this->nameForm . '_griglia');
                        $this->showGrid();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        break;
                }
                break;
            case 'afterSaveCell':  // Click di una checkbox
                $this->saveCheckDb();
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close=true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {

        App::$utente->removeKey($this->nameForm . '_tree');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    /**
     *  Riempie la select dei gruppi utenti
     */
    function showGroupSelect() {
        $sql = "SELECT * FROM GRUPPI";
        $Gruppi_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql, true);

        Out::select($this->nameForm . '_gruppo', '1', "", 1, "Seleziona......");

        foreach ($Gruppi_tab as $keyGruppi => $Gruppi_rec) {
            Out::select($this->nameForm . '_gruppo', '1', $Gruppi_rec['GRUCOD'], 0, $Gruppi_rec['GRUDES']);
        }
    }

    /**
     *  Riempie la select delle voci dei menu
     */
    function showMenuSelect() {

        $sql = "SELECT * FROM ita_menu WHERE me_menu = 'TI_MEN'";// AND APLGRU.APLGRU = '"
                //. str_pad($_POST[$this->nameForm . '_gruppo'], 10, '0', STR_PAD_LEFT) . "' AND APLGRU.APLPRG = ARAPPX.ARCODA";

        $Applicativi_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);

        Out::select($this->nameForm . '_applicativo', '1', "", 1, "Seleziona......");

        foreach ($Applicativi_tab as $keyApplicativo => $Applicativo_rec) {
            Out::select($this->nameForm . '_applicativo', '1', $Applicativo_rec['ARCODA'], 0, $Applicativo_rec['ARDESA']);
        }
    }

    /**
     *  Riempie la griglia con gli opportuni valori
     */
    function showGrid() {
        $chiave = $_POST[$this->nameForm . '_applicativo'];
        $gruppo = str_pad($_POST[$this->nameForm . '_gruppo'], 10, '0', STR_PAD_LEFT);

        $sql = "SELECT * FROM APLGRU WHERE APLGRU.APLPRG = '" . $chiave . "' AND APLGRU.APLGRU = '" . $gruppo . "'";
        $Aplgru_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);

        $sql = "SELECT * FROM ARAPPX WHERE ARCODA = '" . $chiave . "'";
        $Arcoda_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
        $albero = array();
        $inc = 0;
        $albero[$inc]['INDICE'] = $inc;
        $albero[$inc]['ROWID'] = $Aplgru_rec['ROWID'];
        $albero[$inc]['ARDESA'] = $Arcoda_rec['ARDESA'];
        $albero[$inc]['level'] = 0;
        //$albero[$inc]['parent'] = NULL;
        $albero[$inc]['isLeaf'] = 'false';
        $albero[$inc]['expanded'] = false;  // o true?
        $save_count = count($albero);

        $this->tree = $this->caricaTreeLegami($chiave, $albero, 1, $albero[$inc]['INDICE']);
        if ($save_count == count($this->tree)) {
            $this->tree[$inc]['isLeaf'] = 'true';
        }
        $arr = array('arrayTable' => $this->tree,
            'rowIndex' => 'idx');

        $griglia = new TableView($this->tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows('1000');
        TableView::enableEvents($this->tableId);
        TableView::clearGrid($this->tableId);
        $griglia->getDataPage('json');
        return;
    }

    /**
     *  Funzione ricorsiva che costruisce l'array/albero
     * @param String $chiave  La chiave (intera) che identifica la voce di menu
     * @param Array $albero   L'array/albero
     * @param Int $level      Livello di profondita dell'albero
     * @param Int $rowid      ID del nodo o della foglia al quale si agganceranno altri nodi o foglie
     * @return                Restituisce l'albero
     */
    function caricaTreeLegami($chiave, $albero, $level, $rowid) {
        if ($level == 10) {
            return $albero;
        }

        $chiave = explode("|", $chiave);

        $sql = "SELECT
                APLGRU.ROWID AS ROWID,            
                APLGRU.APLGRU AS APLGRU, 
                APLGRU.APLSEQ AS APLSEQ,                 
                APLGRU.APLPRG AS APLPRG,
                ARAPPX.ARDESA AS ARDESA,
                APLGRU.APLOFF AS APLOFF,
                APLGRU.APLNOE AS APLNOE,
                APLGRU.APLNOC AS APLNOC 
            FROM
                APLGRU, ARAPPX
            WHERE
                APLGRU.APLGRU = '" . str_pad($_POST[$this->nameForm . '_gruppo'], 10, '0', STR_PAD_LEFT) . "'
                AND APLGRU.APLMEN = '" . $chiave[1] . "' AND APLGRU.APLPRG = ARAPPX.ARCODA ORDER BY APLSEQ";

        $menu_tab = ItaDB::DBSQLSelect($this->ITW_DB, $sql, true);
        if ($menu_tab) {
            foreach ($menu_tab as $i => $menu_rec) {
                $inc = count($albero) + 1;
                $albero[$inc] = $menu_rec;
                $albero[$inc]['INDICE'] = $inc;
                $albero[$inc]['level'] = $level;
                $albero[$inc]['parent'] = $rowid;
                $albero[$inc]['isLeaf'] = 'false';
                $albero[$inc]['expanded'] = false;
                $save_count = count($albero);
                $chiave = $menu_rec['APLPRG'];
                $albero = $this->caricaTreeLegami($chiave, $albero, $level + 1, $albero[$inc]['INDICE']);
                if ($save_count == count($albero)) {
                    $albero[$inc]['isLeaf'] = 'true';
                    $albero[$inc]['expanded'] = false;
                } else {
                    $albero[$inc]['ARDESA'] = "<span style=\"font-weight:bold;color:darkred;\">" . $albero[$inc]['ARDESA'] . "</span>";
                }
            }
        }
        return $albero;
    }

    /**
     *  Salva il click della checkbox nel database e modifica altre eventuali
     *   occorrenze all'interno della griglia albero
     */
    function saveCheckDb() {
        $colonna = $_POST['cellname'];
        $riga = $_POST['rowid'];
        $riga_id = false;
        $valore = $_POST['value'];
//        if ($valore == 0) {
//            $valore = "";
//        }
        $descrizione = '';

        foreach ($this->tree as $row) {
            if ($row['INDICE'] == $riga) {
                $riga_id = $row['ROWID'];
                $descrizione = $row['ARDESA'];
                break;
            }
        }
        // Salva l'impostazione nel database
        if ($riga !== false) {
            if ($_POST['id'] == $this->nameForm . '_gridPermessi') {
                $sql = 'SELECT * FROM APLGRU WHERE ROWID = ' . $riga_id;
                $Aplgru_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                $Aplgru_rec[$colonna] = $valore;

                try {

                    $nrows = ItaDB::DBUpdate($this->ITW_DB, 'APLGRU', "ROWID", $Aplgru_rec);
                    if ($nrows == 0) {
                        Out::msgStop("Aggiornamento Flag", "Riga non Aggiornata");
                    }

                    // Generazione del log
                    $update_Info = 'Aggiornamento dei permessi dell\'utente: ';
                    switch ($colonna) {
                        case 'APLOFF':
                            if ($valore == "0")
                                $update_Info .= 'Voce di menu "' . $descrizione . '" abilitata.';
                            else
                                $update_Info .= 'Voce di menu "' . $descrizione . '" disabilitata.';
                            break;
                        case 'APLNOE':
                            if ($valore == "0")
                                $update_Info .= 'Applicazione "' . $descrizione . '" con funzione di Edit.';
                            else
                                $update_Info .= 'Applicazione "' . $descrizione . '" senza funzione di Edit.';
                            break;
                        case 'APLNOC':
                            if ($valore == "0")
                                $update_Info .= 'Applicazione "' . $descrizione . '" con funzione di Cancellazione.';
                            else
                                $update_Info .= 'Applicazione "' . $descrizione . '" senza funzione di Cancellazione.';
                            break;
                        default:
                            break;
                    }
                    $this->updateRecord($this->ITW_DB, 'APLGRU', $Aplgru_rec, $update_Info);
                } catch (Exception $exc) {
                    Out::msgStop("Errore in Aggiornamento", $exc->getMessage());
                }
            }
        }
        
        // Aggiorna le voci identiche
        // Non funziona bene!! Problemi nel javascript da identificare bene.
//        foreach ($this->tree as $key => $row) {
//            if ($row['ROWID'] == $riga_id) {
////                if ($row['INDICE'] == $riga) {
////                    App::log($riga);
////                    continue;
////                }
//                $this->tree[$key][$colonna] = $valore;
//                TableView::setCellValue($this->tableId, $this->tree[$key]['INDICE'],$colonna, $valore,'');
//            }
//        }
        //TableView::setRowData($this->tableId, $rowdata['INDICE'],$rowdata); 
        //TableView::setRowData('$this->tableId', '$riga', '', "{'background-color':'#FF6F6F'}");
    }

}

?>
