<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    11.11.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';

class praLibAllegati {

    /**
     * Libreria di funzioni Generiche e Utility per Allegati Pratiche
     *
     */
    public $praLib;
    private $errCode;
    private $errMessage;
    private $risultatoRitorno;

    function __construct() {
        $this->praLib = new praLib();
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

    public function getRisultatoRitorno() {
        return $this->risultatoRitorno;
    }

    public function setRisultatoRitorno($risultatoRitorno) {
        $this->risultatoRitorno = $risultatoRitorno;
    }

    public function EvidenziaBloccaAllegati($arrayAllegati, $rowidAlle, $sha2View, $evidenzia = false) {
        $allegato = $arrayAllegati[$rowidAlle];
        //Modifico l'array dell'allegato selezionato per ricaricare la griglia
        if ($evidenzia == true) { // EVIDENZIAZIONE Allegati
            if ($allegato['PASEVI'] == 0) {
                $pasevi = 1;
                $color = "red";
                $fontWeight = "font-weight:bold";
                $fontSize = "font-size:1.2em";
            } else {
                $pasevi = 0;
                if ($allegato['ROWID'] == 0) {
                    $color = "orange";
                } else {
                    $color = "black";
                }
            }
            $orig_name = ($allegato['PASNAME']) ? $allegato['PASNAME'] : $allegato['FILEORIG'];
            $arrayAllegati[$rowidAlle]['NAME'] = "<p style = 'color:$color;$fontWeight;$fontSize'>" . $orig_name . "</p>";
        } else { //BLOCCO/SBLOCCO Allegati
            if ($allegato['PASLOCK'] == 1) {
                $paslock = 0;
                $icon = "unlock";
                $title = "Blocca Allegato";
            } else {
                $paslock = 1;
                $icon = "lock";
                $title = "Sblocca Allegato";
            }
            $arrayAllegati[$rowidAlle]['LOCK'] = "<span class=\"ita-icon ita-icon-$icon-16x16\">$title</span>";
        }
        $arrayAllegati[$rowidAlle]["PASLOCK"] = $paslock;
        $arrayAllegati[$rowidAlle]["PASEVI"] = $pasevi;
        //
        if ($sha2View == true) {
            $pasdoc_tab_TotFile = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . substr($allegato['PASKEY'], 0, 10) . "%' AND PASSHA2 = '" . $allegato['PASSHA2'] . "'", true);
            //Modifico tutti gli allegati dello stesso PASSHA2 e aggiorno il DB
            foreach ($pasdoc_tab_TotFile as $pasdoc_rec) {
                if ($evidenzia == true) { // EVIDENZIAZIONE Allegati
                    if ($allegato['PASEVI'] == 0) {
                        $pasdoc_rec['PASEVI'] = 1;
                    } else {
                        $pasdoc_rec['PASEVI'] = 0;
                    }
                } else { //BLOCCO/SBLOCCO Allegati
                    if ($allegato['PASLOCK'] == 1) {
                        $pasdoc_rec['PASLOCK'] = 0;
                    } else {
                        $pasdoc_rec['PASLOCK'] = 1;
                    }
                }
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($exc->getMessage());
                    return false;
                }
            }
        } else {
            if (!isset($allegato['ROWID'])) {
                $this->errCode = -1;
                $this->setErrMessage("Attenzione... L'id dell'allegato " . $allegato['FILEORIG'] . " non è stato trovato.");
                return false;
            }
            $pasdoc_rec = $this->praLib->GetPasdoc($allegato['ROWID'], 'ROWID');
            if ($evidenzia == true) { // EVIDENZIAZIONE Allegati
                if ($allegato['PASEVI'] == 0) {
                    $pasdoc_rec['PASEVI'] = 1;
                } else {
                    $pasdoc_rec['PASEVI'] = 0;
                }
            } else { //BLOCCO/SBLOCCO Allegati
                if ($allegato['PASLOCK'] == 1) {
                    $pasdoc_rec['PASLOCK'] = 0;
                } else {
                    $pasdoc_rec['PASLOCK'] = 1;
                }
            }
            if ($pasdoc_rec) {
                try {
                    $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
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
        return $arrayAllegati;
    }

    public function ColorpickerAllegati($Rowid, $color, $decod = false) {
        if (empty($Rowid)) {
            Out::msgStop("Errore", "Allegato non evidenziato");
            return false;
        }
        $decimal_color = hexdec($color);
        $pasdoc_rec = array(
            "ROWID" => $Rowid,
            "PASEVI" => $decimal_color
        );

        try {
            ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PASDOC', 'ROWID', $pasdoc_rec);
            return true;
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage($exc->getMessage());
            Out::msgStop("Errore", "Allegato non evidenziato" . $exc);
            return false;
        }
    }

    public function BlockCellGridAllegati($arrayAllegati, $gridAllegati) {
        foreach ($arrayAllegati as $keyAlle => $praAlle_row) {
            $pasdoc_rec = $this->praLib->GetPasdoc($praAlle_row['ROWID'], "ROWID");
            if ($praAlle_row['isLeaf'] == 'false') {
                TableView::setCellValue($gridAllegati, $keyAlle, 'NOTE', "", 'not-editable-cell', '', 'false');
                TableView::setCellValue($gridAllegati, $keyAlle, 'STATO', "", 'not-editable-cell', '', 'false');
            }
            //Blocco Modifica Allegati Protocollati
//            if ($pasdoc_rec['PASPRTROWID'] != 0 && $pasdoc_rec['PASPRTCLASS'] == "PROGES") {
//                $proges_rec = $this->praLib->GetProges($pasdoc_rec['PASPRTROWID'], "rowid");
//                if ($proges_rec['GESNPR']) {
//                    TableView::setCellValue($gridAllegati, $keyAlle, 'NOTE', "", 'not-editable-cell', '', 'false');
//                }
//            }
            if ($praAlle_row['PASLOCK'] == 1 || $pasdoc_rec['PASLOCK'] == 1) {
                TableView::setCellValue($gridAllegati, $keyAlle, 'NOTE', "", 'not-editable-cell', '', 'false');
                TableView::setCellValue($gridAllegati, $keyAlle, 'STATO', "", 'not-editable-cell', '', 'false');
            }
        }
    }

    public function ChangeNoteStato($arrayAllegati, $rowid, $cellname, $sha2View, $value) {
        $pasdoc_rec = array();
        if ($arrayAllegati[$rowid]['ROWID'] != 0) {
            $pasdoc_rec = $this->praLib->GetPasdoc($arrayAllegati[$rowid]['ROWID'], 'ROWID');
        }
        switch ($cellname) {
            case 'NOTE':
                //$pasdoc_rec['PASNOT'] = $value;
                $campoDB = "PASNOT";
                $arrayAllegati[$rowid]['NOTE'] = $value;
                break;
            case 'STATOALLE': // Stato allegato dal Passo
            case 'STATO':     // Stato allegato dalla Pratica
                $campoDB = "PASSTA";
                $arrayAllegati[$rowid][$cellname] = $this->praLib->GetStatoAllegati($value);
                $arrayAllegati[$rowid]["PASSTA"] = $value;
                //$pasdoc_rec['PASSTA'] = $value;
                //$arrayAllegati[$rowid]['STATO'] = $this->praLib->GetStatoAllegati($value);
                //
                if ($sha2View == true) {
                    $pasdoc_tab_TotFile = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY LIKE '" . substr($pasdoc_rec['PASKEY'], 0, 10) . "%' AND PASSHA2 = '" . $pasdoc_rec['PASSHA2'] . "'", true);
                    //$arrayAllegati[$rowid]['STATO'] = $this->praLib->GetStatoAllegati($value);
                    $arrayAllegati[$rowid][$cellname] = $this->praLib->GetStatoAllegati($value);
                    foreach ($pasdoc_tab_TotFile as $pasdoc_rec_tot) {
                        $pasdoc_rec_tot['PASSTA'] = $value;
                        try {
                            $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec_tot);
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
                break;
        }
        if ($pasdoc_rec) {
            $pasdoc_rec[$campoDB] = $value;
            try {
                $nrow = ItaDB::DBUpdate($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }
        }
        return $arrayAllegati;
    }

    public function ComponiPDFconSegnatura($segnatura, $input) {
        $ret = '';
        $xmlPATH = itaLib::createAppsTempPath('praPDFComposer');
        $xmlFile = $xmlPATH . "/" . md5(rand() * time()) . ".xml";
        $xmlRes = fopen($xmlFile, "w");
        if (!file_exists($xmlFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in composizione PDF");
            return false;
        } else {
            $output = $xmlPATH . "/" . md5(rand() * time()) . "." . pathinfo($input, PATHINFO_EXTENSION);
            //$output = $input;
            $xml = $this->CreaXmlPdf($segnatura, $input, $output);
            fwrite($xmlRes, $xml);
            fclose($xmlRes);
            $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFile;
            exec($command, $ret);
            //
            $taskXml = false;
            foreach ($ret as $value) {
                $arrayExec = explode("|", $value);
                if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                    $taskXml = true;
                    break;
                } else {
                    $errMsg = $arrayExec[0] . " - " . $arrayExec[1] . " - " . $arrayExec[2];
                }
            }
            if ($taskXml == false) {
                $this->setErrCode(-1);
                $this->setErrMessage($errMsg);
                return false;
            } else {
                if (!@rename($output, $input)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nel rinominare il PDF $output");
                    return false;
                }
                @unlink($xmlFile);
                return $output;
            }
        }
    }

    public function CreaXmlPdf($segnatura, $input, $output) {
        $filent_rec = $this->praLib->GetFilent(10);
        $xml .= "<root>\r\n";
        $xml .= "<task name=\"watermark\">\r\n";
        $xml .= "<debug>0</debug>\r\n";
        $xml .= "<firstpageonly>" . (int) $filent_rec['FILDE1'] . "</firstpageonly>\r\n";
        $xml .= "<x-coord>" . (int) $filent_rec['FILDE3'] . "</x-coord>\r\n";
        $xml .= "<y-coord>" . (int) $filent_rec['FILDE4'] . "</y-coord>\r\n";
        $xml .= "<rotation>" . (int) $filent_rec['FILDE2'] . "</rotation>\r\n";
        $xml .= "<string>$segnatura </string>\r\n";
        $xml .= "<input>$input</input>\r\n";
        $xml .= "<output>$output</output>\r\n";
        $xml .= "</task>\r\n";
        $xml .= "</root>\r\n";
        return $xml;
    }

    public function GetMarcatureFromTemplate($pratica, $rowidPasdoc, $template = '') {
        if (!$template) {
            return '';
        }
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($pratica);
        $praLibVar->setRowidPasdoc($rowidPasdoc);
        //$praLibVar->setTipoCom($param['TipoProtocollo']);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $segnaturaDef = $this->praLib->GetStringDecode($dictionaryValues, $template);

        return $segnaturaDef;
    }

    public function GetMarcatureString($param, $pratica, $rowidPasdoc, $idTemplate = 10) {
        $filent_rec = $this->praLib->GetFilent($idTemplate);
        if (!$filent_rec["FILVAL"]) {
            $template = 'Prot. n. @{$PRAALLEGATI.NUMPROT}@/@{$PRAALLEGATI.ANNOPROT}@';
        } else {
            $template = $filent_rec["FILVAL"];
        }
        $Pasdoc_rec = $this->praLib->GetPasdoc($rowidPasdoc, "ROWID");
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($pratica);
        $praLibVar->setChiavePasso($Pasdoc_rec['PASKEY']);
        $praLibVar->setRowidPasdoc($rowidPasdoc);
        //$praLibVar->setTipoCom($param['TipoProtocollo']);
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllData();
        $segnaturaDef = $this->praLib->GetStringDecode($dictionaryValues, $template);
        return $segnaturaDef;
    }

    public function getDatiMarcaturaAlle($alleRowid) {
        $Pasdoc_rec = $this->praLib->GetPasdoc($alleRowid, "ROWID");
        $Propas_rec = $this->praLib->GetPropas($Pasdoc_rec['PASKEY'], "propak");

        if ($Propas_rec) {
            //Se è un passo FO prendo i dati del protocollo della pratica
            if ($Propas_rec['PROPUB'] == 1) {
                $Proges_rec = $this->praLib->GetProges($Propas_rec['PRONUM']);
                $numProt = substr($Proges_rec['GESNPR'], 4);
                //include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
                $metaDati = proIntegrazioni::GetMetedatiProt($Proges_rec['GESNUM']);
                if (isset($metaDati['Data'])) {
                    $dataProt = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                }
                $oraProt = $metaDati['Ora'];
                $annoProt = substr($Proges_rec['GESNPR'], 0, 4);
                $tipoProt = $Proges_rec['GESPAR'];
            } else {
                //Se è un passo endoprocedimento, prendo i dati del protocollo in base ai dati con cui è stato bloccato l'allegato
                if ($Pasdoc_rec['PASPRTROWID'] != 0 && $Pasdoc_rec['PASPRTCLASS']) {
                    if ($Pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                        $Proges_rec = $this->praLib->GetProges($Pasdoc_rec['PASPRTROWID'], "rowid");
                        $numProt = substr($Proges_rec['GESNPR'], 4);
                        //include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
                        $metaDati = proIntegrazioni::GetMetedatiProt($Proges_rec['GESNUM']);
                        if (isset($metaDati['Data'])) {
                            $dataProt = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                        }
                        $oraProt = $metaDati['Ora'];
                        $annoProt = substr($Proges_rec['GESNPR'], 0, 4);
                        $tipoProt = $Proges_rec['GESPAR'];
                    } elseif ($Pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                        $Pracom_rec = $this->praLib->GetPracom($Pasdoc_rec['PASPRTROWID'], "rowid");
                        $numProt = substr($Pracom_rec['COMPRT'], 4);
                        $metaDati = proIntegrazioni::GetMetedatiProt($Propas_rec['PROPAK'], 'PASSO', $Pracom_rec['COMTIP']);
                        if (isset($metaDati['Data'])) {
                            $dataProt = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                        }
                        $oraProt = $metaDati['Ora'];
                        $dataProt = substr($Pracom_rec['COMDPR'], 6, 2) . "/" . substr($Pracom_rec['COMDPR'], 4, 2) . "/" . substr($Pracom_rec['COMDPR'], 0, 4);
                        $annoProt = substr($Pracom_rec['COMPRT'], 0, 4);
                        $tipoProt = $Pracom_rec['COMTIP'];
                    }
                } else {
                    $Pracom_rec = $this->praLib->GetPracomP($Propas_rec['PROPAK']);
                    $numProt = substr($Pracom_rec['COMPRT'], 4);
                    $metaDati = proIntegrazioni::GetMetedatiProt($Propas_rec['PROPAK'], 'PASSO', $Pracom_rec['COMTIP']);
                    if (isset($metaDati['Data'])) {
                        $dataProt = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                    }
                    $oraProt = $metaDati['Ora'];
                    $dataProt = substr($Pracom_rec['COMDPR'], 6, 2) . "/" . substr($Pracom_rec['COMDPR'], 4, 2) . "/" . substr($Pracom_rec['COMDPR'], 0, 4);
                    $annoProt = substr($Pracom_rec['COMPRT'], 0, 4);
                    $tipoProt = $Pracom_rec['COMTIP'];
                }
            }
        } else {
            $Proges_rec = $this->praLib->GetProges($Pasdoc_rec['PASKEY']);
            $numProt = substr($Proges_rec['GESNPR'], 4);
            $tipoProt = $Proges_rec['GESPAR'];
            $annoProt = substr($Proges_rec['GESNPR'], 0, 4);
            //include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
            $metaDati = proIntegrazioni::GetMetedatiProt($Proges_rec['GESNUM']);
            if ($metaDati) {
                if (isset($metaDati['Data'])) {
                    $dataProt = substr($metaDati['Data'], 6, 2) . "/" . substr($metaDati['Data'], 4, 2) . "/" . substr($metaDati['Data'], 0, 4);
                }
                $oraProt = $metaDati['Ora'];
            }
        }
        return array(
            "NUMPROT" => $numProt,
            "TIPOPROT" => $tipoProt,
            "DATAPROT" => $dataProt,
            "ORAPROT" => $oraProt,
            "ANNOPROT" => $annoProt
        );
    }

    public function ApriAllegato($nameformChiamante, $doc, $codicePratica, $chiavePasso) {
        if ($doc['PROVENIENZA'] == "TESTOBASE") {
            $file = '';
            $pramPath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);

            if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf")) {
                $filename = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf";
                $file = $pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf";
            }

            if (file_exists($pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m")) {
                $filename = pathinfo($doc['FILEORIG'], PATHINFO_FILENAME) . ".pdf.p7m";
                $file = $pramPath . "/" . pathinfo($doc['FILEPATH'], PATHINFO_FILENAME) . ".pdf.p7m";
            }

            if ($file) {
                Out::openDocument(utiDownload::getUrl(
                                $filename, $file
                        )
                );
            } else {
                $this->ApriAllegatoTestoBase($nameformChiamante, $doc, $codicePratica, $chiavePasso, false);
            }
        } else {
            if ($doc['FILEORIG']) {
                $name = $doc['FILEORIG'];
            } else {
                $name = $doc['FILENAME'];
            }
            $ext = strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION));
            switch ($ext) {
                case "docx":
                    $docLib = new docLib();
                    $docParametri = itaModel::getInstance('docParametri');
                    $valueOO = $docParametri->getParametro('SEG_OPENOO_DOCX');
                    if ($valueOO == 1) {
                        $docLib->openOODocument($doc['ROWID'], $doc['FILEPATH'], $doc['FILEORIG'], "PRATICHE");
                    } else {
                        Out::openDocument(utiDownload::getUrl(
                                        $name, $doc['FILEPATH']
                                )
                        );
                    }
                    break;
                default:
                    Out::openDocument(utiDownload::getUrl(
                                    $name, $doc['FILEPATH']
                            )
                    );
                    break;
            }
        }
    }

    public function ApriAllegatoTestoBase($nameformChiamante, $doc, $codicePratica, $chiavePasso, $readonly = true) {
        $propas_rec = $this->praLib->getPropas($chiavePasso);

        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($codicePratica);
        $praLibVar->setChiavePasso($chiavePasso);

        if ($propas_rec['PROPUB'] == 1) {
            $praLibVar->setFrontOfficeFlag(true);
        }

        $dictionaryLegend = $praLibVar->getLegendaPratica('adjacency', 'smarty');
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllDataFormatted();

        $tipoAlle = strtolower(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION));
        switch ($tipoAlle) {
            case 'xhtml':
                $contentFile = @file_get_contents($doc['FILEPATH']);
                if (!$contentFile) {
                    Out::msgStop("Attenzione", "Errore in lettura del contenuto del file " . $doc['FILEPATH']);
                    break;
                }
                $model = 'utiEditDiag';
                $rowidText = $_POST['rowid'];
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['edit_text'] = $contentFile;
                $_POST['returnModel'] = $nameformChiamante;
                $_POST['returnEvent'] = 'returnEditDiag';
                $_POST['returnField'] = '';
                $_POST['rowidText'] = $rowidText;
                $_POST['dictionaryLegend'] = $dictionaryLegend;
                $_POST['dictionaryValues'] = $dictionaryValues;
                $_POST['readonly'] = $readonly;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
            case 'docx':
                $docLib = new docLib();
                $docParametri = itaModel::getInstance('docParametri');
                $valueOO = $docParametri->getParametro('SEG_OPENOO_DOCX');
                if ($valueOO == 1) {
                    $docLib->openOODocument($doc['ROWID'], $doc['FILEPATH'], $doc['FILEORIG'], "PRATICHE");
                } else {
                    Out::openDocument(utiDownload::getUrl(
                                    basename($doc['FILEORIG'] ? : $doc['FILEPATH']), $doc['FILEPATH']
                            )
                    );
                }
                break;
        }
    }

    public function GeneraPDF($doc, $codicePratica, $chiavePasso = false) {
        $docLib = new docLib();

        /*
         * Preparo il dizionario
         */
        $target = strtoupper(pathinfo($doc['FILEPATH'], PATHINFO_EXTENSION));
        $praLibVar = new praLibVariabili(array('TARGET' => $target));
        if ($_POST[$this->nameForm . '_PROPAS']['PROPUB'] == 1) {
            $praLibVar->setFrontOfficeFlag(true);
        }

        $praLibVar->setCodicePratica($codicePratica);
        if ($chiavePasso) {
            $praLibVar->setChiavePasso($chiavePasso);
        }
        $dictionaryValues = $praLibVar->getVariabiliPratica()->getAllDataFormatted();

        /*
         * Creo il PDF
         */
        $filepath = pathinfo($doc['FILEPATH'], PATHINFO_DIRNAME);
        $newFilePdf = pathinfo($doc['FILENAME'], PATHINFO_FILENAME) . ".pdf";

        switch ($target) {
            case 'XHTML':
                /*
                 * Leggo il body Value
                 */
                $bodyValue = @file_get_contents($doc['FILEPATH']);
                $pdfPreview = $docLib->Xhtml2Pdf($bodyValue, $dictionaryValues, $filepath . "/" . $newFilePdf);
                if ($pdfPreview === false) {
                    $this->setErrCode($docLib->getErrCode());
                    $this->setErrMessage($docLib->getErrMessage());
                    return false;
                }
                break;

            case 'DOCX':
                $codiceTestoBase = isset($doc['TESTOBASE']) ? $doc['TESTOBASE'] : false;
                $compiledDocx = $docLib->compileDOCX($doc['FILEPATH'], $dictionaryValues, false, $codiceTestoBase);
                if (!$compiledDocx) {
                    $this->setErrCode($docLib->getErrCode());
                    $this->setErrMessage($docLib->getErrMessage());
                    return false;
                }

                if (!$docLib->docx2Pdf($compiledDocx, $filepath . "/" . $newFilePdf)) {
                    $this->setErrCode($docLib->getErrCode());
                    $this->setErrMessage($docLib->getErrMessage());
                    return false;
                }

                unlink($compiledDocx);
                break;
        }

        return true;
    }

    public function ScaricaAllegatiZipPratica($pronum, $propar = '', $praAlle = '') {
        /*
         * Se il file zip esiste, lo cancello
         */

        $subPathZip = "praZipFile-" . md5(microtime());
        $tempPathZip = itaLib::createAppsTempPath($subPathZip);
        $NomeFileZip = "allegati_prat_" . substr($pronum, 0, 10) . $propar . ".zip";
        $fileZip = $tempPathZip . '/' . $NomeFileZip;
        if (file_exists($fileZip)) {
            if (!@unlink($fileZip)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in cancellazione file ZIP.');
                return false;
            }
        }
        /*
         * Creo il file zip
         */
        $archiv = new ZipArchive();
        if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Creazione file ZIP.');
            return false;
        }
        /*
         * Carica Allegati:
         */
        $Pasdoc_tab = array();
        if ($propar == 'All') {
            foreach ($praAlle as $key => $value) {
                if (!$value['ROWID']) {
                    continue;
                }
                $Pasdoc_tab = array_merge($Pasdoc_tab, $this->praLib->GetPasdoc($value['ROWID'], 'ROWID', true)); // RICAVO ALLEGATI DI PASSO + PRATICA
            }
        } else {
            $Pasdoc_tab = $this->praLib->GetPasdoc($pronum, 'codice', true);  // RICAVO ALLEGATI DI PRATICA
        }
        if (!$Pasdoc_tab) {
            Out::msgStop('Attenzione', 'Nessun allegato trovato per la creazione file zip');
            return false;
        }
        foreach ($Pasdoc_tab as $keyAlle => $elemento) {
            $tipo = 'PASSO'; // default è path allegato di passo
            if (strlen($elemento['PASKEY']) < '11') {
                $tipo = 'PROGES';
            }

            if (pathinfo($elemento['PASFIL'], PATHINFO_EXTENSION) == "xhtml" || pathinfo($elemento['PASFIL'], PATHINFO_EXTENSION) == "docx") {
                // CONTROLLO PER I TESTI BASE PER SCARICARE QUANDO PRESENTE QUELLO GENERATO
                $filename = $this->getDocGenereato($elemento['PASKEY'], $elemento['PASFIL'], $tipo);
                if (pathinfo($filename, PATHINFO_EXTENSION) != 'xhtml') {
                    $elemento['PASNAME'] = pathinfo($elemento['PASNAME'], PATHINFO_FILENAME) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                }
            }

            if (strlen($elemento['PASKEY']) > '11' && $propar == 'All') {
                // PER NON SOVRASCRIVERE ALLEGATI CON LO STESSO NOME PASNAME IN PASSI DIVERSI
                $Propas_rec = $this->praLib->GetPropas($elemento['PASKEY']);
                $archiv->addFromString($Propas_rec['PROSEQ'] . '_' . $elemento['PASNAME'], $this->GetDocBinary($elemento['ROWID'], $tipo));
            } else {
                $archiv->addFromString($elemento['PASNAME'], $this->GetDocBinary($elemento['ROWID'], $tipo));
            }
        }
        $archiv->close();
        Out::openDocument(utiDownload::getUrl($NomeFileZip, $fileZip));
        return true;
    }

