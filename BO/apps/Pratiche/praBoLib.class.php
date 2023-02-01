<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    pra
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    15.06.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */


class praBoLib {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $PRAM_DB;
    public $praLib;

    function __construct($ditta = '') {
        try {
            $this->praLib = new praLib($this->praErr);
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function prendiDati($ricnum, $seq = '', $simulaCanc = '') {
        //
        // Vettore di salvataggio mappa dei dati
        //
        $dati = array();

        //
        // Codice Richiesta
        //
        if (!$ricnum) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0015', "N. Lettura Richiesta N." . $ricnum . " Fallita.", __CLASS__);
            return false;
        }

        //@TODO DA VERIFICARE
        if ($simulaCanc) {
            foreach ($simulaCanc as $key => $value) {
                $NewSequenza_pre = str_replace(chr(46) . $value . chr(46), "", $Proric_rec['RICSEQ']);
                $NewSequenza = ereg_replace('[[:space:]]+', '', $NewSequenza_pre);
                $Proric_rec['RICSEQ'] = $NewSequenza;
            }
        }
        //
        // Lettura Proges e Propas
        //
                        
        //$Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $ricnum . "'", false);

        $ProGes_rec = $this->praLib->GetProges($ricnum);
        $ProPas_rec = $this->praLib->GetPropas($ricnum, 'codice');

//        if (!$Proric_rec) {
//            output::$html_out = $this->praErr->parseError(__FILE__, 'E0015', "N. Lettura Richiesta N." . $ricnum . " Fallita.", __CLASS__);
//            return false;
//        }
//        $dati['Proric_rec'] = $Proric_rec;
        //
        //Controllo se la richiesta è offline per manutenzione
        //
        if ($ProPas_rec['RICSTA'] == "OF") {
            $html = new html();
            $html->appendHtml("<img class=\"ImgLavori\" src=\"" . ItaUrlUtil::UrlInc() . "/SUAP_praMup/images/lavori.png\" style=\"border:0px;\"></img>");
            $html->appendHtml("<pre style=\"font-size:1.4em;font-family:arial, tahoma, verdana;\"><b>Attenzione!!! la pratica n. " . $ProPas_rec['RICNUM'] . "  è momentaneamente non disponibile per manutenzione.<b></pre>");
            output::$html_out .= $html->getHtml();
            return false;
        }

        //
        // Codice Procedimento
        //
        $dati['Codice'] = $ProGes_rec['GESPRO'];
        $dati['CodiceEvento'] = $ProGes_rec['GESEVE'];

        //
        // Ruolo
        //
        $dati['ruolo'] = "";


        //
        // Dati Anagrafica Procedimento
        //
        $Anapra_rec = $this->praLib->GetAnapra($dati['Codice'], 'codice', $this->PRAM_DB);
        if (!$Anapra_rec) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0017', "Record procedimento " . $dati['Codice'] . " mancante su ANAPRA", __CLASS__);
            return false;
        }

        //
        // Dati Anagrafica Evento solo se utilizzato
        //
        $Anaeventi_rec = array();
        if ($dati['CodiceEvento']) {
            $Anaeventi_rec = $this->praLib->GetAnaeventi($dati['CodiceEvento'], 'codice', $this->PRAM_DB);
            if (!$Anaeventi_rec) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0021', "Record evento " . $dati['Codice'] . " mancante su ANAEVENTI", __CLASS__);
                return false;
            }
        }


        //
        // Dati Tipologie procedimento
        //
        $Anatip_rec = $this->praLib->GetAnatip($ProGes_rec['GESTIP'], 'codice', $this->PRAM_DB);

        //
        // Dati Settore
        //
        $Anaset_rec = $this->praLib->GetAnaset($ProGes_rec['GESSTT'], 'codice', $this->PRAM_DB);


        //
        // Dati Attivita
        //
        $Anaatt_rec = $this->praLib->GetAnaatt($ProGes_rec['GESATT'], 'codice', $this->PRAM_DB);

        //
        // Dati Elenco Sportelli Aggregati Anagrafati
        //
        $Anaspa_tab = array();
        $Anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPAATT = 1", true);

