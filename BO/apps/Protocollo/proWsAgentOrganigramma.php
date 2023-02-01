<?php

/**
 *
 * Raccolta di funzioni per il web service Organigramma Protocollo
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft Srl
 * @license
 * @version    02.01.2017
 * @link
 * @see
 * @since
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proOrganigrammaDBLib.class.php');

class proWsAgentOrganigramma extends wsModel {

    public $errCode;
    public $errMessage;

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function insertNodo($codice = '', $descrizione = '', $abbreviazione = '', $codicepadre = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['UFFCOD'] = $codice;
        $dati['UFFDES'] = $descrizione;
        $dati['UFFABB'] = $abbreviazione;
        $dati['CODICE_PADRE'] = $codicepadre;

        $esito = $proDBLib->insertUfficio($dati,'');
        if ($esito === false) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $esito;
    }

    function updateNodo($codice = '', $descrizione = '', $abbreviazione = '', $annullamento = '', $codicepadre = '', $codiceresponsabile = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['UFFCOD'] = $codice;
        $dati['UFFDES'] = $descrizione;
        $dati['UFFABB'] = $abbreviazione;
        $dati['UFFANN'] = $annullamento;
        $dati['CODICE_PADRE'] = $codicepadre;
        $dati['UFFRES'] = $codiceresponsabile;

        $esito = $proDBLib->updateUfficio($dati, '');
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }

    function deleteNodo($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['UFFCOD'] = $codice;

        $esito = $proDBLib->deleteUfficio($dati, '');
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }

    function getNodo($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['UFFCOD'] = $codice;

        $ufficio = $proDBLib->getUfficio($dati);
        if ($ufficio == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $ufficio;
    }

    function getElencoNodi() {
        $proDBLib = new proOrganigrammaDBLib();

        $uffici = $proDBLib->getUffici();
        if ($uffici == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $uffici;
    }

    function getSoggetto($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['MEDCOD'] = $codice;

        $soggetto = $proDBLib->getSoggetto($dati);
        if ($soggetto == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $soggetto;
    }

    function getSoggetti() {
        $proDBLib = new proOrganigrammaDBLib();

        $soggetti = $proDBLib->getSoggetti();
        if ($soggetti == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $soggetti;
    }

    function insertSoggetto($codice = '', $titolo = '', $denominazione = '', $cf_pi = '', $indirizzo = '', $cap = '', $citta = '', $provincia = '', $posta_elettronica = '', $telefono = '', $cellulare = '', $fax = '', $codiceAOO = '', $denominazioneAOO = '', $tipo_indirizzo_telematico = '', $annullamento = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['MEDCOD'] = $codice;
        $dati['MEDTIT'] = $titolo;
        $dati['MEDNOM'] = strtoupper($denominazione);
        $dati['MEDFIS'] = $cf_pi;
        $dati['MEDIND'] = strtoupper($indirizzo);
        $dati['MEDCAP'] = $cap;
        $dati['MEDCIT'] = strtoupper($citta);
        $dati['MEDPRO'] = strtoupper($provincia);
        $dati['MEDEMA'] = $posta_elettronica;
        $dati['MEDTEL'] = $telefono;
        $dati['MEDCELL'] = $cellulare;
        $dati['MEDFAX'] = $fax;
        $dati['MEDCODAOO'] = $codiceAOO;
        $dati['MEDDENAOO'] = $denominazioneAOO;
        $dati['MEDTIPIND'] = $tipo_indirizzo_telematico;
        $dati['MEDANN'] = $annullamento;
        $dati['MEDUFF'] = 'true';

        $esito = $proDBLib->insertSoggetto($dati, '');
        if ($esito === false) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function updateSoggetto($codice = '', $titolo = '', $denominazione = '', $cf_pi = '', $indirizzo = '', $cap = '', $citta = '', $provincia = '', $posta_elettronica = '', $telefono = '', $cellulare = '', $fax = '', $codiceAOO = '', $denominazioneAOO = '', $tipo_indirizzo_telematico = '', $annullamento = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['MEDCOD'] = $codice;
        $dati['MEDTIT'] = $titolo;
        $dati['MEDNOM'] = strtoupper($denominazione);
        $dati['MEDFIS'] = $cf_pi;
        $dati['MEDIND'] = strtoupper($indirizzo);
        $dati['MEDCAP'] = $cap;
        $dati['MEDCIT'] = strtoupper($citta);
        $dati['MEDPRO'] = strtoupper($provincia);
        $dati['MEDEMA'] = $posta_elettronica;
        $dati['MEDTEL'] = $telefono;
        $dati['MEDCELL'] = $cellulare;
        $dati['MEDFAX'] = $fax;
        $dati['MEDCODAOO'] = $codiceAOO;
        $dati['MEDDENAOO'] = $denominazioneAOO;
        $dati['MEDTIPIND'] = $tipo_indirizzo_telematico;
        $dati['MEDANN'] = $annullamento;

        $esito = $proDBLib->updateSoggetto($dati, '');
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function deleteSoggetto($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['MEDCOD'] = $codice;

        $esito = $proDBLib->deleteSoggetto($dati);
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function associaSoggetto2Uffici($codiceSoggetto, $uffici) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['SOGGETTO'] = $codiceSoggetto;
        $dati['UFFICI'] = $uffici;
        $esito = $proDBLib->associaSoggetto2Uffici($dati);
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function insertRuolo($codice = '', $descrizione = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['RUOCOD'] = $codice;
        $dati['RUODES'] = $descrizione;

        $esito = $proDBLib->insertRuolo($dati);
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function updateRuolo($codice = '', $descrizione = '') {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['RUOCOD'] = $codice;
        $dati['RUODES'] = $descrizione;

        $esito = $proDBLib->updateRuolo($dati);
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function deleteRuolo($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['RUOCOD'] = $codice;

        $esito = $proDBLib->deleteRuolo($dati);
        if ($esito !== true) {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return true;
    }
    
    function getRuolo($codice) {
        $proDBLib = new proOrganigrammaDBLib();
        $dati['RUOCOD'] = $codice;

        $ruolo = $proDBLib->getRuolo($dati);
        if ($ruolo == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $ruolo;
    }
 
    function getRuoli() {
        $proDBLib = new proOrganigrammaDBLib();

        $ruoli = $proDBLib->getRuoli();
        if ($ruoli == '') {
            $this->setErrMessage($proDBLib->getErrMessage());
            return false;
        }
        return $ruoli;
    }
    
    
}
