<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class praAssegnazioni extends envPortlet{
    public $id = __CLASS__;
    public $model =  'praAssegnazioniPortlet';
    public $description = "Visualizza i passi da prendere in carico dell'utente loggato";
    public $isPublic=true;
    public $title="Elenco Passi da prendere in carico";
    public $config=array(
        //'iconPlus'=>true,
        'iconPlus'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
