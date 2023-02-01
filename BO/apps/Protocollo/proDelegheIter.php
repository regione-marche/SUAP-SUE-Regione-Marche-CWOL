<?php

/**
 *
 * DELEGHE ITER
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  Italsoft srl
 * @license
 * @version    01.02.2020
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';

function proDelegheIter() {
    $proDelegheIter = new proDelegheIter();
    $proDelegheIter->parseEvent();
    return;
}

class proDelegheIter extends itaModel {

    /**
     *
     * @var Ita_DB
     */
    public $PROT_DB;
    public $proLib;
    public $accLib;
    public $itaDate;
    public $nameForm = "proDelegheIter";
    public $divGes = "proDelegheIter_divGestione";
    public $divRis = "proDelegheIter_divRisultato";
    public $gridDeleghe = 'proDelegheIter_gridDeleghe';
    public $delegante;
    public $AdminDeleghe = false;
    public $delegheScrivania = false;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->accLib = new accLib();
            $this->itaDate = new itaDate();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->delegante = App::$utente->getKey($this->nameForm . '_delegante');
            $this->AdminDeleghe = App::$utente->getKey($this->nameForm . '_AdminDeleghe');
            $this->delegheScrivania = App::$utente->getKey($this->nameForm . '_delegheScrivania');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_delegante', $this->delegante);
            App::$utente->setKey($this->nameForm . '_AdminDeleghe', $this->AdminDeleghe);
            App::$utente->setKey($this->nameForm . '_delegheScrivania', $this->delegheScrivania);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaParametri();
                $this->CreaCombo();
                $this->AdminDeleghe = false;
                if ($_POST['Admin']) {
                    $this->AdminDeleghe = true;
                    Out::setAppTitle($this->nameForm, 'Amministrazione Deleghe Iter');
                    Out::setGridCaption($this->gridDeleghe, 'Elenco di tutte le Deleghe ');
                }
                if ($this->delegheScrivania) {
                    Out::show($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                    Out::show($this->nameForm . '_spanAvvisoDeleghe');
                } else {
                    Out::hide($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                    Out::hide($this->nameForm . '_spanAvvisoDeleghe');
                }
                $this->identificaDelegante();
                $this->OpenRicerca();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDeleghe:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;

            case 'printTableToHTML':
                $ParametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->PROT_DB, 'proDelegheIter', $parameters);
                break;

            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDeleghe, array(
                    'sqlDB' => $this->PROT_DB,
                    'sqlQuery' => $sql));
                $ita_grid01->exportXLS('', 'proDelegheIter.xls');
                break;

            case 'onClickTablePager':
                $sql = $this->CreaSql();
                $Result_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                $Result_tab = $this->Elabora($Result_tab);
                $ita_grid01 = new TableView($_POST['id'], array(
                    'arrayTable' => $Result_tab,
                ));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST[$_POST['id']]['gridParam']['rowNum']);
                $ita_grid01->setSortIndex('DELEINIVAL');
                $ita_grid01->setSortOrder('DESC');
                $ita_grid01->getDataPage('json');
                TableView::enableEvents($_POST['id']);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Inserisci':
                        if (!$this->CtrAutorizzazioneProt()) {
                            break;
                        }
                        $delegheiter_rec = $_POST[$this->nameForm . '_DELEGHEITER'];
                        $delegheiter_rec['DELETIMEADD'] = $this->itaDate->GetItaDateTime();
                        $delegheiter_rec['DELEUTEADD'] = App::$utente->getKey('nomeUtente');
                        $ProAut = '';
                        if ($_POST[$this->nameForm . "_Propre1"]) {
                            $ProAut = $_POST[$this->nameForm . "_Propre2"] * 1000000 + $_POST[$this->nameForm . "_Propre1"];
                        }
                        $delegheiter_rec['DELEPROTNUM'] = $ProAut;
                        if (!$this->checkEsistenzaProtAut($delegheiter_rec)) {
                            break;
                        }
                        if (!$this->delegheScrivania) {
                            $delegheiter_rec['DELESCRIVANIA'] = '0';
                        }
                        $esito = $this->controllaDati($delegheiter_rec, 'Inserisci');
                        if ($esito['status'] !== false) {
                            if (!$this->insertRecord($this->PROT_DB, 'DELEGHEITER', $delegheiter_rec, '')) {
                                Out::msgStop('ATTENZIONE', 'Inserimento delega non terminato.');
                            } else {
                                /*
                                 * Notifica della delega.
                                 */
                                $RowidDeleg = $this->PROT_DB->getLastId();
                                $Deleghe_rec = $this->proLib->GetDelegheIter($RowidDeleg, 'rowid');
                                $this->NotificaDelega($delegheiter_rec);
                                $this->OpenRicerca();
                            }
                        } else {
                            $this->messaggio($esito);
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        if (!$this->CtrAutorizzazioneProt()) {
                            break;
                        }
                        $delegheiter_rec = $_POST[$this->nameForm . '_DELEGHEITER'];
                        $delegheiter_rec['DELETIMEEDIT'] = $this->itaDate->GetItaDateTime();
                        $delegheiter_rec['DELEUTEEDIT'] = App::$utente->getKey('nomeUtente');
                        $ProAut = '';
                        if ($_POST[$this->nameForm . "_Propre1"]) {
                            $ProAut = $_POST[$this->nameForm . "_Propre2"] * 1000000 + $_POST[$this->nameForm . "_Propre1"];
                        }
                        $delegheiter_rec['DELEPROTNUM'] = $ProAut;
                        if (!$this->checkEsistenzaProtAut($delegheiter_rec)) {
                            break;
                        }
                        $esito = $this->controllaDati($delegheiter_rec, 'Aggiorna');
                        if ($esito['status'] !== false) {
                            if (!$this->UpdateRecord($this->PROT_DB, 'DELEGHEITER', $delegheiter_rec, '')) {
                                Out::msgStop('ATTENZIONE', 'Aggiornamento delega non terminato.');
                            } else {
                                $Deleghe_rec = $this->proLib->GetDelegheIter($delegheiter_rec['ROWID'], 'rowid');
                                $this->NotificaDelega($Deleghe_rec);
                                $this->OpenRicerca();
                            }
                        } else {
                            $this->messaggio($esito);
                        }
                        break;

                    case $this->nameForm . '_Nuovo':
                        if ($this->AdminDeleghe !== true) {
                            Out::msgInput(
                                    'Selezione Ufficio', array(
                                array(
                                    'label' => array('style' => "width:60px;", 'value' => 'Utente'),
                                    'id' => $this->nameForm . '_UTENTEATTUALE',
                                    'name' => $this->nameForm . '_UTENTEATTUALE',
                                    'width' => '50',
                                    'type' => 'text',
                                    'value' => App::$utente->getKey('nomeUtente'),
                                    'class' => "ita-readonly",
                                    'size' => '10',
                                    'maxchars' => '30'),
                                array(
                                    'label' => array('style' => "width:60px;", 'value' => 'Ufficio'),
                                    'id' => $this->nameForm . '_ANAPRO[PROUOF]',
                                    'name' => $this->nameForm . '_ANAPRO[PROUOF]',
                                    'type' => 'select',
                                    'class' => "ita-select",
                                    'width' => '40',
                                )), array(
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNuovo', 'model' => $this->nameForm, "shortCut" => "f5")
                                    ), $this->nameForm
                            );
                            Out::valori($this->nameForm . '_ANAPRO[PROUOF]', $this->proLib->caricaUof($this), App::$utente->getKey('nomeUtente'));
                        } else {
                            Out::msgInput(
                                    'Selezione Utente', array(
                                array(
                                    'label' => array('style' => "width:60px;", 'value' => 'Utente'),
                                    'id' => $this->nameForm . '_UTENTEATTUALE',
                                    'name' => $this->nameForm . '_UTENTEATTUALE',
                                    'width' => '50',
                                    'type' => 'text',
                                    'value' => App::$utente->getKey('nomeUtente'),
                                    'class' => "ita-edit",
                                    'size' => '10',
                                    'maxchars' => '20'),
                                    ), array(
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaUfficio', 'model' => $this->nameForm, "shortCut" => "f5")
                                    ), $this->nameForm
                            );
                        }
                        break;

                    case $this->nameForm . '_ConfermaUfficio':
                        include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
                        $accLib = new accLib();
                        $utenti_rec = $accLib->GetUtenti($_POST[$this->nameForm . '_UTENTEATTUALE'], 'utelog');
                        if (!$utenti_rec) {
                            Out::msgInfo("ATTENZIONE", "Utente non valido");
                            break;
                        }
                        $this->identificaDelegante($_POST[$this->nameForm . '_UTENTEATTUALE']);

                        Out::msgInput(
                                'Selezione Ufficio', array(
                            array(
                                'label' => array('style' => "width:60px;", 'value' => 'Utente'),
                                'id' => $this->nameForm . '_UTENTEATTUALE',
                                'name' => $this->nameForm . '_UTENTEATTUALE',
                                'width' => '50',
                                'type' => 'text',
                                'value' => $_POST[$this->nameForm . '_UTENTEATTUALE'],
                                'class' => "ita-readonly",
                                'size' => '10',
                                'maxchars' => '30'),
                            array(
                                'label' => array('style' => "width:60px;", 'value' => 'Ufficio'),
                                'id' => $this->nameForm . '_ANAPRO[PROUOF]',
                                'name' => $this->nameForm . '_ANAPRO[PROUOF]',
                                'type' => 'select',
                                'class' => "ita-select",
                                'width' => '40',
                            )), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaNuovo', 'model' => $this->nameForm, "shortCut" => "f5")
                                ), $this->nameForm
                        );

                        Out::valori($this->nameForm . '_ANAPRO[PROUOF]', $this->proLib->caricaUof($this, $utenti_rec['UTECOD']));
                        break;

                    case $this->nameForm . '_ConfermaNuovo':
                        $this->Nascondi();
                        Out::valore($this->nameForm . '_Utente', App::$utente->getKey('nomeUtente'));
                        Out::valore($this->nameForm . '_DELEGHEITER[DELESRCCOD]', $this->delegante);
                        $anamedSrc_rec = $this->proLib->GetAnamed($this->delegante, 'codice', 'si');
                        Out::valore($this->nameForm . "_DecoSrcCod", $anamedSrc_rec["MEDNOM"]);

                        $anauff_Rec = $this->proLib->GetAnauff(($_POST[$this->nameForm . '_ANAPRO']['PROUOF']));
                        //Out::valore($this->nameForm . '_Ufficio', $anauff_Rec['UFFDES']);
                        Out::valore($this->nameForm . '_DecoSrcUff', $anauff_Rec['UFFDES']);
                        if ($this->delegheScrivania) {
                            Out::show($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                            Out::show($this->nameForm . '_spanAvvisoDeleghe');
                        }
                        Out::valore($this->nameForm . '_DELEGHEITER[ROWID]', '');
                        Out::valore($this->nameForm . '_DELEGHEITER[DELESRCUFF]', $anauff_Rec['UFFCOD']);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        Out::show($this->nameForm . '_TornaElenco');
                        Out::show($this->nameForm . '_Inserisci');
                        $this->AbilitaDisabilitaCampi('abilita');
                        break;

                    case $this->nameForm . '_TornaElenco':
                        $this->Nascondi();
                        Out::show($this->divRis);
                        Out::hide($this->divGes);
                        Out::show($this->nameForm . '_Nuovo');
                        TableView::clearGrid($this->gridDeleghe);
                        TableView::enableEvents($this->gridDeleghe);
                        TableView::reload($this->gridDeleghe);
                        $this->AzzeraVariabili();
                        break;

                    case $this->nameForm . '_Annulla':
                        Out::msgInput(
                                'Annullamento Delega', array('label' => 'Inserisci la data di Annullamento    ',
                            'id' => $this->nameForm . '_dataAnnullamento',
                            'name' => $this->nameForm . '_dataAnnullamento',
                            'type' => 'type',
                            'class' => 'ita-datepicker',
                            'size' => '10',
                            'maxchars' => '10'), array('F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnnullamento', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm . "_divGestione"
                        );
                        break;

                    case $this->nameForm . '_ConfermaAnnullamento':
                        $DataAnnullamento = $_POST[$this->nameForm . '_dataAnnullamento'];
                        $delegheiter_rec = $_POST[$this->nameForm . '_DELEGHEITER'];
                        $delegheiter_rec['DELEDATEANN'] = $DataAnnullamento;
                        $delegheiter_rec['DELETIMEEDIT'] = $this->itaDate->GetItaDateTime();
                        $delegheiter_rec['DELEUTEEDIT'] = App::$utente->getKey('nomeUtente');
                        $esito = $this->controllaDati($delegheiter_rec, 'Aggiorna');
                        if ($esito['status'] !== false) {
                            if (!$this->UpdateRecord($this->PROT_DB, 'DELEGHEITER', $delegheiter_rec, '')) {
                                Out::msgStop('ATTENZIONE', 'Aggiornamento delega non terminato.');
                            } else {
                                $Deleghe_rec = $this->proLib->GetDelegheIter($delegheiter_rec['ROWID'], 'rowid');
                                $Anamed_rec = $this->proLib->GetAnamed($Deleghe_rec['DELESRCCOD'], 'codice');
                                $Anauff_rec = $this->proLib->GetAnauff($Deleghe_rec['DELESRCUFF'], 'codice');
                                $oggetto = 'ANNULLAMENTO DELEGA DEL PROTOCOLLO';
                                $testo = "La delega dell'utente " . $Anamed_rec['MEDNOM'] . " per l'ufficio " . $Anauff_rec['UFFDES'] . " è stata Annullata.\n";
                                $testo .= $Deleghe_rec['DELENOTE'];
                                $this->NotificaDelega($Deleghe_rec, $oggetto, $testo);
                                $this->OpenRicerca();
                            }
                        } else {
                            $this->messaggio($esito);
                        }
                        break;

                    case $this->nameForm . '_DELEGHEITER[DELEDSTCOD]_butt':
                        $where = "LEFT OUTER JOIN UFFDES ON ANAMED.MEDCOD=UFFDES.UFFKEY
                                  LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD
                                  WHERE MEDUFF " . $this->proLib->getPROTDB()->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $where, '', '', 'returnanamedFirmatario', " GROUP BY ANAMED.MEDCOD");
                        break;

                    case $this->nameForm . '_UffFirmatario_butt':
                        $codice = ($_POST[$this->nameForm . '_DELEGHEITER']['DELEDSTCOD']);
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if ($anamed_rec) {
                                proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            }
                        }
                        break;

                    case $this->nameForm . '_DELEGHEITER[DELEPROTPAR]_butt':
                        $where = " ( PROPAR ='C' OR PROPAR ='A' OR PROPAR ='P') AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);
                        $data = date('Ymd');
                        $newdata = date('Ymd', strtotime('-30 day', strtotime($data)));
                        $where .= " AND ANAPRO.PRODAR BETWEEN '" . $newdata . "' AND '" . $data . "'";
                        proRic::proRicNumAntecedenti($this->nameForm, $where);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnanamedFirmatario':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTCOD]', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DecoFirmatario", $anamed_rec["MEDNOM"]);
                break;

            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTUFF]', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UffFirmatario', $anauff_rec['UFFDES']);
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DELEGHEITER[DELEINIVAL]':
                        $esito = $this->controllaDataInizio();
                        if ($esito === false) {
                            Out::MsgInfo('ATTENZIONE', 'La data di inizio validità deve essere maggiore o uguale ad oggi.');
                            Out::setFocus('', $this->nameForm . "_DELEGHEITER[DELEINIVAL]");
                        }
                        break;
                    case $this->nameForm . '_DELEGHEITER[DELEDSTCOD]':
                        $codice = $_POST[$this->nameForm . '_DELEGHEITER']['DELEDSTCOD'];
                        $ufficio = $_POST[$this->nameForm . '_DELEGHEITER']['DELEDSTUFF'];
                        if (is_numeric($codice)) {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        }
                        $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                        if (!$anamed_rec) {
                            Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTCOD]', '');
                            Out::valore($this->nameForm . '_DecoFirmatario', '');
                            Out::setFocus('', $this->nameForm . "_DecoFirmatario");
                            break;
                        } else {
                            Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTCOD]', $anamed_rec['MEDCOD']);
                            Out::valore($this->nameForm . '_DecoFirmatario', $anamed_rec['MEDNOM']);
                            $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                            if (count($uffdes_tab) == 1) {
                                $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTCOD]', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_DecoFirmatario', $anauff_rec['UFFDES']);
                            } else {
                                if ($ufficio == '') {
                                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                                }
                            }
                        }
                        break;

                    case $this->nameForm . '_Propre1':
                        if (trim($_POST[$this->nameForm . '_Propre1']) != '') {
                            $codice = $_POST[$this->nameForm . '_Propre1'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . "_Propre1", $codice);
                            $this->ApriRicercaProtoCollegato();
                        }
                        break;
                    case $this->nameForm . '_Propre2':
                        $this->ApriRicercaProtoCollegato();
                        break;
                }
                break;

            case 'returnNumAnte':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                $this->DecodificaProtoCollegato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                break;

            case 'returnRicProtoCollegato':
                $rowid = $_POST['rowData']['ROWID'];
                $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                $this->DecodificaProtoCollegato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DELEGHEITER[DELEDSTCOD]':
                        Out::valore($this->nameForm . '_DELEGHEITER[DELEDSTUFF]', '');
                        Out::valore($this->nameForm . '_UffFirmatario', '');
                        break;
                    case $this->nameForm . '_DELEGHEITER[DELEFUNZIONE]':
                        if ($_POST[$this->nameForm . '_DELEGHEITER']['DELEFUNZIONE'] == 1) {
                            Out::hide($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                            Out::hide($this->nameForm . '_spanAvvisoDeleghe');
                            Out::valore($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]', 0);
                        } else {
                            if ($this->delegheScrivania) {
                                Out::show($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                                Out::show($this->nameForm . '_spanAvvisoDeleghe');
                            }
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_delegante');
        App::$utente->removeKey($this->nameForm . '_AdminDeleghe');
        App::$utente->removeKey($this->nameForm . '_delegheScrivania');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function OpenRicerca() {
        Out::hide($this->divRic);
        Out::show($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        TableView::clearGrid($this->gridDeleghe);
        TableView::enableEvents($this->gridDeleghe);
        TableView::reload($this->gridDeleghe);
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Inserisci');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Annulla');
        Out::hide($this->nameForm . '_divAnnullato');
    }

    private function CreaSql() {
        if ($this->AdminDeleghe === true) {
            $sql = "SELECT * FROM DELEGHEITER WHERE 1 ";
        } else {
            $sql = "SELECT * FROM DELEGHEITER WHERE DELESRCCOD  = '" . $this->delegante . "'";
        }
        return $sql;
    }

    private function Dettaglio($rowid) {
        $this->Nascondi();
        $delegeiter_rec = $this->proLib->GetDelegheIter($rowid);
        $this->delegante = $delegeiter_rec['DELESRCCOD'];

        Out::valori($delegeiter_rec, $this->nameForm . '_DELEGHEITER');
        Out::valore($this->nameForm . '_Utente', $delegeiter_rec['DELEUTEADD']);
        Out::valore($this->nameForm . '_DataInserimento', date("d/m/Y H:i:s", strtotime($delegeiter_rec['DELETIMEADD'])));
        Out::valore($this->nameForm . '_UtenteModifica', $delegeiter_rec['DELEUTEEDIT']);
        Out::valore($this->nameForm . '_DataModifica', date("d/m/Y H:i:s", strtotime($delegeiter_rec['DELETIMEEDIT'])));

        $anamedSrc_rec = $this->proLib->GetAnamed($delegeiter_rec['DELESRCCOD'], 'codice', 'si');
        Out::valore($this->nameForm . "_DecoSrcCod", $anamedSrc_rec["MEDNOM"]);

        $anauffSrc_Rec = $this->proLib->GetAnauff($delegeiter_rec['DELESRCUFF']);
        Out::valore($this->nameForm . '_Ufficio', $anauffSrc_Rec['UFFDES']);
        Out::valore($this->nameForm . '_DecoSrcUff', $anauffSrc_Rec['UFFDES']);
        Out::valore($this->nameForm . '_DELEGHEITER[DELESRCUFF]', $anauffSrc_Rec['UFFCOD']);

        $anamed_rec = $this->proLib->GetAnamed($delegeiter_rec['DELEDSTCOD'], 'codice', 'si');
        Out::valore($this->nameForm . "_DecoFirmatario", $anamed_rec["MEDNOM"]);

        $anauff_rec = $this->proLib->GetAnauff($delegeiter_rec['DELEDSTUFF'], 'codice');
        Out::valore($this->nameForm . '_UffFirmatario', $anauff_rec['UFFDES']);

        if ($delegeiter_rec['DELEDATEANN']) {
            $annullato = substr($delegeiter_rec['DELEDATEANN'], 6, 2) . '/' . substr($delegeiter_rec['DELEDATEANN'], 4, 2) . '/' . substr($delegeiter_rec['DELEDATEANN'], 0, 4);
            $contenuto = "<span style=\"color: red; text-shadow: 1px 1px 1px #000; \"> <b><font size=\"3px\">Delega annullata il   </font> </b></span><b><font size=\"3px\">$annullato</font> </b>";
            Out::html($this->nameForm . '_divAnnullato', $contenuto);
            Out::show($this->nameForm . '_divAnnullato');
        } else {
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_Annulla');
        }


        if ($delegeiter_rec['DELEPROTNUM'] && $delegeiter_rec['DELEPROTPAR']) {
            $AnaproAut_rec = $this->proLib->GetAnapro($delegeiter_rec['DELEPROTNUM'], 'codice', $delegeiter_rec['DELEPROTPAR']);
            if (!$AnaproAut_rec) {
                Out::msgInfo('Attenzione', 'Protocollo autorizzazione non trovato tra i protocolli.');
            }
            Out::valore($this->nameForm . '_Propre1', substr($delegeiter_rec['DELEPROTNUM'], 4));
            Out::valore($this->nameForm . '_Propre2', substr($delegeiter_rec['DELEPROTNUM'], 0, 4));
            Out::valore($this->nameForm . '_DELEGHEITER[DELEPROTPAR]', $delegeiter_rec['DELEPROTPAR']);
        }

        if ($delegeiter_rec['DELEFUNZIONE'] == 1) {
            Out::hide($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
            Out::hide($this->nameForm . '_spanAvvisoDeleghe');
        } else {
            if ($this->delegheScrivania) {
                Out::show($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]_field');
                Out::show($this->nameForm . '_spanAvvisoDeleghe');
            }
        }
        $this->AbilitaDisabilitaCampi('disabilita');
        Out::show($this->nameForm . '_TornaElenco');
        Out::hide($this->divRis);
        Out::show($this->divGes);
        TableView::disableEvents($this->gridDeleghe);
    }

    private function Elabora($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $anamed_rec = $this->proLib->GetAnamed($Result_rec['DELESRCCOD'], 'codice', 'si');
            $Result_tab[$key]['DELESRCCOD'] = $anamed_rec['MEDNOM'];

            $anauff_Rec = $this->proLib->GetAnauff($Result_rec['DELESRCUFF']);
            $Result_tab[$key]['DELESRCUFF'] = $anauff_Rec['UFFDES'];

            $anamed_rec = $this->proLib->GetAnamed($Result_rec['DELEDSTCOD'], 'codice', 'si');
            $Result_tab[$key]['DELEDSTCOD'] = $anamed_rec['MEDNOM'];

            $anauff_rec = $this->proLib->GetAnauff($Result_rec['DELEDSTUFF'], 'codice');
            $Result_tab[$key]['DELEDSTUFF'] = $anauff_rec['UFFDES'];
        }
        return $Result_tab;
    }

    private function identificaDelegante($Utente = null) {
        if ($Utente == null) {
            $utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('nomeUtente'), 'utelog');
        } else {
            $utenti_rec = $this->accLib->GetUtenti($Utente, 'utelog');
        }
        $this->delegante = $utenti_rec['UTEANA__1'];
    }

    private function caricaParametri() {
        $anaent_61 = $this->proLib->GetAnaent('61');
        $this->delegheScrivania = $anaent_61['ENTDE1'];
    }

    private function controllaDati($delegheiter_rec, $daDove) {
        $retArray = array(
            'status' => true,
            'msg' => ''
        );

        if ($delegheiter_rec['DELESRCCOD'] == '' || $delegheiter_rec['DELESRCUFF'] == '') {
            $retArray['status'] = false;
            $retArray['msg'] = "Non sono stati indicati il codice del delegante e/o relativo ufficio.";
            return $retArray;
        }
        if ($delegheiter_rec['DELEDSTCOD'] == '' || $delegheiter_rec['DELEDSTUFF'] == '') {
            $retArray['status'] = false;
            $retArray['msg'] = "Non sono stati indicati il codice del delegato e/o relativo ufficio.";
            return $retArray;
        }
        if ($delegheiter_rec['DELEINIVAL'] == '' || $delegheiter_rec['DELEFINVAL'] == '') {
            $retArray['status'] = false;
            $retArray['msg'] = "Non sono stati indicate le date di inizio e fine validità.";
            return $retArray;
        }
        if ($delegheiter_rec['DELEINIVAL'] > $delegheiter_rec['DELEFINVAL']) {
            $retArray['status'] = false;
            $retArray['msg'] = "La data di inizio validità è maggiore della data di fine validità.";
            return $retArray;
        }

        if ($daDove == 'Inserisci') {
            $ctrDataInizio = $this->controllaDataInizio();
            if ($ctrDataInizio === false) {
                $retArray['status'] = false;
                $retArray['msg'] = "La data di inizio validità è minore della data odierna.";
                return $retArray;
            }
        }
        $uffdes_tab = $this->GetUfficiAnamed($delegheiter_rec['DELEDSTCOD']);
        $trovato = false;
        foreach ($uffdes_tab as $ufficio) {
            if ($ufficio['UFFCOD'] == $delegheiter_rec['DELEDSTUFF']) {
                $trovato = true;
                break;
            }
        }

        if (!$trovato) {
            $retArray['status'] = false;
            $retArray['msg'] = "Il delegato non fa parte dell'ufficio indicato.";
            return $retArray;
        }
        return $retArray;
    }

    private function controllaDataInizio() {
        $delegheiter_rec = $_POST[$this->nameForm . '_DELEGHEITER'];
        if ($delegheiter_rec['DELEINIVAL'] < date('Ymd')) {
            return false;
        }
        return true;
    }

    private function sovrapposizionePeriodi($delegheiter_rec) {
        $retArray = array();
        $dal = $delegheiter_rec['DELEINIVAL'];
        $al = $delegheiter_rec['DELEFINVAL'];
        $sql = "SELECT * FROM DELEGHEITER WHERE DELESRCCOD  = '" . $this->delegante . "' AND DELESRCUFF = '" . $delegheiter_rec['DELESRCUFF'] . "' AND DELEDATEANN = '' AND";
        $sql .= " ((DELEINIVAL <= $dal AND DELEFINVAL >= $dal) OR";
        $sql .= " (DELEINIVAL >= $dal AND DELEFINVAL <= $al) OR";
        $sql .= " (DELEINIVAL <= $al AND DELEFINVAL >= $al))";
        if ($delegheiter_rec['ROWID']) {
            $sql .= " AND ROWID <> " . $delegheiter_rec['ROWID'];
        }
        $sql .= " ORDER BY DELEINIVAL";
        $Periodi_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        $retArray['Periodi_tab'] = $Periodi_tab;
        $retArray['count_periodi'] = count($Periodi_tab);
        return $retArray;
    }

    private function messaggio($esito) {
        $msg = '';
        $msg = $esito['msg'];
        if ($esito['periodi']['count_periodi']) {
            $this->mostraSovrapposizione($esito, $msg);
        } else {
            Out::msgInfo('ATTENZIONE', $msg);
        }
    }

    private function mostraSovrapposizione($esito, $msg) {
        $model = 'utiRicDiag';
        $colonneNome = array(
            "Dal",
            "Al");
        $colonneModel = array(
            array("name" => 'DELEINIVAL', "width" => 100, "formatter" => "eqdate"),
            array("name" => 'DELEFINVAL', "width" => 100, "formatter" => "eqdate"));
        $gridOptions = array(
            "Caption" => 'Periodi Sovrapposti già presenti',
            "width" => '400',
            "height" => "'80%'",
            "rowNum" => '20',
            "rowList" => '[]',
            "colNames" => $colonneNome,
            "colModel" => $colonneModel,
            "arrayTable" => $esito['periodi']['Periodi_tab']
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnPeriodoSovrapposto';
        $_POST['returnKey'] = '';
        $_POST['msgDetail'] = $msg;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function NotificaDelega($Deleghe_rec, $oggetto = '', $testo = '') {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        $Anamed_rec = $this->proLib->GetAnamed($Deleghe_rec['DELESRCCOD'], 'codice');
        $Anauff_rec = $this->proLib->GetAnauff($Deleghe_rec['DELESRCUFF'], 'codice');
        $DaData = date("d/m/Y", strtotime($Deleghe_rec['DELEINIVAL']));
        $AData = date("d/m/Y", strtotime($Deleghe_rec['DELEFINVAL']));

        $Utenti_rec = $this->proLib->getLoginDaMedcod($Deleghe_rec['DELEDSTCOD']);
        if (!$Utenti_rec) {
            Out::msgInfo('Attenzione', 'Non è stato possibile inviare la notifica al Delegato.');
            return false;
        }
        $uteDest = $Utenti_rec['UTELOG'];
        $OggNota = $oggetto;
        if ($oggetto == '') {
            $OggNota = "NOTIFICA DELEGA PROTOCOLLO.";
        }
        $env_notifiche = array();
        $env_notifiche['OGGETTO'] = $OggNota;
        if (!$testo) {
            $testo = "PERIODO DI DELEGA: \n";
            $testo .= "Dal $DaData al $AData. \n";
            $testo .= "L'Utente " . $Anamed_rec['MEDNOM'] . " ti ha Delegato a gestire i suoi protocolli derivanti dall'ufficio " . $Anauff_rec['UFFDES'] . "\n";
            $testo .= $Deleghe_rec['DELENOTE'];
        }
        $env_notifiche['TESTO'] = $testo;
        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
        $env_notifiche['MODELINS'] = $this->nameForm;
        $env_notifiche['DATAINS'] = date("Ymd");
        $env_notifiche['ORAINS'] = date("H:i:s");
        $env_notifiche['UTEDEST'] = $uteDest;
        $insert_Info = $env_notifiche['UTEDEST'] . ' - Oggetto notifica: ' . $env_notifiche['OGGETTO'] . " ";
        $this->insertRecord($ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $insert_Info);
        return true;
    }

    private function ApriRicercaProtoCollegato() {
        if ($this->consultazione == true) {
            return;
        }
        $Numero = $_POST[$this->nameForm . '_Propre1'];
        $Anno = $_POST[$this->nameForm . '_Propre2'];
        $Tipo = $_POST[$this->nameForm . '_DELEGHEITER']['DELEPROTPAR'];
        if ($Numero && $Anno && $Tipo) {
            return;
        }
        if (!$Numero) {
            return;
        }
        proRic::proRicProtoCollegato($this->nameForm, $Numero, $Anno);
    }

    private function DecodificaProtoCollegato($Codice = '', $Tipo = '') {
        $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $Codice, $Tipo);
        if (!$anapro_rec) {
            Out::msgInfo("Attenzione Protocollo", "Protocollo Autorizzazione non accessibile.");
            Out::valore($this->nameForm . '_Propre1', '');
            Out::valore($this->nameForm . '_Propre2', '');
            Out::valore($this->nameForm . '_DELEGHEITER[DELEPROTPAR]', '');
            Out::setFocus('', $this->nameForm . "_Propre1");
        } else {
            Out::valore($this->nameForm . '_Propre1', substr($anapro_rec['PRONUM'], 4));
            Out::valore($this->nameForm . '_Propre2', substr($anapro_rec['PRONUM'], 0, 4));
            Out::valore($this->nameForm . '_DELEGHEITER[DELEPROTPAR]', $anapro_rec['PROPAR']);
        }
    }

    private function CtrAutorizzazioneProt() {
        if (($_POST[$this->nameForm . '_Propre1'] != '' || $_POST[$this->nameForm . '_Propre2'] != '' || $_POST[$this->nameForm . '_DELEGHEITER']['DELEPROTPAR'] != '') &&
                !($_POST[$this->nameForm . '_Propre1'] != '' && $_POST[$this->nameForm . '_Propre2'] != '' && $_POST[$this->nameForm . '_DELEGHEITER']['DELEPROTPAR'] != '')) {
            Out::msgStop("Attenzione!", "Compilare tutti i campi del protocollo collegato.");
            Out::setFocus('', $this->nameForm . '_Propre1');
            return false;
        }
        return true;
    }

    private function checkEsistenzaProtAut($delegeiter_rec) {
        if ($delegeiter_rec['DELEPROTNUM'] && $delegeiter_rec['DELEPROTPAR']) {
            $AnaproAut_rec = $this->proLib->GetAnapro($delegeiter_rec['DELEPROTNUM'], 'codice', $delegeiter_rec['DELEPROTPAR']);
            if (!$AnaproAut_rec) {
                Out::msgStop('Attenzione', '<div>Protocollo Autorizzazione inesistente.<br>Indicare un protocollo esistente prima di procedere.</div>');
                return false;
            }
        }
        return true;
    }

    private function GetUfficiAnamed($codice) {
        $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
        return $uffdes_tab;
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_DELEGHEITER[DELEFUNZIONE]', 1, '0', '0', 'Solo per Protocollo');
        Out::select($this->nameForm . '_DELEGHEITER[DELEFUNZIONE]', 1, '1', '0', 'Solo per Atti Segreteria');
    }

    private function AbilitaDisabilitaCampi($stato = 'disabilita') {
        if ($stato == 'abilita') {
            Out::enableField($this->nameForm . '_DELEGHEITER[DELEDSTCOD]');
            Out::enableField($this->nameForm . '_DecoFirmatario');
            Out::enableField($this->nameForm . '_UffFirmatario');
            Out::enableField($this->nameForm . '_DELEGHEITER[DELEFUNZIONE]');
            Out::enableField($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]');
            Out::enableField($this->nameForm . '_DELEGHEITER[DELEINIVAL]');
            Out::enableField($this->nameForm . '_DELEGHEITER[DELEFINVAL]');
        } else {
            Out::disableField($this->nameForm . '_DELEGHEITER[DELEDSTCOD]');
            Out::disableField($this->nameForm . '_DecoFirmatario');
            Out::disableField($this->nameForm . '_UffFirmatario');
            Out::disableField($this->nameForm . '_DELEGHEITER[DELEFUNZIONE]');
            Out::disableField($this->nameForm . '_DELEGHEITER[DELESCRIVANIA]');
            Out::disableField($this->nameForm . '_DELEGHEITER[DELEINIVAL]');
            Out::disableField($this->nameForm . '_DELEGHEITER[DELEFINVAL]');
        }
    }

}
