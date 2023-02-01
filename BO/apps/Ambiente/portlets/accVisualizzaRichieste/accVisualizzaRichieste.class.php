<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class accVisualizzaRichieste extends envPortlet {

    public $id = __CLASS__;
    public $model = 'accVisualizzaRichieste';
    public $description = "Gestione Richieste Accesso Utenti";
    public $isPublic = false;
    public $title = "Gestione Richieste Accesso Utenti";
    public $config = array(
        'iconPlus' => true,
        'iconEdit' => false
    );

    public function run() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }

}

?>
