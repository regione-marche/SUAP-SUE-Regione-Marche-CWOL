<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author   Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2016 Italsoft snc
 * @license
 * @version    18.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php';

class proLibDocumentale {

    public $proLib;
    public $proLibAllegati;
    public $segLib;
    public $PROT_DB;
    private $errCode;
    private $errMessage;
    private $infoMessage;
    private $lastAnapro_rec;

    function __construct() {
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->segLib = new segLib();
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

    public function getInfoMessage() {
        return $this->infoMessage;
    }

    public function setInfoMessage($infoMessage) {
        $this->infoMessage = $infoMessage;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getLastAnapro_rec() {
        return $this->lastAnapro_rec;
    }

    public function setLastAnapro_rec($lastAnapro_rec) {
        $this->lastAnapro_rec = $lastAnapro_rec;
    }

    public function ProtocollaDocumentale($Idelib, $marcaturaAllegati = true) {
        $Indice_rec = $this->segLib->getIndice($Idelib, 'codice');
        if (!$Indice_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Indice non trovato.");
            return false;
        }
        if (!$Indice_rec['INDPREPAR']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Tipologia di protocollo predisposto non definito.");
            return false;
        }
        /*
         * Lettura Anapro:
         */
        $Anapro_rec = $this->proLib->GetAnapro($Indice_rec['INDPRO'], 'codice', $Indice_rec['INDPAR']);
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo indice non trovato.");
            return false;
        }

        /*
         * Predispongo i Dati Di Protocollazione:
         */
        $elementi = array();
        $elementi['tipo'] = $Indice_rec['INDPREPAR'];
        $elementi['dati'] = array();
        $elementi['dati']['TipoDocumento'] = '';
        $elementi['dati']['Oggetto'] = $Indice_rec['IOGGETTO'];
        $elementi['dati']['NumeroAntecedente'] = '';
        $elementi['dati']['AnnoAntecedente'] = '';
        $elementi['dati']['TipoAntecedente'] = '';
        if ($Anapro_rec['PROPARPRE']) {
            $elementi['dati']['NumeroAntecedente'] = substr($Anapro_rec['PROPRE'], 4);
            $elementi['dati']['AnnoAntecedente'] = substr($Anapro_rec['PROPRE'], 0, 4);
            $elementi['dati']['TipoAntecedente'] = $Anapro_rec['PROPARPRE'];
        }
        $elementi['dati']['ProtocolloMittente']['Numero'] = '';
        $elementi['dati']['ProtocolloMittente']['Data'] = '';
        $elementi['dati']['DataArrivo'] = date('Ymd');
        $elementi['dati']['ProtocolloEmergenza'] = '';
        $elementi['dati']['TipoSpedizione'] = $Anapro_rec['PROTSP'];
        /*
         *  Firmatario:
         */
        $AnadesFir_rec = $this->proLib->GetAnades($Indice_rec['INDPRO'], 'codice', false, $Indice_rec['INDPAR'], 'M');
        $anamed_rec = $this->proLib->GetAnamed($AnadesFir_rec['DESCOD'], 'codice');
        $elementi['firmatari']['firmatario'][0]['CodiceDestinatario'] = $AnadesFir_rec['DESCOD'];
        $elementi['firmatari']['firmatario'][0]['Denominazione'] = $anamed_rec['MEDNOM'];
        $elementi['firmatari']['firmatario'][0]['Ufficio'] = $AnadesFir_rec['DESCUF'];
        /*
         *  Ufficio firmatario come default: 
         */

        $elementi['dati']['UfficioOperatore'] = $AnadesFir_rec['DESCUF'];

        /*
         * Titolario:
         */
        $Titolario = '';
        if ($Anapro_rec['PROCCF']) {
            $Titolario = substr($Anapro_rec['PROCCF'], 0, 4);
            if (substr($Anapro_rec['PROCCF'], 4, 4)) {
                $Titolario.='.' . substr($Anapro_rec['PROCCF'], 4, 4);
                if (substr($Anapro_rec['PROCCF'], 8, 4)) {
                    $Titolario.='.' . substr($Anapro_rec['PROCCF'], 8, 4);
                }
            }
        }
        $elementi['dati']['Classificazione'] = $Titolario;
        /*
         * Destinatari/Firmatari Aggiuntivi 
         */
        $AltriDestinatari = array();
        $MittentiAggiuntivi = array();
        $DestinatarioPrincipale = array();
        $AnadesAltri_tab = $this->proLib->caricaAltriDestinatari($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], false);
        if ($Indice_rec['INDPREPAR'] == 'P') {
            $DestinatarioPrincipale['CodiceMittDest'] = $Anapro_rec['PROCON'];
            $DestinatarioPrincipale['Denominazione'] = $Anapro_rec['PRONOM'];
            $DestinatarioPrincipale['Indirizzo'] = $Anapro_rec['PROIND'];
            $DestinatarioPrincipale['CAP'] = $Anapro_rec['PROCAP'];
            $DestinatarioPrincipale['Citta'] = $Anapro_rec['PROCIT'];
            $DestinatarioPrincipale['Provincia'] = $Anapro_rec['PROPRO'];
            $DestinatarioPrincipale['Email'] = $Anapro_rec['PROMAIL'];
            $AltriDestinatari[0] = $DestinatarioPrincipale;
            foreach ($AnadesAltri_tab as $AnadesAltri_rec) {
                $Destinatario = array();
                $Destinatario['CodiceMittDest'] = $AnadesAltri_rec['DESCOD'];
                $Destinatario['Denominazione'] = $AnadesAltri_rec['DESNOM'];
                $Destinatario['Indirizzo'] = $AnadesAltri_rec['DESIND'];
                $Destinatario['CAP'] = $AnadesAltri_rec['DESCAP'];
                $Destinatario['Citta'] = $AnadesAltri_rec['DESCIT'];
                $Destinatario['Provincia'] = $AnadesAltri_rec['DESPRO'];
                $Destinatario['Email'] = $AnadesAltri_rec['DESMAIL'];
                $Destinatario['TipoSpedizione'] = $AnadesAltri_rec['DESTSP'];
                $AltriDestinatari[] = $Destinatario;
            }
        } else {
            // Firmatari Aggiuntivi...
        }
        $elementi['dati']['Mittenti'] = $MittentiAggiuntivi;
        $elementi['dati']['Destinatari'] = $AltriDestinatari;

        /*
         * Trasmissioni
         */
        
        $Anades_tab = $this->proLib->caricaDestinatari($Indice_rec['INDPRO'], $Indice_rec['INDPAR']);
        foreach ($Anades_tab as $key => $Anades_rec) {

            if ($Anades_rec['DESCOD']) {
                $anamed_rec = $this->proLib->GetAnamed($Anades_rec['DESCOD']);
                if (!$anamed_rec) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Codice " . $Anades_rec['DESCOD'] . " non trovato nell'anagrafica dei mittenti.");
                    return false;
                }
            } else if ($Anades_rec['DESCUF']) {
                // Trasmissione a Ufficio
                $anamed_rec = array();
                $anamed_rec['MEDNOM'] = 'TRASMISSIONE A INTERO UFFICIO';
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage("Trasmissione incompleta, codice utente e ufficio mancanti.");
                return false;
            }
            $elementi['destinatari'][$key]['CodiceDestinatario'] = $Anades_rec['DESCOD'];
            $elementi['destinatari'][$key]['Denominazione'] = $anamed_rec['MEDNOM'];
            $elementi['destinatari'][$key]['Indirizzo'] = $anamed_rec['MEDIND'];
            $elementi['destinatari'][$key]['CAP'] = $anamed_rec['MEDCAP'];
            $elementi['destinatari'][$key]['Citta'] = $anamed_rec['MEDCIT'];
            $elementi['destinatari'][$key]['Provincia'] = $anamed_rec['MEDPRO'];
            $elementi['destinatari'][$key]['Annotazioni'] = $Anades_rec['DESANN'];
            $elementi['destinatari'][$key]['Email'] = $anamed_rec['MEDEMA'];
            $elementi['destinatari'][$key]['Ufficio'] = $Anades_rec['DESCUF'];
            $elementi['destinatari'][$key]['Responsabile'] = $Anades_rec['DESRES'];
            $elementi['destinatari'][$key]['Gestione'] = $Anades_rec['DESGES'];
        }

