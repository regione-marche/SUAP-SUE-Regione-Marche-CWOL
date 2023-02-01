<?php

/**
 *
 * Motore di Gestione e utility del Fascicolo Elettronico
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    06.12.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFascicoloArch.class.php';

class praFascicolo {

    public $message;
    private $praLib;
    private $codicePratica;
    private $chiavePasso;
    private $eqAudit;
    private $rowidSoggetto;

    function __construct($codicePratica = '', $rowidSoggetto = 0) {
        try {
            $this->praLib = new praLib();
        } catch (Exception $e) {
            
        }
        $this->codicePratica = $codicePratica;
        $this->rowidSoggetto = $rowidSoggetto;
        $this->eqAudit = new eqAudit();
    }

    public function setChiavePasso($chiavePasso) {
        $this->chiavePasso = $chiavePasso;
    }

    /**
     * 
     * @return array    elementi preparati per la protocollazione
     */
    public function getElementiProtocollaPratica() {

        $proges_rec = $this->praLib->GetProges($this->codicePratica, 'codice');
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }

        /*
         * Sccelta del mittente per la protocollazione
         */
        $Anades_rec_mittente = $this->praLib->GetAnades($this->rowidSoggetto, 'rowid');
        if (!$Anades_rec_mittente) {
            $Anades_rec_mittente = $this->GetMittenteProtocollo();
            if (!$Anades_rec_mittente) {
                return false;
            }
        }
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(3);
        $praLibVar->setCodicePratica($this->codicePratica);
        //cerco ultima chiave passo per prendere i dati aggiuntivi di tutti i passi
        $sqlP = "SELECT MAX(PROPAK) AS ULTIMO FROM PROPAS WHERE PRONUM = '" . $proges_rec['GESNUM'] . "'";
        $ultimo_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sqlP, false);
        $praLibVar->setChiavePasso($ultimo_rec['ULTIMO']);
        $oggetto = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_rec['FILVAL']);
        $elementi['tipo'] = 'A';
        $elementi['dati']['DenomComune'] = $denomComune;
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $proges_rec['GESDRI'];
        $elementi['dati']['NumeroPratica'] = substr($proges_rec['GESNUM'], 4) . " - " . substr($proges_rec['GESNUM'], 0, 4);
        $elementi['dati']['NumeroFascElet'] = $proges_rec['GESNUM'];
        $elementi['dati']['MittDest']['Nome'] = $Anades_rec_mittente['DESNOME'];
        $elementi['dati']['MittDest']['Cognome'] = $Anades_rec_mittente['DESCOGNOME'];
        $elementi['dati']['NumeroRichiesta'] = $proges_rec['GESPRA'];
        $elementi['dati']['NumeroRichiestaFormatted'] = substr($proges_rec['GESPRA'], 4) . "-" . substr($proges_rec['GESPRA'], 0, 4);
        $elementi['dati']['MittDest']['Denominazione'] = $Anades_rec_mittente['DESNOM'];
        $elementi['dati']['MittDest']['Indirizzo'] = $Anades_rec_mittente['DESIND'] . " " . $Anades_rec_mittente['DESCIV'];
        $elementi['dati']['MittDest']['Desind'] = $Anades_rec_mittente['DESIND'];
        $elementi['dati']['MittDest']['Civico'] = $Anades_rec_mittente['DESCIV'];
        $elementi['dati']['MittDest']['CAP'] = $Anades_rec_mittente['DESCAP'];
        $elementi['dati']['MittDest']['Citta'] = $Anades_rec_mittente['DESCIT'];
        $elementi['dati']['MittDest']['Provincia'] = $Anades_rec_mittente['DESPRO'];
        $elementi['dati']['MittDest']['Email'] = $Anades_rec_mittente['DESEMA'];
        $elementi['dati']['MittDest']['CF'] = $Anades_rec_mittente['DESFIS'];
        $elementi['dati']['corrispondente'] = $Anades_rec_mittente['DESCOD'];

        // per compatibilita con nuove classi proDatiProtocollo
        $elementi['dati']['Mittenti'][] = $elementi['dati']['MittDest'];

        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        if ($destinatario) {
            $elementi['destinatari'][0] = $destinatario;
        }
        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;

        $elementi['dati']['Ruolo'] = $anatsp_rec['TSPRUOLO'];

        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;


        /*
         * Tipo documento protocollo obbligatorio per iride e jiride MM 04/01/2016
         */
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazione($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;

        /*
         * Dati Fascicolazione
         */
        $fascicolo = $this->praLib->GetFascicoloProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['CodiceFascicolo'] = $fascicolo;
        $elementi['dati']['Fascicolazione']['Data'] = "";
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
        $elementi['dati']['Fascicolazione']['Aggiornamento'] = "F";
        //
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    public function getElementiProtocollaComunicazioneP() {
        $propas_rec = $this->praLib->GetPropas($this->chiavePasso, 'propak');
        if (!$propas_rec) {
            return false;
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        $pracomP_rec = $this->praLib->GetPracomP($this->chiavePasso);
        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(4);
        $praLibVar->setCodicePratica($propas_rec['PRONUM']);
        $praLibVar->setChiavePasso($this->chiavePasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggetto = $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
        $praMitDest_tab = $this->praLib->GetPraDestinatari($this->chiavePasso, "codice", true);
        if ($praMitDest_tab) {
            foreach ($praMitDest_tab as $key => $praMitDest_rec) {
                $elementi['dati']['Destinatari'][$key]['CodiceDestinatario'] = $praMitDest_rec['CODICE'];
                $elementi['dati']['Destinatari'][$key]['Denominazione'] = utf8_encode($praMitDest_rec['NOME']);
                $elementi['dati']['Destinatari'][$key]['CF'] = $praMitDest_rec['FISCALE'];
                $elementi['dati']['Destinatari'][$key]['Indirizzo'] = utf8_encode($praMitDest_rec['INDIRIZZO']);
                $elementi['dati']['Destinatari'][$key]['Citta'] = utf8_encode($praMitDest_rec['COMUNE']);
                $elementi['dati']['Destinatari'][$key]['CAP'] = $praMitDest_rec['CAP'];
                $elementi['dati']['Destinatari'][$key]['Provincia'] = $praMitDest_rec['PROVINCIA'];
                $elementi['dati']['Destinatari'][$key]['Email'] = $praMitDest_rec['MAIL'];
                $elementi['dati']['Destinatari'][$key]['DataInvio'] = $praMitDest_rec['DATAINVIO'];
                $elementi['dati']['Destinatari'][$key]['OraInvio'] = $praMitDest_rec['ORAINVIO'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['CodiceDestinatario'] = $praMitDest_rec['CODICE'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['Denominazione'] = utf8_encode($praMitDest_rec['NOME']);
//                $elementi['dati']['DestinatariProtocollo'][$key]['CF'] = $praMitDest_rec['FISCALE'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['Indirizzo'] = utf8_encode($praMitDest_rec['INDIRIZZO']);
//                $elementi['dati']['DestinatariProtocollo'][$key]['Citta'] = utf8_encode($praMitDest_rec['COMUNE']);
//                $elementi['dati']['DestinatariProtocollo'][$key]['CAP'] = $praMitDest_rec['CAP'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['Provincia'] = $praMitDest_rec['PROVINCIA'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['Email'] = $praMitDest_rec['MAIL'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['DataInvio'] = $praMitDest_rec['DATAINVIO'];
//                $elementi['dati']['DestinatariProtocollo'][$key]['OraInvio'] = $praMitDest_rec['ORAINVIO'];
            }
        }
        $elementi['tipo'] = 'P';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $praMitDest_tab[0]['DATAINVIO'];
        $elementi['dati']['MittDest']['Denominazione'] = utf8_encode($praMitDest_tab[0]['NOME']);
        $elementi['dati']['MittDest']['Indirizzo'] = utf8_encode($praMitDest_tab[0]['INDIRIZZO']);
        $elementi['dati']['MittDest']['CAP'] = $praMitDest_tab[0]['CAP'];
        $elementi['dati']['MittDest']['Citta'] = utf8_encode($praMitDest_tab[0]['COMUNE']);
        $elementi['dati']['MittDest']['Provincia'] = $praMitDest_tab[0]['PROVINCIA'];
        $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
        if ($proges_rec['GESNPR']) {
            $elementi['dati']['TipoAntecedente'] = "A";
        }
        $elementi['dati']['MittDest']['Email'] = $praMitDest_tab[0]['MAIL'];
        $elementi['dati']['MittDest']['CF'] = $praMitDest_tab[0]['FISCALE'];

        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        if ($destinatario) {
            $elementi['destinatari'][0] = $destinatario;
        }


        $firmatario = $this->setFirmatarioProtocollo($proges_rec['GESRES']);
        if ($firmatario) {
            $elementi['firmatari']['firmatario'][0] = $firmatario;
        }


        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        return $elementi;
    }

    public function getElementiProtocollaComunicazioneA() {
        $propas_rec = $this->praLib->GetPropas($this->chiavePasso, 'propak');
        if (!$propas_rec) {
            return false;
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        $pracomP_rec = $this->praLib->GetPracomP($this->chiavePasso);
        //$pracomA_rec = $this->praLib->GetPracomA($this->chiavePasso, $pracomP_rec['ROWID']);
        $pracomA_rec = $this->praLib->GetPracomA($this->chiavePasso);

        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(5);
        $praLibVar->setCodicePratica($propas_rec['PRONUM']);
        $praLibVar->setChiavePasso($this->chiavePasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggetto = $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);


        $praMitDest_rec = $this->praLib->GetPraArrivo($this->chiavePasso);
        if ($praMitDest_rec) {
            $elementi['dati']['Mittenti']['Denominazione'] = utf8_encode($praMitDest_rec['NOME']);
            $elementi['dati']['Mittenti']['Indirizzo'] = utf8_encode($praMitDest_rec['INDIRIZZO']);
            $elementi['dati']['Mittenti']['CAP'] = $praMitDest_rec['CAP'];
            $elementi['dati']['Mittenti']['Citta'] = utf8_encode($praMitDest_rec['COMUNE']);
            $elementi['dati']['Mittenti']['Provincia'] = $praMitDest_rec['PROVINCIA'];
            $elementi['dati']['Mittenti']['Email'] = $praMitDest_rec['MAIL'];
            $elementi['dati']['Mittenti']['CF'] = $praMitDest_rec['FISCALE'];
        }

        $elementi['tipo'] = 'A';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $pracomA_rec['COMDAT'];
        $elementi['dati']['ChiavePasso'] = $this->chiavePasso;
//        $elementi['dati']['MittDest']['Denominazione'] = $pracomA_rec['COMNOM'];
//        $elementi['dati']['MittDest']['Indirizzo'] = $pracomA_rec['COMIND'];
//        $elementi['dati']['MittDest']['CAP'] = $pracomA_rec['COMCAP'];
//        $elementi['dati']['MittDest']['Citta'] = $pracomA_rec['COMCIT'];
//        $elementi['dati']['MittDest']['Provincia'] = $pracomA_rec['COMPRO'];
        $elementi['dati']['NumeroAntecedente'] = substr($pracomP_rec['COMPRT'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($pracomP_rec['COMPRT'], 0, 4);
        if ($pracomP_rec['COMPRT']) {
            $elementi['dati']['TipoAntecedente'] = "P";
        }
//        $elementi['dati']['MittDest']['Email'] = $pracomA_rec['COMMLD'];
//        $elementi['dati']['MittDest']['CF'] = $pracomA_rec['COMFIS'];

        $destinatario = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        if ($destinatario) {
            $elementi['destinatari'][0] = $destinatario;
        }
        $uffici = $this->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        return $elementi;
    }

    public function bloccaRicevute($model, $rowidArr = array()) {
        if (!$rowidArr) {
            return;
        }
        $update_Info = 'Oggetto: Blocco Mail Ricevute per Protocollazione';
        foreach ($rowidArr as $rowid) {
            $pramail_rec = $this->praLib->getPraMail($rowid['ROWID'], 'ROWID');
            $pramail_rec['FLPROT'] = 1;
            $model->updateRecord($this->praLib->getPRAMDB(), 'PRAMAIL', $pramail_rec, $update_Info);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $this->praLib->getPRAMDB()->getDB(),
                'DSet' => 'PRAMAIL',
                'Estremi' => "Blocco Ricevuta su PRAMAIL rowid: " . $rowid['ROWID'] . " - passo: " . $pramail_rec['COMPAK']
            ));
        }
    }

    public function updateDatiProtPracom($ritorno, $arrayDoc, $tipo = "A") {
        $praLib = new praLib();

        /*
         * Rileggo PRACOM_REC
         */
        if ($tipo == "A") {
            $pracom_rec = $this->praLib->GetPracomA($this->chiavePasso);
        } else {
            $pracom_rec = $this->praLib->GetPracomP($this->chiavePasso);
        }
        if (!$pracom_rec) {
            $retUpd["Status"] = "-1";
            $retUpd["Message"] = "Rilettura comunicazione del passo chiave $this->chiavePasso, fascicolo N. " . $this->codicePratica . " fallito";
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        /*
         * Istanzio array di ritorno
         */
        $retUpd["Status"] = "0";
        $retUpd["Message"] = "Aggiornamento dati protocollazione avvenuto con successo";
        $retUpd["RetValue"] = true;

        $codice = $ritorno['RetValue']['DatiProtocollazione']['proNum']['value'];
        $anno = $ritorno['RetValue']['DatiProtocollazione']['Anno']['value'];

        /*
         * Salvo i metadati di protocollazione
         */
        $pracom_rec['COMMETA'] = serialize($ritorno['RetValue']);
        //$anno = date("Y");
        $pracom_rec['COMPRT'] = $anno . $codice;
        $pracom_rec['COMDPR'] = date("Ymd");

        try {
            $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), "PRACOM", "ROWID", $pracom_rec);
            if ($nrow == -1) {
                $retUpd["Status"] = "-1";
                $retUpd["Message"] = "Aggiornamento metadati protocollazione del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " fallito";
                $retUpd["RetValue"] = false;
                return $retUpd;
            }
        } catch (Exception $exc) {
            $retUpd["Status"] = "-1";
            $retUpd["Message"] = "Aggiornamento metadati protocollazione del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " fallito: " . $exc->getMessage();
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$this->bloccaAllegati($this->chiavePasso, $arrayDoc['pasdoc_rec'], $tipo)) {
            $retUpd["Status"] = "-2";
            $retUpd["Message"] = "Blocco allegati del del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " con protocollo fallito: ";
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        return $retUpd;
    }

    public function updateDatiProtPracomIdDoc($ritorno, $arrayDoc) {
//        Out::msgInfo("valore", print_r($ritorno, true));
//        Out::msgInfo("doc", print_r($arrayDoc, true));
        $praLib = new praLib();

        /*
         * Rileggo PRACOM_REC
         */
        $pracom_rec = $this->praLib->GetPracomP($this->chiavePasso);
        if (!$pracom_rec) {
            $retUpd["Status"] = "-1";
            $retUpd["Message"] = "Rilettura comunicazione del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " fallito";
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        /*
         * Istanzio array di ritorno
         */
        $retUpd["Status"] = "0";
        $retUpd["Message"] = "Aggiornamento dati documento avvenuto con successo";
        $retUpd["RetValue"] = true;

        /*
         * Salvo i metadati di protocollazione
         */
        $pracom_rec['COMMETA'] = serialize($ritorno['RetValue']);
        $pracom_rec['ROWID'] = $pracom_rec['ROWID'];
        $pracom_rec['COMIDDOC'] = $ritorno['RetValue']['DatiProtocollazione']['DocNumber']['value'];
        $pracom_rec['COMDATADOC'] = date("Ymd");
        try {
            $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), "PRACOM", "ROWID", $pracom_rec);
            if ($nrow == -1) {
                $retUpd["Status"] = "-1";
                $retUpd["Message"] = "Aggiornamento metadati documento del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " fallito";
                $retUpd["RetValue"] = false;
                return $retUpd;
            }
        } catch (Exception $exc) {
            $retUpd["Status"] = "-1";
            $retUpd["Message"] = "Aggiornamento metadati documento del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " fallito: " . $exc->getMessage();
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$this->bloccaAllegati($this->chiavePasso, $arrayDoc['pasdoc_rec'], "P")) {
            $retUpd["Status"] = "-2";
            $retUpd["Message"] = "Blocco allegati del del passo chiave $this->chiavePasso, fascicolo N. " . $pracom_rec['COMNUM'] . " con protocollo fallito: ";
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        return $retUpd;
    }

    public function updateDatiProtProges($ritorno, $arrayDoc) {
        $praLib = new praLib();

        /*
         * Rileggo PROGES_REC
         */
        $proges_rec = $this->praLib->GetProges($this->codicePratica);
        if (!$proges_rec) {
            
        }

        /*
         * Istanzio array di ritorno
         */
        $retUpd["Status"] = "0";
        $retUpd["Message"] = "Aggiornamento dati protocollazione avvenuto con successo";
        $retUpd["RetValue"] = true;

        $codice = $ritorno['RetValue']['DatiProtocollazione']['proNum']['value'];
        $anno = $ritorno['RetValue']['DatiProtocollazione']['Anno']['value'];

        /*
         * Salvo i metadati di protocollazione
         */
        $proges_rec['GESMETA'] = serialize($ritorno['RetValue']);
        $proges_rec['GESNPR'] = $anno . $codice;
        $proges_rec['GESPAR'] = "A";


        try {
            $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), "PROGES", "ROWID", $proges_rec);
            if ($nrow == -1) {
                $retUpd["Status"] = "-1";
                $retUpd["Message"] = "Aggiornamento metadati protocollazione del fascicolo N. " . $proges_rec['GESNUM'] . " fallito";
                $retUpd["RetValue"] = false;
                return $retUpd;
            }
        } catch (Exception $exc) {
            $retUpd["Status"] = "-1";
            $retUpd["Message"] = "Aggiornamento metadati protocollazione del fascicolo N. " . $proges_rec['GESNUM'] . " fallito: " . $exc->getMessage();
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        /*
         * Marco gli allegati col numero protocollo
         */
        if (!$this->bloccaAllegati($proges_rec['GESNUM'], $arrayDoc['pasdoc_rec'], 'PR')) {
            $retUpd["Status"] = "-2";
            $retUpd["Message"] = "Blocco allegati del fascicolo N. " . $proges_rec['GESNUM'] . " con protocollo fallito.";
            $retUpd["RetValue"] = false;
            return $retUpd;
        }

        return $retUpd;
    }

    public function bloccaAllegati($chiave, $rowidArr = array(), $tipo = "A") {
        if (!$rowidArr) {
            return;
        }
        if ($tipo == "PR") {
            $proges_rec = $this->praLib->GetProges($chiave);
            $table = "PROGES";
            $rowidPr = $proges_rec['ROWID'];
        } elseif ($tipo == "A") {
            $pracomA_rec = $this->praLib->GetPracomA($chiave);
            $table = "PRACOM";
            $rowidPr = $pracomA_rec['ROWID'];
        } elseif ($tipo == "P") {
            $pracomP_rec = $this->praLib->GetPracomP($chiave);
            $table = "PRACOM";
            $rowidPr = $pracomP_rec['ROWID'];
        } elseif ($tipo == "ASS") {
            $propas_rec = $this->praLib->GetPropas($chiave);
            $table = "PROPAS";
            $rowidPr = $propas_rec['ROWID'];
        }

        foreach ($rowidArr as $rowid) {
            $pasdoc_rec = $this->praLib->GetPasdoc($rowid['ROWID'], 'ROWID');
            $rowidAllegati = "";
            $rowidAllegati .= " - " . $rowid['ROWID'] . " - ";
            $pasdoc_rec['PASPRTCLASS'] = $table;
            $pasdoc_rec['PASPRTROWID'] = $rowidPr;


            try {
                $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                return false;
            }

            //$update_Info = 'Oggetto: Blocco Allegati Protocollazione';
            //$model->updateRecord($this->praLib->getPRAMDB(), 'PASDOC', $pasdoc_rec, $update_Info);
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $this->praLib->getPRAMDB()->getDB(),
                'DSet' => 'PASDOC',
                'Estremi' => "Blocco Allegato " . $rowid['ROWID'] . " - " . $pasdoc_rec['PASNAME']
            ));
        }
        return true;
    }

    public function getAllegatoPrincipale($tipo = 'Paleo', $downloadB64 = true) {
        $proges_rec = $this->praLib->GetProges($this->codicePratica, 'codice');
        //$passi_tab = $this->praLib->GetPropas($this->codicePratica, 'codice', true);
        $passi_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PROPAS WHERE PRONUM='$this->codicePratica' AND PROPUB=1 ORDER BY PROSEQ", true);
        $pasdoc_tab = $this->praLib->GetPasdoc($this->codicePratica, 'codice', true);
        //
        $arrayfile = array();
        $flag_principale = false;
        $flag_Comunica = false;
        $flag_scegli = false;
        $arrayDoc = array();
        //se c'è il numero di richiesta on line, cerco il documento che è il rapporto completo come documento principale
        if ($proges_rec['GESPRA'] != '') {
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);
            $arrayfile = $this->elencaFiles($pratPath);
            //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
            $index = 0;
            foreach ($pasdoc_tab as $key => $pasdoc_rec) {
                //se il documento è già stato bloccato, lo scarto		
                if ($pasdoc_rec['PASPRTCLASS'] != '') {
                    continue;
                }
                if (stristr($pasdoc_rec['PASCLA'], 'INFOCAMERE') !== false) {
                    $flag_Comunica = true;
                    $sorgFile = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                    if (!file_exists($sorgFile)) {
                        continue;
                    }
                    $infoFile = pathinfo($sorgFile);
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        //l'estensione SUAP.PDF.P7M indica un allegato per Comunica.
                        if (stristr($pasdoc_rec['PASNAME'], 'SUAP.PDF.P7M') !== false) {
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $pasdoc_rec['ROWID'];
                            }
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                        return false;
                    }
                    fclose($fp);
                }
            }
            if (!$flag_Comunica) {
                //pratica da sportello on line, ma non da Comunica, cerco il rapporto completo dentro i passi
                $arrayDoc = array(); //svuoto l'array perchè ci potrebbero essere allegati caricati
                $index = 0;
                foreach ($passi_tab as $passo) {
                    //scansione dei passi
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($passo['PROPAK'], 0, 4), $passo['PROPAK'], 'PASSO', false);
                    $lista = $this->elencaFiles($pramPath);
                    //scorro i file dentro il passo
                    foreach ($lista as $fileName) {
                        $sorgFile = $pramPath . "/" . $fileName;
                        //query su pasdoc per recuperare il nome originale del file
                        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $passo['PROPAK'] . "' AND PASFIL = '" . $fileName . "'";
                        $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                        if ($file_rec['PASPRTCLASS'] != '') {
                            continue;
                        }
                        if (!file_exists($sorgFile)) {
                            continue;
                        }

                        if ($passo['PROPRI'] == 1 || $file_rec['PASPRI'] == 1) {
                            $flag_scegli = true;
                        }

                        $infoFile = pathinfo($sorgFile);
                        if ($infoFile['extension'] != 'p7m' && $infoFile['extension'] != 'P7M') {
                            continue;
                        }
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                            $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                            $base64File = base64_encode($binFile);
                            if ($flag_scegli && !isset($arrayDoc['Principale'])) {
                                $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                                $flag_scegli = false;
                                $flag_principale = true;
                                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                    $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                                    $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                    $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                    $devLib = new devLib();
                                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                    $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                    $arrayDoc['Principale']['sintesiContenuto'] = '';
                                    $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $arrayDoc['Principale']['note'] = '';
                                    $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                                } else {
                                    $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                    $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                    $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                                }
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                            //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                            break;
                        }
                        fclose($fp);
                    }
                    if ($passo['PRODRR'] == 1) {
                        $flag_scegli = true;
                    }
                }
                //estraggo i documenti allegati generali
                $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "'";
                $pasdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);
                $arrayfile = $this->elencaFiles($pratPath);
                foreach ($pasdoc_tab as $key => $pasdoc_rec) {
                    $sorgFile = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                    if (!file_exists($sorgFile)) {
                        continue;
                    }
                    $infoFile = pathinfo($sorgFile);
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        if ($pasdoc_rec['PASPRI'] == 1 && !isset($arrayDoc['Principale'])) {
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $flag_scegli = false;
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $pasdoc_rec['ROWID'];
                            }
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                        //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                        return false;
                    }
                    fclose($fp);
                }
            }
        } else {
            //se non c'è il numero di richiesta on line, cerco l'eml dentro i documenti generali della pratica
            //recupero path in cui ci sono gli allegati generali
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);
            $arrayfile = $this->elencaFiles($pratPath);
            foreach ($arrayfile as $key => $value) {
                $sorgFile = $pratPath . "/" . $value;
                $infoFile = pathinfo($sorgFile);
                //query su pasdoc per recuperare il nome originale del file
                $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "' AND PASFIL = '" . $value . "'";
                $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                if ($file_rec['PASPRTCLASS'] != '') {
                    continue;
                }
                if ($infoFile['extension'] == 'info') {
                    continue;
                }
                if (strtolower($infoFile['extension']) == 'eml') {
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                        $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        if ($base64File === false) {
                            $this->message = "errore in codifica Allegato: " . $sorgFile;
                            return false;
                            //Out::msgStop("Preparazione Allegato", "errore in codifica Allegato: " . $sorgFile);
                        }
                        $flag_principale = true;
                        if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                            $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                            $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                            $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                            $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                            $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                            $devLib = new devLib();
                            $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                            $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                            $arrayDoc['Principale']['sintesiContenuto'] = '';
                            $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                            $arrayDoc['Principale']['note'] = '';
                            $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                        } else {
                            $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                            $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                            $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                            $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                        return false;
                    }
                    fclose($fp);
                    break; // Esco perchè prendo il primo EML che incontro come documento principale
                }
            }


            if (!$arrayDoc) {
                foreach ($arrayfile as $key => $value) {
                    $sorgFile = $pratPath . "/" . $value;
                    $infoFile = pathinfo($sorgFile);
                    //query su pasdoc per recuperare il nome originale del file
                    $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "' AND PASFIL = '" . $value . "'";
                    $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                    if ($file_rec['PASPRTCLASS'] != '') {
                        continue;
                    }
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    if (strtolower($infoFile['extension']) == 'p7m') {
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                            $base64File = base64_encode($binFile);
                            if ($base64File === false) {
                                $this->message = "errore in codifica Allegato: " . $sorgFile;
                                return false;
                                //Out::msgStop("Preparazione Allegato", "errore in codifica Allegato: " . $sorgFile);
                            }
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                            return false;
                        }
                        fclose($fp);
                        break; // Esco perchè prendo il primo p7m che incontro come documento principale
                    }
                }
            }
        }
        //
        //se non ho trovato un documento principale non seleziono nessun documento da allegare
        //        
        if (!$flag_principale) {
            $arrayDoc = array();
        }
        return $arrayDoc;
    }

    public function getAllegatiDaAggiungere($tipo, $downloadB64, $filtriRowid) {
        $whereRowid = "";
        $arrayDoc = array();
        if ($filtriRowid === "NO") {
            $whereRowid .= " AND ROWID = -1";
        } else {
            if (count($filtriRowid) == 1) {
                $whereRowid = " AND ROWID = " . $filtriRowid[0];
            } else {
                $whereRowid .= " AND (ROWID = -1";
                foreach ($filtriRowid as $rowid) {
                    $whereRowid .= " OR ROWID = " . $rowid;
                }
                $whereRowid .= ")";
            }
        }
        //
        //Faccio una query su PASDOC per i rowid selezionati
        //
        //$sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '$this->codicePratica%' $whereRowid";
        $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $this->codicePratica . "%' $whereRowid";
        $file_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        $index = 0;
        foreach ($file_tab as $file_rec) {
            if (strlen($file_rec['PASKEY']) == 10) {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($this->codicePratica, 0, 4), $this->codicePratica, 'PROGES', false);
            } else {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($file_rec['PASKEY'], 0, 4), $file_rec['PASKEY'], 'PASSO', false);
            }
            $sorgFile = $pratPath . "/" . $file_rec['PASFIL'];
            $infoFile = pathinfo($sorgFile);
            if ($file_rec['PASPRTCLASS'] != '') {
                continue;
            }
            if ($infoFile['extension'] == 'info') {
                continue;
            }
            $fp = @fopen($sorgFile, "rb", 0);
            if ($fp) {
                $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                $base64File = base64_encode($binFile);
                if ($base64File === false) {
                    $this->message = "errore in codifica Allegato: " . $sorgFile;
                    return false;
                }
                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                    $arrayDoc['Allegati'][$index]['nomeFile'] = $file_rec['PASNAME'];
                    $arrayDoc['Allegati'][$index]['estensione'] = $infoFile['extension'];
                    $arrayDoc['Allegati'][$index]['dataDocumento'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Allegati'][$index]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Allegati'][$index]['consultazionePubblica'] = "1";
                    $devLib = new devLib();
                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                    $arrayDoc['Allegati'][$index]['codiceOperatore'] = $codOp['CONFIG'];
                    $arrayDoc['Allegati'][$index]['sintesiContenuto'] = "";
                    $arrayDoc['Allegati'][$index]['descrizione'] = 'Documento allegato ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                    $arrayDoc['Allegati'][$index]['note'] = "";
                    $arrayDoc['Allegati'][$index]['stream'] = ($downloadB64) ? $base64File : '';
                    $index++;
                } else {
                    $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $file_rec['PASNAME'];
                    $arrayDoc['Allegati'][$index]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                    $arrayDoc['Allegati'][$index]['Descrizione'] = 'Documento allegato ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                    $index++;
                }
            } else {
                $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                return false;
            }
            fclose($fp);
        }
        return $arrayDoc;
    }

    // FATTO FIX pasdoc_rec_allegati
    public function getAllegatiProtocollaPratica($tipo = 'Paleo', $downloadB64 = true, $onlyMainDoc = false, $filtriRowid = array(), $ignoraProtocollo = false) {
        //
        //Prendo solo il documento principale ed esco
        //
        if ($onlyMainDoc) {
            return $this->getAllegatoPrincipale($tipo, $downloadB64);
        }

        //
        //Prendo gli allegati selezionati ed esco
        //
        if ($filtriRowid) {
            return $this->getAllegatiDaAggiungere($tipo, $downloadB64, $filtriRowid);
        }



        //
        $proges_rec = $this->praLib->GetProges($this->codicePratica, 'codice');
        //$passi_tab = $this->praLib->GetPropas($this->codicePratica, 'codice', true);
        $passi_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PROPAS WHERE PRONUM='$this->codicePratica' AND PROPUB=1 ORDER BY PROSEQ", true);
        if (!$passi_tab) {
            $passi_tab = $this->praLib->GetPropas($this->codicePratica, 'codice', true);
        }
        $pasdoc_tab = $this->praLib->GetPasdoc($this->codicePratica, 'codice', true);
        //
        $arrayfile = array();
        $flag_principale = false;
        $flag_Comunica = false;
        $flag_scegli = false;
        $arrayDoc = array();
        //se c'è il numero di richiesta on line, cerco il documento che è il rapporto completo come documento principale
        if ($proges_rec['GESPRA'] != '') {
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);
            $arrayfile = $this->elencaFiles($pratPath);
            //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
            $index = 0;
            foreach ($pasdoc_tab as $key => $pasdoc_rec) {
                //se il documento è già stato bloccato, lo scarto
                if (!$ignoraProtocollo) {
                    if ($pasdoc_rec['PASPRTCLASS'] != '') {
                        continue;
                    }
                }
                if (stristr($pasdoc_rec['PASCLA'], 'INFOCAMERE') !== false) {
                    $flag_Comunica = true;
                    $sorgFile = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                    if (!file_exists($sorgFile)) {
                        continue;
                    }
                    $infoFile = pathinfo($sorgFile);
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
                        $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        //l'estensione SUAP.PDF.P7M indica un allegato per Comunica.
                        if (stristr($pasdoc_rec['PASNAME'], 'SUAP.PDF.P7M') !== false) {
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $pasdoc_rec['ROWID'];
                            }
                        } else {
                            $arrayDoc['pasdoc_rec_allegati'][$index]['ROWID'] = $pasdoc_rec['ROWID'];
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Allegati'][$index]['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Allegati'][$index]['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Allegati'][$index]['estensione'] = $infoFile['extension'];
                                $arrayDoc['Allegati'][$index]['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Allegati'][$index]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Allegati'][$index]['consultazionePubblica'] = "1";
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Allegati'][$index]['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Allegati'][$index]['sintesiContenuto'] = "";
                                $arrayDoc['Allegati'][$index]['descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Allegati'][$index]['note'] = "";
                                $arrayDoc['Allegati'][$index]['stream'] = $base64File;
                                $index++;
                            } else {
                                //$arrayDoc['Allegati'][$key]['Documento']['Nome'] = $pasdoc_rec['PASFIL'];
                                $arrayDoc['Allegati'][$index]['Documento']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Allegati'][$index]['Documento']['Stream'] = $base64File;
                                $arrayDoc['Allegati'][$index]['Descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $index++;
                            }
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                        return false;
                    }
                    fclose($fp);
                }
            }
            if (!$flag_Comunica) {
                //pratica da sportello on line, ma non da Comunica, cerco il rapporto completo dentro i passi
                $arrayDoc = array(); //svuoto l'array perchè ci potrebbero essere allegati caricati
                $index = 0;
                foreach ($passi_tab as $passo) {
                    //scansione dei passi
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($passo['PROPAK'], 0, 4), $passo['PROPAK'], 'PASSO', false);
                    $lista = $this->elencaFiles($pramPath);
                    //scorro i file dentro il passo
                    foreach ($lista as $fileName) {
                        $sorgFile = $pramPath . "/" . $fileName;
                        //query su pasdoc per recuperare il nome originale del file
                        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $passo['PROPAK'] . "' AND PASFIL = '" . $fileName . "'";
                        $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                        if (!$ignoraProtocollo) {
                            if ($file_rec['PASPRTCLASS'] != '') {
                                continue;
                            }
                        }
                        if (!file_exists($sorgFile)) {
                            continue;
                        }
                        $infoFile = pathinfo($sorgFile);
                        if ($passo['PROPRI'] == 1 || $file_rec['PASPRI'] == 1) {
                            $flag_scegli = true;
                        }

                        if ($infoFile['extension'] != 'p7m' && $infoFile['extension'] != 'P7M') {
                            continue;
                        }
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;

                            $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                            $base64File = base64_encode($binFile);
                            if ($flag_scegli && !isset($arrayDoc['Principale'])) {
                                $flag_scegli = false;
                                $flag_principale = true;
                                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                    $arrayDoc['Principale']['filePath'] = $pramPath . "/" . $file_rec['PASFIL'];
                                    $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                                    $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                    $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                    $devLib = new devLib();
                                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                    $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                    $arrayDoc['Principale']['sintesiContenuto'] = '';
                                    $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $arrayDoc['Principale']['note'] = '';
                                    $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                                } else {
                                    $arrayDoc['Principale']['FilePath'] = $pramPath . "/" . $file_rec['PASFIL'];
                                    $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                    $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                    $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                                }
                            } else {
                                $arrayDoc['pasdoc_rec_allegati'][$index]['ROWID'] = $file_rec['ROWID'];
                                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                    $arrayDoc['Allegati'][$index]['filePath'] = $pramPath . "/" . $file_rec['PASFIL'];
                                    $arrayDoc['Allegati'][$index]['nomeFile'] = $file_rec['PASNAME'];
                                    $arrayDoc['Allegati'][$index]['estensione'] = $infoFile['extension'];
                                    $arrayDoc['Allegati'][$index]['dataDocumento'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Allegati'][$index]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                    $arrayDoc['Allegati'][$index]['consultazionePubblica'] = "1";
                                    $devLib = new devLib();
                                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                    $arrayDoc['Allegati'][$index]['codiceOperatore'] = $codOp['CONFIG'];
                                    $arrayDoc['Allegati'][$index]['sintesiContenuto'] = "";
                                    $arrayDoc['Allegati'][$index]['descrizione'] = 'Documento allegato ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $arrayDoc['Allegati'][$index]['note'] = "";
                                    $arrayDoc['Allegati'][$index]['stream'] = ($downloadB64) ? $base64File : '';
                                    $index++;
                                } else {
                                    $arrayDoc['Allegati'][$index]['Documento']['FilePath'] = $pramPath . "/" . $file_rec['PASFIL'];
                                    $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $file_rec['PASNAME'];
                                    $arrayDoc['Allegati'][$index]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                                    $arrayDoc['Allegati'][$index]['Descrizione'] = 'Documento allegato ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                    $index++;
                                }
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                            //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                            break;
                        }
                        fclose($fp);
                    }
                    if ($passo['PRODRR'] == 1) {
                        $flag_scegli = true;
                    }
                }
                //estraggo i documenti allegati generali
                $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "'";
                $pasdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);
                $arrayfile = $this->elencaFiles($pratPath);
                //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
                foreach ($pasdoc_tab as $key => $pasdoc_rec) {
                    $sorgFile = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                    if (!file_exists($sorgFile)) {
                        continue;
                    }
                    $infoFile = pathinfo($sorgFile);
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
                        $arrayDoc['pasdoc_rec'][] = $rowid_rec;

                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        if ($pasdoc_rec['PASPRI'] == 1 && !isset($arrayDoc['Principale'])) {
                            $flag_scegli = false;
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Principale']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $pasdoc_rec['ROWID'];
                            }
                        } else {
                            $arrayDoc['pasdoc_rec_allegati'][$index]['ROWID'] = $pasdoc_rec['ROWID'];
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Allegati'][$index]['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Allegati'][$index]['nomeFile'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Allegati'][$index]['estensione'] = $infoFile['extension'];
                                $arrayDoc['Allegati'][$index]['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Allegati'][$index]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Allegati'][$index]['consultazionePubblica'] = "1";
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Allegati'][$index]['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Allegati'][$index]['sintesiContenuto'] = "";
                                $arrayDoc['Allegati'][$index]['descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $arrayDoc['Allegati'][$index]['note'] = "";
                                $arrayDoc['Allegati'][$index]['stream'] = ($downloadB64) ? $base64File : '';
                                $index++;
                            } else {
                                $arrayDoc['Allegati'][$index]['Documento']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                                $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $pasdoc_rec['PASNAME'];
                                $arrayDoc['Allegati'][$index]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Allegati'][$index]['Descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                                $index++;
                            }
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                        //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                        return false;
                    }
                    fclose($fp);
                }
            }
        } else {
            //se non c'è il numero di richiesta on line, cerco l'eml dentro i documenti generali della pratica
            //recupero path in cui ci sono gli allegati generali
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);

            $arrayfile = $this->elencaFiles($pratPath);
            foreach ($arrayfile as $key => $value) {
                $sorgFile = $pratPath . "/" . $value;
                $infoFile = pathinfo($sorgFile);
                //query su pasdoc per recuperare il nome originale del file
                $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "' AND PASFIL = '" . $value . "'";

                $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                if (!$ignoraProtocollo) {
                    if ($file_rec['PASPRTCLASS'] != '') {
                        continue;
                    }
                }
                if ($infoFile['extension'] == 'info') {
                    continue;
                }
                //
                //Prima cerco il file eml
                //
                if (strtolower($infoFile['extension']) == 'eml') {
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                        $arrayDoc['pasdoc_rec'][] = $rowid_rec;

                        $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                        $base64File = base64_encode($binFile);
                        if ($base64File === false) {
                            $this->message = "errore in codifica Allegato: " . $sorgFile;
                            return false;
                            //Out::msgStop("Preparazione Allegato", "errore in codifica Allegato: " . $sorgFile);
                        }
                        $flag_principale = true;
                        if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                            $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                            $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                            $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                            $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                            $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                            $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                            $devLib = new devLib();
                            $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                            $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                            $arrayDoc['Principale']['sintesiContenuto'] = '';
                            $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                            $arrayDoc['Principale']['note'] = '';
                            $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                        } else {
                            $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                            $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                            $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                            $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                            $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                        return false;
                    }
                    fclose($fp);
                    break; // Esco perchè prendo il primo eml che incontro come documento principale
                }
            }

            if (!$arrayDoc) {
                foreach ($arrayfile as $key => $value) {
                    $sorgFile = $pratPath . "/" . $value;
                    $infoFile = pathinfo($sorgFile);
                    //query su pasdoc per recuperare il nome originale del file
                    $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "' AND PASFIL = '" . $value . "'";
                    $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                    if (!$ignoraProtocollo) {
                        if ($file_rec['PASPRTCLASS'] != '') {
                            continue;
                        }
                    }
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }

                    //
                    //Se non c'è l'eml cerco i p7m
                    //
                    if (strtolower($infoFile['extension']) == 'p7m') {
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;

                            $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                            $base64File = base64_encode($binFile);
                            if ($base64File === false) {
                                $this->message = "errore in codifica Allegato: " . $sorgFile;
                                return false;
                                //Out::msgStop("Preparazione Allegato", "errore in codifica Allegato: " . $sorgFile);
                            }
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                                $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                                $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                            return false;
                        }
                        fclose($fp);
                        break; // Esco perchè prendo il primo p7m che incontro come documento principale
                    }
                }
            }

            if (!$arrayDoc) {
                foreach ($arrayfile as $key => $value) {
                    $sorgFile = $pratPath . "/" . $value;
                    $infoFile = pathinfo($sorgFile);
                    //query su pasdoc per recuperare il nome originale del file
                    $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "' AND PASFIL = '" . $value . "'";
                    $file_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
                    if (!$ignoraProtocollo) {
                        if ($file_rec['PASPRTCLASS'] != '') {
                            continue;
                        }
                    }
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }

                    /*
                     * Se non co sono neanche p7m cerco il primo pdf.
                     * Per le pratiche inserite da anagrafica dove può capitare che non ci siano ne eml ne p7m
                     */
                    if (strtolower($infoFile['extension']) == 'pdf') {
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                            $base64File = base64_encode($binFile);
                            if ($base64File === false) {
                                $this->message = "errore in codifica Allegato: " . $sorgFile;
                                return false;
                                //Out::msgStop("Preparazione Allegato", "errore in codifica Allegato: " . $sorgFile);
                            }
                            $flag_principale = true;
                            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                                $arrayDoc['Principale']['filePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                                $arrayDoc['Principale']['nomeFile'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                                $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                                $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                                $devLib = new devLib();
                                $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                                $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                                $arrayDoc['Principale']['sintesiContenuto'] = '';
                                $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['note'] = '';
                                $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                            } else {
                                $arrayDoc['Principale']['FilePath'] = $pratPath . "/" . $file_rec['PASFIL'];
                                $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta";
                            return false;
                        }
                        fclose($fp);
                        break; // Esco perchè prendo il primo pdf che incontro come documento principale
                    }
                }
            }

            //estraggo i documenti allegati passi
            $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . $proges_rec['GESNUM'] . "%'";
            $pasdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false);

            //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
            $index = 0;
            foreach ($pasdoc_tab as $key => $pasdoc_rec) {
                if (!$ignoraProtocollo) {
                    if ($pasdoc_rec['PASPRTCLASS'] != '') {
                        continue;
                    }
                }
                if ($arrayDoc['Principale']['Nome'] == $pasdoc_rec['PASNAME']) {
                    continue;
                }
                if ($arrayDoc['Principale']['nomeFile'] == $pasdoc_rec['PASNAME']) {
                    continue;
                }
                $sorgFile = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                if (!file_exists($sorgFile)) {
                    continue;
                }
                $infoFile = pathinfo($sorgFile);
                if ($infoFile['extension'] != 'p7m' && $infoFile['extension'] != 'P7M') {
                    continue;
                }
                $fp = @fopen($sorgFile, "rb", 0);
                if ($fp) {
                    $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
                    $arrayDoc['pasdoc_rec'][] = $rowid_rec;

                    $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                    $base64File = base64_encode($binFile);
                    $arrayDoc['pasdoc_rec_allegati'][$index]['ROWID'] = $pasdoc_rec['ROWID'];
                    if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                        $arrayDoc['Allegati'][$index]['filePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                        $arrayDoc['Allegati'][$index]['nomeFile'] = $pasdoc_rec['PASNAME'];
                        $arrayDoc['Allegati'][$index]['estensione'] = $infoFile['extension'];
                        $arrayDoc['Allegati'][$index]['dataDocumento'] = date("Y-m-d\TH:i:s");
                        $arrayDoc['Allegati'][$index]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                        $arrayDoc['Allegati'][$index]['consultazionePubblica'] = "1";
                        $devLib = new devLib();
                        $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                        $arrayDoc['Allegati'][$index]['codiceOperatore'] = $codOp['CONFIG'];
                        $arrayDoc['Allegati'][$index]['sintesiContenuto'] = "";
                        $arrayDoc['Allegati'][$index]['descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                        $arrayDoc['Allegati'][$index]['note'] = "";
                        $arrayDoc['Allegati'][$index]['stream'] = ($downloadB64) ? $base64File : '';
                        $index++;
                    } else {
                        $arrayDoc['Allegati'][$index]['Documento']['FilePath'] = $pratPath . "/" . $pasdoc_rec['PASFIL'];
                        $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $pasdoc_rec['PASNAME'];
                        $arrayDoc['Allegati'][$index]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                        $arrayDoc['Allegati'][$index]['Descrizione'] = 'Documento allegato ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                        $index++;
                    }
                } else {
                    $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                    //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                    return false;
                }
                fclose($fp);
            }
        }

        /*
         * Se ancora non c'è un principale, prendo il primo e lo faccio diventare principale
         */
        if (!isset($arrayDoc['Principale'])) {
            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                $arrayDoc['Principale']['filePath'] = $arrayDoc['Allegati'][0]['filePath'];
                $arrayDoc['Principale']['nomeFile'] = $arrayDoc['Allegati'][0]['nomeFile'];
                $arrayDoc['Principale']['estensione'] = $arrayDoc['Allegati'][0]['estensione'];
                $arrayDoc['Principale']['dataDocumento'] = $arrayDoc['Allegati'][0]['dataDocumento'];
                $arrayDoc['Principale']['dataArchiviazione'] = $arrayDoc['Allegati'][0]['dataArchiviazione'];
                $arrayDoc['Principale']['consultazionePubblica'] = $arrayDoc['Allegati'][0]['consultazionePubblica'];
                $arrayDoc['Principale']['codiceOperatore'] = $arrayDoc['Allegati'][0]['codiceOperatore'];
                $arrayDoc['Principale']['sintesiContenuto'] = $arrayDoc['Allegati'][0]['sintesiContenuto'];
                $arrayDoc['Principale']['descrizione'] = $arrayDoc['Allegati'][0]['descrizione'];
                $arrayDoc['Principale']['note'] = $arrayDoc['Allegati'][0]['note'];
                $arrayDoc['Principale']['stream'] = $arrayDoc['Allegati'][0]['stream'];
            } else {
                $arrayDoc['Principale']['FilePath'] = $arrayDoc['Allegati'][0]['Documento']['FilePath'];
                $arrayDoc['Principale']['Nome'] = $arrayDoc['Allegati'][0]['Documento']['Nome'];
                $arrayDoc['Principale']['Stream'] = $arrayDoc['Allegati'][0]['Documento']['Stream'];
                $arrayDoc['Principale']['Descrizione'] = $arrayDoc['Allegati'][0]['Descrizione'];
            }
            unset($arrayDoc['Allegati'][0]);
            $flag_principale = true;
        }

        //
        //se non ho trovato un documento principale e se il num di protocollo non c'è, non seleziono nessun documento da allegare
        //        
        if (!$flag_principale && $proges_rec['GESNPR'] == 0) {
            $arrayDoc = array();
        }

        if ($tipo == "Iride" || $tipo == 'Jiride') {
            //Se è un P7M metto l'estensione originale punto P7M nel file principale		
            if (isset($arrayDoc['Principale']['estensione']) && $arrayDoc['Principale']['estensione'] != '') {
                $est1P = $arrayDoc['Principale']['estensione'];
                $nome1P = pathinfo($arrayDoc['Principale']['nomeFile'], PATHINFO_FILENAME);
                $est2P = pathinfo($nome1P, PATHINFO_EXTENSION);
                $arrayDoc['Principale']['estensione'] = $est1P;
                if (strtolower($est2P) == "pdf" || strtolower($est2P) == "jpg" || strtolower($est2P) == "doc" || strtolower($est2P) == "docx" || strtolower($est2P) == "rtf") {
                    $arrayDoc['Principale']['estensione'] = $est2P . "." . $est1P;
                }
            }

            foreach ($arrayDoc['Allegati'] as $key => $doc) {
                //Se è un P7M metto l'estensione originale punto P7M negli altri allegati		
                $est1 = $doc['estensione'];
                $nome1 = pathinfo($doc['nomeFile'], PATHINFO_FILENAME);
                $est2 = pathinfo($nome1, PATHINFO_EXTENSION);
                $arrayDoc['Allegati'][$key]['estensione'] = $est1;
                if (strtolower($est2) == "pdf" || strtolower($est2) == "jpg" || strtolower($est2) == "doc" || strtolower($est2) == "docx" || strtolower($est2) == "rtf") {
                    $arrayDoc['Allegati'][$key]['estensione'] = $est2 . "." . $est1;
                }
            }
        }
        return $arrayDoc;
    }

    public function getAllegatiProtocollaComunicazione($tipo = 'Paleo', $downloadB64 = true, $tipoCom = "", $filtriRowid = array()) {
        $arrayDoc = array();
        $descXhtml = array();
        if ($tipoCom == "A") {
            if ($tipo == 'Iride' || $tipo == 'Jiride' || $tipo == 'Italsoft-remoto-allegati' || $tipo == 'Paleo4' || $tipo == 'Italsoft-ws' || $tipo == 'E-Lios') {
                $whereTipo = '';
            } else {
                $whereTipo = " AND (PASCLA LIKE 'COMUNICAZIONE%' OR PASCLA LIKE 'INTEGRAZIONE%' OR PASCLA LIKE 'ANNULLAMENTO%')";
            }
        } else {
            //} else if ($tipoCom == "P") {
            $whereTipo = " AND (PASCLA NOT LIKE 'COMUNICAZIONE%' OR PASCLA NOT LIKE 'INTEGRAZIONE%' OR PASCLA NOT LIKE 'ANNULLAMENTO%')";
        }
        $whereRowid = "";
        $rowidPrincipale = "";
        if ($filtriRowid) {
            if ($filtriRowid === "NO") {
                $whereRowid .= " AND ROWID = -1";
            } else {
                $rowidPrincipale = $filtriRowid[0];
                unset($filtriRowid[0]);
                if (count($filtriRowid) == 1) {
                    //$whereRowid = " AND ROWID = " . $filtriRowid[0];
                    $whereRowid = " AND ROWID = " . reset($filtriRowid);
                } else {
                    $whereRowid .= " AND (ROWID = -1";
                    foreach ($filtriRowid as $rowid) {
                        $whereRowid .= " OR ROWID = " . $rowid;
                    }
                    $whereRowid .= ")";
                }
            }
        }

        $propas_rec = $this->praLib->GetPropas($this->chiavePasso, 'propak');
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], 'PASSO', false);


        if ($rowidPrincipale) {
            $allPrinc_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PASDOC WHERE ROWID = '$rowidPrincipale' AND PASPRTCLASS = ''", false);
            if (!$allPrinc_rec) {
                Out::msgStop("Errore", "Record allegato principale non trovato.");
                return false;
            }
            $sorgFile = $pramPath . "/" . $allPrinc_rec['PASFIL'];
            $infoFile = pathinfo($sorgFile);
            $pasfil = $allPrinc_rec['PASFIL'];
            $pasname = $allPrinc_rec['PASNAME'];
            if (strtolower($infoFile['extension']) == 'xhtml' || strtolower($infoFile['extension']) == 'docx') {
                $pdf_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf';
                $p7m_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.p7m';
                $p7m2_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m';
                $p7m3_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m.p7m';
                if (file_exists($p7m3_file)) {
                    $sorgFile = $p7m3_file;
                    $infoFile['extension'] = "pdf.p7m.p7m";
                } elseif (file_exists($p7m2_file)) {
                    $sorgFile = $p7m2_file;
                    $infoFile['extension'] = "pdf.p7m";
                } elseif (file_exists($p7m_file)) {
                    $sorgFile = $p7m_file;
                    $infoFile['extension'] = "p7m";
                } elseif (file_exists($pdf_file)) {
                    $sorgFile = $pdf_file;
                    $infoFile['extension'] = "pdf";
                }

                $pasfil = pathinfo($sorgFile, PATHINFO_FILENAME) . "." . pathinfo($sorgFile, PATHINFO_EXTENSION);
                if ($allPrinc_rec['PASNAME'] != '') {
                    $pasname = pathinfo($allPrinc_rec['PASNAME'], PATHINFO_FILENAME) . "." . $infoFile['extension'];
                }
            }


            $fp = @fopen($sorgFile, "rb", 0);
            if ($fp) {
                $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                $base64File = base64_encode($binFile);
                $infoFile = pathinfo($sorgFile);
                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                    $arrayDoc['Principale']['filePath'] = $pramPath . "/" . $pasfil;
                    $arrayDoc['Principale']['nomeFile'] = $pasname;
                    $arrayDoc['Principale']['estensione'] = $infoFile['extension'];
                    $arrayDoc['Principale']['dataDocumento'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Principale']['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Principale']['consultazionePubblica'] = "1"; //come distinguere se un documento è pubblico o meno?
                    $devLib = new devLib();
                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                    $arrayDoc['Principale']['codiceOperatore'] = $codOp['CONFIG'];
                    $arrayDoc['Principale']['sintesiContenuto'] = '';
                    $arrayDoc['Principale']['descrizione'] = 'Documento principale ' . $allPrinc_rec['PASCLA'] . ' - ' . $allPrinc_rec['PASNOT'];
                    $arrayDoc['Principale']['note'] = '';
                    $arrayDoc['Principale']['stream'] = ($downloadB64) ? $base64File : '';
                } else {
                    $arrayDoc['Principale']['FilePath'] = $pramPath . "/" . $pasfil;
                    $arrayDoc['Principale']['Nome'] = $pasname;
                    $arrayDoc['Principale']['Stream'] = ($downloadB64) ? $base64File : '';
                    $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $allPrinc_rec['PASCLA'] . ' - ' . $allPrinc_rec['PASNOT'];
                    $arrayDoc['Principale']['ROWID'] = $allPrinc_rec['ROWID'];
                }
            } else {
                Out::msgStop("Errore", "Errore in fase di trasferimento file principale. Procedura interrotta<br>" . $sorgFile);
                return false;
            }
            fclose($fp);
        }

        $lista = $this->elencaFiles($pramPath);
        $i = 0;
        foreach ($lista as $fileName) {
            $sorgFile = $pramPath . "/" . $fileName;
            if (!file_exists($sorgFile)) {
                continue;
            }
            $infoFile = pathinfo($sorgFile);
            if ($infoFile['extension'] == 'INFO' || $infoFile['extension'] == 'info') {
                continue;
            }
            $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $propas_rec['PROPAK'] . "' AND PASFIL LIKE '%" . $fileName . "%'"
                    . " AND PASPRTCLASS = '' $whereTipo" . $whereRowid;
            $allegato_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            if (!$allegato_rec) {
                continue;
            }
            $descXhtml = $infoFile['filename'];
//
// Trattamento speciale per files XHTML cerco il pdf compilato o p7m Firmasto
//
            if (strtolower($infoFile['extension']) == 'xhtml' || strtolower($infoFile['extension']) == 'docx') {
                $pdf_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf';
                $p7m_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.p7m';
                $p7m2_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m';
                $p7m3_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.pdf.p7m.p7m';
                if (file_exists($p7m3_file)) {
                    $sorgFile = $p7m3_file;
                    $infoFile['extension'] = "pdf.p7m.p7m";
                } elseif (file_exists($p7m2_file)) {
                    $sorgFile = $p7m2_file;
                    $infoFile['extension'] = "pdf.p7m";
                } elseif (file_exists($p7m_file)) {
                    $sorgFile = $p7m_file;
                    $infoFile['extension'] = "p7m";
                } elseif (file_exists($pdf_file)) {
                    $sorgFile = $pdf_file;
                    $infoFile['extension'] = "pdf";
                } else {
                    if ($infoFile['extension'] == 'xhtml') {
                        continue;
                    }
                }

                $allegato_rec[0]['PASFIL'] = pathinfo($sorgFile, PATHINFO_FILENAME) . "." . pathinfo($sorgFile, PATHINFO_EXTENSION);
                if ($allegato_rec[0]['PASNAME'] != '') {
                    //$allegato_rec[0]['PASNAME'] = pathinfo($allegato_rec[0]['PASNAME'], PATHINFO_FILENAME) . "." . pathinfo($sorgFile, PATHINFO_EXTENSION);
                    $allegato_rec[0]['PASNAME'] = pathinfo($allegato_rec[0]['PASNAME'], PATHINFO_FILENAME) . "." . $infoFile['extension'];
                }
            } else {
                $xhtml_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.xhtml';
                if (file_exists($xhtml_file)) {
                    continue;
                }
                $xhtml_file = $infoFile['dirname'] . "/" . $infoFile['filename'] . '.XHTML';
                if (file_exists($xhtml_file)) {
                    continue;
                }
            }
            if ($allegato_rec[0]['PASNAME'] == '') {
                $allegato_rec[0]['PASNAME'] = $allegato_rec[0]['PASFIL'];
            }

            if ($tipo == 'Iride' || $tipo == 'Jiride') {
                $est1 = $infoFile['extension'];
                $nome1 = $infoFile['filename'];
                $est2 = pathinfo($nome1, PATHINFO_EXTENSION);
                $estensione = $est1;
                if (strtolower($est2) == "pdf" || strtolower($est2) == "jpg" || strtolower($est2) == "doc" || strtolower($est2) == "docx" || strtolower($est2) == "rtf") {
                    $estensione = $est2 . "." . $est1;
                }
            } else {
                $estensione = $infoFile['extension'];
            }
            $fp = @fopen($sorgFile, "rb", 0);
            if ($fp) {
                $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                $base64File = base64_encode($binFile);
                $arrayDoc['pasdoc_rec'][$i]['ROWID'] = $allegato_rec[0]['ROWID'];
                //utilizzato da praPasso->GetDatiMailProtocollo()
                if ($tipo == 'WSPU' || $tipo == "Iride" || $tipo == "Jiride" || $tipo == 'Italsoft-remoto-allegati') {
                    $arrayDoc['Allegati'][$i]['filePath'] = $pramPath . "/" . $allegato_rec[0]['PASFIL'];
                    $arrayDoc['Allegati'][$i]['nomeFile'] = $allegato_rec[0]['PASNAME'];
                    //$arrayDoc['Allegati'][$i]['estensione'] = pathinfo($allegato_rec[0]['PASNAME'], PATHINFO_EXTENSION);
                    $arrayDoc['Allegati'][$i]['estensione'] = $estensione;
                    $arrayDoc['Allegati'][$i]['dataDocumento'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Allegati'][$i]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Allegati'][$i]['consultazionePubblica'] = "1";
                    $devLib = new devLib();
                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                    $arrayDoc['Allegati'][$i]['codiceOperatore'] = $codOp['CONFIG'];
                    $arrayDoc['Allegati'][$i]['sintesiContenuto'] = "";
                    $arrayDoc['Allegati'][$i]['descrizione'] = $allegato_rec[0]['PASNOT'];
                    $arrayDoc['Allegati'][$i]['note'] = "";
                    $arrayDoc['Allegati'][$i]['stream'] = ($downloadB64) ? $base64File : '';
                } else {
                    $arrayDoc['Allegati'][$i]['Documento']['FilePath'] = $pramPath . "/" . $allegato_rec[0]['PASFIL'];
                    $arrayDoc['Allegati'][$i]['Documento']['Nome'] = $allegato_rec[0]['PASNAME'];
                    $arrayDoc['Allegati'][$i]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                    $sql = "SELECT PASNOT FROM PASDOC WHERE PASKEY = '" . $propas_rec['PROPAK'] . "' AND PASFIL LIKE '%" . $fileName . "%'";
                    $allegato_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
                    if ($allegato_rec) {
                        $arrayDoc['Allegati'][$i]['Descrizione'] = $allegato_rec[0]['PASNOT'];
                    } else {
                        $arrayDoc['Allegati'][$i]['Descrizione'] = $descXhtml;
                    }
                }
//                }
                $i++;
            } else {
                Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                break;
            }
            fclose($fp);
        }

        if ($rowidPrincipale) {
            $arrayDoc['pasdoc_rec'][]['ROWID'] = $rowidPrincipale;
        }

