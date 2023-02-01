<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class praFascicoli extends envPortlet{
    public $id = __CLASS__;
    public $model =  'praFascicoliPortlet';
    public $description = "Visualizza Fascicoli Utente";
    public $isPublic=true;
    public $title="Elenco Fascicoli Utente";
    public $config=array(
        //'iconPlus'=>true,
        'iconPlus'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
