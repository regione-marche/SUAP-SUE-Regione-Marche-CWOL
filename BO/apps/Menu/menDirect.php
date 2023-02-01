<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menLib.class.php';

function menDirect() {
    $menButton = new menDirect();
    $menButton->parseEvent();
    return;
}

class menDirect extends itaModel {

    public $ITW_DB;
    public $ITALSOFT_DB;
    public $nameForm = 'menDirect';
    public $menLib;

    function __construct() {
        parent::__construct();
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');
        $this->menLib = new menLib();
        $this->ITALSOFT_DB = $this->menLib->getItalsoft();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        if (isset($_POST['prog']) && $_POST['menu']) {
            $prog = $_POST['prog'];
            $menu = $_POST['menu'];
            if ($this->getMenu($menu,$prog)){
                $this->menLib->lanciaProgramma_ini($menu, $prog);                
            }else{
                Out::msgInfo("Attenzione", "Accesso non consentito.");
            }

        }
    }

    function getMenu($menuKey,$progKey) {
        $Ita_puntimenu_tab = $this->menLib->menuFiltrato_ini($menuKey);
        foreach ($Ita_puntimenu_tab as $key => $value) {
            if ($value['pm_voce'] == $progKey ){
                return true;
            }
        }
        
//        App::log($Ita_puntimenu_tab);
        return false;
    }

}

?>