//        if ($tipoCom == "P") {
//            $arrayAlleRicevute = $this->getRicevutePartenza($tipo, $downloadB64);
//            if ($arrayAlleRicevute) {
//                $arrayDoc = array_merge($arrayDoc, $arrayAlleRicevute);
//            }
//        }
        //file_put_contents("/users/pc/dos2ux/Andrea/protLog.log", print_r($arrayDoc, true));
        return $arrayDoc;
    }

    /**
     * Filtra gli allegati in base al parametro di grandezza massima nei parametri generali
     * @param type $arrayDoc
     * @param type $tipo
     * @return type
     */
    function GetAllegatiNonProt($arrayDoc, $tipo) {
        if ($tipo == "Paleo" || $tipo == "Paleo4") {
            $strNoProt = "";
            $filent_rec = $this->praLib->GetFilent(26);
            if ($filent_rec['FILVAL'] == "")
                $filent_rec['FILVAL'] = "0";
            if ($filent_rec['FILVAL'] != "0") {
                $max_file_size = $filent_rec['FILVAL'] * 1024 * 1024;
                $arrKeys = array();

                //
                //Mi scorro gli allegati e scarto quelli con grandezza maggiore del paramentro
                //
                foreach ($arrayDoc['Allegati'] as $keyAlle => $allegato) {
                    //if (strlen($allegato['Documento']['Stream']) > $max_file_size) {
                    if (filesize($allegato['Documento']['FilePath']) > $max_file_size) {
                        $arrKeys[$keyAlle] = $keyAlle;
                        $strNoProt .= "<div>- Allegato <b>" . $allegato['Documento']['Nome'] . "</b> non protocollato perchè più grande di " . $filent_rec['FILVAL'] . " MB</div>";
                        unset($arrayDoc['Allegati'][$keyAlle]);
                    }
                }
                foreach ($arrKeys as $keyAlle => $value) {
                    unset($arrayDoc['pasdoc_rec'][$keyAlle]);
                }

                //
                //Se la grandezza del documento principale è più grande del parametro lo scarto
                //
                //if (strlen($arrayDoc['Principale']['Stream']) > $max_file_size) {
                if (filesize($arrayDoc['Principale']['FilePath']) > $max_file_size) {
                    foreach ($arrayDoc['pasdoc_rec'] as $key => $alle) {
                        if ($alle['ROWID'] == $arrayDoc['Principale']['ROWID']) {
                            unset($arrayDoc['pasdoc_rec'][$key]);
                            break;
                        }
                    }
                    $strNoProt .= "<div>- Allegato Principale <b>" . $arrayDoc['Principale']['Nome'] . "</b> non protocollato perchè più grande di " . $filent_rec['FILVAL'] . " MB</div>";
                    unset($arrayDoc['Principale']);
                }
            }
        }
        return array("arrayDoc" => $arrayDoc, "strNoProt" => $strNoProt);
    }

    private function elencaFiles($dirname) {
        $arrayfiles = Array();
        if (file_exists($dirname)) {
            $handle = opendir($dirname);
            while (false !== ($file = readdir($handle))) {
                if (is_file($dirname . "/" . $file)) {
                    array_push($arrayfiles, $file);
                }
            }
            closedir($handle);
        }
        sort($arrayfiles);  //ordinamento alfabetico
        return $arrayfiles;
    }

    private function setFirmatarioProtocolloDaSportello($proges_rec) {
        if ($proges_rec['GESSPA'] != 0) { //sportello aggregato
            $Anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            if ($Anaspa_rec['SPAFIRMPA']) {
                return $Anaspa_rec['SPAFIRMPA'];
            }
        }
        if ($proges_rec['GESTSP'] != 0) { //sportello on line
            $Anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            if ($Anatsp_rec['TSPFIRMPA']) { //sportello
                return $Anatsp_rec['TSPFIRMPA'];
            }
        }
    }

    private function setFirmatarioProtocollo($gesres) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        $proLib = new proLib();
        $ananom_rec = $this->praLib->GetAnanom($gesres);
        $anamed_rec = array();
        if ($ananom_rec['NOMDEP']) {
            $anamed_rec = $proLib->GetAnamed($ananom_rec['NOMDEP'], 'codice', 'no');
        }
        $destinatario = array();
        if ($anamed_rec) {
            $firmatario['CodiceDestinatario'] = $anamed_rec['MEDCOD'];
            $firmatario['Denominazione'] = $anamed_rec['MEDNOM'];
            $firmatario['Indirizzo'] = $anamed_rec['MEDIND'];
            $firmatario['CAP'] = $anamed_rec['MEDCAP'];
            $firmatario['Citta'] = $anamed_rec['MEDCIT'];
            $firmatario['Provincia'] = $anamed_rec['MEDPRO'];
            $firmatario['Provincia'] = $anamed_rec['MEDPRO'];
            $firmatario['Annotazioni'] = $anamed_rec['MEDNOTE'];
            $firmatario['Email'] = $anamed_rec['MEDEMA'];
            $uffdes_tab = $proLib->GetUffdes($anamed_rec['MEDCOD'], 'uffkey');
            $firmatario['Ufficio'] = $uffdes_tab[0]['UFFCOD'];

            return $firmatario;
        } else {
            return false;
        }
    }

    public function setDestinatarioProtocollo($gesres) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        $proLib = new proLib();
        $ananom_rec = $this->praLib->GetAnanom($gesres);
        $anamed_rec = array();
        if ($ananom_rec['NOMDEP']) {
            $anamed_rec = $proLib->GetAnamed($ananom_rec['NOMDEP'], 'codice', 'no');
        }
        $destinatario = array();
        if ($anamed_rec) {
            $destinatario['CodiceDestinatario'] = $anamed_rec['MEDCOD'];
            $destinatario['Denominazione'] = $anamed_rec['MEDNOM'];
            $destinatario['Indirizzo'] = $anamed_rec['MEDIND'];
            $destinatario['CAP'] = $anamed_rec['MEDCAP'];
            $destinatario['Citta'] = $anamed_rec['MEDCIT'];
            $destinatario['Provincia'] = $anamed_rec['MEDPRO'];
            $destinatario['Provincia'] = $anamed_rec['MEDPRO'];
            $destinatario['Annotazioni'] = $anamed_rec['MEDNOTE'];
            $destinatario['Email'] = $anamed_rec['MEDEMA'];
            $uffdes_tab = $proLib->GetUffdes($anamed_rec['MEDCOD'], 'uffkey');
            $destinatario['Gestione'] = $uffdes_tab[0]['UFFFI1__1'];
            $destinatario['Ufficio'] = $uffdes_tab[0]['UFFCOD'];
            return $destinatario;
        } else {
            return false;
        }
    }

    public function setUfficiProtocollo($uffkey) {
        $uffici = array();
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        $proLib = new proLib();
        //$uffdes_tab = $proLib->GetUffdes($uffkey, 'uffkey');
        $uffdes_tab = $proLib->GetUffdes($uffkey, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
        foreach ($uffdes_tab as $uffdes_rec) {
            $anauff_rec = $proLib->GetAnauff($uffdes_rec['UFFCOD']);
            $ufficio = array();
            $ufficio['CodiceUfficio'] = $uffdes_rec['UFFCOD'];
            $ufficio['Descrizione'] = $anauff_rec['UFFDES'];
            $ufficio['Scarica'] = $uffdes_rec['UFFSCA'];
            $uffici[] = $ufficio;
        }
        return $uffici;
    }

    public function AnnullaChiudiPratica($rowidStato, $tipo, $dataChiusura) {
        $praLibFascicoloArch = new praLibFascicoloArch();
        if ($tipo == "ANNULLA") {
            $operaz = "Annullamento";
        } elseif ($tipo == "CHIUDI") {
            $operaz = "Chiusura";
        }
        if ($tipo == "CHIUDI") {
            if (!$praLibFascicoloArch->ControlliFascicoloArchivistico($this->codicePratica)) {
                Out::msgStop("Errore", "Non è possibile chiudere il fascicolo, parametri mancati per la creazione del fascicolo Archivistico.<br>" . $praLibFascicoloArch->getErrMessage());
                return false;
            }
        }
        $Propas_rec = array();
        $Propas_rec['PRONUM'] = $this->codicePratica;
        $Propas_rec['PROPAK'] = $this->praLib->PropakGenerator($this->codicePratica);
        $Propas_rec['PRODPA'] = "$operaz pratica n. $this->codicePratica";
        $Propas_rec['PRODTP'] = "$operaz pratica";
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PROSTATO'] = $rowidStato;
        $Propas_rec['PROINI'] = $Propas_rec['PROFIN'] = date("Ymd");
        $Propas_rec['PROUTEADD'] = $Propas_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $Propas_rec['PRODATEADD'] = $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $utenti_rec = $this->praLib->GetUtente(App::$utente->getKey('nomeUtente'));
        $Propas_rec['PRORPA'] = $utenti_rec['UTEANA__3'];
        try {
            $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "PROPAS", 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore", $exc->getMessage());
            return false;
        }
        if (!$this->praLib->ordinaPassi($this->codicePratica)) {
            Out::msgStop("Errore", $this->praLib->getErrMessage());
        }
        if (!$this->praLib->sincronizzaStato($this->codicePratica, $dataChiusura)) {
            Out::msgStop("Errore", $this->praLib->getErrMessage());
        }

        if ($tipo == "CHIUDI") {
            if (!$praLibFascicoloArch->CreazioneFascicoloArchivistico($this->codicePratica, $this)) {
                Out::msgStop("Errore", "Errore in creazione Fascicolo Archivistico. " . $praLibFascicoloArch->getErrMessage());
                return false;
            } else {
                $Geskey = $praLibFascicoloArch->getGeskeyCreato();
                if ($Geskey) {
                    Out::msgInfo('Fascicolo Archivistico', "Il fascicolo archivistico è stato creato correttamente: $Geskey.");
                } else {
                    // E' gia creato
                }
            }
        }
        return true;
    }

    function getRicevutePartenza($tipo = 'Paleo', $downloadB64 = true) {
        //$pracomP_rec = $this->GetPracomP($this->chiavePasso);
        $arrayDoc = array();
        $emlLib = new emlLib();
        $pramail_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PRAMAIL WHERE COMPAK = '$this->chiavePasso' AND FLPROT=0 AND (TIPORICEVUTA = 'accettazione' OR TIPORICEVUTA = 'avvenuta-consegna')", true);
        $i = 0;
        foreach ($pramail_tab as $pramail_rec) {
            $mail_archivio_rec = $emlLib->getMailArchivio($pramail_rec['IDMAIL']);
            $pathMail = $emlLib->SetDirectory($mail_archivio_rec['ACCOUNT']);
            $sorgFile = $pathMail . $mail_archivio_rec['DATAFILE'];
            $fp = @fopen($sorgFile, "rb", 0);
            if ($fp) {
                $binFile = filesize($sorgFile) > 0 ? fread($fp, filesize($sorgFile)) : '';
                $base64File = base64_encode($binFile);
                $arrayDoc['pramail_rec'][$i]['ROWID'] = $pramail_rec['ROWID'];
                if ($tipo == 'WSPU' || $tipo == "Iride" || $tipo == "Jiride" || $tipo == 'Italsoft-remoto-allegati') {
                    $arrayDoc['Ricevute'][$i]['filePath'] = $sorgFile;
                    $arrayDoc['Ricevute'][$i]['nomeFile'] = $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . ".eml";
                    $arrayDoc['Ricevute'][$i]['estensione'] = "eml";
                    $arrayDoc['Ricevute'][$i]['dataDocumento'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Ricevute'][$i]['dataArchiviazione'] = date("Y-m-d\TH:i:s");
                    $arrayDoc['Ricevute'][$i]['consultazionePubblica'] = "1";
                    $devLib = new devLib();
                    $codOp = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
                    $arrayDoc['Ricevute'][$i]['codiceOperatore'] = $codOp['CONFIG'];
                    $arrayDoc['Ricevute'][$i]['sintesiContenuto'] = "";
                    $arrayDoc['Ricevute'][$i]['descrizione'] = $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'];
                    $arrayDoc['Ricevute'][$i]['note'] = "";
                    $arrayDoc['Ricevute'][$i]['stream'] = ($downloadB64) ? $base64File : '';
                } else {
                    $arrayDoc['Ricevute'][$i]['Documento']['FilePath'] = $sorgFile;
                    $arrayDoc['Ricevute'][$i]['Documento']['Nome'] = $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . ".eml";
                    $arrayDoc['Ricevute'][$i]['Documento']['Stream'] = ($downloadB64) ? $base64File : '';
                    $arrayDoc['Ricevute'][$i]['Descrizione'] = $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'];
                }
                $i++;
            } else {
                Out::msgStop("Errore", "Errore in fase di trasferimento file ricevuta pec. Procedura interrotta<br>" . $sorgFile . "");
                return false;
            }
            fclose($fp);
        }
        return $arrayDoc;
    }

    function GetMittenteProtocollo() {
        $Anades_rec_mittente = array();

        /*
         * Mittente di default preso da ANADES
         */
        $Anades_rec_mittente = $this->praLib->GetAnades($this->codicePratica, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']);

        /*
         * Mi trovo il prefisso del soggetto dalle 3 preferenze nei parametri
         */
        $anapar_rec_mitt1 = $this->praLib->GetAnapar("FIRST_CHOICE_MITTPROT", "parkey", false);
        if ($anapar_rec_mitt1['PARVAL']) {
            $prefisso1 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt1['PARVAL']);
        }
        $anapar_rec_mitt2 = $this->praLib->GetAnapar("SECOND_CHOICE_MITTPROT", "parkey", false);
        if ($anapar_rec_mitt2['PARVAL']) {
            $prefisso2 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt2['PARVAL']);
        }
        $anapar_rec_mitt3 = $this->praLib->GetAnapar("THIRD_CHOICE_MITTPROT", "parkey", false);
        if ($anapar_rec_mitt3['PARVAL']) {
            $prefisso3 = praRuolo::getSystemSubjectRoleFields($anapar_rec_mitt3['PARVAL']);
        }

        /*
         * Se il prefisso è valorizzato, mi trovo l'ANADES ad esso collegato
         */
        if ($prefisso1) {
            $Anades_rec_prefisso1 = $this->praLib->GetAnades($this->codicePratica, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES[$prefisso1]['RUOCOD']);
        }
        if ($prefisso2) {
            $Anades_rec_prefisso2 = $this->praLib->GetAnades($this->codicePratica, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES[$prefisso2]['RUOCOD']);
        }
        if ($prefisso3) {
            $Anades_rec_prefisso3 = $this->praLib->GetAnades($this->codicePratica, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES[$prefisso3]['RUOCOD']);
        }

        /*
         * Ora scelgo il record Mittente in base alla preferenze e al fatto se sono valorizzati gli ANADES per ciascun prefisso
         */
        if ($Anades_rec_prefisso1) {
            $Anades_rec_mittente = $Anades_rec_prefisso1;
        } else {
            if ($Anades_rec_prefisso2) {
                $Anades_rec_mittente = $Anades_rec_prefisso2;
            } else {
                if ($Anades_rec_prefisso3) {
                    $Anades_rec_mittente = $Anades_rec_prefisso3;
                }
            }
        }

        return $Anades_rec_mittente;
    }

    function getElementiProtocollazionePasso($idScelti, $tipo = "A") {
        $proges_rec = $this->praLib->GetProges($this->codicePratica, 'codice');

        /*
         * Prendo il tipo protocollo
         */
        $tipoProtClasse = $this->praLib->getTipoProtocollo($this->codicePratica);

        /*
         * Inizializzo il tipo di protocollo da istanziare automatico da param. ente
         */
//        $tipoProtClasse = proWsClientFactory::getAutoDriver();
//
//        /*
//         * Carico un array con i dati di protocollazione specifici dell'aggregato se presente
//         */
//        $arrDatiProtAggr = array();
//        if ($proges_rec['GESSPA'] != 0) {
//            $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($proges_rec['GESSPA']);
//        }
//
//        /*
//         * Se c'è il tipo protocolli nell'aggregato, sovrascrivo il tipo protocollo dell'ente
//         */
//        if ($arrDatiProtAggr['TipoProtocollo']) {
//            $tipoProtClasse = $arrDatiProtAggr['TipoProtocollo'];
//        }

        $arrayDoc = $this->getAllegatiProtocollaComunicazione($tipoProtClasse, true, $tipo, $idScelti);
        if ($tipoProtClasse == proWsClientHelper::CLIENT_PALEO4) {
            $arrayDocFiltrati = $this->GetAllegatiNonProt($arrayDoc, $tipoProtClasse);
            $arrayDoc = $arrayDocFiltrati['arrayDoc'];
            $strNoProt = $arrayDocFiltrati['strNoProt'];
        }


        //
        if ($tipo == "A") {
            $elementi = $this->protocollaArrivo();
        } else {
            $elementi = $this->protocollaPartenza($tipo);
        }

        if (!$elementi) {
            $retPrt["Status"] = "-1";
            $retPrt["Message"] = "Impossibile reperire i dati per la protocollazione per il passo con chiave $this->chiavePasso";
            $retPrt["RetValue"] = false;
            return $retPrt;
        }

        if ($arrayDoc) {
            $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
            $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
            $elementi['dati']['pasdoc_rec'] = $arrayDoc['pasdoc_rec'];
            $elementi['dati']['DocumentiRicevute'] = $arrayDoc['Ricevute'];
            $elementi['dati']['arrayDoc'] = $arrayDoc;
            if ($strNoProt) {
                $elementi['dati']['strNoProt'] = $strNoProt;
            }
        }

        /*
         * Se ci sono i metadati e il tipo protocollo nell'aggreggato, li inserisco nell'array elementi
         */
        $elementi['TipoProtocollo'] = $tipoProtClasse;
        $arrDatiProtAggr = array();
        if ($proges_rec['GESSPA'] != 0) {
            $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($proges_rec['GESSPA']);
        }
        if ($arrDatiProtAggr) {
            $elementi['MetaDatiProtocollo'] = $arrDatiProtAggr['MetadatiProtocollo'];
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "array elementi passo preso correttamente";
        $ritorno["RetValue"] = true;
        $ritorno["Elementi"] = $elementi;
        return $ritorno;
    }

    function getElementiProtocollazionePratica($onlyMainDoc = false) {
        $proges_rec = $this->praLib->GetProges($this->codicePratica);

        /*
         * Prendo il tipo protocollo
         */
        $tipoProtClasse = $this->praLib->getTipoProtocollo($this->codicePratica);

        /*
         * Inizializzo il tipo di protocollo da istanziare automatico da param. ente
         */
//        $tipoProtClasse = proWsClientFactory::getAutoDriver();
//
//        /*
//         * Carico un array con i dati di protocollazione specifici dell'aggregato se presente
//         */
//        $arrDatiProtAggr = array();
//        if ($proges_rec['GESSPA'] != 0) {
//            $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($proges_rec['GESSPA']);
//        }
//
//        /*
//         * Se c'è il tipo protocolli nell'aggregato, sovrascrivo il tipo protocollo dell'ente
//         */
//        if ($arrDatiProtAggr['TipoProtocollo']) {
//            $tipoProtClasse = $arrDatiProtAggr['TipoProtocollo'];
//        }

        $elementi = $this->getElementiProtocollaPratica();
        if (!$elementi) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile reperire gli elementi utili per la protocollazione.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        /*
         * Controllo presenza allegati
         */
        $arrayDoc = $this->getAllegatiProtocollaPratica($tipoProtClasse, true, $onlyMainDoc);
        if ($arrayDoc) {
            if ($tipoProtClasse == proWsClientHelper::CLIENT_PALEO4) {
                $arrayDocFiltrati = $this->GetAllegatiNonProt($arrayDoc, $tipoProtClasse);
                $arrayDoc = $arrayDocFiltrati['arrayDoc'];
                $strNoProt = $arrayDocFiltrati['strNoProt'];
            }
            $elementi['dati']['DocumentoPrincipale'] = $arrayDoc['Principale'];
            $elementi['dati']['DocumentiAllegati'] = $arrayDoc['Allegati'];
            $elementi['dati']['arrayDoc'] = $arrayDoc;
            if ($strNoProt) {
                $elementi['dati']['strNoProt'] = $strNoProt;
            }
        }

        /*
         * Se ci sono i metadati e il tipo protocollo nell'aggreggato, li inserisco nell'array elementi
         */
        $elementi['TipoProtocollo'] = $tipoProtClasse;
        $arrDatiProtAggr = array();
        if ($proges_rec['GESSPA'] != 0) {
            $arrDatiProtAggr = $this->praLib->getDatiProtocolloAggregato($proges_rec['GESSPA']);
        }
        if ($arrDatiProtAggr) {
            $elementi['MetaDatiProtocollo'] = $arrDatiProtAggr['MetadatiProtocollo'];
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "array elementi pratica preso correttamente";
        $ritorno["RetValue"] = true;
        $ritorno["Elementi"] = $elementi;
        return $ritorno;
    }

    public function protocollaPartenza($tipo = "P") {
        $propas_rec = $this->praLib->GetPropas($this->chiavePasso, 'propak');
        if (!$propas_rec) {
            // Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
            $propas_rec = $this->praLib->GetPropas($this->keyPasso, 'propak');
        }

        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }

        $pracomP_rec = $this->praLib->GetPracomP($propas_rec['PROPAK']);
        $pramitDest_tab = $this->praLib->GetPraDestinatari($propas_rec['PROPAK'], 'codice', true);
        $oggetto = $this->praLib->GetOggettoProtPartenza($propas_rec['PRONUM'], $propas_rec['PROPAK']);
        $elementi['tipo'] = $tipo;
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DataArrivo'] = $pracomP_rec['COMDAT'];
        $elementi['dati']['DenomComune'] = $denomComune;

        /*
         * Per retro compatibilità nel caso ci sia un vecchio proPaleo.class.php
         */
        $elementi['dati']['MittDest']['Denominazione'] = $pramitDest_tab[0]['NOME'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pramitDest_tab[0]['INDIRIZZO'];
        $elementi['dati']['MittDest']['CAP'] = $pramitDest_tab[0]['CAP'];
        $elementi['dati']['MittDest']['Citta'] = $pramitDest_tab[0]['COMUNE'];
        $elementi['dati']['MittDest']['Provincia'] = $pramitDest_tab[0]['PROVINCIA'];
        $elementi['dati']['MittDest']['Email'] = $pramitDest_tab[0]['MAIL'];
        $elementi['dati']['MittDest']['CF'] = $pramitDest_tab[0]['FISCALE'];

        /*
         * Nuovo tag MITTENTE dal 01/12/2016 (Principalmente per E-Lios)-->Prende i dati dello sportello on-line
         */
        $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
        $elementi['dati']['Mittente']['Denominazione'] = $anatsp_rec['TSPDEN'];
        $elementi['dati']['Mittente']['Indirizzo'] = $anatsp_rec['TSPIND'] . " " . $anatsp_rec['TSPNCI'];
        $elementi['dati']['Mittente']['CAP'] = $anatsp_rec['TSPCAP'];
        $elementi['dati']['Mittente']['Citta'] = $anatsp_rec['TSPCOM'];
        $elementi['dati']['Mittente']['Provincia'] = $anatsp_rec['TSPPRO'];
        $elementi['dati']['Mittente']['Email'] = $anatsp_rec['TSPPEC'];
        $elementi['dati']['Mittente']['CF'] = "";

        /*
         * Nuova versione destinatari multipli
         */
        $elementi['dati']['destinatari'] = array();
        foreach ($pramitDest_tab as $pramitDest_rec) {
            $destinatario = array();
            $destinatario['Codice'] = $pramitDest_rec['CODICE'];
            $destinatario['Denominazione'] = $pramitDest_rec['NOME'];
            $destinatario['Indirizzo'] = $pramitDest_rec['INDIRIZZO'];
            $destinatario['CAP'] = $pramitDest_rec['CAP'];
            $destinatario['Citta'] = $pramitDest_rec['COMUNE'];
            $destinatario['Provincia'] = $pramitDest_rec['PROVINCIA'];
            $destinatario['Email'] = $pramitDest_rec['MAIL'];
            $destinatario['CF'] = $pramitDest_rec['FISCALE'];
            $destinatario['uffici'] = $this->setUfficiProtocollo($destinatario['Codice']);
            $elementi['dati']['destinatari'][] = $destinatario;
        }

        $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
        if ($proges_rec['GESMETA']) {
            $metaDati = unserialize($proges_rec['GESMETA']);
            $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
            $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
        }
        $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $elementi['dati']['Ruolo'] = $anatsp_rec['TSPRUOLO'];
        $elementi['dati']['MittenteInterno'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoPar($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
        //
        $firmatario = $this->setFirmatarioProtocolloDaSportello($proges_rec);
        if ($firmatario) {
            $elementi['dati']['Firmatario'] = $firmatario;
        }

        $mittente = $this->setDestinatarioProtocollo($proges_rec['GESRES']);
        $elementi['mittenti'][0] = $mittente;
        $uffici = $this->setUfficiProtocollo($mittente['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;
        //
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
        //
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

    public function protocollaArrivo() {
        $elementi = array();
        $propas_rec = $this->praLib->GetPropas($this->chiavePasso, 'propak');

        /*
         * Modifica per consentire il recupero dal passo dopo la ricerca anagrafica via Web Service
         */
        if (!$propas_rec) {
            return $elementi;
        }
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $denomComune = $anaspa_rec["SPADES"];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $denomComune = $anatsp_rec["TSPDES"];
        }
        //$pracomP_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACOM WHERE COMPAK='" . $propas_rec['PROPAK'] . "' AND COMTIP='P'", false);
        $pracomP_rec = $this->praLib->GetPracomP($propas_rec['PROPAK']);
        $pracomA_rec = $this->praLib->GetPracomA($propas_rec['PROPAK']);
        $elementi['tipo'] = 'A';

        $praLibVar = new praLibVariabili();
        $Filent_rec = $this->praLib->GetFilent(5);

        $praLibVar->setCodicePratica($this->codicePratica);
        $praLibVar->setChiavePasso($this->chiavePasso);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $oggetto = $this->praLib->GetStringDecode($dictionaryValues, $Filent_rec['FILVAL']);
        $elementi['tipo'] = 'A';
        $elementi['dati']['Oggetto'] = $oggetto;
        $elementi['dati']['DenomComune'] = $denomComune;
        $elementi['dati']['DataArrivo'] = $pracomA_rec['COMDAT'];
        $elementi['dati']['ChiavePasso'] = $this->chiavePasso;
        $elementi['dati']['MittDest']['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['MittDest']['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['MittDest']['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['MittDest']['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['MittDest']['Provincia'] = $pracomA_rec['COMPRO'];
        if ($pracomP_rec['COMPRT']) {
            $elementi['dati']['NumeroAntecedente'] = substr($pracomP_rec['COMPRT'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($pracomP_rec['COMPRT'], 0, 4);
            if ($pracomP_rec['COMMETA']) {
                $metaDati = unserialize($pracomP_rec['COMMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "P"; //Tipo protocollo passo partenza 
        } else {
            $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
            if ($proges_rec['GESMETA']) {
                $metaDati = unserialize($proges_rec['GESMETA']);
                $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
            }
            $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        }
        $elementi['dati']['MittDest']['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['MittDest']['CF'] = $pracomA_rec['COMFIS'];

        $elementi['dati']['destinatari'] = array();
        $elementi['dati']['destinatari'][0]['Denominazione'] = $pracomA_rec['COMNOM'];
        $elementi['dati']['destinatari'][0]['Indirizzo'] = $pracomA_rec['COMIND'];
        $elementi['dati']['destinatari'][0]['CAP'] = $pracomA_rec['COMCAP'];
        $elementi['dati']['destinatari'][0]['Citta'] = $pracomA_rec['COMCIT'];
        $elementi['dati']['destinatari'][0]['Provincia'] = $pracomA_rec['COMPRO'];
        $elementi['dati']['destinatari'][0]['Email'] = $pracomA_rec['COMMLD'];
        $elementi['dati']['destinatari'][0]['CF'] = $pracomA_rec['COMFIS'];
        //
        $destinatario = $this->praLib->setDestinatarioProtocollo($proges_rec['GESRES']);
        $elementi['destinatari'][0] = $destinatario;
        $uffici = $this->praLib->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $elementi['uffici'] = $uffici;

        $classificazione = $this->praLib->GetClassificazioneProtocollazione($proges_rec['GESPRO'], $proges_rec['GESNUM']);
        $elementi['dati']['Classificazione'] = $classificazione;
        $elementi['dati']['MetaDati'] = unserialize($proges_rec['GESMETA']);
        $elementi['dati']['Ruolo'] = $anatsp_rec['TSPRUOLO'];
        $UfficioCarico = $this->praLib->GetUfficioCaricoProtocollazione($proges_rec);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;
        $TipoDocumentoProtocollo = $this->praLib->GetTipoDocumentoProtocollazioneEndoArr($proges_rec);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;
        //
        $praLibVar = new praLibVariabili();
        $Filent_recFasc = $this->praLib->GetFilent(30);
        $oggettoFasc = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_recFasc['FILVAL']);
        $elementi['dati']['Fascicolazione']['Oggetto'] = $oggettoFasc;
        //
        if ($proges_rec['GESSPA']) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $elementi['dati']['Aggregato']['Codice'] = $proges_rec['GESSPA'];
            $elementi['dati']['Aggregato']['CodAmm'] = $anaspa_rec['SPAAMMIPA'];
            $elementi['dati']['Aggregato']['CodAoo'] = $anaspa_rec['SPAAOO'];
        }
        return $elementi;
    }

}

?>
