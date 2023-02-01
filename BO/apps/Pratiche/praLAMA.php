<?php

/**
 *  Programma Popolamento sue fabriano LAMA Edilizia
 *
 *
 * @category   programma importazione dati
 * @package    
 * @author     Tania Angeloni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    12.01.2018
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praLAMA() {
    $intSuapSbt = new praLAMA();
    $intSuapSbt->parseEvent();
    return;
}

class praLAMA extends itaModel {

    const SPORTELLO_SUE_DEFAULT = 6;
    const SPORTELLO_SUAP_DEFAULT = 5; // DA VERIFICARE
    const DESRUOIMPRESA_DEFAULT = '0012';
    const DESRUOTECNICO_DEFAULT = '0007';
    const DESRUORICHIEDENTE_DEFAULT = '0002';
    const PATH_ALLEGATI = '/home/italsoft/files_lama';

    static $SUBJECT_BASE_LETTERA = array(
        "LAMAPE" => "1",
        "LAMAUC" => "2",
        "LAMASAEP" => "3",
        "LAMAAMB" => "4",
        "LAMASU01" => "5",
        "LAMACE" => "6",
        "PRATICHE-EDILZIE" => "7",
    );
    static $RUOLO_SOGGETTO = array(
        "Esecutore dei lavori" => "0012",
        "Richiedente" => "0002",
        "Corichiedente" => "0001",
        "Direttore Lavori" => "0010",
        "Progettista" => "0007",
        "Ditta Sub-Appaltatrice" => "0016",
        "Dir. Lav. Strutturale" => "0010",
        "Prog./Dir. dei lavori" => "0010",
        "Collaudatore" => "0006",
        "Prog./Dir Strutturale" => '0010',
        "Progettista Strutturale" => '0010'
    );

    const RESPONSABILE_DEFAULT = '000029';  // DA MODIFICARE ANCHE SU responsabile[144] e responsabile[0]

    static $RESPONSABILE = array(
        "248" => "000030",
        "420" => "000030",
        "568" => "000030",
        "523" => "000031",
        "228" => "000032",
        "151" => "000033",
        "453" => "000034",
        "412" => "000035",
        "193" => "000035",
        "450" => "000036",
        "166" => "000036",
        "451" => "000037",
        "377" => "000038",
        "123" => "000039",
        "454" => "000039",
        "154" => "000040",
        "715" => "000041",
        "128" => "000042",
        "146" => "000042",
        "304" => "000043",
        "127" => "000043",
        "242" => "000044",
        "490" => "000045",
        "188" => "000046",
        "640" => "000047",
        "126" => "000048",
        "602" => "000049",
        "409" => "000050",
        "169" => "000051",
        "124" => "000052",
        "121" => "000053",
        "167" => "000054",
        "301" => "000055",
        "144" => "000029",
        "0" => "000029",
        "225" => "000056",
        "757" => "000057",
    );
    static $ONERI = array(
        "Oneri di Urbanizzazione Primaria" => '1',
        "Oneri di Urbanizzazione Secondaria" => '2',
        "Costo di Costruzione (Bucalossi)" => '3',
        "Costo di Costruzione (Tur/Commerciale)" => '4',
        "Oblazione ai sensi art.13 L.47/85" => '5',
        "Importo a Perizia" => '6',
        "Sanzioni ai sensi art.3 L. 47/85" => '7'
    );
    static $SUBJECT_BASE_PROCEDIMENTO = array(
        "Concessione Edilizia/Permesso di Costruire" => "910022",
        "Concessione Edilizia in Sanatoria - Condono" => "910023",
        "Certificato di Conformit? al P.R.G." => "910024",
        "Concessione per insegne" => "910025",
        "Pareri Preventivi" => "910026",
        "Pratiche per 2? Commissione" => "910027",
        "Autorizzazione edilizia" => "910028",
        "Prova" => "910029",
        "Concessione Edilizia in zona Agricola" => "910030",
        "SISMA - Autorizzazione edilizia D.C.D. 121/97" => "910031",
        "Autorizzazione allo scarico - Processi Produttivi" => "910032",
        "SISMA - Autorizzazione edilizia D.C.D. 647/98" => "910033",
        "Autorizzazione allo scarico - Altro" => "910034",
        "DOCUP - OB 5B - Beni Culturali Privati" => "910035",
        "SISMA - Autorizzazione/Concessione Art. 4 L. 61/98" => "910036",
        "SISMA - Parere preventivo su demolizione e delocalizzazione L. 61/98" => "910037",
        "SISMA - Autorizzazione/Concessione Art. 3 L. 61/98 Interventi Unitari" => "910038",
        "Recupero IVA Legge 449/97" => "910039",
        "Denuncia di Inizio Attivit" => "910040",
        "SISMA - Lavori eseguiti in anticipazione - D.G.R. 2708/99 e 3369/99" => "910041",
        "DOCUP - VARIANTE - OB 5B - Beni Culturali Privati" => "910042",
        "SISMA - Piano di Recupero" => "910043",
        "SISMA - Autorizzazione D.G.R. 1891/99" => "910044",
        "Autorizzazione allo scarico - Servizi Igienici" => "910045",
        "SISMA - Variante Art. 4 L. 61/98" => "910046",
        "SISMA - Variante Art. 3 L. 61/98 Interventi Unitari" => "910047",
        "Autorizzazione allo scarico - Uso Domestico" => "910048",
        "Autorizzazione allo scarico - Raffreddamento" => "910049",
        "Agibilit" => "910050",
        "Certificato di Destinazione Urbanistica" => "910051",
        "Autorizzazione apertura suolo pubblico" => "910052",
        "Autorizzazione Edilizia Targhe" => "910053",
        "Autorizzazione Paesaggistica" => "910054",
        "Abusi Edilizi" => "910055",
        "Comunicazione Inizio Lavori Asseverata" => "910056",
        "Autorizzazione a Lottizzare" => "910057",
        "Segnalazione Certificata di Inizio Attivit" => "910058",
        "COMPATIBILITA' AMBIENTALE" => "910059",
        "COMPATIBILITA PAESAGGISTICA CARTELLI" => "910060",
        "Procedura Abilitativa Semplificata" => "910061",
        "Attivit? Edilizia Libera Impianti Energetici" => "910062",
        "Comunicazione Cambio di destinazione d'uso senza opere." => "910063",
        "RICHIESTA DI ACQUISIZIONE ATTI DI ASSENSO" => "910064",
        "Certificato di Destinazione d'Uso" => "910065",
        "Comunicazione Inizio Lavori" => "910066",
        "Comunicazione lavori (L.R. 17/2015)" => "910067",
        "SISMA - Pratica agibilit? (Situaz. definitiva)" => "910068",
        "SISMA - Pratica Agibilit? (Residenziale Privata)" => "910069",
        "Realizzazione" => "910070",
        "Autorizzazione allo scarico - Processi Produttivi" => "910071",
        "Autorizzazione allo scarico - Uso domestico" => "910072",
        "Autorizzazione allo scarico - Servizi Igienici" => "910073",
        "Tipo di intervento soggetto a singola autorizzazione o comunicazione" => "910074",
        "Scheda n? 33) - Imp. Elettrici - di Messa a Terra - Denuncia di 1? Imp." => "910075",
        "Autorizzazione allo scarico - Altro" => "910076",
        "Scarico acque reflue nei corpi d'acqua superficiali, suolo o sottosuolo" => "910077",
        "Riconversione" => "910078",
        "Gestione Rifiuti Abbandonati" => "910079",
        "Residenziale - Abitazione" => "910080",
        "Residenziale - Accessori" => "910081",
        "Agricolo - Accessori" => "910082",
        "Commerciale" => "910083",
        "Artigianale" => "910084",
        "Altre Opere" => "910085",
        "Pratica di Condono Edilizio 1985" => "910086",
        "Opere Interne" => "910087",
        "Pozzi idrici" => "910088",
        "Ristrutturazione" => "910089",
        "Scheda n? 01) - Permesso di costruire" => "910090",
        "Riattivazione" => "910091",
        "Scheda n? 99) - Procedimento comportante variante urbanistica" => "910092",
        "Ampliamento" => "910093",
        "SISMA - Domanda di contributo D. 121/97" => "910094",
        "Scheda n? 17) - Valutazione di Impatto Ambientale" => "910095",
        "SISMA - Edificio" => "910096",
        "Autorizzazione in deroga L.R. 28/2001 per manifestazioni rumorose" => "910097",
        "Tipologia di prova" => "910098",
        "TIPOLOGIA DI PROVA" => "910098",
        "SISMA - Riapertura termini D.G.R. 3369/99" => "910099",
        "Autorizzazione abbattimento piante protette" => "910100",
        "AUTORIZZAZIONE UNICA AMBIENTALE" => "910101",
        "CONDONO L.R.N?23/2004" => "910102",
        "Scheda n? 19) - Acque reflue nella Pubblica Fognatura" => "910103",
        "Autorizzazione allo scarico - Raffreddamento" => "910104",
        "Scheda n? 18) - Inquinamento Atmosferico (DPR 203/1988)" => "910105",
        "Scheda n? 55) - Prevenzione Incendi" => "910106",
        "Scheda n? 51) - Piano di Sicurezza e Coordinamento" => "910107",
        "Industriale" => "910108",
        "Agricolo - Abitazioni" => "910109",
        "Scheda n? 10) - Allacciamento alla rete dell'acqua" => "910110",
        "Scarico acque reflue nella pubblica fognatura" => "910111",
        "Scheda n? 23) - Emissioni in Atmosfera" => "910112",
        "Opere ad uso Pubblico" => "910113",
        "Opere per Att. Turistico - Ricett. e Agritur." => "910114",
        "Opere Religiose o a servizio del Culto" => "910115",
        "Attivita' Sportive - Culturali" => "910116",
        "Scheda n? 97) - Impianti radioelettrici con pot.za in antenna =< 20 Watt" => "910117",
        "SISMA - Rich. contributo art. 4  L. 61/98 (beni mobili)" => "910118",
        "Situazione MAM" => "910119",
        "SISMA - Domanda L. 61/98 fuori termine" => "910120",
        "Autorizzazione all'Attivit? Estrattiva" => "910121",
        "SISMA - Riapertura termini D.G.R. 1658/02" => "910122",
        "Scheda n? 00) - Tipologia di prova" => "910123",
        "Scheda n? 37) - Impianti Tecnologici" => "910124",
        "Elettrodotti AT" => "910125",
        "ordinanza amianto" => "910126",
        "Bonifiche siti inquinati" => "910127",
        "OPCM 4007/2012" => "910128",
        "Cessazione" => "910129",
        "SISMA 2016 - CILA Art. 8 L. 229/16 Ordinanza n? 4/2016 (Ric. leggera)" => "910130"  /// NEW PROCEDIMENTO DA INSERIRE IN ANAGRAFICA
    );
    public $praLib;
    public $PRAM_DB;
    public $LAMA_DB;
    public $LAMA2_DB;
    public $fileLog;
    public $nameForm;
    public $Id;
    public $Pratica;
    public $gesnum_anno = array();
    public $relazioni_2 = array();
    public $time = '';
    public $tempo;

    function __construct() {
        parent::__construct();
        try {
            /*
             * carico le librerie
             * 
             */
            $this->nameForm = 'praLAMA';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');

            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->LAMA_DB = ItaDB::DBOpen('LAMA', "");
            $this->LAMA2_DB = ItaDB::DBOpen('LAMA2', "");
            $this->Id = App::$utente->getKey($this->nameForm . '_Id');
            $this->Pratica = App::$utente->getKey($this->nameForm . '_Pratica');
            $this->gesnum_anno = App::$utente->getKey($this->nameForm . '_gesnum_anno');
            $this->relazioni_2 = App::$utente->getKey($this->nameForm . '_relazioni_2');
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
            App::$utente->setKey($this->nameForm . '_relazioni_2', $this->relazioni_2);
        }
        $this->scriviLog($this->time);
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praLAMA_" . time() . ".log";
                $this->scriviLog("Avvio Programma praLAMA");
                Out::html($this->nameForm . "_Lama_Modulo", "");
                Out::select($this->nameForm . '_Lama_Modulo', 1, "LAMAPE", "0", "LAMAPE");
                Out::select($this->nameForm . '_Lama_Modulo', 1, "LAMANNPE", "0", "LAMANNPE Parte 1");
                Out::select($this->nameForm . '_Lama_Modulo', 1, "LAMANNPE_2", "0", "LAMANNPE Parte 2");
                Out::select($this->nameForm . '_Lama_Modulo', 1, "LAMAPE_2", "0", "LAMAPE Old");
                Out::html($this->nameForm . "_divInfo", "<br>QUERY UNICITA' <br>SERIE: select * from (select count(ROWID) AS QUANTI, SERIEANNO, SERIEPROGRESSIVO, SERIECODICE FROM PROGES GROUP BY SERIEANNO, SERIEPROGRESSIVO, SERIECODICE)A WHERE A.QUANTI>1");
                Out::html($this->nameForm . "_divInfo2", "<br><p  style='color:red'>GUIDA IMPORTAZIONE:<p><BR> "
                        . "<p><b>TRA L'IMPORTAZIONE DI UNA TABELLA/SERIE E L'ALTRA NON CHIUDERE E RIAVVIARE IL PROGRAMMA DI POPOLAMENTO PER NON AZZERARE LE VARIABILI D'AMBIENTE UTILIZZATE!</b><p><br>"
                        . "<p>1° Nella Select 'Seleziona la tabella da importare' andare in sequenza LAMAPE, LAMANNPE parte1, LAMANNPE parte 2, come da ordinamento select<br>"
                        . "<br>2° Al termine della terza importazione OVVERO dalla tabella LAMANNPE parte 2 Collegare Pratiche Antecedenti (DA BTTN).<br>"
                        . "<br>3° Al termine di COLLEGA PRATICHE ANTECEDENTI Avviare Elaborazione per tabella da importare LAMAPE OLD</p>"
                        . "<br>4° Infine Carica Allegati Fisici</p>"
                        . "CARICA ALLEGATI FISICI FA UN SELECT * SU PASDOC quindi valutare se modificare la query e mettere un rowid limite</p><br>");

                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $modulo = $_POST[$this->nameForm . '_Lama_Modulo'];
                        Out::msgInfo("Importazione per", $modulo);
                        switch ($modulo) {
                            case "LAMAPE":
                                $this->importafascicoloPE();
                                break;
                            case "LAMANNPE":
                                $this->importafascicoliNNPE('1');
                                break;
                            case "LAMANNPE_2":
                                $this->importafascicoliNNPE('2');
                                break;
                            case "LAMAPE_2":
                                $this->importafascicoloPEOLD();
                                break;

                            default:
                                Out::msgInfo("Attenzione", "Errore nell'individuazione della serie selezionata");
                                break;
                        }
                        break;
                    case $this->nameForm . '_Antecedenti':
                        $this->cercaantecedente();  // ricerco e collego i fascicoli
                        break;
                    case $this->nameForm . '_AttiAmministrativi':
                        $this->cercaantecedente_attiAmm();  // ricerco e metto le note per i procedimenti amministrativi non trovati nella tabella antecedenti vado a controllare in una secionda tabella
                        break;
                    case $this->nameForm . '_RilevaPraCOm':
                        //$this->rilevaPraCom();  // mette gli allegati di  passo e crea le relative pracom
                        $this->getPracom();
                        break;
                    case $this->nameForm . '_Allegati':
                        $this->setdirectoryAll(); // carico gli allegati fisici
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

    private function scrivitime($testo) {
        if (!isset($this->tempo)) {
            $this->tempo = microtime(true);
        }
        $now = microtime(true);
        $this->time .= ($now - $this->tempo) . ' ' . $testo . "\n";
        $this->tempo = $now;
    }

    private function importafascicoloPE() {
        /*
         * lettura db sorgente   TOT 25189 fascicoli
         */
        $sql_base = "SELECT * 
                     FROM ELENCO_PRATICHE_PE
                     ORDER BY Anno";
//WHERE IdPrati = '178858'
        $lama_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_base, true);
        //Out::msgInfo("fascicolo", print_r($sql_base,true));
        // Out::msgInfo("fascicolo", print_r($lama_tab,true));
        $i = 0;
        foreach ($lama_tab as $lama_rec) {
//  file_put_contents("/users/pc/dos2ux/lama.log", $i . " - " . time() . "\n", FILE_APPEND);
            $toinsert = $this->inserisciFascicolo($lama_rec, 'PE');
            if (!$toinsert) {
                $anomalie++;
            } else {
                $i++;
                $this->importasoggetti($this->Id, 'PE');
                $this->caricapasso($this->Id, 'PASSI_PE');
                $this->importaallegati($this->Id, 'DOCUMENTI_PRATICA_PE');
                $this->importaallegati($this->Id, 'DOCUMENTI_COLLEGATI_PE');
                $this->importaallegati($this->Id, 'DOCUMENTI_INTERNI_PE');
                $spesa = $this->importaspese($this->Pratica, $this->Id, 'ONERI_PE');
                if ($spesa) {
                    $this->importaversamenti($this->Pratica, $this->Id, 'VERSAMENTO_ONERI_PE');
                }
            }
        }
        Out::msgInfo("OK", $i . " Fascicoli caricati");

        return true;
    }

    private function importafascicoliNNPE($tipo) {
        if ($tipo == '1') {
            $sql_base = "SELECT Id, NPrat, DataIns,DataEnd, Anno, NumProt, Oggetto, Modulo, Descr, CodFunz 
                     FROM ELENCO_PRATICHE_NN_PE WHERE Id <= '83000' ORDER BY Anno ASC
                     "; ///89283
        } else {
            $sql_base = "SELECT Id, NPrat, DataIns,DataEnd, Anno, NumProt, Oggetto, Modulo, Descr, CodFunz 
                     FROM ELENCO_PRATICHE_NN_PE WHERE Id > '83000' ORDER BY Anno ASC
                     ";
        }

        $lama_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_base, true);
        $i = 0;
        //$this->scrivitime('Inizio procedura');
        foreach ($lama_tab as $lama_rec) {
            // $this->scrivitime('pre inserisci fascicolo');
            $toinsert = $this->inserisciFascicolo($lama_rec);
            // $this->scrivitime('post fascicolo');
            if (!$toinsert) {
                $anomalie++;
            } else {
                $i++;
                // $this->scrivitime('pre soggetto ');
                $this->importasoggetti($this->Id);
                //$this->scrivitime('pre passo');
                $this->caricapasso($this->Id, 'PASSI_NN_PE');
                // $this->scrivitime('pre documento');
                $this->importaallegati($this->Id, 'DOCUMENTI_PRATICA_NN_PE');
                // $this->scrivitime('postdocumento');
            }
        }
        Out::msgInfo("OK", $i . " Fascicoli caricati");

        return true;
    }

    private function importafascicoloPEOLD() {
        $sql_base = "SELECT * FROM elenco_pratiche_pe WHERE pratica_n <> ''
                     ORDER BY Anno ASC";

        $lama_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql_base, true);
        $i = 0;
        foreach ($lama_tab as $lama_rec) {

            $toinsert = $this->inserisciFascicolo_2($lama_rec);
            if (!$toinsert) {
                $anomalie++;
            } else {
                $i++;
                $this->importasoggetti2($this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo']);
                $this->caricalocalizzazioneintervento2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo']);
                $this->importadaticatastali2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo']);
                $where = " AND (inizio_lavori <> '' OR fine_lavori <> '')";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_finale', $where);
                $where = " AND (data_agibilita <> '' OR n_agibilita <> '')";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_agibilita', $where);
                $where = " AND data_cert_collaudo <> ''";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_collaudo', $where);
                $where = " AND (inviato_san <> '' OR parere_sanit <> '' OR data_san <> '')";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_parere_sanitario', $where);
                $where = " AND (data_dep_genio_civile <> '' OR n_genio_civile <> '')";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_genio_civile', $where);
                $where = " AND (parere_comm_edil <> '' OR seduta_del <> '' OR motivazione <> '' OR a_condizione <> '')";
                $this->caricapasso2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo'], 'passo_commissione_edilizia', $where);
                $this->importaspese2($this->Pratica, $this->relazioni_2['anno'], $this->relazioni_2['numero'], $this->relazioni_2['protocollo']);
            }
        }
        Out::msgInfo("OK", $i . " Fascicoli caricati");

        return true;
    }

    private function inserisciFascicolo($lama_rec, $tipo = '') {
        //  $this->scrivitime('pre ricava gesnum');
        $proges_rec['GESNUM'] = $this->gestGesnum($lama_rec['Anno']); //RICAVO GESNUM PROGRESSIVO PER ANNO
        //  $this->scrivitime('post ricava gesnum');
        //$this->Id = $lama_rec['Id'];  // MI SALVO ID PER RELAZIONI 
        if (empty($lama_rec['DataIns'])) {
            $lama_rec['DataIns'] = '19000101';  // SETTO UNA DATA FITTIZIA PER I FASCICOLI SENZA
        }
        if ($tipo == 'PE') {
            $this->Id = $lama_rec['IdPrati'];  // MI SALVO ID PER RELAZIONI 
            $proges_rec['GESDCH'] = $lama_rec['DataRil'];  // Data chiusura fascicolo = data ricezione 
        } else {
            $this->Id = $lama_rec['Id'];  // MI SALVO ID PER RELAZIONI 
            $proges_rec['GESDCH'] = $lama_rec['DataEnd'];   // Data chiusura fascicolo = data ricezione 
        }

        $proges_rec['SERIEANNO'] = $lama_rec['Anno'];                                             // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $lama_rec['NPrat'];                                  // Serie NUMERO
        $proges_rec['SERIECODICE'] = self::$SUBJECT_BASE_LETTERA[$lama_rec['Modulo']];        // Serie PROVENIENZA 
        $proges_rec['GESPRO'] = self::$SUBJECT_BASE_PROCEDIMENTO[$lama_rec['Descr']];
        $proges_rec['GESNPR'] = $lama_rec['Anno'] . $lama_rec['NumProt'];                      // Anno + Numero protocollo 
        $proges_rec['GESORA'] = "00:00";                              // ora INSERIMENTO protocollo 
        $proges_rec['GESDRE'] = $lama_rec['DataIns'];        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDRI'] = $lama_rec['DataIns'];        // Data registrazione = DATA INIZIO protocollo

        $this->Pratica = $proges_rec['GESNUM'];

        $proges_rec['GESTSP'] = self::SPORTELLO_SUE_DEFAULT; // Sportello Da definire
        $proges_rec['GESPAR'] = 'A';                              // protocollo in arrivo
        $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
        $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
        $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato
        if (empty($lama_rec['CodFunz'])) {
            $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;
        } else {
            $proges_rec['GESRES'] = self::$RESPONSABILE[$lama_rec['CodFunz']];
        }

        $proges_rec['GESGIO'] = 0;                                                          // Giorni scadenza pratica
        $proges_rec['GESSPA'] = 0;                                                          // No per SBT
        $proges_rec['GESOGG'] = $lama_rec['Oggetto'];                                // Oggetto
        $proges_rec['GESTIP'] = "";                                                         // Tipologia
        $proges_rec['GESNOT'] = $lama_rec['Causa'];                                                         // Annotazioni 
        $proges_rec['GESMIGRA'] = $this->Id;                                                         // Annotazioni 
        $proges_rec['GESCODPROC'] = $lama_rec['Modulo'];         // su codice procedura mettiamo letter apratica 
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            //   $this->elaboradatiaggiuntivi($this->Pratica, $lama_rec, 'PRATICA');
            if ($tipo == 'PE') {
                $this->caricalocalizzazioneintervento($this->Pratica, 'TERRITORIO_PE', $lama_rec['NPrat'], $lama_rec['Anno'], $lama_rec['Modulo']);
            } else {
                $this->caricalocalizzazioneintervento($this->Pratica, 'TERRITORIO_NN_PE', $lama_rec['NPrat'], $lama_rec['Anno'], $lama_rec['Modulo']);
            }
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Record ID " . $lama_rec['IdPrati'] . $lama_rec['Id'] . " Nome Fascicolo " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function cercaantecedente() {

        //$sql_lama = "SELECT * FROM RELAZIONI_PRATICA WHERE IdPrati < '89401'";
        $sql_lama = "SELECT * FROM RELAZIONI_PRATICA WHERE IdPrati > '89401'";
        // DA DIVIDERE A 2 IN QUANTO VA
        $Relazioni_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_lama, true);
        $i = 1;
        foreach ($Relazioni_tab as $Relazione_rec) {
            // trovo per relazione figlio TROVO FIGLIO
            // trovo per relazione padre TROVO PADRE
            // FACCIO UPDATE PER IL FIGLIO E SVUOTO 

            $sql_padre = "SELECT GESNUM FROM PROGES WHERE GESMIGRA = '" . $Relazione_rec['IdPrati'] . "'";
            $gesnum_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_padre, false);
            if (empty($gesnum_rec)) {
                $this->scriviLog("Non trovato padre" . $Relazione_rec['IdPrati']);
                continue;
            }
            $sql_figlio = "SELECT ROWID,GESNUM FROM PROGES WHERE GESMIGRA = '" . $Relazione_rec['IdPratiR'] . "'";
            $Figlio_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_figlio, false);
            if (!empty($Figlio_rec)) {
                $proges = array(
                    'ROWID' => $Figlio_rec['ROWID'],
                    'GESNUM' => $Figlio_rec['GESNUM'],
                    'GESPRE' => $gesnum_rec['GESNUM'],
                    'GESMIGRA' => ''
                );
                try {
                    if (!ItaDB::DBUpdate($this->PRAM_DB, 'PROGES', 'ROWID', $proges)) {
                        $this->scriviLog("DB UPDATE NON RIUSCITO");
                    }
                    ++$i;
                } catch (Exception $ex) {
                    $testo = "Fatal: Errore INSERIMENTO Fascicolo Figlio" . $Figlio_rec['GESNUM'] . " Nome Fascicolo Padre " . $gesnum_rec['DAGNUM'] . $ex->getMessage();
                    $this->scriviLog($testo);
                    continue;
                }
            }
        }
        Out::msgInfo("Antecedente", "Collegamento fascicoli antecedenti terminata n " . $i);
        Out::msgInfo("Query pulisci note rimanenti", "DA LANCIARE SU PASDOC<BR>UPDATE PROGES SET GESMIGRA = '' WHERE GESMIGRA <> ''");
        return true;
    }

    private function cercaantecedente_attiAmm() {

        $sql_lama = "SELECT ROWID,GESNUM,GESNOT, GESMIGRA FROM PROGES WHERE GESMIGRA <> ''";
        $Relazioni_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_lama, true);
        $i = 0;
        foreach ($Relazioni_tab as $Relazione_rec) {
            // trovo per relazione figlio TROVO FIGLIO
            // trovo per relazione padre TROVO PADRE
            // FACCIO UPDATE PER IL FIGLIO E SVUOTO 

            $sql_relazione = "SELECT * FROM PROCEDIMENTI_ATTI_AMM WHERE Id = '" . $Relazione_rec['GESMIGRA'] . "'";
            $relazione_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_relazione, true);
            foreach ($relazione_tab as $relazione_rec) {
                if (!empty($relazione_rec)) {
                    $testo = 'PROCEDIMENTI ATTI AMMINISTRATIVI: Anno: ' . $relazione_rec['Anno'] . ' Numero Prat: ' . $relazione_rec['NPrat'] . ' OGGETTO: ' . $relazione_rec['Oggetto'] . ' DESCRIZIONE: ' . $relazione_rec['Descr'];
                    $proges = array(
                        'ROWID' => $Relazione_rec['ROWID'],
                        'GESNOT' => $Relazione_rec['GESNOT'] . $testo,
                        'GESMIGRA' => ''
                    );
                    try {
                        if (!ItaDB::DBUpdate($this->PRAM_DB, 'PROGES', 'ROWID', $proges)) {
                            $this->scriviLog("DB UPDATE NON RIUSCITO");
                        }
                        ++$i;
                    } catch (Exception $ex) {
                        $testo = "Fatal: Errore INSERIMENTO Fascicolo cercaantecedente_attiAmm" . $Relazione_rec['GESNUM'] . " Id relazione " . $Relazione_rec['GESMIGRA'] . $ex->getMessage();
                        $this->scriviLog($testo);
                        continue;
                    }
                }
            }
        }
        Out::msgInfo("Procedimenti Atti AMM", "Aggiunta note proc Attiv amministrativi terminata n " . $i);
        //Out::msgInfo("Query pulisci note rimanenti", "DA LANCIARE SU PASDOC<BR>UPDATE PROGES SET GESMIGRA = '' WHERE GESMIGRA <> ''");
        return true;
    }

    private function importasoggetti($id, $tipo = '') {
        if (!$id) {
            return false;
        }
        if ($tipo == 'PE') {
            $where1 = 'REFERENTI_PERS_G_PE';
            $where2 = 'REFERENTI_PERS_F_PE';
        } else {
            $where1 = 'REFERENTI_PERS_G_NN_PE';
            $where2 = 'REFERENTI_PERS_F_NN_PE';
        }
        $sql_1 = "SELECT * FROM $where1 WHERE id = '$id'";
// Out::msgInfo("sqlsogg", $sql_1);
        $impresa_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_1, true);
        if ($impresa_tab) {
//Out::msgInfo("1", print_r($impresa_tab, true));
            $this->caricasoggetti($impresa_tab, $this->Pratica);
        }

        $sql_2 = "SELECT * FROM $where2 WHERE id = '$id'";
        $persone_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql_2, true);
        if ($persone_tab) {
            $this->caricasoggetti($persone_tab, $this->Pratica);
        }
        return true;
    }

    private function caricasoggetti($impresa_tab, $pratica, $tipo = '') {
        if (!$pratica) {
            Out::msgStop("ERRORE", "Nessuna impresa caricata");
            return false;
        }
        foreach ($impresa_tab as $impresa_rec) {
            $proges_rec['DESNUM'] = $pratica;
//            if ($tipo == 'Pers_G') {
//                
//            } 
            if ($tipo == 'richiedente') {
                $proges_rec['DESNOM'] = $impresa_rec['richiedente'];
                $proges_rec['DESRAGSOC'] = $impresa_rec['richiedente'];
                $proges_rec['DESFIS'] = $this->cleanCF($impresa_rec['cod_fisc']);
                $proges_rec['DESCIT'] = $impresa_rec['residente'];
                $proges_rec['DESIND'] = $impresa_rec['via'];
                $proges_rec['DESCIV'] = $impresa_rec['civico'];
                $proges_rec['DESCAP'] = $impresa_rec['cap'];
                $proges_rec['DESRUO'] = '0002';
            } elseif ($tipo == 'progettista') {
                if (empty($impresa_rec['progettista']) && empty($impresa_rec['cod_fisc_progettista'])) {
                    continue;
                }
                $proges_rec['DESNOM'] = $impresa_rec['progettista'];
                $proges_rec['DESRAGSOC'] = $impresa_rec['progettista'];
                $proges_rec['DESFIS'] = $this->cleanCF($impresa_rec['cod_fisc_progettista']);
                $proges_rec['DESRUO'] = '0007';
            } else {
                if ($impresa_rec['indirizzo']) {

                    $indirizzo = explode('@', $impresa_rec['indirizzo']);
                    $proges_rec['DESIND'] = $indirizzo[0];
                    $proges_rec['DESCIV'] = $this->ricavacivico($indirizzo[0]);
                    $proges_rec['DESCAP'] = $indirizzo[1];
                    $proges_rec['DESCIT'] = $indirizzo[2];
                }
                $proges_rec['DESNOM'] = $impresa_rec['RagSoc'];
                $proges_rec['DESRAGSOC'] = $impresa_rec['Per_RagSoc'];
                $proges_rec['DESNOME'] = $impresa_rec['Nome'];
                $proges_rec['DESCOGNOME'] = $impresa_rec['Cognome'];
                $proges_rec['DESPIVA'] = $impresa_rec['PartIVA'];
                $proges_rec['DESFIS'] = $impresa_rec['CodFiscP'];
                $proges_rec['DESSESSO'] = $impresa_rec['Sesso'];
                $proges_rec['DESRUO'] = self::$RUOLO_SOGGETTO[$impresa_rec['DeCod']];
                $proges_rec['DESRAGSOC'] = $impresa_rec['RagSoc_ref'];
            }
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

    private function importaallegati($id, $from) {
        if (!$id) {

            return false;
        }
        // $sql = "SELECT * FROM $from WHERE Id = $id AND FileName <> ''";
        $sql = "SELECT * FROM $from WHERE Id = $id";
        $allegati_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        if (!$allegati_tab) {
            return false;
        }
        if ($from == 'DOCUMENTI_INTERNI_PE' || $from == 'DOCUMENTI_COLLEGATI_PE') {
            $this->caricallagetai2($allegati_tab, $this->Pratica);
            return true;
        }
        $this->caricallagetai($allegati_tab, $this->Pratica);
    }

    private function caricallagetai($allegati_tab, $pratica) {
        foreach ($allegati_tab as $allegati_rec) {
            if (strstr($allegati_rec['FileName'], 'LAMA')) {
                $appoggio = explode("Doc", $allegati_rec['FileName']);
                $NomeAlle = 'Doc' . $appoggio[1];  // mi ricavo nome senza la path
                $PassMigra = $allegati_rec['FileName'];
            } else {
                $NomeAlle = $allegati_rec['FileName'];  // nome senza la path
                $PassMigra = ''; // la path non è presente nel nome del file
            }

            $rand = md5(uniqid()) . '.' . pathinfo($allegati_rec['FileName'], PATHINFO_EXTENSION);
            $proges_rec['PASKEY'] = $pratica;
// $proges_rec['PASFIL'] = $allegati_rec['Descr']; // nome fisico file 
            $proges_rec['PASFIL'] = $rand; // nome fisico file 
            $proges_rec['PASNOT'] = $allegati_rec['Descr'];
            $proges_rec['PASCLA'] = "GENERALE";
            $proges_rec['PASNAME'] = $NomeAlle;  // nome passo
            $proges_rec['PASMIGRA'] = $PassMigra; // nome fisico file  per storico path 
            $proges_rec['PASDATADOC'] = $allegati_rec['dbo_RD_DocPr_DataIns']; // appoggio la data per relaizone pracom DATA INSERIMENTO FILE

            if ($allegati_rec['TipoDoc'] == '1') {
                $proges_rec['PASEVI'] = '1';   // TO DO appoggio la 1 = 1 E 0 =2 per relaizone pracom A /P EVIDENZIA ALLEGATI
            } elseif ($allegati_rec['TipoDoc'] === '0') {
                $proges_rec['PASEVI'] = '2';
            } else {
                $proges_rec['PASEVI'] = '0';
            }
            if (empty($allegati_rec['FileName'])) {
                $proges_rec['PASFIL'] = 'Vuoto.txt';
                $proges_rec['PASMIGRA'] = 'VUOTO'; // NON è PRESNETE LA PATH
            }
            if ($allegati_rec['Esito'] == '-1') {
                $proges_rec['PASSTA'] = 'NP';
            } elseif ($allegati_rec['Esito'] === '0') {
                $proges_rec['PASSTA'] = 'V';
            }

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Allegato ID" . $allegati_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function caricallagetai2($allegati_tab, $pratica) {
        //Out::msgInfo("chiamatooo", print_r($allegati_tab,true));
        foreach ($allegati_tab as $allegati_rec) {
            if (strstr($allegati_rec['filerinominato'], 'lama')) {
                $appoggio = explode("testi_interni_lama/", $allegati_rec['filerinominato']); // mi ricavo nome senza la path
                $NomeAlle = $appoggio[1];
            } else {
                $NomeAlle = $allegati_rec['filerinominato'];  // nome senza la path
            }
            $rand = md5(uniqid()) . '.' . pathinfo($allegati_rec['filerinominato'], PATHINFO_EXTENSION);
            $proges_rec['PASKEY'] = $pratica;
// $proges_rec['PASFIL'] = $allegati_rec['Descr']; // nome fisico file 
            $proges_rec['PASFIL'] = $rand; // nome fisico file 
            $proges_rec['PASNOT'] = $allegati_rec['Descr'];
            $proges_rec['PASCLA'] = "GENERALE";
            $proges_rec['PASNAME'] = $NomeAlle;  // nome allegato
            $proges_rec['PASMIGRA'] = $allegati_rec['filerinominato']; // nome fisico file  per storico path 
            $proges_rec['PASDATADOC'] = $allegati_rec['dbo_RD_DocPr_DataIns']; // appoggio la data per relaizone pracom DATA INSERIMENTO FILE

            if ($allegati_rec['TipoDoc'] == 1) {
                $proges_rec['PASEVI'] = 1;   // TO DO appoggio la 1 = 1 E 0 =2 per relaizone pracom A /P EVIDENZIA ALLEGATI
            } elseif ($allegati_rec['TipoDoc'] === 0) {
                $proges_rec['PASEVI'] = 2;
            } else {
                $proges_rec['PASEVI'] = 0;
            }
            if ($allegati_rec['Esito'] == '-1') {
                $proges_rec['PASSTA'] = 'NP';
            } elseif ($allegati_rec['Esito'] === '0') {
                $proges_rec['PASSTA'] = 'V';
            }

            try {
                //Out::msgInfo("vado a inserire", print_r($proges_rec,true));
                ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $proges_rec);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Allegato caric all 2 ID" . $allegati_rec['Id'] . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function importadaticatastali($id, $from) {
        $sql = "SELECT FogCat, ParCat, SubCat FROM $from WHERE Id = '$id'";
        $catastali_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        foreach ($catastali_tab as $catastali_rec) {
            $daticatastali = array(
                'PRONUM' => $this->Pratica,
                'FOGLIO' => str_pad($catastali_rec['FogCat'], 4, "0", STR_PAD_LEFT),
                'PARTICELLA' => str_pad($catastali_rec['ParCat'], 5, "0", STR_PAD_LEFT),
                'SUBALTERNO' => '',
                'NOTE' => "F: " . $catastali_rec['FogCat'] . " P: " . $catastali_rec['ParCat']
            );

            if ($catastali_rec['SubCat']) {
                $daticatastali['SUBALTERNO'] = str_pad($catastali_rec['SubCat'], 4, "0", STR_PAD_LEFT);
                $daticatastali['NOTE'] .= " S: " . $catastali_rec['SubCat'];
            }
            if ($from == 'CATTER') {
                $daticatastali['TIPO'] = 'T';
            } elseif ($from == 'CATURB') {
                $daticatastali['TIPO'] = 'F';
            }


            try {
                // Out::msgInfo("dati catastale query", print_r($daticatastali,true));
                ItaDB::DBInsert($this->PRAM_DB, 'PRAIMM', 'ROWID', $daticatastali);
            } catch (Exception $ex) {
                $testo = "Fatal: Errore in inserimento Record Dati Catastali per richiesta" . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
            }
        }
        return true;
    }

    private function caricapasso($id, $from) {
        if (!$id) {
            return false;
        }
        $sql = "SELECT * FROM " . $from . " WHERE Id = '$id'";

        $passi_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        if (!$passi_tab) {
            return false;
        }
        $index = 0;
        foreach ($passi_tab as $passi_rec) {
            $index = $index + 1;
            $passo = $this->creapasso($this->Pratica, $passi_rec, $index);
        }
//  $this->ordinaPassi($this->Pratica);
    }

    private function creapasso($Pratica, $dati, $index) {
//   dbo_RD_Eve_Descr   TIPO PASSO
        if ($dati['DeCod'] == 'Eliminata') {
            return false; // NON CARICA I PASSI ELIMINATI
        }
        $seq = 0;
        $propak = $this->praLib->PropakGenerator($Pratica, $index);
        $passo = array(
            'PRONUM' => $Pratica,
            'PROSEQ' => $dati['Seq'],
            'PRORPA' => self::$RESPONSABILE[$dati['CodFunz']],
            'PROANN' => $dati['DeCod'],
            'PRODPA' => $dati['dbo_RD_Eve_Descr'] . ' - ' . $dati['dbo_RD_Azi_Descr'], // nome passo
            'PROINI' => '', // data apertura passo
            'PROFIN' => '', // data chiusura passo
            'PROPAK' => $propak,
            'PROCLT' => $tipo, // tipo passo
            'PRODTP' => $prodpt, // descrizione tipo passo  
        );
        if ($dati['DataAtt'] != 0) {
            $passo['PROINI'] = $dati['DataAtt'];
        }
        if ($dati['DataEnd'] != 0) {
            $passo['PROFIN'] = $dati['DataEnd'];
        }
        if (empty($passo['PRORPA'])) {
            $passo['PRORPA'] = self::RESPONSABILE_DEFAULT;
        }

        if ($dati['DeCod'] == 'Eseguita' || $dati['DeCod'] == 'Eseguita/Favorevole') {
            if ($dati['DataEnd'] == 0 || empty($dati['DataEnd'])) {
                $passo['PROFIN'] = $dati['DataAtt'];
            }
        }

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
            $rowid_pass = ItaDb::DBLastId($this->PRAM_DB);  // mi ritorn ail rowid della insert
            if ($dati["Appunti"]) {
                $this->creanotepasso($dati["Appunti"], $passo, $rowid_pass);
            }
            if ($dati["Appunti1"]) {
                $this->creanotepasso($dati["Appunti1"], $passo, $rowid_pass);
            }
            return true;
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
            $this->elaboradatiaggiuntivi($this->Pratica, $datiaggiuntivi, $testo, $propak['PROPAK']);
        }
    }

    private function elaboradatiaggiuntivi($Pratica, $valori, $tipo = '', $propak = '') {
        foreach ($valori as $key => $datoaggiuntivo) {
//            if ($key == 'Id' || $key == 'CHIAVE') {
//                continue;
//            }

            $dati = array(
                'DAGNUM' => $Pratica,
                'DAGDES' => $key,
                'DAGLAB' => $key,
                'DAGKEY' => $tipo . '_' . strtoupper($key),
                'DAGVAL' => $datoaggiuntivo,
                'DAGSET' => $Pratica,
                'DAGPAK' => $Pratica
            );

            if ($tipo == 'Fine Lavori') {
                $dati['DAGKEY'] = 'FINELAVORI_' . strtoupper($key);
                $dati['DAGPAK'] = $propak;
                $dati['DAGSET'] = $propak . '_01';
            }
            if ($key == 'Descr' || $key == 'Oggetto' || $key == 'oggetto_concess') {
                $dati['DAGTIC'] = 'TextArea';
            }
            if (strstr($key, 'Data')) {
                $dati['DAGTIC'] = 'Data';
            }
            if ($key == 'IdPrati' || $key == 'Id') {
                $dati['DAGROL'] = '1';  // SOLA LETTURA PER CAMPO ID
            }
            $this->caricadatiaggiuntivi($dati);
        }
        return;
    }

    private function creanotepasso($note, $passo, $rowid_pass) {
        //$rowidPropas = $this->getRowid('PROPAS', $where);
        if (!$rowid_pass) {
            $testo = "Fatal: Errore Inserimento note passo, nessun rowid passo recuperato per fascicolo N " . $this->Pratica;
            $this->scriviLog($testo);
            return false;
        }

        $Note = array(
            'OGGETTO' => "APPUNTI PER PASSO Seq. " . $passo['PROSEQ'],
            'TESTO' => $note,
            'UTELOG' => 'Italsoft',
            'UTELOGMOD' => 'Italsoft'
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

    private function caricalocalizzazioneintervento($Pratica, $from, $numero, $anno, $modulo) {
        $localita_tab = $anades_tab = array();
        $sql = "SELECT * FROM " . $from . " WHERE Anno = '$anno' AND NPrat = '$numero' AND Modulo = '$modulo' AND (NomeObj = 'AreaCirc' OR NomeObj = 'CatTer' OR NomeObj = 'CatUrb' OR NomeObj = 'Civico') ORDER BY NomeObj";
// NomeObj  === CatUrb  sono i dati catastali
        $via_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        if (!$via_tab) {
            return false;
        }
        // Out::msgInfo("vie", print_r($via_tab, true));
        // $via_rec['NomeObj'] == 'CatUrb' OLD dati catastali 
        foreach ($via_tab as $key => $via_rec) {
            if ($via_rec['NomeObj'] == 'CatTer') {
                $this->importadaticatastali($via_rec['IdObj'], 'CATTER'); // importa ed elabora i dati catastali TERRENO
                continue;
            }
            if ($via_rec['NomeObj'] == 'CatUrb') {
                $this->importadaticatastali($via_rec['IdObj'], 'CATURB'); // importa ed elabora i dati catastali FABBRICATO
                continue;
            }
            if ($via_rec['NomeObj'] == 'AreaCirc') {
                $localita_tab[$via_rec['IdObj']] = array(
                    'DESNUM' => $Pratica,
                    'DESIND' => $via_rec['DescrMax'],
                    //'DESCIV' => $this->civico_AreaCic($via_rec['IdObj']),
                    'DESCIV' => '',
                    'DESRUO' => '0014'
                );
            }
            if ($via_rec['NomeObj'] == 'Civico') {
                $return_civico = $this->civico_AreaCic($via_rec['IdObj']);
                // Out::msgInfo("return civico".$return_civico['IdACirc'], print_r($return_civico,true));
                $localita_tab[$return_civico['IdACirc']]['DESCIV'] = $return_civico['NumCiv'] . $return_civico['EspCiv'];
                $anades_tab[] = $localita_tab[$return_civico['IdACirc']];
            } else {
                $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_tab[$via_rec['IdObj']], $insert_Info); // fai l'insert diretto
            }
        }
        //Out::msgInfo("da inserire rec via 2", print_r($localita_tab,true));
        foreach ($anades_tab as $anades_rec) {
            $this->insertRecord($this->PRAM_DB, 'ANADES', $anades_rec, $insert_Info);
        }
        return true;
    }

    private function civico_AreaCic($id) {

        $sql = "SELECT IdACirc, NumCiv, EspCiv
                FROM CIVICO 
                WHERE Id = '$id' AND NumCiv <> 0";
        return ItaDB::DBSQLSelect($this->LAMA_DB, $sql, false);
    }

    private function importaspese($Pratica, $id, $from) {
        $sql = "SELECT * FROM $from WHERE id = '$id' ORDER BY Anno ASC ";
        $oneri_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        $i = 0;
        $valore = array();
        foreach ($oneri_tab as $oneri_rec) {
            foreach ($oneri_rec as $key => $onere) {
                if ($key == 'ImpTot' || $key == 'Importo' || $key == 'Scomputo') {
                    $valore[$key] = explode('? ', $onere);
                    $oneri_rec[$key] = str_replace(",", ".", $valore[$key]);
                } else {
                    continue;
                }
            }
            $costo = array(
                'IMPONUM' => $Pratica, // numero pratica
                'IMPOCOD' => self::$ONERI[$oneri_rec['DeCod']], // tipo pagamento
                'IMPOPROG' => $i, // progressivo pagamento
                'IMPORTO' => $oneri_rec['ImpTot'][1],
                'PAGATO' => $oneri_rec['Importo'][1],
                'DIFFERENZA' => $oneri_rec['Scomputo'][1],
                // 'DATAREG' => $oneri_rec, da valutare se qua metter edata fittizia per campo obbligatorio
                'NOTE' => $oneri_rec['DeCod']
            );
            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROIMPO', 'ROWID', $costo);
                $i++;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore Inserimento spesa per fascicolo N " . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
        }
        return true;
    }

    private function importaversamenti($Pratica, $id, $from) {
// PROCONCILIAZIONE seconda tabellas
        $sql = "SELECT * FROM $from WHERE id = '$id' ORDER BY Anno ASC ";
        $versamenti_tab = ItaDB::DBSQLSelect($this->LAMA_DB, $sql, true);
        foreach ($versamenti_tab as $versamento_rec) {
            foreach ($versamento_rec as $key => $versamento) {
                if ($key == 'ImpVers' || $key == 'Mora' || $key == 'ImpDov') {
                    $valore[$key] = explode('? ', $versamento);
                    $versamento_rec[$key] = str_replace(",", ".", $valore[$key]);
                } else {
                    continue;
                }
            }

            $pagamento = array(
                'IMPONUM' => $Pratica, // numero pratica
                'IMPOPROG' => '999', // non è agganciato a nessuna voce di pagamento messa 999 come generica
                'QUIETANZA' => '1',
                'NUMEROQUIETANZA' => '1',
                //'DATAQUIETANZA' => $versamento_rec['DataPag'], //  data ???
//'DATARIVERSAMENTO' => $versamento_rec['DataEnd'], //  data ???
//'DATAINSERIMENTO' => $versamento_rec['DataDoc'], //  data ???
                'SOMMAPAGATA' => $versamento_rec['ImpVers'][1],
                'DIFFERENZA' => 0,
                'TOTALE' => 0
            );
            if ($versamento_rec['DataPag'] != '0') {
                $pagamento['DATAQUIETANZA'] = $versamento_rec['DataPag'];
            }
            if ($versamento_rec['DataEnd'] != '0') {
                $pagamento['DATARIVERSAMENTO'] = $versamento_rec['DataEnd'];
            }
            if ($versamento_rec['DataDoc'] != '0') {
                $pagamento['DATAINSERIMENTO'] = $versamento_rec['DataDoc'];
            }
            $text = '';
            if ($versamento_rec['ImpDov'][1] != '0.00' && !empty($versamento_rec['ImpDov'][1])) {
                $text .= " Importo Dovuto: E" . $versamento_rec['ImpDov'][1];
            }
            if ($versamento_rec['Mora'][1] != '0.00' && !empty($versamento_rec['Mora'][1])) {
//$pagamento['NOTE'] = "Mora: E".$versamento_rec['Mora'][1];
                $text .= " Mora: E" . $versamento_rec['Mora'][1];
            }

            $pagamento['NOTE'] = $text;

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PROCONCILIAZIONE', 'ROWID', $pagamento);
                $i++;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore Inserimento versamento spesa per fascicolo N " . $this->Pratica . " " . $ex->getMessage();
                $this->scriviLog($testo);
                return false;
            }
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

    public function getRowid($from, $where) {
        if (empty($from) || empty($where)) {
            $testo = "Fatal: Errore Inserimento note passo per fascicolo N " . $this->Pratica;
            $this->scriviLog($testo);
            return false;
        }
        $sql = "SELECT ROWID FROM $from" . " " . "$where";
        $rec_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        return $rec_tab;
    }

    public function setdirectoryAll() {
        $sql = "SELECT * FROM PASDOC WHERE ROWID>=528813 AND ROWID<=528824";
        $doc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $Origin_path = self::PATH_ALLEGATI; // path fissa di dove si trova la directory per arricvare agli allegati
        $i = 0;
        foreach ($doc_tab as $doc_rec) {
            if (strlen($doc_rec['PASKEY']) > '10') {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY']); // ALLEGATI DI PASSO 
            } else {
                $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY'], 'PROGES');  // ALLEGATI DI PRATICA
            }


            // Out::msgInfo("pATH",$pratPath);
            if (empty($doc_rec['PASMIGRA'])) {
                $pathfile = $doc_rec['PASNAME'];
            } else {
                $pathfile = $doc_rec['PASMIGRA'];
            }
            if ($pathfile == 'VUOTO') {
                continue;
            }

            if (file_exists($Origin_path . '/' . $pathfile)) {
                if (!@copy($Origin_path . '/' . $pathfile, $pratPath . '/' . $doc_rec['PASFIL'])) {
                    $this->scriviLog("Errore copia allegato" . $Origin_path . '/' . $pathfile);
                    continue;
                }
            } else {
                if ($doc_rec['PASMIGRA'] == 'VUOTO') {
                    $doc_rec['PASNAME'] = "Vuoto.txt";
                    $doc_rec['PASFIL'] = md5(uniqid()) . ".txt";
                    file_put_contents("Vuoto", $pratPath . '/' . $doc_rec['PASFIL']);
                    try {
                        ItaDB::DBUpdate($this->PRAM_DB, 'PASDOC', 'ROWID', $doc_rec);
                    } catch (Exception $ex) {
                        $testo = "Fatal: Errore Assegnazione pasdoc VUOTO ROWID ->" . $doc_rec['ROWID'] . $ex->getMessage();
                        $this->scriviLog($testo);
                        continue;
                    }
                } else {
                    $this->scriviLog("Allegato non trovato" . $Origin_path . '/' . $doc_rec['PASMIGRA']);
                    continue;
                }
            }
            ++$i;
        }
        Out::msgInfo(" ", "N°$i Allegati copiati correttamente");
        return true;
    }

    //  OLD FUNZIONE 
//    public function setdirectoryAll() {
//        $sql = "SELECT * FROM PASDOC WHERE ROWID = '1'";
//        $doc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//        $Origin_path = self::PATH_ALLEGATI; // path fissa di dove si trova la directory per arricvare agli allegati
//
//        foreach ($doc_tab as $doc_rec) {
//            $pratPath = $this->praLib->SetDirectoryPratiche(substr($doc_rec['PASKEY'], 0, 4), $doc_rec['PASKEY'], 'PROGES', false);
//            //$rand = md5(rand() * time()) . "." . pathinfo($doc_rec['PASNAME'], PATHINFO_EXTENSION);
//            $rand = md5(uniqid()) . '.' . pathinfo($doc_rec['PASNAME'], PATHINFO_EXTENSION);
//
//            Out::msgInfo("pp", $Origin_path . '/' . substr($doc_rec['PASFIL'], 12));
//            if (file_exists($Origin_path . '/' . substr($doc_rec['PASFIL'], 12))) {
//                Out::msgInfo("ppp", $Origin_path . '/' . $doc_rec['PASFIL'] . '<br>' . $pratPath . '/' . $doc_rec['PASFIL']);
//                if (!@copy($Origin_path . '/' . substr($doc_rec['PASFIL'], 12), $pratPath . '/' . $rand)) {
//                    $this->scriviLog("Errore copia allegato" . $Origin_path . '/' . substr($doc_rec['PASFIL'], 12));
//                }
//                $pasdoc_rec = array(
//                    "ROWID" => $doc_rec['ROWID'],
//                    "PASFIL" => $rand
//                );
//                try {
//                    ItaDB::DBUpdate($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_rec);
//                } catch (Exception $ex) {
//                    $testo = "Errrore setdirectoy $Origin_path" . $doc_rec['PASFIL'] . $ex->getMessage();
//                    $this->scriviLog($testo);
//                    return false;
//                }
//            }
//        }
//        return true;
//    }

    private function inserisciFascicolo_2($lama_rec) {

        // PER LE ALTRE PRATICHE PE SERIE VECCHIA 
        $anno = str_pad($lama_rec['anno'], 4, "19", STR_PAD_LEFT);  // formatto l'anno a 4 cifre per anni da 2 su db ****
        $proges_rec['GESNUM'] = $this->gestGesnum($anno); //RICAVO GESNUM PROGRESSIVO PER ANNO

        $proges_rec['SERIEANNO'] = $anno;                                             // Serie ANNO
        $proges_rec['SERIEPROGRESSIVO'] = $lama_rec['rich_prot'];                                  // Serie NUMERO
        $proges_rec['SERIECODICE'] = self::$SUBJECT_BASE_LETTERA[$lama_rec['modulo']];        // Serie PROVENIENZA 
        $proges_rec['GESPRO'] = '660109';                                                            // PROCEDIMENTO ONLINE 
        $proges_rec['GESNPR'] = $anno . $lama_rec['rich_prot'];                      // Anno + Numero protocollo 
        $proges_rec['GESORA'] = "00:00";                                                 // ora INSERIMENTO protocollo 
        $data = $this->convertdate($lama_rec['del']);
        $proges_rec['GESDRE'] = $data;        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDRI'] = $data;        // Data registrazione = DATA INIZIO protocollo
        $proges_rec['GESDCH'] = $data;                          // Data chiusura fascicolo = data ricezione 


        $this->Pratica = $proges_rec['GESNUM'];

        $this->relazioni_2['numero'] = $lama_rec['pratica_n'];
        $this->relazioni_2['protocollo'] = $lama_rec['rich_prot'];               // PER LE RELAZIONI
        $this->relazioni_2['anno'] = $lama_rec['anno'];


        $proges_rec['GESTSP'] = self::SPORTELLO_SUE_DEFAULT;             // Sportello Da definire
        $proges_rec['GESPAR'] = 'A';                                     // protocollo in arrivo
        $proges_rec['GESSET'] = "";                                                         // Settore  resp. <- non piu usato
        $proges_rec['GESSER'] = "";                                                         // Servizio resp. <- non piu usato
        $proges_rec['GESOPE'] = "";                                                         // Unita Operatriva resp. <- non piu usato

        $proges_rec['GESRES'] = self::RESPONSABILE_DEFAULT;

        $proges_rec['GESGIO'] = 0;                                                          // Giorni scadenza pratica
        $proges_rec['GESSPA'] = 0;                                                          // No per SBT
        $proges_rec['GESOGG'] = $lama_rec['oggetto_concess'];                                // Oggetto
        $proges_rec['GESTIP'] = "";                                                         // Tipologia
        $proges_rec['GESCODPROC'] = $lama_rec['modulo'];         // su codice procedura mettiamo letter apratica 
        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
            //  $this->elaboradatiaggiuntivi($this->Pratica, $lama_rec, 'PRATICA');
        } catch (Exception $ex) {
            $testo = "Fatal: Errore in inserimento Fascicolo Record MODULO PRATICHE-EDILZIE N prat " . $lama_rec['pratica_n'] . $anno . " Gesnum " . $this->Pratica . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function convertdate($data) {
        // RIFROMATTA LE DATE SPORCHE DA DB    esempio : 1.12.91 
        if (!$data) {
            return false;
        }
        list ($d, $m, $y) = explode(".", $data);
        $data_rec = str_pad($y, 4, "19", STR_PAD_LEFT) . str_pad($m, 2, "0", STR_PAD_LEFT) . str_pad($d, 2, "0", STR_PAD_LEFT);  // FORMATTA DATA
        return $data_rec;
    }

    private function importasoggetti2($anno, $numero, $protocollo) {
        if (!$anno || !$numero || !$protocollo) {
            return false;
        }
        $sql_1 = "SELECT richiedente,cod_fisc,residente,via,civico,cap FROM richiedente WHERE pratica_n = '" . $numero . "' AND anno= '" . $anno . "' AND rich_prot= '" . $protocollo . "'";
        //Out::msgInfo("sqlsogg", $sql_1);
        $impresa_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql_1, true);
        if ($impresa_tab) {
//Out::msgInfo("1", print_r($impresa_tab, true));
            $this->caricasoggetti($impresa_tab, $this->Pratica, 'richiedente');
        }
        $sql_2 = "SELECT progettista,cod_fisc_progettista FROM progettista WHERE pratica_n = '" . $numero . "' AND anno= '" . $anno . "' AND rich_prot= '" . $protocollo . "'";
        $persone_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql_2, true);
        if ($persone_tab) {
            $this->caricasoggetti($persone_tab, $this->Pratica, 'progettista');
        }
        return true;
    }

    private function caricalocalizzazioneintervento2($Pratica, $anno, $numero, $protocollo) {
        $sql = "SELECT zona_concess FROM territorio_zona_concessione WHERE anno = '$anno' AND pratica_n = '$numero' AND rich_prot = '$protocollo'";
// NomeObj  === CatUrb  sono i dati catastali
        $via_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql, true);
        if (!$via_tab) {
            return false;
        }
//Out::msgInfo("vie", print_r($via_tab, true));
        foreach ($via_tab as $key => $via_rec) {

            $localita_rec = array(
                'DESNUM' => $Pratica,
                'DESIND' => $via_rec['zona_concess'],
                'DESCIV' => '',
                'DESRUO' => '0014'
            );
            $this->insertRecord($this->PRAM_DB, 'ANADES', $localita_rec, $insert_Info);
        }
        return true;
    }

    private function importadaticatastali2($Pratica, $anno, $numero, $protocollo) {
        $sql = "SELECT riferimenti_catastali FROM passo_riferimenti_catastali WHERE pratica_n = '$numero' AND rich_prot = '$protocollo' AND anno = '$anno'";
        if (!$cat_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql, true)) {
            return false;
        }
        foreach ($cat_tab as $cat_rec) {
            if (empty($cat_rec['riferimenti_catastali'])) {
                continue;
            }
            $daticatastali = array(
                'PRONUM' => $Pratica,
                'FOGLIO' => "",
                'PARTICELLA' => "",
                'SUBALTERNO' => "",
                'NOTE' => $cat_rec['riferimenti_catastali']
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

    private function caricapasso2($Pratica, $anno, $numero, $protocollo, $from, $where = '') {

        $sql = "SELECT * FROM " . $from . " WHERE pratica_n = '$numero' AND rich_prot = '$protocollo' AND anno = '$anno'$where";
        // Out::msgInfo("slq", $sql);
        $passi_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql, true);
        if (!$passi_tab) {
            return false;
        }
        $index = 0;
        $dati = array();
        foreach ($passi_tab as $passi_rec) {
            $index = $index + 1;
            if ($from == 'passo_finale') {
                $dati['DESCRIZIONE'] = "Finale - inizio lavori " . $passi_rec['inizio_lavori'] . " fine lavori data " . $passi_rec['fine_lavori'];
                $dati['DATA'] = '';
                $dati['ANNOTAZIONI'] = '';
            }
            if ($from == 'passo_agibilita') {
                $dati['DESCRIZIONE'] = "Agibilita' - data " . $passi_rec['data_agibilita'] . " N° " . $passi_rec['n_agibilita'];
                $dati['ANNOTAZIONI'] = '';
            }
            if ($from == 'passo_collaudo') {
                $dati['DESCRIZIONE'] = "Collaudo - data " . $passi_rec['data_cert_collaudo'];
                $dati['ANNOTAZIONI'] = '';
            }
            if ($from == 'passo_parere_sanitario') {
                $dati['DESCRIZIONE'] = "Parere Sanitario  - inviato " . $passi_rec['inviato_san'] . ", Parere " . $passi_rec['parere_sanit'] . ", Data " . $passi_rec['data_san'];
                $dati['ANNOTAZIONI'] = '';
            }
            if ($from == 'passo_genio_civile') {
                $dati['DESCRIZIONE'] = "Genio Civile  - data " . $passi_rec['data_dep_genio_civile'] . ", N° " . $passi_rec['n_genio_civile'];
                $dati['ANNOTAZIONI'] = '';
            }
            if ($from == 'passo_commissione_edilizia') {
                $dati['DESCRIZIONE'] = "Commissione Edilizia  - " . $passi_rec['parere_comm_edil'] . " " . $passi_rec['seduta_del'] . " " . $passi_rec['a_condizione'];
                $dati['ANNOTAZIONI'] = $passi_rec['motivazione'];
            }
            $passo = $this->creapasso2($this->Pratica, $dati, $index);
        }
        unset($dati);
        $this->ordinaPassi($this->Pratica);
    }

    private function creapasso2($Pratica, $dati, $index) {
        $seq = 0;
        $propak = $this->praLib->PropakGenerator($Pratica, $index);
        $passo = array(
            'PRONUM' => $Pratica,
            'PROSEQ' => '',
            'PRORPA' => self::RESPONSABILE_DEFAULT,
            'PROANN' => $dati['ANNOTAZIONI'],
            'PRODPA' => $dati['DESCRIZIONE'], // DESCRIZIONE DEL PASSO
            'PROINI' => $dati['DATA'], // data apertura passo
            'PROFIN' => $dati['DATA'], // data chiusura passo
            'PROPAK' => $propak,
                // 'PROCLT' => $tipo, // tipo passo
                // 'PRODTP' => $prodpt, // descrizione tipo passo
        );

        try {
            ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
            return true;
        } catch (Exception $ex) {
            $testo = "Fatal: Errore creazione passo per richiesta" . $this->Pratica . " " . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
    }

    private function importaspese2($Pratica, $anno, $numero, $protocollo) {
        $sql = "SELECT oneri_urbanizzazzione FROM oneri WHERE pratica_n = '$numero' AND rich_prot = '$protocollo' AND anno = '$anno' AND oneri_urbanizzazzione <> ''";
        $oneri_tab = ItaDB::DBSQLSelect($this->LAMA2_DB, $sql, true);

        $i = 0;
        $valore = array();
        foreach ($oneri_tab as $oneri_rec) {
            foreach ($oneri_rec as $key => $onere) {
                $appoggio = str_replace(".", "", $onere);
                if (is_numeric(str_replace(".", "", $appoggio))) {
                    $importo = str_replace(".", "", $appoggio);
                    $note = '';
                } else {
                    $importo = '';
                    $note = $onere;
                }


                $costo = array(
                    'IMPONUM' => $Pratica, // numero pratica
                    'IMPOCOD' => '9', // tipo pagamento
                    'IMPOPROG' => $i, // progressivo pagamento
                    'IMPORTO' => $importo,
                    'PAGATO' => $importo,
                    'DIFFERENZA' => '',
                    // 'DATAREG' => $oneri_rec, da valutare se qua metter edata fittizia per campo obbligatorio
                    'NOTE' => $note
                );
                try {
                    ItaDB::DBInsert($this->PRAM_DB, 'PROIMPO', 'ROWID', $costo);
                    $i++;
                } catch (Exception $ex) {
                    $testo = "Fatal: Errore Inserimento spesa per fascicolo N " . $this->Pratica . " " . $ex->getMessage();
                    $this->scriviLog($testo);
                    return false;
                }
            }
        }
        return true;
    }

    private function cleanCF($cf) {
        $pi = str_replace('P.I.', '', $cf); // elimina P.I
        $cf = str_replace('C.F.', '', $pi); // elimina C.F
        $result = str_replace('_', '', $cf); // elimina ___
        return str_replace(' ', '', $result); // elimina spazi
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

    private function rilevaPraCom() {
        // $sql = "SELECT ROWID, PASKEY,PASDATADOC FROM PASDOC WHERE PASEVI <> 0 AND ROWID <= 33051";   // SE LI METTO TUTTI MI CRASHA APACHE
        // $sql = "SELECT ROWID, PASKEY,PASDATADOC FROM PASDOC WHERE PASEVI <> 0 AND ROWID BETWEEN '33052' AND '66103'";   // SE LI METTO TUTTI MI CRASHA APACHE
        // $sql = "SELECT * FROM PASDOC WHERE PASEVI <> 0 AND ROWID BETWEEN '66104' AND '132206'";   // SE LI METTO TUTTI MI CRASHA APACHE
        // $sql = "SELECT ROWID, PASKEY,PASDATADOC FROM PASDOC WHERE PASEVI <> 0 AND ROWID BETWEEN '132207' AND '264412'";   // SE LI METTO TUTTI MI CRASHA APACHE
        //$sql = "SELECT ROWID, PASKEY,PASDATADOC FROM PASDOC WHERE PASEVI <> 0 AND ROWID BETWEEN '264413' AND '396618'";  // SE LI METTO TUTTI MI CRASHA APACHE
        $sql = "SELECT ROWID, PASKEY,PASDATADOC FROM PASDOC WHERE PASEVI <> 0 AND ROWID BETWEEN '396619' AND '528824'";   // SE LI METTO TUTTI MI CRASHA APACHE
        $allegati_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $i = 0;
        foreach ($allegati_tab as $allegati_rec) {
            // Out::msgInfo("allegati rec ", print_r($allegati_rec, true));
            $sql_passo = "SELECT PROPAK,PRONUM FROM PROPAS WHERE PRONUM = '" . $allegati_rec['PASKEY'] . "' AND PROINI = '" . $allegati_rec['PASDATADOC'] . "' ORDER BY ROWID ASC";
            $passo_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_passo, false);
            // Out::msgInfo("RISULTATO PROPAK", print_r($passo_rec,true));
            if (empty($passo_rec)) {
                //  $testo = "PROPAK DI PAS DOC NON RILEVATO PER PASKEY" . $allegati_rec['PASKEY'] . " PER LA DATA " . $allegati_rec['PASDATADOC'];
                //  $this->scriviLog($testo);
                continue;
            }
            /// PASLAC ESTERNO  SU PASDOC 
            //PASKEY DI PASDOC CON PROPAK RICAVATO
            // up date documento con propak. + creazione pra com oin partenza o in arrivo
            $All = array(
                "ROWID" => $allegati_rec['ROWID'],
                "PASCLA" => 'ESTERNO',
                "PASKEY" => $passo_rec['PROPAK'],
                    // "PASEVI" => '0'
            );
            try {
                // Out::msgInfo("pp", print_r($All,true));
                ItaDB::DBUpdate($this->PRAM_DB, 'PASDOC', 'ROWID', $All);
                // $this->getPracom($passo_rec['PRONUM'], $passo_rec['PROPAK'], $tipo);
                $i++;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore Assegnazione pasdoc ROWID ->" . $allegati_rec['ROWID'] . $ex->getMessage();
                //$testo = "Fatal: Errore Assegnazione pasdoc pra com " . $Relazione_rec['GESNUM'] . " Id relazione " . $Relazione_rec['GESMIGRA'] . $ex->getMessage();
                $this->scriviLog($testo);
                continue;
            }
        }
        Out::msgInfo("", "Associazione allegati PASSI TERMINATA N° " . $i);
        return true;
    }

    private function getPracom() {
//        SELECT DISTINCT(`PASKEY`),PASEVI,ROWID FROM `PASDOC` WHERE LENGTH(PASKEY)>11
        $sql = "SELECT DISTINCT(`PASKEY`),PASEVI,ROWID FROM `PASDOC` WHERE LENGTH(PASKEY)>11 GROUP BY PASKEY";   // SE LI METTO TUTTI MI CRASHA APACHE
        $allegati_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $i = 0;
        foreach ($allegati_tab as $allegati_rec) {

            if ($allegati_rec['PASEVI'] == '2') {
                $tipo = 'A';
            } elseif ($allegati_rec['PASEVI'] == '1') {
                $tipo = 'P';
            }


            $pracom_rec = array(
                // "ROWID" => $this->rowidPracom,
                "COMNUM" => substr($allegati_rec['PASKEY'], 0, 10),
                "COMPAK" => $allegati_rec['PASKEY'],
                //"COMPRT" => $prot,
                //  "COMDPR" => $data,
                "COMTIP" => $tipo,
                    // "PASSHA2SOST" => '1' // togli flag allegato sostituito
            );

            try {
                ItaDB::DBInsert($this->PRAM_DB, 'PRACOM', 'ROWID', $pracom_rec);
                $i++;
            } catch (Exception $ex) {
                $testo = "Fatal: Errore creazione Record PRACOM pratica" . $pratica . " propak : " . $propak;
                $this->scriviLog($testo);
                // return false;
            }
        }
        Out::msgInfo("", "terminato secondo ciclopracom N° " . $i);
        return true;
    }

}

/*
  TRUNCATE `PROGES`;
  TRUNCATE `PROPAS`;
  TRUNCATE `PRACOM`;
  TRUNCATE `ANADES`;
  TRUNCATE `PASDOC`;
  TRUNCATE `PRAIMM`;
  TRUNCATE `PRODAG`;
  TRUNCATE `PROIMPO`;
  TRUNCATE `NOTE`;
  TRUNCATE `NOTECLAS`;
  TRUNCATE `PROCONCILIAZIONE`;
 * 
 */
?>
