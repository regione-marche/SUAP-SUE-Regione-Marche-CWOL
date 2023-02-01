<?php
require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogReader/itaPHPLogReader.interface.php';

use Dubture\Monolog\Reader\LogReader;

class itaPHPLogReaderMonologFile implements itaPHPLogReaderInterface{
    private $reader;
    
    public function __construct($path) {
        if(!file_exists($path)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "File di log inesistente");
        }
        $this->reader = new LogReader($path);
    }

    /**
     * 
     * @param const $order LOG_ORDER_DESC o LOG_ORDER ASC
     * @param int $start Riga da cui cominciare a recuperare i log
     * @param int $limit Numero di righe da recuperare
     * @return array righe di log. Ogni riga è un array avete questa struttura:
     *              'rowid'=>id univoco della riga
     *              'timestamp'=>Timestamp della creazione del log
     *              'level'=>livello della riga (LOG_LEVEL_*)
     *              'msg'=>Messaggio di log
     *              'context'=>array contenente informazioni aggiuntive
     */
    public function getLogs($order=itaPHPLogReaderInterface::LOG_ORDER_DESC, $start=null, $limit=null){
        $return = array();
        
        $elements = count($this->reader);
                
        $from = (isSet($start)) ? $start : 0;
        $to   = (isSet($limit)) ? min(array($start+$limit,$elements-1)) : $elements-1;
        
        switch($order){
            case itaPHPLogReaderInterface::LOG_ORDER_ASC:
                for($i=$from;$i<=$to;$i++){
                    $row = $this->reader[$i];
                    
                    $return[] = array(
                        'rowid'     => md5($row['date'].$row['level'].$row['message'].json_encode($row['context'])),
                        'timestamp' => $row['date']->getTimestamp(),
                        'level'     => constant('itaPHPLogReaderInterface::LOG_LEVEL_'.$row['level']),
                        'msg'       => $row['message'],
                        'context'   => $row['context']
                    );
                }
                break;
            case itaPHPLogReaderInterface::LOG_ORDER_DESC:
                for($i=$to;$i>=$from;$i--){
                    $row = $this->reader[$i];
                    
                    if(!is_array($row) || empty($row)) continue;
                    
                    $return[] = array(
                        'rowid'     => md5($row['date']->getTimestamp().$row['level'].$row['message'].json_encode($row['context'])),
                        'timestamp' => $row['date']->getTimestamp(),
                        'level'     => constant('itaPHPLogReaderInterface::LOG_LEVEL_'.$row['level']),
                        'msg'       => $row['message'],
                        'context'   => $row['context']
                    );
                }
                break;
        }
        return $return;
    }
    
    /**
     * Ritorna il numero di righe presenti nel file di log
     */
    public function countRows() {
        return count($reader);
    }
    
    /**
     * Restituisce una singola riga in base al rowid
     * @param string $rowId
     * @return array righe di log. Ogni riga è un array avete questa struttura:
     *              'rowid'=>id univoco della riga
     *              'timestamp'=>Timestamp della creazione del log
     *              'level'=>livello della riga (LOG_LEVEL_*)
     *              'msg'=>Messaggio di log
     *              'context'=>array contenente informazioni aggiuntive
     * @return false In caso la riga non sia stata trovata
     */
    public function getRow($rowId){
        foreach($this->reader as $row){
            $id = md5($row['date'].$row['level'].$row['message'].json_encode($row['context']));
            if($rowId == $id){
                return array(
                    'rowid'     => $id,
                    'timestamp' => $row['date']->getTimestamp(),
                    'level'     => constant('itaPHPLogReaderInterface::LOG_LEVEL_'.$row['level']),
                    'msg'       => $row['message'],
                    'context'   => $row['context']
                );
            }
        }
        return false;
    }

}
?>
