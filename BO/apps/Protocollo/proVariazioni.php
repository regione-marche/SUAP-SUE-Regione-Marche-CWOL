<?php

/**
 *
 * Elenco Variazioni
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    19.07.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proVariazioni() {
    $proVariazioni = new proVariazioni();
    $proVariazioni->parseEvent();
    return;
}

class proVariazioni extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proVariazioni";
    public $divDettaglio = "proVariazioni_divDettaglio";
    public $gridVariazioni = "proVariazioni_gridVariazioni";
    public $variazioni = array();
    public $pronum = array();
    public $propar = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->variazioni = App::$utente->getKey($this->nameForm . "_variazioni");
        $this->pronum = App::$utente->getKey($this->nameForm . "_pronum");
        $this->propar = App::$utente->getKey($this->nameForm . "_propar");
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_variazioni", $this->variazioni);
            App::$utente->setKey($this->nameForm . "_pronum", $this->pronum);
            App::$utente->setKey($this->nameForm . "_propar", $this->propar);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->pronum = $_POST['pronum'];
                $this->propar = $_POST['propar'];
                $this->caricaElencoVariazioni();
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridVariazioni:
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridVariazioni:
                        switch ($_POST['colName']) {
                            case 'ALLEGATI':
                                // qui vedere gli allegati
                                break;
                            case 'DETTAGLIOALLEGATI':
                                $var = trim($_POST['cellContent']);
                                if ($_POST['cellContent'] != ' ') {
                                    $rowidAnaprosave = $_POST['rowid'];
                                    // Apertura variazioni allegati
                                    itaLib::openForm('proArriAllegati');
                                    /* @var $proArriAllegatiObj proArriAllegati */
                                    $proArriAllegatiObj = itaModel::getInstance('proArriAllegati');
                                    $proArriAllegatiObj->setEvent('openAnaprosave');
                                    $proArriAllegatiObj->setIndiceRowid($rowidAnaprosave);
                                    $proArriAllegatiObj->setReturnModel($this->nameForm);
                                    $proArriAllegatiObj->setReturnEvent('returnFromVariazioniAllegati');
                                    $proArriAllegatiObj->setReturnId('');
                                    $proArriAllegatiObj->parseEvent();
                                }
                                break;
                        }break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Carica':
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_variazioni');
        App::$utente->removeKey($this->nameForm . '_pronum');
        App::$utente->removeKey($this->nameForm . '_propar');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent() {
        $this->close();
    }

    function CaricaGriglia() {
        $appoggio = array();
        foreach ($this->variazioni as $value) {
            $appoggio[] = $value;
        }
        $this->variazioni = $appoggio;
        TableView::enableEvents($this->gridVariazioni);
        TableView::clearGrid($this->gridVariazioni);
        if ($this->variazioni) {
            $ita_grid01 = new TableView(
                    $this->gridVariazioni, array('arrayTable' => $this->variazioni,
                'rowIndex' => 'idx')
            );
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows('10000');
            $ita_grid01->getDataPage('json');
        }
        return;
    }

    private function caricaElencoVariazioni() {
        $anapro_rec = $this->proLib->GetAnapro($this->pronum, "codice", $this->propar);
        $anaprosave_tab = $this->proLib->getGenericTab("SELECT * FROM ANAPROSAVE WHERE PRONUM=$this->pronum AND PROPAR='" . $anapro_rec['PROPAR'] . "' ORDER BY SAVEDATA, SAVEORA"); //OR PROPAR='" . $anapro_rec['PROPAR'] . "A') 
        $data = $anapro_rec['PRODAR'];
        $ora = $anapro_rec['PROORA'];
        $motivo = "- Originale -";
        foreach ($anaprosave_tab as $anaprosave_rec) {
            $where = "=$this->pronum AND SAVEDATA='" . $anaprosave_rec['SAVEDATA'] . "' AND SAVEORA='" . $anaprosave_rec['SAVEORA'] . "'";
            $anadessave_tab = $this->proLib->getGenericTab("SELECT * FROM ANADESSAVE WHERE DESPAR='$this->propar' AND DESNUM $where AND DESTIPO<>'M' ORDER BY SAVEDATA, SAVEORA");
            $anades_mitt_save_rec = $this->proLib->getGenericTab("SELECT * FROM ANADESSAVE WHERE DESPAR='$this->propar' AND DESNUM $where AND DESTIPO='M' ORDER BY SAVEDATA, SAVEORA", false);
            $anadocsave_tab = $this->proLib->getGenericTab(
                    "SELECT * FROM ANADOCSAVE WHERE DOCKEY LIKE '" . $this->pronum . $anapro_rec['PROPAR'] . "%' 
                        AND SAVEDATA='" . $anaprosave_rec['SAVEDATA'] . "' AND SAVEORA='" . $anaprosave_rec['SAVEORA'] . "' 
                            ORDER BY SAVEDATA, SAVEORA");
            $anaoggsave_rec = $this->proLib->getGenericTab("SELECT * FROM ANAOGGSAVE WHERE OGGPAR='$this->propar' AND OGGNUM $where ORDER BY SAVEDATA, SAVEORA", false);
            $promitaggsave_tab = $this->proLib->getGenericTab("SELECT * FROM PROMITAGGSAVE WHERE PROPAR='$this->propar' AND PRONUM $where ORDER BY SAVEDATA, SAVEORA");
            $uffprosave_tab = $this->proLib->getGenericTab("SELECT * FROM UFFPROSAVE WHERE UFFPAR='$this->propar' AND PRONUM $where ORDER BY SAVEDATA, SAVEORA");
            $data = $anaprosave_rec['SAVEDATA'];
            $ora = $anaprosave_rec['SAVEORA'];
            $utente = $anaprosave_rec['SAVEUTENTE'];
            $motivo = $anaprosave_rec['SAVEMOTIVAZIONE'];
            $variazione = $this->caricaSave($anaprosave_rec, $anadessave_tab, $anadocsave_tab, $anaoggsave_rec, $promitaggsave_tab, $uffprosave_tab, $data, $ora, $motivo, $anades_mitt_save_rec);
// Spostato sopra perchè invertiva le date.
//            $data = $anaprosave_rec['SAVEDATA'];
//            $ora = $anaprosave_rec['SAVEORA'];
//            $utente = $anaprosave_rec['SAVEUTENTE'];
//            $motivo = $anaprosave_rec['SAVEMOTIVAZIONE'];
            $this->variazioni[] = $variazione;
        }
        $this->variazioni[] = $this->caricaOriginale($anapro_rec, '', '', '', 'Situazione Attuale');
        $this->CaricaGriglia();
    }

    private function caricaOriginale($anapro_rec, $data, $ora, $utente, $motivo) {
        $elencoMittenti = '';
        $elencoDestinatari = '';
        $elencoUffici = '';
        $elencoAllegati = '';
        $titolario = '';
        if ($anapro_rec['PROPAR'] == 'P') {
            $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
            $elencoMittenti = $anades_mitt['DESCOD'] . ' ' . $anades_mitt['DESNOM'];
            $elencoDestinatari = $anapro_rec['PRONOM'] . ' ' . $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCAP'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
        } else {
            $elencoMittenti = $anapro_rec['PRONOM'] . ' ' . $anapro_rec['PROIND'] . ' ' . $anapro_rec['PROCAP'] . ' ' . $anapro_rec['PROCIT'] . ' ' . $anapro_rec['PROPRO'];
            $mittentiAggiuntivi = $this->proLib->getPromitagg($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
            foreach ($mittentiAggiuntivi as $mitAgg) {
                $elencoMittenti.='<br>' . $mitAgg['PRONOM'] . ' ' . $mitAgg['PROIND'] . ' ' . $mitAgg['PROCAP'] . ' ' . $mitAgg['PROCIT'] . ' ' . $mitAgg['PROPRO'];
            }
        }
        $destinatari = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
        foreach ($destinatari as $destinatario) {
            if ($elencoDestinatari) {
                $elencoDestinatari.='<br>';
            }
            if ($anapro_rec['PROPAR'] == 'P') {
                $elencoDestinatari .= $destinatario['DESNOM'] . ' ' . $destinatario['DESIND'] . ' ' . $destinatario['DESCAP'] . ' ' . $destinatario['DESCIT'] . ' ' . $destinatario['DESCIT'];
            } else {
                $elencoDestinatari .= $destinatario['DESNOM'];
            }
        }
        $uffici = $this->proLib->GetUffpro($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
        foreach ($uffici as $ufficio) {
            if ($elencoUffici) {
                $elencoUffici.='<br>';
            }
            $anauff_rec = $this->proLib->GetAnauff($ufficio['UFFCOD']);
            $elencoUffici.=$ufficio['UFFCOD'] . ' ' . $anauff_rec['UFFDES'];
        }

        $allegati = $this->proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
        foreach ($allegati as $anadoc_rec) {
            if ($elencoAllegati) {
                $elencoAllegati.='<br>';
            }
            if ($anadoc_rec['DOCNAME']) {
                $elencoAllegati .= $anadoc_rec['DOCNAME'];
            } else {
                $elencoAllegati .= $anadoc_rec['DOCFIL'];
            }
        }

        $anacat_rec = $this->proLib->GetAnacat($anapro_rec['VERSIONE_T'], $anapro_rec['PROCAT'], 'codice');
        $anacla_rec = $this->proLib->GetAnacla($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCA'], 'codice');
        $anafas_rec = $this->proLib->GetAnafas($anapro_rec['VERSIONE_T'], $anapro_rec['PROCCF'], 'fasccf');
        if ($anacat_rec) {
            if ($anacla_rec) {
                if ($anafas_rec) {
                    $cat = substr($anafas_rec['FASCCA'], 0, 4);
                    $cla = substr($anafas_rec['FASCCA'], 4);
                    $fas = $anafas_rec['FASCOD'];
                    $des = $anafas_rec['FASDES'];
                    $titolario = $cat . '.' . $cla . '.' . $fas . " " . $des;
                } else {
                    $cat = $anacla_rec['CLACAT'];
                    $cla = $anacla_rec['CLACOD'];
                    $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                    $titolario = $cat . '.' . $cla . " " . $des;
                }
            } else {
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                $titolario = $cat . " " . $des;
            }
        }

        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $recordOrig = $anapro_rec;
        $recordOrig['SAVEMOTIVAZIONE'] = $motivo;
        $recordOrig['SAVEUTENTE'] = $utente;
        $recordOrig['SAVEDATA'] = $data;
        $recordOrig['SAVEORA'] = $ora;
        $recordOrig['OGGOGG'] = $anaogg_rec['OGGOGG'];
        $recordOrig['MITTENTI'] = $elencoMittenti;
        $recordOrig['DESTINATARI'] = $elencoDestinatari;
        $recordOrig['UFFICI'] = $elencoUffici;
        $recordOrig['ALLEGATI'] = $elencoAllegati;
        $recordOrig['TITOLARIO'] = $titolario;
        $recordOrig['DETTAGLIOALLEGATI'] = ' ';
        return $recordOrig;
    }

    private function caricaSave($anaprosave_rec, $anadessave_tab, $anadocsave_tab, $anaoggsave_rec, $promitaggsave_tab, $uffprosave_tab, $data, $ora, $motivo, $anades_mitt_save_rec) {
        $elencoMittenti = '';
        $elencoDestinatari = '';
        $elencoUffici = '';
        $elencoAllegati = '';
        $titolario = '';
        if ($anaprosave_rec['PROPAR'] == 'P') {
            $elencoMittenti = $anades_mitt_save_rec['DESCOD'] . ' ' . $anades_mitt_save_rec['DESNOM'];
            $elencoDestinatari = $anaprosave_rec['PRONOM'] . ' ' . $anaprosave_rec['PROIND'] . ' ' . $anaprosave_rec['PROCAP'] . ' ' . $anaprosave_rec['PROCIT'] . ' ' . $anaprosave_rec['PROPRO'];
        } else {
            $elencoMittenti = $anaprosave_rec['PRONOM'] . ' ' . $anaprosave_rec['PROIND'] . ' ' . $anaprosave_rec['PROCAP'] . ' ' . $anaprosave_rec['PROCIT'] . ' ' . $anaprosave_rec['PROPRO'];
            foreach ($promitaggsave_tab as $mitAgg) {
                $elencoMittenti.='<br>' . $mitAgg['PRONOM'] . ' ' . $mitAgg['PROIND'] . ' ' . $mitAgg['PROCAP'] . ' ' . $mitAgg['PROCIT'] . ' ' . $mitAgg['PROPRO'];
            }
        }
        foreach ($anadessave_tab as $destinatario) {
            if ($elencoDestinatari) {
                $elencoDestinatari.='<br>';
            }
            if ($anaprosave_rec['PROPAR'] == 'P') {
                $elencoDestinatari .= $destinatario['DESNOM'] . ' ' . $destinatario['DESIND'] . ' ' . $destinatario['DESCAP'] . ' ' . $destinatario['DESCIT'] . ' ' . $destinatario['DESCIT'];
            } else {
                $elencoDestinatari .= $destinatario['DESNOM'];
            }
        }
        foreach ($uffprosave_tab as $ufficio) {
            if ($elencoUffici) {
                $elencoUffici.='<br>';
            }
            $anauff_rec = $this->proLib->GetAnauff($ufficio['UFFCOD']);
            $elencoUffici.=$ufficio['UFFCOD'] . ' ' . $anauff_rec['UFFDES'];
        }
        foreach ($anadocsave_tab as $anadoc_rec) {
            if ($elencoAllegati) {
                $elencoAllegati.='<br>';
            }
            if ($anadoc_rec['DOCNAME']) {
                $elencoAllegati .= $anadoc_rec['DOCNAME'];
            } else {
                $elencoAllegati .= $anadoc_rec['DOCFIL'];
            }
        }

        $anacat_rec = $this->proLib->GetAnacat('', $anaprosave_rec['PROCAT'], 'codice');
        $anacla_rec = $this->proLib->GetAnacla('', $anaprosave_rec['PROCCA'], 'codice');
        $anafas_rec = $this->proLib->GetAnafas('', $anaprosave_rec['PROCCF'], 'fasccf');
        if ($anacat_rec) {
            if ($anacla_rec) {
                if ($anafas_rec) {
                    $cat = substr($anafas_rec['FASCCA'], 0, 4);
                    $cla = substr($anafas_rec['FASCCA'], 4);
                    $fas = $anafas_rec['FASCOD'];
                    $des = $anafas_rec['FASDES'];
                    $titolario = $cat . '.' . $cla . '.' . $fas . " " . $des;
                } else {
                    $cat = $anacla_rec['CLACAT'];
                    $cla = $anacla_rec['CLACOD'];
                    $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                    $titolario = $cat . '.' . $cla . " " . $des;
                }
            } else {
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                $titolario = $cat . " " . $des;
            }
        }

        $recordOrig = $anaprosave_rec;
        $recordOrig['SAVEMOTIVAZIONE'] = $anaprosave_rec['SAVEMOTIVAZIONE'];
        $recordOrig['SAVEUTENTE'] = $anaprosave_rec['SAVEUTENTE']; // BugFix: utente ultima modifica.
        $recordOrig['SAVEDATA'] = $data;
        $recordOrig['SAVEORA'] = $ora;
        $recordOrig['OGGOGG'] = $anaoggsave_rec['OGGOGG'];
        $recordOrig['MITTENTI'] = $elencoMittenti;
        $recordOrig['DESTINATARI'] = $elencoDestinatari;
        $recordOrig['UFFICI'] = $elencoUffici;
        $recordOrig['ALLEGATI'] = $elencoAllegati;
        $DettAllegati = ' ';
        if ($elencoAllegati) {
            $DettAllegati = "<span title = \"Dettaglio Allegati.\" class=\"ita-icon ita-icon-cerca-24x24\" style = \"float:left;display:inline-block;\"></span>";
        }
        $recordOrig['DETTAGLIOALLEGATI'] = $DettAllegati;
        $recordOrig['TITOLARIO'] = $titolario;
        return $recordOrig;
    }

}

?>