<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rasRassegna
 *
 * @author marco
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class rasRassegna extends envPortlet{
    public $id = __CLASS__;
    //public $model =  'rasRassegnaPortlet';
    public $model =  'rasPad';
    public $description = "Visualizza la Rassegna Stampa";
    public $openAsApp = true;
    public $isPublic=true;
    public $title="Rassegna Stampa";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>false
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}
