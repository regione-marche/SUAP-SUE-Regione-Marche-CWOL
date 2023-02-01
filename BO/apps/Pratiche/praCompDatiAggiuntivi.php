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

function praCompDatiAggiuntivi() {
    $praCompDatiAggiuntivi = new praCompDatiAggiuntivi();
    $praCompDatiAggiuntivi->parseEvent();
    return;
}

class praCompDatiAggiuntivi extends praCompPassoGest {

    public $praLib;
    public $praLibElaborazioneDati;
    public $praLibHtml;
    public $PRAM_DB;
    public $nameForm = 'praCompDatiAggiuntivi';
    public $gridDatiAggiuntivi = 'praCompDatiAggiuntivi_gridDatiAggiuntivi';
    protected $gesnum;
    protected $propak;
    protected $datiAggiuntiviTab;
    protected $readOnly = false;

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->praLib = new praLib();
        $this->praLibElaborazioneDati = new praLibElaborazioneDati();
        $this->praLibHtml = new praLibHtml();
        $this->praLibHtml->setDefaultFullWidth(true);
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

                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        $this->caricaGrigliaDatiAggiuntivi();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        praRic::praRicPraidc(
                                array(
                                    'nameForm' => $this->nameForm,
                                    'nameFormOrig' => $this->nameFormOrig
                                ), 'returnPraidc'
                        );
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        Out::msgQuestion("Attenzione", "L'operazione non è reversibile, sei sicuro di voler cancellare il dato aggiuntivo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancDato', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancDato', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancDato':
                        $rowid = $_POST[$this->gridDatiAggiuntivi]['gridParam']['selrow'];
                        $prodag_rec = $this->datiAggiuntiviTab[$rowid];

                        $deleteInfo = sprintf('Cancellazione dato aggiuntivo %s fascicolo %s', $this->datiAggiuntiviTab[$rowid]['DAGKEY'], $this->gesnum);
                        if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $rowid, $deleteInfo)) {
                            break;
                        }

                        unset($this->datiAggiuntiviTab[$rowid]);
                        $this->caricaGrigliaDatiAggiuntivi();
                        break;

                    case $this->nameForm . '_duplicaRaccoltaMultipla':
                        $btnDagset = $_POST['btnDagset'];
                        $preDagset = substr($btnDagset, 0, -3);

                        /*
                         * Prendo tutti i dati relativi al passo indipendentemente
                         * dal DAGSET e raggruppandoli per DAGKEY; trovo anche
                         * l'indice $idx rappresentante l'ultimo DAGSET.
                         * Carico dati da DB così da duplicare anche i dati non
                         * presenti in tabella (es. CheckButton)
                         */
                        $idx = 0;
                        $datiAggiuntiviPasso = array();

                        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->gesnum}' AND DAGSET LIKE '{$preDagset}___'");

                        foreach ($prodag_tab as $prodag_rec) {
                            $datiAggiuntiviPasso[$prodag_rec['DAGKEY']] = $prodag_rec;

                            if ((int) substr($prodag_rec['DAGSET'], -2) > $idx) {
                                $idx = (int) substr($prodag_rec['DAGSET'], -2);
                            }
                        }

                        $newDagset = $preDagset . '_' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);

                        foreach ($datiAggiuntiviPasso as $prodag_rec) {
                            unset($prodag_rec['ROWID']);
                            unset($prodag_rec['DAGVAL']);
                            $prodag_rec['DAGSET'] = $newDagset;

                            $insertInfo = sprintf('Aggiunta dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                            if (!$this->insertRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $insertInfo)) {
                                break;
                            }
                        }

                        $this->openGestione($this->gesnum, $this->propak);

                        Out::msgInfo('Duplicazione raccolta', 'Raccolta duplicata con successo');
                        break;

                    case $this->nameForm . '_cancellaRaccoltaMultipla':
                        $btnDagset = $_POST['btnDagset'];

                        Out::msgQuestion("Attenzione", "L'operazione non è reversibile, sei sicuro di voler cancellare la raccolta?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancRaccolta', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaCancRaccolta',
                                'model' => $this->nameForm,
                                'shortCut' => "f5",
                                'metaData' => "extraData: { btnDagset: '$btnDagset' }"
                            )
                        ));
                        break;

                    case $this->nameForm . '_ConfermaCancRaccolta':
                        $btnDagset = $_POST['btnDagset'];

                        /*
                         * Carico dati da DB così da cancellare anche i dati non
                         * presenti in tabella (es. CheckButton)
                         */

                        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '{$this->gesnum}' AND DAGSET = '{$btnDagset}'");

                        foreach ($prodag_tab as $prodag_rec) {
                            $deleteInfo = sprintf('Cancellazione dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                            if (!$this->deleteRecord($this->PRAM_DB, 'PRODAG', $prodag_rec['ROWID'], $deleteInfo)) {
                                break;
                            }
                        }

                        $this->openGestione($this->gesnum, $this->propak);
                        break;
                    case $this->nameForm . '_CaricaDatiDeiPassi':
                        $this->caricaGrigliaDatiAggiuntivi(true);
                        break;
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
                     * Cerco per un record aggiunto e non ancora salvato
                     */
                    foreach ($this->datiAggiuntiviTab as $k => $datoAggiuntivo) {
                        if ($datoAggiuntivo['DAGKEY'] === $dagkey && $datoAggiuntivo['DAGSET'] === $dagset) {
                            $this->datiAggiuntiviTab[$k]['DAGVAL'] = $_POST[$this->nameForm . '_DATIAGGIUNTIVI'][$dagset][$dagkey];
                            break 2;
                        }
                    }

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
                        'Dizionario' => $this->getDizionarioForm(),
                        'CallerForm' => $this
                            ), true);

                    if ($returnEseguiEvento) {
                        $this->caricaGrigliaDatiAggiuntivi();
                    }
                }

                if ($this->checkCampiDipendenti($this->datiAggiuntiviTab[$rowid]['DAGKEY'])) {
                    $this->caricaGrigliaDatiAggiuntivi();
                    Out::setFocusNext($_POST['id']);
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        if ($this->readOnly) {
                            Out::msgInfo('AVVISO', 'Valore non modificabile.');
                            $this->caricaGrigliaDatiAggiuntivi();
                            break;
                        }

                        if ($_POST['value'] != 'undefined') {
                            if (is_numeric($_POST['value'])) {
                                $this->datiAggiuntiviTab[$_POST['rowid']]['DAGSFL'] = $_POST['value'];
                                $this->RiordinaSequenza($this->datiAggiuntiviTab[$_POST['rowid']]['DAGSET']);
                            } else {
                                Out::msgInfo('AVVISO', 'Valore non valido. Sequenza non modificata.');
                            }
                            $this->caricaGrigliaDatiAggiuntivi();
                        }
                        break;
                }
                break;

            case 'returnPraidc':
                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], 'rowid');
                $controlloPresenza = array_search($praidc_rec['IDCKEY'], array_column($this->datiAggiuntiviTab, 'DAGKEY'));
                if ($controlloPresenza !== false) {
                    Out::msgInfo('ATTENZIONE', 'Il campo aggiuntivo ' . $praidc_rec['IDCKEY'] . ' è già presente nei Dati Aggiuntivi.');
                    break;
                }

                $ins_prodag_rec = array(
                    'DAGNUM' => $this->gesnum,
                    'DAGSET' => ($this->propak ?: $this->gesnum) . '_01',
                    'DAGPAK' => $this->propak ?: $this->gesnum,
                    'DAGCOD' => $this->getCodiceProcedimento(),
                    'DAGKEY' => $praidc_rec['IDCKEY'],
                    'DAGDES' => $praidc_rec['IDCDES'],
                    'DAGTIP' => $praidc_rec['IDCTIP'],
                    'DAGCTR' => $praidc_rec['IDCCTR'],
                    'DAGMETA' => $praidc_rec['IDCMETA'],
                    'DAGVAL' => $praidc_rec['IDCDEF']
                );

                $sequenza = $this->cercaSequenza($ins_prodag_rec['DAGSET']);
                $ins_prodag_rec['DAGSFL'] = $sequenza;

                if ($this->propak) {
                    $propas_rec = $this->praLib->GetPropas($this->propak);
                    $ins_prodag_rec['PROSEQ'] = $propas_rec['PROSEQ'];
                }

                $this->datiAggiuntiviTab[] = $ins_prodag_rec;
                $this->caricaGrigliaDatiAggiuntivi();
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

    private function checkCampiDipendenti($dagkey) {
        foreach ($this->datiAggiuntiviTab as $datoAggiuntivo) {
            if (strpos($datoAggiuntivo['DAGEXPROUT'], $dagkey) !== false) {
                return true;
            }
        }

        return false;
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
                    $dictionaryRoot = $datoAggiuntivo['PROSEQ'] ? 'PRAAGGIUNTIVI' : 'PRABASEAGGIUNTIVI';
                    $this->_plainDictionary[$dictionaryRoot . '.' . $datoAggiuntivo['DAGKEY']] = $datoAggiuntivo['DAGVAL'];

                    if (($datoAggiuntivo['DAGVAL'] === '' || !isset($datoAggiuntivo['DAGVAL']) ) && $datoAggiuntivo['DAGDEF']) {
                        $this->_plainDictionary[$dictionaryRoot . '.' . $datoAggiuntivo['DAGKEY']] = $this->praLibElaborazioneDati->elaboraValoreProdag($datoAggiuntivo, $this->_plainDictionary);
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

    private function caricaGrigliaDatiAggiuntivi($caricaDatiPassi = false) {
        TableView::clearGrid($this->gridDatiAggiuntivi);

        if ($caricaDatiPassi == true) {
            $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSql(true));
            $dataTab = $this->filtraRecords($prodag_tab);
        } else {
            $dataTab = $this->filtraRecords($this->datiAggiuntiviTab);
        }

        $ita_grid = new TableView($this->gridDatiAggiuntivi, array('arrayTable' => $dataTab, 'rowIndex' => 'idx'));

        if (!$_POST['sidx'] || $_POST['sidx'] === 'PROVENIENZA_DATO') {
            $_POST['sidx'] = 'PROSEQ';
        }

        if (!$_POST['sord']) {
            $_POST['sord'] = 'asc';
        }

        $ita_grid->setPageNum($_POST['page'] ?: 1);
        $ita_grid->setPageRows($_POST['rows'] ?: 1000);
        $ita_grid->setSortIndex('PROSEQ ' . $_POST['sord'] . ', DAGSET ' . $_POST['sord'] . ', DAGSFL');
        $ita_grid->setSortOrder($_POST['sord']);

        $tableData = $this->elaboraRecords($ita_grid->getDataArray());
        $return = $ita_grid->getDataPageFromArray('json', $tableData);

        return $return;
    }

    public function openGestione($gesnum, $propak = false) {
        $this->gesnum = $gesnum;
        $this->propak = $propak;

        $this->initCombo();

        $this->caricaDatiAggiuntivi();
        TableView::enableEvents($this->gridDatiAggiuntivi);
        $this->caricaGrigliaDatiAggiuntivi();

        if ($this->readOnly || ($this->getCodiceProcedimento() && $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRAFLNODADD') == '1')) {
            Out::hide($this->gridDatiAggiuntivi . '_addGridRow');
        } else {
            Out::show($this->gridDatiAggiuntivi . '_addGridRow');
        }

        if ($this->readOnly || ($this->getCodiceProcedimento() && $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRAFLNODDEL') == '1')) {
            Out::hide($this->gridDatiAggiuntivi . '_delGridRow');
        } else {
            Out::show($this->gridDatiAggiuntivi . '_delGridRow');
        }

        /*
         * Visualizzazione bottone carica dati agg dei passi
         */
        Out::hide($this->nameForm . '_CaricaDatiDeiPassi');
        $paramCaricaDati = $this->praLib->getParamCaricaDatiAggPassi();
        if ($paramCaricaDati == 0) {
            Out::show($this->nameForm . '_CaricaDatiDeiPassi');
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

    private function sqlAndPropub($initCombo = false) {
        if (!$this->propak) {
            $filent_rec = $this->praLib->GetFilent(15);
            if ($filent_rec['FILVAL'] == 1) {
                return $initCombo ? ' AND PROPUB = 0' : ' AND ( PROPUB = 0 OR ( PROPUB IS NULL AND DAGPAK = DAGNUM ) )';
            }
        }

        return '';
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
            if ($prodag_rec['NUMERO_RACCOLTA']) {
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

            /*
             * Controllo se il dato aggiuntivo è di procedimento o di passo.
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

            if ($prodag_rec['ROWID']) {
                $updateInfo = sprintf('Aggiornamento dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $updateInfo)) {
                    return false;
                }
            } else {
                $insertInfo = sprintf('Inserimento dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                if (!$this->insertRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $insertInfo)) {
                    return false;
                }
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
            $this->caricaGrigliaDatiAggiuntivi();
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

        $gestioneDatiAggiuntivi = $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRADAGGES');

        $arrayDatiAggiuntivi = array();
        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            $arrayDatiAggiuntivi[$prodag_rec['DAGKEY']] = $prodag_rec['DAGVAL'];
        }

        foreach ($this->datiAggiuntiviTab as $prodag_rec) {
            if ($prodag_rec['NUMERO_RACCOLTA']) {
                continue;
            }

            if ($prodag_rec['PROSEQ'] && $this->propak === false && $gestioneDatiAggiuntivi == '0') {
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
                    $invalid[] = $nomeCampo;
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
                    $msgErr .= "<p><u>$field</u></p>";
                }
            }

            if ($blocca_esecuzione) {
                Out::msgStop("Errore in validazione dati", "<div style=\"width: 400px;\">$msgErr</div>");
                return false;
            }
        }

        return true;
    }

    private function initCombo() {
        $sql = "SELECT PROSEQ FROM PROPAS WHERE PRONUM = '{$this->gesnum}'" . $this->sqlAndPropak(true); // . $this->sqlAndPropub(true);

        $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        $provenienzaDato = $_POST['PROVENIENZA_DATO'];
        Out::html($this->nameForm . ' #gs_PROVENIENZA_DATO', '');

        if ($this->propak === false) {
            Out::select($this->nameForm . ' #gs_PROVENIENZA_DATO', 1, 'TT', $provenienzaDato == 'TT' ? true : false, 'Tutti');
            Out::select($this->nameForm . ' #gs_PROVENIENZA_DATO', 1, 'PR', $provenienzaDato == 'PR' ? true : false, 'Da pratica');
        }

        if (
                $this->propak !== false ||
                (
                $this->propak === false &&
                $this->getCodiceProcedimento() &&
                $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRADAGVIS') == '0'
                )
        ) {
            if ($this->propak === false) {
                Out::select($this->nameForm . ' #gs_PROVENIENZA_DATO', 1, 'PA', $provenienzaDato == 'PA' ? true : false, 'Da passo');
            }

            foreach ($propas_tab as $propas_rec) {
                $propas_rec = array_values($propas_rec);
                Out::select($this->nameForm . ' #gs_PROVENIENZA_DATO', 1, $propas_rec[0], $provenienzaDato == $propas_rec[0] ? true : false, 'Da passo #' . $propas_rec[0]);
            }
        }

        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSql());

        $dagkeyFilter = $_POST['DAGKEY'];
        Out::html($this->nameForm . ' #gs_DAGKEY', '');
        Out::select($this->nameForm . ' #gs_DAGKEY', 1, '', $dagkeyFilter == '' ? true : false, '');

        foreach ($prodag_tab as $prodag_rec) {
            Out::select($this->nameForm . ' #gs_DAGKEY', 1, $prodag_rec['DAGKEY'], $dagkeyFilter == $prodag_rec['DAGKEY'] ? true : false, $prodag_rec['DAGKEY']);
        }

        $dagdesFilter = $_POST['DAGDES'];
        $renderFilter = $_POST['RENDER'];

        Out::valore($this->nameForm . ' #gs_DAGDES', $dagdesFilter);
        Out::valore($this->nameForm . ' #gs_RENDER', $renderFilter);
    }

    private function getSql($caricaDatiPassi = false) {
        $sql = "SELECT PRODAG.*, PROSEQ, PRORDM FROM PRODAG
                LEFT OUTER JOIN PROPAS ON PROPAS.PROPAK = PRODAG.DAGPAK
                WHERE
                    DAGNUM = '{$this->gesnum}' AND
                    DAGTIC NOT IN ('RadioButton', 'Html') AND
                    (PRODOW IS NULL OR PRODOW <> 1 OR  (PRODOW=1 AND (PROPUB = 0 OR DAGVAL <> '')))
                    " . $this->sqlAndPropak(); // . $this->sqlAndPropub();

        if ($caricaDatiPassi == true) {
            return $sql;
        }
        $paramCaricaDatiAggPassi = $this->praLib->getParamCaricaDatiAggPassi();
        if ($this->propak === false && $paramCaricaDatiAggPassi == 0) {
            $sql .= " AND DAGPAK = DAGNUM";
            return $sql;
        }
        if ($this->propak === false && $this->getCodiceProcedimento() && $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRADAGVIS') == '1') {
            $sql .= " AND DAGPAK = DAGNUM";
        }


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

        return $this->codiceProcedimento;
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

        foreach ($this->datiAggiuntiviTab as $k => $prodag_rec) {
            if ($prodag_rec['DAGSET'] == $dagset && $prodag_rec['DAGKEY'] == $dagkey) {
                $rowid = $k;
                break;
            }
        }

        return $rowid;
    }

    private function filtraRecords($datiAggiuntivi) {
        $returnData = array();

        if (!$_POST['_search']) {
            return $datiAggiuntivi;
        }

        foreach ($datiAggiuntivi as $k => $datoAggiuntivo) {
            $datoAggiuntivo['DAGLAB'] = $datoAggiuntivo['DAGLAB'] ?: ($datoAggiuntivo['DAGDES'] ?: $datoAggiuntivo['DAGKEY']);

            switch ($_POST['PROVENIENZA_DATO']) {
                case 'TT':
                    break;

                case 'PR':
                    if ($datoAggiuntivo['PROSEQ']) {
                        /*
                         * "In PHP the switch statement is considered a looping
                         *  structure for the purposes of continue. continue
                         *  behaves like break (when no arguments are passed).
                         *  If a switch is inside a loop, continue 2 will
                         *  continue with the next iteration of the outer loop."
                         * 
                         * http://php.net/manual/en/control-structures.continue.php
                         */
                        continue 2;
                    }
                    break;

                case 'PA':
                    if (!$datoAggiuntivo['PROSEQ']) {
                        continue 2;
                    }
                    break;

                default:
                    if (!$_POST['PROVENIENZA_DATO']) {
                        break;
                    }

                    $seq = (int) $_POST['PROVENIENZA_DATO'];

                    if ($datoAggiuntivo['PROSEQ'] != $seq) {
                        continue 2;
                    }
                    break;
            }

            if ($_POST['DAGKEY'] && $datoAggiuntivo['DAGKEY'] != $_POST['DAGKEY']) {
                continue;
            }

            if ($_POST['DAGDES'] && strpos(strtolower($datoAggiuntivo['DAGLAB']), strtolower($_POST['DAGDES'])) === false) {
                continue;
            }

            if (
                    $_POST['RENDER'] &&
                    (
                    strpos(strtolower($datoAggiuntivo['DAGVAL']), strtolower($_POST['RENDER'])) === false &&
                    strpos(strtolower($datoAggiuntivo['DAGDEF']), strtolower($_POST['RENDER'])) === false
                    )
            ) {
                continue;
            }

            $returnData[$k] = $datoAggiuntivo;
        }

        return $returnData;
    }

    private function elaboraRecords($datiAggiuntivi) {
        $returnData = array();

        if ($this->getCodiceProcedimento()) {
            $gestioneDatiAggiuntivi = $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRADAGGES');
            $utilizzaNuoviCampiEdit = $this->praLib->getAltriParametriBO($this->getCodiceProcedimento(), 'PRAFLEDITST');
        }

        $lastDagpak = false;
        $dictionaryProc = $this->getDizionarioForm(true);
        $praLibCustomClass = new praLibCustomClass($this->praLib);

        foreach ($datiAggiuntivi as $k => $datoAggiuntivo) {
            if (!$datoAggiuntivo['PROSEQ'] && $datoAggiuntivo['DAGNUM'] != $datoAggiuntivo['DAGPAK']) {
                continue;
            }

            if ($datoAggiuntivo['DAGPAK'] != $lastDagpak) {
                if ($lastDagpak) {
                    if ($praLibCustomClass->checkEseguiAzione('POST_RENDER_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
                        $praLibCustomClass->eseguiAzione('POST_RENDER_RACCOLTA', array(
                            'PRAM_DB' => $this->PRAM_DB,
                            'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                            'Passo' => $this->praLib->GetPropas($lastDagpak),
                            'Dizionario' => $this->getDizionarioForm(true),
                            'CallerForm' => $this
                                ), true);
                    }
                }

                if ($praLibCustomClass->checkEseguiAzione('PRE_RENDER_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
                    $praLibCustomClass->eseguiAzione('PRE_RENDER_RACCOLTA', array(
                        'PRAM_DB' => $this->PRAM_DB,
                        'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                        'Passo' => $this->praLib->GetPropas($datoAggiuntivo['DAGPAK']),
                        'Dizionario' => $this->getDizionarioForm(true),
                        'CallerForm' => $this
                            ), true);
                }

                $lastDagpak = $datoAggiuntivo['DAGPAK'];

                $dictionaryProc = $this->getDizionarioForm();
            }

            $datoAggiuntivo = $this->praLibElaborazioneDati->ctrRicdagRec($datoAggiuntivo, $dictionaryProc);

            $retTemplateCampo = false;
            if ($praLibCustomClass->checkEseguiDisegnoCampo($datoAggiuntivo)) {
                $retTemplateCampo = $praLibCustomClass->eseguiDisegnoCampo(array(
                    'PRAM_DB' => $this->PRAM_DB,
                    'DatoAggiuntivo' => $datoAggiuntivo,
                    'DatiAggiuntivi' => $this->datiAggiuntiviTab,
                    'Passo' => $this->praLib->GetPropas($this->propak),
                    'Dizionario' => $dictionaryProc,
                    'CallerForm' => $this
                ));
            }

            if (($datoAggiuntivo['DAGVAL'] === '' || !isset($datoAggiuntivo['DAGVAL']) ) && $datoAggiuntivo['DAGDEF'] !== '') {
                $datoAggiuntivo['DAGVAL'] = $this->praLibElaborazioneDati->elaboraValoreProdag($datoAggiuntivo, $dictionaryProc);

                if (!$datoAggiuntivo['DAGROL'] && isset($this->datiAggiuntiviTab[$datoAggiuntivo['ROWID']])) {
                    $this->datiAggiuntiviTab[$datoAggiuntivo['ROWID']]['DAGVAL'] = $datoAggiuntivo['DAGVAL'];
                }
            }

            if ($datoAggiuntivo['DAGTIC'] === 'Hidden') {
                continue;
            }

            if ($datoAggiuntivo['NUMERO_RACCOLTA']) {
                /*
                 * Header per raccolta multipla.
                 */
                $datoAggiuntivo['PROVENIENZA_DATO'] = '<p style="color: blue;"><i class="ui-icon ui-icon-pin-s"></i> Passo #' . $datoAggiuntivo['PROSEQ'] . ' <small>raccolta ' . $datoAggiuntivo['NUMERO_RACCOLTA'] . '</small></p>';

                $btnStyle = 'padding: 2px 4px; display: inline-block; cursor: pointer;';

                $datoAggiuntivo['RENDER'] = '<div class="ita-html" style="text-align: right; padding: .2rem 0;">';

                if (!$this->readOnly) {
                    if ((int) substr($datoAggiuntivo['DAGSET'], -2) == 1) {
                        $btnIdDup = $this->nameForm . '_duplicaRaccoltaMultipla' . $datoAggiuntivo['DAGSET'];
                        $btnExtraDup = "{ id: '{$this->nameForm}_duplicaRaccoltaMultipla', btnDagset: '{$datoAggiuntivo['DAGSET']}' }";
                        $datoAggiuntivo['RENDER'] .= '<button id="' . $btnIdDup . '" type="button" class="ita-button ita-element-animate { extraData: ' . $btnExtraDup . ', iconLeft: \'ui-icon ui-icon-plus\' } ui-corner-all ui-state-default" style="' . $btnStyle . '" value="Duplica Raccolta"></button>';
                    } else {
                        $btnIdDel = $this->nameForm . '_cancellaRaccoltaMultipla' . $datoAggiuntivo['DAGSET'];
                        $btnExtraDel = "{ id: '{$this->nameForm}_cancellaRaccoltaMultipla', btnDagset: '{$datoAggiuntivo['DAGSET']}' }";
                        $datoAggiuntivo['RENDER'] .= '<button id="' . $btnIdDel . '" type="button" class="ita-button ita-element-animate { extraData: ' . $btnExtraDel . ', iconLeft: \'ui-icon ui-icon-trash\' } ui-corner-all ui-state-default" style="' . $btnStyle . '" value="Cancella Raccolta"></button>';
                    }
                }

                $datoAggiuntivo['RENDER'] .= '</div>';

                $returnData[] = $datoAggiuntivo;
                continue;
            }

            if ($datoAggiuntivo['DAGNOT']) {
                $datoAggiuntivo['NOTE'] = '<div class="ita-html"><span class="ui-icon ui-icon-circle-info ita-tooltip" title="' . htmlentities($datoAggiuntivo['DAGNOT']) . '"></span></div>';
            }

            $datoAggiuntivo['DAGLAB'] = $datoAggiuntivo['DAGLAB'] ?: ($datoAggiuntivo['DAGDES'] ?: $datoAggiuntivo['DAGKEY']);
            $datoAggiuntivo['DAGDES'] = $this->praLibHtml->getProdagHtmlLabel($datoAggiuntivo, $this->nameForm, 'DATIAGGIUNTIVI');

            if ($datoAggiuntivo['PROSEQ']) {
                if (isset($this->datiAggiuntiviTab[$datoAggiuntivo['DAGSET']])) {
                    $datoAggiuntivo['PROVENIENZA_DATO'] = "\t<small>Raccolta #" . $this->datiAggiuntiviTab[$datoAggiuntivo['DAGSET']]['NUMERO_RACCOLTA'] . '</small>';
                } else {
                    $datoAggiuntivo['PROVENIENZA_DATO'] = '<p style="color: blue;"><i class="ui-icon ui-icon-pin-s"></i> Passo #' . $datoAggiuntivo['PROSEQ'] . '</p>';
                }

                if ($this->propak === false && $gestioneDatiAggiuntivi == '0') {
                    $datoAggiuntivo['RENDER'] = $datoAggiuntivo['DAGVAL'];

                    $returnData[] = $datoAggiuntivo;
                    continue;
                }
            } else {
                $datoAggiuntivo['PROVENIENZA_DATO'] = '<i class="ui-icon ui-icon-globe-b"></i> Pratica';
            }

            if ($utilizzaNuoviCampiEdit == '0') {
                $datoAggiuntivo['DAGTIC'] = 'Text';
            }

            if ($this->readOnly) {
                $datoAggiuntivo['RENDER'] = $datoAggiuntivo['DAGVAL'];

                $returnData[] = $datoAggiuntivo;
                continue;
            }

            $datoAggiuntivo['RENDER'] = '<div class="ita-html ui-state-default" style="padding: .3rem .5rem;">' . $this->praLibHtml->getProdagHtmlInput($datoAggiuntivo, $this->nameForm, 'DATIAGGIUNTIVI') . '</div>';

            if ($retTemplateCampo !== false) {
                $datoAggiuntivo['RENDER'] = '<div class="ita-html ui-state-default" style="padding: .3rem .5rem;">' . $retTemplateCampo . '</div>';
            }

            $returnData[] = $datoAggiuntivo;
        }

        return $returnData;
    }

    public function cercaSequenza($dagset) {
        $sequenza = 0;
        foreach ($this->datiAggiuntiviTab as $datoAggiuntivo) {
            if ($datoAggiuntivo['DAGSET'] === $dagset && $datoAggiuntivo['DAGSFL'] > $sequenza) {
                $sequenza = $datoAggiuntivo['DAGSFL'];
            }
        }
        return $sequenza + 10;
    }

    public function RiordinaSequenza($dagset) {
        $datiAggiuntiviSortati = $this->array_msort($this->datiAggiuntiviTab, array('DAGSFL' => SORT_ASC));
        $new_seq = 0;
        foreach ($datiAggiuntiviSortati as $idx => $datiAggiuntivo) {
            if ($datiAggiuntivo['DAGSET'] !== $dagset) {
                continue;
            }

            $new_seq += 10;
            $this->datiAggiuntiviTab[$idx]['DAGSFL'] = $new_seq;
        }
    }

    public function array_msort($array, $cols) {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_' . $k] = strtolower($row[$col]);
            }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
        }
        $eval = substr($eval, 0, -1) . ');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k, 1);
                if (!isset($ret[$k]))
                    $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }

}
