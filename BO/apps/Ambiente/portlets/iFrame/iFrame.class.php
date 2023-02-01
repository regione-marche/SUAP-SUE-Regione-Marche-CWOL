<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class iFrame extends envPortlet 
{
    public $id = __CLASS__;
    public $model = 'rasPad';
    public $description = "Visualizza il tuo sito preferito";
    public $isPublic=true;
    public $title="IFrame";
        
    public function run() {
        $portlet_id_content=$this->id."-content";
        $portlet_id_content_iframe=$this->id."-content-iframe"        ;
        Out::html($portlet_id_content,'<iframe id="'.$portlet_id_content_iframe.'" class="ita-iframe" frameborder="0" width="97%" height="400px" src=""></iframe>');        
    }
}

?>
