<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    28.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

class proDatiProtocollo {

    public $workDate;
    public $workYear;
    public $utenteWs;
    public $Prouof;
    public $tipoProt;
    public $tipoProtPredisposto;
    public $anapro_rec;
    public $FirmatarioDescod;
    public $FirmatarioDesnom;
    public $FirmatarioUfficio;
    public $Clacod;
    public $Fascod;
    public $Orgcod;
    public $Propre1;
    public $Parpre;
    public $Propre2;
    public $nomeUtente;
    public $Oggetto;
    public $Prodra;
    public $Progra;
    public $Proqta;
    public $Pronur;
    public $Risrde;
    public $Risrda;
    public $proArriAlle;
    public $proArriDest;
    public $proAltriDest;
    public $mittentiAggiuntivi;
    public $proArriUff;
    public $Proric_parm;
    public $fileDaPEC;
    private $message;
    private $title;
    public $indiceRec;

    function __construct() {
        $this->workDate = date('Ymd');
        $this->workYear = date('Y', strtotime($this->workDate));
    }

    function getmessage() {
        return $this->message;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function getTitle() {
        return $this->title;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    public function setStatoProtIncompleto() {
        $this->anapro_rec["PROSTATOPROT"] = proLib::PROSTATO_INCOMPLETO;
    }

    function getIndiceRec() {
        return $this->indiceRec;
    }
    
    function setIndiceRec($indiceRec) {
        $this->indiceRec = $indiceRec;
    }
    
    public function assegnaDatiDaElementi($elementi) {
        $dati = $elementi['dati'];
        $this->tipoProt = $elementi['tipo'];
        $this->tipoProtPredisposto = $elementi['tipoPredisposto'];
        $this->anapro_rec["PRODAR"] = $this->workDate;

        if ($dati['UfficioOperatore']) {
            $this->Prouof = $dati['UfficioOperatore'];
        }

        $this->anapro_rec["PROCODTIPODOC"] = $dati['TipoDocumento'];
        $this->Oggetto = $dati['Oggetto'];
        $this->Propre1 = $dati['NumeroAntecedente'];
        $this->Parpre = $dati['TipoAntecedente'];
        $this->Propre2 = $dati['AnnoAntecedente'];
        $this->anapro_rec["PRONPA"] = $dati['ProtocolloMittente']['Numero'];
        $this->anapro_rec["PRODAS"] = $dati['ProtocolloMittente']['Data'];
        $this->anapro_rec["PRODAA"] = $dati['DataArrivo'];
        $this->anapro_rec["PROEME"] = $dati['ProtocolloEmergenza'];
        if (isset($dati['Prostatoprot'])) {
            $this->anapro_rec["PROSTATOPROT"] = $dati['Prostatoprot'];
        }
        // Id Mail protocollo
        if (isset($elementi['ProIdMail'])) {
            $this->anapro_rec["PROIDMAIL"] = $elementi['ProIdMail'];
        }
        // @TODO PER MANTENERE COMPATIBILITA' CONTROLLARE mittDest?
        if (substr($this->tipoProt, 0, 1) == 'A' || substr($this->tipoProt, 0, 1) == 'C' || $this->tipoProtPredisposto == 'C') {
            if (isset($dati['Mittenti'][0])) {
                $MittDest = $dati['Mittenti'][0];
                unset($dati['Mittenti'][0]);
                if (count($dati['Mittenti'])) {
                    $this->assegnaMittentiAggiuntivi($dati['Mittenti']);
                }
            } else {
                $MittDest = $dati['Mittenti'];
            }
        } else {
            if (isset($dati['Destinatari'][0])) {
                $MittDest = $dati['Destinatari'][0];
                unset($dati['Destinatari'][0]);
                if (count($dati['Destinatari'])) {
                    $this->assegnaAltriDestinatari($dati['Destinatari']);
                }
            } else {
                $MittDest = $dati['Destinatari'];
            }
        }
        $this->anapro_rec["PROCON"] = $MittDest['CodiceMittDest'];
        $this->anapro_rec["PRONOM"] = $MittDest['Denominazione'];
        $this->anapro_rec["PROIND"] = $MittDest['Indirizzo'];
        $this->anapro_rec["PROCAP"] = $MittDest['CAP'];
        $this->anapro_rec["PROCIT"] = $MittDest['Citta'];
        $this->anapro_rec["PROPRO"] = $MittDest['Provincia'];
        $this->anapro_rec["PROMAIL"] = $MittDest['Email'];
        $this->anapro_rec["PROFIS"] = $MittDest['CodiceFiscale'];
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
        $this->anapro_rec["PROCAT"] = $categoria;
        $this->Clacod = $classe;
        $this->Fascod = $sottoclasse;
        $this->Orgcod = $fascicolo;
        $this->anapro_rec["PROTSP"] = $dati['TipoSpedizione'];
        $this->anapro_rec["PRODRA"] = $dati['DataSpedizione'];
        $this->anapro_rec["PRONUR"] = $dati['NumeroRaccomandata'];

        $this->Pronur = $dati['Spedizioni']['NumeroRaccomandata'];
        $this->Progra = $dati['Spedizioni']['Grammi'];
        $this->Proqta = $dati['Spedizioni']['Quantita'];
        $this->Prodra = $dati['Spedizioni']['DataSpedizione'];

        if ($elementi['destinatari']) {
            $retDest = $this->assegnaDestinatari($elementi['destinatari']);
            if ($retDest === false) {
                $this->title = "Attenzione";
                $this->message = "Destinatari non presenti";
                return false;
            }
        }
//        if ($elementi['firmatario']) {
//            $this->assegnaFirmatariProtocollo($elementi['firmatario']);
//        }
        if (isset($elementi['firmatari']['firmatario'])) {
            if ($elementi['firmatari']['firmatario'][0]) {
                $this->assegnaFirmatariProtocollo($elementi['firmatari']['firmatario'][0]);
                unset($elementi['firmatari']['firmatario'][0]);
                if (count($elementi['firmatari']['firmatario'])) {
                    $this->assegnaFirmatariAggiuntivi($elementi['firmatari']['firmatario']);
                }
            } else if ($elementi['firmatario']) {// @TODO PER MANTENERE COMPATIBILITA
                $this->assegnaFirmatariProtocollo($elementi['firmatario']);
            }
        }

//        if ($elementi['uffici']) {
//            $this->assegnaUffici($elementi['uffici']);
//        }

        if ($elementi['rowidMail']) {
            $this->fileDaPEC = array('TYPE' => 'MAILBOX', 'ROWID' => $elementi['rowidMail']);
        }

        if ($elementi['allegati']) {
            $ret = $this->assegnaAllegati($elementi['allegati']);
            if ($ret === false) {
                return false;
            }
        }
        // Allegati Temporanei
        if ($elementi['allegatiPrecaricati']) {
            $ret = $this->assegnaAllegatiTmp($elementi['allegatiPrecaricati']);
            if ($ret === false) {
                return false;
            }
        }
        return true;
    }

    function setUtenteWs($utenteWs) {
        $this->utenteWs = $utenteWs;
    }

    private function assegnaDestinatari($destinatari) {
        $proLib = new proLib();
        foreach ($destinatari as $destinatario) {
            //Controllo se inserire l'ufficio.
            $inserisci = true;
            foreach ($this->proArriUff as $value) {
                if ($destinatario['Ufficio'] == $value['UFFCOD']) {
                    $inserisci = false;
                    break;
                }
            }
            if ($inserisci == true) {
                $this->proArriUff[] = array('UFFCOD' => $destinatario['Ufficio'], 'UFFDES' => '', 'UFFFI1' => 1);
            }
            // Controllo se utente-ufficio è già presente.
            $presente = false;
            foreach ($this->proArriDest as $key => $value) {
                if ($destinatario['CodiceDestinatario'] == $value['DESCOD'] && $destinatario['Ufficio'] == $value['DESCUF']) {
                    $presente = true;
                    break;
                }
            }
            if (!$presente) {
                $salvaDest['ROWID'] = 0;
                $salvaDest['DESPAR'] = $this->tipoProt;
                $salvaDest['DESTIPO'] = "T";
                $salvaDest['DESCOD'] = $destinatario['CodiceDestinatario'];
                $salvaDest['DESNOM'] = $destinatario['Denominazione'];
                $salvaDest['DESIND'] = $destinatario['Indirizzo'];
                $salvaDest['DESCAP'] = $destinatario['CAP'];
                $salvaDest['DESCIT'] = $destinatario['Citta'];
                $salvaDest['DESPRO'] = $destinatario['Provincia'];
                $salvaDest['DESTSP'] = $destinatario['TipoSpedizione'];
                $salvaDest['DESDAT'] = $this->workDate;
                $salvaDest['DESDAA'] = $this->workDate;
                $salvaDest['DESDUF'] = '';
                $salvaDest['DESANN'] = $destinatario['Annotazioni'];
                $salvaDest['DESMAIL'] = $destinatario['Email'];
                $salvaDest['DESCUF'] = $destinatario['Ufficio'];
                if ($destinatario['Gestione']) {
                    $salvaDest['DESGES'] = 1;
                } else {
                    $salvaDest['DESGES'] = 0;
                }

                if ($destinatario['Responsabile']) {
                    $salvaDest['DESRES'] = 1;
                } else {
                    $salvaDest['DESRES'] = 0;
                }
                $this->proArriDest[] = $salvaDest;
            }
        }
    }

    private function assegnaFirmatariProtocollo($firmatario) {
        $this->FirmatarioDescod = $firmatario['CodiceDestinatario'];
        $this->FirmatarioDesnom = $firmatario['Denominazione'];
        $this->FirmatarioUfficio = $firmatario['Ufficio'];
    }

    private function assegnaFirmatariAggiuntivi($Firmatari) {
        foreach ($Firmatari as $idx => $Firmatario) {
            $salvaFrim['ROWID'] = '';
            $salvaFrim['PROPAR'] = $this->tipoProt;
            $salvaFrim['PRODESCOD'] = $Firmatario['CodiceDestinatario'];
            $salvaFrim['PRODESUFF'] = $Firmatario['Ufficio'];
            $this->mittentiAggiuntivi[] = $salvaFrim;
        }
    }

    private function assegnaMittentiAggiuntivi($Mittenti) {
        foreach ($Mittenti as $idx => $Mittente) {
            $salvaMitt['ROWID'] = 0;
            $salvaMitt['PROPAR'] = $this->tipoProt;
            $salvaMitt['PRODESCOD'] = $Mittente['CodiceMittDest']; //
            $salvaMitt['PRONOM'] = $Mittente['Denominazione'];        //
            $salvaMitt['PROIND'] = $Mittente['Indirizzo'];            //
            $salvaMitt['PROCAP'] = $Mittente['CAP'];                  //
            $salvaMitt['PROCIT'] = $Mittente['Citta'];                //
            $salvaMitt['PROPRO'] = $Mittente['Provincia'];            //
            $salvaMitt['PROMAIL'] = $Mittente['Email'];               //
            $salvaMitt['PRODESUFF'] = $Mittente['Ufficio'];   
            $salvaMitt['PROFIS'] = $Mittente['CodiceFiscale'];   
            $this->mittentiAggiuntivi[] = $salvaMitt;
        }
    }

    private function assegnaAltriDestinatari($destinatari) {
        foreach ($destinatari as $destinatario) {
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESTIPO'] = "D";
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
            $salvaDest['DESMAIL'] = $destinatario['Email'];
            $salvaDest['DESCUF'] = '';
            $salvaDest['DESGES'] = $destinatario['Gestione'];
            $salvaDest['DESRES'] = $destinatario['Responsabile'];
            $salvaDest['DESTSP'] = $destinatario['TipoSpedizione'];
            $salvaDest['DESFIS'] = $destinatario['CodiceFiscale'];
            $this->proAltriDest[] = $salvaDest;
        }
    }

    private function assegnaUffici($uffici) {
        $proLib = new proLib();
        foreach ($uffici as $ufficio) {
            $anauff_rec = $proLib->GetAnauff($ufficio['CodiceUfficio']);
            $this->proArriUff[] = array('UFFCOD' => $anauff_rec['UFFCOD'], 'UFFDES' => $anauff_rec['UFFDES'], 'UFFFI1' => 1);
            //if (substr($this->tipoProt, 0, 1) != 'P' && $ufficio['Scarica'] === true) {
            if ($ufficio['Scarica'] === true) {
                $anaent_rec = $proLib->GetAnaent('25');
                if ($anaent_rec['ENTDE1'] != '1') {
                    $this->confermaScarico($anauff_rec['UFFCOD']);
                }
            }
        }
    }

    private function assegnaAllegati($allegati) {
//
// Path Temporenea du upload
//
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $this->title = "Gestione Acquisizioni";
                $this->message = "Creazione ambiente di lavoro temporaneo fallita";
                return false;
            }
        }

//
// Documento Principale
//
        if ($allegati['Principale']['Stream']) {
            $origFile = $allegati['Principale']['Nome'];
            $randName = md5(rand() * time()) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            if (!@file_put_contents($destFile, base64_decode($allegati['Principale']['Stream']))) {
                $this->title = "Upload File Principale:";
                $this->message = "Errore in salvataggio del file!";
                return false;
            } else {
                $allegato = array(
                    'ROWID' => 0,
                    'FILEPATH' => $destFile,
                    'FILENAME' => $randName,
                    'FILEINFO' => $allegati['Principale']['Descrizione'],
                    'DOCNAME' => $allegati['Principale']['Nome'],
                    'DOCTIPO' => '');
            }
            if (isset($allegati['Principale']['AllaFirma'])) {
                $allegato['METTIALLAFIRMA'] = $allegati['Principale']['AllaFirma'];
            }
            if (isset($allegati['Principale']['DocIDMail'])) {
                $allegato['DOCIDMAIL'] = $allegati['Principale']['DocIDMail'];
            }
            $this->proArriAlle[] = $allegato;
        }
//
// Documenti allegati
//
        foreach ($allegati['Allegati'] as $elemento) {
            if ($elemento['Documento']['Stream']) {
                $proLib = new proLib();
                $allegato = array();
                $origFile = $elemento['Documento']['Nome'];


                $randName = md5(rand() * time()) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
                /*
                 * Se l'estensione è p7m, mi calcolo tutte le eventuali sottoestensioni(DA TESTARE)
                 */
//                if (strtolower(pathinfo($origFile, PATHINFO_EXTENSION)) == "p7m") {
//                    $Est_baseFile = $proLib->GetBaseExtP7MFile($origFile);
//                    // Mi trovo e accodo tutte le estensioni p7m
//                    $Est_tmp = $proLib->GetExtP7MFile($origFile);
//                    $posPrimoPunto = strpos($Est_tmp, ".");
//                    $delEst = substr($Est_tmp, 0, $posPrimoPunto + 1);
//                    $p7mExt = str_replace($delEst, "", $Est_tmp);
//                    //Creo l'estensione finale del file
//                    $ext = $Est_baseFile . "." . $p7mExt;
//                    $randName = md5(rand() * time()) . ".$ext";
//                }

                $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                if (!@file_put_contents($destFile, base64_decode($elemento['Documento']['Stream']))) {
                    $this->title = "Upload File Allegati:";
                    $this->message = "Errore in salvataggio del file!";
                    return false;
                } else {
                    $allegato = array(
                        'ROWID' => 0,
                        'FILEPATH' => $destFile,
                        'FILENAME' => $randName,
                        'FILEINFO' => $elemento['Documento']['Descrizione'],
                        'DOCNAME' => $elemento['Documento']['Nome'],
                        'DOCTIPO' => 'ALLEGATO');
                }
                if (isset($elemento['Documento']['AllaFirma'])) {
                    $allegato['METTIALLAFIRMA'] = $elemento['Documento']['AllaFirma'];
                }
                if (isset($elemento['Documento']['DocServizio'])) {
                    $allegato['DOCSERVIZIO'] = $elemento['Documento']['DocServizio'];
                }
                if (isset($elemento['Documento']['DocIDMail'])) {
                    $allegato['DOCIDMAIL'] = $elemento['Documento']['DocIDMail'];
                }
                //
                if (isset($elemento['Documento']['ForzaTipoDoc'])) {
                    $allegato['DOCTIPO'] = $elemento['Documento']['ForzaTipoDoc'];
                }
                $this->proArriAlle[] = $allegato;
            } else {
                $this->title = "Upload File Allegati:";
                $this->message = "Errore in salvataggio del file!";
                return false;
            }
        }

        return true;
    }

