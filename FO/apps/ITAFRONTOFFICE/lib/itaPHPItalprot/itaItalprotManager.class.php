<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    24.06.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPItalprot/itaItalprotClient.class.php');

class itaItalprotManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaItalprotManager();
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
    private function setClientConfig($italsoftClient) {
        $italsoftClient->setWebservices_uri($this->clientParam['WSITALSOFTENDPOINT']);
        $italsoftClient->setWebservices_wsdl($this->clientParam['WSITALSOFTWSDL']);
        $italsoftClient->setDomain($this->clientParam['WSITALSOFTDOMAIN']);
        $italsoftClient->setUsername($this->clientParam['WSITALSOFTUSERNAME']);
        $italsoftClient->setPassword($this->clientParam['WSITALSOFTPASSWORD']);
        //$italsoftClient->setTimeout($this->clientParam['WSITALSOFTTIMEOUT']);
    }

    /**
     * 
     */
    function LeggiProtocollo() {
        return;
    }

    /**
     * 
     * @param array $elementi array normalizzato di elementi per la protocollazione
     * @param string $origine
     * @return boolean|array
     */
    public function InserisciProtocollo($elementi, $origine = "A") {
        $ItalprotClient = new itaItalprotClient();
        $this->setClientConfig($ItalprotClient);
        $param = array();
        $param['userName'] = $this->clientParam['WSITALSOFTUSERNAME'];
        $param['userPassword'] = $this->clientParam['WSITALSOFTPASSWORD'];
        $param['domainCode'] = $ItalprotClient->getDomain(); // $this->clientParam['WSITALSOFTDOMAIN'];
        $ret = $ItalprotClient->ws_GetItaEngineContextToken($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile reperire un token valido: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $token = $ItalprotClient->getResult();

        //

        $param['token'] = $token;
        //
        
        $elementi['dati'] = frontOfficeLib::utf8_encode_recursive($elementi['dati']);
        
        
        $param['datiProtocollo'] = array();
        $param['datiProtocollo']['tipoProtocollo'] = $origine;
        $param['datiProtocollo']['tipoDocumento'] = $elementi['dati']['TipoDocumento'];

        $param['datiProtocollo']['ufficioOperatore'] = "";

        //
        $param['datiProtocollo']['oggetto'] = $elementi['dati']['Oggetto'];
        if (isset($elementi['dati']['numeroProtocolloAntecedente']) && $elementi['dati']['numeroProtocolloAntecedente']) {
            $param['datiProtocollo']['numeroProtocolloAntecedente'] = $elementi['dati']['numeroProtocolloAntecedente'];
            $param['datiProtocollo']['annoProtocolloAntecedente'] = substr($elementi['dati']['dataProtocolloAntecedente'], 0, 4);
            $param['datiProtocollo']['tipoProtocolloAntecedente'] = "A";
        }

        $param['datiProtocollo']['dataArrivo'] = $elementi['dati']['DataArrivo'];
        $param['datiProtocollo']['classificazione'] = $elementi['dati']['Classificazione'];

        if ($elementi['dati']['MittDest']['Denominazione'] != "") {
            $param['datiProtocollo']['mittenti']['mittenteDestinatario'] = array();
            $mittenteDestinatario = array();
            $mittenteDestinatario['codice'] = "";
            $mittenteDestinatario['denominazione'] = $elementi['dati']['MittDest']['Denominazione'];
            $mittenteDestinatario['indirizzo'] = $elementi['dati']['MittDest']['Indirizzo'];
            $mittenteDestinatario['cap'] = $elementi['dati']['MittDest']['CAP'];
            $mittenteDestinatario['citta'] = $elementi['dati']['MittDest']['Citta'];
            $mittenteDestinatario['prov'] = $elementi['dati']['MittDest']['Provincia'];
            $mittenteDestinatario['codiceFiscale'] = $elementi['dati']['MittDest']['CF'];
            $mittenteDestinatario['partitaIva'] = "";
            $mittenteDestinatario['telefono'] = $elementi['dati']['MittDest']['Telefono'];
            $mittenteDestinatario['fax'] = "";
            $mittenteDestinatario['email'] = $elementi['dati']['MittDest']['Email'];
            $mittenteDestinatario['pec'] = $elementi['dati']['MittDest']['Email'];
            $mittenteDestinatario['ufficio'] = "";
            $param['datiProtocollo']['mittenti']['mittenteDestinatario'][] = $mittenteDestinatario;
        }
        $param['datiProtocollo']['trasmissioniInterne']['trasmissione'] = array();

        if ($elementi['dati']['InCaricoA'] != "") {
            if (strpos($elementi['dati']['InCaricoA'], "|") !== false) {
                $arrTrasmissioni = explode("|", $elementi['dati']['InCaricoA']);
                foreach ($arrTrasmissioni as $keyTras => $trasmissione) {
                    list($ufficio, $codiceDest) = explode(".", $trasmissione);
                    $trasmissione = array();
                    $trasmissione['codiceUfficio'] = $ufficio;
                    if (is_numeric($codiceDest)) {
                        $codiceDest = str_pad($codiceDest, 6, "0", STR_PAD_LEFT);
                    }
                    $trasmissione['codiceDestinatario'] = $codiceDest;
                    $trasmissione['oggettoTrasmissione'] = "";
                    $trasmissione['gestione'] = 1;
                    $trasmissione['responsabile'] = 1;
                    $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][$keyTras] = $trasmissione;
                }
            } else {
                $arrInCarico = explode(".", $elementi['dati']['InCaricoA']);
                $codiceUfficio = str_pad($arrInCarico[0], 4, "0", STR_PAD_LEFT);
                //$codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
                $codiceDest = $arrInCarico[1];
                if (is_numeric($arrInCarico[1])) {
                    $codiceDest = str_pad($arrInCarico[1], 6, "0", STR_PAD_LEFT);
                }
                $trasmissione = array();
                $trasmissione['codiceUfficio'] = $codiceUfficio;
                $trasmissione['codiceDestinatario'] = $codiceDest;
                $trasmissione['oggettoTrasmissione'] = "";
                $trasmissione['gestione'] = 1;
                $trasmissione['responsabile'] = 1;
                $param['datiProtocollo']['trasmissioniInterne']['trasmissione'][] = $trasmissione;
            }
        }

        /*
         * Inserisco l'allegato Principale
         */
        $docPrinc = array();
        if (isset($elementi['dati']['DocumentoPrincipale']) && isset($elementi['dati']['DocumentoPrincipale']['Nome'])) {
            $docPrinc['tipoFile'] = "PRINCIPALE";
            $docPrinc['nomeFile'] = $elementi['dati']['DocumentoPrincipale']['Nome'];
            $docPrinc['stream'] = $elementi['dati']['DocumentoPrincipale']['Stream'];
            $docPrinc['note'] = $elementi['dati']['DocumentoPrincipale']['Descrizione'];
        } else {
            $docPrinc['tipoFile'] = "PRINCIPALE";
            $docPrinc['nomeFile'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Nome'];
            $docPrinc['stream'] = $elementi['dati']['DocumentiAllegati'][0]['Documento']['Stream'];
            $docPrinc['note'] = $elementi['dati']['DocumentiAllegati'][0]['Descrizione'];
            unset($elementi['dati']['DocumentiAllegati'][0]);
        }
        
        $paramAlle = array();
        $paramAlle['token'] = $token;
        $paramAlle['nomeFile'] = $docPrinc['nomeFile'];
        $paramAlle['stream'] = $docPrinc['stream'];
        $ret = $ItalprotClient->ws_insertDocumento($paramAlle);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
        }
        $risultato = $ItalprotClient->getResult();
        $TipoRisultatoAll = $risultato['messageResult']['tipoRisultato'];
        $DescrizioneRisultatoAll = $risultato['messageResult']['descrizione'];
        if ($TipoRisultatoAll == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato principale " . $docPrinc['nomeFile'] . ": <br>$DescrizioneRisultatoAll";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['idunivoco'] = $risultato['allegatoPrecaricato']['idunivoco'];
            $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['hashfile'] = $risultato['allegatoPrecaricato']['hashfile'];
            $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['tipoFile'] = "";
            $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['nomeFile'] = $docPrinc['nomeFile'];
            $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][0]['note'] = $docPrinc['note'];
        }

        /*
         * Inserisco Gli Allegati col nuovo metodo 
         */
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $TipoRisultatoAll = $DescrizioneRisultatoAll = "";
        foreach ($DocAllegati as $keyAlle => $record) {
            $paramAlle = array();
            $paramAlle['token'] = $token;
            $paramAlle['nomeFile'] = $record['Documento']['Nome'];
            $paramAlle['stream'] = $record['Documento']['Stream'];
            $ret = $ItalprotClient->ws_insertDocumento($paramAlle);
            if (!$ret) {
                if ($ItalprotClient->getFault()) {
                    $msg = $ItalprotClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Impossibile inserire l'allegato " . $record['Documento']['Stream'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($ItalprotClient->getError()) {
                    $msg = $ItalprotClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Impossibile inserire l'allegato " . $record['Documento']['Stream'] . ": <br>$msg";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            }
            $risultato = $ItalprotClient->getResult();
            $TipoRisultatoAll = $risultato['messageResult']['tipoRisultato'];
            $DescrizioneRisultatoAll = $risultato['messageResult']['descrizione'];
            if ($TipoRisultatoAll == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Attenzione!! Impossibile inserire l'allegato " . $record['Nome'] . ": <br>$DescrizioneRisultatoAll";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } else {
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle+1]['idunivoco'] = $risultato['allegatoPrecaricato']['idunivoco'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle+1]['hashfile'] = $risultato['allegatoPrecaricato']['hashfile'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle+1]['tipoFile'] = "ALLEGATO";
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle+1]['nomeFile'] = $record['Documento']['Nome'];
                $param['datiProtocollo']['allegatiPrecaricati']['allegatoPrecaricato'][$keyAlle+1]['note'] = $record['Descrizione'];
            }
        }

        /*
         * Inserisco il protocollo
         */
        $ret = $ItalprotClient->ws_putProtocollo($param);
        if (!$ret) {
            if ($ItalprotClient->getFault()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($ItalprotClient->getError()) {
                $msg = $ItalprotClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>$msg";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $ItalprotClient->getResult();

        $TipoRisultato = $risultato['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            $Data = $risultato['datiProtocollo']['dataProtocollo']; //è nel formato 20160101
            $proNum = $risultato['datiProtocollo']['numeroProtocollo'];
            $rowidProtocollo = $risultato['datiProtocollo']['rowidProtocollo'];
            $Segnatura = $risultato['datiProtocollo']['segnatura'];
            $Anno = $risultato['datiProtocollo']['annoProtocollo'];
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Italsoft-ws', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $rowidProtocollo, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        }

        /*
         * Distruggo il token
         */
        $paramDestroy = array();
        $paramDestroy['token'] = $token;
        $paramDestroy['domainCode'] = $ItalprotClient->getDomain();
        $ret = $ItalprotClient->ws_DestroyItaEngineContextToken($paramDestroy);
        if (!$ret) {
            /*
             * Errori e Fault già intercettati nel ws
             */
        }

        return $ritorno;
    }

    /**
     * 
     */
    public function AggiungiAllegati() {
        return;
    }

}

?>