        //
        // Dati Sportello On-Line per la richiesta
        //
        $Anatsp_rec = array();
        $Anatsp_rec = $this->praLib->GetAnatsp($ProGes_rec['GESTSP'], 'codice', $this->PRAM_DB);
        $Ananom_tspres_rec = array();
        $Ananom_tspres_rec = $this->praLib->GetAnanom($Anatsp_rec['TSPRES'], 'codice', $this->PRAM_DB);

        //
        // Dati Sportello Aggregato per la richiesta
        //
        $Anaspa_rec = array();
        $Anaspa_rec = $this->praLib->GetAnaspa($ProGes_rec['GESSPA'], 'codice', $this->PRAM_DB);
        $Ananom_spares_rec = $this->praLib->GetAnanom($Anaspa_rec['SPARES'], 'codice', $this->PRAM_DB);


        //
        // Dati Responsabile procedimento
        //
        $Ananom_prares_rec = $this->praLib->GetAnanom($ProPas_rec['PRORES'], 'codice', $this->PRAM_DB);



        //
        // Dati Elenco Passi della richeista 
        //
        //$Ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '" . $ProPas_rec['PRONUM'] . "' AND PROPUB<>0 ORDER BY PROSEQ", true);
        $Ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '" . $ProPas_rec['PRONUM'] . "' ORDER BY PROSEQ", true);


        //
        // Estrazione dati Raccolta
        // $raccolta   = Vettore dati funzionale alla procedura di controllo visibilità per la procedura filtra passi
        //
        $sql = "SELECT
          RICDAG.ITEKEY AS ITEKEY,
          RICDAG.ITECOD AS ITECOD,
          RICDAG.DAGKEY AS DAGKEY,
          RICDAG.DAGDES AS DAGDES,
          RICDAG.DAGNUM AS DAGNUM,
          RICDAG.DAGSEQ AS DAGSEQ,
          RICDAG.DAGSET AS DAGSET,
          RICDAG.DAGVAL AS DAGVAL,
          RICDAG.RICDAT AS RICDAT,
          RICDAG.DAGTIP AS DAGTIP,
          RICDAG.DAGCTR AS DAGCTR,
          RICDAG.DAGNOT AS DAGNOT,
          RICDAG.DAGLAB AS DAGLAB,
          RICDAG.DAGTIC AS DAGTIC,
          RICDAG.DAGROL AS DAGROL,
          RICDAG.DAGVCA AS DAGVCA,
          RICDAG.DAGREV AS DAGREV,
          RICDAG.DAGLEN AS DAGLEN,
          RICDAG.DAGDIM AS DAGDIM,
          RICDAG.DAGDIZ AS DAGDIZ,
          RICDAG.DAGACA AS DAGACA,
          RICDAG.DAGPOS AS DAGPOS
          FROM
          RICITE RICITE
          LEFT OUTER JOIN
          RICDAG RICDAG
          ON
          RICITE.ITEKEY=RICDAG.ITEKEY AND RICITE.RICNUM=RICDAG.DAGNUM
          WHERE RICITE.RICNUM = '" . $ProPas_rec['PRONUM'] . "' AND RICITE.ITEDAT = 1";
        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $key => $Ricdag_rec) {
                $raccolta[$Ricdag_rec['DAGKEY']] = $Ricdag_rec['RICDAT'];
            }
        }


        //
        // Creazione del vettore Navigatore
        //
//        if ($p) {
        $Ricite_tab_da_filtrare = array();
        foreach ($Ricite_tab as $key => $Ricite_rec) {
            $fl_off = $fl_obl = 0;
            if ($Ricite_rec['PROCLT']) {
                $Praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['PROCLT'] . "'", false);
                if ($Praclt_rec) {
                    if ($Praclt_rec['CLTOFF'] == 1) {
                        $fl_off = 1;
                    }
                    if ($Praclt_rec['CLTOBL'] == 1) {
                        $fl_obl = 1;
                    }
                }
            }
            $Ricite_rec['CLTOFF'] = $fl_off;
            $Ricite_rec['CLTOBL'] = $fl_obl;
            $Ricite_tab_da_filtrare[] = $Ricite_rec;
        }
//        }


        $Ricite_tab_tmp = array();

        foreach ($Ricite_tab as $key => $Ricite_rec) {
            $fl_off = false;
            if ($Ricite_rec['PROCLT']) {
                $Praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['PROCLT'] . "'", false);
                if ($Praclt_rec) {
                    if ($Praclt_rec['CLTOFF'] == 1) {
                        $fl_off = true;
                    }
                }
            }
            if ($fl_off == false) {
                $Ricite_tab_tmp[] = $Ricite_rec;
            }
        }


        $Ricite_tab = $Ricite_tab_tmp;
