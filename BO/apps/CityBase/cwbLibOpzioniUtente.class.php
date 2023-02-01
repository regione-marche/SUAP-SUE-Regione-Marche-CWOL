<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCode.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

class cwbLibOpzioniUtente {

    private $omnisClient;

    public function leggiOpzioniUtente() {
        $this->leggiOpzioniUtenteAnag();
    }
    
    private function leggiOpzioniUtenteAnag() {
        
        // Omnis
//        $this->omnisClient = new itaOmnisClient();
//        $esito = $this->leggiOmnis('S_PARAM_ANAG', 'tv_Obj_Tab_Params.$set_anag', $anag);
        
        // PHP
        $libBge = new cwbLibDB_BGE();
        $anag = $libBge->leggiBgeOpzUtSchema(strtoupper(cwbParGen::getUtente()), 'S_PARAM_ANAG');
        
        if ($anag) {
            cwbParGen::setFormSessionVar('OpzioniUtente', 'anag', $anag);
        }
    }    
    
    public function leggiOmnis($schema, $nomeMetodoScrivi, &$row) {
        $methodArgs[0] = strtoupper(cwbParGen::getUtente());
        $methodArgs[1] = $schema;
        $methodArgs[2] = $nomeMetodoScrivi;

        $result = $this->omnisClient->callExecute('OBJ_BGE_PHP_CODE', 'getOpzioneUtente', $methodArgs, 'CITYWARE', false);
        if ($result['RESULT']['EXITCODE'] == 'S') {
            $row = $result['RESULT']['LIST']['ROW'];
            return true;
        } else {
            //Out::msgStop('Caricamento Opzioni Locali',"Errore ".$result['RESULT']['EXITCODE'].' - Motivo: '.$result['RESULT']['MESSAGE']);
            return false;
        }
    }

}
