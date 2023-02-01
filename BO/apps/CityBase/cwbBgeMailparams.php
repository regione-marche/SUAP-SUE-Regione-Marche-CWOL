<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBgeMailParamsLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function cwbBgeMailparams() {
    $cwbBgeMailparams = new cwbBgeMailparams();
    $cwbBgeMailparams->parseEvent();
    return;
}

class cwbBgeMailparams extends cwbBpaGenTab {

    private $accLib;
    private $libVariabili;
    private $mailParamsLib;
    
    private $DEFAULT_CORPO_FATT_ATTIVA = <<<DEFAULT_CORPO_FATT_ATTIVA
<p>Gent.le Cliente,</p>

<p>si allega alla presente una copia della fattura elettronica emessa.</p>

<p>L'originale della fattura elettronica Vi sarà trasmesso tramite il sistema di Interscambio al Vs. codice destinatario o all'indirizzo pec qualora siano stati comunicati.</p>

<p>Qualora non abbiate ancora proceduto alla comunicazione, l'originale sarà a disposizione nella Vs. area riservata del sito web dell'Agenzia delle Entrate ai sensi della Legge n. 250 del 2017 e del Provvedimento n. 89757 del 2018.</p>

<p>Se desiderate che l'invio della copia pdf della fattura sia effettuato ad un altro indirizzo di posta elettronica, Vi preghiamo di comunicarlo.</p>
DEFAULT_CORPO_FATT_ATTIVA;

    private $DEFAULT_CORPO_ESTR_CONTO_CF = <<<DEFAULT_CORPO_ESTR_CONTO_CF
<p>Invio estratto conto</p>
DEFAULT_CORPO_ESTR_CONTO_CF;
    
    private $DEFAULT_CORPO_BOEL = <<<DEFAULT_CORPO_BOEL
<p>Avviso soglia borsellino</p>
DEFAULT_CORPO_BOEL;
    
    private $DEFAULT_OGGETTO_FATT_ATTIVA = <<<'DEFAULT_OGGETTO_FATT_ATTIVA'
Comunicazione fattura nr. @{$DOCTES.NUMDOCUM}@ del @{$DOCTES.DATADOCUM}@
DEFAULT_OGGETTO_FATT_ATTIVA;

    private $DEFAULT_OGGETTO_ESTR_CONTO_CF = <<<'DEFAULT_OGGETTO_ESTR_CONTO_CF'
Si invia in allegato l'estratto conto
DEFAULT_OGGETTO_ESTR_CONTO_CF;
    
    private $DEFAULT_OGGETTO_BOEL = <<<'DEFAULT_OGGETTO_BOEL'
Questo è un avviso di superamento soglia ...
DEFAULT_OGGETTO_BOEL;
    
