<?php

class itaComponents {

    /**
     * Aggiunge nel container specificato un'icona con un'altra sovrapposta.
     * 
     * @param text $containerId Id del container in cui aggiungere l'elemento.
     * @param text $mainIcon Classe per l'icona principale o base64 dell'icona.
     * @param text $sideIcon Classe per l'icona secondaria o base64 dell'icona.
     * @param int $sizeMain Dimensioni dell'elemento.
     * @param text $mainColor Colore per l'icona principale (valido solo con
     * jquery-ui font icon).
     * @param text $sideColor Colore per l'icona secondaria (valido solo con
     * jquery-ui font icon).
     */
    static public function Icon($containerId, $icon, $size = 32, $color = '', $toolTip = '') {
        Out::html($containerId, self::getHtmlSideIcon($icon, $size, $color, $toolTip), 'append');
    }

    /**
     * Ritorna l'HTML per la visualizzazione di un'icona con un'altra sovrapposta.
     * 
     * @param text $mainIcon Classe per l'icona principale o base64 dell'icona.
     * @param text $sideIcon Classe per l'icona secondaria o base64 dell'icona.
     * @param int $sizeMain Dimensioni dell'elemento.
     * @param text $mainColor Colore per l'icona principale (valido solo con
     * jquery-ui font icon).
     * @param text $sideColor Colore per l'icona secondaria (valido solo con
     * jquery-ui font icon).
     */
    static public function getHtmlIcon($icon, $size = 32, $color = '', $toolTip = '') {

        $toolTipAttr = '';
        $toolTipClass = '';
        if ($toolTip) {
            $toolTipClass = 'ita-tooltip';
            $toolTipAttr = 'title = "' . htmlspecialchars($toolTip, ENT_COMPAT, 'ISO-8859-15') . '"';
        }

        $html = '<div class = "ita-html" style="display: inline-block;">';

        $styleSize = "vertical-align: baseline; display: inline-block; background-size: contain; width: {$size}px; height: {$size}px; font-size: {$size}px;";

        if ($color) {
            $styleSize .= " color: {$color};";
        }

        if (strpos($icon, 'data:') === 0) {
            $html .= '<img  class="' . $toolTipClass . ' ' . $toolTipAttr . ' src="' . $icon . '" style="' . $styleSize . '">';
        } else {
            $html .= '<span class="' . $icon . ' ' . $toolTipClass . '" ' . $toolTipAttr . ' style="' . $styleSize . '"></span>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Aggiunge nel container specificato un'icona con un'altra sovrapposta.
     * 
     * @param text $containerId Id del container in cui aggiungere l'elemento.
     * @param text $mainIcon Classe per l'icona principale o base64 dell'icona.
     * @param text $sideIcon Classe per l'icona secondaria o base64 dell'icona.
     * @param int $sizeMain Dimensioni dell'elemento.
     * @param text $mainColor Colore per l'icona principale (valido solo con
     * jquery-ui font icon).
     * @param text $sideColor Colore per l'icona secondaria (valido solo con
     * jquery-ui font icon).
     */
    static public function sideIcon($containerId, $mainIcon, $sideIcon, $sizeMain = 32, $mainColor = '', $sideColor = '') {
        Out::html($containerId, self::getHtmlSideIcon($mainIcon, $sideIcon, $sizeMain, $mainColor, $sideColor), 'append');
    }

    /**
     * Ritorna l'HTML per la visualizzazione di un'icona con un'altra sovrapposta.
     * 
     * @param text $mainIcon Classe per l'icona principale o base64 dell'icona.
     * @param text $sideIcon Classe per l'icona secondaria o base64 dell'icona.
     * @param int $sizeMain Dimensioni dell'elemento.
     * @param text $mainColor Colore per l'icona principale (valido solo con
     * jquery-ui font icon).
     * @param text $sideColor Colore per l'icona secondaria (valido solo con
     * jquery-ui font icon).
     */
    static public function getHtmlSideIcon($mainIcon, $sideIcon, $sizeMain = 32, $mainColor = '', $sideColor = '') {
        $html = '<div style="display: inline-block;">';

        $top = $sizeMain * .2;
        $left = $sizeMain * .6;

        $sizeSide = $sizeMain * .7;
        $styleSizeMain = "vertical-align: baseline; display: inline-block; background-size: contain; width: {$sizeMain}px; height: {$sizeMain}px; font-size: {$sizeMain}px;";
        $styleSizeSide = "vertical-align: baseline; background-size: contain; width: {$sizeSide}px; height: {$sizeSide}px; font-size: {$sizeSide}px;";

        if ($mainColor) {
            $styleSizeMain .= " color: {$mainColor};";
        }

        if ($sideColor) {
            $styleSizeSide .= " color: {$sideColor};";
        }

        $styleSideIcon = 'margin-left: -' . $left . 'px; display: inline-block; top: ' . $top . 'px; position: relative; ' . $styleSizeSide;

        if (strpos($mainIcon, 'data:') === 0) {
            $html .= '<img src="' . $mainIcon . '" style="' . $styleSizeMain . '">';
        } else {
            $html .= '<span class="' . $mainIcon . '" style="' . $styleSizeMain . '"></span>';
        }

        if (strpos($sideIcon, 'data:') === 0) {
            $html .= '<img src="' . $sideIcon . '" style="' . $styleSizeSide . '">';
        } else {
            $html .= '<span class="' . $sideIcon . '" style="' . $styleSideIcon . '"></span>';
        }

        $html .= '</div>';

        return $html;
    }

