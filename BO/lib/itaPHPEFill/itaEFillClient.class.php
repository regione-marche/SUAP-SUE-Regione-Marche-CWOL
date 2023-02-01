<?php

/**
 *
 * Classe per collegamento ws efill (pagopa)
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPEFill
 * @author     Luca Cardinali <l.cardinali@apra.it>
 * @version    08.03.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

class itaEFillClient {

    const PAGAMENTO_SPONTANEO_ZZ = 'AvviaTransazionePagamentoSpontaneo';
    const PAGAMENTO_PREDETERMINATO_ZZ = 'AvviaTransazionePagamentoPredeterminato';
    const CARICA_DELEGHE_ZZ = 'CaricaDeleghe';
    const APERTURA_SESSIONE_ZZ = 'AperturaSessione';
    const CHIUSURA_SESSIONE_ZZ = 'ChiusuraSessione';
    const INVIA_CARRELLO = 'InviaCarrelloPosizioni';
    const RECUPERA_DELEGA = 'RecuperaDatiDelega';
    const RICEVI_RICEVUTA = 'DownloadDatiOriginaliRicevuta';
    const RICEVI_RICEVUTA_ARRICCHITA = 'DownloadDatiRicevuta';
    const CARICA_POSIZIONE = 'CaricaPosizione';
    const CARICA_POSIZIONI = 'CaricaPosizioni';
    const VALIDA_POSIZIONI = 'ValidaPosizioni';
    const RETTIFICA_POSIZIONE = 'RettificaPosizione';
    const GENERA_RT = 'CreatePdfRT';
    const GENERA_AVVISO = 'CreatePdfAvviso';
    const RICERCA_POSIZIONE_IUV = 'RicercaPosizionePerIdentificavo';
    const RICERCA_POSIZIONE_CF = 'RicercaPosizioniPerCodiceFiscalePartitaIva';
    const RIMUOVI_POSIZIONE = 'RimuoviPosizione';
    const WS_TIMEOUT_DEFAULT = 2400;

    private $namespaces = array();
    private $namespacePrefix = "ent"; // prefisso del namespace
    private $webservices_uri = "";
    private $soapActionPrefix = "";
    private $customNamespacePrefix = array(); // se ci sono dei campi che non hanno $namespacePrefix ma ne hanno 
    //uno custom, va passato in questo array (key=nomeCampo su $params, value= prefisso)
    private $timeout;
    private $result;
    private $error;
    private $request;
    private $response;

    /**
     * Metodo efill zz AperturaSessione, login
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function aperturaSessioneFeedZZ($params) {
        return $this->eseguiOperazione(self::APERTURA_SESSIONE_ZZ, $params);
    }

    /**
     * Metodo efill zz ChiusuraSessione, logout
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function chiusuraSessioneFeedZZ($params) {
        return $this->eseguiOperazione(self::CHIUSURA_SESSIONE_ZZ, $params);
    }

    /**
     * Metodo efill zz caricaDeleghe, permette di eseguire 
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function caricaDelegheZZ($params) {
        // genero xml a mano perche nusoap non risolve bene gli array di array (deleghe)
        $xml = '<zer:request>';
        $xml .= '<zer:IDApplicazione>' . $params['IDApplicazione'] . '</zer:IDApplicazione>';
        $xml .= '<zer:IDPuntoVendita>' . $params['IDPuntoVendita'] . '</zer:IDPuntoVendita>';
        $xml .= '<zer:IDSportello>' . $params['IDSportello'] . '</zer:IDSportello>';
        $xml .= '<zer:TokenIdentificativo>' . $params['TokenIdentificativo'] . '</zer:TokenIdentificativo>';
        $xml .= '<zer:DelegheF24>';
        if ($params['DelegheF24']) {
            foreach ($params['DelegheF24'] as $delega) {
                $xml .= '<car:DelegaF24>';
                $xml .= '<car:CodiceIdentificativo>' . $delega['CodiceIdentificativo'] . '</car:CodiceIdentificativo>';
                $xml .= '<car:Contribuente>';
                $xml .= '<car:CodiceFiscalePartitaIva>' . $delega['Contribuente']['CodiceFiscalePartitaIva'] . '</car:CodiceFiscalePartitaIva>';
                $xml .= '<car:CognomeDenominazione><![CDATA[' . $delega['Contribuente']['CognomeDenominazione'] . ']]></car:CognomeDenominazione>';
                if ($delega['Contribuente']['ComuneOStatoEsteroDiNascita']) {
                    $xml .= '<car:ComuneOStatoEsteroDiNascita>' . $delega['Contribuente']['ComuneOStatoEsteroDiNascita'] . '</car:ComuneOStatoEsteroDiNascita>';
                }
                if ($delega['Contribuente']['DataNascita']) {
                    $xml .= '<car:DataNascita>' . $delega['Contribuente']['DataNascita'] . '</car:DataNascita>';
                }
                $xml .= '<car:Email>' . $delega['Contribuente']['Email'] . '</car:Email>';
                $xml .= '<car:Nome>' . $delega['Contribuente']['Nome'] . '</car:Nome>';
                $xml .= '<car:ProvinciaDiNascita>' . $delega['Contribuente']['ProvinciaDiNascita'] . '</car:ProvinciaDiNascita>';
                $xml .= '<car:Sesso>' . $delega['Contribuente']['Sesso'] . '</car:Sesso>';
                $xml .= '</car:Contribuente>';
                $xml .= '<car:DataScadenza>' . $delega['DataScadenza'] . '</car:DataScadenza>';
                $xml .= '<car:IdentificativoOperazione>' . $delega['IdentificativoOperazione'] . '</car:IdentificativoOperazione>';
                $xml .= '<car:ImportoDelegaInCentesimi>' . $delega['ImportoDelegaInCentesimi'] . '</car:ImportoDelegaInCentesimi>';
                $xml .= '<car:RigaDelegaF24>';
                if ($delega['RigaDelegaF24']) {
                    foreach ($delega['RigaDelegaF24'] as $delegaRiga) {
                        $xml .= '<car:RigaDelegaF24>';
                        $xml .= '<car:Acconto>' . $delegaRiga['Acconto'] . '</car:Acconto>';
                        $xml .= '<car:Anno>' . $delegaRiga['Anno'] . '</car:Anno>';
                        $xml .= '<car:CodiceEnte>' . $delegaRiga['CodiceEnte'] . '</car:CodiceEnte>';
                        $xml .= '<car:CodiceTributo>' . $delegaRiga['CodiceTributo'] . '</car:CodiceTributo>';
                        $xml .= '<car:ImportoDetrazioneInCentesimi>' . $delegaRiga['ImportoDetrazioneInCentesimi'] . '</car:ImportoDetrazioneInCentesimi>';
                        $xml .= '<car:ImportoRigaACreditoInCentesimi>' . $delegaRiga['ImportoRigaACreditoInCentesimi'] . '</car:ImportoRigaACreditoInCentesimi>';
                        $xml .= '<car:ImportoRigaADebitoInCentesimi>' . $delegaRiga['ImportoRigaADebitoInCentesimi'] . '</car:ImportoRigaADebitoInCentesimi>';
                        $xml .= '<car:NumeroImmobili>' . $delegaRiga['NumeroImmobili'] . '</car:NumeroImmobili>';
                        $xml .= '<car:NumeroImmobiliVariati>' . $delegaRiga['ImmobiliVariati'] . '</car:NumeroImmobiliVariati>';
                        $xml .= '<car:Rateazione>' . $delegaRiga['Rateazione'] . '</car:Rateazione>';
                        $xml .= '<car:Ravvedimento>' . $delegaRiga['Ravvedimento'] . '</car:Ravvedimento>';
                        $xml .= '<car:Saldo>' . $delegaRiga['Saldo'] . '</car:Saldo>';
                        $xml .= '<car:Sezione>' . $delegaRiga['Sezione'] . '</car:Sezione>';
                        $xml .= '</car:RigaDelegaF24>';
                    }
                }
                $xml .= '</car:RigaDelegaF24>';
                $xml .= '</car:DelegaF24>';
            }
        }
        $xml .= '</zer:DelegheF24>';
        $xml .= '</zer:request>';

        return $this->eseguiOperazione(self::CARICA_DELEGHE_ZZ, $xml);
    }

    /**
     * Metodo efill zz AvviaTransazionePagamentoSpontaneo, permette di eseguire un pagamento spontaneo
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function eseguiPagamentoSpontaneoZZ($params) {
        // genero xml a mano perche nusoap non risolve bene gli array di array (deleghe)
        $xml = '<e:avviaTransazionePagamentoSpontaneoRequest>';
        $xml .= '<efil:CodiceContratto>' . $params['avviaTransazionePagamentoSpontaneoRequest']['CodiceContratto'] . '</efil:CodiceContratto>';
        $xml .= '<efil:Delega>';
        $xml .= '<efil1:Contribuente>';
        $xml .= '<efil1:Cellulare>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Cellulare'] . '</efil1:Cellulare>';
        $xml .= '<efil1:CodiceFiscalePartitaIva>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['CodiceFiscalePartitaIva'] . '</efil1:CodiceFiscalePartitaIva>';
        $xml .= '<efil1:CognomeDenominazione>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['CognomeDenominazione'] . '</efil1:CognomeDenominazione>';
        $xml .= '<efil1:ComuneOStatoEsteroDiNascita>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['ComuneOStatoEsteroDiNascita'] . '</efil1:ComuneOStatoEsteroDiNascita>';
        $xml .= '<efil1:DataNascita>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['DataNascita'] . '</efil1:DataNascita>';
        $xml .= '<efil1:Email>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Email'] . '</efil1:Email>';
        $xml .= '<efil1:Indirizzo>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Indirizzo'] . '</efil1:Indirizzo>';
        $xml .= '<efil1:Nome>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Nome'] . '</efil1:Nome>';
        $xml .= '<efil1:ProvinciaDiNascita>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['ProvinciaDiNascita'] . '</efil1:ProvinciaDiNascita>';
        $xml .= '<efil1:Sesso>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Sesso'] . '</efil1:Sesso>';
        $xml .= '</efil1:Contribuente>';
        $xml .= '<efil1:DataScadenza>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['DataScadenza'] . '</efil1:DataScadenza>';
        $xml .= '<efil1:Descrizione>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Descrizione'] . '</efil1:Descrizione>';
        $xml .= '<efil1:Fornitore>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Fornitore'] . '</efil1:Fornitore>';
        $xml .= '<efil1:IdentificativoDelegaDicontratto>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['IdentificativoDelegaDicontratto'] . '</efil1:IdentificativoDelegaDicontratto>';
        $xml .= '<efil1:IdentificativoOperazione>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['IdentificativoOperazione'] . '</efil1:IdentificativoOperazione>';
        $xml .= '<efil1:ImportoInCentesimi>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['ImportoInCentesimi'] . '</efil1:ImportoInCentesimi>';
        $xml .= '<efil1:RigheDelegaF24>';
        if ($params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24']) {
            foreach ($params['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'] as $delega) {
                $xml .= '<efil1:RigaDelegaF24>';
                $xml .= '<efil1:Acconto>' . $delega['Acconto'] . '</efil1:Acconto>';
                $xml .= '<efil1:AnnoDiRiferimento>' . $delega['AnnoDiRiferimento'] . '</efil1:AnnoDiRiferimento>';
                $xml .= '<efil1:CodiceEnte>' . $delega['CodiceEnte'] . '</efil1:CodiceEnte>';
                $xml .= '<efil1:CodiceTributo>' . $delega['CodiceTributo'] . '</efil1:CodiceTributo>';
                $xml .= '<efil1:ImmobiliVariati>' . $delega['ImmobiliVariati'] . '</efil1:ImmobiliVariati>';
                $xml .= '<efil1:ImportoDetrazioneInCentesimi>' . $delega['ImportoDetrazioneInCentesimi'] . '</efil1:ImportoDetrazioneInCentesimi>';
                $xml .= '<efil1:ImportoRigaACreditoInCentesimi>' . $delega['ImportoRigaACreditoInCentesimi'] . '</efil1:ImportoRigaACreditoInCentesimi>';
                $xml .= '<efil1:ImportoRigaADebitoInCentesimi>' . $delega['ImportoRigaADebitoInCentesimi'] . '</efil1:ImportoRigaADebitoInCentesimi>';
                $xml .= '<efil1:NumeroImmobili>' . $delega['NumeroImmobili'] . '</efil1:NumeroImmobili>';
                $xml .= '<efil1:Rateazione>' . $delega['Rateazione'] . '</efil1:Rateazione>';
                $xml .= '<efil1:Ravvedimento>' . $delega['Ravvedimento'] . '</efil1:Ravvedimento>';
                $xml .= '<efil1:Saldo>' . $delega['Saldo'] . '</efil1:Saldo>';
                $xml .= '<efil1:Sezione>' . $delega['Sezione'] . '</efil1:Sezione>';
                $xml .= '</efil1:RigaDelegaF24>';
            }
        }
        $xml .= '</efil1:RigheDelegaF24>';
        $xml .= '</efil:Delega>';
        $xml .= '<efil:IdentificativoChiamante>' . $params['avviaTransazionePagamentoSpontaneoRequest']['IdentificativoChiamante'] . '</efil:IdentificativoChiamante>';
        $xml .= '<efil:Password>' . $params['avviaTransazionePagamentoSpontaneoRequest']['Password'] . '</efil:Password>';
        $xml .= '<efil:UrlAnnullo><![CDATA[' . $params['avviaTransazionePagamentoSpontaneoRequest']['UrlAnnullo'] . ']]></efil:UrlAnnullo>';
        $xml .= '<efil:UrlKo><![CDATA[' . $params['avviaTransazionePagamentoSpontaneoRequest']['UrlKo'] . ']]></efil:UrlKo>';
        $xml .= '<efil:UrlOk><![CDATA[' . $params['avviaTransazionePagamentoSpontaneoRequest']['UrlOk'] . ']]></efil:UrlOk>';
        $xml .= '</e:avviaTransazionePagamentoSpontaneoRequest>';

        return $this->eseguiOperazione(self::PAGAMENTO_SPONTANEO_ZZ, $xml);
    }

    /**
     * Metodo efill zz AvviaTransazionePagamentoPredeterminato, permette di eseguire un pagamento di una delega caricata
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function eseguiPagamentoPredeterminatoZZ($params) {
        return $this->eseguiOperazione(self::PAGAMENTO_PREDETERMINATO_ZZ, $params);
    }

    /**
     * Metodo efill InviaCarrelloPosizioni, permette di eseguire un pagamento 
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function eseguiPagamento($params) {
        return $this->eseguiOperazione(self::INVIA_CARRELLO, $params);
    }

    /**
     * Metodo efill RecuperaDatiDelega, permette cercare una delega
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl
     * 
     * @return string
     */
    public function recuperaDatiDelega($params) {
        return $this->eseguiOperazione(self::RECUPERA_DELEGA, $params);
    }

    /**
     * Metodo efill DownloadDatiOriginaliRicevuta, permette di recuperare la ricevuta di un pagamento
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl      
     * 
     * @return string
     */
    public function recuperaRicevutaPagamento($params) {
        return $this->eseguiOperazione(self::RICEVI_RICEVUTA, $params);
    }

    /**
     * Metodo efill DownloadDatiRicevuta, permette di recuperare la ricevuta di un pagamento arricchita
     * 
     * @param array $params deve contenere un array con i parametri richiesti dal wsdl      
     * 
     * @return string
     */
    public function recuperaRicevutaPagamentoArricchita($params) {
        return $this->eseguiOperazione(self::RICEVI_RICEVUTA_ARRICCHITA, $params);
    }

    public function caricaPosizione($params) {
        // genero xml a mano perch? nusoap non risolve bene gli array di array (accertamenti)
        $posizione = $params['request']['Posizione'];
        $creditore = $posizione['Creditore'];
        $debitore = $posizione['Debitore'];
        $accertamenti = $posizione['Accertamenti'];

        $xml = '<plug:request>'
                . '<plug:IdApplicazione>' . $params["request"]["IdApplicazione"] . '</plug:IdApplicazione>'
                . '<plug:Posizione>';
        $xml .= '<plug:Accertamenti>';
        if ($accertamenti) {
            foreach ($accertamenti as $value) {
                $xml .= '<plug:Accertamento>'
                        . '<plug:Codice>' . $value['Accertamento']['Codice'] . '</plug:Codice>'
                        . '<plug:ImportoInCentesimi>' . $value['Accertamento']['ImportoInCentesimi'] . '</plug:ImportoInCentesimi>'
                        . '</plug:Accertamento>';
            }
        }
        $xml .= '</plug:Accertamenti>';
        $xml .= "<plug:Causale><![CDATA[" . $posizione["Causale"] . "]]></plug:Causale>"
                . '<plug:CodiceRiferimentoCreditore>' . $posizione["CodiceRiferimentoCreditore"] . '</plug:CodiceRiferimentoCreditore>'
                . '<plug:Creditore>'
                . '<plug:CodiceEnte>' . $creditore[CodiceEnte] . '</plug:CodiceEnte>'
                . '<plug:Intestazione>' . $creditore[Intestazione] . '</plug:Intestazione>'
                . '</plug:Creditore>'
                . '<plug:DataScadenza>' . $posizione["DataScadenza"] . '</plug:DataScadenza>'
                . '<plug:Debitore>'
                . '<plug:CodiceFiscalePartitaIva>' . $debitore["CodiceFiscalePartitaIva"] . '</plug:CodiceFiscalePartitaIva>'
                . '<plug:Nazione>'
                . '<plug:NomeNazione>' . $debitore["Nazione"]["NomeNazione"] . '</plug:NomeNazione>'
                . '</plug:Nazione>'
                . "<plug:Nominativo><![CDATA[" . substr($debitore['Nominativo'], 0, 50) . "]]></plug:Nominativo>"
                . '<plug:TipoPagatore>' . $debitore["TipoPagatore"] . '</plug:TipoPagatore>'
                . '</plug:Debitore>'
                . '<plug:ImportoInCentesimi>' . $posizione["ImportoInCentesimi"] . '</plug:ImportoInCentesimi>'
                . '<plug:ParametriPosizione/>'
                . '<plug:Servizio>'
                . '<plug1:CodiceServizio>' . $posizione["Servizio"]["CodiceServizio"] . '</plug1:CodiceServizio>'
                . '<plug1:Descrizione>' . $posizione["Servizio"]["Descrizione"] . '</plug1:Descrizione>'
                . '</plug:Servizio>'
                . '<plug:TipoRiferimentoCreditore>' . $posizione["TipoRiferimentoCreditore"] . '</plug:TipoRiferimentoCreditore>';

        $xml .= '</plug:Posizione></plug:request>';

        return $this->eseguiOperazione(self::CARICA_POSIZIONE, $xml, true);
    }

    public function validaPosizioni($params) {
        return $this->eseguiOperazione(self::VALIDA_POSIZIONI, $params);
    }

    public function rettificaPosizione($params) {
        return $this->eseguiOperazione(self::RETTIFICA_POSIZIONE, $params);
    }

    public function generaBollettino($params) {
        return $this->eseguiOperazione(self::GENERA_AVVISO, $params);
    }

    public function ricercaPosizioneIUV($params) {
        return $this->eseguiOperazione(self::RICERCA_POSIZIONE_IUV, $params);
    }

    public function ricercaPosizioneCFPI($params) {
        return $this->eseguiOperazione(self::RICERCA_POSIZIONE_CF, $params);
    }

    public function rimuoviPosizione($params) {
        return $this->eseguiOperazione(self::RIMUOVI_POSIZIONE, $params);
    }

