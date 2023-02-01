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
 * @version    07.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibStrutture.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

function envStruttureGruppi() {
    $envStruttureGruppi = new envStruttureGruppi();
    $envStruttureGruppi->parseEvent();
    return;
}

class envStruttureGruppi extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = "envStruttureGruppi";
    public $gridStruttureGruppi = "envStruttureGruppi_gridStruttureGruppi";
    public $vStruttureGruppi = array();
    public $gridFilters = array();
    public $contesto;
    public $idDelete;

    function __construct() {
        parent::__construct();
        try {
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->contesto = App::$utente->getKey($this->nameForm . '_contesto');
            $this->idDelete = App::$utente->getKey($this->nameForm . '_idDelete');

            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
        App::$utente->setKey($this->nameForm . '_contesto', $this->contesto);
        App::$utente->setKey($this->nameForm . '_idDelete', $this->idDelete);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                //IMPOSTO IL CONTESTO DELLA STRUTTURA
                $this->contesto = $_POST['contesto'];
                $this->CreaComboThreashed();
                if ($this->contesto == "") {
                    $this->contesto = envLibStrutture::CONTEXT_BDAP;
                }

                $this->mostraGridStrutturaGruppi();
                $this->gridFilters = array();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridStruttureGruppi:
                        $model = "envStruttureGruppiGes";
                        $_POST['rowid'] = $_POST['rowid'];
                        $_POST['contesto'] = $this->contesto;
                        $_POST['Nuovo'] = 0;
                        itaLib::openDialog($model);

                        $objGruppo = itaModel::getInstance($model);
                        $objGruppo->setReturnModel($this->nameForm);
                        $objGruppo->setReturnEvent('returnVoceStruttura');
                        if (!$objGruppo) {
                            break;
                        }
                        $objGruppo->setEvent('openform');
                        $objGruppo->parseEvent();
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridStruttureGruppi:
                        $this->idDelete = $_POST['rowid'];
                        Out::msgQuestion("Conferma", "Confermi la cancellazione ?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaStruttura', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridStruttureGruppi:

                        $model = "envStruttureGruppiGes";
                        $_POST['rowid'] = '';
                        $_POST['Nuovo'] = 1;
                        $_POST['contesto'] = $this->contesto;
                        itaLib::openDialog($model);

                        $objGruppo = itaModel::getInstance($model);
                        $objGruppo->setReturnModel($this->nameForm);
                        $objGruppo->setReturnEvent('returnVoceStruttura');
                        if (!$objGruppo) {
                            break;
                        }
                        $objGruppo->setEvent('openform');
                        $objGruppo->parseEvent();
                        break;
                }
                break;

            case 'printTableToHTML':
                break;

            case 'exportTableToExcel':
                break;

            case 'onClickTablePager':

                switch ($_POST['id']) {
                    case $this->gridStruttureGruppi:
                        $this->setGridFilters();
                        TableView::clearGrid($this->gridStruttureGruppi);
                        $this->caricaTabStruttureGruppi($_POST['trashed']);
                        $Result_tab = $this->elaboraGridStruttureGruppi();

                        $ita_grid01 = new TableView($_POST['id'], array(
                            'arrayTable' => $Result_tab,
                        ));

                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex('');
                        $ita_grid01->setSortOrder('asc');
                        $ita_grid01->getDataPage('json');
                        TableView::enableEvents($this->gridStruttureGruppi);
                        break;
                }
                break;


            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaCancellaStruttura':
                        $rec['ID'] = $this->idDelete;
                        $rec['TRASHED'] = 1;
                        $rec['UTENTE_UMOD'] = App::$utente->getKey('idUtente');
                        $rec['DATA_UMOD'] = date("YmdHis");


                        //CONTROLLARE CHE IL GRUPPO NON SIA GIA' USATO E SE USATO CHE SIA TRASHED = 1 ALTRIMENTI NON CANCELLABILE
                        $Bloccato = false;
                        $sql = "SELECT * FROM FNG_SECOBJ WHERE GRUPPO_ID='" . $rec['ID'] . "'";
                        $Fng_secoobj_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
                        foreach ($Fng_secoobj_tab as $Fng_secoobj_rec) {
                            if ($Fng_secoobj_rec) {
                                if ($Fng_secoobj_rec['TRASHED'] == 0) {
                                    Out::msgInfo("Attenzione", "Non è possibile cancellare il gruppo perchè al momento in uso");
                                    $Bloccato = true;
                                    break;
                                }
                            }
                        }
                        if ($Bloccato == false) {
                            $update_Info = "Aggiornamento stato struttura id: " . $rec['ID'];
                            $this->updateRecord($this->ITALWEB_DB, 'FNG_GRUPPO', $rec, $update_Info, 'ID');
                        }

                        TableView::reload($this->gridStruttureGruppi);
                        break;
                }
                break;

            case 'onBlur':
                break;

            case 'returnVoceStruttura':
                TableView::reload($this->gridStruttureGruppi);
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_gridFilters');
        App::$utente->removeKey($this->nameForm . '_contesto');
        App::$utente->removeKey($this->nameForm . '_idDelete');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    function mostraGridStrutturaGruppi() {
        TableView::enableEvents($this->gridStruttureGruppi);
        TableView::reload($this->gridStruttureGruppi);
    }

    function caricaTabStruttureGruppi($type = '0') {

        $this->vStruttureGruppi = array(
            0 => array(
                'FIGLI' => array()
            )
        );

        $sql = "SELECT *, ( ID + ( IDPADRE * 10000 ) ) AS ORDINECALC FROM FNG_GRUPPO ";
        if ($type == '0') {
            $where = " WHERE TRASHED != 1";
        }
        if ($type == '1') {
            $where = " WHERE TRASHED != 0";
        }
        $sql .= $where;

        $filters = $this->gridFilters;

        if ($filters) {
            foreach ($filters as $key => $value) {
                $value = strtoupper($value);
                if ($key == 'NOME') {
                    $sql .= " AND UPPER (NOME) LIKE  '%$value%' ";
                }
                if ($key == 'DESCRIZIONE') {
                    $sql .= " AND  UPPER (DESCRIZIONE) LIKE '%$value%' ";
                }
                if ($key == 'CONTESTO') {
                    $sql .= " AND  UPPER (CONTESTO) LIKE '%$value%' ";
                }
            }
        }

        $sql .= " ORDER BY ORDINECALC";
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);

        foreach ($tab as $cur) {
            $padre = $cur['IDPADRE'];
            $chiave = $cur['ID'];
            $this->vStruttureGruppi[$chiave] = $cur;
            $this->vStruttureGruppi[$padre]['FIGLI'][] = $chiave;
        }

        return true;
    }

    function elaboraGridStruttureGruppi() {

        $resultTab = array();
        foreach ($this->vStruttureGruppi[0]['FIGLI'] as $cur) {
            $this->StruttureGruppiFigli($cur, 0, $resultTab);
        }
        foreach ($resultTab as $key => $resultRec) {
            $properties_checkbox_super = array('size' => 8, 'readonly' => true, 'disabled' => true, 'value' => $resultRec['SUPER']);
            if ($resultRec['SUPER'] == 1) {
                $properties_checkbox_super['checked'] = true;
            }
            $resultTab[$key]['SUPER'] = "<div class='ita-html'>" . itaComponents::getHtmlItaCheckbox(array(
                        "id" => $resultRec['ID'] . '_SUPER', "properties" => $properties_checkbox_super
                    )) . "</div>";
        }

        $stile_I = "<div style='color:#ff0000;text-decoration: line-through;'>";
        $stile_E = "</div>";


        foreach ($resultTab as &$res) {
            if ($res['TRASHED'] == 1) {
                $res['NOME'] = $stile_I . $res['NOME'] . $stile_E;
                $res['DESCRIZIONE'] = $stile_I . $res['DESCRIZIONE'] . $stile_E;
                $res['CONTESTO'] = $stile_I . $res['CONTESTO'] . $stile_E;
                $res['TRASHED'] = $stile_I . $res['TRASHED'] . $stile_E;
            }
        }

        return $resultTab;
    }

    function StruttureGruppiFigli($id, $deep, &$resultTab) {
        $cur = $this->vStruttureGruppi[$id];
        $elem['LIVELLO'] = $deep + 1;
        $elem['NOME'] = str_pad("", $deep * 5 * 6, "&nbsp;") . $cur['NOME'];
        $elem['DESCRIZIONE'] = $cur['DESCRIZIONE'];
        $elem['CONTESTO'] = $cur['CONTESTO'];
        $elem['ID'] = $cur['ID'];
        $elem['SUPER'] = $cur['SUPER'];
        $elem['TRASHED'] = $cur['TRASHED'];
        $elem['MEMBRI'] = $this->getCountMember($cur['ID']);

        $resultTab[] = $elem;
        if (isset($this->vStruttureGruppi[$id]['FIGLI'])) {
            foreach ($this->vStruttureGruppi[$id]['FIGLI'] as $cur) {
                $this->StruttureGruppiFigli($cur, $deep + 1, $resultTab);
            }
        }
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['_search'] == true) {
            if ($_POST['NOME'] != '') {
                $this->gridFilters['NOME'] = $_POST['NOME'];
            }
            if ($_POST['DESCRIZIONE'] != '') {
                $this->gridFilters['DESCRIZIONE'] = $_POST['DESCRIZIONE'];
            }
            if ($_POST['CONTESTO'] != '') {
                $this->gridFilters['CONTESTO'] = $_POST['CONTESTO'];
            }
            if ($_POST['TRASHED'] != '') {
                $this->gridFilters['TRASHED'] = $_POST['TRASHED'];
            }
        }
    }

    public function CreaComboThreashed() {

        Out::select('gs_trashed', 1, "2", "1", "Tutti");
        Out::select('gs_trashed', 1, "1", "1", "Non Attivi");
        Out::select('gs_trashed', 1, "0", "1", "Attivi");
    }

    public function getCountMember($id) {
        $sql = "SELECT * FROM FNG_MEMBRI WHERE IDGRUPPO = $id AND TRASHED = 0";
        $tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        foreach ($tab as $key => $tab_rec) {
            if ($tab_rec['DATAEND']) {
                if ($tab_rec['DATAEND'] < date("Ymd")) {
                    unset($tab[$key]);
                }
            }
        }
        $totale = count($tab);
        return $totale;
    }

}
