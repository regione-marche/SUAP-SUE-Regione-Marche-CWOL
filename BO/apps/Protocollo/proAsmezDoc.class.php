<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo Conservazione Asmez versione 1.0
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    01.08.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

class proAsmezDoc {

    const CLASSE_PARAMETRI = 'ASMEZDOCPROTREG';
    const VERSIONE_SERVIZIO = '1.0';

    public static $ERR_SERVIZIO = array(
        "001" => "ipa non esistente in archivio",
        "002" => "dati non validi",
        "003" => "login o password non valide"
    );

    const NS_PREFIX = "doc";

    public $PROT_DB;
    public $ITALWEB_DB;
    public $devLib;
    public $proLib;
    public $proLibAllegati;
    private $errCode;
    private $errMessage;
    private $xmlRichiesta;
    private $xmlResponso;
    private $datiMinimiEsitoVersamento;

    function __construct() {
        $this->devLib = new devLib();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getDatiMinimiEsitoVersamento() {
        return $this->datiMinimiEsitoVersamento;
    }

    public function setDatiMinimiEsitoVersamento($datiMinimiEsitoVersamento) {
        $this->datiMinimiEsitoVersamento = $datiMinimiEsitoVersamento;
    }

    public function getXmlRichiesta() {
        return $this->xmlRichiesta;
    }

    public function setXmlRichiesta($xmlRichiesta) {
        $this->xmlRichiesta = $xmlRichiesta;
    }

    public function getXmlResponso() {
        return $this->xmlResponso;
    }

    public function setXmlResponso($xmlResponso) {
        $this->xmlResponso = $xmlResponso;
    }

    public function setITALWEBDB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    /**
     * Lettura paramwtri ws
     * 
     * @return type
     */
    public function GetParametri() {
        $Parametri = array();
        $EnvParametri = $this->devLib->getEnv_config(self::CLASSE_PARAMETRI, 'codice', '', true);
        foreach ($EnvParametri as $key => $Parametro) {
            $Parametri[$Parametro['CHIAVE']] = $Parametro['CONFIG'];
        }

        return $Parametri;
    }

    /*
     *  Versamento registro di protocollo
     */

    public function versaAsmezDocWebProtReg($rowid_Anapro) {
        /*
         * Rileggo record Anapro e dati correlati (usa proPortocollo??)
         */
        $Anapro_rec = $this->proLib->GetAnapro($rowid_Anapro, 'rowid');
        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $where = ' AND DOCSERVIZIO=0 ORDER BY DOCTIPO ASC,ROWID ASC';
        $Anadoc_tab = $this->proLib->GetAnadoc($Anapro_rec['PRONUM'], 'protocollo', true, $Anapro_rec['PROPAR'], $where);

        /*
         * Controllo su tipolgia documento deve essere specifica
         */
        $proLibGiornaliero = new proLibGiornaliero();
        $parametriRegistro = $proLibGiornaliero->getParametriRegistro();
        $unitaValida = false;
        if ($Anapro_rec['PROPAR'] == 'C' && $Anapro_rec['PROCODTIPODOC'] == $parametriRegistro['TIPODOCUMENTO']) {
            $unitaValida = true;
        }
        if (!$unitaValida) {
            $this->setErrCode(-1);
            $this->setErrMessage("Documento non valido per la conservazione");
            return false;
        }

        /*
         * Parametri collegamento ws
         */
        $Parametri = $this->GetParametri();


        /*
         * Parametri Protocollo
         * 
         */
        $Anaent_2_rec = $this->proLib->GetAnaent('2');
        $DescrizioneEnte = $Anaent_2_rec['ENTDE1'];
        $ipa_amministrazione = $this->proLib->getIPAAmministrazione();

        /*
         * Leggo i Campi Attivi di Tabdag per il registro di protocollo
         */
        $campoDataRegistro = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_REGISTRO];
        $campoDataIniziale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoDataFinale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoPrimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_INIZIALE];
        $campoUltimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_FINALE];
        $campoSoggettoProduttore = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_SOGGETTO_PRODUTTORE];
        $campoSoggettoResponsabile = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_RESPONSABILE];

        /*
         * Lettura Metadati e preparazione Variabili
         */
        $proLibGiornaliero = new proLibGiornaliero();
        $metaDati = $proLibGiornaliero->getAnaproAndMetadati($Anapro_rec['ROWID']);

        /*
         * Predisposizione Variabili
         */
        $Numero = intval(substr($Anapro_rec['PRONUM'], 4));
        $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $Data = date("d-m-Y", strtotime($Anapro_rec['PRODAR']));
        $DataIniziale = date("d-m-Y", strtotime($metaDati[$campoDataRegistro]));
        $DataFinale = date("d-m-Y", strtotime($metaDati[$campoDataRegistro]));
        if ($metaDati[$campoDataIniziale]) {
            $DataIniziale = date("d-m-Y", strtotime($metaDati[$campoDataIniziale]));
        }
        if ($metaDati[$campoDataFinale]) {
            $DataFinale = date("d-m-Y", strtotime($metaDati[$campoDataFinale]));
        }
        $PrimoNumero = 0;
        $UltimoNumero = 0;
        if ($metaDati[$campoPrimoNumero]) {
            $PrimoNumero = $metaDati[$campoPrimoNumero];
        }
        if ($metaDati[$campoUltimoNumero]) {
            $UltimoNumero = $metaDati[$campoUltimoNumero];
        }
        $SoggettoProduttore = $metaDati[$campoSoggettoProduttore];
        $SoggettoResponsabile = $metaDati[$campoSoggettoResponsabile];
        $Oggetto = $Anaogg_rec['OGGOGG'];

        switch ($Anapro_rec['PROPAR']) {
            case 'C':
                $TipoRegistro = $this->proLib->GetCodiceRegistroDocFormali();
                break;
            case 'A':
            case 'P':
                $TipoRegistro = $this->proLib->GetCodiceRegistroProtocollo();
                break;
        }


        //$path = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $AnadocDocPrincipale = $Anadoc_tab[0];
        //$PathFile = $path . '/' . $AnadocDocPrincipale['DOCFIL'];
        $NomeFile = $AnadocDocPrincipale['DOCNAME'];

        //$StreamFile = urlencode(base64_encode(file_get_contents(($PathFile))));
        $StreamFile = urlencode($this->proLibAllegati->GetDocBinary($AnadocDocPrincipale['ROWID'], true));
        if ($this->proLibAllegati->getErrCode() == -1) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->proLibAllegati->getErrMessage());
            return false;
        }
        $Estensione = $this->GetEstensione($NomeFile);
        $FormatoFile = $Estensione;
        $NumeroAllegati = count($Anadoc_tab) - 1;
        $TipologiaUnitaDocumentaria = 'Registro di Protocollo';
        $TipoDocumento = 'Registro di Protocollo';
        $metadata_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <documento>
                    <dataRegistroGiornalieroProtocollo>' . $DataIniziale . '</dataRegistroGiornalieroProtocollo>
                    <oggetto>' . htmlentities($Oggetto, ENT_COMPAT) . '</oggetto>
                    <tipoProtocollo>T</tipoProtocollo>
                    <utente>' . htmlentities($Parametri['WSWEBPROTREGUTENTE'], ENT_COMPAT) . '</utente>
                    <mittente>' . htmlentities($SoggettoProduttore, ENT_COMPAT) . '</mittente>
            </documento>';
        $metadata_xml = utf8_encode($metadata_xml);
        $metadata_stream = urlencode(base64_encode($metadata_xml));
        $metadata_name = 'Metadata.xml';


        $token = $this->creaToken($Parametri['WSWEBPROTREGUTENTE'], $Parametri['WSWEBPROTREGPWD']);

        //
        //Out::msgInfo("Token 1", $token);
        //



        include_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
        $client = new nusoap_client($Parametri['WSWEBPROTREGENDPOINT'], false);
        //$client = new nusoap_client($Parametri['WSWEBPROTREGWSDL'], true);
        $client->debugLevel = 0;
        $client->timeout = $Parametri['WSWEBPROTREGTIMEOUT'] > 0 ? $Parametri['WSWEBPROTREGTIMEOUT'] : 120;
        $client->response_timeout = $Parametri['WSWEBPROTREGTIMEOUT'];

        /*
         * Verifica credenziali e reperimento token
         * Metodo GetData
         */
        $client->setHeaders($this->creaHeader($Parametri['WSWEBPROTREGUTENTE'], $token));
        $client->soap_defencoding = 'UTF-8';
        $operationName = "GetData";
        $soapAction = $Parametri['WSWEBPROTREGNAMESPACE'] . "/" . $operationName;
        $param = array(self::NS_PREFIX . ":IPA" => $ipa_amministrazione);
        $result = $client->call(self::NS_PREFIX . ":$operationName", $param, array(self::NS_PREFIX => $Parametri['WSWEBPROTREGNAMESPACE'] . "/"), $soapAction);

        //
        //Out::msgInfo("Risultato login", print_r($result, true));
        //
        //$this->setXmlRichiesta($client->request);
        //$this->setXmlResponso($client->response);
        file_put_contents("/users/pc/dos2ux/asmezlogin.log", $client->request . "\n\n\n");
        file_put_contents("/users/pc/dos2ux/asmezlogin.log", $client->response, FILE_APPEND);

        if ($client->fault) {
            $this->setErrCode(-1);
            $this->setErrMessage($client->faultstring);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->setErrCode(-1);
                $this->setErrMessage($err);
                return false;
            }
        }
        if ($result['string'][0] !== '000') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore di Accesso ({$result['string'][0]})");
            return false;
        }
        /*
         * PREPARAZIONE DEI parametri
         */
        //
        //

        $client->setHeaders($this->creaHeader($Parametri['WSWEBPROTREGUTENTE'], $token));
        $client->soap_defencoding = 'UTF-8';
        $operationName = "ImportData";
        $soapAction = $Parametri['WSWEBPROTREGNAMESPACE'] . "/" . $operationName;
        $param = array();
        $param[self::NS_PREFIX . ":Provenienza"] = $DescrizioneEnte;
        $param[self::NS_PREFIX . ":Ipa"] = $ipa_amministrazione;
        $param[self::NS_PREFIX . ":Data"] = $DataIniziale;
        $param[self::NS_PREFIX . ":TipoProto"] = 'T';
        $param[self::NS_PREFIX . ":Descrizione"] = $Oggetto;
        $param[self::NS_PREFIX . ":NomeAllegato"] = $NomeFile;
        $param[self::NS_PREFIX . ":Allegato"] = $StreamFile;
        $param[self::NS_PREFIX . ":Nomemetadata"] = $metadata_name;
        $param[self::NS_PREFIX . ":Metadata"] = $metadata_stream;
        //Out::msgInfo("param import", print_r($param, true));
        $result = $client->call(self::NS_PREFIX . ":$operationName", $param, array(self::NS_PREFIX => $Parametri['WSWEBPROTREGNAMESPACE'] . "/"), $soapAction);

        $this->setXmlRichiesta($metadata_xml);
        $this->setXmlResponso($client->responseData);
        if (!$this->parseEsitoVersamento($result)) {
            $this->setErrCode(-1);
            //$this->setErrMessage("Errore Analisi xml responso versamento DIGIP");
            return false;
        }
        file_put_contents("/users/pc/dos2ux/asmezimport.log", $client->request . "\n\n\n");
        file_put_contents("/users/pc/dos2ux/asmezimport.log", $client->response, FILE_APPEND);
        if ($client->fault) {
            $this->setErrCode(-1);
            $this->setErrMessage($client->faultstring);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->setErrCode(-1);
                $this->setErrMessage($err);
                return false;
            }
        }
        if ($result !== '000') {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore di Importazione Dati ({$result})");
            return false;
        }

        //Out::msgInfo("Result", $result);

        $this->result = $result;
        return true;
    }

    public function GetEstensione($NomeFile) {
        // SEMPLIFICARE?
        $Estensione = '';
        $Ctr = 1;
        while (true) {
            $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            if ($ext == '' || $Ctr == 3) {
                break;
            }
            $Estensione = '.' . $ext . $Estensione;
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    private function creaToken($userName, $password) {
        /*
         * Data ora corrente
         */
        $data = date('dmYHis');
        $data = str_split($data);

        /*
         * le prime 6 dispari
         */
        $dataPosDispari = $data[0] . $data[2] . $data[4] . $data[6] . $data[8] . $data[10];

        /*
         * le prime 5 pari
         */
        $dataPosPari = $data[1] . $data[3] . $data[5] . $data[7] . $data[9];

        /*
         * calcolo la sha256 della pwd
         */
        $pwdSha256 = hash('sha256', $password);
        $pwdSha256 = strtoupper($pwdSha256);


        /*
         * aggiungo pari dispari in testa e in coda
         */
        $pwdSha256 = $dataPosDispari . $pwdSha256 . $dataPosPari;

        /*
         * Riapplico hash
         */
        $sha256def = hash('sha256', $pwdSha256);
        $sha256def = strtoupper($sha256def);
        //Out::msgInfo("Token_1", $sha256def);
        return $sha256def;
    }

    private function creaHeader($utente, $token) {
        $head = "<" . self::NS_PREFIX . ":AuthHeader>
         <" . self::NS_PREFIX . ":userName>$utente</" . self::NS_PREFIX . ":userName>
         <" . self::NS_PREFIX . ":password>$token</" . self::NS_PREFIX . ":password>
      </" . self::NS_PREFIX . ":AuthHeader>";
        return $head;
    }

    private function parseEsitoVersamento($result) {

        if ($result === '000') {
            $esito = proLibConservazione::ESITO_POSTITIVO;
        } else {
            $esito = proLibConservazione::ESITO_NEGATIVO;
        }

        $this->datiMinimiEsitoVersamento = array();
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CONSERVATORE] = self::CLASSE_PARAMETRI;
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_VERSIONE] = self::VERSIONE_SERVIZIO;
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_DATAVERSAMENTO] = date('Y-m-d');
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_ESITO] = $esito;
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CODICEERRORE] = $result;
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE] = '';
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] = "";
        return true;
    }

}

?>
