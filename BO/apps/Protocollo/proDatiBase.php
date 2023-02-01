<?php

/**
 *  Browser per Forms
 *
 *
 * @category   Library
 * @package    /apps/Generator
 * @author     Carlo Iesari <carlo@iesari.em>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    30.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proProtocolla.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDatiProtocollo.class.php';

function proDatiBase() {
    $proDatiBase = new proDatiBase();
    $proDatiBase->parseEvent();
    return;
}

class proDatiBase extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $segLib;
    public $nameForm = "proDatiBase";
    public $buttonBar = "proDatiBase_buttonBar";
    public $gridAllegati = "proDatiBase_gridAllegati";
    public $tmpDir;
    public $apriProtocollo;
    public $DatiProtocollo = array();
    public $DatiObbligatori = array();
    public $DatiNascosti = array();
    public $DatiSolaLettura = array();
    public $ElencoAllegati = array();
    private $errCode;
    private $errMessage;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->segLib = new segLib();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->DatiProtocollo = App::$utente->getKey($this->nameForm . '_DatiProtocollo');
        $this->DatiObbligatori = App::$utente->getKey($this->nameForm . '_DatiObbligatori');
        $this->DatiNascosti = App::$utente->getKey($this->nameForm . '_DatiNascosti');
        $this->DatiSolaLettura = App::$utente->getKey($this->nameForm . '_DatiSolaLettura');
        $this->tmpDir = App::$utente->getKey($this->nameForm . '_tmpDir');
        $this->ElencoAllegati = App::$utente->getKey($this->nameForm . '_ElencoAllegati');
        $this->apriProtocollo = App::$utente->getKey($this->nameForm . '_apriProtocollo');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_DatiProtocollo', $this->DatiProtocollo);
            App::$utente->setKey($this->nameForm . '_DatiObbligatori', $this->DatiObbligatori);
            App::$utente->setKey($this->nameForm . '_DatiNascosti', $this->DatiNascosti);
            App::$utente->setKey($this->nameForm . '_DatiSolaLettura', $this->DatiSolaLettura);
            App::$utente->setKey($this->nameForm . '_tmpDir', $this->tmpDir);
            App::$utente->setKey($this->nameForm . '_ElencoAllegati', $this->ElencoAllegati);
            App::$utente->setKey($this->nameForm . '_apriProtocollo', $this->apriProtocollo);
        }
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function getTmpDir() {
        return $this->tmpDir;
    }

    function setTmpDir($tmpDir) {
        $this->tmpDir = $tmpDir;
    }

    public function getApriProtocollo() {
        return $this->apriProtocollo;
    }

    public function setApriProtocollo($apriProtocollo) {
        $this->apriProtocollo = $apriProtocollo;
    }

    public function getDatiProtocollo() {
        return $this->DatiProtocollo;
    }

    /**
     * 
     * @param array $DatiProtocollo
     * 
     * COD_REPERTORIO   |
     * PROGR_REPERTORIO | SOSPESO
     * PROGR_ANNO       |
     * 
     * FIRMATARIO[CODICE]
     * FIRMATARIO[UFFICIO]
     * OGGETTO
     * TITOLARIO[CATEGORIA]
     * TITOLARIO[CLASSE]
     * TITOLARIO[SOTTOCLASSE]
     * 
     * IDELIB (SEGRXX.INDICE) Facoltativo
     * 
     * CHIAVIFASCICOLO[n elementi] 
     * 
     * ALLEGATI[PRINCIPALE]
     * ALLEGATI[ALLEGATI][]
     * 
     * 
     */
    public function setDatiProtocollo($DatiProtocollo) {
        $this->DatiProtocollo = $DatiProtocollo;
    }

    /**
     * 
     * @param type $DatiObbligatori
     * FIRMATARIO
     * OGGETTO
     * TITOLARIO[CATEGORIA]
     * TITOLARIO[CLASSE]
     * TITOLARIO[SOTTOCLASSE]
     * 
     */
    public function setDatiObbligatori($DatiObbligatori) {
        $this->DatiObbligatori = $DatiObbligatori;
    }

    public function setDatiNascosti($DatiNascosti) {
        $this->DatiNascosti = $DatiNascosti;
    }

    public function setDatiSolaLettura($DatiSolaLettura) {
        $this->DatiSolaLettura = $DatiSolaLettura;
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                $this->CaricaDati();
                break;

            case 'addGridRow':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->CaricaGriglia($this->gridAllegati, $this->ElencoAllegati);
                        break;
                }
                break;

            case 'dbClickRow':
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if ($this->bloccoDaEmail != true) {
                            $messaggio = "Sei sicuro di voler cancellare l'allegato?";
                            Out::msgQuestion("ATTENZIONE!", $messaggio, array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancAlle',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancAlle',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;
                }
                break;


            case 'afterSaveCell':
                if ($_POST['value'] != 'undefined') {
                    switch ($_POST['id']) {
                        case $this->gridAllegati:
                            $currRowid = $_POST['rowid'];
                            $this->ElencoAllegati[$_POST['rowid']][$_POST['cellname']] = $_POST['value'];
                            $this->ControllaAllegati($currRowid);
                            $this->RicaricaDatiProtocolloAllegati(); // Qui serve?
                            $this->CaricaGriglia($this->gridAllegati, $this->ElencoAllegati);
                            break;
                    }
                }
                break;


            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        $this->AggiornaDatiProtocollo();
                        App::log($this->DatiProtocollo);

                        $retPro = $this->Protocolla($this->DatiProtocollo);
                        if (!$retPro) {
                            Out::msgStop("Attenzione", $this->errMessage);
                            break;
                        }

                        Out::msgBlock('', 3000, false, "Documento protocollato con successo.");

                        if (!$this->ApriProtocollo($retPro, 'rowid')) {
                            Out::msgStop("Attenzione", "Protocollo Creato, ma non accessibile:<br>" . $this->errMessage());
                            break;
                        }
                        // PULIZIA CARTELLE TEMPORANEE
//                        $this->returnToParent();
                        $this->close();
                        break;
                    case $this->nameForm . '_TITOLARIO[CATEGORIA]_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;

                    case $this->nameForm . '_FIRMATARIO[CODICE]_butt':
                        proRic::proRicFirmatario($this->nameForm, '', '', '', 'returnFirmatario');
                        break;

                    case $this->nameForm . '_DESC_UFFICIO_butt':
                        $codice = $_POST[$this->nameForm . '_FIRMATARIO']['CODICE'];
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
                    case $this->nameForm . '_DelTitolario':
                        Out::valore($this->nameForm . '_TITOLARIO[CATEGORIA]', '');
                        Out::valore($this->nameForm . '_TITOLARIO[CLASSE]', '');
                        Out::valore($this->nameForm . '_TITOLARIO[SOTTOCLASSE]', '');
                        Out::valore($this->nameForm . '_DESC_TITOLARIO', '');
                        break;

                    case $this->nameForm . '_ConfermaCancAlle':
                        $currRowid = $_POST[$this->gridAllegati]['gridParam']['selarrrow'];
                        unset($this->ElencoAllegati[$currRowid]);
                        $this->ControllaAllegati();
                        $this->RicaricaDatiProtocolloAllegati();
                        $this->CaricaGriglia($this->gridAllegati, $this->ElencoAllegati);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($this->elementId) {
                    case $this->nameForm . '_FIRMATARIO[CODICE]':
                        $codice = $_POST[$this->nameForm . '_FIRMATARIO']['CODICE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $sql = "SELECT ANAMED.* FROM ANAMED WHERE ANAMED.MEDCOD='$codice'";
                            $anamed_rec = $this->proLib->getGenericTab($sql, false);
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . '_FIRMATARIO[CODICE]', '');
                                Out::valore($this->nameForm . '_DESC_FIRMATARIO', '');
                                Out::setFocus('', $this->nameForm . "_DESC_FIRMATARIO");
                                break;
                            } else {
                                Out::valore($this->nameForm . '_FIRMATARIO[CODICE]', $anamed_rec['MEDCOD']);
                                Out::valore($this->nameForm . '_DESC_FIRMATARIO', $anamed_rec['MEDNOM']);
                                $sql_uffdes = "SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0";
                                $uffdes_tab = $this->proLib->getGenericTab($sql_uffdes);
                                if (count($uffdes_tab) == 1) {
                                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                    Out::valore($this->nameForm . '_FIRMATARIO[UFFICIO]', $anauff_rec['UFFCOD']);
                                    Out::valore($this->nameForm . '_DESC_UFFICIO', $anauff_rec['UFFDES']);
                                } else {
                                    if ($_POST[$this->nameForm . '_FIRMATARIO[UFFICIO]'] == '' || $_POST[$this->nameForm . '_DESC_UFFICIO'] == '') {
                                        proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                                    }
                                }
                            }
                        } else {
                            Out::valore($this->nameForm . '_FIRMATARIO[CODICE]', '');
                            Out::valore($this->nameForm . '_DESC_FIRMATARIO', '');
                            Out::valore($this->nameForm . '_FIRMATARIO[UFFICIO]', '');
                            Out::valore($this->nameForm . '_DESC_UFFICIO', '');
                        }
                        break;
                }
                break;

            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DESC_FIRMATARIO':
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
                                itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_FIRMATARIO[CODICE]" => $anamed_rec['MEDCOD'], $this->nameForm . "_FIRMATARIO[UFFICIO]" => '', $this->nameForm . "_DESC_UFFICIO" => ''));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'returnFirmatario':
                $this->DecodFirmatario($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . "_FIRMATARIO[UFFICIO]", '');
                Out::valore($this->nameForm . "_DESC_UFFICIO", '');
                Out::setFocus('', $this->nameForm . "_FIRMATARIO[CODICE]");
                break;

            case 'returnUfficiPerDestinatarioFirmatario':
                $this->DecodFirmatarioUfficio($_POST['retKey'], 'rowid');
                break;

            case 'returnTitolario':
                Out::valore($this->nameForm . '_TITOLARIO[CATEGORIA]', $_POST['rowData']['CATCOD']);
                Out::valore($this->nameForm . '_TITOLARIO[CLASSE]', $_POST['rowData']['CLACOD']);
                Out::valore($this->nameForm . '_TITOLARIO[SOTTOCLASSE]', $_POST['rowData']['FASCOD']);
                Out::valore($this->nameForm . '_DESC_TITOLARIO', $_POST['rowData']['DESCRIZIONE']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_DatiProtocollo');
        App::$utente->removeKey($this->nameForm . '_DatiObbligatori');
        App::$utente->removeKey($this->nameForm . '_DatiNascosti');
        App::$utente->removeKey($this->nameForm . '_DatiSolaLettura');
        App::$utente->removeKey($this->nameForm . '_tmpDir');
        App::$utente->removeKey($this->nameForm . '_ElencoAllegati');
        App::$utente->removeKey($this->nameForm . '_apriProtocollo');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function CaricaDati() {
        if ($this->DatiProtocollo) {
            if ($this->DatiProtocollo['OGGETTO']) {
                Out::valore($this->nameForm . '_OGGETTO', $this->DatiProtocollo['OGGETTO']);
            }
            if ($this->DatiProtocollo['FIRMATARIO']) {
                // Decodifico il firmatario
                Out::valori($this->DatiProtocollo['FIRMATARIO'], $this->nameForm . '_FIRMATARIO');
                $this->DecodFirmatario($this->DatiProtocollo['FIRMATARIO']['CODICE']);
                $this->DecodFirmatarioUfficio($this->DatiProtocollo['FIRMATARIO']['UFFICIO']);
            }
            /*
             * DA COMPLETARE DECODIFICA DA DATI PASSATI 
             */
//            if ($this->DatiProtocollo['TITOLARIO']) {
//                Out::valore($this->nameForm . '_TITOLARIO[CATEGORIA]',$this->DatiProtocollo['TITOLARIO']);
//                Out::valore($this->nameForm . '_TITOLARIO[CLASSE]',$this->DatiProtocollo['TITOLARIO']);
//                Out::valore($this->nameForm . '_TITOLARIO[SOTTOCLASSE]',$this->DatiProtocollo['TITOLARIO']);
//            }
        }

        Out::addClass($this->nameForm . '_OGGETTO', "required");
        Out::addClass($this->nameForm . '_FIRMATARIO[CODICE]', "required");
        Out::addClass($this->nameForm . '_FIRMATARIO[UFFICIO]', "required");


        // Controllo obbligatorietà dati
        // Nascondo i livelli di titolario non abilitati.
        $anaent_32 = $this->proLib->GetAnaent('32');
        if ($anaent_32['ENTDE2'] == 1) {
            Out::addClass($this->nameForm . '_TITOLARIO[CATEGORIA]', "required");
        }

        $anaent_12 = $this->proLib->GetAnaent('12');
        if ($anaent_12['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_TITOLARIO[CLASSE]');
            Out::hide($this->nameForm . '_TITOLARIO[SOTTOCLASSE]');
        }

        $anaent_13 = $this->proLib->GetAnaent('13');
        if ($anaent_13['ENTDE4'] == '1') {
            Out::hide($this->nameForm . '_TITOLARIO[SOTTOCLASSE]');
        }

        // Carico gli Allegati.
        $this->caricaGrigliaAllegati();
        //if ($this->DatiProtocollo['ROWID_INDICE']) {
        if ($this->DatiProtocollo['CLASSE_APP']) {
            Out::hide($this->nameForm . '_divBottoniAllega');
            Out::removeElement($this->gridAllegati . '_delGridRow');
            Out::block($this->nameForm . '_divGrigliaAllegati');
            TableView::hideCol($this->gridAllegati, 'DOCTIPO');
            TableView::hideCol($this->gridAllegati, 'PREVIEW');
        }
    }

    public function ControllaFirmatario($CodiceSogg, $CodiceUff) {
        $proDatiProtocollo = new proDatiProtocollo();
        if (!$proDatiProtocollo->CtrSoggetto($CodiceSogg, $CodiceUff)) {
            Out::valore($this->nameForm . '_FIRMATARIO[CODICE]', '');
            Out::valore($this->nameForm . '_DESC_FIRMATARIO', '');
            Out::valore($this->nameForm . '_FIRMATARIO[UFFICIO]', '');
            Out::valore($this->nameForm . '_DESC_UFFICIO', '');
        } else {
            $this->DecodFirmatario($CodiceSogg, 'codice');
        }
    }

    public function DecodFirmatario($codice, $tipo = 'codice') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'si');
        if ($anamed_rec) {
            Out::valore($this->nameForm . '_FIRMATARIO[CODICE]', $anamed_rec["MEDCOD"]);
            Out::valore($this->nameForm . '_DESC_FIRMATARIO', $anamed_rec["MEDNOM"]);
        }
    }

    public function DecodFirmatarioUfficio($codice, $tipo = 'codice') {
        $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        if ($anauff_rec) {
            Out::valore($this->nameForm . '_FIRMATARIO[UFFICIO]', $anauff_rec['UFFCOD']);
            Out::valore($this->nameForm . '_DESC_UFFICIO', $anauff_rec['UFFDES']);
        }
    }

    public function Protocolla($datiProtocollazione = array()) {
        if (!$datiProtocollazione) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati della protocollazione mancanti.");
            return false;
        }

        /* Esegui i Controlli sui Dati del Nuovo Protocollo */
        if (!$this->ControlloDatiProtocollazione($datiProtocollazione)) {
            return false;
        }

        /*
         * Preparazione elementi protocollazione.
         */
        $elementi = array();
        /**
         * Dati principali
         */
        $elementi['tipo'] = 'C';
        $elementi['dati'] = array();
        $elementi['dati']['TipoDocumento'] = $datiProtocollazione['TIPODOCUMENTO'];
        $elementi['dati']['Oggetto'] = $datiProtocollazione['OGGETTO'];
        $elementi['dati']['ProtocolloMittente']['Numero'] = '';
        $elementi['dati']['ProtocolloMittente']['Data'] = '';
        $elementi['dati']['DataArrivo'] = date('Ymd');
        $elementi['dati']['ProtocolloEmergenza'] = '';

        /*
         * Firmatario FIRMATARIO UFFICIO_FIRMATARIO
         */
        $Firmatario = $datiProtocollazione['FIRMATARIO']['CODICE'];
        $FirmatarioUfficio = $datiProtocollazione['FIRMATARIO']['UFFICIO'];
        $anamed_rec = $this->proLib->GetAnamed($Firmatario);
        if (!$anamed_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice " . $Firmatario . " non trovato nell'anagrafica dei mittenti/destinatari.");
            return false;
        }

        $elementi['firmatari']['firmatario'][0]['CodiceDestinatario'] = $Firmatario;
        $elementi['firmatari']['firmatario'][0]['Denominazione'] = $anamed_rec['MEDNOM'];
        $elementi['firmatari']['firmatario'][0]['Ufficio'] = $FirmatarioUfficio;
        /**
         * Classificazione
         */
        $Titolario = '';
        if ($datiProtocollazione['TITOLARIO']) {
            $DatiTitolario = $datiProtocollazione['TITOLARIO'];
            if ($DatiTitolario['CATEGORIA']) {
                $Titolario = $DatiTitolario['CATEGORIA'];
                if ($DatiTitolario['CLASSE']) {
                    $Titolario.='.' . $DatiTitolario['CLASSE'];
                    if ($DatiTitolario['SOTTOCLASSE']) {
                        $Titolario.='.' . $DatiTitolario['SOTTOCLASSE'];
                    }
                }
            }
        }
        $elementi['dati']['Classificazione'] = $Titolario;

        $elementiAllegati = $this->ElaboraElementiAllegati($datiProtocollazione);
        if (!$elementiAllegati && $this->errCode == '-1') {
            return false;
        }

        if ($elementiAllegati) {
            $elementi = array_merge($elementi, $elementiAllegati);
        }

        /*
         * Istanzio l'oggetto proDatiProtocollo
         */
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /*
         * Attiva Controlli su proDatiProtocollo
         */
        // CtrConformitaDatiWs
