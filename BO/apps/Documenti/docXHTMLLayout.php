<?php

/**
 *
 * DOCUMENTI BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    Documenti
 * @author     Michele Moscioni
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    17.01.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function docXHTMLLayout() {
    $docXHTMLLayout = new docXHTMLLayout();
    $docXHTMLLayout->parseEvent();
    return;
}

class docXHTMLLayout extends itaModel {

    public $ITALWEB;
    public $docLib;
    public $utiEnte;
    public $nameForm = "docXHTMLLayout";
    public $divGes = "docXHTMLLayout_divGestione";
    public $divRis = "docXHTMLLayout_divRisultato";
    public $divRic = "docXHTMLLayout_divRicerca";
    public $gridDocumenti = "docXHTMLLayout_gridDocumenti";
    public $aggiornaFile = false;
    public $classificazione;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->docLib = new docLib();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB = $this->docLib->getITALWEB();
            $this->aggiornaFile = App::$utente->getKey($this->nameForm . '_aggiornaFile');
            $this->classificazione = App::$utente->getKey($this->nameForm . '_classificazione');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_aggiornaFile', $this->aggiornaFile);
            App::$utente->setKey($this->nameForm . '_classificazione', $this->classificazione);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->classificazione = $_POST['classificazione'];
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $this->Dettaglio($_POST['rowid']);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $this->Dettaglio($_POST['rowid']);
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'exportTableToExcel':
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($this->gridDocumenti, array(
                    'sqlDB' => $this->ITALWEB,
                    'sqlQuery' => $sql));
                $ita_grid01->setSortIndex('OGGETTO');
                $ita_grid01->exportXLS('', 'documenti.xls');
                break;
            case 'printTableToHTML':
                $parametriEnte_rec = $this->utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->CreaSql() . " ORDER BY CLASSIFICAZIONE, CODICE", "Ente" => $parametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALWEB, 'docDocumenti', $parameters);
                break;
            case 'onClickTablePager':
                $tableSortOrder = $_POST['sord'];
                $sql = $this->CreaSql();
                $ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->ITALWEB, 'sqlQuery' => $sql));
                $ita_grid01->setPageNum($_POST['page']);
                $ita_grid01->setPageRows($_POST['rows']);
                $ita_grid01->setSortIndex($_POST['sidx']);
                $ita_grid01->setSortOrder($_POST['sord']);
                $result_tab = $ita_grid01->getDataArray();
                $ita_grid01->getDataPageFromArray('json', $result_tab);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridDocumenti, array(
                            'sqlDB' => $this->ITALWEB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(15);
                        $ita_grid01->setSortIndex('CODICE');
                        $ita_grid01->setSortOrder('asc');
                        $result_tab = $ita_grid01->getDataArray();
                        if (!$ita_grid01->getDataPageFromArray('json', $result_tab)) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridDocumenti);
                        }
                        break;
                    case $this->nameForm . '_Preview':
                        $documenti_rec = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['ROWID'], 'rowid');
                        $this->previewXHTML($documenti_rec);
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '1');
                        Out::hide($this->divRic);
                        Out::hide($this->divRis);
                        Out::show($this->divGes);
                        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlHeader");');
                        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlFooter");');
                        $this->AzzeraVariabili();
                        $this->Nascondi();
                        Out::show($this->nameForm . '_Aggiungi');
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[CODICE]');
                        if ($this->classificazione != '') {
                            Out::hide($this->nameForm . '_divClassificazione');
                            Out::valore($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', $this->classificazione);
                        }
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $documenti_rec = $this->docLib->getDocumenti($_POST[$this->nameForm . '_DOC_DOCUMENTI']['CODICE']);
                        if (!$documenti_rec) {
                            $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
                            $documenti_rec['DATAREV'] = date('Ymd');
                            $documenti_rec['NUMREV'] = 1;
                            $documenti_rec['TIPO'] = 'XLAYOUT';
                            $documenti_rec['URI'] = '';
                            $documenti_rec['CONTENT'] = $this->setSerializedContent();
                            $insert_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
                            if ($this->insertRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec, $insert_Info)) {
                                $documenti_rec = $this->docLib->getDocumenti($documenti_rec['CODICE']);
                                $this->Dettaglio($documenti_rec['ROWID']);
                            }
                        } else {
                            Out::msgInfo("Codice già  presente", "Inserire un nuovo codice.");
                            Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[CODICE]');
                        }
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
                        $documenti_rec['DATAREV'] = date('Ymd');
                        $documenti_rec['NUMREV']+=1;
                        $documenti_rec['URI'] = '';
                        $documenti_rec['CONTENT'] = $this->setSerializedContent();
                        $metadati = array();
                        $metadati['ORIENTATION'] = $_POST[$this->nameForm . '_Orientamento'];
                        $metadati['FORMAT'] = $_POST[$this->nameForm . '_formato'];
                        $metadati['MARGIN-TOP'] = $_POST[$this->nameForm . '_Superiore'];
                        $metadati['MARGIN-HEADER'] = $_POST[$this->nameForm . '_Intestazione'];
                        $metadati['MARGIN-LEFT'] = $_POST[$this->nameForm . '_Sinistro'];
                        $metadati['MARGIN-RIGHT'] = $_POST[$this->nameForm . '_Destro'];
                        $metadati['MARGIN-BOTTOM'] = $_POST[$this->nameForm . '_Inferiore'];
                        $metadati['MARGIN-FOOTER'] = $_POST[$this->nameForm . '_Piepagina'];
                        $documenti_rec['METADATI'] = serialize($metadati);

                        $update_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
                        if ($this->updateRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec, $update_Info)) {
                            $this->Dettaglio($documenti_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $documenti_rec = $_POST[$this->nameForm . '_DOC_DOCUMENTI'];
                        try {
                            $delete_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
                            if ($this->deleteRecord($this->ITALWEB, 'DOC_DOCUMENTI', $documenti_rec['ROWID'], $delete_Info)) {
                                $this->OpenRicerca();
                            }
                        } catch (Exception $e) {
                            Out::msgStop("Errore in Cancellazione su DOCUMENTI BASE", $e->getMessage());
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_codice':
                        $codice = $_POST[$this->nameForm . '_codice'];
                        if ($codice != '') {
                            $documenti_rec = $this->docLib->getDocumenti($codice);
                            if ($documenti_rec) {
                                $this->Dettaglio($documenti_rec['ROWID']);
                            }
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_aggiornaFile');
        App::$utente->removeKey($this->nameForm . '_classificazione');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_codice');
        Out::valore($this->nameForm . '_tipo', '');
        if ($this->classificazione != '') {
            Out::hide($this->nameForm . '_divClass');
            Out::valore($this->nameForm . '_classificazione', $this->classificazione);
        }
    }

    function AzzeraVariabili() {
        $this->aggiornaFile = false;
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridDocumenti);
        TableView::clearGrid($this->gridDocumenti);
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_divHtmlHeader');
        Out::hide($this->nameForm . '_divHtmlFooter');
        Out::hide($this->nameForm . '_Preview');
    }

    public function CreaSql() {
        $sql = "SELECT * FROM DOC_DOCUMENTI WHERE ROWID=ROWID AND TIPO = 'XLAYOUT'";
        if ($_POST[$this->nameForm . '_codice'] != "") {
            $codice = $_POST[$this->nameForm . '_codice'];
            $sql .= " AND CODICE = '" . $codice . "'";
        }
        if ($_POST[$this->nameForm . '_oggetto'] != "") {
            $sql .= " AND OGGETTO LIKE '%" . addslashes($_POST[$this->nameForm . '_oggetto']) . "%'";
        }
        if ($_POST[$this->nameForm . '_classificazione'] != "") {
            $codice = $_POST[$this->nameForm . '_classificazione'];
            $sql .= " AND CLASSIFICAZIONE = '" . $codice . "'";
        }
//        App::log($sql);
        return $sql;
    }

    public function Dettaglio($indice) {
        $documenti_rec = $this->docLib->getDocumenti($indice, 'rowid');

        $metadati = unserialize($documenti_rec['METADATI']);
        unset($documenti_rec['METADATI']);

        $open_Info = 'Oggetto: ' . $documenti_rec['CODICE'] . " " . $documenti_rec['OGGETTO'];
        $this->openRecord($this->ITALWEB, 'DOC_DOCUMENTI', $open_Info);
        $this->Nascondi();
        Out::valori($documenti_rec, $this->nameForm . '_DOC_DOCUMENTI');
        $this->getSerializedContent($documenti_rec['CONTENT']);

        switch ($metadati['ORIENTATION']) {
            case "V":
                Out::attributo($this->nameForm . '_radioVerticale', 'checked', 0, 'checked');
                Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 1);
                break;
            case "O":
                Out::attributo($this->nameForm . '_radioverticale', 'checked', 1);
                Out::attributo($this->nameForm . '_radioOrizzontale', 'checked', 0, 'checked');
                break;
        }
        Out::valore($this->nameForm . '_Orientamento', $metadati['ORIENTATION']);
        Out::valore($this->nameForm . '_formato', $metadati['FORMAT']);
        Out::valore($this->nameForm . '_Superiore', $metadati['MARGIN-TOP']);
        Out::valore($this->nameForm . '_Intestazione', $metadati['MARGIN-HEADER']);
        Out::valore($this->nameForm . '_Sinistro', $metadati['MARGIN-LEFT']);
        Out::valore($this->nameForm . '_Destro', $metadati['MARGIN-RIGHT']);
        Out::valore($this->nameForm . '_Inferiore', $metadati['MARGIN-BOTTOM']);
        Out::valore($this->nameForm . '_Piepagina', $metadati['MARGIN-FOOTER']);

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Preview');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        Out::attributo($this->nameForm . '_DOC_DOCUMENTI[CODICE]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_DOC_DOCUMENTI[OGGETTO]');
        TableView::disableEvents($this->gridDocumenti);

        $this->aggiornaFile = false;
        if ($this->classificazione != '') {
            Out::hide($this->nameForm . '_divClassificazione');
            Out::valore($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', $this->classificazione);
        }
        if ($valore != "") {
            Out::valore($this->nameForm . '_varHtml', $valore);
        }
        Out::show($this->nameForm . '_divHtmlHeader');
        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlHeader");');
        Out::show($this->nameForm . '_divHtmlFooter');
        Out::codice('tinyActivate("' . $this->nameForm . '_varHtmlFooter");');

        return;
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_formato', 1, 'A4', '0', 'A4');
        Out::select($this->nameForm . '_formato', 1, 'A3', '0', 'A3');

        $cla_tab = $this->docLib->getClassificazioni();
        if ($cla_tab) {
            foreach ($cla_tab as $cla_rec) {
                Out::select($this->nameForm . '_classificazione', 1, $cla_rec['CLASSIFICAZIONE'], '0', $cla_rec['CLASSIFICAZIONE']);
                Out::select($this->nameForm . '_DOC_DOCUMENTI[CLASSIFICAZIONE]', 1, $cla_rec['CLASSIFICAZIONE'], '0', $cla_rec['CLASSIFICAZIONE']);
            }
        }
    }

    function setSerializedContent() {
        return serialize(array(
            'XHTML_HEADER' => $_POST[$this->nameForm . '_varHtmlHeader'],
            'XHTML_FOOTER' => $_POST[$this->nameForm . '_varHtmlFooter']
        ));
    }

    function getSerializedContent($content) {
        $unserContent = unserialize($content);
        Out::valore($this->nameForm . '_varHtmlHeader', $unserContent['XHTML_HEADER']);
        Out::valore($this->nameForm . '_varHtmlFooter', $unserContent['XHTML_FOOTER']);
    }

    function previewXHTML($Doc_documenti_rec, $extraParam = array()) {
        if (!$Doc_documenti_rec) {
            return;
        }

        $unserContent = unserialize($Doc_documenti_rec['CONTENT']);
        $metadatiLayout = unserialize($Doc_documenti_rec['METADATI']);
        if ($metadatiLayout) {
            $headerContent = $unserContent['XHTML_HEADER'];
            $footerContent = $unserContent['XHTML_FOOTER'];
            $orientation = $metadatiLayout['ORIENTATION'];
            $format = $metadatiLayout['FORMAT'];
            $marginTop = $metadatiLayout['MARGIN-TOP'] + $metadatiLayout['MARGIN-HEADER'];
            $marginHeader = $metadatiLayout['MARGIN-HEADER'];
            $marginLeft = $metadatiLayout['MARGIN-LEFT'];
            $marginRight = $metadatiLayout['MARGIN-RIGHT'];
            $marginBottom = $metadatiLayout['MARGIN-BOTTOM'] + $metadatiLayout['MARGIN-FOOTER'];
            $marginFooter = $metadatiLayout['MARGIN-FOOTER'];
            if ($orientation == "O") {
                $orientation = "landscape";
            } else if ($orientation == "V") {
                $orientation = "portrait";
            }
        }

        $itaSmarty = new itaSmarty();
        $itaSmarty->assign('documentbody', "<span></span>");
        $itaSmarty->assign('documentheader', $headerContent);
        $itaSmarty->assign('documentfooter', $footerContent);
        $itaSmarty->assign('headerHeight', $marginHeader);
        $itaSmarty->assign('footerHeight', $marginFooter);
        $itaSmarty->assign('marginTop', $marginTop);
        $itaSmarty->assign('marginBottom', $marginBottom);
        $itaSmarty->assign('marginLeft', $marginLeft);
        $itaSmarty->assign('marginRight', $marginRight);
        $itaSmarty->assign('pageFormat', $format);
        $itaSmarty->assign('pageOrientation', $orientation);
        //eventuali parametri extra
        foreach ($extraParam as $campo => $valore) {
            $itaSmarty->assign($campo, $valore);
        }

        $documentLayout = itaLib::getAppsTempPath() . '/documentlayout.xhtml';
        $layoutTemplate = App::getConf('modelBackEnd.php') . '/' . App::getPath('appRoute.doc') . "/layoutTemplate.xhtml";
        if (!copy($layoutTemplate, $documentLayout)) {
            Out::msgStop("Errore", "Copia template layout Fallita");
            return false;
        }
        $contentPreview = utf8_encode($itaSmarty->fetch($documentLayout));
        $documentPreview = itaLib::getAppsTempPath() . '/documentpreview.xhtml';
        $pdfPreview = itaLib::getAppsTempPath() . '/documentpreview.pdf';

        if (!file_put_contents($documentPreview, $contentPreview)) {
            Out::msgStop("Errore", "Creazione $documentPreview Fallita");
            return false;
        }
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaH2P/itaH2P.jar ' . $documentPreview . ' ' . $pdfPreview;
        passthru($command, $return_var);
        Out::openDocument(utiDownload::getUrl(
                        App::$utente->getKey('TOKEN') . "-preview.pdf", $pdfPreview
                )
        );
    }

}

?>
