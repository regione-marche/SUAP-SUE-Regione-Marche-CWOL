<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class praRich extends envPortlet{
    public $id = __CLASS__;
    public $model =  'praElencoPratichePortlet';
    public $description = "Visualizza le proprie Richieste Online";
    public $isPublic=true;
    public $title="Procedimenti on-line in attesa di acquisizione";
    //public $title="Le mie Richieste";
    public $config=array(
        //'iconPlus'=>true,
        'iconPlus'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
