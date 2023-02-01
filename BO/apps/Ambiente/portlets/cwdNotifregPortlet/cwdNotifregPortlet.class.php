<?php

/**
 * Portlet per visualizzazione Notifiche elaborate e da elaborare
 *
 * @author Silvia Rivi il 04-06-2019 
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class cwdNotifregPortlet extends envPortlet{
    public $id = __CLASS__;
    public $model =  'cwdElencoNotifregPortlet';
    public $description = "Elenco Notifiche ricevute/da elaborare da ANPR";
    public $isPublic=true;
    public $title="Notifiche ricevute Anpr";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>false
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}

?>