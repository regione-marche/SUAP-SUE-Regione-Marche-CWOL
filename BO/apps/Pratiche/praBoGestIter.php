<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praBoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPasso.php';

function praBoGestIter() {
    $praBoGestIter = new praBoGestIter();
    $praBoGestIter->parseEvent();
    return;
}

class praBoGestIter extends itaModel {

    public $PRAM_DB;
    public $nameForm = "praBoGestIter";
    public $divGes = "praBoGestIter_divGestione";
    public $divRis = "praBoGestIter_divRisultato";
    public $divRic = "praBoGestIter_divRicerca";
    public $gridIter = "praBoGestIter_gridIter";
    public $ProNum;
    public $Dir;
    public $Key;
    public $praLib;
    public $praBoLib;
    public $ModelChiamante;
    public $praPassi;
    public $praPerms;
    public $Propas_key;
    public $Propas;

    function __construct() {
        parent::__construct();
        try {
            $this->ProNum = App::$utente->getKey($this->nameForm . '_ProNum');
            $this->ModelChiamante = App::$utente->getKey($this->nameForm . '_ModelChiamante');
            $this->Dir = App::$utente->getKey($this->nameForm . '_Dir');
            $this->Key = App::$utente->getKey($this->nameForm . '_Key');
            $this->praPassi = App::$utente->getKey($this->nameForm . '_praPassi');
            $this->Propas_key = App::$utente->getKey($this->nameForm . '_Propas_key');
            $this->Propas = App::$utente->getKey($this->nameForm . '_Propas');
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->praLib = new praLib();
            $this->praPerms = new praPerms();
            $this->praBoLib = new praBoLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ProNum', $this->ProNum);
            App::$utente->setKey($this->nameForm . '_ModelChiamante', $this->ModelChiamante);
            App::$utente->setKey($this->nameForm . '_Dir', $this->Dir);
            App::$utente->setKey($this->nameForm . '_Key', $this->Key);
            App::$utente->setKey($this->nameForm . '_praPassi', $this->praPassi);
            App::$utente->setKey($this->nameForm . '_Propas_key', $this->Propas_key);
            App::$utente->setKey($this->nameForm . '_Propas', $this->Propas);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->ProNum = $_POST['ProNum'];
                $this->Dir = $_POST['Dir'];
                $this->ModelChiamante = $_POST['Model'];
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        $rigaSel = $_POST[$this->gridIter]['gridParam']['selrow'];
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $propas_rec = $this->praLib->GetPropas($rowid, "rowid");
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST['praReadonly'] = false;
                        $_POST['selRow'] = $rigaSel;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->ProNum, 4) . "/" . substr($this->ProNum, 0, 4);
                        $_POST['passi'] = $this->praPassi;
                        itaLib::openForm($model);
                        /* @var $objmac praBoGestIter */
                        $objpra = itaModel::getInstance($model);
                        if (!$objpra) {
                            return;
                        }
                        $objpra->setEvent('openform');
                        $objpra->parseEvent();
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        $model = 'praPasso';
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['procedimento'] = $this->ProNum;
                        $_POST['modo'] = "add";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Inserimento Nuovo Passo nel Fascicolo " . substr($this->ProNum, 4) . "/" . substr($this->ProNum, 0, 4);
                        itaLib::openForm($model);
                        /* @var $objmac praBoGestIter */
                        $objpra = itaModel::getInstance($model);
                        if (!$objpra) {
                            return;
                        }
                        $objpra->setEvent('openform');
                        $objpra->parseEvent();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        break;
                }
                break;

