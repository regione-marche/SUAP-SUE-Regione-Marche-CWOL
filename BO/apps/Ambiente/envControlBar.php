<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function envControlBar() {
    $envControlBar = new envControlBar();
    $envControlBar->parseEvent();
    return;
}

class envControlBar {

    public $ITALWEB_DB;
    public $nameForm = "envControlBar";

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
        }
    }

    public function getHtml() {
        $portlet_id = "menPersonal";
        $controlbar_id = "ita-controlbar";
        $portletFile = App::getAppFolder('env') . "/portlets/" . $portlet_id . "/" . $portlet_id . ".class.php";
        if (file_exists($portletFile)) {
            include_once $portletFile;
            $currPortlet = new $portlet_id();
            $html = $currPortlet->load();
            Out::html($controlbar_id, $html, 'append');
            Out::codice('$("#'.$controlbar_id.' .ita-portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                                        .find( ".ita-portlet-header" )
                                        .addClass( "ui-widget-header ui-corner-all").prepend( "<span class=\'ui-icon ui-icon-minusthick\'></span>")
                                        .end()
                                    .find( ".ita-portlet-content" );');
            Out::codice('$("#'.$controlbar_id.' .ita-portlet-header .ui-icon" ).click(function() {
                                    $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                                    $( this ).parents( ".ita-portlet:first" ).find( ".ita-portlet-content" ).toggle();
                                    });');


            $currPortlet->run();
        }



//        $html = " <div id=\"menRecenti\" class=\"ita-portlet\" style=\"width:95%\">
//                                        <div class=\"ita-portlet-header\">Recenti</div>
//                                        <div class=\"ita-portlet-content\">..........</div>
//                                    </div>";
//
//        Out::html('ita-controlbar', $html);
//        Out::codice('$( "#ita-controlbar .ita-portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
//                                        .find( ".ita-portlet-header" )
//                                        .addClass( "ui-widget-header ui-corner-all").prepend( "<span class=\'ui-icon ui-icon-minusthick\'></span>")
//                                        .end()
//                                    .find( ".ita-portlet-content" );');
//        Out::codice('$( "#ita-controlbar .ita-portlet-header .ui-icon" ).click(function() {
//                                    $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
//                                    $( this ).parents( ".ita-portlet:first" ).find( ".ita-portlet-content" ).toggle();
//                                    });');
    }

}

?>
