<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    30.06.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaWsFascicolazioneClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $ruolo = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($tipo = 'tem') {
        if ($tipo == 'tem') {
            $nameSpaces = array("tem" => "http://tempuri.org/");
        }
        $this->nameSpaces = $nameSpaces;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getUtente() {
        return $this->utente;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

    public function getRuolo() {
        return $this->ruolo;
    }

    public function setRuolo($ruolo) {
        $this->ruolo = $ruolo;
    }

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function getCodiceAmministrazione() {
        return $this->CodiceAmministrazione;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    public function setCodiceAmministrazione($CodiceAmministrazione) {
        $this->CodiceAmministrazione = $CodiceAmministrazione;
    }

    public function setCodiceAOO($CodiceAOO) {
        $this->CodiceAOO = $CodiceAOO;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param, $ns = "tem:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName.xml", $param);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName.xml", $client->request);
        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName.xml", $client->response);
        $time = time();
        if ($client->fault) {
            $this->fault = $client->faultstring;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    public function ws_CreaFascicoloString($dati) {
        $paramArr = array();
        if (isset($dati['Anno'])) {
            $AnnoSoapval = new soapval('Anno', 'Anno', $dati['Anno'], false, false);
            $paramArr[] = $AnnoSoapval;
        }
        if (isset($dati['Numero'])) {
            $NumeroSoapval = new soapval('Numero', 'Numero', $dati['Numero'], false, false);
            $paramArr[] = $NumeroSoapval;
        }
        if (isset($dati['Data'])) {
            $DataSoapval = new soapval('Data', 'Data', $dati['Data'], false, false);
            $paramArr[] = $DataSoapval;
        }
        if (isset($dati['Oggetto'])) {
            $OggettoSoapval = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
            $paramArr[] = $OggettoSoapval;
        }
        if (isset($dati['Classifica'])) {
            $ClassificaSoapval = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
            $paramArr[] = $ClassificaSoapval;
        }
        if (isset($dati['AltriDati'])) {
            $AltriDatiSoapval = new soapval('AltriDati', 'AltriDati', $dati['AltriDati'], false, false);
            $paramArr[] = $AltriDatiSoapval;
        }
        $UtenteSoapval = new soapval('Utente', 'Utente', "", false, false); //non utilizzato
        $paramArr[] = $UtenteSoapval;
        $RuoloSoapval = new soapval('Ruolo', 'Ruolo', "", false, false); //non utilizzato
        $paramArr[] = $RuoloSoapval;
        if (isset($dati['Eterogeneo'])) {
            $EterogeneoSoapval = new soapval('Eterogeneo', 'Eterogeneo', $dati['Eterogeneo'], false, false);
            $paramArr[] = $EterogeneoSoapval;
        }
        if (isset($dati['DataChiusura'])) {
            $DataChiusuraSoapval = new soapval('DataChiusura', 'DataChiusura', $dati['DataChiusura'], false, false);
            $paramArr[] = $DataChiusuraSoapval;
        }
        if (isset($dati['DatiAggiuntivi'])) {
            $DatiAggiuntiviSoapval = new soapval('DatiAggiuntivi', 'DatiAggiuntivi', $dati['DatiAggiuntivi'], false, false);
            $paramArr[] = $DatiAggiuntiviSoapval;
        }
        if (isset($dati['Applicazione'])) {
            $ApplicazioneSoapval = new soapval('Applicazione', 'Applicazione', $dati['Applicazione'], false, false);
            $paramArr[] = $ApplicazioneSoapval;
        }
        if (isset($dati['Aggiornamento'])) {
            $AggiornamentoSoapval = new soapval('Aggiornamento', 'Aggiornamento', $dati['Aggiornamento'], false, false);
            $paramArr[] = $AggiornamentoSoapval;
        }
        if (isset($dati['AnagraficaCf'])) {
            $AnagraficaCfSoapval = new soapval('AnagraficaCf', 'AnagraficaCf', $dati['AnagraficaCf'], false, false);
            $paramArr[] = $AnagraficaCfSoapval;
        }
        if (isset($dati['AnagraficaPiva'])) {
            $AnagraficaPivaSoapval = new soapval('AnagraficaPiva', 'AnagraficaPiva', $dati['AnagraficaPiva'], false, false);
            $paramArr[] = $AnagraficaPivaSoapval;
        }

        //CREO CDATA
        $strXML = "<tem:FascicoloInStr><![CDATA[<fascicoloIn>";
        foreach ($paramArr as $parametro) {
            $strXML .= $parametro->serialize('literal');
        }
        $strXML .= "</fascicoloIn>]]></tem:FascicoloInStr>";
        $param = $strXML;

        if (isset($dati['CodiceAmministrazione'])) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $dati['CodiceAmministrazione'], false, false);
        } else {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        }
        if (isset($dati['CodiceAOO'])) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $dati['CodiceAmministrazione'], false, false);
        } else {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('CreaFascicoloString', $param);
    }

    public function ws_FascicolaDocumento($dati) {
        $param = "";
        if (isset($dati['IDFascicolo'])) {
            $IDFascicoloSoapval = new soapval('tem:IDFascicolo', 'tem:IDFascicolo', $dati['IDFascicolo'], false, false);
            $param .= $IDFascicoloSoapval->serialize('literal');
        }
        if (isset($dati['IDDocumento'])) {
            $IDDocumentoSoapval = new soapval('tem:IDDocumento', 'tem:IDDocumento', $dati['IDDocumento'], false, false);
            $param .= $IDDocumentoSoapval->serialize('literal');
        }
        if (isset($dati['AggiornaClassifica'])) {
            $AggiornaClassificaSoapval = new soapval('tem:AggiornaClassifica', 'tem:AggiornaClassifica', $dati['AggiornaClassifica'], false, false);
            $param .= $AggiornaClassificaSoapval->serialize('literal');
        }
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', "", false, false); //non usato
        $param .= $UtenteSoapval->serialize('literal');
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', "", false, false); //non usato
        $param .= $RuoloSoapval->serialize('literal');
        //
        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        //
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        //
        if (isset($dati['Principale'])) {
            $PrincipaleSoapval = new soapval('tem:Principale', 'tem:Principale', $dati['Principale'], false, false);
            $param .= $PrincipaleSoapval->serialize('literal');
        }
        return $this->ws_call('FascicolaDocumento', $param);
    }

    public function ws_LeggiFascicoloString($dati) {
        $param = "";
        $IDFascicoloSoapval = new soapval('tem:IDFascicolo', 'tem:IDFascicolo', $dati['IDFascicolo'], false, false);
        $param .= $IDFascicoloSoapval->serialize('literal');
        $AnnoSoapval = new soapval('tem:Anno', 'tem:Anno', $dati['Anno'], false, false);
        $param .= $AnnoSoapval->serialize('literal');
        $NumeroSoapval = new soapval('tem:Numero', 'tem:Numero', $dati['Numero'], false, false);
        $param .= $NumeroSoapval->serialize('literal');
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', "", false, false); //non usato
        $param .= $UtenteSoapval->serialize('literal');
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', "", false, false); //non usato
        $param .= $RuoloSoapval->serialize('literal');
        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        if (isset($dati['CodiceClassificazione'])) {
            $CodiceClassificazioneSoapval = new soapval('tem:CodiceClassificazione', 'tem:CodiceClassificazione', $dati['CodiceClassificazione'], false, false);
            $param .= $CodiceClassificazioneSoapval->serialize('literal');
        }
        return $this->ws_call('LeggiFascicoloString', $param);
    }

}

?>
