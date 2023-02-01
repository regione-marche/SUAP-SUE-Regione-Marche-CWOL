<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ici/iciLib.class.php';

function utiVediDichiarazioni() {
    $utiVediDichiarazioni = new utiVediDichiarazioni();
    $utiVediDichiarazioni->parseEvent();
    return;
}

class utiVediDichiarazioni extends itaModel {

    public $ANEL_DB;
    public $ICI_DB;
    public $iciLib;
    public $nameForm = "utiVediDichiarazioni";
    public $divGes = "utiVediDichiarazioni_divGestione";
    public $gridDichiarazioni = "utiVediDichiarazioni_gridDichiarazioni";
    public $gridVariazioni = "utiVediDichiarazioni_gridVariazioni";
    public $cf;

    function __construct() {
        parent::__construct();
        try {
            $this->catLib = new catLib();
            $this->iciLib = new iciLib();
            $this->ICI_DB = $this->iciLib->getICIDB();
            $this->ANEL_DB = $this->catLib->getANELDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $sql = $this->CreaSqlDichiarazioni($_POST['CF']);
                $dicdet_anni = ItaDB::DBSQLSelect($this->ICI_DB, $sql, true);
                if (!$dicdet_anni) {
                    Out::msgInfo("Info", "Nessuna dichiarazione trovata per il Contribuente con CF. " . $_POST['CF']);
                    $this->close();
                    break;
                }
                $this->Dettaglio($_POST['CF']);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'printTableToHTML':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'onChange':
                break;
            case 'onClick':
                switch ($_POST['id']) {
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
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function Dettaglio($CF) {

        setlocale(LC_MONETARY, 'it_IT');
        // ----------------------------------------------- TABELLA DICHIARAZIONI
        $i = 0;
        $matrice = array();
        $sql = $this->CreaSqlDichiarazioni($CF);
        $dicdet_anni = ItaDB::DBSQLSelect($this->ICI_DB, $sql, true);
        if ($dicdet_anni) {
            foreach ($dicdet_anni as $dicdet_anno) {
                $i = $i + 1;
                $matrice[$i]['ID'] = 'ANNO' . $dicdet_anno['DDEANN'];
                $matrice[$i]['level'] = 1;
                $matrice[$i]['parent'] = 0;
                $matrice[$i]['isLeaf'] = 'false';
//                if ($ramo == 'dich' && $dicdet_anno['DDEANN'] == $annoRamo) {
//                    $matrice[$i]['expanded'] = 'true';
//                } else {
//                    $matrice[$i]['expanded'] = 'false';
//                }
                $matrice[$i]['loaded'] = 'true';
                //$matrice[$i]['DDEANN'] = "<span style=\"color: green;\">" . $dicdet_anno['DDEANN'] . "</span>";
                $matrice[$i]['DDEANN'] = $dicdet_anno['DDEANN'];
                $matrice[$i]['SEGNALA'] = '';
                $sql = $this->CreaSqlDichAnno($CF, $dicdet_anno['DDEANN']);
                $dicdet_tab = ItaDB::DBSQLSelect($this->ICI_DB, $sql, true);
                $dicdet_tab = $this->elaboraRecords($dicdet_tab);
//                $imposta = 0;
//                foreach ($dicdet_tab as $dicdet_rec) {
//                    if ($dicdet_rec['DDEDCA'] == 0) {
//                        $imposta = $imposta + $dicdet_rec['IMPOSTA'];
//                    }
//                }
                //$matrice[$i]['IMPOSTA'] = "<p style = 'text-align: right;'>" . money_format('%!.2n', $imposta);
                // ------------------- controllo se per l'anno in corso versato diverso da imposta
                $sql = $this->creaSqlVersamentiContr($CF, $dicdet_anno['DDEANN']);
                $versam_tab = ItaDB::DBSQLSelect($this->ICI_DB, $sql, true);
//                $versato = 0;
//                foreach ($versam_tab as $versam_rec) {
//                    $versato = $versato + $versam_rec['VERTOT'];
//                }
//                if ((int) $imposta > (int) $versato) {
//                    //$matrice[$i]['DDEANN'] = "<span style=\"color: red;\">" . $dicdet_anno['DDEANN'] . "</span>";
//                    $matrice[$i]['SEGNALA'] = '<span class="ita-icon ita-icon-bullet-red-16x16"></span>';
//                }
                // -------------------
//                $tariff_rec = $this->iciLib->GetTariff($dicdet_anno['DDEANN'] . "BASE", 'codice');
                foreach ($dicdet_tab as $dicdet_rec) {
                    $i = $i + 1;
                    $matrice[$i]['level'] = 2;
                    $matrice[$i]['parent'] = 'ANNO' . $dicdet_anno['DDEANN'];
                    $matrice[$i]['isLeaf'] = 'true';
                    // $matrice[$i]['expanded'] = 'false';
                    $matrice[$i]['loaded'] = 'false';
                    $matrice[$i]['DDETIP'] = $dicdet_rec['DDETIP'];
                    $matrice[$i]['DDRFLG'] = $dicdet_rec['DDRFLG'];
                    $matrice[$i]['DDEFOG'] = $dicdet_rec['DDEFOG'];
                    $matrice[$i]['DDENUM'] = $dicdet_rec['DDENUM'];
                    $matrice[$i]['DDESUB'] = $dicdet_rec['DDESUB'];
                    $matrice[$i]['DDECAT'] = "<p style = 'text-align: center;'>" . $dicdet_rec['DDECAT'] . "</p>";
                    $matrice[$i]['DDEFLN__2'] = "<p style = 'text-align: center;'>" . $dicdet_rec['DDEFLN__2'] . "</p>";
                    $matrice[$i]['DDEVAL'] = $dicdet_rec['DDEVAL'];
                    $matrice[$i]['DDEPER'] = $dicdet_rec['DDEPER'];
                    $matrice[$i]['DDEMMP'] = $dicdet_rec['DDEMMP'];
                    $matrice[$i]['DDEDET'] = $dicdet_rec['DDEDET'];
                    $matrice[$i]['DDEIND'] = $dicdet_rec['DDEIND'];
                    $matrice[$i]['DDEDIR'] = $dicdet_rec['DDEDIR'];
                    //$matrice[$i]['IMPOSTA'] = "<p style = 'color:red;font-size:1.2em; text-align: right;'>" . money_format('%!.2n', $dicdet_rec['IMPOSTA']) . "</p>";
                    if ($dicdet_rec['DDRFLG']) {
                        $sw_imm_ret = $this->iciLib->GetUltimoTabret($dicdet_rec);
                        $tabretRec = $this->iciLib->GetTabretRowid($sw_imm_ret);
                        $matrice[$i]['DDEVAL'] = $tabretRec['DDRVAL'];
                        $matrice[$i]['DDEPER'] = $tabretRec['DDRPER'];
                        $matrice[$i]['DDEMMP'] = $tabretRec['DDRMMP'];
                        if ($tabretRec['DDRDET'] != 0) {
                            //$matrice[$i]['DDEDET'] = round($tariff_rec['TARVAL__3'] / 12 * $tabretRec['DDRMMP'] * $tabretRec['DDRPER'] / 100, 2);
                            $matrice[$i]['DDEDET'] = $tabretRec['DDRDET'];
                        } else {
                            $matrice[$i]['DDEDET'] = 0;
                        }
                        //$matrice[$i]['DDEDET'] = $tabretRec['DDRDET'];
                        $matrice[$i]['DDECAT'] = "<p style = 'text-align: center;'>" . $tabretRec['RETCAT'] . "</p>";
                        $matrice[$i]['DDEIND'] = $tabretRec['RETIND'];
                        $matrice[$i]['DDEFOG'] = $tabretRec['RETFOG'];
                        $matrice[$i]['DDENUM'] = $tabretRec['RETNUM'];
                        $matrice[$i]['DDESUB'] = $tabretRec['RETSUB'];
                    }
                    $matrice[$i]['ID'] = $dicdet_rec['ROWID'];
                }
            }
            $arrayDichiarazioni = $matrice;
            $this->CaricaGriglia($this->gridDichiarazioni, $arrayDichiarazioni);
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10, $caption = '') {
        if ($caption) {
            Out::codice("$('#$griglia').setCaption('$caption');");
        }

        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($appoggio));
        } else if ($tipo == '3') {
            if ($_POST['page'] == "") {
                $ita_grid01->setPageNum(1);
            } else {
                $ita_grid01->setPageNum($_POST['page']);
            }
            $ita_grid01->setPageRows($_POST['rows']);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        $ita_grid01->getDataPage('json');
        return;
    }

    public function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            switch ($Result_rec['DDETIP']) {
                case 1:
                    $Result_tab[$key]["DDETIP"] = 'Terreno';
                    break;
                case 2:
                    $Result_tab[$key]["DDETIP"] = 'Area';
                    break;
                case 3:
                    $Result_tab[$key]["DDETIP"] = 'Fabbricato';
                    if ($Result_tab[$key]["DDEFLA__2"] == 1) {
                        $Result_tab[$key]["DDETIP"] = 'Fabbricato ' . "<span style=\"color: red !important;\">(R)</span>";
                    }
                    break;
                case 4:
                    $Result_tab[$key]["DDETIP"] = 'Fabbricato con Valore';
                    break;
            }
            $finePossesso = $locato = '';
            if ($Result_rec['DDEFL1']) {
                $finePossesso = '<span class="ita-icon ita-icon-lock-16x16" style="float:left;"></span>';
            }
            if ($Result_rec['DDEFLN__3'] || $Result_rec['DDEFLN__4']) {
                $locato = '<span class="ita-icon ita-icon-user-16x16" style="float:left;"></span>';
            }

            $Result_tab[$key]["DDETIP"] = $finePossesso . $locato . $Result_tab[$key]["DDETIP"];

            if ($Result_rec['DDEDCA'] == 1) {
                $Result_tab[$key]["DDETIP"] = "<span style=\"color: green;\">" . $Result_tab[$key]["DDETIP"] . "</span>";
            }
            if ($Result_rec['DDEFLN__2'] == 0) {
                $Result_tab[$key]["DDEFLN__2"] = "";
            } else {
                $Result_tab[$key]["DDEFLN__2"] = "X";
            }
            $Result_tab[$key]["DDEVAL"] = $Result_rec['DDEVAL'];
            if ($Result_rec['DDRFLG'] == 0) {
                $Result_tab[$key]["DDRFLG"] = '';
            } else {
                $Result_tab[$key]["DDRFLG"] = "<span style=\"color: orange;\">Rettifica</span>";
            }
            if ($Result_rec['DDRNOD'] == 1) {
                $Result_tab[$key]["DDRFLG"] = "<span style=\"color: red;\">Omessa</span>";
            }
        }
        return $Result_tab;
    }

    function creaSqlDichiarazioni($cf) {
        $where = $sql = "";
        $sql = "SELECT DISTINCT DICDET.DDEANN AS DDEANN FROM DICDET WHERE DDECFI = '" . $cf . "' ORDER BY DDEANN DESC";
        return $sql;
    }

    function creaSqlDichAnno($cf, $anno) {
        $where = $sql = "";
        $sql = "SELECT * FROM DICDET WHERE DDECFI = '" . $cf . "' AND DDEANN = '" . $anno . "' ORDER BY DDEDCA, DDETIP, DDEFOG, DDENUM, DDESUB";
        return $sql;
    }

    function creaSqlVersamentiContr($cf, $anno) {
        $where = $sql = "";
        $sql = "SELECT * FROM VERSAM WHERE VERCFI = '" . $cf . "'";
        if ($anno != '') {
            $sql .= " AND VERANN = '" . $anno . "'";
        }
        return $sql;
    }

}

?>
