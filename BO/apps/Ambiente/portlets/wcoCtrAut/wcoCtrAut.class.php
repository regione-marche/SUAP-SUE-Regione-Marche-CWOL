<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wcoControllaAut
 *
 * @author andrea
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class wcoCtrAut extends envPortlet{
    public $id = __CLASS__;
    public $model =  'wcoControllaAut';
    public $description = "Elenco Autorizzazioni";
    public $isPublic=true;
    public $title="Elenco Autorizzazioni";
    public $config=array(
        'iconPlus'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