//        if (!$Ricite_tab) {
//            output::$html_out = $this->praErr->parseError(__FILE__, 'E0018', "Passi non trovati per la Pratica n. " . $ProPas_rec['PRONUM'] . "  con Procedimento n. " . $dati['Codice'], __CLASS__);
//            return false;
//        }


        //
        // Dati Ricite_rec Passo corrente in base alla sequenza
        //
        if ($seq) {
            $Ricite_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '" . $ProPas_rec['PRONUM'] . "'  AND PROSEQ = '" . $seq . "'", false);
        } else {
            $Ricite_rec = $Ricite_tab[0];
            $dati['seq'] = $Ricite_rec['PROSEQ'];
        }
//        if (!$Ricite_rec) {
//            output::$html_out = $this->praErr->parseError(__FILE__, 'E0019', "Passo con sequenza " . $dati['seq'] . " mancante nella Pratca n. " . $ProPas_rec['PRONUM'], __CLASS__);
//            return false;
//        }
        $dati['seq'] = $Ricite_rec['PROSEQ'];

        //
        //Per il passo corrente mi trovo le espressioni per i controlli associati
        //
        $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $ProPas_rec['PRONUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' ORDER BY SEQUENZA", true);
        if (!$Riccontrolli_tab) {
            $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $ProPas_rec['PRONUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITECTP'] . "' ORDER BY SEQUENZA", true);
        }



        //
        // Estrazione dati Raccolta
        // $Ricdag_tab = Records relativi alla raccolta
        // Serve per la gestione dei passi raccolta dati
        //
        $Ricdag_tab = array();
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' ORDER BY DAGSEQ";

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        /*
         * Cambiato controllo valorizzazione ricdat per bug se non veniva valorizzato nessun campo aggiuntivo (22/12/2016)
         */
        if ($_POST['event'] == "submitRaccolta" || $_POST['event'] == "submitRaccoltaMultipla") {
            $dati['ricdat'] = 1;
        } elseif ($_POST['event'] == "annullaRaccolta") {
            $dati['ricdat'] = 0;
        }
        if (strpos($ProPas_rec['PROSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
            $dati['ricdat'] = 0;
        } else {
            $dati['ricdat'] = 1;
        }

        //
        //Mi trovo tutti i dati aggiuntivi della richiesta
        //
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "'";

        $Ricdag_tab_totali = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        //
        //Mi trovo il record del dato aggiuntivo tipizzato del codice fiscale
        //
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND DAGTIP = 'Codfis_InsProduttivo'";

        $Ricdag_rec_codFis = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        //
        //Mi trovo i record dei dati aggiuntivi che finiscono per _CFI e _PIVA per attivare controllo sul rapporto
        //Escudo ovviamente quello dell'esibente
        //
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND DAGKEY<>'ESIBENTE_CODICEFISCALE_CFI' AND DAGKEY<>'DICHIARANTE_CODICEFISCALE_CFI' AND DAGKEY<>'PREPOSTO_CODICEFISCALE_CFI' AND (DAGKEY LIKE '%_CFI%' OR DAGKEY LIKE '%_PIVA%')";

        $Ricdag_tab_CFMappati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        //
        //Dato aggiuntivo codice fiscale
        //  ??
//        $codProc = $dati['Codice'];
//        if ($ProPas_rec['PRORPA']) {
//            $Proric_rec_padre = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $ProPas_rec['PRORPA'] . "' AND RICFIS='" . $dati['Fiscale'] . "'", false);
//            if (!$Proric_rec_padre) {
//                output::$html_out = $this->praErr->parseError(__FILE__, 'E0015', "N. Lettura Richiesta Padre N." . $ProPas_rec['PRORPA'] . " Fallita.", __CLASS__);
//                return false;
//            }
//            $ricnum = $Proric_rec_padre['RICNUM'];
//            $codProc = $Proric_rec_padre['RICPRO'];
//        }
//        $sql = "SELECT * FROM RICDAG WHERE ITECOD = '$codProc' AND DAGNUM = '$ricnum' AND DAGKEY = 'TECPRG_CODICEFISCALE_CFI' AND RICDAT<>''";
//        $Ricdag_recFiscale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
//        if (!$Ricdag_recFiscale) {
//            $sql = "SELECT * FROM RICDAG WHERE ITECOD = '$codProc' AND DAGNUM = '$ricnum' AND DAGKEY = 'DICHIARANTE_CODICEFISCALE_CFI' AND RICDAT<>''";
//            $Ricdag_recFiscale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
//        }
        // ??
        //$sql = "SELECT * FROM RICDAG WHERE ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "' AND DAGKEY = 'DICHIARANTE_CODICEFISCALE_CFI' AND RICDAT<>''";
        //$Ricdag_recFiscale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        //
        // Tabella Allegati del passo corrente
        //
        $Ricdoc_tab = array();
        $sql = "SELECT
                        *
                    FROM
                        RICDOC
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DOCNUM = '" . $Ricite_rec['RICNUM'] . "'";

        $Ricdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        //
        // Tabella Allegati totale
        //
        $Ricdoc_tab_tot = array();
        $sql = "SELECT
                        *
                    FROM
                        RICDOC
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DOCNUM = '" . $Ricite_rec['RICNUM'] . "'";

        $Ricdoc_tab_tot = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        //
        // Decodifica dei dati di ruolo proveninti dai parametri di chiamata a PraMup
        //
//        switch (praMup::$param['ruolo']) {
//            case "0001" :
//                $dati['DescRuolo'] = 'ESIBENTE';
//                break;
//            case "0002" :
//                $dati['DescRuolo'] = 'PROCURATORE';
//                break;
//            case "0003" :
//                $dati['DescRuolo'] = 'AGENZIE';
//                break;
//        }

        $sqlUltSeq = "SELECT MAX(ITESEQ) AS ULTSEQ FROM RICITE WHERE RICNUM = '" . $ProPas_rec['PRONUM'] . "'";
        $ultSeq_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlUltSeq, true);
        $seqlen = 3;
        if ($ultSeq_rec) {
            $seqlen = strlen($ultSeq_rec[0]['ULTSEQ']);
        }
        if ($seqlen < 3)
            $seqlen = 3;

        //
        //Query dagset per raccolta ddati multipla
        //
        $sqlDagset = "SELECT
                        DAGSET
                    FROM
                        RICDAG
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $Ricite_rec['RICNUM'] . "'
                    GROUP BY DAGSET";

        $Dagset_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlDagset, true);

        //
        //Record passo articolo BO
        //
        if ($ProPas_rec['PROPAK']) {
            $propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PROPAK = '" . $ProPas_rec['PROPAK'] . "'", false);
        }

        //
        // Impacchettamento dati precedentemente estratti su variabili di appoggio
        //
        //$dati['Browser'] = frontOfficeApp::getBrowser();
        $dati['seqlen'] = $seqlen;
        $dati['Anaspa_tab'] = $Anaspa_tab;
        $dati['Anatsp_rec'] = $Anatsp_rec;
        $dati['Ananom_tspres_rec'] = $Ananom_tspres_rec;
        $dati['Anaspa_rec'] = $Anaspa_rec;
        $dati['Ananom_spares_rec'] = $Ananom_spares_rec;
        $dati['Anapra_rec'] = $Anapra_rec;
        $dati['Anaeventi_rec'] = $Anaeventi_rec;
        $dati['Anatip_rec'] = $Anatip_rec;
        $dati['Anaset_rec'] = $Anaset_rec;
        $dati['Anaatt_rec'] = $Anaatt_rec;
        $dati['Ananom_prares_rec'] = $Ananom_prares_rec;
        $dati['Ricite_tab'] = $Ricite_tab;
        $dati['Ricite_tab_da_filtrare'] = $Ricite_tab_da_filtrare;
        $dati['Ricite_rec'] = $Ricite_rec;
        $dati['Proges_rec'] = $ProGes_rec;
        $dati['Propas_rec'] = $ProPas_rec;
        $dati['Ricdag_tab'] = $Ricdag_tab;
        $dati['Dagset_tab'] = $Dagset_tab;
        $dati['Ricdag_tab_totali'] = $Ricdag_tab_totali;
        $dati['Ricdag_rec_codFis'] = $Ricdag_rec_codFis;
        $dati['Ricdag_tab_CFMappati'] = $Ricdag_tab_CFMappati;
        $dati['Ricdoc_tab'] = $Ricdoc_tab;
        $dati['Ricdoc_tab_tot'] = $Ricdoc_tab_tot;
        $dati['Ricdag_recFiscale'] = $Ricdag_recFiscale;
        //$dati['Anacla_tab'] = $this->praLib->GetAnacla($Anapra_rec['PRATSP'], "sportello", true, $this->PRAM_DB);
        $dati['Anacla_tab'] = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANACLA ORDER BY CLADES", true);
        $dati['Anaddo_tab'] = $this->praLib->GetAnaddo("", "", true, $this->PRAM_DB);
        //$dati['Ricmail_tab'] = $this->praLib->GetRicmail($ProPas_rec['PRONUM'], 'codice', true, $this->PRAM_DB);
        $dati['Riccontrolli_tab'] = $Riccontrolli_tab;
        $dati['Propas_rec'] = $propas_rec;
        $dati['PRAM_DB'] = $this->PRAM_DB;
        //  $dati['GAFIERE_DB'] = $this->GAFIERE_DB;




        /*
         * Variabili Evento
         */
