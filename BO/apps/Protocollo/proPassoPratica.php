<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    02.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPerms.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proPassoPratica() {
    $proPassoPratica = new proPassoPratica();
    $proPassoPratica->parseEvent();
    return;
}

class proPassoPratica extends itaModel {

    public $praLib;
    public $praPerms;
    public $proLib;
    public $proLibPratica;
    public $proLibFascicolo;
    public $docLib;
    public $PRAM_DB;
    public $PROT_DB;
    public $nameForm = "proPassoPratica";
    public $divGes = "proPassoPratica_divGestione";
    public $gridCampiAggiuntivi = "proPassoPratica_gridCampiAggiuntivi";
    public $gridAllegati = "proPassoPratica_gridAllegati";
    public $altriDati = array();
    public $arrayInfo = array();
    public $allegatiAzione = array();
    public $currAllegato;
    public $currGesnum;
    public $returnModel;
    public $returnMethod;
    public $datiForm;
    public $rowidAppoggio;
    public $workDate;
    public $keyPropas;
    public $allegatiAppoggio = array();
    public $testiAssociati = array();
    public $chiudiForm;
    public $pagina;
    public $sql;
    public $selRow;
    public $tipoFile;
    public $datiRubricaWS = array();
    public $destinatari = array();
    public $duplica = false;
    public $fascicolaDocumento;
    public $appoggioDati;
    public $passoChiusura;
    public $proLibAllegati;

    function __construct() {
        parent::__construct();
        try {
//
// carico le librerie
//
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->proLibPratica = new proLibPratica();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->proLibAllegati = new proLibAllegati();
            $this->docLib = new docLib();
            $this->praPerms = new praPerms();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->PROT_DB = $this->proLib->getPROTDB();
//
// SALVA SESSION
//
            $this->altriDati = App::$utente->getKey($this->nameForm . '_altriDati');
            $this->allegatiAzione = App::$utente->getKey($this->nameForm . '_passAlle');
            $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnMethod = App::$utente->getKey($this->nameForm . '_returnMethod');
            $this->datiForm = App::$utente->getKey($this->nameForm . '_datiForm');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->allegatiAppoggio = App::$utente->getKey($this->nameForm . '_allegatiAppoggio');
            $this->keyPropas = App::$utente->getKey($this->nameForm . '_keyPasso');
            $this->pagina = App::$utente->getKey($this->nameForm . '_pagina');
            $this->sql = App::$utente->getKey($this->nameForm . '_sql');
            $this->selRow = App::$utente->getKey($this->nameForm . '_selRow');
            $this->arrayInfo = App::$utente->getKey($this->nameForm . '_arrayInfo');
            $this->currAllegato = App::$utente->getKey($this->nameForm . '_currAllegato');
            $this->tipoFile = App::$utente->getKey($this->nameForm . '_tipoFile');
            $this->testiAssociati = App::$utente->getKey($this->nameForm . '_testiAssociati');
            $this->datiRubricaWS = App::$utente->getKey($this->nameForm . '_datiRubricaWS');
            $this->chiudiForm = App::$utente->getKey($this->nameForm . '_chiudiForm');
            $this->duplica = App::$utente->getKey($this->nameForm . '_duplica');
            $this->fascicolaDocumento = App::$utente->getKey($this->nameForm . '_fascicolaDocumento');
            $this->appoggioDati = App::$utente->getKey($this->nameForm . '_appoggioDati');
            $this->destinatari = App::$utente->getKey($this->nameForm . '_destinatari');
            $this->passoChiusura = App::$utente->getKey($this->nameForm . '_passoChiusura');

//
// Inizializzo variabili
//
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_altriDati', $this->altriDati);
            App::$utente->setKey($this->nameForm . '_passAlle', $this->allegatiAzione);
            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnMethod', $this->returnMethod);
            App::$utente->setKey($this->nameForm . '_datiForm', $this->datiForm);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_allegatiAppoggio', $this->allegatiAppoggio);
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPropas);
            App::$utente->setKey($this->nameForm . '_pagina', $this->pagina);
            App::$utente->setKey($this->nameForm . '_sql', $this->sql);
            App::$utente->setKey($this->nameForm . '_selRow', $this->selRow);
            App::$utente->setKey($this->nameForm . '_arrayInfo', $this->arrayInfo);
            App::$utente->setKey($this->nameForm . '_currAllegato', $this->currAllegato);
            App::$utente->setKey($this->nameForm . '_tipoFile', $this->tipoFile);
            App::$utente->setKey($this->nameForm . '_testiAssociati', $this->testiAssociati);
            App::$utente->setKey($this->nameForm . '_datiRubricaWS', $this->datiRubricaWS);
            App::$utente->setKey($this->nameForm . '_chiudiForm', $this->chiudiForm);
            App::$utente->setKey($this->nameForm . '_duplica', $this->duplica);
            App::$utente->setKey($this->nameForm . '_fascicolaDocumento', $this->fascicolaDocumento);
            App::$utente->setKey($this->nameForm . '_appoggioDati', $this->appoggioDati);
            App::$utente->setKey($this->nameForm . '_destinatari', $this->destinatari);
            App::$utente->setKey($this->nameForm . '_passoChiusura', $this->passoChiusura);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (Conf::ITA_ENGINE_VERSION > '1.0') {
                    Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                }
                $this->CreaCombo();
                $this->returnModel = $_POST[$this->nameForm . "_returnModel"];
                $this->returnMethod = $_POST[$this->nameForm . "_returnMethod"];
                $this->pagina = $_POST['pagina'];
                $this->sql = $_POST['sql'];
                $this->selRow = $_POST['selRow'];
                $this->fascicolaDocumento = array();
                $this->appoggioDati = array();
                if ($_POST[$this->nameForm . "_fascicolaDocumento"]) {
                    $this->fascicolaDocumento = $_POST[$this->nameForm . "_fascicolaDocumento"];
                }
                switch ($_POST['modo']) {
                    case "edit" :
                        if ($_POST['rowid']) {
                            $this->dettaglio($_POST['rowid'], 'rowid');
                        }
                        break;
                    case "add" :
                        if ($_POST['procedimento']) {
                            $this->currGesnum = $_POST['procedimento'];
                            $this->apriInserimento($this->currGesnum, 'add', $_POST['nodeRowid']);
                        }
                        break;
                    case "addNode" :
                        if ($_POST['procedimento']) {
                            $this->currGesnum = $_POST['procedimento'];
                            $this->apriInserimento($this->currGesnum, 'addNode', $_POST['nodeRowid']);
                        }
                        break;
                    case "chiudiFascicolo" :
                        if ($_POST['procedimento']) {
                            $this->currGesnum = $_POST['procedimento'];
                            $this->chiudiFascicolo($this->currGesnum, $_POST['prostato'], $_POST['nodeRowid']);
                        }
                        break;

                    case "duplica" :
                        if ($_POST['procedimento']) {
                            $this->currGesnum = $_POST['procedimento'];
                            $this->duplica = true;
                            $this->apriDuplica($_POST['last_propas_rec']);
                        }
                        break;
                }
                if (is_array($_POST['listaAllegati'])) {
                    $this->caricaAllegatiEsterni($_POST['listaAllegati']);
                }
                if ($_POST['datiForm']) { // per integrazione
                    $this->datiForm = $_POST['datiForm'];
                }
                if ($_POST['datiInfo']) {
                    Out::show($this->nameForm . '_divInfo');
                    Out::html($this->nameForm . "_divInfo", $_POST['datiInfo']);
                } else {
                    Out::hide($this->nameForm . '_divInfo');
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "UPDATE_FASCICOLO":
                        if ($_POST["msgData"]["PASKEY"] && $_POST["msgData"]["PASKEY"] === $this->keyPropas) {
                            $this->dettaglio($_POST["msgData"]["PASKEY"], 'propak');
                        }
                        break;
                }
                break;
            case 'afterSaveCell':
                $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
                switch ($_POST['id']) {
                    case $this->gridCampiAggiuntivi:
                        $chiave = "";
                        foreach ($this->altriDati as $key => $campo) {
                            if ($campo['DAGSET'] == $_POST['rowid']) {
                                $chiave = $key;
                                break;
                            }
                        }
                        $this->altriDati[$chiave]['DAGVAL'] = $_POST['value'];
                        break;
                    case $this->gridAllegati:

                        $chiave = $_POST['rowid'];
                        $codice = substr($_POST['rowid'], 4);
                        $anadoc_rec = $this->proLib->GetAnadoc($codice, 'codice');


                        if ($this->allegatiAzione[$_POST['rowid']]['DOCLOCK'] == 1 || $this->allegatiAzione[$_POST['rowid']]['PROTOCOLLO']) {
                            TableView::setCellValue($this->gridAllegati, $_POST['rowid'], 'INFO', $anadoc_rec['DOCNOT']);
                            break;
                        }
                        $this->allegatiAzione[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                        if ($anadoc_rec) {
                            switch ($_POST['cellname']) {
                                case 'INFO':
                                    $anadoc_rec['DOCNOT'] = $_POST['value'];
                                    $info = "Aggiornamento note allegato: ";
                                    break;
                            }

                            $update_Info = "Oggetto: $info" . $anadoc_rec['DOCFIL'];
                            if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                                Out::msgStop("Errore in Aggionamento", "Aggiornamento note documento fallito.");
                                break;
                            }
                        }
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridCampiAggiuntivi:
                        praRic::praRicPraidc($this->nameForm, 'returnPraidc');
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $where = array(
                            "PROTOCOLLI" => '',
                            "DOCUMENTI" => ''
                        );
                        if ($_POST['_search'] == true) {
                            if ($_POST['INFO']) {
                                $where['DOCUMENTI'] .= " AND {$this->PROT_DB->strUpper('DOCNOT')}  LIKE '%" . strtoupper($_POST['INFO']) . "%'";
                            }
                            if ($_POST['NAME']) {
                                $where['DOCUMENTI'] .= " AND {$this->PROT_DB->strUpper('DOCNAME')
                                        } LIKE '%" . strtoupper($_POST['NAME']) . "%'";
                            }
                            if ($_POST['PROPAR']) {
                                $where['PROTOCOLLI'] .= " AND ANAPRO.PROPAR='{$_POST['PROPAR']}'";
                            }
                            if ($_POST['NUMPROT']) {
                                $numprot = str_pad($_POST['NUMPROT'], 6, "0", STR_PAD_LEFT);
                                $where['PROTOCOLLI'] .= " AND ".$this->PROT_DB->subString('ANAPRO.PRONUM',5,6)."='{$numprot}'";
                            }
                            if ($_POST['ANNOPROT']) {
                                $where['PROTOCOLLI'] .= " AND ".$this->PROT_DB->subString('ANAPRO.PRONUM',1,4)."='{$_POST['ANNOPROT']}'";
                            }
                        }
                        $this->caricaAllegatiAnadoc($where);
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->allegatiAzione) == true) {
                            $doc = $this->allegatiAzione[$_POST['rowid']];
                            if (!$doc['FILEPATH']) {
                                break;
                            }
                            if ($doc['PROVENIENZA'] == "TESTOBASE") {
                                if (pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION) == "pdf") {
                                    Out::openDocument(utiDownload::getUrl(
                                                    $doc['FILENAME'], $doc['FILEPATH']
                                            )
                                    );
                                } else {
                                    $tipoAlle = strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION));
                                    switch ($tipoAlle) {
                                        case 'xhtml':
                                            $contentFile = @file_get_contents($doc['FILEPATH']);
                                            if (!$contentFile) {
                                                Out::msgStop("Attenzione", "Errore in lettura del contenuto del file " . $doc['FILEPATH']);
                                                break;
                                            }
                                            $rowid = $_POST[$this->nameForm . '_PROPAS']['ROWID'];
                                            $proges_rec = $this->proLibPratica->GetProges($rowid, 'rowid');
                                            $proLibVar = new proLibVariabili();
                                            $proLibVar->setCodicePratica($this->currGesnum);
                                            $proLibVar->setChiavePasso($this->keyPropas);
                                            $dictionaryLegend = $proLibVar->getLegendaFascicolo('adjacency', 'smarty');
                                            $dictionaryValues = $proLibVar->getVariabiliPratica()->getAllData();
                                            $model = 'utiEditDiag';
                                            $rowidText = $_POST['rowid'];
                                            $_POST = array();
                                            $_POST['event'] = 'openform';
                                            $_POST['edit_text'] = $contentFile;
                                            $_POST['returnModel'] = $this->nameForm;
                                            $_POST['returnEvent'] = 'returnEditDiag';
                                            $_POST['returnField'] = '';
                                            $_POST['rowidText'] = $rowidText;
                                            $_POST['dictionaryLegend'] = $dictionaryLegend;
                                            $_POST['dictionaryValues'] = $dictionaryValues;
                                            $_POST['readonly'] = false;
                                            itaLib::openForm($model);
                                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                            $model();
                                            break;
                                    }
                                }
                            } else {
                                if ($doc['FILEORIG']) {
                                    $name = $doc['FILEORIG'];
                                } else {
                                    $name = $doc['FILENAME'];
                                }
                                Out::openDocument(utiDownload::getUrl(
                                                $name, $doc['FILEPATH']
                                        )
                                );
                            }
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if ($this->allegatiAzione[$_POST['rowid']]['PROTOCOLLO']) {
                            Out::msgBlock('', 3000, true, "<p style=\"color:red;font-size:1.5em;\" >Non è possibile cancellare il documento perché appartiene a un protocollo.</p>");
                            //Out::msgStop("Attenzione!", "Non è possibile cancellare il documento perché appartiene a un protocollo.");
                            break;
                        }
                        $allegato = $this->allegatiAzione[$_POST['rowid']];
                        if ($allegato['DOCLOCK'] == 1) {
                            Out::msgBlock('', 3000, true, "<p style=\"color:red;font-size:1.5em;\" >Impossibile cancellare l'allegato perchè risulta bloccato</p>");
//                            Out::msgInfo("Cancellazione Alleagti", "Impossibile cancellare l'allegato perche risulta bloccato");
                            break;
                        }
                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                    case $this->gridCampiAggiuntivi:
                        $gruppo = false;
                        foreach ($this->altriDati as $key => $dati) {
                            if ($dati['DAGSET'] === $_POST['rowid'] && $dati['isLeaf'] === 'false') {
                                $gruppo = true;
                            }
                        }

                        if ($gruppo === true) {
                            Out::msgQuestion("ATTENZIONE!", "Desideri cancellare il gruppo <b>" . substr($_POST['rowid'], -2) . "</b><br>
                                    con tutti i suoi campi aggiuntivi?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAgg', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancGruppo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            $this->rowidAppoggio = $_POST['rowid'];
                            break;
                        }

                        Out::msgQuestion("ATTENZIONE!", "L'operazione non è reversibile, sei sicuro di voler cancellare il Dato Aggiuntivo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAgg', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAgg', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        $this->rowidAppoggio = $_POST['rowid'];
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        //
                        // CONTROLLO E CREO SOTTO FASCICOLO
                        //
                        $pronode = $_POST[$this->nameForm . '_PROPAS']['PRONODE'];
                        $orgnodeRowid = $_POST[$this->nameForm . '_ORGNODEROWID'];
                        $descrizione = $_POST[$this->nameForm . '_PROPAS']['PRODPA'];
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);

                        if ($this->passoChiusura) {
                            $profilo = proSoggetto::getProfileFromIdUtente();
                            $_POST[$this->nameForm . '_PROPAS']['PRORPA'] = $profilo['COD_SOGGETTO'];
                            $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'] = $_POST[$this->nameForm . '_PROPAS']['PASPROUFF'];
                        }
                        if ($pronode == 1) {
                            $rowid_anapro_Azione = $this->proLibFascicolo->creaSottoFascicolo(
                                    $proges_rec['GESKEY'], $descrizione, array(
                                'UFF' => $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'],
                                'RES' => $_POST[$this->nameForm . '_PROPAS']['PRORPA'],
                                'GESPROUFF' => $_POST[$this->nameForm . '_PROPAS']['PASPROUFF']
                                    )
                            );
                        } else {
                            $rowid_anapro_Azione = $this->proLibFascicolo->creaAzione(
                                    $proges_rec['GESKEY'], $descrizione, array(
                                'UFF' => $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'],
                                'RES' => $_POST[$this->nameForm . '_PROPAS']['PRORPA'],
                                'GESPROUFF' => $_POST[$this->nameForm . '_PROPAS']['PASPROUFF']
                                    )
                            );
                        }
                        if (!$rowid_anapro_Azione) {
                            break;
                        }
                        if (!$orgnodeRowid) {
                            Out::msgStop("Attenzione!", "Riferimento al nodo Mancante");
                            break;
                        }
                        $anapro_Azione_rec = $this->proLib->GetAnapro($rowid_anapro_Azione, 'rowid');
                        //
                        // Registro azione e quindi la collego al contenitore padre
                        // 
                        $propas_rec = $_POST[$this->nameForm . '_PROPAS'];

//                        App::log($propas_rec);
//                        break;

                        $propas_rec['PRONUM'] = $this->currGesnum;
                        $propas_rec['PROPRO'] = $proges_rec['GESPRO'];
                        $propas_rec['PROINI'] = date('Ymd');
                        if ($propas_rec['PROSEQ'] == 0 || $propas_rec['PROSEQ'] == '') {
                            $propas_rec['PROSEQ'] = 99999;
                        }
                        $propas_rec['PROPAK'] = $this->proLibPratica->PropakGenerator($this->currGesnum);
                        $this->keyPropas = $propas_rec['PROPAK'];
                        $propas_rec['PROUTEADD'] = $propas_rec['PROUTEEDIT'] = $propas_rec['PASPROUTE'] = App::$utente->getKey('nomeUtente');
                        $propas_rec['PRODATEADD'] = $propas_rec['PRODATEEDIT'] = date("Ymd");
                        $propas_rec['PROORAADD'] = $propas_rec['PROORAEDIT'] = date("H:i:s");
                        $propas_rec['PASPRO'] = $anapro_Azione_rec['PRONUM'];
                        $propas_rec['PASPAR'] = $anapro_Azione_rec['PROPAR'];
                        $insert_Info = 'Oggetto: Inserimento Azione con chiave ' . $propas_rec['PROPAK'] . " e seq " . $propas_rec['PROSEQ'];
                        if (!$this->insertRecord($this->PROT_DB, 'PROPAS', $propas_rec, $insert_Info)) {
                            break;
                        }
                        if (!$this->RegistraAltriDati($propas_rec['PROPAK'], $propas_rec)) {
                            Out::msgStop("ERRORE", "Aggiornamento Atri Dati fallito");
                        }
                        //
                        // Collego al sotto fascicolo
                        //
                        $orgnode_rec = $this->proLib->GetOrgNode($orgnodeRowid, 'rowid');
                        if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $proges_rec['GESKEY'], $anapro_Azione_rec['PRONUM'], $anapro_Azione_rec['PROPAR'], $orgnode_rec['PRONUM'], $orgnode_rec['PROPAR'])) {
                            Out::msgStop("Aggiunta istanza Fallita", $this->proLibFascicolo->getErrMessage());
                            break;
                        }
                        //
                        // Rinfresco i dati in visualizzazione
                        //
                        $this->proLibPratica->ordinaPassi($this->currGesnum);
                        $this->proLibPratica->sincronizzaStato($this->currGesnum);
                        $this->Dettaglio($propas_rec['PROPAK']);
                        break;
                    case $this->nameForm . '_Duplica':
                        $where = " AND PRONUM = '" . $this->currGesnum . "'";
                        praRic::praRicPropas($this->nameForm, $where, '1');
                        break;
                    case $this->nameForm . '_Aggiorna':
