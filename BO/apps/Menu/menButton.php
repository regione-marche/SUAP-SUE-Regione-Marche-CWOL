<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menLib.class.php';

function menButton() {
    $menButton = new menButton();
    $menButton->parseEvent();
    return;
}

class menButton extends itaModel {

    public $ITW_DB;
    public $ITALSOFT_DB;
    public $nameForm = 'menButton';
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
        switch ($_POST['event']) {
            case 'openButton':
                ob_clean();
                $menuId = $_POST['menuId'];
                $menuKey = $_POST['rootMenu'];
                header("Content-Type: text/html; charset=ISO-8859-1\n");
                echo($this->getMenuContent($menuKey, true));
                exit;

            case 'onClick':
                if (isset($_POST['prog'])) {
                    $prog = $_POST['prog'];
                    $menu = $_POST['menu'];
                    $this->menLib->lanciaProgramma_ini($menu, $prog);
                    break;
                }

                if ($_POST['menu']) {
                    $menuKey = $_POST['menu'];
                    ob_clean();
                    header("Content-Type: text/html; charset=ISO-8859-1\n");
                    echo $this->getMenuContent($menuKey);
                    exit;
                }

                break;
            case 'searchProgram':
                $search = $_POST['msgData']['term'];
                $programmi = $this->menLib->searchProgrammi($search);
                
                $return = array();
                foreach($programmi as $descrizione=>$programma){
                    $return[] =  array('id'=>implode('|',$programma),'label'=>$descrizione,'value'=>$descrizione);
                }
                $return = json_encode($return);
                Out::codice("menButton_search_var.addCache('{$_POST['msgData']['term']}',".$return.");");
                break;
        }
    }

    function getMenuContent($menuKey, $rootContent = false, $maxRows = 17) {
        $Ita_menu_rec = $this->menLib->GetIta_menu_ini($menuKey, 'me_menu');
        $Ita_puntimenu_tab = $this->menLib->menuFiltrato_ini($menuKey); //$this->menLib->GetIta_puntimenu($Ita_menu_rec['me_id'], 'me_id');
        $menuContent = '';
        $tot = 0;

        if ($rootContent) {
            $menuContent .= '<ul class="col">';
            //$menuContent .= '<li class="ui-menu-item">Ricerca applicazioni</li>';
            $menuContent .= '<li class="ui-menu-item" style="height: 40px;">'
                          . '  <span class="ui-icon ui-icon-search" style="position: relative; margin-bottom: 1px; vertical-align: text-bottom;"></span><label for="menButton_search_field" style="margin-left: 5px">Ricerca applicazioni:</label><br>'
                          . '  <input class="ita-edit ui-widget-content ui-corner-all" style="width: 97%; margin: 3px 0 0 1%;" id="menButton_search_field">'
                          . '</li>';
            $menuContent .= '<li></li>';
            $tot += 2;
        }

        foreach ($Ita_puntimenu_tab as $key => $Ita_puntimenu_rec) {
            if ($rootContent && $tot && $tot % $maxRows == 0) {
                $menuContent.='</ul><ul class="col">';
            }

            $tot++;

            $gruppi = $this->menLib->getGruppi($this->menLib->utenteAttuale);
            if ($this->menLib->privilegiPuntoMenu($menuKey, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->menLib->defaultVis)) {
                $Ita_menu_rec_temp = $this->menLib->GetIta_menu_ini($Ita_puntimenu_rec['pm_voce'], 'me_menu');
                if ($Ita_menu_rec_temp) {
                    $menuContent .= '<li><a href="' . $Ita_puntimenu_rec['pm_voce'] . '">' . $Ita_puntimenu_rec['pm_sequenza'] . '. ' . $Ita_puntimenu_rec['pm_descrizione'] . '</a><ul></ul></li>';
                } else {
                    $menuContent .= '<li><a href="#menButton?menu=' . $menuKey . '&prog=' . $Ita_puntimenu_rec['pm_voce'] . '">' . $Ita_puntimenu_rec['pm_sequenza'] . '. ' . $Ita_puntimenu_rec['pm_descrizione'] . '</a></li>';
                }
            }
        }

        if ($rootContent) {
            $menuContent.='</ul>';
            $menuContent.='<script>'
                    . 'itaGetScript("","menuAutocomplete.js");'
                    . 'var menButton_search_var = new menuAutocomplete("menButton_search_field");'
                    . '</script>';
        }
        return $menuContent;
    }

}

?>
