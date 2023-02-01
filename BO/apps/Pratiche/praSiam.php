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

function praSiam() {
    $intSuapSbt = new praSiam();
    $intSuapSbt->parseEvent();
    return;
}

class praSiam extends itaModel {

    const SPORTELLO_DEFAULT = 6;
    const PROCEDIMENTO_DEFAULT = 000001;
    const RESPONSABILE_DEFAULT = '000001';
    const DESRUOIMPRESA_DEFAULT = '0012';
    const DESRUOTECNICO_DEFAULT = '0007';
    const DESRUORICHIEDENTE_DEFAULT = '0002';
//  const PATH_ALLEGATI = 'C:/Works/FilesGMI/Edilizia/documentazione/0000000000';
// const PATH_ALLEGATI = '/home/italsoft/files_lama';
    const PATH_ALLEGATI = 'C:/Users/Tania/Desktop/SIROLO/TESTI';

    static $SUBJECT_SERIE_PRATICA = array(
        "1" => "30", //SCARICHI INDUSTRIALI
        "2" => "31", //RIFIUTI SEMPLIFICATI
        "3" => "32", //RIFIUTI ORDINARI
        "7" => "33", //ARIA EMISSIONE IN ATMOSFERA ORDINARIA
        "16" => "34", //SCARICHI URBANI
        "1320499" => "35", //VIA
        "1324047" => "36", //ARIA EMISSIONE IN ATMOSFERA SEMPLIFICATA
        "1325560" => "37", //PARERE ARIA
        "1411852" => "38", //AUA
        "1411853" => "39" //Monitoraggio
    );
    static $SUBJECT_RESPONSABILE_PROC = array(
        'virna.cappellacci' => '700001',
        'silvia.bertini' => '700002',
        'siamadmin' => '700003',
        'salvatore.grillo' => '700004',
        'romina.morici' => '700005',
        'roberto.marsigliani' => '700006',
        'roberto.ciccioli' => '700007',
        'paola.mogetta' => '700008',
        'orietta.severini' => '700009',
        'mauro.mariotti' => '700010',
        'maurizio.paulini' => '700011',
        'maria.melfi' => '700012',
        'marco.paoletti' => '700013',
        'lucia.fioretti' => '700014',
        'katia.pesaresi' => '700015',
        'giuseppina.pieroni' => '700016',
        'francesca.mancinelli' => '700017',
        'elisabetta.poloni' => '700018',
        'dalia.ciccioli' => '700019',
        'claudio.accorsi' => '700020',
        'beatrice.antonelli' => '700021',
        'claudio.contigiani' => '700022',
        'luca.addei' => '700023',
        'adriano.conti' => '700024',
        'maurizio.scarpecci' => '700025'
    );
    static $SUBJECT_RUOLO_SOGGETTO = array(
        'I' => '0004', // Impresa
        'S' => '0002', //  soggetto
    );
    static $SUBJECT_BASE_PROCEDIMENTI = array(
        '18' => '910001',
        '35405' => '910002',
        '526482' => '910003',
        '526489' => '910004',
        '535093' => '910005',
        '537316' => '910007',
        '537340' => '910007',
        '537343' => '910007',
        '537346' => '910007',
        '537349' => '910007',
        '537352' => '910007',
        '1298788' => '910008',
        '1298855' => '910009',
        '1298885' => '910010',
        '1298913' => '910011',
        '1298941' => '910012',
        '1298969' => '910013',
        '1298997' => '910014',
        '1299025' => '910015',
        '1299053' => '910016',
        '1299064' => '910017',
        '1299075' => '910018',
        '1299097' => '910019',
        '1299130' => '910020',
        '1299146' => '910021',
        '1299228' => '910022',
        '1299249' => '910023',
        '1299270' => '910024',
        '1299291' => '910025',
        '1299312' => '910026',
        '1299336' => '910027',
        '1299365' => '910028',
        '1299390' => '910029',
        '1299415' => '910030',
        '1299440' => '910031',
        '1320311' => '910032',
        '1320326' => '910033',
        '1320366' => '910034',
        '1320381' => '910035',
        '1320396' => '910036',
        '1320410' => '910037',
        '1320442' => '910038',
        '1320450' => '910039',
        '1320456' => '910040',
        '1320462' => '910041',
        '1320481' => '910042',
        '1320490' => '910043',
        '1320503' => '910044',
        '1320551' => '910045',
        '1320571' => '910046',
        '1320630' => '910047',
        '1321570' => '910048',
        '1322539' => '910049',
        '1324442' => '910050',
        '1324476' => '910051',
        '1359088' => '910052',
        '1364342' => '910053',
        '1367715' => '910054',
        '1370784' => '910055',
        '1382807' => '910056',
        '1395617' => '910057',
        '1411868' => '910058',
        '1411955' => '910059',
        '1411980' => '910060',
        '1412002' => '910061',
        '1412029' => '910062',
        '1412049' => '910063',
        '1412069' => '910064',
        '1412089' => '910065',
        '1412279' => '910066',
        '1430624' => '910067',
        '1430827' => '910068'
    );
    static $SUBJECT_PROCLT = array(
        '900001' => 'Pratica Suap',
        '900002' => 'Cartaceo',
        '900003' => 'Sospensione',
        '900004' => '  ',
        '900005' => 'Attività',
    );
    static $SUBJECT_BASE_TIPOLOGIA = array(
        "0" => "700000",
        "432542" => "700001",
        "432544" => "700002",
        "432546" => "700003",
        "432550" => "700004",
        "432552" => "700005",
        "537179" => "700006",
        "537180" => "700007",
        "1298825" => "700008",
        "1299174" => "700009",
        "1299176" => "700010",
        "1299177" => "700011",
        "1299178" => "700012",
        "1320500" => "700013",
        "1320501" => "700014",
        "1320502" => "700015",
        "1321488" => "700016",
        "1411854" => "700017",
        "1411867" => "700018"
    );
    public $praLib;
    public $PRAM_DB;
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
            $this->nameForm = 'praSiam';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', "SIAM");
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
                $this->fileLog = sys_get_temp_dir() . "/praSiam_" . time() . ".log";
                $this->scriviLog("Avvio Programma praSiam");
                $this->gesnum_anno = array();
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->importafascicolo(1);
                        $this->gesnum_anno = array();
                        break;
                    case $this->nameForm . '_Conferma2':
                        $this->importafascicolo(2);
                        $this->gesnum_anno = array();
                        break;
                    case $this->nameForm . '_Conferma3':
                        $this->importafascicolo(3);
                        $this->gesnum_anno = array();
                        break;
                    case $this->nameForm . '_CollegaPratica':
                        $this->collegaAntecedente();
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