//                        if ($_POST[$this->nameForm . "_PROPAS"]['PROINI'] == "" && $propas_rec['PROINI'] == "") {
//                            Out::msgQuestion("AGGIORNAMENTO!", "Vuoi aprire l'Azione?", array(
//                                'F8-No' => array('id' => $this->nameForm . '_AnnullaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                                'F5-Si' => array('id' => $this->nameForm . '_ConfermaApriPasso', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                    )
//                            );
//                            break;
//                        } else {
                        if ($this->AggiornaRecord()) {
                            $this->chiudiForm = true;
                            $this->returnToParent();
                        } else {
                            $this->chiudiForm = false;
                            $this->Dettaglio($this->keyPropas);
                        }
//                        }
                        break;
                    case $this->nameForm . '_ConfermaApriPasso':
                        if ($_POST[$this->nameForm . '_PROPAS']['PROFIN']) {
                            $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = "";
                            Out::valore($this->nameForm . '_PROPAS[PROFIN]', "");
//                        } else {
//                            $_POST[$this->nameForm . '_PROPAS']['PROINI'] = $this->workDate;
//                            Out::valore($this->nameForm . '_PROPAS[PROINI]', $this->workDate);
                        }
                    case $this->nameForm . '_AnnullaApriPasso':
                        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                        if ($this->AggiornaRecord()) {
                            $this->chiudiForm = true;
                            $this->returnToParent();
                        } else {
                            $this->chiudiForm = false;
                            $this->Dettaglio($this->keyPropas);
                        }
                        break;
                    case $this->nameForm . '_Scanner':
                        $this->ApriScanner();
                        break;
                    case $this->nameForm . '_FileLocale':
                        $this->AllegaFile();
                        break;
                    case $this->nameForm . '_PROPAS[PROCDR]_butt':
                        proRic::proRicAnamed($this->nameForm, $where, 'proAnamed', '1');
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]_butt':
                        praRic::praRicPraclt("proPassoPratica", "RICERCA Tipo Azione", "returnPraclt");
                        break;
                    case $this->nameForm . '_PROPAS[PRORPA]_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedMittPartenza');
                        break;
                    case $this->nameForm . '_VisualizzaTrasmissione':
                        $riga = $_POST[$this->gridAllegati]['gridParam']['selarrrow'];
                        $record = $this->allegatiAzione[$riga];
                        if (!$record['PRONUM'] || !$record['PROPAR']) {
                            break;
                        }
                        $arcite_rec = $this->proLib->GetArcite($record['PRONUM'], 'codice', false, $record['PROPAR']);
                        $model = 'proGestIter';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProGestIter');
                        $formObj->setReturnId('');
                        $_POST = array();
                        $_POST['rowidIter'] = $arcite_rec['ROWID'];
                        $_POST['tipoOpen'] = 'visualizzazione';
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        Out::setFocus('', 'proGestIter');
                        break;
                    case $this->nameForm . '_VisualizzaProtocollo':
                        $riga = $_POST[$this->gridAllegati]['gridParam']['selarrrow'];
                        $record = $this->allegatiAzione[$riga];
                        if (!$record['PRONUM'] || !$record['PROPAR']) {
                            break;
                        }
                        $anapro_rec = $this->proLib->GetAnapro($record['PRONUM'], 'codice', $record['PROPAR']);
                        $model = 'proArri';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProArri');
                        $formObj->setReturnId('');
                        $_POST = array();
                        $_POST['datiANAPRO']['ROWID'] = $anapro_rec['ROWID'];
                        $_POST['tipoProt'] = $anapro_rec['PROPAR'];
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        Out::setFocus('', 'proArri_Propre1');
                        break;
                    case $this->nameForm . '_ConfermaNote':
                        $this->ConfermaQualificaAllegati('PASNOTE', $_POST[$this->nameForm . '_Note']);
                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        $allegato = $this->allegatiAzione[$this->rowidAppoggio];
                        $anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'rowid');
                        /*
                         * Salvo ANADOCSAVE
                         */
                        $anadoc_rec['SAVEDATA'] = date('Ymd');
                        $anadoc_rec['SAVEORA'] = date("H:i:s");
                        $anadoc_rec['SAVEUTENTE'] = App::$utente->getKey('nomeUtente');
                        if (!$this->insertRecord($this->PROT_DB, 'ANADOCSAVE', $anadoc_rec, '', 'ROWID', false)) {
                            Out::msgStop("Errore", "Errore in salvataggio ANADOCSAVE.");
                            break;
                        }
                        $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                        if ($allegato) {
                            $basename = pathinfo($allegato['FILENAME'], PATHINFO_FILENAME);
                            $delete_Info = 'Oggetto: Cancellazione allegato' . $allegato['DOCFIL'];
                            if (!$this->deleteRecord($this->PROT_DB, 'ANADOC', $allegato['ROWID'], $delete_Info)) {
                                App::$utente->getKey('nomeUtente');
                                break;
                            }
                            if (strtolower($ext) == "pdf") {
                                $Prodst_rec = $this->proLibPratica->GetProdst($basename . ".info", "desc");
                                $fileNameTIF = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($allegato['FILENAME'], PATHINFO_FILENAME) . ".tif";
                                if ($Prodst_rec) {
                                    $dstset = $Prodst_rec['DSTSET'];
                                    $delete_Info = "Oggetto: Cancellazione data set $dstset";
                                    if (!$this->deleteRecord($this->PROT_DB, 'PRODST', $Prodst_rec['ROWID'], $delete_Info)) {
                                        Out::msgStop("Errore", "Errore in cancellazione del data set $dstset");
                                        break;
                                    }
                                    $sql = "SELECT * FROM PRODAG WHERE DAGSET = '$dstset' AND DAGPAK = '$this->keyPropas'";
                                    $Prodag_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
                                    if ($Prodag_tab) {
                                        $delete_Info = "Oggetto: Cancellazione dati aggiuntivi del file $basename.pdf";
                                        foreach ($Prodag_tab as $key => $Prodag_rec) {
                                            if (!$this->deleteRecord($this->PROT_DB, 'PRODAG', $Prodag_rec['ROWID'], $delete_Info)) {
                                                Out::msgStop("Errore", "Errore in cancellazione dato aggiuntivo " . $Prodag_rec['DAGKEY']);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if (strtolower($ext) == "xhtml") {
                                $fileNamePdf = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($allegato['FILENAME'], PATHINFO_FILENAME) . ".pdf";
                                $fileNameP7m = pathinfo($allegato['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($allegato['FILENAME'], PATHINFO_FILENAME) . ".pdf.p7m";
                            }
                            if (file_exists($allegato['FILEPATH'])) {
                                if (!@unlink($allegato['FILEPATH'])) {
                                    Out::msgStop("Gestione Documenti", "Errore in cancellazione file.");
                                    break;
                                }
                            }
                            if ($fileNamePdf) {
                                if (file_exists($fileNamePdf)) {
                                    if (!@unlink($fileNamePdf)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file pdf.");
                                        break;
                                    }
                                }
                            }
                            if ($fileNameP7m) {
                                if (file_exists($fileNameP7m)) {
                                    if (!@unlink($fileNameP7m)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file p7m.");
                                        break;
                                    }
                                }
                            }
                            if ($fileNameTIF) {
                                if (file_exists($fileNameTIF)) {
                                    if (!@unlink($fileNameTIF)) {
                                        Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file TIF.");
                                        break;
                                    }
                                }
                            }
                        }
                        $this->caricaAllegatiAnadoc();
                        break;
                    case $this->nameForm . '_ConfermaCancAgg':



                        $dagset = substr($this->rowidAppoggio, 0, 35);
                        $dagkey = substr($this->rowidAppoggio, 35);
                        $sql = "SELECT * FROM PRODAG WHERE DAGSET = '" . $dagset . "' AND DAGKEY = '" . $dagkey . "' AND DAGNUM = '" . $this->currGesnum . "'";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
                        if ($prodag_rec) {
                            $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $prodag_rec['DAGKEY'] . " dell'Azione " . $prodag_rec['DAGPAK'];
                            if (!$this->deleteRecord($this->PROT_DB, 'PRODAG', $prodag_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Cancellazione dati Aggiuntivi File", "Errore nella cancellazione del dato: " . $dagkey);
                                break;
                            }
                        }
                        foreach ($this->altriDati as $key => $campo) {
                            if ($campo['DAGSET'] == $this->rowidAppoggio) {
                                unset($this->altriDati[$key]);
                            }
                        }

                        if ($this->altriDati == false) {
                            Out::show($this->nameForm . '_InviaProcedura');
                        }
                        $this->caricaDatiAggiuntivi();
                        break;
                    case $this->nameForm . '_Apri':
                        $this->ApriPasso();
                        break;
                    case $this->nameForm . '_Chiudi':
                        $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = $this->workDate;
                        Out::valore($this->nameForm . '_PROPAS[PROFIN]', $this->workDate);
                        if (!$this->AggiornaRecord()) {
                            Out::msgStop("Attenzione!!!!", "Chiusura Azione Fallita");
                        }
                        break;
                    case $this->nameForm . '_ConfermaCancGruppo':
                        $sql = "SELECT * FROM PRODAG WHERE DAGPAK = '" . $this->keyPropas . "' AND DAGSET = '" . $this->rowidAppoggio . "' AND DAGNUM = '" . $this->currGesnum . "'";
                        $prodag_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

//
//Cancello dati aggiuntivi da db
//
                        foreach ($prodag_tab as $key => $prodag_rec) {
                            $dagset = $prodag_rec['DAGSET'];
                            $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $prodag_rec['DAGKEY'] . " dell'Azione " . $prodag_rec['DAGPAK'];
                            if (!$this->deleteRecord($this->PROT_DB, 'PRODAG', $prodag_rec['ROWID'], $delete_Info)) {
                                break;
                            }
                        }

//
//cancello data set
//
                        $Prodst_set = $this->proLibPratica->GetProdst($dagset);
                        $delete_Info = "Oggetto: Cancellazione data set $dagset";
                        if (!$this->deleteRecord($this->PROT_DB, 'PRODST', $Prodst_set['ROWID'], $delete_Info)) {
                            break;
                        }

//
//Cancello dati aggiuntivi da array
//
                        foreach ($this->altriDati as $key => $campo) {
                            if (substr($campo['DAGSET'], 0, 35) == $this->rowidAppoggio) {
                                unset($this->altriDati[$key]);
                            }
                        }
                        if ($this->altriDati == false) {
                            Out::show($this->nameForm . '_InviaProcedura');
                        }
                        $this->caricaDatiAggiuntivi();
                        break;
                    case $this->nameForm . '_ConfermaGenPdf':

//
// Leggo il body Value
//
                        $doc = $this->allegatiAzione[$this->rowidAppoggio];
                        $bodyValue = @file_get_contents($doc['FILEPATH']);
//
// Preparo il dizionario
//
                        $proLibVar = new proLibVariabili();
                        $proLibVar->setCodicePratica($_POST[$this->nameForm . '_PROPAS']['PRONUM']);
                        $proLibVar->setChiavePasso($_POST[$this->nameForm . '_PROPAS']['PROPAK']);
                        $dictionaryValues = $proLibVar->getVariabiliPratica()->getAllData();

//
// Creo il PDF
//

                        $filepath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                        $newFilePdf = pathinfo($doc['FILENAME'], PATHINFO_FILENAME) . ".pdf";
                        $pdfPreview = $this->docLib->Xhtml2Pdf($bodyValue, $dictionaryValues, $filepath . "/" . $newFilePdf);
                        if ($pdfPreview === false) {
                            Out::msgStop("Errore", $this->docLib->getErrMessage());
                            break;
                        }
                        $this->caricaAllegatiAnadoc();
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::valore($this->nameForm . '_Destinazione', '');
                        Out::valore($this->nameForm . '_DescrizioneVai', '');
                        Out::valore($this->nameForm . '_PROPAS[PROVPA]', '');
                        break;
                    case $this->nameForm . '_UploadFileEsterno':
                        $acq_model = 'utiAcqrMen';
                        Out::closeDialog($acq_model);
                        $_POST = array();
                        $_POST[$acq_model . '_flagPDFA'] = $this->praLib->getFlagPDFA();
                        $_POST[$acq_model . '_returnModel'] = $this->nameForm;
                        $_POST[$acq_model . '_returnField'] = $this->nameForm . '_CaricaGridAllegati';
                        $_POST[$acq_model . '_returnMethod'] = 'returnAcqrList';
                        $_POST[$acq_model . '_title'] = 'Allegati all\'Azione del Procedimento';
                        $_POST[$acq_model . '_tipoNome'] = 'original';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($acq_model);
                        $appRoute = App::getPath('appRoute.' . substr($acq_model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $acq_model . '.php';
                        $acq_model();
                        break;
                    case $this->nameForm . '_TestoBase':
                        $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
                        $documento = $this->docLib->getDocumenti($propas_rec['PROTBA'], 'codice');
                        $caricato = false;
                        foreach ($this->allegatiAzione as $alle) {
                            if ($alle['PROVENIENZA'] == "TESTOBASE" && ($alle['FILENAME'] == $documento['URI'] || $alle['FILENAME'] == $propas_rec['PROTBA'])) {
                                $caricato = true;
                                break;
                            }
                        }

                        if ($propas_rec['PROTBA'] == "" || $caricato == true) {
                            docRic::docRicDocumenti($this->nameForm, " WHERE CLASSIFICAZIONE = 'PRATICHE' AND TIPO = 'XHTML'");
                        } else {
                            Out::msgQuestion("ATTENZIONE!", "E' stato trovato un Testo Base inerente a questa Azione.<br>Lo vuoi caricare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaTestoBase', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_TestoAssociato':
                        Out::msgQuestion("Upload testo associato", "Scegli il tipo di ricerca per il testo", array(
                            'F4-Scegli Procedimento' => array('id' => $this->nameForm . '_SiSscegliProc', 'model' => $this->nameForm, 'shortCut' => "f4"),
                            'F5-Procedimento in corso' => array('id' => $this->nameForm . '_SiSingoloProc', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F8-Tutti' => array('id' => $this->nameForm . '_NoTuttiProc', 'model' => $this->nameForm, 'shortCut' => "f8")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaTestoBase':
                        $propas_rec = $this->proLibPratica->GetPropas($_POST[$this->nameForm . "_PROPAS"]['PROPAK'], 'propak');
                        $this->caricaTestoBase($propas_rec['PROTBA']);
                        break;
                    case $this->nameForm . '_AnnullaTestoBase':
                        docRic::docRicDocumenti($this->nameForm, " WHERE CLASSIFICAZIONE = 'PRATICHE' AND TIPO = 'XHTML'");
                        break;
                    case $this->nameForm . '_PROPAS[PROSTATO]_butt':
                        $where = '';
                        if ($this->passoChiusura) {
                            $where = " WHERE STPFLAG LIKE 'Chiusa%' ";
                        }
                        praRic::praRicAnastp($this->nameForm, $where, $this->nameForm . '_PROPAS[PROSTATO]');
                        break;
                    case $this->nameForm . '_AllegaPdfFirmato':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadP7m";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VisualizzaFirme':
                        if (array_key_exists($this->rowidAppoggio, $this->allegatiAzione) == true) {
                            $filePathP7M = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
                            $fileOrig = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                            $this->proLibAllegati->VisualizzaFirme($filePathP7M, $fileOrig);
                        }
                        break;
                    case $this->nameForm . '_DownloadFile':
                        if (array_key_exists($this->rowidAppoggio, $this->allegatiAzione) == true) {
                            $dirName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                            $baseName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME);
                            $baseFile = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                            Out::openDocument(utiDownload::getUrl($baseName . ".$this->tipoFile", $dirName . "/" . $baseFile . ".$this->tipoFile", true));
                        }
                        break;
                    case $this->nameForm . '_VisualizzaPdf':
                        if (array_key_exists($this->rowidAppoggio, $this->allegatiAzione) == true) {
                            $dirName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                            $baseName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                            Out::openDocument(utiDownload::getUrl(
                                            $baseName . ".pdf", $dirName . "/" . $baseName . ".pdf"
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_RemoveTifFile':
                        $dirName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                        $baseName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                        if (file_exists($dirName . "/" . $baseName . ".tif")) {
                            if (!@unlink($dirName . "/" . $baseName . ".tif")) {
                                Out::msgStop("Cancellazione File TIF", "Errore nell'eliminazione del file TIF<br>$dirName/$baseName.tif");
                            }
                        }
                        $this->caricaAllegatiAnadoc();
//                        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                        break;
                    case $this->nameForm . '_DeleteFile':
                        $dirName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                        $baseName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                        if (file_exists($dirName . "/" . $baseName . ".$this->tipoFile")) {
                            if (@unlink($dirName . "/" . $baseName . ".$this->tipoFile")) {
                                if ($this->tipoFile == "pdf") {
                                    $docStato = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
                                } else if ($this->tipoFile == "pdf.p7m") {
                                    $docStato = "<span>PDF generato. Clicca sull'icona per allegare il file firmato</span>";
                                }
                                $anadoc_rec = $this->proLib->GetAnadoc($this->allegatiAzione[$this->rowidAppoggio]['ROWID'], 'ROWID');
                                $anadoc_rec['DOCLOG'] = $docStato;
                                $update_Info = 'Oggetto : Aggiornamento allegato' . $anadoc_rec['DOCFIL'];
                                if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                                    Out::msgStop("Aggiornamento allegati", "Errore nell'aggiornamento dell'allegato " . $anadoc_rec['DOCFIL']);
                                    break;
                                }
                            }
                            $this->caricaAllegatiAnadoc();
//                            $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                        }
                        break;
                    case $this->nameForm . '_FirmaFile':
                        $doc = $this->allegatiAzione[$this->rowidAppoggio];
                        $inputFile = $doc['FILEPATH'];
                        $outputFile = $doc['FILEPATH'] . ".p7m";
                        $fileOrig = $doc['FILEORIG'];
                        $return = "returnFromSignAuth";
                        if (strtoupper(pathinfo($doc['FILENAME'], PATHINFO_EXTENSION)) == "XHTML") {
                            $inputFile = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME) . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf";
                            $fileOrig = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf";
                            if (file_exists($inputFile . ".p7m")) {
                                $inputFile .= '.p7m';
                                $fileOrig .= '.p7m';
                            }
                            $return = "returnFromSignAuthTestiBase";
                        }
                        itaLib::openForm('proFirma', true);
                        /* @var $proFirma proFirma */
                        $proFirma = itaModel::getInstance('proFirma');
                        $proFirma->setEvent('openform');
                        $proFirma->setReturnEvent($return);
                        $proFirma->setReturnModel($this->nameForm);
                        $proFirma->setReturnId('');
                        $proFirma->setInputFilePath($inputFile);
//                        $proFirma->setOutputFilePath($outputFile);
                        $proFirma->setinputFileName($fileOrig);
                        $proFirma->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
                        $proFirma->parseEvent();

                        break;
                    case $this->nameForm . '_CaricaCampiAgg':
                        $doc = $this->allegatiAzione[$this->rowidAppoggio];
                        $this->arrayInfo = praRic::praCampiPdf($doc['FILEPATH'], $this->nameForm, 'returnCampiPdf', "true", $doc['FILEORIG']);
                        break;
                    case $this->nameForm . '_AnnullaPDFA':
                        if ($this->currAllegato['uplFile']) {
                            unlink($this->currAllegato['uplFile']);
                            Out::msgInfo('Allega PDF', "Allegato Rifiutato:" . $this->currAllegato['uplFile']);
                        }
                        break;
                    case $this->nameForm . '_ConfermaPDFA':
                        if (!@rename($this->currAllegato['uplFile'], $this->currAllegato['destFile'])) {
                            Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                        } else {
                            $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile'], array("PROVENIENZA" => "ESTERNO"));
                            Out::msgInfo('Allega PDF', "Allegato PDF Accettato nonostante la non Conformità a PDF/A:" . $this->currAllegato['origFile']);
                        }
                        break;
                    case $this->nameForm . '_ScaricaTestoAss':
                        if (array_key_exists($this->rowidAppoggio, $this->testiAssociati) == true) {
                            Out::openDocument(utiDownload::getUrl(
                                            $this->testiAssociati[$this->rowidAppoggio]['FILENAME'], $this->testiAssociati[$this->rowidAppoggio]['FILEPATH'], true
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_AllegaTestoAss':
                        $this->caricaTestoAssociato($this->rowidAppoggio);
                        break;
                    case $this->nameForm . '_SiSscegliProc':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", "");
                        break;
                    case $this->nameForm . '_NoTuttiProc':
                        $tutti = true;
                    case $this->nameForm . '_SiSingoloProc':
                        $procedimento = $_POST[$this->nameForm . "_PROPAS"]['PROPRO'];
                        $this->testiAssociati = $this->GetTestiAssociati($procedimento, $tutti);
                        if (!$this->testiAssociati) {
                            Out::msgStop("Attenzione!!!", "Testi non trovati");
                            break;
                        }
                        if ($tutti == false) {
                            $msg = "per il procedimento n. $procedimento";
                        }
                        praRic::ricImmProcedimenti($this->testiAssociati, $this->nameForm, 'returnTestiAssociati', "Testi Disponibili $msg");
                        break;
                    case $this->nameForm . '_ConvertiPDFA':
                        $retConvert = $this->docLib->convertiPDFA($this->currAllegato['uplFile'], $this->currAllegato['destFile'], true);

                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($this->currAllegato['randName'], $this->currAllegato['destFile'], $this->currAllegato['origFile'], array("PROVENIENZA" => "ESTERNO"));
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A: " . $this->currAllegato['origFile']);
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                        break;
                    case $this->nameForm . '_DocFormale':
                        $this->nuovoProtocollo('C');
                        break;
                    case $this->nameForm . '_ProtPartenza':
                        $this->nuovoProtocollo('P');
                        break;
                    case $this->nameForm . '_Protocolla':
                        $this->appoggioDati = array();
                        foreach ($this->allegatiAzione as $allegato) {
                            if ($allegato['ROWID']) {
                                if (
                                        (
                                        strtolower(pathinfo($allegato['FILEORIG'], PATHINFO_EXTENSION)) == "p7m" ||
                                        strtolower(pathinfo($allegato['FILEORIG'], PATHINFO_EXTENSION)) == "xhtml" ||
                                        strtolower(pathinfo($allegato['FILEORIG'], PATHINFO_EXTENSION)) == "pdf"
                                        ) &&
                                        $allegato['PROTOCOLLO'] != '1'
                                ) {
                                    $this->appoggioDati[] = $allegato;
                                }
                            }
                        }
                        if ($this->appoggioDati) {

                            $colNames = array(
                                "Nome File",
                                "Descrizione"
                            );
                            $colModel = array(
                                array("name" => 'FILEORIG', "width" => 200),
                                array("name" => 'FILEINFO', "width" => 250)
                            );
                            $msgDetail = "<span style=\"font-size:1.4em;color:green;\"><b>Seleziona i documenti da Protocollare.</b></span>";
                            proRic::proMultiselectGeneric($this->appoggioDati, $this->nameForm, 'DaProtocollare', 'Elenco Documenti da Protocollare', $colNames, $colModel, $msgDetail, array('width' => '500', 'height' => '350'));
                        } else {
                            $this->selectNewProt();
                        }
                        break;
                    case $this->nameForm . '_UFFRESP_butt':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PRORPA'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if ($anamed_rec) {
                                proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            }
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParentClose();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PRORPA]':
                        $this->DecodResponsabile($_POST[$this->nameForm . '_PROPAS']['PRORPA']);
                        break;
                    case $this->nameForm . '_PROPAS[PROCLT]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PROCLT'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $praclt_rec = $this->praLib->GetPraclt($codice);
                        if ($praclt_rec) {
                            Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
                            Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROCDR]':
                        $codice = $_POST[$this->nameForm . '_PROPAS']['PROCDR'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnamed($codice);
                        }
                        break;
                    case $this->nameForm . '_DESC_DESTINATARIO':
                        $anamed_tab = $this->proLib->GetAnamed($_POST[$this->nameForm . '__DESC_DESTINATARIO'], 'nome', 'si', true);
                        if (count($anamed_tab) == 1) {
                            $this->DecodAnamedCom($anamed_tab[0]['ROWID'], 'rowid');
                        }
                        break;
                    case $this->nameForm . '_PROPAS[PROSTATO]':
                        if ($_POST[$this->nameForm . '_PROPAS']['PROSTATO']) {
                            $codice = $_POST[$this->nameForm . '_PROPAS']['PROSTATO'];
                            $where = '';
                            if ($this->passoChiusura) {
                                $where = " AND STPFLAG LIKE 'Chiusa%'";
                            }
                            $anastp_rec = $this->praLib->GetAnastp($codice, 'rowid', $where);
                            Out::valore($this->nameForm . '_PROPAS[PROSTATO]', $anastp_rec['ROWID']);
                            Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                        } else {
                            Out::valore($this->nameForm . '_Stato1', "");
                        }
                        break;
                }
                break;
            case 'returnIFrame':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_protocollaRemotoPartenza':
                    case $this->nameForm . '_protocollaRemotoArrivo':
                        $this->Dettaglio($this->keyPropas);
                        break;
                }
                break;
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '1':
                        $this->DecodAnamed($_POST['retKey'], 'rowid');
                        break;
                }
                break;

            case "returnPraclt":
                $praclt_rec = $this->praLib->GetPraclt($_POST["retKey"], 'rowid');
                if ($praclt_rec) {
                    Out::valore($this->nameForm . '_PROPAS[PROCLT]', $praclt_rec['CLTCOD']);
                    Out::valore($this->nameForm . '_PROPAS[PRODTP]', $praclt_rec['CLTDES']);
                }
                break;
            case 'returnPraidc':
                $praidc_rec = $this->praLib->GetPraidc($_POST['retKey'], 'rowid');
                foreach ($this->altriDati as $dati) {
                    if ($dati['DAGKEY'] == $praidc_rec['IDCKEY']) {
                        $trovato = true;
                        break;
                    }
                }
                if (!$trovato) {
                    if ($_POST['retid'] == "") {
                        $incDagset = $this->proLibPratica->GetIncDagset($this->altriDati);
                        $campoLevel0 = array();
                        $campoLevel0['DAGSET'] = $this->keyPropas . '_' . $incDagset;
                        $campoLevel0['DAGKEY'] = "Data Set " . $incDagset;
                        $campoLevel0['DAGVAL'] = '';
                        $campoLevel0['ADD'] = '<span class="ui-icon ui-icon-plus">Aggiungi Campo in Gruppo ' . $campoLevel0['DAGKEY'] . '</span>';
                        $campoLevel0['level'] = 0;
                        $campoLevel0['parent'] = null;
                        $campoLevel0['isLeaf'] = 'false';
                        $campoLevel0['expanded'] = 'true';
                        $campoLevel0['loaded'] = 'true';
                        $this->altriDati[] = $campoLevel0;
                    }
                    $nuovo_campo = array();
                    $nuovo_campo["ROWID"] = 0;
                    $nuovo_campo["DAGNUM"] = '';
                    $nuovo_campo["DAGCOD"] = '';
                    $nuovo_campo["DAGSEQ"] = 0;
                    $nuovo_campo["DAGSFL"] = $praidc_rec['IDCSEQ'];
                    $nuovo_campo["DAGKEY"] = $praidc_rec['IDCKEY'];
                    $nuovo_campo["DAGTIP"] = $praidc_rec['IDCTIP'];
                    $nuovo_campo["DAGDES"] = $praidc_rec['IDCDES'];
                    $nuovo_campo["DAGVAL"] = '';
                    $nuovo_campo["DAGPAK"] = '';
                    $nuovo_campo["DAGFIN__1"] = $praidc_rec['IDCFIN__1'];
                    $nuovo_campo["DAGFIN__2"] = $praidc_rec['IDCFIN__2'];
                    $nuovo_campo["DAGFIN__3"] = $praidc_rec['IDCFIN__3'];
                    $nuovo_campo["DAGFIA__1"] = $praidc_rec['IDCFIA__1'];
                    $nuovo_campo["DAGFIA__2"] = $praidc_rec['IDCFIA__2'];
                    $nuovo_campo["DAGFIA__3"] = $praidc_rec['IDCFIA__3'];
                    $nuovo_campo['level'] = 1;
                    if ($_POST['retid'] == "") {
                        $nuovo_campo['DAGSET'] = $this->keyPropas . '_' . $incDagset . $nuovo_campo['DAGKEY'];
                        $nuovo_campo['parent'] = $this->keyPropas . '_' . $incDagset;
                    } else {
                        $nuovo_campo['DAGSET'] = $_POST['retid'] . $nuovo_campo['DAGKEY'];
                        $nuovo_campo['parent'] = $_POST['retid'];
                    }
                    $nuovo_campo['isLeaf'] = 'true';
                    $nuovo_campo['expanded'] = 'true';
                    $nuovo_campo['loaded'] = 'true';
                    if ($_POST['retid'] == "") {
                        $this->altriDati[] = $nuovo_campo;
                    } else {
                        $appoggio = $this->getPosizioneCampo($this->altriDati, $_POST['retid'], $nuovo_campo);
                        $this->altriDati = $appoggio;
                    }
                    Out::hide($this->nameForm . '_InviaProcedura');
                    $this->caricaDatiAggiuntivi('1', '100000');
                }
                break;
            case 'returnAcqrList':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CaricaGridAllegati':
                        if ($_POST['retList']) {
                            $lista = $_POST['retList'];
                            $this->caricaAllegatiEsterni($lista);
                        }
                        break;
                }
                break;
            case 'returnPropas':
                $this->DecodVaialpasso($_POST["retKey"], 'rowid');
                break;
            case 'returnPropas1':
                $propas_rec = $this->proLibPratica->GetPropas($_POST["retKey"], 'rowid');
                if ($propas_rec) {
                    $this->DecodResponsabile($propas_rec['PRORPA'], 'codice');
                    $this->DecodStatoPasso($propas_rec['PROSTATO']);
//                    $this->DecodStatoPassoAP($propas_rec['PROSTAP']);
//                    $this->DecodStatoPassoCH($propas_rec['PROSTCH']);
                    $open_Info = 'Oggetto: Apro in duplicazione l\'Azione ' . $propas_rec['PROSEQ'];
                    $this->openRecord($this->PROT_DB, 'PROPAS', $open_Info);
                    Out::valori($propas_rec, $this->nameForm . '_PROPAS');
                    Out::valore($this->nameForm . "_PROPAS[PROSEQ]", "");
                }
                break;
            case "returnAnastp";
                $anastp_rec = $this->praLib->GetAnastp($_POST['retKey'], 'rowid');
                switch ($_POST['retid']) {
                    case $this->nameForm . '_PROPAS[PROSTATO]':
                        Out::valore($this->nameForm . '_PROPAS[PROSTATO]', $anastp_rec['ROWID']);
                        Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);
                        break;
                }
                break;
            case "returnDocumenti";
                $this->caricaTestoBase($_POST['retKey'], 'rowid');
                break;
            case "returnTestiAssociati";
                Out::msgQuestion("ATTENZIONE!", "Cosa vuoi fare con il file selezionato?", array(
                    'F8-Allega' => array('id' => $this->nameForm . '_AllegaTestoAss', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Scarica' => array('id' => $this->nameForm . '_ScaricaTestoAss', 'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                $this->rowidAppoggio = $_POST['retKey'];
                break;

            case "returnPraPDFComposer":
                $fileComposer = $_POST["retFileComposer"];
                $nomeComposer = $_POST["retNomeComposer"];
                if (!is_file($fileComposer)) {
                    Out::msgStop("Attenzione", "File composito non raggiungibile.");
                    break;
                }
                if (!$nomeComposer) {
                    Out::msgStop("Attenzione", "Nome File composito di destinazione mancante.");
                    break;
                }
                $this->aggiungiAllegato(md5(rand() * time()) . "." . pathinfo($fileComposer, PATHINFO_EXTENSION), $fileComposer, $nomeComposer, array("PROVENIENZA" => "ESTERNO"));
                break;
            case 'returnFileFromTwain':
                $this->SalvaScanner();
                break;
            case 'returnEditDiag':
                $doc = $this->allegatiAzione[$_POST['rowidText']];
                $newContent = $_POST['returnText'];
                if (!file_put_contents($doc['FILEPATH'], $newContent)) {
                    Out::msgStop("Attenzione", "Errore in aggiornamento del file $codice");
                } else {
                    Out::msgInfo("Aggiornamento Testo", "Testo " . $doc['INFO'] . " aggiornato correttamente");
                }
                break;
            case 'returnUploadP7m':
                $uplFile = $_POST['uploadedFile'];
                if (strtolower(pathinfo($uplFile, PATHINFO_EXTENSION)) != "p7m") {
                    Out::msgStop("Errore caricamento file", "il file scelto non risulta essere un pdf firmato");
                    break;
                }

                if (!$this->docLib->AnalizzaP7m($uplFile)) {
                    break;
                }

//
//Copio il file firmato nella cartella del passo
//
                $dirName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'], PATHINFO_DIRNAME);
                $baseName = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILENAME'], PATHINFO_FILENAME);
                if (!@rename($uplFile, $dirName . "/" . $baseName . ".pdf.p7m")) {
                    Out::msgStop("Spostamento pdf firmato", "Errore nella copia del pdf firmato $baseName.pdf.p7m");
                    break;
                }

                $anadoc_rec = $this->proLib->GetAnadoc($this->allegatiAzione[$this->rowidAppoggio]['ROWID'], 'rowid');
                $anadoc_rec['DOCLOG'] = "<span>Pdf firmato caricato in data " . date('d/m/Y') . " alle ore " . date('H:i:s') . "</span>";
                $update_Info = 'Oggetto : Aggiornamento allegato' . $anadoc_rec['DOCFIL'];
                if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                    Out::msgStop("Aggiornamento allegati", "Errore nell'aggiornamento dell'allegato " . $anadoc_rec['DOCFIL']);
                    break;
                }
                $this->caricaAllegatiAnadoc();
//                $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                break;
            case 'reloadDettaglio':
                if ($_POST['rowid']) {
                    $this->dettaglio($_POST['rowid'], 'rowid');
                }
                break;
            case 'cellSelect':
                $doc = $this->allegatiAzione[$_POST['rowid']];
                $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
                $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
                if ($propas_rec['PROVISIBILITA'] == "Protetto") {
                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                        if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                            break;
                        }
                    }
                }

                switch ($_POST['id']) {
                    case $this->gridCampiAggiuntivi:
                        switch ($_POST['colName']) {
                            case 'ADD':
                                praRic::praRicPraidc($this->nameForm, 'returnPraidc', $_POST['rowid']);
                                break;
                        }
                        break;
                    case $this->gridAllegati:
                        $doc = $this->allegatiAzione[$_POST['rowid']];
                        $ext = pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION);
                        switch ($_POST['colName']) {
                            case "NUMPROT":
                                if ($doc['PROTOCOLLO'] && $doc['PRONUM'] && $doc['PROPAR']) {
                                    Out::msgQuestion("Visualizzazione documento.", "Vuoi visualizzare il dettaglio del protocollo o della trasmissione?", array(
                                        'F8-Visualizza Trasmissione' => array('id' => $this->nameForm . '_VisualizzaTrasmissione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Visualizza Protocollo' => array('id' => $this->nameForm . '_VisualizzaProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                    break;
                                }
                                break;

                            case "EVIDENZIA":
                            case "LOCK";
                                $chiave = $_POST['rowid'];
                                if (substr($chiave, 0, 4) != "DOC-") {
                                    break;
                                }
                                $allegato = $this->allegatiAzione[$chiave];
                                if ($allegato['PROTOCOLLO'] == 1) {
                                    break;
                                }
                                $codice = substr($_POST['rowid'], 4);
                                $anadoc_rec = $this->proLib->GetAnadoc($codice, 'codice');
                                if ($anadoc_rec) {
                                    $orig_name = ($anadoc_rec['DOCNAME']) ? $anadoc_rec['DOCNAME'] : $anadoc_rec['DOCFIL'];
                                }
                                if ($_POST['colName'] == "EVIDENZIA") {
                                    if (!$allegato['DOCEVI']) {
                                        $allegato['DOCEVI'] = 1;
                                        $color = "color:red;";
                                        $fontWeight = "font-weight:bold;";
                                        $fontSize = "font-size:1.2em;";
                                    } else {
                                        $allegato['DOCEVI'] = 0;
                                        $color = "color:black";
                                    }
                                    $allegato['NAME'] = "<p style = \"$color $fontWeight $fontSize\">" . $orig_name . "</p>";
                                } elseif ($_POST['colName'] == "LOCK") {


                                    if ($allegato['DOCLOCK'] == 1) {
                                        $allegato['DOCLOCK'] = 0;
                                        $icon = "unlock";
                                        $title = "Blocca Documento";
                                    } else {
                                        $allegato['DOCLOCK'] = 1;
                                        $icon = "lock";
                                        $title = "Sblocca Documento";
                                    }
                                    $allegato['LOCK'] = "<span class=\"ita-icon ita-icon-$icon-16x16\">$title</span>";
                                }
                                $this->allegatiAzione[$chiave] = $allegato;
//
// Sincronizzo DB
//
                                if ($anadoc_rec) {
                                    $anadoc_rec['DOCEVI'] = $allegato['DOCEVI'];
                                    $anadoc_rec['DOCLOCK'] = $allegato['DOCLOCK'];
                                    $update_Info = "Oggetto: " . $anadoc_rec['DOCFIL'];
                                    if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                                        Out::msgStop("Errore in Aggionamento", "Aggiornamento note Documento fallito.");
                                        break;
                                    }
                                }
//
// Disegno Tabella
//
                                $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione);
                                break;
                            case 'PREVIEW':
                                $chiave = $_POST['rowid'];
                                if (substr($chiave, 0, 4) != "DOC-") {
                                    break;
                                }
                                $allegato = $this->allegatiAzione[$chiave];
                                if ($allegato['PROTOCOLLO'] == 1) {
                                    break;
                                }
                                if (strtolower($ext) == "pdf") {
                                    $this->rowidAppoggio = $_POST['rowid'];
                                    $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                    Out::msgQuestion("Gestione Allegato", "", array(
                                        'F4-Carica Campi Aggiuntivi' => array('id' => $this->nameForm . '_CaricaCampiAgg', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9")
                                            ), 'auto', 'auto', 'true', false, true, true
                                    );
                                    $this->rowidAppoggio = $_POST['rowid'];
                                } elseif (strtolower($ext) == "tif") {
                                    Out::msgQuestion("Gestione Allegato", "", array(
                                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                                            ), 'auto', 'auto', 'true', false, true, true
                                    );
                                    $this->rowidAppoggio = $_POST['rowid'];
                                } elseif (strtolower($ext) == "p7m") {
                                    $this->proLibAllegati->VisualizzaFirme($doc['FILEPATH'], $doc['FILEORIG']);
                                } else if (strtolower($ext) == "xhtml") {
                                    $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
                                    if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                                        Out::msgQuestion("Gestione Allegato!", "", array(
                                            'F7-Visualizza Firme' => array('id' => $this->nameForm . '_VisualizzaFirme', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f7"),
                                            'F9-Scarica File firmato' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                                            'F8-Cancella File firmato' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                                                ), 'auto', 'auto', 'true', false, true, true
                                        );
                                        $this->tipoFile = "pdf.p7m";
                                    } else if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf")) {
                                        Out::msgQuestion("Gestione Allegato", "", array(
                                            'F4-Allega P7M' => array('id' => $this->nameForm . '_AllegaPdfFirmato', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $this->nameForm, 'shortCut' => "f4"),
                                            'F9-Scarica Pdf' => array('id' => $this->nameForm . '_DownloadFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-download-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                                            'F8-Cancella Pdf' => array('id' => $this->nameForm . '_DeleteFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-delete-32x32'", 'model' => $this->nameForm, 'shortCut' => "f8"),
                                            'F5-Visualizza Pdf' => array('id' => $this->nameForm . '_VisualizzaPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-cerca-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5"),
                                            'F6-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f6")
                                                ), 'auto', 'auto', 'true', false, true, true
                                        );
                                        $this->tipoFile = "pdf";
                                    } else {
                                        Out::msgQuestion("Gestione Allegato!", "", array(
                                            'F5-Genera PDF' => array('id' => $this->nameForm . '_ConfermaGenPdf', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-pdf-32x32'", 'model' => $this->nameForm, 'shortCut' => "f5")
                                                ), 'auto', 'auto', 'true', false, true, true
                                        );
                                    }
                                    $this->rowidAppoggio = $_POST['rowid'];
                                } else {
                                    $this->rowidAppoggio = $_POST['rowid'];
                                    Out::msgQuestion("Gestione Allegato", "", array(
                                        'F9-Firma Allegato' => array('id' => $this->nameForm . '_FirmaFile', "style" => "width:250px;height:40px;", 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $this->nameForm, 'shortCut' => "f9"),
                                            ), 'auto', 'auto', 'true', false, true, true
                                    );
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'returnAllegatiProc':
                $this->caricaAllegatiInterno();
                break;
            case 'returnAnapra':
                $Anapra_rec = $this->praLib->GetAnapra($_POST['retKey'], "rowid");
                $this->testiAssociati = $this->GetTestiAssociati($Anapra_rec['PRANUM']);
                if ($this->testiAssociati == false) {
                    Out::msgInfo("Ricerca testi", "testi associati non trovati");
                    break;
                }
                praRic::ricImmProcedimenti($this->testiAssociati, $this->nameForm, 'returnTestiAssociati', "Testi Disponibili per il procedimento n. " . $Anapra_rec['PRANUM']);
                break;
            case 'returnCampiPdf':
                $campi = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($selRows as $riga) {
                    $campi[] = $this->arrayInfo['arrayTable'][$riga];
                }
                $this->caricaCampiDaPdf($campi, $this->keyPropas); //, $this->arrayInfo['fileName']);
                $this->caricaAllegatiAnadoc();
//                $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                break;
            case 'returnExplodeZip':
                $arrayFile = $this->praLib->CaricaAllegatoDaZip($_POST['rowData'], $this->allegatiAzione);
                if ($arrayFile == false) {
                    break;
                }
                if (isset($arrayFile["daFile"])) {
//
//upload singolo file
//
                    foreach ($this->allegatiAzione as $posE => $allegato) {
                        if ($allegato['RANDOM'] == 'ESTERNO') {
                            $posEsterno = $posE;
                            $parent = $allegato['PROV'];
                            break;
                        }
                    }

                    $i = $posEsterno + 1;
                    $trovato = false;
                    while ($trovato == false) {
                        if ($i >= count($this->allegatiAzione)) {
                            $trovato = true;
                        } else {
                            if ($this->allegatiAzione[$i]['level'] == 0) {
                                $trovato = true;
                            } else {
                                $i++;
                            }
                        }
                    }
                    $arrayTop = array_slice($this->allegatiAzione, 0, $i);
                    $arrayDown = array_slice($this->allegatiAzione, $i);
                    $arrayFile["Allegato"]['parent'] = $parent;
                    $arrayFile["Allegato"]['PROV'] = $i;
                    $arrayFile["Allegato"]['NAME'] = '<span style = "color:orange;">' . $arrayFile['Allegato']['NAME'] . '</span>';
                    $arrayFile["Allegato"]['INFO'] = $arrayFile["Allegato"]['NOTE'];
                    $arrayFile["Allegato"]['FILEINFO'] = $arrayFile["Allegato"]['NOTE'];
                    $arrayTop[] = $arrayFile["Allegato"];

                    foreach ($arrayDown as $chiave => $recordDown) {
                        if ($recordDown['level'] == 1) {
                            $arrayDown[$chiave]['PROV'] = $recordDown['PROV'] + 1;
                        }
                    }
                    $this->allegatiAzione = array_merge($arrayTop, $arrayDown);
                } else {
//
//upload tutti i file della cartella
//
                }
                $this->caricaAllegatiAnadoc();
//                $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                break;
            case "returnFromSignAuthTestiBase";
                if ($_POST['result'] === true) {
                    $fileOrig = pathinfo($this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                    $this->allegatiAzione[$this->rowidAppoggio]['PREVIEW'] = '<span class="ita-icon ita-icon-shield-green-24x24" title="Verifica il file Firmato"></span>';
                    //$this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
                    TableView::setCellValue($this->gridAllegati, $this->rowidAppoggio, 'PREVIEW', $this->allegatiAzione[$this->rowidAppoggio]['PREVIEW']);
                    Out::msgBlock('', 2000, true, "File Firmato correttamente");
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;
            case "returnFromSignAuth";
                if ($_POST['result'] === true) {
                    if ($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'] != $_POST['outputFilePath']) {
                        if (!@unlink($this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'])) {
                            Out::msgStop("Firma remota", "cancellazione file " . $this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'] . " fallita");
                            break;
                        }
                    }
//                    $this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'] = $this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'] . ".p7m";
                    $this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'] = $_POST['outputFileName'];
//                    $this->allegatiAzione[$this->rowidAppoggio]['FILEINFO'] = $this->allegatiAzione[$this->rowidAppoggio]['FILEINFO'] . ".p7m";
//                    $this->allegatiAzione[$this->rowidAppoggio]['INFO'] = $this->allegatiAzione[$this->rowidAppoggio]['INFO'] . ".p7m";
                    $this->allegatiAzione[$this->rowidAppoggio]['FILEPATH'] = $_POST['outputFilePath'];
//                    $this->allegatiAzione[$this->rowidAppoggio]['FILENAME'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                    $this->allegatiAzione[$this->rowidAppoggio]['FILENAME'] = $_POST['outputFileName'];
                    $this->allegatiAzione[$this->rowidAppoggio]['RANDOM'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                    $this->allegatiAzione[$this->rowidAppoggio]['DOCDATAFIRMA'] = date("Ymd");
                    if ($this->allegatiAzione[$this->rowidAppoggio]['ROWID'] != 0) {
                        $this->allegatiAzione[$this->rowidAppoggio]['NAME'] = $this->allegatiAzione[$this->rowidAppoggio]['FILENAME'];
                        $anadoc_rec = $this->proLib->GetAnadoc($this->allegatiAzione[$this->rowidAppoggio]['ROWID'], "ROWID");
                        $anadoc_rec['DOCFIL'] = pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                        $anadoc_rec['DOCLNK'] = "allegato://" . pathinfo($_POST['outputFilePath'], PATHINFO_BASENAME);
                        $anadoc_rec['DOCNAME'] = $this->allegatiAzione[$this->rowidAppoggio]['FILEORIG'];
//                        $anadoc_rec['DOCNOT'] = $this->allegatiAzione[$this->rowidAppoggio]['FILEINFO'];
                        $anadoc_rec['DOCDATAFIRMA'] = $this->allegatiAzione[$this->rowidAppoggio]['DOCDATAFIRMA'];
                        $update_Info = 'Oggetto: Aggiornamento allegato ' . $this->allegatiAzione[$this->rowidAppoggio]['FILENAME'];
                        if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                            return false;
                        }
                    }
                    Out::openDocument(utiDownload::getUrl($_POST['inputFileName'] . ".p7m", $_POST['outputFilePath']));
                    $this->caricaAllegatiAnadoc();
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;
            case "returnMultiselectGeneric";
                switch ($_POST['retid']) {
                    case 'DaProtocollare':
                        $allegati = array();
                        $selezionati = explode(',', $_POST['retKey']);
                        foreach ($selezionati as $id) {
                            if ($id != '') {
                                $doc = $this->appoggioDati[$id];
                                $allegati[] = $doc;
                            }
                        }
                        $this->appoggioDati = $allegati;
                        $this->selectNewProt();
                        break;
                }
                break;
            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_PROPAS[PRORPA]");
                break;
            case 'returnanamedMittPartenza':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_PROPAS[PRORPA]', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_RESPONSABILE", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_PROPAS[PROUFFRES]", '');
                Out::valore($this->nameForm . "_UFFRESP", '');
                Out::setFocus('', $this->nameForm . "_PROPAS[PRORPA]");
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESC_DESTINATARIO':
                        /* new suggest  */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAMED WHERE MEDANN=0 AND " . $where;
                        $anamed_tab = $this->proLib->getGenericTab($sql);
                        if (count($anamed_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anamed_tab as $anamed_rec) {
                                itaSuggest::addSuggest($anamed_rec['MEDNOM']);
                            }
                        }
                        itaSuggest::sendSuggest();
//                        ob_clean();
//                        $anamed_tab = $this->proLib->GetAnamed($_POST['q'], 'nome', 'si', true);
//                        foreach ($anamed_tab as $anamed_rec) {
//                            header('Content-Type: text/plain; charset=ISO-8859-1');
//                            echo $anamed_rec['MEDNOM'] . "\n";
//                        }

                        break;
                    case $this->nameForm . '_PROPAS[PRODTP]':
                        /* new suggest  */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PRAM_DB->strLower('CLTDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT DISTINCT CLTDES FROM PRACLT WHERE " . $where;
                        $TipiPasso_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        if (count($TipiPasso_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($TipiPasso_tab as $TipiPasso_rec) {
                                itaSuggest::addSuggest($TipiPasso_rec['CLTDES']);
                            }
                        }
                        itaSuggest::sendSuggest();
//                        ob_clean();
//                        $TipiPasso_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT DISTINCT CLTDES FROM PRACLT WHERE LOWER(CLTDES) LIKE '%" . addslashes(strtolower($_POST['q'])) . "%'", true);
//                        foreach ($TipiPasso_tab as $TipiPasso_rec) {
//                            header('Content-Type: text/plain; charset=ISO-8859-1');
//                            echo $TipiPasso_rec['CLTDES'] . "\n";
//                        }

                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PRORPA]':
                        Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', '');
                        Out::valore($this->nameForm . '_UFFRESP', '');
                        $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'] = '';
                        $_POST[$this->nameForm . '_UFFRESP'] = '';
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_altriDati');
        App::$utente->removeKey($this->nameForm . '_passAlle');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnMethod');
        App::$utente->removeKey($this->nameForm . '_datiForm');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_$allegati');
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_pagina');
        App::$utente->removeKey($this->nameForm . '_sql');
        App::$utente->removeKey($this->nameForm . '_selRow');
        App::$utente->removeKey($this->nameForm . '_arrayInfo');
        App::$utente->removeKey($this->nameForm . '_currAllegato');
        App::$utente->removeKey($this->nameForm . '_tipoFile');
        App::$utente->removeKey($this->nameForm . '_testiAssociati');
        App::$utente->removeKey($this->nameForm . '_chiudiForm');
        App::$utente->removeKey($this->nameForm . '_datiRubricaWS');
        App::$utente->removeKey($this->nameForm . '_duplica');
        App::$utente->removeKey($this->nameForm . '_fascicolaDocumento');
        App::$utente->removeKey($this->nameForm . '_appoggioDati');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_passoChiusura');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParentClose($close = true) {
        if ($close) {
            $this->close();
        }
        Out::show($this->returnModel);
    }

    public function returnToParent($close = true) {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $model = $this->returnModel;
        $_POST = array();
        if ($model == 'proGestPratica') {
            itaLib::openForm($model);
            $formObj = itaModel::getInstance($model);
            $_POST['rowidDettaglio'] = $proges_rec['ROWID'];
            $_POST['paneVis'] = 'panePassi';
            $formObj->setEvent('openform');
        } else {
            $formObj = itaModel::getInstance($model);
            $formObj->setEvent($this->returnMethod);
        }
        $formObj->parseEvent();
        if ($close) {
            $this->close();
        }
        return;
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
        TableView::disableEvents($this->gridCampiAggiuntivi);
        TableView::clearGrid($this->gridCampiAggiuntivi);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridAllegati);
        $this->allegatiAzione = array();
        $this->altriDati = array();
        $this->destinatari = array();
        $this->passoChiusura = '';
    }

    private function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_Apri');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_InviaProcedura');
        Out::hide($this->nameForm . '_Duplica');
        Out::hide($this->nameForm . '_Sottofascicolo');
        Out::hide($this->nameForm . '_utenteAdd');
        Out::hide($this->nameForm . '_utenteEdit');
        Out::hide($this->nameForm . '_divGridDocumenti');
        Out::hide($this->nameForm . '_divBasso');
        Out::hide($this->nameForm . '_TestoAssociato');
        Out::hide($this->nameForm . '_Interno');
        Out::hide($this->nameForm . '_Composizione');
        Out::hide($this->nameForm . '_TestoAssociato');
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '100000') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function caricaCampiDaPdf($datiAgg, $chiavePasso) {//, $fileName) {
//
//controllo e i campi sono di un data set esistente o nuovo
//
        $trovato = false;
//        $count = 0;

        foreach ($this->altriDati as $dato) {
//if ($dato['DAGKEY'] == $fileName) {
            if ($dato['DAGKEY'] == $this->arrayInfo['pdfName']) {
                $dagset = $dato['DAGSET'];
                $trovato = true;
                break;
            }
        }
        if ($trovato == true) {
            foreach ($this->altriDati as $key => $dato) {
                if (substr($dato['DAGSET'], 0, 35) == $dagset && $dato['isLeaf'] == "true") {
                    $lastPos = $key;
                }
            }
        }

//
//Spezzo l'array in 2 dalla posizione in cui andro ad inserire i nuovi campi
//
        $arrayTop = array_slice($this->altriDati, 0, $lastPos);
        $arrayDown = array_slice($this->altriDati, $lastPos);

        if ($trovato == false && $lastPos == 0) {

//
//Trovo l'ultimo data set e lo incremento
//  
            $incDagset = $this->proLibPratica->GetIncDagset($this->altriDati);
//            $arrayPadri = array();
//            foreach ($this->altriDati as $keyDag => $dato) {
//                if ($dato['isLeaf'] == "false") {
//                    $arrayPadri[] = $dato;
//                }
//            }
//            if ($arrayPadri) {
//                $arrayPadri = $this->praLib->array_sort($arrayPadri, "DAGSET");
//                $lastPadre = end($arrayPadri);
//                $incDagset = substr($lastPadre['DAGSET'], -2) + 1;
//            } else {
//                $incDagset = 1;
//            }
//            $incDagset = str_repeat("0", 2 - strlen($incDagset)) . $incDagset;
//
// Carico l'array dati aggiuntivi
//
            $arrayData = array();
            $inc = count($this->altriDati) + 1;
            $arrayData['DAGSET'] = $chiavePasso . "_" . $incDagset;
//$arrayData['DAGKEY'] = $fileName;
//$arrayData['DAGKEY'] = $this->arrayInfo['pdfName'];
            $arrayData['DAGKEY'] = "<span style = \"color:orange;\">" . $this->arrayInfo['pdfName'] . "</span>";
            $arrayData['DAGVAL'] = "";
            $arrayData['ADD'] = '<span class="ui-icon ui-icon-plus">Aggiungi Campo in Gruppo ' . $arrayData[$inc]['DAGKEY'] . '</span>';
            $arrayData['level'] = 0;
            $arrayData['parent'] = null;
            $arrayData['isLeaf'] = 'false';
            $arrayData['expanded'] = 'true';
            $arrayData['loaded'] = 'true';
            $this->altriDati[$inc] = $arrayData;
            foreach ($datiAgg as $dato) {
                $arrayDataCampo = array();
                $key = count($this->altriDati) + 1;
                $arrayDataCampo['DAGSET'] = $chiavePasso . "_" . $incDagset . $dato["Campo"];
                $arrayDataCampo['DAGKEY'] = $dato["Campo"];
                $arrayDataCampo['ROWID'] = 0;
                $arrayDataCampo['DAGVAL'] = $dato["Valore"];
                $arrayDataCampo['level'] = 1;
                $arrayDataCampo['parent'] = $chiavePasso . "_" . $incDagset;
                $arrayDataCampo['isLeaf'] = 'true';
                $arrayDataCampo['expanded'] = 'true';
                $arrayDataCampo['loaded'] = 'true';
                $this->altriDati[$key] = $arrayDataCampo;
            }
        } else {
            foreach ($datiAgg as $dato) {
                $arrayDataCampo = array();
                $key = count($arrayTop) + 1;
                $arrayDataCampo['DAGSET'] = $dagset . $dato["Campo"];
                $arrayDataCampo['DAGKEY'] = $dato["Campo"];
                $arrayDataCampo['ROWID'] = 0;
                $arrayDataCampo['DAGVAL'] = $dato["Valore"];
                $arrayDataCampo['level'] = 1;
                $arrayDataCampo['parent'] = $dagset;
                $arrayDataCampo['isLeaf'] = 'true';
                $arrayDataCampo['expanded'] = 'true';
                $arrayDataCampo['loaded'] = 'true';
                $arrayTop[$key] = $arrayDataCampo;
            }
            $this->altriDati = array_merge($arrayTop, $arrayDown);
        }
        $this->caricaDatiAggiuntivi();
    }

    private function getPosizioneCampo($altriDati, $retid, $nuovo_campo) {
        $appoggio = array();
        $trovato = false;
        foreach ($altriDati as $valore) {
            if ($valore['DAGSET'] == $retid) {
                $trovato = true;
            } else if ($trovato == true && $valore['parent'] != $retid) {
                $appoggio[] = $nuovo_campo;
                $trovato = false;
            }
            $appoggio[] = $valore;
        }
        if ($trovato == true) {
            $appoggio[] = $nuovo_campo;
        }
        return $appoggio;
    }

    private function apriDuplica($last_propas) {
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");

//
//Definisco il nuovo record del passo
//
        $Propas_new_rec = $last_propas;
        $Propas_new_rec['PRONUM'] = $this->currGesnum;
        $Propas_new_rec['PROSEQ'] = "";
        $Propas_new_rec['PROVISIBILITA'] = "Aperto";
        $Propas_new_rec['PROSTATO'] = "";
        $Propas_new_rec['PROFIN'] = "";
        $Propas_new_rec['PROPART'] = 0;
        $Propas_new_rec['PROPST'] = 0;
        $Propas_new_rec['ROWID'] = 0;
        $this->DecodResponsabile($Propas_new_rec['PRORPA'], 'codice');
        $open_Info = 'Oggetto: ' . $Propas_new_rec['PROPAK'];
        $this->openRecord($this->PROT_DB, 'PROPAS', $open_Info);
        Out::valori($Propas_new_rec, $this->nameForm . '_PROPAS');

//
//Accendo e spengo div e bottoni
//
        Out::show($this->nameForm . '_Aggiungi');

//
//Prendo gli allegati se ci sono e dei testi base prendo i pdf e i p7m
//
        //$this->CaricaAllegati($last_propas['PROPAK']);
        $this->caricaAllegatiAnadoc();
        if ($this->allegatiAzione) {
            $percorsoTmp = itaLib::getPrivateUploadPath();
            if (!@is_dir($percorsoTmp)) {
                if (!itaLib::createPrivateUploadPath()) {
                    Out::msgStop("Archiviazione Mail.", "Creazione ambiente di lavoro temporaneo fallita.");
                    $this->returnToParent();
                }
            }

            foreach ($this->allegatiAzione as $key => $alle) {
                if (isset($alle['ROWID'])) {
                    $this->allegatiAzione[$key]['ROWID'] = 0;
                    $randName = md5(rand() * time()) . "." . pathinfo($alle['FILENAME'], PATHINFO_EXTENSION);
                    if (!@copy($alle['FILEPATH'], $percorsoTmp . "/" . $randName)) {
                        Out::msgStop("Duplica allegati", "Errore copia del file " . $alle['FILEPATH']);
                        return false;
                    }
                    $this->allegatiAzione[$key]['FILEPATH'] = $percorsoTmp . "/" . $randName;
                    $this->allegatiAzione[$key]['RANDOM'] = $randName;
                    $this->allegatiAzione[$key]['FILENAME'] = $randName;
                    if (strpos($alle['PROVENIENZA'], 'TESTOBASE') !== false) {
                        $testoPath = pathinfo($alle['FILEPATH'], PATHINFO_DIRNAME);
                        $testoName = pathinfo($alle['FILEPATH'], PATHINFO_FILENAME);
                        if (file_exists($testoPath . "/" . $testoName . ".pdf.p7m")) {
                            if (!@copy($testoPath . "/" . $testoName . ".pdf.p7m", $percorsoTmp . "/" . pathinfo($randName, PATHINFO_FILENAME) . ".pdf.p7m")) {
                                Out::msgStop("Duplica allegati", "Errore copia del file " . $testoName . ".pdf.p7m");
                                return false;
                            }
                        }

                        if (file_exists($testoPath . "/" . $testoName . ".pdf")) {
                            if (!@copy($testoPath . "/" . $testoName . ".pdf", $percorsoTmp . "/" . pathinfo($randName, PATHINFO_FILENAME) . ".pdf")) {
                                Out::msgStop("Duplica allegati", "Errore copia del file " . $testoName . ".pdf");
                                return false;
                            }
                        }
                    }
                }
            }
        }

//
// Prendo i dati aggiuntivi se ci sono
//
//        $this->CaricoCampiAggiuntivi($last_propas['PROPAK']);
//        if ($this->altriDati) {
//            foreach ($this->altriDati as $key1 => $dato) {
//                if (isset($dato['ROWID'])) {
//                    $this->altriDati[$key1]['ROWID'] = 0;
//                }
//            }
//        }
    }

    private function Dettaglio($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $propas_rec = $this->proLibPratica->GetPropas($rowid, $tipo);
        $anapro_Azione_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], 'codice', $propas_rec['PASPAR']);
        $this->currGesnum = $propas_rec['PRONUM'];
        $this->keyPropas = $propas_rec['PROPAK'];
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $this->Nascondi();
        if ($proges_rec['GESCLOSE'] == $this->keyPropas) {
            $this->passoChiusura = 1;
        } else {
            $this->passoChiusura = '';
        }
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        $open_Info = 'Oggetto: ' . $propas_rec['PROPAK'];
        $this->openRecord($this->PROT_DB, 'PROPAS', $open_Info);

        $Numero_procedimento = substr($proges_rec['GESNUM'], 14) . " / " . substr($proges_rec['GESNUM'], 10, 4);
        $Anareparc_rec = $this->proLib->getAnareparc($proges_rec['GESREP']);
        $subF = '';
        if ($anapro_Azione_rec['PROSUBKEY']) {
            $subF = "<br>    Sub: " . substr($anapro_Azione_rec['PROSUBKEY'], strpos($anapro_Azione_rec['PROSUBKEY'], '-') + 1);
        }
        $titolo = "    Codice Fascicolo: " . $proges_rec['GESKEY'] . "
            $subF 
            <br>    Pratica: " . $Numero_procedimento . "
            <br>    Repertorio: " . $Anareparc_rec['DESCRIZIONE'];
        Out::html($this->nameForm . '_Codice_Fascicolo', $titolo);
        Out::valori($propas_rec, $this->nameForm . '_PROPAS');
        Out::codice('$(protSelector("#' . $this->nameForm . '_PROPAS[PASPROUFF]' . '")+" option").remove();');
        $anauff_uof = $this->proLib->GetAnauff($propas_rec['PASPROUFF']);
        Out::select($this->nameForm . '_PROPAS[PASPROUFF]', 1, $propas_rec['PASPROUFF'], 1, substr($anauff_uof['UFFDES'], 0, 30));
        Out::attributo($this->nameForm . '_PROPAS[PASPROUFF]', 'disabled', '0', '');
        Out::show($this->nameForm . '_Aggiorna');
        $this->DecodStatoPasso($propas_rec['PROSTATO']);

        if (!$this->passoChiusura) {
            $this->CaricoCampiAggiuntivi($this->keyPropas);
            $this->caricaAllegatiAnadoc();
            Out::show($this->nameForm . '_divBasso');
            Out::show($this->nameForm . '_TestoBase');
            if ($propas_rec['PROUFFRES']) {
                $anauff_rec = $this->proLib->GetAnauff($propas_rec['PROUFFRES'], 'codice');
                Out::valore($this->nameForm . "_UFFRESP", $anauff_rec['UFFDES']);
            }
            Out::show($this->nameForm . '_Chiudi');
            if ($propas_rec['PROFIN']) {
                Out::hide($this->nameForm . '_Chiudi');
                Out::show($this->nameForm . '_Apri');
            }
            $this->DecodResponsabile($propas_rec['PRORPA'], 'codice', $propas_rec['PROUFFRES']);
            if ($propas_rec['PROVPA'] != '') {
                $this->DecodVaialpasso($propas_rec['PROVPA']);
            }
            if ($propas_rec['PROVISIBILITA'] == "") {
                Out::valore($this->nameForm . "_PROPAS[PROVISIBILITA]", "Aperto");
            }
            Out::show($this->nameForm . '_Protocolla');
            if ($propas_rec['PRONODE']) {
                Out::show($this->nameForm . '_Sottofascicolo');
            }


            if ($propas_rec['PASPAR'] == 'F') {
                Out::block($this->nameForm . '_PROPAS[PRORPA]_field');
                Out::block($this->nameForm . '_RESPONSABILE_field');
                Out::block($this->nameForm . '_UFFRESP_field');
                Out::attributo($this->nameForm . '_PROPAS[PRORPA]', "readonly", '0');
                Out::hide($this->nameForm . '_PROPAS[PRORPA]_butt');
                Out::hide($this->nameForm . '_UFFRESP_butt');

                $this->nascondiCampiSuNodo();

                $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-open-folder-24x24" Title="Fascicolo">&nbsp;</div><div style="display:inline-block;"> &nbsp; Fascicolo</div>';
            } else {
                Out::unBlock($this->nameForm . '_PROPAS[PRORPA]_field');
                Out::unBlock($this->nameForm . '_RESPONSABILE_field');
                Out::unBlock($this->nameForm . '_UFFRESP_field');
                Out::attributo($this->nameForm . '_PROPAS[PRORPA]', "readonly", '1');
                Out::show($this->nameForm . '_PROPAS[PRORPA]_butt');
                Out::show($this->nameForm . '_UFFRESP_butt');

                if ($propas_rec['PRONODE']) {
                    $this->nascondiCampiSuNodo();
                    $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-sub-folder-24x24" Title="Sottofascicolo">&nbsp;</div><div style="display:inline-block;"> &nbsp; Sottofascicolo</div>';
                    Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
                } else {
                    $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-edit-24x24" Title="Azione">&nbsp;</div><div style="display:inline-block;"> &nbsp; Azione</div>';
                    Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
                }
            }
        } else {
            Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
            Out::delClass($this->nameForm . '_PROPAS[PRORPA]', "required");
            Out::delClass($this->nameForm . '_UFFRESP', "required");
            Out::addClass($this->nameForm . '_PROPAS[PROSTATO]', "required");
            Out::addClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
            Out::attributo($this->nameForm . '_PROPAS[PROFIN]', "readonly", '0');
            Out::codice("$(protSelector('#{$this->nameForm}_PROPAS[PROFIN]_field')).find('.ui-datepicker-trigger').eq(0).hide();");

            Out::hide($this->nameForm . '_PROPAS[PROVISIBILITA]_field');
            Out::hide($this->nameForm . '_PROPAS[PRODTP]_field');
            Out::hide($this->nameForm . '_PROPAS[PRORPA]_field');
            Out::hide($this->nameForm . '_UFFRESP_field');
            Out::hide($this->nameForm . '_RESPONSABILE_field');

            $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-divieto-24x24" Title="Fascicolo Chiuso">&nbsp;</div><div style="display:inline-block;"> &nbsp; Fascicolo Chiuso</div>';
        }
        if ($propas_rec['PROUTEADD'] == "@ADMIN@" && $propas_rec['PROUTEEDIT'] == "@ADMIN@") {
            Out::show($this->nameForm . "_utenteEdit");
            Out::valore($this->nameForm . "_utenteEdit", "Passo proveniente dalla richiesta on-line in data " . date("d/m/Y", strtotime($propas_rec['PRODATEEDIT'])) . " {$propas_rec['PROORAEDIT']} NON MODIFICABILE");
        } else {
            if ($propas_rec['PROUTEEDIT'] != "") {
                Out::show($this->nameForm . "_utenteEdit");
                Out::valore($this->nameForm . "_utenteEdit", "Ultima modifica al passo effettuata dall'utente " . $propas_rec['PROUTEEDIT'] . " in data " . date("d/m/Y", strtotime($propas_rec['PRODATEEDIT'])) . " " . $propas_rec['PROORAEDIT']);
            }
        }

        Out::html($this->nameForm . '_divIcona', $infoRis);
        Out::setFocus('', $this->nameForm . '_PROPAS[PROANN]');
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Protetto", "1", "Protetto");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Privato", "0", "Privato");
        Out::select($this->nameForm . '_PROPAS[PROVISIBILITA]', 1, "Aperto", "0", "Pubblico");
    }

    private function ApriScanner() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
//$modelTwain = 'utiTwain';
        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '1';
        $modelTwain();
    }

    private function SalvaScanner() {
        $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
        $randName = $_POST['retFile'];
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
        $timeStamp = date("Ymd_His");
        $newAllegato = array();
        $newAllegato[0] = array(
            'ROWID' => 0,
            'NUMEROPROTOCOLLO' => $propas_rec['PASPRO'],
            'TIPOPROTOCOLLO' => $propas_rec['PASPAR'],
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => 'File Originale: Da scanner',
            'DOCNAME' => "Scansione_" . $timeStamp . "." . pathinfo($randName, PATHINFO_EXTENSION),
            'DOCFDT' => date('Ymd'),
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => 0,
            'DAFIRMARE' => 0
        );

        $this->salvaAllegati($newAllegato, $propas_rec['PASPRO'], $propas_rec['PASPAR']);
        $this->caricaAllegatiAnadoc();
    }

    private function apriInserimento($procedimento, $tipoIns = 'add', $nodeRowid = 0) {

//        App::log('$nodeRowid');
//        App::log($nodeRowid);

        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);


        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridCampiAggiuntivi);
        TableView::disableEvents($this->gridCampiAggiuntivi);

        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::valore($this->nameForm . "_PROPAS[PRONUM]", $procedimento);
        Out::valore($this->nameForm . "_PROPAS[PRONODE]", 0);
        if ($tipoIns == 'addNode') {
            $this->nascondiCampiSuNodo();
            Out::valore($this->nameForm . "_PROPAS[PRONODE]", 1);
            $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-sub-folder-24x24" Title="Sottofascicolo">&nbsp;</div><div style="display:inline-block;">Sottofascicolo</div>';
        } else {
            $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-edit-24x24" Title="Azione">&nbsp;</div><div style="display:inline-block;">Azione</div>';
        }
        Out::html($this->nameForm . '_divIcona', $infoRis);
        if ($nodeRowid) {
            $orgnode_rec = $this->proLib->GetOrgNode($nodeRowid, 'rowid');
            Out::valore($this->nameForm . "_ORGNODEROWID", $orgnode_rec['ROWID']);
        }


        $titolo = "Codice Fascicolo: " . $proges_rec['GESKEY'] . "<br>Pratica: " . substr($procedimento, 14, 6) . "/" . substr($procedimento, 10, 4);
        Out::html($this->nameForm . '_Codice_Fascicolo', $titolo);

//        Out::valore($this->nameForm . '_Codice_Fascicolo', $proges_rec['GESKEY']);

        $this->caricaUof();
        Out::valore($this->nameForm . '_PROPAS[PASPROUTE]', App::$utente->getKey('nomeUtente'));
        Out::setFocus('', $this->nameForm . '_PROPAS[PRODPA]');
    }

    private function chiudiFascicolo($procedimento, $prostato, $nodeRowid) {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $this->passoChiusura = '1';
        TableView::clearGrid($this->gridAllegati);
        TableView::disableEvents($this->gridAllegati);
        TableView::clearGrid($this->gridCampiAggiuntivi);
        TableView::disableEvents($this->gridCampiAggiuntivi);
        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::tabRemove($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::valore($this->nameForm . "_PROPAS[PRONUM]", $procedimento);
        Out::valore($this->nameForm . "_PROPAS[PRONODE]", 0);

        $anastp_rec = $this->praLib->GetAnastp($prostato);
        Out::valore($this->nameForm . '_PROPAS[PROSTATO]', $anastp_rec['ROWID']);
        Out::valore($this->nameForm . '_Stato1', $anastp_rec['STPDES']);

        Out::valore($this->nameForm . '_PROPAS[PROINI]', date('Ymd'));
        Out::valore($this->nameForm . '_PROPAS[PROFIN]', date('Ymd'));
        Out::attributo($this->nameForm . '_PROPAS[PROFIN]', "readonly", '0');
        Out::codice("$(protSelector('#{$this->nameForm}_PROPAS[PROFIN]_field')).find('.ui-datepicker-trigger').eq(0).hide();");

        Out::hide($this->nameForm . '_PROPAS[PROVISIBILITA]_field');
        Out::hide($this->nameForm . '_PROPAS[PRODTP]_field');
        Out::hide($this->nameForm . '_PROPAS[PRORPA]_field');
        Out::hide($this->nameForm . '_UFFRESP_field');
        Out::hide($this->nameForm . '_RESPONSABILE_field');

        $infoRis = '<div style="display:inline-block;" class="ita-icon ita-icon-divieto-24x24" Title="Chiudi Fascicolo">&nbsp;</div><div style="display:inline-block;"> &nbsp; Chiudi Fascicolo</div>';
        Out::html($this->nameForm . '_divIcona', $infoRis);
        if ($nodeRowid) {
            $orgnode_rec = $this->proLib->GetOrgNode($nodeRowid, 'rowid');
            Out::valore($this->nameForm . "_ORGNODEROWID", $orgnode_rec['ROWID']);
        }
        $titolo = "CHIUDI FASCICOLO<br>Codice Fascicolo: " . $proges_rec['GESKEY'] . "<br>Pratica: " . substr($procedimento, 14, 6) . "/" . substr($procedimento, 10, 4);
        Out::html($this->nameForm . '_Codice_Fascicolo', $titolo);
        $this->caricaUof();
        Out::valore($this->nameForm . '_PROPAS[PASPROUTE]', App::$utente->getKey('nomeUtente'));

        Out::delClass($this->nameForm . '_PROPAS[PRORPA]', "required");
        Out::delClass($this->nameForm . '_UFFRESP', "required");
        Out::addClass($this->nameForm . '_PROPAS[PROSTATO]', "required");

        Out::setFocus('', $this->nameForm . '_PROPAS[PRODPA]');
    }

    private function CaricoCampiAggiuntivi($keyPasso) {
        $this->altriDati = array();
        $sql = "SELECT DISTINCT DAGSET FROM PRODAG WHERE DAGPAK = '" . $keyPasso . "' ORDER BY DAGSET";
        $dataSet_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $arrayData = array();
        foreach ($dataSet_tab as $key => $dataSet_rec) {
            $sql = "SELECT * FROM PRODAG WHERE DAGPAK = '" . $keyPasso . "' AND DAGSET = '" . $dataSet_rec['DAGSET'] . "' ORDER BY DAGSEQ";
            $dataDetail_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
            if ($dataDetail_tab) {
                $prodst_rec = $this->proLibPratica->GetProdst($dataSet_rec['DAGSET']);
                $inc = count($arrayData) + 1;
                $arrayData[$inc]['DAGSET'] = $dataSet_rec['DAGSET'];
                if ($prodst_rec['DSTDES']) {
                    $arrayData[$inc]['DAGKEY'] = $prodst_rec['DSTDES'];
                } else {
                    $arrayData[$inc]['DAGKEY'] = "Data Set " . substr($dataSet_rec['DAGSET'], 33, 2);
                }

                $arrayData[$inc]['DAGVAL'] = "";
                $arrayData[$inc]['ADD'] = '<span class="ui-icon ui-icon-plus">Aggiungi Campo in Gruppo ' . $arrayData[$inc]['DAGKEY'] . '</span>';
                $arrayData[$inc]['level'] = 0;
                $arrayData[$inc]['parent'] = null;
                $arrayData[$inc]['isLeaf'] = 'false';
                $arrayData[$inc]['expanded'] = 'true';
                $arrayData[$inc]['loaded'] = 'true';
                foreach ($dataDetail_tab as $key => $dataDetail_rec) {
                    $inc = count($arrayData) + 1;
//$arrayData[$inc]['DAGSET'] = $dataDetail_rec['DAGSET'] . $dataDetail_rec['DAGKEY'];
                    $arrayData[$inc]['DAGSET'] = str_replace(" ", "_", $dataDetail_rec['DAGSET'] . $dataDetail_rec['DAGKEY']);
                    $arrayData[$inc]['DAGKEY'] = $dataDetail_rec['DAGKEY'];
                    $arrayData[$inc]['ROWID'] = $dataDetail_rec['ROWID'];
                    $arrayData[$inc]['DAGDES'] = $dataDetail_rec['DAGDES'];
                    $arrayData[$inc]['DAGVAL'] = $dataDetail_rec['DAGVAL'];
                    $arrayData[$inc]['level'] = 1;
                    $arrayData[$inc]['parent'] = $dataDetail_rec['DAGSET'];
//$arrayData[$inc]['parent'] = $inc;
                    $arrayData[$inc]['isLeaf'] = 'true';
                    $arrayData[$inc]['expanded'] = 'true';
                    $arrayData[$inc]['loaded'] = 'true';
                }
            }
        }
        $this->altriDati = $arrayData;

// Pulisco l'array dei dati aggiuntivi dagli spazi perche altrimnenti non li visualizza sulla tabella
// lasciando aperto il discorso anche per altri caratteri
//        foreach ($this->altriDati as $keydag => $campo) {
//            $cerca = array(" ");
//            $sostituisci = array("_");
//            $this->altriDati[$keydag]['DAGSET'] = str_replace($cerca, $sostituisci, $campo['DAGSET']);
//        }

        $this->caricaDatiAggiuntivi();
    }

    private function RegistraAltriDati($keyPasso, $propas_rec) {
        foreach ($this->altriDati as $dato) {
            if ($dato['isLeaf'] == "false") {
                $dataset = $keyPasso . substr($dato['DAGSET'], -3);
                $Prodst_rec = $this->proLibPratica->GetProdst($dataset);
                if (!$Prodst_rec) {
                    $Prodst_rec = array();
                    $Prodst_rec["DSTSET"] = $dataset;
//$Prodst_rec["DSTDES"] = $dato['DAGKEY'];
                    $Prodst_rec["DSTDES"] = strip_tags($dato['DAGKEY']); // Tolgo i tag html col colore arancione
                    $insert_Info = "Oggetto : Inserisco data set $dataset del file " . $dato['DAGKEY'];
                    if (!$this->insertRecord($this->PROT_DB, 'PRODST', $Prodst_rec, $insert_Info)) {
                        Out::msgStop("Inserimento data set", "Inserimento data set $dataset fallito");
                        return false;
                    }
                }
            }
        }
        $arrayDatiClean = $this->proLibPratica->cleanArrayTree($this->altriDati);
        foreach ($arrayDatiClean as $keyArray => $DatoClean) {
            try {
                if ($DatoClean['ROWID'] == 0) {
                    $prodag_rec = array();
                    $prodag_rec['DAGSEQ'] = $keyArray;
//$prodag_rec['DAGSET'] = substr($DatoClean['DAGSET'], 0, 25);
                    $prodag_rec['DAGSET'] = $keyPasso . substr($DatoClean['parent'], -3);
                    $prodag_rec['DAGNUM'] = $propas_rec['PRONUM'];
                    $prodag_rec['DAGCOD'] = $propas_rec['PROPRO'];
                    $prodag_rec['DAGPAK'] = $keyPasso;
                    $prodag_rec['DAGKEY'] = $DatoClean['DAGKEY'];
                    $prodag_rec['DAGDES'] = $DatoClean['DAGDES'];
                    $prodag_rec['DAGVAL'] = $DatoClean['DAGVAL'];
                    $prodag_rec['DAGTIP'] = $DatoClean['DAGTIP'];
                    $insert_Info = 'Oggetto : Inserimento dato aggiuntivo' . $prodag_rec['DAGKEY'] . " del passo" . $prodag_rec['DAGPAK'];
                    if (!$this->insertRecord($this->PROT_DB, 'PRODAG', $prodag_rec, $insert_Info)) {
                        return false;
                    }
                } else {
                    $prodag_rec = $this->proLibPratica->GetProdag($DatoClean['ROWID'], 'ROWID');
                    $prodag_rec['DAGSET'] = $keyPasso . substr($DatoClean['parent'], -3);
                    $prodag_rec['DAGVAL'] = $DatoClean['DAGVAL'];
                    $update_Info = 'Oggetto : Aggiornamento dato aggiuntivo' . $prodag_rec['DAGKEY'] . " del passo" . $prodag_rec['DAGPAK'];
                    if (!$this->updateRecord($this->PROT_DB, 'PRODAG', $prodag_rec, $update_Info)) {
                        return false;
                    }
                }
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
                return false;
            }
        }
        return true;
    }

    private function SincronizzaAzione($propas_rec) {
        $NodeAnaogg_rec = $this->proLib->GetAnaogg($propas_rec['PASPRO'], $propas_rec['PASPAR']);
        if ($NodeAnaogg_rec) {
            $NodeAnaogg_rec['OGGOGG'] = $propas_rec['PRODPA'];
            try {
                ItaDB::DBUpdate($this->PROT_DB, 'ANAOGG', 'ROWID', $NodeAnaogg_rec);
            } catch (Exception $exc) {
                Out::msgStop("Errore update", "Errore aggiornamento descrizione sottofascicolo " . $exc->getMessage());
            }
        }


        if ($propas_rec['PASPAR'] != 'F') {
            $anapro_azione = $this->proLib->GetAnapro($propas_rec['PASPRO'], 'codice', $propas_rec['PASPAR']);
            $this->proLibFascicolo->registraResponsabile(array('RES' => $propas_rec['PRORPA'], 'UFF' => $propas_rec['PROUFFRES']), $propas_rec['PASPRO'], $propas_rec['PASPAR']);
            $iter = proIter::getInstance($this->proLib, $anapro_azione);
            $iter->sincIterProtocollo();
        }
        return true;
    }

    private function AllegaFile() {
        $this->currAllegato = null;
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            if (strtoupper(pathinfo($uplFile, PATHINFO_EXTENSION)) == "P7M") {
                $this->docLib->AnalizzaP7m($uplFile);
            }

            $retVerify = $this->docLib->verificaPDFA($uplFile);

            if ($retVerify['status'] !== 0) {
                if ($retVerify['status'] == -5) {
                    $Filde2 = $this->praLib->getFlagPDFA();
//                    $verifyPDFA = substr($Filde2, 0, 1);
                    $convertPDFA = substr($Filde2, 1, 1);
//                    $PDFLevel = substr($Filde2, 2, 1);

                    if (!$convertPDFA) {
                        $this->currAllegato = array(
                            'uplFile' => $uplFile,
                            'randName' => $randName,
                            'destFile' => $destFile,
                            'origFile' => $origFile
                        );
                        Out::msgQuestion("Allegato non conforme PDF/A ", $retVerify['message'], array(
                            'F8-Rifiuta Allegato' => array('id' => $this->nameForm . '_AnnullaPDFA', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Accetta Allegato' => array('id' => $this->nameForm . '_ConfermaPDFA', 'model' => $this->nameForm, 'shortCut' => "f5"),
                            'F1-Converti Allegato' => array('id' => $this->nameForm . '_ConvertiPDFA', 'model' => $this->nameForm, 'shortCut' => "f1")
                                )
                        );
                    } else {
                        $retConvert = $this->docLib->convertiPDFA($uplFile, $destFile, true);
                        if ($retConvert['status'] == 0) {
                            $this->aggiungiAllegato($randName, $destFile, $origFile, array("PROVENIENZA" => "ESTERNO"));
                            Out::msgInfo('Allega PDF', "Allegato PDF Convertito a PDF/A verifica il PDF." . $this->currAllegato['origFile']);
//                            Out::openDocument(utiDownload::getUrl($origFile, $destFile));
                        } else {
                            Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                        }
                    }
                } else {
                    Out::msgStop("Verifica PDF/A Impossibile", $retVerify['message']);
                    unlink($uplFile);
                }
                return;
            } else {
//                if (!@rename($uplFile, $destFile)) {
//                    Out::msgStop("Upload File:", "Errore in salvataggio del file!");
//                } else {
                // 
                //  DA CAPIRE!
                //
                $retConvert = $this->docLib->convertiPDFA($uplFile, $destFile, true);
//                App::log('$uplFile');
//                App::log($uplFile);
//                App::log($destFile);
//                App::log('$retConvert');
//                App::log($retConvert);
                if ($retConvert['status'] == 0) {
                    $this->aggiungiAllegato($randName, $destFile, $origFile, array("PROVENIENZA" => "ESTERNO"));
                } else {
                    Out::msgStop("Conversione PDF/A Impossibile", $retConvert['message']);
                }
//                }
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
        }
    }

    private function aggiungiAllegato($randName, $destFile, $origFile, $param = array()) {
        $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
        $newAllegato = array();
        $newAllegato[0] = array(
            'ROWID' => 0,
            'NUMEROPROTOCOLLO' => $propas_rec['PASPRO'],
            'TIPOPROTOCOLLO' => $propas_rec['PASPAR'],
            'FILEPATH' => $destFile,
            'FILENAME' => $randName,
            'FILEINFO' => 'File Originale: ' . $origFile,
            'DOCNAME' => $origFile,
            'DOCFDT' => date('Ymd'),
            'PROVENIENZA' => $param['PROVENIENZA'],
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => 0,
            'DAFIRMARE' => 0
        );
        $this->salvaAllegati($newAllegato, $propas_rec['PASPRO'], $propas_rec['PASPAR']);
        $this->caricaAllegatiAnadoc();
        Out::setFocus('', $this->nameForm . '_wrapper');
    }

    private function caricaAllegatiAnadoc($where = array()) {
        $propas_rec = $this->proLibPratica->GetPropas($this->keyPropas);
        $this->allegatiAzione = array();
        $this->caricaDocumentiAnadoc($propas_rec['PASPRO'], $propas_rec['PASPAR'], $where, true);
        $sql = "
            SELECT
                ORGCONN.PRONUM,
                ORGCONN.PROPAR,
                ANAPRO.PRODAR,
                ANAPRO.PROORA,
                ANAPRO.PRORISERVA,
                ANAPRO.PROTSO
            FROM
                ORGCONN
            LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
            WHERE
                ORGCONN.CONNDATAANN = '' AND 
                ORGCONN.PRONUMPARENT='{$propas_rec['PASPRO']}' AND ORGCONN.PROPARPARENT='{$propas_rec['PASPAR']}' AND"
                . " (ANAPRO.PROPAR = 'P' OR ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='C')"; // Quindi gli annullati sarebbero da escludere?
        if ($where['PROTOCOLLI']) {
            $sql .= " AND (1=1 {$where['PROTOCOLLI']})";
        }
        $sql .= " ORDER BY ORGCONN.CONNSEQ";
        $orgconn_tab = $this->proLib->getGenericTab($sql);
        if ($orgconn_tab) {
            foreach ($orgconn_tab as $orgconn_rec) {
                $this->caricaDocumentiAnadoc($orgconn_rec['PRONUM'], $orgconn_rec['PROPAR'], $where);
            }
        }
        $this->ContaSizeAllegati($this->allegatiAzione);
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
    }

    private function caricaDocumentiAnadoc($pronum, $propar, $where, $root = false) {
        $sql = "SELECT"
                . " ANAPRO.PRONUM  AS PRONUM,"
                . " ANAPRO.PROPAR  AS PROPAR,"
                . " ANADOC.DOCNAME AS DOCNAME,"
                . " ANADOC.DOCKEY,"
                . " ANADOC.DOCFIL,"
                . " ANADOC.DOCCLA,"
                . " ANADOC.DOCNOT,"
                . " ANADOC.DOCEVI,"
                . " ANADOC.DOCLOCK,"
                . " ANADOC.ROWID AS ROWID"
                . " FROM"
                . " ANAPRO"
                . " LEFT OUTER JOIN ANADOC ANADOC ON ANAPRO.PRONUM=ANADOC.DOCNUM AND ANAPRO.PROPAR=ANADOC.DOCPAR"
                . " WHERE "
                . "ANADOC.ROWID IS NOT NULL AND ANAPRO.PRONUM = '{$pronum}' AND ANAPRO.PROPAR = '{$propar}'";
        if ($where['DOCUMENTI']) {
            $sql .= " AND (1=1 {$where['DOCUMENTI']})";
        }
        if ($where['PROTOCOLLI']) {
            $sql .= " AND (1=1 {$where['PROTOCOLLI']} AND (ANAPRO.PROPAR<>'F' AND ANAPRO.PROPAR<>'N' AND ANAPRO.PROPAR<>'T'))";
        }
        $sql .= " ORDER BY ANADOC.ROWID";
        $documenti_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);

        if (!$documenti_tab) {
            if ($root) {
                return;
            }
            $orgconn_rec = $this->proLib->GetOrgConn($pronum, 'codice', $propar);
            $anaogg_conn_rec = $this->proLib->GetAnaogg($pronum, $propar);
            $inc = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];
            $this->allegatiAzione[$inc]['ORGNODEKEY'] = "PRO-" . $orgconn_rec['PRONUM'] . $orgconn_rec['PROPAR'];
            switch ($orgconn_rec['PROPAR']) {
                case "F":
                    $icon = "ita-icon-open-folder-32x32";
                    break;
                case "N":
                    $icon = "ita-icon-sub-folder-32x32";
                    break;
                case "T":
                    $icon = "ita-icon-edit-32x32";
                    break;
                case "A":
                case "P":
                case "C":
                    $tooltip = "Protocollo:<br>" . substr($orgconn_rec['PRONUM'], 4) . " / " . substr($orgconn_rec['PRONUM'], 0, 4) . "  -  " . $orgconn_rec['PROPAR'];
                    $tooltip .="<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                    $this->allegatiAzione[$inc]['PROTOCOLLO'] = 1;
                    $this->allegatiAzione[$inc]['NUMPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($orgconn_rec['PRONUM'], 4) . '</span></div>';
                    $this->allegatiAzione[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($orgconn_rec['PRONUM'], 0, 4) . '</span></div>';
                    $this->allegatiAzione[$inc]['PRONUM'] = $orgconn_rec['PRONUM'];
                    $this->allegatiAzione[$inc]['PROPAR'] = $orgconn_rec['PROPAR'];
                    $icon = "ita-icon-register-document-32x32";
                    break;
                default:
                    break;
            }

            $this->allegatiAzione[$inc]['ORGNODEICO'] = '<div class="ita-html"><span style="height:24px;background-size:75%;margin:2px;" title="' . htmlspecialchars($tooltip) . '" class="ita-tooltip ita-icon ' . $icon . '"></span></div>';
            $this->allegatiAzione[$inc]['INFO'] = $anaogg_conn_rec['OGGOGG'];
            return true;
        }
        foreach ($documenti_tab as $documenti_rec) {
            $icon = utiIcons::getExtensionIconClass($documenti_rec['DOCNAME'], 32);
            $inc = "DOC-" . $documenti_rec['DOCKEY'];
            $this->allegatiAzione[$inc]['ROWID'] = $documenti_rec['ROWID'];
            $this->allegatiAzione[$inc]['ORGNODEKEY'] = "DOC-" . $documenti_rec['DOCKEY'];
            $this->allegatiAzione[$inc]['ORGNODEICO'] = '<span style="height:24px;background-size:75%;margin:2px;" class="' . $icon . '">Documento</span>';
            $ext = pathinfo($documenti_rec['DOCFIL'], PATHINFO_EXTENSION);
            if ($documenti_rec['DOCNAME']) {
                $this->allegatiAzione[$inc]['NAME'] = $documenti_rec['DOCNAME'];
                $this->allegatiAzione[$inc]['FILEORIG'] = $documenti_rec['DOCNAME'];
            } else {
                $this->allegatiAzione[$inc]['NAME'] = $documenti_rec['DOCFIL'];
                $this->allegatiAzione[$inc]['FILEORIG'] = $documenti_rec['DOCFIL'];
            }
            $this->allegatiAzione[$inc]['INFO'] = $documenti_rec['DOCNOT'];
            $this->allegatiAzione[$inc]['PROVENIENZA'] = $documenti_rec['DOCCLA'];

            $this->allegatiAzione[$inc]['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allpath = $this->proLib->SetDirectory($documenti_rec['PRONUM'], $documenti_rec['PROPAR']);
            switch ($documenti_rec['PROPAR']) {
                case "A":
                case "P":
                case "C":
                    $anaogg_conn_rec = $this->proLib->GetAnaogg($documenti_rec['PRONUM'], $documenti_rec['PROPAR']);
                    $tooltip = "Protocollo:<br>" . substr($documenti_rec['PRONUM'], 4) . " / " . substr($documenti_rec['PRONUM'], 0, 4) . "  -  " . $documenti_rec['PROPAR'];
                    $tooltip .="<br><p style=\"color:lightblue;\">" . $anaogg_conn_rec['OGGOGG'] . "</p>";
                    $this->allegatiAzione[$inc]['PROTOCOLLO'] = 1;
                    $this->allegatiAzione[$inc]['NUMPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($documenti_rec['PRONUM'], 4) . '</span></div>';
                    $this->allegatiAzione[$inc]['ANNOPROT'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlspecialchars($tooltip) . '">' . substr($documenti_rec['PRONUM'], 0, 4) . '</span></div>';
                    $this->allegatiAzione[$inc]['PRONUM'] = $documenti_rec['PRONUM'];
                    $this->allegatiAzione[$inc]['PROPAR'] = $documenti_rec['PROPAR'];
                    $this->allegatiAzione[$inc]['LOCK'] = "";
                    $this->allegatiAzione[$inc]['PREVIEW'] = "";
                    break;
                default:
                    if ($documenti_rec['DOCLOCK'] == 1) {
                        $this->allegatiAzione[$inc]['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Sblocca Documento</span>";
                    } else {
                        $this->allegatiAzione[$inc]['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Documento</span>";
                    }
                    $preview = $this->GetImgPreview($ext, $allpath, $documenti_rec);
                    $this->allegatiAzione[$inc]['PREVIEW'] = $preview;
                    break;
            }
            if ($documenti_rec['DOCEVI']) {
                if ($documenti_rec['DOCNAME']) {
                    $this->allegatiAzione[$inc]['NAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $documenti_rec['DOCNAME'] . "</p>";
                    $this->allegatiAzione[$inc]['FILEORIG'] = $documenti_rec['DOCNAME'];
                } else {
                    $documenti_rec['NAME'] = "<p style = 'color:red;font-weight:bold;font-size:1.2em;'>" . $documenti_rec['DOCFIL'] . "</p>";
                    $documenti_rec['FILEORIG'] = $documenti_rec['DOCFIL'];
                }
            }
            $this->allegatiAzione[$inc]['FILENAME'] = $documenti_rec['DOCFIL'];
            $this->allegatiAzione[$inc]['FILEINFO'] = $documenti_rec['DOCNOT'];
            $this->allegatiAzione[$inc]['FILEPATH'] = $allpath . "/" . $documenti_rec['DOCFIL'];
            $this->allegatiAzione[$inc]['DOCEVI'] = $documenti_rec['DOCEVI'];
            $this->allegatiAzione[$inc]['DOCLOCK'] = $documenti_rec['DOCLOCK'];
//            $preview = $this->GetImgPreview($ext, $allpath, $documenti_rec);
//            $this->allegatiAzione[$inc]['PREVIEW'] = $preview;
        }
        return true;
    }

    public function GetImgPreview($ext, $path, $doc) {
        $title = "Clicca per le funzioni disponibili";
        if (strtolower($ext) == "pdf") {
            $preview = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        } else if (strtolower($ext) == "xhtml") {
            if (file_exists($path . "/" . pathinfo($doc['DOCFIL'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                $preview = "<span class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
            } else if (file_exists($path . "/" . pathinfo($doc['DOCFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                $preview = "<span class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
            } else {
                $preview = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
            }
        } else if (strtolower($ext) == "p7m") {
            $preview = "<span class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
        } else {
            $preview = "<span class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        }

        return $preview;
    }

    private function DecodAnamed($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        Out::valore($this->nameForm . '_PROPAS[PROCDR]', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_MEDNOM', $anamed_rec['MEDNOM']);
        return $anamed_rec;
    }

    private function DecodStatoPasso($Codice) {
        $anastp_recAperto = $this->praLib->GetAnastp($Codice);
        Out::valore($this->nameForm . "_Stato1", $anastp_recAperto['STPDES']);
    }

    private function DecodResponsabile($codice, $tipoRic = 'codice', $uffcod = '') {
        if (trim($codice) != "") {
            if (is_numeric($codice)) {
                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipoRic, 'no');
            if (!$anamed_rec) {
                Out::valore($this->nameForm . '_PROPAS[PRORPA]', '');
                Out::valore($this->nameForm . '_RESPONSABILE', '');
                Out::setFocus('', $this->nameForm . "_RESPONSABILE");
                return;
            } else {
                Out::valore($this->nameForm . '_PROPAS[PRORPA]', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_RESPONSABILE', $anamed_rec['MEDNOM']);

                if ($uffcod) {
                    $anauff_rec = $this->proLib->GetAnauff($uffcod);
                    Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    return;
                } else {
                    $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                    if (count($uffdes_tab) == 1) {
                        $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                        Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', $anauff_rec['UFFCOD']);
                        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    } else {
                        if ($_POST[$this->nameForm . '_PROPAS']['PROUFFRES'] == '' || $_POST[$this->nameForm . '_UFFRESP'] == '') {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            Out::setFocus('', "utiRicDiag_gridRis");
                            return;
                        }
                    }
                }
            }
        } else {
            Out::valore($this->nameForm . '_PROPAS[PRORPA]', '');
            Out::valore($this->nameForm . '_RESPONSABILE', '');
        }
        Out::setFocus('', $this->nameForm . "_RESPONSABILE");

//@TODO DA RIVEDERE
//        if ($propas_rec['PROSET'] == "")
//            $propas_rec['PROSET'] = "";
//        $anauniSett_rec = $this->praLib->GetAnauni($propas_rec['PROSET']);
//        Out::valore($this->nameForm . '_SETTORE', $anauniSett_rec['UNIDES']);
//        if ($propas_rec['PROSER'] == "")
//            $propas_rec['PROSET'] = "";
//        $anauniServ_rec = $this->praLib->GetAnauniServ($propas_rec['PROSET'], $propas_rec['PROSER']);
//        Out::valore($this->nameForm . '_SERVIZIO', $anauniServ_rec['UNIDES']);
//        if ($propas_rec['PROUOP'] == "")
//            $propas_rec['PROSET'] = $propas_rec['PROSER'] = "";
//        $anauniOpe_rec = $this->praLib->GetAnauniOpe($propas_rec['PROSET'], $propas_rec['PROSER'], $propas_rec['PROUOP']);
//        Out::valore($this->nameForm . '_UNITA', $anauniOpe_rec['UNIDES']);
    }

    private function DecodVaialpasso($codice, $tipo = 'propak') {
        $propas_rec = $this->proLibPratica->GetPropas($codice, $tipo);
        Out::valore($this->nameForm . '_Destinazione', $propas_rec['PROSEQ']);
        Out::valore($this->nameForm . '_DescrizioneVai', $propas_rec['PRODPA']);
        Out::valore($this->nameForm . '_PROPAS[PROVPA]', $propas_rec['PROPAK']);
    }

    private function ApriPasso() {
        if ($_POST[$this->nameForm . '_PROPAS']['PROFIN']) {
            $_POST[$this->nameForm . '_PROPAS']['PROFIN'] = "";
            Out::valore($this->nameForm . '_PROPAS[PROFIN]', "");
//        } else {
//            $_POST[$this->nameForm . '_PROPAS']['PROINI'] = $this->workDate;
//            Out::valore($this->nameForm . '_PROPAS[PROINI]', $this->workDate);
        }
        if ($this->AggiornaRecord()) {
//            $this->chiudiForm = true;
//            $this->returnToParent();
//        } else {
//            $this->chiudiForm = false;
            $this->Dettaglio($this->keyPropas);
        }
    }

    private function GetTestiAssociati($procedimento, $tutti = false) {
        $this->testiAssociati = array();
        $Anapra_rec = $this->praLib->GetAnapra($procedimento);
        $tipoEnte = $this->praLib->GetTipoEnte();
        if ($tipoEnte == "M") {
            $ditta = App::$utente->getKey('ditta');
            $DB = $this->PRAM_DB;
        } else {
            if ($Anapra_rec['PRASLAVE'] == 1) {
                $ditta = $this->praLib->GetEnteMaster();
                $DB = ItaDB::DBOpen('PRAM', $ditta);
            } else {
                $ditta = App::$utente->getKey('ditta');
                $DB = $this->PRAM_DB;
            }
        }

        $destinazione = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
        $testiAssociati = array();
        if ($tutti == false) {
            $sql = "SELECT ITEWRD FROM ITEPAS WHERE ITECOD = '$procedimento' AND ITEDOW = 1 AND ITEDRR = 0";
            $testiAssociati = ItaDB::DBSQLSelect($DB, $sql, true);
            foreach ($testiAssociati as $key => $passo) {
                if ($passo['ITEWRD']) {
                    $testiAssociati[$key]['FILEPATH'] = $destinazione . $passo['ITEWRD'];
                    $testiAssociati[$key]['FILENAME'] = $passo['ITEWRD'];
                    $testiAssociati[$key]['CLASSIFICAZIONE'] = "TESTOASSOCIATO";
                } else {
                    unset($testiAssociati[$key]);
                }
            }
        } else {
            $testiAssociati = $this->GetFileList($destinazione);
        }
        if (!$testiAssociati) {
            return false;
        }
        return $testiAssociati;
    }

    private function ConfermaQualificaAllegati($campo, $valore) {
        $key = $_POST[$this->gridAllegati]['gridParam']['selrow'];
        $this->allegatiAzione[$key][$campo] = $valore;
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
    }

    private function AggiornaRecord() {
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $propas_tmp = $this->proLibPratica->GetPropas($this->keyPropas, 'propak');
        $procedimento = $propas_tmp['PRONUM'];
        $keyPasso = $propas_tmp['PROPAK'];
        $propas_rec = $_POST[$this->nameForm . '_PROPAS'];
        $propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_rec['PRODATEEDIT'] = date("Ymd");
        $propas_rec['PROORAEDIT'] = date("H:i:s");

        $update_Info = "Oggetto: Aggiornamento passo con seq " . $propas_rec['PROSEQ'] . " e chiave " . $propas_rec['PROPAK'];
        if (!$this->updateRecord($this->PROT_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("ERRORE", "Aggiornamento record");
            return false;
        }
//        if (!$this->RegistraAllegati($keyPasso)) {
//            Out::msgStop("ERRORE", "Aggiornamento Allegati fallito");
//            return false;
//        }
        if (!$this->RegistraAltriDati($keyPasso, $_POST[$this->nameForm . '_PROPAS'])) {
            Out::msgStop("ERRORE", "Aggiornamento Dati Aggiuntivi fallito");
            return false;
        }
        $this->proLibPratica->ordinaPassi($procedimento);
        $this->proLibPratica->sincronizzaStato($procedimento);

        if ($this->fascicolaDocumento) {
            $pronum = $this->fascicolaDocumento['PRONUM'];
            $propar = $this->fascicolaDocumento['PROPAR'];
            if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $proges_rec['GESKEY'], $pronum, $propar, $propas_rec['PASPRO'], $propas_rec['PASPAR'])) {
                Out::msgStop("Fascicolazione Protocollo", $this->proLibFascicolo->getErrMessage());
                return false;
            }
        }
        $propas_new = $this->proLibPratica->GetPropas($propas_rec['PROPAK']);
        if (!$this->SincronizzaAzione($propas_new)) {
            Out::msgStop("ERRORE", "Aggiornamento Atri Dati fallito");
            return false;
        }
        if ($propas_rec['PROFIN']) {
            Out::hide($this->nameForm . '_Chiudi');
            Out::show($this->nameForm . '_Apri');
        }
        return true;
    }

    private function GetFileList($filePath, $procedimento) {
        if (!$dh = @opendir($filePath)) {
            return false;
        }
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            $rowid += 1;
            $retListGen[$rowid] = array(
//       'rowid' => $rowid,
                'TIPO' => 'Procedimento ' . $procedimento,
                'FILEPATH' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => $obj,
                'RIDORIG' => '0'
            );
        }
        closedir($dh);
        return $retListGen;
    }

    private function ContaSizeAllegati($allegati) {
        if ($allegati) {
            $totSize = 0;
            foreach ($allegati as $allegato) {
                $totSize = $totSize + filesize($allegato['FILEPATH']);
            }
            if ($totSize != 0) {
                $Size = $this->proLib->formatFileSize($totSize);
                Out::valore($this->nameForm . "_Totale", $Size);
            }
        }
    }

    private function verificaNomiAllegati($lista = '') {
//
// Verifico che il nome non sia gia presente
//
        $arrNomi = array();
        foreach ($this->allegatiAzione as $key => $allegato) {
            $arrNomi[] = $allegato['FILEORIG'];
        }

        foreach ($lista as $key => $uplAllegato) {
            $nuovoNome = $uplAllegato['FILEORIG'];
            $contatore = 0;
            while (true) {
                if (in_array($nuovoNome, $arrNomi)) {
                    $contatore += 1;
                    $nuovoNome = pathinfo($uplAllegato['FILEORIG'], PATHINFO_FILENAME) . "_" . $contatore . "." . pathinfo($uplAllegato['FILEORIG'], PATHINFO_EXTENSION);
                } else {
                    break;
                }
            }
            $lista[$key]['FILEORIG'] = $nuovoNome;
        }
        return $lista;
    }

    private function caricaAllegatiEsterni($lista = "") {
        $lista = $this->verificaNomiAllegati($lista);
        $i = count($this->allegatiAzione);
        foreach ($lista as $uplAllegato) {
            $ext = pathinfo($uplAllegato['FILEPATH'], PATHINFO_EXTENSION);
            $edit = "";
            if (strtolower($ext) == "zip") {
                $edit = "<span class=\"ita-icon ita-icon-winzip-24x24\">Estrai File Zip</span>";
            }
            $daInserire = array();
//
//Valorizzo Tabella
//

            $daInserire['PROV'] = $i;
            $daInserire['RANDOM'] = '<span style = "color:orange;">' . $uplAllegato['FILENAME'] . '</span>';
            $daInserire['NAME'] = '<span style = "color:orange;">' . $uplAllegato['FILEORIG'] . '</span>';
            $daInserire['INFO'] = $uplAllegato['FILEINFO'];
            $daInserire['EDIT'] = $edit;
            $daInserire['SIZE'] = $this->proLib->formatFileSize(filesize($uplAllegato['FILEPATH']));
            $daInserire['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $daInserire['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
//
//Valorizzo Array
//
            $daInserire['PROVENIENZA'] = 'ESTERNO';
            $daInserire['FILEINFO'] = $uplAllegato['FILEINFO'];
            $daInserire['FILEPATH'] = $uplAllegato['FILEPATH'];
            $daInserire['FILENAME'] = $uplAllegato['FILENAME'];
            $daInserire['FILEORIG'] = $uplAllegato['FILEORIG'];
            $daInserire['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $daInserire['PASORADOC'] = date("H:i:s");
            $daInserire['PASDATADOC'] = date("Ymd");
            $daInserire['PASDAFIRM'] = 1;
            $daInserire['ROWID'] = 0;
            $this->allegatiAzione[$i] = $daInserire;
        }
//
// Ricarico la griglia della form
// 
        $this->ContaSizeAllegati($this->allegatiAzione);
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
        return;
    }

    private function caricaTestoAssociato($rowid) {
        $testo = $this->testiAssociati[$rowid];
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                $this->returnToParent();
            }
        }
        $percorsoTmp = itaLib::getPrivateUploadPath();
        $randName = md5(rand() * time()) . "." . pathinfo($testo['FILENAME'], PATHINFO_EXTENSION);
        $fileOrig = $testo['FILENAME'];
        @copy($testo['FILEPATH'], $percorsoTmp . "/" . $randName);

        $allegatoLevel1 = array();
//        $arrayTop = array_slice($this->allegatiAzione, 0, $i);
//        $arrayDown = array_slice($this->allegatiAzione, $i);
//Valorizzo Tabella
        $inc = count($this->allegatiAzione); // + 1;
        $allegatoLevel1['PROV'] = $inc;
        $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
        $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $fileOrig . '</span>';
        $allegatoLevel1['INFO'] = $testo['FILENAME'];
//$allegatoLevel1['SIZE'] = round((filesize($percorsoTmp . "/" . $randName) / 1048576), 3) . "MB";
        $allegatoLevel1['SIZE'] = $this->proLib->formatFileSize(filesize($percorsoTmp . "/" . $randName));
        $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
        $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
//Valorizzo Array
        $allegatoLevel1['PROVENIENZA'] = 'TESTOASSOCIATO';
        $allegatoLevel1['FILEINFO'] = $testo['FILENAME'];
        $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $randName;
        $allegatoLevel1['FILENAME'] = $randName;
        $allegatoLevel1['FILEORIG'] = $fileOrig;
        $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
        $allegatoLevel1['PASORADOC'] = date("H:i:s");
        $allegatoLevel1['PASDATADOC'] = date("Ymd");
        $allegatoLevel1['PASDAFIRM'] = 1;
        $allegatoLevel1['CODICE'] = $randName; //$allegato['CODICE'];
        $allegatoLevel1['ROWID'] = 0;

        $this->allegatiAzione[$inc] = $allegatoLevel1;


        $this->ContaSizeAllegati($this->allegatiAzione);
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
        return;
    }

    private function caricaTestoBase($codice, $tipo = "codice") {
        $allegato = $this->docLib->getDocumenti($codice, $tipo);
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                $this->returnToParent();
            }
        }
        $percorsoTmp = itaLib::getPrivateUploadPath();
        $contenuto = $allegato['CONTENT'];
        $contenuto = "<!-- itaTestoBase:" . $allegato['CODICE'] . " -->" . $contenuto;
        $suffix = pathinfo($allegato['URI'], PATHINFO_EXTENSION);
        $randName = md5(rand() * time()) . "." . $suffix;
        file_put_contents($percorsoTmp . "/" . $randName, $contenuto);


        $this->aggiungiAllegato($randName, $percorsoTmp . "/" . $randName, $allegato['CODICE'] . "." . $suffix, array("PROVENIENZA" => "TESTOBASE"));
        return;


        $i = count($this->allegatiAzione);
        $daInserire = array();
        $daInserire['PROV'] = $i;
        $daInserire['RANDOM'] = '<span style="color:orange;">' . $randName . '</span>';
        $daInserire['NAME'] = '<span style="color:orange;">' . $allegato['CODICE'] . "." . $suffix . '</span>';
        $daInserire['INFO'] = $allegato['OGGETTO'];
        $daInserire['SIZE'] = $this->proLib->formatFileSize(filesize($percorsoTmp . "/" . $randName));
        $daInserire['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
        $daInserire['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";
        $daInserire['PROVENIENZA'] = 'TESTOBASE';
        $daInserire['FILEINFO'] = $allegato['OGGETTO'];
        $daInserire['FILEPATH'] = $percorsoTmp . "/" . $randName;
        $daInserire['FILENAME'] = $randName;
        $daInserire['FILEORIG'] = $allegato['CODICE'] . "." . $suffix;
        $daInserire['PASUTELOG'] = App::$utente->getKey('nomeUtente');
        $daInserire['PASORADOC'] = date("H:i:s");
        $daInserire['PASDATADOC'] = date("Ymd");
        $daInserire['PASDAFIRM'] = 1;
        $daInserire['CODICE'] = $randName;
        $daInserire['ROWID'] = 0;
        $this->allegatiAzione[] = $daInserire;
        $this->ContaSizeAllegati($this->allegatiAzione);
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
        return;
    }

    private function caricaAllegatiInterno() {
        $allegati = array();
        $selRows = explode(",", $_POST['retKey']);
        foreach ($selRows as $riga) {
            $allegati[] = $this->allegatiAppoggio[$riga];
        }

        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Archiviazione File.", "Creazione ambiente di lavoro temporaneo fallita.");
                $this->returnToParent();
            }
        }
        $percorsoTmp = itaLib::getPrivateUploadPath();
        foreach ($allegati as $allegato) {
//            $fileOrig = $allegato['FILENAME'];
            usleep(50000); // 50 millisecondi;
            list($msec, $sec) = explode(" ", microtime());
            $msecondi = substr($msec, 2, 2);
            $filename = md5(rand() * $sec . $msecondi) . "." . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
            if (!@copy($allegato['FILEPATH'], $percorsoTmp . "/" . $filename)) {
                Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILEPATH'] . " su " . $percorsoTmp . "/" . $filename . " !");
                return;
            }
            $i = count($this->allegatiAzione);
            $allegatoLevel1 = array();
//Valorizzo Tabella
            $allegatoLevel1['PROV'] = $i;
            $allegatoLevel1['RANDOM'] = '<span style="color:orange;">' . $filename . '</span>';
            $allegatoLevel1['NAME'] = '<span style="color:orange;">' . $allegato['FILEORIG'] . '</span>';
            $allegatoLevel1['INFO'] = $allegato['FILEINFO'];
            $allegatoLevel1['SIZE'] = $this->proLib->formatFileSize(filesize($allegato['FILEPATH']));
            $allegatoLevel1['EVIDENZIA'] = "<span class=\"ita-icon ita-icon-evidenzia-24x24\">Evidenzia Allegato</span>";
            $allegatoLevel1['LOCK'] = "<span class=\"ita-icon ita-icon-unlock-24x24\">Blocca Allegato</span>";

//Valorizzo Array
            $allegatoLevel1['PROVENIENZA'] = 'INTERNO';
            $allegatoLevel1['FILEINFO'] = $allegato['FILEINFO'];
            $allegatoLevel1['FILEPATH'] = $percorsoTmp . "/" . $filename;
            $allegatoLevel1['FILENAME'] = $filename;
            $allegatoLevel1['FILEORIG'] = $allegato['FILEORIG'];
            $allegatoLevel1['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $allegatoLevel1['PASORADOC'] = date("H:i:s");
            $allegatoLevel1['PASDATADOC'] = date("Ymd");
            $allegatoLevel1['PASDAFIRM'] = 1;
            $this->allegatiAzione[$i] = $allegatoLevel1;
        }
        $this->ContaSizeAllegati($this->allegatiAzione);
        $this->CaricaGriglia($this->gridAllegati, $this->allegatiAzione, '1', '100000');
        return;
    }

    private function selectNewProt() {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == '2') {
            Out::msgQuestion("Protocollo.", "Seleziona il Tipo di Protocollo:", array(
                'F6-Documento Formale' => array('id' => $this->nameForm . '_DocFormale', 'model' => $this->nameForm, 'shortCut' => "f6"),
                'F8-Partenza' => array('id' => $this->nameForm . '_ProtPartenza', 'model' => $this->nameForm, 'shortCut' => "f8")
                    )
            );
        } else if ($profilo['PROT_ABILITATI'] == '1' || $profilo['PROT_ABILITATI'] == '3') {
            $this->nuovoProtocollo('C');
        }
    }

    private function nuovoProtocollo($tipo) {
        $allegati = array();
        foreach ($this->appoggioDati as $allegatoAppoggio) {
            $anadoc_orig = $this->proLib->GetAnadoc($allegatoAppoggio['ROWID'], 'rowid');
            $allegato = array();
            $allegato['ROWID'] = 0;
            $allegato['FILEPATH'] = $allegatoAppoggio['FILEPATH'];
            $allegato['FILENAME'] = $allegatoAppoggio['FILENAME'];
            $allegato['FILEINFO'] = $allegatoAppoggio['FILEINFO'];
            $allegato['DOCNAME'] = $allegato['NOMEFILE'] = $allegatoAppoggio['FILEORIG'];
            $allegato['DOCTIPO'] = "";
            $allegato['DOCFDT'] = $anadoc_orig['DOCFDT'];
            $allegato['ROWIDORIGINE'] = $anadoc_orig['ROWID'];
            $allegati[] = $allegato;
        }
        $this->appoggioDati = null;
        $this->AggiornaRecord();
        $proges_rec = $this->proLibPratica->GetProges($this->currGesnum);
        $model = 'proArri';
        itaLib::openForm($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($this->nameForm);
        $formObj->setReturnEvent('returnProArri');
        $formObj->setReturnId('');
        $_POST = array();
        $_POST['tipoProt'] = $tipo;
        $_POST['datiBlocco']['PROFASKEY'] = $proges_rec['GESKEY'];
        $_POST['datiBlocco']['propak'] = $this->keyPropas;
        $_POST['datiBlocco']['allegati'] = $allegati;
        $formObj->setEvent('openform');
        $formObj->parseEvent();
        Out::setFocus('', 'proArri_Propre1');
    }

    private function caricaUof() {
        Out::attributo($this->nameForm . '_PROPAS[PASPROUFF]', 'disabled', '1', '');
        Out::codice('$(protSelector("#' . $this->nameForm . '_PROPAS[PASPROUFF]' . '")+" option").remove();');
        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        if ($anamed_rec) {
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
            $select = "1";
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($select) {
                    $this->prouof = $anauff_rec['UFFCOD'];
                }
                if ($anauff_rec['UFFANN'] == 0) {
                    Out::select($this->nameForm . '_PROPAS[PASPROUFF]', 1, $uffdes_rec['UFFCOD'], $select, substr($anauff_rec['UFFDES'], 0, 30));
                    $select = '';
                }
            }
        }
    }

    private function salvaAllegati($allegati, $NumProt, $TipoProt) {
        $gestiti = $this->proLibAllegati->GestioneAllegati($this, $NumProt, $TipoProt, $allegati, '', '');
        if (!$gestiti) {
            Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
            return false;
        }
    }

    private function salvaAllegatiOld($allegati) {
        foreach ($allegati as $allegato) {
            $destinazione = $this->proLib->SetDirectory($allegato['NUMEROPROTOCOLLO'], substr($allegato['TIPOPROTOCOLLO'], 0, 1));
            if (!$destinazione) {
                Out::msgStop("Archiviazione File", "Errore creazione cartella di destinazione.");
                return false;
            }
            if ($allegato['ROWID'] == 0) {
                if (!@rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                    Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $allegato['FILENAME'] . " !");
                    return false;
                }
                $anadoc_rec = array();
                $iteKey = $this->proLib->IteKeyGenerator($allegato['NUMEROPROTOCOLLO'], '', date('Ymd'), $allegato['TIPOPROTOCOLLO']);
                if (!$iteKey) {
                    Out::msgStop("Errore", $this->proLib->getErrMessage());
                    return false;
                }

                $anadoc_rec['DOCKEY'] = $iteKey;
                $anadoc_rec['DOCNUM'] = $allegato['NUMEROPROTOCOLLO'];
                $anadoc_rec['DOCPAR'] = $allegato['TIPOPROTOCOLLO'];
                $anadoc_rec['DOCFIL'] = $allegato['FILENAME'];
                $anadoc_rec['DOCLNK'] = "allegato://" . $allegato['FILENAME'];
                $anadoc_rec['DOCUTC'] = ""; //$_POST[$this->nameForm . '_ANAPRO']['PROCON'];
                $anadoc_rec['DOCUTE'] = ""; //
                $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
                $anadoc_rec['DOCTIPO'] = $allegato['DOCTIPO'];
                $anadoc_rec['DOCDAFIRM'] = $allegato['DOCDAFIRM'];
                $anadoc_rec['DOCMD5'] = md5_file($destinazione . "/" . $allegato['FILENAME']);
                $anadoc_rec['DOCSHA2'] = hash_file('sha256', $destinazione . "/" . $allegato['FILENAME']);
                $anadoc_rec['DOCNAME'] = $allegato['DOCNAME'];
                $anadoc_rec['DOCFDT'] = $allegato['DOCFDT'];
                $anadoc_rec['DOCFTM'] = $allegato['DOCFTM'];
                $anadoc_rec['DOCSTA'] = $allegato['DOCSTA'];
                $anadoc_rec['DOCORF'] = $allegato['DOCORF'];
                $anadoc_rec['DOCRELEASE'] = $allegato['DOCRELEASE'];
                $anadoc_rec['DOCIDMAIL'] = $allegato['DOCIDMAIL'];
                $anadoc_rec['DOCSERVIZIO'] = $allegato['DOCSERVIZIO'];
                $anadoc_rec['DOCEVI'] = $allegato['DOCEVI'];
                $anadoc_rec['DOCLOCK'] = $allegato['DOCLOCK'];
                $anadoc_rec['DOCCLA'] = $allegato['PROVENIENZA'];
                $anadoc_rec['DOCCLAS'] = $allegato['DOCCLAS'];
                $anadoc_rec['DOCDEST'] = $allegato['DOCDEST'];
                $anadoc_rec['DOCNOTE'] = $allegato['DOCNOTE'];
                $anadoc_rec['DOCMETA'] = $allegato['DOCMETA'];
                $anadoc_rec['DOCDATAFIRMA'] = $allegato['DOCDATAFIRMA'];
                $anadoc_rec['DOCUTELOG'] = $allegato['DOCUTELOG'];
                $anadoc_rec['DOCDATADOC'] = $allegato['DOCDATADOC'];
                $anadoc_rec['DOCORADOC'] = $allegato['DOCORADOC'];
                try {
                    $insert_Info = 'Inserimento: ' . $anadoc_rec['DOCKEY'] . ' ' . $anadoc_rec['DOCFIL'];
                    if (!$this->insertRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $insert_Info)) {
                        return false;
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                    return false;
                }
            } else {
                $anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'ROWID');
                $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
                $anadoc_rec['DOCTIPO'] = $allegato['DOCTIPO'];
                $anadoc_rec['DOCDAFIRM'] = $allegato['DOCDAFIRM'];
                try {
                    $update_Info = 'Oggetto: ' . $anadoc_rec['DOCFIL'] . " " . $anadoc_rec['DOCNOT'];
                    if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                        return false;
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore", $e->getMessage());
                    return false;
                }
            }
        }
    }

    private function nascondiCampiSuNodo() {
        Out::hide($this->nameForm . '_PROPAS[PROSTATO]_field');
        Out::hide($this->nameForm . '_Stato1_field');
        Out::hide($this->nameForm . '_PROPAS[PROINI]_field');
        Out::hide($this->nameForm . '_PROPAS[PROFIN]_field');
        Out::hide($this->nameForm . '_PROPAS[PRODTP]_field');
        Out::hide($this->nameForm . '_Chiudi');
        Out::hide($this->nameForm . '_Apri');
    }

    private function caricaDatiAggiuntivi($tipo = '1', $pageRows = '100000') {
        $this->CaricaGriglia($this->gridCampiAggiuntivi, $this->altriDati, $tipo, $pageRows);
        foreach ($this->altriDati as $key => $datiAgg) {
            if ($datiAgg['level'] === 0) {
                // !!!!!!!!! NON STA FUNZIONANDO !!!!!!!!
                TableView::setCellValue($this->altriDati, $key, 'DAGVAL', "", 'not-editable-cell', '', 'false');
            }
        }
    }

}

?>
