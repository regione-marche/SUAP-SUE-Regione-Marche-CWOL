<?php
class itaChartsOptions{
    const ITA_CHARTS_TOP = 'top';
    const ITA_CHARTS_BOTTOM = 'bottom';
    const ITA_CHARTS_LEFT = 'left';
    const ITA_CHARTS_RIGHT = 'right';
    const ITA_CHARTS_FONT_NORMAL = 'normal';
    const ITA_CHARTS_FONT_BOLD = 'bold';
    const ITA_CHARTS_FONT_ITALIC = 'italic';
    const ITA_CHARTS_AXIS_LINEAR = 'linear';
    const ITA_CHARTS_AXIS_LOGARITHMIC = 'logarithmic';
    
    
    private $responsive;
    private $layout;
    private $legend;
    private $title;
    private $tooltips;
    private $scales;
    private $customOptions;
    
    public function __construct() {
        $this->setResponsive();
        $this->setLegendDisplay();
        $this->setLegendLabel();
        $this->setLegendPosition();
        $this->setPadding();
        $this->setResponsive();
        $this->setTitleDisplay();
        $this->setTitleOptions();
        $this->setTitlePosition();
        $this->setToolTipDisplay();
        $this->customOptions = null;
    }
    
    /**
     * Definisce se il grafico viene ridimensionato al resize dell'elemento padre
     * @param boolean $responsive
     */
    public function setResponsive($responsive=true){
        $this->responsive = $responsive;
    }
    
    /**
     * Definisce il padding, in pixel, all'interno del grafico
     * @param int $left
     * @param int $right
     * @param int $top
     * @param int $bottom
     */
    public function setPadding($left=0,$right=0,$top=0,$bottom=0){
        $this->layout = new stdClass();
        $this->layout->padding = new stdClass();
        $this->layout->padding->left = $left;
        $this->layout->padding->right = $right;
        $this->layout->padding->top = $top;
        $this->layout->padding->bottom = $bottom;
    }
    
    /**
     * Definisce se mostrare o meno la legenda
     * @param boolean $display
     */
    public function setLegendDisplay($display=true){
        if(!isSet($this->legend)) $this->legend = new stdClass();
        
        $this->legend->display = $display;
    }
    
    /**
     * Definisce dove mettere la legenda
     * @param string $position: ITA_CHARTS_TOP,ITA_CHARTS_BOTTOM,ITA_CHARTS_LEFT,ITA_CHARTS_RIGHT
     * @throws ItaException: se la posizione fornita non è valida.
     */
    public function setLegendPosition($position=self::ITA_CHARTS_TOP){
        if($position != self::ITA_CHARTS_TOP && $position != self::ITA_CHARTS_BOTTOM && $position != self::ITA_CHARTS_LEFT && $position != self::ITA_CHARTS_RIGHT){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "La posizione data non è valida");
        }
        if(!isSet($this->legend)) $this->legend = new stdClass();
        
