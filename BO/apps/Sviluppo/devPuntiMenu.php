<?php

/**
 *  Gestione Punti Menu
 * 
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Moscioni
 * @author     Michele Accattoli
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    27.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 */
function devPuntiMenu() {
    $devPuntiMenu = new devPuntiMenu();
    $devPuntiMenu->parseEvent();
    return;
}

class devPuntiMenu extends itaModel {

    public $nameForm = "devPuntiMenu";
    public $ITALSOFT_DB;
    public $returnEvent;
    public $returnModel;

//    public $elenco;

    function __construct() {
        parent::__construct();
        try {
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');        
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);            
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->returnEvent=$_POST[$this->nameForm.'_returnEvent'];
                $this->returnModel=$_POST[$this->nameForm.'_returnModel']; 
                Out::select($this->nameForm.'_ita_puntimenu[pm_flagvis]', '1', "-1",1,"Indefinito");                
                Out::select($this->nameForm.'_ita_puntimenu[pm_flagvis]', '1', "1",0,"Consenti");
                Out::select($this->nameForm.'_ita_puntimenu[pm_flagvis]', '1', "0",0,"Nega");
                switch ($_POST['modo']) {
                    case 'new':
                        $this->nuovoPunto();
                        break;
                    case 'edit':
                        $this->dettaglio();
                        break;
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        if ($this->aggiungiPunto()) {
                            $this->ritornoPrincipale();
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        if ($this->aggiornaPunto()) {
                            $this->ritornoPrincipale();
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del punto menu?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaPuntoMenu', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaPuntoMenu', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_Sposta':
                        $this->spostaPunto($this->nameForm, '', true);
                        break;
                    case $this->nameForm . '_ConfermaCancellaPuntoMenu':
                        if ($this->cancellaPunto()) {
                            $this->ritornoPrincipale();
                        }
                        break;
                    case $this->nameForm . '_AnnullaCancellaPuntoMenu':
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ita_puntimenu[pm_categoria]':
                        if ($this->haPuntiMenu($_POST[$this->nameForm . '_ita_puntimenu']['pm_voce'])) {
                            Out::msgInfo('Attenzione', 'Questa voce ha dei punti menu.');
                            Out::valore($this->nameForm . '_ita_puntimenu[pm_categoria]', "ME");
                        }
                        break;
                }
                break;
            case 'returnMenu':
                $me_id = $_POST['retKey'];
                Out::valore($this->nameForm . '_ita_puntimenu[me_id]', $me_id);
                
                $sql = "SELECT * FROM ita_menu WHERE me_id = $me_id";
                $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                Out::valore($this->nameForm . '_menu_padre', $Ita_menu_rec['me_descrizione']);
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
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_returnEvent');        
        App::$utente->removeKey($this->nameForm . '_returnModel');                
        Out::closeDialog($this->nameForm);
    }

    private function nuovoPunto() {
        $this->Mostra(array('Aggiungi'));
        $this->Nascondi(array('Aggiorna', 'Cancella'));
        $me_id = $_POST['me_id'];
        Out::valore($this->nameForm . '_ita_puntimenu[me_id]', $me_id);
        // Prendo la descrizione
        $sql = "SELECT * FROM ita_menu WHERE me_id = " . $me_id;
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        Out::valore($this->nameForm . '_menu_padre', $Ita_menu_rec['me_descrizione']);
        
        Out::valore($this->nameForm . '_ita_puntimenu[pm_datamod]', date("Ymd"));
        Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', '', 1, 'Scegli...');
        Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'ME', 0, 'Menu');
        Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PR', 0, 'Programma');
        Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PT', 0, 'Portlet');
        Out::attributo($this->nameForm . '_ita_puntimenu[pm_voce]', 'readonly',1);        
    }

    private function dettaglio() {
        $this->Nascondi(array('Aggiungi'));
        $this->Mostra(array('Aggiorna', 'Cancella'));
        $pm_id = $_POST['pm_id'];
        $sql = "SELECT * FROM ita_puntimenu WHERE pm_id = " . $pm_id;
        $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        // Prendo la descrizione
        $sql = "SELECT * FROM ita_menu WHERE me_id = " . $Ita_puntimenu_rec['me_id'];
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        Out::valore($this->nameForm . '_menu_padre', $Ita_menu_rec['me_descrizione']);

        Out::valori($Ita_puntimenu_rec, $this->nameForm . '_ita_puntimenu');
        switch ($Ita_puntimenu_rec['pm_categoria']) {
            case 'ME':
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'ME', 1, 'Menu');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PR', 0, 'Programma');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PT', 0, 'Portlet');
                break;
            case 'PR':
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'ME', 0, 'Menu');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PR', 1, 'Programma');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PT', 0, 'Portlet');
                break;
            case 'PT':
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'ME', 0, 'Menu');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PR', 0, 'Programma');
                Out::select($this->nameForm . '_ita_puntimenu[pm_categoria]', '1', 'PT', 1, 'Portlet');
                break;
        }
        Out::attributo($this->nameForm . '_ita_puntimenu[pm_voce]', 'readonly',0);
    }

    private function aggiungiPunto() {
        $Ita_puntimenu_rec = $_POST[$this->nameForm . '_ita_puntimenu'];

        // Inserisco sequenza
        if ($Ita_puntimenu_rec['pm_sequenza'] == '0') {
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_puntimenu_rec['me_id'] . " ORDER BY pm_sequenza DESC LIMIT 1";
            $Ita_puntimenu_rec_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            if (count($Ita_puntimenu_rec_seq) > 0) {
                $Ita_puntimenu_rec['pm_sequenza'] = $Ita_puntimenu_rec_seq['pm_sequenza'] + 1;
            } else {
                $Ita_puntimenu_rec['pm_sequenza'] = 1;
            }
        }
        $Ita_puntimenu_rec['pm_datamod'] = date("Ymd");

        if (!$this->insertRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec, '', 'pm_id')) {
            Out::msgInfo('Attenzione', 'Errore nell\'inserimento del punto menu.');
            return false;
        }
        
        // Modifico sequenza
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_puntimenu_rec['me_id'] . " ORDER BY pm_sequenza";
        $Ita_puntimenu_tab_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        $i = 10;
        foreach ($Ita_puntimenu_tab_seq as $key => $Ita_puntimenu_rec_seq) {
            $Ita_puntimenu_rec_seq['pm_sequenza'] = $i;
            $this->updateRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec_seq, '', 'pm_id');
            $i += 10;
        }
        return true;
    }

    private function aggiornaPunto() {
        $id = $_POST[$this->nameForm . '_ita_puntimenu']['pm_id'];

        $Ita_puntimenu_rec = $_POST[$this->nameForm . '_ita_puntimenu'];
        

        // Inserisco sequenza
        if ($Ita_puntimenu_rec['pm_sequenza'] == '0') {
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_puntimenu_rec['me_id'] . " ORDER BY pm_sequenza DESC LIMIT 1";
            $Ita_puntimenu_rec_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
            if (count($Ita_puntimenu_rec_seq) > 0) {
                $Ita_puntimenu_rec['pm_sequenza'] = $Ita_puntimenu_rec_seq['pm_sequenza'] + 1;
            } else {
                $Ita_puntimenu_rec['pm_sequenza'] = 1;
            }
        }
        $Ita_puntimenu_rec['pm_datamod'] = date("Ymd");

        if (!$this->updateRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec, '', 'pm_id')) {
            Out::msgInfo('Attenzione', 'Errore nell\'aggiornamento del punto menu.');
            return false;
        }
        
        // Modifico sequenza
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_puntimenu_rec['me_id'] . " ORDER BY pm_sequenza";
        $Ita_puntimenu_tab_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        $i = 10;
        foreach ($Ita_puntimenu_tab_seq as $key => $Ita_puntimenu_rec_seq) {
            $Ita_puntimenu_rec_seq['pm_sequenza'] = $i;
            $this->updateRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec_seq, '', 'pm_id');
            $i += 10;
        }
        
        return true;
    }

    private function cancellaPunto() {
        $id = $_POST[$this->nameForm . '_ita_puntimenu']['pm_id'];
        if ($this->haPuntiMenu($_POST[$this->nameForm . '_ita_puntimenu']['pm_voce'])) {
            Out::msgInfo('Attenzione', 'Questa voce ha dei punti menu.');
            return false;
        }
        if (!$this->deleteRecord($this->ITALSOFT_DB, 'ita_puntimenu', $id, '', 'pm_id')) {
            Out::msgInfo('Attenzione', 'Errore nella cancellazione del punto menu.');
            return false;
        }
        // Modifico sequenza
        $me_id = $_POST[$this->nameForm . '_ita_puntimenu']['me_id'];
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $me_id . " ORDER BY pm_sequenza";
        $Ita_puntimenu_tab_seq = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        $i = 10;
        foreach ($Ita_puntimenu_tab_seq as $key => $Ita_puntimenu_rec_seq) {
            $Ita_puntimenu_rec_seq['pm_sequenza'] = $i;
            $this->updateRecord($this->ITALSOFT_DB, 'ita_puntimenu', $Ita_puntimenu_rec_seq, '', 'pm_id');
            $i += 10;
        }
        return true;
    }
    
    private function spostaPunto($returnModel,$where='', $visData=false) {
        $sql="SELECT * FROM ita_menu";
        if ($where!='') $sql=$sql.' '.$where;
        $model='utiRicDiag';
        if ($visData==true) {
            $colonneNome=array(
                    "ID",
                    "Menu",
                    "Descrizione");
            $colonneModel=array(
                    array("name" => 'me_id',"width" =>50),
                    array("name" => 'me_menu',"width" =>100),
                    array("name" => 'me_descrizione',/*"formatter" => "eqdate",*/"width" =>400));
        } else {
            $colonneNome=array(
                    "Menu",
                    "Descrizione");
            $colonneModel=array(
                    array("name" => 'me_menu',"width" =>100),
                    array("name" => 'me_descrizione',"width" =>400));
        }
        $gridOptions=array(
                "Caption"   => 'Elenco Menu',
                "width"     => '500',
                "height"    => '470',
                "sortname"  => 'me_menu',
                "rowNum"    => '20',
                "rowList"   => '[]',
                "readerId"  => 'me_id',
                "colNames" =>$colonneNome,
                "colModel" =>$colonneModel,
                "dataSource" => array(
                        'sqlDB'=>'italsoft',
                        'dbSuffix'=>'',
                        'sqlQuery'=>$sql
                )
        );
        $_POST=array();
        $_POST['event']='openform';
        $_POST['gridOptions']=$gridOptions;
        $_POST['returnModel']=$returnModel;
        $_POST['returnEvent']='returnMenu';
        $_POST['retid']='devPuntiMenu_ita_menu[me_menu]';
        $_POST['returnKey']='retKey';
        itaLib::openForm($model);
        $appRoute=App::getPath('appRoute.'.substr($model,0,3));
        include_once App::getConf('modelBackEnd.php').'/'.$appRoute.'/'.$model.'.php';
        $model();
    }

    /**
     * Controlla se la voce ha punti menu
     * @param type $pm_voce voce per vedere se ha punti menu
     */
    private function haPuntiMenu($pm_voce) {
        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $pm_voce . "'";
        $Ita_menu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        if (count($Ita_menu_tab) == 0) {
            return false;
        }
        $me_id = $Ita_menu_tab[0]['me_id'];
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = '" . $me_id . "'";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        if (count($Ita_puntimenu_tab) > 0) {
            return true;
        }
        return false;
    }
    
    private function ritornoPrincipale() {
        $appoggio = $_POST[$this->nameForm.'_ita_puntimenu']['me_id'];
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['id_padre'] = $appoggio;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        $this->returnToParent();
    }

// INIZIO FUNZIONI DI UTULITA

    /**
     * Mostra gli elementi HTML indicati dagli ID degli array
     * @param Array $arr Array di ID di elmeneti HTML da mostrare
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Mostra($arr) {
        foreach ($arr as $value) {
            Out::show($this->nameForm . '_' . $value);
        }
    }

    /**
     * Nascondi gli elementi HTML indicati dagli ID degli array
     * @param Array $arr Array di ID di elmeneti HTML da nascondere
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Nascondi($arr) {
        foreach ($arr as $value) {
            Out::hide($this->nameForm . '_' . $value);
        }
    }

    /**
     * Assegna "" (stringa vuota) a tutti i campi indicati dagli ID passati
     * @param Array $arr Array di ID di elmeneti HTML di campi da impostare a ""
     * (non passare la parte "$this->nameForm.'_'")
     */
    function Pulisci($arr) {
        foreach ($arr as $value) {
            Out::valore($this->nameForm . '_' . $value, '');
        }
    }

}

?>
