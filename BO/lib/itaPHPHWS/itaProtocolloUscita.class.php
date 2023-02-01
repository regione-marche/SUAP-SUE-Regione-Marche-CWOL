<?php

/**
 * Description of itaProtocolloUscita
 *
 * @author Mario Mazza <mario.mazza@italsoft.eu>
 */
class itaProtocolloUscita {

    private $accesso;   //obbligatorio
    private $anno;  //obbligatorio
    private $aoo;   //obbligatorio
    private $classificazione;   //obbligatorio
    private $codice;    //obbligatorio
    private $codiceOperatore;   //obbligatorio
    private $codiceSpedizione;  //obbligatorio
    private $comunicazioneInterna;  //obbligatorio
    private $corrispondente;    //obbligatorio
    private $dataDocumento; //optional
    private $dataRegistrazione; //optional
    private $dataScadenza;  //optional
    private $flagCartaceo;  //obbligatorio
    private $flagInArchivio;    //obbligatorio
    private $note;  //optional
    private $numero;    //obbligatorio
    private $fascicoli;    //obbligatorio
    private $oggetto;   //optional
    private $protocolloCollegato;   //obbligatorio
    private $protocolloEmergenza;   //obbligatorio
    private $protocolloRiscontro;   //obbligatorio
    private $segnatura; //optional
    private $statoPratica;  //obbligatorio
    private $statoProtocollo;   //obbligatorio
    private $tipo;  //obbligatorio
    private $ufficio;   //obbligatorio
    private $documentoPrincipale;   //optional
    private $allegati;  //optional
    private $jdbc;

//set()
    public function setAccesso($accesso) {
        $this->accesso = $accesso;
    }
    public function setAnno($anno) {
        $this->anno = $anno;
    }
    public function setAoo($aoo) {
        $this->aoo = $aoo;
    }
    public function setClassificazione($classificazione) {
        $this->classificazione = $classificazione;
    }
    public function setCodice($codice) {
        $this->codice = $codice;
    }
    public function setCodiceOperatore($codiceOperatore) {
        $this->codiceOperatore = $codiceOperatore;
    }
    public function setCodiceSpedizione($codiceSpedizione) {
        $this->codiceSpedizione = $codiceSpedizione;
    }
    public function setComunicazioneInterna($comunicazioneInterna) {
        $this->comunicazioneInterna = $comunicazioneInterna;
    }
    public function setCorrispondente($corrispondente) {
        $this->corrispondente = $corrispondente;
    }
    public function setDataDocumento($dataDocumento) {
        $this->dataDocumento = $dataDocumento;
    }
    public function setDataRegistrazione($dataRegistrazione) {
        $this->dataRegistrazione = $dataRegistrazione;
    }
    public function setDataScadenza($dataScadenza) {
        $this->dataScadenza = $dataScadenza;
    }
    public function setFlagCartaceo($flagCartaceo) {
        $this->flagCartaceo = $flagCartaceo;
    }
    public function setFlagInArchivio($flagInArchivio) {
        $this->flagInArchivio = $flagInArchivio;
    }
    public function setNote($note) {
        $this->note = $note;
    }
    public function setNumero($numero) {
        $this->numero = $numero;
    }
    public function setFascicoli($fascicolo) {
        $this->fascicoli = $fascicolo;
    }
    public function setOggetto($oggetto) {
        $this->oggetto = $oggetto;
    }
    public function setProtocolloCollegato($protocolloCollegato) {
        $this->protocolloCollegato = $protocolloCollegato;
    }
    public function setProtocolloEmergenza($protocolloEmergenza) {
        $this->protocolloEmergenza = $protocolloEmergenza;
    }
    public function setProtocolloRiscontro($protocolloRiscontro) {
        $this->protocolloRiscontro = $protocolloRiscontro;
    }
    public function setSegnatura($segnatura) {
        $this->segnatura = $segnatura;
    }
    public function setStatoPratica($statoPratica) {
        $this->statoPratica = $statoPratica;
    }
    public function setStatoProtocollo($statoProtocollo) {
        $this->statoProtocollo = $statoProtocollo;
    }
    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
    public function setUfficio($ufficio) {
        $this->ufficio = $ufficio;
    }
    public function setDocumentoPrincipale($documentoPrincipale) {
        $this->documentoPrincipale = $documentoPrincipale;
    }
    public function setAllegati($allegati) {
        $this->allegati = $allegati;
    }
    public function setJDBC($jdbc) {
        $this->jdbc = $jdbc;
    }    

//get()
    public function getAccesso() {
        return $this->accesso;
    }
    public function getAnno() {
        return $this->anno;
    }
    public function getAoo() {
        return $this->aoo;
    }
    public function getCodice() {
        return $this->codice;
    }
    public function getClassificazione() {
        return $this->classificazione;
    }
    public function getCodiceOperatore() {
        return $this->codiceOperatore;
    }
    public function getCodiceSpedizione() {
        return $this->codiceSpedizione;
    }
    public function getComunicazioneInterna() {
        return $this->comunicazioneInterna;
    }
    public function getCorrispondente() {
        return $this->corrispondente;
    }
    public function getDataDocumento() {
        return $this->dataDocumento;
    }
    public function getDataRegistrazione() {
        return $this->dataRegistrazione;
    }
    public function getDataScadenza() {
        return $this->dataScadenza;
    }
    public function getFlagCartaceo() {
        return $this->flagCartaceo;
    }
    public function getFlagInArchivio() {
        return $this->flagInArchivio;
    }
    public function getNote() {
        return $this->note;
    }
    public function getNumero() {
        return $this->numero;
    }
    public function getFascicoli() {
        return $this->fascicoli;
    }
    public function getOggetto() {
        return $this->oggetto;
    }
    public function getProtocolloCollegato() {
        return $this->protocolloCollegato;
    }
    public function getProtocolloEmergenza() {
        return $this->protocolloEmergenza;
    }
    public function getProtocolloRiscontro() {
        return $this->protocolloRiscontro;
    }
    public function getSegnatura() {
        return $this->segnatura;
    }
    public function getStatoPratica() {
        return $this->statoPratica;
    }
    public function getStatoProtocollo() {
        return $this->statoProtocollo;
    }
    public function getTipo() {
        return $this->tipo;
    }
    public function getUfficio() {
        return $this->ufficio;
    }
    public function getDocumentoPrincipale() {
        return $this->documentoPrincipale;
    }
    public function getAllegati() {
        return $this->allegati;
    }
    public function getJDBC() {
        return $this->jdbc;
    }
    
}

?>