    private function importafascicolo($fase) {
        /*
         * lettura db sorgente
         */
        switch ($fase) {
            case 1:
                $sql_base = "SELECT * 
            FROM `cr_pratica` 
            WHERE 1=1 
           AND id <= 1400000 
            ORDER BY `cr_pratica`.`id` ASC
            ";
//                // forzatura per testtttttt
//                $sql_base = "SELECT * 
//            FROM `cr_pratica` 
//            WHERE 1=1 
//           AND id = 1463520
//            ORDER BY `cr_pratica`.`id` ASC
//            ";
                break;
            case 2:

                $sql_base = "SELECT * 
            FROM `cr_pratica` 
            WHERE 1=1 
           AND id > 1400000 and id <= 1427100
            ORDER BY `cr_pratica`.`id` ASC
            ";
                break;
            case 3:

                $sql_base = "SELECT * 
            FROM `cr_pratica` 
            WHERE 1=1 
           AND id > 1427100
            ORDER BY `cr_pratica`.`id` ASC
            ";
                break;

            default:
                Out::msgStop('ERORE', 'Nessuna fase inserita');
                return false;
                break;
        }
//        $sql_base = "SELECT * 
//            FROM `cr_pratica` 
//            WHERE 1=1 
//
//            ORDER BY `cr_pratica`.`id` ASC
//            ";
// AND id = 1474315

        $Pratiche_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql_base, true);
        $i = 0;

