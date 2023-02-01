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

    public $encoding = 'ISO-8859-1';
    public $praLib;
    private $errMessage;
    private $errCode;
    public $eqAudit;
    private $currentEstraiXMLPath;

    function __construct() {
        $this->praLib = new praLib();
        $this->eqAudit = new eqAudit();
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

    public function creaXMLProcedimento($pranum, $embeddedStream = true) {
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

        /*
         * Elemento dati DB
         * 
         */
        $xml .= $this->startElement("PRAMXX");

        //
        // Export di ANAPRA
        //
        $encoding = array(
            "PRADES__1" => 'base64',
            "PRADES__2" => 'base64',
            "PRADES__3" => 'base64',
            "PRADES__4" => 'base64',
            "PRAOGGTML" => 'base64'
        );
        $xml .= $this->comment("Tabella ANAPRA");
        $xml .= $this->startElement("ANAPRA", array("ROWID" => $anapra_rec['ROWID']));
        $xml .= $this->parseArraySimple($anapra_rec, $encoding);
        $xml .= $this->endElement("ANAPRA");

        //
        // Export ANATSP che sono negli eventi di questo procedimento
        //
        $sql6 = " SELECT
                    ANATSP.*
                 FROM
                    ANATSP
                 LEFT OUTER JOIN ITEEVT ON ITEEVT.IEVTSP = ANATSP.TSPCOD AND ITEEVT.ITEPRA = '$pranum'
                 WHERE
                    ITEEVT.ITEPRA = '$pranum'
                 GROUP BY ANATSP.ROWID
                ";
        $anatsp_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql6, true);
        $xml .= $this->comment("Tabella ANATSP");
        foreach ($anatsp_tab as $anatsp_rec) {
            $xml .= $this->startElement("ANATSP", array("ROWID" => $anatsp_rec['ROWID']));
            $xml .= $this->parseArraySimple($anatsp_rec);
            $xml .= $this->endElement("ANATSP");
        }

        //
        // Export ANATIP che sono negli eventi di questo procedimento
        //
        $sql3 = " SELECT
                    ANATIP.*
                 FROM
                    ANATIP
                 LEFT OUTER JOIN ITEEVT ON ITEEVT.IEVTIP = ANATIP.TIPCOD AND ITEEVT.ITEPRA = '$pranum'
                 WHERE
                    ITEEVT.ITEPRA = '$pranum'
                 GROUP BY ANATIP.ROWID";
        $anatip_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql3, true);
        $xml .= $this->comment("Tabella ANATIP");
        foreach ($anatip_tab as $anatip_rec) {
            $xml .= $this->startElement("ANATIP", array("ROWID" => $anatip_rec['ROWID']));
            $xml .= $this->parseArraySimple($anatip_rec);
            $xml .= $this->endElement("ANATIP");
        }

        //
        // Export ANASET che sono negli eventi di questo procedimento
        //
        $sql4 = " SELECT
                    ANASET.*
                 FROM
                    ANASET
                 LEFT OUTER JOIN ITEEVT ON ITEEVT.IEVSTT = ANASET.SETCOD AND ITEEVT.ITEPRA = '$pranum'
                 WHERE
                    ITEEVT.ITEPRA = '$pranum'
                 GROUP BY ANASET.ROWID";
        $anaset_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql4, true);
        $xml .= $this->comment("Tabella ANASET");
        foreach ($anaset_tab as $anaset_rec) {
            $xml .= $this->startElement("ANASET", array("ROWID" => $anaset_rec['ROWID']));
            $xml .= $this->parseArraySimple($anaset_rec);
            $xml .= $this->endElement("ANASET");
        }

        //
        // Export ANAATT che sono negli eventi di questo procedimento
        //
        $sql5 = " SELECT
                    ANAATT.*
                 FROM
                    ANAATT
                 LEFT OUTER JOIN ITEEVT ON ITEEVT.IEVATT = ANAATT.ATTCOD AND ITEEVT.ITEPRA = '$pranum'
                 WHERE
                    ITEEVT.ITEPRA = '$pranum'
                 GROUP BY ANAATT.ROWID";
        $anaatt_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql5, true);
        $encoding = array(
            "ATTDES" => 'base64'
        );
        $xml .= $this->comment("Tabella ANAATT");
        foreach ($anaatt_tab as $anaatt_rec) {
            $xml .= $this->startElement("ANAATT", array("ROWID" => $anaatt_rec['ROWID']));
            $xml .= $this->parseArraySimple($anaatt_rec, $encoding);
            $xml .= $this->endElement("ANAATT");
        }

        //
        // Export di ITEPAS
        //
        $xml .= $this->comment("Tabella ITEPAS");
        foreach ($itepas_tab as $itepas_rec) {
            $xml .= $this->startElement("ITEPAS", array("ROWID" => $itepas_rec['ROWID']));
            $encoding = array(
                "ITEMETA" => 'base64',
                "ITEATE" => 'base64',
                "ITEOBE" => 'base64',
                "ITEDES" => 'base64',
                "ITENOT" => 'base64',
                "ITEEXPRRISERVATO" => 'base64'
            );
            $xml .= $this->parseArraySimple($itepas_rec, $encoding);
            $xml .= $this->endElement("ITEPAS");
        }

        //
        //  Export di ITEDAG
        //
        $encoding = array(
            "ITDCTR" => 'base64',
            "ITDMETA" => 'base64',
            "ITDEXPROUT" => 'base64',
            "ITDLAB" => 'base64',
            "ITDKEY" => 'base64',
            "ITDALIAS" => 'base64',
            "ITDVAL" => 'base64',
            "ITDDES" => 'base64'
        );
        $xml .= $this->comment("Tabella ITEDAG");
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY ITDSEQ";
            $itedag_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itedag_tab) {
                foreach ($itedag_tab as $Chiave => $itedag_rec) {
                    $xml .= $this->startElement("ITEDAG", array("ROWID" => $itedag_rec['ROWID']));
                    $xml .= $this->parseArraySimple($itedag_rec, $encoding);
                    $xml .= $this->endElement("ITEDAG");
                }
            }
        }

        //
        //  Export di ITECONTROLLI
        //
        $encoding = array(
            "ESPRESSIONE" => 'base64'
        );
        $xml .= $this->comment("Tabella ITECONTROLLI");
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY SEQUENZA";
            $itecontrolli_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itecontrolli_tab) {
                foreach ($itecontrolli_tab as $itecontrolli_rec) {
                    $xml .= $this->startElement("ITECONTROLLI", array("ROWID" => $itecontrolli_rec['ROWID']));
                    $xml .= $this->parseArraySimple($itecontrolli_rec, $encoding);
                    $xml .= $this->endElement("ITECONTROLLI");
                }
            }
        }

        //
        // Export di ANPDOC
        //
        $anpdoc_tab = $this->praLib->GetAnpdocDaMaster($Pram_db, $pranum);
        if ($anpdoc_tab) {
            $xml .= $this->comment("Tabella ANPDOC");
            foreach ($anpdoc_tab as $anpdoc_rec) {
                $xml .= $this->startElement("ANPDOC", array("ROWID" => $anpdoc_rec['ROWID']));
                $xml .= $this->parseArraySimple($anpdoc_rec);
                $xml .= $this->endElement("ANPDOC");
            }
        }

        //
        // Export di ITEREQ
        //
        $itereq_tab = $this->praLib->GetItereqDaMaster($Pram_db, $pranum);
        if ($itereq_tab) {
            $xml .= $this->comment("Tabella ITEREQ");
            foreach ($itereq_tab as $itereq_rec) {
                $xml .= $this->startElement("ITEREQ", array("ROWID" => $itereq_rec));
                $xml .= $this->parseArraySimple($itereq_rec);
                $xml .= $this->endElement("ITEREQ");
            }
        }

        //
        // Export di ITENOR
        //
        $itenor_tab = $this->praLib->GetItenorDaMaster($Pram_db, $pranum);
        if ($itenor_tab) {
            $xml .= $this->comment("Tabella ITENOR");
            foreach ($itenor_tab as $itenor_rec) {
                $xml .= $this->startElement("ITENOR", array("ROWID" => $itereq_rec['ROWID']));
                $xml .= $this->parseArraySimple($itenor_rec);
                $xml .= $this->endElement("ITENOR");
            }
        }

        //
        // Export di ITEEVT ESPORTO SOLO SE PRESENTI EVENTI
        //
        $iteevt_tab = $this->praLib->GetIteevtDaMaster($Pram_db, $pranum);
        if ($iteevt_tab) {
            $xml .= $this->comment("Tabella ITEEVT");
            foreach ($iteevt_tab as $iteevt_rec) {
                $xml .= $this->startElement("ITEEVT", array("ROWID" => $iteevt_rec['ROWID']));
                $xml .= $this->parseArraySimple($iteevt_rec);
                $xml .= $this->endElement("ITEEVT");
            }
        }

        //
        // Export di PRAAZIONI ESPORTO SOLO SE PRESENTI 
        //
        if ($this->tableExists($Pram_db, 'PRAAZIONI')) {
            $praazioni_tab = $this->praLib->GetPraazioniDaMaster($Pram_db, $pranum);
            if ($praazioni_tab) {
                $xml .= $this->comment("Tabella PRAAZIONI");
                foreach ($praazioni_tab as $praazioni_rec) {
                    $xml .= $this->startElement("PRAAZIONI", array("ROWID" => $praazioni_rec['ROWID']));
                    $xml .= $this->parseArraySimple($praazioni_rec);
                    $xml .= $this->endElement("PRAAZIONI");
                }
            }
        }

        //
        // Export di ITEVPADETT ESPORTO SOLO SE PRESENTI 
        //
        if ($this->tableExists($Pram_db, 'ITEVPADETT')) {
            $itevpadett_tab = $this->praLib->GetItevpadettDaMaster($Pram_db, $pranum, 'itecod');
            if ($itevpadett_tab) {
                $xml .= $this->comment("Tabella ITEVPADETT");
                foreach ($itevpadett_tab as $itevpadett_rec) {
                    $xml .= $this->startElement("ITEVPADETT", array("ROWID" => $itevpadett_rec['ROW_ID']));
                    $xml .= $this->parseArraySimple($itevpadett_rec);
                    $xml .= $this->endElement("ITEVPADETT");
                }
            }
        }

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
        $xml .= $this->comment("Tabella PRACLT");
        foreach ($praclt_tab as $praclt_rec) {
            $xml .= $this->startElement("PRACLT", array("ROWID" => $praclt_rec['ROWID']));
            $xml .= $this->parseArraySimple($praclt_rec);
            $xml .= $this->endElement("PRACLT");
        }

        //
        // Export ANANOR che sono in questo procedimento
        //
        $encoding = array(
            "NORDES" => 'base64',
            "NORURL" => 'base64'
        );
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
        $xml .= $this->comment("Tabella ANANOR");
        foreach ($ananor_tab as $ananor_rec) {
            $xml .= $this->startElement("ANANOR", array("ROWID" => $ananor_rec['ROWID']));
            $xml .= $this->parseArraySimple($ananor_rec, $encoding);
            $xml .= $this->endElement("ANANOR");
        }

        //
        // Export ANAREQ che sono in questo procedimento
        //
        $encoding = array(
            "REQDES" => 'base64'
        );
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
        $xml .= $this->comment("Tabella ANAREQ");
        foreach ($anareq_tab as $anareq_req) {
            $xml .= $this->startElement("ANAREQ", array("ROWID" => $anareq_req['ROWID']));
            $xml .= $this->parseArraySimple($anareq_req, $encoding);
            $xml .= $this->endElement("ANAREQ");
        }

        //
        // Export ANAEVENTI che sono in questo procedimento
        // Esporto solo se presenti
        //
        $sql2 = " SELECT
                    ANAEVENTI.*
                 FROM
                    ANAEVENTI
                 LEFT OUTER JOIN
                    ITEEVT
                 ON
                    ANAEVENTI.EVTCOD = ITEEVT.IEVCOD
                 WHERE
                    ITEEVT.ITEPRA = '$pranum'";
        $anaeventi_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql2, true);
        if ($anaeventi_tab) {
            $xml .= $this->comment("Tabella ANAEVENTI");
            foreach ($anaeventi_tab as $anaeventi_rec) {
                $xml .= $this->startElement("ANAEVENTI", array("ROWID" => $anaeventi_rec['ROWID']));
                $xml .= $this->parseArraySimple($anaeventi_rec, $encoding);
                $xml .= $this->endElement("ANAEVENTI");
            }
        }

        //
        // Export di ITEPRAOBB ESPORTO SOLO SE PRESENTI I PROC Obbligatori
        //
        $itepraobb_tab = $this->praLib->GetItePraObb($pranum, "codice", true);
        if ($itepraobb_tab) {
            $xml .= $this->comment("Tabella ITEPRAOBB");
            foreach ($itepraobb_tab as $itepraobb_rec) {
                $xml .= $this->startElement("ITEPRAOBB", array("ROWID" => $itepraobb_rec['ROWID']));
                $xml .= $this->parseArraySimple($itepraobb_rec);
                $xml .= $this->endElement("ITEPRAOBB");
            }
        }

        //
        // Export di ITEDEST destinatri dei passi comunicazione
        //
        $itedest_tab = $this->praLib->GetItedest($pranum, "itecod");
        if ($itedest_tab) {
            $xml .= $this->comment("Tabella ITEDEST");
            foreach ($itedest_tab as $itedest_rec) {
                $xml .= $this->startElement("ITEDEST", array("ROWID" => $itedest_rec['ROW_ID']));
                $xml .= $this->parseArraySimple($itedest_rec);
                $xml .= $this->endElement("ITEDEST");
            }
        }

        //
        //FINE PRAMXX
        //
        $xml .= $this->endElement("PRAMXX");


        $tempPath = itaLib::getAppsTempPath();
        $nome_file = $tempPath . "/exportProc_$pranum.xml";
        $tempResourcesPath = itaLib::createAppsTempPath("exportProc_$pranum");

        $fp = fopen($nome_file, 'w');
        if (!$fp) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile aprire il file xml $nome_file");
            return false;
        }

        //
        // Inizio Xml
        //
        if (!$this->writeFile($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere il file xml $nome_file");
            return false;
        }

        if (!$this->writeFile($fp, $this->startElement("PROCEDIMENTO", array('version' => '2')))) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere il procedimento nel file xml $nome_file");
            return false;
        }
        //
        // Dati DB
        //
        if (!$this->writeFile($fp, $xml)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere dati db nel file xml $nome_file");
            return false;
        }

        if (!$this->writeFile($fp, "<RISORSE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere risorse nel file xml $nome_file");
            return false;
        }
        //
        // FILE COLLEGATI A ITEWRD
        //
        if (!$this->writeFile($fp, "<TESTI_ASSOCIATI>\r\n")) {
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
                if (!$this->writeFile($fp, "<TESTO_ASSOCIATO ROWID=\"{$itepas_rec['ROWID']}\">\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Testo associati del passo:{$itepas_rec['ITESEQ']} non trovato.");
                    return false;
                }



//                $nodeName = "<NAME>";
//                $nodeName .= "<![CDATA[{$itepas_rec['ITEWRD']}]]>";
//                $nodeName .= "</NAME>\r\n";
                $nodeName = $this->simpleElement("NAME", $itepas_rec['ITEWRD']);
                if (!$this->writeFile($fp, $nodeName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere il nome del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
                $fileSha = hash_file('sha256', $filePath);
                $nodeHash = "<HASH>";
                $nodeHash .= "<![CDATA[{$fileSha}]]>";
                $nodeHash .= "</HASH>\r\n";
                if (!$this->writeFile($fp, $nodeHash)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere l'hash del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }

                if ($embeddedStream) {
                    $nodeStream = "<STREAM>";
                    $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
                    $nodeStream .= "</STREAM>\r\n";
                    if (!$this->writeFile($fp, $nodeStream)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Impossibile scrivere lo stream del Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                        return false;
                    }
                } else {
                    if (!copy($filePath, $tempResourcesPath . '/' . $fileSha)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Impossibile copiare il Testo associato del passo:{$itepas_rec['ITEWRD']}.");
                        return false;
                    }
                }

                if (!$this->writeFile($fp, "</TESTO_ASSOCIATO>\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere fine testo associato del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
            }
        }
        if (!$this->writeFile($fp, "</TESTI_ASSOCIATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi associati");
            return false;
        }

        //
        // FILE COLLEGATI AL PROCEDIMENTO
        //
        if (!$this->writeFile($fp, "<TESTI_ALLEGATI>\r\n")) {
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
            if (!$this->writeFile($fp, "<TESTO_ALLEGATO ROWID=\"{$anpdoc_rec['ROWID']}\">\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }

            $nodeName = $this->simpleElement("NAME", $anpdoc_rec['ANPFIL']);
//            $nodeName = "<NAME>";
//            $nodeName .= "<![CDATA[{$anpdoc_rec['ANPFIL']}]]>";
//            $nodeName .= "</NAME>\r\n";
            if (!$this->writeFile($fp, $nodeName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere nome File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
            $fileSha = hash_file('sha256', $filePath);
            $nodeHash = "<HASH>";
            $nodeHash .= "<![CDATA[{$fileSha}]]>";
            $nodeHash .= "</HASH>\r\n";
            if (!$this->writeFile($fp, $nodeHash)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere hash File allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }

            if ($embeddedStream) {
                $nodeStream = "<STREAM>";
                $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
                $nodeStream .= "</STREAM>\r\n";
                if (!$this->writeFile($fp, $nodeStream)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere nome lo stream allegato {$anpdoc_rec['ANPFIL']}");
                    return false;
                }
            } else {
                if (!copy($filePath, $tempResourcesPath . '/' . $fileSha)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile copiare il file collegato al passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
            }

            if (!$this->writeFile($fp, "</TESTO_ALLEGATO>\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere fine testo allegato {$anpdoc_rec['ANPFIL']}");
                return false;
            }
        }
        if (!$this->writeFile($fp, "</TESTI_ALLEGATI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi allegati");
            return false;
        }

        //
        // FILE ANANOR
        //
        if (!$this->writeFile($fp, "<TESTI_NORMATIVE>\r\n")) {
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
            if (!$this->writeFile($fp, "<NORMATIVA ROWID=\"{$ananor_rec['ROWID']}\">\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere la normativa {$ananor_rec['NORFIL']}");
                return false;
            }
//            $nodeName = "<NAME>";
//            $nodeName .= "<![CDATA[{$ananor_rec['NORFIL']}]]>";
//            $nodeName .= "</NAME>\r\n";
            $nodeName = $this->simpleElement("NAME", $ananor_rec['NORFIL']);
            if (!$this->writeFile($fp, $nodeName)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere il name della normativa {$ananor_rec['NORFIL']}");
                return false;
            }
            $fileSha = hash_file('sha256', $filePath);
            $nodeHash = "<HASH>";
            $nodeHash .= "<![CDATA[{$fileSha}]]>";
            $nodeHash .= "</HASH>\r\n";
            if (!$this->writeFile($fp, $nodeHash)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere l'hash della normativa {$ananor_rec['NORFIL']}");
                return false;
            }

            if ($embeddedStream) {
                $nodeStream = "<STREAM>";
                $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
                $nodeStream .= "</STREAM>\r\n";
                if (!$this->writeFile($fp, $nodeStream)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere lo stream della normativa {$ananor_rec['NORFIL']}");
                    return false;
                }
            } else {
                if (!copy($filePath, $tempResourcesPath . '/' . $fileSha)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile copiare la normativa del passo:{$itepas_rec['ITEWRD']}.");
                    return false;
                }
            }

            if (!$this->writeFile($fp, "</NORMATIVA>\r\n")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile scrivere fine normativa");
                return false;
            }
        }
        if (!$this->writeFile($fp, "</TESTI_NORMATIVE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi normative");
            return false;
        }

        //
        // FILE ANAREQ
        //
        if (!$this->writeFile($fp, "<TESTI_REQUISITI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere inizio testi requisiti");
            return false;
        }
        foreach ($anareq_tab as $anareq_rec) {
            if ($anareq_rec['REQFIL'] != '') {
                $sorgente = $sorgente = Config::getPath('general.itaProc') . 'ente' . $ditta . '/requisiti/';
                $filePath = $sorgente . $anareq_rec['REQFIL'];
                if (!file_exists($filePath)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File requisito " . $anareq_rec['REQFIL'] . " per il procedimento:$pranum non trovato.");
                    return false;
                }
                if (!$this->writeFile($fp, "<REQUISITO ROWID=\"{$anareq_rec['ROWID']}\">\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere il requisito {$anareq_rec['REQFIL']}");
                    return false;
                }
                $nodeName = "<NAME>";
                $nodeName .= "<![CDATA[{$anareq_rec['REQFIL']}]]>";
                $nodeName .= "</NAME>\r\n";
                if (!$this->writeFile($fp, $nodeName)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere il name del testo requisito {$anareq_rec['REQFIL']}");
                    return false;
                }
                $fileSha = hash_file('sha256', $filePath);
                $nodeHash = "<HASH>";
                $nodeHash .= "<![CDATA[{$fileSha}]]>";
                $nodeHash .= "</HASH>\r\n";
                if (!$this->writeFile($fp, $nodeHash)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere l'hash del testo requisito {$anareq_rec['REQFIL']}");
                    return false;
                }

                if ($embeddedStream) {
                    $nodeStream = "<STREAM>";
                    $nodeStream .= "<![CDATA[" . base64_encode(file_get_contents($filePath)) . "]]>";
                    $nodeStream .= "</STREAM>\r\n";
                    if (!$this->writeFile($fp, $nodeStream)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Impossibile scrivere lo stream del testo requisito {$anareq_rec['REQFIL']}");
                        return false;
                    }
                } else {
                    if (!copy($filePath, $tempResourcesPath . '/' . $fileSha)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Impossibile copiare il testo requisito del passo:{$itepas_rec['ITEWRD']}.");
                        return false;
                    }
                }

                if (!$this->writeFile($fp, "</REQUISITO>\r\n")) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Impossibile scrivere fine requisito");
                    return false;
                }
            }
        }
        if (!$this->writeFile($fp, "</TESTI_REQUISITI>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine testi requisiti");
            return false;
        }

        if (!$this->writeFile($fp, "</RISORSE>\r\n")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere fine risorse");
            return false;
        }
        if (!$this->writeFile($fp, "</PROCEDIMENTO>\r\n")) {
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

    public function creaXMLProcSimple($pranum) {
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

        /*
         * Elemento dati DB
         * 
         */
        $xml .= $this->startElement("PRAMXX");

        /*
         * Export di ANAPRA
         */
        $xml .= $this->comment("Tabella ANAPRA");
        $xml .= $this->startElement("ANAPRA", array("ROWID" => $anapra_rec['ROWID']));
        foreach ($anapra_rec as $element => $value) {
            if ($element == "PRANUM") {
                $xml .= $this->simpleElement($element, $value);
            }
        }
        $xml .= $this->endElement("ANAPRA");

        /*
         * Export di ITEPAS
         */
        $campi = array();
        $campi[] = "ITEOBE";
        $campi[] = "ITEATE";
        $xml .= $this->comment("Tabella ITEPAS");
        $incItepas = 0;
        foreach ($itepas_tab as $itepas_rec) {
            $xmlItepas = "";
            foreach ($campi as $Chiave => $Campo) {
                if ($itepas_rec[$Campo]) {
                    $xmlItepas .= $this->simpleElement($Campo, $itepas_rec[$Campo]);
                }
            }
            if ($xmlItepas) {
                $incItepas++;
                $xml .= $this->startElement("ITEPAS", array("ROWID" => $itepas_rec['ROWID']));
                $xml .= $this->simpleElement("ITESEQ", $itepas_rec['ITESEQ']);
                $xml .= $this->simpleElement("ITEDES", $itepas_rec['ITEDES']);
                $xml .= $xmlItepas;
                $xml .= $this->endElement("ITEPAS");
            }
        }

        /*
         * Export di ITEDAG
         */
        $campi = array();
        $campi[] = "ITDVAL";
        $campi[] = "ITDEXPROUT";
        $campi[] = "ITDCTR";
        $xml .= $this->comment("Tabella ITEDAG");
        $incItedag = 0;
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY ITDSEQ";
            $itedag_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itedag_tab) {
                foreach ($itedag_tab as $Chiave => $itedag_rec) {
                    $xmlItedag = "";
                    foreach ($campi as $Chiave => $Campo) {
                        if ($itedag_rec[$Campo] && $itedag_rec[$Campo] != "a:0:{}") {
                            $xmlItedag .= $this->simpleElement($Campo, $itedag_rec[$Campo]);
                        }
                    }
                    if ($xmlItedag) {
                        $incItedag++;
                        $xml .= $this->startElement("ITEDAG", array("ROWID" => $itedag_rec['ROWID'], "ITESEQ" => $itepas_rec['ITESEQ']));
                        $xml .= $this->simpleElement("ITDKEY", $itedag_rec['ITDKEY']);
                        $xml .= $xmlItedag;
                        $xml .= $this->endElement("ITEDAG");
                    }
                }
            }
        }

        /*
         * Export di ITECONTROLLI
         */
        $campi = array();
        $campi[] = "ESPRESSIONE";
        $xml .= $this->comment("Tabella ITECONTROLLI (Tab Validazione)");
        $incItecontrolli = 0;
        foreach ($itepas_tab as $itepas_rec) {
            $sql = "SELECT * FROM ITECONTROLLI WHERE ITEKEY = '" . $itepas_rec['ITEKEY'] . "' ORDER BY SEQUENZA";
            $itecontrolli_tab = ItaDB::DBSQLSelect($Pram_db, $sql, true);
            if ($itecontrolli_tab) {
                foreach ($itecontrolli_tab as $itecontrolli_rec) {
                    $xmlItecontrolli = "";
                    foreach ($campi as $Chiave => $Campo) {
                        if ($itecontrolli_rec[$Campo]) {
                            $xmlItecontrolli .= $this->simpleElement($Campo, $itecontrolli_rec[$Campo]);
                        }
                    }
                    if ($xmlItecontrolli) {
                        $incItecontrolli++;
                        $xml .= $this->startElement("ITECONTROLLI", array("ROWID" => $itecontrolli_rec['ROWID'], "ITESEQ" => $itepas_rec['ITESEQ']));
                        $xml .= $xmlItecontrolli;
                        $xml .= $this->endElement("ITECONTROLLI");
                    }
                }
            }
        }

        /*
         * FINE PRAMXX
         */
        $xml .= $this->endElement("PRAMXX");

        /*
         * Riepilogo
         */
        $xml .= $this->comment("Riepilogo Controlli");
        $xml .= $this->simpleElement("PASSI", $incItepas);
        $xml .= $this->simpleElement("DATI_AGGIUNTIVI", $incItedag);
        $xml .= $this->simpleElement("VALIDAZIONI", $incItecontrolli);

        /*
         * Inizializzo e apro il file
         */
        $tempPath = itaLib::getAppsTempPath();
        $nome_file = $tempPath . "/exportProcSimple_$pranum.xml";
        $fp = fopen($nome_file, 'w');
        if (!$fp) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile aprire il file xml $nome_file");
            return false;
        }

        /*
         * Scrivo XML
         */
        if (!$this->writeFile($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n$xml")) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile scrivere il file xml $nome_file");
            return false;
        }

        fclose($fp);
        return $nome_file;
    }

    public function estraiXMLProcedimento($xmlFile, $risorse = true) {

        $dizionarioPRAM = array(
            "ANAPRA" => "Anapra_rec",
            "ITEPAS" => "Itepas_tab",
            "ITEDAG" => "Itedag_tab",
            "ANPDOC" => "Anpdoc_tab",
            "ITECONTROLLI" => "Itecontrolli_tab",
            "ITEREQ" => "Itereq_tab",
            "ITENOR" => "Itenor_tab",
            "PRACLT" => "Praclt_tab",
            "ANANOR" => "Ananor_tab",
            "ITEEVT" => "Iteevt_tab",
            "ANAREQ" => "Anareq_tab",
            "ANAEVENTI" => "Anaeventi_tab",
            "ANATSP" => "Anatsp_rec",
            "ANATIP" => "Anatip_tab",
            "ANASET" => "Anaset_tab",
            "ANAATT" => "Anaatt_tab",
            "ITEPRAOBB" => "Itepraobb_tab",
            "PRAAZIONI" => "Praazioni_tab",
            "ITEDEST" => "Itedest_tab",
            "ITEVPADETT" => "Itevpadett_tab"
        );

        if ($risorse === false) {
            unset($dizionarioPRAM['ITEDAG']);
            unset($dizionarioPRAM['ITEPAS']);
            unset($dizionarioPRAM['ANPDOC']);
            unset($dizionarioPRAM['ITECONTROLLI']);
            unset($dizionarioPRAM['ITEREQ']);
            unset($dizionarioPRAM['ITENOR']);
            unset($dizionarioPRAM['PRACLT']);
            unset($dizionarioPRAM['ANANOR']);
            unset($dizionarioPRAM['ANAREQ']);
            unset($dizionarioPRAM['ANAEVENTI']);
            unset($dizionarioPRAM['ANATSP']);
            unset($dizionarioPRAM['ANATIP']);
            unset($dizionarioPRAM['ITEEVT']);
            unset($dizionarioPRAM['ANATSP']);
            unset($dizionarioPRAM['ANATIP']);
            unset($dizionarioPRAM['ANASET']);
            unset($dizionarioPRAM['ANAATT']);
            unset($dizionarioPRAM['ITEVPADETT']);
        }

        $this->currentEstraiXMLPath = null;

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
        $this->currentEstraiXMLPath = dirname($xmlFile);
        while (true) {
            if (!$xml->read()) {
                break;
            }
            if ($xml->nodeType == XMLReader::ELEMENT) {
                if (!$xml->hasAttributes) {
                    continue;
                }
                if ($dizionarioPRAM[$xml->name]) {
                    if (!$this->elaboraXMLTag($xml, $dizionarioPRAM, $array)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Elabora tag Xml: " . $this->errMessage);
                        return false;
                    }
                    $ii++;
                    continue;
                }
                //if ($risorse === true) {
                if ($dizionarioRISORSE[$xml->name]) {
                    if (!$this->elaboraXMLRisorse($xml, $dizionarioRISORSE, $array, $risorse)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Risorsa: " . $this->errMessage);
                        return false;
                    }
                    $ii++;
                    continue;
                }
                //}
            }
        }
        $xml = null;
        $this->currentEstraiXMLPath = null;
        return $array;
    }

    private function elaboraXMLRisorse($xml, $dizionario, &$array, $risorse = true) {
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
        if ($risorse) {
            if ($this->seekNode($xmlRisorsa, "STREAM")) {
                $xmlRisorsa->read();
                $risorsaXml_rec['STREAM'] = $xmlRisorsa->value;
            } else {
                $resourceFile = $this->currentEstraiXMLPath . '/resources/' . $risorsaXml_rec['HASH'];
                if (!file_exists($resourceFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File XML : $xml->name. Impossibile trovare il file.");
                    return false;
                }
                $risorsaXml_rec['STREAM'] = base64_encode(file_get_contents($resourceFile));
            }
        }
        $array[$dizionario[$xml->name]][] = $risorsaXml_rec;
        return true;
    }

    private function elaboraXMLTag($xml, $dizionario, &$array) {
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
            $dataEncodeAttr = ($arrayValue[0]['@attributes']['dataencode']) ? $arrayValue[0]['@attributes']['dataencode'] : '';
            switch ($dataEncodeAttr) {
                case 'ent':
                    $Record_rec[$campo] = html_entity_decode($arrayValue[0]['@textNode']);
                    break;
                case 'base64':
                    $Record_rec[$campo] = base64_decode(utf8_decode($arrayValue[0]['@textNode']));
                    break;
                default:
                    $Record_rec[$campo] = utf8_decode($arrayValue[0]['@textNode']);
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
        //$testiAssociati = $this->praLib->GetFileList($destinazione);
        $testiAggiornabili = $testiNonAggiornabili = $testiNuovi = array();
        foreach ($testiAssociatiXml_tab as $testiAssociatiXml_rec) {
            if (!file_exists($destinazione . $testiAssociatiXml_rec['NAME'])) {
                $testiNuovi[] = $testiAssociatiXml_rec;
            }
        }
        foreach ($testiAssociatiXml_tab as $testiAssociatiXml_rec) {
            $infotesto = array(
                'FILEPATH' => $destinazione . '/' . $testiAssociatiXml_rec['NAME'],
                'FILENAME' => $testiAssociatiXml_rec['NAME'],
                'HASH' => hash_file('sha256', $destinazione . '/' . $testiAssociatiXml_rec['NAME'])
            );
            if (file_exists($destinazione . '/' . $testiAssociatiXml_rec['NAME'])) {
                if ($testiAssociatiXml_rec['HASH'] == $infotesto['HASH']) {
                    $testiAggiornabili[] = $testiAssociatiXml_rec;
                } else {
                    $testiNonAggiornabili[] = $testiAssociatiXml_rec;
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

            /*
             * Log DBPARA
             */
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $this->praLib->getPRAMDB()->getDB(),
                'DSet' => 'ANAPRA',
                'Estremi' => "Aggiorno da import XML il procedimento " . $anapra_rec['PRANUM'],
            ));

            try {
                ItaDB::DBUpdate($this->praLib->getPRAMDB(), "ANAPRA", "ROWID", $anapra_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Update Anapra da acquisisci XML: " . $e->getMessage());
                return false;
            }
        } else {
            $anapra_rec = $arrayCtr["Anapra_rec"][0];
            unset($anapra_rec['ROWID']);

            /*
             * Log DBPARA
             */
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_INS_RECORD,
                'DB' => $this->praLib->getPRAMDB()->getDB(),
                'DSet' => 'ANAPRA',
                'Estremi' => "Inserisco da import XML il procedimento " . $anapra_rec['PRANUM'],
            ));

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAPRA", "ROWID", $anapra_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Insert Anapra da acquisisci XML:: " . $e->getMessage());
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
                    $this->setErrMessage("Errore Cancellazione ITEPAS: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itepas_tab'] as $keyPasso => $itepas_rec) {
            unset($itepas_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEPAS", "ROWID", $itepas_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEPAS: " . $e->getMessage());
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
                    $this->setErrMessage("Errore Cancellazione ITEDAG: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itedag_tab'] as $keyDag => $itedag_rec) {
            unset($itedag_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEDAG", "ROWID", $itedag_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEDAG: " . $e->getMessage());
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
                    $this->setErrMessage("Errore Cancellazione ANPDOC: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Anpdoc_tab'] as $keyDoc => $anpdoc_rec) {
            unset($anpdoc_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANPDOC", "ROWID", $anpdoc_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ANPDOC: " . $e->getMessage());
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
                    $this->setErrMessage("Errore Cancellazione ITECONTROLLI: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itecontrolli_tab'] as $keyControlli => $itecontrolli_rec) {
            unset($itecontrolli_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITECONTROLLI", "ROWID", $itecontrolli_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore inserimento Itecontrolli: " . $e->getMessage());
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
                        $this->setErrMessage("Errore Cancellazione ITEREQ: " . $e->getMessage());
                        return false;
                    }
                }
            }
        }
        foreach ($arrayCtr['Itereq_tab'] as $keyReq => $itereq_rec) {
            unset($itereq_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEREQ", "ROWID", $itereq_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEREQ: " . $e->getMessage());
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
                    $this->setErrMessage("Errore Cancellazione ITENOR: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itenor_tab'] as $keyNor => $itenor_rec) {
            unset($itenor_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITENOR", "ROWID", $itenor_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITENOR: " . $e->getMessage());
                return false;
            }
        }

        //
        // Controllo e Inserisco ITEEVT
        //
        if ($Anapra_rec_ctr) {
            $Iteevt_tab = $this->praLib->GetIteevt($Anapra_rec_ctr['PRANUM'], "codice", true);
            foreach ($Iteevt_tab as $Iteevt_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEEVT', 'ROWID', $Iteevt_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ITEEVT: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Iteevt_tab'] as $keyEvt => $iteevt_rec) {
            unset($iteevt_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEEVT", "ROWID", $iteevt_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEEVT: " . $e->getMessage());
                return false;
            }
        }
        //
        // Controllo e Inserisco PRAAZIONI
        //
        if ($Anapra_rec_ctr) {
            if ($this->tableExists($this->praLib->getPRAMDB(), 'PRAAZIONI')) {
                $Praazioni_tab = $this->praLib->GetPraazioni($Anapra_rec_ctr['PRANUM'], "codice", true);
                foreach ($Praazioni_tab as $Praazioni_rec) {
                    try {
                        $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'PRAAZIONI', 'ROWID', $Praazioni_rec['ROWID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Cancellazione PRAAZIONI: " . $e->getMessage());
                        return false;
                    }
                }
            }
        }

        foreach ($arrayCtr['Praazioni_tab'] as $keyAzi => $Praazioni_rec) {
            unset($Praazioni_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "PRAAZIONI", "ROWID", $Praazioni_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento PRAAZIONI: " . $e->getMessage());
                return false;
            }
        }

        //
        // Controllo e Inserisco ITEVPADETT
        //
        if ($Anapra_rec_ctr) {
            if ($this->tableExists($this->praLib->getPRAMDB(), 'ITEVPADETT')) {
                $itevpadett_tab = $this->praLib->GetItevpadett($Anapra_rec_ctr['PRANUM'], 'itecod');
                foreach ($itevpadett_tab as $itevpadett_rec) {
                    try {
                        $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEVPADETT', 'ROW_ID', $itevpadett_rec['ROW_ID']);
                        if ($nrow == 0) {
                            return false;
                        }
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Errore Cancellazione ITEVPADETT: ' . $e->getMessage());
                        return false;
                    }
                }
            }
        }

        foreach ($arrayCtr['Itevpadett_tab'] as $keyVpadett => $itevpadett_rec) {
            unset($itevpadett_rec['ROW_ID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ITEVPADETT', 'ROW_ID', $itevpadett_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore Inserimento ITEVPADETT: ' . $e->getMessage());
                return false;
            }
        }

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
                    $this->setErrMessage("Errore Inserimento PRACLT: " . $e->getMessage());
                    return false;
                }
            } else {
                try {
                    $praclt_rec_ctr['CLTOPEFO'] = $praclt_rec_ctr['CLTOPEFO'];
                    $praclt_rec_ctr['CLTOPE'] = $praclt_rec_ctr['CLTOPE'];
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PRACLT", "ROWID", $praclt_rec_ctr);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Aggiornamento PRACLT: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco normative ANANOR
        //
        foreach ($arrayCtr['Ananor_tab'] as $keyNor => $ananor_rec) {
            $ananor_rec_ctr = $this->praLib->GetAnanor($ananor_rec['NORCOD']);
            if ($ananor_rec_ctr) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ANANOR', 'ROWID', $ananor_rec_ctr['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ANANOR: " . $e->getMessage());
                    return false;
                }
            }
            unset($ananor_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANANOR", "ROWID", $ananor_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ANANOR: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco requisiti ANAREQ
        //
        foreach ($arrayCtr['Anareq_tab'] as $keyReq => $anareq_rec) {
            $anareq_rec_ctr = $this->praLib->GetAnareq($anareq_rec['REQCOD']);
            if ($anareq_rec_ctr) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ANAREQ', 'ROWID', $anareq_rec_ctr['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ANAREQ: " . $e->getMessage());
                    return false;
                }
            }
            unset($anareq_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAREQ", "ROWID", $anareq_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ANAREQ: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco requisiti ANAEVENTI
        //
        foreach ($arrayCtr['Anaeventi_tab'] as $anaeventi_rec) {
            $anaeventi_rec_ctr = $this->praLib->GetAnaeventi($anaeventi_rec['EVTCOD']);
            if ($anaeventi_rec_ctr) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ANAEVENTI', 'ROWID', $anaeventi_rec_ctr['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ANAEVENTI: " . $e->getMessage());
                    return false;
                }
            }
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAEVENTI", "ROWID", $anaeventi_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ANAEVENTI: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco Sportello ANATSP
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
                $this->setErrMessage("Errore Insermento ANATSP: " . $e->getMessage());
                return false;
            }
        }

        //
        // Se non ci sono, inserisco Tipologia ANATIP
        //
        foreach ($arrayCtr["Anatip_tab"] as $keyTip => $Anatip_rec) {
            $anatip_rec_ctr = $this->praLib->GetAnatip($Anatip_rec['TIPCOD']);
            if (!$anatip_rec_ctr) {
                unset($Anatip_rec['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANATIP", "ROWID", $Anatip_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento ANATIP: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco Settore ANASET
        //
        foreach ($arrayCtr["Anaset_tab"] as $keyStt => $Anaset_rec) {
            $anaset_rec_ctr = $this->praLib->GetAnaset($Anaset_rec['SETCOD']);
            if (!$anaset_rec_ctr) {
                unset($Anaset_rec['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANASET", "ROWID", $Anaset_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento ANASET: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Se non ci sono, inserisco Attivita ANAATT
        //
        foreach ($arrayCtr["Anaatt_tab"] as $keyAtt => $Anaatt_rec) {
            $anaatt_rec_ctr = $this->praLib->GetAnaatt($Anaatt_rec['ATTCOD']);
            if (!$anaatt_rec_ctr) {
                unset($Anaatt_rec['ROWID']);
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANAATT", "ROWID", $Anaatt_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Inserimento ANAATT: " . $e->getMessage());
                    return false;
                }
            }
        }

        //
        // Controllo e Inserisco ITEPRAOBB
        //
        if ($Anapra_rec_ctr) {
            $Itepraobb_tab = $this->praLib->GetItePraObb($Anapra_rec_ctr['PRANUM'], "codice", true);
            foreach ($Itepraobb_tab as $Itepraobb_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEPRAOBB', 'ROWID', $Itepraobb_rec['ROWID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ITEPRAOBB: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itepraobb_tab'] as $keyObb => $itepraobb_rec) {
            unset($itepraobb_rec['ROWID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEPRAOBB", "ROWID", $itepraobb_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEPRAOBB: " . $e->getMessage());
                return false;
            }
        }

        //
        // Controllo e Inserisco ITEDEST
        //
        if ($Anapra_rec_ctr) {
            $Itedest_tab = $this->praLib->GetItedest($Anapra_rec_ctr['PRANUM'], "itecod");
            foreach ($Itedest_tab as $Itedest_rec) {
                try {
                    $nrow = ItaDb::DBDelete($this->praLib->getPRAMDB(), 'ITEDEST', 'ROW_ID', $Itedest_rec['ROW_ID']);
                    if ($nrow == 0) {
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Cancellazione ITEDEST: " . $e->getMessage());
                    return false;
                }
            }
        }
        foreach ($arrayCtr['Itedest_tab'] as $keyDest => $itedest_rec) {
            unset($itedest_rec['ROW_ID']);
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ITEDEST", "ROW_ID", $itedest_rec);
                if ($nrow != 1) {
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore Inserimento ITEDEST: " . $e->getMessage());
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
            if ($normativa['NAME'] && $normativa['STREAM']) {
                if (!file_exists($destNormative . $normativa['NAME'])) {
                    if (!@file_put_contents($destNormative . $normativa['NAME'], base64_decode($normativa['STREAM']))) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore", "Errore in salvataggio del file Normativa " . $normativa['NAME']);
                        return false;
                    }
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

    private function seekNode(&$xml, $nodeName) {
        while ($xml->read()) {
            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $nodeName) {
                return $xml;
            }
        }
        return false;
    }

    public function CtrImportFields($arrayCtr) {
        try {
            $arrayAnapraDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANAPRA")->getFields();
            $arrayItepasDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITEPAS")->getFields();
            $arrayItedagDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITEDAG")->getFields();
            $arrayAnpdocDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANPDOC")->getFields();
            $arrayItereqDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITEREQ")->getFields();
            $arrayItenorDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITENOR")->getFields();
            $arrayItecontrolliDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITECONTROLLI")->getFields();
            $arrayAnanorDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANANOR")->getFields();
            $arrayAnareqDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANAREQ")->getFields();
            $arrayPracltDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "PRACLT")->getFields();
            $arrayAnasetDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANASET")->getFields();
            $arrayAnaattDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANAATT")->getFields();
            $arrayAnatipDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANATIP")->getFields();
            $arrayAnatspDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANATSP")->getFields();
            $arrayAnaeventiDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ANAEVENTI")->getFields();
            $arrayIteevtDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "ITEEVT")->getFields();
            if ($arrayCtr['Praazioni_rec']) {
                $arrayPraazioniDBFields = ItaDB::DBGetTableObject($this->praLib->getPRAMDB(), "PRAAZIONI")->getFields();
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage($e->getMessage());
            return false;
        }

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
        if ($arrayCtr['Anaeventi_rec']) {
            $arrayCtr["diffCampi"]['ANAEVENTI'] = $this->GetArrayDiffFields($arrayCtr['Anaeventi_rec'][0], $arrayAnaeventiDBFields);
        }
        if ($arrayCtr['Iteevt_rec']) {
            $arrayCtr["diffCampi"]['ITEEVT'] = $this->GetArrayDiffFields($arrayCtr['Iteevt_rec'][0], $arrayIteevtDBFields);
        }
        if ($arrayCtr['Praazioni_rec']) {
            $arrayCtr["diffCampi"]['PRAAZIONI'] = $this->GetArrayDiffFields($arrayCtr['Praazioni_rec'][0], $arrayPraazioniDBFields);
        }
        return $arrayCtr;
    }

    private function GetArrayDiffFields($arrayTabellaXml, $arrayTabellaDB) {
        foreach ($arrayTabellaXml as $campo => $valore) {
            if (!array_key_exists($campo, $arrayTabellaDB)) {
                $arrayDiff[$campo] = $valore;
            }
        }
        return $arrayDiff;
    }

    public function controllaAutore($Anapra_rec) {
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

    private function startElement($element, $attr = null) {
        $attrString = "";
        foreach ($attr as $key => $value) {
            $attrString .= ' ' . $key . '= "' . $this->parseText($value, ENT_COMPAT) . '"';
        }
        return "<" . $element . " " . $attrString . ">\r\n";
    }

    private function endElement($element) {
        return "</" . $element . ">\r\n";
    }

    private function text($text) {
        return $this->parseText($text, ENT_NOQUOTES);
    }

    private function comment($comment) {
        return "<!-- $comment -->\r\n";
    }

    private function parseText($text, $quoting) {
        return htmlspecialchars($text, $quoting, $this->encoding);
    }

    private function simpleElement($element, $text, $attr = null) {
        $attrString = "";
        foreach ($attr as $key => $value) {
            $attrString .= ' ' . $key . '="' . $this->parseText($value, ENT_COMPAT) . '"';
        }
        return "<" . $element . $attrString . ">" . $this->parseText($text, ENT_NOQUOTES) . "</" . $element . ">\r\n";
    }

    private function parseArraySimple($arr, $encoding) {
        $retXml = "";
        foreach ($arr as $Chiave => $Campo) {
            $attr = null;
            switch ($encoding[$Chiave]) {
                case 'base64':
                    $Campo = base64_encode($Campo);
                    $attr['dataencode'] = $encoding[$Chiave];
                    break;
            }
            $retXml .= $this->simpleElement($Chiave, $Campo, $attr);
        }
        return $retXml;
    }

    private function writeFile($fp, $data) {
        if (!fwrite($fp, utf8_encode($data))) {
            return false;
        }
        return true;
    }

    private function tableExists($PRAM_DB, $nome_tabella) {
        $tables = $PRAM_DB->listTables();
        $tables_lower = array_map('strtolower', $tables);
        return in_array(strtolower($nome_tabella), $tables_lower);
    }

}
