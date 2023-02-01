<?php

/**
 *
 * ANAGRAFICA FASCICOLI ELETTRONICI
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    11.01.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEnvVars.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';

class praLibVariabili {

    const VAR_FONTE_DESTINATARI_PASSO = '1';
    const VAR_FONTE_ALLEGATI_PASSO = '2';
    const VAR_FONTE_ALLEGATI_FRONT = '3';
    const VAR_FONTE_ALLEGATI_FRONT_NON_VALIDI = '4';
    const VAR_FONTE_SOGGETTI_ESIBENTE = '5';
    const VAR_FONTE_SOGGETTI_DICHIARANTE = '6';
    const VAR_FONTE_SOGGETTI_SOCI = '7';
    const VAR_FONTE_SOGGETTI_IMPRESA = '8';
    const VAR_FONTE_SOGGETTI_IMPRESAINDIVIDUALE = '9';
    const VAR_FONTE_IMMOBILI_PRATICA = '10';
    const VAR_FONTE_IMMOBILI_PRATICA_DA4 = '11';
    const VAR_FONTE_ALLEGATI_PASSO_PUBBLICATI = '12';
    const VAR_FONTE_FASCICOLO_ONERI = '13';
    const VAR_FONTE_FASCICOLO_PAGAMENTI = '14';
    const VAR_FONTE_DATIAGGIUNTIVI_PASSO = '15';
    const VAR_FONTE_GENERICA = '16';
    const VAR_FONTE_ALLEGATI_PRAT_NON_VALIDI = '17';

    static $VAR_FONTI_DESCR = array(
        '1' => 'Destinatari della comunicazione passo',
        '2' => 'Allegati del passo',
        '3' => 'Allegati Richiesta On-Line',
        '4' => 'Allegati Richiesta On-Line non validi',
        '5' => 'Soggetti Esibenti',
        '6' => 'Soggetti Dichiaranti',
        '7' => 'Soggetti Soci',
        '8' => 'Soggetti Impresa',
        '9' => 'Soggetti Impresa Individuale',
        '10' => 'Immobili Pratica',
        '11' => 'Immobili Pratica da 4 in poi',
        '12' => 'Allegati del Passo Pubblicati',
        '13' => 'Oneri Fascicolo',
        '14' => 'Pagamenti Oneri Fascicolo',
        '15' => 'Dati Aggiuntivi del Passo',
        '16' => 'Dati Generici',
        '17' => 'Allegati Fascicolo non validi',
    );
    private $variabiliGenerico;
    private $variabiliProcedimento;
    private $variabiliProcedimentoFO;
    private $variabiliBase;
    private $variabiliCampiAggiuntiviPratica;
    private $variabiliCampiAggiuntivi;
    private $variabiliCampiAggiuntiviPasso;
    private $variabiliCampiAggiuntiviGenerico;
    private $variabiliTipiAggiuntivi;
    private $variabiliPasso;
    private $variabiliUtente;
    private $variabiliGenerali;
    private $variabiliDestinatari;
    private $variabiliDatiDestinatari;
    private $variabiliTemplateUpload;
    private $variabiliUpload;
    private $variabiliRichiesta;
    private $variabiliAllegatiRapporto;
    private $variabiliAllegati;
    //private $variabiliAllegatiRichiesta;
    private $variabiliAnavar;
    private $variabiliSociFascicolo;
    private $variabiliSoggettoFascicolo;
    private $variabiliSoggetti;
    private $variabiliDatiSoggetti;
    private $variabiliDatiPassi;
    private $variabiliDestinatariPasso;
    private $variabiliRuoliSogg;
    private $variabiliSeqPasso;
    private $variabiliEsibente;
    private $variabiliImmobiliFascicolo;
    private $variabiliOneriPagamenti;
    private $variabiliLista;
    private $codiceProcedimento;
    private $codicePratica;
    private $codiceRichiesta;
    private $rowidPasdoc;
    private $tipoCom;
    private $chiavePasso;
    private $frontOfficeFlag = false;
    private $fromAnavar = false;
    private $extraParams;

    function __construct($extraParams = array()) {
        $this->extraParams = $extraParams;
    }

    /**
     * Assegna codice procedimento per il calcolo variabili
     * @param string $codiceProcedimento
     */
    public function setCodiceProcedimento($codiceProcedimento) {
        $this->codiceProcedimento = $codiceProcedimento;
    }

    public function setFrontOfficeFlag($frontOfficeFlag) {
        $this->frontOfficeFlag = $frontOfficeFlag;
    }

    public function setFromAnavar($fromAnavar) {
        $this->fromAnavar = $fromAnavar;
    }

    /**
     * Assegna il codice pratica per il calcolo variabili
     * @param type $codicePratica
     */
    public function setCodicePratica($codicePratica) {
        $this->codicePratica = $codicePratica;
    }

    public function setRowidPasdoc($rowidPasdoc) {
        $this->rowidPasdoc = $rowidPasdoc;
    }

    public function setTipoCom($tipoCom) {
        $this->tipoCom = $tipoCom;
    }

    /**
     * Assegna il codice pratica per il calcolo variabili
     * @param type $codicePratica
     */
    public function setCodiceRichiesta($codiceRichiesta) {
        $this->codiceRichiesta = $codiceRichiesta;
    }

    /**
     * Assegna la chiave passo per il calcolo delle variabili
     * @param type $chiavePasso
     */
    public function setChiavePasso($chiavePasso) {
        $this->chiavePasso = $chiavePasso;
    }

    public function setVariabiliAnavar() {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        $anavar_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ANAVAR WHERE VARCLA='SUAP'", true);

        $dizionario = new itaDictionary();

        foreach ($anavar_tab as $chiave => $anavar_rec) {
            $dizionario->addField($anavar_rec['VARCOD'], $anavar_rec['VARDES'], $chiave, $anavar_rec['VARTIP'], '@{$' . $anavar_rec['VARCOD'] . '}@', false, '"type":"html"');
        }

        $this->variabiliAnavar = $dizionario;

        return $this->variabiliAnavar;
    }

    public function getVariabiliOneriPag() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionario->addField('ONERI', 'Oneri', $i++, 'base');
        $dizionario->addField('PAGAMENTI', 'Pagamenti', $i++, 'base');
        return $dizionario;
    }

    /**
     * Ritorna una legenda di campi per le pratiche on-line in formato adjacency per
     * I campi non sono riferiti a procedimento o pratica
     * @param type $tipo
     * @param type $markup
     * @return type
     */
    public function getLegendaGenerico($tipo = "adjacency", $markup = 'smarty') {
        switch ($tipo) {
            case "adjacency":
                return $this->getVariabiliGenerico()->exportAdjacencyModel($markup);
            case "json":
                return $this->getVariabiliGenerico()->getDictionaryJSON($markup);

            default:
                break;
        }
    }

    public function getVariabiliGenerico() {
        $this->variabiliGenerico = new itaDictionary();
        $this->variabiliGenerico->addField('PRABASE', 'Variabili Base Procedimento', 1, 'itaDictionary', $this->getVariabiliBaseProcedimento());
        $this->variabiliGenerico->addField('PRARICHIESTA', 'Variabili Richiesta on-line', 2, 'itaDictionary', $this->getVariabiliRichiesta());
        if ($this->frontOfficeFlag == false) {
            $this->variabiliGenerico->addField('PRAPASSO', 'Variabili Passo Procedimento', 3, 'itaDictionary', $this->getVariabiliPasso());
        }
        $this->variabiliGenerico->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Procedimento', 4, 'itaDictionary', $this->getVariabiliTipiAggiuntiviProcedimento());
        $this->variabiliGenerico->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi', 5, 'itaDictionary', $this->getVariabiliCampiAggiuntiviGenerico());
        $this->variabiliGenerico->addField('PRASOGGETTI', 'Variabili Soggetti', 6, 'itaDictionary', $this->getVariabiliSoggettiDaRuolo());
        $this->variabiliGenerico->addField('PRAANAVAR', 'Variabili ANAVAR', 7, 'itaDictionary', $this->valorizzaVariabiliAnavar());
        if ($this->fromAnavar == true) {
            $this->variabiliGenerico->addField('RICHIESTA_ALLE_NV', 'Variabili Allegati della Richiesta non Validi', 8, 'itaDictionary', $this->getVariabiliAllegati());
            $this->variabiliGenerico->addField('RICHIESTA_ALLE', 'Variabili Allegati della Richiesta', 9, 'itaDictionary', $this->getVariabiliAllegati());
            $this->variabiliGenerico->addField('PASSO_ALLE', 'Variabili Allegati del Passo', 10, 'itaDictionary', $this->getVariabiliAllegati());
            $this->variabiliGenerico->addField('FASCICOLO_SOCI', 'Variabili Soci del Fascicolo', 11, 'itaDictionary', $this->getVariabiliSociFascicolo());
            $this->variabiliGenerico->addField('FASCICOLO_SOGGETTO', 'Variabili Soggetto del Fascicolo', 12, 'itaDictionary', $this->getVariabiliSoggettoFascicolo());
            $this->variabiliGenerico->addField('PASSO_DESTINATARI', 'Variabili Destinatari del Passo', 13, 'itaDictionary', $this->getVariabiliDestinatariFascicolo());
            $this->variabiliGenerico->addField('PASSO_DATIAGGIUNTIVI', 'Variabili Dati Aggiuntivi del Passo', 14, 'itaDictionary', $this->getVariabiliDatiAggiuntiviPasso());
            $this->variabiliGenerico->addField('FASCICOLO_ONERI', 'Variabili Oneri del Fascicolo', 15, 'itaDictionary', $this->getVariabiliOneriFascicolo());
        }
        $this->variabiliGenerico->addField('PRAGENERALI', 'Variabili Generali', 16, 'itaDictionary', $this->getVariabiliGenerali());
        $this->variabiliGenerico->addField('PRAUTENTE', 'Variabili Utente', 17, 'itaDictionary', $this->getVariabiliUtente());
        $this->variabiliGenerico->addField('PRAONERIPAG', 'Variabili Oneri Pagamenti', 18, 'itaDictionary', $this->getVariabiliOneriPag());
        $this->variabiliGenerico->addField('PRALISTA', 'Variabili Lista', 19, 'itaDictionary', $this->getVariabiliLista());
        $this->variabiliGenerico->addField('PRAPASSISEQ', 'Variabili Passi Fascicolo', 20, 'itaDictionary', $this->getVariabiliPassiTipo());
        return $this->variabiliGenerico;
    }

    public function getLegendaProcedimento($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliProcedimento()->exportAdjacencyModel($markup);
    }

    public function getLegendaProcedimentoFO($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliProcedimentoFO()->exportAdjacencyModel($markup);
    }

    public function getLegendaDatiAggiuntiviPasso($tipo = "adjacency", $markup = 'smarty') {
        $tempDictionary = new itaDictionary();
        $tempDictionary->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi', 1, 'itaDictionary', $this->getVariabiliCampiAggiuntiviProcedimentoPasso());
        return $tempDictionary->exportAdjacencyModel($markup);
    }

    public function getLegendaPratica($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliPratica()->exportAdjacencyModel($markup);
    }

    public function getLegendaTemplateUpload($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliTemplateUpload()->exportAdjacencyModel($markup);
    }

    public function getVariabiliTemplateUpload() {
        $this->variabiliTemplateUpload = new itaDictionary();
        $this->variabiliTemplateUpload->addField('NOMEUPLOAD', 'Variabili Template Nome Upload', 1, 'itaDictionary', $this->getVariabiliUpload());
        return $this->variabiliTemplateUpload;
    }

    public function getVariabiliProcedimento() {
        $this->variabiliProcedimento = new itaDictionary();
        $this->variabiliProcedimento->addField('PRABASE', 'Variabili Base Procedimento', 1, 'itaDictionary', $this->getVariabiliBaseProcedimento());
        $this->variabiliProcedimento->addField('PRARICHIESTA', 'Variabili Richiesta on-line', 2, 'itaDictionary', $this->getDatiVariabiliRichiesta());
        $this->variabiliProcedimento->addField('PRAPASSO', 'Variabili Passo Procedimento', 3, 'itaDictionary', $this->getVariabiliPasso());
        $this->variabiliProcedimento->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Procedimento', 3, 'itaDictionary', $this->getVariabiliTipiAggiuntiviProcedimento());
        $this->variabiliProcedimento->addField('PRABASEAGGIUNTIVI', 'Variabili Campi Aggiuntivi Base Procedimento', 2, 'itaDictionary', $this->getVariabiliCampiAggiuntiviProcedimentoBase());
        $this->variabiliProcedimento->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Procedimento', 2, 'itaDictionary', $this->getVariabiliCampiAggiuntiviProcedimento());
        $this->variabiliProcedimento->addField('PRAGENERALI', 'Variabili Generali', 4, 'itaDictionary', $this->getDatiVariabiliGenerali());
        $this->variabiliProcedimento->addField('PRAUTENTI', 'Variabili Utente', 5, 'itaDictionary', $this->getDatiVariabiliUtente());
        $this->variabiliProcedimento->addField('PRAESIBENTE', 'Variabili Esibente FO', 6, 'itaDictionary', $this->getVariabiliEsibente());
        $this->variabiliProcedimento->addField('PRAALLEGATICLA', 'Variabili Allegati Rapporto Completo', 7, 'itaDictionary', $this->getVariabiliAllegatiRapporto());
        $this->variabiliProcedimento->addField('LISTINO', 'Variabili Tariffe', 8, 'itaDictionary', $this->getVariabiliTariffe());
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $tabellaTab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ANAVAR WHERE VARCLA = 'SUAP' ", true);
        if ($tabellaTab) {
            $this->variabiliProcedimento->addField('PRAANAVAR', 'Variabili ANAVAR', 7, 'itaDictionary', $this->valorizzaVariabiliAnavar());
        }
        $this->variabiliProcedimento->addField('AMBIENTE', 'Variabili d\'ambiente', 9, 'itaDictionary', $this->getVariabiliAmbiente());
        return $this->variabiliProcedimento;
    }

    public function getVariabiliProcedimentoFO() {
        $this->variabiliProcedimentoFO = new itaDictionary();
        $this->variabiliProcedimentoFO->addField('PRAINF', 'Variabili Schede Informativa', 1, 'itaDictionary', $this->getVariabiliInformativa());
        return $this->variabiliProcedimentoFO;
    }

    public function getVariabiliPratica($all = false, $enteSuap = "") {
        $this->variabiliPratica = new itaDictionary();
        $this->variabiliPratica->addField('PRABASE', 'Variabili Base Pratica', 1, 'itaDictionary', $this->getVariabiliBasePratica());
        if ($this->frontOfficeFlag == false) {
            $this->variabiliPratica->addField('PRAPASSO', 'Variabili Passo Pratica', 2, 'itaDictionary', $this->getVariabiliPassoPratica());
        }
        $this->variabiliPratica->addField('PRASOGGETTI', 'Variabili Soggetti', 3, 'itaDictionary', $this->getVariabiliSoggetti());
        $this->variabiliPratica->addField('PRAALLEGATI', 'Variabili Allegati', 4, 'itaDictionary', $this->getDatiVariabiliAllegati());
        if ($all) {
            $this->variabiliPratica->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Pratica', 5, 'itaDictionary', $this->getAllVariabiliTipiAggiuntiviPratica());
        } else {
            $ret = $this->getVariabiliTipiAggiuntiviPratica();
            if ($ret) {
                $this->variabiliPratica->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Pratica', 5, 'itaDictionary', $this->getVariabiliTipiAggiuntiviPratica());
            }
        }

        $ret = $this->getVariabiliCampiAggiuntiviBase();
        if ($ret) {
            $this->variabiliPratica->addField('PRABASEAGGIUNTIVI', 'Variabili Campi Aggiuntivi Pratica', 6, 'itaDictionary', $ret);
        }

        $ret = $this->getVariabiliCampiAggiuntiviPassi();
        if ($ret) {
            $this->variabiliPratica->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Passi', 6, 'itaDictionary', $ret);
        }
        $this->variabiliPratica->addField('PRADESTINATARI', 'Variabili Destinatari', 7, 'itaDictionary', $this->getVariabiliDestinatari());
        $this->variabiliPratica->addField('PRAUTENTI', 'Variabili Utente', 8, 'itaDictionary', $this->getDatiVariabiliUtente());
        $this->variabiliPratica->addField('PRAGENERALI', 'Variabili Generali', 9, 'itaDictionary', $this->getDatiVariabiliGenerali());
        //
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        if ($enteSuap) {
            // Per Mondavio e Monteporzio
            $PRAM_DB = ItaDB::DBOpen('PRAM', $enteSuap);
        }
        $tabellaTab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM ANAVAR WHERE VARCLA = 'SUAP' ", true);
        if ($tabellaTab) {
            $this->variabiliPratica->addField('PRAANAVAR', 'Variabili ANAVAR', 10, 'itaDictionary', $this->valorizzaVariabiliAnavar(array(), $enteSuap, $this->extraParams));
        }
        $this->variabiliPratica->addField('PRAONERIPAG', 'Variabili Oneri Pagamenti', 11, 'itaDictionary', $this->valorizzaVariabiliOneriPag());
        $this->variabiliPratica->addField('PRALISTA', 'Variabili Lista', 12, 'itaDictionary', $this->getDatiVariabiliLista());
        $this->variabiliPratica->addField('PRAPASSISEQ', 'Variabili Passi Fascicolo', 13, 'itaDictionary', $this->getVariabiliTipoPasso());
        return $this->variabiliPratica;
    }

    public function getVariabiliPraticaSimple($all = false) {
        $this->variabiliPratica = new itaDictionary();
        $this->variabiliPratica->addField('PRABASE', 'Variabili Base Pratica', 1, 'itaDictionary', $this->getVariabiliBasePratica());
        if ($this->frontOfficeFlag == false) {
            $this->variabiliPratica->addField('PRAPASSO', 'Variabili Passo Pratica', 2, 'itaDictionary', $this->getVariabiliPassoPratica());
        }
        $this->variabiliPratica->addField('PRASOGGETTI', 'Variabili Soggetti', 3, 'itaDictionary', $this->getVariabiliSoggetti());
        $this->variabiliPratica->addField('PRAALLEGATI', 'Variabili Allegati', 4, 'itaDictionary', $this->getDatiVariabiliAllegati());
        if ($all) {
            $this->variabiliPratica->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Pratica', 5, 'itaDictionary', $this->getAllVariabiliTipiAggiuntiviPratica());
        } else {
            $ret = $this->getVariabiliTipiAggiuntiviPratica();
            if ($ret) {
                $this->variabiliPratica->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Pratica', 5, 'itaDictionary', $this->getVariabiliTipiAggiuntiviPratica());
            }
        }

        $ret = $this->getVariabiliCampiAggiuntiviBase();
        if ($ret) {
            $this->variabiliPratica->addField('PRABASEAGGIUNTIVI', 'Variabili Campi Aggiuntivi Pratica', 6, 'itaDictionary', $ret);
        }

        $ret = $this->getVariabiliCampiAggiuntiviPassi();
        if ($ret) {
            $this->variabiliPratica->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Passi', 6, 'itaDictionary', $ret);
        }
        $this->variabiliPratica->addField('PRADESTINATARI', 'Variabili Destinatari', 7, 'itaDictionary', $this->getVariabiliDestinatari());
        $this->variabiliPratica->addField('PRAUTENTI', 'Variabili Utente', 8, 'itaDictionary', $this->getDatiVariabiliUtente());
        $this->variabiliPratica->addField('PRAGENERALI', 'Variabili Generali', 9, 'itaDictionary', $this->getDatiVariabiliGenerali());

        $this->variabiliPratica->addField('PRAONERIPAG', 'Variabili Oneri Pagamenti', 11, 'itaDictionary', $this->valorizzaVariabiliOneriPag());
        $this->variabiliPratica->addField('PRALISTA', 'Variabili Lista', 12, 'itaDictionary', $this->getDatiVariabiliLista());
        $this->variabiliPratica->addField('PRAPASSISEQ', 'Variabili Passi Fascicolo', 13, 'itaDictionary', $this->getVariabiliTipoPasso());
        return $this->variabiliPratica;
    }

    public function getVariabiliBasePratica() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
