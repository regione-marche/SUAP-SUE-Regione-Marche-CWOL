<?php
require_once ITA_LIB_PATH . '/itaCharts/itaChartHelper.php';
require_once ITA_LIB_PATH . '/itaCharts/itaCharts.dataset.class.php';
require_once ITA_LIB_PATH . '/itaCharts/itaCharts.options.class.php';

class itaCharts{
    const ITA_CHARTS_TYPE_LINE = 'line';
    const ITA_CHARTS_TYPE_BAR = 'bar';
    const ITA_CHARTS_TYPE_BAR_HORIZONTAL = 'horizontalBar';
    const ITA_CHARTS_TYPE_RADAR = 'radar';
    const ITA_CHARTS_TYPE_DOUGHNUT = 'doughnut';
    const ITA_CHARTS_TYPE_PIE = 'pie';
    const ITA_CHARTS_TYPE_POLARAREA = 'polarArea';
    const ITA_CHARTS_TYPE_BUBBLE = 'bubble';
    const ITA_CHARTS_TYPE_SCATTER = 'scatter';
    
    private static $CHARTTYPES = array(
        self::ITA_CHARTS_TYPE_BAR,
        self::ITA_CHARTS_TYPE_BAR_HORIZONTAL,
        self::ITA_CHARTS_TYPE_BUBBLE,
        self::ITA_CHARTS_TYPE_DOUGHNUT,
        self::ITA_CHARTS_TYPE_LINE,
        self::ITA_CHARTS_TYPE_PIE,
        self::ITA_CHARTS_TYPE_POLARAREA,
        self::ITA_CHARTS_TYPE_RADAR,
        self::ITA_CHARTS_TYPE_SCATTER);
    
    private $container;    
    private $labels;
    private $dataSets = array();
    private $options;
    private $customParameters;
    private $additionalCode;
    
    private $chartType;
    
    public function __construct($container){
        $this->container = $container;
        $this->customParameters = null;
        $this->additionalCode = null;
    }

    /**
     * Imposta le label delle ascisse
     * @param array $labels: array di label
     * @throws ItaException: se non si sta passando un array
     */
    public function setLabels($labels){
        if(!is_array($labels)) throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Le label non sono state passate sotto forma di array");
        $this->labels = $labels;
    }

    /**
     * Aggiunge un dataset, è possibile aggiungere più dataset che verranno poi sovrapposti
     * @param itaChartDataSet $dataSet
     * @throws ItaException: se la variabile passata non è un'istanza di itaChartDataSet
     */
    public function addDataSet($dataSet){
        if(!($dataSet instanceof itaChartsDataSet)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Non è stata passata un'istanza di itaChartsDataSet");
        }
        $this->dataSets[] = $dataSet;
    }
    
    /**
     * Azzera i dati del grafico
     */
    public function resetData(){
        $this->labels = array();
        $this->dataSets = array();
    }
    
    /**
     * Imposta la tipologia di grafico che si intende usare
     * @param string $chartType: tipologia di grafico
     * @return boolean: true se riuscito
     * @throws ItaException: se fallito
     */
    public function setChartType($chartType=self::ITA_CHARTS_TYPE_LINE){
        if(in_array($chartType,self::$CHARTTYPES)){
            $this->chartType = $chartType;
            return true;
        }
        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Grafico di tipo $chartType non definito");
    }
    
    /**
     * Imposta le opzioni del grafico a partire da un oggetto di tipo itaChartsOptions
     * @param itaChartsOptions $options
     * @throws ItaException: Se l'oggetto delle opzioni non è di tipo itaChartsOptions
     */
    public function setOptions($options){
        if(!($options instanceof itaChartsOptions)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il parametro passato non è un oggetto di tipo itaChartsOptions");
        }
        $this->options = $options;
    }
    
    /**
     * Definisce delle opzioni custom in formato chiave->valore.
     * @param stdClass $parameters struttura stdObject contenente i parametri custom
     */
    public function setCustomParameters($parameters){
        $this->customParameters = $parameters;
    }
    
    /**
     * Aggiunge codice da eseguire liberamente al termine della creazione del chart.
     * @param string $code stringa di codice aggiuntiva
     * 
     * Reinvia il Nome del Canvas contenete il Grafico
     * 
     * NOTA: La variabile dell'oggetto ChartJS si chiama $container.'_Chart' (eg: cwbBorOrgan_divChartjs_Chart')
     */
    public function setAdditionalCode($code){
        $this->additionalCode = $code;
    }
    
    public function render(){
        if(!isSet($this->chartType)) $this->setChartType();
        
        $contName = $this->container.rand();
        $customParameters = $this->customParameters;
        
        Out::html($this->container,"<canvas id='{$contName}_cv'></canvas>");
        Out::codice("itaGetLib('libs/chartjs/Chart.bundle.min.js', 'Chart')");
        
        $js = "var {$contName}_config = {
  type: '{$this->chartType}',
  data: {
    labels: ['".implode("','",$this->labels)."'],
    datasets: [".implode(',',$this->dataSets)."]";
        
        if(isSet($customParameters->data)){
            $js .= ', '.itaChartHelper::objectRender($customParameters->data);
            unset($customParameters->data);
        }
  
        $js .= "
  }";
        if(isSet($this->options)){
            $js .= ",options:".$this->options;
        }
        if(!empty($customParameters)){
            $js .= ', '.itaChartHelper::objectRender($customParameters);
        }
        $js .= "};
var {$contName}_ctx = document.getElementById('{$contName}_cv').getContext('2d');
var {$this->container}_Chart = new Chart({$contName}_ctx, {$contName}_config);";
        if(!empty($this->additionalCode)){
            $js .= $this->additionalCode;
        }

        Out::codice($js);
        
        return $contName.'_cv'; // Reinvio nome del Canvas creato
    }
}
?>