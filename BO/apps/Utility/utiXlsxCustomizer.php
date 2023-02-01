<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

function utiXlsxCustomizer() {
    $utiXlsxCustomizer = new utiXlsxCustomizer();
    $utiXlsxCustomizer->parseEvent();
    return;
}

class utiXlsxCustomizer extends itaFrontController {

    private $db;
    private $ente;
    private $page;
    private $fields;
    private $modelData;

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'utiXlsxCustomizer';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);

        $this->page = cwbParGen::getFormSessionVar($this->nameForm, 'page');
        $this->fields = cwbParGen::getFormSessionVar($this->nameForm, 'fields');
        $this->modelData = cwbParGen::getFormSessionVar($this->nameForm, 'modelData');
        $this->db = cwbParGen::getFormSessionVar($this->nameForm, 'db');
        $this->ente = cwbParGen::getFormSessionVar($this->nameForm, 'ente');
        if (!empty($this->db)) {
            if (isSet($this->ente)) {
                $this->MAIN_DB = ItaDB::DBOpen($this->db, $this->ente);
            } else {
                $this->MAIN_DB = ItaDB::DBOpen($this->db);
            }
        }
    }

    public function __destruct() {
        if (!$this->close) {
            cwbParGen::setFormSessionVar($this->nameForm, 'page', $this->page);
            cwbParGen::setFormSessionVar($this->nameForm, 'fields', $this->fields);
            cwbParGen::setFormSessionVar($this->nameForm, 'modelData', $this->modelData);
            cwbParGen::setFormSessionVar($this->nameForm, 'db', $this->db);
            cwbParGen::setFormSessionVar($this->nameForm, 'ente', $this->ente);
        }
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::css($this->nameForm, 'overflow', 'visible');
                Out::css($this->nameForm . '_wrapper', 'overflow', 'visible');
                break;
            case 'delGridRow':
                $this->deleteRow($_POST['rowid']);
                break;
            case 'addGridRow':
                $this->addRow();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_LOAD':
                        $this->openLoadDialog();
                        break;
                    case $this->nameForm . '_ConfirmLoad':
                        if ($_POST[$this->nameForm . '_LOAD_MODEL'] != -1) {
                            $this->loadModel($_POST[$this->nameForm . '_LOAD_MODEL']);
                        }
                        break;
                    case $this->nameForm . '_DELETE':
                        $this->deleteModelDialog($_POST[$this->nameForm . '_PROGINT']);
                        break;
                    case $this->nameForm . '_ConfirmDelete':
                        $this->deleteModelConfirm(cwbParGen::getFormSessionVar($this->nameForm, 'deleteModel'));
                        break;
                    case $this->nameForm . '_SAVE':
                        $this->openSaveDialog();
                        break;
                    case $this->nameForm . '_overwriteSave':
                        $this->saveCurrentModel($_POST[$this->nameForm . '_MODEL'], true);
                        break;
                    case $this->nameForm . '_selectNameSave':
                        $this->selectNameSave();
                        break;
                    case $this->nameForm . '_ConfirmSave':
                        $saveModel = $_POST[$this->nameForm . '_SAVE_MODEL'];
                        if (!empty($saveModel)) {
                            $this->saveCurrentModel($_POST[$this->nameForm . '_SAVE_MODEL']);
                        } else {
                            Out::msgStop('Errore', 'Assegnare un nome al modello da salvare');
                        }
                        break;
                    case $this->nameForm . '_ForceSave':
                        $saveModel = cwbParGen::getFormSessionVar($this->nameForm, 'saveModel');
                        if (!empty($saveModel)) {
                            $this->saveCurrentModel($saveModel, true);
                        } else {
                            Out::msgStop('Errore', 'Assegnare un nome al modello da salvare');
                        }
                        break;
                    case $this->nameForm . '_PRINT':
                        $this->printXlsx();
                        break;
                    case $this->nameForm . '_ADDALL':
                        $this->addAll();
                        break;
                    case $this->nameForm . '_REMOVEALL':
                        $this->removeAll();
                        break;
                    default:
                        if (preg_match('/^' . $this->nameForm . '_(SaveColumnStyle|SaveHeaderStyle)_(.*?)$/', $_POST['id'], $matches)) {
                            $this->updateStyle($matches[1], $matches[2]);
                        }
                        if (preg_match('/^' . $this->nameForm . '_CALCULATED_BUTT_(.*?)$/', $_POST['id'], $matches)) {
                            $this->openFieldCalculator($matches[1]);
                        }
                        if (preg_match('/^' . $this->nameForm . '_CALLBACK_BUTT_(.*?)$/', $_POST['id'], $matches)) {
                            $this->openFunctionDialog($matches[1]);
                        }
                        if (preg_match('/^' . $this->nameForm . '_CallbackSave_(.*?)$/', $_POST['id'], $matches)) {
                            $this->saveCallbackFunction($matches[1]);
                        }
                        if (preg_match('/^' . $this->nameForm . '_(TOP|UP|DOWN|BOTTOM)_(.*?)$/', $_POST['id'], $matches)) {
                            switch ($matches[1]) {
                                case 'TOP': $this->moveTop($matches[2]);
                                    break;
                                case 'UP': $this->moveUp($matches[2]);
                                    break;
                                case 'DOWN': $this->moveDown($matches[2]);
                                    break;
                                case 'BOTTOM': $this->moveBottom($matches[2]);
                                    break;
                            }
                        }
                        break;
                }
                break;
            case 'onChange':
                if (preg_match('/^' . $this->nameForm . '_(SHEET|FIELD|DESCRIZIONE|WIDTH|FORMAT)_(.*?)$/', $_POST['id'], $matches)) {
                    $field = $matches[1];
                    $row = $matches[2];
                    $value = $_POST[$_POST['id']];
                    $this->changeValue($field, $row, $value);
                }
                break;
            case 'cellSelect':
                if ($_POST['id'] == $this->nameForm . '_gridMetadati') {
                    $row = $_POST['rowid'];
                    $field = $_POST['colName'];

                    if ($field == 'COLUMNSTYLE' || $field == 'HEADERSTYLE') {
                        $this->openStyleDialog($row, $field);
                    } else {
                        $this->toggleOrder($row);
                    }
                }
                break;
            case 'returnFieldModel':
                $this->modelData[$_POST['id']]['calculated'] = $this->formData['returnData'];
                break;
            case 'returnModel':
                $this->loadModel($_POST['rowData']['PROGINT']);
                break;
            case 'sortRowUpdate':
                $this->sortRow($_POST['startRowIndex'] - 1, $_POST['stopRowIndex'] - 1);
                break;
        }
    }

    /**
     * Inizializzazione della pagina di configurazione
     * @param <array> $fields array contenente i campi disponibili dal db/array
     * @param <ItaDB> $db oggetto ItaDB su cui leggere/caricare i modelli
     * @param <string> $nameFormOrig pagina che richiama
     * @param <string> $pageDescription Descrizione della pagina che richiama
     * @param <int> $defaultModel modello di default da usare
     * @throws <ItaException>
     */
    public function initPage($fields, $dbName, $ente = null, $nameFormOrig = null, $pageDescription = null, $defaultModel = null) {
        $this->db = $dbName;
        $this->ente = $ente;
        $this->page = (!empty($nameFormOrig) ? $nameFormOrig : $this->returnModel);
        $this->fields = $fields;
        if (isSet($ente)) {
            $this->MAIN_DB = ItaDB::DBOpen($this->db, $this->ente);
        } else {
            $this->MAIN_DB = ItaDB::DBOpen($this->db);
        }
        $pageDescription = (!empty($pageDescription) ? $pageDescription : $this->page);

        if (empty($this->page))
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non è stato definito il modello su cui lavorare');
        if (empty($this->fields))
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non sono stati definiti i campi del modello');
        if (empty($this->MAIN_DB))
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non sono stato definito il database contenente il modello');

        Out::valore($this->nameForm . '_NAMEFORM', $pageDescription);
        Out::hide($this->nameForm . '_DELETE');

        if (!empty($defaultModel)) {
            $this->loadModel($defaultModel);
        } else {
            $modello = ItaDB::DBSQLSelect($this->MAIN_DB, 'SELECT * FROM BGE_EXCELT WHERE NOME_FILE = \'' . str_replace("\'", '\\\'', $this->page) . '\' ORDER BY PROGINT DESC', false);

            if ($modello) {
                $this->loadModel($modello['PROGINT']);
            } else {
                Out::hide($this->nameForm . '_SAVE');
                Out::hide($this->nameForm . '_PRINT');
            }
        }
    }

    private function renderGrid() {
        $helper = new cwbBpaGenHelper();
        $helper->setGridName('gridMetadati');
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_gridMetadati');
        $records = $this->modelToHtml();

        $ita_grid = $helper->initializeTableArray($records);
        $ita_grid->setPageRows(10000);
        $ita_grid->getDataPage('json');

        cwbLibHtml::attivaJSElemento($this->nameForm . '_gridMetadati');

        if (empty($this->modelData)) {
            Out::hide($this->nameForm . '_SAVE');
            Out::hide($this->nameForm . '_PRINT');
        } else {
            Out::show($this->nameForm . '_SAVE');
            Out::show($this->nameForm . '_PRINT');
        }
    }

    private function modelToHtml() {
        $return = array();
        $i = 1;
        foreach ($this->modelData as $key => $row) {
            $return[$key] = array();
            $return[$key]['KEY'] = $key;

            $component = array(
                'id' => 'FIELD',
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'additionalClass' => 'ita-edit-onchange',
                'options' => array(),
                'properties' => array(
                    'style' => 'width: 110px'
                )
            );
            $preselected = false;
            foreach ($this->fields as $k => $value) {
                if (!is_array($value)) {
                    $field = $value;
                    $description = $value;
                } else {
                    $field = $k;
                    $description = $k . ' - ' . $value['name'];
                }
                if (!in_array($field, array_keys($this->modelData)) || $field == $key) {
                    if ($field == $key) {
                        $selected = true;
                        $preselected = true;
                    } else {
                        $selected = false;
                    }
                    $component['options'][] = array('value' => $field, 'text' => $description, 'selected' => $selected);
                }
            }
            if (!$preselected && isSet($row['calculated'])) {
                $component['options'][] = array('value' => 'calculated', 'text' => 'Calcolato', 'selected' => true);
                $component['options'][] = array('value' => 'callback', 'text' => 'Da funzione');
                $component['properties']['style'] = 'width: 85px; margin-bottom: 4px';
            } elseif (!$preselected && isSet($row['callback'])) {
                $component['options'][] = array('value' => 'calculated', 'text' => 'Calcolato');
                $component['options'][] = array('value' => 'callback', 'text' => 'Da funzione', 'selected' => true);
                $component['properties']['style'] = 'width: 85px; margin-bottom: 4px';
            } else {
                $component['options'][] = array('value' => 'calculated', 'text' => 'Calcolato');
                $component['options'][] = array('value' => 'callback', 'text' => 'Da funzione');
            }
            $return[$key]['FIELD'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            if (!$preselected && isSet($row['calculated'])) {
                $component = array(
                    'id' => 'CALCULATED_BUTT',
                    'type' => 'ita-button',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'icon' => 'ui-icon-gears',
                    'properties' => array(
                        'style' => 'padding:0px;'
                    )
                );
                $return[$key]['FIELD'] .= ' ' . cwbLibHtml::addGridComponent($this->nameForm, $component);
            } elseif (!$preselected && isSet($row['callback'])) {
                $component = array(
                    'id' => 'CALLBACK_BUTT',
                    'type' => 'ita-button',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'icon' => 'ui-icon-gears',
                    'properties' => array(
                        'style' => 'padding:0px;'
                    )
                );
                $return[$key]['FIELD'] .= ' ' . cwbLibHtml::addGridComponent($this->nameForm, $component);
            }

            $component = array(
                'id' => 'DESCRIZIONE',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'additionalClass' => 'ita-edit-onchange',
                'properties' => array(
                    'value' => $row['name'],
                    'style' => 'width: 95%;'
                )
            );
            $return[$key]['DESCRIZIONE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            $component = array(
                'id' => 'WIDTH',
                'type' => 'ita-edit',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'additionalClass' => 'ita-edit-onchange {formatter: \'number\', formatterOptions: {precision: 0, decimal: \'\', thousand: \'\', prefix: \'\', suffix: \'\'}}',
                'properties' => array(
                    'value' => $row['width'],
                    'style' => 'width: 95%;'
                )
            );
            $return[$key]['WIDTH'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            $component = array(
                'id' => 'FORMAT',
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'onChangeEvent' => true,
                'options' => array(
                    array('value' => itaXlsxWriter::FORMAT_STRING, 'text' => 'Testo', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_STRING),
                    array('value' => itaXlsxWriter::FORMAT_INTEGER, 'text' => 'Intero', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_INTEGER),
                    array('value' => itaXlsxWriter::FORMAT_DECIMAL2, 'text' => 'Decimale (2)', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_DECIMAL2),
                    array('value' => itaXlsxWriter::FORMAT_DECIMAL5, 'text' => 'Decimale (5)', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_DECIMAL5),
                    array('value' => itaXlsxWriter::FORMAT_DATE, 'text' => 'Data', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_DATE),
                    array('value' => itaXlsxWriter::FORMAT_PRICE, 'text' => 'Valuta', 'selected' => $row['format'] === itaXlsxWriter::FORMAT_PRICE),
                )
            );
            $return[$key]['FORMAT'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            $return[$key]['HEADERSTYLE'] = '<span class="ui-icon ui-icon-prush"></span>';
            $return[$key]['COLUMNSTYLE'] = '<span class="ui-icon ui-icon-prush"></span>';

            if ($row['orderBy'] == itaXlsxWriter::ORDER_ASC) {
                $return[$key]['COLUMNORDER'] = '<span class="ui-icon ui-icon-triangle-1-n"></span>';
            } elseif ($row['orderBy'] == itaXlsxWriter::ORDER_DESC) {
                $return[$key]['COLUMNORDER'] = '<span class="ui-icon ui-icon-triangle-1-s"></span>';
            } else {
                $return[$key]['COLUMNORDER'] = '';
            }

            $i++;
        }

        return $return;
    }

    private function sortRow($oldPosition, $newPosition) {
        $keys = array_keys($this->modelData);
        $values = array_values($this->modelData);

        $return = array();
        for ($i = 0; $i < count($keys); $i++) {
            if ($oldPosition < $newPosition && $i != $oldPosition) {
                $return[$keys[$i]] = $values[$i];
            }
            if ($i == $newPosition) {
                $return[$keys[$oldPosition]] = $values[$oldPosition];
            }
            if ($oldPosition > $newPosition && $i != $oldPosition) {
                $return[$keys[$i]] = $values[$i];
            }
        }

        $this->modelData = $return;
    }

    private function moveTop($id) {
        $keys = array_keys($this->modelData);
        $values = array_values($this->modelData);

        $idPosition = array_search($id, $keys);
        if ($idPosition == 0) {
            return;
        }

        $return = array();
        $return[$id] = $values[$idPosition];
        for ($i = 0; $i < count($keys); $i++) {
            if ($i != $idPosition) {
                $return[$keys[$i]] = $values[$i];
            }
        }

        $this->modelData = $return;
        $this->renderGrid();
    }

    private function moveBottom($id) {

        $keys = array_keys($this->modelData);
        $values = array_values($this->modelData);

        $idPosition = array_search($id, $keys);
        if ($idPosition == count($keys) - 1) {
            return;
        }

        $return = array();
        for ($i = 0; $i < count($keys); $i++) {
            if ($i != $idPosition) {
                $return[$keys[$i]] = $values[$i];
            }
        }
        $return[$id] = $values[$idPosition];

        $this->modelData = $return;
        $this->renderGrid();
    }

    private function moveUp($id) {
        $keys = array_keys($this->modelData);
        $values = array_values($this->modelData);

        $idPosition = array_search($id, $keys);
        if ($idPosition == 0) {
            return;
        }

        $return = array();
        for ($i = 0; $i < count($keys); $i++) {
            if ($i == $idPosition - 1) {
                $return[$keys[$i + 1]] = $values[$i + 1];
            } elseif ($i == $idPosition) {
                $return[$keys[$i - 1]] = $values[$i - 1];
            } else {
                $return[$keys[$i]] = $values[$i];
            }
        }

        $this->modelData = $return;
        $this->renderGrid();
    }

    private function moveDown($id) {
        $keys = array_keys($this->modelData);
        $values = array_values($this->modelData);

        $idPosition = array_search($id, $keys);
        if ($idPosition == count($keys) - 1) {
            return;
        }

        $return = array();
        for ($i = 0; $i < count($keys); $i++) {
            if ($i == $idPosition) {
                $return[$keys[$i + 1]] = $values[$i + 1];
            } elseif ($i == $idPosition + 1) {
                $return[$keys[$i - 1]] = $values[$i - 1];
            } else {
                $return[$keys[$i]] = $values[$i];
            }
        }

        $this->modelData = $return;
        $this->renderGrid();
    }

    private function deleteRow($id) {
        unset($this->modelData[$id]);
        $this->renderGrid();
    }

    public function openLoadDialog() {
        $modelli = ItaDB::DBSQLSelect($this->MAIN_DB, 'SELECT * FROM BGE_EXCELT WHERE NOME_FILE = \'' . str_replace("\'", '\\\'', $this->page) . '\' ORDER BY PROGINT ASC', true);

        $rows = array();
        foreach ($modelli as $row) {
            $rows[] = array(
                'PROGINT' => $row['PROGINT'],
                'DESCRIZIONE' => $row['DES_EXPORT'],
                'CODUTE' => $row['CODUTE'],
                'DATATIMEOPER' => '<i>' . (strtotime($row['DATAOPER']) ? $row['TIMEOPER'] . ' - ' . date('d/m/Y', strtotime($row['DATAOPER'])) : $row['TIMEOPER']) . '</i>'
            );
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Modelli esportazioni xlsx per ' . $_POST[$this->nameForm . '_NAMEFORM'],
            "width" => '745',
            "height" => '350',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $rows,
            "colNames" => array(
                "Progressivo",
                "Descrizione",
                "Utente mod.",
                "Data mod."
            ),
            "colModel" => array(
                array("name" => 'PROGINT', "width" => 0, "hidden" => 'true'),
                array("name" => 'DESCRIZIONE', "width" => 490),
                array("name" => 'CODUTE', "width" => 120),
                array("name" => 'DATATIMEOPER', "width" => 125),
            ),
            "pgbuttons" => 'false',
            "pginput" => 'false'
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = array();
        $_POST['returnModel']['nameForm'] = $this->nameForm;
        $_POST['returnModel']['nameFormOrig'] = $this->nameFormOrig;
        $_POST['returnEvent'] = 'returnModel';

        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
        $appRoute = App::getPath('appRoute.' . 'uti');
        include_once ITA_BASE_PATH . '/apps/Utility/utiRicDiag.php';
        $model();
    }

    private function loadModel($model) {
        $testa = ItaDB::DBSQLSelect($this->MAIN_DB, 'SELECT * FROM BGE_EXCELT WHERE PROGINT = ' . $model, false);
        if ($testa) {
            Out::valore($this->nameForm . '_MODEL', $testa['DES_EXPORT']);
            $fields = ItaDB::DBSQLSelect($this->MAIN_DB, 'SELECT * FROM BGE_EXCELD WHERE PROGINT = ' . $model . ' ORDER BY PROG_RIGA ASC', true);

            $this->modelData = array();
            foreach ($fields as $field) {
                if (!empty($field['COL_META'])) {
                    $fieldMetadata = json_decode($field['COL_META'], true);
                } else {
                    $fieldMetadata = array('name' => $field['NOMECOLON']);
                }
                $fieldMetadata['name'] = $field['NOMECOLON'];
                $this->modelData[$field['NOMECAMPOE']] = $fieldMetadata;
            }

            Out::show($this->nameForm . '_DELETE');
            Out::valore($this->nameForm . '_PROGINT', $testa['PROGINT']);
            $this->renderGrid();
        }
    }

    private function deleteModelDialog($model) {
        cwbParGen::setFormSessionVar($this->nameForm, 'deleteModel', $model);
        $bottoni = array(
            'Conferma' => array(
                'id' => $this->nameForm . '_ConfirmDelete',
                'model' => $this->nameForm
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_Annulla',
                'model' => $this->nameForm
            )
        );
        Out::msgQuestion('Attenzione', 'Si è sicuri di voler eliminare il modello selezionato?', $bottoni);
    }

    private function deleteModelConfirm($model) {
        try {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBBeginTransaction($this->MAIN_DB);
            }

            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBSQLExec($this->MAIN_DB, 'DELETE FROM BGE_EXCELT WHERE PROGINT = ' . $model);
                ItaDB::DBSQLExec($this->MAIN_DB, 'DELETE FROM BGE_EXCELD WHERE PROGINT = ' . $model);
            } else {
                $table = 'BGE_EXCELT';
                $data = array();
                $this->normalyzeTable($table);
                ItaDB::DBDelete($this->MAIN_DB, $table, 'PROGINT', $model);

                $table = 'BGE_EXCELD';
                $data = array();
                $this->normalyzeTable($table);
                ItaDB::DBDelete($this->MAIN_DB, $table, 'PROGINT', $model);
            }

            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBCommitTransaction($this->MAIN_DB);
            }
            $this->modelData = array();
            Out::valore($this->nameForm . '_MODEL', '');
            $this->renderGrid();
        } catch (ItaException $e) {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBRollbackTransaction($this->MAIN_DB);
            }
            Out::msgStop('Errore', 'Errore durante l\'eliminazione del modello: ' . $e->getNativeErroreDesc());
        } catch (Exception $e) {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBRollbackTransaction($this->MAIN_DB);
            }
            Out::msgStop('Errore', 'Errore durante l\'eliminazione del modello: ' . $e->getMessage());
        }
    }

    private function openSaveDialog() {
        $model = trim($_POST[$this->nameForm . '_MODEL']);
        if (!empty($model)) {
            $bottoni = array(
                'Sovrascrivi' => array(
                    'id' => $this->nameForm . '_overwriteSave',
                    'model' => $this->nameForm
                ),
                'Cambia Nome' => array(
                    'id' => $this->nameForm . '_selectNameSave',
                    'model' => $this->nameForm
                ),
                'Annulla' => array(
                    'id' => $this->nameForm . '_DontSave',
                    'model' => $this->nameForm
                )
            );
            Out::msgQuestion('Salva modello', 'Sovrascrivere il modello ' . $model . '?', $bottoni);
        } else {
            $this->selectNameSave();
        }
    }

    private function selectNameSave() {
        Out::msgInput('Salva modello', array(
            array(
                'id' => $this->nameForm . '_SAVE_MODEL',
                'name' => $this->nameForm . '_SAVE_MODEL',
                'type' => 'input',
                'class' => 'ita-edit',
                'style' => 'width: 400px'
            )
                ), array(
            'Salva' => array(
                'id' => $this->nameForm . '_ConfirmSave',
                'model' => $this->nameForm
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_DontSave',
                'model' => $this->nameForm
            )
                ), $this->nameForm
        );
    }

    private function saveCurrentModel($saveName = null, $force = false) {
        $testa = ItaDB::DBSQLSelect($this->MAIN_DB, 'SELECT * FROM BGE_EXCELT WHERE NOME_FILE = \'' . $this->page . '\' AND DES_EXPORT = \'' . $saveName . '\' AND UTENTE = \'' . cwbParGen::getUtente() . '\'', false);
        if ($testa && $force == false) {
            $bottoni = array(
                'Conferma' => array(
                    'id' => $this->nameForm . '_ForceSave',
                    'model' => $this->nameForm
                ),
                'Annulla' => array(
                    'id' => $this->nameForm . '_DontSave',
                    'model' => $this->nameForm
                )
            );
            Out::msgQuestion('Attenzione', 'Esiste già un modello con nome ' . $saveName . '. Si desidera sovrascriverlo?', $bottoni);
            cwbParGen::setFormSessionVar($this->nameForm, 'saveModel', $saveName);
            return;
        }

        try {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBBeginTransaction($this->MAIN_DB);
            }
            if ($testa) {
                if (ItaDB::usePDO($this->MAIN_DB)) {
                    ItaDB::DBSQLExec($this->MAIN_DB, 'DELETE FROM BGE_EXCELT WHERE PROGINT = ' . $testa['PROGINT']);
                    ItaDB::DBSQLExec($this->MAIN_DB, 'DELETE FROM BGE_EXCELD WHERE PROGINT = ' . $testa['PROGINT']);
                } else {
                    $table = 'BGE_EXCELT';
                    $data = array();
                    $this->normalyzeTable($table);
                    ItaDB::DBDelete($this->MAIN_DB, $table, 'PROGINT', $testa['PROGINT']);

                    $table = 'BGE_EXCELD';
                    $data = array();
                    $this->normalyzeTable($table);
                    ItaDB::DBDelete($this->MAIN_DB, $table, 'PROGINT', $testa['PROGINT']);
                }
            }

            $progint = cwbLibCalcoli::trovaProgressivo("PROGINT", "BGE_EXCELT", null, null, $this->MAIN_DB);

            $data = array(
                'PROGINT' => $progint,
                'NOMEWIN' => $_POST[$this->nameForm . '_NAMEFORM'],
                'UTENTE' => cwbParGen::getUtente(),
                'DES_EXPORT' => $saveName,
                'PERCORSO' => '',
                'NOME_FILE' => $this->page,
                'TIPO_ESPOR' => 0,
                'CODUTE' => cwbParGen::getUtente(),
                'TIMEOPER' => date('H:i:s'),
                'DATAOPER' => date('Ymd'),
                'FLAG_DIS' => 0
            );
            $table = 'BGE_EXCELT';
            $this->normalyzeTable($table);
            ItaDB::DBInsert($this->MAIN_DB, $table, 'ROW_ID', $data);

            $queries = $this->modelToQuery($progint);
            foreach ($queries as $data) {
                $table = 'BGE_EXCELD';
                $this->normalyzeTable($table);
                ItaDB::DBInsert($this->MAIN_DB, $table, 'ROW_ID', $data);
            }
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBCommitTransaction($this->MAIN_DB);
            }
            Out::valore($this->nameForm . '_MODEL', $saveName);
        } catch (ItaException $e) {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBRollbackTransaction($this->MAIN_DB);
            }
            Out::msgStop('Errore', 'Errore durante il salvataggio del modello: ' . $e->getNativeErroreDesc());
        } catch (Exception $e) {
            if (ItaDB::usePDO($this->MAIN_DB)) {
                ItaDB::DBRollbackTransaction($this->MAIN_DB);
            }
            Out::msgStop('Errore', 'Errore durante il salvataggio del modello: ' . $e->getMessage());
        }
    }

    private function modelToQuery($progint) {
        $return = array();
        $i = 0;
        foreach ($this->modelData as $key => $value) {
            $i++;
            $name = $value['name'];
            unset($value['name']);

            $return[] = array(
                'PROGINT' => $progint,
                'PROG_RIGA' => $i,
                'NOMECAMPOE' => $key,
                'NOMECOLON' => $name,
                'ISTR_CAT' => '',
                'CODUTE' => cwbParGen::getUtente(),
                'DATAOPER' => date('Ymd'),
                'TIMEOPER' => date('H:i:s'),
                'COL_META' => json_encode($value)
            );
        }
        return $return;
    }

    private function addRow() {
        $this->modelData['new' . time() . rand(0, 1000)] = array(
            'name' => '',
//            'sheet' => 'Sheet1',
            'calculated' => ''
        );

        $this->renderGrid();
    }

    private function changeValue($field, $row, $value) {
        switch ($field) {
            case 'FIELD':
                if ($value == 'calculated') {
                    unset($this->modelData[$row]['callback']);
                    $this->modelData[$row]['calculated'] = '';
                    if ($row != 'calculated' && $value != 'callback') {
                        $keys = array_keys($this->modelData);
                        $keys[array_search($row, $keys)] = 'new' . time() . rand(0, 1000);
                        $this->modelData = array_combine($keys, $this->modelData);
                    }
                } elseif ($value == 'callback') {
                    unset($this->modelData[$row]['calculated']);
                    $this->modelData[$row]['callback'] = array();
                    if ($row == 'calculated' || $value == 'callback') {
                        $keys = array_keys($this->modelData);
                        $keys[array_search($row, $keys)] = 'new' . time() . rand(0, 1000);
                        $this->modelData = array_combine($keys, $this->modelData);
                    }
                } else {
                    unset($this->modelData[$row]['callback']);
                    unset($this->modelData[$row]['calculated']);
                    $keys = array_keys($this->modelData);
                    $keys[array_search($row, $keys)] = $value;
                    $this->modelData = array_combine($keys, $this->modelData);
                    if (is_array($this->fields[$value])) {
                        $this->modelData[$value] = $this->fields[$value];
                    }
                }
                $this->renderGrid();
                break;
            case 'DESCRIZIONE':
                $this->modelData[$row]['name'] = $value;
                break;
            case 'WIDTH':
                $this->modelData[$row]['width'] = $value;
                break;
            case 'FORMAT':
                $this->modelData[$row]['format'] = $value;
                break;
        }
    }

    private function printXlsx() {
        $objModel = itaFrontController::getInstance($this->returnModel, $this->returnNameForm);
        $objModel->printXlsxFromModel($this->modelData);
    }

    private function addAll() {
        foreach ($this->fields as $key => $value) {
            if (!is_array($value)) {
                $field = $value;
                $data = array('name' => $value);
            } else {
                $field = $key;
                $data = $value;
            }

            if (!isSet($this->modelData[$field])) {
                $this->modelData[$field] = $data;
            }
        }
        $this->renderGrid();
    }

    private function removeAll() {
        $this->modelData = array();
        $this->renderGrid();
    }

    private function openStyleDialog($row, $field) {
        if ($field == 'COLUMNSTYLE') {
            $title = 'Stile colonna';
            $id = $this->nameForm . '_SaveColumnStyle_' . $row;
            $currentStyle = $this->modelData[$row]['fieldStyle'];
        } else {
            $title = 'Stile intestazione';
            $id = $this->nameForm . '_SaveHeaderStyle_' . $row;
            $currentStyle = $this->modelData[$row]['headerStyle'];
        }
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_BORDER]))
            $currentStyle[itaXlsxWriter::STYLE_BORDER] = '';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_BORDERCOLOR]))
            $currentStyle[itaXlsxWriter::STYLE_BORDERCOLOR] = '#000000';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE]))
            $currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] = 'thin';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_COLOR]))
            $currentStyle[itaXlsxWriter::STYLE_COLOR] = '#000000';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_FILL]))
            $currentStyle[itaXlsxWriter::STYLE_FILL] = '';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_FONT]))
            $currentStyle[itaXlsxWriter::STYLE_FONT] = 'Calibri';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_FONTSIZE]))
            $currentStyle[itaXlsxWriter::STYLE_FONTSIZE] = '11';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_FONTSTYLE]))
            $currentStyle[itaXlsxWriter::STYLE_FONTSTYLE] = '';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_HALIGN]))
            $currentStyle[itaXlsxWriter::STYLE_HALIGN] = '';
        if (!isSet($currentStyle[itaXlsxWriter::STYLE_VALIGN]))
            $currentStyle[itaXlsxWriter::STYLE_VALIGN] = '';


