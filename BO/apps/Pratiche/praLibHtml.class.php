<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

class praLibHtml {

    private $praLib;
    private $praLibElaborazioneDati;
    private $defaultFullWidth = false;

    public function __construct() {
        $this->praLib = new praLib;
        $this->praLibElaborazioneDati = new praLibElaborazioneDati;
    }

    /*
     * Mappatura tabelle dati
     */

    private $PRODAG = array(
        'DAGLAB' => 'label',
        'DAGLABSTYLE' => 'label-style',
        'DAGPOS' => 'label-position',
        'DAGDEF' => 'default',
        'DAGVAL' => 'value',
        'DAGLEN' => 'length',
        'DAGROL' => 'readonly',
        'DAGTIC' => 'type',
        'DAGKEY' => 'chiave',
        'DAGFIELDSTYLE' => 'field-style',
        'DAGFIELDCLASS' => 'field-class',
        'DAGMETA' => 'metadata',
        'DAGCTR' => 'controlli',
        'DAGSET' => 'set',
        'DAGACA' => 'acapo'
    );

    /**
     * @param boolean $defaultFullWidth
     */
    public function setDefaultFullWidth($defaultFullWidth) {
        $this->defaultFullWidth = $defaultFullWidth;
    }

    /*
     * Funzioni pubbliche per ritorno HTML, divise per tabella
     */

    /**
     * Trasforma un record di PRODAG in un input html completo.
     * 
     * @param array $prodag_rec
     * @return string
     */
    public function getProdagHtmlField($prodag_rec, $nameform = '', $table = '') {
        $data_rec = $this->getDataFromTable($prodag_rec, 'PRODAG');
        return $this->getHtmlField($data_rec, $nameform, $table);
    }

    /**
     * Ritorna la label HTML di un record di PRODAG.
     * 
     * @param array $prodag_rec
     * @return string
     */
    public function getProdagHtmlLabel($prodag_rec, $nameform = '', $table = '') {
        $data_rec = $this->getDataFromTable($prodag_rec, 'PRODAG');
        return $this->getHtmlLabel($data_rec, $nameform, $table);
    }

    /**
     * Trasforma un record di PRODAG in un input html privo di label.
     * 
     * @param array $prodag_rec
     * @return string
     */
    public function getProdagHtmlInput($prodag_rec, $nameform = '', $table = '') {
        $data_rec = $this->getDataFromTable($prodag_rec, 'PRODAG');
        return $this->getHtmlInput($data_rec, $nameform, $table);
    }

    /*
     * Funzioni interne per la generazione generica dell'HTML
     */

    private function getHtmlField($data_rec, $nameform = '', $table = '') {
        $component = $this->getComponentArray($data_rec, $nameform, $table);
        return $this->getComponentHtml($component);
    }

    private function getHtmlLabel($data_rec, $nameform = '', $table = '') {
        $component = $this->getComponentArray($data_rec, $nameform, $table);
        return itaComponents::getHtmlLabel($component);
    }

    private function getHtmlInput($data_rec, $nameform = '', $table = '') {
        $component = $this->getComponentArray($data_rec, $nameform, $table);
        unset($component['label']);
        return $this->getComponentHtml($component);
    }

    /**
     * Funzione interna per generalizzare i campi di un record.
     * 
     * @param array $prodag_rec
     * @return array
     */
    private function getDataFromTable($prodag_rec, $tableName) {
        $data_rec = array();

        foreach ($prodag_rec as $key => $value) {
            if (isset($this->{$tableName}[$key])) {
                $data_rec[$this->{$tableName}[$key]] = $value;
            }
        }

        return $data_rec;
    }

    /*
     * Funzioni interne per l'interfacciamento con la libreria esterna
     * di generazione HTML
     */

