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
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    29.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaSiciClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_wsdl = "";
    private $applicativo = "";
    private $ente = "";
    private $utente = "";
    private $password = "";
    private $codiceAmm = "";
    private $codiceAOO = "";
    private $timeout = 2400;
    private $result;
    private $error;
    private $fault;
    private $metodo = "";

    public function setNameSpaces($nameSpaces) {
        $nameSpaces = array("mes" => $nameSpaces);
        $this->nameSpaces = $nameSpaces;
    }

    public function setNameSpace($namespace) {
        $this->namespace = $namespace;
    }

    public function setUri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setWsdl($webservices_wsdl) {
        $this->webservices_wsdl = $webservices_wsdl;
    }

    public function setApplicativo($applicativo) {
        $this->applicativo = $applicativo;
    }

    public function getApplicativo() {
        return $this->applicativo;
    }

    public function setUtente($utente) {
        $this->utente = $utente;
    }

    public function getUtente() {
        return $this->utente;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setEnte($ente) {
        $this->ente = $ente;
    }

    public function getEnte() {
        return $this->ente;
    }

    public function setCodiceAmm($codiceAmm) {
        $this->codiceAmm = $codiceAmm;
    }

    public function getCodiceAmm() {
        return $this->codiceAmm;
    }

    public function setCodiceAOO($codiceAOO) {
        $this->codiceAOO = $codiceAOO;
    }

    public function getCodiceAOO() {
        return $this->codiceAOO;
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

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    private function ws_call($operationName, $param, $ns = "mes:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
        //$client = new nusoap_client($this->webservices_wsdl, true);
        $client->useHTTPPersistentConnection();
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->setCredentials($this->username, $this->password, 'basic');
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "." . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/param_$operationName" . "_$this->metodo.xml", $param);
//        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/request_$operationName" . "_$this->metodo.xml", $client->request);
//        file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/response_$operationName" . "_$this->metodo.xml", $client->response);
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

    public function ws_LeggiProtocollo($dati) {
        $this->metodo = "INFOPROTOCOLLO";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $paramXmlSegnatura = array();
        $AnnoSoapval = new soapval('ANNO', 'ANNO', $dati['AnnoProtocollo'], false, false);
        $paramXmlSegnatura[] = $AnnoSoapval;
        $NumeroSoapval = new soapval('NUMERO', 'NUMERO', $dati['NumeroProtocollo'], false, false);
        $paramXmlSegnatura[] = $NumeroSoapval;
        //
        //CREO CDATA
        $ProtocolloInStr = "<XML_PARAM xsi:type=\"xsd:string\"><![CDATA[<SEGNATURA_XML><SegnaturaSK versione=\"2017-01-12\" xml:lang=\"it\">";
        foreach ($paramXmlSegnatura as $parametro) {
            if ($parametro instanceof soapval) {
                $ProtocolloInStr .= $parametro->serialize('literal');
            } else {
                $this->error = "Uno dei parametri non sembra essere un oggetto soapval";
                file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/errParam.log", print_r($parametro, true));
                return false;
            }
        }
        $ProtocolloInStr .= "</SegnaturaSK></SEGNATURA_XML>]]></XML_PARAM>";
        $param .= $ProtocolloInStr;
        //
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

    public function ws_InserisciProtocollo($dati) {
        $this->metodo = "REGISTRAPROTOCOLLO";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $paramXmlSegnatura = array();
        $AccompagnatoriaSoapval = new soapval('Accompagnatoria', 'Accompagnatoria', $dati['Accompagnatoria'], false, false);
        $paramXmlSegnatura[] = $AccompagnatoriaSoapval;
        if ($dati['NumeroProtocolloDiProvenienza']) {
            $NumeroProtocolloDiProvenienzaSoapval = new soapval('NumeroProtocolloDiProvenienza', 'NumeroProtocolloDiProvenienza', $dati['NumeroProtocolloDiProvenienza'], false, false);
            $paramXmlSegnatura[] = $NumeroProtocolloDiProvenienzaSoapval;
        }
        if ($dati['DataRegistrazioneProtocolloDiProvenienza']) {
            $DataRegistrazioneProtocolloDiProvenienzaSoapval = new soapval('DataRegistrazioneProtocolloDiProvenienza', 'DataRegistrazioneProtocolloDiProvenienza', $dati['DataRegistrazioneProtocolloDiProvenienza'], false, false);
            $paramXmlSegnatura[] = $DataRegistrazioneProtocolloDiProvenienzaSoapval;
        }
        $FlussoSoapval = new soapval('Flusso', 'Flusso', $dati['Flusso'], false, false);
        $paramXmlSegnatura[] = $FlussoSoapval;
        $eMailSoapval = new soapval('eMail', 'eMail', $dati['eMail'], false, false);
        $paramXmlSegnatura[] = $eMailSoapval;
        $OggettoSoapval = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
        $paramXmlSegnatura[] = $OggettoSoapval;

        /*
         * Mittente Destinatari
         */
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
            if ($dati['Flusso'] == "U") {
                $MittDestSoapval = new soapval('Destinatari', 'Destinatari', $MDSoapvalArr, false, false);
            } else {
                $MittDestSoapval = $MDSoapvalArr[0];
            }
            $paramXmlSegnatura[] = $MittDestSoapval;
        }

        /*
         * Classifica
         */
        $ClassificaSoapvalArr = array();
        foreach ($dati['Classifica'] as $classifica) {
            $ClassificaSoapvalArr[] = new soapval("Categoria", "Categoria", $classifica['Categoria'], false, false);
            $ClassificaSoapvalArr[] = new soapval("Classe", "Classe", $classifica['Classe'], false, false);
            $ClassificaSoapvalArr[] = new soapval("AnnoFascicolo", "AnnoFascicolo", $classifica['AnnoFascicolo'], false, false);
            $ClassificaSoapvalArr[] = new soapval("NumeroFascicolo", "NumeroFascicolo", $classifica['NumeroFascicolo'], false, false);
            $ClassificaSoapvalArr[] = new soapval("Sottofascicolo", "Sottofascicolo", $classifica['SottoFascicolo'], false, false);
            $ClassSoapvalArr[] = new soapval("Classificazione", "Classificazione", $ClassificaSoapvalArr, false, false);
        }
        $ClassificazioniSoapvalArr = new soapval('Classificazioni', 'Classificazioni', $ClassSoapvalArr, false, false);
        $paramXmlSegnatura[] = $ClassificazioniSoapvalArr;

        /*
         * Assegnazioni
         */
        $AssegnazioneSoapvalArr = array();
        foreach ($dati['Assegnazioni'] as $assegnazione) {
            $AssegnazioneSoapvalArr[] = new soapval("AssegnatoA", "AssegnatoA", $assegnazione["AssegnatoA"], false, false);
            $AssegnazioneSoapvalArr[] = new soapval("AssegnatoDa", "AssegnatoDa", $assegnazione["AssegnatoDa"], false, false);
            $AssSoapvalArr[] = new soapval("Assegnazione", "Assegnazione", $AssegnazioneSoapvalArr, false, false);
        }
        $AssegnazioniSoapvalArr = new soapval('Assegnazioni', 'Assegnazioni', $AssSoapvalArr, false, false);
        $paramXmlSegnatura[] = $AssegnazioniSoapvalArr;

        /*
         * Allegati
         */
        if (is_array($dati['Allegati'])) {
            $AllegatiSoapvalArr = array();
            //foreach ($dati['Descrizione']['Allegati'] as $Allegato) {
            foreach ($dati['Allegati'] as $Allegato) {
                $DocumentoSoapvalArr = array();
                if (isset($Allegato['Commento'])) {
                    $DocumentoSoapvalArr[] = new soapval('DescrizioneDocumento', 'DescrizioneDocumento', $Allegato['Commento'], false, false);
                }
                if (isset($Allegato['Impronta']['Stream'])) {
                    $DocumentoSoapvalArr[] = new soapval('Impronta', 'Impronta', $Allegato['Impronta']['Stream'], false, false, $Allegato['Impronta']['Attributi']);
                    //$DocumentoSoapvalArr[] = new soapval('Impronta', 'Impronta', "", false, false, $Allegato['Impronta']['Attributi']);
                }
                $attrDocumento = $Allegato['Attributi'];
                $AllegatiSoapvalArr[] = new soapval('Documento', 'Documento', $DocumentoSoapvalArr, false, false, $attrDocumento);
            }
            $DescrizioneSoapvalArr = new soapval('Documenti', 'Documenti', $AllegatiSoapvalArr, false, false);
            $paramXmlSegnatura[] = $DescrizioneSoapvalArr;
        }

        //CREO CDATA
        $ProtocolloInStr = "<XML_PARAM xsi:type=\"xsd:string\"><![CDATA[<SEGNATURA_XML><SegnaturaSK versione=\"2017-01-12\" xml:lang=\"it\">";
        foreach ($paramXmlSegnatura as $parametro) {
            if ($parametro instanceof soapval) {
                $ProtocolloInStr .= $parametro->serialize('literal');
            } else {
                $this->error = "Uno dei parametri non sembra essere un oggetto soapval";
                file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/errParam.log", print_r($parametro, true));
                return false;
            }
        }
        $ProtocolloInStr .= "</SegnaturaSK></SEGNATURA_XML>]]></XML_PARAM>";
        $param .= $ProtocolloInStr;
        //
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

    public function ws_LoadFile($dati) {
        $this->metodo = "LOADFILE";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $paramXmlSegnatura = array();
        $FileNameSoapval = new soapval('FILE_NAME', 'FILE_NAME', $dati['nome'], false, false);
        $paramXmlSegnatura[] = $FileNameSoapval;
        $FileContentSoapval = new soapval('FILE_CONTENT', 'FILE_CONTENT', $dati['stream'], false, false);
        $paramXmlSegnatura[] = $FileContentSoapval;
        $isFileCompleteSoapval = new soapval('IS_FILE_COMPLETE', 'IS_FILE_COMPLETE', "TRUE", false, false);
        $paramXmlSegnatura[] = $isFileCompleteSoapval;
        $tmpFileIdSoapval = new soapval('TMP_FILE_ID', 'TMP_FILE_ID', "", false, false);
        $paramXmlSegnatura[] = $tmpFileIdSoapval;
        //
        //CREO CDATA
        $ProtocolloInStr = "<XML_PARAM xsi:type=\"xsd:string\"><![CDATA[<SEGNATURA_XML><SegnaturaSK versione=\"2017-01-12\" xml:lang=\"it\">";
        foreach ($paramXmlSegnatura as $parametro) {
            if ($parametro instanceof soapval) {
                $ProtocolloInStr .= $parametro->serialize('literal');
            } else {
                $this->error = "Uno dei parametri non sembra essere un oggetto soapval";
                file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/errParam.log", print_r($parametro, true));
                return false;
            }
        }
        $ProtocolloInStr .= "</SegnaturaSK></SEGNATURA_XML>]]></XML_PARAM>";
        $param .= $ProtocolloInStr;
        //
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

    public function ws_CreaFascicolo($dati) {
        $this->metodo = "CREAFASCICOLO";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $paramXmlSegnatura = array();
        $AnnoProtSoapval = new soapval('ANNOPROT', 'ANNOPROT', $dati['Anno'], false, false);
        $paramXmlSegnatura[] = $AnnoProtSoapval;
        $CategoriaSoapval = new soapval('CATEGORIA', 'CATEGORIA', $dati['Categoria'], false, false);
        $paramXmlSegnatura[] = $CategoriaSoapval;
        $ClasseSoapval = new soapval('CLASSE', 'CLASSE', $dati['Classe'], false, false);
        $paramXmlSegnatura[] = $ClasseSoapval;
        $AnnoSoapval = new soapval('ANNO', 'ANNO', $dati['Anno'], false, false);
        $paramXmlSegnatura[] = $AnnoSoapval;
        $UfficioSoapval = new soapval('UFFICIO', 'UFFICIO', $dati['Ufficio'], false, false);
        $paramXmlSegnatura[] = $UfficioSoapval;
        $DataAperturaSoapval = new soapval('DATA_APERTURA', 'DATA_APERTURA', $dati['Data'], false, false);
        $paramXmlSegnatura[] = $DataAperturaSoapval;
        $DescrizioneSoapval = new soapval('DESCRIZIONE', 'DESCRIZIONE', $dati['Oggetto'], false, false);
        $paramXmlSegnatura[] = $DescrizioneSoapval;
        //
        $ProtocolloInStr = "<XML_PARAM xsi:type=\"xsd:string\"><![CDATA[<SEGNATURA_XML><SegnaturaSK versione=\"2017-01-12\" xml:lang=\"it\">";
        foreach ($paramXmlSegnatura as $parametro) {
            if ($parametro instanceof soapval) {
                $ProtocolloInStr .= $parametro->serialize('literal');
            } else {
                $this->error = "Uno dei parametri non sembra essere un oggetto soapval";
                file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/errParam.log", print_r($parametro, true));
                return false;
            }
        }
        $ProtocolloInStr .= "</SegnaturaSK></SEGNATURA_XML>]]></XML_PARAM>";
        $param .= $ProtocolloInStr;
        //
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

    public function ws_AssegnaFascicolo($dati) {
        $this->metodo = "ASSEGNAFASCICOLO";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $paramXmlSegnatura = array();
        $AnnoProtSoapval = new soapval('ANNOPROT', 'ANNOPROT', $dati['AnnoProt'], false, false);
        $paramXmlSegnatura[] = $AnnoProtSoapval;
        $NumProtSoapval = new soapval('NUM_PROT', 'NUM_PROT', $dati['NumProt'], false, false);
        $paramXmlSegnatura[] = $NumProtSoapval;
        $CategoriaSoapval = new soapval('CATEGORIA', 'CATEGORIA', $dati['Categoria'], false, false);
        $paramXmlSegnatura[] = $CategoriaSoapval;
        $ClasseSoapval = new soapval('CLASSE', 'CLASSE', $dati['Classe'], false, false);
        $paramXmlSegnatura[] = $ClasseSoapval;
        $AnnoSoapval = new soapval('ANNO', 'ANNO', $dati['Anno'], false, false);
        $paramXmlSegnatura[] = $AnnoSoapval;
        $FascicoloProtSoapval = new soapval('NUMERO', 'NUMERO', $dati['Fascicolo'], false, false);
        $paramXmlSegnatura[] = $FascicoloProtSoapval;
        $SottoFascSoapval = new soapval('SOTTOFASCICOLO', 'SOTTOFASCICOLO', $dati['Sottofascicolo'], false, false);
        $paramXmlSegnatura[] = $SottoFascSoapval;
        //


        $ProtocolloInStr = "<XML_PARAM xsi:type=\"xsd:string\"><![CDATA[<SEGNATURA_XML><SegnaturaSK versione=\"2017-01-12\" xml:lang=\"it\">";
        foreach ($paramXmlSegnatura as $parametro) {
            if ($parametro instanceof soapval) {
                $ProtocolloInStr .= $parametro->serialize('literal');
            } else {
                $this->error = "Uno dei parametri non sembra essere un oggetto soapval";
                file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/errParam.log", print_r($parametro, true));
                return false;
            }
        }
        $ProtocolloInStr .= "</SegnaturaSK></SEGNATURA_XML>]]></XML_PARAM>";
        $param .= $ProtocolloInStr;
        //
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

    public function ws_Titolario() {
        $this->metodo = "TITOLARIO";
        $ApplicativoSoapval = new soapval('APPLICATIVO', 'APPLICATIVO', $this->applicativo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $ApplicativoSoapval->serialize('literal');
        $EnteSoapval = new soapval('ENTE', 'ENTE', $this->ente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $EnteSoapval->serialize('literal');
        $UtenteSoapval = new soapval('COD_UTENTE', 'COD_UTENTE', $this->utente, false, false, array("xsi:type" => "xsd:string"));
        $param .= $UtenteSoapval->serialize('literal');
        $PasswordSoapval = new soapval('PASSWORD', 'PASSWORD', $this->password, false, false, array("xsi:type" => "xsd:string"));
        $param .= $PasswordSoapval->serialize('literal');
        $MetodoSoapval = new soapval('METODO', 'METODO', $this->metodo, false, false, array("xsi:type" => "xsd:string"));
        $param .= $MetodoSoapval->serialize('literal');
        //
        $XML_PARAMoapval = new soapval('XML_PARAM', 'XML_PARAM', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_PARAMoapval->serialize('literal');
        $XML_RETURNSoapval = new soapval('XML_RETURN', 'XML_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $XML_RETURNSoapval->serialize('literal');
        $MSG_RETURNSoapval = new soapval('MSG_RETURN', 'MSG_RETURN', "", false, false, array("xsi:type" => "xsd:string"));
        $param .= $MSG_RETURNSoapval->serialize('literal');
        return $this->ws_call('SICI_WEB_SERVICE', $param);
    }

}

?>
