<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ici/iciLib.class.php';

function utiVediTrib() {
    $utiVediTrib = new utiVediTrib();
    $utiVediTrib->parseEvent();
    return;
}

class utiVediTrib extends itaModel {

    public $TRIB_DB;
    public $iciLib;
    public $nameForm = "utiVediTrib";
    public $divGes = "utiVediTrib_divGestione";
    public $gridRuolo = "utiVediTrib_gridRuolo";

    function __construct() {
        parent::__construct();
        try {
            $this->catLib = new catLib();
            $this->iciLib = new iciLib();
            $this->TRIB_DB = $this->catLib->getTRIBDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $cf = $_POST['cf'];
                if ($_POST['CF'] != '') {
                    $cf = $_POST['CF'];
                }
                $immobile = $_POST['immobile'];
                if ($cf != '') {
                    $this->dettaglioSoggetto($cf);
                }
                if ($immobile) {
                    $this->dettaglioImmobile($immobile);
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function dettaglioSoggetto($cf) {
        $anagra_rec = $this->cercaSoggettoTributi($cf, 'cf');
        if ($anagra_rec == false) {
            Out::msgInfo("Attenzione", "Codice Fiscale $cf non presente");
            Out::closeDialog($this->nameForm);
            return false;
        }
        $resultRec = $this->assegnaDatiAnagrafici($anagra_rec);
        $righeRuolo = array();
        $sql = "SELECT * FROM MOVRUO LEFT OUTER JOIN ANARUO ANARUO ON MOVRUO.CODRUO = ANARUO.CODRUO WHERE CODTRI = " . $anagra_rec['CODTRI'];
        $tabellaRuolo = ItaDB::DBSQLSelect($this->TRIB_DB, $sql, true);
        $righeRuolo = $this->assegnaRigheRuolo($tabellaRuolo);
        $this->visualizzaDatiAnagrafici($resultRec);
        $this->visualizzaModalita('cf');
        $this->visualizzaRigheRuolo($righeRuolo);
    }

    public function dettaglioImmobile($immobile) {
        $sez = $immobile['sezione'];
        $fog = $immobile['foglio'];
        $num = $immobile['numero'];
        $sub = $immobile['sub'];
        if (trim($sez) == '' && trim($fog) == '' && trim($num) == '' && trim($sub) == '') {
            Out::msgInfo("Attenzione", "Estremi dell'immobile non presenti.");
            Out::closeDialog($this->nameForm);
            return false;
        }
        $sql = "SELECT * FROM ANAIMB WHERE IMBSEZ = '$sez' AND IMBFOG = '$fog' AND IMBNUM = '$num' AND IMBSUB = '$sub'";
        $tabellaImmobili = ItaDB::DBSQLSelect($this->TRIB_DB, $sql, true);
        if (!$tabellaImmobili) {
            Out::msgInfo("Attenzione", "Non trovati immobile con estremi richiesti.");
            Out::closeDialog($this->nameForm);
            return false;
        }
        $codiceImmobile = $tabellaImmobili[0]['IMBFIL'];
        $righeRuolo = array();
        $sql = "SELECT * FROM MOVRUO LEFT OUTER JOIN ANARUO ANARUO ON MOVRUO.CODRUO = ANARUO.CODRUO WHERE MOVIMM = $codiceImmobile";
        $tabellaRuolo = ItaDB::DBSQLSelect($this->TRIB_DB, $sql, true);
        $righeRuolo = $this->assegnaRigheRuolo($tabellaRuolo);
        $codiceSoggetto = $tabellaRuolo[0]['CODTRI'];
        $anagra_rec = $this->cercaSoggettoTributi($codiceSoggetto, 'codice');
        if ($anagra_rec == false) {
            Out::msgInfo("Attenzione", "Codice Fiscale $cf non presente");
            Out::closeDialog($this->nameForm);
            return false;
        }
        $resultRec = $this->assegnaDatiAnagrafici($anagra_rec);
        $this->visualizzaDatiAnagrafici($resultRec);
        $this->visualizzaModalita('immobile');
        $this->visualizzaRigheRuolo($righeRuolo);
    }

    public function cercaSoggettoTributi($codice, $tipo) {
        if ($codice != '') {
            if ($tipo == 'cf') {
                $codice = sprintf("%16s", $codice);
                $sql = "SELECT * FROM ANAGRA WHERE CODFIS='$codice'";
            }
            if ($tipo == 'codice') {
                $sql = "SELECT * FROM ANAGRA WHERE CODTRI=$codice";
            }
            $anagra_rec = ItaDB::DBSQLSelect($this->TRIB_DB, $sql, false);
            if ($anagra_rec) {
                return $anagra_rec;
            }
            return false;
        } else {
            return false;
        }
    }

    public function assegnaDatiAnagrafici($anagra_rec) {
        $resultRec['COGNOME'] = $anagra_rec['COGNOM'];
        $resultRec['NOME'] = $anagra_rec['NOME'];
        $resultRec['NATOA'] = $anagra_rec['INDNAT'];
        $datnat = sprintf("%02d", $anagra_rec['GGNAT']) . '/' . sprintf("%02d", $anagra_rec['MMNAT']) . '/' . sprintf("%04d", $anagra_rec['AANAT']);
        if ($anagra_rec['GGNAT'] != 0) {
            $resultRec['DATNAT'] = $datnat;
        } else {
            $resultRec['DATNAT'] = '';
        }
        $resultRec['PROVNA'] = $anagra_rec['PROVNA'];
        if ($anagra_rec['RESIDE'] == '') {
            $anindi_rec = $this->iciLib->GetAnindiTrib($anagra_rec['CODIND']);
            $resultRec['VIARESIDENZA'] = $anindi_rec['SPECIE'] . ' ' . $anindi_rec['INDIR'];
            $resultRec['CIVICO'] = $anagra_rec['CIVICO'];
            $anacit_rec = $this->iciLib->GetAnacitTrib($anagra_rec['CODCIT']);
            $resultRec['CITTARESIDENZA'] = $anacit_rec['RESID'];
            $resultRec['PRORES'] = '';
        } else {
            $sql = "SELECT * FROM TRIBUT WHERE CODTRI=" . $anagra_rec['CODTRI'];
            $tribut_rec = ItaDB::DBSQLSelect($this->TRIB_DB, $sql, false);
            $resultRec['VIARESIDENZA'] = $tribut_rec['INDRES'];
            $resultRec['CIVICO'] = $tribut_rec['CIVRES'];
            $resultRec['CITTARESIDENZA'] = $tribut_rec['CITRES'];
            $resultRec['PRORES'] = $tribut_rec['PROVRE'];
        }
        return $resultRec;
    }

    public function assegnaRigheRuolo($tabellaRuolo) {
        $indice = 0;
        $righeRuolo = array();
        foreach ($tabellaRuolo as $ruolo) {
            $righeRuolo[$indice]['CODICE'] = $ruolo['CODRUO'];
            $righeRuolo[$indice]['DESCRIZIONE'] = $ruolo['DESCRU'];
            $righeRuolo[$indice]['TIPO'] = $ruolo['TIPO'];
            $righeRuolo[$indice]['ANNO'] = $ruolo['VC'];
            $righeRuolo[$indice]['MESI'] = $ruolo['SEMEST'];
            $righeRuolo[$indice]['TARIFFA'] = $ruolo['PREZZO'];
            $righeRuolo[$indice]['QUANTITA'] = $ruolo['QTARUO'];
            $anindi_rec = $this->iciLib->GetAnindiTrib($ruolo['CODIND']);
            $righeRuolo[$indice]['UBICAZIONE'] = $anindi_rec['SPECIE'] . ' ' . $anindi_rec['INDIR'] . $ruolo['MCIVIC'];
            $indice++;
        }
        return $righeRuolo;
    }

    public function visualizzaDatiAnagrafici($resultRec) {
        Out::valore($this->nameForm . '_COGNOME', $resultRec['COGNOME']);
        Out::valore($this->nameForm . '_NOME', $resultRec['NOME']);
        Out::valore($this->nameForm . '_DATNAT', $resultRec['DATNAT']);
        Out::valore($this->nameForm . '_NATOA', $resultRec['NATOA']);
        Out::valore($this->nameForm . '_PROVNA', $resultRec['PROVNA']);
        Out::valore($this->nameForm . '_CITTARESIDENZA', $resultRec['CITTARESIDENZA']);
        Out::valore($this->nameForm . '_PRORES', $resultRec['PRORES']);
        Out::valore($this->nameForm . '_VIARESIDENZA', $resultRec['VIARESIDENZA']);
        Out::valore($this->nameForm . '_CIVICO', $resultRec['CIVICO']);
    }

    public function visualizzaRigheRuolo($righeRuolo) {
        $this->CaricaGriglia($this->gridRuolo, $righeRuolo);
        TableView::enableEvents($this->gridRuolo);
    }

    public function visualizzaModalita($modo) {
        switch ($modo) {
            case 'cf':
                $avviso = 'Risultato dalla ricerca da Codice Fiscale';
                break;
            case 'immobile':
                $avviso = 'Risultato dalla ricerca da Estremi Immobile';
                break;
        }
        $contenuto = "<span style=\"color: red; text-shadow: 1px 1px 1px #000; \"> <b><font size=\"3px\">$avviso</font> </b></span>";
        Out::html($this->nameForm . '_divSegnala', $contenuto);
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                        $griglia,
                        array('arrayTable' => $appoggio,
                            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($appoggio));
        } else if ($tipo == '3') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageRows($_POST['rows']);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        $ita_grid01->getDataPage('json');
        return;
    }

}

?>
