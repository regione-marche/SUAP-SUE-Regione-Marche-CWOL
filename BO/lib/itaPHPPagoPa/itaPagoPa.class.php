<?php

/**
 *
 * Classe di utils per la gestione di pagopa
 * 
 */
require_once ITA_LIB_PATH . '/itaPHPPagoPa/iPagoPa.php';
require_once ITA_LIB_PATH . '/itaException/ItaException.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaHelper.php';
require_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

class itaPagoPa implements iPagoPa {

    const EFILL_TYPE = 1;
    const NEXTSTEPSOLUTION_TYPE = 2;
    const EFILL_ZZ_TYPE = 3;
    const MPAY_TYPE = 4;
    const ALTOADIGE_RISCO_TYPE = 5;

    private $intermediario;

    public function __construct($type = null) {
        $this->initInterm($type);
    }

    public function sincronizzazioneNodo() {
        $esito = true;
        $msgError = '';

        $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE);
        $timeStampAttivo = $cache->get('itaPagoPaSincronizza');
        if ($timeStampAttivo) {
            $diffmin = (time() - $timeStampAttivo) / 60;
        }

        // se c'è già un invio attivo non ne faccio partire un altro
        // se dopo 2 ore ancora è attivo allora sarà piantato, quindi puo' ripartire
        if ($diffmin && $diffmin < 120) {
            $msgError = 'Impossibile sincronizzare. Sincronizzazione già attiva!';
            $esito = false;
        } else {
            //  blocco gli altri
            if ($timeStampAttivo) {
                $cache->delete('itaPagoPaSincronizza');
            }
            $cache->set('itaPagoPaSincronizza', time());

//        if (!$this->inserimentoMassivo(array(), array(), array(), false, true) && $this->intermediario->getLastErrorCode() != -2) {
//            $esito = false;
//            $msgError .= "Inserimento Massivo " . $this->intermediario->getLastErrorDescription();
//        }
            if (!$this->cancellazioneMassiva()) {
                $esito = false;
                $msgError .= "Cancellazione Massiva " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->ricevutaAccettazioneCancellazione()) {
                $esito = false;
                $msgError .= "Ricevuta Accettazione Cancellazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->ricevutaCancellazione()) {
                $esito = false;
                $msgError .= "Ricevuta Cancellazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->elaborazioneScadenzeScartate(array())) {
                $esito = false;
                $msgError .= "Elaborazione Scadenze Scartate " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->pubblicazioneMassiva()) {
                $esito = false;
                $msgError .= "Pubblicazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->ricevutaAccettazionePubblicazione()) {
                $esito = false;
                $msgError .= "Ricevuta Accettazione Pubblicazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->ricevutaPubblicazione()) {
                $esito = false;
                $msgError .= "Ricevuta Pubblicazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->ricevutaArricchita()) {
                $esito = false;
                $msgError .= "Ricevuta Arricchita " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->rendicontazione()) {
                $esito = false;
                $msgError .= "Rendicontazione " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->riversamenti()) {
                $esito = false;
                $msgError .= "Riversamento " . $this->intermediario->getLastErrorDescription();
            }
            if (!$this->riconciliazione()) {
                $esito = false;
                $msgError .= "Riconciliazione " . $this->intermediario->getLastErrorDescription();
            }
        }