    static private function getHtmlInput($component, $tag, $classes = '', $type = '') {
        /*
         * Carico stili aggiunti per l'ita-field
         */
        if (isset($component['ita-field-style'])) {
            $itaFieldStyle = $component['ita-field-style'];
            unset($component['ita-field-style']);
        }

        /*
         * Carico le classi CSS aggiuntive
         */
        if (array_key_exists('additionalClass', $component)) {
            if (!is_array($component['additionalClass'])) {
                $component['additionalClass'] = array($component['additionalClass']);
            }

            foreach ($component['additionalClass'] as $value) {
                $classes .= ' ' . $value;
            }
        }

        /*
         * Imposto le proprietà id/name
         */
        $component['properties'] = $component['properties'] ?: array();
        $component['properties']['id'] = $component['id'] ?: $component['properties']['id'] ?: '';
        $component['properties']['name'] = $component['properties']['name'] ?: $component['properties']['id'];

        /*
         * Inizio il disegno HTML
         */
        $html = '<div class="ita-field" style="' . $itaFieldStyle . '">';
        $html .= self::getHtmlLabel($component);

        $html .= "<$tag class=\"ita-edit $classes ita-edit-onchange ui-widget-content ui-corner-all valid\" ";

        /*
         * Aggiungo le proprietà impostate
         */
        if (array_key_exists('properties', $component)) {
            foreach ($component['properties'] as $key => $value) {
                $html .= $key . '="' . $value . '" ';
            }
        }

        if ($tag === 'input') {
            $html .= "type=\"$type\" />";
        } elseif ($tag === 'select') {
            $html .= '>' . self::getHtmlItaSelectOption($component['options']) . "</$tag>";
        } elseif ($tag === 'textarea') {
            $html .= '>' . $component['properties']['value'] . "</$tag>";
        } else {
            $html .= "></$tag>";
        }

        $html .= '</div>' . ($component['newline'] === 1 ? '<br/>' : '');

        return $html;
    }

    static private function getHtmlItaSelectOption($options) {
        $html = '';
        foreach ($options as $option) {
            if (isset($option['group'])) {
                $html .= '<optgroup label="' . $option['group'] . '"></optgroup>';
                continue;
            }

            $html .= '<option id="' . $option['id'] . '"' . (array_key_exists('selected', $option) && $option['selected'] == 1 ? 'selected="selected"' : '') .
                    ' value="' . $option['value'] . '">' . (array_key_exists('text', $option) ? $option['text'] : $option['value']) . '</option>';
        }
        return $html;
    }

    static public function getHtmlLabel($component) {
        $html = '';

        if (array_key_exists('label', $component)) {
            $html = '<label class="input ' . ($component['label']['position'] ?: 'sx') . '" ' .
                    'for="' . $component['properties']['id'] . '" ' .
                    'style="' . $component['label']['style'] . '">' .
                    $component['label']['text'] .
                    '</label>';
        }

        return $html;
    }

    static public function getHtmlItaDatepicker($component) {
        return self::getHtmlInput($component, 'input', 'ita-datepicker', 'text');
    }

    static public function getHtmlItaEdit($component) {
        return self::getHtmlInput($component, 'input', '', 'text');
    }

    static public function getHtmlItaHidden($component) {
        return self::getHtmlInput($component, 'input', '', 'hidden');
    }

    static public function getHtmlItaPassword($component) {
        return self::getHtmlInput($component, 'input', 'ita-password', 'password');
    }

