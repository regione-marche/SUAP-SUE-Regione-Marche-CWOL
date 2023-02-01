<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibOrganigramma.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once(ITA_BASE_PATH . '/apps/Protocollo/proOrganigrammaDBLib.class.php');

function proAnauff() {
    $proAnauff = new proAnauff();
    $proAnauff->parseEvent();
    return;
}

class proAnauff extends itaModel {

    public $proLib;
    public $PROT_DB;
    public $nameForm = "proAnauff";
    public $divGes = "proAnauff_divGestione";
    public $divRis = "proAnauff_divRisultato";
    public $divTree = "proAnauff_divTree";
    public $divRic = "proAnauff_divRicerca";
    public $divTitolari = "proAnauff_divTitolari";
    public $gridAnauff = "proAnauff_gridAnauff";
    public $gridTree = "proAnauff_gridTree";
    public $gridTitolario = "proAnauff_gridTitolario";
    public $gridSoggetti = "proAnauff_gridSoggetti";
    public $gridMailAutoriz = "proAnauff_gridMailAutoriz";
    public $currUffcod;
    public $proDBLib;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proDBLib = new proOrganigrammaDBLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->currUffcod = App::$utente->getKey($this->nameForm . '_currUffcod');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currUffcod', $this->currUffcod);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                TableView::disableEvents($this->gridAnauff);
                $this->CreaCombo();
                // Out::hide($this->nameForm . '_divSoggetti');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnauff:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                    case $this->gridSoggetti:
                        $anamed_rec = $this->proLib->GetAnamed($_POST['rowid'], 'rowid', 'si', false, false);
                        if ($anamed_rec) {
                            $model = 'proAnamed';
                            itaLib::openForm($model, true);
                            /* @var $proAnamed proAnamed */
                            $_POST = array();
                            $proAnamed = itaModel::getInstance($model);
                            $proAnamed->setEvent('openform');
                            $proAnamed->parseEvent();
                            $proAnamed->Dettaglio($anamed_rec);
                        }
                        break;

