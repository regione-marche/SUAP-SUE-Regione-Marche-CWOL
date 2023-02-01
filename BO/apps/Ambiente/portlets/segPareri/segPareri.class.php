<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of menGes
 *
 * @author marco
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class segPareri extends envPortlet{
    public $id = __CLASS__;
    public $title="Richiesta Pareri";
    public $model="segElencoPareriPortlet";
    public $description = "Visualizza le proposte per le quali esprimere un parere";
    public $isPublic=true;
    public $config = array(
                    'iconPlus'=>true,
                    'iconEdit'=>true
    );


    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
