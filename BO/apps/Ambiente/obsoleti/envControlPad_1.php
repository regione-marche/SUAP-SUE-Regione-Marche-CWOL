<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function envControlPad() {
    $envControlPad = new envControlPad();
    $envControlPad->parseEvent();
    return;
}

class envControlPad {

    public $ITALWEB_DB;
    public $nameForm = "envControlPad";

    function __construct() {
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'open':
                $this->getHtml();
                break;
            case 'sortStop':
                $order = $_POST['order'];
                $sortablecol = $_POST['id'];
                $Env_Profili_rec = $this->getConfig();
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
        }
    }

    public function getConfig() {
        $utente = App::$utente->getKey('idUtente');
        $sqlString = "SELECT * FROM ENV_PROFILI WHERE UTECOD=$utente AND ELEMENTO='ita-controlpad'";
        $Env_Profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlString, false);
        return $Env_Profili_rec;
    }

    public function getHtml() {
        $Env_Profili_rec = $this->getConfig();
        $configurazione = unserialize($Env_Profili_rec['CONFIG']);
        $sortablecol = $configurazione['sortablecol'];
        $html = "";
//        foreach ($sortablecol as $sortable_id => $value) {
//            $html .= "<div id=\"$sortable_id\" class=\"ita-sortable\">";
//            $html .= "</div>";
//        }

            $html .= "<div id=\"env_controlpad_1\" class=\"ita-sortable\">";
            $html .= "</div>";


        Out::html('ita-controlpad', $html);
        $html = "";
        foreach ($sortablecol as $sortable_id => $value) {
            if ($value['order']) {
                $portlet_arr = explode(',', $value['order']);
                foreach ($portlet_arr as $portlet_id) {
                    $portletFile = App::getAppFolder('env') . "/portlets/" . $portlet_id . "/" . $portlet_id . ".class.php";
                    if (file_exists($portletFile)) {
                        include_once $portletFile;
                        $currPortlet = new $portlet_id();
                        $html = $currPortlet->load();
                        Out::html("env_controlpad_1", $html, 'append');
                        $currPortlet->run();
                    }
                }
            }
        }
        Out::codice('$( "#ita-controlpad .ita-sortable" ).disableSelection();');
        Out::codice('$("#ita-controlpad .ita-sortable" ).sortable({
            connectWith: ".ita-sortable",
            update     : function(event, ui){
                            var helper_id=$(this).attr("id");
                            var Order = $(this).sortable(\'toArray\').toString();
                            itaGo(\'ItaCall\',\'\',{asyncCall:false,bloccaui:true,event:\'sortStop\',model:\'envControlPad\',id:helper_id,order:Order});
                            }
        });');
        Out::codice('$( "#ita-controlpad .ita-portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                                        .find( ".ita-portlet-header" )
                                        .addClass( "ui-widget-header ui-corner-all")
                                        .end()
                                        .find( ".ita-portlet-content" );');
        Out::codice('$( "#ita-controlpad .ita-portlet-header .ui-icon" ).click(function() {
                                    $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                                    $( this ).parents( ".ita-portlet:first" ).find( ".ita-portlet-content" ).toggle();
                                    });');
        Out::codice('$( "#ita-controlpad .ita-sortable" ).disableSelection();');



        //Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'portlet_1-content',model:'proStepIterPortlet'});");
    }

}

?>
