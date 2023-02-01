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

function proAssegnazioniFascicolo() {
    $proAssegnazioniFascicolo = new proAssegnazioniFascicolo();
    $proAssegnazioniFascicolo->parseEvent();
    return;
}

class proAssegnazioniFascicolo extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibFascicolo;
    public $proLibPratica;
    public $nameForm = "proAssegnazioniFascicolo";
    public $divRis = "proAssegnazioniFascicolo_divGestione";
    public $gridAssegnazioni = "proAssegnazioniFascicolo_gridAssegnazioni";
    public $pronumFascicolo;
    public $pronumSottoFascicolo;
    public $Anapro_rec = array();
    public $appoggio;
    public $proAssegnazioni = array();
    public $UfficioProgesUsato;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->proLibPratica = new proLibPratica();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->proAssegnazioni = App::$utente->getKey($this->nameForm . '_proAssegnazioni');
            $this->Anapro_rec = App::$utente->getKey($this->nameForm . '_Anapro_rec');
            $this->pronumFascicolo = App::$utente->getKey($this->nameForm . '_pronumFascicolo');
            $this->pronumSottoFascicolo = App::$utente->getKey($this->nameForm . '_pronumSottoFascicolo');
            $this->appoggio = App::$utente->getKey($this->nameForm . '_appoggio');
            $this->UfficioProgesUsato = App::$utente->getKey($this->nameForm . '_UfficioProgesUsato');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proAssegnazioni', $this->proAssegnazioni);
            App::$utente->setKey($this->nameForm . '_Anapro_rec', $this->Anapro_rec);
            App::$utente->setKey($this->nameForm . '_pronumFascicolo', $this->pronumFascicolo);
            App::$utente->setKey($this->nameForm . '_pronumSottoFascicolo', $this->pronumSottoFascicolo);
            App::$utente->setKey($this->nameForm . '_appoggio', $this->appoggio);
            App::$utente->setKey($this->nameForm . '_UfficioProgesUsato', $this->UfficioProgesUsato);
        }
    }

    public function getPronumFascicolo() {
        return $this->pronumFascicolo;
    }

    public function setPronumFascicolo($pronumFascicolo) {
        $this->pronumFascicolo = $pronumFascicolo;
    }

    public function getPronumSottoFascicolo() {
        return $this->pronumSottoFascicolo;
    }

    public function setPronumSottoFascicolo($pronumSottoFascicolo) {
        $this->pronumSottoFascicolo = $pronumSottoFascicolo;
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
                $generator = new itaGenerator();
                $retHtml = $generator->getModelHTML('proDivAssegnazioni', false, $this->nameForm, true);
                Out::html($this->nameForm . '_divAssegnazioni', $retHtml);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'addGridRow':
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAssegnazioni:
                        if (!$this->ControlloPermessiGestioneVisibilita()) {
                            Out::msgStop("Attenzione", "Non hai il permesso di Gestire Trasmissioni e Visibilità.");
                            break;
                        }
                        $rowid = $_POST[$this->gridAssegnazioni]['gridParam']['selarrrow'];
                        $Arcite_rec = $this->proLib->GetArcite($rowid, 'itekey');
                        if ($Arcite_rec['ITENODO'] != 'ASF') {
                            $DescNodo = 'Estensione di Visibilità';
                        } else {
                            $DescNodo = 'Trasmissione';
                        }
                        if ($Arcite_rec['ITENODO'] != 'ASF' && $Arcite_rec['ITENODO'] != 'TRX') {
                            Out::msgStop("Attenzione", "Soltanto le Trasmissioni e le Estensioni di visibilità possono essere annullate.");
                            break;
                        }
                        if ($Arcite_rec['ITEANNULLATO']) {
                            Out::msgStop("Attenzione", "Non puoi annullare una $DescNodo già annullata.");
                            break;
                        }
                        Out::msgQuestion("Annulla", "Confermi di voler annullare questa $DescNodo?", array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaAnnullaTrasm', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAnnullaTrasm', 'model' => $this->nameForm)
                                )
                        );
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAssegnazioni:
                        $this->CaricaAssegnazioniIter($this->pronumFascicolo, $this->pronumSottoFascicolo);
                        $this->CaricaGriglia($this->gridAssegnazioni, $this->proAssegnazioni);
                        break;
                }
                break;
            case 'printTableToHTML':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Estendi':
                        if (!$this->ControlloPermessiGestioneVisibilita()) {
                            Out::msgStop("Attenzione", "Non hai il permesso di Gestire Trasmissioni e Visibilità.");
                            break;
                        }
                        $this->EstendiVisibilita();
                        break;
                    case $this->nameForm . '_Trasmetti':
                        if (!$this->ControlloPermessiGestioneVisibilita()) {
                            Out::msgStop("Attenzione", "Non hai il permesso di Gestire Trasmissioni e Visibilità.");
                            break;
                        }
                        $this->Trasmetti();
                        break;
                    case $this->nameForm . '_Dest_cod_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedDestinatario');
                        break;

                    case $this->nameForm . '_Uff_cod_butt':
                        $codice = $_POST[$this->nameForm . '_Dest_cod'];
                        if ($codice) {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', '');
                        } else {
                            //Ric  uffici:
                            proRic::proRicAnauff($this->nameForm);
                        }
                        break;

                    case $this->nameForm . '_ConfermaAnnullaTrasm':
                        $rowid = $_POST[$this->gridAssegnazioni]['gridParam']['selarrrow'];
                        $this->AnnullaTrasmissione($rowid, 'itekey');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_cod':
                        $this->DecodificaDestinatario($_POST[$this->nameForm . '_Dest_cod']);
                        break;

                    case $this->nameForm . '_Uff_cod':
                        $codice = $_POST[$this->nameForm . '_Uff_cod'];
