<?php

/**
 * Definisce i metodi che definisconi la creazione dell' output <XML>
 * per il motore di controllo itaEngine.js
 *
 * @author Michele Moscioni
 */

/**
 *
 * CLASSE DI COLLOQUIO CON XML CON itaEngine.js
 * Definisce i metodi per la creazione dell' output <XML>
 * necessario il motore di controllo itaEngine.js
 *
 * PHP Version 5
 *
 * @category   Library
 * @package    /lib/itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    19.10.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
class Out {

    static public $contenuto;

    /**
     * Azzzera il contenuto che accumula le specifiche per creare <XML>
     */
    static function clean() {
        self::$contenuto = '';
    }

    /**
     * Analizza le specifiche accumulate nel contenuto e le trasforma in
     * una stringa <XML>
     *
     * @param String    $formato
     * @param Array     $outArray
     * @return <type>
     */
    static private function parseArray($formato, $outArray) {
        switch ($formato) {
            case 'xml' :
                $out = '';
                if ($outArray) {
                    foreach ($outArray as $element) {
                        $tag = "<$element[tag]";
                        foreach ($element['attributi'] as $key => $value) {
                            $tag .= " $key=\"$value\"";
                        }
                        if ($element['nodevalue']) {
                            if (is_array($element['nodevalue'])) {
                                $tag .= '>' . self::parseArray($formato, $element['nodevalue']) . '</' . $element[tag] . '>';
                            } else {
                                $tag .= ">$element[nodevalue]</$element[tag]>";
                            }
                        } else {
                            $tag .= "> </$element[tag]>";
                        }
                        $out .= $tag . "\n";
                    }
                }
                break;
        }
        return $out;
    }

    /**
     * Restituisce il contenuto dell'oggetto Out in formato <XML>
     *
     * @param String $formato
     * @return String
     */
    static function get($formato) {
        switch ($formato) {
            case 'xml' :
                header('Content-Type: text/xml; charset=ISO-8859-1');
                $out = '<?xml version="1.0" encoding="ISO-8859-1"?>
                      <root>
            ';

                $out .= self::parseArray($formato, self::$contenuto);
                $out .= '</root>';
                break;
        }
        /*
         * Pulizia caratteri non visualizzabile acii da 1 a 31 eccetto 9,10,13
         */
        return preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', '', $out);
    }

    /**
     * Crea un contenitore <DIV> allinterno dell'elemento html specificato
     *
     * @param string $parent Elemento ospite del contenitore
     * @param string $id Attributo id del contenitore
     * @param string $attr Attributi aggiuntivi (non usato)
     */
    static function addContainer($parent, $id, $attr = '', $modo = 'add') {
        if ($parent == '' || $parent == 'body') {
            $parent = 'body';
        } else {
            $parent = '#' . $parent;
        }

        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => array(
                'parent' => $parent,
                'id' => $id,
                'comando' => $modo
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Trasforma un div in una dialog ( metoto obsoleto, iniettare un div con classe ita-dialog e quindi usare setDialogOption)
     * @param string $id
     * @param string $attr
     */
    static function setContainerAsDialog($id, $attr = '') {
        if ($parent == '') {
            $parent = 'body';
        }
        $tagattr = array(
            'parent' => $parent,
            'id' => $id,
            'comando' => 'dialog'
        );

        if (is_array($attr)) {
            foreach ($attr as $key => $value) {
                $tagattr[$key] = $value;
            }
        }
        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => $tagattr,
            'nodevalue' => ''
        );
    }

    /**
     * elemina un elemento dal dom della pagina itaEngine
     *
     * @param type $id
     */
    static function removeElement($id) {
        ;
        self::delContainer($id);
    }

    /**
     *
     * @param string $id
     * @param string $attr
     */
    static function delContainer($id, $attr = '') {
        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => array(
                'parent' => '',
                'id' => $id,
                'comando' => 'del'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Abilita un campo d'input
     * @param string $id
     */
    static function enableField($id) {
        self::$contenuto[] = array(
            'tag' => 'field',
            'attributi' => array(
                'id' => $id,
                'comando' => 'enable'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Abilita tutti i campi input all'interno del container
     * @param string $id Id del container
     * @param string $selector Selettore aggiuntivo per filtrare gli elementi
     * da abilitare
     */
    static function enableContainerFields($id, $selector = false) {
        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => array(
                'id' => $id,
                'comando' => 'enablefields',
                'selector' => $selector
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Disabilita un campo d'input
     * @param string $id
     */
    static function disableField($id) {
        self::$contenuto[] = array(
            'tag' => 'field',
            'attributi' => array(
                'id' => $id,
                'comando' => 'disable'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Disabilita tutti i campi input all'interno del container
     * @param string $id Id del container
     * @param string $selector Selettore aggiuntivo per filtrare gli elementi
     * da disabilitare
     */
    static function disableContainerFields($id, $selector = false) {
        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => array(
                'id' => $id,
                'comando' => 'disablefields',
                'selector' => $selector
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Ripristina un campo di input al suo stato originale
     * (abilitato / disabilitato)
     * @param string $id
     */
    static function restoreField($id) {
        self::$contenuto[] = array(
            'tag' => 'field',
            'attributi' => array(
                'id' => $id,
                'comando' => 'restore'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Ripristina tutti i campi input all'interno di un container
     * @param string $id Id del container
     * @param string $selector Selettore aggiuntivo per filtrare gli elementi
     * da ripristinare
     */
    static function restoreContainerFields($id, $selector = false) {
        self::$contenuto[] = array(
            'tag' => 'container',
            'attributi' => array(
                'id' => $id,
                'comando' => 'restorefields',
                'selector' => $selector
            ),
            'nodevalue' => ''
        );
    }

    /**
     *
     * @param type $id
     * @param type $value
     */
    static function setDialogTitle($id, $value) {
        self::$contenuto[] = array(
            'tag' => 'setDialogTitle',
            'attributi' => array(
                'id' => $id
            ),
            'nodevalue' => '<![CDATA[' . $value . ']]>'
        );
    }

    /**
     *
     * @param type $id
     * @param type $value
     */
    static function setAppTitle($id, $value) {
        self::$contenuto[] = array(
            'tag' => 'setAppTitle',
            'attributi' => array(
                'id' => $id
            ),
            'nodevalue' => '<![CDATA[' . $value . ']]>'
        );
    }
    
    /**
     * Modifica il nome applicativo visualizzato subito sotto la tab.
     * @param type $nameform Nome della form
     * @param type $title Titolo da visualizzare
     */
    static function setAppSubTitle($nameform, $title) {
        self::html("tab-$nameform span.appPath", $title);
    }

    /**
     *
     * @param <type> $id
     * @param <type> $opt
     * @param <type> $value
     */
    static function setDialogOption($id, $opt, $value) {
        self::$contenuto[] = array(
            'tag' => 'setDialogOption',
            'attributi' => array(
                'id' => $id,
                'option' => $opt
            ),
            'nodevalue' => '<![CDATA[' . $value . ']]>'
        );
    }

    /**
     *
     * @param <type> $id
     * @param <type> $method
     * @param <type> $param
     */
    static function callDialogMethod($id, $method, $param = '') {
        self::$contenuto[] = array(
            'tag' => 'callDialogMethod',
            'attributi' => array(
                'id' => $id,
                'method' => $method
            ),
            'nodevalue' => '<![CDATA[' . $param . ']]>'
        );
    }

    /**
     *
     * @param string $id
     */
    static function closeDialog($id) {
        self::$contenuto[] = array(
            'tag' => 'dialog',
            'attributi' => array(
                'id' => $id,
                'comando' => 'close'
            ),
            'nodevalue' => ' '
        );
    }

    static function closeCurrentDialog() {
        self::$contenuto[] = array(
            'tag' => 'dialog',
            'attributi' => array(
                'id' => '',
                'comando' => 'closeCurrent'
            ),
            'nodevalue' => ' '
        );
    }

    /**
     * Istanza una dialog da un div
     * 
     * @param type $id identificativo html della dialog da istanziare
     */
    static function showDialog($id) {
        self::$contenuto[] = array(
            'tag' => 'showDialog',
            'attributi' => array(
                'id' => $id
            ),
            'nodevalue' => ' '
        );
    }

    /**
     *   Abilita in alcuni casi la rotellina d'attesa
     */
    static function enableBlockMsg() {
        self::codice('enableBlockMsg=true;');
    }

    /**
     *
     */
    static function disableBlockMsg() {
        self::codice('enableBlockMsg=false;');
    }

    /**
     * 
     * @param type $titolo
     * @param type $messaggio
     * @param type $height
     * @param type $width
     * @param type $container
     * @param type $noRound
     */
    public function msgDialog($titolo, $messaggio, $height = 'auto', $width = 'auto', $container = '', $noRound = false) {
        $dialogId = 'msgDialog-' . rand(0, 999999);
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", "
            <div id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . addslashes($titolo) . "',modal:true,resizable:false}\">
                $messaggio
            </div>
                ");
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
    }

    /**
     *
     * @param type $parent
     * @param type $timeout millsec
     * @param type $modal
     * @param type $nodevalue
     */
    static function msgBlock($parent, $timeout, $modal, $nodevalue) {
        self::$contenuto[] = array(
            'tag' => 'msgBlock',
            'attributi' => array(
                'parent' => $parent,
                'timeout' => $timeout,
                'modal' => $modal
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     * @param <type> $titolo Titolo della dialog
     * @param <type> $messaggio Messaggio da visualizzsare con dialog Jquery
     */
    static function msgStop($titolo, $messaggio, $height = 'auto', $width = 'auto', $container = '', $noRound = false) {
        $dialogId = 'msgStop-' . rand(0, 999999);

        if (App::$clientEngine == 'itaMobile') {
            self::msgStandardMobile($titolo, $messaggio, $dialogId, 'msgStop', $height, $container);
            return true;
        }

        $dialogHtml = "<div id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . addslashes($titolo) . "',modal:true,resizable:false}\">";
        $dialogHtml .= "<div style=\"overflow:auto; max-height:400px; max-width:600px; padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-error ui-corner-all\">";
        $dialogHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-alert\"/><div class=\"ita-Wordwrap\">";
        $dialogHtml .= "$messaggio</div></div></div>";
        
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", $dialogHtml);
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
    }

    /**
     * 
     * @param type $titolo
     * @param type $messaggio
     * @param type $mailButton
     * @param type $clipboardButton
     * @param type $height
     * @param type $width
     * @param type $container
     * @param type $noRound
     * @return boolean
     */
    static function msgError($titolo, $messaggio, $mailButton = true, $clipboardButton = true, $height = 'auto', $width = 'auto', $container = '', $noRound = false) {
        $dialogId = 'msgError-' . rand(0, 999999);
        $btnHtml = '';

        if ($clipboardButton) {
            $btnHtml .= ' <button type="button" style="width:150px;" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus ita-clipboard { target: \'#' . $dialogId . '_messageContent\' }">
                            <span class="ui-icon ui-icon-clipboard"></span><span>Copia negli appunti</span>
                          </button>';
        }

        if ($mailButton) {
            $btnHtml .= ' <a
                            id="sendError"
                            href="mailto:?subject=Segnalazione errore itaEngine ' . htmlentities($titolo, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1') . '&body=' . htmlentities(urlencode($messaggio), ENT_COMPAT | ENT_HTML401, 'ISO-8859-1') . '">
                                <button style="width:150px;" type="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-focus"><span class="ui-icon ui-icon-email"></span><span>Invia segnalazione</span></button></a>';
        }

        if ($btnHtml != '') {
            $btnHtml = "<div style=\"padding-top: 1em; margin-top: 1em; border-top: 1px solid #aaa; text-align: right;\">$btnHtml</div>";
        }

        $dialogHtml = "<div id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . addslashes($titolo) . "',modal:true,resizable:false}\">";
        $dialogHtml .= "<div style=\"overflow:auto; max-height:400px; max-width:600px; padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-error ui-corner-all\">";
        $dialogHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-alert\"/><div class=\"ita-Wordwrap\" id=\"" . $dialogId . "_messageContent\">";
        $dialogHtml .= "$messaggio</div></div>$btnHtml</div>";

        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", $dialogHtml);
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
    }

    static function msgProgress($procObj, $titolo, $container, $height = 'auto', $width = 'auto', $closeButton = true, $header = '', $trailer = '') {
        $dialogId = 'msgProgress-' . $procObj->getProcToken();
        $progressId = 'msgProgress-progress-' . $procObj->getProcToken();
        $progressLabelId = 'msgProgress-progress-label-' . $procObj->getProcToken();
        $createLabel = $procObj->getCreateLabel();
        $responseTimeout = $procObj->getResponseTimeout();
        $completeLabel = $procObj->getCreateLabel();
        $headerHtml = '';
        if ($header) {
            $headerHtml = "<div style=\"overflow: auto; padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-highlight ui-corner-all\">";
            $headerHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-info\"/>";
            $headerHtml .= "<div class=\"ita-Wordwrap\">" . $header . "</div></div>";
        }
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", "
            <div id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . addslashes($titolo) . "',modal:true,width:'$width',height:'$height',resizable:false,closeButton:true}\">
                {$headerHtml}
                <div id =\"$progressId\" class=\"ita-progress-bar {refreshDelay:{$procObj->getRefreshDelay()},openCallback:{event:'startProcess',id:'{$procObj->getProcToken()}',model:'{$procObj->getModel()}',asyncCall:true},createLabel:'{$createLabel}',completeLabel:'{$completeLabel}',max:{$procObj->getProgressMax()},value:{$procObj->getProgressVal()},responseTimeout:{$responseTimeout}}\">
                    <div id =\"$progressLabelId\" class=\"ita-progress-label\"><span class=\"ita-progress-label-span\">Attendere...</span></div>                    
                </div>
		</div>
            </div>
                ");
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
    }

    /**
     *
     * @param type $titolo Titolo del messaggio
     * @param type $campi Array di campi da visualizzare
     * @param type $bottoni Array di bottoni
     * @param type $container Div della form che esegue il messaggio come contenitore
     * @param type $height
     * @param type $width
     * @param type $closeButton
     * @param type $header  Intestazione
     * @param type $trailer
     */
    static function msgInput($titolo, $campi, $bottoni, $container = '', $height = 'auto', $width = 'auto', $closeButton = true, $header = '', $trailer = '', $closeAuto = true) {
        $dialogId = 'msgInput-' . rand(0, 999999);
        $btnhtml = "";
        $campohtml = "";
        if (!$campi[0]) {
            $save_campi = $campi;
            $campi = array();
            $campi[] = $save_campi;
        }
        foreach ($campi as $key => $campo) {
            //
            // Inizializzo variabili campo
            //
            $textNode = "";
            $labelhtml = $class = "";
            //
            // Prendo il node value
            //
            if (isset($campo['@textNode@'])) {
                $textNode = $campo['@textNode@'];
                unset($campo['@textNode@']);
            }
            //
            // Prendo la classe
            //
            if (isset($campo['class'])) {
                $class = $campo['class'];
                unset($campo['class']);
            }
            //
            // Prendo l'eventiale label
            //
            if (isset($campo['label'])) {
                $value = $campo['label'];
                if (is_array($value)) {
                    $labelhtml .= "<label id =\"" . $campo['id'] . "_lbl\" class=\"sx input\" style=\"" . $value['style'] . "\">" . $value['value'] . '</label>';
                } else {
                    $labelhtml .= "<label>" . $value . '</label>';
                }
                unset($campo['label']);
            }
            $input = "input";
            $campohtml .= '<div id="' . $campo['id'] . '_field" class="ita-field">';
            switch ($campo['type']) {
                case "textarea":
                    $input = "textarea";
                    break;
                case "select":
                    $input = "select";
                    $class .= ' ita-select';
                    foreach ($campo['options'] as $key => $value) {
                        $sel = "";
                        if ($value[2] == true) {
                            $sel = "selected";
                        }
                        $textNode .= '<option ' . $sel . ' value="' . $value[0] . '">' . $value[1] . '</option>';
                    }
                    break;
                default:
                    break;
            }
            $campohtml .= $labelhtml . "<$input class=\"ita-edit ui-widget-content ui-corner-all $class\" ";
            foreach ($campo as $attr => $value) {
//                if ($campo['type'] == "textarea") {
//                    if ($attr == "value") {
//                        $nodeValue = $value;
//                        continue;
//                    }
//                }
                if (in_array($attr, array('label', 'br'))) {
                    continue;
                }

                $campohtml .= " " . $attr . "=\"" . $value . "\"";
            }
            $campohtml .= ">$textNode</$input>";
            $campohtml .= "</div>";

            if (isset($campo['br']) && $campo['br'] === false) {
                continue;
            }

            $campohtml .= '<br>';

//            if ($key + 1 < count($campi)) {
//                $campohtml .= "<br>";
//            }
        }

        foreach ($bottoni as $bottone => $bParm) {
            $shortCut = ($bParm['shortCut']) ? "shortCut:'" . $bParm['shortCut'] . "'" : "";
            $class = ($bParm['class']) ? $bParm['class'] . " " : "";
            $metaData = ($bParm['metaData']) ? $bParm['metaData'] . "," : "";

            $request = isset($bParm['request']) ? $bParm['request'] : 'ItaForm';
            $metaData .= "request:'$request'";
//            $metaData .= "request:'ItaForm'";

            $metaData .= ",model:'" . $bParm['model'] . "'";
            $metaData .= ",id:'" . $bParm['id'] . "'";
            $metaData .= ",event:'onClick'";
            $metaData .= ",noObj:true";
            if ($closeAuto == true) {
                if (App::$clientEngine == 'itaMobile') {
                    $metaData .= ",extraCode:'closeUIDialog(\'" . $dialogId . "\');'";
                } else {
                    $metaData .= ",extraCode:'$(\'#" . $dialogId . "_wrapper\').dialog(\'close\');'";
                }
            }
            $metaData = ($shortCut != '' && $metaData != '' ) ? $shortCut . "," . $metaData : $shortCut . $metaData;
            $btnHtml .= '<button
                            type    = "button"
                            id      = "' . $bParm['id'] . '"
                            name    = "' . $bParm['id'] . '"
                            style   = "diplay:inline-block;"
                            class   = "ita-button ita-element-animate ' . $class . '{' . $metaData . '}"
                            value   = "' . $bottone . '"
                        />';
        }
        $headerHtml = '';
        if ($header) {
            $headerWidth = $width == 'auto' ? 'max-width: 600px;' : '';
            $headerHeight = $height == 'auto' ? 'max-height: 400px;' : '';

            $headerHtml = "<div style=\"overflow: auto; $headerWidth $headerHeight padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-highlight ui-corner-all\">";
            $headerHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-info\"/>";
            $headerHtml .= "<div class=\"ita-Wordwrap\">" . $header . "</div></div>";
        }
        $trailerHtml = '';
        if ($trailer) {
            $footerWidth = $width == 'auto' ? 'max-width: 600px;' : '';
            $footerHeight = $height == 'auto' ? 'max-height: 400px;' : '';

            $trailerHtml = "<div style=\"overflow: auto; $footerWidth $footerHeight padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-highlight ui-corner-all\">";
            $trailerHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-info\"/>";
            $trailerHtml .= "<div class=\"ita-Wordwrap\">" . $trailer . "</div></div>";
        }

        if (App::$clientEngine == 'itaMobile') {
            $diagHtml = "<div tabindex=0 id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . $titolo . "',modal:true,width:'$width',height:'$height',resizable:false,closeButton:" . $closeButton . "}\">$headerHtml<div style=\"overflow:auto; max-height:600px; max-width:800px;\" class=\"ita-box ita-data-page\">$campohtml</div>$trailerHtml<br>$btnHtml<br><br></div>";
        } else {
            $diagHtml = "
            <div tabindex=0 id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . $titolo . "',modal:true,width:'$width',height:'$height',resizable:false,closeButton:" . $closeButton . "}\">
                $headerHtml
                <div style=\"overflow:auto; max-height:600px; max-width:800px; padding: 0pt 0.5em;\" class=\"ita-box ita-data-page\">
                $campohtml
		</div>
                $trailerHtml
                <br>" .
                $btnHtml . "<br><br>
            </div>";
        }

        self::addContainer($container, $dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", $diagHtml);
        self::setDialogOption($dialogId, 'title', "'" . $titolo . "'");
        self::setDialogOption($dialogId, 'resizable', 'false');
        $primoCampo = reset($campi);
        self::setFocus('', $primoCampo['id']);
    }

    /**
     * Costruisce una Finestra di dialogo dove mostrare un messaggio di informazione
     *
     * @param String $titolo      Titolo della dialogo
     * @param String $messaggio   Messaggio
     * @param Int $height
     * @param Int $width
     */
    static function msgInfo($titolo, $messaggio, $height = 'auto', $width = 'auto', $container = 'desktopBody', $noRound = false, $noIcon = false) {
        $dialogId = 'msgInfo-' . md5(rand(0, 999999) * microtime());

        if (App::$clientEngine == 'itaMobile') {
            self::msgStandardMobile($titolo, $messaggio, $dialogId, 'msgInfo', $height, $container);
            return true;
        }

        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");

        $html = "
<div id =\"$dialogId\" class=\"ita-dialog {title:'" . addslashes($titolo) . "', modal:true, width:'$width', height:'$height', resizable:false}\">
    <div style=\"overflow:auto; " . ($height == 'auto' ? 'max-height:400px;' : '') . ($width == 'auto' ? 'max-width:600px;' : '' ) . " padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-highlight ui-corner-all\">";
        if ($noIcon == false) {
            $html .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-info\"/>";
        }
        $html .= "
        <div class=\"ita-Wordwrap\">$messaggio</div>
    </div>
</div>";

        self::html($dialogId . "_wrapper", $html);
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
    }

    /**
     * Costruttore standard per dialog mobile
     *
     * @param String $titolo      Titolo della dialogo
     * @param String $messaggio   Messaggio
     * @param String $type		  msgInfo / msgStop
     * @param Int $height
     */
    private static function msgStandardMobile($titolo, $messaggio, $dialogId, $type, $height = 'auto', $container = 'desktopBody') {
        $type = $type == 'msgStop' ? 'ita-box-error' : '';
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        $html = '<div id="' . $dialogId . '" class="ita-dialog {title:\'' . addslashes($titolo) . '\'}" style="height: ' . $height . ';"><div class="ita-box ' . $type . '"><p>' . $messaggio . '</p></div></div>';
        self::html($dialogId . "_wrapper", $html, 'prepend');
        self::show($dialogId . "_wrapper");
    }

    /**
     *
     * @param <type> $titolo
     * @param <type> $messaggio
     * @param <type> $bottoni
     * @param <type> $height
     * @param <type> $width
     */
    static function msgQuestion($titolo, $messaggio, $bottoni, $height = 'auto', $width = 'auto', $closeButton = 'true', $noRound = false, $closeAuto = true, $verticalButtons = false, $requestCall = "ItaForm", $modal = 'true', $parent = '') {
        $dialogId = 'msgWait-' . rand(0, 999999);
        $btnhtml = "";
        foreach ($bottoni as $bottone => $bParm) {
            $shortCut = ($bParm['shortCut']) ? "shortCut:'" . $bParm['shortCut'] . "'" : "";
            $class = ($bParm['class']) ? $bParm['class'] . " " : "";
            $style = ($bParm['style']) ? $bParm['style'] . " " : "float:right;";

            if ($verticalButtons && App::$clientEngine == 'itaMobile') {
                $style .= "display: block;";
            }

            $metaData = ($bParm['metaData']) ? $bParm['metaData'] . "," : "";
            $metaData .= "request:'$requestCall'";
            $metaData .= ",model:'" . $bParm['model'] . "'";
            $metaData .= ",id:'" . $bParm['id'] . "'";
            $metaData .= ",event:'onClick'";
            $metaData .= ",noObj:true";
            if ($closeAuto == true) {
                if (App::$clientEngine == 'itaMobile') {
                    $metaData .= ",extraCode:'closeUIDialog(\'" . $dialogId . "\');'";
                } else {
                    $metaData .= ",extraCode:'$(\'#" . $dialogId . "_wrapper\').dialog(\'close\');'";
                }
            }
            $metaData = ($shortCut != '' && $metaData != '' ) ? $shortCut . "," . $metaData : $shortCut . $metaData;

            $btnHtml .= '<button
                            type    = "button"
                            id      = "' . $bParm['id'] . '"
                            name    = "' . $bParm['id'] . '"
                            style   = "' . $style . '" 
                            class   = "ita-button ita-element-animate ' . $class . '{' . $metaData . '}"
                            value   = "' . $bottone . '"
                        ></button>';
            if ($verticalButtons) {
                $btnHtml .= "<br>";
            }
        }
        $diagHtml = "
            <div tabindex=0 id =\"" . $dialogId . "\" class=\"ita-dialog {title:'" . addslashes($titolo) . "',modal:" . $modal . ",resizable:false,closeButton:" . $closeButton . "}\">";
        if ($messaggio) {
            $diagHtml .= "<div style=\"overflow: auto; max-height: 400px; max-width: 600px; padding: 10px 10px 10px 35px; box-sizing: border-box;\" class=\"ita-box ui-state-highlight\">";
            $diagHtml .= "<span style=\"position: absolute; left: 10px;\" class=\"ui-icon ui-icon-notice\"/>";
            $diagHtml .= "<div class=\"ita-Wordwrap\">" . $messaggio . "</div></div><br>";
        }
        $diagHtml .= $btnHtml . "<br><br>
            </div>";



        //self::addContainer('desktopBody', $dialogId . "_wrapper");
        self::addContainer($parent, $dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", $diagHtml);

        self::setDialogOption($dialogId, 'title', "'" . $titolo . "'");
        self::setDialogOption($dialogId, 'resizable', 'false');
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
    }

    static function openDocument($url, $print = false) {
        self::$contenuto[] = array(
            'tag' => 'openDocument',
            'attributi' => array(
                'stampa' => $print
            ),
            'nodevalue' => '<![CDATA[' . $url . ']]>'
        );
    }

    /**
     * Costruisce una Finestra di dialogo dove mostrare un messaggio di informazione
     *
     * @param String $titolo      Titolo della dialogo
     * @param String $messaggio   Messaggio
     * @param Int $height
     * @param Int $width
     */
    static function openIFrame($titolo, $IFrameId, $IFrameSrc, $height = 'auto', $width = 'auto', $container = 'desktopBody', $noRound = false, $autoPrint = false) {
        $dialogId = 'dlgIFrame-' . rand(0, 999999);
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", "
            <div id =\"" . $dialogId . "\" class=\"ita-dialog {resizable:true,title:'" . addslashes($titolo) . "',modal:true,resizable:false}\">
                <div id=\"" . $IFrameId . "_container\">
                <iframe src=\"$IFrameSrc\" id=\"$IFrameId\" height=\"$height\" width=\"$width\"></iframe>
                </div>
            </div>
                ");
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
        if ($autoPrint) {
            self::codice("$('#" . $dialogId . "_wrapper').everyTime(1000,
                function(){
                    $('#" . $dialogId . "_wrapper').focus();
                });");

            self::codice("$('#" . $IFrameId . "').load(
                function() {
                    document.getElementById('" . $IFrameId . "').focus();
                    document.getElementById('" . $IFrameId . "').contentWindow.print();
                });");
        }
    }

    /**
     * Costruisce una Finestra di dialogo dove mostrare un messaggio di informazione
     *
     * @param String $titolo      Titolo della dialogo
     * @param String $messaggio   Messaggio
     * @param Int $height
     * @param Int $width
     */
    static function openObject($titolo, $objectId, $objectSrc, $height = 'auto', $width = 'auto', $container = 'desktopBody', $noRound = false) {
        $objectSrc = "test.pdf";
        $dialogId = 'dlgObject-' . rand(0, 999999);
        self::addContainer($container, $dialogId . "_wrapper");
        self::hide($dialogId . "_wrapper");
        self::html($dialogId . "_wrapper", "
            <div id =\"" . $dialogId . "\" class=\"ita-dialog {resizable:true,title:'" . addslashes($titolo) . "',modal:true,resizable:false}\">
                <p onClick=\"alert('click $objectId');document.getElementById('$objectId').print();\">Print file</p></br>                                
                <div id=\"pdf\" style=\"border:2px solid red;width:400px;height:400px;\">
                <object id=\"$objectId\" name=\"$objectId\" width=\"100%\" height=\"100%\" type=\"application/pdf\" data=\"$objectSrc\">132321</object>
                </div>
            </div>
                ");
        if ($noRound == true) {
            self::codice('$("#' . $dialogId . '_wrapper").parents(\'div:eq(0)\').removeClass(\'ui-corner-all\');');
        }
        self::show($dialogId . "_wrapper");
        return $dialogId;
    }

    /**
     *
     * @param string $container
     * @param string $nodevalue
     * @param string $modo
     */
    static function html($container, $nodevalue, $modo = '') {
        self::$contenuto[] = array(
            'tag' => 'html',
            'attributi' => array(
                'container' => $container,
                'modo' => $modo
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     * @param string $container
     * @param string $nodevalue
     * @param string $modo
     */
    static function dialogHtml($container, $nodevalue, $modo = '') {
        self::$contenuto[] = array(
            'tag' => 'dialogHtml',
            'attributi' => array(
                'container' => $container,
                'modo' => $modo,
                'openMode' => 'dialog'
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     * @param string $container
     * @param string $nodevalue
     * @param string $modo
     */
    static function appHtml($container, $nodevalue, $modo = '') {
        self::$contenuto[] = array(
            'tag' => 'appHtml',
            'attributi' => array(
                'container' => $container,
                'modo' => $modo,
                'openMode' => 'app'
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     * @param string $container
     * @param string $nodevalue
     * @param string $modo
     */
    static function innerHtml($container, $nodevalue, $modo = '') {
        self::$contenuto[] = array(
            'tag' => 'innerHtml',
            'attributi' => array(
                'container' => $container,
                'modo' => $modo,
                'openMode' => 'inner'
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    static function codice($nodevalue) {
        self::$contenuto[] = array(
            'tag' => 'codice',
            'attributi' => array(),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     *  Mostra l'elemento HTML
     * @param String $id  ID dell'elemento HTML da mostrare
     * @param String $effect  Effetto 'speciale'
     * @param Int $duration   Durata dell'effetto
     *
     * @return String $valore   sksljkjk
     */
    static function show($id, $effect = 'slide', $duration = 0) {
        self::$contenuto[] = array(
            'tag' => 'show',
            'attributi' => array(
                'id' => $id,
                'effect' => $effect,
                'options' => '',
                'duration' => $duration),
            'nodevalue' => " "
        );
    }

    /**
     *  Nasconde l'elemento HTML
     * @param String $id  ID dell'elemento HTML da nascondere
     * @param String $effect  Effetto 'speciale'
     * @param Int $duration   Durata dell'effetto
     */
    static function hide($id, $effect = 'slide', $duration = 0) {
        self::$contenuto[] = array(
            'tag' => 'hide',
            'attributi' => array(
                'id' => $id,
                'effect' => $effect,
                'options' => '',
                'duration' => $duration),
            'nodevalue' => " "
        );
    }

    /**
     *
     * @param type $id
     * @param type $proprieta
     * @param type $nodevalue
     */
    static function css($id, $proprieta, $nodevalue = '') {
        self::$contenuto[] = array(
            'tag' => 'css',
            'attributi' => array(
                'id' => $id,
                'prop' => $proprieta
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    /**
     *
     * @param type $id
     * @param type $attributo
     * @param type $del
     * @param type $nodevalue
     */
    static function attributo($id, $attributo, $del = '0', $nodevalue = '') {
        self::$contenuto[] = array(
            'tag' => 'attributi',
            'attributi' => array(
                'id' => $id,
                'attributo' => $attributo,
                'del' => $del
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    static function setFocus($form = '', $id = '') {
        self::$contenuto[] = array(
            'tag' => 'setFocus',
            'attributi' => array(
                'form' => $form,
                'id' => $id
            ),
            'nodevalue' => "x"
        );
    }

    /**
     * Imposta il focus sull'elemento successivo a quello indicato da $id.
     * @param type $id Elemento di riferimento.
     */
    static function setFocusNext($id) {
        self::codice('var f = moveNext(document.getElementById("' . $id . '")); $(f).focus();');
    }

    /**
     * Imposta il focus sull'elemento precedente a quello indicato da $id.
     * @param type $id Elemento di riferimento.
     */
    static function setFocusPrev($id) {
        self::codice('var f = movePrev(document.getElementById("' . $id . '")); $(f).focus();');
    }

    /**
     * Blocca un elemento (div) con un overlay grigio.
     *
     * @param string $id id univcoco elemento
     */
    static function block($id = '', $bgColor = '#000000', $opacity = '.1') {
        self::$contenuto[] = array(
            'tag' => 'block',
            'attributi' => array(
                'id' => $id,
                'bgColor' => $bgColor,
                'opacity' => $opacity
            ),
            'nodevalue' => " "
        );
    }

    /**
     * Sblocca un elemento (div) blocccato con la funzione block
     *
     * @param string $id id univico elemento
     */
    static function unBlock($id = '') {
        self::$contenuto[] = array(
            'tag' => 'unBlock',
            'attributi' => array(
                'id' => $id
            ),
            'nodevalue' => " "
        );
    }

    static function clearFields($form, $container = '') {
        self::$contenuto[] = array(
            'tag' => 'clearFields',
            'attributi' => array(
                'form' => $form,
                'container' => $container
            ),
            'nodevalue' => " "
        );
    }

    /**
     *  Assegna un valore ad un elemento HTML
     * @param <string> $id        ID dell'elemento che prende il valore
     * @param <string> $nodevalue Valore acquisito
     * @param <boolean> $setTooltip se true setta il title del campo con il valore
     */
    static function valore($id, $nodevalue, $setTooltip=false) {
        self::$contenuto[] = array(
            'tag' => 'valori',
            'attributi' => array(
                'id' => $id
            ),
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
        if($setTooltip == true){
            self::setTooltip($id, $nodevalue);
        }
    }

    static function addClass($id, $class) {
        self::$contenuto[] = array(
            'tag' => 'classi',
            'attributi' => array(
                'id' => $id,
                'comando' => 'add'
            ),
            'nodevalue' => $class
        );
    }

    static function delClass($id, $class) {
        self::$contenuto[] = array(
            'tag' => 'classi',
            'attributi' => array(
                'id' => $id,
                'comando' => 'del'
            ),
            'nodevalue' => $class
        );
    }

    static function toggleClass($id, $class) {
        self::$contenuto[] = array(
            'tag' => 'classi',
            'attributi' => array(
                'id' => $id,
                'comando' => 'toggle'
            ),
            'nodevalue' => $class
        );
    }

    static function valori($nodevalues, $formAlias = '') {
        foreach ($nodevalues as $field => $nodevalue) {
            if ($formAlias != '') {
                $field = $formAlias . '[' . $field . ']';
            }
            self::$contenuto[] = array(
                'tag' => 'valori',
                'attributi' => array(
                    'id' => $field,
                ),
                'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
            );
        }
    }

    static function broadcastMessage($sender, $message, $extradata = null) {
        $jsonRet = '';
        if ($extradata) {
            $json = new Services_JSON();
            $jsonRet = "{";
            $tmpArray2 = array();
            foreach ($extradata as $key => $value) {
                $tmpArray2[] = "\"$key\":" . $json->encode(utf8_encode($value));
            }
            $jsonRet .= implode(',', $tmpArray2);
            $jsonRet .= "}";
        }
        self::$contenuto[] = array(
            'tag' => 'broadcastMsg',
            'attributi' => array(
                'sender' => $sender,
                'message' => $message
            ),
            'nodevalue' => '<![CDATA[' . $jsonRet . ']]>'
        );
    }

    /**
     *
     * @param string $id            identificativo elemento html select
     * @param int $comando          0 = rimuove valore, 1 = aggiunge valore
     * @param string $returnval     valore di ritorno
     * @param string $selected      0 = non selezionato, 1 = selezionato
     * @param string $nodevalue     valore visualizzato
     */
    static function select($id, $comando, $returnval, $selected, $nodevalue, $style = '') {

        self::$contenuto[] = array(
            'tag' => 'select',
            'attributi' => array(
                'id' => $id,
                'comando' => $comando,
                'returnval' => htmlspecialchars($returnval, ENT_COMPAT, 'ISO-8859-1'),
                'selected' => $selected,
                'style' => $style
            ),
            //'nodevalue' => htmlspecialchars($nodevalue)
            'nodevalue' => '<![CDATA[' . $nodevalue . ']]>'
        );
    }

    static function setCellValue($tableId, $rowid, $colname, $value, $class = '', $properties = '', $forceup = 'true') {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'colname' => $colname,
                'class' => $class,
                'properties' => $properties,
                'forceup' => $forceup,
                'comando' => 'setCellValue'
            ),
            'nodevalue' => '<![CDATA[' . $value . ']]>'
        );
    }

    static function setCellFocus($tableId, $rowid, $colname) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'colname' => $colname,
                'comando' => 'setCellFocus'
            ),
            'nodevalue' => ' '
        );
    }

    /**
     * 
     * @param type $tableId id della tbella
     * @param type $rowid identificativo riga
     * @param type $rowData dati da aggiornare. Se vuoto non aggiorna i dati
     * @param type $css 
     */
    static function setRowData($tableId, $rowid, $rowData, $css) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'comando' => 'setRowData'
            ),
            'nodevalue' => '<data><![CDATA[' . $rowData . ']]></data><css><![CDATA[' . $css . ']]></css>'
        );
    }

    /**
     * 
     * @param type $tableId id della tbella
     * @param type $rowData dati footer da inserire
     */
    static function setFooterData($tableId, $rowData) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'comando' => 'setFooterData'
            ),
            'nodevalue' => '<data><![CDATA[' . json_encode($rowData) . ']]></data>'
        );
    }

    static function enableRowSelection($tableId, $rowid, $selectionType) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'type' => $selectionType,
                'comando' => 'enableSelection'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function disableRowSelection($tableId, $rowid, $selectionType) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'type' => $selectionType,
                'comando' => 'disableSelection'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function setRowSelection($tableId, $rowid, $selectionType, $propagateEvent) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'rowid' => $rowid,
                'type' => $selectionType,
                'event' => $propagateEvent ? '1' : '0',
                'comando' => 'setSelection'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function setRowSelectAll($tableId) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'comando' => 'setSelectAll'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function setRowDeselectAll($tableId) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'comando' => 'setDeselectAll'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function gridReload($tableId) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $tableId,
                'comando' => 'reload'
            ),
            'nodevalue' => 'xxx'
        );
    }

    static function addXML($idTabella, $xml, $clearGrid = false) {
        if ($clearGrid == true) {
            $clear = '1';
        } else {
            $clear = '0';
        }
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'comando' => 'addXML',
                'clearGrid' => $clear
            ),
            'nodevalue' => $xml
        );
    }

    static function addJson($idTabella, $json, $clearGrid = false) {
        if ($clearGrid == true) {
            $clear = '1';
        } else {
            $clear = '0';
        }
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'comando' => 'addJson',
                'clearGrid' => $clear
            ),
            'nodevalue' => '<![CDATA[' . $json . ']]>'
        );
    }

    /**
     * Vuota una tabella esistente
     *
     * @param <type> $idTabella Identifica la tabella da vuotare
     */
    static function delAllRow($idTabella) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'idRiga' => '',
                'comando' => 'del'
            ),
            'nodevalue' => ''
        );
    }

    static function setGridCaption($id, $value) {
        self::codice('$("#' . $id . '").setCaption("' . $value . '");');
    }

    static function gridShowCol($tableId, $colName) {
        self::codice("$('#$tableId').showCol('$colName');");
    }

    static function gridHideCol($tableId, $colName) {
        self::codice("$('#$tableId').hideCol('$colName');");
    }

    static function gridForceResize($tableId) {
        self::codice("$('#$tableId').trigger('resize');");
    }

    static function setGridOption($id, $opt, $value) {
        self::$contenuto[] = array(
            'tag' => 'setGridOption',
            'attributi' => array(
                'id' => $id,
                'option' => $opt
            ),
            'nodevalue' => '<![CDATA[' . $value . ']]>'
        );
    }

    /**
     *
     * @param <type> $messaggio Messaggio da visualizzare con Alert js
     */
    static function alert($messaggio) {
        $messaggio = addslashes($messaggio); //str_replace("'", "\'", $messaggio);
        self::codice("alert('$messaggio');");
    }

    static function checkDataButton($model, $perms) {
        if (!$model) {
            return;
        }
        if ($perms['noInsert']) {
            self::codice("$('#$model').find('.ita-button-new').remove();");
            self::codice("$('#$model').find('.ita-addgridrow').remove();");
        }

        if ($perms['noEdit']) {
            //self::codice("$('#$model').find('.ita-button-new').remove();");
            self::codice("$('#$model').find('.ita-button-commit').remove();");
            //self::codice("$('#$model').find('.ita-addgridrow').remove();");
            self::codice('
            $("#' . $model . '").find(".ita-edit-page").find(".ita-edit").each(function(){
                $(this).find(".ita-edit").each(function(){
                    $(this).attr("disabled","disabled").addClass("ita-readonly");
                });
            });
            ');
        }

        if ($perms['noDelete']) {
            self::codice("$('#$model').find('.ita-button-delete').remove();");
            self::codice("$('#$model').find('.ita-delgridrow').remove();");
        }
    }

    static function tabCommad($comando, $idTab, $idPane = '', $html = '') {
        $nodevalue = ' ';
        if ($html) {
            $nodevalue = '<![CDATA[' . $html . ']]>';
        }
        self::$contenuto[] = array(
            'tag' => 'tabs',
            'attributi' => array(
                'idTab' => $idTab,
                'idPane' => $idPane,
                'comando' => $comando
            ),
            'nodevalue' => $nodevalue
        );
    }

    /**
     * Seleziona/Attiva un pannello nel tab
     * @param type $idTab
     * @param type $idPane
     */
    static function tabSelect($idTab, $idPane) {
        self::tabCommad('select', $idTab, $idPane);
    }

    /**
     * Seleziona/Attiva un pannello del desktop
     * @param type $idApp 'ita-home' per selezionare la scheda principale
     */
    static function desktopTabSelect($idApp) {
        self::tabCommad('select-desktop', $idApp);
    }

    /**
     * Abilita un pannello nel tab
     * @param type $idTab
     * @param type $idPane
     */
    static function tabEnable($idTab, $idPane) {
        self::tabCommad('enable', $idTab, $idPane);
    }

    /**
     * Disabilita un pannello nel tab
     * @param type $idTab
     * @param type $idPane
     */
    static function tabDisable($idTab, $idPane) {
        self::tabCommad('disable', $idTab, $idPane);
    }

    /**
     * rimuove un pannello nel tab
     * @param type $idTab
     * @param type $idPane
     */
    static function tabRemove($idTab, $idPane) {
        self::tabCommad('remove', $idTab, $idPane);
    }

    /**
     * aggiunge un pannello nel tab
     * @param type $idTab  Tab dove inserire il pannello
     * @param type $idPane Pannello di riferimento per inserimento nuovo tab ( inserito prima)
     */
    static function tabAdd($idTab, $idPane = '', $html) {
        self::tabCommad('add', $idTab, $idPane, $html);
    }

    /**
     * ggiunge titolo ad un pannello nel tab
     * @param type $idTab
     * @param type $idPane
     * @param type $html
     */
    static function tabSetTitle($idTab, $idPane = '', $html = '') {
        self::tabCommad('setTitle', $idTab, $idPane, $html);
    }

    /**
     * Apre un pannello laterale chiuso
     * @param type $idPanel ID del pannello laterale
     */
    static function openLayoutPanel($idPanel) {
        self::$contenuto[] = array(
            'tag' => 'layout',
            'attributi' => array(
                'idPanel' => $idPanel,
                'comando' => 'open'
            )
        );
    }

    /**
     * Chiude un pannello laterale aperto
     * @param type $idPanel ID del pannello laterale
     */
    static function closeLayoutPanel($idPanel) {
        self::$contenuto[] = array(
            'tag' => 'layout',
            'attributi' => array(
                'idPanel' => $idPanel,
                'comando' => 'close'
            )
        );
    }

    /**
     * Nasconde un pannello laterale
     * @param type $idPanel ID del pannello laterale
     */
    static function hideLayoutPanel($idPanel) {
        self::$contenuto[] = array(
            'tag' => 'layout',
            'attributi' => array(
                'idPanel' => $idPanel,
                'comando' => 'hide'
            )
        );
    }

    /**
     * Mostra un pannello laterale precedentemente nascosto
     * @param type $idPanel ID del pannello laterale
     */
    static function showLayoutPanel($idPanel) {
        self::$contenuto[] = array(
            'tag' => 'layout',
            'attributi' => array(
                'idPanel' => $idPanel,
                'comando' => 'show'
            )
        );
    }

    static function systemEcho($line, $outFlush = false) {
        echo $line;
        if ($outFlush && ob_get_level() > 0) {
            ob_flush();
        }
    }

    static function activateUploader($id) {
        if (defined('Conf::ITA_ENGINE_VERSION') && Conf::ITA_ENGINE_VERSION > '1.0') {
            self::codice("pluploadActivate('$id');");
        }
    }

    /**
     * Apre una nuova finestra di itaEngine con i criteri indicati
     * @param array $openParams Parametri di apertura finestra
     * $openParams['ieurl']*
     * $openParams['ietoken']*
     * $openParams['iedomain']*
     * $openParams['ieOpenMessage']
     * @param array $requestParams Parametri di apertura model
     * $requestParams['model']*
     * $requestParams['menu']*
     * $requestParams['prog']*
     * $requestParams['accessreturn']*
     */
    static function openIEWindow($openParams, $requestParams) {
        $openParams['ieparent'] = App::$utente->getKey('ditta');

        self::codice('ieOpenWindows(' . json_encode($openParams) . ', ' . json_encode($requestParams) . ');');
    }

    static function menuGridInit($id, $opts) {
        self::$contenuto[] = array(
            'tag' => 'menugrid',
            'attributi' => array(
                'id' => $id,
                'comando' => 'init'
            ),
            'nodevalue' => '<![CDATA[' . json_encode($opts) . ']]>'
        );
    }

    static function menuGridClear($id) {
        self::codice("menuGrids['{$id}'].remove_all_widgets();");
    }

    static function menuGridDestroy($id) {
        self::codice("menuGrids['{$id}'].destroy(); menuGrids['{$id}'] = null; $('#{$id}').html('');");
    }

    static function menuGridAddWidget($id, $html, $size_x = 1, $size_y = 1, $col = 'undefined', $row = 'undefined') {
        self::codice("menuGrids['{$id}'].add_widget('{$html}', $size_x, $size_y, $col, $row);");
    }

    /**
     * Aggiunge figli ad un nodo dell'albero
     * @param string $idTabella Nome tabella
     * @param string $json Contiene l'array di nodi da aggiungere all'albero 
     */
    static function treeAddChildren($idTabella, $json) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'comando' => 'addChildren'
            ),
            'nodevalue' => '<![CDATA[' . $json . ']]>'
        );
    }

    static function addGridRow($idTabella, $rowidRiga, $datiRiga, $position = 'last', $referenceRowid = false) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'rowid' => $rowidRiga,
                'position' => $position,
                'reference' => $referenceRowid,
                'comando' => 'add'
            ),
            'nodevalue' => '<![CDATA[' . json_encode($datiRiga) . ']]>'
        );
    }

    static function delGridRow($idTabella, $rowidRiga) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'idRiga' => $rowidRiga,
                'comando' => 'del'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Rimuove figli ad un nodo dell'albero
     * @param string $idTabella Nome tabella
     * @param string $json Contiene id del nodo padre a cui rimuovere i figli
     */
    static function treeRemoveChildren($idTabella, $json) {
        self::$contenuto[] = array(
            'tag' => 'tabella',
            'attributi' => array(
                'id' => $idTabella,
                'comando' => 'removeChildren'
            ),
            'nodevalue' => '<![CDATA[' . $json . ']]>'
        );
    }

    /**
     * Mostra uno dei bottoni inline.
     * 
     * @param string $tableId ID della griglia.
     * @param string $button Indica quale bottone mostrare.
     * ( all | view | edit | delete )
     */
    static function gridShowInlineButton($tableId, $button = 'all') {
        switch ($button) {
            case 'all':
                self::gridShowInlineButton($tableId, 'view');
                self::gridShowInlineButton($tableId, 'edit');
                self::gridShowInlineButton($tableId, 'delete');
                break;

            case 'view':
                self::gridShowCol($tableId, 'VIEWROW');
                break;

            case 'edit':
                self::gridShowCol($tableId, 'EDITROW');
                break;

            case 'delete':
                self::gridShowCol($tableId, 'DELETEROW');
                break;
        }
    }

    /**
     * Nasconde uno dei bottoni inline.
     * 
     * @param string $tableId ID della griglia.
     * @param string $button Indica quale bottone nascondere.
     * ( all | view | edit | delete )
     */
    static function gridHideInlineButton($tableId, $button = 'all') {
        switch ($button) {
            case 'all':
                self::gridHideInlineButton($tableId, 'view');
                self::gridHideInlineButton($tableId, 'edit');
                self::gridHideInlineButton($tableId, 'delete');
                break;

            case 'view':
                self::gridHideCol($tableId, 'VIEWROW');
                break;

            case 'edit':
                self::gridHideCol($tableId, 'EDITROW');
                break;

            case 'delete':
                self::gridHideCol($tableId, 'DELETEROW');
                break;
        }
    }

    static function disableGrid($tableId) {
        self::codice("$('#$tableId').data('isDisabled', true);");
        self::gridHideInlineButton($tableId);
        self::hide($tableId . '_delGridRow');
        self::hide($tableId . '_viewGridRow');
        self::hide($tableId . '_editGridRow');
        self::hide($tableId . '_addGridRow');
    }

    static function enableGrid($tableId) {
        self::codice("$('#$tableId').data('isDisabled', false);");
        self::gridShowInlineButton($tableId);
        self::show($tableId . '_delGridRow');
        self::show($tableId . '_viewGridRow');
        self::show($tableId . '_editGridRow');
        self::show($tableId . '_addGridRow');
    }

    /**
     * Aggiunge degli header aggiuntivi che raggruppano quelli presenti.
     * 
     * @param string $tableId ID della tabella.
     * @param array $tableGroupHeaders Un array contenente per ogni header
     * aggiuntivo un array associativo che lo descrive. Es.
     * [ [ startColumnName => SUB, numberOfColumns => 1, titleText => HEADER ], [ ... ] ]
     */
    public static function gridGroupHeaders($tableId, $tableGroupHeaders) {
        $groupHeaders = '[';
        $separator = '';

        foreach ($tableGroupHeaders as $value) {
            $groupHeaders .= $separator . "{startColumnName: '" . $value['startColumnName'] . "', numberOfColumns: " . $value['numberOfColumns'] . ",titleText: '" . $value['titleText'] . "'}";
            $separator = ',';
        }

        $groupHeaders .= ']';

        $js = "jQuery(protSelector('#" . $tableId . "')).jqGrid('setGroupHeaders', { useColSpanStyle: false, groupHeaders:" . $groupHeaders . " });";

        self::codice($js);
    }

    /**
     * Render componente Accordion
     * 
     * @param string $dest Nome finestra di destinazione
     * @param string $containerName Nome container dove iniettare la finestra
     * @param string $componentName Nome componente
     * @param array $data Struttura dati con cui valorizzare l'accordion
     * @param array $class Classi da applicare all'elemento (per metadata)
     */
    static function accordion($dest, $containerName, $componentName, $data, $class = '') {
        $componentId = $dest . '_' . $componentName;
        $html = '<div class="ita-accordion ' . $class . '" id="' . $componentId . '">';

        // Scorre le sezioni
        foreach ($data['sections'] as $section) {
            // Per ogni sezione, aggiunge le voci
            $html .= '<div class="ita-tabpane" title="' . $section['name'] . '">';
            foreach ($section['items'] as $item) {
                $toolt = htmlspecialchars($item['name']);
                if (isset($item['tooltip'])) {
                    $toolt = $item['tooltip'] ?: '';
                }

                $html .= '<p><a id="' . $item['id'] . '" href="#" title="' . $toolt . '" class="ita-hyperlink' . ($toolt ? ' ita-tooltip' : '') . '">' . $item['name'] . '</a></p><br/>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        self::html($dest . "_" . $containerName, $html);
    }

    /**
     * Apre l'accordion alla tab indicata.
     * 
     * @param type $id Id dell'accordion
     * @param type $tabIndex Index della tab
     */
    static function openAccordion($id, $tabIndex) {
        self::codice("$('#' + protSelector('$id')).accordion('option', 'active', $tabIndex);");
    }

    /**
     * Gestisce la validazione degli input
     * 
     * @param string  $field_id ID del campo
     * @param boolean $required Determina se impostare il campo come
     *                          obbligatorio (true) o meno (false)
     * @param boolean $validate Determina se abilitare la validazione lato
     *                          client (true) o meno (false)
     */
    public static function required($field_id, $required = true, $validate = true) {
        self::$contenuto[] = array(
            'tag' => 'required',
            'attributi' => array(
                'id' => $field_id,
                'required' => $required,
                'validate' => $validate
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Rende un campo 'required'.
     * 
     * @param string  $field_id ID del campo
     */
    public static function setRequired($field_id) {
        self::required($field_id);
    }

    /**
     * Rende un campo non 'required'.
     * 
     * @param string  $field_id ID del campo
     */
    public static function unsetRequired($field_id) {
        self::required($field_id, false, false);
    }

    /**
     * aggiunge il breadcrumb
     */
    public static function breadcrumb($dest, $containerName, $componentName, $data) {
        $componentId = $dest . '_' . $componentName;

        $tabs = '';

        foreach ($data as $key => $value) {
            $onclickFn = '';
            if ($value['componentCell']) {
                $onclickFn = 'onclick="'
                    . "itaGo('ItaCall', this, 
                            {
                                id:'" . $componentId . "',
                                cell:'" . $value['componentCell'] . "',
                                event:'onClick',
                                model:'" . $dest . "'                  
                            })"
                    . '"';
            }

            $tabs = $tabs .
                '<li class="' . ($value['current'] ? 'current' : '') . '">'
                . '<a href="#" ' . $onclickFn . ' >' . $value['label'] . '</a>'
                . '</li>';
        }

        $html = ' <ul id="' . $componentId . '" class="xbreadcrumbs">  ' . $tabs . ' </ul>';

        self::html($dest . "_" . $containerName, $html);
    }

    public function codeEditorMode($id, $ext) {
        switch (strtolower($ext)) {
            default:
                $mode = strtolower($ext);
                break;

            case 'ini':
                $mode = 'properties';
                break;
        }

        self::codice('if ( window.codeMirrors["' . $id . '"] ) window.codeMirrors["' . $id . '"].setOption("mode", "' . $mode . '");');
    }

    /**
     * Aggiunge un timer che scatena un evento 'ontimer'
     * @param string $container id dell'elemento html a cui abbinare il timer
     * @param int $delay delay in secondi a cui parte il timer
     * @param string $model (facoltativo) modello che il timer andrà a richiamare
     * @param boolean $serializeForm indica se allo scatenarsi dell'evento viene passata anche la form abbinata
     * @param boolean $backgroundTick indica se il timer continua a funzionare quando l'elemento a lui abbinato perde il focus
     */
    public static function addTimer($container, $delay = 60, $model = null, $serializeForm = false, $backgroundTick = true) {
        $parameters = new stdClass();
        $parameters->element = $container;
        $parameters->delay = $delay * 1000;
        if (isSet($model)) {
            $parameters->model = $model;
        }
        $parameters->serializeForm = $serializeForm;
        $parameters->backgroundTick = $backgroundTick;

        self::codice('setItaTimer(' . json_encode($parameters) . ')');
    }

    /**
     * Rimuove un timer inserito dalla funzione addTimer
     * @param string $container id dell'elemento html a cui è stato abbinato il timer da rimuovere
     */
    public static function removeTimer($container) {
        self::codice('removeTimer(\'' . $container . '\')');
    }

    public static function msgInputText($formName, $titolo, $messaggio, $inputName = 'modal_inputText', $height = 'auto', $width = 'auto', $closeButton = 'false', $noRound = false, $closeAuto = true, $verticalButtons = false, $modal = 'true', $eventPrefix = null) {
        if (!isSet($eventPrefix)) {
            $eventPrefix = $formName;
        }
        $messaggio .= "<hr>"
            . "<div style='text-align: center; padding-top: 20px'>"
            . "  <input style='width:70%; min-width:400px;' type='text' id='" . $formName . "_" . $inputName . "' name='" . $formName . "_" . $inputName . "' class='ita-edit ui-widget-content ui-corner-all'>"
            . "</div>";
        $bottoni = array(
            'Annulla' => array('id' => $eventPrefix . '_modal_annulla', 'model' => $formName, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover'),
            'Conferma' => array('id' => $eventPrefix . '_modal_conferma', 'model' => $formName, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover')
        );

        self::msgQuestion($titolo, $messaggio, $bottoni, $height, $width, $closeButton, $noRound, $closeAuto, $verticalButtons, 'ItaForm', $modal, $formName);
    }

    /**
     * Nasconde tutti gli elementi che hanno una data classe all'interno di un dato container;
     * @param string $container
     * @param string $class
     */
    public static function hideElementFromClass($container, $class) {
        self::codice("$('#$container').find('.$class').hide();");
    }

    /**
     * Mostra tutti gli elementi che hanno una data classe all'interno di un dato container;
     * @param string $container
     * @param string $class
     */
    public static function showElementFromClass($container, $class) {
        self::codice("$('#$container').find('.$class').show();");
    }

    /**
     * Disabilita tutti gli elementi che hanno una data classe all'interno di un dato container;
     * @param string $container
     * @param string $class
     */
    public static function disableElementFromClass($container, $class) {
        self::codice("$('#$container').find('.$class').prop('disabled', true);");
    }

    /**
     * Abilita tutti gli elementi che hanno una data classe all'interno di un dato container;
     * @param string $container
     * @param string $class
     */
    public static function enableElementFromClass($container, $class) {
        self::codice("$('#$container').find('.$class').prop('disabled', false);");
    }

    public static function setGridHeight($gridId, $height) {
        self::codice("$('#" . $gridId . "').jqGrid('setGridHeight','" . $height . "px')");
    }

    public static function setGridWidth($gridId, $width) {
        self::codice("$('#" . $gridId . "').jqGrid('setGridWidth','" . $width . "')");
    }

    public static function collapseGridRowExpand($gridId, $rowId) {
        self::codice('$("#' . $gridId . '").collapseSubGridRow(' . $rowId . ')');
    }

    /**
     * Cambia il testo della label di un campo
     * @param <string> $fieldID id del campo di cui si vuole modificare la label
     * @param <string> $label testo della label
     */
    public static function setLabel($fieldID, $label) {
        self::html($fieldID . '_lbl', $label . '<span id="' . $fieldID . '_lbl_required" style="display: inline-block; color: red; font-weight: bold; width: 6px; font-family: sans-serif;"></span>');
    }

    /**
     * Imposta il testo di uno span come se fosse una label
     * @param <string> $fieldID id dello span
     * @param <string> $label testo da inserire nello span
     */
    public static function setSpanAsLabel($fieldID, $label) {
        self::html($fieldID, $label . '<span id="' . $fieldID . '_lbl_required" style="display: inline-block; color: red; font-weight: bold; width: 6px; font-family: sans-serif;"></span>');
    }

    public static function hideTab($idPane) {
        self::$contenuto[] = array(
            'tag' => 'tabpane',
            'attributi' => array(
                'id' => $idPane,
                'comando' => 'hide'
            ),
            'nodevalue' => ''
        );
    }

    public static function showTab($idPane) {
        self::$contenuto[] = array(
            'tag' => 'tabpane',
            'attributi' => array(
                'id' => $idPane,
                'comando' => 'show'
            ),
            'nodevalue' => ''
        );
    }

    /**
     * Setta il titolo di una colonna su una griglia
     * @param <string> $nameForm nome della form
     * @param <string> $gridName nome della griglia
     * @param <string> $column nome della colonna
     * @param <string> $value titolo da assegnare alla colonna
     * @param <string> $tooltip (facoltativo) tooltip da assegnare all'header
     */
    public static function gridSetColumnHeader($nameForm, $gridName, $column, $value, $tooltip=null){
        $value = addcslashes(str_replace(array("\r\n", "\r", "\n"), array('\n','\n', '\n'), $value), "'");
        $codice = "$('#jqgh_{$nameForm}_{$gridName}_{$column}').html('{$value}'+$('#jqgh_{$nameForm}_{$gridName}_{$column} .s-ico')[0].outerHTML);";
        self::codice($codice);
        
        if(!empty($tooltip)){
            self::attributo($nameForm.'_'.$gridName.'_'.$column, 'title', '0', $tooltip);
        }
    }
    
    /**
     * Setta l'html di un filtro per una determinata colonna di una jqgrid (solo per colonne che hanno già un filtro impostato)
     * @param <string> $nameForm nome della form
     * @param <string> $gridName nome della griglia
     * @param <string> $column nome della colonna
     * @param <string> $html html da iniettare al posto del filtro della colonna
     * @param <string> $append false=sostituisce il testo, 'append'=fa l'append, 'prepend'=fa il prepend
     */
    public static function gridSetColumnFilterHtml($nameForm, $gridName, $column, $html, $append=false){
        if(!in_array($append, array('append', 'prepend'))){
            $append = 'html';
        }
        
        $codice = " if($('#jqgh_{$nameForm}_{$gridName}_{$column}').length){
                        var index = $('#{$nameForm}_{$gridName}_{$column}').index();
                        var outParent = $('#gbox_{$nameForm}_{$gridName} .ui-search-toolbar .ui-th-ltr').eq(index).children('div');
			
                        outParent.".$append."('".str_replace(array("'", "\r\n", "\n"), array("\'", "", ""), $html)."');
                        outParent.find(':input').change(function(){
                            itaGo('ItaForm', this, { event: 'onClickTablePager', model: '".$nameForm."', id: '".$nameForm."_".$gridName."', validate: false});
                        });
                    }";

//        $codice = " var outParent = $('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').parent();
//                    outParent.html('".str_replace("'", "\'", $html)."');
//                    outParent.find(':input').change(function(){
//                        itaGo('ItaForm', this, { event: 'onClickTablePager', model: '".$nameForm."', id: '".$nameForm."_".$gridName."', validate: false});
//                    });";
        self::codice($codice);
    }
    
    public static function gridSetColumnWidth($nameForm, $gridName, $column, $width){
        $codice = " if($('#jqgh_{$nameForm}_{$gridName}_{$column}').length){
                        var index = $('#{$nameForm}_{$gridName}_{$column}').index();
                        
                        $('#gbox_{$nameForm}_{$gridName} .ui-jqgrid-labels > th:eq('+index+')').css('width','".$width."px')
                        $('#gbox_{$nameForm}_{$gridName} tr').find('td:eq('+index+')').each(function(){
                            $(this).css('width','".$width."px');
                        });
                    }";
        self::codice($codice);
    }
    
    public static function getOnChangeEventJqGridFilter($nameForm, $gridName){
        return "onChange=\"itaGo('ItaForm', this, { event: 'onClickTablePager', model: '".$nameForm."', id: '".$nameForm."_".$gridName."', validate: false});\"";
    }
    
    public static function gridSetColumnFilterSelect($nameForm, $gridName, $column, $options){
        $html = '<select id="gs_'.$column.'" name="' . $column . '" style="width:100%" '.self::getOnChangeEventJqGridFilter($nameForm, $gridName).'></select>';
        self::gridSetColumnFilterHtml($nameForm, $gridName, $column, $html);
        
        $codice = '';
        foreach($options as $k=>$v){
            $codice .= "$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').append('<option value=\"{$k}\">{$v}</option>');";
        }
        self::codice($codice);
    }
    
    /**
     * Setta il valore di un filtro per una determinata colonna di una jqgrid
     * @param <string> $nameForm nome della form
     * @param <string> $gridName nome della griglia
     * @param <string> $column nome della colonna
     * @param <string> $value valore da assegnare al filtro
     */
    public static function gridSetColumnFilterValue($nameForm, $gridName, $column, $value){
        $codice = "$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').val('{$value}');";
        self::codice($codice);
    }
    
    /**
     * Azzera tutti i filtri dell'header di una griglia o quello di una specifica colonna
     * @param <string> $nameForm nome della form
     * @param <string> $gridName nome della griglia
     * @param <string> $column (facoltativo) nome della colonna (se si vuole azzerare una sola colonna)
     */
    public static function gridCleanFilters($nameForm, $gridName, $column=null){
        if(!empty($column)){
            $codice = "$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').val('');";
        }
        else{
            $codice = "$('#gbox_{$nameForm}_{$gridName} .ui-th-column').find(':input').val('');";
        }
        
        self::codice($codice);
    }
    
    public static function gridSetPage($nameForm, $gridName, $page=1){
        self::codice("$('#{$nameForm}_{$gridName}').trigger('reloadGrid', [{page:{$page}}]);");
    }
    
    /**
     * Permette di impostare il tooltip mostrato quando si va in hover con il mouse su un dato elemento
     * @param <string> $id id dell'elemento a cui settare il tooltip
     * @param <string> $tooltip tooltip da settare. se vuoto annulla il tooltip rimettendo quello di default
     */
    public static function setTooltip($id, $tooltip=null){
        if(empty($tooltip)){
            self::attributo($id, 'title', 1);
        }
        else{
            self::attributo($id, 'title', 0, $tooltip);
        }
    }

    public static function enableButton($id) {
        self::attributo($id, 'disabled', 1);
        self::delClass($id, 'ui-state-disabled');
    }

    public static function disableButton($id) {
        self::attributo($id, 'disabled', 0, 'disabled');
        self::addClass($id, 'ui-state-disabled');
    }

    public static function saveCanvasAsImage($idCanvas, $filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $codice = "var saveCanvasAsImage = function(blob) { var a = document.createElement('a'); a.href = window.navigator && window.navigator.msSaveOrOpenBlob ? window.navigator.msSaveOrOpenBlob(blob, '$filename') : window.URL.createObjectURL(blob); a.download = '$filename'; document.body.appendChild(a); a.click(); document.body.removeChild(a); };";
        $codice .= " var imageExtension = 'image/$extension';";
        $codice .= " var canvas = $('#$idCanvas').get(0);";
        $codice .= " if ( canvas.msToBlob ) { var blob = canvas.msToBlob(); saveCanvasAsImage(blob); } else { canvas.toBlob(saveCanvasAsImage, imageExtension); }";
        self::codice($codice);
    }

    public static function recursiveValori($valori, $nameForm){
        foreach($valori as $k=>$v){
            self::recursiveAssegnaValore($v, $nameForm . '_' . $k);
        }
    }
    
    private static function recursiveAssegnaValore($valore, $root){
        if(is_array($valore)){
            foreach($valore as $k=>$v){
                self::recursiveAssegnaValore($v, $root . '[' . $k . ']');
            }
        }
        else{
            self::valore($root, $valore);
        }
    }

    /**
     * Collassa un elemento ita-box con '{ collapse: true }'.
     * @param type $idBox
     */
    public static function boxCollapse($idBox) {
        self::codice("$(protSelector('#{$idBox} .ita-portlet-plus.ui-icon-minusthick')).click();");
        self::codice('resizeTabs();');
    }

    /**
     * Espande un elemento ita-box con '{ collapse: true }'.
     * @param type $idBox
     */
    public static function boxExpand($idBox) {
        self::codice("$(protSelector('#{$idBox} .ita-portlet-plus.ui-icon-plusthick')).click();");
        self::codice('resizeTabs();');
    }

    /**
     * Inverte lo stato di un elemento ita-box con '{ collapse: true }'.
     * @param type $idBox
     */
    public static function boxToggle($idBox) {
        self::codice("$(protSelector('#{$idBox} .ita-portlet-plus')).click();");
    }

    /**
     * Ordina gli elementi di un gruppo 'ita-sortable'.
     * @param type $sortedIds Stringa contenente gli id degli elementi nell'ordine
     * desiderato. (stringa ritornata dal metodo 'sortStop')
     */
    public static function setSortableOrder($sortedIds) {
        $ids = explode(',', $sortedIds);

        foreach ($ids as $id) {
            self::codice("var parent = $(protSelector('#{$id}')).parent(); $(protSelector('#{$id}')).detach().appendTo(parent);");
        }
    }
    
    /**
     * Imposta il tooltip in un campo d'input con data-ita-tooltip.
     * Se il testo è vuoto, l'icona info viene nascosta.
     * 
     * @param type $id ID del campo d'input.
     * @param type $text Testo del tooltip.
     */
    public static function setInputTooltip($id, $text = '') {
        $text = addslashes($text);
        self::codice("itaFieldUtilities.inputTooltip('$id', '$text');");
    }
    
    /**
     * Setta/rimuove un attributo di un campo di input di un filtro per una determinata colonna di una jqgrid
     * @param <string> $nameForm nome della form
     * @param <string> $gridName nome della griglia
     * @param <string> $column nome della colonna
     * @param <string> $attributo nome dell'attributo da aggiungere/rimuovere
     * @param <boolean> $del se false viene aggiunto l'attributo, se true viene rimosso
     * @param <string> $value valore dell'attributo
     */
    public static function gridSetColumnFilterAttribute($nameForm, $gridName, $column, $attributo, $del=false, $value=null){
        if(!$del){
            $codice = "$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').attr('{$attributo}','".str_replace(array("'", "\r\n", "\n"), array("\'", "", ""), $value)."');";
        }
        else{
            $codice = "$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').removeAttr('{$attributo}');";
        }
        self::codice($codice);
    }
    
    public static function gridSetColumnFilterClass($nameForm, $gridName, $column, $class){
        self::gridSetColumnFilterAttribute($nameForm, $gridName, $column, 'class', true);
        self::gridSetColumnFilterAttribute($nameForm, $gridName, $column, 'class', false, $class);
        self::codice("parseHtmlContainer($('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').parent());");
        self::codice("$('#gbox_{$nameForm}_{$gridName} .ui-th-column #gs_{$column}').change(function(){
                            itaGo('ItaForm', this, { event: 'onClickTablePager', model: '".$nameForm."', id: '".$nameForm."_".$gridName."', validate: false});
                        });");
    }

    /**
     * Attiva il parser html di itaEngine su un dato elemento
     * @param string $container
     */
    public static function attivaJSElemento($container) {
        self::codice("parseHtmlContainer($('#" . $container . "'));");
    }

}