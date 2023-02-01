<?php

/**
 *
 * GESTIONE VARIABILI APPLICATIVO CITY-BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    City-Base
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    29.11.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibVarUtils.class.php';

class cwbLibVariabili {    
    
    const LOGO_EXT = 'png';
    const FIRMA_EXT = 'png';
    
    private $libDB_BTA;
    private $libDB_BOR;
    private $libDB_BGE;
    private $datiBase = null;
    
    public function __construct() {
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->libDB_BOR = new cwbLibDB_BOR();        
        $this->libDB_BGE = new cwbLibDB_BGE();
    }
    
// <editor-fold defaultstate="collapsed" desc="METODI getLegenda PER POPOLAMENTO DIZIONARIO ONLY OFFICE">
    /**
     * Ritorna una legenda di campi base da includere in tutti i dizionari
     * @param string $tipo Tipo
     * @param string $markup Markup
     * @return string legenda
     */
    public function getLegendaBase($tipo = "adjacency", $markup = 'smarty') {
        switch ($tipo) {
            case "adjacency":
                return $this->getCampiBase()->exportAdjacencyModel($markup);
            case "json":
                return $this->getCampiBase()->getDictionaryJSON($markup);
            default:
                break;
        }
    }   

// </editor-fold> 
        
// <editor-fold defaultstate="collapsed" desc="METODI getCampi DELLE SINGOLE SEZIONI">
    /**
     * Restituisce il dizionario dei campi base
     * @return \itaDictionary
     */
    public function getCampiBase() {
        $i = 0;
        $dizionario = new itaDictionary();
        
        // Dati ente
        $dizionario->addField('ENTI_DESENTE', 'Ente - Descrizione', $i++, 'base', '@{$ENTI_DESENTE}@');
        $dizionario->addField('ENTI_PROVINCIA', 'Ente - Provincia', $i++, 'base', '@{$ENTI_PROVINCIA}@');
        $dizionario->addField('ENTI_INDIRENTE', 'Ente - Indirizzo', $i++, 'base', '@{$ENTI_INDIRENTE}@');
        $dizionario->addField('ENTI_CODENTE', 'Ente - Codice ISTAT', $i++, 'base', '@{$ENTI_CODENTE}@');
        $dizionario->addField('ENTI_PARTIVA', 'Ente - Partita IVA', $i++, 'base', '@{$ENTI_PARTIVA}@');
        $dizionario->addField('ENTI_CODFISCALE', 'Ente - Codice Fiscale', $i++, 'base', '@{$ENTI_CODFISCALE}@');
        $dizionario->addField('ENTI_LOGO', 'Ente - Logo', $i++, 'base', '@{$ENTI_LOGO}@');
        $dizionario->addField('ENTI_CAP', 'Ente - CAP', $i++, 'base', '@{$ENTI_CAP}@');
        $dizionario->addField('ENTI_TELEFONO1', 'Ente - Telefono', $i++, 'base', '@{$ENTI_TELEFONO1}@');
        $dizionario->addField('ENTI_TELEFONO2', 'Ente - Secondo telefono', $i++, 'base', '@{$ENTI_TELEFONO2}@');
        $dizionario->addField('ENTI_CODATECO7', 'Ente - Codice attività', $i++, 'base', '@{$ENTI_CODATECO7}@');
        $dizionario->addField('ENTI_NATGIURID', 'Ente - Natura giuridica', $i++, 'base', '@{$ENTI_NATGIURID}@');

        $dizionario->addField('CLIENT_PARTIVA', 'Cliente - Partita IVA', $i++, 'base', '@{$CLIENT_PARTIVA}@');
        $dizionario->addField('CLIENT_CODFISCALE', 'Cliente - Codice Fiscale', $i++, 'base', '@{$CLIENT_CODFISCALE}@');
        $dizionario->addField('CLIENT_CODATTIVE', 'Cliente - Partita IVA', $i++, 'base', '@{$CLIENT_CODATTIVE}@');
        $dizionario->addField('CLIENT_INDIRENTE', 'Cliente - Indirizzo', $i++, 'base', '@{$CLIENT_INDIRENTE}@');
        $dizionario->addField('CLIENT_DESLOCAL', 'Cliente - Luogo', $i++, 'base', '@{$CLIENT_DESLOCAL}@');
        $dizionario->addField('CLIENT_PROVINCIA', 'Cliente - Provincia', $i++, 'base', '@{$CLIENT_PROVINCIA}@');
        $dizionario->addField('CLIENT_CAP', 'Cliente - CAP', $i++, 'base', '@{$CLIENT_CAP}@');
        $dizionario->addField('CLIENT_TELEFONO1', 'Cliente - Telefono', $i++, 'base', '@{$CLIENT_TELEFONO1}@');
        $dizionario->addField('CLIENT_TELEFONO2', 'Cliente - Secondo telefono', $i++, 'base', '@{$CLIENT_TELEFONO2}@');
        $dizionario->addField('CLIENT_CODATECO7', 'Cliente - Codice attività', $i++, 'base', '@{$CLIENT_CODATECO7}@');
        $dizionario->addField('CLIENT_NATGIURID', 'Cliente - Natura giuridica', $i++, 'base', '@{$CLIENT_NATGIURID}@');
        $dizionario->addField('CLIENT_CODENTEMMI', 'Cliente - Codice ente modelli ministeriali', $i++, 'base', '@{$CLIENT_CODENTEMMI}@');

        $dizionario->addField('UTENTI_CODUTE', 'Utente - Codice utente', $i++, 'base', '@{$UTENTI_CODUTE}@');
        $dizionario->addField('UTENTI_NOMEUTE', 'Utente - Nominativo', $i++, 'base', '@{$UTENTI_NOMEUTE}@');
        $dizionario->addField('UTENTI_RAGSOC', 'Utente - Ragione Sociale', $i++, 'base', '@{$UTENTI_RAGSOC}@');
        $dizionario->addField('UTENTI_SIGLAUTE', 'Utente - Sigla utente', $i++, 'base', '@{$UTENTI_SIGLAUTE}@');
        $dizionario->addField('UTENTI_FIRMA', 'Utente - Firma elettronica utente', $i++, 'base', '@{$UTENTI_FIRMA}@');
        $dizionario->addField('UTENTI_RUOLOUTE', 'Utente - Ruolo utente per organigramma', $i++, 'base', '@{$UTENTI_RUOLOUTE}@');
        $dizionario->addField('UTENTI_UFFICIO', 'Utente - Descrizione struttura organizzativa utente', $i++, 'base', '@{$UTENTI_UFFICIO}@');
        
        $dizionario->addField('DATA_ANNOCONT', 'Data - Anno contabile', $i++, 'base', '@{$DATA_ANNOCONT}@');
        $dizionario->addField('DATA_ANNO', 'Data - Anno corrente', $i++, 'base', '@{$DATA_ANNO}@');
        $dizionario->addField('DATA_MESE', 'Data - Data corrente', $i++, 'base', '@{$DATA_MESE}@');
        $dizionario->addField('DATA_GIORNO', 'Data - Giorno corrente', $i++, 'base', '@{$DATA_GIORNO}@');
        $dizionario->addField('DATA_ORA', 'Data - Ora corrente', $i++, 'base', '@{$DATA_ORA}@');
        
        return $dizionario;
    }

    public function getCampiSogg() {
        $i = 0;
        $dizionario = new itaDictionary();

        // Dati soggetto
        $dizionario->addField('RAGSOC', 'Ragione Sociale', $i++, 'base', '@{$RAGSOC}@');
        $dizionario->addField('INDRESIDESTCOMP', 'Indirizzo Residenza Esterno Completo', $i++, 'base', '@{$INDRESIDESTCOMP}@');
        $dizionario->addField('CAPRESID', 'CAP Residenza', $i++, 'base', '@{$CAPRESID}@');
        $dizionario->addField('LOCRESID', 'Località residenza', $i++, 'base', '@{$LOCRESID}@');
        $dizionario->addField('PROVRESID', 'Provincia Residenza', $i++, 'base', '@{$PROVRESID}@');
        $dizionario->addField('PROGSOGG', 'Matricola soggetto', $i++, 'base', '@{$PROGSOGG}@');
        $dizionario->addField('CODFISPARTIVA', 'Codice fiscale o partita IVA', $i++, 'base', '@{$CODFISPARTIVA}@');
        $dizionario->addField('PARTIVA', 'Partita IVA', $i++, 'base', '@{$PARTIVA}@');

        return $dizionario;
    }

