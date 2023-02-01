<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDictionary
 *
 * @author michele
 */
class itaDictionary {

    private $message;
    private $returnCode;
    private $dictionary;
    private $data;

    public function __construct($dictionary = "") {
        if ($dictionary) {
            $this->setDictionary($dictionary);
        }
    }

    public function __destruct() {
        
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getReturnCode() {
        return $this->returnCode;
    }

    public function setReturnCode($returnCode) {
        $this->returnCode = $returnCode;
    }

    public function getDictionary() {
        return $this->dictionary;
    }

    public function setDictionary($dictionary) {
        $this->dictionary = $dictionary;
    }

    /**
     * Carica dati su dizionario già presente
     * @param type $xmlString
     * @return boolean
     */
    public function importDataFromXml($xmlString) {
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($xmlString);
        if (!$retXml) {
            return false;
        }
        $arrXml = $xmlObj->getArray();
        //$this->importXmlArray($arrXml);
        //App::log('arrXml');
        //App::log($arrXml);
        file_put_contents("/users/pc/dos2ux/log.txt", print_r($arrXml, true));
//        App::log('current arrXml');
//        App::log(current($arrXml));
//        App::log('reset1');
//        App::log(reset($arrXml));
//        App::log('reset2');
//        App::log(reset(reset($arrXml)));
//        App::log('faccio import');
        //$this->importXmlArray(reset(reset($xmlObj->getArray())));
        $this->importXmlArray(reset(reset($arrXml)));
//        App::log('fatto import');
        return true;
    }

    public function importXmlArray($arrXml, $parent = null) {
        if ($parent == null) {
            $parent = $this;
        }
//        App::log('parent');
//        App::log($parent);
//        App::log('dizionario');
//        App::log($this->dictionary);
//        App::log('data');
//        App::log($this->data);
        foreach ($arrXml as $key => $value) {
            if ($this->dictionary[$key]['type'] == 'itaDictionary') {
//                App::log('entra dizionario');
                $this->importXmlArray($value[0], $this->data[$key]);
            } else {
                if (is_array($value) && isset($value[0])) {
                    if (!$value[0]['@textNode']) {
//                        App::log('entra1');
                        $parent->addFieldData($key, '');
                    } else {
//                        App::log('entra2');
                        $parent->addFieldData($key, $value[0]['@textNode']);
                    }
                }
            }
        }
    }

    public static function importFromXml($xmlString) {
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($xmlString);
        if (!$retXml) {
            return false;
        }
//        $arrXml = $xmlObj->toArray($xmlObj->asObject());
//        App::log('$arrXml');
//        App::log($arrXml);
//        App::log($xmlObj->getArray());
//        $arrXml = self::filterTextNode($arrXml);
        $arrXml = self::filterTextNode($xmlObj->getArray());
        return $arrXml;
    }

    public static function importFormArray($arrXml) {
        foreach ($arrXml as $key => $value) {
            if ($dictionary === null) {
                $dictionary = new itaDictionary();
            }
            if (is_array($value)) {
                $dictionary->addFieldData($key, self::importFormArray($value));
            } else {
                $dictionary->addFieldData($key, $value);
            }
        }
        return $dictionary;
    }

    public static function filterTextNode($arrXml) {
        foreach ($arrXml as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                unset($value[0]['@attributes']);
                if ($value[0]['@textNode'] === null || $value[0] === '' || isset($value[0]['@textNode']) == false) {
                    unset($value[0]['@textNode']);
                    if (!$value[0])
                        $value[0] = '';
                } else {
                    $value[0] = $value[0]['@textNode'];
                }
                $arrXml[$key] = self::filterTextNode($value[0]);
//                unset($value[0]['@attributes']);
//                if (!$value[0]['@textNode']) {
//                    unset($value[0]['@textNode']);
//                    if (!$value[0])
//                        $value[0] = '';
//                } else {
//                    $value[0] = $value[0]['@textNode'];
//                }
//                $arrXml[$key] = self::filterTextNode($value[0]);
            }
        }
        return $arrXml;
    }

