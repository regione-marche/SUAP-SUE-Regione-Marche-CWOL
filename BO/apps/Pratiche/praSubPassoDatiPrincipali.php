<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';

function praSubPassoDatiPrincipali() {
    $praSubPassoDatiPrincipali = new praSubPassoDatiPrincipali();
    $praSubPassoDatiPrincipali->parseEvent();
    return;
}

class praSubPassoDatiPrincipali extends praSubPasso {
    /* @var $parentObj praCompPassoDett */

    public $nameForm = 'praSubPassoDatiPrincipali';

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function postInstance() {
        parent::postInstance();
    }

    public function close() {
        parent::close();
    }

    public function returnToParent($close = true) {
        parent::returnToParent($close);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->inizializzaForm();
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PROPART]':
                        $this->getParentObj()->attivaPaneArticoli($_POST[$this->nameForm . '_PROPAS']['PROPART']);
                        break;
                    case $this->nameForm . '_PROPAS[PROPUBALL]':
                        /* @var $alleObj praSubPassoCaratteristiche */
                        $this->getParentObj()->abilitaAllegatiAllaPubblicazione($_POST[$this->nameForm . '_PROPAS']['PROPUBALL']);
                        break;
                }
            case 'onClick':

                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    /**
     * 
     */
    function inizializzaForm() {
        return;
        /**
         * 
         * Da Elminare
         */
        Out::removeElement($this->nameForm . '_PROPAS[PROUOP]_field');
        Out::removeElement($this->nameForm . '_PROPAS[PROIMP]_field');
        Out::removeElement($this->nameForm . '_PROPAS[PRORIS]_field');
        Out::removeElement($this->nameForm . '_UNITA');

        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $this->tipoProtocollo = $PARMENTE_rec['TIPOPROTOCOLLO'];

        /*
         * Controllo se l'utente ha configurati i parametri per protocollare in altro ente
         */
        $enteProtRec_rec = $this->accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
        if ($enteProtRec_rec) {
            $meta = unserialize($enteProtRec_rec['METAVALUE']);
            if ($meta['TIPO'] && $meta['URLREMOTO']) {
                $this->tipoProtocollo = $meta['TIPO'];
            }
        }
//
        $this->CreaCombo();
        $this->selectCalendari();
        $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
        $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
        $this->daMail = $_POST[$this->nameForm . "_daMail"];
        $this->pagina = $_POST['pagina'];
        $this->sql = $_POST['sql'];
        $this->selRow = $_POST['selRow'];
        Out::setDialogOption($this->nameForm, 'title', "'" . $_POST[$this->nameForm . "_title"] . "'");

//        if ($this->flagAssegnazioniPasso == true) {
//            $generator = new itaGenerator();
//            $retHtml = $generator->getModelHTML('praTabAssegnazioniPassi', false, $this->nameForm, false);
//            Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
//        }
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Protetto", "1", "Protetto");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Privato", "0", "Privato");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Aperto", "0", "Pubblico");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "soloPasso", "0", "Protetto solo Passo");

        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 0, "1", "");
        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 1, "0", "Descrizione breve");
        Out::select($this->nameForm . '_PROPAS[PROPST]', 1, 2, "0", "Descrizione estesa");
    }

    public function Nuovo($rowid, $tipo = 'propak') {
        
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    private function Nascondi() {
        
    }

}
