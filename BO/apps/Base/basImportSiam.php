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

function basImportSiam() {
    $basImportSiam = new basImportSiam();
    $basImportSiam->parseEvent();
    return;
}

class basImportSiam extends itaModel {

    // const SPORTELLO_DEFAULT = 6;
    static $SUBJECT_AN_RECAPITO = array(
        "1415932" => "EMAIL",
        "1415933" => "TELEFONO",
        "1415934" => "FAX",
        "1415935" => "CELL",
        "1415936" => "PEC",
        "1415937" => "SITO"
    );
    static $SUBJECT_AN_MANSIONE = array(
        "1321469" => "0001", // REFERENTE PER LA PRATICA //RP
        "1299476" => "0014", //LEGALE RAPPRESENTANTE //MAN2
        "1321471" => "0007", //RESPONSABILE TECNICO //RT
        "1299479" => "9001", //DIRETTORE STABILIMENTO // MAN4
        "1299477" => "9002", //TITOLARE  // MAN2
        "1299480" => "9003", //PRESIDENTE PRO TEMPORE //MAN5
        "1299481" => "9004", //AMMINISTRATORE  //MAN6
        "1299478" => "9005", //AMMINISTRATORE UNICO // MAN3
    );
    static $ITALWEB_TAB = array('ANA_SOGGETTI', 'ANA_RECAPITISOGGETTI', 'ANA_RUOLISOGGETTI');
    public $praLib;
    public $ITALWEB_DB;
    public $SIAM_DB;
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
            $this->nameForm = 'basImportSiam';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');

            $this->praLib = new praLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->SIAM_DB = ItaDB::DBOpen('mc_siam', "");
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
                $this->fileLog = sys_get_temp_dir() . "/basImportSiam_" . time() . ".log";
                $this->scriviLog("Avvio Programma basImportSiam");
                $this->setAna_comuni();
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->importasoggettiImp();
                        $this->importaEnti();
                        break;
                    case $this->nameForm . '_ConfermaRelazioni':
                        $this->importaRelazioni();
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::msgQuestion("Svuota Tabelle", "Sei sicuro di vole svuotare le tabelle  - " . print_r(self::$ITALWEB_TAB, true) . " del DB " . print_r($this->ITALWEB_DB, true), array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAllegati', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-ConfermaSvuota' => array('id' => $this->nameForm . '_ConfermaSvuota', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaSvuota':
                        $this->SvuotaDB(self::$ITALWEB_TAB);
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

    private function SvuotaDB($truncate) {
        foreach ($truncate as $tab) {
            $sql = "TRUNCATE " . $tab;
            ItaDB::DBSQLExec($this->ITALWEB_DB, $sql);
        }
        Out::msgInfo('OK', 'Tabelle svuotate');
    }

    private function importaEnti() {
        /*
         * lettura db sorgente
         */

        $sql_base = "SELECT * FROM `an_ente`";
        $soggetti_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql_base, true);
        $i = 0;
        foreach ($soggetti_tab as $soggetto_rec) {
            $i++;
            $rowid = $this->inserisciSoggettoEnte($soggetto_rec);
            $this->ricavaRecapiti($rowid, $soggetto_rec['id'], $soggetto_rec['codfis']);
        }
        Out::msgInfo("OK", $i . " Soggetti Caricati an_ente ");
        return true;
    }

    private function importasoggettiImp() {
        /*
         * lettura db sorgente
         */

        $sql_base = "SELECT * FROM `an_soggettoimpresa`";
        // $sql_base .= " WHERE id = 1480889";
        // $sql_base = " ORDER BY id LIMIT 5";
        //Out::msgInfo('tttt', print_r($gmi_tab,true));
        $soggetti_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql_base, true);
        $i = 0;
        foreach ($soggetti_tab as $soggetto_rec) {
            $i++;
            $rowid = $this->inserisciSoggetto($soggetto_rec);
            $this->ricavaRecapiti($rowid, $soggetto_rec['id'], $soggetto_rec['codfis']);
        }
        Out::msgInfo("OK", $i . " Soggetti Caricati an_soggettoimpresa");
        return true;
    }

