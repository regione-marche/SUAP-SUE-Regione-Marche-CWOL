<?php

/**
 *  * PHP Version 5
 *
 * @category   itaModel
 * @copyright  1987-2015 Italsoft snc
 * @license 
 * @version    27.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

function proMobElencoIter() {
    $proMobElencoIter = new proMobElencoIter();
    $proMobElencoIter->parseEvent();
    return;
}

class proMobElencoIter extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibAllegati;
    public $proLibMail;
    public $nameForm = "proMobElencoIter";
    public $gridProtocollo = "proMobElencoIter_gridProtocollo";
    public $gridFirmatari = "proMobElencoIter_gridFirmatari";
    public $gridAllegati = "proMobElencoIter_gridAllegati";
    public $currArcite = array();
    public $proIterAlle = array();
    public $tipoProt;

    function __construct() {
        parent::__construct();
        try {
            $this->codiceDest = proSoggetto::getCodiceSoggettoFromIdUtente();
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->proLibMail = new proLibMail();
            $this->currArcite = App::$utente->getKey($this->nameForm . '_currArcite');
            $this->proIterAlle = App::$utente->getKey($this->nameForm . '_proIterAlle');
            $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currArcite', $this->currArcite);
            App::$utente->setKey($this->nameForm . '_proIterAlle', $this->proIterAlle);
            App::$utente->setKey($this->nameForm . '_tipoProt', $this->tipoProt);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openRisultato();
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Chiusi':
                    case $this->nameForm . '_Rifiutati':
                    case $this->nameForm . '_DaFirmare':
                        TableView::clearGrid($this->gridProtocollo);
                        TableView::reload($this->gridProtocollo);
                        break;
                }
                break;

            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridProtocollo:
                        $this->openGestione($_POST['rowid']);
                        break;

                    case $this->gridAllegati:
                        $this->EditAllegati();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridProtocollo:
                        $ita_grid01 = new TableView($this->gridProtocollo, array('sqlDB' => $this->PROT_DB, 'sqlQuery' => $this->creaSql()));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPageFromArray('json', $this->elaboraTable($ita_grid01->getDataArray()));
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ritorna':
                        $this->openRisultato();
                        break;
                    case $this->nameForm . '_VaiAllaFirma':
                        $this->VaiAllaFirma();
                        break;

                    case $this->nameForm . '_Chiudi':
                        $this->chiudiIter($_POST[$this->nameForm . '_Annotazioni']);
                        $this->openGestione($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;

                    case $this->nameForm . '_Mail':
                        $this->inviaMail();
                        break;

                    case $this->nameForm . '_Riapri':
                        /**
                         * aggiunto bottone per riaprire un protocollo chiuso
                         */
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $arcite_rec['ITEFIN'] = '';
                        $arcite_rec['ITEFLA'] = '';
                        $arcite_rec['ITEANN'] = 'RIAPERTO';
                        $update_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'];
                        if (!$this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $update_Info)) {
                            Out::msgStop("Attenzione!!!", "Errore nell'aggiornamento ITER. Protocollo n. " . $arcite_rec['ITEPRO'] . " tipo " . $arcite_rec['ITEPAR']);
                        }
                        $this->openGestione($_POST[$this->nameForm . '_ARCITE']['ROWID']);
                        break;

                    case $this->nameForm . '_Rifiuta':
                    case $this->nameForm . '_RifiutaMotivo':
                        if ($_POST[$this->nameForm . '_motivazione'] == '') {
                            $docfirma_check = $this->proLibAllegati->GetDocfirma($this->currArcite['ROWID'], 'rowidarcite');
                            if ($docfirma_check) {
                                $valori[] = array(
                                    'label' => array(
                                        'value' => "Perché rifiuti di firmare gli allegati?",
                                        'style' => 'margin-right: 10px;'
                                    ),
                                    'id' => $this->nameForm . '_motivazione',
                                    'name' => $this->nameForm . '_motivazione',
                                    'type' => 'text',
                                    'style' => 'margin:2px;width:350px;',
                                    'value' => ''
                                );
                            } else {
                                $valori[] = array(
                                    'label' => array(
                                        'value' => "Perché rifiuti la gestione della Trasmissione?",
                                        'style' => 'margin-right: 10px;'
                                    ),
                                    'id' => $this->nameForm . '_motivazione',
                                    'name' => $this->nameForm . '_motivazione',
                                    'type' => 'text',
                                    'style' => 'margin:2px;width:350px;',
                                    'value' => ''
                                );
                            }
                            Out::msgInput('Motivo del Rifiuto.', $valori, array(
                                'Rifiuta' => array(
                                    'id' => $this->nameForm . '_RifiutaMotivo',
                                    'model' => $this->nameForm
                                )
                                    ), $this->nameForm);
                            Out::setFocus('', $this->nameForm . '_motivazione');
                            break;
                        }
                        $iter = proIter::getInstance($this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PRONUM'], $_POST[$this->nameForm . '_ANAPRO']['PROPAR']);
                        $arcite_rec = $this->proLib->GetArcite($_POST[$this->nameForm . '_ARCITE']['ROWID'], 'rowid');
                        $iter->rifiutaIterNode($arcite_rec, $_POST[$this->nameForm . '_motivazione']);
                        $this->openGestione($this->currArcite['ROWID']);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case "returnFromSignAuth";
                if ($_POST['result'] === true) {
                    if ($_POST['returnAllegati']) {
                        foreach ($_POST['returnAllegati'] as $key => $allegato) {
                            if ($allegato['SIGNRESULT'] === 'OK') {
                                $this->salvaDocumentoFirmato($key, $allegato['OUTPUTFILEPATH'], $allegato['INPUTFILEPATH'], $allegato['FILENAMEFIRMATO']);
                            }
                        }
                    } else {
                        $this->salvaDocumentoFirmato($this->appoggio['ROWID'], $_POST['outputFilePath'], $_POST['inputFilePath'], $_POST['fileNameFirmato']);
                    }
                    $this->caricaAllegati($this->currArcite['ITEPRO'], $this->currArcite['ITEPAR'], true);
                    Out::msgBlock('', 2000, true, "Firma Avvenuta con successo");
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;

            case 'returnMail':
                $destinatari = array();
                $destinatariKey = explode(',', $_POST['valori']['Destinatari']);
                $destMap = $_POST['valori']['DestinatariOriginari'];
                foreach ($destinatariKey as $key => $value) {
                    $destinatari[] = $destMap[$value];
                }
                if ($_POST['valori']['Inviata']) {
                    $valori = array(
                        'destMap' => $destinatari,
                        'Oggetto' => $_POST['valori']['Oggetto'],
                        'Corpo' => $_POST['valori']['Corpo'],
                        'allegati' => $_POST['allegati']
                    );
                    $result = $this->proLibMail->servizioInvioMail($this, $valori, $this->currArcite['ITEPRO'], $this->currArcite['ITEPAR'], array(), $this->appoggio['proArriDest'], $this->appoggio['proAltriDestinatari']);
                    if (!$result) {
                        Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                        break;
                    }

                    $this->chiudiIter("Firme effettuate correttamente.");

                    $arcite_pre = $this->proLib->GetArcite($this->currArcite['ITEPRE'], 'itekey');
                    if ($arcite_pre['ITEDES'] != $this->currArcite['ITEDES']) {
                        $oggetto = "Invio mail ai destinatari per il protocollo: " . (int) substr($this->currArcite['ITEPRO'], 4) . " / " . substr($this->currArcite['ITEPRO'], 0, 4) . " - " . $this->currArcite['ITEPAR'];
                        $testo = "Invio avvenuto con successo ai destinatari del protocollo.";
                        $utente = $this->proLib->getLoginDaMedcod($this->currArcite['ITEANT']);
                        if ($utente) {
                            $this->inserisciNotifica($oggetto, $testo, $utente['UTELOG']);
                        }
                    }
                    $this->openGestione($this->currArcite['ROWID']);
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currArcite');
        App::$utente->removeKey($this->nameForm . '_proIterAlle');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons) {
        Out::hide($this->nameForm . '_VaiAllaFirma');
        Out::hide($this->nameForm . '_Ritorna');
        Out::hide($this->nameForm . '_Rifiuta');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Mail');
        Out::hide($this->nameForm . '_Riapri');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    private function creaSql() {
        $sql0 = '';
        switch ($_POST[$this->nameForm . '_OpzioniVisualizzazione']) {
            case 2:
                $sql0 .= " (";
                $sql0 .= " (ARCITE.ITESTATO='1' OR ARCITE.ITEFLA='2')";
                $sql0 .= " AND ARCITE.ITEFIN<>''";
                $sql0 .= " AND ARCITE.ITESUS=''
                           "; //non inviato
                $sql0 .= ")";
                break;
            case 5:
                $sql0 .= " ARCITE.ITESTATO='1'";
                $sql0 .= " AND ARCITE.ITEDATRIF<>''
                          ";
                break;
            case 6:
                $sql0 .= " (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
                $sql0 .= " AND ARCITE.ITEFIN=''"; //non chiuso
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                $sql0 .= " "; //da firmare
                break;
            case 1:
            default:
                $sql0 .= " (ARCITE.ITESTATO='0' OR ARCITE.ITESTATO='2')";
                $sql0 .= " AND ARCITE.ITEFIN=''"; //non chiuso
                $sql0 .= " AND ARCITE.ITESUS=''"; //non inviato
                break;
        }

        $sql0 .= " AND ARCITE.ITETIP='" . proIter::ITETIP_ALLAFIRMA . "' AND ARCITE.ITEGES='1'";

        $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, '', true);
        /*
         * Sql di base per il soggetto.
         */
        $sqlBaseSoggetto = "
            SELECT
                * 
            FROM
                ARCITE
            WHERE
                ARCITE.ITEDES='" . $this->codiceDest . "' AND 
                ARCITE.ITEORGWORKLIV=0 AND
                $sql0 AND
                ARCITE.ITEBASE=0                     
            ";
        /*
         * Sql di base per gli uffici del soggetto
         */
        $sqlBaseUffici = array();
        foreach ($uffdes_tab as $uffdes_rec) {
            $sqlBaseUffici[] = "
            SELECT
                * 
            FROM
                ARCITE
            WHERE
                ARCITE.ITEUFF='{$uffdes_rec['UFFCOD']}' AND 
                ARCITE.ITEORGWORKLIV=1 AND
                ARCITE.ITEDES<>'" . $this->codiceDest . "' AND 
                $sql0 AND
                ARCITE.ITEBASE=0 
            ";
        }
        /*
         * sql base è l'union tra la select per soggetto e uffici.
         */
        $sqlBase = "
            $sqlBaseSoggetto
            UNION ALL
         " . implode(' UNION ALL ', $sqlBaseUffici);
        $oggi = date('Ymd');
        /*
         * Sql principale
         * 

         */
        $sql = "SELECT
                    ARCITE.ROWID AS ROWID,
                    ARCITE.ITEDAT,
                    ARCITE.ITEDES,
                    ARCITE.ITEUFF,
                    ARCITE.ITEPRO,
                    ARCITE.ITEPAR,
                    " . $this->PROT_DB->subString('ARCITE.ITEPRO', 1, 4) . " AS ANNO,
                    " . $this->PROT_DB->subString('ARCITE.ITEPRO', 5, 6) . " AS NUMERO,
                    ARCITE.ITEDLE,
                    ARCITE.ITESTATO,
                    ARCITE.ITEDATACC,
                    ARCITE.ITEGES,
                    ARCITE.ITESUS,
                    ARCITE.ITETERMINE,
                    " . $this->PROT_DB->dateDiff($this->PROT_DB->coalesce($this->PROT_DB->nullIf("ARCITE.ITETERMINE", "''"), "'20681231'"), "'$oggi'") . " AS GIORNITERMINE,                    
                    ARCITE.ITEDATRIF,
                    ARCITE.ITEFLA,
                    ARCITE.ITEEVIDENZA,
                    ARCITE.ITEORGWORKLIV,
                    ARCITE.ITEANN,
                    ARCITE.ITETIP,
                    ARCITE.ITENTRAS,
                    ARCITE.ITENLETT,
                    ARCITEPADRE.ITESTATO AS STATOPADRE,
                    ARCITEPADRE.ITEMOTIVO AS ITEMOTIVOP,
                    ANAOGG.OGGOGG AS OGGETTO,
                    ANAPRO.PRONOM AS PROVENIENZA,
                    ANAPRO.PROCAT,
                    ANAPRO.PROCCA,
                    ANAPRO.PRORISERVA,
                    ANAPRO.PROTSO,
                    ANAPRO.PRONOM,
                    ANAPRO.PROCODTIPODOC,
                    ANADES_FIRMATARIO.DESNOM AS DESNOM_FIRMATARIO,
                    0 AS NDESTPROT
                FROM ($sqlBase) ARCITE
                    LEFT OUTER JOIN ANAOGG ON ARCITE.ITEPRO=ANAOGG.OGGNUM AND ARCITE.ITEPAR=ANAOGG.OGGPAR
                    LEFT OUTER JOIN ANAPRO ON ARCITE.ITEPRO=ANAPRO.PRONUM AND ARCITE.ITEPAR=ANAPRO.PROPAR
                    LEFT OUTER JOIN ARCITE ARCITEPADRE ON ARCITE.ITEPRE = ARCITEPADRE.ITEKEY
                    LEFT OUTER JOIN PROGES PROGES ON PROGES.GESKEY = ANAPRO.PROFASKEY
                    LEFT OUTER JOIN ANADES ANADES_FIRMATARIO FORCE INDEX FOR JOIN (I_DESPAR) ON ANAPRO.PRONUM=ANADES_FIRMATARIO.DESNUM AND ANAPRO.PROPAR=ANADES_FIRMATARIO.DESPAR AND (ANADES_FIRMATARIO.DESPAR = 'C' OR ANADES_FIRMATARIO.DESPAR = 'P') AND ANADES_FIRMATARIO.DESTIPO = 'M'
                    WHERE ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " AND ARCITE.ITEPAR<>'I' ";

        $where = '';
        $utente = proSoggetto::getCodiceSoggettoFromIdUtente();
        $where .= " AND ((ANAPRO.PRORISERVA<>'1' OR ANAPRO.PROTSO<>'1' AND ARCITE.ITEDES<>'$utente') OR ARCITE.ITEDES='$utente')";
        $sql .= $where;

        $sql .= " GROUP BY ARCITE.ROWID ";
        if (!$this->giorniTermineDefault) {
            $this->giorniTermineDefault = '99999999999';
        }
        $sql = "SELECT * FROM ($sql) A WHERE A.GIORNITERMINE <= " . $this->giorniTermineDefault;
        return $sql;
    }

    private function elaboraRecord($arcite_rec) {
        $itedat = $arcite_rec['ITEDAT'];
        $dataAcc = "";
        $record = array();
        $codice = $arcite_rec['ITEPRO'];
        $anapro_rec = $this->proLib->GetAnapro($codice, 'codice', $arcite_rec['ITEPAR']);
        //$anaogg_rec = $this->proLib->GetAnaogg($codice, $arcite_rec['ITEPAR']);
        $record['ROWID'] = $arcite_rec['ROWID'];
        $tooltipitepar = '';
        $iconaDocumento = 'ita-icon-register-document-16x16';
        switch ($arcite_rec['ITEPAR']) {
            case 'P':
                $tooltipitepar = 'PROTOCOLLO IN PARTENZA';
                break;
            case 'C':
                $tooltipitepar = 'DOCUMENTO FORMALE';
                break;
            case 'A':
                $tooltipitepar = 'PROTOCOLLO IN ARRIVO';
                break;
            case 'I':
                $iconaDocumento = 'ita-icon-portrait-16x16';
                $tooltipitepar = 'INDICE DOCUMENTALE';
                break;
        }
        $record['ITEPAR'] = "<span title=\"$tooltipitepar\" class=\"ita-tooltip \" style=\"display:inline-block;vertical-align:middle;\">{$arcite_rec['ITEPAR']}</span>";
        if ($arcite_rec['PROCODTIPODOC'] == 'EFAA' || $arcite_rec['PROCODTIPODOC'] == 'EFAP' || $arcite_rec['PROCODTIPODOC'] == 'SDIA' || $arcite_rec['PROCODTIPODOC'] == 'SDIP') {
            $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:middle;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
        }
        if ($arcite_rec['ITEPAR'] == 'F') {
            $record['ITEPAR'] .= "<span title=\"Tipo {$arcite_rec['ITEPAR']}\" style=\"display:inline-block;\" class=\"ita-tooltip ita-icon ita-icon-open-folder-16x16\"></span>";
        } else if ($arcite_rec['ITEPAR'] == 'N') {
            $subF = substr($anapro_rec['PROSUBKEY'], strpos($anapro_rec['PROSUBKEY'], '-') + 1);
            $record['ITEPAR'] .= "<span style=\"display:inline-block;\" title=\"Tipo " . $arcite_rec['ITEPAR'] . " - " . $subF . "\" class=\"ita-tooltip ita-icon ita-icon-sub-folder-16x16\"></span>";
        } else if ($arcite_rec['ITEPAR'] == 'T') {
            $record['ITEPAR'] .= "<span style=\"display:inline-block;\" title=\"Tipo {$arcite_rec['ITEPAR']}\" class=\"ita-tooltip ita-icon ita-icon-edit-16x16\"></span>";
        } else {
            $itepar_text = " Tipo {$arcite_rec['ITEPAR']} ";
            if ($arcite_rec['ITEANN']) {
                $itepar_text = $arcite_rec['ITEANN'];
                if ($arcite_rec['ITEMOTIVOP']) {
                    $itepar_text .= ' - ' . $arcite_rec['ITEMOTIVOP'];
                }
            }
            $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:middle;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon $iconaDocumento\"></span>";

            if ($arcite_rec['ITETIP'] == proIter::ITETIP_ALLAFIRMA) {
                $profilo = proSoggetto::getProfileFromIdUtente();
                $docfirma_tab = $this->proLibAllegati->GetDocfirma($arcite_rec['ROWID'], 'rowidarcite', true, " AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
                if ($docfirma_tab) {
                    $record['ITEPAR'] .= "<span style=\"display:inline-block;vertical-align:middle;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon ita-icon-sigillo-16x16\"></span>";
                }
//                } else {
//                    $record['ITEPAR'] = "<span style=\"display:inline-block;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon ita-icon-register-document-16x16\"></span><span style=\"display:inline-block;\">{$arcite_rec['ITEPAR']}</span>";
//                }
            } elseif ($arcite_rec['ITETIP'] == proIter::ITETIP_PARERE) {
                $record['ITEPAR'] .= "<span style=\"display:inline-block; vertical-align:middle;\" title=\"$itepar_text\" class=\"ita-tooltip ita-icon ita-icon-comment-new-16x16\"></span>";
                $record['ITEPAR'] .= $this->getPareriColor($arcite_rec);
            }
        }
        $record['OGGETTO'] = $arcite_rec['OGGETTO'];

        if ($arcite_rec['ITEPAR'] == 'F' || $arcite_rec['ITEPAR'] == 'N') {
            $record['NUMERO'] = $anapro_rec['PROFASKEY'];
        } else {
            $record['NUMERO'] = intval(substr($codice, 4)) . ' / ' . substr($codice, 0, 4);
        }

        if ($arcite_rec['ITEDAT']) {
            $itedat = $arcite_rec['ITEDAT'];
        } else {
            $itedat = $anapro_rec['PRODAR'];
        }
        if (!$anapro_rec) {
            return false;
        }
        $ev = "";
        if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100") {
            $ev = "background-color:yellow;";
        }
        if ($arcite_rec["ITEDES"] != proSoggetto::getCodiceSoggettoFromIdUtente()) {
            $ev = "background-color:orange;";
        }

        if ($this->proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $record['PROVENIENZA'] = "<div style=\"background-color:lightgrey;\">RISERVATO</div>";
        } else {
            $record['PROVENIENZA'] = $anapro_rec['PRONOM'];
            if ($arcite_rec['ITEPAR'] == 'C') {
                $record['PROVENIENZA'] = $arcite_rec['DESNOM_FIRMATARIO'];
            }
        }
        $rifiutaImg = "";
        if ($arcite_rec['STATOPADRE'] == 1 || $arcite_rec['STATOPADRE'] == 3) {
            $rifiutaImg = "<span style=\"display:inline-block\" class=\"ita-icon ita-icon-divieto-16x16\"></span>";
        }
        $record['STATO'] = "";
        if ($arcite_rec['ITEDLE'] != '') {
            if ($arcite_rec['ITEGES'] != 1) {
                $dataLet = substr($arcite_rec['ITEDLE'], 6, 2) . "/" . substr($arcite_rec['ITEDLE'], 4, 2) . "/" . substr($arcite_rec['ITEDLE'], 0, 4);
                $record['STATO'] = "<span style=\"display:inline-block\" title=\"Letto in data $dataLet\" class=\"ita-icon ita-icon-apertagray-16x16\"></span>";
            } else {
                if ($arcite_rec['ITESTATO'] == 2) {
                    if ($arcite_rec['ITEDATACC']) {
                        $dataAcc = "in data " . substr($arcite_rec['ITEDATACC'], 6, 2) . "/" . substr($arcite_rec['ITEDATACC'], 4, 2) . "/" . substr($arcite_rec['ITEDATACC'], 0, 4);
                    }
                    $record['STATO'] = "<span style=\"display:inline-block\" title=\"Preso in carico $dataAcc\" class=\"ita-icon ita-icon-mail-green-verify-16x16\"></span>";
                } else {
                    $dataLet = substr($arcite_rec['ITEDLE'], 6, 2) . "/" . substr($arcite_rec['ITEDLE'], 4, 2) . "/" . substr($arcite_rec['ITEDLE'], 0, 4);
                    $record['STATO'] = "<span style=\"display:inline-block\" title=\"Letto in data $dataLet\" class=\"ita-icon ita-icon-apertagreen-16x16\"></span>";
                }
            }
        } else {
            if ($arcite_rec['ITEGES'] != 1) {
                $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-chiusagray-16x16"></span>';
            } else {
                $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-chiusagreen-16x16"></span>';
            }
        }
        if ($arcite_rec['ITESUS'] != '') {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-inoltrata-16x16"></span>';
        }
        if ($arcite_rec['ITETERMINE'] != '' && $arcite_rec['ITETERMINE'] < date("Ymd")) {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-lock-16x16"></span>';
        }
        if ($arcite_rec['ITEDATRIF'] != '') {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-divieto-16x16"></span>';
        }


        if ($arcite_rec['ITEFLA'] == 2) {
            $record['STATO'] = '<span style="display:inline-block" class="ita-icon ita-icon-check-red-16x16"></span>';
        }
        $record['STATO'] .= $rifiutaImg;
        if ($arcite_rec['ITEORGWORKLIV'] == 1) {
            $record['STATO'] .= "<span style=\"display:inline-block\" class=\"ita-tooltip ita-icon ita-icon-group-16x16\" title=\"Trasmesso all'ufficio\"></span>";
        }

//        $anades_rec = $this->proLib->getGenericTab("SELECT COUNT(ROWID) AS CONTA FROM ANADES WHERE DESNUM=$codice AND DESPAR='" . $arcite_rec['ITEPAR'] . "' AND DESTIPO='T'", false);
//        $record['NDESTPROT'] = $anades_rec['CONTA'];
        $record['NDESTITER'] = $arcite_rec['ITENTRAS'];
        $record['NDESLETTI'] = $arcite_rec['ITENLETT'];

//$ini_tag = "<p style = '$ev font-weight:lighter;'>";
//$fin_tag = "</p>";
        if ($arcite_rec['ITEEVIDENZA'] == 1) {
            $ini_tag = "<div style = 'font-weight:900;color:#BE0000;'>";
            $fin_tag = "</div>";
        }
//        if (substr($record['PROPAR'], 1, 1) == 'A') {
        if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            $ini_tag = "<div style = 'color:white;background-color:black;font-weight:bold;'>";
            $fin_tag = "</div>";
        }

        $record['ITEPAR'] = '<div class="ita-html ">' . $ini_tag . $record['ITEPAR'] . $fin_tag . '</div>';
//        $record['ANNO'] = $ini_tag . $record['ANNO'] . $fin_tag;
        $record['NUMERO'] = $ini_tag . $record['NUMERO'] . $fin_tag;
        $record['ITEDAT'] = $ini_tag . date('d/m/Y', strtotime($itedat)) . $fin_tag;

        // Controllo speizione PEC!
        if (trim(strtoupper($anapro_rec['PROTSP'])) == 'PEC') {
            $ini_tagOggetto = '<div class="ita-html" style="width:18px; display:inline-block;vertical-align:top;"><span class="ita-tooltip ui-icon ui-icon-mail-closed" title="Da PEC"></span></div><div style="display:inline-block;vertical-align:top;">';
            $fin_tagOggetto = '</div>';
        } else {
            $ini_tagOggetto = '<div style="width:18px; display:inline-block;vertical-align:top;"></div><div style="display:inline-block;vertical-align:top;">';
            $fin_tagOggetto = '</div>';
        }

        if ($this->proLib->checkRiservatezzaProtocollo($anapro_rec)) {
            $record['OGGETTO'] = $ini_tagOggetto . "<div style=\"background-color:lightgrey;\">RISERVATO</div>" . $fin_tagOggetto;
        } else {
            $record['OGGETTO'] = $ini_tagOggetto . $ini_tag . $record['OGGETTO'] . $fin_tag . $fin_tagOggetto;
        }

        $record['PROVENIENZA'] = $ini_tag . $record['PROVENIENZA'] . $fin_tag;
//        $record['NDESTPROT'] = $ini_tag . $record['NDESTPROT'] . $fin_tag;
        if ($arcite_rec['GIORNITERMINE'] > 1000) {
            $arcite_rec['GIORNITERMINE'] = '';
        }
        $opacity = "";
        if ($arcite_rec['GIORNITERMINE']) {

            if ($arcite_rec['GIORNITERMINE'] <= 15) {
                $delta = 15 - $arcite_rec['GIORNITERMINE'];
                $opacity1 = (($delta <= 15) ? $delta * (100 / 15) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
        }
        $record['GIORNITERMINE'] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $arcite_rec['GIORNITERMINE'] . '</span></div>';


        //$record['GIORNITERMINE'] = $ini_tag . $arcite_rec['GIORNITERMINE'] . $fin_tag;
        $record['NDESTITER'] = $ini_tag . $record['NDESTITER'] . $fin_tag;
        $record['NDESLETTI'] = $ini_tag . $record['NDESLETTI'] . $fin_tag;
        $record['STATO'] = '<div class="ita-html ">' . $record['STATO'] . '</div>';
        return $record;
    }

    public function elaboraTable($table) {
        foreach ($table as $key => $record) {
            $table[$key] = $this->elaboraRecord($record);
        }
        return $table;
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array());
        Out::hide($this->nameForm . '_statoTrasmissione');
        Out::show($this->nameForm . '_selectStato');

        TableView::enableEvents($this->gridProtocollo);
        TableView::reload($this->gridProtocollo);
    }

    public function openGestione($rowid) {
        $this->mostraForm('divGestione');
        $this->mostraButtonBar(array('VaiAllaFirma', 'Rifiuta'));
        Out::show($this->nameForm . '_statoTrasmissione');
        Out::hide($this->nameForm . '_selectStato');

        $this->proIterAlle = array();
        $this->currArcite = $arcite_rec = $this->proLib->GetArcite($rowid, 'rowid'); // ARCITE CORRENTE
        $this->tipoProt = $arcite_rec['ITEPAR'];
        $anapro_rec = $this->proLib->GetAnapro($arcite_rec['ITEPRO'], 'codice', $arcite_rec['ITEPAR']);
        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $anapro_rec['ROWID']);

        $arcite_pre = $this->proLib->GetArcite($arcite_rec['ITEPRE'], 'itekey');
        $arcite_sus = $this->proLib->GetArciteSus($arcite_rec['ITEKEY']);
        $open_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'] . " " . $arcite_rec['ITEANN'];
        $this->openRecord($this->PROT_DB, 'ARCITE', $open_Info);

        if (substr($this->tipoProt, 0, 1) == 'P' || substr($this->tipoProt, 0, 1) == 'C') {
            $this->caricaGrigliePartenza($anapro_rec);
        } else {
            $this->caricaGriglieArrivo($anapro_rec);
        }

        if ($arcite_rec['ITETIP'] == proIter::ITETIP_ALLAFIRMA) {
            if ($arcite_rec['ITEGES'] == 0) {
                $pre_a = '<pre style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Procedura delle Firme Rifiutata.' . $pre_c);
                Out::show($this->nameForm . '_statoTrasmissione');
                Out::hide($this->nameForm . '_Rifiuta');
            } else {
                $pre_a = '<pre style="color:red;font-weight:bold;text-align:center;font-size:120%;">';
                $pre_c = '</pre>';
                Out::html($this->nameForm . '_statoTrasmissione', $pre_a . 'Firma gli allegati.' . $pre_c);
                $this->mostraButtonBar(array('Rifiuta'));
            }
            if ($this->currArcite['ITESTATO'] != '1' && $this->currArcite['ITESTATO'] != '3' && $this->currArcite['ITEGES'] != 0) {
                if ($arcite_rec['ITEFIN'] != '' && $arcite_rec['ITEFLA'] == '2') {
                    Out::show($this->nameForm . '_Riapri');
                } else {
                    Out::show($this->nameForm . '_Chiudi');
                }
            }
        }

        Out::valori($anapro_rec, $this->nameForm . '_ANAPRO');
        Out::valori($arcite_rec, $this->nameForm . '_ARCITE');
        Out::valore($this->nameForm . "_Pronum", substr($anapro_rec['PRONUM'], 4));
        Out::valore($this->nameForm . "_ITEKEY", $arcite_rec['ITEKEY']);
        /*
         * Utente originario
         */
        $anaprosave_tab = $this->proLib->getGenericTab("SELECT ROWID, PROUTE FROM ANAPROSAVE WHERE PRONUM=" . $anapro_rec['PRONUM'] . " AND PROPAR='" . $anapro_rec['PROPAR'] . "' ORDER BY ROWID");
        if ($anaprosave_tab) {
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', $anaprosave_tab[0]['PROUTE']);
        } else {
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', $anapro_rec['PROUTE']);
        }

        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        Out::valore($this->nameForm . "_Oggetto", $anaogg_rec['OGGOGG']);
        $this->caricaAllegati($arcite_rec['ITEPRO'], $arcite_rec['ITEPAR']);
        Out::show($this->nameForm . '_Ritorna');
        return $arcite_rec;
    }

    private function caricaAllegati($numeroProtocollo, $tipoProt, $daFirma = false) {
        $proLibSdi = new proLibSdi();
        $visualizzaDaFirmare = false;
        // $destinazione = $this->proLib->SetDirectory($numeroProtocollo, $tipoProt);
        $sql = "SELECT * FROM ANADOC WHERE DOCKEY LIKE '" . $numeroProtocollo . $tipoProt . "%' ORDER BY DOCKEY ASC";
        $anadoc_tab = $this->proLib->getGenericTab($sql);
        $AnaproRec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', $tipoProt);
        $anaent_38 = $this->proLib->GetAnaent('38');
        $fattEleSdi = '';
        if ($AnaproRec['PROCODTIPODOC']) {
            if ($anaent_38['ENTDE1'] == $AnaproRec['PROCODTIPODOC'] || $anaent_38['ENTDE2'] == $AnaproRec['PROCODTIPODOC'] ||
                    $anaent_38['ENTDE3'] == $AnaproRec['PROCODTIPODOC'] || $anaent_38['ENTDE4'] == $AnaproRec['PROCODTIPODOC']) {
                $fattEleSdi = "<span style=\"display:inline-block;vertical-align:bottom;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
            }
        }

        foreach ($anadoc_tab as $anadoc_rec) {
            if (!$anadoc_rec['DOCSERVIZIO']) {
                $firma = "";
                $rowid = $anadoc_rec['ROWID'];
                $this->proIterAlle[$rowid] = $anadoc_rec;
//                $this->proIterAlle[$rowid]['FILEPATH'] = $destinazione . '/' . $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILEPATH'] = $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILENAME'] = $anadoc_rec['DOCFIL'];
                $this->proIterAlle[$rowid]['FILEINFO'] = $anadoc_rec['DOCNOT'];
                if ($anadoc_rec['DOCNAME'] == '') {
                    $anadoc_rec['DOCNAME'] = $anadoc_rec['DOCFIL'];
                }
                $ext = pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
                if (strtolower($ext) == "p7m") {
                    $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                } else if (!$this->visualizzazione && $this->currArcite['ITESTATO'] != '1' && $this->currArcite['ITESTATO'] != '3' && $this->currArcite['ITEGES'] != 0) {
                    $profilo = proSoggetto::getProfileFromIdUtente();
                    $docfirma_rec = $this->proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', false, " AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
                    if ($docfirma_rec) {
                        $firma = "<span class=\"ita-icon ita-icon-sigillo-24x24\">Da Firmare</span>";
                        $this->proIterAlle[$rowid]['DAFIRMARE'] = true;
                        $visualizzaDaFirmare = true;
                    }
                }

                $this->proIterAlle[$rowid]['PROVENIENZAALLE'] = $anadoc_rec['DOCUTE'];
                $this->proIterAlle[$rowid]['FIRMA'] = $firma;
                $isFatturaPA = $proLibSdi->isAnadocFileFattura($AnaproRec['ROWID'], $anadoc_rec['DOCNAME']);
                $isMessaggioFatturaPA = $proLibSdi->isAnadocFileMessaggio($AnaproRec['ROWID'], $anadoc_rec['DOCNAME']);
                if ($isFatturaPA || $isMessaggioFatturaPA) {
                    $this->proIterAlle[$rowid]['SDI'] = $fattEleSdi;
                } else {
                    $this->proIterAlle[$rowid]['SDI'] = '';
                }
            }
        }
        if ($visualizzaDaFirmare) {
            Out::show($this->nameForm . '_VaiAllaFirma');
            $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATA<>''");
            if (!$docfirma_tab) {
                Out::show($this->nameForm . '_Rifiuta');
            } else {
                Out::hide($this->nameForm . '_Rifiuta');
            }
        } else {
            Out::hide($this->nameForm . '_VaiAllaFirma');
            Out::hide($this->nameForm . '_statoTrasmissione');
            $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATA<>''");
            if ($docfirma_tab) {
                Out::hide($this->nameForm . '_Rifiuta');
            }
        }

        /**
         * DA SISTEMARE
         */
        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($numeroProtocollo, $tipoProt, true, " AND FIRDATARICH<>''");
        if ($docfirma_tab) {
            Out::show($this->nameForm . '_Mail');
            $apri = true;
            foreach ($docfirma_tab as $docfirma_rec) {
                if (!$docfirma_rec['FIRDATA']) {
                    Out::hide($this->nameForm . '_Mail');
                    $apri = false;
                }
            }
            $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
            $proArriDest = $this->proLib->caricaDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $proAltriDestinatari = $this->proLib->caricaAltriDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], true);
            $destMap = $this->proLibMail->checkInvioAvvenuto($anapro_rec, $proArriDest, $proAltriDestinatari);
            if (!$destMap) {
                Out::hide($this->nameForm . '_Mail');
                $apri = false;
            }
            if ($daFirma == true && $apri == true) {
                $this->inviaMail();
            }
        }
        $this->caricaGriglia($this->gridAllegati, $this->proIterAlle);
    }

    private function caricaGriglieArrivo($anapro_rec) {
        // TableView::setLabel($this->nameForm . '_gridMittentiProt', 'MITNOME', 'Mittenti');
        $mittenti_tab = $this->proLib->getGenericTab("SELECT PRONOM AS MITNOME FROM PROMITAGG WHERE PROPAR='{$anapro_rec['PROPAR']}' AND PRONUM=" . $anapro_rec['PRONUM']);
        array_unshift($mittenti_tab, array('MITNOME' => $anapro_rec['PRONOM']));
        $this->CaricaGriglia($this->gridFirmatari, $mittenti_tab);
    }

    private function caricaGrigliePartenza($anapro_rec) {
        // TableView::setLabel($this->nameForm . '_gridMittentiProt', 'MITNOME', 'Firmatari');
        $firmatari_tab = $this->proLib->getGenericTab("SELECT PRONOM AS MITNOME FROM PROMITAGG WHERE PROPAR='{$anapro_rec['PROPAR']}' AND PRONUM=" . $anapro_rec['PRONUM']);
        $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
        array_unshift($firmatari_tab, array('MITNOME' => $anades_mitt['DESNOM']));
        $this->CaricaGriglia($this->gridFirmatari, $firmatari_tab);
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
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

    private function chiudiIter($annotazioni) {
        $arcite_rec = $this->proLib->GetArcite($this->currArcite['ROWID'], 'rowid');
        $arcite_rec['ITEFIN'] = date('Ymd');
        $arcite_rec['ITEFLA'] = '2';
        $arcite_rec['ITEANN'] = $annotazioni;
        $update_Info = 'Oggetto: ' . $arcite_rec['ITEPRO'];
        if (!$this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, $update_Info)) {
            Out::msgStop("Attenzione", "Errore in aggiornamento ITER.");
        }
    }

    private function VaiAllaFirma() {
        Out::tabSelect($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneAllegati");
        $daFirmare = array();
        /*
         * Sposta il file da firmare sulla cartella temporanea
         */
        $subPath = "segFirma-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);

        foreach ($this->proIterAlle as $key => $allegato) {
            if ($allegato['DAFIRMARE']) {
                $daFirmare[$key]['FILEORIG'] = $allegato['DOCNAME'];
//                $baseName = pathinfo($allegato['FILEPATH'], PATHINFO_BASENAME);
//                $InputFileTemporaneo = $tempPath . "/" . $baseName;
//                if (!@copy($allegato['FILEPATH'], $InputFileTemporaneo)) {
//                    Out::msgStop("Attenzione", "Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
//                    return false;
//                }
                $baseName = pathinfo($allegato['FILEPATH'], PATHINFO_BASENAME);
                $InputFileTemporaneo = $tempPath . "/" . $baseName;
                $FilePathCopy = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], $InputFileTemporaneo);
                if (!$FilePathCopy) {
                    Out::msgStop("Attenzione", "Errore durante la copia del file nell'ambiente temporaneo di lavoro." . $this->proLibAllegati->getErrMessage());
                    return false;
                }
//                Out::msgInfo('file', $InputFileTemporaneo);
                $daFirmare[$key]['INPUTFILEPATH'] = $InputFileTemporaneo;
                $daFirmare[$key]['OUTPUTFILEPATH'] = $InputFileTemporaneo . ".p7m";
//                $daFirmare[$key]['INPUTFILEPATH'] = $allegato['FILEPATH'];
//                $daFirmare[$key]['OUTPUTFILEPATH'] = $allegato['FILEPATH'] . ".p7m";
                $daFirmare[$key]['SIGNRESULT'] = '';
                $daFirmare[$key]['SIGNMESSAGE'] = '';
            }
        }
        //
        // Apertura Nuova Form
        //
        $return = "returnFromSignAuth";
        itaLib::openForm('proFirma', true);
        /* @var $proFirma proFirma */
        $proFirma = itaModel::getInstance('proFirma');
        $proFirma->setEvent('openform');
        $proFirma->setReturnEvent($return);
        $proFirma->setReturnModel($this->nameForm);
        $proFirma->setReturnId('');
        $proFirma->setReturnMultiFile(true);
        $proFirma->setMultiFile(false);
        $proFirma->setAllegati($daFirmare);
        $proFirma->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
        $proFirma->parseEvent();
    }

    private function salvaDocumentoFirmato($rowidAnadoc, $outputFilePath, $inputFilePath, $FilenameFirmato) {
        $anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, "ROWID");
        $DocFil_Input = $anadoc_rec['DOCFIL'];
        /*
         * Sposto il file firmato:
         */
        $protPath = $this->proLib->SetDirectory($anadoc_rec['DOCNUM'], substr($anadoc_rec['DOCPAR'], 0, 1));

        $fileName = $anadoc_rec['DOCNAME'] . '.p7m';
        $FileNameDest = $anadoc_rec['DOCFIL'] . '.p7m';

        $FileDest = $protPath . "/" . $FileNameDest;
        // Sposto dalla cartella temporanea alla cartella del prot.
        if (!@rename($outputFilePath, $FileDest)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in salvataggio del file " . $fileName . " !");
            return false;
        }

        /*
         *  Inserisco l'anadocsave
         */
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anadocSave_rec = $anadoc_rec;
        $anadocSave_rec['ROWID'] = '';
        $anadocSave_rec['SAVEDATA'] = $savedata;
        $anadocSave_rec['SAVEORA'] = $saveora;
        $anadocSave_rec['SAVEUTENTE'] = $saveutente;
        if (!$this->insertRecord($this->PROT_DB, 'ANADOCSAVE', $anadocSave_rec, '', 'ROWID', false)) {
            Out::msgStop("Firma File", "Errore in salvataggio ANADOCSAVE.");
            return false;
        }
        $anadoc_rec['DOCUUID'] = '';
        /* Se attivo parametri alfresco - salvo su alfresco */
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1']) {
            $anapro_rec = $this->proLib->getAnapro($anadoc_rec['DOCNUM'], 'codice', $anadoc_rec['DOCPAR']);
            $Uuid = $this->proLibAllegati->AggiungiAllegatoAlfresco($anapro_rec, $FileDest, $FilenameFirmato);
            if (!$Uuid) {
                Out::msgStop('Attenzione', 'Errore in salvataggio file firmato.');
                return false;
            }
            $anadoc_rec['DOCUUID'] = $Uuid;
        }

        $anadoc_rec['DOCFIL'] = pathinfo($FileDest, PATHINFO_BASENAME);
        $anadoc_rec['DOCLNK'] = "allegato://" . pathinfo($FileDest, PATHINFO_BASENAME);
        $anadoc_rec['DOCNAME'] = $FilenameFirmato;
        $anadoc_rec['DOCDATAFIRMA'] = date("Ymd");
        $anadoc_rec['DOCNOT'] = 'Firmato: ' . $anadoc_rec['DOCNOT'];
        $anadoc_rec['DOCMD5'] = md5_file($FileDest);
        $anadoc_rec['DOCSHA2'] = hash_file('sha256', $FileDest);
        $update_Info = 'Oggetto: Aggiornamento allegato ' . $anadoc_rec['DOCNAME'];
        if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
            Out::msgStop("Firma remota", "Aggiornamento dati documento " . $anadoc_rec['DOCNAME'] . " fallito");
            return false;
        }
        // @TODO se ci sarà firma di un p7m, controllare l'unlink o farlo prima del rename.
