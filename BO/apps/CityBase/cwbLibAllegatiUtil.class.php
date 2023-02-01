<?php

class cwbLibAllegatiUtil {
    
    /**
     * Apre finestra allegati
     * @param string $contesto Contesto di utilizzo
     * @param array $chiaveTestata Chiave testata
     * @param array $metadati Metadati aggiuntivi
     * @param array $config Configurazioni specifiche
     */
    public static function apriFinestraAllegati($contesto, $chiaveTestata, $metadati = array(), $config = array(), $viewMode=false) {
        $datiProvenienza = array(
            'CONTESTO' => $contesto,
            'CHIAVE_TESTATA' => $chiaveTestata,
            'METADATI' => $metadati,
            'CONFIG' => $config
        );
        
        $model = "cwbGestioneAllegati";
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model, $model);
        if (!$formObj) {
            Out::msgStop("Errore", "apertura dettaglio fallita");
            return;
        }
        $formObj->setViewMode($viewMode);
        $formObj->setDatiProvenienza($datiProvenienza);
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

}
