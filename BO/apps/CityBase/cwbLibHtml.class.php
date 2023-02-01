<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';

/**
 *
 * Utility HTML Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibHtml {

    const ICON_POS_LEFT = 1;
    const ICON_POS_RIGHT = 2;

    /**
     * Formattazione codice su grid
     * @param array $props Proprieta'
     * @return string html
     */
    public static function formatDataGridProps($props) {
        $beginTag = '';
        $endTag = '';
        $html = '<span';
        $hasSpanStyle = false;
        $spanStyle = '';
        $value = $props['value'];

        // style
        if (array_key_exists('style', $props)) {
            if (is_array($props['style'])) {
                foreach ($props['style'] as $current) {
                    self::tagByStyle($current, $beginTagTmp, $endTagTmp);
                    $beginTag .= $beginTagTmp;
                    $endTagTmp .= $endTagTmp;
                }
            } else {
                self::tagByStyle($props['style'], $beginTag, $endTag);
            }
        }

        // valueFormat
        if (array_key_exists('valueFormat', $props)) {
            switch ($props['valueFormat']) {
                case 'date':
                    $value = date('d-m-Y', strtotime($props['value']));
                    break;
                case 'concat':
                    $value = '';
                    $addSpace = false;
                    foreach ($props['value'] as $v) {
                        if ($addSpace) {
                            $value .= ' ';
                        } else {
                            $addSpace = true;
                        }
                        $value .= trim($v);
                    }
                    break;
                default:
                    break;
            }
        }

        // textcolor
        if (array_key_exists('textColor', $props)) {
            $hasSpanStyle = true;
            $spanStyle .= 'color:' . $props['textColor'];
        }

        // bgcolor
        if (array_key_exists('bgColor', $props)) {
            $hasSpanStyle = true;
            $spanStyle .= 'background:' . $props['bgColor'];
            $spanStyle .= ';width:100%;height:100%;display:block';
        }

        // iconPath
        if (array_key_exists('iconPath', $props)) {
            $hasSpanStyle = true;
            $iconData = itaImg::base64src($props['iconPath']);
            $spanImg = "<img style=\"position:relative;margin:2px;\" src=\"" . $iconData . "\"></img>";
        }

        $html .= $hasSpanStyle ? ' style="' . $spanStyle . '"' : '';
        $html .= ">$beginTag$value$endTag</span>";

        // Aggiunge icona
        if (strlen($spanImg) > 0) {
            switch ($props['iconPos']) {
                case cwbLibHtml::ICON_POS_LEFT:
                    $html = $spanImg . $html;
                    break;
                case cwbLibHtml::ICON_POS_RIGHT:
                    $html .= $spanImg;
                    break;
            }
        }

        return $html;
    }

    /**
     * Formattazione codice su DataGrid
     * @param int $value Valore
     * @param string $style stile (bold/italic/underline). E' anche possibile 
     *                      passare al metodo un array di stili ('bold','italic');
     * @return string Html
     */
    public static function formatDataGridCod($value, $style = 'bold') {
        return self::formatDataGridProps(array(
                    'value' => $value,
                    'style' => $style
        ));
    }

    private static function tagByStyle($style, &$beginTag, &$endTag) {
        switch ($style) {
            case 'italic':
                $tag = 'i';
                break;
            case 'underline':
                $tag = 'u';
                break;
            default:
                $tag = 'b';
                break;
        }
        $beginTag = "<$tag>";
        $endTag = "</$tag>";
    }

    /**
     * Impostazione textcolor su DataGrid
     * @param string $value Valore
     * @param string $textColor colore
     * @return string Html
     */
    public static function formatDataGridTextColor($value, $textColor) {
        return self::formatDataGridProps(array(
                    'value' => $value,
                    'textColor' => $textColor
        ));
    }

    /**
     * Impostazione background color su DataGrid
     * @param string $value Valore
     * @param string $bgColor colore
     * @return string Html
     */
    public static function formatDataGridBgColor($value, $bgColor) {
        return self::formatDataGridProps(array(
                    'value' => $value,
                    'bgColor' => $bgColor
        ));
    }

    /**
     * Impostazione icona su DataGrid
     * @param string $value Testo
     * @param string $iconPath Path icona     
     * @return string Html
     */
    public static function formatDataGridIcon($value, $iconPath, $iconPos = self::ICON_POS_LEFT) {
        return self::formatDataGridProps(array(
                    'value' => $value,
                    'iconPath' => $iconPath,
                    'iconPos' => $iconPos
        ));
    }

    /**
     * Formattazione flag su DataGrid
     * @param int $value Valore
     * @param readOnly Se true, imposta il campo readonly
     * @return string Html
     */
    public static function formatDataGridFlag($value, $readOnly = true) {
        $html = '<span><input type="checkbox" '
                . ($readOnly ? 'disabled readonly ' : '')
                . ($value == 1 ? 'checked' : '')
                . '></input></span>';

        return $html;
    }

    /**
     * Formattazione data su DataGrid
     * @param int $value Valore
     * @return string Html
     */
    public static function formatDataGridDate($value) {
        return self::formatDataGridProps(array(
                    'value' => $value,
                    'valueFormat' => 'date'
        ));
    }

    /**
     * Formattazione data - campi concatenati
     * @param array $values Valori da concatenare
     * @return string Html
     */
    public static function formatDataGridConcat($values) {
        return self::formatDataGridProps(array(
                    'value' => $values,
                    'valueFormat' => 'concat'
        ));
    }

    /**
     * Setta tutti i campi di una detail a readOnly o contrario 
     * @param boolean $readOnly true setta a readOnly, false il contrario
     * @param array[] $nodevalues CURRENT_RECORD
     * @param String $formAlias nome della form e della tabella
     */
    public static function setReadOnly($readOnly, $nodevalues, $formAlias = '') {
        foreach ($nodevalues as $field => $nodevalue) {
            if ($formAlias != '') {
                $field = $formAlias . '[' . $field . ']';
            }
            Out::attributo($field, 'readonly', $readOnly ? '0' : '1');
        }
    }

    /**
     * Inclusione finestra
     * @param string $src Nome finestra da includere
     * @param string $dest Nome finestra di destinazione
     * @param string $containerName Nome container dove iniettare la finestra
     */
    public static function includiFinestra($src, $dest, $containerName) {
        $generator = new itaGenerator();
        $html = $generator->getModelHTML($src, false, $dest, true);
        Out::html($dest . "_" . $containerName, $html);
    }

    /**
     * Aggiunge pulsanti dinamici alla buttonbar
     * @param string $formName Nome form
     * @param string $divName Nome div dove inserire i pulsanti
     * @param array $pulsanti Array di pulsanti
     *      Ogni pulsante viene rappresentato logicamente da un array con le seguenti chiavi:
     *      - id         -> Id pulsante (internamente viene trasformato in nomeForm_idPulsante)     
     *      - icon       -> Icona (https://api.jqueryui.com/theming/icons/)
     *      - newline    -> 1 = va a capo riga  altrimenti 0
     *      - properties -> array di attributui (corrispondono a quelli definiti su generator)
     */
    public static function pulsantiDinamiciButtonBar($formName, $divName, $pulsanti) {
        $html = '';
        foreach ($pulsanti as $pulsante) {
            $html .= self::getHtmlItaButton($pulsante, $formName);
        }
        Out::html($formName . "_" . $divName, $html);
    }

    /**
     * Aggiunge n componenti dinamici
     * @param string $formName Nome form
     * @param string $divName Nome div dove inserire i componenti
     * @param array $components Array di componenti
     */
    public static function componentiDinamici($formName, $divName, $components) {
        foreach ($components as $component) {
            $html .= self::componentiDinamiciAggiungi($formName, $component);
        }
        Out::html($formName . "_" . $divName, $html);
    }

    /**
     * Aggiunge un componente dinamico
     * @param string $formName Nome form
     * @param string $divName Nome div dove inserire i componenti
     * @param array $components componente
     */
    public static function componenteDinamico($formName, $divName, $component) {
        $html .= self::componentiDinamiciAggiungi($formName, $component);

        Out::html($formName . "_" . $divName, $html);
    }

    private static function componentiDinamiciAggiungi($formName, $component, $isGrid = false) {
        switch ($component['type']) {
            case 'div':
                $html .= self::getHtmlDivBegin($component, $formName);
                if (array_key_exists('children', $component)) {
                    foreach ($components as $component) {
                        $html .= self::componentiDinamiciAggiungi($formName, $component['children']);
                    }
                }
                $html .= self::getHtmlDivEnd();
                break;
            case 'fieldset':
                $html .= self::getHtmlFieldsetBegin($component, $formName);
                if (array_key_exists('children', $component)) {
                    foreach ($component['children'] as $childComponent) {
                        $html .= self::componentiDinamiciAggiungi($formName, $childComponent);
                    }
                }
                $html .= self::getHtmlFieldsetEnd();
                break;
            case 'span':
                $html .= self::getHtmlSpan($component, $formName, $isGrid);
                break;
            case 'ita-button':
                $html .= self::getHtmlItaButton($component, $formName, $isGrid);
                break;
            case 'ita-edit':
                $html .= self::getHtmlItaEdit($component, $formName, $isGrid);
                break;
            case 'ita-edit-multiline':
                $html .= self::getHtmlItaEditMultiline($component, $formName, $isGrid);
                break;
            case 'ita-number':
                $component['additionalClass'][] = " {formatter: 'number', formatterOptions: { precision: 0,decimal: '', thousand: ''}}"; // aggiunge il formatter per i number
                $html .= self::getHtmlItaEdit($component, $formName, $isGrid);
                break;
            case 'ita-decimal':
                // per prendere il val() dei campi importo bisogna passare per lo stesso giro degli edit da generator per 
                // gestire le formattazioni
                self::getComponentId($component, $formName, $componentName, $componentId, $isGrid); // calcolo componentId
                $component['customOnChangeEventReturn'] = "function(){ var e = $('#" . $componentId . "'); e.blur(); return e.hasClass('currency-display') ? e.get(0).getAttribute('data-ita-value') : e.get(0).value; }";
                $component['additionalClass'][] = " {formatter: 'number', formatterOptions: { precision: 2,decimal: ',', thousand: '.'}}"; // aggiunge il formatter per i number
                $html .= self::getHtmlItaEdit($component, $formName, $isGrid);
                break;
            case 'ita-datepicker':
                $html .= self::getHtmlItaDatepicker($component, $formName, $isGrid);
                break;
            case 'ita-edit-date':
                $component['additionalClass'][] = 'ita-date'; // aggiunge la class ita-date ad un ita-edit
                if (!array_key_exists('formatter', $component)) {
                    $component['formatter'] = 'formatItaDate';
                }
//                if(isSet($component['properties']['value'])){
//                    $timestamp = strtotime($component['properties']['value']);
//                    if($timestamp){
//                        $component['properties']['value'] = date('d/m/Y',$timestamp);
//                    }
//                    else{
//                        $component['properties']['value'] = '';
//                    }
//                }

                $html .= self::getHtmlItaEdit($component, $formName, $isGrid);
                break;
            case 'ita-edit-lookup':
                $html .= self::getHtmlItaEditLookup($component, $formName, $isGrid);
                break;
            case 'ita-readonly':
                $html .= self::getHtmlItaReadonly($component, $formName, $isGrid);
                break;
            case 'ita-checkbox':
                $html .= self::getHtmlItaCheckbox($component, $formName, $isGrid);
                break;
            case 'ita-select':
                $html .= self::getHtmlItaSelect($component, $formName, $isGrid);
                break;
            case 'jqgrid':
                $html .= self::getHtmlJqgrid($component, $formName, $isGrid);
                break;
            case 'ita-radio':
                $html .= self::getHtmlItaRadio($component, $formName, $isGrid);
                break;
            case 'ita-password':
                $html .= self::getHtmlItaPassword($component, $formName, $isGrid);
                break;
        }

        return $html;
    }

    /**
     * Crea tag di apertura div
     * @param string $component Dati div
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlDivBegin($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);
        $html = '<div id="' . $componentId . '"'
                . ($component['style'] ? (' style="' . $component['style'] . '" ') : '')
                . ($component['title'] ? (' title="' . $component['title'] . '" ') : '')
                . ($component['class'] ? (' class="' . $component['class'] . '" ') : '')
                . '>';

        return $html;
    }

    /**
     * Crea tag di chiusura div
     * @return string html
     */
    public static function getHtmlDivEnd() {
        return '</div>';
    }

    /**
     * Crea tag di apertura div
     * @param string $component Dati div
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlFieldsetBegin($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);
        $html = '<fieldset id="' . $componentId . '" '
                . ' style="display: block; margin-left: 2px; margin-right: 2px; padding:0.35em 0.75em 0.625em 0.75em; border: 2px groove" '
                . '>'
                . ' <legend>' . $component['title'] . '</legend>';

        return $html;
    }

    /**
     * Crea tag di chiusura div
     * @return string html
     */
    public static function getHtmlFieldsetEnd() {
        return '</fieldset>';
    }

    /**
     * Crea html pulsante (ita-button)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaButton($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if ($component['icon']) {
            $icon = "{iconLeft:'ui-icon " . $component['icon'] . "'}";
        } else {
            $icon = '';
        }

        $html = '<div class="ita-field">' .
                '<button type="button" id="' . $componentId . '" name="' . $componentId .
                '" class="ita-button ita-element-animate ' . $icon . ' ui-corner-all ui-state-default" ' .
                ($component['title'] ? ' title="' . $component['title'] . '" ' : '') .
                self::getHtmlComponentProperties($component) .
                '></button>' .
                '</div>' . ($component['newline'] === 1 ? '<br/>' : '');

        return $html;
    }

    /**
     * Crea html edit (ita-edit)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlSpan($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        if (!$isGrid) {
            $html .= self::getHtmlLabel($component);
        }

        $value = '';
        if (isSet($component['properties']['value'])) {
            $value = htmlentities($component['properties']['value']);
            unset($component['properties']['value']);
        }

        // input
        $html .= '<span id="' . $componentId . '" name="' . $componentId . '"' .
                ' class="' . $additionalClass . '" ' . self::getHtmlComponentProperties($component) .
                '>' . $value . '</span>';

        $html .= '</div>';
        if (!$isGrid) {
             $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    public static function getHtmlItaDatepicker($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        if (!$isGrid) {
            $html .= self::getHtmlLabel($component);
        }

        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];

        $html .= '<input type="text" id="' . $componentId . '" name="' . $componentId . '"' .
                ($onChangeEvent ? ('onchange="' . self::getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid) . '"') : '' ) .
                ' class="ita-edit ita-datepicker' . $additionalClass . '" ' .
                self::getHtmlComponentProperties($component) .
                '></input>';

        $html .= '</div>';
        if (!$isGrid) {
             $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html edit (ita-edit)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaEdit($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        if (!$isGrid) {
            $html .= self::getHtmlLabel($component);
        }

        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];

        // input
        $html .= '<input type="text" id="' . $componentId . '" name="' . $componentId . '"' .
                ($onChangeEvent ? ('onchange="' . self::getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid) . '"') : '' ) .
                ' class="ita-edit' . $additionalClass . ' ui-widget-content ui-corner-all valid" ' .
                self::getHtmlComponentProperties($component) .
                '></input>';

        $html .= '</div>';
        if (!$isGrid) {
             $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html radio (ita-radio)
     * @param string $component dati del radio
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaRadio($component, $formName = '') {
        $html = '';
        $name = $component['name']; // name che raggruppa i singoli radio
        foreach ($component['radios'] as $singleRadio) {
            self::getComponentId($singleRadio, $formName, $componentName, $componentId, false);

            if (isSet($singleRadio['ita-field-style'])) {
                $itaFieldStyle = $singleRadio['ita-field-style'];
                unset($singleRadio['ita-field-style']);
            }
            $additionalClass = self::getAdditionalClass($singleRadio);
            $html .= '<div id="' . $componentId . '_field" class="ita-field" style="' . $itaFieldStyle . '">';
            $html .= self::getHtmlLabel($singleRadio);

            $onChangeEvent = array_key_exists('onChangeEvent', $singleRadio) && $singleRadio['onChangeEvent'];

            $checked = $singleRadio['checked'] ? 'checked' : '';
            
            // input
            $html .= '<input type="radio" value="' . $singleRadio['value'] . '" id="' . $componentId . '" name="' . $name . '"' .
                    ($onChangeEvent ? ('onchange="' . self::getHtmlOnChangeEvent($singleRadio, $componentId, $componentName, false) . '"') : '' ) .
                    ' class="ita-edit ita-radio ' . $additionalClass . ' ui-widget-content ui-corner-all" ' .
                    self::getHtmlComponentProperties($singleRadio) .
                    ' '.$checked.'></input>';

            $html .= '</div>';
        }

        return $html;
    }
    
    /**
     * Crea html edit (ita-edit)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaPassword($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        if (!$isGrid) {
            $html .= self::getHtmlLabel($component);
        }

        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];

        // input
        $html .= '<input type="password" id="' . $componentId . '" name="' . $componentId . '"' .
                ($onChangeEvent ? ('onchange="' . self::getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid) . '"') : '' ) .
                ' class="ita-password' . $additionalClass . ' ui-widget-content ui-corner-all valid" ' .
                self::getHtmlComponentProperties($component) .
                '></input>';

        $html .= '</div>';
        if (!$isGrid) {
             $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html edit (ita-edit)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaEditMultiline($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (!$component['cols']) {
            $component['cols'] = 60;
        }
        if (!$component['rows']) {
            $component['rows'] = 2;
        }

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        if (!$isGrid) {
            $html .= self::getHtmlLabel($component);
        }
        
        $value = (isSet($component['options']['value']) ? $component['options']['value'] : '');

        // input
        $html .= '<textarea id="' . $componentId . '" name="' . $componentId . '"' .
                ' cols="' . $component['cols'] . '" rows="' . $component['rows'] . '"' .
                ' class="ita-edit ita-edit-multiline ' . $additionalClass . ' ui-widget-content ui-corner-all valid" ' .
                self::getHtmlComponentProperties($component) .
                '>'.$value.'</textarea>';

        $html .= '</div>';
        if (!$isGrid) {
             $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html edit (ita-lookup)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaEditLookup($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);
        
        $html = '<div class="ita-field" style="width:' . $component['size'] . 'px;">';
        if (!$isGrid) {
            // label
            $html .= self::getHtmlLabel($component);
        }

        // input
        $html .= '<input type="text" id="' . $componentId . '" name="' . $componentId .
                '" class="ita-edit ita-edit-lookup ita-edit-onchange ui-widget-content ui-corner-all" ' .
                self::getHtmlComponentProperties($component) .
                '></input>' . ($component['newline'] === 1 ? '<br/>' : '');

        $html .= '</div>';  // ita-field

        return $html;
    }

    /**
     * Crea html edit (ita-readonly)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaReadonly($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);

        if (isSet($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }
        $additionalClass = self::getAdditionalClass($component);
        
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';

        if (!$isGrid) {
            // label
            $html .= self::getHtmlLabel($component);
        }
        // input
        $html .= '<input readonly="readonly" type="text" id="' . $componentId . '" name="' . $componentId .
                '" class="ita-readonly ita-edit ui-widget-content ui-corner-all" ' .
                self::getHtmlComponentProperties($component) .
                '></input>';

        $html .= '</div>';
        if ($isGrid) {
            $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html checkbox (ita-checkbox)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaCheckbox($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);
        $html = '<div class="ita-field">';
        if (!$isGrid) {
            // label
            $html .= self::getHtmlLabel($component);
        }

        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];

        // input
        $html .= '<input type="checkbox" id="' . $componentId . '" name="' . $componentId .
                '" class="ita-edit ita-checkbox ita-edit-onchange ui-widget-content ui-corner-all" ' .
                self::getHtmlComponentProperties($component) .
                ($onChangeEvent ? 'onchange="' . self::getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid) . '"' : '' ) .
                '>'
                . '</input>';

        $html .= '</div>';
        if ($isGrid) {
            $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }

        return $html;
    }

    /**
     * Crea html select - Combobox (ita-select)
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlItaSelect($component, $formName = '', $isGrid = false) {
        self::getComponentId($component, $formName, $componentName, $componentId, $isGrid);
        
        $html = '<div class="ita-field">';
        if (!$isGrid) {
            // label
            $html .= self::getHtmlLabel($component);
        }
        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];
        $additionalClass = self::getAdditionalClass($component);

        // input
        $html .= '<select id="' . $componentId . '" name="' . $componentId .
                '" class="ita-select ita-edit ita-edit-onchange ui-widget-content ui-corner-all ' . $additionalClass . '" ' .
                self::getHtmlComponentProperties($component) .
                ($onChangeEvent ? 'onchange="' . self::getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid) . '"' : '' ) . '>' .
                self::getHtmlItaSelectOption($component['options']) .
                '</select>';

        $html .= '</div>';
        if ($isGrid) {
            $html .= ($component['newline'] === 1 ? '<br/>' : '');
        }


        return $html;
    }

    /**
     * Crea html jqgrid
     * @param string $component Dati componente
     * @param string $formName Nome form
     * @return string html
     */
    public static function getHtmlJqgrid($component, $formName = '') {
        self::getComponentId($component, $formName, $componentName, $componentId, false);
        $html = '<div class="ita-field" >';

        // label
        $html .= self::getHtmlLabel($component);

        $props = '';
        $separator = "";
        if ($component['properties']) {
            foreach ($component['properties'] as $key => $value) {
                $props .= $separator . $key . ':' . $value . ' ';
                $separator = ",";
            }
        }

        // jqgrid
        $html .= '<div style="float:left" >'
                . '<table class="ita-jqGrid ita-jqGrid '
                . '{' . $props . '}" id="' . $componentId . '">';
        $th = '<thead>';
        $td = '<tbody><tr id="baseRow" style="background-color:">';
        foreach ($component['columns'] as $key => $value) {
            $th .= '<th id="' . $formName . '_th' . $value['id'] . '">' . $value['label'] . '</th>';

            $width = '';
            if ($value['width']) {
                $width = 'width="' . $value['width'] . '"';
            }
            $class = '';
            if ($value['class']) {
                $class = 'class="' . $value['class'] . '"';
            }
            $td .= '<td ' . $width . ' id="' . $value['id'] . '" ' . $class . '></td>';
        }

        $th .= '</thead>';
        $td .= ' </tr></tbody>';

        $html .= $th . $td . '</table>';

        $html .= '</div> </div>' . ($component['newline'] === 1 ? '<br/>' : '');

        return $html;
    }

    private static function getHtmlLabel($component) {
        $html = '';
        if (array_key_exists('label', $component)) {
            $html = '<label class="input ' . $component['label']['position'] . '" ' .
                    'style="' . $component['label']['style'] . '" ' .
                    'title="' . $component['label']['title'] . '" ' .
                    'for="' . $componentId . '" ' .
                    'id="' . $componentId . '_lbl">' .
                    $component['label']['text'] .
                    '</label>';
        }

        return $html;
    }

    private static function getAdditionalClass($component) {
        $additionalClass = '';
        if (array_key_exists('additionalClass', $component)) {
            if (!is_array($component['additionalClass'])) {
                $component['additionalClass'] = array($component['additionalClass']);
            }
            foreach ($component['additionalClass'] as $value) {
                $additionalClass .= ' ' . $value . ' ';
            }
        }

        return $additionalClass;
    }

    private static function getHtmlItaSelectOption($options) {
        $html = '';
        foreach ($options as $option) {
            $html .= '<option id="' . $option['id'] . '"' . (array_key_exists('selected', $option) && $option['selected'] == 1 ? 'selected="selected"' : '') .
                    ' value="' . $option['value'] . '">' . (array_key_exists('text', $option) ? $option['text'] : $option['value']) . '</option>';
        }
        return $html;
    }

    private static function getHtmlComponentProperties($component) {
        $html = '';
        if (array_key_exists('properties', $component)) {
            foreach ($component['properties'] as $key => $value) {
                $html .= $key . '="' . $value . '" ';
            }
        }
        return $html;
    }

    private static function getComponentId($component, $formName = '', &$componentName, &$componentId, $isGrid = false) {
        $componentName = (strlen($formName) === 0 ? $component['id'] : $formName . '_' . $component['id']);

        if ($isGrid) {
            $componentId = $componentName . '_' . $component['rowKey'];
        } else {
            $componentId = $componentName;
        }
    }

    /**
     * Aggiunge componente all'interno di una grid
     * @param array $component Dati componente
     *      - type: Identifica il tipo di componente
     * @return string Html
     */
    public static function addGridComponent($formName, $component) {
        return self::componentiDinamiciAggiungi($formName, $component, true);
    }
    
    public static function addHtmlComponent($formName, $component){
        return self::componentiDinamiciAggiungi($formName, $component);
    }

    /*
     * rimuove il primo <BR> all'interno del div passato 
     * (es. nel caso di una form innestata tramite openInner, il framework mette un br all'interno del div wrapper)
     * 
     * @param $divId id del div
     */

    public static function removeFirstDivBr($divId) {
        Out::codice("$('#$divId').find('br:first').remove();");
    }

    private static function getHtmlOnChangeEvent($component, $componentId, $componentName, $isGrid = false) {
        // abilito l'evento onchange
        $onChangeEvent = array_key_exists('onChangeEvent', $component) && $component['onChangeEvent'];
        if ($onChangeEvent) {
            $additionalData = '';
            if (array_key_exists('additionalData', $component)) {
                foreach ($component['additionalData'] as $key => $value) {
                    $additionalData .= $key . ":'" . $value . "',";
                }
            }

            if (array_key_exists('customOnChangeEventReturn', $component)) {
                $function = $component['customOnChangeEventReturn'];
                //    $function = "function(){ var e = $('#" . $componentId . "'); e.blur(); return e.hasClass('currency-display') ? e.get(0).getAttribute('data-ita-value') : e.get(0).value; }";
            } else if (array_key_exists('formatter', $component)) {
                $function = "function(){ return " . $component['formatter'] . "($('#" . $componentId . "').val()); }";
            } else {
                $function = "function(){ return $('#" . $componentId . "').val(); }";
            }


            $onclickFn = "itaGo('ItaCall', '', 
                {
                    id:'" . $componentId . "',
                    name:'" . $componentName . "'," .
                    ($isGrid ? "event: 'afterSaveCell'" : "event: 'onChangeComponent'") . "," .
                    "model:'" . $component['model'] . "',
                    rowid:'" . $component['rowKey'] . "',
                    value:" . $function .
                    (strlen($additionalData) > 0 ? (', ' . substr($additionalData, 0, -1)) : '') . "
                })";

            return $onclickFn;
        } else {
            return '';
        }
    }
    
    public static function getOnChangeEventJqGridFilter($nameForm, $gridName){
        return Out::getOnChangeEventJqGridFilter($nameForm, $gridName);
    }

    /**
     * Consente la creazione di un timer che ad intervalli predefiniti scatena l'evento ontimer trasmettendo alcuni dati
     * 
     * @param string $container: id dell'elemento html che contiene il timer (usare un div possibilmente)
     * @param string $model: model a cui inviare l'evento
     * @param int $delay (facoltativo): intervallo di richiamo in millisecondi
     * @param array $data (facoltativo): array associativo contenente i dati da trasmettere
     * @param boolean $enable (facoltativo): specifica se il timer è attivo fin dall'inizio
     * @param boolean $backgroundRefresh (facoltativo): specifica se il timer rimane attivo anche quando si visualizza un'altra tab
     */
    public static function timerCreate($container, $model, $delay = 60000, $data = null, $enable = true, $backgroundRefresh = false) {
        $script = '
<script>
  var timer = null;
  var backgroundRefresh = ' . (($backgroundRefresh) ? 'true' : 'false') . ';
  
  function pollingTimer(controller){
    if(controller.length){
      var data = controller.attr("data");
      var model = controller.attr("model");

      var activeTab = $("li.ui-state-active","#mainTabs").attr("id").replace("tab-index-","");
      if(typeof controller.attr("enabled") !== "undefined" && (activeTab == model || backgroundRefresh)){
        var out = "model="+model;
        if(data && data != ""){
          out += "||"+data;
        }

        itaGo("ItaCall", "", {
          bloccaui: false,
          asyncCall: true,
          event: "ontimer",
          model: model,
          id: out
        });
      }
    }
  }
  
  $(function(){
    $("#' . $container . '").on("remove", function(){
      clearInterval(timer);
    });

    timer = setInterval(function(){
                pollingTimer($("#' . $container . '"));

            },' . $delay . ');
  });
</script>';


        Out::innerHtml($container, $script);
        Out::attributo($container, 'model', 0, $model);


        if ($enable) {
            self::timerEnable($container);
        }


        if (isSet($data) && is_array($data)) {
            $data = self::timerSetData($data);
        }
    }

    /**
     * Consente di impostare i dati da far trasmettere all'evento (nb. quando richiamato i dati preesistenti vengono sovrascritti)
     * 
     * @param string $container: id dell'elemento html che contiene il timer (usare un div possibilmente)
     * @param array $data (facoltativo): array associativo contenente i dati da trasmettere
     */
    public static function timerSetData($container, $data) {
        $attribute = array();
        foreach ($data as $key => $value) {
            $attribute[] = $key . "=" . $value;
        }
        $attribute = implode("||", $attribute);
        Out::attributo($container, 'data', 0, $attribute);
    }

    /**
     * Consente di leggere i dati che vengono trasmessi allo scatenarsi dell'evento
     * 
     * @return false|array: se i dati non hanno l'aspetto dovuto ritorna false (es: evento chiamato da un altro timer), sennò ritorna l'array associativo contenente i dati.
     */
    public static function timerReadData() {
        if (!isSet($_POST['id']) || stripos($_POST['id'], 'model=') !== 0) {
            return false;
        }


        $data = array();
        foreach (explode('||', $_POST['id']) as $value) {
            $value = explode('=', $value);
            $data[$value[0]] = $value[1];
        }
        return $data;
    }

    /**
     * Consente di azzerare i dati trasmessi allo scatenersi dell'evento
     * 
     * @param string $container: id dell'elemento html che contiene il timer (usare un div possibilmente)
     */
    public static function timerRemoveData($container) {
        Out::attributo($container, 'data', 1);
    }

    /**
     * Attiva il timer
     * 
     * @param string $container: id dell'elemento html che contiene il timer (usare un div possibilmente)
     */
    public static function timerEnable($container) {
        Out::attributo($container, 'enabled', 0);
    }

    /**
     * Disattiva il timer
     * 
     * @param string $container: id dell'elemento html che contiene il timer (usare un div possibilmente)
     */
    public static function timerDisable($container) {
        Out::attributo($container, 'enabled', 1);
    }

    /**
     * Attiva il parser html di itaEngine su un dato elemento
     * @param string $container
     */
    public static function attivaJSElemento($container) {
        Out::attivaJSElemento($container);
    }

    /**
     * Imposta per un campo di testo l'autocomplete con i valori passati
     * @param string $field Campo a cui assegnare un autocomplete
     * @param array $tags Array dei valori da assegnare
     */
    public static function autocompleteField($field, $tags = array()) {
        if (!is_array($tags) || empty($tags)) {
            return;
        }
        array_walk($tags, function(&$v,$k){
            $v = str_replace('"', '\"', $v);
        });
        $tags = '"' . implode('","', $tags) . '"';
        $uniqueClass = preg_replace('/[^A-Za-z]/', '', $field) . time() . rand(0, 1000); //workaround con uniqueClass perché l'autocomplete non funziona con campi aventi per id un array
        $command = '$(".' . $uniqueClass . '").autocomplete({source:[' . $tags . ']});';

        Out::addClass($field, $uniqueClass);
        Out::codice($command);
        Out::delClass($field, $uniqueClass);
    }

    public static function setJqGridGroupHeader($gridId, $listGroupHeaders) {
        if (!$listGroupHeaders) {
            return;
        }

        $groupHeaders = '[';
        $separator = '';

        foreach ($listGroupHeaders as $value) {
            $groupHeaders .= $separator . "{startColumnName: '" . $value['startColumnName'] . "', numberOfColumns: " . $value['numberOfColumns'] . ",titleText: '" . $value['titleText'] . "'}";
            $separator = ',';
        }

        $groupHeaders .= ']';

        $js = "jQuery('#" . $gridId . "').jqGrid('setGroupHeaders', 
        {useColSpanStyle: false,groupHeaders:" . $groupHeaders . "});";

        Out::codice($js);

        // itaStyle formatta il testo del th mettendolo text-align :left invece che center
