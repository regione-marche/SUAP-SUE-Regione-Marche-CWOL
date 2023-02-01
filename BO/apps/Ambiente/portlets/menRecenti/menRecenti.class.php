<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class menRecenti extends envPortlet{
    public $id = __CLASS__;
    public $description = "Recenti...";
    public $isPublic=false;
    public $title="Utimi Utilizzati";
    public $config=array('model'=>'menRecenti');
    
    //put your code here
    public function load() {
        $portlet_id=__CLASS__;
        $html = "<div id=\"$portlet_id\" class=\"ita-portlet\">
                            <div id=\"$portlet_id-header\" class=\"ita-portlet-header\">$this->title</div>
                            <div id=\"$portlet_id-content\" class=\"ita-portlet-content\"></div>
                        </div>";
        return $html;
    }
    
    public function run(){
        $portlet_id=__CLASS__;        
        $model=$this->config['model'];
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$portlet_id-content',model:'$model'});"); 
    }
    
    public function refresh(){
        $portlet_id=__CLASS__;        
        $model=$this->config['model'];
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'refresh',context:'$portlet_id-content',model:'$model'});"); 
    }
    

}

?>