    /**
     * Restituisce un array associativo che come chiave usa il codice campo dizionario e come valore il valore del campo dizionario.
     * Nel caso che il valore dizionario sia un oggetto itaDictionary il valore fornito sarà un ulteriore array associativo.
     * @return array
     */
    public function getAllData() {
        $retData = array();
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                if (get_class($value) == 'itaDictionary') {
                    $retData[$key] = $value->getAllData(); //mm
                } else {
                    $retData[$key] = '';
                }
            } else {
                $retData[$key] = $value;
            }
        }
        return $retData;
    }

    public function getAllDataFormatted() {
        $retData = array();
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                if (get_class($value) == 'itaDictionary') {
                    $retData[$key] = $value->getAllDataFormatted(); //mm
                } else {
                    $retData[$key] = '';
                }
            } else {
                $retData[$key] = $this->dataFormatter($key, $value);
            }
        }
        return $retData;
    }

    public function dataFormatter($key, $value) {
        $xvalue = $value;
        $wKey = $key;
        if (!$this->dictionary[$wKey]) {
            //
            // se l'ultima parte del campo dopo _ è numerica e non esiste la formattazione cerca con il base name del campo......
            //
            // Es: campo=IMM_1 cerca la formattazione con il campo IMM  
            // Es: campo=IMM_a cerca la formattazione con il campo IMM_a
            //
            $wKey = is_numeric(end(explode("_", $key))) ? implode("_", explode("_", $key, -1)) : $key;
        }
        if (isset($this->dictionary[$wKey]['format'])) {
            if ($this->dictionary[$wKey]['format']) {
                $arrf = json_decode("{" . $this->dictionary[$wKey]['format'] . "}", true);
                switch ($arrf['type']) {
                    case 'date':
                        if (!$arrf['format']) {
                            $arrf['format'] = "d/m/Y";
                        }
                        $xvalue = ($value) ? date($arrf['format'], strtotime($value)) : "";
                        break;
                    case 'currency':
                        if (!$arrf['format']) {
                            $arrf['format'] = "%(#10n";
                        }
                        $xvalue = ($value) ? money_format($arrf['format'], (floatval($value))) : "";
                        break;
                    case 'number':
                        $xvalue = ($value) ? number_format($value, $arrf['decimals'], $arrf['dec_point'], $arrf['thousand_sep']) : "";
                        break;
                    default:
                        $xvalue = $value;
                        break;
                }
            }
        }
        return $xvalue;
    }

    public function getAllDataXML() {
        $retXml = '';
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                if (get_class($value) == 'itaDictionary') {
                    $retXml .= "<" . $key . ">";
                    $retXml .= $value->getAllDataXML();
                    $retXml .= "</" . $key . ">\n";
                } else {
                    $retXml .= "<" . $key . "></" . $key . ">\n";
                }
            } else {
                $retXml .= "<" . $key . ">" . htmlspecialchars(utf8_encode($value), ENT_COMPAT, 'UTF-8') . "</" . $key . ">\n";
            }
        }
        return $retXml;
    }

    public function getDictionaryXML() {
        $retXml = '';
        foreach ($this->dictionary as $key => $value) {
            if (is_object($this->data[$key])) {
                if (get_class($this->data[$key]) == 'itaDictionary') {
                    $retXml .= "<" . $key . ">";
                    $retXml .= $this->data[$key]->getDictionaryXML();
                    $retXml .= "</" . $key . ">\n";
                } else {
                    $retXml .= "<" . $key . "></" . $key . ">\n";
                }
            } else {
                $attributes = "";
                foreach ($value as $attrKey => $attrValue) {
                    $attributes .= "$attrKey=\"" . htmlspecialchars(utf8_encode($attrValue), ENT_COMPAT, 'UTF-8') . "\" ";
                }
                $retXml .= "<$key $attributes>";
                $retXml .= ""; //"...";//htmlspecialchars(utf8_encode($value), ENT_COMPAT, 'UTF-8') . "</" . $key . ">\n";
                $retXml .= "</$key>\n";
            }
        }
        return $retXml;
    }

    public function getAllDataPlain($suffix = '', $separator = "_") {
        $wsuffix = "";
        if ($suffix) {
            $wsuffix = $suffix . $separator;
        }
        $retData = array();
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                if (get_class($value) == 'itaDictionary') {
                    $retData = array_merge($retData, $value->getAllDataPlain($wsuffix . $key, $separator));
                    //$retData[$suffix."_".$key] = $value->getAllDataPlain($key);//mm
                } else {
                    $retData[$wsuffix . $key] = '';
                }
            } else {
                $retData[$wsuffix . $key] = $value;
            }
        }
        return $retData;
    }

    public function getData($key = '') {
        if ($key) {
            $data_ret = $this;
            $ar_key = explode(".", $key);
            foreach ($ar_key as $sub_key) {
                $data_ret = $data_ret->data[$sub_key];
            }
            return $data_ret;
        } else {
            return $this->data;
        }
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function ReplacementSpaceKey($key) {
        return str_replace(" ", "_", $key);
    }

    public function addField($key, $desc, $seq, $type, $value = "", $flUnique = false, $format = '') {
        $key = $this->ReplacementSpaceKey($key);
        if ($flUnique == true) {
            if (in_array($key, $this->dictionary)) {
                return;
            }
        }
        $this->dictionary[$key] = array('desc' => $desc,
            'seq' => $seq,
            'type' => $type,
            'format' => $format);

        if ($value !== false || $value !== null || $value !== '') {
            $this->addFieldData($key, $value);
        }
    }

    public function delField($key) {
        unset($this->dictionary[$key]);
        $this->delField($key);
    }

    public function addFieldData($key, $value) {
        $this->data[$key] = $value;
    }

    public function delFieldData($key) {
        unset($this->data[$key]);
    }

    public function exportAdjacencyModel($markup = "smarty", $RootDescription = "Legenda Campi", $withValue = false) {
        $inc = 0;
        $lev = 0;
        $albero = array();
        $albero[$inc]['varidx'] = $inc;
        $albero[$inc]['chiave'] = "";
        $albero[$inc]['markupkey'] = "";
        $albero[$inc]['descrizione'] = '<span style="color: darkred;font-size: 1.2em;font-weight: bold;">' . $RootDescription . "<span>";
        $albero[$inc]['valore'] = "";
        $albero[$inc]['level'] = $lev;
        $albero[$inc]['parent'] = NULL;
        $albero[$inc]['isLeaf'] = 'false';
        $albero[$inc]['expanded'] = 'true';
        $albero[$inc]['loaded'] = 'true';
        $lev += 1;
        foreach ($this->dictionary as $dictKey => $dictField) {
            $inc = count($albero);
            $albero[$inc]['varidx'] = $inc;
            $albero[$inc]['chiave'] = $dictKey;
            $albero[$inc]['markupkey'] = $this->createKey($dictKey, $markup);
            $albero[$inc]['descrizione'] = '<span style="color: darkred;font-size: 1.2em;font-weight: bold;">' . $dictField['desc'] . "</span>";
            $albero[$inc]['type'] = $dictField['type'];
            $albero[$inc]['level'] = $lev;
            $albero[$inc]['parent'] = 0;
            $albero[$inc]['isLeaf'] = 'false';
            $albero[$inc]['expanded'] = 'true';
            $albero[$inc]['loaded'] = 'true';


            $save_count = count($albero);
            if ($dictField['type'] == 'itaDictionary') {
                $albero[$inc]['markupkey'] = "";
                $albero = $this->explodeDictionary($this->getData($dictKey), $albero, $lev + 1, $inc, $markup);
                $albero[$inc]['valore'] = "";
            } else {
                $albero[$inc]['valore'] = $this->getData($dictKey);
            }
            if ($save_count == count($albero)) {
                $albero[$inc]['descrizione'] = $dictField['desc'];
                $albero[$inc]['isLeaf'] = 'true';
            }
        }
        return $albero;
    }

    private function createKey($key, $markup = "") {
        switch ($markup) {
            case "smarty":
                return '@{$' . $key . '}@';
                break;
            default:
                return $key;
                break;
        }
    }

    private function explodeDictionary($dictionary, $albero, $lev, $parent, $markup) {
        if ($lev == 10) {
            return $albero;
        }
        foreach ($dictionary->dictionary as $dictKey => $dictField) {
            $inc = count($albero);
            $albero[$inc]['varidx'] = $inc;
            $albero[$inc]['chiave'] = $albero[$parent]['chiave'] . "." . $dictKey;
            $albero[$inc]['markupkey'] = $this->createKey($albero[$parent]['chiave'] . "." . $dictKey, $markup);
            $albero[$inc]['descrizione'] = '<span style="color: darkred;font-size: 1.2em;font-weight: bold;">' . $dictField['desc'] . "</span>";
            $albero[$inc]['type'] = $dictField['type'];
            $albero[$inc]['level'] = $lev;
            $albero[$inc]['parent'] = $parent;
            $albero[$inc]['isLeaf'] = 'false';
            $albero[$inc]['expanded'] = 'true';
            $albero[$inc]['loaded'] = 'true';
            $save_count = count($albero);
            if ($dictField['type'] == 'itaDictionary') {
                $albero[$inc]['markupkey'] = "";
                $albero = $this->explodeDictionary($dictionary->getData($dictKey), $albero, $lev + 1, $inc, $markup);
                $albero[$inc]['valore'] = "";
            } else {
                $albero[$inc]['valore'] = $this->getData($albero[$parent]['chiave'] . "." . $dictKey);
            }
            if ($save_count == count($albero)) {
                $albero[$inc]['descrizione'] = $dictField['desc'];
                $albero[$inc]['isLeaf'] = 'true';
            }
        }
        return $albero;
    }

}

?>
