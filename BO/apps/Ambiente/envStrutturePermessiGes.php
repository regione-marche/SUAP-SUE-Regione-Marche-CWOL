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
include_once ITA_BASE_PATH . '/apps/Bdap/bdaAmtSecObjAmt_indice.class.php';

function envStrutturePermessiGes() {
    $envStrutturePermessiGes = new envStrutturePermessiGes();
    $envStrutturePermessiGes->parseEvent();
    return;
}

class envStrutturePermessiGes extends itaModel {

    public $nameForm = "envStrutturePermessiGes";
    public $gridPermessi = "envStrutturePermessiGes_gridPermessi";
    public $ITALWEB_DB;
    private $objID;
    private $objClass;
    private $temp_tab;
    private $headerSezione;
    private $headerTabella;
    private $permessi;
    private $secID;
    private $permessiTab;

    function __construct() {
        parent::__construct();
        try {
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->temp_tab = App::$utente->getKey($this->nameForm . '_temp_tab');
            $this->headerSezione = App::$utente->getKey($this->nameForm . '_headerSezione');
            $this->headerTabella = App::$utente->getKey($this->nameForm . '_headerTabella');
            $this->objID = App::$utente->getKey($this->nameForm . '_objID');
            $this->objClass = App::$utente->getKey($this->nameForm . '_objClass');
            $this->permessi = App::$utente->getKey($this->nameForm . '_permessi');
            $this->secID = App::$utente->getKey($this->nameForm . '_secID');
            $this->permessiTab = App::$utente->getKey($this->nameForm . '_permessiTab');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_temp_tab", $this->temp_tab);
            App::$utente->setKey($this->nameForm . "_header", $this->header);
            App::$utente->setKey($this->nameForm . "_headerSezione", $this->headerSezione);
            App::$utente->setKey($this->nameForm . "_headerTabella", $this->headerTabella);
            App::$utente->setKey($this->nameForm . "_objID", $this->objID);
            App::$utente->setKey($this->nameForm . "_objClass", $this->objClass);
            App::$utente->setKey($this->nameForm . "_permessi", $this->permessi);
            App::$utente->setKey($this->nameForm . "_secID", $this->secID);
            App::$utente->setKey($this->nameForm . "_permessiTab", $this->permessiTab);
        }
    }