//        if (!$Proges_rec) {
//            return null;
//        }
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
        $proLibSerie = new proLibSerie();
        $decod_serie = $proLibSerie->GetSerie($Proges_rec['SERIECODICE'], 'codice');
        if ($Proges_rec) {
            $Anapra_rec = $praLib->GetAnapra($Proges_rec['GESPRO']);
            $Anaset_rec = $praLib->GetAnaset($Proges_rec['GESSTT']);
            $Anaatt_rec = $praLib->GetAnaatt($Proges_rec['GESATT']);
            $Anatip_rec = $praLib->GetAnatip($Proges_rec['GESTIP']);
            $Anatsp_rec = $praLib->GetAnatsp($Proges_rec['GESTSP']);
            $Ananom_tspres_rec = $praLib->GetAnanom($Anatsp_rec['TSPRES']);

            $Anaspa_rec = $praLib->GetAnaspa($Proges_rec['GESSPA']);
            $Ananom_spares_rec = $praLib->GetAnanom($Anaspa_rec['SPARES']);

            $Ananom_prares_rec = $praLib->GetAnanom($Proges_rec['GESRES']);
            $Anades_rec = $praLib->GetAnades($Proges_rec['GESNUM']);

            $i = 1;
            $dizionario = $this->getVariabiliBaseProcedimento();

            if ($Anaspa_rec) {
                $descrizione = $Anaspa_rec['SPADES'];
                $denominazione = $Anaspa_rec['SPADES'];
                $comune = $Anaspa_rec['SPACOM'];
                $indirizzo = $Anaspa_rec['SPAIND'];
                $civico = $Anaspa_rec['SPANCI'];
                $provincia = $Anaspa_rec['SPAPRO'];
                $cap = $Anaspa_rec['SPACAP'];
                $codiceCatasto = $Anaspa_rec['SPACCA'];
                $codiceIstat = $Anaspa_rec['SPAIST'];
                $Tesoreria = $Anaspa_rec['SPATES'];
                $dest = $Anaspa_rec['SPADEST'];
                $iban = $Anaspa_rec['SPAIBAN'];
                $banca = $Anaspa_rec['SPABANCA'];
                $swift = $Anaspa_rec['SPASWIFT'];
                $cc = $Anaspa_rec['SPACC'];
                $ccp = $Anaspa_rec['SPACCP'];
                $causalecc = $Anaspa_rec['SPACAUSALECC'];
                $causaleccp = $Anaspa_rec['SPACAUSALECCP'];
            } else {
                $descrizione = $Anatsp_rec['TSPDES'];
                $denominazione = $Anatsp_rec['TSPDEN'];
                $comune = $Anatsp_rec['TSPCOM'];
                $indirizzo = $Anatsp_rec['TSPIND'];
                $civico = $Anatsp_rec['TSPNCI'];
                $provincia = $Anatsp_rec['TSPPRO'];
                $cap = $Anatsp_rec['TSPCAP'];
                $codiceCatasto = $Anatsp_rec['TSPCCA'];
                $codiceIstat = $Anatsp_rec['TSPIST'];
                $Tesoreria = $Anatsp_rec['TSPTES'];
                $dest = $Anatsp_rec['TSPDEST'];
                $iban = $Anatsp_rec['TSPIBAN'];
                $banca = $Anatsp_rec['TSPBANCA'];
                $swift = $Anatsp_rec['TSPSWIFT'];
                $cc = $Anatsp_rec['TSPCC'];
                $ccp = $Anatsp_rec['TSPCCP'];
                $causalecc = $Anatsp_rec['TSPCAUSALECC'];
                $causaleccp = $Anatsp_rec['TSPCAUSALECCP'];
            }
            $dizionario->addFieldData('SPT_DES', $descrizione);
            $dizionario->addFieldData('SPT_DEN', $denominazione);
            $dizionario->addFieldData('SPT_COM', $comune);
            $dizionario->addFieldData('SPT_IND', $indirizzo);
            $dizionario->addFieldData('SPT_CIV', $civico);
            $dizionario->addFieldData('SPT_PRV', $provincia);
            $dizionario->addFieldData('SPT_CAP', $cap);
            $dizionario->addFieldData('SPT_CCA', $codiceCatasto);
            $dizionario->addFieldData('SPT_CIS', $codiceIstat);
            $dizionario->addFieldData('SPT_TES', $Tesoreria);
            $dizionario->addFieldData('SPT_DEST', $dest);
            $dizionario->addFieldData('SPT_IBAN', $iban);
            $dizionario->addFieldData('SPT_BANCA', $banca);
            $dizionario->addFieldData('SPT_SWIFT', $swift);
            $dizionario->addFieldData('SPT_CC', $cc);
            $dizionario->addFieldData('SPT_CCP', $ccp);
            $dizionario->addFieldData('SPT_CAUSALECC', $causalecc);
            $dizionario->addFieldData('SPT_CAUSALECCP', $causaleccp);
            //
            $dizionario->addFieldData('TSPDES', $Anatsp_rec['TSPDES']);
            $dizionario->addFieldData('TSPDEN', $Anatsp_rec['TSPDEN']);
            $dizionario->addFieldData('TSPCOM', $Anatsp_rec['TSPCOM']);
            $dizionario->addFieldData('TSPIND', $Anatsp_rec['TSPIND']);
            $dizionario->addFieldData('TSPNCI', $Anatsp_rec['TSPNCI']);
            $dizionario->addFieldData('TSPPRO', $Anatsp_rec['TSPPRO']);
            $dizionario->addFieldData('TSPCAP', $Anatsp_rec['TSPCAP']);
            $dizionario->addFieldData('TSPCCA', $Anatsp_rec['TSPCCA']);
            $dizionario->addFieldData('TSPIST', $Anatsp_rec['TSPIST']);
            $dizionario->addFieldData('TSPPEC', $Anatsp_rec['TSPPEC']);
            $dizionario->addFieldData('TSPWEB', $Anatsp_rec['TSPWEB']);
            $dizionario->addFieldData('TSPMOD', $Anatsp_rec['TSPMOD']);

            $dizionario->addFieldData('TSPRES_NOM', $Ananom_tspres_rec['NOMCOG'] . " " . $Ananom_tspres_rec['NOMNOM']);
            $dizionario->addFieldData('TSPRES_ORARIO', $Ananom_tspres_rec['NOMANN']);
            $dizionario->addFieldData('TSPRES_NOMEML', $Ananom_tspres_rec['NOMEML']);
            $dizionario->addFieldData('TSPRES_NOMTEL', $Ananom_tspres_rec['NOMTEL']);
            $dizionario->addFieldData('TSPRES_NOMFAX', $Ananom_tspres_rec['NOMFAX']);
            $dizionario->addFieldData('TSPTES', $Anatsp_rec['TSPTES']);
            $dizionario->addFieldData('TSPDEST', $Anatsp_rec['TSPDEST']);
            $dizionario->addFieldData('TSPIBAN', $Anatsp_rec['TSPIBAN']);
            $dizionario->addFieldData('TSPBANCA', $Anatsp_rec['TSPBANCA']);
            $dizionario->addFieldData('TSPSWIFT', $Anatsp_rec['TSPSWIFT']);
            $dizionario->addFieldData('TSPCC', $Anatsp_rec['TSPCC']);
            $dizionario->addFieldData('TSPCCP', $Anatsp_rec['TSPCCP']);
            $dizionario->addFieldData('TSPCAUSALECC', $Anatsp_rec['TSPCAUSALECC']);
            $dizionario->addFieldData('TSPCAUSALECCP', $Anatsp_rec['TSPCAUSALECCP']);

            $dizionario->addFieldData('SPADES', $Anaspa_rec['SPADES']);
            $dizionario->addFieldData('SPACOM', $Anaspa_rec['SPACOM']);
            $dizionario->addFieldData('SPAIND', $Anaspa_rec['SPAIND']);
            $dizionario->addFieldData('SPANCI', $Anaspa_rec['SPANCI']);
            $dizionario->addFieldData('SPAPRO', $Anaspa_rec['SPAPRO']);
            $dizionario->addFieldData('SPACAP', $Anaspa_rec['SPACAP']);
            $dizionario->addFieldData('SPACCA', $Anaspa_rec['SPACCA']);
            $dizionario->addFieldData('SPAIST', $Anaspa_rec['SPAIST']);
            $dizionario->addFieldData('SPATES', $Anaspa_rec['SPATES']);
            $dizionario->addFieldData('SPADEST', $Anaspa_rec['SPADEST']);
            $dizionario->addFieldData('SPAIBAN', $Anaspa_rec['SPAIBAN']);
            $dizionario->addFieldData('SPABANCA', $Anaspa_rec['SPABANCA']);
            $dizionario->addFieldData('SPASWIFT', $Anaspa_rec['SPASWIFT']);
            $dizionario->addFieldData('SPACC', $Anaspa_rec['SPACC']);
            $dizionario->addFieldData('SPACCP', $Anaspa_rec['SPACCP']);
            $dizionario->addFieldData('SPACAUSALECC', $Anaspa_rec['SPACAUSALECC']);
            $dizionario->addFieldData('SPACAUSALECCP', $Anaspa_rec['SPACAUSALECCP']);
            $dizionario->addFieldData('SPADISABILITAPAGOPA', $Anaspa_rec['SPADISABILITAPAGOPA']);

            $dizionario->addFieldData('SPARES_NOM', $Ananom_spares_rec['NOMCOG'] . " " . $Ananom_spares_rec['NOMNOM']);
            $dizionario->addFieldData('SPARES_ORARIO', $Ananom_spares_rec['NOMANN']);
            $dizionario->addFieldData('SPARES_NOMEML', $Ananom_spares_rec['NOMEML']);
            $dizionario->addFieldData('SPARES_NOMEML', $Ananom_spares_rec['NOMEML']);
            $dizionario->addFieldData('SPARES_NOMTEL', $Ananom_spares_rec['NOMTEL']);
            $dizionario->addFieldData('SPARES_NOMFAX', $Ananom_spares_rec['NOMFAX']);

            $dizionario->addFieldData('SERIEPROGRESSIVO', $Proges_rec['SERIEPROGRESSIVO']);
            $dizionario->addFieldData('SERIEANNO', $Proges_rec['SERIEANNO']);
            $dizionario->addFieldData('DECOD_SERIE', $decod_serie['SIGLA']);
            $dizionario->addFieldData('SERIE_FORMATTED', $decod_serie['SIGLA'] . "/" . $Proges_rec['SERIEANNO'] . "/" . $Proges_rec['SERIEPROGRESSIVO']);
            $dizionario->addFieldData('GESNUM', $Proges_rec['GESNUM']);
            $dizionario->addFieldData('GESNUM_FORMATTED', substr($Proges_rec['GESNUM'], 4, 6) . "/" . substr($Proges_rec['GESNUM'], 0, 4));
            $dizionario->addFieldData('GESCODPROC', $Proges_rec['GESCODPROC']);
            $dizionario->addFieldData('GESDRI', date("d/m/Y", strtotime($Proges_rec['GESDRI'])));
            $dizionario->addFieldData('GESDRE', date("d/m/Y", strtotime($Proges_rec['GESDRE'])));
            $dizionario->addFieldData('GESNOT', $Proges_rec['GESNOT']);
            $dizionario->addFieldData('RICNUM', $Proges_rec['GESPRA']);
            $dizionario->addFieldData('RICNUM_FORMATTED', substr($Proges_rec['GESPRA'], 4, 6) . "/" . substr($Proges_rec['GESPRA'], 0, 4));
            $proric_rec = $praLib->GetProric($Proges_rec['GESPRA']);
            $dizionario->addFieldData('RICRPA', $proric_rec['RICRPA']);
            $dizionario->addFieldData('RICRPA_FORMATTED', substr($proric_rec['RICRPA'], 4, 6) . "/" . substr($proric_rec['RICRPA'], 0, 4));
            $dizionario->addFieldData('RICRUN', $proric_rec['RICRUN']);
            $dizionario->addFieldData('RICRUN_FORMATTED', substr($proric_rec['RICRUN'], 4, 6) . "/" . substr($proric_rec['RICRUN'], 0, 4));
            $dizionario->addFieldData('RICEVE', $proric_rec['RICEVE']);
            $dizionario->addFieldData('PRANUM', $Anapra_rec['PRANUM']);
            $dizionario->addFieldData('PRADES', $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4']);
            $dizionario->addFieldData('TIPDES', $Anatip_rec['TIPDES']);

            $dizionario->addFieldData('GESNPR_NM', substr($Proges_rec['GESNPR'], 4, 6));

            $RICRPA_PROCEDIMENTO = '';
            if ($proric_rec['RICRPA']) {
                $sql = "SELECT RICPRO FROM PRORIC WHERE RICNUM = '{$proric_rec['RICRPA']}'";
                $proric_rpa_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
                if ($proric_rpa_rec) {
                    $RICRPA_PROCEDIMENTO = $proric_rpa_rec['RICPRO'];
                }
            }

            $dizionario->addFieldData('RICRPA_PROCEDIMENTO', $RICRPA_PROCEDIMENTO);

            include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
            //$metaDati = proIntegrazioni::GetMetedatiProt($Proges_rec['GESNUM']);
            $metaDati = unserialize($Proges_rec['GESMETA']);
            //if (isset($metaDati['Data'])) {
            if (is_array($metaDati)) {
                //$dataProtFasc = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                $dataProtFasc = $praLib->GetDataProtNormalizzata($metaDati);
                $dizionario->addFieldData('DATA_PRT', $dataProtFasc);
            }
            $dizionario->addFieldData('GESNPR_AA', substr($Proges_rec['GESNPR'], 0, 4));

            $dizionario->addFieldData('SETDES', $Anaset_rec['SETDES']);
            $dizionario->addFieldData('ATTDES', $Anaatt_rec['ATTDES']);
            $dizionario->addFieldData('GESOGG', $Proges_rec['GESOGG']);

            $dizionario->addFieldData('PRARES_NOM', $Ananom_prares_rec['NOMCOG'] . " " . $Ananom_prares_rec['NOMNOM']);
            $dizionario->addFieldData('PRARES_ORARIO', $Ananom_prares_rec['NOMANN']);
            $dizionario->addFieldData('PRARES_NOMEML', $Ananom_prares_rec['NOMEML']);
            $dizionario->addFieldData('PRARES_NOMTEL', $Ananom_prares_rec['NOMTEL']);
            $dizionario->addFieldData('PRARES_NOMFAX', $Ananom_prares_rec['NOMFAX']);


            $dizionario->addFieldData('RICSOG', $Anades_rec['DESNOM']);
            $dizionario->addFieldData('RICCOG', $Anades_rec['DESCOGNOME']);
            $dizionario->addFieldData('RICNOM', $Anades_rec['DESNOME']);
            $dizionario->addFieldData('RICVIA', $Anades_rec['DESIND']);
            $dizionario->addFieldData('RICCAP', $Anades_rec['DESCAP']);
            $dizionario->addFieldData('RICCOM', $Anades_rec['DESCIT']);
            $dizionario->addFieldData('RICPRV', $Anades_rec['DESPRO']);
            $dizionario->addFieldData('RICNAZ', $Anades_rec['DESNAZ']);
            $dizionario->addFieldData('RICNAS', $Anades_rec['DESDAT']);
            $dizionario->addFieldData('RICFIS', $Anades_rec['DESFIS']);
            $dizionario->addFieldData('RICEMA', $Anades_rec['DESEMA']);
        } else {
            //
            // Usato solo come descrizione su parametri vari per oggetto protocollo
            //
            $dizionario = $this->getVariabiliBaseProcedimento();
        }
        return $this->variabiliBase;
    }

    public function getVariabiliCampiAggiuntiviPratica() {
        $praLib = new praLib();
        $variabiliCampiAggiuntivi = new itaDictionary();
        $PRAM_DB = $praLib->getPRAMDB();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
        if ($Proges_rec) {
            $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL                
            FROM
                PRODAG
            WHERE DAGNUM='" . $this->codicePratica . "' AND DAGPAK='" . $this->codicePratica . "'";

            $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        } else {
            $j = 0;
            $Prodag_tab[$j]['DAGDES'] = "Denominazione Impresa";
            $Prodag_tab[$j]['DAGKEY'] = "DENOMINAZIONE_IMPRESA";

            $j += 1;
            $Prodag_tab[$j]['DAGDES'] = "Codice Fiscale Insediamento Produttivo";
            $Prodag_tab[$j]['DAGKEY'] = "CF_IMPRESA";

            $j += 1;
            $Prodag_tab[$j]['DAGDES'] = "Comune Ins. produttivo";
            $Prodag_tab[$j]['DAGKEY'] = "COMUNEDESTINATARIO";
        }

        $i = 0;
        if ($Prodag_tab) {
            foreach ($Prodag_tab as $Prodag_rec) {
                $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGKEY'];
                $variabiliCampiAggiuntivi->addField($Prodag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL']);
            }
            $this->variabiliCampiAggiuntiviPratica = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntiviPratica = null;
        }
        return $this->variabiliCampiAggiuntiviPratica;
    }

    public function getVariabiliGenerali() {
        $i = 0;
        $dizionario = new itaDictionary();
        $dizionario->addField('GIORNOSETTIMANA', 'Giorno della settimana', $i++, 'base');
        $dizionario->addField('DATAODIERNA', 'Data odierna', $i++, 'base');
        $dizionario->addField('TIMESTAMP', 'Time Stamp', $i++, 'base');
        $dizionario->addField('OGGETTO_DOMANDA', 'Oggetto Domanda', $i++, 'base');
        $this->variabiliGenerali = $dizionario;
        return $this->variabiliGenerali;
    }

    public function getDatiVariabiliGenerali() {
        $praLib = new praLib();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
        if ($Proges_rec) {
            $dizionario = $this->getVariabiliGenerali();
            $dizionario->addFieldData('DATAODIERNA', substr(date("Ymd"), 6, 2) . "/" . substr(date("Ymd"), 4, 2) . "/" . substr(date("Ymd"), 0, 4));
            $dizionario->addFieldData('TIMESTAMP', date("Y-m-d") . " " . date("H:i:s"));
            $prodag_rec_oggDomanda = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRODAG WHERE DAGNUM = '$this->codicePratica' AND DAGKEY = 'OGGETTO_DOMANDA' AND DAGVAL <> ''", false);
            $dizionario->addFieldData('OGGETTO_DOMANDA', $prodag_rec_oggDomanda['DAGVAL']);
//            unlink($filetemp);
        } else {
            $dizionario = $this->getVariabiliGenerali();
        }
        return $this->variabiliGenerali;
    }

    public function getVariabiliAllegatiRapporto() {
        $i = 0;
        $dizionario = new itaDictionary();
        $dizionario->addField('CLASSIFICAZIONE', 'Classificazione Allegato', $i++, 'base');
        $dizionario->addField('DESTINAZIONE', 'Destinazione Allegato', $i++, 'base');
        $dizionario->addField('NOTE', 'Note Allegato', $i++, 'base');
        $dizionario->addField('DOCNAME', 'Nome Allegato', $i++, 'base');
        $this->variabiliAllegatiRapporto = $dizionario;
        return $this->variabiliAllegatiRapporto;
    }

    public function getVariabiliUtente() {
        $i = 0;
        $dizionario = new itaDictionary();
        $dizionario->addField('COGNOME', 'Cognome Dipendente', $i++, 'base');
        $dizionario->addField('NOME', 'Nome Dipendente', $i++, 'base');
        $dizionario->addField('ORARIO', 'Orario al Pubblico', $i++, 'base');
        $dizionario->addField('EMAIL', 'E-mail Dipendente', $i++, 'base');
        $dizionario->addField('TELEFONO', 'Telefono Dipendente', $i++, 'base');
        $dizionario->addField('FAX', 'Fax Dipendente', $i++, 'base');
        $this->variabiliUtente = $dizionario;
        return $this->variabiliUtente;
    }

    public function getVariabiliEsibente() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili Esibente
        //
        $dizionario->addField('COGNOME', 'Cognome', $i++, 'base');
        $dizionario->addField('NOME', 'Nome', $i++, 'base');
        $dizionario->addField('PEC', 'Pec', $i++, 'base');
        $dizionario->addField('CODICEFISCALE', 'Codice Fiscale', $i++, 'base');
        $dizionario->addField('RESIDENZAVIA', 'Indirizzo', $i++, 'base');
        $dizionario->addField('RESIDENZACIVICO', 'Civico', $i++, 'base');
        $dizionario->addField('RESIDENZACOMUNE', 'Comune', $i++, 'base');
        $dizionario->addField('RESIDENZACAP', 'Cap', $i++, 'base');
        $dizionario->addField('RESIDENZAPROVINCIA', 'Provincia', $i++, 'base');
        $dizionario->addField('TELEFONO', 'Telefono', $i++, 'base');
        $dizionario->addField('SEDEISCRIZIONE', 'Sede Iscrizione', $i++, 'base');
        $dizionario->addField('NUMEROISCRIZIONE', 'Numero Iscrizione', $i++, 'base');
        $dizionario->addField('CITY_PROGSOGG', 'Codice Soggetto CityWare', $i++, 'base');
        $dizionario->addField('RESIDENTE', 'Indicatore Soggetto residente (SI/NO)', $i++, 'base');
        $this->variabiliEsibente = $dizionario;
        return $this->variabiliEsibente;
    }

    public function getDatiVariabiliUtente() {
        $praLib = new praLib();
        $Utenti_rec = $praLib->GetUtente(App::$utente->getKey('idUtente'), 'codiceUtente');
        if ($Utenti_rec['UTEANA__3']) {
            $Ananom_rec = $praLib->GetAnanom($Utenti_rec['UTEANA__3']);
            $dizionario = $this->getVariabiliUtente();
            $dizionario->addFieldData('COGNOME', $Ananom_rec['NOMCOG']);
            $dizionario->addFieldData('NOME', $Ananom_rec['NOMNOM']);
            $dizionario->addFieldData('ORARIO', $Ananom_rec['NOMANN']);
            $dizionario->addFieldData('EMAIL', $Ananom_rec['NOMEML']);
            $dizionario->addFieldData('TELEFONO', $Ananom_rec['NOMTEL']);
            $dizionario->addFieldData('FAX', $Ananom_rec['NOMFAX']);
        } else {
            $dizionario = $this->getVariabiliUtente();
        }
        $this->variabiliUtente = $dizionario;
        return $this->variabiliUtente;
    }

    public function getVariabiliRichiesta() {
        $i = 0;
        $dizionario = new itaDictionary();

        $dizionario->addField('RICSOG', 'Intestatario', $i++, 'base');
        $dizionario->addField('RICORE', 'Orario Apertura Richiesta', $i++, 'base');
        $dizionario->addField('RICDRE', 'Data Apertura Richiesta', $i++, 'base');
        $dizionario->addField('RICEMA', 'E-mail Intestatario', $i++, 'base');
        $dizionario->addField('RICDAT', 'Data Inoltro Richiesta', $i++, 'base');
        $this->variabiliRichiesta = $dizionario;
        return $this->variabiliRichiesta;
    }

    public function getDatiVariabiliRichiesta() {
        $praLib = new praLib();
        $Proric_rec = $praLib->GetProric($this->codiceRichiesta);
        if ($Proric_rec) {
            $dizionario = $this->getVariabiliRichiesta();
            $dizionario->addField('RICSOG', $Proric_rec['RICCOG'] . " " . $Proric_rec['RICNOM']);
            $dizionario->addField('RICORE', $Proric_rec['RICORE']);
            $dizionario->addField('RICDRE', $Proric_rec['RICDRE']);
            $dizionario->addField('RICEMA', $Proric_rec['RICEMA']);
            $dizionario->addField('RICDAT', substr($Proric_rec['RICDAT'], 6, 2) . "/" . substr($Proric_rec['RICDAT'], 4, 2) . "/" . substr($Proric_rec['RICDAT'], 0, 4));
        } else {
            $dizionario = $this->getVariabiliRichiesta();
        }
        $this->variabiliRichiesta = $dizionario;
        return $this->variabiliRichiesta;
    }

    public function getVariabiliDestinatari() {
        $praLib = new praLib();
        $praMitDest_tab = $praLib->GetPraDestinatari($this->chiavePasso, "codice", true);
        $this->variabiliDestinatari = new itaDictionary();
        $i = 0;
        if ($praMitDest_tab) {
            foreach ($praMitDest_tab as $praMitDest_rec) {
                $i += 1;
                $this->variabiliDestinatari->addField("DESTINATARIO$i", "Destinatario " . $praMitDest_rec['NOME'], 7, 'itaDictionary', $this->getDatiVariabiliDestinatari($praMitDest_rec));
            }
        } else {
            $this->variabiliDestinatari = null;
        }
        return $this->variabiliDestinatari;
    }

    public function getVariabiliSoggetti() {
        $praLib = new praLib();
        $Anades_tab = $praLib->GetAnades($this->codicePratica, "codice", true);
        $this->variabiliSoggetti = new itaDictionary();
        $index = array();
        if ($Anades_tab) {
            foreach ($Anades_tab as $key => $Anades_rec) {
                $Anaruo_rec = $praLib->GetAnaruo($Anades_rec['DESRUO']);
                if ($Anaruo_rec['RUODIS'] == 0) {
                    if (in_array($Anaruo_rec['RUOCOD'], array_keys($index))) {
                        // PER PIù SOGGETTI CON LO STESSO RUOLO
                        $this->variabiliSoggetti->addField("SOGGETTO" . $Anaruo_rec['RUOCOD'] . "_" . $index[$Anaruo_rec['RUOCOD']], $Anaruo_rec['RUODES'] . " - Soggetto " . $Anades_rec['DESNOM'], 7, 'itaDictionary', $this->getDatiVariabiliSoggetti($Anades_rec));
                        $index[$Anaruo_rec['RUOCOD']] ++;
                        continue;
                    }
//                    $index[$key] = $Anaruo_rec['RUOCOD'];
                    $index[$Anaruo_rec['RUOCOD']] = 1;
                    $this->variabiliSoggetti->addField("SOGGETTO" . $Anaruo_rec['RUOCOD'], $Anaruo_rec['RUODES'] . " - Soggetto " . $Anades_rec['DESNOM'], 7, 'itaDictionary', $this->getDatiVariabiliSoggetti($Anades_rec));
                }
            }
        } else {
            $this->variabiliSoggetti = null;
        }
        return $this->variabiliSoggetti;
    }

    public function getDatiVariabiliDestinatari($praMitDest_rec) {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("NOME", "Denominazione", $i++, 'base', $praMitDest_rec['NOME']);
        $dizionario->addField("FISCALE", "Codice Fiscale", $i++, 'base', $praMitDest_rec['FISCALE']);
        $dizionario->addField("INDIRIZZO", "Indirizzo", $i++, 'base', $praMitDest_rec['INDIRIZZO']);
        $dizionario->addField("COMUNE", "Comune", $i++, 'base', $praMitDest_rec['COMUNE']);
        $dizionario->addField("CAP", "Cap", $i++, 'base', $praMitDest_rec['CAP']);
        $dizionario->addField("PROVINCIA", "Provincia", $i++, 'base', $praMitDest_rec['PROVINCIA']);
        $dizionario->addField("MAIL", "E-Mail", $i++, 'base', $praMitDest_rec['MAIL']);
        $this->variabiliDatiDestinatari = $dizionario;
        return $this->variabiliDatiDestinatari;
    }

    public function getDatiVariabiliSoggetti($Anades_rec) {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("DESNOM", "Denominazione", $i++, 'base', $Anades_rec['DESNOM']);
        $dizionario->addField("DESCOGNOME", "Cognome", $i++, 'base', $Anades_rec['DESCOGNOME']);
        $dizionario->addField("DESNOME", "Nome", $i++, 'base', $Anades_rec['DESNOME']);
        $dizionario->addField("DESRAGSOC", "Ragione Sociale", $i++, 'base', $Anades_rec['DESRAGSOC']);
        $dizionario->addField("DESFIS", "Codice Fiscale", $i++, 'base', $Anades_rec['DESFIS']);
        $dizionario->addField("DESPIVA", "Partita Iva", $i++, 'base', $Anades_rec['DESPIVA']);
        $dizionario->addField("DESIND", "Indirizzo", $i++, 'base', $Anades_rec['DESIND']);
        $dizionario->addField("DESCIV", "Civico", $i++, 'base', $Anades_rec['DESCIV']);
        $dizionario->addField("DESCIT", "Comune", $i++, 'base', $Anades_rec['DESCIT']);
        $dizionario->addField("DESCAP", "Cap", $i++, 'base', $Anades_rec['DESCAP']);
        $dizionario->addField("DESPRO", "Provincia", $i++, 'base', $Anades_rec['DESPRO']);
        $dizionario->addField("DESEMA", "E-Mail", $i++, 'base', $Anades_rec['DESEMA']);
        $dizionario->addField("DESPEC", "Pec", $i++, 'base', $Anades_rec['DESPEC']);
        $this->variabiliDatiSoggetti = $dizionario;
        return $this->variabiliDatiSoggetti;
    }

    public function getDatiVariabiliPassi($Propas_rec) {
        $praLib = new praLib();
        $dizionario = new itaDictionary();
        $i = 1;

        $PracomP_rec = $praLib->GetPracomP($Propas_rec['PROPAK']);
        $PracomA_rec = $praLib->GetPracomA($Propas_rec['PROPAK'], $PracomP_rec['ROWID']);
        $TipologiaDoc = $praLib->GetAnadoctipreg($Propas_rec['PRODOCTIPREG']);
        $proric_rec = $praLib->GetProric($Propas_rec['PRORIN']);
        $Anapra_rec = $praLib->GetAnapra($proric_rec['RICPRO']);
        $Ananom_rec = $praLib->GetAnanom($Propas_rec['PRORPA']);

        $dizionario->addField("PRORIN_FORMATTED", "Richiesta on-line integrazione formatted", $i++, 'base', substr($Propas_rec['PRORIN'], 4) . "/" . substr($Propas_rec['PRORIN'], 0, 4));
        $dizionario->addField("DESC_PROC_INTEGR", "Descrizione procedimento di integrazione", $i++, 'base', $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4']);
        $dizionario->addField("PROSEQ", "Sequenza", $i++, 'base', $Propas_rec['PROSEQ']);
        $dizionario->addField("PRODPA", "Descrizione", $i++, 'base', $Propas_rec['PRODPA']);
        $dizionario->addField("PRORIN", "Data Creazione Passo", $i++, 'base', substr($Propas_rec['PRORIN'], 6, 2) . "/" . substr($Propas_rec['PRORIN'], 4, 2) . "/" . substr($Propas_rec['PRORIN'], 0, 4));  /// formattare
        $dizionario->addField("PROANN", "Annotazioni", $i++, 'base', $Propas_rec['PROANN']);
        $dizionario->addField("PRODTP", "Tipo Passo", $i++, 'base', $Propas_rec['PRODTP']);
        $dizionario->addField("TIPODOCUMENTO", "Tipo Documento", $i++, 'base', $TipologiaDoc['DESDOCREG']);
        $dizionario->addField("PRODOCINIVAL", "Data inizio validita documento rilasciato", $i++, 'base', substr($Propas_rec['PRODOCINIVAL'], 6, 2) . "/" . substr($Propas_rec['PRODOCINIVAL'], 4, 2) . "/" . substr($Propas_rec['PRODOCINIVAL'], 0, 4));
        $dizionario->addField("PRODOCFINVAL", "Data fine validita documento rilasciato", $i++, 'base', substr($Propas_rec['PRODOCFINVAL'], 6, 2) . "/" . substr($Propas_rec['PRODOCFINVAL'], 4, 2) . "/" . substr($Propas_rec['PRODOCFINVAL'], 0, 4));
        $dizionario->addField("PRODOCPROG", "Progressivo documento rilasciato", $i++, 'base', $Propas_rec['PRODOCPROG']);
        $dizionario->addField("PRODOCANNO", "Anno documento rilasciato", $i++, 'base', $Propas_rec['PRODOCANNO']);
        $dizionario->addField("PROPPASS", "Password Articolo", $i++, 'base', $Propas_rec['PROPPASS']);
        $dizionario->addField("RESP_PASSO", "Denominazione Responsabile Passo", $i++, 'base', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
        $dizionario->addField("PROUTEADD", "Utente Creazione passo", $i++, 'base', $Propas_rec['PROUTEADD']);
        $dizionario->addField("PA_COMNOM", "Denominazione Destinatario Comunicazione Partenza", $i++, 'base', $PracomP_rec['COMNOM']);
        $dizionario->addField("PA_COMPRT", "Protocollo Comunicazione Partenza", $i++, 'base', substr($PracomP_rec['COMPRT'], 4));
        $dizionario->addField("PA_COMDPR", "Data Protocollo Comunicazione Partenza", $i++, 'base', date("d/m/Y", strtotime($PracomP_rec['COMDPR'])));
        $dizionario->addField("PA_COMNOT", "Note Comunicazione Partenza", $i++, 'base', $PracomP_rec['COMNOT']);
        $dizionario->addField("PA_COMMLD", "Email Comunicazione Partenza", $i++, 'base', $PracomP_rec['COMMLD']);
        $dizionario->addField("AR_COMPRT", "Protocollo Comunicazione Arrivo", $i++, 'base', substr($PracomA_rec['COMPRT'], 4));
        $dizionario->addField("AR_COMDPR", "Data Protocollo Comunicazione Arrivo", $i++, 'base', date("d/m/Y", strtotime($PracomA_rec['COMDPR'])));
        $dizionario->addField("AR_COMNOM", "Denominazione Destinatario Comunicazione Arrivo", $i++, 'base', $PracomA_rec['COMNOM']);
        $dizionario->addField("AR_COMNOT", "Note Comunicazione Arrivo", $i++, 'base', $PracomA_rec['COMNOT']);

        $this->variabiliDatiPassi = $dizionario;
        return $this->variabiliDatiPassi;
    }

    public function getVariabiliCampiAggiuntiviBase() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
        if (!$Proges_rec) {
            return null;
        }

        $variabiliCampiAggiuntiviBase = new itaDictionary();

        $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL,
                PRODAG.DAGTIC AS DAGTIC
            FROM
                PRODAG PRODAG
            WHERE DAGPAK = '" . $this->codicePratica . "' AND DAGNUM = '" . $this->codicePratica . "' ORDER BY DAGSEQ, DAGSFL";


        $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;
        if ($Prodag_tab) {
            foreach ($Prodag_tab as $Prodag_rec) {
                $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGKEY'];
                if($Prodag_rec['DAGTIC'] == 'Data'){
                    $data = substr($Prodag_rec['DAGVAL'], 6,2). "/" .substr($Prodag_rec['DAGVAL'], 4,2). "/" .substr($Prodag_rec['DAGVAL'], 0,4);
                    $Prodag_rec['DAGVAL'] = $data;
                }
                $variabiliCampiAggiuntiviBase->addField($Prodag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL']);
            }
            $this->variabiliCampiAggiuntiviBase = $variabiliCampiAggiuntiviBase;
        } else {
            $this->variabiliCampiAggiuntiviBase = null;
        }
        return $this->variabiliCampiAggiuntiviBase;
    }

    public function getVariabiliCampiAggiuntiviPassi() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
        if (!$Proges_rec) {
            return null;
        }

        $Propas_rec = $praLib->GetPropas($this->chiavePasso);
        if (!$Propas_rec) {
            return null;
        }

        $variabiliCampiAggiuntivi = new itaDictionary();
        $currSeq = $Propas_rec['PROSEQ'];
        $sql = "
            SELECT * FROM(
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PROPAS.PROSEQ AS PROSEQ,
                PRODAG.DAGVAL AS DAGVAL,
                PRODAG.DAGDEF AS DAGDEF,
                PRODAG.DAGDIZ AS DAGDIZ,
                PRODAG.DAGTIC AS DAGTIC
            FROM
                PROPAS PROPAS
            LEFT OUTER JOIN
                PRODAG PRODAG
            ON
                PROPAS.PROPAK=PRODAG.DAGPAK
            WHERE DAGKEY IS NOT NULL AND PROPAS.PRONUM='" . $this->codicePratica . "' AND PROSEQ <= $currSeq AND PROPAS.PRODOW <> 1
                
                 UNION
                 
            SELECT 
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL,
                PRODAG.DAGDEF AS DAGDEF,
                PRODAG.DAGDIZ AS DAGDIZ,
                PRODAG.DAGTIC AS DAGTIC,
                '' AS PROSEQ
            FROM
             PRODAG
            WHERE 
             DAGKEY IS NOT NULL AND DAGNUM='$this->codicePratica' AND DAGPAK = '$this->codicePratica'
                 ) AS T
                 ORDER BY PROSEQ
            ";


        //WHERE DAGTIP = ''  AND PROPAS.PRONUM='" . $this->codicePratica . "' AND PROSEQ <= $currSeq ORDER BY PROSEQ";        

        $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;
        if ($Prodag_tab) {
            foreach ($Prodag_tab as $Prodag_rec) {
                $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGKEY'];
                if($Prodag_rec['DAGTIC'] == 'Data'){
                     $data = substr($Prodag_rec['DAGVAL'], 6,2). "/" .substr($Prodag_rec['DAGVAL'], 4,2). "/" .substr($Prodag_rec['DAGVAL'], 0,4);
                    $Prodag_rec['DAGVAL'] = $data;
                }
                $variabiliCampiAggiuntivi->addField($Prodag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL']);
            }

            $compareVariabiliCampiAggiuntivi = array();
            $praLibElaborazioneDati = new praLibElaborazioneDati();

            while ($variabiliCampiAggiuntivi->getAllDataPlain() != $compareVariabiliCampiAggiuntivi) {
                $compareVariabiliCampiAggiuntivi = $variabiliCampiAggiuntivi->getAllDataPlain();

                $tmpDictionary = new itaDictionary();
                $tmpDictionary->addField('PRAAGGIUNTIVI', 'Variabili PRAAGGIUNTIVI', 1, 'itaDictionary', $variabiliCampiAggiuntivi);
                $plainTmpDictionary = $tmpDictionary->getAllDataPlain('', '.');

                foreach ($Prodag_tab as $Prodag_rec) {
                    if (!$Prodag_rec['DAGVAL'] && $Prodag_rec['DAGDEF']) {
                        $dagval = $praLibElaborazioneDati->elaboraValoreProdag($Prodag_rec, $plainTmpDictionary);
                        if ($dagval) {
                            $variabiliCampiAggiuntivi->addField($Prodag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $dagval);
                        }
                    }
                }
            }

            $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntivi = null;
        }
        return $this->variabiliCampiAggiuntivi;
    }

    public function getVariabiliTipiAggiuntiviPratica() {
        $variabiliTipiDato = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        if ($this->chiavePasso) {
            $Proges_rec = $praLib->GetProges($this->codicePratica);
            if (!$Proges_rec) {
                return false;
            }

            $Propas_rec = $praLib->GetPropas($this->chiavePasso);
            if (!$Propas_rec) {
                return false;
            }
            $currSeq = $Propas_rec['PROSEQ'];
            $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,            
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PRODAG.DAGVAL AS DAGVAL                
            FROM
                PROPAS PROPAS
            LEFT OUTER JOIN
                PRODAG PRODAG
            ON
                PROPAS.PROPAK=PRODAG.DAGPAK
            WHERE
                PRODAG.DAGTIP<>'' AND PRODAG.DAGTIP<>'Text' AND PROPAS.PRONUM='" . $this->codicePratica . "' AND PROPAS.PROSEQ < '" . $currSeq . "'
            ORDER BY
                PROSEQ";

            $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        } else {
            $j = 0;
            $Prodag_tab[$j]['DAGTIP'] = "Sportello_Aggregato";
            $Prodag_tab[$j]['DAGDES'] = "Sportello Aggregato";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "DenominazioneImpresa";
            $Prodag_tab[$j]['DAGDES'] = "Denominazione Impresa";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Indir_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "Indirizzo Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Civico_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "N. Civico Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Cap_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "Cap Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Prov_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "Provincia Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Codfis_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "Codice Fiscale Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Email_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "E-mail Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Comune_InsProduttivo";
            $Prodag_tab[$j]['DAGDES'] = "Comune Insediamento Produttivo";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Foglio_catasto";
            $Prodag_tab[$j]['DAGDES'] = "N. Foglio Catasto";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Sub_catasto";
            $Prodag_tab[$j]['DAGDES'] = "N. Sub Catasto";

            $j += 1;
            $Prodag_tab[$j]['DAGTIP'] = "Particella_catasto";
            $Prodag_tab[$j]['DAGDES'] = "N. Particella Catasto";
        }

        if ($Prodag_tab) {
            $i = 0;
            foreach ($Prodag_tab as $Prodag_rec) {
                $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGTIP'];
                $variabiliTipiDato->addField($Prodag_rec['DAGTIP'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL'], true);
            }
            $ComuneInsProdutivo = $this->GetComuneInsProduttivo($Proges_rec, $praLib);
            $variabiliTipiDato->addField("Comune_InsProduttivo", "Comune Impresa", $i++, 'base', $ComuneInsProdutivo, true);
            $this->variabiliTipiAggiuntivi = $variabiliTipiDato;
        } else {
            $this->variabiliTipiAggiuntivi = null;
        }
        return $this->variabiliTipiAggiuntivi;
    }

    public function getAllVariabiliTipiAggiuntiviPratica() {
        $variabiliTipiDato = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        $Proges_rec = $praLib->GetProges($this->codicePratica);
        if (!$Proges_rec) {
            return false;
        }

        $sql = "
            SELECT *
            FROM
                PRODAG
            WHERE
                PRODAG.DAGTIP<>'' AND PRODAG.DAGTIP<>'Text' AND PRODAG.DAGNUM='" . $this->codicePratica . "'
            ";

        $Prodag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        //if ($Prodag_tab) {
        $i = 0;
        foreach ($Prodag_tab as $Prodag_rec) {
            $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGTIP'];
            $variabiliTipiDato->addField($Prodag_rec['DAGTIP'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL'], true);
        }
        $ComuneInsProdutivo = $this->GetComuneInsProduttivo($Proges_rec, $praLib);
        $DatiImpresa = $praLib->DatiImpresa($Proges_rec['GESNUM']);
        $variabiliTipiDato->addField("Comune_InsProduttivo", "Comune Impresa", $i++, 'base', $ComuneInsProdutivo, true);
        $variabiliTipiDato->addField("DenominazioneImpresa", "Denominazione Impresa", $i++, 'base', $DatiImpresa['IMPRESA'], true);
        $this->variabiliTipiAggiuntivi = $variabiliTipiDato;
        //} else {
        //    $this->variabiliTipiAggiuntivi = null;
        //}
        return $this->variabiliTipiAggiuntivi;
    }

    public function GetComuneInsProduttivo($Proges_rec, $praLib) {
        if ($Proges_rec["GESSPA"] != 0) {
            $Anaspa_rec = $praLib->GetAnaspa($Proges_rec["GESSPA"]);
            return $Anaspa_rec['SPACOM'];
        } else {
            $Anatsp_rec = $praLib->GetAnatsp($Proges_rec["GESTSP"]);
            return $Anatsp_rec['TSPCOM'];
        }
    }

    public function getVariabiliUpload() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili Sportello on Line
        //
        $dizionario->addField('CODICEDESTINAZIONE', 'Codice Destinazione', $i++, 'base');
        $dizionario->addField('TIPOALLEGATO', 'Tipo Allegato', $i++, 'base');
        $dizionario->addField('CODICECLASSIFICAZIONE', 'Codice Classificazione', $i++, 'base');
        $dizionario->addField('NUMERORICHIESTA', 'Numero Richiesta', $i++, 'base');
        $dizionario->addField('NUMERORICHIESTAPADRE', 'Numero Richiesta Padre', $i++, 'base');
        $dizionario->addField('NOMEFILEORIG', 'Nome File Originale', $i++, 'base');
        $dizionario->addField('NUMERORICHIESTAACCORPATA', 'Numero RIchiesta Principale Accorpata', $i++, 'base');
        $this->variabiliUpload = $dizionario;
        return $this->variabiliUpload;
    }

    public function getVariabiliBaseProcedimento() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili Sportello di destinazione
        //
        $dizionario->addField('SPT_DES', 'Descrizione Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_DEN', 'Denominazione Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_COM', 'Comune Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_IND', 'Indirizo Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CIV', 'Civico Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_PRV', 'Provincia Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CAP', 'Cap Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CCA', 'Codice Catastale Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CIS', 'Codice Istat Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_TES', 'Tesoreria Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_DEST', 'Destinazione Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_IBAN', 'Iban Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_BANCA', 'Banca Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_SWIFT', 'Codice Swift Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CC', 'Conto Correntte Bancario Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CCP', 'Conto Correntte Postale Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CAUSALECC', 'Causale CC Sportello di destinazione', $i++, 'base');
        $dizionario->addField('SPT_CAUSALECCP', 'Causale CCP Sportello di destinazione', $i++, 'base');

        //
        // Variabili Sportello on Line
        //
        $dizionario->addField('TSPDES', 'Descrizione Breve Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPDEN', 'Denominazione Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCOM', 'Comune Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPIND', 'Indirizo Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPNCI', 'Civico Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPPRO', 'Provincia Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCAP', 'CAP Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCCA', 'Codice Catastale Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPIST', 'Istat Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPPEC', 'Indirizzo Mail Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPWEB', 'Sito WEB Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPMOD', 'Indirizzo WEB Modulistica Sportello On-Line', $i++, 'base');

        $dizionario->addField('TSPRES_NOM', 'Nominativo Responsabile Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPRES_ORARIO', 'Orario di ricevimento Responsabile Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPRES_NOMEML', 'Mail Responsabile Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPRES_NOMTEL', 'Telefono Responsabile Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPRES_NOMFAX', 'Fax Responsabile Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPTES', 'Tesoreria Sportello On-line', $i++, 'base');
        $dizionario->addField('TSPDEST', 'Destinazione Sportello On-line', $i++, 'base');
        $dizionario->addField('TSPIBAN', 'Iban Sportello On-line', $i++, 'base');
        $dizionario->addField('TSPBANCA', 'Banca Sportello On-line', $i++, 'base');
        $dizionario->addField('TSPSWIFT', 'Codice Swift Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCC', 'Conto Correntte Bancario Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCCP', 'Conto Correntte Postale Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCAUSALECC', 'Causale CC Sportello On-Line', $i++, 'base');
        $dizionario->addField('TSPCAUSALECCP', 'Causale CCP Sportello On-Line', $i++, 'base');


        //
        // Variabili Sportello on Line Aggregato
        //
        $dizionario->addField('SPADES', 'Descrizione Breve Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACOM', 'Comune Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPAIND', 'Indirizo Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPANCI', 'Civico Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPAPRO', 'Provincia Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACAP', 'CAP Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACCA', 'Codice Catastale Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPAIST', 'Istat Sportello Aggregato', $i++, 'base');

        $dizionario->addField('SPARES_NOM', 'Nominativo Responsabile Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPARES_ORARIO', 'Orario di ricevimento Responsabile Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPARES_NOMEML', 'Mail Responsabile Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPARES_NOMTEL', 'Telefono Responsabile Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPARES_NOMFAX', 'Fax Responsabile Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPATES', 'Tesoreria Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPADEST', 'Destinazione Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPAIBAN', 'Iban Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPABANCA', 'Banca Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPASWIFT', 'Codice Swift Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACC', 'Conto Correntte Bancario Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACCP', 'Conto Correntte Postale Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACAUSALECC', 'Causale CC Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPACAUSALECCP', 'Causale CCP Sportello Aggregato', $i++, 'base');
        $dizionario->addField('SPADISABILITAPAGOPA', 'Flag Disabailita Passi Paso PA', $i++, 'base');

        //
        // Dati Procedimento
        //
        $dizionario->addField('SERIEPROGRESSIVO', 'Numero Pratica', $i++, 'base');
        $dizionario->addField('SERIEANNO', 'Anno Pratica', $i++, 'base');
        $dizionario->addField('DECOD_SERIE', 'Serie Pratica', $i++, 'base');
        $dizionario->addField('SERIE_FORMATTED', 'Numero Pratica SERIE/AAAA/N', $i++, 'base');
        $dizionario->addField('GESNUM', 'Identificativo Pratica', $i++, 'base');
        $dizionario->addField('GESNUM_FORMATTED', 'Identificativo Pratica N/AAAA', $i++, 'base');
        $dizionario->addField('GESCODPROC', 'Codice Procedura', $i++, 'base');
        $dizionario->addField('GESDRE', 'Data Registrazione', $i++, 'base');
        $dizionario->addField('GESDRI', 'Data Ricezione On-Line', $i++, 'base');
        $dizionario->addField('GESNOT', 'Note del Fascicolo', $i++, 'base');
        $dizionario->addField('RICNUM', 'Numero Richiesta On-line', $i++, 'base');
        $dizionario->addField('RICNUM_FORMATTED', 'Numero Richiesta On-line N/AAAA', $i++, 'base');
        $dizionario->addField('RICRPA', 'Numero Richiesta On-line Padre', $i++, 'base');
        $dizionario->addField('RICRPA_FORMATTED', 'Numero Richiesta On-line Padre N/AAAA', $i++, 'base');
        $dizionario->addField('RICRPA_PROCEDIMENTO', 'Codice Procedimento Richiesta On-line Padre', $i++, 'base');
        $dizionario->addField('RICRUN', 'Numero Richiesta On-line Principale (accorpata)', $i++, 'base');
        $dizionario->addField('RICRUN_FORMATTED', 'Numero Richiesta On-line Principale (accorpata) N/AAAA', $i++, 'base');
        $dizionario->addField('RICEVE', 'Codice evento procedimento', $i++, 'base');


        $dizionario->addField('PRANUM', 'Codice Procedimento', $i++, 'base');
        $dizionario->addField('PRADES', 'Descrizione Procedimento', $i++, 'base');
        $dizionario->addField('TIPDES', 'Tipologia Procedimento', $i++, 'base');
        $dizionario->addField('SETDES', 'Settore', $i++, 'base');
        $dizionario->addField('ATTDES', 'Attività', $i++, 'base');
        $dizionario->addField('GESOGG', 'Oggetto Fascicolo', $i++, 'base');
        if ($this->frontOfficeFlag == false) {
            $dizionario->addField('GESNPR_NM', 'Numero protocollo Pratica', $i++, 'base');
            $dizionario->addField('DATA_PRT', 'Data protocollo Pratica', $i++, 'base');
            $dizionario->addField('GESNPR_AA', 'Anno protocollo Pratica', $i++, 'base');
        }

        $dizionario->addField('PRARES_NOM', 'Nominativo Responsabile Procedimento', $i++, 'base');
        $dizionario->addField('PRARES_ORARIO', 'Orario di ricevimento Responsabile Procedimento', $i++, 'base');
        $dizionario->addField('PRARES_NOMEML', 'Mail Responsabile Procedimento', $i++, 'base');
        $dizionario->addField('PRARES_NOMTEL', 'Telefono Responsabile Procedimento', $i++, 'base');
        $dizionario->addField('PRARES_NOMFAX', 'Fax Responsabile Procedimento', $i++, 'base');

        //
        //Tariffe
        //
        $dizionario->addField('TOTALE_TARIFFE', 'Tariffa Totale del Procedimento', $i++, 'base');

        //
        // Dati Richiedente
        //
        $dizionario->addField('RICSOG', 'Demoninazione Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICCOG', 'Cognome Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICNOM', 'Nome Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICVIA', 'Indirizzo Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICCAP', 'Cap Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICCOM', 'Comune Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICPRV', 'Provincia Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICNAZ', 'Nazione Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICNAS', 'Data nascita Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICFIS', 'Codice Fiscale/P.Iva Intestatario Richiesta', $i++, 'base');
        $dizionario->addField('RICEMA', 'Email Intestatario Richiesta', $i++, 'base');

        /*
         * Dati Richiesta
         */
        $dizionario->addField('RICDRE', 'Data Compilazione', $i++, 'base');
        $dizionario->addField('RICORE', 'Orario Apertura Richiesta', $i++, 'base');
        $dizionario->addField('RICDAT', 'Data Inoltro Richiesta', $i++, 'base');
        $dizionario->addField('RICTIM', 'Ora Inoltro Richiesta', $i++, 'base');
        $dizionario->addField('ANNORICHIESTA', 'Anno Apertura Richiesta', $i++, 'base');
        $dizionario->addField('RICNPR_NUM', 'Numero Protocollo Richiesta', $i++, 'base'); /////
        $dizionario->addField('RICNPR_ANNONUM', 'Anno/Numero Protocollo Richiesta', $i++, 'base'); /////
        $dizionario->addField('RICDPR', 'Data Protocollo Richiesta', $i++, 'base'); /////


        $this->variabiliBase = $dizionario;
        return $this->variabiliBase;
    }

    public function getVariabiliPassoPratica() {

        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $Proges_rec = $praLib->GetProges($this->codicePratica);
//        if (!$Proges_rec) {
//            return null;
//        }
        if ($Proges_rec) {
            $Propas_rec = $praLib->GetPropas($this->chiavePasso);
            $PracomP_rec = $praLib->GetPracomP($this->chiavePasso);
            $PracomA_rec = $praLib->GetPracomA($this->chiavePasso, $PracomP_rec['ROWID']);
            //
            $TipologiaDoc = $praLib->GetAnadoctipreg($Propas_rec['PRODOCTIPREG']);
            $i = 1;
            $dizionario = $this->getVariabiliPasso();
            $dizionario->addFieldData('PRORIN', $Propas_rec['PRORIN']);
            $dizionario->addFieldData('PRORIN_FORMATTED', substr($Propas_rec['PRORIN'], 4) . "/" . substr($Propas_rec['PRORIN'], 0, 4));
            //
            if ($Propas_rec['PRORIN']) {
                $proric_rec = $praLib->GetProric($Propas_rec['PRORIN']);
                $Anapra_rec = $praLib->GetAnapra($proric_rec['RICPRO']);
                $dizionario->addFieldData('DESC_PROC_INTEGR', $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4']);
            }

            $dizionario->addFieldData('PRODTP', $Propas_rec['PRODTP']);
            $dizionario->addFieldData('PRODPA', $Propas_rec['PRODPA']);
            $dizionario->addFieldData('PROFIN', substr($Propas_rec['PROFIN'], 6, 2) . "/" . substr($Propas_rec['PROFIN'], 4, 2) . "/" . substr($Propas_rec['PROFIN'], 0, 4));
            $dizionario->addFieldData('PROINI', substr($Propas_rec['PROINI'], 6, 2) . "/" . substr($Propas_rec['PROINI'], 4, 2) . "/" . substr($Propas_rec['PROINI'], 0, 4));
            $dizionario->addFieldData('PROANN', $Propas_rec['PROANN']);
            $dizionario->addFieldData('TIPODOCUMENTO', $TipologiaDoc['DESDOCREG']);
            $dizionario->addFieldData('PROPPASS', $Propas_rec['PROPPASS']);
            $daDataDoc = substr($Propas_rec['PRODOCINIVAL'], 6, 2) . "/" . substr($Propas_rec['PRODOCINIVAL'], 4, 2) . "/" . substr($Propas_rec['PRODOCINIVAL'], 0, 4);
            $aDataDoc = substr($Propas_rec['PRODOCFINVAL'], 6, 2) . "/" . substr($Propas_rec['PRODOCFINVAL'], 4, 2) . "/" . substr($Propas_rec['PRODOCFINVAL'], 0, 4);
            $dizionario->addFieldData('PRODOCINIVAL', $daDataDoc);
            $dizionario->addFieldData('PRODOCFINVAL', $aDataDoc);
            $dizionario->addFieldData('PRODOCPROG', $Propas_rec['PRODOCPROG']);
            $dizionario->addFieldData('PRODOCANNO', $Propas_rec['PRODOCANNO']);

            $Ananom_rec = $praLib->GetAnanom($Propas_rec['PRORPA']);
            $dizionario->addFieldData('RESP_PASSO', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);

            //
            // Comunicazione in partenza
            //
            $dizionario->addFieldData('PA_COMNOM', $PracomP_rec['COMNOM']);
            $dizionario->addFieldData('PA_COMIND', $PracomP_rec['COMIND']);
            $dizionario->addFieldData('PA_COMMLD', $PracomP_rec['COMMLD']);
            $dizionario->addFieldData('PA_COMCIT', $PracomP_rec['COMCIT']);
            $dizionario->addFieldData('PA_COMCAP', $PracomP_rec['COMCAP']);
            $dizionario->addFieldData('PA_COMPRO', $PracomP_rec['COMPRO']);
            //$dizionario->addFieldData('PA_COMPRT', $PracomP_rec['COMPRT']);
            $dizionario->addFieldData('PA_COMPRT', substr($PracomP_rec['COMPRT'], 4));
            if ($PracomP_rec['COMDPR']) {
                $dizionario->addFieldData('PA_COMDPR', date("d/m/Y", strtotime($PracomP_rec['COMDPR'])));
            }
            $dizionario->addFieldData('PA_COMNOT', $PracomP_rec['COMNOT']);

            //
            // Comunicazione in Arrivo Collegata
            //
            $dizionario->addFieldData('AR_COMNOM', $PracomA_rec['COMNOM']);
            //$dizionario->addFieldData('AR_COMPRT', $PracomA_rec['COMPRT']);
            $dizionario->addFieldData('AR_COMPRT', substr($PracomA_rec['COMPRT'], 4));
            $dizionario->addFieldData('AR_COMDPR', $PracomA_rec['COMDPR']);
            if ($PracomA_rec['COMDPR']) {
                $dizionario->addFieldData('AR_COMDPR', date("d/m/Y", strtotime($PracomA_rec['COMDPR'])));
            }
            $dizionario->addFieldData('AR_COMNOT', $PracomA_rec['COMNOT']);

            include_once ITA_BASE_PATH . '/apps/CodeGateway/cgwLib.class.php';
            $cgwLibClient = new cgwLibClient();
            $qrcode = $cgwLibClient->generateGatewayURI(cgwLibContexts::CTX_PRATICHE_ALLEGATI_TITOLO_ABILITATIVO, array('id' => $this->chiavePasso));
            $dizionario->addFieldData('ALLEGATI_TITOLO_ABILITATIVO', $qrcode);
        } else {
            $dizionario = $this->getVariabiliPasso();
        }
        $this->variabiliPasso = $dizionario;
        return $this->variabiliPasso;
    }

    public function getVariabiliTipoPasso() {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $Propas_rec = $praLib->GetPropas($this->chiavePasso);
        $where = "AND PROSEQ < '" . $Propas_rec['PROSEQ'] . "' AND PROOPE = '' AND PROPUB = 0 "; //PROOPE distingue i passi di gestione
        $PropasAnt_tab = $praLib->GetPropassi($Propas_rec['PRONUM'], $where, 'codice', true);
        if ($PropasAnt_tab) {
            $this->variabiliPassi = new itaDictionary();
            $dizionario = $this->getVariabiliPasso();
            $duplucati = array();
            $k = 0;
            foreach ($PropasAnt_tab as $key => $PropasAnt_rec) {
                if (in_array($PropasAnt_rec['PROCLT'], $duplucati)) {
                    continue;
                }
                $duplucati[$k] = $PropasAnt_rec['PROCLT'];
                $k++;
                $tipopasso = $praLib->GetPraclt($PropasAnt_rec['PROCLT']);
                $this->variabiliPassi->addField("PASSOTIP_" . $PropasAnt_rec['PROCLT'], "Tipo Passo " . $tipopasso['CLTDES'] . ' Seq. ' . $PropasAnt_rec['PROSEQ'], 20, 'itaDictionary', $this->getDatiVariabiliPassi($PropasAnt_rec));
            }
        } else {
            $this->variabiliPassi = null;
        }
        return $this->variabiliPassi;
    }

    public function getVariabiliPasso() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili dei Passo
        //

        $dizionario->addField('PRORIN', 'Richiesta on-line integrazione', $i++, 'base');
        $dizionario->addField('PRORIN_FORMATTED', 'Richiesta on-line integrazione formatted', $i++, 'base');
        $dizionario->addField('DESC_PROC_INTEGR', 'Descrizione procedimento di integrazione', $i++, 'base');
        $dizionario->addField('PRODTP', 'Tipo di Passo', $i++, 'base');
        $dizionario->addField('PRODPA', 'Descrizione Passo', $i++, 'base');
        $dizionario->addField('PROANN', 'Annotazione Passo', $i++, 'base');
        $dizionario->addField('PROINI', 'Data Apertura Passo', $i++, 'base');
        $dizionario->addField('PROFIN', 'Data Chiusura Passo', $i++, 'base');
        $dizionario->addField('TIPODOCUMENTO', 'Tipologia del documento rilasciato', $i++, 'base');
        $dizionario->addField('PROPPASS', 'Password Articolo', $i++, 'base');
        $dizionario->addField('RESP_PASSO', 'Denominazione Responsabile Passo', $i++, 'base');
        $dizionario->addField('PRODOCINIVAL', 'Data inizio validita documento rilasciato', $i++, 'base');
        $dizionario->addField('PRODOCFINVAL', 'Data fine validita documento rilasciato', $i++, 'base');
        $dizionario->addField('PRODOCPROG', 'Progressivo documento rilasciato', $i++, 'base');
        $dizionario->addField('PRODOCANNO', 'Anno documento rilasciato', $i++, 'base');

        $dizionario->addField('PA_COMNOM', 'Denominazione Destinatario Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMIND', 'Indirizzo Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMCIT', 'Città Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMCAP', 'CAP Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMPRO', 'Provincia Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMPRT', 'Protocollo Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMDPR', 'Data Protocollo Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMNOT', 'Note Comunicazione Partenza', $i++, 'base');
        $dizionario->addField('PA_COMMLD', 'Email Comunicazione Partenza', $i++, 'base');

        $dizionario->addField('AR_COMNOM', 'Denominazione Destinatario Comunicazione Arrivo', $i++, 'base');
        $dizionario->addField('AR_COMPRT', 'Protocollo Mittente Comunicazione Arrivo', $i++, 'base');
        $dizionario->addField('AR_COMDPR', 'Data Protocollo Comunicazione Arrivo', $i++, 'base');
        $dizionario->addField('AR_COMNOT', 'Note Comunicazione Arrivo', $i++, 'base');

        //
        //Tariffe
        //
        $dizionario->addField('TARIFF', 'Tariffa Passo', $i++, 'base');

        $dizionario->addField('ALLEGATI_TITOLO_ABILITATIVO', 'URI per download allegati titolo abilitativo', $i++, 'base', '', false, '"type":"qr"');

        $this->variabiliPasso = $dizionario;
        return $this->variabiliPasso;
    }

    public function getVariabiliAllegati() {
        $i = 1;
        $dizionario = new itaDictionary();

        //
        // Variabili Allegati del Passo
        //
        $dizionario->addField('NOME', 'Nome', $i++, 'base');
        $dizionario->addField('NOTE', 'Descrizione', $i++, 'base');
        $dizionario->addField('NOTEALLEGATO', 'Note Allegato', $i++, 'base');
        $dizionario->addField('CLASSIFICAZIONE', 'Classificazione', $i++, 'base');
        $dizionario->addField('HASH', 'Hash', $i++, 'base');
        $dizionario->addField('NUMPROT', 'N. Protocollo per Marcatura', $i++, 'base');
        $dizionario->addField('ANNOPROT', 'Anno Protocollo per Marcatura', $i++, 'base');
        $dizionario->addField('DATAPROT', 'Data Protocollo per Marcatura', $i++, 'base');
        $dizionario->addField('ORAPROT', 'Ora Protocollo per Marcatura', $i++, 'base');
        $dizionario->addField('FIRMATARIO', 'Firmatario del File', $i++, 'base');
        return $dizionario;
    }

    public function getVariabiliInformativa() {
        $i = 1;
        $dizionario = new itaDictionary();

        $dizionario->addField('SERVIZIO', 'Servizio', $i++, 'base');
        $dizionario->addField('ELENCO', 'Elenco richieste in corso', $i++, 'base');
        $dizionario->addField('INQUADRAMENTO', 'Inquadramento', $i++, 'base');
        $dizionario->addField('NORMATIVA', 'Normativa', $i++, 'base');
        $dizionario->addField('REQUISITI', 'Requisiti', $i++, 'base');
        $dizionario->addField('ADEMPIMENTI', 'Adempimenti', $i++, 'base');
        $dizionario->addField('TERMINI', 'Termini', $i++, 'base');
        $dizionario->addField('ONERI', 'Oneri', $i++, 'base');
        $dizionario->addField('RESPONSABILE', 'Responsabile', $i++, 'base');
        $dizionario->addField('UO_RESP_ISTRUTTORIA', 'Unità organizzativa responsabile dell\'istruttoria', $i++, 'base');
        $dizionario->addField('DISCIPLINE', 'Discipline sanzionatorie', $i++, 'base');
        $dizionario->addField('MODULISTICA', 'Modulistica', $i++, 'base');
        $dizionario->addField('ALLEGATI', 'Allegati', $i++, 'base');
        $dizionario->addField('PROC_CORRELATI', 'Altri procedimenti correlati', $i++, 'base');

        return $dizionario;
    }

    public function getDatiVariabiliAllegati() {
        $dizionario = $this->getVariabiliAllegati();
        $praLib = new praLib();
        $Pasdoc_rec = $praLib->GetPasdoc($this->rowidPasdoc, "ROWID");
        $praLibAllegati = new praLibAllegati();
        $arrDatiProt = $praLibAllegati->GetDatiMarcaturaAlle($this->rowidPasdoc);
        //
        $dizionario->addFieldData('NOME', $Pasdoc_rec['PASNAME']);
        $dizionario->addFieldData('NOTE', $Pasdoc_rec['PASNOT']);
        $dizionario->addFieldData('NOTEALLEGATO', $Pasdoc_rec['PASNOTE']);
        $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
        $dizionario->addFieldData('CLASSIFICAZIONE', $Anacla_rec['CLADES']);
        $dizionario->addFieldData('HASH', $Pasdoc_rec['PASSHA2']);
        $dizionario->addFieldData('NUMPROT', $arrDatiProt['NUMPROT']);
        $dizionario->addFieldData('DATAPROT', $arrDatiProt['DATAPROT']);
        $dizionario->addFieldData('ANNOPROT', $arrDatiProt['ANNOPROT']);
        $dizionario->addFieldData('ORAPROT', $arrDatiProt['ORAPROT']);
        $dizionario->addFieldData('FIRMATARIO', '@{$PRAALLEGATI.FIRMATARIO}@');
        $this->variabiliAllegati = $dizionario;
        return $this->variabiliAllegati;
    }

    public function getVariabiliSociFascicolo() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili Allegati del Passo
        //

        $dizionario->addField('DENOM', 'Denominazione', $i++, 'base');
        $dizionario->addField('DESNASCIT', 'Comune di Nascita', $i++, 'base');
        $dizionario->addField('DESNASPROV', 'Provincia di Nascita', $i++, 'base');
        $dizionario->addField('DESNASDAT', 'Data di Nascita', $i++, 'base');
        $dizionario->addField('DESCIT', 'Comune di Residenza', $i++, 'base');
        $dizionario->addField('DESPRO', 'Provincia di Residenza', $i++, 'base');
        $dizionario->addField('INDRES', 'Indirizzo di Residenza', $i++, 'base');
        $dizionario->addField('DESFIS', 'Codice Fiscale', $i++, 'base');
        $this->variabiliSociFascicolo = $dizionario;
        return $this->variabiliSociFascicolo;
    }

    public function getVariabiliSoggettoFascicolo() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionario->addField('DENOM', 'Denominazione', $i++, 'base');
        $dizionario->addField('DESNASCIT', 'Comune di Nascita', $i++, 'base');
        $dizionario->addField('DESNASPROV', 'Provincia di Nascita', $i++, 'base');
        $dizionario->addField('DESNASDAT', 'Data di Nascita', $i++, 'base');
        $dizionario->addField('DESCIT', 'Comune di Residenza', $i++, 'base');
        $dizionario->addField('DESPRO', 'Provincia di Residenza', $i++, 'base');
        $dizionario->addField('INDRES', 'Indirizzo di Residenza', $i++, 'base');
        $dizionario->addField('DESFIS', 'Codice Fiscale', $i++, 'base');
        $this->variabiliSoggettoFascicolo = $dizionario;
        return $this->variabiliSoggettoFascicolo;
    }

    public function getVariabiliDestinatariFascicolo() {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("CODICE", "Codice", $i++, 'base');
        $dizionario->addField("NOME", "Denominazione", $i++, 'base');
        $dizionario->addField("FISCALE", "Codice Fiscale", $i++, 'base');
        $dizionario->addField("INDIRIZZO", "Indirizzo", $i++, 'base');
        $dizionario->addField("COMUNE", "Comune", $i++, 'base');
        $dizionario->addField("CAP", "Cap", $i++, 'base');
        $dizionario->addField("PROVINCIA", "Provincia", $i++, 'base');
        $dizionario->addField("MAIL", "E-Mail", $i++, 'base');
        $this->variabiliDestinatariPasso = $dizionario;
        return $this->variabiliDestinatariPasso;
    }

    public function getVariabiliDatiAggiuntiviPasso() {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("DAGKEY", "Codice", $i++, 'base');
        $dizionario->addField("DAGDES", "Descrizione", $i++, 'base');
        $dizionario->addField("DAGVAL", "Valore", $i++, 'base');
        $this->variabiliDatiAggiuntiviPasso = $dizionario;
        return $this->variabiliDatiAggiuntiviPasso;
    }

    public function getVariabiliOneriFascicolo() {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("IMPCOD", "Tipologia Onere", $i++, 'base');
        $dizionario->addField("IMPORTO", "Importo Onere", $i++, 'base');
        $dizionario->addField("PAGATO", "Importo Pagato", $i++, 'base');
        $dizionario->addField("DATASCAD", "Data Scadenza", $i++, 'base');
        $dizionario->addField("DATAREG", "Data Registrazione Onere", $i++, 'base');
        $this->variabiliOneriFascicolo = $dizionario;
        return $this->variabiliOneriFascicolo;
    }

    public function getVariabiliSoggettiDaRuolo() {
        $dizionario = new itaDictionary();
        $praLib = new praLib();
        $Anaruo_tab = $praLib->getGenericTab("SELECT * FROM ANARUO");
        foreach ($Anaruo_tab as $Anaruo_rec) {
            if ($Anaruo_rec['RUODIS'] == 0) {
                $dizionario->addField("SOGGETTO" . $Anaruo_rec['RUOCOD'], $Anaruo_rec['RUODES'], 7, 'itaDictionary', $this->getVariabiliSoggetto());
            }
        }
        $this->variabiliRuoliSogg = $dizionario;
        return $this->variabiliRuoliSogg;
    }

    public function getVariabiliPassiTipo() {
//        $praLib = new praLib();
//        $PRAM_DB = $praLib->getPRAMDB();
//        $Propas_rec = $praLib->GetPropas($this->chiavePasso);
//        $where = "AND PROSEQ < '" . $Propas_rec['PROSEQ'] . "' ";
//        $PropasAnt_tab = $praLib->GetPropassi($Propas_rec['PRONUM'], $where, 'codice', true);
        $dizionario = new itaDictionary();
        // foreach ($PropasAnt_tab as $PropasAnt_tab) {
        // $dizionario->addField("SOGGETTO" . $PropasAnt_tab['PROSEQ'], $PropasAnt_tab['PROSEQ'], 20, 'itaDictionary', $this->getVariabiliPasso());
        $dizionario->addField("PASSOTIP_N", 'Passo Tipologia. N', 20, 'itaDictionary', $this->getVariabiliPasso());
        // }
        $this->variabiliSeqPasso = $dizionario;
        return $this->variabiliSeqPasso;
    }

    public function getVariabiliSoggetto() {
        $dizionario = new itaDictionary();
        $i = 0;
        $dizionario->addField("DESNOM", "Denominazione", $i++, 'base');
        $dizionario->addField("DESCOGNOME", "Cognome", $i++, 'base');
        $dizionario->addField("DESNOME", "Nome", $i++, 'base');
        $dizionario->addField("DESRAGSOC", "Ragione Sociale", $i++, 'base');
        $dizionario->addField("DESFIS", "Codice Fiscale", $i++, 'base');
        $dizionario->addField("DESIND", "Indirizzo", $i++, 'base');
        $dizionario->addField("DESCIV", "Civico", $i++, 'base');
        $dizionario->addField("DESCIT", "Comune", $i++, 'base');
        $dizionario->addField("DESCAP", "Cap", $i++, 'base');
        $dizionario->addField("DESPRO", "Provincia", $i++, 'base');
        $dizionario->addField("DESEMA", "E-Mail", $i++, 'base');
        $dizionario->addField("DESPEC", "Pec", $i++, 'base');
        $variabiliRuoliSogg = $dizionario;
        return $variabiliRuoliSogg;
    }

    public function getVariabiliCampiAggiuntiviGenerico() {
        $variabiliCampiGenerico = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $sql = "SELECT * FROM PRAIDC";
        $Praidc_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        if ($Praidc_tab) {
            foreach ($Praidc_tab as $Praidc_rec) {
                $descrizioneCampo = $Praidc_rec['IDCDES'];
                $variabiliCampiGenerico->addField($Praidc_rec['IDCKEY'], $descrizioneCampo, $i++, 'base');
            }
            $this->variabiliCampiAggiuntiviGenerico = $variabiliCampiGenerico;
        } else {
            $this->variabiliCampiAggiuntiviGenerico = null;
        }
        return $this->variabiliCampiAggiuntiviGenerico;
    }

    public function getVariabiliCampiAggiuntiviProcedimentoBase() {
        $variabiliCampiAggiuntiviBase = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        //
        $sql = "
            SELECT
                ITEDAG.ITEKEY AS ITEKEY,
                ITEDAG.ITECOD AS ITECOD,
                ITEDAG.ITDDES AS ITDDES,
                ITEDAG.ITDTIP AS ITDTIP,
                ITEDAG.ITDKEY AS ITDKEY,
                ITEDAG.ITDALIAS AS ITDALIAS
            FROM
                ITEDAG
            WHERE
                ITEDAG.ITEKEY = '" . $this->codiceProcedimento . "' AND ITEDAG.ITECOD = '" . $this->codiceProcedimento . "'
            ORDER BY
                ITDSEQ";


        $Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;

        if ($Itedag_tab) {
            foreach ($Itedag_tab as $Itedag_rec) {
                $descrizioneCampo = $Itedag_rec['ITDDES'] ? $Itedag_rec['ITDDES'] : $Itedag_rec['ITDKEY'];
                $variabiliCampiAggiuntiviBase->addField($Itedag_rec['ITDKEY'], $descrizioneCampo, $i++, 'base');
            }
            $this->variabiliCampiAggiuntiviBase = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntiviBase = null;
        }
        return $this->variabiliCampiAggiuntiviBase;
    }

    public function getVariabiliCampiAggiuntiviProcedimento() {
        $variabiliCampiAggiuntivi = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        //
        $currSeq = 100000;
        if ($this->chiavePasso) {
            $Itepas_rec = $praLib->GetItepas($this->chiavePasso, 'itekey');
            if (!$Itepas_rec) {
                return false;
            }
            $currSeq = $Itepas_rec['ITESEQ'];
        }
        $sql = "
            SELECT
                ITEDAG.ITEKEY AS ITEKEY,
                ITEDAG.ITECOD AS ITECOD,
                ITEDAG.ITDDES AS ITDDES,
                ITEDAG.ITDTIP AS ITDTIP,
                ITEDAG.ITDKEY AS ITDKEY,
                ITEDAG.ITDALIAS AS ITDALIAS
            FROM
                ITEPAS ITEPAS
            LEFT OUTER JOIN
                ITEDAG ITEDAG
            ON
                ITEPAS.ITEKEY=ITEDAG.ITEKEY
            WHERE
                ITEDAG.ITEKEY IS NOT NULL AND ITEPAS.ITECOD='" . $this->codiceProcedimento . "' AND ITESEQ <= '" . $currSeq . "'
            ORDER BY
                ITEPAS.ITESEQ , ITDSEQ";


        //ITEPAS.ITECOD='" . $this->codiceProcedimento . "' AND ITESEQ <= '" . $currSeq . "'
        //ITDTIP = ''  AND ITEPAS.ITECOD='" . $this->codiceProcedimento . "' AND ITESEQ <= '" . $currSeq . "'
        //ITDTIP = ''  AND ITEPAS.ITECOD='" . $this->codiceProcedimento . "' AND ITESEQ < '" . $currSeq . "'
        $Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;

        if ($Itedag_tab) {
            foreach ($Itedag_tab as $Itedag_rec) {
                $descrizioneCampo = $Itedag_rec['ITDDES'] ? $Itedag_rec['ITDDES'] : $Itedag_rec['ITDKEY'];
                $variabiliCampiAggiuntivi->addField($Itedag_rec['ITDKEY'], $descrizioneCampo, $i++, 'base');
            }
            $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntivi = null;
        }
        return $this->variabiliCampiAggiuntivi;
    }

    public function getVariabiliCampiAggiuntiviProcedimentoPasso() {
        $variabiliCampiAggiuntivi = new itaDictionary();
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        if (!$this->chiavePasso) {
            return $variabiliCampiAggiuntivi;
        }

        $Itepas_rec = $praLib->GetItepas($this->chiavePasso, 'itekey');
        if (!$Itepas_rec) {
            return $variabiliCampiAggiuntivi;
        }
        
        $currSeq = $Itepas_rec['ITESEQ'];

        $sql = "SELECT
                    ITEDAG.ITEKEY AS ITEKEY,
                    ITEDAG.ITECOD AS ITECOD,
                    ITEDAG.ITDDES AS ITDDES,
                    ITEDAG.ITDTIP AS ITDTIP,
                    ITEDAG.ITDKEY AS ITDKEY,
                    ITEDAG.ITDALIAS AS ITDALIAS
                FROM
                    ITEPAS ITEPAS
                LEFT OUTER JOIN
                    ITEDAG ITEDAG
                ON
                    ITEPAS.ITEKEY=ITEDAG.ITEKEY
                WHERE
                    ITEDAG.ITEKEY IS NOT NULL AND ITEPAS.ITECOD = '" . $this->codiceProcedimento . "' AND ITESEQ = '" . $currSeq . "'
                ORDER BY
                    ITEPAS.ITESEQ , ITDSEQ";


        $Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;

        if ($Itedag_tab) {
            foreach ($Itedag_tab as $Itedag_rec) {
                $descrizioneCampo = $Itedag_rec['ITDDES'] ? $Itedag_rec['ITDDES'] : $Itedag_rec['ITDKEY'];
                $variabiliCampiAggiuntivi->addField($Itedag_rec['ITDKEY'], $descrizioneCampo, $i++, 'base');
            }
            $this->variabiliCampiAggiuntiviPasso = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntiviPasso = null;
        }

        return $this->variabiliCampiAggiuntiviPasso;
    }

    public function getVariabiliTipiAggiuntiviProcedimento() {
        $variabiliTipiDato = new itaDictionary();
        $praLib = new praLib();
        if ($this->chiavePasso) {
            $PRAM_DB = $praLib->getPRAMDB();
            $Itepas_rec = $praLib->GetItepas($this->chiavePasso, 'itekey');
            if (!$Itepas_rec) {
                return false;
            }
            $currSeq = $Itepas_rec['ITESEQ'];
            $sql = "
            SELECT
                ITEDAG.ITEKEY AS ITEKEY,
                ITEDAG.ITECOD AS ITECOD,
                ITEDAG.ITDDES AS ITDDES,
                ITEDAG.ITDTIP AS ITDTIP,
                ITEDAG.ITDKEY AS ITDKEY
            FROM
                ITEPAS ITEPAS
            LEFT OUTER JOIN
                ITEDAG ITEDAG
            ON
                ITEPAS.ITEKEY=ITEDAG.ITEKEY
            WHERE
                ITEDAG.ITDTIP<>'' AND ITEPAS.ITECOD='" . $this->codiceProcedimento . "' AND ITEPAS.ITESEQ <= '" . $currSeq . "'
            ORDER BY
                ITESEQ";

            $Itedag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        } else {
            $j = 0;
            $Itedag_tab[$j]['ITDTIP'] = "Sportello_Aggregato";
            $Itedag_tab[$j]['ITDDES'] = "Sportello Aggregato";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "DenominazioneImpresa";
            $Itedag_tab[$j]['ITDDES'] = "Denominazione Impresa";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Indir_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "Indirizzo Insediamento Produttivo";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Civico_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "N. Civico Insediamento Produttivo";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Cap_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "Cap Insediamento Produttivo";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Prov_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "Provincia Insediamento Produttivo";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Codfis_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "Codice Fiscale Insediamento Produttivo";

            $j += 1;
            $Itedag_tab[$j]['ITDTIP'] = "Email_InsProduttivo";
            $Itedag_tab[$j]['ITDDES'] = "E-mail Insediamento Produttivo";
        }

        if ($Itedag_tab) {
            $i = 0;
            foreach ($Itedag_tab as $chiave => $Itedag_rec) {
                $variabiliTipiDato->addField($Itedag_rec['ITDTIP'], $Itedag_rec['ITDDES'], $i++, 'base', "", true);
            }
            $this->variabiliTipiAggiuntivi = $variabiliTipiDato;
        } else {
            $this->variabiliTipiAggiuntivi = null;
        }
        return $this->variabiliTipiAggiuntivi;
    }

    public function getVariabiliLista() {
        $i = 1;
        $dizionario = new itaDictionary();

        $dizionario->addField('ALLEGATI_PASSO', 'Allegati passo', $i++, 'base');
        $dizionario->addField('ALLEGATI_PASSO_PUBBLICATI', 'Allegati passo pubblicati', $i++, 'base');
        $dizionario->addField('ALLEGATI_RICHIESTA', 'Allegati richiesta', $i++, 'base');
        $dizionario->addField('ALLEGATI_RICHIESTA_NV', 'Allegati richiesta non validi', $i++, 'base');
        $dizionario->addField('ALLEGATI_FASCICOLO_NV', 'Allegati Fascicolo non validi', $i++, 'base');
        $dizionario->addField('FASCICOLO_SOCI', 'Soci fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_DICHIARANTI', 'Dichiaranti fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_ESIBENTI', 'Esibenti fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_IMPRESA', 'Impresa fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_DESTINATARI', 'Destinatari fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_IMMOBILI', 'Immobili fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_ONERI', 'Oneri del Fascicolo', $i++, 'base');
        $dizionario->addField('FASCICOLO_PAGAMENTI', 'Pagamenti del Fascicolo', $i++, 'base');
        $dizionario->addField('DATIAGGIUNTIVI_PASSO', 'Dati Aggiuntivi del Passo', $i++, 'base');

        return $dizionario;
    }

    public function getDatiVariabiliLista() {
        $dizionario = $this->getVariabiliLista();
        $praLib = new praLib();

        $AllegatiPasso = $this->componiAllegatiPasso($praLib);
        $AllegatiPassoPubblicati = $this->componiAllegatiPassoPubblicati($praLib);
        $AllegatiRichiesta = $this->componiAllegatiRichiesta($praLib);
        $AllegatiRichiestaNV = $this->componiAllegatiRichiestaNV($praLib);
        $AllegatiFascicoloNV = $this->componiAllegatiFascicoloNV($praLib);
        $sociFascicolo = $this->componiSoggettoFascicolo($praLib, 'SOCIO');
        $dichiaranteFascicolo = $this->componiSoggettoFascicolo($praLib, 'DICHIARANTE');
        $esibenteFascicolo = $this->componiSoggettoFascicolo($praLib, 'ESIBENTE');
        $impresaFascicolo = $this->componiSoggettoFascicolo($praLib, 'IMPRESA');
        $destinatariPasso = $this->componiDestinatariPasso($praLib);
        $immobiliFascicolo = $this->componiImmobiliFascicolo($praLib);
        $oneriFascicolo = $this->componiOneriFascicolo($praLib);
        $pagamentiFascicolo = $this->componiPagamentiOneriFascicolo($praLib);
        $datiAggiuntiviPasso = $this->componiDatiAggiuntiviPasso($praLib);

        $dizionario->addFieldData('ALLEGATI_PASSO', $AllegatiPasso);
        $dizionario->addFieldData('ALLEGATI_PASSO_PUBBLICATI', $AllegatiPassoPubblicati);
        $dizionario->addFieldData('ALLEGATI_RICHIESTA', $AllegatiRichiesta);
        $dizionario->addFieldData('ALLEGATI_RICHIESTA_NV', $AllegatiRichiestaNV);
        $dizionario->addFieldData('ALLEGATI_FASCICOLO_NV', $AllegatiFascicoloNV);
        $dizionario->addFieldData('FASCICOLO_SOCI', $sociFascicolo);
        $dizionario->addFieldData('FASCICOLO_DICHIARANTI', $dichiaranteFascicolo);
        $dizionario->addFieldData('FASCICOLO_ESIBENTI', $esibenteFascicolo);
        $dizionario->addFieldData('FASCICOLO_IMPRESA', $impresaFascicolo);
        $dizionario->addFieldData('FASCICOLO_DESTINATARI', $destinatariPasso);
        $dizionario->addFieldData('FASCICOLO_IMMOBILI', $immobiliFascicolo);
        $dizionario->addFieldData('FASCICOLO_ONERI', $oneriFascicolo);
        $dizionario->addFieldData('FASCICOLO_PAGAMENTI', $pagamentiFascicolo);
        $dizionario->addFieldData('DATIAGGIUNTIVI_PASSO', $datiAggiuntiviPasso);

        $this->variabiliLista = $dizionario;
        return $this->variabiliLista;
    }

    //public function valorizzaVariabiliAnavar($propas_rec) {
    public function valorizzaVariabiliAnavar($propas_rec = array(), $enteSuap = "", $extraParams = array()) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        if ($enteSuap) {
            // Per Mondavio e Monteporzio
            $PRAM_DB = ItaDB::DBOpen('PRAM', $enteSuap);
        }

        $dizionario = $this->setVariabiliAnavar();

        $AllegatiPasso = $this->componiAllegatiPasso($praLib);
        $DatiAggiuntiviPasso = $this->componiDatiAggiuntiviPasso($praLib);
        $AllegatiPassoPubblicati = $this->componiAllegatiPassoPubblicati($praLib);
        $AllegatiRichiesta = $this->componiAllegatiRichiesta($praLib);
        $AllegatiRichiestaNV = $this->componiAllegatiRichiestaNV($praLib);
        $AllegatiFascicoloNV = $this->componiAllegatiFascicoloNV($praLib);
        $sociFascicolo = $this->componiSoggettoFascicolo($praLib, 'SOCIO');
        $dichiaranteFascicolo = $this->componiSoggettoFascicolo($praLib, 'DICHIARANTE');
        $esibenteFascicolo = $this->componiSoggettoFascicolo($praLib, 'ESIBENTE');
        $impresaFascicolo = $this->componiSoggettoFascicolo($praLib, 'IMPRESA');
        $destinatariPasso = $this->componiDestinatariPasso($praLib);
        $immobiliFascicolo = $this->componiImmobiliFascicolo($praLib);
        $oneriFascicolo = $this->componiOneriFascicolo($praLib);
        $pagamentiFascicolo = $this->componiPagamentiOneriFascicolo($praLib);

        $dizionarioVariabiliLista = array('PRALISTA' => $this->getDatiVariabiliLista()->getAllDataPlain());

        //Carico la tabella
        $sql = "SELECT * FROM ANAVAR WHERE VARCLA = 'SUAP' ";
        $tabellaTab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        foreach ($tabellaTab as $tabellaRec) {
            switch ($tabellaRec['VARFONTE']) {
                case self::VAR_FONTE_ALLEGATI_PASSO:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $AllegatiPasso);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_DATIAGGIUNTIVI_PASSO:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $DatiAggiuntiviPasso);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_ALLEGATI_PASSO_PUBBLICATI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $AllegatiPassoPubblicati);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_ALLEGATI_FRONT:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $AllegatiRichiesta);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_ALLEGATI_FRONT_NON_VALIDI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $AllegatiRichiestaNV);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_SOGGETTI_SOCI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $sociFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_SOGGETTI_DICHIARANTE:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $dichiaranteFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_SOGGETTI_ESIBENTE:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $esibenteFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_SOGGETTI_IMPRESA:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $impresaFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_DESTINATARI_PASSO:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $destinatariPasso);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_IMMOBILI_PRATICA:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $immobiliFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_FASCICOLO_ONERI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $oneriFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_FASCICOLO_PAGAMENTI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $pagamentiFascicolo);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_GENERICA:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $dizionarioVariabiliLista);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                case self::VAR_FONTE_ALLEGATI_PRAT_NON_VALIDI:
                    $htmTab = $this->elaboraAnavarPerTarget($extraParams['TARGET'], $tabellaRec, $AllegatiFascicoloNV);
                    $this->variabiliAnavar->addFieldData($tabellaRec['VARCOD'], $htmTab);
                    break;
                default :
                    break;
            }
        }

        foreach ($this->variabiliAnavar->getDictionary() as $key => $value) {
            switch ($value['type']) {
                case '02': case '03':
                    $this->variabiliAnavar->addFieldData($key, $this->sostituisciVariabili($this->variabiliAnavar->getData($key), $dizionario));
                    break;
                case 'itaDictionary':
                    $this->valorizzaVariabiliAnavar($this->variabiliAnavar->getData($key));
                    break;
                default:
                    break;
            }
        }
        return $this->variabiliAnavar;
    }

    public function valorizzaVariabiliOneriPag() {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLib = new praLib();
        $oneri_tab = $praLib->GetProimpo($this->codicePratica, 'codice', '', true);
        $pagamenti_tab = $praLib->GetProconciliazione($this->codicePratica, 'codice', '', true);
        foreach ($oneri_tab as $key => $oneri_rec) {
            foreach ($oneri_rec as $chiave => $value) {
                if ($chiave == 'DATAREG' || $chiave == 'DATASCAD') {
                    if (empty($value)) {
                        continue;
                    }
                    $data = substr($value, -2) . '/' . substr($value, 4, -2) . '/' . substr($value, 0, 4);
                    $oneri_tab[$key][$chiave] = $data;
                }
                if ($chiave == 'IMPOCOD') {
                    $decod_tipo = $praLib->GetAnatipimpo($value);
                    $oneri_tab[$key][$chiave] = $decod_tipo['DESCTIPOIMPO'];
                }
            }
        }
        foreach ($pagamenti_tab as $key => $pagamento_rec) {
            foreach ($pagamento_rec as $chiave => $value) {
                if ($chiave == 'DATAQUIETANZA' || $chiave == 'DATARIVERSAMENTO' || $chiave == 'DATAINSERIMENTO') {
                    if (empty($value)) {
                        continue;
                    }
                    $data = substr($value, -2) . '/' . substr($value, 4, -2) . '/' . substr($value, 0, 4);
                    $pagamenti_tab[$key][$chiave] = $data;
                }
                if ($chiave == 'QUIETANZA') {
                    $decod_tipo = $praLib->GetAnaquiet($value);
                    $pagamenti_tab[$key][$chiave] = $decod_tipo['QUIETANZATIPO'];
                }
            }
        }
        $dizionario = $this->getVariabiliOneriPag();
        $dizionario->addFieldData('ONERI', $oneri_tab);
        $dizionario->addFieldData('PAGAMENTI', $pagamenti_tab);
        $this->variabiliOneriPagamenti = $dizionario;
        return $this->variabiliOneriPagamenti;
    }

    public function sostituisciVariabili($expr, $dict) {
        $itaSmarty = new itaSmarty();
        $itaSmarty->force_compile = true;
        foreach ($dict as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.tpl';
        if (!$this->writeFile($documentoTmp, $expr)) {
            return false;
        }
        $contenuto = $itaSmarty->fetch($documentoTmp);
        @unlink($documentoTmp);
        return $contenuto;
    }

    public function componiAllegatiPasso($praLib) {
        $allePasso = array();
        $Pasdoc_tab = $praLib->GetPasdoc($this->chiavePasso, "codice", true);
        $cc = 1;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $allePasso[$cc]['PASSO_ALLE']['NOME'] = $Pasdoc_rec['PASNAME'];
            $allePasso[$cc]['PASSO_ALLE']['NOTE'] = $Pasdoc_rec['PASNOT'];
            $allePasso[$cc]['PASSO_ALLE']['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
            $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
            $allePasso[$cc]['PASSO_ALLE']['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
            $allePasso[$cc]['PASSO_ALLE']['HASH'] = $Pasdoc_rec['PASSHA2'];
            $cc ++;
        }
        return $allePasso;
    }

    public function componiDatiAggiuntiviPasso($praLib) {
        $datiAggPasso = array();
        $Prodag_tab = $praLib->GetProdag($this->chiavePasso, "dagpak", true);
        $cc = 1;
        foreach ($Prodag_tab as $Prodag_rec) {
            $datiAggPasso[$cc]['PASSO_DATIAGGIUNTIVI']['DAGDES'] = $Prodag_rec['DAGDES'];
            $datiAggPasso[$cc]['PASSO_DATIAGGIUNTIVI']['DAGKEY'] = $Prodag_rec['DAGKEY'];
            $datiAggPasso[$cc]['PASSO_DATIAGGIUNTIVI']['DAGVAL'] = $Prodag_rec['DAGVAL'];
            $cc ++;
        }
        return $datiAggPasso;
    }

    public function componiAllegatiPassoPubblicati($praLib) {
        $allePasso = array();
        $Pasdoc_tab = $praLib->GetPasdoc($this->chiavePasso, "codice", true);
        $cc = 1;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            if ($Pasdoc_rec['PASPUB'] != 1) {
                continue;
            }
            $allePasso[$cc]['PASSO_ALLE']['NOME'] = $Pasdoc_rec['PASNAME'];
            $allePasso[$cc]['PASSO_ALLE']['NOTE'] = $Pasdoc_rec['PASNOT'];
            $allePasso[$cc]['PASSO_ALLE']['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
            $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
            $allePasso[$cc]['PASSO_ALLE']['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
            $allePasso[$cc]['PASSO_ALLE']['HASH'] = $Pasdoc_rec['PASSHA2'];
            $cc ++;
        }
        $proclt_rec = $praLib->GetPropassi(substr($this->chiavePasso, 0, 10), '', 'proclt', true);

        $alleTipPasso = array();
        foreach ($proclt_rec as $key => $proclt) {
            $arr = $this->componiAllegatiTipoPassoPubblicati($praLib, $proclt['PROCLT']);
            foreach ($arr as $idx => $value) {
                if (isset($alleTipPasso[$idx])) {
                    $alleTipPasso[$idx] = array_merge($alleTipPasso[$idx], $value);
                } else {
                    $alleTipPasso[$idx] = $value;
                }
            }
        }
        $PasDoc_all = $allePasso;
        foreach ($alleTipPasso as $key => $value) {
            if (isset($PasDoc_all[$key])) {
                $PasDoc_all[$key] = array_merge($PasDoc_all[$key], $value);
            } else {
                $PasDoc_all[$key] = $value;
            }
        }

        return $PasDoc_all;
    }

    public function componiAllegatiTipoPassoPubblicati($praLib, $proclt) {
        $alleTipPasso = array();
        $TipPasso = array();
        $Propas_rec = $praLib->GetPropas($this->chiavePasso);
        $where = "AND PROSEQ < '" . $Propas_rec['PROSEQ'] . "' AND PROCLT = '$proclt' AND PROOPE = '' "; //PROOPE distingue i passi di gestione
        $Propas_tab = $praLib->GetPropassi(substr($this->chiavePasso, 0, 10), $where, 'codice', true);
        $cc = 1;
        //Out::msgInfo('$Propas_tab', print_r($Propas_tab, true));
        foreach ($Propas_tab as $key => $Propas_rec) {
            $Pasdoc_tab = $praLib->GetPasdoc($Propas_rec['PROPAK'], 'codice', true);
            if (in_array($Propas_rec['PROCLT'], $TipPasso)) {
                continue;
            }
            if (!$proclt) {
                $proclt = '000000';
            }
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                if ($Pasdoc_rec['PASPUB'] != 1) {
                    continue;
                }
                $alleTipPasso[$cc]['PASSO_ALLE_' . $proclt]['NOME'] = $Pasdoc_rec['PASNAME'];
                $alleTipPasso[$cc]['PASSO_ALLE_' . $proclt]['NOTE'] = $Pasdoc_rec['PASNOT'];
                $alleTipPasso[$cc]['PASSO_ALLE_' . $proclt]['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
                $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
                $alleTipPasso[$cc]['PASSO_ALLE_' . $proclt]['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
                $alleTipPasso[$cc]['PASSO_ALLE_' . $proclt]['HASH'] = $Pasdoc_rec['PASSHA2'];
                $cc ++;
            }
            $TipPasso[$key] = $Propas_rec['PROCLT'];
        }
        return $alleTipPasso;
    }

    public function componiAllegatiRichiesta($praLib) {
        $alleRichiesta = array();
        $sql = "SELECT
                    PASDOC.PASNAME,
                    PASDOC.PASNOT,
                    PASDOC.PASSHA2
                FROM
                    PASDOC
                LEFT OUTER JOIN
                    PROPAS
                ON
                    PASDOC.PASKEY=PROPAS.PROPAK
                WHERE
                    PROPAS.PRONUM = '$this->codicePratica' AND
                    PROPAS.PROPUB = 1
                ";
        $Pasdoc_tab = $praLib->getGenericTab($sql);
        $cc = 1;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $alleRichiesta[$cc]['RICHIESTA_ALLE']['NOME'] = $Pasdoc_rec['PASNAME'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE']['NOTE'] = $Pasdoc_rec['PASNOT'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE']['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
            $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
            $alleRichiesta[$cc]['RICHIESTA_ALLE']['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE']['HASH'] = $Pasdoc_rec['PASSHA2'];
            $cc ++;
        }
        return $alleRichiesta;
    }

    public function componiAllegatiRichiestaNV($praLib) {
        $alleRichiesta = array();
        $sql = "SELECT
                    PASDOC.PASNAME,
                    PASDOC.PASNOT,
                    PASDOC.PASSHA2
                FROM
                    PASDOC
                LEFT OUTER JOIN
                    PROPAS
                ON
                    PASDOC.PASKEY=PROPAS.PROPAK
                WHERE
                    PROPAS.PRONUM = '$this->codicePratica' AND
                    PROPAS.PROPUB = 1 AND
                    PASDOC.PASSTA = 'N'
                ";
        $Pasdoc_tab = $praLib->getGenericTab($sql);
        $cc = 1;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $alleRichiesta[$cc]['RICHIESTA_ALLE_NV']['NOME'] = $Pasdoc_rec['PASNAME'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE_NV']['NOTE'] = $Pasdoc_rec['PASNOT'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE_NV']['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
            $Anacla_rec = $praLib->GetAnacla($Pasdoc_rec['PASCLAS']);
            $alleRichiesta[$cc]['RICHIESTA_ALLE_NV']['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];
            $alleRichiesta[$cc]['RICHIESTA_ALLE_NV']['HASH'] = $Pasdoc_rec['PASSHA2'];
            $cc ++;
        }
        return $alleRichiesta;
    }
    public function componiAllegatiFascicoloNV($praLib) {
        $alleRichiesta = array();
        $sql = "SELECT
                    PASNAME,
                    PASNOT,
                    PASNOTE,
                    PASSHA2
                FROM
                    PASDOC
                WHERE
                    PASKEY LIKE '".$this->codicePratica."%' AND PASSTA = 'N'
                ";
        $Pasdoc_tab = $praLib->getGenericTab($sql);
        $cc = 1;
        foreach ($Pasdoc_tab as $Pasdoc_rec) {
            $alleRichiesta[$cc]['PRATICA_ALLE_NV']['NOME'] = $Pasdoc_rec['PASNAME'];
            $alleRichiesta[$cc]['PRATICA_ALLE_NV']['NOTE'] = $Pasdoc_rec['PASNOT'];
            $alleRichiesta[$cc]['PRATICA_ALLE_NV']['NOTEALLEGATO'] = $Pasdoc_rec['PASNOTE'];
           $alleRichiesta[$cc]['PRATICA_ALLE_NV']['HASH'] = $Pasdoc_rec['PASSHA2'];
            $cc ++;
        }
        return $alleRichiesta;
    }

    public function componiSociFascicolo($praLib) {
        //include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
        $soci = array();
        $Anades_tab = $praLib->GetAnades($this->codicePratica, "ruolo", true, praRuolo::getSystemSubjectCode('SOCIO'));
        $cc = 1;
        foreach ($Anades_tab as $Anades_rec) {
            $denom = $Anades_rec['DESCOG'] . " " . $Anades_rec['DESNOM'];
            if ($denom == "")
                $denom = $Anades_rec['DESNOM'];
            $soci[$cc]['FASCICOLO_SOCI']['DENOM'] = $denom;
            $soci[$cc]['FASCICOLO_SOCI']['DESNASCIT'] = $Anades_rec['DESNASCIT'];
            $soci[$cc]['FASCICOLO_SOCI']['DESNASPROV'] = $Anades_rec['DESNASPROV'];
            $soci[$cc]['FASCICOLO_SOCI']['DESNASDAT'] = substr($Anades_rec['DESNASDAT'], 6, 2) . "/" . substr($Anades_rec['DESNASDAT'], 4, 2) . "/" . substr($Anades_rec['DESNASDAT'], 0, 4); //$Anades_rec['DESNASDAT'];
            //
            $soci[$cc]['FASCICOLO_SOCI']['DESCIT'] = $Anades_rec['DESCIT'];
            $soci[$cc]['FASCICOLO_SOCI']['DESPRO'] = $Anades_rec['DESPRO'];
            $soci[$cc]['FASCICOLO_SOCI']['INDRES'] = $Anades_rec['DESIND'] . " " . $Anades_rec['DESCIV'];
            //
            $soci[$cc]['FASCICOLO_SOCI']['DESFIS'] = $Anades_rec['DESFIS'];
            $cc ++;
        }
        return $soci;
    }

    public function componiSoggettoFascicolo($praLib, $ruolo) {
        //include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
        $soggetto = array();
        $Anades_tab = $praLib->GetAnades($this->codicePratica, "ruolo", true, praRuolo::getSystemSubjectCode($ruolo));
        $cc = 1;
        foreach ($Anades_tab as $Anades_rec) {
            $denom = $Anades_rec['DESCOG'] . " " . $Anades_rec['DESNOM'];
            if ($denom == "")
                $denom = $Anades_rec['DESNOM'];
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DENOM'] = $denom;
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESNASCIT'] = $Anades_rec['DESNASCIT'];
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESNASPROV'] = $Anades_rec['DESNASPROV'];
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESNASDAT'] = substr($Anades_rec['DESNASDAT'], 6, 2) . "/" . substr($Anades_rec['DESNASDAT'], 4, 2) . "/" . substr($Anades_rec['DESNASDAT'], 0, 4); //$Anades_rec['DESNASDAT'];
            //
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESCIT'] = $Anades_rec['DESCIT'];
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESPRO'] = $Anades_rec['DESPRO'];
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['INDRES'] = $Anades_rec['DESIND'] . " " . $Anades_rec['DESCIV'];
            //
            $soggetto[$cc]['FASCICOLO_SOGGETTO']['DESFIS'] = $Anades_rec['DESFIS'];
            if ($ruolo == 'SOCIO') {
                $soggetto[$cc]['FASCICOLO_SOCI']['DENOM'] = $denom;
                $soggetto[$cc]['FASCICOLO_SOCI']['DESNASCIT'] = $Anades_rec['DESNASCIT'];
                $soggetto[$cc]['FASCICOLO_SOCI']['DESNASPROV'] = $Anades_rec['DESNASPROV'];
                $soggetto[$cc]['FASCICOLO_SOCI']['DESNASDAT'] = substr($Anades_rec['DESNASDAT'], 6, 2) . "/" . substr($Anades_rec['DESNASDAT'], 4, 2) . "/" . substr($Anades_rec['DESNASDAT'], 0, 4); //$Anades_rec['DESNASDAT'];
                //
                $soggetto[$cc]['FASCICOLO_SOCI']['DESCIT'] = $Anades_rec['DESCIT'];
                $soggetto[$cc]['FASCICOLO_SOCI']['DESPRO'] = $Anades_rec['DESPRO'];
                $soggetto[$cc]['FASCICOLO_SOCI']['INDRES'] = $Anades_rec['DESIND'] . " " . $Anades_rec['DESCIV'];
                //
                $soggetto[$cc]['FASCICOLO_SOCI']['DESFIS'] = $Anades_rec['DESFIS'];
            }
            $cc ++;
        }
        return $soggetto;
    }

    public function componiDestinatariPasso($praLib) {
        $destinatari = array();
        $praMitDest_tab = $praLib->GetPraDestinatari($this->chiavePasso, "codice", true);
        $cc = 1;
        foreach ($praMitDest_tab as $praMitDest_rec) {
            $destinatari[$cc]['PASSO_DESTINATARI']['CODICE'] = $praMitDest_rec['CODICE'];
            $destinatari[$cc]['PASSO_DESTINATARI']['NOME'] = $praMitDest_rec['NOME'];
            $destinatari[$cc]['PASSO_DESTINATARI']['COMUNE'] = $praMitDest_rec['COMUNE'];
            $destinatari[$cc]['PASSO_DESTINATARI']['PROVINCIA'] = $praMitDest_rec['PROVINCIA'];
            $destinatari[$cc]['PASSO_DESTINATARI']['CAP'] = $praMitDest_rec['CAP'];
            $destinatari[$cc]['PASSO_DESTINATARI']['INDIRIZZO'] = $praMitDest_rec['INDIRIZZO'];
            $destinatari[$cc]['PASSO_DESTINATARI']['MAIL'] = $praMitDest_rec['MAIL'];
            $destinatari[$cc]['PASSO_DESTINATARI']['FISCALE'] = $praMitDest_rec['FISCALE'];
            $cc ++;
        }
        return $destinatari;
    }

    public function componiImmobiliFascicolo($praLib) {
        $immobili = array();
        $praimm_tab = $praLib->GetPraimm($this->codicePratica, "codice", true);
        $cc = 1;
        foreach ($praimm_tab as $praimm_rec) {
            $immobili[$cc]['FASCICOLO_IMM']['SEZIONE'] = $praimm_rec['SEZIONE'];
            $immobili[$cc]['FASCICOLO_IMM']['FOGLIO'] = $praimm_rec['FOGLIO'];
            $immobili[$cc]['FASCICOLO_IMM']['PARTICELLA'] = $praimm_rec['PARTICELLA'];
            $immobili[$cc]['FASCICOLO_IMM']['SUBALTERNO'] = $praimm_rec['SUBALTERNO'];
            $cc ++;
        }
        return $immobili;
    }

    public function componiOneriFascicolo($praLib) {
        $Oneri = array();
        $praoneri_tab = $praLib->GetProimpo($this->codicePratica, 'codice', '', true);
        $cc = 1;
        foreach ($praoneri_tab as $praoneri_rec) {
            foreach ($praoneri_rec as $chiave => $value) {
                if ($chiave == 'DATAREG' || $chiave == 'DATASCAD') {
                    if (empty($value)) {
                        continue;
                    }
                    $data = substr($value, -2) . '/' . substr($value, 4, -2) . '/' . substr($value, 0, 4);  // formatto date
                    $praoneri_rec[$chiave] = $data;
                }
                if ($chiave == 'IMPOCOD') {
                    $decod_tipo = $praLib->GetAnatipimpo($value);  // decod tipo costo
                    $praoneri_rec[$chiave] = $decod_tipo['DESCTIPOIMPO'];
                }
            }
            $Oneri[$cc]['FASCICOLO_ONERI']['IMPOCOD'] = $praoneri_rec['IMPOCOD'];
            $Oneri[$cc]['FASCICOLO_ONERI']['IMPORTO'] = $praoneri_rec['IMPORTO'];
            $Oneri[$cc]['FASCICOLO_ONERI']['DATASCAD'] = $praoneri_rec['DATASCAD'];
            $Oneri[$cc]['FASCICOLO_ONERI']['DATAREG'] = $praoneri_rec['DATAREG'];
            $Oneri[$cc]['FASCICOLO_ONERI']['PAGATO'] = $praoneri_rec['PAGATO'];
            $cc ++;
        }
        return $Oneri;
    }

    public function componiPagamentiOneriFascicolo($praLib) {
        $Pagamenti = array();
        $prapagamenti_tab = $praLib->GetProconciliazione($this->codicePratica, 'codice', '', true);
        $cc = 1;
        foreach ($prapagamenti_tab as $prapagamenti_rec) {
            foreach ($prapagamenti_rec as $chiave => $value) {
                if ($chiave == 'DATAQUIETANZA' || $chiave == 'DATARIVERSAMENTO' || $chiave == 'DATAINSERIMENTO') {
                    if (empty($value)) {
                        continue;
                    }
                    $data = substr($value, -2) . '/' . substr($value, 4, -2) . '/' . substr($value, 0, 4);  // formatto date
                    $prapagamenti_rec[$chiave] = $data;
                }
                if ($chiave == 'QUIETANZA') {
                    $decod_tipo = $praLib->GetAnaquiet($value);  // decod tipo costo
                    $prapagamenti_rec[$chiave] = $decod_tipo['QUIETANZATIPO'];
                }
            }
            $Pagamenti[$cc]['FASCICOLO_PAGAMENTI']['QUIETANZA'] = $prapagamenti_rec['QUIETANZA'];
            $Pagamenti[$cc]['FASCICOLO_PAGAMENTI']['SOMMAPAGATA'] = $prapagamenti_rec['SOMMAPAGATA'];
            $Pagamenti[$cc]['FASCICOLO_PAGAMENTI']['DATARIVERSAMENTO'] = $prapagamenti_rec['DATARIVERSAMENTO'];
            $Pagamenti[$cc]['FASCICOLO_PAGAMENTI']['DATAQUIETANZA'] = $prapagamenti_rec['DATAQUIETANZA'];
            $cc ++;
        }
        return $Pagamenti;
    }

    private function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!@fwrite($fpw, $string)) {
            fclose($fpw);
            return false;
        }
        fclose($fpw);
        return true;
    }

    function elaboraTabella($html, $dati) {
        $dom = new DOMDocument;
        //
        // Documento template
        //
        @$dom->loadHTML($html);

        //
        // Tabella template
        //
        $table = $dom->getElementsByTagName('table');

        //
        // Dom di Output
        //
        $printDOM = new DOMDocument();
        $cloned = $table->item(0)->cloneNode(TRUE);
        $tbody = $cloned->getElementsByTagName('tbody')->item(0);
        $trToRemove = $tbody->getElementsByTagName('tr');
        while ($trToRemove->length > 0) {
            $tbody->removeChild($trToRemove->item(0));
        }
        $printDOM->appendChild($printDOM->importNode($cloned, TRUE));


        $links = $dom->getElementsByTagName('tr');
        $trArr = array();
        foreach ($links as $id => $link) {
            $class = $link->getAttribute('class');
            if ($class == 'ita-table-header') {
                $printDOM->getElementsByTagName('tbody');
                $printDOM->documentElement->appendChild($printDOM->importNode($link, TRUE));
            }
        }

        foreach ($links as $id => $link) {
            $class = $link->getAttribute('class');
            if ($class !== 'ita-table-header' && $class !== 'ita-table-footer') {
                $tmpDOM = new DOMDocument();

                $cloned = $link->cloneNode(TRUE);

                $tmpDOM->appendChild($tmpDOM->importNode($cloned, TRUE));

                $stringTR = $tmpDOM->saveHtml();

                $tmpDOM = null;
                $printDOM->getElementsByTagName('tbody');
                foreach ($dati as $key => $recordDati) {
                    $chiavePresente = false;
                    foreach (array_keys($recordDati) as $keyDato) {
                        if (preg_match('/@{\$' . $keyDato . '(?:\.[\w.]*?|)}@/m', $stringTR)) {
                            $chiavePresente = true;
                            break;
                        }
                    }

                    if (!$chiavePresente) {
                        continue;
                    }
                    //
                    // Qui Sostitisco
                    //
                    $contenutoFinale = $this->sostituisciVariabili($stringTR, $recordDati);
                    $tmpDOM = new DOMDocument();
                    $tmpDOM->loadHTML($contenutoFinale);
                    $trNode = $tmpDOM->getElementsByTagName('tr')->item(0);
                    $printDOM->documentElement->appendChild($printDOM->importNode($trNode, TRUE));
                }
            }
        }
        foreach ($links as $id => $link) {
            $class = $link->getAttribute('class');
            if ($class == 'ita-table-footer') {
                $printDOM->getElementsByTagName('tbody');
                $printDOM->documentElement->appendChild($printDOM->importNode($link, TRUE));
            }
        }
        return $printDOM->saveHtml();
    }

    public function getVariabiliTariffe() {
        $i = 1;
        $dizionario = new itaDictionary();

        //
        // Variabili Listino
        //
        
        $praLib = new praLib();
        $itepas_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM ITEPAS WHERE ITECOD = '{$this->codiceProcedimento}' AND ITEPAY = '1'", true);

        foreach ($itepas_tab as $itepas_rec) {
            $dizionario->addField('TARIFFA_' . $itepas_rec['ITEKEY'], 'Tariffa Passo "' . $itepas_rec['ITEDES'] . '"', $i++, 'base');
        }

        return $dizionario;
    }

    public function getVariabiliAmbiente() {
        $i = 1;
        $dizionario = new itaDictionary();

        //
        // Variabili d'Ambiente
        //
        
        $praLib = new praLib();
        $ambiente_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM VARIABILIAMBIENTE", true);

        foreach ($ambiente_tab as $ambiente_rec) {
            $chiave = $ambiente_rec['VARKEY'];
            $dizionario->addField($ambiente_rec['VARKEY'], praLibEnvVars::$SISTEM_ENVIRONMENT_VARIABLES[$chiave]['ENVDES'], $i++, 'base');
        }

        return $dizionario;
    }

    private function elaboraAnavarPerTarget($target, $anavar_rec, $data) {
        switch ($target) {
            case 'DOCX':
                return $this->elaboraDOCX($anavar_rec['VAREXPDOCX'], $data);

            default:
            case 'XHTML':
                if ($anavar_rec['VARFONTE'] == self::VAR_FONTE_GENERICA) {
                    return $this->sostituisciVariabili($anavar_rec['VAREXP'], $data);
                }
                return $this->elaboraTabella($anavar_rec['VAREXP'], $data);
        }
    }

    /**
     * @param string $documentiCod Codice documento DOC_DOCUMENTI
     * @param array $datiTab (opzionale) Array di dati tabella da elaborare (in formato [RIGA][TABELLA][CHIAVE] = VALORE, es. [1][PARERI_DELIB][ESITO] = POSITIVO)
     * @param array $baseDictionary (opzionale) Dizionario da utilizzare come base
     * @return itaDocumentDOCX
     */
    private function elaboraDOCX($documentiCod, $datiTab = array(), $baseDictionary = array()) {
        if (!$documentiCod) {
            return '';
        }

        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

        /* @var $docLib docLib */
        $docLib = new docLib();
        $documenti_rec = $docLib->getDocumenti($documentiCod);

        $docx_path = $docLib->setDirectory() . $documenti_rec['URI'];

        if (!$documenti_rec || !$documenti_rec['URI'] || !file_exists($docx_path)) {
            return '';
        }

        $dictionary = $baseDictionary;

        foreach ($datiTab as $dato) {
            foreach ($dato as $tabella => $record) {
                if (!isset($dictionary[$tabella])) {
                    $dictionary[$tabella] = array();
                }

                foreach ($record as $column => $value) {
                    if (!isset($dictionary[$tabella][$column])) {
                        $dictionary[$tabella][$column] = array();
                    }

                    $dictionary[$tabella][$column][] = $value;
                }
            }
        }

        /* @var $documentDocx itaDocumentDOCX */
        $documentDocx = itaDocumentFactory::getDocument('docx');
        $documentDocx->setDictionary($dictionary);

        if (!$documentDocx->loadContent($docx_path) || !$documentDocx->mergeDictionary()) {
            Out::msgStop("Errore", $documentDocx->getMessage());
            return '';
        }

        return $documentDocx;
    }

}
