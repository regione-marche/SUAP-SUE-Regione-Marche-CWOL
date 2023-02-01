<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.01.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class praLibUpgrade {

    public $praLib;
    private $errMessage;
    private $errCode;

    function __construct() {
        $this->praLib = new praLib();
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

    public function creaXMLProcedimento($pranum) {
        $anapra_rec = $this->praLib->GetAnapra($pranum);
        $tipoEnte = $this->praLib->GetTipoEnte();
        if ($tipoEnte != "M" && $anapra_rec['PRASLAVE'] == 1) {
            $dbsuffix = $this->praLib->GetEnteMaster();
            $Pram_db = ItaDB::DBOpen('PRAM', $dbsuffix);
        }
        if ($Pram_db == "") {
            $Pram_db = $this->praLib->getPRAMDB();
        }
        $itepas_tab = $this->praLib->GetItepasDaMaster($Pram_db, $pranum, "codice", true, " ORDER BY ITESEQ");

        $xml .= "<PRAMXX>\r\n";

        //
        // Export di ANAPRA
        //
        $xml .= "<!-- Tabella ANAPRA -->\r\n";
        $xml .= "<ANAPRA ROWID=\"{$anapra_rec['ROWID']}\">\r\n";
        foreach ($anapra_rec as $Chiave => $Campo) {
            if ($Chiave == "PRADES__1" || $Chiave == "PRADES__2" || $Chiave == "PRADES__3") {
                //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
            } else {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
        }
        $xml .= "</ANAPRA>\r\n";


        //
        // Export ANATSP che sono in questo procedimento
        //
        $sql6 = " SELECT
                    ANATSP.*
                 FROM
                    ANATSP
                 LEFT OUTER JOIN
                    ANAPRA
                 ON
                    ANATSP.TSPCOD = ANAPRA.PRATSP
                 WHERE
                    ANAPRA.PRANUM = '$pranum'";
        $anatsp_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql6, false);
        $xml .= "<!-- Tabella ANATSP -->\r\n";
        $xml .= "<ANATSP ROWID=\"{$anatsp_rec['ROWID']}\">\r\n";
        foreach ($anatsp_rec as $Chiave => $Campo) {
            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
        }
        $xml .= "</ANATSP>\r\n";

        //
        // Export ANATIP che sono in questo procedimento
        //
        $sql3 = " SELECT
                    ANATIP.*
                 FROM
                    ANATIP
                 LEFT OUTER JOIN
                    ANAPRA
                 ON
                    ANATIP.TIPCOD = ANAPRA.PRATIP
                 WHERE
                    ANAPRA.PRANUM = '$pranum'";
        $anatip_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql3, false);
        $xml .= "<!-- Tabella ANATIP -->\r\n";
        $xml .= "<ANATIP ROWID=\"{$anatip_rec['ROWID']}\">\r\n";
        foreach ($anatip_rec as $Chiave => $Campo) {
            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
        }
        $xml .= "</ANATIP>\r\n";

        //
        // Export ANASET che sono in questo procedimento
        //
        $sql4 = " SELECT
                    ANASET.*
                 FROM
                    ANASET
                 LEFT OUTER JOIN
                    ANAPRA
                 ON
                    ANASET.SETCOD = ANAPRA.PRASTT
                 WHERE
                    ANAPRA.PRANUM = '$pranum'";
        $anaset_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql4, false);
        $xml .= "<!-- Tabella ANASET -->\r\n";
        $xml .= "<ANASET ROWID=\"{$anaset_rec['ROWID']}\">\r\n";
        foreach ($anaset_rec as $Chiave => $Campo) {
            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
        }
        $xml .= "</ANASET>\r\n";

        //
        // Export ANAATT che sono in questo procedimento
        //
        $sql5 = " SELECT
                    ANAATT.*
                 FROM
                    ANAATT
                 LEFT OUTER JOIN
                    ANAPRA
                 ON
                    ANAATT.ATTCOD = ANAPRA.PRAATT
                 WHERE
                    ANAPRA.PRANUM = '$pranum'";
        $anaatt_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql5, false);
        $xml .= "<!-- Tabella ANAATT -->\r\n";
        $xml .= "<ANAATT ROWID=\"{$anaatt_rec['ROWID']}\">\r\n";
        foreach ($anaatt_rec as $Chiave => $Campo) {
            //$xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            if ($Chiave == "ATTDES") {
                //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
            } else {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
        }
        $xml .= "</ANAATT>\r\n";


        //
        // Export di ITEPAS
        //
        $xml .= "<!-- Tabella ITEPAS -->\r\n";
        foreach ($itepas_tab as $itepas_rec) {
            $xml .= "<ITEPAS ROWID=\"{$itepas_rec['ROWID']}\">\r\n";
            foreach ($itepas_rec as $Chiave => $Campo) {
                switch ($Chiave) {
                    case "ITEMETA":
                    case "ITEATE":
                    case "ITEOBE":
                    case "ITEDES":
                    case "ITENOT":
                        //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                        $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                        break;
                    default:
                        $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                        break;
                }
            }
            $xml .= "</ITEPAS>\r\n";
        }

        //
        //  Export di ITEDAG
        //
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY ITDSEQ";
            $itedag_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itedag_tab) {
                $xml .= "<!-- Tabella ITEDAG -->\r\n";
                foreach ($itedag_tab as $Chiave => $itedag_rec) {
                    $xml .= "<ITEDAG ROWID=\"{$itedag_rec['ROWID']}\">\r\n";
                    foreach ($itedag_rec as $Chiave => $Campo) {
                        switch ($Chiave) {
                            case "ITDCTR":
                            case "ITDMETA":
                            case "ITDEXPROUT":
                            case "ITDLAB":
                            case "ITDKEY":
                            case "ITDALIAS":
                            case "ITDVAL":
                            case "ITDDES":
                                //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                                $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                                break;
                            default:
                                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                                break;
                        }
                    }
                    $xml .= "</ITEDAG>\r\n";
                }
            }
        }

        //
        //  Export di ITECONTROLLI
        //
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY SEQUENZA";
            $itecontrolli_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itecontrolli_tab) {
                $xml .= "<!-- Tabella ITECONTROLLI -->\r\n";
                foreach ($itecontrolli_tab as $itecontrolli_rec) {
                    $xml .= "<ITECONTROLLI ROWID=\"{$itecontrolli_rec['ROWID']}\">\r\n";
                    foreach ($itecontrolli_rec as $Chiave => $Campo) {
                        if ($Chiave == "ESPRESSIONE") {
                            $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                        } else {
                            $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                        }
                    }
                    $xml .= "</ITECONTROLLI>\r\n";
                }
            }
        }

        //
        // Export di ANPDOC
        //
        $anpdoc_tab = $this->praLib->GetAnpdocDaMaster($Pram_db, $pranum);
        if ($anpdoc_tab) {
            $xml .= "<!-- Tabella ANPDOC -->\r\n";
            foreach ($anpdoc_tab as $anpdoc_rec) {
                $xml .= "<ANPDOC ROWID=\"{$anpdoc_rec['ROWID']}\">\r\n";
                foreach ($anpdoc_rec as $Chiave => $Campo) {
                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                }
                $xml .= "</ANPDOC>\r\n";
            }
        }

        //
        // Export di ITEREQ
        //
        $itereq_tab = $this->praLib->GetItereqDaMaster($Pram_db, $pranum);
        if ($itereq_tab) {
            $xml .= "<!-- Tabella ITEREQ -->\r\n";
            foreach ($itereq_tab as $itereq_rec) {
                $xml .= "<ITEREQ ROWID=\"{$itereq_rec['ROWID']}\">\r\n";
                foreach ($itereq_rec as $Chiave => $Campo) {
                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                }
                $xml .= "</ITEREQ>\r\n";
            }
        }

        //
        // Export di ITENOR
        //
        $itenor_tab = $this->praLib->GetItenorDaMaster($Pram_db, $pranum);
        if ($itenor_tab) {
            $xml .= "<!-- Tabella ITENOR -->\r\n";
            foreach ($itenor_tab as $itenor_rec) {
                $xml .= "<ITENOR ROWID=\"{$itereq_rec['ROWID']}\">\r\n";
                foreach ($itenor_rec as $Chiave => $Campo) {
                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                }
                $xml .= "</ITENOR>\r\n";
            }
        }

        //
        // Export di ITEEVT (SOSPESO IN ATTESA DI PRALIBUPGRADE2)
        //