    protected function initVars() {
        $this->GRID_NAME = 'gridBgeMailparams';
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 12;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
        $this->accLib = new accLib();        
        $this->mailParamsLib = new cwbBgeMailParamsLib();
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGE_MAILPARAMS[KCODUTE]_butt':
                        $this->lookupAccUtenti();
                        break;
                    case $this->nameForm . '_KCODUTE_butt':
                        $this->lookupAccUtenti();
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[IDACCOUNT]_butt':
                        $this->lookupEmlAccounts();
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[TPL_OGGETTO]_butt':
                        $contenuto = $_POST[$this->nameForm . '_BGE_MAILPARAMS']['TPL_OGGETTO'];
                        $this->instanceLibVariabili();
                        if (!$this->libVariabili) {
                            Out::msgInfo("Attenzione", "Per poter utilizzare il dizionario è necessario selezionare l'area");
                            return;
                        }
                        $Dictionary = $this->libVariabili->getLegendaGenerico('adjacency', 'smarty');
                        $this->CaricaDizionario($Dictionary, 'cwbBgeMailparams', 'returnOggetto', $contenuto);
                        break;
                    case $this->nameForm . '_DESAREA_butt':
                        $this->decodBorMaster($this->formData[$this->nameForm . '_AREA'], ($this->nameForm . '_AREA'), $this->formData[$this->nameForm . '_DESAREA'], ($this->nameForm . '_DESAREA'), true);
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[DESAREA]_butt':
                        $this->decodBorMaster($this->formData[$this->nameForm . '_BGE_MAILPARAMS[AREA]'], ($this->nameForm . '_BGE_MAILPARAMS[AREA]'), $this->formData[$this->nameForm . '_DESAREA'], ($this->nameForm . '_DESAREA'), true);
                        break;
                    case $this->nameForm . '_Default':
                        $this->impostaValoriDefault();
                        break;                    
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGE_MAILPARAMS[KCODUTE]':
                        $formDataKey = $this->nameForm . '_BGE_MAILPARAMS';
                        $utelogRic = $this->formData[$formDataKey]['KCODUTE'];
                        $rowUtente = $this->accLib->GetUtenti($utelogRic, 'utelog');
                        if (!$rowUtente) {
                            Out::msgStop("Errore", "Utente $utelogRic non trovato");
                            Out::valore($this->nameForm . '_BGE_MAILPARAMS[KCODUTE]', '');
                            return;
                        }
                        Out::valore($this->nameForm . '_BGE_MAILPARAMS[KCODUTE]', $rowUtente['UTELOG']);
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[IDACCOUNT]':
                        $formDataKey = $this->nameForm . '_BGE_MAILPARAMS';
                        $rowId = $this->formData[$formDataKey]['IDACCOUNT'];
                        $rowAccount = $this->leggiEmlAccount($rowId);
                        $this->decodAccount($rowAccount);                    
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[AREA]':
                        $this->decodBorMaster($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['AREA'], ($this->nameForm . '_BGE_MAILPARAMS[AREA]'), $_POST[$this->nameForm . '_BGE_MAILPARAMS[DESAREA]'], ($this->nameForm . '_BGE_MAILPARAMS[DESAREA]'));
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[DESAREA]':
                        $this->decodBorMaster($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['AREA'], ($this->nameForm . '_BGE_MAILPARAMS[AREA]'), $_POST[$this->nameForm . '_BGE_MAILPARAMS[DESAREA]'], ($this->nameForm . '_BGE_MAILPARAMS[DESAREA]'));
                        break;
                    case $this->nameForm . '_AREA':
                        $this->decodBorMaster($_POST[$this->nameForm . '_AREA'], ($this->nameForm . '_AREA'), $_POST[$this->nameForm . '_DESAREA'], ($this->nameForm . '_DESAREA'));
                        break;
                    case $this->nameForm . '_DESAREA':
                        $this->decodBorMaster($_POST[$this->nameForm . '_AREA'], ($this->nameForm . '_AREA'), $_POST[$this->nameForm . '_DESAREA'], ($this->nameForm . '_DESAREA'));
                        break;
                }
                break;
            case 'returnAccUtenti':
                $rowId = $_POST['retKey'];
                $rowUtente = $this->accLib->GetUtenti($rowId, 'rowid');
                if (!$rowUtente) {
                    Out::msgStop("Errore", "Utente con rowid=$rowId non trovato");
                    return;
                }
                switch ($_POST['retid']) {
                    case $this->nameForm . '_BGE_MAILPARAMS[KCODUTE]_butt':
                        Out::valore($this->nameForm . '_BGE_MAILPARAMS[KCODUTE]', $rowUtente['UTELOG']);
                        break;
                    case $this->nameForm . '_KCODUTE_butt':
                        Out::valore($this->nameForm . '_KCODUTE', $rowUtente['UTELOG']);
                        break;
                }
                break;
            case 'returnEmlAccounts':
                $rowId = $_POST['retKey'];
                $rowAccount = $this->leggiEmlAccount($rowId);
                if (!$rowAccount) {
                    Out::msgStop("Errore", "Account con rowid=$rowId non trovato");
                    return;
                }
                Out::valore($this->nameForm . '_BGE_MAILPARAMS[IDACCOUNT]', $rowId);
                Out::valore($this->nameForm . '_account_name', $rowAccount['NAME']);
                break;
            case 'returnBorMaster':
                switch ($this->elementId) {
                    case $this->nameForm . '_DESAREA_butt':
                        Out::valore($this->nameForm . '_AREA', $this->formData['returnData']['CODAREAMA']);
                        Out::valore($this->nameForm . '_DESAREA', $this->formData['returnData']['DESAREA']);
                        break;
                    case $this->nameForm . '_BGE_MAILPARAMS[DESAREA]_butt':
                        Out::valore($this->nameForm . '_BGE_MAILPARAMS[AREA]', $this->formData['returnData']['CODAREAMA']);
                        Out::valore($this->nameForm . '_BGE_MAILPARAMS[DESAREA]', $this->formData['returnData']['DESAREA']);
                        break;
                }
                break;
            case 'returnOggetto':
                $this->instanceLibVariabili();
                if (!$this->libVariabili) {
                    Out::msgInfo("Attenzione", "Per poter utilizzare il dizionario è necessario selezionare l'area");
                    return;
                }
                $Dictionary = $this->libVariabili->getLegendaGenerico('adjacency', 'smarty');
                Out::codice("$(protSelector('#" . $this->nameForm . '_BGE_MAILPARAMS[TPL_OGGETTO]' . "')).replaceSelection('" . $Dictionary[$_POST['retKey']]['markupkey'] . "', true);");
                break;
            case 'returnCorpo':
                $this->instanceLibVariabili();
                if (!$this->libVariabili) {
                    Out::msgInfo("Attenzione", "Per poter utilizzare il dizionario è necessario selezionare l'area");
                    return;
                }
                $Dictionary = $this->libVariabili->getLegendaGenerico('adjacency', 'smarty');
                Out::codice('tinyInsertContent("' . $this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]","' . $Dictionary[$_POST['retKey']]['markupkey'] . '");');
                break;
            case 'embedVars':
                $contenuto = $_POST[$this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]'];
                $this->instanceLibVariabili();
                if (!$this->libVariabili) {
                    Out::msgInfo("Attenzione", "Per poter utilizzare il dizionario è necessario selezionare l'area");
                    return;
                }
                $Dictionary = $this->libVariabili->getLegendaGenerico('adjacency', 'smarty');
                $this->CaricaDizionario($Dictionary, 'cwbBgeMailparams', 'returnCorpo', $contenuto);
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['IDMAILPARAMS_formatted'] != '') {
            $this->gridFilters['IDMAILPARAMS'] = $this->formData['IDMAILPARAMS_formatted'];
        }
        if ($_POST['CONTESTO_APP'] != '') {
            $this->gridFilters['CONTESTO_APP'] = $this->formData['CONTESTO_APP'];
        }
        if ($_POST['METADATI'] != '') {
            $this->gridFilters['METADATI'] = $this->formData['METADATI'];
        }
        if ($_POST['KCODUTE'] != '') {
            $this->gridFilters['KCODUTE'] = $this->formData['KCODUTE'];
        }
    }

    protected function postApriForm() {
        $this->initComboContestoApp();
        $this->initComboRepmail();
        Out::valore($this->nameForm . '_account_name', '');
        Out::setFocus("", $this->nameForm . '_CONTESTO');        
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_CONTESTO');
        Out::hide($this->nameForm . '_Default');
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_BGE_MAILPARAMS[IDMAILPARAMS]_field');
        $progr = cwbLibCalcoli::trovaProgressivo("IDMAILPARAMS", "BGE_MAILPARAMS");
        Out::valore($this->nameForm . '_BGE_MAILPARAMS[IDMAILPARAMS]', $progr);
        Out::attributo($this->nameForm . '_BGE_MAILPARAMS[IDMAILPARAMS]', 'readonly', '1');
        Out::setFocus("", $this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]');
        Out::codice('tinyActivate("' . $this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]");');
        Out::valore($this->nameForm . '_account_name', '');
        Out::show($this->nameForm . '_Default');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]');
        Out::codice('tinyActivate("' . $this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]");');
        Out::valore($this->nameForm . '_account_name', '');
        Out::show($this->nameForm . '_Default');
    }
    
    protected function postElenca() {
        Out::hide($this->nameForm . '_Default');
    }
    
    protected function postDettaglio($index) {
        Out::show($this->nameForm . '_BGE_MAILPARAMS[IDMAILPARAMS]_field');
        Out::setFocus('', $this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]');
        Out::codice('tinyActivate("' . $this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]");');
        Out::show($this->nameForm . '_Default');
        
        // Decodifica account                
        $rowAccount = $this->leggiEmlAccount($this->CURRENT_RECORD['IDACCOUNT']);
        $this->decodAccount($rowAccount);
        
        // Decodifica Area Master
        $this->decodBorMaster($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['AREA'], ($this->nameForm . '_BGE_MAILPARAMS[AREA]'), $_POST[$this->nameForm . '_BGE_MAILPARAMS[DESAREA]'], ($this->nameForm . '_BGE_MAILPARAMS[DESAREA]'));        
    }
    
    public function postSqlElenca($filtri, &$sqlParams) {
        $filtri['IDMAILPARAMS'] = trim($this->formData[$this->nameForm . '_IDMAILPARAMS']);
        $filtri['KCODUTE'] = trim($this->formData[$this->nameForm . '_KCODUTE']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeMailparams($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBgeMailparamsChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDMAILPARAMS_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['IDMAILPARAMS']);
        }
        return $Result_tab;
    }

    private function lookupAccUtenti() {
        $sql = "SELECT * FROM UTENTI";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Utenti',
            "width" => '550',
            "height" => '470',
            "sortname" => 'UTELOG',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Codice"
            ),
            "colModel" => array(
                array("name" => 'UTELOG')
            ),
            "dataSource" => array(
                'sqlDB' => 'ITW',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('UTELOG');
        $retId = $_POST['id'];
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnAccUtenti';
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        //$_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function lookupEmlAccounts() {
        $sql = "SELECT * FROM MAIL_ACCOUNT";
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Account',
            "width" => '550',
            "height" => '470',
            "sortname" => 'NAME',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Id",
                "Nome",
                "Indirizzo",
                "Dominio"
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 40),
                array("name" => 'NAME', "width" => 140),
                array("name" => 'MAILADDR', "width" => 140),
                array("name" => 'DOMAIN', "width" => 140),
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('NAME', 'MAILADDR', 'DOMAIN');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnEmlAccounts';
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        //$_POST['keynavButtonAdd'] = $aggiungi;
        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function decodBorMaster($codValue, $codField, $desValue, $desField, $searchButton = false) {
        cwbLib::decodificaLookup("cwbBorMaster", $this->nameForm, $this->nameFormOrig, $codValue, $codField, "CODAREAMA", $desValue, $desField, "DESAREA", 'returnBorMaster', $_POST['id'], $searchButton);
    }
    
    private function leggiEmlAccount($rowId) {
        return $this->mailParamsLib->leggiRowAccount($rowId);
    }        
    
    private function CaricaDizionario($arrayDizionario, $returnModel, $returnEvent, $contenuto = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Dizionario',
            "width" => '560',
            "height" => '430',
            "sortname" => "valore",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $arrayDizionario,
            "colNames" => array(
                "CODICE",
                "DESCRIZIONE"
            ),
            "colModel" => array(
                array("name" => 'markupkey', "width" => 250),
                array("name" => 'descrizione', "width" => 320)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $contenuto;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function instanceLibVariabili() {        
        $area = $this->formData[$this->nameForm . '_BGE_MAILPARAMS']['AREA'];    
        $this->libVariabili = $this->mailParamsLib->instanceLibVariabili($area);
        if (!$this->libVariabili) {
            Out::msgStop("Errore", "Dizionario variabili non previsto per area $area");
        }
    }
    
    private function initComboContestoApp() {
        // TODO: Implementare qui tutti gli altri contesti applicativi

        // Ricerca
        Out::select($this->nameForm . '_CONTESTO_APP', 1, "FATT_ATTIVA", 1, "Fattura Attiva");        
        Out::select($this->nameForm . '_CONTESTO_APP', 1, "ESTR_CONTO_CF", 0, "Estratto Conto CF");        
        Out::select($this->nameForm . '_CONTESTO_APP', 1, "BOEL", 0, "Borsellino Elettronico");        

        // Gestione
        Out::select($this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]', 1, "FATT_ATTIVA", 1, "Fattura Attiva");        
        Out::select($this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]', 1, "ESTR_CONTO_CF", 0, "Estratto Conto CF");        
        Out::select($this->nameForm . '_BGE_MAILPARAMS[CONTESTO_APP]', 1, "BOEL", 0, "Borsellino Elettronico");        
    }
    
    private function initComboRepmail() {
        // Popolamento di tutte le tre combo con i valori di default
        for ($i = 1; $i <=3; $i++) {
            Out::select($this->nameForm . '_BGE_MAILPARAMS[MOD_REPMAIL' . $i . ']', 1, "0", 1, "---Nessuno---");        
            Out::select($this->nameForm . '_BGE_MAILPARAMS[MOD_REPMAIL' . $i . ']', 1, "1", 0, "Utente");        
            Out::select($this->nameForm . '_BGE_MAILPARAMS[MOD_REPMAIL' . $i . ']', 1, "2", 0, "Account");        
            Out::select($this->nameForm . '_BGE_MAILPARAMS[MOD_REPMAIL' . $i . ']', 1, "3", 0, "Organigramma");        
        }                
    }        
    
    private function impostaValoriDefault() {        
        $contesto = $_POST[$this->nameForm . '_BGE_MAILPARAMS']['CONTESTO_APP'];        
        
        // Corpo
        $nomeCorpo = 'DEFAULT_CORPO_' . $contesto;
        $corpo = $this->$nomeCorpo;
        Out::valore($this->nameForm . '_BGE_MAILPARAMS[TPL_CORPO]', $corpo);

        // Oggetto
        $nomeOggetto = 'DEFAULT_OGGETTO_' . $contesto;
        $oggetto = $this->$nomeOggetto;
        Out::valore($this->nameForm . '_BGE_MAILPARAMS[TPL_OGGETTO]', $oggetto);        
    }            
    
    private function decodAccount($rowAccount) {
        if ($rowAccount) {
            Out::valore($this->nameForm . '_account_name', $rowAccount['NAME']);
        } else {
            Out::valore($this->nameForm . '_account_name', '');
        }     
    }
    
}
