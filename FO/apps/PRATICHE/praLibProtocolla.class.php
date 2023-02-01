<?php

/**
 * Description of praLibProtocolla
 *
 * @author michele
 */
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDati.class.php';

class praLibProtocolla {

    private $praLib;
    private $praLibDati;
    private $frontOfficeLib;
    private $PRAM_DB;
    private $ITAFRONTOFFICE_DB;
    private $errCode;
    private $errMessage;

    const RESULT_PROTOCOLLA_OK = 0;
    const RESULT_PROTOCOLLA_WARNING = 1;
    const RESULT_PROTOCOLLA_ERROR = 2;

    public function __construct() {
        $this->praLib = new praLib;
        $this->praLibDati = praLibDati::getInstance($this->praLib);
        $this->frontOfficeLib = new frontOfficeLib;
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        $this->ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function protocollaArrivo($dati, $TotaleAllegati, $arrayOggettoProt) {
        $praLib = new praLib();

        //
        //Leggo il tipo protocollo
        //
        $anapar_rec = $praLib->GetAnapar("TIPO_PROTOCOLLO", "parkey", $dati["PRAM_DB"], false);
        
        //
        // Dati base OK
        //
        $elementi['tipo'] = 'A';
        $elementi['dati']['NumRichiesta'] = $dati['Proric_rec']['RICNUM'];
        $elementi['dati']['Oggetto'] = $arrayOggettoProt['oggettoProtocollo'];
        $elementi['dati']['DataArrivo'] = date('Ymd');

        //
        // NON UTILIZZATO PER QUESTO TIPO DI PROTOCOLLAZIONE DA VEDERE IN FUTURO SE NECESARIO
        //
        $elementi['dati']['NumeroPratica'] = '';

        //
        // Mittente Arrivo OK
        //
        $Anades_rec_mittente = $praLib->GetMittenteProtocollo($dati['Proric_rec'], $dati['PRAM_DB']);
        if (!$Anades_rec_mittente) {
            return false;
        }


        if ($dati['Proric_rec']['PROPAK']) {
            $elementi['dati']['MittDest']['Nome'] = $praLib->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "Referente_EnteTerzo");
            $elementi['dati']['MittDest']['Denominazione'] = $praLib->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "Denom_EnteTerzo");
            $elementi['dati']['MittDest']['Email'] = $praLib->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "Pec_EnteTerzo");
            $elementi['dati']['MittDest']['CF'] = $praLib->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "Fiscale_EnteTerzo");
        } else {
            $elementi['dati']['MittDest']['Nome'] = $Anades_rec_mittente['DESNOME'];
            $elementi['dati']['MittDest']['Cognome'] = $Anades_rec_mittente['DESCOGNOME'];
            $elementi['dati']['MittDest']['Denominazione'] = $Anades_rec_mittente['DESNOM'];
            $elementi['dati']['MittDest']['Indirizzo'] = $Anades_rec_mittente['DESIND'] . " " . $Anades_rec_mittente['DESCIV'];
            $elementi['dati']['MittDest']['CAP'] = $Anades_rec_mittente['DESCAP'];
            $elementi['dati']['MittDest']['Citta'] = $Anades_rec_mittente['DESCIT'];
            $elementi['dati']['MittDest']['Provincia'] = $Anades_rec_mittente['DESPRO'];
            //$mail = $Anades_rec_mittente['DESPEC'] ? $Anades_rec_mittente['DESPEC'] : $Anades_rec_mittente['DESEMA'];
            $elementi['dati']['MittDest']['Email'] = $Anades_rec_mittente['DESEMA'];
            $elementi['dati']['MittDest']['CF'] = $Anades_rec_mittente['DESFIS'];
            $elementi['dati']['MittDest']['Telefono'] = frontOfficeApp::$cmsHost->getAltriDati("TELEFONO");
        }
        //$elementi['dati']['MittDest']['Mezzo'] = "TRAMITE PROCEDURA INFORMATICA";
        //
        // Destinatario Partenza Non usato perchè necessario solo in partenza
        //
        $elementi['dati']['destinatari'] = array();

        //
        // Destinatario Interno per la destinazione interna DA IMPLEMENTARE
        //
        $Ananom_rec = $this->GetDestinatarioInterno($dati);
        $destinatario = $this->setDestinatarioProtocollo($Ananom_rec['NOMRES'], $dati['PRAM_DB']);
        $elementi['destinatari'][0] = $destinatario;

        /*
         * Mi trovo la classificazione del protocollo 
         */
        $classificazione = $this->GetClassificazioneProtocollazione($dati['Proric_rec'], $dati['PRAM_DB']);

        //
        // Mi trovo il codice procedimento se è un'integrazione
        //
        $codiceProc = $dati['Proric_rec']['RICPRO'];
        if ($dati['Proric_rec']['RICRPA']) {
            $proric_rec_padre = $praLib->GetProric($dati['Proric_rec']['RICRPA'], "codice", $dati['PRAM_DB']);
            $Metadati = unserialize($proric_rec_padre['RICMETA']);
            $codiceProc = $proric_rec_padre['RICPRO'];
            $classificazione = $this->GetClassificazioneProtocollazione($proric_rec_padre, $dati['PRAM_DB']);
            if ($Metadati['DatiProtocollazione']['TipoProtocollo']['value'] == $anapar_rec['PARVAL']) {
                $elementi['dati']['numeroProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['proNum']['value'];
                $elementi['dati']['dataProtocolloAntecedente'] = date("Ymd", strtotime($Metadati['DatiProtocollazione']['Data']['value']));
                $elementi['dati']['annoProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['Anno']['value'];
                $elementi['dati']['DocNumberProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
            }
        }

        /*
         * Se sto protocollando un parere, prendo i dati del prot antecedente, dal fascicolo elettronico padre
         */
        if ($dati['Propas_rec']) {
            $Proges_rec = ItaDB::DBSQLSelect($dati['PRAM_DB'], "SELECT * FROM PROGES WHERE GESNUM='" . $dati['Propas_rec']['PRONUM'] . "'", false);
            $Metadati = unserialize($Proges_rec['GESMETA']);
            $elementi['dati']['numeroProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['proNum']['value'];
            $elementi['dati']['dataProtocolloAntecedente'] = date("Ymd", strtotime($Metadati['DatiProtocollazione']['Data']['value']));
            $elementi['dati']['annoProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['Anno']['value'];
            $elementi['dati']['DocNumberProtocolloAntecedente'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
        }

        /*
         * Assegno la classficazione trovata in precedenza
         */
        $elementi['dati']['Classificazione'] = $classificazione;

        //
        // Ufficio in carico portare il codice da pralib back-office
        //
        $UfficioCarico = $this->GetUfficioCaricoProtocollazione($dati['Proric_rec'], $dati['PRAM_DB']);
        $elementi['dati']['InCaricoA'] = $UfficioCarico;

        //
        // Tipo Documento protocollo portare il codice da pralib back-office
        //
        $TipoDocumentoProtocollo = $this->GetTipoDocumentoProtocollazione($dati['Proric_rec'], $dati['PRAM_DB']);
        $elementi['dati']['TipoDocumento'] = $TipoDocumentoProtocollo;

        /*
         * Dati Fascicolazione
         */
        $elementi['dati']['Fascicolazione']['Oggetto'] = $arrayOggettoProt['oggettoFascicolo'];
        $elementi['dati']['Fascicolazione']['Aggiornamento'] = "F";
        return $elementi;
    }

    private function setDestinatarioProtocollo($gesres, $PRAMDB) {
        $praLib = new praLib();
        $ananom_rec = $praLib->GetAnanom($gesres, "codice", $PRAMDB);
        $anamed_rec = $praLib->GetAnamed($ananom_rec['NOMDEP'], 'codice', 'no');
        $destinatario = array();
        if ($anamed_rec) {
            $uffdes_tab = $praLib->GetUffdes($anamed_rec['MEDCOD']);
            foreach ($uffdes_tab as $uffdes_rec) {
                $anauff_rec = $praLib->GetAnauff($uffdes_rec['UFFCOD']);
                if ($anauff_rec['UFFANN'] == 0) {
                    $destinatario['CodiceDestinatario'] = $anamed_rec['MEDCOD'];
                    $destinatario['Denominazione'] = $anamed_rec['MEDNOM'];
                    $destinatario['Indirizzo'] = $anamed_rec['MEDIND'];
                    $destinatario['CAP'] = $anamed_rec['MEDCAP'];
                    $destinatario['Citta'] = $anamed_rec['MEDCIT'];
                    $destinatario['Provincia'] = $anamed_rec['MEDPRO'];
                    $destinatario['Annotazioni'] = $anamed_rec['MEDNOTE'];
                    $destinatario['Ufficio'] = $anauff_rec['UFFCOD'];
                    break;
                }
            }
        }
        return $destinatario;
    }

    function GetDestinatarioInterno($dati) {
        $praLib = new praLib();
        $Ananom_rec = $praLib->GetAnanom($dati['Anapra_rec']['PRARES'], 'codice', $dati['PRAM_DB']);
        return $Ananom_rec;
    }

    function GetClassificazioneProtocollazione($proric_rec, $PRAMDB) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori  PRORIC e non più da ANAPRA
         */
        if ($proric_rec) {
            $praLib = new praLib();
            $Anapra_rec = $praLib->GetAnapra($proric_rec['RICPRO'], "codice", $PRAMDB); //procedimento
            if ($proric_rec['RICATT'] != 0) {   //attività
                $Anaatt_rec = $praLib->GetAnaatt($proric_rec['RICATT'], "codice", $PRAMDB);
            }
            if ($proric_rec['RICSTT'] != 0) { //settore commerciale
                $Anaset_rec = $praLib->GetAnaset($proric_rec['RICSTT'], "codice", $PRAMDB);
            }

            /*
             * Tolta lettura classificazione dall'aggregato perchè nel caso di protocollazione diversa dell'aggregato,
             * prendeva la classificazionedel tipo protocollo dell'aggregato e non dello sportello.
             * Vedi Morrovalle sotto Montiazzurri
             */
//            if ($proric_rec['RICSPA'] != 0) { //sportello aggregato
//                $Anaspa_rec = $praLib->GetAnaspa($proric_rec['RICSPA'], "codice", $PRAMDB);
//            }
            if ($proric_rec['RICTSP'] != 0) { //sportello on line
                $Anatsp_rec = $praLib->GetAnatsp($proric_rec['RICTSP'], "codice", $PRAMDB);
            }
            /*
             * Tolta la parte che andava a prendere Sportello,Settore, Attività da ANAPRA
             */

            if ($Anapra_rec['PRACLA'] != "") { //procedimento amministrativo
                return $Anapra_rec['PRACLA'];
            } else if ($Anaatt_rec['ATTCLA']) { //attività
                return $Anaatt_rec['ATTCLA'];
            } if ($Anaset_rec['SETCLA']) {  //settore
                return $Anaset_rec['SETCLA'];
            }
//            else if ($Anaspa_rec['SPACLA']) { //aggregato
//                return $Anaspa_rec['SPACLA'];
            else if ($Anatsp_rec['TSPCLA']) { //sportello
                return $Anatsp_rec['TSPCLA'];
            }
        }
    }

    function GetUfficioCaricoProtocollazione($proric_rec, $PRAMDB) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori  PRORIC e non più da ANAPRA
         */
        if ($proric_rec) {
            $praLib = new praLib();
            if ($proric_rec['RICSPA'] != 0) { //sportello aggregato
                $Anaspa_rec = $praLib->GetAnaspa($proric_rec['RICSPA'], "codice", $PRAMDB);
                if ($Anaspa_rec['SPAUOP']) {
                    return $Anaspa_rec['SPAUOP'];
                }
            }
            if ($proric_rec['RICTSP'] != 0) { //sportello on line
                $Anatsp_rec = $praLib->GetAnatsp($proric_rec['RICTSP'], "codice", $PRAMDB);
                if ($Anatsp_rec['TSPUOP']) { //sportello
                    return $Anatsp_rec['TSPUOP'];
                }
            }
        }
        return '';
    }

    function GetTipoDocumentoProtocollazione($proric_rec, $PRAMDB) {
        /*
         * Dopo la modifica della classificazione del procedimento interna all'evento,
         * prendiamo i valori  PRORIC e non più da ANAPRA
         */
        if ($proric_rec) {
            $praLib = new praLib();
            if ($proric_rec['RICSPA'] != 0) { //sportello aggregato
                $Anaspa_rec = $praLib->GetAnaspa($proric_rec['RICSPA'], "codice", $PRAMDB);
                if ($Anaspa_rec) {
                    if ($Anaspa_rec['SPATDO']) { //sportello aggregato
                        return $Anaspa_rec['SPATDO'];
                    }
                }
            }
            if ($proric_rec['RICTSP'] != 0) { //sportello on line
                $Anatsp_rec = $praLib->GetAnatsp($proric_rec['RICTSP'], "codice", $PRAMDB);
                if ($Anatsp_rec) {
                    if ($Anatsp_rec['TSPTDO']) { //sportello
                        return $Anatsp_rec['TSPTDO'];
                    }
                }
            }
        }
        return '';
    }

    function GetAllegatiNonProt($arrayDoc, $tipo, $dati) {
        //
        //Ciclo per eliminare dalla protocollazione file maggiori della size nei parametri
        //
        if ($tipo == "Paleo" || $tipo == "Paleo4") {
            $praLib = new praLib();
            $anapar_rec = $praLib->GetAnapar("MAX_FILESIZE_PROTALLEGATO", "parkey", $dati["PRAM_DB"], false);
            if ($anapar_rec['PARVAL'] == "")
                $anapar_rec['PARVAL'] = "0";
            if ($anapar_rec['PARVAL'] != "0") {
                $max_file_size = $anapar_rec['PARVAL'] * 1024 * 1024;
                foreach ($arrayDoc['Allegati'] as $keyAlle => $allegato) {
                    if (filesize($dati['CartellaAllegati'] . "/" . $allegato['Documento']['Docupl']) > $max_file_size) {
                        $strNoProt .= "<div>- Allegato <b>" . $allegato['Documento']['Nome'] . "</b> non protocollato perchè più grande di " . $anapar_rec['PARVAL'] . " MB</div>";
                        unset($arrayDoc['Allegati'][$keyAlle]);
                    }
                }
            }
        }
        return array("arrayDoc" => $arrayDoc, "strNoProt" => $strNoProt);
    }

    function estraiAllegatiWS($TotaleAllegati, $dati, $tipo) {
        $index = 0;
        $passoUploadRapporto = false;
        $praLib = new praLib();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $keyPasso => $ricite_rec) {
            if ($ricite_rec['ITEDRR'] == 1) {
                if ($dati['Navigatore']['Ricite_tab_new'][$keyPasso + 1]['ITEUPL'] == 1 && $dati['Navigatore']['Ricite_tab_new'][$keyPasso + 1]['ITECTP'] === $ricite_rec['ITEKEY']) {
                    $passoUploadRapporto = $dati['Navigatore']['Ricite_tab_new'][$keyPasso + 1];
                    break;
                }
            }
        }

        /*
         * Se non c'è il passo upload del rapporto vedo c'è il passo rapporto e raccolta con pdf
         */
        if (!$passoUploadRapporto) {
            foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
                if ($ricite_rec['ITEDRR'] == 1) {
                    if (($ricite_rec['ITEDAT'] == 1 || $ricite_rec['ITERDM'] == 1) && $ricite_rec['ITEMETA']) {
                        $passoUploadRapporto = $ricite_rec;
                        break;
                    }
                }
            }

            /*
             * Controllo Flag Allegato Principale su RICDOC
             */
            if (!$passoUploadRapporto) {
                foreach ($dati['Ricdoc_tab_tot'] as $ricdoc_rec) {
                    if ($ricdoc_rec['DOCPRI'] == 1) {
                        $passoUploadRapporto = $praLib->GetRicite($ricdoc_rec['ITEKEY'], 'itekey', $dati['PRAM_DB'], false, $dati['Proric_rec']['RICNUM']);
                        break;
                    }
                }
            }
        }

        /*
         * Qui leggo il primo allegato dell'elenco se non è presente il rapporto, per creare un principale
         * Utilizzando la variabile $passoUploadRapporto per simulare il primo allegato principale
         */
        $arrayDoc = array();

        /*
         * Se c'è solo un allegato, è per forza  il principale
         */
        if (count($TotaleAllegati) == 1) {
            $allegato = $TotaleAllegati[0];
            $base64File = base64_encode(file_get_contents($dati['CartellaAllegati'] . "/" . $allegato['DOCUPL']));
            $est1 = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
            $nome1 = pathinfo($allegato['DOCUPL'], PATHINFO_FILENAME);
            $est2 = pathinfo($nome1, PATHINFO_EXTENSION);
            $estensione = $est1;
            if (strtolower($est2) == "pdf" || strtolower($est2) == "jpg" || strtolower($est2) == "doc" || strtolower($est2) == "docx" || strtolower($est2) == "rtf") {
                $estensione = $est2 . "." . $est1;
            }
            $arrayDoc['Principale']['ROWID'] = $allegato['ROWID'];
            if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                $arrayDoc['Principale']['filepath'] = $dati['CartellaAllegati'] . "/" . $allegato['DOCUPL'];
                $arrayDoc['Principale']['nomeFile'] = $allegato['DOCNAME'];
                //$arrayDoc['Principale']['estensione'] = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
                $arrayDoc['Principale']['estensione'] = $estensione;
                //$arrayDoc['Principale']['descrizione'] = 'Documento principale ';
                $arrayDoc['Principale']['descrizione'] = $passoUploadRapporto['ITEDES'];
                $arrayDoc['Principale']['stream'] = $base64File;
                $arrayDoc['Principale']['docupl'] = $allegato['DOCUPL'];
            } else {
                $arrayDoc['Principale']['FilePath'] = $dati['CartellaAllegati'] . "/" . $allegato['DOCUPL'];
                $arrayDoc['Principale']['Nome'] = $allegato['DOCNAME'];
                $arrayDoc['Principale']['Stream'] = $base64File;
                $arrayDoc['Principale']['Descrizione'] = $passoUploadRapporto['ITEDES'];
                $arrayDoc['Principale']['Docupl'] = $allegato['DOCUPL'];
                //$arrayDoc['Principale']['Descrizione'] = 'Documento principale ';
            }
            return $arrayDoc;
        }


        /*
         * Mi creo l'array degli allegati da protocollare
         */
        $principale = false;
        foreach ($TotaleAllegati as $allegato) {
            $praLib = new praLib();
            $pathRichiesta = $praLib->getCartellaAttachmentPratiche($allegato['DOCNUM']);
            $base64File = base64_encode(file_get_contents($pathRichiesta . "/" . $allegato['DOCUPL']));
            $fileSize = filesize($pathRichiesta . "/" . $allegato['DOCUPL']);

            /*
             * Se il contenuto del file è vuoto, (0 byte) lo scarto
             */
            if ($fileSize == 0 && $base64File == "") {
                continue;
            }
            $est1 = pathinfo($allegato['DOCUPL'], PATHINFO_EXTENSION);
            $nome1 = pathinfo($allegato['DOCUPL'], PATHINFO_FILENAME);
            $est2 = pathinfo($nome1, PATHINFO_EXTENSION);
            $estensione = $est1;
            if (strtolower($est2) == "pdf" || strtolower($est2) == "jpg" || strtolower($est2) == "doc" || strtolower($est2) == "docx" || strtolower($est2) == "rtf") {
                $estensione = $est2 . "." . $est1;
            }

            if ($allegato['ITEKEY'] == $passoUploadRapporto['ITEKEY'] && !isset($arrayDoc['Principale'])) {
                $arrayDoc['Principale']['ROWID'] = $allegato['ROWID'];
                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                    $arrayDoc['Principale']['filepath'] = $pathRichiesta . "/" . $allegato['DOCUPL'];
                    $arrayDoc['Principale']['nomeFile'] = $allegato['DOCNAME'];
                    $arrayDoc['Principale']['estensione'] = $estensione;
                    $arrayDoc['Principale']['descrizione'] = $passoUploadRapporto['ITEDES'];
                    $arrayDoc['Principale']['stream'] = $base64File;
                    $arrayDoc['Principale']['docupl'] = $allegato['DOCUPL'];
                } else {
                    $arrayDoc['Principale']['FilePath'] = $pathRichiesta . "/" . $allegato['DOCUPL'];
                    $arrayDoc['Principale']['Nome'] = $allegato['DOCNAME'];
                    $arrayDoc['Principale']['Stream'] = $base64File;
                    $arrayDoc['Principale']['Descrizione'] = $passoUploadRapporto['ITEDES'];
                    $arrayDoc['Principale']['Docupl'] = $allegato['DOCUPL'];
                }
            } else {
                $Ricite_rec = $praLib->GetRicite($allegato['ITEKEY'], "itekey", $dati['PRAM_DB'], false, $allegato['DOCNUM']);
                $arrayDoc['Allegati'][$index]['ROWID'] = $allegato['ROWID'];
                if ($tipo == 'WSPU' || $tipo == 'Iride' || $tipo == 'Jiride') {
                    $arrayDoc['Allegati'][$index]['Documento']['filepath'] = $pathRichiesta . "/" . $allegato['DOCUPL'];
                    $arrayDoc['Allegati'][$index]['nomeFile'] = $allegato['DOCNAME'];
                    $arrayDoc['Allegati'][$index]['estensione'] = $estensione;
                    $arrayDoc['Allegati'][$index]['descrizione'] = $Ricite_rec['ITEDES'] ? $Ricite_rec['ITEDES'] : $allegato['DOCNAME'];
                    $arrayDoc['Allegati'][$index]['stream'] = $base64File;
                    $arrayDoc['Allegati'][$index]['docupl'] = $allegato['DOCUPL'];
                    $index++;
                } else {
                    $arrayDoc['Allegati'][$index]['Documento']['FilePath'] = $pathRichiesta . "/" . $allegato['DOCUPL'];
                    $arrayDoc['Allegati'][$index]['Documento']['Nome'] = $allegato['DOCNAME'];
                    $arrayDoc['Allegati'][$index]['Documento']['Stream'] = $base64File;
                    $arrayDoc['Allegati'][$index]['Descrizione'] = $Ricite_rec['ITEDES'] ? $Ricite_rec['ITEDES'] : $allegato['DOCNAME'];
                    $arrayDoc['Allegati'][$index]['Documento']['Docupl'] = $allegato['DOCUPL'];
                    $index++;
                }
            }
        }
        return $arrayDoc;
    }

    public function checkRichestaDaProtocollare($dati) {
        $anaparPrtRem_rec = $this->praLib->GetAnapar("PROTOCOLLO_REMOTO", "parkey", $this->PRAM_DB, false);

        if ($anaparPrtRem_rec['PARVAL'] != "Si") {
            return false;
        }

        if ($dati['Anaspa_rec']['SPADISABILITAPROT'] != 0) {
            return false;
        }

        return true;
    }

    public function checkProtocollazioneDifferita() {
        $anaparTipoComWsProt_rec = $this->praLib->GetAnapar("TIPO_COMUNICAZIONE_WS", "parkey", $this->PRAM_DB, false);

        if ($anaparTipoComWsProt_rec['PARVAL'] == "Differita") {
            return true;
        }

        return false;
    }

    /**
     * 
     * @return type
     */
    public function protocollaRichiesta($dati) {
        /*
         * Lettura Parametri
         */

        $return = array(
            'RESULT' => self::RESULT_PROTOCOLLA_OK,
            'RICHIESTA' => array(),
            'PROTOCOLLATO' => false,
            'ERRORE' => '',
            'ERRORE_MESSAGGIO' => ''
        );

        $anapar_rec = $this->praLib->GetAnapar("TIPO_PROTOCOLLO", "parkey", $dati["PRAM_DB"], false);
        if ($anapar_rec['PARVAL'] == "Iride") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("IRIDEWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Jiride") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("JIRIDEWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Paleo") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("PALEOWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Paleo4") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("PALEO4WSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Paleo41") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("PALEO4WSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Italsoft") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("ITALSOFTWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Halley") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("HALLEYWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Infor") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("INFORWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Sici") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("SICIWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "HyperSic") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("HYPERSICWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Leonardo") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("LEONARDOWSCONNECTION", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "Kibernetes") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("KIBERNETESWSPROTOCOLLAZIONE", $this->ITAFRONTOFFICE_DB);
        } elseif ($anapar_rec['PARVAL'] == "CiviliaNext") {
            $paramTmp = $this->frontOfficeLib->getEnv_config("CIVILIANEXTWSPROTOCOLLAZIONE", $this->ITAFRONTOFFICE_DB);
        } else {
            $return['RESULT'] = self::RESULT_PROTOCOLLA_ERROR;
            $return['ERRORE'] = "Impossibile protocollare.<br>Selezionare prima il tipo di protocollo remoto la richiesta n. " . $dati['Proric_rec']['RICNUM'];
            return $return;
        }

        $clientParam = $this->praLib->GetNormalArrayParam($paramTmp);

        $tipoAlle = "NA";
        $anapar_rec_protRapp = $this->praLib->GetAnapar("PROTOCOLLA_RAPPORTO", "parkey", $dati["PRAM_DB"], false);
        if ($anapar_rec_protRapp['PARVAL'] == "Si") {
            $tipoAlle = "RC";
        }

        /*
         * Estraggo gli allegati per la protocollazione.
         * Se ci sono, prendo anche gli allegati delle accorpate
         */
        $TotaleAllegati = $this->praLib->GetRicdocFromNavigatore($dati, $this->PRAM_DB, array("TIPOALLEGATI" => $tipoAlle));

        /*
         * Se tipo NA (non accorpati), aggiungo anche gli allegati non accorpati delle richieste accorpate
         */
        if ($tipoAlle == "NA") {
            $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $dati['Proric_rec']['RICNUM']);
            $allegatiAccorpate = array();
            foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                $dati_accorpata = $this->praLibDati->prendiDati($proric_rec_accorpate['RICNUM']);
                $allegatiAccorpata = $this->praLib->GetRicdocFromNavigatore($dati_accorpata, $this->PRAM_DB, array("TIPOALLEGATI" => $tipoAlle));
                $allegatiAccorpate = array_merge($allegatiAccorpate, $allegatiAccorpata);
            }

            $TotaleAllegati = array_merge($TotaleAllegati, $allegatiAccorpate);
        }

        /*
         * Carico i testi parametrici per il corpo delle mail decodificando le variabili dizionario
         */
        $arrayOggettoProt = $this->praLib->arrayOggettoProt($dati, $this->PRAM_DB);
        if (!$arrayOggettoProt) {
            $return['RESULT'] = self::RESULT_PROTOCOLLA_ERROR;
            $return['ERRORE'] = "Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato";
            return $return;
        }

        if (!$this->RichiestaInFaseDiInvio($dati, true)) {
            $return['RESULT'] = self::RESULT_PROTOCOLLA_ERROR;
            $return['ERRORE'] = $this->getErrMessage();
            return $return;
        }

        $ret = $this->praLib->InvioRichiestaProtocollo($dati, $TotaleAllegati, $arrayOggettoProt, "RICHIESTA-PROTOCOLLO", $clientParam);
        $return['RICHIESTA'] = $ret;

        if ($ret['Status'] != "0") {
            $this->RichiestaInFaseDiInvio($dati, false);

            $return['RESULT'] = self::RESULT_PROTOCOLLA_ERROR;
            $return['ERRORE'] = "Errore Protocollazione Remota n. Pratica " . $dati['Proric_rec']['RICNUM'] . "<br>" . $ret['Message'];
            $return['ERRORE_MESSAGGIO'] = 'E\' stato riscontrato un errore in fase di protocollazione.<br>Si prega di riprovare più tardi';
            return $return;
        }

        $return['PROTOCOLLATO'] = true;

        $DocNumber = $ret['RetValue']['DatiProtocollazione']['DocNumber']['value'];
        $NProt = $ret['RetValue']['DatiProtocollazione']['proNum']['value'];
        $Anno = $ret['RetValue']['DatiProtocollazione']['Anno']['value'];

        /*
         * Se protocollato, se attivo e il ws lo permette, creo Fascicolo
         */
        $anaparFascRem_rec = $this->praLib->GetAnapar("FASCICOLAZIONE_REMOTA", "parkey", $this->PRAM_DB, false);
        if ($anaparFascRem_rec['PARVAL'] == "Si") {
            if ($anapar_rec['PARVAL'] == "Jiride") {
                $paramTmpFascicolazione = $this->frontOfficeLib->getEnv_config("JIRIDEWSFASCICOLICONNECTION", $this->ITAFRONTOFFICE_DB);
            } elseif ($anapar_rec['PARVAL'] == "Italsoft") {
                $paramTmpFascicolazione = $this->frontOfficeLib->getEnv_config("ITALSOFTWSCONNECTION", $this->ITAFRONTOFFICE_DB);
            } elseif ($anapar_rec['PARVAL'] == "Iride") {
                $paramTmpFascicolazione = $this->frontOfficeLib->getEnv_config("IRIDEWSFASCICOLICONNECTION", $this->ITAFRONTOFFICE_DB);
            } elseif ($anapar_rec['PARVAL'] == "Sici") {
                $paramTmpFascicolazione = $this->frontOfficeLib->getEnv_config("SICIWSCONNECTION", $this->ITAFRONTOFFICE_DB);
            }
            $clientParamFascicolazione = $this->praLib->GetNormalArrayParam($paramTmpFascicolazione);

            $idFascicolo = null;

            /*
             * Se c'è, prendo il codice fascicolo da ANAPRA
             */
            if ($dati['Anapra_rec']['PRAFASCICOLO']) {
                $idFascicolo = $dati['Anapra_rec']['PRAFASCICOLO'];
            }

            $ret = $this->praLib->InvioRichiestaFascicolazione($dati, $arrayOggettoProt, $clientParamFascicolazione, $clientParam, $DocNumber, $idFascicolo, $NProt, $Anno);
            $return['RICHIESTA'] = $ret;

            if ($ret['Status'] != "0") {
                /*
                 * Invio errore silenzioso a italsoft
                 */

                $return['RESULT'] = self::RESULT_PROTOCOLLA_WARNING;
                $return['ERRORE'] = "Errore Fascicolazione Remota n. Pratica " . $dati['Proric_rec']['RICNUM'] . "<br>" . $ret['Message'];
                return $return;
            }
        }

        return $return;
    }

    private function RichiestaInFaseDiInvio($dati, $blocca) {
        /*
         * Blocco la Richiesta
         */

        if ($blocca == true) {
            if ($dati["Proric_rec"]['RICSTA'] == '99') {
                $dati["Proric_rec"]['RICSTA'] = 'IM';
            } else {
                return true;
            }
        } else if ($blocca == false) {
            $dati["Proric_rec"]['RICSTA'] = '99';
        }

        try {
            ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $dati["Proric_rec"]);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage() . " Errore aggiornamento su PRORIC della pratican. " . $dati["Proric_rec"]['RICNUM']);
            return false;
        }

        return true;
    }

}
