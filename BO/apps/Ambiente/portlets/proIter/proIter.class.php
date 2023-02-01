<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class proIter extends envPortlet {

    public $id = __CLASS__;
    public $model = 'proElencoIterPortlet';
    public $description = "Visualizza i propri Documenti in carico";
    public $isPublic = true;
    public $openAsApp = true;
    public $title = "Documenti in carico";
    public $config = array(
        'iconPlus' => true,
        'iconEdit' => true
    );

    public function run() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }

}

?>