//
//    public function generaPdfRT($params) {
//        return $this->eseguiOperazione(self::GENERA_RT, $params);
//    }

    private function eseguiOperazione($nomeMetodo, $params) {
        $this->clearResult();

        if (is_array($params)) {
            // se params è array, aggiungo i prefissi e lo passo a nusoap, 
            // altrimenti se è già xml non devo aggiugnere i prefissi che ci sono già e passo a nusoap la stringa
            $params = $this->aggiungiPrefisso($params);

            if (!$params) {
                $this->handleError("Inserire i parametri");
                return false;
            }
        }

        if (!$this->getWebservices_uri()) {
            $this->handleError("Inserire l'endpoint");
            return false;
        }

        return $this->ws_call($nomeMetodo, $params);
    }

    private function ws_call($operationName, $params, $headers = null) {
        $this->setRequest(null);
        $this->setResponse(null);
        $client = new nusoap_client($this->webservices_uri, false);
        $client->debugLevel = 0;
        //setting timeout
        $client->timeout = $this->timeout > 0 ? $this->timeout : self::WS_TIMEOUT_DEFAULT;
        $client->response_timeout = $client->timeout;
        //setting headers
        $client->setHeaders($headers);
        $client->soap_defencoding = 'UTF-8';
        $soapAction = $this->getSoapActionPrefix() . $operationName;
        $result = $client->call(($this->getNamespacePrefix() . ':' . $operationName), $params, $this->getNamespaces(), $soapAction, false, null, 'rpc', 'literal');
        $this->setRequest($client->request);
        $this->setResponse($client->response);
        if ($client->fault) {
            $this->handleError("Errore: " . $client->faultstring["!"]);
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->handleError("Errore: " . $err);
                return false;
            }
        }

        $this->result = $result;
        return true;
    }

    private function aggiungiPrefisso($params) {
        $toReturn = $this->aggiungiPrefissoRicorsivo($params);

        return $toReturn;
    }

    private function aggiungiPrefissoRicorsivo($params) {
        $toReturn = array();
        foreach ($params as $key => $value) {
            $prefixKey = $this->getNamespacePrefix() . ':' . $key;
            $customPrefix = $this->getCustomNamespacePrefix();
            if (array_key_exists($key, $customPrefix)) {
                // alcuni tag possono avere un prefix diverso, in questo caso vanno dichiarati su getCustomNamespacePrefix
                $prefixKey = $customPrefix[$key] . ':' . $key;
            }

            if (is_array($value)) {
                $toReturn[$prefixKey] = $this->aggiungiPrefissoRicorsivo($value);
            } else {
                $toReturn[$prefixKey] = $value;
            }
        }

        return $toReturn;
    }

    function getNamespacePrefix() {
        return $this->namespacePrefix;
    }

    function getWebservices_uri() {
        return $this->webservices_uri;
    }

    function getTimeout() {
        return $this->timeout;
    }

    function getResult() {
        return $this->result;
    }

    function getError() {
        return $this->error;
    }

    function setNamespacePrefix($namespacePrefix) {
        $this->namespacePrefix = $namespacePrefix;
    }

    function setWebservices_uri($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function setResult($result) {
        $this->result = $result;
    }

    function setError($error) {
        $this->error = $error;
    }

    public function handleError($err) {
        $this->result = null;
        $this->error = $err;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
    }

    function getNamespaces() {
        return $this->namespaces;
    }

    function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    function getCustomNamespacePrefix() {
        return $this->customNamespacePrefix;
    }

    function setCustomNamespacePrefix($customNamespacePrefix) {
        $this->customNamespacePrefix = $customNamespacePrefix;
    }

    function getSoapActionPrefix() {
        return $this->soapActionPrefix;
    }

    function setSoapActionPrefix($soapActionPrefix) {
        $this->soapActionPrefix = $soapActionPrefix;
    }

    function getRequest() {
        return $this->request;
    }

    function setRequest($request) {
        $this->request = $request;
    }

    function getResponse() {
        return $this->response;
    }

    function setResponse($response) {
        $this->response = $response;
    }

}

?>
