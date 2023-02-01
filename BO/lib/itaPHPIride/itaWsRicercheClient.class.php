<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Paolo Rosati <paolo.rosati@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    06.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

//require_once(ITA_LIB_PATH . '/nusoap/nusoap1.2.php');

class itaWsRicercheClient {

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
    private $NomeObbligatorio = "";
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

    function getNomeObbligatorio() {
        return $this->NomeObbligatorio;
    }

    function setNomeObbligatorio($NomeObbligatorio) {
        $this->NomeObbligatorio = $NomeObbligatorio;
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
        file_put_contents("C:/tmp/IRparam_$operationName.xml", $param);
        file_put_contents("C:/tmp/IRrequest_$operationName.xml", $client->request);
        file_put_contents("C:/tmp/IRresponse_$operationName.xml", $client->response);
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

    public function ws_RicercaDocumentiString($dati) {
        $paramArr = array();
        $paramArrProt = array();
        $paramArrDoc = array();
        $paramArrProposta = array();
        $paramArrDelibera = array();
        $paramArrDetermina = array();
        $paramArrImpegno = array();
        $paramArrAccertamento = array();
        $paramArrFas = array();
        $paramArrProc = array();
        $paramArrDatiUtente = array();
        $paramArrPianoLavoro = array();

        /*
         * Protocollo
         */
        $AnnoProtSoapval = new soapval('Anno', 'Anno', $dati['Anno'], false, false);
        $paramArrProt[] = $AnnoProtSoapval;

        $NumProtSoapval = new soapval('Numero', 'Numero', $dati['Numero'], false, false);
        $paramArrProt[] = $NumProtSoapval;

//        $AooProtSoapval = new soapval('AOO', 'AOO', $dati['AOO'], false, false);
//        $paramArrProt[] = $AooProtSoapval;

        $DataProtSoapval = new soapval('Data', 'Data', $dati['Data'], false, false);
        $paramArrProt[] = $DataProtSoapval;

//        $ClassificaProtSoapval = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
//        $paramArrProt[] = $ClassificaProtSoapval;

        $RuoloInserimentoProtSoapval = new soapval('RuoloInserimento', 'RuoloInserimento', $dati['RuoloInserimento'], false, false);
        $paramArrProt[] = $RuoloInserimentoProtSoapval;

        $DataAnnullamentoProtSoapval = new soapval('DataAnnullamento', 'DataAnnullamento', $dati['DataAnnullamento'], false, false);
        $paramArrProt[] = $DataAnnullamentoProtSoapval;

        $RegEmeProtSoapval = new soapval('RegistroEmergenzaUfficio', 'RegistroEmergenzaUfficio', $dati['RegistroEmergenzaUfficio'], false, false);
        $paramArrProt[] = $RegEmeProtSoapval;

        $RegNumProtSoapval = new soapval('RegistroEmergenzaNumero', 'RegistroEmergenzaNumero', $dati['RegistroEmergenzaNumero'], false, false);
        $paramArrProt[] = $RegNumProtSoapval;

        $RegDataProtSoapval = new soapval('RegistroEmergenzaData', 'RegistroEmergenzaData', $dati['RegistroEmergenzaData'], false, false);
        $paramArrProt[] = $RegDataProtSoapval;

        $AnnoCopiaProtSoapval = new soapval('AnnoCopia', 'AnnoCopia', $dati['AnnoCopia'], false, false);
        $paramArrProt[] = $AnnoCopiaProtSoapval;

        $NumeroCopiaProtSoapval = new soapval('NumeroCopia', 'NumeroCopia', $dati['NumeroCopia'], false, false);
        $paramArrProt[] = $NumeroCopiaProtSoapval;

        $UtenteInsProtSoapval = new soapval('UtenteInserimento', 'UtenteInserimento', $dati['UtenteInserimento'], false, false);
        $paramArrProt[] = $UtenteInsProtSoapval;

        $StrProt = "<Protocollo>";
        foreach ($paramArrProt as $parametro) {
            $StrProt .= $parametro->serialize('literal');
        }
        $StrProt .= "</Protocollo>";

        /*
         * Documento
         */
        $IdProtSoapval = new soapval('Id', 'Id', $dati['docId'], false, false);
        $paramArrDoc[] = $IdProtSoapval;

        $DataProtSoapval = new soapval('Data', 'Data', $dati['Data'], false, false);
        $paramArrDoc[] = $DataProtSoapval;

        $TipoProtSoapval = new soapval('Tipo', 'Tipo', $dati['Tipo'], false, false);
        $paramArrDoc[] = $TipoProtSoapval;

        $ClassificaProtSoapval = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
        $paramArrDoc[] = $ClassificaProtSoapval;

        $MitIntProtSoapval = new soapval('MittenteInterno', 'MittenteInterno', $dati['MittenteInterno'], false, false);
        $paramArrDoc[] = $MitIntProtSoapval;

        $OggettoProtSoapval = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
        $paramArrDoc[] = $OggettoProtSoapval;

        $TpRicOggettoProtSoapval = new soapval('TipoRicercaOggetto', 'TipoRicercaOggetto', $dati['TipoRicercaOggetto'], false, false);
        $paramArrDoc[] = $TpRicOggettoProtSoapval;

        $OrigineProtSoapval = new soapval('Origine', 'Origine', $dati['Origine'], false, false);
        $paramArrDoc[] = $OrigineProtSoapval;

        $CaricoProtSoapval = new soapval('Carico', 'Carico', $dati['Carico'], false, false);
        $paramArrDoc[] = $CaricoProtSoapval;

        $AssegnazIntProtSoapval = new soapval('AssegnazioneInterna', 'AssegnazioneInterna', $dati['AssegnazioneInterna'], false, false);
        $paramArrDoc[] = $AssegnazIntProtSoapval;

        $DestIntProtSoapval = new soapval('DestinatarioInterno', 'DestinatarioInterno', $dati['DestinatarioInterno'], false, false);
        $paramArrDoc[] = $DestIntProtSoapval;

        $PartecipatiProtSoapval = new soapval('Partecipati', 'Partecipati', $dati['Partecipati'], false, false);
        $paramArrDoc[] = $PartecipatiProtSoapval;

        $UtInserimentoProtSoapval = new soapval('UtenteInserimento', 'UtenteInserimento', $dati['UtenteInserimento'], false, false);
        $paramArrDoc[] = $UtInserimentoProtSoapval;

        $RuoloInserimentoProtSoapval = new soapval('RuoloInserimento', 'RuoloInserimento', $dati['RuoloInserimento'], false, false);
        $paramArrDoc[] = $RuoloInserimentoProtSoapval;

        $DataInserimento = new soapval('DataInserimento', 'DataInserimento', $dati['DataInserimento'], false, false);
        $paramArrDoc[] = $DataInserimento;

        $DataEvidenza = new soapval('DataEvidenza', 'DataEvidenza', $dati['DataEvidenza'], false, false);
        $paramArrDoc[] = $DataEvidenza;

        $DataScarto = new soapval('DataScarto', 'DataScarto', $dati['DataScarto'], false, false);
        $paramArrDoc[] = $DataScarto;

        $DataSorteggio = new soapval('DataSorteggio', 'DataSorteggio', $dati['DataSorteggio'], false, false);
        $paramArrDoc[] = $DataSorteggio;

        $DataInvio = new soapval('DataInvio', 'DataInvio', $dati['DataInvio'], false, false);
        $paramArrDoc[] = $DataInvio;

        $Barcode = new soapval('Barcode', 'Barcode', $dati['Barcode'], false, false);
        $paramArrDoc[] = $Barcode;

        $Responsabile = new soapval('Responsabile', 'Responsabile', $dati['Responsabile'], false, false);
        $paramArrDoc[] = $Responsabile;

        $RuoloResponsabile = new soapval('RuoloResponsabile', 'RuoloResponsabile', $dati['RuoloResponsabile'], false, false);
        $paramArrDoc[] = $RuoloResponsabile;

        $MittenteDestinatario = new soapval('MittenteDestinatario', 'MittenteDestinatario', $dati['MittenteDestinatario'], false, false);
        $paramArrDoc[] = $MittenteDestinatario;


        $StrDoc = "<Documento>";
        foreach ($paramArrDoc as $parametro) {
            $StrDoc .= $parametro->serialize('literal');
        }
        $StrDoc .= "</Documento>";

        /*
         * Proposta
         */
//                <Organo></Organo>
//        <Trattamento></Trattamento>
//        <RegistroAnno></RegistroAnno>
//        <RegistroNumero></RegistroNumero>
//        <RegistroData></RegistroData>
//        <Relatore></Relatore>
//        <ImmediataEsecutivita></ImmediataEsecutivita>
//        <Capigruppo></Capigruppo>
//        <Prefetto></Prefetto>
//        <Coreco></Coreco>
        $organo = new soapval('Organo', 'Organo', $dati['Organo'], false, false);
        $paramArrProposta[] = $organo;

        $Trattamento = new soapval('Trattamento', 'Trattamento', $dati['Trattamento'], false, false);
        $paramArrProposta[] = $Trattamento;

        $RegistroAnno = new soapval('RegistroAnno', 'RegistroAnno', $dati['RegistroAnno'], false, false);
        $paramArrProposta[] = $RegistroAnno;

        $RegistroNumero = new soapval('RegistroNumero', 'RegistroNumero', $dati['RegistroNumero'], false, false);
        $paramArrProposta[] = $RegistroNumero;

        $RegistroData = new soapval('RegistroData', 'RegistroData', $dati['RegistroData'], false, false);
        $paramArrProposta[] = $RegistroData;

        $Relatore = new soapval('Relatore', 'Relatore', $dati['Relatore'], false, false);
        $paramArrProposta[] = $Relatore;

        $ImmediataEsecutivita = new soapval('ImmediataEsecutivita', 'ImmediataEsecutivita', $dati['ImmediataEsecutivita'], false, false);
        $paramArrProposta[] = $ImmediataEsecutivita;

        $Capigruppo = new soapval('Capigruppo', 'Capigruppo', $dati['Capigruppo'], false, false);
        $paramArrProposta[] = $Capigruppo;

        $Prefetto = new soapval('Prefetto', 'Prefetto', $dati['Prefetto'], false, false);
        $paramArrProposta[] = $Prefetto;

        $Coreco = new soapval('Coreco', 'Coreco', $dati['Coreco'], false, false);
        $paramArrProposta[] = $Coreco;

        $StrProposta = "<Proposta>";
        foreach ($paramArrProposta as $parametro) {
            $StrProposta .= $parametro->serialize('literal');
        }
        $StrProposta .= "</Proposta>";

        /*
         * Delibera
         */
//         <DeliberaAnno></DeliberaAnno>
//        <DeliberaNumero></DeliberaNumero>
//        <DeliberaData></DeliberaData>
//        <Organo></Organo>
//        <Trattamento></Trattamento>
//        <Relatore></Relatore>
//        <PubblicazioneData></PubblicazioneData>
//        <EsecutivitaData></EsecutivitaData>
//        <ImmediataEsecutivita></ImmediataEsecutivita>
//        <Capigruppo></Capigruppo>
//        <Prefetto></Prefetto>
//        <Coreco></Coreco>
        $DeliberaAnno = new soapval('DeliberaAnno', 'DeliberaAnno', $dati['DeliberaAnno'], false, false);
        $paramArrDelibera[] = $DeliberaAnno;

        $DeliberaNumero = new soapval('DeliberaNumero', 'DeliberaNumero', $dati['DeliberaNumero'], false, false);
        $paramArrDelibera[] = $DeliberaNumero;

        $DeliberaData = new soapval('DeliberaData', 'DeliberaData', $dati['DeliberaData'], false, false);
        $paramArrDelibera[] = $DeliberaData;


        $organo = new soapval('Organo', 'Organo', $dati['Organo'], false, false);
        $paramArrDelibera[] = $organo;

        $Trattamento = new soapval('Trattamento', 'Trattamento', $dati['Trattamento'], false, false);
        $paramArrDelibera[] = $Trattamento;

        $Relatore = new soapval('Relatore', 'Relatore', $dati['Relatore'], false, false);
        $paramArrDelibera[] = $Relatore;

        $PubblicazioneData = new soapval('PubblicazioneData', 'PubblicazioneData', $dati['PubblicazioneData'], false, false);
        $paramArrDelibera[] = $PubblicazioneData;

        $EsecutivitaData = new soapval('EsecutivitaData', 'EsecutivitaData', $dati['EsecutivitaData'], false, false);
        $paramArrDelibera[] = $EsecutivitaData;

        $ImmediataEsecutivita = new soapval('ImmediataEsecutivita', 'ImmediataEsecutivita', $dati['ImmediataEsecutivita'], false, false);
        $paramArrDelibera[] = $ImmediataEsecutivita;

        $Capigruppo = new soapval('Capigruppo', 'Capigruppo', $dati['Capigruppo'], false, false);
        $paramArrDelibera[] = $Capigruppo;

        $Prefetto = new soapval('Prefetto', 'Prefetto', $dati['Prefetto'], false, false);
        $paramArrDelibera[] = $Prefetto;

        $Coreco = new soapval('Coreco', 'Coreco', $dati['Coreco'], false, false);
        $paramArrDelibera[] = $Coreco;

        $StrDelibera = "<Delibera>";
        foreach ($paramArrDelibera as $parametro) {
            $StrDelibera .= $parametro->serialize('literal');
        }
        $StrDelibera .= "</Delibera>";

        /*
         * Determina
         */
//        <DeterminaAnno></DeterminaAnno>
//        <DeterminaNumero></DeterminaNumero>
//        <DeterminaData></DeterminaData>
//        <Trattamento></Trattamento>
//        <Dirigente></Dirigente>
//        <PubblicazioneData></PubblicazioneData>
//        <EsecutivitaData></EsecutivitaData>
        $DeterminaAnno = new soapval('DeterminaAnno', 'DeterminaAnno', $dati['DeterminaAnno'], false, false);
        $paramArrDetermina[] = $DeterminaAnno;

        $DeterminaNumero = new soapval('DeterminaNumero', 'DeterminaNumero', $dati['DeterminaNumero'], false, false);
        $paramArrDetermina[] = $DeterminaNumero;


        $DeterminaData = new soapval('DeterminaData', 'DeterminaData', $dati['DeterminaData'], false, false);
        $paramArrDetermina[] = $DeterminaData;

        $Trattamento = new soapval('Trattamento', 'Trattamento', $dati['Trattamento'], false, false);
        $paramArrDetermina[] = $Trattamento;

        $Dirigente = new soapval('Dirigente', 'Dirigente', $dati['Dirigente'], false, false);
        $paramArrDetermina[] = $Dirigente;

        $PubblicazioneData = new soapval('PubblicazioneData', 'PubblicazioneData', $dati['PubblicazioneData'], false, false);
        $paramArrDetermina[] = $PubblicazioneData;

        $EsecutivitaData = new soapval('EsecutivitaData', 'EsecutivitaData', $dati['EsecutivitaData'], false, false);
        $paramArrDetermina[] = $EsecutivitaData;

        $StrDetermina = "<Determina>";
        foreach ($paramArrDetermina as $parametro) {
            $StrDetermina .= $parametro->serialize('literal');
        }
        $StrDetermina .= "</Determina>";

        /*
         * Impegno
         */
//        <ImpegnoNumero></ImpegnoNumero>
//        <ImpegnoImporto></ImpegnoImporto>
//        <ImpegniTotale></ImpegniTotale>
        $ImpegnoNumero = new soapval('ImpegnoNumero', 'ImpegnoNumero', $dati['EsecutivitaData'], false, false);
        $paramArrImpegno[] = $ImpegnoNumero;

        $ImpegnoImporto = new soapval('ImpegnoImporto', 'ImpegnoImporto', $dati['ImpegnoImporto'], false, false);
        $paramArrImpegno[] = $ImpegnoImporto;

        $ImpegniTotale = new soapval('ImpegniTotale', 'ImpegniTotale', $dati['ImpegniTotale'], false, false);
        $paramArrImpegno[] = $ImpegniTotale;

        $StrImpegno = "<Impegno>";
        foreach ($paramArrImpegno as $parametro) {
            $StrImpegno .= $parametro->serialize('literal');
        }
        $StrImpegno .= "</Impegno>";

        /*
         * Accertamento
         */
//        <AccertamentoNumero></AccertamentoNumero>
//        <AccertamentoImporto></AccertamentoImporto>
//        <ImpegniTotale></ImpegniTotale>
        $AccertamentoNumero = new soapval('AccertamentoNumero', 'AccertamentoNumero', $dati['AccertamentoNumero'], false, false);
        $paramArrAccertamento[] = $AccertamentoNumero;

        $AccertamentoImporto = new soapval('AccertamentoImporto', 'AccertamentoImporto', $dati['AccertamentoImporto'], false, false);
        $paramArrAccertamento[] = $AccertamentoImporto;

        $ImpegniTotale = new soapval('ImpegniTotale', 'ImpegniTotale', $dati['ImpegniTotale'], false, false);
        $paramArrAccertamento[] = $ImpegniTotale;

        $StrAccertamento = "<Accertamento>";
        foreach ($paramArrAccertamento as $parametro) {
            $StrAccertamento .= $parametro->serialize('literal');
        }
        $StrAccertamento .= "</Accertamento>";

        /*
         * Fascicolo
         */
        $NonFascicolato = new soapval('NonFascicolato', 'NonFascicolato', $dati['NonFascicolato'], false, false);
        $paramArrFas[] = $NonFascicolato;

        $Anno = new soapval('Anno', 'Anno', $dati['AnnoFasc'], false, false);
        $paramArrFas[] = $Anno;

        $NumeroFas = new soapval('Numero', 'Numero', $dati['NumeroFasc'], false, false);
        $paramArrFas[] = $NumeroFas;

        $Id = new soapval('Id', 'Id', $dati['Id'], false, false);
        $paramArrFas[] = $Id;

        $Oggetto = new soapval('Oggetto', 'Oggetto', $dati['Oggetto'], false, false);
        $paramArrFas[] = $Oggetto;

        $TipoRicercaOggetto = new soapval('TipoRicercaOggetto', 'TipoRicercaOggetto', $dati['TipoRicercaOggetto'], false, false);
        $paramArrFas[] = $TipoRicercaOggetto;

        $Classifica = new soapval('Classifica', 'Classifica', $dati['Classifica'], false, false);
        $paramArrFas[] = $Classifica;

        $AltriDati = new soapval('AltriDati', 'AltriDati', $dati['AltriDati'], false, false);
        $paramArrFas[] = $AltriDati;

        $DataChiusura = new soapval('DataChiusura', 'DataChiusura', $dati['DataChiusura'], false, false);
        $paramArrFas[] = $DataChiusura;

        $UtenteInserimento = new soapval('UtenteInserimento', 'UtenteInserimento', $dati['UtenteInserimento'], false, false);
        $paramArrFas[] = $UtenteInserimento;

        $RuoloInserimento = new soapval('RuoloInserimento', 'RuoloInserimento', $dati['RuoloInserimento'], false, false);
        $paramArrFas[] = $RuoloInserimento;

        $Responsabile = new soapval('Responsabile', 'Responsabile', $dati['Responsabile'], false, false);
        $paramArrFas[] = $Responsabile;

        $RuoloResponsabile = new soapval('RuoloResponsabile', 'RuoloResponsabile', $dati['RuoloResponsabile'], false, false);
        $paramArrFas[] = $RuoloResponsabile;


        $StrFas = "<Fascicolo>";
        foreach ($paramArrFas as $parametro) {
            $StrFas .= $parametro->serialize('literal');
        }
        $StrFas .= "</Fascicolo>";


        /*
         * Procedimento
         */
        $CodiceFiscaleMittente = new soapval('CodiceFiscaleMittente', 'CodiceFiscaleMittente', $dati['CodiceFiscaleMittente'], false, false);
        $paramArrProc[] = $CodiceFiscaleMittente;

        $StatoProcedimento = new soapval('StatoProcedimento', 'StatoProcedimento', $dati['StatoProcedimento'], false, false);
        $paramArrProc[] = $StatoProcedimento;

        $IdIter = new soapval('IdIter', 'IdIter', $dati['IdIter'], false, false);
        $paramArrProc[] = $IdIter;

        $StatoDocumento = new soapval('StatoDocumento', 'StatoDocumento', $dati['StatoDocumento'], false, false);
        $paramArrProc[] = $StatoDocumento;

        $InviatoDa = new soapval('InviatoDa', 'InviatoDa', $dati['InviatoDa'], false, false);
        $paramArrProc[] = $InviatoDa;

        $DataInvio = new soapval('DataInvio', 'DataInvio', $dati['DataInvio'], false, false);
        $paramArrProc[] = $DataInvio;

        $Responsabile = new soapval('Responsabile', 'Responsabile', $dati['Responsabile'], false, false);
        $paramArrProc[] = $Responsabile;

        $Assegnazioni = new soapval('Assegnazioni', 'Assegnazioni', $dati['Assegnazioni'], false, false);
        $paramArrProc[] = $Assegnazioni;

        $CodicePasso = new soapval('CodicePasso', 'CodicePasso', $dati['CodicePasso'], false, false);
        $paramArrProc[] = $CodicePasso;



        $StrProc = "<Procedimento>";
        foreach ($paramArrProc as $parametro) {
            $StrProc .= $parametro->serialize('literal');
        }
        $StrProc .= "</Procedimento>";

        /*
         * Dati Utente
         */
//        <DatoUtente>
//            <NomeTabella></NomeTabella>
//            <NomeCampo></NomeCampo>
//            <ValoreCampo></ValoreCampo>
//        </DatoUtente>
//        <DatoUtente>
//            <NomeTabella></NomeTabella>
//            <NomeCampo></NomeCampo>
//            <ValoreCampo></ValoreCampo>
//        </DatoUtente>
//        <DatoUtente>
//            <NomeTabella></NomeTabella>
//            <NomeCampo></NomeCampo>
//            <ValoreCampo></ValoreCampo>
//        </DatoUtente>
        $DatiUtentiSoapvalArr = array();
        foreach ($dati['datiUtenti'] as $key => $utente) {
            $utenteSoapvalArr = array();
            if (isset($utente['NomeTabella'])) {
                $utenteSoapvalArr[] = new soapval('NomeTabella', 'NomeTabella', $utente['NomeTabella'], false, false);
            }
            if (isset($utente['NomeCampo'])) {
                $utenteSoapvalArr[] = new soapval('NomeCampo', 'NomeCampo', $utente['NomeCampo'], false, false);
            }
            if (isset($utente['ValoreCampo'])) {
                $utenteSoapvalArr[] = new soapval('ValoreCampo', 'ValoreCampo', $utente['ValoreCampo'], false, false);
            }
            //$DatiUtentiSoapvalArr[] = new soapval('DatoUtente', 'DatoUtente', $utenteSoapvalArr, false, false);
            $DatiUtentiSoapvalArr[] = $utenteSoapvalArr;
        }


        $StrDatiUtente = "<DatiUtente>";
        foreach ($DatiUtentiSoapvalArr as $utente) {
            $StrDatiUtente .= "<DatoUtente>";
            foreach ($utente as $parametro) {
                $StrDatiUtente .= $parametro->serialize('literal');
            }
            $StrDatiUtente .= "</DatoUtente>";
        }
        $StrDatiUtente .= "</DatiUtente>";


        /*
         * Piano di Lavoro
         */
//        <IdPDL></IdPDL>
//        <MacroAttivita></MacroAttivita>
//        <Attivita></Attivita>
//        <StatoAttivita></StatoAttivita>
//        <Responsabile></Responsabile>
//        <DataScadenza></DataScadenza>
//        <DataInizioAttivita></DataInizioAttivita>
//        <DataFineAttivita></DataFineAttivita>
        $IdPDL = new soapval('IdPDL', 'IdPDL', $dati['IdPDL'], false, false);
        $paramArrPianoLavoro[] = $IdPDL;

        $MacroAttivita = new soapval('MacroAttivita', 'MacroAttivita', $dati['MacroAttivita'], false, false);
        $paramArrPianoLavoro[] = $MacroAttivita;

        $Attivita = new soapval('Attivita', 'Attivita', $dati['Attivita'], false, false);
        $paramArrPianoLavoro[] = $Attivita;

        $StatoAttivita = new soapval('StatoAttivita', 'StatoAttivita', $dati['StatoAttivita'], false, false);
        $paramArrPianoLavoro[] = $StatoAttivita;

        $Responsabile = new soapval('Responsabile', 'Responsabile', $dati['Responsabile'], false, false);
        $paramArrPianoLavoro[] = $Responsabile;

        $DataScadenza = new soapval('DataScadenza', 'DataScadenza', $dati['DataScadenza'], false, false);
        $paramArrPianoLavoro[] = $DataScadenza;

        $DataInizioAttivita = new soapval('DataInizioAttivita', 'DataInizioAttivita', $dati['DataInizioAttivita'], false, false);
        $paramArrPianoLavoro[] = $DataInizioAttivita;

        $DataFineAttivita = new soapval('DataFineAttivita', 'DataFineAttivita', $dati['DataFineAttivita'], false, false);
        $paramArrPianoLavoro[] = $DataFineAttivita;

        $StrPianoLavoro = "<PianoDiLavoro>";
        foreach ($paramArrPianoLavoro as $parametro) {
            $StrPianoLavoro .= $parametro->serialize('literal');
        }
        $StrPianoLavoro .= "</PianoDiLavoro>";





        $UtenteSoapval = new soapval('Utente', 'Utente', $this->utente, false, false);
        $paramArr[] = $UtenteSoapval;

        $RuoloSoapval = new soapval('Ruolo', 'Ruolo', $this->ruolo, false, false);
        $paramArr[] = $RuoloSoapval;







        //CREO CDATA
        $ProtocolloInStr = "<tem:RicercaFiltriStr><![CDATA[<RicercaFiltri>" . $StrProt . $StrDoc . $StrProposta . $StrDelibera . $StrDetermina . $StrImpegno . $StrAccertamento . $StrFas . $StrProc . $StrDatiUtente . $StrPianoLavoro;
        foreach ($paramArr as $parametro) {
            $ProtocolloInStr .= $parametro->serialize('literal');
        }
        $ProtocolloInStr .= "</RicercaFiltri>]]></tem:RicercaFiltriStr>";
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
        return $this->ws_call('RicercaDocumentiString', $param);
    }

}

?>