    public function parseEvent() {

        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                /* Creo Oggetto */
                $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
                $obj->loadSecObjGroupMetaData($this->secID);
                $this->temp_tab = $obj->getSecMeta_tab();


                $c = count($this->temp_tab);
                if ($c == 0) {
                    $lista = $obj->getSec_meta_key_list();

                    foreach ($lista as $key => $value) {

                        $rec = array();
                        $rec['CHIAVE'] = $key;
                        $rec['VALORE'] = 0;
                        $rec['SEC_OBJ_ID'] = $this->secID;
                        $obj->saveSecObjMetaData($rec);
                    }
                    $obj->loadSecObjGroupMetaData($this->secID);
                    $this->temp_tab = $obj->getSecMeta_tab();
                }


                $gruppoName = $this->getGruppoName($this->secID);

                Out::html($this->nameForm . '_Header', $this->getHeaderTabella());
                Out::html($this->nameForm . '_Header1', "Sezione : " . $this->getHeaderSezione());
                Out::html($this->nameForm . '_Header2', "Gruppo : " . $gruppoName);
                TableView::enableEvents($this->gridPermessi);
                TableView::reload($this->gridPermessi);

                break;

            case 'onBlur':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaAddPermesso':

                        $datiForm = $this->formData[$this->nameForm . '_struttura_gruppo_membro'];
                        $rec = array();
                        $rec['CHIAVE'] = $datiForm['utelog'];
                        $rec['VALORE'] = 0;
                        $rec['SEC_OBJ_ID'] = $this->secID;
                        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
                        $obj->saveSecObjMetaData($rec);

                        //AGGIORNA GRID

                        $obj->loadSecObjGroupMetaData($this->secID);

                        $this->temp_tab = $obj->getSecMeta_tab();
                        


                        TableView::clearGrid($this->gridPermessi);
                        TableView::reload($this->gridPermessi);
                        break;

                    case $this->nameForm . '_Aggiorna':
                        foreach ($this->permessiTab as $permessi_rec) {
                            $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
                            $obj->updateSecObjMetaData($permessi_rec);
                            Out::msgBlock("", 1000, true, "Aggiornamento permessi avvenuto correttamente.");
                        }
                        // TableView::reload($this->gridGruppi);
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'onChange':
                $Tipo = explode("_", $_POST['id']);
                $Riga = $Tipo[0];
                $Tipo = $Tipo[1];
                $this->Chiave = $Riga;

                switch ($Tipo) {

                    case 'VALORE' :

                        $val = $_POST[$Riga . '_VALORE'];

                        foreach ($this->permessiTab as &$permessi_rec) {

                            if ($permessi_rec['ROW_ID'] == $Riga) {
                                $permessi_rec['VALORE'] = $val;
                            }
                        }
                        break;
                }


                break;


            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridPermessi:

                        $this->dialogAddAction();

                        break;
                }
                break;


            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridPermessi:
                        $ResultTab = $this->temp_tab;
                        $this->permessiTab = $ResultTab;
                        $ResultTab = $this->ElaboraRecord($ResultTab);
                        TableView::clearGrid($this->gridPermessi);
                        $ita_grid01 = new TableView($_POST['id'], array(
                            'arrayTable' => $ResultTab,
                        ));

                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');


                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_temp_tab');
        App::$utente->removeKey($this->nameForm . '_headerTabella');
        App::$utente->removeKey($this->nameForm . '_headerSezione');
        App::$utente->removeKey($this->nameForm . '_objID');
        App::$utente->removeKey($this->nameForm . '_objClass');
        App::$utente->removeKey($this->nameForm . '_permessi');
        App::$utente->removeKey($this->nameForm . '_secID');
        App::$utente->removeKey($this->nameForm . '_permessiTab');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function dialogAddAction() {

        $fields = array(
            array(
                'label' => array(
                    'value' => "Permessi",
                    'style' => 'width: 50px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'name' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'margin-left: 5px; width: 120px;'
            )
        );

        $header = "Aggiungi Permesso";

        $vBottoni = array(
            'F5-Conferma' => array(
                'id' => $this->nameForm . '_ConfermaAddPermesso',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f5",
                'style' => "height:20px;"
            ),
            'F8-Annulla' => array(
                'id' => $this->nameForm . '_AnnullaAddPermesso',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f8",
                'style' => "height:20px;"
        ));

        Out::msgInput('Aggiunta Permesso', $fields, $vBottoni, $this->nameForm . "_workSpace", 'auto', 'auto', true, $header);
        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
        $this->permessi = $obj->getSec_meta_key_list();

        
        $permesso = $this->temp_tab;

        $per = array();
        foreach ($permesso as $perm) {
            array_push($per, $perm['CHIAVE']);
        }
        


        foreach ($this->permessi as $key => $permesso) {
            if (in_array($key, $per, TRUE) == false) {
                Out::select($this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]', 1, $key, 0, $permesso['DESCRIZIONE']);
            }
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

    public function setSecID($secID) {
        $this->secID = $secID;
    }

    public function getSecID() {
        return $this->secID;
    }

    public function setHeaderTabella($headerTabella) {
        $this->headerTabella = $headerTabella;
    }

    public function setHeaderSezione($headerSezione) {
        $this->headerSezione = $headerSezione;
    }

    public function getHeaderTabella() {
        return $this->headerTabella;
    }

    public function getHeaderSezione() {
        return $this->headerSezione;
    }

    public function getPermessi($id) {
        $sql = "SELECT * FROM FNG_SECMETA WHERE SEC_OBJ_ID=" . $id;
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        return $tab;
    }

    public function ElaboraRecord($Utenti_tab) {

        foreach ($Utenti_tab as $key => $Utenti_rec) {
            $properties_checkbox_trashed = array('size' => 8, 'value' => $Utenti_rec['TRASHED']);
            if ($Utenti_rec['VALORE'] == 1) {
                $properties_checkbox_trashed['checked'] = true;
            }

            $Utenti_tab[$key]['CHIAVE'] = $this->getDescPermesso($Utenti_tab[$key]['CHIAVE']);
            $Utenti_tab[$key]['VALORE'] = "<div class='ita-html'>" . itaComponents::getHtmlItaCheckbox(array(
                        "id" => $Utenti_rec['ROW_ID'] . '_VALORE', "properties" => $properties_checkbox_trashed
                    )) . "</div>";
        }
        return $Utenti_tab;
    }

    public function getDescPermesso($id) {

        $obj = envSecureObjFactory::getEnvSecureObj($this->objClass, $this->objID);
        $this->permessi = $obj->getSec_meta_key_list();
        foreach ($this->permessi as $key => $per) {
            if ($id == $key) {
                if (substr($key, 0, 5) == 'CUST@') {
                    return '<b>'.$per['DESCRIZIONE'].'</b>';
                } else {
                    return $per['DESCRIZIONE'];
                }
            }
        }
    }

    public function getGruppoName($id) {

        $sql = "SELECT GRUPPO_ID FROM FNG_SECOBJ WHERE ROW_ID=" . $id;
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $sql1 = "SELECT NOME FROM FNG_GRUPPO WHERE ID=" . $tab['GRUPPO_ID'];
        $tab1 = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql1, false);
        return $tab1['NOME'];
    }

}

?>
