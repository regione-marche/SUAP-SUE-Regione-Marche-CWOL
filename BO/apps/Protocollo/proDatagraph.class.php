<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author    Luca Cardinali <l.cardinali@apra.it>
 * @license
 * @version    17.09.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPDatagraph/itaDatagraphClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proDatagraph extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($italsoftClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHENDPOINT', false);
        $italsoftClient->setWebservices_uri($uri['CONFIG']);

        $aoo = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHAOO', false);
        $italsoftClient->setCodiceAOO($aoo['CONFIG']);

        $codEnte = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHCODENTE', false);
        $italsoftClient->setCodiceEnte($codEnte['CONFIG']);

        $utente = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHUSER', false);
        $italsoftClient->setUsername($utente['CONFIG']);

        $passwd = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHPASSWD', false);
        $italsoftClient->setPassword($passwd['CONFIG']);

        $uo = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHUO', false);
        $italsoftClient->setUnitaOrganizzativa($uo['CONFIG']);

        $codTitolario = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHTITOLARIO', false);
        $italsoftClient->setCodTitolario($codTitolario['CONFIG']);

        $nomeAppl = $devLib->getEnv_config('DATAGRAPHWSCONNECTION', 'codice', 'DATAGRAPHNOMEAPPL', false);
        $italsoftClient->setNomeApplicativo($nomeAppl['CONFIG']);
    }

    public function login() {
        $itaDatagraphClient = new itaDatagraphClient();
        $this->setClientConfig($itaDatagraphClient);
        if ($itaDatagraphClient->ws_Login()) {
            $returnData = $itaDatagraphClient->getResult();
            if ($returnData->lngErrNumber > 0) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . $returnData->strErrString;
                $ritorno["RetValue"] = false;
            } else {
                $ritorno["Status"] = "0";
                $ritorno["Message"] = 'Login avvenuto con successo!';
                $ritorno["RetValue"] = $returnData->strDST;
            }
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Error: <br>" . $itaDatagraphClient->getError();
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    private function inserisciAllegati($itaDatagraphClient, $token, &$elementi) {
        $allegatiInseriti = array();
        $ritorno = array();
        $errMessage = "";
        $errore = false;

        if ($elementi['dati']['DocumentoPrincipale']) {
            $stream = $elementi['dati']['DocumentoPrincipale']['Stream'];
            if ($itaDatagraphClient->ws_inserisciDocumento($token, $elementi['dati']['DocumentoPrincipale']['Nome'], $stream)) {
                $returnData = $itaDatagraphClient->getResult();
                if ($returnData->lngErrNumber > 0) {
                    $errMessage .= 'allegato: ' . $elementi['dati']['DocumentoPrincipale']['Nome'] . " errore: " . $returnData->strErrString . "<br>";
                    $errore = true;
                } else {
                    $elementi['dati']['DocumentoPrincipale']['Id'] = $returnData->lngDocID;
                }
            }
        }

        foreach ($elementi['dati']['DocumentiAllegati'] as $key => $allegato) {
            $stream = $allegato['Stream'];
            if ($itaDatagraphClient->ws_inserisciDocumento($token, $allegato['Nome'], $stream)) {
                $returnData = $itaDatagraphClient->getResult();
                if ($returnData->lngErrNumber > 0) {
                    $errMessage .= 'allegato: ' . $allegato['Nome'] . " errore: " . $returnData->strErrString . "<br>";
                    $errore = true;
                } else {
                    $elementi['dati']['DocumentiAllegati'][$key]['Id'] = $returnData->lngDocID;
                }
            }
        }

        if ($errore) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $errMessage;
            $ritorno["RetValue"] = false;
        } else {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Allegati Inseriti";
            $ritorno["RetValue"] = true;
        }
        return $ritorno;
    }

    private function inserisciProtocollo($elementi) {
        $itaDatagraphClient = new itaDatagraphClient();
        $this->setClientConfig($itaDatagraphClient);
        $loginReturn = $this->login();
        if ($loginReturn['Status'] == '-1') {
            // errore login
            return $loginReturn;
        }

        $token = $loginReturn['RetValue'];

        $returnInserimento = $this->inserisciAllegati($itaDatagraphClient, $token, $elementi);

        if ($returnInserimento['Status'] == '-1') {
            // errore inserimento allegati
            return $returnInserimento;
        }

        $ret = $itaDatagraphClient->ws_protocolla($token, $elementi);

        if (!$ret) {
            if ($itaDatagraphClient->getError()) {
                $msg = $itaDatagraphClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaDatagraphClient->getResult();
        if ($risultato->lngErrNumber == 0) {
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";          
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'proNum' => array('value' => $risultato->lngNumPG, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $risultato->strDataPG, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $risultato->lngAnnoPG, 'status' => true, 'msg' => '')
                )
            );

            return $ritorno;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato->strErrString;
            $ritorno["RetValue"] = false;

            return $ritorno;
        }
    }

    public function InvioMail($elementi) {
        return $this->eseguiInviaMail($elementi['Anno'], $elementi['proNum']);
    }

    private function eseguiInviaMail($anno, $numero) {
        $itaDatagraphClient = new itaDatagraphClient();
        $this->setClientConfig($itaDatagraphClient);
        $loginReturn = $this->login();
        if ($loginReturn['Status'] == '-1') {
            // errore login
            return $loginReturn;
        }

        $token = $loginReturn['RetValue'];

        $ret = $itaDatagraphClient->ws_inviaMail($token, $anno, $numero);

        if (!$ret) {
            if ($itaDatagraphClient->getError()) {
                $msg = $itaDatagraphClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di invio mail: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaDatagraphClient->getResult();
        if ($risultato->lngErrNumber == 0) {
            $ritorno["Status"] = "0";
            //TODO
//            $ritorno["Message"] = $ritorno["RetValue"] = array(
//                'DatiProtocollazione' => array(
//                    'proNum' => $risultato->lngNumPG,
//                    'Data' => $risultato->strDataPG,
//                    'Anno' => $risultato->lngAnnoPG
//            ));
            $ritorno["RetValue"] = true;

            return $ritorno;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato->strErrString;
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_DATAGRAPH;
    }

    public function leggiProtocollazione($params) {
        return '';
    }

    public function inserisciProtocollazionePartenza($elementi) {
        $elementi['tipo'] = 'U';
        return $this->inserisciProtocollo($elementi);
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        $elementi['tipo'] = 'E';
        return $this->inserisciProtocollo($elementi);
    }

    public function inserisciDocumentoInterno($elementi) {
        return "";
    }

    public function leggiDocumentoInterno($params) {
        return "";
    }

}

?>