        foreach ($Pratiche_tab as $key => $Pratiche_rec) {

            $toinsert = $this->inserisciFascicolo($Pratiche_rec);
            if (!$toinsert) {
                $anomalie++;
                $flAnomalia = true;
            } else {

                $i++;
                if ($Pratiche_rec['flagsuap'] == 'S') {
                    $toinsert_1 = $this->creapassoSuap($Pratiche_rec['PROTSUAP'], $Pratiche_rec['dataprotsuap'], $Pratiche_rec['IDSUAP']);
                }
                if ($Pratiche_rec['stato'] == 'CL') {
                    $toinsert_2 = $this->creapassoStatoPratica($Pratiche_rec['stato'], $Pratiche_rec['esito'], $Pratiche_rec['dataconclusione']);
                }

                // $toinsert_3 = $this->creapassoSospensione($this->Id);
                $toinsert_4 = $this->creapassoAttivita($this->Id);
                $this->caricariSoggetti($Pratiche_rec['id']);
                $this->cercaALtriLegami($Pratiche_rec['id']);

                $this->caricaOperatore($Pratiche_rec['P_RESPAMMINISTRATIVO'], 'P_RESPAMMINISTRATIVO');
                $this->caricaOperatore($Pratiche_rec['P_ISTRUTTORE'], 'P_ISTRUTTORE');
                $this->importaAllegati($Pratiche_rec['id'], $this->Pratica);


////                $toinsert_2 = $this->creapassoPareri($Pratiche_rec['UTCOSRL']);
////            // RIORDINO GLI EVENTUALI PASSI CREATI
                if ($toinsert_1 || $toinsert_2 || $toinsert_3 || $toinsert_4) {
                    $this->ordinaPassi($this->Pratica);
                }
            }
        }
//  $this->collegaAntecedente();
        Out::msgInfo("OK", "<b>FASE " . $fase . " COMPLETATA</b><br>" . $i . " Fascicoli caricati");
        return true;
    }

    private function inserisciFascicolo($Dati_rec) {
//   Out::msgInfo('RECORD', print_r($Dati_rec, TRUE));

        $proges_rec = array();

        $proges_rec['GESMIGRA'] = $Dati_rec['id'];
        $this->Id = $Dati_rec['id'];  // mi salvo id per relazioni
        //  $NumProt = str_pad(substr($Dati_rec['PROTOCOLLORICHIESTA'], 0, 6), 6, "0", STR_PAD_LEFT);
        $NumProt = $Dati_rec['PROTOCOLLORICHIESTA'];
        $proges_rec['GESNPR'] = substr($Dati_rec['dataprotrichiesta'], 0, 4) . $NumProt;  // PROTOCOLLO PRATICA
        $proges_rec['GESPAR'] = 'A';
        $proges_rec['GESORA'] = "00:00";

        $segnatura = $Dati_rec['classificazione'];
        if ($Dati_rec['numfascicolo']) {
            $segnatura = $Dati_rec['classificazione'] . '/' . $Dati_rec['annofascicolo'] . '/' . $Dati_rec['numfascicolo'];
        }
        $proges_rec['GESMETA'] = $this->scriviDatiProtocollo($NumProt, $this->cleardate($Dati_rec['dataprotrichiesta']), $segnatura);


        $AnnoP = substr($Dati_rec['datainizio'], 0, 4);
        $this->Pratica = $proges_rec['GESNUM'] = $this->gestGesnum($AnnoP); //RICAVO GESNUM PROGRESSIVO PER ANNO

        $proges_rec['SERIEANNO'] = $AnnoP;         // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $this->gesnum_anno[$AnnoP];         // Serie NUMERO
        $proges_rec['SERIECODICE'] = self::$SUBJECT_SERIE_PRATICA[$Dati_rec['idtema']];         // Serie PROVENIENZA // TO DO 


        $proges_rec['GESCODPROC'] = $Dati_rec['FASCICOLOCARTACEO'];

        $proges_rec['GESTSP'] = self::SPORTELLO_DEFAULT; // SPORTELLO
        /*
         * setto le date
         */
        $proges_rec['GESDRI'] = $this->cleardate($Dati_rec['dataricezione']);
        $proges_rec['GESDRE'] = $this->cleardate($Dati_rec['datainizio']);
        $proges_rec['GESDSC'] = $this->cleardate($Dati_rec['scadenza']);
        $proges_rec['GESGIO'] = $this->cleardate($Dati_rec['P_GGSCADENZA']);

        /*
         * Tipologia procedimento
         */
        $proges_rec['GESTIP'] = self::$SUBJECT_BASE_TIPOLOGIA[$Dati_rec['idarticolo']]; // TODO anagrafica tipologia decod loto tabella ( UTTLGCO )
        if (!$proges_rec['GESTIP']) {
            $this->scriviLog('Decodifica tipologia proc non trovata ' . $Dati_rec['idarticolo']);
        }

        /*
         * Anagrafica 
         * Procedimento
         */
        $proges_rec['GESPRO'] = self::$SUBJECT_BASE_PROCEDIMENTI[$Dati_rec['idprocedimento']]; // anagrafica procedimenti
        if (!$proges_rec['GESPRO']) {
            $this->scriviLog('Decod Procedimento non trovato' . $Dati_rec['idprocedimento']);
        }

        /*
         *
         *
         */
        $proges_rec['GESOGG'] = $Dati_rec['DESCRIZIONE'];    // Oggetto


        $proges_rec['GESRES'] = self::$SUBJECT_RESPONSABILE_PROC[$Dati_rec['operatore']];
        if (!$proges_rec['GESRES']) {
            if ($Dati_rec['operatore']) {
                $this->scriviLog('decodifica responsabile proc non trovata nome:' . $Dati_rec['operatore']);
            }
            $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;
        }


        $proges_rec['GESDCH'] = $this->cleardate($Dati_rec['dataconclusione']);     // data chiusura pratica
        $proges_rec['GESNOT'] = $Dati_rec['note'];     // data chiusura pratica

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            $this->elaboradatiaggiuntivi($Dati_rec, $this->Pratica);
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
        $sql = "SELECT * FROM cr_pratica_attore  "
                . "WHERE idpratica = " . $IdPratica;

        $PratSoggetti_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        $ARRAY_SOGG = $ARRAY_LOCALI = $tmp_array = array();
        foreach ($PratSoggetti_tab as $key => $PratSoggetti_rec) {
            $sql = "SELECT * FROM an_soggettoimpresa "
                    . "WHERE (" . $this->SIAM_DB->strUpper('codfis') . " = '" . $PratSoggetti_rec['CR_PA_CODFISCALE'] . "' OR " . $this->SIAM_DB->strUpper('piva') . " = '" . $PratSoggetti_rec['CR_PA_CODFISCALE'] . "') "
                    . "AND datainizio <= '" . $PratSoggetti_rec['datavalidita'] . "'"
                    . " AND datafine >= '" . $PratSoggetti_rec['datavalidita'] . "' AND tipo = '" . $PratSoggetti_rec['CR_PA_TIPOPROPRIETARIO'] . "'";
            $ARRAY_SOGG[$key] = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, false);
//            if (count($ARRAY_SOGG[$key]) > 1) {
//                $this->scriviLog('ATTENZIONE trovati più soggetti validi ' . print_r($ARRAY_SOGG[$key], true));
//            }
            if ($PratSoggetti_rec['CR_PA_IDATTORE'] != 0) {
                $this->caricariImpianto($PratSoggetti_rec['CR_PA_IDATTORE'], $ARRAY_SOGG[$key]['NOMECOMPLETO']);
            }
            if (!$ARRAY_SOGG[$key]) {
                continue;
            }
        }
