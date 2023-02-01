<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function proGestSottoFascicolo() {
    $proGestSottoFascicolo = new proGestSottoFascicolo();
    $proGestSottoFascicolo->parseEvent();
    return;
}

class proGestSottoFascicolo extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibFascicolo;
    public $proLibPratica;
    public $nameForm = "proGestSottoFascicolo";
    public $divGes = "proGestSottoFascicolo_divGestione";
    public $pronumFascicolo;
    public $rowidPropas = array();
    public $UfficioProgesUsato;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->proLibPratica = new proLibPratica();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->rowidPropas = App::$utente->getKey($this->nameForm . '_rowidPropas');
            $this->pronumFascicolo = App::$utente->getKey($this->nameForm . '_pronumFascicolo');
            $this->UfficioProgesUsato = App::$utente->getKey($this->nameForm . '_UfficioProgesUsato');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidPropas', $this->rowidPropas);
            App::$utente->setKey($this->nameForm . '_pronumFascicolo', $this->pronumFascicolo);
            App::$utente->setKey($this->nameForm . '_UfficioProgesUsato', $this->UfficioProgesUsato);
        }
    }

    public function getPronumFascicolo() {
        return $this->pronumFascicolo;
    }

    public function setPronumFascicolo($pronumFascicolo) {
        $this->pronumFascicolo = $pronumFascicolo;
    }

    public function getRowidPropas() {
        return $this->rowidPropas;
    }

    public function setRowidPropas($rowidPropas) {
        $this->rowidPropas = $rowidPropas;
    }

    public function setUfficioProgesUsato($UfficioProgesUsato) {
        $this->UfficioProgesUsato = $UfficioProgesUsato;
    }

    public function getUfficioProgesUsato() {
        return $this->UfficioProgesUsato;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->rowidPropas) {
                    $this->Dettaglio($this->rowidPropas, 'rowid');
                } else {
                    $this->Nuovo();
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'addGridRow':
                break;
            case 'delGridRow':
                break;
            case 'onClickTablePager':
                break;
            case 'printTableToHTML':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $Destinatario = $_POST[$this->nameForm . '_PROPAS']['PRORPA'];
                        $Ufficio = $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'];
                        if (!$this->ControlloDestinatario($Destinatario, $Ufficio)) {
                            break;
                        }
                        $this->Aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $Destinatario = $_POST[$this->nameForm . '_PROPAS']['PRORPA'];
                        $Ufficio = $_POST[$this->nameForm . '_PROPAS']['PROUFFRES'];
                        if (!$this->ControlloDestinatario($Destinatario, $Ufficio)) {
                            break;
                        }
                        $this->Aggiorna();
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

                    case $this->nameForm . '_PROPAS[PRORPA]_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedMittPartenza');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROPAS[PRORPA]':
                        $this->DecodResponsabile($_POST[$this->nameForm . '_PROPAS']['PRORPA']);
                        break;
                }
                break;
            case 'suggest':
                break;

            case 'returnanamedMittPartenza' : $anamed_rec = $this->proLib->GetAnamed($_POST ['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_PROPAS[PRORPA]', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_RESPONSABILE", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_PROPAS[PROUFFRES]", '');
                Out::valore($this->nameForm . "_UFFRESP", '');
                Out::setFocus('', $this->nameForm . "_PROPAS[PRORPA]");
                break;

            case 'returnUfficiPerDestinatarioFirmatario' : $anauff_rec = $this->proLib->GetAnauff($_POST ['retKey'], 'rowid');
                Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_PROPAS[PRORPA]");
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_rowidPropas');
        App::$utente->removeKey($this->nameForm . '_pronumFascicolo');
        App::$utente->removeKey($this->nameForm . '_UfficioProgesUsato');
        Out::closeDialog($this->nameForm);
    }

    public function

    returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out ::hide($this->nameForm . '_Aggiorna');
    }

    public function Dettaglio($rowid, $tipo = 'rowid') {
        Out::clearFields($this->divGes);
        $this->Nascondi();
        $Anapro_rec = $this->proLib->GetAnapro($this->pronumFascicolo, 'codice', 'F');
        $propas_rec = $this->proLibPratica->GetPropas($rowid, $tipo);
        $AnaproSottoFas_rec = $this->proLib->GetAnapro($propas_rec['PASPRO'], 'codice', $propas_rec['PASPAR']);
        // Setto il titolo della form di gestione sottofascicolo.
        list($skip, $sottofascicolo) = explode($AnaproSottoFas_rec['PROFASKEY'] . "-", $AnaproSottoFas_rec['PROSUBKEY']);
        Out::setAppTitle($this->nameForm, "Gestione Sottofascicolo: $sottofascicolo");

        $this->DecodResponsabile($propas_rec['PRORPA'], 'codice', $propas_rec['PROUFFRES']);
        $DescrizioneElemento = '<b>Fascicolo: </b>' . $Anapro_rec['PROFASKEY'];
        $DescrizioneElemento.='<br>';
        $DescrizioneElemento .='<b><span style="color:red">Modifica sottofascicolo.</span></b>';

        $AnaproSottFasSave_tab = $this->proLib->GetAnaproSave($AnaproSottoFas_rec['PRONUM'], $AnaproSottoFas_rec['PROPAR']);
        if ($AnaproSottFasSave_tab) {
            $UtenteCrea = $AnaproSottFasSave_tab[0]['PROUTE'];
            $UffCrea = $AnaproSottFasSave_tab[0]['PROUOF'];
        } else {
            $UtenteCrea = $AnaproSottoFas_rec['PROUTE'];
            $UffCrea = $AnaproSottoFas_rec['PROUOF'];
        }
        $anauffCrea_rec = $this->proLib->GetAnauff($UffCrea);
        $CreatoDa = '<b>Creato da:</b> ' . $UtenteCrea . " - " . $anauffCrea_rec['UFFDES'] . '<br>';
        $anauffMod_rec = $this->proLib->GetAnauff($AnaproSottoFas_rec['PROUOF']);
        $Modificato = '<b>Ultima Mod:</b> ' . $AnaproSottoFas_rec['PROUTE'] . " - " . $anauffMod_rec['UFFDES'] . '<br>';

        $Messaggio = '<div style="display:inline-block;">' . $DescrizioneElemento . '</div>';
        $Messaggio.= '<div style="display:inline-block; position:absolute; right:10px; color:blue">' . $CreatoDa . $Modificato . '</div>';

        Out::valori($propas_rec, $this->nameForm . '_PROPAS');
        Out::show($this->nameForm . '_Aggiorna');

        Out::html($this->nameForm . '_DESCRIZIONE', $Messaggio);
        /*
         * utilizzo geskey per vedere i peremssi del fascicolo
         */
        $pronumSottoFascicolo = $AnaproSottoFas_rec['PRONUM'];
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $Anapro_rec['PROFASKEY'], 'geskey', $pronumSottoFascicolo);
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
            Out::disableField($this->nameForm . '_PROPAS[PRODPA]');
            Out::disableField($this->nameForm . '_PROPAS[PRORPA]');
            Out::disableField($this->nameForm . '_UFFRESP');
            Out::hide($this->nameForm . '_Aggiorna');
        } else {
            Out::enableField($this->nameForm . '_PROPAS[PRODPA]');
            Out::enableField($this->nameForm . '_PROPAS[PRORPA]');
            Out::enableField($this->nameForm . '_UFFRESP');
            Out::show($this->nameForm . '_Aggiorna');
        }
        $ProGes_rec = $this->proLib->GetProges($Anapro_rec['PROFASKEY'], 'geskey');
        if ($ProGes_rec['GESDCH']) {
            Out::disableField($this->nameForm . '_PROPAS[PRORPA]');
            Out::disableField($this->nameForm . '_UFFRESP');
            Out::disableField($this->nameForm . '_PROPAS[PRODPA]');
            Out::hide($this->nameForm . '_Aggiorna');
        }
    }

    public function Nuovo() {
        Out::clearFields($this->divGes);
        $this->Nascondi();
        $Anapro_rec = $this->proLib->GetAnapro($this->pronumFascicolo, 'codice', 'F');
        $DescrizioneElemento = '<b>Fascicolo: </b>' . $Anapro_rec ['PROFASKEY'];
        $DescrizioneElemento.='<br>';
        $DescrizioneElemento .='<b><span style="color:red">Aggiunta nuovo sottofascicolo.</span></b>';
        /* Valorizzo il responsabile dal padre fascicolo: */
        $Proges_rec = $this->proLib->GetProges($Anapro_rec['PROFASKEY'], 'geskey');
        $this->DecodResponsabile($Proges_rec['GESRES'], 'codice', $Proges_rec['GESPROUFF']);

        Out::html($this->nameForm . '_DESCRIZIONE', $DescrizioneElemento);
        Out::show($this->nameForm . '_Aggiungi');
        Out::setFocus($this->nameForm, $this->nameForm . '_PROPAS[PRODPA]');
    }

    public function Aggiungi() {
        $Dati = array();
        $Dati['EVENTO'] = 'Aggiungi';
        $Dati['PROPAS_REC'] = $_POST[$this->nameForm . '_PROPAS'];
        $Dati['UFFICIO'] = $this->UfficioProgesUsato;
        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setReturnData($Dati);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
    }

    public function Aggiorna() {
        $Dati = array();
        $Dati['EVENTO'] = 'Aggiorna';
        $Dati['PROPAS_REC'] = $_POST[$this->nameForm . '_PROPAS'];
        $Dati['UFFICIO'] = $this->UfficioProgesUsato;
        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setReturnData($Dati);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
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
                    $sql = "SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0 AND UFFDES.UFFCOD = '$uffcod'";
                    $uffdesTest_rec = $this->proLib->getGenericTab($sql, false);
                    // Se fa parte dell'ufficio scelto lo decodifico, altrimenti propongo la scelta.
                    if ($uffdesTest_rec) {
                        $anauff_rec = $this->proLib->GetAnauff($uffcod);
                        Out::valore($this->nameForm . '_PROPAS[PROUFFRES]', $anauff_rec['UFFCOD']);
                        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                        return;
                    }
                }
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
        } else {
            Out::valore($this->nameForm . '_PROPAS[PRORPA]', '');
            Out::valore($this->nameForm . '_RESPONSABILE', '');
        }
        Out::setFocus('', $this->nameForm . "_RESPONSABILE");
    }

    private function ControlloDestinatario($Destinatario, $Ufficio) {
        $ret = $this->proLib->ControlloAssociazioneUtenteUfficio($Destinatario, $Ufficio, 'responsabile');
        if (!$ret) {
            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
            return false;
        }
        return true;
    }

}

?>
