<?php

/**
 *
 * LIBRERIA PER GESTIONE CHIAMATE A PROTOCOLLI DI TERZE PARTI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    07.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praWSClientManager extends itaModel {

    const CLIENT_PALEO = "Paleo";
    const CLIENT_PALEO4 = "Paleo4";
    const CLIENT_INFOR = "Infor";
    const CLIENT_WSPU = "WSPU";
    const CLIENT_IRIDE = "Iride";
    const CLIENT_JIRIDE = "Jiride";
    const CLIENT_HYPERSIC = "HyperSIC";
    const CLIENT_ELIOS = "E-Lios";
    const CLIENT_ITALPROT = "Italsoft-ws";
    const CLIENT_KIBERNETES = "Kibernetes";
    const CLIENT_CIVILIANEXT = "CiviliaNext";

    private $clientType;
    private $clientObj;
    private $keyPasso;
    private $currGesnum;
    private $arrayDoc;
    private $arrayDocRicevute;
    private $retClient;
    private $errMessage;
    private $errCode;
    private $message;

    public static function getInstance($driver) {
        if (!$driver) {
            return false;
        }
        try {
            switch ($driver) {
                case self::CLIENT_PALEO:
                    $model = 'proPaleo.class';
                    break;
                case self::CLIENT_PALEO4:
                    $model = 'proPaleo4.class';
                    break;
                case self::CLIENT_WSPU:
                    $model = 'proHWS.class';
                    break;
                case self::CLIENT_INFOR:
                    $model = 'proInforJProtocollo.class';
                    break;
                case self::CLIENT_IRIDE:
                    $model = 'proIride.class';
                    break;
                case self::CLIENT_JIRIDE:
                    $model = 'proJiride.class';
                    break;
                case self::CLIENT_HYPERSIC:
                    $model = 'proHyperSIC.class';
                    break;
                case self::CLIENT_ELIOS:
                    $model = 'proELios.class';
                    break;
                case self::CLIENT_ITALPROT:
                    $model = 'proItalprot.class';
                    break;
                case self::CLIENT_KIBERNETES:
                    $model = 'proKibernetesProt.class';
                    break;
                case self::CLIENT_CIVILIANEXT:
                    $model = 'proCiviliaNext.class';
                    break;
                default:
                    return false;
            }
            list($classe, $kip) = explode(".", $model);
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

            $managerObj = new praWSClientManager();
            $managerObj->clientObj = new $classe();
            $managerObj->clientType = $driver;
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    function getKeyPasso() {
        return $this->keyPasso;
    }

    function getCurrGesnum() {
        return $this->currGesnum;
    }

    function getArrayDoc() {
        return $this->arrayDoc;
    }

    function getArrayDocRicevute() {
        return $this->arrayDocRicevute;
    }

    function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    function setCurrGesnum($currGesnum) {
        $this->currGesnum = $currGesnum;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    /**
     * 
     * @param type $errCode
     */
    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function loadAllegatiFromComunicazioneComP($downloadB64 = true, $idScelti = array()) {
        $this->arrayDoc = $this->loadAllegatiFromComunicazione("P", $downloadB64, $idScelti);
    }

    public function loadAllegatiFromComunicazioneComA($idScelti, $downloadB64 = true) {
        $this->arrayDoc = $this->loadAllegatiFromComunicazione("P", $downloadB64, $idScelti);
    }

    public function loadAllegatiFromComunicazionePratica($downloadB64 = true) {
        
    }

    public function loadAllegatiFromRicevutePartenza($downloadB64 = true) {
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
        $this->arrayDocRicevute = $praFascicolo->getRicevutePartenza($this->clientType, $downloadB64);
    }

    public function loadAllegatiFromComunicazione($tipo, $downloadB64 = true, $idScelti = array()) {
        //chiamaprafascicolo
        $praFascicolo = new praFascicolo($this->currGesnum);
        $praFascicolo->setChiavePasso($this->keyPasso);
        return $praFascicolo->getAllegatiProtocollaComunicazione($this->clientType, $downloadB64, $tipo, $idScelti);
    }

    /**
     * 
     * @return boolean
     */
    public function AggiungiAllegati() {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Documenti protocollati con successo!";
        $ritorno["RetValue"] = true;
        //
        $this->retClient = $arrayDocFiltrati = array();
        $strNoProt = "";
        $praLib = new praLib();
        $pracom_recP = $praLib->GetPracomP($this->keyPasso);
        //
        switch ($this->clientType) {
            case self::CLIENT_PALEO4:
                $praFascicolo = new praFascicolo();
                $arrayDocFiltrati = $praFascicolo->GetAllegatiNonProt($this->arrayDoc, $this->clientType);
                $this->arrayDoc = $arrayDocFiltrati['arrayDoc'];
                $param = array();
                $Metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                $param['DocNumber'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
                $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
//                if ($param['DocNumber']) {
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
//                } else {
//                    $ritorno = array();
//                    $ritorno["Status"] = "-1";
//                    $ritorno["Message"] = "Numero collegamento protocollo remoto mancante!";
//                    $ritorno["RetValue"] = false;
//                    return $ritorno;
//                    return $ritorno;
//                }
                break;
            case self::CLIENT_IRIDE:
                $param = array();
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
//                if ($param['DocNumber']) {
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
//                } else {
//                    $ritorno = array();
//                    $ritorno["Status"] = "-1";
//                    $ritorno["Message"] = "Numero collegamento protocollo remoto mancante!";
//                    $ritorno["RetValue"] = false;
//                    return $ritorno;
//                }
                break;
            case self::CLIENT_JIRIDE:
                $param = array();
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
//                if ($param['DocNumber']) {
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
//                } else {
//                    $ritorno = array();
//                    $ritorno["Status"] = "-1";
//                    $ritorno["Message"] = "Numero collegamento protocollo remoto mancante!";
//                    $ritorno["RetValue"] = false;
//                    return $ritorno;
//                }
                break;
            case self::CLIENT_HYPERSIC:
                $param = array();
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                if ($param['DocNumber']) {
                    $this->retClient = $this->clientObj->AggiungiAllegati($param);
                } else {
                    return $ritorno;
                }
                break;
            case self::CLIENT_ITALPROT:
                $param = array();
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                $param['tipo'] = "P";
                //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
                break;
            case self::CLIENT_KIBERNETES:
                $param = array();
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                $param['tipo'] = "P";
                $metadati = unserialize($pracom_recP['COMMETA']);
                $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
                break;
            case self::CLIENT_CIVILIANEXT:
                $param = array();
                $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                $param['arrayDoc'] = $this->arrayDoc;
                $param['tipo'] = "P";
                $this->retClient = $this->clientObj->AggiungiAllegati($param);
                break;
        }

        if ($arrayDocFiltrati['strNoProt']) {
            $strNoProt = "<br>" . $arrayDocFiltrati['strNoProt'];
        }

        if ($this->retClient["Status"] == "-1") {
            $ritorno["Status"] = "-1";
            $ritorno["RetValue"] = false;
        }
        $ritorno["Message"] = $this->retClient['Message'] . "$strNoProt";
        return $ritorno;
    }

    public function AggiungiRicevute() {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Ricevute protocollate con successo!";
        $ritorno["RetValue"] = true;
        //
        $this->retClient = array();
        $praLib = new praLib();
        $pracom_recP = $praLib->GetPracomP($this->keyPasso);
        //
        $errDetails = $retDetails = array();
        $errBlocca = $retBlocca = array();
        foreach ($this->arrayDocRicevute['pramail_rec'] as $key => $pramail_rec) {
            /*
             * ricrea un array con un solo allegato : $arrayDocRicevuta
             */
            $arrayDocRicevuta = array();
            $arrayDocRicevuta["pramail_rec"][$key] = $pramail_rec;
            $arrayDocRicevuta["Ricevute"][$key] = $this->arrayDocRicevute['Ricevute'][$key];
            /*
              $arrayDocRicevuta['Ricevute'][1]['filePath'] = ""; //Test per far andare in errore la singola ricevuta con $key = 1
             */
            //
            switch ($this->clientType) {
                case self::CLIENT_PALEO4:
                    $param = array();
                    $Metadati = unserialize($pracom_recP['COMMETA']);
                    $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                    $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                    $param['arrayDocRicevute'] = $arrayDocRicevuta;
                    //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                    $param['DocNumber'] = $Metadati['DatiProtocollazione']['DocNumber']['value'];
                    $param['Segnatura'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
                    $this->retClient = $this->clientObj->AggiungiAllegati($param);
                    break;
                case self::CLIENT_IRIDE:
                    $param = array();
                    $metadati = unserialize($pracom_recP['COMMETA']);
                    $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                    $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                    $param['arrayDocRicevute'] = $arrayDocRicevuta;
                    //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                    $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                    $this->retClient = $this->clientObj->AggiungiAllegati($param);
                    break;
                case self::CLIENT_JIRIDE:
                    $param = array();
                    $metadati = unserialize($pracom_recP['COMMETA']);
                    $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                    $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                    $param['arrayDocRicevute'] = $arrayDocRicevuta;
                    //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                    $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                    $this->retClient = $this->clientObj->AggiungiAllegati($param);
                    break;
                case self::CLIENT_HYPERSIC:
                    $param = array();
                    $metadati = unserialize($pracom_recP['COMMETA']);
                    $param['NumeroProtocollo'] = substr($pracom_recP['COMPRT'], 4);
                    $param['AnnoProtocollo'] = substr($pracom_recP['COMDPR'], 0, 4);
                    $param['arrayDocRicevute'] = $arrayDocRicevuta;
                    //$param['arrayDocRicevute'] = $this->arrayDocRicevute;
                    $metadati = unserialize($pracom_recP['COMMETA']);
                    $param['DocNumber'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                    $this->retClient = $this->clientObj->AggiungiAllegati($param);
                    break;
            }
            if ($this->retClient["Status"] == "-1") {
                //$errDetails[] = "<div class=\"ui-state-error ui-corner-all\" style=\"padding:2px;margin-bottom:1px;\">- Errore durante l'aggiunta della ricevuta: <b>" . $arrayDocRicevuta['Ricevute'][$key]['nomeFile'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "---><span style=\"color:red;\"><b>" . $this->retClient['Message'] . "</b></span></div>";
                $errDetails[] = "Errore durante la protocollazione della ricevuta: " . $arrayDocRicevuta['Ricevute'][$key]['nomeFile'] . " con prot. N. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "--->" . $this->retClient['Message'];
            } else {
                //$retDetails[] = "<div style=\"padding:2px;\">- Aggiunta correttamente la ricevuta: <b>" . $arrayDocRicevuta['Ricevute'][$key]['nomeFile'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "</div>";
                $retDetails[] = "Protocollata ricevuta: " . $arrayDocRicevuta['Ricevute'][$key]['nomeFile'] . " con prot. N. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'];
                $returnBlocca = $this->bloccaRicevute($arrayDocRicevuta['pramail_rec']);
                if ($returnBlocca['StatusBlocca'] == "-1") {
                    $errBlocca[] = $returnBlocca['ErrBlocca'];
                } else {
                    $retBlocca[] = $returnBlocca['RetBlocca'];
                }
            }
        }
        if ($errDetails) {
            $ritorno["Status"] = "-1";
            $ritorno["RetValue"] = false;
        }
        //$ritorno["Message"] = $this->retClient['Message'];
        $ritorno["ErrDetails"] = $errDetails;
        $ritorno["RetDetails"] = $retDetails;
        $ritorno['ErrBlocca'] = $errBlocca;
        $ritorno['RetBlocca'] = $retBlocca;
        return $ritorno;
    }

    public function bloccaRicevute($rowidArr = array()) {
        $retBlocca = array();
        $praLib = new praLib();
        if (!$rowidArr) {
            return;
        }
        foreach ($rowidArr as $key => $rowid) {
            $pramail_rec = $praLib->getPraMail($rowid['ROWID'], 'ROWID');
            $pramail_rec['FLPROT'] = 1;
            try {
                $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), 'PRAMAIL', 'ROWID', $pramail_rec);
                if ($nrow == -1) {
                    $this->setErrCode(-1);
                    //$retBlocca["ErrBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " fallito.";
                    $retBlocca["ErrBlocca"] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " fallito.";
                    $retBlocca["StatusBlocca"] = "-1";
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                //$retBlocca["ErrBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " fallito -->" . $e->getMessage();
                $retBlocca["ErrBlocca"] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " fallito -->" . $e->getMessage();
                $retBlocca["StatusBlocca"] = "-1";
            }
            //$retBlocca["RetBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " avvenuto correttamente.";
            $retBlocca["RetBlocca"] = "blocco ricevuta " . $pramail_rec['TIPORICEVUTA'] . "-" . $pramail_rec['ROWID'] . " avvenuto correttamente.";
            $retBlocca["StatusBlocca"] = "0";

            /*
             * Auditing operazione
             */
            if ($retBlocca["StatusBlocca"] == "-1") {
                //$estremi = $retBlocca["ErrBlocca"][$key];
                $estremi = $retBlocca["ErrBlocca"];
            } else {
                //$estremi = $retBlocca["RetBlocca"][$key];
                $estremi = $retBlocca["RetBlocca"];
            }
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $praLib->getPRAMDB()->getDB(),
                'DSet' => 'PRAMAIL',
                'Estremi' => $estremi
            ));
        }
        return $retBlocca;
    }

}

?>