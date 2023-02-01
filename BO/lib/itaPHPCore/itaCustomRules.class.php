<?php

/**
 * @author Carlo Iesari <carlo.iesari@italsoft.eu>
 */
class itaCustomRules {

    public static function applyCustomRule($form_name, $custom_set_id) {
        if (!$custom_set_id) {
            return false;
        }

        $generator = new itaGenerator();
        $xmlPath = $generator->getModelFilename($form_name);

        if (!$xmlPath) {
            return false;
        }

        $customRulesPath = substr($xmlPath, 0, -4) . '.customRules.xml';

        if (!file_exists($customRulesPath)) {
            return false;
        }

        $XMLReader = new XMLReader();

        if (!$XMLReader->open($customRulesPath)) {
            return false;
        }

        while ($XMLReader->read()) {
            // Cerco un customSet
            if ($XMLReader->localName == 'customSet' && $XMLReader->nodeType == XMLReader::ELEMENT) {
                // Verifico l'ID
                if ($XMLReader->getAttribute('id') == $custom_set_id) {
                    // Continuo la lettura per applicare tutte le regole "rule"
                    while ($XMLReader->read()) {
                        // Se arrivo al nodo customSet (di chiusura), esco
                        if ($XMLReader->localName == 'customSet') {
                            break 2;
                        }

                        // Applico le regole
                        if ($XMLReader->localName == 'rule' && $XMLReader->nodeType == XMLReader::ELEMENT) {
                            $XMLReader->moveToElement();
                            $SimpleXML = new SimpleXMLElement($XMLReader->readOuterXml());

                            switch ((string) $SimpleXML['name']) {
                                case 'removeElement':
                                    $elementId = $SimpleXML->elementId;
                                    Out::removeElement($form_name . '_' . $elementId);
                                    break;
                                case 'removeTab':
                                    $paneId = $SimpleXML->paneId;
                                    $tabId = $SimpleXML->tabId;
                                    Out::tabCommad('remove', $form_name . '_' . $tabId, $form_name . '_' . $paneId);
                                    break;
                                
                                case 'hideElement':
                                    $elementId = $SimpleXML->elementId;
                                    Out::hide($form_name . '_' . $elementId);
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

}
