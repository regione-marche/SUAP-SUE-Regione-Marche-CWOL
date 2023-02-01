<?php

/**
 * Description of praLibSostituzioni
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';

class praLibDati {

    private $praLib;
    private $PRAM_DB;
    private $GAFIERE_DB;
    private $ITAFRONTOFFICE_DB;
    private $ITALWEB_DB;
    private $workDate;
    private $errCode;
    private $errMessage;

    public static function getInstance($praLib) {
        $obj = new praLibDati();
        $obj->setPraLib($praLib);
        $obj->setPRAM_DB(ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte()));
        $obj->setGAFIERE_DB(ItaDB::DBOpen('GAFIERE', frontOfficeApp::getEnte()));
        $obj->setITAFRONTOFFICE_DB(ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte()));
        $obj->setITALWEB_DB(ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte()));
        $obj->setWorkDate(date('Ymd'));
        return $obj;
    }

    function getPRAM_DB() {
        return $this->PRAM_DB;
    }

    function getGAFIERE_DB() {
        return $this->GAFIERE_DB;
    }

    function getITAFRONTOFFICE_DB() {
        return $this->ITAFRONTOFFICE_DB;
    }

    function getITALWEB_DB() {
        return $this->ITALWEB_DB;
    }

    function setPRAM_DB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    function setGAFIERE_DB($GAFIERE_DB) {
        $this->GAFIERE_DB = $GAFIERE_DB;
    }

    function setITAFRONTOFFICE_DB($ITAFRONTOFFICE_DB) {
        $this->ITAFRONTOFFICE_DB = $ITAFRONTOFFICE_DB;
    }

    function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
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

    function getWorkDate() {
        return $this->workDate;
    }

    function setWorkDate($workDate) {
        $this->workDate = $workDate;
    }

    public function prendiDati($ricnum = '', $seq = '', $simulaCanc = '', $ignoraPassi = false) {
        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();

        $this->setErrCode(0);
        $this->setErrMessage('');

        /*
         * Vettore di salvataggio mappa dei dati
         */
        $dati = array();

        /*
         * Codice Richiesta
         */
        if (!$ricnum) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura Richiesta N." . $ricnum . " Fallita.");
            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0015', "Lettura Richiesta N." . $ricnum . " Fallita.", __CLASS__);
            return false;
        }

        /*
         * Codice Fiscale di chi si è collegato
         */
        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'admin' && strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'pitaprotec') {
            if ($datiUtente['fiscale']) {
                $dati['Fiscale'] = $datiUtente['fiscale'];
            }
        } else {
            $fiscale = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT RICFIS FROM PRORIC WHERE RICNUM=$ricnum", false);
            $dati['Fiscale'] = $fiscale['RICFIS'];
        }
        if ($dati['Fiscale'] == "") {
            $this->setErrCode(-2);
            $this->setErrMessage("L'utente <b>" . $datiUtente['username'] . "</b> non è adatto per accedere alla compilazione on-line perchè manca il codice fiscale nel suo profilo.");
            return false;
        }

        /*
         * Controllo indirizzi mail richiedente
         */
        $mailRich = $this->praLib->GetMailRichiedente();
        if (!$mailRich) {
            $this->setErrCode(-2);
            $this->setErrMessage("L'utente <b>" . $datiUtente['username'] . "</b> non può attivare richieste on-line data la mancanza del suo indirizzo e-mail.");
            return false;
        }

        $ricnum_formatted = (int) substr($ricnum, 4) . '/' . substr($ricnum, 0, 4);

        //@TODO DA VERIFICARE
        if ($simulaCanc && is_array($simulaCanc)) {
            foreach ($simulaCanc as $key => $value) {
                $NewSequenza_pre = str_replace(chr(46) . $value . chr(46), "", $Proric_rec['RICSEQ']);
                $NewSequenza = preg_replace('/\s+/', '', $NewSequenza_pre);
                $Proric_rec['RICSEQ'] = $NewSequenza;
            }
        }

        /*
         * Lettura Proric_rec (Dati di Testata Richiesta)
         */
        $ricsoggetti_rec = array();
        $Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $ricnum . "' AND RICFIS='" . $dati['Fiscale'] . "'", false);
        if (!$Proric_rec) {
            $ricsoggetti_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSOGGETTI WHERE UPPER(SOGRICFIS) = '" . strtoupper($dati['Fiscale']) . "' AND SOGRICNUM = '$ricnum'", false);
            if (!$ricsoggetti_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Record soggetto non trovato per la richiesta n. $ricnum e il codice fiscale " . $dati['Fiscale']);
                return false;
            }

            $Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $ricnum . "'", false);
            if (!$Proric_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura Richiesta N." . $ricnum . " su PRORIC Fallita.");
                return false;
            }
        }

        $dati['Proric_rec'] = $Proric_rec;

        /*
         * Controllo se la richiesta è offline per manutenzione
         */
        if ($Proric_rec['RICSTA'] == "OF") {
            $this->setErrCode(-2);
            $this->setErrMessage("La richiesta n. " . $ricnum_formatted . "  è momentaneamente non disponibile per manutenzione.");

            //output::addAlert("La pratica n. " . $Proric_rec['RICNUM'] . "  è momentaneamente non disponibile per manutenzione.", 'Attenzione!', 'warning');
            return false;
        }

        /*
         * Codice Procedimento
         */
        $dati['Codice'] = $Proric_rec['RICPRO'];
        $dati['CodiceEvento'] = $Proric_rec['RICEVE'];

        /*
         * Da inserire il campo su DB
         */
        $dati['IdEvento'] = $Proric_rec['RICROWIDEVE'];

        /*
         * Ruolo
         */
        $dati['ruolo'] = "";

        /*
         * Flag indicativi della modalità di ingresso: consultazione/modifica
         */
        if (($Proric_rec['RICSTA'] == '99' || $Proric_rec['RICSTA'] == '81') && strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'admin' && strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'pitaprotec') {
            $dati['Modifica'] = true;
            $dati['Consulta'] = false;
            if ($ricsoggetti_rec) {
                $dati['Modifica'] = false;
                $dati['Consulta'] = true;
            } else {
                if ($this->praLib->isEnableScadenza()){
                    if ($this->praLib->getDataScadenza($Proric_rec) < date('Ymd') && $Proric_rec['RICFORZAINVIO'] == 0 ) {
                        $dati['Modifica'] = false;
                        $dati['Consulta'] = true;
                    }
                }
            }
        } else {
            $dati['Consulta'] = true;
            $dati['ConsultaMessaggio'] = 'La richiesta non è modificabile perchè già inviata.';
            $dati['Modifica'] = false;
        }

        if ($Proric_rec['RICRUN'] != '') {
            $Proric_rec_padre = $this->praLib->GetProric($Proric_rec['RICRUN'], 'codice', $this->PRAM_DB);
            $Ricite_rec_padre = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICRUN'] . "' AND ITERICUNI = '1'", false);

            if ($Ricite_rec_padre && $this->praLib->checkEsecuzionePasso($Proric_rec_padre, $Ricite_rec_padre)) {
                $praticaPadre = sprintf('%s/%s', substr($Proric_rec['RICRUN'], 4), substr($Proric_rec['RICRUN'], 0, 4));

                $dati['Consulta'] = true;
                $dati['ConsultaMessaggio'] = "La richiesta non &egrave; modificabile in quanto accorpata alla richiesta $praticaPadre.";
                $dati['ConsultaMessaggio'] .= "<br>Annulla il passo di accorpamento della richiesta $praticaPadre per apportare modifiche.";
                $dati['Modifica'] = false;
            }
        }

        /*
         * Dati Anagrafica Procedimento
         */
        $Anapra_rec = $this->praLib->GetAnapra($dati['Codice'], 'codice', $this->PRAM_DB);
        if (!$Anapra_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Record procedimento " . $dati['Codice'] . " mancante su ANAPRA");
            //output::$html_out = $this->praErr->parseError(__FILE__, 'E0017', "Record procedimento " . $dati['Codice'] . " mancante su ANAPRA", __CLASS__);
            return false;
        }

        /*
         * Dati Anagrafica Evento solo se utilizzato
         */
        $Anaeventi_rec = array();
        if ($dati['CodiceEvento']) {
            $Anaeventi_rec = $this->praLib->GetAnaeventi($dati['CodiceEvento'], 'codice', $this->PRAM_DB);
            if (!$Anaeventi_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Record evento " . $dati['Codice'] . " mancante su ANAEVENTI");
                //output::$html_out = $this->praErr->parseError(__FILE__, 'E0021', "Record evento " . $dati['Codice'] . " mancante su ANAEVENTI", __CLASS__);
                return false;
            }
        }

        /*
         * Dati Tipologie procedimento
         */
        $Anatip_rec = $this->praLib->GetAnatip($Proric_rec['RICTIP'], 'codice', $this->PRAM_DB);

        /*
         * Dati Settore
         */
        $Anaset_rec = $this->praLib->GetAnaset($Proric_rec['RICSTT'], 'codice', $this->PRAM_DB);

        /*
         * Dati Attivita
         */
        $Anaatt_rec = $this->praLib->GetAnaatt($Proric_rec['RICATT'], 'codice', $this->PRAM_DB);

        /*
         * Dati Elenco Sportelli Aggregati Anagrafati
         */
        $Anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPAATT = 1 ORDER BY SPADES ASC", true);

        /*
         * Dati Sportello On-Line per la richiesta
         */
        $Anatsp_rec = $this->praLib->GetAnatsp($Proric_rec['RICTSP'], 'codice', $this->PRAM_DB);
        $Ananom_tspres_rec = $this->praLib->GetAnanom($Anatsp_rec['TSPRES'], 'codice', $this->PRAM_DB);

        /*
         * Dati Sportello Aggregato per la richiesta
         */
        $Anaspa_rec = $this->praLib->GetAnaspa($Proric_rec['RICSPA'], 'codice', $this->PRAM_DB);
        $Ananom_spares_rec = $this->praLib->GetAnanom($Anaspa_rec['SPARES'], 'codice', $this->PRAM_DB);

        /*
         * Dati Responsabile procedimento
         */
        $Ananom_prares_rec = $this->praLib->GetAnanom($Proric_rec['RICRES'], 'codice', $this->PRAM_DB);

        /*
         * Dati Elenco Passi della richeista già filtrati per ruolo (N.B.!! Ancora non filtrati per eventi domanda e condizioni speciali passo)
         */
        $Ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITERUO = '" . $datiUtente['ruolo'] . "' AND ITEPUB<>0 ORDER BY ITESEQ", true);
        foreach ($Ricite_tab as $ricite_rec) {
            /*
             * Effettua l'aggiornamento delle tariffe
             */
            $itelis_rec = $this->praLib->GetTariffaPasso($ricite_rec, $this->PRAM_DB);
        }


        if ($ignoraPassi === false) {
            /*
             * Creazione del vettore Navigatore
             */
            if ($Ricite_tab) {
                $Ricite_tab_da_filtrare = array();
                foreach ($Ricite_tab as $key => $Ricite_rec) {
                    $fl_off = $fl_obl = 0;
                    $clt_ope_fo = '';
                    if ($Ricite_rec['ITECLT']) {
                        $Praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['ITECLT'] . "'", false);
                        if ($Praclt_rec) {
                            if ($Praclt_rec['CLTOFF'] == 1) {
                                $fl_off = 1;
                            }
                            if ($Praclt_rec['CLTOBL'] == 1) {
                                $fl_obl = 1;
                            }
                            if ($Praclt_rec['CLTOPEFO']) {
                                $clt_ope_fo = $Praclt_rec['CLTOPEFO'];
                            }
                        }
                    }
                    $Ricite_rec['CLTOFF'] = $fl_off;
                    $Ricite_rec['CLTOBL'] = $fl_obl;
                    $Ricite_rec['CLTOPEFO'] = $clt_ope_fo;
                    $Ricite_tab_da_filtrare[] = $Ricite_rec;
                }
            }


            //@TODO: separare a richiesta i controlli su dati per il corretto funzionamento fo dal resto.

            $Ricite_tab_tmp = array();
            foreach ($Ricite_tab as $key => $Ricite_rec) {
                $fl_off = false;
                if ($Ricite_rec['ITECLT']) {
                    $Praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['ITECLT'] . "'", false);
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
            if (!$Ricite_tab) {
                $this->setErrCode(-1);
                $this->setErrMessage("Passi non trovati per la Pratica n. " . $Proric_rec['RICNUM'] . "  con Procedimento n. " . $dati['Codice']);
//          output::$html_out = $this->praErr->parseError(__FILE__, 'E0018', "Passi non trovati per la Pratica n. " . $Proric_rec['RICNUM'] . "  con Procedimento n. " . $dati['Codice'], __CLASS__);
                return false;
            }

            /*
             * Dati Ricite_rec Passo corrente in base alla sequenza
             */
            if ($seq) {
                $Ricite_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "'  AND ITESEQ = '" . $seq . "'", false);
            } else {
                $Ricite_rec = $Ricite_tab[0];
                $dati['seq'] = $Ricite_rec['ITESEQ'];
            }
            if (!$Ricite_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Passo con sequenza " . $dati['seq'] . " mancante nella Pratca n. " . $Proric_rec['RICNUM']);

                //output::$html_out = $this->praErr->parseError(__FILE__, 'E0019', "Passo con sequenza " . $dati['seq'] . " mancante nella Pratca n. " . $Proric_rec['RICNUM'], __CLASS__);
                return false;
            }
            $dati['seq'] = $Ricite_rec['ITESEQ'];

            /*
             * Per il passo corrente mi trovo le espressioni per i controlli associati
             */
            $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' ORDER BY SEQUENZA", true);
            if (!$Riccontrolli_tab) {
                $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITECTP'] . "' ORDER BY SEQUENZA", true);
            }


            /*
             * Info su trasmissione a infocamere
             */
            $Ricite_rec_infocamere = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITEZIP<>0", false);
            $dati['Note_Infocamere'] = unserialize($Ricite_rec_infocamere['RICNOT']);

            /*
             * Estrazione dati Raccolta
             * $Ricdag_tab = Records relativi alla raccolta
             * Serve per la gestione dei passi raccolta dati
             */
            $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "' ORDER BY DAGSEQ";

            $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

            /*
             * Cambiato controllo valorizzazione ricdat per bug se non veniva valorizzato nessun campo aggiuntivo (22/12/2016)
             */
            if ($this->request['event'] == "submitRaccolta" || $this->request['event'] == "submitRaccoltaMultipla") {
                $dati['ricdat'] = 1;
            } elseif ($this->request['event'] == "annullaRaccolta") {
                $dati['ricdat'] = 0;
            }
            if (strpos($Proric_rec['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
                $dati['ricdat'] = 0;
            } else {
                $dati['ricdat'] = 1;
            }
        }

        /*
         * Mi trovo tutti i dati aggiuntivi della richiesta
         */
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "'";

        $Ricdag_tab_totali = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        /*
         * Mi trovo il record del dato aggiuntivo tipizzato del codice fiscale
         */
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "' AND DAGTIP = 'Codfis_InsProduttivo'";

        $Ricdag_rec_codFis = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        /*
         * Mi trovo il record del dato aggiuntivo tipizzato del codice fiscale del legale rappresentante
         */
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "' AND DAGTIP = 'FiscaleLegale'";

        $Ricdag_rec_codFisLegale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

        /*
         * Mi trovo i record dei dati aggiuntivi che finiscono per _CFI e _PIVA per attivare controllo sul rapporto
         * Escudo ovviamente quello dell'esibente
         */
        $sql = "SELECT
                        *
                    FROM
                        RICDAG
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "' AND DAGKEY<>'ESIBENTE_CODICEFISCALE_CFI' AND DAGKEY<>'DICHIARANTE_CODICEFISCALE_CFI' AND DAGKEY<>'PREPOSTO_CODICEFISCALE_CFI' AND (DAGKEY LIKE '%_CFI%' OR DAGKEY LIKE '%_PIVA%')";

        $Ricdag_tab_CFMappati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        /*
         * Dato aggiuntivo codice fiscale
         */
        $ricnumCF = $ricnum;
        $codProcCF = $dati['Codice'];
        if ($Proric_rec['RICRPA']) {
            $Proric_rec_padre = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $Proric_rec['RICRPA'] . "' AND RICFIS='" . $dati['Fiscale'] . "'", false);
            if (!$Proric_rec_padre) {

                $ricsoggetti_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSOGGETTI WHERE UPPER(SOGRICFIS) = '" . strtoupper($dati['Fiscale']) . "' AND SOGRICNUM = '" . $Proric_rec['RICRPA'] . "'", false);
                if (!$ricsoggetti_rec) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Record soggetto non trovato per la richiesta n. " . $Proric_rec['RICRPA'] . " e il codice fiscale " . $dati['Fiscale']);
                    return false;
                }

                $Proric_rec_padre = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICNUM='" . $Proric_rec['RICRPA'] . "'", false);
                if (!$Proric_rec_padre) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("N. Lettura Richiesta Padre N." . $Proric_rec['RICRPA'] . " Fallita.");
                    return false;
                }
            }
            $ricnumCF = $Proric_rec_padre['RICNUM'];
            $codProcCF = $Proric_rec_padre['RICPRO'];
        }
        $sql = "SELECT * FROM RICDAG WHERE ITECOD = '$codProcCF' AND DAGNUM = '$ricnumCF' AND DAGKEY = 'TECPRG_CODICEFISCALE_CFI' AND RICDAT<>''";
        $Ricdag_recFiscale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Ricdag_recFiscale) {
            $sql = "SELECT * FROM RICDAG WHERE ITECOD = '$codProcCF' AND DAGNUM = '$ricnumCF' AND DAGKEY = 'DICHIARANTE_CODICEFISCALE_CFI' AND RICDAT<>''";
            $Ricdag_recFiscale = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        }

        /*
         * Allegati al passo
         */
        $Ricdoc_tab = array();
        if ($ignoraPassi === false) {

            $sql = "SELECT
                        *
                    FROM
                        RICDOC
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DOCNUM = '" . $ricnum . "'";

            $Ricdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        }

        /*
         * Tabella Allegati totale
         */
        $sql = "SELECT
                        *
                    FROM
                        RICDOC
                    WHERE
                        ITECOD = '" . $dati['Codice'] . "' AND DOCNUM = '" . $ricnum . "'";

        $Ricdoc_tab_tot = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        /*
         * Decodifica dei dati di ruolo proveninti dai parametri di chiamata a PraMup
         */
        switch ($datiUtente['ruolo']) {
            case "0001" :
                $dati['DescRuolo'] = 'ESIBENTE';
                break;
            case "0002" :
                $dati['DescRuolo'] = 'PROCURATORE';
                break;
            case "0003" :
                $dati['DescRuolo'] = 'AGENZIE';
                break;
        }

        $sqlUltSeq = "SELECT MAX(ITESEQ) AS ULTSEQ FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "'";
        $ultSeq_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlUltSeq, true);
        $seqlen = 3;
        if ($ultSeq_rec) {
            $seqlen = strlen($ultSeq_rec[0]['ULTSEQ']);
        }
        if ($seqlen < 3)
            $seqlen = 3;

        /*
         * Query dagset per raccolta dati multipla
         */
        $Dagset_tab = array();
        if ($ignoraPassi == false) {
            $sqlDagset = "SELECT
                        DAGSET
                    FROM
                        RICDAG
                    WHERE
                        ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "'
                    GROUP BY DAGSET";

            $Dagset_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlDagset, true);
        }

        /*
         * Record passo articolo BO
         */
        if ($Proric_rec['PROPAK']) {
            $propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PROPAK = '" . $Proric_rec['PROPAK'] . "'", false);
        }

        $praLibAcl = new praLibAcl();

        /*
         * Impacchettamento dati precedentemente estratti su variabili di appoggio
         */
        $dati['Browser'] = frontOfficeApp::getBrowser();
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
        $dati['Praclt_rec'] = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRACLT WHERE CLTCOD = '" . $Ricite_rec['ITECLT'] . "'", false);
        $dati['Proric_rec'] = $Proric_rec;
        $dati['Ricdag_tab'] = $Ricdag_tab;
        $dati['Dagset_tab'] = $Dagset_tab;
        $dati['Ricdag_tab_totali'] = $Ricdag_tab_totali;
        $dati['Ricdag_rec_codFis'] = $Ricdag_rec_codFis;
        $dati['Ricdag_rec_codFisLegale'] = $Ricdag_rec_codFisLegale;
        $dati['Ricdag_tab_CFMappati'] = $Ricdag_tab_CFMappati;
        $dati['Ricdag_tab_Esibente'] = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$ricnum' AND DAGKEY LIKE 'ESIBENTE%'", true);
        $dati['Ricdoc_tab'] = $Ricdoc_tab;
        $dati['Ricdoc_tab_tot'] = $Ricdoc_tab_tot;
        $dati['Ricdag_recFiscale'] = $Ricdag_recFiscale;
        //$dati['Anacla_tab'] = $this->praLib->GetAnacla($Anapra_rec['PRATSP'], "sportello", true, $this->PRAM_DB);
        $dati['Anacla_tab'] = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANACLA ORDER BY CLADES", true);
        $dati['Anaddo_tab'] = $this->praLib->GetAnaddo("", "", true, $this->PRAM_DB);
        $dati['Ricmail_tab'] = $this->praLib->GetRicmail($Proric_rec['RICNUM'], 'codice', true, $this->PRAM_DB);
        $dati['Riccontrolli_tab'] = $Riccontrolli_tab;
        $dati['Propas_rec'] = $propas_rec;
        $dati['Ricsoggetti_tab'] = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = '$ricnum'", true);
        $sqlAcl = "SELECT 
                        *
                   FROM
                        RICACL
                   LEFT OUTER JOIN RICSOGGETTI ON RICACL.ROW_ID_RICSOGGETTI = RICSOGGETTI.ROW_ID
                   WHERE 
                        SOGRICNUM = '$ricnum' AND
                        SOGRICFIS = '" . $dati['Fiscale'] . "' AND
                        RICACL.RICACLTRASHED = 0 AND
                        RICACL.RICACLDATA_INIZIO <= '" . date('Ymd') . "' AND
                        RICACL.RICACLDATA_FINE >= '" . date('Ymd') . "'
                    ORDER BY RICACL.ROW_ID_PASSO";

        $dati['Ricacl_tab'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlAcl, true);

        $sqlAclTotali = "SELECT 
                            RICACL.*,
                            RICSOGGETTI.SOGRICFIS
                        FROM
                             RICACL
                        LEFT OUTER JOIN RICSOGGETTI ON RICACL.ROW_ID_RICSOGGETTI = RICSOGGETTI.ROW_ID
                        WHERE 
                             SOGRICNUM = '$ricnum' AND
                             RICACL.RICACLTRASHED = 0
                         ORDER BY RICACL.ROW_ID_PASSO";
        $dati['Ricacl_tab_totali'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlAclTotali, true);
        $dati['permessiPasso'] = $praLibAcl->getAclPasso($dati);
        $dati['PRAM_DB'] = $this->PRAM_DB;
        $dati['GAFIERE_DB'] = $this->GAFIERE_DB;
        $dati['ITAFRONTOFFICE_DB'] = $this->ITAFRONTOFFICE_DB;
        $dati['ITALWEB_DB'] = $this->ITALWEB_DB;
        $dati['Scaduta'] = $this->praLib->isRichiestaScaduta($dati['Proric_rec']);
        if (!$dati['Scaduta']){
            $dati['passiDisponibili'] = $praLibAcl->getPassiDisponibili($dati);
        }

        /*
         * Sql Fiere
         */
        $sqlFiere = "SELECT
                        FIERE.*,
                        ANAFIERE.FIERA AS NOMEFIERA
                     FROM 
                        FIERE
                     INNER JOIN 
                        ANAFIERE ON ANAFIERE.TIPO=FIERE.FIERA
                     WHERE
                        FIERE.DECENNALE = 0 AND
                        FIERE.BLOCCAPUBB = 0 AND
                        FIERE.BANDO = 0 AND
                        FIERE.DATATERMINE >= $this->workDate";
        $dati['Anafiere_tab'] = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlFiere, true);

        /*
         * Sql Fiere Bandi
         */
        $sqlFiere = "SELECT
                        FIERE.*,
                        ANAFIERE.FIERA AS NOMEFIERA
                     FROM 
                        FIERE
                     INNER JOIN 
                        ANAFIERE ON ANAFIERE.TIPO=FIERE.FIERA
                     WHERE
                        FIERE.DECENNALE = 1 AND
                        FIERE.BANDO = 1 AND
                        FIERE.BLOCCAPUBB = 0 AND
                        FIERE.DATATERMINE >= $this->workDate";
        $dati['BandiFiere_tab'] = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlFiere, true);

        /*
         * Sql Fiere Pluriennali Bandi
         */
        $sqlFiere = "SELECT
                        FIERE.*,
                        ANAFIERE.FIERA AS NOMEFIERA
                     FROM 
                        FIERE
                     INNER JOIN 
                        ANAFIERE ON ANAFIERE.TIPO=FIERE.FIERA
                     WHERE
                        FIERE.DECENNALE = 0 AND
                        FIERE.BANDO = 1 AND
                        FIERE.BLOCCAPUBB = 0 AND
                        FIERE.DATATERMINE >= $this->workDate";
        $dati['BandiFiereP_tab'] = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlFiere, true);

        /*
         * Sql Mercati Bandi
         */
        $sqlFiere = "SELECT
                        BANDIM.*,
                        ANAMERC.MERCATO AS NOMEFIERA
                     FROM 
                        BANDIM
                     INNER JOIN 
                        ANAMERC ON ANAMERC.CODICE=BANDIM.FIERA
                     WHERE
                        BANDIM.DECENNALE = 1 AND
                        BANDIM.BANDO = 1 AND
                        BANDIM.BLOCCAPUBB = 0 AND
                        BANDIM.DATATERMINE >= $this->workDate";
        $dati['BandiMercati_tab'] = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlFiere, true);

        /*
         * Sql Posteggi Isolati Bandi
         */
        $sqlFiere = "SELECT
                        BANDIM.*,
                        ANAMERC.MERCATO AS NOMEFIERA
                     FROM 
                        BANDIM
                     INNER JOIN 
                        ANAMERC ON ANAMERC.CODICE=BANDIM.FIERA
                     WHERE
                        BANDIM.DECENNALE = 0 AND
                        BANDIM.BANDO = 1 AND
                        BANDIM.BLOCCAPUBB = 0 AND
                        BANDIM.DATATERMINE >= $this->workDate";
        $dati['BandiPosteggiIsolati_tab'] = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlFiere, true);

        foreach ($dati['Ricdag_tab_totali'] as $ricdag_rec) {
            if ($ricdag_rec['DAGTIP'] == "Denom_Fiera") {
                $dati['ricdag_denom_fiera'] = $ricdag_rec;
            }
            if ($ricdag_rec['DAGTIP'] == "Posteggi_fiera") {
                $dati['ricdag_posto_fiera'] = $ricdag_rec;
            }
        }

        /*
         * Variabili Evento
         */
        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');
        /* @var $praLibEventi praLibEventi */
        $praLibEventi = new praLibEventi();
