<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

function utiXlsxCalculator() {
    $utiXlsxCalculator = new utiXlsxCalculator();
    $utiXlsxCalculator->parseEvent();
    return;
}

class utiXlsxCalculator extends itaFrontController {

    private $fields;
    private $fieldModel;

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'utiXlsxCalculator';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);

        $this->fields = cwbParGen::getFormSessionVar($this->nameForm, 'fields');
        $this->fieldModel = cwbParGen::getFormSessionVar($this->nameForm, 'fieldModel');
    }

    public function __destruct() {
        if (!$this->close) {
            cwbParGen::setFormSessionVar($this->nameForm, 'fields', $this->fields);
            cwbParGen::setFormSessionVar($this->nameForm, 'fieldModel', $this->fieldModel);
        }
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::css($this->nameForm, 'overflow', 'visible');
                Out::css($this->nameForm . '_wrapper', 'overflow', 'visible');
                $this->renderGrid();
                break;
            case 'delGridRow':
                $this->deleteRow($_POST['rowid']);
                break;
            case 'addGridRow':
                $this->addRow();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_SAVE':
                        cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $this->modelToString(), $this->nameForm, $this->returnNameForm);
                        $this->close();
                        break;
                    case $this->nameForm . '_ANNULLA':
                        Out::closeDialog($this->nameForm);
                        $this->close();
                        break;
                }
                break;
            case 'onChange':
                if (preg_match('/^' . $this->nameForm . '_(TYPE|VALUE)_([0-9]*)$/', $_POST['id'], $matches)) {
                    $field = $matches[1];
                    $row = $matches[2];
                    $value = $_POST[$_POST['id']];

                    $this->changeValue($field, $row, $value);
                }
                break;
        }
    }

    /**
     * Inizializzazione della pagina di configurazione
     * @param <array> $fields array contenente i campi disponibili dal db/array
     * @param <string> $model modello in formato stringa da caricare
     * @throws <ItaException>
     */
    public function initField($fields, $model = null) {
        $this->fields = $fields;
        $this->fieldModel = $this->stringToModel($model);
    }

    private function stringToModel($model = null) {
        $return = array();

        if (!empty($model)) {
            itaXlsxWriter::getCalculateMatches($model, $matches);
            /*
              preg_match_all("/([+\-\/*]|'.*?'|<.*?>|%.*?%)/", $model, $matches);
             * 
             */
            foreach ($matches[1] as $chunk) {
                if (strpos($chunk, '<') === 0) {
                    $return[] = array('FIELD' => substr($chunk, 1, -1));
                } elseif (strpos($chunk, '%') === 0) {
                    $return[] = array('EXTRAFIELD' => substr($chunk, 1, -1));
                } elseif (strpos($chunk, '\'') === 0) {
                    $return[] = array('STRING' => substr($chunk, 1, -1));
                } else {
                    $return[] = array('OPERATION' => $chunk);
                }
            }
        }

        return $return;
    }

    private function modelToString() {
        $return = '';

        foreach ($this->fieldModel as $chunk) {
            if (isSet($chunk['FIELD'])) {
                $return .= '<' . $chunk['FIELD'] . '>';
            } elseif (isSet($chunk['EXTRAFIELD'])) {
                $return .= '%' . $chunk['EXTRAFIELD'] . '%';
            } elseif (isSet($chunk['STRING'])) {
                $return .= '\'' . $chunk['STRING'] . '\'';
            } else {
                $return .= $chunk['OPERATION'];
            }
        }

        return $return;
    }

    private function renderGrid() {
        $helper = new cwbBpaGenHelper();
        $helper->setGridName('gridMetadati');
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_gridMetadati');
        $records = $this->modelToHtml();

        $ita_grid = $helper->initializeTableArray($records);
        $ita_grid->setPageRows($_POST['rows']);
        $ita_grid->getDataPage('json');

        cwbLibHtml::attivaJSElemento($this->nameForm . '_gridMetadati');
    }

    private function modelToHtml() {
        $return = array();
        foreach ($this->fieldModel as $key => $row) {
            $return[$key] = array();
            $return[$key]['KEY'] = $key;

            $component = array(
                'id' => 'TYPE',
                'type' => 'ita-select',
                'model' => $this->nameForm,
                'rowKey' => $key,
                'additionalClass' => 'ita-edit-onchange',
                'options' => array(
                    array('value' => 'FIELD', 'text' => 'Campo DB', 'selected' => (isSet($row['FIELD']))),
                    array('value' => 'EXTRAFIELD', 'text' => 'Campo DB Extra', 'selected' => (isSet($row['EXTRAFIELD']))),
                    array('value' => 'STRING', 'text' => 'Costante', 'selected' => (isSet($row['STRING']))),
                    array('value' => 'OPERATION', 'text' => 'Operazione', 'selected' => (isSet($row['OPERATION'])))
                )
            );
            $return[$key]['TYPE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);

            if (isSet($row['FIELD'])) {
                $component = array(
                    'id' => 'VALUE',
                    'type' => 'ita-select',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'additionalClass' => 'ita-edit-onchange',
                    'options' => array()
                );
                foreach ($this->fields as $field) {
                    $component['options'][] = array(
                        'value' => $field,
                        'text' => $field,
                        'selected' => ($row['FIELD'] == $field)
                    );
                }
            } elseif (isSet($row['EXTRAFIELD'])) {
                $component = array(
                    'id' => 'VALUE',
                    'type' => 'ita-edit',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'additionalClass' => 'ita-edit-onchange',
                    'properties' => array(
                        'value' => $row['EXTRAFIELD'],
                        'style' => 'width: 95%;'
                    )
                );
            } elseif (isSet($row['STRING'])) {
                $component = array(
                    'id' => 'VALUE',
                    'type' => 'ita-edit',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'additionalClass' => 'ita-edit-onchange',
                    'properties' => array(
                        'value' => $row['STRING'],
                        'style' => 'width: 95%;'
                    )
                );
            } elseif (isSet($row['OPERATION'])) {
                $component = array(
                    'id' => 'VALUE',
                    'type' => 'ita-select',
                    'model' => $this->nameForm,
                    'rowKey' => $key,
                    'additionalClass' => 'ita-edit-onchange',
                    'options' => array(
                        array('value' => '+', 'text' => '+', 'selected' => ($row['OPERATION'] == '+')),
                        array('value' => '-', 'text' => '-', 'selected' => ($row['OPERATION'] == '-')),
                        array('value' => '*', 'text' => '*', 'selected' => ($row['OPERATION'] == '*')),
                        array('value' => '/', 'text' => '/', 'selected' => ($row['OPERATION'] == '/'))
                    )
                );
            }
            $return[$key]['VALUE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
        }

        return $return;
    }

    private function deleteRow($id) {
        unset($this->fieldModel[$id]);
        $this->renderGrid();
    }

    private function addRow() {
        $this->fieldModel[] = array('FIELD' => reset($this->fields));
        $this->renderGrid();
    }

    private function changeValue($field, $row, $value) {
        if ($field == 'TYPE') {
            switch ($value) {
                case 'FIELD':
                    $this->fieldModel[$row]['FIELD'] = reset($this->fields);
                    unset($this->fieldModel[$row]['EXTRAFIELD']);
                    unset($this->fieldModel[$row]['STRING']);
                    unset($this->fieldModel[$row]['OPERATION']);
                    break;
                case 'EXTRAFIELD':
                    unset($this->fieldModel[$row]['FIELD']);
                    $this->fieldModel[$row]['EXTRAFIELD'] = '';
                    unset($this->fieldModel[$row]['STRING']);
                    unset($this->fieldModel[$row]['OPERATION']);
                    break;
                case 'STRING':
                    unset($this->fieldModel[$row]['FIELD']);
                    unset($this->fieldModel[$row]['EXTRAFIELD']);
                    $this->fieldModel[$row]['STRING'] = '';
                    unset($this->fieldModel[$row]['OPERATION']);
                    break;
                case 'OPERATION':
                    unset($this->fieldModel[$row]['FIELD']);
                    unset($this->fieldModel[$row]['EXTRAFIELD']);
                    unset($this->fieldModel[$row]['STRING']);
                    $this->fieldModel[$row]['OPERATION'] = '+';
                    break;
            }
            $this->renderGrid();
        } else {
            $keys = array_keys($this->fieldModel[$row]);
            $this->fieldModel[$row][$keys[0]] = $value;
        }
    }

}
