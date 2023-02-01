<?php
require_once ITA_LIB_PATH . '/itaCharts/itaCharts.colors.class.php';

class itaChartsDataSet{
    const ITA_CHARTS_INTERPOLATION_NONE = 'none';
    const ITA_CHARTS_INTERPOLATION_CUBIC = 'cubic';
    const ITA_CHARTS_INTERPOLATION_MONOTONE = 'monotone';
    
    private $label;
    
    private $backgroundColor;
    private $hoverBackgroundColor;
    private $borderColor;
    private $hoverBorderColor;
    private $borderWidth;
    private $hoverBorderWidth;
    private $pointBorderWidth;
    private $pointRadius;
    private $pointHoverBorderWidth;
    private $pointBackgroundColor;
    private $pointBorderColor;
    private $fill;    
    private $interpolation;
    private $data;
    
    /**
     * Recupera le componenti di colore di una data stringa di colore e restituisce un oggetto che le contiene
     * @param string $colorCode: Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @return boolean|\stdClass: false se non riesce nella lettura, stdclass con i seguenti componenti se riesce:
     *                              ->red
     *                              ->green
     *                              ->blue
     *                              ->alpha
     */
    private function getColorArray($colorCode){
        $color = new stdClass();
        
        //rgba(r,g,b,a)
        if(preg_match('/rgba\(([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),([0-1]\.[0-9]*)\)/',$colorCode,$matches)===1){
            $color->red = $matches[1];
            $color->green = $matches[2];
            $color->blue = $matches[3];
            $color->alpha = $matches[4];
            return $color;
        }
        
        //rgb(r,g,b)
        if(preg_match('/rgb\(([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5]),([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5])\)/',$colorCode,$matches)===1){
            $color->red = $matches[1];
            $color->green = $matches[2];
            $color->blue = $matches[3];
            $color->alpha = 1;
            return $color;
        }
        
        //#RRGGBB
        if(preg_match('/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', $colorCode,$matches) === 1){
            $color->red = hexdec($matches[1]);
            $color->green = hexdec($matches[2]);
            $color->blue = hexdec($matches[3]);
            $color->alpha = 1;
            return $color;
        }
        
        //#RGB
        if(preg_match('/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/', $colorCode,$matches) === 1){
            $color->red = hexdec($matches[1])*16;
            $color->green = hexdec($matches[2])*16;
            $color->blue = hexdec($matches[3])*16;
            $color->alpha = 1;
            return $color;
        }
        
        $c = itaChartsColors::getColor(strtolower($colorCode));
        if($c !== false){
            $color->red = $c['red'];
            $color->green = $c['green'];
            $color->blue = $c['blue'];
            $color->alpha = 1;
            return $color;
        }
        return false;
    }

    /**
     * Converte un colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale in una stringa in formato rgba(r,g,b,a). Se si specifica alpha viene sovrascritta la trasparenza
     * @param string $colorCode: Stringa in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal $alpha: valore che va da 0-1 per la trasparenza
     * @return string: colore codificato in rgba(r,g,b,a)
     * @throws ItaException se non riesce la conversione
     */    
    private function normalizeColor($color,$alpha=null){
        $colorCode = $this->getColorArray($color);
        if(!$colorCode){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Impossibile decodificare la stringa di colore $color");
        }
        if(isSet($alpha) && stripos($color, 'rgba(') === false){
            if(!is_numeric($alpha)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il valore di trasparenza $alpha non è un valore valido");
            }
            $colorCode->alpha = $alpha;
        }
        return 'rgba('.$colorCode->red.','.$colorCode->green.','.$colorCode->blue.','.$colorCode->alpha.')';
    }    
    
    
    /**
     * Costruttore, inserisce i dati di default
     */
    public function __construct() {
        $this->label = '';
        $this->backgroundColor = 'rgba(9,119,185,0.5)';
        $this->hoverBackgroundColor = 'rgba(9,119,185,0.6)';
        $this->borderColor = 'rgba(9,119,185,0.8)';
        $this->hoverBorderColor = 'rgba(9,119,185,0.9)';
        $this->borderWidth = 1;
        $this->hoverBorderWidth = 2;
        $this->pointBorderWidth = 1;
        $this->pointRadius = 4;
        $this->pointHoverBorderWidth = 2;
        $this->pointBackgroundColor = 'rgba(9,119,185,0.5)';
        $this->pointBorderColor = 'rgba(9,119,185,0.8)';
        $this->fill = true;
        $this->data = array();
    }
    
