<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCustomClass.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praCompPassoGest.php';

function praCompDatiAggiuntiviForm() {
    $praCompDatiAggiuntiviForm = new praCompDatiAggiuntiviForm();
    $praCompDatiAggiuntiviForm->parseEvent();
    return;
}

class praCompDatiAggiuntiviForm extends praCompPassoGest { // itaModel {

    public $praLib;
    public $praLibElaborazioneDati;
    public $praLibHtml;
    public $PRAM_DB;
    public $nameForm = 'praCompDatiAggiuntiviForm';
    protected $gesnum;
    protected $propak;
    //private $gesnum;
    //private $propak;
    private $datiAggiuntiviTab;
    protected $readOnly = false;

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->praLib = new praLib();
        $this->praLibElaborazioneDati = new praLibElaborazioneDati();
        $this->praLibHtml = new praLibHtml();
        $this->PRAM_DB = ItaDB::DBOpen('PRAM');

        $this->gesnum = App::$utente->getKey($this->nameForm . '_gesnum');
        $this->propak = App::$utente->getKey($this->nameForm . '_propak');
        $this->readOnly = App::$utente->getKey($this->nameForm . '_readOnly');
        $this->datiAggiuntiviTab = App::$utente->getKey($this->nameForm . '_datiAggiuntiviTab');

