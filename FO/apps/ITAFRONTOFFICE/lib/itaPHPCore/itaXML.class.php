<?php

/*
 * QXML
 * @author: Thomas Schäfer
 * 
 * @mail: dipl.paed.thomas.schaefer@web.de
 * 
 * Modificato il 30/10/2012 per valore di ritorno booleano su funzione setXmlFromFile (michele.moscioni@italsoft.eu)
 * Modificato il 12/04/2013 fork della versione precedente su itaXML per controllo validità xml e elaborazione grandi XML
 * Modificato il 13/05/2014 disabilita addslashes su attributi (michele.moscioni@italsoft.eu)
 * 
 */

class itaXML {
    /* attribute constant, used for array conversion, preserves xml attributes */

    const attribute = "@attributes";

    /* place for textnodes, used for array converion, CDATA enabling
     */
    const textNode = "@textNode";

    protected $xml;
    protected $object;
    protected $nodes = array();
    protected $array = null;
    protected $cdata = true;
    protected $addslashesattr = true;

    /**
     * set property cdata
     * useful if you want to make rss feeds or something similar
     */
    public function noCDATA() {
        $this->cdata = false;
    }

    public function noAddslashesattr() {
        $this->addslashesattr = false;
    }

    /**
     * facade for getting an object from xml
     *
     * @return mixed
     */
    public function asObject() {
        if (strlen($this->asXML())) {
            return simplexml_load_string($this->asXml(), null, LIBXML_NOCDATA);
        } else {
            return false;
        }
    }

    /**
     * setter method for xml property
     *
     * @param mixed $xml
     */
    public function setXml($xml) {
        $this->xml = $xml;
    }

    /**
     * getter method for xml property
     *
     * @return mixed
     */
    public function getXml() {
        return $this->xml;
    }

    public function setXmlFromFile($file) {
        return $this->setXmlFromString(file_get_contents($file));
    }

