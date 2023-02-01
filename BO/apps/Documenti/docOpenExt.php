<?php

/**
 *
 * DOCUMENTI BASE
 *
 * PHP Version 5
 *
 * @category
 * @package    Documenti
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function docOpenExt() {
    $docOpenExt = new docOpenExt();
    $docOpenExt->parseEvent();
    return;
}

class docOpenExt extends itaModel {

    public $ITALWEB;
    public $docLib;
    public $devLib;
    public $utiEnte;
    public $nameForm = "docOpenExt";
    public $divGes = "docOpenExt_divGestione";
    public $gridDocumenti = "docOpenExt_gridDocumenti";
    private $documentiExt = array();

    function __construct() {
        parent::__construct();
        $this->documentiExt = App::$utente->getKey($this->nameForm . '_documentiExt');
        // Apro il DB
        try {
            $this->docLib = new docLib();
            $this->devLib = new devLib();
            $this->utiEnte = new utiEnte();
            $this->ITALWEB = $this->docLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_documentiExt', $this->documentiExt);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CaricaDocumentiExt();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $DocumentoExt = $this->documentiExt[$_POST['rowid']];
                        $this->ModificaDocumentoExt($DocumentoExt);
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        Out::msgQuestion("Cancellazione", "Confermi la cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'afterSaveCell':
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridDocumenti:
                        $this->AggiungiDocumentoExt();
                        break;
                }
                break;

            case 'exportTableToExcel':
                break;
            case 'printTableToHTML':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        $this->CaricaComandi();
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $rowid = $_POST[$this->gridDocumenti]['gridParam']['selarrrow'];
                        $DocumentoExt = $this->documentiExt[$rowid];
                        if ($DocumentoExt['ROWID']) {
                            $delete_Info = " Cancellazione Estensione: " . $DocumentoExt['CHIAVE'] . ' ' . $DocumentoExt['CONFIG'];
                            if (!$this->deleteRecord($this->ITALWEB, 'ENV_CONFIG', $DocumentoExt['ROWID'], $delete_Info, 'ROWID')) {
                                Out::msgStop("Attenzione", "Errore nella cancellazione estensione.");
                            }
                        }
                        $this->CaricaDocumentiExt();
                        break;

                    case $this->nameForm . '_AggiungiDocumentoExt':
                        $Ext = $_POST[$this->nameForm . '_EXT'];
                        $PrgExt = $_POST[$this->nameForm . '_PROGRAMMA_EXT'];
                        if (!$Ext || !$PrgExt) {
                            Out::msgStop("Attenzione", "Estensione e programma obbligatori.");
                            return false;
                        }
                        Out::closeCurrentDialog();
                        // Controllo esistenza
                        $Ext = strtoupper(str_replace('.', '', $Ext));
                        if ($this->devLib->getEnv_config('OPENDOCEXT', 'codice', $Ext, false)) {
                            Out::msgStop("Attenzione", "Il programma di esecuzione per i documenti con estensione $Ext è già definito.");
                            return false;
                        }
                        $env_config_rec = array();
                        $env_config_rec['CHIAVE'] = $Ext;
                        $env_config_rec['CLASSE'] = 'OPENDOCEXT';
                        $env_config_rec['CONFIG'] = $PrgExt;
                        try {
                            ItaDB::DBInsert($this->ITALWEB, 'ENV_CONFIG', 'ROWID', $env_config_rec);
                        } catch (Exception $exc) {
                            Out::msgStop('Attenzione', "Inserimento Comando Fallito." . $exc->getMessage());
                        }
                        $this->CaricaDocumentiExt();
                        break;

                    case $this->nameForm . '_AggiornaDocumentoExt':
                        $Ext = strtoupper($_POST[$this->nameForm . '_EXT']);
                        $PrgExt = $_POST[$this->nameForm . '_PROGRAMMA_EXT'];
                        if (!$PrgExt) {
                            Out::msgStop("Attenzione", "Programma obbligatorio.");
                            return false;
                        }
                        Out::closeCurrentDialog();
                        $env_config_rec = $this->devLib->getEnv_config('OPENDOCEXT', 'codice', $Ext, false);
                        if (!$env_config_rec) {
                            Out::msgStop("Attenzione", "Estensione non trovata.");
                        } else {
                            $env_config_rec['CONFIG'] = $PrgExt;
                            try {
                                ItaDB::DBUpdate($this->ITALWEB, 'ENV_CONFIG', 'ROWID', $env_config_rec);
                            } catch (Exception $exc) {
                                Out::msgStop('Attenzione', "Inserimento Comando Fallito." . $exc->getMessage());
                                return false;
                            }
                        }
                        $this->CaricaDocumentiExt();
                        break;
                    case $this->nameForm . '_AnnullaAggiungiDocumentoExt':
                    case $this->nameForm . '_AnnullaAggiornaDocumentoExt':
                        Out::closeCurrentDialog();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_documentiExt');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function CaricaDocumentiExt() {
        $this->documentiExt = $this->devLib->getEnv_config('OPENDOCEXT', 'codice', '', true);
        foreach ($this->documentiExt as $key => $estensione) {
            $this->documentiExt[$key]['ESTENSIONE'] = strtolower($estensione['CHIAVE']);
            // Decodifico il comando
            $Comando = $this->devLib->getEnv_config('OPENDOCCMD', 'codice', $estensione['CONFIG'], false);
            $ConfigCmd = unserialize($Comando['CONFIG']);
            $this->documentiExt[$key]['PROGRAMMA'] = $ConfigCmd['DESCRIZIONE'];
        }
        $this->CaricaTabellaDocumentiExt();
    }

    public function CaricaTabellaDocumentiExt() {
        $this->caricaGriglia($this->gridDocumenti, $this->documentiExt);
    }

    function caricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(10000);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    public function GetCampiDocumento() {
        $valori[] = array(
            'label' => array(
                'value' => "Estensione",
                'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_EXT',
            'name' => $this->nameForm . '_EXT',
            'type' => 'text',
            'class' => 'ita-edit required',
            'size' => '8',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Programma",
                'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_PROGRAMMA_EXT',
            'name' => $this->nameForm . '_PROGRAMMA_EXT',
            'type' => 'select',
            'class' => 'required'
        );
        return $valori;
    }

    public function AggiungiDocumentoExt() {
        $messaggio = "Imposta l'estensione del documento e il suo programma di esecuzione:";
        $valori = $this->GetCampiDocumento();
        Out::msgInput(
                'Nuova Apertura Documento', $valori
                , array(
            'Aggiungi' => array('id' => $this->nameForm . '_AggiungiDocumentoExt', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiungiDocumentoExt', 'model' => $this->nameForm)
                ), $this->nameForm, 'auto', '400', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>", "", false
        );
        $this->CreaComboProgrammi();
    }

    public function ModificaDocumentoExt($DocumentoExt) {
        $messaggio = "Indica il programma da eseguire:";
        $valori = $this->GetCampiDocumento();
        Out::msgInput(
                'Aggiorna Apertura Documento', $valori
                , array(
            'Aggiorna' => array('id' => $this->nameForm . '_AggiornaDocumentoExt', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAggiornaDocumentoExt', 'model' => $this->nameForm)
                ), $this->nameForm, 'auto', '400', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>", "", false
        );
        $this->CreaComboProgrammi();
        $Ext = strtolower($DocumentoExt['CHIAVE']);

        Out::valore($this->nameForm . '_EXT', $Ext);
        Out::disableField($this->nameForm . '_EXT');
        Out::valore($this->nameForm . '_PROGRAMMA_EXT', $DocumentoExt['CONFIG']);
        Out::setFocus('', $this->nameForm . '_PROGRAMMA_EXT');
    }

    public function CreaComboProgrammi() {
        $Programmi = $this->devLib->getEnv_config('OPENDOCCMD', 'codice', '', true);
        App::log($Programmi);
        foreach ($Programmi as $Programma) {
            $ConfigPrg = unserialize($Programma['CONFIG']);
            Out::select($this->nameForm . '_PROGRAMMA_EXT', 1, $Programma['CHIAVE'], "1", $ConfigPrg['DESCRIZIONE']);
        }
    }

}

?>