//        $iteevt_tab = $this->praLib->GetIteevtDaMaster($Pram_db, $pranum);
//        if ($iteevt_tab) {
//            $xml .= "<!-- Tabella ITEEVT -->\r\n";
//            foreach ($iteevt_tab as $iteevt_rec) {
//                $xml .= "<ITEEVT ROWID=\"{$iteevt_rec['ROWID']}\">\r\n";
//                foreach ($iteevt_rec as $Chiave => $Campo) {
//                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
//                }
//                $xml .= "</ITEEVT>\r\n";
//            }
//        }

        //
        // Export PRACLT che sono in questo procedimento
        //
        $sql = " SELECT
                    PRACLT.*
                 FROM
                    PRACLT
                 LEFT OUTER JOIN
                    ITEPAS
                 ON
                    ITEPAS.ITECLT = PRACLT.CLTCOD
                 WHERE
                    ITEPAS.ITECOD = '$pranum'
                 GROUP BY 
                    PRACLT.CLTCOD";
        $praclt_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        $xml .= "<!-- Tabella PRACLT -->\r\n";
        foreach ($praclt_tab as $praclt_rec) {
            $xml .= "<PRACLT ROWID=\"{$praclt_rec['ROWID']}\">\r\n";
            foreach ($praclt_rec as $Chiave => $Campo) {
                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
            }
            $xml .= "</PRACLT>\r\n";
        }

        //
        // Export ANANOR che sono in questo procedimento
        //
        $sql1 = " SELECT
                    ANANOR.*
                 FROM
                    ANANOR
                 LEFT OUTER JOIN
                    ITENOR
                 ON
                    ANANOR.NORCOD = ITENOR.NORCOD
                 WHERE
                    ITENOR.ITEPRA = '$pranum'";
        $ananor_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql1, true);
        $xml .= "<!-- Tabella ANANOR -->\r\n";
        foreach ($ananor_tab as $ananor_rec) {
            $xml .= "<ANANOR ROWID=\"{$ananor_rec['ROWID']}\">\r\n";
            foreach ($ananor_rec as $Chiave => $Campo) {
                if ($Chiave == "NORDES") {
                    //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                    $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                } else {
                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                }
            }
            $xml .= "</ANANOR>\r\n";
        }

        //
        // Export ANAREQ che sono in questo procedimento
        //
        $sql2 = " SELECT
                    ANAREQ.*
                 FROM
                    ANAREQ
                 LEFT OUTER JOIN
                    ITEREQ
                 ON
                    ANAREQ.REQCOD = ITEREQ.REQCOD
                 WHERE
                    ITEREQ.ITEPRA = '$pranum'";
        $anareq_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql2, true);
        $xml .= "<!-- Tabella ANAREQ -->\r\n";
        foreach ($anareq_tab as $anareq_req) {
            $xml .= "<ANAREQ ROWID=\"{$anareq_req['ROWID']}\">\r\n";
            foreach ($anareq_req as $Chiave => $Campo) {
                if ($Chiave == "REQDES") {
                    //$xml .= "<" . $Chiave . "><![CDATA[" . htmlentities($Campo) . "]]></" . $Chiave . ">\r\n";
                    $xml .= "<" . $Chiave . "><![CDATA[" . base64_encode($Campo) . "]]></" . $Chiave . ">\r\n";
                } else {
                    $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
                }
            }
            $xml .= "</ANAREQ>\r\n";
        }

        //
        // Export ANAEVENTI che sono in questo procedimento (SOSPESO IN ATTESA DI PRALIBUPGRADE2)
        //
