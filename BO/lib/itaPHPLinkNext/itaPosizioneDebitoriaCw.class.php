<?php
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibPagoPaUtils.php';
/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Francesco Margiotta <f.margiotta@palinformatica.it>
 * @license
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaPosizioneDebitoriaCw extends itaPosizioneDebitoria{
    protected $riepilogoIva;
    protected $praticaEsternaPrecedente;
    protected $documento;
    protected $riferimentoRuoloEsterno;
    protected $forceDisableEmptyFields = false;
    
    public function forceDisableEmptyFields($value=false){
        $this->forceDisableEmptyFields = $value;
    }
    
    public function getRiepilogoIva(){
        return $this->riepilogoIva;
    }
    
    public function setRiepilogoIva($riepilogoIva){
        $this->riepilogoIva = $riepilogoIva;
    }
    
    public function getRiferimentoPraticaEsternaPrecedente(){
        return $this->praticaEsternaPrecedente;
    }
    
    public function setRiferimentoPraticaEsternaPrecedente($praticaEsternaPrecedente){
        $this->praticaEsternaPrecedente = $praticaEsternaPrecedente;
    }
    
    public function getDocumento(){
        return $this->praticaEsternaPrecedente;
    }
    
    public function setDocumento($documento){
        $this->documento = $documento;
    }
    
    public function getRiferimentoRuoloEsterno(){
        return $this->praticaEsternaPrecedente;
    }
    
    public function setRiferimentoRuoloEsterno($riferimentoRuoloEsterno){
        $this->riferimentoRuoloEsterno = $riferimentoRuoloEsterno;
    }
    
    public function addDettaglio($dettaglio){
        if(!is_array($this->Dettagli))
            $this->Dettagli = array();
        if(!isSet($dettaglio['ProgressivoDettaglio']))
            $dettaglio['ProgressivoDettaglio'] = count($this->Dettagli)+1;
        $this->Dettagli[] = $dettaglio;
    }
    
    public function cleanUp(&$element){
        if(is_array($element)){
            if(empty($element)){
                unset($element);
            }
            else{
                foreach($element as &$value){
                    $this->cleanUp($value);
                }
            }
        }
        else{
            if(trim($element) == ''){
                unset($element);
            }
        }
    }
    
    public function getRichiesta($namespace = false) {
        if ($namespace) {
            $prefix = $namespace . ":";
        }
        /*
         * preparazione array richiesta
         */
        $richiesta = array();

        // Descrizione - opzionale
        if (cwbLibPagoPaUtils::valorized($this->getDescrizione())) {
            $richiesta["Descrizione"] = $this->getDescrizione();
        }
        // AnnoImposta
        if (cwbLibPagoPaUtils::valorized($this->getAnnoImposta()) || !$this->forceDisableEmptyFields) {
            $richiesta["AnnoImposta"] = $this->getAnnoImposta();
        }
        // Numero
        if (cwbLibPagoPaUtils::valorized($this->getNumero()) || !$this->forceDisableEmptyFields) {
            $richiesta["Numero"] = $this->getNumero();
        }
        // Sezionale - opzionale
        if (cwbLibPagoPaUtils::valorized($this->getSezionale())) {
            $richiesta["Sezionale"] = $this->getSezionale();
        }
        // Note - opzionale
        if (cwbLibPagoPaUtils::valorized($this->getNote())) {
            $richiesta["Note"] = $this->getNote();
        }
        // RiferimentoPraticaEsterna
        if (cwbLibPagoPaUtils::valorized($this->getRiferimentoPraticaEsterna())) {
            $richiesta["RiferimentoPraticaEsterna"] = $this->getRiferimentoPraticaEsterna();
        }
        // ImportoDovuto
        $richiesta["ImportoDovuto"] = 0;
//        if (cwbLibPagoPaUtils::valorized($this->getImportoDovuto()) || !$this->forceDisableEmptyFields) {
//            $richiesta["ImportoDovuto"] = $this->getImportoDovuto();
//        }
        // Annullato
        $richiesta["Annullato"] = $this->getAnnullato() == true ? "1" : "0";
        // GestioneIva
        $richiesta["GestioneIva"] = $this->getGestioneIva() == true ? "1" : "0";
        // NumeroProtocollo
        if (cwbLibPagoPaUtils::valorized($this->getNumeroProtocollo()) || !$this->forceDisableEmptyFields) {
            $richiesta["NumeroProtocollo"] = $this->getNumeroProtocollo();
        }
        // DataProtocollo
        if (cwbLibPagoPaUtils::valorized($this->getDataProtocollo()) || !$this->forceDisableEmptyFields) {
            $richiesta["DataProtocollo"] = $this->getDataProtocollo();
        }
        // DataEmissione
        if (cwbLibPagoPaUtils::valorized($this->getDataEmissione()) || !$this->forceDisableEmptyFields) {
            $richiesta["DataEmissione"] = $this->getDataEmissione();
        }
        // DataInizioPeriodo
        if (cwbLibPagoPaUtils::valorized($this->getDataInizioPeriodo()) || !$this->forceDisableEmptyFields) {
            $richiesta["DataInizioPeriodo"] = $this->getDataInizioPeriodo();
        }
        // DataFinePeriodo
        if (cwbLibPagoPaUtils::valorized($this->getDataFinePeriodo()) || !$this->forceDisableEmptyFields) {
            $richiesta["DataFinePeriodo"] = $this->getDataFinePeriodo();
        }
        // TipoDocumento
        if (cwbLibPagoPaUtils::valorized($this->getTipoDocumento()) || !$this->forceDisableEmptyFields) {
            $richiesta["TipoDocumento"] = $this->getTipoDocumento();
        }
        // Contribuente - opzionale
        if (cwbLibPagoPaUtils::valorized($this->getContribuente())) {
            $contribuente = $this->getContribuente();
            $richiesta["Contribuente"] = array();
            if (cwbLibPagoPaUtils::valorized($contribuente['NaturaGiuridica']) || !$this->forceDisableEmptyFields)
                $richiesta["Contribuente"]["NaturaGiuridica"] = $contribuente['NaturaGiuridica'];
            if (cwbLibPagoPaUtils::valorized($contribuente['RagioneSociale']))
                $richiesta["Contribuente"]["RagioneSociale"] = $contribuente['RagioneSociale'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Cognome']))
                $richiesta["Contribuente"]["Cognome"] = $contribuente['Cognome'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Nome']))
                $richiesta["Contribuente"]["Nome"] = $contribuente['Nome'];
            if (cwbLibPagoPaUtils::valorized($contribuente['CodiceFiscale']))
                $richiesta["Contribuente"]["CodiceFiscale"] = $contribuente['CodiceFiscale'];
            if (cwbLibPagoPaUtils::valorized($contribuente['PartitaIva']))
                $richiesta["Contribuente"]["PartitaIva"] = $contribuente['PartitaIva'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Email']))
                $richiesta["Contribuente"]["Email"] = $contribuente['Email'];
            if (cwbLibPagoPaUtils::valorized($contribuente['CodiceIsoNazionalita']))
                $richiesta["Contribuente"]["CodiceIsoNazionalita"] = $contribuente['CodiceIsoNazionalita'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']) && !empty($contribuente['Residenza'])) {
                $richiesta["Contribuente"]["Residenza"] = array();
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Riferimento']))
                    $richiesta["Contribuente"]["Residenza"]["Riferimento"] = $contribuente['Residenza']['Riferimento'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Comune']))
                    $richiesta["Contribuente"]["Residenza"]["Comune"] = $contribuente['Residenza']['Comune'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Localita']))
                    $richiesta["Contribuente"]["Residenza"]["Localita"] = $contribuente['Residenza']['Localita'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Provincia']))
                    $richiesta["Contribuente"]["Residenza"]["Provincia"] = $contribuente['Residenza']['Provincia'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['CAP']))
                    $richiesta["Contribuente"]["Residenza"]["CAP"] = $contribuente['Residenza']['CAP'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Indirizzo']))
                    $richiesta["Contribuente"]["Residenza"]["Indirizzo"] = $contribuente['Residenza']['Indirizzo'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['NumeroCivico']))
                    $richiesta["Contribuente"]["Residenza"]["NumeroCivico"] = $contribuente['Residenza']['NumeroCivico'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Lettera']))
                    $richiesta["Contribuente"]["Residenza"]["Lettera"] = $contribuente['Residenza']['Lettera'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Residenza']['Km']))
                    $richiesta["Contribuente"]["Residenza"]["Km"] = $contribuente['Residenza']['Km'];
            }
            if (cwbLibPagoPaUtils::valorized($contribuente['Codice']))
                $richiesta["Contribuente"]["Codice"] = $contribuente['Codice'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Pec']))
                $richiesta["Contribuente"]["Pec"] = $contribuente['Pec'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Telefono']))
                $richiesta["Contribuente"]["Telefono"] = $contribuente['Telefono'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Cellulare']))
                $richiesta["Contribuente"]["Cellulare"] = $contribuente['Cellulare'];
            if (cwbLibPagoPaUtils::valorized($contribuente['CodiceIsoCittadinanza']))
                $richiesta["Contribuente"]["CodiceIsoCittadinanza"] = $contribuente['CodiceIsoCittadinanza'];
            if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']) && !empty($contribuente['Domicilio'])) {
                $richiesta["Contribuente"]["Domicilio"] = array();
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Riferimento']))
                    $richiesta["Contribuente"]["Domicilio"]["Riferimento"] = $contribuente['Domicilio']['Riferimento'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Comune']))
                    $richiesta["Contribuente"]["Domicilio"]["Comune"] = $contribuente['Domicilio']['Comune'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Localita']))
                    $richiesta["Contribuente"]["Domicilio"]["Localita"] = $contribuente['Domicilio']['Localita'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Provincia']))
                    $richiesta["Contribuente"]["Domicilio"]["Provincia"] = $contribuente['Domicilio']['Provincia'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['CAP']))
                    $richiesta["Contribuente"]["Domicilio"]["CAP"] = $contribuente['Domicilio']['CAP'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Indirizzo']))
                    $richiesta["Contribuente"]["Domicilio"]["Indirizzo"] = $contribuente['Domicilio']['Indirizzo'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['NumeroCivico']))
                    $richiesta["Contribuente"]["Domicilio"]["NumeroCivico"] = $contribuente['Domicilio']['NumeroCivico'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Lettera']))
                    $richiesta["Contribuente"]["Domicilio"]["Lettera"] = $contribuente['Domicilio']['Lettera'];
                if (cwbLibPagoPaUtils::valorized($contribuente['Domicilio']['Km']))
                    $richiesta["Contribuente"]["Domicilio"]["Km"] = $contribuente['Domicilio']['Km'];
            }
        }
        if(isSet($richiesta["Contribuente"]) && empty($richiesta["Contribuente"]))
            unset($richiesta["Contribuente"]);
        // Rate - opzionale
        $rate = $this->getRate();
        App::log('Rate');
        App::log($rate);
        if (cwbLibPagoPaUtils::valorized($this->getRate())) {
            $richiesta["Rate"] = array();
            foreach ($rate as $key => $Rata) {
                if (cwbLibPagoPaUtils::valorized($Rata['NumeroRata']) && cwbLibPagoPaUtils::valorized($Rata['Importo']) && cwbLibPagoPaUtils::valorized($Rata['Scadenza'])){
                    if (cwbLibPagoPaUtils::valorized($Rata['NumeroRata']) || !$this->forceDisableEmptyFields)
                        $richiesta["Rate"][$key]["NumeroRata"] = $Rata['NumeroRata'];
                    if (cwbLibPagoPaUtils::valorized($Rata['Importo']) || !$this->forceDisableEmptyFields)
                        $richiesta["Rate"][$key]["Importo"] = $Rata['Importo'];
                    if (cwbLibPagoPaUtils::valorized($Rata['Scadenza']) || !$this->forceDisableEmptyFields)
                        $richiesta["Rate"][$key]["Scadenza"] = $Rata['Scadenza'];
                    if (cwbLibPagoPaUtils::valorized($Rata['QuintoCampo']))
                        $richiesta["Rate"][$key]["QuintoCampo"] = $Rata['QuintoCampo'];
                    if (cwbLibPagoPaUtils::valorized($Rata['IUV']))
                        $richiesta["Rate"][$key]["IUV"] = $Rata['IUV'];
                }
            }
        }
        if(isSet($richiesta["Rate"]) && empty($richiesta["Rate"]))
            unset($richiesta["Rate"]);
        
        // Dettagli - opzionale
        if (cwbLibPagoPaUtils::valorized($this->getDettagli())){
            $dettagli = $this->getDettagli();
            $richiesta["Dettagli"] = array();
            foreach($dettagli as $key => $Dettaglio) {
                if (cwbLibPagoPaUtils::valorized($Dettaglio['RiferimentoDettaglio']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["RiferimentoDettaglio"] = $Dettaglio['RiferimentoDettaglio'];
                
                if (cwbLibPagoPaUtils::valorized($Dettaglio['ID_VOCE_DI_COSTO']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["ID_VOCE_DI_COSTO"] = $Dettaglio['ID_VOCE_DI_COSTO'];
                
                if(cwbLibPagoPaUtils::valorized($Dettaglio['NomeVoceDiCosto']))
                    $richiesta["Dettagli"][$key]["NomeVoceDiCosto"] = $Dettaglio['NomeVoceDiCosto'];
                
                if (cwbLibPagoPaUtils::valorized($Dettaglio['Fruitore'])) {
                    $richiesta["Dettagli"][$key]["Fruitore"] = array();
                    if (cwbLibPagoPaUtils::valorized($Dettaglio['Fruitore']['Cognome']))
                        $richiesta["Dettagli"][$key]["Fruitore"]["Cognome"] = $Dettaglio['Fruitore']['Cognome'];
                    if (cwbLibPagoPaUtils::valorized($Dettaglio['Fruitore']['Nome']))
                        $richiesta["Dettagli"][$key]["Fruitore"]["Nome"] = $Dettaglio['Fruitore']['Nome'];
                    if (cwbLibPagoPaUtils::valorized($Dettaglio['Fruitore']['CodiceFiscale']))
                        $richiesta["Dettagli"][$key]["Fruitore"]["CodiceFiscale"] = $Dettaglio['Fruitore']['CodiceFiscale'];
                }
                if(isSet($richiesta["Dettagli"][$key]["Fruitore"]) && empty($richiesta["Dettagli"][$key]["Fruitore"]))
                    unset($richiesta["Dettagli"][$key]["Fruitore"]);
                
                if(cwbLibPagoPaUtils::valorized($Dettaglio['Quantita']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["Quantita"] = $Dettaglio['Quantita'];
                if(cwbLibPagoPaUtils::valorized($Dettaglio['Importo']) || !$this->forceDisableEmptyFields){
                    $richiesta["Dettagli"][$key]["Importo"] = $Dettaglio['Importo'];
                    $richiesta["ImportoDovuto"] += $Dettaglio['Importo'];
                }
                if(cwbLibPagoPaUtils::valorized($Dettaglio['Iva']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["Iva"] = $Dettaglio['Iva'];
                if(cwbLibPagoPaUtils::valorized($Dettaglio['Descrizione']))
                    $richiesta["Dettagli"][$key]["Descrizione"] = $Dettaglio['Descrizione'];
                if(cwbLibPagoPaUtils::valorized($Dettaglio['CausaleImporto']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["CausaleImporto"] = $Dettaglio['CausaleImporto'];
                if(cwbLibPagoPaUtils::valorized($Dettaglio['AnnoCompetenza']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["AnnoCompetenza"] = $Dettaglio['AnnoCompetenza'];
                if(cwbLibPagoPaUtils::valorized($Dettaglio['SezioniParametriSpecifici'])) {
                    foreach ($Dettaglio['SezioniParametriSpecifici'] as $key2 => $ParametroSpecifico) {
                        $richiesta["Dettagli"][$key]["SezioniParametriSpecifici"][$key2] = $ParametroSpecifico;
                    }
                }
                if(cwbLibPagoPaUtils::valorized($Dettaglio['AliquotaIva']) || !$this->forceDisableEmptyFields)
                    $richiesta["Dettagli"][$key]["AliquotaIva"] = $Dettaglio['AliquotaIva'];
            }
        }
        //RiepilogoIva - Opzionale
        if(cwbLibPagoPaUtils::valorized($this->getRiepilogoIva())){
            $riepilogoIva = $this->getRiepilogoIva();
            $richiesta['RiepilogoIva'] = array();
            foreach($riepilogoIva as $key => $riepilogoIva){
                if (cwbLibPagoPaUtils::valorized($riepilogoIva['AliquotaIva']) || !$this->forceDisableEmptyFields)
                    $richiesta['RiepilogoIva'][$key]['AliquotaIva'] = $riepilogoIva['AliquotaIva'];
                if (cwbLibPagoPaUtils::valorized($riepilogoIva['BaseImponibile']) || !$this->forceDisableEmptyFields)
                    $richiesta['RiepilogoIva'][$key]['BaseImponibile'] = $riepilogoIva['BaseImponibile'];
                if (cwbLibPagoPaUtils::valorized($riepilogoIva['Iva']) || !$this->forceDisableEmptyFields)
                    $richiesta['RiepilogoIva'][$key]['Iva'] = $riepilogoIva['Iva'];
                if (cwbLibPagoPaUtils::valorized($riepilogoIva['Dovuto']) || !$this->forceDisableEmptyFields)
                    $richiesta['RiepilogoIva'][$key]['Dovuto'] = $riepilogoIva['Dovuto'];
                
            }
            if(empty($richiesta['RiepilogoIva'])) unset($richiesta['RiepilogoIva']);
        }
        
        if(cwbLibPagoPaUtils::valorized($this->getParametriSpecifici())){
            $richiesta["SezioniParametriSpecifici"] = $this->getParametriSpecifici();
        }

        // ID_RUOLO
        if(cwbLibPagoPaUtils::valorized($this->getID_RUOLO()) || !$this->forceDisableEmptyFields)
            $richiesta["ID_RUOLO"] = $this->getID_RUOLO();
        //NomeFileAcquisito
        if(cwbLibPagoPaUtils::valorized($this->getNomeFileAcquisito()) || !$this->forceDisableEmptyFields)
            $richiesta["NomeFileAcquisito"] = $this->getNomeFileAcquisito();
        //Riferimento Pratica Esterna precedente
        if(cwbLibPagoPaUtils::valorized($this->getRiferimentoPraticaEsternaPrecedente()) || !$this->forceDisableEmptyFields)
            $richiesta["RiferimentoPraticaEsternaPrecedente"] = $this->getRiferimentoPraticaEsternaPrecedente();
        //Documento
        if(cwbLibPagoPaUtils::valorized($this->getDocumento()) || !$this->forceDisableEmptyFields)
            $richiesta["Documento"] = $this->getDocumento();
        //ScadenzaSoluzioneUnica
        if(cwbLibPagoPaUtils::valorized($this->getScadenzaSoluzioneUnica()) || !$this->forceDisableEmptyFields)
            $richiesta["ScadenzaSoluzioneUnica"] = $this->getScadenzaSoluzioneUnica();
        //Riferimento ruolo esterno
        if(cwbLibPagoPaUtils::valorized($this->getRiferimentoRuoloEsterno()) || !$this->forceDisableEmptyFields)
            $richiesta["RiferimentoRuoloEsterno"] = $this->getRiferimentoRuoloEsterno();
        
//        Out::msgInfo("Test",var_export($richiesta,true));
        
        /*
         * preparazione della request già in formato string
         */
        $RequestString = "";
        //va costruito TUTTO il soapval dall'array Richiesta, perchè bisogna elaborare l'array dei dettagli, delle rate ecc...
        if ($richiesta['Descrizione']) {
            $DescrizioneSoapVal = new soapval('ent:Descrizione', 'ent:Descrizione', $richiesta['Descrizione'], false, false);
            $RequestString .= $DescrizioneSoapVal->serialize('literal');
        }

        
        if ($richiesta['AnnoImposta']) {
            $AnnoImpostaSoapVal = new soapval('ent:AnnoImposta', 'ent:AnnoImposta', $richiesta['AnnoImposta'], false, false);
            $RequestString .= $AnnoImpostaSoapVal->serialize('literal');
        }

        
        if ($richiesta['Numero']) {
            $NumeroSoapVal = new soapval('ent:Numero', 'ent:Numero', $richiesta['Numero'], false, false);
            $RequestString .= $NumeroSoapVal->serialize('literal');
        }

        if ($richiesta['Sezionale']) {
            $SezionaleSoapVal = new soapval('ent:Sezionale', 'ent:Sezionale', $richiesta['Sezionale'], false, false);
            $RequestString .= $SezionaleSoapVal->serialize('literal');
        }
        if ($richiesta['Note']) {
            $NoteSoapVal = new soapval('ent:Note', 'ent:Note', $richiesta['Note'], false, false);
            $RequestString .= $NoteSoapVal->serialize('literal');
        }
        if ($richiesta['RiferimentoPraticaEsterna']) {
            $RiferimentoPraticaEsternaSoapVal = new soapval('ent:RiferimentoPraticaEsterna', 'ent:RiferimentoPraticaEsterna', $richiesta['RiferimentoPraticaEsterna'], false, false);
            $RequestString .= $RiferimentoPraticaEsternaSoapVal->serialize('literal');
        }

        if ($richiesta['ImportoDovuto']) {
            $ImportoDovutoSoapVal = new soapval('ent:ImportoDovuto', 'ent:ImportoDovuto', $richiesta['ImportoDovuto'], false, false);
            $RequestString .= $ImportoDovutoSoapVal->serialize('literal');
        }

        if ($richiesta['Annullato']) {
            $AnnullatoSoapVal = new soapval('ent:Annullato', 'ent:Annullato', $richiesta['Annullato'], false, false);
            $RequestString .= $AnnullatoSoapVal->serialize('literal');
        }

        if ($richiesta['GestioneIva']) {
            $GestioneIvaSoapVal = new soapval('ent:GestioneIva', 'ent:GestioneIva', $richiesta['GestioneIva'], false, false);
            $RequestString .= $GestioneIvaSoapVal->serialize('literal');
        }

        if ($richiesta['NumeroProtocollo']) {
            $NumeroProtocolloSoapVal = new soapval('ent:NumeroProtocollo', 'ent:NumeroProtocollo', $richiesta['NumeroProtocollo'], false, false);
            $RequestString .= $NumeroProtocolloSoapVal->serialize('literal');
        }

        if ($richiesta['DataProtocollo']) {
            $DataProtocolloSoapVal = new soapval('ent:DataProtocollo', 'ent:DataProtocollo', $richiesta['DataProtocollo'], false, false);
            $RequestString .= $DataProtocolloSoapVal->serialize('literal');
        }
        
        if ($richiesta['DataEmissione']) {
            $DataEmissioneSoapVal = new soapval('ent:DataEmissione', 'ent:DataEmissione', $richiesta['DataEmissione'], false, false);
            $RequestString .= $DataEmissioneSoapVal->serialize('literal');
        }
        
        if ($richiesta['DataInizioPeriodo']) {
            $DataInizioPeriodoSoapVal = new soapval('ent:DataInizioPeriodo', 'ent:DataInizioPeriodo', $richiesta['DataInizioPeriodo'], false, false);
            $RequestString .= $DataInizioPeriodoSoapVal->serialize('literal');
        }
        
        if ($richiesta['DataFinePeriodo']) {
            $DataFinePeriodoSoapVal = new soapval('ent:DataFinePeriodo', 'ent:DataFinePeriodo', $richiesta['DataFinePeriodo'], false, false);
            $RequestString .= $DataFinePeriodoSoapVal->serialize('literal');
        }
        
        if ($richiesta['TipoDocumento']) {
            $TipoDocumentoSoapVal = new soapval('ent:TipoDocumento', 'ent:TipoDocumento', $richiesta['TipoDocumento'], false, false);
            $RequestString .= $TipoDocumentoSoapVal->serialize('literal');
        }

        //Contribuente non ha ripetizioni, quindi posso trattare tutto i lsottoarray con un unico oggetto SoapVal
        if ($richiesta['Contribuente']) {
            $ContribuenteString = "<ent:Contribuente>";
            $Contribuente = $richiesta['Contribuente'];

            if ($Contribuente['NaturaGiuridica']) {
                $NaturaGiuridicaSoapVal = new soapval('ent:NaturaGiuridica', 'ent:NaturaGiuridica', $Contribuente['NaturaGiuridica'], false, false);
                $ContribuenteString .= $NaturaGiuridicaSoapVal->serialize('literal');
            }

            if ($Contribuente['RagioneSociale']) {
                $RagioneSocialeSoapVal = new soapval('ent:RagioneSociale', 'ent:RagioneSociale', $Contribuente['RagioneSociale'], false, false);
                $ContribuenteString .= $RagioneSocialeSoapVal->serialize('literal');
            }
            if ($Contribuente['Cognome']) {
                $CognomeSoapVal = new soapval('ent:Cognome', 'ent:Cognome', $Contribuente['Cognome'], false, false);
                $ContribuenteString .= $CognomeSoapVal->serialize('literal');
            }
            if ($Contribuente['Nome']) {
                $NomeSoapVal = new soapval('ent:Nome', 'ent:Nome', $Contribuente['Nome'], false, false);
                $ContribuenteString .= $NomeSoapVal->serialize('literal');
            }
            if ($Contribuente['CodiceFiscale']) {
                $CodiceFiscaleSoapVal = new soapval('ent:CodiceFiscale', 'ent:CodiceFiscale', $Contribuente['CodiceFiscale'], false, false);
                $ContribuenteString .= $CodiceFiscaleSoapVal->serialize('literal');
            }
            if ($Contribuente['PartitaIva']) {
                $PartitaIvaSoapVal = new soapval('ent:PartitaIva', 'ent:PartitaIva', $Contribuente['PartitaIva'], false, false);
                $ContribuenteString .= $PartitaIvaSoapVal->serialize('literal');
            }
            if ($Contribuente['Email']) {
                $EmailSoapVal = new soapval('ent:Email', 'ent:Email', $Contribuente['Email'], false, false);
                $ContribuenteString .= $EmailSoapVal->serialize('literal');
            }
            if ($Contribuente['CodiceIsoNazionalita']) {
                $CodiceIsoNazionalitaSoapVal = new soapval('ent:CodiceIsoNazionalita', 'ent:CodiceIsoNazionalita', $Contribuente['CodiceIsoNazionalita'], false, false);
                $ContribuenteString .= $CodiceIsoNazionalitaSoapVal->serialize('literal');
            }
            if ($Contribuente['Residenza']) {
                $ResidenzaString = "<ent:Residenza>";
                $Residenza = $Contribuente['Residenza'];
                if ($Residenza['Riferimento']) {
                    $RiferimentoSoapVal = new soapval('ent:Riferimento', 'ent:Riferimento', $Residenza['Riferimento'], false, false);
                    $ResidenzaString .= $RiferimentoSoapVal->serialize('literal');
                }
                if ($Residenza['Comune']) {
                    $ComuneSoapVal = new soapval('ent:Comune', 'ent:Comune', $Residenza['Comune'], false, false);
                    $ResidenzaString .= $ComuneSoapVal->serialize('literal');
                }
                if ($Residenza['Localita']) {
                    $LocalitaSoapVal = new soapval('ent:Localita', 'ent:Localita', $Residenza['Localita'], false, false);
                    $ResidenzaString .= $LocalitaSoapVal->serialize('literal');
                }
                if ($Residenza['Provincia']) {
                    $ProvinciaSoapVal = new soapval('ent:Provincia', 'ent:Provincia', $Residenza['Provincia'], false, false);
                    $ResidenzaString .= $ProvinciaSoapVal->serialize('literal');
                }
                if ($Residenza['CAP']) {
                    $CAPSoapVal = new soapval('ent:CAP', 'ent:CAP', $Residenza['CAP'], false, false);
                    $ResidenzaString .= $CAPSoapVal->serialize('literal');
                }
                if ($Residenza['Indirizzo']) {
                    $IndirizzoSoapVal = new soapval('ent:Indirizzo', 'ent:Indirizzo', $Residenza['Indirizzo'], false, false);
                    $ResidenzaString .= $IndirizzoSoapVal->serialize('literal');
                }
                if ($Residenza['NumeroCivico']) {
                    $NumeroCivicoSoapVal = new soapval('ent:NumeroCivico', 'ent:NumeroCivico', $Residenza['NumeroCivico'], false, false);
                    $ResidenzaString .= $NumeroCivicoSoapVal->serialize('literal');
                }
                if ($Residenza['Lettera']) {
                    $LetteraSoapVal = new soapval('ent:Lettera', 'ent:Lettera', $Residenza['Lettera'], false, false);
                    $ResidenzaString .= $LetteraSoapVal->serialize('literal');
                }
                if ($Residenza['Km']) {
                    $KmSoapVal = new soapval('ent:Km', 'ent:Km', $Residenza['Km'], false, false);
                    $ResidenzaString .= $KmSoapVal->serialize('literal');
                }
                $ResidenzaString .= "</ent:Residenza>";
                $ContribuenteString .= $ResidenzaString;
            }
            if ($Contribuente['Codice']) {
                $CodiceSoapVal = new soapval('ent:Codice', 'ent:Codice', $Contribuente['Codice'], false, false);
                $ContribuenteString .= $CodiceSoapVal->serialize('literal');
            }
            if ($Contribuente['Pec']) {
                $PecSoapVal = new soapval('ent:Pec', 'ent:Pec', $Contribuente['Pec'], false, false);
                $ContribuenteString .= $PecSoapVal->serialize('literal');
            }
            if ($Contribuente['Telefono']) {
                $TelefonoSoapVal = new soapval('ent:Telefono', 'ent:Telefono', $Contribuente['Telefono'], false, false);
                $ContribuenteString .= $TelefonoSoapVal->serialize('literal');
            }
            if ($Contribuente['Cellulare']) {
                $CellulareSoapVal = new soapval('ent:Cellulare', 'ent:Cellulare', $Contribuente['Cellulare'], false, false);
                $ContribuenteString .= $CellulareSoapVal->serialize('literal');
            }
            if ($Contribuente['CodiceIstatCittadinanza']) {
                $CodiceIstatCittadinanzaSoapVal = new soapval('ent:CodiceIstatCittadinanza', 'ent:CodiceIstatCittadinanza', $Contribuente['CodiceIstatCittadinanza'], false, false);
                $ContribuenteString .= $CodiceIstatCittadinanzaSoapVal->serialize('literal');
            }
            if ($Contribuente['Domicilio']) {
                $DomicilioString = "<ent:Domicilio>";
                $Domicilio = $Contribuente['Domicilio'];
                if ($Domicilio['Riferimento']) {
                    $RiferimentoSoapVal = new soapval('ent:Riferimento', 'ent:Riferimento', $Domicilio['Riferimento'], false, false);
                    $DomicilioString .= $RiferimentoSoapVal->serialize('literal');
                }
                if ($Domicilio['Comune']) {
                    $ComuneSoapVal = new soapval('ent:Comune', 'ent:Comune', $Domicilio['Comune'], false, false);
                    $DomicilioString .= $ComuneSoapVal->serialize('literal');
                }
                if ($Domicilio['Localita']) {
                    $LocalitaSoapVal = new soapval('ent:Localita', 'ent:Localita', $Domicilio['Localita'], false, false);
                    $DomicilioString .= $LocalitaSoapVal->serialize('literal');
                }
                if ($Domicilio['Provincia']) {
                    $ProvinciaSoapVal = new soapval('ent:Provincia', 'ent:Provincia', $Domicilio['Provincia'], false, false);
                    $DomicilioString .= $ProvinciaSoapVal->serialize('literal');
                }
                if ($Domicilio['CAP']) {
                    $CAPSoapVal = new soapval('ent:CAP', 'ent:CAP', $Domicilio['CAP'], false, false);
                    $DomicilioString .= $CAPSoapVal->serialize('literal');
                }
                if ($Domicilio['Indirizzo']) {
                    $IndirizzoSoapVal = new soapval('ent:Indirizzo', 'ent:Indirizzo', $Domicilio['Indirizzo'], false, false);
                    $DomicilioString .= $IndirizzoSoapVal->serialize('literal');
                }
                if ($Domicilio['NumeroCivico']) {
                    $NumeroCivicoSoapVal = new soapval('ent:NumeroCivico', 'ent:NumeroCivico', $Domicilio['NumeroCivico'], false, false);
                    $DomicilioString .= $NumeroCivicoSoapVal->serialize('literal');
                }
                if ($Domicilio['Lettera']) {
                    $LetteraSoapVal = new soapval('ent:Lettera', 'ent:Lettera', $Domicilio['Lettera'], false, false);
                    $DomicilioString .= $LetteraSoapVal->serialize('literal');
                }
                if ($Domicilio['Km']) {
                    $KmSoapVal = new soapval('ent:Km', 'ent:Km', $Domicilio['Km'], false, false);
                    $DomicilioString .= $KmSoapVal->serialize('literal');
                }
                $DomicilioString .= "</ent:Domicilio>";
                $ContribuenteString .= $DomicilioString;
            }
            $ContribuenteString .= "</ent:Contribuente>";
            $RequestString .= $ContribuenteString;
        }
        //Rate - per ogni rata va creato un oggetto soapval in quanto il ws richiede la ripetizione del tag Rate
        if ($richiesta['Rate']) {
            $RateString = "";
            foreach ($richiesta['Rate'] as $Rata) {
                $RataString = "<ent:Rate>";
                
                if ($Rata['NumeroRata']) {
                    $NumeroRataSoapVal = new soapval('ent:NumeroRata', 'ent:NumeroRata', $Rata['NumeroRata'], false, false);
                    $RataString .= $NumeroRataSoapVal->serialize('literal');
                }
                
                if ($Rata['Importo']) {
                    $ImportoSoapVal = new soapval('ent:Importo', 'ent:Importo', $Rata['Importo'], false, false);
                    $RataString .= $ImportoSoapVal->serialize('literal');
                }
                
                if ($Rata['Scadenza']) {
                    $ScadenzaSoapVal = new soapval('ent:Scadenza', 'ent:Scadenza', $Rata['Scadenza'], false, false);
                    $RataString .= $ScadenzaSoapVal->serialize('literal');
                }
                
                if ($Rata['QuintoCampo']) {
                    $QuintoCampoSoapVal = new soapval('ent:QuintoCampo', 'ent:QuintoCampo', $Rata['QuintoCampo'], false, false);
                    $RataString .= $QuintoCampoSoapVal->serialize('literal');
                }
                
                if ($Rata['IUV']) {
                    $IUVSoapVal = new soapval('ent:IUV', 'ent:IUV', $Rata['IUV'], false, false);
                    $RataString .= $IUVSoapVal->serialize('literal');
                }
                $RataString .= "</ent:Rate>";
                $RateString .= $RataString;
            }
            $RequestString .= $RateString;
        }
        //Dettagli - per ogni dettaglio va creato un oggetto soapval in quanto il ws richiede la ripetizione del tag Dettagli
        if ($richiesta['Dettagli']) {
            $DettagliString = "";
            foreach ($richiesta['Dettagli'] as $Dettaglio) {
                $DettagliString .= "<ent:Dettagli>";
                
                if ($Dettaglio['RiferimentoDettaglio']) {
                    $RiferimentoDettaglioSoapVal = new soapval('ent:RiferimentoDettaglio', 'ent:RiferimentoDettaglio', $Dettaglio['RiferimentoDettaglio'], false, false);
                    $DettagliString .= $RiferimentoDettaglioSoapVal->serialize('literal');
                }
                
                if ($Dettaglio['ID_VOCE_DI_COSTO']) {
                    $ID_VOCE_DI_COSTOSoapVal = new soapval('ent:ID_VOCE_DI_COSTO', 'ent:ID_VOCE_DI_COSTO', $Dettaglio['ID_VOCE_DI_COSTO'], false, false);
                    $DettagliString .= $ID_VOCE_DI_COSTOSoapVal->serialize('literal');
                }

                if ($Dettaglio['NomeVoceDiCosto']) {
                    $NomeVoceDiCostoSoapVal = new soapval('ent:NomeVoceDiCosto', 'ent:NomeVoceDiCosto', $Dettaglio['NomeVoceDiCosto'], false, false);
                    $DettagliString .= $NomeVoceDiCostoSoapVal->serialize('literal');
                }
                //il Fruitore va inserito SOLO NEL CASO di servizi mensa
                if ($Dettaglio['Fruitore']) {
                    $FruitoreString = "<ent:Fruitore>";
                    $Fruitore = $Dettaglio['Fruitore'];
                    if ($Fruitore['Cognome']) {
                        $CognomeSoapVal = new soapval('ent:Cognome', 'ent:Cognome', $Fruitore['Cognome'], false, false);
                        $FruitoreString .= $CognomeSoapVal->serialize('literal');
                    }
                    if ($Fruitore['Nome']) {
                        $NomeSoapVal = new soapval('ent:Nome', 'ent:Nome', $Fruitore['Nome'], false, false);
                        $FruitoreString .= $NomeSoapVal->serialize('literal');
                    }
                    if ($Fruitore['CodiceFiscale']) {
                        $CodiceFiscaleSoapVal = new soapval('ent:CodiceFiscale', 'ent:CodiceFiscale', $Fruitore['CodiceFiscale'], false, false);
                        $FruitoreString .= $CodiceFiscaleSoapVal->serialize('literal');
                    }
                    $FruitoreString .= "</ent:Fruitore>";
                    $DettagliString .= $FruitoreString;
                }

                if ($Dettaglio['Quantita']) {
                    $QuantitaSoapVal = new soapval('ent:Quantita', 'ent:Quantita', $Dettaglio['Quantita'], false, false);
                    $DettagliString .= $QuantitaSoapVal->serialize('literal');
                }

                if ($Dettaglio['Importo']) {
                    $ImportoSoapVal = new soapval('ent:Importo', 'ent:Importo', $Dettaglio['Importo'], false, false);
                    $DettagliString .= $ImportoSoapVal->serialize('literal');
                }

                if ($Dettaglio['Iva']) {
                    $IvaSoapVal = new soapval('ent:Iva', 'ent:Iva', $Dettaglio['Iva'], false, false);
                    $DettagliString .= $IvaSoapVal->serialize('literal');
                }

                if ($Dettaglio['Descrizione']) {
                    $DescrizioneSoapVal = new soapval('ent:Descrizione', 'ent:Descrizione', $Dettaglio['Descrizione'], false, false);
                    $DettagliString .= $DescrizioneSoapVal->serialize('literal');
                }

                if ($Dettaglio['CausaleImporto']) {
                    $CausaleImportoSoapVal = new soapval('ent:CausaleImporto', 'ent:CausaleImporto', $Dettaglio['CausaleImporto'], false, false);
                    $DettagliString .= $CausaleImportoSoapVal->serialize('literal');
                }

                if ($Dettaglio['AnnoCompetenza']) {
                    $AnnoCompetenzaSoapVal = new soapval('ent:AnnoCompetenza', 'ent:AnnoCompetenza', $Dettaglio['AnnoCompetenza'], false, false);
                    $DettagliString .= $AnnoCompetenzaSoapVal->serialize('literal');
                }

                if ($Dettaglio['SezioniParametriSpecifici']) {
                    $SezioniPrametriSpecificiString = "";
                    foreach ($Dettaglio['SezioniParametriSpecifici'] as $Sezione) {
                        $attrSezione = $Sezione['Sezione'];
                        $ParametroSpecificoString = '';
                        foreach ($Sezione['ParametriSpecifici'] as $ParametroSpecifico) {
                            $ParametroSpecificoSoapVal = new soapval('ent:ParametroSpecifico', 'ent:ParametroSpecifico', $ParametroSpecifico, false, false);
                            $ParametroSpecificoString .= $ParametroSpecificoSoapVal->serialize('literal');
                        }
                        $SezioniPrametriSpecificiString .= "<ent:SezioniParametriSpecifici Sezione=\"" . $attrSezione . "\">"
                                . "<ent:ParametriSpecifici>" . $ParametroSpecificoString . "</ent:ParametriSpecifici>"
                                . "</ent:SezioniParametriSpecifici>";
                    }
                    $DettagliString .= $SezioniPrametriSpecificiString;
                }
                
                if ($Dettaglio['AliquotaIva']) {
                    $AliquotaIvaSoapVal = new soapval('ent:AliquotaIva', 'ent:AliquotaIva', $Dettaglio['AliquotaIva'], false, false);
                    $DettagliString .= $AliquotaIvaSoapVal->serialize('literal');
                }
                
                $DettagliString .= "</ent:Dettagli>";
            }
            $RequestString .= $DettagliString;
        }
        //RiepilogoIva
        if ($richiesta['RiepilogoIva']) {
            $RiepilogoIvaString = "";
            foreach ($richiesta['RiepilogoIva'] as $PosizioneDebitoria_RiepilogoIva) {
                $RiepilogoIvaSoapVal = new soapval('ent:PosizioneDebitoria_RiepilogoIva', 'ent:PosizioneDebitoria_RiepilogoIva', $PosizioneDebitoria_RiepilogoIva, false, false);
                $RiepilogoIvaString .= $RiepilogoIvaSoapVal->serialize('literal');
            }
            $RequestString .= "<ent:RiepilogoIva>" . $RiepilogoIvaString . "</ent:RiepilogoIva>";
        }
        //SezioniParametriSpecifici
        if($richiesta["SezioniParametriSpecifici"]) {
            $SezioniPrametriSpecificiString = "";
            foreach ($Sezioni["SezioniParametriSpecifici"] as $Sezione) {
                $attrSezione = $Sezione["Sezione"];
                $SezioniParametriSpecificiString .= "<ent:SezioniParametriSpecifici Sezione=\"" . $attrSezione . "\">";
                $SezioniParametriSpecificiString .= "<ent:ParametriSpecifici>";
                foreach ($Sezione["ParametriSpecifici"] as $ParametroSpecifico) {
                    $ParametroSpecificoSoapVal = new soapval('ent:ParametroSpecifico', 'ent:ParametroSpecifico', $ParametroSpecifico, false, false);
                    $SezioniParametriSpecificiString .= $ParametroSpecificoSoapVal->serialize('literal');
                }
                $SezioniParametriSpecificiString .= "</ent:ParametriSpecifici>";
                $SezioniParametriSpecificiString .= "</ent:SezioniParametriSpecifici>";
                $RequestString .= $SezioniPrametriSpecificiString;
            }
        }
        
        if($richiesta['ID_RUOLO']){
            $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $richiesta['ID_RUOLO'], false, false);
            $RequestString .= $ID_RUOLOSoapVal->serialize('literal');
        }

        if($richiesta['NomeFileAcquisito']){
            $NomeFileAcquisitoSoapVal = new soapval('ent:NomeFileAcquisito', 'ent:NomeFileAcquisito', $richiesta['NomeFileAcquisito'], false, false);
            $RequestString .= $NomeFileAcquisitoSoapVal->serialize('literal');
        }

        if($richiesta['RiferimentoPraticaEsternaPrecedente']){
            $RiferimentoPraticaEsternaPrecedenteSoapVal = new soapval('ent:RiferimentoPraticaEsternaPrecedente', 'ent:RiferimentoPraticaEsternaPrecedente', $richiesta['RiferimentoPraticaEsternaPrecedente'], false, false);
            $RequestString .= $RiferimentoPraticaEsternaPrecedenteSoapVal->serialize('literal');
        }

        if($richiesta['Documento']){
            $DocumentoSoapVal = new soapval('ent:Documento', 'ent:Documento', base64_encode($richiesta['Documento']), false, false);
            $RequestString .= $DocumentoSoapVal->serialize('literal');
        }

        if($richiesta['ScadenzaSoluzioneUnica']){
            $ScadenzaSoluzioneUnicaSoapVal = new soapval('ent:ScadenzaSoluzioneUnica', 'ent:ScadenzaSoluzioneUnica', $richiesta['ScadenzaSoluzioneUnica'], false, false);
            $RequestString .= $ScadenzaSoluzioneUnicaSoapVal->serialize('literal');
        }

        if($richiesta['RiferimentoRuoloEsterno']){
            $RiferimentoRuoloEsternoSoapVal = new soapval('ent:RiferimentoRuoloEsterno', 'ent:RiferimentoRuoloEsterno', $richiesta['RiferimentoRuoloEsterno'], false, false);
            $RequestString .= $RiferimentoRuoloEsternoSoapVal->serialize('literal');
        }
        App::log('request string');
        App::log($RequestString);
        return $RequestString;
    }

}

?>