//                        if (!$_POST[$this->nameForm . '_Dest_cod'] && $codice) {
//                            Out::valore($this->nameForm . '_Uff_cod', '');
//                            Out::valore($this->nameForm . '_Uff_des', '');
//                            Out::msgInfo("Attenzione", "Indicare prima il Destinatario.");
//                            break;
//                        } else {
                        if (trim($codice)) {
                            $codice = str_pad($codice, 4, '0', STR_PAD_LEFT);
                            $anauff_rec = $this->proLib->GetAnauff($codice, 'codice');
                            if ($anauff_rec) {
                                Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                            } else {
                                Out::valore($this->nameForm . '_Uff_cod', '');
                                Out::valore($this->nameForm . '_Uff_des', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_Uff_cod', '');
                            Out::valore($this->nameForm . '_Uff_des', '');
                        }
//                        }
                        break;
                }
                break;

            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_nome':
                        /* new suggest */
                        $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAMED WHERE MEDANN<>1 AND " . $where . " AND MEDANN=0 AND $filtroUff ";
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
                        foreach ($anauff_tab as $anauff_rec) {
                            itaSuggest::addSuggest($anauff_rec['UFFDES'], array($this->nameForm . "_Uff_cod" => $anauff_rec['UFFCOD']));
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'returnUfficiPerDestinatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                if ($this->appoggio) {
                    $anamed_rec = $this->proLib->GetAnamed($this->appoggio, 'codice', 'no');
                    Out::valore($this->nameForm . '_Dest_cod', $anamed_rec['MEDCOD']);
                    Out::valore($this->nameForm . '_Dest_nome', $anamed_rec['MEDNOM']);
                    $this->appoggio = '';
                }
                Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                break;

            case 'returnanamedDestinatario':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_Dest_cod', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_Dest_nome", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_Uff_cod", '');
                Out::valore($this->nameForm . "_Uff_des", '');
                $uffdes_tab = $this->proLib->GetUfficiAnamed($anamed_rec['MEDCOD']);
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                } else {
                    $this->appoggio = $anamed_rec['MEDCOD'];
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '');
                }
                break;

            case 'returnanauff':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_proAssegnazioni');
        App::$utente->removeKey($this->nameForm . '_Anapro_rec');
        App::$utente->removeKey($this->nameForm . '_pronumFascicolo');
        App::$utente->removeKey($this->nameForm . '_pronumSottoFascicolo');
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_UfficioProgesUsato');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function Nascondi() {
        //Out::hide($this->nameForm . '_divCampiAssegnazione');
    }

    public function Dettaglio() {
        $this->Nascondi();
        if ($this->pronumSottoFascicolo) {
            $Anapro_rec = $this->proLib->GetAnapro($this->pronumSottoFascicolo, 'codice', 'N');
            $Descrizione = '<b>Sottofascicolo: </b> ' . $Anapro_rec['PROSUBKEY'];
        } else {
            $Anapro_rec = $this->proLib->GetAnapro($this->pronumFascicolo, 'codice', 'F');
            $Descrizione = '<b>Fascicolo: </b>' . $Anapro_rec['PROFASKEY'];
        }
        $this->Anapro_rec = $Anapro_rec;
        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $Descrizione.='<br><br>';
        $Descrizione.=$Anaogg_rec['OGGOGG'];
        Out::html($this->nameForm . '_DESCRIZIONE', $Descrizione);

        $this->CaricaAssegnazioniIter($this->pronumFascicolo, $this->pronumSottoFascicolo);
        $this->CaricaGriglia($this->gridAssegnazioni, $this->proAssegnazioni);
        // Controllo se 
        $ProGes_rec = $this->proLib->GetProges($Anapro_rec['PROFASKEY'], 'geskey');
        $retPermGestVis = $this->ControlloPermessiGestioneVisibilita();
        if ($ProGes_rec['GESDCH'] || !$retPermGestVis) {
            Out::hide($this->nameForm . '_divCampiAssegnazione');
            Out::hide($this->nameForm . '_Estendi');
            Out::hide($this->nameForm . '_Trasmetti');
            Out::hide($this->gridAssegnazioni . '_delGridRow');
        }
    }

    private function CaricaAssegnazioniIter($fascicolo, $sottofascicolo = '') {
        $this->proAssegnazioni = $this->proLibFascicolo->CaricaAssegnazioniFascicolo($fascicolo, $sottofascicolo);
//        foreach ($this->proAssegnazioni as $key => $assegnazione) {
//            $this->proAssegnazioni[$key]['ITERANNOTAZIONI'] = '<div class="ita-Wordwrap">' . $assegnazione['ITERANNOTAZIONI'] . '</div>';
//        }
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1') {
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
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(100000);
        }
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    private function DecodificaDestinatario($codice, $tipo = 'codice') {
        Out::valore($this->nameForm . "_Dest_cod", "");
        Out::valore($this->nameForm . '_Dest_nome', "");
        Out::valore($this->nameForm . "_Uff_cod", "");
        Out::valore($this->nameForm . '_Uff_des', "");
        if (trim($codice) != "") {
            if ($tipo == 'codice') {
                if (is_numeric($codice)) {
                    $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                }
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no', false, true);
            if ($anamed_rec) {
                $uffdes_tab = $this->proLib->GetUfficiAnamed($codice);
                if (count($uffdes_tab) == 1) {
                    Out::valore($this->nameForm . '_Dest_cod', $anamed_rec['MEDCOD']);
                    Out::valore($this->nameForm . '_Dest_nome', $anamed_rec['MEDNOM']);
                    Out::valore($this->nameForm . '_Uff_cod', $uffdes_tab[0]['UFFCOD']);
                    Out::valore($this->nameForm . '_Uff_des', $uffdes_tab[0]['UFFDES']);
                } else {
                    $this->appoggio = $codice;
                    Out::valore($this->nameForm . '_Dest_cod', $anamed_rec['MEDCOD']);
                    Out::valore($this->nameForm . '_Dest_nome', $anamed_rec['MEDNOM']);
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', $codice);
                }
            }
        }
    }

    private function ControlloPermessiGestioneVisibilita() {
        $pronumSottoFascicolo = '';
        if ($this->pronumSottoFascicolo) {
            $pronumSottoFascicolo = $this->pronumSottoFascicolo;
        }
        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $this->Anapro_rec['PROFASKEY'], 'codice', $pronumSottoFascicolo);
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_VISIBILITA]) {
            return false;
        }
        return true;
    }

    private function ControlloDestinatario() {
        $ret = $this->proLib->ControlloAssociazioneUtenteUfficio($_POST[$this->nameForm . '_Dest_cod'], $_POST[$this->nameForm . '_Uff_cod']);
        if (!$ret) {
            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
            return false;
        }
        return true;
    }

    private function EstendiVisibilita() {
        if (!$this->ControlloDestinatario()) {
            return false;
        }

        $anapro_rec = $this->Anapro_rec;
        $Destinatario = $_POST[$this->nameForm . '_Dest_cod'];
        $Ufficio = $_POST[$this->nameForm . '_Uff_cod'];
        $Gestione = $_POST[$this->nameForm . '_Gestione'];
        $Annotazioni = $_POST[$this->nameForm . '_Annotazioni'];
        $DescrizioneFN = 'FASCICOLO';
        $NumeroFN = $anapro_rec['PROFASKEY'];
        if ($this->pronumSottoFascicolo) {
            $DescrizioneFN = 'SOTTOFASCICOLO';
            $NumeroFN = $anapro_rec['PROSUBKEY'];
        }


        if (!$this->ControllaPresenzaTrasmissione($anapro_rec, 'ASF', $Destinatario, $Ufficio)) {
            Out::msgInfo("Attenzione", "Visibilità già estesa per il destinatario indicato.");
            return false;
        }


        $arcite_rec = $this->proLib->GetArcite($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR']);
        $destinatario = array(
            "DESCUF" => $Ufficio,
            "DESCOD" => $Destinatario,
            "DESGES" => 0, // Una estensione di visibilità non può essere in gestione
            "ITEBASE" => 0,
            "DESTERMINE" => ''
        );
        $extraParm = array(
            "NOTE" => "ESTENSIONE VISIBILITA ' $DescrizioneFN. " . $Annotazioni,
            "NODO" => "ASF"
        );
        $iter = proIter::getInstance($this->proLib, $anapro_rec);
        $iterNode = $iter->insertIterNode($destinatario, $arcite_rec, $extraParm);
        if (!$iterNode) {
            return false;
        }
        if ($iterNode) {
            $iter->chiudiIterNode($iterNode);
        }


        // Dovrebbe controllare se ha la visibilita per i sottofascicoli precedenti o per il fascicolo?
        // controllo quando c'è la presenza di sottofascicoli..
        //Out::hide($this->nameForm . '_divCampiAssegnazione');
        Out::clearFields($this->nameForm . '_divCampiAssegnazione');

        $this->CaricaAssegnazioniIter($this->pronumFascicolo, $this->pronumSottoFascicolo);
        $this->CaricaGriglia($this->gridAssegnazioni, $this->proAssegnazioni);

        Out::msgBlock('', 2000, true, '<div style="font-size:1.4em;">Visibilità estesa correttamente.</div>');
        // Messaggio da rimuovere quando sarà risolto il problema di visualizzazione dei documenti:
        if ($anapro_rec['PROPAR'] == 'N') {
            Out::msgInfo("Visibilita", "Ricordati di assegnare la visibilità anche agli eventuali sottofascicoli o fascicoli che contengono il sottofascicolo in oggetto. ");
        }
    }

    // Si può migliorare l'utilizzo di proIter...
    private function Trasmetti() {
        // Controllo Destinatario
        if ($_POST[$this->nameForm . '_Dest_cod'] && $_POST[$this->nameForm . '_Uff_cod']) {
            if (!$this->ControlloDestinatario()) {
                return false;
            }
        } else {
            // Controllo Ufficio
            $Anauff_rec = $this->proLib->GetAnauff($_POST[$this->nameForm . '_Uff_cod'], 'codice');
            if (!$Anauff_rec) {
                Out::msgStop("Attenzione", "Ufficio non valido.");
                return false;
            }
            if ($Anauff_rec['UFFANN'] == 1) {
                Out::msgStop("Attenzione", "Ufficio annullato, non è possibile utilizzarlo per trasmissioni.");
                return false;
            }
        }

        $anapro_rec = $this->Anapro_rec;
        $Destinatario = $_POST[$this->nameForm . '_Dest_cod'];
        $Ufficio = $_POST[$this->nameForm . '_Uff_cod'];
        $Gestione = $_POST[$this->nameForm . '_Gestione'];
        $Annotazioni = $_POST[$this->nameForm . '_Annotazioni'];
        $DescrizioneFN = 'FASCICOLO';
        $NumeroFN = $anapro_rec['PROFASKEY'];
        if ($this->pronumSottoFascicolo) {
            $DescrizioneFN = 'SOTTOFASCICOLO';
            $NumeroFN = $anapro_rec['PROSUBKEY'];
        }

        if (!$this->ControllaPresenzaTrasmissione($anapro_rec, 'TRX', $Destinatario, $Ufficio)) {

            if ($Destinatario && $Ufficio) {
                Out::msgInfo("Attenzione", "Il " . strtolower($DescrizioneFN) . " risulta già trasmesso al destinatario indicato.");
            } else {
                Out::msgInfo("Attenzione", "Il " . strtolower($DescrizioneFN) . " risulta già trasmesso all'ufficio indicato.");
            }
            return false;
        }

        $profilo = proSoggetto::getProfileFromIdUtente();
        if (!$profilo) {
            Out::msgInfo("Attenzione", "Profilo utente non definito.");
            return false;
        }


        $iter = proIter::getInstance($this->proLib, $anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $ArciteIns_rec = $this->GetTrasmissione($anapro_rec, 'INS', $profilo['COD_SOGGETTO']);
        if (!$ArciteIns_rec) {
            // creo arcite ins
            $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
            if (!$anamed_rec) {
                return false;
            }
            $UfficioIns = $this->UfficioProgesUsato;
            if (!$this->UfficioProgesUsato) {
                $uffdes_tab = $this->proLib->GetUffdes($profilo['COD_SOGGETTO']);
                if ($uffdes_tab) {
                    $UfficioIns = $uffdes_tab[0]['UFFCOD'];
                }
            }
            if (!$UfficioIns) {
                Out::msgStop("Attenzione", "Codice ufficio del Trasmittente mancante. Non è possibile inviare la trasmissione.");
                return false;
            }
            $ultimoBase = array();
            $pr = (int) substr($iter->protocollo['PRONUM'], 4) . "/" . substr($iter->protocollo['PRONUM'], 0, 4);
            $note = "INSERIMENTO VISIBILITA $DescrizioneFN N.$NumeroFN - $pr";
            $retPadre = $iter->insertIterNodeFromAnamed($anamed_rec, $ultimoBase, array(
                "NODO" => "INS",
                "UFFICIO" => $UfficioIns,
                "ITEBASE" => 1,
                "GESTIONE" => 1,
                "NOTE" => $note));
            if (!$retPadre) {
                return false;
            }

            $retPadre = $iter->chiudiIterNode($retPadre);
            if (!$retPadre) {
                return false;
            }
            $retPadre = $iter->leggiIterNode($retPadre);
            if (!$retPadre) {
                return false;
            }
            $ArciteIns_rec = $retPadre;
        }
        $extraParm = array(
            "NOTE" => "TRASMISSIONE $DescrizioneFN. " . $Annotazioni
        );

        $destinatario = array(
            "DESCUF" => $Ufficio,
            "DESCOD" => $Destinatario,
            "DESGES" => $Gestione,
            "DESTERMINE" => '',
            "ITEBASE" => 0
        );

        if (!$iter->insertIterNode($destinatario, $ArciteIns_rec, $extraParm)) {
            return false;
        }
        $arcite_rec = $this->proLib->GetArcite($arcite_rec['ROWID'], 'rowid');

        //Out::hide($this->nameForm . '_divCampiAssegnazione');
        Out::clearFields($this->nameForm . '_divCampiAssegnazione');

        $this->CaricaAssegnazioniIter($this->pronumFascicolo, $this->pronumSottoFascicolo);
        $this->CaricaGriglia($this->gridAssegnazioni, $this->proAssegnazioni);

        Out::msgBlock('', 2000, true, '<div style="font-size:1.4em;">Trasmissione al destinatario effettuata correttamente.</div>');
        if ($anapro_rec['PROPAR'] == 'N') {
            Out::msgInfo("Visibilita", "Ricordati di assegnare la visibilità anche agli eventuali sottofascicoli o fascicoli che contengono il sottofascicolo in oggetto. ");
        }
    }

    private function ControllaPresenzaTrasmissione($Anapro_rec, $Nodo, $Destinatario, $Ufficio = '') {
        $Arcite_rec = $this->GetTrasmissione($Anapro_rec, $Nodo, $Destinatario, $Ufficio);
        if ($Arcite_rec) {
            return false;
        }
        return true;
    }

    private function GetTrasmissione($Anapro_rec, $Nodo, $Destinatario, $Ufficio = '') {
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $Anapro_rec['PRONUM'] . " AND 
                              ITEPAR = '" . $Anapro_rec['PROPAR'] . "' AND
                              ITENODO = '$Nodo' AND
                              ITEDES = '$Destinatario' ";
        if ($Ufficio) {
            $sql.=" AND ITEUFF = '$Ufficio' ";
        }
        return ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
    }

    private function AnnullaTrasmissione($codice, $tipo = 'rowid') {
        $Arcite_rec = $this->proLib->GetArcite($codice, $tipo);
        if (!$Arcite_rec) {
            Out::msgStop("Attenzione", "Iter non trovato.");
            return;
        }
        $anapro_rec = $this->Anapro_rec;
        $DescrizioneFN = 'FASCICOLO';
        $NumeroFN = $anapro_rec['PROFASKEY'];
        if ($this->pronumSottoFascicolo) {
            $DescrizioneFN = 'SOTTOFASCICOLO';
            $NumeroFN = $anapro_rec['PROSUBKEY'];
        }
        $OldNote = $Arcite_rec['ITEANN'];
        $extraParm = array(
            "NOTE" => "ANNULLAMENTO ITER: " . $OldNote
        );
        /*
         * Preparo e annullo iter node.
         */
        $iter = proIter::getInstance($this->proLib, $anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        if (!$iter->annullaIterNode($Arcite_rec, $extraParm)) {
            return;
        }

        $this->CaricaAssegnazioniIter($this->pronumFascicolo, $this->pronumSottoFascicolo);
        $this->CaricaGriglia($this->gridAssegnazioni, $this->proAssegnazioni);
        Out::msgBlock('', 2000, true, '<div style="font-size:1.4em;">Annullamento Trasmissione/Visibilità ' . $DescrizioneFN . ' terminato con successo.</div>');
    }

    private function ControlloParentVisibilita($Anapro_rec, $Destinatario, $Ufficio, $TipoVisTras = 'Visibilità') {
        // Controllo se ha una visibilita sul anapro:
        // Qui prendo i padri e controllo le loro visibilità?
        // Scorro uno ad uno e controllo se hanno visibilita?
        $livello = 0;
        $AnaproTest_rec = $Anapro_rec;
        $AnaproVisMancanti = array();
        while (true) {
            $livello++;
            if ($livello >= 10) {
                break;
            }
            // Cerco il padre.
            $Orgconn_rec = $this->proLib->GetOrgConn($AnaproTest_rec['PRONUM'], 'codice', $AnaproTest_rec['PROPAR']);
            if ($Orgconn_rec['PRONUMPARENT']) {
                $AnaproTest_rec = $this->proLib->GetAnapro($Orgconn_rec['PRONUMPARENT'], 'codice', $Orgconn_rec['PROPARPARENT']);
                // ex    ControlloVisibilitaSoggetto
                $RetVisibilita = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $AnaproTest_rec['PRONUM'], $AnaproTest_rec['PROPAR']);
                if ($AnaproTest_rec['PROPAR'] == 'F') {
                    $MsgVisib = 'Fascicolo.';
                } else {
                    $MsgVisib = 'Sottofascicolo: ' . $Anapro_rec['PROSUBKEY'];
                }
                $AnaproVisMancanti[$AnaproTest_rec['ROWID']] = $MsgVisib;
            } else {
                break;
            }
        }
        if ($AnaproVisMancanti) {
            $Anamed_rec = $this->proLib->GetAnamed($Destinatario, 'codice');
            $Messaggio = "Stai assegnando una " . $TipoVisTras . " a " . $Anamed_rec['MEDNOM'] . ", ma il destinatario non ha visibilità su:<br>";
            foreach ($AnaproVisMancanti as $VisMancante) {
                $Messaggio.='- ' . $VisMancante . '<br>';
            }
            Out::msgInfo("Visibilita", $Messaggio);
        }

        App::log($AnaproVisMancanti);
    }

    private function ControlloVisibilitaSoggetto($Anapro_rec, $Destinatario, $Ufficio) {
        $sql = "SELECT * FROM ARCITE 
                        WHERE ITEANNULLATO = '' AND 
                              ITEPRO = " . $Anapro_rec['PRONUM'] . " AND 
                              ITEPAR = '" . $Anapro_rec['PROPAR'] . "' AND
                              ITEDES = '$Destinatario' ";
        if ($Ufficio) {
            $sql.=" AND ITEUFF = '$Ufficio' ";
        }
        return ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
    }

}

?>
