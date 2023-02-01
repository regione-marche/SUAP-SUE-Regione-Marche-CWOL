<?php

/**
 *
 * RINUMERA PRATICA
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praImmobili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

function praRinumeraPratica() {
    $praRinumeraPratica = new praRinumeraPratica();
    $praRinumeraPratica->parseEvent();
    return;
}

class praRinumeraPratica extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praImmobili;
    public $praSoggetti;
    public $proRic;
    public $proLibSerie;
    public $nameForm = "praRinumeraPratica";
    public $divRicIdentificativo = "praRinumeraPratica_divRicercaIdentificativo";
    public $divRicNumero = "praRinumeraPratica_divRicercaPratica";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->proRic = new proRic();
            $this->proLibSerie = new proLibSerie();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->OpenRicerca();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaNumero':
                        //$pratica = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . "_Identificativo"]))) . trim($_POST[$this->nameForm . "_Identificativo"]);
                        $this->GetMsgConfermaNumero($_POST[$this->nameForm . '_AnnoSerie'], $_POST[$this->nameForm . '_Numero'], $_POST[$this->nameForm . '_ric_codiceserie'], $_POST[$this->nameForm . '_NumeroDest']);
                        break;
                    case $this->nameForm . '_ConfermaIdentificativo':
                        $pratica = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . "_Identificativo"]))) . trim($_POST[$this->nameForm . "_Identificativo"]);
                        $this->GetMsgConfermaIdentificativo($_POST[$this->nameForm . "_Anno"] . $pratica);
                        break;
                    case $this->nameForm . '_bttn_numero':
                        $this->GetRequiredNumero(); // obbligatorietà campi per div visualizzato
                        $this->Nascondi();
                        $this->CreaCombo();
                        Out::show($this->nameForm . '_ConfermaNumero');
                        Out::show($this->divRicNumero, '');
                        Out::setFocus('', $this->nameForm . '_Numero');
                        $this->GetRequiredIdentificativo(false); //non obbligatorietà campi per div visualizzato
                        break;
                    case $this->nameForm . '_bttn_identificativo':
                        $this->GetRequiredIdentificativo(); // obbligatorietà campi per div visualizzato
                        $this->Nascondi();
                        $this->CreaCombo();
                        Out::show($this->nameForm . '_ConfermaIdentificativo');
                        Out::clearFields($this->nameForm, $this->divRicIdentificativo);
                        Out::show($this->divRicIdentificativo, '');
                        Out::setFocus('', $this->nameForm . '_Identificativo');
                        $this->GetRequiredNumero(false); // non obbligatorietà campi per div visualizzato
                        break;
                    case $this->nameForm . '_Torna':
                        $this->OpenRicerca();
                        Out::show($this->nameForm . '_divIniziale');
                        Out::show($this->nameForm . '_divInfo');
                        break;
                    case $this->nameForm . '_Anno_butt':
//                        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//                        $where = "";
//                        if ($retVisibilta['SPORTELLO'] != 0 && $retVisibilta['SPORTELLO'] != 0) {
//                            $where .= " AND GESTSP = " . $retVisibilta['SPORTELLO'];
//                        }
//                        if ($retVisibilta['AGGREGATO'] && $retVisibilta['AGGREGATO'] != 0) {
//                            $where .= " AND GESSPA = " . $retVisibilta['AGGREGATO'];
//                        }
                        $where = $this->praLib->GetWhereVisibilitaSportello();
                        praRic::praRicProges($this->nameForm, $where);
                        break;
                    case $this->nameForm . "_returnPasswordRINUMERA":
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $ret = $this->RinumeraIdentificativo();
                        if ($ret) {
                           Out::msgInfo("Rinumerazione Identificativo", "Identificativo n. " . $ret['praticaSorg'] . " rinumerata correttamente in " . $ret['praticaDest']);
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . "_returnPasswordRINUMERASERIE":
                        if (!$this->praLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            break;
                        }
                        $ret = $this->RinumeraNumero();
                        if ($ret) {
                            Out::msgInfo("Rinumerazione Pratica", "pratica n. " . $ret['praticaSorg'] . " rinumerata correttamente in " . $ret['praticaDest']);
                            $this->OpenRicerca();
                        }
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_ric_siglaserie_butt':
                        proRic::proRicSerieArc($this->nameForm);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Identificativo':
                        $Dal_num = $_POST[$this->nameForm . '_Identificativo'];
                        if ($Dal_num) {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Identificativo', $Dal_num);
                        }
                        break;
                    case $this->nameForm . '_IdentificativoDest':
                        $num = $_POST[$this->nameForm . '_IdentificativoDest'];
                        if ($num) {
                            $num = str_pad($num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_IdentificativoDest', $num);
                        }
                        break;
                    case $this->nameForm . '_Anno':
                        $Dal_num = $_POST[$this->nameForm . '_Identificativo'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if ($anno != '' && $Dal_num != '') {
                            $this->GetMsgConfermaIdentificativo($anno . $Dal_num);
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ric_siglaserie':
                        if ($_POST[$this->nameForm . '_ric_siglaserie']) {
                            $AnaserieArc_tab = $this->proLibSerie->GetSerie($_POST[$this->nameForm . '_ric_siglaserie'], 'sigla', true);
                            if (!$AnaserieArc_tab) {
                                Out::msgStop("Attenzione", "Sigla Inesistente.");
                                Out::valore($this->nameForm . '_ric_codiceserie', '');
                                Out::valore($this->nameForm . '_ric_siglaserie', '');
                                Out::valore($this->nameForm . '_descRicSerie', '');
                                break;
                            }
                            $result = count($AnaserieArc_tab);
                            if ($result > 1) {
                                $where = "WHERE " . $this->proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($AnaserieArc_tab[0]['SIGLA']) . "'";
                                proRic::proRicSerieArc($this->nameForm, $where);
                                break;
                            }
                            Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_tab[0]['CODICE']);
                            Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_tab[0]['SIGLA']);
                            Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_tab[0]['DESCRIZIONE']);
                            break;
                        }
                        break;
                }
                break;
            case 'returnProges':
                //$this->DecodProges($_POST['retKey'], 'rowid');
                $this->DecodProges($_POST['rowData']['ROWID'], 'rowid');
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                }
                break;
        }
    }

    public function close() {
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function OpenRicerca() {
        // Out::clearFields($this->nameForm, $this->divRicIdentificativo);
        //Out::show($this->divRicIdentificativo, '');
        $this->Nascondi();
        Out::show($this->nameForm . '_bttn_numero');
        Out::show($this->nameForm . '_bttn_identificativo');
        Out::setFocus('', $this->nameForm . '_Identificativo');
        $retVisibilta = $this->praLib->GetVisibiltaSportello();
        Out::show($this->nameForm . '_divInfo');
        Out::show($this->nameForm . '_divIniziale');
        Out::html($this->nameForm . "_divInfo", "Sportelli On-line Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['SPORTELLO_DESC'] . "</span> Aggregati Visibili: <span style=\"font-weight:bold;\">" . $retVisibilta['AGGREGATO_DESC'] . "</span>");
        Out::html($this->nameForm . "_divIniziale", "Premi Rinumera Pratica per modificare il numero Pratica per la serie del Fascisolo<br>Premi Rinumera Identificativo per modificare numero identificativo [GESNUM] per relazioni Fascicolo<br><br><br>");
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_bttn_numero');
        Out::hide($this->nameForm . '_bttn_identificativo');
        Out::hide($this->nameForm . '_ConfermaIdentificativo');
        Out::hide($this->nameForm . '_ConfermaNumero');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divRicercaIdentificativo');
        Out::hide($this->nameForm . '_divRicercaPratica');
    }

    public function CreaCombo() {
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divInfo');
        Out::hide($this->nameForm . '_divIniziale');
        Out::clearFields($this->nameForm, $this->divRicIdentificativo);
        Out::clearFields($this->nameForm, $this->divRicNumero);
    }

    function GetMsgConfermaIdentificativo($pratica) {
        $proges_rec = $this->praLib->GetProges($pratica);
        if (!$proges_rec) {
            Out::msgStop("Attenzione", "Identificativo non trovato");
            return false;
        }

        $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
        if (!$ret) {
            Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti");
            return false;
        }
        $html = $this->praLib->GetHtmlCancellaPratica($proges_rec);
        $this->praLib->GetMsgInputPassword($this->nameForm, "Rinumerazione Pratica", "RINUMERA", $html);
    }

    function GetMsgConfermaNumero($anno, $numero, $serie, $destinazione) {
        $proges_rec = $this->praLib->GetProgesSerie($anno, $numero, $serie);
        if (!$proges_rec) {
            Out::msgStop("Attenzione", "Pratica non trovata");
            return false;
        }

        $ret = $this->praLib->checkVisibilitaSportello(array('SPORTELLO' => $proges_rec['GESTSP'], 'AGGREGATO' => $proges_rec['GESSPA']), $this->praLib->GetVisibiltaSportello());
        if (!$ret) {
            Out::msgStop("Attenzione", "Pratica non Visibile.<br>Controllare le impostazioni di visibilita nella scheda Pianta Organica ---> Dipendenti");
            return false;
        }
        $html = $this->praLib->GetHtmlCancellaPratica($proges_rec);
        $this->praLib->GetMsgInputPassword($this->nameForm, "Rinumerazione Pratica", "RINUMERASERIE", $html);
    }

    function RinumeraIdentificativo() {
        $pratica = $_POST[$this->nameForm . "_Identificativo"];
        $pratica = $_POST[$this->nameForm . "_Anno"] . str_repeat("0", 6 - strlen(trim($pratica))) . trim($pratica);
        $newNumero = str_repeat("0", 6 - strlen(trim($_POST[$this->nameForm . "_IdentificativoDest"]))) . trim($_POST[$this->nameForm . "_IdentificativoDest"]);
        //$anno = substr($pratica, 0, 4);
        $anno = $_POST[$this->nameForm . "_AnnoDest"];
        $this->praImmobili = praImmobili::getInstance($this->praLib, $pratica);
        $this->praSoggetti = praSoggetti::getInstance($this->praLib, $pratica);


        $proges_rec_newNumero = $this->praLib->GetProges($anno . $newNumero);
        if ($proges_rec_newNumero) {
            Out::msgStop("Errore", "Il numero Identificativo $anno$newNumero è gia presente.<br>Scegliere un nuovo numero");
            return false;
        }

        $pratPath = $this->praLib->SetDirectoryPratiche(substr($pratica, 0, 4), $pratica, 'PROGES', false);
        $newPratPath = $this->praLib->SetDirectoryPratiche(substr($anno . $newNumero, 0, 4), $anno . $newNumero, 'PROGES');
        if (is_dir($pratPath)) {
            if (!rename($pratPath, $newPratPath)) {
                Out::msgStop("Rinumera Identificativo", "Errore nel rinumerare la cartella di lavoro dell'Identificativo $pratica");
                return false;
            }
        }

        $proges_rec = $this->praLib->GetProges($pratica, 'codice');
        $prasta_rec = $this->praLib->GetPrasta($pratica, 'codice');
        $propas_tab = $this->praLib->GetPropas($pratica, 'codice', true);
        $prodst_tab = $this->praLib->GetProdst($pratica, 'numero', true);
        $pracom_tab = $this->praLib->GetPracom($pratica, 'numero', true);
        $praMitDest_tab = $this->praLib->GetPraMitDest($pratica, 'numero', true);
        $prodag_tab = $this->praLib->GetProdag($pratica, 'numero', true);
        $pasdoc_tab = $this->praLib->GetPasdoc($pratica, 'numero', true);
        $pramail_tab = $this->praLib->GetPramail($pratica, "gesnum", true);
        //
        $update_Info = 'Oggetto: Rinumera Prat. ' . $pratica . ' in ' . $newNumero;
        if ($proges_rec) {
            $proges_rec['GESNUM'] = $anno . $newNumero;
            if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                Out::msgStop("Errore", "Errore in aggiornamento su PROGES");
                return false;
            }
        }
        if ($prasta_rec) {
            $prasta_rec['STANUM'] = $anno . $newNumero;
            if (!$this->updateRecord($this->PRAM_DB, 'PRASTA', $prasta_rec, $update_Info)) {
                Out::msgStop("Errore", "Errore in aggiornamento su PRASTA");
                return false;
            }
        }
        if ($propas_tab) {
            foreach ($propas_tab as $propas_rec) {
                $newProvpa = $newProvpn = "";
                $propas_rec['PRONUM'] = $anno . $newNumero;
                $newKey = substr_replace($propas_rec['PROPAK'], $anno . $newNumero, 0, 10);
                if ($propas_rec['PROVPA']) {
                    $newProvpa = substr_replace($propas_rec['PROVPA'], $anno . $newNumero, 0, 10);
                }
                if ($propas_rec['PROVPN']) {
                    $newProvpn = substr_replace($propas_rec['PROVPN'], $anno . $newNumero, 0, 10);
                }
                $dir = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", false);
                $newDir = $this->praLib->SetDirectoryPratiche(substr($newKey, 0, 4), $newKey);

                if (is_dir($dir)) {
                    if (!@rename($dir, $newDir)) {
                        Out::msgStop("Errore", "Errore rename cartella da $dir a $newDir");
                        return false;
                    }
                }
                $propas_rec['PROPAK'] = $newKey;
                $propas_rec['PROVPA'] = $newProvpa;
                $propas_rec['PROVPN'] = $newProvpn;
                if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PROPAS");
                    return false;
                }
            }
        }
        if ($prodst_tab) {
            foreach ($prodst_tab as $prodst_rec) {
                $prodst_rec['DSTSET'] = substr_replace($prodst_rec['DSTSET'], $anno . $newNumero, 0, 10);
                if (!$this->updateRecord($this->PRAM_DB, 'PRODST', $prodst_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PRODST");
                    break;
                }
            }
        }

        if ($pracom_tab) {
            foreach ($pracom_tab as $pracom_rec) {
                $pracom_rec['COMPAK'] = substr_replace($pracom_rec['COMPAK'], $anno . $newNumero, 0, 10);
                if (!$this->updateRecord($this->PRAM_DB, 'PRACOM', $pracom_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PRACOM");
                    return false;
                }
            }
        }
        if ($praMitDest_tab) {
            foreach ($praMitDest_tab as $praMitDest_rec) {
                $praMitDest_rec['KEYPASSO'] = substr_replace($praMitDest_rec['KEYPASSO'], $anno . $newNumero, 0, 10);
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PRAMITDEST");
                    return false;
                }
            }
        }
        if ($prodag_tab) {
            foreach ($prodag_tab as $prodag_rec) {
                $prodag_rec['DAGNUM'] = $anno . $newNumero;
                if (trim(strlen($prodag_rec['DAGPAK'])) == 10) {
                    $prodag_rec['DAGPAK'] = $anno . $newNumero;
                    $prodag_rec['DAGSET'] = $anno . $newNumero;
                } else if (trim(strlen($prodag_rec['DAGPAK'])) == 22) {
                    $prodag_rec['DAGPAK'] = substr_replace($prodag_rec['DAGPAK'], $anno . $newNumero, 0, 10);
                    $prodag_rec['DAGSET'] = substr_replace($prodag_rec['DAGSET'], $anno . $newNumero, 0, 10);
                }

                //$prodag_rec['DAGPAK'] = substr_replace($prodag_rec['DAGPAK'], $anno . $newNumero, 0, 10);
                $prodag_rec['DAGSET'] = substr_replace($prodag_rec['DAGSET'], $anno . $newNumero, 0, 10);
                if (!$this->updateRecord($this->PRAM_DB, 'PRODAG', $prodag_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PRODAG");
                    return false;
                }
            }
        }
        if ($pasdoc_tab) {
            foreach ($pasdoc_tab as $pasdoc_rec) {
                //$pasdoc_rec['PASKEY'] = substr_replace($pasdoc_rec['PASKEY'], $anno . $newNumero, 0, 10);
                if (trim(strlen($pasdoc_rec['PASKEY'])) == 10) {
                    $pasdoc_rec['PASKEY'] = $anno . $newNumero;
                } else if (trim(strlen($pasdoc_rec['PASKEY'])) == 22) {
                    $pasdoc_rec['PASKEY'] = substr_replace($pasdoc_rec['PASKEY'], $anno . $newNumero, 0, 10);
                }

                //if ($pasdoc_rec['PASCLA'] != "" && $pasdoc_rec['PASCLA'] != "GENERALE" && strpos($pasdoc_rec['PASCLA'], "COMUNICAZIONE") === false) {
                if (strpos($pasdoc_rec['PASCLA'], "PRATICA N.") !== false) {
                    $pasdoc_rec['PASCLA'] = "PRATICA N. $newNumero/$anno";
                }
                if (!$this->updateRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PASDOC");
                    return false;
                }
            }
        }
        if ($pramail_tab) {
            foreach ($pramail_tab as $pramail_rec) {
                $pramail_rec['GESNUM'] = $anno . $newNumero;
                if ($pramail_rec['PROPAK']) {
                    $pramail_rec['PROPAK'] = substr_replace($pramail_rec['PROPAK'], $anno . $newNumero, 0, 10);
                    ;
                }
                if ($pramail_rec['COMPAK']) {
                    $pramail_rec['COMPAK'] = substr_replace($pramail_rec['COMPAK'], $anno . $newNumero, 0, 10);
                    ;
                }
                if (!$this->updateRecord($this->PRAM_DB, 'PRAMAIL', $pramail_rec, $update_Info)) {
                    Out::msgStop("Errore", "Errore in aggiornamento su PRAMAIL");
                    return false;
                }
            }
        }

        if (!$this->praImmobili->RinumeraImmobili($this, $anno, $newNumero)) {
            Out::msgStop("Errore", "Errore in aggiornamento su PRAIMM");
            return false;
        }
        if (!$this->praSoggetti->RinumeraSoggetti($this, $anno, $newNumero)) {
            Out::msgStop("Errore", "Errore in aggiornamento su ANADES");
            return false;
        }
        Out::msgInfo("Rinumerazione", "Identificativo N. $pratica rinominata correttamente in $anno$newNumero");


        return array("praticaSorg" => $pratica, "praticaDest" => $newNumero);
    }

    function RinumeraNumero() {
        $pratica = $_POST[$this->nameForm . "_Numero"];
        $anno = $_POST[$this->nameForm . "_AnnoSerie"];
        $serie = $_POST[$this->nameForm . "_ric_codiceserie"];
        $newNumero = $_POST[$this->nameForm . "_NumeroDest"];
        $proges_rec_newNumero = $this->praLib->GetProgesSerie($anno, $newNumero, $serie);
        if ($proges_rec_newNumero) {
            Out::msgStop("Errore", "Il numero Fascicolo $newNumero dell'anno $anno è gia presente per la serie $serie.<br>Scegliere un nuovo numero");
            return false;
        }
        $numAntecedente = $newNumero;
        $numAntecedente--;
        if ($numAntecedente > 0) {
            $i = 1;
            while ($i != 'STOP') {
                $proges_rec_antecedente = $this->praLib->GetProgesSerie($anno, $numAntecedente, $serie);
                if ($proges_rec_antecedente) {
                    $i = 'STOP';
                } elseif ($numAntecedente == 0) {
                    $i = 'STOP';
                } else {
                    $numAntecedente--;
                }
            }
        }
        $proges_rec = $this->praLib->GetProgesSerie($anno, $pratica, $serie);
         if ($proges_rec_antecedente['GESDRE']>$proges_rec['GESDRE']){
            Out::msgStop("Errore", "Il la data di registrazione Fascicolo $numAntecedente dell' anno $anno serie $serie è più recente.<br>Scegliere un nuovo numero");
            return false;
        }
        $update_Info = 'Oggetto: Rinumera Prat. ' . $pratica . 'anno ' . $anno . ' per la serie ' . $serie . ' in ' . $newNumero;
        if ($proges_rec) {
            $proges_rec['SERIEPROGRESSIVO'] = $newNumero;
            if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $proges_rec, $update_Info)) {
                Out::msgStop("Errore", "Errore in aggiornamento su PROGES");
                return false;
            }
        }
        Out::msgInfo("Rinumerazione", "Pratica N. $pratica per l'anno $anno serie $serie numerazione rinominata correttamente in $newNumero");


        return array("praticaSorg" => $pratica, "praticaDest" => $newNumero);
    }

    function DecodProges($Codice, $tipoRic = 'codice') {
        $proges_rec = $this->praLib->GetProges($Codice, $tipoRic);
        Out::valore($this->nameForm . "_Identificativo", substr($proges_rec['GESNUM'], 4));
        Out::valore($this->nameForm . "_Anno", substr($proges_rec['GESNUM'], 0, 4));
    }

    private function GetRequiredNumero($tipo = true) {
        Out::required($this->nameForm . '_Numero', $tipo, $tipo);
        Out::required($this->nameForm . '_AnnoSerie', $tipo, $tipo);
        Out::required($this->nameForm . '_NumeroDest', $tipo, $tipo);
        Out::required($this->nameForm . '_ric_siglaserie', $tipo, $tipo);
    }

    private function GetRequiredIdentificativo($tipo = true) {
        Out::required($this->nameForm . '_Identificativo', $tipo, $tipo);
        Out::required($this->nameForm . '_Anno', $tipo, $tipo);
        Out::required($this->nameForm . '_IdentificativoDest', $tipo, $tipo);
        Out::required($this->nameForm . '_AnnoDest', $tipo, $tipo);
    }

}

?>