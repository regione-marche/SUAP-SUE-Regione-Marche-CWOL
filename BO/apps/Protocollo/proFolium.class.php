<?php

/**
 * Protocollo folium di dedagroup
 * 
 * PHP Version 5
 *
 * @category
 * @package
 * @author    Luca Cardinali <l.cardinali@apra.it>
 * @license
 * @version    12.11.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPFolium/itaFoliumClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proFolium extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($client) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMENDPOINT', false);
        $client->setWebservices_uri($uri['CONFIG']);

        $aoo = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMAOO', false);
        $client->setCodiceAOO($aoo['CONFIG']);

        $codEnte = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMCODENTE', false);
        $client->setCodiceEnte($codEnte['CONFIG']);

        $utente = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMUSER', false);
        $client->setUsername($utente['CONFIG']);

        $passwd = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMPASSWD', false);
        $client->setPassword($passwd['CONFIG']);

        $codTitolario = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMTITOLARIO', false);
        $client->setCodTitolario($codTitolario['CONFIG']);

        $nomeAppl = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMNOMEAPPL', false);
        $client->setNomeApplicativo($nomeAppl['CONFIG']);

        $registro = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMREGISTRO', false);
        $client->setRegistro($registro['CONFIG']);
        
        $mezzoSpedizioneDefault = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMMEZZOSPEDIZ', false);
        $client->setMezzoSpedizioneDefault($mezzoSpedizioneDefault['CONFIG']);
        
        $ufficio = $devLib->getEnv_config('FOLIUMWSCONNECTION', 'codice', 'FOLIUMUFFICIO', false);
        $client->setCodiceUfficio($ufficio['CONFIG']);
    }

    private function inserisciAllegati($itaFoliumClient, $elementi, $idProtocollo) {
        $allegatiInseriti = array();
        $ritorno = array();
        $errMessage = "";
        $errore = false;

        if ($elementi['dati']['DocumentoPrincipale']) {
            $stream = $elementi['dati']['DocumentoPrincipale']['Stream'];
            if ($itaFoliumClient->ws_inserisciDocumentoPrincipale($idProtocollo, $elementi['dati']['DocumentoPrincipale']['Nome'], $stream)) {
                $returnData = $itaFoliumClient->getResult();
                if ($returnData['esito']['codiceEsito'] !== '000') {
                    $errMessage .= 'allegato: ' . $elementi['dati']['DocumentoPrincipale']['Nome'] . " errore: " . $returnData['esito']['descrizioneEsito'] . "<br>";
                    $errore = true;
                }
            }
        }

        foreach ($elementi['dati']['DocumentiAllegati'] as $key => $allegato) {
            $stream = $allegato['Stream'];
            if ($itaFoliumClient->ws_inserisciAllegato($idProtocollo, $allegato['Nome'], $stream)) {
                $returnData = $itaFoliumClient->getResult();
                if ($returnData['esito']['codiceEsito'] !== '000') {
                    $errMessage .= 'allegato: ' . $allegato['Nome'] . " errore: " . $returnData['esito']['descrizioneEsito'] . "<br>";
                    $errore = true;
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
        $itaFoliumClient = new itaFoliumClient();
        $this->setClientConfig($itaFoliumClient);

        $ret = $itaFoliumClient->ws_protocolla($elementi);

        if (!$ret) {
            if ($itaFoliumClient->getError()) {
                $msg = $itaFoliumClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $itaFoliumClient->getResult();
        if ($risultato['esito']['codiceEsito'] === '000') {
            $this->inserisciAllegati($itaFoliumClient, $elementi, $risultato['id']);

            $data = $risultato['dataProtocollo'];
            $anno = strtok($data, "-");

            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'proNum' => array('value' => $risultato['numeroProtocollo'], 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $data, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $anno, 'status' => true, 'msg' => '')
                )
            );

            return $ritorno;
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['esito']['descrizioneEsito'];
            $ritorno["RetValue"] = false;

            return $ritorno;
        }
    }

    public function InvioMail($elementi) {
        return false;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_FOLIUM;
    }

    public function leggiProtocollazione($params) {
        return '';
    }

    public function inserisciProtocollazionePartenza($elementi) {
        $elementi['tipo'] = "U";
        return $this->inserisciProtocollo($elementi);
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        $elementi['tipo'] = "I";
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