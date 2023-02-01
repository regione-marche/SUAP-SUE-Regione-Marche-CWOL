<?php

/* * 
 *
 * 
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    01.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proHalley.class.php';

function proConfermaHalley() {
    $proConfermaHalley = new proConfermaHalley();
    $proConfermaHalley->parseEvent();
    return;
}

class proConfermaHalley extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSdi;
    public $nameForm = "proConfermaHalley";
    public $currObjSdi;
    public $Anapro_rec = array();

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibSdi = new proLibSdi();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->currObjSdi = unserialize(App::$utente->getKey($this->nameForm . "_currObjSdi"));
            $this->Anapro_rec = App::$utente->getKey($this->nameForm . "_Anapro_rec");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_currObjSdi", serialize($this->currObjSdi));
            App::$utente->setKey($this->nameForm . "_Anapro_rec", $this->Anapro_rec);
        }
    }

    function getCurrObjSdi() {
        return $this->currObjSdi;
    }

    function setCurrObjSdi($currObjSdi) {
        $this->currObjSdi = $currObjSdi;
    }

    public function getAnapro_rec() {
        return $this->Anapro_rec;
    }

    public function setAnapro_rec($Anapro_rec) {
        $this->Anapro_rec = $Anapro_rec;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->ApriForm();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        if (!$_POST[$this->nameForm . '_TipoOggetto']) {
                            Out::msgInfo('Informazione', "Selezionare il tipo di oggetto.");
                            break;
                        }
                        $OggettoSelezionato = '';
                        $DatiFatt = $_POST[$this->nameForm . '_DATIFATT'];
                        switch ($_POST[$this->nameForm . '_TipoOggetto']) {
                            case'1':
                                $OggettoSelezionato = $DatiFatt['OGGETTOPROTOCOLLO'];
                                break;
                            case'2':
                                $OggettoSelezionato = $DatiFatt['CAUSALEFATTURA'];
                                break;
                            case'3':
                                $OggettoSelezionato = $DatiFatt['BENIPRIMARIGA'];
                                break;
                            case'4':
                                $OggettoSelezionato = $DatiFatt['BENITOTALE'];
                                break;
                        }
                        $_POST = array();
                        $_POST['OggettoSelezionato'] = $OggettoSelezionato;
                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->parseEvent();
                        $this->close();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currObjSdi');
        App::$utente->removeKey($this->nameForm . '_Anapro_rec');
        Out::closeDialog($this->nameForm);
    }

    public function ApriForm() {
        $Anapro_rec = $this->Anapro_rec;
        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        /*
         * Istanza Oggetto ProHalley
         */
        $proHalley = new proHalley();
        $objProSdi = $proHalley->CaricaOggettoSdi($Anapro_rec);
        if (!$objProSdi) {
            Out::msgStop('Errore', $proHalley->getErrMessage());
            return false;
        }
        $EstrattoFattura = $objProSdi->getEstrattoFattura();
        /*
         * Preparazione Campi
         */
        $Oggetto = $Anaogg_rec['OGGOGG'];
        $Causale = $EstrattoFattura[0]['Body'][0]['Oggetto'];
        $BeniPrimaRiga = $EstrattoFattura[0]['Body'][0]['DescrizioneBeni'][0]['Descrizione'];
        $BeniDescTotale = '';
        foreach ($EstrattoFattura[0]['Body'][0]['DescrizioneBeni'] as $DescrizioneRigaBene) {
            $BeniDescTotale.=$DescrizioneRigaBene['Descrizione'] . ' ';
        }

        if (!$Causale) {
            Out::hide($this->nameForm . '_DATIFATT[CAUSALEFATTURA]_field');
            Out::hide($this->nameForm . '_FlagCausale');
        }
        if (!$BeniPrimaRiga) {
            Out::hide($this->nameForm . '_DATIFATT[BENIPRIMARIGA]_field');
            Out::hide($this->nameForm . '_FlagRiga');
        }
        if (!$BeniDescTotale) {
            Out::hide($this->nameForm . '_DATIFATT[BENITOTALE]_field');
            Out::hide($this->nameForm . '_FlagBeni');
        }

        $messaggio = '<span style="font-size:1.2em; padding:5px; "><b>Protocollo:  ' . $Anapro_rec['PROSEG'] . '<br> Scegli la descrizione da usare per la fattura. Modifica quella che vuoi usare.</b></span>';
        Out::html($this->nameForm . '_divInformazione', $messaggio);

        Out::valore($this->nameForm . '_DATIFATT[OGGETTOPROTOCOLLO]', $Anaogg_rec['OGGOGG']);
        if ($Causale) {
            Out::valore($this->nameForm . '_DATIFATT[CAUSALEFATTURA]', $Causale);
        }
        if ($BeniPrimaRiga) {
            Out::valore($this->nameForm . '_DATIFATT[BENIPRIMARIGA]', $BeniPrimaRiga);
        }
        if ($BeniDescTotale) {
            Out::valore($this->nameForm . '_DATIFATT[BENITOTALE]', $BeniDescTotale);
        }
        Out::attributo($this->nameForm . "_FlagRiga", "checked", "0", "checked");
    }

    public function returnToParent() {
        $_POST = array();
        $_POST['OggettoSelezionato'] = $this->mittentiAggiuntivi;
        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->close();
    }

}

?>
