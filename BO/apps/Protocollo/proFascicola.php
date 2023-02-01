<?php

/**
 *
 * RICHIESTA DATI FASCICOLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    29.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';

function proFascicola() {
    $proFascicola = new proFascicola();
    $proFascicola->parseEvent();
    return;
}

class proFascicola extends itaModel {

    public $PROT_DB;
    public $nameForm = "proFascicola";
    public $proLib;
    public $proLibSerie;
    public $gridDestinatari = "proFascicola_gridDestinatari";
    public $dati;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibSerie = new proLibSerie();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->dati = App::$utente->getKey($this->nameForm . '_dati');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_dati', $this->dati);
        }
    }

    public function getDati() {
        return $this->dati;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                $anaent_33 = $this->proLib->GetAnaent('33');
                if (!$anaent_33['ENTDE4']) {
                    Out::hide($this->nameForm . '_Procedimento_field');
                    Out::hide($this->nameForm . '_Desc_proc_field');
                }

                $livello1 = $this->dati['livello1'];
                $livello2 = $this->dati['livello2'];
                $livello3 = $this->dati['livello3'];

                $this->CreaCombo();

                $descTitolo = $this->dati['descTitolo'];
                $rowid_protocollo = $this->dati['rowid_protocollo'];
                $prouof = $this->dati['prouof'];
                if ($this->dati['tipoInserimento'] == 'nuovo') {
                    Out::hide($this->nameForm . '_divFascicolo');
                    Out::addClass($this->nameForm . '_descFascicolo', "required");
                    Out::setFocus('', $this->nameForm . '_descFascicolo');
                } else {
                    Out::hide($this->nameForm . '_divInsert');
                    Out::addClass($this->nameForm . '_ANAPRO[PROARG]', "required");
                    Out::setFocus('', $this->nameForm . '_Organn');
                }
                $this->BloccaSerie();
                Out::enableField($this->nameForm . '_SERIE[CODICE]');
                if (!$this->caricaDati($livello1, $livello2, $livello3, $descTitolo, $prouof, $rowid_protocollo)) {
                    $this->returnToParent();
                }
                /* Setto default natura fascicolo: ibrido */
                Out::valore($this->nameForm . '_ANAORG[NATFAS]', 2);
                //butto fuori serie e progressivo se mi sono passati
                if ($this->dati['Serie'] != "") {
                    Out::valore($this->nameForm . '_SERIE[CODICE]', $this->dati['Serie']);
                    $this->DecodificaSerie($this->dati['Serie']);
                    Out::valore($this->nameForm . '_SERIE[PROGSERIE]', $this->dati['Progressivo']);
                    Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '0');
                    Out::addClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
                }
                if ($this->dati['Oggetto'] != "") {
                    Out::valore($this->nameForm . '_descFascicolo', $this->dati['Oggetto']);
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Registra':

                        if ($_POST[$this->nameForm . '_descFascicolo']) {
                            $this->dati['tipoRegistrazione'] = 'nuovo';
                            $this->dati['descrizione'] = $_POST[$this->nameForm . '_descFascicolo'];
                            $this->dati['procedimento'] = $_POST[$this->nameForm . '_Procedimento'];
                        } else {
                            $this->dati['tipoRegistrazione'] = 'esistente';
                            $this->dati['anno'] = $_POST[$this->nameForm . '_Organn'];
                            $this->dati['codice'] = $_POST[$this->nameForm . '_ANAPRO']['PROARG'];
                        }
                        if (!$this->ControlloDestinatario($_POST[$this->nameForm . '_RES'], $_POST[$this->nameForm . '_UFF'])) {
                            break;
                        }

                        /* Controlli sulle serie */
                        $Serie_rec = $_POST[$this->nameForm . '_SERIE'];
                        $dati = $this->dati;
                        $titolario = $dati['FASCICOLO']['livello1'] . $dati['FASCICOLO']['livello2'] . $dati['FASCICOLO']['livello3'];
                        $versione_t = $dati['FASCICOLO']['versione'];
                        if (!$this->proLibSerie->ControlloDatiObbligatoriSerie($Serie_rec, $titolario, $versione_t)) {
                            if ($Serie_rec) {
                                Out::msgStop("Attenzione", $this->proLibSerie->getErrMessage());
                                break;
                            } else {
                                Out::msgInfo("Attenzione", $this->proLibSerie->getErrMessage());
                            }
                        }
                        $Dati_Anaorg = $_POST[$this->nameForm . '_ANAORG'];

                        $this->dati['SERIE'] = $Serie_rec;
                        $this->dati['RES'] = $_POST[$this->nameForm . '_RES'];
                        $this->dati['UFF'] = $_POST[$this->nameForm . '_UFF'];
                        $this->dati['DATI_ANAORG'] = $Dati_Anaorg;

                        $_POST = array();
                        $_POST['dati'] = $this->dati;
                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->setElementId($this->returnId);
                        $returnObj->parseEvent();
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_Procedimento_butt':
                        $titolario = (int) $this->dati['livello1'];
                        if ($this->dati['livello2']) {
                            $titolario .= "." . (int) $this->dati['livello2'];
                        }
                        if ($this->dati['livello3']) {
                            $titolario .= "." . (int) $this->dati['livello3'];
                        }
                        $msgDetail = "Filtro per Titolario: " . $this->getMessage((int) $this->dati['livello1'], (int) $this->dati['livello2'], (int) $this->dati['livello3'], $this->dati['descTitolo']);
                        proRic::proRicAnapra($this->nameForm, "Ricerca procedimento", '', " WHERE PRACLA='$titolario'", $msgDetail);
                        break;
                    case $this->nameForm . '_ANAPRO[PROARG]_butt':

//                        App::log($this->dati);

                        $where = " WHERE ORGDAT='' AND ORGCCF='" . $this->dati['livello1'] . $this->dati['livello2'] . $this->dati['livello3'] . "'";
                        if ($_POST[$this->nameForm . '_Organn']) {
                            $where .= " AND ORGANN='{$_POST[$this->nameForm . '_Organn']}'";
                        }
                        proric::proRicOrg($this->nameForm, $where);

                        break;
                    case $this->nameForm . '_AggiungiFascicolo':
                        Out::hide($this->nameForm . '_divFascicolo');
                        Out::show($this->nameForm . '_divInsert');

                        Out::delClass($this->nameForm . '_ANAPRO[PROARG]', "required");
                        Out::addClass($this->nameForm . '_descFascicolo', "required");
                        Out::setFocus('', $this->nameForm . '_descFascicolo');

                        break;

                    case $this->nameForm . '_UFFRESP_butt':
                        $codice = $_POST[$this->nameForm . '_RES'];
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

                    case $this->nameForm . '_RES_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedMittPartenza');
                        break;

                    case $this->nameForm . '_SERIE[CODICE]_butt':
                        $dati = $this->dati['FASCICOLO'];
                        $titolario = $dati['livello1'] . $dati['livello2'] . $dati['livello3'];
                        $versione_t = $dati['versione'];

                        if ($this->proLibSerie->CtrSerieObbligatoria($titolario, $versione_t)) {
                            proRic::proRicSeriePerTitolario($this->nameForm, $titolario, $versione_t);
                        } else {
                            Out::msgInfo("Attenzione", $this->proLibSerie->getErrMessage());
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Procedimento':
                        $proc = $_POST[$this->nameForm . '_Procedimento'];
                        if ($proc) {
                            $proc = str_pad($proc, 6, "0", STR_PAD_LEFT);
                            $titolario = (int) $this->dati['livello1'];
                            if ($this->dati['livello2']) {
                                $titolario .= "." . (int) $this->dati['livello2'];
                            }
                            if ($this->dati['livello3']) {
                                $titolario .= "." . (int) $this->dati['livello3'];
                            }
                            $anapra_rec = $this->proLib->getAnapra($proc, 'codice', " AND PRACLA='$titolario'");
                            Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                            Out::valore($this->nameForm . '_Desc_proc', $anapra_rec['PRADES__1']);
                        }
                        break;
                    case $this->nameForm . '_ANAPRO[PROARG]':
                        $codice = str_pad($_POST[$this->nameForm . '_ANAPRO']['PROARG'], 6, "0", STR_PAD_LEFT);
                        $codiceCcf = $this->dati['livello1'] . $this->dati['livello2'] . $this->dati['livello3'];
                        if (!$this->DecodAnaorg($codice, 'codice', $codiceCcf, $_POST[$this->nameForm . '_Organn'])) {
                            Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                            Out::valore($this->nameForm . '_Organn', '');
                            Out::valore($this->nameForm . '_FascicoloDecod', '');
                        }
                        break;

                    case $this->nameForm . '_RES':
                        $this->DecodResponsabile($_POST[$this->nameForm . '_RES']);
                        break;
                    case $this->nameForm . '_SERIE[CODICE]':
                        if ($_POST[$this->nameForm . '_SERIE']['CODICE']) {
                            $dati = $this->dati;
                            $titolario = $dati['livello1'] . $dati['livello2'] . $dati['livello3'];
                            $versione_t = $dati['versione'];
                            if (!$titolario) {
                                Out::valore($this->nameForm . '_SERIE[CODICE]', '');
                                Out::valore($this->nameForm . '_SERIE[SIGLA]', '');
                                Out::valore($this->nameForm . '_SERIE[DESCRIZIONE]', '');
                                Out::msgInfo("Attenzione", "Occorre indicare il titolario per procedere.");
                                break;
                            }
                            if (!$this->proLibSerie->CtrSerieInTitolario($_POST[$this->nameForm . '_SERIE']['CODICE'], $titolario, $versione_t)) {
                                Out::msgInfo("Attenzione", $this->proLibSerie->getErrMessage());
                                break;
                            }
                            $this->DecodificaSerie($_POST[$this->nameForm . '_SERIE']['CODICE']);
                        }
                        break;
                }
                break;
            case 'returnAnapra':
                $anapra_rec = $this->proLib->getAnapra($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Procedimento', $anapra_rec['PRANUM']);
                Out::valore($this->nameForm . '_Desc_proc', $anapra_rec['PRADES__1']);
                break;
            case 'returnorg':
                if (!$this->DecodAnaorg($_POST['retKey'], 'rowid')) {
                    Out::valore($this->nameForm . '_ANAPRO[PROARG]', '');
                    Out::valore($this->nameForm . '_Organn', '');
                    Out::valore($this->nameForm . '_FascicoloDecod', '');
                }
                break;

            case 'returnanamedMittPartenza' : $anamed_rec = $this->proLib->GetAnamed($_POST ['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_RES', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_RESPONSABILE", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_UFF", '');
                Out::valore($this->nameForm . "_UFFRESP", '');
                Out::setFocus('', $this->nameForm . "_RES");
                break;

            case 'returnUfficiPerDestinatarioFirmatario' : $anauff_rec = $this->proLib->GetAnauff($_POST ['retKey'], 'rowid');
                Out::valore($this->nameForm . '_UFF', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_RES");
                break;


            case 'returnSerieTitolario':
                $tabella_rec = $_POST['rowData'];
                $this->DecodificaSerie($tabella_rec['CODICE']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_dati');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function caricaDati($livello1, $livello2, $livello3, $descTitolo, $prouof, $rowid_protocollo = '') {
        if ($livello1 . $livello2 . $livello3 != '' && $prouof != '') {
            $profilo = proSoggetto::getProfileFromIdUtente();
            if ($rowid_protocollo) {
                $headerMsg = '<b>Fascicolazione del protocollo </b>';
                $Anapro_rec = $this->proLib->GetAnapro($rowid_protocollo, 'rowid');
                if ($Anapro_rec['PROPAR'] == 'I') {
                    $headerMsg = '<b>Fascicolazione documentale </b>';
                }

                $Protoc = substr($Anapro_rec['PRONUM'], 4) . '/' . substr($Anapro_rec['PRONUM'], 0, 4) . ' ' . $Anapro_rec['PROPAR'];
                if ($Anapro_rec['PROPAR'] == 'I') {
                    $tipodoc = 'Documento';
                    $segLib = new segLib();
                    $Indice_rec = $segLib->GetIndice($Anapro_rec['PRONUM'], 'anapro', false, $Anapro_rec['PROPAR']);
                    $dizionarioIdelib = $segLib->getDizionarioFormIdelib($Indice_rec['INDTIPODOC'], $Indice_rec);
                    $NumeroInd = $dizionarioIdelib['PROGRESSIVO'];
                    $AnnoInd = substr($Indice_rec['IDATDE'], 0, 4);
                    $headerMsg .= "<br>" . $Indice_rec['INDTIPODOC'] . ": " . $NumeroInd . " / $AnnoInd ";
                } else {
                    $tipodoc = 'Protocollo';
                    $headerMsg .= "<br>Protocollo: " . $Protoc;
                }
            } else {
                $headerMsg = '<b>Creazione nuovo fascicolo</b>';
            }
            $Anauff_rec = $this->proLib->GetAnauff($prouof, 'codice');
            if ($Anauff_rec['UFFRES']) {
                $this->DecodResponsabile($Anauff_rec['UFFRES'], 'codice', $prouof);
            } else {
                $this->DecodResponsabile($profilo['COD_SOGGETTO'], 'codice', $prouof);
            }

            $descTitolo = $this->DecodificaTitolario($this->dati);
            $headerMsg .= "<br>Titolario Originale del $tipodoc: " . $this->getMessage($livello1, $livello2, $livello3, $descTitolo['DESCRIZIONE']);
            if (!$profilo['COD_SOGGETTO']) {
                Out::msgStop("Attenzione!", "Configurare il Profilo Utente con il Destinatario della Pianta Organica.");
                return false;
            }
            $anamed_rec = $this->proLib->GetAnamed($profilo['COD_SOGGETTO']);
            $ufficio = $this->proLib->GetAnauff($prouof);
            $headerMsg .= "<br>Utente: {$anamed_rec['MEDNOM']}";
            $headerMsg .= "<br>Ufficio: {$ufficio['UFFDES']}";

            $headerMsg .= "<br><br>Dati del nuovo fascicolo :";

            $DescTitolario = $this->DecodificaTitolario($this->dati['FASCICOLO']);
            $headerMsg .= "<br>Titolario:" . $this->getMessage($this->dati['FASCICOLO']['livello1'], $this->dati['FASCICOLO']['livello2'], $this->dati['FASCICOLO']['livello3'], $DescTitolario['DESCRIZIONE']);
            Out::html($this->nameForm . "_divInfo", $headerMsg . '<br><br>Inserire la descrizione per il <u>nuovo fascicolo</u>.<br><br>');
        } else {
            Out::msgStop("Attenzione!", "Un fascicolo può essere aperto solo in presenza di un Titolario e di una unità operativa.");
            return false;
        }
        return true;
    }

    private function getMessage($livello1, $livello2, $livello3, $descTitolo) {
        $headerMsg = $livello1;
        if ($livello2) {
            $headerMsg .= "." . $livello2;
        }
        if ($livello3) {
            $headerMsg .= "." . $livello3;
        }
        $headerMsg .= " - " . $descTitolo;
        return $headerMsg;
    }

    function DecodAnaorg($codice, $tipo = 'codice', $codiceCcf = '', $anno = '') {
        $anaorg_rec = $this->proLib->GetAnaorg($codice, $tipo, $codiceCcf, $anno);
        if ($anaorg_rec) {
            Out::valore($this->nameForm . '_ANAPRO[PROARG]', $anaorg_rec['ORGCOD']);
            Out::valore($this->nameForm . '_Organn', $anaorg_rec['ORGANN']);
            Out::valore($this->nameForm . '_FascicoloDecod', $anaorg_rec['ORGDES']);
        } else {
            Out::valore($this->nameForm . '_FascicoloDecod', '');
            return false;
        }
        return true;
    }

    private function DecodResponsabile($codice, $tipoRic = 'codice', $uffcod = '') {
        if (trim($codice) != "") {
            if (is_numeric($codice)) {
                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipoRic, 'no');
            if (!$anamed_rec) {
                Out::valore($this->nameForm . '_RES', '');
                Out::valore($this->nameForm . '_RESPONSABILE', '');
                Out::setFocus('', $this->nameForm . "_RESPONSABILE");
                return;
            } else {
                Out::valore($this->nameForm . '_RES', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_RESPONSABILE', $anamed_rec['MEDNOM']);

                if ($uffcod) {
                    $anauff_rec = $this->proLib->GetAnauff($uffcod);
                    Out::valore($this->nameForm . '_UFF', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    return;
                } else {
                    $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                    if (count($uffdes_tab) == 1) {
                        $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                        Out::valore($this->nameForm . '_UFF', $anauff_rec['UFFCOD']);
                        Out::valore($this->nameForm . '_UFFRESP', $anauff_rec['UFFDES']);
                    } else {
                        if ($_POST[$this->nameForm . '_UFF'] == '' || $_POST[$this->nameForm . '_UFFRESP'] == '') {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            Out::setFocus('', "utiRicDiag_gridRis");
                            return;
                        }
                    }
                }
            }
        } else {
            Out::valore($this->nameForm . '_RES', '');
            Out::valore($this->nameForm . '_RESPONSABILE', '');
        }
        Out::setFocus('', $this->nameForm . "_RESPONSABILE");
    }

    private function GetUfficiAnamed($codice) {
        return $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND UFFDES.UFFCESVAL='' AND ANAUFF.UFFANN=0");
    }

    private function ControlloDestinatario($Destinatario, $Ufficio) {
        if ($Destinatario || $Ufficio) {
            if ($Destinatario && !$Ufficio) {
                Out::msgInfo("Attenzione", "Occorre indicare anche l'ufficio del responsabile.");
                return false;
            }
            if (!$Destinatario && $Ufficio) {
                Out::msgInfo("Attenzione", "Occorre indicare anche il responsabile.");
                return false;
            }
            $uffdes_tab = $this->GetUfficiAnamed($Destinatario);
            $trovato = false;
            foreach ($uffdes_tab as $ufficio) {
                if ($ufficio['UFFCOD'] == $Ufficio) {
                    $trovato = true;
                    break;
                }
            }
            if (!$trovato) {
                Out::msgInfo("Attenzione", "Il destinatario non fa pare dell'ufficio indicato.");
                return false;
            }
        } else {
            Out::msgInfo("Attenzione", "Indicare il destinatario e il suo ufficio per poter procedere.");
            return false;
        }
        return true;
    }

    public function DecodificaTitolario($Titolario) {
        $DescTitolario = array();
        $cat = $cla = $fas = $org = $des = '';
        if ($Titolario) {
            if ($Titolario['livello1']) {
                $anacat_rec = $this->proLib->GetAnacat($Titolario['versione'], $Titolario['livello1'], 'codice');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
            }
            if ($Titolario['livello2']) {
                $anacla_rec = $this->proLib->GetAnacla($Titolario['versione'], $Titolario['livello1'] . $Titolario['livello2'], 'codice');
                if ($anacla_rec) {
                    $cat = $anacla_rec['CLACAT'];
                    $cla = $anacla_rec['CLACOD'];
                    $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                }
            }
            if ($Titolario['livello3']) {
                $anafas_rec = $this->proLib->GetAnafas($Titolario['versione'], $Titolario['livello1'] . $Titolario['livello2'] . $Titolario['livello3']);
                if ($anafas_rec) {
                    $cat = substr($anafas_rec['FASCCA'], 0, 4);
                    $cla = substr($anafas_rec['FASCCA'], 4);
                    $fas = $anafas_rec['FASCOD'];
                    $des = $anafas_rec['FASDES'];
                }
            }
            $DescTitolario['CODICE'] = $cat . $cla . $fas;
            $DescTitolario['DESCRIZIONE'] = $des;
            $this->VisualizzaNascondiSerie($DescTitolario['CODICE'], $Titolario['versione']);
        }
        return $DescTitolario;
    }

    public function BloccaSerie() {
        Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '0');
        Out::addClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        Out::disableField($this->nameForm . '_SERIE[CODICE]');
    }

    public function SbloccaSerie() {
        Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
        Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        Out::enableField($this->nameForm . '_SERIE[CODICE]');
    }

    public function DecodificaSerie($codiceSerie, $anaorg_rec = array()) {
        Out::clearFields($this->nameForm . '_divSerie');
        $Serie_rec = $this->proLibSerie->GetSerie($codiceSerie, 'codice');
        if (!$Serie_rec) {
            Out::msgStop("Attenzione", "Serie non trovata.");
            return false;
        }
        // Decodifico le serie:
        Out::valori($Serie_rec, $this->nameForm . '_SERIE');
        if ($Serie_rec['TIPOPROGRESSIVO'] == 'MANUALE' && !$anaorg_rec['PROGSERIE']) {
            Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '1');
            Out::delClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        } else {
            Out::attributo($this->nameForm . '_SERIE[PROGSERIE]', "readonly", '0');
            Out::addClass($this->nameForm . '_SERIE[PROGSERIE]', "ita-readonly");
        }
        if ($anaorg_rec) {
            // Valorizzazione progressivo serie, se presente.
            Out::valore($this->nameForm . '_SERIE[PROGSERIE]', $anaorg_rec['PROGSERIE']);
        }
    }

    public function CreaCombo() {
        // Combo Natrua fascicolo
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "", "0", "Digitale");
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "1", "0", "Cartaceo");
        Out::select($this->nameForm . '_ANAORG[NATFAS]', 1, "2", "0", "Ibrido");
    }

    public function VisualizzaNascondiSerie($titolario, $versione = '*') {
        if ($versione == '*') {
            $versione = $this->proLib->GetTitolarioCorrente();
        }
        if (!$this->proLibSerie->CtrSerieObbligatoria($titolario, $versione)) {
            Out::hide($this->nameForm . '_divSerie');
        } else {
            Out::show($this->nameForm . '_divSerie');
        }
    }

}

?>