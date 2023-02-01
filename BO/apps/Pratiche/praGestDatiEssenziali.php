<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';

/**
 *
 * GESTIONE EMAIL PRATICHE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    27.11.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praGobidManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';

function praGestDatiEssenziali() {
    $praGestDatiEssenziali = new praGestDatiEssenziali();
    $praGestDatiEssenziali->parseEvent();
    return;
}

class praGestDatiEssenziali extends itaModel {

    public $PRAM_DB;
    public $COMUNI_DB;
    public $nameForm = "praGestDatiEssenziali";
    public $praLib;
    public $proLib;
    public $praGobid;
    public $returnModel;
    public $returnEvent;
    public $datiMail;
    public $resultRest;
    public $rowidDipendente;
    public $rowidTipoPasso;
    public $mittDest;
    public $anades_rec;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->praGobid = new praGobidManager();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->datiMail = App::$utente->getKey($this->nameForm . "_datiMail");
            $this->rowidDipendente = App::$utente->getKey($this->nameForm . "_rowidDipendente");
            $this->rowidTipoPasso = App::$utente->getKey($this->nameForm . "_rowidTipoPasso");
            $this->mittDest = App::$utente->getKey($this->nameForm . '_mittDest');
            $this->anades_rec = App::$utente->getKey($this->nameForm . '_anades_rec');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_datiMail", $this->datiMail);
            App::$utente->setKey($this->nameForm . "_resultRest", $this->resultRest);
            App::$utente->setKey($this->nameForm . "_rowidDipendente", $this->rowidDipendente);
            App::$utente->setKey($this->nameForm . "_rowidTipoPasso", $this->rowidTipoPasso);
            App::$utente->setKey($this->nameForm . '_mittDest', $this->mittDest);
            App::$utente->setKey($this->nameForm . '_anades_rec', $this->anades_rec);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->creaCombo();
                $this->rowidDipendente = $this->rowidTipoPasso = "";
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->datiMail = $_POST['datiMail'];
                //Out::setDialogOption($this->nameForm, 'title', "'Inserire i dati obbligatori per caricare la mail proveniente da ".$_POST['email']);
                Out::valore($this->nameForm . '_Oggetto', $_POST['oggetto']);
                Out::valore($this->nameForm . '_ANADES[DESEMA]', $_POST['email']);
                Out::valore($this->nameForm . '_PROGES[GESDRE]', date('Ymd'));
                Out::hide($this->nameForm . '_CercaAnagrafeProt');

                /*
                 * Visualizzo i campi solo per goBid, cioè se c'è l'url rest dei parametri generali
                 */
                Out::hide($this->nameForm . '_PROGES[GESCODPROC]_field');
                Out::hide($this->nameForm . '_butt_anteprima');
                Out::hide($this->nameForm . '_divProtocollo');
                Out::hide($this->nameForm . '_divProtocolloIdSegn');
                Out::hide($this->nameForm . '_divMittDest');

                /*
                 * Lettura Parametro Gobid per cancellazione divIntestatario
                 */
                $urlRest = $this->praGobid->leggiParametro('RESTURL');
                if ($urlRest || $_POST['isFrontOfficeAvanzato'] === true) {
                    $profilo = proSoggetto::getProfileFromIdUtente();
                    Out::delContainer($this->nameForm . '_divIntestatario');
                    if ($urlRest) {
                        Out::valore($this->nameForm . '_PROGES[GESCODPROC]', $this->cercaCodiceProcedura($_POST['oggetto']));
                        Out::show($this->nameForm . '_PROGES[GESCODPROC]_field');
                        Out::show($this->nameForm . '_butt_anteprima');
                    }
                    $this->DecodAnanom($profilo['COD_ANANOM'], "Responsabile", "codice");
                    Out::hide($this->nameForm . '_Oggetto_field');
                }

                /*
                 * Se da front office, disabilito e spengo alcuni campi
                 */
                Out::show($this->nameForm . '_PROGES[GESPRO]_butt');
                Out::show($this->nameForm . '_PROGES[GESEVE]_butt');
                Out::show($this->nameForm . '_PROGES[GEWFSPRO]_butt');
                Out::attributo($this->nameForm . '_PROGES[GESPRO]', "readonly", '1');
                Out::attributo($this->nameForm . '_PROGES[GESEVE]', "readonly", '1');
                Out::attributo($this->nameForm . '_PROGES[GEWFSPRO]', "readonly", '1');
                if ($_POST['isFrontOffice'] === true) {
                    Out::delContainer($this->nameForm . '_divIntestatario');
                    Out::hide($this->nameForm . '_PROGES[GESPRO]_butt');
                    Out::hide($this->nameForm . '_PROGES[GESEVE]_butt');
                    Out::attributo($this->nameForm . '_PROGES[GESPRO]', "readonly", '0');
                    Out::attributo($this->nameForm . '_PROGES[GESEVE]', "readonly", '0');
                    Out::attributo($this->nameForm . '_PROGES[GEWFSPRO]', "readonly", '0');
                    Out::attributo($this->nameForm . '_PROGES[GESRES]', "readonly", '0');
                    $this->decodAnapra($this->datiMail['Dati']['PRORIC_REC']['RICPRO']);
                    $this->decodIteevt($this->datiMail['Dati']['PRORIC_REC']['RICPRO'], $this->datiMail['Dati']['PRORIC_REC']['RICEVE']);
                    $this->DecodAnanom($this->datiMail['Dati']['PRORIC_REC']['RICRES'], "Responsabile");
                }

                /*
                 * Div Assegnazioni
                 */
                $Filent_Rec_TabAss = $this->praLib->GetFilent(20);
                if ($Filent_Rec_TabAss['FILVAL'] != 1) {
                    Out::delContainer($this->nameForm . '_divAssegnazione');
                }

                /*
                 * Se da protocollo, mostro il div con i campi del protocollo
                 */
                if ($_POST['daProtocollo'] === true) {
                    $divPrt = $this->setDivSearchProt();
                    if (!$divPrt) {
                        Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                        break;
                    }
                    Out::show($this->nameForm . "_$divPrt");
                    Out::hide($this->nameForm . '_CercaAnagrafe');
                    Out::hide($this->nameForm . '_VediFamiglia');
                    Out::hide($this->nameForm . '_CercaIPA');
                    Out::hide($this->nameForm . '_ANADES[DESNOM]_butt');
                }

                /*
                 * Se vengo da praGest spengo il campo oggetto mail
                 */
                if ($this->returnModel == "praGest") {
                    Out::hide($this->nameForm . '_Oggetto_field');
                }
                break;
            case 'onClick':
                if (strpos($_POST['id'], "open_") !== false) {
                    Out::msgInfo("", print_r($_POST, true));
                    break;
                }
                switch ($_POST['id']) {
                    case $this->nameForm . '_Carica':
                        if ($_POST[$this->nameForm . '_PROGES']['GESCODPROC']) {
                            $proges_check_tab = $this->praLib->GetProges($_POST[$this->nameForm . '_PROGES']['GESCODPROC'], 'codiceProcedimento', true);
                            $msg = "Codice procedura " . $_POST[$this->nameForm . '_PROGES']['GESCODPROC'] . " già utilizzato.";
                            $msg .= "Codice fascicolo  " . substr($proges_check_tab[0]['GESNUM'], 4) . "/" . substr($proges_check_tab[0]['GESNUM'], 0, 4);
                            $msg .= ' <br>Desideri continuare?';
                            if ($proges_check_tab) {
                                Out::msgQuestion("ATTENZIONE!", $msg, array(
                                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAnagrafica', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnagrafica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                        )
                                );

                                //Out::msgInfo("Attenzione", $msg);
                                break;
                            }
                        }
                        Out::closeDialog($this->nameForm);
                        $this->returnToParent(true);
                        break;

                    case $this->nameForm . '_ConfermaAnagrafica':
                        Out::closeDialog($this->nameForm);
                        $this->returnToParent(true);
                        break;

                    case $this->nameForm . '_ConfermaCampoAgg':

                    case $this->nameForm . '_Annulla':
                        Out::closeDialog($this->nameForm);
                        $this->returnToParent(false);
                        break;
                    case $this->nameForm . "_ANADES[DESNOM]_butt":
//                        $Filent_Rec = $this->praLib->GetFilent(18);
//                        if ($Filent_Rec['FILVAL'] == 1) {
//                            proRic::proRicAnamed($this->nameForm, "", 'proAnamed', '2');
//                        } else {
                        praRic::praRicAnades($this->nameForm);
//                        }
                        break;
                    case $this->nameForm . '_CercaAnagrafe':
                        //anaRic::anaRicAnagra($this->nameForm);
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediFamiglia':
                        $cf = strtoupper($_POST[$this->nameForm . "_ANADES"]['DESFIS']);
                        if ($cf == "") {
                            Out::msgInfo("Attenzione", "Per visualizzare la famiglia scegliere un codice fiscale");
                            break;
                        }
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['cf'] = $cf;
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_PROGES[GESPRO]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_PROGES[GESPRO]', " PRAOFFLINE=0", "", true);
                        break;
                    case $this->nameForm . '_PROGES[GESWFPRO]_butt':
                        praRic::praRicAnapra($this->nameForm, "Ricerca endo-procedimenti", $this->nameForm . '_PROGES[GESWFPRO]', " (PRATPR = 'ENDOPROCEDIMENTOWRKF')", "", true);
                        break;
                    case $this->nameForm . '_CercaIPA':
                        $model = 'proRicIPA';
                        itaLib::openForm($model);
                        /* @var $modelObj itaModel */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnRicIPA');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                    case $this->nameForm . '_CercaAnagrafeProt':
                        Out::msgInput(
                                'Soggetto', array(
                            'label' => 'Inserisci il codice soggetto    ',
                            'id' => $this->nameForm . '_searchIdSoggetto',
                            'name' => $this->nameForm . '_searchIdSoggetto',
                            'type' => 'text',
                            'size' => '10',
                            'value' => '',
                            'maxchars' => '10'), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnagrafeProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;
                    case $this->nameForm . '_ConfermaAnagrafeProtocollo':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proIride.class.php';
                        $proIride = new proIride();
                        $paramA = array();
                        $paramA['IdSoggetto'] = $_POST[$this->nameForm . '_searchIdSoggetto'];
                        $ritorno = $proIride->LeggiAnagrafica($paramA);
                        if ($ritorno['Status'] == "0") {
                            $dati = $ritorno['RetValue']['Dati'];
                            Out::valore($this->nameForm . '_ANADES[DESNOM]', $dati['CognomeNome']);
                            Out::valore($this->nameForm . '_ANADES[DESFIS]', $dati['CodiceFiscale']);
                            Out::valore($this->nameForm . '_ANADES[DESEMA]', $dati['Email']);
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESEVE]_butt':
                        if ($_POST[$this->nameForm . "_PROGES"]['GESPRO']) {
                            praRic::ricIteevt($this->nameForm, "WHERE ITEPRA = '" . $_POST[$this->nameForm . "_PROGES"]['GESPRO'] . "'", "DETT");
                        } else {
                            Out::msgInfo("Attenzione!!", "Selezionare prima un procedimento");
                        }
                        break;

                    case $this->nameForm . '_butt_anteprima':
                        if ($_POST[$this->nameForm . '_PROGES']['GESCODPROC']) {
                            $resultRest = $this->callGobidRest($_POST[$this->nameForm . '_PROGES']['GESCODPROC']);
                            if (!$resultRest) {
                                Out::msgStop('ERRORE', $this->praGobid->getErrMessage());
                                break;
                            }
                            $impresa = $resultRest['IMPRESA'];
                            $curatore = $resultRest['CURATORE'];
                            $msg = "Impresa<br>" .
                                    $impresa['ragioneSociale'] . "<br>" .
                                    $impresa['indirizzo'] . "<br>" .
                                    $impresa['comune'] . "<br><br>" .
                                    "Curatore<br>" .
                                    $curatore['cognome'] . ' ' . $curatore['nome'] . "<br>" .
                                    $curatore['indirizzo'] . "<br>" .
                                    $curatore['comune'] . "<br><br>";
                            $old_key_tipo = '';
                            $msg .= "<br>Documenti<br>";
                            $i = 0;
                            foreach ($resultRest['DOCUMENTI'] as $key_tipo => $TipoDocumento) {
                                foreach ($TipoDocumento as $key => $documento) {
                                    if ($documento['file'] != '') {
                                        if ($old_key_tipo != $key_tipo) {
                                            //$msg .= "<br>" . $key_tipo . "<br>";
                                            $old_key_tipo = $key_tipo;
                                        }
                                        $i++;
                                        $msg .= '<a href = "' . $documento['file'] . '" target = "_blank">' . sprintf("%03d", $i) . "-" . $key_tipo . '</a><br>';
                                    }
                                }
                            }
                            Out::msgInfo($_POST[$this->nameForm . '_PROGES']['GESCODPROC'], $msg);
                            //Out::msgInfo($_POST[$this->nameForm . '_PROGES']['GESCODPROC'], print_r($resultRest,true));
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Ricerca Responsabile", "", "Responsabile");
                        break;
                    case $this->nameForm . '_Assegnatario_butt':
                        $msgDetail = "La Pratica sarà assegnata al soggetto scelto con un passo di gestione,<br>oppure di riassegnazione da prendere in carico.";
                        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Soggetto a cui assegnare la pratica", " WHERE NOMABILITAASS = 1 ", "Assegnatario", false, null, $msgDetail, true);
                        break;
                    case $this->nameForm . '_TipoOperazione_butt':
                        $ananom_rec = $this->praLib->GetAnanom($this->rowidDipendente, "rowid");
                        $where = " WHERE CLTOPE = '" . praFunzionePassi::FUN_GEST_ASS . "' OR CLTOPE = '" . praFunzionePassi::FUN_GEST_GEN . "'";
                        $msgDetail = "Scegliere tra i tipi passo disponibili, quello da abbinare all'utente<br><b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "</b>";
                        praRic::praRicPraclt($this->nameForm, "", "AssegnaTipoPasso", $where, $msgDetail, true);
                        break;
                    case $this->nameForm . '_Segnatura_butt':
                    case $this->nameForm . '_AnnoProtocollo_butt':
                    case $this->nameForm . '_DataProtocollo_butt':
                        Out::hide($this->nameForm . '_divMittDest');
                        $proObject = proWsClientFactory::getInstance();
                        if (!$proObject) {
                            Out::msgStop("Importazione pratica da protocollo", "Errore inizializzazione driver protocollo");
                            break;
                        }

                        /*
                         * Creazione array param
                         */
                        $param = array(
                            "NumeroProtocollo" => $_POST[$this->nameForm . "_NumeroProtocollo"],
                            "AnnoProtocollo" => $_POST[$this->nameForm . "_AnnoProtocollo"],
                            "DataProtocollo" => $_POST[$this->nameForm . "_DataProtocollo"],
                            "TipoProtocollo" => $_POST[$this->nameForm . "_TipoProtocollo"],
                            "Docnumber" => $_POST[$this->nameForm . "_IdDocumento"],
                            "Segnatura" => $_POST[$this->nameForm . "_Segnatura"],
                        );

                        /*
                         * Validazione array param
                         */
                        $msgErr = $this->praLib->validatorFields($proObject->getClientType(), $param);
                        if ($msgErr) {
                            Out::msgStop("Attenzione", $msgErr);
                            break;
                        }

                        $ret = $proObject->LeggiProtocollo($param);
                        if ($ret['Status'] == 0) {
                            $dati = $ret['RetValue']['Dati'];
                            //
                            $this->mittDest = array();
                            foreach ($ret['RetValue']['DatiProtocollo']['MittentiDestinatari'] as $key => $anagrafica_rec) {
                                $basLib = new basLib();
                                if ($anagrafica_rec['CapComuneDiResidenza']) {
                                    $comuni_rec = $basLib->getComuni($anagrafica_rec['CapComuneDiResidenza'], "coavpo");
                                }
                                if (!$comuni_rec) {
                                    $comuni_rec = $basLib->getComuni($anagrafica_rec['DescrizioneComuneDiResidenza']);
                                }
                                if ($anagrafica_rec['CapComuneDiNascita']) {
                                    $comuni_recNascita = $basLib->getComuni($anagrafica_rec['CapComuneDiNascita'], "coavpo");
                                }
                                if (!$comuni_recNascita) {
                                    $comuni_recNascita = $basLib->getComuni($anagrafica_rec['DescrizioneComuneDiNascita']);
                                }
                                $anades_rec['DESRUO'] = $anagrafica_rec['Ruolo'];
                                $anades_rec['DESRAGSOC'] = $anagrafica_rec['RagioneSociale'];
                                $anades_rec['DESFISGIU'] = $anagrafica_rec['FormaGiuridica'];
                                $anades_rec['DESNOM'] = $anagrafica_rec['Denominazione'];
                                $anades_rec['DESNOME'] = $anagrafica_rec['Nome'];
                                $anades_rec['DESCOGNOME'] = $anagrafica_rec['Cognome'];
                                $anades_rec['DESCOD'] = $anagrafica_rec['IdSoggetto'];
                                $anades_rec['DESFIS'] = $anagrafica_rec['CodiceFiscale'];
                                $anades_rec['DESSESSO'] = $anagrafica_rec['Sesso'];
                                $anades_rec['DESNASCIT'] = $anagrafica_rec['DescrizioneComuneDiNascita'];
                                $anades_rec['DESNASPROV'] = $comuni_recNascita['PROVIN'];
                                $anades_rec['DESNASNAZ'] = $anagrafica_rec['DescrizioneNazionalita'];
                                $anades_rec['DESNASDAT'] = $anagrafica_rec['DataDiNascita'];
                                $anades_rec['DESEMA'] = $anagrafica_rec['Email'];
                                $anades_rec['DESTEL'] = $anagrafica_rec['Telefono'];
                                $anades_rec['DESIND'] = $anagrafica_rec['Indirizzo'];
                                $anades_rec['DESCIV'] = $anagrafica_rec['Civico'];
                                $anades_rec['DESCAP'] = $anagrafica_rec['CapComuneDiResidenza'];
                                $anades_rec['DESCIT'] = $anagrafica_rec['DescrizioneComuneDiResidenza'];
                                $anades_rec['DESPRO'] = $comuni_rec['PROVIN'] ? $comuni_rec['PROVIN'] : $anagrafica_rec['ProvComuneDiResidenza'];
                                $anades_rec['DESNAZ'] = $anagrafica_rec['Nazionalita'];
                                $anades_rec['DESNATLEGALE'] = $anagrafica_rec['NaturaGiuridica'];
                                if ($key == 0) {
                                    $this->anades_rec = $anades_rec;
                                    Out::valori($anades_rec, $this->nameForm . "_ANADES");
                                } else {
                                    $this->mittDest[] = $anades_rec;
                                }
                            }

                            if (count($this->mittDest) > 1) {
                                Out::show($this->nameForm . '_divMittDest');
                                $this->CaricaGriglia($this->nameForm . "_gridMittDest", $this->mittDest);
                            }
                        } else {
                            Out::msgStop("Errore!!!", $ret['Message']);
                        }

                        break;

//                    case 'close-portlet':
//                        $this->returnToParent();
//                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'PRENDI_DA_ANAGRAFE':
                        $soggetto = $_POST['msgData'];
                        $this->AggiungiDaAnagrafe($soggetto);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANADES[DESCOD]':
                        if ($_POST[$this->nameForm . '_ANADES']["DESCOD"]) {
                            $codice = str_pad($_POST[$this->nameForm . '_ANADES']["DESCOD"], 6, "0", STR_PAD_LEFT);
                            $Filent_Rec = $this->praLib->GetFilent(18);
                            if ($Filent_Rec['FILVAL'] == 1) {
                                $this->DecodAnamedComP($codice);
                            } else {
                                $this->decodAnades($codice, "", "codiceSogg");
                            }
                        } else {
                            $this->SvuotaCampiAnades();
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESNOM]':
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $anamed_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), "SELECT * FROM ANAMED WHERE MEDNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_ANADES']["DESNOM"]) . "%' AND MEDANN=0", false);
                            $this->DecodAnamedComP($anamed_rec['MEDCOD']);
                        } else {
                            $anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_ANADES']["DESNOM"]) . "%'", false);
                            $this->decodAnades($anades_rec['ROWID'], "", "rowid");
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        $comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE ='"
                                        . addslashes($_POST[$this->nameForm . '_ANADES']['DESCIT']) . "'", false);
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_ANADES[DESCAP]', $comuni_rec['COAVPO']);
                            Out::valore($this->nameForm . '_ANADES[DESPRO]', $comuni_rec['PROVIN']);
                        }
                        break;
                    case $this->nameForm . '_PROGES[GESRES]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESRES'];
                        $Codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->DecodAnanom($Codice, "Responsabile", 'codice');
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGES[GESPRO]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->decodAnapra($codice);
                        $_POST[$this->nameForm . '_PROGES']['GESPRO'] = $codice;
                        $iteevt_tab = $this->praLib->GetIteevt($codice, 'codice', true);
                        $quanti = count($iteevt_tab);
                        if ($quanti == 1) {
                            $this->decodIteevt($codice, $iteevt_tab[0]['IEVCOD']);
                            Out::setFocus('', $this->nameForm . '_PROGES[GESRES]');
                        } else {
                            Out::valore($this->nameForm . '_PROGES[GESEVE]', '');
                            Out::valore($this->nameForm . '_Desc_evento', '');
                        }

                        break;
                    case $this->nameForm . '_PROGES[GESWFPRO]':
                        $codice = $_POST[$this->nameForm . '_PROGES']['GESWFPRO'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        $this->decodAnapraWf($codice);
                        break;
                    case $this->nameForm . '_PROGES[GESEVE]': // più eventi per procedimento  $quanti!= 1 
                        //Out::msgInfo("codice",$_POST[$this->nameForm . '_PROGES']['GESPRO']);
                        $evento = $_POST[$this->nameForm . '_PROGES']['GESEVE'];
                        $evento = str_repeat("0", 6 - strlen(trim($evento))) . trim($evento);
                        //Out::msgInfo("evento", $evento);
                        $this->decodIteevt($_POST[$this->nameForm . '_PROGES']['GESPRO'], $evento);
                        Out::setFocus('', $this->nameForm . '_PROGES[GESRES]');
                        break;
                }

                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANADES[DESNOM]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE " . $this->proLib->getPROTDB()->strUpper('MEDNOM') . " LIKE '%"
                                    . addslashes(strtoupper(itaSuggest::getQuery())) . "%' AND MEDANN=0");
                            foreach ($anamed_tab as $anamed_rec) {
                                $indirizzo = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCIT'] . " " . $anamed_rec['MEDPRO'];
                                if (trim($indirizzo) != '') {
                                    $indirizzo = " - " . $indirizzo;
                                } else {
                                    $indirizzo = '';
                                }
                                itaSuggest::addSuggest($anamed_rec['MEDNOM'], array(
                                    $this->nameForm . "_ANADES[DESCOD]" => $anamed_rec['MEDCOD'],
                                    $this->nameForm . "_ANADES[DESFIS]" => $anamed_rec['MEDFIS'],
                                    $this->nameForm . "_ANADES[DESIND]" => $anamed_rec['MEDIND'],
                                    $this->nameForm . "_ANADES[DESCIT]" => $anamed_rec['MEDCIT'],
                                    $this->nameForm . "_ANADES[DESPRO]" => $anamed_rec['MEDPRO'],
                                    $this->nameForm . "_ANADES[DESCAP]" => $anamed_rec['MEDCAP'],
                                    $this->nameForm . "_ANADES[DESEMA]" => $anamed_rec['MEDEMA']
                                ));
                            }
                        } else {
                            $anades_tab = $this->praLib->getGenericTab("SELECT * FROM ANADES WHERE " . $this->proLib->getPROTDB()->strUpper('DESNOM') . " LIKE '%"
                                    . addslashes(strtoupper(itaSuggest::getQuery())) . "%'");
                            foreach ($anades_tab as $anades_rec) {
                                $indirizzo = $anades_rec['DESIND'] . " " . $anades_rec['DESCIT'] . " " . $anades_rec['DESPRO'];
                                if (trim($indirizzo) != '') {
                                    $indirizzo = " - " . $indirizzo;
                                } else {
                                    $indirizzo = '';
                                }
                                itaSuggest::addSuggest($anades_rec['DESNOM'] . "|" . $anades_rec['DESFIS'] . "|" . $anades_rec['DESEMA'] . "|"
                                        . $this->nameForm . "_ANADES[DESCOD]|" . $anades_rec['DESCOD'] . "|"
                                        . $this->nameForm . "_ANADES[DESFIS]|" . $anades_rec['DESFIS'] . "|"
                                        . $this->nameForm . "_ANADES[DESIND]|" . $anades_rec['DESIND'] . "|"
                                        . $this->nameForm . "_ANADES[DESCIT]|" . $anades_rec['DESCIT'] . "|"
                                        . $this->nameForm . "_ANADES[DESPRO]|" . $anades_rec['DESPRO'] . "|"
                                        . $this->nameForm . "_ANADES[DESCAP]|" . $anades_rec['DESCAP'] . "|"
                                        . $this->nameForm . "_ANADES[DESEMA]|" . $anades_rec['DESEMA']);
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $comuni_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($comuni_tab as $comuni_rec) {
                            ;
                            itaSuggest::addSuggest($comuni_rec['COMUNE']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnAnades':
                $this->decodAnades($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '2':
                        $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                        break;
                }
                break;
//            case 'returnAnapra':
//
//                $this->decodAnapra($_POST['rowData']['ID_ANAPRA'], 'rowid');
//                $this->decodIteevt('', $_POST['rowData']['ID_ITEEVT'], "rowid");
//                Out::setFocus('', $this->nameForm . '_PROGES[GESRES]');
//
//                break;
            case 'returnAnapra':
                switch ($_POST['retid']) {
                    case $this->nameForm . '_PROGES[GESPRO]':
                        $this->decodAnapra($_POST['rowData']['ID_ANAPRA'], 'rowid');
                        $this->decodIteevt('', $_POST['rowData']['ID_ITEEVT'], "rowid");
                        Out::setFocus('', $this->nameForm . '_PROGES[GESRES]');
                        break;
                    case $this->nameForm . '_PROGES[GESWFPRO]':
                        $this->decodAnaprawf($_POST['rowData']['ID_ANAPRA'], 'rowid');
                        Out::setFocus('', $this->nameForm . '_PROGES[GESRES]');
                        break;
                }
                break;
            case 'returnRicIPA':
                //
                //Estraggo il numero civico dall'indirizzo
                //
                $lastSpacePos = strrpos($_POST['PROIND'], " ");
                $civico = substr($_POST['PROIND'], $lastSpacePos + 1);
                $indirizzo = substr($_POST['PROIND'], 0, $lastSpacePos);
                //
                Out::valore($this->nameForm . '_ANADES[DESNOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_ANADES[DESIND]', $indirizzo);
                Out::valore($this->nameForm . '_ANADES[DESCIV]', $civico);
                Out::valore($this->nameForm . '_ANADES[DESCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_ANADES[DESPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_ANADES[DESCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_ANADES[DESPEC]', $_POST['PROMAIL']);
                break;
            case 'returnIteevt':
                $this->decodIteevt('', $_POST['retKey'], "rowid");
//                $iteevt_rec = $this->praLib->GetIteevt($_POST['retKey'], "rowid");
//                if ($iteevt_rec) {
//                    $anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD']);
//                }
//                Out::valore($this->nameForm . '_ITEEVT[ROWID]', $iteevt_rec['ROWID']);
//                Out::valore($this->nameForm . '_PROGES[GESEVE]', $iteevt_rec['IEVCOD']);
//                Out::valore($this->nameForm . '_Desc_evento', $anaeventi_rec['EVTDESCR']);
                break;
            case 'returnUnires':
                $this->DecodAnanom($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case "returnPraclt":
                $praclt_rec = $this->praLib->GetPraclt($_POST['retKey'], "rowid");
                $this->rowidTipoPasso = $praclt_rec['ROWID'];
                Out::valore($this->nameForm . '_TipoOperazione', $praclt_rec['CLTCOD']);
                Out::valore($this->nameForm . '_Desc_tipoOperazione', $praclt_rec['CLTDES']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_datiMail');
        App::$utente->removeKey($this->nameForm . '_resultRest');
        App::$utente->removeKey($this->nameForm . '_rowidDipendente');
        App::$utente->removeKey($this->nameForm . '_rowidTipoPasso');
        App::$utente->removeKey($this->nameForm . '_mittDest');
        App::$utente->removeKey($this->nameForm . '_anades_rec');
        $this->close = true;
    }

    public function returnToParent($carica = true) {
        if ($carica == true) {
            $gespro = $_POST[$this->nameForm . '_PROGES']['GESPRO'];
            $geswfpro = $_POST[$this->nameForm . '_PROGES']['GESWFPRO'];
            $gescodproc = $_POST[$this->nameForm . '_PROGES']['GESCODPROC'];
            $rowidEvento = $_POST[$this->nameForm . '_ITEEVT']['ROWID'];
            $descod = $_POST[$this->nameForm . '_ANADES']['DESCOD'];
            $desnom = $_POST[$this->nameForm . '_ANADES']['DESNOM'];
            $desfis = $_POST[$this->nameForm . '_ANADES']['DESFIS'];
            $desema = $_POST[$this->nameForm . '_ANADES']['DESEMA'];
            $desind = $_POST[$this->nameForm . '_ANADES']['DESIND'];
            $desciv = $_POST[$this->nameForm . '_ANADES']['DESCIV'];
            $descit = $_POST[$this->nameForm . '_ANADES']['DESCIT'];
            $descap = $_POST[$this->nameForm . '_ANADES']['DESCAP'];
            $despro = $_POST[$this->nameForm . '_ANADES']['DESPRO'];
            $gesres = $_POST[$this->nameForm . '_PROGES']['GESRES'];
            $gesdre = $_POST[$this->nameForm . '_PROGES']['GESDRE'];
            $assegnatario = $_POST[$this->nameForm . '_Assegnatario'];
            $tipoPassoAss = $_POST[$this->nameForm . '_TipoOperazione'];
            $noteAss = $_POST[$this->nameForm . '_Note'];
            $dataProt = $_POST[$this->nameForm . '_DataProtocollo'];
            $annoProt = $_POST[$this->nameForm . '_AnnoProtocollo'];
            if ($annoProt == "") {
                $annoProt = substr($dataProt, 0, 4);
            }
            $protocollo = $annoProt . $_POST[$this->nameForm . '_NumeroProtocollo'];
            $tipoProt = $_POST[$this->nameForm . '_TipoProtocollo'];
            $idDocumento = $_POST[$this->nameForm . '_IdDocumento'];
            $segnatura = $_POST[$this->nameForm . '_Segnatura'];
            $_POST = array();
            $_POST['datiMail'] = $this->datiMail;
            $_POST['datiMail']['Dati']['PROGES']['GESPRO'] = $gespro;
            $_POST['datiMail']['Dati']['PROGES']['GESWFPRO'] = $geswfpro;
            $_POST['datiMail']['Dati']['PROGES']['GESCODPROC'] = $gescodproc;
            $_POST['datiMail']['Dati']['PROGES']['GESRES'] = $gesres;
            $_POST['datiMail']['Dati']['PROGES']['GESDRE'] = $gesdre;
            $_POST['datiMail']['Dati']['PROGES']['GESNPR'] = $protocollo;
            $_POST['datiMail']['Dati']['PROGES']['GESPAR'] = $tipoProt;
            //
            $_POST['datiMail']['Dati']['ITEEVT']['ROWID'] = $rowidEvento;
            //
            if ($this->anades_rec) {
                $_POST['datiMail']['Dati']['ANADES'] = $this->anades_rec;
            }

            $_POST['datiMail']['Dati']['ANADES']['DESCOD'] = $descod;
            $_POST['datiMail']['Dati']['ANADES']['DESNOM'] = $desnom;
            $_POST['datiMail']['Dati']['ANADES']['DESFIS'] = $desfis;
            $_POST['datiMail']['Dati']['ANADES']['DESEMA'] = $desema;
            $_POST['datiMail']['Dati']['ANADES']['DESIND'] = $desind;
            $_POST['datiMail']['Dati']['ANADES']['DESCIV'] = $desciv;
            $_POST['datiMail']['Dati']['ANADES']['DESCIT'] = $descit;
            $_POST['datiMail']['Dati']['ANADES']['DESCAP'] = $descap;
            $_POST['datiMail']['Dati']['ANADES']['DESPRO'] = $despro;
            if ($_POST['datiMail']['Dati']['ANADES']['DESRUO'] == "") {
                $_POST['datiMail']['Dati']['ANADES']['DESRUO'] = praRuolo::getSystemSubjectCode("ESIBENTE");
            }

            if ($protocollo || $idDocumento || $segnatura) {
                $_POST['datiMail']['Dati']['MITTDEST'] = $this->mittDest;
            }

            $_POST['datiMail']['Dati']['EscludiPassiFO'] = true;
            $_POST['datiMail']['Dati']['RESULTREST'] = $gescodproc;
            if ($assegnatario && $tipoPassoAss) {
                $_POST['datiMail']['Dati']['Assegnazione']['ASSEGNATARIO'] = $assegnatario;
                $_POST['datiMail']['Dati']['Assegnazione']['TIPOPASSO'] = $tipoPassoAss;
                $_POST['datiMail']['Dati']['Assegnazione']['NOTE'] = $noteAss;
            }
            if ($this->datiMail) {
                $_POST['datiMail']['Dati']['provenienza'] = "daCaricaMail";
            } else if ($protocollo) {
                $_POST['datiMail']['Dati']['provenienza'] = "daProtocollo";
            } else if ($idDocumento) {
                $_POST['datiMail']['Dati']['provenienza'] = "daProtocollo";
            } else if ($segnatura) {
                $_POST['datiMail']['Dati']['provenienza'] = "daProtocollo";
            } else {
                $_POST['datiMail']['Dati']['provenienza'] = "daAnagrafica";
            }
            //
            $_POST['datiMail']['Dati']['idDocumento'] = $idDocumento;
            $_POST['datiMail']['Dati']['segnatura'] = $segnatura;
            $_POST['datiMail']['Dati']['dataProtocollo'] = $dataProt;
            //
            $_POST['carica'] = $carica;
            $objModel = itaModel::getInstance($this->returnModel);
            $objModel->setEvent($this->returnEvent);
            $objModel->parseEvent();
        }
        $this->close();
    }

    private function SvuotaCampiAnades() {
        Out::valore($this->nameForm . "_ANADES[DESCOD]", "");
        Out::valore($this->nameForm . "_ANADES[DESNOM]", "");
        Out::valore($this->nameForm . "_ANADES[DESFIS]", "");
        Out::valore($this->nameForm . "_ANADES[DESEMA]", "");
        Out::valore($this->nameForm . "_ANADES[DESIND]", "");
        Out::valore($this->nameForm . "_ANADES[DESCIT]", "");
        Out::valore($this->nameForm . "_ANADES[DESCAP]", "");
        Out::valore($this->nameForm . "_ANADES[DESPRO]", "");
        Out::valore($this->nameForm . "_ANADES[DESCIV]", "");
        Out::valore($this->nameForm . "_PROGES[GESPRO]", "");
    }

    private function decodAnades($Codice, $retid, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        Out::valore($this->nameForm . "_ANADES[DESCOD]", $anades_rec['DESCOD']);
        Out::valore($this->nameForm . "_ANADES[DESNOM]", $anades_rec['DESNOM']);
        Out::valore($this->nameForm . "_ANADES[DESFIS]", $anades_rec['DESFIS']);
        Out::valore($this->nameForm . "_ANADES[DESEMA]", $anades_rec['DESEMA']);
        Out::valore($this->nameForm . "_ANADES[DESIND]", $anades_rec['DESIND']);
        Out::valore($this->nameForm . "_ANADES[DESCIT]", $anades_rec['DESCIT']);
        Out::valore($this->nameForm . "_ANADES[DESCAP]", $anades_rec['DESCAP']);
        Out::valore($this->nameForm . "_ANADES[DESPRO]", $anades_rec['DESPRO']);
        Out::valore($this->nameForm . "_ANADES[DESCIV]", $anades_rec['DESCIV']);
    }

    function DecodAnamedComP($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            //
            //Estraggo il numero civico dall'indirizzo
            //
            $lastSpacePos = strrpos($anamed_rec['MEDIND'], " ");
            $civico = substr($anamed_rec['MEDIND'], $lastSpacePos + 1);
            $indirizzo = $anamed_rec['MEDIND'];
            if (is_numeric($civico)) {
                $indirizzo = substr($anamed_rec['MEDIND'], 0, $lastSpacePos);
            } else {
                $civico = "";
            }
            Out::valore($this->nameForm . "_ANADES[DESCOD]", $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . "_ANADES[DESNOM]", $anamed_rec['MEDNOM']);
            Out::valore($this->nameForm . "_ANADES[DESFIS]", $anamed_rec['MEDFIS']);
            Out::valore($this->nameForm . "_ANADES[DESEMA]", $anamed_rec['MEDEMA']);
            Out::valore($this->nameForm . "_ANADES[DESIND]", $indirizzo);
            Out::valore($this->nameForm . "_ANADES[DESCIV]", $civico);
            Out::valore($this->nameForm . "_ANADES[DESCIT]", $anamed_rec['MEDCIT']);
            Out::valore($this->nameForm . "_ANADES[DESCAP]", $anamed_rec['MEDCAP']);
            Out::valore($this->nameForm . "_ANADES[DESPRO]", $anamed_rec['MEDPRO']);
        }
        return $anamed_rec;
    }

    private function creaCombo() {
        Out::select($this->nameForm . '_CaricaPassi', 1, false, "1", "Si");
        Out::select($this->nameForm . '_CaricaPassi', 1, true, "0", "No");
        //
        Out::select($this->nameForm . '_TipoProtocollo', 1, "", "1", "");
        Out::select($this->nameForm . '_TipoProtocollo', 1, "A", "0", "Arrivo     ");
        Out::select($this->nameForm . '_TipoProtocollo', 1, "P", "0", "Partenza   ");
        Out::select($this->nameForm . '_TipoProtocollo', 1, "C", "0", "Interno   ");
    }

    private function decodAnapra($codice, $tipo = 'codice') {
        $anapra_rec = $this->praLib->GetAnapra($codice, $tipo);

        Out::valore($this->nameForm . "_PROGES[GESPRO]", $anapra_rec['PRANUM']);
        Out::valore($this->nameForm . '_Desc_proc2', $anapra_rec['PRADES__1']);
    }

    private function decodAnapraWf($codice, $tipo = 'codice') {
        $anapra_rec = $this->praLib->GetAnapra($codice, $tipo);
        Out::valore($this->nameForm . "_PROGES[GESWFPRO]", $anapra_rec['PRANUM']);
        Out::valore($this->nameForm . '_Desc_wfproc2', $anapra_rec['PRADES__1']);
    }

    private function cercaCodiceProcedura($oggetto) {
        $result_preg = preg_match_all('/\[([^]]*)\]/', $oggetto, $matches);
        if ($result_preg === false) {
            // Non trovato
        } else {
            return $matches[1][0];
        }
    }

    private function callGobidRest($procedura) {
        return $this->praGobid->output($procedura);
    }

    private function AggiungiDaAnagrafe($soggetto) {

        Out::valore($this->nameForm . '_ANADES[DESCOD]', $soggetto['CODICEUNIVOCO']);
        Out::valore($this->nameForm . '_ANADES[DESNOM]', $soggetto['NOME'] . " " . $soggetto['COGNOME']);
        Out::valore($this->nameForm . '_ANADES[DESFIS]', $soggetto['CODICEFISCALE']);
        Out::valore($this->nameForm . '_ANADES[DESCIT]', $soggetto['RESIDENZA']);
        Out::valore($this->nameForm . '_ANADES[DESPRO]', $soggetto['PROVINCIA']);
        Out::valore($this->nameForm . '_ANADES[DESIND]', $soggetto['INDIRIZZO']);
        Out::valore($this->nameForm . '_ANADES[DESCIV]', $soggetto['CIVICO']);
        Out::valore($this->nameForm . '_ANADES[DESCAP]', $soggetto['CAP']);
    }

    function DecodAnanom($Codice, $retid, $tipoRic = 'codice') {
        $ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        switch ($retid) {
            case "Responsabile":
                Out::valore($this->nameForm . '_PROGES[GESRES]', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case "Assegnatario":
                Out::valore($this->nameForm . '_Assegnatario', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_Desc_assegnatario', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                $this->rowidDipendente = $ananom_rec['ROWID'];
                break;
        }

        return $ananom_rec;
    }

    function decodIteevt($proc, $codice, $tipoRic = "codice") {
        if ($tipoRic == "codice") {
            $iteevt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $proc . "' AND IEVCOD = '$codice'", false);
        } elseif ($tipoRic == "rowid") {
            $iteevt_rec = $this->praLib->GetIteevt($codice, $tipoRic);
        }
        if ($iteevt_rec) {
            $anaeventi_rec = $this->praLib->GetAnaeventi($iteevt_rec['IEVCOD']);
            Out::valore($this->nameForm . '_ITEEVT[ROWID]', $iteevt_rec['ROWID']);
            Out::valore($this->nameForm . '_PROGES[GESEVE]', $iteevt_rec['IEVCOD']);
            Out::valore($this->nameForm . '_Desc_evento', $anaeventi_rec['EVTDESCR']);
        } else {
            Out::valore($this->nameForm . '_ITEEVT[ROWID]', "");
            Out::valore($this->nameForm . '_PROGES[GESEVE]', "");
            Out::valore($this->nameForm . '_Desc_evento', "");
        }
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $caption = '') {
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
            $ita_grid01->setPageRows($_POST[$griglia]['gridParam']['rowNum']);
        } else {
            $this->page = $_POST['page'];
            $ita_grid01->setPageNum($_POST['page']);

            $ita_grid01->setPageRows(1000);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    function setDivSearchProt() {
        $proObject = proWsClientFactory::getInstance();
        if (!$proObject) {
            return false;
        }
        switch ($proObject->getClientType()) {
            case proWsClientHelper::CLIENT_PALEO4:
            case proWsClientHelper::CLIENT_PALEO41:
                $div = "divProtocolloIdSegn";
                break;
            case proWsClientHelper::CLIENT_ITALPROT:
            case proWsClientHelper::CLIENT_IRIDE:
            case proWsClientHelper::CLIENT_JIRIDE:
                $div = "divProtocollo";
                Out::hide($this->nameForm . "_TipoProtocollo_field");
                Out::hide($this->nameForm . "_DataProtocollo_field");
                if ($proObject->getClientType() == proWsClientHelper::CLIENT_ITALPROT) {
                    Out::show($this->nameForm . "_TipoProtocollo_field");
                }
                break;
            case proWsClientHelper::CLIENT_CIVILIANEXT:
                $div = "divProtocollo";
                Out::hide($this->nameForm . "_TipoProtocollo_field");
                Out::hide($this->nameForm . "_AnnoProtocollo_field");
                break;
        }
        return $div;
    }

}

?>
