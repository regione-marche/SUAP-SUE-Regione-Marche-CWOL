<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menGetinidata.php';
include_once './apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envDesktopMobile.php';

function menPersonalMobile() {
    $menPersonalMobile = new menPersonalMobile();
    $menPersonalMobile->parseEvent();
    return;
}

class menPersonalMobile extends itaModel {

    public $ITALWEB_DB;
    public $ITALSOFT_DB;
    public $nameForm = 'menPersonalMobile';
    public $gridRecenti = 'menPersonalMobile_gridRecenti';
    public $gridFrequenti = 'menPersonalMobile_gridFrequenti';
    public $gridPreferiti = 'menPersonalMobile_gridPreferiti';
    public $menLib;

    function __construct() {
        parent::__construct();
        if (!$this->ITALSOFT_DB) {
            try {
                $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        $this->menLib = new menLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openButton':
                $Men_Recenti_tab = $this->PreparaDati();

                $items = array(
                    array(
                        'class' => "{ model: '" . $this->nameForm . "', event: 'backSubmenu' }",
                        'label' => 'Indietro',
                        'icon' => 'carat-l'
                    ), array(
                        'label' => 'Recenti',
                        'divider' => true
                    )
                );

                foreach ($Men_Recenti_tab as $entry) {
                    $items[] = array(
                        'id' => $entry['MENU'] . '&' . $entry['PROG'],
                        'class' => "{ model: '" . $this->nameForm . "', event: 'openProgram' }",
                        'label' => $entry['DESCRIZIONE_PROG'],
                        'icon' => 'grid'
                    );
                }

                outputMobileMenu($items);
                break;

            case 'backSubmenu':
                baseMobileMenu();
                break;

            case 'openProgram':
                $data = explode('&', $_POST['listId']);
                $menu = $data[0];
                $prog = $data[1];
                $this->menLib->lanciaProgramma_ini($menu, $prog);
                baseMobileMenu();
                break;
        }
    }

    function PreparaDati() {
        $Men_Recenti_tab = menLib::loadRecent();
        foreach ($Men_Recenti_tab as $key => $Men_Recenti_rec) {
            $Ita_menu_rec = $this->menLib->GetIta_menu_ini($Men_Recenti_rec['MENU']);
            $Ita_puntimenu_rec = $this->menLib->GetIta_puntimenu_ini($Men_Recenti_rec['MENU'], $Men_Recenti_rec['PROG']);
            $Men_Recenti_tab[$key]['DESCRIZIONE_MENU'] = $Ita_menu_rec['me_descrizione'];
            $Men_Recenti_tab[$key]['DESCRIZIONE_PROG'] = $Ita_puntimenu_rec['pm_descrizione'];
        }

        return $Men_Recenti_tab;
    }

}

?>
