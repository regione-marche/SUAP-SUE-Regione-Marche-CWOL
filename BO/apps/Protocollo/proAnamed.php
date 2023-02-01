<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSoggetti.class.php';
include_once(ITA_BASE_PATH . '/apps/Protocollo/proOrganigrammaDBLib.class.php');

function proAnamed() {
    $proAnamed = new proAnamed();
    $proAnamed->parseEvent();
    return;
}

class proAnamed extends itaModel {

    public $PROT_DB;
    public $COMUNI_DB;
    public $ITW_DB;
    public $proLib;
    public $nameForm = "proAnamed";
    public $divRadio = "proAnamed_divRadio";
    public $divDest = "proAnamed_divDest";
    public $divGes = "proAnamed_divGestione";
    public $divRis = "proAnamed_divRisultato";
    public $divRic = "proAnamed_divRicerca";
    public $gridAnamed = "proAnamed_gridAnamed";
    public $gridUffici = "proAnamed_gridUffici";
    public $gridEmail = "proAnamed_gridMail";
    public $gridRuoli = "proAnamed_gridRuoli";
    public $gridSerie = "proAnamed_gridSerie";
    public $returnField = '';
    public $returnModel = '';
    public $soloDest;
    public $uffici;
    public $email;
    public $codice;
    public $rowidAppoggio;
    public $dati = array();
    public $proDBLib;

    public function getDati() {
        return $this->dati;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proDBLib = new proOrganigrammaDBLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->returnField = App::$utente->getKey($this->nameForm . '_returnField');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->soloDest = App::$utente->getKey($this->nameForm . '_soloDest');
        $this->uffici = App::$utente->getKey($this->nameForm . '_uffici');
        $this->email = App::$utente->getKey($this->nameForm . '_email');
        $this->codice = App::$utente->getKey($this->nameForm . '_codice');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->dati = App::$utente->getKey($this->nameForm . '_dati');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnField', $this->returnField);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_soloDest', $this->soloDest);
            App::$utente->setKey($this->nameForm . '_uffici', $this->uffici);
            App::$utente->setKey($this->nameForm . '_email', $this->email);
            App::$utente->setKey($this->nameForm . '_codice', $this->codice);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_dati', $this->dati);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->inizializzaForm();
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridUffici:
                        proRic::proRicAnauff($this->nameForm);
                        break;

                    case $this->gridEmail:
                        $this->email[] = array(
                            "ROWID" => 0,
                            "TDAGSEQ" => "",
                            "TDAGCHIAVE" => "",
                            "TDAGVAL" => "",
                            "PREFERITO" => "<span class=\"ita-icon ita-icon-star-yellow-24x24\">Imposta Questa Mail come Preferita</span>"
                        );
                        $this->CaricaGriglia($this->gridEmail, $this->email);
                        break;

                    case $this->gridRuoli:
                        $valori = $this->GetCampiDocumento();
                        Out::msgInput(
                                'Nuovo Ruolo', $valori
                                , array(
                            'Aggiungi' => array('id' => $this->nameForm . '_AggiuntiRuolo', 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiuntiRuolo', 'model' => $this->nameForm)
                                ), $this->nameForm, 'auto', '400', true, "", "", false
                        );
                        break;

                    case $this->gridSerie:
                        $valori = $this->GetCampiSerie();
                        Out::msgInput(
                                'Nuova Serie', $valori
                                , array(
                            'Aggiungi' => array('id' => $this->nameForm . '_AggiungiSerie', 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiungiSerie', 'model' => $this->nameForm)
                                ), $this->nameForm, 'auto', '400', true, "", "", false
                        );
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnamed:
                        $anamed_rec = $this->proLib->GetAnamed($_POST['rowid'], 'rowid', 'si', false, false);
                        $this->Dettaglio($anamed_rec);
                        if ($this->proDBLib->ControllaCancellaSoggetto($anamed_rec)) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Destinatario usato. Non è possibile procedere con la cancellazione.");
                        }
                        break;

                    case $this->gridUffici:
                        if ($this->ControllaCancellaRuolo($this->uffici[$_POST['rowid']])) {
                            if (array_key_exists($_POST['rowid'], $this->uffici) == true) {
                                unset($this->uffici[$_POST['rowid']]);
                            }
                            $this->CaricaGriglia($this->gridUffici, $this->uffici);
                        } else {
                            if (array_key_exists($_POST['rowid'], $this->uffici) == true) {
                                $this->uffici[$_POST['rowid']]['UFFCESVAL'] = date('Ymd');
                                $this->uffici[$_POST['rowid']] = $this->elaboraRecordUffici($this->uffici[$_POST['rowid']]);
                            }
                            $this->CaricaGriglia($this->gridUffici, $this->uffici);
                            //Out::msgStop("Attenzione", "Ruolo in uso, impossibile procedere con la cancellazione");
                            Out::msgInfo("Attenzione", "Ruolo in uso, impossibile procedere con la cancellazione.Il ruolo è stato cessato");
                        }
                        break;

                    case $this->gridEmail:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'indirizzo mail?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancMail', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;

                    case $this->gridRuoli:
                        $rowid = $_POST['rowid'];
                        if ($rowid) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del Ruolo selezionato?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaRuolo', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaRuolo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;

                    case $this->gridSerie:
                        $rowid = $_POST['rowid'];
                        if ($rowid) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione della Serie selezionata?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaSerie', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaSerie', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAnamed:
                        $anamed_rec = $this->proLib->GetAnamed($_POST['rowid'], 'rowid', 'si', false, false);
                        $this->Dettaglio($anamed_rec);
                        break;
                }
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridAnamed, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('MEDNOM');
                $ita_grid01->exportXLS('', 'Anamed.xls');
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnamed:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->clearGrid($this->gridAnamed);
                        $ita_grid01->getDataPage('json');
                        break;

                    case $this->gridRuoli:
                        if ($_POST[$this->nameForm . '_ANAMED']['MEDCOD']) {
                            $this->CaricaRuoli($_POST[$this->nameForm . '_ANAMED']['MEDCOD']);
                        }
                        break;