    private function assegnaAllegatiTmp($allegatiPrecaricati) {
        // Path temporanea.
        
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $this->title = "Gestione Acquisizioni";
                $this->message = "Creazione ambiente di lavoro temporaneo fallita";
                return false;
            }
        }
        $proLibAllegati = new proLibAllegati();
        $principale = false;

        foreach ($allegatiPrecaricati as $allegatoPrecaricato) {
            if ($allegatoPrecaricato['tipoFile'] == '' && $principale == false) {
                $principale = true;
                $DocTipo = '';
            } else {
                $DocTipo = 'ALLEGATO';
            }
            // Lettura del file.
            $filePath = $proLibAllegati->getAllegatoTmp($allegatoPrecaricato['idunivoco'], $allegatoPrecaricato['hashfile']);

            if (!$filePath) {
                $this->title = "Caricamento File:";
                $this->message = "Errore in salvataggio del file! " . $proLibAllegati->getErrMessage();
                return false;
            }
            $origFile = $allegatoPrecaricato['nomeFile'];
            $randName = md5(rand() * time()) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            // Copio il file su cartella temporanea.
            if (!@copy($filePath, $destFile)) {
                $this->title = "Caricamento File:";
                $this->message = "Errore in salvataggio copia del file! ";
                return false;
            }

            $allegato = array(
                'ROWID' => 0,
                'FILEPATH' => $destFile,
                'FILENAME' => $randName,
                'FILEINFO' => $allegatoPrecaricato['note'],
                'DOCNAME' => $allegatoPrecaricato['nomeFile'],
                'DOCTIPO' => $DocTipo);

            if (isset($allegatoPrecaricato['AllaFirma'])) {
                $allegato['METTIALLAFIRMA'] = $allegatoPrecaricato['AllaFirma'];
            }
            if (isset($allegatoPrecaricato['DocIDMail'])) {
                $allegato['DOCIDMAIL'] = $allegatoPrecaricato['DocIDMail'];
            }
            if (isset($allegatoPrecaricato['DocServizio'])) {
                $allegato['DOCSERVIZIO'] = $allegatoPrecaricato['DocServizio'];
            }
            if (isset($allegatoPrecaricato['ForzaTipoDoc'])) {
                $allegato['DOCTIPO'] = $allegatoPrecaricato['ForzaTipoDoc'];
            }
            $this->proArriAlle[] = $allegato;
        }
        // Setto un principale.
        if (!$principale) {
            $this->proArriAlle[0]['DOCTIPO'] = '';
        }

        return true;
    }

    function confermaScarico($codice) {
        $proLib = new proLib();
        $uffdes_tab = $proLib->GetUffdes($codice, 'uffcod');
        foreach ($uffdes_tab as $uffdes_rec) {
            $this->caricaDestinatarioInterno($uffdes_rec['UFFKEY'], 'codice', $uffdes_rec['UFFCOD'], '', $uffdes_rec['UFFFI1__1']);
        }
    }

    function caricaDestinatarioInterno($codice, $tipo = 'codice', $uffcod = '', $responsabile = '', $gestisci = '') {
        $proLib = new proLib();
        $anamed_rec = $proLib->GetAnamed($codice, $tipo, 'no');
        if (!$anamed_rec) {
            return false;
        }
        $inserisci = true;
        $selResponsabile = null;
        foreach ($this->proArriDest as $key => $value) {
            if ($anamed_rec['MEDCOD'] == $value['DESCOD']) {
                $inserisci = false;
                if ($responsabile) {
                    $selResponsabile = $key;
                }
                break;
            }
        }
        if ($inserisci == true) {
            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESTIPO'] = "T";
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
            $salvaDest['DESCUF'] = $uffcod;
            $salvaDest['DESGES'] = $gestisci;
            $salvaDest['DESRES'] = $responsabile;
            $salvaDest['DESORIGINALE'] = '';
            $this->proArriDest[] = $salvaDest;
        } else if ($selResponsabile != '' || $selResponsabile == '0') {
            $this->proArriDest[$selResponsabile]['DESRES'] = $responsabile;
        }
        return $anamed_rec;
    }

    /**
     * @return boolean
     */
    public function controllaDati() {
        /**
         * Controllo Protocollo Collegato caricato in modo completo o difforme
         */
        if ($this->Propre1 != '' || $this->Propre2 != '' || $this->Parpre != '') {
            if ($this->Propre1 == '' || $this->Propre2 == '' || $this->Parpre == '') {
                $this->setTitle("Errore");
                $this->setMessage("Campi del protocollo collegato incompleti.");
                return false;
            }
        }
        if ($this->Parpre && $this->Parpre != 'C' && $this->Parpre != 'P' && $this->Parpre != 'A') {
            $this->setTitle("Errore");
            $this->setMessage("Tipo del protocollo collegato non contemplato.");
            return false;
        }
        // Controllo del titolario: obbligatorio.
        $retCtrTit = $this->CtrTitolario();
        if (!$retCtrTit) {
            return false;
        }

        $this->setMessage('');
        return true;
    }

    public function CtrConformitaDatiWs() {
        /*
         *  Carico Librerie
         */
        $proLib = new proLib();

        // Controllo operatore
        $codiceOperatore = proSoggetto::getCodiceSoggettoFromIdUtente();
        if (!$codiceOperatore) {
            $this->setTitle("Errore");
            $this->setMessage("Utente collegato senza profilo protocollazione.");
            return false;
        }

        $retCtrSogg = $this->CtrSoggetto($codiceOperatore, $this->Prouof);
        if (!$retCtrSogg) {
            $this->setMessage("Uffico {$this->Prouof} per l'operatore non è valido: " . $this->getmessage());
            return false;
        }

        // 1. Funzione Controllo Titolario
        $retCtrTit = $this->CtrTitolario();
        if (!$retCtrTit) {
            return false;
        }

        //2. Mittente obbligatorio in Arrivo(Almeno Denominazione)
        //   se inserito il codice, (dato che viene decodificato) non basta.
        if (substr($this->tipoProt, 0, 1) == 'A') {
            if (!trim($this->anapro_rec["PRONOM"])) {
                $this->setTitle("Errore");
                $this->setMessage("Descrizione Mittente obbligatorio per un protocollo in Arrivo.");
                return false;
            }
        }

        //3. Destinatario obbligatorio in Partenza(Almeno Denominazione)
        //   se inserito il codice, (dato che viene decodificato) non basta.
        if (substr($this->tipoProt, 0, 1) == 'P' || $this->tipoProtPredisposto == 'P') {
            if (!trim($this->anapro_rec["PRONOM"])) {
                $this->setTitle("Errore");
                $this->setMessage("Descrizione Destinatario obbligatorio per un protocollo in Partenza.");
                return false;
            }
        }
        // Per le Comunicazioni Formali il Destinatario non serve.
        // 4. Firmatario obbligatorio in Partenza/Doc Formale
        $retCtrFirm = $this->CtrFirmatario();
        if (!$retCtrFirm) {
            return false;
        }

        // 5. Controllo Oggetto: per ora solo obbligatorietà.
        if (!trim($this->Oggetto)) {
            $this->setTitle("Errore");
            $this->setMessage("Oggetto obbligatorio.");
            return false;
        }

        // 6. Protocollo Antecedente:
        if ($this->Propre1 != '' || $this->Propre2 != '' || $this->Parpre != '') {
            if ($this->Propre1 == '' || $this->Propre2 == '' || $this->Parpre == '') {
                $this->setTitle("Errore");
                $this->setMessage("Campi del protocollo collegato incompleti.");
                return false;
            }
            $codiceProt = $this->Propre2 * 1000000 + $this->Propre1;
            $tipoProt = $this->Parpre;
            $AnaproColleg_rec = $proLib->GetAnapro($codiceProt, 'codice', $tipoProt);
            if (!$AnaproColleg_rec) {
                $this->setTitle("Errore");
                $this->setMessage("Protocollo collegato inesistente.");
                return false;
            }
        }
        /* 7. Controllo delle date: Data Mittente,Data Arrivo, Spedizione?
         * 7.1 Data Prot. Mittente, 
         */
        if ($this->anapro_rec["PRODAS"]) {
            $retCtrDatMitt = $this->CtrData($this->anapro_rec["PRODAS"]);
            if (!$retCtrDatMitt) {
                $this->setMessage("Data Mitttente: " . $this->getmessage());
                return false;
            }
        }
        /* 7.2 Data Arrivo */
        if ($this->anapro_rec["PRODAA"]) {
            $retCtrDatArr = $this->CtrData($this->anapro_rec["PRODAA"]);
            if (!$retCtrDatArr) {
                $this->setMessage("Data Arrivo: " . $this->getmessage());
                return false;
            }
        }

        /* 8. Controllo Trasmissioni Interne  */
        foreach ($this->proArriDest as $Destinatario) {
            if ($Destinatario['DESCOD']) {
                $retCtrSogg = $this->CtrSoggetto($Destinatario['DESCOD'], $Destinatario['DESCUF']);
                if (!$retCtrSogg) {
                    $this->setMessage("Destinatario Trasmissione {$Destinatario['DESCOD']}: " . $this->getmessage());
                    return false;
                }
            }
        }

        /* 9 Controllo Firmatari */
//        $this->CtrAltriFirmatari();

        $this->setMessage('');
        return true;
    }

    public function CtrTitolario() {
        // Carico Librerie
        $proLib = new proLib();

        // Titolario Obbligatorio, quindi categoria deve essere presente.
        if (!$this->anapro_rec["PROCAT"]) {
            // Controllo se protocollo incompleto, può non avere un titolario valorizzato.
            if ($this->anapro_rec["PROSTATOPROT"] && $this->anapro_rec["PROSTATOPROT"] == prolib::PROSTATO_INCOMPLETO) {
                return true;
            }
            /*
             * Indice Non predisposto per Protocollo
             */
            if (!$this->tipoProtPredisposto && $this->tipoProt == 'I') {
                return true;
            }


            $this->setTitle("Errore");
            $this->setMessage("Titolario obbligatorio.");
            return false;
        }
        //1.1 Controllo Categoria valida.
        //   La lunghezza viene portata a 4 quando viene assegnata.
        $anacat_rec = $proLib->GetAnacat('', $this->anapro_rec["PROCAT"], 'codice');
        if (!$anacat_rec) {
            $this->setTitle("Errore");
            $this->setMessage("Categoria del Titolario inesistente. Controllare il titolario inserito.");
            return false;
        }
        //1.2 Controllo Classe valida.
        //   La lunghezza viene portata a 4 quando viene assegnata.
        if ($this->Clacod) {
            $codice = $this->anapro_rec["PROCAT"] . $this->Clacod;
            $anacla_rec = $proLib->GetAnacla('', $codice, 'codice');
            if (!$anacla_rec) {
                $this->setTitle("Errore");
                $this->setMessage("Classe del Titolario inesistente. Controllare il titolario inserito.");
                return false;
            }
        }
        //1.3 Controllo SottoClasse valida.
        if ($this->Fascod) {
            $codice = $this->anapro_rec["PROCAT"] . $this->Clacod . $this->Fascod;
            $anafas_rec = $proLib->GetAnafas('', $codice, 'fasccf');
            if (!$anafas_rec) {
                $this->setTitle("Errore");
                $this->setMessage("Sottoclasse del Titolario inesistente. Controllare il titolario inserito.");
                return false;
            }
        }
        return true;
    }

    public function CtrFirmatario() {

        // 4. Firmatario obbligatorio in Partenza/Doc Formale
        if (substr($this->tipoProt, 0, 1) == 'P' || substr($this->tipoProt, 0, 1) == 'C' || $this->tipoProtPredisposto == 'P' || $this->tipoProtPredisposto == 'C') {
            if (!$this->FirmatarioDescod || !$this->FirmatarioUfficio || !$this->FirmatarioDesnom) {
                $this->setTitle("Errore");
                $this->setMessage("Firmatario obbligatorio in un protocollo in Partenza o Comunicazione Formale.");
                return false;
            }
            $CtrSogg = $this->CtrSoggetto($this->FirmatarioDescod, $this->FirmatarioUfficio);
            if (!$CtrSogg) {
                $this->setMessage("Firmatario: " . $this->getmessage());
                return false;
            }
        }
        return true;
    }

    public function CtrSoggetto($CodiceSogg, $CodiceUff) {
        $proLib = new proLib();
        // Controllo esistenza anamed
        $anamed_rec = $proLib->GetAnamed($CodiceSogg);
        if (!$anamed_rec) {
            $this->setTitle("Errore");
            $this->setMessage("Codice " . $CodiceSogg . " non trovato nell'anagrafica dei mittenti/destinatari.");
            return false;
        }
        // Controllo lunghezza codice ufficio
        if (strlen($CodiceUff) != 4) {
            $this->setTitle("Errore");
            $this->setMessage("Il codice ufficio deve essere lungo 4 caratteri.");
            return false;
        }
        // Anauff 
        $Anauff_rec = $proLib->GetAnauff($CodiceUff);
        if (!$Anauff_rec) {
            $this->setTitle("Errore");
            $this->setMessage("Ufficio " . $CodiceUff . " inesistente.");
            return false;
        }

        /**
         * Controlli profilo
         */
        $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($CodiceSogg);
        if (!$ruoli) {
            $this->setTitle("Errore");
            $this->setMessage('Nessun ufficio trovato per il soggetto ' . $CodiceSogg);
            return false;
        }
        $trovato = false;
        foreach ($ruoli as $ruolo) {
            if ($CodiceUff == $ruolo['CODICEUFFICIO']) {
                $trovato = true;
            }
        }
        if (!$trovato) {
            $this->setTitle("Errore");
            $this->setMessage('Nessun ufficio corrispondente per il soggetto ' . $CodiceSogg);
            return false;
        }

        return true;
    }

    public function CtrData($Data) {
        if (strlen($Data) != 8) {
            $this->setTitle("Errore");
            $this->setMessage("La data deve essere lunga 8 caratteri nel formato AAAAMMGG.");
            return false;
        }
        if (!is_numeric($Data)) {
            $this->setTitle("Errore");
            $this->setMessage("La data deve essere numerica nel formato AAAAMMGG.");
            return false;
        }
        // Validata mese/anno/giorno?
        $month = substr($Data, 4, 2);
        $day = substr($Data, 6, 2);
        $year = substr($Data, 0, 4);
        $retCheck = checkdate($month, $day, $year);
        if (!$retCheck) {
            $this->setTitle("Errore");
            $this->setMessage("La data deve essere nel formato AAAAMMGG e deve essere una data valida.");
            return false;
        }
        return true;
    }

    public function CtrAltriFirmatari() {
        if (substr($this->tipoProt, 0, 1) == 'P' || substr($this->tipoProt, 0, 1) == 'C' || substr($this->tipoProt, 0, 1) == 'I') {
            foreach ($this->mittentiAggiuntivi as $firmatarioAggiuntivo) {
                if (!$firmatarioAggiuntivo['PRODESCOD']) {
                    $this->setTitle("Errore");
                    $this->setMessage("Codice del firmatario aggiuntivo obbligatorio.");
                    return false;
                }
                if (!$firmatarioAggiuntivo['PRODESUFF']) {
                    $this->setTitle("Errore");
                    $this->setMessage("Codice Ufficio del firmatario aggiuntivo obbligatorio.");
                    return false;
                }
                $CtrSogg = $this->CtrSoggetto($firmatarioAggiuntivo['PRODESCOD'], $firmatarioAggiuntivo['PRODESUFF']);
                if (!$CtrSogg) {
                    $this->setMessage("Firmatario Aggiuntivo: " . $this->getmessage());
                    return false;
                }
            }
        }

        return true;
    }

}

?>