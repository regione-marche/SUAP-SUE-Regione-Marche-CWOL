<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaPHPUnit/itaPHPUnit.class.php';

function cwbPhpUnitConsole() {
    $cwbPhpUnitConsole = new cwbPhpUnitConsole();
    $cwbPhpUnitConsole->parseEvent();
    return;
}

class cwbPhpUnitConsole extends itaFrontControllerCW {
    private $phpUnit;
    
    function __construct($nameFormOrig=null, $nameForm=null){
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'cwbPhpUnitConsole';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initialize();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_testAll':
                        $this->testAll();
                        break;
                    default:
                        if(preg_match('/.*_(.*)_executeTest/', $_POST['id'], $matches) === 1){
                            $test = $matches[1];
                            $this->test($test);
                            break;
                        }
                        if(preg_match('/.*_(.*)_detailsShow/', $_POST['id'], $matches) === 1){
                            $test = $matches[1];
                            $this->showDetails($test);
                            break;
                        }
                        if(preg_match('/.*_(.*)_details_hide/', $_POST['id'], $matches) === 1){
                            $test = $matches[1];
                            $this->hideDetails($test);
                            break;
                        }
                        
                }

        }
    }
    
    private function initialize(){
        if(!isSet($this->phpUnit)) $this->phpUnit = new itaPHPUnit();
        
        $tests = @$this->phpUnit->getTestsNames();
        foreach($tests as $test){
            $this->buildRow($test);
        }
    }
    
    private function buildRow($testName){
        itaLib::openInner('cwbPhpUnitDetails', '', true, $this->nameForm . '_workSpace', '', '', 'cwbPhpUnitDetails_'.$testName);
        
        Out::hide('cwbPhpUnitDetails_'.$testName.'_detailsShow');
        Out::hide('cwbPhpUnitDetails_'.$testName.'_detailsDiv');
        
        Out::html('cwbPhpUnitDetails_'.$testName.'_name', $testName);
        Out::html('cwbPhpUnitDetails_'.$testName.'_details_testName',$testName);
    }
    
    private function testAll(){
        if(!isSet($this->phpUnit)) $this->phpUnit = new itaPHPUnit();
        
        $tests = $this->phpUnit->getTestsNames();
        foreach($tests as $test){
            $this->test($test);
        }
    }
    
    private function test($testName){
        if(!isSet($this->phpUnit)) $this->phpUnit = new itaPHPUnit();
        
        $testResult = $this->phpUnit->executeTest($testName);
        
        if($testResult['result']===true){
            Out::html('cwbPhpUnitDetails_'.$testName.'_detailsShow_lbl','PASS');
            Out::html('cwbPhpUnitDetails_'.$testName.'_details_testResult','PASS');
        }
        else{
            Out::html('cwbPhpUnitDetails_'.$testName.'_detailsShow_lbl','FAIL');
            Out::html('cwbPhpUnitDetails_'.$testName.'_details_testResult','FAIL');
        }
        
        Out::html('cwbPhpUnitDetails_'.$testName.'_details_testCount',$testResult['tests']);
        
        $passHtml = '<ul>';
        foreach($testResult['passed'] as $pass){
            $passHtml .= "<li style=\"list-style-type:circle; margin-left: 20px\"><b>$pass</b></li>\r\n";
        }
        $passHtml.= '</ul>';
        Out::html('cwbPhpUnitDetails_'.$testName.'_details_testPass',$passHtml);
        
        $failHtml = '<ul>';
        foreach($testResult['failed'] as $test=>$error){
            $failHtml .= "<li style=\"list-style-type:circle; margin-left: 20px\"><b>$test:</b> $error</li>\r\n";
        }
        $failHtml.= '</ul>';
        Out::html('cwbPhpUnitDetails_'.$testName.'_details_testFail',$failHtml);
        
        Out::show('cwbPhpUnitDetails_'.$testName.'_detailsShow');
    }
    
    private function showDetails($testName){
        Out::show('cwbPhpUnitDetails_'.$testName.'_detailsDiv');
    }
    
    private function hideDetails($testName){
        Out::hide('cwbPhpUnitDetails_'.$testName.'_detailsDiv');
    }
}