//        $FileInInput = $protPath . "/" . $DocFil_Input;
//        if (!@unlink($FileInInput)) {
//            Out::msgStop("Firma remota", "cancellazione file " . $FileInInput . " fallita.... il procedimento continua.");
//        }
        $profilo = proSoggetto::getProfileFromIdUtente();
        $docfirma_rec = $this->proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', false, " AND ROWIDARCITE={$this->currArcite['ROWID']} AND FIRCOD='{$profilo['COD_SOGGETTO']}'");
        if ($docfirma_rec) {
            $docfirma_rec['FIRDATA'] = date('Ymd');
            $docfirma_rec['FIRORA'] = date('H:i:s');
            $update_Info = "Oggetto: Documento firmato da soggetto " . $profilo['COD_SOGGETTO'];
            if (!$this->updateRecord($this->PROT_DB, 'DOCFIRMA', $docfirma_rec, $update_Info)) {
                Out::msgStop("Firma remota", "Aggiornamento dati documento da firmare " . $anadoc_rec['DOCNAME'] . " fallito");
                return false;
            }
        }
    }

    private function inviaMail() {
        $anapro_rec = $this->proLib->GetAnapro($this->currArcite['ITEPRO'], 'codice', $this->currArcite['ITEPAR']);
        $proArriDest = $this->proLib->caricaDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $proAltriDestinatari = $this->proLib->caricaAltriDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], true);
        $proArriAlle = $this->proLib->caricaAllegatiProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $allegati = array();
        foreach ($proArriAlle as $allegato) {
            if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0) {
                // Sovrascrivo la path. Attenzione, potrebbero occupare molto spazio le copie prima dell'invio.
                $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], '', true);
                $allegato['FILEPATH'] = $CopyPathFile;
                $allegati[] = $allegato;
            }
        }

        $this->appoggio = array('proArriDest' => $proArriDest, 'proAltriDestinatari' => $proAltriDestinatari);
        $risultato = $this->proLibMail->inviaMailDestinatari($this->nameForm, $proArriDest, $proAltriDestinatari, $allegati, $anapro_rec['PRONUM'], 'codice', $anapro_rec['PROPAR']);
        if (!$risultato) {
            if ($this->proLibMail->getErrCode() != -1) {
                Out::msgInfo("Notifica Destinatari", $this->proLibMail->getErrMessage());
            } else {
                Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
            }
        }
    }

    private function inserisciNotifica($oggetto, $testo, $uteins) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $env_notifiche = array();
        $env_notifiche['OGGETTO'] = $oggetto;
        $env_notifiche['TESTO'] = $testo;
        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
        $env_notifiche['MODELINS'] = $this->nameForm;
        $env_notifiche['DATAINS'] = date("Ymd");
        $env_notifiche['ORAINS'] = date("H:i:s");
        $env_notifiche['UTEDEST'] = $uteins;
        $insert_Info = 'Oggetto notifica: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
        $this->insertRecord($ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $insert_Info);
    }

    private function EditAllegati() {
        if (array_key_exists($_POST[$this->gridAllegati]['gridParam']['selrow'], $this->proIterAlle) == true) {
            $fileName = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILENAME'];
            $filepath = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILEPATH'];
            $FileAllegato = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['FILENAME'];
            $rowid = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['ROWID'];
            if (strtolower(pathinfo($FileAllegato, PATHINFO_EXTENSION)) == "eml") {
                Out::msgQuestion("Upload.", "Cosa vuoi fare con il file eml selezionato?", array(
                    'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                    'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        )
                );
                $this->currAllegato = array("FileAllegato" => $FileAllegato, "FileDati" => $filepath, 'Rowid' => $rowid);
                return;
            }

            $force = false;
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) == 'xml') {
                $force = true;
            }

            $rowid = $this->proIterAlle[$_POST[$this->gridAllegati]['gridParam']['selrow']]['ROWID'];
            $this->proLibAllegati->OpenDocAllegato($rowid, $force);
        }
    }

}

?>