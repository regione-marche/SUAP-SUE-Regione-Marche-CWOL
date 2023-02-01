<?php

include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

function docImportDialog() {
    $docImportDialog = new docImportDialog();
    $docImportDialog->parseEvent();
    return;
}

/**
 * Dialog che consente di selezionare i documenti da esportare
 */
class docImportDialog extends itaModel {

    public $nameForm = "docImportDialog";
    public $returnModel;
    public $returnEvent;
    private $pathZip;
    private $docList;
    private $gridDocumenti;
    private $selectedDocs;
    private $docLib;

    function __construct() {
        parent::__construct();
        try {
            $this->gridDocumenti = $this->nameForm . '_gridDocumenti';
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->pathZip = App::$utente->getKey($this->nameForm . '_pathZip');
            $this->docList = App::$utente->getKey($this->nameForm . '_docList');
            $this->selectedDocs = App::$utente->getKey($this->nameForm . '_selectedDocs');
            $this->docLib = new docLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_pathZip', $this->pathZip);
            App::$utente->setKey($this->nameForm . '_docList', $this->docList);
            App::$utente->setKey($this->nameForm . '_selectedDocs', $this->selectedDocs);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openZip();
                break;
            case "onClick":
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        $this->conferma();
                        break;
                    case $this->nameForm . '_AnnullaImport':
                        $this->annullaImport();
                        break;
                    case $this->nameForm . '_ConfermaImport':
                        $this->confermaImport();
                        break;
                }
                break;
            case "onSelectCheckRow":
                $this->handleSelection($_POST['status'], $_POST['rowid']);
                break;
            case "onSelectCheckAll":
                $this->handleSelection($_POST['status']);
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    private function openZip() {
        // Crea cartella temporanea per elaborazione
        $tmpPathKey = 'import-docs' . uniqid();
        $tmpPath = itaLib::createAppsTempPath($tmpPathKey);

        // Scompatta zip nella cartella temporanea
        $zip = new ZipArchive;
        if ($zip->open($this->getPathZip()) === true) {
            $zip->extractTo($tmpPath);
            $zip->close();
        } else {
            Out::msgStop("Errore", "Errore apertura file zip");

            // Elimina cartella temporanea per elaborazione
            itaLib::deleteAppsTempPath($tmpPathKey);

            return;
        }

        // Scorre file all'interno della cartella
        $this->docList = array();
        $files = glob($tmpPath . '/*.xml');
        foreach ($files as $file) {
            $doc = $this->parseDoc($file);
            if ($doc) {
                $this->docList[] = $doc;
            }
        }

        // Popola griglia con i documenti caricati
        $this->populateGrid();

        // Elimina cartella temporanea per elaborazione
        itaLib::deleteAppsTempPath($tmpPathKey);
    }

    private function parseDoc($path) {
        // Carica documento        
        try {
            $xml = $this->xml2array($path);
            $doc = json_decode(json_encode($xml), 1);   // Converte oggetto simplexml in array
            $doc['SELECTED'] = false;

            // Controlla se l'elemento è già stato importato:
            // in caso positivo, evidenzia il codice
            $toCheck = $this->docLib->getDocumenti($doc['CODICE']);
            $doc['IS_NEW_DOC'] = true;
            $doc['CODICE_FMT'] = $doc['CODICE'];
            if ($toCheck) {
                $doc['CODICE_FMT'] = '<span style="color: #F00; background-color: #FFFF00;">' . $doc['CODICE'] . '</span>';
                $doc['IS_NEW_DOC'] = false;
            }

            if (!$doc) {
                return false;
            }
            return $doc;
        } catch (Exception $ex) {
            return false;
        }
    }

    private function populateGrid() {
        $ita_grid01 = new TableView($this->gridDocumenti, array('arrayTable' => $this->docList,
            'rowIndex' => 'ROWID'));
        $ita_grid01->setSortOrder('CODICE');
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(50000);
        TableView::enableEvents($this->gridDocumenti);
        TableView::clearGrid($this->gridDocumenti);
        $ita_grid01->getDataPage('json');
    }

    private function handleSelection($status, $rowid = null) {
        if ($rowid !== null) {
            $this->docList[$rowid]['SELECTED'] = $status;
        } else {
            array_walk($this->docList, array($this, 'changeRowStatus'), $status);
        }
    }

    private function changeRowStatus(&$item, $key, $status) {
        $item['SELECTED'] = $status;
    }

    private function filterSelected($item) {
        return $item['SELECTED'] == true;
    }

    private function filterDocsToOverwrite($item) {
        return $item['IS_NEW_DOC'] == false;
    }

    private function conferma() {
        // Filtra solamente le righe selezionate
        $this->selectedDocs = array_filter($this->docList, array($this, 'filterSelected'));

        // Se nessuna riga selezionata, notifica con un messaggio a video
        if (count($this->selectedDocs) === 0) {
            Out::msgInfo("Attenzione", "Nessun documento selezionato per importazione");
            return;
        }

        // Controlla se tra i documenti selezionati ci sono elementi da aggiornare
        $docsToOverwrite = array_filter($this->selectedDocs, array($this, 'filterDocsToOverwrite'));
        if (count($docsToOverwrite) > 0) {
            Out::msgQuestion("Conferma importazione", "Esistono documenti da aggiornare. I documenti esistenti verranno aggiornati. Continuare?", array(
                'Annulla' => array('id' => $this->nameForm . '_AnnullaImport', 'model' => $this->nameForm),
                'Conferma' => array('id' => $this->nameForm . '_ConfermaImport', 'model' => $this->nameForm)
                    )
            );
            return;
        }

        // Gestione ritorno
        $this->confermaImport();
    }

    private function confermaImport() {
        Out::closeDialog($this->nameForm);
        $formObj = itaModel::getInstance($this->returnModel);
        if (!$formObj) {
            Out::msgStop("Errore", "Apertura finestra gestione documenti fallita");
            return;
        }
        $formObj->setEvent($this->returnEvent);
        $_POST['selectedDocs'] = $this->selectedDocs;
        $formObj->parseEvent();
        $this->close = true;
    }

    private function annullaImport() {
        $this->close();
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_pathZip');
        App::$utente->removeKey($this->nameForm . '_docList');
        App::$utente->removeKey($this->nameForm . '_selectedDocs');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function getPathZip() {
        return $this->pathZip;
    }

    public function setPathZip($pathZip) {
        $this->pathZip = $pathZip;
    }

    private function xml2array($path) {
        $xml = simplexml_load_file($path);
        if (!$xml) {
            return false;
        }
        return $xml;
    }

}
