<?php

require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueWorker.php';
require_once ITA_LIB_PATH . '/itaPHPQueue/itaQueueWorkerBase.class.php';
require_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDanNuoviEventi.class.php';

/**
 *
 * Worker specifico per ANPR
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPQueue/workers
 * @author     Biagioli/Pergolini
 * @copyright  
 * @license
 * @version    31.03.2017
 * @link
 * @see
 * 
 */
class itaQueueWorkerANPR extends itaQueueWorkerBase implements itaQueueWorker {

    public function getMessageExecuteStrategy() {
        return itaQueueWorkerBase::STRATEGY_REINSERT;
    }

    protected function executeMessage($message) {
        //Nota: $message è un oggetto non un array.
        //Inizializza il messaggio
        $this->setErrorCode(0);

        //istanzia Libs  
        $libNuoviEventi = new cwdLibDanNuoviEventi();
        //$listaVariazioniANPR = $libNuoviEventi->generaVariazioniANPR(10509); //AC_MORTE
        $msg = $message->getData();

        //Chiamo il salvataggio
        $ret = $libNuoviEventi->salvaVariazioniANPR($msg['id'], 0, 1);
        $libNuoviEventi->getMessaggio($param);
        if ($ret == false) {
            $this->setErrorCode(-1);
            $this->setErrorDescription($param['messaggio']);
        } else {
            $this->setErrorCode(0);
            $this->setErrorDescription();
        }

        return $ret;
//        $listaVariazioniANPR = $libNuoviEventi->generaVariazioniANPR($msg['id']);
//
//        foreach ($listaVariazioniANPR as $key => $value) {
//
//            $nomeMetodo = 's' . $value['TIPVAR'] . 'ANPR';
//            if (!method_exists($libNuoviEventi, $nomeMetodo)) { //Il metodo non esiste, si vede che non è previsto
//                continue;
//            }
//
//            $messageOut = "Elaborazione metodo: " . $nomeMetodo;
//            Out::systemEcho($messageOut . " \n", true);
//            App::log(date("d/m/Y H:i:s") . " " . $messageOut, false, true);
//
//            //Richiamo la variazione per ANPR
//            $libNuoviEventi->$nomeMetodo($value, $value['PROGSOGG']);
//            $libNuoviEventi->getMessaggio($param);
//            if ($param['stato'] == false) {
//                $this->setErrorCode(-1);
//                $this->setErrorDescription($param['messaggio']);
//                return false;
//            }
//            
//            
//            $this->setResultData();
//        }
    }

    public function getMaxRetries($message) {
        // il "get" va valorizzato in base al errore e al tipo di messaggio 
        return 2;
    }

}
