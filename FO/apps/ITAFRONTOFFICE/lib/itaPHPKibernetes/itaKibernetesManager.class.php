<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    20.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesClient.class.php');

class itaKibernetesManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaKibernetesManager();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($KibernetesClient) {
        $KibernetesClient->setWebservices_uri($this->clientParam['WSKIBERNETSPROTOCOLLOENDPOINT']);
        $KibernetesClient->setWebservices_wsdl($this->clientParam['WSKIBERNETSPROTOCOLLOWSDL']);
        $KibernetesClient->setIstatAmministrazione($this->clientParam['WSKIBERNETSPROTOCOLLOCODICEISTATAMMINISTRAZIONE']);
        $KibernetesClient->setUsername($this->clientParam['WSKIBERNETSPROTOCOLLOUTENTE']);
        $KibernetesClient->setPassword($this->clientParam['WSKIBERNETSPROTOCOLLOPASSWORD']);
        $KibernetesClient->setCodiceUOArr($this->clientParam['WSKIBERNETSPROTOCOLLOCODICEUO']);
        $KibernetesClient->setFunzionarioArr($this->clientParam['WSKIBERNETSPROTOCOLLOCODICEFUNZIONARIO']);
        $KibernetesClient->setNamespace();
        $KibernetesClient->setNamespaces();
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi) {
        $WSClient = new itaKibernetesClient();
        $this->setClientConfig($WSClient);

        $param = array();
        //
        if ($elementi['dati']['MittDest']['Denominazione']) {
            $mitt = $elementi['dati']['MittDest']['Denominazione'];
        } else {
            $mitt = $elementi['dati']['MittDest']['Cognome'] . " " . $elementi['dati']['MittDest']['Nome'];
        }

        $param['Mittente'] = $mitt;
        $param['Indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
        $param['Oggetto'] = utf8_encode($elementi['dati']['Oggetto']);
        $param['FunzionarioDest'] = $elementi['dati']['MittDest']['Funzionario'];
        $param['AnnoPrec'] = $elementi['dati']['annoProtocolloAntecedente'];
        $param['ProtPrec'] = $elementi['dati']['numeroProtocolloAntecedente'];
        $param['FunzionarioDestSecCC'] = "false";
        //
        $risultato = $WSClient->ws_Set4ProtocolloEntrata($param);
        if (!$risultato) {
            $response = $WSClient->getResponse();
            if ($WSClient->getFault()) {
                $msg = $WSClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di protocollazione: <br>$msg<br><br>$response";
                $ritorno["RetValue"] = false;
            } elseif ($WSClient->getError()) {
                $msg = $WSClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>$msg<br><br>$response";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }

        $risultato = $WSClient->getResult();

        if ($risultato['CodStato'] == 0 && $risultato['DescrStato'] == "OK") {

            /*
             * Aggiungo gli allegati se la protocollazione è andata a buon fine
             */
            $retAllegati = $this->AggiungiAllegati($elementi, $risultato['Numero'], $risultato['Anno']);

            $Data = date("Y-m-d");
            $proNum = $risultato['Numero'];
            $Anno = $risultato['Anno'];
            //
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Kibernetes', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
            if (isset($retAllegati['strNoProt'])) {
                $ritorno['strNoProt'] = $retAllegati['strNoProt'];
            }
        } else {
            $response = $WSClient->getResponse();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione!! protocollazione fallita: " . $risultato['DescrStato']."<br><br>$response";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    /**
     * 
     */
    public function AggiungiAllegati($elementi, $Numero, $Anno) {
        $WSClient = new itaKibernetesClient();
        $this->setClientConfig($WSClient);

        $msgErr = "";
        $countErr = 0;

        /*
         * Aggiungo l'allegato Principale
         */
        $docPrinc = $elementi['dati']['DocumentoPrincipale'];
        $nome = $docPrinc['Nome'];
        $strem = $docPrinc['Stream'];
        $descrizione = $docPrinc['Descrizione'];
        if (!$docPrinc) {
            $docPrinc = $elementi['dati']['DocumentiAllegati'][0];
            $nome = $docPrinc['Documento']['Nome'];
            $strem = $docPrinc['Documento']['Stream'];
            $descrizione = $docPrinc['Descrizione'];
            unset($elementi['dati']['DocumentiAllegati'][0]); //Tolgo il primo allegato perchè è diventato il principale
        }

        if ($docPrinc) {
            $Allegato['Numero'] = $Numero;
            $Allegato['Anno'] = $Anno;
            $Allegato['Image'] = $strem;
            $Allegato['Filename'] = utf8_encode($nome);
            $Allegato['Descrizione'] = $descrizione;
            $Allegato['Principale'] = "true";
            $ret = $WSClient->ws_SetAllegato4Protocollo($Allegato);
            if (!$ret) {
                $response = $WSClient->getResponse();
                if ($WSClient->getFault()) {
                    $msgErr = "Fault in fase di aggiungi allegato principale: " . $WSClient->getFault() . "<br><br>$response";
                } elseif ($WSClient->getError()) {
                    $msgErr = "Errore in fase di aggiungi allegato principale: " . $WSClient->getError() . "<br><br>$response";
                }
                $countErr++;
            }
            $risultato = $WSClient->getResult();
            if ($risultato['CodStato'] == -1) {
                $msgErr = "Allegato principale non aggiunto: " . $risultato['DescrStato'] . "<br>";
                $countErr++;
            }
        }

        /*
         * Aggiungo altri allegati
         */
        foreach ($elementi['dati']['DocumentiAllegati'] as $key => $alle) {
            $Allegato['Numero'] = $Numero;
            $Allegato['Anno'] = $Anno;
            $Allegato['Image'] = $alle['Documento']['Stream'];
            $Allegato['Filename'] = utf8_encode($alle['Documento']['Nome']);
            $Allegato['Descrizione'] = $alle['Descrizione'];
            $Allegato['Principale'] = "false";
            $ret = $WSClient->ws_SetAllegato4Protocollo($Allegato);
            if (!$ret) {
                $response = $WSClient->getResponse();
                if ($WSClient->getFault()) {
                    $msgErr .= "Fault in fase di aggiungi allegato: " . $WSClient->getFault() . "<br><br>$response";
                } elseif ($WSClient->getError()) {
                    $msgErr .= "Errore in fase di aggiungi allegato: " . $WSClient->getError() . "<br><br>$response";
                }
                $countErr++;
            }
            $risultato = $WSClient->getResult();
            if ($risultato['CodStato'] == -1) {
                $msgErr .= "Allegato non aggiunto: " . $risultato['DescrStato'] . "<br>";
                $countErr++;
            }
        }

        if ($countErr > 0) {
            $ritorno["strNoProt"] = $msgErr;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Allegati aggiunti correttamenti";
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

}

?>