    /**
     * loads an xml from file and converts it to an object
     *
     * @param string $file
     */
    public function setXmlFromString($xmlStr) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xmlObject = simplexml_load_string(str_replace(chr(2), '', $xmlStr));
        //$xmlObject = simplexml_load_file($file,'SimpleXMLElement');        
        if ($xmlObject instanceof SimpleXMLElement) {
            $array = $this->toArray($xmlObject);
            $root = array();
            $root[$xmlObject->getName()][0] = (array) $xmlObject->attributes();
            $root[$xmlObject->getName()][0] = array_merge($root[$xmlObject->getName()][0], $array);
            //$root[key(get_object_vars($xmlObject))] = (array) $xmlObject->attributes();
            //$root[key(get_object_vars($xmlObject))] = array_merge($root[key(get_object_vars($xmlObject))], $array);

            $xmlArray = $root;
            $this->array = $xmlArray;
            $this->toNodeList($xmlArray);
            $this->toXML($this->array);
            $this->object = $this->asObject();
            return true;
        } else {
//            App::log("Errori XML:");
//            App::log(libxml_get_errors());
            return false;
        }
    }

    /**
     * serialize objects resp. arrays
     *
     * @param mixed $toSerialize
     * @return string
     */
    public function serialize($toSerialize) {
        if ($toSerialize instanceof SimpleXMLElement) {
            $stdClass = new stdClass();
            $stdClass->type = get_class($toSerialize);
            $stdClass->data = $toSerialize->asXml();
            return serialize($stdClass);
        } else {
            return serialize($stdClass);
        }
    }

    /**
     * unserialize to object
     *
     * @param string $toUnserialize
     * @return object
     */
    function unserialize($toUnserialize) {
        $toUnserialize = unserialize($toUnserialize);
        if ($toSerialize instanceof stdclass) {
            if ($toUnserialize->type == "SimpleXMLElement") {
                $toUnserialize = simplexml_load_string($toUnserialize->data);
            }
            return $toUnserialize;
        } else {
            return $toUnserialize;
        }
    }

    /**
     * getter and alias for xml property
     *
     * @return string
     */
    public function asXML() {
        return $this->xml;
    }

    /**
     * facade to preserve an object's root element and its attributes
     *
     * @return array
     */
    public function asArray() {
        $array = $this->toArray($this->xml);
//		$root[$this->xml->getName()] = (array) $this->xml->attributes();
//		$root[$this->xml->getName()] = array_merge($root[$this->xml->getName()], $array);
        $root[key(get_object_vars($this->xml))] = (array) $this->xml->attributes();
        $root[key(get_object_vars($this->xml))] = array_merge($root[key(get_object_vars($this->xml))], $array);
        return $root;
    }

    /**
     * gets a node list
     *
     * @return array
     */
    public function getNodesFromArray() {
        $array = $this->array;
//		$root[$this->object->getName()] = (array) $this->object->attributes();
//		$root[$this->object->getName()] = array_merge($root[$this->object->getName()], $array);
        $root[key(get_object_vars($this->object))] = (array) $this->object->attributes();
        $root[key(get_object_vars($this->object))] = array_merge($root[key(get_object_vars($this->object))], $array);
        return $root;
    }

    /**
     * node merging method
     *
     * @param array $array
     * @param string $node
     * @param integer $level
     * @param string $delim
     * @return array
     */
    protected function buildNodes($array, $node = "", $level = 0, $delim = ".") {
        $text = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $nodeset = ($node == "") ? $key : $node . $delim . $key;
                $text = array_merge($text, $this->buildNodes($value, $nodeset, $level++, $delim));
            } else {
                $nodeset = $node . $delim . $key;
                $text[] = $nodeset;
            }
        }
        return $text;
    }

    /*
     * facade for node builder
     */

    public function toNodeList($array) {
        $this->nodes = $this->buildNodes($array);
    }

    /**
     * method for return a node list
     *
     * @return array
     */
    public function getNodeList() {
        return $this->nodes;
    }

    /**
     * facade for getting a nodelist form XML
     *
     * @return array
     */
    public function getNodesFromXML() {
        $this->toNodeList($this->asArray());
        return $this->getNodeList();
    }

    /**
     * facade for array helper class
     *
     * @param string $path
     * @return array
     */
    public function getNode($path) {
        return QSet::get($this->asArray(), $path);
    }

    /**
     * facade for array helper class
     *
     * @param string $path
     * @param array $data
     * @param string $root
     */
    public function setNode($path, $data, $root = "chart") {
        if (empty($this->array)) {
            $this->array = $this->asArray();
        }
        $this->array = QSet::set($this->array, $path, $data);
    }

    /*
     * return helper 
     */

    public function getArray() {
        return $this->array;
    }

    /**
     * getter for object property
     *
     * @return SimpleXMLElement object
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * facade for array helper method  
     *
     * @param string $name
     * @param SimpleXMLElement $object
     * @return bool
     */
    public function hasAttribute($name, $object) {
        $attrs = $object->attributes();
        if (isset($attrs[$name])) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 
     * @param object SimpleXMLElement
     * @return array
     */

    public function toArray(SimpleXMLElement $xml) {

        $results = array();
        foreach ($xml->children() as $key => $object) {
            $recObj = $this->toArray($object);
            foreach ($object->attributes() as $ak => $av) {
                $recObj[self::attribute][$ak] = (string) $av;
            }
            if (!$recObj[self::attribute]) {
                $recObj[self::attribute] = "";
            }
            if (!$recObj[self::textNode]) {
                $recObj[self::textNode] = trim((string) $object);
            }

            $results[$key][] = $recObj;

//            if (!in_array($key, array_keys($results))) {
//                $results[$key] = $recObj;
//            } elseif (@in_array(0, @array_keys($results[$key]))) {
//                if (count($results[$key]) == 2) {
//                    if ($results[$key][self::attribute] || $results[$key][self::textNode]) {
//                        $tmp = Array();
//                        $tmp[self::attribute] = $results[$key][self::attribute];
//                        $tmp[self::textNode] = $results[$key][self::textNode];
//                        unset($results[$key][self::attribute]);
//                        unset($results[$key][self::textNode]);
//                        $results[$key][0] = $tmp;
//                        unset($tmp);
//                    }
//                }
//                if (count(array_diff_key($results[$key], $recObj)) == 0 && count($results[$key]) > 0) {
//                    $tmp = $results[$key];
//                    unset($results[$key]);
//                    $results[$key][] = $tmp;
//                }
//                $results[$key][] = $recObj;
//            } else {
//                $results[$key] = array($results[$key]);
//                $results[$key][] = $recObj;
//            }
        }
        return $results;
    }

    /*
     * @desc facade method for recursion
     * @param mixed $mixed array or SimpleXMLElement
     * @param string $elmName Name of root element
     * @return void
     */

    public function toXML($mixed, $elmName = 'chart') {
        if ($mixed instanceof SimpleXMLElement) {
            $array = $this->toArray($this->xml);
            $root = array();
//			$root[$this->xml->getName()] = (array) $this->xml->attributes();
//			$root[$this->xml->getName()] = array_merge($root[$this->xml->getName()], $array);
            $root[key(get_object_vars($this->xml))] = (array) $this->xml->attributes();
            $root[key(get_object_vars($this->xml))] = array_merge($root[key(get_object_vars($this->xml))], $array);

            $mixed = $root;
        }
        $xml = $this->_toXML($mixed, $elmName);
        $this->setXml($xml);
    }

    /*
     * recursing method for mining xml to array
     * @param array $xmlArray
     * @param string $elmName Name of root node
     * @param string $elmCloseTag optional:<br/>normally used by function _toXML() itself
     * @param integer $level counter
     * @return string
     */

    protected function _toXML($xmlArray, $elmName = 'chart', $elmCloseTag = "", $level = 0) {
        $xmlString = "";

        if (is_array($xmlArray)) {
            $strXmlAttributes = "";
            $key_xml = "";
            $keysXmlArray = array_keys($xmlArray);
            $curLevel = $level + 1;
            if (in_array(self::attribute, $keysXmlArray)) {
                if (isset($xmlArray[self::attribute])) {
                    if (is_array($xmlArray[self::attribute])) {
                        foreach ($xmlArray[self::attribute] as $xmlArrayKey => $xmlArrayValue) {
                            if ($this->addslashesattr) {
                                $strXmlAttributes .= sprintf(' %s="%s"', $xmlArrayKey, addslashes($xmlArrayValue));
                            } else {
                                $strXmlAttributes .= sprintf(' %s="%s"', $xmlArrayKey, $xmlArrayValue);
                            }
                        }
                    }
                }
                unset($xmlArray[self::attribute]);
            }
            if (in_array(self::textNode, $keysXmlArray)) {
                if (isset($xmlArray[self::textNode])) {
                    if ($xmlArray[self::textNode]) {
                        $key_xml = $xmlArray[self::textNode];
                    }
                    if (strlen($key_xml)) {
                        if ($this->cdata == true) {
                            $key_xml = sprintf("<![CDATA[%s]]>", $key_xml);
                        }
                    } else {
                        $key_xml = "";
                    }
                }
                unset($xmlArray[self::textNode]);
            }
            $keysXmlArray = array_keys($xmlArray);
            if ($elmCloseTag) {
                $indent = str_repeat(" ", $level * 5);
                $xmlString.="\n" . $indent . "<" . $elmCloseTag . $strXmlAttributes . ">" . $key_xml;
            }

            if (is_array($xmlArray) && count($xmlArray) > 0 && count($keysXmlArray) > 0) {
                reset($xmlArray);
                foreach ($keysXmlArray as $key) {
                    $altKey = $altKeyXml = $xmlArray[$key];
                    $check = false;
                    if (is_array($altKeyXml)) {
                        foreach (array_keys($altKeyXml) as $j => $p) {
                            if (is_numeric($j)) {
                                $check = true;
                                $xmlString.= $this->_toXML($altKeyXml[$j], "", $key, $curLevel);
                                unset($altKeyXml[$j]);
                            }
                        }
                    }
                    if ($check) {
                        $altKey = $altKeyXml;
                    }
                    if ($altKey) {
                        $xmlString .= $this->_toXML($altKey, "", $key, $curLevel);
                    }
                }
            }
            if ($elmCloseTag) {
                $xmlString.=(count($xmlArray) > 0 ? "\n" . $indent : "") . "</" . $elmCloseTag . ">";
            }
        }

        if ($elmName) {
            $xmlString = "<?xml version='1.0' encoding='UTF-8'?>\n$xmlString\n";
        }
        return $xmlString;
    }

    /**
     * convert xml string to JSON object
     *
     * @param string $xml
     * @return string JSON
     */
    public function toJSONFromXML($xml) {
        return xml2json::transformXmlStringToJson($xml);
    }

    /**
     * convert SimpleXmlElement to JSON Object
     *
     * @param SimpleXMLElement $xml
     * @return string JSON
     */
    public function toJSONFromObject(SimpleXMLElement $xml) {
        return xml2json::convertSimpleXmlElementObjectIntoArray($xml);
    }

}

?>