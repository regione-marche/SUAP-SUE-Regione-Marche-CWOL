<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of xmlManager
 *
 * @author michele
 */
class xmlManager {

    private $xmlText;
    private $domNode;
    private $resourceDescriptor;
    private $operationResult;

    public function __construct($xmlText="") {
        if ($xmlText) {
            $this->xmlText = $xmlText;
        }
    }

    public function setXmlText($xmlText) {
        $this->xmlText = $xmlText;
    }

    public function getXmlText() {
        return $this->xmlText;
    }

    public function setDomNode($domNode) {
        $this->domNode = $domNode;
    }

    public function getDomNode() {
        return $this->domNode;
    }

    public function setResourceDescriptor($resourceDescriport) {
        $this->resourceDescriptor = $resourceDescriport;
    }

    public function getResourceDescriptor() {
        return $this->resourceDescriptor;
    }

    public function getOperationResultFormXml() {
        if (!$this->xmlText) {
            return false;
        }
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->xmlText);
        $this->setDomNode($domDocument);
        return $this->getOperationResultFormNode();
    }

    public function getOperationResultFormNode() {
        if (!$this->domNode) {
            return false;
        }
        $domDocument = $this->domNode;
        $domDocument->loadXML($this->xmlText);
        $or = new operationResult();

        foreach ($domDocument->childNodes AS $ChildNode) {
            if ($ChildNode->nodeName != '#text') {
                if ($ChildNode->nodeName == "operationResult") {
                    $or->setVersion($ChildNode->getAttributeNode("version")->value);
                    foreach ($ChildNode->childNodes AS $ChildChildNode) {
                        switch ($ChildChildNode->nodeName) {
                            case 'returnCode':
                                $or->setReturnCode($ChildChildNode->nodeValue);
                                break;
                            case 'returnMessage':
                                $or->setMessage($ChildChildNode->nodeValue);
                                break;
                            case 'resourceDescriptor':
                                $or->addResourceDescriptor($this->readResourceDescriptorFromNode($ChildChildNode));
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }

        return $or;
    }

    function getResourceDescriptorsFromXml() {
        if (!$this->xmlText) {
            return false;
        }
        $domDocument = new DOMDocument();
        $domDocument->loadXML($this->xmlText);
        $resourceDescriptors = array();

        foreach ($domDocument->childNodes AS $ChildNode) {
            if ($ChildNode->nodeName != '#text') {
                if ($ChildNode->nodeName == "operationResult") {
                    foreach ($ChildNode->childNodes AS $ChildChildNode) {
                        if ($ChildChildNode->nodeName == 'resourceDescriptor') {
                            $resourceDescriptors[] = $this->readResourceDescriptorFromNode($ChildChildNode);
                        }
                    }
                }
            }
        }
        return $resourceDescriptors;
    }

    private function readResourceDescriptorFromNode($node) {
        $rd = new resourceDescriptor();
        $rd->setName($node->getAttributeNode("name")->value);
        $rd->setUriString($node->getAttributeNode("uriString")->value);
        $rd->setWsType($node->getAttributeNode("wsType")->value);
        $rd->setIsNew($node->getAttributeNode("isNew")->value);
        $children = array();
        $parameters = array();

        // Read subelements...
        foreach ($node->childNodes AS $ChildNode) {
            $currNodename = $ChildNode->nodeName;
            switch ($currNodename) {
                case 'label':
                    $rd->setLabel($ChildNode->nodeValue);
                    break;
                case 'description':
                    $rd->setDescription($ChildNode->nodeValue);
                    break;
                case 'creationDate':
                    $rd->setCreationDate($ChildNode->nodeValue);
                    break;
                case 'resourceProperty':
                    $resourceProperty = $this->addReadResourcePropertyFromNode($ChildNode);
                    $rd->setResourceProperty($resourceProperty['name'], $resourceProperty['value']);
                    break;
                case 'parameter':
                    $parameters[$ChildNode->getAttributeNode("name")] = $ChildNode->nodeValue;
                    break;
                case 'resourceDescriptor':
                    $children[] = $this->readResourceDescriptorFromNode($ChildNode);
                    break;
                default:
                    break;
            }
        }
        $rd->setChildren($children);
        $rd->setParameters($parameters);
        return $rd;
    }

    function readResourceDescriptor($node) {
        $resourceDescriptor = array();

        $resourceDescriptor['name'] = $node->getAttributeNode("name")->value;
        $resourceDescriptor['uri'] = $node->getAttributeNode("uriString")->value;
        $resourceDescriptor['type'] = $node->getAttributeNode("wsType")->value;

        $resourceProperties = array();
        $subResources = array();
        $parameters = array();

        // Read subelements...
        foreach ($node->childNodes AS $ChildNode) {
            if ($ChildNode->nodeName == 'label') {
                $resourceDescriptor['label'] = $ChildNode->nodeValue;
            } else if ($ChildNode->nodeName == 'description') {
                $resourceDescriptor['description'] = $ChildNode->nodeValue;
            } else if ($ChildNode->nodeName == 'creationDate') {
                $resourceDescriptor['creationDate'] = $ChildNode->nodeValue / 1000;
            } else if ($ChildNode->nodeName == 'resourceProperty') {
                //$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
                // read properties...
                $resourceProperty = $this->addReadResourceProperty($ChildNode);
                $resourceProperties[$resourceProperty["name"]] = $resourceProperty;
            } else if ($ChildNode->nodeName == 'resourceDescriptor') {
                array_push($subResources, $this->readResourceDescriptor($ChildNode));
            } else if ($ChildNode->nodeName == 'parameter') {
                $parameters[$ChildNode->getAttributeNode("name")->value] = $ChildNode->nodeValue;
            }
        }

        $resourceDescriptor['properties'] = $resourceProperties;
        $resourceDescriptor['resources'] = $subResources;
        $resourceDescriptor['parameters'] = $parameters;


        return $resourceDescriptor;
    }

    private function addReadResourcePropertyFromNode($node) {
        $resourceProperty = array();

        $resourceProperty['name'] = $node->getAttributeNode("name")->value;

        // Read subelements...
        foreach ($node->childNodes AS $ChildNode) {
            if ($ChildNode->nodeName == 'value') {
                $resourceProperty['value'] = $ChildNode->nodeValue;
            } else if ($ChildNode->nodeName == 'resourceProperty') {
                $resourceProperty['value'] = $this->addReadResourcePropertiyFormNode($ChildNode->nodeValue);
            }
        }
        return $resourceProperty;
    }

    private function addReadResourceProperty($node) {
        $resourceProperty = array();

        $resourceProperty['name'] = $node->getAttributeNode("name")->value;

        $resourceProperties = array();

        // Read subelements...
        foreach ($node->childNodes AS $ChildNode) {
            if ($ChildNode->nodeName == 'value') {
                $resourceProperty['value'] = $ChildNode->nodeValue;
            } else if ($ChildNode->nodeName == 'resourceProperty') {
                //$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
                // read properties...
                array_push($resourceProperties, addReadResourceProperty($ChildNode));
            }
        }

        $resourceProperty['properties'] = $resourceProperties;

        return $resourceProperty;
    }

}

?>