        $this->gridDatiAggiuntivi = $this->nameForm . '_gridDatiAggiuntivi';
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gesnum', $this->gesnum);
            App::$utente->setKey($this->nameForm . '_propak', $this->propak);
            App::$utente->setKey($this->nameForm . '_readOnly', $this->readOnly);
            App::$utente->setKey($this->nameForm . '_datiAggiuntiviTab', $this->datiAggiuntiviTab);
        }
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':
                Out::css($this->nameForm . '_workSpace', 'overflow', 'auto');
                Out::delClass($this->nameForm . '_workSpace', 'ui-corner-all ui-widget-content');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'onBlur':
            case 'onChange':
                preg_match_all('/\[(\w+)\]\[(\w+)\]/', $_POST['id'], $matches);
                $dagset = $matches[1][0];
                $dagkey = $matches[2][0];
                $rowid = $this->getRowidDato($dagset, $dagkey);

                if (!$rowid) {
                    /*
                     * Caso di RadioButton, poiché esclusi dalla griglia non hanno una relativa
                     * chiave su $this->datiAggiuntiviTab
                     */

                    $prodag_rec = $this->selectRecord(array('DAGKEY' => $dagkey));
                    $meta = unserialize($prodag_rec['DAGMETA']);
                    $radioGroupKey = $meta['ATTRIBUTICAMPO']['NAME'];
                    $radioGroupRowid = $this->getRowidDato($matches[1][0], $radioGroupKey);

                    $this->datiAggiuntiviTab[$radioGroupRowid]['DAGVAL'] = $_POST[$this->nameForm . '_DATIAGGIUNTIVI'][$dagset][$radioGroupKey];
                    break;
                }

                switch ($this->datiAggiuntiviTab[$rowid]['DAGTIC']) {
                    case 'CheckBox':
                        $meta = unserialize($this->datiAggiuntiviTab[$rowid]['DAGMETA']);
                        $states = explode('/', $meta['ATTRIBUTICAMPO']['RETURNVALUES']);
//                        
                        $this->datiAggiuntiviTab[$rowid]['DAGVAL'] = ($_POST[$this->nameForm . '_DATIAGGIUNTIVI'][$dagset][$dagkey] == 1) ? $states[0] : $states[1];
                        break;

                    default:
                        $this->datiAggiuntiviTab[$rowid]['DAGVAL'] = $_POST[$this->nameForm . '_DATIAGGIUNTIVI'][$dagset][$dagkey];
                        break;
                }

                $praLibCustomClass = new praLibCustomClass($this->praLib);

                if ($praLibCustomClass->checkEseguiEvento('ONCHANGE', $this->datiAggiuntiviTab[$rowid])) {
                    $returnEseguiEvento = $praLibCustomClass->eseguiEvento('ONCHANGE', array(
                        'PRAM_DB' => $this->PRAM_DB,
                        'DatoAggiuntivo' => $this->datiAggiuntiviTab[$rowid],
                        'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                        'Passo' => $this->praLib->GetPropas($this->propak),
                        'Dizionario' => $this->getDizionarioForm(true),
                        'CallerForm' => $this
                            ), true);

                    if ($returnEseguiEvento) {
                        $this->disegnaDatiAggiuntivi();
                    }
                }
                break;
        }
    }

    public function close() {
        parent::close();

        App::$utente->removeKey($this->nameForm . '_gesnum');
        App::$utente->removeKey($this->nameForm . '_propak');
        App::$utente->removeKey($this->nameForm . '_readOnly');
        App::$utente->removeKey($this->nameForm . '_datiAggiuntiviTab');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function setDatiAggiuntivi($datiAggiuntivi) {
        $this->datiAggiuntiviTab = $datiAggiuntivi;
    }

    public function setDatoAggiuntivo($dagKey, $dagVal) {
        foreach ($this->datiAggiuntiviTab as &$datoAggiuntivo) {
            if ($datoAggiuntivo['DAGKEY'] === $dagKey) {
                $datoAggiuntivo['DAGVAL'] = $dagVal;
                break;
            }
        }
    }

    public function setDizionario($dizionario) {
        $this->_plainDictionary = $dizionario;
        $this->getDizionarioForm(true);
    }

    private function getDizionarioForm($reload = false) {
        if (isset($this->_plainDictionary)) {
            if ($reload) {
                foreach ($this->datiAggiuntiviTab as $datoAggiuntivo) {
                    $this->_plainDictionary['PRAAGGIUNTIVI.' . $datoAggiuntivo['DAGKEY']] = $datoAggiuntivo['DAGVAL'];

                    if (($datoAggiuntivo['DAGVAL'] === '' || !isset($datoAggiuntivo['DAGVAL']) ) && $datoAggiuntivo['DAGDEF']) {
                        $this->_plainDictionary['PRAAGGIUNTIVI.' . $datoAggiuntivo['DAGKEY']] = $this->praLibElaborazioneDati->elaboraValoreProdag($datoAggiuntivo, $this->_plainDictionary);
                    }
                }
            }

            return $this->_plainDictionary;
        }

        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($this->gesnum);
        $praLibVar->setChiavePasso($this->propak);

        $this->_plainDictionary = $praLibVar->getVariabiliPraticaSimple()->getAlldataPlain('', '.');

        return $this->getDizionarioForm(true);
    }

    private function disegnaDatiAggiuntivi() {
        $htmlDiv = '';

        $praLibCustomClass = new praLibCustomClass($this->praLib);

        if ($praLibCustomClass->checkEseguiAzione('PRE_RENDER_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
            $praLibCustomClass->eseguiAzione('PRE_RENDER_RACCOLTA', array(
                'PRAM_DB' => $this->PRAM_DB,
                'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                'Passo' => $this->praLib->GetPropas($this->propak),
                'Dizionario' => $this->getDizionarioForm(true),
                'CallerForm' => $this
                    ), true);
        }

        foreach ($this->datiAggiuntiviTab as $datoAggiuntivo) {
            $dictionary = $this->getDizionarioForm();

            $datoAggiuntivo = $this->praLibElaborazioneDati->ctrRicdagRec($datoAggiuntivo, $dictionary);

            if ($praLibCustomClass->checkEseguiDisegnoCampo($datoAggiuntivo)) {
                $retTemplateCampo = $praLibCustomClass->eseguiDisegnoCampo(array(
                    'PRAM_DB' => $this->PRAM_DB,
                    'DatoAggiuntivo' => $datoAggiuntivo,
                    'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                    'Passo' => $this->praLib->GetPropas($this->propak),
                    'Dizionario' => $dictionary,
                    'CallerForm' => $this
                ));

                if ($retTemplateCampo !== false) {
                    $htmlDiv .= $retTemplateCampo;
                    continue;
                }
            }

            if (($datoAggiuntivo['DAGVAL'] === '' || !isset($datoAggiuntivo['DAGVAL']) ) && $datoAggiuntivo['DAGDEF'] !== '') {
                $datoAggiuntivo['DAGVAL'] = $this->praLibElaborazioneDati->elaboraValoreProdag($datoAggiuntivo, $dictionary);

                if (!$datoAggiuntivo['DAGROL']) {
                    $this->datiAggiuntiviTab[$datoAggiuntivo['ROWID']]['DAGVAL'] = $datoAggiuntivo['DAGVAL'];
                }
            }

            if ($datoAggiuntivo['DAGTIC'] === 'Hidden') {
                continue;
            }

            $datoAggiuntivo['DAGLAB'] = $datoAggiuntivo['DAGLAB'] ?: ($datoAggiuntivo['DAGDES'] ?: $datoAggiuntivo['DAGKEY']);

            $htmlDiv .= $this->praLibHtml->getProdagHtmlField($datoAggiuntivo, $this->nameForm, 'DATIAGGIUNTIVI');
        }

        if ($praLibCustomClass->checkEseguiAzione('POST_RENDER_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
            $praLibCustomClass->eseguiAzione('POST_RENDER_RACCOLTA', array(
                'PRAM_DB' => $this->PRAM_DB,
                'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                'Passo' => $this->praLib->GetPropas($this->propak),
                'Dizionario' => $this->getDizionarioForm(true),
                'CallerForm' => $this
                    ), true);
        }

        Out::html($this->nameForm . '_divGestione', $htmlDiv);
    }

    public function openGestione($gesnum, $propak = false) {
        $this->gesnum = $gesnum;
        $this->propak = $propak;

        if (!$this->datiAggiuntiviTab) {
            $this->caricaDatiAggiuntivi();
        }
        $propas_rec = $this->praLib->GetPropas($this->propak);
//        Out::msgInfo("Propas_rec", print_r($propas_rec, true) );
//        Out::msgInfo("Form Dati", "Visualizza Form Dati");
        $this->disegnaDatiAggiuntivi();
    }

    public function openGestione_OLD($gesnum, $propak = false) {
        $this->gesnum = $gesnum;
        $this->propak = $propak;

        if (!$this->datiAggiuntiviTab) {
            $this->caricaDatiAggiuntivi();
        }
        $propas_rec = $this->praLib->GetPropas($this->propak);
        Out::msgInfo("Propas_rec", print_r($propas_rec, true));
//        if ($passoCorrente['PROQST'] == 0) {
        // Raccolta Dati
//            $this->disegnaDomandaMultipla($propas_rec);
        if ($propas_rec['PROQST'] == 1) {
            //Out::msgInfo("Domanda Semplice", "E' un passo domanda semplice");
//            // Domanda Semplice
            $this->disegnaDomandaSemplice($propas_rec);
//        }
//        if ($passoCorrente['PROQST'] == 2) {
//            // Domanda Muiltipla
//            $this->disegnaDomandaMultipla($propas_rec);
        } else {
            Out::msgInfo("Form Dati", "Visualizza Form Dati");
            $this->disegnaDatiAggiuntivi();
        }
    }

    private function caricaDatiAggiuntivi() {
        $this->datiAggiuntiviTab = array();

        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSql());
        foreach ($prodag_tab as $prodag_rec) {
            if ($prodag_rec['PRORDM'] == '1' && !isset($this->datiAggiuntiviTab[$prodag_rec['DAGSET']])) {
                $this->datiAggiuntiviTab[$prodag_rec['DAGSET']] = array(
                    'PROSEQ' => $prodag_rec['PROSEQ'],
                    'NUMERO_RACCOLTA' => ((int) end(explode('_', $prodag_rec['DAGSET']))),
                    'DAGSET' => $prodag_rec['DAGSET']
                );
            }

            $this->datiAggiuntiviTab[$prodag_rec['ROWID']] = $prodag_rec;
        }
    }

    private function sqlAndPropak($tabPassi = false) {
        $whereField = $tabPassi ? 'PROPAK' : 'DAGPAK';
        return $this->propak ? " AND $whereField = '{$this->propak}'" : '';
    }

    private function selectRecord($q) {
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->gesnum}'" . $this->sqlAndPropak();

        if (isset($q['ROWID'])) {
            $sql .= " AND ROWID = '{$q['ROWID']}'";
        }

        if (isset($q['DAGKEY'])) {
            $sql .= " AND DAGKEY = '{$q['DAGKEY']}'";
        }

        return ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
    }

    public function aggiornaDati() {
//        Out::msgInfo("Riga 342", "praCompDatiAggiuntiviForm->aggiornaDati()");
//        Out::msgInfo("$this->nameForm",print_r($this->datiAggiuntiviTab,true));
        if (!$this->validaDati(false)) {
            return false;
        }

        $praLibCustomClass = new praLibCustomClass($this->praLib);

        if ($praLibCustomClass->checkEseguiAzione('PRE_SUBMIT_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
            $praLibCustomClass->eseguiAzione('PRE_SUBMIT_RACCOLTA', array(
                'PRAM_DB' => $this->PRAM_DB,
                'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                'Passo' => $this->praLib->GetPropas($this->propak),
                'Dizionario' => $this->getDizionarioForm(true),
                'CallerForm' => $this
                    ), true);
        }

        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            if (!$prodag_rec['ROWID']) {
                continue;
            }

            if ($prodag_rec['DAGROL']) {
                continue;
            }

            unset($prodag_rec['PROSEQ']);
            unset($prodag_rec['PRORDM']);

            /*
             * Aggiornamento PRODST
             */

            if ($prodag_rec['DAGPAK'] != $prodag_rec['DAGNUM']) {
                $prodst_rec = $this->praLib->GetProdst($prodag_rec['DAGSET']);

                if (!$prodst_rec) {
                    $prodst_rec = array();
                    $prodst_rec['DSTSET'] = $prodag_rec['DAGSET'];
                    $prodst_rec['DSTDES'] = 'Data Set ' . substr($prodag_rec['DAGSET'], -2);

                    $insertInfo = "Oggetto : Inserisco data set {$prodag_rec['DAGSET']} del file " . $prodag_rec['DAGKEY'];
                    if (!$this->insertRecord($this->PRAM_DB, 'PRODST', $prodst_rec, $insertInfo)) {
                        Out::msgStop('Errore', "Inserimento dataset {$prodag_rec['DAGSET']} fallito.");
                        return false;
                    }
                }
            }

            $updateInfo = sprintf('Aggiornamento dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
            if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $updateInfo)) {
                return false;
            }
        }

        if ($praLibCustomClass->checkEseguiAzione('POST_SUBMIT_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
            $praLibCustomClass->eseguiAzione('POST_SUBMIT_RACCOLTA', array(
                'PRAM_DB' => $this->PRAM_DB,
                'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                'Passo' => $this->praLib->GetPropas($this->propak),
                'Dizionario' => $this->getDizionarioForm(true),
                'CallerForm' => $this
                    ), true);
        }

        return true;
    }

    public function validaDati($ricaricaDati = true) {
        if (!$this->validaPasso()) {
            return false;
        }

        if (!$this->validaDatiAggiuntivi()) {
            return false;
        }

        if ($ricaricaDati) {
            $this->disegnaDatiAggiuntivi();
        }

        return true;
    }

    public function validaPasso() {
        if (!$this->propak) {
            return true;
        }

        $propas_rec = $this->praLib->GetPropas($this->propak);
        $riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '{$propas_rec['PROITK']}' ORDER BY SEQUENZA", true);
        $msg = $this->praLibElaborazioneDati->CheckValiditaPasso($riccontrolli_tab, $this->getDizionarioForm());

        if ($msg) {
            Out::msgStop('Errore in validazione passo', $msg);
            return false;
        }

        return true;
    }

    public function validaDatiAggiuntivi() {
        $required = $invalid = array();
        $blocca_esecuzione = false;

        /*
         * Se è un passo integrazione non faccio i controlli
         */
        if ($this->propak) {
            $propas_rec = $this->praLib->GetPropas($this->propak);
            if ($propas_rec['PRORIN']) {
                return true;
            }
        }

        $arrayDatiAggiuntivi = array();
        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            $arrayDatiAggiuntivi[$prodag_rec['DAGKEY']] = $prodag_rec['DAGVAL'];
        }

        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            if (!$prodag_rec['ROWID']) {
                continue;
            }

            $is_required = false;
            $error_class = !$prodag_rec['DAGFIELDERRORACT'] ? 'ita-state-error ui-state-error' : 'ui-state-highlight';

            $dagkey = $prodag_rec['DAGKEY'];
            $dagset = $prodag_rec['DAGSET'];
            $value = $prodag_rec['DAGVAL'];
            $nomeCampo = $prodag_rec['DAGLAB'] ?: ($prodag_rec['DAGDES'] ?: $prodag_rec['DAGKEY']);

            if ($prodag_rec['DAGCTR']) {
                $arrayCtr = unserialize($prodag_rec['DAGCTR']);
                $is_required = $this->praLibElaborazioneDati->ctrCampiRaccoltaDati($arrayCtr, $arrayDatiAggiuntivi);
            }

            if ($is_required) {
                if ($prodag_rec['DAGTIC'] == "CheckBox") {
                    if ($value == 0 || $value == "Off") {
                        $required[] = $nomeCampo;
                        Out::addClass($this->nameForm . "_DATIAGGIUNTIVI[$dagset][$dagkey]", $error_class);

                        if (!$prodag_rec['DAGFIELDERRORACT']) {
                            $blocca_esecuzione = true;
                        }
                    }
                } else {
                    if ($value == '') {
                        $required[] = $nomeCampo;
                        Out::addClass($this->nameForm . "_DATIAGGIUNTIVI[$dagset][$dagkey]", $error_class);

                        if (!$prodag_rec['DAGFIELDERRORACT']) {
                            $blocca_esecuzione = true;
                        }
                    }
                }
            }

            if ($prodag_rec['DAGVCA'] && $value != '') {
                if (!$this->praLibElaborazioneDati->controlli($prodag_rec['DAGVCA'], $value, $nomeCampo, $prodag_rec['DAGREV'])) {
                    $invalid[] = array($nomeCampo, $prodag_rec['DAGVCA']);
                    Out::addClass($this->nameForm . "_DATIAGGIUNTIVI[$dagset][$dagkey]", $error_class);

                    if (!$prodag_rec['DAGFIELDERRORACT']) {
                        $blocca_esecuzione = true;
                    }
                }
            }
        }

        if (count($required) || count($invalid)) {
            $msgErr = '';

            if (count($required)) {
                $msgErr .= '<br /><p><b>I seguenti campi sono obbligatori</b>:</p>';
                foreach ($required as $field) {
                    $msgErr .= "<p><u>$field</u></p>";
                }
            }

            if (count($invalid)) {
                $msgErr .= '<br /><p><b>I seguenti campi non hanno un valore valido</b>:</p>';
                foreach ($invalid as $field) {
                    $msgErr .= "<p><u>{$field[0]} ({$field[1]})</u></p>";
                }
            }


            if ($blocca_esecuzione) {
                Out::msgStop("Errore in validazione dati", "<div style=\"width: 400px;\">$msgErr</div>");
                return false;
            }
        }

        return true;
    }

    private function getSql() {
        $sql = "SELECT PRODAG.*, PROSEQ, PRORDM FROM PRODAG
                LEFT OUTER JOIN PROPAS ON PROPAS.PROPAK = PRODAG.DAGPAK
                WHERE
                    DAGNUM = '{$this->gesnum}' AND
                    DAGTIC NOT IN ('RadioButton')" . $this->sqlAndPropak();

        if ($this->propak === false && $this->getCodiceProcedimento() && $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRADAGVIS') == '1') {
            $sql .= " AND PROSEQ IS NULL";
        }

        $sql .= " ORDER BY DAGSFL";

        return $sql;
    }

    private function getCodiceProcedimento() {
        if (isset($this->codiceProcedimento)) {
            return $this->codiceProcedimento;
        }

        $sql = "SELECT GESPRO FROM PROGES WHERE GESNUM = '{$this->gesnum}'";
        $proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        if (!$proges_rec) {
            $this->codiceProcedimento = false;
            return false;
        }

        $this->codiceProcedimento = $proges_rec['GESPRO'];

        return $proges_rec['GESPRO'];
    }

    private function getCodiceProcedimentoPasso() {
        if (isset($this->codiceProcedimentoPasso)) {
            return $this->codiceProcedimentoPasso;
        }

        if (!$this->propak) {
            $this->codiceProcedimentoPasso = false;
            return false;
        }

        $sql = "SELECT PROPRO FROM PROPAS WHERE PROPAK = '{$this->propak}'";
        $propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        if (!$propas_rec) {
            $this->codiceProcedimentoPasso = false;
            return false;
        }

        $this->codiceProcedimentoPasso = $propas_rec['PROPRO'];
        return $propas_rec['PROPRO'];
    }

    private function getCodicePasso() {
        if (isset($this->codicePasso)) {
            return $this->codicePasso;
        }

        if (!$this->propak) {
            $this->codicePasso = false;
            return false;
        }

        $sql = "SELECT PROITK FROM PROPAS WHERE PROPAK = '{$this->propak}'";
        $propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        if (!$propas_rec) {
            $this->codicePasso = false;
            return false;
        }

        $this->codicePasso = $propas_rec['PROITK'];

        return $this->codicePasso;
    }

    private function getRowidDato($dagset, $dagkey) {
        $rowid = false;

        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            if ($prodag_rec['DAGSET'] == $dagset && $prodag_rec['DAGKEY'] == $dagkey) {
                $rowid = $prodag_rec['ROWID'];
                break;
            }
        }

        return $rowid;
    }

    public function cancellaDati() {
        foreach ($this->datiAggiuntiviTab as $datoAggiuntivo) {
            if (!$datoAggiuntivo['ROWID']) {
                continue;
            }

            if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $datoAggiuntivo['ROWID'], '')) {
                return false;
            }
        }

        return true;
    }

    private function disegnaDomandaSemplice($passoCorrente) {
        //Out::msgInfo("form",$this->divForm);
        Out::html($this->nameForm . '_divGestione', '');

        /* @var $praCompPassoGest praCompPassoGest */
        $praCompPassoGest = itaFormHelper::innerForm('praCompDomandaSemplice', $this->nameForm . '_divGestione');
        $praCompPassoGest->setEvent('openform');
        $praCompPassoGest->setReturnModel($this->nameForm);
        $praCompPassoGest->setReturnEvent('returnFromGestPasso');
        $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
        //$praCompPassoGest->setPropak($this->datiWf->passi[0]['PROPAK']);
        $praCompPassoGest->setReturnId('');
        $praCompPassoGest->parseEvent();

        //$praCompPassoGest->openGestione($passoCorrente['PRONUM'], $passoCorrente['PROPAK']);
        //$this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] = $praCompPassoGest->getNameForm();


        $this->praCompPassoGestFormname['praCompDomandaSemplice'] = $praCompPassoGest->getNameForm();
    }

    private function disegnaDomandaMultipla($passoCorrente) {
        Out::html($this->nameForm . '_divContenitorePasso', '');

        /* @var $praCompPassoGest praCompPassoGest */
        $praCompPassoGest = itaFormHelper::innerForm('praCompDatiAggiuntiviForm', $this->nameForm . '_divContenitorePasso');
        $praCompPassoGest->setEvent('openform');
        $praCompPassoGest->setReturnModel($this->nameForm);
        $praCompPassoGest->setReturnEvent('returnFromGestPasso');
//        $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
//        $praCompPassoGest->setReturnId('');
        $praCompPassoGest->parseEvent();
        $praCompPassoGest->openGestione($passoCorrente['PRONUM'], $passoCorrente['PROPAK']);


        $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] = $praCompPassoGest->getNameForm();
    }

}