    public function GetDocBinary($rowidAnadoc, $tipo, $returnBase64 = false) {
        $Pasdoc_rec = $this->praLib->GetPasdoc($rowidAnadoc, 'ROWID');
        if (!$Pasdoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura binario. Lettura di Anadoc Fallita.");
            return false;
        }

        if (pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION) == "xhtml" || pathinfo($Pasdoc_rec['PASFIL'], PATHINFO_EXTENSION) == "docx") {
            $praPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], $tipo);
            $ext = $this->getDocGenereato($Pasdoc_rec['PASKEY'], $Pasdoc_rec['PASFIL'], $tipo);
            $filePathSorg .= $praPath . "/" . $ext;
            // $praPath
        } else {
            $praPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], $tipo);
            $filePathSorg = $praPath . "/" . $Pasdoc_rec['PASFIL'];
        }

        if ($returnBase64 === true) {
            $base64 = base64_encode(file_get_contents($filePathSorg));
            if (!$base64) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia Allegato. Errore nella lettura del file binario.");
                return false;
            }
            return $base64;
        }
        return file_get_contents($filePathSorg);
    }

    public function getDocGenereato($Paskey, $Pasfil, $tipo = 'PASSO') {

        /*
         * Per i testi base compilati
         * per estrerre eventuale testo compilato
         */

        $namefile = pathinfo($Pasfil, PATHINFO_FILENAME);
        $praPath = $this->praLib->SetDirectoryPratiche(substr($Paskey, 0, 4), $Paskey, $tipo);
        if (file_exists($praPath . "/" . pathinfo($Pasfil, PATHINFO_FILENAME) . ".pdf.p7m")) {
            $ext = $namefile . '.pdf.p7m';
        } elseif (file_exists($praPath . "/" . pathinfo($Pasfil, PATHINFO_FILENAME) . ".pdf")) {
            $ext = $namefile . '.pdf';
        } elseif (file_exists($praPath . "/" . pathinfo($Pasfil, PATHINFO_FILENAME) . ".xhtml")) {
            $ext = $namefile . '.xhtml';
        } elseif (file_exists($praPath . "/" . pathinfo($Pasfil, PATHINFO_FILENAME) . ".docx")) {
            $ext = $namefile . '.docx';
        }

        return $ext;
    }

    public function GetImgPreview($ext, $path, $doc) {
        $title = "Clicca per le funzioni disponibili";
        if (strtolower($ext) == "pdf") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        } else if (strtolower($ext) == "xhtml") {
            if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m") || file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
            } else if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
            }
        } else if (strtolower($ext) == "docx") {
            if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m") || file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf.p7m.p7m")) {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"File firmato caricato\"></span>";
            } else if (file_exists($path . "/" . pathinfo($doc['PASFIL'], PATHINFO_FILENAME) . ".pdf")) { //mm 22/11/2012
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-pdf-24x24\" title=\"PDF Generato\"></span>";
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"Genera PDF\"></span>";
            }
        } else if (strtolower($ext) == "p7m") {
            $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-shield-green-24x24\" title=\"Verifica il file Firmato\"></span>";
        } else if (in_array(strtolower($ext), array('xls', 'xlsx'))) {
            if (file_exists($path . '/' . $this->getFilenameFoglioElaborato($doc['PASFIL'], true))) {
                $preview = '<span style="display:inline-block;" class="ita-icon ita-icon-excel-flat-24x24" title="Definitivo caricato"></span>';
            } else {
                $preview = "<span style=\"display:inline-block;\" class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
            }
        } else {
            $preview = "<span style=\"display:inline-block;\"class=\"ita-icon ita-icon-ingranaggio-24x24\" title=\"$title\"></span>";
        }
        return $preview;
    }

    public function getFilenameFoglioElaborato($filename, $def = false) {
        return substr($filename, 0, strlen(pathinfo($filename, PATHINFO_EXTENSION)) * -1) . ($def ? 'definitivo.' : 'elaborato.') . pathinfo($filename, PATHINFO_EXTENSION);
    }

    public function getStringDestinatari($destinazioni) {
        $arrayDest = unserialize($destinazioni);
        $strDest = "";
        if (is_array($arrayDest)) {
            foreach ($arrayDest as $dest) {
                $Anaddo_rec = $this->praLib->GetAnaddo($dest);
                $strDest .= $Anaddo_rec['DDONOM'] . "<br>";
            }
        }
        return $strDest;
    }

}