//        $sql2 = " SELECT
//                    ANAEVENTI.*
//                 FROM
//                    ANAEVENTI
//                 LEFT OUTER JOIN
//                    ITEEVT
//                 ON
//                    ANAEVENTI.EVTCOD = ITEEVT.IEVCOD
//                 WHERE
//                    ITEEVT.ITEPRA = '$pranum'";
//        $anaeventi_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql2, true);
//        $xml .= "<!-- Tabella ANAEVENTI -->\r\n";
//        foreach ($anaeventi_tab as $anaeventi_req) {
//            $xml .= "<ANAEVENTI ROWID=\"{$anaeventi_req['ROWID']}\">\r\n";
//            foreach ($anaeventi_req as $Chiave => $Campo) {
//                $xml .= "<" . $Chiave . "><![CDATA[" . $Campo . "]]></" . $Chiave . ">\r\n";
//            }
//            $xml .= "</ANAEVENTI>\r\n";
//        }

        //
        //FINE PRAMXX
        //
        $xml .= "</PRAMXX>\r\n";


        $tempPath = itaLib::getAppsTempPath();
        $nome_file = $tempPath . "/exportProc_$pranum.xml";

        $fp = fopen($nome_file, 'w');
        if (!$fp) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile aprire il file xml $nome_file");
            return false;
        }

        //
        // Inizio Xml
        if (!fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere il file xml $nome_file");
            return false;
        }
        if (!fwrite($fp, "<PROCEDIMENTO>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere il procedimento nel file xml $nome_file");
            return false;
        }
        //
        // Dati DB
        //
        if (!fwrite($fp, $xml)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere dati db nel file xml $nome_file");
            return false;
        }

        if (!fwrite($fp, "<RISORSE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere risorse nel file xml $nome_file");
            return false;
        }
        //
        // FILE COLLEGATI A ITEWRD
        //
        if (!fwrite($fp, "<TESTI_ASSOCIATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere testi associati nel file xml $nome_file");
            return false;
        }
        foreach ($itepas_tab as $itepas_rec) {
            if ($itepas_rec['ITEWRD'] != '') {
                $ditta = App::$utente->getKey('ditta');
                $sorgente = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
                if (!is_dir($sorgente)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Directory non presente!');
                    return false;
                }
                $filePath = $sorgente . $itepas_rec['ITEWRD'];
                if (!file_exists($filePath)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File associato per il passo:{$itepas_rec['ITESEQ']} non trovato.");
                    return false;
                }
                if (!fwrite($fp, "<TESTO_ASSOCIATO ROWID=\"{$itepas_rec['ROWID']}\">\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Testo associati del passo:{$itepas_rec['ITESEQ']} non trovato.");
                    return false;
                }

                $nodeName = "<NAME>";
                $nodeName .= "<![CDATA[{$itepas_rec['ITEWRD']}]]>";
                $nodeName .= "</NAME>\r\n";
                if (!fwrite($fp, $nodeName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere il nome del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
                $fileSha = hash_file('sha256', $filePath);
                $nodeHash = "<HASH>";
                $nodeHash .= "<![CDATA[{$fileSha}]]>";
                $nodeHash .= "</HASH>\r\n";
                if (!fwrite($fp, $nodeHash)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere l'hash del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }

                $nodeStream = "<STREAM>";
                $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
                $nodeStream .= "</STREAM>\r\n";
                if (!fwrite($fp, $nodeStream)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere lo stream del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
                if (!fwrite($fp, "</TESTO_ASSOCIATO>\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere fine testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
            }
        }
        if (!fwrite($fp, "</TESTI_ASSOCIATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi associati");
            return false;
        }

        //
        // FILE COLLEGATI AL PROCEDIMENTO
        //
        if (!fwrite($fp, "<TESTI_ALLEGATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere inizio testi allegati");
            return false;
        }
        foreach ($anpdoc_tab as $anpdoc_rec) {
            $sorgente = $this->praLib->SetDirectoryProcedimenti($pranum, 'allegati');
            $filePath = $sorgente . "/" . $anpdoc_rec['ANPFIL'];
            if (!file_exists($filePath)) {
                $this->setErrCode(-1);
                $this->setErrMessage("File allegato per il procedimento:$pranum non trovato.");
                return false;
            }
            if (!fwrite($fp, "<TESTO_ALLEGATO ROWID=\"{$anpdoc_rec['ROWID']}\">\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
            $nodeName = "<NAME>";
            $nodeName .= "<![CDATA[{$anpdoc_rec['ANPFIL']}]]>";
            $nodeName .= "</NAME>\r\n";
            if (!fwrite($fp, $nodeName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere nome File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
            $fileSha = hash_file('sha256', $filePath);
            $nodeHash = "<HASH>";
            $nodeHash .= "<![CDATA[{$fileSha}]]>";
            $nodeHash .= "</HASH>\r\n";
            if (!fwrite($fp, $nodeHash)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere hash File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }

            $nodeStream = "<STREAM>";
            $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
            $nodeStream .= "</STREAM>\r\n";
            if (!fwrite($fp, $nodeStream)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere nome lo stream allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
            if (!fwrite($fp, "</TESTO_ALLEGATO>\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere fine testo allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
        }
        if (!fwrite($fp, "</TESTI_ALLEGATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi allegati");
            return false;
        }

        //
        // FILE ANANOR
        //
        if (!fwrite($fp, "<TESTI_NORMATIVE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere inizio testi normative");
            return false;
        }
        foreach ($ananor_tab as $ananor_rec) {
            $sorgente = Config::getPath('general.itaProc') . 'ente' . $ditta . '/normativa/';
            $filePath = $sorgente . $ananor_rec['NORFIL'];
            if (!file_exists($filePath)) {
                $this->setErrCode(-1);
                $this->setErrMessage("File normativa " . $ananor_rec['NORFIL'] . " per il procedimento:$pranum non trovato.");
                return false;
            }
            if (!fwrite($fp, "<NORMATIVA ROWID=\"{$ananor_rec['ROWID']}\">\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere la normativa {$ananor_rec['NORFIL']}");
                return false;
            }
            $nodeName = "<NAME>";
            $nodeName .= "<![CDATA[{$ananor_rec['NORFIL']}]]>";
            $nodeName .= "</NAME>\r\n";
            if (!fwrite($fp, $nodeName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere il name della normativa {$ananor_rec['NORFIL']}");
                return false;
            }
            $fileSha = hash_file('sha256', $filePath);
            $nodeHash = "<HASH>";
            $nodeHash .= "<![CDATA[{$fileSha}]]>";
            $nodeHash .= "</HASH>\r\n";
            if (!fwrite($fp, $nodeHash)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere l'hash della normativa {$ananor_rec['NORFIL']}");
                return false;
            }
            $nodeStream = "<STREAM>";
            $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
            $nodeStream .= "</STREAM>\r\n";
            if (!fwrite($fp, $nodeStream)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere lo stream della normativa {$ananor_rec['NORFIL']}");
                return false;
            }
            if (!fwrite($fp, "</NORMATIVA>\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere fine normativa");
                return false;
            }
        }
        if (!fwrite($fp, "</TESTI_NORMATIVE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi normative");
            return false;
        }

        //
        // FILE ANAREQ
        //
        if (!fwrite($fp, "<TESTI_REQUISITI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere inizio testi requisiti");
            return false;
        }
        foreach ($anareq_tab as $anareq_rec) {
            $sorgente = $sorgente = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
            $filePath = $sorgente . $anareq_rec['REQFIL'];
            if (!file_exists($filePath)) {
                $this->setErrCode(-1);
                $this->setErrMessage("File requisito " . $anareq_rec['REQFIL'] . " per il procedimento:$pranum non trovato.");
                return false;
            }
            if (!fwrite($fp, "<REQUISITO ROWID=\"{$anareq_rec['ROWID']}\">\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere il requisito {$anareq_rec['REQFIL']}");
                return false;
            }
            $nodeName = "<NAME>";
            $nodeName .= "<![CDATA[{$anareq_rec['REQFIL']}]]>";
            $nodeName .= "</NAME>\r\n";
            if (!fwrite($fp, $nodeName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere il name del testo requisito {$anareq_rec['REQFIL']}");
                return false;
            }
            $fileSha = hash_file('sha256', $filePath);
            $nodeHash = "<HASH>";
            $nodeHash .= "<![CDATA[{$fileSha}]]>";
            $nodeHash .= "</HASH>\r\n";
            if (!fwrite($fp, $nodeHash)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere l'hash del testo requisito {$anareq_rec['REQFIL']}");
                return false;
            }
            $nodeStream = "<STREAM>";
            $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
            $nodeStream .= "</STREAM>\r\n";
            if (!fwrite($fp, $nodeStream)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere lo stream del testo requisito {$anareq_rec['REQFIL']}");
                return false;
            }
            if (!fwrite($fp, "</REQUISITO>\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere fine requisito");
                return false;
            }
        }
        if (!fwrite($fp, "</TESTI_REQUISITI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi requisiti");
            return false;
        }

        if (!fwrite($fp, "</RISORSE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine risorse");
            return false;
        }
        if (!fwrite($fp, "</PROCEDIMENTO>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine procedimento");
            return false;
        }
        //
        // Fine Xml;
        //
        fclose($fp);
        return $nome_file;
    }

    public function estraiXMLProcedimento($xmlFile) {
        $dizionarioEncode = array(
            "ANAPRA" => array(
                "PRADES__1" => 'b64', //'ent',
                "PRADES__2" => 'b64', //'ent',
                "PRADES__3" => 'b64', //'ent'
            ),
            "ANAATT" => array(
                "ATTDES" => 'b64',
            ),
            "ITEPAS" => array(
                "ITEDES" => 'b64', //'ent',
                "ITENOT" => 'b64', //'ent',
                "ITEATE" => 'b64',
                "ITEOBE" => 'b64',
                "ITEMETA" => 'b64',
            ),
            "ITEDAG" => array(
                "ITDCTR" => 'b64',
                "ITDMETA" => 'b64',
                "ITDEXPROUT" => 'b64',
                "ITDLAB" => 'b64',
                "ITDKEY" => 'b64', //'ent',
                "ITDALIAS" => 'b64', //'ent',
                "ITDVAL" => 'b64', //,
                "ITDDES" => 'b64',
            ),
            "ITECONTROLLI" => array(
                "ESPRESSIONE" => 'b64',
            ),
            "ANAREQ" => array(
                "REQDES" => 'b64', //'ent',
            ),
            "ANANOR" => array(
                "NORDES" => 'b64', //'ent',
            ),
        );
        $dizionarioPRAM = array(
            "ANAPRA" => "Anapra_rec",
            "ITEPAS" => "Itepas_tab",
            "ITEDAG" => "Itedag_tab",
            "ANPDOC" => "Anpdoc_tab",
            "ITECONTROLLI" => "Itecontrolli_tab",
            "ITEREQ" => "Itereq_tab",
            "ITENOR" => "Itenor_tab",
//            "ITEEVT" => "Iteevt_tab",
            "PRACLT" => "Praclt_tab",
            "ANANOR" => "Ananor_tab",
            "ANAREQ" => "Anareq_tab",
//            "ANAEVENTI" => "Anaeventi_tab",
            "ANATSP" => "Anatsp_rec",
            "ANATIP" => "Anatip_rec",
            "ANASET" => "Anaset_rec",
            "ANAATT" => "Anaatt_rec",
        );
        $dizionarioRISORSE = array(
            "TESTO_ALLEGATO" => "testiAllegatiXml_tab",
            "TESTO_ASSOCIATO" => "testiAssociatiXml_tab",
            "REQUISITO" => "testiRequisitiXml_tab",
            "NORMATIVA" => "testiNormativeXml_tab"
        );
        $array = array();
        $xml = new XMLReader();
        if (!$xml->open($xmlFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML : $xmlFile. Impossibile Aprire il file.");
            return false;
        }
        $ii = 0;
        while (true) {
            if (!$xml->read()) {
                break;
            }
            if ($xml->nodeType == XMLReader::ELEMENT) {
                if (!$xml->hasAttributes) {
                    continue;
                }
                if ($dizionarioPRAM[$xml->name]) {
                    if (!$this->elaboraXMLTag($xml, $dizionarioPRAM, $dizionarioEncode, $array)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore: " . $this->errMessage);
                        return false;
                    }
                    $ii++;
                    continue;
                }
                if ($dizionarioRISORSE[$xml->name]) {
                    if (!$this->elaboraXMLRisorse($xml, $dizionarioRISORSE, $array)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Risorsa: " . $this->errMessage);
                        return false;
                    }
                    $ii++;
                    continue;
                }
            }
        }
        return $array;
    }

    public function elaboraXMLRisorse($xml, $dizionario, &$array) {
        $xmlRisorsa = new XMLReader();
        if (!$xmlRisorsa->XML($xml->readOuterXml())) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML : $xml->name. Impossibile Aprire il file.");
            return false;
        }
        if ($this->seekNode($xmlRisorsa, "NAME")) {
            $xmlRisorsa->read();
            $risorsaXml_rec['NAME'] = $xmlRisorsa->value;
        }
        if ($this->seekNode($xmlRisorsa, "HASH")) {
            $xmlRisorsa->read();
            $risorsaXml_rec['HASH'] = $xmlRisorsa->value;
        }
        if ($this->seekNode($xmlRisorsa, "STREAM")) {
            $xmlRisorsa->read();
            $risorsaXml_rec['STREAM'] = $xmlRisorsa->value;
        }
        $array[$dizionario[$xml->name]][] = $risorsaXml_rec;
        return true;
    }

    public function elaboraXMLTag($xml, $dizionario, $dizionarioEnc, &$array) {
        $itaXMLObj = new itaXML;
        $retITAXml = $itaXMLObj->setXmlFromString($xml->readOuterXml());
        if (!$retITAXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML : $xml->name. Impossibile leggere il testo nell'xml.");
            return false;
        }
        $arrayXml = $itaXMLObj->getArray();
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML : $xml->name. Impossibile estrarre i dati.");
            return false;
        }
        foreach ($arrayXml[$xml->name][0] as $campo => $arrayValue) {
            if ($campo == '@attributes') {
                continue;
            }
            switch ($dizionarioEnc[$xml->name][$campo]) {
                case 'ent':
                    $Record_rec[$campo] = html_entity_decode($arrayValue[0]['@textNode']);
                    break;
                case 'b64':
                    $Record_rec[$campo] = base64_decode($arrayValue[0]['@textNode']);
                    break;
                default:
                    $Record_rec[$campo] = $arrayValue[0]['@textNode'];
                    break;
            }
        }

        $array[$dizionario[$xml->name]][] = $Record_rec;
        return true;
    }

    public function controllaPassiTemplate($Itepas_tab) {
        $arrayProc_template = array();
        foreach ($Itepas_tab as $itepas_rec) {
            $Itepas_rec_template = $Anapra_rec_template = array();
            if ($itepas_rec['TEMPLATEKEY']) {
                $Itepas_rec_template = $this->praLib->GetItepas($itepas_rec['TEMPLATEKEY'], "itekey");
                if ($Itepas_rec_template) {
                    $Anapra_rec_template = $this->praLib->GetAnapra($Itepas_rec_template['ITECOD']);
                }
                $arrayProc_template[$itepas_rec['ITEKEY']]["ITEPAS"] = $itepas_rec;
                $arrayProc_template[$itepas_rec['ITEKEY']]["PASSO_TEMPLATE"] = $Itepas_rec_template;
                $arrayProc_template[$itepas_rec['ITEKEY']]["PROC_TEMPLATE"] = $Anapra_rec_template;
            }
        }
        return $arrayProc_template;
    }

    public function controllaTestiAssociati($testiAssociatiXml_tab) {
        $destinazione = Config::getPath('general.itaProc') . 'ente' . App::$utente->getKey('ditta') . '/testiAssociati/';
        $testiAssociati = $this->praLib->GetFileList($destinazione);
        $testiAggiornabili = $testiNonAggiornabili = $testiNuovi = array();
        foreach ($testiAssociatiXml_tab as $testiAssociatiXml_rec) {
            if (!file_exists($destinazione . $testiAssociatiXml_rec['NAME'])) {
                $testiNuovi[] = $testiAssociatiXml_rec;
            }
        }
        foreach ($testiAssociatiXml_tab as $testiAssociatiXml_rec) {
            foreach ($testiAssociati as $testo) {
                if ($testiAssociatiXml_rec['NAME'] == $testo['FILENAME']) {
                    if ($testiAssociatiXml_rec['HASH'] == $testo['HASH']) {
                        $testiAggiornabili[] = $testiAssociatiXml_rec;
                    } else {
                        $testiNonAggiornabili[] = $testiAssociatiXml_rec;
                    }
                }
            }
        }
        return array(
            "testiNuovi" => $testiNuovi,
            "testiAggiornabili" => $testiAggiornabili,
            "testiNonAggiornabili" => $testiNonAggiornabili,
        );
    }

    public function acquisisciXMLProcedimento($arrayCtr, $arrayTesti) {
        $Anapra_rec_ctr = $this->praLib->GetAnapra($arrayCtr["Anapra_rec"][0]['PRANUM']);
        //
        // Aggiorno Anapra
        //
        if ($Anapra_rec_ctr) {
            $oldRowid = $Anapra_rec_ctr["ROWID"];
            //$anapra_rec = $Anapra_rec_ctr;
            $anapra_rec = $arrayCtr["Anapra_rec"][0];
            $anapra_rec['ROWID'] = $oldRowid;
            try {
                ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ANAPRA", "ROWID", $anapra_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        } else {
            $anapra_rec = $arrayCtr["Anapra_rec"][0];
            unset($anapra_rec['ROWID']);
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAPRA", "ROWID", $anapra_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco ITEPAS 
        //
        if ($Anapra_rec_ctr) {
            $Itepas_tab = $this->praLib->GetItepas($Anapra_rec_ctr['PRANUM'], "codice", true);
            foreach ($Itepas_tab as $Itepas_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEPAS', 'ROWID', $Itepas_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itepas_tab'] as $keyPasso => $itepas_rec) {
            unset($arrayCtr['Itepas_tab'][$keyPasso]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $itepas_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco ITEDAG 
        //
        if ($Anapra_rec_ctr) {
            $Itedag_tab = $this->praLib->GetItedag($Anapra_rec_ctr['PRANUM'], "codice", true);
            foreach ($Itedag_tab as $Itedag_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEDAG', 'ROWID', $Itedag_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itedag_tab'] as $keyDag => $itedag_rec) {
            unset($arrayCtr['Itedag_tab'][$keyDag]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEDAG", "ROWID", $itedag_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco ANPDOC 
        //
        if ($Anapra_rec_ctr) {
            $Anpdoc_tab = $this->praLib->GetAnpdoc($Anapra_rec_ctr['PRANUM']);
            foreach ($Anpdoc_tab as $Anpdoc_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ANPDOC', 'ROWID', $Anpdoc_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Anpdoc_tab'] as $keyDoc => $anpdoc_rec) {
            unset($arrayCtr['Anpdoc_tab'][$keyDoc]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANPDOC", "ROWID", $anpdoc_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco ITECONTROLLI 
        //
        if ($Anapra_rec_ctr) {
            $Itecontrolli_tab = $this->praLib->GetItecontrolli($Anapra_rec_ctr['PRANUM'], "itecod");
            foreach ($Itecontrolli_tab as $Itecontrolli_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITECONTROLLI', 'ROWID', $Itecontrolli_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itecontrolli_tab'] as $keyControlli => $itecontrolli_rec) {
            unset($arrayCtr['Itecontrolli_tab'][$keyControlli]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITECONTROLLI", "ROWID", $itecontrolli_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco ITEREQ
        //
        
        if ($Anapra_rec_ctr) {
            $Itereq_tab = $this->praLib->GetItereq($Anapra_rec_ctr['PRANUM'], "codice", true);
            if ($Itereq_tab) {
                foreach ($Itereq_tab as $Itereq_rec) {
                    try {
                        $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEREQ', 'ROWID', $Itereq_rec['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore: " . $e->getMessage());
                        return false;
                    }
                }
            }
        }
        foreach ($arrayCtr['Itereq_tab'] as $keyReq => $itereq_rec) {
            unset($arrayCtr['Itereq_tab'][$keyReq]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEREQ", "ROWID", $itereq_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Controllo e Inserisco ITENOR
        //
        if ($Anapra_rec_ctr) {
            $Itenor_tab = $this->praLib->GetItenor($Anapra_rec_ctr['PRANUM'], "codice", true);
            foreach ($Itenor_tab as $Itenor_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITENOR', 'ROWID', $Itenor_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itenor_tab'] as $keyNor => $itenor_rec) {
            unset($arrayCtr['Itenor_tab'][$keyNor]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITENOR", "ROWID", $itenor_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Controllo e Inserisco ITEEVT (SOSPESO IN ATTESA DI PRALIBUPGRADE2)
        //
//        if ($Anapra_rec_ctr) {
//            $Iteevt_tab = $this->praLib->GetIteevt($Anapra_rec_ctr['PRANUM'], "codice", true);
//            foreach ($Iteevt_tab as $Iteevt_rec) {
//                try {
//                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEEVT', 'ROWID', $Iteevt_rec['ROWID']);
//                    if ($nrow == 0) {
//                        return false;
//                    }
//                } catch (Exception $e) {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("Errore: " . $e->getMessage());
//                    return false;
//                }
//            }
//        }
//        foreach ($arrayCtr['Iteevt_tab'] as $keyEvt => $iteevt_rec) {
//            unset($arrayCtr['Iteevt_tab'][$keyEvt]['ROWID']);
//            try {
//                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEEVT", "ROWID", $iteevt_rec);
//                if ($nrow != 1) {
//                    return false;
//                }
//            } catch (Exception $e) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Errore: " . $e->getMessage());
//                return false;
//            }
//        }

        //
        // Se non ci sono, inserisco tipo passi PRACLT
        //
        foreach ($arrayCtr['Praclt_tab'] as $keyClt => $praclt_rec) {
            $praclt_rec_ctr = $this->praLib->GetPraclt($praclt_rec['CLTCOD']);
            if (!$praclt_rec_ctr) {
                unset($arrayCtr['Praclt_tab'][$keyClt]['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "PRACLT", "ROWID", $praclt_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco normative ANANOR
        //
        foreach ($arrayCtr['Ananor_tab'] as $keyNor => $ananor_rec) {
            $ananor_rec_ctr = $this->praLib->GetAnanor($ananor_rec['NORCOD']);
            if (!$ananor_rec_ctr) {
                unset($arrayCtr['Ananor_tab'][$keyNor]['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANANOR", "ROWID", $ananor_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco requisiti ANAREQ
        //
        foreach ($arrayCtr['Anareq_tab'] as $keyReq => $anareq_rec) {
            $anareq_rec_ctr = $this->praLib->GetAnareq($anareq_rec['REQCOD']);
            if (!$anareq_rec_ctr) {
                unset($arrayCtr['Anareq_tab'][$keyReq]['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAREQ", "ROWID", $anareq_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco requisiti ANAEVENTI (SOSPESO IN ATTESA DI PRALIBUPGRADE2)
        //
//        foreach ($arrayCtr['Anaeventi_tab'] as $keyAevt => $anaeventi_rec) {
//            $anaeventi_rec_ctr = $this->praLib->GetAnaeventi($anaeventi_rec['EVTCOD']);
//            if (!$anaeventi_rec_ctr) {
//                unset($arrayCtr['Anaeventi_tab'][$keyAevt]['ROWID']);
//                try {
//                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAEVENTI", "ROWID", $anaeventi_rec);
//                    if ($nrow != 1) {
//                        return false;
//                    }
//                } catch (Exception $e) {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("Errore: " . $e->getMessage());
//                    return false;
//                }
//            }
//        }

        //
        // Se non ci sono, inserisco Tipologia ANATSP
        //
        $anatsp_rec_ctr = $this->praLib->GetAnatsp($arrayCtr["Anatsp_rec"][0]['TSPCOD']);
        if (!$anatsp_rec_ctr) {
            unset($arrayCtr['Anatsp_rec'][0]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANATSP", "ROWID", $arrayCtr["Anatsp_rec"][0]);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco Tipologia ANATIP
        //
        $anatip_rec_ctr = $this->praLib->GetAnatip($arrayCtr["Anatip_rec"][0]['TIPCOD']);
        if (!$anatip_rec_ctr) {
            unset($arrayCtr['Anatip_rec'][0]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANATIP", "ROWID", $arrayCtr["Anatip_rec"][0]);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco Settore ANASET
        //
        $anaset_rec_ctr = $this->praLib->GetAnaset($arrayCtr["Anaset_rec"][0]['SETCOD']);
        if (!$anaset_rec_ctr) {
            unset($arrayCtr['Anaset_rec'][0]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANASET", "ROWID", $arrayCtr["Anaset_rec"][0]);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco Attivita ANAATT
        //
        $anaatt_rec_ctr = $this->praLib->GetAnaatt($arrayCtr["Anaatt_rec"][0]['ATTCOD']);
        if (!$anaatt_rec_ctr) {
            unset($arrayCtr['Anaatt_rec'][0]['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAATT", "ROWID", $arrayCtr["Anaatt_rec"][0]);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore: " . $e->getMessage());
                return false;
            }
        }

        //
        // Cancello, Inserisco Testi Allegati
        //
        if ($Anapra_rec_ctr) {
            $pathAllegati = $this->praLib->SetDirectoryProcedimenti($Anapra_rec_ctr['PRANUM']);
            $allegatiProc = $this->praLib->GetFileList($pathAllegati);
            foreach ($allegatiProc as $allegato) {
                if (!@unlink($allegato['FILEPATH'])) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore cancellazione allegato " . $allegato['FILEPATH']);
                    return false;
                }
            }
        } else {
            $pathAllegati = $this->praLib->SetDirectoryProcedimenti($arrayCtr["Anapra_rec"][0]['PRANUM']);
        }
        foreach ($arrayCtr["testiAllegatiXml_tab"] as $Allegato) {
            if (!@file_put_contents($pathAllegati . "/" . $Allegato['NAME'], base64_decode($Allegato['STREAM']))) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in salvataggio del file allegato " . $Allegato['NAME']);
                return false;
            }
        }

        //
        // Inserisco Testi Associati
        //
        $ditta = App::$utente->getKey('ditta');
        $destTestiAssociati = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
        if (!is_dir($destTestiAssociati)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Directory Testi Associati non presente!');
            return false;
        }
        //Aggiorno Nuovi
        foreach ($arrayTesti["testiNuovi"] as $Allegato) {
            if (!@file_put_contents($destTestiAssociati . $Allegato['NAME'], base64_decode($Allegato['STREAM']))) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in salvataggio del file nuovo " . $Allegato['NAME']);
                return false;
            }
        }

        //
        // Inserisco Testi Normative
        //Aggiungo i nuovi file, non viene controllata l'esistenza precedente
        $destNormative = Config::getPath('general.itaProc') . 'ente' . $ditta . '/normativa/';
        if (!is_dir($destNormative)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Directory Normative non presente!');
            return false;
        }
        foreach ($arrayCtr["testiNormativeXml_tab"] as $normativa) {
            if (!file_exists($destNormative . $normativa['NAME'])) {
                if (!@file_put_contents($destNormative . $normativa['NAME'], base64_decode($normativa['STREAM']))) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore", "Errore in salvataggio del file Normativa " . $normativa['NAME']);
                    return false;
                }
            }
        }

        //
        // Inserisco Testi Requisiti
        //Aggiungo i nuovi file, non viene controllata l'esistenza precedente
        $destRequisiti = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
        if (!is_dir($destRequisiti)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Directory Requisiti non presente!');
            return false;
        }
        foreach ($arrayCtr["testiRequisitiXml_tab"] as $requisiti) {
            if (!file_exists($destRequisiti . $requisiti['NAME'])) {
                if (!@file_put_contents($destRequisiti . $requisiti['NAME'], base64_decode($requisiti['STREAM']))) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in salvataggio del file Requisiti " . $requisiti['NAME']);
                    return false;
                }
            }
        }
        return true;
    }

    public function estraiNodiXml($xml, $nodeName, $mode = 'first', $content = 'outer') {
        switch ($mode) {
            case 'first':
                if (!$this->seekNode($xml, $nodeName)) {
                    $xml->close();
                    return false;
                }
                if ($content == 'outer') {
                    return $xml->readOuterXml();
                } else {
                    return $xml->readInnerXml();
                }

                break;
            case 'next':
                if (!$xml->next($nodeName)) {
                    return false;
                }
                if ($content == 'outer') {
                    return $xml->readOuterXml();
                } else {
                    return $xml->readInnerXml();
                }
                break;
            default:
                return false;
                break;
        }
    }

    private function seekNode(&$xml, $nodeName) {
        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $nodeName) {
                return $xml;
            }
        }
        return false;
    }

//    private function getNodeValue($xml) {
//        while ($xml->read()) {
//            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $nodeName) {
//                return $xml;
//            }
//        }
//        return false;
//    }

    function CtrImportFields($arrayCtr) {
        try {
            $arrayAnapraDBFields = $this->praLib->getPRAMDB()->getTableObject("ANAPRA")->getFields();
            $arrayItepasDBFields = $this->praLib->getPRAMDB()->getTableObject("ITEPAS")->getFields();
            $arrayItedagDBFields = $this->praLib->getPRAMDB()->getTableObject("ITEDAG")->getFields();
            $arrayAnpdocDBFields = $this->praLib->getPRAMDB()->getTableObject("ANPDOC")->getFields();
            $arrayItereqDBFields = $this->praLib->getPRAMDB()->getTableObject("ITEREQ")->getFields();
            $arrayItenorDBFields = $this->praLib->getPRAMDB()->getTableObject("ITENOR")->getFields();
            $arrayItecontrolliDBFields = $this->praLib->getPRAMDB()->getTableObject("ITECONTROLLI")->getFields();
            $arrayAnanorDBFields = $this->praLib->getPRAMDB()->getTableObject("ANANOR")->getFields();
            $arrayAnareqDBFields = $this->praLib->getPRAMDB()->getTableObject("ANAREQ")->getFields();
            $arrayPracltDBFields = $this->praLib->getPRAMDB()->getTableObject("PRACLT")->getFields();
            $arrayAnasetDBFields = $this->praLib->getPRAMDB()->getTableObject("ANASET")->getFields();
            $arrayAnaattDBFields = $this->praLib->getPRAMDB()->getTableObject("ANAATT")->getFields();
            $arrayAnatipDBFields = $this->praLib->getPRAMDB()->getTableObject("ANATIP")->getFields();
            $arrayAnatspDBFields = $this->praLib->getPRAMDB()->getTableObject("ANATSP")->getFields();
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }
        //
        $arrayCtr["diffCampi"]['ANAPRA'] = $this->GetArrayDiffFields($arrayCtr['Anapra_rec'][0], $arrayAnapraDBFields);
        $arrayCtr["diffCampi"]['ITEPAS'] = $this->GetArrayDiffFields($arrayCtr['Itepas_tab'][0], $arrayItepasDBFields);
        $arrayCtr["diffCampi"]['ITEDAG'] = $this->GetArrayDiffFields($arrayCtr['Itedag_tab'][0], $arrayItedagDBFields);
        $arrayCtr["diffCampi"]['ANPDOC'] = $this->GetArrayDiffFields($arrayCtr['Anpdoc_tab'][0], $arrayAnpdocDBFields);
        $arrayCtr["diffCampi"]['ITEREQ'] = $this->GetArrayDiffFields($arrayCtr['Itereq_tab'][0], $arrayItereqDBFields);
        $arrayCtr["diffCampi"]['ITENOR'] = $this->GetArrayDiffFields($arrayCtr['Itenor_tab'][0], $arrayItenorDBFields);
        $arrayCtr["diffCampi"]['ITECONTROLLI'] = $this->GetArrayDiffFields($arrayCtr['Itecontrolli_tab'][0], $arrayItecontrolliDBFields);
        $arrayCtr["diffCampi"]['ANANOR'] = $this->GetArrayDiffFields($arrayCtr['Ananor_tab'][0], $arrayAnanorDBFields);
        $arrayCtr["diffCampi"]['ANAREQ'] = $this->GetArrayDiffFields($arrayCtr['Anareq_tab'][0], $arrayAnareqDBFields);
        $arrayCtr["diffCampi"]['PRACLT'] = $this->GetArrayDiffFields($arrayCtr['Praclt_tab'][0], $arrayPracltDBFields);
        $arrayCtr["diffCampi"]['ANASET'] = $this->GetArrayDiffFields($arrayCtr['Anaset_rec'][0], $arrayAnasetDBFields);
        $arrayCtr["diffCampi"]['ANAATT'] = $this->GetArrayDiffFields($arrayCtr['Anaatt_rec'][0], $arrayAnaattDBFields);
        $arrayCtr["diffCampi"]['ANATIP'] = $this->GetArrayDiffFields($arrayCtr['Anatip_rec'][0], $arrayAnatipDBFields);
        $arrayCtr["diffCampi"]['ANATSP'] = $this->GetArrayDiffFields($arrayCtr['Anatsp_rec'][0], $arrayAnatspDBFields);
        return $arrayCtr;
    }

    function GetArrayDiffFields($arrayTabellaXml, $arrayTabellaDB) {
        foreach ($arrayTabellaXml as $campo => $valore) {
            if (!array_key_exists($campo, $arrayTabellaDB)) {
                $arrayDiff[$campo] = $valore;
            }
        }
        return $arrayDiff;
    }

    function controllaAutore($Anapra_rec) {
        $arr = array();
        $Anapra_rec_ctr = $this->praLib->GetAnapra($Anapra_rec['PRANUM']);
        if ($Anapra_rec_ctr) {
            $arr['PROC_ESISTENTE']['PRAINSEDITOR'] = $Anapra_rec_ctr['PRAINSEDITOR'];
            $arr['PROC_ESISTENTE']['PRAUPDEDITOR'] = $Anapra_rec_ctr['PRAUPDEDITOR'];
        }
        $arr['PROC_NUOVO']['PRAINSEDITOR'] = $Anapra_rec['PRAINSEDITOR'];
        $arr['PROC_NUOVO']['PRAUPDEDITOR'] = $Anapra_rec['PRAUPDEDITOR'];
        $Filent_rec = $this->praLib->GetFilent(1);
        $arr['PARAM']['EDITOR'] = $Filent_rec['FILDE6'];
        if (!$Filent_rec['FILDE6']) {
            $arr['PARAM']['EDITOR'] = 'indefinito';
        }
        return $arr;
    }

}

?>