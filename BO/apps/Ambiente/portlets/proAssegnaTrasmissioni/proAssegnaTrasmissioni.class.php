<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * PORTLET ASSEGNA TRASMISSIONI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    26.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class proAssegnaTrasmissioni extends envPortlet{
    public $id = __CLASS__;
    public $model =  'proElencoAssegnaTrasmissioni';
    public $description = "Assegna le trasmissioni rifiutate o scadute";
    public $isPublic=true;
    public $title="Assegna Trasmissioni";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>true
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
    
}

?>
