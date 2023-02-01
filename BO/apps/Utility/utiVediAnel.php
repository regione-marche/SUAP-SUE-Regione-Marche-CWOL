<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once ITA_BASE_PATH . '/apps/Catasto/catLib.class.php';

include_once ITA_BASE_PATH . '/apps/Utility/utiAnagrafe.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function utiVediAnel() {
    $utiVediAnel = new utiVediAnel();
    $utiVediAnel->parseEvent();
    return;
}

class utiVediAnel extends itaModel {

    public $nameForm = "utiVediAnel";
    public $divGes = "utiVediAnel_divGestione";
    public $divRis = "utiVediAnel_divRisultato";
    public $divRic = "utiVediAnel_divRicerca";
    public $divRicData = "utiVediAnel_divRicData";
    public $gridFamily = "utiVediAnel_gridFamily";
    public $gridVariazioni = "utiVediAnel_gridVariazioni";
    public $cf;
    public $returnModel;
    public $returnMethod;
    public $operazione;
    public $gridAnagrafica = "utiVediAnel_gridAnagrafica";
    public $Ricerca;
    public $datiSoggetto;
    public $returnBroadcast;
    public $familiari;
    public $ArrayVariazioni;
    public $ArrayFamily;
    public $Img;
    public $elencoCittadini;
    public $livello;