//        $style ="jQuery('#cwbBtaNazion_gridBtaNazion').find( 'td' ).css('background-color','red');";
//        Out::codice($style);
    }

    /**
     * Crea l'html di un elemento clickabile che scatena un evento onclick
     * @param <string> $model model di riferimento ($this->nameForm)
     * @param <string> $id id dell'elemento da creare
     * @param <string> $text contenuto html dell'elemento da creare
     * @param <array> $style array contenente gli stili css in formato array('color'=>'#000000', 'border'=>'0px')
     * @return <string> html generato
     */
    public static function getHtmlClickableObject($model, $id, $text, $style = array(), $title = "", $serialize=false) {
        if (!is_array($style)) {
            $style = array();
        }
//        if (!isSet($style['background'])) {
//            $style['background'] = 'none';
//        }
//        if (!isSet($style['border'])) {
//            $style['border'] = '0';
//        }
//        if (!isSet($style['border-radius'])) {
//            $style['border-radius'] = '0px';
//        }
//        if (!isSet($style['width'])) {
//            $style['width'] = 'auto';
//        }
//        if (!isSet($style['height'])) {
//            $style['height'] = 'auto';
//        }
//        if (!isSet($style['margin'])) {
//            $style['margin'] = '0px';
//        }
//        if (!isSet($style['padding'])) {
//            $style['padding'] = '0px';
//        }
        if(!isSet($style['text-decoration'])){
            $style['text-decoration'] = 'none';
        }

        $text = str_replace('"', "'", $text);

        $styleHtml = '';
        foreach ($style as $key => $value) {
            $styleHtml .= $key . ':' . $value . ';';
        }
        
        $function = $serialize ? 'ItaForm' : 'ItaCall';
        
        $onclickFn = "itaGo('".$function."', '',{
                    id:'$id',
                    name:'$id',
                    event:'onClick',
                    model:'$model',
                    validate: false
                })";
        return '<a href="#" id="'.$id.'" onClick="event.stopPropagation(); '.$onclickFn.'" style="'.$styleHtml.'" title="'.$title.'">'.$text.'</a>';
        
