<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaJasperReport
 *
 * @author michele
 */
require_once(ITA_LIB_PATH . '/itaPHPJasper/itaJasperClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPJasper/resourceDescriptor.class.php');
require_once (ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php');

class itaJasperReport {

    private $itaJClient;
    private $returnCode;
    private $message;

    public function __construct() {
        $this->itaJClient = new itaJasperClient();
        $this->itaJClient->setWebservices_uri(App::getConf('itaJasperReport.webservices_uri'));
        $this->itaJClient->setUserName(App::getConf('itaJasperReport.username'));
        $this->itaJClient->setPassword(App::getConf('itaJasperReport.password'));
    }

    /**
     *
     * @return String     Cartella repository per ditta/ente/tenancy
     */
    private function getOrganizationPath() {
        $organizationPath = App::getConf('itaJasperReport.organizationsFolder') . App::$utente->getKey('ditta');
        $operationResult = $this->itaJClient->ws_get($organizationPath);
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() != "0") {
            $rd = new resourceDescriptor();
            $tokens = explode('/', $organizationPath);
            $folderName = $tokens[sizeof($tokens) - 1];
            $folderParent = implode("/", explode('/', $organizationPath, -1));
            $rd->setName($folderName);
            $rd->setWsType(resourceDescriptor::TYPE_FOLDER);
            $rd->setUriString($organizationPath);
            $rd->setLabel($folderName);
            $rd->setIsNew('true');
            $rd->setDescription($folderName);
            $rd->setResourceProperty(resourceDescriptor::PROP_PARENT_FOLDER, $folderParent);
            $operationResult = $this->itaJClient->ws_put($rd);
            if (!$operationResult)
                return false;
            if ($this->itaJClient->getResult()->getReturnCode() != "0") {
                return false;
            }

            $rdPriv = new resourceDescriptor();
            $folderName = "private";
            $folderParent = $organizationPath;
            $rdPriv->setName($folderName);
            $rdPriv->setWsType(resourceDescriptor::TYPE_FOLDER);
            $rdPriv->setUriString($organizationPath . "/private");
            $rdPriv->setLabel($folderName);
            $rdPriv->setIsNew('true');
            $rdPriv->setDescription($folderName);
            $rdPriv->setResourceProperty(resourceDescriptor::PROP_PARENT_FOLDER, $folderParent);
            $operationResult = $this->itaJClient->ws_put($rdPriv);
            if (!$operationResult)
                return false;
            if ($this->itaJClient->getResult()->getReturnCode() != "0") {
                return false;
            }
        }

        return $organizationPath;
    }

    /**
     *
     * @return String   Ritorna il nome della cartella privata per utente nel repository, se non esiste la crea.
     */
    private function getPrivatePath() {
        $privatePath = $this->getOrganizationPath() . "/private/" . App::$utente->getkey('nomeUtente');
        $operationResult = $this->itaJClient->ws_get($privatePath);
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() != "0") {
            $rd = new resourceDescriptor();
            $rd->setName(App::$utente->getkey('nomeUtente'));
            $rd->setWsType(resourceDescriptor::TYPE_FOLDER);
            $rd->setUriString($privatePath);
            $rd->setLabel(App::$utente->getkey('nomeUtente'));
            $rd->setIsNew('true');
            $rd->setDescription('folder privato per il lancio di report Unit da ItaEngine per l\'utente ' . App::$utente->getkey('nomeUtente'));
            $rd->setResourceProperty(resourceDescriptor::PROP_PARENT_FOLDER, $this->getOrganizationPath() . "/private");
            $operationResult = $this->itaJClient->ws_put($rd);
            if (!$operationResult)
                return false;
            if ($this->itaJClient->getResult()->getReturnCode() != "0") {
                return false;
            }
        }
        return $privatePath;
    }

    public function clearCustomReport($report) {
        $arr = explode("/", $report);
        $customReport = end($arr);
        return $this->getPrivateReportPath($customReport);
    }

    private function getPrivateReportPath($report) {
        $privatePath = $this->getPrivatePath();
        if (!$privatePath)
            return false;
        $privateReportPath = $privatePath . "/" . $report;
        $operationResult = $this->itaJClient->ws_get($privateReportPath);
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() == "0") {
            $rd = new resourceDescriptor();
            $rd->setName($report);
            $rd->setWsType(resourceDescriptor::TYPE_REPORTUNIT);
            $rd->setUriString($privateReportPath);
            $rd->setIsNew('false');
            $operationResult = $this->itaJClient->ws_delete($rd);
            if (!$operationResult)
                return false;
            if ($this->itaJClient->getResult()->getReturnCode() != "0") {
                return false;
            }
        }
        return $privateReportPath;
    }

    private function getReportToCopy($report) {
        $reportBase = $this->getCompleteReportPath($report);
        $customReportPath = App::getConf('itaJasperReport.organizationsFolder') . App::$utente->getKey('ditta') . "/reports/" . App::getPath('reportRoute.' . substr($report, 0, 3)) . "/" . $report;
        $operationResult = $this->itaJClient->ws_get($customReportPath);
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() == "0") {
            return $customReportPath;
        } else {
            $operationResult = $this->itaJClient->ws_get($reportBase);
            if (!$operationResult)
                return false;
            if ($this->itaJClient->getResult()->getReturnCode() == "0") {


                return $reportBase;
            } else {
                throw new Exception($this->itaJClient->getResult()->getMessage(), $this->itaJClient->getResult()->getReturnCode());
                return false;
            }
        }
    }

    private function getCompleteReportPath($report) {
        $completeUri = App::getConf('itaJasperReport.italsoftFolder') . "/reports/" . App::getPath('reportRoute.' . substr($report, 0, 3)) . "/" . $report;
        return $completeUri;
    }

    public function customizeReport($report, $dataSource = null, $verbose = true) {
        try {
            return $this->privatizeReport($report, $dataSource);
        } catch (Exception $exc) {
            if ($verbose) {
                Out::msgStop("Eccezione in esecuzione Report", "<pre class=\"ita-Wordwrap\" style=\"font-size:1.2em\">" . $e->getMessage() . "<pre>");
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            return false;
        }

        return $this->privatizeReport($report, $dataSource);
    }

    private function privatizeReport($report, $dataSource = null) {
        $privateReportPath = $this->getPrivateReportPath($report);
        if (!$privateReportPath)
            return false;
        $reportToCopy = $this->getReportToCopy($report);
        $operationResult = $this->itaJClient->ws_get($reportToCopy, array());
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() != "0") {
            return false;
        }
        $rd = $this->itaJClient->getResult()->getResourceDescriptors();
        $operationResult = $this->itaJClient->ws_copy($rd[0], $privateReportPath);
        if (!$operationResult)
            return false;
        if ($this->itaJClient->getResult()->getReturnCode() != "0") {
            return false;
        }
        //$myResult = $this->itaJClient->ws_get("/test/praDipe");            
        $myResult = $this->itaJClient->ws_get($privateReportPath);
        $myReportUnitDescriptor = $this->itaJClient->getResult()->getResourceDescriptors();
        $myReportUnitResources = $myReportUnitDescriptor[0]->getChildren();
        $myReportUnitDataSource = null;
        foreach ($myReportUnitResources as $key => $myReportUnitResource) {
            if ($myReportUnitResource->getWsType() == 'jdbc') {
                $myReportUnitDataSource = $myReportUnitResource;
            }
        }

        $newRd = new resourceDescriptor();
        $newRd->setName('report_datasource');
        $newRd->setLabel('dataSourceCostruito');
        $newRd->setDescription('test datasource');
        $newRd->setWsType('jdbc');
        $newRd->setIsNew(resourceDescriptor::VALUE_TRUE);
        $newRd->setUriString($privateReportPath . "_files/report_datasource");
        $newRd_Prop = array(
            "PROP_PARENT_FOLDER" => $privateReportPath . "_files",
            "PROP_DATASOURCE_DRIVER_CLASS" => $dataSource->getJdbcDriverClass(),
            "PROP_DATASOURCE_USERNAME" => $dataSource->getUser(),
            "PROP_DATASOURCE_PASSWORD" => $dataSource->getPassword(),
            "PROP_DATASOURCE_CONNECTION_URL" => $dataSource->getJdbcConnectionUrl()
        );
        $newRd->setProperties($newRd_Prop);
        $putArgs = array(resourceDescriptor::MODIFY_REPORTUNIT => $privateReportPath);
        $newResult = $this->itaJClient->ws_put($newRd, null, $putArgs);
        $myResult = $this->itaJClient->ws_get($privateReportPath);
        $myReportUnitDescriptor = $this->itaJClient->getResult()->getResourceDescriptors();
        return $privateReportPath;
    }

    public function getAttachments() {
        $attachments = $this->itaJClient->getAttachments();
        return $attachments[0]['data'];
    }

    public function getReturnCode() {
        return $this->itaJClient->getResult()->getReurnCode();
    }

    public function getMessage() {
        return $this->itaJClient->getResult()->getMessage();
    }

    public function getSQLReport($dataSource, $report, $outputFormat = "PDF", $parameters = array(), $verbose = true, $privateReport = '') {
        $this->returnCode = "";
        $this->errorMessage = "";
        try {
            if ($privateReport) {
                $reportToRun = $privateReport;
            } else {
                $reportToRun = $this->privatizeReport($report, $dataSource);
            }
            if (!$reportToRun) {
                throw new Exception("Accesso al report fallito.\nControllare la configurazione delle risorse del Server dei report.", "-1");
                return false;
            }

            switch ($outputFormat) {
                case "RTF":
                    $jrOutFormat = resourceDescriptor::RUN_OUTPUT_FORMAT_RTF;
                    break;
                case "PDF":
                default:
                    $jrOutFormat = resourceDescriptor::RUN_OUTPUT_FORMAT_PDF;
                    break;
            }
            $arguments = array(resourceDescriptor::RUN_OUTPUT_FORMAT => $jrOutFormat);
            $responso = $this->itaJClient->ws_runReport($reportToRun, $parameters, $arguments);
            if ($responso) {
                $operationResult = $this->itaJClient->getResult();
                if ($operationResult->getReturnCode() == "0") {
                    return $this->getAttachments();
                } else {
                    if (!$privateReport) {
                        $this->getPrivateReportPath($report);
                    }
                    throw new Exception($operationResult->getMessage(), $operationResult->getReturnCode());
                    return false;
                }
            } else {
                if (!$privateReport) {
                    $this->getPrivateReportPath($report);
                }
                return false;
            }
        } catch (Exception $e) {
            if ($verbose) {
                Out::msgStop("Eccezione in esecuzione Report", "<pre class=\"ita-Wordwrap\" style=\"font-size:1.2em\">" . $e->getMessage() . "<pre>");
            } else {
                throw new Exception($e->getMessage(), $e->getCode());
            }
            return false;
        }
    }

    public function runSQLReport($dataSource, $report, $outputFormat, $parameters = array(), $verbose = true, $openMode = 'window', &$reportPath = null) {
        $retReport = $this->getSQLReport($dataSource, $report, $outputFormat, $parameters, $verbose);
        if ($retReport === false) {
            return false;
        }
        switch ($outputFormat) {
            case "RTF":
                $suffix = "rtf";
                break;
            case "PDF":
            default:
                $suffix = "pdf";
                break;
        }

        $PDFName = itaLib::createAppsTempPath("jasper-print") . '/' . $report . "-" . App::$utente->getKey('TOKEN') . "-" . itaLib::getRandBaseName() . ".$suffix";
        ;

        $ptr = fopen($PDFName, 'wb');
        fwrite($ptr, $retReport);
        fclose($ptr);

        // Se flag "openMode" = 'none', non apre subito il report in un'altra scheda del browser, 
        // ma restituisce il path nella variabile "reportPath" in modo da poter visualizzare il report
        // nella finestra di anteprima
        if ($openMode == 'none') {
            $reportPath = $PDFName;
            return;
        }

        if ($openMode == 'window') {
            Out::openDocument(utiDownload::getUrl(pathinfo($PDFName, PATHINFO_BASENAME), $PDFName));
        } elseif ($openMode == 'dialog') {
            Out::openIFrame($report, $report . "_toPrint", utiDownload::getUrl(
                            App::$utente->getKey('TOKEN') . "-" . $report . ".$suffix", $PDFName
                    ), '800', '580');
        } elseif ($openMode == 'directPrint') {
            $hostDialogID = Out::openObject($report, $report . "_toPrint", utiDownload::getUrl(
                                    App::$utente->getKey('TOKEN') . "-" . $report . ".$suffix", $PDFName
                            ), 400, 400);
        }
        $this->getPrivateReportPath($report);
        return true;
    }

    public function getSQLReportPDF($dataSource, $report, $parameters = array(), $verbose = true, $privateReport = '') {
        return $this->getSQLReport($dataSource, $report, "PDF", $parameters, $verbose, $privateReport);
    }

    public function runSQLReportPDF($dataSource, $report, $parameters = array(), $verbose = true, $openMode = 'window', &$reportPath = null) {
        return $this->runSQLReport($dataSource, $report, "PDF", $parameters, $verbose, $openMode, $reportPath);
    }

    public function getSQLReportRTF($dataSource, $report, $parameters = array(), $verbose = true) {
        return $this->getSQLReport($dataSource, $report, "RTF", $parameters, $verbose);
    }

    public function runSQLReportRTF($dataSource, $report, $parameters = array(), $verbose = true, $openMode = 'window') {
        return $this->runSQLReport($dataSource, $report, "RTF", $parameters, $verbose, $openMode);
    }

    public function runXMLReportPDF($dataSource, $report, $parameters = array(), $verbose = true, $openMode = 'window', &$reportPath = null) {
        if (array_key_exists('ARRAY_DATA', $parameters)) {
            $parameters['XML_DATA'] = $this->arrayData2xmlData($parameters['ARRAY_DATA']);
            unset($parameters['ARRAY_DATA']);
        }

        return $this->runSQLReport($dataSource, $report, "PDF", $parameters, $verbose, $openMode, $reportPath);
    }

    private function arrayData2XmlData($arrayData) {
        $xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlData .= "<DATA>";
        $xmlData .= "<LIST>";
        foreach ($arrayData as $row) {
            $xmlData .= "<ROW>";
            foreach ($row as $key => $value) {
                $xmlData .= "<$key>" . htmlspecialchars(utf8_encode($value), ENT_COMPAT) . " </$key>";
            }
            $xmlData .= "</ROW>";
        }
        $xmlData .= "</LIST>";
        $xmlData .= "</DATA>";
        return $xmlData;
    }

}