                    case $this->gridMailAutoriz:
                        $Uffmail_rec_rec = $this->proLib->GetUffMail($_POST['rowid'], 'rowid', false);
                        $this->GetCampiAutorizzazioni($Uffmail_rec_rec);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnauff:
                        $anauff_rec = $this->proLib->GetAnauff($_POST['rowid'], 'rowid');
                        if ($this->proDBLib->ControllaCancellaUfficio($anauff_rec)) {
                            $this->Dettaglio($_POST['rowid']);
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Ufficio in uso. Non è possibile procedere con la cancellazione.");
                        }
                        break;
                    case $this->gridTitolario:
                        $this->deleteRecord($this->PROT_DB, 'UFFTIT', $_POST['rowid'], '', 'ROWID', false);
                        $this->caricaTitolari($this->formData[$this->nameForm . '_ANAUFF']['UFFCOD']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridTitolario:
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->gridMailAutoriz:
                        $this->GetCampiAutorizzazioni();
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridAnauff:
                        $sql = $this->CreaSql($_POST[$this->nameForm . '_Uffann']);
                        $ita_grid01 = new TableView($this->gridAnauff, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('UFFDES');
                        $ita_grid01->exportXLS('', 'Anauff.xls');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnauff:
                        $sql = $this->CreaSql($_POST[$this->nameForm . '_Uffann']);
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridAnauff:
                        $Anaent_rec = $this->proLib->GetAnaent('2');
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(), "Ente" => $Anaent_rec['ENTDE1']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnauff', $parameters);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Esplora':
                        $this->CaricaTree();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        break;

                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql($_POST[$this->nameForm . '_Uffann']);
                        $ita_grid01 = new TableView($this->gridAnauff, array(
                            'sqlDB' => $this->PROT_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridAnauff]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('UFFDES');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo la ricerca
                            Out::hide($this->divGes, '', 0);
                            Out::hide($this->divRic, '', 0);
                            Out::hide($this->divTree, '', 0);
                            Out::show($this->divRis, '', 0);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridAnauff);
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        break;

                    case $this->nameForm . '_Progressivo':
                        $proLibOrganigramma = new proLibOrganigramma();
                        $codice = $proLibOrganigramma->getProgANAUFF();
                        if ($codice !== false) {
                            Out::valore($this->nameForm . '_ANAUFF[UFFCOD]', $codice);
                            Out::setFocus('', $this->nameForm . '_ANAUFF[UFFDES]');
                        } else {
                            Out::msgStop("Errore", $proLibOrganigramma->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_AggiungiNuovo':
                        $ApriNuovo = 'Nuovo';
                    case $this->nameForm . '_Aggiungi':
                        $Anauff_rec = $_POST[$this->nameForm . '_ANAUFF'];
                        $esito = $this->proDBLib->insertUfficio($Anauff_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        if ($ApriNuovo == 'Nuovo') {
                            $this->Nuovo();
                        } else {
                            $this->Dettaglio($Anauff_rec['UFFCOD'], 'codice');
                        }
                        break;

                    case $this->nameForm . '_AggiornaNuovo':
                        $ApriNuovo = 'Nuovo';
                    case $this->nameForm . '_Aggiorna':
                        $Anauff_rec = $_POST[$this->nameForm . '_ANAUFF'];
                        $esito = $this->proDBLib->updateUfficio($Anauff_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        if ($ApriNuovo == 'Nuovo') {
                            $this->Nuovo();
                        } else {
                            $this->OpenRicerca();
                        }
                        break;

                    case $this->nameForm . '_Cancella':
                        $anauff_rec = $this->proLib->GetAnauff($_POST[$this->nameForm . '_ANAUFF']['ROWID'], 'rowid');
                        if ($this->proDBLib->ControllaCancellaUfficio($anauff_rec)) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Ufficio in uso. Non è possibile procedere con la cancellazione.");
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $Anauff_rec = $_POST[$this->nameForm . '_ANAUFF'];
                        $esito = $this->proDBLib->deleteUfficio($Anauff_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_ANAUFF[UFFRES]_butt':
                        proRic::proRicAnamed($this->nameForm, " WHERE MEDUFF<>''");
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSER]_butt':
                        proRic::proRicAnaservizi($this->nameForm);
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSEGSER]_butt':
                        $SEGR_DB = ItaDB::DBOpen('SEGR');
                        if ($SEGR_DB->exists()) {
                            segRic::segRicAnaser($this->nameForm, " WHERE CODSER<>''", '', false, true);
                        }
                        break;

                    case $this->nameForm . '_ANAUFF[UFFFATOGG]_butt':
                        proRic::proRicOgg($this->nameForm, '');
                        break;

                    case $this->nameForm . '_Torna':
                        $this->TornaElenco();
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSEGCLA]_butt':
                        segRic::segRicAnacla($this->nameForm, '', '', false, true);
                        break;
                    case $this->nameForm . '_ANAUFF[CODICE_PADRE]_butt':
                        proRic::proRicAnauff($this->nameForm);
                        break;

                    case $this->nameForm . '_UFFMAIL[UFFMAIL]_butt':
                        // emlRic::emlRicAccount($this->nameForm, '', 'Mail');
                        proRic::proRicElencoMail($this->nameForm);
                        break;

                    case $this->nameForm . '_ConfermaAutoMail':
                        $Uffmail_rec = $_POST[$this->nameForm . '_UFFMAIL'];
                        if (!$Uffmail_rec['UFFMAIL'] || !$Uffmail_rec['DADATA']) {
                            Out::msgInfo('Attenzione', "Mail e Data inizio validità sono obbligatori.");
                            break;
                        }
                        /*
                         * Check Mail Valida.
                         */
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($Uffmail_rec['UFFMAIL'], 'indirizzo');
                        if (!$mailAccount_rec) {
                            Out::msgInfo('Attenzione', "La mail indicata non è corretta.");
                            break;
                        }

                        /*  Chiudo Finestra Input */
                        Out::closeCurrentDialog();
                        /*
                         * Controllo se Inserimento o Aggiornamento
                         */

                        if ($Uffmail_rec['ROW_ID']) {
                            /* Aggiornamento Dati */
                            $update_info = "Oggetto: aggiorno mail atuorizzazioni: " . $Uffmail_rec['UFFMAIL'] . ' ROWID: ' . $Uffmail_rec['ROW_ID'];
                            if (!$this->updateRecord($this->PROT_DB, 'UFFMAIL', $Uffmail_rec, $update_info, 'ROW_ID')) {
                                Out::msgStop("Inserimento filtro", "Inserimento data set UFFMAIL fallito");
                                return false;
                            }
                        } else {
                            /* Inserimento Record: */
                            $Uffmail_rec['UFFCOD'] = $this->currUffcod;
                            $insert_Info = "Oggetto: inserisco mail atuorizzazioni: " . $Uffmail_rec['UFFMAIL'] . ' Account: ' . $Mail;
                            if (!$this->insertRecord($this->PROT_DB, 'UFFMAIL', $Uffmail_rec, $insert_Info, 'ROW_ID')) {
                                Out::msgStop("Inserimento filtro", "Inserimento data set UFFMAIL fallito");
                                return false;
                            }
                        }
                        $this->CaricaGrigliaMailAutoriz();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Uffcod':
                        $codice = $_POST[$this->nameForm . '_Uffcod'];
                        if ($codice != '') {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $Anauff_tab = $this->GetAnauff($codice);
                            if (count($Anauff_tab) == 1) {
                                $this->Dettaglio($Anauff_tab[0]['ROWID']);
                            }
                        }
                        break;

                    case $this->nameForm . '_ANAUFF[UFFCOD]':
                        $codice = $_POST[$this->nameForm . '_ANAUFF']['UFFCOD'];
                        if (trim($codice) != "") {
                            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . '_ANAUFF[UFFCOD]', $codice);
                        }
                        break;

                    case $this->nameForm . '_ANAUFF[CODICE_PADRE]':
                        if ($_POST[$this->nameForm . '_ANAUFF']['CODICE_PADRE']) {
                            $Anauff_rec = $this->proLib->GetAnauff($_POST[$this->nameForm . '_ANAUFF']['CODICE_PADRE'], 'codice');
                            if ($Anauff_rec) {
                                Out::valore($this->nameForm . '_ANAUFF[CODICE_PADRE]', $Anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_DESC_UFFICIO_PADRE', $Anauff_rec['UFFDES']);
                                break;
                            } else {
                                Out::msgInfo('Attenzione', "Codice ufficio padre non trovato.");
                            }
                        }
                        Out::valore($this->nameForm . '_ANAUFF[CODICE_PADRE]', '');
                        Out::valore($this->nameForm . '_DESC_UFFICIO_PADRE', '');
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAUFF[UFFRES]':
                        $codice = $_POST[$this->nameForm . '_ANAUFF']['UFFRES'];
                        if (is_numeric($codice)) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        }
                        $this->decodAnamed($codice, 'codice');
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSER]':
                        $this->decodAnaservizi($_POST[$this->nameForm . '_ANAUFF']['UFFSER'], 'codice');
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSEGSER]':
                        $this->decodServiziSegreteria($_POST[$this->nameForm . '_ANAUFF']['UFFSEGSER'], 'codice');
                        break;

                    case $this->nameForm . '_ANAUFF[UFFFATOGG]':
                        if ($_POST[$this->nameForm . '_ANAUFF']['UFFFATOGG']) {
                            $Anadog_rec = $this->proLib->GetAnadog($_POST[$this->nameForm . '_ANAUFF']['UFFFATOGG'], 'codice');
                            if ($Anadog_rec) {
                                Out::valore($this->nameForm . '_ANAUFF[UFFFATOGG]', $Anadog_rec['DOGCOD']);
                            } else {
                                Out::msgInfo('Attenzione', 'Codice Oggetto inesistente.');
                                Out::valore($this->nameForm . '_ANAUFF[UFFFATOGG]', '');
                            }
                        }
                        break;

                    case $this->nameForm . '_ANAUFF[UFFSEGCLA]':
                        $this->decodClassificazioneSegreteria($_POST[$this->nameForm . '_ANAUFF']['UFFSEGCLA'], 'codice');
                        break;
                }
                break;
            case 'returnanamed':
                $this->decodAnamed($_POST['retKey']);
                break;

