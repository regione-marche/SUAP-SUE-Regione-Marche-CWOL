<?php

/**
 *  Gestione Menu di lancio Models ItaEngine
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Marco Camilletti
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    20.12.2011
 * @link
 * @see
 * @since
 * @deprecated
 */
function devTestProg() {
    $devTestProg = new devTestProg();
    $devTestProg->parseEvent();
    return;
}

//include_once './apps/Utility/utiEnte.class.php';

class devTestProg extends itaModel {

    public $nameForm = "devTestProg";

//    public $ITALSOFT_DB;
//    public $elenco;
//    public $utiEnte;
//    public $me_id;

    function __construct() {
        parent::__construct();
//        try {
//            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
//        } catch (Exception $e) {
//            Out::msgStop("Errore", $e->getMessage());
//        }
//        $this->utiEnte = new utiEnte();
//        $this->utiEnte->getITALWEB_DB();
//        $this->elenco = App::$utente->getKey($this->nameForm . '_elenco');
//        $this->me_id = App::$utente->getKey($this->nameForm . '_me_id');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
//            App::$utente->setKey($this->nameForm . '_elenco', $this->elenco);
//            App::$utente->setKey($this->nameForm . '_me_id', $this->me_id);
        }
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::valore($this->nameForm . '_Evento', 'openform');
                Out::setFocus('', $this->nameForm . '_Programma');
//                Out::valore($this->nameForm . '_Percorso', App::getConf('modelBackEnd.php').'/Interni');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Esegui':
                    case $this->nameForm . '_EseguiSenza':
                        $postDati = $_POST[$this->nameForm . '_PostDati'];
                        $postEsploso = explode('&', $postDati);
                        $postComposto = array();
                        foreach ($postEsploso as $key => $elemento) {
                            $elementoEsploso = explode('=', $elemento);
                            if (count($elementoEsploso) > 1) {
                                $postComposto[$elementoEsploso[0]] = $elementoEsploso[1];
                            }
                        }
                        $model = $_POST[$this->nameForm . '_Programma'];
                        if ($model == '') {
                            break;
                        }
                        $evento = $_POST[$this->nameForm . '_Evento'];
                        if ($_POST['id'] != $this->nameForm . '_EseguiSenza') {
                            Out::closeDialog($this->nameForm);
                        }
                        
                        $_POST = array();
                        $_POST = $postComposto;
                        itaLib::openForm($model);
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setEvent($evento);
                        $modelObj->parseEvent();
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    /**
     *  Apre la Form
     */
//    public function OpenForm() {
//        Out::show($this->nameForm);
//    }
}

?>
