<?php
/**
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Ambiente
 * @author     Carlo Iesari <carlo@iesari.me>....
 * @copyright  1987-2015 Italsoft snc
 * @license 
 * @version    23.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 **/
include_once ITA_LIB_PATH . '/nusoap/nusoap.php';

function envEditor() {
    $envEditor = new envEditor();
    $envEditor->parseEvent();
    return;
}

class envEditor extends itaModel {
    public $nameForm = "envEditor";
    public $soapuri = 'http://192.168.191.1/itaEngine/ws/Protocollo/proWsProtocollo.php?wsdl';
    public $domaincode = '01';
    public $revsPath;

    function __construct() {
        parent::__construct();
        $this->revsPath = App::getConf('admin.historyPath');
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {        
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ( !$this->revsPath ) {
                    Out::msgStop("Attenzione", "Non è stata configurata la variabile admin.historyPath nel file config.ini");
                }
                $this->openRicerca();
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Apri':
                        $model = $_POST[$this->nameForm . '_nomeProgramma'];
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        if (file_exists($modelSrc)) {
                            $this->openGestione($modelSrc);
                        } else {
                            Out::msgInfo("","Model $model.php non trovato");
                        }
                        break;
                        
                    case $this->nameForm . '_Torna':
                        $this->openRicerca();
                        break;
                        
                    case $this->nameForm . '_Revisioni':
                        $revs = array();
                        
                        $prog = $_POST[$this->nameForm.'_nomeProgramma'];
                        $revRoute = $this->revsPath . '/' . App::getPath('appRoute.' . substr($prog, 0, 3));
                        
                        foreach ( glob( "$revRoute/" . $prog . "_*.ini" ) as $ini ) {
                            array_push( $revs, parse_ini_file( $ini ) );
                        }
                        
                        if ( count( $revs ) < 1 ) {
                            Out::msgInfo("Attenzione", "Non ci sono revisioni per $prog");
                            break;
                        }

                        $model = 'utiRicDiag';
                        $gridOptions = array(
                            "Caption" => "Revisioni " . $prog,
                            "width" => '460',
                            "height" => '500',
                            "rowNum" => '20',
                            "rowList" => '[]',
                            "arrayTable" => $revs,
                            "colNames" => array(
                                "Utente",
                                "Data",
                                "Commento"
                            ),
                            "colModel" => array(
                                array("name" => 'Utente', "width" => 80),
                                array("name" => 'Data', "width" => 110),
                                array("name" => 'Commento', "width" => 255)
                            ),
                            "pgbuttons" => 'false',
                            "pginput" => 'false'
                        );
                        
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['gridOptions'] = $gridOptions;
                        $_POST['returnModel'] = $this->nameForm;
                        $_POST['returnEvent'] = 'returnRevisioni';
                        $_POST['returnKey'] = 'retKey';
                        $_POST['retid'] = $prog;
                        itaLib::openForm($model, true, true, 'desktopBody', $this->nameForm);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        
//                        /* @var $utiRicDiag utiRicDiag */
//                        $utiRicDiag = itaModel::getInstance('utiRicDiag');
//
//                        $utiRicDiag->event = 'openform';
//                        $utiRicDiag->gridOptions = $gridOptions;
//                        $utiRicDiag->returnModel = $this->nameForm;
//                        $utiRicDiag->returnEvent = 'returnRevisioni';
//                        $utiRicDiag->returnKey = 'retKey';
//                        $utiRicDiag->parseEvent();
                        break;
                        
                    case $this->nameForm . '_Salva':
                        $model = $_POST[$this->nameForm . '_nomeProgramma'];
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        
                        $md5new = md5( str_replace("\r\n", "\n", $_POST[$this->nameForm . '_codice'] ) );
                        $md5old = md5( str_replace("\r\n", "\n", file_get_contents( App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php' ) ) );
                        
                        if ( $md5new === $md5old ) {
                            Out::msgInfo("Attenzione", "Non ci sono modifiche da salvare");
                            break;
                        }
                        
                        Out::msgInput("Accesso", array(
                            array(
                                'label' => array(
                                        'value' => 'Nome utente',
                                        'style' => 'display: block; float: initial; text-align: left;'
                                ),
                                'id' => $this->nameForm . '_nomeUtente',
                                'name' => $this->nameForm . '_nomeUtente',
                                'type' => 'text',
                                'class' => 'required'
                            ),
                            array(
                                'label' => array(
                                        'value' => '<br>Password',
                                        'style' => 'display: block; float: initial; text-align: left;'
                                ),
                                'id' => $this->nameForm . '_password',
                                'name' => $this->nameForm . '_password',
                                'type' => 'password',
                                'class' => 'required'
                            ),
                            array(
                                'label' => array(
                                        'value' => '<br>Motivo della modifica',
                                        'style' => 'display: block; float: initial; text-align: left;'
                                ),
                                'id' => $this->nameForm . '_commentoModifica',
                                'name' => $this->nameForm . '_commentoModifica',
                                'type' => 'text',
                                'size' => '30'
                            )
                        ), array(
                            'F3 - Accedi' => array(
                                'id' => $this->nameForm . '_Accesso',
                                'model' => $this->nameForm,
                                'class' => 'ita-button-validate',
                                'shortCut' => 'f3'
                            )
                        ), $this->nameForm);
                        break;
                    
                    case $this->nameForm . '_Accesso':
                        Out::closeCurrentDialog();
                        $client = new nusoap_client($this->soapuri, true);
                        $client->soap_defencoding = 'UTF-8';
                        $client->decode_utf8 = true;
                        $client->debugLevel = 0;

                        $token = $client->call('GetItaEngineContextToken', array(
                            "userName" => $_POST[$this->nameForm.'_nomeUtente'],
                            "userPassword" => $_POST[$this->nameForm.'_password'],
                            "domainCode" => $this->domaincode
                        ), '');
                        
                        if ($client->fault) {
                            Out::msgStop("Fault Autenticazione", $client->faultstring);
                            break;
                        }
                        if ( $client->getError() ) {
                            Out::msgStop("Errore Autenticazione", $client->getError());
                            break;
                        }
                        
                        // -----------------------------------
                        
                        $this->salvaFile(); // indipendentemente dal risultato, continuo per eliminare il token
                        
                        // -----------------------------------
                        
                        $response_destroy = $client->call('DestroyItaEngineContextToken', array(
                            "token" => $token,
                            "domainCode" => $this->domaincode
                        ), '');
                        
                        if (!$response_destroy) {
                            Out::msgStop("Fault Chiusura", $client->faultstring);
                            break;
                        }
                        if ( $client->getError() ) {
                            Out::msgStop("Errore Chiusura", $client->getError());
                            break;
                        }
                        
                        break;
                    
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
            
            case 'returnRevisioni':
                $model = $_POST['retid'];
                $revRoute = $this->revsPath . '/' . App::getPath('appRoute.' . substr($model, 0, 3));
                Out::msgInfo("", htmlspecialchars(file_get_contents($revRoute.'/'.$model.'_'.$_POST['rowData']['Timestamp'].'.php')));
                break;
        }
    }
    
    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons) {
        Out::hide($this->nameForm . '_Apri');
        Out::hide($this->nameForm . '_Salva');
        Out::hide($this->nameForm . '_Revisioni');
        Out::hide($this->nameForm . '_Torna');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }
    
    public function openRicerca() {
        $this->mostraForm('divRicerca');
        $this->mostraButtonBar(array('Apri'));
        
        Out::setFocus($this->nameForm, $this->nameForm . '_nomeProgramma');
    }
    
    public function openGestione($src) {
        $this->mostraForm('divGestione');
        $this->mostraButtonBar(array('Salva', 'Revisioni', 'Torna'));
        
        Out::valore($this->nameForm.'_codice', file_get_contents($src));
    }

    public function salvaFile() {
        $utente = $_POST[$this->nameForm . '_nomeUtente'];
        $model = $_POST[$this->nameForm . '_nomeProgramma'];
        $codice = $_POST[$this->nameForm . '_codice'];
        $commento = str_replace( '"', ' ', $_POST[$this->nameForm . '_commentoModifica'] );
        
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        $revRoute = $this->revsPath . '/' . $appRoute;
        $time = filemtime( App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php' );
        
        $ini = "[$model]\r\n";
        $ini.= "Timestamp = \"$time\"\r\n";
        $ini.= "Data = \"" . date('d/m/Y H:i', $time) . "\"\r\n";
        $ini.= "Utente = \"$utente\"\r\n";
        $ini.= "Checksum = \"" . md5( str_replace( "\r\n", "\n", $codice ) ) . "\"\r\n";
        $ini.= "Commento = \"$commento\"";
        
        if ( !is_dir($revRoute) ) {
            if ( !@mkdir($revRoute, 0777, true) ) {
                    Out::msgStop("Errore", "Impossibile creare la cartella $revRoute");
                    return false;
            }
        }
        
        if ( !file_put_contents( $revRoute . '/' . $model . '_' . $time . '.php', file_get_contents( App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php' ) ) ) {
            Out::msgStop("Errore", "Impossibile salvare la copia di revisione");
            return false;
        }
        chmod( $revRoute . '/' . $model . '_' . $time . '.php', 0777 );
        
        if ( !file_put_contents( $revRoute . '/' . $model . '_' . $time . '.ini', $ini ) ) {
            Out::msgStop("Errore", "Impossibile salvare il file .ini");
            return false;
        }
        chmod( $revRoute . '/' . $model . '_' . $time . '.ini', 0777 );
        
        if ( !file_put_contents( App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php', $codice ) ) {
            Out::msgStop("Errore", "Impossibile salvare il model");
            return false;
        }
        
        
      	Out::msgInfo("","File salvato con successo");
        return true;
    }
}

?>