<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of proArrivi
 *
 * @author 
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class cdsScadenzaPuntiPortlet extends envPortlet{
    public $id = __CLASS__;
    public $model =  'cdsScadenzaPunti';
    public $description = "Scadenza Punti";
    public $isPublic=true;
    public $title="Scadenza Punti";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}

?>