    private function inserisciSoggetto($soggetto_rec) {

        $ana_soggetti = array();
        $this->Id = $soggetto_rec['id'];

        $ana_soggetti['COGNOME'] = $soggetto_rec['RAGSOC'];
        $ana_soggetti['NOME'] = $soggetto_rec['NOME'];
//        if (strstr($soggetto_rec['NOMECOMPLETO'], $ana_soggetti['NOME']) > 0 && !$soggetto_rec['RAGSOC']) {
//             $ana_soggetti['COGNOME'] = str_replace($ana_soggetti['NOME'], '', $soggetto_rec['NOMECOMPLETO']);
//        NOME COMPLETO E' LA CONCATENAZIONE DI RAGSOC E NOME    
//        }

        $ana_soggetti['CF'] = $soggetto_rec['codfis'];
        $ana_soggetti['PIVA'] = $soggetto_rec['piva'];

        if ($soggetto_rec['tipo'] == 'S') {
            $ana_soggetti['NATGIU'] = 0;  //0 fisica   1giurifica
        } elseif ($soggetto_rec['tipo'] == 'I') {
            $ana_soggetti['NATGIU'] = 1;
        }

        $comune_rec = $this->ricavacomune($soggetto_rec['CODPROVNASCITA'], $soggetto_rec['CODCOMUNENASCITA']);
        $ana_soggetti['DATANASCITA'] = $this->convertdate($soggetto_rec['DATANASCITA']); // giradata
        $ana_soggetti['CITTANASCITA'] = $comune_rec['COMUNE'];
        $ana_soggetti['PROVNASCITA'] = $comune_rec['PROVINCIA'];

        // DATI RESIDENZA
        $ana_soggetti['CITTARESI'] = '';
        $ana_soggetti['DESCRIZIONEVIA'] = '';
        $ana_soggetti['CIVICO'] = '';
        $ana_soggetti['PROVRESI'] = '';
        $ana_soggetti['CAPRESI'] = '';


        $ana_soggetti['CODANAG'] = '';



        $ana_soggetti['UTENTEINSERIMENTO'] = $soggetto_rec['UTENTECREAZIONE'];
        $ana_soggetti['DATAINSERIMENTO'] = $this->convertdate($soggetto_rec['DATACREAZIONE']);
        $ana_soggetti['ORAINSERIMENTO'] = 'Impo';
        $ana_soggetti['FONTEDATI'] = $soggetto_rec['fonte'];

        $ana_soggetti['UTENTEAGGIORNAMENTO'] = $soggetto_rec['utenteultmod'];
        $ana_soggetti['DATAAGGIORNAMENTO'] = $this->convertdate($soggetto_rec['dataultmod']);




        try {
            ItaDB::DBInsert($this->ITALWEB_DB, 'ANA_SOGGETTI', 'ROWID', $ana_soggetti);
            $eventId = ItaDB::DBLastId($this->ITALWEB_DB);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in ana_soggetti ID " . $ana_soggetti['id'] . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return $eventId;
    }

    private function inserisciSoggettoEnte($soggetto_rec) {

        $ana_soggetti = array();
        $this->Id = $soggetto_rec['id'];

        $ana_soggetti['COGNOME'] = $soggetto_rec['descrizione'];
        if ($soggetto_rec['DESCRIZIONE2'] && $soggetto_rec['DESCRIZIONE2'] != ' ') {
            $ana_soggetti['COGNOME'] .= ' - ' . $soggetto_rec['DESCRIZIONE2'];
        }

        $ana_soggetti['CF'] = $soggetto_rec['CODFISCALE'];

        $ana_soggetti['NATGIU'] = 2;



        // DATI RESIDENZA
        $comune_rec = $this->ricavacomune($soggetto_rec['codprov'], $soggetto_rec['codcomune']);
        $ana_soggetti['CITTARESI'] = $comune_rec['COMUNE'];
        $ana_soggetti['DESCRIZIONEVIA'] = $soggetto_rec['indirizzo'];
        $ana_soggetti['CIVICO'] = $soggetto_rec['CIVICO'];
        $ana_soggetti['PROVRESI'] = $comune_rec['PROVINCIA'];
        $ana_soggetti['CAPRESI'] = $soggetto_rec['cap'];


        $ana_soggetti['CODANAG'] = '';


        $ana_soggetti['UTENTEINSERIMENTO'] = $soggetto_rec['UTENTECREAZIONE'];
        $ana_soggetti['DATAINSERIMENTO'] = $this->convertdate($soggetto_rec['DATACREAZIONE']);
        $ana_soggetti['ORAINSERIMENTO'] = 'ImpoE';
        $ana_soggetti['FONTEDATI'] = $soggetto_rec['fonte'];

        $ana_soggetti['UTENTEAGGIORNAMENTO'] = $soggetto_rec['UTENTEULTMOD'];
        $ana_soggetti['DATAAGGIORNAMENTO'] = $this->convertdate($soggetto_rec['DATAULTMOD']);




        try {
            ItaDB::DBInsert($this->ITALWEB_DB, 'ANA_SOGGETTI', 'ROWID', $ana_soggetti);
            $eventId = ItaDB::DBLastId($this->ITALWEB_DB);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in ana_soggetti ID " . $ana_soggetti['id'] . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return $eventId;
    }

    private function convertdate($data, $tipo = '-') {
        // SEPARA DATA DA ORARIO 
        if (!$data) {
            return false;
        }
        if ($tipo == '-') {
            return str_replace('-', '', $data);
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

    private function ricavacomune($codProv, $codComune) {
        if (!$codProv || !$codComune) {
            return false;
        }
        //   da qui mi ricavo il comune e il cap
        $sqlC = "SELECT * FROM `comuni` WHERE `provincia_stato` = '$codProv' AND `comune` = '$codComune'";
        $comuni_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlC, true);
        if (count($comuni_tab) > 1) {
            // 
            $this->scriviLog('ATTENZIONE PIU RISULTATI TROVATI ' . $sqlC);
            return false;
        }
        //   da qui mi ricavo la provincia

        $sqlP = "SELECT * FROM `province` WHERE `provincia` = '$codProv'";
        $provincia_rec = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlP, false);
        $comune_rec = array('COMUNE' => $comuni_tab[0]['denominazione'], 'PROVINCIA' => $provincia_rec['SIGLA'], 'CAP' => $comuni_tab[0]['cap']);
        return $comune_rec;
    }

    private function ricavaRecapiti($rowId_sogg, $id, $cf) {
        $sqlR = "SELECT * FROM `an_recapito` WHERE AR_IDATTORE = '$id' OR (AR_IDATTORE = 0 AND AR_CODFISCALE = '$cf')";
        // $sqlR = "SELECT * FROM `an_recapito` where AR_ID = 1459177 OR AR_ID = 1463283"; // PER TEST
        // a volte AR_IDATTORE = 0 MA C'è IL CF

        $an_recapiti_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlR, true);
        $RECAPITI_TMP = array();

        foreach ($an_recapiti_tab as $key => $an_recapiti_rec) {

            if (!$an_recapiti_rec['AR_RECAPITO']) {
                continue;
            }

            /*
             * 1) collego con soggetto  e
             * anagrafica comune tipo dato
             */
            $RECAPITI_TMP['ROW_ID_SOGGETTO'] = $rowId_sogg;
            // DECODIFICO LA TIPOLOGIA
            $desc = self::$SUBJECT_AN_RECAPITO[$an_recapiti_rec['AR_IDTIPORIF']];
            if (!$desc) {
                $this->scriviLog('DECODIFICA TIPOLOGIA ANA_RECAPITO NON RIUSCITA ' . $an_recapiti_rec['AR_IDTIPORIF']);
                continue;
            }
            $sqlITALWEB = "SELECT ROWID FROM ANA_COMUNE WHERE ANACAT = 'RIF' AND ANADES = '$desc'";
            $anComune_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlITALWEB, false);
            $RECAPITI_TMP['ROW_ID_ANARECAPITO'] = $anComune_rec['ROWID'];

            /*
             * fine 1)
             */

            $RECAPITI_TMP['RECAPITO'] = $an_recapiti_rec['AR_RECAPITO'];
            $RECAPITI_TMP['NOTE'] = $an_recapiti_rec['AR_NOTE'];

            $RECAPITI_TMP['PREDEFINITO'] = 0;
            if ($an_recapiti_rec['AR_PREDEFINITO'] == 'S') {
                $RECAPITI_TMP['PREDEFINITO'] = 1;
            }

            $RECAPITI_TMP['DATAINSERIMENTO'] = $RECAPITI_TMP['DATAVALINI'] = $this->convertdate($an_recapiti_rec['AR_DATAVALIDITA']);
            //$RECAPITI_TMP['DATAVALFIN'] = $this->convertdate($an_recapiti_rec['AR_DATAVALIDITA']);


            $RECAPITI_TMP['FONTEDATI'] = $an_recapiti_rec['AR_FONTE'];
            $RECAPITI_TMP['UTENTEAGGIORNAMENTO'] = $an_recapiti_rec['AR_UTENTEULTMOD'];

            try {
                ItaDB::DBInsert($this->ITALWEB_DB, 'ANA_RECAPITISOGGETTI', 'ROW_ID', $RECAPITI_TMP);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in ANA_COMUNE  " . $ex->getMessage();
                $this->scriviLog($testo);
                break;
            }
        }
    }

