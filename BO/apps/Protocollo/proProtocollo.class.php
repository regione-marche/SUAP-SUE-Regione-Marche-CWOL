<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    19.06.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

class proProtocollo {

    public $PROT_DB;

    /**
     *
     * @var proLib 
     */
    public $proLib;
    private $lastExitCode;
    private $lastMessage;
    private $anapro_rec;
    private $anaogg_rec;
    private $mittenti_tab;
    private $destinatari_tab;
    private $trasmissioni_tab;
    private $datiConservazione;
    private $allegati_tab;
    private $datiAggiuntivi;
    private $dizionarioDocumentale;

    public static function getInstanceForRowid($proLib, $rowid = null) {
        try {
            $obj = new proProtocollo();
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        if (!$obj->caricaProtocolloForRowid($rowid)) {
            return false;
        }
        return $obj;
    }

    /**
     * 
     * @param proLib $proLib
     * @param type $codProt
     * @return boolean|\proProtocollo
     */
    public static function getInstance($proLib, $numero = '', $anno = '', $tipo = '', $segnatura = '', $where = '') {
        try {
            $obj = new proProtocollo();
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        if (!$obj->caricaProtocollo($numero, $anno, $tipo, $segnatura, $where)) {
            return false;
        }
        return $obj;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function setLastExitCode($lastExitCode) {
        $this->lastExitCode = $lastExitCode;
    }

    public function setLastMessage($lastMessage) {
        $this->lastMessage = $lastMessage;
    }

    public function getAnapro_rec() {
        return $this->anapro_rec;
    }

    public function setAnapro_rec($anapro_rec) {
        $this->anapro_rec = $anapro_rec;
    }

    function getAnaogg_rec() {
        return $this->anaogg_rec;
    }

    function getTrasmissioni_tab() {
        return $this->trasmissioni_tab;
    }

    function setAnaogg_rec($anaogg_rec) {
        $this->anaogg_rec = $anaogg_rec;
    }

    function setTrasmissioni_tab($trasmissioni_tab) {
        $this->trasmissioni_tab = $trasmissioni_tab;
    }

    public function getMittenti_tab() {
        return $this->mittenti_tab;
    }

    public function getDestinatari_tab() {
        return $this->destinatari_tab;
    }

    public function setMittenti_tab($mittenti_tab) {
        $this->mittenti_tab = $mittenti_tab;
    }

    public function setDestinatari_tab($destinatari_tab) {
        $this->destinatari_tab = $destinatari_tab;
    }

    public function getAllegati_tab() {
        return $this->allegati_tab;
    }

    public function setAllegati_tab($allegati_tab) {
        $this->allegati_tab = $allegati_tab;
    }

    public function getDizionarioDocumentale($Tipo, $FileName, $UuidPadre = '') {
        $this->CaricaDizionarioProtocollo($Tipo, $FileName, $UuidPadre);
        return $this->dizionarioDocumentale;
    }

    public function setDizionarioDocumentale($dizionarioDocumentale) {
        $this->dizionarioDocumentale = $dizionarioDocumentale;
    }

    public function setDestinatari_rec($destinatari_rec, $index = false) {
        if ($index === false) {
            $destinatari_rec['ROWID'] = 0;
            $this->destinatari_tab[] = $destinatari_rec;
        } else {
            $this->destinatari_tab[$index] = $destinatari_rec;
        }
    }

    public function getDestinatari_rec($index) {
        return $this->destinatari_tab[$index];
    }

    public function setTrasmissioni_rec($trasmissioni_rec, $index = false) {
        if ($index === false) {
            $trasmissioni_rec['ROWID'] = 0;
            $this->trasmissioni_tab[] = $trasmissioni_rec;
        } else {
            $this->trasmissioni_tab[$index] = $trasmissioni_rec;
        }
    }

    public function getTrasmissioni_rec($index) {
        return $this->trasmissioni_tab[$index];
    }

    public function setMittenti_rec($mittenti_rec, $index = false) {
        if ($index === false) {
            $mittenti_rec['ROWID'] = 0;
            $this->mittenti_tab[] = $mittenti_rec;
        } else {
            $this->mittenti_tab[$index] = $mittenti_rec;
        }
    }

    public function getMittenti_rec($index) {
        return $this->mittenti_tab[$index];
    }

    public function getFirmatario_rec() {
        foreach ($this->mittenti_tab as $mittente) {
            if ($mittente['FIRMATARIO'] == true) {
                return $mittente;
            }
        }
        return false;
    }

    public function setAllegati_rec($allegati_rec, $index = false) {
        if ($index === false) {
            $allegati_rec['ROWID'] = 0;
            $this->allegati_tab[] = $allegati_rec;
        } else {
            $this->allegati_tab[$index] = $allegati_rec;
        }
    }

    public function getAllegati_rec($index) {
        return $this->allegati_tab[$index];
    }

    public function getDatiAggiuntivi() {
        return $this->datiAggiuntivi;
    }

    public function setDatiAggiuntivi($datiAggiuntivi) {
        $this->datiAggiuntivi = $datiAggiuntivi;
    }

    public function getDatiConservazione() {
        return $this->datiConservazione;
    }

    public function setDatiConservazione($datiConservazione) {
        $this->datiConservazione = $datiConservazione;
    }

    private function caricaProtocolloForRowid($rowid) {
        $this->anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'rowid', $rowid);
        if (!$this->anapro_rec) {
            return false;
        }

        $numero = substr($this->anapro_rec['PRONUM'], 4);
        $anno = substr($this->anapro_rec['PRONUM'], 0, 4);
        $tipo = $this->anapro_rec['PROPAR'];

        $this->caricaOggetto();
        $this->caricaMittenti($numero, $anno, $tipo);
        $this->caricaDestinatari($numero, $anno, $tipo);
        $this->caricaAllegati($numero, $anno, $tipo);
        $this->carciaDatiConservazione();
        // Non servono indicazioni aggiuntive.Utilizza ciò che serve da anapro_rec.
        $this->caricaDatiAggiuntivi();
        return true;
    }

    private function caricaProtocollo($numero, $anno, $tipo, $segnatura = '', $where = '') {
        if ((!$numero || !$anno || !$tipo) && $segnatura == '') {
            return false;
        }
        if ($segnatura != '') {
            $this->anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'segnatura', $segnatura, $tipo);
            $numero = substr($this->anapro_rec['PRONUM'], 4);
            $anno = substr($this->anapro_rec['PRONUM'], 0, 4);
            $tipo = $this->anapro_rec['PROPAR'];
        } else {
            $codice = str_pad($anno, 4, '0', STR_PAD_RIGHT) . str_pad($numero, 6, '0', STR_PAD_LEFT);
            $this->anapro_rec = proSoggetto::getSecureAnaproFromIdUtente($this->proLib, 'codice', $codice, $tipo);
        }
        if (!$this->anapro_rec) {
            return true;
        }

        $this->caricaOggetto();
        $this->caricaMittenti($numero, $anno, $tipo);
        $this->caricaDestinatari($numero, $anno, $tipo);
        $this->caricaAllegati($numero, $anno, $tipo);
        $this->carciaDatiConservazione();
        // Non servono indicazioni aggiuntive.Utilizza ciò che serve da anapro_rec.
        $this->caricaDatiAggiuntivi();
        return true;
    }

    private function caricaOggetto() {
        $this->anaogg_rec = $this->proLib->GetAnaogg($this->anapro_rec['PRONUM'], $this->anapro_rec['PROPAR']);
    }

    private function carciaDatiConservazione() {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($this->anapro_rec['PRONUM'], $this->anapro_rec['PROPAR']);
        $this->datiConservazione = $ProConser_rec;
    }

    private function caricaMittenti($numero, $anno, $tipo) {
        if (!$this->anapro_rec) {
            return false;
        }
        switch ($tipo) {
            case 'P':
            case 'C':
                //firmatario
                $sql = "SELECT * FROM ANADES WHERE DESNUM = '{$this->anapro_rec['PRONUM']}' AND DESPAR = '{$this->anapro_rec['PROPAR']}' AND DESTIPO = 'M'";
                $firmatario_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
                if ($firmatario_rec) {
                    $mittente_rec = array();
                    $mittente_rec['DENOMINAZIONE'] = $firmatario_rec['DESNOM'];
                    $mittente_rec['CAP'] = $firmatario_rec['DESCAP'];
                    $mittente_rec['COMUNE'] = $firmatario_rec['DESCIT'];
                    $mittente_rec['PROVINCIA'] = $firmatario_rec['DESPRO'];
                    $mittente_rec['INDIRIZZO'] = $firmatario_rec['DESIND'];
                    $mittente_rec['EMAIL'] = $firmatario_rec['DESMAIL'];
                    $mittente_rec['ID'] = $firmatario_rec['ROWID'];
                    $mittente_rec['TABORIG'] = 'ANADES';
                    $mittente_rec['FIRMATARIO'] = true;
                    $mittente_rec['IDMAIL'] = $firmatario_rec['DESIDMAIL'];
                    $this->setMittenti_rec($mittente_rec);
                }
                break;
            case 'A':
                $mittente_rec = array();
                $mittente_rec['DENOMINAZIONE'] = $this->anapro_rec['PRONOM'];
                $mittente_rec['CAP'] = $this->anapro_rec['PROCAP'];
                $mittente_rec['COMUNE'] = $this->anapro_rec['PROCIT'];
                $mittente_rec['PROVINCIA'] = $this->anapro_rec['PROPRO'];
                $mittente_rec['INDIRIZZO'] = $this->anapro_rec['PROIND'];
                $mittente_rec['EMAIL'] = $this->anapro_rec['PROMAIL'];
                $mittente_rec['ID'] = $this->anapro_rec['ROWID'];
                $mittente_rec['TABORIG'] = 'ANAPRO';
                $mittente_rec['FIRMATARIO'] = true;
                $mittente_rec['IDMAIL'] = $this->anapro_rec['PROIDMAILDEST'];
                $mittente_rec['PROFIS'] = $this->anapro_rec['PROFIS'];
                $this->setMittenti_rec($mittente_rec);
                break;
        }
        $sql = "SELECT * FROM PROMITAGG WHERE PRONUM = '{$this->anapro_rec['PRONUM']}' AND PROPAR = '{$this->anapro_rec['PROPAR']}'";
        $promitagg_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        foreach ($promitagg_tab as $promitagg_rec) {
            $mittente_rec = array();
            $mittente_rec['DENOMINAZIONE'] = $promitagg_rec['PRONOM'];
            $mittente_rec['CAP'] = $promitagg_rec['PROCAP'];
            $mittente_rec['COMUNE'] = $promitagg_rec['PROCIT'];
            $mittente_rec['PROVINCIA'] = $promitagg_rec['PROPRO'];
            $mittente_rec['INDIRIZZO'] = $promitagg_rec['PROIND'];
            $mittente_rec['EMAIL'] = $promitagg_rec['PROMAIL'];
            $mittente_rec['ID'] = $promitagg_rec['ROWID'];
            $mittente_rec['TABORIG'] = 'PROMITAGG';
            $mittente_rec['FIRMATARIO'] = false;
            $mittente_rec['IDMAIL'] = $promitagg_rec['PROIDMAILDEST'];
            $mittente_rec['PROFIS'] = $promitagg_rec['PROFIS'];
            $this->setMittenti_rec($mittente_rec);
        }
    }

    private function caricaDestinatari($numero, $anno, $tipo) {
        if (!$numero || !$anno || !$tipo) {
            return false;
        }
        if (!$this->anapro_rec) {
            return false;
        }
        switch ($tipo) {
            case 'P':
                //principale
                $destinatario_rec = array();
                $destinatario_rec['DENOMINAZIONE'] = $this->anapro_rec['PRONOM'];
                $destinatario_rec['CAP'] = $this->anapro_rec['PROCAP'];
                $destinatario_rec['COMUNE'] = $this->anapro_rec['PROCIT'];
                $destinatario_rec['PROVINCIA'] = $this->anapro_rec['PROPRO'];
                $destinatario_rec['INDIRIZZO'] = $this->anapro_rec['PROIND'];
                $destinatario_rec['EMAIL'] = $this->anapro_rec['PROMAIL'];
                $destinatario_rec['ID'] = $this->anapro_rec['ROWID'];
                $destinatario_rec['TABORIG'] = 'ANAPRO';
                $destinatario_rec['IDMAIL'] = $this->anapro_rec['PROIDMAILDEST'];
                $destinatario_rec['DESFIS'] = $this->anapro_rec['PROFIS'];
                $this->setDestinatari_rec($destinatario_rec);
                //altri destinatari
                $sql = "SELECT * FROM ANADES WHERE DESNUM = '{$this->anapro_rec['PRONUM']}' AND DESPAR = '{$this->anapro_rec['PROPAR']}' AND DESTIPO = 'D'";
                $anades_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
                foreach ($anades_tab as $anades_rec) {
                    $destinatario_rec = array();
                    $destinatario_rec['DENOMINAZIONE'] = $anades_rec['DESNOM'];
                    $destinatario_rec['CAP'] = $anades_rec['DESCAP'];
                    $destinatario_rec['COMUNE'] = $anades_rec['DESCIT'];
                    $destinatario_rec['PROVINCIA'] = $anades_rec['DESPRO'];
                    $destinatario_rec['INDIRIZZO'] = $anades_rec['DESIND'];
                    $destinatario_rec['EMAIL'] = $anades_rec['DESMAIL'];
                    $destinatario_rec['ID'] = $anades_rec['ROWID'];
                    $destinatario_rec['TABORIG'] = 'ANADES';
                    $destinatario_rec['IDMAIL'] = $anades_rec['DESIDMAIL'];
                    $destinatario_rec['DESFIS'] = $anades_rec['DESFIS'];
                    $this->setDestinatari_rec($destinatario_rec);
                }
                break;
            case 'C':
            case 'A':
                //solo destinatari interni
                break;
        }

        //destinatari interni
        $sql = "SELECT * FROM ANADES WHERE DESNUM = '{$this->anapro_rec['PRONUM']}'  AND DESPAR = '{$this->anapro_rec['PROPAR']}' AND DESTIPO = 'T'";
        $anades_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        foreach ($anades_tab as $anades_rec) {
            $trasmissioni_rec = array();
            $trasmissioni_rec['codiceDestinatario'] = $anades_rec['DESCOD'];
            $trasmissioni_rec['descrizioneDestinatario'] = $anades_rec['DESNOM'];
            $trasmissioni_rec['CAP'] = $anades_rec['DESCAP'];
            $trasmissioni_rec['COMUNE'] = $anades_rec['DESCIT'];
            $trasmissioni_rec['PROVINCIA'] = $anades_rec['DESPRO'];
            $trasmissioni_rec['INDIRIZZO'] = $anades_rec['DESIND'];
            $trasmissioni_rec['EMAIL'] = $anades_rec['DESMAIL'];
            $trasmissioni_rec['ID'] = $anades_rec['ROWID'];
            $trasmissioni_rec['TABORIG'] = 'ANADES';
            $trasmissioni_rec['IDMAIL'] = $anades_rec['DESIDMAIL'];
            // Dati della trasmissione
            $trasmissioni_rec['codiceUfficio'] = $anades_rec['DESCUF'];
            $trasmissioni_rec['descrizioneUfficio'] = '';
            if ($anades_rec['DESCUF']) {
                $sql = "SELECT * FROM ANAUFF WHERE UFFCOD = '{$anades_rec['DESCUF']}' ";
                $Anauff_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
                $trasmissioni_rec['descrizioneUfficio'] = $Anauff_rec['UFFDES'];
            }
            $trasmissioni_rec['responsabile'] = $anades_rec['DESRES'];
            $trasmissioni_rec['gestione'] = $anades_rec['DESGES'];
            //Elaborazione Oggetto Trasmissione, prendo l'iter..
            $trasmissioni_rec['oggettoTrasmissione'] = '';
            $this->setTrasmissioni_rec($trasmissioni_rec);
        }
    }

    private function caricaAllegati($numero, $anno, $tipo) {
        if (!$numero || !$anno || !$tipo) {
            return false;
        }
        $proLibAllegati = new proLibAllegati();
        $codice = str_pad($anno, 4, '19', STR_PAD_LEFT) . str_pad($numero, 6, '0', STR_PAD_LEFT);
        $allegati_tab = $this->proLib->GetAnadoc($codice, 'codice', true, $tipo);
        $path = $this->proLib->SetDirectory($codice, $tipo);

        foreach ($allegati_tab as $allegati_rec) {
            if ($proLibAllegati->CheckDocAllegato($allegati_rec['ROWID'])) {
//            if (is_file($path . "/" . $allegati_rec['DOCFIL'])) {
                $allegato = array();
                $allegato['ID'] = $allegati_rec['ROWID'];
                $allegato['TIPOFILE'] = $allegati_rec['DOCTIPO'];
                $allegato['NOMEFILE'] = $allegati_rec['DOCNAME'];
                $ext = pathinfo($path . "/" . $allegati_rec['DOCFIL'], PATHINFO_EXTENSION);
                $allegato['ESTENSIONE'] = $ext;
                /*
                  $fp = @fopen($path . "/" . $allegati_rec['DOCFIL'], "rb", 0);
                  if ($fp) {
                  $binFile = fread($fp, filesize($path . "/" . $allegati_rec['DOCFIL']));
                  $base64File = base64_encode($binFile);
                  }
                  fclose($fp);
                  $allegato['STREAM'] = $base64File;
                 * 
                 */
                $allegato['NOTE'] = $allegati_rec['DOCNOT'];
                $this->setAllegati_rec($allegato);
            }
        }
    }

    public function getWsAllegatoFromId($id, $blockSize = '', $part = 1) {
        $proLibAllegati = new proLibAllegati();

        if (!$this->anapro_rec) {
            $this->setLastExitCode(-1);
            $this->setLastMessage('Protocollo non accessibile.');
            return false;
        }

        $anadoc_rec = $this->proLib->GetAnadoc($id, 'rowid', false);
        if (!$anadoc_rec) {
            $this->setLastExitCode(-1);
            $this->setLastMessage('Allegato non accessibile');
            return false;
        }

        // Prendo il binario in base64
        $binary = $proLibAllegati->GetDocBinary($anadoc_rec['ROWID'], true);
        if ($binary) {
            $allegato = array();
            $allegato['id'] = $anadoc_rec['ROWID'];
            $allegato['tipoFile'] = $anadoc_rec['DOCTIPO'];
            $allegato['nomeFile'] = $anadoc_rec['DOCNAME'];
            $ext = pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
            $allegato['estensione'] = $ext;
            $allegato['note'] = $anadoc_rec['DOCNOT'];
            if ($blockSize == '' || $blockSize < 1) {
                $allegato['stream'] = $binary;
            } else {
                $start = $blockSize * 1024 * ($part - 1);
                if ($start > strlen($binary)) {
                    $allegato['stream'] = '';
                }
                $allegato['stream'] = trim(substr($binary, $start, $blockSize * 1024));
                $allegato['part'] = $part;
            }
        } else {
            $this->setLastExitCode(-1);
            $this->setLastMessage('lettura Allegato: ' . $anadoc_rec['DOCFIL'] . ' fallita');
            return false;
        }
        return $allegato;
    }

    public function getWsElementiAllegatoFromId($id) {
        if (!$this->anapro_rec) {
            $this->setLastExitCode(-1);
            $this->setLastMessage('Protocollo non accessibile.');
            return false;
        }

        $anadoc_rec = $this->proLib->GetAnadoc($id, 'rowid', false);
        if (!$anadoc_rec) {
            $this->setLastExitCode(-1);
            $this->setLastMessage('Allegato non accessibile');
            return false;
        }

        $allegato = array();
        $allegato['id'] = $anadoc_rec['ROWID'];
        $allegato['tipoFile'] = $anadoc_rec['DOCTIPO'];
        $allegato['nomeFile'] = $anadoc_rec['DOCNAME'];
        $ext = pathinfo($anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
        $allegato['estensione'] = $ext;
        $allegato['note'] = $anadoc_rec['DOCNOT'];
//        $docmeta = unserialize($anadoc_rec['DOCMETA']);
//        if ($docmeta['SEGNATURA']) {
//            $allegato['marcaDocumento'] = '1';
//        }
        return $allegato;
    }

    public function inserisciAllegato($anno = '', $numero = '', $tipo = '', $segnatura = '', $TipoFile = '', $NomeFile = '', $Estensione = '', $File = '', $Note = '') {
        if ((!$numero || !$anno || !$tipo) && $segnatura == '') {
            return false;
        }
        if ($TipoFile == '' || $NomeFile == '' || $Estensione == '') {
            return false;
        }
    }

    public function getWsItemProtocollo($numero, $anno, $tipo) {
        if (!$numero || !$anno || !$tipo) {
            return false;
        }
        if (!$this->anapro_rec) {
            return false;
        }
        $item = array();
        $item['rowID'] = $this->anapro_rec['ROWID'];
        $item['annoProtocollo'] = substr($this->anapro_rec['PRONUM'], 0, 4);
        $item['numeroProtocollo'] = substr($this->anapro_rec['PRONUM'], 4);
        $item['dataProtocollo'] = $this->anapro_rec['PRODAR'];
        $item['segnatura'] = $this->anapro_rec['PROSEG'];
        $item['oggetto'] = $this->anaogg_rec['OGGOGG'];

        $sqlNote = "SELECT NOTE.* FROM NOTECLAS JOIN NOTE ON NOTECLAS.ROWIDNOTE = NOTE.ROWID WHERE NOTECLAS.CLASSE = 'ANAPRO' AND NOTECLAS.ROWIDCLASSE = " . $this->anapro_rec['ROWID'];
        $note_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlNote, true);
        if ($note_tab) {
            $item['note'] = array();
            foreach ($note_tab as $key => $nota) {
                $item['note']['nota'][$key]['oggetto'] = $nota['OGGETTO'];
                $item['note']['nota'][$key]['testo'] = $nota['Testo'];
            }
        }

//        $item['titolario'] = $this->anapro_rec['PROCCF'];

        $classificazione = '';
        if (strlen($this->anapro_rec['PROCCF']) === 4) {
            $classificazione = intval($this->anapro_rec['PROCCF']);
        } else if (strlen($this->anapro_rec['PROCCF']) === 8) {
            $classificazione = intval(substr($this->anapro_rec['PROCCF'], 0, 4)) . '.' . intval(substr($this->anapro_rec['PROCCF'], 4, 4));
        } else if (strlen($this->anapro_rec['PROCCF']) === 12) {
            $classificazione = intval(substr($this->anapro_rec['PROCCF'], 0, 4)) . '.' . intval(substr($this->anapro_rec['PROCCF'], 4, 4)) . '.' . intval(substr($this->anapro_rec['PROCCF'], 8, 4));
        } else {
            $classificazione = $this->anapro_rec['PROCCF'];
        }
        $item['classificazione'] = $classificazione;

        $sql = "SELECT * FROM ANACAT WHERE CATCOD = '{$this->anapro_rec['PROCCF']}' AND VERSIONE_T = '" . $this->anapro_rec['VERSIONE_T'] . "' AND CATDAT = '' ";
        $anacat_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, FALSE);
        if ($anacat_rec) {
            $item['classificazione_Descrizione'] = $anacat_rec['CATDES'];
        }
        $sql = "SELECT * FROM ANACLA WHERE CLACCA = '{$this->anapro_rec['PROCCF']}' AND VERSIONE_T = '" . $this->anapro_rec['VERSIONE_T'] . "' AND CLADAT = '' ";
        $anacla_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, FALSE);
        if ($anacla_rec) {
            $item['classificazione_Descrizione'] = $anacla_rec['CLADE1'] . " " . $anacla_rec['CLADE2'];
        }
        $sql = "SELECT * FROM ANAFAS WHERE FASCCF = '{$this->anapro_rec['PROCCF']}' AND VERSIONE_T = '" . $this->anapro_rec['VERSIONE_T'] . "' AND FASDAT = '' ";
        $anafas_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, FALSE);
        if ($anafas_rec) {
            $item['classificazione_Descrizione'] = $anafas_rec['FASDES'];
        }
        $item['tipoProtocollo'] = $this->anapro_rec['PROPAR'];
        switch ($this->anapro_rec['PROPAR']) {
            case 'A':
                $item['tipoProtocollo_Descrizione'] = 'ARRIVO';
                break;
            case 'P':
                $item['tipoProtocollo_Descrizione'] = 'PARTENZA';
                break;
            case 'C':
                $item['tipoProtocollo_Descrizione'] = 'DOCUMENTO FORMALE';
                break;
        }
        $item['tipoDocumento'] = $this->anapro_rec['PROCODTIPODOC'];
        if ($this->anapro_rec['PROCODTIPODOC']) {
            $sql = "SELECT * FROM ANATIPODOC WHERE CODICE = '{$this->anapro_rec['PROCODTIPODOC']}'";
            $anadoctipo_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, FALSE);
            if ($anadoctipo_rec) {
                $item['descrTipoDocumento'] = $anadoctipo_rec['DESCRIZIONE'];
            }
        }
        $firmatario = $this->getFirmatario_rec();
        if ($firmatario) {
            $item['firmatario'] = $firmatario['ID'];
            $item['firmatario_Descrizione'] = $firmatario['DENOMINAZIONE'];
        }
        if ($this->mittenti_tab) {
            $item['mittenti'] = array();
            foreach ($this->mittenti_tab as $key => $mittenti_rec) {
                $item['mittenti'][$key]['denominazione'] = $mittenti_rec['DENOMINAZIONE'];
                $item['mittenti'][$key]['cap'] = $mittenti_rec['CAP'];
                $item['mittenti'][$key]['citta'] = $mittenti_rec['COMUNE'];
                $item['mittenti'][$key]['prov'] = $mittenti_rec['PROVINCIA'];
                $item['mittenti'][$key]['indirizzo'] = $mittenti_rec['INDIRIZZO'];
                $item['mittenti'][$key]['email'] = $mittenti_rec['EMAIL'];
                $item['mittenti'][$key]['codiceFiscale'] = $mittenti_rec['PROFIS'];
                $ElencoMail = $this->getNotifiche($mittenti_rec['IDMAIL']);
                foreach ($ElencoMail as $key2 => $Mail_rec) {
                    $item['mittenti'][$key]['notificheMail'][$key2]['rowidmail'] = $Mail_rec['ROWID'];
                    $item['mittenti'][$key]['notificheMail'][$key2]['tipomail'] = $Mail_rec['PECTIPO'];
                }
            }
        }
        $item['documentoRiservato'] = $this->anapro_rec['PRORISERVA'];
        $item['utenteDiInserimento'] = $this->anapro_rec['PROUTE'];
        if ($this->allegati_tab) {
            $item['allegati'] = array();
            foreach ($this->allegati_tab as $key => $allegato_Rec) {
                $item['allegati'][$key]['id'] = $allegato_Rec['ID'];
                $item['allegati'][$key]['tipoFile'] = $allegato_Rec['TIPOFILE'];
                $item['allegati'][$key]['nomeFile'] = $allegato_Rec['NOMEFILE'];
                $item['allegati'][$key]['estensione'] = $allegato_Rec['ESTENSIONE'];
                $item['allegati'][$key]['note'] = $allegato_Rec['NOTE'];
            }
        }
        if ($this->anapro_rec['PROPAR'] == 'A') {
            $item['dataDocumento'] = $this->anapro_rec['PRODAS'];
            $item['protocolloMittente'] = $this->anapro_rec['PRONPA'];
        }
        $item['numeroProtocolloAntecedente'] = substr($this->anapro_rec['PROPRE'], 4);
        $item['annoProtocolloAntecedente'] = substr($this->anapro_rec['PROPRE'], 0, 4);
        $item['codiceFascicolo'] = $this->anapro_rec['PROFASKEY'];

        if ($this->destinatari_tab) {
            $item['destinatari'] = array();
            foreach ($this->destinatari_tab as $key => $destinatari_rec) {
                $item['destinatari'][$key]['denominazione'] = $destinatari_rec['DENOMINAZIONE'];
                $item['destinatari'][$key]['cap'] = $destinatari_rec['CAP'];
                $item['destinatari'][$key]['citta'] = $destinatari_rec['COMUNE'];
                $item['destinatari'][$key]['prov'] = $destinatari_rec['PROVINCIA'];
                $item['destinatari'][$key]['indirizzo'] = $destinatari_rec['INDIRIZZO'];
                $item['destinatari'][$key]['email'] = $destinatari_rec['EMAIL'];
                $item['destinatari'][$key]['codiceFiscale'] = $destinatari_rec['DESFIS'];
                $ElencoMail = $this->getNotifiche($destinatari_rec['IDMAIL']);
                foreach ($ElencoMail as $key2 => $Mail_rec) {
                    $item['destinatari'][$key]['notificheMail'][$key2]['rowidmail'] = $Mail_rec['ROWID'];
                    $item['destinatari'][$key]['notificheMail'][$key2]['tipomail'] = $Mail_rec['PECTIPO'];
                }
            }
        }
        if ($this->trasmissioni_tab) {
            $item['trasmissioniInterne'] = array();
            foreach ($this->trasmissioni_tab as $key => $trasmissione_rec) {
                $item['trasmissioniInterne'][$key]['cap'] = $trasmissione_rec['CAP'];
                $item['trasmissioniInterne'][$key]['citta'] = $trasmissione_rec['COMUNE'];
                $item['trasmissioniInterne'][$key]['prov'] = $trasmissione_rec['PROVINCIA'];
                $item['trasmissioniInterne'][$key]['indirizzo'] = $trasmissione_rec['INDIRIZZO'];
                $item['trasmissioniInterne'][$key]['email'] = $trasmissione_rec['EMAIL'];
                // Dati della trasmissione
                $item['trasmissioniInterne'][$key]['codiceDestinatario'] = $trasmissione_rec['codiceDestinatario'];
                $item['trasmissioniInterne'][$key]['descrizioneDestinatario'] = $trasmissione_rec['descrizioneDestinatario'];
                $item['trasmissioniInterne'][$key]['codiceUfficio'] = $trasmissione_rec['codiceUfficio'];
                $item['trasmissioniInterne'][$key]['descrizioneUfficio'] = $trasmissione_rec['descrizioneUfficio'];
                $item['trasmissioniInterne'][$key]['responsabile'] = $trasmissione_rec['responsabile'];
                $item['trasmissioniInterne'][$key]['gestione'] = $trasmissione_rec['gestione'];
                $item['trasmissioniInterne'][$key]['oggettoTrasmissione'] = $trasmissione_rec['oggettoTrasmissione'];
                $ElencoMail = $this->getNotifiche($destinatari_rec['IDMAIL']);
                foreach ($ElencoMail as $key2 => $Mail_rec) {
                    $item['trasmissioniInterne'][$key]['notificheMail'][$key2]['rowidmail'] = $Mail_rec['ROWID'];
                    $item['trasmissioniInterne'][$key]['notificheMail'][$key2]['tipomail'] = $Mail_rec['PECTIPO'];
                }
            }
        }

        /*
         * Dati conservazione
         */
        if ($this->datiConservazione) {
            $item['statoConservazione'] = 'CONSERVATO';
            $item['dataConservazione'] = $this->datiConservazione['DATAVERSAMENTO'];
        } else {
            $item['statoConservazione'] = 'NON CONSERVATO';
        }
        return $item;
    }

    public function caricaDatiAggiuntivi() {
        $datiAggiuntivi = array();
        // $this->anapro_rec
        $proLibTabDag = new proLibTabDag();

        // Prevedere lotti di fatture????
        // Dati aggiuntivi Fattura
        $TabDag_tab = $proLibTabDag->GetTabdag('ANAPRO', 'codice', $this->anapro_rec['ROWID'], '', '', true);
        foreach ($TabDag_tab as $TabDag_rec) {
            $Fonte = $TabDag_rec['TDAGFONTE'];
            $DagSet = $TabDag_rec['TDAGSET'];

            $datiAggiuntivi[$Fonte][$DagSet][$TabDag_rec['TDAGCHIAVE']] = $TabDag_rec['TDAGVAL'];
        }
        $this->setDatiAggiuntivi($datiAggiuntivi);
    }

    public function CaricaDizionarioProtocollo($Tipo, $FileName, $UuidPadre = '') {
        $DizionarioProtocollo = array();
        $DizionarioProtocollo['NUMERO'] = substr($this->anapro_rec['PRONUM'], 4); // Deve essere allineato?
        $DizionarioProtocollo['ANNO'] = substr($this->anapro_rec['PRONUM'], 0, 4);
        $DizionarioProtocollo['TIPO'] = $this->anapro_rec['PROPAR'];
        $DizionarioProtocollo['DATA'] = $this->anapro_rec['PRODAR']; // Date già formattato?
        if ($this->anapro_rec['PROPAR'] == 'A') {
            $DizionarioProtocollo['MITTENTE'] = htmlspecialchars($this->anapro_rec['PRONOM']);
            $DizionarioProtocollo['DESTINATARIO'] = '';
        } else if ($this->anapro_rec['PROPAR'] == 'C') {
            $Firmatario = $this->getFirmatario_rec();
            $DizionarioProtocollo['MITTENTE'] = htmlspecialchars($Firmatario['DENOMINAZIONE']);
            $DizionarioProtocollo['DESTINATARIO'] = '';
        } else {
            $DizionarioProtocollo['MITTENTE'] = '';
            $DizionarioProtocollo['DESTINATARIO'] = htmlspecialchars($this->anapro_rec['PRONOM']);
        }
        $DizionarioProtocollo['OGGETTO'] = utf8_encode(htmlspecialchars($this->anaogg_rec['OGGOGG']));
        $DizionarioProtocollo['RISERVATO'] = $this->anapro_rec['PRORISERVA'];
        $DizionarioProtocollo['RUOLO'] = 'Protocollatore'; // Ruolo ???
        // Dizionario Dati Aggiuntivi...

        $DizionarioProtocollo['UUIDPADRE'] = $UuidPadre;

        switch ($Tipo) {
            case proLibAllegati::ALLEGATO_FATT:
                $DatiAggiuntivi = $this->getDatiAggiuntivi();
                $DatiAggFattura = $DatiAggiuntivi['FATT_ELETTRONICA'];
                /* Prendo la prima fattura elettronica per inserire i metadati */
//                $ArrFattura = $DatiAggFattura[0];
                $ArrFattura = reset($DatiAggFattura); // Altrimenti "ANAPRO".ROWID."1"
                $DizionarioProtocollo['CODICEFISCALE'] = $ArrFattura['CodiceFiscale'];
                $DizionarioProtocollo['CODICEDESTINATARIO'] = $ArrFattura['CodiceDestinatario'];
//                $DizionarioProtocollo['IDFISCALEIVA'] = $ArrFattura['IdFiscaleIVA'];// é composto da "Paese"+"CF" contabilità chiede solo CF
                $DizionarioProtocollo['IDFISCALEIVA'] = $ArrFattura['CodiceFiscale'];
                $DizionarioProtocollo['FORNITORE_DENOMINAZIONE'] = htmlspecialchars($ArrFattura['Fornitore_Denominazione']);
                $DizionarioProtocollo['TOTFATTURE'] = count($DatiAggFattura);
                // Trovo MT 
                $DatiAggMessaggi = $DatiAggiuntivi['MESSAGGIO_SDI'];
                foreach ($DatiAggMessaggi as $key => $MessaggioSdi) {
                    if ($DatiAggMessaggi[$key]['Tipo'] == 'MT') {
                        $DizionarioProtocollo['IDENTIFICATIVOSDI'] = $DatiAggMessaggi[$key]['IdentificativoSdI'];
                        $DizionarioProtocollo['VERSIONEFLUSSO'] = $DatiAggMessaggi[$key]['Formato'];
                        break;
                    }
                }

                break;

            case proLibAllegati::ALLEGATO_ANNESSO_FATT:
                $DatiAggiuntivi = $this->getDatiAggiuntivi();
                $DatiAggMessaggi = $DatiAggiuntivi['MESSAGGIO_SDI'];
                foreach ($DatiAggMessaggi as $key => $MessaggioSdi) {
                    if ($DatiAggMessaggi[$key]['NomeFileMessaggio'] == $FileName) {
                        $DizionarioProtocollo['IDENTIFICATIVOSDI'] = $DatiAggMessaggi[$key]['IdentificativoSdI'];
                        $DizionarioProtocollo['TIPOANNESSO'] = $DatiAggMessaggi[$key]['Tipo'];
                        $DizionarioProtocollo['IDUNITADOC'] = $DatiAggMessaggi[$key]['CodUnivocoFile'];
                        break;
                    }
                }
                break;

            default:
            case proLibAllegati::ALLEGATO_PROT:
                break;
        }

        $this->setDizionarioDocumentale($DizionarioProtocollo);
    }

    public function getNotifiche($idMail = '') {
        if (!$idMail) {
            return array();
        }
        $proLibMail = new proLibMail();
        $ElencoMail = $proLibMail->GetElencoNotifiche($idMail);
        foreach ($ElencoMail as $key => $MailRec) {
            if (!$MailRec['PECTIPO']) {
                $ElencoMail[$key]['PECTIPO'] = 'messaggio';
            }
        }
        return $ElencoMail;
    }

}

?>
