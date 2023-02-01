<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    27.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proWizardCopiaAnalogica() {
    $proWizardCopiaAnalogica = new proWizardCopiaAnalogica();
    $proWizardCopiaAnalogica->parseEvent();
    return;
}

class proWizardCopiaAnalogica extends itaModel {

    public $PROT_DB;
    public $nameForm = "proWizardCopiaAnalogica";
    public $proLib;
    public $proLibAllegati;
    public $DatiDocumento = array();
    public $datiUtiP7m = array();
    public $PosizMarcatura = array();

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->DatiDocumento = App::$utente->getKey($this->nameForm . '_DatiDocumento');
        $this->datiUtiP7m = App::$utente->getKey($this->nameForm . '_datiUtiP7m');
        $this->PosizMarcatura = App::$utente->getKey($this->nameForm . '_PosizMarcatura');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_datiUtiP7m', $this->datiUtiP7m);
            App::$utente->setKey($this->nameForm . '_DatiDocumento', $this->DatiDocumento);
            App::$utente->setKey($this->nameForm . '_PosizMarcatura', $this->PosizMarcatura);
        }
    }

    public function setDatiDocumento($DatiDocumento) {
        $this->DatiDocumento = $DatiDocumento;
    }

    public function setDatiUtiP7m($datiUtiP7m) {
        $this->datiUtiP7m = $datiUtiP7m;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openform':
                $this->openDettaglio();
                $this->CreaCombo();
                break;
            case 'openAnaprosave':
                $this->openAnaprosave($this->IndiceRowid);
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_CopiaAnalogica':
                        $PosSegnatura = $_POST[$this->nameForm . '_PosMarc'];
                        $PosCopia = $_POST[$this->nameForm . '_PosizioneCopia'];
                        $PosizioniSegnatura = $this->proLibAllegati->GetPosizioniSegnatura();
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $ForcePosSegn['SEGNATURA'] = $PosizioniSegnatura[$PosSegnatura];
                        }
                        if ($PosCopia) {
                            $ForcePosSegn['COPIA'] = $PosizioniSegnatura[$PosCopia];
                        }
                        $p7m = unserialize($this->datiUtiP7m['p7m']);
                        $FileOriginale = $this->datiUtiP7m['NomeFileOriginale'];
                        if (!$this->proLibAllegati->GetCopiaAnalogica($this, $FileOriginale, $p7m->getFileName(), $this->DatiDocumento['ANAPRO_REC'], $this->DatiDocumento['ANADOC_REC'], $ForcePosSegn)) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_NomeFile_butt':
                        $p7m = unserialize($this->datiUtiP7m['p7m']);
                        $NomeFile = $this->datiUtiP7m['NomeFileContenuto'];
                        Out::openDocument(utiDownload::getUrl($NomeFile, $p7m->getContentFileName()));
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_DatiDocumento');
        App::$utente->removeKey($this->nameForm . '_datiUtiP7m');
        App::$utente->removeKey($this->nameForm . '_PosizMarcatura');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openDettaglio() {
//        datiUtiP7m
        //toggleAllegati
        if (!$this->datiUtiP7m['p7m']) {
            Out::msgStop("Attenzione", "Oggetto p7m mancante.");
            return false;
        }
        if (!$this->datiUtiP7m['NomeFileContenuto']) {
            Out::msgStop("Attenzione", "Nome file contenuto mancante.");
            return false;
        }
        $p7m = unserialize($this->datiUtiP7m['p7m']);
        $NomeFile = $this->datiUtiP7m['NomeFileContenuto'];
        Out::valore($this->nameForm . '_NomeFile', $NomeFile);
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_PosMarc', 1, "", "1", "Posizione predefinita");
        Out::select($this->nameForm . '_PosizioneCopia', 1, "", "1", "Posizione predefinita");
        $anaent_47 = $this->proLib->GetAnaent('47');
        $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
        foreach ($PosizioniMarcatura as $key => $Segnatura) {
            Out::select($this->nameForm . '_PosMarc', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
            Out::select($this->nameForm . '_PosizioneCopia', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
        }
//        Out::valore($this->nameForm . '_PosMarc', $anaent_47['ENTDE3']);
    }

}

?>
