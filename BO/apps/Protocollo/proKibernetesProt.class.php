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
 * @version    24.07.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesProtClient.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proKibernetesProt extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Kibernetes
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private function setClientConfig($kibernetesClient) {

        $devLib = new devLib();
        $uri = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESENDPOINT', false);
        $wsdl = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESWSDL', false);
        $username = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESUSER', false);
        $password = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESPASSWORD', false);
        $codiceUOPar = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESCODICEUOPAR', false);
        $funzionarioPar = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESFUNZIONARIOPAR', false);
        $codiceUOArr = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESCODICEUOARR', false);
        $funzionarioArr = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETESFUNZIONARIOARR', false);
        $istatAmministrazione = $devLib->getEnv_config('KIBERNETESWSCONNECTION', 'codice', 'WSKIBERNETSCODICEISTATAMMINISTRAZIONE', false);

        $kibernetesClient->setWebservices_uri($uri['CONFIG']);
        $kibernetesClient->setWebservices_wsdl($wsdl['CONFIG']);
        $kibernetesClient->setUsername($username['CONFIG']);
        $kibernetesClient->setPassword($password['CONFIG']);
        $kibernetesClient->setCodiceUOPar($codiceUOPar['CONFIG']);
        $kibernetesClient->setFunzionarioPar($funzionarioPar['CONFIG']);
        $kibernetesClient->setCodiceUOArr($codiceUOArr['CONFIG']);
        $kibernetesClient->setFunzionarioArr($funzionarioArr['CONFIG']);
        $kibernetesClient->setIstatAmministrazione($istatAmministrazione['CONFIG']);
        $kibernetesClient->setNamespace();
        $kibernetesClient->setNamespaces();
    }

    public function ProtocollaUscita($elementi) {
        $WSClient = new itaKibernetesProtClient();
        $this->setClientConfig($WSClient);

        $param = array();
        //
        $param['Destinatario'] = $elementi['dati']['destinatari'][0]['Denominazione'] . " " . "<" . $elementi['dati']['destinatari'][0]['Email'] . ">";
        $param['Indirizzo'] = $elementi['dati']['destinatari'][0]['Indirizzo'];

        $param['Oggetto'] = utf8_encode($elementi['dati']['Oggetto']);

        if (isset($elementi['dati']['destinatari'][1])) {
            $param['DestinatarioSec'] = $elementi['dati']['destinatari'][1]['Denominazione'] . " " . "<" . $elementi['dati']['destinatari'][1]['Email'] . ">";
            $param['IndirDestinatarioSec'] = $elementi['dati']['destinatari'][1]['Indirizzo'];
        }

        $param['DestinatarioSecCC'] = "false";

        $param['AnnoPrec'] = $elementi['dati']['AnnoAntecedente'];
        $param['ProtPrec'] = $elementi['dati']['NumeroAntecedente'];
        //
        $risultato = $WSClient->ws_Set4ProtocolloUscita($param);
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
                    'TipoProtocollo' => array('value' => 'Kibernetes', 'status' => true, 'msg' => 'ProtocollazioneUscita'),
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
            $ritorno["Message"] = "Attenzione!! protocollazione fallita: " . $risultato['DescrStato'] . "<br><br>$response";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    public function ProtocollaEntrata($elementi) {
        $WSClient = new itaKibernetesProtClient();
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
        $param['AnnoPrec'] = $elementi['dati']['AnnoAntecedente'];
        $param['ProtPrec'] = $elementi['dati']['NumeroAntecedente'];
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
            $ritorno["Message"] = "Attenzione!! protocollazione fallita: " . $risultato['DescrStato'] . "<br><br>$response";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    public function ProtocollaInterno($elementi) {
        $WSClient = new itaKibernetesProtClient();
        $this->setClientConfig($WSClient);

        $param = array();
        //
        if ($elementi['dati']['MittDest']['Denominazione']) {
            $dest = $elementi['dati']['MittDest']['Denominazione'];
        } else {
            $dest = $elementi['dati']['MittDest']['Cognome'] . " " . $elementi['dati']['MittDest']['Nome'];
        }
        $dest = strtoupper($dest);

        $retCheck = $this->checkFunzionario($WSClient, $dest);
        if ($retCheck['Status'] == "-1") {
            return $retCheck;
        }


        $param['Mittente'] = $elementi['mittenti'][0]['Denominazione'];
        $param['Oggetto'] = utf8_encode($elementi['dati']['Oggetto']);

        $funzionarioDest = $dest;
        if ($elementi['dati']['MittDest']['Email']) {
            $funzionarioDest = $dest . " " . "<" . $elementi['dati']['MittDest']['Email'] . ">";
        }
        $param['FunzionarioDest'] = $funzionarioDest;
        $param['DestinatarioUO'] = $elementi['dati']['destinatari'][0]['uffici'][0]['Descrizione'];
        $param['AnnoPrec'] = $elementi['dati']['AnnoAntecedente'];
        $param['ProtPrec'] = $elementi['dati']['NumeroAntecedente'];
        $param['FunzionarioDestSecCC'] = "false";
        //
        $risultato = $WSClient->ws_Set4ProtocolloInterno($param);
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
                    'TipoProtocollo' => array('value' => 'Kibernetes', 'status' => true, 'msg' => 'ProtocollazioneInterno'),
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
            $ritorno["Message"] = "Attenzione!! protocollazione fallita: " . $risultato['DescrStato'] . "<br><br>$response";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    public function AggiungiAllegati($elementi, $Numero, $Anno) {
        $WSClient = new itaKibernetesProtClient();
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

    private function checkFunzionario($WSClient, $dest) {
        $risultato = $WSClient->ws_Check4Funzionario($dest);
        if (!$risultato) {
            $response = $WSClient->getResponse();
            if ($WSClient->getFault()) {
                $msg = $WSClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un fault in fase di check funzionario: <br>$msg<br><br>$response";
                $ritorno["RetValue"] = false;
            } elseif ($WSClient->getError()) {
                $msg = $WSClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di check funzionario: <br>$msg<br><br>$response";
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }

        $risultato = $WSClient->getResult();
        if ($risultato['CodStato'] != "0") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $dest . ":<br>" . $risultato['DescrStato'] . "<br><br>$response";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $risultato['DescrStato'];
        $ritorno["RetValue"] = true;
        return $ritorno;
    }

    public function leggiCopie() {
        return true;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_KIBERNETES;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->ProtocollaUscita($elementi);
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->ProtocollaEntrata($elementi);
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->ProtocollaInterno($elementi);
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}