        /*
         * Allegato principale e Altri allegati.
         */
        // $Allegati_Tab = $this->proLib->caricaAllegatiProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $Allegati_Tab = $this->segLib->caricaAllegatiOrdinanze($Indice_rec);
        foreach ($Allegati_Tab as $key => $Allegato) {
            $docfirma_rec = $this->proLibAllegati->GetDocfirma($Allegato['ROWID'], 'rowidanadoc', false);
            if ($docfirma_rec) {//&& $docfirma_rec['FIRDATA'] == ''
                $firmaDoc = array();
                $firmaDoc['DESCOD'] = $AnadesFir_rec['DESCOD'];
                $firmaDoc['DESCUF'] = $AnadesFir_rec['DESCUF'];
                $Allegati_Tab[$key]['DOCFIRMA'] = $firmaDoc;
            }
        }

        $AllegatoPrinc = $Allegati_Tab[0];
        unset($Allegati_Tab[0]);
        $FileAllegato = $AllegatoPrinc['FILEPATH'];

        $fh = fopen($FileAllegato, 'rb');
        if (!$fh) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
            return false;
        }
        $binary = fread($fh, filesize($FileAllegato));
        fclose($fh);
        $binary = base64_encode($binary);

        $elementi['allegati']['Principale'] = array();
        $elementi['allegati']['Principale']['Nome'] = $AllegatoPrinc['DOCNAME'];
        $elementi['allegati']['Principale']['Stream'] = $binary;
        $elementi['allegati']['Principale']['Descrizione'] = $AllegatoPrinc['DOCNOT'];
        $elementi['allegati']['Principale']['marcaDocumento'] = 1;
        if ($AllegatoPrinc['DOCFIRMA']) {
            $elementi['allegati']['Principale']['AllaFirma'] = $AllegatoPrinc['DOCFIRMA'];
        }
        /*
         * Controllo se ci sono altri allegati:
         */
        foreach ($Allegati_Tab as $AllegatoExtra) {
            $fh = fopen($AllegatoExtra['FILEPATH'], 'rb');
            if (!$fh) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
                return false;
            }
            $binary = fread($fh, filesize($AllegatoExtra['FILEPATH']));
            fclose($fh);
            $binary = base64_encode($binary);
            $elementoAllegato = array();
            $elementoAllegato['Documento']['Nome'] = $AllegatoExtra['DOCNAME'];
            $elementoAllegato['Documento']['Stream'] = $binary;
            $elementoAllegato['Documento']['Descrizione'] = $AllegatoExtra['DOCNOT'];
            $elementoAllegato['Documento']['marcaDocumento'] = 1;
            if ($AllegatoExtra['DOCFIRMA']) {
                $elementoAllegato['Documento']['AllaFirma'] = $AllegatoExtra['DOCFIRMA'];
            }
            $elementi['allegati']['Allegati'][] = $elementoAllegato;
        }
        /**
         * Istanza Oggetto ProtoDatiProtocollo
         */
        $model = 'proDatiProtocollo.class'; // @TODO cambiata per provare nuove funzioni senza creare problemi al SUAP
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Attiva Controlli su proDatiProtocollo
         */
        if (!$proDatiProtocollo->controllaDati()) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Lancia il protocollatore con i dati impostati
         */
        $model = 'proProtocolla.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proProtocolla = new proProtocolla();
        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        /*
         * Rilettura ANAPRO
         */
        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
        $this->lastAnapro_rec = $Anapro_rec;
        /*
         * Riservatezza:
         */
        $AnaproIndice_rec = $this->proLib->GetAnapro($Indice_rec['INDPRO'], 'codice', $Indice_rec['INDPAR']);
        if ($AnaproIndice_rec) {
            if ($AnaproIndice_rec['PRORISERVA']) {
                try {
                    $Anapro_rec['PRORISERVA'] = $AnaproIndice_rec['PRORISERVA'];
                    ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $Anapro_rec);
                } catch (Exception $e) {
                    $this->setInfoMessage("Aggiornamento protocollo riservato.");
                    // Non deve bloccare.
                }
            }
        }
        $this->lastAnapro_rec = $Anapro_rec;

        /* Elaboro gli allegati e li marco */
        if ($marcaturaAllegati) {
            $retRitorno = $proProtocolla->getRisultatoRitorno();
            foreach ($retRitorno['ROWIDAGGIUNTI'] as $rowdAllegato) {
                if ($rowdAllegato) {
                    $anadoc_rec = $this->proLib->GetAnadoc($rowdAllegato, 'rowid');
                    if (strtolower(pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'pdf') {
                        $retRitornoSegna = $proProtocolla->SegnaAllegato($Anapro_rec, $rowdAllegato);
                        if (!$retRitornoSegna) {
                            $this->setInfoMessage($this->infoMessage . " Attenzione allegato inserito, ma non marcato. " . $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
                        }
                    }
                }
            }
        }
        /*
         * Collego Prot e Documento:
         */
        $ProDocProt_rec = array();
        $ProDocProt_rec['SORGNUM'] = $Indice_rec['INDPRO'];
        $ProDocProt_rec['SORGTIP'] = $Indice_rec['INDPAR'];
        $ProDocProt_rec['DESTNUM'] = $Anapro_rec['PRONUM'];
        $ProDocProt_rec['DESTTIP'] = $Anapro_rec['PROPAR'];
        try {
            ItaDB::DBInsert($this->getPROTDB(), 'PRODOCPROT', 'ROWID', $ProDocProt_rec);
        } catch (Exception $exc) {
            $this->setInfoMessage($this->infoMessage . " - Collegamento Documento a Protocollo fallito.");
        }
        /*
         * Inserimento INS DI VISIBILITA
         */
        if (!$this->InsertVisibilitaDocumentoAllaFirma($AnaproIndice_rec, $Anapro_rec)) {
            $this->setInfoMessage($this->infoMessage . " - Errore in estensione visibilita a Creatore Documento alla Firma.");
        }

        return true;
    }

    public function InsertVisibilitaDocumentoAllaFirma($anaproInd_rec, $anapro_rec) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $codiceSogg = proSoggetto::getCodiceSoggettoFromNomeUtente($anaproInd_rec['PROUTE']);
        // Leggo Anamed
        $anamed_rec = $this->proLib->GetAnamed($codiceSogg, 'codice');

        $proIter = proIter::getInstance($this->proLib, $anapro_rec, '', false);
        if (!$proIter) {
            return false;
        }
        $extraParms = array(
            "UFFICIO" => $anaproInd_rec['PROUOF'],
            "ITEBASE" => 1,
            "NODO" => "INS",
            "GESTIONE" => 1,
            "NOTE" => 'Assegnazione Visibilità a Creatore Documento alla Firma.');
        $retPadre = $proIter->insertIterNodeFromAnamed($anamed_rec, '', $extraParms);
        if (!$retPadre) {
            return false;
        }

        $retPadre = $proIter->chiudiIterNode($retPadre);
        if (!$retPadre) {
            return false;
        }
        return true;
    }

    public function ApriNuovoAttoRiscontro($anapro_rec, $TipoProt = 'P') {
        $modelDaAprire = 'segDocumenti';
        itaLib::openForm($modelDaAprire);
        $modelAtto = itaModel::getInstance($modelDaAprire);
        $modelAtto->setEvent('openform');
        $modelAtto->parseEvent();
        $modelAtto->Nuovo();
        $modelAtto->ApriRiscontroProt($anapro_rec, $TipoProt);
        return true;
    }

    public function ApriNuovoAtto() {
        $modelDaAprire = 'segDocumenti';
        itaLib::openForm($modelDaAprire);
        $modelAtto = itaModel::getInstance($modelDaAprire);
        $modelAtto->setEvent('openform');
        $modelAtto->parseEvent();
        $modelAtto->Nuovo();
        return true;
    }

}