<?php

/**
 *
 * 
 *
 * PHP Version 5
 *
 * @category
 * @author
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    13.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibStrutture.class.php';
include_once ITA_BASE_PATH . '/apps/Bdap/bdaLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

function envStruttureGruppiGes() {
    $envStruttureGruppiGes = new envStruttureGruppiGes();
    $envStruttureGruppiGes->parseEvent();
    return;
}

class envStruttureGruppiGes extends itaModel {

    public $ITALWEB_DB;
    public $bdaLib;
    public $nameForm = "envStruttureGruppiGes";
    public $gridMembri = "envStruttureGruppiGes_gridMembri";
    public $vStruttureGruppi;
    public $rowid;
    public $contesto;
    public $idMembroDel;
    public $Utenti_rec;

    function __construct() {
        parent::__construct();
        try {
            $this->bdaLib = new bdaLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->rowid = App::$utente->getKey($this->nameForm . '_rowid');
            $this->contesto = App::$utente->getKey($this->nameForm . '_contesto');
            $this->nuovo = App::$utente->getKey($this->nameForm . '_nuovo');
            $this->idMembroDel = App::$utente->getKey($this->nameForm . '_idMembroDel');
            $this->Utenti_rec = App::$utente->getKey($this->nameForm . '_Utenti_rec');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowid', $this->rowid);
            App::$utente->setKey($this->nameForm . '_contesto', $this->contesto);
            App::$utente->setKey($this->nameForm . '_nuovo', $this->nuovo);
            App::$utente->setKey($this->nameForm . '_idMembroDel', $this->idMembroDel);
            App::$utente->setKey($this->nameForm . '_Utenti_rec', $this->Utenti_rec);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->rowid = $_POST['rowid'];
                $Nuovo = $_POST['Nuovo'];
                $this->nuovo = $Nuovo;

                $this->initialize();

                break;

            case 'dbClickRow':
            case 'editGridRow':
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridMembri:
                        $this->idMembroDel = $_POST['rowid'];
                        Out::msgQuestion("Conferma", "Confermi la cancellazione ?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaMembro', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridMembri:
                        // $sql = "SELECT * FROM FNG_MEMBRI WHERE idgruppo = '" . $this->rowid . "' AND trashed != 1";
                        $sql = "SELECT * FROM FNG_MEMBRI WHERE IDGRUPPO = " . $this->rowid;
                        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);

                        $membri = array();
                        foreach ($tab as $rec) {
                            $membri[] = $rec['USERNAME'];
                        }

                        $options['escludiByUser'] = $membri;

                        $this->dialogAddUtente($options);
                        break;
                }
                break;

            case 'printTableToHTML':
                break;

            case 'exportTableToExcel':
                break;

            case 'onChange':

                switch ($_POST['id']) {
                    case $this->nameForm . '_FNG_GRUPPO[CONTESTO]':

                        $res = $this->aggiornaContesto($_POST['envStruttureGruppiGes_FNG_GRUPPO']['CONTESTO']);
                        $this->comboPadreVociIndice($_POST['envStruttureGruppiGes_FNG_GRUPPO']['CONTESTO']);
                        break;


                    default:

                        $Tipo = explode("_", $_POST['id']);
                        $Rowid = $Tipo[0];
                        $Tipo = $Tipo[1];

                        foreach ($this->Utenti_rec as $key => &$utenti) {
                            switch ($Tipo) {
                                case 'VALIDODAL':
                                    if ($utenti['ID'] == $Rowid) {
                                        $utenti['DATAINI'] = $_POST[$Rowid . "_" . $Tipo];
                                        break;
                                    }
                                case 'VALIDOAL':
                                    if ($utenti['ID'] == $Rowid) {
                                        $utenti['DATAEND'] = $_POST[$Rowid . "_" . $Tipo];
                                        break;
                                    }
                                case 'TRASHED':
                                    if ($utenti['ID'] == $Rowid) {
                                        $utenti['TRASHED'] = $_POST[$Rowid . "_" . $Tipo];
                                        break;
                                    }
                            }
                            $sql = "SELECT * FROM FNG_MEMBRI WHERE ID = " . $Rowid;
                            $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);

                            $date = date('Ymd');
                            if (($this->Utenti_rec['TRASHED'] == 1) && ($tab['TRASHED'] == 0) && (!$tab['DATAEND'])) {
                                $this->Utenti_rec['DATAEND'] = $date;
                            }
                        }
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridMembri:
                        //   $sql = "SELECT * FROM FNG_MEMBRI WHERE idgruppo = '" . $this->rowid . "' AND trashed != 1";
                        $sql = "SELECT * FROM FNG_MEMBRI ";
                        if ($this->rowid) {
                            $sql .= " WHERE IDGRUPPO =" . $this->rowid;
                        }

                        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
                        $this->Utenti_rec = $tab;
                        $membri = array();
                        foreach ($tab as $rec) {
                            $chiave = $rec['ID'];
                            $membri[$chiave] = $rec['USERNAME'];
                        }

                        $options['filtroByUser'] = $membri;
                        $options['userAsID'] = true;
                        $utenti = $this->bdaLib->getUtentiItaEngine(array(), $options);
                        foreach ($utenti as &$ute) {
                            $ute = array_change_key_case($ute, CASE_UPPER);
                        }


                        $app = $utenti;
                        $utenti = array();
                        foreach ($app as $cur) {
                            $chiave = $cur['ID'];
                            $utenti[$chiave] = $cur;
                        }

                        $ResultTab = array();

                        foreach ($membri as $key => $cur) {
                            $ResultTab[$key] = array(
                                'ID' => $key,
                                'RICCOG' => $utenti[$cur]['RICCOG'],
                                'RICNOM' => $utenti[$cur]['RICNOM'],
                                'UTELOG' => $utenti[$cur]['UTELOG'],
                            );
                        }

                        foreach ($tab as $tab1) {
                            foreach ($ResultTab as $key => &$ResultTab1) {
                                if ($ResultTab1['ID'] == $tab1['ID']) {
                                    $ResultTab[$key]['DATAINI'] = $tab1['DATAINI'];
                                    $ResultTab[$key]['DATAEND'] = $tab1['DATAEND'];
                                    $ResultTab[$key]['TRASHED'] = $tab1['TRASHED'];
                                }
                            }
                        }

                        $ResultTab = $this->ElaboraRecord($ResultTab);
                        TableView::clearGrid($this->gridMembri);
                        $ita_grid01 = new TableView($_POST['id'], array(
                            'arrayTable' => $ResultTab,
                        ));


                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        TableView::enableEvents($this->gridMembri);
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaAddMembro':
                        $datiForm = $this->formData[$this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO'];

                        $rec = array();
                        $rec['IDGRUPPO'] = $this->rowid;
                        $rec['USERNAME'] = $datiForm['UTELOG'];
                        $rec['TRASHED'] = 0;
                        //  ItaDB::DBInsert($this->ITALWEB_DB, 'FNG_MEMBRI', 'id', $rec);
                        $insert_Info = "Inserimento membro : " . $rec['USERNAME'];
                        $this->insertRecord($this->ITALWEB_DB, 'FNG_MEMBRI', $rec, $insert_Info, 'ID');
                        //AGGIORNA GRID
                        TableView::clearGrid($this->gridMembri);
                        TableView::reload($this->gridMembri);
                        break;

                    case $this->nameForm . '_ConfermaCancellaMembro':

                        $rec['ID'] = $this->idMembroDel;
                       
                        
                        $sql = "SELECT * FROM FNG_MEMBRI WHERE ID = " . $rec['ID'];
                        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);

                        if ((!$tab['DATAEND'])) {
                            $rec['DATAEND'] = date("Ymd");
                        }
                        $rec['TRASHED'] = 1;
                        $update_Info = "Aggiornamento stato membro : " . $rec['ID'];
                        $this->updateRecord($this->ITALWEB_DB, 'FNG_MEMBRI', $rec, $update_Info, 'ID');

                        TableView::reload($this->gridMembri);
                        break;

                    case $this->nameForm . '_Nuovo':
                        break;

                    case $this->nameForm . '_Inserisci':
                        $rec = array();
                        $rec = $this->formData[$this->nameForm . '_FNG_GRUPPO'];

                        if (!$this->checkDatiForm($rec)) {
                            return false;
                        }
                        $rec['TRASHED'] = 0;
                        // $rec['contesto'] = $this->contesto;

                        $insert_Info = "Inserimento nuovo gruppo :" . $rec['NOME'];
                        if (!$this->insertRecord($this->ITALWEB_DB, 'FNG_GRUPPO', $rec, $insert_Info, 'ID')) {
                            Out::msgStop("Errore", "Errore salvataggio dati");
                            return false;
                        }

                        Out::msgBlock("", 2000, true, "Record registrato correttamente.");
                        $this->rowid = $this->getLastInsertId();
                        $this->nuovo = 0;
                        $this->initialize();

                        break;

                    case $this->nameForm . '_Aggiorna':

                        $rec = array();
                        $rec = $this->formData[$this->nameForm . '_FNG_GRUPPO'];


                        if (!$this->checkDatiForm($rec)) {
                            return false;
                        }

                        $rec['TRASHED'] = 0;

                        $update_Info = "Aggiornamento Gruppo id: " . $rec['ID'];
                        if (!$this->updateRecord($this->ITALWEB_DB, 'FNG_GRUPPO', $rec, $update_Info, 'ID')) {
                            Out::msgStop("Errore", "Errore salvataggio dati");
                            return false;
                        }
                        Out::msgBlock("", 2000, true, "Record aggiornato correttamente.");


                        foreach ($this->Utenti_rec as $utenti) {
                            $update_Info = "Aggiornamento membro id: " . $utenti['ID'];
                            if ($this->updateRecord($this->ITALWEB_DB, 'FNG_MEMBRI', $utenti, $update_Info, 'ID')) {

                                Out::msgBlock("", 1000, true, "Aggiornamento Utente avvenuto correttamente.");
                            }
                        }
                        TableView::reload($this->gridMembri);

                        $this->initialize();
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_rowid');
        App::$utente->removeKey($this->nameForm . '_contesto');
        App::$utente->removeKey($this->nameForm . '_nuovo');
        App::$utente->removeKey($this->nameForm . '_idMembroDel');
        App::$utente->removeKey($this->nameForm . '_Utenti_rec');
    }

    public function returnToParent($close = true) {
        $formObj = itaModel::getInstance($this->returnModel);
        if (!$formObj) {
            Out::msgStop("Errore", "Apertura fallita");
        }
        $formObj->setEvent($this->returnEvent);
        $formObj->parseEvent();
        if ($close) {
            $this->close();
        }
    }

    function checkDatiForm(&$dati) {
        if ($dati['ID'] == $dati['IDPADRE']) {
            Out::msgStop("Errore", "Errore nella selezione del livello padre");
            return false;
        }

        return true;
    }

    function comboPadreVociIndice($id = '') {
        Out::html($this->nameForm . '_FNG_GRUPPO[IDPADRE]', '');
        $this->caricaSezioniGruppi($id);
        $resultTab = array();

        $this->SezioniAmtFigli(0, 0, $resultTab);

        unset($resultTab[0]);

        Out::select($this->nameForm . '_FNG_GRUPPO[IDPADRE]', 1, "", '', '');
        Out::select($this->nameForm . '_FNG_GRUPPO[IDPADRE]', 1, 0, '', 'Livello radice', 'font-style: italic;');
        foreach ($resultTab as $key => $cur) {
            Out::select($this->nameForm . '_FNG_GRUPPO[IDPADRE]', 1, $cur['ID'], '', $cur['NOME']);
        }
    }

    function caricaSezioniGruppi($id = '') {
        // CARICA INDICE
        $this->vStruttureGruppi = array(
            0 => array(
                'ID' => 0,
                'VOCE' => 'Livello radice',
                'FIGLI' => array()
            )
        );

        if (!$id) {
            if ($this->nuovo == 1) {
                $sql = "SELECT *, (ID + (IDPADRE*10000)) AS ORDINECALC FROM FNG_GRUPPO WHERE CONTESTO = '' AND TRASHED != 1   ORDER BY ORDINECALC";
            } else {
                $sql = "SELECT *, (ID + (IDPADRE*10000)) AS ORDINECALC FROM FNG_GRUPPO WHERE  TRASHED != 1   ORDER BY ORDINECALC";
            }
        } else {
            $sql = "SELECT *, (ID + (IDPADRE*10000)) AS ORDINECALC FROM FNG_GRUPPO WHERE CONTESTO ='" . $id . "'  AND  TRASHED != 1   ORDER BY ORDINECALC";
        }
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        foreach ($tab as $cur) {
            $padre = $cur['IDPADRE'];
            $chiave = $cur['ID'];
            $this->vStruttureGruppi[$chiave] = $cur;
            $this->vStruttureGruppi[$padre]['FIGLI'][] = $chiave;
        }
        return true;
    }

    function caricaContesti() {
        $env = new envLibStrutture();
        $env_const = $env::getCONTEXT_LIST();
        foreach ($env_const as $context) {
            Out::select($this->nameForm . '_FNG_GRUPPO[CONTESTO]', 1, $context['CODICE'], "0", $context['DESCRIZIONE']);
        }
        Out::valore($this->nameForm . '_FNG_GRUPPO[CONTESTO]', '');
    }

    function SezioniAmtFigli($id, $deep, &$resultTab) {
        $cur = $this->vStruttureGruppi[$id];
        $elem['NOME'] = str_repeat("&nbsp;", 4 * ($deep - 1)) . '&mdash; ' . $cur['NOME'];
        $elem['ID'] = $cur['ID'];

        if ($this->rowid && $this->rowid == $id) {
            return false;
        }

        $resultTab[] = $elem;
        if (isset($this->vStruttureGruppi[$id]['FIGLI'])) {
            foreach ($this->vStruttureGruppi[$id]['FIGLI'] as $cur) {
                $this->SezioniAmtFigli($cur, $deep + 1, $resultTab);
            }
        }
    }

    function dialogAddUtente($options = array()) {
        $fields = array(
            array(
                'label' => array(
                    'value' => "Utente",
                    'style' => 'width: 50px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'name' => $this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]',
                'type' => 'select',
                'class' => 'ita-select',
                'style' => 'margin-left: 5px; width: 120px;'
            )
        );

        $header = "Associa utente al gruppo";

        $vBottoni = array(
            'F5-Conferma' => array(
                'id' => $this->nameForm . '_ConfermaAddMembro',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f5",
                'style' => "height:20px;"
            ),
            'F8-Annulla' => array(
                'id' => $this->nameForm . '_AnnullaAddMembro',
                'model' => $this->nameForm,
                'class' => 'ita-button',
                'shortCut' => "f8",
                'style' => "height:20px;"
        ));

        Out::msgInput('Associa utente', $fields, $vBottoni, $this->nameForm . "_workSpace", 'auto', 'auto', true, $header);

        $utenti = $this->bdaLib->getUtentiItaEngine($gruppi, $options);
        foreach ($utenti as $utente) {
            $utente = array_change_key_case($utente, CASE_UPPER);
            Out::select($this->nameForm . '_STRUTTURA_GRUPPO_MEMBRO[UTELOG]', 1, $utente['UTELOG'], 0, $utente['UTELOG']);
        }
    }

    function aggiornaContesto($id) {


        $sql = "SELECT * FROM FNG_GRUPPO ";
        if ($id) {
            $where = " WHERE CONTESTO='" . $id . "'";
        }
        $sql .= $where;

        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        return $tab;
    }

    function ElaboraRecord($Utenti_tab) {

        foreach ($Utenti_tab as $key => $Utenti_rec) {
            $properties_checkbox_trashed = array('size' => 8, 'value' => $Utenti_rec['TRASHED']);
            if ($Utenti_rec['TRASHED'] == 1) {
                $properties_checkbox_trashed['checked'] = true;
            }
            $Utenti_tab[$key]['DATAINI'] = "<div class='ita-html'>" . itaComponents::getHtmlItaDatepicker(array(
                        "id" => $Utenti_rec['ID'] . '_VALIDODAL',
                        "dataini" => 'utenti_rec[' . $Utenti_rec['DATAINI'] . '][DATAINI]',
                        "properties" => array('size' => 8, 'value' => $Utenti_rec['DATAINI']),
                    )) . "</div>";
            $Utenti_tab[$key]['DATAEND'] = "<div class='ita-html'>" . itaComponents::getHtmlItaDatepicker(array(
                        "id" => $Utenti_rec['ID'] . '_VALIDOAL', "properties" => array('size' => 8, 'value' => $Utenti_rec['DATAEND'])
                    )) . "</div>";
            $Utenti_tab[$key]['TRASHED'] = "<div class='ita-html'>" . itaComponents::getHtmlItaCheckbox(array(
                        "id" => $Utenti_rec['ID'] . '_TRASHED', "properties" => $properties_checkbox_trashed
                    )) . "</div>";
        }
        return $Utenti_tab;
    }

    public function initialize() {

        $this->caricaContesti();
        if ($this->nuovo != 1) {
            $rec = array();
            $sql = "SELECT * FROM FNG_GRUPPO WHERE ID='" . $this->rowid . "'";
            $rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);

            TableView::enableEvents($this->gridMembri);
            TableView::reload($this->gridMembri);
        } else {
            Out::hide($this->nameForm . '_divGridMembri');
        }

        $this->comboPadreVociIndice();

        if ($this->nuovo == 1) {
            Out::hide($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_Inserisci');
        } else {
            Out::hide($this->nameForm . '_Inserisci');
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm . '_divGridMembri');

            Out::valori($rec, $this->nameForm . '_FNG_GRUPPO');
        }
    }

}
