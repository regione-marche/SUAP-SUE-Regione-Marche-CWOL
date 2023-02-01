<?php

/**
 *
 * Classe per collegamento rest servoice
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPCiviliaNext
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    21.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class itaPHPCiviliaNextClient {

    private $token;
    private $endpoint;
    private $codiceOrganigramma;
    private $idOperatore;
    private $timeout = 2400;
    private $errMessage;

    function getToken() {
        return $this->token;
    }

    function getEndpoint() {
        return $this->endpoint;
    }

    function getIdOperatore() {
        return $this->idOperatore;
    }

    function setToken($token) {
        $this->token = $token;
    }

    function setEndpoint($endpoint) {
        $this->endpoint = $endpoint;
    }

    function setIdOperatore($idOperatore) {
        $this->idOperatore = $idOperatore;
    }

    function getCodiceOrganigramma() {
        return $this->codiceOrganigramma;
    }

    function getTimeout() {
        return $this->timeout;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setCodiceOrganigramma($codiceOrganigramma) {
        $this->codiceOrganigramma = $codiceOrganigramma;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function sendRequest($resource, $data) {

        $url = $this->endpoint . $resource;
        $itaRestClient = new itaRestClient();
        if (!$itaRestClient->post($url, false, array('Authorization: Bearer ' . $this->token), $data, 'application/json')) {
            $this->setErrMessage("Errore nella richiesta.");
            return false;
        }
        return $itaRestClient->getResult();
    }

    public function Protocolla($param) {
        $request = array();
        $request['oggetto'] = $param['oggetto'];
        if (isset($param['note'])) {
            $request['note'] = $param['note'];
        }
        $request['isFromModificaAllegato'] = $param['isFromModificaAllegato'];
        $request['isFromModificaCorrispondente'] = $param['isFromModificaCorrispondente'];
        $request['tipoProtocollo'] = $param['tipoProtocollo'];
        if (isset($param['dataConsegnaDocumento'])) {
            $request['dataConsegnaDocumento'] = $param['dataConsegnaDocumento'];
        }
        //idCorrispondentiList
        if (!is_array($param['CorrispondentiList'])) {
            $this->setErrMessage("Lista Corrispondenti vuota");
            return false;
        }
        $corrispondenti = array();
        foreach ($param['CorrispondentiList'] as $CorrispondenteElement) {
            $corrispondente = array(
                'denominazione' => $CorrispondenteElement['denominazione'],
                'email' => $CorrispondenteElement['email'],
                'tipoIndividuoProtocollo' => $CorrispondenteElement['tipoIndividuoProtocollo'],
            );
            $corrispondenti[] = $corrispondente;
        }
        $request['idCorrispondentiList'] = $corrispondenti;
        //AllegatiList
        $allegati = array();
        foreach ($param['AllegatiList'] as $AllegatoElement) {
            $allegato = array();
            $allegato['nomeFile'] = $AllegatoElement['nomeFile'];
            if (isset($AllegatoElement['titolo'])) {
                $allegato['titolo'] = $AllegatoElement['titolo'];
            }
            if (isset($AllegatoElement['descrizione'])) {
                $allegato['descrizione'] = $AllegatoElement['descrizione'];
            }
            $allegato['file'] = $AllegatoElement['file'];
            $allegato['mimeType'] = $AllegatoElement['mimeType'];
            $allegato['isPrincipale'] = $AllegatoElement['isPrincipale'];
            if (isset($AllegatoElement['idSingolaFattura'])) {
                $allegato['idSingolaFattura'] = $AllegatoElement['idSingolaFattura'];
            }
            $allegati[] = $allegato;
        }
        $request['AllegatiList'] = $allegati;

        $request['protocollatoDa'] = $param['protocollatoDa'];
        $request['codiceLivelloOrganigramma'] = $this->codiceOrganigramma; //$param['codiceLivelloOrganigramma'];
        $request['idOperatore'] = $this->idOperatore;
        //$request['idCodiceAOO'] = $param['idCodiceAOO'];
        return $this->sendRequest('/Protocollo/Protocollo/Protocolla', json_encode($request));
    }

    public function AssegnaPratica($param) {
        $request = array();
        $request['idPratica'] = $param['idPratica'];
        $request['idOperatore'] = $this->idOperatore;
        $assegnatari = array();
        foreach ($param['Assegnatari'] as $assegnatarioElement) {
            $assegnatario = array();
            $assegnatario['idIndividuoAssegnatario'] = $assegnatarioElement['idIndividuoAssegnatario'];
            $assegnatario['codiceLivelloOrganigrammaAssegnatario'] = $this->codiceOrganigramma;
            $assegnatario['attributo'] = $assegnatarioElement['attributo'];
            $assegnatario['path'] = $assegnatarioElement['path'];
            $assegnatario['ruoloAssegnatario'] = $assegnatarioElement['ruoloAssegnatario'];
            $assegnatario['isAssegnatario'] = $assegnatarioElement['isAssegnatario'];
            $assegnatari[] = $assegnatario;
        }
        $request['assegnatari'] = $assegnatari;
        return $this->sendRequest('/Protocollo/Protocollo/AssegnaPratica', json_encode($request));
    }

}