        $this->legend->position = $position;
    }
    
    /**
     * Definisce alcuni parametri della legenda
     * @param int $boxWidth: Larghezza dei quadratini della legenda
     * @param int $fontSize: Dimensione in punti del font
     * @param string $fontStyle: stile del font ('normal','italic','bold')
     * @param string $fontColor: colore del font
     * @param string $fontFamily: famiglia del font da usare (come da css)
     * @param int $padding: padding della legenda
     */
    public function setLegendLabel($boxWidth=40,$fontSize=12,$fontStyle=self::ITA_CHARTS_FONT_NORMAL,$fontColor='#333',$fontFamily="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",$padding=10){
        if(!isSet($this->legend)) $this->legend = new stdClass();
        
        $this->legend->labels = new stdClass();
        $this->legend->labels->boxWidth = $boxWidth;
        $this->legend->labels->fontSize = $fontSize;
        $this->legend->labels->fontStyle = $fontStyle;
        $this->legend->labels->fontColor = $fontColor;
        $this->legend->labels->fontFamily = $fontFamily;
        $this->legend->labels->padding = $padding;
    }
    
    /**
     * Definisce se mostrare o meno il titolo del grafico
     * @param boolean $display
     */
    public function setTitleDisplay($display=true){
        if(!isSet($this->title)) $this->title = new stdClass();
        
        $this->title->display = $display;
    }
    
    /**
     * Definisce il titolo del grafico
     * @param type $title
     */
    public function setTitle($title=''){
        if(!isSet($this->title)) $this->title = new stdClass();
        
        $this->title->text = $title;
    }
    
    /**
     * Definisce la posizione del titolo
     * @param string $position: 'top','left','right','bottom'
     * @throws ItaException: se la posizione fornita non è valida.
     */
    public function setTitlePosition($position=self::ITA_CHARTS_TOP){
        if($position != self::ITA_CHARTS_TOP && $position != self::ITA_CHARTS_BOTTOM && $position != self::ITA_CHARTS_LEFT && $position != self::ITA_CHARTS_RIGHT){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "La posizione data non è valida");
        }
        if(!isSet($this->title)) $this->title = new stdClass();
        
        $this->title->position = $position;
    }
    
    /**
     * Definisce alcuni parametri del titolo
     * @param int $fontSize: dimensione del font in punti
     * @param string $fontStyle: stile del font ('normal','bold','italic')
     * @param string $fontColor: colore del font
     * @param string $fontFamily: famiglia dei font da usare (come da css)
     * @param int $padding: padding del titolo
     */
    public function setTitleOptions($fontSize=12,$fontStyle=self::ITA_CHARTS_FONT_BOLD,$fontColor='#333',$fontFamily="'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",$padding=10){
        if(!isSet($this->title)) $this->title = new stdClass();
        
        $this->title->fontSize = $fontSize;
        $this->title->fontStyle = $fontStyle;
        $this->title->fontColor = $fontColor;
        $this->title->fontFamily = $fontFamily;
        $this->title->padding = $padding;
    }
    
    /**
     * Definisce se visualizzare o meno i tooltips
     * @param boolean $display
     */
    public function setToolTipDisplay($display=true){
        if(!isSet($this->tooltips)) $this->tooltips = new stdClass();
        
        $this->tooltips->enabled = $display;
    }
    
    /**
     * Impostazioni relative all'asse delle ascisse
     * @param boolean $display: mostra o meno l'asse
     * @param string $type: 'linear', 'logarithmic'
     * @param string $position: 'top','left','right','bottom'
     * @param boolean $stacked: Se sovrapporre o meno le linee
     * @param string $ticksCallback: funzione di callback per i custom ticks (es: $ticksCallback = 'function(a,b,c){...}';)
     */
    public function setXAxis($display=true,$type=null,$position=null,$stacked=null,$ticksCallback=null){
        if(!isSet($this->scales)) $this->scales = new stdClass();
        
        $this->scales->xAxes = array();
        $this->scales->xAxes[0] = new stdClass();
        
        $this->scales->xAxes[0]->display = $display;
        
        if(isSet($type)){
            $this->scales->xAxes[0]->type = $type;
        }
        if(isSet($position)){
            $this->scales->xAxes[0]->position = $position;
        }
        if(isSet($stacked)){
            $this->scales->xAxes[0]->stacked = $stacked;
        }
        if(isSet($ticksCallback)){
            $this->scales->xAxes[0]->ticks = new stdClass();
            $this->scales->xAxes[0]->ticks->callback = new itaChartsCustomString($ticksCallback);
        }
    }
    
    /**
     * Impostazioni relative all'asse delle ordinate
     * @param boolean $display: mostra o meno l'asse
     * @param string $type: 'linear', 'logarithmic'
     * @param string $position: 'top','left','right','bottom'
     * @param boolean $stacked: Se unire in un unica linea o meno le linee
     * @param string $ticksCallback: funzione di callback per i custom ticks (es: $ticksCallback = 'function(a,b,c){...}';)
     * @param string $ticksMix: Valore Minimo per i custom ticks
     * @param string $ticksMax: Valore Massimo per i custom ticks
     */
    public function setYAxis($display=true,$type=null,$position=null,$stacked=null,$ticksCallback=null,$ticksMin=null,$ticksMax=null){
        if(!isSet($this->scales)) $this->scales = new stdClass();
        
        $this->scales->yAxes = array();
        $this->scales->yAxes[0] = new stdClass();
        
        $this->scales->yAxes[0]->display = $display;
        
        if(isSet($type)){
            $this->scales->yAxes[0]->type = $type;
        }
        if(isSet($position)){
            $this->scales->yAxes[0]->position = $position;
        }
        if(isSet($stacked)){
            $this->scales->yAxes[0]->stacked = $stacked;
        }
        if(isSet($ticksCallback) || isSet($ticksMin) || isSet($ticksMax) ){
            $this->scales->yAxes[0]->ticks = new stdClass();
            
            if(isSet($ticksCallback)){
                $this->scales->yAxes[0]->ticks->callback = new itaChartsCustomString($ticksCallback);
            }
            if(isSet($ticksMin) || isSet($ticksMax) ){
                if(isSet($ticksMin) ){
//                    $this->scales->yAxes[0]->ticks->min = $ticksMin;
                    $this->scales->yAxes[0]->ticks->suggestedMin = $ticksMin;
                }
                if(isSet($ticksMax) ){
//                    $this->scales->yAxes[0]->ticks->max = $ticksMax;
                    $this->scales->yAxes[0]->ticks->suggestedMax = $ticksMax;
                }
            }
        }
    }
    
    /**
     * Definisce delle opzioni custom in formato chiave->valore.
     * @param stdClass $options struttura stdObject contenente le opzioni custom
     */
    public function setCustomOptions($options){
        $this->customOptions = $options;
    }
    
    public function __toString() {
        $customOptions = $this->customOptions;
        
        $options = new stdClass();
        $options->responsive = $this->responsive;
        if(isSet($customOptions->responsive)){
            foreach($customOptions->reponsive as $key=>$value){
                $options->responsive->$key = $value;
            }
            unset($customOptions->responsive);
        }
        
        $options->layout = $this->layout;
        if(isSet($customOptions->layout)){
            foreach($customOptions->layout as $key=>$value){
                $options->layout->$key = $value;
            }
            unset($customOptions->layout);
        }
        
        $options->legend = $this->legend;
        if($options->legend->display == false){
            unset($options->legend->labels);
            unset($options->legend->position);
        }
        elseif(isSet($customOptions->legend)){
            foreach($customOptions->legend as $key=>$value){
                $options->legend->$key = $value;
            }
            unset($customOptions->legend);
        }
        $options->title = $this->title;
        if(isSet($customOptions->title)){
            foreach($customOptions->title as $key=>$value){
                $options->title->$key = $value;
            }
            unset($customOptions->title);
        }
        
        $options->tooltips = $this->tooltips;
        if(isSet($customOptions->tooltips)){
            foreach($customOptions->tooltips as $key=>$value){
                $this->tooltips->$key = $value;
            }
            unset($customOptions->tooltips);
        }
        if(isSet($this->scales)){
            $options->scales = $this->scales;
            if(isSet($customOptions->scales)){
                foreach($customOptions->scales as $key=>$value){
                    $options->scales->$key = $value;
                }
                unset($customOptions->scales);
            }
        }
        
        if(!empty($customOptions)){
            foreach($customOptions as $key=>$value){
                $options->$key = $value;
            }
        }
        
        return itaChartHelper::objectRender($options);
    }
}
?>