//        return '<button title="' . $title . '" type="button" id="' . $id . '" name="' . $id . '" value="' . $text . '" class="ita-button" style="' . $styleHtml . '" />';
    }

    /**
     * Crea un elemento clickabile che scatena un evento onclick
     * @param <string> $model model di riferimento ($this->nameForm)
     * @param <string> $container id del contenitore in cui inserire l'elemento
     * @param <string> $id id dell'elenento da inserire
     * @param <string> $text contenuto html dell'elemento da creare
     * @param <array> $style array contenente gli stili css in formato array('color'=>'#000000', 'border'=>'0px')
     * @param <string> $append: ''=>sovrascrive il contenuto del container
     *                          'append'=>mette l'elemento in coda
     *                          'prepend'=>mette l'elemento in test
     * 
     */
    public static function insertClickableObject($model, $container, $id, $text, $style = array(), $append = '', $serialize=false) {
        $html = self::getHtmlClickableObject($model, $id, $text, $style, '', $serialize);
        Out::html($container, $html, $append);
        
        
//        self::attivaJSElemento($id);
    }

    public static function getHtmlJqgridOrderSpan($orderBy = none) {
        $html = '<span class="s-ico" style="display:none">';
        $html .= '<span sort="asc" class="ui-grid-ico-sort ui-icon-asc ' . ($orderBy == 'asc' ? '' : 'ui-state-disabled') . ' ui-icon ui-icon-triangle-1-n ui-sort-ltr"></span>';
        $html .= '<span sort="desc" class="ui-grid-ico-sort ui-icon-desc ' . ($orderBy == 'desc' ? '' : 'ui-state-disabled') . ' ui-icon ui-icon-triangle-1-s ui-sort-ltr"></span>';
        $html .= '</span>';

        return $html;
    }

}

?>