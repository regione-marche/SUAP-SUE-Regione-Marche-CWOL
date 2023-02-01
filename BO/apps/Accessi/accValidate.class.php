<?php

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class accValidate {

    const outerContainer = 'accValidateController';

    public $private = false;
    public $nameForm = 'accValidate';
    public $headerImage = true;
    public $leftForm = true;
    public $rightForm = true;
    public $loginForm = 'accLogin2015';
    public $bottomForm = 'accBottomBar';
    public $cssFiles = 'accValidate.css';

    public static function getInstance($itaLoginForm) {
        $file = App::getAppFolder($itaLoginForm) . '/' . $itaLoginForm . '.class.php';

        if (file_exists($file)) {
            include_once $file;
        } else {
            Out::msgStop("Errore", "Impossibile caricare la form di login");
            return false;
        }

        try {
            Out::addContainer('', self::outerContainer);
            Out::attributo(self::outerContainer, 'style', 0, 'height: 100%;');
            Out::attributo(self::outerContainer, 'class', 0, 'ita-model');
            if (App::$clientEngine == 'itaMobile') {
                Out::attributo(self::outerContainer, 'data-role', 0, 'page');
            }
            $advancedLogin = new $itaLoginForm();
            return $advancedLogin;
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }
    }

    public static function destroy() {
        Out::removeElement(self::outerContainer);
    }

    public function create() {
        $accLib = new accLib();
        $generator = new itaGenerator();

        $formBase = $generator->getModelHTML($this->nameForm, false, '', true);
        Out::html(self::outerContainer, $formBase);

        $pathHeader = $accLib->getResourcePath($this->nameForm . '_header.png');
        $pathLeftColumn = $accLib->getResourcePath($this->nameForm . '_leftColumn.html');
        $pathRightColumn = $accLib->getResourcePath($this->nameForm . '_rightColumn.html');

        if (file_exists($pathHeader) && $this->headerImage) {
            include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
            Out::html($this->nameForm . '_headerForm', '<img src="' . itaImg::base64src($pathHeader) . '" alt="" />');
        }

        if (file_exists($pathLeftColumn) && $this->leftForm) {
            Out::html($this->nameForm . '_leftForm', file_get_contents($pathLeftColumn));
        }

        if (file_exists($pathRightColumn) && $this->rightForm) {
            Out::html($this->nameForm . '_rightForm', file_get_contents($pathRightColumn));
        }

        if ($this->loginForm) {
            itaLib::openForm($this->loginForm, false, true, $this->nameForm . '_loginForm');
            $modelObj = itaModel::getInstance($this->loginForm);
            $modelObj->setEvent('openform');
            $modelObj->parseEvent();
        }

        if ($this->bottomForm) {
            itaLib::openForm($this->bottomForm, false, true, $this->nameForm . '_bottomForm');
            $modelObj = itaModel::getInstance($this->bottomForm);
            $modelObj->setEvent('openform');
            $modelObj->parseEvent();
        }

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
