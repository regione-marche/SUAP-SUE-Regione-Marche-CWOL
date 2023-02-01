<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class cwbLauncherPortlet extends envPortlet {

    public $id = __CLASS__;
    public $model = 'cwbLauncher';
    public $description = "Launcher";
    public $isPublic = true;
    public $openAsApp = true;
    public $title = "Launcher";
    public $config = array(
        'iconPlus' => true,
        'iconEdit' => false
    );

    public function run() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',nameform:'$this->model',model:'$this->model'});");
    }

}

?>