//        include_once (ITA_FRONTOFFICE_ROOT . '/PRATICHE_italsoft/praLibEventi.class.php');
//        /* @var $praLibEventi praLibEventi */
//        $praLibEventi = new praLibEventi();
////        $dati['Oggetto'] = $praLibEventi->getOggetto($this->PRAM_DB, $Anapra_rec, $dati['Proric_rec']['RICEVE']);
//        $dati['Oggetto'] = $praLibEventi->getOggettoProric($this->PRAM_DB, $dati['Proric_rec']);
//        $dati['DescrizioneEvento'] = $praLibEventi->getDescrizioneEvento($this->PRAM_DB, $dati['Proric_rec']['RICEVE']);
        //
        // Preparazioni dizionario per compilazione valori di default
        //

//
//        $praVar = new praVars();
//        $praVar->setPRAM_DB($this->PRAM_DB);
//        $praVar->setDati($dati);
//        $praVar->loadVariabiliBaseRichiesta();
//        $praVar->loadVariabiliGenerali();
//        $praVar->loadVariabiliEsibente();
//        $praVar->loadVariabiliDistinta("");
//        $praVar->loadVariabiliTariffe();
//        $praVar->loadVariabiliAmbiente();
//
//        //
//        // Creazione del vettore Navigatore
//        //
//        //$datiNavigatore = $this->praLib->filtraPassi($Ricite_tab_da_filtrare, 7, $dati['seq'], $praVar);
//        //$datiNavigatore = $this->praLib->filtraPassi($dati, 7, $dati['seq'], $praVar);
        $datiNavigatore = $this->filtraPassi($dati, 7, $seq, $praVar);
        if (!$datiNavigatore) {
            return;
        }

