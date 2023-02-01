<?php
class itaPHPUnit{
    private $phpUnit;
    private $tests;
    private $buffer;
    
    /**
     * Costruttore
     * @param string $xml (facoltativo) Path del file phpunit.xml, se non valorizzato si prende ITA_BASE_PATH/test/phpunit.xml
     * @throws ItaException
     */
    public function __construct($xml=null){
        if(!isSet($xml)){
            $xml = ITA_BASE_PATH . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'phpunit.xml';
        }
        if(!file_exists($xml)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Il file phpunit.xml non è stato trovato');
        }
        
        $this->phpUnit = new PHPUnit_TextUI_TestRunner();
        
        $testPathsArray = $this->readPathsFromXml($xml);
        $this->initializeTests($testPathsArray);
    }
    
    private function readPathsFromXml($xmlPath){
        $paths = array();
        
        try{
            $xml = simplexml_load_file($xmlPath);
            
            foreach($xml->testsuites->testsuite as $testSuite){
                foreach($testSuite->directory as $dir){
                    $paths[realpath(dirname($xmlPath) . DIRECTORY_SEPARATOR . $dir)] = true;
                }
                foreach($testSuite->file as $file){
                    $paths[realpath(dirname($xmlPath) . DIRECTORY_SEPARATOR . $file)] = true;
                }
            }
            
            return array_keys($paths);
        }
        catch(Exception $e){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, $e->getLine());
        }
        
    }
    
    private function initializeTests($testPathsArray){
        $this->tests = array();
        
        try{
            foreach($testPathsArray as $testPath){
                set_error_handler(function(){});
                $tests = @$this->phpUnit->getTest($testPath);
                restore_error_handler();
                
                if(is_dir($testPath)){
                    foreach($tests as $test){
                        $this->tests[$test->getName()] = $test;
                    }
                }
                elseif(is_file($testPath)){
                    $this->tests[$tests->getName()] = $tests;
                }
            }
        }
        catch(Exception $e){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, $e->getLine());
        }
    }
    
    private function saveBuffer(){
        $this->buffer = '';
        while(ob_get_level()>0){
            $this->buffer .= ob_get_clean();
        }
    }
    
    private function restoreBuffer(){
        while(ob_get_level()>0){
            ob_end_clean();
        }
        ob_start();
        echo $this->buffer;
        $this->buffer = '';
    }
    
    /**
     * Restituisce un array con i nomi di tutte le test unit trovate
     * @return array
     */
    public function getTestsNames(){
        return array_keys($this->tests);
    }
    
    /**
     * Esegue una singola test unit
     * @param string $test nome del test
     * @return array 'name' string nome del test unit
     *               'result' boolean indica se l'intero test è andato a buon fine
     *               'tests' integer numero di test eseguiti
     *               'passed' array nomi dei test andati a buon fine
     *               'failed' array chiave=nomi dei test falliti, valore=testo dell'eccezione scatenata dal test.
     * @throws ItaException
     */
    public function executeTest($test){
        if(!isSet($this->tests[$test])){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Il test selezionato non è stato trovato');
        }
        
        try{
            $return = array();

            $this->saveBuffer();
            $result = $this->tests[$test]->run();
            $this->restoreBuffer();

            $return['name'] = $test;
            $return['result'] = $result->wasSuccessful();
            $return['tests'] = $result->count();

            $return['passed'] = array();
            $passed = array_keys($result->passed());
            foreach($passed as $value){
                $tmp = @end(explode('::',$value));
                if($tmp){
                    $return['passed'][] = $tmp;
                }
                else{
                    echo "wat";
                }
            }

            $return['failed'] = array();
            $failures = $result->failures();
            $errors = $result->errors();
            $failed = empty($failures) ? $errors : $failures;
            foreach($failed as $value){
                $tmp = @end(explode('::',$value->getTestName()));
                if($tmp){
                    $return['failed'][$tmp] = $value->getExceptionAsString();
                }
                else{
                    echo "wat";
                }
            }
            return $return;
        }
        catch(Exception $e){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Errore critico PHPUnit nel test '.$test.': '.$e->getMessage());
        }
    }
    
    /**
     * Esegue tutti i test disponibili
     * @return array array di array dello stesso tipo restituito da getTestNames
     * @throws ItaException
     */
    public function executeAllTests(){
        $return = array();
        
        foreach(array_keys($this->tests) as $test){
            $return[] = $this->executeTest($test);
        }
        
        return $return;
    }
}
?>