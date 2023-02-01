<?php

/**
 *  Programma Popolamento sue chiaravalle GMI Edilizia
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Tania Angeloni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.11.2017
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praGmi() {
    $intSuapSbt = new praGmi();
    $intSuapSbt->parseEvent();
    return;
}

class praGmi extends itaModel {

    const SPORTELLO_DEFAULT = 6;
    const PROCEDIMENTO_DEFAULT = 500001;
    const PROCEDIMENTO_AGIBILITA = 500002;
    const PROCEDIMENTO_PAESAGGISTICA = 500003;
    const RESPONSABILE_DEFAULT = '000001';
    const DESRUOIMPRESA_DEFAULT = '0012';
    const DESRUOTECNICO_DEFAULT = '0007';
    const DESRUORICHIEDENTE_DEFAULT = '0002';
    const PATH_ALLEGATI = 'C:/Works/FilesGMI/Edilizia/documentazione/0000000000';
    const CDU_SERIE = 46;
    const CDU_PROCEDIMENTO = 500004;

    static $SUBJECT_BASE_RESPONSABILE = array(
        "AN" => "000003",
        "CC" => "000002",
        "MG" => "000004",
        "FF" => "000005",
        "EV" => "000006",
        "0" => "000000"
    );
    static $SUBJECT_PROT_COMUNICAZIONE = array(
        "A" => "ARRIVO",
        "P" => "PARTENZA"
    );
    static $SUBJECT_BASE_TIPOPASSO = array(
        "SANITARIO" => "000001",
        "ISTRUTTORIA" => "000002",
        "FINELAVORI" => "000003",
        "PDC" => "1",
        "VERBALE" => "2",
        "AGIBILITA" => "3",
    );
    static $SUBJECT_BASE_RAGIONESOCIALE = array(
        "01" => "ASSOCIAZIONI",
        "0" => "Non definito",
        "02" => "CONDOMINIO",
        "03" => "COOPERATIVA EDILIZIA DI ABITAZIONE",
        "04" => "COMUNE",
        "05" => "ENTE",
        "06" => "COOPERATIVA",
        "07" => "DITTA INDIVIDUALE ",
        "08" => "IMPRESA IMMOBILIARE BANCARIA O ASSICURATIVA",
        "09" => "PERSONA FISICA",
        "10" => "REGIONE",
        "11" => "PROVINCIA",
        "12" => "ISTITUTI E SIMILI",
        "12A" => "I.A.C.P",
        "13" => "IMPRESA COSTRUZIONI",
        "14" => "S.R.L.",
        "15" => "S.A.S.",
        "16" => "S.N.C",
        "17" => "S.P.A.",
        "18" => "AMMINISTRATORE",
        "19" => "IMPRESA",
        "20" => "PROCURATORE",
        "21" => "STUDIO MEDICO",
        "22" => "STUDIO ASSOCIATO",
        "23" => "CONGREGAZIONE",
        "24" => "SOCIETA",
        "01A" => "AZIENDA",
    );
    static $SUBJECT_BASE_LETTERA = array(
        "CE" => "1",
        "C.E." => "1",
        "C.E" => "1",
        "701" => "1",
        "CE3" => "1",
        "132" => "1",
        "5760" => "1",
        "DIA" => "2",
        "PDL" => "3",
        "CIL" => "4",
        "PRG" => "5",
        "MO" => "6",
        "DIAS" => "7",
        "SCIA" => "8",
        "CIA" => "9",
        "PP" => "10",
        "COND" => "11",
        "CILA" => "12",
        "DET" => "13",
        "DI" => "99",
        "DU" => "99",
        "28" => "99",
        "MOù" => "99",
        "2006" => "99",
        "2013" => "99",
        "PC" => "99",
        "DIAI" => "99.",
        "1890" => "99",
        "1951" => "99",
        "CCE" => "99",
        "L662" => "99",
        "7167" => "99",
        "6332" => "99",
        "CEE" => "99",
        "284" => "99",
        "V" => "99",
        "3822" => "99",
        "1860" => "99",
        "1985" => "99",
    );
    static $SUBJECT_BASE_PROCEDIMENTO = array(
        "CE" => "000001",
        "DIA" => "000002",
        "PDL" => "000003",
        "CIL" => "000004",
        "PRG" => "000005",
        "DI" => "500001", //
        "DU" => "500001", //
        "28" => "500001", //
        "MO" => "000006",
        "MOù" => "500001", //
        "DIAS" => "000002",
        "2006" => "500001", //
        "SCIA" => "000007",
        "CIA" => "000008",
        "PP" => "000009",
        "2013" => "500001", //
        "PC" => "500001", ///
        "DIAI" => "500001", ////
        "COND" => "000010",
        "C.E." => "000001",
        "C.E" => "000001",
        "701" => "000001",
        "1890" => "500001", //
        "CE3" => "000001",
        "1951" => "500001", ///
        "CCE" => "500001", ///
        "132" => "000001", ///
        "L662" => "500001", ///
        "CILA" => "000011",
        "7167" => "500001", ///
        "5760" => "000001",
        "6332" => "500001",
        "CEE" => "500001",
        "284" => "500001",
        "V" => "500001",
        "3822" => "500001",
        "1860" => "500001",
        "1985" => "500001",
        "DET" => "000012",
    );
    static $SUBJECT_BASE_AGIBILITA = array(
        "A" => "20",
        "B" => "21",
        "C" => "22",
        "D" => "23",
        "E" => "24",
        "F" => "25", //
        "G" => "26", //
        "H" => "27", //
        "I" => "28",
        "L" => "29", //
        "M" => "30",
        "N" => "31", //
        "O" => "32",
        "P" => "33",
        "Q" => "34",
        "R" => "35", //
        "S" => "36", ///
        "D1" => "37",
        "T" => "38", ////
        "U" => "39",
        "Z" => "40",
        "Y" => "41",
        "V" => "42",
    );
    public $praLib;
    public $PRAM_DB;
    public $fileLog;
    public $nameForm;
    public $Id;
    public $Pratica;
    public $gesnum_anno = array();

    function __construct() {
        parent::__construct();
        try {
            /*
             * carico le librerie
             * 
             */
            $this->nameForm = 'praGMI';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');

            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', "C615");
            $this->GMI_DB = ItaDB::DBOpen('GMI2', "");
            $this->Id = App::$utente->getKey($this->nameForm . '_Id');
            $this->Pratica = App::$utente->getKey($this->nameForm . '_Pratica');
            $this->gesnum_anno = App::$utente->getKey($this->nameForm . '_gesnum_anno');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
            App::$utente->setKey($this->nameForm . '_Id', $this->Id);
            App::$utente->setKey($this->nameForm . '_Pratica', $this->Pratica);
            App::$utente->setKey($this->nameForm . '_gesnum_anno', $this->gesnum_anno);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praGMI_" . time() . ".log";
                $this->scriviLog("Avvio Programma praGMI");
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->importafascicolo();
                        break;
                    case $this->nameForm . '_ConfermaPaesaggistica':
                        $this->importaPaesaggistica();
                        break;
                    case $this->nameForm . '_ConfermaAgibilita':
                        $this->importaAgibilita();
                        break;
                    case $this->nameForm . '_Allegati':
                        $this->setdirectoryAll();
                        break;
                    case $this->nameForm . '_Cdu':
                        $this->importaCDU();
                        break;
                    case $this->nameForm . '_vediLog':
                        $FileLog = 'LOG_IMPORTAZIONE_' . date('His') . '.log';
                        Out::openDocument(utiDownload::getUrl($FileLog, $this->fileLog));
                        break;
                    case $this->nameForm . '_Rilancia':
                        $model = $this->nameForm;
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }

                break;

            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    private function scriviLog($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function importafascicolo() {
        /*
         * lettura db sorgente
         */
        $sql_base = "SELECT
            pratica.*,
            protocollo.GeneraleAnno,
            protocollo.GeneraleNumero,
            protocollo.GeneraleLettera,
            protocollo.RiferimentoSUAPAnno,
            protocollo.RiferimentoSUAPNumero,
            protocollo.RiferimentoSUAPLettera,
            protocollo.DataInserimento,
            protocollo.ParticellaAnnotazioni,
            protocollo.IdEdificio,
            protocollo.Via,
            protocollo.Civico
            FROM
            pratica,
            protocollo
            WHERE
            pratica.Id = protocollo.Id
            ORDER BY pratica.Anno ASC
            ";
        // AND pratica.Id = '12578'
        $gmi_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql_base, true);
        $i = 0;
        foreach ($gmi_tab as $gmi_rec) {
            if (empty($gmi_rec['Istruttore'])) {  // CERCO IL RESPONSABILE DEL FASCICOLO 
                $gmi_rec['GESRES'] = self::RESPONSABILE_DEFAULT;
            } else {
                $gmi_rec['GESRES'] = self::$SUBJECT_BASE_RESPONSABILE[$gmi_rec['Istruttore']];
            }
            if ($gmi_rec['DataInserimento']) {
                $data = $this->cleardate($gmi_rec['DataInserimento']);
                $datarec = $this->convertdate($data);
                $gmi_rec['GESDRE'] = $datarec;
            } else {
                $gmi_rec['GESDRE'] = '18991230';
            }
            if (empty($gmi_rec['GESDRE'])) {
                $gmi_rec['GESDRE'] = '18991230';
            }
            $toinsert = $this->inserisciFascicolo($gmi_rec);
            if (!$toinsert) {
                $anomalie++;
                $flAnomalia = true;
            } else {
                $i++;
                $finelavori_rec = $this->importafinelavori($this->Id);
                if ($finelavori_rec) {
                    $pass_rec = array('DESCRIZIONE' => 'Fine Lavori', 'DATA' => '', 'ESTREMI' => '', 'NUMERODATA' => $finelavori_rec['DataFineLavoro']);
                    $toinsert_5 = $this->creapasso($this->Pratica, self::$SUBJECT_BASE_TIPOPASSO['FINELAVORI'], $gmi_rec['GESRES'], $prodpt = 'FineLavori', $pass_rec);
                    if ($toinsert_5) {
                        $propak = $this->getpropak($this->Pratica, 'Fine Lavori', $finelavori_rec);
                    }
                }

                $impresa_rec = $this->importaimpresa($this->Id);
                $tecnico_tab = $this->importatecnico($this->Id);
                $richiedente_tab = $this->importarichiedente($this->Id);
                if ($gmi_rec['Via']) {
                    $this->caricalocalizzazioneintervento($this->Pratica, $gmi_rec['Via'], $gmi_rec['Civico']);
                }
                if (!empty($gmi_rec['TestoParereSanitario'])) {
                    // CREO PASSO PARERE
                    $pass_rec = array('DESCRIZIONE' => 'Parere Sanitario', 'DATA' => $gmi_rec['DataParereSanitario'], 'ESTREMI' => $gmi_rec['TestoParereSanitario'] . ' ' . $gmi_rec['EstremiParereSanitario']);
                    $toinsert_1 = $this->creapasso($this->Pratica, self::$SUBJECT_BASE_TIPOPASSO['SANITARIO'], $gmi_rec['GESRES'], $prodpt = 'ParereSanitario', $pass_rec);
                }
                if (!empty($gmi_rec['TestoIstruttoria'])) {
                    $pass_rec = array('DESCRIZIONE' => 'Istruttoria', 'DATA' => $gmi_rec['DataIstruttoria'], 'ESTREMI' => $gmi_rec['TestoIstruttoria']);
                    $toinsert_2 = $this->creapasso($this->Pratica, self::$SUBJECT_BASE_TIPOPASSO['ISTRUTTORIA'], $gmi_rec['GESRES'], $prodpt = 'Istruttoria', $pass_rec);
                }
                if (!empty($gmi_rec['NumeroAtto']) || !empty($gmi_rec['LetteraAtto']) || !empty($gmi_rec['DataAtto'])) {
                    // ATTO TO DO
                    if ($gmi_rec['DataAtto'] != '30/12/1899 00:00:00' || $gmi_rec['NumeroAtto'] != 0 || !empty($gmi_rec['LetteraAtto'])) {
                        $pass_rec = array('DESCRIZIONE' => 'Permesso di costruire', 'DATA' => '', 'ESTREMI' => '', 'NUMERO' => $gmi_rec['NumeroAtto'], 'NUMEROANNO' => '', 'NUMERODATA' => $gmi_rec['DataAtto'], 'LETTERAATTO' => $gmi_rec['LetteraAtto']);
                        $toinsert_3 = $this->creapasso($this->Pratica, 'PDC', $gmi_rec['GESRES'], $prodpt = '', $pass_rec);
                    }
                }
                if (!empty($gmi_rec['GeneraleLettera'])) { // CREA PASSO VERBALE 
                    $pass_rec = array('DESCRIZIONE' => 'Verbale', 'DATA' => '', 'ESTREMI' => '', 'NUMERO' => $gmi_rec['GeneraleLettera'], 'NUMEROANNO' => '');
                    $toinsert_4 = $this->creapasso($this->Pratica, 'VERBALE', $gmi_rec['GESRES'], $prodpt = '', $pass_rec);
                }

                if (!empty($gmi_rec['ContributoCostoCostruzione']) || !empty($gmi_rec['UrbanizzazionePrimaria']) || !empty($gmi_rec['UrbanizzazioneSecondaria'])) {
                    // INSERISCO I COSTI
                    $this->importaspese($this->Pratica, $gmi_rec['ContributoCostoCostruzione'], $gmi_rec['UrbanizzazionePrimaria'], $gmi_rec['UrbanizzazioneSecondaria']);
                }
            }
            // RIORDINO GLI EVENTUALI PASSI CREATI
            if ($toinsert_1 || $toinsert_2 || $toinsert_3 || $toinsert_4 || $toinsert_5 || $toinsert_6) {
                $this->ordinaPassi($this->Pratica);
            }
        }
        Out::msgInfo("OK", $i . " Fascicoli caricati");
        return true;
    }

    private function inserisciFascicolo($gmi_rec) {
        $proges_rec['GESNUM'] = $this->gestGesnum($gmi_rec['Anno']); //RICAVO GESNUM PROGRESSIVO PER ANNO
        $this->Id = $gmi_rec['Id'];  // MI SALVO ID PER RELAZIONI 
        if (!empty($gmi_rec['Lettera'])) {
            $decodlettera = self::$SUBJECT_BASE_LETTERA[strtoupper($gmi_rec['Lettera'])];
            $proges_rec['GESPRO'] = self::$SUBJECT_BASE_PROCEDIMENTO[strtoupper($gmi_rec['Lettera'])];
        } else {
            $decodlettera = '1234';
            $proges_rec['GESPRO'] = self::PROCEDIMENTO_DEFAULT;
        }
        $this->Pratica = $proges_rec['GESNUM'];
        $proges_rec['GESTSP'] = self::SPORTELLO_DEFAULT; // Sportello Da definire
        if ($gmi_rec['RiferimentoSUAPAnno'] == '2015' or $gmi_rec['RiferimentoSUAPAnno'] == '2016' or $gmi_rec['RiferimentoSUAPAnno'] == '2017') {

            $proges_rec['GESPRA'] = $gmi_rec['RiferimentoSUAPAnno'] . str_pad($gmi_rec['RiferimentoSUAPNumero'], 6, "0", STR_PAD_LEFT); //   Numero Pratica on-line.
            if ($gmi_rec['RiferimentoSUAPLettera'] == 'SUAP') {
                $proges_rec['GESTSP'] = '5'; // Sportello Da definire setto sportello suap se proviene da suap 
            }
            $this->ricavadaticatastali($gmi_rec['ParticellaAnnotazioni']);
        }
        if ($gmi_rec['IdEdificio'] != 0) {
            // FUNZIONE CARICA CATASTO  ['ID EDIFICO']
            $this->importadaticatastali($gmi_rec['IdEdificio']);
        }
        if ($gmi_rec['GeneraleAnno'] && $gmi_rec['GeneraleNumero']) {
            // PROTOCOLLO IL ARRIVO
            $proges_rec['GESPAR'] = 'A';
        }
        $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
        $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
        $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato
        $proges_rec['GESRES'] = $gmi_rec['GESRES'];                                                   // Resp. Procedimento <- Da valorizzare per sbt
        $proges_rec['GESDRE'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDRI'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESORA'] = "00:00";     // Data registrazione <- ora INSERIMENTO protocollo 
        $proges_rec['GESDCH'] = $gmi_rec['GESDRE'];      // data chiusura fascicolo che è uaguale a quella di apertura    per non lasciarlo aperto
        $proges_rec['GESNPR'] = $gmi_rec['GeneraleAnno'] . $gmi_rec['GeneraleNumero'];                      // Anno + Numero protocollo 
        $proges_rec['GESGIO'] = 0;                                                          // Giorni scadenza pratica
        $proges_rec['GESSPA'] = 0;                                                          // No per SBT
        $proges_rec['GESNOT'] = "";     // Note Pratica
        $proges_rec['GESOGG'] = $gmi_rec['Lavori'];                                // Oggetto
        $proges_rec['GESTIP'] = "";                                                         // Tipologia
        $proges_rec['GESNOT'] = $gmi_rec['Annotazioni'];                                                         // Annotazioni 
        $proges_rec['GESCODPROC'] = $gmi_rec['Lettera'];         // su codice procedura mettiamo letter apratica 


        $proges_rec['SERIEANNO'] = $gmi_rec['Anno'];         // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $gmi_rec['Numero'];         // Serie NUMERO
        $proges_rec['SERIECODICE'] = $decodlettera;         // Serie PROVENIENZA // TO DO 
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            $this->elaboradatiaggiuntivi($this->Pratica, $gmi_rec, 'PRATICA');
            $this->importaallegati($this->Id, $gmi_rec['GeneraleNumero']);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Record ID " . $gmi_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function importaPaesaggistica() {
        $sql_base = "SELECT
            prpaesaggistica.*,
            protocollo.GeneraleAnno,
            protocollo.GeneraleNumero,
            protocollo.GeneraleLettera,
            protocollo.RiferimentoSUAPAnno,
            protocollo.RiferimentoSUAPNumero,
            protocollo.RiferimentoSUAPLettera,
            protocollo.DataInserimento,
            protocollo.ParticellaAnnotazioni,
            protocollo.IdEdificio,
            protocollo.Via,
            protocollo.Civico
            FROM
            prpaesaggistica,
            protocollo
            WHERE
            prpaesaggistica.Id = protocollo.Id
            ORDER BY prpaesaggistica.Anno ASC
            ";

        $gmi_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql_base, true);
        $i = 0;
        foreach ($gmi_tab as $gmi_rec) {

            if ($gmi_rec['DataInserimento']) {
                $data = $this->cleardate($gmi_rec['DataInserimento']);
                $datarec = $this->convertdate($data);
                $gmi_rec['GESDRE'] = $datarec;
            } else {
                $gmi_rec['GESDRE'] = '18991230';
            }
            if (empty($gmi_rec['GESDRE'])) {
                $gmi_rec['GESDRE'] = '18991230';
            }
            $toinsert = $this->inserisciPaesaggistica($gmi_rec);
            if ($toinsert) {
                $i++;
                //$this->importaallegati($this->Id); // se ci stanno 
                $impresa_rec = $this->importaimpresa($this->Id);
                $tecnico_tab = $this->importatecnico($this->Id);
                $richiedente_tab = $this->importarichiedente($this->Id);
            }
        }
        Out::msgInfo("OK", $i . " Fascicoli paesaggistica caricati");
    }

    private function inserisciPaesaggistica($gmi_rec) {
        $this->Id = $gmi_rec['Id'];  // MI SALVO ID PER RELAZIONI 
        $proges_rec['GESNUM'] = $this->gestGesnum($gmi_rec['Anno'], 'P', " WHERE GESCODPROC = 'PAESAGGISTICA'"); //RICAVO GESNUM PROGRESSIVO PER ANNO
        //$this->Pratica = $gmi_rec['Anno'] . '9' . str_pad($gmi_rec['Numero'], 5, "0", STR_PAD_LEFT);
        $sql = "WHERE Id = " . $gmi_rec['IdRiferimento'];
        $antecedente = $this->cercaantecedente($sql, 'Paesaggistica');
        $this->Pratica = $proges_rec['GESNUM'];
        $proges_rec['GESTSP'] = self::SPORTELLO_DEFAULT; // Sportello Da definire
        if ($gmi_rec['RiferimentoSUAPAnno'] == '2015' or $gmi_rec['RiferimentoSUAPAnno'] == '2016' or $gmi_rec['RiferimentoSUAPAnno'] == '2017') {

            $proges_rec['GESPRA'] = $gmi_rec['RiferimentoSUAPAnno'] . str_pad($gmi_rec['RiferimentoSUAPNumero'], 6, "0", STR_PAD_LEFT); //   Numero Pratica on-line.
            if ($gmi_rec['RiferimentoSUAPLettera'] == 'SUAP') {
                $proges_rec['GESTSP'] = '5'; // Sportello Da definire setto sportello suap se proviene da suap 
            }
            $this->ricavadaticatastali($gmi_rec['ParticellaAnnotazioni']);
        }
        if ($gmi_rec['IdEdificio'] != 0) {
            // FUNZIONE CARICA CATASTO  ['ID EDIFICO']
            $this->importadaticatastali($gmi_rec['IdEdificio']);
        }
        if ($gmi_rec['GeneraleAnno'] && $gmi_rec['GeneraleNumero']) {
            // PROTOCOLLO IL ARRIVO
            $proges_rec['GESPAR'] = 'A';
        }
        $proges_rec['GESPRO'] = self::PROCEDIMENTO_PAESAGGISTICA;                                                   // Provvisorio.
        $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
        $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
        $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato
        $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;                                                   // Resp. Procedimento <- Da valorizzare per sbt
        $proges_rec['GESDRE'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDRI'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESORA'] = "00:00";     // Data registrazione <- ora INSERIMENTO protocollo 
        $proges_rec['GESDCH'] = $gmi_rec['GESDRE'];      // data chiusura fascicolo che è uaguale a quella di apertura    per non lasciarlo aperto
        $proges_rec['GESNPR'] = $gmi_rec['GeneraleAnno'] . $gmi_rec['GeneraleNumero'];                      // Anno + Numero protocollo 
        $proges_rec['GESGIO'] = 0;                                                          // Giorni scadenza pratica
        $proges_rec['GESSPA'] = 0;                                                          // No per SBT
        $proges_rec['GESNOT'] = "";     // Note Pratica
        $proges_rec['GESOGG'] = $gmi_rec['Oggetto'];                                // Oggetto
        $proges_rec['GESTIP'] = "";                                                         // Tipologia
        $proges_rec['GESNOT'] = $gmi_rec['Annotazioni'];                                                         // Annotazioni 
        $proges_rec['GESCODPROC'] = 'PAESAGGISTICA';         // su codice procedura mettiamo letter apratica 
        $proges_rec['GESPRE'] = $antecedente;         // PRATICA ANTECEDENTE 

        $proges_rec['SERIEANNO'] = $gmi_rec['Anno'];         // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $gmi_rec['Numero'];         // Serie NUMERO
        $proges_rec['SERIECODICE'] = '2222';         // Serie PROVENIENZA // TO DO 
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            $this->elaboradatiaggiuntivi($this->Pratica, $gmi_rec, 'PAESAGGISTICA');
            $this->importaallegati($this->Id, $gmi_rec['GeneraleNumero']);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Paesaggistica Record ID " . $gmi_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function importaAgibilita() {
        // DA TENERE IN SOSPESO
        $sql = "SELECT abitabilita.*, prabitabilita.Protocollo AS CHIAVE FROM abitabilita INNER JOIN prabitabilita ON prabitabilita.Abitabilita = abitabilita.Id ORDER BY abitabilita.AnnoDomanda ASC";
        $agibilita_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        $i = 0;
        foreach ($agibilita_tab as $agibilita_rec) {
            if ($gmi_rec['DataAbitabilita']) {
                $data = $this->cleardate($gmi_rec['DataAbitabilita']);
                $datarec = $this->convertdate($data);
                $gmi_rec['GESDRE'] = $datarec;
            } else {
                $gmi_rec['GESDRE'] = '18991230';
            }
            if (empty($gmi_rec['GESDRE'])) {
                $gmi_rec['GESDRE'] = '18991230';
            }
            $toinsert = $this->caricaAgibilita($agibilita_rec);
            if ($toinsert) {
                $i++;
                //$this->importaallegati($this->Id); // se ci sono 
                $impresa_rec = $this->importaimpresa($this->Id);
                $tecnico_tab = $this->importatecnico($this->Id);
                $richiedente_tab = $this->importarichiedente($this->Id);
            }
        }
        Out::msgInfo("Ok", $i . " Fascicoli agibilita' caricati");
    }

    private function caricaAgibilita($gmi_rec) {
        $this->Id = $gmi_rec['ProtocolloDomanda'];  // MI SALVO ID PER RELAZIONI 
//        if (!empty($gmi_rec['LetteraDomanda'])) {
//            $this->Pratica = str_pad($gmi_rec['AnnoDomanda'], 4, "0", STR_PAD_LEFT) . str_pad($gmi_rec['LetteraDomanda'], 2, "0", STR_PAD_LEFT) . str_pad($gmi_rec['NumeroDomanda'], 4, "0", STR_PAD_LEFT);
//        } else {
//            $decodlettera = '00';
//            $this->Pratica = str_pad($gmi_rec['AnnoDomanda'], 4, "0", STR_PAD_LEFT) . '8' . str_pad($gmi_rec['NumeroDomanda'], 5, "0", STR_PAD_LEFT);
//        }

        $proges_rec['GESNUM'] = $this->gestGesnum($gmi_rec['AnnoDomanda'], 'A', " WHERE GESCODPROC = 'AGIBILITA'"); //RICAVO GESNUM PROGRESSIVO PER ANNO
        $sql = "WHERE Id = " . $gmi_rec['CHIAVE'];
        $antecedente = $this->cercaantecedente($sql, 'Agibilita');
        $this->Pratica = $proges_rec['GESNUM'];
        $proges_rec['GESTSP'] = self::SPORTELLO_DEFAULT; // Sportello Da definire
        if ($gmi_rec['RiferimentoSUAPAnno'] == '2015' or $gmi_rec['RiferimentoSUAPAnno'] == '2016' or $gmi_rec['RiferimentoSUAPAnno'] == '2017') {

            $proges_rec['GESPRA'] = $gmi_rec['RiferimentoSUAPAnno'] . str_pad($gmi_rec['RiferimentoSUAPNumero'], 6, "0", STR_PAD_LEFT); //   Numero Pratica on-line.
            if ($gmi_rec['RiferimentoSUAPLettera'] == 'SUAP') {
                $proges_rec['GESTSP'] = '5'; // Sportello Da definire setto sportello suap se proviene da suap 
            }
            // $this->ricavadaticatastali($gmi_rec['ParticellaAnnotazioni']);
        }
//        if ($gmi_rec['IdEdificio'] != 0) {
//            // FUNZIONE CARICA CATASTO  ['ID EDIFICO']
//            // $this->importadaticatastali($gmi_rec['IdEdificio']);
//        }
        if ($gmi_rec['ProtocolloDomanda']) {
            // PROTOCOLLO IL ARRIVO
            $proges_rec['GESPAR'] = 'A';
        }
        $proges_rec['GESPRO'] = self::PROCEDIMENTO_AGIBILITA;                                                   // Provvisorio.
        $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
        $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
        $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato
        $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;                                                  // Resp. Procedimento <- Da valorizzare per sbt
        $proges_rec['GESDRE'] = '18991230';        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDRI'] = '18991230';        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESORA'] = "00:00";     // Data registrazione <- ora INSERIMENTO protocollo 
        $proges_rec['GESDCH'] = '18991230';      // data chiusura fascicolo che è uaguale a quella di apertura    per non lasciarlo aperto
        $proges_rec['GESNPR'] = $gmi_rec['AnnoDomanda'] . $gmi_rec['ProtocolloDomanda'];                      // Anno + Numero protocollo 
        $proges_rec['GESGIO'] = 0;                                                          // Giorni scadenza pratica
        $proges_rec['GESSPA'] = 0;                                                          // No per SBT
        $proges_rec['GESNOT'] = "";     // Note Pratica
        $proges_rec['GESOGG'] = "Richiesta Agibilita'";                                // Oggetto
        $proges_rec['GESTIP'] = "";                                                         // Tipologia
        $proges_rec['GESNOT'] = $gmi_rec['LetteraDomanda'];                                                         // Annotazioni 
        $proges_rec['GESCODPROC'] = 'AGIBILITA';         // su codice procedura mettiamo letter apratica 
        $proges_rec['GESPRE'] = $antecedente;         // PRATICA ANTECEDENTE 

        $proges_rec['SERIEANNO'] = $gmi_rec['AnnoDomanda'];         // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $gmi_rec['NumeroDomanda'];         // Serie NUMERO
        if (!empty($gmi_rec['LetteraDomanda'])) {
            $lettera = self::$SUBJECT_BASE_AGIBILITA[strtoupper($gmi_rec['LetteraDomanda'])];
        } else {
            $lettera = '3333';
        }
        $proges_rec['SERIECODICE'] = $lettera;         // Serie PROVENIENZA // TO DO 

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            $this->elaboradatiaggiuntivi($this->Pratica, $gmi_rec, 'AGIBILITA');
            $this->importaallegati($this->Id, $gmi_rec['ProtocolloDomanda']);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Paesaggistica Record ID " . $gmi_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function cercaantecedente($where, $tipo = '') {
        $sql = "SELECT Numero, Anno, Lettera, Lavori FROM pratica $where";
        $pratica_rec = ItaDB::DBSQLSelect($this->GMI_DB, $sql, false);
        // DA QUESTO SU PROGES TI DEVI PREDERE IL NUMERO DEL FASCICOLO GESNUM
        if (!$pratica_rec) {
            return false;
        }
        if (!empty($pratica_rec['Lettera'])) {
            $lettera = self::$SUBJECT_BASE_LETTERA[strtoupper($pratica_rec['Lettera'])];
        } else {
            $lettera = '1111';
        }
        $sql_antecedente = "SELECT GESNUM, COUNT(GESNUM) AS totale FROM PROGES WHERE SERIEANNO = " . $pratica_rec['Anno'] . " AND SERIEPROGRESSIVO = " . $pratica_rec['Numero'] . " AND SERIECODICE = " . $lettera . " AND GESOGG = '" . addslashes($pratica_rec['Lavori']) . "'";
        $proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_antecedente, false);
        if ($proges_rec['totale'] == 1) {
            return $proges_rec['GESNUM'];
        }
        if ($proges_rec['totale'] > 1) {
            $this->scriviLog("TROVATI PIù RISULTATI PER IMPORTAZIONE FASCICOLO ANTECEDENTE " . $tipo . $proges_rec['GESNUM']);
            return false;
        }
    }

    private function importaimpresa($id) {
        if (!$id) {
            return false;
        }
        $sql = "SELECT primpresa.Impresa, impresa.* FROM primpresa INNER JOIN impresa ON primpresa.Protocollo = $id AND primpresa.Impresa = impresa.Id";
        $impresa_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        if (!$impresa_tab) {
            return false;
        }

        $this->caricaimpresa($impresa_tab, $this->Pratica);
    }

    private function caricaimpresa($impresa_tab, $pratica) {
        if (!$pratica) {
            Out::msgStop("ERRORE", "Nessuna impresa caricata");
            return false;
        }
        foreach ($impresa_tab as $impresa_rec) {

            if ($impresa_rec['DataNascita']) {
                $data = $this->cleardate($impresa_rec['DataNascita']);
                $datarec = $this->convertdate($data);
                $impresa_rec['DESNASDAT'] = $data;
            }
            if ($impresa_rec['RagioneSociale']) {
                $proges_rec['DESRAGSOC'] = $this->caricaragionesociale($impresa_rec['RagioneSociale']);
            }
            if (!empty($impresa_rec['Indirizzo'])) {
                $civico = $this->ricavacivico($impresa_rec['Indirizzo']);
                if ($civico) {
                    $proges_rec['DESCIV'] = $civico;
                } else {
                    $proges_rec['DESCIV'] = "";
                }
            }

            $proges_rec['DESNUM'] = $pratica;
            $proges_rec['DESCOD'] = "";
            $proges_rec['DESNOME'] = $impresa_rec['Nome'];
            $proges_rec['DESCOGNOME'] = $impresa_rec['Cognome'];
            //$proges_rec['DESRAGSOC'] = $impresa_rec['RagioneSociale'];
            //$proges_rec['DESRAGSOC'] = '';
            $proges_rec['DESSESSO'] = "";
            $proges_rec['DESNASCIT'] = $impresa_rec['LuogoNascita'];
            $proges_rec['DESNASPROV'] = "";
            $proges_rec['DESNASNAZ'] = "";
            //$proges_rec['DESNASDAT'] = "";     // nascita data      
            $proges_rec['DESNASDAT'] = $impresa_rec['DESNASDAT'];     // nascita data      
            //$proges_rec['DESNASDAT'] = "";     // nascita data      
            $proges_rec['DESNOM'] = $impresa_rec['Nominativo'];
            $proges_rec['DESEMA'] = $impresa_rec['email'];
            $proges_rec['DESPEC'] = $impresa_rec['Pec'];
            $proges_rec['DESTEL'] = $impresa_rec['Telefono'];
            $proges_rec['DESCEL'] = "";
            $proges_rec['DESFAX'] = "";
            $proges_rec['DESIND'] = $impresa_rec['Indirizzo'];
            //$proges_rec['DESCIV'] = "";
            $proges_rec['DESCAP'] = $impresa_rec['Cap'];
            $proges_rec['DESCIT'] = $impresa_rec['Citta'];
            $proges_rec['DESPRO'] = "";
            $proges_rec['DESNAZ'] = "";
            $proges_rec['DESDAT'] = "";
            $proges_rec['DESDRE'] = "";
            $proges_rec['DESDCH'] = "";
            $proges_rec['DESANN'] = "";
            $proges_rec['DESSON'] = "";
            $proges_rec['DESFIS'] = $impresa_rec['CodiceFiscale'];
            $proges_rec['DESPIVA'] = $impresa_rec['PartitaIva'];
            $proges_rec['DESFISGIU'] = "";
            $proges_rec['DESNATLEGALE'] = "";
            $proges_rec['DESRUO'] = self::DESRUOIMPRESA_DEFAULT;
            $proges_rec['DESPAK'] = "";
            $proges_rec['DESDSET'] = "";
            $proges_rec['DESORDISCRIZIONE'] = "";
            $proges_rec['DESLOC'] = "";
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Impresa ID" . $impresa_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }

        return true;
    }

    private function importatecnico($id) {
        if (!$id) {
            return false;
        }
        $sql = "SELECT prtecnico.Tecnico, tecnico.* FROM prtecnico INNER JOIN tecnico ON prtecnico.Tecnico = tecnico.Id WHERE prtecnico.Protocollo = $id";

        $tecnico_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        if (!$tecnico_tab) {
            return false;
        }
        $this->caricatecnico($tecnico_tab, $this->Pratica);
    }

    private function caricatecnico($tecnico_tab, $pratica) {
        if (!$pratica) {
            Out::msgStop("ERRORE", "Nessuna tecnico caricato");
            return false;
        }
        foreach ($tecnico_tab as $tecnico_rec) {
            if ($tecnico_rec['DataNascita']) {
                $data = $this->cleardate($tecnico_rec['DataNascita']);
                $datarec = $this->convertdate($data);
                $tecnico_rec['DESNASDAT'] = $data;
            }
            if (!empty($tecnico_rec['Indirizzo'])) {
                $civico = $this->ricavacivico($tecnico_rec['Indirizzo']);
                if ($civico) {
                    $proges_rec['DESCIV'] = $civico;
                } else {
                    $proges_rec['DESCIV'] = "";
                }
            }
            $proges_rec['DESNUM'] = $pratica;
            $proges_rec['DESCOD'] = "";
            $proges_rec['DESNOME'] = $tecnico_rec['Nome'];
            $proges_rec['DESCOGNOME'] = $tecnico_rec['Cognome'];
            //$proges_rec['DESRAGSOC'] = $tecnico_rec['RagioneSociale'];
            // $proges_rec['DESRAGSOC'] = '';
            $proges_rec['DESSESSO'] = "";
            $proges_rec['DESNASNAZ'] = "";
            $proges_rec['DESNASPROV'] = "";
            $proges_rec['DESNASCIT'] = $tecnico_rec['LuogoNascita'];
            //$proges_rec['DESNASDAT'] = "";     // nascita data      
            $proges_rec['DESNASDAT'] = $tecnico_rec['DESNASDAT'];     // nascita data    
            $proges_rec['DESNOM'] = $tecnico_rec['Nominativo'];
            $proges_rec['DESEMA'] = $tecnico_rec['EmailPEC'];
            $proges_rec['DESPEC'] = $tecnico_rec['Pec'];
            $proges_rec['DESTEL'] = $tecnico_rec['Telefono'];
            $proges_rec['DESCEL'] = "";
            $proges_rec['DESFAX'] = "";
            $proges_rec['DESIND'] = $tecnico_rec['Indirizzo'];
            //$proges_rec['DESCIV'] = "";
            $proges_rec['DESCAP'] = $tecnico_rec['Cap'];
            $proges_rec['DESCIT'] = $tecnico_rec['Citta'];
            $proges_rec['DESPRO'] = "";
            $proges_rec['DESNAZ'] = "";
            $proges_rec['DESDAT'] = "";
            $proges_rec['DESDRE'] = "";
            $proges_rec['DESDCH'] = "";
            $proges_rec['DESANN'] = "";
            $proges_rec['DESSON'] = "";
            $proges_rec['DESFIS'] = $tecnico_rec['CodiceFiscale'];
            $proges_rec['DESPIVA'] = $tecnico_rec['PartitaIva'];
            $proges_rec['DESFISGIU'] = "";
            $proges_rec['DESNATLEGALE'] = "";
            $proges_rec['DESRUO'] = self::DESRUOTECNICO_DEFAULT;
            $proges_rec['DESPAK'] = "";
            $proges_rec['DESDSET'] = "";
            $proges_rec['DESORDISCRIZIONE'] = "";
            $proges_rec['DESLOC'] = "";
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Tecnico ID" . $tecnico_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }

        return true;
    }

    private function importarichiedente($id) {
        if (!$id) {
            return false;
        }
        $sql = "SELECT prrichiedente.Richiedente, richiedente.* FROM prrichiedente INNER JOIN richiedente ON prrichiedente.Richiedente = richiedente.Id WHERE prrichiedente.Protocollo = $id";

        $richiedente_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        if (!$richiedente_tab) {
            return false;
        }
        $this->caricarichiedente($richiedente_tab, $this->Pratica);
    }

    private function caricarichiedente($richiedente_tab, $pratica) {
        // DICHIARANTE
        if (!$pratica) {
            Out::msgStop("ERRORE", "Nessuna tecnico caricato");
            return false;
        }
        foreach ($richiedente_tab as $richiedente_rec) {
            if ($richiedente_rec['DataNascita']) {
                $data = $this->cleardate($richiedente_rec['DataNascita']);
                $datarec = $this->convertdate($data);
                $richiedente_rec['DESNASDAT'] = $data;
            }
            if ($richiedente_rec['RagioneSociale']) {
                $proges_rec['DESRAGSOC'] = $this->caricaragionesociale($richiedente_rec['RagioneSociale']);
            }
            if (!empty($richiedente_rec['Indirizzo'])) {
                $civico = $this->ricavacivico($richiedente_rec['Indirizzo']);
                if ($civico) {
                    $proges_rec['DESCIV'] = $civico;
                } else {
                    $proges_rec['DESCIV'] = "";
                }
            }

            $proges_rec['DESNUM'] = $pratica;
            $proges_rec['DESCOD'] = "";
            $proges_rec['DESNOME'] = $richiedente_rec['Nome'];
            $proges_rec['DESCOGNOME'] = $richiedente_rec['Cognome'];
            //$proges_rec['DESRAGSOC'] = '';
            //$proges_rec['DESRAGSOC'] = $richiedente_rec['RagioneSociale'];
            $proges_rec['DESSESSO'] = "";
            $proges_rec['DESNASNAZ'] = "";
            $proges_rec['DESNASPROV'] = "";
            $proges_rec['DESNASCIT'] = $richiedente_rec['LuogoNascita'];
            //$proges_rec['DESNASDAT'] = "";     // nascita data      
            $proges_rec['DESNASDAT'] = $richiedente_rec['DESNASDAT'];     // nascita data   
            $proges_rec['DESNOM'] = $richiedente_rec['Nominativo'];
            $proges_rec['DESEMA'] = $richiedente_rec['EmailPEC'];
            $proges_rec['DESPEC'] = $richiedente_rec['Pec'];
            $proges_rec['DESTEL'] = $richiedente_rec['Telefono'];
            $proges_rec['DESCEL'] = "";
            $proges_rec['DESFAX'] = "";
            $proges_rec['DESIND'] = $richiedente_rec['Indirizzo'];
            // $proges_rec['DESCIV'] = "";
            $proges_rec['DESCAP'] = $richiedente_rec['Cap'];
            $proges_rec['DESCIT'] = $richiedente_rec['Citta'];
            $proges_rec['DESPRO'] = "";
            $proges_rec['DESNAZ'] = "";
            $proges_rec['DESDAT'] = "";
            $proges_rec['DESDRE'] = "";
            $proges_rec['DESDCH'] = "";
            $proges_rec['DESANN'] = "";
            $proges_rec['DESSON'] = "";
            $proges_rec['DESFIS'] = $richiedente_rec['CodiceFiscale'];
            $proges_rec['DESPIVA'] = $richiedente_rec['PartitaIva'];
            $proges_rec['DESFISGIU'] = "";
            $proges_rec['DESNATLEGALE'] = "";
            $proges_rec['DESRUO'] = self::DESRUORICHIEDENTE_DEFAULT;
            $proges_rec['DESPAK'] = "";
            $proges_rec['DESDSET'] = "";
            $proges_rec['DESORDISCRIZIONE'] = "";
            $proges_rec['DESLOC'] = "";
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Richiedente ID" . $richiedente_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }

        return true;
    }

    private function importaallegati($id, $prot) {
        if (!$id) {
            return false;
        }
        $sql = "SELECT * FROM documento WHERE IdProtocollo = '$id'";
        $allegati_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        if (!$allegati_tab) {
            return false;
        }
        $count = 0;
        foreach ($allegati_tab as $allegati_rec) {
            $proges_rec['PASKEY'] = $this->Pratica;
            $extension = pathinfo($allegati_rec['NomeFisicoFile'], PATHINFO_EXTENSION);
            if ($extension == 'p7m') {
                $rand = md5(uniqid()) . '.pdf.' . pathinfo($allegati_rec['NomeFisicoFile'], PATHINFO_EXTENSION);
            } else {
                $rand = md5(uniqid()) . '.' . pathinfo($allegati_rec['NomeFisicoFile'], PATHINFO_EXTENSION);
            }
            $proges_rec['PASFIL'] = $rand;
            $proges_rec['PASMIGRA'] = $allegati_rec['NomeFisicoFile']; // nome fisico file 
            //
            //$proges_rec['PASNOT'] = "File originale " . $allegati_rec['NomeOriginale'];
            $proges_rec['PASCLA'] = "GENERALE";
            $proges_rec['PASNAME'] = $allegati_rec['NomeOriginale'];  // nome passo

            if ($allegati_rec['NumeroProtocollo'] != $prot && !empty($allegati_rec['NumeroProtocollo'])) {
                $propak = $this->creapassoallegati($this->Pratica, $allegati_rec);   // MI RESTITUISCE IL PROPAK PER L'ALLEGATO DLE PASOS E IL ROWID DI PRACOM PER TIPO COMUNICAZIONE
                if (empty($propak)) {
                    $testo = "Fatal: Errore F creapasso allegati return propak Record Allegato ID" . $allegati_rec['Id'] . " " . $ex->getMessage();
                    $this->scriviLog($testo);
                    return false;
                }
                $count++;
                //$proges_rec['PASPRTCLASS'] = 'PRACOM';
                $proges_rec['PASKEY'] = $propak;
                $proges_rec['PASCLA'] = 'ESTERNO';
                //$proges_rec['PASPRTROWID'] = $pasdoc_param['PRACOM_ROWID'];
                $proges_rec['PASSHA2'] = '2';
            }
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Allegato ID" . $allegati_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        if ($count > 0) {
            $this->ordinaPassi($this->Pratica);
        }
        return true;
    }

    private function getPracom($pratica, $propak, $prot, $data, $tipo) {
        $pracom_rec = array(
            // "ROWID" => $this->rowidPracom,
            "COMNUM" => $this->Pratica,
            "COMPAK" => $propak,
            "COMPRT" => $prot,
            "COMDPR" => $data,
            "COMTIP" => $tipo
        );

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRACOM', 'ROWID', $pracom_rec);
            return true;
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione Record PRACOM pratica" . $this->Pratica . " ID : " . $this->Id . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
    }

    private function importadaticatastali($id) {
        $sql = "SELECT Foglio, Particella, Subalterno FROM catasto WHERE IdEdificio = $id";

        $catasto_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql, true);
        if (!$catasto_tab) {
            return false;
        }
        foreach ($catasto_tab as $catasto_rec) {
            // strlen($string) conta caratteri stringa per non trocarle 
            $note = '';
            foreach ($catasto_rec as $key => $valore) {
                if ($key != 'Particella' && strlen($valore) > 4) {
                    $note .= $key . ': ' . $valore . ' ';
                }
                if ($key == 'Particella' && strlen($valore) > 5) {
                    $note .= $key . ': ' . $valore . ' ';
                }
            }
            $daticatastali = array(
                'PRONUM' => $this->Pratica,
                'FOGLIO' => str_pad($catasto_rec['Foglio'], 4, "0", STR_PAD_LEFT),
                'PARTICELLA' => str_pad($catasto_rec['Particella'], 5, "0", STR_PAD_LEFT),
                'SUBALTERNO' => str_pad($catasto_rec['Subalterno'], 4, "0", STR_PAD_LEFT),
                'NOTE' => $note
            );

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PRAIMM', 'ROWID', $daticatastali);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Dati Catastali per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }

        return true;
    }

    private function ricavadaticatastali($immobile_rec) {
        // per i fascifcoli che hanno i dati catastali su annotazione protocollo
        if (!$immobile_rec) {
            return;
        }
        $appoggio = explode("|", $immobile_rec);
        list($scarto, $f) = explode("F= ", $appoggio[0]);
        list($scarto, $p) = explode("M= ", $appoggio[1]);
        list($scarto, $s) = explode("Sub= ", $appoggio[2]);
        // list ($scarto, $Foglio) = explode("|M=", $dati); 
        $daticatastali = array(
            'PRONUM' => $this->Pratica,
            'FOGLIO' => str_pad($f, 4, "0", STR_PAD_LEFT),
            'PARTICELLA' => str_pad($p, 5, "0", STR_PAD_LEFT),
            'SUBALTERNO' => str_pad($s, 4, "0", STR_PAD_LEFT)
        );
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRAIMM', 'ROWID', $daticatastali);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Record Dati Catastali per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function creapasso($Pratica, $tipo = '', $responsabile = self::RESPONSABILE_DEFAULT, $prodpt = '', $dati = '') {
        $data_1 = $this->cleardate($dati['DATA']);
        if (!$data_1) {
            $data = '';
        } else {
            $data = $this->convertdate($data_1);
        }
        $seq = 0;
        $propak = $this->praLib->PropakGenerator($Pratica);
        $passo = array(
            'PRONUM' => $Pratica,
            'PROSEQ' => $seq,
            'PROPRO' => self::PROCEDIMENTO_DEFAULT,
            'PRORPA' => $responsabile,
            'PROANN' => $dati['ESTREMI'],
            'PRODPA' => $dati['DESCRIZIONE'], // nome passo
            'PROINI' => $data, // data apertura passo
            'PROFIN' => $data, // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => $tipo, // tipo passo
            'PRODTP' => $prodpt, // descrizione tipo passo  
        );
        if (!empty($prodpt)) {
            $passo['PROCLT'] = ''; // tipo passo
        }
        if ($tipo == 'PDC' || $tipo == 'VERBALE') {
            $passo['PRODOCTIPREG'] = self::$SUBJECT_BASE_TIPOPASSO[$tipo];         // tipologia codumento rilasciato
            $passo['PRODOCPROG'] = $dati['NUMERO'];         //numero  ex v3
            $data_1 = $this->cleardate($dati['NUMERODATA']);
            $passo['PROCLT'] = ''; // svuoto tipo passo
            if (!$data_1 && $tipo != 'FINELAVORI') {
                $data = '';
                $y = '';
            } else {
                $data = $this->convertdate($data_1);
                list($nuemroanno, $scarto) = explode(" ", $data_1);
                list($d, $m, $y) = explode("/", $nuemroanno);
            }
            $passo['PRODOCANNO'] = $y;         //numero  anno
            $passo['PRODOCINIVAL'] = $data;         //numero progressivo anno
            if (!empty($dati['LETTERAATTO'])) {
                $passo['PROANN'] = "LETTERA ATTO: " . $dati['LETTERAATTO'];
            }
        }
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
            return true;
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
    }

    private function creapassoallegati($Pratica, $allegato, $responsabile = self::RESPONSABILE_DEFAULT) {

        $data_1 = $this->cleardate($allegato['DataProtocollo']);
        if (!$data_1) {
            $data = '';
            $anno = '';
        } else {
            $data = $this->convertdate($data_1);
            $anno = substr($data, 0, 4);
        }
        if ($allegato['Direzione'] == 'U') {
            $allegato['Direzione'] = 'P';
        }
        // da sistemare non fa l'inserte se il passo fia esiste      TO DO
        $sql = "SELECT COMPAK FROM PRACOM WHERE COMNUM = '$Pratica' AND COMPRT = '" . $anno . $allegato['NumeroProtocollo'] . "'";
        $pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        if ($pracom_rec) {
            return $pracom_rec['COMPAK'];
        }
        $seq = 0;
        $propak = $this->praLib->PropakGenerator($Pratica);
        $passo = array(
            'PRONUM' => $Pratica,
            'PROSEQ' => $seq,
            'PROPRO' => self::PROCEDIMENTO_DEFAULT,
            'PRORPA' => $responsabile,
            'PRODPA' => 'COMUNICAZIONE IN ' . self::$SUBJECT_PROT_COMUNICAZIONE[$allegato['Direzione']] . ' PROT ' . $allegato['NumeroProtocollo'], // nome passo
            'PROINI' => $data, // data apertura passo
            'PROFIN' => $data, // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => '000004', // tipo passo 
            'PRODTP' => 'Comunicazione', // descrizione tipo passo  
        );
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
            $this->getPracom($this->Pratica, $propak, $anno . $allegato['NumeroProtocollo'], $data, $allegato['Direzione']);
            return $propak;
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
    }

    private function ordinaPassi($Pratica) {
        $Propas_tab = $this->praLib->getPropasTab($Pratica, 'PROINI ASC');
        $new_seq = 0;
        if ($Propas_tab) {
            foreach ($Propas_tab as $Propas_rec) {
                $new_seq += 10;
                $Propas_rec['PROSEQ'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->PRAM_DB, "PROPAS", "ROWID", $Propas_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($exc->getMessage());
                    return false;
                }
            }
        }
    }

    private function getpropak($Pratica, $testo, $datiaggiuntivi) {
        $sql = "SELECT PROPAK FROM PROPAS WHERE PRONUM = $Pratica AND PRODPA = '" . $testo . "'";

        if ($propak = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false)) {
            //Out::msgInfo("propak", print_r($propak, true));
            $this->elaboradatiaggiuntivi($this->Pratica, $datiaggiuntivi, $testo, $propak['PROPAK']);
        }
    }

    private function elaboradatiaggiuntivi($Pratica, $valori, $tipo = '', $propak = '') {
        foreach ($valori as $key => $datoaggiuntivo) {
            if ($key == 'GeneraleAnno') {
                break;  // PER NON CARICARE I DATI DEL PROTOCOLLO CONTENUTI NELLO STESSO ARRAY
            }
            if ($key == 'Id' || $key == 'CHIAVE') {
                continue;
            }

            if ($datoaggiuntivo == '30/12/1899 00:00:00') {
                $datoaggiuntivo = ''; // PER NON FAR CARICARE DATI AGGIUNTIVI CON NESSUN VALORE ACQUISITO
            }
            if (strstr($datoaggiuntivo, ' 00:00:00')) {
                list ($appoggio_data, $ora) = explode(" ", $datoaggiuntivo);
                $datoaggiuntivo = $appoggio_data;
            }
            if (strstr($key, 'Data') || $key == 'DecorrenzaIstruttore') {
                $appoggio_data = $this->convertdate($datoaggiuntivo);
                $datoaggiuntivo = $appoggio_data;
            }
            $dati = array(
                'DAGNUM' => $Pratica,
                'DAGDES' => $key,
                'DAGLAB' => $key,
                'DAGKEY' => $tipo . '_' . strtoupper($key),
                'DAGVAL' => $datoaggiuntivo,
                'DAGSET' => $Pratica,
                'DAGPAK' => $Pratica
            );
            if ($key == 'Lavori' || $key == 'Oggetto' || strstr($key, 'Testo') || strstr($key, 'Annotazioni')) {
                $dati['DAGTIC'] = 'TextArea'; // TEXT AREA
            }
            if (strstr($key, 'Data') || $key == 'DecorrenzaIstruttore') {
                $dati['DAGTIC'] = 'Data';
            }
            if ($tipo == 'Fine Lavori') {
                $dati['DAGKEY'] = 'FINELAVORI_' . strtoupper($key);
                $dati['DAGPAK'] = $propak;
                $dati['DAGSET'] = $propak . '_01';
            }
            $this->caricadatiaggiuntivi($dati);
        }
        return;
    }

    private function caricadatiaggiuntivi($dati) {
        /*
         * CARICA I DATI AGGIUNTIVI DI PRATICA
         */
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRODAG', 'ROWID', $dati);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Record dati aggiuntivi per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
        }
    }

    private function cleardate($data) {
        /*
         * PULISCE LE DATE SPORCHE NEL DB
         */
        if ($data == '30/12/1899 00:00:00') {
            unset($data);
            return $data;
        } else {
            return $data;
        }
    }

    private function convertdate($data, $tipo = '') {
        // SEPARA DATA DA ORARIO 
        if (!$data) {
            return false;
        }
        list ($datainizio, $ora) = explode(" ", $data);
        list ($d, $m, $y) = explode("/", $datainizio);
        $data_rec = $y . str_pad($m, 2, "0", STR_PAD_LEFT) . str_pad($d, 2, "0", STR_PAD_LEFT);  // FORMATTA DATA
        if ($tipo == 'ALLINEA') {
            $data_rec = str_pad($d, 2, "0", STR_PAD_LEFT) . '/' . str_pad($m, 2, "0", STR_PAD_LEFT) . '/' . $y;
        }
        return $data_rec;
    }

    private function caricaragionesociale($cod) {
        if (!$cod) {
            return $ragionesociale = '';
        }
        return self::$SUBJECT_BASE_RAGIONESOCIALE[$cod];
    }

    private function caricalocalizzazioneintervento($Pratica, $codvia, $civico) {
        $sql = "SELECT Toponimo, DescrizioneSecondaria, DescrizionePrincipale FROM via WHERE Id = $codvia";
        $via_rec = ItaDB::DBSQLSelect($this->GMI_DB, $sql, false);
        if (!$via_rec) {
            return false;
        }
        $localita_rec = array(
            'DESNUM' => $Pratica,
            'DESIND' => $via_rec['Toponimo'] . ' ' . $via_rec['DescrizioneSecondaria'] . ' ' . $via_rec['DescrizionePrincipale'],
            'DESCIV' => $civico,
            'DESRUO' => '0014'
        );
        $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_rec, $insert_Info);
        return;
    }

    private function importaspese($Pratica, $contributocostruzuine = '', $urbanizzazioneprimaria = '', $urbanizzazionesecondaria = '') {
        if (!empty($contributocostruzuine)) {
            $costi['contributocostruzuine'] = array(
                'IMPONUM' => $Pratica, // numero pratica
                'IMPOCOD' => 1, // tipo pagamento
                'IMPORTO' => $contributocostruzuine,
                'PAGATO' => $contributocostruzuine  // segnato come saldato per l'intera cifra
            );
        }
        if (!empty($urbanizzazioneprimaria)) {
            $costi['urbanizzazioneprimaria'] = array(
                'IMPONUM' => $Pratica, // numero pratica
                'IMPOCOD' => 2, // tipo pagamento
                'IMPORTO' => $urbanizzazioneprimaria,
                'PAGATO' => $urbanizzazioneprimaria  // segnato come saldato per l'intera cifra
            );
        }
        if (!empty($urbanizzazionesecondaria)) {
            $costi['urbanizzazionesecondaria'] = array(
                'IMPONUM' => $Pratica, // numero pratica
                'IMPOCOD' => 3, // tipo pagamento
                'IMPORTO' => $urbanizzazionesecondaria,
                'PAGATO' => $urbanizzazionesecondaria  // segnato come saldato per l'intera cifra
            );
        }
        $i = 0;
        foreach ($costi as $costo) {
            $i++;
            $costo['IMPOPROG'] = $i;
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROIMPO', 'ROWID', $costo);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore Inserimento costo per fascicolo N " . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
            }
        }
    }

    private function importafinelavori($id) {
        //$sql = "SELECT Protocollo, ProtocolloDomanda, DataFineLavoro FROM finelavoro WHERE ID = $id ";
        $sql = "SELECT * FROM finelavoro WHERE ID = $id ";
        $finelavori_rec = ItaDB::DBSQLSelect($this->GMI_DB, $sql, false);
        if (!$finelavori_rec) {
            return false;
        }
        return $finelavori_rec;
    }

    public function gestGesnum($anno) {
        if (isset($this->gesnum_anno[$anno])) {
            ++$this->gesnum_anno[$anno];
            return $anno . str_pad($this->gesnum_anno[$anno], 6, "0", STR_PAD_LEFT);
        }
        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(GESNUM) AS ULTIMO FROM PROGES WHERE SERIEANNO = '$anno'", false);

        $progressivo = (int) substr($Proges_rec['ULTIMO'], 4, 10);
        $this->gesnum_anno[$anno] = ++$progressivo;
        return $anno . str_pad($this->gesnum_anno[$anno], 6, "0", STR_PAD_LEFT);
        ;
    }

