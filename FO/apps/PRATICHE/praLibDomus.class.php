<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praWsDomusClient.class.php';

class praLibDomus {

    private $errCode;
    private $errMessage;

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getConfig() {
        $configArray = array('NAMESPACES' => array());
        $ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
        $frontOfficeLib = new frontOfficeLib;
        $envconfig_tab = $frontOfficeLib->getEnv_config("WSDOMUS", $ITAFRONTOFFICE_DB);

        foreach ($envconfig_tab as $envconfig_rec) {
            $chiave = substr($envconfig_rec['CHIAVE'], 6);

            if (in_array($chiave, array('DOM', 'DOM1'))) {
                $configArray['NAMESPACES'][strtolower($chiave)] = $envconfig_rec['CONFIG'];
            } else {
                $configArray[$chiave] = $envconfig_rec['CONFIG'];
            }
        }

        return $configArray;
    }

    public function getPratica($ricnum) {
        $wsConfig = $this->getConfig();

        $praWsDomusClient = new praWsDomusClient();

        $praWsDomusClient->setWebservices_uri($wsConfig['URI']);
        $praWsDomusClient->setWebservices_wsdl($wsConfig['WSDL']);
        $praWsDomusClient->setNamespace($wsConfig['NAMESPACE']);
        $praWsDomusClient->setNameSpaces($wsConfig['NAMESPACES']);
        $praWsDomusClient->setActionURI($wsConfig['ACTION']);

        $statusGetPratica = $praWsDomusClient->ws_GetPratica(array(
            'Password' => $wsConfig['PASSWORD'],
            'UserID' => $wsConfig['USERNAME'],
            'NumeroPratica' => $ricnum
        ));

        if (!$statusGetPratica) {
            if ($praWsDomusClient->getFault()) {
                $this->errCode = -1;
                $this->errMessage = 'Fault: ' . $praWsDomusClient->getFault();
            } elseif ($praWsDomusClient->getError()) {
                $this->errCode = -1;
                $this->errMessage = 'Error: ' . $praWsDomusClient->getError();
            }

            return false;
        }

        $resultGetPratica = $praWsDomusClient->getResult();

        if ($resultGetPratica['ResultInfo']['Tipo'] == 'Error') {
            $this->errCode = -1;
            $this->errMessage = $resultGetPratica['ResultInfo']['Descrizione'];
            return false;
        }

        return $resultGetPratica['Result'];
    }

    public function getDocumentiFascicolo($ricnum) {
        $wsConfig = $this->getConfig();

        $praWsDomusClient = new praWsDomusClient();

        $praWsDomusClient->setWebservices_uri($wsConfig['URI']);
        $praWsDomusClient->setWebservices_wsdl($wsConfig['WSDL']);
        $praWsDomusClient->setNamespace($wsConfig['NAMESPACE']);
        $praWsDomusClient->setNameSpaces($wsConfig['NAMESPACES']);
        $praWsDomusClient->setActionURI($wsConfig['ACTION']);

        $statusGetPratica = $praWsDomusClient->ws_GetDocumentiFascicolo(array(
            'Password' => $wsConfig['PASSWORD'],
            'UserID' => $wsConfig['USERNAME'],
            'NumeroRichiesta' => $ricnum
        ));

        if (!$statusGetPratica) {
            if ($praWsDomusClient->getFault()) {
                $this->errCode = -1;
                $this->errMessage = 'Fault: ' . $praWsDomusClient->getFault();
            } elseif ($praWsDomusClient->getError()) {
                $this->errCode = -1;
                $this->errMessage = 'Error: ' . $praWsDomusClient->getError();
            }

            return false;
        }

        $resultGetPratica = $praWsDomusClient->getResult();
        file_put_contents("/tmp/resultpratica.log", print_r($resultGetPratica, true));
        if ($resultGetPratica['ResultInfo']['Tipo'] == 'Error') {
            $this->errCode = -1;
            $this->errMessage = $resultGetPratica['ResultInfo']['Descrizione'];
            return false;
        }

        return $resultGetPratica['Result'];
    }

    public function getDocumentiProtocollo($DocNumber, $IstatComune) {
        $wsConfig = $this->getConfig();

        $praWsDomusClient = new praWsDomusClient();

        $praWsDomusClient->setWebservices_uri($wsConfig['URI']);
        $praWsDomusClient->setWebservices_wsdl($wsConfig['WSDL']);
        $praWsDomusClient->setNamespace($wsConfig['NAMESPACE']);
        $praWsDomusClient->setNameSpaces($wsConfig['NAMESPACES']);
        $praWsDomusClient->setActionURI($wsConfig['ACTION']);

        $statusGetAllegati = $praWsDomusClient->ws_GetDocumentiProtocollo(array(
            'Password' => $wsConfig['PASSWORD'],
            'UserID' => $wsConfig['USERNAME'],
            'DocNumber' => $DocNumber,
            'IstatComune' => $IstatComune
        ));

        if (!$statusGetAllegati) {
            if ($praWsDomusClient->getFault()) {
                $this->errCode = -1;
                $this->errMessage = 'Fault: ' . $praWsDomusClient->getFault();
            } elseif ($praWsDomusClient->getError()) {
                $this->errCode = -1;
                $this->errMessage = 'Error: ' . $praWsDomusClient->getError();
            }

            return false;
        }

        $resultGetAllegati = $praWsDomusClient->getResult();
        file_put_contents("/tmp/getAllegati.log", print_r($resultGetAllegati, true));
        if ($resultGetAllegati['ResultInfo']['Tipo'] == 'Error') {
            $this->errCode = -1;
            $this->errMessage = $resultGetAllegati['ResultInfo']['Descrizione'];
            return false;
        }

        return $resultGetAllegati['Result'];
    }

}