        $cache->delete('itaPagoPaSincronizza');
        return array(
            'ESITO' => $esito,
            'MSG_ERRORE' => $msgError
        );
    }

    private function initInterm($type) {
        switch ($type) {
            case self::EFILL_TYPE:
                require_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaEfil.class.php');
                $this->intermediario = new cwbPagoPaEfil();
                break;
            case self::NEXTSTEPSOLUTION_TYPE:
                require_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaNextStepSolution.class.php');
                $this->intermediario = new cwbPagoPaNextStepSolution();
                break;
            case self::EFILL_ZZ_TYPE:
                require_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaEfilZZ.class.php');
                $this->intermediario = new cwbPagoPaEfilZZ();
                break;
            case self::MPAY_TYPE:
                require_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMPay.class.php');
                $this->intermediario = new cwbPagoPaMPay();
                break;
            case self::ALTOADIGE_RISCO_TYPE:
                require_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaAltoAdigeRisco.class.php');
                $this->intermediario = new cwbPagoPaAltoAdigeRisco();
                break;
        }
    }

    /**
     * Inserisce su bge_agid_scadenze e pubblica la posizione in modalità puntuale (efil-> ws, nss->ws)
     * 
     * @param int $codtipscad
     * @param int $subtipscad
     * @param int $progcitysc
     * @param int $annorif
     * @param int $progcitysca
     * @param int $annorif
     * @return $infoAggiuntive
     */
    public function pubblicazioneSingolaDaChiavePendenza($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca = null, $infoAggiuntive = null, $progsoggex = null) {
        $params = array(
            'Progcitysc' => $progcitysc,
            'Progcitysca' => $progcitysca,
            'CodTipScad' => $codtipscad,
            'SubTipScad' => $subtipscad
        );
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->pubblicazioneSingolaDaChiavePendenza($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca, $infoAggiuntive, $progsoggex);
    }

    /**
     * Inserisce su bge_agid_scadenze e pubblica la posizione in modalità puntuale gestendo le eventuali rate e soggetto esterno (efil-> ws, nss->ws)
     * 
     * @param array $pendenze
     */
    public function pubblicazioneSingolaDaChiavePendenzaConRate($pendenze) {
        $this->checkIntermediarioDaPendenza($pendenze[0]);
        $this->cleanError();
        return $this->intermediario->pubblicazioneSingolaDaChiavePendenzaConRate($pendenze);
    }

    /**
     * Inserisce su bge_agid_scadenze già pagata e pubblica la posizione in modalità puntuale(efil-> ws, nss->ws)
     * e crea la bge_agid_risco
     * 
     * @param array $pendenze
     */
    public function pubblicazioneSingolaPagataDaPendenza($pendenza) {
        $this->checkIntermediarioDaPendenza($pendenza);
        $this->cleanError();
        return $this->intermediario->pubblicazioneSingolaPagataDaPendenza($pendenza);
    }

    /**
     * Inserisce su bwe_penden (se $insertPenden = true), su bge_agid_scadenze e 
     * pubblica la posizione in modalità puntuale (efil-> ws, nss->ws)
     * 
     * @param array $pendenza la pendenza (bwe_penden), sotto la chiave PENDETT vanno messi tutte i record di dettaglio della pendenza (BWE_PENDDET), valorizzati solo se $insertPenden =true 
     * @param boolean $insertPenden 
     *                          true = la pendenza passata va inserita su bwe_penden, 
     *                          false = la pendenza passata esiste già su bwe_penden e non va reinserita
     * @return result
     */
    public function pubblicazioneSingolaDaPendenza($pendenza, $insertPenden = true) {
        $this->checkIntermediarioDaPendenza($pendenza);
        $this->cleanError();
        return $this->intermediario->pubblicazioneSingolaDaPendenza($pendenza, $insertPenden);
    }

    private function checkIntermediarioDaPendenza($pendenza) {
        $params = array(
            'AnnoEmi' => $pendenza['ANNOEMI'],
            'NumEmi' => $pendenza['NUMEMI'],
            'IdBolSere' => $pendenza['IDBOL_SERE'],
            'Progcitysc' => $pendenza['PROGCITYSC'],
            'CodTipScad' => $pendenza['CODTIPSCAD'],
            'SubTipScad' => $pendenza['SUBTIPSCAD']
        );
        $this->checkIntermediario($params);
    }

    /**
     * Inserisce su bwe_penden (se $insertPenden = true), su bge_agid_scadenze e 
     * pubblica le posizioni in modalità massiva (efil->ftp,nss->ftp, etc...)
     * 
     * @param array() $pendenze la lista delle pendenze (bwe_penden), sotto la chiave PENDETT vanno messi tutte i record di dettaglio della pendenza (BWE_PENDDET), valorizzati solo se $insertPenden =true 
     * @param boolean $insertPenden 
     *                          true = la pendenza passata va inserita su bwe_penden, 
     *                          false = la pendenza passata esiste già su bwe_penden e non va reinserita
     * @return result
     */
    public function pubblicazioneMassivaDaPendenze($pendenze, $insertPenden = true) {
        // cerco l'intermediario se non è settato
        $this->checkIntermediarioDaPendenza($pendenze[0]);
        $this->cleanError();
        return $this->intermediario->pubblicazioneMassivaDaPendenze($pendenze, $insertPenden);
    }

    /**
     * Inserisce su bge_agid_scadenze e 
     * pubblica le posizioni in modalità massiva (efil->ftp,nss->ftp, etc...)
     * 
     * @param int $annoEmi
     * @param int $numEmi
     * @param int $idBolSere
     * 
     * @return result
     */
    public function pubblicazioneMassivaDaChiaveEmissione($annoEmi, $numEmi, $idBolSere) {
        // cerco l'intermediario se non è settato
        $params = array(
            'AnnoEmi' => $annoEmi,
            'NumEmi' => $numEmi,
            'IdBolSere' => $idBolSere
        );
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->pubblicazioneMassivaDaChiaveEmissione($annoEmi, $numEmi, $idBolSere);
    }

    /**
     * Carica tutte le pendenze da pubblicare, inserisce su bge_agid_scadenze e 
     * pubblica le posizioni in modalità massiva (efil->ftp,nss->ftp, etc...)
     * 
     * @return result
     */
    public function pubblicazioneMassiva() {
        $this->cleanError();
        return $this->intermediario->pubblicazioneMassiva();
    }

    /**
     * Carica tutte le pendenze da pubblicare, inserisce su bge_agid_scadenze e tba_dati_scambio senza pubblicare
     * sul nodo
     * 
     * @return result
     */
    public function inserimentoMassivo() {
        $this->cleanError();
        return $this->intermediario->inserimentoMassivo();
    }

    /**
     * Inserisce solo la bwe_penden
     * 
     * @return result
     */
    public function inserisciPendenze(&$pendenze) {
        $this->cleanError();
        return $this->intermediario->inserisciPendenze($pendenze);
    }

    /**
     * Inserisce bge_Agid_soggetti
     * 
     * @return result
     */
    public function inserisciAgidSoggetto($soggetto) {
        $this->cleanError();
        return $this->intermediario->inserisciAgidSoggetto($soggetto);
    }

    /**
     * pubblica le posizioni presenti con stato = 1 su bge_agid_scadenze
     * in modalità massiva (efil->ftp,nss->ftp, etc...)
     * 
     * @return result
     */
    public function pubblicazioneScadenzeCreateMassiva() {
        $this->cleanError();
        return $this->intermediario->pubblicazioneScadenzeCreateMassiva();
    }

    public function leggiIUV() {
        $this->cleanError();
        return $this->intermediario->leggiIUV();
    }

    public function ricevutaAccettazionePubblicazione() {
        $this->cleanError();
        return $this->intermediario->ricevutaAccettazionePubblicazione();
    }

    public function ricevutaPubblicazione() {
        $this->cleanError();
        return $this->intermediario->ricevutaPubblicazione();
    }

    public function ricevutaArricchita() {
        $this->cleanError();
        return $this->intermediario->ricevutaArricchita();
    }

    public function ricevutaAccettazioneCancellazione() {
        $this->cleanError();
        return $this->intermediario->ricevutaAccettazioneCancellazione();
    }

    public function ricevutaCancellazione() {
        $this->cleanError();
        return $this->intermediario->ricevutaCancellazione();
    }

    public function rendicontazione($params) {
        $this->cleanError();
        return $this->intermediario->rendicontazione($params);
    }

    public function riversamenti($params) {
        $this->cleanError();
        return $this->intermediario->riversamenti($params);
    }

    public function riconciliazione() {
        $this->cleanError();
        return $this->intermediario->riconciliazione();
    }

    public function cancellazioneMassiva() {
        $this->cleanError();
        return $this->intermediario->cancellazioneMassiva();
    }

    public function elaborazioneScadenzeScartate($filtri) {
        $this->cleanError();
        return $this->intermediario->elaborazioneScadenzeScartate($filtri);
    }

    public function reinvioPubblicazione($progkeytabInvio) {
        $this->cleanError();
        $this->intermediario->reinvioPubblicazione($progkeytabInvio);
    }

    public function getDatiPagamentoDaIUV($IUV) {
        $this->cleanError();
        return $this->intermediario->getDatiPagamentoDaIUV($IUV);
    }

    /**
     * Ricerca una posizione da iuv
     * 
     * @param String $IUV
     * @return posizione
     */
    public function ricercaPosizioneDaIUV($IUV) {
        $param = array();
        $param['CodiceIdentificativo'] = $IUV;
        $this->checkIntermediario($param);
        $this->cleanError();
        return $this->intermediario->ricercaPosizioneDaIUV($IUV);
    }

    /**
     * Ricerca n posizioni Da Info Aggiuntive 
     * 
     * @param array info aggiuntive
     * @return posizione
     */
    public function ricercaPosizioniDaInfoAggiuntive($params) {
        $this->cleanError();
        return $this->intermediario->ricercaPosizioniDaInfoAggiuntive($params);
    }

    /**
     * Ricerca n posizioni Da Info Aggiuntive 
     * 
     * @param array info aggiuntive
     * @return posizione
     */
    public function ricercaPosizioniDataPagamDaA($dataDa, $dataA, $codtipscad, $subtipscad) {
        $this->cleanError();
        return $this->intermediario->ricercaPosizioniDataPagamDaA($dataDa, $dataA, $codtipscad, $subtipscad);
    }

    /**
     * Ricerca una posizione da iuv
     * 
     * @param String $IUV
     * @return posizione
     */
    public function ricercaPosizioneChiaveEsterna($codtipscad, $subtipscad, $progcitysc, $annorif, $numRata, $progcitysca = null) {
        $this->cleanError();
        return $this->intermediario->ricercaPosizioneChiaveEsterna($codtipscad, $subtipscad, $progcitysc, $annorif, $numRata, $progcitysca);
    }

    /**
     * Ricerca una posizione da cf o piva
     * 
     * @param String $CODFISCALE
     * @return posizione
     */
    public function ricercaPosizioneCFPI($params) {
        $this->cleanError();
        return $this->intermediario->ricercaPosizioneCFPI($params);
    }

    /*
     * aggiorna i dati di una posizione già pubblicata
     * 
     * @param String $IUV
     * @param array $toUpdate chiave/valore dei dati da aggiornare
     * @return result      
     *  
     */

    public function rettificaPosizioneDaIUV($IUV, $toUpdate) {
        $this->cleanError();
        return $this->intermediario->rettificaPosizioneDaIUV($IUV, $toUpdate);
    }

    /**
     * genera il bollettino per il pagamento
     * @param type $codtipscad
     * @param type $subtipscad
     * @param type $annorif
     * @param type $progcitysc
     * @param type $numrata
     * @return type
     */
    public function generaBollettinoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata) {
        $params = array(
            'CODTIPSCAD' => $codtipscad,
            'SUBTIPSCAD' => $subtipscad
        );
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->generaBollettinoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata);
    }

    /**
     * genera il bollettino per il pagamento
     * 
     * @param string $iuv
     * @return type
     */
    public function generaBollettinoDaIUV($iuv) {
        $params = array(
            'CodiceIdentificativo' => $iuv
        );
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->generaBollettinoDaIUV($iuv);
    }

    function getSimulazione() {
        return $this->intermediario->getSimulazione();
    }

    function setSimulazione($simulazione) {
        $this->intermediario->setSimulazione($simulazione);
    }

    function getSimulazioneSF() {
        return $this->intermediario->getSimulazioneSF();
    }

    function setSimulazioneSF($simulazioneSF) {
        $this->intermediario->setSimulazioneSF($simulazioneSF);
    }

    public function testConnection($massivo = true, $tipoChiamata = null) {
        return $this->intermediario->testConnection($massivo, $tipoChiamata);
    }

    public function fileDaElaborare($stato, $tipo, $codServizio, $confEfil, $cartella) {
        return $this->intermediario->fileDaElaborare($stato, $tipo, $codServizio, $confEfil, $cartella);
    }

    public function getConfIntermediario() {
        $this->cleanError();
        return $this->intermediario->getConfIntermediario();
    }

    public function getServizioIntermediario($codtipscad, $subtipscad) {
        $this->cleanError();
        return $this->intermediario->getServizioIntermediario($codtipscad, $subtipscad);
    }

    public function getEmissioneDaIUV($iuv) {
        $this->cleanError();
        return $this->intermediario->getEmissioneDaIUV($iuv);
    }

    public function eseguiPagamentoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $urlReturn) {
        $this->cleanError();
        return $this->intermediario->eseguiPagamentoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $urlReturn);
    }

    public function eseguiPagamentoDaIuv($iuv, $urlReturn, $redirectVerticale = 0) {
        $this->cleanError();
        return $this->intermediario->eseguiPagamentoDaIuv($iuv, $urlReturn, $redirectVerticale);
    }

    public function recuperaIUV($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $progcitysca = null) {
        $this->cleanError();
        return $this->intermediario->recuperaIUV($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $progcitysca);
    }

    public function rimuoviPosizione($params) {
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->rimuoviPosizione($params);
    }

    public function rimuoviPosizioni($idRuolo) {
        $params['TipoIntermediario'] = self::NEXTSTEPSOLUTION_TYPE;
        $this->checkIntermediario($params);
        $this->cleanError();
        return $this->intermediario->rimuoviPosizioni($idRuolo);
    }

    public function scaricaPagamentoRT($params) {
        $this->cleanError();
        return $this->intermediario->scaricaPagamentoRT($params);
    }

    public function recuperaRicevutaPagamento($iuv, $arricchita) {
        $this->cleanError();
        return $this->intermediario->recuperaRicevutaPagamento($iuv, $arricchita);
    }

    function getLastErrorCode() {
        return $this->intermediario->getLastErrorCode();
    }

    function getLastErrorDescription() {
        return $this->intermediario->getLastErrorDescription();
    }

    private function cleanError() {
        $this->intermediario->handleError(null, "");
    }

    // non usare, mantenuto solo per retrocompatibilita
    public function rimuoviPosizioneEPenden($iuv) {
        $this->checkIntermediario(array('CodiceIdentificativo' => $iuv));
        $this->cleanError();
        return $this->intermediario->rimuoviPosizioneEPenden($iuv);
    }

    public function rendicontazionePuntuale($params) {
        $this->cleanError();
        return $this->intermediario->rendicontazionePuntuale($params);
    }

    private function checkIntermediario($param) {
        if (!$this->intermediario) {
            $interm = cwbPagoPaHelper::getIntermediario($param);
            $this->initInterm($interm);
            if (!$this->intermediario) {
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Tipo intermediario non specificato");
            }
        }
    }

}

?>