    static public function getHtmlItaEditMultiline($component) {
        $component['properties'] = $component['properties'] ?: array();
        $component['properties']['cols'] = $component['cols'] ?: 60;
        $component['properties']['rows'] = $component['rows'] ?: 2;

        return self::getHtmlInput($component, 'textarea', 'ita-edit-multiline');
    }

    static public function getHtmlItaEditLookup($component) {
        return self::getHtmlInput($component, 'input', 'ita-edit-lookup', 'text');
    }

    static public function getHtmlItaCheckbox($component) {
        return self::getHtmlInput($component, 'input', 'ita-checkbox', 'checkbox');
    }

    static public function getHtmlItaRadio($component) {
        return self::getHtmlInput($component, 'input', 'ita-radio', 'radio');
    }

    static public function getHtmlItaSelect($component) {
        return self::getHtmlInput($component, 'select', 'ita-select');
    }

    static public function getHtmlJqGrid($nameForm, $nameGrid, $gridOptions, $keynavButtonAdd = 'false', $keynavButtonRefresh = 'true', $keynavButtonDel = 'false') {
        $htmlGrid = $metadataJs = '';

        $metadataArray = isset($gridOptions['metadata']) ? $gridOptions['metadata'] : array();
        if (count($metadataArray)) {
            foreach ($metadataArray as $key => $value) {
                $metadataJs .= ", $key: '$value'";
            }
        }

        if ($gridOptions['treeGrid']) {
            $readerId = "idx";

            if ($gridOptions['readerId']) {
                $readerId = $gridOptions['readerId'];
            }

            if ($gridOptions['multiselect']) {
                $multiselect = ',multiselect:' . $gridOptions['multiselect'] . ',multiselectEvents:true';
                if ($gridOptions['disableselectall'] == 'true') {
                    $multiselect .= ",disableselectall:true";
                }
            }

            $navOptions = ',navGrid:true';
            $navOptions .= ',columnChooser:false';
            $navOptions .= ',navButtonRefresh:false';
            $navOptions .= ',navButtonEdit:' . ( isset($gridOptions['navButtonEdit']) ? $gridOptions['navButtonEdit'] : 'true' );
            $navOptions .= ',navButtonAdd:' . ( isset($gridOptions['navButtonAdd']) ? $gridOptions['navButtonAdd'] : $keynavButtonAdd );
            $navOptions .= ',navButtonDel:' . ( isset($gridOptions['navButtonDel']) ? $gridOptions['navButtonDel'] : $keynavButtonDel);
            $navOptions .= ',navButtonPrint:' . ( isset($gridOptions['navButtonPrint']) ? $gridOptions['navButtonPrint'] : 'false' );


            $htmlGrid = ' <table class="ita-jqGrid ita-jqGrid {caption:\'' . $gridOptions['Caption'] . '\''
                    . ',sortorder:\'' . $gridOptions['sortorder'] . '\''
                    . ',sortname:\'' . $gridOptions['sortname'] . '\''
                    . $multiselect
                    . ',rowNum:' . $gridOptions['rowNum']
                    . ',rowList:' . $gridOptions['rowList']
                    . ',width:' . $gridOptions['width']
                    . ',height:' . $gridOptions['height']
                    . ',shrinkToFit:false'
                    . $navOptions
                    . ',readerId:\'' . $readerId . '\',treeGrid:true,treeGridModel:\'adjacency\''
                    . $metadataJs
                    . ',ExpandColumn :\'' . $gridOptions['ExpCol'] . '\'}" style=\'font-size:100%\' id="' . $nameGrid . '">';
        } elseif ($gridOptions['arrayTable'] != '') {
            $readerId = "idx";

            if ($gridOptions['readerId']) {
                $readerId = $gridOptions['readerId'];
            }

            if ($gridOptions['filterToolbar']) {
                $filterBox = ',filterToolbar:' . $gridOptions['filterToolbar'];
            }

            if ($gridOptions['multiselect']) {
                $multiselect = ',multiselect:' . $gridOptions['multiselect'] . ',multiselectEvents:true';
                if ($gridOptions['disableselectall'] == 'true') {
                    $multiselect .= ",disableselectall:true";
                }
            }

            if ($gridOptions['pgbuttons']) {
                $pgbuttons = ',pgbuttons:' . $gridOptions['pgbuttons'];
            }

            if ($gridOptions['pginput']) {
                $pginput = ',pginput:' . $gridOptions['pginput'];
            }

            $sortablerows = '';
            if ($gridOptions['sortablerows']) {
                $sortablerows = ',sortablerows:' . $gridOptions['sortablerows'];
            }

            $navOptions = ',navGrid:true';
            $navOptions .= ',columnChooser:false';
            $navOptions .= ',navButtonRefresh:false';
            $navOptions .= ',navButtonEdit:' . ( isset($gridOptions['navButtonEdit']) ? $gridOptions['navButtonEdit'] : 'true' );
            $navOptions .= ',navButtonAdd:' . ( isset($gridOptions['navButtonAdd']) ? $gridOptions['navButtonAdd'] : $keynavButtonAdd );
            $navOptions .= ',navButtonDel:' . ( isset($gridOptions['navButtonDel']) ? $gridOptions['navButtonDel'] : $keynavButtonDel );
            $navOptions .= ',navButtonPrint:' . ( isset($gridOptions['navButtonPrint']) ? $gridOptions['navButtonPrint'] : 'false' );

            $htmlGrid = ' <table class="ita-jqGrid ita-jqGrid {caption:\'' . $gridOptions['Caption'] . '\''
                    . ',sortorder:\'' . $gridOptions['sortorder'] . '\''
                    . ',sortname:\'' . $gridOptions['sortname'] . '\''
                    . ',rowNum:' . $gridOptions['rowNum']
                    . ',rowList:' . $gridOptions['rowList']
                    . ',width:' . $gridOptions['width']
                    . ',height:' . $gridOptions['height']
                    . $filterBox
                    . $multiselect
                    . $pgbuttons
                    . $pginput
                    . $sortablerows
                    . ',shrinkToFit:false'
                    . $navOptions
                    . $metadataJs
                    . ',readerId:\'' . $readerId . '\'}" style=\'font-size:100%\' id="' . $nameGrid . '">';
        } elseif ($gridOptions['fileTable'] != '') {
            
        } else {
            $readerId = "";

            if ($gridOptions['readerId']) {
                $readerId = ",readerId:'" . $gridOptions['readerId'] . "'";
            }

            if ($gridOptions['filterToolbar']) {
                $filterBox = ',filterToolbar:' . $gridOptions['filterToolbar'];
            }

            if ($gridOptions['multiselect']) {
                $multiselect = ',multiselect:' . $gridOptions['multiselect'] . ',multiselectEvents:true';
                if ($gridOptions['disableselectall'] == 'true') {
                    $multiselect .= ",disableselectall:true";
                }
            }

            if ($gridOptions['pgbuttons']) {
                $pgbuttons = ',pgbuttons:' . $gridOptions['pgbuttons'];
            }

            if ($gridOptions['pginput']) {
                $pginput = ',pginput:' . $gridOptions['pginput'];
            }

            if ($gridOptions['sortorder'] == '') {
                $gridOptions['sortorder'] = 'asc';
            }

            $navOptions = ',navGrid:true';
            $navOptions .= ',columnChooser:false';
            $navOptions .= ',navButtonRefresh:' . $keynavButtonRefresh;
            $navOptions .= ',navButtonEdit:' . ( isset($gridOptions['navButtonEdit']) ? $gridOptions['navButtonEdit'] : 'true' );
            $navOptions .= ',navButtonAdd:' . ( isset($gridOptions['navButtonAdd']) ? $gridOptions['navButtonAdd'] : $keynavButtonAdd );
            $navOptions .= ',navButtonDel:' . ( isset($gridOptions['navButtonDel']) ? $gridOptions['navButtonDel'] : $keynavButtonDel );
            $navOptions .= ',navButtonPrint:' . ( isset($gridOptions['navButtonPrint']) ? $gridOptions['navButtonPrint'] : 'false' );

            $htmlGrid = ' <table class="ita-jqGrid ita-jqGrid {caption:\'' . $gridOptions['Caption'] . '\',sortorder:\''
                    . $gridOptions['sortorder'] . '\',sortname:\'' . $gridOptions['sortname'] . '\',rowNum:'
                    . $gridOptions['rowNum'] . ',rowList:' . $gridOptions['rowList'] . ',width:'
                    . $gridOptions['width'] . ',height:' . $gridOptions['height']
                    . $filterBox
                    . $multiselect
                    . $pgbuttons
                    . $pginput
                    . ',shrinkToFit:false'
                    . $navOptions
                    . $metadataJs
                    . $readerId . '}" id="' . $nameGrid . '">';
        }

        $htmlGrid .= '<thead>';

        foreach ($gridOptions['colNames'] as $name) {
            $htmlGrid .= '      <th id="' . $nameForm . "_th" . $name . '">' . $name . '</th>';
        }

        $htmlGrid .= '</thead>';
        $htmlGrid .= '<tbody>';
        $htmlGrid .= '<tr id="baseRow" style="background-color:">';

        $columnReaderId = false;

        foreach ($gridOptions['colModel'] as $colModel) {
            $class = "ui-state-default ui-corner-all";
            $metaData = '';

            if (!$columnReaderId && $colModel['name'] && $colModel['key']) {
                $columnReaderId = $colModel['name'];
            }

            if ($colModel['name']) {
                $name = $colModel['name'];
                unset($colModel['name']);
            }
            if ($colModel['width']) {
                $width = $colModel['width'];
                unset($colModel['width']);
            }
            if ($colModel['formatter']) {
                $metaData .= 'formatter:' . $colModel['formatter'] . ",";
                unset($colModel['formatter']);
            }
            if ($colModel['hidden']) {
                $metaData .= 'hidden:' . $colModel['hidden'] . ",";
                unset($colModel['hidden']);
            }
            if ($colModel['key']) {
                $metaData .= 'key:' . $colModel['key'] . ",";
                unset($colModel['key']);
            }

            foreach ($colModel as $key => $value) {
                $metaData .= "$key:$value,";
            }

            if ($metaData != '') {
                $metaData = ' {' . substr($metaData, 0, strlen($metaData) - 1) . '}';
            }
            $class .= $metaData;
            $htmlGrid .= '<td width="' . $width . '" class="' . $class . '" id="' . $name . '"></td>';
        }

        if ($columnReaderId) {
            $gridOptions['readerId'] = $columnReaderId;
        }

        $htmlGrid .= '</tr>';
        $htmlGrid .= '</tbody>';
        $htmlGrid .= '</table>';

        if ($gridOptions['multiselect']) {
            $htmlGrid .= '<br>';
            $htmlGrid .= "<button id=\"utiRicDiag_Conferma\" class=\"ita-button ita-element-animate ita-button-commit {shortCut:'f2'} ui-corner-all ui-state-default\" name=\"utiRicDiag_Conferma\" type=\"button\" style=\"display: inline;\">
                     <div class=\"ita-button-element ita-button-icon-left ita-icon ui-icon ui-icon-check\" style=\"height: 18px;\"></div>
                        <div class=\"ita-button-element ita-button-text\" style=\"height:40px;width:125px;\">
                           <div class=\"ita-button-text-content\">F2-Conferma Selezione</div>
                        </div>
                 </button>";
        }

        return $htmlGrid;
    }

