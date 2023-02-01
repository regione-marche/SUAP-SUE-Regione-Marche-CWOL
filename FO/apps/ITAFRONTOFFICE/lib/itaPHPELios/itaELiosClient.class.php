<?php

/**
 *
 * Classe per collegamento ws halley E-Lios
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPELios
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    06.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaELiosClient {

    private $nameSpaces = array();
    private $namespace = "";
    private $webservices_uri = "";
    private $webservices_uriDizionario = "";
    private $webservices_wsdl = "";
    private $webservices_wsdlDizionario = "";
    private $username = "";
    private $password = "";
    private $CodiceAOO = "";
    private $CodiceDitta = "";
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

    public function setWebservices_uriDizionario($webservices_uriDiz) {
        $this->webservices_uriDizionario = $webservices_uriDiz;
    }

    public function setWebservices_wsdlDizionario($webservices_wsdlDiz) {
        $this->webservices_wsdlDizionario = $webservices_wsdlDiz;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getCodiceAOO() {
        return $this->CodiceAOO;
    }

    public function setCodiceAOO($CodiceAOO) {
        $this->CodiceAOO = $CodiceAOO;
    }

    public function setCodiceDitta($CodiceDitta) {
        $this->CodiceDitta = $CodiceDitta;
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

    private function ws_call($operationName, $param, $ns = "tem:") {
        $this->clearResult();
        $client = new nusoap_client($this->webservices_uri, false);
//        $client = new nusoap_client($this->webservices_wsdl, true);
        $client->debugLevel = 0;
        $client->timeout = $this->timeout > 0 ? $this->timeout : 120;
        $client->response_timeout = $this->timeout;
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->namespace . "/" . $operationName;
        $headers = false;
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';
        $ditta = ITA_DB_SUFFIX;
        $result = $client->call($ns . $operationName, $param, $this->nameSpaces, $soapAction, $headers, $rpcParams, $style, $use);
//        file_put_contents("/users/tmp/param_$operationName" . "_$ditta.xml", $param);
//        file_put_contents("/users/tmp/request_$operationName" . "_$ditta.xml", $client->request);
//        file_put_contents("/users/tmp/response_$operationName" . "_$ditta.xml", $client->response);
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
        $strCodEnteSoapval = new soapval('tem:strCodEnte', 'tem:strCodEnte', $param['strCodEnte'], false, false);
        $strUserNameSoapval = new soapval('tem:strUserName', 'tem:strUserName', $param['strUserName'], false, false);
        $strPasswordSoapval = new soapval('tem:strPassword', 'tem:strPassword', $param['strPassword'], false, false);
        $param = $strCodEnteSoapval->serialize("literal") . $strUserNameSoapval->serialize("literal") . $strPasswordSoapval->serialize("literal");
        return $this->ws_call('Login', $param);
    }

    public function ws_Inserimento($param) {
        $strUserNameSoapval = new soapval('tem:strUserName', 'tem:strUserName', $param['strUserName'], false, false);
        $strTokenSoapval = new soapval('tem:strDST', 'tem:strDST', $param['strDST'], false, false);
        $strNomeFileSoapval = new soapval('tem:strDocument', 'tem:strDocument', $param['strDocument'], false, false);
        $strStreamSoapval = new soapval('tem:objDocument', 'tem:objDocument', $param['objDocument'], false, false);
        $param = $strUserNameSoapval->serialize("literal") . $strTokenSoapval->serialize("literal") . $strNomeFileSoapval->serialize("literal") . $strStreamSoapval->serialize("literal");
        return $this->ws_call('Inserimento', $param);
    }

    public function ws_Protocollazione($dati) {
        $strUserNameSoapval = new soapval('tem:strUserName', 'tem:strUserName', $dati['strUserName'], false, false);
        $strTokenSoapval = new soapval('tem:strDST', 'tem:strDST', $dati['strDST'], false, false);

        /*
         * PREPARAZIONE XML
         */
        $paramArr = array();
        if (is_array($dati['Intestazione'])) {
            $IntestazioneSoapvalArr = array();
            //Oggetto
            $OggettoSoapval = new soapval('Oggetto', 'Oggetto', $dati['Intestazione']['Oggetto'], false, false);
            $IntestazioneSoapvalArr[] = $OggettoSoapval;
            //Identificatore
            if (is_array($dati['Intestazione']['Identificatore'])) {
                $IdentificatoreSoapvalArr = array();
                if (isset($dati['Intestazione']['Identificatore']['CodiceAmministrazione'])) {
                    $IdentificatoreSoapvalArr[] = new soapval('CodiceAmministrazione', 'CodiceAmministrazione', $dati['Intestazione']['Identificatore']['CodiceAmministrazione'], false, false);
                }
                if (isset($dati['Intestazione']['Identificatore']['CodiceAOO'])) {
                    $IdentificatoreSoapvalArr[] = new soapval('CodiceAOO', 'CodiceAOO', $dati['Intestazione']['Identificatore']['CodiceAOO'], false, false);
                }
                if (isset($dati['Intestazione']['Identificatore']['NumeroRegistrazione'])) {
                    $IdentificatoreSoapvalArr[] = new soapval('NumeroRegistrazione', 'NumeroRegistrazione', $dati['Intestazione']['Identificatore']['NumeroRegistrazione'], false, false);
                }
                if (isset($dati['Intestazione']['Identificatore']['DataRegistrazione'])) {
                    $IdentificatoreSoapvalArr[] = new soapval('DataRegistrazione', 'DataRegistrazione', $dati['Intestazione']['Identificatore']['DataRegistrazione'], false, false);
                }
                if (isset($dati['Intestazione']['Identificatore']['Flusso'])) {
                    $IdentificatoreSoapvalArr[] = new soapval('Flusso', 'Flusso', $dati['Intestazione']['Identificatore']['Flusso'], false, false);
                }
                $IntestazioneSoapvalArr[] = new soapval('Identificatore', 'Identificatore', $IdentificatoreSoapvalArr, false, false);
            }
            //PARTENZA -> Mittente = Amministrazione
            if (isset($dati['Intestazione']['Mittente']['Amministrazione'])) {
                //Destinatario = Persona, Persona
                if (is_array($dati['Intestazione']['Mittente'])) {
                    $MittenteSoapvalArr = array();
                    if (is_array($dati['Intestazione']['Mittente']['Amministrazione'])) {
                        $AmministrazioneSoapvalArr = array();
                        if (isset($dati['Intestazione']['Mittente']['Amministrazione']['Denominazione'])) {
                            $AmministrazioneSoapvalArr[] = new soapval('Denominazione', 'Denominazione', $dati['Intestazione']['Mittente']['Amministrazione']['Denominazione'], false, false);
                        }
                        if (isset($dati['Intestazione']['Mittente']['Amministrazione']['CodiceAmministrazione'])) {
                            $AmministrazioneSoapvalArr[] = new soapval('CodiceAmministrazione', 'CodiceAmministrazione', $dati['Intestazione']['Mittente']['Amministrazione']['CodiceAmministrazione'], false, false);
                        }
                        if (isset($dati['Intestazione']['Mittente']['Amministrazione']['IndirizzoTelematico'])) {
                            $AmministrazioneSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $dati['Intestazione']['Mittente']['Amministrazione']['IndirizzoTelematico'], false, false);
                        }
                        if (isset($dati['Intestazione']['Mittente']['Amministrazione']['UnitaOrganizzativa'])) {
                            $attrUO = $dati['Intestazione']['Mittente']['Amministrazione']['UnitaOrganizzativa']['Attributi']; //id => 5
                            $AmministrazioneSoapvalArr[] = new soapval('UnitaOrganizzativa', 'UnitaOrganizzativa', "", false, false, $attrUO);
                        }
                        $MittenteSoapvalArr[] = new soapval('Amministrazione', 'Amministrazione', $AmministrazioneSoapvalArr, false, false);
                    }
                    if (isset($dati['Intestazione']['Mittente']['AOO'])) {
                        $MittenteSoapvalArr[] = new soapval('AOO', 'AOO', $dati['Intestazione']['Mittente']['AOO'], false, false);
                    }
                    if (isset($dati['Intestazione']['Mittente']['IndirizzoTelematico'])) {
                        $MittenteSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $dati['Intestazione']['Mittente']['IndirizzoTelematico'], false, false);
                    }
                    $IntestazioneSoapvalArr[] = new soapval('Mittente', 'Mittente', $MittenteSoapvalArr, false, false);
                }
                if (is_array($dati['Intestazione']['Destinatario'])) {
                    foreach ($dati['Intestazione']['Destinatario'] as $Destinatario) {
                        $DestinatarioSoapvalArr = array();
                        if (is_array($Destinatario['Persona'])) {
                            $PersonaSoapvalArr = array();
                            if (isset($Destinatario['Persona']['Nome'])) {
                                $PersonaSoapvalArr[] = new soapval('Nome', 'Nome', $Destinatario['Persona']['Nome'], false, false);
                            }
                            if (isset($Destinatario['Persona']['Cognome'])) {
                                $PersonaSoapvalArr[] = new soapval('Cognome', 'Cognome', $Destinatario['Persona']['Cognome'], false, false);
                            }
                            if (isset($Destinatario['Persona']['CodiceFiscale'])) {
                                $PersonaSoapvalArr[] = new soapval('CodiceFiscale', 'CodiceFiscale', $Destinatario['Persona']['CodiceFiscale'], false, false);
                            }
                            if (isset($Destinatario['Persona']['IndirizzoTelematico'])) {
                                $PersonaSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $Destinatario['Persona']['IndirizzoTelematico'], false, false);
                            }
                            $attrPersona = $Destinatario['Persona']['Attributi']; //id => MZZMRA82D22F522Q
                            $DestinatarioSoapvalArr[] = new soapval('Persona', 'Persona', $PersonaSoapvalArr, false, false, $attrPersona);
                        }
                        $IntestazioneSoapvalArr[] = new soapval('Destinatario', 'Destinatario', $DestinatarioSoapvalArr, false, false);
                    }
                }
            } else {
                //ARRIVO -> Mittente = Persona
                if (is_array($dati['Intestazione']['Mittente'])) {
                    foreach ($dati['Intestazione']['Mittente'] as $Mittente) {
                        $MittenteSoapvalArr = array();
                        if (is_array($Mittente['Persona'])) {
                            $PersonaSoapvalArr = array();
                            if (isset($Mittente['Persona']['Nome'])) {
                                $PersonaSoapvalArr[] = new soapval('Nome', 'Nome', $Mittente['Persona']['Nome'], false, false);
                            }
                            if (isset($Mittente['Persona']['Cognome'])) {
                                $PersonaSoapvalArr[] = new soapval('Cognome', 'Cognome', $Mittente['Persona']['Cognome'], false, false);
                            }
                            if (isset($Mittente['Persona']['CodiceFiscale'])) {
                                $PersonaSoapvalArr[] = new soapval('CodiceFiscale', 'CodiceFiscale', $Mittente['Persona']['CodiceFiscale'], false, false);
                            }
                            if (isset($Mittente['Persona']['IndirizzoTelematico'])) {
                                $PersonaSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $Mittente['Persona']['IndirizzoTelematico'], false, false);
                            }
                            $attrPersona = $Mittente['Persona']['Attributi']; //id => MZZMRA82D22F522Q
                            $MittenteSoapvalArr[] = new soapval('Persona', 'Persona', $PersonaSoapvalArr, false, false, $attrPersona);
                        }
                        $IntestazioneSoapvalArr[] = new soapval('Mittente', 'Mittente', $MittenteSoapvalArr, false, false);
                    }
                }
                //Destinatario = Amministrazione
                if (is_array($dati['Intestazione']['Destinatario'])) {
                    foreach ($dati['Intestazione']['Destinatario'] as $Destinatario) {
                        $DestinatarioSoapvalArr = array();
                        if (is_array($Destinatario['Amministrazione'])) {
                            $AmministrazioneSoapvalArr = array();
                            if (isset($Destinatario['Amministrazione']['Denominazione'])) {
                                $AmministrazioneSoapvalArr[] = new soapval('Denominazione', 'Denominazione', $Destinatario['Amministrazione']['Denominazione'], false, false);
                            }
                            if (isset($Destinatario['Amministrazione']['CodiceAmministrazione'])) {
                                $AmministrazioneSoapvalArr[] = new soapval('CodiceAmministrazione', 'CodiceAmministrazione', $Destinatario['Amministrazione']['CodiceAmministrazione'], false, false);
                            }
                            if (isset($Destinatario['Amministrazione']['IndirizzoTelematico'])) {
                                $AmministrazioneSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $Destinatario['Amministrazione']['IndirizzoTelematico'], false, false);
                            }
                            if (isset($Destinatario['Amministrazione']['UnitaOrganizzativa'])) {
                                $attrUnitaOrganizzativa = $Destinatario['Amministrazione']['UnitaOrganizzativa']['Attributi']; //id => 1
                                $AmministrazioneSoapvalArr[] = new soapval('UnitaOrganizzativa', 'UnitaOrganizzativa', "", false, false, $attrUnitaOrganizzativa);
                            }
                            $DestinatarioSoapvalArr[] = new soapval('Amministrazione', 'Amministrazione', $AmministrazioneSoapvalArr, false, false);
                        }
                        if (isset($Destinatario['AOO'])) {
                            $AOOSoapvalArr = array();
                            if (isset($Destinatario['AOO']['CodiceAOO'])) {
                                $AOOSoapvalArr[] = new soapval('CodiceAOO', 'CodiceAOO', $Destinatario['AOO']['CodiceAOO'], false, false);
                            }
                            $DestinatarioSoapvalArr[] = new soapval('AOO', 'AOO', $AOOSoapvalArr, false, false);
                        }
                        if (isset($Destinatario['IndirizzoTelematico'])) {
                            $DestinatarioSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $Destinatario['IndirizzoTelematico'], false, false);
                        }
                        $IntestazioneSoapvalArr[] = new soapval('Destinatario', 'Destinatario', $DestinatarioSoapvalArr, false, false);
                    }
