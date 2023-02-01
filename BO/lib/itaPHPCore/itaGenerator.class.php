<?php

/**
 *
 * Generatore codice html da database italsoft
 *
 * PHP Version 5
 *
 * @category   coreclass
 * @package    itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    23.06.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaGenerator {

    public $nome_host;
    public $elementi = array();
    public $duplicati = array();
    public $arrDisposizione = array();
    public $arrayModel = array();
    public $textNodeKey = "@NODE@";
    public $attributeKey = "@ATTRS@";
    public $italsoftDB;

    function __construct() {
        try {
            $this->italsoftDB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            //
        }
    }

    public function setTextNodeKey($textNodeKey) {
        $this->textNodeKey = $textNodeKey;
    }

    public function setAttributekey($attributeKey) {
        $this->attributeKey = $attributeKey;
    }

    /**
     * Compila un file xml partendo dai dati del model nel DB
     *
     * Modificare le parti commentate con la parola SWIRCH quando si passerà
     * alla gestione esclusiva con xml delle form
     *
     * @param string $model     nome form/modello
     * @param string $host_model
     * @param type $saveXml
     * @return boolean
     */
    public function compileXml($model, $host_model = '', $saveXml = '') {
        $this->duplicati = array();
        if (!$saveXml) {
            return false;
        }
        $this->setAttributekey('@attributes');
        $this->setTextNodeKey('@textNode');
        $sql = "SELECT * FROM ita_elementi,ita_dizionario where el_tipo=tipo_id and el_nome='$model'";
        $conf_padre = ItaDB::DBSQLSelect($this->italsoftDB, $sql, false);
        $arrayModel = array();
        if ($conf_padre) {
            $idPadre = $conf_padre['el_id'];
            unset($conf_padre['el_input']);
            $this->checkElemento($conf_padre['el_nome']);
            $this->nome_padre = $conf_padre['el_nome'];
            $this->nome_host = $host_model;
            if ($conf_padre['el_attributi']) {
                $attributi = unserialize($conf_padre['el_attributi']);
            } else {
                $attributi = array();
            }
            $properties = array();
            foreach ($attributi as $key => $value) {
                $properties[trim($key)] = array($this->textNodeKey => $value);
            }

            unset($conf_padre['el_classe']);
            unset($conf_padre['el_attributi']);
            unset($conf_padre['tipo_attributi']);
            unset($conf_padre['el_label_value']);
            unset($conf_padre['el_label_pos']);
            unset($conf_padre['el_label_class']);
            unset($conf_padre['el_label_width']);
            unset($conf_padre['el_access_key']);
            unset($conf_padre['el_alt_text']);
            unset($conf_padre['tipo_id']);
            unset($conf_padre['tipo_control']);
            unset($conf_padre['tipo_input']);
            unset($conf_padre['tipo_descr']);

            $arrayModel['engineModel'] = array(
                $this->attributeKey => $conf_padre,
                'properties' => $properties,
                'engineElement' => $this->addElementiToXmlArray($idPadre)
            );
        } else {
            return false;
        }


        require_once (ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $rootTag = "";
        $xmlObj->toXML($arrayModel, $rootTag);
        $fileXml = $xmlObj->getXml();
        $arrayModel['info'] = array(
            'date' => array($this->textNodeKey => date('d/m/Y')),
            'time' => array($this->textNodeKey => date('H:i:s')),
            'user' => array($this->textNodeKey => App::$utente->getKey('nomeUtente')),
            'modelSHA' => array($this->textNodeKey => sha1($fileXml))
        );
        $xmlObj->toXML($arrayModel, $rootTag);
        $fileXml = $xmlObj->getXml();
        $File = fopen($saveXml, "w+");
        fwrite($File, utf8_encode('<?xml version="1.0" encoding="UTF-8"?>') . "\n");
        fwrite($File, utf8_encode('<itaEngine>'));
        fwrite($File, utf8_encode($fileXml));
        fwrite($File, utf8_encode('</itaEngine>'));
        fclose($File);
        chmod($saveXml, 0777);
        return true;
    }

    public function getModelArray($model, $host_model = '') {
        $renderBackend_generator = App::getConf('renderBackEnd.generator');
        $renderBackend_altGenerator = App::getConf('renderBackEnd.altGenerator');
        switch ($renderBackend_generator) {
            case 'local/xml':
                $modelFile = $this->getModelFilename($model);
                if ($modelFile) {
                    return $this->getModelArrayXML($modelFile, $host_model);
                } else {
                    if ($renderBackend_altGenerator == 'dbms/table') {
                        return $this->getModelArrayDB($model, $host_model);
                    }
                }
            default:
                return $this->getModelArrayDB($model, $host_model);
        }
    }

    public function getModelArrayXML($modelFile, $host_model = '') {
        require_once(ITA_LIB_PATH . '/HTML/tag.php');
        require_once(ITA_LIB_PATH . '/HTML/html.php');
        require_once(ITA_LIB_PATH . '/HTML/formHTML.php');
        require_once (ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $this->setAttributekey('@attributes');
        $this->setTextNodeKey('@textNode');
        $arrayModelXML = array();
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromFile($modelFile);
        if (!$retXml) {
            Out::msgStop("Lettura XML", "Xml non conforme");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        $node = $arrayXml['engineModel'][0];
        $nome = utf8_decode($node[$this->attributeKey]['el_nome']);
        $this->nome_padre = $nome;
        $this->nome_host = $host_model;
        $arrayModelXML[$this->nome_padre]['@ATTRS@'] = $node[$this->attributeKey];
        if (isset($node['engineElement'])) {
            $arrayModelXML[$this->nome_padre]['@NODE@'] = $this->addElementiToArrayFromXML($node['engineElement']);
        }
        return $arrayModelXML;
    }

    private function addElementiToArrayFromXML($nodes) {
        if ($this->nome_host) {
            $this->nome_padre = $this->nome_host;
        }
        if (!isset($nodes[0])) {
            $nodes = array($nodes);
        }
        $arrayRet = array();
        foreach ($nodes as $node) {
            $nome = utf8_decode($node[$this->attributeKey]['el_nome']);
            $arrayRet[$nome]['@ATTRS@'] = $node[$this->attributeKey];
            if (isset($node['engineElement'])) {
                $arrayRet[$nome]['@NODE@'] = $this->addElementiToArrayFromXML($node['engineElement']);
            }
        }
        return $arrayRet;
    }

    public function getModelArrayDB($model, $host_model = '') {
        $sql = "SELECT * FROM ita_elementi,ita_dizionario where el_tipo=tipo_id and el_nome='$model'";
        $conf_padre = ItaDB::DBSQLSelect($this->italsoftDB, $sql, false);
        $arrayModel = array();
        if ($conf_padre) {
            $this->checkElemento($conf_padre['el_nome']);
            $this->nome_padre = $conf_padre['el_nome'];
            $this->nome_host = $host_model;
            if ($conf_padre['el_attributi']) {
                $attributi = unserialize($conf_padre['el_attributi']);
            } else {
                $attributi = array();
            }
            $arrayModel[$this->nome_padre][$this->attributeKey] = $conf_padre;

            $arrayModel[$this->nome_padre][$this->textNodeKey] = $this->addElementiToArray($conf_padre['el_id']);
            return $arrayModel;
        } else {
            return array();
        }
    }

    /**
     * Restituisce il codice HTML da inviare al client
     * @param type $model
     * @param type $preview
     * @param type $host_model
     * @return boolean
     */
    public function getModelHTML($model, $preview = false, $host_model = '', $innerHtml = false, $alias_model = '') {
        $renderBackend_generator = App::getConf('renderBackEnd.generator');
        $renderBackend_altGenerator = App::getConf('renderBackEnd.altGenerator');
        switch ($renderBackend_generator) {
            case 'local/xml':
                $modelFile = $this->getModelFilename($model);
                if ($modelFile) {
                    return $this->getModelXML($modelFile, $preview, $host_model, false, $innerHtml, $alias_model);
                } else {
                    if ($renderBackend_altGenerator == 'dbms/table') {
                        return $this->getModelDB($model, $preview, $host_model, $innerHtml, $alias_model);
                    }
                }
            default:
                return $this->getModelDB($model, $preview, $host_model, $innerHtml, $alias_model);
        }
    }

    public function removeLayoutHTML($html) {
        return str_ireplace('ita-layout-diag', '', $html);
    }

    /**
     *
     * @param type $model NOME FILE MODEL XML
     * @param type $preview
     * @param type $host_model
     * @return string
     */
    public function getModelXML($modelFile, $preview = false, $host_model = '', $tryFromDB = false, $innerHtml = false, $alias_model = '') {
        require_once(ITA_LIB_PATH . '/HTML/tag.php');
        require_once(ITA_LIB_PATH . '/HTML/html.php');
        require_once(ITA_LIB_PATH . '/HTML/formHTML.php');
        require_once (ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $this->setAttributekey('@attributes');
        $this->setTextNodeKey('@textNode');
//
// carico xml
//
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromFile($modelFile);
        if (!$retXml) {
            Out::msgStop("Lettura XML", "Xml non conforme.....");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
//
// carico il nodo root
//
        $node = $arrayXml['engineModel'][0];
        $nome = ($alias_model) ? $alias_model : utf8_decode($node[$this->attributeKey]['el_nome']);
        $tipo_tag = utf8_decode($node[$this->attributeKey]['tipo_tag']);
        $colonne = utf8_decode($node[$this->attributeKey]['el_colonne']);



        $this->checkElemento($nome);
        $this->nome_padre = $nome;
        $this->nome_host = $host_model;
//
// Carico properties per attributi html
//
        $attributi = array();
        if (isSet($node['properties'][0]) && is_array($node['properties'][0])) {
            foreach ($node['properties'][0] as $property => $value) {
                if ($property == $this->attributeKey)
                    continue;
                if ($property == $this->textNodeKey)
                    continue;
                $attributi[$property] = utf8_decode($value[0][$this->textNodeKey]);
            }
        }
        switch ($tipo_tag) {
            case 'form':
                $padre = new AV_Form($attributi['action'], $colonne, 'line', 'post', $this->nome_padre);
                unset($attributi['action']);
//                if(isset($attributi['style'])){
//                    $attributi['style'] ="border:1px dotted blue;" . $attributi['style'];
//                }else{
//                    $attributi['style'] ="border:1px dotted blue;";
//                }
                $padre->aggiungiAttributi($attributi);
                break;
            case 'div':
                if ($this->nome_host) {
                    $nome = $this->nome_host . "_" . $nome;
                }
                $padre = new AV_DivId($nome, isset($attributi['value']) ? $attributi['value'] : '');
                unset($attributi['value']);
                $padre->aggiungiAttributi($attributi);
                if ($colonne) {
                    $padre->add($padre2 = new AV_Tabella($colonne, array('cellspacing' => '0', 'cellpadding' => '0')));
                }
                break;
        }
        if ($padre) {
            $this->addElementiToFromXml($node['engineElement'], $padre);
            if ($innerHtml) {
                $righe = $padre->contenuto;
                $retHtml = "";
                foreach ($righe as $riga) {
                    if (is_object($riga)) {
                        if (is_subclass_of($riga, 'AV_Tag') or ( $riga instanceof AV_Tag)) {
                            $riga->nTab = $this->nTab + 1;
                        }
                        if (method_exists($riga, 'prendiContenuto')) {
                            $retHtml .= $riga->prendiContenuto();
                        }
                    } else {
                        //altrimenti inseriamo il testo
                        $retHtml .= $riga;
                    }
                }
                return $retHtml;
            } else {
                return $padre->prendiContenuto();
            }
        } else {
            return '';
        }
    }

    private function addElementiToFromXml($nodes, $objpadre) {
        if (!is_array($nodes)) {
            return;
        }
        if ($this->nome_host) {
            $this->nome_padre = $this->nome_host;
        }
        if (!isset($nodes[0])) {
            $nodes = array($nodes);
        }
        foreach ($nodes as $keyTag => $node) {
            $attributi = array();
            if (isset($node['properties'][0]) && is_array($node['properties'][0])) {
                foreach ($node['properties'][0] as $property => $value) {
                    if ($property == $this->attributeKey)
                        continue;
                    if ($property == $this->textNodeKey)
                        continue;
                    $attributi[$property] = utf8_decode($value[0][$this->textNodeKey]);
                }
            }
            /*
             * Leggo gli attributi dal nuovo nodo tipoProperties
             *
             */
            $attributi2 = array();
            if (isset($node['tipoProperties'][0]) && is_array($node['tipoProperties'][0])) {
                foreach ($node['tipoProperties'][0] as $property => $value) {
                    if ($property == $this->attributeKey)
                        continue;
                    if ($property == $this->textNodeKey)
                        continue;
                    $attributi2[$property] = utf8_decode($value[0][$this->textNodeKey]);
                }
            }
            /*
             * FUSIONE ATTRIBUTI DEL TIPO CON ATTRIBUTI ELEMENTO
             * @TODO If per evitare classi vuote?
             */
            $attributi ['class'] = isset($attributi['class']) ? $attributi ['class'] : '';
            $attributi2['class'] = isset($attributi2['class']) ? $attributi2['class'] : '';
            $attributi['class'] = trim($attributi2['class'] . ' ' . $attributi['class']);
            unset($attributi2['class']);
            $attributi = array_merge($attributi2, $attributi);

            $nome = utf8_decode($node[$this->attributeKey]['el_nome']);
            $tipo_tag = utf8_decode(isset($node[$this->attributeKey]['tipo_tag']) ? $node[$this->attributeKey]['tipo_tag'] : '');
            $colonne = utf8_decode(isset($node[$this->attributeKey]['el_colonne']) ? $node[$this->attributeKey]['el_colonne'] : '');
            $colspan = utf8_decode(isset($node[$this->attributeKey]['el_colspan']) ? $node[$this->attributeKey]['el_colspan'] : '');
            $rowspan = utf8_decode(isset($node[$this->attributeKey]['el_rowspan']) ? $node[$this->attributeKey]['el_rowspan'] : '');
            $acapo = utf8_decode(isset($node[$this->attributeKey]['disp_acapo']) ? $node[$this->attributeKey]['disp_acapo'] : '');
            $label = isset($node['label'][0]) ? $node['label'][0] : '';
            $figlio = $controlla_figli = '';
//imposto nome
            if ($tipo_tag != 'td') {
                $nome = $this->nome_padre . '_' . $nome;
                $this->checkElemento($nome);
            }

            if ($label) {
                $labelAttrs = array();
                if (isset($label[$this->attributeKey]['class']) && trim($label[$this->attributeKey]['class'])) {
                    $labelAttrs['class'] = utf8_decode($label[$this->attributeKey]['class']);
                }
                if (isset($label[$this->attributeKey]['style']) && trim($label[$this->attributeKey]['style'])) {
                    $labelAttrs['style'] = utf8_decode($label[$this->attributeKey]['style']);
                }
                $objLabel = new AV_Label($nome, utf8_decode($label[$this->textNodeKey]) . '&nbsp;&nbsp;', $labelAttrs);
            } else {
                $objLabel = false;
            }
            switch ($tipo_tag) {
                case 'text':
                    $input = new AV_Input('text', $nome, $attributi);
                    $figlio = new AV_DivClass('ita-field');
                    $figlio->aggiungiAttributo('id', $nome . "_field");
                    if ($objLabel) {
                        $figlio->add($objLabel);
                    }
                    $figlio->add($input);
                    break;
                case 'textarea':
                    $input = new AV_TextArea($nome);
                    $input->aggiungiAttributi($attributi);
                    $figlio = new AV_DivClass('ita-field');
                    $figlio->aggiungiAttributo('id', $nome . "_field");
                    if ($objLabel) {
                        $figlio->add($objLabel);
                    }
                    $figlio->add($input);
                    break;
                case 'select':
                    $select = new AV_Select($nome, array());
                    $select->aggiungiAttributi($attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $nome . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($select);
                    } else
                        $figlio = $select;
                    break;
                case 'checkbox':
                    $input = new AV_Input('checkbox', $nome, $attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $nome . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else
                        $figlio = $input;
                    break;
                case 'password':
                    $input = new AV_Input('password', $nome, $attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $nome . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else {
                        $figlio = $input;
                    }
                    break;
                case 'radio':
                    $save_name = $this->nome_padre . "_" . $attributi['name'];
                    $input = new AV_Input('radio', $nome, $attributi);
                    $input->rimuoviAttributo('name');
                    $input->aggiungiAttributo('name', $save_name);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $nome . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else
                        $figlio = $input;
                    break;
                case 'tabpane':
                    $figlio = $figlio2 = new AV_DivId('tab');
                    $controlla_figli = true;
                    break;

                case 'fieldset':
                    $figlio = new AV_Fieldset($attributi['Title'], $attributi);
                    $controlla_figli = true;
                    break;
                case 'div':
                    $figlio = new AV_DivId($nome, isset($attributi['value']) ? $attributi['value'] : '');
                    unset($attributi['value']);
                    $figlio->aggiungiAttributi($attributi);
                    if ($colonne) {
                        $figlio->add($figlio2 = new AV_Tabella($colonne, array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'button':
                    $figlio = new AV_Button('button', $nome);
                    $figlio->aggiungiAttributi($attributi);
                    break;
                case 'link':
                    $value = $attributi['value'];
                    unset($attributi['value']);
                    $figlio = new AV_link($value, '', $attributi);
                    $figlio->aggiungiAttributo('id', $nome);
                    break;
                case 'table':
                    $attributi['id'] = $nome;
                    $figlio = new AV_Tabella($colonne, $attributi, true);
                    $figlio->body->last()->aggiungiAttributo('id', 'baseRow');
                    $controlla_figli = true;
                    $figlio2 = $figlio;
                    break;
                case 'th':
                    $attributi['id'] = $nome;
                    $figlio = new AV_Tag('th');
                    $figlio->add(utf8_decode(isSet($label[$this->textNodeKey]) ? $label[$this->textNodeKey] : ''));
                    $figlio->aggiungiAttributi($attributi);
                    break;
                case 'td':
                    $attributi['id'] = $nome;
                    $figlio = new AV_Cella('');
                    $figlio->aggiungiAttributi($attributi);
                    $controlla_figli = true;
                    $figlio2 = $figlio;
                    break;
                case 'br':
                    $figlio = new AV_Tag('br');
                    $figlio->add(utf8_decode(isSet($label[$this->textNodeKey]) ? $label[$this->textNodeKey] : ''));
                    break;

                case 'span':
                    $figlio = new AV_Tag('span');
                    $attributi['id'] = $nome;
                    $figlio->aggiungiAttributi($attributi);
                    if ($colonne) {
                        $figlio->add($figlio2 = new AV_Tabella($colonne, array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'img':
                    $figlio = new AV_Tag('img');
                    $attributi['id'] = $nome;
                    $figlio->aggiungiAttributi($attributi);
                    if ($colonne) {
                        $figlio->add($figlio2 = new AV_Tabella($colonne, array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'ul':
                    $attributi['id'] = $nome;
                    $figlio = new AV_Tag('ul');
                    $figlio->add($label[$this->attributeKey]['el_label_value']);
                    $figlio->aggiungiAttributi($attributi);
                    $controlla_figli = true;
                    break;
                case 'li':
                    $attributi['id'] = $nome;
                    $figlio = new AV_Tag('li');
                    $figlio->add($label[$this->attributeKey]['el_label_value']);
                    $figlio->aggiungiAttributi($attributi);
                    break;
            }
            if ($figlio) {
                if ($acapo) {
                    $colspan = -1;
                } else {
                    $colspan = 0;
                }
                if ($figlio->tag == 'th' and $objpadre->tag == 'table') {
                    $objpadre->head->add($figlio);
                } else {
                    $objpadre->add($figlio, $colspan, $rowspan);
                }
                if ($controlla_figli) {
                    $this->addElementiToFromXml(isset($node['engineElement']) ? $node['engineElement'] : '', $figlio2);
                }
            }
        }
    }

    public function getModelDB($model, $preview = false, $host_model = '', $innerHtml = false, $alias_model = '') {
        if (!$this->italsoftDB->exists()) {
            return '';
        }

        require_once(ITA_LIB_PATH . '/HTML/tag.php');
        require_once(ITA_LIB_PATH . '/HTML/html.php');
        require_once(ITA_LIB_PATH . '/HTML/formHTML.php');

        $sql = "SELECT * FROM ita_elementi,ita_dizionario where el_tipo=tipo_id and el_nome='$model'";
        $conf_padre = ItaDB::DBSQLSelect($this->italsoftDB, $sql, false);


        $this->checkElemento($conf_padre['el_nome']);
        $this->nome_padre = ($alias_model) ? $alias_model : $conf_padre['el_nome'];
        $this->nome_host = $host_model;
        if ($conf_padre['el_attributi']) {
            $attributi = unserialize($conf_padre['el_attributi']);
        } else {
            $attributi = array();
        }
        switch ($conf_padre['tipo_tag']) {
            case 'form':
                $padre = new AV_Form($attributi['action'], $conf_padre['el_colonne'], 'line', 'post', $this->nome_padre);
                unset($attributi['action']);
                $padre->aggiungiAttributi($attributi);
                break;
            case 'div':
                //$nome=$conf_padre['el_nome'];
                $nome = $this->nome_padre;
                if ($this->nome_host) {
                    $nome = $this->nome_host . "_" . $nome;
                }
                $padre = new AV_DivId($nome, $attributi['value']);
                unset($attributi['value']);
                $padre->aggiungiAttributi($attributi);
                if ($conf_padre['el_colonne']) {
                    $padre->add($padre2 = new AV_Tabella($conf_padre['el_colonne'], array('cellspacing' => '0', 'cellpadding' => '0')));
                }
                break;
        }
        if ($padre) {
            $this->addElementiTo($conf_padre['el_id'], $padre);
            if ($innerHtml) {
                $righe = $padre->contenuto;
                $retHtml = "";
                foreach ($righe as $riga) {
                    if (is_object($riga)) {
                        if (is_subclass_of($riga, 'AV_Tag') or ( $riga instanceof AV_Tag)) {
                            $riga->nTab = $this->nTab + 1;
                        }
                        if (method_exists($riga, 'prendiContenuto')) {
                            $retHtml .= $riga->prendiContenuto();
                        }
                    } else {
                        //altrimenti inseriamo il testo
                        $retHtml .= $riga;
                    }
                }
                return $retHtml;
            } else {
                return $padre->prendiContenuto();
            }
        } else {
            return '';
        }
    }

    private function addElementiTo($idpadre, $objpadre) {
        if ($this->nome_host) {
            $this->nome_padre = $this->nome_host;
        }
        $figli = $this->getFigliElemento($idpadre);
        foreach ($figli as $conf_figlio) {
            $figlio = $controlla_figli = '';
//imposto nome
            if ($conf_figlio['tipo_tag'] != 'td') {
                $conf_figlio['el_nome'] = $this->nome_padre . '_' . $conf_figlio['el_nome'];
                $this->checkElemento($conf_figlio['el_nome']);
            }

//imposto attributi
            $attributi2 = $attributi = array();
            if ($conf_figlio['el_attributi'])
                $attributi = unserialize($conf_figlio['el_attributi']);
            if ($conf_figlio['tipo_attributi'])
                $attributi2 = unserialize($conf_figlio['tipo_attributi']);
            $attributi['class'] = trim($attributi2['class'] . ' ' . $attributi['class']);
            unset($attributi2['class']);
            $attributi = array_merge($attributi2, $attributi);
            if ($conf_figlio['el_classe']) {
                $attributi['class'] = trim($conf_figlio['el_classe'] . ' ' . $attributi['class']);
            }//creo label se esiste
            if ($label = $conf_figlio['el_label_value']) {
                switch ($conf_figlio['el_label_pos']) {
                    case 1:
                        $pos = 'sx input';
                        break;
                    case 2:
                        $pos = 'dx input';
                        break;
                    case 3:
                        $pos = 'top input';
                        break;
                    case 4:
                        $pos = 'bot input';
                        break;
                    default: $pos = 'sx ui-widget input';
                }
                if ($conf_figlio['el_label_class'])
                    $pos .= ' ' . $conf_figlio['el_label_class'];
                $labelAttrs = array();
                if (trim($pos)) {
                    $labelAttrs['class'] = $pos;
                }
                if (trim($conf_figlio['el_label_width'])) {
                    $labelAttrs['style'] = 'width:' . $conf_figlio['el_label_width'] . 'px';
                }
                $objLabel = new AV_Label($conf_figlio['el_nome'], trim($label) . '&nbsp;&nbsp;', $labelAttrs);
            } else {
                $objLabel = false;
            }
            switch ($conf_figlio['tipo_tag']) {
                case 'text':
                    $input = new AV_Input('text', $conf_figlio['el_nome'], $attributi);
                    $this->parse_cara($input, $conf_figlio['el_input']);
                    $figlio = new AV_DivClass('ita-field');
                    $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                    if ($objLabel) {
                        $figlio->add($objLabel);
                    }
                    $figlio->add($input);
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    break;
                case 'textarea':
                    $input = new AV_TextArea($conf_figlio['el_nome']);
                    $input->aggiungiAttributi($attributi);
                    $figlio = new AV_DivClass('ita-field');
                    $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                    if ($objLabel) {
                        $figlio->add($objLabel);
                    }
                    $figlio->add($input);
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $this->parse_cara($input, $conf_figlio['el_input']);
                    break;

                case 'select':
                    $select = new AV_Select($conf_figlio['el_nome']);
                    $select->aggiungiAttributi($attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($select);
                    } else
                        $figlio = $select;
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $this->parse_cara($select, $conf_figlio['el_input']);
                    break;
                case 'checkbox':
                    $input = new AV_Input('checkbox', $conf_figlio['el_nome'], $attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else
                        $figlio = $input;
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $this->parse_cara($input, $conf_figlio['el_input']);
                    break;
                case 'password':
                    $input = new AV_Input('password', $conf_figlio['el_nome'], $attributi);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else {
                        $figlio = $input;
                    }
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $this->parse_cara($input, $conf_figlio['el_input']);
                    break;
                case 'radio':
                    $save_name = $this->nome_padre . "_" . $attributi['name'];
                    $input = new AV_Input('radio', $conf_figlio['el_nome'], $attributi);
                    $input->rimuoviAttributo('name');
                    $input->aggiungiAttributo('name', $save_name);
                    if ($objLabel) {
                        $figlio = new AV_DivClass('ita-field');
                        $figlio->aggiungiAttributo('id', $conf_figlio['el_nome'] . "_field");
                        $figlio->add($objLabel);
                        $figlio->add($input);
                    } else
                        $figlio = $input;
                    if ($preview)
                        $input->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $this->parse_cara($input, $conf_figlio['el_input']);
                    break;
                case 'tabpane':
                    $figlio = $figlio2 = new AV_DivId('tab');
                    $controlla_figli = true;
                    break;

                case 'fieldset':
                    $figlio = new AV_Fieldset($attributi['Title'], $attributi);
                    $controlla_figli = true;
                    break;
                case 'div':
                    $figlio = new AV_DivId($conf_figlio['el_nome'], $attributi['value']);
                    unset($attributi['value']);
                    $figlio->aggiungiAttributi($attributi);
                    if ($conf_figlio['el_colonne']) {
                        $figlio->add($figlio2 = new AV_Tabella($conf_figlio['el_colonne'], array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'button':
                    $figlio = new AV_Button('button', $conf_figlio['el_nome']);
                    $figlio->aggiungiAttributi($attributi);
                    if ($preview)
                        $figlio->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    break;
                case 'link':
                    $figlio = new AV_link($conf_figlio['el_label_value'], '', $attributi);
                    if ($preview)
                        $figlio->aggiungiAttributo('onclick', 'location.href=\'add.php?id=' . $conf_figlio['el_id'] . "'");
                    $figlio->aggiungiAttributo('id', $conf_figlio['el_nome']);
                    break;
                case 'table':
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio = new AV_Tabella($conf_figlio['el_colonne'], $attributi, true);
                    $figlio->body->last()->aggiungiAttributo('id', 'baseRow');
                    $controlla_figli = true;
                    $figlio2 = $figlio;
                    break;
                case 'th':
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio = new AV_Tag('th');
                    $figlio->add($conf_figlio['el_label_value']);
                    $figlio->aggiungiAttributi($attributi);
                    break;
                case 'td':
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio = new AV_Cella();
                    $figlio->aggiungiAttributi($attributi);
                    $controlla_figli = true;
                    $figlio2 = $figlio;
                    break;
                case 'br':
                    $figlio = new AV_Tag('br');
                    $figlio->add($conf_figlio['el_label_value']);
                    break;

                case 'span':
                    $figlio = new AV_Tag('span');
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio->aggiungiAttributi($attributi);
                    if ($conf_figlio['el_colonne']) {
                        $figlio->add($figlio2 = new AV_Tabella($conf_figlio['el_colonne'], array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'img':
                    $figlio = new AV_Tag('img');
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio->aggiungiAttributi($attributi);
                    if ($conf_figlio['el_colonne']) {
                        $figlio->add($figlio2 = new AV_Tabella($conf_figlio['el_colonne'], array('cellspacing' => '0', 'cellpadding' => '0')));
                    } else
                        $figlio2 = $figlio;
                    $controlla_figli = true;
                    break;
                case 'ul':
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio = new AV_Tag('ul');
                    $figlio->add($conf_figlio['el_label_value']);
                    $figlio->aggiungiAttributi($attributi);
                    $controlla_figli = true;
                    break;
                case 'li':
                    $attributi['id'] = $conf_figlio['el_nome'];
                    $figlio = new AV_Tag('li');
                    $figlio->add($conf_figlio['el_label_value']);
                    $figlio->aggiungiAttributi($attributi);
                    break;
            }
            if ($figlio) {
                if ($conf_figlio['disp_acapo']) {
                    $conf_figlio['disp_colspan'] = -1;
                } else {
                    $conf_figlio['disp_colspan'] = 0;
                }
                if ($figlio->tag == 'th' and $objpadre->tag == 'table') {
                    $objpadre->head->add($figlio);
                } else {
                    $objpadre->add($figlio, $conf_figlio['disp_colspan'], $conf_figlio['disp_rowspan']);
                }
                if ($controlla_figli) {
                    if ($preview)
                        $figlio->aggiungiClass('subliv');
                    $this->addElementiTo($conf_figlio['el_id'], $figlio2);
                }
            }
        }
    }

    private function addElementiToArray($id) {
        $figli = $this->getFigliElemento($id);
        $arrayRet = array();
        foreach ($figli as $conf_figlio) {
            if ($conf_figlio['tipo_tag'] != 'td' && $conf_figlio['tipo_tag'] != 'br') {
                $this->checkElemento($this->nome_padre . "_" . $conf_figlio['el_nome']);
            }
            $arrayRet[$conf_figlio['el_nome']]['@ATTRS@'] = $conf_figlio;
            $arrayRet[$conf_figlio['el_nome']]['@NODE@'] = $this->addElementiToArray($conf_figlio['el_id']);
        }
        return $arrayRet;
    }

    /**
     * Carica la variabile array che poi sara trasformato in xml
     * @param String $id    id del nodo da elaborare
     * @return <type>
     */
    private function addElementiToXmlArray($id) {
        $figli = $this->getFigliElemento($id);
        $arrayRet = array();
        foreach ($figli as $conf_figlio) {
            $idElemento = $conf_figlio['el_id'];
            if ($conf_figlio['tipo_tag'] != 'td' && $conf_figlio['tipo_tag'] != 'br') {
                $this->checkElemento($this->nome_padre . "_" . $conf_figlio['el_nome']);
            }

            $attributi2 = $attributi = array();
            if ($conf_figlio['el_attributi'])
                $attributi = unserialize($conf_figlio['el_attributi']);
            if ($conf_figlio['tipo_attributi'])
                $attributi2 = unserialize($conf_figlio['tipo_attributi']);

            /*
             * SWITCH
             *
             * FUSIONE ATTRINUTI DEL ELEMENTO CON GLI ATTRIBUTI DEL TIPO DIZIONARIO
             * PER IL PASSAGGIO AL NUOVO GENERATOR DI FORM BLOCCARE in fase di swicth
             */
            $attributi['class'] = trim($attributi2['class'] . ' ' . $attributi['class']);
            unset($attributi2['class']);
            $attributi = array_merge($attributi2, $attributi);
            /*
             * FINE FUSIONE
             */

            /*
             * CONCATENAZIONE CAMPO CLASSE CON CAMPO ATTRIBUTO DI TIPO CLASSE, DEPRECATO NEL NUOVO GENERATOR
             */
            if (trim($conf_figlio['el_classe'])) {
                $attributi['class'] = trim($conf_figlio['el_classe'] . ' ' . $attributi['class']);
            }
            unset($conf_figlio['el_attributi']);
            unset($conf_figlio['el_classe']);
            unset($conf_figlio['tipo_attributi']);

            if ($conf_figlio['el_input']) {
                $inputProps = unserialize($conf_figlio['el_input']);
                if ($inputProps['required'])
                    $attributi['class'] .= ' required';
                if ($inputProps['text-transform'] && $inputProps['text-transform'] != 'none')
                    $attributi['class'] .= ' ' . $inputProps['text-transform'];
            }

            $properties = array();
            foreach ($attributi as $key => $value) {
                $properties[trim($key)] = array($this->textNodeKey => $value);
            }


            /*
             * SWITCH
             *
             * nuovo nodo attrubuti provenienti dal dizionario tipi
             * salvato separatamente dal nodo properties
             * per agevolare l'edit dell'xml da programma genEditor
             *
             * da attivaare in fase di switch della gestione delle form
             *
             */
//            $tipoProperties = array();
//            foreach ($attributi2 as $key => $value) {
//                $tipoProperties[trim($key)] = array($this->textNodeKey => $value);
//            }
            /*
             * Fine nuovo nodo
             *
             */
            $labelNode = array();

            if ($label = $conf_figlio['el_label_value']) {
                switch ($conf_figlio['el_label_pos']) {
                    case 1:
                        $pos = 'sx input';
                        break;
                    case 2:
                        $pos = 'dx input';
                        break;
                    case 3:
                        $pos = 'top input';
                        break;
                    case 4:
                        $pos = 'bot input';
                        break;
                    default: $pos = 'sx ui-widget input';
                }
                if ($conf_figlio['el_label_class'])
                    $pos .= ' ' . $conf_figlio['el_label_class'];

                $labelNode[$this->attributeKey]['class'] = $pos;
                if (trim($conf_figlio['el_label_width'])) {
                    $labelNode[$this->attributeKey]['style'] = 'width:' . $conf_figlio['el_label_width'] . 'px';
                }
                $labelNode[$this->textNodeKey] = trim($label);
            }

            unset($conf_figlio['el_input']);
            unset($conf_figlio['el_label_value']);
            unset($conf_figlio['el_label_pos']);
            unset($conf_figlio['el_label_class']);
            unset($conf_figlio['el_label_width']);
            unset($conf_figlio['el_access_key']);
            unset($conf_figlio['el_alt_text']);
            unset($conf_figlio['disp_padre']);
            unset($conf_figlio['disp_clone']);
            unset($conf_figlio['disp_id']);
            unset($conf_figlio['disp_ordine']);
            unset($conf_figlio['disp_model']);
            unset($conf_figlio['tipo_id']);
            unset($conf_figlio['tipo_control']);
            unset($conf_figlio['tipo_input']);
            unset($conf_figlio['tipo_descr']);

            $engineElement = array(
                $this->attributeKey => $conf_figlio,
            );
            if ($labelNode) {
                $engineElement = array_merge($engineElement, array('label' => $labelNode));
            }
            if ($properties) {
                $engineElement = array_merge($engineElement, array('properties' => $properties));
            }


            /*
             * SWITCH
             *
             * AGGIUNGO IL NUOVO NODO tipoProperties all'array per poi convertirlo in xml
             * DA ATTIVARE IN FASE DI SWITCH
             */
//            if ($tipoProperties) {
//                $engineElement = array_merge($engineElement, array('tipoProperties' => $tipoProperties));
//            }
            /*
             * fine aggiunta su array
             */

            $engineElement = array_merge($engineElement, array('engineElement' => $this->addElementiToXmlArray($idElemento)));
            $arrayRet[] = $engineElement;
        }
        return $arrayRet;
    }

    private function parse_cara($figlio, $el_input) {
        if ($el_input) {
            $cara = unserialize($el_input);
            if ($cara['required'])
                $figlio->aggiungiClass('required');
            if ($cara['text-transform'] && $cara['text-transform'] != 'none')
                $figlio->aggiungiClass($cara['text-transform']);
        }
    }

    private function getFigliElemento($id) {
        $sql = "SELECT * FROM ita_elementi inner join ita_disposizione on el_id=disp_id inner join ita_dizionario on el_tipo=tipo_id where disp_padre=$id order by disp_ordine ASC";
        return ItaDB::DBSQLSelect($this->italsoftDB, $sql, true);
    }

    function checkElemento($key) {
        if (!key_exists($key, $this->elementi)) {
            $this->elementi[$key] = $key;
        } else {
            $this->duplicati[$key] ++;
            //Out::msgStop("Errore","Elemento duplicato: ".$key);
        }
    }

    function getArrDisposizione($model) {
        $sql = "SELECT * FROM ita_elementi,ita_dizionario where el_tipo=tipo_id and el_nome='$model'";
        $conf_padre = ItaDB::DBSQLSelect($this->italsoftDB, $sql, false);
        $id_padre = $conf_padre['el_id'];
        $arrFigli = array();
        $arrFigli = $this->getFigliElementoRecursive($id_padre, $arrFigli);
        return $arrFigli;
    }

    function getFigliElementoRecursive($id, $arrFigli) {
        $arrTmp = $this->getFigliElemento($id);
        if ($arrTmp) {
            foreach ($arrTmp as $key => $figlio) {
                $arrFigli[] = $figlio['el_id'];
                $arrFigli = $this->getFigliElementoRecursive($figlio['el_id'], $arrFigli);
            }
            $arrTmp = null;
            return $arrFigli;
        } else {
            return $arrFigli;
        }
    }

    public function myUtf8Decode($value) {
        return utf8_decode($value);
    }

    function getModelFilename($model) {
        $modelFile = "";
        $localPath = App::getConf('renderBackEnd.localPath');
        $formRoute = App::getPath('formRoute.' . substr($model, 0, 3));
        if (!$formRoute) {
            return false;
        }
        if (file_exists($localPath . "/" . $formRoute . "/" . $model . ".xml")) {
            $modelFile = $localPath . "/" . $formRoute . "/" . $model . ".xml";
            return $modelFile;
        } else {
            return false;
        }
    }

    /**
     * Ritorna gli elementi grafici della pagina 
     * @param type $nameForm string o array  nome della pagina in cui cercare oppure elenco dei componeti su cui cercare
     * @param type $elementToFind componente specifici da cui cercare (string o array se + di uno) +
     * @param type $componentTypeToFind tipi di elementi da cercare (esempio div, edit ecc) 
     * @param type $componentNameToFind nome degli elementi da cercare (esempio lookup). effettua la contains sul nome 
     * @param type $recursive va in ricorsione sui nodi sottostanti per cercare gli elementi
     * @param type $showAttrs ritorna anche gli attributo per oni nodo trovato 
     * @return array $toReturn array di elementi della pagina 
     */
    public function getElementsFromForm($nameForm, $elementToFind = array(), $componentTypeToFind = array(), $componentNameToFind = array(), $recursive = false, $showAttrs = false) {
        if ($nameForm && is_array($nameForm)) {
            $formArray = $nameForm;
        } else {
            $formArray = $this->getModelArray($nameForm);
        }

        $form = $formArray[$nameForm];
        $toReturn = $form;
        if ($elementToFind && is_array($elementToFind)) {
            foreach ($elementToFind as $value) {
                $toReturn = $toReturn['@NODE@'][$value];
            }
            $toReturn = $toReturn['@NODE@'];
        } else {
            $toReturn = $form[$elementToFind];
        }

        if (!is_array($toReturn)) {
            $toReturn = array();
        }
        if ($recursive) {
            $result = array();
            foreach ($toReturn as $key => $value) {
                $this->getRecursiveChildren($key, $value, $result, $componentTypeToFind, $componentNameToFind);
            }
            $toReturn = $result;
        }

        if (!$showAttrs) {
            $app = array();
            foreach ($toReturn as $key => $value) {
                $app[] = $key;
            }

            return $app;
        } else {
            return $toReturn;
        }
    }

    public function getRecursiveChildren($key, $element, &$toReturn, $componentTypeToFind, $componentNameToFind) {
        if (is_array($element) && $element['@NODE@']) {
            foreach ($element['@NODE@'] as $keyEl => $value) {
                if ($this->verifyAddElement($value, $componentTypeToFind, $componentNameToFind, true)) {
                    $toReturn[$keyEl] = $value;
                } else {
                    $this->getRecursiveChildren($keyEl, $value, $toReturn, $componentTypeToFind, $componentNameToFind);
                }
            }
        } else {
            if ($this->verifyAddElement($element, $componentTypeToFind, $componentNameToFind, false)) {
                $toReturn[$key] = $element;
            }
        }
    }

    // controlla se soddisfo le condizioni per aggiungere l'elemento mediante tipo e nome 
    private function verifyAddElement($element, $componentTypeToFind, $componentNameToFind, $isContainer = false) {
        if ((empty($componentTypeToFind) && $isContainer === false) ||
                ($componentTypeToFind && in_array($element["@ATTRS@"]["tipo_tag"], $componentTypeToFind))) {
            if (empty($componentNameToFind)) {
                return true;
            } else {
                foreach ($componentNameToFind as $value) {
                    if (strpos($element["@ATTRS@"]["el_nome"], $value) !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

}

?>