    static public function getHtmlJqGridComponent($nameForm, $nameGrid, $colModel, $gridMetadata) {
        $thead = $tr = '';

        //Scrittura base table con metadati
        $htmlGrid = '<table class="ita-jqGrid';
        $metadata = array();
        if (is_array($gridMetadata)) {
            foreach ($gridMetadata as $k => $v) {
                $row = trim($k) . ':';
                if (is_integer($v) || is_bool($v) || strtolower($v) == 'true' || strtolower($v) == 'false' || preg_match('/^\s*[{\[].*?[}\]]\s*$/', $v) === 1) {
                    if ($v === true) {
                        $row .= 'true';
                    } elseif ($v === false) {
                        $row .= 'false';
                    } else {
                        $row .= $v;
                    }
                } else {
                    $v = str_replace('"', '&quot;', $v);
                    $row .= '\'' . $v . '\'';
                }
                $metadata[] = $row;
            }
        }
        if (!empty($metadata)) {
            $htmlGrid .= ' {' . implode(',', $metadata) . '}';
        }
        $htmlGrid .= '" style="font-size:100%" id="' . $nameForm . '_' . $nameGrid . '">';

        //Calcolo righe thead e prima riga del modello
        foreach ($colModel as $col) {
            $thead .= '<th id="' . $nameForm . "_th" . $col['name'] . '">' . (isSet($col['title']) ? $col['title'] : $col['name']) . '</th>';

            $tr .= '<td id="' . $col['name'] . '"';
            foreach ($col as $k => $v) {
                $k = strtolower(trim($k));
                if ($k != 'name' && $k != 'title') {
                    $tr .= ' ' . $k . '="' . str_replace('"', '&quot;', $v) . '"';
                }
            }
            $tr .= '></td>';
        }

        //render thead
        $htmlGrid .= '<thead>' . $thead . '</thead>';

        //renader tbody
        $htmlGrid .= '<tbody><tr id="baseRow" style="background-color:">' . $tr . '</tr></tbody>';

        $htmlGrid .= '</table>';

        return $htmlGrid;
    }

}
