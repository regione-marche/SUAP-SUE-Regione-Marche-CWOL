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

    public function getAllData() {
        $retData = array();
        foreach ($this->data as $key => $value) {
            if (is_object($value)) {
                if (get_class($value) == 'itaDictionary') {
                    $retData[$key] = $value->getData();
                } else {
                    $retData[$key] = '';
                }
            } else {
                $retData[$key] = $value;
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

    public function addField($key, $desc, $seq, $type, $value = "", $flUnique = false) {
        if ($flUnique == true) {
            if (in_array($key, $this->dictionary)) {
                return;
            }
        }
        $this->dictionary[$key] = array('desc' => $desc,
            'seq' => $seq,
            'type' => $type);

        //if ($value) {
        $this->addFieldData($key, $value);
        //}
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

    public function exportAdjacencyModel($markup = "smarty", $RootDescription = "Legenda Campi") {
        $inc = 0;
        $lev = 0;
        $albero = array();
        $albero[$inc]['varidx'] = $inc;
        $albero[$inc]['chiave'] = "";
        $albero[$inc]['markupkey'] = "";
        $albero[$inc]['descrizione'] = '<span style="color: darkred;font-size: 1.2em;font-weight: bold;">' . $RootDescription . "<span>";
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
                $albero = $this->explodeDictionary($dictionary->getData($dictKey), $albero, $lev + 1, $inc);
            }
            if ($save_count == count($albero)) {
                $albero[$inc]['descrizione'] = $dictField['desc'];
                $albero[$inc]['isLeaf'] = 'true';
            }
        }
        return $albero;
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
                } else {
                    $retData[$wsuffix . $key] = '';
                }
            } else {
                $retData[$wsuffix . $key] = $value;
            }
        }
        return $retData;
    }

}

?>
