<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class timVisualizzaTimbrature extends envPortlet{
    public $id = __CLASS__;
    public $model =  'timVisTimbraturePortlet';
    public $description = "Visualizza Timbrature";
    public $isPublic=true;
    public $title="Visualizza Timbrature";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}

?>