<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbMenuTesti.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

function cwbBgeTesti() {
    $cwbBgeTesti = new cwbBgeTesti();
    $cwbBgeTesti->parseEvent();
    return;
}

class cwbBgeTesti extends cwbBpaGenTab {

    protected $libDB_BOR;
    protected $returnData;
    private $visualizzaMenuTesti;
    private $datiCaricamentoListaGuida;

    protected function initVars() {
        $this->GRID_NAME = 'gridBgeTesti';
        $this->GRID_NAME_TESTIL = 'gridBgeTestil';
        $this->GRID_NAME_FILTRI = 'gridBgeTesfil';
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BGE();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->libDB_DTA = new cwdLibDB_DTA();
        $this->skipAuth = true;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function postConstruct() {
        parent::postConstruct();
    }

    protected function preConstruct() {
        parent::preConstruct();
        $this->visualizzaMenuTesti = cwbParGen::getFormSessionVar($this->nameForm, '_visualizzaMenuTesti');
        $this->datiCaricamentoListaGuida = cwbParGen::getFormSessionVar($this->nameForm, '_datiCaricamentoListaGuida');
    }

    protected function preDestruct() {
        parent::preDestruct();
        cwbParGen::setFormSessionVar($this->nameForm, '_visualizzaMenuTesti', $this->visualizzaMenuTesti);
        cwbParGen::setFormSessionVar($this->nameForm, '_datiCaricamentoListaGuida', $this->datiCaricamentoListaGuida);
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['CODTESTO_formatted'] != '') {
            $this->gridFilters['CODTESTO'] = $this->formData['CODTESTO_formatted'];
        }
        if ($_POST['CODMODULO'] != '') {
            $this->gridFilters['CODMODULO'] = $this->formData['CODMODULO'];
        }
        if ($_POST['DESTESTO'] != '') {
            $this->gridFilters['DESTESTO'] = $this->formData['DESTESTO'];
        }
        if ($_POST['TIPOTESTO'] != '') {
            $testit = array();
            $testit = $this->libDB->leggiBgeTestit(array('TESTIT' => $_POST['TIPOTESTO']));
            if (intval(count($testit)) === 1) {
                $this->gridFilters['PROGTESTIT'] = $testit[0]['PROGTESTIT'];
            } elseif (intval(count($testit)) > 1) {
                $this->gridFilters['PROGTESTIT_or'] = $testit;
            }
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODMODULO]':
                        $this->decodMod($this->formData[$this->nameForm . '_CODMODULO'], ($this->nameForm . '_CODMODULO'), null, ($this->nameForm . '_DESMODULO_decod'));
                        break;
                    case $this->nameForm . '_DESMODULO_decod':
                        $this->decodMod(null, ($this->nameForm . '_CODMODULO'), $this->formData[$this->nameForm . '_DESMODULO_decod'], ($this->nameForm . '_DESMODULO_decod'));
                        break;
                    case $this->nameForm . '_BGE_TESTI[CODMODULO]':
                        $this->decodMod($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODMODULO'], ($this->nameForm . '_BGE_TESTI[CODMODULO]'), null, ($this->nameForm . '_DESMODULO'));
                        break;
                    case $this->nameForm . '_DESMODULO':
                        $this->decodMod(null, ($this->nameForm . '_BGE_TESTI[CODMODULO]'), $this->formData[$this->nameForm . '_DESMODULO'], ($this->nameForm . '_DESMODULO'));
                        break;
                    case $this->nameForm . '_BGE_TESTI[FORMATORTF]':
                        $this->soloTesto($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['FORMATORTF']);
                        break;
                    case $this->nameForm . '_BGE_TESTI[CODMOD_AUT]':
                        $this->initComboProgAutorizz($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODMOD_AUT']);
                        break;
                    case $this->nameForm . '_IDADASSM':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_IDADASSM'], $this->nameForm . '_IDADASSM');
                        break;
                    case $this->nameForm . '_BGE_TESTI[NUMRIGHE]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMRIGHE'], $this->nameForm . '_BGE_TESTI[NUMRIGHE]');
                        break;
                    case $this->nameForm . '_BGE_TESTI[NUMCOLONNE]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['NUMCOLONNE'], $this->nameForm . '_BGE_TESTI[NUMCOLONNE]');
                        break;
                    case $this->nameForm . '_BGE_TESTI[COPIE]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COPIE'], $this->nameForm . '_BGE_TESTI[COPIE]');
                        break;
                    case $this->nameForm . '_BGE_TESTI[SCALA_PAG]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['SCALA_PAG'], $this->nameForm . '_BGE_TESTI[SCALA_PAG]');
                        break;
                    case $this->nameForm . '_BGE_TESTI[TIPDIRITTI]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPDIRITTI'], $this->nameForm . '_BGE_TESTI[TIPDIRITTI]')) {
                            $this->decodDiritti($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['TIPDIRITTI'], ($this->nameForm . '_BGE_TESTI[TIPDIRITTI]'), null, ($this->nameForm . '_DESTIPODIRITTI'));
                        } else {
                            Out::valore($this->nameForm . '_DESTIPODIRITTI', '');
                        }
                        break;
                    case $this->nameForm . '_DESTIPODIRITTI':
                        $this->decodDiritti(null, ($this->nameForm . '_BGE_TESTI[TIPDIRITTI]'), $this->formData[$this->nameForm . '_DESTIPODIRITTI'], ($this->nameForm . '_DESTIPODIRITTI'));
                        break;
                    case $this->nameForm . '_BGE_TESTI[PROGTESTIT]':
                        if (cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGTESTIT'], $this->nameForm . '_BGE_TESTI[PROGTESTIT]')) {
                            $this->decodTesti($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['PROGTESTIT'], ($this->nameForm . '_BGE_TESTI[PROGTESTIT]'), null, ($this->nameForm . '_DESTIPOTESTI'));
                        } else {
                            Out::valore($this->nameForm . '_DESTIPOTESTI', '');
                        }
                        break;
                    case $this->nameForm . '_DESTIPOTESTI':
                        $this->decodTesti(null, ($this->nameForm . '_BGE_TESTI[PROGTESTIT]'), $this->formData[$this->nameForm . '_DESTIPOTESTI'], ($this->nameForm . '_DESTIPOTESTI'), false);
                        break;
                    case $this->nameForm . '_BGE_TESTI[COD_NR_D]':
                        if ($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D']) {
                            $this->decodNumeratori($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), null, ($this->nameForm . '_DES_COD_NR'));
                        } else {
                            Out::valore($this->nameForm . '_DES_COD_NR', '');
                        }
                        break;
                    case $this->nameForm . '_DES_COD_NR':
                        $this->decodNumeratori(null, ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), $this->formData[$this->nameForm . '_DES_COD_NR'], ($this->nameForm . '_DES_COD_NR'));
                        break;
                    case $this->nameForm . '_CODTESTO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_CODTESTO'], $this->nameForm . '_CODTESTO');
                        break;
                    case $this->nameForm . '_TESTOPROV':
                        $this->initComboRicercaTipoligie($_POST[$this->nameForm . '_TESTOPROV']);
                        $this->GestisciLabelsRicerca($_POST[$this->nameForm . '_TESTOPROV']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    //Modulo ricerca
                    case $this->nameForm . '_DESMODULO_decod_butt':
                        $this->decodMod(null, ($this->nameForm . '_CODMODULO'), $this->formData[$this->nameForm . '_DESMODULO_decod'], ($this->nameForm . '_DESMODULO_decod'), true);
                        break;
                    //Modulo dettaglio  
                    case $this->nameForm . '_DESMODULO_butt':
                        $this->decodMod(null, ($this->nameForm . '_BGE_TESTI[CODMODULO]'), $this->formData[$this->nameForm . '_DESMODULO'], ($this->nameForm . 'DESMODULO'), true);
                        break;
                    case $this->nameForm . '_DESTIPODIRITTI_butt':
                        $this->decodDiritti($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['TIPDIRITTI'], ($this->nameForm . '_BGE_TESTI[TIPDIRITTI]'), $this->formData[$this->nameForm . '_DESTIPODIRITTI'], ($this->nameForm . '_DESTIPODIRITTI'), true);
                        break;
                    case $this->nameForm . '_DESTIPOTESTI_butt':
                        $this->decodTesti($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['TIPOTESTI'], ($this->nameForm . '_BGE_TESTI[TIPOTESTI]'), $this->formData[$this->nameForm . '_DESTIPOTESTI'], ($this->nameForm . '_DESTIPOTESTI'), true);
                        break;
                    case $this->nameForm . '_paneCorpo':
                        $this->caricaTesto($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['CODTESTO']);
                        break;
                    case $this->nameForm . '_DES_COD_NR_butt':
                        $this->decodNumeratori($this->formData[$this->nameForm . '_' . $this->TABLE_NAME]['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), $this->formData[$this->nameForm . '_DES_COD_NR'], ($this->nameForm . '_DES_COD_NR'), true);
                        break;
                }
                break;
            case 'returnFromDtaDiritt':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESTIPODIRITTI':
                    case $this->nameForm . '_DESTIPODIRITTI_butt':
                        Out::valore($this->nameForm . '_BGE_TESTI[TIPDIRITTI]', $this->formData['returnData']['TIPDIRITTI']);
                        Out::valore($this->nameForm . '_DESTIPODIRITTI', $this->formData['returnData']['DESTIPDIRI']);
                        break;
                }
                break;
            case 'returnFromBgeTestit':
                switch ($this->elementId) {
                    //DETTAGLIO TESTI 
                    case $this->nameForm . '_BGE_TESTI[PROGTESTIT]':
                    case $this->nameForm . '_DESTIPOTESTI':
                    case $this->nameForm . '_DESTIPOTESTI_butt':
                        Out::valore($this->nameForm . '_BGE_TESTI[PROGTESTIT]', $this->formData['returnData']['PROGTESTIT']);
                        Out::valore($this->nameForm . '_DESTIPOTESTI', $this->formData['returnData']['TESTIT']);
                        break;
                }
                break;
            case 'returnFromBorModuli':
                switch ($this->elementId) {
                    //Ricerca modulo
                    case $this->nameForm . '_CODMODULO':
                    case $this->nameForm . '_DESMODULO_decod':
                    case $this->nameForm . '_DESMODULO_decod_butt':
                        Out::valore($this->nameForm . '_CODMODULO', $this->formData['returnData']['CODMODULO']);
                        Out::valore($this->nameForm . '_DESMODULO_decod', $this->formData['returnData']['DESMODULO']);
                        break;
                    //dettaglio modulo
                    case $this->nameForm . '_BGE_TESTI[CODMODULO]':
                    case $this->nameForm . '_DESMODULO':
                    case $this->nameForm . '_DESMODULO_butt':
                        Out::valore($this->nameForm . '_BGE_TESTI[CODMODULO]', $this->formData['returnData']['CODMODULO']);
                        Out::valore($this->nameForm . '_DESMODULO', $this->formData['returnData']['DESMODULO']);
                        break;
                }
                break;
            case 'returnFromBtaNrd':
                switch ($this->elementId) {
                    case $this->nameForm . '_BGE_TESTI[COD_NR_D]':
                    case $this->nameForm . '_DES_COD_NR':
                    case $this->nameForm . '_DES_COD_NR_butt':
                        Out::valore($this->nameForm . '_BGE_TESTI[COD_NR_D]', $this->formData['returnData']['COD_NR_D']);
                        Out::valore($this->nameForm . '_DES_COD_NR', $this->formData['returnData']['DES_NR_D']);
                        break;
                }
                break;
            case 'onReturnMenuTestiUpload':
                Out::msgInfo('onReturnMenuTestiUpload', print_r($this->returnData, true));
                break;
        }
    }

    protected function setVisRisultato() {
        parent::setVisRisultato();

        //TODO: Abilitare il controllo appeena la finestra è chiamata da fuori
        //if ($this->getVisualizzaMenuTesti()) {
        //} else {
        //    Out::hide($this->nameForm . '_Elabora');
        //}        
    }

    protected function postApriForm() {
        //nascondo checkbox
        Out::hide($this->nameForm . '_divValido');
        $this->initComboOrientamentoFoglio();
        $this->initComboFormatoFoglio();
        $this->initComboFormatoTestoGestione();
        $this->initComboFormatoTestoRicerca();
        $this->initComboProvTesto();
        $this->initComboTutteTipologie();
        $this->initComboNrPagina();
        $this->initComboCpi_txt();
        $this->initComboLpi_txt();
        $this->initComboAutorizzazioni();
        Out::attributo($this->nameForm . "_F_INCLUDE_NO", "checked", "0", "checked");
        Out::attributo($this->nameForm . "_F_TABULATO_TUTTI", "checked", "0", "checked");
        Out::attributo($this->nameForm . "_FLAG_DIS", "checked", "0", "checked");
        Out::setFocus("", $this->nameForm . '_DESTESTO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESTESTO');
    }

    protected function postNuovo() {
        Out::setFocus("", $this->nameForm . '_BGE_TESTI[DESTESTO]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGE_TESTI[DESTESTO]');
    }

    protected function postDettaglio($index) {
        $this->decodMod($this->CURRENT_RECORD['CODMODULO'], ($this->nameForm . '_BGE_TESTI[CODMODULO]'), ($this->nameForm . '_DESMODULO'));
        $this->decodDiritti($this->CURRENT_RECORD['TIPDIRITTI'], ($this->nameForm . '_BGE_TESTI[TIPDIRITTI]'), ($this->nameForm . '_DESTIPODIRITTI'));
        $this->decodNumeratori($this->CURRENT_RECORD['COD_NR_D'], ($this->nameForm . '_BGE_TESTI[COD_NR_D]'), ($this->nameForm . '_DES_COD_NR'));
        $this->soloTesto($this->CURRENT_RECORD['FORMATORTF']);
        $this->initComboProgAutorizz($this->CURRENT_RECORD['CODMOD_AUT']);
        $this->caricaListaCiclo($this->CURRENT_RECORD['CODTESTO']);
        $this->caricaListaFiltri($this->CURRENT_RECORD['CODTESTO']);
        $testoprov = $this->provenienzaTesto($this->CURRENT_RECORD['TESTOPROV']);
        $this->hideProvtesto($this->CURRENT_RECORD['TESTOPROV']);
        Out::valore($this->nameForm . '_BGE_TESTI[TESTOPROV]', $testoprov);
        Out::valore($this->nameForm . '_BGE_TESTI[PROGAUT]', $this->CURRENT_RECORD['PROGAUT']);
        $this->GestisciLabelsGestione($this->CURRENT_RECORD['TESTOPROV']);
        Out::setFocus('', $this->nameForm . '_BGE_TESTI[DESTESTO]');
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        if ($this->flagSearch == true) {
            //Eventuale personalizzazione dei filtri esterni
        } else {
            $filtri['CODTESTO'] = trim($this->formData[$this->nameForm . '_CODTESTO']);
            $filtri['DESTESTO'] = trim($this->formData[$this->nameForm . '_DESTESTO']);
            $filtri['FLAG_DIS'] = trim($this->formData[$this->nameForm . '_FLAG_DIS']);
            $filtri['TESTOPROV'] = trim($this->formData[$this->nameForm . '_TESTOPROV']);
            $filtri['PROGTESTIT'] = trim($this->formData[$this->nameForm . '_PROGTESTIT']);
            $filtri['FORMATORTF'] = trim($this->formData[$this->nameForm . '_FORMATORTF']);
            $filtri['F_V_TES_1'] = trim($this->formData[$this->nameForm . '_F_V_TES_1']);
            $filtri['F_V_TES_2'] = trim($this->formData[$this->nameForm . '_F_V_TES_2']);
            $filtri['F_V_TES_3'] = trim($this->formData[$this->nameForm . '_F_V_TES_3']);
            $filtri['F_V_TES_4'] = trim($this->formData[$this->nameForm . '_F_V_TES_4']);
            $filtri['F_V_TES_5'] = trim($this->formData[$this->nameForm . '_F_V_TES_5']);
            $filtri['F_V_TES_6'] = trim($this->formData[$this->nameForm . '_F_V_TES_6']);
            $filtri['F_V_TES_7'] = trim($this->formData[$this->nameForm . '_F_V_TES_7']);
            $filtri['F_V_TES_8'] = trim($this->formData[$this->nameForm . '_F_V_TES_8']);
            $filtri['F_V_TES_9'] = trim($this->formData[$this->nameForm . '_F_V_TES_9']);
        }
        $this->compilaFiltri($filtri);
        if (!cwbLibCheckInput::IsNBZ($filtri['ORDER_BY'])) {
            $orderBy = $filtri['ORDER_BY'];
        } else {
            $orderBy = 'DESTESTO';
        }

        $this->SQL = $this->libDB->getSqlLeggiBgeTesti($filtri, true, $sqlParams, $orderBy);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeTestiChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {

        // salvo path icone in variabile
        $path_ico_word = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_120032-16x16.png'; // icona quando Formato testo WORD
        $path_ico_office = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_120033-16x16.png'; // icona quando Formato testo Open Office
        $path_ico_txcontrol = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_120034-16x16.png'; // icona quando Formato testo Tx-control.
        $path_ico_solotesto = ITA_BASE_PATH . '/apps/CityBase/resources/omnisicons/UP_8685-16x16.png'; // icona quando Formato solo testo.
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODTESTO_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODTESTO']);
            switch ($Result_tab[$key]['FORMATORTF']) {
                case 0: // Tx-control
                    $Result_tab[$key]['FORMATORTF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_txcontrol);
                    break;
                case 1: // Word      
                    $Result_tab[$key]['FORMATORTF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_word);
                    break;
                case 2: // Open Office
                    $Result_tab[$key]['FORMATORTF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_office);
                    break;
                case 3: // Solo testo
                    $Result_tab[$key]['FORMATORTF'] = cwbLibHtml::formatDataGridIcon('', $path_ico_solotesto);
                    break;
            }

            $Result_tab[$key]['FORMATORTF'] === 0 ? cwbLibHtml::formatDataGridIcon('', $path_ico_txcontrol) : $cellcontent = ' ';
            $Result_tab[$key]['TESTOPROV'] = $this->provenienzaTesto($Result_tab[$key]['TESTOPROV']);
            $testit = array();
            $testit = $this->libDB->leggiBgeTestit(array("PROGTESTIT" => $Result_tab[$key]['PROGTESTIT']), false);
            $Result_tab[$key]['TIPOTESTO'] = $testit[0]['TESTIT'];
        }
        return $Result_tab;
    }

    private function initComboFormatoTestoRicerca() {
        // Formato Testo divRicerca
        Out::select($this->nameForm . '_FORMATORTF', 1, " ", 1, " ");
        Out::select($this->nameForm . '_FORMATORTF', 1, "0", 0, "0 - TX-Control");
        Out::select($this->nameForm . '_FORMATORTF', 1, "1", 0, "1 - Microsoft Word");
        Out::select($this->nameForm . '_FORMATORTF', 1, "2", 0, "2 - Open Office");
        Out::select($this->nameForm . '_FORMATORTF', 1, "3", 0, "3 - Solo testo");
    }

    private function initComboFormatoTestoGestione() {
        // Formato Testo divGestione
        Out::select($this->nameForm . '_BGE_TESTI[FORMATORTF]', 1, " ", 1, " ");
        Out::select($this->nameForm . '_BGE_TESTI[FORMATORTF]', 1, "0", 0, "0 - TX-Control");
        Out::select($this->nameForm . '_BGE_TESTI[FORMATORTF]', 1, "1", 0, "1 - Microsoft Word");
        Out::select($this->nameForm . '_BGE_TESTI[FORMATORTF]', 1, "2", 0, "2 - Open Office");
        Out::select($this->nameForm . '_BGE_TESTI[FORMATORTF]', 1, "3", 0, "3 - Solo testo");
    }

    private function initComboProvTesto() {
        // Provenienza Testo
        Out::select($this->nameForm . '_TESTOPROV', 1, "0", 1, " ");
        Out::select($this->nameForm . '_TESTOPROV', 1, "1", 0, "Demografici");
        Out::select($this->nameForm . '_TESTOPROV', 1, "2", 0, "Tributi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "3", 0, "Ici");
        Out::select($this->nameForm . '_TESTOPROV', 1, "4", 0, "Servizi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "5", 0, "Finanziaria");
        Out::select($this->nameForm . '_TESTOPROV', 1, "6", 0, "Unità ecografiche");
        Out::select($this->nameForm . '_TESTOPROV', 1, "7", 0, "Atti Amministrativi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "8", 0, "Acquedotto");
        Out::select($this->nameForm . '_TESTOPROV', 1, "9", 0, "Sociali");
        Out::select($this->nameForm . '_TESTOPROV', 1, "10", 0, "Controllo di gestione");
        Out::select($this->nameForm . '_TESTOPROV', 1, "11", 0, "Servizi cimiteriali");
        Out::select($this->nameForm . '_TESTOPROV', 1, "12", 0, "Avviso Unico Pagamento");
        Out::select($this->nameForm . '_TESTOPROV', 1, "13", 0, "Recupero crediti");
        Out::select($this->nameForm . '_TESTOPROV', 1, "14", 0, "Anagrafe");
        Out::select($this->nameForm . '_TESTOPROV', 1, "15", 0, "Elettorale");
        Out::select($this->nameForm . '_TESTOPROV', 1, "16", 0, "Stato Civile");
        Out::select($this->nameForm . '_TESTOPROV', 1, "17", 0, "Notifiche");
        Out::select($this->nameForm . '_TESTOPROV', 1, "18", 0, "Portale Web");
        Out::select($this->nameForm . '_TESTOPROV', 1, "19", 0, "Protocollo");
    }

    private function initComboAutorizzazioni() {

        // Azzera combo
        Out::html($this->nameForm . '_BGE_TESTI[CODMOD_AUT]', '');

        // Aggiungi voce 'VUOTA'
        Out::select($this->nameForm . '_BGE_TESTI[CODMOD_AUT]', 1, '', 1, " ");

        $autorizzazioni = $this->libDB_BOR->leggiBorDesaut();
        cwbParGen::setFormSessionVar($this->nameForm, 'autorizzazioni', $autorizzazioni);
        // Popola combo in funzione dei dati caricati da db        
        foreach ($autorizzazioni as $autorizzazione) {
            if ($autorizzazione['CODMODULO'] === $autorizzazione_old) { // se sono uguali non aggiungo ma passo avanti per evitare di creare duplicati.
                continue;
            } else {
                $autorizzazione_old = $autorizzazione['CODMODULO'];
                Out::select($this->nameForm . '_BGE_TESTI[CODMOD_AUT]', 1, $autorizzazione['CODMODULO'], 0, trim($autorizzazione['CODMODULO']));
            }
        }
    }

    private function initComboProgAutorizz($codice) {
        // Azzera combo
        Out::html($this->nameForm . '_BGE_TESTI[PROGAUT]', '');
        $autorizzazioni = cwbParGen::getFormSessionVar($this->nameForm, 'autorizzazioni');
        foreach ($autorizzazioni as $key => $autorizzazione) {
            if ($autorizzazione['CODMODULO'] === $codice) {
                Out::select($this->nameForm . '_BGE_TESTI[PROGAUT]', 1, $autorizzazione['PROGAUT'], 0, $autorizzazione['PROGAUT'] . "-" . trim($autorizzazione['DESCRI']));
            }
        }
    }

    private function initComboTutteTipologie() {

        // Azzera combo
        Out::html($this->nameForm . '_PROGTETSTIT', '');

        // Aggiungi voce 'VUOTA'
        Out::select($this->nameForm . '_PROGTESTIT', 1, '', 1, " ");

        // Carica lista aree
        $tipologie = $this->libDB->leggiBgeTestit(array());

        // Popola combo in funzione dei dati caricati da db
        foreach ($tipologie as $tipologia) {
            Out::select($this->nameForm . '_PROGTESTIT', 1, $tipologia['PROGTESTIT'], 0, trim($tipologia['TESTIT']));
        }
    }

    private function initComboRicercaTipoligie($provtesto) {

        // Azzera combo
        Out::html($this->nameForm . '_PROGTESTIT', '');

        // Aggiungi voce 'VUOTA'
        Out::select($this->nameForm . '_PROGTESTIT', 1, '', 1, " ");

        // Se provenienza testo corrente non valorizzata, esce
        if ($provtesto == '') {
            return;
        }

        // Carica lista moduli
        $filtri = array();
        $filtri['TESTOPROV'] = $provtesto;
        $tipologie = $this->libDB->leggiBgeTestit($filtri);

        // Popola combo in funzione dei dati caricati da db        
        foreach ($tipologie as $tipologia) {
            Out::select($this->nameForm . '_PROGTESTIT', 1, $tipologia['PROGTESTIT'], 0, trim($tipologia['TESTIT']));
        }
    }

    private function caricaListaCiclo($codtesto) {
        $filtri = array();
        $filtri['CODTESTO'] = $codtesto;
        $result_tab = $this->libDB->leggiBgeTestil($filtri);
        if (!$result_tab) {
            Out::delAllRow($this->GRID_NAME_TESTIL);
        } else {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($this->nameForm);
            $helper->setGridName($this->GRID_NAME_TESTIL);

            $ita_grid01 = $helper->initializeTableArray($result_tab);
            $ita_grid01->getDataPage('json');
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_TESTIL);
        }
    }

    protected function elaboraGridTestil($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $Result_tab_tmp;

        return $Result_tab;
    }

    protected function elaboraRecordsTestil($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODTESTO_testil'] = $Result_tab[$key]['CODTESTO'];
        }
        return $Result_tab;
    }

    private function caricaListaFiltri($codicetesto) {
        if (strlen(trim($codicetesto)) === 0) {
            TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_FILTRI); // Pulisco grid filtri per essere sicuro di avere la situazione pulita.
            return;
        }
        $filtri = array();
        $filtri['CODTESTO'] = trim($codicetesto);
        $result_tab = $this->libDB->leggiBgeTesfil($filtri);
        if (!$result_tab) {
            Out::delAllRow($this->GRID_NAME_FILTRI);
        } else {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($this->nameForm);
            $helper->setGridName($this->GRID_NAME_FILTRI);

            $ita_grid01 = $helper->initializeTableArray($result_tab);
            $ita_grid01->getDataPage('json');
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME_FILTRI);
        }
    }

    protected function elaboraGridFiltri($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsFiltri($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraRecordsFiltri($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGRESSIVO'] = trim($Result_tab[$key]['DESFILTRO']);
        }
        return $Result_tab;
    }

    private function GestisciLabelsRicerca($provtesto) {
        switch ($provtesto) {
            case 0:
                Out::hide($this->nameForm . '_divValido');
                break;

            case 1: //Demografici
                Out::show($this->nameForm . '_divValido');
                Out::show($this->nameForm . '_F_V_TES_1');
                Out::show($this->nameForm . '_F_V_TES_2');
                Out::show($this->nameForm . '_F_V_TES_3');
                Out::show($this->nameForm . '_F_V_TES_4');
                Out::show($this->nameForm . '_F_V_TES_5');
                Out::show($this->nameForm . '_F_V_TES_6');
                Out::show($this->nameForm . '_F_V_TES_7');
                Out::show($this->nameForm . '_F_V_TES_8');
                Out::show($this->nameForm . '_F_V_TES_9');
                Out::html($this->nameForm . '_F_V_TES_1_lbl', "Valido per CERTIFIC.ANAGR.");
                Out::html($this->nameForm . '_F_V_TES_2_lbl', "Valido per ELETTORALE");
                Out::html($this->nameForm . '_F_V_TES_3_lbl', "Valido per CERTIFIC.ELETT.");
                Out::html($this->nameForm . '_F_V_TES_4_lbl', "Valido per CERTIFIC.STA.CIV.");
                Out::html($this->nameForm . '_F_V_TES_5_lbl', "Valido per CERTIFIC.WEB");
                Out::html($this->nameForm . '_F_V_TES_6_lbl', "Valido per AUTOCERTIFICAZIONE");
                Out::html($this->nameForm . '_F_V_TES_7_lbl', "Valido per Atti Notori");
                Out::html($this->nameForm . '_F_V_TES_8_lbl', "Valido per registrazione Residenza");
                Out::html($this->nameForm . '_F_V_TES_9_lbl', "Testo per Famiglia?");
                break;

            case 2: //Tributi
                Out::show($this->nameForm . '_divValido');
                Out::show($this->nameForm . '_F_V_TES_1');
                Out::show($this->nameForm . '_F_V_TES_2');
                Out::show($this->nameForm . '_F_V_TES_3');
                Out::show($this->nameForm . '_F_V_TES_4');
                Out::show($this->nameForm . '_F_V_TES_5');
                Out::html($this->nameForm . '_F_V_TES_1_lbl', "Valido per DENUNCE");
                Out::html($this->nameForm . '_F_V_TES_2_lbl', "Valido per ACCERTAMENTI");
                Out::html($this->nameForm . '_F_V_TES_3_lbl', "Valido per DOCUMENTI");
                Out::html($this->nameForm . '_F_V_TES_4_lbl', "Valido per PROVVEDIMENTI");
                Out::html($this->nameForm . '_F_V_TES_5_lbl', "Valido per SGRAVI-RIMB.");
                Out::html($this->nameForm . '_F_V_TES_6_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_7_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_8_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_9_lbl', " ");
                Out::hide($this->nameForm . '_F_V_TES_6');
                Out::hide($this->nameForm . '_F_V_TES_7');
                Out::hide($this->nameForm . '_F_V_TES_8');
                Out::hide($this->nameForm . '_F_V_TES_9');
                break;

            case 8: //Acquedotto
                Out::show($this->nameForm . '_divValido');
                Out::show($this->nameForm . '_F_V_TES_1');
                Out::show($this->nameForm . '_F_V_TES_2');
                Out::show($this->nameForm . '_F_V_TES_3');
                Out::html($this->nameForm . '_F_V_TES_1_lbl', "Valido per UTENZE");
                Out::html($this->nameForm . '_F_V_TES_2_lbl', "Valido per DOCUMENTI");
                Out::html($this->nameForm . '_F_V_TES_3_lbl', "Valido per PROVVEDIMENTI");
                Out::html($this->nameForm . '_F_V_TES_4_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_5_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_6_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_7_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_8_lbl', " ");
                Out::html($this->nameForm . '_F_V_TES_9_lbl', " ");
                Out::hide($this->nameForm . '_F_V_TES_4');
                Out::hide($this->nameForm . '_F_V_TES_5');
                Out::hide($this->nameForm . '_F_V_TES_6');
                Out::hide($this->nameForm . '_F_V_TES_7');
                Out::hide($this->nameForm . '_F_V_TES_8');
                Out::hide($this->nameForm . '_F_V_TES_9');
                break;
        }
        if (($provtesto >= 3 && $provtesto <= 7) || $provtesto > 8) {
            Out::hide($this->nameForm . '_divValido');
        }
    }

    private function GestisciLabelsGestione($provtesto) {
        switch ($provtesto) {
            case 0:
                Out::hide($this->nameForm . '_divValidoGest');
                break;

            case 1: //Demografici
                Out::show($this->nameForm . '_divValidoGest');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_1]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_2]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_3]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_4]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_5]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_6]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_7]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_8]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_9]');
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_1]_lbl', "Valido per CERTIFIC.ANAGR.");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_2]_lbl', "Valido per ELETTORALE");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_3]_lbl', "Valido per CERTIFIC.ELETT.");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_4]_lbl', "Valido per CERTIFIC.STA.CIV.");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_5]_lbl', "Valido per CERTIFIC.WEB");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_6]_lbl', "Valido per AUTOCERTIFICAZIONE");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_7]_lbl', "Valido per Atti Notori");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_8]_lbl', "Valido per registrazione Residenza");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_9]_lbl', "Testo per Famiglia?");
                break;

            case 2: //Tributi
                Out::show($this->nameForm . '_divValidoGest');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_1]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_2]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_3]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_4]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_5]');
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_1]_lbl', "Valido per DENUNCE");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_2]_lbl', "Valido per ACCERTAMENTI");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_3]_lbl', "Valido per DOCUMENTI");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_4]_lbl', "Valido per PROVVEDIMENTI");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_5]_lbl', "Valido per SGRAVI-RIMB.");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_6]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_7]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_8]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_9]_lbl', " ");
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_6]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_7]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_8]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_9]');
                break;

            case 8: //Acquedotto
                Out::show($this->nameForm . '_divValidoGest');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_1]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_2]');
                Out::show($this->nameForm . '_BGE_TESTI[F_V_TES_3]');
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_1]_lbl', "Valido per UTENZE");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_2]_lbl', "Valido per DOCUMENTI");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_3]_lbl', "Valido per PROVVEDIMENTI");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_4]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_5]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_6]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_7]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_8]_lbl', " ");
                Out::html($this->nameForm . '_BGE_TESTI[F_V_TES_9]_lbl', " ");
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_4]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_5]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_6]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_7]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_8]');
                Out::hide($this->nameForm . '_BGE_TESTI[F_V_TES_9]');
                break;
        }
        if (($provtesto >= 3 && $provtesto <= 7) || $provtesto > 8) {
            Out::hide($this->nameForm . '_divValidoGest');
        }
    }

    public function initComboFormatoFoglio() {
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "0", 1, "0 - Personalizzato");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "1", 0, "1 - US Letter (21,59*27,94)");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "2", 0, "2 - US Legal (21,59*35,56)");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "3", 0, "3 - A3 (29,70*42,00)");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "4", 0, "4 - A4 (21,00*29,70)");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "5", 0, "5 - A5 (14,80*21,00)");
        Out::select($this->nameForm . '_BGE_TESTI[ST_FOGLIO]', 1, "6", 0, "6 - Modulo cont.(trattore) Std Ger (21,59*30,48)");
    }

    public function initComboOrientamentoFoglio() {
        Out::select($this->nameForm . '_BGE_TESTI[ST_ORIENT]', 1, "0", 1, "0 - Verticale");
        Out::select($this->nameForm . '_BGE_TESTI[ST_ORIENT]', 1, "1", 0, "1 - Orizzontale");
    }

    protected function initComboNrPagina() {
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "0", 1, "assente");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "1", 0, "in alto a destra");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "2", 0, "in alto al centro");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "3", 0, "in alto a sinistra");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "4", 0, "in basso a destra");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "5", 0, "in basso al centro");
        Out::select($this->nameForm . '_BGE_TESTI[NUMPAG_POS]', 1, "6", 0, "in basso a sinistra");
    }

    protected function initComboCpi_txt() {
        Out::select($this->nameForm . '_BGE_TESTI[CPI_TXT]', 1, "0", 1, "0-Non specificato(default)");
        Out::select($this->nameForm . '_BGE_TESTI[CPI_TXT]', 1, "7", 0, "7-CPI 10");
        Out::select($this->nameForm . '_BGE_TESTI[CPI_TXT]', 1, "8", 0, "8-CPI 12");
        Out::select($this->nameForm . '_BGE_TESTI[CPI_TXT]', 1, "9", 0, "9-CPI 15");
        Out::select($this->nameForm . '_BGE_TESTI[CPI_TXT]', 1, "10", 0, "10-CPI 17");
    }

    protected function initComboLpi_txt() {
        Out::select($this->nameForm . '_BGE_TESTI[LPI_TXT]', 1, "0", 1, "0-Non specificato(default)");
        Out::select($this->nameForm . '_BGE_TESTI[LPI_TXT]', 1, "4", 0, "4-LPI 4");
        Out::select($this->nameForm . '_BGE_TESTI[LPI_TXT]', 1, "5", 0, "5-LPI 5");
        Out::select($this->nameForm . '_BGE_TESTI[LPI_TXT]', 1, "6", 0, "6-LPI 6");
    }

    private function decodMod($codValue, $codField, $desValue, $desField=null, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBorModuli", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODMODULO", $desValue, $desField, "DESMODULO", 'returnFromBorModuli', $_POST['id'], $searchButton);
    }

    private function decodDiritti($codValue, $codField, $desValue, $desField=null, $searchButton = false) {
        cwbLib::decodificaLookup("cwdDtaDiritt", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "TIPDIRITTI", $desValue, $desField, "DESTIPDIRI", 'returnFromDtaDiritt', $_POST['id'], $searchButton);
    }

    private function decodNumeratori($codValue, $codField, $desValue, $desField=null, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBtaNrd", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "COD_NR_D", $desValue, $desField, "DES_NR_D", 'returnFromBtaNrd', $_POST['id'], $searchButton);
    }

    private function decodTesti($codValue, $codField, $desValue, $desField=null, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBgeTestit", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "PROGTESTIT", $desValue, $desField, "TESTIT", 'returnFromBgeTestit', $_POST['id'], $searchButton);
    }

    public function soloTesto($solotesto) {
        if ($solotesto <> 3) { // Mostro div per settare dimensioni, solo se formato è "Solo Testo".
            Out::hide($this->nameForm . '_divDimensioni');
        } else {
            Out::show($this->nameForm . '_divDimensioni');
        }
    }

    public function provenienzaTesto($testoprov) {
        switch ($testoprov) {
            case 1:
                $testoprov_decod = 'Demografici';
                break;

            case 2:
                $testoprov_decod = 'Tributi';
                break;

            case 3:
                $testoprov_decod = 'Ici';
                break;

            case 4:
                $testoprov_decod = 'Servizi';
                break;

            case 5:
                $testoprov_decod = 'Finanziaria';
                break;

            case 6:
                $testoprov_decod = 'Unità ecografiche';
                break;

            case 7:
                $testoprov_decod = 'Atti Amministrativi';
                break;

            case 8:
                $testoprov_decod = 'Acquedotto';
                break;

            case 9:
                $testoprov_decod = 'Sociali';
                break;

            case 10:
                $testoprov_decod = 'Controllo di gestione';
                break;

            case 11:
                $testoprov_decod = 'Servizi cimiteriali';
                break;

            case 12:
                $testoprov_decod = 'Avviso Unico Pagamento';
                break;

            case 13:
                $testoprov_decod = 'Recupero crediti';
                break;

            case 14:
                $testoprov_decod = 'Anagrafe';
                break;

            case 15:
                $testoprov_decod = 'Elettorale';
                break;

            case 16:
                $testoprov_decod = 'Stato Civile';
                break;

            case 17:
                $testoprov_decod = 'Notifiche';
                break;

            case 18:
                $testoprov_decod = 'Portale Web';
                break;

            case 19:
                $testoprov_decod = 'Protocollo';
                break;
        }
        return $testoprov_decod;
    }

    private function hideProvtesto($provtesto) {
        // In base alla provenienza testo, nascondo/mostro lookup "Diritti" e "Numeratore"
        if ($provtesto > 1) {
            Out::hide($this->nameForm . '_BGE_TESTI[TIPDIRITTI]');
            Out::hide($this->nameForm . '_BGE_TESTI[TIPDIRITTI]_lbl');
            Out::hide($this->nameForm . '_BGE_TESTI[TIPDIRITTI]_butt');
            Out::hide($this->nameForm . '_DESTIPODIRITTI');
            Out::hide($this->nameForm . '_BGE_TESTI[COD_NR_D]');
            Out::hide($this->nameForm . '_BGE_TESTI[COD_NR_D]_lbl');
            Out::hide($this->nameForm . '_BGE_TESTI[COD_NR_D]_butt');
            Out::hide($this->nameForm . '_DES_COD_NR');
            Out::hide($this->nameForm . '_BGE_TESTI[F_ESEC_CTL]');
            Out::hide($this->nameForm . '_BGE_TESTI[F_ESEC_CTL]_lbl');
        } else {
            Out::show($this->nameForm . '_BGE_TESTI[TIPDIRITTI]');
            Out::show($this->nameForm . '_BGE_TESTI[TIPDIRITTI]_lbl');
            Out::show($this->nameForm . '_BGE_TESTI[TIPDIRITTI]_butt');
            Out::show($this->nameForm . '_DESTIPODIRITTI');
            Out::show($this->nameForm . '_BGE_TESTI[COD_NR_D]');
            Out::show($this->nameForm . '_BGE_TESTI[COD_NR_D]_lbl');
            Out::show($this->nameForm . '_BGE_TESTI[COD_NR_D]_butt');
            Out::show($this->nameForm . '_DES_COD_NR');
            Out::show($this->nameForm . '_BGE_TESTI[F_ESEC_CTL]');
            Out::show($this->nameForm . '_BGE_TESTI[F_ESEC_CTL]_lbl');
        }
    }

    private function caricaTesto($codicetesto) {
        $filtri = array();
        $filtri['CODTESTO'] = trim($codicetesto);
        $testo = $this->libDB->leggiBgeTestic($filtri, true);
        //todo : capire come poter aprire tramite visualizzatore l'rtf che estraggo con la query.
//        $files = array(
//            array(
//                'NOME' => 
//            )
//        );                
//        cwbLib::apriVisualizzatoreDocumenti($files);
    }

    public function getReturnData() {
        return $this->returnData;
    }

    public function setReturnData($returnData) {
        $this->returnData = $returnData;
    }

    public function getVisualizzaMenuTesti() {
        return $this->visualizzaMenuTesti;
    }

    public function setVisualizzaMenuTesti($visualizzaMenuTesti) {
        $this->visualizzaMenuTesti = $visualizzaMenuTesti;
    }

    public function getDatiCaricamentoListaGuida() {
        return $this->datiCaricamentoListaGuida;
    }

    public function setDatiCaricamentoListaGuida($datiCaricamentoListaGuida) {
        $this->datiCaricamentoListaGuida = $datiCaricamentoListaGuida;
    }

}