    private function importaRelazioni() {


        $sqlR = "SELECT * FROM an_mansione";
        $an_manisoni_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlR, true);

        $ANA_RUOLISOGGETTI = array();
        $i = 1;
        foreach ($an_manisoni_tab as $key => $an_mansione_rec) {

            /*
             * 1) CERCO SU ANA_SOGGETTI
             * CODFISSOGG
             * 
             * 2) RICERCO IL SOGGETTO COLLEGATO 
             * CODFISATTORERELAZIONE
             * 
             * 3)INSERISCO IL RECORD SU 
             * ANA_RUOLISOGGETTI
             * 
             */


            // VEDEERE SE OLTRE A DENTRO IL CF CERCAR EANCHE SULLA P.I
            // CERCO SOGGETTO 1 
            // PRINCIPALE RELAZIONE 
            // CONSIDERO ANCHE IL TIPO PER DUPLICATI


            $sqlSogg1 = "SELECT ROWID,COUNT(ROWID) AS QUANTI FROM ANA_SOGGETTI WHERE CF = '" . $an_mansione_rec['CODFISSOGG'] . "'";
            $Soggetto1_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlSogg1, false);

            if ($Soggetto1_rec['QUANTI'] > 1) {

                if ($an_mansione_rec['TIPOATTORERELAZIONE'] == 'I') {
                    $sqlTipo = " AND NATGIU = 1";
                } else {
                    $sqlTipo = " AND NATGIU = 0";
                }
                $sqlSogg1 = "SELECT ROWID,COUNT(ROWID) AS QUANTI FROM ANA_SOGGETTI WHERE CF = '" . $an_mansione_rec['CODFISSOGG'] . "'$sqlTipo";
                $Soggetto1_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlSogg1, false);
                if ($Soggetto1_rec['QUANTI'] > 1) {
                    $this->scriviLog('Attenzione trovati più soggetti_1 per cf ' . $an_mansione_rec['CODFISSOGG']);

                    //continue;
                }
            }
            if ($Soggetto1_rec['QUANTI'] == 0) {
                $this->scriviLog('Attenzione nessun soggetto_1 trovato per cf ' . $an_mansione_rec['CODFISSOGG']);
                continue;
            }

            // CERCO SOGGETTO 2 
            // COLLEGATO

            $sqlSogg2 = "SELECT ROWID,COUNT(ROWID) AS QUANTI FROM ANA_SOGGETTI WHERE CF = '" . $an_mansione_rec['CODFISATTORERELAZIONE'] . "'";
            $Soggetto2_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlSogg2, false);

            if ($Soggetto2_rec['QUANTI'] > 1) {
                if ($an_mansione_rec['TIPOATTORERELAZIONE'] == 'I') {
                    $sqlTipo = " AND NATGIU = 1";
                } else {
                    $sqlTipo = " AND NATGIU = 0";
                }

                $sqlSogg2 = "SELECT ROWID,COUNT(ROWID) AS QUANTI FROM ANA_SOGGETTI WHERE CF = '" . $an_mansione_rec['CODFISATTORERELAZIONE'] . "'$sqlTipo";
                $Soggetto2_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlSogg2, false);
                if ($Soggetto2_rec['QUANTI'] > 1) {
                    $this->scriviLog('Attenzione trovati più soggetti_2 per cf ' . $an_mansione_rec['CODFISATTORERELAZIONE']);
                    // continue;
                }
            }
            if ($Soggetto2_rec['QUANTI'] == 0) {
                $this->scriviLog('Attenzione nessun soggetto_2 trovato per cf ' . $an_mansione_rec['CODFISATTORERELAZIONE']);
                continue;
            }


            $ANA_RUOLISOGGETTI['ROW_ID_PRESTATORE'] = $Soggetto1_rec['ROWID']; // soggetto 1

            $ANA_RUOLISOGGETTI['ROW_ID_DATORE'] = $Soggetto2_rec['ROWID']; // SOGGETTO 2
            // decodifico la MANSIONE
            $manCod = self::$SUBJECT_AN_MANSIONE[$an_mansione_rec['idtipomansione']];

            if (!$manCod) {
                $this->scriviLog('DECODIFICA TIPOLOGIA MANSIONE NON RIUSCITA ' . $an_mansione_rec['idtipomansione']);
                continue;
            }
            $sqlITALWEB = "SELECT ROWID FROM ANA_RUOLI WHERE RUOCOD = '$manCod'";
            $anRuoli_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sqlITALWEB, false);
            $ANA_RUOLISOGGETTI['ROW_ID_ANARUOLI'] = $anRuoli_rec['ROWID'];



            $ANA_RUOLISOGGETTI['NOTE'] = '';
            $ANA_RUOLISOGGETTI['PREDEFINITO'] = '';
            $ANA_RUOLISOGGETTI['DATAVALINI'] = $this->convertdate($an_mansione_rec['datainizio']);
            $ANA_RUOLISOGGETTI['DATAVALFIN'] = $this->convertdate($an_mansione_rec['datafine']);

            $ANA_RUOLISOGGETTI['FONTEDATI'] = $an_mansione_rec['FONTE'];
            $ANA_RUOLISOGGETTI['DATAINSERIMENTO'] = '';
            $ANA_RUOLISOGGETTI['ORAINSERIMENTO'] = '';
            $ANA_RUOLISOGGETTI['UTENTEINSERIMENTO'] = '';

            $ANA_RUOLISOGGETTI['DATAAGGIORNAMENTO'] = $this->convertdate($an_mansione_rec['DATAULTMOD']);
            $ANA_RUOLISOGGETTI['ORAAGGIORNAMENTO'] = '';
            $ANA_RUOLISOGGETTI['UTENTEAGGIORNAMENTO'] = $an_mansione_rec['UTENTEULTMOD'];

            if ($an_mansione_rec['idstabilimento'] != 0) {
                // collego stabilimento????
            }


            try {
                ItaDB::DBInsert($this->ITALWEB_DB, 'ANA_RUOLISOGGETTI', 'ROW_ID', $ANA_RUOLISOGGETTI);
                $i++;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in ANA_RUOLISOGGETTI  " . $ex->getMessage();
                $this->scriviLog($testo);
                break;
            }
        }
        Out::msgInfo("OK", $i . " Mansioni sogetto Caricate ANA_RUOLISOGGETTI");
    }

    private function setAna_comuni() {
        $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT = 'RIF'";

        $classificazioni_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        if (!$classificazioni_tab) {
            $classificazioni_tab = array();
            $i = 1;
            foreach (self::$SUBJECT_AN_RECAPITO as $value) {
                $classificazioni_tab['ANACAT'] = 'RIF';
                $classificazioni_tab['ANACOD'] = str_pad($i, 6, 0, STR_PAD_LEFT);
                $classificazioni_tab['ANADES'] = $value;
                $i++;
                try {
                    ItaDB::DBInsert($this->ITALWEB_DB, 'ANA_COMUNE', 'ROWID', $classificazioni_tab);
                } catch (Exception $ex) {
                    $testo = "Fatal: Errore in ANA_COMUNE  " . $ex->getMessage();
                    $this->scriviLog($testo);
                    break;
                }
            }
        }
        //  Out::msgInfo('ppp', print_r($classificazioni_tab,true));
    }

}
