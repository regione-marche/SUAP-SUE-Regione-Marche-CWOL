<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    06.10.2011
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envSecureObjFActory.class.php';

function envStrutturePermessi() {
    $envStrutturePermessi = new envStrutturePermessi();
    $envStrutturePermessi->parseEvent();
    return;
}

class envStrutturePermessi extends itaModel {

    public $nameForm = "envStrutturePermessi";
    public $gridGruppi = "envStrutturePermessi_gridGruppi";
    public $buttonBar = "envStrutturePermessi_buttonBar";
    public $ITALWEB_DB;
    private $temp_tab;
    private $figli_tab;
    private $gruppi;
    private $secObj;
    private $objID;
    private $objClass;
    private $headerSezione;
    private $headerIntestazione;
    private $gruppi_temp;
    private $gruppi_completo = '';

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);
        $this->gridGruppi = $this->nameForm . '_gridGruppi';

        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->objID = App::$utente->getKey($this->nameForm . '_objID');
            $this->temp_tab = App::$utente->getKey($this->nameForm . '_temp_tab');
            $this->figli_tab = App::$utente->getKey($this->nameForm . '_figli_tab');
            $this->gruppi = App::$utente->getKey($this->nameForm . '_gruppi');
            $this->secObj = App::$utente->getKey($this->nameForm . '_secObj');
            $this->objClass = App::$utente->getKey($this->nameForm . '_objClass');
            $this->headerSezione = App::$utente->getKey($this->nameForm . '_headerSezione');
            $this->headerIntestazione = App::$utente->getKey($this->nameForm . '_headerIntestazione');
            $this->gruppi_temp = App::$utente->getKey($this->nameForm . '_gruppi_temp');
            $this->gruppi_completo = App::$utente->getKey($this->nameForm . '_gruppi_completo');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_objID", $this->objID);
            App::$utente->setKey($this->nameForm . "_temp_tab", $this->temp_tab);
            App::$utente->setKey($this->nameForm . "_figli_tab", $this->figli_tab);
            App::$utente->setKey($this->nameForm . "_gruppi", $this->gruppi);
            App::$utente->setKey($this->nameForm . "_secObj", $this->secObj);
            App::$utente->setKey($this->nameForm . "_objClass", $this->objClass);
            App::$utente->setKey($this->nameForm . "_headerSezione", $this->headerSezione);
            App::$utente->setKey($this->nameForm . "_headerIntestazione", $this->headerIntestazione);
            App::$utente->setKey($this->nameForm . "_gruppi_temp", $this->gruppi_temp);
            App::$utente->setKey($this->nameForm . "_gruppi_completo", $this->gruppi_completo);
        }
    }

    public function parseEvent() {

        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                Out::html($this->nameForm . '_Header', $this->headerIntestazione);
                Out::html($this->nameForm . '_Header1', "Sezione : " . $this->headerSezione);
                TableView::enableEvents($this->gridGruppi);
                TableView::reload($this->gridGruppi);

                break;
            case 'onBlur':
                break;

            case 'cellSelect':
                switch ($_POST['id']) {

                    case $this->gridGruppi:
                        $model = 'envStrutturePermessiGes';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setEvent('openform');
                        $formObj->setReturnEvent('openform');
                        $formObj->setReturnId('');
                        $formObj->setObjID($this->objID);
                        $formObj->setObjClass($this->objClass);
                        $formObj->setSecID($_POST['rowid']);
                        $formObj->setHeaderTabella($this->headerIntestazione);
                        $formObj->setHeaderSezione($this->getHeaderSezione());
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'onChange':

                $this->gruppi_temp = $this->updateTempGruppi();

                switch ($_POST['id']) {
                    case $this->nameForm . '_CheckGruppi':

                        if ($_POST[$this->nameForm . '_CheckGruppi'] == 1) {
                            $this->gruppi_completo = 'complete';
                        } else {
                            $this->gruppi_completo = '';
                        }

                        TableView::reload($this->gridGruppi);
                        break;
                }

            case 'onClick':

                switch ($_POST['id']) {

                    case $this->nameForm . '_ConfermaAddGruppo':
                        $datiForm = $this->formData[$this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO'];
                        $rec = array();
                        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
                        $rec['GRUPPO_ID'] = $datiForm['UTELOG'];
                        $rec['OBJ_CLASS'] = $obj->getObjClass();
                        $rec['OBJ_ID'] = $obj->getObjID();
                        $rec['DATAINI'] = '';
                        $rec['DATAEND'] = '';
                        $rec['TRASHED'] = 0;
                        /* Inserimento Gruppo su Oggetto */
                        $obj->saveSecObjData($rec);


                        //AGGIORNA GRID
                        TableView::clearGrid($this->gridGruppi);
                        TableView::reload($this->gridGruppi);
                        break;

                    case $this->nameForm . '_Aggiorna':

                        $res = $this->updateGruppi();
                        if ($res) {
                            Out::msgBlock("", 1000, true, "Aggiornamento effettuato sui dati dei gruppi");
                        }
                        $this->returnToParent();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;



            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridGruppi:

                        /* Creo Oggetto */

                        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);

                        $ResultTab = $obj->loadSecObjdata($this->gruppi_completo);
                        $ResultTab = $obj->getSecObj_tab('1');
                        $this->gruppi_temp = $ResultTab;
                        $ResultTab = $this->ElaboraRecord($ResultTab);

                        TableView::clearGrid($this->gridGruppi);

                        $ita_grid01 = new TableView($_POST['id'], array(
                            'arrayTable' => $ResultTab,
                        ));

                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        TableView::enableEvents($this->gridGruppi);

                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridGruppi:
                        $this->dialogAddUtente();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_objID');
        App::$utente->removeKey($this->nameForm . '_temp_tab');
        App::$utente->removeKey($this->nameForm . '_figli_tab');
        App::$utente->removeKey($this->nameForm . '_gruppi');
        App::$utente->removeKey($this->nameForm . '_secObj');
        App::$utente->removeKey($this->nameForm . '_objClass');
        App::$utente->removeKey($this->nameForm . '_headerSezione');
        App::$utente->removeKey($this->nameForm . '_headerIntestazione');
        App::$utente->removeKey($this->nameForm . '_gruppi_temp');
        App::$utente->removeKey($this->nameForm . '_gruppi_completo');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function dialogAddUtente() {
        $fields = array(
            array(
                'label' => array(
                    'value' => "Gruppo",
                    'style' => 'width: 50px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'name' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'margin-left: 5px; width: 120px;'
            )
        );

        $header = "Aggiungi Gruppo";

        $vBottoni = array(
            'F5-Conferma' => array(
                'id' => $this->nameForm . '_ConfermaAddGruppo',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f5",
                'style' => "height:20px;"
            ),
            'F8-Annulla' => array(
                'id' => $this->nameForm . '_AnnullaAddGruppo',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f8",
                'style' => "height:20px;"
        ));

        Out::msgInput('Aggiunta Gruppo', $fields, $vBottoni, $this->nameForm . "_workSpace", 'auto', 'auto', true, $header);
        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
        $gruppi = $obj->getObjContextGroups();
        

        foreach ($gruppi as $gruppo) {
            Out::select($this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]', 1, $gruppo['ID'], $sel1, $gruppo['NOME']);
        }
    }

    public function setObjID($objID) {
        $this->objID = $objID;
    }

    public function getObjID() {
        return $this->objID;
    }

    public function setObjClass($objClass) {
        $this->objClass = $objClass;
    }

    public function getObjClass() {
        return $this->objClass;
    }

    public function ElaboraRecord($Utenti_tab) {

        foreach ($Utenti_tab as $key => $Utenti_rec) {
            $properties_checkbox_trashed = array('size' => 8, 'value' => $Utenti_rec['TRASHED']);
            if ($Utenti_rec['trashed'] == 1) {
                $properties_checkbox_trashed['checked'] = true;
            }
            $Utenti_tab[$key]['GRUPPO_ID'] = "<div class='ita-html'>" . $this->getGruppoName($Utenti_rec['GRUPPO_ID']) . "</div>";
            $Utenti_tab[$key]['DATAINI'] = "<div class='ita-html'>" . itaComponents::getHtmlItaDatepicker(array(
                        "id" => $Utenti_rec['ROW_ID'] . '_VALIDODAL',
                        "dataini" => 'utenti_rec[' . $Utenti_rec['DATAINI'] . '][DATAINI]',
                        "properties" => array('size' => 8, 'value' => $Utenti_rec['DATAINI']),
                    )) . "</div>";
            $Utenti_tab[$key]['DATAEND'] = "<div class='ita-html'>" . itaComponents::getHtmlItaDatepicker(array(
                        "id" => $Utenti_rec['ROW_ID'] . '_VALIDOAL', "dataend" => 'utenti_rec[' . $Utenti_rec['DATAEND'] . '][DATAEND]',
                        "properties" => array('size' => 8, 'value' => $Utenti_rec['DATAEND'])
                    )) . "</div>";
            $Utenti_tab[$key]['trashed'] = "<div class='ita-html'>" . itaComponents::getHtmlItaCheckbox(array(
                        "id" => $Utenti_rec['ROW_ID'] . '_TRASHED', "properties" => $properties_checkbox_trashed
                    )) . "</div>";
            $Utenti_tab[$key]['Permessi'] = "<div class='ita-html'>" . itaComponents::getHtmlIcon('ui-icon ui-icon-locked', 24, 'red', 'Clicca per gestire i permessi di questo gruppo');
        }
        return $Utenti_tab;
    }

    public function setGruppi($gruppi) {
        $this->gruppi = $gruppi;
    }

    public function getGruppi() {
        return $this->gruppi;
    }

    public function setHeaderSezione($headerSezione) {
        $this->headerSezione = $headerSezione;
    }

    public function getHeaderSezione() {
        return $this->headerSezione;
    }

    public function setHeaderIntestazione($headerIntestazione) {
        $this->headerIntestazione = $headerIntestazione;
    }

    public function getHeaderIntestazione() {
        return $this->headerIntestazione;
    }

    public function getGruppoName($id) {
        $sql = "SELECT NOME FROM FNG_GRUPPO WHERE ID=" . $id;
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return $tab['NOME'];
    }

    public function hideButtonBar() {
        Out::removeElement($this->nameForm . '_buttonBar');
    }

    public function updateGruppi() {
        foreach ($this->gruppi_temp as $gruppi_temp_rec) {
            $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
            $obj->updateSecObjData($gruppi_temp_rec);
        }
        return true;
    }

    public function updateTempGruppi() {

        $Tipo = explode("_", $_POST['id']);
        $Riga = $Tipo[0];
        $Tipo = $Tipo[1];
        $this->Chiave = $Riga;

        switch ($Tipo) {
            case 'VALIDODAL' :
                $this->temp_tab[$Riga]['VALIDODAL'] = $_POST[$Riga . '_VALIDODAL'];
                foreach ($this->gruppi_temp as &$gruppi_temp_rec) {
                    if ($gruppi_temp_rec['ROW_ID'] == $this->Chiave) {
                        $gruppi_temp_rec['DATAINI'] = $this->temp_tab[$Riga]['VALIDODAL'];
                    }
                }
                break;

            case 'VALIDOAL' :
                $this->temp_tab[$Riga]['VALIDOAL'] = $_POST[$Riga . '_VALIDOAL'];
                foreach ($this->gruppi_temp as &$gruppi_temp_rec) {
                    if ($gruppi_temp_rec['ROW_ID'] == $this->Chiave) {
                        $gruppi_temp_rec['DATAEND'] = $this->temp_tab[$Riga]['VALIDOAL'];
                    }
                }
                break;

            case 'TRASHED' :
                $this->temp_tab[$Riga]['TRASHED'] = $_POST[$Riga . '_TRASHED'];
                foreach ($this->gruppi_temp as &$gruppi_temp_rec) {
                    if ($gruppi_temp_rec['ROW_ID'] == $this->Chiave) {
                        $gruppi_temp_rec['TRASHED'] = $this->temp_tab[$Riga]['TRASHED'];
                    }
                }
                break;
        }
        
        return $this->gruppi_temp;
    }



}