//        STYLE_FONTSTYLE
//        STYLE_HALIGN
//        STYLE_VALIGN


        $campi = array(
            array(
                'label' => array(
                    'value' => 'Allineamento orizzontale:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_HALIGN,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_HALIGN,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('', 'Automatico', ($currentStyle[itaXlsxWriter::STYLE_HALIGN] == '')),
                    array('left', 'Sinistra', ($currentStyle[itaXlsxWriter::STYLE_HALIGN] == 'left')),
                    array('center', 'Centro', ($currentStyle[itaXlsxWriter::STYLE_HALIGN] == 'center')),
                    array('right', 'Destra', ($currentStyle[itaXlsxWriter::STYLE_HALIGN] == 'right'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Allineamento verticale:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_VALIGN,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_VALIGN,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('', 'Automatico', ($currentStyle[itaXlsxWriter::STYLE_VALIGN] == '')),
                    array('bottom', 'Basso', ($currentStyle[itaXlsxWriter::STYLE_VALIGN] == 'bottom')),
                    array('center', 'Centro', ($currentStyle[itaXlsxWriter::STYLE_VALIGN] == 'center')),
                    array('distributed', 'Distribuito', ($currentStyle[itaXlsxWriter::STYLE_VALIGN] == 'distributed'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Font:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONT,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONT,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('Arial', 'Arial', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == 'Arial')),
                    array('Courier New', 'Courier New', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == 'Courier New')),
                    array('Times New Roman', 'Times New Roman', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == 'Times New Roman')),
                    array('Calibri', 'Calibri', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == 'Calibri'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Dimensione testo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSIZE,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSIZE,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('8', '8', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '8')),
                    array('9', '9', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '9')),
                    array('10', '10', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '10')),
                    array('11', '11', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '11')),
                    array('12', '12', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '12')),
                    array('13', '13', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '13')),
                    array('14', '14', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '14')),
                    array('15', '15', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '15')),
                    array('16', '16', ($currentStyle[itaXlsxWriter::STYLE_FONTSIZE] == '16'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Colore testo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_COLOR,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_COLOR,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('#000000', 'Nero', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#000000')),
                    array('#FF0000', 'Rosso', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#FF0000')),
                    array('#00FF00', 'Verde', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#00FF00')),
                    array('#0000FF', 'Blu', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#0000FF')),
                    array('#FFFF00', 'Giallo', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#FFFF00')),
                    array('#FF00FF', 'Magenta', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#FF00FF')),
                    array('#00FFFF', 'Azzurro', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#00FFFF')),
                    array('#FFFFFF', 'Bianco', ($currentStyle[itaXlsxWriter::STYLE_COLOR] == '#FFFFFF'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Stile testo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSTYLE,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSTYLE,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('', 'Nessuno', ($currentStyle[itaXlsxWriter::STYLE_FONTSTYLE] == '')),
                    array('bold', 'Grassetto', ($currentStyle[itaXlsxWriter::STYLE_FONTSTYLE] == 'bold')),
                    array('italic', 'Corsivo', ($currentStyle[itaXlsxWriter::STYLE_FONTSTYLE] == 'italic')),
                    array('underline', 'Sottolineato', ($currentStyle[itaXlsxWriter::STYLE_FONTSTYLE] == 'underline'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Colore riempimento:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FILL,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_FILL,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('', 'Nessuno', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '')),
                    array('#000000', 'Nero', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#000000')),
                    array('#FF0000', 'Rosso', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#FF0000')),
                    array('#00FF00', 'Verde', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#00FF00')),
                    array('#0000FF', 'Blu', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#0000FF')),
                    array('#FFFF00', 'Giallo', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#FFFF00')),
                    array('#FF00FF', 'Magenta', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#FF00FF')),
                    array('#00FFFF', 'Azzurro', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#00FFFF')),
                    array('#FFFFFF', 'Bianco', ($currentStyle[itaXlsxWriter::STYLE_FILL] == '#FFFFFF'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Bordo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDER,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDER,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('left,right,bottom,top', 'Si', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == 'left,right,bottom,top')),
                    array('', 'No', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == ''))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Colore bordo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERCOLOR,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERCOLOR,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('#000000', 'Nero', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#000000')),
                    array('#FF0000', 'Rosso', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#FF0000')),
                    array('#00FF00', 'Verde', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#00FF00')),
                    array('#0000FF', 'Blu', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#0000FF')),
                    array('#FFFF00', 'Giallo', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#FFFF00')),
                    array('#FF00FF', 'Magenta', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#FF00FF')),
                    array('#00FFFF', 'Azzurro', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#00FFFF')),
                    array('#FFFFFF', 'Bianco', ($currentStyle[itaXlsxWriter::STYLE_BORDER] == '#FFFFFF'))
                )
            ),
            array(
                'label' => array(
                    'value' => 'Stile bordo:',
                    'style' => 'width: 150px; text-align: right;'
                ),
                'id' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERSTYLE,
                'name' => $this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERSTYLE,
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'width: 150px;',
                'options' => array(
                    array('thin', 'Sottile', ($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] == 'thin')),
                    array('medium', 'Medio', ($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] == 'medium')),
                    array('thick', 'Spesso', ($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] == 'thick')),
                    array('dashed', 'Tratteggiato', ($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] == 'dashed')),
                    array('dotted', 'Puntato', ($currentStyle[itaXlsxWriter::STYLE_BORDERSTYLE] == 'dotted'))
                )
            )
        );
        $bottoni = array(
            'Conferma' => array(
                'id' => $id,
                'model' => $this->nameForm
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_DontSave',
                'model' => $this->nameForm
            )
        );
        Out::msgInput($title, $campi, $bottoni, $this->nameForm);
    }

    private function toggleOrder($row) {
        if (empty($this->modelData[$row]['orderBy'])) {
            $this->modelData[$row]['orderBy'] = itaXlsxWriter::ORDER_ASC;
        } elseif ($this->modelData[$row]['orderBy'] == itaXlsxWriter::ORDER_ASC) {
            $this->modelData[$row]['orderBy'] = itaXlsxWriter::ORDER_DESC;
        } else {
            unset($this->modelData[$row]['orderBy']);
        }
        $this->renderGrid();
    }

    private function updateStyle($type, $row) {
        $type = ($type == 'SaveColumnStyle' ? 'fieldStyle' : 'headerStyle');
        $data = array(
            itaXlsxWriter::STYLE_BORDER => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_BORDER],
            itaXlsxWriter::STYLE_BORDERCOLOR => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERCOLOR],
            itaXlsxWriter::STYLE_BORDERSTYLE => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_BORDERSTYLE],
            itaXlsxWriter::STYLE_COLOR => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_COLOR],
            itaXlsxWriter::STYLE_FILL => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_FILL],
            itaXlsxWriter::STYLE_FONT => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_FONT],
            itaXlsxWriter::STYLE_FONTSIZE => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSIZE],
            itaXlsxWriter::STYLE_FONTSTYLE => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_FONTSTYLE],
            itaXlsxWriter::STYLE_HALIGN => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_HALIGN],
            itaXlsxWriter::STYLE_VALIGN => $_POST[$this->nameForm . '_' . itaXlsxWriter::STYLE_VALIGN]
        );
        $this->modelData[$row][$type] = $data;
    }

    private function openFieldCalculator($row) {
        $model = cwbLib::apriFinestra('utiXlsxCalculator', $this->nameForm, 'returnFieldModel', $row, array(), $this->nameFormOrig);

        $fields = array();
        foreach ($this->fields as $key => $value) {
            if (is_string($value)) {
                $fields[] = $value;
            } else {
                $fields[] = $key;
            }
        }

        $model->initField($fields, $this->modelData[$row]['calculated']);
        $model->parseEvent();
    }

    private function openFunctionDialog($row) {
        Out::msgInput('Selezione funzione di calcolo campo', array(
            array(
                'label' => array(
                    'value' => 'Oggetto:',
                    'style' => 'width: 100px; text-align: right;'
                ),
                'id' => $this->nameForm . '_CALLBACK_OBJECT',
                'name' => $this->nameForm . '_CALLBACK_OBJECT',
                'type' => 'text',
                'class' => 'ita-edit',
                'style' => 'width: 200px',
                'value' => (isSet($this->modelData[$row]['callback']['object']) ? $this->modelData[$row]['callback']['object'] : ''),
                'options' => $options
            ),
            array(
                'label' => array(
                    'value' => 'Funzione:',
                    'style' => 'width: 100px; text-align: right;'
                ),
                'id' => $this->nameForm . '_CALLBACK_FUNCTION',
                'name' => $this->nameForm . '_CALLBACK_FUNCTION',
                'type' => 'text',
                'class' => 'ita-edit',
                'style' => 'width: 200px',
                'value' => (isSet($this->modelData[$row]['callback']['function']) ? $this->modelData[$row]['callback']['function'] : ''),
                'options' => $options
            ),
            array(
                'label' => array(
                    'value' => 'Dati aggiuntivi:',
                    'style' => 'width: 100px; text-align: right;'
                ),
                'id' => $this->nameForm . '_CALLBACK_DATA',
                'name' => $this->nameForm . '_CALLBACK_DATA',
                'type' => 'text',
                'class' => 'ita-edit',
                'style' => 'width: 200px',
                'value' => (isSet($this->modelData[$row]['callback']['data']) ? implode(';', $this->modelData[$row]['callback']['data']) : ''),
                'options' => $options
            )
                ), array(
            'Conferma' => array(
                'id' => $this->nameForm . '_CallbackSave_' . $row,
                'model' => $this->nameForm
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_DontSave',
                'model' => $this->nameForm
            )
                ), $this->nameForm
        );
    }

    private function saveCallbackFunction($row) {
        $data = array();
        $data['object'] = $_POST[$this->nameForm . '_CALLBACK_OBJECT'];
        $data['function'] = $_POST[$this->nameForm . '_CALLBACK_FUNCTION'];
        $data['data'] = explode(';', $_POST[$this->nameForm . '_CALLBACK_DATA']);

        $this->modelData[$row]['callback'] = $data;
    }

    private function normalyzeTable(&$table) {
        if (ItaDB::usePDO($this->MAIN_DB)) {
            if (is_string($table)) {
                include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php';
                include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';

                $modelService = itaModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($table));
                $table = $modelService->newTableDef($table, $this->MAIN_DB);
            }
        }
    }

}
