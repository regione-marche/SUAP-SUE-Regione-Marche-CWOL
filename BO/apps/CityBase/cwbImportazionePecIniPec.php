<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once (ITA_BASE_PATH . '/apps/OpenData/opdLib.class.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbIniPecHelper.php';
include_once ITA_BASE_PATH . '/apps/OpenData/opdIniPecManager.class.php';

function cwbImportazionePecIniPec() {
    $cwbImportazionePecIniPec = new cwbImportazionePecIniPec();
    $cwbImportazionePecIniPec->parseEvent();
    return;
}

class cwbImportazionePecIniPec extends itaFrontControllerCW {

    private $iniPecManager;
    private $matricole;
    private $arrayCf;
    private $progsoggLotto;
    private $tabellaLotto;
    public static $mappingTabResidenti = array(
        'TDE_FAT' => 'TBA_RESID',
        'TIC_PRV' => 'TBA_RESID',
        'TBO_PRV' => 'TBA_RESID',
        'SUC_PROV' => 'TBA_RESID',
        'TDE_ACC' => 'TBA_RESID');

    protected function postItaFrontControllerCostruct() {
        $this->iniPecManager = opdIniPecManager::getInstance();
        Out::activateUploader($this->nameForm . '_UPLOAD_upld_uploader');
        $this->GRID_NAME = 'gridMatricole';
        $this->matricole = cwbParGen::getFormSessionVar($this->nameForm, '_matricole');
        $this->arrayCf = cwbParGen::getFormSessionVar($this->nameForm, '_arrayCf');
        $this->progsoggLotto = cwbParGen::getFormSessionVar($this->nameForm, '_progsoggLotto');
        $this->tabellaLotto = cwbParGen::getFormSessionVar($this->nameForm, '_tabellaLotto');
        $this->connettiDB();
    }

    public function __destruct() {
        parent::__destruct();
        cwbParGen::setFormSessionVar($this->nameForm, '_matricole', $this->matricole);
        cwbParGen::setFormSessionVar($this->nameForm, '_arrayCf', $this->arrayCf);
        cwbParGen::setFormSessionVar($this->nameForm, '_progsoggLotto', $this->progsoggLotto);
        cwbParGen::setFormSessionVar($this->nameForm, '_tabellaLotto', $this->tabellaLotto);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'daGestioneLotti':
                $this->daGestioneLotti();
                break;
            case 'openform':
                $this->apriForm();
                break;
            case "onChange":
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPOSELEZIONE':
                        $this->tipoSelezione($_POST[$this->nameForm . '_TIPOSELEZIONE']);
                        break;
                    case $this->nameForm . '_AGGIORNAPERGIORNI':
                        $this->perGiorni($_POST[$this->nameForm . '_AGGIORNAPERGIORNI']);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->gestioneLista($this->matricole, $this->GRID_NAME, '_SELEZ');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DOWNLOAD':
                        $this->eseguiChiamataScaricoFornituraPec();
                        break;
                    case $this->nameForm . '_UPLOAD_upld':
                        $this->eseguiChiamataRichiestaFornituraPec();
                        break;
                    case $this->nameForm . '_Elenca':
                        $this->elenca();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->aggiornaPec();
                        break;
                }
                break;
        }
    }

    private function apriForm() {
        $this->initComboTipo();
        $this->initComboTipoAggio();
        $this->initComboTabella();
        $this->tipoSelezione(1);
        Out::disableField($this->nameForm . '_GIORNI');
    }

    private function perGiorni($check) {
        Out::disableField($this->nameForm . '_GIORNI');

        if ($check) {
            Out::enableField($this->nameForm . '_GIORNI');
        }
        Out::valore($this->nameForm . '_GIORNI', 0);
    }

    private function initComboTipo() {
        Out::select($this->nameForm . '_TIPOSELEZIONE', 1, 1, 1, 'Seleziona Matricole');
        Out::select($this->nameForm . '_TIPOSELEZIONE', 1, 2, 0, 'Elabora .txt');
    }

    private function initComboTabella() {
        Out::select($this->nameForm . '_TABELLA', 1, 'TBA_RESID', 1, 'TBA_RESID');
    }

    private function aggiornaPec() {
        $flusso_rec = cwbIniPecHelper::RichiestaFornituraPec($this->arrayCf, 'CITYWARE');
        sleep(20);
        // metto sleep perchè tra la richiesta dell'id e lo scarico vero e proprio bisogna aspettare un po' altrimenti il ws non risponde correttamente
        cwbIniPecHelper::ScaricoFornituraPec($flusso_rec['IDRICHIESTA']);
        $arrayRisposta = cwbIniPecHelper::leggiFileRispostaFromId($flusso_rec['IDRICHIESTA']);
        $esito = cwbIniPecHelper::aggiornaTabelleDopoScarico($arrayRisposta, $_POST[$this->nameForm . '_TABELLA'], $this->MAIN_DB);
        if ($esito) {
            $msg = 'Aggiornamento indirizzi PEC completato con successo.';
        } else {
            $msg = 'Aggiornamento indirizzi PEC Fallito.';
        }
        //cartella temporanea per apertura zip
        Out::msgInfo('Attenzione', $msg);
    }

    private function initComboTipoAggio() {
        Out::select($this->nameForm . '_TIPOAGGIO', 1, 0, 1, 'Aggiorna tutti i soggetti');
        Out::select($this->nameForm . '_TIPOAGGIO', 1, 1, 0, 'Aggiorna i soggetti senza PEC');
    }

    private function tipoSelezione($tipo) {
        switch (intval($tipo)) {
            case 1:
                Out::show($this->nameForm . '_divGrid');
                Out::hide($this->nameForm . '_divTxt');
                break;
            case 2:
                Out::hide($this->nameForm . '_divGrid');
                Out::show($this->nameForm . '_divTxt');
                break;
        }
    }

    private function elenca($reload = false) {
        try {
            if ($reload) {
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
                TableView::reload($this->nameForm . '_' . $this->GRID_NAME);
                return;
            }
            TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);
            $tabella = $_POST[$this->nameForm . '_TABELLA'];
            $filtri = array();
            $filtri = $this->getFilters();
            if ($this->progsoggLotto) {
                $filtri['PROGSOGGOR'] = $this->progsoggLotto;
                $tabella= self::$mappingTabResidenti[$this->tabellaLotto];
                Out::valore($this->nameform . '_TABELLA', $tabella);
            }
            $this->matricole = cwbIniPecHelper::recuperaMatricole($tabella, $filtri);
            if ($this->matricole) {
                // leggo con il primo tipometcs perchè è quello con sanzione + alta
                $this->arrayCf = cwbIniPecHelper::creaArrayCodiciFiscali($this->matricole);
                $this->gestioneLista($this->matricole, $this->GRID_NAME, '_SELEZ');
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    public function daGestioneLotti() {
        $this->apriForm();
        Out::hide($this->nameForm . '_divParRic');
        $this->elenca();
    }

    private function gestioneLista($datiLista, $gridName, $suffix) {
        $this->helper->setGridName($gridName);
        $data = $this->elaboraRecords($datiLista, $suffix);
        $this->caricaGrid($data);
    }

    private function caricaGrid($data) {
        if ($_POST["sidx"] && $_POST["sidx"] !== "ROW_ID") {
            $sortIndex = $_POST["sidx"];
            if ($sortIndex === false) {
                throw new Exception("Errore Ordinamento");
            }
            $sortOrder = $_POST["sord"];
        }

        $ita_grid = $this->helper->initializeTableArray($data, $sortIndex, $sortOrder);

        if (!$this->helper->getDataPage($ita_grid)) {
            return;
        } else {
            TableView::enableEvents($this->nameForm . '_' . $this->helper->getGridName());
        }
    }

    protected function elaboraRecords($records, $suffix = null) {
        if ($suffix && $records) {
            $methodName = 'customElaboraRecords' . $suffix;
            $result_tab = $this->$methodName($records, $keyRec, $record);
        }

        return $result_tab;
    }

    private function customElaboraRecords_SELEZ($Result_tab) {
        $result = array();
        foreach ($Result_tab as $key => $Result_rec) {

            $result[$key]['CODFISCALE'] = $Result_rec['CODFISCALE'];
            $result[$key]['NOMINATIVO'] = $Result_rec['NOME'] . ' ' . $Result_rec['COGNOME'];
            $result[$key]['PEC'] = $Result_rec['E_MAIL_PEC'];
            $result[$key]['DATA_AGG_PEC'] = $Result_rec['DATA_AGG_PEC'];
        }
        return $result;
    }

    private function getFilters() {
        $filtri = array();
        $filtri['CODFISCALEDA'] = $_POST[$this->nameForm . '_CODFISCALEDA'];
        $filtri['CODFISCALEA'] = $_POST[$this->nameForm . '_CODFISCALEA'];
        $filtri['NOMINATIVODA'] = $_POST[$this->nameForm . '_NOMINATIVODA'];
        $filtri['NOMINATIVOA'] = $_POST[$this->nameForm . '_NOMINATIVOA'];
        $filtri['TIPOAGGIO'] = $_POST[$this->nameForm . '_TIPOAGGIO'];
        $filtri['GIORNI'] = $_POST[$this->nameForm . '_GIORNI'];
        return $filtri;
    }

    private function connettiDB() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    private function eseguiChiamataRichiestaFornituraPec() {
        $fileName = $_POST['file'];
        $estensione = strtolower(array_pop(explode('.', $fileName)));

        if ($estensione == 'txt') {
            $fullPath = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;
            $content = file_get_contents($fullPath);
            if ($content) {
                // genero un array dal txt
                $arr_cf = explode("\r\n", $content);
                // faccio la richiesta tramite ws
                $flusso_rec = $this->iniPecManager->RichiestaFornituraPec($arr_cf, 'CITYWARE');
                if (!$flusso_rec) {
                    Out::msgStop('Errore', $this->iniPecManager->getLastMessage());
                    return;
                }

                Out::msgInfo('Risultato', 'Richiesta effettuata, id: ' . $flusso_rec['IDRICHIESTA']);
            } else {
                Out::msgStop('Errore', 'Erore caricamento txt');
            }
        } else {
            Out::msgStop('Errore', 'Formato errato, caricare un txt');
        }
    }

    private function eseguiChiamataScaricoFornituraPec() {
        $prova = cwbIniPecHelper::recuperaIndirizziPecDaCf('TBA_RESID', array("CODFISCALE" => 'BBID'), false);
        cwbIniPecHelper::aggiornaTabelleDopoScarico('TBA_RESID', $prova);

        return;
        if (!$_POST[$this->nameForm . '_ID_RICHIESTA']) {
            Out::msgStop('Errore', 'Inserire un id richiesta');
            return;
        }
        // chiamo il ws per scaricare la richiesta fatta 
        // (non è istantaneo, ci mette qualche minuto prima di rendere disponibile lo scarico dopo la richiesta)
        $nomeFile = $this->iniPecManager->ScaricoFornituraPec($_POST[$this->nameForm . '_ID_RICHIESTA']);
        if ($nomeFile) {
            $opdLib = new opdLib();
            $dir = $opdLib->SetDirectoryOpd('INIPEC');
            $fullPath = $dir . '/' . $nomeFile;
            Out::openDocument(utiDownload::getUrl($nomeFile, $fullPath));
        } else {
            Out::msgStop('Errore', $this->iniPecManager->getLastMessage());
        }
    }

    function getProgsoggLotto() {
        return $this->progsoggLotto;
    }

    function setProgsoggLotto($progsoggLotto) {
        $this->progsoggLotto = $progsoggLotto;
    }

    function getTabellaLotto() {
        return $this->tabellaLotto;
    }

    function setTabellaLotto($tabellaLotto) {
        $this->tabellaLotto = $tabellaLotto;
    }

}

?>