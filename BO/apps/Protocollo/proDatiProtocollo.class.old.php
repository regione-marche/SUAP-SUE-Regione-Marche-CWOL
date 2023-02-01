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

class proDatiProtocollo {

    public $workDate;
    public $workYear;
    public $utenteWs;
    public $Prouof;
    public $tipoProt;
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

    function __construct() {
        $this->workDate = date('Ymd');
        $this->workYear = date('Y', strtotime($this->workDate));
    }

    function getmessage() {
        return $this->message;
    }

    function getTitle() {
        return $this->title;
    }

    function assegnaDatiDaElementi($elementi) {
        $dati = $elementi['dati'];
        $this->tipoProt = $elementi['tipo'];
        $this->anapro_rec["PRODAR"] = $this->workDate;
        $this->anapro_rec["PROCODTIPODOC"] = $dati['TipoDocumento'];
        $this->Oggetto = $dati['Oggetto'];
        $this->Propre1 = $dati['NumeroAntecedente'];
        $this->Parpre = $dati['TipoAntecedente'];
        $this->Propre2 = $dati['AnnoAntecedente'];
        $this->anapro_rec["PRONPA"] = $dati['ProtocolloMittente']['Numero'];
        $this->anapro_rec["PRODAS"] = $dati['ProtocolloMittente']['Data'];
        $this->anapro_rec["PRODAA"] = $dati['DataArrivo'];
        $this->anapro_rec["PROEME"] = $dati['ProtocolloEmergenza'];
        
        if (isset($dati['MittDest'][0])) {
            $MittDest = $dati['MittDest'][0];
            unset($dati['MittDest'][0]);
            if (count($dati['MittDest'])) {
                $this->assegnaAltriDestinatari($dati['MittDest']);
            }
        } else {
            $MittDest = $dati['MittDest'];
        }
        $this->anapro_rec["PROCON"] = $MittDest['CodiceMittDest'];
        $this->anapro_rec["PRONOM"] = $MittDest['Denominazione'];
        $this->anapro_rec["PROIND"] = $MittDest['Indirizzo'];
        $this->anapro_rec["PROCAP"] = $MittDest['CAP'];
        $this->anapro_rec["PROCIT"] = $MittDest['Citta'];
        $this->anapro_rec["PROPRO"] = $MittDest['Provincia'];
        $this->anapro_rec["PROMAIL"] = $MittDest['Email'];

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
        if ($elementi['firmatario']) {
            $this->assegnaFirmatariProtocollo($elementi['firmatario']);
        }

//        if ($elementi['uffici']) {
//            $this->assegnaUffici($elementi['uffici']);
//        }

        if ($elementi['allegati']) {
            $ret = $this->assegnaAllegati($elementi['allegati']);
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
                $salvaDest['DESDAT'] = $this->workDate;
                $salvaDest['DESDAA'] = $this->workDate;
                $salvaDest['DESDUF'] = '';
                $salvaDest['DESANN'] = $destinatario['Annotazioni'];
                $salvaDest['DESMAIL'] = $destinatario['Email'];
                $salvaDest['DESCUF'] = $destinatario['Ufficio'];
                $salvaDest['DESGES'] = 1;
                $this->proArriDest[] = $salvaDest;
            }
        }
    }

    private function assegnaFirmatariProtocollo($firmatario) {
        $this->FirmatarioDescod = $firmatario['CodiceDestinatario'];
        $this->FirmatarioDesnom = $firmatario['Denominazione'];
        $this->FirmatarioUfficio = $firmatario['Ufficio'];
//        foreach ($firmatari as $idx => $firmatario) {
//            if ($idx === 0) {
//                $this->Descod = $firmatari[0]['CodiceDestnatario'];
//                $this->Desnom = $firmatari[0]['Denominazione'];
//                $this->Uffnom = $firmatari[0]['Ufficio'];
//            } else {
//                $salvaFirm['ROWID'] = 0;
//                $salvaFirm['DESPAR'] = $this->tipoProt;
//                $salvaFirm['DESTIPO'] = "M";
//                $salvaFirm['PRODESCOD'] = $firmatario['CodiceDestnatario']; //
//                $salvaFirm['PRONOM'] = $firmatario['Denominazione'];        //
//                $salvaFirm['PROIND'] = $firmatario['Indirizzo'];            // 
//                $salvaFirm['PROCAP'] = $firmatario['CAP'];                  //
//                $salvaFirm['PROCIT'] = $firmatario['Citta'];                //
//                $salvaFirm['PROPRO'] = $firmatario['Provincia'];            //
//                $salvaFirm['PROMAIL'] = $firmatario['Email'];               //
//                $salvaFirm['PRODESFF'] = $firmatario['Ufficio'];            //
//                $this->mittentiAggiuntivi[] = $salvaFirm;
//            }
//        }
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
            $this->proArriAlle[] = $allegato;
        }
//
// Documenti allegati
//

        foreach ($allegati['Allegati'] as $elemento) {
            /*
             * TODO: eliminare la parte ['Documento'], è superflua
             */
            if ($elemento['Documento']['Stream']) {
                $allegato = array();
                $origFile = $elemento['Documento']['Nome'];
                $randName = md5(rand() * time()) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
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
                $this->proArriAlle[] = $allegato;
            } else {
                $this->title = "Upload File Allegati:";
                $this->message = "Errore in salvataggio del file!";
                return false;
            }
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

}

?>