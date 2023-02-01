<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    20.06.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLibFrontOffice.class.php');
include_once(ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaFileUtils.class.php');



class praWsAgent extends wsModel {

    public $PRAM_DB;
    public $praLib;
    public $praLibFrontOffice;
    public $errCode;
    public $errMessage;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->praLibFrontOffice = new praLibFrontOffice();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        parent::__destruct();
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

    function CtrRichieste($anno = '', $procedimento = '', $statoAcquisizioneBO = '', $statoRichieste = '', $statoConfermaAcquisizione = '', $dataConfermaAcquisizione = '', $contestoConfermaAcquisizione = '') {
        if ($statoAcquisizioneBO == '') {
            /*
             * Prendo da default stabilito nei parametri 
             * Se non parametrizzato prende solo non acquisite_bo
             * 
             */

            $Config = $this->praLibFrontOffice->getEnv_config('WSAGENTFO_DEFAULTS', 'codice', 'CTRRICHIESTE_DEFAULT_STATO', false);
            if ($Config) {
                $statoAcquisizioneBO = $Config['CONFIG'];
            }
        }

        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();
        switch (strtoupper($statoAcquisizioneBO)) {
            case 'ACQUISITE_BO' :
                $whereAcquisite = " AND (PROGES.GESPRA IS NOT NULL OR PROPAS.PRORIN IS NOT NULL)";
                break;
            case 'TUTTE' :
                $whereAcquisite = '';
                break;
            case 'NON_ACQUISITE_BO':
            default:
                $whereAcquisite = " AND PROGES.GESPRA IS NULL AND PROPAS.PRORIN IS NULL";
                break;
        }

        $whereStatoFO = '';
        $filtroStatoFO = array();
        switch (strtoupper($statoRichieste)) {
            case 'TUTTE' :
                $filtroStatoFO[] = "1=1";
                break;
            case '' :
                $filtroStatoFO[] = "RICSTA='01'";
                break;
            case 'ATTESA_PROTOCOLLAZIONE' :
                $filtroStatoFO[] = "RICSTA='01' AND RICDATARPROT <> '' AND RICORARPROT <> '' AND RICNPR = '' AND RICDPR = ''";
                break;
            default :
                foreach (explode(',', $statoRichieste) as $stato) {
                    $filtroStatoFO[] = "RICSTA='$stato'";
                }
                break;
        }
        if (count($filtroStatoFO)) {
            $whereStatoFO = "(" . implode(" OR ", $filtroStatoFO) . ")";
            //$whereStatoFO = implode(" AND ", $filtroStatoFO);
        }

        switch (strtoupper($statoConfermaAcquisizione)) {
            case 'CONFERMATE' :
                $whereConfermaAcquisizione = " AND RICCONFDATA<>''";
                break;
            case 'NON_CONFERMATE':
                $whereConfermaAcquisizione = " AND RICCONFDATA=''";
                break;
            case 'TUTTE' :
            default:
                $whereConfermaAcquisizione = "";
                break;
        }

        if ($dataConfermaAcquisizione) {
            $whereConfermaAcquisizione .= " AND RICCONFDATA='$dataConfermaAcquisizione'";
        }

        if ($contestoConfermaAcquisizione) {
            $whereConfermaAcquisizione .= " AND RICCONFCONTEXT='$contestoConfermaAcquisizione'";
        }

        $sql = "SELECT
            PRORIC.RICNUM AS RICNUM,
            PRORIC.RICPRO AS RICPRO,
            PRORIC.RICDAT AS RICDAT,            
            PRORIC.RICTIM AS RICTIM,            
            PRORIC.RICTSP AS RICTSP,
            PRORIC.RICSPA AS RICSPA,
            PRORIC.RICRPA AS RICRPA,
            PRORIC.RICRES AS RICRES,
            PRORIC.RICPC AS RICPC," .
                $this->PRAM_DB->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES,
            PRORIC.RICOGG AS RICOGG,
            PRORIC.RICNPR AS RICNPR,
            PRORIC.RICDPR AS RICDPR,
            PROGES.GESNUM AS GESNUM,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'OGGETTO_DOMANDA' AND RICDAT <> '' GROUP BY DAGNUM) AS OGGETTO_DOMANDA,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_NUMERO' AND RICDAT <> '') AS PRATICA_NUMERO,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_ANNO' AND RICDAT <> '') AS PRATICA_ANNO,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_LETTERA' AND RICDAT <> '') AS PRATICA_LETTERA,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_NUMERO_VARIANTE' AND RICDAT <> '') AS PRATICA_NUMERO_VARIANTE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_ANNO_VARIANTE' AND RICDAT <> '') AS PRATICA_ANNO_VARIANTE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'PRATICA_LETTERA_VARIANTE' AND RICDAT <> '') AS PRATICA_LETTERA_VARIANTE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'DOMANDA_NUMERO' AND RICDAT <> '') AS DOMANDA_NUMERO,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'DOMANDA_ANNO' AND RICDAT <> '') AS DOMANDA_ANNO,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'DOMANDA_LETTERA' AND RICDAT <> '') AS DOMANDA_LETTERA,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'RICHIESTA_PADRE_FORMATTED_VARIANTE' AND RICDAT <> '') AS RICHIESTA_PADRE_FORMATTED_VARIANTE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'RICHIESTA_PADRE_VARIANTE' AND RICDAT <> '') AS RICHIESTA_PADRE_VARIANTE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'RICHIESTA_PADRE_FORMATTED' AND RICDAT <> '') AS RICHIESTA_PADRE_FORMATTED,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'RICHIESTA_PADRE' AND RICDAT <> '') AS RICHIESTA_PADRE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'INTER_VIA' AND RICDAT <> '' AND DAGSET LIKE '%_01') AS LOCALIZZAZIONE_VIA,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'INTER_CIV' AND RICDAT <> '' AND DAGSET LIKE '%_01') AS LOCALIZZAZIONE_CIVICO,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICRPA AND DAGKEY = 'INTER_VIA' AND RICDAT <> '' AND DAGSET LIKE '%_01') AS LOCALIZZAZIONE_VIA_PADRE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICRPA AND DAGKEY = 'INTER_CIV' AND RICDAT <> '' AND DAGSET LIKE '%_01') AS LOCALIZZAZIONE_CIVICO_PADRE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'DICHIARANTE_NOME' AND RICDAT <> '' ORDER BY DAGSET ASC LIMIT 1) AS DICHIARANTE_NOME,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICNUM AND DAGKEY = 'DICHIARANTE_COGNOME' AND RICDAT <> '' ORDER BY DAGSET ASC LIMIT 1) AS DICHIARANTE_COGNOME,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICRPA AND DAGKEY = 'DICHIARANTE_NOME' AND RICDAT <> '' ORDER BY DAGSET ASC LIMIT 1) AS DICHIARANTE_NOME_PADRE,
            (SELECT RICDAT FROM RICDAG WHERE DAGNUM = RICRPA AND DAGKEY = 'DICHIARANTE_COGNOME' AND RICDAT <> '' ORDER BY DAGSET ASC LIMIT 1) AS DICHIARANTE_COGNOME_PADRE,
            PRORIC.RICDRE AS RICDRE,
            PRORIC.CODICEPRATICASW AS CODICEPRATICASW,
            PRORIC.RICCONFDATA AS RICCONFDATA,
            PRORIC.RICCONFDATA AS RICCONFORA,
            PRORIC.RICCONFCONTEXT AS RICCONFCONTEXT,
            PRORIC.RICUUID AS RICUUID,
            PRORIC.RICCONFINFO AS RICCONFINFO," .
                $this->PRAM_DB->strConcat("RICCOG", "' '", "RICNOM") . " AS ESIBENTE
            FROM PRORIC PRORIC
               LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
               LEFT OUTER JOIN PROGES PROGES ON RICNUM=PROGES.GESPRA
               LEFT OUTER JOIN PROPAS PROPAS ON RICNUM=PROPAS.PRORIN
            WHERE $whereStatoFO $whereAcquisite $whereVisibilita $whereConfermaAcquisizione";

        $sql .= " AND RICRUN = '' ";

        if ($anno) {
            $sql .= " AND RICNUM LIKE '$anno%'";
        }
        if ($procedimento) {
            $sql .= " AND RICPRO='$procedimento'";
        }

        $sql .= " ORDER BY RICNUM";


        try {
            $Richieste_tab = itaDB::DBSQLSelect($this->PRAM_DB, $sql);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore acceso dati:$sql: " . $exc->getMessage());
            return false;
        }

        $anaparFascRem_rec = $this->praLib->GetAnapar("FASCICOLAZIONE_REMOTA", "parkey", false);

        if ($Richieste_tab) {
            $cdata_a = ""; //<![CDATA[";
            $cdata_c = ""; //]]>";
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
            $xml .= "<RICHIESTEFO>\r\n";
            foreach ($Richieste_tab as $Richieste_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Richieste_rec as $Chiave => $Valore) {
                    $xml = $xml . "<$Chiave>$cdata_a" . htmlspecialchars($Valore, ENT_COMPAT, "ISO-8859-1") . "$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml .= "<FASCICOLA>" . $anaparFascRem_rec['PARVAL'] . "</FASCICOLA>\r\n";
            $xml = $xml . "</RICHIESTEFO>\r\n";
        }
        $xmlB64 = base64_encode($xml);
        return $xmlB64;
    }

    function AcquisisciRichiesta($NumeroRichiesta, $AnnoRichiesta) {
//        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//
//        $whereVisibilta = '';
//        if ($retVisibilta['SPORTELLO'] != 0) {
//            $whereVisibilta .= " AND RICTSP = " . $retVisibilta['SPORTELLO'];
//        }
//        if ($retVisibilta['AGGREGATO'] != 0) {
//            $whereVisibilta .= " AND RICSPA = " . $retVisibilta['AGGREGATO'];
//        }
        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();

        //
        // Attenzione: se tipo inoltrata info camenre non importabile
        //

        $CodiceRichiesta = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, "0", STR_PAD_LEFT);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM='" . $CodiceRichiesta . "' " . $whereVisibilita;
        $Proric_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta on line non disponibile");
            return false;
        }
        //
        //  Trovato il record di PRORIC si usa per preparare i dati
        //  Parametri per aggiungi
        //
        $tipoInserimento = "PECSUAP";
        $esterna = false;
        $sportello = $Proric_rec['RICTSP'];
        $Filent_Rec = $this->praLib->GetFilent(1);
        $ProgressivoDaRichiesta = false;
        if ($Filent_Rec['FILVAL'] == 1) {
            $ProgressivoDaRichiesta = true;
        }
        /*
         * $proric_param[] array con dati per l'aggiunta
         * 
         */
        $proric_parm = array();
        $proric_parm['PRORIC_REC'] = $Proric_rec;
        //
        // DA vedere come si costruisce
        //
        $proric_parm['ELENCOALLEGATI'] = $this->GetAllegatiPratica($Proric_rec);
        //
        // Valorizzo dati richiedente pratica
        //
        $anades_rec = array();
        $anades_rec['DESNOM'] = $proric_parm['PRORIC_REC']['RICCOG'] . " " . $proric_parm['PRORIC_REC']['RICNOM'];
        $anades_rec['DESFIS'] = $proric_parm['PRORIC_REC']['RICFIS'];
        $anades_rec['DESIND'] = $proric_parm['PRORIC_REC']['RICVIA'];
        $anades_rec['DESCAP'] = $proric_parm['PRORIC_REC']['RICCAP'];
        $anades_rec['DESCIT'] = $proric_parm['PRORIC_REC']['RICCOM'];
        $anades_rec['DESPRO'] = $proric_parm['PRORIC_REC']['RICPRV'];
        $anades_rec['DESEMA'] = $proric_parm['PRORIC_REC']['RICEMA'];
        $anades_rec['DESRUO'] = "0001";
        //
        // Valorizzo dati testata pratica
        //
        $proges_rec = array();
        $proges_rec['GESDRE'] = date('Ymd');
        $proges_rec['GESPRO'] = str_pad($proric_parm['PRORIC_REC']['RICPRO'], 6, "0", STR_PAD_LEFT);
        $proges_rec['GESGIO'] = $anapra_rec['PRAGIO'];
        $proges_rec['GESDRI'] = $proric_parm['PRORIC_REC']['RICDAT'];
        $proges_rec['GESORA'] = $proric_parm['PRORIC_REC']['RICTIM'];
        $proges_rec['GESRES'] = str_pad($proric_parm['PRORIC_REC']['RICRES'], 6, "0", STR_PAD_LEFT);
        $proges_rec['GESPRA'] = $proric_parm['PRORIC_REC']['RICNUM'];
        $proges_rec['GESTSP'] = $sportello;
        $proges_rec['GESSPA'] = $proric_parm['PRORIC_REC']['RICSPA'];

        //Ribalto il protocollo se c'è
        if ($proric_parm['PRORIC_REC']['RICNPR'] != 0) {
            $proges_rec['GESNPR'] = $proric_parm['PRORIC_REC']['RICNPR'];
            $proges_rec['GESPAR'] = "A";
            $proges_rec['GESMETA'] = $proric_parm['PRORIC_REC']['RICMETA'];
        }

        //Assegno il codice evento e segnalazione comunica per statistiche
        $proges_rec['GESEVE'] = $proric_parm['PRORIC_REC']['RICEVE'];
        $proges_rec['GESSEG'] = $proric_parm['PRORIC_REC']['RICSEG'];

        //
        // Identifico la presenza del file xmlinsfo.xml
        //
        $fileXML = "";
        foreach ($proric_parm['ELENCOALLEGATI'] as $allegato) {
            //if (strpos($allegato['FILENAME'], 'XMLINFO') !== false) {
            if (strpos($allegato['FILENAME'], 'XMLINFO.xml') !== false) {
                $fileXML = $allegato['DATAFILE'];
                break;
            }
        }

        /*
         * Se è una richiesta on-line e se è un'integrazione, vedo se c'è il dato aggiuntivo della variante
         */
        $variante = false;
        if ($proric_parm['PRORIC_REC']['RICPC'] == "1") {
            $variante = true;
        }

        $starweb = false;
        if ($proric_parm['PRORIC_REC']['RICRPA'] && !$variante) {
            $Propas_rec = $this->praLib->CaricaPassoIntegrazione($proric_parm['PRORIC_REC'], $proric_parm['ELENCOALLEGATI']);
            if ($Propas_rec == false) {
                $this->setErrCode(-1);
                $this->setErrMessage($this->praLib->getErrMessage());
                return false;
            }
            $Proges_rec = $this->praLib->GetProges($Propas_rec['PRONUM']);
            $ret = array();
            $ret['TIPO'] = "INTEGRAZIONE";
            $ret['GESNUM'] = $Propas_rec['PRONUM'];
            $ret['GESPRA'] = $Proges_rec['GESPRA'];
            $ret['PRORIN'] = $Propas_rec['PRORIN'];
            $ret['PROPAK'] = $Propas_rec['PROPAK'];
        } else {
            /* @var $praLibPratica praLibPratica */
            $praLibPratica = praLibPratica::getInstance();
            $parm_aggiungi = array(
                "PROGES_REC" => $proges_rec,
                "ANADES_REC" => $anades_rec,
                "PRORIC_REC" => $proric_parm['PRORIC_REC'],
                "XMLINFO" => $fileXML,
                "ALLEGATI" => $proric_parm['ELENCOALLEGATI'],
                "ALLEGATIACCORPATE" => $this->praLib->GetAllegatiAccorpate($fileXML),
                "ALLEGATICOMUNICA" => array(),
                "esterna" => $esterna,
                "tipoInserimento" => $tipoInserimento,
                "starweb" => $starweb,
                "EscludiPassiFO" => false,
                "ProgressivoDaRichiesta" => $ProgressivoDaRichiesta
            );
            $ret_aggiungi = $praLibPratica->aggiungi($this, $parm_aggiungi);
            if (!$ret_aggiungi) {
                $this->setErrCode(-1);
                $this->setErrMessage($praLibPratica->getErrMessage());
                return false;
            } else {
                $ret = array();
                $ret['TIPO'] = "RICHIESTA";
                $ret['ROWID'] = $ret_aggiungi['ROWID'];
                $ret['GESNUM'] = $ret_aggiungi['GESNUM'];
                $ret['GESPRA'] = $ret_aggiungi['GESPRA'];
            }
        }

        $cdata_a = ""; //<![CDATA[";
        $cdata_c = ""; //]]>";
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $xml .= "<ACQUISIZIONE>\r\n";
        foreach ($ret as $Chiave2 => $Valore2) {
            $xml = $xml . "<$Chiave2>$cdata_a" . htmlspecialchars($Valore2) . "$cdata_c</$Chiave2>\r\n";
        }
        $xml = $xml . "</ACQUISIZIONE>\r\n";
        $xmlB64 = base64_encode($xml);
        return $xmlB64;
    }

    function SetErroreProtocollazione($NumeroRichiesta, $AnnoRichiesta, $erroreProtocollazione) {
        $Codice = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, '0', STR_PAD_LEFT);
        $Proric_rec = $this->praLib->GetProric($Codice, 'codice');
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice,  non esistente");
            return false;
        }

        $update_Info = "marco errore protocollazione $erroreProtocollazione su richiesta: $Codice";
        $Proric_rec['RICERRRPROT'] = $erroreProtocollazione;

        $retUpd = $this->updateRecord($this->PRAM_DB, 'PRORIC', $Proric_rec, $update_Info);
        if (!$retUpd) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Aggiornamento marcatura richiesta ' . $Proric_rec['RICNUM']);
            return false;
        }
        return true;
    }

    function SetMarcaturaRichiesta($NumeroRichiesta, $AnnoRichiesta, $numeroProtocollo, $dataProtocollo, $metadatiProtocollazione) {
        $Codice = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, '0', STR_PAD_LEFT);
        $Proric_rec = $this->praLib->GetProric($Codice, 'codice');
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice,  non esistente");
            return false;
        }

        if ($this->isJson($metadatiProtocollazione)) {
            $metadatiProtocollazione = serialize(json_decode($metadatiProtocollazione, true));
        }

        if (!$metadatiProtocollazione) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice, metadati protocollo non indicati");
            return false;
        }

        if (!$numeroProtocollo) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice, numero protocollo non indicato.");
            return false;
        }
        if (!$dataProtocollo) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice, data protocollo non indicata");
            return false;
        }

        $update_Info = "Set conferma marcatura con protocollo n. $numeroProtocollo del $dataProtocollo su richiesta: $Codice";
        $anno = substr($dataProtocollo, 0, 4);
        $Proric_rec['RICNPR'] = $anno . $numeroProtocollo;
        $Proric_rec['RICDPR'] = $dataProtocollo;
        $Proric_rec['RICMETA'] = $metadatiProtocollazione;

        $retUpd = $this->updateRecord($this->PRAM_DB, 'PRORIC', $Proric_rec, $update_Info);
        if (!$retUpd) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Aggiornamento marcatura richiesta ' . $Proric_rec['RICNUM']);
            return false;
        }
        return true;
    }

    function SetAcquisizioneRichiesta($NumeroRichiesta, $AnnoRichiesta, $dataAcquisizione, $oraAcquisizione, $contestoAcquisizione, $infoAcquisizione) {
        $utenteAcquisizione = App::$utente->getKey('nomeUtente');
        $Codice = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, '0', STR_PAD_LEFT);
        $Proric_rec = $this->praLib->GetProric($Codice, 'codice');
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice,  non esistente");
            return false;
        }
        if (!$dataAcquisizione) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice, data acquisizione non indicata.");
            return false;
        }
        if (!$oraAcquisizione) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice, ora acquisizione non indicata");
            return false;
        }
        if ($Proric_rec['RICCONFDATA']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta $Codice, Acquisizione già Confermata.");
            return false;
        }

        $ret_stato = $this->controllaAcquisizioneRichiesta($Proric_rec);
        if ($ret_stato !== true) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta $Codice, stato {$Proric_rec['RICSTA']} non modificabile con il valore $statoRichiesta.");
            return false;
        }
        $Proric_rec['RICCONFDATA'] = $dataAcquisizione;
        $Proric_rec['RICCONFORA'] = $oraAcquisizione;
        $Proric_rec['RICCONFCONTEXT'] = $contestoAcquisizione;
        $Proric_rec['RICCONFINFO'] = $infoAcquisizione;
        $Proric_rec['RICCONFUTE'] = $utenteAcquisizione;

        $update_Info = "Set conferma acquisizione:" . $dataAcquisizione . " su richiesta: " . $Codice;
        $retUpd = $this->updateRecord($this->PRAM_DB, 'PRORIC', $Proric_rec, $update_Info);  //ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $Proric_rec);
        if (!$retUpd) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Aggiornamento stato richiesta ' . $Proric_rec['RICNUM']);
            return false;
        }
        return true;
    }

    function SetStatoRichiesta($NumeroRichiesta, $AnnoRichiesta, $statoRichiesta) {
        $Codice = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, '0', STR_PAD_LEFT);
        $Proric_rec = $this->praLib->GetProric($Codice, 'codice');
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice,  non esistente");
            return false;
        }
        $ret_stato = $this->controllaCambioStatoRichiesta($Proric_rec, $statoRichiesta);
        if ($ret_stato !== true) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta $Codice, stato {$Proric_rec['RICSTA']} non modificabile con il valore $statoRichiesta.");
            return false;
        }
        $Proric_rec['RICSTA'] = $statoRichiesta;
        $update_Info = "Set stato:" . $statoRichiesta . ", su richiesta: " . $Codice;
        $retUpd = $this->updateRecord($this->PRAM_DB, 'PRORIC', $Proric_rec, $update_Info);  //ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $Proric_rec);
        if (!$retUpd) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Aggiornamento stato richiesta ' . $Proric_rec['RICNUM']);
            return false;
        }
        return true;
    }

    function GetXMLINFO($richiesta) {
        $ricPath = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms')) . "attachments/" . $richiesta;
        $xmlFilePath = $ricPath . "/XMLINFO.xml";
        $fh = fopen($xmlFilePath, 'rb');
        if ($fh) {
            $binary = fread($fh, filesize($xmlFilePath));
            fclose($fh);
            return base64_encode($binary);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Lettura Allegato:' . $xmlFilePath . ' fallita');
            return false;
        }
    }

    function GetXMLINFOAccorpate($richiesta) {
        $ricPath = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms')) . "attachments/" . $richiesta;
        $xmlInfoList = glob($ricPath . "/XMLINFO_*.xml");
        $arrBase64 = array();
        foreach ($xmlInfoList as $key => $filePath) {
            $fh = fopen($filePath, 'rb');
            if ($fh) {
                $binary = fread($fh, filesize($filePath));
                fclose($fh);
                $arrBase64[$key]['Stream'] = base64_encode($binary);
                $arrBase64[$key]['Richiesta'] = substr(basename($filePath, ".xml"), -10);
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage('Lettura Allegato:' . $filePath . ' fallita');
                return false;
            }
        }
        return base64_encode(json_encode($arrBase64, JSON_HEX_APOS));
    }

    function GetBodyFile($richiesta) {
        $ricPath = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms')) . "attachments/" . $richiesta;
        $bodyFile = $ricPath . "/body.txt";
        $fh = fopen($bodyFile, 'rb');
        if ($fh) {
            $binary = fread($fh, filesize($bodyFile));
            fclose($fh);
            return base64_encode($binary);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Lettura Allegato:' . $bodyFile . ' fallita');
            return false;
        }
    }

    function GetPRORIC($Chiave, $Valore) {
        $sql = "
            SELECT
                *
            FROM PRORIC
            WHERE $Chiave = '$Valore'";

        try {
            $Proric_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Record non disponibile');
            return false;
        }

        $Proric_rec_encode = itaLib::utf8_encode_recursive($Proric_rec);
        $jsonEnconde = json_encode($Proric_rec_encode, JSON_HEX_APOS);
        if (!$jsonEnconde) {
            $this->setErrCode(-1);
            $this->setErrMessage('Codifica json fallita');
            return false;
        }

        $jsonB64 = base64_encode($jsonEnconde);
        if (!$jsonB64) {
            $this->setErrCode(-1);
            $this->setErrMessage('Codifica base64 fallita');
            return false;
        }
        return $jsonB64;
    }

    function GetRICDOC($Chiave, $Valore) {
        $sql = "
            SELECT
                *
            FROM RICDOC
            WHERE $Chiave = '$Valore'";

        try {
            $Ricdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        if (!$Ricdoc_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage('Record non disponibile');
            return false;
        }

        $Ricdoc_tab_encode = itaLib::utf8_encode_recursive($Ricdoc_tab);
        $jsonEnconde = json_encode($Ricdoc_tab_encode, JSON_HEX_APOS);
        if (!$jsonEnconde) {
            $this->setErrCode(-1);
            $this->setErrMessage('Codifica json fallita');
            return false;
        }

        $jsonB64 = base64_encode($jsonEnconde);
        if (!$jsonB64) {
            $this->setErrCode(-1);
            $this->setErrMessage('Codifica base64 fallita');
            return false;
        }

        return $jsonB64;
    }

    function GetRichiestaDati($NumeroRichiesta, $AnnoRichiesta, $agenzia = '', $hashRichiesta = '', $idSUAP = '') {

//        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//
//        $whereVisibilta = '';
//        if ($retVisibilta['SPORTELLO'] != 0) {
//            $whereVisibilta .= " AND RICTSP = " . $retVisibilta['SPORTELLO'];
//        }
//        if ($retVisibilta['AGGREGATO'] != 0) {
//            $whereVisibilta .= " AND RICSPA = " . $retVisibilta['AGGREGATO'];
//        }
        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();

        $whereStato = '';
        if ($stato) {
            $whereStato = " AND PRASTA='$stato'";
        }

        //
        // Controllo Esistenza
        //
        $cdata_a = ""; //<![CDATA[";
        $cdata_c = ""; //]]>";
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $CodiceRichiesta = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, "0", STR_PAD_LEFT);

        $sql = "
            SELECT
                PRORIC.*,
                ANAATT.ATTDES AS ATTDES,
                ANAEVENTI.EVTDESCR AS EVTDESCR,
                ANAPRA.PRADES__1 AS PRADES,                
                ANASET.SETDES AS SETDES,
                ANATSP.TSPIDE AS TSPIDE,
                ANATSP.TSPDEN AS TSPDEN,
                ANATSP.TSPCCA AS TSPCCA,
                ANATSP.TSPIST AS TSPIST,
                ANATSP.TSPPEC AS TSPPEC,
                ANATSP.TSPTIP AS TSPTIP,                
                ANASPA.SPADES AS SPADES,
                ANASPA.SPACCA AS SPACCA,
                ANASPA.SPAIST AS SPAIST
            FROM
                PRORIC PRORIC
            LEFT OUTER JOIN ANAPRA ANAPRA ON ANAPRA.PRANUM=PRORIC.RICPRO
            LEFT OUTER JOIN ANATSP ANATSP ON ANATSP.TSPCOD=PRORIC.RICTSP
            LEFT OUTER JOIN ANASPA ANASPA ON ANASPA.SPACOD=PRORIC.RICSPA AND PRORIC.RICSPA<>0
            LEFT OUTER JOIN ANAEVENTI ANAEVENTI ON ANAEVENTI.EVTCOD=PRORIC.RICEVE
            LEFT OUTER JOIN ANASET ANASET ON ANASET.SETCOD=PRORIC.RICSTT
            LEFT OUTER JOIN ANAATT ANAATT ON ANAATT.ATTCOD=PRORIC.RICATT
            WHERE RICNUM='" . $CodiceRichiesta . "' " . $whereVisibilita;

        try {
            $Proric_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Richiesta non disponibile');
            return false;
        }

        //
        // Controlli Per Agenzia
        //
        if ($agenzia) {
            $sql = "SELECT * FROM ANATSP WHERE TSPCOD = " . $Proric_rec['RICTSP'];
            $Anatsp_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
            if (!$Anatsp_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore lettura sportello.');
                return false;
            }
            if ($Anatsp_rec['TSPIDE'] && $Anatsp_rec['TSPIDE'] != $idSUAP) {
                $this->setErrCode(-1);
                $this->setErrMessage('Id Suap Richiesta diverso da Id Suap Sportello');
                return false;
            }
            if (!$hashRichiesta) {
                $this->setErrCode(-1);
                $this->setErrMessage('Richiesta non disponibile hash mancante.');
                return false;
            }

            $sql = "SELECT * FROM RICITE WHERE RICNUM='$CodiceRichiesta' AND ITEAGE=1 AND RICNOT<>''";
            $Ricite_tab = itaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            if (!$Ricite_tab) {
                $this->setErrCode(-1);
                $this->setErrMessage('Richiesta non disponibile');
                return false;
            }
            $trovatoHash = false;
            foreach ($Ricite_tab as $Ricite_rec) {
                $metaDati = unserialize($Ricite_rec['RICNOT']);
                if (is_array($metaDati)) {
                    $arrAgenzia = $metaDati['INVIOAGENZIA'];
                    if ($arrAgenzia) {
                        foreach ($arrAgenzia as $arrInoltro) {
                            if ($arrInoltro['OK']) {
                                if ($arrInoltro['OK']['AGENZIA'] == $agenzia && $arrInoltro['OK']['HASH'] === $hashRichiesta && $arrInoltro['OK']['RESPONSE'] == 'OK') {
                                    $trovatoHash = true;
                                    break;
                                }
                            }
                        }
                        if ($trovatoHash)
                            break;
                    }
                }
            }
            if (!$trovatoHash) {
                $this->setErrCode(-1);
                $this->setErrMessage('Richiesta non disponibile hash incongruente.');
                return false;
            }
        }
        //
        // Fine controllo agenzia
        //

        $xml = $xml . "<RICHIESTADATI id=\"$CodiceRichiesta\">\r\n";
        $xml = $xml . "<PRORIC>\r\n";
        $xml = $xml . "<RECORD>\r\n";
        foreach ($Proric_rec as $Chiave => $Campo) {
            $Campo = $this->parseText($Campo, ENT_NOQUOTES);
            //$xml = $xml . "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
        }
        $xml = $xml . "</RECORD>\r\n";
        $xml = $xml . "</PRORIC>\r\n";

        $campi = array();
        $campi[] = "RICNUM";
        $campi[] = "ITEKEY";
        $campi[] = "ITEDES";
        $campi[] = "ITESEQ";
        $campi[] = "ITECLT";
        $campi[] = "ITECLT_DESCRIZIONE";
        $campi[] = "ITEDRR";

        $chiavi = array();
        $chiavi[] = $Proric_rec['RICPRO'];

        $chiavi_attach = array();

        $retRiciteTab = $this->getRiciteTab($CodiceRichiesta);
        if ($retRiciteTab['Status'] == "-1") {
            $this->setErrCode(-1);
            $this->setErrMessage($retRiciteTab['Message']);
            return false;
        }
        $Ricite_tab = $retRiciteTab['Ricite_tab'];
//        if (!$Ricite_tab) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Passi non trovati');
//            return false;
//        }


        if ($Ricite_tab) {
            $xml_ricite = "<RICITE>\r\n";
            foreach ($Ricite_tab as $Ricite_rec) {
                $chiavi[] = "'" . $Ricite_rec['ITEKEY'] . "'";
                $xml_ricite = $xml_ricite . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    $xml_ricite = $xml_ricite . "<$Campo>$cdata_a" . $this->parseText($Ricite_rec[$Campo], ENT_NOQUOTES) . "$cdata_c</$Campo>\r\n";
                }
                $xml_ricite = $xml_ricite . "</RECORD>\r\n";
            }
            $xml_ricite = $xml_ricite . "</RICITE>\r\n";
        }

        $xml .= $xml_ricite;
        $campi = array();
        $campi[] = "DAGNUM";
        $campi[] = "ITEKEY";
        $campi[] = "DAGKEY";
        $campi[] = "DAGSET";
        $campi[] = "RICDAT";
        $campi[] = "DAGTIP";
        $sql = "SELECT * FROM RICDAG WHERE ITEKEY IN (" . implode(",", $chiavi) . ") AND DAGNUM = '" . $CodiceRichiesta . "' AND DAGTIC<>'Html' AND DAGTIC<>'RadioButton' ORDER BY DAGSEQ";
        try {
            $Ricdag_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore acceso dati RICDAG: " . $exc->getMessage());
            return false;
        }
        if ($Ricdag_tab) {
            $xml = $xml . "<RICDAG>\r\n";
            foreach ($Ricdag_tab as $Ricdag_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    //$xml = $xml . "<$Campo><![CDATA[" . $this->parseText($Ricdag_rec[$Campo], ENT_NOQUOTES) . "]]></$Campo>\r\n";
                    if ($Campo == "RICDAT") {
                        $xml = $xml . "<$Campo><![CDATA[" . $Ricdag_rec[$Campo] . "]]></$Campo>\r\n";
                    } else {
                        $xml = $xml . "<$Campo>$cdata_a" . $Ricdag_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                    }
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</RICDAG>\r\n";
        }

        /*
         *  Indice Allegati
         */
        $campi = array();
        $campi[] = "ROWID";
        $campi[] = "DOCNUM";
        $campi[] = "ITEKEY";
        $campi[] = "DOCUPL";
        $campi[] = "DOCNAME";
        $campi[] = "DOCPRI";
        $campi[] = "DOCMETA";

        $sql = "SELECT * FROM RICDOC WHERE ITEKEY IN (" . implode(",", $chiavi) . ") AND DOCNUM = '" . $CodiceRichiesta . "' ORDER BY ITEKEY";
        try {
            $Ricdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore acceso dati RICDOC: " . $exc->getMessage());
            return false;
        }
        if ($Ricdoc_tab) {
            $xml = $xml . "<RICDOC>\r\n";
            foreach ($Ricdoc_tab as $Ricdoc_rec) {

                $sql_check = "SELECT * FROM RICITE  WHERE RICNUM='" . $CodiceRichiesta . "' AND ITEKEY =  '" . $Ricdoc_rec['ITEKEY'] . "'";
                $Ricite_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql_check, false);
                if ($Ricite_rec['ITEIDR'] <> 0) {
                    continue;
                }

                if (strtolower(pathinfo($Ricdoc_rec['DOCUPL'], PATHINFO_EXTENSION)) == 'info') {
                    continue;
                }

                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    if ($Campo == 'DOCMETA') {
//                        $xml = $xml . "<$Campo>";
                        $arr_docmeta = unserialize($Ricdoc_rec[$Campo]);
                        foreach ($arr_docmeta as $Campo_meta => $Valore_meta) {
                            $xml = $xml . "<DOCMETA_$Campo_meta>$cdata_a" . $this->parseText($Valore_meta, ENT_NOQUOTES) . "$cdata_c</DOCMETA_$Campo_meta>\r\n";
                            if ($Campo_meta == "CLASSIFICAZIONE") {
                                $Anacla_rec = $this->praLib->GetAnacla($Valore_meta, 'codice');
                                $xml = $xml . "<DOCMETA_CLASSIFICAZIONE_DESCRIZIONE>$cdata_a" . $this->parseText($Anacla_rec['CLADES'], ENT_NOQUOTES) . "$cdata_c</DOCMETA_CLASSIFICAZIONE_DESCRIZIONE>\r\n";
                            }
                        }
//                         $xml = $xml . "</$Campo>\r\n";
                    } else {
                        $xml = $xml . "<$Campo>$cdata_a" . $this->parseText($Ricdoc_rec[$Campo], ENT_NOQUOTES) . "$cdata_c</$Campo>\r\n";
                    }
                }
                $xml = $xml . "<DOCSHA2SOST>$cdata_a" . $this->parseText($Ricite_rec['RICSHA2SOST'], ENT_NOQUOTES) . "$cdata_c</DOCSHA2SOST>\r\n";
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</RICDOC>\r\n";
        }
        $xml = $xml . "</RICHIESTADATI>\r\n";
        $xmlB64 = base64_encode($xml);
        return $xmlB64;
    }

    function GetRichiestaAllegatoForRowid($rowid) {
//        $retVisibilta = $this->praLib->GetVisibiltaSportello();
//        $whereVisibilta = '';
//        if ($retVisibilta['SPORTELLO'] != 0) {
//            $whereVisibilta .= " AND RICTSP = " . $retVisibilta['SPORTELLO'];
//        }
//        if ($retVisibilta['AGGREGATO'] != 0) {
//            $whereVisibilta .= " AND RICSPA = " . $retVisibilta['AGGREGATO'];
//        }

        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();
        $sql = "SELECT * FROM RICDOC WHERE ROWID = " . $rowid;
        $Ricdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Ricdoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Allegato non disponibile");
            return false;
        }

        $sqlTestata = "SELECT * FROM PRORIC WHERE RICNUM='" . $Ricdoc_rec['DOCNUM'] . "' " . $whereVisibilita;
        $Proric_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sqlTestata, false);
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta non disponibile");
            return false;
        }

        $ricPath = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms')) . "attachments/" . $Ricdoc_rec['DOCNUM'];
        $Ricdoc_rec['FILEPATH'] = $ricPath . "/" . $Ricdoc_rec['DOCUPL'];
        $fh = fopen($Ricdoc_rec['FILEPATH'], 'rb');
        if ($fh) {
            $binary = fread($fh, filesize($Ricdoc_rec['FILEPATH']));
            fclose($fh);
            return base64_encode($binary);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Lettura Allegato:' . $Ricdoc_rec['FILEPATH'] . ' fallita');
            return false;
        }
    }

    function RicercaPratiche($filtri) {
        $arrNomiFiltri = array("stato_proc", "dal_num", "al_num", "anno", "ric_siglaserie", "dal_numserie", "al_numserie", "annoserie", "da_data", "a_data", "sportello", "aggregato", "nprot", "annoprot",
            "tipo", "sezione", "foglio", "particella", "sub", "note", "codice");

        /*
         * Normalizzaizone Array
         */
        $arrFiltri = array();
        if (!isset($filtri['filtro'][0])) {
            $arrFiltri[] = $filtri['filtro'];
        } else {
            $arrFiltri = $filtri['filtro'];
        }

        /*
         * Controllo preliminare sulla correttezza dei filtri
         */
        foreach ($arrFiltri as $filtro) {
            if (!in_array(strtolower($filtro['chiave']), $arrNomiFiltri)) {
                $this->setErrCode(-1);
                $this->setErrMessage("La chiave " . $filtro['chiave'] . " non è stata riconosciuta");
                return false;
            } else {
//                if ($filtro['valore'] == "") {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("La chiave " . $filtro['chiave'] . " non ha il valore di ricerca");
//                    return false;
//                }
            }
        }

        $campiPad = array(
            "sezione" => 3,
            "foglio" => 4,
            "particella" => 5,
            "sub" => 4,
        );

        $sqlWhere = "";
        foreach ($arrFiltri as $filtro) {
            if ($campiPad[strtolower($filtro['chiave'])]) {
                $filtro['valore'] = str_pad($filtro['valore'], $campiPad[strtolower($filtro['chiave'])], "0", STR_PAD_LEFT);
            }

            if (strtolower($filtro['chiave']) == "stato_proc" && $filtro['valore']) {
                if ($filtro['valore'] == 'C') {
                    $sqlWhere .= " AND GESDCH <> ''";
                } else if ($filtro['valore'] == 'A') {
                    $sqlWhere .= " AND GESDCH = ''";
                }
            }

            if (strtolower($filtro['chiave']) == "anno" && $filtro['valore']) {
                $anno = $filtro['valore'];
                $sqlWhere .= " AND SUBSTRING(GESNUM,1,4) = '" . $filtro['valore'] . "'";
            }


            /*
             * Ricerca per numero fascicolo
             */
            if (strtolower($filtro['chiave']) == "dal_num" && $filtro['valore']) {
                $Dal_num = str_pad($filtro['valore'], 6, "0", STR_PAD_LEFT);
            }
            if (strtolower($filtro['chiave']) == "al_num" && $filtro['valore']) {
                $al_num = str_pad($filtro['valore'], 6, "0", STR_PAD_LEFT);
            }

            /*
             * Ricerca per numero e sigla Serie
             */
            if (strtolower($filtro['chiave']) == "ric_siglaserie" && $filtro['valore']) {
                $proLibSerie = new proLibSerie();
                $AnaserieArc_tab = $proLibSerie->GetSerie($filtro['valore'], 'sigla', true);
                if (!$AnaserieArc_tab) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("sigla serie non trovata");
                    return false;
                }
                if ($AnaserieArc_tab[0]['CODICE']) {
                    $sqlWhere .= " AND SERIECODICE = " . $AnaserieArc_tab[0]['CODICE'];
                }
            }

            if (strtolower($filtro['chiave']) == "dal_numserie" && $filtro['valore']) {
                $Dal_numserie = $filtro['valore'];
            }
            if (strtolower($filtro['chiave']) == "al_numserie" && $filtro['valore']) {
                $al_numserie = $filtro['valore'];
            }
            if (strtolower($filtro['chiave']) == "annoserie" && $filtro['valore']) {
                $sqlWhere .= " AND SERIEANNO = " . $filtro['valore'];
            }

            /*
             * Ricerca per data registrazione
             */
            $Da_data = "";
            if (strtolower($filtro['chiave']) == "da_data" && $filtro['valore']) {
                $Da_data = $filtro['valore'];
            }
            $a_data = "";
            if (strtolower($filtro['chiave']) == "a_data" && $filtro['valore']) {
                $a_data = $filtro['valore'];
            }

            /*
             * Ricerca per sportello on-line
             */
            if (strtolower($filtro['chiave']) == "sportello" && $filtro['valore']) {
                $sqlWhere .= " AND GESTSP = " . $filtro['valore'];
            }

            /*
             * Ricerca per sportello aggregato
             */
            if (strtolower($filtro['chiave']) == "aggregato" && $filtro['valore']) {
                $sqlWhere .= " AND GESSPA = " . $filtro['valore'];
            }

            /*
             * Ricerca per protocollo
             */
            if (strtolower($filtro['chiave']) == "nprot" && $filtro['valore']) {
                $NumProt = $filtro['valore'];
            }
            if (strtolower($filtro['chiave']) == "annoprot" && $filtro['valore']) {
                $AnnoProt = $filtro['valore'];
            }

            if (strtolower($filtro['chiave']) == "tipo" && $filtro['valore']) {
                $join1 = " PRAIMM.TIPO =  '" . $filtro['valore'] . "'";
            }

            if (strtolower($filtro['chiave']) == "sezione" && $filtro['valore']) {
                $join2 = ($join1) ? " AND" : "";
                $join2 .= " PRAIMM.SEZIONE =  '" . $filtro['valore'] . "'";
            }

            if (strtolower($filtro['chiave']) == "foglio" && $filtro['valore']) {
                $join3 = ($join2 || $join1) ? " AND" : "";
                $join3 .= " PRAIMM.FOGLIO =  '" . $filtro['valore'] . "'";
            }

            if (strtolower($filtro['chiave']) == "particella" && $filtro['valore']) {
                $join4 = ($join3 || $join2 || $join1) ? " AND" : "";
                $join4 .= " PRAIMM.PARTICELLA =  '" . $filtro['valore'] . "'";
            }

            if (strtolower($filtro['chiave']) == "sub" && $filtro['valore']) {
                $join5 = ($join3 || $join2 || $join1 || $join4) ? " AND" : "";
                $join5 .= " PRAIMM.SUBALTERNO =  '" . $filtro['valore'] . "'";
            }

            if (strtolower($filtro['chiave']) == "note" && $filtro['valore']) {
                $join6 = ($join3 || $join2 || $join1 || $join4 || $join5) ? " AND" : "";
                $join6 .= $this->PRAM_DB->strLower('PRAIMM.NOTE') . " LIKE  '%" . strtolower($filtro['valore']) . "%'";
            }

            if (strtolower($filtro['chiave']) == "codice" && $filtro['valore']) {
                $join7 = ($join3 || $join2 || $join1 || $join4 || $join5 || $join6) ? " AND" : "";
                $join7 .= " PRAIMM.CODICE =  '" . $filtro['valore'] . "'";
            }

            if ($join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7) {
                $joinImmobili = "INNER JOIN PRAIMM ON PROGES.GESNUM = PRAIMM.PRONUM AND " . $join1 . $join2 . $join3 . $join4 . $join5 . $join6 . $join7;
                $fieldListImmobili = "PRAIMM.CODICE,
                                      PRAIMM.TIPO,
                                      PRAIMM.FOGLIO,
                                      PRAIMM.PARTICELLA,
                                      PRAIMM.SUBALTERNO,
                                      PRAIMM.SEZIONE,
                                      PRAIMM.NOTE,";
            }
        }


        if ($Dal_num && $al_num) {
            $sqlWhere .= " AND (GESNUM BETWEEN '$anno$Dal_num' AND '$anno$al_num') ";
        }

        if ($Dal_numserie && $al_numserie) {
            $sqlWhere .= " AND (SERIEPROGRESSIVO BETWEEN '$Dal_numserie' AND '$al_numserie')";
        }

        if ($Da_data && $a_data) {
            $sqlWhere .= " AND (GESDRE BETWEEN '$Da_data' AND '$a_data')";
        }

        if ($NumProt) {
            if ($AnnoProt == "") {
                $sqlWhere .= " AND SUBSTR(PROGES.GESNPR,5) = '$NumProt'";
            } else {
                $sqlWhere .= " AND (PROGES.GESNPR = '$AnnoProt$NumProt' OR PROGES.GESNPR='$AnnoProt" . str_pad($NumProt, 6, "0", STR_PAD_LEFT) . "')";
            }
        }

        $Pratiche_tab = array();

        $sql = "
                SELECT
                    $fieldListImmobili
                    PROGES.GESNUM,
                    PROGES.GESPRA,
                    PROGES.SERIEPROGRESSIVO,
                    PROGES.SERIEANNO,
                    PROGES.GESNPR,
                    PROGES.GESDRI,
                    PROGES.GESDRE,
                    PROGES.GESTSP,
                    PROGES.GESSPA
                FROM
                    PROGES PROGES
                    $joinImmobili
                WHERE
                    1
                 $sqlWhere   
                GROUP BY GESNUM";
        //return $sql;
        try {
            $Pratiche_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            return false;
        }
        if ($Pratiche_tab) {
            $cdata_a = ""; //<![CDATA[";
            $cdata_c = ""; //]]>";
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
            $xml .= "<PRATICHE>\r\n";
            foreach ($Pratiche_tab as $Pratiche_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Pratiche_rec as $Chiave => $Valore) {
                    $xml = $xml . "<$Chiave>$cdata_a" . htmlspecialchars($Valore, ENT_COMPAT, "ISO-8859-1") . "$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRATICHE>\r\n";
            $xmlB64 = base64_encode($xml);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Pratiche non trovate");
            return false;
        }

        return $xmlB64;
    }

    function GetPraticaDati($NumeroPratica, $AnnoPratica, $rawDump = false) {
        $cdata_a = ""; //"<![CDATA[";
        $cdata_c = ""; //"]]>";

        $xml = "";
        $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
        $CodicePratica = $AnnoPratica . str_pad($NumeroPratica, 6, "0", STR_PAD_LEFT);
        if ($rawDump) {
            $Proges_rec = $this->praLib->GetProges($CodicePratica);
        } else {
            $sql = "
                SELECT
                    PROGES.GESNUM,
                    PROGES.GESPRA,
                    PROGES.SERIEANNO,
                    PROGES.SERIEPROGRESSIVO,
                    PROGES.SERIECODICE,
                    PROGES.GESPRO,
                    ANAPRA.PRADES__1 AS PRADES,                    
                    PROGES.GESRES,
                    PROGES.GESDRE,
                    PROGES.GESDRI,
                    PROGES.GESORA,
                    PROGES.GESDCH,
                    PROGES.GESPAR,
                    PROGES.GESNPR,
                    PROGES.GESNRC,
                    PROGES.GESTSP,
                    PROGES.GESSPA,
                    PROGES.GESEVE,
                    PROGES.GESNOT,
                    PROGES.GESDATAREG,
                    PROGES.GESORAREG,
                    PROGES.GESMETA
                FROM
                    PROGES PROGES
                LEFT OUTER JOIN
                    ANAPRA ANAPRA ON PROGES.GESPRO=ANAPRA.PRANUM                    
                WHERE
                    PROGES.GESNUM='$CodicePratica'";
            try {
                $Proges_rec = itaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }
        }
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica non disponibile');
            return false;
        }
        if (!$Proges_rec['GESDATAREG'] && !$Proges_rec['GESORAREG']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica non Acquisita Correttamente');
            return false;
        }

        $xml = $xml . "<PRATICADATI id=\"$CodicePratica\">\r\n";
        $xml = $xml . "<PROGES>\r\n";
        $xml = $xml . "<RECORD>\r\n";
        foreach ($Proges_rec as $Chiave => $Campo) {
            $Campo = $this->parseText($Campo, ENT_NOQUOTES);
            //$xml = $xml . "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
        }
        $xml = $xml . "</RECORD>\r\n";
        $xml = $xml . "</PROGES>\r\n";

        $sql = "SELECT * FROM ANADES WHERE DESNUM='" . $CodicePratica . "'";
        $Anades_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Anades_tab) {
            $xml = $xml . "<ANADES>\r\n";
            foreach ($Anades_tab as $Anades_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Anades_rec as $Chiave => $Campo) {
                    $Campo = $this->parseText($Campo, ENT_NOQUOTES);
                    $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</ANADES>\r\n";
        }

        /*
         * Passi
         */
        $campi = array();
        $campi[] = "PRONUM";
        $campi[] = "PROPRO";
        $campi[] = "PROSEQ";
        $campi[] = "PROPAK";
        $campi[] = "PRODTP";
        $campi[] = "PRODPA";
        $campi[] = "PRORIN";
        $campi[] = "PROPUB";
        $campi[] = "PROINI";
        $campi[] = "PROFIN";
        $campi[] = "PROALL";
        $campi[] = "PROOPE";
        $campi[] = "PROCLT";
        $campi[] = "PRODOW";
        $campi[] = "PRORPA";
        $campi[] = "PROIDR";
        $campi[] = "PRODRR";
        $campi[] = "PROUPL";
        $campi[] = "PROMLT";
        $campi[] = "PRODAT";
        $campi[] = "PROPRI";

        $chiavi = array();
        $chiavi[] = $CodicePratica;
        $chiavi_attach = array();
        //$sql = "SELECT * FROM PROPAS WHERE PRONUM='" . $CodicePratica . "' AND ((PROPUB = 1 AND (PROUPL = 1 OR PROMLT = 1 OR PRODAT = 1)) OR PRORIN<>'')";
        $sql = "SELECT * FROM PROPAS WHERE PRONUM='" . $CodicePratica . "'";
        $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Propas_tab) {
            $xml = $xml . "<PROPAS>\r\n";
            foreach ($Propas_tab as $Propas_rec) {
                $chiavi[] = "'" . $Propas_rec['PROPAK'] . "'";
                if ($Propas_rec['ITEIDR'] !== 1) {
                    $chiavi_attach[] = "'" . $Propas_rec['PROPAK'] . "'";
                }
                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    $xml = $xml . "<$Campo>$cdata_a" . $this->parseText($Propas_rec[$Campo], ENT_NOQUOTES) . "$cdata_c</$Campo>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PROPAS>\r\n";
        }


        /*
         * Dati Aggiuntivi
         */
        $campi = array();
        $campi[] = "DAGNUM";
        $campi[] = "DAGPAK";
        $campi[] = "DAGKEY";
        $campi[] = "DAGSET";
        $campi[] = "DAGVAL";
        $campi[] = "DAGSEQ";
        $campi[] = "DAGTIC";
        $sql = "SELECT * FROM PRODAG WHERE DAGPAK IN (" . implode(",", $chiavi) . ") AND DAGNUM = '" . $CodicePratica . "'";
        $Prodag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Prodag_tab) {
            $xml = $xml . "<PRODAG>\r\n";
            foreach ($Prodag_tab as $Prodag_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($campi as $Chiave => $Campo) {
                    //$xml = $xml . "<$Campo>$cdata_a" . $Prodag_rec[$Campo] . "$cdata_c</$Campo>\r\n";
                    $xml = $xml . "<$Campo>$cdata_a" . $this->parseText($Prodag_rec[$Campo], ENT_NOQUOTES) . "$cdata_c</$Campo>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRODAG>\r\n";
        }

        /*
         * Indice Allegati
         */
        $campi = array();
        $campi[] = "ROWID";
        $campi[] = "PASKEY";
        $campi[] = "PASFIL";
        $campi[] = "PASNAME";
        $campi[] = "PASNOT";
        $campi[] = "PASCLAS";
        $campi[] = "PASDEST";
        $campi[] = "PASNOTE";
        $sql = "SELECT * FROM PASDOC WHERE PASKEY IN (" . implode(",", $chiavi) . ") ORDER BY PASKEY";
        $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Propas_tab) {
            $xml = $xml . "<PASDOC>\r\n";
            foreach ($Propas_tab as $Propas_rec) {
                if (strtolower(pathinfo($Propas_rec['PASFIL'], PATHINFO_EXTENSION)) == 'info') {
                    continue;
                }
                $xml = $xml . "<RECORD>\r\n";
                //foreach ($campi as $Chiave => $Campo) {
                foreach ($Propas_rec as $Campo => $valore) {
                    if ($Campo == "PASDEST" && $Propas_rec[$Campo]) {
                        $dest = unserialize($Propas_rec[$Campo]);
                        $xml = $xml . "<$Campo>";
                        foreach ($dest as $key => $value) {
                            $value = $this->parseText($value, ENT_NOQUOTES);
                            $xml = $xml . "<DEST>$cdata_a" . $value . "$cdata_c</DEST>\r\n";
                        }
                        $xml = $xml . "</$Campo>\r\n";
                    } else {
                        $xml = $xml . "<$Campo>$cdata_a" . $this->parseText($Propas_rec[$Campo], ENT_NOQUOTES) . "$cdata_c</$Campo>\r\n";
                    }
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PASDOC>\r\n";
        }

        /*
         * Immobili
         */
        $sql = "SELECT * FROM PRAIMM WHERE PRONUM='" . $CodicePratica . "'";
        $Praimm_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Praimm_tab) {
            $xml = $xml . "<PRAIMM>\r\n";
            foreach ($Praimm_tab as $Praimm_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Praimm_rec as $Chiave => $Campo) {
                    $Campo = $this->parseText($Campo, ENT_NOQUOTES);
                    $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRAIMM>\r\n";
        }

        /*
         * Mittenti/Destinatari
         */
        $sql = "SELECT * FROM PRAMITDEST WHERE KEYPASSO LIKE '$CodicePratica%'";
        $Pramitdest_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Pramitdest_tab) {
            $xml = $xml . "<PRAMITDEST>\r\n";
            foreach ($Pramitdest_tab as $Pramitdest_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Pramitdest_rec as $Chiave => $Campo) {
                    $Campo = $this->parseText($Campo, ENT_NOQUOTES);
                    $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRAMITDEST>\r\n";
        }

        /*
         * Comunicazioni
         */
        $sql = "SELECT * FROM PRACOM WHERE COMNUM = '$CodicePratica'";
        $Pracom_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Pracom_tab) {
            $xml = $xml . "<PRACOM>\r\n";
            foreach ($Pracom_tab as $Pracom_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Pracom_rec as $Chiave => $Campo) {
                    $Campo = $this->parseText($Campo, ENT_NOQUOTES);
                    $xml = $xml . "<$Chiave>$cdata_a$Campo$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PRACOM>\r\n";
        }



        $xml = $xml . "</PRATICADATI>\r\n";
        $xmlB64 = base64_encode($xml);
        return $xmlB64;
    }

    function GetPraticaAllegatoForRowid($rowid) {
        $sql = "SELECT * FROM PASDOC WHERE ROWID = " . $rowid;

        $Pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Pasdoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Allegato non disponibile');
            return false;
        }
        $ext = pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION);
        $keyPasso = $Pasdoc_rec['PASKEY'];
        if (strlen($keyPasso) == 10) {
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PROGES", false, App::$utente->getKey('ditta'));
        } else {
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($keyPasso, 0, 4), $keyPasso, "PASSO", false, App::$utente->getKey('ditta'));
        }

        $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
        $fh = fopen($Pasdoc_rec['FILEPATH'], 'rb');
        if ($fh) {
            $binary = fread($fh, filesize($Pasdoc_rec['FILEPATH']));
            fclose($fh);
            return base64_encode($binary);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Lettura Allegato:' . $Pasdoc_rec['FILEPATH'] . ' fallita');
            return false;
        }
    }

    function PutAllegatoPassoForKeypasso($keyPasso, $allegato, $pubblicato) {
        $Propas_rec = $this->praLib->GetPropas($keyPasso, 'propak');
        if (!$Propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Passo non disponibile');
            return false;
        }

        if ($allegato) {

            if ($allegato['sha256digest']) {
                if (hash('sha256', base64_decode($allegato['stream'])) !== $allegato['sha256digest']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore controllo impronta file inviato: ' . $allegato['nomeFile']);
                    return false;
                }
            }

            $pasdoc_rec = array();
            $origFile = $allegato['nomeFile'];
            $uniqueName = uniqid(rand() * time());
            $randName = md5($uniqueName) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK'], "PASSO", true);
            $destFile = $pramPath . "/" . $randName;
            if (!@file_put_contents($destFile, base64_decode($allegato['stream']))) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore salvataggio file: ' . $allegato['nomeFile']);
                return false;
            }

            $pascla = "ESTERNO";
            if ($allegato['classificazione']) {
                $pascla = $allegato['classificazione'];
            }

            //valorizzo pasdoc
            $pasdoc_rec['PASKEY'] = $keyPasso;
            $pasdoc_rec['PASFIL'] = $randName;
            $pasdoc_rec['PASLNK'] = "allegato://" . $randName;
            $pasdoc_rec['PASNAME'] = $allegato['nomeFile'];
            $pasdoc_rec['PASUTC'] = "";
            $pasdoc_rec['PASUTE'] = "";
            $pasdoc_rec['PASNOT'] = $allegato['note'];
            $pasdoc_rec['PASCLA'] = $pascla;
            $pasdoc_rec['PASLOG'] = ""; //vuoto
            $pasdoc_rec['PASEVI'] = ""; //vuoto
            $pasdoc_rec['PASLOCK'] = ""; //vuoto
            $pasdoc_rec['PASCLAS'] = ""; //per ora in sospeso
            $pasdoc_rec['PASDEST'] = ""; //sospeso
            $pasdoc_rec['PASNOTE'] = "";
            $pasdoc_rec['PASPRTCLASS'] = ""; // vuoto
            $pasdoc_rec['PASPRTROWID'] = ""; // vuoto
            $pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $pasdoc_rec['PASORADOC'] = date("H:i:s");
            $pasdoc_rec['PASDATADOC'] = date("Ymd");
            $pasdoc_rec['PASDAFIRM'] = 0; // vuoto/0
            $pasdoc_rec['PASMETA'] = ""; // vuoto
            $pasdoc_rec['PASSHA2'] = hash_file('sha256', $destFile);
            $pasdoc_rec['PASPUB'] = 0;
            //if ($Propas_rec['PROPUBALL'] == 1) {
            $pasdoc_rec['PASPUB'] = $pubblicato; // Se è attiva la pubblicazione sul passo, se fosse, la attivo anche sull'allegato
            //}
            try {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento allegato " . $allegato['nomeFile'] . " al passo seq. " . $Propas_rec['PROSEQ']);
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento allegato " . $allegato['nomeFile'] . " al passo seq. " . $Propas_rec['PROSEQ'] . " ---> " . $e->getMessage());
                return false;
            }

            //
            //Aggiorno la variabile serializzata del passo aggiungendo il nuovo allegato
            //
            if ($Propas_rec['PROALL']) {
                $arrAlle = unserialize($Propas_rec['PROALL']);
            } else {
                $arrAlle = array();
            }
            $arrAlle[]['FILEORIG'] = $allegato['nomeFile'];
            $Propas_rec['PROALL'] = serialize($arrAlle);
            try {
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore Aggiornamento passo sequenza ' . $Propas_rec['PROSEQ']);
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore Aggiornamento passo sequenza ' . $Propas_rec['PROSEQ'] . " ---> " . $e->getMessage());
                return false;
            }

            return "Success";
        }
    }

    function AppendPassoPraticaArticolo($numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $pubblicaAllegatiArticolo, $utente, $password, $categoria, $titolo, $dadatapubbl, $daorapubbl, $adatapubbl, $aorapubbl, $corpo) {
        $CodicePratica = $annoPratica . str_pad($numeroPratica, 6, "0", STR_PAD_LEFT);
        $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $CodicePratica . "'";

        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica non disponibile');
            return false;
        }

        $dataApertura = date("Ymd");
        $retAppedSimple = $this->AppendPassoPraticaSimple($numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $dataApertura, '', '', '', 0, 0);
        if (!$retAppedSimple) {
            return false;
        }

        $Propas_rec = $this->praLib->GetPropas($retAppedSimple, 'propak');
        if (!$Propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nuovo passo non accessibile per compilazione dati Articolo');
            return false;
        }
        $Propas_rec['PROPART'] = 1;
        $Propas_rec['PROPFLALLE'] = $pubblicaAllegatiArticolo;
        $Propas_rec['PROPPASS'] = $password;
        $Propas_rec['PROPUSER'] = $utente;
        $Propas_rec['PROCAR'] = $categoria;
        $Propas_rec['PROPTIT'] = $titolo;
        $Propas_rec['PROPDADATA'] = $dadatapubbl;
        $Propas_rec['PROPPDAORA'] = $daorapubbl;
        $Propas_rec['PROPADDATA'] = $adatapubbl;
        $Propas_rec['PROPADORA'] = $aorapubbl;
        $Propas_rec['PROPCONT'] = base64_decode($corpo);
        try {
            $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in aggiornamento dati articolo');
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in aggiornamento dati articolo:' . $e->getMessage());
            return false;
        }

        $this->praLib->ordinaPassi($Proges_rec['GESNUM']);
        $this->praLib->sincronizzaStato($Proges_rec['GESNUM']);
        return $Propas_rec['PROPAK'];
    }

    function GetUrlPassoPratica($chiavePasso) {
        $filent_rec_url = $this->praLib->GetFilent(45);
        $url = $filent_rec_url['FILVAL'] . $chiavePasso;
        if (!file_get_contents($url)) {
            return "Url per il passo $chiavePasso non valido";
        } else {
            return base64_encode($url);
        }
    }

    function AppendPassoPraticaSimple($numeroPratica, $annoPratica, $annotazione, $descrizioneTipoPasso, $descrizionePasso, $dataApertura, $statoApertura, $dataChiusura, $statoChiusura, $pubblicaStatoPasso, $pubblicaAllegati) {
        //
        // Esiste pratica ?
        //
        $CodicePratica = $annoPratica . str_pad($numeroPratica, 6, "0", STR_PAD_LEFT);
        $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $CodicePratica . "'";

        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica non disponibile');
            return false;
        }
        //
        // Controllo validità degli stati
        //

        if ($statoApertura) {
            $Anastp_apri_rec = $this->praLib->GetAnastp($statoApertura);
            if (!$Anastp_apri_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Stato Apertura: ' . $statoApertura . ' non disponibile');
                return false;
            }
        }

        if ($statoChiusura) {
            $Anastp_chiudi_rec = $this->praLib->GetAnastp($statoChiusura);
            if (!$Anastp_chiudi_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Stato Chiusura: ' . $statoChiusura . ' non disponibile');
                return false;
            }
        }

        //
        // Valorizzo campi
        //
        $Propas_rec = array(); //$_POST[$this->nameForm . '_PROPAS'];
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PRORES'] = $Proges_rec['GESRES'];
        $Propas_rec['PRORPA'] = $Proges_rec['GESRES'];

        $Ananom_rec = $this->praLib->GetAnanom($Propas_rec['PRORES']);

        if ($Ananom_rec) {
            $Propas_rec['PROUOP'] = $Ananom_rec['NOMOPE'];
            $Propas_rec['PROSET'] = $Ananom_rec['NOMSET'];
            $Propas_rec['PROSER'] = $Ananom_rec['NOMSER'];
        }
        $Propas_rec['PRODTP'] = $descrizioneTipoPasso;
        $Propas_rec['PRODPA'] = $descrizionePasso;
        $Propas_rec['PROPAK'] = $this->praLib->PropakGenerator($Proges_rec['GESNUM']);
        $Propas_rec['PROINI'] = $dataApertura;
        $Propas_rec['PROSTAP'] = $statoApertura;
        $Propas_rec['PROFIN'] = $dataChiusura;
        $Propas_rec['PROSTCH'] = $statoChiusura;
        $Propas_rec['PROANN'] = $annotazione;
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROUTEADD'] = ""; // da desumere dal token
        $Propas_rec['PROPST'] = $pubblicaStatoPasso;
        $Propas_rec['PROPUBALL'] = $pubblicaAllegati;
        $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date("H:i:s");

        try {
            $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore Inserimento passo');
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Inserimento passo:' . $e->getMessage());
            return false;
        }

        $this->praLib->ordinaPassi($Proges_rec['GESNUM']);
        $this->praLib->sincronizzaStato($Proges_rec['GESNUM']);
        return $Propas_rec['PROPAK'];
    }

    //$ret = $wsAgent->AppendPassoPraticaPortale($numeroPratica, $annoPratica, $datiPasso, $allegato, $destinatari);
    function AppendPassoPraticaPortale($numeroPratica, $annoPratica, $datiPasso, $allegato = array(), $destinatari = array()) {
        //
        // Esiste pratica ?
        //
        $CodicePratica = $annoPratica . str_pad($numeroPratica, 6, "0", STR_PAD_LEFT);
        $sql = "SELECT * FROM PROGES WHERE GESNUM='" . $CodicePratica . "'";

        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica non disponibile');
            return false;
        }
        //
        // Controllo validità degli stati
        //

        if ($datiPasso['statoApertura']) {
            $Anastp_apri_rec = $this->praLib->GetAnastp($datiPasso['statoApertura']);
            if (!$Anastp_apri_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Stato Apertura: ' . $datiPasso['statoApertura'] . ' non disponibile');
                return false;
            }
        }

        if ($datiPasso['statoChiusura']) {
            $Anastp_chiudi_rec = $this->praLib->GetAnastp($datiPasso['statoChiusura']);
            if (!$Anastp_chiudi_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Stato Chiusura: ' . $datiPasso['statoChiusura'] . ' non disponibile');
                return false;
            }
        }

        //
        // Valorizzo campi
        //
        $Propas_rec = array(); //$_POST[$this->nameForm . '_PROPAS'];
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
        $Propas_rec['PROSEQ'] = 99999;
        $Propas_rec['PRORES'] = $Proges_rec['GESRES'];
        $Propas_rec['PRORPA'] = $Proges_rec['GESRES'];

        $Ananom_rec = $this->praLib->GetAnanom($Propas_rec['PRORES']);

        if ($Ananom_rec) {
            $Propas_rec['PROUOP'] = $Ananom_rec['NOMOPE'];
            $Propas_rec['PROSET'] = $Ananom_rec['NOMSET'];
            $Propas_rec['PROSER'] = $Ananom_rec['NOMSER'];
        }
        $Propas_rec['PRODTP'] = $datiPasso['descrizioneTipoPasso'];
        $Propas_rec['PRODPA'] = $datiPasso['descrizionePasso'];
        $Propas_rec['PROPAK'] = $this->praLib->PropakGenerator($Proges_rec['GESNUM']);
        $Propas_rec['PROINI'] = $datiPasso['dataApertura'];
        $Propas_rec['PROSTAP'] = $datiPasso['statoApertura'];
        $Propas_rec['PROFIN'] = $datiPasso['dataChiusura'];
        $Propas_rec['PROSTCH'] = $datiPasso['statoChiusura'];
        $Propas_rec['PROANN'] = $datiPasso['annotazione'];
        $Propas_rec['PRONUM'] = $Proges_rec['GESNUM'];
        $Propas_rec['PROUTEADD'] = ""; // da desumere dal token
        $Propas_rec['PROPST'] = $datiPasso['pubblicaStatoPasso'];
        $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date("H:i:s");

        try {
            $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $Propas_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore Inserimento passo');
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Inserimento passo:' . $e->getMessage());
            return false;
        }

        /**
         * @TODO
         * Inserire allegato e destinatari
         * 
         */
        /*
         * Inserisco allegato
         */
        if ($allegato) {
            $pasdoc_rec = array();
//            $allegato_rec = array();
            $origFile = $allegato['nomeFile'];
            $randName = md5(rand() * time()) . "." . pathinfo($origFile, PATHINFO_EXTENSION);
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK'], "PASSO", true);
//            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
            $destFile = $pramPath . "/" . $randName;
            if (!@file_put_contents($destFile, base64_decode($allegato['stream']))) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore salvataggio file:' . $e->getMessage());
                return false;
            }
            //valorizzo pasdoc
            $pasdoc_rec['PASKEY'] = $Propas_rec['PROPAK'];
            $pasdoc_rec['PASFIL'] = $randName;
            $pasdoc_rec['PASNAME'] = $allegato['nomeFile'];
            $pasdoc_rec['PASUTC'] = "";
            $pasdoc_rec['PASUTE'] = "";
            $pasdoc_rec['PASNOT'] = $allegato['note'];
            $pasdoc_rec['PASCLA'] = "ESTERNO";
            $pasdoc_rec['PASLOG'] = ""; //vuoto
            $pasdoc_rec['PASEVI'] = ""; //vuoto
            $pasdoc_rec['PASLOCK'] = ""; //vuoto
            $pasdoc_rec['PASCLAS'] = ""; //per ora in sospeso
            $pasdoc_rec['PASDEST'] = ""; //sospeso
            $pasdoc_rec['PASNOTE'] = "";
            $pasdoc_rec['PASPUB'] = 1;
            $pasdoc_rec['PASPRTCLASS'] = ""; // vuoto
            $pasdoc_rec['PASPRTROWID'] = ""; // vuoto
            $pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
            $pasdoc_rec['PASORADOC'] = date("H:i:s");
            $pasdoc_rec['PASDATADOC'] = date("Ymd");
            $pasdoc_rec['PASDAFIRM'] = 0; // vuoto/0
            $pasdoc_rec['PASMETA'] = ""; // vuoto
            $pasdoc_rec['PASSHA2'] = hash_file('sha256', $destFile);

            try {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PASDOC', 'ROWID', $pasdoc_rec);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore Inserimento documento');
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore Inserimento documento:' . $e->getMessage());
                return false;
            }
        }

        /*
         * Inserisco destinatari
         */
        if ($destinatari) {
            /*
             * vedi praPasso registraDestinatari()
             */
        }

        $this->praLib->ordinaPassi($Proges_rec['GESNUM']);
        $this->praLib->sincronizzaStato($Proges_rec['GESNUM']);
        return 'Success';
    }

    //
    // Funzioni private per la classe
    //
    private function GetAllegatiPratica($Proric_rec) {
        $Ricdoc_tab = $this->praLib->GetRicdoc($Proric_rec['RICNUM'], "codice", true);
        $pathAllegatiRichiste = $this->getPathAllegatiRichieste();
        $listAllegati = $this->GetFileList($pathAllegatiRichiste . "attachments/" . $Proric_rec['RICNUM']);
        if ($listAllegati) {
            //
            //Rimuovo dall'array gli allegati che non verranno importatati (
            //
            foreach ($listAllegati as $key1 => $allegato) {
                if (strpos("|info|html|txt|", "|" . pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION) . "|") !== false) {
                    unset($listAllegati[$key1]);
                }
                if (strpos($allegato['FILENAME'], "rapporto") != false) {
                    unset($listAllegati[$key1]);
                }
            }
        }

        $allegati = array();
        if ($Ricdoc_tab) {
            foreach ($listAllegati as $allegato) {
                $ext = strtolower(pathinfo($allegato['FILEINFO'], PATHINFO_EXTENSION));
                if ($ext == "xml") {
                    $allegati[] = array(
                        'DATAFILE' => $allegato['DATAFILE'],
                        'FILENAME' => $allegato['FILENAME'],
                        'FILEINFO' => $allegato['FILEINFO'],
                        'FIRMA' => ""
                    );
                }

                foreach ($Ricdoc_tab as $Ricdoc_rec) {
                    if ($Ricdoc_rec['DOCUPL'] == $allegato['FILEINFO']) {
                        $firma = "";
                        if ($ext == "p7m") {
                            $firma = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                        }
                        $allegati[] = array(
                            'DATAFILE' => $allegato['DATAFILE'],
                            'FILENAME' => $Ricdoc_rec['DOCNAME'],
                            'FILEINFO' => $Ricdoc_rec['DOCUPL'],
                            'FIRMA' => $firma
                        );
                        break;
                    }
                }
            }
        } else {
            $allegati = $listAllegati;
        }

        return $allegati;
    }

    private function getPathAllegatiRichieste() {
        return str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
    }

    private function GetFileList($filePath) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'DATAFILE' => $filePath . '/' . $obj,
                'FILENAME' => $obj,
                'FILEINFO' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    private function parseText($text, $quoting) {
        return htmlspecialchars($text, $quoting, 'ISO-8859-1');
    }

    function CancellaPratica($NumeroPratica, $AnnoPratica) {
        $NumeroPratica = str_pad($NumeroPratica, 6, "0", STR_PAD_LEFT);
        $praLibPratica = praLibPratica::getInstance();
        $ret_cancella = $praLibPratica->cancella($this, $NumeroPratica, $AnnoPratica);
        if ($ret_cancella !== false) {
            //return "Cancellazione della pratica $NumeroPratica/$AnnoPratica avvenuta con successo";
            return "Success";
        } else {
            return $praLibPratica->getErrMessage();
        }
    }

    function GetElencoPassi($Stato, $Responsabile, $TipoPasso) {
        $sql = "
                SELECT
                   PROGES.GESNUM,
                   PROGES.GESPRO,
                   PROGES.GESPRA," .
                $this->PRAM_DB->strConcat("PRADES__1", "PRADES__2", "PRADES__3") . " AS DESCRIZIONE,
                   PROGES.GESNPR,
                   PROGES.GESDRI,
                   PROGES.GESDRE,
                   PROPAS.PROPAK,
                   PROPAS.PRODPA,
                   PROPAS.PRORPA,
                   PROPAS.PROSTATO,
                   PROPAS.PROCLT,
                   PROPAS.PROINI,
                   PROPAS.PROFIN,
                   PROPAS.PROANN,
                   PRACOM.COMPRT,
                   PRACOM.COMDPR
                FROM
                    PROPAS
                LEFT OUTER JOIN PROGES ON PROGES.GESNUM = PROPAS.PRONUM
                LEFT OUTER JOIN ANAPRA ON PROGES.GESPRO = ANAPRA.PRANUM
                LEFT OUTER JOIN PRACOM ON PROPAS.PROPAK = PRACOM.COMPAK AND COMTIP = 'A'
                WHERE
                    GESDCH = '' AND
                    PROFIN = '' AND
                    PROPUB = 0
                ";
        if ($Stato) {
            $sql .= " AND PROSTATO = $Stato";
        }
        if ($Responsabile) {
            $sql .= " AND PRORPA = '$Responsabile'";
        }
        if ($TipoPasso) {
            $sql .= " AND PROCLT = '$TipoPasso'";
        }

        $sql .= " ORDER BY PROGES.GESNUM DESC";

        try {
            $Passi_tab = itaDB::DBSQLSelect($this->PRAM_DB, $sql);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore acceso dati: " . $exc->getMessage());
            return false;
        }

        if ($Passi_tab) {
            $cdata_a = ""; //<![CDATA[";
            $cdata_c = ""; //]]>";
            $xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n";
            $xml .= "<PASSIBO>\r\n";
            foreach ($Passi_tab as $Passi_rec) {
                $xml = $xml . "<RECORD>\r\n";
                foreach ($Passi_rec as $Chiave => $Valore) {
                    $xml = $xml . "<$Chiave>$cdata_a" . htmlspecialchars($Valore, ENT_COMPAT, "ISO-8859-1") . "$cdata_c</$Chiave>\r\n";
                }
                $xml = $xml . "</RECORD>\r\n";
            }
            $xml = $xml . "</PASSIBO>\r\n";
        }
        $xmlB64 = base64_encode($xml);
        return $xmlB64;
    }

    function AggiornaStatoPasso($codicePasso, $Stato, $dataApertura, $dataChiusura) {
        $propas_rec = $this->praLib->GetPropas($codicePasso, "propak");
        if (!$propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Passo non trovato.");
            return false;
        }

        if ($Stato) {
            $anastp_rec = $this->praLib->GetAnastp($Stato);
            if (!$anastp_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Stato Passo non trovato in anagrafica.Inserire uno stato valido.");
                return false;
            }
        }

        $propas_rec['PROSTATO'] = $Stato;
        $propas_rec['PROINI'] = $dataApertura;
        $propas_rec['PROFIN'] = $dataChiusura;
        try {
            $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $propas_rec);
            if ($nrow != 1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Aggiornamento passo con id $codicePasso");
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Aggiornamento passo con id $codicePasso" . " ---> " . $e->getMessage());
            return false;
        }
        return 'Success';
    }

    private function controllaAcquisizioneRichiesta($Proric_rec) {
        switch ($Proric_rec['RICSTA']) {
            case praLibRichiesta::RICSTA_INOLTRATA:
                return true;
            default:
                return false;
        }
        return true;
    }

    private function controllaCambioStatoRichiesta($Proric_rec, $statoRihiesta) {
        if (array_key_exists($statoRihiesta, praLibRichiesta::$RICSTA_DESCR)) {
            return true;
        } else {
            return false;
        }
    }

    function ResetAcquisizioneRichiesta($NumeroRichiesta, $AnnoRichiesta) {
        $Codice = $AnnoRichiesta . str_pad($NumeroRichiesta, 6, '0', STR_PAD_LEFT);
        $Proric_rec = $this->praLib->GetProric($Codice, 'codice');
        if (!$Proric_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta: $Codice,  non esistente");
            return false;
        }

        $Proric_rec['RICCONFDATA'] = "";
        $Proric_rec['RICCONFORA'] = "";
        $Proric_rec['RICCONFCONTEXT'] = "";
        $Proric_rec['RICCONFINFO'] = "";
        $Proric_rec['RICCONFUTE'] = "";

        $update_Info = "Reset conferma acquisizione:" . date("Ymd") . " su richiesta: " . $Codice;
        $retUpd = $this->updateRecord($this->PRAM_DB, 'PRORIC', $Proric_rec, $update_Info);
        if (!$retUpd) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore Aggiornamento stato richiesta ' . $Proric_rec['RICNUM']);
            return false;
        }
        return true;
    }

    function getRiciteTab($CodiceRichiesta) {
        $Ricite_tab = $ret = array();
        $proric_rec = $this->praLib->GetProric($CodiceRichiesta);
        if ($proric_rec['RICSTA'] == "01" || $proric_rec['RICSTA'] == "91") {

            /*
             * Leggo o Creo la path temporanea
             */
            $tempPath = itaLib::createAppsTempPath($CodiceRichiesta);
            if (!is_dir($tempPath)) {
                $ret['Status'] = "-1";
                $ret['Message'] = "Creazione Directory di lavoro temporanea della richiesta $CodiceRichiesta fallita.";
                $ret['RetValue'] = false;
                return $ret;
            }

            /*
             * Prendo il file XMLINFO
             */
            $base64XmlInfo = $this->GetXMLINFO($CodiceRichiesta);
            if (!$base64XmlInfo) {
                $ret['Status'] = "-1";
                $ret['Message'] = 'Impossibile recuperare il file XMLINFO';
                $ret['RetValue'] = false;
                return $ret;
            }

            $contentFile = base64_decode($base64XmlInfo);
            $fileXML = $tempPath . "/XMLINFO.xml";
            file_put_contents($fileXML, $contentFile);
            //
            $xmlObj = new QXML;
            $retXml = $xmlObj->setXmlFromFile($fileXML);
            if ($retXml == false) {
                $ret['Status'] = "-1";
                $ret['Message'] = "Errore apertura XML: $fileXML";
                $ret['RetValue'] = false;
                return $ret;
            }
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            $Ricite_tab_tmp = $arrayXml['RICITE']['RECORD'];
            foreach ($Ricite_tab_tmp as $Ricite_rec_tmp) {
                $Ricite_rec = $this->praLib->GetRicite($Ricite_rec_tmp['ROWID'][itaXML::textNode], "rowid");
                if ($Ricite_rec) {
                    $Ricite_tab[] = $Ricite_rec;
                }
            }
        } else {
            $sql = "SELECT * FROM RICITE  WHERE RICNUM='" . $CodiceRichiesta . "' AND ITEPUB = 1 AND (ITEUPL = 1 OR ITEMLT = 1 OR ITEDAT = 1)";
            try {
                $Ricite_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            } catch (Exception $exc) {
                $ret['Status'] = "-1";
                $ret['Message'] = "Errore acceso dati RICITE: " . $exc->getMessage();
                $ret['RetValue'] = false;
                return $ret;
            }
        }

        foreach ($Ricite_tab as $key => $passo_rec) {
            $praclt_rec = $this->praLib->GetPraclt($passo_rec['ITECLT']);
            $Ricite_tab[$key]['ITECLT_DESCRIZIONE'] = $praclt_rec['CLTDES'];
        }


        $ret['Status'] = "0";
        $ret['Message'] = "";
        $ret['RetValue'] = true;
        $ret['Ricite_tab'] = $Ricite_tab;
        return $ret;
    }

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function GetPraticaAllegatoFromTestoBase($pasfil) {
        $ritorno = array();

        $sql = "SELECT * FROM PASDOC WHERE PASFIL = '$pasfil'";

        $Pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Pasdoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Allegato non disponibile');
            return false;
        }

        if ($Pasdoc_rec['PASCLA'] != 'TESTOBASE') {
            $this->setErrCode(-1);
            $this->setErrMessage("L'allegato scelto non è un testo base");
            return false;
        }

        $pramPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], "PASSO", false, App::$utente->getKey('ditta'));
        $Pasdoc_rec['FILEPATH'] = $pramPath . "/" . $Pasdoc_rec['PASFIL'];

        $testoPath = pathinfo($Pasdoc_rec['FILEPATH'], PATHINFO_DIRNAME);
        $testoName = pathinfo($Pasdoc_rec['FILEPATH'], PATHINFO_FILENAME);
        if (file_exists($testoPath . "/" . $testoName . ".pdf.p7m")) {
            $nomeFile = $testoName . ".pdf.p7m";
        } else if (file_exists($testoPath . "/" . $testoName . ".pdf")) {
            $nomeFile = $testoName . ".pdf";
        } else {
            $ritorno['Status'] = "0";
            $ritorno['RetValue'] = true;
            return $ritorno;
        }

        if ($nomeFile) {
            $fh = fopen($testoPath . "/" . $nomeFile, 'rb');
            if ($fh) {
                $binary = fread($fh, filesize($testoPath . "/" . $nomeFile));
                fclose($fh);
                $ritorno['Status'] = "0";
                $ritorno['RetValue'] = true;
                $ritorno['stream'] = base64_encode($binary);
                $ritorno['filename'] = $nomeFile;
                return $ritorno;
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage('Lettura Allegato:' . $testoPath . '/' . $nomeFile . ' fallita');
                return false;
            }
        }
    }

    function SyncFascicolo($FascicoloJason) {
        $dati = base64_decode($FascicoloJason);
        
        //$arrayDati = json_decode($dati,true);
        $arrayDati = itaLib::utf8_decode_recursive(json_decode($dati,true));

        if (!is_array($arrayDati)){
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro passato non corretto. Il parametro deve essere un Array in formato JASON");
            return false;
            
        }
        
/*        
        echo $fascicolo; // print_r($arrayFascicolo, true);
        
        var_dump($fascicolo);
*/
        $fascicolo = $arrayDati['PROGES'][0];
        if (!$fascicolo){
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro passato non corretto. Il parametro deve contenere l'Array del Fascicolo");
            return false;
        }
        
        $codice = $fascicolo['GESNUM'];
        if (!$codice){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente il codice del fascicolo");
            return false;
        }
        $proges_rec = $this->praLib->GetProges($codice, 'codice');
        if ($proges_rec){
            // Si aggiorna record PROGES
            $fascicolo['ROWID'] = $proges_rec['ROWID'];
            try {
                //$nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROGES', 'ROWID', $proges_rec);
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROGES', 'ROWID', $fascicolo);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore 1 Aggiornamento fascicolo con gesnum = $codice");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Aggiornamento fascicolo con gesnum = $codice" . " ---> " . $e->getMessage());
                return false;
            }
            
        }
        else {
            // Si aggiunge record PROGES
            try {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PROGES', '', $fascicolo);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento fascicolo con gesnum = " . $codice);
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento fascicolo con gesnum = " . $codice  . " ---> " . $e->getMessage());
                return false;
            }
            
        }

        $rowId = -1;
        $progesN_rec = $this->praLib->GetProges($codice, 'codice');
        if ($progesN_rec){
            $rowId = $progesN_rec['ROWID'];
        }
        
        return $rowId;
    }
    
    
    function SyncPasso($PassoJason) {
        $dati = base64_decode($PassoJason);
        
        //$arrayDati = json_decode($dati,true);
        $arrayDati = itaLib::utf8_decode_recursive(json_decode($dati,true));

        if (!is_array($arrayDati)){
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro passato non corretto. Il parametro deve essere un Array in formato JASON");
            return false;
            
        }
        
        $passo = $arrayDati['PROPAS'][0];
        if (!$passo){
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro passato non corretto. Il parametro deve contenere l'Array del Passo");
            return false;
        }
        
        $codice = $passo['PROPAK'];
        if (!$codice){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente il codice del passo");
            return false;
        }
        $propas_rec = $this->praLib->GetPropas($codice, 'propak');
        if ($propas_rec){
            // Si aggiorna record PROGES
            $passo['ROWID'] = $propas_rec['ROWID'];
            try {
                //$nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $propas_rec);
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Aggiornamento passo con gesnum = $codice");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Aggiornamento passo con gesnum = $codice" . " ---> " . $e->getMessage());
                return false;
            }
            
        }
        else {
            // Si aggiunge record PROGES
            try {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, 'PROPAS', 'ROWID', $passo);
                if ($nrow != 1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento passo con gesnum = " . $codice);
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento passo con gesnum = " . $codice  . " ---> " . $e->getMessage() );
                //$this->setErrMessage("Errore Inserimento passo con gesnum = " . $codice  . " ---> " . $e->getMessage() . "\n" . print_r($passo,true) . "\n\n " . print_r($this->PRAM_DB, true) . "\n\n" . print_r(ItaDB::getTableDef($this->PRAM_DB, 'PROPAS'), true) . "\n\n" .print_r($this->PRAM_DB->getColumnsInfo('PROPAS'),true)  );
                return false;
            }
            
        }

        $rowId = -1;
        $propasN_rec = $this->praLib->GetPropas($codice, 'propak');
        if ($propasN_rec){
            $rowId = $propasN_rec['ROWID'];
        }
        
        return $rowId;
    }
    
    function SyncAllegatiInfo($propak) {

        if (!$propak){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente il codice del passo");
            return false;
            
        }
        
        $propas_rec = $this->praLib->GetPropas($propak, 'propak');
        if (!$propas_rec){
            $this->setErrCode(-1);
            $this->setErrMessage("Il passo con codice " . $propak . " non è stato trovato");
            return false;
        }

        $dati = array();
        
        
        $numero = 0;
        // Scorro gli eventuali allegati collegati al passo (PASDOC)
        $pasDoc_tab = $this->praLib->GetPasdoc($propak, 'codice', true);
        if ($pasDoc_tab){
            $numero = count($pasDoc_tab);
            foreach($pasDoc_tab as $key => $pasDoc_rec){
                
                $dati['PASDOC'][$key] = $pasDoc_rec;

            }
        }
        
        $dati['NUMERO'][0] = $numero;
        
        $rispostaJason = json_encode(itaLib::utf8_encode_recursive($dati));

        $risposta = base64_encode($rispostaJason);
        
        return $risposta;
    }
    
    function DeleteAllegatoPasso($propak, $pasSha2) {
        // Fare cancellazione Allegato
        if (!$propak){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente il codice del passo");
            return false;
            
        }

        if (!$pasSha2){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente l'impronta del file");
            return false;
            
        }

        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $propak . "' "
                . "AND PASSHA2 = '" . $pasSha2 . "' ";
        $pasdoc_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        if (!$pasdoc_rec){
            return true;
//            $this->setErrCode(-1);
//            $this->setErrMessage("L'allegato non è stato trovato nel passo con codice " . $propak . " ");
//            return false;
        }

        $dir = $this->praLib->SetDirectoryPratiche(substr($propak, 0, 4), $propak, "PASSO", false);
        $filename = $dir . "/" . $pasdoc_rec['PASFIL'];

        $delete_Info = 'Oggetto: Cancellazione allegato ' . $pasdoc_rec['PASFIL'] . " con impronta " . $pasdoc_rec['PASSHA2'];
        if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec['ROWID'], $delete_Info)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Cancellazione allegato non riuscita");
            return false;
        }
