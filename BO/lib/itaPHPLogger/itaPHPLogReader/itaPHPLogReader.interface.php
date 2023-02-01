<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaPHPLogReader
 *
 * @author f.margiotta
 */
interface itaPHPLogReaderInterface {
    const LOG_ORDER_ASC = 0;
    const LOG_ORDER_DESC = 1;
    
    const LOG_LEVEL_DEBUG = 100;
    const LOG_LEVEL_INFO = 200;
    const LOG_LEVEL_NOTICE = 250;
    const LOG_LEVEL_WARNING = 300;
    const LOG_LEVEL_ERROR = 400;
    const LOG_LEVEL_CRITICAL = 500;
    const LOG_LEVEL_ALERT = 550;
    const LOG_LEVEL_EMERGENCY = 600;
    
    /**
     * Path del file su cui è salvato il log
     * @param string $path
     */
    public function __construct($path);
    
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
    public function getLogs($order=self::LOG_ORDER_DESC,$start=null,$limit=null);
    
    /**
     * Ritorna il numero di righe presenti nel file di log
     */
    public function countRows();
    
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
    public function getRow($rowId);
}
