<?php

/**
 *  Explorer per il menu
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Michele Accattoli
 * @author     Michele Moscioni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPortlet.class.php';

function menDeskConfig() {
    $menDeskConfig = new menDeskConfig();
    $menDeskConfig->parseEvent();
    return;
}

class menDeskConfig extends itaModel {

    //put your code here

    public $envLibPortlet;
    public $menLib;
    public $nameForm = "menDeskConfig";
    public $ITALWEB_DB;
    public $lista_portlet;
    public $menuPortlet = 'PT_MEN';
    public $rowid_aggiungi = false;

    function __construct() {
        parent::__construct();
        $this->menLib = new menLib();
        $this->envLibPortlet = new envLibPortlet();

        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $this->lista_portlet = App::$utente->getKey($this->nameForm . '_lista_portlet');
        $this->rowid_aggiungi = App::$utente->getKey($this->nameForm . '_rowid_aggiungi');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_lista_portlet', $this->lista_portlet);
        App::$utente->setKey($this->nameForm . '_rowid_aggiungi', $this->rowid_aggiungi);
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenForm();
                break;

            case 'cellSelect':
                if ($this->lista_portlet[$_POST['rowid']]['portlet_active'] == false) {
                    $portlet_id = $this->lista_portlet[$_POST['rowid']]['portlet_id'];
                    $portletFile = App::getAppFolder('env') . "/portlets/" . $portlet_id . "/" . $portlet_id . ".class.php";

                    if (file_exists($portletFile)) {
                        include_once $portletFile;
                        $currPortlet = new $portlet_id();
                        if (isset($currPortlet->openAsApp) && $currPortlet->openAsApp === true) {
                            $this->rowid_aggiungi = $_POST['rowid'];

                            Out::msgQuestion("Aggiungi Portlet", "Selezionare il metodo di apertura del model<br><br><small>App: visualizza il portlet in una scheda apposita<br>Portlet: visualizza il portlet nella scheda home</small>", array(
                                'Portlet' => array(
                                    'id' => $this->nameForm . '_aggiungiPortlet',
                                    'model' => $this->nameForm
                                ),
                                'App' => array(
                                    'id' => $this->nameForm . '_aggiungiApp',
                                    'model' => $this->nameForm
                                )
                            ));

                            break;
                        }
                    }
                }

                $this->aggiungi($_POST['rowid']);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_aggiungiPortlet':
                        $portlet_id = $this->lista_portlet[$this->rowid_aggiungi]['portlet_id'];
                        $this->envLibPortlet->caricaPortlet($portlet_id, false);
                        $this->rowid_aggiungi = false;
                        $this->OpenForm();
                        break;

                    case $this->nameForm . '_aggiungiApp':
                        $portlet_id = $this->lista_portlet[$this->rowid_aggiungi]['portlet_id'];
                        $this->envLibPortlet->caricaPortlet($portlet_id, false, true);
                        $this->rowid_aggiungi = false;
                        $this->OpenForm();
                        break;
                }
                break;
        }
    }

    public function OpenForm() {
        // legge i file
//        $basePath = App::getPath('general.itaPortlets');
//        App::log($basePath);
//        $lista = scandir($basePath);
//        $portlet_totali = array();
//        $i = 0;
//        foreach ($lista as $key => $value) {
//            if ($value === '.' || $value === '..') {
//                continue;
//            }
//            if (is_dir($basePath . '/' . $value)) {
//                $portlet_totali[$i] = $value;
//                $i++;
//            }
//        }
        //
        //Leggo i portlet attivi
        //
//        $Env_profili_rec = $this->envLibPortlet->getConfigRecord();
//        $Controlpad_config = unserialize($Env_profili_rec['CONFIG']);
//
//        $portlet_attivi = explode(',', $Controlpad_config['sortablecol']['env_controlpad_1']['order']);
//
//        if (isset($Controlpad_config['sortablecol']['env_controlpad_2']['order'])) {
//            $portlet_attivi_app = explode(',', $Controlpad_config['sortablecol']['env_controlpad_2']['order']);
//            $portlet_attivi = array_merge($portlet_attivi, $portlet_attivi_app);
//        }

        $portlet_attivi = $this->envLibPortlet->getPortletAttivi();
        
        //
        // Leggo i premessi
        //
        $Ita_puntimenu = $this->menLib->GetIta_puntimenu_ini($this->menuPortlet);
        $gruppi = $this->menLib->getGruppi($this->menLib->utenteAttuale);

        //
        // Creo La lista dei portlet da visualizzare 
        //
        $portlet_public = array();
        $i = 0;
        foreach ($Ita_puntimenu as $Ita_puntimenu_rec) {
            $portlet_id = $Ita_puntimenu_rec['pm_model'];
            $portlet_descrizione = $Ita_puntimenu_rec['pm_descrizione'];
            $privilegio = $this->menLib->privilegiPuntoMenu($this->menuPortlet, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->menLib->defaultVis);
            if (!$privilegio) {
                continue;
            }
            if (!in_array($portlet_id, $portlet_attivi)) {
                $portlet_public[$i]['portlet_id'] = $portlet_id;
                $portlet_public[$i]['portlet_active'] = false;
                $portlet_public[$i]['portlet_description'] = $portlet_descrizione;
                $portlet_public[$i]['portlet_aggiungi'] = '<div style ="width:17px;align:center;margin:2px;padding:2px;" class="ui-widget-content ui-corner-all"><span style="align:center;" class="ita-icon ita-icon-add-16x16"></span></div>';
            } else {
                $portlet_public[$i]['portlet_id'] = $portlet_id;
                $portlet_public[$i]['portlet_active'] = true;
                $portlet_public[$i]['portlet_description'] = $portlet_descrizione;
                $portlet_public[$i]['portlet_aggiungi'] = '<div style ="width:17px;align:center;margin:2px;padding:2px;" class="ui-widget-content ui-corner-all"><span style="align:center;" class="ita-icon ita-icon-delete-16x16"></span></div>';
            }
            $i++;
        }
        $this->lista_portlet = $portlet_public;


        //
        // Visualizzo la tabella
        //
        $arr = array('arrayTable' => $this->lista_portlet,
            'rowIndex' => 'portlet_id');
        $tableId = $this->nameForm . '_gridPortlet';
        $griglia = new TableView($tableId, $arr);
        $griglia->setPageNum(1);
        $griglia->setPageRows('1000');
        TableView::enableEvents($tableId);
        TableView::clearGrid($tableId);
        $griglia->getDataPage('json');
    }

    public function aggiungi($rowid) {
        $portlet_id = $this->lista_portlet[$rowid]['portlet_id'];
        $portlet_active = $this->lista_portlet[$rowid]['portlet_active'];

        if ($portlet_active == false) {
            $this->envLibPortlet->caricaPortlet($portlet_id, false);
        } else {
            $this->envLibPortlet->rimuoviPortlet($portlet_id);
        }

        //
        // Aggiorno griglia
        // 
        $this->OpenForm();
    }

}

?>