// </editor-fold>
    
// <editor-fold defaultstate="collapsed" desc="METODI getVariabili DELLE SINGOLE SEZIONI">
    /**
     * Restituisce i valori del dizionario per il testo da risolvere per il modulo Fattura Attiva
     * @return \itaDictionary
     */
    public function getVariabiliBase() {
        $this->caricaDatiBase();
        $dizionario = $this->getCampiBase();
        
        // Dati Ente
        $dizionario->addFieldData('ENTI_DESENTE', $this->datiBase['dati_ente']['DESENTE']);
        $dizionario->addFieldData('ENTI_PROVINCIA', $this->datiBase['dati_ente']['PROVINCIA']);
        $dizionario->addFieldData('ENTI_INDIRENTE', $this->datiBase['dati_ente']['INDIRENTE']);
        $dizionario->addFieldData('ENTI_CODENTE', $this->datiBase['dati_ente']['CODENTE']);
        $dizionario->addFieldData('ENTI_PARTIVA', $this->datiBase['dati_ente']['PARTIVA']);
        $dizionario->addFieldData('ENTI_CODFISCALE', $this->datiBase['dati_ente']['CODFISCALE']);
        $dizionario->addFieldData('ENTI_LOGO', $this->getLogoEnte());
        $dizionario->addFieldData('ENTI_CAP', $this->datiBase['dati_ente']['CAP']);
        $dizionario->addFieldData('ENTI_TELEFONO1', $this->datiBase['dati_ente']['TELEFONO']);
        $dizionario->addFieldData('ENTI_TELEFONO2', $this->datiBase['dati_ente']['TELEFONO_1']);
        $dizionario->addFieldData('ENTI_CODATECO7', $this->datiBase['dati_ente']['CODATECO7']);
        $dizionario->addFieldData('ENTI_NATGIURID', $this->datiBase['dati_ente']['NATGIURID']);
        
        $dizionario->addFieldData('CLIENT_PARTIVA', $this->datiBase['dati_cliente']['PARTIVA']);
        $dizionario->addFieldData('CLIENT_CODFISCALE', $this->datiBase['dati_cliente']['CODFISCALE']);
        $dizionario->addFieldData('CLIENT_CODATTIVE', $this->datiBase['dati_cliente']['CODATTIVE']);
        $dizionario->addFieldData('CLIENT_INDIRENTE', $this->datiBase['dati_cliente']['INDIRENTE']);
        $dizionario->addFieldData('CLIENT_DESLOCAL', $this->datiBase['dati_cliente']['DESLOCAL']);
        $dizionario->addFieldData('CLIENT_PROVINCIA', $this->datiBase['dati_cliente']['PROVINCIA']);
        $dizionario->addFieldData('CLIENT_CAP', $this->datiBase['dati_cliente']['CAP']);
        $dizionario->addFieldData('CLIENT_TELEFONO1', $this->datiBase['dati_cliente']['TELEFONO']);
        $dizionario->addFieldData('CLIENT_TELEFONO2', $this->datiBase['dati_cliente']['TELEFONO_1']);
        $dizionario->addFieldData('CLIENT_CODATECO7', $this->datiBase['dati_cliente']['CODATECO7']);
        $dizionario->addFieldData('CLIENT_NATGIURID', $this->datiBase['dati_cliente']['NATGIURID']);
        $dizionario->addFieldData('CLIENT_CODENTEMMI', $this->datiBase['dati_cliente']['CODENTEMMI']);
        
        $dizionario->addFieldData('UTENTI_CODUTE', $this->datiBase['dati_utente']['CODUTE']);
        $dizionario->addFieldData('UTENTI_NOMEUTE', $this->datiBase['dati_utente']['NOMEUTE']);
        $dizionario->addFieldData('UTENTI_RAGSOC', $this->datiBase['dati_utente']['NOMEUTE']);
        $dizionario->addFieldData('UTENTI_SIGLAUTE', $this->datiBase['dati_utente']['SIGLAUTE']);
        $dizionario->addFieldData('UTENTI_FIRMA', $this->getFirmaUtente());
        $dizionario->addFieldData('UTENTI_RUOLOUTE', $this->datiBase['dati_uteorg']['RUOLOUTE']);
        $dizionario->addFieldData('UTENTI_UFFICIO', $this->datiBase['dati_uteorg']['DESPORG']);
        
        $dizionario->addFieldData('DATA_ANNOCONT', cwbParGen::getAnnoContabile());
        $dizionario->addFieldData('DATA_ANNO', date('Y'));
        $dizionario->addFieldData('DATA_MESE', date('m'));
        $dizionario->addFieldData('DATA_GIORNO', date('d'));
        $dizionario->addFieldData('DATA_ORA', date('H:i:s'));
                
        return $dizionario;
    }

    public function getVariabiliSogg() {
        $dizionario = $this->getCampiSogg();

        // Dati soggetto
        $dizionario->addFieldData('RAGSOC', $this->datiBase['dati_cliente']['RAGSOC']);
        $dizionario->addFieldData('INDRESIDESTCOMP', $this->getFatAttIndirizzoResidenzaEsternoCompleto($this->datiBase['dati_cliente']));
        $dizionario->addFieldData('CAPRESID', $this->datiBase['dati_cliente']['CAPRE']);
        $dizionario->addFieldData('LOCRESID', $this->getFatAttLocalitaResidenza($this->datiBase['dati_cliente']));
        $dizionario->addFieldData('PROVRESID', $this->getFatAttProvResidenza($this->datiBase['dati_cliente']));
        $dizionario->addFieldData('PROGSOGG', $this->datiBase['dati_cliente']['PROGSOGG']);
        $dizionario->addFieldData('CODFISPARTIVA', $this->getFatAttCodFiscPartiva($this->datiBase['dati_cliente']));
        $dizionario->addFieldData('PARTIVA', $this->datiBase['dati_cliente']['PARTIVA']);


        return $dizionario;
    }