//
//
//        $in_keys = array();
//        foreach ($datiNavigatore['Ricite_tab_new'] as $key => $Ricite_rec_new) {
//            $in_keys[] = $Ricite_rec_new['ITEKEY'];
//        }
//        $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $Ricite_rec_new['RICNUM'] . "' AND ITEKEY IN ('" . implode("','", $in_keys) . "')";
//        $datiNavigatore['Ricdag_tab_new'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//
//        $sql = "SELECT * FROM RICDOC WHERE DOCNUM = '" . $Ricite_rec_new['RICNUM'] . "' AND ITEKEY IN ('" . implode("','", $in_keys) . "')";
//        $datiNavigatore['Ricdoc_tab_new'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
//
//        $praVar2 = new praVars();
//        $praVar2->setPRAM_DB($this->PRAM_DB);
//        $praVar2->setDati($dati);
//        $praVar2->loadVariabiliDistinta("");
//
//
//
//        //
//        // Rilevo e valuto l'espressione di obbligatorietà per tutti i passi estratti da cambiare gli OBL CON PROOBL
//        //
        foreach ($dati['Ricite_tab'] as $key => $dati_ricite_rec) {
            if ($dati_ricite_rec['PROOBL'] == 1) {
                if ($dati_ricite_rec['ITEOBE']) {
                    $dati['Ricite_tab'][$key]['PROOBL'] = ($this->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                } else {
                    $dati['Ricite_tab'][$key]['PROOBL'] = 1;
                }
            } else {
                $dati['Ricite_tab'][$key]['PROOBL'] = 0;
            }
        }
        foreach ($datiNavigatore['Ricite_tab_new'] as $key => $dati_ricite_rec) {
            if ($dati_ricite_rec['PROOBL'] == 1) {
                if ($dati_ricite_rec['ITEOBE'] != '') {
                    $datiNavigatore['Ricite_tab_new'][$key]['PROOBL'] = ($this->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                } else {
                    $datiNavigatore['Ricite_tab_new'][$key]['PROOBL'] = 1;
                }
            } else {
                $datiNavigatore['Ricite_tab_new'][$key]['PROOBL'] = 0;
            }
        }
//
        foreach ($datiNavigatore['Ricite_tab'] as $key => $dati_ricite_rec) {
            if ($dati_ricite_rec['PROOBL'] == 1) {
                if ($dati_ricite_rec['ITEOBE'] != '') {
                    $datiNavigatore['Ricite_tab'][$key]['PROOBL'] = ($this->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                } else {
                    $datiNavigatore['Ricite_tab'][$key]['PROOBL'] = 1;
                }
            } else {
                $datiNavigatore['Ricite_tab'][$key]['PROOBL'] = 0;
            }
        }
//
        if ($dati['Ricite_rec']['PROOBL'] == 1) {
            if ($dati['Ricite_rec']['ITEOBE']) {
                $dati['Ricite_rec']['PROOBL'] = ($this->ctrExpression($dati['Ricite_rec'], $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
            } else {
                $dati['Ricite_rec']['PROOBL'] = 1;
            }
        } else {
            $dati['Ricite_rec']['PROOBL'] = 0;
        }
        //
        // Conteggio passi Obbligatori/Eseguiti
        //
        $PassiObbligatori = array();
        $almenoUno = false;

        foreach ($Ricite_tab as $key => $passoDomanda) {
            if ($passoDomanda['PROQST'] || $passoDomanda['PROOBL']) {
                $almenoUno = true;
                break;
            }
        }
        if ($almenoUno == true) {
            $array = $datiNavigatore['Ricite_tab_new'];
        } else {
            $array = $Ricite_tab;
        }
        //
        // Controllo se tutti i passi obbligatori sono stai fatti
        //
        foreach ($array as $obbligatorio) {
            //if ($obbligatorio['RICOBL'] || $obbligatorio['ITEQST'] || $obbligatorio['ITEDAT']) {
            if ($obbligatorio['PROOBL']) {
                if ($obbligatorio['PROIRE'])
                    continue;
                $PassiObbligatori[] = $obbligatorio;
            }
        }

        //
        //Mi trovi i passi Obbligatori eseguiti
        //
        $OblEseguiti = array();
        foreach ($PassiObbligatori as $Eseguito) {
            //Un controllo con PROFIN?
            if ($Eseguito['PROOBL'] && $Eseguito['PROFIN']) {
                $OblEseguiti[] = $Eseguito;
            }//Vecchio controllo che non mi funziona
//            if (strpos($Proric_rec['RICSEQ'], "." . $Eseguito['PROSEQ'] . ".") !== false) {
//                $OblEseguiti[] = $Eseguito;
//            }
        }

        //
        //Mi trovo i passi obbligatori prima dell'asseverazione che non sono stati fatti
        //
        $PassiOblPrimaAss = array();
        foreach ($array as $obbligatorio) {
            //Un controllo con PROFIN?
            if ($obbligatorio['PROOBL'] && !$obbligatorio['PROFIN']) {
                $PassiOblPrimaAss[] = $obbligatorio;
            }

//Vecchio controllo che non mi funziona
//            if (($obbligatorio['PROOBL'] ) && $obbligatorio['PROSEQ'] < $dati['seq']) {
//                if (strpos($Proric_rec['RICSEQ'], "." . $obbligatorio['PROSEQ'] . ".") === false) {
//                    $PassiOblPrimaAss[] = $obbligatorio;
//                }
//            }
        }

        $countObl = count($PassiObbligatori);
        $countEsg = count($OblEseguiti);
        $countOblNonFatti = count($PassiOblPrimaAss);
//        $dati['Navigatore'] = $datiNavigatore;
//        $dati['CartellaAllegati'] = $this->praLib->getCartellaAttachmentPratiche($dati['Ricite_rec']['RICNUM']);
//        $dati['CartellaRepository'] = $this->praLib->getCartellaRepositoryPratiche($dati['Ricite_rec']['RICNUM']);
//        $dati['CartellaMail'] = $this->praLib->getCartellaRepositoryPratiche($dati['Ricite_rec']['RICNUM'] . "/mail");
//        $dati['CartellaTemporary'] = $this->praLib->getCartellaTemporaryPratiche($dati['Ricite_rec']['RICNUM']);
//        $dati['URLTemporary'] = $this->praLib->getTemporaryURL($dati['Ricite_rec']['RICNUM']);
        $dati['countObl'] = $countObl;
        $dati['countEsg'] = $countEsg;
        $dati['countOblNonFatti'] = $countOblNonFatti;
////        $dati['setuser'] = praMup::$param['setuser'];
//        $dati['ita-Npage'] = $_POST['Npage'];
//        $dati['ita-pageRows'] = $_POST['pageRows'];
        //
        // Lettura dati per infocamere
        //
//        $dati['dati_infocamere'] = $this->praLib->getDatiInfocamere($dati, $this->PRAM_DB);
        return $dati;
    }

    function ctrExpression($ricite_rec, $raccolta, $campoEspressione = 'ITEATE') {
        return $this->evalExpression($raccolta, $ricite_rec[$campoEspressione]);
    }

    function evalExpression($raccolta, $serExpression) {
        $espressione = '';
        $controlli = unserialize($serExpression);
        foreach ($controlli as $controllo) {
            switch ($controllo['OPERATORE']) {
                case 'AND':
                    $espressione = $espressione . ' && ';
                    break;
                case 'OR':
                    $espressione = $espressione . ' || ';
                    break;
                default:
                    break;
            }

            if (substr($controllo['CAMPO'], 0, 1) === '#') {
                $controllo['CAMPO'] = substr($controllo['CAMPO'], 1);
                $espressione = $espressione . $controllo['CAMPO'];
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . $controllo['VALORE'];
            } else {
                $espressione = $espressione . '$raccolta[\'' . $controllo['CAMPO'] . '\']';
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . '\'' . $controllo['VALORE'] . '\'';
            }
        }
        $espressione = $espressione . '';
        eval('$ret = (' . $espressione . ');');
        return $ret;
    }

    public function filtraPassi($dati, $davedere, $currSeq, $praVar) {
        $passo_accorpamento_pratica_unica = false;
        $Proric_rec = $dati['Proric_rec'];
        $Ricite_tab = $dati['Ricite_tab_da_filtrare'];
        $coppie = array();
        $coppie[] = array('offset' => 0, 'length' => 0);
        $fl_esci = false;
        $salta = false;
        $praVar_domande = unserialize(serialize($praVar));
        foreach ($Ricite_tab as $key => $Ricite_rec) {
            if ($Ricite_rec['ITERICSUB'] == 1) {
                $passo_accorpamento_pratica_unica = true;
            }

            $ret_attivo = false;
            $PassoFatto = false;
            if ($salta === $Ricite_rec['ITEKEY']) {
                $salta = false;
                $coppie[] = array('offset' => $key, 'length' => 0);
            }
            if ($salta === false) {
                if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['PROSEQ'] . chr(46)) !== false) {
                    $PassoFatto = true;
                }
                if (($Ricite_rec['ITEQSTDAG'] == 1 && !$PassoFatto && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) || ($Ricite_rec['ITEIRE'] == 1 && $Ricite_rec['ITEQSTDAG'] == 1 && $ret_attivo == true)) {
                    $coppie[count($coppie) - 1]['length'] +=1;
                    $fl_esci = true;
                    break;
                }

                if ($Ricite_rec['PROQST'] == 1 && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) {
                    switch ($Ricite_rec['PRORIS']) {
                        case 'SI' :
                            $coppie[count($coppie) - 1]['length'] +=1;
                            if ($Ricite_rec['PROVPA']) {
                                $salta = $Ricite_rec['PROVPA'];
                            }
                            break;
                        case 'NO' :
                            $coppie[count($coppie) - 1]['length'] +=1;
                            if ($Ricite_rec['PROVPN']) {
                                $salta = $Ricite_rec['PROVPN'];
                            }
                            break;
                        default:
                            $coppie[count($coppie) - 1]['length'] += 1;
                            if ($ret_attivo) {
                                $fl_esci = true;
                            }
                            break;
                    }
                    if ($fl_esci == true) {
                        break;
                    }
                } else {
                    if ($Ricite_rec['PROVPA'] && $Ricite_rec['CLTOFF'] == 0 && $ret_attivo == true) {
                        $coppie[count($coppie) - 1]['length'] +=1;
                        $salta = $Ricite_rec['PROVPA'];
                    } else {
                        $coppie[count($coppie) - 1]['length'] += 1;
                    }
                }
            }
        }

        //
        $praVar_domande = null;
        //
        $Ricite_tab_new = array();
        foreach ($coppie as $value) {
            $Ricite_tab_new = array_merge($Ricite_tab_new, array_slice($Ricite_tab, $value['offset'], $value['length'], false));
        }


        /*
         * Controllo passi di chiusura intermedi
         */
        $keyPassoFinale = false;
        foreach ($Ricite_tab_new as $keyPasso => $ricite_rec) {
            if ($keyPassoFinale && $keyPassoFinale < $keyPasso) {
                unset($Ricite_tab_new[$keyPasso]);
                continue;
            }

            if ($ricite_rec['PROZIP'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }

            if ($ricite_rec['PROIRE'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }

            /*
             * Finalizzazione su passo incorporazione a pratica unica
             */
            if ($ricite_rec['ITERICSUB'] == 1 && $ricite_rec['CLTOFF'] == 0) {
                $keyPassoFinale = $keyPasso;
                continue;
            }
        }

        /*
         * Verifico la presenza del passo Accorpamento
         * Pratica Unica nei passi attivi
         */
        if ($passo_accorpamento_pratica_unica === true) {
            $passo_accorpamento_attivo = false;

            foreach ($Ricite_tab_new as $keyPasso => $ricite_rec) {
                if ($ricite_rec['ITERICSUB'] == 1) {
                    $passo_accorpamento_attivo = true;
                }
            }
        }

        $Ricite_tab_appoggio = array();

//
// Carico dizionario variabili base
//
        foreach ($Ricite_tab_new as $key1 => $Ricite_rec_new) {
            if ($Ricite_rec_new['CLTOFF'] == 1) {
                continue;
            }
            $Ricite_tab_appoggio[] = $Ricite_tab_new[$key1];
        }

        $Ricite_tab_new = $Ricite_tab_appoggio;
        $tot = count($Ricite_tab_new);
        $spazio = intval($davedere / 2);
        $p = false;

        if (!$currSeq) {
            $currSeq = $Ricite_tab_new[0]['PROSEQ'];
        }

        foreach ($Ricite_tab_new as $Key => $Ricite_rec) {
            if ($Ricite_rec['PROSEQ'] == $currSeq) {
                if ($p == false) {
                    $p = $Key;
                }
            }
        }



        if ($p !== false) {
            $fin = ($p + $spazio >= $tot) ? $tot - 1 : (($p + $spazio < $davedere - 1) ? $davedere - 1 : $p + $spazio);
            $ini = $fin + 1 - $davedere;
            if ($ini < 0)
                $ini = 0;
            $per = $davedere;
            $next = ($p == $tot - 1) ? false : true;
            $prev = ($p == 0) ? false : true;
            return array(
                'Ricite_tab' => array_slice($Ricite_tab_new, $ini, $per, true),
                'Ricite_tab_new' => $Ricite_tab_new,
//                'Dizionario_Richiesta_new' => $praVar->getVariabiliRichiesta(),
                'Posizione' => $p,
                'Quanti' => $tot,
                'Inizio' => $ini,
                'Fine' => $fin,
                'Davedere' => $davedere,
                'Successivo' => $next,
                'Precedente' => $prev,
                'Passo_Accorpamento_Presente' => $passo_accorpamento_pratica_unica,
                'Passo_Accorpamento_Attivo' => $passo_accorpamento_attivo
            );
        } else {
//            output::$html_out = $this->praErr->parseError(__FILE__, 'E0021', "Impossibile andare al passo " . $_POST['seq'] . " della pratica n. " . $Ricite_rec['RICNUM'], __CLASS__);
            return false;
        }
    }

}
