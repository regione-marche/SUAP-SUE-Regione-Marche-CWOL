<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class proProt extends envPortlet{
    public $id = __CLASS__;
    public $model =  'proElencoProtocolliPortlet';
    public $description = "Visualizza i Protocolli Accessibili";
    public $isPublic=true;
    public $openAsApp = true;
    public $title="Accesso ai protocolli";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>false
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
