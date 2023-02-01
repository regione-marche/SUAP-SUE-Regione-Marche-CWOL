<?php

include_once(ITA_BASE_PATH . '/lib/itaPHPOmnis/itaOmnisClient.class.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';

/**
 * Interfaccia con Motore dei Testi CW
 *
 * @author m.biagioli
 */
class cwbLibMotoreTesti {
    
    private $omnisClient;
    private $errorCode;
    private $errorDescription;
    
    public function __construct() {
        $this->omnisClient = new itaOmnisClient();
    }
    
    /**
     * Risolve testo
     * @param int $codiceTesto Codice Testo
     * @param boolean $convertToPdf Se true, converte il documento in pdf
     * @return type
     */
    public function risolviTesto($codiceTesto, $datiCaricamentoListaGuida, $convertToPdf = false) {
        $this->setError(0, '');
        
        // Precondizioni
        if (!$codiceTesto) {
            $this->setError(-1, 'Codice testo non specificato');
            return null;
        }
        
        // TODO: Sistemare chiamata Omnis
//        $methodArgs = array();
//        $methodArgs[0] = $codiceTesto;
//        $methodArgs[1] = 'apr4';
//        $methodArgs[2] = $datiCaricamentoListaGuida;
//        $result = $this->omnisClient->callExecute('OBJ_BGE_PHP_STAMPE', 'stampaTesto', $methodArgs, 'CITYWARE', false);        
//        
//        // TODO: prendere il binario
//        $content = null;
        
        // Restituisce un testo mock
        $content = file_get_contents("F:/sample.rtf");        
        
        // Controlla se deve effettuare la conversione in pdf
        if ($convertToPdf) {
            $content = $this->rtfToPdf($content);            
        }
        
        return $content;
    }
    
    /**
     * Effettua la conversione del testo da rtf a pdf
     * @param string $binRtf Documento in formato rtf (binary)
     * @param string $pathPdf Path del documento su cui salvare il documento convertito in pdf
     *      (se non specificato, calcola il nome internamente)
     * @return string Documento in formato pdf (binary)
     */
    public function rtfToPdf($binRtf) {                
        $this->setError(0, '');
        
        // Precondizioni        
        if (!$binRtf) {
            $this->setError(-1, "Documento rtf non valido");
            return null;
        }
        
        // Converte il contenuto del file rtf in hex
        $hexContent = cwbLibOmnis::convertStringToHex($binRtf);
        if (!$hexContent) {
            $this->setError(-2, "Errore conversione rtf in hex");
            return null;
        }
                
        // Effettua chiamata a Omnis
        $methodArgs = array();
        $methodArgs[0] = $hexContent;
        $omnisResult = $this->omnisClient->callExecute('OBJ_DAN_PHP_STAMPE', 'rtfTopdf', $methodArgs, 'CITYWARE', false);
        
        // Controlla risultato della chiamata a OWS
        if ($omnisResult['RESULT']['EXITCODE'] !== 'S') {
            $this->setError(-3, $omnisResult['RESULT']['MESSAGE']);
            return null;
        }
        
        // Converte il testo da hex a binary
        $pdfHex = $omnisResult['RESULT']['LIST']['ROW']['TESTO'];        
        return cwbLibOmnis::convertHexToString($pdfHex);        
    }
    
    private function setError($errorCode, $errorDescription) {
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorDescription() {
        return $this->errorDescription;
    }

}

?>