    /**
     * Imposta il nome del dataset
     * @param string $label
     */
    public function setLabel($label){
        $this->label = $label;
    }
    
    /**
     * Imposta il colore di riempimento del grafico
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setBackgroundColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }
        
        if($color===null) $color = $this->borderColor;
        if($alpha===null) $alpha = 0.5;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->backgroundColor = $color;
    }
    
    /**
     * Imposta il colore di riempimento del grafico con il mouse in Hover
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setHoverBackgroundColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }
        
        if($color===null) $color = $this->backgroundColor;
        if($alpha===null) $alpha = 0.6;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->hoverBackgroundColor = $color;
    }
    
    /**
     * Imposta il colore di del bordo del grafico
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setBorderColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }

        if($color===null) $color = $this->backgroundColor;
        if($alpha===null) $alpha = 0.8;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->borderColor = $color;
    }
    
    /**
     * Imposta il colore di del bordo del grafico in hover
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setHoverBorderColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }

        if($color===null) $color = $this->borderColor;
        if($alpha===null) $alpha = 0.9;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->hoverBorderColor = $color;
    }
    
    /**
     * Imposta la larghezza in pixel del bordo del grafico
     * @param int $width
     */
    public function setBorderWidth($width=1){
        $this->borderWidth = $width;
    }
    
    /**
     * Imposta la larghezza in pixel del bordo del grafico in Hover
     * @param int $width
     */
    public function setHoverBorderWidth($width=2){
        $this->hoverBorderWidth = $width;
    }
    
    /**
     * Imposta la larghezza in pixel del bordo del cerchio di intersezione Grafici a Linee
     * @param int $width
     */
    public function setPointBorderWidth($width=1){
        $this->pointBorderWidth = $width;
    }
    
    /**
     * Imposta la larghezza in pixel del bordo del cerchio di intersezione Grafici a Linee in Hover
     * @param int $width
     */
    public function setPointHoverBorderWidth($width=2){
        $this->pointHoverBorderWidth = $width;
    }
    
    /**
     * Imposta la larghezza in pixel del cerchio di intersezione Grafici a Linee
     * @param int $width
     */
    public function setPointRadius($width=4){
        $this->pointRadius = $width;
    }
    
    /**
     * Imposta il colore di sfondo del Point del grafico Line
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setPointBackgroundColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }

        if($color===null) $color = $this->pointBorderColor;
        if($alpha===null) $alpha = 0.5;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->pointBackgroundColor = $color;
    }
    
    /**
     * Imposta il colore del Bordo del Point del grafico Line
     * @param string/array(string) $color Colore in formato #RRGGBB, #RGB, rgba(r,g,b,a), rgb(r,g,b) o testuale
     * @param decimal/array(decimal) $alpha valore da 0-1 per trasparenza
     */
    public function setPointBorderColor($color=null,$alpha=null){
        if(is_array($color) && is_array($alpha) && count($color) != count($alpha)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP,-1,"Sono stati forniti degli array di colore e alpha, ma hanno quantitativi diversi");
        }

        if($color===null) $color = $this->pointBackgroundColor;
        if($alpha===null) $alpha = 0.8;
        
        if(is_array($color) && !is_array($alpha)) $alpha = array_fill(0,count($color),$alpha);
        
