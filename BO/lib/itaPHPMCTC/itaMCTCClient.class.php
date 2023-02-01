<?php

/**
 *
 * Classe per collegamento ws Paleo
 *
 * PHP Version 5
 *
 * @category
 * @package    lib/itaPHPPaleo
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    20.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaMCTCClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;
    private $request;
    private $response;

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    public function getNameSpaces() {
        return $this->nameSpaces;
    }

    public function setNameSpaces($nameSpaces) {
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

    public function setpassword($password) {
        $this->password = $password;
    }

    function getWSSecurity($username, $password, $param = array()) {
        $wsse = '<wsse:Security
			xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
			SOAP-ENV:mustUnderstand="1">
			<wsse:UsernameToken>
				<wsse:Username>' . $username . '</wsse:Username>
				<wsse:Password
					Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . $password . '</wsse:Password>
			</wsse:UsernameToken>
		</wsse:Security>';
        return $wsse;
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

    public function getRequest() {
        return $this->request;
    }

    public function getResponse() {
        return $this->response;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        //setting timeout
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->timeout;
        //setting headers
        $client->setHeaders($this->getWSSecurity($this->username, $this->password));
        $client->soap_defencoding = 'UTF-8';
//        $result = $client->call($operationName, $param);
        $soapAction = "";
        $headers = $client->getHeaders();
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);

        file_put_contents('C:/tmp/MCTC/MCTCParam_' . $soapAction . date('YmdHis') . '.xml', $param);
        file_put_contents('C:/tmp/MCTC/MCTCRequest_' . $soapAction . date('YmdHis') . '.xml', $client->request);
        file_put_contents('C:/tmp/MCTC/MCTCResponse_' . $soapAction . date('YmdHis') . '.xml', $client->response);

        $this->request = $client->request;
        $this->response = $client->response;
        if ($client->fault) {
            $this->fault = $client->faultstring;
            //throw new Exception("Request Fault:" . $this->fault);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
//                throw new Exception("Client SOAP Error: " . $err);
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    public function ws_cambioPassword($param) {
        $utente = $param['utente'];
        $vecchiaPassword = $param['vecchiaPassword'];
        $nuovaPassword = $param['nuovaPassword'];
        $confermaNuovaPassword = $param['confermaNuovaPassword'];

        $RequestString = "
         <sic:utente>" . $utente . "</sic:utente>
         <sic:vecchiaPassword>" . $vecchiaPassword . "</sic:vecchiaPassword>
         <sic:nuovaPassword>" . $nuovaPassword . "</sic:nuovaPassword>
         <sic:confermaNuovaPassword>" . $confermaNuovaPassword . "</sic:confermaNuovaPassword>
      ";
        return $this->ws_call("sic:richiestaCambioPassword", $RequestString);
    }

    public function ws_dettaglioAutoveicoloBase($param) {
        $targa = $param['numeroTarga'];
        $telaio = $param['numeroTelaio'];
        $cf = $param['codiceFiscale'];
        $pdf = false;
        if (isset($param['pdf'])) {
            $pdf = $param['pdf'];
        }
        $RequestString = "<inf:login><inf:codicePin></inf:codicePin></inf:login>";
        $RequestString .= "<inf:dettaglioAutoveicoloBaseInput>";
        if ($cf) {
            $RequestString .= "<inf:codiceFiscale>" . $cf . "</inf:codiceFiscale>";
        }
        if ($targa) {
            $RequestString .= "<inf:targa><inf:numeroTarga>" . $targa . "</inf:numeroTarga></inf:targa>";
        }
        if ($telaio) {
            $RequestString .= "<inf:telaio><inf:numeroTelaio>" . $telaio . "</inf:numeroTelaio></inf:telaio>";
        }
        $RequestString .= "</inf:dettaglioAutoveicoloBaseInput>";
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:dettaglioAutoveicoloBaseRequest", $RequestString);
    }

    public function ws_dettaglioAutoveicoloComproprietari($param) {
        $targa = $param['numeroTarga'];
        $telaio = $param['numeroTelaio'];
        $cf = $param['codiceFiscale'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "<inf:login><inf:codicePin></inf:codicePin></inf:login>";
        $RequestString .= "<inf:dettaglioAutoveicoloBaseInput>";
        if ($cf) {
            $RequestString .= "<inf:codiceFiscale>" . $cf . "</inf:codiceFiscale>";
        }
        if ($targa) {
            $RequestString .= "<inf:targa><inf:numeroTarga>" . $targa . "</inf:numeroTarga></inf:targa>";
        }
        if ($telaio) {
            $RequestString .= "<inf:telaio><inf:numeroTelaio>" . $telaio . "</inf:numeroTelaio></inf:telaio>";
        }
        $RequestString .= "</inf:dettaglioAutoveicoloBaseInput>";
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:dettaglioAutoveicoloComproprietariRequest", $RequestString);
    }

    public function ws_dettaglioMotoveicoloComproprietari($param) {
        $targa = $param['numeroTarga'];
        $telaio = $param['numeroTelaio'];
        $cf = $param['codiceFiscale'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "<inf:login><inf:codicePin></inf:codicePin></inf:login>";
        $RequestString .= "<inf:dettaglioMotoveicoloBaseInput>";
        if ($cf) {
            $RequestString .= "<inf:codiceFiscale>" . $cf . "</inf:codiceFiscale>";
        }
        if ($targa) {
            $RequestString .= "<inf:targa><inf:numeroTarga>" . $targa . "</inf:numeroTarga></inf:targa>";
        }
        if ($telaio) {
            $RequestString .= "<inf:telaio><inf:numeroTelaio>" . $telaio . "</inf:numeroTelaio></inf:telaio>";
        }
        $RequestString .= "</inf:dettaglioMotoveicoloBaseInput>";
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:dettaglioMotoveicoloComproprietariRequest", $RequestString);
    }

    public function ws_dettaglioCiclomotoreComproprietari($param) {
        $targa = $param['numeroTarga'];
        $telaio = $param['numeroTelaio'];
        $cf = $param['codiceFiscale'];
        $cic = $param['cic'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "<inf:login><inf:codicePin></inf:codicePin></inf:login>";
        $RequestString .= "<inf:dettaglioCiclomotoreBaseInput>";
        if ($cf) {
            $RequestString .= "<inf:codiceFiscale>" . $cf . "</inf:codiceFiscale>";
        }
        if ($targa) {
            $RequestString .= "<inf:targa><inf:numeroTarga>" . $targa . "</inf:numeroTarga></inf:targa>";
        }
        if ($telaio) {
            $RequestString .= "<inf:telaio><inf:numeroTelaio>" . $telaio . "</inf:numeroTelaio></inf:telaio>";
        }
        if ($cic) {
            $RequestString .= "<inf:cic><inf:cic>" . $cic . "</inf:cic></inf:cic>";
        }
        $RequestString .= "</inf:dettaglioCiclomotoreBaseInput>";
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:dettaglioCiclomotoreComproprietariRequest", $RequestString);
    }

    public function ws_dettaglioRimorchioComproprietari($param) {
        $targa = $param['numeroTarga'];
        $telaio = $param['numeroTelaio'];
        $cf = $param['codiceFiscale'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "<inf:login><inf:codicePin></inf:codicePin></inf:login>";
        $RequestString .= "<inf:dettaglioRimorchioBaseInput>";
        if ($cf) {
            $RequestString .= "<inf:codiceFiscale>" . $cf . "</inf:codiceFiscale>";
        }
        if ($targa) {
            $RequestString .= "<inf:targa><inf:numeroTarga>" . $targa . "</inf:numeroTarga></inf:targa>";
        }
        if ($telaio) {
            $RequestString .= "<inf:telaio><inf:numeroTelaio>" . $telaio . "</inf:numeroTelaio></inf:telaio>";
        }
        $RequestString .= "</inf:dettaglioRimorchioBaseInput>";
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:dettaglioRimorchioComproprietariRequest", $RequestString);
    }

    public function ws_dettaglioAutoveicoloComproprietariTrasferimentoRes($param) {
        $targa = $param['numeroTarga'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "   
                <inf:login>
                    <inf:codicePin></inf:codicePin>
                </inf:login>
                <inf:dettaglioAutoveicoloBaseInput>
                    <inf:targa>
                        <inf:numeroTarga>" . $targa . "</inf:numeroTarga>
                    </inf:targa>
                </inf:dettaglioAutoveicoloBaseInput>
                <inf:pdf>" . $pdf . "</inf:pdf>
        ";
        return $this->ws_call("inf:dettaglioAutoveicoloComproprietariTrasferimentiResRequest", $RequestString);
    }

    public function ws_verificaCoperturaAssicurativa($param) {
        $targa = $param['codiceTarga'];
        $tipoVeicolo = $param['codiceTipoVeicolo'];
        $data = $param['dataRiferimento'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "   
                <inf:login>
                    <inf:codicePin></inf:codicePin>
                </inf:login>";
        if ($targa) {
            $RequestString .= "<inf:codiceTarga>" . $targa . "</inf:codiceTarga>";
        }
        if ($tipoVeicolo) {
            $RequestString .= "<inf:codiceTipoVeicolo>" . $tipoVeicolo . "</inf:codiceTipoVeicolo>";
        }
        if ($data) {
            $RequestString .= "<inf:dataRiferimento>" . $data . "</inf:dataRiferimento>";
        }
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:verificaAssicurazioneRequest", $RequestString);
    }

    public function ws_verificaCoperturaAssicurativaScadenzaRevisione($param) {
        $targa = $param['codiceTarga'];
        $tipoVeicolo = $param['codiceTipoVeicolo'];
        $data = $param['dataRiferimento'];
        $pdf = 'false';
        if ($param['pdf']) {
            $pdf = 'true';
        }
        $RequestString = "   
                <inf:login>
                    <inf:codicePin></inf:codicePin>
                </inf:login>";
        if ($targa) {
            $RequestString .= "<inf:codiceTarga>" . $targa . "</inf:codiceTarga>";
        }
        if ($tipoVeicolo) {
            $RequestString .= "<inf:codiceTipoVeicolo>" . $tipoVeicolo . "</inf:codiceTipoVeicolo>";
        }
        if ($data) {
            $RequestString .= "<inf:dataRiferimento>" . $data . "</inf:dataRiferimento>";
        }
        $RequestString .= "<inf:pdf>" . $pdf . "</inf:pdf>";
        return $this->ws_call("inf:verificaAssicurazioneScadenzaRevisioneRequest", $RequestString);
    }

    public function setMCTCClientConfig($metodo, $username = "", $password = "") {
        require_once (ITA_BASE_PATH . '/apps/Cds/cdsLib.class.php');
        $cdsLib = new cdsLib();
        $uri = $cdsLib->GetParametroMCTC("ENDPOINT");
        $ns = $cdsLib->GetParametroMCTC("NAMESPACE");
        if ($username == "") {
            $username = $cdsLib->GetParametroMCTC("USER");
        }
        if ($password == "") {
            $password = $cdsLib->GetParametroMCTC("PASSWD");
        }
        $timeout = $cdsLib->GetParametroMCTC("TIMEOUT");
//        $metodo = $this->cdsLib->GetParametroMCTC("METODO");

        $this->setWebservices_uri($uri . $metodo . "/");
        $this->setWebservices_wsdl($uri . $metodo . "/" . $metodo . ".wsdl");
        $this->setNamespaces(array("inf" => "$ns"));
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setTimeout($timeout);
    }

    public function getMetodoVisuraDaTipoVeicolo($tipoMCTC) {
        switch ($tipoMCTC) {
            case "A":
                return "dettaglioAutoveicoloComproprietari";
                break;
            case "M":
                return "dettaglioMotoveicoloComproprietari";
                break;
            case "C":
                return "dettaglioCiclomotoreComproprietari";
                break;
            case "R":
                return "dettaglioRimorchioComproprietari";
                break;
            default:
                return "dettaglioAutoveicoloComproprietari";
                break;
        }
        return false;
    }

}
