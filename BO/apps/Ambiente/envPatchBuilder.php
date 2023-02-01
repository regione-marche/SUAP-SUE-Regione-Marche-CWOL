<?php

include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPatch.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function envPatchBuilder() {
    $envUpdater = new envPatchBuilder();
    $envUpdater->parseEvent();
    return;
}

class envPatchBuilder extends itaModel {

    public $nameForm = 'envPatchBuilder';
    private $gridFiles;
    private $gridResults;
    private $envLibPatch;
    private $ITALWEB_DB;
    private $arrayFiles = array();
    private $arrayContexts = array();

    public function postInstance() {
        parent::postInstance();

        $this->gridFiles = $this->nameForm . '_gridFiles';
        $this->gridResults = $this->nameForm . '_gridResults';

        $this->envLibPatch = new envLibPatch();

        $this->ITALWEB_DB = $this->envLibPatch->getITALWEB();

        $this->arrayFiles = App::$utente->getKey($this->nameForm . '_arrayFiles');
        $this->arrayContexts = App::$utente->getKey($this->nameForm . '_arrayContexts');
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_arrayFiles', $this->arrayFiles);
            App::$utente->setKey($this->nameForm . '_arrayContexts', $this->arrayContexts);
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_arrayFiles');
        App::$utente->removeKey($this->nameForm . '_arrayContexts');
    }

    public function workSpace() {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        if (func_num_args()) {
            foreach (func_get_args() as $div) {
                Out::show($this->nameForm . "_$div");
            }
        }
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Elenco');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Ricerca');
        Out::hide($this->nameForm . '_CreaPatch');
        Out::hide($this->nameForm . '_Esporta');

        if (func_num_args()) {
            foreach (func_get_args() as $button) {
                Out::show($this->nameForm . "_$button");
            }
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openRicerca();
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridResults:
                        $this->caricaGriglia($this->gridResults, array(
                            'sqlDB' => $this->ITALWEB_DB,
                            'sqlQuery' => $this->creaSQL()
                        ));
                        break;

                    case $this->gridFiles:
                        $this->caricaGriglia($this->gridFiles, array(
                            'arrayTable' => $this->arrayFiles,
                            'rowIndex' => 'idx'
                        ));

                        TableView::resizeGrid($this->gridFiles, false, false);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Nuovo':
                        $this->openGestione();
                        break;

                    case $this->nameForm . '_Elenco':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Ricerca':
                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_Aggiungi':
                        $patch_deft_rec = $this->getCurrentRecord();
                        $patch_deft_rec['PATCH_UPLOAD_DATE'] = '0000-00-00';
                        $patch_defd_tab = $this->arrayFiles;

                        $insertROWID = $this->envLibPatch->insertPatch($patch_deft_rec, $patch_defd_tab);
                        if ($insertROWID === false) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->openGestione($insertROWID);
                        Out::msgBlock($this->nameForm, 1500, '', "Record inserito.");
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $patch_deft_rec = $this->getCurrentRecord();
                        $patch_defd_tab = $this->arrayFiles;

                        if (!$this->envLibPatch->updatePatch($patch_deft_rec, $patch_defd_tab)) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->openGestione($patch_deft_rec['ROW_ID']);
                        Out::msgBlock($this->nameForm, 1500, '', "Record aggiornato.");
                        break;

                    case $this->nameForm . '_Esporta':
                        $appsTempPath = itaLib::getAppsTempPath();
                        $patchNotes = $this->envLibPatch->createPatchNotesTXT($appsTempPath, $_POST[$this->nameForm . '_PATCH_DEFT']['ROW_ID']);
                        Out::openDocument(utiDownload::getUrl('patch_notes.txt', $patchNotes, true));
                        break;

                    case $this->nameForm . '_CreaPatch':
                        $patch_deft_rec = $this->getCurrentRecord();
                        $patch_defd_tab = $this->arrayFiles;

                        if (!$this->envLibPatch->updatePatch($patch_deft_rec, $patch_defd_tab)) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        if (!$this->envLibPatch->uploadPatch($_POST[$this->nameForm . '_PATCH_DEFT']['ROW_ID'])) {
                            $this->openGestione($patch_deft_rec['ROW_ID']);
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->openGestione($patch_deft_rec['ROW_ID']);
                        Out::msgBlock($this->nameForm, 1500, '', "Patch caricata con successo.");
                        break;

                    case $this->nameForm . '_PATCH_DEFT[PATCH_CONTEXT]_butt':
                        $contextList = $this->envLibPatch->getContextList();
                        $tagsArray = array();
                        foreach ($contextList as $key => $desc) {
                            $tagsArray[] = array('DESCRIZIONE' => $desc);
                        }

                        $gridOptions = array(
                            'Caption' => 'Seleziona un ambito',
                            'width' => '450',
                            'height' => '450',
                            'rowNum' => '999',
                            'rowList' => '[]',
                            'arrayTable' => $tagsArray,
                            'multiselect' => 'true',
                            'colNames' => array(
                                'Ambito'
                            ),
                            'colModel' => array(
                                array('name' => 'DESCRIZIONE', 'width' => 450, 'key' => 'true')
                            ),
                            'pgbuttons' => 'false',
                            'pginput' => 'false',
                            'navButtonEdit' => 'false'
                        );

                        $_POST = array();
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'onLookupContext';
                        $_POST['gridOptions'] = $gridOptions;
                        $_POST['returnKey'] = 'context';

                        $model = 'utiRicDiag';

                        itaLib::openForm($model);
                        $utiRicDiag = itaModel::getInstance($model);
                        $utiRicDiag->setEvent('openform');
                        $utiRicDiag->parseEvent();

                        foreach ($this->arrayContexts as $context) {
                            TableView::setSelection($utiRicDiag->nameGrid, $context);
                        }
                        break;

                    case $this->nameForm . '_modal_conferma':
                        $filePattern = $_POST[$this->nameForm . '_modal_inputText'];
                        $this->arrayFiles[] = array('FILENAME' => $filePattern);
                        TableView::reload($this->gridFiles);
                        break;

                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion('Cancellazione', 'Confermi la cancellazione?', array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => 'f5')
                            )
                        );
                        break;

