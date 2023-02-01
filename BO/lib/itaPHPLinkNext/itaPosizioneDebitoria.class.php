<?php

/**
 *
 * Classe per collegamento jProtocollo services
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaInforWS
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    21.11.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaPosizioneDebitoria {

    protected $Descrizione;
    protected $AnnoImposta;
    protected $Numero;
    protected $Sezionale;
    protected $Note;
    protected $RiferimentoPraticaEsterna;
    protected $Annullato;
    protected $GestioneIva;
    protected $ImportoDovuto;
    protected $IUV;
    protected $NumeroProtocollo;
    protected $DataProtocollo;
    protected $DataEmissione;
    protected $DataInizioPeriodo;
    protected $DataFinePeriodo;
    protected $TipoDocumento;
    protected $Contribuente; //Oggetto Contribuente
    protected $Rate;
    protected $Dettagli;
    protected $ParametriSpecifici;
    protected $ID_RUOLO;
    protected $NomeFileAcquisito;
    protected $RiferimentoPraticaEsternaPrecedente;
    protected $ScadenzaSoluzioneUnica;

    public function getDescrizione() {
        return $this->Descrizione;
    }

    public function getAnnoImposta() {
        return $this->AnnoImposta;
    }

    public function getNumero() {
        return $this->Numero;
    }

    public function getSezionale() {
        return $this->Sezionale;
    }

    public function getNote() {
        return $this->Note;
    }

    public function getRiferimentoPraticaEsterna() {
        return $this->RiferimentoPraticaEsterna;
    }

    public function getImportoDovuto() {
        return $this->ImportoDovuto;
    }

    public function getIUV() {
        return $this->IUV;
    }

    public function getNumeroProtocollo() {
        return $this->NumeroProtocollo;
    }

    public function getDataProtocollo() {
        return $this->DataProtocollo;
    }

    public function getDataEmissione() {
        return $this->DataEmissione;
    }

    public function getDataInizioPeriodo() {
        return $this->DataInizioPeriodo;
    }

    public function getDataFinePeriodo() {
        return $this->DataFinePeriodo;
    }

    public function getTipoDocumento() {
        return $this->TipoDocumento;
    }

    public function getContribuente() {
        return $this->Contribuente;
    }

    public function getRate() {
        return $this->Rate;
    }

    public function getDettagli() {
        return $this->Dettagli;
    }

    public function getParametriSpecifici() {
        return $this->ParametriSpecifici;
    }

    public function getID_RUOLO() {
        return $this->ID_RUOLO;
    }

    public function getAnnullato() {
        return $this->Annullato;
    }

    public function setAnnullato($Annullato) {
        $this->Annullato = $Annullato;
    }

    public function getGestioneIva() {
        return $this->GestioneIva;
    }

    public function setGestioneIva($GestioneIva) {
        $this->GestioneIva = $GestioneIva;
    }

    public function setDescrizione($Descrizione) {
        $this->Descrizione = $Descrizione;
    }

    public function setAnnoImposta($AnnoImposta) {
        $this->AnnoImposta = $AnnoImposta;
    }

    public function setNumero($Numero) {
        $this->Numero = $Numero;
    }

    public function setSezionale($Sezionale) {
        $this->Sezionale = $Sezionale;
    }

    public function setNote($Note) {
        $this->Note = $Note;
    }

    public function setRiferimentoPraticaEsterna($RiferimentoPraticaEsterna) {
        $this->RiferimentoPraticaEsterna = $RiferimentoPraticaEsterna;
    }

    public function setImportoDovuto($ImportoDovuto) {
        $this->ImportoDovuto = $ImportoDovuto;
    }

    public function setIUV($IUV) {
        $this->IUV = $IUV;
    }

    public function setNumeroProtocollo($NumeroProtocollo) {
        $this->NumeroProtocollo = $NumeroProtocollo;
    }

    public function setDataProtocollo($DataProtocollo) {
        $this->DataProtocollo = $DataProtocollo;
    }

    public function setDataEmissione($DataEmissione) {
        $this->DataEmissione = $DataEmissione;
    }

    public function setDataInizioPeriodo($DataInizioPeriodo) {
        $this->DataInizioPeriodo = $DataInizioPeriodo;
    }

    public function setDataFinePeriodo($DataFinePeriodo) {
        $this->DataFinePeriodo = $DataFinePeriodo;
    }

    public function setTipoDocumento($TipoDocumento) {
        $this->TipoDocumento = $TipoDocumento;
    }

    public function setContribuente($Contribuente) {
        $this->Contribuente = $Contribuente;
    }

    public function setRate($Rate) {
        $this->Rate = $Rate;
    }

    public function setDettagli($Dettagli) {
        $this->Dettagli = $Dettagli;
    }

    public function setParametriSpecifici($ParametriSpecifici) {
        $this->ParametriSpecifici = $ParametriSpecifici;
    }

    public function setID_RUOLO($ID_RUOLO) {
        $this->ID_RUOLO = $ID_RUOLO;
    }

    public function getNomeFileAcquisito() {
        return $this->NomeFileAcquisito;
    }

    public function setNomeFileAcquisito($NomeFileAcquisito) {
        $this->NomeFileAcquisito = $NomeFileAcquisito;
    }

    function getScadenzaSoluzioneUnica() {
        return $this->ScadenzaSoluzioneUnica;
    }

    function setScadenzaSoluzioneUnica($ScadenzaSoluzioneUnica) {
        $this->ScadenzaSoluzioneUnica = $ScadenzaSoluzioneUnica;
    }

    function getRiferimentoPraticaEsternaPrecedente() {
        return $this->RiferimentoPraticaEsternaPrecedente;
    }

    function setRiferimentoPraticaEsternaPrecedente($RiferimentoPraticaEsternaPrecedente) {
        $this->RiferimentoPraticaEsternaPrecedente = $RiferimentoPraticaEsternaPrecedente;
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
        if ($this->getDescrizione()) {
            $richiesta["Descrizione"] = $this->getDescrizione();
        }
        // AnnoImposta
        $richiesta["AnnoImposta"] = $this->getAnnoImposta();
        // Numero
        $richiesta["Numero"] = $this->getNumero();
        // Sezionale - opzionale
        if ($this->getSezionale()) {
            $richiesta["Sezionale"] = $this->getSezionale();
        }
        // Note - opzionale
        if ($this->getNote()) {
            $richiesta["Note"] = $this->getNote();
        }
        // RiferimentoPraticaEsterna
        $richiesta["RiferimentoPraticaEsterna"] = $this->getRiferimentoPraticaEsterna();
        // ImportoDovuto
        $richiesta["ImportoDovuto"] = $this->getImportoDovuto();
        // Annullato
        $richiesta["Annullato"] = $this->getAnnullato();
        // GestioneIva
        $GestioneIva = $this->getGestioneIva() == true ? "1" : "0";
        $richiesta["GestioneIva"] = $GestioneIva;
        // NumeroProtocollo
        $richiesta["NumeroProtocollo"] = $this->getNumeroProtocollo();
        // DataProtocollo
        $richiesta["DataProtocollo"] = $this->getDataProtocollo();
        // DataEmissione
        $richiesta["DataEmissione"] = $this->getDataEmissione();
        // DataInizioPeriodo
        $richiesta["DataInizioPeriodo"] = $this->getDataInizioPeriodo();
        // DataFinePeriodo
        $richiesta["DataFinePeriodo"] = $this->getDataFinePeriodo();
        // TipoDocumento
        $richiesta["TipoDocumento"] = $this->getTipoDocumento();
        // Contribuente - opzionale
        if ($this->getContribuente()) {
            $richiesta["Contribuente"] = array();
            if (isset($this->Contribuente['NaturaGiuridica']))
                $richiesta["Contribuente"]["NaturaGiuridica"] = $this->Contribuente['NaturaGiuridica'];
            if (isset($this->Contribuente['RagioneSociale']))
                $richiesta["Contribuente"]["RagioneSociale"] = $this->Contribuente['RagioneSociale'];
            if (isset($this->Contribuente['Cognome']))
                $richiesta["Contribuente"]["Cognome"] = $this->Contribuente['Cognome'];
            if (isset($this->Contribuente['Nome']))
                $richiesta["Contribuente"]["Nome"] = $this->Contribuente['Nome'];
            if (isset($this->Contribuente['CodiceFiscale']))
                $richiesta["Contribuente"]["CodiceFiscale"] = $this->Contribuente['CodiceFiscale'];
            if (isset($this->Contribuente['PartitaIva']))
                $richiesta["Contribuente"]["PartitaIva"] = $this->Contribuente['PartitaIva'];
            if (isset($this->Contribuente['Email']))
                $richiesta["Contribuente"]["Email"] = $this->Contribuente['Email'];
            if (isset($this->Contribuente['CodiceIstatNazionalita']))
                $richiesta["Contribuente"]["CodiceIstatNazionalita"] = $this->Contribuente['CodiceIstatNazionalita'];
            if (isset($this->Contribuente['Pec']))
                $richiesta["Contribuente"]["Pec"] = $this->Contribuente['Pec'];
            if (isset($this->Contribuente['Telefono']))
                $richiesta["Contribuente"]["Telefono"] = $this->Contribuente['Telefono'];
            if (isset($this->Contribuente['Cellulare']))
                $richiesta["Contribuente"]["Cellulare"] = $this->Contribuente['Cellulare'];
            if (isset($this->Contribuente['CodiceIstatCittadinanza']))
                $richiesta["Contribuente"]["CodiceIstatCittadinanza"] = $this->Contribuente['CodiceIstatCittadinanza'];
            if (isset($this->Contribuente['Residenza'])) {
                $richiesta["Contribuente"]["Residenza"] = array();
                if (isset($this->Contribuente['Residenza']['Riferimento']))
                    $richiesta["Contribuente"]["Residenza"]["Riferimento"] = $this->Contribuente['Residenza']['Riferimento'];
                if (isset($this->Contribuente['Residenza']['Comune']))
                    $richiesta["Contribuente"]["Residenza"]["Comune"] = $this->Contribuente['Residenza']['Comune'];
                if (isset($this->Contribuente['Residenza']['Localita']))
                    $richiesta["Contribuente"]["Residenza"]["Localita"] = $this->Contribuente['Residenza']['Localita'];
                if (isset($this->Contribuente['Residenza']['Provincia']))
                    $richiesta["Contribuente"]["Residenza"]["Provincia"] = $this->Contribuente['Residenza']['Provincia'];
                if (isset($this->Contribuente['Residenza']['CAP']))
                    $richiesta["Contribuente"]["Residenza"]["CAP"] = $this->Contribuente['Residenza']['CAP'];
                if (isset($this->Contribuente['Residenza']['Indirizzo']))
                    $richiesta["Contribuente"]["Residenza"]["Indirizzo"] = $this->Contribuente['Residenza']['Indirizzo'];
                if (isset($this->Contribuente['Residenza']['NumeroCivico']))
                    $richiesta["Contribuente"]["Residenza"]["NumeroCivico"] = $this->Contribuente['Residenza']['NumeroCivico'];
                if (isset($this->Contribuente['Residenza']['Lettera']))
                    $richiesta["Contribuente"]["Residenza"]["Lettera"] = $this->Contribuente['Residenza']['Lettera'];
                if (isset($this->Contribuente['Residenza']['Km']))
                    $richiesta["Contribuente"]["Residenza"]["Km"] = $this->Contribuente['Residenza']['Km'];
            }
            if (isset($this->Contribuente['Domicilio'])) {
                $richiesta["Contribuente"]["Domicilio"] = array();
                if (isset($this->Contribuente['Domicilio']['Riferimento']))
                    $richiesta["Contribuente"]["Domicilio"]["Riferimento"] = $this->Contribuente['Domicilio']['Riferimento'];
                if (isset($this->Contribuente['Domicilio']['Comune']))
                    $richiesta["Contribuente"]["Domicilio"]["Comune"] = $this->Contribuente['Domicilio']['Comune'];
                if (isset($this->Contribuente['Domicilio']['Localita']))
                    $richiesta["Contribuente"]["Domicilio"]["Localita"] = $this->Contribuente['Domicilio']['Localita'];
                if (isset($this->Contribuente['Domicilio']['Provincia']))
                    $richiesta["Contribuente"]["Domicilio"]["Provincia"] = $this->Contribuente['Domicilio']['Provincia'];
                if (isset($this->Contribuente['Domicilio']['CAP']))
                    $richiesta["Contribuente"]["Domicilio"]["CAP"] = $this->Contribuente['Domicilio']['CAP'];
                if (isset($this->Contribuente['Domicilio']['Indirizzo']))
                    $richiesta["Contribuente"]["Domicilio"]["Indirizzo"] = $this->Contribuente['Domicilio']['Indirizzo'];
                if (isset($this->Contribuente['Domicilio']['NumeroCivico']))
                    $richiesta["Contribuente"]["Domicilio"]["NumeroCivico"] = $this->Contribuente['Domicilio']['NumeroCivico'];
                if (isset($this->Contribuente['Domicilio']['Lettera']))
                    $richiesta["Contribuente"]["Domicilio"]["Lettera"] = $this->Contribuente['Domicilio']['Lettera'];
                if (isset($this->Contribuente['Domicilio']['Km']))
                    $richiesta["Contribuente"]["Domicilio"]["Km"] = $this->Contribuente['Domicilio']['Km'];
            }
        }
        // Rate - opzionale
        if ($this->getRate()) {
            $richiesta["Rate"] = array();
            foreach ($this->Rate as $key => $Rata) {
                if (isset($Rata['NumeroRata']))
                    $richiesta["Rate"][$key]["NumeroRata"] = $Rata['NumeroRata'];
                if (isset($Rata['Importo']))
                    $richiesta["Rate"][$key]["Importo"] = $Rata['Importo'];
                if (isset($Rata['Scadenza']))
                    $richiesta["Rate"][$key]["Scadenza"] = $Rata['Scadenza'];
                if (isset($Rata['QuintoCampo']))
                    $richiesta["Rate"][$key]["QuintoCampo"] = $Rata['QuintoCampo'];
                if (isset($Rata['IUV']))
                    $richiesta["Rate"][$key]["IUV"] = $Rata['IUV'];
//                if (isset($Rata['NumeroRata']))
//                    $richiesta["Rate"]["NumeroRata"] = $Rata['NumeroRata'];
//                if (isset($Rata['Importo']))
//                    $richiesta["Rate"]["Importo"] = $Rata['Importo'];
//                if (isset($Rata['Scadenza']))
//                    $richiesta["Rate"]["Scadenza"] = $Rata['Scadenza'];
//                if (isset($Rata['QuintoCampo']))
//                    $richiesta["Rate"]["QuintoCampo"] = $Rata['QuintoCampo'];
//                if (isset($Rata['IUV']))
//                    $richiesta["Rate"]["IUV"] = $Rata['IUV'];
            }
        }
        // Dettagli - opzionale
        if ($this->getDettagli()) {
            $richiesta["Dettagli"] = array();
            foreach ($this->Dettagli as $key => $Dettaglio) {
                if (isset($Dettaglio['ID_VOCE_DI_COSTO']))
                    $richiesta["Dettagli"][$key]["ID_VOCE_DI_COSTO"] = $Dettaglio['ID_VOCE_DI_COSTO'];
                if (isset($Dettaglio['Fruitore'])) {
                    $richiesta["Dettagli"][$key]["Fruitore"] = array();
                    if ($Dettaglio['Fruitore']['NaturaGiuridica'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["NaturaGiuridica"] = $Dettaglio['Fruitore']['NaturaGiuridica'];
                    if ($Dettaglio['Fruitore']['RagioneSociale'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["RagioneSociale"] = $Dettaglio['Fruitore']['RagioneSociale'];
                    if ($Dettaglio['Fruitore']['Cognome'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["Cognome"] = $Dettaglio['Fruitore']['Cognome'];
                    if ($Dettaglio['Fruitore']['Nome'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["Nome"] = $Dettaglio['Fruitore']['Nome'];
                    if ($Dettaglio['Fruitore']['CodiceFiscale'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["CodiceFiscale"] = $Dettaglio['Fruitore']['CodiceFiscale'];
                    if ($Dettaglio['Fruitore']['PartitaIva'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["PartitaIva"] = $Dettaglio['Fruitore']['PartitaIva'];
                    if ($Dettaglio['Fruitore']['Email'])
                        $richiesta["Dettagli"][$key]["Fruitore"]["Email"] = $Dettaglio['Fruitore']['Email'];
                }
                if (isset($Dettaglio['Quantita']))
                    $richiesta["Dettagli"][$key]["Quantita"] = $Dettaglio['Quantita'];
                if (isset($Dettaglio['Importo']))
                    $richiesta["Dettagli"][$key]["Importo"] = $Dettaglio['Importo'];
                if (isset($Dettaglio['Iva']))
                    $richiesta["Dettagli"][$key]["Iva"] = $Dettaglio['Iva'];
                if (isset($Dettaglio['Descrizione']))
                    $richiesta["Dettagli"][$key]["Descrizione"] = $Dettaglio['Descrizione'];
                if (isset($Dettaglio['CausaleImporto']))
                    $richiesta["Dettagli"][$key]["CausaleImporto"] = $Dettaglio['CausaleImporto'];
                if (isset($Dettaglio['AnnoCompetenza']))
                    $richiesta["Dettagli"][$key]["AnnoCompetenza"] = $Dettaglio['AnnoCompetenza'];
                if (isset($Dettaglio['SezioniParametriSpecifici'])) {
                    foreach ($Dettaglio['SezioniParametriSpecifici'] as $key2 => $ParametroSpecifico) {
                        $richiesta["Dettagli"][$key]["SezioniParametriSpecifici"][$key2] = $ParametroSpecifico;
                    }
                }
//                if (isset($Dettaglio['ID_VOCE_DI_COSTO']))
//                    $richiesta["Dettagli"]["ID_VOCE_DI_COSTO"] = $Dettaglio['ID_VOCE_DI_COSTO'];
//                if (isset($Dettaglio['Fruitore'])) {
//                    $richiesta["Dettagli"]["Fruitore"] = array();
//                    if ($Dettaglio['Fruitore']['NaturaGiuridica'])
//                        $richiesta["Dettagli"]["Fruitore"]["NaturaGiuridica"] = $Dettaglio['Fruitore']['NaturaGiuridica'];
//                    if ($Dettaglio['Fruitore']['RagioneSociale'])
//                        $richiesta["Dettagli"]["Fruitore"]["RagioneSociale"] = $Dettaglio['Fruitore']['RagioneSociale'];
//                    if ($Dettaglio['Fruitore']['Cognome'])
//                        $richiesta["Dettagli"]["Fruitore"]["Cognome"] = $Dettaglio['Fruitore']['Cognome'];
//                    if ($Dettaglio['Fruitore']['Nome'])
//                        $richiesta["Dettagli"]["Fruitore"]["Nome"] = $Dettaglio['Fruitore']['Nome'];
//                    if ($Dettaglio['Fruitore']['CodiceFiscale'])
//                        $richiesta["Dettagli"]["Fruitore"]["CodiceFiscale"] = $Dettaglio['Fruitore']['CodiceFiscale'];
//                    if ($Dettaglio['Fruitore']['PartitaIva'])
//                        $richiesta["Dettagli"]["Fruitore"]["PartitaIva"] = $Dettaglio['Fruitore']['PartitaIva'];
//                    if ($Dettaglio['Fruitore']['Email'])
//                        $richiesta["Dettagli"]["Fruitore"]["Email"] = $Dettaglio['Fruitore']['Email'];
//                }
//                if (isset($Dettaglio['Quantita']))
//                    $richiesta["Dettagli"]["Quantita"] = $Dettaglio['Quantita'];
//                if (isset($Dettaglio['Importo']))
//                    $richiesta["Dettagli"]["Importo"] = $Dettaglio['Importo'];
//                if (isset($Dettaglio['Iva']))
//                    $richiesta["Dettagli"]["Iva"] = $Dettaglio['Iva'];
//                if (isset($Dettaglio['Descrizione']))
//                    $richiesta["Dettagli"]["Descrizione"] = $Dettaglio['Descrizione'];
//                if (isset($Dettaglio['CausaleImporto']))
//                    $richiesta["Dettagli"]["CausaleImporto"] = $Dettaglio['CausaleImporto'];
//                if (isset($Dettaglio['AnnoCompetenza']))
//                    $richiesta["Dettagli"]["AnnoCompetenza"] = $Dettaglio['AnnoCompetenza'];
//                if (isset($Dettaglio['SezioniParametriSpecifici'])) {
//                    foreach ($Dettaglio['SezioniParametriSpecifici'] as $key2 => $ParametroSpecifico) {
//                        $richiesta["Dettagli"]["SezioniParametriSpecifici"][$key2] = $ParametroSpecifico;
//                    }
//                }
            }
        }
        // ParametriSpecifici - opzionale
        if ($this->getParametriSpecifici()) {
            $richiesta["SezioniParametriSpecifici"] = array();
            foreach ($this->ParametriSpecifici as $key => $ParametroSpecifico) {
                if (isset($ParametroSpecifico['Nome']))
                    $richiesta["SezioniParametriSpecifici"][$key]["Nome"] = $ParametroSpecifico['Nome'];
                if (isset($ParametroSpecifico['Valore']))
                    $richiesta["SezioniParametriSpecifici"][$key]["Valore"] = $ParametroSpecifico['Valore'];
            }
        }
        // ID_RUOLO
//        if ($this->getID_RUOLO()) {
//            $richiesta["ID_RUOLO"] = $this->getID_RUOLO();
//        }
        //NomeFileAcquisito
        $richiesta["NomeFileAcquisito"] = $this->getNomeFileAcquisito();
        
        //se si tratta di un sollecito posso passare il riferimento alla pratica precedente
        if ($this->getRiferimentoPraticaEsternaPrecedente()){
            $richiesta["RiferimentoPraticaEsternaPrecedente"] = $this->getRiferimentoPraticaEsternaPrecedente();
        }

        //ScadenzaSoluzioneUnica
        $richiesta["ScadenzaSoluzioneUnica"] = $this->getScadenzaSoluzioneUnica();

//        Out::msgInfo("array richiesta", print_r($richiesta, true));
//        return array('PosizioniDebitoria' => $richiesta);


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
//        if ($richiesta['Annullato']) {
        $AnnullatoSoapVal = new soapval('ent:Annullato', 'ent:Annullato', $richiesta['Annullato'], false, false);
        $RequestString .= $AnnullatoSoapVal->serialize('literal');
//        }
//        if ($richiesta['GestioneIva']) {
        $GestioneIvaSoapVal = new soapval('ent:GestioneIva', 'ent:GestioneIva', $richiesta['GestioneIva'], false, false);
        $RequestString .= $GestioneIvaSoapVal->serialize('literal');
//        }
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
            if ($Contribuente['CodiceIstatNazionalita']) {
                $CodiceIstatNazionalitaSoapVal = new soapval('ent:CodiceIstatNazionalita', 'ent:CodiceIstatNazionalita', $Contribuente['CodiceIstatNazionalita'], false, false);
                $ContribuenteString .= $CodiceIstatNazionalitaSoapVal->serialize('literal');
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
                if ($Dettaglio['ID_VOCE_DI_COSTO']) {
                    $ID_VOCE_DI_COSTOSoapVal = new soapval('ent:ID_VOCE_DI_COSTO', 'ent:ID_VOCE_DI_COSTO', $Dettaglio['ID_VOCE_DI_COSTO'], false, false);
                    $DettagliString .= $ID_VOCE_DI_COSTOSoapVal->serialize('literal');
                }
                //il Fruitore va inserito SOLO NEL CASO di servizi mensa
                if ($Dettaglio['Fruitore']) {
                    $FruitoreString = "<ent:Fruitore>";
                    $Fruitore = $Dettaglio['Fruitore'];
                    if ($Fruitore['NaturaGiuridica']) {
                        $NaturaGiuridicaSoapVal = new soapval('ent:NaturaGiuridica', 'ent:NaturaGiuridica', $Fruitore['NaturaGiuridica'], false, false);
                        $FruitoreString .= $NaturaGiuridicaSoapVal->serialize('literal');
                    }
                    if ($Fruitore['RagioneSociale']) {
                        $RagioneSocialeSoapVal = new soapval('ent:RagioneSociale', 'ent:RagioneSociale', $Fruitore['RagioneSociale'], false, false);
                        $FruitoreString .= $RagioneSocialeSoapVal->serialize('literal');
                    }
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
                    if ($Fruitore['PartitaIva']) {
                        $PartitaIvaSoapVal = new soapval('ent:PartitaIva', 'ent:PartitaIva', $Fruitore['PartitaIva'], false, false);
                        $FruitoreString .= $PartitaIvaSoapVal->serialize('literal');
                    }
                    if ($Fruitore['Email']) {
                        $EmailSoapVal = new soapval('ent:Email', 'ent:Email', $Fruitore['Email'], false, false);
                        $FruitoreString .= $EmailSoapVal->serialize('literal');
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
        if ($richiesta['SezioniParametriSpecifici']) {
            $SezioniPrametriSpecificiString = "";
            foreach ($richiesta['SezioniParametriSpecifici'] as $Sezione) {
                $attrSezione = $Sezione['Sezione'];
                foreach ($Sezione['ParametriSpecifici'] as $ParametroSpecifico) {
                    $ParametroSpecificoSoapVal = new soapval('ent:ParametroSpecifico', 'ent:ParametroSpecifico', $ParametroSpecifico, false, false);
                    $ParametroSpecificoString .= $ParametroSpecificoSoapVal->serialize('literal');
                }
                $SezioniPrametriSpecificiString .= "<ent:SezioniParametriSpecifici Sezione=\"" . $attrSezione . "\">"
                        . "<ent:ParametriSpecifici>" . $ParametroSpecificoString . "</ent:ParametriSpecifici>"
                        . "</ent:SezioniParametriSpecifici>";
            }
            $RequestString .= $SezioniPrametriSpecificiString;
        }
        //ID_RUOLO
        if ($richiesta['ID_RUOLO']) {
            $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $richiesta['ID_RUOLO'], false, false);
            $RequestString .= $ID_RUOLOSoapVal->serialize('literal');
        }
        //NomeFileAcquisito
        $NomeFileAcquisitoSoapVal = new soapval('ent:NomeFileAcquisito', 'ent:NomeFileAcquisito', $richiesta['NomeFileAcquisito'], false, false);
        $RequestString .= $NomeFileAcquisitoSoapVal->serialize('literal');
        //RiferimentoPraticaEsternaPrecedente
        if ($richiesta['RiferimentoPraticaEsternaPrecedente']) {
            $RiferimentoPraticaEsternaPrecedenteSoapVal = new soapval('ent:RiferimentoPraticaEsternaPrecedente', 'ent:RiferimentoPraticaEsternaPrecedente', $richiesta['RiferimentoPraticaEsternaPrecedente'], false, false);
            $RequestString .= $RiferimentoPraticaEsternaPrecedenteSoapVal->serialize('literal');
        }
        //ScadenzaSoluzioneUnica
        if ($richiesta['ScadenzaSoluzioneUnica']) {
            $ScadenzaSoluzioneUnicaSoapVal = new soapval('ent:ScadenzaSoluzioneUnica', 'ent:ScadenzaSoluzioneUnica', $richiesta['ScadenzaSoluzioneUnica'], false, false);
            $RequestString .= $ScadenzaSoluzioneUnicaSoapVal->serialize('literal');
        }

//        $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $richiesta['ID_RUOLO'], false, false);
//        $RequestString .= $ID_RUOLOSoapVal->serialize('literal');
//        $RequestString = "<ent:PosizioniDebitoria>" . $RequestString . "</ent:PosizioniDebitoria>";
        $RequestString = $RequestString;
        return $RequestString;
    }

}
