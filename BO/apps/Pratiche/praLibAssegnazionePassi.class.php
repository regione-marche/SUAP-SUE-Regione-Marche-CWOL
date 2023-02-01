<?php

/**
 *
 * LIBRERIA PER ASSEGNAZIONI DEI PASSI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    05.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php');
include_once (ITA_BASE_PATH . '/apps/Protocollo/proProtocolla.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';

class praLibAssegnazionePassi {

    const TYPE_ASSEGNAZIONI = "W";

    public $PRAM_DB;
    public $PROT_DB;
    public $praLib;
    public $proLib;
    private $errMessage;
    private $errCode;

    function __construct() {
        try {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
        $this->praLib = new praLib();
        $this->proLib = new proLib();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function insertAnapro($DatiProtocollo) {
        $protObj = new proProtocolla();

//        $profilo = proSoggetto::getProfileFromIdUtente();
//        if (!$profilo['COD_SOGGETTO']) {
//            $this->errCode = -1;
//            $this->setErrMessage("Profilo Utente non accessibile");
//            return false;
//        }

        $retLock = $this->bloccaProgressivoAnapro();
        if (!$retLock) {
            $this->setErrCode(-1);
            $this->setErrMessage("Accesso esclusivo al progressivo di ANAPRO fallito.");
            return false;
        }

        /*
         * Prenotazione Codice ANAPRO
         */
        $codice = $protObj->PrenotaDocumentoAnapro(date('Y'));

        /*
         * Assegnazione record ANAPRO
         */
        $anapro_new = array();
        $anapro_new['PRONUM'] = $codice;
        $anapro_new['PROPAR'] = self::TYPE_ASSEGNAZIONI;
        $anapro_new['PRODAR'] = date('Ymd');
        $anapro_new['PROCAT'] = '';
        $anapro_new['PROCCA'] = '';
        $anapro_new['PROCCF'] = '';
        $anapro_new['PROARG'] = '';
        $anapro_new['PRORDA'] = date('Ymd');
        $anapro_new['PROROR'] = date('H:i:s');
        $anapro_new['PROORA'] = date('H:i:s');
        $anapro_new['PROUTE'] = App::$utente->getKey('nomeUtente');
        $anapro_new['PROUOF'] = $DatiProtocollo['PROUOF']; // Ufficio creatore
        $anapro_new['PROLOG'] = "999" . substr(App::$utente->getKey('nomeUtente'), 0, 7) . date('d/m/y');
        $anapro_new['PROFASKEY'] = "";
        $anapro_new['PROSECURE'] = 0;
        $anapro_new['PROTSO'] = 0;
        $anapro_new['PRORISERVA'] = 0;
        // Versione Titolario:
        $anapro_new['VERSIONE_T'] = $this->proLib->GetTitolarioCorrente();
        $anapro_new['PROCAT'] = $DatiProtocollo['PROCAT']; //0008
        $anapro_new['PROCCA'] = $DatiProtocollo['PROCCA']; //00080004
        $anapro_new['PROCCF'] = $DatiProtocollo['PROCCF']; //000800040001

        $segnatura = proSegnatura::getStringaSegnatura($anapro_new);
        if (!$segnatura) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella codifica della segnatura dell'indice.");
            $this->sbloccaProgressivoAnapro($retLock);
            return false;
        }
        $anapro_new['PROSEG'] = $segnatura;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANAPRO', 'ROWID', $anapro_new);
            $rowid = $this->proLib->getPROTDB()->getLastId();
            $anaproNew_rec = $this->proLib->GetAnapro($anapro_new['PRONUM'], 'codice', $anapro_new['PROPAR']);
            if (!$anaproNew_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nella creazione instanza documento indice.");
                $this->sbloccaProgressivoAnapro($retLock);
                return false;
            }
            /* @var $protObj proProtocolla */
            $risultato = $protObj->saveOggetto($anaproNew_rec['PRONUM'], $anaproNew_rec['PROPAR'], $DatiProtocollo['OGGETTO']);
            if (!$risultato) {
                $this->errCode = -1;
                $this->setErrMessage("Errore in salvataggio descrizione indice");
                return false;
            }

            /* Salvo Firmatario */
            $anades_rec = array();
            $anades_rec['DESNUM'] = $anapro_new['PRONUM'];
            $anades_rec['DESPAR'] = $anapro_new['PROPAR'];
            $anades_rec['DESTIPO'] = "M";
            $anades_rec['DESCOD'] = $DatiProtocollo['CODICE'];
            $anades_rec['DESCUF'] = $DatiProtocollo['UFFICIO']; //nuovo controllo con codice ufficio differenziato
            $anades_rec['DESNOM'] = $DatiProtocollo['DENOMINAZIONE'];
            $anades_rec['DESCONOSCENZA'] = 0;
            //$insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
            try {
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADES', 'ROWID', $anades_rec);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore nell'inserimento del firmatario " . $exc->getTraceAsString());
                return false;
            }
            $this->sbloccaProgressivoAnapro($retLock);

            $iter = proIter::getInstance($this->proLib, $anaproNew_rec);
            $iter->sincIterProtocollo();

            return $rowid;
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in creazione istanza documento Indice: " . $exc->getTraceAsString());
            return false;
        }
    }

    public function bloccaProgressivoAnapro() {
        $retLock = $this->proLib->lockAnapro("1");
        if (!$retLock) {
            return false;
        }
        return $retLock;
    }

    function sbloccaProgressivoAnapro($retLock) {
        return $this->proLib->unlockAnapro($retLock);
    }

    public function getDatiProtocollo($keyPasso) {
        $propas_rec = $this->praLib->GetPropas($keyPasso);
        $praFascicolo = new praFascicolo(substr($keyPasso, 0, 10));
        $praFascicolo->setChiavePasso($keyPasso);
        
        /*
         * Scelta destinatario metti alla firma in base al parametro
         */
        $responsabile = $propas_rec['PRORPA'];
        $filent_rec = $this->praLib->GetFilent(49);
        if($filent_rec['FILVAL'] == 'GESRES'){
            $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
            $responsabile = $proges_rec['GESRES'];
        }
        
        $destinatario = $praFascicolo->setDestinatarioProtocollo($responsabile);
        $uffici = $praFascicolo->setUfficiProtocollo($destinatario['CodiceDestinatario']);
        $ufficioDefault = $this->proLib->GetUfficioUtentePredef();
        $DatiProtocollo = array();
        $DatiProtocollo['OGGETTO'] = $propas_rec['PRODPA'];
        $DatiProtocollo['PROUOF'] = $ufficioDefault;
        $DatiProtocollo['UFFICIO'] = $uffici[0]['CodiceUfficio'];
        $DatiProtocollo['CODICE'] = $destinatario['CodiceDestinatario'];
        $DatiProtocollo['DENOMINAZIONE'] = $destinatario['Denominazione'];
        $DatiProtocollo['PROCAT'] = "";
        $DatiProtocollo['PROCCA'] = "";
        $DatiProtocollo['PROCCF'] = "";
        return $DatiProtocollo;
    }

    public function AggiornaAllegatoPasso($allegato, $rowidAllegato) {
        $Pasdoc_rec = $this->praLib->GetPasdoc($rowidAllegato, 'ROWID');
        if (!$Pasdoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Documento del passo non trovato.");
            return false;
        }
        $keyPasso = $Pasdoc_rec['PASKEY'];
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false);
        // Copio file sul destinazione passo
        if (!@copy($allegato['FILEPATH'], $pramPath . "/" . $allegato['FILENAME'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia del file " . $allegato['DOCNAME'] . " nella cartella temporanea fallita.");
            return false;
        }
        /*
         * Update di pasdoc_rec.
         */
        $pasfil_originale = $Pasdoc_rec['PASFIL'];
        $Pasdoc_rec['PASFIL'] = $allegato['FILENAME'];
        $Pasdoc_rec['PASNAME'] = $allegato['DOCNAME'];
        $Pasdoc_rec['PASNOT'] = $allegato['DOCNAME'];
        $Pasdoc_rec['PASSHA2'] = hash_file('sha256', $pramPath . "/" . $allegato['FILENAME']);
        try {
            ItaDB::DBUpdate($this->PRAM_DB, 'PASDOC', 'ROWID', $Pasdoc_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento PROCONSER.<br> " . $e->getMessage());
            return false;
        }
        // Unlink se file è variato.
        if ($allegato['FILENAME'] != $pasfil_originale) {
            if (!@unlink($pramPath . "/" . $pasfil_originale)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in cancellazione file presente sul passo.");
                return false;
            }
        }
        return true;
    }

}