//                    $DestinatarioSoapvalArr = array();
//                    if (is_array($dati['Intestazione']['Destinatario']['Amministrazione'])) {
//                        $AmministrazioneSoapvalArr = array();
//                        if (isset($dati['Intestazione']['Destinatario']['Amministrazione']['Denominazione'])) {
//                            $AmministrazioneSoapvalArr[] = new soapval('Denominazione', 'Denominazione', $dati['Intestazione']['Destinatario']['Amministrazione']['Denominazione'], false, false);
//                        }
//                        if (isset($dati['Intestazione']['Destinatario']['Amministrazione']['CodiceAmministrazione'])) {
//                            $AmministrazioneSoapvalArr[] = new soapval('CodiceAmministrazione', 'CodiceAmministrazione', $dati['Intestazione']['Destinatario']['Amministrazione']['CodiceAmministrazione'], false, false);
//                        }
//                        if (isset($dati['Intestazione']['Destinatario']['Amministrazione']['IndirizzoTelematico'])) {
//                            $AmministrazioneSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $dati['Intestazione']['Destinatario']['Amministrazione']['IndirizzoTelematico'], false, false);
//                        }
//                        if (isset($dati['Intestazione']['Destinatario']['Amministrazione']['UnitaOrganizzativa'])) {
//                            $attrUnitaOrganizzativa = $dati['Intestazione']['Destinatario']['Amministrazione']['UnitaOrganizzativa']['Attributi']; //id => 1
//                            $AmministrazioneSoapvalArr[] = new soapval('UnitaOrganizzativa', 'UnitaOrganizzativa', "", false, false, $attrUnitaOrganizzativa);
//                        }
//                        $DestinatarioSoapvalArr[] = new soapval('Amministrazione', 'Amministrazione', $AmministrazioneSoapvalArr, false, false);
//                    }
//                    if (isset($dati['Intestazione']['Destinatario']['AOO'])) {
//                        $AOOSoapvalArr = array();
//                        if (isset($dati['Intestazione']['Destinatario']['AOO']['CodiceAOO'])) {
//                            $AOOSoapvalArr[] = new soapval('CodiceAOO', 'CodiceAOO', $dati['Intestazione']['Destinatario']['AOO']['CodiceAOO'], false, false);
//                        }
//                        $DestinatarioSoapvalArr[] = new soapval('AOO', 'AOO', $AOOSoapvalArr, false, false);
//                    }
//                    if (isset($dati['Intestazione']['Destinatario']['IndirizzoTelematico'])) {
//                        $DestinatarioSoapvalArr[] = new soapval('IndirizzoTelematico', 'IndirizzoTelematico', $dati['Intestazione']['Destinatario']['IndirizzoTelematico'], false, false);
//                    }
//                    $IntestazioneSoapvalArr[] = new soapval('Destinatario', 'Destinatario', $DestinatarioSoapvalArr, false, false);
                }
            }
            //Classifica
            if (is_array($dati['Intestazione']['Classifica'])) {
                $ClassificaSoapvalArr = array();
                if (isset($dati['Intestazione']['Classifica']['CodiceAmministrazione'])) {
                    $ClassificaSoapvalArr[] = new soapval('CodiceAmministrazione', 'CodiceAmministrazione', $dati['Intestazione']['Classifica']['CodiceAmministrazione'], false, false);
                }
                if (isset($dati['Intestazione']['Classifica']['CodiceAOO'])) {
                    $ClassificaSoapvalArr[] = new soapval('CodiceAOO', 'CodiceAOO', $dati['Intestazione']['Classifica']['CodiceAOO'], false, false);
                }
                if (isset($dati['Intestazione']['Classifica']['CodiceTitolario'])) {
                    $ClassificaSoapvalArr[] = new soapval('CodiceTitolario', 'CodiceTitolario', $dati['Intestazione']['Classifica']['CodiceTitolario'], false, false);
                }
                $IntestazioneSoapvalArr[] = new soapval('Classifica', 'Classifica', $ClassificaSoapvalArr, false, false);
            }
            //Fascicolo
            $attrFascicolo = $dati['Intestazione']['Fascicolo']['Attributi']; //array(numero=>53, anno=>2013);
            $IntestazioneSoapvalArr[] = new soapval('Fascicolo', 'Fascicolo', $dati['Intestazione']['Fascicolo']['Descrizione'], false, false, $attrFascicolo);
        }//INTESTAZIONE
        //Descrizione
        if (is_array($dati['Descrizione'])) {
            $DescrizioneSoapvalArr = array();
            //Documento - è il documento principale
            if (is_array($dati['Descrizione']['Documento'])) {
                $DocumentoSoapvalArr = array();
                if (isset($dati['Descrizione']['Documento']['DescrizioneDocumento'])) {
                    $DocumentoSoapvalArr[] = new soapval('DescrizioneDocumento', 'DescrizioneDocumento', $dati['Descrizione']['Documento']['DescrizioneDocumento'], false, false);
                }
                if (isset($dati['Descrizione']['Documento']['TipoDocumento'])) {
                    $DocumentoSoapvalArr[] = new soapval('TipoDocumento', 'TipoDocumento', $dati['Descrizione']['Documento']['TipoDocumento'], false, false);
                }
                $attrDocumento = $dati['Descrizione']['Documento']['Attributi']; //nome=> CI.jpg, id => 122
                $DescrizioneSoapvalArr[] = new soapval('Documento', 'Documento', $DocumentoSoapvalArr, false, false, $attrDocumento);
            }
            //Allegati
            if (is_array($dati['Descrizione']['Allegati'])) {
                $AllegatiSoapvalArr = array();
                //foreach ($dati['Descrizione']['Allegati'] as $Allegato) {
                foreach ($dati['Descrizione']['Allegati']['Documento'] as $Allegato) {
                    $DocumentoSoapvalArr = array();
                    if (isset($Allegato['DescrizioneDocumento'])) {
                        $DocumentoSoapvalArr[] = new soapval('DescrizioneDocumento', 'DescrizioneDocumento', $Allegato['DescrizioneDocumento'], false, false);
                    }
                    if (isset($Allegato['TipoDocumento'])) {
                        $DocumentoSoapvalArr[] = new soapval('TipoDocumento', 'TipoDocumento', $Allegato['TipoDocumento'], false, false);
                    }
                    $attrDocumento = $Allegato['Attributi']; //nome=> CI.jpg, id => 122
                    $AllegatiSoapvalArr[] = new soapval('Documento', 'Documento', $DocumentoSoapvalArr, false, false, $attrDocumento);
                }
                $DescrizioneSoapvalArr[] = new soapval('Allegati', 'Allegati', $AllegatiSoapvalArr, false, false);
            }
        }
        $IntestazioneSoapval = new soapval('Intestazione', 'Intestazione', $IntestazioneSoapvalArr, false, false);
        $DescrizioneSoapval = new soapval('Descrizione', 'Descrizione', $DescrizioneSoapvalArr, false, false);

        $paramArr[] = $IntestazioneSoapval;
        $paramArr[] = $DescrizioneSoapval;
        /*
         * FINE XML
         */

        //CREO CDATA
        $strDocumentInfo = "<tem:strDocumentInfo><![CDATA[<Segnatura xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\">";
        foreach ($paramArr as $parametro) {
            $strDocumentInfo .= $parametro->serialize('literal');
        }
        $strDocumentInfo .= "</Segnatura>]]></tem:strDocumentInfo>";
        $param = $strDocumentInfo;
        $param = $strUserNameSoapval->serialize("literal") . $strTokenSoapval->serialize("literal") . $strDocumentInfo;

        return $this->ws_call('Protocollazione', $param);
    }

}

?>