// </editor-fold>
    
// <editor-fold defaultstate="collapsed" desc="FUNZIONI PER GENERAZIONE DI CAMPI CALCOLATI">
    private function getLogoEnte() {        
        $logo = $this->libDB_BGE->leggiLogoEnte();
        if(empty($logo)){
            return '';
        }            
        return itaImg::base64src($logo, self::LOGO_EXT);
    }
    
    private function getFirmaUtente(){
        $firma = $this->libDB_BOR->leggiFirmaUtente();
        if($firma === false){
            return '';
        }
        return itaImg::base64src($firma, self::FIRMA_EXT);
    }
    
    private function getFatAttIndirizzoResidenzaEsternoCompleto($dati) {
        if ($dati['NUMCIVRE'] > 0) {
            $indirizzo = $dati['DESVIARE'] . ' n. ' . $dati['NUMCIVRE'];
        } else {
            $indirizzo = $dati['DESVIARE'];
        }
        if ($dati['NUMCIVRE'] > 0) {
            if (strlen(trim($dati['SUBNUMCIVRE']) > 0 && strpos($dati['SUBNUMCIVRE'], '/'))) {
                $barra = "/";
            }
        }
        $indirizzo .= $barra . $dati['SUBNUMCIVRE'];

        return $indirizzo;
    }
    
    private function getFatAttLocalitaResidenza($dati) {
        $local = $this->libDB_BTA->leggiBtaLocalChiave($dati['CODNAZPRRE'], $dati['CODLOCALRE']);
        if (!$local) {
            return '';
        }
        return $local['DESLOCAL'];
    }

    private function getFatAttProvResidenza($dati) {
        $local = $this->libDB_BTA->leggiBtaLocalChiave($dati['CODNAZPRRE'], $dati['CODLOCALRE']);
        if (!$local) {
            return '';
        }
        return $local['PROVINCIA'];
    }

    private function getFatAttCodFiscPartiva($dati) {
        return $dati['CODFISCALE'] ?: $dati['PARTIVA'];
    }

