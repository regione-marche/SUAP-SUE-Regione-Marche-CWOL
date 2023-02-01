<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDFRWclass
 *
 * @author utente
 */
//error_reporting(E_ALL);
require_once('./lib/html2pdf/config.inc.php');
require_once(HTML2PS_DIR.'pipeline.factory.class.php');
require_once(HTML2PS_DIR.'destination.file.class.php');

class itaDestinationFile extends Destination {

  function itaDestinationFile($filename, $link_text = null) {
    $this->Destination($filename);

  }

  function process($tmp_filename, $content_type) {
   copy($tmp_filename, $this->filename);

  }
}

class PDFRW {
    private $Html;
    private $ReportName;
    private $ReportTitle;
    private $ErrorCode;
    private $ErrorMessage;
    private $PDFName;
    
    function  __construct($ReportName='',$ReportTitle='',$Html='') {
        $this->Html=$Html;
        $this->ReportName=$ReportName;
        $this->ReportTitle=$ReportTitle;
        $this->PDFName='';
    }
    
    function setHtml($Html){
        $this->Html=$Html;
    }
    
    function setReportName($ReportName){
        $this->ReportName=$ReportName;
    }

    function setReportTitle($ReportTitle){
        $this->ReportTitle=$ReportTitle;
    }


    function getErrorCode(){
        return $this->ErrorCode;
    }

    function getPDFName(){
        return $this->PDFName;
    }

    
    function getErrorMessage(){
        return $this->ErrorMessage;
    }

    function createPDF(){
            parse_config_file(HTML2PS_DIR.'html2ps.config');

            global $g_config;
            $g_config = array(
                              'cssmedia'     => 'screen',
                              'renderimages' => true,
                              'renderforms'  => false,
                              'renderlinks'  => true,
                              'mode'         => 'html',
                              'debugbox'     => false,
                              'draw_page_border' => false
                              );

            $media = Media::predefined('A4');
            $media->set_landscape(false);
            $media->set_margins(array('left'   => 5,
                                      'right'  => 5,
                                      'top'    => 5,
                                      'bottom' => 5));
            $media->set_pixels(1024);

            global $g_px_scale;
            $g_px_scale = mm2pt($media->width() - $media->margins['left'] - $media->margins['right']) / $media->pixels;

            global $g_pt_scale;
            $g_pt_scale = $g_px_scale * 1.43;

            $pipeline = PipelineFactory::create_default_pipeline("","");
            $this->PDFName='./'.App::$utente->getkey('privPath').'/'.App::$utente->getKey('TOKEN')."-".$this->ReportName.".pdf";
            $pipeline->destination = new itaDestinationFile($this->PDFName);
            $status=$pipeline->process($this->Html, $media);
            if ($status != null) {
                return true;
            }else{
                return false;
            }
    }



    function createPDFOld(){
        // ricordati di inserire i controlli sui parametri di creazione
        $dompdf = new DOMPDF();
        $dompdf->load_html($this->Html);
        $dompdf->render();
        $pdfBin=$dompdf->output();
        $this->PDFName='./'.App::$utente->getkey('privPath').'/'.App::$utente->getKey('TOKEN')."-".$this->ReportName.".pdf";
        //
        // la path ./tmp/  dovrà essere parametrica
        //
        if (!$handle = fopen($this->getPDFName(), 'w')) {
            $this->ErrorCode=-1;
            $this->ErrorMessage="Non posso aprire il file temporaneo per la preparazione del PDF ($this->PDFName)";
            return false;
        }
        if (fwrite($handle, $pdfBin) == FALSE) {
            $this->ErrorCode=-2;
            $this->ErrorMessage="Impossibile scriver il file PDF($this->PDFName)";
            fclose($handle);
            return false;
        }
        fclose($handle);
        return true;
        
    }

    function showAsDialog($fl_verbose=true){
        if ( $this->createPDF()){
            $url="http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].App::$utente->getKey('privUrl')."/".App::$utente->getKey('TOKEN')."-".$this->ReportName.".pdf";
            Out::addContainer('desktopBody', 'pdfPrewiew-'.$this->ReportName.'_wrapper');
            Out::html('pdfPrewiew-'.$this->ReportName.'_wrapper','
                <div id="pdfPrewiew-'.$this->ReportName.'" class="ita-dialog {title:\''.$this->ReportName.'\',width:\'auto\',height:\'auto\',modal:true}">
                    <iframe
                         border="0" width="600px" height="700px" src="'.$url.'">
                    </iframe>
                </div>');
            return true;
        }else{
            if ($fl_verbose){
                Out::Alert('Errore:'.$this->getErrorCode().' in Report PDF. ('.$this->getErrorMessage().')');
            }
            return false;
        }
    }

    function showAsUrl($fl_verbose=true){
        if ( $this->createPDF()){
            $url="http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].App::$utente->getKey('privUrl')."/".App::$utente->getKey('TOKEN')."-".$this->ReportName.".pdf";
            Out::openDocument($url);
            return true;
        }else{
            if ($fl_verbose){
                Out::Alert('Errore:'.$this->getErrorCode().' in Report PDF. ('.$this->getErrorMessage().')');
            }
            return false;
        }
    }

}
?>