// Out::msgInfo('soggetto', print_r($ARRAY_SOGG, true));

        /*
         *      TODO da finire
         */

        if (!$ARRAY_SOGG) {
            $this->scriviLog('ATTENZIONE Nessun soggetto trovato ' . $sql);
            return false;
        }
        $richiedente_rec = array();
// foreach ($ARRAY_SOGG as $Soggetti_tab) {
        foreach ($ARRAY_SOGG as $Soggetti_rec) {
            if (!$Soggetti_rec) {
                continue;
            }
            $richiedente_rec['DESNUM'] = $this->Pratica;

            $richiedente_rec['DESNOM'] = $Soggetti_rec['NOMECOMPLETO'];
            $richiedente_rec['DESRAGSOC'] = $Soggetti_rec['RAGSOC'];

// $richiedente_rec['DESNOME'] = $Soggetti_rec['UTCOGNO'];
//$richiedente_rec['DESCOGNOME'] = $Soggetti_rec['UTCNOME'];
            $richiedente_rec['DESFIS'] = $Soggetti_rec['codfis'];
            $richiedente_rec['DESPIVA'] = $Soggetti_rec['piva'];

            $richiedente_rec['DESTEL'] = $Soggetti_rec['TELEFONO'];
            $richiedente_rec['DESCEL'] = $Soggetti_rec['CELLULARE'];
            $richiedente_rec['DESFAX'] = $Soggetti_rec['fax'];
            $richiedente_rec['DESEMA'] = $Soggetti_rec['EMAIL']; // email
            $richiedente_rec['DESSITO'] = $Soggetti_rec['www']; // Sito

            /*
             * Dati Nascita
             */
            $comune_rec = $this->ricavacomune($Soggetti_rec['CODPROVNASCITA'], $Soggetti_rec['CODCOMUNENASCITA']);
            $richiedente_rec['DESNASDAT'] = $this->cleardate($Soggetti_rec['DATANASCITA']); // giradata
            $richiedente_rec['DESNASCIT'] = $comune_rec['COMUNE'];
            $richiedente_rec['DESNASPROV'] = $comune_rec['PROVINCIA'];

            $richiedente_rec['DESNOTE'] = $Soggetti_rec['id'];




//                $richiedente_rec['DESIND'] = $Soggetti_rec['UTANVIA'];
//                /*
//                 * ricavo il civico
//                 */
//                $indirizzoTmp = explode(" ", $Soggetti_rec['UTANVIA']);
//                $i = count($indirizzoTmp);
//                $richiedente_rec['DESCIV'] = $indirizzoTmp[$i - 1];
//                /*
//                 */
//                //     $comuneResid = $this->ricavaComune($Soggetti_rec['UTRESID']);
//                $richiedente_rec['DESCIT'] = $comuneResid['citta'];
//                $richiedente_rec['DESCAP'] = $comuneResid['cap'];
//                $richiedente_rec['DESPRO'] = $comuneResid['prov'];
//                

            /*
             * Determino il Ruolo
             */

            $richiedente_rec['DESRUO'] = self::$SUBJECT_RUOLO_SOGGETTO[$Soggetti_rec['tipo']];
            if (!$richiedente_rec['DESRUO']) {
                $this->scriviLog('nessun ruolo soggetto in anagrafica trovato per ' . $Soggetti_rec['tipo']);
            }

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $richiedente_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Richiedente ID" . $Soggetti_rec['UTANSRL'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
// }
        }
        return true;
    }

    private function caricaOperatore($operatore, $tipo) {
        if (!$operatore) {
            return false;
        }

        $richiedente_rec = array();

        $richiedente_rec['DESNUM'] = $this->Pratica;

        $richiedente_rec['DESRAGSOC'] = str_replace('.', ' ', $operatore);


        if ($tipo == 'P_RESPAMMINISTRATIVO') {
            $richiedente_rec['DESRUO'] = 9004;
        } elseif ($tipo == 'P_ISTRUTTORE') {
            $richiedente_rec['DESRUO'] = 9003;
        }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'ANADES', 'ROWID', $richiedente_rec);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Record cr_pratiche soggetto ID" . print_r($richiedente_rec, true) . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }

        return true;
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

    private function creapassoAttivita($Id) {
        //   $this->scriviLog('CHIAMO CREAPASSOATTIVITA()' . date("Y-m-d H:i:s"));
        $Passi_tab = array();
        $sql = "SELECT cr_attivita.*,cf_tipoattivita.nome,cf_tipoattivita.descrizione FROM cr_attivita"
                . " LEFT JOIN cf_tipoattivita ON cf_tipoattivita.id = cr_attivita.idtipoattivita "
                . " WHERE idpratica = $Id";
        $Passi_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        //  $this->scriviLog('FINE SQL CREAPASSOATTIVITA()' . date("Y-m-d H:i:s"));

        if (!$Passi_tab) {
            return false;
        }

        foreach ($Passi_tab as $Passi_rec) {
            //  $this->scriviLog('CICLO RECORD'.date("Y-m-d H:i:s"));


            $propak = $this->praLib->PropakGenerator($this->Pratica);
            //  $this->scriviLog('GENERO PROPAK'.date("Y-m-d H:i:s"));

            $passo = array(
                'PRONUM' => $this->Pratica,
                'PROSEQ' => 0,
                //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
                'PRORPA' => self::$SUBJECT_RESPONSABILE_PROC[$Passi_rec['OPERATORE_RESP']], // todo responsabile
                'PROANN' => $Passi_rec['id'],
                'PRODPA' => $Passi_rec['nome'] . ' (' . $Passi_rec['descrizione'] . ')', // nome passo
                'PROINI' => $this->cleardate($Passi_rec['datainizio']), // data apertura passo
                'PROFIN' => $this->cleardate($Passi_rec['datafine']), // data chiusura passo
                'PRODSC' => $this->cleardate($Passi_rec['scadenza']), // data scadenza
                'PROPAK' => $propak,
                'PROCLT' => '900005', // tipo passo
                'PRODTP' => self::$SUBJECT_PROCLT['900005'], // descrizione tipo passo  
                'PROSTATO' => $Passi_rec['esito'], // descrizione tipo passo  
            );
            if (!$passo['PRORPA']) {
                //    $this->scriviLog('Decodifica responsabile passo attivita non riuscita ' . $Passi_rec['OPERATORE_RESP']); // TODO
                $passo['PRORPA'] = self::RESPONSABILE_DEFAULT;
            }


            if ($Passi_rec['idresponsabile'] != 0) {
                /* cerco responsabile 
                 * Scadenza
                 */
                $sqlR = "SELECT descrizione FROM an_ente WHERE id = " . $Passi_rec['idresponsabile'];
                $Responsabile_rec = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlR, false);
                $passo['PRODPA'] .= ' Responsabile Scadenza ' . $Responsabile_rec['descrizione'];
            }


            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                if ($Passi_rec['note'] && $Passi_rec['note'] != ' ') {
                    $rowid_pass = ItaDb::DBLastId($this->PRAM_DB);  // mi ritorn ail rowid della insert
                    $this->creanotepasso($Passi_rec['note'], $propak, $rowid_pass);
                }
                /*
                 *  TODO CRECO ALLEGATI
                 */
                $this->importaAllegati($this->Id, $propak, $Passi_rec['id']);
                $this->cercaScarico($Passi_rec['id'], $propak);

                //  $this->scriviLog('IMPORTO ALLEGATO CREAPASSOATTIVITA()'.date("Y-m-d H:i:s"));
            } catch (Exception $ex) {
                $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function creanotepasso($note, $passo, $rowid_pass) {
        //$rowidPropas = $this->getRowid('PROPAS', $where);
        if (!$rowid_pass) {
            $testo = "Fatal: Errore Inserimento note passo, nessun rowid passo recuperato per fascicolo N " . $this->Pratica;
            $this->scriviLog($testo);
            return false;
        }

        $Note = array(
            'OGGETTO' => "NOTE PASSO ", //. $passo['PROSEQ']
            'TESTO' => $note,
            'UTELOG' => 'italsoft',
            'UTELOGMOD' => 'italsoft'
        );
        //Out::msgInfo("note", print_r($Note,true));
        $toinsert = $this->insertRecord($this->PRAM_DB, 'NOTE', $Note, $insert_Info);

        if (!$toinsert) {
            $testo = "Fatal: Errore Inserimento NOTE rec per note passo " . $passo['PROPAK'] . ", fascicolo N " . $this->Pratica;
            $this->scriviLog($testo);
            return false;
        }
        $rowidNote = ItaDB::DBLastId($this->PRAM_DB);

        $NoteClas = array(
            'CLASSE' => 'PROPAS',
            'ROWIDCLASSE' => $rowid_pass, //PROPAS.ROWID
            'ROWIDNOTE' => $rowidNote
        );
        $toinsert = $this->insertRecord($this->PRAM_DB, 'NOTECLAS', $NoteClas, $insert_Info);

        if (!$toinsert) {
            $testo = "Fatal: Errore Inserimento noteclas rec per NOTECLAS passo " . $passo['PROPAK'] . ", fascicolo N " . $this->Pratica;
            $this->scriviLog($testo);
            return false;
        }

        return true;
    }

    private function elaboradatiaggiuntivi($Valori_rec, $propak, $tipo = '') {
        foreach ($Valori_rec as $key => $datoaggiuntivo) {

            if (strstr($key, 'Data') || $key == 'DecorrenzaIstruttore') {
                $appoggio_data = $this->cleardate($datoaggiuntivo);
                $datoaggiuntivo = $appoggio_data;
            }

            if ($tipo == 'IMPIANTI') {
                $key = 'IMPIANTO_' . $key;
            }
            $dati = array(
                'DAGNUM' => $this->Pratica,
                'DAGDES' => $key,
                'DAGLAB' => $key,
                'DAGKEY' => strtoupper($key),
                'DAGVAL' => $datoaggiuntivo,
                'DAGSET' => $propak . '_01',
                'DAGPAK' => $propak
            );
//            if ($key == 'Lavori' || $key == 'Oggetto' || strstr($key, 'Testo') || strstr($key, 'Annotazioni')) {
//                $dati['DAGTIC'] = 'TextArea'; // TEXT AREA
//            }
            if (strstr($key, 'data')) {
                $dati['DAGTIC'] = 'Data';
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

    private function creapassoSospensione($Id) {
//SELECT * FROM `cr_sospensione` 

        /*
         * passi fatti
         */

//$sql = "SELECT * FROM UTRIAPP WHERE UTRIIDP = 4630 ORDER BY `UTRIRSM` ASC";
        $sql = "SELECT * FROM `cr_sospensione` WHERE idpratica = $Id";
        $Passi_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        if (!$Passi_tab) {
            return false;
        }

        foreach ($Passi_tab as $Passi_rec) {

// $seq = 0;
            $propak = $this->praLib->PropakGenerator($this->Pratica);
            $passo = array(
                'PRONUM' => $this->Pratica,
                'PROSEQ' => 0,
                //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
                'PRORPA' => self::RESPONSABILE_DEFAULT, // todo responsabile
                'PROANN' => $Passi_rec['id'],
                'PRODPA' => $Passi_rec['motivo'], // nome passo
                'PROINI' => $this->cleardate($Passi_rec['datainizio']), // data apertura passo
                'PROFIN' => $this->cleardate($Passi_rec['datafine']), // data chiusura passo
                'PROPAK' => $propak,
                'PROCLT' => '900003', // tipo passo
                'PRODTP' => self::$SUBJECT_PROCLT['900003'], // descrizione tipo passo  
            );

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

    private function creapassoSuap($Protocollo, $DataProtocollo, $IdUfficio) {

        if ($IdUfficio != 0) {
            /*
             * vado a cercare l'ente
             */
            $sql = "SELECT descrizione FROM an_ente WHERE id  = $IdUfficio";
            $ente_rec = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, false);
        }



        $propak = $this->praLib->PropakGenerator($this->Pratica);
        $passo = array(
            'PRONUM' => $this->Pratica,
            'PROSEQ' => 0,
            //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
            'PRORPA' => self::RESPONSABILE_DEFAULT, // responsabile
            'PROANN' => '',
            'PRODPA' => 'Pratica Suap', // nome passo
            'PROINI' => $this->cleardate($DataProtocollo), // data apertura passo
            // 'PROFIN' => '', // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => '900001', // tipo passo
            'PRODTP' => 'Pratica Suap', // descrizione tipo passo  
        );
        $passo['PROFIN'] = $passo['PROINI'];
        $EnteComunicazione = '';
        if ($ente_rec['descrizione']) {
            $passo['PRODPA'] .= ' - Uffico di competenza ' . $ente_rec['descrizione'];
            $EnteComunicazione = $ente_rec['descrizione'];
        }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
            $this->scriviComunicazione($propak, $passo['PROINI'], $Protocollo, $EnteComunicazione);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }

        return true;
    }

    private function creapassoStatoPratica($StatoPratica, $Esito, $DataChiusura) {

        $propak = $this->praLib->PropakGenerator($this->Pratica);
        $PraSta = array();
        $passo = array(
            'PRONUM' => $this->Pratica,
            'PROSEQ' => 99,
            //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
            'PRORPA' => self::RESPONSABILE_DEFAULT, // responsabile
            'PROANN' => '',
            'PRODPA' => 'Chiusura pratica n. ' . $this->Pratica, // nome passo
            'PROINI' => $this->cleardate($DataChiusura), // data apertura passo
            // 'PROFIN' => '', // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => '', // tipo passo
            'PRODTP' => 'Chiusura pratica', // descrizione tipo passo  
        );
        $passo['PROFIN'] = $passo['PROINI'];

        switch ($Esito) {
            case 0:
                $passo['PROSTATO'] = $PraSta['STACOD'] = 1; //incorso
                $PraSta['STAFLAG'] = 'In corso';
                $PraSta['STADES'] = 'In corso';
                break;
            case 1:
                $passo['PROSTATO'] = $PraSta['STACOD'] = 2; // positivo
                $PraSta['STAFLAG'] = 'Chiusa Positivamente';
                $PraSta['STADES'] = 'Pratica chiusa positivamente';
                break;
            case 2:
                $passo['PROSTATO'] = 22;  // da verificare TODO
                break;
            case 3:
                $passo['PROSTATO'] = 33; // da verificare TODO
                break;
            case 4:
                $passo['PROSTATO'] = 44; // da verificare TODO
                break;
            case 5:
                $passo['PROSTATO'] = 55; // da verificare TODO
                break;
            case 6:
                $passo['PROSTATO'] = 66; // da verificare TODO
                break;
        }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }


        if (!$PraSta['STAFLAG']) {
            return true;
        }
        $PraSta['STANUM'] = $this->Pratica;
        $PraSta['STAPAK'] = $propak;
        $PraSta['STADIN'] = $PraSta['STADFI'] = $passo['PROINI'];

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRASTA', 'ROWID', $PraSta);
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione PRASTA per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function scriviComunicazione($ProPak, $data, $Prot, $Destinatario = '') {
        if (!$Prot) {
            return false;
        }

        $Pracom_rec = array(
            'COMTIP' => 'A',
            'COMNUM' => $this->Pratica,
            'COMPAK' => $ProPak,
            'COMDPR' => $this->cleardate($data),
            'COMNOM' => '', // soggetto
            'COMNOT' => '',
        );
        if ($Prot) {
            $Pracom_rec['COMPRT'] = substr($data, 0, 4) . $Prot;
        }
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PRACOM', 'ROWID', $Pracom_rec);
            $RowidPracom = ItaDb::DBLastId($this->PRAM_DB);
            if ($Destinatario) {
                $Pramitdest = array(
                    'KEYPASSO' => $ProPak,
                    'TIPOCOM' => 'M',
                    'NOME' => $Destinatario,
                );
                if (!ItaDB::DBInsert($this->PRAM_DB, 'PRAMITDEST', 'ROWID', $Pramitdest)) {
                    $this->scriviLog('Errore inserimento mittente comunicazione ' . print_r($Pramitdest, true));
                }
            }
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per parere" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return $RowidPracom;
    }

    private function importaAllegati($IdPrat, $Propak, $IdAttivita = '') {
        if (!$IdPrat) {
            return false;
        }

        $sql = "SELECT * FROM cr_allegato WHERE IDPRATICA = '$IdPrat'";
        if ($IdAttivita) {
            $sql .= " AND IDATTIVITA = " . $IdAttivita;
        } else {
            $sql .= " AND IDATTIVITA = 0";
        }
        $Allegati_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        if (!$Allegati_tab) {
            return false;
        }
        foreach ($Allegati_tab as $Allegati_rec) {
            $Propas_rec = array();

            $Propas_rec['PASKEY'] = $Propak;

            if ($IdAttivita && $Allegati_rec['NUMPROTOCOLLO']) {
                $RowidPracom = $this->scriviComunicazione($Propak, $Allegati_rec['dataprotocollo'], $Allegati_rec['NUMPROTOCOLLO']);
                $Propas_rec['PASPRTROWID'] = $RowidPracom;
                $Propas_rec['PASPRTCLASS'] = 'PRACOM';
            }

            $rand = md5(uniqid()) . '.' . pathinfo($Allegati_rec['NOMEFILE'], PATHINFO_EXTENSION);
            $Propas_rec['PASFIL'] = $rand;
            $Propas_rec['PASMIGRA'] = $Allegati_rec['AL_NOMEFILESYSTEM'];
            $Propas_rec['PASNAME'] = $Allegati_rec['NOMEFILE']; // nome fisico file 
            $Propas_rec['PASNOT'] = $Allegati_rec['descrizione'];
            $Propas_rec['PASLNK'] = $Allegati_rec['id']; // mi salvol'id
            $Propas_rec['PASCLA'] = "GENERALE";
            $Propas_rec['PASPUB'] = 0; // blocca pubblicazione sul F.O
            $Propas_rec['PASCLA'] = 'ESTERNO';

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $Propas_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Allegato ID" . $Allegati_rec['UTCWSRL'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }

        return true;
    }

    private function cercaScarico($IdAttivita, $PropakAntecedente) {
        if (!$IdAttivita) {
            return false;
        }

        $sql = "SELECT * FROM ac_scaricoidrico WHERE idattivita = '$IdAttivita'";

        $PassiScarichi_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        if (!$PassiScarichi_tab) {
            return false;
        }


        foreach ($PassiScarichi_tab as $Passi_rec) {

            $propak = $this->praLib->PropakGenerator($this->Pratica);

            $passo = array(
                'PRONUM' => $this->Pratica,
                'PROSEQ' => 0,
                //  'PROPRO' => self::PROCEDIMENTO_DEFAULT,
                'PROANN' => $Passi_rec['id'],
                'PRODPA' => 'Scarico numero '.$Passi_rec['numero'], // nome passo
               // 'PROINI' => $this->cleardate($Passi_rec['datainizio']), // data apertura passo
               // 'PROFIN' => $this->cleardate($Passi_rec['datafine']), // data chiusura passo
               // 'PRODSC' => $this->cleardate($Passi_rec['scadenza']), // data scadenza
                'PROPAK' => $propak,
               // 'PROCLT' => '900005', // tipo passo
                'PRORPA' => self::RESPONSABILE_DEFAULT, // descrizione tipo passo  
                //'PROSTATO' => $Passi_rec['esito'], // descrizione tipo passo  
                'PROKPRE' => $PropakAntecedente, // descrizione tipo passo  
            );
           



            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                $this->elaboradatiaggiuntivi($Passi_rec, $propak);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore creazione passo scarico per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
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

    private function collegaAntecedente() {

        $sql = "SELECT id,idpraticapadre FROM cr_pratica ORDER BY id";
        //$sql = "SELECT id,idpraticapadre FROM cr_pratica WHERE id = 1474315 ORDER BY id";
        $Collegamenti_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        $i = 0;

        foreach ($Collegamenti_tab as $Collegamenti_rec) {
            if ($Collegamenti_rec['idpraticapadre'] == 0) {
                continue;
            }
            $sql = "SELECT GESNUM FROM PROGES WHERE GESMIGRA = " . $Collegamenti_rec['idpraticapadre'];
            $Padre_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if (!$Padre_rec) {
                $this->scriviLog('nessun record trovato PADRE ' . $sql);
                continue;
            }
            $sql = "SELECT ROWID FROM PROGES WHERE GESMIGRA = " . $Collegamenti_rec['id'];
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

    private function caricariImpianto($Id, $Riferimento) {
        $sql = "SELECT * FROM an_impiantotrattamento WHERE IT_ID = $Id";
//  return;
        $Impianto_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        if (!$Impianto_tab) {
            return false;
        }
        if (count($Impianto_tab) > 1) {
            $this->scriviLog("Attenzione Doppio Impianto trovato " . $sql);
        }

        foreach ($Impianto_tab as $Impianto_rec) {
            $comune_rec = $this->ricavacomune($Impianto_rec['IT_CODPROV'], $Impianto_rec['IT_CODCOMUNE']);

            $localita_rec = array(
                'DESNUM' => $this->Pratica,
                //  'DESRUOEXT' => $Impianto_rec['IT_DESCRIZIONE'],
                'DESRAGSOC' => $Riferimento,
                'DESIND' => $Impianto_rec['IT_DESCRIZIONE'] . ' ' . $Impianto_rec['IT_LOCALITA'],
                'DESCIV' => '',
                'DESCAP' => $Impianto_rec['IT_CAP'],
                'DESCIT' => $comune_rec['COMUNE'],
                'DESPRO' => $comune_rec['PROVINCIA'],
                'DESRUO' => '0014',
                'DESNOTE' => 'Impo'
            );
            $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_rec, $insert_Info);
            $this->elaboradatiaggiuntivi($Impianto_rec, $this->Pratica, 'IMPIANTI');
        }

        return true;
    }

    private function cercaALtriLegami($Id) {
        $sql = "SELECT * FROM cr_entita_comune WHERE EC_IDENTITA = $Id";
//  return;
        $AltriLegami_tab = ItaDB::DBSQLSelect($this->SIAM_DB, $sql, true);
        if (!$AltriLegami_tab) {
            return false;
        }


        foreach ($AltriLegami_tab as $AltriLegami_rec) {
            $sqlC = "SELECT denominazione,cap FROM comuni WHERE provincia_stato = '" . $AltriLegami_rec['EC_CODPROV'] . "' AND comune = '" . $AltriLegami_rec['EC_CODCOMUNE'] . "'";

            $Comune_rec = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlC, false);

            $sqlP = "SELECT denominazione,SIGLA FROM province WHERE provincia = '" . $AltriLegami_rec['EC_CODPROV'] . "'";
            $Provincia_rec = ItaDB::DBSQLSelect($this->SIAM_DB, $sqlP, false);

            $localita_rec = array(
                'DESNUM' => $this->Pratica,
                'DESIND' => 'Altri Legami',
                'DESCIV' => '',
                'DESCAP' => $Comune_rec['cap'],
                'DESCIT' => $Provincia_rec['denominazione'] . ' - ' . $Comune_rec['denominazione'],
                'DESPRO' => $Provincia_rec['SIGLA'],
                'DESRUO' => '0014',
                'DESNOTE' => 'Impo'
            );
            $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_rec, $insert_Info);
        }

        return true;
    }

    private function scriviDatiProtocollo($NumProt, $DataProt, $segnatura) {
        $gesmetaEsempio = '';
        $gesmeta_tmp = array();
        $gesmetaEsempio = 'a:1:{s:19:"DatiProtocollazione";'
                . 'a:6:{s:14:"TipoProtocollo";'
                . 'a:3:{s:5:"value";s:11:"Italsoft-ws";'
                . 's:6:"status";b:1;s:3:"msg";'
                . 's:21:"ProtocollazioneArrivo";}'
                . 's:6:"proNum";'
                . 'a:3:{s:5:"value";s:6:"' . str_pad(substr($NumProt, 0, 6), 6, "0", STR_PAD_LEFT) . '";'
                . 's:6:"status";b:1;s:3:"msg";s:0:"";}'
                . 's:4:"Data";a:3:{s:5:"value";s:8:"' . $DataProt . '";'
                . 's:6:"status";b:1;s:3:"msg";s:0:"";}'
                . 's:9:"DocNumber";a:3:{s:5:"value";s:4:"4416";s:6:"status";b:1;s:3:"msg";s:0:"";}'
                . 's:9:"Segnatura";a:3:{s:5:"value";s:37:"0000640-04/10/2019-PG-SUAP-00080004-A";'
                . 's:6:"status";b:1;s:3:"msg";s:0:"";}s:4:"Anno";'
                . 'a:3:{s:5:"value";s:4:"' . substr($DataProt, 0, 4) . '";s:6:"status";b:1;s:3:"msg";s:0:"";}}}';
        $gesmeta_tmp = unserialize($gesmetaEsempio);

        $gesmeta_tmp['DatiProtocollazione']['Segnatura'] = $segnatura;
        // unset($gesmeta_tmp['DatiProtocollazione']['DocNumber']); // rowid documento ??????
        serialize($gesmeta_tmp);
        return serialize($gesmeta_tmp);
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
  TRUNCATE `NOTE`;
  TRUNCATE `NOTECLAS`;
  TRUNCATE `PRAMITDEST`;
  TRUNCATE `PRASTA`;
 */
