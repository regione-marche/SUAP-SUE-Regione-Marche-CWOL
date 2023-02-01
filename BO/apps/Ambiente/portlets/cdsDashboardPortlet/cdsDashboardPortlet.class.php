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

class cdsDashboardPortlet extends envPortlet{
    public $id = __CLASS__;
    public $model =  'cdsDashboard';
    public $description = "Cruscotto";
    public $isPublic=true;
    public $title="Cruscotto";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}