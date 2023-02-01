<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    27.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/QXml/QXml.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proEtic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicPratiche.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibRicevute.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proProtocollo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function proArri() {
    $proArri = new proArri();
    $proArri->parseEvent();
    return;
}

class proArri extends itaModel {

    public $PROT_DB;
    public $nameForm;
    public $gridDestinatari = "proArri_gridDestinatari";
    public $gridAltriDestinatari = "proArri_gridAltriDestinatari";
    public $gridAllegati = "proArri_gridAllegati";
    public $gridNote = "proArri_gridNote";
    public $divTestaUte = "proArri_divTestaUte";
    public $gridFascicoli = "proArri_gridFascicoli";
    public $workDate;
    public $workYear;
    public $proLib;
    public $proLibSdi;
    public $proLibMail;
    public $proLibAllegati;
    public $proLibFascicolo;
    public $proLibTitolario;
    public $proLibConservazione;
    public $accLib;
    public $proDestinatari = array();
    public $proAltriDestinatari = array();
    public $proArriDest = array();
    public $proArriUff = array();
    public $proArriAlle = array();
    public $tipoProt;
    public $annullato;
    public $proArriIndice;
    public $rowidAppoggio;
    public $varAppoggio;
    public $consultazione;
    public $fileDaPEC;
    public $disabledRec;
    public $Proric_parm;
    public $inviaConfermaMail;
    public $mittentiAggiuntivi;
    public $destMap;
    public $prouof = '';
    public $currDescod;
    public $emergenza;
    public $bloccoDaEmail;
    public $altriDestPerms;
    /* @var $this->noteManager proNoteManager */
    public $noteManager;
    public $datiPasso;
    public $anapro_record;
    public $proLibPratica;
    public $profilo;
    /* @var $this->currObjSdi proSdi.class.php  */
    public $currObjSdi;
    public $proLibGiornaliero;
    public $varMarcatura = array();
    public $datiUtiP7m = array();
    public $StatoToggle = array();
    public $returnData = array();
    public $datiFascicolazine = array();
    public $duplicaAllegati;
    public $ElencoFascicoli = array();
    public $FascicoloDiProvenienza;
    public $ProtocollaSimple = '';
    public $ProtAllegaDaFascicolo = '';
    public $DelegatoFirmatario = array();
    public $DatiPreProtocollazione = array();
    public $CtrDatiRichiestiProt = array();
    public $LockIdMail = array();
    public $proSubMittDest;
    public $proSubTrasmissioni;
    public $rowidAnaproDuplicatoSorgente;

    function __construct() {
        parent::__construct();
        $this->nameForm = "proArri";
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibFascicolo = new proLibFascicolo();
        $this->accLib = new accLib();
        $this->proLibMail = new proLibMail();
        $this->proLibPratica = new proLibPratica();
        $this->proLibSdi = new proLibSdi();
        $this->proLibGiornaliero = new proLibGiornaliero();
        $this->proLibTitolario = new proLibTitolario();
        $this->proLibConservazione = new proLibConservazione();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->workDate = date('Ymd');
        $this->workYear = date('Y', strtotime($this->workDate));
        $this->proDestinatari = App::$utente->getKey($this->nameForm . '_proDestinatari');
        $this->proAltriDestinatari = App::$utente->getKey($this->nameForm . '_proAltriDestinatari');
        $this->proArriDest = App::$utente->getKey($this->nameForm . '_proArriDest');
        $this->proArriUff = App::$utente->getKey($this->nameForm . '_proArriUff');
        $this->proArriAlle = App::$utente->getKey($this->nameForm . '_proArriAlle');
        $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
        $this->annullato = App::$utente->getKey($this->nameForm . '_annullato');
        $this->proArriIndice = App::$utente->getKey($this->nameForm . '_proArriIndice');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->varAppoggio = App::$utente->getKey($this->nameForm . '_varAppoggio');
        $this->consultazione = App::$utente->getKey($this->nameForm . '_consultazione');
        $this->fileDaPEC = App::$utente->getKey($this->nameForm . '_fileDaPEC');
        $this->disabledRec = App::$utente->getKey($this->nameForm . '_disabledRec');
        $this->Proric_parm = App::$utente->getKey($this->nameForm . '_Proric_parm');
        $this->inviaConfermaMail = App::$utente->getKey($this->nameForm . '_inviaConfermaMail');
        $this->mittentiAggiuntivi = App::$utente->getKey($this->nameForm . "_mittentiAggiuntivi");
        $this->destMap = App::$utente->getKey($this->nameForm . "_destMap");
        $this->prouof = App::$utente->getKey($this->nameForm . "_prouof");
        $this->currDescod = App::$utente->getKey($this->nameForm . "_currDescod");
        $this->emergenza = App::$utente->getKey($this->nameForm . "_emergenza");
        $this->bloccoDaEmail = App::$utente->getKey($this->nameForm . "_bloccoDaEmail");
        $this->altriDestPerms = App::$utente->getKey($this->nameForm . "_altriDestPerms");
        $this->noteManager = unserialize(App::$utente->getKey($this->nameForm . '_noteManager'));
        $this->datiPasso = App::$utente->getKey($this->nameForm . '_datiPasso');
        $this->anapro_record = App::$utente->getKey($this->nameForm . '_anapro_record');
        $this->profilo = App::$utente->getKey($this->nameForm . '_profilo');
        $this->currObjSdi = unserialize(App::$utente->getKey($this->nameForm . '_currObjSdi'));
        $this->varMarcatura = App::$utente->getKey($this->nameForm . '_varMarcatura');
        $this->datiUtiP7m = App::$utente->getKey($this->nameForm . '_datiUtiP7m');
        $this->StatoToggle = App::$utente->getKey($this->nameForm . '_StatoToggle');
        $this->returnData = App::$utente->getKey($this->nameForm . '_returnData');
        $this->datiFascicolazine = App::$utente->getKey($this->nameForm . '_datiFascicolazine');
        $this->duplicaAllegati = App::$utente->getKey($this->nameForm . '_duplicaAllegati');
        $this->ElencoFascicoli = App::$utente->getKey($this->nameForm . '_ElencoFascicoli');
        $this->FascicoloDiProvenienza = App::$utente->getKey($this->nameForm . '_FascicoloDiProvenienza');
        $this->ProtocollaSimple = App::$utente->getKey($this->nameForm . '_ProtocollaSimple');
        $this->ProtAllegaDaFascicolo = App::$utente->getKey($this->nameForm . '_ProtAllegaDaFascicolo');
        $this->DelegatoFirmatario = App::$utente->getKey($this->nameForm . '_DelegatoFirmatario');
        $this->DatiPreProtocollazione = App::$utente->getKey($this->nameForm . '_DatiPreProtocollazione');
        $this->CtrDatiRichiestiProt = App::$utente->getKey($this->nameForm . '_CtrDatiRichiestiProt');
        $this->LockIdMail = App::$utente->getKey($this->nameForm . '_LockIdMail');
        $this->proSubMittDest = App::$utente->getKey($this->nameForm . '_proSubMittDest');
        $this->proSubTrasmissioni = App::$utente->getKey($this->nameForm . '_proSubTrasmissioni');
        $this->rowidAnaproDuplicatoSorgente = App::$utente->getKey($this->nameForm . '_rowidAnaproDuplicatoSorgente');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proDestinatari', $this->proDestinatari);
            App::$utente->setKey($this->nameForm . '_proAltriDestinatari', $this->proAltriDestinatari);
            App::$utente->setKey($this->nameForm . '_proArriDest', $this->proArriDest);
            App::$utente->setKey($this->nameForm . '_proArriUff', $this->proArriUff);
            App::$utente->setKey($this->nameForm . '_proArriAlle', $this->proArriAlle);
            App::$utente->setKey($this->nameForm . '_tipoProt', $this->tipoProt);
            App::$utente->setKey($this->nameForm . '_annullato', $this->annullato);
            App::$utente->setKey($this->nameForm . '_proArriIndice', $this->proArriIndice);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_varAppoggio', $this->varAppoggio);
            App::$utente->setKey($this->nameForm . '_consultazione', $this->consultazione);
            App::$utente->setKey($this->nameForm . '_fileDaPEC', $this->fileDaPEC);
            App::$utente->setKey($this->nameForm . '_disabledRec', $this->disabledRec);
            App::$utente->setKey($this->nameForm . '_Proric_parm', $this->Proric_parm);
            App::$utente->setKey($this->nameForm . '_inviaConfermaMail', $this->inviaConfermaMail);
            App::$utente->setKey($this->nameForm . "_mittentiAggiuntivi", $this->mittentiAggiuntivi);
            App::$utente->setKey($this->nameForm . "_destMap", $this->destMap);
            App::$utente->setKey($this->nameForm . "_prouof", $this->prouof);
            App::$utente->setKey($this->nameForm . "_currDescod", $this->currDescod);
            App::$utente->setKey($this->nameForm . "_emergenza", $this->emergenza);
            App::$utente->setKey($this->nameForm . "_bloccoDaEmail", $this->bloccoDaEmail);
            App::$utente->setKey($this->nameForm . "_altriDestPerms", $this->altriDestPerms);
            App::$utente->setKey($this->nameForm . '_noteManager', serialize($this->noteManager));
            App::$utente->setKey($this->nameForm . '_datiPasso', $this->datiPasso);
            App::$utente->setKey($this->nameForm . '_anapro_record', $this->anapro_record);
            App::$utente->setKey($this->nameForm . '_profilo', $this->profilo);
            App::$utente->setKey($this->nameForm . '_currObjSdi', serialize($this->currObjSdi));
            App::$utente->setKey($this->nameForm . '_varMarcatura', $this->varMarcatura);
            App::$utente->setKey($this->nameForm . '_datiUtiP7m', $this->datiUtiP7m);
            App::$utente->setKey($this->nameForm . '_StatoToggle', $this->StatoToggle);
            App::$utente->setKey($this->nameForm . '_returnData', $this->returnData);
            App::$utente->setKey($this->nameForm . '_datiFascicolazine', $this->datiFascicolazine);
            App::$utente->setKey($this->nameForm . '_duplicaAllegati', $this->duplicaAllegati);
            App::$utente->setKey($this->nameForm . '_ElencoFascicoli', $this->ElencoFascicoli);
            App::$utente->setKey($this->nameForm . '_ElementiDiRiscontro', $this->ElementiDiRiscontro);
            App::$utente->setKey($this->nameForm . '_FascicoloDiProvenienza', $this->FascicoloDiProvenienza);
            App::$utente->setKey($this->nameForm . '_ProtocollaSimple', $this->ProtocollaSimple);
            App::$utente->setKey($this->nameForm . '_ProtAllegaDaFascicolo', $this->ProtAllegaDaFascicolo);
            App::$utente->setKey($this->nameForm . '_DelegatoFirmatario', $this->DelegatoFirmatario);
            App::$utente->setKey($this->nameForm . '_DatiPreProtocollazione', $this->DatiPreProtocollazione);
            App::$utente->setKey($this->nameForm . '_CtrDatiRichiestiProt', $this->CtrDatiRichiestiProt);
            App::$utente->setKey($this->nameForm . '_LockIdMail', $this->LockIdMail);
            App::$utente->setKey($this->nameForm . '_proSubMittDest', $this->proSubMittDest);
            App::$utente->setKey($this->nameForm . '_proSubTrasmissioni', $this->proSubTrasmissioni);
            App::$utente->setKey($this->nameForm . '_rowidAnaproDuplicatoSorgente', $this->rowidAnaproDuplicatoSorgente);
        }
    }

    public function getReturnData() {
        return $this->returnData;
    }

    public function setReturnData($returnData) {
        $this->returnData = $returnData;
    }

    public function setDatiUtiP7m($datiUtiP7m) {
        $this->datiUtiP7m = $datiUtiP7m;
    }

    function setFascicoloDiProvenienza($FascicoloDiProvenienza) {
        $this->FascicoloDiProvenienza = $FascicoloDiProvenienza;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            $this->Nascondi();
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($_POST['event']) {
            case 'openform':
                Out::attributo($this->gridAltriDestinatari . '_exportTableToExcel', 'title', 0, 'Importa CSV');
                Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                Out::css($this->nameForm . '_Oggetto_field', 'width', '85%');
                Out::css($this->nameForm . '_TitolarioDecod_field', 'flex', '1');
                Out::css('gview_' . $this->gridAllegati . ' .ui-jqgrid-bdiv', 'overflow-x', 'hidden');
                itaLib::clearAppsTempPath();
                $this->proDestinatari = array();
                $this->proArriAlle = array();
                $this->Proric_parm = array();
                $this->fileDaPEC = array();
                $this->destMap = array();
                $this->prouof = '';
                $this->inviaConfermaMail = '';
                $this->rowidAppoggio = '';
                $this->datiPasso = '';
                $this->anapro_record = '';
                $this->CreaCombo();
                $this->StatoToggle = array(); // Nuova Funzione
                $this->proLib->caricaElementiUtenteUfficio('proUfficioUtente', $this->divTestaUte, $this->nameForm);
                $anaent_32 = $this->proLib->GetAnaent('32');
                if ($anaent_32['ENTDE2'] == 1) {
                    Out::addClass($this->nameForm . '_ANAPRO[PROCAT]', "required");
                } else {
                    Out::delClass($this->nameForm . '_ANAPRO[PROCAT]', "required");
                }
                Out::hide($this->nameForm . '_altriDest');
                Out::hide($this->nameForm . "_ANAPRO[PROSECURE]_field");
                // ita-edit-uppercase ***
                $anaent_37 = $this->proLib->GetAnaent('37');
                if ($anaent_37['ENTDE3'] == 1) {
                    Out::addClass($this->nameForm . '_Oggetto', "ita-edit-uppercase");
                }
                /*
                 * Abilitazione o blocco del caricamento semplificato destinatari
                 */
                $anaent_40 = $this->proLib->GetAnaent('40');
                if (!$anaent_40['ENTDE3']) {
                    Out::removeElement($this->nameForm . "_DestSimple");
                }
                // Nascondo per futura gestione.
                Out::removeElement($this->nameForm . "_divCodFiscale");


                $this->profilo = proSoggetto::getProfileFromIdUtente();

                if (!$this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) {
                    Out::removeElement($this->nameForm . '_Annulla');
                }

                $anaent_29 = $this->proLib->GetAnaent('29');
                if ($anaent_29['ENTDE4'] == 1) {
                    Out::removeElement($this->nameForm . '_Mail');
                    Out::removeElement($this->nameForm . '_MailMittenti');
                    Out::removeElement($this->nameForm . '_Trasmissioni');
                    Out::removeElement($this->nameForm . '_UsaModello');
                    Out::removeElement($this->nameForm . '_ComForm');
                    Out::removeElement($this->nameForm . '_FileLocale');
                    Out::removeElement($this->nameForm . '_Scanner');
                    Out::removeElement($this->nameForm . '_ScannerShared');
                    Out::removeElement($this->nameForm . '_DaP7m');
                    Out::removeElement($this->nameForm . '_DaTestoBase');
                    Out::removeElement($this->nameForm . '_DaFascicolo');
                    Out::removeElement($this->nameForm . '_DaProtCollegati');
                    Out::hide($this->nameForm . '_divGrigliaAllegati');

                    if ($_POST['tipoProt'] == 'C') {
                        Out::msgStop("Errore", "Questo è un protocollo di emergenza Comunicazioni formali non attive.");
                        $this->returnToParent(true);
                    }
                } else {
                    Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                }

                if ($_POST['tipo'] == 'EMERGENZA' && $anaent_29['ENTDE4'] !== 1) {
                    $this->emergenza = true;
                    $this->tipoProt = 'A';
                    $this->Nuovo();
                    Out::addClass($this->nameForm . '_ANAPRO[PROEME]', "required");
                    Out::addClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
                    Out::addClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
                    Out::addClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
                    break;
                } else {
                    $this->emergenza = false;
                    Out::delClass($this->nameForm . '_ANAPRO[PROEME]', "required");
                    Out::delClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
                    Out::delClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
                    Out::delClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
                }



                $indice = $_POST['proGest_ANAPRO']['ROWID'];

                if ($_POST['datiANAPRO']['ROWID']) {
                    $indice = $_POST['datiANAPRO']['ROWID'];
                }
                $this->consultazione = $_POST['consultazione'];
                $this->tipoProt = $_POST['tipoProt'];
                /*
                 * Carica SubForm
                 */
                $this->CaricaSubFormMittDest();
                $this->CaricaSubFormTrasmissioni();
                /*
                 *  Controllo se viene da mail
                 */
                if ($indice == '') {
                    $this->Nuovo();
                    if ($_POST['datiMail'] != '') {
                        /*
                         * Id Lock Mail
                         */
                        Out::show($this->nameForm . '_TornaElencoMail');
                        $this->LockIdMail = $_POST['datiMail']['lockMail'];
                        if (isset($_POST['objSdi'])) {
                            $this->currObjSdi = unserialize($_POST['objSdi']);
                            if ($this->currObjSdi->isMessaggioSdi()) {
                                $this->CaricaDaSdi(); // carica da sdi
                                break;
                            }
                        }
                        $this->CaricaDaPec();
                        break;
                    }
                    // Controllo se viene da Riscontro SDI
                    if ($_POST['datiRiscontro'] != '') {
                        if (isset($_POST['objSdi'])) {
                            $this->currObjSdi = unserialize($_POST['objSdi']);
                        }
                        $this->CaricaRiscontro();
                        break;
                    }

                    if ($_POST['datiBlocco']) {
                        if ($_POST['datiBlocco']['PROFASKEY']) {
                            //
                            // Decodifico il fascicolo per popolamento campi
                            //
                            $anaorg_rec = $this->proLib->GetAnaorg($_POST['datiBlocco']['PROFASKEY'], 'orgkey');
                            $orgccf = $anaorg_rec['ORGCCF'];
                            $this->DecodAnacat('', substr($orgccf, 0, 4));
                            $this->DecodAnacla('', substr($orgccf, 0, 8));
                            $this->DecodAnafas('', $orgccf, 'fasccf');
                            $this->DecodAnaorg($anaorg_rec['ROWID'], 'rowid');
                            Out::attributo($this->nameForm . '_Organn', "readonly", '0');
                            Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
                            /* Non serve più nasconderlo, più fascicoli */
//                            Out::hide($this->nameForm . '_ANAPRO[PROARG]_butt');
//                            Out::hide($this->nameForm . '_addFascicolo');
                            //Out::hide($this->nameForm . '_AggiungiFascicolo');
                            Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '0');
                            Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
                            Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
                            Out::hide($this->nameForm . '_ANAPRO[PROCAT]_butt');
                            Out::hide($this->nameForm . '_Clacod_butt');
                            Out::hide($this->nameForm . '_Fascod_butt');
                        }
                        if ($_POST['datiBlocco']['propak']) {
//
// Imposto passo / Azione di provenienza
//
                            $this->datiPasso = $_POST['datiBlocco'];
                            if ($this->datiPasso['propak']) {
                                $this->decodDettaglioPratica($this->datiPasso['propak']);
                            }
                        }
                        if ($_POST['datiBlocco']['allegati']) {
//
// Carico automaticamente gli asllegati da protocollare
//
                            $this->proArriAlle = $_POST['datiBlocco']['allegati'];
                            $this->caricaGrigliaAllegati();
//
// Mostro e Blocco il pannello degli allegati
//
                            Out::show($this->nameForm . '_paneAllegati');
                            Out::block($this->nameForm . '_paneAllegati');
                        }
                    }
                } else {
                    $anapro_rec = $this->Modifica($indice);
                    $open_Info = 'Oggetto: ' . $anapro_rec['PRONUM'] . " " . $anapro_rec['PRODAR'];
                    $this->openRecord($this->PROT_DB, 'ANAPRO', $open_Info, $anapro_rec['PRONUM']);
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->proArriAlle) == true) {
                            $doc = $this->proArriAlle[$_POST['rowid']];
                            if (strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION)) === 'xhtml') {
                                $DocPath = $this->proLibAllegati->GetDocPath($doc['ROWID'], true);
                                if (!$DocPath) {
                                    Out::msgStop("Attenzione", "Errore in lettura del contenuto del file " . $this->proLibAllegati->getErrMessage());
                                    break;
                                }
//                                $contentFile = @file_get_contents($doc['FILEPATH']);
                                $contentFile = $DocPath['BINARY'];
                                if (!$contentFile) {
                                    Out::msgStop("Attenzione", "Errore in lettura del contenuto del file " . $doc['FILEPATH']);
                                    break;
                                }
                                $proLibVar = new proLibVariabili();
                                $proLibVar->setCodiceProtocollo($this->anapro_record['PRONUM']);
                                $proLibVar->setTipoProtocollo($this->tipoProt);
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
                            } else {
                                $doc = $this->proArriAlle[$_POST['rowid']];
                                if (strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION)) == "eml") {
                                    Out::msgQuestion("Download", "Cosa vuoi fare con il file eml selezionato?", array(
                                        'F2-Scarica' => array('id' => $this->nameForm . '_ScaricaEml', 'model' => $this->nameForm, 'shortCut' => "f2"),
                                        'F8-Visualizza' => array('id' => $this->nameForm . '_VisualizzaEml', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                            )
                                    );
                                    $this->varAppoggio = array('Rowid' => $doc['ROWID']);
                                    return;
                                }
                                $force = false;
                                $ext = strtolower(pathinfo($doc['FILENAME'], PATHINFO_EXTENSION));
                                if ($ext == 'xml' || $ext == 'eml') {
                                    $force = true;
                                }
                                if (!$this->proLibAllegati->CheckAllegatoDaFirmare($doc['ROWID'])) {
                                    Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                                    break;
                                }
                                /* Se è un allegato provvisorio uso filepath per aprirlo */
                                if (!$doc['ROWID']) {
                                    Out::openDocument(utiDownload::getUrl($doc['NOMEFILE'], $doc['FILEPATH'], $force));
                                } else {
                                    $this->proLibAllegati->OpenDocAllegato($doc['ROWID'], $force);
                                }
                            }
                        }
                        break;
                    case $this->gridNote:
                        $dati = $this->noteManager->getNota($_POST['rowid']);
                        $readonly = false;
                        if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                            Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                            $readonly = true;
                        }
                        $rowidAnapro = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        $anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                        $pronum = $anapro_rec['PRONUM'];
                        $propar = $anapro_rec['PROPAR'];
                        $arcite_tab = $this->proLib->getGenericTab("SELECT DISTINCT(ITEDES) AS ITEDES  FROM ARCITE WHERE ITEPAR='$propar' AND ITEPRO=" . $pronum . " ORDER BY ROWID DESC");
                        $destinatari = array();
                        foreach ($arcite_tab as $arcite_rec) {
                            $destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE MEDCOD='{$arcite_rec['ITEDES']}' AND MEDANN=0", false);
                        }
                        $model = 'proDettNote';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnProDettNote');
                        $formObj->setReturnId('');
                        $rowid = $_POST['rowid'];
                        $_POST = array();
                        $_POST['dati'] = $dati;
                        $_POST['rowid'] = $rowid;
                        $_POST['destinatari'] = $destinatari;
                        $_POST['readonly'] = $readonly;
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->gridFascicoli:
                        $rowid = $_POST['rowid'];
                        $orgkey = $this->ElencoFascicoli[$rowid]['ORGKEY'];
                        if ($orgkey) {
                            $this->ApriGestioneFascicolo($orgkey);
                        }
                        break;
                }
                break;
            case 'addGridRow':
                if ($this->consultazione != true) {
                    switch ($_POST['id']) {
                        case $this->gridNote:
                            //@TODO: UNIFICARE CON EDIT NOTA IN UNA FUNZIONE UNICA
                            $rowidAnapro = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                            $anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                            $pronum = $anapro_rec['PRONUM'];
                            $propar = $anapro_rec['PROPAR'];
                            $arcite_tab = $this->proLib->getGenericTab("SELECT DISTINCT(ITEDES) AS ITEDES  FROM ARCITE WHERE ITEPAR='$propar' AND ITEPRO=" . $pronum . " ORDER BY ROWID DESC");
                            $destinatari = array();
                            foreach ($arcite_tab as $arcite_rec) {
                                $destinatari[] = $this->proLib->getGenericTab("SELECT MEDNOM AS DESTINATARIO, MEDCOD FROM ANAMED WHERE MEDCOD='{$arcite_rec['ITEDES']}' AND MEDANN=0", false);
                            }

                            $model = 'proDettNote';
                            itaLib::openForm($model);
                            $formObj = itaModel::getInstance($model);
                            $formObj->setReturnModel($this->nameForm);
                            $formObj->setReturnEvent('returnProDettNote');
                            $formObj->setReturnId('');
                            $rowapp = $_POST[$this->nameForm . '_ARCITE']['ROWID'];
                            $_POST = array();
                            $_POST['idRitorno'] = $rowapp;
                            $_POST['destinatari'] = $destinatari;
                            $formObj->setEvent('openform');
                            $formObj->parseEvent();
                            break;
                    }
                }

                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if ($this->bloccoDaEmail != true) {

                            $doc = $this->proArriAlle[$_POST['rowid']];

                            $docfirma_rec = $this->proLibAllegati->GetDocfirma($doc['ROWID'], 'rowidanadoc', false, " ORDER BY FIRDATA DESC");
                            /*
                             * Blocchi su cancellazione allegati
                             */
                            if ($docfirma_rec) {
                                if ($docfirma_rec['FIRDATA']) {
                                    Out::msgStop("Attenzione!", "Non è possibile eliminare il documento perché è stato firmato.");
                                } else {
                                    Out::msgStop("Attenzione!", "Togliere il documento dall'elenco delle firme prima di eliminare il file.");
                                }
                                break;
                            }

                            if ($doc['DOCIDMAIL']) {
                                Out::msgStop("Attenzione!", "Non è possibile eliminare il documento perché collegato ad una Mail di origine");
                                break;
                            }

                            $messaggio = "L'operazione non è reversibile, sei sicuro di voler cancellare l'allegato?";

                            if ($doc['DOCROWIDBASE']) {
                                $messaggio .= "<br>Al documento è associato un Testo Base che verrà cancellato.";
                            }
                            Out::msgQuestion("ATTENZIONE!", $messaggio, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            $this->rowidAppoggio = $_POST['rowid'];
                        }
                        break;
                }
                if ($this->consultazione != true && $this->disabledRec != true) {
                    switch ($_POST['id']) {
                        case $this->gridNote:
                            $dati = $this->noteManager->getNota($_POST['rowid']);
                            if ($dati['UTELOG'] != App::$utente->getKey('nomeUtente')) {
                                Out::msgStop("Attenzione!", "Solo l'utente " . $dati['UTELOG'] . " è abilitato alla modifica della Nota.");
                                break;
                            }

                            $this->noteManager->cancellaNota($_POST['rowid']);
                            $this->noteManager->salvaNote();
                            $this->caricaNote();
                            break;
                    }
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->CaricaAllegati();
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $allegato = $this->proArriAlle[$_POST['rowid']];
                        switch ($_POST['colName']) {
                            case 'DAMAIL':
                                if ($allegato['DOCIDMAIL']) {
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $allegato['DOCIDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'PREVIEW':
                                /* Allegato provvisorio */
                                $ExtFile = pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION);
                                if (!$allegato['ROWID'] && !$ExtFile) {
                                    $this->varAppoggio = $_POST['rowid'];
                                    $this->proLibAllegati->setFunzioneAllegati($this->nameForm, $allegato, $this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'], $this->bloccoDaEmail);
                                    break;
                                }
                                if (!$allegato['ROWID']) {
                                    Out::msgInfo('Attenzione', "Protocollo non ancora definitivo.<br>Prima di poter accedere al menu funzioni occorre confermare la creazione del protocollo.");
                                    break;
                                }
                                $this->varAppoggio = $allegato;
                                $this->proLibAllegati->setFunzioneAllegati($this->nameForm, $allegato, $this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'], $this->bloccoDaEmail);
                                break;
                        }
                        break;
                }
                break;
            case 'afterSaveCell':
                if ($_POST['value'] != 'undefined') {
                    switch ($_POST['id']) {
                        case $this->gridAllegati:
                            $currRowid = $_POST['rowid'];
                            $this->proArriAlle[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                            $this->proLibAllegati->AggiornAllegato($this, $this->proArriAlle[$_POST['rowid']]);
                            $this->ControllaAllegati($currRowid);
                            $this->CaricaAllegati();
                            break;
                    }
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AggiornaAss':
                        if ($this->correggiAssegnazioni($this->anapro_record)) {
                            Out::msgInfo("Messaggio", "Le assegnazioni sono state aggiornate");
                        }
                        $this->Modifica($this->anapro_record['ROWID']);
                        break;
                    /* Nuovi Eventi per Fascicolazione proto collegato automatica */
//                    case $this->nameForm . '_ConfermaFascicolaProtoPre':
//                        $this->datiFascicolazine['FASCICOLA_PROPRE'] = true;
                    case $this->nameForm . '_AnnullaFascicolaProtoPre':
                    case $this->nameForm . '_ContinuaRegistraCtrFascicolo':
                    case $this->nameForm . '_Registra':
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $newTipoProt = '';
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        if ($profilo['PROT_ABILITATI'] == 1) {
                            $newTipoProt = 'A';
                        } else if ($profilo['PROT_ABILITATI'] == '2') {
                            $newTipoProt = 'P';
                            $this->consultazione = false; //***
                        } else if ($profilo['PROT_ABILITATI'] == '3') {
                            $newTipoProt = 'C';
                        }
                        if ($newTipoProt != '' && $newTipoProt != $this->tipoProt) {
                            if ($this->tipoProt != 'C') {
                                Out::msgInfo('Attenzione', 'Tipo di Protocollo non consentito.');
                                break;
                            }
                        }
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_Ristampa':
                        $this->stampaEtichetta();
                        break;
                    case $this->nameForm . '_Mail':
                        $this->inviaMailDestinatari();
                        break;
                    case $this->nameForm . '_XOL':
                        $this->inviaRaccomandataDestinatari();
                        break;
                    case $this->nameForm . '_MailMittenti':
                        $this->inviaMailMittenti();
                        break;
                    case $this->nameForm . '_Indirizzi':
                        $this->stampaEtichettaIndirizzi();
                        break;
                    case $this->nameForm . '_Ricevuta':
                        $this->rowidAppoggio = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        $this->ricevuta();
                        break;
                    case $this->nameForm . '_Evidenza':
//                        //!!! TEST DA RIMUOVERE
//                        $this->proLibMail->TestSegnature($this->anapro_record, $this->proArriAlle);
//                        break;
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
                        $arcite_tab = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
                        $evidenza = 0;
                        if ($arcite_tab[0]['ITEEVIDENZA'] == 0) {
                            $evidenza = 1;
                            Out::html($this->nameForm . "_Evidenza_lbl", "Togli Evidenza");
                        } else {
                            $evidenza = 0;
                            Out::html($this->nameForm . "_Evidenza_lbl", "Metti Evidenza");
                        }
                        foreach ($arcite_tab as $arcite_rec) {
                            $arcite_rec['ITEEVIDENZA'] = $evidenza;
                            $this->updateRecord($this->PROT_DB, 'ARCITE', $arcite_rec, '', 'ROWID', false);
                        }
                        break;
                    case $this->nameForm . '_Riserva':
                        Out::valore($this->nameForm . '_ANAPRO[PRORISERVA]', '1');
                        Out::hide($this->nameForm . '_Riserva');
                        Out::show($this->nameForm . '_NonRiserva');
                        Out::show($this->nameForm . '_protRiservato_field');
                        if ($this->anapro_record) {
                            $this->proLib->MettiTogliRiservato($this->anapro_record, true);
                        }
                        break;
                    case $this->nameForm . '_NonRiserva':
                        Out::valore($this->nameForm . '_ANAPRO[PRORISERVA]', '');
                        Out::show($this->nameForm . '_Riserva');
                        Out::hide($this->nameForm . '_NonRiserva');
                        Out::hide($this->nameForm . '_protRiservato_field');
                        if ($this->anapro_record) {
                            $this->proLib->MettiTogliRiservato($this->anapro_record);
                        }
                        break;
                    case $this->nameForm . '_ComForm':
                        $indice = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        if ($indice == '') {
                            $this->tipoProt = 'C';
                            $this->Nuovo();
                        } else {
                            Out::msgQuestion("Vai in Comunicazioni Formali.", "I dati inseriti non saranno memorizzati, sei sicuro di continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCom', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCom', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaCom':
                        $this->tipoProt = 'C';
                        itaLib::clearAppsTempPath();
                        $this->proDestinatari = array();
                        $this->proAltriDestinatari = array();
                        $this->proArriAlle = array();
                        $this->Nuovo();
                        break;

                    case $this->nameForm . '_Partenza':
                        $indice = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        if ($indice == '') {
                            $this->tipoProt = 'P';
                            $this->Nuovo();
                        } else {
                            Out::msgQuestion("Vai in Partenza.", "I dati inseriti non saranno memorizzati, sei sicuro di continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPart', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPart', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaPart':
                        $this->tipoProt = 'P';
                        itaLib::clearAppsTempPath();
                        $this->proDestinatari = array();
                        $this->proAltriDestinatari = array();
                        $this->proArriAlle = array();
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_Arrivo':
                        $indice = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        if ($indice == '') {
                            $this->tipoProt = 'A';
                            $this->Nuovo();
                        } else {
                            Out::msgQuestion("Vai in Arrivo", "I dati inseriti non saranno memorizzati, sei sicuro di continuare?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaArri', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaArri', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                    case $this->nameForm . '_ConfermaArri':
                        $this->tipoProt = 'A';
                        itaLib::clearAppsTempPath();
                        $this->proDestinatari = array();
                        $this->proAltriDestinatari = array();
                        $this->proArriAlle = array();
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_ConfermaInputModifica':
                        if ($_POST[$this->nameForm . '_motivazione'] == '') {
                            $valori[] = array(
                                'label' => array(
                                    'value' => "Motiva la modifica del Protocollo. (obbligatorio)",
                                    'style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                ),
                                'id' => $this->nameForm . '_motivazione',
                                'name' => $this->nameForm . '_motivazione',
                                'type' => 'text',
                                'style' => 'margin:2px;width:300px;',
                                'value' => '',
                                'class' => 'required'
                            );
                            Out::msgInput(
                                    'Aggiorna.', $valori
                                    , array(
                                'Conferma' => array('id' => $this->nameForm . '_ConfermaInputModifica', 'model' => $this->nameForm)
                                    ), $this->nameForm . "_workSpace"
                            );
                            break;
                        }
                        $this->CtrDatiRichiestiProt['CTRINPUTMOD'] = '1';
                        $this->Registra($_POST[$this->nameForm . '_motivazione']);
                        break;
                    case $this->nameForm . '_MettiRiservato':
                        Out::valore($this->nameForm . '_ANAPRO[PRORISERVA]', '1');
                        $_POST[$this->nameForm . '_ANAPRO']['PRORISERVA'] = '1';
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;
                    case $this->nameForm . '_ConfermaTSO':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
                        $_POST[$this->nameForm . '_ANAPRO']['PROTSO'] = 1;
                        Out::valore($this->nameForm . '_ANAPRO[PROTSO]', '1');
                        $_POST[$this->nameForm . '_ANAPRO']['PRORISERVA'] = 1;
                        Out::valore($this->nameForm . '_ANAPRO[PRORISERVA]', '1');
                        if ($this->ControllaPrerequisitiProt()) {
                            $rePronum = $this->Registra();
                            $this->eqAudit->logEqEvent($this, array(
                                'DB' => $this->PROT_DB->getDB(),
                                'DSet' => 'ANAPRO',
                                'Operazione' => '06',
                                'Estremi' => "Conferma assegnazione TSO per il protocollo: {$anapro_rec['PRONUM']}",
                                'Key' => $rePronum
                            ));
                        }
                        break;
                    case $this->nameForm . '_tso':
                        Out::msgQuestion("Attenzione!", "Vuoi togliere la riservatezza del documento TSO?", array(
                            'F8-Mantieni TSO' => array('id' => $this->nameForm . '_MantieniTSO', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Togli TSO ' => array('id' => $this->nameForm . '_TogliTSO', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_TogliTSO':
                        $header = "<span style=\"font-size:1.4em;color:red;\"><b>Conferma la rimozione di riservatezza TSO</b></span>
                            <span style=\"color:red;font-weight:bold;font-size:1.2em;\">Digitare la password utilizzata per il login</span>";
                        Out::msgInput("Attenzione!", array(
                            'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Password'),
                            'id' => $this->nameForm . '_password',
                            'name' => $this->nameForm . '_password',
                            'type' => 'password',
                            'width' => '70',
                            'size' => '40',
                            'maxchars' => '30'), array('F5-Conferma' => array(
                                'id' => $this->nameForm . "_returnPassword", 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm, "auto", "auto", true, $header, ''
                        );
                        break;
                    case $this->nameForm . "_returnPassword":
                        if (!$this->proLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $_POST[$this->nameForm . '_ANAPRO']['PROTSO'] = 0;
                        $_POST[$this->nameForm . '_ANAPRO']['PRORISERVA'] = 0;
                        $rePronum = $this->Registra("Rimozione di riservatezza TSO");
                        $this->eqAudit->logEqEvent($this, array(
                            'DB' => $this->PROT_DB->getDB(),
                            'DSet' => 'ANAPRO',
                            'Operazione' => '06',
                            'Estremi' => "Rimozione di riservatezza TSO per il protocollo: $rePronum",
                            'Key' => $rePronum
                        ));
                        break;
                    case $this->nameForm . '_InserisciDest':
                        $this->CtrDatiRichiestiProt['CTRNODEST'] = '';
                        if ($this->tipoProt == 'P') {
                            Out::setFocus('', $this->proSubMittDest . '_ANAPRO[PRONOM]');
                        } else {
                            Out::setFocus('', $this->nameForm . '_Dest_nome');
                        }
                        break;
                    case $this->nameForm . '_ContinuaRegistra':
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;

                    case $this->nameForm . '_ContinuaRegistraFattura':
                        $this->CtrDatiRichiestiProt['CTRFATTURADOPPIA'] = '1';
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;

                    case $this->nameForm . '_MarcaPDF':
                        $this->CtrDatiRichiestiProt['CTRMARCAPDF'] = '1';
                        $this->fileDaPEC['MARCA_PDF'] = true;
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;
                    case $this->nameForm . '_ModificaDataArrivo':
                        Out::setFocus('', $this->nameForm . '_ANAPRO[PRODAA]');
                        break;
                    case $this->nameForm . '_Annulla':
                        $this->GetFormAnnullaRiattiva('A');
                        break;
                    case $this->nameForm . '_ConfermaInputAnnulla':
                        $Annullamento = $_POST[$this->nameForm . '_ANNULLAMENTO'];
                        if (!$Annullamento['PROANNMOTIVO']) {
                            Out::msgBlock('', 2000, true, 'Attenzione, il Motivo è obbligatorio.');
                            $this->GetFormAnnullaRiattiva('A');
                            break;
                        }
                        /* Ctr se registro giornaliero
                         * Non posso annullarlo se conservato.
                         */
                        $anapro_rec = $this->proLib->getAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
                        if ($this->proLibGiornaliero->isRegistroGiornaliero($anapro_rec)) {
                            $proLibConservazione = new proLibConservazione();
                            $esito_rec = $proLibConservazione->GetEsitoConservazione($anapro_rec['ROWID']);
                            if ($esito_rec['Esito'] == proLibConservazione::ESITO_POSTITIVO) {
                                Out::msgStop("Attenzione", "Questo registro giornaliero risulta già essere conservato. Non è possibile procedere con l'annullamento");
                                break;
                            }
                        }
                        // Annullamento del protocollo.
                        $motivo = "ANNULLAMENTO DEL PROTOCOLLO: " . $Annullamento['PROANNMOTIVO'];
                        $this->registraSave($motivo, $this->anapro_record['ROWID'], 'rowid');
                        $stato = $this->AnnullaRiattiva('A', $Annullamento);
                        if (!$stato) {
                            Out::msgStop("ATTENZIONE!", "Errore in Annullamento.");
                        }
                        $this->Modifica($this->anapro_record['ROWID']);
                        break;
                    case $this->nameForm . '_Riattiva':
                        $this->GetFormAnnullaRiattiva('');
                        break;
                    case $this->nameForm . '_ConfermaInputRiattiva':
                        $Annullamento = $_POST[$this->nameForm . '_ANNULLAMENTO'];
                        if (!$Annullamento['PROANNMOTIVO']) {
                            Out::msgBlock('', 2000, true, 'Attenzione, il Motivo è obbligatorio.');
                            $this->GetFormAnnullaRiattiva('');
                            break;
                        }
                        $motivo = "RIATTIVAZIONE DEL PROTOCOLLO: " . $Annullamento['PROANNMOTIVO'];
                        $this->registraSave($motivo, $this->anapro_record['ROWID'], 'rowid');
                        $stato = $this->AnnullaRiattiva('', $Annullamento);
                        if (!$stato) {
                            Out::msgStop("ATTENZIONE!", "Errore in Riattivazione.");
                        }
                        $this->Modifica($this->anapro_record['ROWID']);
                        break;
                    case $this->nameForm . '_ConfermaCancAlle':
                        $rowid_alle = $this->rowidAppoggio;
                        /* Allegato Provvisorio */
                        if ($this->proLibAllegati->CheckObbligoAllegatiProt($this->tipoProt, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'])) {
                            if (count($this->proArriAlle) <= 1) {
                                Out::msgStop("Attenzione!", "Allegati obbligatori per il protocollo. Non è possibile cancellare l'unico allegato presente.");
                                break;
                            }
                        }
                        /* Cancellazione di un allegato provvisorio */
                        if (!$this->proArriAlle[$rowid_alle]['ROWID']) {
                            $this->cancellaAllegatoProvvisorio($rowid_alle);
                            break;
                        }
//                        Out::msginfo('passa qui');
//                        break;

                        if (array_key_exists($rowid_alle, $this->proArriAlle) == true) {
                            $this->cancellaAllegato($rowid_alle);
                        }
                        $this->rowidAppoggio = null;
                        $this->ControllaAllegati();
                        $this->CaricaAllegati();
                        $this->ricaricaFascicolo();
                        $this->MostraNascondiDocServizio();
                        break;
//                    case $this->nameForm . '_DESNOM_butt':
                    case $this->nameForm . '_DESCOD_butt':
                        if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
                            $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                            proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedMittPartenza');
                        }
                        break;
                    case $this->nameForm . '_ANAPRO[PROCAT]_butt':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t);
                        } else {
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t);
                        }
                        break;
                    case $this->nameForm . '_Clacod_butt':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t);
                        } else {
                            if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                                $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT'] . "'");
                            }
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where);
                        }
                        break;
                    case $this->nameForm . '_Fascod_butt':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                            proRic::proRicTitolarioFiltrato($this->nameForm, $this->proLib, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t);
                        } else {
                            if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                                $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT'] . "'";
                                if ($_POST[$this->nameForm . '_Clacod']) {
                                    $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_ANAPRO']['PROCAT']
                                            . $_POST[$this->nameForm . '_Clacod'] . "'";
                                }
                            }
                            proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $versione_t, $where);
                        }
                        break;
                    case 'proMsgQuestion_Fascicola':
                    case $this->nameForm . '_Fascicola':
//                    case $this->nameForm . '_ANAPRO[PROARG]_butt':
                    case $this->nameForm . '_addFascicolo':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
//                        if ($anapro_rec['PROFASKEY']) {
//                            Out::msgInfo("Attenzione", "Protocollo già fascicolato. <br>Registra le modifiche o ricarica il protocollo.");
//                            break;
//                        }
                        $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                        /*
                         * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può movimentare
                         */
                        $fl_movimenta = false;
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
                            $fl_movimenta = true;
                        }

                        /* Se è archivista può sempre fascicolare */
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
                            $fl_movimenta = true;
                        }
                        if ($fl_movimenta !== true) {
                            Out::msgStop("Attenzione", "Non hai il permesso pe movimentare i fascicoli.");
                            break;
                        }

                        $sel_procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $sel_clacod = $_POST[$this->nameForm . '_Clacod'];
                        $sel_fascod = $_POST[$this->nameForm . '_Fascod'];
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        if ($anapro_rec['PROCCF'] != $sel_procat . $sel_clacod . $sel_fascod) {
                            Out::msgStop("Ricerca fascicolo", "Titolario variato: registrare il protocollo prima di procedere con la fascicolazione");
                            break;
                        }

                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $sel_procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $sel_clacod = $_POST[$this->nameForm . '_Clacod'];
                        $sel_fascod = $_POST[$this->nameForm . '_Fascod'];
                        $sel_organn = $_POST[$this->nameForm . '_Organn'];
                        $Titolario['VERSIONE_T'] = $anapro_rec['VERSIONE_T'];
                        $Titolario['PROCAT'] = $sel_procat;
                        $Titolario['CLACOD'] = $sel_clacod;
                        $Titolario['FASCOD'] = $sel_fascod;
                        $Titolario['ORGANN'] = $sel_organn;
                        $this->ApriSelezioneFascicolo($Titolario);
                        break;

                    case $this->nameForm . '_FascicolaPre':
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                        /*
                         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
                         */
                        $fl_fascicola = false;
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
                            $fl_fascicola = true;
                        }
                        /*
                         * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può movimentare
                         */
                        $fl_movimenta = false;
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI]) {
                            $fl_movimenta = true;
                        }
                        /* Se è archivista può sempre fascicolare */
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
                            $fl_fascicola = true;
                            $fl_movimenta = true;
                        }
                        if (!$fl_movimenta && !$fl_fascicola) {
                            Out::msgStop('Attenzione', "Non hai il permesso pe movimentare i fascicoli.");
                            break;
                        }

                        $sel_procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $sel_clacod = $_POST[$this->nameForm . '_Clacod'];
                        $sel_fascod = $_POST[$this->nameForm . '_Fascod'];
                        $sel_organn = $_POST[$this->nameForm . '_Organn'];
                        if ($sel_procat) {
                            $Titolario['VERSIONE_T'] = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                            $Titolario['PROCAT'] = $sel_procat;
                            $Titolario['CLACOD'] = $sel_clacod;
                            $Titolario['FASCOD'] = $sel_fascod;
                            $Titolario['ORGANN'] = $sel_organn;
                            $this->ApriSelezioneFascicolo($Titolario, 'returnAlberoFascicoloPreProt', false);
                        } else {
                            Out::msgInfo('Attenzione', 'Indicare un Titolario per poter procedere.');
                        }
                        break;
                    // Per ora commentata
//                        if ($anapro_rec['PROPRE']) {
//                            $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                            if ($anapro_pre['PROFASKEY']) {
//                                $fascicolo_rec = $this->proLib->GetAnaorg($anapro_pre['PROFASKEY'], 'orgkey');
//                                $this->varAppoggio = array();
//                                $this->varAppoggio['ROWID'] = $fascicolo_rec['ROWID'];
//                                $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                                $catcod = substr($fascicolo_rec['ORGCCF'], 0, 4);
//                                $clacod = substr($fascicolo_rec['ORGCCF'], 4, 8);
//                                $fascod = substr($fascicolo_rec['ORGCCF'], 8, 12);
//                                $titolario = (int) $catcod;
//                                if ($clacod) {
//                                    $titolario .= "." . (int) $clacod;
//                                }
//                                if ($fascod) {
//                                    $titolario .= "." . (int) $fascod;
//                                }
//                                $messaggio = "<br>Il protocollo collegato è del fascicolo {$fascicolo_rec['ORGCOD']} titolario $titolario - {$fascicolo_rec['ORGDES']}";
//                                if ($propas_rec) {
//                                    $messaggio .= "<br>Collegare l'azione '{$propas_rec['PRODPA']}' a questo protocollo?";
//                                    $bottoni = array(
//                                        'Seleziona un altro Fascicolo' => array('id' => $this->nameForm . '_SelezionaFascicolo', 'model' => $this->nameForm),
//                                        'Assegna questo Documento a un altra Azione' => array('id' => $this->nameForm . '_SelezionaAltroPasso', 'model' => $this->nameForm),
//                                        'Assegna questo Documento all\'Azione' => array('id' => $this->nameForm . '_ImpostaPasso', 'model' => $this->nameForm),
//                                    );
//                                } else {
//                                    $bottoni = array(
//                                        'Seleziona un altro Fascicolo' => array('id' => $this->nameForm . '_SelezionaFascicolo', 'model' => $this->nameForm),
//                                        'Assegna questo Documento al Fascicolo ' => array('id' => $this->nameForm . '_AssegnaFascicoloEsistente', 'model' => $this->nameForm),
//                                    );
//                                }
//                                Out::msgQuestion("Fascicolazione.", $messaggio, $bottoni);
//                                break;
//                            }
//                        }
//                        break;
//                    case $this->nameForm . '_SelezionaFascicolo':
//                        $sel_procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
//                        $sel_clacod = $_POST[$this->nameForm . '_Clacod'];
//                        $sel_fascod = $_POST[$this->nameForm . '_Fascod'];
//                        $sel_organn = $_POST[$this->nameForm . '_Organn'];
//                        $where = " WHERE
//                            ORGDAT='' AND 
//                            ORGCCF='{$sel_procat}{$sel_clacod}{$sel_fascod}' AND 
//                            GESDCH = ''";
//                        if ($_POST[$this->nameForm . '_Organn']) {
//                            $where .= " AND ORGANN='{$sel_organn}'";
//                        }
//                        proric::proRicOrgFas($this->nameForm, $where);
//                        break;
//                    case $this->nameForm . '_AssegnaFascicoloEsistente':
//                        $sql = "SELECT PROGES.* FROM ANAORG LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY WHERE ANAORG.ROWID=" . $this->varAppoggio['ROWID'];
//                        $risultato = $this->proLib->getGenericTab($sql, false);
//                        if (!$risultato['GESNUM']) {
//                            break;
//                        }
//                        $matrice = $this->proLibFascicolo->getAlberoFascicolo($risultato['GESNUM'], array('PROTOCOLLI' => ' OR 1<>1 '));
//                        proric::proRicAlberoFascicolo($this->nameForm, $matrice);
//                        break;
//                        
//                    case $this->nameForm . '_ImpostaPasso':
//                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
//                        $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                        $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                        $propas_rec = $this->proLib->getGenericTab("SELECT
//                                    PROPAS.* 
//                                    FROM PROPAS PROPAS 
//                                    LEFT OUTER JOIN PAKDOC PAKDOC
//                                    ON PROPAS.PROPAK = PAKDOC.PROPAK
//                                    WHERE PROPAS.PRONUM='{$progest_rec['GESNUM']}' AND PAKDOC.PRONUM={$anapro_rec['PROPRE']} AND PAKDOC.PROPAR='{$anapro_rec['PROPARPRE']}'", false);
//
//                        $this->proLibFascicolo->insertPakdoc($this, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $propas_rec['PROPAK']);
//                        $this->passoSelezionato();
//                        break;
//                    case $this->nameForm . '_SelezionaAltroPasso':
//                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
//                        if ($anapro_rec['PROPRE']) {
//                            $anapro_pre = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
//                            if ($anapro_pre['PROFASKEY']) {
//                                $fascicolo_rec = $this->proLib->GetAnaorg($anapro_pre['PROFASKEY'], 'orgkey');
//                                $this->varAppoggio = array();
//                                $this->varAppoggio['ROWID'] = $fascicolo_rec['ROWID'];
//                                $progest_rec = $this->proLibPratica->GetProges($anapro_pre['PROFASKEY'], 'geskey');
//                                if (!$progest_rec['GESNUM']) {
//                                    Out::msgStop("Attenzione!", "Errore in Selezione del procedimento.1");
//                                    break;
//                                }
//                                proRicPratiche::proRicPropas($this->nameForm, " WHERE PRONUM='{$progest_rec['GESNUM']}' AND PROFIN='' AND PROPUB=''");
//                                break;
//                            }
//                        }
//                        Out::msgStop("Attenzione!", "Errore in Selezione dell'Azione.");
//                        break;
                    case $this->nameForm . '_codiceOggetto_butt':
                        $where = '';
                        proRic::proRicOgg($this->nameForm, $where, '', 'DecodOggetto');
                        break;
                    case $this->nameForm . '_ANAPRO[PROINCOGG]_butt':
                        /* PATCH PROGRE */
                        $Codice = $_POST[$this->nameForm . '_ANAPRO']['PROINCOGG'];
                        if ($Codice != 'IN PRENOTAZIONE' && $Codice != '' && $Codice != '0') {
                            Out::msgInfo("Attenzione", "Il progressivo risulta già prenotato. <br>Cancellare il progressivo se si vuole prenotare un altro numero.");
                            break;
                        }
                        /* FINE PATCH PROGRE */
                        $where = ' WHERE DOGINCREMENTALE>0';
                        proRic::proRicOgg($this->nameForm, $where, 'proAnaogg', 'ANAPRO[PROINCOGG]_butt');
                        break;
                    /* PATCH PROGRE */
                    case $this->nameForm . '_CancellaProgressivoOggetto':
                        if (!$_POST[$this->nameForm . '_ANAPRO']['PROINCOGG']) {
                            return;
                        }
                        $anadog_rec = $this->proLib->GetAnadog($_POST[$this->nameForm . '_ANAPRO']['PRODOGCOD']);
                        if ($anadog_rec['DOGINCREMENTALE'] > 0) {
                            $progressivo = $_POST[$this->nameForm . '_ANAPRO']['PROINCOGG'];
                            $progressivotest = $progressivo + 1;
                            if ($anadog_rec['DOGINCREMENTALE'] > $progressivotest) {
                                $Messaggio = "É stato trovato un protocollo con numero progressivo superiore a $progressivo.<br> Non è possibile rimuovere automaticamente il progressivo prenotato.";
                                Out::msgInfo("Progressivo Oggetto.", $Messaggio);
                                return false;
                            }
                            if ($progressivo != 'IN PRENOTAZIONE') {
                                $anadog_rec['DOGINCREMENTALE'] --;
                                $update_Info = 'Oggetto: ' . $anadog_rec['DOGCOD'] . " " . $anadog_rec['DOGINCREMENTALE'];
                                if (!$this->updateRecord($this->PROT_DB, 'ANADOG', $anadog_rec, $update_Info)) {
                                    return false;
                                }
                                // Update anche su anapro!

                                if ($_POST[$this->nameForm . '_ANAPRO']['ROWID']) {
                                    $Anapro_rec = array();
                                    $Anapro_rec['ROWID'] = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                                    $Anapro_rec['PROINCOGG'] = 0;
                                    $Anapro_rec['PRODOGCOD'] = '';
                                    $update_Info = 'Rimosso Progressivo ANAPRO:  ' . $Anapro_rec['ROWID'];
                                    if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $Anapro_rec, $update_Info)) {
                                        Out::msgStop("Attenzione", "Errore in aggiornamento ANAPRO.");
                                        return false;
                                    }
                                }
                            }
                            Out::valore($this->nameForm . '_ANAPRO[PROINCOGG]', '');
                            Out::valore($this->nameForm . '_ANAPRO[PRODOGCOD]', '');
                            Out::valore($this->nameForm . '_codiceOggetto', '');
                            Out::msgInfo("Progressivo Oggetto.", "Il progressivo è stato rimosso dal Protocollo e decrementato nell'anagrafica oggetto.");
                        } else {
                            Out::valore($this->nameForm . '_ANAPRO[PROINCOGG]', '');
                            Out::valore($this->nameForm . '_ANAPRO[PRODOGCOD]', '');
                            Out::valore($this->nameForm . '_codiceOggetto', '');
                            Out::msgStop("Attenzione!", "Non è stato possibile trovare l'oggetto di riferimento in anagrafica.<br>Procedere manualmente.");
                        }
                        /* FINE PATCH PROGRE */
                        break;
                    case $this->nameForm . '_ANAPRO[PROTSP]_butt':
                        proRic::proRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . '_CercaProtPre':
                        if ($this->consultazione != true) {
                            if ($this->anapro_record['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
//                            if (substr($this->tipoProt, 1, 1) == 'A') {
                                Out::msgInfo("Attenzione", "Il protocollo è Annullato!");
                            } else {
                                $where = " ( PROPAR ='C' OR PROPAR ='A' OR PROPAR ='P') AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);
                                $data = date('Ymd');
                                $newdata = date('Ymd', strtotime('-30 day', strtotime($data)));
                                $where .= " AND ANAPRO.PRODAR BETWEEN '" . $newdata . "' AND '" . $data . "'";
                                proRic::proRicNumAntecedenti($this->nameForm, $where);
                            }
                        }
                        break;
                    case $this->nameForm . '_Uff_cod_butt':
                        proRic::proRicAnauff($this->nameForm);
                        break;
                    case $this->nameForm . '_sercod_butt':
                        proRic::proRicAnaservizi($this->nameForm);
                        break;
                    case $this->nameForm . '_DuplicaProt':
                        $rowid = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        $this->CheckDuplicaProt($rowid);
                        break;
                    case $this->nameForm . '_NuovoProtocollo':
                        $this->rowidAppoggio = '';
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_Scanner':
                    case $this->nameForm . '_AllegaScanner':
                        $this->setModelData($_POST);
                        $this->ApriScanner();
                        break;

                    case $this->nameForm . '_ScannerShared':
                        $this->ApriScannerShared();
                        break;

                    case $this->nameForm . '_FileLocale':
                        $this->verificaUpload();
                        break;

                    case $this->nameForm . '_ConfermaAllegaDocumentoProvvisorio':
                        $PosSegnatura = $_POST[$this->nameForm . '_PosizioneMarcatura'];
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $PosizioniSegnatura = $this->proLibAllegati->GetPosizioniSegnatura();
                            $ForcePosSegn = $PosizioniSegnatura[$PosSegnatura];
                        }
                        $Allegato = $this->varAppoggio;
                        $Allegato['POSIZIONE'] = $ForcePosSegn;
                        $Allegato['DOCFIL'] = pathinfo($Allegato['destFile'], PATHINFO_BASENAME);
                        if ($_POST[$this->nameForm . '_MarcaAllegato']) {
                            $this->DatiPreProtocollazione['MARCATURA_ALLEGATI'][$Allegato['DOCFIL']] = $Allegato;
                        }
                        // Predisposto.
                        if ($_POST[$this->nameForm . '_FirmaDocumento']) {
                            
                        }
                        $this->AllegaFile($Allegato['destFile'], $Allegato['fileInfo'], $Allegato['docName']);
                        break;

                    case $this->nameForm . '_ConfermaAllegaDocumento':
                        $destFile = $this->varAppoggio['destFile'];
                        $fileInfo = $this->varAppoggio['fileInfo'];
                        $docName = $this->varAppoggio['docName'];
                        $destDaFarFirmare = $docmeta = array();
                        $PosSegnatura = $_POST[$this->nameForm . '_PosizioneMarcatura'];
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $PosizioniSegnatura = $this->proLibAllegati->GetPosizioniSegnatura();
                            $ForcePosSegn = $PosizioniSegnatura[$PosSegnatura];
                        }
                        $messaggio = "Documento Salvato Correttamente";
                        if ($_POST[$this->nameForm . '_MarcaAllegato']) {
                            $risultato = $this->MarcaDocumentoConSegnatura($destFile, $ForcePosSegn);
                            $destFile = $risultato['output'];
                            $docmeta = $risultato['docmeta'];
                            $messaggio .= " con Marcatura della Segnatura";
                        }
                        if ($_POST[$this->nameForm . '_MettiDaFirmare']) {
                            $destDaFarFirmare = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
                            if (!$destDaFarFirmare) {
                                Out::msgStop("Attenzione", "Firmatario Principale Mancante");
                                return;
                            }
                        }
                        /* Allega file controllato. Messaggio di esito solo se allega file è corretto. */
                        if ($this->AllegaFile($destFile, $fileInfo, $docName, $docmeta, $destDaFarFirmare)) {
                            Out::msgBlock('', 2000, true, $messaggio);
                        }


                        if (!$_POST[$this->nameForm . '_MettiDaFirmare'] && $_POST[$this->nameForm . '_FirmaDocumento']) {
                            $rowidAggiunti = $this->proLibAllegati->getRisultatoRitorno();
                            if ($rowidAggiunti['ROWIDAGGIUNTI'][0]) {
                                /* Vecchio lettura file da firmare */
//                                $protPath = $this->proLib->SetDirectory($this->anapro_record['PRONUM'], $this->tipoProt);
//
//                                $doc = $this->proLib->GetAnadoc($rowidAggiunti['ROWIDAGGIUNTI'][0], 'rowid');
//                                $doc['FILEPATH'] = $protPath . "/" . $doc['DOCFIL'];
//                                $doc['FILENAME'] = $doc['DOCFIL'];
//                                $doc['FILEINFO'] = $doc['DOCNOT'];
//                                $inputFile = $doc['FILEPATH'];
//                                /*
//                                 * Sposta il file da firmare sulla cartella temporanea
//                                 */
//                                $subPath = "segFirma-work-" . md5(microtime());
//                                $tempPath = itaLib::createAppsTempPath($subPath);
//                                $baseName = pathinfo($inputFile, PATHINFO_BASENAME);
//                                $InputFileTemporaneo = $tempPath . "/" . $baseName;
//                                if (!@copy($inputFile, $InputFileTemporaneo)) {
//                                    Out::msgStop("Attenzione", "Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
//                                    return false;
//                                }
                                /* Nuovo lettura file */
                                $doc = $this->proLib->GetAnadoc($rowidAggiunti['ROWIDAGGIUNTI'][0], 'rowid');
                                $doc['FILENAME'] = $doc['DOCFIL'];
                                $doc['FILEINFO'] = $doc['DOCNOT'];
                                /*
                                 * Il file da firmare dovrà trovari nella
                                 * cartella temporanea.
                                 */
                                $subPath = "segFirma-work-" . md5(microtime());
                                $tempPath = itaLib::createAppsTempPath($subPath);
                                $baseName = pathinfo($doc['DOCFIL'], PATHINFO_BASENAME);

                                $InputFileTemporaneo = $tempPath . "/" . $doc['DOCFIL'];
                                /* Lettura e Copia del File */
                                $retCopia = $this->proLibAllegati->CopiaDocAllegato($doc['ROWID'], $InputFileTemporaneo);
                                if (!$retCopia) {
                                    Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                                    return false;
                                }
                                $doc['FILEPATH'] = $InputFileTemporaneo;

                                $outputFile = $doc['FILEPATH'] . ".p7m";
                                $fileOrig = $doc['DOCNAME'];
                                $this->varAppoggio = $doc;

                                $return = "returnFromSignAuth";
                                itaLib::openForm('proFirma', true);
                                /* @var $proFirma proFirma */
                                $proFirma = itaModel::getInstance('proFirma');
                                $proFirma->setEvent('openform');
                                $proFirma->setReturnEvent($return);
                                $proFirma->setReturnModel($this->nameForm);
                                $proFirma->setReturnId('');
                                $proFirma->setInputFilePath($InputFileTemporaneo);
                                $proFirma->setOutputFilePath($outputFile);
                                $proFirma->setinputFileName($fileOrig);
                                $proFirma->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
                                $proFirma->parseEvent();
                            }
                        }
                        $this->CaricaAllegati();
                        break;

                    case $this->nameForm . '_AggiungiFascicolo':
                        $fl_fascicola = false;
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                        $anapro_rec = $this->proLib->GetAnapro($this->formData[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                        /*
                         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
                         */
                        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'] || $permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA])) {
                            $fl_fascicola = true;
                        }
                        if ($fl_fascicola !== true) {
                            Out::msgStop("Attenzione", "Non hai il permesso di aggiungere un fascicolo.");
                            break;
                        }
                        $versione_t = $this->formData[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $procat = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $procla = $this->formData[$this->nameForm . '_Clacod'];
                        $profas = $this->formData[$this->nameForm . '_Fascod'];
                        if ($anapro_rec['PROCCF'] != $procat . $procla . $profas) {
                            Out::msgStop("Aggiunta fascicolo", "Titolario variato: registrare il protocollo prima di procedere con la fascicolazione");
                            break;
                        }

//                        if ($procat == '100' || $procla == '100') {
//                            break;
//                        }
                        $dati = array();
                        $dati['versione'] = $versione_t;
                        $dati['livello1'] = $procat;
                        $dati['livello2'] = $procla;
                        $dati['livello3'] = $profas;
                        $dati['descTitolo'] = $this->formData[$this->nameForm . '_TitolarioDecod'];
                        $dati['prouof'] = $this->formData[$this->nameForm . '_ANAPRO']['PROUOF'];
                        $dati['rowid_protocollo'] = $this->formData[$this->nameForm . '_ANAPRO']['ROWID'];

                        $dati['FASCICOLO'] = array();

                        $dati['FASCICOLO']['versione'] = $this->returnData['TITOLARIO']['VERSIONE_T'];
                        $dati['FASCICOLO']['livello1'] = $this->returnData['TITOLARIO']['PROCAT'];
                        $dati['FASCICOLO']['livello2'] = $this->returnData['TITOLARIO']['CLACOD'];
                        $dati['FASCICOLO']['livello3'] = $this->returnData['TITOLARIO']['FASCOD'];
                        if ($dati['FASCICOLO']['livello1'] == '100' || $dati['FASCICOLO']['livello2'] == '100') {
                            break;
                        }
                        $dati['tipoInserimento'] = 'nuovo';
                        $_POST = array();
                        $model = 'proFascicola';
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('onClick');
                        $formObj->setReturnId($this->nameForm . '_ConfermaInputFasc2');
                        $formObj->setDati($dati);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaInputFasc2':
                        $dati = $_POST['dati'];
                        App::log('$dati');
                        App::log($dati);
                        /*
                         * Sospeso
                         */
                        //$this->AggiornaTitolario($this->anapro_record['PRONUM'], $this->tipoProt);
                        $anapro_rec = $this->anapro_record;
                        $descrizione = $dati['descrizione'];
                        $codiceProcedimento = $dati['procedimento'];
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        $Ufficio = $anapro_rec['PROUOF'];
                        $UfficioGest = $dati['prouof'];
                        $Respon = $profilo['COD_SOGGETTO'];
                        if ($dati['RES'] && $dati['UFF']) {
                            $Respon = $dati['RES'];
                            $Ufficio = $dati['UFF'];
                        }
                        /* Lettura della serie */
                        $Serie_rec = $dati['SERIE'];
                        $Dati_Anaorg = $dati['DATI_ANAORG'];

                        $newVersione = $dati['FASCICOLO']['versione'];
                        $newClassFascicolo = $dati['FASCICOLO']['livello1'] . $dati['FASCICOLO']['livello2'] . $dati['FASCICOLO']['livello3'];
                        $esitoFascicolo = $this->proLibFascicolo->creaFascicolo(
                                $this, array(
                            'VERSIONE_T' => $newVersione,
                            'TITOLARIO' => $newClassFascicolo,
                            'UFF' => $Ufficio,
                            'RES' => $Respon,
                            'GESPROUFF' => $UfficioGest,
                            'SERIE' => $Serie_rec,
                            'DATI_ANAORG' => $Dati_Anaorg
                                ), $descrizione, $codiceProcedimento
                        );
                        if (!$esitoFascicolo) {
                            Out::msgStop("Attenzione!", $this->proLibFascicolo->getErrMessage());
                            break;
                        }
                        $proges_rec = $this->proLibPratica->GetProges($esitoFascicolo, 'rowid');
                        $anapro_F_rec = $this->proLibFascicolo->getAnaproFascicolo($proges_rec['GESKEY']);
                        if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $proges_rec['GESKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_F_rec['PRONUM'], $anapro_F_rec['PROPAR'])) {
                            Out::msgStop("Attenzione! Nuovo Fascicolo", $this->proLibFascicolo->getErrMessage());
                        } else {
                            $this->Modifica($anapro_rec['ROWID']);
                            $this->ApriGestioneFascicolo($proges_rec['GESKEY']);
                            Out::msgBlock('', 2000, false, "Nuovo fascicolo creato correttamente");
                        }
                        break;

                    case $this->nameForm . '_AggiungiMittente':
                        $email = $_POST[$this->nameForm . '_ANAPRO']['PROMAIL'];
                        //Controllo se la mail è tra quelle sdi:
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        if ($anaent_38) {
                            $ElencoMail = unserialize($anaent_38['ENTVAL']);
                            $presente = false;
                            foreach ($ElencoMail as $Mail) {
                                if ($Mail['EMAIL'] == $email) {
                                    $presente = true;
                                    break;
                                }
                            }
                            if ($presente == true) {
                                // Blocca l'inserimento o chiede una conferma?
                                $Msg = 'La mail <b>' . $email . '</b> è presente tra i Mittenti dello SDI';
                                $Msg .= ', non è possibile aggiungerla all\'archivio dei Mittenti/Destinatari.';
                                Out::msgStop('Attenzione', $Msg);
                                break;
                            }
                        }
                        $this->AggiungiMittente();
                        break;

                    case $this->nameForm . '_AggiungiOggetto':
                        $Oggetto = $_POST[$this->nameForm . '_Oggetto'];
                        $Clacod = $_POST[$this->nameForm . '_Clacod'];
                        $Procat = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $Dati = array(
                            'NUOVOOGGETTO' => true,
                            'OGGETTO' => $Oggetto,
                            'PROCAT' => $Procat,
                            'CLACOD' => $Clacod
                        );
                        $model = 'proAnaogg';
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnAggiungiOggetto');
                        $formObj->setReturnId('');
                        $formObj->setDatiAggiuntaOggetto($Dati);
                        $formObj->setEvent('AggiuntaNuovoOggetto');
                        $formObj->parseEvent();
                        Out::setFocus('', $this->nameForm . "_Oggetto");
                        break;

                    case $this->nameForm . '_VisMail':
                        if ($this->fileDaPEC['TYPE'] == 'MAILBOX') {
                            $model = 'emlViewer';
                            $_POST['event'] = 'openform';
                            $_POST['codiceMail'] = $this->fileDaPEC['ROWID'];
                            $_POST['tipo'] = 'rowid';
                            $_POST['abilitaStampa'] = true;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_Trasmissioni':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
                        $arcite_rec = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR']);
                        if ($arcite_rec) {
                            $rowId = $arcite_rec['ROWID'];
                            $model = 'proGestIter';
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['tipoOpen'] = 'visualizzazione';
                            $_POST['rowidIter'] = $rowId;
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->nameForm . '_Variazioni':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $model = 'proVariazioni';
                        $_POST['event'] = 'openform';
                        $_POST['pronum'] = $anapro_rec['PRONUM'];
                        $_POST['propar'] = $anapro_rec['PROPAR'];
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_Riscontro':
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        $anaent_39 = $this->proLib->GetAnaent('39');
                        $anaent_45 = $this->proLib->GetAnaent('45');
                        $anaent_49 = $this->proLib->GetAnaent('49');
                        $anaent_55 = $this->proLib->GetAnaent('55');
                        $Anapro_rec = $this->proLib->GetAnapro($this->anapro_record['PRONUM'], 'codice', $this->anapro_record['PROPAR']);
                        if (($this->anapro_record['PROCODTIPODOC'] == $anaent_38['ENTDE1'] || $this->anapro_record['PROCODTIPODOC'] == $anaent_45['ENTDE5'] ) && $this->tipoProt == 'A' && $this->anapro_record['PROCODTIPODOC'] != '') {//|| $this->anapro_record['PROCODTIPODOC'] == 'EFAS') {
                            if ($anaent_39['ENTDE2'] != '' && $anaent_39['ENTDE2'] != '2') {
                                Out::msgInfo("Attenzione", "Il riscontro sulle Fatture Elettroniche non è abilitato.");
                                break;
                            }
                            /* Se EFAA e attivo Riscontri solo su EFAS: */
                            if ($anaent_49['ENTDE3'] && $this->anapro_record['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                                if ($this->proLib->CaricaElencoEFASCollegati($this->anapro_record)) {
                                    proRic::proRicEFASCollegati($this->nameForm, $this->anapro_record);
                                    break;
                                }
                            }

                            //Rilegge anapro prima di passarlo, o passa il rowid e ci pensa openRiscontroFattura ad aprire il record?
                            $anapro_pretab = $this->proLib->checkRiscontro(substr($this->anapro_record['PRONUM'], 0, 4), substr($this->anapro_record['PRONUM'], 4), $this->anapro_record['PROPAR']);
                            if ($anapro_pretab) {
                                Out::msgQuestion("Riscontro.", "Risultano già collegati dei riscontri a questa Fattura Elettronica.<br>  Vuoi caricare un altro riscontro?", array(
                                    'Annulla' => array('id' => $this->nameForm . '_AnnullaRiscontroFattura', 'model' => $this->nameForm),
                                    'Conferma' => array('id' => $this->nameForm . '_ConfermaRiscontroFattura', 'model' => $this->nameForm),
                                        )
                                );
                            } else {
                                $ret = $this->proLibSdi->openRiscontroFattura($this->anapro_record);
                                if (!$ret) {
                                    Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                                }
                            }
                            break;
                        }

                        $profilo = proSoggetto::getProfileFromIdUtente();
                        $utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                        if ($profilo['PROT_ABILITATI'] == '1') {
                            Out::msgQuestion("Seleziona il tipo di Protocollo.", "Che tipo di Protocollo vuoi creare?", array(
                                'F9-Doc.Formale' => array('id' => $this->nameForm . '_RiscontroDocFormale',
                                    'model' => $this->nameForm, 'shortCut' => "f9"),
                                'F5-Arrivo' => array('id' => $this->nameForm . '_RiscontroArrivo',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        } else if ($profilo['PROT_ABILITATI'] == '2') {
                            $Bottoni = array(
                                'F9-Doc.Formale' => array('id' => $this->nameForm . '_RiscontroDocFormale',
                                    'model' => $this->nameForm, 'shortCut' => "f9"),
                                'F8-Partenza' => array('id' => $this->nameForm . '_RiscontroPartenza',
                                    'model' => $this->nameForm, 'shortCut' => "f8")
                            );
                            Out::msgQuestion("Seleziona il tipo di Protocollo.", "Che tipo di Protocollo vuoi creare?", $Bottoni);
                            break;
                        } else if ($profilo['PROT_ABILITATI'] == '3') {
                            $Bottoni = array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_nessunRiscontro',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Doc.Formale' => array('id' => $this->nameForm . '_RiscontroDocFormale',
                                    'model' => $this->nameForm, 'shortCut' => "f5"));
                            Out::msgQuestion("Attenzione!", "Sei sicuro di voler Riscontrare questo Documento con un Nuovo Documento Formale?", $Bottoni);
                            break;
                        } else {
                            $Bottoni = array(
                                'F9-Doc.Formale' => array('id' => $this->nameForm . '_RiscontroDocFormale',
                                    'model' => $this->nameForm, 'shortCut' => "f9"),
                                'F8-Partenza' => array('id' => $this->nameForm . '_RiscontroPartenza',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Arrivo' => array('id' => $this->nameForm . '_RiscontroArrivo',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                            );
                        }
                        /*
                         * Controllo se attivare doc alla firma
                         */
                        if ($anaent_55['ENTDE2']) {
                            $Bottoni['Documento Alla Firma'] = array('id' => $this->nameForm . '_RiscontroDocumento',
                                'model' => $this->nameForm);
                        }
                        Out::msgQuestion("Seleziona il tipo di Protocollo.", "Che tipo di Protocollo vuoi creare?", $Bottoni);
                        break;

                    case $this->nameForm . '_ConfermaRiscontroFattura':
                        $ret = $this->proLibSdi->openRiscontroFattura($this->anapro_record);
                        if (!$ret) {
                            Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                        }
                        break;
                    case $this->nameForm . '_RiscontroArrivo':
                        $this->tipoProt = 'A';
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $this->Nuovo();
                        $this->setRiscontro($anapro_rec);
                        break;
                    case $this->nameForm . '_RiscontroPartenza':
                        $this->tipoProt = 'P';
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $this->Nuovo();
                        $this->setRiscontro($anapro_rec);
                        break;
                    case $this->nameForm . '_RiscontroDocFormale':
                        $this->tipoProt = 'C';
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        $this->Nuovo();
                        $this->setRiscontro($anapro_rec);
                        break;

                    case $this->nameForm . '_RiscontroDocumento':
                        $Bottoni['Partenza'] = array('id' => $this->nameForm . '_RiscontroDocumentoPartenza',
                            'model' => $this->nameForm);
                        $Bottoni['Doc. Formale'] = array('id' => $this->nameForm . '_RiscontroDocumentoDocFormale',
                            'model' => $this->nameForm);
                        Out::msgQuestion("Documento alla Firma", "Che tipo di documento vuoi predisporre?", $Bottoni);
                        break;

                    case $this->nameForm . '_RiscontroDocumentoPartenza':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAttoRiscontro($anapro_rec, 'P');
                        break;
                    case $this->nameForm . '_RiscontroDocumentoDocFormale':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAttoRiscontro($anapro_rec, 'C');
                        break;

                    case $this->nameForm . '_UsaModello':
                        $anapro_rec = array();
                        $anapro_rec['ROWID'] = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        $anapro_rec['PROTEMPLATE'] = true;
                        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, '', 'ROWID', false);
                        $this->Modifica($_POST[$this->nameForm . '_ANAPRO']['ROWID']);
                        break;
                    case $this->nameForm . '_AnnullaModello':
                        $anapro_rec = array();
                        $anapro_rec['ROWID'] = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
                        $anapro_rec['PROTEMPLATE'] = false;
                        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, '', 'ROWID', false);
                        $this->Modifica($_POST[$this->nameForm . '_ANAPRO']['ROWID']);
                        break;
                    case $this->nameForm . '_UFFNOM_butt':
                        $codice = $_POST[$this->nameForm . '_DESCOD'];
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
                    case $this->nameForm . '_SegnaDocumento':
                        $this->MarcaturaSuDocumento();
                        break;
                    case $this->nameForm . '_SegnaDocumentoDaFirmare':
                        $this->MarcaturaSuDocumentoDaFirmare();
                        break;
                    case $this->nameForm . '_VisualizzaDocumento':
                        $this->proLibAllegati->OpenDocAllegato($this->varAppoggio['ROWID']);
//                        Out::openDocument(utiDownload::getUrl($this->varAppoggio['DOCNAME'], $this->varAppoggio['FILEPATH']));
                        break;
                    case $this->nameForm . '_DaFirmare':
                        $anapro_rec = $this->anapro_record;
                        $anades_mitt = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
                        if (!$anades_mitt) {
                            Out::msgStop("Attenzione", "Firmatario Principale Mancante");
                            return;
                        }

                        if ($anades_mitt['DESCOD'] != $this->formData[$this->nameForm . "_DESCOD"]) {
                            Out::msgStop("Attenzione", "Firmatario Principale non Ancora aggiornato.<br>Aggiorna il protocollo prima di procedere.");
                            return;
                        }

                        $this->proLibAllegati->DaFirmare($this, $anades_mitt, $this->varAppoggio['ROWID']);
                        $iter = proIter::getInstance($this->proLib, $anapro_rec);
                        $iter->sincronizzaIterFirma('aggiungi');
                        $this->CaricaAllegati();
                        break;
                    case $this->nameForm . '_TogliDaFirmare':
                        $anadoc_rec = $this->proLib->GetAnadoc($this->varAppoggio['ROWID'], "ROWID");
                        $docfirma_tab = $this->proLibAllegati->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', true);
                        if (!$docfirma_tab) {
                            Out::msgStop("Attenzione", "Richieste di firma non trovate.");
                            break;
                        }
                        foreach ($docfirma_tab as $docfirma_rec) {
                            $delete_Info = 'Oggetto: Cancellazione Richiesta di firma ' . $anadoc_rec['DOCKEY'];
                            if (!$this->deleteRecord($this->PROT_DB, "DOCFIRMA", $docfirma_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Attenzione", "Cancellazione Richieste di firma non avvenuta.");
                                break;
                            }
                            $anapro_rec = $this->anapro_record;
                            $iter = proIter::getInstance($this->proLib, $anapro_rec);
                            $iter->sincronizzaIterFirma('cancella', $docfirma_rec['ROWIDARCITE']);
                        }
                        $this->CaricaAllegati();
                        break;
                    case $this->nameForm . '_FirmaFile':
                        if (!$this->proLibAllegati->checkAbilitazioneAllaFirma($this->anapro_record['PRONUM'], $this->tipoProt)) {
                            Out::msgQuestion("Gestione Allegato.", "Non sei uno dei firmatari di questo protocollo, vuoi proseguire con la firma?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaProcFirma',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Firmare' => array('id' => $this->nameForm . '_ConfermaProcFirma',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ConfermaProcFirma':
                        $doc = $this->varAppoggio;
//                        /* Vecchia lettura file */
//                        $inputFile = $doc['FILEPATH'];
//                        /*
//                         * Sposta il file da firmare sulla cartella temporanea
//                         */
//                        $subPath = "segFirma-work-" . md5(microtime());
//                        $tempPath = itaLib::createAppsTempPath($subPath);
//                        $baseName = pathinfo($inputFile, PATHINFO_BASENAME);
//                        $InputFileTemporaneo = $tempPath . "/" . $baseName;
//                        if (!@copy($inputFile, $InputFileTemporaneo)) {
//                            Out::msgStop("Attenzione", "Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
//                            return false;
//                        }

                        /* Nuovo lettura file */
                        //$doc = $this->proLib->GetAnadoc($rowidAggiunti['ROWIDAGGIUNTI'][0], 'rowid');
                        /*
                         * Il file da firmare dovrà trovari nella
                         * cartella temporanea.
                         */
                        $subPath = "segFirma-work-" . md5(microtime());
                        $tempPath = itaLib::createAppsTempPath($subPath);
                        $baseName = pathinfo($doc['DOCFIL'], PATHINFO_BASENAME);

                        $InputFileTemporaneo = $tempPath . "/" . $doc['DOCFIL'];
                        /* Lettura e Copia del File */
                        $retCopia = $this->proLibAllegati->CopiaDocAllegato($doc['ROWID'], $InputFileTemporaneo);
                        if (!$retCopia) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                            return false;
                        }
                        $doc['FILEPATH'] = $InputFileTemporaneo;

                        $outputFile = $doc['FILEPATH'] . ".p7m";
                        $fileOrig = $doc['DOCNAME'];

                        $return = "returnFromSignAuth";
                        itaLib::openForm('proFirma', true);
                        /* @var $proFirma proFirma */
                        $proFirma = itaModel::getInstance('proFirma');
                        $proFirma->setEvent('openform');
                        $proFirma->setReturnEvent($return);
                        $proFirma->setReturnModel($this->nameForm);
                        $proFirma->setReturnId('');
                        $proFirma->setInputFilePath($InputFileTemporaneo);
                        $proFirma->setOutputFilePath($outputFile);
                        $proFirma->setinputFileName($fileOrig);
                        $proFirma->setTopMsg("<div style=\"font-size:1.3em;color:red;\">Inserisci le credenziali per la firma remota:</div><br><br>");
                        $proFirma->parseEvent();
                        break;
                    case $this->nameForm . '_RimuoviAllaFirma':
                        $anades_mitt = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
                        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($this->anapro_record['PRONUM'], $this->tipoProt, true, " AND FIRCOD='{$anades_mitt['DESCOD']}'");
                        $anapro_rec = $this->anapro_record;
                        $iter = proIter::getInstance($this->proLib, $anapro_rec);
                        foreach ($docfirma_tab as $docfirma_rec) {
                            $delete_Info = 'Oggetto: Cancellazione Richiesta di firma ' . $docfirma_rec['ROWIDARCITE'];
                            if (!$this->deleteRecord($this->PROT_DB, "DOCFIRMA", $docfirma_rec['ROWID'], $delete_Info)) {
                                Out::msgStop("Attenzione", "Cancellazione Richieste di firma non avvenuta.");
                                break;
                            }
                            $iter->sincronizzaIterFirma('cancella', $docfirma_rec['ROWIDARCITE']);
                        }
                        break;
                    case $this->nameForm . '_ConfermaMarcaAllegati':
                        $risultato = $this->proLibAllegati->checkMarcati($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
                        $anadoc_tab = $risultato['ELENCO_NON_MARCATI'];
                        $protPath = $this->proLib->SetDirectory($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);

                        foreach ($anadoc_tab as $anadoc_rec) {
                            $DocPath = $this->proLibAllegati->GetDocPath($anadoc_rec['ROWID'], false, false, true);
                            $this->proLibAllegati->SegnaDocumento($this, $DocPath['DOCPATH'], $this->anapro_record, $anadoc_rec['ROWID']);
//                            $this->proLibAllegati->SegnaDocumento($this, $protPath . "/" . $anadoc_rec['DOCFIL'], $this->anapro_record, $anadoc_rec['ROWID']);
                        }
                        $this->CaricaAllegati();
                        break;
                    case $this->nameForm . '_GestPratica':
                        $this->ApriGestioneFascicolo();
                        break;
                    case $this->nameForm . '_ANAPRO[PROCODTIPODOC]_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm);
                        break;

                    case $this->nameForm . '_AllegatiServizio':
                        proRic::proRicAnadocServizio($this->nameForm, $this->anapro_record);
                        break;

                    case $this->nameForm . '_ScaricaAllegatiZip':
                        if ($this->anapro_record) {
                            if (!$this->proLibAllegati->ScaricaAllegatiZipProtocollo($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'])) {
                                Out::msgStop('Attenzione', $this->proLibAllegati->getErrMessage());
                            }
                        }
                        break;

                    case $this->nameForm . '_GestioneAllegati':
                        if ($this->proArriIndice) {
                            itaLib::openForm('proArriAllegati');
                            /* @var $ptiROLAppendObj ptiROLAppend */
                            $ptiROLAppendObj = itaModel::getInstance('proArriAllegati');
                            $ptiROLAppendObj->setEvent('openDettaglio');
                            $ptiROLAppendObj->setIndiceRowid($this->proArriIndice);
                            $ptiROLAppendObj->setProArriAlle($this->proArriAlle);
                            $ptiROLAppendObj->setReturnModel($this->nameForm);
                            $ptiROLAppendObj->setReturnEvent('returnFromGestioneAllegati');
                            $ptiROLAppendObj->setReturnId('');
                            $ptiROLAppendObj->parseEvent();
                        }
                        break;

                    case $this->nameForm . '_ProtCollegati':
                        if ($this->anapro_record) {
                            proRic::proRicLegame($this->proLib, $this->nameForm, 'returnProtCollegati', $this->PROT_DB, $this->anapro_record);
                        }
                        break;

                    case $this->nameForm . '_CambiaPosizioneSegnatura':
                        $this->ProponiPosizioneMarcatura('PosizioneRegistra');
                        break;
                    case $this->nameForm . '_GesSegnatura':
                        $this->ProponiPosizioneMarcatura('GesSegnatura');
                        break;
                    case $this->nameForm . '_GesSegnaturaFirma':
                        $this->ProponiPosizioneMarcatura('GesSegnaturaFirma');
                        break;

                    case $this->nameForm . '_ConfMarcaturaGesSegnatura':
                        $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
                        $PosSegnatura = $_POST[$this->nameForm . '_PosizioneMarcatura'];
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $ForcePosSegn = $PosizioniMarcatura[$PosSegnatura];
                        }
                        $this->varMarcatura['FORZA_MARCATURA'] = $ForcePosSegn;
                        $this->MarcaturaSuDocumento();
                        break;
                    case $this->nameForm . '_ConfMarcaturaGesSegnaturaFirma':
                        $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
                        $PosSegnatura = $_POST[$this->nameForm . '_PosizioneMarcatura'];
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $ForcePosSegn = $PosizioniMarcatura[$PosSegnatura];
                        }
                        $this->varMarcatura['FORZA_MARCATURA'] = $ForcePosSegn;
                        $this->MarcaturaSuDocumentoDaFirmare();
                        break;

                    case $this->nameForm . '_ConfMarcaturaPosizioneRegistra':
                        $this->CtrDatiRichiestiProt['CTRMARCAPDF'] = '1';
                        $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
                        $PosSegnatura = $_POST[$this->nameForm . '_PosizioneMarcatura'];
                        $ForcePosSegn = array();
                        if ($PosSegnatura) {
                            $ForcePosSegn = $PosizioniMarcatura[$PosSegnatura];
                        }
                        $this->varMarcatura['FORZA_MARCATURA'] = $ForcePosSegn;
                        $this->fileDaPEC['MARCA_PDF'] = true;
                        if ($this->ControllaPrerequisitiProt()) {
                            $this->Registra();
                        }
                        break;

                    case $this->nameForm . '_DaP7m':
                        $this->datiUtiP7m = array();
                        $rowid = $_POST[$this->gridAllegati]['gridParam']['selarrrow'];
                        App::log($rowid);
                        if ($rowid === '' || $rowid == 'null') {
                            Out::msgInfo("Informazione", "Selezionare il file p7m tra gli allegati.");
                            break;
                        }
                        $Allegato = $this->proArriAlle[$rowid];
                        if ($Allegato) {
                            $ext = strtolower(pathinfo($Allegato['FILEPATH'], PATHINFO_EXTENSION));
                            if ($ext != 'p7m') {
                                Out::msgInfo("Informazione", "Selezionare un file di tipo p7m tra gli allegati.");
                                break;
                            }
                            $CustomButt = array();
                            $CustomButt['CustomReturnEvent'] = 'returnAllegaDaP7m';
                            $CustomButt['CustomReturnModel'] = $this->nameForm;
                            $CustomButt['CustomButton'] = 'Aggiungi file contenuto al protocollo';
                            $Titolo = 'Verifica Firma - Protocollo N. ' . substr($this->anapro_record['PRONUM'], 4) . '/' . substr($this->anapro_record['PRONUM'], 0, 4) . ' ' . $this->anapro_record['PROPAR'];
                            $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($Allegato['ROWID'], '', true);
                            if (!$CopyPathFile) {
                                Out::msgStop('Attenzione', "Errore nell'apertura del file Firmato. " . $this->proLibAllegati->getErrMessage());
                                break;
                            }
                            $_POST['file'] = $CopyPathFile;
//                            $_POST['file'] = $Allegato['FILEPATH'];
                            $_POST['fileOriginale'] = $Allegato['DOCNAME'];
                            itaLib::openForm('utiP7m');
                            /* @var $utiP7m utiP7m */
                            $utiP7m = itaModel::getInstance('utiP7m');
                            $utiP7m->setEvent('openform');
                            $utiP7m->setTitoloForm($Titolo);
                            $utiP7m->setCustomButt($CustomButt);
                            $utiP7m->parseEvent();
                        }
                        break;

                    case $this->nameForm . '_DaTestoBase':
                        docRic::docRicDocumentiAdv($this->nameForm, "PROTOCOLLO", "TESTIBASE", " AND DATASCAD = '' AND TIPO = 'XHTML'");
                        break;

                    case $this->nameForm . '_ApriTestoBase':
                        $Allegato = $this->varAppoggio;
                        $Anadoc_rec = $this->proLib->GetAnadoc($Allegato['DOCROWIDBASE'], 'rowid');
                        $this->proLibAllegati->ApriTestoBaseAnadoc($Anadoc_rec, $this->nameForm, 'TestoBase');
                        break;
                    case $this->nameForm . '_ScaricaTestoBaseServizio':
                        $anadoc_rec = $this->varAppoggio;
//                        $protPath = $this->proLib->SetDirectory($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
//                        $filepath = $protPath . "/" . $anadoc_rec['DOCFIL'];
//                        Out::openDocument(utiDownload::getUrl($anadoc_rec['DOCNAME'], $filepath));
                        $this->proLibAllegati->OpenDocAllegato($anadoc_rec['ROWID']);
                        break;

                    case $this->nameForm . '_CancellaTestoBaseServizio':
                        //Out::msgQuestion("Cancellazione.", "Confermi di voler cancellare il testo base e gli eventuali allegati associati?", array(
                        Out::msgQuestion("Cancellazione.", "Confermi di voler cancellare il testo base?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaCancTestoBase', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfCancellaTestoBaseServizio', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfCancellaTestoBaseServizio':
                        // Salvo lo stato del protocollo ed i suoi allegati.
                        $motivo = "Cancellazione Allegato Testo Base";
                        $this->registraSave($motivo, $this->proArriIndice);
                        $Anadoc_rec = $this->varAppoggio;
                        if (!$this->CancellaDocumento($Anadoc_rec['ROWID'], $this->anapro_record)) {
                            break;
                        }
                        //  caso cancellazione solo xhtml e rimuovo docrowidbase collegato.
                        $Anadoc_tab = $this->proLib->GetAnadoc($Anadoc_rec['ROWID'], 'docrowidbase', true);
                        foreach ($Anadoc_tab as $AnadocMod_rec) {
                            $AnadocMod_rec['DOCSUBTIPO'] = '';
                            $AnadocMod_rec['DOCROWIDBASE'] = '';
                            $update_Info = 'DocBase Eliminato: ' . $Anadoc_rec['ROWID'];
                            if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $AnadocMod_rec, $update_Info)) {
                                Out::msgStop("Attenzione", "Errore in aggiornamento ANADOC collegato.");
                                return false;
                            }
                        }
                        $this->ControllaAllegati();
                        $this->CaricaAllegati();
                        Out::msgBlock('', 2000, false, "Testo Base cancellato correttamente");
                        $this->MostraNascondiDocServizio();
                        break;
//                        // cas oControllo presenza docrowidbase allegati e li cancello
//                        $Anadoc_tab = $this->proLib->GetAnadoc($Anadoc_rec['ROWID'], 'docrowidbase', true);
//                        foreach ($Anadoc_tab as $AnadocDel_rec) {
//                            if (!$this->CancellaDocumento($AnadocDel_rec['ROWID'], $this->anapro_record)) {
//                                return false;
//                            }
//                        }
//                        Out::msgBlock('', 2000, false, "Testo Base cancellato correttamente");
//                        $this->ControllaAllegati();
//                        $this->CaricaAllegati();
//                        break;

                    case $this->nameForm . '_ConfermaInsFascicolo':
                        $this->InserisciInFascicolo();
                        break;

                    case $this->nameForm . '_CreaCopiaAnalogica':
                        $DocPath = $this->proLibAllegati->GetDocPath($this->varAppoggio['ROWID'], false, false, true);
                        if (!$this->proLibAllegati->GetCopiaAnalogica($this, $DocPath['DOCPATH'], $DocPath['DOCPATH'], $this->anapro_record, $this->varAppoggio)) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                        }
                        break;

                    case $this->nameForm . '_AnnullaRiscontroFasc':

                        break;

                    case $this->nameForm . '_DaFascicolo':
                        $this->ProtAllegaDaFascicolo = $this->anapro_record['ROWID'];
                        $this->proLib->AllegaDaFascicolo($this->anapro_record['ROWID']);
                        break;

                    case $this->nameForm . '_DaProtCollegati':
                        // Qui controllo se ha qualche prot collegato:
                        if ($this->anapro_record) {
                            $anno = substr($this->anapro_record['PRONUM'], 0, 4);
                            $codice = intval(substr($this->anapro_record['PRONUM'], 4));
                            if ($this->anapro_record['PROPRE'] || $this->proLib->checkRiscontro($anno, $codice, $this->anapro_record['PROPAR'])) {
                                $this->CaricaAllegatiDaProtocolliCollegati($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
                            } else {
                                Out::msgInfo('Info', 'Nessun protocollo collegato trovato.');
                            }
                        }
                        break;

                    case $this->nameForm . '_ScaricaMail':
                        $this->proLibAllegati->OpenDocAllegato($this->varAppoggio['ROWID']);
                        break;
                    case $this->nameForm . '_RipristinaMailDaProt':
                        if (!$this->proLibAllegati->RipristinaMailDaProt($this->varAppoggio['ROWID'])) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                        } else {
                            Out::msgInfo("Informazione", "Mail Ripristinata correttamente.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaCambioFirm':
                        $this->DelegatoFirmatario['DELEGATO'] = true;
                        $Anamed_rec = $this->proLib->GetAnamed($this->DelegatoFirmatario['CODICE'], 'codice');
                        Out::valore($this->nameForm . '_DESCOD', $Anamed_rec['MEDCOD']);
                        Out::valore($this->nameForm . '_DESNOM', $Anamed_rec['MEDNOM']);
                        $anauff_rec = $this->proLib->GetAnauff($this->DelegatoFirmatario['UFFICIO']);
                        Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                        Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                        break;

                    case $this->nameForm . '_InfoMailProt':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        if ($anapro_rec['PROIDMAIL']) {
                            $model = 'emlViewer';
                            $_POST['event'] = 'openform';
                            $_POST['codiceMail'] = $anapro_rec['PROIDMAIL'];
                            $_POST['tipo'] = 'id';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;

                    case $this->nameForm . '_TornaElencoMail':
                        if ($this->LockIdMail) {
                            $this->proLibMail->unlockMail($this->LockIdMail);
                            $this->LockIdMail = array();
                        }
                        $model = 'proGestMail';
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->close();
                        break;

                    case $this->nameForm . '_CreaDocAllaFirma':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDocumentale.class.php';
                        $proLibDocumentale = new proLibDocumentale();
                        $proLibDocumentale->ApriNuovoAtto();
                        $this->close();
                        break;

                    case $this->nameForm . '_VisualizzaEml':
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $FilePathDest = $this->proLibAllegati->CopiaDocAllegato($this->varAppoggio['Rowid'], '', true);
                        if (!$FilePathDest) {
                            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                            break;
                        }
                        $_POST['codiceMail'] = $FilePathDest;
                        $this->varAppoggio = array();
                        $_POST['tipo'] = 'file';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ScaricaEml':
                        $this->proLibAllegati->OpenDocAllegato($this->varAppoggio['Rowid'], true);
                        $this->varAppoggio = array();
                        break;

                    case $this->nameForm . '_GestEstensione':
                        $this->ChiediInserimentoEstensione();
                        break;

                    case $this->nameForm . '_ConfermaEstensione':
                        $rowid = $this->varAppoggio;
                        $allegato = $this->proArriAlle[$rowid];
                        $Ext = $_POST[$this->nameForm . '_ESTENSIONEFILE'];
                        if (!$Ext) {
                            $this->ChiediInserimentoEstensione();
                            break;
                        }
                        // Sposto il file fisico.
                        $FilePathDest = $allegato['FILEPATH'] . '.' . $Ext;
                        if (!rename($allegato['FILEPATH'], $FilePathDest)) {
                            Out::msgStop('Attenzione', "Errore in inserimento estensione allegato.");
                            break;
                        }
                        $allegato['FILEPATH'] = $allegato['FILEPATH'] . '.' . $Ext;
                        $allegato['FILENAME'] = $allegato['FILENAME'] . '.' . $Ext;
                        $allegato['DOCNAME'] = $allegato['DOCNAME'] . '.' . $Ext;
                        $allegato['FILEINFO'] = $allegato['FILEINFO'] . '.' . $Ext;
                        $allegato['NOMEFILE'] = $allegato['NOMEFILE'] . '.' . $Ext;
                        $allegato['PREVIEW'] = '';
                        //
                        $this->proArriAlle[$rowid] = $allegato;
                        $this->CaricaAllegati();
                        $this->varAppoggio = array();
                        break;

                    case $this->nameForm . '_StampaMailZip':
                        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
                        if ($anapro_rec['PROPAR'] == 'P') {
                            //Chiamo funzione
                            if (!$this->proLibMail->StampaMailDiConsegnaProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                                Out::msgInfo('Attenzione', $this->proLibMail->getErrMessage());
                            }
                        }
                        break;

                    case 'close-portlet':
                        if ($this->LockIdMail) {
                            $this->proLibMail->unlockMail($this->LockIdMail);
                            $this->LockIdMail = array();
                        }
                        $this->returnToParent(true);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAPRO[PROUOF]':
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        $this->prouof = $_POST[$this->nameForm . '_ANAPRO']['PROUOF'];
                        $this->bloccoTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF']);
                        $utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                        if (($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) && $this->tipoProt == 'A') {
                            $oggutenti_check = $this->proLib->GetOggUtenti($utenti_rec['UTELOG']);
                            if ($oggutenti_check) {
                                Out::setFocus('', $this->nameForm . "_ANAPRO[PROCON]");
                            }
                        }
                        break;
                    case $this->nameForm . '_ANAPRO[PRONPA]':
                    case $this->nameForm . '_ANAPRO[PRODAS]':
                        if ($this->tipoProt != 'C') {
                            $pronpa = $_POST[$this->nameForm . '_ANAPRO']['PRONPA'];
                            $prodas = $_POST[$this->nameForm . '_ANAPRO']['PRODAS'];
                            $pronom = $_POST[$this->nameForm . '_ANAPRO']['PRONOM'];
                            $this->checkEsistenzaProt($pronpa, $prodas, $pronom);
                        }
                        break;
//                    case $this->nameForm . '_Pronur':
//                        if ($_POST[$this->nameForm . '_Pronur'] != '' && $_POST[$this->nameForm . '_ANAPRO']['PROTSP'] == '') {
//                            $tipoPosta = array('TSPCOD' => ' RAC', 'TSPDES' => 'POSTA RACCOMANDATA');
//                            $anatsp_rec = $this->DecodAnatsp($tipoPosta['TSPCOD']);
//                            if (!$anatsp_rec) {
//                                $insert_Info = 'Oggetto Set Spedizione: ' . $tipoPosta['TSPCOD'] . " " . $tipoPosta['TSPDES'];
//                                $this->insertRecord($this->PROT_DB, 'ANATSP', $tipoPosta, $insert_Info);
//                                $this->DecodAnatsp($tipoPosta['TSPCOD']);
//                            }
//                        }
//                        break;
                    case $this->nameForm . '_DESCOD':
                        Out::valore($this->nameForm . '_DESCUF', '');
                        Out::valore($this->nameForm . '_UFFNOM', '');
                        break;
                    case $this->nameForm . '_ANAPRO[PROCAT]':
                        $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                        }
                        if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                            $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t, $codice);
                            if (!$titolario_tab) {
                                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
                                Out::valore($this->nameForm . '_Clacod', '');
                                Out::valore($this->nameForm . '_Fascod', '');
                                Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                                Out::valore($this->nameForm . '_Organn', '');
                                Out::valore($this->nameForm . '_FascicoloDecod', '');
                                Out::valore($this->nameForm . '_TitolarioDecod', '');
                                Out::valore($this->nameForm . '_CodSottofascicolo', '');
                                Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
                            } else {
                                $this->checkCatFiltrato($versione_t, $codice);
                            }
                        } else {
                            $this->DecodAnacat($versione_t, $codice);
                        }
                        break;
                    case $this->nameForm . '_Clacod':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $codice = $_POST[$this->nameForm . '_Clacod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t, $codice1, $codice2);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                                    Out::valore($this->nameForm . '_Organn', '');
                                    Out::valore($this->nameForm . '_FascicoloDecod', '');
                                    Out::valore($this->nameForm . '_TitolarioDecod', '');
                                    Out::valore($this->nameForm . '_CodSottofascicolo', '');
                                    Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
                                    $this->checkCatFiltrato($versione_t, $codice1);
                                } else {
                                    $this->checkClaFiltrato($versione_t, $codice1, $codice2);
                                }
                            } else {
                                $this->DecodAnacla($versione_t, $codice1 . $codice2);
                            }
                        } else {
                            $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t, $codice);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_TitolarioDecod', '');
                                } else {
                                    $this->checkCatFiltrato($versione_t, $codice);
                                }
                            } else {
                                $this->DecodAnacat($versione_t, $codice);
                            }
                        }
                        break;
                    case $this->nameForm . '_Fascod':
                        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                        $codice = $_POST[$this->nameForm . '_Fascod'];
                        if (trim($codice) != "") {
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice2 = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice2))) . trim($codice2);
                            $codice3 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t, $codice1, $codice2, $codice3);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_TitolarioDecod', '');
                                    $this->checkClaFiltrato($versione_t, $codice1, $codice2);
                                } else {
                                    $this->DecodAnafas($versione_t, $codice1 . $codice2 . $codice3, 'fasccf');
                                }
                            } else {
                                $this->DecodAnafas($versione_t, $codice1 . $codice2 . $codice3, 'fasccf');
                            }
                        } else {
                            $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                            $codice = $_POST[$this->nameForm . '_Clacod'];
                            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
                            $codice1 = str_repeat("0", 4 - strlen(trim($codice1))) . trim($codice1);
                            $codice2 = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                                $titolario_tab = $this->controllaTitolario($_POST[$this->nameForm . '_ANAPRO']['PROUOF'], $versione_t, $codice1, $codice2);
                                if (!$titolario_tab) {
                                    Out::valore($this->nameForm . '_Clacod', '');
                                    Out::valore($this->nameForm . '_Fascod', '');
                                    Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                                    Out::valore($this->nameForm . '_Organn', '');
                                    Out::valore($this->nameForm . '_FascicoloDecod', '');
                                    Out::valore($this->nameForm . '_TitolarioDecod', '');
                                    Out::valore($this->nameForm . '_CodSottofascicolo', '');
                                    Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
                                } else {
                                    $this->checkClaFiltrato($versione_t, $codice1, $codice2);
                                }
                            } else {
                                $this->DecodAnacla($versione_t, $codice1 . $codice2);
                            }
                        }
                        break;
                    case $this->nameForm . '_MettiDaFirmare':
                        if ($_POST[$this->nameForm . '_MettiDaFirmare']) {
                            Out::hide($this->nameForm . '_FirmaDocumento_field');
                        } else {
                            Out::show($this->nameForm . '_FirmaDocumento_field');
                        }
                        break;

                    case $this->nameForm . '_ANAPRO[PROCODTIPODOC]':
                        if (!$_POST[$this->nameForm . '_ANAPRO']['PROCODTIPODOC']) {
                            Out::valore($this->nameForm . '_descr_tipodoc', '');
                        } else {
                            $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST[$this->nameForm . '_ANAPRO']['PROCODTIPODOC'], 'codice');
                            if ($AnaTipoDoc_rec) {
                                Out::valore($this->nameForm . '_ANAPRO[PROCODTIPODOC]', $AnaTipoDoc_rec['CODICE']);
                                Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
                                if ($AnaTipoDoc_rec['OGGASSOCIATO']) {
                                    $this->DecodAnadog($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice');
                                }
                            } else {
                                Out::valore($this->nameForm . '_ANAPRO[PROCODTIPODOC]', '');
                                Out::valore($this->nameForm . '_descr_tipodoc', '');
                                Out::setFocus('', $this->nameForm . "_ANAPRO[PROCODTIPODOC]");
                            }
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAPRO[PROCON]':
                        $medcodOLD = App::$utente->getKey($this->nameForm . '_medcodOLD');
                        if ($_POST[$this->nameForm . '_ANAPRO']['PROCON'] != $medcodOLD) {
                            $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCON'];
                            if (trim($codice) != "") {
                                if (is_numeric($codice)) {
                                    $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                                }
                                if ($this->tipoProt == 'C') {
                                    $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                                    if (!$anamed_rec) {
                                        Out::valore($this->nameForm . '_ANAPRO[PROCON]', '');
                                        Out::valore($this->proSubMittDest . '_ANAPRO[PRONOM]', '');
                                        Out::setFocus('', $this->nameForm . "_ANAPRO[PROCON]");
                                        break;
                                    }
                                }
                                /* Se è un arrivo ripristino la sua mail senza sovrascriverla: */
                                $MailPrec = $this->formData[$this->nameForm . '_ANAPRO']['PROMAIL'];
                                $anamed_rec = $this->DecodAnamed($codice);
                                if ($this->tipoProt == 'A' && $MailPrec) {
                                    /*
                                     * Se il parametro permette la sovrascrittura della mail in Arrivo
                                     * Non lo ripristino e lascio sovrascritto.
                                     */
                                    $anaent_52 = $this->proLib->GetAnaent('52');
                                    if (!$anaent_52['ENTDE3']) {
                                        Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $MailPrec);
                                    }
                                }
                            }
                        }
                        Out::setFocus('', $this->proSubMittDest . "_ANAPRO[PRONOM]");
                        break;
                    case $this->nameForm . '_DESCOD':
                        $codice = $_POST[$this->nameForm . '_DESCOD'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . '_DESCOD', '');
                                Out::valore($this->nameForm . '_DESNOM', '');
                                Out::setFocus('', $this->nameForm . "_DESNOM");
                                break;
                            } else {
                                Out::valore($this->nameForm . '_DESCOD', $anamed_rec['MEDCOD']);
                                Out::valore($this->nameForm . '_DESNOM', $anamed_rec['MEDNOM']);

                                if ($this->anapro_record) {
                                    $anades_mitt = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
                                    if ($codice != $anades_mitt['DESCOD']) {
                                        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($this->anapro_record['PRONUM'], $this->tipoProt, true, " AND FIRCOD='{$anades_mitt['DESCOD']}'");
                                        if ($docfirma_tab) {
                                            //
                                            // Ripristina i vecchi valori
                                            //
                                            Out::valore($this->nameForm . '_DESCOD', $anades_mitt['DESCOD']);
                                            Out::valore($this->nameForm . '_DESNOM', $anades_mitt['DESNOM']);

                                            $anauff_rec = $this->proLib->GetAnauff($anades_mitt['DESCUF']);
                                            Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                                            Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                                            $docfirma_tab2 = $this->proLibAllegati->GetDocFirmaFromArcite($this->anapro_record['PRONUM'], $this->tipoProt, true, " AND FIRCOD='{$anades_mitt['DESCOD']}' AND FIRDATA<>''");
                                            if ($docfirma_tab2) {
                                                Out::msgStop('Attenzione!', 'Sono presenti documenti firmati, non è possibile cambiare il firmatario.');
                                            } else {
                                                Out::msgStop('Attenzione!', 'Sono presenti documenti alla firma impossibile cambiare il firmatario.<br>Rimuovi documenti alla firma prima di procedere.');
                                            }
                                            break;
                                        }
                                    }
                                }

                                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                                if (count($uffdes_tab) == 1) {
                                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                    Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                                    Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                                    $this->DelegatoFirmatario['CHECKCOD'] = $uffdes_tab[0]['UFFCOD'];
                                    $this->CheckDelegaFirmatario($anamed_rec['MEDCOD'], $anauff_rec['UFFCOD']);
                                } else {
                                    if ($_POST[$this->nameForm . '_DESCUF'] == '' || $_POST[$this->nameForm . '_UFFNOM'] == '') {
                                        $this->DelegatoFirmatario['CHECKCOD'] = $anamed_rec['MEDCOD'];
                                        proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                                        Out::setFocus('', "utiRicDiag_gridRis");
                                    }
                                }
                            }
                        } else {
                            Out::valore($this->nameForm . '_DESCOD', '');
                            Out::valore($this->nameForm . '_DESNOM', '');
                        }
//                        Out::setFocus('', $this->nameForm . "_DESNOM");
                        break;
                    case $this->nameForm . '_codiceOggetto':
                        $codice = str_pad(trim($_POST[$this->nameForm . '_codiceOggetto']), 4, "0", STR_PAD_LEFT);
                        $anadog_rec = $this->proLib->GetAnadog($codice);
                        if ($anadog_rec) {
                            $this->DecodAnadog($codice);
                        }
                        Out::valore($this->nameForm . '_codiceOggetto', "");
                        break;



//                    case $this->nameForm . '_ANAPRO[PROTSP]':
//                        $codice = $_POST[$this->nameForm . '_ANAPRO']['PROTSP'];
//                        if (trim($codice) != "") {
//                            if (is_numeric($codice)) {
//                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
//                            } else {
//                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
//                            }
//                            $this->DecodAnatsp($codice);
//                        } else {
//                            Out::valore($this->nameForm . '_Tspdes', "");
//                        }
//                        break;
                    case $this->nameForm . '_Propre1':
                        if (trim($_POST[$this->nameForm . '_Propre1']) != '') {
                            $codice = $_POST[$this->nameForm . '_Propre1'];
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            Out::valore($this->nameForm . "_Propre1", $codice);
                            $this->ApriRicercaProtoCollegato();
                        }
                        break;
                    case $this->nameForm . '_Propre2':
                        $this->ApriRicercaProtoCollegato();
                        break;
                    case $this->nameForm . '_ANAPRO[PROPARPRE]':
                        if (trim($_POST[$this->nameForm . '_ANAPRO']['PROPARPRE']) != '') {
                            if (trim($_POST[$this->nameForm . '_Propre2']) != '' && trim($_POST[$this->nameForm . '_Propre2']) != '0' && $this->consultazione != true) {
                                /* SE NON HO GIA VALORIZZATO QUALCOSA: ES OGGETTO E MITT/DEST. */
                                if (trim($_POST[$this->nameForm . '_Oggetto']) == '' && trim($_POST[$this->nameForm . '_ANAPRO']['PRONOM']) == '') {
                                    $codice = str_pad($_POST[$this->nameForm . '_Propre1'], 6, '0', STR_PAD_LEFT);
                                    $anno = str_pad(trim($_POST[$this->nameForm . '_Propre2']), 4, "0", STR_PAD_RIGHT);
                                    $tipo = $_POST[$this->nameForm . '_ANAPRO']['PROPARPRE'];
                                    $this->DecodificaProtoCollegato($anno . $codice, $tipo);
                                }
                            }
                        }
                        break;


                    case $this->nameForm . '_ANAPRO[PROCAP]':
                        $utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                        if (($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) && $this->tipoProt == 'A') {
                            $oggutenti_check = $this->proLib->GetOggUtenti($utenti_rec['UTELOG']);
                            if ($oggutenti_check) {
                                Out::setFocus('', $this->nameForm . "_ANAPRO[PROCON]");
                            }
                        }
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case "UPDATE_NOTE_ANAPRO":
                        if ($_POST["msgData"]["PRONUM"] && $_POST["msgData"]["PROPAR"] && $_POST["msgData"]["PRONUM"] === $this->anapro_record['PRONUM'] && $_POST["msgData"]["PROPAR"] === $this->tipoProt) {
                            $this->Modifica($this->anapro_record['ROWID']);
                        }
                        break;
                }
                break;

            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_DESNOM");
                $this->CheckDelegaFirmatario($this->DelegatoFirmatario['CHECKCOD'], $anauff_rec['UFFCOD']);
                break;

            case 'returnUfficiPerDestinatarioFirmatario2':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                Out::msgQuestion(
                        "Attenzione.", "Sono presenti documenti messi alla firma.", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_Noneffettuarenulla',
                        'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Rimuovi i documenti dalla firma' => array('id' => $this->nameForm . '_RimuoviAllaFirma',
                        'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
                break;
            case 'returnAggiungiOggetto':
                Out::valore($this->nameForm . '_Oggetto', $this->formData['NUOVOOGGETTO']['OGGETTO']);
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $this->formData['NUOVOOGGETTO']['PROCAT']);
                Out::valore($this->nameForm . '_Clacod', $this->formData['NUOVOOGGETTO']['CLACOD']);
                break;
            case 'returnFileFromTwain':
                // $this->SalvaScanner();
                $origFile = $_POST['retFile'];
                // $this->ApriRiepilogo();
                $this->verificaScanner($origFile);
                break;
            case 'returnanamed':
                $this->DecodAnamed($_POST['retKey'], 'rowid');
                break;

            case 'returnanamedMittPartenza':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_DESCOD', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESNOM", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_DESCUF", '');
                Out::valore($this->nameForm . "_UFFNOM", '');
                Out::setFocus('', $this->nameForm . "_DESCOD");
                break;

            case 'returnTitolario':
                $retTitolario = $_POST['rowData'];
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $retTitolario['CATCOD']);
                Out::valore($this->nameForm . '_Clacod', $retTitolario['CLACOD']);
                Out::valore($this->nameForm . '_Fascod', $retTitolario['FASCOD']);
                Out::valore($this->nameForm . '_TitolarioDecod', $retTitolario['DECOD_DESCR']);
                break;
            case 'returnorg':
                if (!$this->DecodAnaorg($_POST['retKey'], 'rowid')) {
                    Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                    Out::valore($this->nameForm . '_Organn', '');
                    Out::valore($this->nameForm . '_FascicoloDecod', '');
                    Out::valore($this->nameForm . '_CodSottofascicolo', '');
                    Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
                }
                break;
            case 'returnorgfas':
                $this->varAppoggio = array();
                $this->varAppoggio['ROWID'] = $_POST['retKey'];
                $sql = "SELECT PROGES.* FROM ANAORG LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY WHERE ANAORG.ROWID=" . $_POST['retKey'];
                $risultato = $this->proLib->getGenericTab($sql, false);
                if (!$risultato['GESNUM']) {
//
//  ATTENZIONE!!! NON DEVE CAPITARE, DA SISTEMARE
//
//                    $this->passoSelezionato();
                    break;
                }

                $matrice = $this->proLibFascicolo->getAlberoFascicolo($risultato['GESNUM'], array('PROTOCOLLI' => ' OR 1<>1 '));
                proric::proRicAlberoFascicolo($this->nameForm, $matrice);
//                proRicPratiche::proRicPropas($this->nameForm, " WHERE PRONUM='{$risultato['GESNUM']}' AND PROFIN='' AND PROPUB=''", '', '', true);
                break;
            case 'returnAlberoFascicolo':
                $this->varAppoggio['ROWID'] = $this->returnData['ROWID_ANAORG'];
                $fascicolo_rec = $this->proLib->GetAnaorg($this->varAppoggio['ROWID'], 'rowid');
                $anapro_rec = $this->anapro_record;
                /* Verifica Titolario Differente */
                list($base_fascicolo, $skip) = explode('.', $fascicolo_rec['ORGKEY'], 2);
                if ($anapro_rec['PROCCF'] != $base_fascicolo) {
                    $Msg = "Il titolario: <b>$base_fascicolo</b> del Fascicolo selezionato è differente da quello del protocollo.<br>";
                    $Msg .= "Confermando, inserirai il protocollo nel Fascicolo secondario selezionato.";
                    Out::msgQuestion("Fascicolazione", $Msg, array(
                        'Annulla' => array('id' => $this->nameForm . '_AnnInsFascicolo', 'model' => $this->nameForm),
                        'Conferma' => array('id' => $this->nameForm . '_ConfermaInsFascicolo', 'model' => $this->nameForm)
                            )
                    );
                    break;
                }
                $this->InserisciInFascicolo();
                break;

            case 'returnAlberoFascicoloPreProt':
                $rowidFasc = $this->returnData['ROWID_ANAORG'];
                if ($rowidFasc) {
                    $this->DatiPreProtocollazione['FASCICOLI'][$rowidFasc] = $this->returnData;

                    $pronumR = substr($this->returnData['retKey'], 4, 10);
                    $proparR = substr($this->returnData['retKey'], 14);
                    $SottoFas = '';
                    if ($proparR == 'N') {
                        $AnaproSottoFascicolo_rec = $this->proLib->GetAnapro($pronumR, 'codice', $proparR);
                        $SottoFas = str_replace($AnaproSottoFascicolo_rec['PROFASKEY'] . '-', '', $AnaproSottoFascicolo_rec['PROSUBKEY']);
                    }
                    $Anaorg_rec = $this->proLib->GetAnaorg($this->returnData['ROWID_ANAORG'], 'rowid');
                    $DescFascicolo = '<div class="ita-html"><span class="ita-tooltip" title="' . $Anaorg_rec['ORGDES'] . '">';
                    $DescFascicolo .= $Anaorg_rec['ORGKEY'] . ' : ' . $Anaorg_rec['ORGDES'];
                    $DescFascicolo .= '</span></div>';

                    $this->ElencoFascicoli[$rowidFasc] = array('ICON_FAS' => '', 'SOTTOFAS' => $SottoFas, 'FASCICOLO' => $DescFascicolo);
                    $this->CaricaGriglia($this->gridFascicoli, $this->ElencoFascicoli);
                }
                break;

            case 'returnPropas':
                $sql = "SELECT PROGES.GESNUM FROM ANAORG LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY WHERE ANAORG.ROWID=" . $this->varAppoggio['ROWID'];
                $proges_rec = $this->proLib->getGenericTab($sql, false);
                if (!$proges_rec) {
                    Out::msgStop("Fascicolazione Azione Pratica", "Errore di accesso alla pratica.");
                    break;
                }
                $anapro_rec = $this->proLib->GetAnapro($this->proArriIndice, 'rowid');
                if (!$anapro_rec) {
                    Out::msgStop("Fascicolazione Azione Pratica", "Errore di accesso al docimento da fascicolare.");
                    break;
                }
                $model = 'proPassoPratica';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $appoggioPost = $_POST;
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['listaAllegati'] = array();
                $_POST['perms'] = $this->perms;
                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnMethod'] = 'returnProPassoPratica';
                $_POST[$model . '_title'] = 'Gestione Azione proveniente dalla pratica: ' . (int) substr($proges_rec['GESNUM'], 14, 6) . "/" . substr($proges_rec['GESNUM'], 10, 4);
                $datiInfo = "Caricamento Azione attività per la pratica " . (int) substr($proges_rec['GESNUM'], 14, 6) . "/" . substr($proges_rec['GESNUM'], 10, 4);
                if ($appoggioPost['retKey'] == '') {
                    $datiInfo .= '<br>Inserire i dati dell\'Azione prima di Aggiungere.';
                    $_POST['procedimento'] = $proges_rec['GESNUM'];
                    $_POST['modo'] = "add";
                } else {
                    $_POST['rowid'] = $appoggioPost['retKey'];
                    $_POST['modo'] = "edit";
                }
                $_POST[$model . '_fascicolaDocumento'] = array(
                    "PRONUM" => $anapro_rec['PRONUM'],
                    "PROPAR" => $anapro_rec['PROPAR']
                );
                $_POST['datiInfo'] = $datiInfo;
                $model();
                break;
            case 'returnProPassoPratica':
                $this->passoSelezionato();
                break;
            case 'returndog':
                switch ($_POST['retid']) {
                    case 'DecodOggetto':
                        $this->DecodAnadog($_POST['retKey'], 'rowid');
                        break;
                    case 'ANAPRO[PROINCOGG]_butt':
                        /* PATCH PROGRE */
                        $anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
                        if ($anadog_rec) {
                            Out::valore($this->nameForm . '_ANAPRO[PRODOGCOD]', $anadog_rec['DOGCOD']);
                            Out::valore($this->nameForm . '_codiceOggetto', $anadog_rec['DOGCOD']);
                            Out::valore($this->nameForm . '_ANAPRO[PROINCOGG]', 'IN PRENOTAZIONE');
                            Out::setFocus('', $this->nameForm . '_codiceOggetto');
                        }
                        /* FINE PATCH PROGRE */
                        break;
//                        $anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
//                        if ($anadog_rec && $this->anapro_record) {
//                            $this->PrenotaAggiornaCodOggettoAnapro($this->anapro_record['ROWID'], $anadog_rec['DOGCOD']);
//                        }
                        break;
                }
                break;
            case 'returnTitolarioFiltrato':
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $_POST['rowData']['CATCOD']);
                Out::valore($this->nameForm . '_Clacod', $_POST['rowData']['CLACOD']);
                Out::valore($this->nameForm . '_Fascod', $_POST['rowData']['FASCOD']);
                Out::valore($this->nameForm . '_TitolarioDecod', $_POST['rowData']['DESCRIZIONE']);
                break;
//            case 'returnanatsp':
//                $this->DecodAnatsp($_POST['retKey'], 'rowid');
//                break;
            case 'returnNumAnte':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                $this->DecodificaProtoCollegato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                // $this->setRiscontro($anapro_rec);
                break;
            case 'returnDuplica':
                $this->duplicaAllegati = true;
                $anaent_48 = $this->proLib->GetAnaent('48');
                if ($anaent_48['ENTDE6'] != 1) {
                    $this->duplicaAllegati = false;
                }
                $this->duplicaDoc($_POST['retKey']);
                break;
            case 'returnaOggUtenti':
                $oggutenti_check = $this->proLib->GetOggUtenti($_POST['retKey'], '', 'rowid');
                $this->DecodAnadog($oggutenti_check['DOGCOD']);
                $this->disabilitaFormPerOggetto();
                break;

            case 'returnMail':
                $destinatari = array();
                $destinatariKey = explode(',', $_POST['valori']['Destinatari']);

                foreach ($destinatariKey as $key => $value) {
                    $destinatari[] = $this->destMap[$value];
                }
                $anapro_rec = $this->proLib->GetAnapro($this->proArriIndice, 'rowid');
                if ($_POST['valori']['Inviata']) {
                    $valori = array(
                        'destMap' => $destinatari,
                        'Oggetto' => $_POST['valori']['Oggetto'],
                        'Corpo' => $_POST['valori']['Corpo'],
                        'allegati' => $_POST['allegati']
                    );
                    $ForzaDaMail = '';
                    if ($_POST['valori']['ForzaDaMail']) {
                        $ForzaDaMail = $_POST['valori']['ForzaDaMail'];
                    }
                    /*
                     * Carico Altri Destinatari da subform
                     */
                    $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
                    $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
                    $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
                    /*
                     * Carico ALtri Destinatari Interni
                     */
                    $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
                    $proArriDest = $proSubTrasmissioni->getProArriDest();

                    $result = $this->proLibMail->servizioInvioMail($this, $valori, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $this->mittentiAggiuntivi, $proArriDest, $this->proAltriDestinatari, $ForzaDaMail);
//                    $this->servizioInvioMail($valori, $anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                    if (!$result) {
                        Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                        break;
                    }
                    if (!$this->proLibAllegati->CheckInvioFatturaSpacchettata($anapro_rec)) {
                        Out::msgStop("Attenzione - SDIP", $this->proLibAllegati->getErrMessage());
                    }
                    $this->Modifica($anapro_rec['ROWID']);
                    $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
                    $proSubMittDest->Decod($anapro_rec);

                    $this->destMap = array();
                }
                $datipost = $this->fileDaPEC['DATIPOST'];
                if ($datipost) {
                    $this->returnGestioneMail($anapro_rec['PRONUM'], $datipost);
                }
                break;
            case 'returntoform':
                switch ($_POST['retField']) {
                    case $this->nameForm . '_CaricaGridPart':
                        $proDettCampi = $_POST['proDettCampi'];
                        $salvaDest = array();
                        $salvaDest['PROPAR'] = $salvaDest['DESPAR'] = $this->tipoProt;
                        $salvaDest['DESCOD'] = $proDettCampi['destCodice'];
                        $salvaDest['PRONOM'] = $salvaDest['DESNOM'] = $proDettCampi['destNome'];
                        $salvaDest['PROIND'] = $salvaDest['DESIND'] = $proDettCampi['destInd'];
                        $salvaDest['PROCAP'] = $salvaDest['DESCAP'] = $proDettCampi['destCap'];
                        $salvaDest['PROCIT'] = $salvaDest['DESCIT'] = $proDettCampi['destCitta'];
                        $salvaDest['PROPRO'] = $salvaDest['DESPRO'] = $proDettCampi['destProv'];
                        $salvaDest['PRODAR'] = $salvaDest['DESDAT'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESDAA'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESDUF'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESANN'] = $proDettCampi['destAnn'];
                        $salvaDest['DESMAIL'] = $proDettCampi['email'];
                        $salvaDest['DESTSP'] = $proDettCampi['destProtsp'];
                        $salvaDest['DESFIS'] = $proDettCampi['destFis'];
                        $salvaDest['DESCUF'] = '';
                        $salvaDest['DESGES'] = 1;
                        $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                        if ($proDettCampi['destRowid'] != '') {
                            $this->proAltriDestinatari[$proDettCampi['destRowid']] = $salvaDest;
                        } else {
                            $this->proAltriDestinatari[] = $salvaDest;
                        }
                        $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                        if (!$this->consultazione) {
                            if ($this->disabledRec) {
                                Out::show($this->nameForm . "_Registra");
                            }
                        }
                        break;
                    case 'proMsgQuestion_Nuovo':
                    case 'proMsgQuestion_ConfermaNuovo':
                        $this->Nuovo();
                        break;
                    case 'proMsgQuestion_Ricevuta':
                        $this->ricevuta();
                        break;
                    case 'proMsgQuestion_Chiudi':
                    case 'proMsgQuestion_ConfermaChiudi':
                        $this->returnToParent(true);
                        break;
                    case 'proMsgQuestion_Scanner':
                        $_POST = $_POST['postAppoggio'];
                        $_POST[$this->nameForm . '_ANAPRO']['ROWID'] = $this->rowidAppoggio;
                        $this->setModelData($_POST);
                        $this->ApriScanner();
                        break;
                    case 'proMsgQuestion_Etichetta':
                        $this->stampaEtichetta();
                        break;
                    case 'proMsgQuestion_Raccomandata':
                        $this->inviaRaccomandataDestinatari($this->rowidAppoggio);
                        break;
                    case 'proMsgQuestion_Notifica':
                        $this->inviaMailDestinatari($this->rowidAppoggio);
                        break;
                    case 'proMsgQuestion_NotificaMittenti':
                        $this->inviaMailMittenti($this->rowidAppoggio);
                        break;
                    case 'proMsgQuestion_FileLocale':
                        $_POST = $_POST['postAppoggio'];
                        $this->verificaUpload();
//                        $this->ApriRiepilogo();
                        break;
                    case 'proMsgQuestion_DuplicaProt':
                        $this->CheckDuplicaProt($this->rowidAppoggio);
                        break;
                }
                break;
            case 'returnaAnagra':

                break;
            case 'returnanauff':
                echo $anauff_rec['UFFDES'] . "|" . $this->nameForm . '_Uff_cod' . "|" . $anauff_rec['UFFCOD'] . "\n";
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . '_Uff_cod');
                break;
            case 'returnProDettNote':
                $rowidAnapro = $this->formData["{$this->nameForm}_ANAPRO"]['ROWID'];
                $anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                $pronum = $anapro_rec['PRONUM'];
                $propar = $anapro_rec['PROPAR'];
                $dati = array(
                    'OGGETTO' => $_POST['oggetto'],
                    'TESTO' => $_POST['testo'],
                    'CLASSE' => proNoteManager::NOTE_CLASS_PROTOCOLLO,
                    'CHIAVE' => array('PRONUM' => $pronum, 'PROPAR' => $propar)
                );

                $tipoNotifica = "CARICATA";
                if ($_POST['NON_AGGIORNA'] !== true) {
                    if ($_POST['NOTE_ROWID'] === '') {
                        $tipoNotifica = "INSERITA";
                        $this->noteManager->aggiungiNota($dati);
                    } else {
                        $tipoNotifica = "MODIFICATA";
                        $this->noteManager->aggiornaNota($_POST['NOTE_ROWID'], $dati);
                    }
                    $this->noteManager->salvaNote();
                }
                $this->messaggioBroadcast($dati['CLASSE'], $dati['CHIAVE']);
                $Oggetto = $_POST['oggetto'] . "\n" . $_POST['testo'];
                foreach ($_POST['destinatari'] as $destinatario) {
                    $this->inserisciNotifica($Oggetto, $destinatario, $tipoNotifica, $pronum);
                }
                $this->caricaNote();
                break;
            case "returnFromSignAuth";
                //Sposto il file dalla cartella temporanea:
                if ($_POST['result'] === true) {
                    $allegato = $this->varAppoggio;
//                  Non cancello più il file..
//                    if (!@unlink($allegato['FILEPATH'])) {
//                        Out::msgStop("Firma remota", "cancellazione file " . $allegato['FILEPATH'] . " fallita");
////                        break;
//                    }
                    $anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], "ROWID");
                    $protPath = $this->proLib->SetDirectory($this->anapro_record['PRONUM'], $this->tipoProt);
                    $outputFilePath = $_POST['outputFilePath'];
                    $OutputFileName = $_POST['outputFileName'];
                    $FilenameFirmato = $_POST['fileNameFirmato'];
                    /*
                     *  Se ho l'outputFileName lo uso, altrimenti me lo devo creare
                     * @TODO prevedere per una futura apposizione di firma al file già firamto.
                     */
//                    if (!$OutputFileName) {
                    $fileName = $anadoc_rec['DOCNAME'] . '.p7m';
                    $FileNameDest = $anadoc_rec['DOCFIL'] . '.p7m';
//                    } else {
//                        $fileName = $OutputFileName;
//                        $FileNameDest = pathinfo($outputFilePath, PATHINFO_BASENAME);
//                    }
                    $FileDest = $protPath . "/" . $FileNameDest;
                    // Sposto dalla cartella temporanea alla cartella del prot.
                    if (!@rename($outputFilePath, $FileDest)) {
                        Out::msgStop("Attenzione", "Errore in salvataggio del file " . $fileName . " !");
                        return false;
                    }
                    /*
                     *  Inserisco l'anadocsave
                     */
                    $savedata = date('Ymd');
                    $saveora = date('H:i:s');
                    $saveutente = App::$utente->getKey('nomeUtente');
                    $anadocSave_rec = $anadoc_rec;
                    $anadocSave_rec['ROWID'] = '';
                    $anadocSave_rec['SAVEDATA'] = $savedata;
                    $anadocSave_rec['SAVEORA'] = $saveora;
                    $anadocSave_rec['SAVEUTENTE'] = $saveutente;
                    if (!$this->insertRecord($this->PROT_DB, 'ANADOCSAVE', $anadocSave_rec, '', 'ROWID', false)) {
                        Out::msgStop("Firma File", "Errore in salvataggio ANADOCSAVE.");
                        return false;
                    }
                    $anadoc_rec['DOCUUID'] = '';
                    /* Se attivo parametri alfresco - salvo su alfresco */
                    $anaent_49 = $this->proLib->GetAnaent('49');
                    if ($anaent_49['ENTDE1']) {
                        $anapro_rec = $this->proLib->getAnapro($anadoc_rec['DOCNUM'], 'codice', $anadoc_rec['DOCPAR']);
                        $Uuid = $this->proLibAllegati->AggiungiAllegatoAlfresco($anapro_rec, $FileDest, $FilenameFirmato);
                        if (!$Uuid) {
                            Out::msgStop('Attenzione', 'Errore in salvataggio file firmato.');
                            return false;
                        }
                        $anadoc_rec['DOCUUID'] = $Uuid;
                    }
                    /*
                     * Salvataggio Anapro 
                     */
                    $anadoc_rec['DOCFIL'] = pathinfo($FileDest, PATHINFO_BASENAME);
                    $anadoc_rec['DOCLNK'] = "allegato://" . pathinfo($FileDest, PATHINFO_BASENAME);
                    //$anadoc_rec['DOCNAME'] = $fileName;
                    $anadoc_rec['DOCNAME'] = $FilenameFirmato;
//                    $anadoc_rec['DOCNOT'] = $fileName;
                    $anadoc_rec['DOCDATAFIRMA'] = date("Ymd");
                    $anadoc_rec['DOCMD5'] = md5_file($FileDest);
                    $anadoc_rec['DOCSHA2'] = hash_file('sha256', $FileDest);
                    $update_Info = 'Oggetto: Aggiornamento allegato ' . $allegato['FILENAME'];
                    if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_rec, $update_Info)) {
                        return false;
                    }
//                    Out::openDocument(utiDownload::getUrl($_POST['inputFileName'] . ".p7m", $_POST['outputFilePath']));
                    $this->CaricaAllegati();
                } elseif ($_POST['result'] === false) {
                    Out::msgStop("Firma remota", "Firma Fallita");
                }
                break;

            case "returnAnaTipoDoc":
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ANAPRO[PROCODTIPODOC]', $AnaTipoDoc_rec['CODICE']);
                Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
                //Controllare se campi già valorizzati non automatizza nulla?
                if ($AnaTipoDoc_rec['OGGASSOCIATO']) {
                    $this->DecodAnadog($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice');
                }
                break;

            case "returnAnadocServizio":
                $anadoc_rec = $this->proLib->GetAnadoc($_POST['retKey'], 'rowid');
                if ($anadoc_rec) {
                    $this->varAppoggio = $anadoc_rec;
                    $toggleAllegati = $this->StatoToggle['toggleAllegati'];
                    if (strtolower(pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'xhtml' && $toggleAllegati == 'abilita') {
                        Out::msgQuestion("Testo base.", "Cosa vuoi fare con il Testo Base Selezionato ?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaTestoBase', 'model' => $this->nameForm),
                            'Cancella' => array('id' => $this->nameForm . '_CancellaTestoBaseServizio', 'model' => $this->nameForm),
                            'Scarica' => array('id' => $this->nameForm . '_ScaricaTestoBaseServizio', 'model' => $this->nameForm)
                                )
                        );
                    } else {
                        if (strtolower(pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'eml') {
                            /* Se è eml associata tramite protocolla mail, posso ripristinare da protocollare */
                            if ($anadoc_rec['DOCIDMAIL']) {
                                include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                                $emlLib = new emlLib();
                                $mail_rec = $emlLib->getMailArchivio($anadoc_rec['DOCIDMAIL'], 'id');
                                if ($mail_rec['PECTIPO'] <> 'accettazione' && $mail_rec['PECTIPO'] <> 'avvenuta-consegna') {
                                    Out::msgQuestion("Mail Associata.", "Questa mail risulta associata tramite Protocollazine da Mail.<br>Cosa vuoi fare?", array(
                                        'Ripristina Mail da protocollare' => array('id' => $this->nameForm . '_RipristinaMailDaProt', 'model' => $this->nameForm),
                                        'Scarica' => array('id' => $this->nameForm . '_ScaricaMail', 'model' => $this->nameForm)
                                    ));
                                    break;
                                }
                            }
                        }
                        $this->proLibAllegati->OpenDocAllegato($anadoc_rec['ROWID']);
                    }
                }
                break;

            case 'suggest':
                if ($this->consultazione != true) {
                    switch ($_POST['id']) {
                        case $this->nameForm . '_Oggetto':
                            /* new suggest */
                            $COMUNI_DB = $this->proLib->getCOMUNIDB();
                            $q = itaSuggest::getQuery();
                            //itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $COMUNI_DB->strUpper('DOGDEX') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANADOG WHERE " . $where;
                            $anadog_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
                            if (count($anadog_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anadog_tab as $anadog_rec) {
                                    itaSuggest::addSuggest($anadog_rec['DOGDEX'], array($this->nameForm . "_codiceOggetto" => $anadog_rec['DOGCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                        case $this->nameForm . '_Dest_nome':
                            $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANAMED WHERE MEDANN=0 AND $filtroUff AND " . $where;
                            $anamed_tab = $this->proLib->getGenericTab($sql);
                            if (count($anamed_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anamed_tab as $anamed_rec) {
                                    itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_Dest_cod" => $anamed_rec['MEDCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                        case $this->nameForm . '_DESNOM':
                            $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANAMED WHERE MEDANN=0 AND $filtroUff AND " . $where;
                            $anamed_tab = $this->proLib->getGenericTab($sql);
                            if (count($anamed_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anamed_tab as $anamed_rec) {
                                    itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_DESCOD" => $anamed_rec['MEDCOD'], $this->nameForm . "_DESCUF" => '', $this->nameForm . "_UFFNOM" => ''));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                        case $this->nameForm . '_AltriDestNome':
                            $this->suggestAnamed('altri');
                            break;
                        case $this->nameForm . '_DESNOM':
                            $this->suggestAnamed('altri');
                            break;
                        case $this->nameForm . '_Uff_des':
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANAUFF WHERE " . $where;
                            $anauff_tab = $this->proLib->getGenericTab($sql);
                            if (count($anauff_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anauff_tab as $anauff_rec) {
                                    itaSuggest::addSuggest($anauff_rec['UFFDES'], array($this->nameForm . "_Uff_cod" => $anauff_rec['UFFCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                        case $this->nameForm . '_serdes':
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('SERDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANASERVIZI WHERE " . $where;
                            $anaservizi_tab = $this->proLib->getGenericTab($sql);
                            if (count($anaservizi_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anaservizi_tab as $anaservizi_rec) {
                                    itaSuggest::addSuggest($anaservizi_rec['SERDES'], array($this->nameForm . "_sercod" => $anaservizi_rec['SERCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                    }
                }
                break;

            case 'returnProtCollegati':
                $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $_POST['retKey']);
                if (!$anaproctr_rec) {
                    Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
                    break;
                }
                $this->Modifica($_POST['retKey']);
                break;

            case 'returnRicProtoCollegato':
                $rowid = $_POST['rowData']['ROWID'];
                $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
                $this->DecodificaProtoCollegato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                break;

            case 'returnAllegaDaP7m':
                $this->AllegaDaP7m();
                break;

            case 'returnDocumenti':
                $rowidAnadocPdf = $this->proLibAllegati->caricaTestoBase($this, $this->anapro_record, $_POST['retKey'], 'rowid');
                if (!$rowidAnadocPdf) {
                    Out::msgInfo('Attenzione', $this->proLibAllegati->getErrMessage());
                    break;
                }
                $this->CaricaAllegati();
                Out::show($this->nameForm . '_AllegatiServizio');
//                // Apro il documento appena creato. 
                $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadocPdf, 'rowid');
                if ($Anadoc_rec) {
                    $this->varAppoggio = $Anadoc_rec;
                    $AnadocXhtm_rec = $this->proLib->GetAnadoc($Anadoc_rec['DOCROWIDBASE'], 'rowid');
                    if ($AnadocXhtm_rec) {
                        $this->proLibAllegati->ApriTestoBaseAnadoc($AnadocXhtm_rec, $this->nameForm, 'TestoBase');
                    }
                }
                break;

            case 'returnEditDiagTestoBase':
                $newContent = $_POST['returnText'];
                $RowidPDF = $this->varAppoggio['ROWID'];
                if (!$this->proLibAllegati->AggiornaTestoBase($newContent, $RowidPDF)) {
                    Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                } else {
                    $messSegnatura = '';
                    $docMeta = unserialize($this->varAppoggio['DOCMETA']);
                    if ($docMeta['SEGNATURA'] === true) {
                        $messSegnatura = '<br>La segnatura applicata in precedenza è stata rimossa.';
                    }
                    Out::msgBlock('', 3000, true, '<div style="font-size:1.3em;">Testo base generato correttamente.' . $messSegnatura . '</div>');
                }
                $this->CaricaAllegati();
                break;

            case 'returnMultiselectAllegati':
                /* Controllo se ha selezionato almento un allegato..
                 * Se non ha selezionato nulla retKey torna vuoto.
                 */
                if ($_POST['retKey'] == '') {
                    break;
                }
                $proArriAlle = array();
                if (isset($_POST['rowData']['ROWID'])) {
                    $proArriAlle[0] = $_POST['rowData'];
                } else {
                    $proArriAlle = $_POST['rowData'];
                }

                if ($proArriAlle) {
                    $this->proArriAlle = $proArriAlle;
                    $this->ControllaAllegati();
                    $this->caricaGrigliaAllegati();
                    Out::show($this->nameForm . '_paneAllegati');
                    Out::block($this->nameForm . '_paneAllegati');
                }
                $this->CtrAllegatiObbligatori();
                break;

            case 'returnMultiSelezioneFascicolo':
                $FascicoliSelezionati = $_POST['FASCICOLI_SELEZIONATI'];
                if (!$this->proLibFascicolo->FascicolaProtInElencoFascicoli($this, $this->anapro_record, $FascicoliSelezionati)) {
                    Out::msgStop('Attenzione', 'Errore in fascicolazione.<br>' . $this->proLibFascicolo->getErrMessage());
                    $this->Modifica($this->anapro_record['ROWID']);
                    break;
                }
                Out::msgInfo('Fascicolazione', 'Protocollo fascicolato correttamente.');
                $this->Modifica($this->anapro_record['ROWID']);
                break;

            case 'returnEFAS':
                $RowData = $_POST['rowData'];
                $this->Modifica($RowData['ROWID']);
                $ret = $this->proLibSdi->openRiscontroFattura($RowData);
                if (!$ret) {
                    Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                }
                break;

            case 'returnCopiaAllegatiProt':
                /* Controllo se ha selezionato almento un allegato..
                 * Se non ha selezionato nulla retKey torna vuoto.
                 */
                if ($_POST['retKey'] == '') {
                    break;
                }
                $proArriAlle = $_POST['rowData'];
                if ($proArriAlle) {
                    foreach ($proArriAlle as $Allegato) {
                        $this->proArriAlle[] = $Allegato;
                    }
                    $this->ControllaAllegati();
                    if ($this->anapro_record) {
                        $this->GestioneAllegati($this->anapro_record['PRONUM']);
                    }
                    $this->CaricaAllegati();
                    Out::msgBlock('', 2000, true, 'Allegati copiati correttamente.');
                }
                break;

            case $this->nameForm . '_VaiAdAtto':
                include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
                include_once ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php';
                $segLib = new segLib();
                $segLibDocumenti = new segLibDocumenti();
                $id = $_POST['id'];
                $Indice_rec = $segLib->GetIndice($id, 'rowid');
                $segLibDocumenti = new segLibDocumenti();
                if (!$segLibDocumenti->ApriAtto($this->nameForm, $Indice_rec)) {
                    Out::msgStop("Attenzione", $segLibDocumenti->getErrMessage());
                }
                break;

            case 'returnScannerShared':
                $ErroreAllegati = array();
                $RetList = $_POST['retList'];
                /*
                 * Salvataggio degli Allegati.
                 */
                foreach ($RetList as $uplAllegato) {
                    $destFile = $uplAllegato['FILEPATH'];
                    $fileInfo = $uplAllegato['FILEINFO'];
                    $docName = $uplAllegato['FILEORIG'];

                    foreach ($this->proArriAlle as $proArriAllegato) {
                        if ($proArriAllegato['DOCTIPO'] == '') {
                            $tipo = 'ALLEGATO';
                        }
                    }
                    $proArriAllePre = $this->proArriAlle;
                    $this->proArriAlle[] = array(
                        'ROWID' => 0,
                        'FILEPATH' => $destFile,
                        'FILENAME' => pathinfo($destFile, PATHINFO_BASENAME),
                        'NOMEFILE' => $docName,
                        'FILEINFO' => $fileInfo,
                        'DOCNAME' => $docName,
                        'DOCTIPO' => $tipo,
                        'DOCFDT' => date('Ymd'),
                        'DOCRELEASE' => '1',
                        'DOCSERVIZIO' => 0
                    );
                    /* Se allegati obbligatori e non è presente un anapro_record:
                     * Si stanno inserendo allegati provvisori.
                     */
                    $anaent_32 = $this->proLib->GetAnaent('32');
                    if ($anaent_32['ENTDE4'] == '2' && !$this->anapro_record) {
                        $this->CaricaAllegati();
                        continue;
                    }

                    if (!$this->proLibAllegati->ControlloAllegatiPreProtocollo($this->proArriAlle)) {
                        // Qui prevedere una pulizia dell'ultimo record inserito.. se ha dato errore deve essere escluso.
                        $this->proArriAlle = $proArriAllePre;
                        $Errore = array();
                        $Errore['FILE'] = $docName;
                        $Errore['MESSAGGIO'] = $this->proLibAllegati->getErrMessage();
                        $ErroreAllegati[] = $Errore;
                        continue;
                    }
                }
                /* 1. Controllo Tipologie Allegati - Riordina */
                $this->proArriAlle = $this->proLibAllegati->ControlloAllegatiProtocollo($this->proArriAlle, $this->currObjSdi);
                /* Elaborazione Errori ALlegati */
                $Msg = '';
                if ($ErroreAllegati) {
                    $Msg = "I seguenti allegati non sono stati caricati:<br>" . '<div style="margin-left:10px; color:red;">';
                    foreach ($ErroreAllegati as $Allegato) {
                        $Msg .= 'File: ' . $Allegato['FILE'] . ' <br>Errore: ' . $Allegato['MESSAGGIO'] . "<br><br>";
                    }
                    $Msg .= "</div>";
                }
                $anaent_32 = $this->proLib->GetAnaent('32');
                if ($anaent_32['ENTDE4'] == '2' && !$this->anapro_record) {
                    $this->CaricaAllegati();
                    break;
                }
                /*
                 * Salvataggio degli allegati.
                 */
                $risultato = $this->proLibAllegati->GestioneAllegati($this, $this->anapro_record['PRONUM'], $this->tipoProt, $this->proArriAlle, $this->anapro_record['PROCON'], $this->anapro_record['PRONOM']);
                if (!$risultato) {
                    Out::msgStop('Attenzione', $this->proLibAllegati->getErrMessage() . "<br>" . $Msg);
                } else {
                    if ($ErroreAllegati) {
                        Out::msgInfo('Caricamento Allegati', $Msg);
                    }
                }

                $this->ricaricaFascicolo();
                $this->CaricaAllegati();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_proDestinatari');
        App::$utente->removeKey($this->nameForm . '_proAltriDestinatari');
        App::$utente->removeKey($this->nameForm . '_proArriDest');
        App::$utente->removeKey($this->nameForm . '_proArriUff');
        App::$utente->removeKey($this->nameForm . '_proArriAlle');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_annullato');
        App::$utente->removeKey($this->nameForm . '_proArriIndice');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_varAppoggio');
        App::$utente->removeKey($this->nameForm . '_consultazione');
        App::$utente->removeKey($this->nameForm . '_fileDaPEC');
        App::$utente->removeKey($this->nameForm . '_disabledRec');
        App::$utente->removeKey($this->nameForm . '_Proric_parm');
        App::$utente->removeKey($this->nameForm . '_inviaConfermaMail');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_medcodOLD');
        App::$utente->removeKey($this->nameForm . '_mittentiAggiuntivi');
        App::$utente->removeKey($this->nameForm . '_destMap');
        App::$utente->removeKey($this->nameForm . '_prouof');
        App::$utente->removeKey($this->nameForm . '_currDescod');
        App::$utente->removeKey($this->nameForm . '_emergenza');
        App::$utente->removeKey($this->nameForm . '_bloccoDaEmail');
        App::$utente->removeKey($this->nameForm . '_altriDestPerms');
        App::$utente->removeKey($this->nameForm . '_noteManager');
        App::$utente->removeKey($this->nameForm . '_datiPasso');
        App::$utente->removeKey($this->nameForm . '_anapro_record');
        App::$utente->removeKey($this->nameForm . '_profilo');
        App::$utente->removeKey($this->nameForm . '_currObjSdi');
        App::$utente->removeKey($this->nameForm . '_varMarcatura');
        App::$utente->removeKey($this->nameForm . '_datiUtiP7m');
        App::$utente->removeKey($this->nameForm . '_StatoToggle');
        App::$utente->removeKey($this->nameForm . '_returnData');
        App::$utente->removeKey($this->nameForm . '_datiFascicolazine');
        App::$utente->removeKey($this->nameForm . '_duplicaAllegati');
        App::$utente->removeKey($this->nameForm . '_ElencoFascicoli');
        App::$utente->removeKey($this->nameForm . '_FascicoloDiProvenienza');
        App::$utente->removeKey($this->nameForm . '_ProtocollaSimple');
        App::$utente->removeKey($this->nameForm . '_ProtAllegaDaFascicolo');
        App::$utente->removeKey($this->nameForm . '_DelegatoFirmatario');
        App::$utente->removeKey($this->nameForm . '_DatiPreProtocollazione');
        App::$utente->removeKey($this->nameForm . '_CtrDatiRichiestiProt');
        App::$utente->removeKey($this->nameForm . '_LockIdMail');
        App::$utente->removeKey($this->nameForm . '_proSubMittDest');
        App::$utente->removeKey($this->nameForm . '_proSubTrasmissioni');
        App::$utente->removeKey($this->nameForm . '_rowidAnaproDuplicatoSorgente');
        itaLib::deletePrivateUploadPath();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
//        Out::show('proGest');
//        Out::setFocus('', 'proGest_Dal_prot');
    }

    public function OpenRicerca() {
        $this->returnToParent();
    }

    public function Nuovo() {
        //Out::show($this->nameForm . '_CercaProtPre');
        $this->rowidAppoggio = '';
        // $this->rowidAnaproDuplicatoSorgente = '';
        $this->datiFascicolazine = array();
        $this->StatoToggle = array(); // Nuova Funzione toggle
        $this->prouof = $this->proLib->caricaUof($this);
        $this->tipoProt = $this->tipoProt;
        Out::show($this->nameForm . '');
        $this->toggleAll('abilita');
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Registra');
        $this->AbilitaCampi();
        $anaent_3 = $this->proLib->GetAnaent('3');

        //Out::valore($this->nameForm . '_ANAPRO[PROUTE]', App::$utente->getKey('nomeUtente'));
        Out::valore($this->nameForm . '_UTENTEORIGINARIO', '');
        Out::valore($this->nameForm . '_UTENTEULTIMO', '');
        Out::show($this->nameForm . '_DuplicaProt');
        $this->setMittPartenzaDefault();

        Out::attributo($this->nameForm . '_ANAPRO[PRONOTE]', "readonly", '1');
        Out::hide($this->nameForm . '_ModificaNota');

        $this->disabledRec = false;
// Possibile modifica:        
        $this->consultazione = false;
        $profilo = proSoggetto::getProfileFromIdUtente();
        if (($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) && $this->tipoProt == 'A') {
            if ($profilo['OGG_UTENTE']) {
                if (count($profilo['OGG_UTENTE']) == 1) {
                    $this->DecodAnadog($profilo['OGG_UTENTE'][0]['DOGCOD']);
                    $this->disabilitaFormPerOggetto();
                } else {
                    proRic::proRicOggUtenti($this->nameForm, " WHERE UTELOG='" . $profilo['UTELOG'] . "'");
                }
            }
        }
        if ($profilo['NO_RISERVATO'] == 0) {
            Out::show($this->nameForm . '_Riserva');
        }

        $this->statoFascicolo('');
        $this->bloccoTitolario($this->prouof);
        if ($this->emergenza) {
            Out::addClass($this->nameForm . '_ANAPRO[PROEME]', "required");
            Out::addClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
            Out::addClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
            Out::addClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
        } else {
            Out::delClass($this->nameForm . '_ANAPRO[PROEME]', "required");
            Out::delClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
            Out::delClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
            Out::delClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
        }
        Out::show($this->nameForm . '_FascicolaPre');
        //
        //
        // Focus finale
        //
        $this->CtrAllegatiObbligatori();
        Out::setFocus('', $this->nameForm . '_Oggetto');
        // proSubMittDest
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->setTipoProt($this->tipoProt);
        $proSubMittDest->Nuovo();
        //proSubTrasmissioni
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proSubTrasmissioni->setTipoProt($this->tipoProt);
        $proSubTrasmissioni->Nuovo();
        $this->CheckUtilizzoDocAllaFirma();
    }

    public function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->nameForm . '_paneArrivi');
        Out::clearFields($this->nameForm, $this->nameForm . '_divMittPartenza');
        Out::clearFields($this->nameForm, $this->nameForm . '_paneDest');
        Out::clearFields($this->nameForm, $this->nameForm . '_paneAllegati');
        Out::clearFields($this->nameForm, $this->nameForm . '_divAppoggio');
        /*
         * Set titolario corrente di lavoro
         */
        Out::valore($this->nameForm . '_ANAPRO[VERSIONE_T]', $this->proLib->GetTitolarioCorrente());
        Out::hide($this->nameForm . '_VersioneTitolario');
//        Out::msgInfo('titolario',$this->proLib->GetTitolarioCorrente());
        //todo: azzerare prodar
        Out::valore($this->nameForm . '_ANAPRO[PRODAR]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROORA]', '');
        Out::valore($this->nameForm . '_Pronum', '');
        Out::valore($this->nameForm . '_span1', '');
        Out::valore($this->nameForm . '_protStato', '');
        //Out::valore($this->nameForm . '_ANAPRO[PROUTE]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROSEG]', '');
        Out::valore($this->nameForm . '_ANAPRO[PRONOTE]', '');
        Out::valore($this->nameForm . '_ANAPRO[PRORDA]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROROR]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROEME]', '');
        Out::valore($this->nameForm . '_ANAPRO[PRODATEME]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROORAEME]', '');
        Out::valore($this->nameForm . '_ANAPRO[PROSEGEME]', '');
        $profilo = proSoggetto::getProfileFromIdUtente();
        Out::valore($this->nameForm . '_ANAPRO[PROSECURE]', $profilo['LIV_PROTEZIONE']);
        Out::valore($this->nameForm . '_UTENTEORIGINARIO', '');
        Out::valore($this->nameForm . '_UTENTEATTUALE', App::$utente->getKey('nomeUtente'));
        $this->varAppoggio = '';
        $this->destMap = array();
        $this->annullato = false;
        $this->bloccoDaEmail = false;
        $this->proArriIndice = '';
        $this->fileDaPEC = array();
        $this->proArriUff = array();
        $this->proArriAlle = array();
        $this->Proric_parm = array();
        $this->inviaConfermaMail = '';
        $this->proDestinatari = array();
        $this->mittentiAggiuntivi = array();
        $this->proAltriDestinatari = array();
        $this->noteManager = array();
        $this->datiPasso = '';
        $this->anapro_record = '';
        if ($this->currObjSdi != null) {
            $this->currObjSdi->cleanData();
            $this->currObjSdi = null;
        }
        $this->varMarcatura = array();
        $this->datiUtiP7m = array();

        TableView::clearGrid($this->gridDestinatari);
        TableView::clearGrid($this->gridAltriDestinatari);
        TableView::clearGrid($this->gridAllegati);
        TableView::clearGrid($this->gridNote);
        TableView::clearGrid($this->gridFascicoli);
        TableView::setLabel($this->gridFascicoli, 'FASCICOLO', 'Fascicoli');
        itaLib::deletePrivateUploadPath();
        App::$utente->removeKey($this->nameForm . '_medcodOLD');
        $this->datiFascicolazine = array();
        $this->duplicaAllegati = '';
        $this->ElencoFascicoli = array();
        $this->ProtAllegaDaFascicolo = '';
        $this->DelegatoFirmatario = array();
        $this->DatiPreProtocollazione = array();
        $this->CtrDatiRichiestiProt = array();
    }

    public function Nascondi($nuovo = true) {
        $this->sbloccaSeDaInviare();
        Out::removeElement($this->nameForm . '_AggiungiFascicolo');
        Out::hide($this->nameForm . '_protMotivo_field');
        Out::hide($this->nameForm . '_Registra');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Annulla');
        Out::hide($this->nameForm . '_Riattiva');
        Out::hide($this->nameForm . '_Partenza');
        Out::hide($this->nameForm . '_Arrivo');
        Out::hide($this->nameForm . '_divSpese');
        if ($nuovo) {
            Out::hide($this->nameForm . '_divProtMit');
            Out::hide($this->nameForm . '_divMittPartenza');
        }
        Out::hide($this->nameForm . '_divCampiDest');
        Out::hide($this->nameForm . '_divCampiUff');
        Out::hide($this->nameForm . '_AggiornaAss');
        Out::hide($this->nameForm . '_DuplicaProt');
        Out::hide($this->nameForm . '_Ristampa');
        Out::hide($this->nameForm . '_Mail');
        Out::hide($this->nameForm . '_XOL');
        Out::hide($this->nameForm . '_MailMittenti');
        Out::hide($this->nameForm . '_Indirizzi');
        Out::hide($this->nameForm . '_Ricevuta');
        Out::hide($this->nameForm . '_Evidenza');
        Out::hide($this->nameForm . '_Riserva');
        Out::hide($this->nameForm . '_NonRiserva');
        Out::hide($this->nameForm . '_Uff_cod_butt');
        Out::hide($this->nameForm . '_DestSimple');
        if ($nuovo) {
            Out::hide($this->nameForm . '_paneAllegati');
        }
        Out::hide($this->nameForm . '_Scanner');
        Out::hide($this->nameForm . '_ScannerShared');
        Out::hide($this->nameForm . '_DaP7m');
        Out::hide($this->nameForm . '_DaTestoBase');
        Out::hide($this->nameForm . '_DaFascicolo');
        Out::hide($this->nameForm . '_DaProtCollegati');
        Out::hide($this->nameForm . '_FileLocale_uploader');

        Out::hide($this->nameForm . '_VisMail');
        if ($nuovo) {
            Out::hide($this->nameForm . '_Riscontro');
            Out::hide($this->nameForm . '_Trasmissioni');
            Out::hide($this->nameForm . '_divNotifiche');
        }
        Out::hide($this->nameForm . '_Variazioni');
        Out::hide($this->nameForm . '_campoRiscontro');
        Out::hide($this->nameForm . '_ANAPRO[PROINCOGG]_field');
        Out::hide($this->nameForm . '_divPratica');
        Out::hide($this->nameForm . '_CancellaProgressivoOggetto');
        Out::hide($this->nameForm . '_divArrEme');
        Out::hide($this->nameForm . '_ricevutaStampata');
        Out::hide($this->nameForm . '_protRiservato_field');
        Out::hide($this->nameForm . '_MailNotifica');
        Out::hide($this->nameForm . '_AccettazioneNotifica');
        Out::hide($this->nameForm . '_ConsegnaNotifica');
        Out::hide($this->nameForm . '_NotifichePEC');
        Out::hide($this->nameForm . '_divInfoErrorProto');
        Out::hide($this->nameForm . '_SbloccaMail');
        Out::hide($this->nameForm . '_tso');
        Out::hide($this->nameForm . '_UsaModello');
        Out::hide($this->nameForm . '_AnnullaModello');
        Out::hide($this->nameForm . '_ModificaNota');
        Out::hide($this->nameForm . '_GestPratica');
        Out::hide($this->nameForm . '_AggiungiOggetto');
        Out::hide($this->nameForm . '_AggiungiMittente');
        Out::hide($this->nameForm . '_divConservazione');
        Out::hide($this->nameForm . '_divInfoDatiProto');


        Out::hide($this->nameForm . '_ANAPRO[PRONUR]_field');
        Out::hide($this->nameForm . '_ANAPRO[PRODRA]_field');

        $anaent_11 = $this->proLib->GetAnaent('11');
        if ($anaent_11['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_ANAPRO[PROCAT]_field');
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
            Out::hide($this->nameForm . '_TitolarioDecod');
        }
        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Clacod_field');
            Out::hide($this->nameForm . '_Fascod_field');
        }
        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_Fascod_field');
        }
        Out::hide($this->nameForm . '_AllegatiServizio');
        Out::hide($this->nameForm . '_ProtCollegati');
        Out::hide($this->nameForm . '_AggiungiFascicolo'); /* Aggiunta Fascicolo da SelezioneFascicoli */
        Out::hide($this->nameForm . '_FascicolaPre'); /* Aggiunta Fascicolo da SelezioneFascicoli */
        Out::hide($this->nameForm . '_InfoMailProt');
        Out::hide($this->nameForm . '_TornaElencoMail');
        Out::hide($this->nameForm . '_ANAPRO[PRODRA]_field');
        Out::hide($this->nameForm . '_ANAPRO[PRONUR]_field');

        $this->abilitaProt($nuovo);
    }

    /**
     * Attiva i giusti campi in fuzione del tipo di protocollo (A/P/C)
     */
    public function AbilitaCampi() {
        Out::show($this->nameForm . '_divMittDestPrincipale');
        if ($this->checkPermsAnaogg()) {
            Out::show($this->nameForm . '_AggiungiOggetto');
        } else {
            Out::hide($this->nameForm . '_AggiungiOggetto');
        }
        Out::show($this->nameForm . '_divPrecedente');
        Out::show($this->nameForm . '_ANAPRO[PRODAA]_field');
        Out::html($this->nameForm . '_Pronum_lbl', 'N.Protocollo&nbsp;&nbsp;');
        Out::valore($this->nameForm . '_protStato', '');
        $anaent_31 = $this->proLib->GetAnaent('31');
        if ($anaent_31['ENTDE4'] == '1') {
            Out::show($this->nameForm . '_CercaAnagrafe');
            Out::show($this->nameForm . '_CercaAnagrafeAltri');
        } else {
            Out::hide($this->nameForm . '_CercaAnagrafe');
            Out::hide($this->nameForm . '_CercaAnagrafeAltri');
        }
        Out::show($this->nameForm . '_CercaIPA');
        if (( $this->tipoProt == 'A' || $this->tipoProt == 'P') && $this->proLib->checkVisibilitaIncrOggetto($anaent_31['ENTDE5'])) {
            Out::show($this->nameForm . '_ANAPRO[PROINCOGG]_field');
            Out::show($this->nameForm . '_CancellaProgressivoOggetto');
        }
        Out::delClass($this->nameForm . '_DESCOD', "required");
        Out::delClass($this->nameForm . '_UFFNOM', "required");
        Out::addClass($this->nameForm . '_divTesta', 'ui-corner-all');
        Out::css($this->nameForm . '_divTesta', 'background-image', '');
        if ($this->tipoProt == 'C') {
            if ($this->annullato === false) {
                Out::show($this->nameForm . '_Arrivo');
                Out::show($this->nameForm . '_Partenza');
            }
            Out::hide($this->nameForm . '_divSpese');
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');
            Out::hide($this->nameForm . '_divCampiAltriDest');
            //Out::hide($this->nameForm . '_ANAPRO[PRODAA]_field'); // Utile anche per i Doc Formali.
            Out::hide($this->nameForm . '_divProtMit');
            Out::setAppTitle($this->nameForm, "Gestione Documenti Formali");
            Out::hide($this->nameForm . '_divGrigliaDestPart');
            Out::hide($this->nameForm . '_CercaAnagrafeAltri');
            Out::html($this->nameForm . '_Pronum_lbl', 'Doc. Formale&nbsp;&nbsp;');
            Out::valore($this->nameForm . '_protTipo', 'DOC.FORMALE');
//            Out::addClass($this->nameForm . '_divTesta', 'ita-tapering-gray ui-corner-all');
            Out::css($this->nameForm . '_divTesta', 'background-image', ' linear-gradient(to right, #C2C2C2 0%, #FFFFFF 100%)');
            Out::setFocus('', $this->nameForm . "_Oggetto");
            Out::show($this->nameForm . '_divMittPartenza');
            Out::hide($this->nameForm . '_divMittDestPrincipale');
            Out::addClass($this->nameForm . '_DESCOD', "required");
            Out::addClass($this->nameForm . '_UFFNOM', "required");
            Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
            Out::show($this->nameForm . '_Uff_cod_butt');
        } else if ($this->tipoProt == 'A') {
            Out::show($this->nameForm . '_divSpese');
            Out::hide($this->nameForm . '_divGrigliaDestPart');
            Out::show($this->nameForm . '_divGrigliaDest');
            Out::hide($this->nameForm . '_Arrivo');
            if ($this->annullato === false) {
                Out::show($this->nameForm . '_Partenza');
            }
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');
            Out::hide($this->nameForm . '_divCampiAltriDest');
            Out::show($this->nameForm . '_divProtMit');
            Out::hide($this->nameForm . '_divMittPartenza');
            Out::setAppTitle($this->nameForm, "<span style=\"font-size:1.0em;color:darkred;font-weight:bold;\">Gestione Arrivi</span>");
            Out::html($this->nameForm . '_ANAPRO[PRODAA]_lbl', 'Arrivato il');
            Out::html($this->nameForm . '_ANAPRO[PROCON]_lbl', 'Mittente');
            Out::valore($this->nameForm . '_protTipo', 'ARRIVO');
            //Out::addClass($this->nameForm . '_divTesta', 'ita-tapering-green ui-corner-all');
            Out::css($this->nameForm . '_divTesta', 'background-image', ' linear-gradient(to right, #96ED8C 0%, #FFFFFF 100%)');
            $anaent_rec = $this->proLib->GetAnaent('15');
            if ($anaent_rec['ENTDE4'] == '1') {
                Out::show($this->nameForm . '_divSpese');
            }
            $anaent_3 = $this->proLib->GetAnaent('3');
            Out::show($this->nameForm . '_ANAPRO[PRODAA]_field');
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
            Out::show($this->nameForm . '_Uff_cod_butt');
        } else if ($this->tipoProt == 'P') {
            Out::show($this->nameForm . '_divSpese');
            Out::show($this->nameForm . '_divGrigliaDestPart');
            if ($this->annullato === false) {
                Out::show($this->nameForm . '_Arrivo');
            }
            Out::hide($this->nameForm . '_Partenza');
            Out::show($this->nameForm . '_ANAPRO[PRODAA]_field');
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');
            Out::hide($this->nameForm . '_divCampiAltriDest');
            Out::show($this->nameForm . '_divMittPartenza');
            Out::hide($this->nameForm . '_divProtMit');
            Out::show($this->nameForm . '_Uff_cod_butt');
            Out::show($this->nameForm . '_DestSimple');
            Out::setAppTitle($this->nameForm, "<span style=\"font-size:1.0em;color:orange;font-weight:bold;\">Gestione Partenze</span>");
            Out::html($this->nameForm . '_ANAPRO[PRODAA]_lbl', 'Inviato il');
            Out::html($this->nameForm . '_ANAPRO[PROCON]_lbl', 'Destinatario');
            Out::valore($this->nameForm . '_protTipo', 'PARTENZA');
//            Out::addClass($this->nameForm . '_divTesta', 'ita-tapering-blue ui-corner-all');
            Out::css($this->nameForm . '_divTesta', 'background-image', ' linear-gradient(to right, #AACFEF 0%, #FFFFFF 100%)');
            $anaent_rec = $this->proLib->GetAnaent('15');
            if ($anaent_rec['ENTDE4'] == '1') {
                Out::show($this->nameForm . '_divSpese');
            }
            $anaent_3 = $this->proLib->GetAnaent('3');
            Out::addClass($this->nameForm . '_DESCOD', "required");
            Out::addClass($this->nameForm . '_UFFNOM', "required");
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
        }
        $anaent_3 = $this->proLib->GetAnaent('3');

        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($profilo['PROT_ABILITATI'] == '1') {
            Out::hide($this->nameForm . '_Partenza');
        } else if ($profilo['PROT_ABILITATI'] == '2') {
            Out::hide($this->nameForm . '_Arrivo');
        } else if ($profilo['PROT_ABILITATI'] == '3') {
            Out::hide($this->nameForm . '_Partenza');
            Out::hide($this->nameForm . '_Arrivo');
        }
        if ($this->emergenza) {
            Out::show($this->nameForm . '_divArrEme');
            Out::hide($this->nameForm . '_ComForm');
        }
        $anaent_1 = $this->proLib->GetAnaent('1');
        if ($anaent_1['ENTDE3'] == '0') {
            Out::hide($this->nameForm . '_Registra');
        }
        $anaent_25 = $this->proLib->GetAnaent('25');
        if ($anaent_25['ENTDE1'] === '1') {
            Out::hide($this->nameForm . '_divCampiUff');
        }
    }

    public function Modifica($indice) {
        //
        // Leggo parametri di utilizzo proArri
        //
        $anaent_1 = $this->proLib->GetAnaent('1');
        $anaent_3 = $this->proLib->GetAnaent('3');
        $anaent_24 = $this->proLib->GetAnaent('24');
        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_37 = $this->proLib->GetAnaent('37');
        $anaent_47 = $this->proLib->GetAnaent('47');
        $anaent_57 = $this->proLib->GetAnaent('57');
        //
        // Lettura record principlae
        //
        $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $indice);
        if (!$anapro_rec) {
            Out::msgStop("Accesso al protocollo", "Protocollo non accessibile");
            $this->Nuovo();
            return;
        }
        /* Spostato qui. Problemi di tempistica. */
        $this->tipoProt = $anapro_rec['PROPAR'];
        $this->StatoToggle = array(); // Nuova Funzione Stato Toggle
        $this->AzzeraVariabili();

        $this->anapro_record = $anapro_rec;

        $this->proArriIndice = $anapro_rec['ROWID'];
        if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            $this->annullato = true;
        } else {
            $this->annullato = false;
        }
        $this->toggleAll('abilita');
        $this->Nascondi(false);
        Out::show($this->nameForm . '_DuplicaProt');
        $this->AbilitaCampi();
        if ($anapro_rec['PROTEMPLATE'] == true) {
            Out::show($this->nameForm . '_AnnullaModello');
            Out::hide($this->nameForm . '_UsaModello');
        } else {
            Out::show($this->nameForm . '_UsaModello');
            Out::hide($this->nameForm . '_AnullaModello');
        }
        if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            Out::hide($this->nameForm . '_UsaModello');
            Out::hide($this->nameForm . '_AnullaModello');
        }
        $this->disabledRec = ($anaent_24['ENTDE2'] * 1000000) + $anaent_24['ENTDE1'] >= $anapro_rec['PRONUM'] ? true : false;
        if (!$this->disabledRec && $anaent_1['ENTDE4'] == '1' && $anapro_rec['PRODAR'] < $this->workDate || $anaent_1['ENTDE4'] == '2') {
            $this->disabledRec = true;
            // Se l'utente può annulare, può modificare il protocollo: Solo se parametro permette tale funzione.
            if ($this->profilo['OK_ANNULLA'] && $anaent_57['ENTDE6']) {
                $this->disabledRec = false;
            }
        }
        if ($this->annullato === true) {
            Out::valore($this->nameForm . '_protStato', 'REGISTRAZIONE ANNULLATA');
        }
        if (!$this->disabledRec) {
            if ($this->annullato === false) {
                Out::show($this->nameForm . '_Registra');
                Out::show($this->nameForm . '_Annulla');
                //Out::show($this->nameForm . '_Ristampa');
                $this->checkObbligoAllegati();
            }
        } else {
            if ($this->annullato === false) {
                if (($this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) || !$anaent_37['ENTDE5']) {
                    Out::show($this->nameForm . '_AggiornaAss');
                }
                Out::show($this->nameForm . '_Annulla');
            }
        }
        Out::show($this->nameForm . '_Nuovo');
        if ($anaent_1['ENTDE2'] == '0') {
            Out::hide($this->nameForm . '_Cancella');
        }
        if ($anaent_1['ENTDE3'] == '0') {
            Out::hide($this->nameForm . '_Registra');
        }
        $sql_save = "
            SELECT
                ROWID,
                PROUTE,
                PROUOF
            FROM
                ANAPROSAVE
            WHERE
                PRONUM={$anapro_rec['PRONUM']} AND (PROPAR='{$anapro_rec['PROPAR']}' ) ORDER BY ROWID
        ";
        $anaprosave_tab = $this->proLib->getGenericTab($sql_save);
        //$anaprosave_tab = $this->proLib->getGenericTab("SELECT ROWID, PROUTE, PROUOF FROM ANAPROSAVE WHERE PRONUM=" . $anapro_rec['PRONUM'] . " AND PROPAR='" . $anapro_rec['PROPAR'] . "' ORDER BY ROWID");
        if ($anaprosave_tab) {
            $anauff_rec = $this->proLib->GetAnauff($anaprosave_tab[0]['PROUOF']);
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', 'Creato da: ' . $anaprosave_tab[0]['PROUTE'] . " - " . $anauff_rec['UFFDES']);
        } else {
            $anauff_rec = $this->proLib->GetAnauff($anapro_rec['PROUOF']);
            Out::valore($this->nameForm . '_UTENTEORIGINARIO', 'Creato da: ' . $anapro_rec['PROUTE'] . " - " . $anauff_rec['UFFDES']);
        }
        $anauff_rec = $this->proLib->GetAnauff($anapro_rec['PROUOF']);
        Out::valore($this->nameForm . '_UTENTEULTIMO', 'Ultima Mod.: ' . $anapro_rec['PROUTE'] . " - " . $anauff_rec['UFFDES']);
        $this->prouof = $this->proLib->caricaUof($this);

        $this->Decod($anapro_rec, true);

        // Fix Bug: troppo tardi.
        //$this->tipoProt = $anapro_rec['PROPAR'];
        if ($this->tipoProt == 'P' && $this->annullato === false) {
            if ($anaent_32['ENTDE1'] == 1) {
                Out::show($this->nameForm . '_Indirizzi');
            } else {
                Out::hide($this->nameForm . '_Indirizzi');
            }
            Out::show($this->nameForm . '_Ricevuta');
            Out::show($this->nameForm . '_Riscontro');
            if (trim($anapro_rec['PROTSP']) == 'ROL') {
                Out::show($this->nameForm . '_XOL');
            }

            if ($anaprosave_tab) {
                Out::show($this->nameForm . '_Variazioni');
            }
            Out::show($this->nameForm . '_Trasmissioni');
        } else if ($this->tipoProt == 'A' && $this->annullato === false) {
            Out::show($this->nameForm . '_Ricevuta');
            Out::show($this->nameForm . '_Riscontro');
            Out::show($this->nameForm . '_MailMittenti');

            if ($anaprosave_tab) {
                Out::show($this->nameForm . '_Variazioni');
            }
            Out::show($this->nameForm . '_Trasmissioni');
        } else if ($this->tipoProt == 'C' && $this->annullato === false) {
            if ($anaprosave_tab) {
                Out::show($this->nameForm . '_Variazioni');
            }
            Out::show($this->nameForm . '_Riscontro');
        }
        /*
         * Attivo le variazioni anche se annullato
         */
        if ($anaprosave_tab) {
            Out::show($this->nameForm . '_Variazioni');
        }

        if ($anapro_rec['PROIDMAILDEST']) {
            Out::show($this->nameForm . '_MailNotifica');
            if ($this->tipoProt == 'P') {
                Out::show($this->nameForm . '_SbloccaMail');
            }
            $retRic = $this->proLib->checkMailRic($anapro_rec['PROIDMAILDEST']);
            if ($retRic['ACCETTAZIONE']) {
                Out::show($this->nameForm . '_AccettazioneNotifica');
            }
            if ($retRic['CONSEGNA']) {
                Out::show($this->nameForm . '_ConsegnaNotifica');
            }
            // ALTRE NOTIFICHE
            if ($retRic['NOTIFICHE']) {
                Out::show($this->nameForm . '_NotifichePEC');
                Out::show($this->nameForm . '_divInfoErrorProto');
                Out::css($this->nameForm . '_divTesta', 'background-image', ' linear-gradient(to right, #de3b43 0%, #FFFFFF 100%)');
                Out::html($this->nameForm . '_divInfoErrorProto', 'Riscontrate anomalie nelle notifiche inviate ai destinatari del protocollo.');
            }
        }

        Out::show($this->nameForm . '_Evidenza');
        $arcite_tab = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
        $evidenza = 0;
        foreach ($arcite_tab as $arcite_rec) {
            if ($arcite_rec['ITEEVIDENZA'] == 1) {
                $evidenza = 1;
            }
        }
        if ($evidenza == 1) {
            Out::html($this->nameForm . "_Evidenza_lbl", "Togli Evidenza");
        } else {
            Out::html($this->nameForm . "_Evidenza_lbl", "Metti Evidenza");
        }


        Out::show($this->nameForm . '_paneAllegati');
        Out::show($this->nameForm . '_Scanner');
        if ($anaent_57['ENTDE1']) {
            Out::show($this->nameForm . '_ScannerShared');
        }
        if ($this->tipoProt != 'A' && $anaent_47['ENTDE5']) {
            Out::show($this->nameForm . '_DaTestoBase');
        }
        if ($anaent_47['ENTDE4']) {
            Out::show($this->nameForm . '_DaP7m');
        }
        Out::show($this->nameForm . '_FileLocale_uploader');
        Out::show($this->nameForm . '_DaFascicolo');
        Out::show($this->nameForm . '_DaProtCollegati');

        Out::show($this->nameForm . '_divNotifiche');
        $profilo = proSoggetto::getProfileFromIdUtente();


        if (($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) && $this->tipoProt == 'A') {
            $oggutenti_check = $this->proLib->GetOggUtenti($profilo['UTELOG']);
            if ($oggutenti_check) {
                $this->disabilitaFormPerOggetto();
            }
        }


        if ($anapro_rec['PROTSO']) {
            Out::show($this->nameForm . '_tso');
        }
        if (strtolower($anapro_rec['PROUTE']) == 'nobody') {
            Out::valore($this->nameForm . '_UTENTEULTIMO', '');
        }
        if ($anapro_rec['PRORICEVUTA']) {
            Out::show($this->nameForm . '_ricevutaStampata');
        } else {
            Out::hide($this->nameForm . '_ricevutaStampata');
        }

        if (
                ($profilo['PROT_ABILITATI'] == '3') ||
                ($profilo['PROT_ABILITATI'] == '1' && $this->tipoProt == "P") ||
                ($profilo['PROT_ABILITATI'] == '2' && $this->tipoProt == "A")
        ) {
            $this->consultazione = true;
        }

//        if (!$abilitato) {
//            Out::hide($this->nameForm . '_Registra');
//            Out::hide($this->nameForm . '_MailMittenti');
//            Out::hide($this->nameForm . '_AggiungiOggetto');
//            Out::hide($this->nameForm . '_AggiungiMittente');
//            Out::hide($this->nameForm . '_MailMittenti');
//            Out::hide($this->nameForm . '_Mail');
//            Out::hide($this->nameForm . '_Annulla');
//            Out::hide($this->nameForm . '_Evidenza');
//            Out::hide($this->nameForm . '_UsaModello');
//            Out::hide($this->nameForm . '_Riserva');
//            Out::hide($this->nameForm . '_Scanner');
//            Out::hide($this->nameForm . '_FileLocale_uploader');
//            $this->consultazione = true;
//        }
        $this->abilitaDatiProtEmergenza($anapro_rec);

        $proges_rec = $this->proLibPratica->GetProges($anapro_rec['PROFASKEY'], 'geskey');
        if ($proges_rec['GESDCH']) {
            $this->consultazione = true;
        }

        if ($this->consultazione == true) {
            $this->Nascondi(false);
        }
        $pakdoc_rec = $this->proLibPratica->GetPakdoc(array("PRONUM" => $anapro_rec['PRONUM'], "PROPAR" => $anapro_rec['PROPAR']), 'pronum', false);
        if ($pakdoc_rec) {
            $this->decodDettaglioPratica($pakdoc_rec['PROPAK']);
        }
        if ($anapro_rec['PRORISERVA'] == "1") {
            Out::show($this->nameForm . '_protRiservato_field');
        }

        /*
         * Dettaglio subForm
         */
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->setAnnullato($this->annullato);
        $proSubMittDest->setDisabledRec($this->disabledRec);
        $proSubMittDest->setConsultazione($this->consultazione);
        $proSubMittDest->setAnapro_rec($anapro_rec);
        $proSubMittDest->setTipoProt($this->tipoProt);
        $proSubMittDest->Modifica();
        $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
        $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
        /*
         * Dettaglio proSubTrasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proSubTrasmissioni->setTipoProt($this->tipoProt);
        $proSubTrasmissioni->setAnnullato($this->annullato);
        //$proSubTrasmissioni->setDisabledRec($this->disabledRec);
        $proSubTrasmissioni->setConsultazione($this->consultazione);
        $proSubTrasmissioni->setAnapro_rec($anapro_rec);
        $proSubTrasmissioni->Modifica();


        //
        // Fase di blocco campi e funzioni
        //

        if ($this->consultazione) {
            $this->toggleDatiPrincipali('disabilita');
            //$this->toggleMittentiDestinatari('disabilita');
            $this->toggleAssegnazioniInterne('disabilita');
            $this->toggleAllegati('disabilita');
        } elseif ($this->disabledRec) {
            $this->toggleDatiPrincipali('disabilita');
            $this->toggleAllegati('disabilita');
            //$this->toggleMittentiDestinatari('disabilita', false);
            if ($this->tipoProt != "P") {
                if ($anapro_rec['PRODAR'] < $this->workDate) {
                    $this->toggleAllegati('disabilita');
                } else {
//                    Out::hide($this->gridAllegati . '_delGridRow');
                    // Out::show($this->nameForm . '_Registra');
                }
            } else {
                if ($this->bloccareSeInviato($anapro_rec) === true) {
                    $this->bloccoDaEmail = true;
                    $this->toggleAllegati('disabilita');
                    if ($anapro_rec['PROIDMAILDEST'] == '') {
//                        Out::show($this->nameForm . '_Registra');
//                        Out::hide($this->nameForm . '_AggiornaAss');
                        $this->toggleMailMittentiDestinatari('abilita');
                    }
                    /*
                     * Attivi entrambi i bottoni:
                     * Per permettere aggiornamento assegnazioni o sistemazione dati modificabili.
                     * [Registra visibile solo se stato precedentemente attivato]
                     */
                    //Out::hide($this->nameForm . '_Registra');
                    Out::show($this->nameForm . '_AggiornaAss');
                } else {
                    //Out::show($this->nameForm . '_Registra');
                    Out::show($this->nameForm . '_AggiornaAss');
                    $this->bloccoDaEmail = false;
                    $this->toggleMailMittentiDestinatari('abilita');
                }
            }

            if (!$this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) {
                $this->toggleAssegnazioniInterne('disabilita');
            }
        } elseif (!$this->disabledRec) {
            if ($this->tipoProt == "P") {
                if ($this->bloccareSeInviato($anapro_rec) === true) {
                    $this->bloccoDaEmail = true;
                    $this->toggleDatiPrincipali('disabilita');
                    $this->toggleAllegati('disabilita');
                    //$this->toggleMittentiDestinatari('disabilita', false);
                    if ($anapro_rec['PROIDMAILDEST'] == '') {
                        //Out::show($this->nameForm . '_Registra');
                        //Out::hide($this->nameForm . '_AggiornaAss');
                        $this->toggleMailMittentiDestinatari('abilita');
                    }
//                    Out::hide($this->nameForm . '_Registra');
                    Out::show($this->nameForm . '_AggiornaAss');
                } else {
                    //Out::show($this->nameForm . '_Registra');
                    //Out::hide($this->nameForm . '_AggiornaAss');
                    $this->toggleMailMittentiDestinatari('abilita');
                }
            }
        }
        if ($this->disabledRec) {
            foreach ($this->proArriAlle as $keyAlle => $Allegato) {
                TableView::setCellValue($this->gridAllegati, $keyAlle, 'FILEINFO', "", 'not-editable-cell', '', 'false');
            }
        }
        // @TODO - Funzione spostata qui perchè il riscontro altrimenti non si vedrebbe.
        if ($this->tipoProt == 'C') {
            //Out::hide($this->nameForm . '_Ristampa');
        } else {
            $anapro_pretab = $this->proLib->checkRiscontro(substr($anapro_rec['PRONUM'], 0, 4), substr($anapro_rec['PRONUM'], 4), $anapro_rec['PROPAR']);

            if ($anapro_pretab) {
                $numeri = '';
                foreach ($anapro_pretab as $anapro_prerec) {
                    if ($numeri != '') {
                        $numeri .= '<br>';
                    }
                    $numeri .= (int) substr($anapro_prerec['PRONUM'], 4) . "/" . substr($anapro_prerec['PRONUM'], 0, 4);
                }
                Out::show($this->nameForm . '_campoRiscontro');
                $infoRis = '<div class="ita-header ui-widget-header ui-corner-all" Title="Riscontri: ">&nbsp;</div>' . $numeri . '<br>';
                Out::html($this->nameForm . '_campoRiscontro', $infoRis);
            }
        }
        /*
         * Controllo se sono presenti allegati di servizio:
         */
        $whereDoc = "  AND DOCSERVIZIO <> 0 ";
        $AnadocServ_tab = $this->proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], $whereDoc);
        if ($AnadocServ_tab) {
            Out::show($this->nameForm . '_AllegatiServizio');
        }

        $this->statoFascicolo($anapro_rec);
        //Visualizzo sempre in modifica il 'Ristampa'
        Out::show($this->nameForm . '_Ristampa');
        //
        // Focus finale
        //
        Out::setFocus('', $this->nameForm . '_Oggetto');
        /*
         * Qui il controllo STRG e Metadati:
         */
        if ($this->proLibGiornaliero->isRegistroGiornaliero($anapro_rec)) {
            $this->toggleDatiPrincipali('disabilita');
            //$this->toggleMittentiDestinatari('disabilita', false);
            $this->toggleAllegati('disabilita');
//            Out::block($this->nameForm . '_paneAllegati');
//            Il registra è da far vedere?
        }

        /* Mostro/Nascondo Prot. Collegati */
        if ($anapro_rec['PROPRE'] > 0) {
            Out::show($this->nameForm . '_ProtCollegati');
        } else {
            $anno = substr($anapro_rec['PRONUM'], 0, 4);
            $codice = intval(substr($anapro_rec['PRONUM'], 4));
            if ($this->proLib->checkRiscontro($anno, $codice, $anapro_rec['PROPAR'])) {
                Out::show($this->nameForm . '_ProtCollegati');
            } else {
                Out::hide($this->nameForm . '_ProtCollegati');
            }
        }
        /*
         * Spostato in coda: 
         * Metti e togli riservato devono essere sempre visibili (se abilitati)
         */
        if (!$anapro_rec['PRORISERVA']) {
            if ($profilo['NO_RISERVATO'] == 0) {
                Out::show($this->nameForm . '_Riserva');
            }
        } else {
            Out::show($this->nameForm . '_NonRiserva');
        }

        /*
         * Verifico se protocollo conservato:
         */
        $this->CheckProtConservato($anapro_rec);
        /*
         * Verifico protocollo annullato:
         */
        if ($this->annullato) {
            $this->toggleAllegati('disabilita');
            $this->toggleDatiPrincipali('disabilita');
            // $this->toggleMittentiDestinatari('disabilita', false);
        }
        /*
         * Show Info Mail
         */
        if ($anapro_rec['PROIDMAIL']) {
            Out::show($this->nameForm . '_InfoMailProt');
        }

        $ProDocProt_rec = $this->proLib->GetProDocProt($anapro_rec['PRONUM'], 'destnum', $anapro_rec['PROPAR']);
        if ($ProDocProt_rec) {
            include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
            $segLib = new segLib();
            $Indice_rec = $segLib->GetIndice($ProDocProt_rec['SORGNUM'], 'anapro', false, $ProDocProt_rec['SORGTIP']);
            $messaggio = "<span style=\"color:red\">PROTOCOLLO PREDISPOSTO TRAMITE DOCUMENTO N. " . $Indice_rec['IDELIB'] . "</span>";
            $messaggio .= ' <a href="#" id="' . $Indice_rec['ROWID'] . '" class="ita-hyperlink {event:\'' . $this->nameForm . '_VaiAdAtto\'}"><span style="display:inline-block;vertical-align:bottom;" title="Vai al Documento" class="ita-tooltip ita-icon ita-icon-register-document-green-16x16"></span>Vai al Documento</a>';
            Out::show($this->nameForm . '_divInfoDatiProto');
            Out::html($this->nameForm . "_divInfoDatiProto", $messaggio);
        }
        return $anapro_rec;
    }

    private function abilitaProt($nuovo = true) {
        $abilitaProt_tab = $this->proLib->GetAbilitaProt('', 'tutti');
        foreach ($abilitaProt_tab as $abilitaProt_rec) {
            if ($nuovo == true) {
                Out::unBlock($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
            } else {
                switch ($abilitaProt_rec['CODICE']) {
                    case '':
                        break;
                    default:
                        if ($abilitaProt_rec['MODIFICA'] == '1') {
                            Out::unBlock($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
                        } else {
                            Out::block($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
                        }
                        break;
                }
            }
        }
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
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

    private function Decod($anapro_rec, $da_modifica = false) {
        if ($this->datiPasso) {
            unset($anapro_rec['PROCAT']);
            unset($anapro_rec['PROCCA']);
            unset($anapro_rec['PROCCF']);
            unset($anapro_rec['PROARG']);
        }
        Out::valori($anapro_rec, $this->nameForm . '_ANAPRO');
        $this->DecodVersioneTitolario($anapro_rec['VERSIONE_T']);
        if ($anapro_rec['PRODAS'] == 0)
            Out::valore($this->nameForm . "_ANAPRO[PRODAS]", '');
        if ($da_modifica) {
            Out::valore($this->nameForm . "_Pronum", substr($anapro_rec['PRONUM'], 4));
            Out::valore($this->nameForm . "_Propre1", substr($anapro_rec['PROPRE'], 4));
            if (substr($anapro_rec['PROPRE'], 0, 4) != 0) {
                Out::valore($this->nameForm . "_Propre2", substr($anapro_rec['PROPRE'], 0, 4));
            } else {
                Out::valore($this->nameForm . "_Propre2", '');
            }
        }
        if (strlen($anapro_rec['PROROR']) < 8) {
            Out::valore($this->nameForm . "_ANAPRO[PROROR]", '');
        }
        $tipo = 'codice';
        if (!$this->datiPasso) {
            $this->DecodAnacat($anapro_rec['VERSIONE_T'], $anapro_rec['PROCAT'], $tipo, false);
            $this->DecodAnacla($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCA'], $tipo, false);
            $this->DecodAnafas($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCF'], 'fasccf', false);
            $this->DecodAnaorg($anapro_rec['PROFASKEY'], 'orgkey');
        }
        // $this->DecodAnatsp($anapro_rec['PROTSP']);
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        Out::valore($this->nameForm . "_Oggetto", $anaogg_rec['OGGOGG']);
        if ($this->tipoProt == 'P') {
            //$this->proArriUff = $this->proLib->caricaUffici($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            /*
             * Carico Altri Destinatari da subform
             */
            $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
            $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
            $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
        }
        if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
            $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
            if ($anades_mitt) {
                Out::valore($this->nameForm . "_DESCOD", $anades_mitt['DESCOD']);
                Out::valore($this->nameForm . "_DESNOM", $anades_mitt['DESNOM']);
                Out::valore($this->nameForm . "_DESCUF", $anades_mitt['DESCUF']);
                $anauff_rec = $this->proLib->GetAnauff($anades_mitt['DESCUF'], 'codice');
                Out::valore($this->nameForm . "_UFFNOM", $anauff_rec['UFFDES']);
            } else {
                if ($anapro_rec['PRONOM'] && $this->tipoProt == 'C') {
                    Out::valore($this->nameForm . "_DESNOM", $anapro_rec['PRONOM']);
                }
            }
        }
        if ($da_modifica) {
            Out::show($this->nameForm . '_Trasmissioni');
        }
        $this->DecodAnaris($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        if (!$this->datiPasso) {
            if ($da_modifica) {
                $this->CaricaAllegati();
            } else {
//                $this->proArriAlle = array();
            }
        }
        $this->decodNotifiche($anapro_rec);
        // Decod TipoDoc
        if ($anapro_rec['PROCODTIPODOC']) {
            $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($anapro_rec['PROCODTIPODOC'], 'codice');
            Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
        }
        /* Nuovo Caricamento Fascicoli */
        $this->ElencoFascicoli = $this->proLibFascicolo->CaricaFascicoliProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_rec['PROFASKEY']);
        $this->CaricaGriglia($this->gridFascicoli, $this->ElencoFascicoli);
        TableView::setLabel($this->gridFascicoli, 'FASCICOLO', 'Fascicoli: ' . count($this->ElencoFascicoli));
    }

    private function DecodAnamed($codice, $tipoRic = 'codice', $tutti = 'si') {
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $anamed_rec = $proSubMittDest->DecodAnamed($codice, $tipoRic, $tutti);
        App::$utente->setKey($this->nameForm . '_medcodOLD', $anamed_rec['MEDCOD']);
        return $anamed_rec;
    }

    private function decodTitolario($rowid, $tipoArc) {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        // Da testare bene. @TODO - Alle
        if (!$cat && !$cla && !$fas && $des) {
            Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
            Out::valore($this->nameForm . '_Organn', '');
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
            Out::valore($this->nameForm . '_CodSottofascicolo', '');
            Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
        } else {
//            if ($cat) {
            Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $cat);
//            }
//            if ($cla) {
            Out::valore($this->nameForm . '_Clacod', $cla);
//            }
//            if ($fas) {
            Out::valore($this->nameForm . '_Fascod', $fas);
//            }
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
            Out::valore($this->nameForm . '_Organn', '');
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            Out::valore($this->nameForm . '_CodSottofascicolo', '');
            Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
            if ($des) {
                Out::valore($this->nameForm . '_TitolarioDecod', $des);
            }
        }
    }

    private function DecodAnacat($versione_t, $codice, $tipo = 'codice', $soloValidi = true) {
        $anacat_rec = $this->proLib->GetAnacat($versione_t, $codice, $tipo, $soloValidi);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
            Out::valore($this->nameForm . '_Organn', '');
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
            Out::valore($this->nameForm . '_CodSottofascicolo', '');
            Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
        }
        return $anacat_rec;
    }

    private function DecodAnacla($versione_t, $codice, $tipo = 'codice', $soloValidi = true) {
        $anacla_rec = $this->proLib->GetAnacla($versione_t, $codice, $tipo, $soloValidi);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_Clacod', '');
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anacla_rec;
    }

    private function DecodAnafas($versione_t, $codice, $tipo = 'codice', $soloValidi = true) {
        $anafas_rec = $this->proLib->GetAnafas($versione_t, $codice, $tipo, $soloValidi);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_Fascod', '');
        }
        return $anafas_rec;
    }

    private function DecodAnaorg($codice, $tipo = 'codice', $codiceCcf = '', $anno = '') {
        $anaorg_rec = $this->proLib->GetAnaorg($codice, $tipo, $codiceCcf, $anno);
        if ($anaorg_rec) {
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', $anaorg_rec['ORGCOD']);
            Out::valore($this->nameForm . '_Organn', $anaorg_rec['ORGANN']);
            Out::valore($this->nameForm . '_FascicoloDecod', $anaorg_rec['ORGDES']);
            // Controllo se si trova in un sottofascicolo
            $retChkSottoFas = $this->proLibFascicolo->ChkProtoInSottoFascicolo($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
            Out::valore($this->nameForm . '_CodSottofascicolo', $retChkSottoFas['NUMERO']);
            Out::valore($this->nameForm . '_SottoFascicoloDecod', $retChkSottoFas['DESCRIZIONE']);
        } else {
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            Out::valore($this->nameForm . '_CodSottofascicolo', '');
            Out::valore($this->nameForm . '_SottoFascicoloDecod', '');
            return false;
        }
        return $anaorg_rec;
    }

    private function DecodAnadog($codice, $tipo = 'codice', $ScaricaUffici = true) {
        $anadog_rec = $this->proLib->GetAnadog($codice, $tipo);
        if ($anadog_rec) {
            if ($anadog_rec['DOGMED']) {
                $this->DecodAnamed($anadog_rec['DOGMED'], 'codice', 'si');
            }
            if ($anadog_rec['DOGUFF'] && $ScaricaUffici) {
                /* Carico Sub Form */
                $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
                $uffici = explode("|", $anadog_rec['DOGUFF']);
                foreach ($uffici as $ufficio_soggetto) {
                    /*
                     * Visione: Nuova opzione forza in visione l'assegnazione
                     */
                    list($ufficio, $soggetto, $visione, $tipotrasm) = explode('@', $ufficio_soggetto);
                    $anauff_rec = $this->proLib->GetAnauff($ufficio);
                    if (!$soggetto) {
                        $forzaGestisci = null;
                        if ($visione == 1) {
                            $forzaGestisci = 0;
                        }
                        /*
                         * $forzaGestisci: null=automatico, 1=si, 0 = no
                         */
                        if ($tipotrasm == 'Ufficio') {
                            $proSubTrasmissioni->scaricaUfficio($anauff_rec, true, $forzaGestisci, true);
                        } else {
                            $proSubTrasmissioni->scaricaUfficio($anauff_rec, true, $forzaGestisci);
                        }
                    } else {
                        $uffdes_rec = $this->proLib->GetUffdes(array('UFFKEY' => $soggetto, 'UFFCOD' => $ufficio), 'ruolo', true, '', true);
                        if ($uffdes_rec) {
                            $gestisci = $uffdes_rec['UFFFI1__1'];
                            if ($visione == 1) {
                                $gestisci = '0';
                            }
                            /*
                             * $gestisci: controlla gestione/visione 
                             */
                            $proSubTrasmissioni->caricaDestinatarioInterno($soggetto, 'codice', $ufficio, '', $gestisci);
                            $proSubTrasmissioni->scaricaUfficio($anauff_rec, 'false');
                        }
                    }
                }
                $proSubTrasmissioni->elaboraAlbero();
            }
            if ($anadog_rec['DOGCAT']) {
                $this->DecodAnacat('', $anadog_rec['DOGCAT']);
                if ($anadog_rec['DOGCLA']) {
                    $this->DecodAnacla('', $anadog_rec['DOGCAT'] . $anadog_rec['DOGCLA']);
                    if ($anadog_rec['DOGFAS']) {
                        $this->DecodAnafas('', $anadog_rec['DOGCAT'] . $anadog_rec['DOGCLA'] . $anadog_rec['DOGFAS'], 'fasccf');
                    }
                }
            }
        }
        Out::valore($this->nameForm . '_Oggetto', trim($anadog_rec['DOGDEX']));
        return $anadog_rec;
    }

    public function registraPro($tipo, $motivo = '') {
        $modificato = false;
        $anaogg_rec = array();

        $proarg = $_POST[$this->nameForm . '_ANAPRO']['PROARG'];
        $Organn = $_POST[$this->nameForm . '_Organn'];
        $titolario = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'] . $_POST[$this->nameForm . '_Clacod']
                . $_POST[$this->nameForm . '_Fascod'];

        $anapro_rec = $_POST[$this->nameForm . "_ANAPRO"];
        /*
         * Aggiungo i dati della subForm
         */
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->setFormData($this->formData);
        $AnaproSub_rec = $proSubMittDest->getDatiAnapro();
        $anapro_rec = array_merge($anapro_rec, $AnaproSub_rec);


        /* PRENOTAZIONE PROGRESSIVO OGGETTO PATCH PROGRE */
        $PrenotaOggetto = '';
        if ($anapro_rec['PROINCOGG'] == 'IN PRENOTAZIONE') {
            $anapro_rec['PROINCOGG'] = 0;
            $PrenotaOggetto = $anapro_rec['PRODOGCOD'];
        }
        /* FINE PATCH PROGRE */
        if ($tipo == "Aggiungi") {
            $anapro_rec['PRODAR'] = date('Ymd');
            if ($anapro_rec['PROORA'] == '') {
                $anapro_rec['PROORA'] = date('H:i:s');
            }
            if ($this->tipoProt == 'C') {
                /* Se parametro Doc Formali Unico Progressivo Attivo :
                 * Blocco il progressivo generale (A/P)-> ANAENT(1)
                 * Altrimenti ANAENT(23) prog a parte per i Doc Formali
                 */
                $anaent_48 = $this->proLib->GetAnaent('48');
                if ($anaent_48['ENTDE4']) {
                    $retLock = $this->lockAnaent("1");
                    if (!$retLock) {
                        return false;
                    }
                } else {
                    $retLock = $this->lockAnaent("23");
                    if (!$retLock) {
                        return false;
                    }
                }
                $risultato = $this->proLib->prenotaProtocollo($anapro_rec['PRODAR'], "LEGGI", $this->workYear, 'C');
                $newPronum = $risultato['pronum'];
                if ($newPronum == "Error") {
                    Out::msgStop($risultato['errTitolo'], $risultato['errMsg']);
                }
            } else {
                $retLock = $this->lockAnaent("1");
                if (!$retLock) {
                    return false;
                }
                $risultato = $this->proLib->prenotaProtocollo($anapro_rec['PRODAR'], "LEGGI", $this->workYear);
                $newPronum = $risultato['pronum'];
                if ($newPronum == "Error") {
                    Out::msgStop($risultato['errTitolo'], $risultato['errMsg']);
                }
            }
            if ($newPronum == "Error") {
                $this->unlockAnaent($retLock);
                return false;
            }
            $anapro_rec['PRONUM'] = $newPronum;
            $anapro_rec['PROPAR'] = $this->tipoProt;
        } else {
            if ($this->tipoProt == 'A' || $this->tipoProt == 'C') {
                $anaproEsistente_rec = $this->proLib->GetAnapro($anapro_rec['ROWID'], 'rowid');
                $arcite_tab = $this->proLib->GetArcite($anaproEsistente_rec['PRONUM'], 'codice', true, $this->tipoProt);
                if ($arcite_tab) {
                    foreach ($arcite_tab as $arcite_rec) {
                        if ($arcite_rec['ITEDLE'] != '') {
//                            $modificato = true;
                        }
                    }
                }
                if ($modificato) {
                    if ($this->tipoProt == 'A') {
                        Out::msgStop("ATTENZIONE!", "Il Protocollo non può essere modificato perché è stato Gestito.<br><br>Inserimento Interrotto!");
                    } else if ($this->tipoProt == 'C') {
                        Out::msgStop("ATTENZIONE!", "Il Documento Formale non può essere modificata perché è stata Gestita.<br><br>Inserimento Interrotto!");
                    }
                    return "Error";
                }
            }

            if ($anapro_rec['PRODAR'] == date('Ymd') && $motivo == '') {
                $motivo = "Modifica in giornata.";
            } else if ($motivo == '') {
                $motivo = 'Indefinito';
            }

            $this->registraSave($motivo, $anapro_rec['ROWID']);

            /*
             * Controllo se il protocollo era incompleto:
             * lo setto come completo perchè variato.
             */
            $AnaproExist_rec = $this->proLib->GetAnapro($anapro_rec['ROWID'], 'rowid');
            if ($AnaproExist_rec['PROSTATOPROT'] == proLib::PROSTATO_INCOMPLETO) {
                $anapro_rec['PROSTATOPROT'] = 0;
            }
        }

        $anapro_rec['PROCCA'] = $anapro_rec['PROCAT'] . $_POST[$this->nameForm . "_Clacod"];
        $anapro_rec['PROCCF'] = $anapro_rec['PROCCA'] . $_POST[$this->nameForm . "_Fascod"];
        $anapro_rec['PROCHI'] = $anapro_rec['PROCCF'] . $anapro_rec['PROARG'];
        $anapro_rec['PRONRA'] = $anapro_rec['PRODAR']; // INTEGRARE CON OLD_DAT
        if ($anapro_rec['PRODAA'] == '') {
            $anapro_rec['PRODAA'] = $anapro_rec['PRODAR'];
        }
        $anapro_rec['PROAGG'] = $anaproEsistente_rec['PRONUM'];
        /*
         * Valorizzo propre solo se sto inserendo un nuovo protocollo
         * Oppure se sto aggiornando e il protocollo non è bloccato perchè inviato.
         */
        if (!$this->anapro_record || ($this->anapro_record && $this->bloccareSeInviato($this->anapro_record) !== true )) {
            $anapro_rec['PROPRE'] = $_POST[$this->nameForm . "_Propre2"] * 1000000 + $_POST[$this->nameForm . "_Propre1"];
        } else {
            /* Rimuovo i campi per non variarli. */
            unset($anapro_rec['PROPRE']);
            unset($anapro_rec['PROPARPRE']);
        }
        if ($anapro_rec['PROPRE'] != '') {
            $anaproPrec_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
            $anapro_rec['PROAGG'] = $anaproPrec_rec['PROAGG'];
        }
        $anapro_rec['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        $anapro_rec['PRONOM'] = str_replace('"', '', $anapro_rec['PRONOM']);
        $anapro_rec['PROIND'] = str_replace('"', '', $anapro_rec['PROIND']);

        if ($this->fileDaPEC) {
            if ($this->fileDaPEC['ROWID']) {
                $emlLib = new emlLib();
                $mail_rec = $emlLib->getMailArchivio($this->fileDaPEC['ROWID'], 'rowid');
                $anapro_rec['PROIDMAIL'] = $mail_rec['IDMAIL'];
            }
        }

        $profilo = proSoggetto::getProfileFromIdUtente();
//$anapro_rec['PROSECURE'] = $profilo['LIV_PROTEZIONE'];

        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        if ($anamed_rec) {
            $uffdes_rec = $this->proLib->GetUffdes(array("UFFKEY" => $anamed_rec['MEDCOD'], "UFFCOD" => $anapro_rec['PROUOR']), 'ruolo');
            $anapro_rec['PRORUO'] = $uffdes_rec['UFFFI1__2'];
        }
        /* Resgistrazione Principale */
        try {
            if ($tipo == "Aggiungi") {

                include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
                $segnatura = proSegnatura::getStringaSegnatura($anapro_rec);
                if (!$segnatura) {
                    $this->unlockAnaent($retLock);
                    return false;
                }
                $anapro_rec['PROSEG'] = $segnatura;
                $anapro_rec['PROLOG'] = "999" . substr(App::$utente->getKey('nomeUtente'), 0, 7) . date('d/m/y');
                $insert_Info = 'Oggetto Protocollo: ' . $anapro_rec['PRONUM'] . " " . $anapro_rec['PRODAR'];

                if (!$this->insertRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $insert_Info)) {
                    $this->unlockAnaent($retLock);
                    return false;
                }
                $anaproNew_rec = $this->proLib->GetAnapro($anapro_rec['PRONUM'], 'codice', $this->tipoProt);
                if ($this->tipoProt == 'C') {
                    $risultato = $this->proLib->prenotaProtocollo('', "AGGIORNA", $this->workYear, 'C');
                    $aggPronum = $risultato['pronum'];
                    if ($aggPronum == "Error") {
                        Out::msgStop($risultato['errTitolo'], $risultato['errMsg']);
                    }
                } else {
                    $risultato = $this->proLib->prenotaProtocollo('', "AGGIORNA", $this->workYear);
                    $aggPronum = $risultato['pronum'];
                    if ($aggPronum == "Error") {
                        Out::msgStop($risultato['errTitolo'], $risultato['errMsg']);
                    }
                }
                $this->unlockAnaent($retLock);
                if ($aggPronum == "Error")
                    return false;
            } else {
                $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
                if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
                    $this->unlockAnaent($retLock);
                    return false;
                }
                $anaproNew_rec = $this->proLib->GetAnapro($anapro_rec['ROWID'], 'rowid');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->unlockAnaent($retLock);
            return false;
        }
        /*
         * Salvo Oggetto 
         * Aggiunta funzione per caratteri 
         * speciali derivanti da copia e incolla 
         * VERIFICARE UTILITA' str_replace di "  
         */
        $Oggetto = itaLib::decodeUnicodeCharacters($_POST[$this->nameForm . "_Oggetto"]);
        $Oggetto = str_replace('"', '', $Oggetto);
        $anaogg_rec['OGGNUM'] = $anaproNew_rec['PRONUM'];
        $anaogg_rec['OGGOGG'] = $Oggetto;
        $anaogg_rec['OGGPAR'] = $this->tipoProt;
        /*
         * CANCELLO E INSERISCO LA TABELLA OGGETTI
         */
        $anaogg_old = $this->proLib->GetAnaogg($anaproNew_rec['PRONUM'], $this->tipoProt);
        if ($anaogg_old) {
            $delete_Info = 'Oggetto ANAOGG: ' . $anaogg_old['OGGNUM'] . " " . $anaogg_old['OGGOGG'];
            if (!$this->deleteRecord($this->PROT_DB, 'ANAOGG', $anaogg_old['ROWID'], $delete_Info, 'ROWID', false)) {
                return false;
            }
        }
        /* Salvo Oggetto */
        $insert_Info = 'Inserimento: ' . $anaogg_rec['OGGNUM'] . ' ' . $anaogg_rec['OGGDE1'];
        if (!$this->insertRecord($this->PROT_DB, 'ANAOGG', $anaogg_rec, $insert_Info)) {
            return false;
        }

        $ananom_rec['NOMNUM'] = $anaproNew_rec['PRONUM'];
        $ananom_rec['NOMNOM'] = $anaproNew_rec['PRONOM'];
        $ananom_rec['NOMPAR'] = $this->tipoProt;
        $ananom_tab = $this->proLib->GetAnanom($anaproNew_rec['PRONUM'], true, $this->tipoProt);
        if ($ananom_tab) {
            foreach ($ananom_tab as $key => $ananom_newRec) {
                $delete_Info = 'Oggetto ANANOM: ' . $ananom_newRec['NOMNUM'] . " " . $ananom_newRec['NOMNOM'];
                if (!$this->deleteRecord($this->PROT_DB, 'ANANOM', $ananom_newRec['ROWID'], $delete_Info)) {
                    return false;
                }
            }
        }
        $insert_Info = 'Inserimento: ' . $ananom_rec['NOMNUM'] . ' ' . $ananom_rec['NOMNOM'];
        if (!$this->insertRecord($this->PROT_DB, 'ANANOM', $ananom_rec, $insert_Info)) {
            return false;
        }
        /* Salvo Destinatari */
        try {
            $anades_tab = $this->proLib->GetAnades($anaproNew_rec['PRONUM'], 'codice', true, $this->tipoProt, '');
            if ($anades_tab) {
                foreach ($anades_tab as $key => $anades_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
            }
            if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
                /* Salvo Firmatario */
                $anades_partenza = array();
                $anades_partenza['DESNUM'] = $anaproNew_rec['PRONUM'];
                $anades_partenza['DESPAR'] = $this->tipoProt;
                $anades_partenza['DESTIPO'] = "M";
                $anades_partenza['DESCOD'] = $_POST[$this->nameForm . "_DESCOD"];
                $anades_partenza['DESCUF'] = $_POST[$this->nameForm . '_DESCUF']; //nuovo controllo con codice ufficio differenziato
                $anades_partenza['DESNOM'] = $_POST[$this->nameForm . "_DESNOM"];
                $anades_partenza['DESCONOSCENZA'] = 0;
                $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_partenza, $insert_Info)) {
                    return false;
                }
            }
            /*
             * Lettura proAltriDestinatari da subForm
             */
            $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
            $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
            if ($this->proAltriDestinatari) {
                foreach ($this->proAltriDestinatari as $key => $record) {
                    $anades_rec = array();
                    $anades_rec['DESNUM'] = $anaproNew_rec['PRONUM'];
                    $anades_rec['DESPAR'] = $record['DESPAR']; // Qui è corretto?
                    $anades_rec['DESCOD'] = $record['DESCOD'];
                    $anades_rec['DESNOM'] = $record['DESNOM'];
                    $anades_rec['DESIND'] = $record['DESIND'];
                    $anades_rec['DESCAP'] = $record['DESCAP'];
                    $anades_rec['DESCIT'] = $record['DESCIT'];
                    $anades_rec['DESPRO'] = $record['DESPRO'];
                    $anades_rec['DESDAT'] = $record['DESDAT'];
                    $anades_rec['DESDAA'] = $record['DESDAA'];
                    $anades_rec['DESDUF'] = $record['DESDUF'];
                    $anades_rec['DESANN'] = $record['DESANN'];
                    $anades_rec['DESMAIL'] = $record['DESMAIL'];
                    $anades_rec['DESSER'] = $record['DESSER'];
                    $anades_rec['DESCUF'] = $record['DESCUF'];
                    $anades_rec['DESGES'] = $record['DESGES'];
                    $anades_rec['DESRES'] = $record['DESRES'];
                    $anades_rec['DESTSP'] = $record['DESTSP'];
                    $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                    $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                    $anades_rec['DESCONOSCENZA'] = $record['DESCONOSCENZA'];
                    $anades_rec['DESTIPO'] = "D";
                    $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                    $anades_rec['DESFIS'] = $record['DESFIS'];
                    $anades_rec['DESNRAC'] = $record['DESNRAC'];
                    $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                    if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                        return false;
                    }
                }
            }

            /*
             * Lettura ProArriDest:
             */
            $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
            $proArriDest = $proSubTrasmissioni->getProArriDest();
            $proArriUff = $proSubTrasmissioni->getProArriUff();

            if ($proArriDest) {
                foreach ($proArriDest as $key => $record) {
                    $anades_rec = array();
                    $anades_rec['DESNUM'] = $anaproNew_rec['PRONUM'];
                    $anades_rec['DESPAR'] = $anaproNew_rec['PROPAR']; //$record['DESPAR'];// Prima era despar, corretto?
                    $anades_rec['DESCOD'] = $record['DESCOD'];
                    $anades_rec['DESNOM'] = $record['DESNOM'];
                    $anades_rec['DESIND'] = $record['DESIND'];
                    $anades_rec['DESCAP'] = $record['DESCAP'];
                    $anades_rec['DESCIT'] = $record['DESCIT'];
                    $anades_rec['DESPRO'] = $record['DESPRO'];
                    $anades_rec['DESDAT'] = $record['DESDAT'];
                    $anades_rec['DESDAA'] = $record['DESDAA'];
                    $anades_rec['DESDUF'] = $record['DESDUF'];
                    $anades_rec['DESANN'] = $record['DESANN'];
                    $anades_rec['DESMAIL'] = $record['DESMAIL'];
                    $anades_rec['DESSER'] = $record['DESSER'];
                    $anades_rec['DESCUF'] = $record['DESCUF'];
                    $anades_rec['DESGES'] = $record['DESGES'];
                    $anades_rec['DESRES'] = $record['DESRES'];
                    $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                    // Fix sistemazione termine.
                    $anades_rec['DESTERMINE'] = str_replace('-', '', $record['TERMINE']);
                    $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                    $anades_rec['DESINV'] = $record['DESINV'];
                    $anades_rec['DESTIPO'] = "T";
                    $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                    $anades_rec['DESFIS'] = $record['DESFIS'];
                    $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                    if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                        return false;
                    }
                }
            }


            $anaent_3 = $this->proLib->GetAnaent('3');
            if ($anaent_3['ENTDE1'] == 1) {
                $promitagg_tab = $this->proLib->getPromitagg($anaproNew_rec['PRONUM'], 'codice', true, $anaproNew_rec['PROPAR']);
                foreach ($promitagg_tab as $promitagg_rec) {
                    if (!$this->deleteRecord($this->PROT_DB, 'PROMITAGG', $promitagg_rec['ROWID'], '', 'ROWID', false)) {
                        return false;
                    }
                }
                $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
                $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
                if ($this->mittentiAggiuntivi) {
                    foreach ($this->mittentiAggiuntivi as $mittenteAgg) {
                        $mittenteAgg['PRONUM'] = $anaproNew_rec['PRONUM'];
                        $mittenteAgg['PROPAR'] = $anaproNew_rec['PROPAR'];
                        $insert_Info = 'Inserimento: ' . $mittenteAgg['PRONUM'] . ' ' . $mittenteAgg['PRONOM'];
                        if (!$this->insertRecord($this->PROT_DB, 'PROMITAGG', $mittenteAgg, $insert_Info)) {
                            return false;
                        }
                    }
                }
            }

            $uffpro_tab = $this->proLib->GetUffpro($anaproNew_rec['PRONUM'], 'codice', true, $anaproNew_rec['PROPAR']);
            foreach ($uffpro_tab as $key => $uffpro_rec) {
                if (!$this->deleteRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
            foreach ($proArriUff as $key => $record) {
                $uffpro_rec = array();
                $uffpro_rec['PRONUM'] = $anaproNew_rec['PRONUM'];
                $uffpro_rec['UFFPAR'] = $anaproNew_rec['PROPAR'];
                $uffpro_rec['UFFCOD'] = $record['UFFCOD'];
                $uffpro_rec['UFFFI1'] = $record['UFFFI1'];
                $insert_Info = 'Inserimento: ' . $uffpro_rec['PRONUM'] . ' ' . $uffpro_rec['UFFCOD'];
                if (!$this->insertRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec, $insert_Info)) {
                    return false;
                }
            }

            /*
             * Se è un inserimento (quinid il prot non può essere annullato)
             * o non è annullato:
             */
            if ($tipo == 'Aggiungi' || ($this->anapro_record && $this->anapro_record['PROSTATOPROT'] != proLib::PROSTATO_ANNULLATO)) {
//            if (strlen($this->tipoProt) == 1) {
                $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
                $iter->sincIterProtocollo();
            }
            /*             * **@Alfresco */
            /* 1. Controllo Tipologie Allegati - Riordina */
            $this->proArriAlle = $this->proLibAllegati->ControlloAllegatiProtocollo($this->proArriAlle, $this->currObjSdi);
            /* 2. Salvataggio metadati - Solo se è in Aggiunta */
            if ($tipo == "Aggiungi") {
                $retTabDag = $this->InserisciTabDag($anaproNew_rec, $this->currObjSdi);
                if ($retTabDag['stato'] == false) {
                    Out::msgStop("Attenzione!", $retTabDag['messaggio']);
                    /* Rimuovo Metadati Fatt */
                    $proLibTabDag = new proLibTabDag();
                    $retCanc = $proLibTabDag->CancellaTabDagSdi($anaproNew_rec, 'MESSAGGIO_SDI');
                    $retCanc = $proLibTabDag->CancellaTabDagSdi($anaproNew_rec, 'FATT_ELETTRONICA');
                }
            }
            /* 3. Preparazione Obj Protocollo */
            $numero = substr($anaproNew_rec['PRONUM'], 4);
            $anno = substr($anaproNew_rec['PRONUM'], 0, 4);
            $tipoPro = $anaproNew_rec['PROPAR'];
            $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $tipoPro, '');
            /* 4. Passo Oggetto Protocollo a Gestione Allegati */
            $gestiti = $this->proLibAllegati->GestioneAllegati($this, $anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $this->proArriAlle, $anaproNew_rec['PROCON'], $anaproNew_rec['PRONOM'], $objProtocollo);
            if (!$gestiti) {
                Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
                if ($tipo == "Aggiungi") {
                    /* Rimuovo Metadati Fatt */
                    $proLibTabDag = new proLibTabDag();
                    $retCanc = $proLibTabDag->CancellaTabDagSdi($anaproNew_rec, 'MESSAGGIO_SDI');
                    $retCanc = $proLibTabDag->CancellaTabDagSdi($anaproNew_rec, 'FATT_ELETTRONICA');
                }
                return false;
            }

            if ($this->fileDaPEC['DATIPOST']) {
                $risultato = $this->setClasseMail();
                if ($risultato === false) {
                    return false;
                }
                $this->inserisciPromail($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR']);
                Out::unBlock($this->nameForm . '_paneAllegati');
                $ForceSign = array();
                if ($this->varMarcatura['FORZA_MARCATURA']) {
                    $ForceSign = $this->varMarcatura['FORZA_MARCATURA'];
                }
                /* Controllo per Marcatura PDF */
                if ($this->fileDaPEC['MARCA_PDF'] == true) {
                    $proArriAlle = $this->proLib->caricaAllegatiProtocollo($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR']);
                    foreach ($proArriAlle as $allegato) {
                        if (strtolower(pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION)) == 'pdf') {
                            $DocPath = $this->proLibAllegati->GetDocPath($allegato['ROWID'], false, false, true);
//                            $this->proLibAllegati->SegnaDocumento($this, $allegato['FILEPATH'], $anaproNew_rec, $allegato['ROWID'], $ForceSign);
                            $this->proLibAllegati->SegnaDocumento($this, $DocPath['DOCPATH'], $anaproNew_rec, $allegato['ROWID'], $ForceSign);
                        }
                    }
                }
            }

//            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
//            $proLibFascicolo = new proLibFascicolo();
            if ($this->datiPasso) {
                $propas_rec = $this->proLibPratica->GetPropas($this->datiPasso['propak'], 'propak');
                $this->proLibFascicolo->insertDocumentoFascicolo($this, $anaproNew_rec['PROFASKEY'], $anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $propas_rec['PASPRO'], $propas_rec['PASPAR']);
                Out::unBlock($this->nameForm . '_paneAllegati');

                $this->marcaAllegatiDaFascicolo($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR']);
            }
            if ($this->duplicaAllegati === true) {
                $this->duplicaAllegati = false;
                Out::unBlock($this->nameForm . '_paneAllegati');
            }
            if ($this->ProtocollaSimple === true) {
                $this->ProtocollaSimple = false;
                Out::unBlock($this->nameForm . '_paneAllegati');
            }

            /*
             * Dati Pre Porotocollazione:
             */
            $this->SalvaDatiPreProtocollazione($anaproNew_rec);


            $this->ricaricaFascicolo($anaproNew_rec);
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }
        /*
         * Controllo se protocollo conservato 
         * e base dati variata.
         */
        if ($tipo == 'Aggiorna') {
            if (!$this->proLibConservazione->CheckConservazioneBaseDatiVariata($anaproNew_rec['ROWID'])) {
                Out::msgInfo('Controllo Conservazione', 'Errore in controllo variazioni per conservazione.' . $this->proLibConservazione->getErrMessage());
            }
        }

        /* PRENOTO E AGGIORNO NUMERO OGGETTO PATCH PROGRE */
        if ($PrenotaOggetto) {
            $this->PrenotaAggiornaCodOggettoAnapro($anaproNew_rec['ROWID'], $PrenotaOggetto);
        }
        /* FINE PATCH PROGRE */
        return $anaproNew_rec['ROWID'];
    }

//    private function DecodAnatsp($codTsp, $tipo = 'codice') {
//        $anatsp_rec = $this->proLib->GetAnatsp($codTsp, $tipo);
//        if ($anatsp_rec['TSPGRACC'] == 1) {
//            Out::show($this->nameForm . '_ANAPRO[PRODRA]_field');
//            Out::show($this->nameForm . '_ANAPRO[PRONUR]_field');
//        } else {
//            Out::hide($this->nameForm . '_ANAPRO[PRODRA]_field');
//            Out::hide($this->nameForm . '_ANAPRO[PRONUR]_field');
//        }
//        Out::valore($this->nameForm . '_ANAPRO[PROTSP]', $anatsp_rec['TSPCOD']);
//        Out::valore($this->nameForm . '_Tspdes', $anatsp_rec['TSPDES']);
//
//        return $anatsp_rec;
//    }

    private function DecodAnaris($codice, $TipoProt) {
        $anaris_rec = $this->proLib->GetAnaris($codice, 'codice', false, $TipoProt);
        Out::valore($this->nameForm . '_Risrde', $anaris_rec['RISRDE']);
        Out::valore($this->nameForm . '_Risrda', $anaris_rec['RISRDA']);
        return $anaris_rec;
    }

    public function GestioneAllegati($numeroProtocollo) {
        $risultato = $this->proLibAllegati->GestioneAllegati($this, $numeroProtocollo, $this->tipoProt, $this->proArriAlle, $_POST[$this->nameForm . '_ANAPRO']['PROCON'], $_POST[$this->nameForm . '_ANAPRO']['PRONOM']);
        if (!$risultato) {
            Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
        }
        return $risultato;
    }

    private function CaricaAllegati() {
        /* Controllo se Anapro provvisorio */
        if ($this->anapro_record) {
            $this->proArriAlle = $this->proLib->caricaAllegatiProtocollo($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
        }
        /* Altrimenti ricarico quello che ho già */
        $this->caricaGrigliaAllegati();
        if ($this->anapro_record) {
            $this->checkObbligoAllegati();
        }
    }

    private function CaricaDaPec() {
        $bl_proric = $_POST['datiMail']['PRORIC_REC'] != null ? true : false;
        $this->Proric_parm = array();
        if ($bl_proric) {
            $this->Proric_parm['PRORIC_REC'] = $_POST['datiMail']['PRORIC_REC'];
            $this->Proric_parm['destinatari'] = $_POST['datiMail']['destinatari'];
            $soggetto = split('@', $_POST['datiMail']['Soggetto']);
            if (count($soggetto) > 0) {
                $oggetto .= $_POST['datiMail']['SUBJECT'] . ' Data Procedimento: ' . $soggetto[3] . ' ' . $soggetto[7];
                Out::valore($this->nameForm . '_Oggetto', $oggetto);
            }
        } else {
            Out::valore($this->nameForm . '_Oggetto', $_POST['datiMail']['SUBJECT']);
        }
        Out::valore($this->nameForm . '_ANAPRO[PRODAS]', substr($_POST['datiMail']['MSGDATE'], 0, 8));
        Out::valore($this->nameForm . '_ANAPRO[PRODAA]', substr($_POST['datiMail']['MSGDATE'], 0, 8));
        $nomeFileSegnatura = '';
        if ($_POST['datiMail']['ELENCOALLEGATI'] != '') {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Arrivo da PEC", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
            $destFile = itaLib::getPrivateUploadPath();
            // temporaneo
            // $tipoAlle = '';


            /*
             * Determino il documento principale
             */
            $allegatiMail = $_POST['datiMail']['ELENCOALLEGATI'];
            $keyPrincipale = null;
            /*
             * Cerca EMLPEC
             */
            while (true) {
                foreach ($allegatiMail as $key => $elemento) {
                    if ($elemento['PRINCIPALE']) {
                        $keyPrincipale = $key;
                        break;
                    }
                }
                if ($keyPrincipale !== null) {
                    break;
                }
                foreach ($allegatiMail as $key => $elemento) {
                    if ($elemento['DATATIPO'] == 'EMLPEC') {
                        $keyPrincipale = $key;
                        break;
                    }
                }
                if ($keyPrincipale !== null) {
                    break;
                }
                /*
                 * Cerca EMLORIGINALE
                 */
                foreach ($allegatiMail as $key => $elemento) {
                    if ($elemento['DATATIPO'] == 'EMLORIGINALE') {
                        $keyPrincipale = $key;
                        break;
                    }
                }
                if ($keyPrincipale !== null) {
                    break;
                }

                /*
                 * Cerca ALLEGATO
                 */
                foreach ($allegatiMail as $key => $elemento) {
                    if ($elemento['DATATIPO'] == 'ALLEGATO') {
                        $keyPrincipale = $key;
                        break;
                    }
                }
                if ($keyPrincipale !== null) {
                    break;
                }
                /*
                 * Cerca CORPOEML
                 */
                foreach ($allegatiMail as $key => $elemento) {
                    if ($elemento['DATATIPO'] == 'CORPOEML') {
                        $keyPrincipale = $key;
                        break;
                    }
                }
                break;
            }
            foreach ($allegatiMail as $keyAlle => $elemento) {
                $randName = md5(rand() * time()) . "." . pathinfo($elemento['DATAFILE'], PATHINFO_EXTENSION);
                $extFile = pathinfo($elemento['DATAFILE'], PATHINFO_EXTENSION);
                $preview = '';
                if (@copy($elemento['FILE'], $destFile . '/' . $randName)) {

                    //@TODO RISOLVERE LA GESTIONE DELL'ALLEGATO PRINCIPALE
                    //
                    // temporaneo
                    //
//                    $tipoAlle = '';
//                    if (substr($elemento['DATAFILE'], 0, 18) != 'MessaggioOriginale')
//                        $tipoAlle = 'ALLEGATO';


                    if ($keyAlle == $keyPrincipale) {
                        $tipoAlle = '';
                    } else {
                        $tipoAlle = 'ALLEGATO';
                    }
                    if (!$extFile) {
                        $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Menu Funzioni\"></span>";
                    }
                    $daMail = '';
                    if ($_POST['datiMail']['IDMAIL'] != '') {
                        $daMail = "<span title = \"Allegato da Email.\" class=\"ita-icon ita-icon-chiusagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
                    }

                    $this->proArriAlle[] = Array(
                        'ROWID' => 0,
                        'FILEPATH' => $destFile . '/' . $randName,
                        'FILENAME' => $randName,
                        'FILEINFO' => $elemento['DATAFILE'],
                        'NOMEFILE' => $elemento['DATAFILE'],
                        'DOCTIPO' => $tipoAlle,
                        'DAMAIL' => $daMail,
                        'DOCNAME' => $elemento['DATAFILE'],
                        'DOCIDMAIL' => $_POST['datiMail']['IDMAIL'],
                        'DOCFDT' => date('Ymd'),
                        'DOCRELEASE' => '1',
                        'DOCSERVIZIO' => 0,
                        'PREVIEW' => $preview,
//                        'DAFIRMARE' => 0,
//                        'DOCMD5' => '',
//                        'VSIGN' => $vsign
                    );
//                    $tipoAlle = 'ALLEGATO';
                    if (strtolower($elemento['DATAFILE']) == 'segnatura.xml') {
                        $nomeFileSegnatura = $destFile . '/' . $randName;
                    }
                } else {
                    Out::msgStop("Errore", "Errore in copia File: {$elemento['FILE']} su $destFile/$randName");
                }
            }
            $this->caricaGrigliaAllegati();
        }
        $anamed_decoded = false;
        if ($bl_proric && count($soggetto) > 0) {
            Out::valore($this->proSubMittDest . '_ANAPRO[PRONOM]', $soggetto[17] . ' ' . $soggetto[19]);
        } else {
            $sql = "SELECT * FROM ANAMED WHERE MEDEMA='" . $_POST['datiMail']['FROMADDR'] . "' AND MEDANN=0";
            $anamed_tab = $this->proLib->getGenericTab($sql);
            if (count($anamed_tab) == 1) {
                $anamed_decoded = true;
                $this->DecodAnamed($anamed_tab[0]['ROWID'], 'rowid');
            }
            $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
            Out::valore($this->proSubMittDest . '_ANAPRO[PROMAIL]', $_POST['datiMail']['FROMADDR']);
        }
        /*
         * Lettura dati da subForm Trasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();

        if ($bl_proric && $_POST['datiMail']['destinatari']) {
            foreach ($_POST['datiMail']['destinatari'] as $valueDest) {
                $codice = $valueDest;
                if (trim($codice) != "") {
                    if (is_numeric($codice)) {
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                    } else {
                        $codice = trim($codice);
                    }
                    $inserisci = true;
                    foreach ($proArriDest as $value) {
                        if ($codice == $value['DESCOD']) {
                            $inserisci = false;
                            break;
                        }
                    }
                    if ($inserisci == true) {
                        $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                        if ($anamed_rec) {
                            $salvaDest = array();
                            $salvaDest['ROWID'] = 0;
                            $salvaDest['DESPAR'] = $this->tipoProt;
                            $salvaDest['DESCOD'] = $anamed_rec['MEDCOD'];
                            $salvaDest['DESNOM'] = $anamed_rec['MEDNOM'];
                            $salvaDest['DESIND'] = $anamed_rec['MEDIND'];
                            $salvaDest['DESCAP'] = $anamed_rec['MEDCAP'];
                            $salvaDest['DESCIT'] = $anamed_rec['MEDCIT'];
                            $salvaDest['DESPRO'] = $anamed_rec['MEDPRO'];
                            $salvaDest['DESDAT'] = $this->workDate;
                            $salvaDest['DESDAA'] = $this->workDate;
                            $salvaDest['DESDUF'] = '';
                            $salvaDest['DESANN'] = '';
                            $salvaDest['DESMAIL'] = $anamed_rec['MEDEMA'];
                            $salvaDest['DESCUF'] = ''; //$anamed_rec['MEDUFF'];
                            $salvaDest['DESGES'] = 1;
                            $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                            $salvaDest['TERMINE'] = '';

                            $proArriDest = $proSubTrasmissioni->getProAltriDestinatari();
                            $proArriDest[] = $salvaDest;
                            $proSubTrasmissioni->setProAltriDestinatari($proArriDest);
                            $proSubTrasmissioni->CaricaGrigliaDestinatari();
                        }
                    }
                }
            }
        }
        $tipoPosta = array('TSPCOD' => ' PEC', 'TSPDES' => 'PEC');
        if ($_POST['datiMail']['PECTIPO'] == '') {
            $tipoPosta = array('TSPCOD' => ' EML', 'TSPDES' => 'EMAIL');
        }
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $anatsp_rec = $proSubMittDest->DecodAnatsp($tipoPosta['TSPCOD']);
        if (!$anatsp_rec) {
            $insert_Info = 'Oggetto Set Spedizione: ' . $tipoPosta['TSPCOD'] . " " . $tipoPosta['TSPDES'];
            $this->insertRecord($this->PROT_DB, 'ANATSP', $tipoPosta, $insert_Info);
            $proSubMittDest->DecodAnatsp($tipoPosta['TSPCOD']);
        }
        if ($_POST['tipoEml'] == 'MAILBOX') {
            Out::show($this->nameForm . '_VisMail');
            $this->fileDaPEC = array('TYPE' => $_POST['tipoEml'], 'ROWID' => $_POST['datiMail']['ROWID'], 'DATIPOST' => $_POST['datiPost']);
        } else if ($_POST['tipoEml'] == 'LOCALE') {
            $this->fileDaPEC = array('TYPE' => $_POST['tipoEml'], 'FILENAME' => $_POST['datiMail']['FILENAME'], 'DATIPOST' => $_POST['datiPost']);
        }

        if ($nomeFileSegnatura != '') {
            $this->assegnaDatiXml($nomeFileSegnatura, $anamed_decoded);
        }
        Out::show($this->nameForm . '_paneAllegati');
        //Out::block($this->nameForm . '_paneAllegati');
        Out::hide($this->nameForm . '_divBottoniAllega'); // o block
        Out::hide($this->gridAllegati . '_delGridRow'); // o block
        Out::show($this->nameForm . '_CercaProtPre');
        Out::hide($this->nameForm . '_DuplicaProt');
    }

    private function CaricaDaSdi() {
        // Valorizzo date di arrivo
        Out::valore($this->nameForm . '_ANAPRO[PRODAS]', substr($_POST['datiMail']['MSGDATE'], 0, 8));
        Out::valore($this->nameForm . '_ANAPRO[PRODAA]', substr($_POST['datiMail']['MSGDATE'], 0, 8));
        //
        // NON SERVE CONTROLLARE SE CI SONO ALLEGATI.
        // C'E' SEMPRE ALMENO UN ALLEGATO PER LO SDI.
        //
        // Preparo ambiente temporaneo per elaborazione allegati.
        if (!itaLib::createPrivateUploadPath()) {
            Out::msgStop("Gestione Arrivo da PEC", "Creazione ambiente di lavoro temporaneo fallita");
            $this->returnToParent();
        }
        $destFile = itaLib::getPrivateUploadPath();
        // Controllo se c'è il file Fattura o se è un solo un messaggio.
        $AllegatoMessaggioSdi = $this->currObjSdi->getNomeFileMessaggio();
        if ($this->currObjSdi->isFatturaPA()) {
            $AllegatoFatturaPA = $this->currObjSdi->getFileFatturaUnivoco();
            $AllegatoPrincipale = $AllegatoFatturaPA;
        } else {
            $AllegatoPrincipale = $AllegatoMessaggioSdi;
        }


        //@TODO LEGGERE I FILE DELL'OBJ PROSDI ??
        foreach ($_POST['datiMail']['ELENCOALLEGATI'] as $elemento) {
            $randName = md5(rand() * time()) . "." . pathinfo($elemento['DATAFILE'], PATHINFO_EXTENSION);
            if (@copy($elemento['FILE'], $destFile . '/' . $randName)) {
                $tipoAlle = '';
                $docMeta = '';
                if ($elemento['DATAFILE'] != $AllegatoPrincipale) {
                    $tipoAlle = 'ALLEGATO';
                }
                $daMail = '';
                if ($_POST['datiMail']['IDMAIL'] != '') {
                    $daMail = "<span title = \"Allegato da Email.\" class=\"ita-icon ita-icon-chiusagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
                //
                // Controllo i files sdi messaggio e fattura
                //
                if ($AllegatoMessaggioSdi == $elemento['DATAFILE']) {
                    $docMeta = array();
                    $docMeta['INFOFATTURAPA']['TIPOFILE'] = "MESSAGGIOFATTURAPA";
                    //$docMeta['INFOFATTURAPA']['EXPORT'] = array("ESITO" => "", "DATA" => "", "ORA" => "", "SHA2" => "");
                }

                if ($AllegatoFatturaPA == $elemento['DATAFILE']) {
                    $docMeta = array();
                    $docMeta['INFOFATTURAPA']['TIPOFILE'] = "FATTURAPA";
                    //$docMeta['INFOFATTURAPA']['EXPORT'] = array("ESITO" => "", "DATA" => "", "ORA" => "", "SHA2" => "");
                }
                $this->proArriAlle[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $destFile . '/' . $randName,
                    'FILENAME' => $randName,
                    'FILEINFO' => $elemento['DATAFILE'],
                    'DOCTIPO' => $tipoAlle,
                    'DAMAIL' => $daMail,
                    'NOMEFILE' => $elemento['DATAFILE'],
                    'DOCNAME' => $elemento['DATAFILE'],
                    'DOCIDMAIL' => $_POST['datiMail']['IDMAIL'],
                    'DOCFDT' => date('Ymd'),
                    'DOCRELEASE' => '1',
                    'DOCSERVIZIO' => 0,
                    'DOCMETA' => ($docMeta) ? serialize($docMeta) : ""
                );
            } else {
                Out::msgStop("Errore", "Errore in copia File: {$elemento['FILE']} su $destFile/$randName");
            }
        }
        $this->caricaGrigliaAllegati();
        // Vedo se trovo un destinatario
        $anamed_decoded = false;
        $sql = "SELECT * FROM ANAMED WHERE MEDEMA='" . $_POST['datiMail']['FROMADDR'] . "' AND MEDANN=0";
        $anamed_tab = $this->proLib->getGenericTab($sql);
        if (count($anamed_tab) == 1) {
            $anamed_decoded = true;
            $this->DecodAnamed($anamed_tab[0]['ROWID'], 'rowid');
        } else {
            // Utilizzo un nominativo SDI Predefinito?
        }
        Out::valore($this->proSubMittDest . '_ANAPRO[PROMAIL]', $_POST['datiMail']['FROMADDR']);
        // Decodificto tipo posta...
        $tipoPosta = array('TSPCOD' => ' PEC', 'TSPDES' => 'PEC');
        if ($_POST['datiMail']['PECTIPO'] == '') {
            $tipoPosta = array('TSPCOD' => ' EML', 'TSPDES' => 'EMAIL');
        }
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $anatsp_rec = $proSubMittDest->DecodAnatsp($tipoPosta['TSPCOD']);
        if (!$anatsp_rec) {
            $insert_Info = 'Oggetto Set Spedizione: ' . $tipoPosta['TSPCOD'] . " " . $tipoPosta['TSPDES'];
            $this->insertRecord($this->PROT_DB, 'ANATSP', $tipoPosta, $insert_Info);
            $proSubMittDest->DecodAnatsp($tipoPosta['TSPCOD']);
        }
        // TENERE, ANCHE SE TEORICAMENTE ARRIVANO TRAMITE PEC LE COMUNICAZIONI SDI.
        if ($_POST['tipoEml'] == 'MAILBOX') {
            Out::show($this->nameForm . '_VisMail');
            $this->fileDaPEC = array('TYPE' => $_POST['tipoEml'], 'ROWID' => $_POST['datiMail']['ROWID'], 'DATIPOST' => $_POST['datiPost']);
        } else if ($_POST['tipoEml'] == 'LOCALE') {
            $this->fileDaPEC = array('TYPE' => $_POST['tipoEml'], 'FILENAME' => $_POST['datiMail']['FILENAME'], 'DATIPOST' => $_POST['datiPost']);
        }
        // DA PREPARARE ANCHE LA MAIL PER NOTIFICA DESTINATARI ATUOMATICA?
        $this->AssegnaDatiFromSdi(substr($_POST['datiMail']['MSGDATE'], 0, 8));

        Out::show($this->nameForm . '_paneAllegati');
        //Out::block($this->nameForm . '_paneAllegati');
        Out::hide($this->nameForm . '_divBottoniAllega');
        Out::hide($this->gridAllegati . '_delGridRow');
        Out::show($this->nameForm . '_CercaProtPre');
        Out::hide($this->nameForm . '_DuplicaProt');
    }

    private function AssegnaDatiFromSdi($dataArrivo) {
        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_39 = $this->proLib->GetAnaent('39');
        $anaent_45 = $this->proLib->GetAnaent('45');
        $ggFattura = $anaent_39['ENTDE3'];
        /* Carico Sub Form */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();
        // Uso estratto
        $Oggetto = '';
        $ScaricaUffici = true;
        if ($this->currObjSdi->isFatturaPA()) {
            $EstrattoFattura = $this->currObjSdi->getEstrattoFattura();
            foreach ($EstrattoFattura as $keyFatt => $Fattura) {
                foreach ($Fattura['Body'] as $keyBody => $BodyFattura) {
                    $DataFattura = $dataVer = date("d/m/Y", strtotime($BodyFattura['DataFattura']));
                    $Oggetto .= ' Fattura N. ' . $BodyFattura['NumeroFattura'] . ' del ' . $DataFattura . ', ';
                }
            }
            $Oggetto .= ' Fornitore: ' . $Fattura['Header']['Fornitore']['Denominazione'] . '.';
            $TipoDoc = 'EFAA';
            if ($anaent_38['ENTDE1']) {
                $TipoDoc = $anaent_38['ENTDE1'];
            }

            $EstrattoMessaggio = $this->currObjSdi->getEstrattoMessaggio();
            // Aggiungo all'oggetto il codice destinatario:
            if ($EstrattoMessaggio['CodiceDestinatario']) {
                $Oggetto .= ' Codice Destinatario: ' . $EstrattoMessaggio['CodiceDestinatario'] . '. ';
            }
            // Controllo presenza uffico specifico.
            if ($EstrattoMessaggio['CodiceDestinatario']) {
                $anauff_tab = $this->proLib->GetAnauff($EstrattoMessaggio['CodiceDestinatario'], 'uffSdi');
                if ($anauff_tab) {
                    $ScaricaUffici = false;
                    if (!$anaent_45['ENTDE3'] && $anauff_tab[0]['UFFFATOGG']) {
                        $proSubTrasmissioni->scaricaUfficio($anauff_tab[0]);
                        $proSubTrasmissioni->elaboraAlbero();
                    }
                }
            }
        } else {
            $Oggetto = proSdi::$ElencoTipiMessaggio[$this->currObjSdi->getTipoMessaggio()];
            $TipoDoc = 'SDIA';
            if ($anaent_38['ENTDE3']) {
                $TipoDoc = $anaent_38['ENTDE3'];
            }
        }
        // Prendo e decodifico Anatipodoc
        $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($TipoDoc, 'codice');
        Out::valore($this->nameForm . '_ANAPRO[PROCODTIPODOC]', $AnaTipoDoc_rec['CODICE']);
        Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
        if ($AnaTipoDoc_rec['OGGASSOCIATO']) {
            $this->DecodAnadog($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice', $ScaricaUffici);
        }
        // Qui controllo se c'è un oggetto associato all'ufficio.
        if (isset($anauff_tab[0])) {
            if ($anauff_tab[0]['UFFFATOGG']) {
                $this->DecodAnadog($anauff_tab[0]['UFFFATOGG'], 'codice', true);
            }
        }
        /*
         * Ricarico subTrasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();

        if (!$this->currObjSdi->isFatturaPA()) {
            // Controllo Protocolli Collegati solo se è un messaggio di notifica
            $AnaproCollegato = $this->proLibSdi->GetAnaproDaCollegareFromEstratto($this->currObjSdi->getEstrattoMessaggio(), 'A');
            if (!$AnaproCollegato) {
                Out::msgInfo('Attenzione', 'Non è stato possibile trovare il protocollo collegato a questo Messaggio di Notifica.<br>' . $this->proLibSdi->getErrMessage());
            } else {
                Out::valore($this->nameForm . '_ANAPRO[PROPARPRE]', $AnaproCollegato['PROPAR']);
                Out::valore($this->nameForm . '_Propre1', substr($AnaproCollegato['PRONUM'], 4));
                Out::valore($this->nameForm . '_Propre2', substr($AnaproCollegato['PRONUM'], 0, 4));
            }
            // Qui si potrebbero caricare gli stessi destinatari interni.
        } else {
            foreach ($proArriDest as $key => $value) {
                if ($dataArrivo && intval($ggFattura) >= 1) {
                    $dataTermine = date('Ymd', strtotime($dataArrivo . ' + ' . intval($ggFattura) . ' days'));
                    $proArriDest[$key]['TERMINE'] = $dataTermine;
                }
            }
            if ($proArriDest) {
                $proSubTrasmissioni->setProArriDest($proArriDest);
                $proSubTrasmissioni->elaboraAlbero();
            }
        }

        Out::valore($this->nameForm . '_Oggetto', $Oggetto);
    }

    private function CaricaRiscontro() {
        if (!itaLib::createPrivateUploadPath()) {
            Out::msgStop("Gestione Arrivo da PEC", "Creazione ambiente di lavoro temporaneo fallita");
            $this->returnToParent();
        }
        Out::valore($this->nameForm . '_ANAPRO[PRODAS]', $_POST['datiRiscontro']['PRODAS']);
        Out::valore($this->nameForm . '_ANAPRO[PRODAA]', $_POST['datiRiscontro']['PRODAA']);
        Out::valore($this->nameForm . '_Propre1', $_POST['datiRiscontro']['Propre1']);
        Out::valore($this->nameForm . '_Propre2', $_POST['datiRiscontro']['Propre2']);
        Out::valore($this->nameForm . '_ANAPRO[PROPARPRE]', $_POST['datiRiscontro']['PROPARPRE']);

        $destFile = itaLib::getPrivateUploadPath();
        // Carico gli Allegati
        foreach ($_POST['datiRiscontro']['ELENCOALLEGATI'] as $elemento) {
            $randName = md5(rand() * time()) . "." . pathinfo($elemento['DATAFILE'], PATHINFO_EXTENSION);
            if (@copy($elemento['FILE'], $destFile . '/' . $randName)) {
                $tipoAlle = '';
                $daMail = '';
                $this->proArriAlle[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $destFile . '/' . $randName,
                    'FILENAME' => $randName,
                    'FILEINFO' => $elemento['DATAFILE'],
                    'DOCTIPO' => $tipoAlle,
                    'DAMAIL' => $daMail,
                    'DOCNAME' => $elemento['DATAFILE'],
                    'DOCIDMAIL' => '',
                    'DOCFDT' => date('Ymd'),
                    'DOCRELEASE' => '1',
                    'DOCSERVIZIO' => 0,
                );
            } else {
                Out::msgStop("Errore", "Errore in copia File: {$elemento['FILE']} su $destFile/$randName");
            }
        }
        $this->caricaGrigliaAllegati();
        // Prendo e decodifico Anatipodoc
        if ($_POST['datiRiscontro']['PROCODTIPODOC']) {
            $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['datiRiscontro']['PROCODTIPODOC'], 'codice');
            Out::valore($this->nameForm . '_ANAPRO[PROCODTIPODOC]', $AnaTipoDoc_rec['CODICE']);
            Out::valore($this->nameForm . '_descr_tipodoc', $AnaTipoDoc_rec['DESCRIZIONE']);
            if ($AnaTipoDoc_rec['OGGASSOCIATO']) {
                $this->DecodAnadog($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice', true);
            }
        }

        Out::valore($this->nameForm . '_Oggetto', $_POST['datiRiscontro']['Oggetto']);
        if (isset($_POST['datiRiscontro']['PROMAIL']) && $_POST['datiRiscontro']['PROMAIL']) {
            $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
            Out::valore($this->proSubMittDest . '_ANAPRO[PROMAIL]', $_POST['datiRiscontro']['PROMAIL']);
        }
        Out::show($this->nameForm . '_paneAllegati');
//        Out::block($this->nameForm . '_paneAllegati');
    }

    /**
     * 
     * @param type $tipo
     *  A = Annulla
     *  Vuoto = Riattiva
     * @return boolean
     */
    private function AnnullaRiattiva($tipo = '', $Annullamento = array()) {
        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
        if ($tipo == 'A') {
            $update_Info = 'Oggetto: Annullamento Anapro. Prot. ';
            $anapro_rec['PROSTATOPROT'] = proLib::PROSTATO_ANNULLATO;
        } else {
            $update_Info = 'Oggetto: Riattivazione Anapro. Prot. ';
            $anapro_rec['PROSTATOPROT'] = 0;
        }
        $update_Info .= $anapro_rec['PRONUM'] . $anapro_rec['PROPAR'];
        $anapro_rec['PROANNAUTOR'] = $Annullamento['PROANNAUTOR'];
        $anapro_rec['PROANNMOTIVO'] = $Annullamento['PROANNMOTIVO'];
        $anapro_rec['PROANNPDATA'] = $Annullamento['PROANNPDATA'];
        $anapro_rec['PROANNPNUM'] = $Annullamento['PROANNPNUM'];
        $anapro_rec['PROANNPTIPO'] = $Annullamento['PROANNPTIPO'];
        // Data e Ora Ultima Modifica
        $anapro_rec['PRORDA'] = date('Ymd');
        $anapro_rec['PROROR'] = date('H:i:s');
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }
        /*
         * Controllo base dati variata e conservata.
         */
        if (!$this->proLibConservazione->CheckConservazioneBaseDatiVariata($anapro_rec['ROWID'])) {
            Out::msgInfo('Controllo Conservazione', 'Errore in controllo variazioni per conservazione.' . $this->proLibConservazione->getErrMessage());
        }
        return true;
    }

    private function lockAnaent($rowid) {
        $retLock = ItaDB::DBLock($this->PROT_DB, "ANAENT", $rowid, "", 20);
        if ($retLock['status'] != 0) {
            Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVI non Riuscito.');
            return false;
        }
        return $retLock;
    }

    private function unlockAnaent($retLock = '') {
        if ($retLock)
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            Out::msgStop('Errore', 'Sblocco Tabella PROGRESSIVI non Riuscito.');
        }
    }

    private function ApriRiepilogo() {
//!!!! Form Riepilogativa !!!!
        $anaent_24 = $this->proLib->GetAnaent('24');
        $anaent_32 = $this->proLib->GetAnaent('32');
        if (($this->tipoProt == 'A' || $this->tipoProt == 'P') && $anaent_24['ENTDE4'] == 1) {
            $avviso = '';
            // Avvisare solo se parametro richiede "avviso"
            if ($anaent_32['ENTDE4'] == 1) { // && $tipoReg == "Aggiorna") {
                $avviso = "ATTENZIONE!<br>PROTOCOLLO SENZA ALLEGATI.";
                foreach ($this->proArriAlle as $allegato) {
                    if ($allegato['DOCSERVIZIO'] == 0) {
                        $avviso = '';
                        break;
                    }
                }
            }
            $titolarioDesc = $_POST[$this->nameForm . '_TitolarioDecod'];
            $anapro_rec = $this->proLib->GetAnapro($this->proArriIndice, 'rowid');
            $this->rowidAppoggio = $anapro_rec['ROWID'];
            $model = 'proMsgQuestion';
            itaLib::openForm($model, true);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST[$model . '_returnModel'] = $this->nameForm;
            $_POST[$model . '_DATIPROT'] = $anapro_rec;
            $_POST[$model . '_DATIPROT']['TitolarioDecod'] = $titolarioDesc;
            $anaent_24 = $this->proLib->GetAnaent('24');
            $_POST[$model . '_etichette'] = $anaent_24['ENTDE3'];
            $_POST[$model . '_avviso'] = $avviso;
            $model();
        }
        /*
         * Nuovo controllo, se è fascicolato il prot precedente. 
         */

        if ($anapro_rec['PROPRE'] || $this->rowidAnaproDuplicatoSorgente) {
            if ($this->rowidAnaproDuplicatoSorgente) {
                if ($this->proLibFascicolo->CtrProtFascicolato($this->rowidAnaproDuplicatoSorgente)) {
                    $AnaproDup_rec = $this->proLib->GetAnapro($this->rowidAnaproDuplicatoSorgente, 'rowid');
                    if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                        $this->proLibFascicolo->ApriSelezioneFascicoloFromProt($AnaproDup_rec['ROWID'], $this->nameForm, 'returnMultiSelezioneFascicolo', $anapro_rec);
                    }
                }
            } else {
                if ($this->proLibFascicolo->CtrProtPreFascicolato($anapro_rec['ROWID'])) {
                    $AnaproPre_rec = $this->proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
                    if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                        $this->proLibFascicolo->ApriSelezioneFascicoloFromProt($AnaproPre_rec['ROWID'], $this->nameForm, 'returnMultiSelezioneFascicolo', $anapro_rec);
                    }
                }
            }


            $this->rowidAnaproDuplicatoSorgente = '';
        }
    }

    private function ApriScanner() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }
        $anapro_rec = $this->proLib->GetAnapro($_POST[$this->nameForm . '_ANAPRO']['ROWID'], 'rowid');
//        $parametriCaps = $this->caricaParametriCaps($anapro_rec);
//        $endorserParams = $this->getScannerEndorserParams($anapro_rec);
        $endorserParams = $this->proLib->getScannerEndorserParams($anapro_rec);
        if ($anapro_rec['PRONUM'] != '') {
            $titolo = "Scansione del Protocollo " . (int) substr($anapro_rec['PRONUM'], 4) . " del " . (int) substr($anapro_rec['PRONUM'], 0, 4);
        } else {
            $titolo = "Scansione del Protocollo";
        }

        $anaent_34 = $this->proLib->GetAnaent('34');
        $forzaMicrorei = '';
        if ($anaent_34['ENTDE5'] == '1') {
            $forzaMicrorei = 'microrei';
        }

        $modelTwain = 'utiTwain';
        itaLib::openForm($modelTwain, true);
        $appRoute = App::getPath('appRoute.' . substr($modelTwain, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $modelTwain . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST[$modelTwain . '_endorserParams'] = $endorserParams;
        $_POST[$modelTwain . '_forzaDevice'] = $forzaMicrorei;
        $_POST[$modelTwain . '_titolo'] = $titolo;
        $_POST[$modelTwain . '_returnModel'] = $this->nameForm;
        $_POST[$modelTwain . '_returnMethod'] = 'returnFileFromTwain';
        $_POST[$modelTwain . '_returnField'] = $this->nameForm . '_Scanner';
        $_POST[$modelTwain . '_closeOnReturn'] = '1';
        $modelTwain();
    }

    private function ApriScannerShared() {
        /*
         * Lettura parametro per path protocollo
         */
        $Anaent57_rec = $this->proLib->GetAnaent(57);
        $PathProtocollo = $Anaent57_rec['ENTVAL'];

        $model = 'utiAcqrMen';
        /* @var $formObj utiAcqrMen */
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnEvent('returnScannerShared');
        $formObj->setEvent('openform');
        $formObj->setReturnId('');
        if ($PathProtocollo) {
            $formObj->setPath($PathProtocollo);
        }
        $formObj->setOpenModeWait();
        itaLib::openForm($model);
        $formObj->parseEvent();
        $formObj->setReturnModel($this->nameForm);
    }

    private function verificaAllega($origFile, $destFile, $fileInfo, $docName, $source) {
        $ext = strtolower(pathinfo($origFile, PATHINFO_EXTENSION));
        if ($ext == 'xhtml' || $ext == 'p7m' || ( $this->tipoProt != 'P' && $this->tipoProt != 'C')) {
            $this->AllegaFile($destFile, $fileInfo, $docName);
            return true;
        }
        $campi = array();
        $anaent_35 = $this->proLib->GetAnaent('35');
        $anaent_47 = $this->proLib->GetAnaent('47');
        $fl_posMarcatura = false;

        if ($anaent_35['ENTDE2'] != '' && $anaent_35['ENTDE3'] != '') {
            if ($ext === 'pdf') {
                $checked = 'checked';
                if ($source == 'scanner') {
                    $checked = '';
                }
                $campi[] = array(
                    'label' => array(
                        'value' => "Marca file PDF con segnatura Protocollo",
                        'style' => 'width:250px;display:block;float:right;padding: 0 5px 0 0;text-align:left;'
                    ),
                    'id' => $this->nameForm . '_MarcaAllegato',
                    'name' => $this->nameForm . '_MarcaAllegato',
                    'type' => 'checkbox',
                    'style' => 'margin:2px;width:50px;',
                    $checked => $checked,
                    'class' => 'ita-edit ita-checkbox ui-widget-content ui-corner-all'
                );
                /* Scelta Posizione Marcatura */
                $campi[] = array(
                    'label' => array(
                        'value' => "Posizione Marcatura",
                        'style' => 'margin-left:55px;width:150px;display:block;float:left;padding: 0 5px 0 0;text-align:left;'
                    ),
                    'id' => $this->nameForm . '_PosizioneMarcatura',
                    'name' => $this->nameForm . '_PosizioneMarcatura',
                    'type' => 'select',
                    'style' => 'margin:2px;',
                    'class' => 'ita-select'
                );
                $fl_posMarcatura = true;
            }
        }
        /*
         * Se allegati obbligatori e protocollo provvisorio:
         * Il controllo firmatario lo effettuo sui dati che ho in post.
         */
        $anaent_32 = $this->proLib->GetAnaent('32');
        $btnConferma = $this->nameForm . '_ConfermaAllegaDocumento';
        if ($anaent_32['ENTDE4'] == '2' && !$this->anapro_record) {
//            $anades_mitt['DESCOD'] = $this->formData[$this->nameForm . '_DESCOD'];
//            $this->AllegaFile($destFile, $fileInfo, $docName);
//            return true;
            $btnConferma = $this->nameForm . '_ConfermaAllegaDocumentoProvvisorio';
        } else {
            $profilo = proSoggetto::getProfileFromIdUtente();
            $anades_mitt = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
        }
        if ($profilo['COD_SOGGETTO'] != $anades_mitt['DESCOD']) {
            $campi[] = array(
                'label' => array(
                    'value' => "Invia alla Firma",
                    'style' => 'width:250px;display:block;float:right;padding: 0 5px 0 0;text-align:left;'
                ),
                'id' => $this->nameForm . '_MettiDaFirmare',
                'name' => $this->nameForm . '_MettiDaFirmare',
                'type' => 'checkbox',
                'style' => 'margin:2px;width:50px;',
                'class' => 'ita-edit ita-checkbox ui-widget-content ui-corner-all ita-edit-onchange'
            );
        }
        $anaent_34 = $this->proLib->GetAnaent('34');
        if ($anaent_34['ENTDE4'] == '1' && $this->anapro_record) {
            $campi[] = array(
                'label' => array(
                    'value' => "Firma del Documento",
                    'style' => 'width:250px;display:block;float:right;padding: 0 5px 0 0;text-align:left;'
                ),
                'id' => $this->nameForm . '_FirmaDocumento',
                'name' => $this->nameForm . '_FirmaDocumento',
                'type' => 'checkbox',
                'style' => 'margin:2px;width:50px;',
                'checked' => 'checked',
                'class' => 'ita-edit ita-checkbox ui-widget-content ui-corner-all'
            );
        }
        if (!$campi) {
            $this->AllegaFile($destFile, $fileInfo, $docName);
            return true;
        }
        $this->varAppoggio = array();
        $this->varAppoggio['origFile'] = $origFile;
        $this->varAppoggio['destFile'] = $destFile;
        $this->varAppoggio['fileInfo'] = $fileInfo;
        $this->varAppoggio['docName'] = $docName;
        Out::msgInput("Salva allegato", $campi, array('Conferma' => array('id' => $btnConferma, 'model' => $this->nameForm)), $this->nameForm);
        /* Scelta Posizione Marcatura */
        if ($fl_posMarcatura) {
            Out::select($this->nameForm . '_PosizioneMarcatura', 1, "", "1", "Posizione predefinita");
            $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
            foreach ($PosizioniMarcatura as $key => $Segnatura) {
                Out::select($this->nameForm . '_PosizioneMarcatura', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
            }
            Out::valore($this->nameForm . '_PosizioneMarcatura', $anaent_47['ENTDE3']);
        }
//        Out::msgInput("Salva allegato", $campi, array('Conferma' => array('id' => $this->nameForm . '_ConfermaAllegaDocumento', 'model' => $this->nameForm)), 'desktopBody');
        return true;
    }

    private function verificaScanner($origFile) {
        $destFile = itaLib::getPrivateUploadPath() . "/" . $origFile;
        $fileInfo = "File Originale: Da scanner";
        $anaent_36 = $this->proLib->GetAnaent('36');
        switch ($anaent_36['ENTDE1']) {
            case '':
            default:
                $docName = "Scansione-" . date("d-m-Y_H-i-s") . "." . pathinfo($origFile, PATHINFO_EXTENSION);
                break;
            case '1':
                $docName = $this->anapro_record['PRONUM'] . '-' . $this->anapro_record['PROPAR'] . '-' . date("d-m-Y_H-i-s") . "." . pathinfo($origFile, PATHINFO_EXTENSION);
                break;
        }
        return $this->verificaAllega($origFile, $destFile, $fileInfo, $docName, 'scanner');
    }

    private function verificaUpload() {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
                return false;
            }
        }
        if ($_POST['response'] == 'success') {
            $origFile = $_POST['file'];
            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
            $randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            $fileInfo = "File Originale: " . $origFile;
            $docName = $origFile;

            //Out::msgInfo('post', print_r($uplFile, true));
            if (!@rename($uplFile, $destFile)) {
                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                return false;
            } else {
                return $this->verificaAllega($origFile, $destFile, $fileInfo, $docName, 'upload');
            }
        } else {
            Out::msgStop("Upload File", "Errore in Upload");
            return;
        }
    }

    private function AllegaFile($destFile, $fileInfo = '', $docName = '', $docmeta = '', $mettiAllaFirma = '') {
        $tipo = '';
//        if (count($this->proArriAlle) >= 1) {
//            $tipo = 'ALLEGATO';
//        }

        foreach ($this->proArriAlle as $proArriAllegato) {
            if ($proArriAllegato['DOCTIPO'] == '') {
                $tipo = 'ALLEGATO';
            }
        }
        $proArriAllePre = $this->proArriAlle;
        $this->proArriAlle[] = array(
            'ROWID' => 0,
            'FILEPATH' => $destFile,
            'FILENAME' => pathinfo($destFile, PATHINFO_BASENAME),
            'NOMEFILE' => $docName,
            'FILEINFO' => $fileInfo,
            'DOCNAME' => $docName,
            'DOCTIPO' => $tipo,
            'DOCFDT' => date('Ymd'),
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => 0,
            'DOCMETA' => ($docmeta) ? serialize($docmeta) : "",
            'METTIALLAFIRMA' => $mettiAllaFirma
                //,'PREVIEW' =>  "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Allegato \"></span>"
        );
        /*
         * Controllo se sto caricando una fattura in partenza.
         */
        $anapro_rec = $_POST[$this->nameForm . '_ANAPRO'];
        $anapro_rec['PROPAR'] = $this->tipoProt;
        if ($this->proLibSdi->ControllaSePartenzaSdi($anapro_rec)) {
            $ext = pathinfo($destFile, PATHINFO_EXTENSION);
            if (strtolower($ext) == 'zip' || strtolower($ext) == 'xml' || strtolower($ext) == 'p7m') {
                $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $docName);
                $this->currObjSdi = proSdi::getInstance($FileSdi);
            }
        }
        /* Se allegati obbligatori e non è presente un anapro_record:
         * Si stanno inserendo allegati provvisori.
         */
        $anaent_32 = $this->proLib->GetAnaent('32');
        if ($anaent_32['ENTDE4'] == '2' && !$this->anapro_record) {
            $this->CaricaAllegati();
            return true;
        }

        if (!$this->proLibAllegati->ControlloAllegatiPreProtocollo($this->proArriAlle)) {
            // Qui prevedere una pulizia dell'ultimo record inserito.. se ha dato errore deve essere escluso.
            $this->proArriAlle = $proArriAllePre;
            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
            return false;
        }

        // ***@Alfresco
        /* 1. Controllo Tipologie Allegati - Riordina */
        $this->proArriAlle = $this->proLibAllegati->ControlloAllegatiProtocollo($this->proArriAlle, $this->currObjSdi);

        /* 2. Salvataggio metadati. */
        if ($this->proLibSdi->ControllaSePartenzaSdi($this->anapro_record)) {

            $Ret = $this->SalvaMetadatiPartenzaSdi($this->anapro_record, $destFile, $docName);
            if ($Ret['stato'] == false) {
                Out::msgInfo('Attenzione', 'Errore nel salvataggio Metadati SDI.<br>' . $Ret['messaggio']);
                /* Pulizia degli eventuali metadati inseriti */
                $proLibTabDag = new proLibTabDag();
                $retCanc = $proLibTabDag->CancellaTabDagSdi($this->anapro_record, 'MESSAGGIO_SDI');
                return false;
            }
        }
        /* 3. Preparazione Obj Protocollo */
        $numero = substr($this->anapro_record['PRONUM'], 4);
        $anno = substr($this->anapro_record['PRONUM'], 0, 4);
        $tipo = $this->anapro_record['PROPAR'];
        $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $tipo, '');
        /* 4. Passo Oggetto Protocollo a Gestione Allegati */
        $risultato = $this->proLibAllegati->GestioneAllegati($this, $this->anapro_record['PRONUM'], $this->tipoProt, $this->proArriAlle, $this->anapro_record['PROCON'], $this->anapro_record['PRONOM'], $objProtocollo);
        if (!$risultato) {
            Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
            /* Pulizia degli eventuali metadati inseriti */
            if ($this->proLibSdi->ControllaSePartenzaSdi($this->anapro_record)) {
                $proLibTabDag = new proLibTabDag();
                $retCanc = $proLibTabDag->CancellaTabDagSdi($this->anapro_record, 'MESSAGGIO_SDI');
            }
            return false;
        }
        $this->ricaricaFascicolo();
        $this->CaricaAllegati();
        // @TODO SPOSTARE ?

        Out::setFocus('', $this->nameForm . '_wrapper');
        return true;
    }

    private function stampaEtichetta() {
        $anaent_2 = $this->proLib->GetAnaent('2');
        $anaent_24 = $this->proLib->GetAnaent('24');
        $anapro_rec = $this->proLib->GetAnapro($this->proArriIndice, 'rowid');
        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($anaent_24['ENTDE3'] == 1) {
            //$prntNum = $anaent_24['ENTDE5'];
            $prntNum = '0';
            if ($profilo['PRNT_ETICUTE']) {
                $prntNum = $profilo['PRNT_ETICUTE'];
            }
            $proEtic = new proEtic();
            $proEtic->stampaEtichettaProtocollo($anapro_rec, $prntNum, $anaent_2['ENTDE1']);
        } else if ($anaent_24['ENTDE3'] == 5) {
            $utente = App::$utente->getKey('nomeUtente');
            $tmppro_tab = $this->proLib->getGenericTab("SELECT ROWID FROM TMPPRO WHERE UTENTE='$utente'");
            if ($tmppro_tab) {
                foreach ($tmppro_tab as $tmppro_del) {
                    if (!$this->deleteRecord($this->PROT_DB, 'TMPPRO', $tmppro_del['ROWID'], '', 'ROWID', false)) {
                        break;
                    }
                }
            }
            $uffici = $this->proLib->getStringaUffici($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $tmppro_rec = array();
            $tmppro_rec['UTENTE'] = $utente;
            $tmppro_rec['CHIAVENUM'] = $anapro_rec['PRONUM'];
            $tmppro_rec['CAMPO1'] = $uffici;
            $this->insertRecord($this->PROT_DB, 'TMPPRO', $tmppro_rec, '', 'ROWID', false);
            $sql = "SELECT * FROM ANAPRO LEFT OUTER JOIN TMPPRO ON PRONUM=CHIAVENUM WHERE ANAPRO.ROWID=" . $anapro_rec['ROWID'];
            include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
            $itaJR = new itaJasperReport();
            $parameters = array("Sql" => $sql,
                "Ente" => $anaent_2['ENTDE1']
            );
            $itaJR->runSQLReportPDF($this->PROT_DB, 'proStampaZebra', $parameters);
            $tmppro_tabfin = $this->proLib->getGenericTab("SELECT ROWID FROM TMPPRO WHERE UTENTE='$utente'");
            if ($tmppro_tabfin) {
                foreach ($tmppro_tabfin as $tmppro_del) {
                    if (!$this->deleteRecord($this->PROT_DB, 'TMPPRO', $tmppro_del['ROWID'], '', 'ROWID', false)) {
                        break;
                    }
                }
            }
        } else if ($anaent_24['ENTDE3'] > 1) {
            $this->rowidAppoggio = $anapro_rec['ROWID'];
            $this->grigliaStampaEtichette($anaent_24['ENTDE3']);
        } else {
            return false;
        }
    }

    private function stampaEtichettaIndirizzi() {
        $proEtic = new proEtic();
        $anaent_rec = $this->proLib->GetAnaent('24');
        $prntNum = $anaent_rec['ENTDE5'];
        $anapro_rec = $this->proLib->GetAnapro($this->proArriIndice, 'rowid');
        $anades_rec['DESNOM'] = $anapro_rec['PRONOM'];
        $anades_rec['DESIND'] = $anapro_rec['PROIND'];
        $anades_rec['DESCAP'] = $anapro_rec['PROCAP'];
        $anades_rec['DESCIT'] = $anapro_rec['PROCIT'];
        $anades_rec['DESPRO'] = $anapro_rec['PROPRO'];
        $proEtic->stampaEtichettaIndirizzi($anades_rec, $prntNum);
        $anades_rec = array();
        $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], 'D');
        foreach ($anades_tab as $anades_rec) {
            $proEtic->stampaEtichettaIndirizzi($anades_rec, $prntNum);
        }
    }

    private function Registra($motivo = '') {
        if ($_POST[$this->nameForm . '_ANAPRO']['ROWID'] > 0) {
            $tipoReg = "Aggiorna";
        } else {
            $tipoReg = "Aggiungi";
        }
        $anaent_49 = $this->proLib->GetAnaent('49');


        /* Controllo degli eventuali allegati al protocollo */
        if (!$this->proLibAllegati->ControlloAllegatiPreProtocollo($this->proArriAlle)) {
            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
            return false;
        }

        $rowid = $this->registraPro($tipoReg, $motivo);
        if ($rowid == false || $rowid == 'Error') {
            $mesg = "Errore in registrazione dati protocollo.";
            Out::msgStop("ATTENZIONE!", $mesg);
            Out::hide($this->nameForm . '_Registra');
            return $mesg;
        }
        if ($this->LockIdMail) {
            $this->proLibMail->unlockMail($this->LockIdMail);
            $this->LockIdMail = array();
        }
        $datipost = $this->fileDaPEC['DATIPOST'];
        $this->rowidAppoggio = $rowid;
        $tipoMessaggio = '';
        if (!$this->disabledRec && !$this->consultazione) {
            $tipoMessaggio = 'riepilogo';
        } else {
            $tipoMessaggio = 'blocco';
        }
        $InvioConfermaMail = $this->inviaConfermaMail;
        $currObjSdi = $this->currObjSdi;
        // Salvo tabdag e registro il prot collegato.
        // Solo all'aggiungi..
        if ($tipoReg == 'Aggiungi') {
            $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
            /**
             * Carico i metadati da oggetto sdi
             */
//            if (!$anaent_49['ENTDE1']) {// Carico i metadati DOPO, solo se non è attivo salvataggio su alfresco  // ***@Alfresco
//                $retTabDag = $this->InserisciTabDag($anapro_rec, $currObjSdi);
//                if ($retTabDag['stato'] == false) {
//                    Out::msgStop("Attenzione!", $retTabDag['messaggio']);
//                }
//            }
            /**
             * Esporto files xml eventualmente arrivati nel repository attivato.
             * SE: EFAA o SDIA ( Abilitato per ora su DT )
             */
//            if (substr($anapro_rec['PROPAR'], 0, 1) == 'A') {
            if (is_object($currObjSdi)) {
                if ($this->proLibSdi->CheckAnaproEsportabile($anapro_rec)) {
                    $proLibSdi = new proLibSdi();
                    $retStatus = $proLibSdi->AllegatiSDI2Repository($anapro_rec);
                    if ($retStatus['ESPORTAZIONE']) {
                        $OutMsg = 'msgInfo';
                        Out::msgBlock('', 3000, true, $retStatus['MESSAGGIO']);
                    } else {
                        $OutMsg = 'msgStop';
                        Out::$OutMsg($retStatus['RISULTATO'], $retStatus['MESSAGGIO']);
                    }
                }
            }
//            }
        }
        if ($this->datiFascicolazine['FASCICOLA_PROPRE'] == true) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proArriFascicoli.class.php';
            $proArriFascicoli = new proArriFascicoli();
            $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
            $proArriFascicoli->FascicolaProPre($this, $anapro_rec);
        }
        // Notifica a firmatario sostituto:
        if ($this->DelegatoFirmatario['DELEGATO'] == true) {
            // Informazione Firmatario:
            $Anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
            $arcite_tab = $this->proLib->GetArcite($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR']);
            $iter = proIter::getInstance($this->proLib, $Anapro_rec);
            $dest = $extraParam = array();
            $dest['DESCUF'] = $this->DelegatoFirmatario['UFFDELEGANTE'];
            $dest['DESCOD'] = $this->DelegatoFirmatario['DELEGANTE'];
            $dest['DESGES'] = 0;
            $extraParam['NOTE'] = 'DELEGATO ' . $this->DelegatoFirmatario['NOMINATIVO'] . ' INDICATO COME FIRMATARIO DEL PROTOCOLLO.';
            $extraParam['NODELEGA'] = true;
            $extraParam['ITETIP'] = proIter::ITETIP_PARERE_DELEGA;
            $iter->insertIterNode($dest, $arcite_tab[0], $extraParam);
        }


        // Visualizzo il dettaglio del protocollo
        $anapro_rec = $this->Modifica($rowid);
        if (( $this->tipoProt == 'A' || $this->tipoProt == 'P') && !$datipost) {
            if ($tipoMessaggio == 'riepilogo') {
                $this->ApriRiepilogo();
            } else {
                Out::msgBlock('', 3000, true, "Dati Aggiornati");
            }
        }

        /* Controllo se Fattura da sapcchettare + parametro per abilitare questo controllo. */
        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_47 = $this->proLib->GetAnaent('47');
        $anaent_49 = $this->proLib->GetAnaent('49');
        /* Controllo se attivo parametro spacchetta automatico */
        if ($anaent_49['ENTDE2']) {
            /* Controllo se è una fattura elettronica in arrivo */
            if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] && $anaent_38['ENTDE1']) {
                if (!$this->proLibAllegati->SpacchettaFattura($rowid)) {
                    /* Se è tronato errore 2 non è bloccante. */
                    if ($this->proLibAllegati->getErrCode() != 2) {
                        Out::msgStop('Attenzione', $this->proLibAllegati->getErrMessage());
                    }
                }
            }
        }
        /*
         * Accettazione/Rifiuto Fatture
         */
        if ($anaent_49['ENTDE2']) {
            /* Controllo se è una fattura SDIP */
            if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4'] && $anaent_38['ENTDE4']) {
                if (!$this->proLibAllegati->AccettaRifiutaFatturaSpacchettata($anapro_rec)) {
                    Out::msgStop('Attenzione', $this->proLibAllegati->getErrMessage());
                }
            }
        }


        if (($this->tipoProt == 'A' || $this->tipoProt == 'P') && $tipoReg == "Aggiungi") {
            if (!$datipost) {
                $anaent_3 = $this->proLib->GetAnaent('3');
                if ($anaent_3['ENTDE2']) {
                    $this->stampaEtichetta();
                }
            }
            $anaent_29 = $this->proLib->GetAnaent('29');
            $inviaMail = false;
            if ($anaent_29['ENTDE2'] == '1' && $datipost) {
                $inviaMail = $this->inviaMailDestinatari($rowid);
            }
            if ($datipost) { //&& $inviaMail === false) {
                $this->returnGestioneMail($anapro_rec['PRONUM'], $datipost);
            }

            /*
             * Controllo se richiesta sempre notiica di ricezione.
             * Ricavo il mittente mail.
             */
            $anaent_54 = $this->proLib->GetAnaent('54');
            if ($anaent_54['ENTDE4'] == '1') {
                $MailArchivioProt_rec = $this->proLib->GetMailArchivioProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                $InvioConfermaMail = $MailArchivioProt_rec['FROMADDR'];
                $metadata = unserialize($MailArchivioProt_rec["METADATA"]);
                if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                    if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                        $InvioConfermaMail = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'];
                    }
                }
            }
            // Invio la notifica 
            if ($InvioConfermaMail != '') {
                if ($MailArchivioProt_rec['TIPOINTEROPERABILE']) {
                    $result = $this->proLibMail->InviaConfermaRicezione($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                    if (!$result) {
                        Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                    }
                } else {
                    $result = $this->proLibMail->InviaConfermaRicezione($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], false);
                    if (!$result) {
                        Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                    }
                }
            }
        }
        Out::setFocus('', $this->nameForm . '_Oggetto');
        return $anapro_rec['PRONUM'];
    }

    private function ricevuta() {
        $anaent_rec = $this->proLib->GetAnaent('2');
        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $sql = "SELECT * FROM ANAPRO WHERE ROWID=$this->rowidAppoggio";
        $anapro_rec = $this->proLib->GetAnapro($this->rowidAppoggio, 'rowid');
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $oggetto = $anaogg_rec['OGGOGG'];
        if ($anapro_rec['PROPAR'] == 'A') {
            $mittente = $anapro_rec['PRONOM'];
            $indirizzo = $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
            if ($anapro_rec['PROCAP'] != 0) {
                $indirizzo .= ' ' . $anapro_rec['PROCAP'];
            }
            if (trim($indirizzo) != '') {
                $mittente .= ' - ' . $indirizzo;
            }
            //Aggiungo Mail:
            if ($anapro_rec['PROMAIL']) {
                $mittente .= "<br>PEC/Mail: " . $anapro_rec['PROMAIL'];
            }
            $parameters = array("Sql" => $sql, "Ente" => $anaent_rec['ENTDE1'], "Oggetto" => $oggetto, "Mittente" => $mittente);
            $itaJR->runSQLReportPDF($this->PROT_DB, 'proRicevuta', $parameters);
        } else if ($anapro_rec['PROPAR'] == 'P') {
            $destinatario = $anapro_rec['PRONOM'];
            $indirizzo = $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
            if ($anapro_rec['PROCAP'] != 0) {
                $indirizzo .= ' ' . $anapro_rec['PROCAP'];
            }
            if (trim($indirizzo) != '') {
                $destinatario .= ' - ' . $indirizzo;
            }
            //Aggiungo Mail:
            if ($anapro_rec['PROMAIL']) {
                $destinatario .= "<br>PEC/Mail: " . $anapro_rec['PROMAIL'];
            }

            $parameters = array("Sql" => $sql, "Ente" => $anaent_rec['ENTDE1'], "Oggetto" => $oggetto, "Destinatario" => $destinatario);
            $itaJR->runSQLReportPDF($this->PROT_DB, 'proRicevutaPartenza', $parameters);
        }
        $anapro_rec['PRORICEVUTA'] = '1';
        $update_Info = 'Oggetto: ' . $anapro_rec['PRONUM'] . ' - ' . $anapro_rec['PROPAR'];
        if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info)) {
            return false;
        }
        Out::show($this->nameForm . '_ricevutaStampata');
    }

    public function assegnaDati($dati) {
        $_POST[$this->nameForm . "_ANAPRO"]["PRODAR"] = $this->workDate;
        $_POST[$this->nameForm . "_Oggetto"] = $dati['Oggetto'];
        $_POST[$this->nameForm . "_Propre1"] = $dati['NumeroAntecedente'];
        $_POST[$this->nameForm . "_Propre2"] = $dati['AnnoAntecedente'];
        $_POST[$this->nameForm . "_ANAPRO"]["PRONPA"] = $dati['ProtocolloMittente']['Numero'];
        $_POST[$this->nameForm . "_ANAPRO"]["PRODAS"] = $dati['ProtocolloMittente']['Data'];
        $_POST[$this->nameForm . "_ANAPRO"]["PRODAA"] = $dati['DataArrivo'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROEME"] = $dati['ProtocolloEmergenza'];
        $_POST[$this->nameForm . "_ANAPRO"]["PRODATEME"] = $dati['DataProtocolloEmergenza'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROORAEME"] = $dati['OraProtocolloEmergenza'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROCON"] = $dati['destinatari'][0]['CodiceMittDest'];
        $_POST[$this->nameForm . "_ANAPRO"]["PRONOM"] = $dati['destinatari'][0]['Denominazione'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROIND"] = $dati['destinatari'][0]['Indirizzo'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROCAP"] = $dati['destinatari'][0]['CAP'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROCIT"] = $dati['destinatari'][0]['Citta'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROPRO"] = $dati['destinatari'][0]['Provincia'];
        $_POST[$this->nameForm . "_ANAPRO"]["PROMAIL"] = $dati['destinatari'][0]['Email'];
        $profilo = proSoggetto::getProfileFromIdUtente();
        $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
        if ($anamed_rec) {
            $uffdes_tab = $this->proLib->GetUffdes($anamed_rec['MEDCOD']);
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $this->proLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $_POST[$this->nameForm . "_ANAPRO"]["PROUOF"] = $anauff_rec['UFFCOD'];
                    break;
                }
            }
        }
        for ($index = 1; $index < count($dati['destinatari']); $index++) {
            $destinatario = $dati['destinatari'][$index];
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESCOD'] = $destinatario['CodiceMittDest'];
            $salvaDest['DESNOM'] = $destinatario['Denominazione'];
            $salvaDest['DESIND'] = $destinatario['Indirizzo'];
            $salvaDest['DESCAP'] = $destinatario['CAP'];
            $salvaDest['DESCIT'] = $destinatario['Citta'];
            $salvaDest['DESPRO'] = $destinatario['Provincia'];
            $salvaDest['DESDAT'] = $this->workDate;
            $salvaDest['DESDAA'] = $this->workDate;
            $salvaDest['DESDUF'] = '';
            $salvaDest['DESANN'] = $destinatario['Annotazioni'];
            $salvaDest['DESTIPO'] = 'D';
            $salvaDest['DESMAIL'] = '';
            $salvaDest['DESCUF'] = '';
            $salvaDest['DESGES'] = 1;
            $salvaDest['TERMINE'] = '';
            $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
            $this->proAltriDestinatari[] = $salvaDest;
        }
        $categoria = $classe = $sottoclasse = $fascicolo = '';
        $titolario = $dati['Classificazione'];
        $separatore = '.';
        if ($separatore != '') {
            $titExp = explode($separatore, $titolario);
            $titElenco = array();
            foreach ($titExp as $value) {
                if ($value != '') {
                    $titElenco[] = $value;
                }
            }
            if ($titElenco[0]) {
                $categoria = str_pad($titElenco[0], 4, "0", STR_PAD_LEFT);
            }
            if ($titElenco[1]) {
                $classe = str_pad($titElenco[1], 4, "0", STR_PAD_LEFT);
            }
            if ($titElenco[2]) {
                $sottoclasse = str_pad($titElenco[2], 4, "0", STR_PAD_LEFT);
            }
            if ($titElenco[3]) {
                $fascicolo = str_pad($titElenco[3], 4, "0", STR_PAD_LEFT);
            }
        }
        $_POST[$this->nameForm . "_ANAPRO"]["PROCAT"] = $categoria;
        $_POST[$this->nameForm . "_Clacod"] = $classe;
        $_POST[$this->nameForm . "_Fascod"] = $sottoclasse;
        $_POST[$this->nameForm . "_Orgcod"] = $fascicolo;
        $_POST[$this->nameForm . "_ANAPRO"]["PROTSP"] = $dati['TipoSpedizione'];
        $_POST[$this->nameForm . "_Pronur"] = $dati['Spedizioni']['NumeroRaccomandata'];
        $_POST[$this->nameForm . "_Progra"] = $dati['Spedizioni']['Grammi'];
        $_POST[$this->nameForm . "_Proqta"] = $dati['Spedizioni']['Quantita'];
        $_POST[$this->nameForm . "_Prodra"] = $dati['Spedizioni']['DataSpedizione'];
    }

    private function CreaCombo() {
        $this->prouof = $this->proLib->caricaUof($this);
        Out::select($this->nameForm . '_ANAPRO[PROSECURE]', 1, 1, 1, 1);
        Out::select($this->nameForm . '_ANAPRO[PROSECURE]', 1, 2, 0, 2);
        Out::select($this->nameForm . '_ANAPRO[PROSECURE]', 1, 3, 0, 3);

        Out::select($this->nameForm . '_ANAPRO[PROPARPRE]', 1, '', '0', '');
        Out::select($this->nameForm . '_ANAPRO[PROPARPRE]', 1, 'A', '0', 'A');
        Out::select($this->nameForm . '_ANAPRO[PROPARPRE]', 1, 'P', '0', 'P');
        Out::select($this->nameForm . '_ANAPRO[PROPARPRE]', 1, 'C', '0', 'C');
    }

    private function registraSave($motivo, $rowid, $tipo = 'rowid') {
        App::requireModel('proProtocolla.class');
        $protObj = new proProtocolla();
        $protObj->registraSave($motivo, $rowid, $tipo);
    }

    private function disabilitaFormPerOggetto() {
        Out::block($this->nameForm . '_divAlto');
        Out::block($this->nameForm . '_divOggetto');
        Out::block($this->nameForm . '_divClaSpe');
        //Out::block($this->nameForm . '_paneDest');// Le trasmissioni interne servono in ogni caso.
        Out::block($this->nameForm . '_paneAllegati');
        Out::hide($this->nameForm . '_DuplicaProt');
        Out::hide($this->nameForm . '_Partenza');
        Out::hide($this->nameForm . '_Evidenza');
        Out::hide($this->nameForm . '_NonRiserva');
        Out::hide($this->nameForm . '_Riserva');
        Out::setFocus('', $this->nameForm . "_ANAPRO[PROCON]");
    }

    private function grigliaStampaEtichette($tipo) {
        $model = 'proStampaEtichetta';
        itaLib::openForm($model, true);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['tipo'] = $tipo;
        $_POST['chiave'] = $this->rowidAppoggio;
        $_POST[$model . '_returnModel'] = $this->nameForm;
        $model();
    }

    private function inviaRaccomandataDestinatari($rowid = '') {

//
// Leggo protocollo
//
        if ($rowid == '') {
            $anapro_rec = $_POST[$this->nameForm . '_ANAPRO'];
            $rowid = $anapro_rec['ROWID'];
        }

        $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');

//
// Controllo presenza Allegato
//
        $allegati = array();
        foreach ($this->proArriAlle as $allegato) {
            if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0 && strtolower(pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION)) == 'pdf') {
                $allegati[] = $allegato;
            }
        }
        if (!$allegati) {
            Out::msgStop("Apertura dettaglio Raccomandata", "Documento Mancante.");
            return false;
        }

//
// Creo Nuova transazione
//
        include_once ITA_BASE_PATH . '/apps/PosteItaliane/ptiLib.class.php';
        include_once ITA_BASE_PATH . '/apps/PosteItaliane/ptiXOLTransaction.class.php';
        $ptiLib = new ptiLib();
        $XOLTransaction = ptiXOLTransaction::getInstance($ptiLib);
        if (!$XOLTransaction) {
            Out::msgStop("Apertura dettaglio Raccomandata", "Errore apertura transazione.");
            return false;
        }
        $XOLTransaction->setTipoXOL('ROL');
//
// Mittente
//
//       $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $this->tipoProt, 'M');
        $anaent_2 = $this->proLib->GetAnaent('2');

        list($cap, $skip) = explode(" ", $anaent_2['ENTDE3'], 2);
        list($citta, $skip) = explode("(", $skip, 2);
        list($prov, $skip) = explode(")", $skip, 2);

        list ($dug, $ind) = explode(" ", $anaent_2['ENTDE2'], 2);
        list ($ind, $civico) = explode(',', $ind, 2);

//$list($prov,$skip2)=  explode(")", $prov);
        $XOLTransaction->setXOL_Mittente_rec(
                array(
                    "TIPOSOGGETTO" => "M",
                    "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
                    "STATO" => "ITA",
                    "PROVINCIA" => $prov,
                    "RAGIONESOCIALE" => $anaent_2['ENTDE1'],
                    "CITTA" => $citta,
                    "CAP" => $cap,
                    "DUG" => $dug,
                    "TOPONIMO" => trim($ind),
                    "NUMEROCIVICO" => trim($civico),
                    "ESPONENTE" => ""
                )
        );


        list ($dug, $ind) = explode(" ", $anapro_rec['PROIND'], 2);
        list ($ind, $civico) = explode(',', $ind, 2);

        $XOLNominativo = array(
            'TIPOSOGGETTO' => "D",
            "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
            "STATO" => "ITA",
            'PROVINCIA' => $anapro_rec['PROPRO'],
            'RAGIONESOCIALE' => $anapro_rec['PRONOM'],
            'CITTA' => $anapro_rec['PROCIT'],
            'CAP' => $anapro_rec['PROCAP'],
            'DUG' => trim($dug),
            'TOPONIMO' => trim($ind),
            "NUMEROCIVICO" => trim($civico),
            "ESPONENTE" => ""
        );
        $XOLTransaction->setXOL_Destinatari_rec($XOLNominativo);
        $XOLTransaction->setStato(ptiXOLTransaction::XOL_STATO_INSERITO);

        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();

        foreach ($this->proAltriDestinatari as $destinatario) {
            list ($dug, $ind) = explode(" ", $destinatario['DESIND'], 2);
            list ($ind, $civico) = explode(',', $ind, 2);
            $XOLNominativo = array(
                'TIPOSOGGETTO' => "D",
                "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
                "STATO" => "ITA",
                'PROVINCIA' => $destinatario['PROPRO'],
                'RAGIONESOCIALE' => $destinatario['DESNOM'],
                'CITTA' => $destinatario['DESCIT'],
                'CAP' => $destinatario['DESCAP'],
                'DUG' => trim($dug),
                'TOPONIMO' => trim($ind),
                "NUMEROCIVICO" => trim($civico),
                "ESPONENTE" => ""
            );
            $XOLTransaction->setXOL_Destinatari_rec($XOLNominativo);
            $XOLTransaction->setStato(ptiXOLTransaction::XOL_STATO_INSERITO);
        }

//
// Documento Allegato
//
        // Copio il file nella cartella temporanea:
        $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($allegati[0]['ROWID'], '', true);
        if (!$CopyPathFile) {
            Out::msgStop('Attenzione', "Errore nell'apertura del file Firmato. " . $this->proLibAllegati->getErrMessage());
            return false;
        }
        $XOLTransaction->setDataFile($CopyPathFile, $allegati[0]['FILEINFO'], true);
//        $XOLTransaction->setDataFile($allegati[0]['FILEPATH'], $allegati[0]['FILEINFO'], true);
        $XOLTransaction->setDescrizioneStato("Nominativi Modificati: Validare.");

//
// Apro Dialog di gestione
//
        itaLib::openForm('ptiROLAppend');
        /* @var $ptiROLAppendObj ptiROLAppend */
        $ptiROLAppendObj = itaModel::getInstance('ptiROLAppend');
        $ptiROLAppendObj->setXOLTransaction($XOLTransaction);
        $ptiROLAppendObj->setEvent('openform');
        $ptiROLAppendObj->setSourceClass(ptiXOLTransaction::XOL_CLASSE_ORIGINE_PROTOCOLLO);
        $ptiROLAppendObj->setReturnModel($this->nameForm);
        $ptiROLAppendObj->setReturnEvent('returnFromPtiROLAppend');
        $ptiROLAppendObj->setReturnId('');
        $ptiROLAppendObj->parseEvent();
    }

    private function inviaMailDestinatari($rowid = '') {
        if ($this->proLibAllegati->CheckAllegatiAllaFirma($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'])) {
            Out::msgStop("Invio mail protocolo a Destinatari", "Sono presenti documenti alla firma, occorre firmarli prima di poter procedere.");
            return false;
        }

        if ($rowid == '') {
            $anapro_rec = $_POST[$this->nameForm . '_ANAPRO'];
            $rowid = $anapro_rec['ROWID'];
        }

        $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);

        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_40 = $this->proLib->GetAnaent('40');
        $check_p7m = ($anaent_32['ENTDE6'] == 1) ? true : false;
        switch ($this->tipoProt) {
            case "P":
            case "C":
                $allegati = $this->proLibAllegati->checkPresenzaAllegati($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $check_p7m);
                if ($allegati == 0 && !$anaent_40['ENTDE1']) {
                    $messaggio = ($check_p7m == true ) ? "firmati (p7m)." : ".";
                    Out::msgStop("Invio mail protocolo a Destinatari", "L'invio non è possibile in mancaza di allegati " . $messaggio);
                    return false;
                }
                break;
        }

        $anaent_rec = $this->proLib->GetAnaent('27');
        if ($anaent_rec['ENTDE5'] == '') {
            $utelog = App::$utente->getKey('nomeUtente');
            $utenti_rec = $this->accLib->GetUtenti($utelog, 'utelog');
            $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
            if ($richut_rec['RICMAI'] == '') {
                return false;
            }
        }

        $this->checkInvioAvvenuto($anapro_rec);
        if ($this->destMap) {
            $allegati = array();
            foreach ($this->proArriAlle as $allegato) {
                if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0) {
                    // Sovrascrivo la path. Attenzione, potrebbero occupare molto spazio le copie prima dell'invio.
                    $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($allegato['ROWID'], '', true);
                    if (!$CopyPathFile) {
                        Out::msgStop("Invio mail protocolo a Destinatari", "Errore in caricamento allegato: " . $this->proLibAllegati->getErrMessage());
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Anadoc Rowid:  " . $allegato['ROWID'] . ". Errore: " . $this->proLibAllegati->getErrMessage()));
                        return false;
                    }
                    /*
                     * MessaggioOriginale.txt diventa html se inviato via mail per una migliore lettura.
                     */
                    if ($allegato['DOCNAME'] == 'MessaggioOriginale.txt') {
                        $allegato['DOCNAME'] = 'MessaggioOriginale.htm';
                        $allegato['FILENAME'] = 'MessaggioOriginale.htm';
                        $allegato['NOMEFILE'] = 'MessaggioOriginale.htm';
                        $allegato['FILEORIG'] = 'MessaggioOriginale.htm';
                        $pathDest = pathinfo($CopyPathFile, PATHINFO_DIRNAME);
                        $baseName = pathinfo($CopyPathFile, PATHINFO_FILENAME) . '.html';
                        $CopyPathFileDest = $pathDest . '/' . $baseName;
                        if (!@copy($CopyPathFile, $CopyPathFileDest)) {
                            Out::msgStop("Invio mail protocolo a Destinatari", "Errore in caricamento allegato htm: $baseName ");
                            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Anadoc Rowid:  " . $allegato['ROWID'] . ". Errore: " . $this->proLibAllegati->getErrMessage()));
                            return false;
                        }
                        $CopyPathFile = $CopyPathFileDest;
                    }

                    /* Controllo Impronta Sha256 del file */
                    $Anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'rowid');
                    if (!$Anadoc_rec) {
                        Out::msgStop("Invio mail protocolo a Destinatari", "Errore in caricamento allegato: Record su ANADOC non trovato.");
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Record su ANADOC non trovato. Anadoc Rowid:  " . $allegato['ROWID'] . ". "));
                        return false;
                    }
                    $sha256 = hash_file('sha256', $CopyPathFile);
                    if ($sha256 != $Anadoc_rec['DOCSHA2']) {
                        Out::msgStop("Invio mail protocolo a Destinatari", "Errore in controllo allegato, Impronta file non corrispondente. " . $allegato['DOCNAME'] . " Non è possibile procedere con l'invio.");
                        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD_FAILED, 'Estremi' => "Errore invio mail. Impronta non corrispondente. Anadoc Rowid:  " . $allegato['ROWID'] . ". Calcolata: " . $sha256 . ' Su DB: ' . $Anadoc_rec['DOCSHA2']));
                        return false;
                    }
                    $allegato['FILEPATH'] = $CopyPathFile;
                    $allegati[] = $allegato;
                }
            }
            $anaent_rec = $this->proLib->GetAnaent('35');
            if ($anaent_rec['ENTDE5'] != '1') {
                if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
                    $anaent_38 = $this->proLib->GetAnaent('38');
                    if ($anapro_rec['PROCODTIPODOC'] &&
                            ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4'] ||
                            $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE2'] ||
                            $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] ||
                            $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'])) {
                        // - Non deve creare la segnatura perchè fatturaPA: EFAA,EFAP,SIDA,SDIP
                        // - ulteriori controlli?
                    } else {
                        $segnatura = $this->proLibAllegati->ScriviFileXML($rowid);
                        if ($segnatura['stato'] == '-1') {
                            Out::msgInfo("Invio mail protocolo a Destinatari", $segnatura['messaggio']);
                            return false;
                        } else if ($segnatura['stato'] == '-2') {
                            Out::msgInfo("Invio mail protocolo a Destinatari", $segnatura['messaggio']);
                        } else {
                            $allegati[] = array('FILEPATH' => $segnatura, 'FILENAME' => 'Segnatura.xml', 'FILEINFO' => 'Segnatura.xml');
                        }
                    }
                }
            }
            if ($this->tipoProt == 'A') {
                $anaent_38 = $this->proLib->GetAnaent('38');
                $anaent_39 = $this->proLib->GetAnaent('39');
                //
                // FATTURA ELETTRONICA IN ARRIVO
                //
                $tipoInfoNotificaFattura = $anaent_39['ENTDE4'];
                if ($anapro_rec['PROCODTIPODOC'] && $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                    $retArr = $this->proLibSdi->generaFileInfoFatturaArrivo($anapro_rec['ROWID'], $tipoInfoNotificaFattura);
                    if ($retArr) {
                        $allegati[] = array('FILEPATH' => $retArr['FILEPATH'], 'FILENAME' => $retArr['FILENAME'], 'FILEINFO' => $retArr['FILENAME']);
                    }
                }
            }
            // Qui controllo parametro, controllo tipoProt = A e tipo fattura elettronica
            // Per creazione file csv per la fattura elettronica da inviare.
            $tipoProtoc = "PROTOCOLLO IN ARRIVO";
            if ($this->tipoProt == 'P') {
                $tipoProtoc = "PROTOCOLLO IN PARTENZA";
            } else if ($this->tipoProt == 'C') {
                $tipoProtoc = "DOC.FORMALE";
            }

            /*
             * Preparo Oggetti Mail
             */
            $OggMail = $CorpoMail = '';
            $ElementiMail = $this->proLibMail->GetElementiTemplateMail($anapro_rec, 2, true);
            $OggMail = $ElementiMail['OGGETTOMAIL'];
            $CorpoMail = $ElementiMail['BODYMAIL'];
            if (!$OggMail) {
                $OggMail = "$tipoProtoc - " . $anapro_rec['PROSEG'];
            }
            if (!$CorpoMail) {
                $CorpoMail = $anaogg_rec['OGGOGG'];
            }
            /* Valori Mail */
            $valori = array(
                'Destinatari' => $this->destMap,
                'Oggetto' => $OggMail,
                'Corpo' => $CorpoMail
            );

            $DaMail = $this->proLib->GetElencoDaMail('send', $anapro_rec['PROUOF']);
            $model = 'utiGestMail';
            $_POST = array();
            $_POST['tipo'] = 'protocollo';
            $_POST['valori'] = $valori;
            $_POST['allegati'] = $allegati;
            $_POST['returnModel'] = $this->nameForm;
            $_POST['returnEvent'] = 'returnMail';
            $_POST['ElencoDaMail'] = $DaMail;
            $_POST['returnEventOnClose'] = 'true';
            $_POST['event'] = 'openform';
            itaLib::openForm($model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
            return true;
        } else {
            Out::msgInfo("Attenzione.", "Non esistono indirizzi PEC/Email a cui inviare la notifica.");
        }
        return false;
    }

    private function inviaMailMittenti($rowid = '') {
        if ($rowid == '') {
            $rowid = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
        }
        $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $anaent_rec = $this->proLib->GetAnaent('27');
        if ($anaent_rec['ENTDE5'] == '') {
            $utelog = App::$utente->getKey('nomeUtente');
            $utenti_rec = $this->accLib->GetUtenti($utelog, 'utelog');
            $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
            if ($richut_rec['RICMAI'] == '') {
                return false;
            }
        }
        $destMap = array();
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
        $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();

        foreach ($this->mittentiAggiuntivi as $i => $mittAgg) {
            $destMap_rec = array();
            if ($mittAgg['PROMAIL'] != '') {
                $destMap_rec['MAIL'] = $mittAgg['PROMAIL'];
                $destMap_rec['TIPO'] = "mittentiAggiuntivi";
                $destMap_rec['indice'] = $i;
                $destMap[] = $destMap_rec;
            }
        }
        $destMap_rec = array();
        if ($anapro_rec['PROMAIL'] != '') {
            $destMap_rec['MAIL'] = $anapro_rec['PROMAIL'];
            $destMap_rec['TIPO'] = "proArri-A";
            $destMap_rec['indice'] = "";
            $destMap[] = $destMap_rec;
        } else {
            $anamed_rec = $this->proLib->GetAnamed($anapro_rec['PROCON'], 'codice');
            if ($anamed_rec['MEDEMA'] != '') {
                $destMap_rec['MAIL'] = $anamed_rec['MEDEMA'];
                $destMap_rec['TIPO'] = "proArri-A";
                $destMap_rec['indice'] = "";
                $destMap[] = $destMap_rec;
            }
        }
        if ($destMap) {
            $Corpo = proLibRicevute::getCorpoRicezione($anapro_rec);
            $OggettoM = proLibRicevute::getOggettoRicezione($anapro_rec);
            $valori = array(
                'destMap' => $destMap,
                'Oggetto' => $OggettoM,
                'Corpo' => htmlspecialchars_decode($Corpo)
            );
            if ($valori['Corpo'] == '') {
                $valori['Corpo'] = $anaogg_rec['OGGOGG'];
            }
            /*
             * Lettura dati da SubForm Trasmissioni
             */
            $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
            $proArriDest = $proSubTrasmissioni->getProArriDest();

            $result = $this->proLibMail->servizioInvioMail($this, $valori, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $this->mittentiAggiuntivi, $proArriDest, $this->proAltriDestinatari);
            if (!$result) {
                Out::msgStop("Attenzione!", $this->proLibMail->getErrMessage());
                return false;
            }
            Out::msgInfo("Notifica Inviata.", "Sono state inviate n° " . count($destMap) . " notifiche.");
            $this->Modifica($rowid);

            return true;
        } else {
            Out::msgInfo("Attenzione.", "Non esistono indirizzi PEC/Email a cui inviare la notifica.");
        }
        return false;
    }

    private function assegnaDatiXml($file, $anamed_decoded = false) {
        $fileXmlAppoggio = $this->leggiXml($file);
        if ($fileXmlAppoggio) {
            $fileXml = $fileXmlAppoggio['Segnatura'];
            $numProtocMit = $fileXml['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'];
            if ($fileXml['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] != '') {
                $dataProtocMit = date('Ymd', strtotime($fileXml['Intestazione']['Identificatore']['DataRegistrazione']['@textNode']));
            }
            $denominazione = $fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'];
            $indirizzo = $fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'];
            if ($fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Comune']['@textNode'] != '') {
                $citta = $fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Comune']['@textNode'];
            }
            if ($fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['CAP']['@textNode'] != '') {
                $cap = $fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['CAP']['@textNode'];
            }
            if ($fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Provincia']['@textNode'] != '') {
                $provincia = $fileXml['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Provincia']['@textNode'];
            }
            $oggetto = $fileXml['Intestazione']['Oggetto']['@textNode'];
            $this->inviaConfermaMail = $fileXml['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'];
            Out::valore($this->nameForm . '_ANAPRO[PRONPA]', $numProtocMit);
            Out::valore($this->nameForm . '_ANAPRO[PRODAS]', $dataProtocMit);
            if ($anamed_decoded === false) {
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $denominazione);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $indirizzo);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $citta);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $provincia);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $cap);
            }
            Out::valore($this->nameForm . '_Oggetto', $oggetto);
        }
    }

    private function leggiXml($file) {
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($file);
        $arrayXml = $xmlObj->getArray();
        return $arrayXml;
    }

    private function setClasseMail() {
        if ($this->fileDaPEC['TYPE'] == 'MAILBOX') {
            include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
            $emlDbMailBox = new emlDbMailBox();
            $risultato = $emlDbMailBox->updateClassForRowId($this->fileDaPEC['ROWID'], '@PROTOCOLLATO@');
            if ($risultato === false) {
                App::log($emlDbMailBox->getLastMessage());
            }
        } else if ($this->fileDaPEC['TYPE'] == 'LOCALE') {
            if (is_file($this->fileDaPEC['FILENAME'])) {
                if (!@unlink($this->fileDaPEC['FILENAME'])) {
                    Out::msgStop("Nuovo Protocollo", "File:" . $this->fileDaPEC['FILENAME'] . " non Eliminato");
                }
            }
        }
        return $risultato;
    }

    private function inserisciPromail($pronum, $propar) {
        if ($this->fileDaPEC['TYPE'] == 'MAILBOX') {
            $emlLib = new emlLib();
            $mail_rec = $emlLib->getMailArchivio($this->fileDaPEC['ROWID'], 'rowid');
            if ($mail_rec) {
                $promail_rec = array(
                    'PRONUM' => $pronum,
                    'PROPAR' => $propar,
                    'IDMAIL' => $mail_rec['IDMAIL'],
                    'SENDREC' => $mail_rec['SENDREC']
                );
                $insert_Info = 'Inserimento: ' . $promail_rec['PRONUM'] . ' ' . $promail_rec['IDMAIL'];
                $this->insertRecord($this->PROT_DB, 'PROMAIL', $promail_rec, $insert_Info);
            } else {
                return false;
            }
        }
        return true;
    }

    private function returnGestioneMail($pronum, $datipost) {
        $model = 'proGestMail';
        //$datipost = $datipost;
        $datipost['event'] = 'openform';
        $datipost['daProtocollo'] = true;
        $this->apriForm($model, $datipost);
        Out::msgInfo("Protocollazione avvenuta con successo.", "L'email è stata protocollata con il numero " . (int) substr($pronum, 4) . "/" . substr($pronum, 0, 4));
    }

    private function apriForm($model, $datipost) {
        $_POST = $datipost;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function checkEsistenzaProt($pronpa, $prodas, $pronom) {
        if ($this->tipoProt == 'A' && $pronpa != '' && $prodas != '' && $pronom != '') {
//            $PronomParole = explode(' ', $pronom);
            //$sql = "SELECT * FROM ANAPRO WHERE PRONPA = '$pronpa' "; //AND PRODAS=" . $prodas . "";// Basta solo verificare il Prot. Spesso la data può essere vuota.
            $anno = date('Y');
            $sql = "SELECT * FROM ANAPRO WHERE PRONPA = '$pronpa' AND PRONUM LIKE '" . $anno . "%' "; //AND PRODAS=" . $prodas . "";// Basta solo verificare il Prot. Spesso la data può essere vuota.
//Controllo su sql
//            $sql .= " AND ( ";
//            foreach ($PronomParole as $Parola) {
//                $value = addslashes(strtoupper($Parola));
//                $sql .= "PRONOM LIKE '% $value%' OR PRONOM LIKE '%$value %' OR PRONOM LIKE '% $value %' OR ";
//            }
//            $sql = substr($sql, 0, -3) . " )";
//            Out::msgInfo('sql',$sql);
//            $anapro_tab = $this->proLib->getGenericTab($sql);
//            if ($anapro_tab) {
//                $pronum = '';
//                $dicitura = "il Protocollo";
//                foreach ($anapro_tab as $anapro_rec) {
//                    if ($pronum != '') {
//                        $pronum .= ", ";
//                        $dicitura = "i Protocolli";
//                    }
//                    $pronum .= (int) substr($anapro_rec['PRONUM'], 4) . "/" . substr($anapro_rec['PRONUM'], 0, 4);
//                }
//                Out::msgStop("Attenzione!", "E' già presente un altro protocollo con Prot.Mit.: $pronpa del " . date('d/m/Y', strtotime($prodas)) .
//                        "<br>Controllare che non sia un duplicato prima di salvare.
//                            <br>Verificare $dicitura n°: $pronum");
//            }
//        }
            // Controllo su array
            $anapro_tab = $this->proLib->getGenericTab($sql);
            $PronomParole = explode(' ', strtoupper($pronom));
            if ($anapro_tab) {
                $pronum = '';
                $dicitura = "il Protocollo";
                foreach ($anapro_tab as $anapro_rec) {
                    $ParolaTrovata = false;
                    $AnaproPronomParole = explode(' ', strtoupper($anapro_rec['PRONOM']));
                    foreach ($PronomParole as $PronomParola) {
                        if (array_search($PronomParola, $AnaproPronomParole) !== false) {
                            $ParolaTrovata = true;
                            break;
                        }
                    }
                    if ($ParolaTrovata) {
                        if ($pronum != '') {
                            $pronum .= ", ";
                            $dicitura = "i Protocolli";
                        }
                        $pronum .= (int) substr($anapro_rec['PRONUM'], 4) . "/" . substr($anapro_rec['PRONUM'], 0, 4);
                    }
                }
                if ($pronum) {
                    Out::msgStop("Attenzione!", "E' già presente un altro protocollo con Prot.Mit.: $pronpa del " . date('d/m/Y', strtotime($prodas)) .
                            "<br>Controllare che non sia un duplicato prima di salvare.
                            <br>Verificare $dicitura n°: $pronum");
                }
            }
        }
    }

    private function caricaGrigliaAllegati() {
        foreach ($this->proArriAlle as $key => $allegato) {
            if ($allegato['DOCNAME'] == '') {
                $this->proArriAlle[$key]['DOCNAME'] = $allegato['FILENAME'];
            }
        }
        $daVisualizzare = $this->proArriAlle;
        foreach ($daVisualizzare as $key => $value) {
            if ($value['DOCSERVIZIO']) {
                unset($daVisualizzare[$key]);
            }
        }
        TableView::clearGrid($this->gridAllegati);
        $this->CaricaGriglia($this->gridAllegati, $daVisualizzare);

        $anaent_48 = $this->proLib->GetAnaent('48');
        if ($anaent_48['ENTDE3'] == 1) {
            foreach ($daVisualizzare as $key => $allegato) {
                TableView::setCellValue($this->gridAllegati, $key, 'FILEINFO', "", 'not-editable-cell', '', 'false');
            }
        }
    }

    public function setRiscontro($anapro_rec, $matieniAllegati = false) {
        $pronum_ante = $anapro_rec['PRONUM'];
        $riscontro_tab = $this->proLib->checkRiscontro(substr($pronum_ante, 0, 4), substr($pronum_ante, 4), $anapro_rec['PROPAR']);
        if ($riscontro_tab) {
            $numeri = '';
            foreach ($riscontro_tab as $riscontro_rec) {
                if ($numeri != '') {
                    $numeri .= '<br>';
                }
                $numeri .= (int) substr($riscontro_rec['PRONUM'], 4) . "/" . substr($riscontro_rec['PRONUM'], 0, 4);
            }
            Out::msgInfo("Attenzione!", "Al Protocollo num. " . (int) substr($pronum_ante, 4) . "/" . substr($pronum_ante, 0, 4)
                    . "<br>è stato già registrato un riscontro con Protocollo num. "
                    . $numeri);
        }
        unset($anapro_rec['ROWID']);
        unset($anapro_rec['PRODAR']);
        unset($anapro_rec['PROORA']);
        unset($anapro_rec['PROSEG']);
        unset($anapro_rec['PRODAA']);
        unset($anapro_rec['PRORISERVA']);
        unset($anapro_rec['PROINCOGG']);
        unset($anapro_rec['PRORDA']);
        unset($anapro_rec['PROROR']);
        unset($anapro_rec['PROSECURE']);
        unset($anapro_rec['PROUOF']);
        unset($anapro_rec['PROEME']);
        unset($anapro_rec['PRODATEME']);
        unset($anapro_rec['PROORAEME']);
        unset($anapro_rec['PROSEGEME']);
        unset($anapro_rec['PROPARPRE']);
        unset($anapro_rec['PROFASKEY']);
        unset($anapro_rec['PRONAL']);
        // Tipo spedizione non è da copiare mai
        unset($anapro_rec['PROTSP']);

        /* Campi di controllo per mail prot in arrivo. */
        $TipoSped = $this->formData[$this->nameForm . '_ANAPRO']['PROTSP'];
        $MailInArrivo = $this->CheckProtMailInArrivo($TipoSped);

        if ($this->tipoProt == 'C' || $MailInArrivo == true) {
            unset($anapro_rec['PRONOM']);
            unset($anapro_rec['PROCON']);
            unset($anapro_rec['PROIND']);
            unset($anapro_rec['PROCIT']);
            unset($anapro_rec['PROPRO']);
            unset($anapro_rec['PROCAP']);
            unset($anapro_rec['PROMAIL']);
            unset($anapro_rec['PROFIS']);
            unset($anapro_rec['PRONAZ']);
            unset($anapro_rec['PROTSP']);
        }
        Out::hide($this->nameForm . '_VersioneTitolario');
        //@ SE LA VERSIONE CORRENTE NON CORRISPONDE CON  IL RISCONTRO DA CUI COPIARE
        /* Controllo su versione corrente e del riscontro */
        $VersioneCorrente = $this->proLib->GetTitolarioCorrente();
        if ($anapro_rec['VERSIONE_T'] != $VersioneCorrente) {
            $MessaggioAvviso .= 'Il protocollo collegato ha una versione del titolario differente da quella attuale.<br>';
            $Titolario = $this->proLibTitolario->getTitolarioCorrispondenteSucc($anapro_rec['PROCCF'], $anapro_rec['VERSIONE_T'], $VersioneCorrente);
            $anapro_rec['PROCAT'] = $Titolario['CATEGORIA'];
            $anapro_rec['PROCCA'] = $Titolario['CLASSE'];
            $anapro_rec['PROCCF'] = $Titolario['SOTTOCLASSE'];
            if ($Titolario['CATEGORIA']) {
                $MessaggioAvviso .= '<br><b>Il titolario corrispondente è stato automaticamente predisposto:</b><br>';
                $MessaggioAvviso .= $anapro_rec['PROCCF'] . ': ' . $Titolario['DESCRIZIONE'] . "<br/><br/>";
                $MessaggioAvviso .= "<b>Verifica il titolario predisposto.</b>";
            } else {
                $MessaggioAvviso .= "<b>Il titolario non può essere predisposto dal protocollo di origine.<br/><br/>Inserisci il titolario.</b>";
            }
            Out::msgInfo("Titolario", $MessaggioAvviso);
        }
        // Imposto il titolario corrente.
        $anapro_rec['VERSIONE_T'] = $VersioneCorrente;
        /* Pulizia di proArriAlle solo se non si sta decodificato un protocollo collegato.
         * Può creare problemi se sono presenti allegati al protocollo e si effettua un riscontro: gli allegati non verrebbero caricati.
         */
        if (!$matieniAllegati) {
            $this->proArriAlle = array();
        }
        $this->datiFascicolazine = array();
        $this->Decod($anapro_rec);
        /*
         * Decod della SubForm Mitt/Dest
         */
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->Decod($anapro_rec);
        /*
         * Decod della SubForm Mitt/Dest
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proSubTrasmissioni->setTipoProt($this->tipoProt);
        $anaent_54 = $this->proLib->GetAnaent('54');
        $proArriUff = array();
        // Se non è attivo parametro per assegnazioni vuote in riscontro.
        if (!$anaent_54['ENTDE6']) {
            $proSubTrasmissioni->Decod($anapro_rec, true);
            $proArriUff = $proSubTrasmissioni->getProArriUff();
        }

        /*
         *  Se Arrivo Mail oggetto lo ripristino:
         */
        if ($MailInArrivo == true) {
            $OggettoProt = $this->formData[$this->nameForm . '_Oggetto'];
            Out::valore($this->nameForm . '_Oggetto', $OggettoProt);
        }

        if ($anaent_54['ENTDE5']) {
            Out::valore($this->nameForm . '_Oggetto', '');
        }

        $this->setMittPartenzaDefault();
        Out::valore($this->nameForm . '_Propre1', substr($pronum_ante, 4));
        Out::valore($this->nameForm . '_Propre2', substr($pronum_ante, 0, 4));
        Out::valore($this->nameForm . '_ANAPRO[PROPARPRE]', $anapro_rec['PROPAR']);
        //Out::valore($this->nameForm . '_ANAPRO[PROUTE]', App::$utente->getKey('nomeUtente'));
        $anaent_3 = $this->proLib->GetAnaent('3');
        if ($anaent_3['ENTDE1'] == 1) {
            $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
            $proSubMittDest->setStatoMittenti($this->mittentiAggiuntivi);
        }

        // Pulizia tabella fascicoli
        $this->ElencoFascicoli = array();
        TableView::clearGrid($this->gridFascicoli);

        if ($this->tipoProt == 'A') {
            foreach ($proArriUff as $ufficio) {
                $proSubTrasmissioni->confermaScarico($ufficio['UFFCOD']);
            }
        }
        // Chiamo funzione di riscontro
        $proSubTrasmissioni->setRiscontro();
        Out::hide($this->nameForm . '_Trasmissioni');

        $this->CtrAllegatiObbligatori();
    }

    private function setMittPartenzaDefault() {
        if ($this->tipoProt == "A") {
            return;
        }
        $anaent_29 = $this->proLib->GetAnaent('29');
        if ($anaent_29['ENTDE5'] != 1) {
            $profilo = proSoggetto::getProfileFromIdUtente();
            $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
            if ($this->tipoProt == "P") {
                Out::valore($this->nameForm . '_DESCOD', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_DESNOM', $anamed_rec['MEDNOM']);
                // Si potrebbe migliorare! @TODO - Alle.
                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='" . $anamed_rec['MEDCOD'] . "' AND ANAUFF.UFFANN=0 ORDER BY UFFDES.UFFFI1__3 DESC ");
                $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
//            } else if ( $this->tipoProt  == "C") {
//                Out::valore($this->nameForm . '_ANAPRO[PROCON]', $anamed_rec['MEDCOD']);
//                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $anamed_rec['MEDNOM']);
            } else if ($this->tipoProt == "C") {
                Out::valore($this->nameForm . '_DESCOD', '');
                Out::valore($this->nameForm . '_DESNOM', '');
                Out::valore($this->nameForm . '_DESCUF', '');
                Out::valore($this->nameForm . '_UFFNOM', '');
            }
        }
        // Nuovo parametro per firmatario:
        // Inserito qui, perchè potrebbe essere richiesto per C e P.
        // 
        $anaent_52 = $this->proLib->GetAnaent('52');
        if ($anaent_52['ENTDE1'] && $anaent_52['ENTDE2'] && $this->tipoProt == "P") {
            $anamed_rec = $this->proLib->GetAnamed($anaent_52['ENTDE1'], 'codice');
            Out::valore($this->nameForm . '_DESCOD', $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . '_DESNOM', $anamed_rec['MEDNOM']);
            $anauff_rec = $this->proLib->GetAnauff($anaent_52['ENTDE2'], 'codice');
            Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
            Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
        }
        if ($anamed_rec && $anauff_rec) {
            $this->CheckDelegaFirmatario($anamed_rec['MEDCOD'], $anauff_rec['UFFCOD']);
        }
    }

    /**
     * 
     * Popola i campi del titolario se ce un solo titolario abilitato
     *
     * @param type $uffcod codice ufficio di lavoro
     * @return type
     */
    private function bloccoTitolario($uffcod) {
        if ($uffcod) {
            if ($this->proLibTitolario->CheckUtenteBloccoTitolario($this->tipoProt)) {
                $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
                $ufftit_tab = $this->proLib->getUfftit($uffcod, 'uffcod', $versione_t);
                if (count($ufftit_tab) == 1) {
                    $this->DecodAnacat('', $ufftit_tab[0]['CATCOD']);
                    $this->DecodAnacla('', $ufftit_tab[0]['CATCOD'] . $ufftit_tab[0]['CLACOD']);
                    $this->DecodAnafas('', $ufftit_tab[0]['CATCOD'] . $ufftit_tab[0]['CLACOD'] . $ufftit_tab[0]['FASCOD'], 'fasccf');
                    return;
                }
                Out::valore($this->nameForm . '_ANAPRO[PROCAT]', '');
                Out::valore($this->nameForm . '_Clacod', '');
                Out::valore($this->nameForm . '_Fascod', '');
                Out::valore($this->nameForm . '_TitolarioDecod', '');
            }
        }
    }

    private function controllaTitolario($uffcod, $versione_t, $catcod, $clacod = '', $fascod = '') {
        $sql = "SELECT * FROM UFFTIT WHERE UFFCOD='$uffcod' AND VERSIONE_T=$versione_t AND CATCOD='$catcod'";
        if ($clacod) {
            $sql .= " AND CLACOD='$clacod'";
        }
        if ($fascod) {
            $sql .= " AND FASCOD='$fascod'";
        }
        $ufftit_test = $this->proLib->getGenericTab($sql, false);
        if ($ufftit_test) {
            return $ufftit_test;
        }
        return false;
    }

    private function checkCatFiltrato($versione_t, $codice) {
        $titolario_rec = $this->controllaTitolario($this->prouof, $versione_t, $codice);
        Out::valore($this->nameForm . '_ANAPRO[PROCAT]', $codice);
        Out::valore($this->nameForm . '_TitolarioDecod', '');
        if ($titolario_rec) {
            $this->DecodAnacat($versione_t, $codice);
        }
//        foreach ($titolario_tab as $titolario_rec) {
//            if ($titolario_rec['CLACOD'] == '' && $titolario_rec['FASCOD'] == '') {
//                $this->DecodAnacat($codice);
//            }
//        }
    }

    private function checkClaFiltrato($versione_t, $codice1, $codice2) {
        $titolario_rec = $this->controllaTitolario($this->prouof, $versione_t, $codice1, $codice2);
        Out::valore($this->nameForm . '_Clacod', $codice2);
        Out::valore($this->nameForm . '_TitolarioDecod', '');
        if ($titolario_rec) {
            $this->DecodAnacla($versione_t, $codice1 . $codice2);
        }
//        foreach ($titolario_tab as $titolario_rec) {
//            if ($titolario_rec['FASCOD'] == '') {
//                $this->DecodAnacla($codice1 . $codice2);
//            }
//        }
    }

    private function checkObbligoAllegati() {
        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_40 = $this->proLib->GetAnaent('40');

        Out::hide($this->nameForm . '_Mail');
        if ($this->annullato === true) {
            Out::attributo($this->nameForm . '_protMotivo', 'style', '1');
            $anaprosave_motivo = $this->proLib->getGenericTab("SELECT SAVEMOTIVAZIONE FROM ANAPROSAVE WHERE PRONUM=" . $this->anapro_record['PRONUM'] . " AND PROPAR='" . $this->tipoProt . "' ORDER BY PRORDA DESC, PROROR DESC", false);
            Out::valore($this->nameForm . '_protMotivo', $anaprosave_motivo['SAVEMOTIVAZIONE']);
            Out::show($this->nameForm . '_protMotivo_field');
            if ($anaent_32['ENTDE3'] == 1) {
                Out::show($this->nameForm . '_Riattiva');
            }
            return false;
        }
        if ($anaent_32['ENTDE4'] == 1 && ( $this->tipoProt == 'A' || $this->tipoProt == 'P')) {
            $stato = 'REGISTRATO';
            $anadoc_check = $this->proLibAllegati->checkPresenzaAllegati($this->anapro_record['PRONUM'], $this->tipoProt);

            if (!$anadoc_check) {
                Out::valore($this->nameForm . '_protMotivo', "NON SONO STATI ALLEGATI DOCUMENTI AL PROTOCOLLO!");
                Out::show($this->nameForm . '_protMotivo_field');
                Out::attributo($this->nameForm . '_protMotivo', 'style', '0', 'color:white;background:red;');
                Out::valore($this->nameForm . '_protStato', $stato);
                //Qui controllo per attivare il notifica destinatari
                if ($anaent_40['ENTDE1']) {
                    Out::show($this->nameForm . '_Mail');
                }
                return false;
            }
        }
        Out::attributo($this->nameForm . '_protMotivo', 'style', '1');
        Out::hide($this->nameForm . '_protMotivo_field');

        $conAll = false;
        foreach ($this->proArriAlle as $allegato) {
            if ($allegato['DOCSERVIZIO'] == 0) {
                $conAll = true;
                break;
            }
        }
        $msgall = "";
        if ($conAll) {
            $msgall = "CON ALLEGATI";
        }
        Out::valore($this->nameForm . '_protStato', $stato . " " . $msgall);

        $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($this->anapro_record['PRONUM'], $this->tipoProt, true, " AND FIRDATA=''");
        if (!$docfirma_tab) {
            Out::show($this->nameForm . '_Mail');
        } else {
            Out::hide($this->nameForm . '_Mail');
        }
        // Qui controllo per attivare il notifica destinatari
        if ($anaent_40['ENTDE1']) {
            Out::show($this->nameForm . '_Mail');
        }
        return true;
    }

    private function checkPermsAnamed() {
        $menLib = new menLib();
        $gruppi = $menLib->getGruppi(App::$utente->getKey('idUtente'));
        $fl1 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGVIS", $menLib->defaultVis);
        $fl2 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGACC", $menLib->defaultAcc);
        $fl3 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGEDT", $menLib->defaultMod);
        $fl4 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGINS", $menLib->defaultIns);
        if ($fl1 && $fl2 && $fl3 && $fl4) {
            return true;
        } else {
            return false;
        }
    }

    private function checkPermsAnaogg() {
        $menLib = new menLib();
        $gruppi = $menLib->getGruppi(App::$utente->getKey('idUtente'));
        $fl1 = $menLib->privilegiModel('proAnaogg', $gruppi, "PER_FLAGVIS", $menLib->defaultVis);
        $fl2 = $menLib->privilegiModel('proAnaogg', $gruppi, "PER_FLAGACC", $menLib->defaultAcc);
        $fl3 = $menLib->privilegiModel('proAnaogg', $gruppi, "PER_FLAGEDT", $menLib->defaultMod);
        $fl4 = $menLib->privilegiModel('proAnaogg', $gruppi, "PER_FLAGINS", $menLib->defaultIns);
        if ($fl1 && $fl2 && $fl3 && $fl4) {
            return true;
        } else {
            return false;
        }
    }

    private function abilitaDatiProtEmergenza($anapro_rec) {
        if ($anapro_rec['PROEME']) {
            if (App::$utente->getKey('nomeUtente') == $anapro_rec['PROUTE']) {
                Out::attributo($this->nameForm . '_ANAPRO[PROEME]', "readonly", '1');
                Out::attributo($this->nameForm . '_ANAPRO[PRODATEME]', "readonly", '1');
                Out::attributo($this->nameForm . '_ANAPRO[PROORAEME]', "readonly", '1');
                Out::attributo($this->nameForm . '_ANAPRO[PROSEGEME]', "readonly", '1');
                Out::addClass($this->nameForm . '_ANAPRO[PROEME]', "required");
                Out::addClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
                Out::addClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
                Out::addClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
            } else {
                Out::attributo($this->nameForm . '_ANAPRO[PROEME]', "readonly", '0');
                Out::attributo($this->nameForm . '_ANAPRO[PRODATEME]', "readonly", '0');
                Out::attributo($this->nameForm . '_ANAPRO[PROORAEME]', "readonly", '0');
                Out::attributo($this->nameForm . '_ANAPRO[PROSEGEME]', "readonly", '0');
                Out::delClass($this->nameForm . '_ANAPRO[PROEME]', "required");
                Out::delClass($this->nameForm . '_ANAPRO[PRODATEME]', "required");
                Out::delClass($this->nameForm . '_ANAPRO[PROORAEME]', "required");
                Out::delClass($this->nameForm . '_ANAPRO[PROSEGEME]', "required");
            }
            Out::show($this->nameForm . '_divArrEme');
        } else {
            Out::attributo($this->nameForm . '_ANAPRO[PROEME]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PRODATEME]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROORAEME]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROSEGEME]', "readonly", '1');
        }
    }

    private function checkInvioAvvenuto($anapro_rec) {
        $nonInviata = 0;
        $inviata = 0;
        $inviataConSuccesso = 0;
        $this->destMap = array();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $SeleMan = false;
        if ($anaent_26['ENTDE6']) {
            $SeleMan = true;
        }
        $SelezioneDinamica = '';
        $anaent_48 = $this->proLib->GetAnaent('48');
        if ($anaent_48['ENTVAL']) {
            $SelezioneDinamica = true;
        }
        /*
         * Lettura dati da SubForm Trasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();

        /*
         * DESTINATARI INTERNI SEMPRE
         */
        foreach ($proArriDest as $i => $destinatario) {
            if ($destinatario['DESMAIL'] != '' && $destinatario['ROWID'] != '') {
                if ($destinatario['DESIDMAIL']) {
                    $inviata++;
                    $retRic = $this->proLib->checkMailRic($destinatario['DESIDMAIL']);
                    if ($retRic['ACCETTAZIONE'] && $retRic['CONSEGNA']) {
                        $inviataConSuccesso++;
                    }
                } else {
                    $nonInviata++;
                }
                $destMap_rec = array();
                $destMap_rec['MAIL'] = $destinatario['DESMAIL'];
                $destMap_rec['NOME'] = $destinatario['DESNOM'];
                $destMap_rec['TIPO'] = "proArridest";
                if ($SelezioneDinamica) {
                    if ($destinatario['DESINV'] == 1) {
                        $destMap_rec['SELEMAN'] = $SeleMan;
                    } else {
                        // Se non è selezionato lo salto come destinatario
                        continue;
                    }
                } else {
                    $destMap_rec['SELEMAN'] = $SeleMan;
                }
                $destMap_rec['indice'] = $i;
                $this->destMap[] = $destMap_rec;
            }
        }
        /*
         * Altri DESTINATARI E DESTINATARIO SOLO SE PROTOCOLLO P E C
         */
        if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
            $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
            $this->proAltriDestinatari = $proSubMittDest->getProAltriDestinatari();
            foreach ($this->proAltriDestinatari as $i => $destinatario) {
                if ($destinatario['DESMAIL'] != '' && $destinatario['ROWID'] != '') {
                    if ($destinatario['DESIDMAIL']) {
                        $inviata++;
                        $retRic = $this->proLib->checkMailRic($destinatario['DESIDMAIL']);
                        if ($retRic['ACCETTAZIONE'] && $retRic['CONSEGNA']) {
                            $inviataConSuccesso++;
                        }
                    } else {
                        $nonInviata++;
                        $destMap_rec = array();
                        $destMap_rec['MAIL'] = $destinatario['DESMAIL'];
                        $destMap_rec['NOME'] = $destinatario['DESNOM'];
                        $destMap_rec['TIPO'] = "proAltriDestinatari";
                        $destMap_rec['indice'] = $i;
                        $this->destMap[] = $destMap_rec;
                    }
                }
            }

            if ($anapro_rec['PROMAIL'] != '') {
                if ($anapro_rec['PROIDMAILDEST']) {
                    $inviata++;
                    $retRic = $this->proLib->checkMailRic($anapro_rec['PROIDMAILDEST']);
                    if ($retRic['ACCETTAZIONE'] && $retRic['CONSEGNA']) {
                        $inviataConSuccesso++;
                    }
                } else {
                    $nonInviata++;
                    $destMap_rec = array();
                    $destMap_rec['MAIL'] = $anapro_rec['PROMAIL'];
                    $destMap_rec['NOME'] = $anapro_rec['PRONOM'];
                    $destMap_rec['TIPO'] = "proArri-P";
                    $destMap_rec['indice'] = "";
                    $this->destMap[] = $destMap_rec;
                }
            }
        }
        $risultato = array('nonInviate' => $nonInviata, 'inviate' => $inviata, 'inviataConSuccesso' => $inviataConSuccesso);
        return $risultato;
    }

    private function bloccareSeInviato($anapro_rec) {
        if ($this->tipoProt == 'P') {
            if ($anapro_rec['PROIDMAILDEST'] != '') {
                return true;
            }
            foreach ($this->proAltriDestinatari as $destinatario) {
                if ($destinatario['DESIDMAIL']) {
                    return true;
                }
            }
        }
        return false;
    }

    private function bloccaDopoInvio() {
        $this->bloccoDaEmail = true;
        Out::addClass($this->nameForm . '_codiceOggetto', "ita-readonly");
        Out::addClass($this->nameForm . '_Oggetto', "ita-readonly");
        Out::addClass($this->nameForm . '_DESCOD', "ita-readonly");
        Out::addClass($this->nameForm . '_DESNOM', "ita-readonly");
        if (!$this->anapro_record['PROIDMAILDEST']) {
            Out::delClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
            Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '1');
        } else {
            Out::addClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
            Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '0');
        }
        Out::attributo($this->nameForm . '_codiceOggetto', "readonly", '0');
        Out::attributo($this->nameForm . '_Oggetto', "readonly", '0');
        Out::attributo($this->nameForm . '_DESCOD', "readonly", '0');
        Out::attributo($this->nameForm . '_DESNOM', "readonly", '0');
        Out::hide($this->nameForm . '_codiceOggetto_butt');
        Out::hide($this->nameForm . '_DESCOD_butt');
        Out::hide($this->nameForm . '_UFFNOM_butt');
        Out::hide($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::hide($this->nameForm . '_CercaAnagrafe');
        Out::hide($this->nameForm . '_CercaIPA');
        Out::hide($this->nameForm . '_AggiungiMittente');
        Out::hide($this->nameForm . '_AggiungiOggetto');
        Out::hide($this->nameForm . '_FileLocale_uploader');
        Out::hide($this->nameForm . '_Scanner');
        Out::hide($this->nameForm . '_DaP7m');
        Out::hide($this->nameForm . '_DaTestoBase');
        Out::hide($this->nameForm . '_DaFascicolo');
        Out::hide($this->nameForm . '_DaProtCollegati');
        Out::hide($this->nameForm . '_ScannerShared');
    }

    private function sbloccaSeDaInviare() {
        $this->bloccoDaEmail = false;
        Out::delClass($this->nameForm . '_codiceOggetto', "ita-readonly");
        Out::delClass($this->nameForm . '_Oggetto', "ita-readonly");
        Out::delClass($this->nameForm . '_DESCOD', "ita-readonly");
        Out::delClass($this->nameForm . '_DESNOM', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
        Out::attributo($this->nameForm . '_codiceOggetto', "readonly", '1');
        Out::attributo($this->nameForm . '_Oggetto', "readonly", '1');
        Out::attributo($this->nameForm . '_DESCOD', "readonly", '1');
        Out::attributo($this->nameForm . '_DESNOM', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '1');
        Out::show($this->nameForm . '_codiceOggetto_butt');
        Out::show($this->nameForm . '_DESCOD_butt');
        Out::show($this->nameForm . '_UFFNOM_butt');
        Out::show($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::show($this->nameForm . '_CercaAnagrafe');
        Out::show($this->nameForm . '_CercaIPA');

        if ($this->checkPermsAnamed()) {
            Out::show($this->nameForm . '_AggiungiMittente');
        } else {
            Out::hide($this->nameForm . '_AggiungiMittente');
        }

        Out::show($this->nameForm . '_AggiungiOggetto');
        Out::show($this->nameForm . '_FileLocale_uploader');
        Out::show($this->nameForm . '_Scanner');
        $anaent_57 = $this->proLib->GetAnaent('57');
        if ($anaent_57['ENTDE1']) {
            Out::show($this->nameForm . '_ScannerShared');
        }
        Out::show($this->nameForm . '_DaFascicolo');
        Out::show($this->nameForm . '_DaProtCollegati');
        $anaent_47 = $this->proLib->GetAnaent('47');
        if ($anaent_47['ENTDE4']) {
            Out::show($this->nameForm . '_DaP7m');
        }
        if ($this->tipoProt != 'A' && $anaent_47['ENTDE5']) {
            Out::show($this->nameForm . '_DaTestoBase');
        }
        return $this->bloccoDaEmail;
    }

    private function decodNotifiche($anapro_rec) {
        $this->noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $anapro_rec['PRONUM'], "PROPAR" => $anapro_rec['PROPAR']));
        $this->caricaNote();
    }

    private function caricaNote() {
        $datiGrigliaNote = array();
        foreach ($this->noteManager->getNote() as $key => $nota) {
            $data = date("d/m/Y", strtotime($nota['DATAINS']));
            $datiGrigliaNote[$key]['NOTE'] = '<div>' . $data . " - " . $nota['ORAINS'] . " da " . $nota['UTELOG'] . '</div>';
            $datiGrigliaNote[$key]['OGGETTO'] = $nota['OGGETTO'];
            $datiGrigliaNote[$key]['TESTO'] = $nota['TESTO'];
        }
        $this->caricaGriglia($this->gridNote, $datiGrigliaNote);
    }

    private function inserisciNotifica($oggetto, $uteins, $tipoNotifica, $pronum) {
//        try {
//            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
//        } catch (Exception $e) {
//            Out::msgStop("Errore", $e->getMessage());
//        }
        if ($this->tipoProt == 'C') {
            $tipoP = "DOCUMENTO FORMALE";
        } else {
            $tipoP = "PROTOCOLLO";
        }
//        $env_notifiche = array();
//        $env_notifiche['OGGETTO'] = "$tipoNotifica UNA NOTA AL $tipoP NUM. " . (int) substr($pronum, 4, 10) . " / " . substr($pronum, 0, 4);
//        $env_notifiche['TESTO'] = $oggetto;
//        $env_notifiche['UTEINS'] = App::$utente->getKey('nomeUtente');
//        $env_notifiche['MODELINS'] = $this->nameForm;
//        $env_notifiche['DATAINS'] = date("Ymd");
//        $env_notifiche['ORAINS'] = date("H:i:s");
//        $env_notifiche['UTEDEST'] = $uteins;
//        $env_notifiche['ACTIONMODEL'] = 'proOpenProtDaNotifica';
//        $env_notifiche['ACTIONPARAM'] = serialize(array('setOpenMode' => array('OpenProtocollo'), 'setOpenRowid' => array($this->anapro_record['ROWID']), 'setTipoOpen' => array('visualizzazione')));
//        $insert_Info = 'Oggetto notifica: ' . $env_notifiche['OGGETTO'] . " " . $env_notifiche['UTEDEST'];
//
//        $this->insertRecord($ITALWEB_DB, 'ENV_NOTIFICHE', $env_notifiche, $insert_Info);

        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
        $envLib = new envLib();
        $oggetto_notifica = "$tipoNotifica UNA NOTA AL $tipoP NUM. " . (int) substr($pronum, 4, 10) . " / " . substr($pronum, 0, 4);
        $testo_notifica = $oggetto;
        $dati_extra = array();
        $dati_extra['ACTIONMODEL'] = 'proOpenProtDaNotifica';
        $dati_extra['ACTIONPARAM'] = serialize(array('setOpenMode' => array('OpenProtocollo'), 'setOpenRowid' => array($this->anapro_record['ROWID']), 'setTipoOpen' => array('visualizzazione')));
        $envLib->inserisciNotifica($this->nameform, $oggetto_notifica, $testo_notifica, $uteins, $dati_extra);
        return;
    }

    private function statoFascicolo($anapro_rec) {
        Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '1');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '1');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '1');
        Out::show($this->nameForm . '_ANAPRO[PROCAT]_butt');
        Out::show($this->nameForm . '_Clacod_butt');
        Out::show($this->nameForm . '_Fascod_butt');
        /*
         * Solo Se nuovo protocollo 
         * se è già fascicolato può fascicolare in piu fascicoli.
         */
        if (!$anapro_rec || $anapro_rec['PROFASKEY']) {
            if (!$anapro_rec) {
                Out::attributo($this->nameForm . '_Organn', "readonly", '0');
                Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
                Out::hide($this->nameForm . '_ANAPRO[PROARG]_butt');
                Out::hide($this->nameForm . '_addFascicolo');
            }
            /* Out::hide($this->nameForm . '_AggiungiFascicolo');
             * Non serve più visualizzare il bottone "_GestPratica".
             * Gestione tramite db click su grid fascicoli.
             * Se è fascicolato nel principale, non può cambiare il titolario. 
             */
            if ($anapro_rec['PROFASKEY']) {
                Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '0');
                Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
                Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
                Out::hide($this->nameForm . '_ANAPRO[PROCAT]_butt');
                Out::hide($this->nameForm . '_Clacod_butt');
                Out::hide($this->nameForm . '_Fascod_butt');
//                Out::show($this->nameForm . '_GestPratica');
            }
            return;
        }

        $retIterStato = proSoggetto::getIterStato($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();

        /*
         * Se permessi attivati per la gestione completa del fascicolo e protocollo in gestione può fascicolare 
         */
        $fl_fascicola = false;
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
            $fl_fascicola = true;
        }

        /*
         * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può movimentare
         */
        $fl_movimenta = false;
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
            $fl_movimenta = true;
        }

        /* Se è archivista può sempre fascicolare */
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
            $fl_fascicola = true;
            $fl_movimenta = true;
        }

        if ($fl_movimenta || $fl_fascicola) {
            Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '1');
            Out::attributo($this->nameForm . '_Clacod', "readonly", '1');
            Out::attributo($this->nameForm . '_Fascod', "readonly", '1');
            Out::attributo($this->nameForm . '_Organn', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '1');
            Out::show($this->nameForm . '_ANAPRO[PROARG]_butt');
            Out::show($this->nameForm . '_addFascicolo');
        } else {
            /* Se non può movimentare, non è detto che non possa cambiare il titolario.
             * Se non è permesso, comunque non potrà registrare le modifiche. */
            //Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '0');
            //Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
            //Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
            Out::attributo($this->nameForm . '_Organn', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROARG]', "readonly", '0');
            Out::hide($this->nameForm . '_ANAPRO[PROARG]_butt');
            Out::hide($this->nameForm . '_addFascicolo');
        }

        if ($fl_fascicola) {
//            Out::show($this->nameForm . '_AggiungiFascicolo');
        } else {
            //Out::hide($this->nameForm . '_AggiungiFascicolo');
        }
    }

    public function duplicaDoc($rowid) {

        $anapro_rec = $this->proLib->GetAnapro($rowid, 'rowid');
        $this->rowidAnaproDuplicatoSorgente = $rowid;
        unset($anapro_rec['ROWID']);
        unset($anapro_rec['PRODAR']);
        unset($anapro_rec['PROORA']);
        unset($anapro_rec['PROSEG']);
        unset($anapro_rec['PRORISERVA']);
        unset($anapro_rec['PROINCOGG']);
        unset($anapro_rec['PRORDA']);
        unset($anapro_rec['PROROR']);
        unset($anapro_rec['PROSECURE']);
        unset($anapro_rec['PROUOF']);
        unset($anapro_rec['PRONPA']);
        unset($anapro_rec['PRODAS']);
        unset($anapro_rec['PROEME']);
        unset($anapro_rec['PRODATEME']);
        unset($anapro_rec['PROORAEME']);
        unset($anapro_rec['PROSEGEME']);
        unset($anapro_rec['PROPARPRE']);
        unset($anapro_rec['PROFASKEY']);
        unset($anapro_rec['PRONAL']);
        unset($anapro_rec['PROIDMAIL']);
        $anaent_33 = $this->proLib->GetAnaent('33');
        if ($anaent_33['ENTDE3'] == 1) {
            unset($anapro_rec['PRODAA']);
        }
        Out::hide($this->nameForm . '_VersioneTitolario');
        // SE LA VERSIONE CORRENTE NON CORRISPONDE CON IL DOCUMENTO DA DUPLICARE
        /* Controllo su versione corrente e del duplica */
        $VersioneCorrente = $this->proLib->GetTitolarioCorrente();
        if ($anapro_rec['VERSIONE_T'] != $VersioneCorrente) {
            $MessaggioAvviso .= 'Il protocollo modello ha una versione del titolario differente da quella attuale.<br>';
            $Titolario = $this->proLibTitolario->getTitolarioCorrispondenteSucc($anapro_rec['PROCCF'], $anapro_rec['VERSIONE_T'], $VersioneCorrente);
            $anapro_rec['PROCAT'] = $Titolario['CATEGORIA'];
            $anapro_rec['PROCCA'] = $Titolario['CLASSE'];
            $anapro_rec['PROCCF'] = $Titolario['SOTTOCLASSE'];
            if ($Titolario['CATEGORIA']) {
                $MessaggioAvviso .= '<br><b>Il titolario corrispondente è stato automaticamente predisposto:</b><br>';
                $MessaggioAvviso .= $anapro_rec['PROCCF'] . ': ' . $Titolario['DESCRIZIONE'] . "<br/><br/>";
                $MessaggioAvviso .= "<b>Verifica il titolario predisposto.</b>";
            } else {
                $MessaggioAvviso .= "<b>Il titolario non può essere predisposto dal protocollo di origine.<br/><br/>Inserisci il titolario.</b>";
            }
            Out::msgInfo("Titolario", $MessaggioAvviso);
        }
        // Imposto il titolario corrente.
        $anapro_rec['VERSIONE_T'] = $VersioneCorrente;
        $this->Decod($anapro_rec);
        /*
         * Decod della subform
         */
        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->AzzeraVariabili();
        $proSubMittDest->Decod($anapro_rec);
        /*
         * Decod della SubForm Mitt/Dest
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proSubTrasmissioni->setTipoProt($this->tipoProt);
        $proSubTrasmissioni->Decod($anapro_rec);

        // Dopo la decodifica, pulisco i fascicoli.
        $this->ElencoFascicoli = array();
        TableView::clearGrid($this->gridFascicoli);
        TableView::setLabel($this->gridFascicoli, 'FASCICOLO', 'Fascicoli');
        $anaent_3 = $this->proLib->GetAnaent('3');
        if ($anaent_3['ENTDE1'] == 1) {
            $this->mittentiAggiuntivi = $proSubMittDest->getMittentiAggiuntivi();
            $proSubMittDest->setStatoMittenti($this->mittentiAggiuntivi);
        }
        $proSubTrasmissioni->duplicaDoc();

//        Out::valore($this->nameForm . '_ANAPRO[PROUTE]', App::$utente->getKey('nomeUtente'));
        Out::valore($this->nameForm . '_UTENTEORIGINARIO', '');
        Out::valore($this->nameForm . '_UTENTEULTIMO', '');

        // Copia Allegati di un protocollo:
        if ($this->duplicaAllegati === true) {
            $this->CaricaAllegatiDaProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        }
        $this->CtrAllegatiObbligatori();
    }

    private function cancellaAllegato($rowid_alle) {
        $anadoc_check = $this->proLib->GetAnadoc($this->proArriAlle[$rowid_alle]['ROWID'], 'rowid');
        //Controllo se effettuare la cancellazione Metadati sdi
        if ($this->proLibSdi->ControllaSeProtocolloSdi($this->anapro_record)) {

            $CopyPathFile = $this->proLibAllegati->CopiaDocAllegato($this->proArriAlle[$rowid_alle]['ROWID'], '', true);
            if (!$CopyPathFile) {
                Out::msgStop('Attenzione', "Errore nell'apertura del file Firmato. " . $this->proLibAllegati->getErrMessage());
                return false;
            }
//            $Ret = $this->proLibSdi->CancellaMetadatiSdi($this->anapro_record, $this->proArriAlle[$rowid_alle]['FILEPATH'], $this->proArriAlle[$rowid_alle]['DOCNAME']);
            $Ret = $this->proLibSdi->CancellaMetadatiSdi($this->anapro_record, $CopyPathFile, $this->proArriAlle[$rowid_alle]['DOCNAME']);
            if (!$Ret) {
                Out::msgInfo('Attenzione', 'Errore nella cancellazione dei Metadati SDI.<br>' . $this->proLibSdi->getErrCode());
                return false;
            }
        }

        // @TODO DA RIVEDERE QUESTO MECCANISMO
        $orgconn_rec = $this->proLib->GetOrgConn($anadoc_check['DOCNUM'], 'codice', $anadoc_check['DOCPAR']);
        if ($orgconn_rec) {
            $destinazione = $this->proLib->SetDirectory($orgconn_rec['PRONUMPARENT'], $orgconn_rec['PROPARPARENT']);
            if (!$destinazione) {
                Out::msgStop("Archiviazione File", "Errore creazione cartella di destinazione.");
                return false;
            }

            if (!$this->proLibAllegati->CopiaDocAllegato($anadoc_check['ROWID'], $destinazione . "/" . $anadoc_check['DOCFIL'])) {
//            if (!@copy($this->proArriAlle[$rowid_alle]['FILEPATH'], $destinazione . "/" . $this->proArriAlle[$rowid_alle]['FILENAME'])) {
                Out::msgStop("Archiviazione File", "Errore in salvataggio del file " . $this->proArriAlle[$rowid_alle]['FILENAME'] . " !");
                return false;
            }
            // Non cancellare, da problemi con iL DOCUUID, rimarrebbe una copia nella cartella del protocollo.
//            if (!$this->proLibAllegati->CancellaDocAllegato($this->proArriAlle[$rowid_alle]['ROWID'])) {
//                Out::msgStop("Cancellazione", "Errore in cancellazione file." . $this->proLibAllegati->getErrMessage());
//            }

            $iteKey = $this->proLib->IteKeyGenerator($orgconn_rec['PRONUMPARENT'], '', date('Ymd'), $orgconn_rec['PROPARPARENT']);
            if (!$iteKey) {
                Out::msgStop("Errore", $this->proLib->getErrMessage());
                return false;
            }
            $anadoc_check['DOCKEY'] = $iteKey;
            $anadoc_check['DOCNUM'] = $orgconn_rec['PRONUMPARENT'];
            $anadoc_check['DOCPAR'] = $orgconn_rec['PROPARPARENT'];
            $anadoc_check['DOCMD5'] = md5_file($destinazione . "/" . $anadoc_check['DOCFIL']);
            $anadoc_check['DOCSHA2'] = hash_file('sha256', $destinazione . "/" . $anadoc_check['DOCFIL']);
            $update_Info = "Oggetto: Sposto l'allegato nel fascicolo di provenienza";
            if (!$this->updateRecord($this->PROT_DB, 'ANADOC', $anadoc_check, $update_Info)) {
                return false;
            }
//            if (!@unlink($this->proArriAlle[$rowid_alle]['FILEPATH'])) {
//                Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
//            }
            return true;
        }

//        if (!@unlink($this->proArriAlle[$rowid_alle]['FILEPATH'])) {
//            Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
//            return false;
        if (!$this->proLibAllegati->CancellaDocAllegato($this->proArriAlle[$rowid_alle]['ROWID'])) {
            Out::msgStop("Cancellazione", "Errore in cancellazione file." . $this->proLibAllegati->getErrMessage());
            return false;
        } else {
            if ($this->proArriAlle[$rowid_alle]['ROWID'] != 0) {
                $motivo = "Cancellazione Allegato";
                $this->registraSave($motivo, $this->proArriIndice);
                $delete_Info = 'Oggetto: ' . $this->proArriAlle[$rowid_alle]['FILEPATH'];
                $AnadocAllegato_rec = $this->proLib->getAnadoc($this->proArriAlle[$rowid_alle]['ROWID'], 'rowid');
                if (!$this->deleteRecord($this->PROT_DB, 'ANADOC', $this->proArriAlle[$rowid_alle]['ROWID'], $delete_Info)) {
                    Out::msgStop("Gestione Acquisizioni", "Errore in cancellazione file.");
                    return false;
                }
                /* Nuovo Testo Base */
                if ($this->proArriAlle[$rowid_alle]['DOCROWIDBASE']) {
                    $AnadocXhtm_rec = $this->proLib->GetAnadoc($this->proArriAlle[$rowid_alle]['DOCROWIDBASE'], 'rowid');
                    // Cancello solo se ho trovato l'Anadoc xhtml e se il protocollo dell'anadoc cancellato è lo stesso del padre xhtm trovato
                    if ($AnadocXhtm_rec && $AnadocXhtm_rec['DOCNUM'] == $AnadocAllegato_rec['DOCNUM'] && $AnadocXhtm_rec['DOCPAR'] == $AnadocAllegato_rec['DOCPAR']) {
                        if (!$this->CancellaDocumento($this->proArriAlle[$rowid_alle]['DOCROWIDBASE'], $this->anapro_record)) {
                            return false;
                        }
                    }
                }
            }
            unset($this->proArriAlle[$rowid_alle]);
        }
        return true;
    }

    /* Nuovo Per Testo Base */

    private function CancellaDocumento($rowidAllegato, $Anapro_rec) {
        if ($rowidAllegato) {
            $AnadocBase_rec = $this->proLib->GetAnadoc($rowidAllegato, 'rowid');
//            $protPath = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//            $FileProto = $protPath . "/" . $AnadocBase_rec['DOCFIL'];
//            if (!@unlink($FileProto)) {
//                Out::msgStop("Cancellazioen Doc.", "Errore in cancellazione file.");
//                return false;
//            }
            if (!$this->proLibAllegati->CancellaDocAllegato($AnadocBase_rec['ROWID'])) {
                Out::msgStop("Cancellazioen Doc.", "Errore in cancellazione file." . $this->proLibAllegati->getErrMessage());
                return false;
            }
            $delete_Info = 'Oggetto: ' . $AnadocBase_rec['DOCFIL'];
            if (!$this->deleteRecord($this->PROT_DB, 'ANADOC', $AnadocBase_rec['ROWID'], $delete_Info)) {
                Out::msgStop("Gestione Cancellazione", "Errore in cancellazione file.");
                return false;
            }
        }
        return true;
    }

    private function ricaricaFascicolo($anapro_rec = array()) {
        if (!$anapro_rec) {
            $anapro_rec = $this->anapro_record;
        }
        $pakdoc_rec = $this->proLibPratica->GetPakdoc(array("PRONUM" => $anapro_rec['PRONUM'], "PROPAR" => $anapro_rec['PROPAR']), 'pronum', false);
        if ($pakdoc_rec) {
            Out::broadcastMessage($this->nameForm, "UPDATE_FASCICOLO", array("PASKEY" => $pakdoc_rec['PROPAK'], 'GESKEY' => $anapro_rec['PROFASKEY'], 'PRONUM' => $anapro_rec['PRONUM'], 'PROPAR' => $anapro_rec['PROPAR']));
        }
    }

    private function messaggioBroadcast($classe, $chiave) {
        Out::broadcastMessage($this->nameForm, 'UPDATE_NOTE_' . $classe, array('PRONUM' => $chiave['PRONUM'], 'PROPAR' => $chiave['PROPAR']));
    }

    private function passoSelezionato() {
        $fascicolo_rec = $this->proLib->GetAnaorg($this->varAppoggio['ROWID'], 'rowid');
        $anapro_rec = $this->anapro_record;
        if (!$anapro_rec) {
            Out::msgStop("Attenzione!", "Errore nei riferimenti al protocollo in salvataggio.");
            return;
        }
        if ($anapro_rec['PROFASKEY']) {
            $this->Modifica($anapro_rec['ROWID']);
            return;
        }
        $anapro_rec['PROCAT'] = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'];
        $anapro_rec['PROCCA'] = $this->formData[$this->nameForm . '_ANAPRO']['PROCAT'] . $this->formData[$this->nameForm . '_Clacod'];
        $anapro_rec['PROARG'] = $fascicolo_rec['ORGCOD'];
        $anapro_rec['PROFASKEY'] = $fascicolo_rec['ORGKEY'];
        $update_Info = 'Oggetto: ' . $anapro_rec['PROAGG'] . " " . $anapro_rec['PRODAR'];
        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info);
        $this->Modifica($anapro_rec['ROWID']);
    }

    private function decodDettaglioPratica($chiave) {
//TODO@ rivedere la messaggistica del fascicolo.........
        $propas_rec = $this->proLibPratica->GetPropas($chiave);
        $anapro_fas = $this->proLib->GetAnapro($propas_rec['PASPRO'], 'codice', $propas_rec['PASPAR']);
        $chiave = $anapro_fas['PROFASKEY'];
        if ($anapro_fas['PROSUBKEY']) {
            $chiave = $anapro_fas['PROSUBKEY'];
        }
        Out::show($this->nameForm . '_divPratica');


        $testo = "Pratica : "
                . (int) substr($propas_rec['PRONUM'], 14) . '/' . substr($propas_rec['PRONUM'], 10, 4)
                . ' - ' . $propas_rec['PRODPA'] . " - $chiave";
        if (!$this->proLibPratica->checkStatoFascicolo($chiave)) {
            $testo = ' FASCICOLO CHIUSO - '; // . $testo;
            Out::html($this->nameForm . '_divIconaPrat', '<div class="ita-icon ita-icon-divieto-16x16">Fascicolo Chiuso</div>');
            Out::show($this->nameForm . '_datiPratica');
        } else {
            $testo = '';
//Out::html($this->nameForm . '_divIconaPrat', '<div class="ita-icon ita-icon-edit-16x16">Azione</div>');
            Out::html($this->nameForm . '_divIconaPrat', '');
            Out::hide($this->nameForm . '_datiPratica');
        }
        Out::valore($this->nameForm . '_datiPratica', $testo);
    }

    private function MarcaDocumentoConSegnatura($destFile, $ForcePos = array()) {
        $docmeta = array();
        $output = $this->proLibAllegati->ComponiPDFconSegnatura($this->anapro_record, $destFile, $ForcePos);
        if (!$output) {
            $errMsg = $this->proLibAllegati->getErrMessage();
            Out::msgStop("Attenzione!", "$errMsg<br><br>Marcatura del documento impossibile.<br>Allego il documento senza marcatura.");
            $output = $destFile;
        } else {
            $docmeta = array('SEGNATURA' => true);
        }
        return array('output' => $output, 'docmeta' => $docmeta);
    }

    private function marcaAllegatiDaFascicolo($pronum, $propar) {
        $risultato = $this->proLibAllegati->checkMarcati($pronum, $propar);
        if ($risultato['TOTALI'] > 0 && $risultato['TOTALI'] <> $risultato['MARCATI']) {
            Out::msgQuestion("Marca Allegati.", "Vuoi marcare gli allegati PDF provenienti dal fascicolo?", array(
                'No' => array('id' => $this->nameForm . '_AnnullaMarcaAllegati', 'model' => $this->nameForm),
                'Marca gli Allegati' => array('id' => $this->nameForm . '_ConfermaMarcaAllegati', 'model' => $this->nameForm)
                    )
            );
        }
    }

    private function correggiAssegnazioni($anaproNew_rec) {
        $anades_tab = $this->proLib->GetAnades($anaproNew_rec['PRONUM'], 'codice', true, $this->tipoProt, 'T');
        if ($anades_tab) {
            foreach ($anades_tab as $key => $anades_rec) {
                if (!$this->deleteRecord($this->PROT_DB, 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                    return false;
                }
            }
        }
        /*
         * Lettura dati da SubForm Trasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();
        $proArriUff = $proSubTrasmissioni->getProArriUff();

        if ($proArriDest) {
            foreach ($proArriDest as $key => $record) {
                $anades_rec = array();
                $anades_rec['DESNUM'] = $anaproNew_rec['PRONUM'];
                $anades_rec['DESPAR'] = $record['DESPAR'];
                $anades_rec['DESCOD'] = $record['DESCOD'];
                $anades_rec['DESNOM'] = $record['DESNOM'];
                $anades_rec['DESIND'] = $record['DESIND'];
                $anades_rec['DESCAP'] = $record['DESCAP'];
                $anades_rec['DESCIT'] = $record['DESCIT'];
                $anades_rec['DESPRO'] = $record['DESPRO'];
                $anades_rec['DESDAT'] = $record['DESDAT'];
                $anades_rec['DESDAA'] = $record['DESDAA'];
                $anades_rec['DESDUF'] = $record['DESDUF'];
                $anades_rec['DESANN'] = $record['DESANN'];
                $anades_rec['DESMAIL'] = $record['DESMAIL'];
                $anades_rec['DESSER'] = $record['DESSER'];
                $anades_rec['DESCUF'] = $record['DESCUF'];
                $anades_rec['DESGES'] = $record['DESGES'];
                $anades_rec['DESRES'] = $record['DESRES'];
                $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                $anades_rec['DESTERMINE'] = str_replace('-', '', $record['TERMINE']);
                $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                $anades_rec['DESTIPO'] = "T";
                $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                    return false;
                }
            }
        }

        $uffpro_tab = $this->proLib->GetUffpro($anaproNew_rec['PRONUM'], 'codice', true, $anaproNew_rec['PROPAR']);
        foreach ($uffpro_tab as $key => $uffpro_rec) {
            if (!$this->deleteRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec['ROWID'], '', 'ROWID', false)) {
                return false;
            }
        }
        foreach ($proArriUff as $key => $record) {
            $uffpro_rec = array();
            $uffpro_rec['PRONUM'] = $anaproNew_rec['PRONUM'];
            $uffpro_rec['UFFPAR'] = $anaproNew_rec['PROPAR'];
            $uffpro_rec['UFFCOD'] = $record['UFFCOD'];
            $uffpro_rec['UFFFI1'] = $record['UFFFI1'];
            $insert_Info = 'Inserimento: ' . $uffpro_rec['PRONUM'] . ' ' . $uffpro_rec['UFFCOD'];
            if (!$this->insertRecord($this->PROT_DB, 'UFFPRO', $uffpro_rec, $insert_Info)) {
                return false;
            }
        }
        if ($this->anapro_record['PROSTATOPROT'] != proLib::PROSTATO_ANNULLATO) {
//        if (strlen($this->tipoProt) == 1) {
            $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
            $iter->sincIterProtocollo();
        }
        return true;
    }

    public function toggleAll($modo = 'disabilita') {
        $this->toggleDatiPrincipali($modo);
        //  $this->toggleMittentiDestinatari($modo);
//        $this->toggleAssegnazioniInterne($modo);
        $this->toggleAllegati($modo);
        $this->toggleNote($modo);
    }

    public function toggleDatiPrincipali($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                $enableDis = 'disableField';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                $enableDis = 'enableField';
                break;
        }
        Out::$classMethod($this->nameForm . '_Propre1', "ita-readonly");
        Out::attributo($this->nameForm . '_Propre1', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_Propre2', "ita-readonly");
        Out::attributo($this->nameForm . '_Propre2', "readonly", $attrCmd);

//        Out::$classMethod($this->nameForm . '_ANAPRO[PROPARPRE]', "ita-readonly");
//        Out::attributo($this->nameForm . '_ANAPRO[PROPARPRE]', "readonly", $attrCmd);
        out::$enableDis($this->nameForm . '_ANAPRO[PROPARPRE]');
        Out::$hideShow($this->nameForm . '_CercaProtPre');

        Out::$classMethod($this->nameForm . '_ANAPRO[PROCODTIPODOC]', "ita-readonly");
        Out::$hideShow($this->nameForm . '_ANAPRO[PROCODTIPODOC]_butt');

        Out::$classMethod($this->nameForm . '_codiceOggetto', "ita-readonly");
        Out::attributo($this->nameForm . '_codiceOggetto', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_codiceOggetto_butt');

        Out::$classMethod($this->nameForm . '_Oggetto', "ita-readonly");
        Out::attributo($this->nameForm . '_Oggetto', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_AggiungiOggetto');


        Out::$classMethod($this->nameForm . '_ANAPRO[PRODAA]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRODAA]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRODAS]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRODAS]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONPA]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONPA]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_DESCOD', "ita-readonly");
        Out::attributo($this->nameForm . '_DESCOD', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_DESNOM', "ita-readonly");
        Out::attributo($this->nameForm . '_DESNOM', "readonly", $attrCmd);

        Out::$hideShow($this->nameForm . '_DESCOD_butt');

        Out::$hideShow($this->nameForm . '_UFFNOM_butt');

        Out::$classMethod($this->nameForm . '_Proarg', "ita-readonly");
        Out::attributo($this->nameForm . '_Proarg', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROTSP]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROTSP]', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_ANAPRO[PROTSP]_butt');

        Out::$classMethod($this->nameForm . '_ANAPRO[PRODRA]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRODRA]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONUR]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONUR]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRODAAORA]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRODAAORA]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONAL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONAL]', "readonly", $attrCmd);
        if ($modo == 'disabilita') {
            Out::hide($this->nameForm . '_Riserva');
            Out::hide($this->nameForm . '_NonRiserva');
            Out::hide($this->nameForm . '_tso');
        }
        $this->StatoToggle['toggleDatiPrincipali'] = $modo;
    }

    public function toggleMailMittentiDestinatari($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $this->altriDestPerms = 'nessuno';
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $this->altriDestPerms = 'indirizzo';
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }

        Out::$classMethod($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", $attrCmd);
        $this->StatoToggle['toggleMailMittentiDestinatari'] = $modo;
    }

    public function toggleMittentiDestinatari($modo = 'disabilita', $toggleAdd = true) {
        switch ($modo) {
            case 'disabilita':
                $this->altriDestPerms = 'nessuno';
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $this->altriDestPerms = 'tutti';
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
//
//
        Out::$classMethod($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", $attrCmd);
        Out::$classMethod($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::$hideShow($this->nameForm . '_CercaAnagrafe');
        Out::$hideShow($this->nameForm . '_CercaIPA');
//Out::$hideShow($this->nameForm . '_AggiungiMittente');

        Out::$classMethod($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", $attrCmd);
//
//
        Out::$hideShow($this->nameForm . '_MittentiAggiuntivi');
        if ($toggleAdd) {
            Out::$hideShow($this->gridAltriDestinatari . '_addGridRow');
        }
        Out::$hideShow($this->gridAltriDestinatari . '_delGridRow');
        $this->StatoToggle['toggleMittentiDestinatari'] = $modo;
    }

    public function toggleAssegnazioniInterne($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
        Out::$hideShow($this->gridDestinatari . '_addGridRow');
        Out::$hideShow($this->gridDestinatari . '_delGridRow');
        Out::$hideShow($this->gridDestinatari . '_divCamp');
        $this->StatoToggle['toggleAssegnazioniInterne'] = $modo;
    }

    public function toggleAllegati($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
        Out::$hideShow($this->gridAllegati . '_addGridRow');
        Out::$hideShow($this->gridAllegati . '_delGridRow');
        Out::$hideShow($this->nameForm . '_divBottoniAllega');
        Out::$hideShow($this->nameForm . '_Scanner');
        Out::$hideShow($this->nameForm . '_ScannerShared');
        Out::$hideShow($this->nameForm . '_DaP7m');
        Out::$hideShow($this->nameForm . '_DaTestoBase');
        Out::$hideShow($this->nameForm . '_FileLocale_uploader');
        Out::$hideShow($this->nameForm . '_DaFascicolo');
        Out::$hideShow($this->nameForm . '_DaProtCollegati');
        $anaent_47 = $this->proLib->GetAnaent('47');
        if ($anaent_47['ENTDE4']) {
            Out::hide($this->nameForm . '_DaP7m');
        }
        $anaent_57 = $this->proLib->GetAnaent('57');
        if (!$anaent_57['ENTDE1']) {
            Out::hide($this->nameForm . '_ScannerShared');
        }
        if ($this->tipoProt == 'A' || !$anaent_47['ENTDE5']) {
            Out::hide($this->nameForm . '_DaTestoBase');
        }
        $this->StatoToggle['toggleAllegati'] = $modo;
    }

    public function toggleNote($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
        Out::$hideShow($this->gridNote . '_addGridRow');
        Out::$hideShow($this->gridNote . '_delGridRow');
        $this->StatoToggle['toggleNote'] = $modo;
    }

    public function toggleButtonBar($modo = 'disabilita') {
        $this->StatoToggle['toggleButtonBar'] = $modo;
    }

    public function InserisciTabDag($Anapro_rec, $currObjSdi) {
        $retTabDag = array();
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        if (is_object($currObjSdi)) {
            if ($currObjSdi->isFatturaPA() || $currObjSdi->isMessaggioSdi()) {
                if (!$currObjSdi->isFatturaPA()) {
                    $Estratto = $currObjSdi->getEstrattoMessaggio();
                    $AnaproCollegato = $this->proLibSdi->GetAnaproDaCollegareFromEstratto($Estratto, $this->tipoProt);
                    if ($AnaproCollegato) {
                        //Valorizzo solo se sono vuoti:
                        if (!$Anapro_rec['PROPRE']) {
                            $Anapro_rec['PROPARPRE'] = $AnaproCollegato['PROPAR'];
                            $Anapro_rec['PROPRE'] = $AnaproCollegato['PRONUM'];
                            //Aggiorno Anapro
                            $update_Info = 'Oggetto: Prot. ' . $Anapro_rec['PRONUM'] . " " . $Anapro_rec['PROPAR'] . '. Collegamento Anapro - SDI.';
                            if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $Anapro_rec, $update_Info)) {
                                $retTabDag['stato'] = false;
                                $retTabDag['messaggio'] = 'Errore in aggiornamento ANAPRO.';
                                return $retTabDag;
                            }
                            //Nel scaso in cui si alleghi soltanto un EC, bisogna anche visualizzare prot prec.
                            Out::valore($this->nameForm . '_ANAPRO[PROPARPRE]', $AnaproCollegato['PROPAR']);
                            Out::valore($this->nameForm . '_Propre1', substr($AnaproCollegato['PRONUM'], 4));
                            Out::valore($this->nameForm . '_Propre2', substr($AnaproCollegato['PRONUM'], 0, 4));
                        }
                        // Aggiungo e poi Aggiorno il collegamento all'anapro?
                    } else if ($this->proLibSdi->getErrCode() < 0) {
                        Out::msgInfo('Attenzione', 'Non è stato possibile trovare il protocollo collegato a questo Messaggio di Notifica.<br>' . $this->proLibSdi->getErrMessage());
                    } else {
                        //@TODO Altrimenti non esiste un collegamento per questo messaggio...?!
                    }
                }
                // Salvo i TabDag <-
                $ret = $proLibTabDag->InserisciTabDagSdi($Anapro_rec, $currObjSdi);
                if (!$ret) {
                    $retTabDag['stato'] = false;
                    $retTabDag['messaggio'] = $proLibTabDag->getErrMessage();
                    return $retTabDag;
                }
            }
        }
        $retTabDag['stato'] = true;
        return $retTabDag;
    }

    private function SalvaMetadatiPartenzaSdi($Anapro_rec, $destFile, $docName) {
        $anaent_38 = $this->proLib->GetAnaent('38');

        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        if (strtolower($ext) != 'zip' && strtolower($ext) != 'xml' && strtolower($ext) != 'p7m') {
            return array('stato' => true);
        }
//        $protPath = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//        $FileProto = $protPath . "/" . pathinfo($destFile, PATHINFO_BASENAME);

        $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $docName);
        $currObjSdi = proSdi::getInstance($FileSdi);
// Se sono attivi i controlli su xml:
//        if ($anaent_38['ENTDE5']) {
//            $retCtr = $currObjSdi->ControllaValiditaXml();
//            if (!$retCtr) {
//                $ret['stato'] = false;
//                $ret['messaggio'] = $currObjSdi->getErrMessage();
//                return $ret;
//            }
//        }
//
        if (!$currObjSdi) {
            $ret['stato'] = false;
            $ret['messaggio'] = 'Errore nell\'istanziare proSdi.';
            return $ret;
        }
        if ($currObjSdi->getErrCode() < 0) {
            $ret['stato'] = false;
            $ret['messaggio'] = $currObjSdi->getErrMessage();
            return $ret;
        }
        $ret = $this->InserisciTabDag($Anapro_rec, $currObjSdi);
        return $ret;
    }

    private function CancellaMetadatiSdi($Anapro_rec, $destFile, $docName) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        if (strtolower($ext) != 'zip' && strtolower($ext) != 'xml' && strtolower($ext) != 'p7m') {
            return array('stato' => true);
        }
        $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $docName);
        $currObjSdi = proSdi::getInstance($FileSdi);
        if (!$currObjSdi) {
            $ret['stato'] = false;
            $ret['messaggio'] = 'Errore nell\'istanziare proSdi.';
            return $ret;
        }
        if ($currObjSdi->getErrCode() < 0) {
            $ret['stato'] = false;
            $ret['messaggio'] = $currObjSdi->getErrMessage();
            return $ret;
        }
// Se non è un messaggio SDI è una FatturaPA
        if ($currObjSdi->isMessaggioSdi()) {
            $retCanc = $proLibTabDag->CancellaTabDagSdi($Anapro_rec, 'MESSAGGIO_SDI');
        } else {
            $retCanc = $proLibTabDag->CancellaTabDagSdi($Anapro_rec, 'FATT_ELETTRONICA');
        }
        if (!$retCanc) {
            $ret['stato'] = false;
            $ret['messaggio'] = $proLibTabDag->getErrMessage();
            return $ret;
        }

        return array('stato' => true);
    }

    private function AggiungiMittente() {
        $mednom = $_POST[$this->nameForm . '_ANAPRO']['PRONOM'];
        $medcit = $_POST[$this->nameForm . '_ANAPRO']['PROCIT'];
        $medind = $_POST[$this->nameForm . '_ANAPRO']['PROIND'];
        $medcap = $_POST[$this->nameForm . '_ANAPRO']['PROCAP'];
        $medpro = $_POST[$this->nameForm . '_ANAPRO']['PROPRO'];
        $email = $_POST[$this->nameForm . '_ANAPRO']['PROMAIL'];
        $fisc = $_POST[$this->nameForm . '_ANAPRO']['PROFIS'];
        if ($mednom) {
            $risultato = $this->proLib->registraAnamed($mednom, $medcit, $medind, $medcap, $medpro, $email, $fisc);
            if ($risultato['MEDCOD']) {
                $this->DecodAnamed($risultato['MEDCOD']);
            }
            Out::msgInfo($risultato['titolo'], $risultato['messaggio']);
        }
        Out::setFocus('', $this->nameForm . "_ANAPRO[PRONOM]");
    }

    private function ControllaAllegati($currRowid = '') {
        $principale = false;
        $keyPrincipale = '';
        foreach ($this->proArriAlle as $k => $allegato) {
            if ($allegato['DOCTIPO'] == '') {
                $principale = true;
                $keyPrincipale = $k;
            }
        }
        /* Se ho appena impostato che l'allegato corrente è il principale, allora 
         * keyPrincipale è il rowid corrente.
         */
        if ($currRowid) {
            if ($this->proArriAlle[$currRowid]['DOCTIPO'] == '') {
                $keyPrincipale = $currRowid;
            }
        }
        /* Metto il primo Principale 
         * 1. se è l'unico allegato, il primo è principale
         * 2. se è più di un allegato, ed è indicato allegato appena modificato
         *    prendo il primo diverso
         * 3. se è più di un allegato e non è indicato appena modificato
         *    prendo il primo che trovo
         */
        if ($principale == false) {
            foreach ($this->proArriAlle as $key => $allegato) {
                if (count($this->proArriAlle) == 1 || $key != $currRowid || $currRowid == '') {
                    $this->proArriAlle[$key]['DOCTIPO'] = '';
                    if ($allegato['ROWID']) {
                        $this->proLibAllegati->AggiornAllegato($this, $this->proArriAlle[$key]);
                    }
                    break;
                }
            }
        } else {
            /* Verifico se è presente più di un 
             * allegato princiaple e ne tengo uno */
            foreach ($this->proArriAlle as $key => $allegato) {
                if ($allegato['DOCTIPO'] == '' && $key != $keyPrincipale) {
                    $this->proArriAlle[$key]['DOCTIPO'] = 'ALLEGATO';
                    if ($allegato['ROWID']) {
                        $this->proLibAllegati->AggiornAllegato($this, $this->proArriAlle[$key]);
                    }
                }
            }
        }
    }

    public function ApriGestioneFascicolo($chiaveFascicolo = '') {
        if (!$chiaveFascicolo) {
            $chiaveFascicolo = $this->anapro_record['PROFASKEY'];
        }
        $anapro_check = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'fascicolo', $chiaveFascicolo);
        if (!$anapro_check) {
            Out::msgStop("Attenzione", "Non hai accesso al Fascicolo.");
            return;
        }
        $model = 'proGestPratica';
        itaLib::openForm($model);
        $proGestPratica = itaModel::getInstance($model, $model);
        $proGestPratica->setEvent('openform');
        $proGestPratica->setOpenGeskey($chiaveFascicolo);
        $proGestPratica->parseEvent();
    }

    public function ApriSelezioneFascicolo($Titolario, $retEvent = 'returnAlberoFascicolo', $abilitaCreazione = true) {
        $model = 'proSeleFascicolo';
        itaLib::openForm($model);
        /* @var $proSeleFascicolo proSeleFascicolo */
        $proSeleFascicolo = itaModel::getInstance($model);
        $proSeleFascicolo->setEvent('openform');
        $proSeleFascicolo->setReturnModel($this->nameForm);
        $proSeleFascicolo->setTitolario($Titolario);
        $proSeleFascicolo->setReturnEvent($retEvent);
        $proSeleFascicolo->setReturnId($this->nameForm . '_AggiungiFascicolo');
        $proSeleFascicolo->setAbilitaCreazione($abilitaCreazione);
        $proSeleFascicolo->parseEvent();
    }

    /*  PATCH PROGRE */

    public function PrenotaAggiornaCodOggettoAnapro($rowid, $codiceOggetto = '') {
        $progressivo = '0';
        if ($codiceOggetto) {
            $retLock = ItaDB::DBLock($this->PROT_DB, "ANADOG", "", "", 20);
            if ($retLock['status'] != 0) {
                Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVO OGGETTO non Riuscito.');
                return false;
            }
            $anadog_rec = $this->proLib->GetAnadog($codiceOggetto, 'codice');
            if ($anadog_rec) {
                if ($anadog_rec['DOGINCREMENTALE'] > 0) {
                    $progressivo = $anadog_rec['DOGINCREMENTALE'];
                    $anadog_rec['DOGINCREMENTALE'] ++;
                    $update_Info = 'Oggetto: ' . $anadog_rec['DOGCOD'] . " " . $anadog_rec['DOGINCREMENTALE'];
                    if (!$this->updateRecord($this->PROT_DB, 'ANADOG', $anadog_rec, $update_Info)) {
                        Out::msgStop('Errore', 'Errore in aggiornamento tabella ANADOG.');
                        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
                        return false;
                    }
                }
            }
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
            if ($retUnlock['status'] != 0) {
                Out::msgStop('Errore', 'Sblocco Tabella PROGRESSIVI non Riuscito.');
                return false;
            }
            $Anapro_rec = array();
            $Anapro_rec['ROWID'] = $rowid;
            $Anapro_rec['PROINCOGG'] = $progressivo;
            $update_Info = 'ANAPRO ROWID:' . $rowid . '. Oggetto: ' . $progressivo;
            if (!$this->updateRecord($this->PROT_DB, 'ANAPRO', $Anapro_rec, $update_Info)) {
                Out::msgStop('Errore', 'Errore in aggiornamento progressivo OGGETTO sul PROTOCOLLO.');
                return false;
            }
            Out::valore($this->nameForm . '_ANAPRO[PROINCOGG]', $progressivo);
            Out::valore($this->nameForm . '_ANAPRO[PRODOGCOD]', $anadog_rec['PRODOGCOD']);
        }
        return true;
    }

    /* FINE PATCH PROGRE */

    public function ApriRicercaProtoCollegato() {
        if ($this->consultazione == true) {
            return;
        }
        $Numero = $_POST[$this->nameForm . '_Propre1'];
        $Anno = $_POST[$this->nameForm . '_Propre2'];
        $Tipo = $_POST[$this->nameForm . '_ANAPRO']['PROPARPRE'];
        if ($Numero && $Anno && $Tipo) {
            return;
        }
        if (!$Numero) {
            return;
        }
        proRic::proRicProtoCollegato($this->nameForm, $Numero, $Anno);
    }

    public function DecodificaProtoCollegato($Codice = '', $Tipo = '') {
        if ($this->consultazione != true) {
            $anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $Codice, $Tipo);
            if (!$anapro_rec) {
                Out::msgInfo("Attenzione Protocollo", "Protocollo Collegato non accessibile.");
                Out::valore($this->nameForm . '_Propre1', '');
                Out::valore($this->nameForm . '_Propre2', '');
                Out::valore($this->nameForm . '_ANAPRO[PROPARPRE]', '');
                Out::setFocus('', $this->nameForm . "_Propre1");
            } else {
                $this->setRiscontro($anapro_rec, true);
                Out::setFocus('', $this->nameForm . '_ANAPRO[PRODAS]');
            }
        }
    }

    public function ControlloDestinatariObbligatori() {

        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();

        /* proDestinatari ha almeno 1 record per la dicitura "DESTINATARI:", vedi elaboraAlbero */
        if ($this->tipoProt != 'A' && $this->tipoProt != 'C') {
            return true;
        }
        $anaent_36 = $this->proLib->GetAnaent('36');
        if (!$anaent_36['ENTDE3'] && !$anaent_36['ENTDE4']) {
            return true;
        }
        if ($anaent_36['ENTDE3'] == 1 && $this->tipoProt == 'A') {
            if (count($proArriDest) < 1) {
                return false;
            }
        }
        if ($anaent_36['ENTDE4'] == 1 && $this->tipoProt == 'C') {
            if (count($proArriDest) < 1) {
                return false;
            }
        }
        return true;
    }

    public function ProponiPosizioneMarcatura($Evento = '') {
        $anaent_47 = $this->proLib->GetAnaent('47');
        $campi[] = array(
            'label' => array(
                'value' => "Posizione Marcatura",
                'style' => 'margin-left:55px;width:150px;display:block;float:left;padding: 0 5px 0 0;text-align:left;'
            ),
            'id' => $this->nameForm . '_PosizioneMarcatura',
            'name' => $this->nameForm . '_PosizioneMarcatura',
            'type' => 'select',
            'style' => 'margin:2px;',
            'class' => 'ita-select'
        );
        Out::msgInput("Posizione Marcatura", $campi, array('Conferma' => array('id' => $this->nameForm . '_ConfMarcatura' . $Evento, 'model' => $this->nameForm)), $this->nameForm);
        Out::select($this->nameForm . '_PosizioneMarcatura', 1, "", "1", "Posizione predefinita");
        $PosizioniMarcatura = $this->proLibAllegati->GetPosizioniSegnatura();
        foreach ($PosizioniMarcatura as $key => $Segnatura) {
            Out::select($this->nameForm . '_PosizioneMarcatura', 1, $Segnatura['IDSEGN'], "0", $Segnatura['DESC']);
        }
        if ($this->anapro_record['PROPAR'] == 'A') {
            Out::valore($this->nameForm . '_PosizioneMarcatura', $anaent_47['ENTDE2']);
        } else {
            Out::valore($this->nameForm . '_PosizioneMarcatura', $anaent_47['ENTDE3']);
        }
    }

    public function AllegaDaP7m() {
        if (!$this->datiUtiP7m['p7m']) {
            Out::msgStop("Attenzione", "Oggetto p7m mancante.");
            return false;
        }
        if (!$this->datiUtiP7m['NomeFileContenuto']) {
            Out::msgStop("Attenzione", "Nome file contenuto mancante.");
            return false;
        }
        $p7m = unserialize($this->datiUtiP7m['p7m']);
        $NomeFile = $this->datiUtiP7m['NomeFileContenuto'];
        $subPath = "proP7mCheck-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        //$basename = pathinfo($p7m->getContentFileName(), PATHINFO_BASENAME);
        $randName = md5(rand() * time()) . "." . pathinfo($NomeFile, PATHINFO_EXTENSION);
        $DestinoFile = $tempPath . '/' . $randName;
        if (!@copy($p7m->getContentFileName(), $DestinoFile)) {
            Out::msgStop("Errore", "Sbusta File. Errore nella copia del file da p7m.");
            return false;
        }
        /* Copiato il file pulisco l'oggetto p7m */
        $p7m->cleanData();

        if (!$this->proLibAllegati->AggiungiAllegato($this, $this->anapro_record, $DestinoFile, $NomeFile)) {
            Out::msgStop("Errore", "Impossibile aggiungere il file pdf contenuto nel p7m.<br>" . $this->proLibAllegati->getErrMessage());
            return false;
        }
        /* Rilettura dell'anadoc appena caricato */
        $RisultatoRitorno = $this->proLibAllegati->getRisultatoRitorno();
        $rowidAnadoc = $RisultatoRitorno['ROWIDAGGIUNTI'][0];
//        $protPath = $this->proLib->SetDirectory($this->anapro_record['PRONUM'], $this->anapro_record['PROPAR']);
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
//        $Anadoc_rec['FILEPATH'] = $protPath . "/" . $Anadoc_rec['DOCFIL'];
        $DocPath = $this->proLibAllegati->GetDocPath($Anadoc_rec['ROWID']);
        $Anadoc_rec['FILEPATH'] = $DocPath['DOCPATH'];
        $this->varAppoggio = $Anadoc_rec;
        $this->CaricaAllegati();
        $this->proLibAllegati->setFunzioneAllegati($this->nameForm, $Anadoc_rec, $this->anapro_record['PRONUM'], $this->anapro_record['PROPAR'], $this->bloccoDaEmail);
        return true;
    }

    public function MarcaturaSuDocumento() {
        $anapro_rec = $this->anapro_record;
        $ForceMarcat = array();
        if ($this->varMarcatura['FORZA_MARCATURA']) {
            $ForceMarcat = $this->varMarcatura['FORZA_MARCATURA'];
        }
        $DocPath = $this->proLibAllegati->GetDocPath($this->varAppoggio['ROWID'], false, false, true);
//        $this->proLibAllegati->SegnaDocumento($this, $this->varAppoggio['FILEPATH'], $anapro_rec, $this->varAppoggio['ROWID'], $ForceMarcat);
        $this->proLibAllegati->SegnaDocumento($this, $DocPath['DOCPATH'], $anapro_rec, $this->varAppoggio['ROWID'], $ForceMarcat);
        $this->CaricaAllegati();
        $this->ricaricaFascicolo();
    }

    public function MarcaturaSuDocumentoDaFirmare() {
        $anapro_rec = $this->anapro_record;
        $ForceMarcat = array();
        if ($this->varMarcatura['FORZA_MARCATURA']) {
            $ForceMarcat = $this->varMarcatura['FORZA_MARCATURA'];
        }
        $DocPath = $this->proLibAllegati->GetDocPath($this->varAppoggio['ROWID'], false, false, true);
        $this->proLibAllegati->SegnaDocumento($this, $DocPath['DOCPATH'], $anapro_rec, $this->varAppoggio['ROWID'], $ForceMarcat);
//        $this->proLibAllegati->SegnaDocumento($this, $this->varAppoggio['FILEPATH'], $anapro_rec, $this->varAppoggio['ROWID'], $ForceMarcat);
        $anades_mitt = $this->proLib->GetAnades($this->anapro_record['PRONUM'], 'codice', false, $this->tipoProt, 'M');
        if (!$anades_mitt) {
            Out::msgStop("Attenzione", "Firmatario Principale Mancante");
            return;
        }
        $this->proLibAllegati->DaFirmare($this, $anades_mitt, $this->varAppoggio['ROWID']);
        $iter = proIter::getInstance($this->proLib, $anapro_rec);
        $iter->sincronizzaIterFirma('aggiungi');
        $this->CaricaAllegati();
        $this->ricaricaFascicolo();
    }

    private function MostraNascondiDocServizio() {
        Out::hide($this->nameForm . '_AllegatiServizio');
        $whereDoc = "  AND DOCSERVIZIO <> 0 ";
        $AnadocServ_tab = $this->proLib->GetAnadoc($this->anapro_record['PRONUM'], 'codice', true, $this->anapro_record['PROPAR'], $whereDoc);
        if ($AnadocServ_tab) {
            Out::show($this->nameForm . '_AllegatiServizio');
        }
    }

    private function CaricaAllegatiDaProtocollo($pronum, $propar) {
        $proArriAlle = $this->proLibAllegati->CopiaAllegatiDaProtocollo($pronum, $propar);
        if ($proArriAlle === false) {
            Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
        } else {
            Out::hide($this->nameForm . '_CercaProtPre');
            Out::hide($this->nameForm . '_DuplicaProt');
        }
        if ($proArriAlle) {
            proRic::proSelectAllegati($proArriAlle, $this->nameForm);
        }
    }

    public function InserisciInFascicolo() {
        $pronumR = substr($this->returnData['retKey'], 4, 10);
        $proparR = substr($this->returnData['retKey'], 14);
        $this->varAppoggio = array();
        $this->varAppoggio['ROWID'] = $this->returnData['ROWID_ANAORG'];
        $fascicolo_rec = $this->proLib->GetAnaorg($this->varAppoggio['ROWID'], 'rowid');
        $anapro_rec = $this->anapro_record;
        if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $fascicolo_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $pronumR, $proparR)) {
            Out::msgStop("Attenzione! Fascicolazione.", $this->proLibFascicolo->getErrMessage());
        } else {
            $this->Modifica($anapro_rec['ROWID']);
//            Out::msgBlock('', 2000, false, "Nuovo fascicolo creato correttamente");
        }
    }

    public function DecodVersioneTitolario($Versione_t = '') {
        // Out Descrizione Versione Titolario:
        Out::hide($this->nameForm . '_VersioneTitolario');
        if (!$this->proLibTitolario->CheckVersioneUnica() && $Versione_t !== '') {
            Out::show($this->nameForm . '_VersioneTitolario');
            $Versione_rec = $this->proLibTitolario->GetVersione($Versione_t, 'codice');
            Out::html($this->nameForm . '_VersioneTitolario', '(' . $Versione_rec['DESCRI_B'] . ')');
        }
    }

    public function GetFormAnnullaRiattiva($tipo = '') {
        // Dati Annullamento
        $AnnullamentoRec = $_POST[$this->nameForm . '_ANNULLAMENTO'];
        $anapro_rec = $this->proLib->GetAnapro($this->anapro_record['ROWID'], 'rowid');
        $arcite_tab = $this->proLib->getGenericTab("SELECT * FROM ARCITE WHERE ITEPRO=" . $anapro_rec['PRONUM']
                . " AND ITENODO='ASS' AND ITEPAR='$this->tipoProt'");
        // Data provvedimento
        $valori[] = array('label' => array('style' => "width:180px;", 'value' => 'Tipo provvedimento:'),
            'id' => $this->nameForm . '_ANNULLAMENTO[PROANNPTIPO]',
            'name' => $this->nameForm . '_ANNULLAMENTO[PROANNPTIPO]',
            'type' => 'text',
            'size' => '25',
            'maxlength' => '20',
            'class' => 'ita-edit');
        $valori[] = array('label' => array('style' => "width:180px;", 'value' => 'N. provvedimento:'),
            'id' => $this->nameForm . '_ANNULLAMENTO[PROANNPNUM]',
            'name' => $this->nameForm . '_ANNULLAMENTO[PROANNPNUM]',
            'type' => 'text',
            'size' => '25',
            'maxlength' => '20',
            'class' => 'ita-edit');
        $valori[] = array('label' => array('style' => "width:180px;", 'value' => 'Data provvedimento:'),
            'id' => $this->nameForm . '_ANNULLAMENTO[PROANNPDATA]',
            'name' => $this->nameForm . '_ANNULLAMENTO[PROANNPDATA]',
            'type' => 'text',
            'size' => '12',
            'class' => 'ita-datepicker');
        $valori[] = array('label' => array('style' => "width:180px;", 'value' => 'Operazione autorizzata da:'),
            'id' => $this->nameForm . '_ANNULLAMENTO[PROANNAUTOR]',
            'name' => $this->nameForm . '_ANNULLAMENTO[PROANNAUTOR]',
            'type' => 'text',
            'size' => '25',
            'maxlength' => '20',
            'class' => 'ita-edit');

        // Motivo Annullamento/Riattivazione 
        $valori[] = array(
            'label' => array(
                'value' => "<b>Motivo:</b>",
                'style' => 'margin-top:10px;width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right; color:red;'
            ),
            'id' => $this->nameForm . '_ANNULLAMENTO[PROANNMOTIVO]',
            'name' => $this->nameForm . '_ANNULLAMENTO[PROANNMOTIVO]',
            'type' => 'textarea',
            'class' => 'ita-edit-multiline',
            'style' => 'margin-top:10px;width:450px;',
            'value' => ''
        );

        // Quale messaggio deve partire: Annulla/Riattiva
        if ($tipo == 'A') {
            $messaggio = "Conferma motivando l'annullamento del Protocollo:";
            if (count($arcite_tab) > 0) {
                $messaggio = "Il Protocollo è stato Gestito.<br>Forzare l'Annullamento del Protocollo?";
                if ($this->tipoProt == 'C') {
                    $messaggio = "Il Documento Formale è stata Gestita.<br>Forzare l'Annullamento del Documento Formale?";
                }
            }
            Out::msgInput(
                    'Annulla Protocollo', $valori
                    , array(
                'Conferma' => array('id' => $this->nameForm . '_ConfermaInputAnnulla', 'model' => $this->nameForm),
                'Annulla' => array('id' => $this->nameForm . '_AnnullaInputRiattiva', 'model' => $this->nameForm)
                    ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
            );
        } else {
            $messaggio = "Conferma motivando la riattivazione del Protocollo:";
            Out::msgInput(
                    'Riattiva Protocollo', $valori
                    , array(
                'Conferma' => array('id' => $this->nameForm . '_ConfermaInputRiattiva', 'model' => $this->nameForm),
                'Annulla' => array('id' => $this->nameForm . '_AnnullaInputRiattiva', 'model' => $this->nameForm)
                    ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
            );
        }
        Out::setFocus('', $this->nameForm . '_ANNULLAMENTO[PROANNMOTIVO]');
        if ($AnnullamentoRec) {
            Out::valori($AnnullamentoRec, $this->nameForm . '_ANNULLAMENTO');
        }
    }

    public function CancellaAllegatoProvvisorio($rowid_alle) {
        if (!@unlink($this->proArriAlle[$rowid_alle]['FILEPATH'])) {
            Out::msgStop("Cancellazione", "Errore in cancellazione file.");
            return false;
        }
        unset($this->proArriAlle[$rowid_alle]);
        $this->CaricaAllegati();
        return true;
    }

    public function CtrAllegatiObbligatori() {
        $anaent_32 = $this->proLib->GetAnaent('32');
        if ($anaent_32['ENTDE4'] == '2') {
            // Attivo Da File e Da Scanner.
            Out::show($this->nameForm . '_divBottoniAllega');
            Out::show($this->nameForm . '_paneAllegati');
            Out::unBlock($this->nameForm . '_paneAllegati');
            Out::show($this->nameForm . '_Scanner');
            Out::show($this->nameForm . '_FileLocale_uploader');
            Out::show($this->nameForm . '_DaFascicolo');
            Out::show($this->nameForm . '_DaProtCollegati');
            $anaent_57 = $this->proLib->GetAnaent('57');
            if ($anaent_57['ENTDE1']) {
                Out::show($this->nameForm . '_ScannerShared');
            }
        }
    }

    public function CheckProtMailInArrivo($TipoSped = '') {
        /*
         * 1. Se è Arrivo
         * 2. Se il tipo "PEC" è una pec in arrivo
         * 3. Se c'è una mail ricevuta
         * 4. Se sto creando un protocollo, ho valorizzato "fileDaPEC"
         */
        if ($this->tipoProt == 'A') {
            $Promail_R = array();
            // Se è il dettaglio di un protocollo, ricavo i dati da esso:
            if ($this->anapro_record) {
                $where = " PRONUM = '" . $this->anapro_record['PRONUM'] . "' AND PROPAR = '" . $this->anapro_record['PROPAR'] . "' AND SENDREC = 'R' ";
                $Promail_R = $this->proLib->getPromail($where);
                $TipoSped = $this->anapro_record['PROTSP'];
            }
            if ($TipoSped == 'PEC' || $Promail_R || $this->fileDaPEC) {
                return true;
            }
        }
        return false;
    }

    public function ProtocollaSimple($DatiProtocollo, $rowidAnapro = '') {
//        Out::msgInfo('Prootocolla Simple', print_r($DatiProtocollo, true));
        /*
         *  Decodifiche:
         */
        if (!$rowidAnapro) {
            if ($DatiProtocollo['COD_OGGETTO']) {
                $this->DecodAnadog($DatiProtocollo['COD_OGGETTO']);
            }
            if ($DatiProtocollo['OGGETTO']) {
                Out::valore($this->nameForm . '_Oggetto', trim($DatiProtocollo['OGGETTO']));
            }
            if ($DatiProtocollo['FIRMATARIO'] && $DatiProtocollo['FIRMATARIO_UFFICIO']) {
                $anamed_rec = $this->proLib->GetAnamed($DatiProtocollo['FIRMATARIO'], 'codice', 'si');
                Out::valore($this->nameForm . '_DESCOD', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESNOM", $anamed_rec["MEDNOM"]);
                $anauff_rec = $this->proLib->GetAnauff($DatiProtocollo['FIRMATARIO_UFFICIO'], 'codice');
                Out::valore($this->nameForm . '_DESCUF', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
            }
            if ($DatiProtocollo['MITT_DEST']) {
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $DatiProtocollo['MITT_DEST']);
            }
            if ($DatiProtocollo['TITOLARIO']) {
                $this->DecodAnacat('', substr($DatiProtocollo['TITOLARIO'], 0, 4));
                $this->DecodAnacla('', substr($DatiProtocollo['TITOLARIO'], 0, 8));
                $this->DecodAnafas('', $DatiProtocollo['TITOLARIO'], 'fasccf');
            }
            // ANADES
            if ($DatiProtocollo['ANADES']) {
                $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
                $MittDestPrincipale = $DatiProtocollo['ANADES'][0];
                unset($DatiProtocollo['ANADES'][0]);
                // PRINCIPALE:
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROCON]', $MittDestPrincipale['DESCOD']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PRONOM]', $MittDestPrincipale['DESNOM']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROIND]', $MittDestPrincipale['DESIND']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROCIT]', $MittDestPrincipale['DESCIT']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROPRO]', $MittDestPrincipale['DESPRO']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROCAP]', $MittDestPrincipale['DESCAP']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROMAIL]', $MittDestPrincipale['DESMAIL']);
                Out::valore($proSubMittDest->nameForm . '_ANAPRO[PROFIS]', $MittDestPrincipale['DESFIS']);


                $proSubMittDest->proAltriDestinatari = $DatiProtocollo['ANADES'];
                $proSubMittDest->CaricaGriglia($proSubMittDest->gridAltriDestinatari, $proSubMittDest->proAltriDestinatari);
            }

            $this->proArriAlle = $DatiProtocollo['ALLEGATI'];
            $this->CaricaAllegati();
            Out::show($this->nameForm . '_paneAllegati');
            Out::hide($this->nameForm . '_CercaProtPre');
            Out::hide($this->nameForm . '_DuplicaProt');
            $this->ProtocollaSimple = true;
        } else {
            if ($this->ProtAllegaDaFascicolo == $rowidAnapro) {
                $Anapro_rec = $this->proLib->GetAnapro($rowidAnapro, 'rowid');
                $risultato = $this->proLibAllegati->GestioneAllegati($this, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $DatiProtocollo['ALLEGATI'], $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
                $this->CaricaAllegati();
                if (!$risultato) {
                    Out::msgStop("Attenzione!", $this->proLibAllegati->getErrMessage());
                    return;
                }
                $this->ProtAllegaDaFascicolo = '';
                Out::msgBlock('', 2000, true, 'Allegati aggiunti correttamente.');
            } else {
                Out::msgInfo("Attenzione", "Protocollo variato durante la selezione.<br>Non è possibile procedere con la protocollazione.");
            }
        }
    }

    public function CheckDuplicaProt($rowid = '') {
        if ($rowid == '') {
            $data = $this->workDate;
            $newdata = date('Ymd', strtotime('-90 day', strtotime($data)));
            $utente = '999' . substr(App::$utente->getKey('nomeUtente'), 0, 7);
            if ($this->tipoProt == 'C') {
                $where = "PRODAR BETWEEN '" . $newdata . "' AND '" . $data . "' AND PROLOG LIKE '" . $utente . "%' AND PROPAR ='C'";
            } else {
                $where = "PRODAR BETWEEN '" . $newdata . "' AND '" . $data . "' AND PROLOG LIKE '" . $utente . "%' AND ( PROPAR ='A' OR  PROPAR ='P')";
            }
            $where .= " AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
            proRic::proRicNumAntecedenti($this->nameForm, $where, '', 'returnDuplica');
        } else {
            //$rowid = $_POST[$this->nameForm . '_ANAPRO']['ROWID'];
            $this->Nuovo();
            $anaent_48 = $this->proLib->GetAnaent('48');
            $this->duplicaAllegati = true;
            if ($anaent_48['ENTDE6'] != 1) {
                $this->duplicaAllegati = false;
            }
            $this->duplicaDoc($rowid);
        }
    }

    public function CheckProtConservato($Anapro_rec) {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if ($ProConser_rec) {
            // Visualizzo divConservaizone e Blocco Modifiche al protocollo.
            Out::show($this->nameForm . '_divConservazione');
            $Data = date("d/m/Y", strtotime($ProConser_rec['DATAVERSAMENTO']));
            $icona = "<span  class=\"ita-icon ita-icon-safe-24x24\" style = \"float:left;display:inline-block;margin-left:5px;\"></span>";
            $Conservazione = "<span style = \"padding:3px;display:inline-block;\"> PROTOCOLLO VERSATO IN<b> CONSERVAZIONE</b></span>";
            $Conservazione .= "<span style = \"margin-left:20px;\"> DATA VERSAMENTO<b>: $Data </b></span>";
            /*
             * Info conservazione controllato:
             */
            $DescCons = proLibConservazione::$ElencoDescrConserEsito[$ProConser_rec['ESITOCONSERVAZIONE']];
            $Conservazione .= "<span style = \"margin-left:20px;\"> ESITO CONSERVAZIONE<b>: $DescCons </b></span>";

            Out::css($this->nameForm . '_divConservazione', 'background-image', ' linear-gradient(to right, #fbec88 0%, #FFFFFF 100%)');

            Out::html($this->nameForm . '_divInfoConservazione', $icona . $Conservazione);
            $this->DisabilitaDatiConservazione(); //RIATTIVARE! PER I TEST COMMENTATO.
            /* Attivo Aggiorna assegnazioni se conservato */
            Out::show($this->nameForm . '_AggiornaAss');
        } else {
            // Nascondo comunque divConservazione.
            Out::hide($this->nameForm . '_divConservazione');
        }
        // Annullamento deve essere però permesso. (si può annullare in un secondo momento)
    }

    public function DisabilitaDatiConservazione() {
        /*
         * Dei Toggle Disabilito:
         *  - Dati principali
         *  - Assegnazioni Interne
         *  - Allegati
         * Mitt/Dest non serve, se disabilitato perchè notificato a destinatari sono già spenti.
         */
        $this->toggleDatiPrincipali('disabilita');
        $this->toggleAssegnazioniInterne('disabilita');
        $this->toggleAllegati('disabilita');
        /*
         *  Riabilito i campi modificbili:
         */
//        Out::hide($this->nameForm . '_Registra');
        Out::delClass($this->nameForm . '_Oggetto', "ita-readonly");
        Out::attributo($this->nameForm . '_Oggetto', "readonly", '1');
        /*
         * Disabilito Dati Titolario:
         */
        Out::attributo($this->nameForm . '_ANAPRO[PROCAT]', "readonly", '0');
        Out::attributo($this->nameForm . '_Clacod', "readonly", '0');
        Out::attributo($this->nameForm . '_Fascod', "readonly", '0');
        Out::hide($this->nameForm . '_ANAPRO[PROCAT]_butt');
        Out::hide($this->nameForm . '_Clacod_butt');
        Out::hide($this->nameForm . '_Fascod_butt');
        /*
         * Disabilitio Bottoni:
         */
        Out::hide($this->nameForm . '_Annulla'); // Per riattivare funzioni speciali
        Out::hide($this->nameForm . '_Riattiva'); // Per riattivare funzioni speciali
//        Out::hide($this->nameForm . '_Mail'); // Per riattivare funzioni speciali
        Out::hide($this->nameForm . '_MailMittenti'); // Per riattivare funzioni speciali.
        Out::hide($this->nameForm . '_Evidenza');
        Out::hide($this->nameForm . '_UsaModello');
        Out::hide($this->nameForm . '_AnnullaModello');
        Out::hide($this->nameForm . '_Riserva');
        Out::hide($this->nameForm . '_NonRiserva');
        //Out::hide($this->nameForm . '_addFascicolo');//Questo può rimanere attivo.
        Out::hide($this->nameForm . '_AggiungiMittente');

        $proSubMittDest = itaModel::getInstance('proSubMittDest', $this->proSubMittDest);
        $proSubMittDest->DisabilitaDatiConservazione();
    }

    public function CheckDelegaFirmatario($CodiceDes = '', $CodUfficio = '') {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php';
        $proLibDeleghe = new proLibDeleghe();
        /*
         * Controllo Firmatario Delegante:
         * DelegatoFirmatario
         * Solo se è indicato il firmatario.
         */
        if (!$CodiceDes) {
            $CodiceDes = $this->DelegatoFirmatario['CHECKCOD'];
        }
        if ($CodUfficio && $CodiceDes) {
            $DataFir = date('Ymd');
            $Delegato_rec = $proLibDeleghe->CheckSostitutoDelega($CodiceDes, $CodUfficio, $DataFir, proLibDeleghe::DELEFUNZIONE_PROTOCOLLO);
            if ($Delegato_rec) {
                $this->DelegatoFirmatario['CODICE'] = $Delegato_rec['DELEDSTCOD'];
                $this->DelegatoFirmatario['UFFICIO'] = $Delegato_rec['DELEDSTUFF'];
                $this->DelegatoFirmatario['DELEGANTE'] = $CodiceDes;
                $this->DelegatoFirmatario['UFFDELEGANTE'] = $CodUfficio;
                $AnamedFir = $this->proLib->GetAnamed($CodiceDes, 'codice');
                $Anamed_rec = $this->proLib->GetAnamed($this->DelegatoFirmatario['CODICE'], 'codice');
                $this->DelegatoFirmatario['NOMINATIVO'] = $Anamed_rec['MEDNOM'];
                $messaggio = "<div><b><u>FIRMATARIO DEL PROTOCOLLO:</u></b><br><br>";
                $messaggio .= "É presente una Delega per <b>" . $AnamedFir['MEDNOM'] . "</b>";
                $messaggio .= "<br>ed stato indicato <b>{$Anamed_rec['MEDNOM']} </b>come <u>Sostituto/Delegato</u>.<br>";
                $messaggio .= "Il firmatario verrà quindi sostituito. Vuoi confermare l'operazione?</div>";
                Out::msgQuestion("Attenzione", $messaggio, array(
                    'No' => array('id' => $this->nameForm . '_MantieniFirmatario',
                        'model' => $this->nameForm),
                    'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaCambioFirm',
                        'model' => $this->nameForm, 'shortCut' => "f5")
                        )
                );
            }
        }
    }

    public function ControllaPrerequisitiProt() {
        if (!$this->proLib->getUtenteInterno()) {
            Out::msgStop("Attenzione!", "Configurare l'Utente con il Destinatario Interno per poter registrare.");
            return false;
        }
        if ($_POST[$this->nameForm . '_ANAPRO']['PROUOF'] == '') {
            Out::msgStop("Attenzione!", "Configurare l'Utente con l'Ufficio di appartenenza.");
            return false;
        }
        // Se allegati obbligatori, non consente le aggiunta/modifiche del protocollo.
        if ($this->proLibAllegati->CheckObbligoAllegatiProt($this->tipoProt, $_POST[$this->nameForm . '_ANAPRO']['PROUOF'])) {
            if (count($this->proArriAlle) < 1) {
                Out::msgStop("Attenzione", "Allegati obbligatori per il protocollo. Occorre caricare almeno un allegato prima di poter procedere.");
                return false;
            }
        }
        /*
         * Controllo su protocollo collegato solo se sto inserendo un protocollo o se anapro non è bloccato
         */
        if (!$this->anapro_record || ($this->anapro_record && $this->bloccareSeInviato($this->anapro_record) !== true )) {
            if (($_POST[$this->nameForm . '_Propre1'] != '' || $_POST[$this->nameForm . '_Propre2'] != '' || $_POST[$this->nameForm . '_ANAPRO']['PROPARPRE'] != '') &&
                    !($_POST[$this->nameForm . '_Propre1'] != '' && $_POST[$this->nameForm . '_Propre2'] != '' && $_POST[$this->nameForm . '_ANAPRO']['PROPARPRE'] != '')) {
                Out::msgStop("Attenzione!", "Compilare tutti i campi del protocollo collegato.");
                Out::setFocus('', $this->nameForm . '_Propre1');
                return false;
            }
        }
        /*
         * Controllo Coerenza date "Arrivato il" e "Data Prot Mittente" con la data di protocollazione
         * PRODAR
         * PRODAS
         * PRODAA
         */
        if ($this->anapro_record) {
            $DataProt = $this->anapro_record['PRODAR'];
        } else {
            $DataProt = $this->workDate;
        }
        if ($_POST[$this->nameForm . '_ANAPRO']['PRODAS']) {
            if ($_POST[$this->nameForm . '_ANAPRO']['PRODAS'] > $DataProt) {
                Out::msgStop("Attenzione!", "La Data del Documento risulta essere maggiore della data di protocollazione.");
                Out::setFocus('', $this->nameForm . '_ANAPRO[PRODAS]');
                return false;
            }
        }
        if ($_POST[$this->nameForm . '_ANAPRO']['PRODAA']) {
            if ($_POST[$this->nameForm . '_ANAPRO']['PRODAA'] > $DataProt) {
                $CampoData = 'Arrivato il';
                if ($this->tipoProt == 'P') {
                    $CampoData = 'Inviato il';
                }
                Out::msgStop("Attenzione!", 'Il campo "' . $CampoData . '" risulta essere maggiore della data di protocollazione.');
                Out::setFocus('', $this->nameForm . '_ANAPRO[PRODAA]');
                return false;
            }
        }

        /*
         * Controllo Titolario: se parametro rende obligatorio
         */
        $anaent_32 = $this->proLib->GetAnaent('32');
        if ($anaent_32['ENTDE2'] == 1) {
            if (!$_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
                Out::msgStop("Attenzione!", 'Titolario obbligatorio, completa i dati prima di procedere.');
                Out::setFocus('', $this->nameForm . '_ANAPRO[PROCAT]');
                return false;
            }
        }
        /*
         * Se non è una "C" il Mitt/Dest è sempre obbligatorio
         */
        if ($this->tipoProt != 'C') {
            if (!$_POST[$this->proSubMittDest . '_ANAPRO']['PRONOM']) {
                $TipoDesc = 'Destinatario';
                if ($this->tipoProt == 'A') {
                    $TipoDesc = 'Mittente';
                }
                Out::msgStop("Attenzione!", $TipoDesc . ' obbligatorio, completa i dati prima di procedere.');
                Out::setFocus('', $this->proSubMittDest . '_ANAPRO[PRONOM]');
                return false;
            }
        }
        /*
         * Funzione di controllo del titolario.
         */
        $proLibTitolario = new proLibTitolario();
        $versione_t = $codice1 = $codice2 = $codice3 = '';
        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
        if ($_POST[$this->nameForm . '_ANAPRO']['PROCAT']) {
            $codice1 = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
        }
        if ($_POST[$this->nameForm . '_Clacod']) {
            $codice2 = $_POST[$this->nameForm . '_Clacod'];
        }
        if ($_POST[$this->nameForm . '_Fascod']) {
            $codice3 = $_POST[$this->nameForm . '_Fascod'];
        }
        if ($codice1 || $codice2 || $codice3) {
            $retCtr = $proLibTitolario->ControllaTitolarioProtocollo($this->prouof, $this->tipoProt, $versione_t, $codice1, $codice2, $codice3);
            if (!$retCtr) {
                Out::msgStop('Errore nel Titolario', $proLibTitolario->getErrMessage());
                Out::setFocus('', $this->nameForm . '_ANAPRO[PROCAT]');
                return false;
            }
        }

        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($anaent_37['ENTDE1'] == '1') {
            //Controllo conformità oggetto
            $retCtrOgg = $this->proLib->ControllaFormatoOggetto($_POST[$this->nameForm . '_Oggetto']);
            if ($retCtrOgg['STATO'] == false) {
                Out::msgStop('Errore nel campo Oggetto', $retCtrOgg['MESSAGGIO']);
                Out::setFocus('', $this->nameForm . '_Oggetto');
                return false;
            }
        }
        // ControlloDestinatariObbligatori
        if (!$this->ControlloDestinatariObbligatori()) {
            Out::msgStop('Attenzione', 'I Destinatari Interni per questo tipo di protocollo sono obbligatori.');
            Out::setFocus('', $this->proSubTrasmissioni . '_Dest_cod');
            return false;
        }

        /*
         * Controllo se ha creato una partenza con tipo doc
         * EFAA o SDIA, non utilizzabili in partenza.
         */
        $anaent_38 = $this->proLib->GetAnaent('38');
        $TipoDoc = $_POST[$this->nameForm . '_ANAPRO']['PROCODTIPODOC'];
        if (($this->tipoProt == 'P' || $this->tipoProt == 'C') && $TipoDoc != '' &&
                ($TipoDoc == $anaent_38['ENTDE1'] || $TipoDoc == $anaent_38['ENTDE3'])) {
            Out::msgStop('Errore Tipo Documento', 'Il tipo documento selezionato non è valido per un protocollo di tipo ' . $this->tipoProt);
            Out::setFocus('', $this->nameForm . '_ANAPRO[PROCODTIPODOC]');
            return false;
        }
        if (($this->tipoProt == 'A' || $this->tipoProt == 'C') && $TipoDoc != '' &&
                ($TipoDoc == $anaent_38['ENTDE4'] || $TipoDoc == $anaent_38['ENTDE2'])) {
            Out::msgStop('Errore Tipo Documento', 'Il tipo documento selezionato non è valido per un protocollo di tipo ' . $this->tipoProt);
            Out::setFocus('', $this->nameForm . '_ANAPRO[PROCODTIPODOC]');
            return false;
        }
        /*
         * Lettura dati da SubForm Trasmissioni
         */
        $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
        $proArriDest = $proSubTrasmissioni->getProArriDest();
        $fl_regWarn = false;
        if ($this->tipoProt == 'A' || $this->tipoProt == 'C') {
            if (count($proArriDest) == 0) {
                $fl_regWarn = true;
            }
        }
        if ($this->tipoProt == 'P') {
            if (!$_POST[$this->nameForm . '_DESCOD']) {
                Out::msgStop("Inserimento Partenza", "Codice Mittente Obbligatorio");
                Out::setFocus('', $this->nameForm . '_DESCOD');
                return false;
            } else {
                $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($_POST[$this->nameForm . '_DESCOD']);
                $ok_ruolo = false;
                foreach ($ruoli as $ruolo) {
                    if ($ruolo['CODICEUFFICIO'] == $_POST[$this->nameForm . '_DESCUF']) {
                        $ok_ruolo = true;
                        break;
                    }
                }
                if (!$ok_ruolo) {
                    Out::msgStop("Inserimento Partenza", "Il firmatario non appartiene all'ufficio selezionato. Controllare.");
                    Out::setFocus('', $this->nameForm . '_DESCOD');
                    return false;
                }
            }
            if (count($proArriDest) == 0 && $_POST[$this->nameForm . '_ANAPRO']['PRONOM'] == '') {
                $fl_regWarn = true;
            }
        }

        $versione_t = $_POST[$this->nameForm . '_ANAPRO']['VERSIONE_T'];
        $catcod = $_POST[$this->nameForm . '_ANAPRO']['PROCAT'];
        $clacod = $_POST[$this->nameForm . '_Clacod'];
        $fascod = $_POST[$this->nameForm . '_Fascod'];
        $tso = false;
        $riservato = false;
        if ($clacod) {
            if ($fascod) {
                $anafas_rec = $this->proLib->GetAnafas($versione_t, $catcod . $clacod . $fascod);
                if ($anafas_rec['FASATTGIUD']) {
                    $tso = true;
                }
                if ($anafas_rec['FASRIS']) {
                    $riservato = true;
                    // return false; // Non dovrebbe servire.
                }
            } else {
                $anacla_rec = $this->proLib->GetAnacla($versione_t, $catcod . $clacod);
                if ($anacla_rec['CLAATTGIUD']) {
                    $tso = true;
                }
                if ($anacla_rec['CLARIS']) {
                    $riservato = true;
                }
            }
        } else {
            $anacat_rec = $this->proLib->GetAnacat($versione_t, $catcod);
            if ($anacat_rec['CATATTGIUD']) {
                $tso = true;
            }
            if ($anacat_rec['CATRIS']) {
                $riservato = true;
            }
        }
        if ($tso === true) {
            $chiedi = true;
            if ($_POST[$this->nameForm . "_ANAPRO"]['ROWID']) {
                $anapro_tso = $this->proLib->GetAnapro($_POST[$this->nameForm . "_ANAPRO"]['ROWID'], 'rowid');
                if ($anapro_tso['PROTSO']) {
                    $chiedi = false;
                }
            }
            if ($chiedi && $this->CtrDatiRichiestiProt['CTRTSO'] !== '1') {
                $this->CtrDatiRichiestiProt['CTRTSO'] = '1';
                Out::msgQuestion("Attenzione.", "Il Titolario fa riferimento a un TSO.<BR>Confermi l'inserimento del protocollo TSO?", array(
                    "F8-Annulla" => array('id' => $this->nameForm . '_AnnullaRegistra',
                        'model' => $this->nameForm, 'shortCut' => "f8"),
                    "F5-Conferma" => array('id' => $this->nameForm . '_ConfermaTSO',
                        'model' => $this->nameForm, 'shortCut' => "f5"),
                        ), true
                );
                return false;
            }
        }
        if ($riservato === true) {
            $chiedi = false;
            if ($_POST[$this->nameForm . '_ANAPRO']['PRORISERVA'] != '1' && $this->CtrDatiRichiestiProt['CTRRIS'] !== '1') {
                $this->CtrDatiRichiestiProt['CTRRIS'] = '1';
                Out::msgQuestion("Attenzione.", "Il Titolario fa riferimento a un Documento Riservato.<BR>Vuoi rendere Riservato il Documento?", array(
                    "F8-Registra" => array('id' => $this->nameForm . '_ContinuaRegistra',
                        'model' => $this->nameForm, 'shortCut' => "f8"),
                    "F5-Metti Riservato" => array('id' => $this->nameForm . '_MettiRiservato',
                        'model' => $this->nameForm, 'shortCut' => "f5"),
                        ), true
                );
                return false;
            }
        }

        if ($this->fileDaPEC && $this->CtrDatiRichiestiProt['CTRMARCAPDF'] !== '1') {
            $checkPdf = false;
            foreach ($this->proArriAlle as $allegato) {
                if (strtolower(pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION)) == 'pdf') {
                    $checkPdf = true;
                }
            }
            if ($checkPdf === true) {
                $anaent_34 = $this->proLib->GetAnaent('34');
                if ($anaent_34['ENTDE6'] == '1') {
                    Out::msgQuestion("Protocollazione da Pec.", "Vuoi Marcare i File Pdf con la Segnatura?", array(
                        "F8-Registra" => array('id' => $this->nameForm . '_ContinuaRegistra',
                            'model' => $this->nameForm, 'shortCut' => "f8"),
                        "F5-Marca i PDF" => array('id' => $this->nameForm . '_MarcaPDF',
                            'model' => $this->nameForm, 'shortCut' => "f5"),
                        "Cambia posizione segnatura e Marca i PDF" => array('id' => $this->nameForm . '_CambiaPosizioneSegnatura',
                            'model' => $this->nameForm)
                            ), true
                    );
                    $this->CtrDatiRichiestiProt['CTRMARCAPDF'] = '1';
                    return false;
                } else if ($anaent_34['ENTDE6'] == '2') {
                    $this->fileDaPEC['MARCA_PDF'] = true;
                }
            }
            $this->CtrDatiRichiestiProt['CTRMARCAPDF'] = '1';
        }
        if ($_POST[$this->nameForm . '_ANAPRO']['ROWID'] > 0 && $DataProt != date('Ymd') && $this->CtrDatiRichiestiProt['CTRINPUTMOD'] !== '1') {
            $valori[] = array(
                'label' => array(
                    'value' => "Motiva la modifica del Protocollo.",
                    'style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                ),
                'id' => $this->nameForm . '_motivazione',
                'name' => $this->nameForm . '_motivazione',
                'type' => 'text',
                'style' => 'margin:2px;width:300px;',
                'value' => '',
                'class' => 'required'
            );
            Out::msgInput(
                    'Aggiorna.', $valori
                    , array(
                'Conferma' => array('id' => $this->nameForm . '_ConfermaInputModifica', 'model' => $this->nameForm)
                    ), $this->nameForm . "_workSpace"
            );
            return false;
        }
        /*
         * Controllo Doppia Fattura In Protocollazione:
         */
        if (!$this->anapro_record && $this->CtrDatiRichiestiProt['CTRFATTURADOPPIA'] != '1' && $this->currObjSdi) {
            if ($this->currObjSdi->isFatturaPA()) {
                $EstrattoMessaggio = $this->currObjSdi->getEstrattoMessaggio();
                $IdentificativoSdI = '';
                foreach ($EstrattoMessaggio as $CampoMessaggio => $Valore) {
                    if ($CampoMessaggio == 'IdentificativoSdI') {
                        $IdentificativoSdI = $Valore;
                        break;
                    }
                }
                if ($IdentificativoSdI) {
                    $proLibTabDag = new proLibTabDag();
                    $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', '', 'IdentificativoSdI', $IdentificativoSdI, false, '', 'MESSAGGIO_SDI');
                    if ($TabDag_rec) {
                        $Anapro_rec = $this->proLib->GetAnapro($TabDag_rec['TDROWIDCLASSE'], 'rowid');
                        $messaggio = "Questa fattura risultà già protocollata con prot. numero : "
                                . substr($Anapro_rec['PRONUM'], 4) . " / " . substr($Anapro_rec['PRONUM'], 0, 4) . ". Confermare la registrazione?";
                        Out::msgQuestion("Protocollo Fattura", $messaggio, array(
                            "F8-Conferma" => array('id' => $this->nameForm . '_ContinuaRegistraFattura',
                                'model' => $this->nameForm, 'shortCut' => "f8"),
                            "F5-Annulla" => array('id' => $this->nameForm . '_AnnullaRegistraFattura',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                ), true
                        );
                        return false;
                    }
                }
            }
        }


        return true;
    }

    public function SalvaDatiPreProtocollazione($anapro_rec) {
        /*
         * Salvataggio Fascicoli
         */
        $Fascicoli = $this->DatiPreProtocollazione['FASCICOLI'];
        foreach ($Fascicoli as $FascicoloDaInserire) {
            $pronumR = substr($FascicoloDaInserire['retKey'], 4, 10);
            $proparR = substr($FascicoloDaInserire['retKey'], 14);

            $this->varAppoggio = array();
            $this->varAppoggio['ROWID'] = $FascicoloDaInserire['ROWID_ANAORG'];

            $fascicolo_rec = $this->proLib->GetAnaorg($FascicoloDaInserire['ROWID_ANAORG'], 'rowid');

            if (!$this->proLibFascicolo->insertDocumentoFascicolo($this, $fascicolo_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $pronumR, $proparR)) {
                Out::msgStop("Attenzione! Fascicolazione.", $this->proLibFascicolo->getErrMessage());
                return false;
            }
        }
        if ($this->DatiPreProtocollazione['MARCATURA_ALLEGATI']) {
            $ElencoAllegati = $this->proLib->caricaAllegatiProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $AllegatiDaMarcare = $this->DatiPreProtocollazione['MARCATURA_ALLEGATI'];
            // Cerco Allegato da marcare
            foreach ($ElencoAllegati as $Allegato) {
                $KeyAllegato = $Allegato['DOCFIL'];
                if ($AllegatiDaMarcare[$KeyAllegato]) {
                    $DocPath = $this->proLibAllegati->GetDocPath($Allegato['ROWID'], false, false, true);
                    $ForcePosSegn = $AllegatiDaMarcare[$KeyAllegato]['POSIZIONE'];
                    $this->proLibAllegati->SegnaDocumento($this, $DocPath['DOCPATH'], $anapro_rec, $Allegato['ROWID'], $ForcePosSegn);
                }
            }
        }
        /*
         * Marcatura degli Allegati:
         */


        return true;
    }

    private function CaricaAllegatiDaProtocolliCollegati($pronum, $propar) {
        $anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);

        $ElencoProtCollegati = proRic::proGetLegamiProt($this->proLib, $this->PROT_DB, $anapro_rec);

        $ElencoAllegati = array();
        foreach ($ElencoProtCollegati as $ProtoCollegato) {
            if ($ProtoCollegato['PRONUM'] == $pronum && $ProtoCollegato['PROPAR'] == $propar) {
                continue;
            }
            $proArriAlle = $this->proLibAllegati->CopiaAllegatiDaProtocollo($ProtoCollegato['PRONUM'], $ProtoCollegato['PROPAR']);
            if ($proArriAlle === false) {
                Out::msgStop("Attenzione", $this->proLibAllegati->getErrMessage());
                return false;
            }
            foreach ($proArriAlle as $Allegato) {
                $ElencoAllegati[] = $Allegato;
            }
        }
        if ($ElencoAllegati) {
            proRic::proSelectAllegati($ElencoAllegati, $this->nameForm, 'returnCopiaAllegatiProt');
        }
    }

    public function CaricaSubFormMittDest() {
        $model = 'proSubMittDest';
        $proSubMittDest = itaFormHelper::innerForm($model, $this->nameForm . '_divMittDestProt');
        $proSubMittDest->setEvent('openform');
        $proSubMittDest->setTipoProt($this->tipoProt);
        $proSubMittDest->setNameFormChiamante($this->nameForm);
        $proSubMittDest->parseEvent();
        $this->proSubMittDest = $proSubMittDest->nameForm;
    }

    public function CaricaSubFormTrasmissioni() {
        $model = 'proSubTrasmissioni';
        $proSubTrasmissioni = itaFormHelper::innerForm($model, $this->nameForm . '_divTrasmissioniInterne');
        $proSubTrasmissioni->setEvent('openform');
        $proSubTrasmissioni->setTipoProt($this->tipoProt);
        $proSubTrasmissioni->setNameFormChiamante($this->nameForm);
        $proSubTrasmissioni->parseEvent();
        $this->proSubTrasmissioni = $proSubTrasmissioni->nameForm;
    }

    public function Dettaglio($indice) {
        $this->Modifica($indice);
    }

    public function CheckUtilizzoDocAllaFirma() {
        if ($this->tipoProt == 'P' || $this->tipoProt == 'C') {
            $descDoc = 'Protocollo in Partenza';
            if ($this->tipoProt == 'C') {
                $descDoc = 'Documento Formale';
            }
            $anaent_55 = $this->proLib->GetAnaent('55');
            if ($anaent_55['ENTDE2']) {
                //$messaggio = 'Attenzione duplicando un ' . $descDoc . ' non saranno disponibili le funzioni di <b>Firma</span></b> e <b>Metti alla Firma</b>.';
                $messaggio = 'Attenzione creando un ' . $descDoc . ' non saranno disponibili le funzioni di <b>Firma</span></b> e <b>Metti alla Firma</b>.';
                Out::msgQuestion("Nuovo Protocollo", $messaggio, array(
                    'Continua' => array('id' => $this->nameForm . '_ContinuaProt',
                        'model' => $this->nameForm),
                    'Crea Documento alla Firma' => array('id' => $this->nameForm . '_CreaDocAllaFirma',
                        'model' => $this->nameForm)
                        )
                );
            }
        }
    }

    public function ChiediInserimentoEstensione() {
        $valori[] = array(
            'label' => array(
                'value' => "<b>Estensione:</b>",
                'style' => 'margin-top:10px;width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_ESTENSIONEFILE',
            'name' => $this->nameForm . '_ESTENSIONEFILE',
            'type' => 'text',
            'class' => 'ita-edit',
            'style' => 'margin-top:10px;width:65px;',
            'value' => ''
        );
        $messaggio = "Una volta inserita l'estensione <b>non</b> potrà più essere cambiata.";
        Out::msgInput(
                'Estensione File', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaEstensione', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaEstensione', 'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace", 'auto', '350', true, "<span style=\"font-size:1.1em;\">$messaggio</span>"
        );
    }

}
