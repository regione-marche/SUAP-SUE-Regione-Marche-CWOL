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

class itaJirideClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $ruolo = "";
    private $tipoDocumento = "";
    private $aggiornaAnagrafiche = "";
    private $CodiceAmministrazione = "";
    private $CodiceAOO = "";
    private $TipoNumeroDocumento = "";
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
        if ($tipo == 'sch') {
            $nameSpaces = array("sch" => "http://wwwpa2k/Ulisse/iride/web_services/ws_tabelle/schema");
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

    public function getTipoDocumento() {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento($tipoDocumento) {
        $this->tipoDocumento = $tipoDocumento;
    }

    public function getAggiornaAnagrafiche() {
        return $this->aggiornaAnagrafiche;
    }

    public function setAggiornaAnagrafiche($aggiornaAnagrafiche) {
        if ($aggiornaAnagrafiche) {
            $this->aggiornaAnagrafiche = $aggiornaAnagrafiche;
        } else {
            $this->aggiornaAnagrafiche = "F";
        }
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
    
    function getTipoNumeroDocumento() {
        return $this->TipoNumeroDocumento;
    }

    function setTipoNumeroDocumento($TipoNumeroDocumento) {
        $this->TipoNumeroDocumento = $TipoNumeroDocumento;
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

//        file_put_contents("C:/tmp/param_$operationName.xml", $param);
//        file_put_contents("C:/tmp/request_$operationName.xml", $client->request);
//        file_put_contents("C:/tmp/response_$operationName.xml", $client->response);
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

    public function ws_LeggiProtocollo($param) {
        $AnnoProtocolloSoapval = new soapval('tem:AnnoProtocollo', 'tem:AnnoProtocollo', $param['AnnoProtocollo'], false, false);
        $NumeroProtocolloSoapval = new soapval('tem:NumeroProtocollo', 'tem:NumeroProtocollo', $param['NumeroProtocollo'], false, false);
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', $this->utente, false, false);
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', $this->ruolo, false, false);
        $param = $AnnoProtocolloSoapval->serialize("literal") . $NumeroProtocolloSoapval->serialize("literal") . $UtenteSoapval->serialize() . $RuoloSoapval->serialize();
        return $this->ws_call('LeggiProtocollo', $param);
    }

    public function ws_LeggiAnagrafica($param) {
        $IdSoggettoSoapval = new soapval('tem:IdSoggetto', 'tem:IdSoggetto', $param['IdSoggetto'], false, false);
        $CodiceFiscaleSoapval = new soapval('tem:CodiceFiscale', 'tem:CodiceFiscale', $param['CodiceFiscale'], false, false);
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', $this->utente, false, false);
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', $this->ruolo, false, false);
        $paramLA = $IdSoggettoSoapval->serialize('literal') . $CodiceFiscaleSoapval->serialize('literal') . $UtenteSoapval->serialize('literal') . $RuoloSoapval->serialize('literal');
//        if (isset($param['CodiceAmministrazione'])){
        //$CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $param['CodiceAmministrazione'], false, false);
        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        $paramLA .= $CodiceAmministrazioneSoapval->serialize('literal');
//        }
//        if (isset($param['CodiceAOO'])){
        //$CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $param['CodiceAOO'], false, false);
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        $paramLA .= $CodiceAOOSoapval->serialize('literal');
//        }
        return $this->ws_call('LeggiAnagrafica', $paramLA);
    }

    public function ws_InserisciProtocollo($dati) {
        $paramArr = array();
        $DataSoapval = new soapval('tem:Data', 'tem:Data', $dati['Data'], false, false);
        $paramArr[] = $DataSoapval;
        $ClassificaSoapval = new soapval('tem:Classifica', 'tem:Classifica', $dati['Classifica'], false, false);
        $paramArr[] = $ClassificaSoapval;
        if (!isset($dati['TipoDocumento'])) {
            $dati['TipoDocumento'] = $this->tipoDocumento;
        }
        $TipoDocumentoSoapval = new soapval('tem:TipoDocumento', 'tem:TipoDocumento', $dati['TipoDocumento'], false, false);
        $paramArr[] = $TipoDocumentoSoapval;
        $OggettoSoapval = new soapval('tem:Oggetto', 'tem:Oggetto', $dati['Oggetto'], false, false);
        $paramArr[] = $OggettoSoapval;
        $OrigineSoapval = new soapval('tem:Origine', 'tem:Origine', $dati['Origine'], false, false);
        $paramArr[] = $OrigineSoapval;
        if (isset($dati['MittenteInterno'])) {
            $MittenteInternoSoapval = new soapval('tem:MittenteInterno', 'tem:MittenteInterno', $dati['MittenteInterno'], false, false);
            $paramArr[] = $MittenteInternoSoapval;
        }
        if (is_array($dati['MittentiDestinatari'])) {
            $MDSoapvalArr = array();
            foreach ($dati['MittentiDestinatari'] as $MittDest) {
                $MDSoapvalArr[] = $MittDest->getSoapValRequest();
            }
            $MittDestSoapval = new soapval('tem:MittentiDestinatari', 'tem:MittentiDestinatari', $MDSoapvalArr, false, false);
            $paramArr[] = $MittDestSoapval;
        }
        if (!isset($dati['AggiornaAnagrafiche'])) {
            $dati['AggiornaAnagrafiche'] = $this->aggiornaAnagrafiche;
        }
        $AggiornaAnagraficheSoapval = new soapval('tem:AggiornaAnagrafiche', 'tem:AggiornaAnagrafiche', $dati['AggiornaAnagrafiche'], false, false);
        $paramArr[] = $AggiornaAnagraficheSoapval;

        if (isset($dati['InCaricoA'])) {
            $InCaricoASoapval = new soapval('tem:InCaricoA', 'tem:InCaricoA', $dati['InCaricoA'], false, false);
            $paramArr[] = $InCaricoASoapval;
        }
        if (isset($dati['AnnoPratica'])) {
            $AnnoPraticaSoapval = new soapval('tem:AnnoPratica', 'tem:AnnoPratica', $dati['AnnoPratica'], false, false);
            $paramArr[] = $AnnoPraticaSoapval;
        }
        if (isset($dati['NumeroPratica'])) {
            $NumeroPraticaSoapval = new soapval('tem:NumeroPratica', 'tem:NumeroPratica', $dati['NumeroPratica'], false, false);
            $paramArr[] = $NumeroPraticaSoapval;
        }
        if (isset($dati['DataDocumento'])) {
            $DataDocumentoSoapval = new soapval('tem:DataDocumento', 'tem:DataDocumento', $dati['DataDocumento'], false, false);
            $paramArr[] = $DataDocumentoSoapval;
        }
        if (isset($dati['NumeroDocumento'])) {
            $NumeroDocumentoSoapval = new soapval('tem:NumeroDocumento', 'tem:NumeroDocumento', $dati['NumeroDocumento'], false, false);
            $paramArr[] = $NumeroDocumentoSoapval;
        }
        if (isset($dati['NumeroAllegati'])) {
            $NumeroAllegatiSoapval = new soapval('tem:NumeroAllegati', 'tem:NumeroAllegati', $dati['NumeroAllegati'], false, false);
            $paramArr[] = $NumeroAllegatiSoapval;
        }
        if (isset($dati['DataEvid'])) {
            $DataEvidSoapval = new soapval('tem:DataEvid', 'tem:DataEvid', $dati['DataEvid'], false, false);
            $paramArr[] = $DataEvidSoapval;
        }
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('tem:TipoFile', 'tem:TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:ContentType', 'tem:ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('tem:Image', 'tem:Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Commento', 'tem:Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['Schema'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Schema', 'tem:Schema', $Allegato['Schema'], false, false);
                }
                if (isset($Allegato['NomeAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:NomeAllegato', 'tem:NomeAllegato', $Allegato['NomeAllegato'], false, false);
                }
                if (isset($Allegato['TipoAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:TipoAllegato', 'tem:TipoAllegato', $Allegato['TipoAllegato'], false, false);
                }
//                if (isset($Allegato['IdAllegatoPrincipale'])) {
//                    $AllegatoSoapvalArr[] = new soapval('tem:IdAllegatoPrincipale', 'tem:IdAllegatoPrincipale', $Allegato['IdAllegatoPrincipale'], false, false);
//                }
                $AllegatiSoapvalArr[] = new soapval('tem:Allegato', 'tem:Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('tem:Allegati', 'tem:Allegati', $AllegatiSoapvalArr, false, false);
            $paramArr[] = $AllegatiSoapval;
        }
        $paramArrSoapval = new soapval('tem:ProtoIn', 'tem:ProtoIn', $paramArr, false, false);
        $param = $paramArrSoapval->serialize('literal');
        return $this->ws_call('InserisciProtocolloEAnagrafiche', $param);
    }

    public function ws_InserisciProtocolloString($dati) {
        $paramArr = array();
        $DataSoapval = new soapval('Data', 'Data', $dati['Data'], false, false);
        $paramArr[] = $DataSoapval;
        if (isset($dati['DataProt'])) {
            $DataProtSoapval = new soapval('DataProt', 'DataProt', $dati['DataProt'], false, false);
            $paramArr[] = $DataProtSoapval;
        }

        if (isset($dati['NumProt'])) {
            $NumProtSoapval = new soapval('tem:NumProt', 'tem:NumProt', $dati['NumProt'], false, false);
            $paramArr[] = $NumProtSoapval;
        }

        if (isset($dati['Classifica'])) {
            $ClassificaSoapval = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
            $paramArr[] = $ClassificaSoapval;
        }

        if (!isset($dati['TipoDocumento'])) {
            $dati['TipoDocumento'] = $this->tipoDocumento;
        }
        $TipoDocumentoSoapval = new soapval('TipoDocumento', 'TipoDocumento', $dati['TipoDocumento'], false, false);
        $paramArr[] = $TipoDocumentoSoapval;

        $OggettoSoapval = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
        $paramArr[] = $OggettoSoapval;

        $OggettoBilingueSoapval = new soapval('OggettoBilingue', 'OggettoBilingue', $dati['OggettoBilingue'], false, false);
        $paramArr[] = $OggettoBilingueSoapval;

        $OrigineSoapval = new soapval('Origine', 'Origine', $dati['Origine'], false, false);
        $paramArr[] = $OrigineSoapval;


//        if (isset($dati['MittenteInterno'])) {
        $MittenteInternoSoapval = new soapval('MittenteInterno', 'MittenteInterno', $dati['MittenteInterno'], false, false);
        $paramArr[] = $MittenteInternoSoapval;
//        }

        if (is_array($dati['MittentiDestinatari'])) {
            $MDSoapvalArr = array();
            foreach ($dati['MittentiDestinatari'] as $MittDest) {
                if ($MittDest instanceof itaMittenteDestinatario) {
                    $MDSoapvalArr[] = $MittDest->getSoapValRequest('', true);
                } else {
                    $this->error = "Oggetto MittenteDestinatario non della classe itaMittenteDestinatario";
                    return false;
                }
            }
            $MittDestSoapval = new soapval('MittentiDestinatari', 'MittentiDestinatari', $MDSoapvalArr, false, false);
            $paramArr[] = $MittDestSoapval;
        }

        if (!isset($dati['AggiornaAnagrafiche'])) {
            $dati['AggiornaAnagrafiche'] = $this->aggiornaAnagrafiche;
        }
        $AggiornaAnagraficheSoapval = new soapval('AggiornaAnagrafiche', 'AggiornaAnagrafiche', $dati['AggiornaAnagrafiche'], false, false);
        $paramArr[] = $AggiornaAnagraficheSoapval;

        if (isset($dati['InCaricoA'])) {
            $InCaricoASoapval = new soapval('InCaricoA', 'InCaricoA', $dati['InCaricoA'], false, false);
            $paramArr[] = $InCaricoASoapval;
        }

//        if (isset($dati['AnnoPratica'])) {
        $AnnoPraticaSoapval = new soapval('AnnoPratica', 'AnnoPratica', $dati['AnnoPratica'], false, false);
        $paramArr[] = $AnnoPraticaSoapval;
//        }
        //      if (isset($dati['NumeroPratica'])) {
        $NumeroPraticaSoapval = new soapval('NumeroPratica', 'NumeroPratica', $dati['NumeroPratica'], false, false);
        $paramArr[] = $NumeroPraticaSoapval;
//        }
//        if (isset($dati['DataDocumento'])) {
        $DataDocumentoSoapval = new soapval('DataDocumento', 'DataDocumento', $dati['DataDocumento'], false, false);
        $paramArr[] = $DataDocumentoSoapval;
//        }
//        if (isset($dati['NumeroDocumento'])) {
        $NumeroDocumentoSoapval = new soapval('NumeroDocumento', 'NumeroDocumento', $dati['NumeroDocumento'], false, false);
        $paramArr[] = $NumeroDocumentoSoapval;
//        }

        if (isset($dati['NumeroAllegati'])) {
            $NumeroAllegatiSoapval = new soapval('NumeroAllegati', 'NumeroAllegati', $dati['NumeroAllegati'], false, false);
            $paramArr[] = $NumeroAllegatiSoapval;
        }

//        if (isset($dati['DataEvid'])) {
        $DataEvidSoapval = new soapval('DataEvid', 'DataEvid', $dati['DataEvid'], false, false);
        $paramArr[] = $DataEvidSoapval;
//        }
//        if (isset($dati['OggettoStandard'])) {
        $OggettoStandardSoapval = new soapval('OggettoStandard', 'OggettoStandard', $dati['OggettoStandard'], false, false);
        $paramArr[] = $DataEvidSoapval;
//        }

        $UtenteSoapval = new soapval('Utente', 'Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;

        $RuoloSoapval = new soapval('Ruolo', 'Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;

        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('TipoFile', 'TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('ContentType', 'ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('Image', 'Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('Commento', 'Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['Schema'])) {
                    $AllegatoSoapvalArr[] = new soapval('Schema', 'Schema', $Allegato['Schema'], false, false);
                }
                if (isset($Allegato['NomeAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('NomeAllegato', 'NomeAllegato', $Allegato['NomeAllegato'], false, false);
                }
                if (isset($Allegato['TipoAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('TipoAllegato', 'TipoAllegato', $Allegato['TipoAllegato'], false, false);
                }
                $AllegatiSoapvalArr[] = new soapval('Allegato', 'Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('Allegati', 'Allegati', $AllegatiSoapvalArr, false, false);
            $paramArr[] = $AllegatiSoapval;
        }

        //CREO CDATA

        $ProtocolloInStr = "<tem:ProtocolloInStr><![CDATA[<ProtoIn>";
        foreach ($paramArr as $parametro) {
            $ProtocolloInStr .= $parametro->serialize('literal');
        }
        $ProtocolloInStr .= "</ProtoIn>]]></tem:ProtocolloInStr>";
        $param = $ProtocolloInStr;

        if (isset($dati['CodiceAmministrazione'])) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $dati['CodiceAmministrazione'], false, false);
        }
        if (isset($dati['CodiceAOO'])) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $dati['CodiceAmministrazione'], false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('InserisciProtocolloEAnagraficheString', $param);
    }

    public function ws_InserisciProtocolloStringXML($dati) {
        $paramArr = array();
        //'Data' non utilizzato
        if (isset($dati['DataProt'])) {
            $DataProtSoapval = new soapval('tem:DataProt', 'tem:DataProt', $dati['DataProt'], false, false);
            $paramArr[] = $DataProtSoapval;
        }
        if (isset($dati['NumProt'])) {
            $NumProtSoapval = new soapval('tem:NumProt', 'tem:NumProt', $dati['NumProt'], false, false);
            $paramArr[] = $NumProtSoapval;
        }
        if (isset($dati['Classifica'])) {
            $ClassificaSoapval = new soapval('tem:Classifica', 'tem:Classifica', $dati['Classifica'], false, false);
            $paramArr[] = $ClassificaSoapval;
        }
        $TipoDocumentoSoapval = new soapval('tem:TipoDocumento', 'tem:TipoDocumento', $dati['TipoDocumento'], false, false);
        $paramArr[] = $TipoDocumentoSoapval;
        $OggettoSoapval = new soapval('tem:Oggetto', 'tem:Oggetto', $dati['Oggetto'], false, false);
        $paramArr[] = $OggettoSoapval;
        if (isset($dati['OggettoBilingue'])) {
            $OggettoBilingueSoapval = new soapval('tem:OggettoBilingue', 'tem:OggettoBilingue', $dati['OggettoBilingue'], false, false);
            $paramArr[] = $OggettoBilingueSoapval;
        }
        $OrigineSoapval = new soapval('tem:Origine', 'tem:Origine', $dati['Origine'], false, false);
        $paramArr[] = $OrigineSoapval;
        if (isset($dati['MittenteInterno'])) {
            $MittenteInternoSoapval = new soapval('tem:MittenteInterno', 'tem:MittenteInterno', $dati['MittenteInterno'], false, false);
            $paramArr[] = $MittenteInternoSoapval;
        }
        if (is_array($dati['MittentiDestinatari'])) {
            $MDSoapvalArr = array();
            foreach ($dati['MittentiDestinatari'] as $MittDest) {
                if ($MittDest instanceof itaMittenteDestinatario) {
                    $MDSoapvalArr[] = $MittDest->getSoapValRequest();
                } else {
                    $this->error = "Oggetto MittenteDestinatario non della classe itaMittenteDestinatario";
                    return false;
                }
            }
            $MittDestSoapval = new soapval('tem:MittentiDestinatari', 'tem:MittentiDestinatari', $MDSoapvalArr, false, false);
            $paramArr[] = $MittDestSoapval;
        }
        $AggiornaAnagraficheSoapval = new soapval('tem:AggiornaAnagrafiche', 'tem:AggiornaAnagrafiche', $dati['AggiornaAnagrafiche'], false, false);
        $paramArr[] = $AggiornaAnagraficheSoapval;
        if (isset($dati['InCaricoA'])) {
            $InCaricoASoapval = new soapval('tem:InCaricoA', 'tem:InCaricoA', $dati['InCaricoA'], false, false);
            $paramArr[] = $InCaricoASoapval;
        }
        if (isset($dati['AnnoPratica'])) {
            $AnnoPraticaSoapval = new soapval('tem:AnnoPratica', 'tem:AnnoPratica', $dati['AnnoPratica'], false, false);
            $paramArr[] = $AnnoPraticaSoapval;
        }
        if (isset($dati['NumeroPratica'])) {
            $NumeroPraticaSoapval = new soapval('tem:NumeroPratica', 'tem:NumeroPratica', $dati['NumeroPratica'], false, false);
            $paramArr[] = $NumeroPraticaSoapval;
        }
        if (isset($dati['DataDocumento'])) {
            $DataDocumentoSoapval = new soapval('tem:DataDocumento', 'tem:DataDocumento', $dati['DataDocumento'], false, false);
            $paramArr[] = $DataDocumentoSoapval;
        }
        if (isset($dati['NumeroDocumento'])) {
            $NumeroDocumentoSoapval = new soapval('tem:NumeroDocumento', 'tem:NumeroDocumento', $dati['NumeroDocumento'], false, false);
            $paramArr[] = $NumeroDocumentoSoapval;
        }
        if (isset($dati['NumeroAllegati'])) {
            $NumeroAllegatiSoapval = new soapval('tem:NumeroAllegati', 'tem:NumeroAllegati', $dati['NumeroAllegati'], false, false);
            $paramArr[] = $NumeroAllegatiSoapval;
        }
        if (isset($dati['DataEvid'])) {
            $DataEvidSoapval = new soapval('tem:DataEvid', 'tem:DataEvid', $dati['DataEvid'], false, false);
            $paramArr[] = $DataEvidSoapval;
        }
        if (isset($dati['OggettoStandard'])) {
            $OggettoStandardSoapval = new soapval('tem:OggettoStandard', 'tem:OggettoStandard', $dati['OggettoStandard'], false, false);
            $paramArr[] = $OggettoStandardSoapval;
        }
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('tem:TipoFile', 'tem:TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:ContentType', 'tem:ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('tem:Image', 'tem:Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Commento', 'tem:Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['Schema'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Schema', 'tem:Schema', $Allegato['Schema'], false, false);
                }
                if (isset($Allegato['NomeAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:NomeAllegato', 'tem:NomeAllegato', $Allegato['NomeAllegato'], false, false);
                }
                if (isset($Allegato['TipoAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:TipoAllegato', 'tem:TipoAllegato', $Allegato['TipoAllegato'], false, false);
                }
//                if (isset($Allegato['IdAllegatoPrincipale'])) {
//                    $AllegatoSoapvalArr[] = new soapval('tem:IdAllegatoPrincipale', 'tem:IdAllegatoPrincipale', $Allegato['IdAllegatoPrincipale'], false, false);
//                }
                $AllegatiSoapvalArr[] = new soapval('tem:Allegato', 'tem:Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('tem:Allegati', 'tem:Allegati', $AllegatiSoapvalArr, false, false);
            $paramArr[] = $AllegatiSoapval;
        }
        //CREO CDATA
        $ProtocolloInStr = "<tem:ProtocolloInStr><![CDATA[<?xml version='1.0' encoding='UTF-8'?><tem:ProtoIn>";
        foreach ($paramArr as $parametro) {
            $ProtocolloInStr .= $parametro->serialize('literal');
        }
        $ProtocolloInStr .= "</ProtoIn>]]></tem:ProtocolloInStr>";
        $param = $ProtocolloInStr;

        if (isset($dati['CodiceAmministrazione'])) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $dati['CodiceAmministrazione'], false, false);
        }
        if (isset($dati['CodiceAOO'])) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $dati['CodiceAmministrazione'], false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('InserisciProtocolloEAnagraficheString', $param);
    }

    public function ws_AggiungiAllegati($dati) {
        $paramArr = array();
        $idDocSoapval = new soapval('tem:idDoc', 'tem:idDoc', $dati['idDoc'], false, false);
        $paramArr[] = $idDocSoapval;
        $annoProtSoapval = new soapval('tem:annoProt', 'tem:annoProt', $dati['annoProt'], false, false);
        $paramArr[] = $annoProtSoapval;
        $numProtSoapval = new soapval('tem:numProt', 'tem:numProt', $dati['numProt'], false, false);
        $paramArr[] = $numProtSoapval;
        $utenteSoapval = new soapval('tem:utente', 'tem:utente', $this->utente, false, false);
        $paramArr[] = $utenteSoapval;
        $ruoloSoapval = new soapval('tem:ruolo', 'tem:ruolo', $this->ruolo, false, false);
        $paramArr[] = $ruoloSoapval;
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('tem:TipoFile', 'tem:TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:ContentType', 'tem:ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('tem:Image', 'tem:Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Commento', 'tem:Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['IdAllegatoPrincipale'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:IdAllegatoPrincipale', 'tem:IdAllegatoPrincipale', $Allegato['IdAllegatoPrincipale'], false, false);
                }
                $AllegatiSoapvalArr[] = new soapval('tem:Allegato', 'tem:Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('tem:Allegati', 'tem:Allegati', $AllegatiSoapvalArr, false, false);
            //$paramArr .= $AllegatiSoapval->serialize('literal');
            $paramArr[] = $AllegatiSoapval;
        }
        $paramArrSoapval = new soapval('tem:NuoviAllegati', 'tem:NuoviAllegati', $paramArr, false, false);
        $param = $paramArrSoapval->serialize('literal');

        $NuoviAllegatiStr = "<tem:NuoviAllegatiStr><![CDATA[<?xml version='1.0' encoding='UTF-8'?>$param]]></tem:NuoviAllegatiStr>";
        //return $this->ws_call('AggiungiAllegati', $param);
        return $this->ws_call('AggiungiAllegatiString', $NuoviAllegatiStr);
    }

    public function ws_AggiungiAllegatiString($dati) {
        $paramArr = array();
        $idDocSoapval = new soapval('idDoc', 'idDoc', $dati['idDoc'], false, false);
        $paramArr[] = $idDocSoapval;
        if ($dati['annoProt']) {
            $annoProtSoapval = new soapval('annoProt', 'annoProt', $dati['annoProt'], false, false);
            $paramArr[] = $annoProtSoapval;
        }
        if ($dati['numProt']) {
            $numProtSoapval = new soapval('numProt', 'numProt', $dati['numProt'], false, false);
            $paramArr[] = $numProtSoapval;
        }
        $utenteSoapval = new soapval('utente', 'utente', $this->utente, false, false);
        $paramArr[] = $utenteSoapval;
        $ruoloSoapval = new soapval('ruolo', 'ruolo', $this->ruolo, false, false);
        $paramArr[] = $ruoloSoapval;
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('TipoFile', 'TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('ContentType', 'ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('Image', 'Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('Commento', 'Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['IdAllegatoPrincipale'])) {
                    $AllegatoSoapvalArr[] = new soapval('IdAllegatoPrincipale', 'IdAllegatoPrincipale', $Allegato['IdAllegatoPrincipale'], false, false);
                }
                if (isset($Allegato['Principale'])) {
                    $AllegatoSoapvalArr[] = new soapval('Principale', 'Principale', $Allegato['Principale'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('NomeAllegato', 'NomeAllegato', $Allegato['NomeAllegato'], false, false); //Aggiunto NomeAllegato perche ci metteva untitled
                $AllegatiSoapvalArr[] = new soapval('Allegato', 'Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('Allegati', 'Allegati', $AllegatiSoapvalArr, false, false);
            //$paramArr .= $AllegatiSoapval->serialize('literal');
            $paramArr[] = $AllegatiSoapval;
        }
        $paramArrSoapval = new soapval('NuoviAllegati', 'NuoviAllegati', $paramArr, false, false);
        $param = $paramArrSoapval->serialize('literal');
        $NuoviAllegatiStr = "<tem:NuoviAllegatiStr><![CDATA[<?xml version='1.0' encoding='UTF-8'?>$param]]></tem:NuoviAllegatiStr>";
        return $this->ws_call('AggiungiAllegatiString', $NuoviAllegatiStr);
    }

    public function ws_AggiungiAllegati2($dati) {
        $paramArr = array();
        $idDocSoapval = new soapval('tem:idDoc', 'tem:idDoc', $dati['idDoc'], false, false);
        $paramArr[] = $idDocSoapval;
        $annoProtSoapval = new soapval('tem:annoProt', 'tem:annoProt', $dati['annoProt'], false, false);
        $paramArr[] = $annoProtSoapval;
        $numProtSoapval = new soapval('tem:numProt', 'tem:numProt', $dati['numProt'], false, false);
        $paramArr[] = $numProtSoapval;
        $utenteSoapval = new soapval('tem:utente', 'tem:utente', $this->utente, false, false);
        $paramArr[] = $utenteSoapval;
        $ruoloSoapval = new soapval('tem:ruolo', 'tem:ruolo', $this->ruolo, false, false);
        $paramArr[] = $ruoloSoapval;
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('tem:TipoFile', 'tem:TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:ContentType', 'tem:ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('tem:Image', 'tem:Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:Commento', 'tem:Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['IdAllegatoPrincipale'])) {
                    $AllegatoSoapvalArr[] = new soapval('tem:IdAllegatoPrincipale', 'tem:IdAllegatoPrincipale', $Allegato['IdAllegatoPrincipale'], false, false);
                }
//                if (isset($Allegato['Schema'])) {
//                    $AllegatoSoapvalArr[] = new soapval('tem:Schema', 'tem:Schema', $Allegato['Schema'], false, false);
//                }
//                if (isset($Allegato['NomeAllegato'])) {
//                    $AllegatoSoapvalArr[] = new soapval('tem:NomeAllegato', 'tem:NomeAllegato', $Allegato['NomeAllegato'], false, false);
//                }
//                if (isset($Allegato['TipoAllegato'])) {
//                    $AllegatoSoapvalArr[] = new soapval('tem:TipoAllegato', 'tem:TipoAllegato', $Allegato['TipoAllegato'], false, false);
//                }
                $AllegatiSoapvalArr[] = new soapval('tem:Allegato', 'tem:Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('tem:Allegati', 'tem:Allegati', $AllegatiSoapvalArr, false, false);
            //$paramArr .= $AllegatiSoapval->serialize('literal');
            $paramArr[] = $AllegatiSoapval;
        }
        $paramArrSoapval = new soapval('tem:NuoviAllegati', 'tem:NuoviAllegati', $paramArr, false, false);
        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        $param = $paramArrSoapval->serialize('literal');
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize();
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize();
        }
        return $this->ws_call('AggiungiAllegati2', $param);
    }

    /*
     * FUNZIONI WS TABELLE
     */

    public function wm_tipiDocumento($filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return $this->ws_call('wm_tipiDocumento', $param, 'sch:');
    }

    public function wm_struttura($filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return $this->ws_call('wm_struttura', $param, 'sch:');
    }

    public function wm_classifiche($filtro = "") {
        $param = "";
        //if ($filtro != "") {
        $filtroSoapval = new soapval('sch:filtro', 'sch:filtro', $filtro, false, false);
        $param = $filtroSoapval->serialize('literal');
        //}
        return $this->ws_call('wm_classifiche', $param, 'sch:');
    }

    public function ws_InserisciDocumentoEAnagraficheString($dati) {
        $paramArr = array();
        $DataSoapval = new soapval('Data', 'Data', $dati['Data'], false, false);
        $paramArr[] = $DataSoapval;
        if (isset($dati['DataProt'])) {
            $DataProtSoapval = new soapval('DataProt', 'DataProt', $dati['DataProt'], false, false);
            $paramArr[] = $DataProtSoapval;
        }

        if (isset($dati['NumProt'])) {
            $NumProtSoapval = new soapval('NumProt', 'NumProt', $dati['NumProt'], false, false);
            $paramArr[] = $NumProtSoapval;
        }

        if (isset($dati['Classifica'])) {
            $ClassificaSoapval = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
            $paramArr[] = $ClassificaSoapval;
        }

        if (!isset($dati['TipoDocumento'])) {
            $dati['TipoDocumento'] = $this->tipoDocumento;
        }
        $TipoDocumentoSoapval = new soapval('TipoDocumento', 'TipoDocumento', $dati['TipoDocumento'], false, false);
        $paramArr[] = $TipoDocumentoSoapval;

        $OggettoSoapval = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
        $paramArr[] = $OggettoSoapval;

        $OggettoBilingueSoapval = new soapval('OggettoBilingue', 'OggettoBilingue', $dati['OggettoBilingue'], false, false);
        $paramArr[] = $OggettoBilingueSoapval;

        $OrigineSoapval = new soapval('Origine', 'Origine', $dati['Origine'], false, false);
        $paramArr[] = $OrigineSoapval;


        $MittenteInternoSoapval = new soapval('MittenteInterno', 'MittenteInterno', $dati['MittenteInterno'], false, false);
        $paramArr[] = $MittenteInternoSoapval;

        if (is_array($dati['MittentiDestinatari'])) {
            $MDSoapvalArr = array();
            foreach ($dati['MittentiDestinatari'] as $MittDest) {
                if ($MittDest instanceof itaMittenteDestinatario) {
                    $MDSoapvalArr[] = $MittDest->getSoapValRequest('', true);
                } else {
                    $this->error = "Oggetto MittenteDestinatario non della classe itaMittenteDestinatario";
                    return false;
                }
            }
            $MittDestSoapval = new soapval('MittentiDestinatari', 'MittentiDestinatari', $MDSoapvalArr, false, false);
            $paramArr[] = $MittDestSoapval;
        }

        if (!isset($dati['AggiornaAnagrafiche'])) {
            $dati['AggiornaAnagrafiche'] = $this->aggiornaAnagrafiche;
        }
        $AggiornaAnagraficheSoapval = new soapval('AggiornaAnagrafiche', 'AggiornaAnagrafiche', $dati['AggiornaAnagrafiche'], false, false);
        $paramArr[] = $AggiornaAnagraficheSoapval;

        if (isset($dati['InCaricoA'])) {
            $InCaricoASoapval = new soapval('InCaricoA', 'InCaricoA', $dati['InCaricoA'], false, false);
            $paramArr[] = $InCaricoASoapval;
        }

//        if (isset($dati['AnnoPratica'])) {
        $AnnoPraticaSoapval = new soapval('AnnoPratica', 'AnnoPratica', $dati['AnnoPratica'], false, false);
        $paramArr[] = $AnnoPraticaSoapval;
//        }
        //      if (isset($dati['NumeroPratica'])) {
        $NumeroPraticaSoapval = new soapval('NumeroPratica', 'NumeroPratica', $dati['NumeroPratica'], false, false);
        $paramArr[] = $NumeroPraticaSoapval;
//        }
//        if (isset($dati['DataDocumento'])) {
        $DataDocumentoSoapval = new soapval('DataDocumento', 'DataDocumento', $dati['DataDocumento'], false, false);
        $paramArr[] = $DataDocumentoSoapval;
//        }
//        if (isset($dati['NumeroDocumento'])) {
        $NumeroDocumentoSoapval = new soapval('NumeroDocumento', 'NumeroDocumento', $dati['NumeroDocumento'], false, false);
        $paramArr[] = $NumeroDocumentoSoapval;
//        }

        if (isset($dati['NumeroAllegati'])) {
            $NumeroAllegatiSoapval = new soapval('NumeroAllegati', 'NumeroAllegati', $dati['NumeroAllegati'], false, false);
            $paramArr[] = $NumeroAllegatiSoapval;
        }

//        if (isset($dati['DataEvid'])) {
        $DataEvidSoapval = new soapval('DataEvid', 'DataEvid', $dati['DataEvid'], false, false);
        $paramArr[] = $DataEvidSoapval;
//        }
//        if (isset($dati['OggettoStandard'])) {
        $OggettoStandardSoapval = new soapval('OggettoStandard', 'OggettoStandard', $dati['OggettoStandard'], false, false);
        $paramArr[] = $DataEvidSoapval;
//        }

        $UtenteSoapval = new soapval('Utente', 'Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;

        $RuoloSoapval = new soapval('Ruolo', 'Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;

        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            foreach ($dati['Allegati'] as $Allegato) {
                $AllegatoSoapvalArr = array();
                $AllegatoSoapvalArr[] = new soapval('TipoFile', 'TipoFile', $Allegato['TipoFile'], false, false);
                if (isset($Allegato['ContentType'])) {
                    $AllegatoSoapvalArr[] = new soapval('ContentType', 'ContentType', $Allegato['ContentType'], false, false);
                }
                $AllegatoSoapvalArr[] = new soapval('Image', 'Image', $Allegato['Image'], false, false);
                if (isset($Allegato['Commento'])) {
                    $AllegatoSoapvalArr[] = new soapval('Commento', 'Commento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['Schema'])) {
                    $AllegatoSoapvalArr[] = new soapval('Schema', 'Schema', $Allegato['Schema'], false, false);
                }
                if (isset($Allegato['NomeAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('NomeAllegato', 'NomeAllegato', $Allegato['NomeAllegato'], false, false);
                }
                if (isset($Allegato['TipoAllegato'])) {
                    $AllegatoSoapvalArr[] = new soapval('TipoAllegato', 'TipoAllegato', $Allegato['TipoAllegato'], false, false);
                }
                $AllegatiSoapvalArr[] = new soapval('Allegato', 'Allegato', $AllegatoSoapvalArr, false, false);
            }
            $AllegatiSoapval = new soapval('Allegati', 'Allegati', $AllegatiSoapvalArr, false, false);
            $paramArr[] = $AllegatiSoapval;
        }

        //CREO CDATA
        $ProtocolloInStr = "<tem:ProtocolloInStr><![CDATA[<ProtoIn>";
        foreach ($paramArr as $parametro) {
            $ProtocolloInStr .= $parametro->serialize('literal');
        }
        $ProtocolloInStr .= "</ProtoIn>]]></tem:ProtocolloInStr>";
        $param = $ProtocolloInStr;

        if (isset($this->CodiceAmministrazione)) {
            $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        }
        if (isset($this->CodiceAOO)) {
            $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);
        }
        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('InserisciDocumentoEAnagraficheString', $param);
    }

    public function ws_CreaCopieString($dati) {
        $paramArr = array();

        $idDocSoapval = new soapval('IdDocumento', 'IdDocumento', $dati['IdDocumento'], false, false);
        $paramArr[] = $idDocSoapval;

        $annoProtSoapval = new soapval('AnnoProtocollo', 'AnnoProtocollo', $dati['AnnoProtocollo'], false, false);
        $paramArr[] = $annoProtSoapval;


        $numProtSoapval = new soapval('NumeroProtocollo', 'NumeroProtocollo', $dati['NumeroProtocollo'], false, false);
        $paramArr[] = $numProtSoapval;

        if (isset($dati['FascicolaConOriginale'])) {
            $fascicolaSoapval = new soapval('FascicolaConOriginale', 'FascicolaConOriginale', $dati['FascicolaConOriginale'], false, false);
            $paramArr[] = $fascicolaSoapval;
        }

        $UODestinatarieSoapvalArr = array();
        foreach ($dati['UODestinatarie'] as $UODest) {
            $UODestSoapvalArr = array();
            $UODestSoapvalArr[] = new soapval('Carico', 'Carico', $UODest['Carico'], false, false);
            if (isset($UODest['TipoUO'])) {
                $UODestSoapvalArr[] = new soapval('TipoUO', 'TipoUO', $UODest['TipoUO'], false, false);
            }
            if (isset($UODest['Data'])) {
                $UODestSoapvalArr[] = new soapval('Data', 'Data', $UODest['Data'], false, false);
            }
            if (isset($UODest['NumeroCopie'])) {
                $UODestSoapvalArr[] = new soapval('NumeroCopie', 'NumeroCopie', $UODest['NumeroCopie'], false, false);
            }
            if (isset($UODest['TipoAssegnazione'])) {
                $UODestSoapvalArr[] = new soapval('TipoAssegnazione', 'TipoAssegnazione', $UODest['TipoAssegnazione'], false, false);
            }
            $UODestinatarieSoapvalArr[] = new soapval('UODestinataria', 'UODestinataria', $UODestSoapvalArr, false, false);
        }
        $UODestinatarieSoapval = new soapval('UODestinatarie', 'UODestinatarie', $UODestinatarieSoapvalArr, false, false);
        $paramArr[] = $UODestinatarieSoapval;

        $UtenteSoapval = new soapval('Utente', 'Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;

        $RuoloSoapval = new soapval('Ruolo', 'Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;


        //CREO CDATA
        $CreaCopieInStr = "<tem:CreaCopieInStr><![CDATA[<CreaCopieIn>";
        foreach ($paramArr as $parametro) {
            $CreaCopieInStr .= $parametro->serialize('literal');
        }
        $CreaCopieInStr .= "</CreaCopieIn>]]></tem:CreaCopieInStr>";
        $param = $CreaCopieInStr;

        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);

        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }
        return $this->ws_call('CreaCopieString', $param);
    }

    public function ws_LeggiDocumentoString($param) {
        $idDocSoapval = new soapval('tem:IdDocumento', 'tem:IdDocumento', $param['IdDocumento'], false, false);
        $UtenteSoapval = new soapval('tem:Utente', 'tem:Utente', $this->utente, false, false);
        $RuoloSoapval = new soapval('tem:Ruolo', 'tem:Ruolo', $this->ruolo, false, false);
        $param = $idDocSoapval->serialize("literal") . $UtenteSoapval->serialize() . $RuoloSoapval->serialize();
        $CodiceAmministrazioneSoapval = new soapval('tem:CodiceAmministrazione', 'tem:CodiceAmministrazione', $this->CodiceAmministrazione, false, false);
        $CodiceAOOSoapval = new soapval('tem:CodiceAOO', 'tem:CodiceAOO', $this->CodiceAOO, false, false);

        if ($CodiceAmministrazioneSoapval) {
            $param .= $CodiceAmministrazioneSoapval->serialize('literal');
        }
        if ($CodiceAOOSoapval) {
            $param .= $CodiceAOOSoapval->serialize('literal');
        }

        return $this->ws_call('LeggiDocumentoString', $param);
    }

}

?>