//        if (!$proDatiProtocollo->controllaDati()) {
//            $this->setErrCode(-1);
//            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
//            return false;
//        }
        /* Istanzio Oggetto proProtocolla */
        $proProtocolla = new proProtocolla();
        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
        /*
         * Crea collegamento con segreteria.
         */
        //if ($datiProtocollazione['ROWID_INDICE']) {
        if ($datiProtocollazione['CLASSE_APP']) {
            //  Occorre controllarlo prima di eseguire la protocollazione ?
            //$Indice_rec = $this->segLib->GetIndice($datiProtocollazione['ROWID_INDICE'], 'rowid');
            $ProRepDoc_rec = array();
            $ProRepDoc_rec['PRONUM'] = $Anapro_rec['PRONUM'];
            $ProRepDoc_rec['PROPAR'] = $Anapro_rec['PROPAR'];
            $ProRepDoc_rec['CLASSE'] = $datiProtocollazione['CLASSE_APP'];
            $ProRepDoc_rec['CHIAVE'] = $datiProtocollazione['CHIAVE_APP'];
            if (!$this->insertRecord($this->PROT_DB, 'PROREPDOC', $ProRepDoc_rec, '')) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in inserimento su PROREPDOC.");
                return false;
            }
        }
        return $Anapro_rec['ROWID'];
    }

    public function ControlloDatiProtocollazione($datiProtocollazione) {
        /*
         * Ufficio Operatore di protocollazione
         */
        $codiceOperatore = proSoggetto::getCodiceSoggettoFromIdUtente();
        if (!$codiceOperatore) {
            $this->setErrCode(-1);
            $this->setErrMessage("Utente senza profilo protocollazione.");
            return false;
        }
        /*
         * Controlli sul firmatario
         */
        $Firmatario = $datiProtocollazione['FIRMATARIO']['CODICE'];
        $FirmatarioUfficio = $datiProtocollazione['FIRMATARIO']['UFFICIO'];
        if (!$Firmatario || !$FirmatarioUfficio) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati Firmatario mancanti o incompleti.");
            return false;
        }
        $anamed_rec = $this->proLib->GetAnamed($Firmatario);
        if (!$anamed_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice " . $Firmatario . " non trovato nell'anagrafica dei mittenti/destinatari.");
            return false;
        }
        /*
         * Controlli profilo
         */
        $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($Firmatario);
        if (!$ruoli) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessun ufficio trovato per il soggetto  ' . $Firmatario);
            return false;
        }
        $trovato = false;
        foreach ($ruoli as $ruolo) {
            if ($FirmatarioUfficio == $ruolo['CODICEUFFICIO']) {
                $trovato = true;
            }
        }
        if (!$trovato) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessun ufficio corrispondente per il firmatario ' . $Firmatario);
            return false;
        }
        /* Controllo Titolario */
        $anaent_32 = $this->proLib->GetAnaent('32');
        if ($anaent_32['ENTDE2'] == 1) {
            // Titolario obbligatorio
            if (!$datiProtocollazione['CATEGORIA']) {
                $this->setErrCode(-1);
                $this->setErrMessage('Titolario obbligatorio.');
                return false;
            }
        }
        /* Controllo Trasmissioni Interne  Obbligatorie ? */
        return true;
    }

    public function ElaboraElementiAllegati($datiProtocollo) {
        // Salvo gli allegati pubblicabili
        $ElencoAllegati = array();

        $Allegati = $datiProtocollo['ALLEGATI'];
        if ($Allegati) {
            if ($Allegati['PRINCIPALE']) {
                $SorgenteFile = $Allegati['PRINCIPALE']['FilePath'];
                $fh = fopen($SorgenteFile, 'rb');
                if (!$fh) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $SorgenteFile");
                    return false;
                }
                $binary = fread($fh, filesize($SorgenteFile));
                fclose($fh);
                $binary = base64_encode($binary);

                $ElencoAllegati['allegati']['Principale'] = array();
                $ElencoAllegati['allegati']['Principale']['Nome'] = $Allegati['PRINCIPALE']['Nome'];
                $ElencoAllegati['allegati']['Principale']['Stream'] = $binary;
                $ElencoAllegati['allegati']['Principale']['Descrizione'] = $Allegati['PRINCIPALE']['Descrizione'];
            }
            if ($Allegati['ALLEGATI']) {
                foreach ($Allegati['ALLEGATI'] as $Allegato) {
                    $SorgenteFile = $Allegato['FilePath'];
                    $fh = fopen($SorgenteFile, 'rb');
                    if (!$fh) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $SorgenteFile");
                        return false;
                    }
                    $binary = fread($fh, filesize($SorgenteFile));
                    fclose($fh);
                    $binary = base64_encode($binary);

                    $elementoAllegato = array();
                    $elementoAllegato['Documento']['Nome'] = $Allegato['Nome'];
                    $elementoAllegato['Documento']['Stream'] = $binary;
                    $elementoAllegato['Documento']['Descrizione'] = $Allegato['Descrizione'];
                    $ElencoAllegati['allegati']['Allegati'][] = $elementoAllegato;
                }
            }
        }

        return $ElencoAllegati;
        // Fine copia allegati 
    }

    private function caricaGrigliaAllegati() {
        $this->ElencoAllegati = array();
        if ($this->DatiProtocollo['ALLEGATI']) {
            if ($this->DatiProtocollo['ALLEGATI']['PRINCIPALE']) {
                $Principale = $this->DatiProtocollo['ALLEGATI']['PRINCIPALE'];
                $Principale['NOMEFILE'] = $Principale['Nome'];
                $Principale['FILEINFO'] = $Principale['Descrizione'];
                $preview = '';
                $ext = pathinfo($Principale['Nome'], PATHINFO_EXTENSION);
                if (strtolower($ext) == "p7m") {
                    $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
                }
                $Principale['PREVIEW'] = $preview;
                $Principale['DOCTIPO'] = '';
                $this->ElencoAllegati[] = $Principale;
            }
            if ($this->DatiProtocollo['ALLEGATI']['ALLEGATI']) {
                $Allegati = $this->DatiProtocollo['ALLEGATI']['ALLEGATI'];
                foreach ($Allegati as $Allegato) {
                    App::log($Allegato);
                    $Allegato['NOMEFILE'] = $Allegato['Nome'];
                    $Allegato['FILEINFO'] = $Allegato['Descrizione'];
                    $preview = '';
                    $ext = pathinfo($Allegato['Nome'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == "p7m") {
                        $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
                    }
                    $Allegato['PREVIEW'] = $preview;
                    $Allegato['DOCTIPO'] = 'ALLEGATO';
                    $this->ElencoAllegati[] = $Allegato;
                }
            }
        }

        TableView::clearGrid($this->gridAllegati);
        $this->CaricaGriglia($this->gridAllegati, $this->ElencoAllegati);
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

    private function ControllaAllegati($currRowid = '') {
        $principale = false;
        $keyPrincipale = '';
        foreach ($this->ElencoAllegati as $k => $allegato) {
            if ($allegato['DOCTIPO'] == '') {
                $principale = true;
                $keyPrincipale = $k;
            }
        }
        /* Se ho appena impostato che l'allegato corrente è il principale, allora 
         * keyPrincipale è il rowid corrente.
         */
        if ($currRowid !== '') {
            if ($this->ElencoAllegati[$currRowid]['DOCTIPO'] == '') {
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
            foreach ($this->ElencoAllegati as $key => $allegato) {
                if (count($this->ElencoAllegati) == 1 || $key != $currRowid || $currRowid == '') {
                    $this->ElencoAllegati[$key]['DOCTIPO'] = '';
                    break;
                }
            }
        } else {
            /* Verifico se è presente più di un 
             * allegato princiaple e ne tengo uno */
            foreach ($this->ElencoAllegati as $key => $allegato) {
                if ($allegato['DOCTIPO'] == '' && $key != $keyPrincipale) {
                    $this->ElencoAllegati[$key]['DOCTIPO'] = 'ALLEGATO';
                }
            }
        }
    }

    public function RicaricaDatiProtocolloAllegati() {
        $ALLEGATI = array();
        foreach ($this->ElencoAllegati as $Allegato) {
            if ($Allegato['DOCTIPO'] == '') {
                $ALLEGATI['PRINCIPALE'] = $Allegato;
            } else {
                $ALLEGATI['ALLEGATO'][] = $Allegato;
            }
        }
        $this->DatiProtocollo['ALLEGATI'] = $ALLEGATI;
    }

    public function AggiornaDatiProtocollo() {
        // Oggetto
        $this->DatiProtocollo['OGGETTO'] = $_POST[$this->nameForm . '_OGGETTO'];
        $this->DatiProtocollo['FIRMATARIO'] = $_POST[$this->nameForm . '_FIRMATARIO'];
        $this->DatiProtocollo['TITOLARIO'] = $_POST[$this->nameForm . '_TITOLARIO'];
    }

    public function ApriProtocollo($codice, $tipo = 'codice', $tipoProt = '') {
        $Anapro_rec = $this->proLib->GetAnapro($codice, $tipo, $tipoProt);
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Anapro non trovato.");
            return false;
        }

        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if (!$anaproctr_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo non accessibile");
            return false;
        }

        $model = 'proArri';
        $_POST['tipoProt'] = $anaproctr_rec['PROPAR'];
        $_POST['event'] = 'openform';
        $_POST['proGest_ANAPRO']['ROWID'] = $anaproctr_rec['ROWID'];
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();

        return true;
    }

}

?>
