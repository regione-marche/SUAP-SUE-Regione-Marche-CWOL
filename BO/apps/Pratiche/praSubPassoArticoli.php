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

function praSubPassoArticoli() {
    $praSubPassoArticoli = new praSubPassoArticoli();
    $praSubPassoArticoli->parseEvent();
    return;
}

class praSubPassoArticoli extends praSubPasso {

    public $nameForm = 'praSubPassoArticoli';

    function __construct() {
        parent::__construct();
    }

    public function postInstance() {
        parent::postInstance();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->Dettaglio($this->keyPasso);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . "_GeneraPassword":
                        $pwd = itaLib::generatePassword();
                        Out::valore($this->nameForm . "_PROPAS[PROPPASS]", $pwd);
                        
                        $this->aggiornaDati();
                        break;
                }

                break;
            case 'onChange':
                switch ($_POST['id']) {
//                    case $this->nameForm . '_PROPAS[PROPART]':
//                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
//                        if ($_POST[$this->nameForm . '_PROPAS']['PROPART'] == 1) {
//                            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
//                            Out::showTab($this->nameForm . "_paneArticoli");
//                            Out::valore($this->nameForm . "_PROPAS[PROPTIT]", $propas_rec['PRODPA']);
//                        } else {
//                            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
//                            Out::hideTab($this->nameForm . "_paneArticoli");
//                            Out::valore($this->nameForm . "_PROPAS[PROPTIT]", "");
//                        }
//                        break;

                    case $this->nameForm . '_PROPAS[PROFLCDS]':
                        $this->getParentObj()->abilitaAllegatiAllaConferenza($_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                        
                        
                        /*
                         * Blocco lo spostamento dei destinatari se il processo di firma è già iniziato
                         */
//                        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
//                        $processoIniziato = $this->checkFirmaCds($propas_rec['PROFLCDS']);
//                        if ($processoIniziato === true) {
//                            Out::msgInfo("Conferenza di Servizi", "Impossibile modificare la spunta CDS.<br>Il Processo di firma è già iniziato.");
//                            Out::valore($this->nameForm . "_PROPAS[PROFLCDS]", $propas_rec['PROFLCDS']);
//                            break;
//                        } else {
//                            /* @var $alleObj praSubPassoCaratteristiche */
//                            $alleObj = $this->parentObj->getArrSubFormObj('praSubPassoCaratteristiche');
//                            $alleObj->abilitaAllegatiAllaConferenzaServizi($_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
//                        }
//
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
    }

    public function returnToParent($close = true) {
        parent::returnToParent($close);
    }

    private function CreaCombo() {
        
    }

    public function Nuovo($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();

        $propas_rec = $this->praLib->GetPropas($rowid, $tipo);
        if ($propas_rec) {
            // Riporta i valori dal recordset al form dati
            Out::valori($propas_rec, $this->nameForm . '_PROPAS');
        }
        
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();

        //Out::msgInfo("Valore PROPAK", "KeyPasso: " . $this->keyPasso . "  rowId: " . $rowid);
        
        $propas_rec = $this->praLib->GetPropas($rowid, $tipo);
        if ($propas_rec) {
            // Riporta i valori dal recordset al form dati
            Out::valori($propas_rec, $this->nameForm . '_PROPAS');
        }

        
    }

    
    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    private function Nascondi() {
        
    }

    /**
     * 
     * @param type $flagCds
     * @return boolean
     */
    private function checkFirmaCds($flagCds) {
        $processoIniziato = false;
        if ($flagCds == 1) {
            foreach ($this->passAlle as $allegato) {
                $pasdoc_recCtr = array();
                if ($allegato['PASROWIDBASE'] != 0) {
                    $pasdoc_recCtr = $this->praLib->GetPasdoc($allegato['PASROWIDBASE'], "ROWID");
                    if ($pasdoc_recCtr) {
                        $processoIniziato = true;
                        break;
                    }
                }
            }
        }
        return $processoIniziato;
    }

    /**
     * 
     * @param type $titolo
     */
    public function setTitoloArticolo($titolo) {
        Out::valore($this->nameForm . "_PROPAS[PROPTIT]", $titolo);
    }

    /**
     * 
     * @param type $titolo
     */
    public function setProFlcDs($valore) {
        Out::valore($this->nameForm . "_PROPAS[PROFLCDS]", $valore);
    }
    
    
    public function aggiornaDati(){
        // Salvataggio dati inseriti

//        $this->setErrCode(null);
//        $this->setErrMessage('');
        
        $propas_rec = $_POST[$this->nameForm . '_PROPAS'];
        
        //Out::msgInfo("Propas_rec", print_r($propas_rec, true));
        
        if (!$propas_rec) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Non trovato record di PROPAS da aggiornare');

            return false;
        }
        
        $propas_rec['PROPAK'] = $this->keyPasso;

        $pram_db = ItaDB::DBOpen('PRAM');

        
        $update_Info = "Oggetto: Aggiornamento passo con chiave " . $this->keyPasso;
        if (!$this->updateRecord($pram_db, 'PROPAS', $propas_rec, $update_Info)) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Salvataggio risposta non riuscito');
            Out::msgStop("ERRORE", "Aggiornamento record");
            
            return false;
        }
        
        
        return true;
    }
    
    
}
