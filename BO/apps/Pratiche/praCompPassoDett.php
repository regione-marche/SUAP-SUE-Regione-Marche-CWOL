<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoDatiPrincipali.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoDestinatari.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoCaratteristiche.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoComunicazione.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoArticoli.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSubTrasmissioni.php';

include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function praCompPassoDett() {
    $praCompPassoDett = new praCompPassoDett();
    $praCompPassoDett->parseEvent();
    return;
}

class praCompPassoDett extends itaModel {

    public $nameForm = 'praCompPassoDett';
    private $arrSubFormObj = array();
    private $arrSubFormNames = array();
    private $currGesnum;
    private $keyPasso;
    private $praLib;

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();
        $this->praLib = new praLib;
        $this->paneDati = $this->nameForm . '_paneDati';
        $this->paneDestinatari = $this->nameForm . '_paneDestinatari';
        $this->paneCaratteristiche = $this->nameForm . '_paneCaratteristiche';
        $this->paneComunicazione = $this->nameForm . '_paneComunicazione';
        $this->paneNote = $this->nameForm . '_paneNote';
        $this->paneArticoli = $this->nameForm . '_paneArticoli';
        $this->paneAssegnazioniPassi = $this->nameForm . '_paneAssegnazioniPassi';
        $this->paneDatiAggiuntivi = $this->nameForm . '_paneDatiAggiuntivi';
        $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
        $this->arrSubFormNames = unserialize(App::$utente->getKey($this->nameForm . '_arrSubFormNames'));
        $this->arrSubFormObj = array();
        if ($this->arrSubFormNames) {
            foreach ($this->arrSubFormNames as $formName) {
                $subFormObj = itaModel::getInstance($formName['nameFormOrig'], $formName['nameForm']);
                $this->arrSubFormObj[$formName['panel']] = $subFormObj;
            }
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
            App::$utente->setKey($this->nameForm . '_arrSubFormNames', serialize($this->arrSubFormNames));
        }
    }

    /**
     * 
     * @param type $subformName
     * @return type
     */
    public function getArrSubFormObj($subformName = null) {
        if ($subformName == null) {
            return $this->arrSubFormObj;
        } elseif (isset($this->arrSubFormObj[$subformName])) {
            return $this->arrSubFormObj[$subformName];
        } else {
            return null;
        }
    }

    public function getKeyPasso() {
        return $this->keyPasso;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
//     SIMONE
                $this->currGesnum = '2018000016';
                $this->keyPasso = '2018000016154281294861';
//     MICHELE
//                $this->currGesnum = '2018000060';
//                $this->keyPasso = '2018000060154478922935';
                $this->caricaSubForm();
                break;

            case 'onClick':

                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_arrSubFormNames');
    }

    public function returnToParent($propak, $close = true) {
        if ($close) {
            $this->close();
        }

        /**
         * Momentaneamnte non attivato
         */
//        $model = $this->returnModel;
//        /* @var $objModel itaModel */
//        $objModel = itaModel::getInstance($model);
//        $objModel->setEvent($this->returnEvent);
//        $objModel->setFormData($propak);
//        $objModel->parseEvent();
    }

    /**
     * 
     */
    private function caricaSubForm() {

        $this->arrSubFormObj = array();

        foreach (praLibPasso::$PANEL_LIST as $key => $panel) {
            if (!$panel[praLibPasso::PANEL_FIELD_SUB_FORM]) {
                continue;
            }
            /* @var $subFormObj praSubPasso */
            $subFormObj = itaFormHelper::innerForm($panel[praLibPasso::PANEL_FIELD_SUB_FORM], $this->nameForm . '_' . $panel[praLibPasso::PANEL_ID_ELEMENT]);
            $subFormObj->setEvent('openform');
            $subFormObj->setReturnModel($this->nameForm);
            $subFormObj->setReturnModelOrig($this->nameFormOrig);
            $subFormObj->setReturnEvent("returnFrom{$panel[praLibPasso::PANEL_FIELD_SUB_FORM]}");
            $subFormObj->setCurrGesnum($this->currGesnum);
            $subFormObj->setKeyPasso($this->keyPasso);
            $subFormObj->parseEvent();
            $this->arrSubFormNames[$panel[praLibPasso::PANEL_FIELD_SUB_FORM]] = array(
                "panel" => $panel[praLibPasso::PANEL_FIELD_SUB_FORM],
                "nameForm" => $subFormObj->getNameForm(),
                "nameFormOrig" => $subFormObj->getNameFormOrig()
            );
        }
    }

    public function Dettaglio($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();
        $propas_rec = $this->praLib->GetPropas($rowid, $tipo);
        $this->currGesnum = $propas_rec['PRONUM'];
        $this->keyPasso = $propas_rec['PROPAK'];

        $arrayStatiTab = $this->praLibPasso->setStatiTabPasso($propas_rec);
        $this->caricaTabPassi($arrayStatiTab);

        /*
         * QUI DEVE ESSERE INVOCATO IL DETTAGLIO DEL SINGOLO MODULO
         * 
         */
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDati");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCaratteristiche");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneDatiAggiuntivi");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneNote");
        Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAssegnazioniPassi");


        $this->attivaPaneArticoli($propas_rec['PROPART']);

        $this->caricaSubForms($propas_rec['PRODAT'] ? 'praCompDatiAggiuntiviForm' : 'praCompDatiAggiuntivi', $perms);
        /* @var $praCompDatiAggiuntivi praCompDatiAggiuntivi */
        $praCompDatiAggiuntivi = itaModel::getInstance($this->praCompDatiAggiuntiviFormname[0], $this->praCompDatiAggiuntiviFormname[1]);
        $praCompDatiAggiuntivi->openGestione($this->currGesnum, $this->keyPasso);

        /*
         * Gestione Tab Passo
         */


        /*
         * Rileggo le trasmissioni. Se passo già esistente senza ANAPRO, lo inserisco se il parametro è attivo
         */
        if ($this->flagAssegnazioniPasso) {
            $proSubTrasmissioni = itaModel::getInstance('proSubTrasmissioni', $this->proSubTrasmissioni);
            $proSubTrasmissioni->setTipoProt(praLibAssegnazionePassi::TYPE_ASSEGNAZIONI);
            $anapro_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], "codice", $propas_rec['PASPAR']);
            if (!$anapro_rec) {
                $retInsertAnapro = $this->inserisciAnapro($this->keyPasso);
                if ($retInsertAnapro['Status'] == "-1") {
                    Out::msgStop("Errore in Aggiornamento", $retInsertAnapro['Status'] . " per il passo " . $propas_rec['PROPAK']);
                    return false;
                }
                $anapro_rec = $retInsertAnapro['anapro_rec'];
            }
            $proSubTrasmissioni->setAnapro_rec($anapro_rec);
            $proSubTrasmissioni->Modifica();
            $htmlTabAss = "Assegnazioni <span style=\"color:red;\"><b>(" . count($proSubTrasmissioni->getProArriDest()) . ")</b></span>";
            Out::tabSetTitle($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneAssegnazioniPassi", $htmlTabAss);
            if ($this->flagAssegnazioniPasso && $this->daTrasmissioni == false) {
                Out::show($this->nameForm . "_Trasmissioni");
            }

            /*
             * Cerco se il passo è assegnato all'utente loggato e se lo stesso lo ha in gestione
             */
            foreach ($proSubTrasmissioni->getProArriDest() as $dest) {
                $inGestione = false;
                $ananom_rec = $this->praLib->GetAnanom($this->profilo['COD_ANANOM']);
                if ($dest['DESCOD'] == $this->profilo['COD_SOGGETTO'] || $dest['DESCOD'] == $ananom_rec['NOMDEP']) {
                    if ($dest['DESGES'] == 1) {
                        $inGestione = true;
                        break;
                    }
                }
            }
        }


        if ($inGestione == false) {
            if ($visibilitaPasso != "Aperto") {
                $proges_rec = $this->praLib->GetProges($this->currGesnum);
                if (!$this->praPerms->checkSuperUser($proges_rec)) {
                    $perms = $this->praPerms->impostaPermessiPasso($propas_rec);
                    Out::checkDataButton($this->nameForm, $perms);
                    if ($this->praPerms->checkUtenteGenerico($propas_rec)) {
                        Out::attributo($this->nameForm . "_PROPAS[PROVISIBILITA]", "disabled");
                        Out::hide($this->nameForm . "_Apri");
                        Out::hide($this->nameForm . "_SbloccaCom");
                        Out::hide($this->nameForm . "_Invia");
                        Out::hide($this->nameForm . "_Risposta");
                        Out::hide($this->gridAllegati . "_delGridRow");
                        Out::hide($this->gridAllegati . "_addGridRow");
                        Out::hide($this->nameForm . "_divBottoniAllega");
                        Out::hide($this->nameForm . "_ProtocollaPartenza");
                        Out::hide($this->nameForm . "_PROTRIC_DESTINATARIO_butt");
                        Out::hide($this->nameForm . "_RimuoviProtocollaP");
                        Out::hide($this->nameForm . "_ProtocollaArrivo");
                        Out::hide($this->nameForm . "_RimuoviProtocollaA");
                        Out::hide($this->nameForm . "_PROTRIC_MITTENTE_butt");
                        Out::hide($this->nameForm . '_CreaNuovoPasso');
                        Out::hide($this->nameForm . '_NuovaComDaProt');
                    }
                }
            }
        }

        if ($_POST['daCommercio'] == true) {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCaratteristiche");
        }
        if ($_POST['daComunicazione'] == true) {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        }
        if ($this->returnModel == "praArticoli") {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
        } else if ($this->returnModel == "praComunicazioni") {
            Out::tabSelect($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneCom");
        }

        if ($propas_rec['PROKPRE']) {
            $this->DecodVaialpasso($propas_rec['PROKPRE'], "propak", "ANTECEDENTE");
        }
        if ($propas_rec['PROCOMDEST']) {
            Out::show($this->nameForm . "_NuoviPassiDaDest");
        }

        /*
         * Decodifico il calendario
         */
        $idCalendar = $this->praLib->DecodCalendar($propas_rec['ROWID'], "PASSI_SUAP");
        Out::valore($this->nameForm . "_Calendario", "");
        if ($idCalendar) {
            Out::valore($this->nameForm . "_Calendario", $idCalendar);
        }

//Nascondo la lentina antecedente se il passo risulta già antecedente di qualcun'altro
        $propas_tab_collegati = $this->praLib->GetPropas($propas_rec['PROPAK'], "prokpre", true);
        if ($propas_tab_collegati) {
            Out::hide($this->nameForm . "_SEQANTECEDENTE_butt");
        }

        $this->valorizzaInfoDestinatari($propas_rec);

        Out::setFocus('', $this->nameForm . '_PROPAS[PROANN]');
        Out::attributo($this->nameForm . '_SEQANTECEDENTE', "readonly", '0');
        Out::show($this->nameForm . '_SEQANTECEDENTE_butt');

        if ($this->praReadOnly == true) {
            $this->HideButton();
        }

        if (!$this->ValorizzaProall()) {
            Out::msgStop("Aggiornamento", "Errore aggiornamento campo PROALL");
            return false;
        }
    }

    private function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divGes);
    }

    private function Nascondi() {
        
    }

    /**
     * 
     * @param type $arrayStatiTab
     */
    public function caricaTabPassi($arrayStatiTab) {
        foreach ($arrayStatiTab as $panel) {
            switch ($panel['Stato']) {
                case "Show":
                    Out::showTab($this->nameForm . "_" . $panel['Id']);
                    break;
                case "Hide":
                    Out::hideTab($this->nameForm . "_" . $panel['Id']);
                    break;
                case "Add":
                    $generator = new itaGenerator();
                    $retHtml = $generator->getModelHTML(basename($panel['FileXml'], ".xml"), false, $this->nameForm, false);
                    Out::tabAdd($this->nameForm . '_tabProcedimento', '', $retHtml);
                    break;
                case "Remove":
                    Out::tabRemove($this->nameForm . '_tabProcedimento', $this->nameForm . "_" . basename($panel['FileXml'], ".xml"));
                    break;
                default:
                    break;
            }
        }

        foreach ($arrayStatiTab as $panel) {
            switch ($panel['Flag']) {
                case "On":
                    Out::show($this->nameForm . "_" . $panel['IdFlag'] . "_field");
                    break;
                case "Off":
                    Out::hide($this->nameForm . "_" . $panel['IdFlag'] . "_field");
                    break;
            }
        }
    }

    /**
     * 
     * @param type $flag
     */
    public function attivaPaneArticoli($flag) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        if ($flag == 1) {
            Out::tabEnable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
            Out::showTab($this->nameForm . "_paneArticoli");
            $this->arrSubFormObj['praSubPassoArticoli']->setTitoloArticolo($this->nameForm . "_PROPAS[PROPTIT]", $propas_rec['PRODPA']);
            Out::valore($this->nameForm . "_PROPAS[PROPTIT]", $propas_rec['PRODPA']);
        } else {
            Out::tabDisable($this->nameForm . "_tabProcedimento", $this->nameForm . "_paneArticoli");
            Out::hideTab($this->nameForm . "_paneArticoli");
            $this->arrSubFormObj['praSubPassoArticoli']->setTitoloArticolo('');
        }
    }

    public function abilitaAllegatiAllapubblicazione() {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        $this->arrSubFormObj['praSubPassoCaratteristiche']->abilitaAllegatiAllaPubblicazione($propas_rec['PROPUBALL']);
    }

    public function abilitaAllegatiAllaConferenza($processoIniziato) {
        $propas_rec = $this->praLib->GetPropas($this->keyPasso, "propak");
        /*
         * Blocco lo spostamento dei destinatari se il processo di firma è già iniziato
         */
        if ($processoIniziato === true) {
            Out::msgInfo("Conferenza di Servizi", "Impossibile modificare la spunta CDS.<br>Il Processo di firma è già iniziato.");
            $this->arrSubFormObj['praSubPassoArticoli']->setProFlcDs($propas_rec['PROFLCDS']);
        } else {
            $this->arrSubFormObj['praSubPassoCaratteristiche']->abilitaAllegatiAllaConferenzaServizi($_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
        }
    }

    public function getDestinatari(){
        
        //Out::msgInfo("Valore", print_r($this->arrSubFormObj['praSubPassoDestinatari'], true));

        if ($this->arrSubFormObj['praSubPassoDestinatari'] != null){
            return $this->arrSubFormObj['praSubPassoDestinatari']->getDestinatari();
        }
        else return null;
    }
    
}
