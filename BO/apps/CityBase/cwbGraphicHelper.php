<?php
include_once ITA_LIB_PATH . '/itaCharts/itaCharts.class.php';
include_once ITA_LIB_PATH . '/itaPHPMath/itaPhpMathFactory.class.php';

class cwbGraphicHelper{
    
    /*
     * Imposta il dataSet ** Creazione di un Grafico di Tipo Gauge con ChartJS
     */
    public function dataSet($tipo, $denominatore, $numeratore) {
        $mathLib = itaPhpMathFactory::getItaPhpMathInstance(itaPhpMathFactory::PROVIDER_BCMATH, 5);
        
        $perc = 0;
        if ($denominatore !=0 && !empty($denominatore)) {
//            $perc = (($numeratore*100)/$denominatore);
            $perc = $mathLib->div($mathLib->mlt($numeratore, 100), $denominatore);
            $perc = number_format($perc, 2, '.', ''); // Americano senza separatore migliaia
        }
//        $desc_canvas = "Andamento...";
        
        $color = $this->colorRange($tipo);

        $valo1 = $perc;
//        $valo2 = (100-$valo1);
        $valo2 = $mathLib->sub(100, $valo1);
        if ($valo2 < 0) {
            $valo1 = 100;
            $valo2 = 0;
        }

        $meta = 4.0;
//        $frame = (100-$meta*17)/18;
        $frame = $mathLib->div($mathLib->sub(100, $mathLib->mlt($meta, 17)),18);
        $colorMeta = '#fff';
        
        $values = array();
        $colors = array();
        $colorAst = '#000000';
        $totalValue = 0;
        $asta = false;
        for ($n=0;$n<18;$n++) {
            $values[]=$frame;
            if ($valo1 == 0 && $n == 0){
                $colors[]=$colorAst;
                $asta = true;
            } else {
                if ($valo1 < $totalValue && $asta == false && $valo1>0){
                    $colors[]=$colorAst;
                    $asta = true;
                } else {
                    $colors[]=$color[$n];
                }
            }
//            $totalValue = $totalValue+$frame;
            $totalValue = $mathLib->sum($totalValue, $frame);
            if ($n<18){
                $values[]=$meta;
                // Valore raggiunto
                if ($valo1 >= $totalValue) {
                    $colors[]=$color[$n];
                } else {
//          Asta più grande
//                    if ($asta == false && $valo1>0){
//                        $colors[]=$colorAst;
//                        $asta = true;
//                    } else {
                        $colors[]=$colorMeta;
//                    }
                }
//                $totalValue = $totalValue+$meta;
                $totalValue = $mathLib->sum($totalValue, $meta);
            }
        }
 
        // Creazione da Meta' DONUTS di ChartJS
        $dataSetPie = new itaChartsDataSet();
        $dataSetPie->setData($values);
        $dataSetPie->setBackgroundColor( $colors, 1 );  
        $dataSetPie->setBorderColor( array('#fff','#fff') );
        $dataSetPie->setBorderWidth(0);
        
        $dataSetPie->setHoverBackgroundColor( array('#FFF','#fff') );
        $dataSetPie->setHoverBorderColor( array('#fff','#fff') );
        $dataSetPie->setHoverBorderWidth(0);
//        $dataSetPie->setLabel('Percentuale'); // NON FUNZIONA!!!!
        
        return $dataSetPie;
    }
    
    /*
     * Imposta options ** Creazione di un Grafico di Tipo Gauge con ChartJS
     */
    public function options($tipo, $denominatore,$numeratore, $type,$desc_canvas) {
        $mathLib = itaPhpMathFactory::getItaPhpMathInstance(itaPhpMathFactory::PROVIDER_BCMATH, 5);
        
        $colore_centro = $this->coloreCentro($tipo, $denominatore,$numeratore);
        $perc_vis = "0,00";
        if ($denominatore !=0 && !empty($denominatore)) {
//            $perc = (($numeratore*100)/$denominatore);
            $perc = $mathLib->div($mathLib->mlt($numeratore, 100), $denominatore);
            $perc_vis = number_format($perc, 2, ',', '.'); // Italiano
        }
        
        $options = new itaChartsOptions();
        $options->setLegendDisplay(false);
        $options->setToolTipDisplay(false);
        if ($desc_canvas!=null){
//        $options->setTitle( "['".$desc_canvas."','".$desc_canvas2."']" ); // Se array multiple lines ma non funziona
            $options->setTitle($desc_canvas); // Se array multiple lines ma non funziona           
        }
        $customoptions = new stdClass;
        if ($type == 1){
            // Tachimetro 270 gradi
            $customoptions->rotation = (0.75 * pi() );       // In JS (1 * Math.PI)
            $customoptions->circumference = (1.5 *  pi() );  // In JS (1 * Math.PI) 
            $customoptions->aspectRatio = 1.5; //Dafult e' 1 (2 dalla 2.7.1)
        } else {
            // Tachimetro Standard
            $customoptions->rotation = (1 * pi() );       // In JS (1 * Math.PI)
            $customoptions->circumference = (1 *  pi() );  // In JS (1 * Math.PI) 
            $customoptions->aspectRatio = 2; //Dafult e' 1 (2 dalla 2.7.1)
        }
        
        
            // Custom Options per PluginService
                // Visualizzo Testo al Centro del Donut
        $elements = new stdClass;
        $center = new stdClass;
        $center->text = $perc_vis.'%'; // Testo da Visualizzare
        $center->color = $colore_centro; // Default is #000000
        $center->fontStyle = 'Arial';  // Default is Arial
        $center->sidePadding = 20;     // Default is 20 (as a percentage)
        $customoptions->elements = $elements;
        $customoptions->elements->center = $center;

        $options->setCustomOptions($customoptions);
        
        return $options;
    }
    