//OLD FUNZIONE
//    public function gestGesnum($anno, $tipo = '', $where = '') {
//        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(GESNUM) AS ULTIMO FROM PROGES" . $where, false);
//        if (substr($Proges_rec['ULTIMO'], 0, 4) != $anno) {
//            $progressivo = 0;
//            if ($tipo == 'A') {
//                // agibilita'
//                $appoggio = $progressivo + '100000';
//                $progressivo = $appoggio;
//            }
//            if ($tipo == 'P') {
//                // paesaggistica
//                $appoggio = $progressivo + '200000';
//                $progressivo = $appoggio;
//            }
//        } else {
//            $progressivo = (substr($Proges_rec['ULTIMO'], 4, 10));
//        }
//        return $anno . str_pad($progressivo + 1, 6, "0", STR_PAD_LEFT);
//    }

    private function ricavacivico($stringa) {
        $i = 4;
        while ($i > 0) {
            $da = '-' . $i;
            $result = substr($stringa, $da, $i);
            $controllo = intval($result);
            if ($controllo != 0) {
                $i = 0;
                return $controllo;
            } else {
                --$i;
            }
        }
        return false;
    }

    public function setdirectoryAll() {
        $sql = "SELECT * FROM PASDOC WHERE ROWID BETWEEN '670' AND '698'";
        $doc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $Origin_path = self::PATH_ALLEGATI; // path fissa di dove si trova la directory per arricvare agli allegati
        $i = 0;
        foreach ($doc_tab as $doc_rec) {

            //IF CHE VEDE LA LUNGHEZZA DEL PASKEY  PER CAPIRE SE E' UN ALLEGATO DI PRATICA O FI PASSO 
            if (strlen($doc_rec['PASKEY']) > '10') {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY']); // ALLEGATI DI PASSO 
            } else {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY'], 'PROGES');  // ALLEGATI DI PRATICA
            }

            $pathfile = $doc_rec['PASMIGRA'];

            if (file_exists($Origin_path . '/' . $pathfile)) {
                if (!@copy($Origin_path . '/' . $pathfile, $pratPath . '/' . $doc_rec['PASFIL'])) {
                    $this->scriviLog("Errore copia allegato" . $Origin_path . '/' . $pathfile);
                    continue;
                }
            } else {
                $this->scriviLog("Allegato non trovato" . $Origin_path . '/' . $doc_rec['PASMIGRA']);
                continue;
            }
            ++$i;
        }
        Out::msgInfo(" ", "N°$i Allegati copiati correttamente");
        return true;
    }

    private function importaCDU() {
        $sql_cdu = "SELECT
            cdu.*,
            protocollo.GeneraleAnno,
            protocollo.GeneraleNumero,
            protocollo.DataGenerale
            FROM
            cdu,
            protocollo
            WHERE
            cdu.Id = protocollo.Id
            ORDER BY cdu.Anno ASC";
        $gmi_tab = ItaDB::DBSQLSelect($this->GMI_DB, $sql_cdu, true);
        $i = 0;
        foreach ($gmi_tab as $gmi_rec) {

            $proges_rec['GESNUM'] = $this->gestGesnum($gmi_rec['Anno']);
            $this->Id = $gmi_rec['Id'];
            $proges_rec['GESPRO'] = self::CDU_PROCEDIMENTO;
            $this->Pratica = $proges_rec['GESNUM'];
            $proges_rec['GESTSP'] = ''; // SPORTRELLO DI PROVENIENZA NESSUNO
            $data = $this->cleardate($gmi_rec['DataGenerale']);
            $gmi_rec['GESDRE'] = $this->convertdate($data);
            if (empty($gmi_rec['GESDRE'])) {
                $gmi_rec['GESDRE'] = '18991230';   // se la data è nuova steto data fittizia
            }
            // DataGenerale    data protocollo
            $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
            $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
            $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato
            $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;                                                   // Resp. Procedimento <- Da valorizzare per sbt
            $proges_rec['GESDRE'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
            $proges_rec['GESDRI'] = $gmi_rec['GESDRE'];        // Data registrazione = DATA INIZIO protocollo
            $proges_rec['GESORA'] = "00:00";     // Data registrazione <- ora INSERIMENTO protocollo 
            $proges_rec['GESDCH'] = $gmi_rec['GESDRE'];      // data chiusura fascicolo che è uaguale a quella di apertura    per non lasciarlo aperto
            $proges_rec['GESNPR'] = $gmi_rec['GeneraleAnno'] . $gmi_rec['GeneraleNumero'];                      // Anno + Numero protocollo                                                    
            $proges_rec['GESNOT'] = "";     // Note Pratica
            $proges_rec['GESOGG'] = "";   // Oggetto domanda
            $proges_rec['GESTIP'] = "";                                                         // Tipologia


            $proges_rec['SERIEANNO'] = $gmi_rec['Anno'];           // Serie ANNO
            $proges_rec['SERIEPROGRESSIVO'] = $gmi_rec['Numero'];  // Serie NUMERO
            $proges_rec['SERIECODICE'] = self::CDU_SERIE;         // Serie PROVENIENZA // TO DO 
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
                // $this->elaboradatiaggiuntivi($this->Pratica, $gmi_rec, 'PRATICA');
                $this->importaallegati($this->Id, $gmi_rec['GeneraleNumero']);
                $this->importarichiedente($this->Id);
                ++$i;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Fascicolo CDU Record ID " . $gmi_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
            // return true;
        }
        Out::msgInfo("OK", $i . " Fascicoli CDU caricati");
        return true;
    }

}

/*
  TRUNCATE `PROGES`;
  TRUNCATE `PROPAS`;
  TRUNCATE `ANADES`;
  TRUNCATE `PASDOC`;
  TRUNCATE `PRAIMM`;
  TRUNCATE `PRODAG`;
  TRUNCATE `PROIMPO`;
  TRUNCATE `PRACOM`;
 */
?>