    function __construct() {
        parent::__construct();
        try {
            $this->catLib = new catLib();
            $this->accLib = new accLib();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->operazione = App::$utente->getKey($this->nameForm . '_operazione');
            $this->datiSoggetto = App::$utente->getKey($this->nameForm . '_datiSoggetto');
            $this->Ricerca = App::$utente->getKey($this->nameForm . '_Ricerca');
            $this->returnBroadcast = App::$utente->getKey($this->nameForm . '_returnBroadcast');
            $this->familiari = App::$utente->getKey($this->nameForm . '_familiari');
            $this->cf = App::$utente->getKey($this->nameForm . '_cf');
            $this->ArrayVariazioni = App::$utente->getKey($this->nameForm . '_ArrayVariazioni');
            $this->ArrayFamily = App::$utente->getKey($this->nameForm . '_ArrayFamily');
            $this->Img = App::$utente->getKey($this->nameForm . '_Img');
            $this->elencoCittadini = App::$utente->getKey($this->nameForm . '_elencoCittadini');
            $this->livello = App::$utente->getKey($this->nameForm . '_livello');
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
            App::$utente->setKey($this->nameForm . '_datiSoggetto', $this->datiSoggetto);
            App::$utente->setKey($this->nameForm . '_Ricerca', $this->Ricerca);
            App::$utente->setKey($this->nameForm . '_returnBroadcast', $this->returnBroadcast);
            App::$utente->setKey($this->nameForm . '_familiari', $this->familiari);
            App::$utente->setKey($this->nameForm . '_cf', $this->cf);
            App::$utente->setKey($this->nameForm . '_ArrayVariazioni', $this->ArrayVariazioni);
            App::$utente->setKey($this->nameForm . '_ArrayFamily', $this->ArrayFamily);
            App::$utente->setKey($this->nameForm . '_Img', $this->Img);
            App::$utente->setKey($this->nameForm . '_elencoCittadini', $this->elencoCittadini);
            App::$utente->setKey($this->nameForm . '_livello', $this->livello);
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
                $this->returnBroadcast = $_POST['returnBroadcast'];
                $this->creaCombo();
                $this->nascondi();
                Out::show($this->nameForm . '_DivRicerca');
                Out::hide($this->nameForm . '_AssociaFamiglia');
                $this->operazione = $_POST['operazione'];
                if ($this->operazione == 'prendiDatiAnagrafici') {
                    $this->prendiAnagrafe();
                } else {
                    if ($_POST['operazione'] == 'associa') {
                        Out::show($this->nameForm . '_AssociaFamiglia');
                    }
                    if ($this->Ricerca == 1 || $this->cf == '') {
                        $devLib = new devLib();
                        $Anagrafe_parm_rec = $devLib->getEnv_config('CONNESSIONI', 'codice', 'ANAGRAFE', false);
                        $provider = $Anagrafe_parm_rec['CONFIG'];
                        switch ($provider) {
                            case 'CityWareOnLine':
                                $this->dettaglioSoggetto('', '');
                                break;
                            default:
                                Out::show($this->divRic);
                                Out::setFocus('', $this->nameForm . '_Cognome');
                                Out::show($this->nameForm . '_Elenca');
                        }
                    } else {
                        $this->dettaglioSoggetto($this->cf, 'diretto');
                    }
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridAnagrafica:
                        Out::hide($this->divRis);
                        if ($_POST['rowid']) {  // TORNA SEMPRE IL CODICE FISCALE
                            $this->dettaglioSoggetto($_POST['rowid'], 'daGrid');
                            break;
                        }
                        break;
                    case $this->gridFamily:
                        $row = $_POST['utiVediAnel_gridFamily'];
                        $riga = $row['gridParam']['selrow'];
                        if ($riga) {
                            $progSogg = $this->familiari[($riga - 1)]['CODICEUNIVOCO'];
                            if ($progSogg) {
                                $this->dettaglioSoggetto($progSogg, 'daGrid');
//                                if ($this->cf == '') {
//                                    $this->dettaglioSoggetto($progSogg, 'daGrid');
//                                } else {
//                                    $this->dettaglioSoggetto($progSogg, 'diretto');
//                                }
                            }
                        }
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
                    case $this->nameForm . '_Stampa':
                        $Record = $_POST;
                        $report = 'utiVediAnel';
                        $HTMLName = $this->getHtmlRicevuta($report, $Record);
                        Out::openIFrame($report, $report . "_toPrint", utiDownload::getUrl(App::$utente->getKey('TOKEN') . "-" . $report . ".html", $HTMLName), "600px", "900px", 'desktopBody', false, true);
                        break;
                    case $this->nameForm . '_AssociaFamiglia':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_Elenca':
                        $Nome = strtoupper($_POST[$this->nameForm . '_Nome']);
                        $Cognome = strtoupper($_POST[$this->nameForm . '_Cognome']);
                        if ($Nome == '' && $Cognome == '' && $_POST[$this->nameForm . '_CodFis'] == '' && $_POST[$this->nameForm . '_CodVia'] == '' && $_POST[$this->nameForm . '_RelPar'] == '' && $_POST[$this->nameForm . '_DaData'] == '') {
                            out::msgInfo("", "Inserire un elemento di ricerca.");
                            break;
                        }
                        $this->nascondi();
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_ExportToExcel');
                        $ricParm = array(
                            'COGNOME' => $Cognome,
                            'NOME' => $Nome,
                            'CODICEFISCALE' => $_POST[$this->nameForm . '_CodFis'],
                            'CODICEVIA' => $_POST[$this->nameForm . '_CodVia'],
                            'DALCIVICO' => $_POST[$this->nameForm . '_DaNumCiv'],
                            'ALCIVICO' => $_POST[$this->nameForm . '_ANumCiv'],
                            'NATODAL' => $_POST[$this->nameForm . '_DaData'],
                            'NATOAL' => $_POST[$this->nameForm . '_AData']
                        );
                        $provider = utiAnagrafe::getAnagrafeProvider();
                        $Tabella = $provider->getCittadiniLista($ricParm);
                        if ($Tabella) {
                            $this->elencoCittadini = $Tabella = $this->elaboraTabella($Tabella);
                            TableView::clearGrid($this->gridAnagrafica);
                            $ita_grid01 = new TableView($this->gridAnagrafica, array('arrayTable' => $Tabella,
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
                        Out::hide($this->nameForm . '_ExportToExcel');
                        Out::hide($this->nameForm . '_TornaElenco');
                        Out::show($this->nameForm . '_Elenca');
                        Out::clearFields($this->divRic);
                        Out::clearFields($this->divGes);
                        Out::setFocus('', $this->nameForm . '_Cognome');
                        Out::hide($this->nameForm . '_Stampa');
                        break;

                    case $this->nameForm . '_TornaElenco':
                        Out::clearFields($this->divGes);
                        Out::hide($this->divGes);
                        Out::show($this->divRis);
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_ExportToExcel');
                        Out::hide($this->nameForm . '_TornaElenco');
                        Out::hide($this->nameForm . '_Stampa');
                        break;

                    case $this->nameForm . '_PrendiAnagrafe':
                        Out::broadcastMessage($this->nameForm, $this->returnBroadcast, $this->datiSoggetto);
                        $this->close();
                        break;

                    case $this->nameForm . '_ExportToExcel':
                        $this->ExportToExcel($this->elencoCittadini);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnFromDanRicerca':
                //Out::msgInfo('', print_r($_POST, true));
                $ricParm = array(
                    'COGNOME' => '',
                    'NOME' => '',
                    'CODICEFISCALE' => '',
                    'PROGSOGG' => $_POST['rowid'],
                    'OPERAZIONE' => 'CONSULTA'
                );
                $provider = utiAnagrafe::getAnagrafeProvider();
                $provider->setReturnModel($this->returnModel);
                $soggetto = $provider->getCittadiniLista($ricParm);
                Out::broadcastMessage($this->nameForm, $this->returnBroadcast, $soggetto);
                $this->close();
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
        App::$utente->removeKey($this->nameForm . '_datiSoggetto');
        App::$utente->removeKey($this->nameForm . '_Ricerca');
        App::$utente->removeKey($this->nameForm . '_returnBroadcast');
        App::$utente->removeKey($this->nameForm . '_familiari');
        App::$utente->removeKey($this->nameForm . '_cf');
        App::$utente->removeKey($this->nameForm . '_ArrayVariazioni');
        App::$utente->removeKey($this->nameForm . '_ArrayFamily');
        App::$utente->removeKey($this->nameForm . '_Img');
        App::$utente->removeKey($this->nameForm . '_elencoCittadini');
        App::$utente->removeKey($this->nameForm . '_livello');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->operazione == 'associa') {
            $CodFamily = $_POST[$this->nameForm . '_FAMILY'];
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
        Out::hide($this->nameForm . '_PrendiAnagrafe');
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_RelPar_field');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_ExportToExcel');
        Out::hide($this->nameForm . '_divContainerFoto');

        $idUtente = App::$utente->getKey('idUtente');
        $this->livello = $this->accLib->GetLivelloAnagrafe($idUtente);
        switch ($this->livello) {
            case 1: // LIVELLO BASE
                Out::hide($this->nameForm . '_div2livello');
                Out::hide($this->nameForm . '_div3livello');
                break;
            case 2: // LIVELLO INTERMEDIO
                Out::hide($this->nameForm . '_div3livello');
                break;
            case 3: // LIVELLO COMPLETO

                break;
        }
        $devLib = new devLib();
        $Anagrafe_parm_rec = $devLib->getEnv_config('CONNESSIONI', 'codice', 'ANAGRAFE', false);
        if ($Anagrafe_parm_rec['CONFIG'] != 'CityWare') {
            Out::hide($this->nameForm . '_divRicData');
        }
    }

    public function dettaglioSoggetto($progSogg, $daDove) {
        switch ($daDove) {
            case 'daGrid';  // TORNA IL CODICE INDIVIDUALE
                $cf = '';
                break;
            case 'diretto';  // TORNA IL CODICE CODICE FISCALE
                $cf = $progSogg;
                $progSogg = '';
                break;
        }
        $ricParm = array(
            'COGNOME' => '',
            'NOME' => '',
            'CODICEFISCALE' => $cf,
            'PROGSOGG' => $progSogg,
            'OPERAZIONE' => 'CONSULTA'
        );
        $provider = utiAnagrafe::getAnagrafeProvider();
        $provider->setReturnModel($this->returnModel);
        $soggetto = $provider->getCittadiniLista($ricParm);
        $soggetto = $soggetto[0];
        if (!$soggetto) {
            Out::msgInfo("Attenzione", "Codice Fiscale $cf non presente");
            Out::closeDialog($this->nameForm);
            return false;
        }

        //Out::msgInfo('',print_r($soggetto,true));

        $this->datiSoggetto = $soggetto;
        Out::show($this->divGes);
        if ($daDove == 'daGrid') {
            Out::show($this->nameForm . '_TornaElenco');
        }
        if ($this->operazione) {
            Out::hide($this->nameForm . '_TornaElenco');
            Out::hide($this->nameForm . '_Elenca');
        }
        Out::hide($this->nameForm . '_ExportToExcel');
        Out::valore($this->nameForm . '_CODICEUNIVOCO', $soggetto['CODICEUNIVOCO']);
        Out::valore($this->nameForm . '_COGNOME', $soggetto['COGNOME']);
        Out::valore($this->nameForm . '_NOME', $soggetto['NOME']);
        Out::valore($this->nameForm . '_SESSO', $soggetto['SESSO']);
        Out::valore($this->nameForm . '_CODICEFISCALE', $soggetto['CODICEFISCALE']);
        Out::valore($this->nameForm . '_FAMILY', $soggetto['FAMILY']);
        Out::valore($this->nameForm . '_DATANASCITA', substr($soggetto['DATANASCITA'], 6, 2) . '/' . substr($soggetto['DATANASCITA'], 4, 2) . '/' . substr($soggetto['DATANASCITA'], 0, 4));
        Out::valore($this->nameForm . '_LUOGONASCITA', $soggetto['LUOGONASCITA']);
        Out::valore($this->nameForm . '_PROVINCIANASCITA', $soggetto['PROVINCIANASCITA']);
        Out::valore($this->nameForm . '_INDIRIZZO', $soggetto['INDIRIZZO']);
        Out::valore($this->nameForm . '_CIVICO', $soggetto['CIVICO']);
        Out::valore($this->nameForm . '_PATERNITA', $soggetto['PATERNITA']);
        Out::valore($this->nameForm . '_MATERNITA', $soggetto['MATERNITA']);
        Out::valore($this->nameForm . '_STATOCIVILE', $soggetto['STATOCIVILE']);
        if ($soggetto['CONIUGE']) {
            Out::valore($this->nameForm . '_CONIUGE', $soggetto['CONIUGE'] . ' ' . substr($soggetto['DATAMATRIMONIO'], 6, 2) . '/' . substr($soggetto['DATAMATRIMONIO'], 4, 2) . '/' . substr($soggetto['DATAMATRIMONIO'], 0, 4));
        } else {
            Out::valore($this->nameForm . '_CONIUGE', '');
        }
        Out::valore($this->nameForm . '_PROFESSIONE', $soggetto['PROFESSIONE']);
        Out::valore($this->nameForm . '_TITOLOSTUDIO', $soggetto['TITOLOSTUDIO']);
        Out::valore($this->nameForm . '_CITTADINANZA', $soggetto['CITTADINANZA']);
        Out::valore($this->nameForm . '_CARTAIDENTITA', $soggetto['CARTAIDENTITA']);
        Out::valore($this->nameForm . '_CARTAIDENTITARIL', substr($soggetto['CARTAIDENTITARIL'], 6, 2) . '/' . substr($soggetto['CARTAIDENTITARIL'], 4, 2) . '/' . substr($soggetto['CARTAIDENTITARIL'], 0, 4));
        Out::valore($this->nameForm . '_CARTAIDENTITASCA', substr($soggetto['CARTAIDENTITASCA'], 6, 2) . '/' . substr($soggetto['CARTAIDENTITASCA'], 4, 2) . '/' . substr($soggetto['CARTAIDENTITASCA'], 0, 4));
        if ($soggetto['DATAIMMIGRAZIONE'] != '') {
            Out::valore($this->nameForm . '_DATI-IMMIGRAZIONE', trim($soggetto['LUOGOIMMI']) . ' il ' . substr($soggetto['DATAIMMIGRAZIONE'], 6, 2) . '/' . substr($soggetto['DATAIMMIGRAZIONE'], 4, 2) . '/' . substr($soggetto['DATAIMMIGRAZIONE'], 0, 4));
        } else {
            Out::valore($this->nameForm . '_DATI-IMMIGRAZIONE', '');
        }
        if ($soggetto['DATAEMIGRAZIONE'] != '') {
            Out::valore($this->nameForm . '_DATI-EMIGRAZIONE', trim($soggetto['LUOGOEMI']) . ' il ' . substr($soggetto['DATAEMIGRAZIONE'], 6, 2) . '/' . substr($soggetto['DATAEMIGRAZIONE'], 4, 2) . '/' . substr($soggetto['DATAEMIGRAZIONE'], 0, 4));
        } else {
            Out::valore($this->nameForm . '_DATI-EMIGRAZIONE', '');
        }
        //
        TableView::clearGrid($this->gridFamily);
        $this->familiari = array();
        if ($soggetto['FAMILY']) {
            $ricParm = array(
                'FAMILY' => $soggetto['FAMILY'],
                'TIPOFAMILY' => $soggetto['TIPOFAMILY'],
                'CODICEFISCALE' => $soggetto['CODICEFISCALE']       // PER SOLWS LA RICERCA NECESSITA DEL CF
            );
            $this->familiari = $provider->getCittadinoFamiliari($ricParm);
            if ($this->familiari) {
                $this->CaricaGriglia($this->gridFamily, $this->familiari);
            }
        }
        //
        TableView::clearGrid($this->gridVariazioni);
        $ricParm = array(
            'CODICEUNIVOCO' => $soggetto['CODICEUNIVOCO']
        );
        $variazioni = $provider->getCittadinoVariazioni($ricParm);
        if ($variazioni) {
            $this->CaricaGriglia($this->gridVariazioni, $variazioni);
            Out::show($this->nameForm . '_divVariazioni');
        } else {
            Out::hide($this->nameForm . '_divVariazioni');
        }
        Out::hide($this->nameForm . '_PrendiAnagrafe');
        if ($this->Ricerca == 1) {
            Out::show($this->nameForm . '_PrendiAnagrafe');
        }
        Out::show($this->nameForm . '_Stampa');
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
        if ($griglia == $this->gridVariazioni) {
            $this->ArrayVariazioni = $appoggio;
        }if ($griglia == $this->gridFamily) {
            $this->ArrayFamily = $appoggio;
        }
        return;
    }

    public function creaCombo() {
        $provider = utiAnagrafe::getAnagrafeProvider();
        $vie = $provider->getVie();
        if ($vie) {
            Out::select($this->nameForm . '_CodVia', 1, "", "1", "Tutte");
            foreach ($vie as $via) {
                Out::select($this->nameForm . '_CodVia', 1, $via['CODICEVIA'], "0", $via['TOPONIMO'] . ' ' . $via['NOMEVIA']);
            }
        } else {
            Out::hide($this->nameForm . '_CodVia_field');
            Out::hide($this->nameForm . '_DaNumCiv_field');
            Out::hide($this->nameForm . '_ANumCiv_field');
            Out::hide($this->nameForm . '_RelPar_field');
        }
    }

    public function elaboraTabella($Tabella) {
        //Out::msgInfo('tabella',print_r($Tabella,true));
        foreach ($Tabella as $key => $anagra) {
            $Tabella[$key]['INDIRIZZO'] .= ' ' . $Tabella[$key]['CIVICO'];
            $color = 'ita-icon ita-icon-bullet-green-16x16';
            $titolo = $anagra['STATOCIT'];
            if ($anagra['DATAEMIGRAZIONE'] || $anagra['STATOCIT'] == 'Emigrato' || $anagra['STATOCIT'] == 'Irreperib.' || $anagra['STATOCIT'] == 'Canc.Altri' || $anagra['STATOCIT'] == 'Canc.Aire' || $anagra['STATOCIT'] == 'Non resid.') {
                if (($anagra['DATAEMIGRAZIONE'] && $anagra['DATAEMIGRAZIONE'] > $anagra['DATAIMMIGRAZIONE']) || $anagra['STATOCIT']) {
                    $color = 'ita-icon ita-icon-bullet-red-16x16';
                }
            }
            if ($anagra['DATADECESSO'] || $anagra['STATOCIT'] == 'Morto') {
                $color = 'ita-icon ita-icon-bullet-gray-16x16';
            }
            if ($anagra['STATOCIT'] == 'Aire') {
                $color = 'ita-icon ita-icon-bullet-orange-16x16';
                $titolo = 'Aire';
            }
            $Tabella[$key]['ICONA'] = '<div class="ita-html"><span style="width:20px;" title="' . $titolo . ' " class="ita-tooltip">' . "<p align = \"center\"><span class=\"$color \" style=\"height:12px;width:12px;background-size:100%;vertical-align:bottom;margin-left:1px;display:inline-block;\" ></span></p>" . '</span></div>';
        }
        return $Tabella;
    }

    public function prendiAnagrafe() {
        $ricParm = array(
            'CODICEFISCALE' => $this->cf,
            'OPERAZIONE' => 'RETURNDATI'
        );
        $provider = utiAnagrafe::getAnagrafeProvider();
        $anagrafe = $provider->getCittadiniLista($ricParm);
        if ($anagrafe) {
            Out::broadcastMessage($this->nameForm, $this->returnBroadcast, $anagrafe[0]);
        } else {
            Out::msgInfo("Attenzione", "Codice Fiscale $this->cf non presente");
        }
        $this->close();
    }

    public function creaSql() {
        $sql = "SELECT * FROM";
        return $sql;
    }

    function getHtmlRicevuta($report, $record = array()) {
        $Nucleo = '';
        $TdVariazioni = '';
        foreach ($this->ArrayFamily as $Familiare) {
            $Nucleo = $Nucleo . '<tr>
                        <td class="txttab">
                           ' . $Familiare['COGNOM'] . '
                        </td>
                         <td class="txttab">
                           ' . $Familiare['NOME'] . '
                        </td>
                         <td class="txttab">
                           ' . $Familiare['PARENTELA'] . '
                        </td>
                          <td class="txttab">
                           ' . $Familiare['STATOCIVILE'] . '
                        </td>
                           <td class="txttab">
                           ' . $Familiare['DATNAT'] . '
                        </td>
                           <td class="txttab">
                           ' . $Familiare['LUOGONASCITA'] . '
                        </td>
                           <td class="txttab">
                           ' . $Familiare['STATOCIT'] . '
                        </td>
                        <td class="txttab">
                           ' . $Familiare['NOTE'] . '
                        </td>
                    </tr>';
        }
        foreach ($this->ArrayVariazioni as $Variazione) {
            $TdVariazioni = $TdVariazioni . '<tr>
                        <td class="txttab">
                           ' . $Variazione['DATA'] . '
                        </td>
                         <td class="txttab">
                           ' . $Variazione['EVENTO'] . '
                        </td>
                        </tr>';
        }

        $html = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
                <meta http-equiv="Pragma" content="no-cache" />
                <title>Italsoft</title>
             <style type="text/css">
        
            .tith    { font-family:Arial, Helvetica, Sans-Serif; font-size:10px; font-weight:bold; color:#000000; background-color:#CDD6DD; text-decoration:none; border:2px solid #CDD6DD;}
            .Testata    { font-family:Arial, Helvetica, Sans-Serif; font-size:18px; font-weight:bold; color:#000000; background-color:#CDD6DD; text-decoration:none; border:1px solid #CDD6DD;align:center}
            .txttab { font-family:Arial, Helvetica, Sans-Serif; font-size:10px; font-weight:normal; color:#000000; text-decoration:none; border:1px solid #CDD6DD;}
            .m3      {font-family:Arial; font-size:12px; color:#000000; font-weight: bold;}"
            </style>
            </head>
            <body>
             
            <table width="800" cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "Testata" style="" align=left width="300">
                           Anagrafica Residenti
                         </td>
                         <td class = "Testata" style="" align=left width="300">
                         ' . date('d/m/Y') . '
                         </td>
                     
                    </tr>
                 
            </table>
            <br><br>
              <div style="width:210mm;">
              <div style="display:inline-block; width: 70%; margin: auto;">
            <table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Codice<br>' . $record[$this->nameForm . '_CODICEUNIVOCO'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Cognome<br>' . $record[$this->nameForm . '_COGNOME'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Nome<br>' . $record[$this->nameForm . '_NOME'] . '
                         </td>
                          <td class = "m3" style="" align=left width="250">
                           Sesso<br>' . $record[$this->nameForm . '_SESSO'] . '
                         </td>
                          <td class = "m3" style="" align=left width="350">
                           C.F.<br>' . $record[$this->nameForm . '_CODICEFISCALE'] . '
                         </td>
                          <td class = "m3" style="" align=left width="250">
                           Famiglia<br>' . $record[$this->nameForm . '_FAMILY'] . '
                         </td>
                    </tr>
                 
</table>
<br>                 
            <table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Nato il<br>' . $record[$this->nameForm . '_DATANASCITA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Comune Nascita<br>' . $record[$this->nameForm . '_LUOGONASCITA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Provincia<br>' . $record[$this->nameForm . '_PROVINCIANASCITA'] . '
                         </td>
                          <td class = "m3" style="" align=left width="350">
                           Via di residenza<br>' . $record[$this->nameForm . '_INDIRIZZO'] . '
                         </td>
                          <td class = "m3" style="" align=left width="150">
                           Civico<br>' . $record[$this->nameForm . '_CIVICO'] . '
                         </td>
                    </tr>
                 </table>
                 <br>';
        if ($this->livello == 2 || $this->livello == 3 || $this->livello == 0) {
            $html .= '<table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Cittadinanza<br>' . $record[$this->nameForm . '_CITTADINANZA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Stato Civile<br>' . $record[$this->nameForm . '_STATOCIVILE'] . '
                         </td>
                    </tr>
                 </table>
                 <br>';
        }
        if ($this->livello == 3 || $this->livello == 0) {
            $html .= '<table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Paternità<br>' . $record[$this->nameForm . '_PATERNITA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Maternità<br>' . $record[$this->nameForm . '_MATERNITA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Coniuge<br>' . $record[$this->nameForm . '_CONIUGE'] . '
                         </td>
                    </tr>
                 </table>
                 <br>
                <table  cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Professione<br>' . $record[$this->nameForm . '_PROFESSIONE'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Titolo di Studio<br>' . $record[$this->nameForm . '_TITOLOSTUDIO'] . '
                         </td>
                      
                    </tr>
                 </table>
                 <br>
                <table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                         <td class = "m3" style="" align=left width="300">
                           Carta d identità<br>' . $record[$this->nameForm . '_CARTAIDENTITA'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Rilasciata il<br>' . $record[$this->nameForm . '_CARTAIDENTITARIL'] . '
                         </td>
                          <td class = "m3"  style="" align=left width="250">
                           Scade il<br>' . $record[$this->nameForm . '_CARTAIDENTITASCA'] . '
                         </td>
                      
                    </tr>
                 </table>
                 <br>
                <table cellpadding="1" style="border-spacing:0px;" >
                   <tr>
                        <td class = "m3" style="" align=left width="300">
                           Immigrazione<br>' . $record[$this->nameForm . '_DATI-IMMIGRAZIONE'] . '
                         </td>
                         <td class = "m3" style="" align=left width="300">
                           Emigrazione<br>' . $record[$this->nameForm . '_DATI-EMIGRAZIONE'] . '
                         </td>
                      
                    </tr>
                 </table>
                 </div>
            <div style="display:inline-block; width: 25%; height:100%;"> ' . $this->Img . '
         </div>                 
         </div>';
        }
        $html .= '     <br><br>
     
                 <table width="800" style="border-spacing:0px; border:1px solid #000000" >
                    <tr>
                        <td class="tith" colspan = 7>
                           Nucleo Famigliare
                         </td>
                    </tr>
                   <tr>
                        <td class="tith">
                           Cognome
                         </td>
                         <td  class="tith">
                           Nome
                         </td>
                         <td  class="tith">
                           Parentela
                         </td>
                          <td  class="tith">
                           Stato Civile
                         </td>
                          <td  class="tith">
                           Nato il
                         </td>
                          <td  class="tith">
                           a
                         </td>
                          <td  class="tith">
                           Posizione Cittadino
                         </td>
                          <td  class="tith">
                           Note
                         </td>
                    </tr>' . $Nucleo . '
                 </table>
                  
                 <br><br>
                     <table width="800" cellpadding="2" style="border-spacing:0px; border:1px solid #000000" >
                    <tr>
                        <td class="tith" colspan = 2>
                           Elenco Variazioni
                         </td>
                    </tr>
                      <tr>
                        <td  class="tith">
                           Data
                         </td>
                          <td  class="tith">
                           Variazione
                         </td>
                    </tr>' . $TdVariazioni . '
                    </table>

              </div> 
                <div class="header">
                </div>
                <div class="footer">
                </div>
            </body>
        </html>';

        //$HTMLName = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . "-" . $report . ".html";
        $HTMLName = itaLib::createAppsTempPath('vediAnel') . '/' . App::$utente->getKey('TOKEN') . "-" . $report . ".html";
        $ptr = fopen($HTMLName, 'wb');
        fwrite($ptr, $html);
        fclose($ptr);
        return $HTMLName;
    }

    public function FormattaValoreExport($Valore, $Formato) {
        switch ($Formato) {
            case 'DATA':
                $NewValore = substr($Valore, 6, 2) . '/' . substr($Valore, 4, 2) . '/' . substr($Valore, 0, 4);
                break;
            case 'IMPORTO':
                $NewValore = str_replace('.', ',', $Valore);
                break;
            case 'CHECK':
                $NewValore = 'X';
                break;
        }
        return $NewValore;
    }

    public function ExportToExcel($dati) {
        $DaFormattare = array('DATANASCITA' => 'DATA');
        $CampiUtilizzati = array('STATOCIT' => 'Stato', 'COGNOME' => 'Cognome', 'NOME' => 'Nome', 'CODICEFISCALE' => 'Codice Fiscale', 'DATANASCITA' => 'Nato/a il', 'LUOGONASCITA' => 'Luogo di Nascita', 'INDIRIZZO' => 'Indirizzo Residenza', 'PATERNITA' => 'Paternita', 'MATERNITA' => 'Maternita');
        $ValoriTabella = array();
        $i = 0;
        foreach ($dati as $riga) {
            foreach ($CampiUtilizzati as $chiave => $valore) {
                if ($DaFormattare[$chiave] && $riga[$chiave]) {
                    $NewVal = $this->FormattaValoreExport($riga[$chiave], $DaFormattare[$chiave]);
                    $ValoriTabella[$i][$valore] = $NewVal;
                } else {
                    $ValoriTabella[$i][$valore] = $riga[$chiave];
                }
            }
            $i++;
        }
        $ita_grid01 = new TableView('griglia', array('arrayTable' => $ValoriTabella,
            'rowIndex' => 'idx'));
        $ita_grid01->setSortOrder('NOMINATIVO');
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        $ita_grid01->exportXLS('', 'cittadini.xls');
    }

}

?>