    /**
     * Funzione interna per la creazione dell'array $component.
     * 
     * @param array $data_rec
     * @return array
     */
    private function getComponentArray($data_rec, $nameform = '', $table = '') {
        $component = array(
            'additionalClass' => '',
            'properties' => array(
                'value' => htmlentities($data_rec['value'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1'),
                'style' => ''
            )
        );

        if ($data_rec['acapo']) {
            $component['newline'] = 1;
        }

        if ($data_rec['chiave'] && $nameform !== '') {
            $idFormat = $table === '' ? '%1$s_%2$s[%3$s]' : '%1$s_%4$s[%2$s][%3$s]';
            $component['properties']['id'] = sprintf($idFormat, $nameform, $data_rec['set'], $data_rec['chiave'], $table);
            $component['properties']['name'] = $component['properties']['id'];

            if (isset($data_rec['radiovalue'])) {
                $component['properties']['name'] = sprintf($idFormat, $nameform, $data_rec['set'], $data_rec['radioname'], $table);
            }
        }

        /*
         * Lunghezza campo
         */
        if ($data_rec['length']) {
            $component['properties']['size'] = $data_rec['length'];
            $component['properties']['maxlength'] = $data_rec['length'];
        } else if ($this->defaultFullWidth && $data_rec['type'] === 'Text') {
            $component['ita-field-style'] = 'width: 99%;';
            $component['properties']['style'] .= 'width: 100%;';
        }

        /*
         * Readonly
         */
        if ($data_rec['readonly']) {
            $component['additionalClass'] .= ' ita-readonly';
            $component['properties']['readonly'] = 'readonly';
        }

        /*
         * Label
         */
        if ($data_rec['label']) {
            $component['label'] = array();
            $component['label']['text'] = $data_rec['label'];
            $component['label']['style'] = $data_rec['label-style'];

            switch ($data_rec['label-position']) {
                case 'Sinistra':
                    $component['label']['position'] = 'sx';
                    break;

                case 'Destra':
                    $component['label']['position'] = 'dx';
                    break;

                case 'Sopra':
                    $component['label']['position'] = 'top';
                    break;

                case 'Sotto':
                    $component['label']['position'] = 'bot';
                    break;

                case 'Nascosta':
                    $component['label']['position'] = 'hidden';
                    break;
            }
        }

        $component['dagtic'] = $data_rec['type'];

        switch ($component['dagtic']) {
            case 'Importo':
                unset($component['ita-field-style']);
                $component['properties']['value'] = $component['properties']['value'] ?: 0;
                $component['additionalClass'] .= " { formatter: 'number', formatterOptions: { prefix: 'E ' } }";
                break;

            case 'Data':
                unset($component['ita-field-style']);
                $component['properties']['size'] = '10';
                $component['properties']['maxlength'] = '10';
                $component['properties']['style'] .= 'float: left;';
                break;

            case 'RadioGroup':
                $component['radio'] = array();

                $radiodata_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGTIC = 'RadioButton' AND DAGSET = '{$data_rec['set']}'");

                foreach ($radiodata_tab as $radiodata_rec) {
                    $meta = unserialize($radiodata_rec['DAGMETA']);
                    if ($meta && $meta['ATTRIBUTICAMPO'] && $meta['ATTRIBUTICAMPO']['NAME'] == $data_rec['chiave']) {
                        $radiocomponent_rec = $this->getDataFromTable($radiodata_rec, 'PRODAG');
                        $radiocomponent_rec['radioname'] = $meta['ATTRIBUTICAMPO']['NAME'];
                        $radiocomponent_rec['radiovalue'] = $meta['ATTRIBUTICAMPO']['RETURNVALUE'];
                        $radiocomponent_rec['value'] = $data_rec['value'];

                        $component['radio'][] = $this->getComponentArray($radiocomponent_rec, $nameform, $table);
                    }
                }
                break;

            case 'Select':
                unset($component['properties']['value']);
                $component['options'] = array(array('value' => '', 'text' => ''));

                $options = explode('|', $data_rec['default']);

                foreach ($options as $option) {
                    $optText = $option;

                    if (strpos($option, ':') !== false) {
                        list($option, $optText) = explode(':', $option);
                    }

                    if (substr($option, 0, 1) === '@' && substr($option, -1, 1) === '@') {
                        $compOption = array('group' => substr($option, 1, -1));
                    } else {
                        $compOption = array('value' => $option, 'text' => $optText);

                        if ($option == $data_rec['value']) {
                            $compOption['selected'] = true;
                        }
                    }

                    $component['options'][] = $compOption;
                }
                break;

            case 'CheckBox':
                unset($component['ita-field-style']);
                $component['properties']['value'] = 1;

                $meta = unserialize($data_rec['metadata']);
                $states = explode('/', $meta['ATTRIBUTICAMPO']['RETURNVALUES']);

                $states[0] = $states[0] !== '' ? $states[0] : 'On';
                $states[1] = $states[1] !== '' ? $states[1] : 'Off';

                if ($data_rec['value'] == $states[0]) {
                    $component['properties']['checked'] = 'checked';
                }
                break;

            case 'RadioButton':
                unset($component['ita-field-style']);
                unset($component['properties']['style']);

                $component['properties']['value'] = $data_rec['radiovalue'];

                if ($component['properties']['value'] == $data_rec['value']) {
                    $component['properties']['checked'] = 'checked';
                }
                break;

            case 'Html':
                return array(
                    'dagtic' => 'Html',
                    'html' => $data_rec['value']
                );

            default:
            case 'TextArea':
                $component['properties']['style'] .= ' height: 16px;';
                break;
        }

        if ($data_rec['field-style']) {
            $component['properties']['style'] .= $data_rec['field-style'];
        }

        if ($data_rec['field-class']) {
            $component['additionalClass'] .= ' ' . $data_rec['field-class'];
        }

        if ($component['label']['text'] && $data_rec['controlli']) {
            $arrayCtr = unserialize($data_rec['controlli']);
            if ($this->praLibElaborazioneDati->ctrCampiRaccoltaDati($arrayCtr)) {
                $component['label']['text'] .= ' <span style="color: red;">*</span>';
            }
        }

        return $component;
    }

    /**
     * Funzione interna per la composizione dell'HTML partendo da un array
     * $component.
     * 
     * @param array $component
     * @return string
     */
    private function getComponentHtml($component) {
        switch ($component['dagtic']) {
            case 'Text':
            case 'Importo':
                return itaComponents::getHtmlItaEdit($component);

            case 'Data':
                return itaComponents::getHtmlItaDatepicker($component);

            default:
            case 'TextArea':
                return itaComponents::getHtmlItaEditMultiline($component);

            case 'Select':
                return itaComponents::getHtmlItaSelect($component);

            case 'Password':
                return itaComponents::getHtmlItaPassword($component);

            case 'CheckBox':
                return itaComponents::getHtmlItaCheckbox($component);

            case 'RadioGroup':
                /*
                 * Per un RadioGroup stampo un radio per ciauscun RadioButton
                 * associato.
                 */
                $html = '';

                foreach ($component['radio'] as $radioComponent) {
                    $html .= itaComponents::getHtmlItaRadio($radioComponent);
                }

                return $html;

            case 'Html':
                return $component['html'];

            case 'Hidden':
                return '';
        }
    }

}
