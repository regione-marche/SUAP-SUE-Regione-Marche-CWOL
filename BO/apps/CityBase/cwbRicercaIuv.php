<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
include_once(ITA_LIB_PATH . '/itaPHPEFill/itaEFillClient.class.php');

function cwbRicercaIuv() {
    $cwbRicercaIuv = new cwbRicercaIuv();
    $cwbRicercaIuv->parseEvent();
    return;
}

class cwbRicercaIuv extends cwbBpaGenTab {

    private $clientiPagoPa = array(
        0 => array("CLIENTE" => 'Riccione', "IDAPPLICAZIONE" => 'cd9f24df-fb2d-41f5-a1a0-4f61104aba1b', "CODICECLIENTE" => '0000960'),
        1 => array("CLIENTE" => 'Cavenago', "IDAPPLICAZIONE" => 'a704e0e7-971e-4eab-bb6c-5cc5b2b0ce81', "CODICECLIENTE" => '0000350'),
        2 => array("CLIENTE" => 'Fano', "IDAPPLICAZIONE" => 'A7A78D05-D1FD-444A-875B-91CD2EC53542', "CODICECLIENTE" => '0000255'),
        3 => array("CLIENTE" => 'Utda', "IDAPPLICAZIONE" => '5B45242A-BC20-4131-829F-B2BF0F72A597', "CODICECLIENTE" => '0000566'),
        4 => array("CLIENTE" => 'Carpi', "IDAPPLICAZIONE" => '77CEA8CC-0727-4882-A1D3-6007BE200946', "CODICECLIENTE" => '0000578'),
        5 => array("CLIENTE" => 'Sirmione', "IDAPPLICAZIONE" => '50cdb661-cbea-4b42-8816-5dd05398fd3b', "CODICECLIENTE" => '0001165'),
    );

    function initVars() {
        $this->skipAuth = true;
        $this->libDB = new cwbLibDB_BGE();
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
        if ($this->close != true) {
        }
    }

    protected function preDestruct() {
        if ($this->close != true) {
            
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnVerificaIUV':
                        $this->ricercaPosizioneDaIUV();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CLIENTI':
                        $this->cambiaCliente($_POST[$this->nameForm . '_CLIENTI']);
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        Out::show($this->nameForm . '_divGestione');
        $this->initComboClienti();
    }

    public function ricercaPosizioneDaIUV($data) {
        $data = array(
            'IdApplicazione' => $_POST[$this->nameForm . '_IDAPPLICAZIONE'],
            'CodiceEnte' => $_POST[$this->nameForm . '_CODENTECRED'],
            'CodiceIdentificativo' => $_POST[$this->nameForm . '_IUV']
        );

        if (!$data['CodiceEnte']) {
            $this->handleError(-1, "Parametro CodiceEnte mancante");
            return false;
        }
        if (!$data['IdApplicazione']) {
            $this->handleError(-1, "Parametro IdApplicazione mancante");
            return false;
        }

        $wsParams = array('request' => $data);

        $namespaces = array(
            'plug' => "http://e-fil.eu/PnP/PlugAndPayDeliver",
        );

        // chiamo ws di efill ricercaPosizioneIUV
        $result = $this->callWs($wsParams, 'ricercaPosizioneIUV', 'https://services.plugandpay.it/Deliver/DigitBusDeliver.svc', 'http://e-fil.eu/PnP/PlugAndPayDeliver/IPlugAndPayDeliver/',$namespaces);

        Out::msgInfo('Info Posizione', print_r($result, true));
    }

    private function callWs($wsParams, $method, $endpoint, $soapActionPrefix, $namespaces = array(), $customNamespacePrefix = array(), $posizioneDebitoria = null, $nameSpacePrefixDefault = 'plug') {
        $client = new itaEFillClient();
        $client->setWebservices_uri($endpoint);
        $client->setNamespacePrefix($nameSpacePrefixDefault);
        $client->setNamespaces($namespaces);
        $client->setSoapActionPrefix($soapActionPrefix);
        $client->setCustomNamespacePrefix($customNamespacePrefix);
        $result = $client->$method($wsParams);
        $xmlRequest = $client->getRequest();
        $xmlResponse = $client->getResponse();
        if (!$result) {
            return false;
        } else {
            return $client->getResult();
        }
    }

    private function initComboClienti() {
        Out::html($this->nameForm . '_CLIENTI', ' ');
        foreach ($this->clientiPagoPa as $key => $cliente) {
            Out::select($this->nameForm . '_CLIENTI', 1, $key, 0, $cliente['CLIENTE']);
        }
        Out::valore($this->nameForm . '_CODENTECRED', $this->clientiPagoPa[0]['CODICECLIENTE']);
        Out::valore($this->nameForm . '_IDAPPLICAZIONE', $this->clientiPagoPa[0]['IDAPPLICAZIONE']);
    }

    private function cambiaCliente($key) {
        Out::valore($this->nameForm . '_CODENTECRED', $this->clientiPagoPa[$key]['CODICECLIENTE']);
        Out::valore($this->nameForm . '_IDAPPLICAZIONE', $this->clientiPagoPa[$key]['IDAPPLICAZIONE']);
    }

}

?>