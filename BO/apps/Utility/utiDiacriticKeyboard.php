<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaDiacriticChars.class.php';

function utiDiacriticKeyboard() {
    $utiDiacriticKeyboard = new utiDiacriticKeyboard();
    $utiDiacriticKeyboard->parseEvent();
    return;
}

class utiDiacriticKeyboard extends itaModel {

    public $nameForm = 'utiDiacriticKeyboard';
    private $lastChar;
    private $returnInput;

    public function __construct() {
        parent::__construct();
        $this->lastChar = App::$utente->getKey($this->nameForm . '_lastChar');
        $this->returnInput = App::$utente->getKey($this->nameForm . '_returnInput');
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_lastChar', $this->lastChar);
            App::$utente->setKey($this->nameForm . '_returnInput', $this->returnInput);
        }
    }

    public function setStartValue($startValue) {
        $this->startValue = $startValue;
    }

    public function setReturnInput($returnInput) {
        $this->returnInput = $returnInput;
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_lastLetter');
        App::$utente->removeKey($this->nameForm . '_returnInput');

        if ($this->returnInput) {
            $char = itaDiacriticChars::HTML2Unicode($this->returnInputValue);
            Out::codice("document.getElementById('$this->returnInput').value = unescape('$char');");
        } else if ($this->returnModel) {
            $_POST['returnValue'] = $this->returnInputValue;
            $returnModel = itaModel::getInstance($this->returnModel);
            $returnModel->setEvent($this->returnEvent ?: 'returnDiacriticKeyboard');
            $returnModel->parseEvent();
        }

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':
                Out::css($this->nameForm, 'overflow', 'initial');
                Out::css($this->nameForm . '_wrapper', 'overflow', 'initial');
                Out::css($this->nameForm . '_workSpace', 'overflow', 'initial');
                Out::css($this->nameForm . '_inputPreview_field', 'width', '98%');

                $html = '';
                foreach (itaDiacriticChars::getChars() as $char) {
                    $r = $this->nameForm . '_' . trim($char['code'], '&#;');
                    $html .= '<button id="' . $r . '" name="' . $r . '" value="' . $char['code'] . '" style="padding: 10px; width: 36px; height: 42px;" type="button" class="ita-button ita-element-animate ita-tooltip" title="' . $char['description'] . '"></button>';
                }
                Out::html($this->nameForm . '_divDiacriticChars', $html);

                if ($this->startValue) {
                    Out::codice("document.getElementById('{$this->nameForm}_inputPreview').value = unescape('$this->startValue');");
                }

                Out::setFocus($this->nameForm, $this->nameForm . '_inputPreview');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Inserisci':
                        $this->returnInputValue = $_POST[$this->nameForm . '_inputPreview'];
                        $this->returnToParent();
                        break;
                }

                $btnId = $_POST['id'];
                list(, $btnName) = explode('_', $btnId, 2);

                if (is_numeric($btnName)) {
                    $diacriticChar = itaDiacriticChars::HTML2Unicode('&#' . $btnName . ';');

                    $codice = <<<SCRIPT
var el = document.getElementById('{$this->nameForm}_inputPreview'),
    sel = $(el).getSelection().start + 1,
    str = unescape('$diacriticChar');

$(el).replaceSelection(str, true);

if (el.setSelectionRange) {
    el.focus(); el.setSelectionRange(sel, sel);
} else if (el.createTextRange) {
    var range = el.createTextRange();
    range.collapse(true);
    range.moveEnd('character', sel);
    range.moveStart('character', sel);
    range.select();
} else if (el.selectionStart) {
    el.selectionStart = sel;
    el.selectionEnd = sel;
}
SCRIPT;

                    Out::codice($codice);

                    Out::setFocus($this->nameForm, $this->nameForm . '_inputPreview');
                } else if (strlen($btnName) === 1) {
                    $divId = $this->nameForm . '_divDiacriticChars';
                    $isDiff = (int) ($this->lastChar !== $btnId);

                    $chars = itaDiacriticChars::getCharsByTransliteration();
                    Out::hideElementFromClass($this->nameForm . '_divDiacriticChars', 'ita-button');
                    foreach ($chars[substr($btnId, -1)] as $char) {
                        Out::show($this->nameForm . '_' . trim($char['code'], '&#;'));
                    }

                    $codice = <<<SCRIPT
jDiv = $('#$divId'), jBtn = $('#$btnId');

if ( $isDiff || jDiv.is(':hidden') ) {
    jDiv.show().position({
        of: $( "#$btnId" ),
        my: 'center bottom',
        at: 'center top-5',
        collision: 'fit'
    });
} else {
    jDiv.hide();
}
SCRIPT;

                    Out::codice($codice);
                    $this->lastChar = $btnId;
                }
                break;
        }
    }

}
