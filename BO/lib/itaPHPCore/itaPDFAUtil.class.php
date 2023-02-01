<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaJasperReport
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/itaPHPPDFA/itaPDFA.class.php');

class itaPDFAUtil {

    public $managerEngine;

    public function __construct() {
        $managerEngine = App::getConf('PDFAManager.manager');
        $manager = itaPDFA::getManagerInstance();
        if ($manager) {
            $managerVersion = $manager->getManagerVersion();
        }
    }

    public static function getManagerType(){
        $ret = array();
        $managerEngine = App::getConf('PDFAManager.manager');
        if (!$managerEngine) {
            return false;
        }
        $manager = itaPDFA::getManagerInstance($managerEngine);
        if ($manager) {
            return $manager->getManagerType();
        } else {
            return false;
        }
        
    }
    
    public static function verifyPDFSimple($fileName, $verbose = 0, $level = "A") {
        App::log('Verify Simple');        
        App::log(App::getConf('PDFAManager.manager'));
        $ret = array();
        $managerEngine = App::getConf('PDFAManager.manager');
        if (!$managerEngine) {
            $ret['status'] = -99;
            $ret['message'] = "pdf manager non definito.";
            return $ret;
        }
        $manager = itaPDFA::getManagerInstance($managerEngine);
        App::log($manager);
        if ($manager) {
            $exitCode = $manager->verifyPDFSimple($fileName, $verbose, $level);
            $ret['status'] = $exitCode;
            $ret['message'] = $manager->getLastMessage();
            $ret['output'] = $manager->getLastOutput();
            App::log($ret);
        } else {
            $ret['status'] = -99;
            $ret['message'] = "Errore in attivazione pdf manager";
        }
        return $ret;
    }

    public static function convertPDF($fileName, $outputFile, $verbose = 0, $level = "A") {
        $ret = array();
        $managerEngine = App::getConf('PDFAManager.manager');
        if (!$managerEngine) {
            $managerEngine = 'none';
        }

        $manager = itaPDFA::getManagerInstance($managerEngine);
        if ($manager) {
            $manager->convertPDF($fileName, $outputFile, $verbose, $level);
            //$manager->getLastExitCode();
            $ret['status'] = $manager->getLastExitCode();
            $ret['message'] = $manager->getLastMessage();
        } else {
            $ret['status'] = -99;
            $ret['message'] = "Errore in attivazione pdf manager";
        }
        return $ret;
    }

}
