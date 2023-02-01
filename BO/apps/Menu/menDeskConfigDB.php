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
include_once './apps/Menu/menLib.class.php';

function menDeskConfig() {
    $menDeskConfig = new menDeskConfig();
    $menDeskConfig->parseEvent();
    return;
}

class menDeskConfig extends itaModel {

    //put your code here

    public $menLib;
    public $nameForm = "menDeskConfig";
    public $ITALWEB_DB;
    public $lista_portlet;
    public $menuPortlet = 'PT_MEN';

    function __construct() {
        parent::__construct();
        $this->menLib = new menLib();
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->lista_portlet = App::$utente->getKey($this->nameForm . '_lista_portlet');
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_lista_portlet', $this->lista_portlet);
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
                $this->aggiungi($_POST['rowid']);
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
        $sql = "SELECT * FROM ENV_PROFILI WHERE UTECOD = " . $this->menLib->utenteAttuale . " AND ELEMENTO = 'ita-controlpad'";
        $Env_profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $Controlpad_config = unserialize($Env_profili_rec['CONFIG']);
        $portlet_attivi = explode(',', $Controlpad_config['sortablecol']['env_controlpad_1']['order']);

        //
        // Leggo i premessi
        //
        $Ita_menu_rec = $this->menLib->GetIta_menu($this->menuPortlet);
        $me_id = $Ita_menu_rec['me_id'];
        $Ita_puntimenu = $this->menLib->GetIta_puntimenu($me_id);
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
        //
        // Carico la configurazione dell'elemento ita-controlpad
        //
        $sql = "SELECT * FROM ENV_PROFILI WHERE UTECOD = " . $this->menLib->utenteAttuale . " AND ELEMENTO = 'ita-controlpad'";
        $Env_profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
//        App::log($Env_profili_rec);

        if (!$Env_profili_rec) {
            $portlet_attivi['sortablecol']['env_controlpad_1']['order'] = "";
            $Env_profili_rec['UTECOD'] = $this->menLib->utenteAttuale;
            $Env_profili_rec['ELEMENTO'] = "ita-controlpad";
            $Env_profili_rec['CONFIG'] = serialize($portlet_attivi);
            $res = ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_PROFILI', 'ROWID', $Env_profili_rec);
            $Env_profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        }
//
// Aggiorno il config
//
        $Controlpad_config = unserialize($Env_profili_rec['CONFIG']);
        $portlet_attivi = explode(',', $Controlpad_config['sortablecol']['env_controlpad_1']['order']);
        if ($portlet_active == false) {
//
// Aggiungo un portlet
//
            $portlet_attivi[] = $portlet_id;

            $this->visualizzaPortlet($portlet_id);
        } else {
//
// Elimino  un portlet
//
            $portlet_key = array_search($portlet_id, $portlet_attivi);
            if ($portlet_key !== false) {
                unset($portlet_attivi[$portlet_key]);
            }
            Out::delContainer($portlet_id);
        }
        $Controlpad_config['sortablecol']['env_controlpad_1']['order'] = implode(",", $portlet_attivi);
        $Env_profili_rec['CONFIG'] = serialize($Controlpad_config);
        $res = ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_PROFILI', 'ROWID', $Env_profili_rec);

//
// Aggiorno griglia
// 
        $this->OpenForm();
    }

    private function visualizzaPortlet($portlet_id) {
        $portletFile = App::getAppFolder('env') . "/portlets/" . $portlet_id . "/" . $portlet_id . ".class.php";
        if (file_exists($portletFile)) {
            include_once $portletFile;
            $currPortlet = new $portlet_id();
            $html = $currPortlet->load();
            Out::html("env_controlpad_1", $html, 'append');
            $currPortlet->run();
            Out::codice('$("#' . $portlet_id . '").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                                        .find( ".ita-portlet-header" )
                                        .addClass( "ui-widget-header ui-corner-all")
                                        .end()
                                        .find( ".ita-portlet-content" );');

            Out::codice('$("#ita-controlpad").find("#' . $portlet_id . '").find(".ita-portlet-header .ita-portlet-plus").click(function() {
                                    $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                                    $( this ).parents( ".ita-portlet:first" ).find( ".ita-portlet-content" ).toggle();
                                    });');

            Out::codice('$("#ita-controlpad").find("#' . $portlet_id . '").find(".ita-portlet-header .ita-portlet-trash").click(function() {
                                    var helper_id="' . $portlet_id . '";
                                    itaGo(\'ItaCall\',\'\',{asyncCall:false,bloccaui:true,event:\'iconTrashClick\',model:\'envControlPad\',id:helper_id});
                                    });');
        }
    }

}

?>
