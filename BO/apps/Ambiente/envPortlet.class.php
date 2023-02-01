<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of envPortlet
 *
 * @author michele
 */
class envPortlet extends itaModel {
    public $id;
    public $id_content;
    public $id_header;
    public $id_wait;
    public $model='';
    public $description;
    public $isPublic=true;
    public $title="Portlet";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>false
    );


    public function load() {
        $this->id_header="$this->id-header";
        $this->id_content="$this->id-content";
        $this->id_wait="$this->id-wait";
        if ($this->config['iconPlus'] == true ){
            $htmlIconPlus="<span id=\"".$this->id."_Plus\" class=\"ita-portlet-icon ita-portlet-plus ui-icon ui-icon-minusthick\"></span>";
        }
        if ($this->config['iconEdit'] == true ){
            $htmlIconEdit="<span id=\"".$this->id."_Edit\" class=\"ita-portlet-icon ita-portlet-edit ui-icon ui-icon-pencil\"></span>";
        }
        if ($this->config['iconDelete'] !== false ){
            $htmlIconDelete="<span id=\"".$this->id."_Delete\" class=\"ita-portlet-icon ita-portlet-trash ui-icon ui-icon-trash\"></span>";
        }
        
        $html = "<div id=\"$this->id\" class=\"ita-portlet\">
                            <div id=\"$this->id_header\" class=\"ita-portlet-header\">$this->title
                                <div style=\"float:right;\">$htmlIconPlus$htmlIconEdit$htmlIconDelete</div>
                            </div>
                            <div id=\"$this->id_content\" class=\"ita-portlet-content\"><img id=\"$this->id_wait\" src=\"public/css/images/wait.gif\"></div>
                        </div>";
        return $html;
    }

    public function parseEvent() {
        parent::parseEvent();
        
    }

    public function runApp() {
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportletapp',context:'$this->id',model:'$this->model'});");
    }
        



    //    }

}

?>
