<?php

/**
 *
 * GESTIONE Note
 *
 * PHP Version 5
 *
 * @category
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    06.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';

function proParamSegnatura() {
    $proParamSegnatura = new proParamSegnatura();
    $proParamSegnatura->parseEvent();
    return;
}

class proParamSegnatura extends itaModel {

    public $PROT_DB;
    public $nameForm = "proParamSegnatura";
    public $proLib;
    public $gridSegnature = "proParamSegnatura_gridSegnature";
    public $posizioniSegnatura = array();
    public $ANAENT_TAB = array();

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->posizioniSegnatura = App::$utente->getKey($this->nameForm . "_posizioniSegnatura");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_posizioniSegnatura", $this->posizioniSegnatura);
        }
    }

    public function getANAENT_TAB() {
        $ANAENT_TAB = array(34, 35, 47, 51, 55, 31,56);
        return $ANAENT_TAB;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $this->CreaComboSegnatura();
                $this->OpenForm();
                break;
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_ConfermaAddSegnatura':
                        $SEGN_rec = $_POST[$this->nameForm . '_SEGN'];
                        if (!$SEGN_rec['X_COORD'] || !$SEGN_rec['Y_COORD'] || !$SEGN_rec['DESC']) {
                            Out::msgInfo("Attenzione", "Coordinata X, Coordinata Y e Descrizione della segnatura sono obbligatori.");
                            $this->GestPosizioneSegnatura();
                            Out::valori($SEGN_rec, $this->nameForm . '_SEGN');
                            break;
                        }
                        if ($SEGN_rec['IDSEGN']) {
                            $this->posizioniSegnatura[$SEGN_rec['IDSEGN']] = $SEGN_rec;
                            $this->CaricaGriglia($this->gridSegnature, $this->posizioniSegnatura);
                        }
                        break;

                    case $this->nameForm . '_paneSegnAP':
                        $ValX = $_POST[$this->nameForm . '_ANAENT_47']['ENTDE2'];
                        $ValY = $_POST[$this->nameForm . '_ANAENT_47']['ENTDE3'];
                        $ValAnalogica = $_POST[$this->nameForm . '_ANAENT_51']['ENTDE2'];
                        $ValDoc = $_POST[$this->nameForm . '_ANAENT_55']['ENTDE5'];
                        $this->CreaComboSegnatura();
                        Out::valore($this->nameForm . '_ANAENT_47[ENTDE2]', $ValX);
                        Out::valore($this->nameForm . '_ANAENT_47[ENTDE3]', $ValY);
                        Out::valore($this->nameForm . '_ANAENT_51[ENTDE2]', $ValAnalogica);
                        Out::valore($this->nameForm . '_ANAENT_55[ENTDE5]', $ValDoc);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;

                    case $this->nameForm . '_segnaturaCopiaAnalog':
                        $this->embedVars("returnCopiaAnalogica");
                        break;
                    case $this->nameForm . '_segnaturaCopiaAnalogDigitale':
                        $this->embedVars("returnCopiaAnalogicaDigitale");
                        break;

                    case $this->nameForm . '_segnaturaVarDoc':
                        $this->embedVars("returnModelloSegnaturaDoc", 'baseprotocollo');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridSegnature:
                        $this->GestPosizioneSegnatura($_POST['rowid']);
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridSegnature:
                        $this->GestPosizioneSegnatura();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridSegnature:
                        $ValX = $_POST[$this->nameForm . '_ANAENT_47[ENTDE2]'];
                        $ValY = $_POST[$this->nameForm . '_ANAENT_47[ENTDE3]'];
                        if ($ValX == $_POST['rowid'] || $ValY == $_POST['rowid']) {
                            Out::msgInfo("Attenzione", "Non è possibile cancellare la marcatura selezionata perchè è utilizzata.");
                            break;
                        }
                        unset($this->posizioniSegnatura[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridSegnature, $this->posizioniSegnatura);
                        break;
                }
                break;
            case 'printTableToHTML':
                break;

            case 'returnCopiaAnalogica':
                Out::codice("$(protSelector('#" . $this->nameForm . '_ANALOGICA[AUTOGRAFA]' . "')).replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnCopiaAnalogicaDigitale':
                Out::codice("$(protSelector('#" . $this->nameForm . '_ANALOGICA[DIGITALE]' . "')).replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;

            case 'returnModelloSegnaturaDoc':
                Out::codice("$(protSelector('#" . $this->nameForm . '_ANAENT_56[ENTDE2]' . "')).replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;

            case 'embedVars':
                $this->embedVars('', 'baseprotocollo');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_posizioniSegnatura');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function OpenForm() {
        $open_Info = 'Oggetto: visualizza parametri marcatura';
        $this->openRecord($this->PROT_DB, 'ANAENT', $open_Info);
        $Anaent_tab = $this->getANAENT_TAB();
        foreach ($Anaent_tab as $AnaentKey) {
            $Anaent_rec = $this->proLib->GetAnaent($AnaentKey);
            Out::valori($Anaent_rec, $this->nameForm . '_ANAENT_' . $AnaentKey);
        }
        $this->caricaPosizioniSegnatura();
        $this->caricaModelloCopiaAnalogica();

        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Aggiorna');
    }

    private function CaricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaPosizioniSegnatura() {
        $anaent_47 = $this->proLib->GetAnaent('47');
        $this->posizioniSegnatura = unserialize($anaent_47['ENTVAL']);
        $this->CaricaGriglia($this->gridSegnature, $this->posizioniSegnatura);
    }

    private function caricaModelloCopiaAnalogica() {
        $anaent_51 = $this->proLib->GetAnaent('51');
        $ModelliCopiaAnalogica = unserialize($anaent_51['ENTVAL']);
        Out::valore($this->nameForm . '_ANALOGICA[AUTOGRAFA]', $ModelliCopiaAnalogica['AUTOGRAFA']);
        Out::valore($this->nameForm . '_ANALOGICA[DIGITALE]', $ModelliCopiaAnalogica['DIGITALE']);
    }

    private function CreaCombo() {

        Out::select($this->nameForm . '_ANAENT_34[ENTDE6]', 1, "", "1", "");
        Out::select($this->nameForm . '_ANAENT_34[ENTDE6]', 1, "1", "0", "Richiedi Marcatura Pdf da Pec");
        Out::select($this->nameForm . '_ANAENT_34[ENTDE6]', 1, "2", "0", "Marca in automatico i Pdf da Pec");

        Out::select($this->nameForm . '_ANAENT_35[ENTDE4]', 1, "0", "1", "0°");
        Out::select($this->nameForm . '_ANAENT_35[ENTDE4]', 1, "90", "0", "90°");
        Out::select($this->nameForm . '_ANAENT_35[ENTDE4]', 1, "180", "0", "180°");
        Out::select($this->nameForm . '_ANAENT_35[ENTDE4]', 1, "270", "0", "270°");

        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "", "1", "Ente + Segnatura + Uffici");
        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "1", "0", "Ente + Segnatura + Uffici + Barcode");
        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "2", "0", "Tipo protocollo + Ente + Segnatura + Uffici");
        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "3", "0", "Ente + Segnatura");
        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "4", "0", "Tipo protocollo + Ente + Segnatura");
        Out::select($this->nameForm . '_ANAENT_31[ENTDE6]', 1, "5", "0", "Tipo protocollo + Ente + Segnatura + Data Pervenuto + Uffici");

        Out::select($this->nameForm . '_ANAENT_34[ENTDE5]', 1, "", "1", "Nessuna Forzatura");
        Out::select($this->nameForm . '_ANAENT_34[ENTDE5]', 1, "1", "0", "MicroREI");
    }

    private function CreaComboSegnatura() {
        Out::html($this->nameForm . '_ANAENT_47[ENTDE2]', '');
        Out::html($this->nameForm . '_ANAENT_47[ENTDE3]', '');
        Out::html($this->nameForm . '_ANAENT_51[ENTDE2]', '');
        Out::html($this->nameForm . '_ANAENT_55[ENTDE5]', '');

        if (!$this->posizioniSegnatura) {
            $anaent_47 = $this->proLib->GetAnaent('47');
            $this->posizioniSegnatura = unserialize($anaent_47['ENTVAL']);
        }
        Out::select($this->nameForm . '_ANAENT_47[ENTDE2]', 1, "", "1", "Usa Default");
        Out::select($this->nameForm . '_ANAENT_47[ENTDE3]', 1, "", "1", "Usa Default");
        Out::select($this->nameForm . '_ANAENT_51[ENTDE2]', 1, "", "1", "Usa Default");
        Out::select($this->nameForm . '_ANAENT_55[ENTDE5]', 1, "", "1", "Usa Default");

        foreach ($this->posizioniSegnatura as $key => $Segnatura) {
            App::log($Segnatura);
            Out::select($this->nameForm . '_ANAENT_47[ENTDE2]', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
            Out::select($this->nameForm . '_ANAENT_47[ENTDE3]', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
            Out::select($this->nameForm . '_ANAENT_51[ENTDE2]', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
            Out::select($this->nameForm . '_ANAENT_55[ENTDE5]', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
        }
    }

    private function GestPosizioneSegnatura($id = '') {
        $valori[] = array(
            'label' => array(
                'value' => "Codice posizione marcatura:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[IDSEGN]',
            'name' => $this->nameForm . '_SEGN[IDSEGN]',
            'type' => 'text',
            'style' => 'margin:2px; width:60px;',
            'class' => 'ita-readonly required'
        );
        $valori[] = array(
            'label' => array(
                'value' => "Descrizione posizione marcatura:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[DESC]',
            'name' => $this->nameForm . '_SEGN[DESC]',
            'type' => 'text',
            'style' => 'margin:2px; width:380px;',
            'value' => '',
            'class' => 'required'
        );
        $valori[] = array(
            'label' => array(
                'value' => "Stampa solo nella prima pagina:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[FIRST_PAGE]',
            'name' => $this->nameForm . '_SEGN[FIRST_PAGE]',
            'type' => 'checkbox',
            'style' => 'margin:2px;',
            'value' => '',
            'class' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Rotazione stampa marcatura:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[ROTAZ]',
            'name' => $this->nameForm . '_SEGN[ROTAZ]',
            'type' => 'select',
            'style' => 'margin:2px;',
            'value' => '',
            'class' => 'required'
        );
        $valori[] = array(
            'label' => array(
                'value' => "Coordinata X:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[X_COORD]',
            'name' => $this->nameForm . '_SEGN[X_COORD]',
            'type' => 'text',
            'style' => 'margin:2px;',
            'value' => '',
            'class' => 'required'
        );
        $valori[] = array(
            'label' => array(
                'value' => "Coordinata Y:",
                'style' => 'width:250px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_SEGN[Y_COORD]',
            'name' => $this->nameForm . '_SEGN[Y_COORD]',
            'type' => 'text',
            'style' => 'margin:2px;',
            'value' => '',
            'class' => 'required'
        );


        if (!$id) {
            $Titolo = 'Inserimento Nuova Posizione Marcatura';
            $BottConf = '_ConfermaAddSegnatura';
            $id = $this->getMaxIdSegn();
        } else {
            $Titolo = 'Modifica Posizione Marcatura';
            $BottConf = '_ConfermaModSegnatura';
        }
        Out::msgInput(
                $Titolo, $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAddSegnatura',
                'model' => $this->nameForm)
                ), $this->nameForm, 'auto', '700', true
        );
        // Creo Combo
        Out::select($this->nameForm . '_SEGN[ROTAZ]', 1, "0", "1", "0°");
        Out::select($this->nameForm . '_SEGN[ROTAZ]', 1, "90", "0", "90°");
        Out::select($this->nameForm . '_SEGN[ROTAZ]', 1, "180", "0", "180°");
        Out::select($this->nameForm . '_SEGN[ROTAZ]', 1, "270", "0", "270°");

        Out::valori($this->posizioniSegnatura[$id], $this->nameForm . '_SEGN');
        Out::valore($this->nameForm . '_SEGN[IDSEGN]', $id);
    }

    private function getMaxIdSegn() {
        $keys = array_keys($this->posizioniSegnatura);
        $max = max($keys);
        return $max + 1;
    }

    private function Aggiorna() {
        $Anaent_tab = $this->getANAENT_TAB();
        foreach ($Anaent_tab as $AnaentKey) {
            $AnaentRec = $this->proLib->GetAnaent($AnaentKey);
            $Anaent_rec = $_POST[$this->nameForm . '_ANAENT_' . $AnaentKey];
            $Anaent_rec['ROWID'] = $AnaentRec['ROWID'];
            try {
                itaDB::DBUpdate($this->PROT_DB, 'ANAENT', 'ROWID', $Anaent_rec);
            } catch (Exception $e) {
                Out::msgStop("Errore in aggiornamento", $e->getMessage());
                return false;
            }
        }
        // Aggiornamento particolare per anaent 47 entval
        $anaent_47 = $this->proLib->GetAnaent('47');
        $anaent_47['ENTVAL'] = serialize($this->posizioniSegnatura); // * Nuova Gestione */
        $update_Info = 'Aggiornameto anaent: ' . $anaent_47['ENTKEY'];
        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_47, $update_Info);
        // Firma analogica
        $anaent_51 = $this->proLib->GetAnaent('51');
        $ModelliCopiaAnalogica = $_POST[$this->nameForm . '_ANALOGICA'];
        $anaent_51['ENTVAL'] = serialize($ModelliCopiaAnalogica); // * Nuova Gestione */
        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_51, $update_Info);
        // 
        Out::msgBlock('', 2000, true, "Parametri Marcatura aggiornati correttamente.");
        $this->OpenForm();
    }

    private function embedVars($ritorno, $tipoDiz = 'segnatura') {
        $proLibVar = new proLibVariabili();
        switch ($tipoDiz) {
            case 'segnatura':
                $dictionaryLegend = $proLibVar->getLegendaSegnatura('adjacency', 'smarty');
                docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
                break;

            case 'baseprotocollo':
                $dictionaryLegend = $proLibVar->getLegendaCampiProtocollo('adjacency', 'smarty');
                docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
                break;
        }
        return true;
    }

}

?>