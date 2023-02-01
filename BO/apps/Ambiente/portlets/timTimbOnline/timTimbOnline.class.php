<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class timTimbOnline extends envPortlet{
    public $id = __CLASS__;
    public $model =  'timTimbOnlinePortlet';
    public $description = "Timbrature Online";
    public $isPublic=true;
    public $title="Timbrature Online";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}

?>