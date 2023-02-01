<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class envCalendar extends envPortlet {

    public $id = __CLASS__;
    public $model = 'envFullCalendar';
    public $description = "Gestione Calendario";
    public $isPublic = true;
    public $openAsApp = true;
    public $title = "Calendario";
    public $config = array(
        'iconPlus' => true,
        'iconEdit' => false
    );

    public function run() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }

}

?>
