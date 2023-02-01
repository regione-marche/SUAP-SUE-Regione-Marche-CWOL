<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';

include_once ITA_BASE_PATH . '/apps/Utility/utiAnagrafe.class.php';



function utiVediAnel() {
    $utiVediAnel = new utiVediAnel();
    $utiVediAnel->parseEvent();
    return;
}

class utiVediAnel extends itaModel {

    public $ANEL_DB;
    public $nameForm = "utiVediAnel";
    public $divGes = "utiVediAnel_divGestione";
    public $divRis = "utiVediAnel_divRisultato";
    public $divRic = "utiVediAnel_divRicerca";
    public $gridFamily = "utiVediAnel_gridFamily";
    public $gridVariazioni = "utiVediAnel_gridVariazioni";
    public $cf;
    public $returnModel;
    public $returnMethod;
    public $operazione;
    public $gridAnagrafica = "utiVediAnel_gridAnagrafica";
    public $Ricerca;

    function __construct() {
        parent::__construct();
        try {
            $this->catLib = new catLib();
            $this->ANEL_DB = $this->catLib->getANELDB();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->operazione = App::$utente->getKey($this->nameForm . '_operazione');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_operazione', $this->operazione);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                $this->cf = $_POST['cf'];
                $this->Ricerca = $_POST['Ricerca'];
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->creaCombo();
                $this->nascondi();
                Out::show($this->nameForm . '_DivRicerca');
                Out::hide($this->nameForm . '_AssociaFamiglia');
                $this->operazione = $_POST['operazione'];
                if ($_POST['operazione'] == 'associa') {
                    Out::show($this->nameForm . '_AssociaFamiglia');
                }
                if ($this->Ricerca == 1 || $this->cf == '') {
                    Out::show($this->divRic);
                    Out::setFocus('', $this->nameForm . '_Cognome');
                    Out::show($this->nameForm . '_Elenca');
                } else {
                    $this->dettaglioSoggetto($this->cf, 'diretto');
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        Out::hide($this->divRis);
                        $sql = "SELECT * FROM ANAGRA LEFT OUTER JOIN LAVORO ON ANAGRA.CODTRI=LAVORO.CODTRI WHERE ANAGRA.ROWID =" . $_POST['rowid'];
                        $Soggetto = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
                        $this->dettaglioSoggetto($Soggetto['FISCAL'], 'daGrid');
                        break;
                }
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
                    case $this->nameForm . '_AssociaFamiglia':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Elenca':


                        
        // TOGLIERE LA CLASSE IN TESTA utiAnagrafe.class.php
                        
//        $datiAnagrafe = Array();
//        $ricParm = array(
//		'CODICEFISCALE'=> 'RSSCST75D41A271M',
//		'SOLORESIDENTI' => true
//	 );
//        $datiAnagrafe = utiAnagrafe::datiAnagrafe($ricParm);
//        Out::msgInfo('datiAnagrafe',print_r($datiAnagrafe,true));


//        $ricParm = array(
//            'COGNOME' => $_POST[$this->nameForm . '_Cognome'],
//            'NOME' => $_POST[$this->nameForm . '_Nome'],
//            'CODICEFISCALE' => ''
//        );        
//        utiAnagrafe::ricAnagrafe($this->nameForm, $ricParm, '', '');

                        
                        
//            $ricParm = array(
//                'COGNOME' => 'ROSSI',
//                'NOME' => 'SIVIA',
//                'CODICEFISCALE' => 'RSSCST75D41A271M',
//                'SOLORESIDENTI' => true
//            );
//            $check = false;
//            $check = utiAnagrafe::controllaAnagrafe($ricParm);
//            Out::msgInfo('$check',$check);

                        
//        break;
                        
                        
                        
                        
                        $Nome = strtoupper($_POST[$this->nameForm . '_Nome']);
                        $Cognome = strtoupper($_POST[$this->nameForm . '_Cognome']);
                        if ($Nome == '' && $Cognome == '' && $_POST[$this->nameForm . '_CodFis'] == '' && $_POST[$this->nameForm . '_CodVia'] == '' && $_POST[$this->nameForm . '_RelPar'] == '') {
                            out::msgInfo("", "Inserire un elemento di ricerca.");
                            break;
                        }
                        $this->nascondi();
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_AltraRicerca');
                        $sql = "SELECT ANAGRA.ROWID AS ROWID,
                                           ANAGRA.NOME AS NOME,
                                           ANAGRA.COGNOM AS COGNOM,
                                           ANAGRA.GGNAT AS GGNAT,
                                           ANAGRA.MMNAT AS MMNAT,
                                           ANAGRA.AANAT AS AANAT,
                                           ANACIT.RESID AS RESID,
                                           LAVORO.FISCAL AS FISCAL," .
                                $this->ANEL_DB->strConcat('ANINDI.SPECIE', "' '", 'ANINDI.INDIR', "' '", 'ANAGRA.CIVICO') . " AS INDIRIZZO
                                    FROM ANAGRA 
                                    LEFT OUTER JOIN ANACIT ON ANAGRA.CODVEC = ANACIT.CODCIT
                                    LEFT OUTER JOIN ANINDI ON ANAGRA.CODIND = ANINDI.CODIND
                                    LEFT OUTER JOIN LAVORO ON ANAGRA.CODTRI = LAVORO.CODTRI
                                    WHERE 1=1";

                        if ($Nome != '') {
                            $sql.= " AND ANAGRA.NOME LIKE '$Nome%'";
                        }
                        if ($Cognome != '') {
                            $sql.="  AND ANAGRA.COGNOM LIKE '$Cognome%'";
                        }
                        if ($_POST[$this->nameForm . '_CodFis']) {
                            $sql.="  AND LAVORO.FISCAL = '" . $_POST[$this->nameForm . '_CodFis'] . "'";
                        }
                        if ($_POST[$this->nameForm . '_CodVia']) {
                            $sql.="  AND ANAGRA.CODIND = '" . $_POST[$this->nameForm . '_CodVia'] . "'";
                            if ($_POST[$this->nameForm . '_NumCiv']) {
                                $sql.="  AND TRIM(ANAGRA.CIVICO) = '" . trim($_POST[$this->nameForm . '_NumCiv']) . "'";
                            }
                        }
                        if ($_POST[$this->nameForm . '_RelPar']) {
                            $sql.="  AND ANAGRA.CODREL = '" . $_POST[$this->nameForm . '_RelPar'] . "'";
                        }
                        $sql.=" ORDER BY COGNOM,NOME"; 
                        $Tabella = ItaDB::DBSQLSelect($this->ANEL_DB, $sql);
                        if ($Tabella) {
                            $Tabella = $this->elaboraTabella($Tabella);
                            TableView::clearGrid($this->gridAnagrafica);
                            $ita_grid01 = new TableView($this->gridAnagrafica,
                                            array('arrayTable' => $Tabella,
                                                'rowIndex' => 'idx')
                            );
                            $ita_grid01->setPageNum(1);
                            $ita_grid01->setPageRows($_POST[$this->gridAnagrafica]['gridParam']['rowNum']);
                            $ita_grid01->setSortOrder('DESC');
                            $ita_grid01->getDataPage('json');
                            TableView::enableEvents($this->gridAnagrafica);
                        } else {
                            $this->nascondi();
                            Out::show($this->divRic);
                            Out::show($this->nameForm . '_Elenca');
                            Out::MsgInfo('ATTENZIONE', 'Non trovati nominativi.');
                        }
                        break;

                    case $this->nameForm . '_AltraRicerca':
                        Out::hide($this->divRis);
                        Out::hide($this->divGes);
                        Out::show($this->divRic);
                        Out::hide($this->nameForm . '_AltraRicerca');
                        Out::hide($this->nameForm . '_TornaElenco');
                        Out::show($this->nameForm . '_Elenca');
                        Out::clearFields($this->divRic);
                        Out::clearFields($this->divGes);
                        Out::setFocus('', $this->nameForm . '_Cognome');
                        break;

                    case $this->nameForm . '_TornaElenco':
                        Out::clearFields($this->divGes);
                        Out::hide($this->divGes);
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::hide($this->nameForm . '_TornaElenco');
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
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_operazione');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->operazione == 'associa') {
            $CodFamily = $_POST[$this->nameForm . '_ANAGRA']['FAMILY'];
            $_POST = array();
            $_POST['event'] = $this->returnMethod;
            $_POST['model'] = $this->returnModel;
            $_POST['family'] = $CodFamily;
            $phpURL = App::getConf('modelBackEnd.php');
            $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
            include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
            $returnModel = $this->returnModel;
            $returnModel();
        }
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function nascondi() {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_TornaElenco');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
    }

    public function dettaglioSoggetto($cf, $daDove) {
        $soggetto = $this->cercaSoggettoAnagrafe($cf);
        if ($soggetto == false) {
            Out::msgInfo("Attenzione", "Codice Fiscale $cf non presente");
            Out::closeDialog($this->nameForm);
            return false;
        }
        Out::show($this->divGes);
        if ($daDove == 'daGrid') {
            Out::show($this->nameForm . '_TornaElenco');
        }
        if ($this->operazione) {
            Out::hide($this->nameForm . '_TornaElenco');
            Out::hide($this->nameForm . '_Elenca');
        }
        $lavoro = $this->cercaSoggettoLavoro($cf);
        $matrim = $this->cercaSoggettoMatrim($soggetto['CODTRI']);
        $family = $soggetto['FAMILY'];
        $anagraTab = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM ANAGRA WHERE FAMILY = $family ORDER BY COGNOM, NOME", true);
        $righeFam = array();
        $indice = 0;
        if ($anagraTab) {
            foreach ($anagraTab as $anagraRec) {
                $via = $anagraRec['CODIND'];
                $anindiRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM ANINDI WHERE CODIND = '$via'", false);
                $parentela = $anagraRec['CODREL'];
                $relparRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM RELPAR WHERE CODREL = '$parentela'", false);
                $statoCivile = $anagraRec['CODSTA'];
                $scivilRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM SCIVIL WHERE CODSTA = '$statoCivile'", false);
                $righeFam[$indice]['COGNOM'] = $anagraRec['COGNOM'];
                $righeFam[$indice]['NOME'] = $anagraRec['NOME'];
                $righeFam[$indice]['VIARESIDENZA'] = $anindiRec['SPECIE'] . ' ' . $anindiRec['INDIR'];
                $righeFam[$indice]['CIVICO'] = $anagraRec['CIVICO'];
                $righeFam[$indice]['PARENTELA'] = $relparRec['DESREL'];
                $righeFam[$indice]['STATOCIVILE'] = $scivilRec['DESSTA'];
                $righeFam[$indice]['DATNAT'] = sprintf("%02d", $anagraRec['GGNAT']) . '/' . sprintf("%02d", $anagraRec['MMNAT']) . '/' . sprintf("%04d", $anagraRec['AANAT']);
                $codtri = $anagraRec['CODTRI'];
                $morteRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM MORTE WHERE CODTRI = $codtri", false);
                if ($morteRec) {
                    $righeFam[$indice]['NOTE'] = 'Deceduto';
                }
                $immigrRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM IMMIGR WHERE CODTRI = $codtri ORDER BY AAEMIG DESC", true);
                if ($immigrRec) {
                    $dataEmig = sprintf("%04d", $immigrRec[0]['AAEMIG']) . sprintf("%02d", $immigrRec[0]['MMEMIG']) . sprintf("%02d", $immigrRec[0]['GGEMIG']);
                    $dataImmig = sprintf("%04d", $immigrRec[0]['AAIMMI']) . sprintf("%02d", $immigrRec[0]['MMIMMI']) . sprintf("%02d", $immigrRec[0]['GGIMMI']);
                    if ($dataEmig > $dataImmig) {
                        $righeFam[$indice]['NOTE'] = 'Emigrato';
                    }
                }
                $indice = $indice + 1;
            }
        }
        Out::valori($soggetto, $this->nameForm . '_ANAGRA');
        Out::valori($lavoro, $this->nameForm . '_LAVORO');
        Out::valori($matrim, $this->nameForm . '_MATRIM');
        $datnat = sprintf("%02d", $soggetto['GGNAT']) . '/' . sprintf("%02d", $soggetto['MMNAT']) . '/' . sprintf("%04d", $soggetto['AANAT']);
        if ($soggetto['GGNAT'] != 0) {
            Out::valore($this->nameForm . '_DATNAT', $datnat);
        } else {
            Out::valore($this->nameForm . '_DATNAT', '');
        }
        $via = $soggetto['CODIND'];
        $anindiRec = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM ANINDI WHERE CODIND = '$via'", false);
        Out::valore($this->nameForm . '_VIARESIDENZA', $anindiRec['SPECIE'] . ' ' . $anindiRec['INDIR']);
        $this->CaricaGriglia($this->gridFamily, $righeFam);
        TableView::enableEvents($this->gridFamily);
        //
        $codtri = $soggetto['CODTRI'];
        $variazioniTab = ItaDB::DBSQLSelect($this->ANEL_DB, "SELECT * FROM VFCODT WHERE VCODTR = $codtri ORDER BY VDATA DESC", true);
        $righeVar = array();
        $indice = 0;
        if ($variazioniTab) {
            foreach ($variazioniTab as $variazioniRec) {
                $righeVar[$indice]['DATA'] = sprintf("%02d", $variazioniRec['VGGVA']) . '/' . sprintf("%02d", $variazioniRec['VMMVA']) . '/' . sprintf("%04d", $variazioniRec['VAAVA']);
                $righeVar[$indice]['CODICE'] = $variazioniRec['VCODVA'];
                switch ($variazioniRec['VCODVA']) {
                    case '01':
                        $des = 'TESSERA ELETTORALE';
                        break;
                    case '02':
                        $des = 'E/U FAMIGLIA';
                        break;
                    case '04':
                        $des = 'LIBRETTO DI LAVORO';
                        break;
                    case '05':
                        $des = 'CARTA D\'IDENTITA';
                        break;
                    case '16':
                        $des = 'CENSIMENTO';
                        break;
                    case '17':
                        $des = 'POP. INASAIA RESIDENTI';
                        break;
                    case '18':
                        $des = 'POP. INASAIA EMIGRATI';
                        break;
                    case '19':
                        $des = 'POP. INASAIA DEFUNTI';
                        break;
                    case '20':
                        $des = 'TESSERA ELETTORALE';
                        break;
                    case '21':
                        $des = 'MORTE';
                        break;
                    case '22':
                        $des = 'MATRIMONIO';
                        break;
                    case '23':
                        $des = 'SCIOGLIMENTO MATRIMONIO';
                        break;
                    case '24':
                        $des = 'VARIAZ. DI CITTADINANZA';
                        break;
                    case '25':
                        $des = 'ISCRIZ.ANAGRAFICA';
                        break;
                    case '26':
                        $des = 'EMIGRAZIONE';
                        break;
                    case '27':
                        $des = 'VARIAZIONE DI INDIRIZZO';
                        break;
                    case '28':
                        $des = 'COGNOME E NOME';
                        break;
                    case '29':
                        $des = 'LUOGO E DATA DI NASCITA';
                        break;
                    case '30':
                        $des = 'PROFESSIONE';
                        break;
                    case '31':
                        $des = 'TITOLO DI STUDIO';
                        break;
                    case '32':
                        $des = 'INDIRIZZO ALL\'ESTERO';
                        break;
                    case '33':
                        $des = 'SESSO';
                        break;
                    case '34':
                        $des = 'PERDITA DIRITTO ELETT.';
                        break;
                    case '35':
                        $des = 'ACQUISTO DIR.ELETTORALE';
                        break;
                    case '37':
                        $des = 'IRREPERIBILITA\'';
                        break;
                    case '38':
                        $des = 'ISCRIZIONE AIRE';
                        break;
                    case '39':
                        $des = 'RINNOVO PERM.DI SOGGIORNO';
                        break;
                    default:
                        $des = 'Variazione non utile al fine dei controlli.';
                }
                $righeVar[$indice]['EVENTO'] = $des;
                $indice = $indice + 1;
            }
            $this->CaricaGriglia($this->gridVariazioni, $righeVar);
            TableView::enableEvents($this->gridVariazioni);
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
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

    public function cercaSoggettoAnagrafe($cf) {
        $soggetto = array();
        if ($cf != '' && $this->ANEL_DB) {
            $cf = sprintf("%16s", $cf);
            $sql = "SELECT * FROM LAVORO WHERE FISCAL = '$cf'";
            $lavoro = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
            if ($lavoro) {
                $codtri = $lavoro['CODTRI'];
                $sql = "SELECT * FROM ANAGRA WHERE CODTRI = $codtri";
                $soggetto = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
                return $soggetto;
            }
            return false;
        }
    }

    public function cercaSoggettoLavoro($cf) {
        if ($cf != '' && $this->ANEL_DB) {
            $cf = sprintf("%16s", $cf);
            $sql = "SELECT * FROM LAVORO WHERE FISCAL = '$cf'";
            $lavoro = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
            if ($lavoro) {
                return $lavoro;
            }
            return false;
        }
    }
    
    public function cercaSoggettoMatrim($codice) {
        $matrim = array();
        if ($codice != 0 && $this->ANEL_DB) {
            $sql = "SELECT * FROM MATRIM WHERE CODTRI = $codice";
            $matrim = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, false);
            if ($matrim) {
                return $matrim;
            } else {
                $matrim = array();
            }
        }
        return $matrim;
    }

    public function creaCombo() {
        $sql = "SELECT * FROM RELPAR ORDER BY DESREL";
        $relparTab = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, true);
        Out::select($this->nameForm . '_RelPar', 1, "", "1", "Tutti");
        foreach ($relparTab as $relparRec) {
            Out::select($this->nameForm . '_RelPar', 1, $relparRec['CODREL'], "0", $relparRec['DESREL'] . ' - ' . $relparRec['CODREL']);
        }

        $sql = "SELECT * FROM ANINDI ORDER BY SPECIE, INDIR";
        $anindiTab = ItaDB::DBSQLSelect($this->ANEL_DB, $sql, true);
        Out::select($this->nameForm . '_CodVia', 1, "", "1", "Tutte");
        foreach ($anindiTab as $anindiRec) {
            Out::select($this->nameForm . '_CodVia', 1, $anindiRec['CODIND'], "0", $anindiRec['SPECIE'] . ' ' . $anindiRec['INDIR']);
        }
    }

    public function elaboraTabella($Tabella) {
        foreach ($Tabella as $key => $anagra) {
            $mm = str_pad($anagra['MMNAT'], 2, "0", STR_PAD_LEFT);
            $gg = str_pad($anagra['GGNAT'], 2, "0", STR_PAD_LEFT);
            $data = $anagra['AANAT'] . $mm . $gg;
            if ((int) $data == 0) {
                $data = '';
            }
            $Tabella[$key]['DATANASCITA'] = $data;
        }
        return $Tabella;
    }

}

?>