    private function coloreCentro ($tipo, $denominatore,$numeratore){
        $mathLib = itaPhpMathFactory::getItaPhpMathInstance(itaPhpMathFactory::PROVIDER_BCMATH, 5);
        $color = $this->colorRange($tipo);
        
        $perc = 0;
        if ($denominatore !=0 && !empty($denominatore)) {
//            $perc = (($numeratore*100)/$denominatore);
            $perc = $mathLib->div($mathLib->mlt($numeratore, 100), $denominatore);
            $perc = number_format($perc, 2, '.', ''); // Americano senza separatore migliaia
        }
        
        switch ($tipo) {
            case 'R':
                $colore_centro = '#ff0000'; // Rosso x lo 0
                break;
            case 'RVR':
                $colore_centro = '#ff0000'; // Rosso x lo 0
                break;
            case 'V':
                $colore_centro = '#66cd00'; // Verde x lo 0
                break;
        }
        
        $valo1 = $perc;
//        $valo2 = (100-$valo1);
        $valo2 = $mathLib->sub(100, $valo1);
        if ($valo2 < 0) {
            $valo1 = 100;
            $valo2 = 0;
        }

        $meta = 4.0;
//        $frame = (100-$meta*17)/18;
        $frame = $mathLib->div($mathLib->sub(100, $mathLib->mlt($meta, 17)), 18);
        $totalValue = 0;
        for ($n=0;$n<18;$n++) {
//            $totalValue = $totalValue+$frame;
            $totalValue = $mathLib->sum($totalValue, $frame);
            if ($n<18){
//                $totalValue = $totalValue+$meta;
                $totalValue = $mathLib->sum($totalValue, $meta);
                // Valore raggiunto
                if ($valo1 >= $totalValue) {
                    $colore_centro = $color[$n+1]; // Colore relativo
                }
            } else {
                if ($valo1 >= $totalValue) {
                    $colore_centro = $color[$n]; // Colore relativo
                }
            }
        }
        return $colore_centro;
    }
    
