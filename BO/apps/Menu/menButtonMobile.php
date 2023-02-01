<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envDesktopMobile.php';

function menButtonMobile() {
    $menButtonMobile = new menButtonMobile();
    $menButtonMobile->parseEvent();
    return;
}

class menButtonMobile extends itaModel {

    public $ITW_DB;
    public $ITALSOFT_DB;
    public $nameForm = 'menButtonMobile';
    public $menLib;
    public $root = "MOB_MEN";
    public $percorso;

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
        $this->percorso = App::$utente->getKey($this->nameForm . '_percorso');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_percorso', $this->percorso);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openButton':
                $this->percorso = array('MOB_MEN');
                $this->disegnaMenu();
                break;

            case 'openProgram':
                $prog = $_POST['listId'];
                $menu = $this->percorso[count($this->percorso) - 1];
                $this->menLib->lanciaProgramma_ini($menu, $prog);
                baseMobileMenu();
                break;

            case 'backSubmenu':
                if (count($this->percorso) < 2) {
                    baseMobileMenu();
                    break;
                } else {
                    /* Rimuovo l'attuale e il penultimo che corrisponde al parent,
                     * che verrà riaggiunto dentro il case 'openSubmenu' automaticamente */
                    array_pop($this->percorso);
                    array_pop($this->percorso);
                }

            case 'openSubmenu':
                array_push($this->percorso, $_POST['listId']);
                $this->disegnaMenu();
                break;
        }
    }

    function disegnaMenu() {
        outputMobileMenu($this->getMenuContentSide($this->percorso[count($this->percorso) - 1]));
    }

    function getMenuContentSide($menuKey) {
        $Ita_menu_rec = $this->menLib->GetIta_menu_ini($menuKey);
        $Ita_puntimenu_tab = $this->menLib->menuFiltrato_ini($menuKey);
        $Items = array();

        $Items[] = array(
            'id' => $this->percorso[count($this->percorso) - 2],
            'class' => "{ model: '" . $this->nameForm . "', event: 'backSubmenu' }",
            'label' => 'Indietro',
            'icon' => 'carat-l'
        );

        $Items[] = array(
            'label' => $Ita_menu_rec['me_descrizione'],
            'divider' => true
        );

        foreach ($Ita_puntimenu_tab as $Ita_puntimenu_rec) {
            $gruppi = $this->menLib->getGruppi($this->menLib->utenteAttuale);
            if ($this->menLib->privilegiPuntoMenu($menuKey, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->menLib->defaultVis)) {
                $is_menu = $this->menLib->GetIta_menu_ini($Ita_puntimenu_rec['pm_voce']);

                $Items[] = array(
                    'id' => $Ita_puntimenu_rec['pm_voce'],
                    'class' => "{ model: '" . $this->nameForm . "', event: '" . ( $is_menu ? 'openSubmenu' : 'openProgram' ) . "' }",
                    'label' => $Ita_puntimenu_rec['pm_sequenza'] . '. ' . $Ita_puntimenu_rec['pm_descrizione'],
                    'icon' => $is_menu ? 'carat-r' : 'grid'
                );
            }
        }
        return $Items;
    }

}

?>