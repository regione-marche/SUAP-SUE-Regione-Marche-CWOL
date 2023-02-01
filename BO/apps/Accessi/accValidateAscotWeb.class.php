<?php

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class accValidateAscotWeb {

    public $nameForm = 'accValidateAscotWeb';
    public $loginForm = 'accLogin2015';
    public $bottomForm = 'accBottomBar';
    public $cssFiles = 'accValidateAscotWeb.css';

    public function create() {
        $accLib = new accLib();
        $generator = new itaGenerator();

        $formBase = $generator->getModelHTML($this->nameForm, false, '', true);
        Out::html(accValidate::outerContainer, $formBase);

        $pathHeader = $accLib->getResourcePath($this->nameForm . '_header.html');
        $pathLeftColumn = $accLib->getResourcePath($this->nameForm . '_leftColumn.html');
        $pathRightColumn = $accLib->getResourcePath($this->nameForm . '_rightColumn.html');

        if (file_exists($pathHeader)) {
            Out::html($this->nameForm . '_headerForm', file_get_contents($pathHeader));
        }

        if (file_exists($pathLeftColumn)) {
            Out::html($this->nameForm . '_leftForm', file_get_contents($pathLeftColumn));
        }

        if (file_exists($pathRightColumn)) {
            Out::html($this->nameForm . '_rightForm', file_get_contents($pathRightColumn));
        }

        if ($this->loginForm) {
            itaLib::openForm($this->loginForm, false, true, $this->nameForm . '_loginForm');
            $modelObj = itaModel::getInstance($this->loginForm);
            $modelObj->setEvent('openform');
            $modelObj->parseEvent();
        }

//        if ($this->bottomForm) {
//            itaLib::openForm($this->bottomForm, false, true, $this->nameForm . '_bottomForm');
//            $modelObj = itaModel::getInstance($this->bottomForm);
//            $modelObj->setEvent('openform');
//            $modelObj->parseEvent();
//        }

        if ($this->cssFiles) {
            if (is_array($this->cssFiles)) {
                $css = '';
                foreach ($this->cssFiles as $cssFile) {
                    $css .= ' ' . file_get_contents($accLib->getResourcePath($cssFile));
                }
            } else {
                $css = file_get_contents($accLib->getResourcePath($this->cssFiles));
            }
            $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', addslashes($css));
            Out::codice("$('html head').append(\"<style>$css</style>\");");
        }
    }

}
