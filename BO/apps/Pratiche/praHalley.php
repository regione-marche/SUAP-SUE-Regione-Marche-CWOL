<?php

/**
 *  Programma Popolamento sue Sirolo Halley
 *
 *
 * @category   Importazione
 * @package    /apps/Menu
 * @author     Tania Angeloni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    12.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praHalley() {
    $intSuapSbt = new praHalley();
    $intSuapSbt->parseEvent();
    return;
}

class praHalley extends itaModel {

    const SPORTELLO_DEFAULT = 6;
    const PROCEDIMENTO_DEFAULT = 500001;
    const RESPONSABILE_DEFAULT = '000001';
    const DESRUOIMPRESA_DEFAULT = '0012';
    const DESRUOTECNICO_DEFAULT = '0007';
    const DESRUORICHIEDENTE_DEFAULT = '0002';
    //  const PATH_ALLEGATI = 'C:/Works/FilesGMI/Edilizia/documentazione/0000000000';
    // const PATH_ALLEGATI = '/home/italsoft/files_lama';
    const PATH_ALLEGATI = 'C:/Users/Tania/Desktop/SIROLO/TESTI';

    static $SUBJECT_PARERE_COMMISSIONE = array(
        // tabella UTUTTAN
        '1', "IN ATTESA",
        '2' => "FAVOREVOLE",
        '3' => "SOSPENSIVO",
        '4' => "CONTRARIO",
        '5' => "FAVOREVOLE A CONDIZIONE",
        '6' => "SILENZIO/ASSENSO",
    );
    static $SUBJECT_RUOLO_SOGGETTO = array(
        // tabella UTUTTAN
        '1' => '0001', // Richiedente
        '2' => '0002', // Proprietario
        '3' => '0007', //Progettista
        '7' => '0012', //Impresa esecutrice
        '4' => '0008', //Direttore lavori
        '17' => '9001', //Impresa strutture
        '26' => '9002', //Dirigente
        '15' => '0010', //Direttore lavori strutture
    );
    static $SUBJECT_BASE_PROCEDIMENTI = array(
        '1' => '720150',
        '2' => '720151',
        '3' => '720152',
        '6' => '720153',
        '7' => '720154',
        '8' => '720155',
        '9' => '720156',
        '11' => '720157',
        '13' => '720158',
        '14' => '720159',
        '17' => '720160',
        '18' => '720161',
        '19' => '720162',
        '22' => '720163',
        '23' => '720164',
        '24' => '720165',
        '34' => '720166',
        '37' => '720167',
        '38' => '720168',
    );
    static $SUBJECT_BASE_TIPOLOGIA = array(
        "1" => "700001",
        "2" => "700002",
        "3" => "700003",
        "4" => "700004",
        "5" => "700005",
        "9" => "700009",
        "18" => "700018",
        "24" => "700024",
        "26" => "700026",
        "28" => "700028",
        "29" => "700029",
        "30" => "700030",
        "31" => "700031",
        "40" => "700040",
        "41" => "700041",
        "42" => "700042",
        "50" => "700050",
        "51" => "700051",
        "52" => "700052",
        "53" => "700053",
        "56" => "700056",
        "57" => "700057",
        "58" => "700058",
        "59" => "700059",
        "60" => "700060",
        "69" => "700069",
        "71" => "700071",
        "72" => "700072",
        "73" => "700073",
        "74" => "700074",
        "75" => "700075",
        "76" => "700076",
        "77" => "700077",
        "80" => "700080",
        "81" => "700081",
        "82" => "700082",
        "93" => "700093",
        "98" => "700098",
        "101" => "700101",
        "103" => "700103",
        "122" => "700122",
        "146" => "700146",
        "147" => "700147",
        "148" => "700148",
        "149" => "700149",
        "150" => "700150",
        "151" => "700151",
        "152" => "700152",
        "153" => "700153",
        "154" => "700154",
        "155" => "700155",
        "156" => "700156",
        "157" => "700157",
        "158" => "700158",
        "159" => "700159",
        "160" => "700160",
        "161" => "700161",
        "162" => "700162",
        "163" => "700163",
        "164" => "700164",
        "165" => "700165",
        "166" => "700166",
        "167" => "700167",
        "168" => "700168",
        "169" => "700169",
        "170" => "700170",
        "171" => "700171",
        "172" => "700172",
        "173" => "700173",
        "174" => "700174",
        "175" => "700175",
        "176" => "700176",
        "177" => "700177",
        "178" => "700178",
        "179" => "700179",
        "180" => "700180",
        "196" => "700196",
        "206" => "700206",
        "233" => "700233",
        "234" => "700234",
        "235" => "700235",
        "236" => "700236",
        "237" => "700237",
        "238" => "700238",
        "239" => "700239",
        "240" => "700240",
        "241" => "700241",
        "242" => "700242",
        "244" => "700244",
        "245" => "700245",
        "246" => "700246",
        "247" => "700247",
        "248" => "700248",
        "250" => "700250",
        "252" => "700252",
        "271" => "700271",
        "283" => "700283",
        "286" => "700286",
        "309" => "700309",
        "310" => "700310",
        "311" => "700311",
        "312" => "700312",
        "313" => "700313",
        "356" => "700356",
        "357" => "700357",
        "358" => "700358",
        "376" => "700376",
        "377" => "700377",
        "378" => "700378",
        "379" => "700379",
        "380" => "700380",
        "381" => "700381",
        "382" => "700382",
        "383" => "700383",
        "384" => "700384",
        "385" => "700385",
        "386" => "700386",
        "387" => "700387",
        "388" => "700388",
        "389" => "700389",
        "390" => "700390",
        "391" => "700391",
        "392" => "700392",
        "393" => "700393",
        "394" => "700394",
        "395" => "700395",
        "396" => "700396",
        "397" => "700397",
        "398" => "700398",
        "399" => "700399",
        "400" => "700400",
        "402" => "700402",
        "403" => "700403",
        "404" => "700404",
        "405" => "700405",
        "406" => "700406",
        "407" => "700407",
        "408" => "700408",
        "409" => "700409",
        "411" => "700411",
        "412" => "700412",
        "413" => "700413",
        "414" => "700414",
        "415" => "700415",
        "416" => "700416",
        "418" => "700418",
        "419" => "700419",
        "420" => "700420",
        "421" => "700421",
        "422" => "700422",
        "423" => "700423",
        "424" => "700424",
        "425" => "700425",
        "428" => "700428",
        "429" => "700429",
        "430" => "700430",
        "431" => "700431"
    );
    public $praLib;
    public $PRAM_DB;
    public $HALLEY_DB;
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
            $this->nameForm = 'praHalley';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', "I758");
            $this->HALLEY_DB = ItaDB::DBOpen('halley', "");
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
                $this->fileLog = sys_get_temp_dir() . "/praHalley_" . time() . ".log";
                $this->scriviLog("Avvio Programma praHalley");
                $this->gesnum_anno = array();
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->importafascicolo();
                        $this->gesnum_anno = array();
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
        $sql_base = "SELECT * 
            FROM `UTCONCS`
            WHERE 1=1
            
            ORDER BY `UTCOSRL` ASC
            ";
        // AND UTCOSRL = 3944

        $Pratiche_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql_base, true);
        $i = 0;

        foreach ($Pratiche_tab as $Pratiche_rec) {

            $toinsert = $this->inserisciFascicolo($Pratiche_rec);
            if (!$toinsert) {
                $anomalie++;
                $flAnomalia = true;
            } else {
                $i++;
                $this->caricariLocalizzazione($Pratiche_rec['UTCOSRL']);
                $this->ricavaDatiCatastali($Pratiche_rec['UTCOSRL']);
                $this->caricariSoggetti($Pratiche_rec['UTCOSRL']);
                $toinsert_1 = $this->creapasso($Pratiche_rec['UTCOSRL']);
                $toinsert_2 = $this->creapassoPareri($Pratiche_rec['UTCOSRL']);
//            // RIORDINO GLI EVENTUALI PASSI CREATI
                if ($toinsert_1 || $toinsert_2) {
                    $this->ordinaPassi($this->Pratica);
                }
            }
        }
        $this->collegaAntecedente();
        Out::msgInfo("OK", $i . " Fascicoli caricati");
        return true;
    }

    private function inserisciFascicolo($Dati_rec) {
        //  Out::msgInfo('RECORD', print_r($Dati_rec, TRUE));

        $proges_rec = array();

        $proges_rec['GESMIGRA'] = $Dati_rec['UTCOSRL'];
        $this->Id = $Dati_rec['UTCOSRL'];  // mi salvo id per relazioni

        $proges_rec['GESNPR'] = substr($Dati_rec['UTCDATA'], 0, 4) . $Dati_rec['UTPROTO'];  // PROTOCOLLO PRATICA
        $proges_rec['GESPAR'] = 'A';
        $proges_rec['GESORA'] = "00:00";

        $proges_rec['GESNUM'] = $this->gestGesnum($Dati_rec['UTANNOP']); //RICAVO GESNUM PROGRESSIVO PER ANNO
        $this->Pratica = $proges_rec['GESNUM']; //??????
        $proges_rec['SERIEANNO'] = $Dati_rec['UTANNOP'];         // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $this->gesnum_anno[$Dati_rec['UTANNOP']];         // Serie NUMERO
        $proges_rec['SERIECODICE'] = 30;         // Serie PROVENIENZA // TO DO 
        $proges_rec['GESCODPROC'] = $Dati_rec['UTRIFER'];
        if ($Dati_rec['UTRIFER'] != $Dati_rec['UTPRAED'] && $Dati_rec['UTPRAED']) {
            $proges_rec['GESCODPROC'] .= " " . $Dati_rec['UTPRAED'];
        }

        $proges_rec['GESTSP'] = self::SPORTELLO_DEFAULT; // SPORTELLO
        /*
         * setto le date
         */
        $proges_rec['GESDRI'] = $proges_rec['GESDRE'] = $this->cleardate($Dati_rec['UTCDATA']);

        /*
         * Tipologia procedimento
         */
        $proges_rec['GESTIP'] = self::$SUBJECT_BASE_TIPOLOGIA[$Dati_rec['UTTIPCO']]; // TODO anagrafica tipologia decod loto tabella ( UTTLGCO )
        if (!$proges_rec['GESTIP']) {
            $this->scriviLog('Decod Tipologia Porcedimento non trovata' . $Dati_rec['UTTIPCO']);
            // $proges_rec['GESPRO'] = 720307; // metto il generico
        }

        /*
         * Anagrafica 
         * Procedimento
         */
        $proges_rec['GESPRO'] = self::$SUBJECT_BASE_PROCEDIMENTI[$Dati_rec['UTTPATT']]; // TODO anagrafica procedimenti loro tabella ( UTUTTPP )
        if (!$proges_rec['GESPRO']) {
            $this->scriviLog('Decod Procedimento non trovato' . $Dati_rec['UTTPATT']);
            // $proges_rec['GESPRO'] = 720307; // metto il generico
        }

        /*
         */
        $proges_rec['GESOGG'] = $this->ricavaOggetto($Dati_rec['UTCOSRL']);    // Oggetto

        $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;

        // data chiusura pratica

        $proges_rec['GESDCH'] = '';

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            if ($Dati_rec['UTLAVOF'] != '0001-01-01') {
                $this->creapassoDate($Dati_rec['UTLAVOF'], 'Fine Lavori', 900001);
            }
            if ($Dati_rec['UTLAVOI'] != '0001-01-01') {
                $this->creapassoDate($Dati_rec['UTLAVOI'], 'Inizio Lavori', 900002);
            }
            if ($Dati_rec['UTDATAC'] != '0001-01-01' || $Dati_rec['UTNUMCO']) {
                $this->creapassoDate($Dati_rec['UTDATAC'], 'Rilascio Concessione', 900003, $Dati_rec['UTNUMCO']);
            }
            if ($Dati_rec['UTDISTR'] != '0001-01-01') {
                $this->creapassoDate($Dati_rec['UTDISTR'], 'Itruttoria', 900004);
            }
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Record ID " . $Dati_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
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
    }

    private function caricariSoggetti($IdPratica) {
        $sql = "SELECT UTANAPR.UTAPTPN,UTUTANA.* 
                FROM UTUTANA
                INNER JOIN UTANAPR ON UTANAPR.UTAPANA = UTUTANA.UTANSRL
                WHERE UTAPIDP = " . $IdPratica;

        $Soggetti_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        /*
         *      TODO LIST
         *         - NATURA LEGALE??
         *         - DECODIFICA COMUNE 
         *         - CIVICO ESTRAPOLARE DALLA VIA
         */
        $richiedente_rec = array();
        foreach ($Soggetti_tab as $Soggetti_rec) {
            $richiedente_rec['DESNUM'] = $this->Pratica;

            $richiedente_rec['DESNOM'] = $Soggetti_rec['UTCNOME'];
            $richiedente_rec['DESNOME'] = $Soggetti_rec['UTCOGNO'];
            $richiedente_rec['DESCOGNOME'] = $Soggetti_rec['UTCNOME'];
            $richiedente_rec['DESFIS'] = $Soggetti_rec['UTCFISC'];
            if (!$Soggetti_rec['UTPAIVA']) {
                $richiedente_rec['DESPIVA'] = $Soggetti_rec['UTPAIVA']; //fix per record sporchi con lo 0
            }
            $richiedente_rec['DESTEL'] = $Soggetti_rec['UTUANTE'];
            $richiedente_rec['DESCEL'] = $Soggetti_rec['UTUANCE'];
            $richiedente_rec['DESFAX'] = $Soggetti_rec['UTUANFX'];
            $richiedente_rec['DESEMA'] = $Soggetti_rec['UTUTEMA']; // email


            $richiedente_rec['DESIND'] = $Soggetti_rec['UTANVIA'];
            /*
             * ricavo il civico
             */
            $indirizzoTmp = explode(" ", $Soggetti_rec['UTANVIA']);
            $i = count($indirizzoTmp);
            $richiedente_rec['DESCIV'] = $indirizzoTmp[$i - 1];
            /*
             */
            $comuneResid = $this->ricavaComune($Soggetti_rec['UTRESID']);
            $richiedente_rec['DESCIT'] = $comuneResid['citta'];
            $richiedente_rec['DESCAP'] = $comuneResid['cap'];
            $richiedente_rec['DESPRO'] = $comuneResid['prov'];

            $richiedente_rec['DESNASDAT'] = $this->cleardate($Soggetti_rec['UTDNASC']);
            $comuneNascita = $this->ricavaComune($Soggetti_rec['UTLNASC']);
            $richiedente_rec['DESNASCIT'] = $comuneNascita['citta'];
            $richiedente_rec['DESNOTE'] = $Soggetti_rec['UTANSRL']; // MI SALVO IL ROWID CHIAVE SOGGETTO IMPORTTAO

            $richiedente_rec['DESRUO'] = self::$SUBJECT_RUOLO_SOGGETTO[$Soggetti_rec['UTAPTPN']];

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $richiedente_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Richiedente ID" . $Soggetti_rec['UTANSRL'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function ricavaComune($Cod) {
        if (!$Cod) {
            return false;
        }
        $localitaTmp = array();

        $sql = "SELECT GTRILOC.GTLIDE2,GTRILOC.GTLICAP,GTDCITT.GTDCPRO FROM `GTRILOC`" .
                "LEFT JOIN GTDCITT ON GTDCITT.GTDCSER = GTRILOC.GTLICOD"
                . " WHERE `GTLICOD` = $Cod ";

        //$sql = "SELECT * FROM `GTRILOC` WHERE `GTLICOD` = $Cod ";
        $localita_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);

        $localitaTmp = array(
            'citta' => $localita_rec['GTLIDE2'],
            'cap' => $localita_rec['GTLICAP'],
            'prov' => $localita_rec['GTDCPRO'],
        );
        return $localitaTmp;
    }

    private function creapassoDate($Data, $Descrizione, $tipopasso, $numero = '') {
        /*
         * passi fatti
         */

        $Desctipopaso = $Descrizione;
        if ($numero) {
            $Descrizione .= ' Numero ' . $numero . ' del ' . $Data;
        }
        // $seq = 0;
        $propak = $this->praLib->PropakGenerator($this->Pratica);
        $passo = array(
            'PRONUM' => $this->Pratica,
            'PROSEQ' => 0,
            //'PROPRO' => self::PROCEDIMENTO_DEFAULT,
            'PRORPA' => self::RESPONSABILE_DEFAULT, // responsabile
            'PROANN' => '',
            'PRODPA' => $Descrizione, // nome passo
            'PROINI' => $this->cleardate($Data), // data apertura passo
            'PROFIN' => '', // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => $tipopasso, // tipo passo
            'PRODTP' => $Desctipopaso, // descrizione tipo passo  
        );
        if ($numero) {
            $passo['PRODOCPROG'] = $numero;
        }
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }

        return true;
    }

    private function creapasso($Id) {
        /*
         * passi fatti
         */

        //$sql = "SELECT * FROM UTRIAPP WHERE UTRIIDP = 4630 ORDER BY `UTRIRSM` ASC";
        $sql = "SELECT * FROM UTRIAPP WHERE UTRIIDP = $Id ORDER BY UTRIDUE";
        $Passi_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        if (!$Passi_tab) {
            return false;
        }

        foreach ($Passi_tab as $Passi_rec) {

            // $seq = 0;
            $propak = $this->praLib->PropakGenerator($this->Pratica);
            $passo = array(
                'PRONUM' => $this->Pratica,
                'PROSEQ' => $Passi_rec['UTRIDUE'],
                //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
                'PRORPA' => self::RESPONSABILE_DEFAULT, // responsabile
                'PROANN' => $Passi_rec['UTRISRL'],
                'PRODPA' => str_replace('-', "", $Passi_rec['UTRITPS']), // nome passo
                'PROINI' => $this->cleardate($Passi_rec['UTRIDAT']), // data apertura passo
                'PROFIN' => '', // data chiusura passo
                'PROPAK' => $propak,
                'PROCLT' => '', // tipo passo
                'PRODTP' => '', // descrizione tipo passo  
            );
            //cosi prendi l'allegato
            //$passi_rec['UTRIRSM'] 
            //lo cerchi IN  SELECT * FROM `UTRISTM` WHERE `UTRMSRL` = 5719 ORDER BY `UTRMTPS` ASC 
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                $this->importaAllegati($Passi_rec['UTRIRSM'], $propak, $Passi_rec['UTRITPS']);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function creapassoPareri($Id) {
        /*
         * passi fatti
         */

        //$sql = "SELECT * FROM UTRIAPP WHERE UTRIIDP = 4630 ORDER BY `UTRIRSM` ASC";
        $sql = "SELECT UTPARER.*,UTPARUF.UTPUDES FROM `UTPARER` "
                . "LEFT JOIN UTPARUF ON UTPARER.UTPEUFF = UTPARUF.UTPUSRL "
                . "WHERE UTPARER.UTPEPRA = $Id";
        $Passi_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        if (!$Passi_tab) {
            return false;
        }

        foreach ($Passi_tab as $Passi_rec) {

            $DESC = '';
            $DESC = $Passi_rec['UTPUDES'];
            if ($Passi_rec['UTPENSE'] != 0) {
                $DESC .= ' Nr.Seduta ' . $Passi_rec['UTPENSE'] . ' ';
            }
            $DESC .= self::$SUBJECT_PARERE_COMMISSIONE[$Passi_rec['UTPETIP']] . ' ';
            $DESC .= ' ' . $Passi_rec['UTPEPES'];
            // $seq = 0;
            $Note = $DESC;
            $propak = $this->praLib->PropakGenerator($this->Pratica);
            $passo = array(
                'PRONUM' => $this->Pratica,
                'PROSEQ' => 0,
                //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
                'PRORPA' => self::RESPONSABILE_DEFAULT, // responsabile
                'PROANN' => $Passi_rec['UTPESRL'],
                'PRODPA' => $DESC, // nome passo
                'PROINI' => $this->cleardate($Passi_rec['UTPEDAT']), // data apertura passo
                'PROFIN' => '', // data chiusura passo
                'PROPAK' => $propak,
                'PROCLT' => '', // tipo passo
                'PRODTP' => ''
            );

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                //  $this->importaAllegati($Passi_rec['UTRIRSM'], $propak, $Passi_rec['UTRITPS']);
                $this->scriviComunicazione($Passi_rec, $propak, $Note);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore creazione passo per parere" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function scriviComunicazione($Passi_rec, $ProPak, $Note = '') {

        $Pracom_rec = array(
            'COMTIP' => 'A',
            'COMNUM' => $this->Pratica,
            'COMPAK' => $ProPak,
            'COMDPR' => $this->cleardate($Passi_rec['UTPEDAT']),
            'COMNOM' => $Passi_rec['UTPUDES'],
            'COMNOT' => $Note,
        );
        if ($Passi_rec['UTPEPRO']) {
            $Pracom_rec['COMPRT'] = substr($Passi_rec['UTPEDAT'], 0, 4) . $Passi_rec['UTPEPRO'];
        }
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRACOM', 'ROWID', $Pracom_rec);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per parere" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
    }

    private function importaAllegati($Id, $ProPak, $NomeFile) {
        if (!$Id) {
            return false;
        }

        $sql = "SELECT * FROM `UTRISTM` WHERE `UTRMSRL` = $Id";
        $Allegati_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);

        if (!$Allegati_rec) {
            $this->scriviLog('Nessun ALLEGATO trovato ' . $sql);
            return false;
        }
        $Propas_rec = array();

        $NomeFile = str_replace(' ', "", $NomeFile);
        $NomeFile = str_replace('-', "", $NomeFile) . '.doc';

        $Propas_rec['PASKEY'] = $ProPak;
        $rand = md5(uniqid()) . '.' . pathinfo($NomeFile, PATHINFO_EXTENSION);
        $Propas_rec['PASFIL'] = $rand;
        $Propas_rec['PASMIGRA'] = $Allegati_rec['UTRMSRL'];
        $Propas_rec['PASNAME'] = $NomeFile; // nome fisico file 

        $Propas_rec['PASNOT'] = "File originale " . $Allegati_rec['UTCWSRL'];
        $Propas_rec['PASCLA'] = "GENERALE";
        $Propas_rec['PASPUB'] = 0; // blocca pubblicazione sul F.O
        //
        $Propas_rec['PASCLA'] = 'ESTERNO';
//           $proges_rec['PASPRTROWID'] = $pasdoc_param['PRACOM_ROWID'];
//                $proges_rec['PASSHA2'] = '2';
//            }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $Propas_rec);
            //$RowidPasDOc = ItaDB::DBLastId($this->PRAM_DB);
            $this->creaALL($Propas_rec, $Allegati_rec['UTRMTXT']);

            // TODO creao un file con il contenuto di $Allegati_rec['UTRMTXT'].docx
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Record Allegato ID" . $Allegati_rec['UTCWSRL'] . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }

        return true;
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

    private function cleardate($data) {
        /*
         * PULISCE LE DATE SPORCHE NEL DB
         */
        if (!$data || $data == '0001-01-01') {
            return false;
        }
        return str_replace("-", "", $data);
    }

    public function creaALL($doc_rec, $contenuto) {
        $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY']); // ALLEGATI DI PASSO 
        file_put_contents($pratPath . '/' . $doc_rec['PASFIL'], $contenuto);
        return true;
    }

    private function ricavaOggetto($IdPrat) {
        $sql = "SELECT UTXTSTC FROM uttxtco WHERE UTXNPRC = " . $IdPrat;
        $Oggetto_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);

        $oggetto = '';
        foreach ($Oggetto_tab as $Oggetto_rec) {
            $oggetto .= $Oggetto_rec['UTXTSTC'] . ' ';
        }
        return $oggetto;
    }

    private function ricavaProcedimento($cod) {
        /*
         * UTTLGCO TABELLA ANAGRAFICA PROCEDIMENTI
         * IO HO PRESO SOLO QUELLI USATI
         * SELECT UTTCSRL,UTTCDES FROM UTTLGCO WHERE UTTCSRL IN (SELECT distinct(UTTIPCO) FROM `UTCONCS`) ORDER BY `UTTCSRL` DESC  
         */
        $sql = "SELECT * FROM UTTLGCO WHERE UTTCSRL " . $cod;
        $Procedimento_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);
        return $Procedimento_rec['UTTCDES'];
    }

    private function collegaAntecedente() {

        $sql = "SELECT * FROM UTECOLP ORDER BY UTKCSRL";
        $Collegamenti_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        $i = 0;

        foreach ($Collegamenti_tab as $Collegamenti_rec) {
            $sql = "SELECT GESNUM FROM PROGES WHERE GESMIGRA = " . $Collegamenti_rec['UTKCIDP'];
            $Padre_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if (!$Padre_rec) {
                $this->scriviLog('nessun record trovato PADRE ' . $sql);
                continue;
            }
            $sql = "SELECT ROWID FROM PROGES WHERE GESMIGRA = " . $Collegamenti_rec['UTKCIDF'];
            $Figlio_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if (!$Figlio_rec) {
                $this->scriviLog('nessun record trovato FIGLIO ' . $sql);
                continue;
            }
            $Array = array(
                'ROWID' => $Figlio_rec['ROWID'],
                'GESPRE' => $Padre_rec['GESNUM'],
            );
            if (!$this->updateRecord($this->PRAM_DB, 'PROGES', $Array, ' ')) {
                Out::msgInfo('ERRORE IN UPDATE', 'ERRORE IN UPDATE ANTECEDENTE <br>PROCEDURA INTERROTTA');
                return false;
            } else {
                ++$i;
            }
        }
        Out::msgInfo('', 'Antecedneti collegati ' . $i);
    }

    private function caricariLocalizzazione($Id) {
        $sql = "SELECT utterri.UTTTCIV,GTVIE.GTVITOD FROM utterri "
                . "INNER JOIN GTVIE ON utterri.UTTTARE = GTVIE.GTVISRL"
                . " WHERE UTTTIDC = $Id";
        $via_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        if (!$via_tab) {
            return false;
        }
        if (count($via_tab) > 1) {
            $this->scriviLog("Attenzione Doppia Localizzazione Intervento " . $sql);
        }

        foreach ($via_tab as $via_rec) {
            $Civico = '';
            if ($via_rec['UTTTCIV'] != 0) {
                //SELECT * FROM `GTNUMER` WHERE `GTNUVIA` = 133 ORDER BY `GTNUMER`.`GTNUSRL` ASC 
                $sql = "SELECT GTNUNCI FROM GTNUMER WHERE GTNUSRL = " . $via_rec['UTTTCIV'];
                $civico_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);
                $Civico = $civico_rec['GTNUNCI'] . $civico_rec['GTNUECI'];
            }
            $localita_rec = array(
                'DESNUM' => $this->Pratica,
                'DESIND' => $via_rec['GTVITOD'],
                'DESCIV' => $Civico,
                'DESRUO' => '0014',
                'DESNOTE' => 'Impo'
            );
            $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_rec, $insert_Info);
        }

        return true;
    }

    private function ricavaDatiCatastali($Id) {

        $sql = "SELECT * FROM utprcat WHERE UTCKIDP = $Id";
        $catasto_tab = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, true);
        foreach ($catasto_tab as $key => $catasto_rec) {
            $catasto_rec['UTCKMAP'];
            // lo vai a cercare in 
            $sql = "SELECT UTMPFGL,UTMPPAR FROM UTMAPPL WHERE UTMPSRL = " . $catasto_rec['UTCKMAP'];
            $Fogli_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);
            // foglio UTMPFGL
            // particella UTMPPAR
            // subalterno
            if ($catasto_rec['UTCKSBB'] != 0) {
                $sql = "SELECT UTSUSUB FROM utsublt WHERE UTSUSRL = " . $catasto_rec['UTCKSBB'];
                $Sub_rec = ItaDB::DBSQLSelect($this->HALLEY_DB, $sql, false);
            }



            $daticatastali = array(
                'PRONUM' => $this->Pratica,
                'SEQUENZA' => $key,
                'FOGLIO' => str_pad($Fogli_rec['UTMPFGL'], 4, "0", STR_PAD_LEFT),
                'PARTICELLA' => str_pad($Fogli_rec['UTMPPAR'], 5, "0", STR_PAD_LEFT),
                    //'SUBALTERNO' => str_pad($Sub_rec['UTSUSUB'], 4, "0", STR_PAD_LEFT),
            );
            if($Sub_rec['UTSUSUB']) {
                $daticatastali['SUBALTERNO'] = str_pad($Sub_rec['UTSUSUB'], 4, "0", STR_PAD_LEFT);
            }

            /*
              if ($catastali_rec['SubCat']) {
              $daticatastali['SUBALTERNO'] = str_pad($catastali_rec['SubCat'], 4, "0", STR_PAD_LEFT);
              $daticatastali['NOTE'] .= " S: " . $catastali_rec['SubCat'];
              }
              if ($from == 'CATTER') {
              $daticatastali['TIPO'] = 'T';
              } elseif ($from == 'CATURB') {
              $daticatastali['TIPO'] = 'F';
              }

             */

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PRAIMM', 'ROWID', $daticatastali);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Dati Catastali per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
            }
        }
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
