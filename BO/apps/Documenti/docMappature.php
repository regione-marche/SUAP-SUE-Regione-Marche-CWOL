<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentMapping/itaDocumentMappingFactory.class.php';

function docMappature() {
    $docMappature = new docMappature();
    $docMappature->parseEvent();
    return;
}

class docMappature extends itaModel {

    public $nameForm = 'docMappature';
    public $gridAnagrafica = 'docMappature_gridAnagrafica';
    public $gridVoci = 'docMappature_gridVoci';
    private $ITALWEB_DB;
    private $docLib;
    private $tabVoci;
    private $tabVociDeleted;
    private $rowidCancellazione;

    public function __construct() {
        parent::__construct();
        $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        $this->docLib = new docLib;
        $this->tabVoci = App::$utente->getKey($this->nameForm . '_tabVoci');
        $this->tabVociDeleted = App::$utente->getKey($this->nameForm . '_tabVociDeleted');
        $this->rowidCancellazione = App::$utente->getKey($this->nameForm . '_rowidCancellazione');
    }

    public function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tabVoci', $this->tabVoci);
            App::$utente->setKey($this->nameForm . '_tabVociDeleted', $this->tabVociDeleted);
            App::$utente->setKey($this->nameForm . '_rowidCancellazione', $this->rowidCancellazione);
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_tabVoci');
        App::$utente->removeKey($this->nameForm . '_tabVociDeleted');
        App::$utente->removeKey($this->nameForm . '_rowidCancellazione');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::select($this->nameForm . '_DOC_MAP_ANAG[TIPO_SINTASSI]', 1, '', 1, 'Nessuna');
                foreach (itaDocumentMappingFactory::getMappingSyntaxes() as $mCode => $mDescription) {
                    Out::select($this->nameForm . '_DOC_MAP_ANAG[TIPO_SINTASSI]', 1, $mCode, 0, $mCode);
                }

