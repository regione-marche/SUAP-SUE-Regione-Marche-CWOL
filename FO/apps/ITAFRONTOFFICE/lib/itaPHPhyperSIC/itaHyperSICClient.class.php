<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    10.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaHyperSICClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;
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

    public function setNameSpaces($tipo = 'ser') {
        if ($tipo == 'tem') {
            $nameSpaces = array("tem" => "http://tempuri.org/");
        }
        if ($tipo == 'ser') {
            $nameSpaces = array("ser" => "http://services.apsystems.it/");
        }
        if ($tipo == 'xs') {
            $nameSpaces = array("xs" => "http://www.w3.org/2001/XMLSchema");
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

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    public function getResponse() {
        return $this->response;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param, $ns = "ser:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 1;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;

        $client->response_timeout = $this->timeout;
        $client->setHeaders($this->getSecurity($this->username, $this->password));
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents("/users/tmp/param_$operationName.xml", $param);
//        file_put_contents("/users/tmp/request_$operationName.xml", $client->request);
//        file_put_contents("/users/tmp/response_$operationName.xml", $client->response);
        $this->response = $client->response;
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

    function getSecurity($username, $password) {
        $secHeader = "
        <ser:AuthenticationDetails>
            <ser:UserName>$username</ser:UserName>
            <ser:Password>$password</ser:Password>
        </ser:AuthenticationDetails>";
        return $secHeader;
    }

    public function ws_GetAllegato($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $annoSoapval = new soapval('ser:sbustaAllegatiFirmati', 'ser:sbustaAllegatiFirmati', $dati['sbustaAllegatiFirmati'], false, false);
        $paramArr[] = $annoSoapval;
        $param = $paramArr;
        return $this->ws_call('GetAllegato', $param);
    }

    public function ws_GetComune($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $codiceIstatSoapval = new soapval('ser:codiceIstat', 'ser:codiceIstat', $dati['codiceIstat'], false, false);
        $paramArr[] = $codiceIstatSoapval;
        $descrizioneSoapval = new soapval('ser:descrizione', 'ser:descrizione', $dati['descrizione'], false, false);
        $paramArr[] = $descrizioneSoapval;
        $siglaProvinciaSoapval = new soapval('ser:siglaProvincia', 'ser:siglaProvincia', $dati['siglaProvincia'], false, false);
        $paramArr[] = $siglaProvinciaSoapval;
        $codiceStatoSoapval = new soapval('ser:codiceStato', 'ser:codiceStato', $dati['codiceStato'], false, false);
        $paramArr[] = $codiceStatoSoapval;
        $param = $paramArr;
        return $this->ws_call('GetComune', $param);
    }

    public function ws_GetCorrispondente($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $codiceFiscaleSoapval = new soapval('ser:codiceFiscale', 'ser:codiceFiscale', $dati['codiceFiscale'], false, false);
        $paramArr[] = $codiceFiscaleSoapval;
        $descrizioneSoapval = new soapval('ser:descrizione', 'ser:descrizione', $dati['descrizione'], false, false);
        $paramArr[] = $descrizioneSoapval;
        $indirizzoSoapval = new soapval('ser:indirizzo', 'ser:indirizzo', $dati['indirizzo'], false, false);
        $paramArr[] = $indirizzoSoapval;
        $descrizioneComuneSoapval = new soapval('ser:descrizioneComune', 'ser:descrizioneComune', $dati['descrizioneComune'], false, false);
        $paramArr[] = $descrizioneComuneSoapval;
        $descrizioneUfficioSoapval = new soapval('ser:descrizioneUfficio', 'ser:descrizioneUfficio', $dati['descrizioneUfficio'], false, false);
        $paramArr[] = $descrizioneUfficioSoapval;
        $codiceIPASoapval = new soapval('ser:codiceIPA', 'ser:codiceIPA', $dati['codiceIPA'], false, false);
        $paramArr[] = $codiceIPASoapval;
        $tipologiaSoapval = new soapval('ser:tipologia', 'ser:tipologia', $dati['tipologia'], false, false);
        $paramArr[] = $tipologiaSoapval;
        $param = $paramArr;
        return $this->ws_call('GetCorrispondente', $param);
    }

    public function ws_GetFascicolo($dati) {
        $paramArr = array();
        $classificazioneSoapval = new soapval('ser:classificazione', 'ser:classificazione', $dati['classificazione'], false, false);
        $paramArr[] = $classificazioneSoapval;
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $descrizioneSoapval = new soapval('ser:descrizione', 'ser:descrizione', $dati['descrizione'], false, false);
        $paramArr[] = $descrizioneSoapval;
        $annoSoapval = new soapval('ser:anno', 'ser:anno', $dati['anno'], false, false);
        $paramArr[] = $annoSoapval;
        $annualeSoapval = new soapval('ser:annuale', 'ser:annuale', $dati['annuale'], false, false);
        $paramArr[] = $annualeSoapval;
        $dataInizioValiditaSoapval = new soapval('ser:dataInizioValidita', 'ser:dataInizioValidita', $dati['dataInizioValidita'], false, false);
        $paramArr[] = $dataInizioValiditaSoapval;
        $dataFineValiditaSoapval = new soapval('ser:dataFineValidita', 'ser:dataFineValidita', $dati['dataFineValidita'], false, false);
        $paramArr[] = $dataFineValiditaSoapval;
        $codiceUfficioSoapval = new soapval('ser:codiceUfficio', 'ser:codiceUfficio', $dati['codiceUfficio'], false, false);
        $paramArr[] = $codiceUfficioSoapval;
        $userNameSoapval = new soapval('ser:userName', 'ser:userName', $dati['userName'], false, false);
        $paramArr[] = $userNameSoapval;
        $statoSoapval = new soapval('ser:stato', 'ser:stato', $dati['stato'], false, false);
        $paramArr[] = $statoSoapval;
        $param = $paramArr;
        return $this->ws_call('GetFascicolo', $param);
    }

    public function ws_InsertCorrispondente($dati) {
        $paramArr = array();
        $codiceFiscaleSoapval = new soapval('ser:codiceFiscale', 'ser:codiceFiscale', $dati['codiceFiscale'], false, false);
        $paramArr[] = $codiceFiscaleSoapval;
        $descrizioneSoapval = new soapval('ser:descrizione', 'ser:descrizione', $dati['descrizione'], false, false);
        $paramArr[] = $descrizioneSoapval;
        $indirizzoSoapval = new soapval('ser:indirizzo', 'ser:indirizzo', $dati['indirizzo'], false, false);
        $paramArr[] = $indirizzoSoapval;
        $capSoapval = new soapval('ser:cap', 'ser:cap', $dati['cap'], false, false);
        $paramArr[] = $capSoapval;
        $codice_comuneSoapval = new soapval('ser:codice_comune', 'ser:codice_comune', $dati['codice_comune'], false, false);
        $paramArr[] = $codice_comuneSoapval;
        $emailSoapval = new soapval('ser:email', 'ser:email', $dati['email'], false, false);
        $paramArr[] = $emailSoapval;
        $telefonoSoapval = new soapval('ser:telefono', 'ser:telefono', $dati['telefono'], false, false);
        $paramArr[] = $telefonoSoapval;
        $faxSoapval = new soapval('ser:fax', 'ser:fax', $dati['fax'], false, false);
        $paramArr[] = $faxSoapval;
        $userNameSoapval = new soapval('ser:userName', 'ser:userName', $dati['userName'], false, false);
        $paramArr[] = $userNameSoapval;
        $tipologiaSoapval = new soapval('ser:tipologia', 'ser:tipologia', $dati['tipologia'], false, false);
        $paramArr[] = $tipologiaSoapval;
        $param = $paramArr;
        return $this->ws_call('InsertCorrispondente', $param);
    }

    public function ws_GetProcedimento($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $descrizioneSoapval = new soapval('ser:descrizione', 'ser:descrizione', $dati['descrizione'], false, false);
        $paramArr[] = $descrizioneSoapval;
        $StatoSoapval = new soapval('ser:Stato', 'ser:Stato', $dati['Stato'], false, false);
        $paramArr[] = $StatoSoapval;
        $param = $paramArr;
        return $this->ws_call('GetProcedimento', $param);
    }

    public function ws_GetProtocolloGenerale($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $annoSoapval = new soapval('ser:anno', 'ser:anno', $dati['anno'], false, false);
        $paramArr[] = $annoSoapval;
        $da_numeroSoapval = new soapval('ser:daNumero', 'ser:daNumero', $dati['daNumero'], false, false);
        $paramArr[] = $da_numeroSoapval;
        $a_numeroSoapval = new soapval('ser:aNumero', 'ser:aNumero', $dati['aNumero'], false, false);
        $paramArr[] = $a_numeroSoapval;
        $da_dataSoapval = new soapval('ser:daData', 'ser:daData', $dati['daData'], false, false);
        $paramArr[] = $da_dataSoapval;
        $a_dataSoapval = new soapval('ser:aData', 'ser:aData', $dati['aData'], false, false);
        $paramArr[] = $a_dataSoapval;
        $tipologiaSoapval = new soapval('ser:tipologia', 'ser:tipologia', $dati['tipologia'], false, false);
        $paramArr[] = $tipologiaSoapval;
        $statoSoapval = new soapval('ser:stato', 'ser:stato', $dati['stato'], false, false);
        $paramArr[] = $statoSoapval;
        $oggettoSoapval = new soapval('ser:oggetto', 'ser:oggetto', $dati['oggetto'], false, false);
        $paramArr[] = $oggettoSoapval;
        $codiceCorrispondenteSoapval = new soapval('ser:codiceCorrispondente', 'ser:codiceCorrispondente', $dati['codiceCorrispondente'], false, false);
        $paramArr[] = $codiceCorrispondenteSoapval;
        $descrizioneCorrispondenteSoapval = new soapval('ser:descrizioneCorrispondente', 'ser:descrizioneCorrispondente', $dati['descrizioneCorrispondente'], false, false);
        $paramArr[] = $descrizioneCorrispondenteSoapval;
        $codiceProcedimentoSoapval = new soapval('ser:codiceProcedimento', 'ser:codiceProcedimento', $dati['codiceProcedimento'], false, false);
        $paramArr[] = $codiceProcedimentoSoapval;
        $classificazioneSoapval = new soapval('ser:classificazione', 'ser:classificazione', $dati['classificazione'], false, false);
        $paramArr[] = $classificazioneSoapval;
        $codiceFascicoloSoapval = new soapval('ser:codiceFascicolo', 'ser:codiceFascicolo', $dati['codiceFascicolo'], false, false);
        $paramArr[] = $codiceFascicoloSoapval;
        $annoFascicoloSoapval = new soapval('ser:annoFascicolo', 'ser:annoFascicolo', $dati['annoFascicolo'], false, false);
        $paramArr[] = $annoFascicoloSoapval;
        $param = $paramArr;
        return $this->ws_call('GetProtocolloGenerale', $param);
    }

    private function GetSchemaInputProtocollo() {
        $schema = '<xs:schema id="protocolli" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata">
               <xs:element name="protocolli" msdata:IsDataSet="true" msdata:UseCurrentLocale="true">
                  <xs:complexType>
                     <xs:choice minOccurs="0" maxOccurs="unbounded">
                        <xs:element name="protocollo">
                           <xs:complexType>
                              <xs:sequence>
                                 <xs:element name="codice" type="xs:string" minOccurs="0"/>
                                 <xs:element name="numero" type="xs:string" minOccurs="0"/>
                                 <xs:element name="data" type="xs:string" minOccurs="0"/>
                                 <xs:element name="oggetto" type="xs:string" minOccurs="0"/>
                                 <xs:element name="classificazione" type="xs:string" minOccurs="0"/>
                                 <xs:element name="fascicolo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="anno_fascicolo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice_procedimento" type="xs:string" minOccurs="0"/>
                                 <xs:element name="tipologia" type="xs:string" minOccurs="0"/>
                                 <xs:element name="registro_emergenza" type="xs:string" minOccurs="0"/>
                                 <xs:element name="numero_registro_emergenza" type="xs:string" minOccurs="0"/>
                                 <xs:element name="data_registro_emergenza" type="xs:string" minOccurs="0"/>
                              </xs:sequence>
                           </xs:complexType>
                        </xs:element>
                        <xs:element name="mittente">
                           <xs:complexType>
                              <xs:sequence>
                                 <xs:element name="codice_protocollo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice" type="xs:string" minOccurs="0"/>
                                 <xs:element name="descrizione" type="xs:string" minOccurs="0"/>
                                 <xs:element name="indirizzo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="cap" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice_comune" type="xs:string" minOccurs="0"/>
                                 <xs:element name="descrizione_comune" type="xs:string" minOccurs="0"/>
                                 <xs:element name="e_mail" type="xs:string" minOccurs="0"/>
                              </xs:sequence>
                           </xs:complexType>
                        </xs:element>
                        <xs:element name="destinatario">
                           <xs:complexType>
                              <xs:sequence>
                                 <xs:element name="codice_protocollo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice" type="xs:string" minOccurs="0"/>
                                 <xs:element name="descrizione" type="xs:string" minOccurs="0"/>
                                 <xs:element name="indirizzo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="cap" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice_comune" type="xs:string" minOccurs="0"/>
                                 <xs:element name="descrizione_comune" type="xs:string" minOccurs="0"/>
                                 <xs:element name="e_mail" type="xs:string" minOccurs="0"/>
                                 <xs:element name="per_conoscenza" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice_spedizione" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice_ufficio" type="xs:string" minOccurs="0"/>
                                 <xs:element name="descrizione_ufficio" type="xs:string" minOccurs="0"/>
                              </xs:sequence>
                           </xs:complexType>
                        </xs:element>
                        <xs:element name="allegato">
                           <xs:complexType>
                              <xs:sequence>
                                 <xs:element name="codice_protocollo" type="xs:string" minOccurs="0"/>
                                 <xs:element name="codice" type="xs:string" minOccurs="0"/>
                                 <xs:element name="nome" type="xs:string" minOccurs="0"/>
                              </xs:sequence>
                           </xs:complexType>
                        </xs:element>
                     </xs:choice>
                  </xs:complexType>
               </xs:element>
            </xs:schema>';
        return $schema;
    }

    public function ws_InsertProtocolloGenerale($dati) {
        $param = "";
        /*
         * HP: chiamare GetInputStructProtocollo per prendere lo schema da passare ad InsertProtocolloGenerale
         * poco fattibile perchè bisognerebbe andare a rileggere la stringa xml della risposta, quindi meglio prenderla già direttamente da una stringa codificata nel client
         * recuperabile dal metodo GetSchemaInputProtocollo
         */
//            $retGetStruct = $this->ws_call('GetInputStructInsertProtocolloGenerale', array());
//            $retStruct = $this->getResult();
//            $arrStruct = $retStruct['schema'];
        $schema = $this->GetSchemaInputProtocollo();

        $dsDatiString = "";
        $dsDati = array();
        $dsDati['protocolli'] = $dati['dsDati']['protocolli'];

        $openDiffrgram = '<diffgr:diffgram xmlns:msdata="urn:schemas-microsoft-com:xml-msdata" xmlns:diffgr="urn:schemas-microsoft-com:xml-diffgram-v1">';
        $closeDiffgram = '</diffgr:diffgram>';

        $protocolliSoapval = new soapval('protocolli', 'protocolli', $dsDati['protocolli'], false, false);
        $dsDatiString = "<ser:dsDati>" . $schema . $openDiffrgram . $protocolliSoapval->serialize('literal') . $closeDiffgram . "</ser:dsDati>";
//            $dsDatiSoapval = new soapval('ser:dsDati', 'ser:dsDati', $dsDatiString, false, false);
        $userNameSoapval = new soapval('ser:userName', 'ser:userName', $dati['userName'], false, false);
//            $param = $dsDatiSoapval->serialize('literal') . $userNameSoapval->serialize('literal');
        $param = $dsDatiString . $userNameSoapval->serialize('literal');
        return $this->ws_call('InsertProtocolloGenerale', $param);
    }

    public function ws_GetProtocolloInterno($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $annoSoapval = new soapval('ser:anno', 'ser:anno', $dati['anno'], false, false);
        $paramArr[] = $annoSoapval;
        $da_numeroSoapval = new soapval('ser:daNumeroProtocolloGenerale', 'ser:daNumeroProtocolloGenerale', $dati['daNumeroProtocolloGenerale'], false, false);
        $paramArr[] = $da_numeroSoapval;
        $a_numeroSoapval = new soapval('ser:aNumeroProtocolloGenerale', 'ser:aNumeroProtocolloGenerale', $dati['aNumeroProtocolloGenerale'], false, false);
        $paramArr[] = $a_numeroSoapval;
        $da_dataSoapval = new soapval('ser:da_data', 'ser:da_data', $dati['da_data'], false, false);
        $paramArr[] = $da_dataSoapval;
        $a_dataSoapval = new soapval('ser:a_data', 'ser:a_data', $dati['a_data'], false, false);
        $paramArr[] = $a_dataSoapval;
        $tipologiaSoapval = new soapval('ser:tipologia', 'ser:tipologia', $dati['tipologia'], false, false);
        $paramArr[] = $tipologiaSoapval;
        $statoSoapval = new soapval('ser:stato', 'ser:stato', $dati['stato'], false, false);
        $paramArr[] = $statoSoapval;
        $oggettoSoapval = new soapval('ser:oggetto', 'ser:oggetto', $dati['oggetto'], false, false);
        $paramArr[] = $oggettoSoapval;
        $descrizioneCorrispondenteSoapval = new soapval('ser:descrizioneCorrispondente', 'ser:descrizioneCorrispondente', $dati['descrizioneCorrispondente'], false, false);
        $paramArr[] = $descrizioneCorrispondenteSoapval;
        $codiceProcedimentoSoapval = new soapval('ser:codiceProcedimento', 'ser:codiceProcedimento', $dati['codiceProcedimento'], false, false);
        $paramArr[] = $codiceProcedimentoSoapval;
        $classificazioneSoapval = new soapval('ser:classificazione', 'ser:classificazione', $dati['classificazione'], false, false);
        $paramArr[] = $classificazioneSoapval;
        $fascicoloSoapval = new soapval('ser:fascicolo', 'ser:fascicolo', $dati['fascicolo'], false, false);
        $paramArr[] = $fascicoloSoapval;
        $annoFascicoloSoapval = new soapval('ser:annoFascicolo', 'ser:annoFascicolo', $dati['annoFascicolo'], false, false);
        $paramArr[] = $annoFascicoloSoapval;
        $codiceUfficioSoapval = new soapval('ser:codiceUfficio', 'ser:codiceUfficio', $dati['codiceUfficio'], false, false);
        $paramArr[] = $codiceUfficioSoapval;
        $loginUtenteSoapval = new soapval('ser:loginUtente', 'ser:loginUtente', $dati['loginUtente'], false, false);
        $paramArr[] = $loginUtenteSoapval;
        $principaleSoapval = new soapval('ser:principale', 'ser:principale', $dati['principale'], false, false);
        $paramArr[] = $principaleSoapval;
        $param = $paramArr;
        return $this->ws_call('GetProtocolloInterno', $param);
    }

    public function ws_InsertAllegatoProtocolloGenerale($dati) {
        $paramArr = array();
        $codiceSoapval = new soapval('ser:codice', 'ser:codice', $dati['codice'], false, false);
        $paramArr[] = $codiceSoapval;
        $fileBytesSoapval = new soapval('ser:fileBytes', 'ser:fileBytes', $dati['fileBytes'], false, false);
        $paramArr[] = $fileBytesSoapval;
        $fileNameSoapval = new soapval('ser:fileName', 'ser:fileName', $dati['fileName'], false, false);
        $paramArr[] = $fileNameSoapval;
        $userNameSoapval = new soapval('ser:userName', 'ser:userName', $dati['userName'], false, false);
        $paramArr[] = $userNameSoapval;
        $param = $paramArr;
        return $this->ws_call('InsertAllegatoProtocolloGenerale', $param);
    }

}

?>