// </editor-fold>

// <editor-fold defaultstate="collapsed" desc="FUNZIONI PER CARICAMENTO BLOCCO DATI">
    private function caricaDatiBase() {
        if(empty($this->datiBase)){
            $this->datiBase = array();

            $this->datiBase['dati_cliente'] = $this->libDB_BOR->leggiBorClient(array(), false);
            $this->datiBase['dati_ente'] = $this->libDB_BOR->leggiBorEnti(array(), false);

            $filtriUte = array('CODUTE_key'=>strtoupper(trim(cwbParGen::getUtente())));
            $this->datiBase['dati_utente'] = $this->libDB_BOR->leggiBorUtenti($filtriUte, false);

            $filtriUte = array(
                'KEY_CODUTE'=>strtoupper(trim(cwbParGen::getUtente())),
                'ATTIVO'=>true
            );
            $this->datiBase['dati_uteorg'] = $this->libDB_BOR->leggiBorUteorg($filtriUte, false);
        }
    }

    public function caricaDatiSogg($progsogg){
        if(!isSet($this->datiFatturaAttiva)){
            $this->datiFatturaAttiva = array();
        }
        
        $libDB_BTA = new cwbLibDB_BTA_SOGG();
        $this->datiFatturaAttiva['dati_soggetto'] = $libDB_BTA->leggiBtaSoggChiave($progsogg);
    }

// </editor-fold>
}