                    case $this->gridSerie:
                        if ($_POST[$this->nameForm . '_ANAMED']['MEDCOD']) {
                            $this->CaricaSerie($_POST[$this->nameForm . '_ANAMED']['MEDCOD']);
                        }
                        break;
                }
                break;

            case 'printTableToHTML':
                $anaent_rec = $this->proLib->GetAnaent('2');
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $anaent_rec['ENTDE1']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proAnamed', $parameters);
                break;

            case 'afterSaveCell':
                if ($_POST['value'] != 'undefined') {
                    switch ($_POST['id']) {
                        case $this->gridUffici:
                            switch ($_POST['cellname']) {
                                case 'UFFFI1__3':
                                    foreach ($this->uffici as $key => $ufficio) {
                                        $this->uffici[$key]['UFFFI1__3'] = '';
                                    }
                                    $this->uffici[$_POST['rowid']]['UFFFI1__3'] = $_POST['value'];
                                    $this->CaricaGriglia($this->gridUffici, $this->uffici);
                                    break;

                                default :
                                    $this->uffici[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                                    break;
                            }
                            break;
                        case $this->gridEmail:
                            $OldSeq = $this->email[$_POST['rowid']]["TDAGSEQ"];
                            $this->email[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                            if ($_POST['cellname'] == "TDAGSEQ") {
                                if ($OldSeq && $OldSeq != $_POST['value']) {
                                    $this->email = $this->proLib->array_sort($this->email, "TDAGSEQ");
                                }
                                $this->email = $this->proLib->RiordinaSequenzeMail($this->email);
                                $this->CaricaGriglia($this->gridEmail, $this->email);
                            }
                            break;
                    }
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridUffici:
                        switch ($_POST['colName']) {
                            case 'CERCARUOLO':
                                proRic::proRicAnaruoli($this->nameForm);
                                break;

                            case 'CESSAZIONE':
                                if (array_key_exists($_POST['rowid'], $this->uffici) == true) {
                                    if ($this->uffici[$_POST['rowid']]['UFFCESVAL']) {
                                        $this->uffici[$_POST['rowid']]['UFFCESVAL'] = '';
                                    } else {
                                        $Anauff_rec = $this->proLib->GetAnauff($this->uffici[$_POST['rowid']]['UFFCOD'], 'codice');
                                        if ($Anauff_rec['UFFRES'] == $this->uffici[$_POST['rowid']]['UFFKEY']) {
                                            Out::msgStop("Attenzione", "Per poter cessare l'ufficio è necessario rimuovere il Mittente/Destinatario dal ruolo di Responsabile in anagrafica ufficio.");
                                            break;
                                        }
                                        $this->uffici[$_POST['rowid']]['UFFCESVAL'] = date('Ymd');
                                    }
                                    $this->uffici[$_POST['rowid']] = $this->elaboraRecordUffici($this->uffici[$_POST['rowid']]);
                                    $this->CaricaGriglia($this->gridUffici, $this->uffici);
                                    Out::msgInfo("Attenzione", "Cessazione Variata. Aggiorna dati destinatario.");
                                }
                                break;
                        }
                        break;

                    case $this->gridEmail:
                        switch ($_POST['colName']) {
                            case 'PREFERITO':
                                $this->email[$_POST['rowid']]["TDAGSEQ"] = 1;
                                $this->email = $this->proLib->array_sort($this->email, "TDAGSEQ");
                                $this->email = $this->proLib->RiordinaSequenzeMail($this->email);
                                $this->CaricaGriglia($this->gridEmail, $this->email);
                                Out::valore($this->nameForm . "_ANAMED[MEDEMA]", $this->email[$_POST['rowid']]["TDAGVAL"]);
                                break;
                        }
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        try {
                            $sql = $this->CreaSql();
                            $ita_grid01 = new TableView($this->gridAnamed, array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $sql));
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridAnamed]['gridParam']['rowNum']);
                            $ita_grid01->setSortIndex('MEDNOM');
                            $ita_grid01->setSortOrder('asc');
                            if (!$ita_grid01->getDataPage('json')) {
                                Out::msgStop("Selezione", "Nessun record trovato.");
                                $this->OpenRicerca();
                            } else {   // Visualizzo la ricerca
                                Out::hide($this->divGes, '', 0);
                                Out::hide($this->divRic, '', 0);
                                Out::show($this->divRis, '', 0);
                                $this->Nascondi();
                                Out::show($this->nameForm . '_AltraRicerca');
                                Out::show($this->nameForm . '_Nuovo');
                                Out::setFocus('', $this->nameForm . '_Nuovo');
                                TableView::enableEvents($this->gridAnamed);
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
                            App::log($e->getMessage());
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->SetNuovo();
                        break;

                    case $this->nameForm . '_Progressivo':
                        $proLibSoggetti = new proLibSoggetti();
                        $codice = $proLibSoggetti->getProgANAMED();
                        if ($codice !== false) {
                            Out::valore($this->nameForm . '_ANAMED[MEDCOD]', $codice);
                            Out::setFocus('', $this->nameForm . '_ANAMED[MEDNOM]');
                        } else {
                            Out::msgStop("Errore", $proLibSoggetti->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $anamed_rec = $_POST[$this->nameForm . '_ANAMED'];
                        $anamed_rec['MEDPRI'] = $_POST[$this->nameForm . '_MailXTutti'];
                        if ($this->uffici) {
                            $anamed_rec['MEDUFF'] = 'true';
                        } else {
                            $anamed_rec['MEDUFF'] = '';
                        }

                        $esito = $this->proDBLib->insertSoggetto($anamed_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        $this->insertUffici($anamed_rec);
                        $this->insertEmail($anamed_rec);
                        $anamed = $this->proLib->GetAnamed($anamed_rec['MEDCOD'], 'codice');
                        $this->Dettaglio($anamed);
                        $this->restituisciDati();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $anamed_rec = $_POST[$this->nameForm . '_ANAMED'];
                        $anamed_rec['MEDPRI'] = $_POST[$this->nameForm . '_MailXTutti'];
                        if ($this->uffici) {
                            $anamed_rec['MEDUFF'] = 'true';
                        } else {
                            $anamed_rec['MEDUFF'] = '';
                        }
                        $esito = $this->proDBLib->updateSoggetto($anamed_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        $this->insertUffici($anamed_rec);
                        $this->insertEmail($anamed_rec);
                        $this->Dettaglio($anamed_rec);
                        Out::msgBlock('', 2000, false, "Mittente/Destinatario aggiornato.");
                        $this->restituisciDati();
                        break;

                    case $this->nameForm . '_Cancella':
                        $anamed_rec = $this->proLib->GetAnamed($_POST[$this->nameForm . '_ANAMED']['MEDCOD'], 'codice');
                        if ($this->proDBLib->ControllaCancellaSoggetto($anamed_rec)) {
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        } else {
                            Out::msgStop("Attenzione", "Destinatario usato. Non è possibile procedere con la cancellazione.");
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $anamed_rec = $_POST[$this->nameForm . '_ANAMED'];
                        $esito = $this->proDBLib->deleteSoggetto($anamed_rec);
                        if ($esito !== true) {
                            Out::msgStop('ATTENZIONE', $this->proDBLib->getErrMessage());
                            break;
                        }
                        $this->OpenRicerca();
                        break;

                    case $this->nameForm . '_ConfermaCancMail':
                        if (array_key_exists($this->rowidAppoggio, $this->email) == true) {
                            if ($this->email[$this->rowidAppoggio]['ROWID'] != 0) {
                                $delete_Info = 'Oggetto: Cancellazione mail ' . $this->email[$this->rowidAppoggio]['TDAGVAL'];
                                if (!$this->deleteRecord($this->PROT_DB, 'TABDAG', $this->email[$this->rowidAppoggio]['ROWID'], $delete_Info)) {
                                    Out::msgStop("Attenzione", "Errore in cancellazione della mail su TABDAG");
                                    break;
                                }
                            }
                            unset($this->email[$this->rowidAppoggio]);
                        }
                        $this->email = $this->proLib->RiordinaSequenzeMail($this->email);
                        $this->CaricaGriglia($this->gridEmail, $this->email);
                        $this->insertEmail($_POST[$this->nameForm . "_ANAMED"]);
                        break;
                    case $this->nameForm . '_Importa':
                        Out::msgInfo("Attenzione", "Funzione non più attiva.");
                        break;

                    case $this->nameForm . '_Stampa':
                        $anaent_rec = $this->proLib->GetAnaent('2');
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Ente" => $anaent_rec['ENTDE1']);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proPiantaOrganica', $parameters);
                        break;

                    case $this->nameForm . '_ConfermaImport':
                        $this->ImportaIPA();
                        break;

                    case $this->nameForm . '_Amministratore':
                        $this->restituisciDati();
                        break;

                    case $this->nameForm . '_Torna':
                        $this->TornaElenco();
                        break;

                    case $this->nameForm . '_RUOLOCOD_butt':
                        proRic::proRicAnaruoli($this->nameForm, '', '', 'returnAnaruoloCodice');
                        break;

                    case $this->nameForm . '_SERIECODICE_butt':
                        proRic::proRicSerieArc($this->nameForm, '', 'returnAnaserieCodice');
                        break;

                    case $this->nameForm . '_AggiuntiRuolo':
                        Out::closeCurrentDialog();
                        $this->AggiungiRuoloSoggetto();
                        break;

                    case $this->nameForm . '_AggiungiSerie':
                        Out::closeCurrentDialog();
                        $this->AggiungiSerieSoggetto();
                        break;

                    case $this->nameForm . '_ConfermaCancellaRuolo':
                        $this->CancellaRuoloSoggetto();
                        break;

                    case $this->nameForm . '_ConfermaCancellaSerie':
                        $this->CancellaSerieSoggetto();
                        break;

                    case $this->nameForm . '_SvuotaTag':
                        Out::valore($this->nameForm . '_ANAMED[MEDTAG]', '');
                        break;

                    case 'close-portlet':
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Medcod':
                        $codice = $_POST[$this->nameForm . '_Medcod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            Out::valore($this->nameForm . '_Medcod', $codice);
                            $sql = "SELECT * FROM ANAMED WHERE MEDCOD='$codice'";
                            $anamed_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                            if (count($anamed_tab) == 1) {
                                $this->Dettaglio($anamed_tab[0]);
                            }
                        }
                        break;

                    case $this->nameForm . '_ANAMED[MEDCOD]':
                        $codice = $_POST[$this->nameForm . '_ANAMED']['MEDCOD'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            Out::valore($this->nameForm . '_ANAMED[MEDCOD]', $codice);
                        }
                        break;

                    case $this->nameForm . '_ANAMED[MEDCIT]':
                        $comuni_rec = $this->proLib->getGenericTab("SELECT * FROM COMUNI WHERE COMUNE ='"
                                . addslashes($_POST[$this->nameForm . '_ANAMED']['MEDCIT']) . "'", false, 'COMUNI');
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_ANAMED[MEDPRO]', $comuni_rec['PROVIN']);
                            Out::valore($this->nameForm . '_ANAMED[MEDCAP]', $comuni_rec['COAVPO']);
                        }
                        break;

                    case $this->nameForm . '_tag':
                        if ($_POST[$this->nameForm . '_tag'] != '') {
                            if (strpos($_POST[$this->nameForm . '_tag'], ".") !== false) {
                                Out::msgInfo("Inserimento Tag", "L'uso del punto non è consentito");
                                break;
                            }
                            $posi = strpos($_POST[$this->nameForm . '_ANAMED']['MEDTAG'], '.' . $_POST[$this->nameForm . '_tag'] . '.');
                            if ($posi !== false) {
                                Out::valore($this->nameForm . '_ANAMED[MEDTAG]', str_replace('.' . $_POST[$this->nameForm . '_tag'] . '.', '', $_POST[$this->nameForm . '_ANAMED']['MEDTAG']));
                            } else {
                                Out::valore($this->nameForm . '_ANAMED[MEDTAG]', $_POST[$this->nameForm . '_ANAMED']['MEDTAG'] . '.' . $_POST[$this->nameForm . '_tag'] . '.');
                            }
                            Out::valore($this->nameForm . '_tag', '');
                            Out::setFocus('', $this->nameForm . '_tag');
                        }
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RUOLOCOD':
                        if ($_POST[$this->nameForm . '_RUOLOCOD']) {
                            $this->DecodificaRuoloGenerico($_POST[$this->nameForm . '_RUOLOCOD'], 'codice');
                        } else {
                            Out::valore($this->nameForm . '_RUOLOCOD', '');
                            Out::valore($this->nameForm . '_DESCRUOLO', '');
                        }
                        break;
                }

                break;

            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAMED[MEDCIT]':
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('COMUNE') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $comuni_tab = $this->proLib->getGenericTab("SELECT * FROM COMUNI WHERE " . $where, true, 'COMUNE');
                        if (count($comuni_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($comuni_tab as $comuni_rec) {
                                itaSuggest::addSuggest($comuni_rec['COMUNE']);
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'returnanaruoli':
                $anaruoli_rec = $this->proLib->getAnaruoli($_POST['retKey'], 'rowid');
                $this->uffici[$this->formData['rowid']]['UFFFI1__2'] = $anaruoli_rec['RUOCOD'];
                $this->uffici[$this->formData['rowid']]['RUOLI'] = $anaruoli_rec['RUOCOD'] . ' - ' . $anaruoli_rec['RUODES'];
                $this->CaricaGriglia($this->gridUffici, $this->uffici);
                break;

            case 'returnanauff':
                $sql = "SELECT UFFCOD, UFFDES FROM ANAUFF WHERE ROWID='" . $_POST['retKey'] . "'";
                $anauff_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
                if ($anauff_rec) {
                    $inserisci = true;
                    foreach ($this->uffici as $value) {
                        if ($anauff_rec['UFFCOD'] == $value['UFFCOD']) {
                            $inserisci = false;
                            break;
                        }
                    }
                    if ($inserisci == true) {
                        $this->uffici[] = $this->elaboraRecordUffici(array('UFFCOD' => $anauff_rec['UFFCOD'],
                            'UFFDES' => $anauff_rec['UFFDES'],
                            'UFFSCA' => '0',
                            'UFFFI1__1' => '0',
                            'UFFFI1__2' => '',
                            'UFFCESVAL' => ''));
                        $this->CaricaGriglia($this->gridUffici, $this->uffici);
                    }
                }
                break;

            case 'vediAmministratore':
                $anamed_rec = $this->proLib->GetAnamed($this->dati['id'], 'codice', 'si', false, false);
                $this->Dettaglio($anamed_rec);
                break;

            case 'returnAnaruoloCodice':
                $this->DecodificaRuoloGenerico($_POST['retKey'], 'rowid');
                break;

            case 'returnAnaserieCodice':
                $this->DecodificaSerie($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        if ($this->returnModel != '') {
            $rowId = $_POST['rowid'];
            $_POST = array();
            $_POST['event'] = 'returntoform';
            $_POST['model'] = $this->returnModel;
            $_POST['retField'] = $this->returnField;
            $_POST['retKey'] = $rowId;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
            $model = $this->returnModel;
            $model();
        }
        App::$utente->removeKey($this->nameForm . '_returnField');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_soloDest');
        App::$utente->removeKey($this->nameForm . '_uffici');
        App::$utente->removeKey($this->nameForm . '_email');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_dati');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    private function inizializzaForm($param) {
        $this->soloDest = $_POST['soloDest'];
        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($anaent_37['ENTDE3'] == 1) {
            Out::addClass($this->nameForm . '_ANAMED[MEDNOM]', "ita-edit-uppercase");
            Out::addClass($this->nameForm . '_ANAMED[MEDIND]', "ita-edit-uppercase");
            Out::addClass($this->nameForm . '_ANAMED[MEDCIT]', "ita-edit-uppercase");
            Out::addClass($this->nameForm . '_ANAMED[MEDPRO]', "ita-edit-uppercase");
        }
        if ($_POST['proAnamed_returnField'] == '') {
            $this->OpenRicerca();
            if ($this->soloDest == "1") {
                Out::attributo($this->nameForm . "_FlagTutti", "checked", "0", "checked");
            }
            TableView::disableEvents($this->gridAnamed);
        } else {
            $this->SetNuovo();
            $this->returnField = $_POST['proAnamed_returnField'];
            $this->returnModel = $_POST['proAnamed_returnModel'];
        }
        if ($this->dati['daDove'] != '') {
            Out::hide($this->nameForm . '_Stampa');
        }
    }

    private function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::hide($this->divRis, '');
        Out::hide($this->divGes, '');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm . '_Stampa');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Medcod');
    }

    private function AzzeraVariabili() {
        Out::valore($this->nameForm . '_ANAMED[ROWID]', '');
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        if ($this->soloDest != "") {
            Out::attributo($this->nameForm . "_FlagTutti", "checked", "0", "checked");
            Out::hide($this->divRadio, '');
        } else {
            Out::attributo($this->nameForm . "_FlagEsterno", "checked", "1", "checked");
            Out::attributo($this->nameForm . "_FlagInterno", "checked", "0", "checked");
            Out::attributo($this->nameForm . "_FlagTutti", "checked", "0", "");
        }
        TableView::clearToolbar($this->gridAnamed);
        TableView::disableEvents($this->gridAnamed);
        TableView::clearGrid($this->gridUffici);
        TableView::clearGrid($this->gridEmail);
        $this->uffici = '';
        $this->email = '';
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Progressivo');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_StampaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_divDest');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_Amministratore');
        Out::hide($this->nameForm . '_Torna');
    }

    private function CreaSql() {
        $sql = "SELECT * FROM ANAMED";
        if ($_POST[$this->nameForm . '_TipoSog'] == "E") {
            $where = " WHERE MEDUFF " . $this->PROT_DB->isBlank();
        } else if ($_POST[$this->nameForm . '_TipoSog'] == "I") {
            $where = " WHERE MEDUFF " . $this->PROT_DB->isNotBlank();
        } else {
            $where = " WHERE MEDUFF=MEDUFF";
        }
        if ($_POST[$this->nameForm . '_Medcod'] != "") {
            $codice = $_POST[$this->nameForm . '_Medcod'];
            if (is_numeric($codice)) {
                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            }
            $where = $where . " AND MEDCOD='$codice'";
        }
        if ($_POST[$this->nameForm . '_Mednom'] != "") {
            $valore = addslashes(trim($_POST[$this->nameForm . '_Mednom']));
            $where = $where . " AND " . $this->PROT_DB->strUpper("MEDNOM") . " LIKE " . $this->PROT_DB->strUpper("'%" . addslashes($valore) . "%'");
        }
        if ($_POST['_search'] == true) {
            if ($_POST['MEDCOD']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDCOD') . " = '" . addslashes(strtoupper($_POST['MEDCOD'])) . "'";
            }
            if ($_POST['MEDNOM']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDNOM') . " LIKE " . $this->PROT_DB->strUpper("'%" . addslashes($_POST['MEDNOM']) . "%'");
            }
            if ($_POST['MEDIND']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDIND') . " LIKE '%" . addslashes(strtoupper($_POST['MEDIND'])) . "%'";
            }
            if ($_POST['MEDCIT']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDCIT') . " LIKE '%" . addslashes(strtoupper($_POST['MEDCIT'])) . "%'";
            }
            if ($_POST['MEDIND']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDIND') . " LIKE '%" . addslashes(strtoupper($_POST['MEDIND'])) . "%'";
            }
            if ($_POST['MEDCAP']) {
                $where .= " AND " . $this->PROT_DB->strUpper('MEDCAP') . " LIKE '%" . addslashes(strtoupper($_POST['MEDCAP'])) . "%'";
            }
        }
        if ($_POST[$this->nameForm . '_Medann'] != 0) {
            $where .= " AND MEDANN = 1";
        } else {
            $where .= " AND MEDANN = 0";
        }
        $sql = $sql . $where;
        return $sql;
    }

    public function Dettaglio($anamed_rec) {
        $open_Info = 'Oggetto: ' . $anamed_rec['MEDCOD'] . " " . $anamed_rec['MEDNOM'];
        $this->openRecord($this->PROT_DB, 'ANAMED', $open_Info);
        $this->Nascondi();
        Out::valori($anamed_rec, $this->nameForm . '_ANAMED');
        Out::valore($this->nameForm . '_MailXTutti', substr($anamed_rec['MEDPRI'], 0, 1));
        Out::tabEnable($this->nameForm . '_tabDestinatario', $this->nameForm . '_paneRuoli');

        if ($_POST[$this->nameForm . '_TipoSog'] == "E") {
            
        } else {
            $this->decodUffdes($anamed_rec['MEDCOD']);
        }
        $this->CaricaEmail($anamed_rec['MEDCOD']);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic, '', 0);
        Out::hide($this->divRis, '', 0);
        Out::show($this->divGes, '', 0);

        if ($this->soloDest != "") {
            Out::hide($this->divDest, '');
        }

        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($anaent_37['ENTVAL'] == 0) {
            Out::tabRemove($this->nameForm . "_tabDestinatario", $this->nameForm . "_paneEmail");
            Out::attributo($this->nameForm . '_ANAMED[MEDEMA]', 'readonly', '1');
        } else {
            Out::attributo($this->nameForm . '_ANAMED[MEDEMA]', 'readonly', '0');
        }
        if ($this->dati['daDove']) {
            if ($this->dati['id'] == '') {
                Out::show($this->nameForm . '_Amministratore');
            } else {
                Out::hide($this->nameForm . '_Amministratore');
            }
            Out::hide($this->nameForm . '_Cancella');
            Out::hide($this->nameForm . '_AltraRicerca');
        }
        Out::setFocus('', $this->nameForm . '_ANAMED[MEDNOM]');

        $this->CaricaRuoli($anamed_rec['MEDCOD']);
        $this->CaricaSerie($anamed_rec['MEDCOD']);

        Out::attributo($this->nameForm . '_ANAMED[MEDTAG]', 'readonly', '0');
        TableView::disableEvents($this->gridAnamed);
    }

    private function CaricaEmail($medcod) {
        $Anamed_rec = $this->proLib->GetAnamed($medcod, 'codice', 'si', false, false);
        $Tabdag_tab_mail_tmp = $this->proLib->GetTabdag("ANAMED", "chiave", $Anamed_rec['ROWID'], "EMAIL", "", true);
        $Tabdag_tab_pec_tmp = $this->proLib->GetTabdag("ANAMED", "chiave", $Anamed_rec['ROWID'], "EMAILPEC", "", true);
        $Tabdag_tab_mail = $this->proLib->array_sort($Tabdag_tab_mail_tmp, "TDAGSEQ");
        $Tabdag_tab_pec = $this->proLib->array_sort($Tabdag_tab_pec_tmp, "TDAGSEQ");
        $this->email = array_merge($Tabdag_tab_pec, $Tabdag_tab_mail);
        foreach ($this->email as $key => $mail) {
            $this->email[$key]['PREFERITO'] = "<span class=\"ita-icon ita-icon-star-yellow-24x24\">Imposta Questa Mail come Preferita</span>";
        }
        $this->CaricaGriglia($this->gridEmail, $this->email);
    }

    private function decodUffdes($medcod) {
        $sql = "SELECT UFFDES.ROWID AS ROWID, UFFDES.UFFKEY AS UFFKEY, UFFDES.UFFCOD AS UFFCOD,
        UFFDES.UFFSCA AS UFFSCA, ANAUFF.UFFDES AS UFFDES, UFFDES.UFFFI1__1 AS UFFFI1__1, UFFDES.UFFFI1__2 AS UFFFI1__2, 
        UFFDES.UFFFI1__3 AS UFFFI1__3, UFFDES.UFFCESVAL AS UFFCESVAL, UFFANN AS UFFANN, UFFPROTECT AS UFFPROTECT
        FROM UFFDES UFFDES LEFT OUTER JOIN ANAUFF ANAUFF
        ON UFFDES.UFFCOD = ANAUFF.UFFCOD
        WHERE UFFDES.UFFKEY='$medcod' ORDER BY UFFFI1__3 DESC, UFFSCA DESC, UFFFI1__1 DESC, UFFDES ASC";
        $this->uffici = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        foreach ($this->uffici as $key => $value) {
            $this->uffici[$key] = $this->elaboraRecordUffici($value);
        }
        $this->CaricaGriglia($this->gridUffici, $this->uffici);
    }

    private function elaboraRecordUffici($record) {
        $record['CERCARUOLO'] = '<span class="ui-icon ui-icon-search"></span>';
        if ($record['UFFCESVAL']) {
            $icona = 'ui-icon-unlocked';
            $uffcesval = date('d/m/Y', strtotime($record['UFFCESVAL']));
        } else {
            $icona = 'ui-icon-locked';
            $uffcesval = '';
        }

        if ($record['UFFFI1__2']) {
            $Anaruoli_rec = $this->proLib->getAnaruoli($record['UFFFI1__2']);
            $record['RUOLI'] = $record['UFFFI1__2'] . ' - ' . $Anaruoli_rec['RUODES'];
        }
        $record['CESSAZIONE'] = '<span style="display:inline-block" class="ui-icon ' . $icona . '"></span><span style="display:inline-block">' . $uffcesval . '</span>';

        return $record;
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function SetNuovo() {
        $this->AzzeraVariabili();
        Out::attributo($this->nameForm . '_ANAMED[MEDCOD]', 'readonly', '1');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Progressivo');
        Out::setFocus('', $this->nameForm . '_ANAMED[MEDCOD]');
        Out::tabDisable($this->nameForm . '_tabDestinatario', $this->nameForm . '_paneRuoli');
        Out::tabSelect($this->nameForm . '_tabDestinatario', $this->nameForm . '_paneUffici');
        Out::attributo($this->nameForm . '_ANAMED[MEDTAG]', 'readonly', '0');

        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($anaent_37['ENTVAL'] == 0) {
            Out::tabRemove($this->nameForm . "_tabDestinatario", $this->nameForm . "_paneEmail");
            Out::attributo($this->nameForm . '_ANAMED[MEDEMA]', 'readonly', '1');
        } else {
            Out::attributo($this->nameForm . '_ANAMED[MEDEMA]', 'readonly', '0');
        }
        Out::attributo($this->nameForm . '_ANAMED[MEDEMA]', 'readonly', '1');
    }

    private function insertEmail($anamed_rec) {
        foreach ($this->email as $mail) {
            unset($mail['PREFERITO']);
            if ($mail['ROWID'] == 0) {
                if ($mail["TDAGCHIAVE"] == "EMAILPEC") {
                    $Tabdag_tab_emailpec = $this->proLib->GetTabdag("ANAMED", "chiave", $anamed_rec['ROWID'], $mail["TDAGCHIAVE"], 0, true);
                    if ($Tabdag_tab_emailpec) {
                        $mail['TDPROG'] = count($Tabdag_tab_emailpec);
                    }
                } else {
                    $Tabdag_tab_email = $this->proLib->GetTabdag("ANAMED", "chiave", $anamed_rec['ROWID'], $mail["TDAGCHIAVE"], 0, true);
                    if ($Tabdag_tab_email) {
                        $mail['TDPROG'] = count($Tabdag_tab_email);
                    }
                }
                $mail['TDCLASSE'] = "ANAMED";
                $mail['TDROWIDCLASSE'] = $anamed_rec['ROWID'];
                $insert_Info = 'Oggetto: Inserisco Mail' . $mail['TDAGVAL'] . " su utente " . $anamed_rec['MEDCOD'];
                $this->insertRecord($this->PROT_DB, 'TABDAG', $mail, $insert_Info);
            } else {
                $update_Info = 'Oggetto: Aggiorno Mail' . $mail['TDAGVAL'] . " su utente " . $anamed_rec['MEDCOD'];
                $this->updateRecord($this->PROT_DB, 'TABDAG', $mail, $update_Info);
            }
        }
    }

    private function insertUffici($anamed_rec) {
        $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
        foreach ($uffdes_tab as $uffdes_rec) {
            $delete_Info = 'Oggetto: ' . $uffdes_rec['UFFKEY'] . " " . $uffdes_rec['UFFCOD'];
            $this->deleteRecord($this->PROT_DB, 'UFFDES', $uffdes_rec['ROWID'], $delete_Info);
        }
        foreach ($this->uffici as $ufficio) {
            $uffdes_new = array(
                'UFFKEY' => $anamed_rec['MEDCOD'],
                'UFFCOD' => $ufficio['UFFCOD'],
                'UFFSCA' => $ufficio['UFFSCA'],
                'UFFPROTECT' => $ufficio['UFFPROTECT'],
                'UFFFI1__1' => $ufficio['UFFFI1__1'],
                'UFFFI1__2' => $ufficio['UFFFI1__2'],
                'UFFFI1__3' => $ufficio['UFFFI1__3'],
                'UFFCESVAL' => $ufficio['UFFCESVAL']
            );
            $insert_Info = 'Oggetto: ' . $anamed_rec['MEDCOD'] . " " . $ufficio['UFFCOD'];
            $this->insertRecord($this->PROT_DB, 'UFFDES', $uffdes_new, $insert_Info);
        }
    }

    private function ImportaIPA() {
        $sql = "SELECT * FROM AMMINISTRAZIONI ORDER BY DES_AMM";
        $amm_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
        $i = 0;
        $u = 0;
        $this->codice = 1;
        $t0 = time();
        foreach ($amm_tab as $key => $amm_rec) {
            if ($amm_rec['TIPO_MAIL1'] != 'pec') {
                continue;
            }
            $sql = "SELECT * FROM ANAMED WHERE MEDNOM = '" . addslashes(strtoupper(substr($amm_rec['DES_AMM'], 0, 100))) . "'
                                AND MEDUFF = '' 
                                AND " . $this->PROT_DB->strUpper('MEDCIT') . " = '" . addslashes(strtoupper($amm_rec['COMUNE'])) . "'
                                AND " . $this->PROT_DB->strUpper('MEDPRO') . " = '" . strtoupper($amm_rec['PROVINCIA']) . "' 
                                AND " . $this->PROT_DB->strUpper('MEDCAP') . " = '" . strtoupper($amm_rec['CAP']) . "' 
                                AND " . $this->PROT_DB->strUpper('MEDIND') . " = '" . addslashes(strtoupper($amm_rec['INDIRIZZO'])) . "' ";
            $anamed_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
            if ($anamed_rec) {
                if ($amm_rec['CF_VALIDATO'] == 'S') {
                    $anamed_rec['MEDFIS'] = $amm_rec['CF'];
                }
                $anamed_rec['MEDEMA'] = $amm_rec['MAIL1'];
                $anamed_rec['MEDTIPIND'] = 'pec';
                $anamed_rec['MEDIPA'] = true;
                if ($this->updateRecord($this->PROT_DB, 'ANAMED', $anamed_rec, 'update IPA')) {
                    $u++;
                } else {
                    Out::msgStop("Errore update da AMMINISTRAZIONI", print_r($anamed_rec, true));
                }
            } else {
                $codice = 0;
                while (true) {
                    $sql = "SELECT MEDCOD FROM ANAMED WHERE MEDCOD = '" . str_pad($this->codice, 6, '0', STR_PAD_LEFT) . "'";
                    $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
                    if ($rec) {
                        $this->codice++;
                    } else {
                        break;
                    }
                }
                $codice = str_pad($this->codice, 6, '0', STR_PAD_LEFT);
                $anamed_rec = array();
                $anamed_rec['MEDCOD'] = $codice;
                $anamed_rec['MEDNOM'] = strtoupper($amm_rec['DES_AMM']);
                $anamed_rec['MEDIND'] = strtoupper($amm_rec['INDIRIZZO']);
                $anamed_rec['MEDCAP'] = strtoupper($amm_rec['CAP']);
                $anamed_rec['MEDCIT'] = strtoupper($amm_rec['COMUNE']);
                $anamed_rec['MEDPRO'] = strtoupper($amm_rec['PROVINCIA']);
                $anamed_rec['MEDEMA'] = $amm_rec['MAIL1'];
                if ($amm_rec['CF_VALIDATO'] == 'S') {
                    $anamed_rec['MEDFIS'] = $amm_rec['CF'];
                }
                $anamed_rec['MEDTIPIND'] = 'pec';
                $anamed_rec['MEDIPA'] = true;
                if ($this->insertRecord($this->PROT_DB, 'ANAMED', $anamed_rec, 'insert IPA')) {
                    $i++;
                } else {
                    Out::msgStop("Errore insert da AMMINISTRAZIONI", print_r($anamed_rec, true));
                }
            }
            $t1 = time();
            if (($t1 - $t0) > 600) { //mi fermo in ogni caso dopo 5 minuti!
                Out::msgStop("FERMATO", "Trascorsi 10 minuti per importazione da AMMINISTRAZIONI.\n\rProcedura interrotta.");
                break;
                return;
            }
        }

        $sql = "SELECT * FROM AOO ORDER BY DES_AOO";
        $aoo_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);

        $uu = 0;
        $ii = 0;
        $t0 = time();
        foreach ($aoo_tab as $key => $aoo_rec) {
            if ($aoo_rec['TIPO_MAIL1'] != 'pec') {
                continue;
            }

            $sql = "SELECT DES_AMM FROM AMMINISTRAZIONI WHERE COD_AMM = '" . $aoo_rec['COD_AMM'] . "'";
            $amm_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, false);
            if (!$amm_rec) {
                continue;
            }

            $sql = "SELECT * FROM ANAMED WHERE MEDNOM = '" . addslashes(substr(strtoupper($amm_rec['DES_AMM']) . "-" . strtoupper($aoo_rec['DES_AOO']), 0, 100)) . "'
                                AND MEDUFF = '' 
                                AND " . $this->PROT_DB->strUpper('MEDCIT') . " = '" . addslashes(strtoupper($aoo_rec['COMUNE'])) . "'
                                AND " . $this->PROT_DB->strUpper('MEDPRO') . " = '" . strtoupper($aoo_rec['PROVINCIA']) . "' 
                                AND " . $this->PROT_DB->strUpper('MEDCAP') . " = '" . strtoupper($aoo_rec['CAP']) . "' 
                                AND " . $this->PROT_DB->strUpper('MEDIND') . " = '" . addslashes(strtoupper($aoo_rec['INDIRIZZO'])) . "' ";
            $anamed_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

            if ($anamed_rec) {
                $anamed_rec['MEDEMA'] = $aoo_rec['MAIL1'];
                $anamed_rec['MEDTIPIND'] = 'pec';
                $anamed_rec['MEDTEL'] = $aoo_rec['TEL'];
                $anamed_rec['MEDCODAOO'] = $aoo_rec['COD_AOO'];
                $anamed_rec['MEDDENAOO'] = $aoo_rec['DES_AOO'];
                $anamed_rec['MEDIPA'] = true;
                if ($this->updateRecord($this->PROT_DB, 'ANAMED', $anamed_rec, 'update IPA')) {
                    $uu++;
                } else {
                    Out::msgStop("Errore update da AOO", print_r($anamed_rec, true));
                }
            } else {
                while (true) {
                    $sql = "SELECT MEDCOD FROM ANAMED WHERE MEDCOD = '" . str_pad($this->codice, 6, '0', STR_PAD_LEFT) . "'";
                    $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
                    if ($rec) {
                        $this->codice++;
                    } else {
                        break;
                    }
                }
                $codice = str_pad($this->codice, 6, '0', STR_PAD_LEFT);
                $anamed_rec = array();
                $anamed_rec['MEDCOD'] = $codice;
                $anamed_rec['MEDNOM'] = strtoupper($amm_rec['DES_AMM']) . "-" . strtoupper($aoo_rec['DES_AOO']);
                $anamed_rec['MEDIND'] = strtoupper($aoo_rec['INDIRIZZO']);
                $anamed_rec['MEDCAP'] = strtoupper($aoo_rec['CAP']);
                $anamed_rec['MEDCIT'] = strtoupper($aoo_rec['COMUNE']);
                $anamed_rec['MEDPRO'] = strtoupper($aoo_rec['PROVINCIA']);
                $anamed_rec['MEDEMA'] = $aoo_rec['MAIL1'];
                $anamed_rec['MEDTEL'] = $aoo_rec['TEL'];
                $anamed_rec['MEDCODAOO'] = $aoo_rec['COD_AOO'];
                $anamed_rec['MEDDENAOO'] = $aoo_rec['DES_AOO'];
                $anamed_rec['MEDTIPIND'] = 'pec';
                $anamed_rec['MEDIPA'] = true;

                if ($this->insertRecord($this->PROT_DB, 'ANAMED', $anamed_rec, 'insert IPA')) {
                    $ii++;
                } else {
                    Out::msgStop("Errore insert da AOO", print_r($anamed_rec, true));
                }
            }
            $t1 = time();
            if (($t1 - $t0) > 900) {
                Out::msgStop("FERMATO", "Trascorsi oltre 15 minuti per importazione da AOO.\n\rProcedura interrotta.");
                break;
            }
        }
    }

    private function ControllaCancella($medcod) {
        $sql = "SELECT ROWID FROM ARCITE WHERE ITEDES = '" . $medcod . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM ANADES WHERE DESCOD = '" . $medcod . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM UTENTI WHERE UTEANA__1 = '" . $medcod . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM ANAPRO WHERE PROCON = '" . $medcod . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        return true;
    }

    private function ControllaCancellaRuolo($uffdes_rec) {

        $sql = "SELECT ROWID FROM ARCITE WHERE ITEDES = '" . $uffdes_rec['UFFKEY'] . "' AND ITEUFF = '" . $uffdes_rec['UFFCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }

        $sql = "SELECT ROWID FROM ANADES WHERE DESCOD='" . $uffdes_rec['UFFKEY'] . "' AND DESCUF='" . $uffdes_rec['UFFCOD'] . "' LIMIT 1";
        $rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($rec) {
            return false;
        }
        return true;
    }

    private function restituisciDati() {
        if ($this->dati['daDove']) {
            $modelObj = itaModel::getInstance($this->getReturnModel());
            $modelObj->setEvent($this->getReturnEvent());
            $modelObj->setReturnModel($this->getReturnModel());
            $modelObj->setReturnDatiAnamed($_POST);
            $modelObj->parseEvent();
            $this->returnToParent();
        }
    }

    private function TornaElenco() {
        Out::hide($this->divGes);
        Out::hide($this->divRic);
        Out::show($this->divRis);
        $this->Nascondi();
        Out::show($this->nameForm . '_AltraRicerca');
        TableView::enableEvents($this->gridAnamed);
        TableView::reload($this->gridAnamed);
    }

    private function GetCampiDocumento() {
        $valori[] = array(
            'label' => array(
                'value' => "Codice Ruolo",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_RUOLOCOD',
            'name' => $this->nameForm . '_RUOLOCOD',
            'type' => 'text',
            'class' => 'ita-edit-lookup ita-edit-onchange',
            'size' => '8',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Descrizione",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_DESCRUOLO',
            'name' => $this->nameForm . '_DESCRUOLO',
            'type' => 'text',
            'class' => 'ita-readonly',
            'size' => '35',
            'value' => ''
        );
        return $valori;
    }

    private function GetCampiSerie() {
        $valori[] = array(
            'label' => array(
                'value' => "Codice Serie",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SERIECODICE',
            'name' => $this->nameForm . '_SERIECODICE',
            'type' => 'text',
            'class' => 'ita-edit-lookup ita-edit-onchange',
            'size' => '8',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Descrizione",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_DESCSERIE',
            'name' => $this->nameForm . '_DESCSERIE',
            'type' => 'text',
            'class' => 'ita-readonly',
            'size' => '35',
            'value' => ''
        );
        return $valori;
    }

    private function DecodificaRuoloGenerico($Codice, $Tipo = 'rowid') {
        $anaruoli_rec = $this->proLib->getAnaruoli($Codice, $Tipo);
        if ($anaruoli_rec) {
            Out::valore($this->nameForm . '_RUOLOCOD', $anaruoli_rec['RUOCOD']);
            Out::valore($this->nameForm . '_DESCRUOLO', $anaruoli_rec['RUODES']);
        } else {
            Out::msgStop("Attenzione", "Ruolo non inesistente.");
            Out::valore($this->nameForm . '_RUOLOCOD', '');
            Out::valore($this->nameForm . '_DESCRUOLO', '');
        }
    }

    private function DecodificaSerie($Codice, $Tipo = 'rowid') {
        $anaseriearc_rec = $this->proLib->getAnaseriearc($Codice, $Tipo);
        if ($anaseriearc_rec) {
            Out::valore($this->nameForm . '_SERIECODICE', $anaseriearc_rec['CODICE']);
            Out::valore($this->nameForm . '_DESCSERIE', $anaseriearc_rec['DESCRIZIONE']);
        } else {
            Out::msgStop("Attenzione", "Serie non inesistente.");
            Out::valore($this->nameForm . '_SERIEOCOD', '');
            Out::valore($this->nameForm . '_DESCSERIE', '');
        }
    }

    private function CaricaRuoli($CodiceSogg) {
        $ElencoRuoli = $this->proLib->GetRuoliSoggetto($CodiceSogg);
        $this->CaricaGriglia($this->gridRuoli, $ElencoRuoli);
    }

    private function CaricaSerie($CodiceSogg) {
        $ElencoSerie = $this->proLib->GetSerieSoggetto($CodiceSogg);
        $this->CaricaGriglia($this->gridSerie, $ElencoSerie);
    }

    private function AggiungiRuoloSoggetto() {
        $Anamed_rec = $_POST[$this->nameForm . '_ANAMED'];
        $CodiceRuolo = $_POST[$this->nameForm . '_RUOLOCOD'];
        if ($CodiceRuolo) {
            /*
             * 1. Controllo Ruolo Valido
             */
            $anaruoli_rec = $this->proLib->getAnaruoli($CodiceRuolo, 'codice');
            if (!$anaruoli_rec) {
                Out::msgStop("Attenzione", "Codice Ruolo non valido.");
                return false;
            }

            /*
             * 2. Controllo  se ruolo già presente
             */
            if ($Anamed_rec['MEDCOD']) {
                $ElencoRuoli = $this->proLib->GetRuoliSoggetto($Anamed_rec['MEDCOD']);
                // Se gia presente blocca.                
                $Presente = false;
                foreach ($ElencoRuoli as $SingoloRuolo) {
                    if ($CodiceRuolo == $SingoloRuolo['RUOCOD']) {
                        $Presente = true;
                        break;
                    }
                }
                if ($Presente) {
                    Out::msgStop("Attenzione", "Codice Ruolo già presente per il soggetto indicato.");
                    return;
                }
            }

            /*
             * 3. Aggiungo:
             */
            $MedRuoli = array();
            $MedRuoli['MEDCOD'] = $Anamed_rec['MEDCOD'];
            $MedRuoli['RUOCOD'] = $CodiceRuolo;

            try {
                ItaDB::DBInsert($this->PROT_DB, 'MEDRUOLI', 'ID', $MedRuoli);
            } catch (Exception $exc) {
                Out::msgStop("Errore db", $exc->getMessage());
                return false;
            }
        }

        $this->CaricaRuoli($Anamed_rec['MEDCOD']);
    }

    private function AggiungiSerieSoggetto() {
        $Anamed_rec = $_POST[$this->nameForm . '_ANAMED'];
        $CodiceSerie = $_POST[$this->nameForm . '_SERIECODICE'];
        if ($CodiceSerie) {
            /*
             * 1. Controllo Serie Valida
             */
            $anaseriearc_erc = $this->proLib->getAnaseriearc($CodiceSerie);
            if (!$anaseriearc_erc) {
                Out::msgStop("Attenzione", "Codice Serie non valida.");
                return false;
            }

            /*
             * 2. Controllo se serie già presente
             */
            if ($Anamed_rec['MEDCOD']) {
                $ElencoSerie = $this->proLib->GetSerieSoggetto($Anamed_rec['MEDCOD']);
                // Se gia presente blocca.                
                $Presente = false;
                foreach ($ElencoSerie as $SingolaSerie) {
                    if ($CodiceSerie == $SingolaSerie['SERIECODICE']) {
                        $Presente = true;
                        break;
                    }
                }
                if ($Presente) {
                    Out::msgStop("Attenzione", "Codice Serie già presente per il soggetto indicato.");
                    return;
                }
            }

            /*
             * 3. Aggiungo:
             */
            $MedSerie = array();
            $MedSerie['MEDCOD'] = $Anamed_rec['MEDCOD'];
            $MedSerie['SERIECODICE'] = $CodiceSerie;

            try {
                ItaDB::DBInsert($this->PROT_DB, 'MEDSERIE', 'ROW_ID', $MedSerie);
            } catch (Exception $exc) {
                Out::msgStop("Errore db", $exc->getMessage());
                return false;
            }
        }

        $this->CaricaSerie($Anamed_rec['MEDCOD']);
    }

    private function CancellaRuoloSoggetto() {
        $rowid = $this->formData[$this->gridRuoli]['gridParam']['selarrrow'];
        $MedRuoli_rec = $this->proLib->GetMedRuoli($rowid);
        if ($MedRuoli_rec) {
            $delete_Info = "Cancellazione Ruolo: " . $MedRuoli_rec['RUOCOD'] . ' Soggetto: ' . $MedRuoli_rec['MEDCOD'];
            if (!$this->deleteRecord($this->PROT_DB, 'MEDRUOLI', $MedRuoli_rec['ID'], $delete_Info, 'ID')) {
                return false;
            } else {
                $this->CaricaRuoli($MedRuoli_rec['MEDCOD']);
            }
        }
    }

    private function CancellaSerieSoggetto() {
        $rowid = $this->formData[$this->gridSerie]['gridParam']['selarrrow'];
        $SerieRuoli_rec = $this->proLib->GetMedSerie($rowid);
        if ($SerieRuoli_rec) {
            $delete_Info = "Cancellazione Serie: " . $SerieRuoli_rec['SERIECODICE'] . ' Soggetto: ' . $SerieRuoli_rec['MEDCOD'];
            if (!$this->deleteRecord($this->PROT_DB, 'MEDSERIE', $SerieRuoli_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                return false;
            } else {
                $this->CaricaSerie($SerieRuoli_rec['MEDCOD']);
            }
        }
    }

}
