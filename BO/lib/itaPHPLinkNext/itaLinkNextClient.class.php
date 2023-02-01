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
//require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/itaPHPLinkNext/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/itaPHPLinkNext/itaPosizioneDebitoria.class.php');
require_once(ITA_LIB_PATH . '/itaPHPLinkNext/itaPosizioneDebitoriaCw.class.php');
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLibPagoPaUtils.php';

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaLinkNextClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $namespacePrefix = "ent";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $username = "";
    private $password = "";
    private $utente = "";
    private $idConnettore = "";
    private $cfEnte = "";
    private $idAccesso = "";
    private $token = "";
    private $timeout = 2400;
    private $SOAPHeader;
    private $result;
    private $error;
    private $fault;
    private $GestionePDFACaricoDelFornitore;
    private $FileAcquisizionePDF;

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
//        if ($tipo == 'foh') {
//            $nameSpaces = array("tem" => "http://tempuri.org/");
//        }
//        if ($tipo == 'sch') {
//            $nameSpaces = array("sch" => "http://wwwpa2k/Ulisse/iride/web_services/ws_tabelle/schema");
//        }
        $this->nameSpaces = $nameSpaces;
    }

    public function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWebservices_wsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setUsername($username) {
        $this->username = trim($username);
    }

    public function setPassword($password) {
        $this->password = trim($password);
    }

    public function getIdConnettore() {
        return $this->idConnettore;
    }

    public function getCfEnte() {
        return $this->cfEnte;
    }

    public function getIdAccesso() {
        return $this->idAccesso;
    }

    public function getToken() {
        return $this->token;
    }

    public function setIdConnettore($idConnettore) {
        $this->idConnettore = trim($idConnettore);
    }

    public function setCFEnte($cfEnte) {
        $this->cfEnte = trim($cfEnte);
    }

    public function setIdAccesso($idAccesso) {
        $this->idAccesso = trim($idAccesso);
    }

    public function setToken($token) {
        $this->token = trim($token);
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getWebservices_uri() {
        return $this->webservices_uri;
    }

    public function getWebservices_wsdl() {
        return $this->webservices_wsdl;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getTimeout() {
        return $this->timeout;
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

    public function getNamespacePrefix() {
        return $this->namespacePrefix;
    }

    public function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    public function getGestionePDFACaricoDelFornitore() {
        return $this->GestionePDFACaricoDelFornitore;
    }

    public function setGestionePDFACaricoDelFornitore($GestionePDFACaricoDelFornitore) {
        $this->GestionePDFACaricoDelFornitore = $GestionePDFACaricoDelFornitore;
    }

    public function getFileAcquisizionePDF() {
        return $this->FileAcquisizionePDF;
    }

    public function setFileAcquisizionePDF($FileAcquisizionePDF) {
        $this->FileAcquisizionePDF = $FileAcquisizionePDF;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function getHeaders() {
        $header = "<foh:IntestazioneFO>
         <TokenAuth>" . $this->getToken() . "</TokenAuth>
         <IdentificativoConnettore>" . $this->getIdConnettore() . "</IdentificativoConnettore>
         <CodiceFiscaleEnte>" . $this->getCfEnte() . "</CodiceFiscaleEnte>
      </foh:IntestazioneFO>";
        return $header;
    }

    private function ws_call($operationName, $param, $soapAction) {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setHeaders($this->getHeaders());
        $client->soap_defencoding = 'UTF-8';
//        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
        //$result = $client->call($operationName, $param, $nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents('/users/tmp/LNparam_' . $soapAction . '.xml', $param);
//        file_put_contents('/users/tmp/LNrequest_' . $soapAction . '.xml', $client->request);
//        file_put_contents('/users/tmp/LNresponse_' . $soapAction . '.xml', $client->response);
        file_put_contents('C:/tmp/LNparam_' . $soapAction . date('YmdHis') . '.xml', $param);
        file_put_contents('C:/tmp/LNrequest_' . $soapAction . date('YmdHis') . '.xml', $client->request);
        file_put_contents('C:/tmp/LNresponse_' . $soapAction . date('YmdHis') . '.xml', $client->response);
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

    public function ws_Login($param) {
        $Versione = new soapval('Versione', 'Versione', "", false, false);
        $IdentificativoSoapval = new soapval('Identificativo', 'Identificativo', $this->getIdAccesso(), false, false);
        $UtenteSoapval = new soapval('Username', 'Username', $this->getUsername(), false, false);
        $PasswordSoapval = new soapval('PasswordMD5', 'PasswordMD5', $this->getPassword(), false, false);
        $paramL = $IdentificativoSoapval->serialize() . $UtenteSoapval->serialize() . $PasswordSoapval->serialize();
        return $this->ws_call("ent:LoginRequest", $paramL, "Login");
    }

    public function ws_InserisciPosizione($PosizioneDebitoria) {
        $RequestString = $PosizioneDebitoria->getRichiesta($this->namespacePrefix);
        return $this->ws_call("ent:InserisciPosizioneRequest", "<ent:PosizioniDebitoria>" . $RequestString . "</ent:PosizioniDebitoria>", "InserisciPosizione");
    }

    public function ws_InserisciRuoloPosizioni($param) {
        $RequestString = "";

        foreach ($param['PosizioniDebitorie'] as $k => $PosizioneDebitoria) {
            App::log('giro ' . $k);
            if ($PosizioneDebitoria instanceof itaPosizioneDebitoria) {
                $RequestPDString = $PosizioneDebitoria->getRichiesta($this->namespacePrefix);
            }
            $RequestString .= "<ent:PosizioniDebitorie>" . $RequestPDString . "</ent:PosizioniDebitorie>";
        }
        if ($param['RuoloPosizioniDebitorie']) {
            $RequestRuoloString = "";
            $RuoloPosizioniDebitorie = $param['RuoloPosizioniDebitorie'];
            if ($RuoloPosizioniDebitorie['Descrizione']) {
                $DescrizioneSoapVal = new soapval('ent:Descrizione', 'ent:Descrizione', $RuoloPosizioniDebitorie['Descrizione'], false, false);
                $RequestRuoloString .= $DescrizioneSoapVal->serialize('literal');
            }
            if ($RuoloPosizioniDebitorie['AnnoImposta']) {
                $AnnoImpostaSoapVal = new soapval('ent:AnnoImposta', 'ent:AnnoImposta', $RuoloPosizioniDebitorie['AnnoImposta'], false, false);
                $RequestRuoloString .= $AnnoImpostaSoapVal->serialize('literal');
            }
            if ($RuoloPosizioniDebitorie['DataInizioPeriodo']) {
                $DataInizioPeriodoSoapVal = new soapval('ent:DataInizioPeriodo', 'ent:DataInizioPeriodo', $RuoloPosizioniDebitorie['DataInizioPeriodo'], false, false);
                $RequestRuoloString .= $DataInizioPeriodoSoapVal->serialize('literal');
            }
            if ($RuoloPosizioniDebitorie['DataFinePeriodo']) {
                $DataFinePeriodoSoapVal = new soapval('ent:DataFinePeriodo', 'ent:DataFinePeriodo', $RuoloPosizioniDebitorie['DataFinePeriodo'], false, false);
                $RequestRuoloString .= $DataFinePeriodoSoapVal->serialize('literal');
            }
            if ($RuoloPosizioniDebitorie['Note']) {
                $NoteSoapVal = new soapval('ent:Note', 'ent:Note', $RuoloPosizioniDebitorie['Note'], false, false);
                $RequestRuoloString .= $NoteSoapVal->serialize('literal');
            }
            if ($RuoloPosizioniDebitorie['TipoDocumento']) {
                $TipoDocumentoSoapVal = new soapval('ent:TipoDocumento', 'ent:TipoDocumento', $RuoloPosizioniDebitorie['TipoDocumento'], false, false);
                $RequestRuoloString .= $TipoDocumentoSoapVal->serialize('literal');
            }

            $GestionePDFACaricoDelFornitoreSoapVal = new soapval('ent:GestionePDFACaricoDelFornitore', 'ent:GestionePDFACaricoDelFornitore', $this->GestionePDFACaricoDelFornitore, false, false);
            $RequestRuoloString .= $GestionePDFACaricoDelFornitoreSoapVal->serialize('literal');
            if ($this->GestionePDFACaricoDelFornitore) {
                $FileAcquisizionePDFSoapVal = new soapval('ent:FileAcquisizionePDF', 'ent:FileAcquisizionePDF', $this->FileAcquisizionePDF, false, false);
                $RequestRuoloString .= $FileAcquisizionePDFSoapVal->serialize('literal');
            }

            $RequestString .= "<ent:RuoloPosizioniDebitorie>" . $RequestRuoloString . "</ent:RuoloPosizioniDebitorie>";
        }
        return $this->ws_call("ent:InserisciRuoloPosizioniRequest", $RequestString, "InserisciRuoloPosizioni");
    }

    public function ws_InserisciRuoloPosizioniCw($param, $forceDisableNull = false) {
        $RequestString = "";

        foreach ($param['PosizioniDebitorie'] as $k => $PosizioneDebitoria) {
            App::log('giro ' . $k);
            if ($PosizioneDebitoria instanceof itaPosizioneDebitoriaCw) {
                $PosizioneDebitoria->forceDisableEmptyFields($forceDisableNull);
                $RequestPDString = $PosizioneDebitoria->getRichiesta($this->namespacePrefix);
            }
            $RequestString .= "<ent:PosizioniDebitorie>" . $RequestPDString . "</ent:PosizioniDebitorie>";
        }
        if ($param['RuoloPosizioniDebitorie']) {
            $RequestRuoloString = "";
            $RuoloPosizioniDebitorie = $param['RuoloPosizioniDebitorie'];

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['Descrizione']) || !$forceDisableNull) {
                $descrizione =  "<![CDATA[" . $RuoloPosizioniDebitorie['Descrizione'] . "]]>";
                $DescrizioneSoapVal = new soapval('ent:Descrizione', 'ent:Descrizione', $descrizione, false, false);
                $RequestRuoloString .= $DescrizioneSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['AnnoImposta']) || !$forceDisableNull) {
                $AnnoImpostaSoapVal = new soapval('ent:AnnoImposta', 'ent:AnnoImposta', $RuoloPosizioniDebitorie['AnnoImposta'], false, false);
                $RequestRuoloString .= $AnnoImpostaSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['DataInizioPeriodo']) || !$forceDisableNull) {
                $DataInizioPeriodoSoapVal = new soapval('ent:DataInizioPeriodo', 'ent:DataInizioPeriodo', $RuoloPosizioniDebitorie['DataInizioPeriodo'], false, false);
                $RequestRuoloString .= $DataInizioPeriodoSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['DataFinePeriodo']) || !$forceDisableNull) {
                $DataFinePeriodoSoapVal = new soapval('ent:DataFinePeriodo', 'ent:DataFinePeriodo', $RuoloPosizioniDebitorie['DataFinePeriodo'], false, false);
                $RequestRuoloString .= $DataFinePeriodoSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['Note'])) {
                $NoteSoapVal = new soapval('ent:Note', 'ent:Note', $RuoloPosizioniDebitorie['Note'], false, false);
                $RequestRuoloString .= $NoteSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['TipoDocumento']) || !$forceDisableNull) {
                $TipoDocumentoSoapVal = new soapval('ent:TipoDocumento', 'ent:TipoDocumento', $RuoloPosizioniDebitorie['TipoDocumento'], false, false);
                $RequestRuoloString .= $TipoDocumentoSoapVal->serialize('literal');
            }

            if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['Ruolo_CausaliImporti'])) {
                $RequestRuoloString .= "<ent:Ruolo_CausaliImporti>";
                foreach ($RuoloPosizioniDebitorie['Ruolo_CausaliImporti']['Ruolo_CausaleImporto'] as $CausaleImporto) {
                    $TipoDocumentoSoapVal = new soapval('ent:Ruolo_CausaleImporto', 'ent:Ruolo_CausaleImporto', $CausaleImporto, false, false);
                    $RequestRuoloString .= $TipoDocumentoSoapVal->serialize('literal');
                }
                $RequestRuoloString .= "</ent:Ruolo_CausaliImporti>";
            }


            if (isSet($RuoloPosizioniDebitorie['GestionePDFACaricoDelFornitore'])) {
                $gestionePDFACaricoDelFornitore = $RuoloPosizioniDebitorie['GestionePDFACaricoDelFornitore'];
                if (cwbLibPagoPaUtils::valorized($RuoloPosizioniDebitorie['FileAcquisizionePDF'])) {
                    $fileAcquisizionePDF = $RuoloPosizioniDebitorie['FileAcquisizionePDF'];
                }
            } else {
                $gestionePDFACaricoDelFornitore = $this->GestionePDFACaricoDelFornitore;
                $fileAcquisizionePDF = $this->FileAcquisizionePDF;
            }
            $GestionePDFACaricoDelFornitoreSoapVal = new soapval('ent:GestionePDFACaricoDelFornitore', 'ent:GestionePDFACaricoDelFornitore', $gestionePDFACaricoDelFornitore, false, false);
            $RequestRuoloString .= $GestionePDFACaricoDelFornitoreSoapVal->serialize('literal');

            if ($gestionePDFACaricoDelFornitore) {
                $FileAcquisizionePDFSoapVal = new soapval('ent:FileAcquisizionePDF', 'ent:FileAcquisizionePDF', $fileAcquisizionePDF, false, false);
                $RequestRuoloString .= $FileAcquisizionePDFSoapVal->serialize('literal');
            }

            $RequestString .= "<ent:RuoloPosizioniDebitorie>" . $RequestRuoloString . "</ent:RuoloPosizioniDebitorie>";
        }

        if (cwbLibPagoPaUtils::valorized($param['ID_RUOLO']) || !$forceDisableNull) {
            $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $param['ID_RUOLO'], false, false);
            $RequestString .= $ID_RUOLOSoapVal->serialize('literal');
        }

        if (cwbLibPagoPaUtils::valorized($param['RiferimentoRuoloEsterno']) || !$forceDisableNull) {
            $RiferimentoRuoloEsternoSoapVal = new soapval('ent:RiferimentoRuoloEsterno', 'ent:RiferimentoRuoloEsterno', $param['RiferimentoRuoloEsterno'], false, false);
            $RequestString .= $RiferimentoRuoloEsternoSoapVal->serialize('literal');
        }
        //Out::msgInfo("test",htmlentities($RequestString));
        return $this->ws_call("ent:InserisciRuoloPosizioniRequest", $RequestString, "InserisciRuoloPosizioni");
    }

    public function ws_VerificaPosizione($param) {
        if (!isset($param['TipoChiaveApplicativa']) || $param['TipoChiaveApplicativa'] == '') {
            $param['TipoChiaveApplicativa'] = 'IUV';
        }
        $TipoChiaveApplicativaSoapVal = new soapval('ent:TipoChiaveApplicativa', 'ent:TipoChiaveApplicativa', $param['TipoChiaveApplicativa'], false, false);
        $RequestString .= $TipoChiaveApplicativaSoapVal->serialize('literal');
        $ChiaveApplicativaSoapVal = new soapval('ent:ChiaveApplicativa', 'ent:ChiaveApplicativa', $param['ChiaveApplicativa'], false, false);
        $RequestString .= $ChiaveApplicativaSoapVal->serialize('literal');

        return $this->ws_call("ent:VerificaPosizioneRequest", $RequestString, "VerificaPosizione");
    }

    public function ws_AnnullaPosizioneDebitoria($param) {
        if (!isset($param['TipoChiaveApplicativa']) || $param['TipoChiaveApplicativa'] == '') {
            $param['TipoChiaveApplicativa'] = 'IUV';
        }
        $TipoChiaveApplicativaSoapVal = new soapval('ent:TipoChiaveApplicativa', 'ent:TipoChiaveApplicativa', $param['TipoChiaveApplicativa'], false, false);
        $RequestString .= $TipoChiaveApplicativaSoapVal->serialize('literal');
        $ChiaveApplicativaSoapVal = new soapval('ent:ChiaveApplicativa', 'ent:ChiaveApplicativa', $param['ChiaveApplicativa'], false, false);
        $RequestString .= $ChiaveApplicativaSoapVal->serialize('literal');

        return $this->ws_call("ent:AnnullaPosizioneDebitoriaRequest", $RequestString, "AnnullaPosizioneDebitoria");
    }

    public function ws_AnnullaRuoloPosizioni($param) {
        $idRuolo = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $param['IdRuolo'], false, false);
        $RequestString .= $idRuolo->serialize('literal');

        return $this->ws_call("ent:AnnullaRuoloPosizioniRequest", $RequestString, "AnnullaRuoloPosizioni");
    }

    public function ws_ScaricaPagamentoRT($param) {
        // todo messo IUV fisso.. nel caso cambiare
        $tipoChiaveSoapVal = new soapval('ent:ChiaveApplicativa', 'ent:ChiaveApplicativa', "IUV", false, false);
        $ChiaveSoapVal = new soapval('ent:ChiaveApplicativa', 'ent:ChiaveApplicativa', $param['CodiceIdentificativo'], false, false);
        $RequestString .= $ChiaveSoapVal->serialize('literal');
        $RequestString .= $tipoChiaveSoapVal->serialize('literal');

        return $this->ws_call("ent:ScaricaPagamentoRTRequest", $RequestString, "ScaricaPagamentoRT");
    }

    public function ws_RiceviRendicontazionePagamenti($param) {
        $DataContabileInizialeSoapVal = new soapval('ent:DataContabileIniziale', 'ent:DataContabileIniziale', $param['DataContabileIniziale'], false, false);
        $RequestString .= $DataContabileInizialeSoapVal->serialize('literal');
        $DataContabileFinaleSoapVal = new soapval('ent:DataContabileFinale', 'ent:DataContabileFinale', $param['DataContabileFinale'], false, false);
        $RequestString .= $DataContabileFinaleSoapVal->serialize('literal');
        if (isset($param['Inizio'])) {
            $InizioSoapVal = new soapval('ent:Inizio', 'ent:Inizio', $param['Inizio'], false, false);
            $RequestString .= $InizioSoapVal->serialize('literal');
        }
        if (isset($param['Inizio'])) {
            $InizioSoapVal = new soapval('ent:Inizio', 'ent:Inizio', $param['Inizio'], false, false);
            $RequestString .= $InizioSoapVal->serialize('literal');
        }
        if (isset($param['NumeroPosizioni'])) {
            $NumeroPosizioniSoapVal = new soapval('ent:NumeroPosizioni', 'ent:NumeroPosizioni', $param['NumeroPosizioni'], false, false);
            $RequestString .= $NumeroPosizioniSoapVal->serialize('literal');
        }

        return $this->ws_call("ent:RiceviRendicontazionePagamentiRequest", $RequestString, "RiceviRendicontazionePagamenti");
    }

    public function ws_RiceviRuoloIUV($param) {
        $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $param['ID_RUOLO'], false, false);
        $RequestString = $ID_RUOLOSoapVal->serialize('literal');

        return $this->ws_call("ent:RiceviRuoloIUVRequest", $RequestString, "RiceviRuoloIUV");
    }

    public function ws_VerificaStatoRuolo($param) {
        $ID_RUOLOSoapVal = new soapval('ent:ID_RUOLO', 'ent:ID_RUOLO', $param['ID_RUOLO'], false, false);
        $RequestString = $ID_RUOLOSoapVal->serialize('literal');

        return $this->ws_call("ent:VerificaStatoRuoloRequest", $RequestString, "VerificaStatoRuolo");
    }

    public function ws_ScaricaDocumentoPDF($param) {
        // TODO lasciare fisso iuv?
        $tipoChiaveSoapVal = new soapval('ent:TipoChiaveApplicativa', 'ent:TipoChiaveApplicativa', "IUV", false, false);
        $chiaveSoapVal = new soapval('ent:ChiaveApplicativa', 'ent:ChiaveApplicativa', $param['CodiceIdentificativo'], false, false);
        $RequestString = $tipoChiaveSoapVal->serialize('literal');
        $RequestString .= $chiaveSoapVal->serialize('literal');

        return $this->ws_call("ent:ScaricaDocumentoPDFRequest", $RequestString, "ScaricaDocumentoPDF");
    }

}

?>
