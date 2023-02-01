<?php

include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPortlet.class.php';

function envControlPad() {
    $envControlPad = new envControlPad();
    $envControlPad->parseEvent();
    return;
}

class envControlPad extends itaModel {

    public $envLibPortlet;
    public $ITALWEB_DB;
    public $nameForm = "envControlPad";
    public $portlet_id;

    function __construct() {
        try {
            $this->envLibPortlet = new envLibPortlet();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $this->portlet_id = App::$utente->getKey($this->nameForm . '_portlet_id');
    }

    function __destruct() {
        App::$utente->setKey($this->nameForm . '_portlet_id', $this->portlet_id);
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'open':
                $this->creaContenitore();
                $this->caricaContenitore();
                break;

            case 'sortStop':
                $order = $_POST['order'];
                $sortablecol = $_POST['id'];

                $Env_Profili_rec = $this->envLibPortlet->getConfigRecord();

                if ($Env_Profili_rec) {
                    $configurazione = unserialize($Env_Profili_rec['CONFIG']);
                    $configurazione['sortablecol'][$sortablecol]['order'] = $order;
                    $Env_Profili_rec['CONFIG'] = serialize($configurazione);
                    ItaDB::DBUpdate($this->ITALWEB_DB, "ENV_PROFILI", "ROWID", $Env_Profili_rec);
                } else {
                    $Env_Profili_rec['UTECOD'] = $utente;
                    $Env_Profili_rec['ELEMENTO'] = "ita-controlpad";
                    $configurazione['sortablecol'][$sortablecol]['order'] = $order;
                    $Env_Profili_rec['CONFIG'] = serialize($configurazione);
                    ItaDB::DBInsert($this->ITALWEB_DB, "ENV_PROFILI", "ROWID", $Env_Profili_rec);
                }
                break;

            case 'iconTrashClick':
                $this->portlet_id = $_POST['id'];
                Out::msgQuestion("Rimozione", "Confermi la rimozione del portlet?", array(
                    'F8-Annulla' => array(
                        'id' => $this->nameForm . '_AnnullaTrash', 'model' => $this->nameForm, 'shortCut' => "f8"
                    ),
                    'F5-Conferma' => array(
                        'id' => $this->nameForm . '_ConfermaTrash', 'model' => $this->nameForm, 'shortCut' => "f5"
                    )
                        ), 'auto', 'auto', 'true', false, true, false, "ItaCall");
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaTrash':
                        $portlet_id = $this->portlet_id;
                        $this->envLibPortlet->rimuoviPortlet($portlet_id);
                        break;
                }
                break;
        }
    }

//    public function getConfig() {
//        $utente = App::$utente->getKey('idUtente');
//        $sqlString = "SELECT * FROM ENV_PROFILI WHERE UTECOD=$utente AND ELEMENTO='ita-controlpad'";
//        $Env_Profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlString, false);
//        return $Env_Profili_rec;
//    }

    public function creaContenitore() {
        $html = "";
        $html .= "<div id=\"env_controlpad_1\" class=\"ita-sortable\" style=\"width: 100%; padding-bottom: 25px;\">";
        $html .= "</div>";

        Out::html('ita-controlpad', $html);
        Out::codice('$("#ita-controlpad .ita-sortable" ).sortable({
            handle: ".ita-portlet-header",
            update     : function(event, ui){
                            var helper_id=$(this).attr("id");
                            var Order = $(this).sortable(\'toArray\').toString();
                            itaGo(\'ItaCall\',\'\',{asyncCall:false,bloccaui:true,event:\'sortStop\',model:\'envControlPad\',id:helper_id,order:Order});
                            }
        });');
    }

    public function caricaContenitore() {
        $Env_Profili_rec = $this->envLibPortlet->getConfigRecord();
        $configurazione = unserialize($Env_Profili_rec['CONFIG']);
        $sortablecol = $configurazione['sortablecol'];

        $check_doubles = array();

        foreach ($sortablecol as $sortable_id => $value) {
            if ($value['order']) {
                $portlet_arr = explode(',', $value['order']);
                foreach ($portlet_arr as $portlet_id) {
                    if (!isset($check_doubles[$portlet_id])) {
                        $check_doubles[$portlet_id] = true;

                        switch ($sortable_id) {
                            default:
                            case 'env_controlpad_1':
                                $this->envLibPortlet->caricaPortlet($portlet_id, true);
                                break;

                            case 'env_controlpad_2':
                                $this->envLibPortlet->caricaPortlet($portlet_id, true, true);
                                break;
                        }
                    }
                }
            }
        }
    }

}

?>