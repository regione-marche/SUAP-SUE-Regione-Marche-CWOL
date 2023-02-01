<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/lib/itaPHPCityWare/itaLibCity.class.php';

function utiCapSituazione() {
    $utiCapSituazione = new utiCapSituazione();
    $utiCapSituazione->parseEvent();
    return;
}

class utiCapSituazione extends itaModel {

    public $nameForm = "utiCapSituazione";
    public $divGes = "utiCapSituazione_divGestioni";
    public $capitolo;
    public $itaLibCity;

    function __construct() {
        parent::__construct();
        $this->itaLibCity = new itaLibCity();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function setCapitolo($capitolo) {
        $this->capitolo = $capitolo;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->divGes);
                $this->visualizzaStato();
                break;
            case 'onClick':
                break;
            case 'dbClickRow':

                break;
            case 'cellSelect':

                break;
        }
    }

    public function close() {
        parent::close();

        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function visualizzaStato() {
        switch ($this->capitolo['E_S']) {
            case 'S':
                $Parte = 'SPESA';
                break;
            case 'E':
                $Parte = 'ENTRATA';
                break;
        }
        Out::valore($this->nameForm . '_Parte', $Parte);
        Out::valore($this->nameForm . '_Meccanografico', $this->capitolo['CODMECCAN']);
        Out::valore($this->nameForm . '_CodVoce', $this->capitolo['CODVOCEBIL']);
        // Anno corrente - Stanziamento Competenza
        Out::valore($this->nameForm . '_PrevIniziale', $this->itaLibCity->importoEuro($this->capitolo['IMPO_PREAC']));
        Out::valore($this->nameForm . '_VariazioniPiu', $this->itaLibCity->importoEuro($this->capitolo['IMPV_COVP']));
        Out::valore($this->nameForm . '_VariazioniMeno', $this->itaLibCity->importoEuro($this->capitolo['IMPV_COVN']));
        $assestatoCompetenza = $this->capitolo['IMPO_PREAC'] + $this->capitolo['IMPV_COVP'] - $this->capitolo['IMPV_COVN'];
        Out::valore($this->nameForm . '_Assestato', $this->itaLibCity->importoEuro($assestatoCompetenza));
        // Anno corrente - Fondo Pluriennale Vincolato
        Out::valore($this->nameForm . '_PrevIniziale2', $this->itaLibCity->importoEuro($this->capitolo['IMPO_FPLUV']));
        Out::valore($this->nameForm . '_VariazioniPiu2', $this->itaLibCity->importoEuro($this->capitolo['IMPV_FPVP']));
        Out::valore($this->nameForm . '_VariazioniMeno2', $this->itaLibCity->importoEuro($this->capitolo['IMPV_FPVN']));
        $assestatoFondoPluriennale = $this->capitolo['IMPO_FPLUV'] + $this->capitolo['IMPV_FPVP'] - $this->capitolo['IMPV_FPVN'];
        Out::valore($this->nameForm . '_Assestato2', $this->itaLibCity->importoEuro($assestatoFondoPluriennale));
        Out::valore($this->nameForm . '_AssestatoTot', $this->itaLibCity->importoEuro($assestatoCompetenza + $assestatoFondoPluriennale));
        // Risultati della Gestione
        // Competenza
        Out::valore($this->nameForm . '_AssestatoEs', $this->itaLibCity->importoEuro($assestatoCompetenza + $assestatoFondoPluriennale));
        Out::valore($this->nameForm . '_ImpegniPrenotati', $this->itaLibCity->importoEuro($this->capitolo['IMPO_IAPR']));
        Out::valore($this->nameForm . '_ImpegniDefinitivi', $this->itaLibCity->importoEuro($this->capitolo['IMPO_IMAC']));
        $totaleImpegni = $this->capitolo['IMPO_IAPR'] + $this->capitolo['IMPO_IMAC'];
        Out::valore($this->nameForm . '_TotaleImpegni', $this->itaLibCity->importoEuro($totaleImpegni));
        Out::valore($this->nameForm . '_Economie', $this->itaLibCity->importoEuro($this->capitolo['xxxxxxx']));
        Out::valore($this->nameForm . '_DispImpegnare', $this->itaLibCity->importoEuro($assestatoCompetenza - $totaleImpegni));
        Out::valore($this->nameForm . '_Pagato', $this->itaLibCity->importoEuro($this->capitolo['IMPO_PARI']));
        $dispPagare = $totaleImpegni + ($assestatoCompetenza - $totaleImpegni) - $this->capitolo['IMPO_PARI'];
        Out::valore($this->nameForm . '_DispPagare', $this->itaLibCity->importoEuro($dispPagare));
        $residuiGeneratiComp = $this->capitolo['IMPO_IMAC'] - $this->capitolo['IMPO_PARI'];
        $residuiGeneratiRes = $this->capitolo['IMPO_IARE'] - $this->capitolo['IMPO_PRRE'];
        Out::valore($this->nameForm . '_ResiduiGenerali', $this->itaLibCity->importoEuro($residuiGeneratiComp));
        Out::valore($this->nameForm . '_TotaleResidui', $this->itaLibCity->importoEuro($residuiGeneratiComp + $residuiGeneratiRes));
        // Residuo
        Out::valore($this->nameForm . '_AssestatoEsRes', $this->itaLibCity->importoEuro($this->capitolo['IMPO_REAP']));
        Out::valore($this->nameForm . '_ImpegniDefinitiviRes', $this->itaLibCity->importoEuro($this->capitolo['IMPO_IARE']));
        Out::valore($this->nameForm . '_TotaleImpegniRes', $this->itaLibCity->importoEuro($this->capitolo['IMPO_IARE']));
        $dispImpegnareRes = $this->capitolo['IMPO_REAP'] - $this->capitolo['IMPO_IARE'];
        Out::valore($this->nameForm . '_DispImpegnareRes', $this->itaLibCity->importoEuro($dispImpegnareRes));
        Out::valore($this->nameForm . '_PagatoRes', $this->itaLibCity->importoEuro($this->capitolo['IMPO_PRRE']));
        Out::valore($this->nameForm . '_DispPagareRes', $this->itaLibCity->importoEuro($dispImpegnareRes));
        Out::valore($this->nameForm . '_ResiduiGeneraliRes', $this->itaLibCity->importoEuro($residuiGeneratiRes));
        
        //Out::msgInfo('', print_r($this->capitolo, true));
    }

}

?>
