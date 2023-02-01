<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDati.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praVars.class.php';

class praLibFirma {

    public $PRAM_DB;
    public $praLib;
    private $errCode;
    private $errMessage;
    private $htmlSoggetti = '';

    public function __construct($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
        $this->praLib = new praLib();
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

    /**
     * Esegue tutte le funzioni di verifica di espressione controllo firme
     * dato il p7m e l'array dati di richiesta.
     * 
     * @param type $p7m
     * @param type $dati
     * @return boolean
     */
    public function eseguiVerificaFirma($p7m, $dati) {
        $espressioneControlloFirma = $this->getEspressioneControlloFirma($dati['Ricite_rec']);
        if (!$espressioneControlloFirma) {
            /*
             * Non è presente un espressione di controllo, esco senza errori.
             */

            return true;
        }

        if ($dati['Proric_rec']['RICRPA']) {
            $praLibDati = praLibDati::getInstance($this->praLib);

            $praVar = new praVars();
            $praVar->setPRAM_DB($this->PRAM_DB);
            $praVar->setDati($praLibDati->prendiDati($dati['Proric_rec']['RICRPA']));
            $praVar->loadVariabiliSoggetti();
        } else {
            $praVar = new praVars();
            $praVar->setPRAM_DB($this->PRAM_DB);
            $praVar->setDati($dati);
            $praVar->loadVariabiliSoggetti();
        }

        $resultControlloFirma = $this->checkEspressioneControlloFirma($espressioneControlloFirma, $p7m, $praVar->getVariabiliSoggetti());

        if (!$resultControlloFirma) {
            $errorMessage = $this->getMessaggioControlloFirma($dati['Ricite_rec']);
            $this->errCode = -1;
            $this->errMessage = 'Errore in verifica firma' . ($errorMessage ? ': ' . $errorMessage : '.');

            if ($this->htmlSoggetti) {
                $this->errMessage .= '<br>' . $this->htmlSoggetti;
            }
            return false;
        }

        return true;
    }

    public function getEspressioneControlloFirma($ricite_rec) {
        $request = frontOfficeApp::$cmsHost->getRequest();

        if (isset($request['QualificaAllegato']['CLASSIFICAZIONE'])) {
            $codiceClassificazione = $request['QualificaAllegato']['CLASSIFICAZIONE'];
        } else {
            $metadati = unserialize($ricite_rec['ITEMETA']);
            $codiceClassificazione = $metadati['CODICECLASSIFICAZIONE'];
        }

        if (!$codiceClassificazione) {
            return false;
        }

        $anacla_rec = $this->praLib->GetAnacla($codiceClassificazione, 'codice', false, $this->PRAM_DB);

        if (!$anacla_rec['CLAEXPRCTRFIRMA']) {
            return false;
        }

        return $anacla_rec['CLAEXPRCTRFIRMA'];
    }

    public function getMessaggioControlloFirma($ricite_rec) {
        $request = frontOfficeApp::$cmsHost->getRequest();

        if (isset($request['QualificaAllegato']['CLASSIFICAZIONE'])) {
            $codiceClassificazione = $request['QualificaAllegato']['CLASSIFICAZIONE'];
        } else {
            $metadati = unserialize($ricite_rec['ITEMETA']);
            $codiceClassificazione = $metadati['CODICECLASSIFICAZIONE'];
        }

        if (!$codiceClassificazione) {
            return false;
        }

        $anacla_rec = $this->praLib->GetAnacla($codiceClassificazione, 'codice', false, $this->PRAM_DB);

        if (!$anacla_rec['CLAMSGCTRFIRMA']) {
            return false;
        }

        return $anacla_rec['CLAMSGCTRFIRMA'];
    }

    private function getDescrizioneRuolo($codiceRuolo) {
        if (isset($this->_cacheRuoli[$codiceRuolo])) {
            return $this->_cacheRuoli[$codiceRuolo];
        }

        $sql = "SELECT RUODES FROM ANARUO WHERE RUOCOD = '" . addslashes($codiceRuolo) . "'";
        $anaruo_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $this->_cacheRuoli[$codiceRuolo] = $anaruo_rec['RUODES'];
        return $this->_cacheRuoli[$codiceRuolo];
    }

    public function checkEspressioneControlloFirma($espressione, $p7mobj, $dictionary) {
        $arraySoggetti = $dictionary->getAllData();
        $arraySoggettiCoinvolti = array();

        /*
         * Fix per rilevazione codice fiscale controfirmatario 
         * 
         * @Michele Moscioni 20/01/2020
         * 
        $infoSummary = $p7mobj->getInfoSummary();
        $codiciFiscaliP7M = array_map('strtoupper', array_column($infoSummary, 'fiscalCode'));
        */
        
        $certSummary = $p7mobj->getCertificateSummary();
        $codiciFiscaliP7M = array_map('strtoupper', array_column($certSummary, 'fiscalCode'));
        /*
         * Fine Fix
         * 
         */
        
        
        
        /*
         * Eseguo il match di tutte le variabili SOGGETTO nell'espressione.
         */

        $matches = array();
        $regex = '/\[?(SOGGETTO\d\d\d\d)(?:,(\d))?\]?/m';
        preg_match_all($regex, $espressione, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $chiaveRuolo = $match[1];
            $descrizioneRuolo = $this->getDescrizioneRuolo(substr($chiaveRuolo, -4));
            $verificaTutti = isset($match[2]) && $match[2] == '1' ? true : false;

            if (!count($arraySoggetti[$chiaveRuolo])) {
                /*
                 * Se non ci sono soggetti relativi a quel ruolo,
                 * la condizione non è soddisfatta.
                 */

                $imageEsito = '<img src="' . frontOfficeLib::getIcon('error') . '" style="width: 32px; height: 32px; vertical-align: middle; margin-left: -5px;">';
                $this->htmlSoggetti .= "<br>$imageEsito <span style=\"vertical-align: middle;\">Soggetti con ruolo '$descrizioneRuolo' non presenti</span>";

                $espressione = str_replace($match[0], 'false', $espressione);
                continue;
            }

            $countFirmePresenti = 0;
            $countFirmeNecessarie = $verificaTutti ? count($arraySoggetti[$chiaveRuolo]) : 1;

            foreach ($arraySoggetti[$chiaveRuolo] as $soggetto) {
                if (!$soggetto['CODICEFISCALE_CFI']) {
                    $imageEsito = '<img src="' . frontOfficeLib::getIcon('error') . '" style="width: 32px; height: 32px; vertical-align: middle; margin-left: -5px;">';
                    $this->htmlSoggetti .= "<br>$imageEsito <span style=\"vertical-align: middle;\">{$soggetto['NOME']} {$soggetto['COGNOME']}: Codice Fiscale non presente</span>";
                    continue;
                }

                $codiceFiscaleSoggetto = strtoupper($soggetto['CODICEFISCALE_CFI']);

                if ($this->checkPresenzaCodiceFiscale($codiceFiscaleSoggetto, $codiciFiscaliP7M)) {
                    $countFirmePresenti++;
                    $soggetto['ESITO'] = true;
                } else {
                    $soggetto['ESITO'] = false;
                }

                if (!isset($arraySoggettiCoinvolti[$codiceFiscaleSoggetto]) || $arraySoggettiCoinvolti[$codiceFiscaleSoggetto]['ESITO'] === false) {
                    $arraySoggettiCoinvolti[$codiceFiscaleSoggetto] = $soggetto;
                }
            }

            $resultSoggetto = $countFirmePresenti >= $countFirmeNecessarie ? 'true' : 'false';
            $espressione = str_replace($match[0], $resultSoggetto, $espressione);
        }

        foreach ($arraySoggettiCoinvolti as $soggetto) {
            $imageEsito = '<img src="' . frontOfficeLib::getIcon('shield-' . ($soggetto['ESITO'] ? 'ok' : 'error')) . '" style="width: 32px; height: 32px; vertical-align: middle; margin-left: -5px;">';
            $this->htmlSoggetti .= "<br>$imageEsito <span style=\"vertical-align: middle;\">{$soggetto['NOME']} {$soggetto['COGNOME']} ({$soggetto['CODICEFISCALE_CFI']})</span>";
        }

        $result = eval("return ($espressione);");

        return (boolean) $result;
    }

    private function checkPresenzaCodiceFiscale($codiceFiscale, $arrayCodiciFiscali) {
        foreach ($arrayCodiciFiscali as $checkCodiceFiscale) {
            if (strpos($checkCodiceFiscale, $codiceFiscale) !== false) {
                return true;
            }
        }

        return false;
    }

}
