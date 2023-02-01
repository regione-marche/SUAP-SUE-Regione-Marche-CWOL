<?php

/**
 *
 * Gestione Composizione Atti
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Segreteria
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    03.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proTestiBase() {
    $proTestiBase = new proTestiBase();
    $proTestiBase->parseEvent();
    return;
}

class proTestiBase extends itaModel {

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openDocumento();
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    function openDocumento() {
        $FixedFields['CLASSIFICAZIONE'] = 'PROTOCOLLO'; //CLASSIFICAZIONE 
        $FixedFields['FUNZIONE'] = array('TESTIBASE');
        //  Apro Form documenti
        // 
        $_POST = array();
        $_POST['FixedFields'] = $FixedFields;
        $_POST['TipoAperturaDocumento'] = 'PROTOCOLLO';
        $_POST['classificazione'] = 'PROTOCOLLO';
        $model = 'docDocumenti';
        itaLib::openForm($model);
        /* @var $objForm docDocumenti */
        $objForm = itaModel::getInstance($model);
        $objForm->setEvent('OpenFixField');
        $objForm->parseEvent();
        $objForm->OpenRicerca();
    }

}

?>
