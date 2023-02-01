<?php

/**
 *
 * Gestione Protocollo/Fascicoli archivistici di Pratiche/Passi
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft sRL
 * @license
 * @version    27.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientRicerche.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientTabelle.class.php';

function praGestProtocollo() {
    $praGestProtocollo = new praGestProtocollo();
    $praGestProtocollo->parseEvent();
    return;
}

class praGestProtocollo extends itaModel {

    public $nameForm = "praGestProtocollo";
    public $PRAM_DB;
    public $praLib;
    public $currGesnum = '';
    public $keyPasso = '';
    public $tipoCom = '';
    public $tipoProtocollo = '';
    public $rowidAppoggio;
    public $copie = array();
    public $codice;
    public $tipo;
    public $rowidCopia;
    public $gridCopie = "praGestProtocollo_gridCopie";

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
            $this->tipoCom = App::$utente->getKey($this->nameForm . '_tipoCom');
            $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
            $this->codice = App::$utente->getKey($this->nameForm . '_codice');
            $this->tipo = App::$utente->getKey($this->nameForm . '_tipo');
            $this->copie = App::$utente->getKey($this->nameForm . '_copie');
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            $this->tipoProtocollo = App::$utente->getKey($this->nameForm . '_tipoProtocollo');
            $this->rowidCopia = App::$utente->getKey($this->nameForm . '_rowidCopia');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
        App::$utente->setKey($this->nameForm . '_tipoCom', $this->tipoCom);
        App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
        App::$utente->setKey($this->nameForm . '_codice', $this->codice);
        App::$utente->setKey($this->nameForm . '_tipo', $this->tipo);
        App::$utente->setKey($this->nameForm . '_copie', $this->copie);
        App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
        App::$utente->setKey($this->nameForm . '_tipoProtocollo', $this->tipoProtocollo);
        App::$utente->setKey($this->nameForm . '_rowidCopia', $this->rowidCopia);
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function setTipoCom($tipoCom) {
        $this->tipoCom = $tipoCom;
    }

    public function setGesnum($gesnum) {
        $this->currGesnum = $gesnum;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->AzzeraVariabili();

                /*
                 * Valorizzo variabili ambiente
                 */
                if ($this->keyPasso) {
                    $this->codice = $this->keyPasso;
                    $this->tipo = "PASSO";
                } else {
                    $this->codice = $this->currGesnum;
                    $this->tipo = "PRATICA";
                    $this->tipoCom = "";
                }

                /*
                 * Setto il tipo protocollo
                 */
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                $this->tipoProtocollo = proWsClientFactory::getAutoDriver();
                $arrDatiProtAggr = array();
                if ($proges_rec['GESSPA'] != 0) {
                    $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($proges_rec['GESSPA']);
                }
                if ($arrDatiProtAggr['TipoProtocollo']) {
                    $this->tipoProtocollo = $arrDatiProtAggr['TipoProtocollo'];
                }
                $this->Nascondi();

                /*
                 * Carica il vedi protocollo
                 */
                $this->caricaVediProtocollo();
                if ($this->tipoProtocollo == proWsClientHelper::CLIENT_IRIDE || $this->tipoProtocollo == proWsClientHelper::CLIENT_JIRIDE) {

                    /*
                     * Carica Fascicoli 
                     */
                    $this->caricaFascicoli();
                }

                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridCopie:
                        $retFascioli = $this->getArrayFascicoli();
                        if ($retFascioli['Status'] == "-1") {
                            Out::msgStop("Errore", $retFascioli['Message']);
                            break;
                        }
                        $this->CaricaGriglia($this->gridCopie, $this->copie);
                        break;
                }
                break;
            case 'addGridRow':
                $this->rowidCopia = '';
                switch ($_POST['id']) {
                    case $this->gridCopie:
                        Out::msgQuestion("Fascicoalzione Documenti", "Scegliere dove fascicolare il protocollo", array(
                            'In un Fascicolo Esistente' => array('id' => $this->nameForm . '_ConfermaFascicoloEsistente', 'model' => $this->nameForm),
                            'Nuovo Fasciolo' => array('id' => $this->nameForm . '_ConfermaFascicoloNuovo', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridCopie:
                        $documento = $this->copie[$_POST['rowid']];
                        if ($documento['ANNULLATO'] == true) {
                            Out::msgInfo("Attenzione", "Il documento selezionato risulta annullato quindi non è possibile effettuare modifiche.");
                            break;
                        }
                        if ($documento['TIPO'] == "Copia") {
                            Out::msgQuestion("Getsione Protocollo", "Scegliere l'operazione da effetuare", array(
                                'Annulla Copia Documento' => array('id' => $this->nameForm . '_AnnullaDocumento', 'model' => $this->nameForm),
                                'Riclassifica Documento' => array('id' => $this->nameForm . '_RiclassificaDocumento', 'model' => $this->nameForm)
                                    )
                            );
                            $this->rowidCopia = $_POST['rowid'];
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BloccaAllegatiPratica':
                        //$metaDati = proIntegrazioni::GetMetedatiProt($this->currGesnum);
                        $praFascicolo = new praFascicolo($this->currGesnum);
                        if ($this->keyPasso) {
                            $praFascicolo->setChiavePasso($this->keyPasso);
                            $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, $this->tipoCom);
                            if ($this->tipoCom == "P") {
                                $returnEvent = 'returnAggiungiAllegatiWsPar';
                            } elseif ($this->tipoCom == "A") {
                                $returnEvent = 'returnAggiungiAllegatiWsArr';
                            }
                        } else {
                            $returnEvent = 'returnAggiungiAllegatiWs';
                            $arrayDoc = $praFascicolo->getAllegatiProtocollaPratica($this->tipoProtocollo);
                        }
                        //salvo i rowid di TUTTI gli allegati
                        if ($arrayDoc) {
                            foreach ($arrayDoc['pasdoc_rec'] as $documento) {
                                $arrayDocWs[] = $documento['ROWID'];
                            }
                            praRic::ricAllegatiWs($this->PRAM_DB, $arrayDocWs, $this->nameForm, $returnEvent);
                        }

                        break;
                    case $this->nameForm . '_ConfermaFascicoloEsistente':
//                        $elementi['TipoProtocollo'] = $this->tipoProtocollo;
//                        $retRicFascicoli = proWsClientRicerche::lanciaRicercaFascicoliWS($elementi);
//                        if ($retRicFascicoli['Status'] == "-1") {
//                            Out::msgStop("Errore", $retRicFascicoli['Message']);
//                            break;
//                        }
//                        proWsClientRicerche::ricercaFascicoliWS($retRicFascicoli['arrayFascicoli'], $this->nameForm);
                        $this->getFormRicercaFascicoli("", "", "", "", false);
                        break;
                    case $this->nameForm . '_ConfermaFascicoloNuovo':
                        //$this->getFormRicercaFascicoli("", "", "", $this->copie[$this->rowidCopia]['INCARICO']);
                        $this->getFormRicercaFascicoli("", "", "", "");
                        break;
                    case $this->nameForm . '_ConfermaCreaFascicolo':

                        /*
                         * Come parametri per il metodo, prendo i Metadati del protocollo e ci aggiungo InCaricoA
                         */
                        $metaDati = proIntegrazioni::GetMetedatiProt($this->codice, $this->tipo, $this->tipoCom);
                        $metaDati['InCaricoA'] = $_POST[$this->nameForm . '_inCaricoA'];

                        /*
                         * CreaCopia o setto id copia selezionata
                         */
                        $retCreaCopie = array();
                        if ($this->rowidCopia) {
                            $retCreaCopie['RetValue']['DatiCopia']["idCopia"] = $this->copie[$this->rowidCopia]['IDDOC'];
                            $retCreaCopie['RetValue']['DatiCopia']["Carico"] = $_POST[$this->nameForm . '_inCaricoA'];
                        } else {
                            $retCreaCopie = proWsClientHelper::lanciaCreaCopiaWS($metaDati);
                            if ($retCreaCopie['Status'] == "-1") {
                                Out::msgStop("Errore", $retCreaCopie['Message']);
                                break;
                            }
                        }

                        /*
                         * Crea il Fascicolo e fascicolo il documento(la copia del protocollo)
                         * se la copia è stata creata
                         */
                        if (isset($retCreaCopie['RetValue']['DatiCopia'])) {
                            $retCreaRicl = $this->riclassificaCreaCopia($retCreaCopie['RetValue']['DatiCopia']["idCopia"], $retCreaCopie['RetValue']['DatiCopia']["Carico"]);
                            if ($retCreaRicl['Status'] == "-1") {
                                Out::msgStop("Errore", $retCreaRicl['Message']);
                                break;
                            }


                            Out::closeCurrentDialog();

                            Out::msgBlock('', 3000, false, "Fascicolazione del protocollo " . $metaDati['ProNum'] . "/" . $metaDati['Anno'] . "<br>avvenuta con successo nel fascicolo " . $param['dati']['Fascicolazione']['Oggetto']);
                        } else {
                            Out::msgInfo("", $retCreaCopie['Message']);
                        }
                        break;
                    case $this->nameForm . '_AnnullaMarcatura':
                        $retAddAll = $this->lanciaAggiungiAllegatiPar($this->rowidAppoggio, false, "P");
                        if ($retAddAll['Status'] == "-1") {
                            Out::msgStop("Protocollazione Allegati", $retAddAll['Message']);
                            break;
                        }
                        Out::msgInfo("Protocollazione Allegati", $retAddAll['Message']);
                        $this->caricaVediProtocollo();
                        break;
                    case $this->nameForm . '_ConfermaMarcatura':
                        $retAddAll = $this->lanciaAggiungiAllegatiPar($this->rowidAppoggio, true, "P");
                        if ($retAddAll['Status'] == "-1") {
                            Out::msgStop("Protocollazione Allegati", $retAddAll['Message']);
                            break;
                        }
                        Out::msgInfo("Protocollazione Allegati", $retAddAll['Message']);
                        $this->caricaVediProtocollo();
                        break;
                    case $this->nameForm . "_RiclassificaDocumento":
                        Out::msgQuestion("Fascicoalzione Documenti", "Scegliere dove fascicolare il protocollo", array(
                            'In un Fascicolo Esistente' => array('id' => $this->nameForm . '_ConfermaFascicoloEsistente', 'model' => $this->nameForm),
                            'Nuovo Fasciolo' => array('id' => $this->nameForm . '_ConfermaFascicoloNuovo', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . "_AnnullaDocumento":
                        $copia = $this->copie[$this->rowidCopia];
                        Out::msgInput(
                                "Annullamento Documento N. " . $copia['IDDOC'], array(
                            array(
                                'label' => array('style' => "width:70px;", 'value' => 'Motivo'),
                                'id' => $this->nameForm . '_motivoAnnullamento',
                                'name' => $this->nameForm . '_motivoAnnullamento',
                                'type' => 'textarea',
                                'class' => "required ita-edit-newline ita-edit-multiline",
                                'rows' => '5',
                                'cols' => '50',
                                'width' => '50',
                                'size' => '30'),
                                ), array(
                            'F5-Conferma Annullamento' => array('id' => $this->nameForm . '_ConfermaAnnullaDoc', 'model' => $this->nameForm, 'class' => 'ita-button-validate', 'shortCut' => "f5")
                                ), $this->nameForm . "_divGestione"
                        );
                        break;
                    case $this->nameForm . "_ConfermaAnnullaDoc":
                        $elementi = array();
                        $elementi['idDocumento'] = $this->copie[$this->rowidCopia]['IDDOC'];
                        $elementi['Motivazione'] = $_POST[$this->nameForm . '_motivoAnnullamento'];
                        $elementi['TipoProtocollo'] = $this->tipoProtocollo;
                        $retAnnulla = proWsClientHelper::lanciaAnnullaDocumentoWS($elementi);
                        if ($retAnnulla['Status'] == "-1") {
                            Out::closeCurrentDialog();
                            Out::msgStop("Erroe", $retAnnulla['Message']);
                            break;
                        }

                        Out::closeCurrentDialog();

                        $copia = array();
                        $copia['idFascicolo'] = $this->copie[$this->rowidCopia]['ID'];
                        $copia['idCopia'] = $this->copie[$this->rowidCopia]['IDDOC'];
                        $copia['FascicoloNumero'] = $this->copie[$this->rowidCopia]['NUMERO'];
                        $copia['FascicoloAnno'] = $this->copie[$this->rowidCopia]['ANNO'];
                        $copia['Carico'] = $this->copie[$this->rowidCopia]['INCARICO'];
                        $newCopia = $this->getArrayCopia($copia);

                        $this->copie[$this->rowidCopia] = $newCopia;

                        $this->CaricaGriglia($this->gridCopie, $this->copie);

                        Out::msgBlock('', 3000, false, $retAnnulla['Message']);
                        break;
                    case $this->nameForm . "_titolario_butt":
                        $elementi['TipoProtocollo'] = $this->tipoProtocollo;
                        $retRicTitolari = proWsClientTabelle::lanciaRicercaTitolarioWS($elementi);
                        if ($retRicTitolari['Status'] == "-1") {
                            Out::msgStop("Errore", $retRicTitolari['Message']);
                            break;
                        }
                        proWsClientTabelle::ricercaTitolariWS($retRicTitolari['arrayTitolari'], $this->nameForm);
                        break;
                    case $this->nameForm . "_inCaricoA_butt":
                        $elementi['TipoProtocollo'] = $this->tipoProtocollo;
                        $retRicOperatori = proWsClientTabelle::lanciaRicercaOperatoriWS($elementi);
                        if ($retRicOperatori['Status'] == "-1") {
                            Out::msgStop("Errore", $retRicOperatori['Message']);
                            break;
                        }
                        proWsClientTabelle::ricercaOpearatoriWS($retRicOperatori['arrayOperatori'], $this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case 'returnAggiungiAllegatiWs':
                if (!$_POST['retKey']) {
                    Out::msgInfo("Attenzione.", "Non sono stati selezionati allegati da mandare al protocollo");
                    break;
                }

                $retAddAll = $this->lanciaAggiungiAllegati($_POST['retKey']);
                if ($retAddAll['Status'] == "-1") {
                    Out::msgStop("Protocollazione Allegati", $retAddAll['Message']);
                    break;
                }
                Out::msgInfo("Protocollazione Allegati", $retAddAll['Message']);
                $this->caricaVediProtocollo();
                break;
            case 'returnAggiungiAllegatiWsPar':
                if (!$_POST['retKey']) {
                    Out::msgInfo("Attenzione.", "Non sono stati selezionati allegati da mandare al protocollo");
                    break;
                }

                $pracomP_rec = $this->praLib->GetPracomP($this->keyPasso);
                //$metaDati = proIntegrazioni::GetMetedatiProt($this->keyPasso, "PASSO", "P");
                $idScelti = array();
                $idAllegatiScelti = explode(",", $_POST['retKey']);
                foreach ($idAllegatiScelti as $id) {
                    $idScelti[] = substr($id, 1);
                }
                $praFascicolo = new praFascicolo($this->currGesnum);
                $praFascicolo->setChiavePasso($this->keyPasso);
                if (!$idScelti) {
                    $idScelti = "NO";
                }
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "P", $idScelti); //aggiungo i filtri alla selezione!
                $daMarcare = false;

                if (isset($arrayDoc['Principale'])) {
                    if ($this->tipoProtocollo != proWsClientHelper::CLIENT_PALEO4) {
                        if (strtolower($arrayDoc['Principale']['estensione'] == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    } else {
                        if (strtolower(pathinfo($arrayDoc['Principale']['nomeFile'], PATHINFO_EXTENSION) == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    }
                }
                foreach ($arrayDoc['Allegati'] as $allegato) {
                    if ($this->tipoProtocollo != proWsClientHelper::CLIENT_PALEO4) {
                        if (strtolower($allegato['estensione'] == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    } else {
                        if (strtolower(pathinfo($allegato['Documento']['Nome'], PATHINFO_EXTENSION) == 'pdf') && $pracomP_rec['COMPRT']) {
                            $daMarcare = true;
                        }
                    }
                }
                if ($daMarcare) {
                    Out::msgQuestion("ATTENZIONE!", "Ci sono dei pdf da allegare. Vuoi marcarli con il numero protocollo?", array(
                        'F8-Prosegui senza Marcatura' => array('id' => $this->nameForm . '_AnnullaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f8"),
                        'F5-Prosegui con Marcatura' => array('id' => $this->nameForm . '_ConfermaMarcatura', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                    );
                    $this->rowidAppoggio = $_POST['retKey'];
                } else {
                    $retAddAll = $this->lanciaAggiungiAllegatiPar($_POST['retKey'], false, "P");
                    if ($retAddAll['Status'] == "-1") {
                        Out::msgStop("Protocollazione Allegati", $retAddAll['Message']);
                        break;
                    }
                    Out::msgInfo("Protocollazione Allegati", $retAddAll['Message']);
                    $this->caricaVediProtocollo();
                }

                break;
            case 'returnAggiungiAllegatiWsArr':
                if (!$_POST['retKey']) {
                    Out::msgInfo("Attenzione.", "Non sono stati selezionati allegati da mandare al protocollo");
                    break;
                }
                //$metaDati = proIntegrazioni::GetMetedatiProt($this->keyPasso, "PASSO", "A");
                $idScelti = array();
                $idAllegatiScelti = explode(",", $_POST['retKey']);
                foreach ($idAllegatiScelti as $id) {
                    $idScelti[] = substr($id, 1);
                }
                $praFascicolo = new praFascicolo($this->currGesnum);
                $praFascicolo->setChiavePasso($this->keyPasso);
                if (!$idScelti) {
                    $idScelti = "NO";
                }
                $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, false, "A", $idScelti); //aggiungo i filtri alla selezione!
                $retAddAll = $this->lanciaAggiungiAllegatiArr($_POST['retKey'], false, "A");
                if ($retAddAll['Status'] == "-1") {
                    Out::msgStop("Protocollazione Allegati", $retAddAll['Message']);
                    break;
                }
                Out::msgInfo("Protocollazione Allegati", $retAddAll['Message']);
                $this->caricaVediProtocollo();
                break;
//            case 'returnRicercaFascicoliWs':
//                if (isset($_POST['rowData'])) {
//                    $this->getFormRicercaFascicoli($_POST['rowData']['ID'], $_POST['rowData']['OGGETTO'], $_POST['rowData']['CLASSIFICA'], $this->copie[$this->rowidCopia]['INCARICO']);
//                }
//                break;
            case 'returnRicercaTitolarioWs':
                Out::valore($this->nameForm . "_titolario", $_POST['rowData']['CODICE']);
                break;
            case 'returnRicercaOperatoriWs':
                Out::valore($this->nameForm . "_inCaricoA", $_POST['rowData']['CODICE']);
                break;
        }
    }

    public function returnToParent() {
        $returnModelObj = itaModel::getInstance($this->returnModel);
        if ($returnModelObj == false) {
            return;
        }
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['destinatario'] = $this->destinatario;
        $_POST['rowid'] = $this->rowid;
        $returnModelObj->parseEvent();
        $this->close();
    }

    public function Nascondi() {
        /*
         * Trovo i metadati del protocollo
         */
        $metaDati = proIntegrazioni::GetMetedatiProt($this->codice, $this->tipo, $this->tipoCom);
        Out::hide($this->nameForm . '_BloccaAllegatiPratica');
        Out::tabDisable($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneFascicoli");

        if (($this->tipoProtocollo == proWsClientHelper::CLIENT_IRIDE || $this->tipoProtocollo == proWsClientHelper::CLIENT_JIRIDE || $this->tipoProtocollo == proWsClientHelper::CLIENT_ITALSOFT_REMOTO_ALLE ||
                $this->tipoProtocollo == proWsClientHelper::CLIENT_PALEO4 || $this->tipoProtocollo == proWsClientHelper::CLIENT_ITALPROT || $this->tipoProtocollo == proWsClientHelper::CLIENT_KIBERNETES || $this->tipoProtocollo == proWsClientHelper::CLIENT_CIVILIANEXT) && $metaDati['ProNum']) {
            Out::show($this->nameForm . "_BloccaAllegatiPratica");
        }
    }

    public function AzzeraVariabili() {
        TableView::disableEvents($this->gridCopie);
        TableView::clearGrid($this->gridCopie);
        //
        $this->tipo = '';
        $this->codice = '';
        $this->rowidAppoggio = '';
        $this->tipoProtocollo = '';
        $this->rowidCopia = '';
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_tipoCom');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_codice');
        App::$utente->removeKey($this->nameForm . '_tipo');
        App::$utente->removeKey($this->nameForm . '_copie');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_tipoProtocollo');
        App::$utente->removeKey($this->nameForm . '_rowidCopia');
        Out::closeDialog($this->nameForm);
    }

    function caricaFascicoli() {
        /*
         * Abilito la tab
         */
        Out::tabEnable($this->nameForm . "_tabProtocollo", $this->nameForm . "_paneFascicoli");

        /*
         * Leggo il fascicolo principale
         */
        TableView::enableEvents($this->gridCopie);
        TableView::reload($this->gridCopie);
    }

    function caricaVediProtocollo() {
        $html = proIntegrazioni::VediProtocollo($this->codice, $this->tipo, $this->tipoCom);

        if ($html['Status'] == "-1") {
            Out::msgStop("Dati Protocollo", $html['Message']);
            return;
        }
        Out::html($this->nameForm . "_divVediProtocollo", $html);
        Out::codice('tableToGrid("#tableAllProt", {});');
    }

    public function lanciaAggiungiAllegatiPar($serieAllegati, $marca = false) {
        $pracom_recP = $this->praLib->GetPracomP($this->keyPasso);
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        $idAllegatiScelti = explode(",", $serieAllegati);
        $idScelti = array();
        //$this->allegatiPrtSel = array();
        foreach ($idAllegatiScelti as $id) {
            //$this->allegatiPrtSel[]['ROWID'] = substr($id, 1);
            $idScelti[] = substr($id, 1);
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }
        if ($marca) {
            $param = array();
            $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
            $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
            if (!$this->SegnaturaAllegati($idScelti, $param)) {
                return false;
            }
        }

        //$metaDati = proIntegrazioni::GetMetedatiProt($this->keyPasso, $this->tipo, $this->tipoCom);
        include_once ITA_BASE_PATH . '/apps/Pratiche/praWsClientManager.class.php';

        /*
         * Istanzio l'oggetto praWsClientManager in base al tipo protocollo configurato nei dati ente
         */
        $praWsClientManager = praWsClientManager::getInstance($this->tipoProtocollo);

        /*
         * Setto il numero del fascicolo
         */
        $praWsClientManager->setCurrGesnum($this->currGesnum);

        /*
         * Setto la chiave del passo
         */
        $praWsClientManager->setKeyPasso($this->keyPasso);

        /*
         * Carico gli allegati selezionati da aggiungere al protocollo
         */
        $praWsClientManager->loadAllegatiFromComunicazioneComP(true, $idScelti);

        /*
         * Aggiungo gli allegati al protocollo
         */
        $valore = $praWsClientManager->AggiungiAllegati();
        if ($valore["Status"] == "-1") {
            return $valore;
        }
        $arrayDoc = $praWsClientManager->getArrayDoc();

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$praFascicolo->bloccaAllegati($this->keyPasso, $arrayDoc['pasdoc_rec'], $this->tipoCom)) {
            $retAddAll["Status"] = "-2";
            $retAddAll["Message"] = "Blocco allegati del passo con chiave $this->keyPasso con protocollo fallito.";
            $retAddAll["RetValue"] = false;
            return $retAddAll;
        }

        return $valore;
    }

    public function lanciaAggiungiAllegatiArr($serieAllegati) {
        $metaDati = proIntegrazioni::GetMetedatiProt($this->keyPasso, $this->tipo, $this->tipoCom);
        //$pracom_recA = $this->praLib->GetPracomA($this->keyPasso);
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);

        $idAllegatiScelti = explode(",", $serieAllegati);
        $idScelti = array();
        foreach ($idAllegatiScelti as $id) {
            $idScelti[] = substr($id, 1);
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }

        $arrayDoc = $praFascicolo->getAllegatiProtocollaComunicazione($this->tipoProtocollo, true, "A", $idScelti);
        if (!$arrayDoc) {
            $retAddAll['Status'] = "-1";
            $retAddAll['Message'] = "Non sono stati trovati allegati da aggiungere al protocollo per il passo con chiave $this->keyPasso";
            $retAddAll['RetValue'] = false;
            return $retAddAll;
        }
        $arrayDocFiltrati = $praFascicolo->GetAllegatiNonProt($arrayDoc, $this->tipoProtocollo);
        //
        $param = array();
        $param['DocNumber'] = $metaDati['CodiceWS'];
        $param['NumeroProtocollo'] = $metaDati['ProNum'];
        $param['AnnoProtocollo'] = $metaDati['Anno'];
        $param['Segnatura'] = $metaDati['Segnatura'];
        $param['arrayDoc'] = $arrayDocFiltrati['arrayDoc'];
        $param['TipoProtocollo'] = $this->tipoProtocollo;

        /*
         * Aggiungi Allegati
         */
        $retAddAll = proWsClientHelper::lanciaAggiungiAllegatiWS($param);
        if ($retAddAll['Status'] == "-1") {
            return $retAddAll;
        }

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$praFascicolo->bloccaAllegati($this->keyPasso, $param['arrayDoc']['pasdoc_rec'], 'A')) {
            $retAddAll["Status"] = "-2";
            $retAddAll["Message"] = "Blocco allegati con protocollo del passo in arrivo con chiave $this->keyPasso fallito.";
            $retAddAll["RetValue"] = false;
            return $retAddAll;
        }

        if ($arrayDocFiltrati['strNoProt']) {
            $retAddAll['Message'] = $retAddAll['Message'] . "<br>" . $arrayDocFiltrati['strNoProt'];
        }
        return $retAddAll;
    }

    function lanciaAggiungiAllegati($serieAllegati, $marca = false) {
        $retAddAll = array();

        /*
         * Leggo i Metadati
         */
        $metaDati = proIntegrazioni::GetMetedatiProt($this->codice, $this->tipo, $this->tipoCom);

        /*
         * Rileggo PROGES_REC
         */
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        if (!$proges_rec) {
            $retAddAll['Status'] = "-1";
            $retAddAll['Message'] = "Record pratica non trovato";
            $retAddAll['RetValue'] = false;
            return $retAddAll;
        }

        $idAllegatiScelti = explode(",", $serieAllegati);
        $idScelti = array();
        foreach ($idAllegatiScelti as $id) {
            $idScelti[] = substr($id, 1);
        }
        if (!$idScelti) {
            $idScelti = "NO";
        }

        $param = array();
        $param['NumeroProtocollo'] = substr($proges_rec['GESNPR'], 4);
        $param['AnnoProtocollo'] = substr($proges_rec['GESNPR'], 0, 4);
        $param['tipo'] = $proges_rec['GESPAR'];
        $param['DocNumber'] = $metaDati['CodiceWS'];
        $param['Segnatura'] = $metaDati['Segnatura'];
        if ($marca) {
            $param['NumeroProtocollo'] = $metaDati['ProNum'];
            $param['AnnoProtocollo'] = $metaDati['Anno'];
        }

        $praFascicolo = new praFascicolo($this->currGesnum);
        $arrayDoc = $praFascicolo->getAllegatiProtocollaPratica($this->tipoProtocollo, true, false, $idScelti);
        if (!$arrayDoc) {
            $retAddAll['Status'] = "-1";
            $retAddAll['Message'] = "Non sono stati trovati allegati da aggiungere al protocollo per la pratica n. $this->currGesnum";
            $retAddAll['RetValue'] = false;
            return $retAddAll;
        }
        $arrayDocFiltrati = $praFascicolo->GetAllegatiNonProt($arrayDoc, $this->tipoProtocollo);
        $param['arrayDoc'] = $arrayDocFiltrati['arrayDoc'];

        /*
         * Se ci sono i metadati e il tipo protocollo nell'aggreggato, li inserisco nell'array elementi
         */
        $param['TipoProtocollo'] = $this->tipoProtocollo;
//        if ($arrDatiProtAggr) {
//            $param['MetaDatiProtocollo'] = $arrDatiProtAggr['MetadatiProtocollo'];
//        }

        /*
         * Aggiungi Allegati
         */
        $retAddAll = proWsClientHelper::lanciaAggiungiAllegatiWS($param);
        if ($retAddAll['Status'] == "-1") {
            return $retAddAll;
        }

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$praFascicolo->bloccaAllegati($proges_rec['GESNUM'], $param['arrayDoc']['pasdoc_rec'], 'PR')) {
            $retAddAll["Status"] = "-2";
            $retAddAll["Message"] = "Blocco allegati del fascicolo N. $this->currGesnum con protocollo fallito.";
            $retAddAll["RetValue"] = false;
            return $retAddAll;
        }

        if ($arrayDocFiltrati['strNoProt']) {
            $retAddAll['Message'] = $retAddAll['Message'] . "<br>" . $arrayDocFiltrati['strNoProt'];
        }
        return $retAddAll;
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '20') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
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

    function getArrayFascicoli() {
        $this->copie = array();

        /*
         * Rileggo PROGES_REC
         */
        $proges_rec = $this->praLib->GetProges($this->currGesnum);
        if (!$proges_rec) {
            $retAddAll['Status'] = "-1";
            $retAddAll['Message'] = "Record pratica non trovato";
            $retAddAll['RetValue'] = false;
            return $retAddAll;
        }

        /*
         * Come parametri per il metodo, prendo i Metadati del protocollo e ci aggiungo InCaricoA
         */
        $metaDati = proIntegrazioni::GetMetedatiProt($this->codice, $this->tipo, $this->tipoCom);

        /*
         * Leggo i dati del fascicolo principale
         */
        $arrayDati = proIntegrazioni::GetArrayDatiProtocollo($this->codice, $this->tipo, $this->tipoCom);
        if ($arrayDati['RetValue']['Dati']['IdPratica']) {
            $param = array();
            $param['idFascicolo'] = $arrayDati['RetValue']['Dati']['IdPratica'];
            $param['TipoProtocollo'] = $this->tipoProtocollo;
            $retLeggiFacc = proWsClientHelper::lanciaLeggiFascicoloWS($param);
            if ($retLeggiFacc['Status'] == "-1") {
                return $retLeggiFacc;
            }
            $Icona = '<div><span style="height:30px; margin-top:5px; background-size:85%; display:inline-block;" class="ita-icon ita-icon-document-24x24" ></span>';
            $Icona.= '<span title ="Fascicolo principale" style="display:inline-block; position:relative; margin-left:-15px; top:-5px; " class="ita-icon ita-icon-star-yellow-16x16"></span>';
            $Icona.='</div>';
            //
            $this->copie[0]["ID"] = $arrayDati['RetValue']['Dati']['IdPratica'];
            $this->copie[0]["IDDOC"] = $arrayDati['RetValue']['Dati']['IdDocumento'];
            $this->copie[0]["ANNO"] = $metaDati['Anno'];
            $this->copie[0]["NUMPRT"] = $metaDati['ProNum'];
            $this->copie[0]["NUMERO"] = $retLeggiFacc['RetValue']['DatiFascicolo']['Anno'] . " " . $retLeggiFacc['RetValue']['DatiFascicolo']['Numero'];
            $this->copie[0]["DATA"] = date('Ymd', strtotime($retLeggiFacc['RetValue']['DatiFascicolo']['Data']));
            $this->copie[0]["OGGETTO"] = $retLeggiFacc['RetValue']['DatiFascicolo']['Oggetto'];
            //$this->copie[0]["TIPOICON"] = '<span class="ita-icon ita-icon-mail-16x16">Principale</span>';
            $this->copie[0]["TIPOICON"] = $Icona;
            $this->copie[0]["TIPO"] = 'Principale';
            $this->copie[0]["INCARICO"] = $arrayDati['RetValue']['Dati']['InCaricoA_Descrizione'];
        }

        /*
         * LeggiCopie
         */
        $metaDati['TipoProtocollo'] = $this->tipoProtocollo;
        $retLeggiCopie = proWsClientHelper::lanciaLeggiCopiaWS($metaDati);
        if ($retLeggiCopie['Status'] == "-1") {
            return $retLeggiCopie;
        }

        /*
         * Creo l'array definitivo dei copie 
         */
        $i = 0;
        foreach ($retLeggiCopie['RetValue']['DatiCopia'] as $copia) {
            if ($copia['FascicoloNumero']) {
                $i++;
                $arrCopia = $this->getArrayCopia($copia);
                if ($arrCopia['Status'] == "-1") {
                    Out::msgStop("Errore", $arrCopia['Message']);
                    break;
                }
                $this->copie[$i] = $arrCopia;
            }
        }
    }

    function getArrayCopia($copia) {
        $metaDati = proIntegrazioni::GetMetedatiProt($this->codice, $this->tipo, $this->tipoCom);
        //
        $ini_tag = $fin_tag = '';
        $param = $arrCopia = array();
        $param['idFascicolo'] = $copia['idFascicolo'];
        $param['IdDocumento'] = $copia['idCopia'];
        $param['Numero'] = $copia['FascicoloNumero'];
        $param['Anno'] = $copia['FascicoloAnno'];
        $param['TipoProtocollo'] = $this->tipoProtocollo;
        $retLeggiDoc = proWsClientHelper::lanciaLeggiDocumentoWS($param);
        if ($retLeggiDoc['Status'] == "-1") {
            return $retLeggiDoc;
        }
        $arrCopia["ANNULLATO"] = false;
        if ($retLeggiDoc['RetValue']['DatiProtocollo']['IterAttivo'] == "") {
            $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
            $fin_tag = "</p>";
            $arrCopia["ANNULLATO"] = true;
        }
        $retLeggiFacc = proWsClientHelper::lanciaLeggiFascicoloWS($param);
        if ($retLeggiFacc['Status'] == "-1") {
            return $retLeggiFacc;
        }
        $arrCopia["ID"] = $ini_tag . $retLeggiFacc['RetValue']['DatiFascicolo']['CodiceFascicolo'] . $fin_tag;
        $arrCopia["IDDOC"] = $ini_tag . $copia['idCopia'] . $fin_tag;
        $arrCopia["ANNO"] = $ini_tag . $metaDati['Anno'] . $fin_tag;
        $arrCopia["NUMPRT"] = $ini_tag . $metaDati['ProNum'] . $fin_tag;
        $arrCopia["NUMERO"] = $ini_tag . $retLeggiFacc['RetValue']['DatiFascicolo']['Anno'] . " " . $retLeggiFacc['RetValue']['DatiFascicolo']['Numero'] . $fin_tag;
        $arrCopia["DATA"] = $ini_tag . date('Ymd', strtotime($retLeggiFacc['RetValue']['DatiFascicolo']['Data'])) . $fin_tag;
        $arrCopia["OGGETTO"] = $ini_tag . $retLeggiFacc['RetValue']['DatiFascicolo']['Oggetto'] . $fin_tag;
        $arrCopia["TIPOICON"] = '<div><span style="height:30px; margin-top:2px; background-size:85%;" class="ita-icon ita-icon-document-24x24" ></span>';
        $arrCopia["TIPO"] = 'Copia';
        $arrCopia["INCARICO"] = $ini_tag . $retLeggiDoc['RetValue']['DatiProtocollo']['InCaricoA_Descrizione'] . $fin_tag;
        //$arrCopia["INCARICO"] = $ini_tag . $copia['Carico'] . $fin_tag;
        return $arrCopia;
    }

    function getFormRicercaFascicoli($id = "", $oggetto = "", $titolario = "", $inCarico = "", $new = true) {
        $class = $classInCarico = $classId = $required = $requiredId = $lookup = "";
        if ($oggetto && $titolario) {
            $class = "ita-readonly";
        }

        if ($inCarico) {
            $classInCarico = "ita-readonly";
        }
        if ($new == true) {
            $classId = "ita-readonly";
            $required = "required";
            $lookup = "ita-edit-lookup";
        } else {
            $class = "ita-readonly";
            $classInCarico = "ita-readonly";
            $requiredId = "required";
        }
        Out::msgInput(
                'Dati Fascicolo', array(
            array(
                //'label' => array('style' => "display:none;width:70px;", 'value' => 'Id'),
                'label' => array('style' => "width:70px;", 'value' => 'Id'),
                'id' => $this->nameForm . '_idFascicolo',
                'name' => $this->nameForm . '_idFascicolo',
                'type' => 'text',
                //'class' => "ita-hidden",
                'class' => "$classId $requiredId",
                'value' => $id,
                'type' => 'text'),
            array(
                'label' => array('style' => "width:70px;", 'value' => 'Oggetto'),
                'id' => $this->nameForm . '_oggettoFascicolo',
                'name' => $this->nameForm . '_oggettoFascicolo',
                'type' => 'textarea',
                'class' => "$required ita-edit-newline ita-edit-multiline $class",
                'rows' => '5',
                '@textNode@' => $oggetto,
                'cols' => '50',
                'width' => '50',
                'size' => '30'),
            array(
                'label' => array('style' => "width:70px;", 'value' => 'Titolario'),
                'id' => $this->nameForm . '_titolario',
                'name' => $this->nameForm . '_titolario',
                'type' => 'text',
                'value' => $titolario,
                'class' => "$required $lookup $class",
                'width' => '50',
                'size' => '30'),
            array(
                'label' => array('style' => "width:70px;", 'value' => 'In Carico A'),
                'id' => $this->nameForm . '_inCaricoA',
                'name' => $this->nameForm . '_inCaricoA',
                'type' => 'text',
                'value' => $inCarico,
                //'class' => "$required $lookup $classInCarico",
                'class' => "$required $classInCarico",
                'width' => '50',
                'size' => '30'),
                ), array(
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCreaFascicolo', 'model' => $this->nameForm, 'class' => 'ita-button-validate', 'shortCut' => "f5")
                ), $this->nameForm . "_divGestione"
        );
    }

    private function SegnaturaAllegati($rowidScelti, $param) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';
        $praLibAllegati = new praLibAllegati();
        foreach ($rowidScelti as $id) {
            $pasdoc_rec = $this->praLib->GetPasdoc($id, "ROWID");
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, "PASSO", false);
            $fileInput = $pasdoc_rec['PASFIL'];
            $extAllegato = strtolower(pathinfo($pasdoc_rec['PASFIL'], PATHINFO_EXTENSION));
            if ($extAllegato == "xhtml" || $extAllegato == 'docx') {
                $fileInput = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME) . ".pdf";
            }
            if (strtolower(pathinfo($fileInput, PATHINFO_EXTENSION)) != "pdf") {
                continue;
            }
            $segnatura = $praLibAllegati->GetMarcatureString($param, $this->currGesnum, $id);
            $output = $praLibAllegati->ComponiPDFconSegnatura($segnatura, $pramPath . "/" . $fileInput);
            if (!$output) {
                Out::msgStop("Marcatura Allegato", $praLibAllegati->getErrMessage());
                return false;
            }
        }
        return true;
    }

    function riclassificaCreaCopia($idCopia, $inCarico) {
        $param = array();
        $param['dati']['Fascicolazione']['CodiceFascicolo'] = $_POST[$this->nameForm . '_idFascicolo']; //$_POST['rowData']['ID'];
        $param['idFascicolo'] = $_POST[$this->nameForm . '_idFascicolo']; //$_POST['rowData']['ID'];
        $param['idCopia'] = $idCopia; //$this->copie[$this->rowidCopia]['IDDOC'];
        $param['Carico'] = $inCarico; //$this->copie[$this->rowidCopia]['INCARICO'];
        $param['dati']['Fascicolazione']['Oggetto'] = $_POST[$this->nameForm . '_oggettoFascicolo'];
        $param['dati']['Classificazione'] = $_POST[$this->nameForm . '_titolario'];
        $param['DocNumber'] = $idCopia; //$this->copie[$this->rowidCopia]['IDDOC'];
        $param['TipoProtocollo'] = $this->tipoProtocollo;
        $retCreaFascicolo = proWsClientHelper::lanciaFascicolazioneWS($param);
        if ($retCreaFascicolo['Status'] == "-1") {
            return $retCreaFascicolo;
        }

        /*
         * Se viene creato un nuovo fascicolo riassegno il parametro
         */
        if ($retCreaFascicolo['IdFascicolo']) {
            $param['idFascicolo'] = $retCreaFascicolo['IdFascicolo'];
        }

        $arrCopia = $this->getArrayCopia($param);
        if ($arrCopia['Status'] == "-1") {
            return $arrCopia;
        }

        if ($this->rowidCopia) {
            $operazione = "Riclassificata";
            $this->copie[$this->rowidCopia] = $arrCopia;
        } else {
            $operazione = "Creata";
            $this->copie[] = $arrCopia;
        }

        $this->CaricaGriglia($this->gridCopie, $this->copie);

        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Copia $operazione correttamente.";
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

}