                    case $this->nameForm . '_ConfermaCancella':
                        if (!$this->envLibPatch->deletePatch($_POST[$this->nameForm . '_PATCH_DEFT']['ROW_ID'])) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridResults:
                        $this->openGestione();
                        break;

                    case $this->gridFiles:
                        $html = '<span>Il formato del percorso può assumere i seguenti valori:</span><br>' .
                            '<span> - singolo file <b>apps/Accessi/accAuditing.php</b></span><br>' .
                            '<span> - intera cartella <b>apps/Accessi/*</b></span><br>' .
                            '<span> - solo alcuni files all\'interno di una cartella <b>apps/Accessi/*.php</b></span><br>';

                        Out::msgInputText($this->nameForm, 'Aggiunta file', $html);
                        Out::setFocus('', $this->nameForm . '_modal_inputText');
                        break;
                }
                break;

            case 'viewRowInline':
            case 'editRowInline':
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridResults:
                        $this->openGestione($_POST['rowid']);
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridFiles:
                        $this->arrayFiles[$_POST['rowid']]['FILENAME'] = $_POST['value'];
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['colName']) {
                    case 'INFO':
                        $patch_deft_rec = $this->envLibPatch->getPatchDeft($_POST['rowid']);
                        if ($patch_deft_rec) {
                            $html = '';

                            if ($patch_deft_rec['PATCH_CONTEXT']) {
                                $html .= '<b>AMBITO:</b> ';
                                $html .= str_replace(';', '; ', $patch_deft_rec['PATCH_CONTEXT']) . '<br>';
                            }

                            if ($patch_deft_rec['PATCH_NOTES']) {
                                $html .= '<b>NOTE:</b> ';
                                $html .= str_replace(';', '; ', $patch_deft_rec['PATCH_NOTES']) . '<br>';
                            }

                            $html .= '<b>FILE:</b>';
                            foreach ($this->envLibPatch->getPatchDefd($_POST['rowid'], 'deft', true) as $patch_defd_rec) {
                                $html .= '<br>- ' . $patch_defd_rec['FILENAME'];
                            }

                            Out::msgInfo('Info', $html);
                        }
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridFiles':
                        $selectedFilesString = $_POST[$this->gridFiles]['gridParam']['selarrrow'];
                        if ($selectedFilesString === '') {
                            $selectedFilesString = $_POST['rowid'];
                        }

                        $selectedFiles = explode(',', $selectedFilesString);
                        foreach ($selectedFiles as $selectedFile) {
                            unset($this->arrayFiles[$selectedFile]);
                        }

                        TableView::reload($this->gridFiles);
                        break;
                }
                break;

            case 'onLookupContext':
                $this->arrayContexts = explode(',', $_POST['context']);
                Out::valore($this->nameForm . '_PATCH_DEFT[PATCH_CONTEXT]', implode(';', $this->arrayContexts));
                break;
        }
    }

    public function caricaSelect() {
        Out::html($this->nameForm . '_RICERCA[BUILD_TAG_MIN]', '');
        Out::html($this->nameForm . '_RICERCA[BUILD_TAG_MAX]', '');

        Out::select($this->nameForm . '_RICERCA[BUILD_TAG_MIN]', 1, '', 0, '');
        Out::select($this->nameForm . '_RICERCA[BUILD_TAG_MAX]', 1, '', 0, '');

        $build_tag_min = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT DISTINCT BUILD_TAG_MIN AS BUILD FROM PATCH_DEFT WHERE BUILD_TAG_MIN != ''");
        $build_tag_max = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT DISTINCT BUILD_TAG_MAX AS BUILD FROM PATCH_DEFT WHERE BUILD_TAG_MAX != ''");
        $currentBuilds = array_unique(array_merge(array_column($build_tag_min, 'BUILD'), array_column($build_tag_max, 'BUILD')));

        foreach ($currentBuilds as $build) {
            Out::select($this->nameForm . '_RICERCA[BUILD_TAG_MIN]', 1, $build, 0, $build);
            Out::select($this->nameForm . '_RICERCA[BUILD_TAG_MAX]', 1, $build, 0, $build);
        }

        $releases = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT DISTINCT RELEASE_RIF FROM PATCH_DEFT WHERE RELEASE_RIF != ''");

        Out::html($this->nameForm . '_RICERCA[RELEASE_RIF]', '');
        Out::select($this->nameForm . '_RICERCA[RELEASE_RIF]', 1, '', 0, '');

        foreach ($releases as $release) {
            Out::select($this->nameForm . '_RICERCA[RELEASE_RIF]', 1, $release['RELEASE_RIF'], 0, $release['RELEASE_RIF']);
        }

        Out::html($this->nameForm . '_RICERCA[PATCH_CONTEXT]', '');
        Out::select($this->nameForm . '_RICERCA[PATCH_CONTEXT]', 1, '', 0, '');

        foreach ($this->envLibPatch->getContextList() as $context) {
            Out::select($this->nameForm . '_RICERCA[PATCH_CONTEXT]', 1, $context, 0, $context);
        }
    }

    public function openRicerca() {
        $this->caricaSelect();

        Out::clearFields($this->nameForm . '_divRicerca');
        Out::clearFields($this->nameForm . '_divRisultato');

        $this->workSpace('divRicerca');
        $this->buttonBar('Nuovo', 'Elenca');

        Out::setFocus('', $this->nameForm . '_RICERCA[RELEASE_RIF]');
    }

    public function openRisultato() {
        $this->workSpace('divRisultato');
        $this->buttonBar('Nuovo', 'Ricerca');

        if (ItaDB::DBSQLCount($this->ITALWEB_DB, $this->creaSQL()) == 0) {
            Out::msgBlock($this->nameForm, 1500, '', "Nessun risultato trovato.");
            $this->openRicerca();
            return;
        }

        TableView::enableEvents($this->gridResults);
        TableView::reload($this->gridResults);

        Out::setFocus('', $this->nameForm . '_Nuovo');
    }

    public function openGestione($id = null) {
        $this->workSpace('divGestione');

        Out::clearFields($this->nameForm . '_divGestione');
        Out::clearFields($this->nameForm . '_divAppoggio');

        Out::restoreContainerFields($this->nameForm . '_divGestione');

        /*
         * Fix per enable del campo "Ambito" (readonly viene visto
         * come "disabled" e toglie la lentina di ricerca)
         */

        Out::enableField($this->nameForm . '_PATCH_DEFT[PATCH_CONTEXT]');
        Out::addClass($this->nameForm . '_PATCH_DEFT[PATCH_CONTEXT]', 'ita-readonly');
        Out::attributo($this->nameForm . '_PATCH_DEFT[PATCH_CONTEXT]', 'readonly', '0', 'readonly');

        Out::enableGrid($this->gridFiles);

        if ($id) {
            Out::show($this->nameForm . '_divPatchInfo');

            $this->buttonBar('Esporta', 'Aggiorna', 'Cancella', 'Elenco', 'Ricerca', 'CreaPatch');

            $patch_deft_rec = $this->envLibPatch->getPatchDeft($id);

            Out::valori($patch_deft_rec, $this->nameForm . '_PATCH_DEFT');

            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MIN_DATE', $this->envLibPatch->Build2Ymd($patch_deft_rec['BUILD_TAG_MIN']));
            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MIN_TIME', $this->envLibPatch->Build2Time($patch_deft_rec['BUILD_TAG_MIN']));

            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MAX_DATE', $this->envLibPatch->Build2Ymd($patch_deft_rec['BUILD_TAG_MAX']));
            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MAX_TIME', $this->envLibPatch->Build2Time($patch_deft_rec['BUILD_TAG_MAX']));

            Out::hide($this->nameForm . '_divInfoUpload');
            Out::html($this->nameForm . '_divInfoUpload', '');

            if ($patch_deft_rec['PATCH_UPLOAD_DATE']) {
                if ($patch_deft_rec['PATCH_UPLOAD_DATE'] == '0000-00-00') {
                    Out::valore($this->nameForm . '_PATCH_DEFT[PATCH_UPLOAD_DATE]', '');
                } else {
                    if (!$this->envLibPatch->checkFTPPatch($id)) {
                        Out::msgStop('Attenzione', 'File FTP non trovato.');
                    } else {
                        Out::show($this->nameForm . '_divInfoUpload');
                        Out::html($this->nameForm . '_divInfoUpload', '<b>La modifica è disabilitata in quanto è già stato effettuato l\'upload su sito FTP.</b>');

                        Out::disableContainerFields($this->nameForm . '_divGestione');
                        Out::disableGrid($this->gridFiles);

                        $this->buttonBar('Esporta', 'Elenco', 'Ricerca');
                    }
                }
            }

            $this->arrayFiles = $this->envLibPatch->getPatchDefd($id, 'deft', true);
            $this->arrayContexts = explode(';', $patch_deft_rec['PATCH_CONTEXT']);

            Out::setFocus('', $this->nameForm . '_PATCH_DEFT[RELEASE_RIF]');
        } else {
            Out::hide($this->nameForm . '_divPatchInfo');

            $this->buttonBar('Aggiungi', 'Elenco', 'Ricerca');

            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MAX_DATE', date('Ymd'));
            Out::valore($this->nameForm . '_PATCH_DEFT_TAG_MAX_TIME', date('H:i'));

            $this->arrayFiles = array();
            $this->arrayContexts = array();

            Out::setFocus('', $this->nameForm . '_PATCH_DEFT[RELEASE_RIF]');
        }

        TableView::enableEvents($this->gridFiles);
        TableView::reload($this->gridFiles);
    }

    public function getCurrentRecord() {
        $patch_deft_rec = $_POST[$this->nameForm . '_PATCH_DEFT'];

        if ($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MIN_DATE']) {
            $patch_deft_rec['BUILD_TAG_MIN'] = $this->envLibPatch->Ymd2Build($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MIN_DATE']) . '-';
            if ($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MIN_TIME']) {
                $patch_deft_rec['BUILD_TAG_MIN'] .= str_replace(':', '', $_POST[$this->nameForm . '_PATCH_DEFT_TAG_MIN_TIME']);
            } else {
                $patch_deft_rec['BUILD_TAG_MIN'] .= '0000';
            }
        }

        if ($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MAX_DATE']) {
            $patch_deft_rec['BUILD_TAG_MAX'] = $this->envLibPatch->Ymd2Build($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MAX_DATE']) . '-';
            if ($_POST[$this->nameForm . '_PATCH_DEFT_TAG_MAX_TIME']) {
                $patch_deft_rec['BUILD_TAG_MAX'] .= str_replace(':', '', $_POST[$this->nameForm . '_PATCH_DEFT_TAG_MAX_TIME']);
            } else {
                $patch_deft_rec['BUILD_TAG_MAX'] .= '2359';
            }
        }

        return $patch_deft_rec;
    }

    public function caricaGriglia($id, $opts, $page = null, $rows = null, $sidxp = null, $sordp = null) {
        TableView::clearGrid($id);
        TableView::enableEvents($id);

        $gridObj = new TableView($id, $opts);

        $sidx = $sidxp ?: $_POST['sidx'] ?: '';
        $sord = $sordp ?: $_POST['sord'] ?: '';

        $gridObj->setPageNum($page ?: $_POST['page'] ?: $_POST[$id]['gridParam']['page'] ?: 1);
        $gridObj->setPageRows($rows ?: $_POST['rows'] ?: $_POST[$id]['gridParam']['rowNum'] ?: 999);
        $gridObj->setSortIndex($sidx);
        $gridObj->setSortOrder($sord);

        $elaboraRecords = 'elaboraRecords' . ucfirst(substr($id, strlen($this->nameForm) + 1));
        if (method_exists($this, $elaboraRecords)) {
            return $gridObj->getDataPageFromArray('json', $this->$elaboraRecords($gridObj->getDataArray()));
        }

        return $gridObj->getDataPage('json');
    }

    public function elaboraRecordsGridResults($records) {
        foreach ($records as $k => $record) {
            if ($records[$k]['PATCH_UPLOAD_DATE'] === '0000-00-00') {
                $records[$k]['PATCH_UPLOAD_DATE'] = '';
            }

            if (!$records[$k]['PATCH_UPLOAD_DATE']) {
                $spanStyle = 'style="color: red; font-style: italic;"';
                $records[$k]['PATCH_NAME'] = "<span $spanStyle>{$records[$k]['PATCH_NAME']}</span>";
                $records[$k]['PATCH_DES'] = "<span $spanStyle>{$records[$k]['PATCH_DES']}</span>";
                $records[$k]['PATCH_NAME'] = "<span $spanStyle>{$records[$k]['PATCH_NAME']}</span>";
            }

            if ($records[$k]['FILES'] == '0') {
                $records[$k]['FILES'] = '';
            } else {
                $records[$k]['FILES'] .= ' <i class="ui-icon ui-icon-file"></i>';
            }

            $records[$k]['INFO'] = '<i style="color: blue;" class="ui-icon ui-icon-info"></i>';
            $records[$k]['DATAOPER'] = '<small style="line-height: 10px; display: inline-block; padding: 3px 0;">' . $record['DATAOPER'] . '<br>' . $record['TIMEOPER'] . '</small>';
            $records[$k]['DATAINSER'] = '<small style="line-height: 10px; display: inline-block; padding: 3px 0;">' . $record['DATAINSER'] . '<br>' . $record['TIMEINSER'] . '</small>';
        }

        return $records;
    }

    public function creaSQL() {
        $sql = "SELECT
                    PATCH_DEFT.*,
                    ( SELECT COUNT(*) FROM PATCH_DEFD WHERE PATCH_DEFT_ID = PATCH_DEFT.ROW_ID ) AS FILES
                FROM
                    PATCH_DEFT
                WHERE 1 = 1";

        $filtriRicerca = $_POST[$this->nameForm . '_RICERCA'];

        foreach ($filtriRicerca as $key => $value) {
            if (!$value) {
                continue;
            }

            $safeValue = addslashes($value);

            switch ($key) {
                case 'BUILD_TAG_MIN':
                    $sql .= " AND BUILD_TAG_MIN >= '$safeValue'";
                    break;

                case 'BUILD_TAG_MAX':
                    $sql .= " AND BUILD_TAG_MAX <= '$safeValue'";
                    break;

                case 'PATCH_DATE_FROM':
                    $sql .= " AND DATAINSER >= '$safeValue'";
                    break;

                case 'PATCH_DATE_TO':
                    $sql .= " AND DATAINSER <= '$safeValue'";
                    break;

                case 'TYPE':
                    if ($safeValue === 'TMP') {
                        $sql .= " AND ( PATCH_UPLOAD_DATE = '' OR PATCH_UPLOAD_DATE = '0000-00-00' )";
                    } else {
                        $sql .= " AND PATCH_UPLOAD_DATE != '' AND PATCH_UPLOAD_DATE != '0000-00-00'";
                    }
                    break;

                default:
                    $sql .= " AND " . $this->ITALWEB_DB->strUpper($key) . " LIKE " . $this->ITALWEB_DB->strUpper("'%$safeValue%'");
                    break;
            }
        }

        return $sql;
    }

}
