<?php

/**
 *
 * Raccolta di funzioni per il web service open data
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft sRL
 * @license
 * @version    02.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');

class praWsOpenDataAgent {

    public $PRAM_DB;
    public $ITALWEB_DB;
    public $praLib;
    public $errCode;
    public $errMessage;
    private $mesi = array(
        '01' => 'Gennaio',
        '02' => 'Febbraio',
        '03' => 'Marzo',
        '04' => 'Aprile',
        '05' => 'Maggio',
        '06' => 'Giugno',
        '07' => 'Luglio',
        '08' => 'Agosto',
        '09' => 'Settembre',
        '10' => 'Ottobre',
        '11' => 'Novembre',
        '12' => 'Dicembre'
    );
    private $trimestri = array(
        'primo' => array(
            '01' => 'Gennaio',
            '02' => 'Febbraio',
            '03' => 'Marzo',
        ),
        'secondo' => array(
            '04' => 'Aprile',
            '05' => 'Maggio',
            '06' => 'Giugno',
        ),
        'terzo' => array(
            '07' => 'Luglio',
            '08' => 'Agosto',
            '09' => 'Settembre',
        ),
        'quarto' => array(
            '10' => 'Ottobre',
            '11' => 'Novembre',
            '12' => 'Dicembre'
        ),
    );

    function __construct($ditta) {
        try {
            $this->praLib = new praLib($ditta);
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB_DB = $this->praLib->getITALWEBDB();
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function getDataSetSuapClassificazione($anno) {

        $openData = array(
            'Anno' => (int) $anno,
            'Sportello' => array(),
            'Settore' => array(),
            'Attivita' => array(),
        );

        $sql_pra_where = "WHERE SUBSTRING(GESDRI, 1, 6) = '" . addslashes($anno) . "%s'";

        $sql_pra_tsp = "SELECT COUNT(*) as COUNT, GESTSP, GESSPA FROM PROGES $sql_pra_where GROUP BY GESTSP, GESSPA";
        $sql_pra_set = "SELECT COUNT(*) as COUNT, GESSTT, GESTSP, GESSPA FROM PROGES $sql_pra_where GROUP BY GESSTT";
        $sql_pra_att = "SELECT COUNT(*) as COUNT, GESATT, GESTSP, GESSPA FROM PROGES $sql_pra_where GROUP BY GESATT";

        foreach ($this->mesi as $meseCod => $meseDes) {
            $proges_tab_sportello = ItaDB::DBSQLSelect($this->PRAM_DB, sprintf($sql_pra_tsp, $meseCod));

            /*
             * Mi scorro gli sportelli
             */
            foreach ($proges_tab_sportello as $proges_rec_sportello) {
                $anatsp_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT TSPDES, TSPCOM FROM ANATSP WHERE TSPCOD = '{$proges_rec_sportello['GESTSP']}'", false);
                if (!$anatsp_rec['TSPDES']) {
                    continue;
                }

                $comune = $this->getComune($proges_rec_sportello);

                $anatsp_rec['TSPDES'] = utf8_encode($anatsp_rec['TSPDES']);

                $openData['Sportello'][$anatsp_rec['TSPDES']]['Totale'] += $proges_rec_sportello['COUNT'];
                $openData['Sportello'][$anatsp_rec['TSPDES']]['Comuni'][$comune] += $proges_rec_sportello['COUNT'];

                if (!isset($openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'])) {
                    $openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'] = $this->getArrayMensile($anno);
                }

                $openData['Sportello'][$anatsp_rec['TSPDES']]['Mensile'][$meseDes] += $proges_rec_sportello['COUNT'];
            }

            /*
             * Mi scorro i Settori
             */
            $proges_tab_settore = ItaDB::DBSQLSelect($this->PRAM_DB, sprintf($sql_pra_set, $meseCod));
            foreach ($proges_tab_settore as $proges_rec_settore) {
                $anaset_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT SETDES FROM ANASET WHERE SETCOD = '{$proges_rec_settore['GESSTT']}'", false);

                if (!$anaset_rec['SETDES']) {
                    continue;
                }

                $comune = $this->getComune($proges_rec_sportello);

                $anaset_rec['SETDES'] = utf8_encode($anaset_rec['SETDES']);

                $openData['Settore'][$anaset_rec['SETDES']]['Totale'] += $proges_rec_settore['COUNT'];
                $openData['Settore'][$anaset_rec['SETDES']]['Comuni'][$comune] += $proges_rec_settore['COUNT'];

                if (!isset($openData['Settore'][$anaset_rec['SETDES']]['Mensile'])) {
                    $openData['Settore'][$anaset_rec['SETDES']]['Mensile'] = $this->getArrayMensile($anno);
                }

                $openData['Settore'][$anaset_rec['SETDES']]['Mensile'][$meseDes] += $proges_rec_settore['COUNT'];
            }

            /*
             * Mi scorro le Attivita
             */
            $proges_tab_attivita = ItaDB::DBSQLSelect($this->PRAM_DB, sprintf($sql_pra_att, $meseCod));
            foreach ($proges_tab_attivita as $proges_rec_attivita) {
                $anaatt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT ATTDES FROM ANAATT WHERE ATTCOD = '{$proges_rec_attivita['GESATT']}'", false);
                if (!$anaatt_rec['ATTDES']) {
                    continue;
                }

                $comune = $this->getComune($proges_rec_attivita);

                $anaatt_rec['ATTDES'] = utf8_encode($anaatt_rec['ATTDES']);

                $openData['Attivita'][$anaatt_rec['ATTDES']]['Totale'] += $proges_rec_attivita['COUNT'];
                $openData['Attivita'][$anaatt_rec['ATTDES']]['Comuni'][$comune] += $proges_rec_attivita['COUNT'];

                if (!isset($openData['Attivita'][$anaatt_rec['ATTDES']]['Mensile'])) {
                    $openData['Attivita'][$anaatt_rec['ATTDES']]['Mensile'] = $this->getArrayMensile($anno);
                }

                $openData['Attivita'][$anaatt_rec['ATTDES']]['Mensile'][$meseDes] += $proges_rec_attivita['COUNT'];
            }
        }
        return $openData;
    }

    public function getDataSetSuapEventi($anno) {
        $openData = array(
            'Anno' => (int) $anno,
            'Totale' => (int) 0,
            'Eventi' => array(),
        );

        $sql_pra_where = "AND SUBSTRING(GESDRI, 1, 4) = '" . addslashes($anno) . "'";

        $sql = "SELECT
                    EVTDESCR,
                    COUNT(*)  AS QUANTI
                FROM
                    PROGES
                LEFT OUTER JOIN ANAEVENTI ON PROGES.GESEVE = ANAEVENTI.EVTCOD
                WHERE
                    1=1
                    $sql_pra_where
                GROUP BY
                    GESEVE";

        $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $totale = 0;
        foreach ($proges_tab as $proges_rec) {
            $proges_rec['EVTDESCR'] = utf8_encode($proges_rec['EVTDESCR']);
            $totale += $proges_rec['QUANTI'];
            $openData['Eventi'][$proges_rec['EVTDESCR']] = $proges_rec['QUANTI'];
        }
        $openData['Totale'] = $totale;
        return $openData;
    }

    public function getDataSetSuapPeriodoEventi($anno) {
        $openData = array(
            'Anno' => (int) $anno,
            'Eventi' => array(),
        );

        $sql_where = "WHERE SUBSTRING(GESDRI, 1, 6) = '" . addslashes($anno) . "%s'";

        $sql_geseve = "SELECT COUNT(*) as COUNT, GESEVE, GESTSP, GESSPA FROM PROGES $sql_where GROUP BY GESEVE";

        foreach ($this->mesi as $meseCod => $meseDes) {
            $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, sprintf($sql_geseve, $meseCod));
            foreach ($proges_tab as $proges_rec) {
                $anaeventi_rec = $this->praLib->GetAnaeventi($proges_rec['GESEVE']);
                if (!$anaeventi_rec['EVTDESCR']) {
                    continue;
                }

                $comune = $this->getComune($proges_rec);

                $openData['Eventi'][$anaeventi_rec['EVTDESCR']]['Totale'] += $proges_rec['COUNT'];
                $openData['Eventi'][$anaeventi_rec['EVTDESCR']]['Comuni'][$comune] += $proges_rec['COUNT'];

                if (!isset($openData['Eventi'][$anaeventi_rec['EVTDESCR']]['Mensile'])) {
                    $openData['Eventi'][$anaeventi_rec['EVTDESCR']]['Mensile'] = $this->getArrayMensile($anno);
                }

                $openData['Eventi'][$anaeventi_rec['EVTDESCR']]['Mensile'][$meseDes] += $proges_rec['COUNT'];
            }
        }
        return $openData;
    }

    public function getDataSetSuapNazionalitaDichiaranti($anno) {
        $openData = array(
            'Anno' => (int) $anno,
            'Totale' => "",
            'Nazionalita' => array(),
        );

        $sql_pra_where = "AND SUBSTRING(DAGNUM, 1, 4) = '" . addslashes($anno) . "'";

        $sql = "SELECT
                    UPPER(PRODAG.DAGVAL) AS CITTADINANZA,
                    COUNT(*) AS QUANTI
                FROM
                    PRODAG
               
                WHERE
                    (PRODAG.DAGKEY = 'DICHIARANTE_CITTADINANZA' OR PRODAG.DAGKEY = 'DEFAULT_DICHIARANTE_CITTADINANZA') AND
                    PRODAG.DAGVAL <> ''
                    $sql_pra_where
                GROUP BY CITTADINANZA";

        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $totale = 0;
        foreach ($prodag_tab as $prodag_rec) {
            $totale += $prodag_rec['QUANTI'];
            $openData['Nazionalita'][$prodag_rec['CITTADINANZA']] = $prodag_rec['QUANTI'];
        }
        $openData['Totale'] = $totale;
        return $openData;
    }

    public function getDataSetSuapTempiMedi($anno) {
        $openData = array(
            'Anno' => (int) $anno,
            'Settore' => array(),
            'Attivita' => array(),
        );

        foreach ($this->trimestri as $trimestreDes => $arrTrimestre) {
            foreach ($arrTrimestre as $meseCod => $meseDes) {

                /*
                 * Scorro i Settori
                 */
                $sql_pra_where = "WHERE SUBSTRING(GESDRI, 1, 6) = '" . addslashes($anno) . $meseCod . "' AND GESDCH <> ''";
                $sql_pra_set = "SELECT
                                    COUNT(*) as COUNT,
                                    GESSTT,
                                    GESSPA,
                                    GESTSP,
                                    SUM(" . $this->PRAM_DB->dateDiff('GESDCH', $this->PRAM_DB->nullIf("GESDRI", "''")) . ") AS SOMMA
                                FROM 
                                    PROGES
                                $sql_pra_where
                                GROUP BY
                                    GESSTT";

                $proges_tab_settore = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_pra_set);
                foreach ($proges_tab_settore as $proges_rec_settore) {
                    $anaset_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT SETDES FROM ANASET WHERE SETCOD = '{$proges_rec_settore['GESSTT']}'", false);
                    if (!$anaset_rec['SETDES']) {
                        continue;
                    }

                    $comune = $this->getComune($proges_rec_settore);

                    $anaset_rec['SETDES'] = utf8_encode($anaset_rec['SETDES']);

                    $openData['Settore'][$anaset_rec['SETDES']]['Totale'] += $proges_rec_settore['COUNT'];
                    $openData['Settore'][$anaset_rec['SETDES']]['Comuni'][$comune] += $proges_rec_settore['COUNT'];
                    $openData['Settore'][$anaset_rec['SETDES']]['Somma'] += $proges_rec_settore['SOMMA'];

                    if (!isset($openData['Settore'][$anaset_rec['SETDES']]['Trimestre'])) {
                        $openData['Settore'][$anaset_rec['SETDES']]['Trimestre'] = $this->getArrayTrimestrale($anno);
                    }

                    $openData['Settore'][$anaset_rec['SETDES']]['Trimestre'][$trimestreDes][$meseDes] += $proges_rec_settore['COUNT'];
                }

                /*
                 * Scorro le attività
                 */
                $sql_pra_where = "WHERE SUBSTRING(GESDRI, 1, 6) = '" . addslashes($anno) . $meseCod . "' AND GESDCH <> ''";
                $sql_pra_att = "SELECT
                                    COUNT(*) as COUNT,
                                    GESATT,
                                    GESSPA,
                                    GESTSP,
                                    SUM(" . $this->PRAM_DB->dateDiff('GESDCH', $this->PRAM_DB->nullIf("GESDRI", "''")) . ") AS SOMMA
                                FROM
                                    PROGES
                                $sql_pra_where
                                 GROUP BY
                                    GESATT";

                $proges_tab_attivita = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_pra_att);
                foreach ($proges_tab_attivita as $proges_rec_attivita) {
                    $anaatt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT ATTDES FROM ANAATT WHERE ATTCOD = '{$proges_rec_attivita['GESATT']}'", false);
                    if (!$anaatt_rec['ATTDES']) {
                        continue;
                    }

                    $comune = $this->getComune($proges_rec_attivita);

                    $anaatt_rec['ATTDES'] = utf8_encode($anaatt_rec['ATTDES']);

                    $openData['Attivita'][$anaatt_rec['ATTDES']]['Totale'] += $proges_rec_attivita['COUNT'];
                    $openData['Attivita'][$anaatt_rec['ATTDES']]['Comuni'][$comune] += $proges_rec_attivita['COUNT'];
                    $openData['Attivita'][$anaatt_rec['ATTDES']]['Somma'] += $proges_rec_attivita['SOMMA'];

                    if (!isset($openData['Attivita'][$anaatt_rec['ATTDES']]['Trimestre'])) {
                        $openData['Attivita'][$anaatt_rec['ATTDES']]['Trimestre'] = $this->getArrayTrimestrale($anno);
                    }

                    $openData['Attivita'][$anaatt_rec['ATTDES']]['Trimestre'][$trimestreDes][$meseDes] += $proges_rec_attivita['COUNT'];
                }
            }
        }

        /*
         * Calcolo media per i settori
         */
        foreach ($openData['Settore'] as $setdes => $settoreData) {
            $openData['Settore'][$setdes]['TempiMedi'] = round($settoreData['Somma'] / $settoreData['Totale']);
            unset($openData['Settore'][$setdes]['Somma']);
        }

        /*
         * Calcolo media per le attività
         */
        foreach ($openData['Attivita'] as $attdes => $attivitaData) {
            $openData['Attivita'][$attdes]['TempiMedi'] = round($attivitaData['Somma'] / $attivitaData['Totale']);
            unset($openData['Attivita'][$attdes]['Somma']);
        }

        return $openData;
    }

    private function getArrayMensile($anno) {
        $mensile = array();
        $ultimo = date('Y') == $anno ? date('m') : '12';
        foreach ($this->mesi as $mese => $desc) {
            if ((int) $mese > (int) $ultimo) {
                break;
            }

            $mensile[$desc] = 0;
        }

        return $mensile;
    }

    private function getArrayTrimestrale($anno) {
        $trimestre = array();
        $ultimo = date('Y') == $anno ? date('m') : '12';
        foreach ($this->trimestri as $trimestreDes => $arrTrimestre) {
            foreach ($arrTrimestre as $mese => $desc) {
                if ((int) $mese > (int) $ultimo) {
                    break;
                }

                $trimestre[$trimestreDes][$desc] = 0;
            }
        }

        return $trimestre;
    }

    private function getComune($proges_rec) {
        if ($proges_rec['GESSPA'] <> 0) {
            $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
            $comune = $anaspa_rec['SPACOM'];
        } else {
            $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            $comune = $anatsp_rec['TSPCOM'];
        }
        $comune = utf8_encode($comune) ?: $ente;
        return $comune;
    }

    function getDataSetSueClassificazione($anno) {
        $openData = array(
            'Anno' => (int) $anno,
            'Totale' => "",
            'Interventi' => array(),
        );

        $sql_pra_where = "WHERE SUBSTRING(GESDRI, 1, 4) = '" . addslashes($anno) . "'";

        $sql_pra_proc = "SELECT COUNT(*) as COUNT, GESPRO, GESTSP, GESSPA FROM PROGES $sql_pra_where GROUP BY GESPRO";
        $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_pra_proc);
        $quanti = 0;
        foreach ($proges_tab as $proges_rec) {
            $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
            if (!$anapra_rec['PRADES__1']) {
                continue;
            }

            $descrizione = utf8_encode($anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4']);

            $openData['Interventi'][$descrizione] = (int) $proges_rec['COUNT'];
            $quanti += $proges_rec['COUNT'];
        }
        $openData['Totale'] = $quanti;
        return $openData;
    }

    function getDataSetSuePeriodoEventi($anno) {

        $openData = array(
            'Anno' => (int) $anno,
            'Interventi' => array(),
        );

        $sql_pra_where = "WHERE SUBSTRING(GESDRI, 1, 6) = '" . addslashes($anno) . "%s'";

        $sql_pra_proc = "SELECT COUNT(*) as COUNT, GESPRO, GESTSP, GESSPA FROM PROGES $sql_pra_where GROUP BY GESPRO";

        foreach ($this->mesi as $meseCod => $meseDes) {
            $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, sprintf($sql_pra_proc, $meseCod));
            foreach ($proges_tab as $proges_rec) {
                $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
                if (!$anapra_rec['PRADES__1']) {
                    continue;
                }

                $comune = $this->getComune($proges_rec);
                $anapra_rec['PRADES__1'] = utf8_encode($anapra_rec['PRADES__1']);

                $openData['Interventi'][$anapra_rec['PRADES__1']]['Totale'] += $proges_rec['COUNT'];
                $openData['Interventi'][$anapra_rec['PRADES__1']]['Comuni'][$comune] += $proges_rec['COUNT'];

                if (!isset($openData['Interventi'][$anapra_rec['PRADES__1']]['Mensile'])) {
                    $openData['Interventi'][$anapra_rec['PRADES__1']]['Mensile'] = $this->getArrayMensile($anno);
                }

                $openData['Interventi'][$anapra_rec['PRADES__1']]['Mensile'][$meseDes] += $proges_rec['COUNT'];
            }
        }
        return $openData;
    }

    public function GetMetaData($params) {
        $retStatus = array(
            'RetValue' => true,
            'Message' => "MetaDati Estratti con Successo",
            'MetaData' => array(),
        );

        $parametriEnte_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE='" . $params['ente'] . "'", false);

        switch ($params['dataSetName']) {
            case 'SuapClassificazione':
                $desc = "Classificazione delle attivita produttive: Settore-attivita";
                $categoria = "SUAP";
                $tag = "Suap-Classificazione-Settore-Attivita";
                $anatsp_rec = $this->praLib->GetAnatsp(1);
                break;
            case 'SuapEventi':
                $desc = "Eventi che coinvolgono l'attivita produttiva: apertura-cessazione-ampliamento-ecc";
                $categoria = "SUAP";
                $tag = "Suap-Eventi";
                $anatsp_rec = $this->praLib->GetAnatsp(1);
                break;
            case 'SuapPeriodoEventi':
                $desc = "Periodo dell'accadimento di eventi: apertura-cessazione-ampliamento-ecc che coinvolgono l'attivita produttiva";
                $categoria = "SUAP";
                $tag = "Suap-Perido-Eventi";
                $anatsp_rec = $this->praLib->GetAnatsp(1);
                break;
            case 'SuapNazionalitaDichiaranti':
                $desc = "Nazionalita dichiaranti";
                $categoria = "SUAP";
                $tag = "Suap-Nazionalita-Dichiaranti";
                $anatsp_rec = $this->praLib->GetAnatsp(1);
                break;
            case 'SuapTempiMedi':
                $desc = "Tempi medi per attivita";
                $categoria = "SUAP";
                $tag = "Suap-Tempi-Medi";
                $anatsp_rec = $this->praLib->GetAnatsp(1);
                break;
            case 'SueClassificazione':
                $desc = "Classificazione degli interventi PDC-SCIA-CILA-ecc";
                $categoria = "SUE";
                $tag = "Sue-Classificazione-Interventi";
                $anatsp_rec = $this->praLib->GetAnatsp(6);
                break;
            case 'SuePeriodoEventi':
                $desc = "Periodo dell'accadimento dell evento di richiesta";
                $categoria = "SUE";
                $tag = "Sue-Periodo-Eventi";
                $anatsp_rec = $this->praLib->GetAnatsp(6);
                break;
            case 'SueNazionalitaDichiaranti':
                $desc = "-Nazionalita proprietari";
                $categoria = "SUE";
                $tag = "Sue-Nazionalita-Proprietari";
                $anatsp_rec = $this->praLib->GetAnatsp(6);
                break;
        }

        $metadata = array(
            'Titolo' => $params['dataSetName'],
            'Titolare' => $parametriEnte_rec['DENOMINAZIONE'],
            'Referente' => "",
            'Contatto' => $anatsp_rec['TSPPEC'],
            'Descrizione' => $desc,
            'Categorie' => $categoria,
            'Tag' => $tag,
            'DocumentazioneTecnica' => "",
            'DescrizioneCampi' => "",
            'CoperturaGeografica' => $parametriEnte_rec['CITTA'],
            'CoperturaTemporaleDataInizio' => "01-01-" . $params['anno'],
            'CoperturaTemporaleDataFine' => "31-12-" . $params['anno'],
            'DataPubblicazione' => "",
            'Formato' => "json",
            'CodificaCaratteri' => "ISO8859-1",
            'Dimensione' => "",
        );
        $retStatus['MetaData'] = $metadata;
        return $retStatus;
    }

}
