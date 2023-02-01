<?php
/* * 
 *
 * PORTLET PROTOCOLLI DA FIRMARE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    02.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Ambiente/envPortlet.class.php';

class proProtDaFirm extends envPortlet{
    public $id = __CLASS__;
    public $model =  'proElencoProtocolliDaFirmPortlet';
    public $description = "Visualizza i Protocolli con Documenti da Firmare";
    public $isPublic=true;
    public $title="Protocolli con Documenti da Firmare";
    public $config=array(
        'iconPlus'=>true,
        'iconEdit'=>false
    );

    public function run(){
        Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openportlet',context:'$this->id',model:'$this->model'});");
    }
}

?>