            case 'returnanaservizi':
                $this->decodAnaservizi($_POST['retKey']);
                break;

            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolario($rowid, $tipoArc);
                break;

            case 'returnAnaser':
                $this->decodServiziSegreteria($_POST['retKey'], 'rowid');
                break;

            case 'returndog':
                $Anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ANAUFF[UFFFATOGG]', $Anadog_rec['DOGCOD']);
                break;

            case 'returnAnacla':
                $this->decodClassificazioneSegreteria($_POST['retKey'], 'rowid');
                break;

            case 'returnanauff':
                $this->decodUfficioPadre($_POST['retKey'], 'rowid');
                break;

            case 'returnAccountMail':
                $emlLib = new emlLib();
                $mailAccount_rec = $emlLib->getMailAccount($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_UFFMAIL[UFFMAIL]', $mailAccount_rec['MAILADDR']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currUffcod');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        $this->AzzeraVariabili();
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divTree);
        Out::hide($this->divGes);
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Esplora');
        Out::show($this->nameForm . '');
        Out::setFocus('', $this->nameForm . '_Uffcod');
    }

    function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANAUFF[ROWID]', '');
        $this->currUffcod = '';
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridAnauff);
        TableView::clearGrid($this->gridAnauff);
        TableView::clearGrid($this->gridTitolario);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_AggiungiNuovo');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_AggiornaNuovo');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Esplora');
        Out::hide($this->nameForm . '_divTitolari');
        Out::hide($this->nameForm . '_divSoggetti');
        Out::hide($this->nameForm . '_Torna');
    }

    public function CreaSql($fl_ann = 0) {
        // Importo l'ordinamento del filtro
        $sql = "SELECT ANAUFF.*,"
                . " ANAMED.MEDNOM AS UFFRESP, "
                . " ANASERVIZI.SERDES AS UFFSETTORE "
                . " FROM ANAUFF "
                . " LEFT OUTER JOIN ANAMED ON ANAUFF.UFFRES=ANAMED.MEDCOD "
                . " LEFT OUTER JOIN ANASERVIZI ON ANAUFF.UFFSER=ANASERVIZI.SERCOD "
                . "WHERE UFFCOD=UFFCOD";
        if ($_POST[$this->nameForm . '_Uffcod'] != "") {
            $sql .= " AND UFFCOD = '" . $_POST[$this->nameForm . '_Uffcod'] . "'";
        }
        if ($_POST[$this->nameForm . '_Uffdes'] != "") {
            $sql .= " AND " . $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper(trim($_POST[$this->nameForm . '_Uffdes']))) . "%'";
        }
        if ($fl_ann != 0) {
            $sql .= " AND UFFANN = 1";
        } else {
            $sql .= " AND UFFANN = 0";
        }

        return $sql;
    }

    public function Dettaglio($codice, $tipo = 'rowid') {
        $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        $this->currUffcod = $anauff_rec['UFFCOD'];
        $open_Info = 'Oggetto: ' . $anauff_rec['UFFCOD'] . " " . $anauff_rec['UFFDES'];
        $this->openRecord($this->PROT_DB, 'ANAUFF', $open_Info);
        $this->Nascondi();
        Out::valori($anauff_rec, $this->nameForm . '_ANAUFF');
        $this->decodAnamed($anauff_rec['UFFRES'], 'codice');
        $this->decodAnaservizi($anauff_rec['UFFSER'], 'codice');
        $this->decodServiziSegreteria($anauff_rec['UFFSEGSER'], 'codice');
        $this->decodClassificazioneSegreteria($anauff_rec['UFFSEGCLA'], 'codice');
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_AggiornaNuovo');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::hide($this->divTree, '', 0);
        Out::show($this->divGes, '', 0);
        Out::attributo($this->nameForm . '_ANAUFF[UFFCOD]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_ANAUFF[UFFDES]');
        TableView::disableEvents($this->gridAnauff);
        Out::show($this->nameForm . '_divTitolari');
        $this->caricaTitolari($anauff_rec['UFFCOD']);
        Out::show($this->nameForm . '_divSoggetti');
        $this->caricaSoggettiPresenti($anauff_rec['UFFCOD']);
        if ($anauff_rec['CODICE_PADRE']) {
            $this->decodUfficioPadre($anauff_rec['CODICE_PADRE'], 'codice');
        }
        $this->CaricaGrigliaMailAutoriz();
        Out::tabEnable($this->nameForm . "_divTabUfficio", $this->nameForm . "_paneAutorizzMail");
    }

    /* Non PRENDE GLI ANNULLATI */

    public function caricaSoggettiPresenti($Ufficio) {
        $sql = "SELECT 
                    ANAMED.ROWID AS ROWID,
                    ANAMED.MEDNOM AS MEDNOM, 
                    UFFICI.UFFKEY AS UFFKEY,
                    UFFICI.UFFFI1__1 AS UFFFI1__1,
                    UFFICI.UFFCESVAL AS UFFCESVAL,
                    UFFICI.UFFCOD AS UFFCOD,
                    UFFICI.UFFSCA AS UFFSCA
                    FROM UFFDES UFFICI
                LEFT OUTER JOIN ANAMED ON UFFICI.UFFKEY=ANAMED.MEDCOD
                WHERE UFFICI.UFFCOD = '$Ufficio'  AND MEDANN = 0 ";
        App::log($sql);
        TableView::clearGrid($this->gridSoggetti);
        $ita_grid01 = new TableView($this->gridSoggetti, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        $ita_grid01->setSortIndex('MEDNOM');
        $ita_grid01->setSortOrder('asc');
        $result_tab = $this->elaboraSoggetti($ita_grid01->getDataArray());
        $ita_grid01->getDataPageFromArray('json', $result_tab);
    }

    public function elaboraSoggetti($result_tab) {
        foreach ($result_tab as $key => $record) {
            if ($record['UFFCESVAL']) {
                $icona = 'ui-icon-unlocked';
                $uffcesval = date('d/m/Y', strtotime($record['UFFCESVAL']));
            } else {
                $icona = 'ui-icon-locked';
                $uffcesval = '';
            }
            $result_tab[$key]['CESSAZIONE'] = '<span style="display:inline-block" class="ui-icon ' . $icona . '"></span><span style="display:inline-block">' . $uffcesval . '</span>';
        }
        return $result_tab;
    }

    function GetAnauff($_Uffcod) {
        $sql = "SELECT ROWID FROM ANAUFF WHERE UFFCOD='$_Uffcod'";
        $Anauff_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        return $Anauff_tab;
    }

    function decodAnamed($codice, $tipo = 'rowid') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no');
        Out::valore($this->nameForm . '_ANAUFF[UFFRES]', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_Responsabile', $anamed_rec['MEDNOM']);
    }

    function decodAnaservizi($codice, $tipo = 'rowid') {
        $anaservizi_rec = $this->proLib->getAnaservizi($codice, $tipo);
        Out::valore($this->nameForm . '_ANAUFF[UFFSER]', $anaservizi_rec['SERCOD']);
        Out::valore($this->nameForm . '_Servizio', $anaservizi_rec['SERDES']);
    }

    function decodServiziSegreteria($codice, $tipo = 'rowid') {
        $SEGR_DB = ItaDB::DBOpen('SEGR');
        if ($SEGR_DB->exists()) {
            $segLib = new segLib();
            $anaser_rec = $segLib->GetAnaser($codice, $tipo);
            Out::valore($this->nameForm . '_ANAUFF[UFFSEGSER]', $anaser_rec['CODSER']);
            Out::valore($this->nameForm . '_ServizioSegr', $anaser_rec['DESCSE']);
        }
    }

    function decodUfficioPadre($codice, $tipo = 'rowid') {
        $Anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        Out::valore($this->nameForm . '_ANAUFF[CODICE_PADRE]', $Anauff_rec['UFFCOD']);
        Out::valore($this->nameForm . '_DESC_UFFICIO_PADRE', $Anauff_rec['UFFDES']);
    }

    function decodClassificazioneSegreteria($codice, $tipo = 'rowid') {
        $SEGR_DB = ItaDB::DBOpen('SEGR');
        if ($SEGR_DB->exists()) {
            $segLib = new segLib();
            $Anacla_rec = $segLib->GetAnacla($codice, $tipo);
            Out::valore($this->nameForm . '_ANAUFF[UFFSEGCLA]', $Anacla_rec['IMCLAS']);
            Out::valore($this->nameForm . '_ClassSegr', $Anacla_rec['DESCLA']);
        }
    }

    function decodTitolario($rowid, $tipoArc) {
        $cat = $cla = $fas = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $versione = $anacat_rec['VERSIONE_T'];
                $cat = $anacat_rec['CATCOD'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $versione = $anacla_rec['VERSIONE_T'];
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $versione = $anafas_rec['VERSIONE_T'];
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                break;
        }
        $ufftit_rec = array();
        $ufftit_rec['UFFCOD'] = $this->formData[$this->nameForm . '_ANAUFF']['UFFCOD'];
        $ufftit_rec['CATCOD'] = $cat;
        $ufftit_rec['CLACOD'] = $cla;
        $ufftit_rec['FASCOD'] = $fas;
        $ufftit_rec['VERSIONE_T'] = $versione;
        $sql = "SELECT * FROM UFFTIT WHERE VERSIONE_T=" . $ufftit_rec['VERSIONE_T'] . " AND UFFCOD='" . $ufftit_rec['UFFCOD'] . "' AND CATCOD='" . $ufftit_rec['CATCOD'] . "' AND CLACOD='" . $ufftit_rec['CLACOD'] . "' AND FASCOD='" . $ufftit_rec['FASCOD'] . "'";
        $ufftit_test = $this->proLib->getGenericTab($sql);
        if ($ufftit_test) {
            return;
        }
        $this->insertRecord($this->PROT_DB, 'UFFTIT', $ufftit_rec, '', 'ROWID', false);
        $this->caricaTitolari($ufftit_rec['UFFCOD']);
    }

    private function caricaTitolari($uffcod) {
        TableView::clearGrid($this->gridTitolario);
        $sql = " 
                SELECT
                    UFFTIT.*,
                    AACVERS.DESCRI
                FROM
                    UFFTIT
                LEFT OUTER JOIN AACVERS AACVERS ON AACVERS.VERSIONE_T=UFFTIT.VERSIONE_T
                WHERE UFFCOD='$uffcod'";
        $ita_grid01 = new TableView($this->gridTitolario, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        $ita_grid01->setSortIndex('VERSIONE_T');
        $ita_grid01->setSortOrder('desc');
        Out::setFocus('', $this->nameForm . '_ANAUFF[UFFDES]');
        $result_tab = $this->proLib->elaboraTitolarioUfftit($ita_grid01->getDataArray());
        $ita_grid01->getDataPageFromArray('json', $result_tab);
    }

    public function Nuovo() {
        $this->AzzeraVariabili();
        Out::attributo($this->nameForm . '_ANAUFF[UFFCOD]', 'readonly', '1');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divTree);
        Out::show($this->divGes);
        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AggiungiNuovo');
        Out::show($this->nameForm . '_Progressivo');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::valore($this->nameForm . '_ANAUFF[ROWID]', '');
        Out::valore($this->nameForm . '_ANAUFF[UFFCOD]', '');
        Out::valore($this->nameForm . '_ANAUFF[UFFDES]', '');
        Out::setFocus('', $this->nameForm . '_ANAUFF[UFFCOD]');
        Out::tabDisable($this->nameForm . "_divTabUfficio", $this->nameForm . "_paneAutorizzMail");
        Out::tabSelect($this->nameForm . '_divTabUfficio', $this->nameForm . "_paneDati");
    }

    private function TornaElenco() {
        Out::hide($this->divGes, '');
        Out::hide($this->divRic, '');
        Out::hide($this->divTree, '');
        Out::show($this->divRis, '');
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Nuovo');
        Out::setFocus('', $this->nameForm . '_Nuovo');
        TableView::enableEvents($this->gridAnauff);
    }

    private function CreaCombo() {

        Out::select($this->nameForm . '_ANAUFF[ABILITAPROT]', 1, "", "1", "Arrivo/Partenza");
        Out::select($this->nameForm . '_ANAUFF[ABILITAPROT]', 1, "1", "0", "Arrivo");
        Out::select($this->nameForm . '_ANAUFF[ABILITAPROT]', 1, "2", "0", "Partenza");
        Out::select($this->nameForm . '_ANAUFF[ABILITAPROT]', 1, "3", "0", "Nega");

        Out::select($this->nameForm . '_ANAUFF[ABILITAFASC]', 1, "", "1", "Consultazione");
        Out::select($this->nameForm . '_ANAUFF[ABILITAFASC]', 1, "1", "0", "Archivistica");
        Out::select($this->nameForm . '_ANAUFF[ABILITAFASC]', 1, "2", "0", "Completa");
        Out::select($this->nameForm . '_ANAUFF[ABILITAFASC]', 1, "3", "0", "Movimentazione");


        Out::select($this->nameForm . '_ANAUFF[TIPOUFFICIO]', 1, "", "1", "");
        Out::select($this->nameForm . '_ANAUFF[TIPOUFFICIO]', 1, "R", "0", "Raggruppamento Soggetti");
        Out::select($this->nameForm . '_ANAUFF[TIPOUFFICIO]', 1, "U", "0", "Unità Operativa");
        Out::select($this->nameForm . '_ANAUFF[TIPOUFFICIO]', 1, "A", "0", "Alias Organigramma");
    }

    private function CaricaTree($search = false) {
        $this->getArrayGridUffici();
        Out::clearFields($this->nameForm, $this->divGes);

        $ita_grid = new TableView($this->gridTree, array(
            'arrayTable' => $this->arrayData,
            'rowIndex' => 'idx'
        ));

        $ita_grid->setPageNum(1);
        $ita_grid->setPageRows(10000);
        $ita_grid->setSortIndex('');
        $ita_grid->setSortOrder('');

        if (!$ita_grid->getDataPage('json')) {
            Out::msgStop("Selezione", "Nessun record trovato.");
            $this->OpenRicerca();
        } else {   // Visualizzo il risultato
            Out::hide($this->divGes);
            Out::hide($this->divRic);
            Out::hide($this->divRis);
            Out::show($this->divTree);
            $this->Nascondi();
            Out::show($this->nameForm . '_AltraRicerca');
            TableView::enableEvents($this->gridTree);
        }
    }

    private function getArrayGridUffici() {
        unset($this->RowidPadri);
        $this->arrayData = array();
        $sql = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='' AND UFFANN=0";
        $Uffici_padre_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        if ($Uffici_padre_tab) {
            /*
             * 
             * Costruzione Albero classificazione documenti
             */
            $i = 1;
            $this->arrayData[$i] = array(
                'level' => 0,
                'parent' => null,
                'isLeaf' => 'false',
                'loaded' => 'true',
                'expanded' => 'true',
                'INDICE' => $i,
                'UFFCOD' => '',
                'UFFDES' => '<p style="color:#a51b0b;font-size:15px;"><b>Organigramma</b></p>',
                'ROWID' => 'P',
                'CODICE_PADRE' => '0'
            );

            $this->caricaFigli('', $i, 1);
        }
    }

    private function caricaFigli($cod_padre, &$i, $level_padre) {
        $sql_soggetti = "
                 SELECT
                    UFFDES.UFFKEY,
                    ANAMED.MEDNOM
                 FROM UFFDES
                 LEFT OUTER JOIN ANAMED ON ANAMED.MEDCOD=UFFDES.UFFKEY
                 WHERE UFFCOD='$cod_padre' AND UFFCESVAL=''";
        $Soggetti_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql_soggetti, true);


        $sqlUffici = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='$cod_padre' AND UFFANN=0 AND TIPOUFFICIO<>'R' ORDER BY UFFCOD";
        $Uffici_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlUffici, true);

        $sqlRuoli = "SELECT * FROM ANAUFF WHERE CODICE_PADRE='$cod_padre' AND UFFANN=0 AND TIPOUFFICIO='R'  ORDER BY LIVELLOVIS, UFFCOD";
        $Ruoli_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlRuoli, true);

        $id_parent = $i;

        foreach ($Soggetti_figli_tab as $Soggetti_figli_rec) {
            $i++;
            $curr_idx = $i;
            $descrSoggetto = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-user" title="Soggetto"></span>';
            $descrSoggetto .= $Soggetti_figli_rec['MEDNOM'];
            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => 'true',
                'INDICE' => $i,
                'UFFCOD' => $Soggetti_figli_rec['UFFKEY'],
                'UFFDES' => '<div>' . $descrSoggetto . '</div>',
                'ROWID' => $Uffici_figli_rec['ROWID'],
                'CODICE_PADRE' => $Uffici_figli_rec['CODICE_PADRE']
            );
        }


        foreach ($Ruoli_figli_tab as $Ruoli_figli_rec) {
            $i++;
            $curr_idx = $i;

            $descrRuolo = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-group" title="Ruolo"></span>';
            $descrRuolo .= $Ruoli_figli_rec['UFFDES'] . ' <span style="color:darkred;font-weight:bold;"> [' . $Ruoli_figli_rec['LIVELLOVIS'] . ']</span>';

            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => 'true',
                'INDICE' => $i,
                'UFFCOD' => $Ruoli_figli_rec['UFFCOD'],
                'UFFDES' => '<div>' . $descrRuolo . '</div>',
                'ROWID' => $Ruoli_figli_rec['ROWID'],
                'CODICE_PADRE' => $Ruoli_figli_rec['CODICE_PADRE']
            );

            $this->caricaFigli($Ruoli_figli_rec['UFFCOD'], $i, $level_padre + 1);
            if ($i > $curr_idx) {
                $this->arrayData[$curr_idx]['isLeaf'] = false;
            }
        }

        foreach ($Uffici_figli_tab as $Uffici_figli_rec) {
            $i++;
            $curr_idx = $i;

            $descrUfficio = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-home" title="Ufficio"></span>';
            $descrUfficio .= $Uffici_figli_rec['UFFDES'];

            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => 'true',
                'INDICE' => $i,
                'UFFCOD' => $Uffici_figli_rec['UFFCOD'],
                'UFFDES' => '<div>' . $descrUfficio . '</div>',
                'ROWID' => $Uffici_figli_rec['ROWID'],
                'CODICE_PADRE' => $Uffici_figli_rec['CODICE_PADRE']
            );

            $this->caricaFigli($Uffici_figli_rec['UFFCOD'], $i, $level_padre + 1);
            if ($i > $curr_idx) {
                $this->arrayData[$curr_idx]['isLeaf'] = false;
            }
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1' || $_POST['page'] == 0) {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    public function CaricaGrigliaMailAutoriz() {
        $Uffmail_tab = $this->proLib->GetUffMail($this->currUffcod, 'codice');
        $this->CaricaGriglia($this->gridMailAutoriz, $Uffmail_tab, '1');
    }

    private function GetCampiAutorizzazioni($Uffmail_rec = array()) {
        // ROWID
        $valori[] = array(
            'label' => array(
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[ROW_ID]',
            'name' => $this->nameForm . '_UFFMAIL[ROW_ID]',
            'type' => 'text',
            'class' => 'invisible',
            'style' => 'width:50px;',
            'value' => $Uffmail_rec['ROW_ID']
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Mail:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[UFFMAIL]',
            'name' => $this->nameForm . '_UFFMAIL[UFFMAIL]',
            'type' => 'text',
            'class' => 'ita-edit-lookup',
            'style' => 'width:140px;',
            'value' => $Uffmail_rec['UFFMAIL']
        );
        $valori[] = array(
            'label' => array(
                'value' => "<b>Da Data:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[DADATA]',
            'name' => $this->nameForm . '_UFFMAIL[DADATA]',
            'type' => 'text',
            'class' => 'ita-datepicker',
            'style' => 'width:100px;',
            'value' => $Uffmail_rec['DADATA']
        );
        $valori[] = array(
            'label' => array(
                'value' => "<b>A Data:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[ADATA]',
            'name' => $this->nameForm . '_UFFMAIL[ADATA]',
            'type' => 'text',
            'class' => 'ita-datepicker',
            'style' => 'width:100px;',
            'value' => $Uffmail_rec['ADATA']
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Abilita Invio:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[PERM_SEND]',
            'name' => $this->nameForm . '_UFFMAIL[PERM_SEND]',
            'type' => 'checkbox',
            'class' => 'ita-edit ita-checkbox',
            'style' => ''
        );

        $valori[] = array(
            'label' => array(
                'value' => "<b>Abilita Protocollazione:</b>",
                'style' => 'width:120px;display:block;'
            ),
            'id' => $this->nameForm . '_UFFMAIL[PERM_REC]',
            'name' => $this->nameForm . '_UFFMAIL[PERM_REC]',
            'type' => 'checkbox',
            'class' => 'ita-edit ita-checkbox',
            'style' => ''
        );

        $messaggio = "Indicare la mail che deve si vuole autorizzare.";
        Out::msgInput(
                'Autorizzazioni Mail', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAutoMail', 'class' => 'ita-button-validate', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAutoMail', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '400', true, "<span style=\"font-size:1.0em;font-weight:bold;\">$messaggio</span>"
        );
        Out::valore($this->nameForm . '_UFFMAIL[PERM_REC]', $Uffmail_rec['PERM_REC']);
        Out::valore($this->nameForm . '_UFFMAIL[PERM_SEND]', $Uffmail_rec['PERM_SEND']);
        Out::setFocus('', $this->nameForm . '_UFFMAIL[LOGIN]');
        Out::hide($this->nameForm . '_UFFMAIL[ROW_ID]_field');
        if ($Uffmail_rec['ROWID']) {
            Out::disableField($this->nameForm . '_UFFMAIL[LOGIN]');
        }
    }

}
