<?php

/**
 *
 * Elenco Notifiche
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    05.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

function envGestNotice() {
    $envGestNotice = new envGestNotice();
    $envGestNotice->parseEvent();
    return;
}

class envGestNotice extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = "envGestNotice";
    public $gridNotifiche = "envGestNotice_gridNotifiche";
    public $elencoNotifiche;
    public $envLib;
    public $testoFiltro = '';

    function __construct() {
        parent::__construct();
        $this->envLib = new envLib();
        $this->ITALWEB_DB = $this->envLib->getITALWEB_DB();
        $this->elencoNotifiche = App::$utente->getKey($this->nameForm . "_elencoNotifiche");
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_elencoNotifiche", $this->elencoNotifiche);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                Out::valore($this->nameForm . "_Dal_periodo", date('Ymd', strtotime('-30 day', strtotime(date("Ymd")))));
                Out::valore($this->nameForm . "_Al_periodo", date("Ymd"));
                TableView::enableEvents($this->gridNotifiche);
                TableView::reload($this->gridNotifiche);
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridNotifiche:
                        $rowid = $this->elencoNotifiche[$_POST['rowid']]['ROWID'];
                        $model = 'envViewerNotice';
                        itaLib::openForm($model);
                        $envViewerNotice = itaModel::getInstance($model);
                        $envViewerNotice->setReturnModel('');
                        $envViewerNotice->setReturnEvent('');
                        $envViewerNotice->setReturnId('');
                        $_POST = array();
                        $_POST['rowid'] = $rowid;
                        $envViewerNotice->setEvent('openform');
                        $envViewerNotice->parseEvent();
                        $this->caricaElenco();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridNotifiche:
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridNotifiche:
                        break;
                }
                break;
            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridNotifiche:
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $this->creaSql(), "Ente" => $ParametriEnte_rec['DENOMINAZIONE'], "Titolo" => "REGISTRO AVVISI", "Filtro" => $this->testoFiltro);
                        $itaJR->runSQLReportPDF($this->ITALWEB_DB, 'envGestNotice', $parameters);
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridNotifiche:
                        TableView::clearGrid($this->gridNotifiche);
                        $this->caricaElenco('2');
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ApplicaFiltri':
                        $this->caricaElenco();
                        break;
                    case $this->nameForm . '_SegnaLetti':
                        $this->selezionaAvvisi($this->nameForm, 'returnSelezioneAvvisi', 'segnaLetti', 'Segna gli avvisi selezionati come letti.');
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TipoAvvisi':
                    case $this->nameForm . '_VediAvvisi':
                        TableView::enableEvents($this->gridNotifiche);
                        TableView::reload($this->gridNotifiche);
                        break;
                }
                break;
            case 'returnSelezioneAvvisi';
                $chiavi = explode(',', $_POST['retKey']);
                if (!$_POST['retKey'] || count($chiavi) === 0) {
                    break;
                }
                foreach ($chiavi as $rowid) {
                    $env_notifiche = $this->envLib->getGenericTab("SELECT * FROM ENV_NOTIFICHE WHERE ROWID = $rowid", false);
                    $utente = App::$utente->getKey('nomeUtente');
                    if ($env_notifiche['UTEDEST'] == $utente) {
                        $data = date("Ymd");
                        $ora = date("H:i:s");
                        if ($env_notifiche['DATAVIEW'] == '') {
                            $env_notifiche['DATAVIEW'] = $data;
                            $env_notifiche['ORAVIEW'] = $ora;
                            $update_Info = 'Oggetto notifica viewed: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
                            $this->updateRecord($this->ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $update_Info);
                        }
                    }
                }
                TableView::enableEvents($this->gridNotifiche);
                TableView::reload($this->gridNotifiche);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_elencoNotifiche');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function CreaCombo() {
//        Out::select($this->nameForm . "_TipoAvvisi", 1, "", "0", "Tutti");
        Out::select($this->nameForm . "_TipoAvvisi", 1, "1", "0", "Inviati");
        Out::select($this->nameForm . "_TipoAvvisi", 1, "2", "1", "Ricevuti");

        Out::select($this->nameForm . "_VediAvvisi", 1, "", "0", "Tutti");
        Out::select($this->nameForm . "_VediAvvisi", 1, "1", "0", "Letti");
        Out::select($this->nameForm . "_VediAvvisi", 1, "2", "1", "Non Letti");
    }

    private function creaSql() {
        $order_by = isset($_POST['sidx']) && $_POST['sidx'] !== '' ? $_POST['sidx'] : 'DATADELIV';
        $order = isset($_POST['sord']) && $_POST['sord'] !== '' ? strtoupper($_POST['sord']) : 'DESC';

        if ($order_by == 'LETTO') {
            $order_by = 'DATAVIEW';
        }

        $utente = App::$utente->getKey('nomeUtente');
        $where = " WHERE 1 = 1 ";
        TableView::showCol($this->gridNotifiche, 'UTEINS');
        TableView::showCol($this->gridNotifiche, 'UTEDEST');
        switch ($this->formData[$this->nameForm . '_TipoAvvisi']) {
            case "1":
                $where .= " AND UTEINS='$utente' ";
                $this->testoFiltro = "Avvisi Inviati";
                //TableView::hideCol($this->gridNotifiche, 'UTEINS');
                break;
            case "2":
                $where .= " AND UTEDEST = '$utente' ";
                $this->testoFiltro = "Avvisi Ricevuti";
                //TableView::hideCol($this->gridNotifiche, 'UTEDEST');
                break;
            default:
                $where .= " AND (UTEDEST = '$utente' OR UTEINS='$utente') ";
                $this->testoFiltro = "Avvisi Inviati e Avvisi Ricevuti";
                break;
        }
        //WHERE 
        $al = date("Ymd");
        $dal = date('Ymd', strtotime('-30 day', strtotime($al)));
        $alTesto = date("d/m/Y");
        $dalTesto = date('d/m/Y', strtotime('-30 day', strtotime($al)));
        if ($this->formData[$this->nameForm . '_Dal_periodo']) {
            $dal = $this->formData[$this->nameForm . '_Dal_periodo'];
            $dalTesto = date('d/m/Y', strtotime($dal));
        }
        if ($this->formData[$this->nameForm . '_Al_periodo']) {
            $al = $this->formData[$this->nameForm . '_Al_periodo'];
            $alTesto = date('d/m/Y', strtotime($al));
        }
        $this->testoFiltro .= ", periodo dal $dalTesto al $alTesto";

        $where .= " AND DATAINS BETWEEN $dal AND $al ";
        $where .= $this->whereVisLettiNonLetti();
        $whreAltriFiltri = $this->whereAltriFiltri();
        $sql = "SELECT * FROM ENV_NOTIFICHE $where $whreAltriFiltri ORDER BY $order_by $order";
        return $sql;
    }

    private function whereVisLettiNonLetti() {
        $lett = '';
        if ($this->formData[$this->nameForm . '_VediAvvisi'] == "1") {
            $lett = " AND DATAVIEW<>'' ";
            $this->testoFiltro .= ", Letti";
        } else if ($this->formData[$this->nameForm . '_VediAvvisi'] == "2") {
            $this->testoFiltro .= ", Non Letti";
            $lett .= " AND DATAVIEW = ''";
        } else {
            $this->testoFiltro .= ", Letti, Non Letti";
        }
        return $lett;
    }

    private function whereAltriFiltri() {
        $where = '';
        $altriFiltri = '';
        if ($this->formData['OGGETTO']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('OGGETTO') . " LIKE '%" . addslashes(strtoupper($this->formData['OGGETTO'])) . "%'";
            $altriFiltri .= " Oggetto: '" . strtoupper($this->formData['OGGETTO']) . "'";
        }
        if ($this->formData['TESTO']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('TESTO') . " LIKE '%" . addslashes(strtoupper($this->formData['TESTO'])) . "%'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Testo: '" . strtoupper($this->formData['TESTO']) . "'";
        }
        if ($this->formData['UTEINS']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('UTEINS') . " LIKE '%" . addslashes(strtoupper($this->formData['UTEINS'])) . "%'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Mittente: '" . strtoupper($this->formData['UTEINS']) . "'";
        }
        if ($this->formData['UTEDEST']) {
            $where .= " AND " . $this->ITALWEB_DB->strUpper('UTEDEST') . " LIKE '%" . addslashes(strtoupper($this->formData['UTEDEST'])) . "%'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Destinatario: '" . strtoupper($this->formData['UTEDEST']) . "'";
        }
        if ($this->formData['DATAINS']) {
            if (strlen($this->formData['DATAINS']) == 8) {
                $data = substr($this->formData ['DATAINS'], 4) . substr($this->formData ['DATAINS'], 2, 2) . substr($this->formData ['DATAINS'], 0, 2);
            } else if (strlen($this->formData['DATAINS']) == 10) {
                $data = substr($this->formData ['DATAINS'], 6) . substr($this->formData ['DATAINS'], 3, 2) . substr($this->formData ['DATAINS'], 0, 2);
            }
            if ($data) {
                $where .= " AND DATAINS = '" . $data . "'";
                if ($altriFiltri) {
                    $altriFiltri .= ", ";
                }
                $altriFiltri .= " Data Inserimento: " . $this->formData['DATAINS'];
            }
        }
        if ($this->formData['ORAINS']) {
            $where .= " AND ORAINS LIKE '" . $this->formData['ORAINS'] . "'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Ora Inserimento: " . $this->formData['ORAINS'];
        }
        if ($this->formData['DATADELIV']) {
            if (strlen($this->formData['DATADELIV']) == 8) {
                $data = substr($this->formData ['DATADELIV'], 4) . substr($this->formData ['DATADELIV'], 2, 2) . substr($this->formData ['DATADELIV'], 0, 2);
            } else if (strlen($this->formData['DATADELIV']) == 10) {
                $data = substr($this->formData ['DATADELIV'], 6) . substr($this->formData ['DATADELIV'], 3, 2) . substr($this->formData ['DATADELIV'], 0, 2);
            }
            if ($data) {
                $where .= " AND DATADELIV = '" . $data . "'";
                if ($altriFiltri) {
                    $altriFiltri .= ", ";
                }
                $altriFiltri .= " Data Consegna: " . $this->formData['DATADELIV'];
            }
        }
        if ($this->formData['ORADELIV']) {
            $where .= " AND ORADELIV LIKE '" . $this->formData['ORADELIV'] . "'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Ora Consegna: " . $this->formData['ORADELIV'];
        }
        if ($this->formData['DATAVIEW']) {
            if (strlen($this->formData['DATAVIEW']) == 8) {
                $data = substr($this->formData ['DATAVIEW'], 4) . substr($this->formData ['DATAVIEW'], 2, 2) . substr($this->formData ['DATAVIEW'], 0, 2);
            } else if (strlen($this->formData['DATAVIEW']) == 10) {
                $data = substr($this->formData ['DATAVIEW'], 6) . substr($this->formData ['DATAVIEW'], 3, 2) . substr($this->formData ['DATAVIEW'], 0, 2);
            }
            if ($data) {
                $where .= " AND DATAVIEW = '" . $data . "'";
                if ($altriFiltri) {
                    $altriFiltri .= ", ";
                }
                $altriFiltri .= " Data Visualizzazione: " . $this->formData['DATAVIEW'];
            }
        }
        if ($this->formData['ORAVIEW']) {
            $where .= " AND ORAVIEW LIKE '" . $this->formData['ORAVIEW'] . "'";
            if ($altriFiltri) {
                $altriFiltri .= ", ";
            }
            $altriFiltri .= " Ora Visualizzazione: " . $this->formData['ORAVIEW'];
        }

        if ($altriFiltri) {
            $this->testoFiltro .= "<br>Altri Filtri:" . $altriFiltri;
        }

        return $where;
    }

    private function caricaElenco($tipo = '1') {

        $utente = App::$utente->getKey('nomeUtente');
        // chiusagray-24x24
        $cssBase = '<div ><span style="height:24px;background-size:75%; background-repeat: no-repeat; display:inline-block;" class="ita-icon-chiusagreen-32x32"></span>';
        $cssInviata = '<span title ="Inviato" style="display:inline-block; position:relative; margin-left:-19px; top:2px; " class="ita-tooltip ita-icon ita-icon-arrow-green-dx-16x16"></span></div>';
        $cssRicevuta = '<span title ="Ricevuto" style="display:inline-block; position:relative; margin-left:-19px; top:2px; " class="ita-tooltip ita-icon ita-icon-arrow-red-sx-16x16"></span></div>';
        $cssStesso = '<span title ="Inviato a te stesso" style="display:inline-block; position:relative; margin-left:-19px; top:2px; " class="ita-tooltip ita-icon ita-icon-user-16x16"></span></div>';

        $this->elencoNotifiche = $this->envLib->getGenericTab($this->creaSql(), true);
        foreach ($this->elencoNotifiche as $key => $Notifica) {
            if ($Notifica['UTEINS'] == $Notifica['UTEDEST']) {
                $this->elencoNotifiche[$key]['ICONA'] = $cssBase . $cssStesso;
            } else if ($Notifica['UTEINS'] == $utente) {
                $this->elencoNotifiche[$key]['ICONA'] = $cssBase . $cssInviata;
            } else {
                $this->elencoNotifiche[$key]['ICONA'] = $cssBase . $cssRicevuta;
            }

            if ($Notifica['DATAVIEW']) {
                $this->elencoNotifiche[$key]['LETTO'] = '<span class="ita-icon ita-icon-apertagray-24x24"></span>';
            }

            if ($Notifica['MAILTOSEND'] == 0) {
                $MessaggioTooltip = '<span class="ita-tooltip" title="Questo è il consiglio visualizzato nella cella" >Messaggio non da inviare</span>';
                $this->elencoNotifiche[$key]['STATO'] = "<div class=\"ita-html\">" . '<span class="ita-tooltip ita-icon ita-icon-check-grey-24x24"> ' . $MessaggioTooltip . '</span>' . "</div>"; // non da inviare
            } elseif ($Notifica['MAILDATE'] != '') {
                $MessaggioTooltip = '<span class="ita-tooltip" title="Questo è il consiglio visualizzato nella cella" >Messaggio Inviato: ' . $this->elencoNotifiche[$key]['MAILSENDMSG'] . '</span>';
                $this->elencoNotifiche[$key]['STATO'] = "<div class=\"ita-html\">" . '<span class="ita-tooltip ita-icon ita-icon-check-green-24x24"> ' . $MessaggioTooltip . '</span>' . "</div>"; // inviato
            } elseif ($Notifica['MAILDATE'] == '') {
                $MessaggioTooltip = '<span class="ita-tooltip" title="Questo è il consiglio visualizzato nella cella" >Messaggio non Inviato: ' . $this->elencoNotifiche[$key]['MAILSENDMSG'] . '</span>';
                $this->elencoNotifiche[$key]['STATO'] = "<div class=\"ita-html\">" . '<span class="ita-tooltip ita-icon ita-icon-check-red-24x24"> ' . $MessaggioTooltip . '</span>' . "</div>"; // // non inviato
            }

            $this->elencoNotifiche[$key]['OGGETTO'] = "<div style=\"height:35px;overflow:auto; \" class=\"ita-Wordwrap\">" . $Notifica['OGGETTO'] . "</div>";
            $this->elencoNotifiche[$key]['TESTO'] = "<div style=\"height:35px;overflow:auto; \" class=\"ita-Wordwrap\">" . $Notifica['TESTO'] . "</div>";
        }
        $this->caricaGriglia($this->gridNotifiche, $this->elencoNotifiche, $tipo);

        if ($this->formData[$this->nameForm . '_VediAvvisi'] == "2") {
            Out::show($this->nameForm . "_SegnaLetti");
        } else {
            Out::hide($this->nameForm . "_SegnaLetti");
        }
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

    private function selezionaAvvisi($returnModel, $returnEvent, $retId, $msgDetail) {
        if(count($this->elencoNotifiche) > 500){
            Out::msgInfo("Attenzione: Selzione Avvisi.","Elenco troppo grande, selezionare un perido di tempo ridotto.");
            return;
        }
        $colNames = array(
            "Rowid",
            "Data",
            "Ora",
            "Oggetto",
            "Mittente",
            "Destinatario"
        );
        $colModel = array(
            array("name" => 'ROWID', "width" => 1, "hidden" => "true", "key" => "true"),
            array("name" => 'DATAINS', "width" => 75, "formatter" => "eqdate"),
            array("name" => 'ORAINS', "width" => 70),
            array("name" => 'OGGETTO', "width" => 300),
            array("name" => 'UTEINS', "width" => 120),
            array("name" => 'UTEDEST', "width" => 120)
        );

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => $caption,
            "width" => 800,
            "height" => 400,
            "multiselect" => 'true',
            "pginput" => 'false',
            "pgbuttons" => 'false',
            "rowNum" => '20000',
            "rowList" => '[]',
            "filterToolbar" => 'false',
            "arrayTable" => $this->elencoNotifiche,
            "colNames" => $colNames,
            "colModel" => $colModel
        );
//        $filterName = array();
//        foreach ($colModel as $Colonna) {
//            $filterName[] = $Colonna['name'];
//        }

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        //$_POST['filterName'] = $filterName;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $retId;
        $_POST['returnKey'] = 'retKey';
        $_POST['msgDetail'] = $msgDetail;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>
