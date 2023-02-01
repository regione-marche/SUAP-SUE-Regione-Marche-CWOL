<?php

require_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewer.class.php';

class itaDocViewerBootstrap {
    const DOCVIEWER_TAB = 0;
    const DOCVIEWER_MODAL = 1;
    const DOCVIEWER_INNER = 2;
    const DOCVIEWER_INNER_COMPONENT = 3; //ATTENZIONE: l'elemento contenitore del docViewer deve avere position: relative/absolute;

    private $files = array();

    public function addFile($filepath, $filename = null) {
        if (!file_exists($filepath) || !is_file($filepath)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Il file $filepath non esiste");
        }
        if (!isSet($filename)) {
            $filename = basename($filepath);
        }

        $this->files[] = array('FileName' => $filename, 'FilePath' => $filepath);
    }

    public function openViewer($mode = DOCVIEWER_TAB, $deleteOnClose = false, $showButtonBar = true, $nameForm = '', $container = '') {
        if (empty($this->files)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Si sta cercando di aprire un visualizzatore senza alcun file al suo interno");
        }
        $alias = 'utiDocViewer' . time();

        switch ($mode) {
            case self::DOCVIEWER_MODAL:
                itaLib::openDialog('utiDocViewer', '', true, 'desktopBody', '', '', $alias);
                $this->instanceUtiDocViewer($alias, $deleteOnClose, $showButtonBar, $mode);
                break;
            case self::DOCVIEWER_INNER:
                $docViewer = new itaDocViewer($nameForm, null, $nameForm . '_' . $container);
                $docViewer->setFiles($this->files);
                $docViewer->previewFile(0);
                break;
            case self::DOCVIEWER_INNER_COMPONENT:
                itaLib::openInner('utiDocViewerComponent', '', true, $nameForm . '_' .$container, '', '', $alias);
                $this->instanceUtiDocViewer($alias, $deleteOnClose, $showButtonBar, $mode);
                break;
            case self::DOCVIEWER_TAB:
            default:
                itaLib::openApp('utiDocViewer', true, true, 'desktopBody', "", '', $alias);
                $this->instanceUtiDocViewer($alias, $deleteOnClose, $showButtonBar, $mode);
                break;
        }
    }

    private function instanceUtiDocViewer($alias, $deleteOnClose, $showButtonBar, $mode) {
        $objModel = itaFrontController::getInstance('utiDocViewer', $alias);
        $objModel->setFiles($this->files);
        $objModel->setDeleteOnClose($deleteOnClose);
        $objModel->setShowButtonBar($showButtonBar);
        $objModel->setMode($mode);
        $objModel->setEvent('openform');
        $objModel->parseEvent();
    }

}

?>