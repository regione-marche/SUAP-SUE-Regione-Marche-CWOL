<?php

/**
 *
 * Gestione Elenco registri giornalieri
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPRestClient
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    16.06.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDigiPMarche.class.php';

function proGiornaliero() {
    $proGiornaliero = new proGiornaliero();
    $proGiornaliero->parseEvent();
    return;
}

class proGiornaliero extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibAllegati;
    public $proLibTabDag;
    public $proLibGiornaliero;
    public $proLibConservazione;
    public $proDigiPMarche;
    public $nameForm = "proGiornaliero";
    public $divRis = "proGiornaliero_divRisultato";
    public $gridRegistro = "proGiornaliero_gridRegistro";
    public $workDate;
    public $workYear;
    public $eqAudit;
    public $rowidAnapro;
    public $filePDA;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibTabDag = new proLibTabDag();
            $this->proLibConservazione = new proLibConservazione();
            $this->proLibGiornaliero = new proLibGiornaliero();
            $this->proDigiPMarche = new proDigiPMarche();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->eqAudit = new eqAudit();
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($data));
            $this->rowidAnapro = App::$utente->getKey($this->nameForm . '_rowidAnapro');
            $this->filePDA = App::$utente->getKey($this->nameForm . '_filePDA');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidAnapro', $this->rowidAnapro);
            App::$utente->setKey($this->nameForm . '_filePDA', $this->filePDA);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (!$this->ControllaPresenzaDatiFondamentali()) {
                    return;
                }
                $this->ElencaRegistro();
//                Out::hide($this->nameForm . '_divLegenda');
                $this->CaricaLegenda();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridRegistro:
                        $indice = $_POST['rowid'];
                        $Anaproctr_rec = $this->proLib->GetAnapro($indice, 'rowid');
                        $model = 'proArri';
                        $_POST = array();
                        $_POST['tipoProt'] = $Anaproctr_rec['PROPAR'];
                        $_POST['event'] = 'openform';
                        $_POST['proGest_ANAPRO']['ROWID'] = $indice;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridRegistro:
                        $this->LanciaElencaRegistro();
                        break;
                }
                break;
            case 'printTableToHTML':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_StampaVerifica':
                        $this->StampaAnomalie();
//                        $retControlli = $this->proLibGiornaliero->ControlliRegistri();
//                        App::log($retControlli);
                        break;
                    case $this->nameForm . '_GeneraRegistro':
                        if (!$this->proLibGiornaliero->ControllaPresenzaDatiFondamentali()) {
                            Out::msgInfo("Attenzione", $this->proLibGiornaliero->getErrMessage());
                            break;
                        }
                        $retGiorni = $this->proLibGiornaliero->getGiorniRegistro();
                        if (!$retGiorni) {
                            Out::msgInfo("Attenzione", "Nessun registro giornaliero da generare.");
                            break;
                        }
                        $msgGg = '<div>Verrà generato il registro giornaliero per i seguenti giorni:<br><br>';
                        //$Giorni = implode('<br>', $retGiorni);
                        $Giorni = '';
                        foreach ($retGiorni as $Giorno) {
                            $Giorni.= date("d/m/Y", strtotime($Giorno)) . '<br>';
                        }
                        Out::msgQuestion("Generazione Registro", $msgGg . $Giorni . '</div>', array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaGeneraRegistro', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaGeneraRegistro', 'model' => $this->nameForm)
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaGeneraRegistro':
                        $this->GeneraRegistri();
                        $this->ElencaRegistro();
                        break;

                    case $this->nameForm . '_StampaRegistri':
                        $Anaent_rec = $this->proLib->GetAnaent('2');
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $Anaent_rec['ENTDE1']
                        );
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proRegistriGio', $parameters);
                        break;

                    case $this->nameForm . '_Funzioni':
                        $rowid = $this->formData[$this->gridRegistro]['gridParam']['selarrrow'];
                        if ($rowid == '' || $rowid == 'null') {
                            Out::msgInfo('Attenzione', "Selezionare un registro prima di procedere.");
                            break;
                        }
                        $arrBottoni = $this->proLibGiornaliero->getMenuFunzioni($this->nameForm, $rowid);
                        if (!$arrBottoni) {
                            Out::msgInfo("Attenzione", "Non ci sono funzioni disponibili.");
                            break;
                        }
                        Out::msgQuestion("Menu Funzioni", "Seleziona la funzione da utilizzare", $arrBottoni, 'auto', 'auto', 'true', false, true, true);
                        break;

                    case $this->nameForm . '_Esporta':
                        $this->EsportaAllegatiNonEsportati();
                        $this->ElencaRegistro();
                        break;

                    case $this->nameForm . '_Conserva':
                        $rowid = $this->formData[$this->gridRegistro]['gridParam']['selarrrow'];
                        if ($rowid == '' || $rowid == 'null') {
                            Out::msgInfo('Attenzione', "Occorre selezionare il registro che si vuole riversare in conservazione.");
                            break;
                        }
                        $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                        if (!$Anapro_rec) {
                            Out::msgStop("Errore", "Documento da versare non accessibile");
                            break;
                        }
                        $AnaproMetadati_rec = $this->proLibGiornaliero->getAnaproAndMetadati($rowid);
                        $retControlliAnapro = $this->proLibGiornaliero->ControlliRegistri(array($AnaproMetadati_rec));
                        if ($retControlliAnapro[proLibGiornaliero::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']]) {
                            Out::msgInfo("Attenzione", "Conservazione non eseguibile: <br>" . $retControlliAnapro[proLibGiornaliero::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']]);
                            break;
                        }
                        $esito_rec = $this->proLibConservazione->GetEsitoConservazione($rowid);
                        if ($esito_rec) {
                            if ($esito_rec['Esito'] == proLibConservazione::ESITO_NEGATIVO) {
                                Out::msgQuestion(
                                        "Versamento in conservazione Doc:{$Anapro_rec['PRONUM']}", "Tentativo di versamento già effettuato:<br/><br/>Esito: {$esito_rec['Esito']}<br/>Messaggio: {$esito_rec['MessaggioErrore']}<br/><br>Riprovi?.", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaVersamento', 'model' => $this->nameForm),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaVersamento', 'model' => $this->nameForm)
                                ));
                                break;
                            } elseif ($esito_rec['Esito'] == proLibConservazione::ESITO_WARNING) {
                                Out::msgInfo("Versamento in conservazione", "Documento gia versato con esito:<br>{$esito_rec['Esito']}<br>{$esito_rec['MessaggioErrore']}");
                                break;
                            } elseif ($esito_rec['Esito'] == proLibConservazione::ESITO_POSTITIVO) {
                                Out::msgInfo("Versamento in conservazione", "Documento gia versato con esito:<br>{$esito_rec['Esito']}<br>{$esito_rec['MessaggioErrore']}");
                                break;
                            }
                        }
                    case $this->nameForm . '_ConfermaVersamento':
                        $rowid = $this->formData[$this->gridRegistro]['gridParam']['selarrrow'];
                        if ($rowid == '' || $rowid == 'null') {
                            Out::msgInfo('Attenzione', "Occorre selezionare il registro che si vuole riversare in conservazione.");
                            break;
                        }
                        if ($this->VersaInConservazione($rowid)) {
                            $this->ElencaRegistro();
                        }
                        break;


                    case $this->nameForm . '_ImpostaEsitoConservazione':
                        $rowid = $this->formData[$this->gridRegistro]['gridParam']['selarrrow'];
                        if ($rowid == '' || $rowid == 'null') {
                            Out::msgInfo('Attenzione', "Selezionare un registro prima di procedere.");
                            break;
                        }
                        $this->rowidAnapro = $rowid;
                        $this->filePDA = '';

                        $valori[] = array(
                            'label' => array(
                                'value' => "Carica PDA di Conservazione (se presente)",
                                'style' => ' display:block;float:none;padding: 0 5px 0 0;text-align: left;'
                            ),
                            'id' => $this->nameForm . '_CaricaPDA',
                            'name' => $this->nameForm . '_CaricaPDA',
                            'type' => 'text',
                            'style' => 'margin:2px;width:450px;',
                            'value' => '',
                            'class' => 'ita-edit-upload ita-readonly'
                        );
                        Out::msgInput(
                                'Stato conservazione', $valori, array(
                            'Positivo' => array('id' => $this->nameForm . '_ConfermaConsPositivo', 'metaData' => "iconLeft:'ita-icon-check-green-16x16'", 'model' => $this->nameForm),
                            'Negativo' => array('id' => $this->nameForm . '_ConfermaConsNegativo', 'metaData' => "iconLeft:'ita-icon-delete-16x16'", 'model' => $this->nameForm),
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCons', 'metaData' => "iconLeft:'ita-icon-rotate-left-16x16'", 'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        // Question
                        Out::codice("pluploadActivate('" . $this->nameForm . "_CaricaPDA_upld_uploader');");
                        break;

                    case $this->nameForm . '_ConfermaConsPositivo':
                        $this->ImpostaEsitoConservazione(proLibConservazione::CONSER_ESITO_POSTITIVO);
                        break;
                    case $this->nameForm . '_ConfermaConsNegativo':
                        $this->ImpostaEsitoConservazione(proLibConservazione::CONSER_ESITO_NEGATIVO);
                        break;

                    case $this->nameForm . '_CaricaPDA_upld':
                        $this->verificaUpload();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_NascondiAnnullati':
                    case $this->nameForm . '_VediNonConservati':
                        $this->ElencaRegistro();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_rowidAnapro');
        App::$utente->removeKey($this->nameForm . '_filePDA');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
        Out::show('menuapp');
    }

    private function CreaSql() {
        if ($_POST[$this->nameForm . '_NascondiAnnullati']) {
            $where = " AND ANAPRO.PROPAR='C' AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
        }
        $sql = $this->proLibGiornaliero->getSqlElencoRegistro($where);
        $sql = " SELECT *
                    FROM
                       ($sql) AS REGISTRO_CONSERVA
                    WHERE 1 ";
        if ($_POST[$this->nameForm . '_VediNonConservati']) {
            $sql.=" AND (ESITO_CONSERVAZIONE_ESITO <> '" . proLibConservazione::ESITO_POSTITIVO . "' 
                    AND ESITO_CONSERVAZIONE_ESITO <> '" . proLibConservazione::ESITO_WARNING . "' 
                    OR ESITO_CONSERVAZIONE_ESITO IS NULL) ";
        }
        return $sql;
    }

    function elaboraRecords($result_tab, $retControlli) {
        foreach ($result_tab as $key => $result_rec) {
            $result_tab[$key]['CODICE'] = substr($result_rec['PRONUM'], 4, 6);
            $result_tab[$key]['ANNO'] = substr($result_rec['PRONUM'], 0, 4);
            $result_tab[$key]['STATO'] = $this->getStato($result_rec, $retControlli);
            $result_tab[$key]['STATO_CONSERVAZIONE'] = $this->getStatoConservazione($result_rec);
        }
        return $result_tab;
    }

    private function ElencaRegistro() {
        //TableView::clearGrid($this->gridRegistro);
        TableView::enableEvents($this->gridRegistro);
        TableView::reload($this->gridRegistro);
    }

    private function LanciaElencaRegistro() {
        TableView::clearGrid($this->gridRegistro);
        $sql = $this->CreaSql();
        $ita_grid01 = new TableView($this->gridRegistro, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        if ($_POST['page']) {
            $page = $_POST['page'];
        }
        $rows = $_POST[$this->gridRegistro]['gridParam']['rowNum'];
        if ($_POST['rows']) {
            $rows = $_POST['rows'];
        }
        $order = 'desc';
        if ($_POST['sord']) {
            $order = $_POST['sord'];
        }
        $ordinamento = $_POST['sidx'];
        switch ($ordinamento) {
            case 'ANNO':
            case 'CODICE':
            case 'STATO':
                $ordinamento = 'PRONUM';
                break;
        }
        $ita_grid01->setPageNum($page);
        $ita_grid01->setPageRows($rows);
        $ita_grid01->setSortIndex($ordinamento);
        $ita_grid01->setSortOrder($order);

        //$result_tab = $ita_grid01->getDataArray();
        $retControlli = $this->proLibGiornaliero->ControlliRegistri($ita_grid01->getDataArray());
        $result_tab = $this->elaboraRecords($ita_grid01->getDataArray(), $retControlli);
        $ita_grid01->getDataPageFromArray('json', $result_tab);
        foreach ($result_tab as $result_rec) {
//            if ($result_rec['PROPAR'] == 'CA') {
            if ($result_rec['PROPAR'] == 'C' && $result_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                TableView::setRowData($this->gridRegistro, $result_rec['ROWID'], '', array('background' => 'black', 'color' => 'white', 'opacity' => '0.8'));
            }
        }
        TableView::enableEvents($this->gridRegistro);
    }

    private function GeneraRegistri() {
        $retGenera = $this->proLibGiornaliero->GeneraRegistri();
        if (!$retGenera) {
            Out::msgStop("Errore", $this->proLibGiornaliero->getErrMessage());
        } else {
            Out::msgInfo("Generazione Registri", $retGenera);
        }
    }

    public function getStato($Anapro_rec, $retControlli) {
        $styleDiv = "border:1px solid black; display:inline-block; width:15px; height:15px;  -moz-border-radius: 22px; border-radius: 22px;box-shadow:0px 0px 3px #CCCCCC; ";
        $colore = 'background-color:green;';
        $Descr = 'Nessuna Anomalia';
        foreach (proLibGiornaliero::$ElencoColoriCtrAnomalie as $key => $ColoreStato) {
            if ($retControlli[$key][$Anapro_rec['ROWID']]) {
                $colore = 'background-color:' . $ColoreStato . ';';
                $Descr = proLibGiornaliero::$ElencoDescrCtrAnomalie[$key];
                break;
            }
        }
        $Stato = "<div class=\"ita-html\"><div class=\" ita-tooltip\"  title =\"" . $Descr . "\"style=\" margin-left:10px; $styleDiv $colore \"></div></div>";
        return $Stato;
    }

    public function CaricaLegenda() {
        $styleDiv = "border:1px solid black; display:inline-block; width:12px; height:12px;  -moz-border-radius: 20px; border-radius: 20px; ";
        $Descrizione = " <div style=\"display:inline-block;\"> Nessuna Anomalia</div>";
        $html = "<br><div><div style=\" margin-left:2px; $styleDiv background-color:green; \"></div>$Descrizione</div><br>";

        foreach (proLibGiornaliero::$ElencoColoriCtrAnomalie as $key => $ColoreStato) {
            $colore = 'background-color:' . $ColoreStato . ';';
            $Descrizione = " <div style=\"display:inline-block; width:85%\">" . proLibGiornaliero::$ElencoDescrCtrAnomalie[$key] . "</div>";
            $html.= "<div><div style=\"display:inline-block; margin-left:2px; $styleDiv $colore \"></div>$Descrizione</div><br>";
        }
        App::log($html);
        Out::html($this->nameForm . '_divLegendaStato', $html);
    }

    public function StampaAnomalie() {
        $retControlli = $this->proLibGiornaliero->ControlliRegistri();
        $msg = "<br>";

        foreach (proLibGiornaliero::$ElencoDescrCtrAnomalie as $key => $Descrizione) {
            if (proLibGiornaliero::CTR_GIORNI_MANCANTI == $key) {
                if ($retControlli[$key]) {
                    $msg.="<b><u>$Descrizione:</u></b><br>";
                    foreach ($retControlli[$key] as $rowidAnapro => $valore) {
                        $DataRegistro = date("d/m/Y", strtotime($valore));
                        $msg.=" REGISTRO DEL " . $DataRegistro . " <br>";
                    }
                    $msg.='<br><br>';
                }
                continue;
            }
            if (proLibGiornaliero::CTR_ALLEGATO_NO_METADATI == $key) {
                if ($retControlli[$key]) {
                    $msg.="<b><u>$Descrizione:</u></b><br>";
                    foreach ($retControlli[$key] as $rowidAnapro => $valore) {
                        $Anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                        $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], proLibGiornaliero::CHIAVE_DATA_REGISTRO, '', false, '', proLibGiornaliero::FONTE_DATI_REGISTRO);
                        $Codice = substr($Anapro_rec['PRONUM'], 4, 6);
                        $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
                        $DataRegistro = date("d/m/Y", strtotime($TabDag_rec['TDAGVAL']));
                        $msg.=$Anaogg_rec['OGGOGG'] . " - PROT. C " . $Anno . '/' . $Codice . "<br>";
                    }
                }
                continue;
            }
            if ($retControlli[$key]) {
                $msg.="<b><u>$Descrizione:</u></b><br>";
                foreach ($retControlli[$key] as $rowidAnapro => $valore) {
                    $Anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                    $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], proLibGiornaliero::CHIAVE_DATA_REGISTRO, '', false, '', proLibGiornaliero::FONTE_DATI_REGISTRO);
                    $Codice = substr($Anapro_rec['PRONUM'], 4, 6);
                    $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
                    $DataRegistro = date("d/m/Y", strtotime($TabDag_rec['TDAGVAL']));
                    $msg.=" REGISTRO DEL " . $DataRegistro . " - PROT. C " . $Anno . '/' . $Codice . "<br>";
                }
                $msg.='<br><br>';
            }
        }
        $sql = "SELECT * FROM ANAENT LIMIT 1";
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $parameters = array("Sql" => $sql, "Ente" => '01', "Risultato" => $msg);
        $itaJR->runSQLReportPDF($this->PROT_DB, 'proGiornalieroAnomalie', $parameters);
    }

    public function EsportaAllegatiNonEsportati() {
        $retControlli = $this->proLibGiornaliero->ControlliRegistri();
        if ($retControlli[proLibGiornaliero::CTR_ALLEGATO_NON_ESPORTATO]) {
            foreach ($retControlli[proLibGiornaliero::CTR_ALLEGATO_NON_ESPORTATO] as $rowidAnapro => $valore) {
                $Anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                if (!$this->proLibGiornaliero->ExportFileRegistrioGiornaliero($Anapro_rec)) {
                    Out::msgStop("Salvataggio Allegati", $this->proLibGiornaliero->getErrMessage());
                    return;
                }
            }
        } else {
            Out::msgInfo("Esportazione", "Nessuno allegato da esportare trovato.");
            return;
        }
        Out::msgInfo("Esportazione", "Esportazione allegati conclusa con successo.");
    }

    public function VersaInConservazione($rowid) {
        $param = array();
        $parametriRegistro = $this->proLibGiornaliero->getParametriRegistro();
        $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $rowid, proLibGiornaliero::CHIAVE_DATA_REGISTRO, '', false, '', proLibGiornaliero::FONTE_DATI_REGISTRO);
        if ($TabDag_rec) {
            $DataRegistro = $TabDag_rec['TDAGVAL'];
            $datetime_esito = date('Ymd_His');
            //$param['NOMEFILEESITO'] = 'ESITO_CONSERVAZIONE_REG_' . $DataRegistro . '_' . $datetime_esito . '.xml';
        }
        $param['TIPOCONSERVAZIONE'] = $parametriRegistro['TIPOCONSERVAZIONE'];

        $Anapro_da_conservare_rec = $this->proLib->GetAnapro($rowid, 'rowid');

        $Audit = 'Inizio Chiamata conservazione registro giornaliero prot. rowid: ' . $rowid;
        $Audit.='- Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        /*
         * Chiamata a conservazione
         */
        $retConservazione = $this->proLibConservazione->conservaAnapro($rowid, $param);
        if (!$retConservazione) {
            $Audit = 'Chiamata conservazione registro giornaliero conclusa con errori  prot. rowid: ' . $rowid;
            $Audit.='- Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"] . '. Esito: ' . $this->proLibConservazione->getErrCode() . ' ' . $this->proLibConservazione->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            switch ($this->proLibConservazione->getErrCode()) {
                case proLibConservazione::ERR_CODE_FATAL:
                    Out::msgStop("Errore", $this->proLibConservazione->getErrMessage());
                    return false;
                case proLibConservazione::ERR_CODE_INFO:
                    Out::msgInfo("Errore", $this->proLibConservazione->getErrMessage());
                    return false;
                case proLibConservazione::ERR_CODE_QUESTION:
                    Out::msgInfo("Errore", $this->proLibConservazione->getErrMessage());
                    return false;
                case proLibConservazione::ERR_CODE_WARNING:
                    Out::msgStop("Errore", $this->proLibConservazione->getErrMessage());
                    return false;
            }
        }
        $esitoMsg = $this->proLibConservazione->getRetEsito();
        $DescrEsito = proLibConservazione::$ElencoDescrCtrEsito[$esitoMsg];
        $esitoMsg = $esitoMsg[proLibConservazione::CHIAVE_ESITO_ESITO];
        $Audit = 'Chiamata conservazione registro giornaliero conclusa senza errori Prot. rowid: ' . $rowid;
        $Audit.='- Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"] . '. Esito: ' . $esitoMsg;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        Out::msgInfo('Riversato in conservazione', 'Esito Conservazione: ' . $DescrEsito);
        return true;
    }

    public function ControllaPresenzaDatiFondamentali() {
        $CodiceRegistro = $this->proLib->GetCodiceRegistroProtocollo();
        if (!$CodiceRegistro) {
            Out::msgStop("Attenzione", "Parametro del Codice Registro Protocollo mancante. <br>Non è possibile procedere con la stampa del Registro Giornaliero.");
            $this->close();
            return false;
        }
        $CodiceDocFormali = $this->proLib->GetCodiceRegistroDocFormali();
        if (!$CodiceDocFormali) {
            Out::msgStop("Attenzione", "Parametro del Codice Registro Documenti Formali mancante. <br>Non è possibile procedere con la stampa del Registro Giornaliero.");
            $this->close();
            return false;
        }

        return true;
    }

    public function getStatoConservazione($result_rec) {
        $iconaStato = '';
        switch ($result_rec['ESITO_CONSERVAZIONE_ESITO']) {
            case proLibConservazione::ESITO_POSTITIVO:
                $iconaStato = '<div class="ita-html"><div class=" ita-tooltip ita-icon ita-icon-check-green-24x24"  title ="Conservato" style=" margin-left:11px;\"></div></div>';
                break;
            case proLibConservazione::ESITO_WARNING:
                $iconaStato = '<div class="ita-html"><div class=" ita-tooltip ita-icon ita-icon-check-blue-24x24"  title ="Conservato con avvertimenti" style=" margin-left:10px;\"></div></div>';
                break;
            case proLibConservazione::ESITO_NEGATIVO:
                $iconaStato = '<div class="ita-html"><div class=" ita-tooltip ita-icon ita-icon-delete-16x16"  title ="Errore in conservazione" style=" margin-left:12px;\"></div></div>';
                break;
            case proLibConservazione::ESITO_DAVERIFICARE:
                $iconaStato = '<div class="ita-html"><div class=" ita-tooltip ita-icon ita-icon ita-icon-check-orange-24x24"  title ="Esito di conservazione da verificare." style=" margin-left:12px;\"></div></div>';
                break;

            default:
                break;
        }
        return $iconaStato;
    }

    private function verificaUpload() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
                return false;
            }
        }
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = itaLib::getRandBaseName() . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            //Out::msgInfo('post', print_r($uplFile, true));
            if (!@rename($uplFile, $destFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                $this->filePDA = $destFile;
                Out::valore($this->nameForm . '_CaricaPDA', $origFile);
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return;
        }
    }

    public function ImpostaEsitoConservazione($EsitoConservazione) {
        $Anapro_rec = $this->proLib->GetAnapro($this->rowidAnapro, 'rowid');
        if (!$Anapro_rec) {
            Out::msgInfo('Attenzione', "Registro non trovato.");
            return;
        }
        // Contorllo Estensione
        if ($this->filePDA) {
            $ext = strtolower(pathinfo($this->filePDA, PATHINFO_EXTENSION));
            if ($ext != 'p7m') {
                Out::msgInfo('Attenzione', "Il file PDA deve essere in formato p7m ");
                return;
            }
        }
        //Controllo contenuto?...

        /*
         * Inserisco AUDIT
         */
        $Audit = 'Variazione manuale di Esito Conservazione in ' . $EsitoConservazione . ' per il Registro: ' . $Anapro_rec['PRONUM'] . ' ' . $Anapro_rec['PROPAR'];
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        /*
         *  Lettura TABDAG
         */
        $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $this->rowidAnapro, proLibConservazione::CHIAVE_ESITO_ESITO, '', false, '', proLibConservazione::FONTE_DATI_ESITO_CONSERVAZIONE);
        try {
            $TabDag_rec['TDAGVAL'] = $EsitoConservazione;
            ItaDB::DBUpdate($this->PROT_DB, 'TABDAG', 'ROWID', $TabDag_rec);
        } catch (Exception $e) {
            Out::msgInfo('Attenzione', "Errore in aggiornamento TABDAG.<br> " . $e->getMessage());
            $this->ElencaRegistro();
            return;
        }
        if ($this->filePDA) {

            $datetime_esito = date('Ymd_His');
            $randNameRichiesta = itaLib::getRandBaseName() . ".xml.p7m";
            $NomeFileRichiesta = $FileInfo = "PDA_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml.p7m";
            $AllegatoDiServizio[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $this->filePDA,
                'FILENAME' => $randNameRichiesta,
                'FILEINFO' => $FileInfo,
                'DOCTIPO' => 'ALLEGATO',
                'DAMAIL' => '',
                'DOCNAME' => $NomeFileRichiesta,
                'DOCIDMAIL' => '',
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 1,
            );
            $risultato = $this->proLibAllegati->GestioneAllegati($this, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
            if (!$risultato) {
                Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
            } else {
                //Aggiorno TABDAG RDV
                $TabDag_rec = $this->proLibTabDag->GetTabdag('ANAPRO', 'chiave', $this->rowidAnapro, proLibConservazione::CHIAVE_ESITO_FILE, '', false, '', proLibConservazione::FONTE_DATI_ESITO_CONSERVAZIONE);
                try {
                    $TabDag_rec['TDAGVAL'] = $NomeFileRichiesta;
                    ItaDB::DBUpdate($this->PROT_DB, 'TABDAG', 'ROWID', $TabDag_rec);
                } catch (Exception $e) {
                    Out::msgInfo('Attenzione', "Errore in aggiornamento TABDAG.<br> " . $e->getMessage());
                    $this->ElencaRegistro();
                    return;
                }
                Out::msgInfo("Informazione", "Esito Conservazione aggiornato correttamente.");
            }
        }
        $this->ElencaRegistro();
    }

}

?>
