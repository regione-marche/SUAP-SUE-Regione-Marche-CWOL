<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proCheckIter() {
    $proCheckIter = new proCheckIter();
    $proCheckIter->parseEvent();
    return;
}

class proCheckIter extends itaModel {
    public $PROT_DB;
    public $ITW_DB;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->PROT_DB=ItaDB::DBOpen('PROT');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        try {
            $this->ITW_DB=ItaDB::DBOpen('ITW');
        }catch(Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'ontimer':
                $id=$_POST['id'];
//                Out::html('desktopFooter',"Codute = ".App::$utente->getKey('idUtente')." Utente = ".App::$utente->getKey('nomeUtente')." Codice Destinatario = ".$Destinatario);
                $Destinatario=proSoggetto::getCodiceSoggettoFromIdUtente();
                $sql="SELECT * FROM ARCITE WHERE (ITEDES BETWEEN '$Destinatario' AND '$Destinatario') AND ITESUS =''" ;
                $Arcite_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
                if ($Arcite_rec) {
                    Out::codice("$('#".$id."').addClass('ui-state-error');");
                }else {
                    Out::codice("$('#".$id."').removeClass('ui-state-error');");
                }
                break;
            case 'onClick':
                $Destinatario =  proSoggetto::getCodiceSoggettoFromIdUtente();
                $model='proStepIter2';
                itaLib::openForm($model, true);
                $appRoute=App::getPath('appRoute.'.substr($model,0,3));
                include_once App::getConf('modelBackEnd.php').'/'.$appRoute.'/'.$model.'.php';
                $closeApp=$_POST['closeApp'];
                $_POST=array();
                $_POST['destinatario']=$Destinatario;
                $_POST['event']='openform';
                $_POST['closeApp']=$closeApp;
                $model();
                break;
        }
    }
}
?>