                $this->openRisultato();
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        $this->caricaGriglia($this->gridAnagrafica, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $this->getSQLAnag()
                        ));
                        break;

                    case $this->gridVoci:
                        $this->caricaGriglia($this->gridVoci, array(
                            'arrayTable' => $this->tabVoci,
                            'rowIndex' => 'idx'
                        ));
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        $this->openGestione();
                        break;

                    case $this->gridVoci:
                        Out::msgInput('Nuova variabile', array(
                            'label' => array(
                                'value' => 'Variabile esterna',
                                'style' => 'display: block;'
                            ),
                            'id' => $this->nameForm . '_varext',
                            'name' => $this->nameForm . '_varext',
                            'class' => 'required',
                            'type' => 'text'
                            ), array(
                            'F5-Aggiungi' => array(
                                'id' => $this->nameForm . '_AggiungiVariabile',
                                'model' => $this->nameForm,
                                'shortCut' => 'f5'
                            )
                            ), $this->nameForm . '_workSpace');
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        $map_voci_tab = $this->docLib->getDocMapVoci($_POST['rowid'], 'mappatura');

                        if (count($map_voci_tab)) {
                            $this->rowidCancellazione = $_POST['rowid'];

                            Out::msgQuestion('Cancellazione', 'Confermi la cancellazione della mappatura e di tutte le variabili mappate al suo interno?', array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => 'f5')
                            ));

                            break;
                        }

                        $this->cancellaDocMapAnag($_POST['rowid']);

                        $this->caricaGriglia($this->gridAnagrafica, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $this->getSQLAnag()
                        ));

                        Out::msgBlock('', 1200, true, 'Cancellazione effettuata');
                        break;

                    case $this->gridVoci:
                        $this->rowidCancellazione = $_POST['rowid'];

                        Out::msgQuestion('Cancellazione', 'Confermi la cancellazione della variabile?', array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaVoce', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaVoce', 'model' => $this->nameForm, 'shortCut' => 'f5')
                        ));
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        $this->openGestione($_POST['rowid']);
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridVoci:
                        $this->tabVoci[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenco':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->openGestione();
                        break;

                    case $this->nameForm . '_Salva':
                        $mapanag_rec = $_POST[$this->nameForm . '_DOC_MAP_ANAG'];

                        if (false === $this->updateRecord($this->ITALWEB_DB, 'DOC_MAP_ANAG', $mapanag_rec, sprintf("Oggetto: aggiornamento record '%s'", $mapanag_rec['DESCRIZIONE']), 'ROW_ID')) {
                            break;
                        }

                        foreach ($this->tabVoci as $mapvoci_rec) {
                            if (!$mapvoci_rec['ANAG_ID']) {
                                $mapvoci_rec['ANAG_ID'] = $mapanag_rec['ROW_ID'];
                            }

                            if ($mapvoci_rec['ROW_ID']) {
                                if (false === $this->updateRecord($this->ITALWEB_DB, 'DOC_MAP_VOCI', $mapvoci_rec, sprintf("Oggetto: aggiornamento record '%d'", $mapvoci_rec['ROW_ID']), 'ROW_ID')) {
                                    break;
                                }
                            } else {
                                if (false === $this->insertRecord($this->ITALWEB_DB, 'DOC_MAP_VOCI', $mapvoci_rec, sprintf("Oggetto: inserimento record '%s'", $mapvoci_rec['VARIABILE_EXT']), 'ROW_ID')) {
                                    break;
                                }
                            }
                        }

                        foreach ($this->tabVociDeleted as $mapvoci_rowid) {
                            if (false === $this->deleteRecord($this->ITALWEB_DB, 'DOC_MAP_VOCI', $mapvoci_rowid, sprintf("Oggetto: cancellazione record '%s'", $_POST['rowid']), 'ROW_ID')) {
                                break;
                            }
                        }

                        $this->openGestione($mapanag_rec['ROW_ID']);

                        Out::msgBlock('', 1200, true, 'Aggiornamento effettuato');
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $mapanag_rec = $_POST[$this->nameForm . '_DOC_MAP_ANAG'];

                        if (false === $this->insertRecord($this->ITALWEB_DB, 'DOC_MAP_ANAG', $mapanag_rec, sprintf("Oggetto: inserimento record '%s'", $mapanag_rec['DESCRIZIONE']), 'ROW_ID')) {
                            break;
                        }

                        $this->openGestione(ItaDB::DBLastId($this->ITALWEB_DB));
                        break;

                    case $this->nameForm . '_AggiungiVariabile':
                        if (!$_POST[$this->nameForm . '_varext']) {
                            Out::msgStop('Errore', 'Inserire un valore.');
                            break;
                        }

                        $variabile_ext = $_POST[$this->nameForm . '_varext'];
                        $mapanagid = (int) $_POST[$this->nameForm . '_DOC_MAP_ANAG']['ROW_ID'];

                        if (ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM DOC_MAP_VOCI WHERE ANAG_ID = '$mapanagid' AND VARIABILE_EXT = '" . addslashes($variabile_ext) . "'", false)) {
                            Out::msgStop('Errore', 'Chiave già esistente.');
                            break;
                        }

                        $this->tabVoci[] = array(
                            'ANAG_ID' => $mapanagid,
                            'VARIABILE_EXT' => $variabile_ext
                        );

                        $this->caricaGriglia($this->gridVoci, array(
                            'arrayTable' => $this->tabVoci,
                            'rowIndex' => 'idx'
                            ), 9999);

                        Out::setRowSelection($this->gridVoci, count($this->tabVoci) - 1, 'id');
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        $this->cancellaDocMapAnag($this->rowidCancellazione);
                        $this->rowidCancellazione = false;

                        $this->caricaGriglia($this->gridAnagrafica, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $this->getSQLAnag()
                        ));

                        Out::msgBlock('', 1200, true, 'Cancellazione effettuata');
                        break;

                    case $this->nameForm . '_ConfermaCancellaVoce':
                        if ($this->tabVoci[$this->rowidCancellazione]['ROW_ID']) {
                            $this->tabVociDeleted[] = $this->tabVoci[$this->rowidCancellazione]['ROW_ID'];
                        }

                        unset($this->tabVoci[$this->rowidCancellazione]);
                        $this->rowidCancellazione = false;

                        $this->caricaGriglia($this->gridVoci, array(
                            'arrayTable' => $this->tabVoci,
                            'rowIndex' => 'idx'
                        ));
                        break;

                    case $this->nameForm . '_Importa':
                        $model = 'utiUploadDiag';
                        $_POST['messagge'] = 'Carica un file CSV con i campi in questo ordine:<br>Variabile Ext | Variabile Int | Descrizione<br><br>';

                        itaLib::openForm($model);
                        /* @var $utiUploadDiag utiUploadDiag */
                        $utiUploadDiag = itaModel::getInstance($model);
                        $utiUploadDiag->setEvent('openform');
                        $utiUploadDiag->setReturnModel($this->nameForm);
                        $utiUploadDiag->setReturnEvent('returnUpload');
                        $utiUploadDiag->parseEvent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'returnUpload':
                $filepath = $_POST['uploadedFile'];
                $i = 0;

                $keys = array();
                foreach ($this->tabVoci as $k => $voci_rec) {
                    $keys[$voci_rec['VARIABILE_EXT']] = $k;
                }

                $handle = fopen($filepath, "r");
                while (($line = fgets($handle)) !== false) {
                    $rec = array_map('trim', explode(';', $line), array_fill(0, 3, ' "'));
                    if (count($rec) < 3) {
                        continue;
                    }

                    $i++;

                    if (isset($keys[$rec[0]])) {
                        $this->tabVoci[$keys[$rec[0]]]['VARIABILE_INT'] = $rec[1];
                        $this->tabVoci[$keys[$rec[0]]]['DESCRIZIONE'] = $rec[2];
                        $this->tabVoci[$keys[$rec[0]]]['METADATI'] = '';
                    } else {
                        $this->tabVoci[] = array('VARIABILE_EXT' => $rec[0], 'VARIABILE_INT' => $rec[1], 'DESCRIZIONE' => $rec[2]);
                    }
                }

                fclose($handle);

                if ($i) {
                    Out::msgInfo('Importazione', "Sono stati importati $i record.");
                    TableView::reload($this->gridVoci);
                } else {
                    Out::msgInfo('Importazione', "Nessun record importato.");
                }

                break;
        }
    }

    public function workSpace() {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        foreach (func_get_args() as $div) {
            Out::show($this->nameForm . "_$div");
        }
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Elenco');
        Out::hide($this->nameForm . '_Salva');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Importa');

        foreach (func_get_args() as $button) {
            Out::show($this->nameForm . "_$button");
        }
    }

    public function caricaGriglia($id, $opts, $page = null, $rows = null, $sidx = null, $sord = null) {
        TableView::clearGrid($id);

        $gridObj = new TableView($id, $opts);

        $gridObj->setPageNum($page ?: $_POST['page'] ?: $_POST[$id]['gridParam']['page'] ?: 1);
        $gridObj->setPageRows($rows ?: $_POST['rows'] ?: $_POST[$id]['gridParam']['rowNum'] ?: 50);
        $gridObj->setSortIndex($sidx ?: $_POST['sidx'] ?: '');
        $gridObj->setSortOrder($sord ?: $_POST['sord'] ?: '');

        return $gridObj->getDataPage('json');
    }

    public function getSQLAnag() {
        return "SELECT ANAG.*, COUNT(VOCI.ROW_ID ) NVAR FROM DOC_MAP_ANAG ANAG LEFT JOIN DOC_MAP_VOCI VOCI ON ANAG.ROW_ID = VOCI.ANAG_ID GROUP BY ANAG.ROW_ID";
    }

    public function getSQLVoci($id) {
        $sql = "SELECT
                    *
                FROM
                    DOC_MAP_VOCI
                WHERE
                    ANAG_ID = '$id'";

        if ($_POST['_search'] == true) {
            if ($_POST['VARIABILE_EXT']) {
                $sql .= " AND UPPER(VARIABILE_EXT) LIKE UPPER('%" . addslashes($_POST['VARIABILE_EXT']) . "%')";
            }

            if ($_POST['VARIABILE_INT']) {
                $sql .= " AND UPPER(VARIABILE_INT) LIKE UPPER('%" . addslashes($_POST['VARIABILE_INT']) . "%')";
            }

            if ($_POST['DESCRIZIONE']) {
                $sql .= " AND UPPER(DESCRIZIONE) LIKE UPPER('%" . addslashes($_POST['DESCRIZIONE']) . "%')";
            }
        }

        return $sql;
    }

    public function openRisultato() {
        TableView::enableEvents($this->gridAnagrafica);
        $this->caricaGriglia($this->gridAnagrafica, array(
            'sqlDB' => $this->ITALWEB_DB,
            'sqlQuery' => $this->getSQLAnag()
            ), 1);

        TableView::disableEvents($this->gridVoci);

        $this->workSpace('divRisultato');
        $this->buttonBar('Nuovo');
    }

    public function openGestione($rowid = false) {
        Out::clearFields($this->nameForm);
        TableView::disableEvents($this->gridAnagrafica);

        $this->workSpace('divGestione');

        if ($rowid === false) {
            $this->buttonBar('Elenco', 'Aggiungi');
            Out::hide($this->nameForm . '_divGridVoci');
        } else {
            $this->buttonBar('Elenco', 'Salva', 'Importa');
            Out::show($this->nameForm . '_divGridVoci');

            $mapanag_rec = $this->docLib->getDocMapAnag($rowid);
            Out::valori($mapanag_rec, $this->nameForm . '_DOC_MAP_ANAG');

            TableView::enableEvents($this->gridVoci);

            $this->tabVoci = ItaDB::DBSQLSelect($this->ITALWEB_DB, $this->getSQLVoci($rowid));
            $this->tabVociDeleted = array();

            $this->caricaGriglia($this->gridVoci, array(
                'arrayTable' => $this->tabVoci,
                'rowIndex' => 'idx'
                ), 1);
        }
    }

    public function cancellaDocMapAnag($rowid) {
        if (!$rowid) {
            return false;
        }

        if (!$this->cancellaDocMapVoci($rowid)) {
            return false;
        }

        if (false === $this->deleteRecord($this->ITALWEB_DB, 'DOC_MAP_ANAG', $rowid, sprintf("Oggetto: cancellazione record '%s'", $rowid), 'ROW_ID')) {
            return false;
        }

        return true;
    }

    public function cancellaDocMapVoci($rowid) {
        $map_voci_tab = $this->docLib->getDocMapVoci($rowid, 'mappatura');

        foreach ($map_voci_tab as $map_voci_rec) {
            if (false === $this->deleteRecord($this->ITALWEB_DB, 'DOC_MAP_VOCI', $map_voci_rec['ROW_ID'], sprintf("Oggetto: cancellazione record '%s'", $map_voci_rec['ROW_ID']), 'ROW_ID')) {
                return false;
            }
        }

        return true;
    }

}