//        $dati['Oggetto'] = $praLibEventi->getOggetto($this->PRAM_DB, $Anapra_rec, $dati['Proric_rec']['RICEVE']);
        $dati['Oggetto'] = $praLibEventi->getOggettoProric($this->PRAM_DB, $dati['Proric_rec']);
        $dati['DescrizioneEvento'] = $praLibEventi->getDescrizioneEvento($this->PRAM_DB, $dati['Proric_rec']['RICEVE']);

        /*
         * Preparazioni dizionario per compilazione valori di default
         */

        $praVar = new praVars();
        $praVar->setPRAM_DB($this->PRAM_DB);
        $praVar->setGAFIERE_DB($this->GAFIERE_DB);
        $praVar->setDati($dati);
        $praVar->loadVariabiliBaseRichiesta();
        $praVar->loadVariabiliGenerali();
        $praVar->loadVariabiliEsibente();
        $praVar->loadVariabiliDistinta("");
        $praVar->loadVariabiliTariffe();
        $praVar->loadVariabiliAmbiente();
        $praVar->loadVariabiliTipiAggiuntiviBaseRichiesta();

        /*
         * Carico il dizionario Parent per pratiche uniche
         */
        if ($dati['Proric_rec']['RICRUN']) {
            $objTest = praLibDati::getInstance($this->praLib);
            $datiParent = $objTest->prendiDati($dati['Proric_rec']['RICRUN'], '', '', $ignoraPassi);
            $praVar->loadVariabiliParent($datiParent);
        }

        /*
         * Carico il dizionario Parent per pratiche collegate
         */
        if ($dati['Proric_rec']['RICRPA']) {
            $objTest = praLibDati::getInstance($this->praLib);
            $datiParent = $objTest->prendiDati($dati['Proric_rec']['RICRPA'], '', '', $ignoraPassi);
            $praVar->loadVariabiliParent($datiParent);
        }

        /*
         * Creazione del vettore Navigatore
         * 
         * @TODO: Testare correttamente e approfonditamente
         * 
         * 
         */
        if ($ignoraPassi === false) {
            if (!$this->caricaDatiNavigatore($dati, $seq, $praVar)) {
                $this->setErrCode(-2);
                $this->setErrMessage("PASSO NON DISPONIBILE.<br><br>A causa dei cambiamenti avvenuti nella Richiesta OnLine il passo: <span style=\"text-decoration:underline;\">" . $Ricite_rec['ITEDES'] . " </span>non è piu' disponibile.</span>");
                return false;
            }

            if ($dati['Navigatore']['Ricite_tab_new'][$dati['Navigatore']['Posizione']]['ITESEQ'] !== $seq) {
                $seq = $dati['Navigatore']['Ricite_tab_new'][$dati['Navigatore']['Posizione']]['ITESEQ'];

                $Ricite_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "'  AND ITESEQ = '" . $seq . "'", false);

                $dati['seq'] = $Ricite_rec['ITESEQ'];

                $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' ORDER BY SEQUENZA", true);
                if (!$Riccontrolli_tab) {
                    $Riccontrolli_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICCONTROLLI WHERE RICNUM = '" . $Proric_rec['RICNUM'] . "' AND ITEKEY = '" . $Ricite_rec['ITECTP'] . "' ORDER BY SEQUENZA", true);
                }

                $sql = "SELECT *
                        FROM RICDAG
                        WHERE ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "' ORDER BY DAGSEQ";

                $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

                $sql = "SELECT *
                        FROM RICDOC
                        WHERE ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DOCNUM = '" . $ricnum . "'";

                $Ricdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

                $sql = "SELECT DAGSET
                        FROM RICDAG
                        WHERE ITEKEY = '" . $Ricite_rec['ITEKEY'] . "' AND ITECOD = '" . $dati['Codice'] . "' AND DAGNUM = '" . $ricnum . "'
                        GROUP BY DAGSET";

                $Dagset_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

                $dati['Ricite_rec'] = $Ricite_rec;
                $dati['Ricdag_tab'] = $Ricdag_tab;
                $dati['Ricdoc_tab'] = $Ricdoc_tab;
                $dati['Riccontrolli_tab'] = $Riccontrolli_tab;
                $dati['Dagset_tab'] = $Dagset_tab;
            }
        }

        $dati['CartellaAllegati'] = $this->praLib->getCartellaAttachmentPratiche($ricnum);
        $dati['CartellaRepository'] = $this->praLib->getCartellaRepositoryPratiche($ricnum);
        $dati['CartellaMail'] = $this->praLib->getCartellaRepositoryPratiche($ricnum . "/mail");
        $dati['CartellaTemporary'] = $this->praLib->getCartellaTemporaryPratiche($ricnum);
        $dati['URLTemporary'] = $this->praLib->getTemporaryURL($ricnum);
        //
        // Lettura dati per infocamere
        //
        $dati['dati_infocamere'] = $this->praLib->getDatiInfocamere($dati, $this->PRAM_DB);
        return $dati;
    }

    public function caricaDatiNavigatore(&$dati, $seq, $praVar) {
        $datiNavigatore = array();
        if ($dati['Ricite_tab']) {
            $datiNavigatore = $this->praLib->filtraPassi($dati, 7, $seq, $praVar);
            if ($datiNavigatore === false) {
                $this->setErrCode($this->praLib->getErrCode());
                $this->setErrMessage($this->praLib->getErrMessage());
                return false;
            }
        }

        if ($datiNavigatore) {
            $in_keys = array();
            foreach ($datiNavigatore['Ricite_tab_new'] as $key => $Ricite_rec_new) {
                $in_keys[] = $Ricite_rec_new['ITEKEY'];
            }
            $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $Ricite_rec_new['RICNUM'] . "' AND ITEKEY IN ('" . implode("','", $in_keys) . "')";
            $datiNavigatore['Ricdag_tab_new'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

            $sql = "SELECT * FROM RICDOC WHERE DOCNUM = '" . $Ricite_rec_new['RICNUM'] . "' AND ITEKEY IN ('" . implode("','", $in_keys) . "')";
            $datiNavigatore['Ricdoc_tab_new'] = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

            $praVar2 = new praVars();
            $praVar2->setPRAM_DB($this->PRAM_DB);
            $praVar2->setGAFIERE_DB($this->GAFIERE_DB);
            $praVar2->setDati($dati);
            $praVar2->loadVariabiliDistinta("");

            /*
             * Rilevo e valuto l'espressione di obbligatorietà per tutti i passi estratti
             */
            foreach ($dati['Ricite_tab'] as $key => $dati_ricite_rec) {
                if ($dati_ricite_rec['ITEOBL'] == 1) {
                    if ($dati_ricite_rec['ITEOBE']) {
                        $dati['Ricite_tab'][$key]['RICOBL'] = ($this->praLib->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                    } else {
                        $dati['Ricite_tab'][$key]['RICOBL'] = 1;
                    }
                } else {
                    $dati['Ricite_tab'][$key]['RICOBL'] = 0;
                }
            }
            foreach ($datiNavigatore['Ricite_tab_new'] as $key => $dati_ricite_rec) {
                if ($dati_ricite_rec['ITEOBL'] == 1) {
                    if ($dati_ricite_rec['ITEOBE'] != '') {
                        $datiNavigatore['Ricite_tab_new'][$key]['RICOBL'] = ($this->praLib->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                    } else {
                        $datiNavigatore['Ricite_tab_new'][$key]['RICOBL'] = 1;
                    }
                } else {
                    $datiNavigatore['Ricite_tab_new'][$key]['RICOBL'] = 0;
                }
            }

            foreach ($datiNavigatore['Ricite_tab'] as $key => $dati_ricite_rec) {
                if ($dati_ricite_rec['ITEOBL'] == 1) {
                    if ($dati_ricite_rec['ITEOBE'] != '') {
                        $datiNavigatore['Ricite_tab'][$key]['RICOBL'] = ($this->praLib->ctrExpression($dati_ricite_rec, $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                    } else {
                        $datiNavigatore['Ricite_tab'][$key]['RICOBL'] = 1;
                    }
                } else {
                    $datiNavigatore['Ricite_tab'][$key]['RICOBL'] = 0;
                }
            }

            if ($dati['Ricite_rec']['ITEOBL'] == 1) {
                if ($dati['Ricite_rec']['ITEOBE']) {
                    $dati['Ricite_rec']['RICOBL'] = ($this->praLib->ctrExpression($dati['Ricite_rec'], $datiNavigatore['Dizionario_Richiesta_new']->getAlldataPlain("", "."), 'ITEOBE')) ? 1 : 0;
                } else {
                    $dati['Ricite_rec']['RICOBL'] = 1;
                }
            } else {
                $dati['Ricite_rec']['RICOBL'] = 0;
            }

            /*
             * Conteggio passi Obbligatori/Eseguiti
             */
            $PassiObbligatori = array();
            $almenoUno = false;
            foreach ($dati['Ricite_tab'] as $key => $passoDomanda) {
                if ($passoDomanda['ITEQST'] || $passoDomanda['ITEOBL']) {
                    $almenoUno = true;
                    break;
                }
            }
            if ($almenoUno == true) {
                $array = $datiNavigatore['Ricite_tab_new'];
            } else {
                $array = $dati['Ricite_tab'];
            }

            /*
             * Controllo se tutti i passi obbligatori sono stai fatti
             */
            foreach ($array as $obbligatorio) {
                if ($obbligatorio['RICOBL'] || $obbligatorio['CLTOBL'] || $obbligatorio['ITEQST']) {
                    if ($obbligatorio['ITEIRE'])
                        continue;
                    $PassiObbligatori[] = $obbligatorio;
                }
            }

            /*
             * Mi trovi i passi Obbligatori eseguiti
             */
            $OblEseguiti = array();
            foreach ($PassiObbligatori as $Eseguito) {
                if (strpos($dati['Proric_rec']['RICSEQ'], "." . $Eseguito['ITESEQ'] . ".") !== false) {
                    $OblEseguiti[] = $Eseguito;
                }
            }

            /*
             * Mi trovo i passi obbligatori prima dell'asseverazione che non sono stati fatti
             */
            $PassiOblPrimaAss = array();
            foreach ($array as $obbligatorio) {
                if (($obbligatorio['RICOBL'] || $obbligatorio['CLTOBL']) && $obbligatorio['ITESEQ'] < $dati['seq']) {
                    if (strpos($dati['Proric_rec']['RICSEQ'], "." . $obbligatorio['ITESEQ'] . ".") === false) {
                        $PassiOblPrimaAss[] = $obbligatorio;
                    }
                }
            }

            $countObl = count($PassiObbligatori);
            $countEsg = count($OblEseguiti);
            $countOblNonFatti = count($PassiOblPrimaAss);
            $dati['countObl'] = $countObl;
            $dati['countEsg'] = $countEsg;
            $dati['countOblNonFatti'] = $countOblNonFatti;
            $dati['Navigatore'] = $datiNavigatore;

            /*
             * Fine Creazione array di navigazione passi
             */
        }
        return true;
    }

    public function gestioneErrore($errCode, $errMessage, $praErr) {
        switch ($errCode) {
            case -1:
                return $praErr->parseError(__CLASS__, 'E000', $errMessage, __CLASS__);

            case -2:
                return $praErr->parseError(__CLASS__, 'E000', $errMessage, __CLASS__, $errMessage);
        }
    }

}