//        if ($this->flagAssegnazioniPasso) {
//            $this->togliAllaFirma($allegato['ROWID']);
//        }


        if (file_exists($filename)){
            if (!@unlink($filename)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Cancellazione allegato non riuscita - File non trovato");
                return false;
            }
            
        }
        
        
        return true;
        
    }

    function SyncAllegatiDelete($stream) {

        if (!$stream){
            $this->setErrCode(-1);
            $this->setErrMessage("Nel Parametro passato non è presente il file con l'elenco degli Articoli Pubblicati");
            return false;
            
        }


        // Salvo il file con i PROPAK nella directory di appoggio D:\works\phpDev\data\tmp\itaEngine
        $pathFile = itaLib::createAppsTempPath('propak');

        
        if (!@file_put_contents("$pathFile/propak.zip", base64_decode($stream))) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel decodificare il file ZIP" . $this->getErrMessage());
            return false;
        }
        
        if (!itaZip::Unzip("$pathFile/propak.zip", $pathFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel scompattare il file ZIP " . $this->getErrMessage());
            return false;
        }
        $lista = glob($pathFile . "/*.txt");
        if ($lista === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nella directory $pathFile non sono stati ritrovati file TXT" . $this->getErrMessage());
            return false;
        }
        
        $retIndex = null;
        foreach ($lista as $indice => $file) {
            if (strpos($file, 'propak') !== false) {
                $retIndex = $indice;
                break;
            }
        }

        if ($retIndex === null) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non trovato il file TXT con i PROPAK da aggiornare" . $this->getErrMessage());
            return false;
        }

        $fileTxt = $lista[$retIndex];




        $sql = "SELECT * FROM PROPAS WHERE PROPART = 1 ORDER BY PROPAK";
        $propas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);

        $nomeFileFO = $pathFile . "/PROPAK_FO.txt";

        unlink($nomeFileFO);

        // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
        //$dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);

        foreach ($propas_tab as $propas_rec) {
            file_put_contents($nomeFileFO, $propas_rec['PROPAK'] . "\n", FILE_APPEND);
        }
        
        // Se l'impronta del file ricevuto e quello generato è uguale, allora non ci sono articoli da spubblicare
        if (hash_file('sha256', $fileTxt) == hash_file('sha256', $nomeFileFO)){
            return true;
        }
        
        $contenuto = file_get_contents($fileTxt);

        $array = str_split($contenuto, 23);

        foreach ($propas_tab as $propas_rec) {

            $trovato = false;
            foreach ($array as $propakBO){
                if ($propakBO == $propas_rec['PROPAK']){
                    $trovato = true;
                    break;
                }
            }
            
            if (!$trovato){
                $pasdoc_tab = $this->praLib->GetPasdoc($propas_rec['PROPAK'], 'codice', true);
                if ($pasdoc_tab){
                    $dir = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", false);
                    // Si cancellano gli allegati del passo
                    foreach($pasdoc_tab as $pasdoc_rec){
                        
                        $filename = $dir . "/" . $pasdoc_rec['PASFIL'];

                        $delete_Info = 'Oggetto: Cancellazione allegato ' . $pasdoc_rec['PASFIL'] . " con impronta " . $pasdoc_rec['PASSHA2'];
                        if (!$this->deleteRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec['ROWID'], $delete_Info)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Cancellazione allegato non riuscita");
                            return false;
                        }

                        if (file_exists($filename)){
                            if (!@unlink($filename)) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Cancellazione allegato non riuscita - File non trovato");
                                return false;
                            }

                        }
                    }
                    
                }
                
                // Spubblicare il passo
                $propas_rec['PROPART'] = false;

                //$nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $propas_rec);
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'PROPAS', 'ROWID', $propas_rec);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Aggiornamento passo con PROPAK = " . $propas_rec['PROPAK']);
                    return false;
                }
                
                
                
            }
            
        }

        
        // Cancella directory utilizzata per salvare i files
        itaFileUtils::removeDir($pathFile);
        
       
        return true;
    }
    
}
