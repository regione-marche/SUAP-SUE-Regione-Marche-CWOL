<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class timControlloOrologi extends envPortlet {

    public $id = __CLASS__;
    public $model = 'timControlloOrologiPortlet';
    public $description = "Controllo orologi";
    public $isPublic = true;
    public $title = "Controllo orologi";
    public $config = array(
        'iconPlus' => true,
        'iconEdit' => true
    );

    public function run() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }

}

?>