        if(is_array($color)){
            foreach($color as $key=>$value){
                $color[$key] = $this->normalizeColor($color[$key],$alpha[$key]);
            }
        }
        else{
            $color = $this->normalizeColor($color,$alpha);
        }
        $this->pointBorderColor = $color;
    }
    
    /**
     * Imposta se riempire il grafico o lasciare solo il bordo
     * @param boolean $fill
     */
    public function setFill($fill=true){
        $this->fill = $fill;
    }
    
    /**
     * Prende un array da usare come dataset
     * @param array $data
     * @throws ItaException: Se i dati passati non sono in forma di Array
     */
    public function setData($data){
        if(!is_array($data)) throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "I dati passati non sono in forma di array");
        foreach($data as &$value){
            if(!is_numeric($value)){
                $value = "'".$value."'";
            }
        }
        $this->data = $data;
    }
    
    
    /**
     * Imposta il tipo di interpolazione per i grafici a linea
     * @param string $interpolation: 'cubic','none','monotone'
     * @throws type
     */
    public function setInterpolation($interpolation=self::ITA_CHARTS_INTERPOLATION_CUBIC){
        if($interpolation !== self::ITA_CHARTS_INTERPOLATION_CUBIC && $interpolation != self::ITA_CHARTS_INTERPOLATION_MONOTONE && $interpolation !== self::ITA_CHARTS_INTERPOLATION_NONE){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Tipologia di interpolazione non gestita");
        }
        $this->interpolation = $interpolation;
    }
    
    public function __toString(){
        if($this->backgroundColor == 'rgba(9,119,185,0.5)') $this->setBackgroundColor();
        if($this->hoverBackgroundColor == 'rgba(9,119,185,0.6)') $this->setHoverBackgroundColor();
        if($this->borderColor == 'rgba(9,119,185,0.8)') $this->setBorderColor();
        if($this->hoverBorderColor == 'rgba(9,119,185,0.9)') $this->setHoverBorderColor();
        
        $return = "{
label: '{$this->label}',";

        if(is_array($this->backgroundColor)){
            $return .= "backgroundColor: ['".implode("','", $this->backgroundColor)."'],\r\n";
        }
        else{
            $return .= "backgroundColor: '{$this->backgroundColor}',\r\n";
        }
        if(is_array($this->hoverBackgroundColor)){
            $return .= "hoverBackgroundColor: ['".implode("','", $this->hoverBackgroundColor)."'],\r\n";
        }
        else{
            $return .= "hoverBackgroundColor: '{$this->hoverBackgroundColor}',\r\n";
        }
        
        if(is_array($this->borderColor)){
            $return .= "borderColor: ['".implode("','", $this->borderColor)."'],\r\n";
        }
        else{
            $return .= "borderColor: '{$this->borderColor}',\r\n";
        }
        if(is_array($this->hoverBorderColor)){
            $return .= "hoverBorderColor: ['".implode("','", $this->hoverBorderColor)."'],\r\n";
        }
        else{
            $return .= "hoverBorderColor: '{$this->hoverBorderColor}',\r\n";
        }
        
        if(is_array($this->pointBackgroundColor)){
            $return .= "pointBackgroundColor: ['".implode("','", $this->pointBackgroundColor)."'],\r\n";
        }
        else{
            $return .= "pointBackgroundColor: '{$this->pointBackgroundColor}',\r\n";
        }
        if(is_array($this->pointBorderColor)){
            $return .= "pointBorderColor: ['".implode("','", $this->pointBorderColor)."'],\r\n";
        }
        else{
            $return .= "pointBorderColor: '{$this->pointBorderColor}',\r\n";
        }
        
        if(isSet($this->interpolation)){
            switch($this->interpolation){
                case self::ITA_CHARTS_INTERPOLATION_NONE:
                    $return .= "lineTension: 0,\r\n";
                    break;
                case self::ITA_CHARTS_INTERPOLATION_MONOTONE:
                    $return .= "cubicInterpolationMode: 'monotone',\r\n";
                    break;
            }
        }
            
        $return .= "borderWidth: {$this->borderWidth},
hoverBorderWidth: {$this->hoverBorderWidth},
pointBorderWidth: {$this->pointBorderWidth},
pointRadius: {$this->pointRadius},
pointHoverBorderWidth: {$this->pointHoverBorderWidth},
fill: ".(($this->fill)?'true':'false').",
data: [".implode(',',$this->data)."]
}";
        
        return $return;
    }
}
?>