    private function colorRange ($tipo){
        switch ($tipo) {
            case 'R':
                $colore1 = '#FE0000';
                $colore2 = '#FC2608';
                $colore3 = '#F2570A';
                $colore4 = '#F46E09';
                $colore5 = '#FB8617';
                $colore6 = '#F89E17';
                $colore7 = '#FBB415';
                $colore8 = '#FEC803';
                $colore9 = '#FEE000';
                $colore10 = '#FCDF03';
                $colore11 = '#E4DD1E';
                $colore12 = '#D9E222';
                $colore13 = '#C4E027';
                $colore14 = '#AFDD25';
                $colore15 = '#9ADD1E';
                $colore16 = '#89D82B';
                $colore17 = '#70D11A';
                $colore18 = '#4FCA1B';
                break;
            case 'RVR':
                $colore1 = '#FE0000';
                $colore2 = '#F2570A';
                $colore3 = '#FB8617';
                $colore4 = '#FBB415';
                $colore5 = '#FCDF03';
                $colore6 = '#D9E222';
                $colore7 = '#AFDD25';
                $colore8 = '#89D82B';
                $colore9 = '#4FCA1B';

                $colore10 = '#4FCA1B';
                $colore11 = '#89D82B';
                $colore12 = '#AFDD25';
                $colore13 = '#D9E222';
                $colore14 = '#FCDF03';
                $colore15 = '#FBB415';
                $colore16 = '#FB8617';
                $colore17 = '#F2570A';
                $colore18 = '#FE0000';
                break;
            case 'V':
                $colore18 = '#FE0000';
                $colore17 = '#FC2608';
                $colore16 = '#F2570A';
                $colore15 = '#F46E09';
                $colore14 = '#FB8617';
                $colore13 = '#F89E17';
                $colore12 = '#FBB415';
                $colore11 = '#FEC803';
                $colore10 = '#FEE000';
                $colore9 = '#FCDF03';
                $colore8 = '#E4DD1E';
                $colore7 = '#D9E222';
                $colore6 = '#C4E027';
                $colore5 = '#AFDD25';
                $colore4 = '#9ADD1E';
                $colore3 = '#89D82B';
                $colore2 = '#70D11A';
                $colore1 = '#4FCA1B';
                break;
        }
        
        $color = array($colore1, $colore2, $colore3, $colore4, $colore5, $colore6, $colore7, 
            $colore8, $colore9, $colore10, $colore11, $colore12, $colore13, $colore14, $colore15, 
            $colore16, $colore17, $colore18);
        
        return $color;
    }
    
    
    public function js_pluginservice() {
//      AdditionalCode per Plugin Service Visualizzo Testo al Centro
        $js_pluginservice = "
            Chart.pluginService.register({
                beforeDraw: function (chart) {
                    if (chart.config.options.elements.center) {
                        // Get ctx from string
                        var ctx = chart.chart.ctx;
                        // Get options from the center object in options
                        var centerConfig = chart.config.options.elements.center;
                        var fontStyle = centerConfig.fontStyle || 'Arial';
                        var txt = centerConfig.text;
                        var color = centerConfig.color || '#000';
                        var sidePadding = centerConfig.sidePadding || 20;
                        var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
                        // Start with a base font of 24px
                        ctx.font = '20px ' + fontStyle;
                        // Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                        var stringWidth = ctx.measureText(txt).width;
                        var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;
                        // Find out how much the font can grow in width.
                        var widthRatio = elementWidth / stringWidth;
                        var newFontSize = Math.floor(20 * widthRatio);
                        var elementHeight = (chart.innerRadius * 2);
                        // Pick a new font size so it will not be larger than the height of label.
                        var fontSizeToUse = Math.min(newFontSize, elementHeight);
                        // Set font settings to draw it correctly.
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                        var centerY = (chart.chartArea.bottom); 
                        ctx.font = fontSizeToUse+'px ' + fontStyle;
                        ctx.fillStyle = color;
                        // Draw text in center
                        ctx.fillText(txt, centerX, centerY);
                    }
                }
            }); ";
        
        return $js_pluginservice;
    }
    
    
    /*
     * Imposta il dataSet ** Creazione di un Grafico di Tipo Pie con ChartJS
     */
    public function dataSetPie($values, $colors, $label) {
        // Creazione da PIE di ChartJS
        $dataSetPie = new itaChartsDataSet();
        $dataSetPie->setData($values);
        if (!empty($colors) && count($colors)>0){
//            $dataSetPie->setBackgroundColor($colors, 1);
            $alpha = 1;
            $dataSetPie->setBackgroundColor($colors, $alpha);
        }
        $bordcolor = array('white','white');        
        $dataSetPie->setBorderColor($bordcolor);
        $dataSetPie->setBorderWidth(0);
        $dataSetPie->setLabel($label); // NON FUNZIONA!!!!
          
        $dataSetPie->setHoverBackgroundColor($colors);
        $dataSetPie->setHoverBorderColor($colors);
        $dataSetPie->setHoverBorderWidth(0);
//        $dataSetPie->setLabel('Percentuale'); // NON FUNZIONA!!!!
        
        return $dataSetPie;
    }

    
    /*
     * Imposta options ** Creazione di un Grafico di Tipo Pie con ChartJS
     */
    public function optionsPie($desc_canvas) {
        
        $options = new itaChartsOptions();
        if ($desc_canvas!=null){
            $options->setTitle($desc_canvas); // Se array multiple lines ma non funziona           
        }
        $options->setLegendPosition('left');
        
        return $options;
    }
    
    
    /*
     * Imposta options ** Creazione di un Grafico di Tipo Istogramma con ChartJS
     */    
    public static function newDataset($data, $label, $color, $alpha = null) {
        $dataSet = new itaChartsDataSet();
        $dataSet->setData($data);
        $dataSet->setHoverBorderWidth(0);
        $dataSet->setBorderWidth(0);
        if ($alpha) {
            $dataSet->setBackgroundColor($color, $alpha);
        } else {
            $dataSet->setBackgroundColor($color);
        }

        $dataSet->setLabel($label);

        return $dataSet;
    }

    
}