            case 'printTableToHTML':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $utiEnte = new utiEnte();
                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        $parameters = array("Sql" => $this->CreaSql(),
                            "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->PRAM_DB, 'praBoGestIter', $parameters);
                        break;
                }
                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridIter, array(
                            'sqlDB' => $this->PRAM_DB,
                            'sqlQuery' => $sql));
                        $ita_grid01->setSortIndex('CODICE');
                        $ita_grid01->exportXLS('', 'Aggregato.xls');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridIter:
                        TableView::clearGrid($this->gridIter);
                        $this->praPassi = $this->praLib->caricaPassiBO($this->ProNum);
                        if (!$this->praPerms->checkSuperUser($proges_rec)) {
                            $this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
                        }
                        $ita_grid01 = new TableView($this->gridIter, array('arrayTable' => $this->praPassi));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows(100);
                        $ita_grid01->getDataPage('json');
                        Out::show('praBoGestIter_wrapper');
                        $this->DisattivaCellStatoPasso();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaVis':
                        $model = 'praPasso';
                        $rowid = $_POST[$this->nameForm . '_Appoggio'];
                        $propas_rec = $this->praLib->GetPropas($rowid, "rowid");
                        $_POST = array();
                        $_POST['rowid'] = $rowid;
                        $_POST['modo'] = "edit";
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnMethod'] = 'returnProPasso';
                        $_POST[$model . '_title'] = "Gestione Passo seq. " . $propas_rec['PROSEQ'] . " del Fascicolo " . substr($this->ProNum, 4) . "/" . substr($this->ProNum, 0, 4);

                        itaLib::openForm($model);
                        /* @var $objmac praBoGestIter */
                        $objpra = itaModel::getInstance($model);
                        if (!$objpra) {
                            return;
                        }
                        $objpra->setEvent('openform');
                        $objpra->parseEvent();
                        break;

                    case $this->nameForm . '_ConfermaApriPasso':
                        $rowid = $_POST[$this->nameForm . '_gridIter']['gridParam']['selrow'];

                        $sql = " SELECT * FROM PROPAS WHERE ROWID=$rowid";
                        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                        $Propas_rec['PROINI'] = date('Ymd');
                        $Propas_rec['PROFIN'] = '';

                        $praPasso = new praPasso();
                        $praPasso->SincronizzaRecordPasso($Propas_rec);
                        $this->CheckAperturaPassoPadre($Propas_rec);
                        $this->RefreshPostPassi();
                        break;

                    case $this->nameForm . '_ConfermaApriPadre':
                        $sql = "SELECT * FROM PROPAS WHERE PROPAK='" . $this->Propas_key . "'";
                        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        $Propas_rec['PROINI'] = date('Ymd');
                        $praPasso = new praPasso();
                        $praPasso->SincronizzaRecordPasso($Propas_rec);
                        $this->RefreshPostPassi();
                        break;

                    case $this->nameForm . '_AzzeraDatePasso':
                        $Rowid = $_POST[$this->nameForm . '_gridIter']['gridParam']['selrow'];
                        $Propas_rec = $this->praLib->GetPropas($Rowid, 'rowid');
                        $Propas_rec['PROINI'] = $Propas_rec['PROFIN'] = '';
                        $update_Info = 'Oggetto : Aggiornamento Passo' . $Propas_rec['PRONUM'] . '/' . $Propas_rec['PROSEQ'];
                        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $Propas_rec, $update_Info)) {
                            Out::msgStop("Aggiornamento Passo", "Errore nell'aggiornamento del passo " . $Propas_rec['PROSEQ']);
                            break;
                        }
                        $this->RefreshPostPassi();
                        break;

                    case $this->nameForm . '_ChiudiPasso':
                        $Rowid = $_POST[$this->nameForm . '_gridIter']['gridParam']['selrow'];
                        $this->ChiudiPasso($Rowid);
                        if ($this->Propas) {
                            Out::msgQuestion("Attenzione", "Vi sono dei passi interni al passo in chiusura. Vuoi chiudere anche i passi interni?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaChiusura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaChiusura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        $this->RefreshPostPassi();
                        break;

                    case $this->nameForm . '_ConfermaChiusura':
                        $praPasso = new praPasso();
                        foreach ($this->Propas as $Propas_rec) {
                            if ($Propas_rec['PROINI']) {
                                $Propas_rec['PROFIN'] = date('Ymd');
                                $praPasso->SincronizzaRecordPasso($Propas_rec);
                            }
                        }
                        $this->RefreshPostPassi();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    
                }
                break;
            case 'cellSelect':
                $this->Key = '';
                $propas_rec = $this->praLib->GetPropas($_POST['rowid'], 'rowid');
                switch ($_POST['colName']) {
                    case 'VAI':
                        if ($propas_rec['PROQST'] == 0) {
                            break;
                        }
                        $propas_SI = $this->praLib->GetPropas($propas_rec['PROVPA'], 'propak');
                        $propas_NO = $this->praLib->GetPropas($propas_rec['PROVPN'], 'propak');
                        if ($propas_SI) {
                            $propas_vai = $propas_SI; //$this->praLib->GetPropas($propas_rec['PROVPA'], 'propak');
                        } elseif ($propas_NO) {
                            $propas_vai = $propas_vai = $propas_NO; //$this->praLib->GetPropas($propas_rec['PROVPA'], 'propak');
                        }
                        Out::valore($this->nameForm . '_Appoggio', $propas_vai['ROWID']);
                        Out::msgQuestion("Info Passo.", "Il passo di destinazione ha sequenza: " . $propas_vai['PROSEQ'] . "
                                <BR>" . $propas_vai['PRODPA'] . "<BR><BR>Vuoi visualizzare il passo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaVis', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaVis', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case 'PROCEDURA':
                        if ($propas_rec['PROCTR'] == "") {
                            break;
                        }
                        $anapco_rec = $this->praLib->GetAnapco($propas_rec['PROCTR']);
                        Out::msgInfo("Info Procedura.", "La procdura di controllo ha codice: " . $anapco_rec['PCOCOD'] . "
                                <BR>" . $anapco_rec['PCODES']);
                        break;
                    case 'ALLEGATI':
                        if ($propas_rec['PROALL'] == "") {
                            break;
                        }
                        $allegati = unserialize($propas_rec['PROALL']);
                        foreach ($allegati as $value) {
                            if ($value['FILEORIG']) {
                                $str = $str . "<br>" . $value['FILEORIG'];
                            } else {
                                $str = $str . "<br>" . $value['FILENAME'];
                            }
                        }

                        Out::msgInfo("Info Allegati", "<b>N. Allegati: " . count($allegati) . "</b><BR> $str");
                        break;
                    case 'STATOPASSO':
                        $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $_POST['rowid'] . "'";
                        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if ((!$Propas_rec['PROINI']) || ($Propas_rec['PROINI'] && $Propas_rec['PROFIN'])) {
                            if (!$Propas_rec['PROINI']) {
                                Out::msgQuestion("Apertura passo", "Confermi l'apertura del passo " . $Propas_rec['PRODPA'] . " ?", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                            } else {
                                Out::msgQuestion("Apertura passo", "Confermi l'apertura del passo " . $Propas_rec['PRODPA'] . " ?", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'Azzera Date' => array('id' => $this->nameForm . '_AzzeraDatePasso', 'model' => $this->nameForm, 'shortCut' => "f6"),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );
                            }
                        }
                        if ($Propas_rec['PROINI'] && !$Propas_rec['PROFIN']) {
                            Out::msgQuestion("Chiudi Passo", "Chiudere il passo " . $Propas_rec['PRODPA'] . " inserendo la data odierna e opzionalmente attivare un altro passo?", array(
                                'Annulla' => array('id' => $this->nameForm . '_AnnullaChiudiPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'Azzera Date' => array('id' => $this->nameForm . '_AzzeraDatePasso', 'model' => $this->nameForm, 'shortCut' => "f7"),
                                'Chiudi' => array('id' => $this->nameForm . '_ChiudiPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }

                        break;
                }
                break;
            case 'returnProPasso':
                $this->RefreshPostPassi();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_ProNum');
        App::$utente->removeKey($this->nameForm . '_ModelChiamante');
        App::$utente->removeKey($this->nameForm . '_Dir');
        App::$utente->removeKey($this->nameForm . '_Key');
        App::$utente->removeKey($this->nameForm . '_KeySave');
        App::$utente->removeKey($this->nameForm . '_praPassi');
        App::$utente->removeKey($this->nameForm . '_Propas_key');
        App::$utente->removeKey($this->nameForm . '_Propas');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function Nascondi() {
        Out::hide($this->divGes);
        Out::hide($this->divRis);
        Out::hide($this->divRic);
        Out::hide($this->nameForm . "_Aggiungi");
        Out::hide($this->nameForm . "_Aggiorna");
        Out::hide($this->nameForm . "_Cancella");
        Out::hide($this->nameForm . "_Elenca");
        Out::hide($this->nameForm . "_Nuovo");
        Out::hide($this->nameForm . "_AltraRicerca");
        Out::hide($this->nameForm . "_TornaElenco");
    }

    public function OpenRicerca() {
        $this->Nascondi();
        Out::show($this->divRis);
        Out::clearFields($this->nameForm);
        TableView::enableEvents($this->gridIter);
        TableView::reload($this->gridIter);
    }

    public function DisattivaCellStatoPasso() {
        foreach ($this->praPassi as $passo) {
            $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $passo['ROWID'] . "'";
            $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Propas_rec['PROVISIBILITA'] != 'Aperto') {
                $proges_rec = $this->praLib->GetProges($Propas_rec['PRONUM']);
                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                    if ($this->praPerms->checkUtenteGenerico($Propas_rec)) {
                        TableView::setCellValue($this->gridIter, $passo['ROWID'], 'STATOPASSO', "", 'not-editable-cell', '', 'false');
                    }
                }
            }
        }
    }

    public function CheckAperturaPassoPadre($Propas) {
        $sql = "SELECT * FROM PROPAS WHERE PROPAK='" . $Propas['PROKPRE'] . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($Propas_rec) {
            $this->Propas_key = $Propas['PROKPRE'];
            if (!$Propas_rec['PROINI']) {
                Out::msgQuestion("Attenzione", "Il passo antecedente di questo passo non è aperto si desidera aprirlo?", array(
                    'Non aprire' => array('id' => $this->nameForm . '_AnnullaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaApriPadre', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
            } else {
                TableView::reload($this->gridIter);
            }
        } else {
            TableView::reload($this->gridIter);
        }
    }

    public function ChiudiPasso($Rowid) {
        $sql = " SELECT * FROM PROPAS WHERE ROWID='" . $Rowid . "'";
        $Propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $praPasso = new praPasso();

        $Propas_rec['PROFIN'] = date('Ymd');
        $praPasso->SincronizzaRecordPasso($Propas_rec);

        $sql = "SELECT * FROM PROPAS WHERE PROKPRE='" . $Propas_rec['PROPAK'] . "'";
        $this->Propas = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
    }

    public function RefreshPostPassi() {
        TableView::clearGrid($this->gridIter);
        TableView::reload($this->gridIter);

        $formObj = itaModel::getInstance($this->ModelChiamante);
        if (!$formObj) {
            Out::msgStop("Errore", "apertura fallita");
        }
        $formObj->RefreshPostPassi();
    }

}

?>
