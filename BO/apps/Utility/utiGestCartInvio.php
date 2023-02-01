<?php

/**
 *
 * Form controllo invio mail
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    10.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCart.class.php';

function utiGestCartInvio() {
    $utiGestCartInvio = new utiGestCartInvio();
    $utiGestCartInvio->parseEvent();
    return;
}

class utiGestCartInvio extends itaModel {

    public $nameForm = "utiGestCartInvio";
    public $gridAllegati = "utiGestCartInvio_gridAllegati";
    public $gridDestinatari = "utiGestCartInvio_gridDestinatari";
    public $tipo;
    public $praLib;
    public $allegati;
    public $returnModel;
    public $returnEvent;
    public $valori;
    public $sizeAllegati;
    public $returnEventOnClose;
    public $destinatari = array();
    public $obbligoInvioMail;
    public $stimoli = array();

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        try {
            // DATI SALVATI IN SESSION //
            $this->tipo = App::$utente->getKey($this->nameForm . "_tipo");
            $this->allegati = App::$utente->getKey($this->nameForm . "_allegati");
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->valori = App::$utente->getKey($this->nameForm . "_valori");
            $this->sizeAllegati = App::$utente->getKey($this->nameForm . "_sizeAllegati");
            $this->returnEventOnClose = App::$utente->getKey($this->nameForm . "_returnEventOnClose");
            $this->destinatari = App::$utente->getKey($this->nameForm . "_destinatari");
            $this->stimoli = App::$utente->getKey($this->nameForm . "_stimoli");
            $this->obbligoInvioMail = App::$utente->getKey($this->nameForm . "_obbligoInvioMail");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function getObbligoInvioMail() {
        return $this->obbligoInvioMail;
    }

    public function setObbligoInvioMail($obbligoInvioMail) {
        $this->obbligoInvioMail = $obbligoInvioMail;
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_tipo", $this->tipo);
            App::$utente->setKey($this->nameForm . "_allegati", $this->allegati);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_valori", $this->valori);
            App::$utente->setKey($this->nameForm . "_sizeAllegati", $this->sizeAllegati);
            App::$utente->setKey($this->nameForm . "_returnEventOnClose", $this->returnEventOnClose);
            App::$utente->setKey($this->nameForm . "_destinatari", $this->destinatari);
            App::$utente->setKey($this->nameForm . "_stimoli", $this->stimoli);
            App::$utente->setKey($this->nameForm . "_obbligoInvioMail", $this->obbligoInvioMail);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->valori = $_POST['valori'];
                $this->sizeAllegati = $_POST['sizeAllegati'];
//                $this->returnEventOnClose = $_POST['returnEventOnClose'];
                $this->tipo = $_POST['tipo'];
                $this->allegati = $_POST['allegati'];
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->destinatari = $_POST['valori']['Destinatari'];
                $this->stimoli = $_POST['valori']['Stimoli'];
                
                
//                $this->obbligoInvioMail = $_POST['obbligoInvioMail'];
                Out::show($this->nameForm);
                $this->Dettaglio();
                
                
                break;
            case 'delGridRow':
                if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                    unset($this->allegati[$_POST['rowid']]);
                }
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        //$this->CaricaGriglia($this->gridAllegati, $this->allegati, '2');
                        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $this->allegati[$_POST['rowid']]['FILEORIG'], $this->allegati[$_POST['rowid']]['FILEPATH']
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stimolo':

                         $this->attivaDettaglioStimolo($_POST[$this->nameForm . '_Stimolo'] );
                        
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        if (!$this->checkDestinatari()) {
                            break;
                        }
                        
                        $tipoStimolo = $_POST[$this->nameForm . '_Stimolo'];

//                        Out::msgInfo("", $tipoStimolo);
                        $destinatari = $_POST[$this->gridDestinatari]['gridParam']['selarrrow'];
                        $_POST['valori']['Destinatari'] = $destinatari;
                        $_POST['valori']['DestinatariOriginari'] = $this->destinatari;
                        $_POST['valori']['TIPOSTIMOLO'] = $tipoStimolo;
                        $_POST['valori']['GIORNIRISPOSTA'] = 0;
                        switch ($tipoStimolo) {
                            case "comunicazione":
                                $_POST['valori']['OPERATION'] = 'inviaStimoloRequest';
                                $_POST['valori']['OGGETTO'] = $_POST[$this->nameForm . '_Oggetto'];
                                $_POST['valori']['MESSAGGIO'] = $_POST[$this->nameForm . '_Corpo'];
                                $_POST['valori']['ATTESA_GG'] = $_POST[$this->nameForm . '_AttesaGg'];
                                $_POST['valori']['GIORNIRISPOSTA'] = $_POST[$this->nameForm . '_AttesaGg'];
                          
                                //Out::msgInfo("POST 11", print_r($_POST,true));
                                //Out::msgInfo("Giorni", print_r($_POST[$this->nameForm . 'AttesaGg'],true));

                                
                                break;
                            case "notifica":
                                $_POST['valori']['OPERATION'] = 'inviaStimoloRequest';
                                
                                if ($_POST[$this->nameForm . '_integrazione_check'] == 0){
                                    $_POST['valori']['INTEGRAZIONE'] = "false";
                                }
                                else {
                                    $_POST['valori']['INTEGRAZIONE'] = "true";
                                }
                                
                                break;
                            case "richiestaIntegrazioni":
                            case "richiestaConformazioni":
                            case "diniego":
                                $_POST['valori']['OPERATION'] = 'inviaStimoloRequest';
                                $_POST['valori']['GGMAXINTEGRA'] = $_POST[$this->nameForm . '_MaxGgIntegra'];
                                $_POST['valori']['MSGINTEGRA'] = $_POST[$this->nameForm . '_CorpoIntegra'];
                                $_POST['valori']['GIORNIRISPOSTA'] = $_POST[$this->nameForm . '_MaxGgIntegra'];
                                
                                break;
                            case "inoltroIntegrazione":
                            case "inoltroConformazione":
                                $_POST['valori']['OPERATION'] = 'inviaStimoloRequest';
                                break;
                            
                        }        
                        
                        
                        //Out::msgInfo("POST 11", print_r($_POST,true));

                        $model = $this->returnModel;
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent($this->returnEvent);
                        $objModel->parseEvent();
                        $this->returnToParent();
                        
                        
                        break;

                    case 'before-close-portlet':
                        if ($this->obbligoInvioMail === true) {
                            Out::msgStop("Attenzione", "É obbligatorio l'invio di almeno una PEC/Mail. Confermare l'invio per poter procedere.");
                            break;
                        }
                    case 'close-portlet':
                        if ($this->returnEventOnClose != '') {
                            $model = $this->returnModel;
                            $_POST = array();
                            $_POST['rowid'] = $this->valori['rowidChiamante'];
                            $_POST['event'] = $this->returnEvent;
                            $_POST['valori']['Inviata'] = 0;
                            $phpURL = App::getConf('modelBackEnd.php');
                            $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                            $model();
                        }
                        $this->returnToParent(true);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    
                }
                break;

        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_tipo');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_valori');
        App::$utente->removeKey($this->nameForm . '_sizeAllegati');
        App::$utente->removeKey($this->nameForm . '_returnEventOnClose');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_stimoli');
        App::$utente->removeKey($this->nameForm . '_obbligoInvioMail');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show($this->returnModel);
    }

    function checkDestinatari() {
        $nomi = "";
        foreach ($this->destinatari as $dest) {
            $trovato = false;
            if ($dest['DESTCART'] == "") {
                $nomi .= "<b>" . $dest['NOME'] . "</b><br>";
                Out::msgInfo("Attenzione", "Controllare i codici CART delle seguenti destinazioni perchè sembrano essere vuote:<br>$nomi");
                $trovato = true;
            }
        }
        if ($trovato) {
            return false;
        } else {
            return true;
        }
    }

    function Dettaglio() {
        $praLibCart = new praLibCart();
        
        foreach ($this->allegati as $key => $allegato) {
            if ($allegato['FILEORIG'] == '' || isset($allegato['FILEORIG']) === false) {
                $this->allegati[$key]['FILEORIG'] = $allegato['FILENAME'];
            }
        }
        Out::hide($this->nameForm . '_divGridDestinatari');
        $this->CreaComboStimoli();
        $this->attivaDettaglioStimolo($this->stimoli[0]);
        Out::valore($this->nameForm . '_integrazione_check', 1);
        if ($praLibCart->getTipoProcedimento($_POST['valori']['gesnum']) != 'automatico'){
            Out::hide($this->nameForm . '_integrazione_check_field');
        }

        switch ($this->tipo) {
            case 'passo':
                Out::show($this->nameForm . '_divGridDestinatari');
                if ($_POST['valori']['Destinatari']) {
                    $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, '1', '2000', true);
                }
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Procedimento', $_POST['valori']['Procedimento']);
                Out::valore($this->nameForm . '_Seq', $_POST['valori']['Seq']);
                Out::valore($this->nameForm . '_rowidChiamante', $_POST['valori']['rowidChiamante']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::hide($this->gridAllegati . "_delGridRow");

                Out::valore($this->nameForm . '_AttesaGg', $_POST['valori']['GgRisposta']);
                Out::valore($this->nameForm . '_MaxGgIntegra', $_POST['valori']['GgRisposta']);

                break;
        }
        

        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
    }


    function elaboraArrayAllegati() {
        foreach ($this->allegati as $key => $allegato) {
            $fileOrig = $allegato['FILEORIG'];
            $icon = utiIcons::getExtensionIconClass($allegato['FILENAME'], 32);
            $fileSize = $this->praLib->formatFileSize(filesize($allegato['FILEPATH']));
            $this->allegati[$key]['FILEORIG'] = str_replace("'", " ", $fileOrig);
            $this->allegati[$key]["FileIcon"] = "<span style = \"margin:2px;\" class=\"$icon\"></span>";
            $this->allegati[$key]["FileSize"] = $fileSize;
        }
        return $this->allegati;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = '1000', $selectAll = false) {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        if ($selectAll === true) {
            TableView::setSelectAll($_griglia);
        }

        return;
    }
    
    private function CreaComboStimoli() {

        if ($this->stimoli){
            foreach ($this->stimoli as $stimolo){
                Out::select($this->nameForm . '_Stimolo', 1, $stimolo, 0, $stimolo);
            }
        }
        
        // Out::msgInfo("Stimoli", print_r($this->stimoli,true));

    }
    
    private function attivaDettaglioStimolo($valore){

        //Out::msgInfo("Valore", $valore);
        Out::hide($this->nameForm . '_divComunicazione');
        Out::hide($this->nameForm . '_divNotifica');
        Out::hide($this->nameForm . '_divIntegrazione');
        Out::hide($this->nameForm . '_divInoltro');
        
        switch ($valore) {
            case "comunicazione":
                Out::show($this->nameForm . '_divComunicazione');
                break;
            case "notifica":
                Out::show($this->nameForm . '_divNotifica');
                break;
            case "richiestaIntegrazioni":
            case "richiestaConformazioni":
            case "diniego":
                Out::show($this->nameForm . '_divIntegrazione');
                if ($valore == 'diniego'){
                    Out::hide($this->nameForm . '_MaxGgIntegra_field');
                }
                else {
                    Out::show($this->nameForm . '_MaxGgIntegra_field');
                }
                break;
            case "inoltroIntegrazione":
            case "inoltroConformazione":
                Out::show($this->nameForm . '_divInoltro');
                break;
        }        
        
        
    }
    
}
