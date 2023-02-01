<?php

/**
 *
 * Raccolta di funzioni per il web service rest protocollo 
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft Srl
 * @license
 * @version    02.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proProtocollo.class.php');

class proWsRestAgent {

    private $proLib;
    private $PROT_DB;
    private $eqAudit;
    private $abbinaPath;
    private $errCode;
    private $errMessage;

    function __construct() {
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->eqAudit = new eqAudit();
            $this->abbinaPath = $this->proLib->SetDirectory('', "ABBINA");
        } catch (Exception $e) {
            
        }
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
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_GENERIC_ERROR,
            'Estremi' => $errMessage)
        );
    }

    /**
     * 
     * @param array $params
     * @return boolean
     */
    public function salvaAllegatoProtocollo($params) {

        /*
         * Temporaneo log
         * 
         */
        $loggerFile = 'C:/cityware.online/tmp/scansione_massiva_' . App::$utente->getKey('ditta') . '.log';
        file_put_contents($loggerFile, print_r($params, true) . "\n\n\n", FILE_APPEND);

        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_MISC_AUDIT,
            'Estremi' => 'Inizio elaborazione richiesta salvataggio allegato.')
        );

        /*
         * ATTENZIONE: Se esiste almeno il file, salvare sul repository
         * 
         */
        if (
                !$params['idProtocollo'] ||
                !$params['fileName'] ||
                $params['numeroPagine'] == 0 ||
                !$params['codiceEnte']
        ) {
            $this->setErrCode('Error');
            $this->setErrMessage('Parametri mancanti o incompleti');
            if (file_exists($params['FILES']['attachment']['tmp_name'])) {
                $this->salvaSuRepository($params, 'PARAMETR ABBINAMENTO FILE INCOMPLETI MA FILE ESISTENTE');
            }
            return false;
        }

        if (!file_exists($params['FILES']['attachment']['tmp_name'])) {
            $this->setErrCode('Error');
            $this->setErrMessage('File binario allegato non trovato');
            return false;
        }

        if ($params['idProtocollo'] == '-1'){
            $this->salvaSuRepository($params, 'BARCODE NON RICONOSCIUTO O MANCANTE NEL DOCUMENTO');
            return true;
        }
        
        $key_anapro = "ROWID";
        $id_anapro = $params['idProtocollo'];
        
        //@TODO: INSERIRE REGEXP
        if (substr($id_anapro, 0, 1) === '@' && substr($id_anapro, -3) === '@**') {
            /*
             * Utilizzo PRONUM
             * 
             */
            $key_anapro = "PRONUM";
            $tipo_anapro = substr($id_anapro, 1, 1);
            if ($tipo_anapro === 'P') {
                $tipo_anapro = '';
            }
            $id_anapro = str_replace('@**', '', substr($id_anapro, 2));
            $anno_anapro = substr($id_anapro, 0, 4);
            $numero_anapro = substr($id_anapro, 4);
        }

        /*
         * Controllo se il protocollo è in manutenzione
         * Ma qui cosa fare....?
         * 1 - se rifiuto la chiamata la scansione è persa e nessuno lo sa?
         * 2 - se procedo potrebbero mancare i dati e avere dati parziali su cui elaborare
         * 3 - metto su repository abbina allegati........ può funzionare. 
         * 
         */
        $ret_Manutenz = $this->checkManutenzione();
        if ($ret_Manutenz) {
            $this->setErrCode('Warning');
            $this->setErrMessage('Il protocollo è nello stato di manutenzione. Allegato passato al repository di abbinamento.');
            $this->salvaSuRepository($params, 'PROTOCOLLO IN MANUTENZIONE GESTIONI NON CONSENTITE.');
            return false;
        }

        file_put_contents($loggerFile, print_r($key_anapro . '-' . $id_anapro, true) . "\n\n\n", FILE_APPEND);

        $model = 'proProtocollo.class';
        itaModelHelper::requireAppFileByName($model);
        /* @var $protocollo proProtocollo */
        $protocollo = null;
        switch ($key_anapro) {
            case 'ROWID':
                $protocollo = proProtocollo::getInstanceForRowid($this->proLib, $id_anapro);
                break;
            case 'PRONUM':
                if ($tipo_anapro) {
                    $protocollo = proProtocollo::getInstance($this->proLib, $numero_anapro, $anno_anapro, $tipo_anapro);
                } else {
                    $tipo_anapro = 'A';
                    $protocollo = proProtocollo::getInstance($this->proLib, $numero_anapro, $anno_anapro, $tipo_anapro);
                    if (!$protocollo || !$protocollo->getAnapro_rec()) {
                        $tipo_anapro = 'P';
                        $protocollo = proProtocollo::getInstance($this->proLib, $numero_anapro, $anno_anapro, $tipo_anapro);
                    }
                }
                break;
        }

        if (!$protocollo || !$protocollo->getAnapro_rec() ) {
            $this->setErrCode('Error');
            $this->setErrMessage('Oggetto Protocollo non istanziato: protocollo non accessibile.');
            $this->salvaSuRepository($params, 'Oggetto Protocollo non istanziato: protocollo non accessibile.');
            return false;
        }
        $Anapro_rec = $protocollo->getAnapro_rec();
        file_put_contents($loggerFile, print_r($Anapro_rec, true) . "\n\n\n", FILE_APPEND);

        $DatiConservazione = $protocollo->getDatiConservazione();
        if ($DatiConservazione) {
            $this->setErrCode('Error');
            $this->setErrMessage('Il protocollo è in conservazione. Non è possibile apportare modifiche.');
            $this->salvaSuRepository($params, 'PROTOCOLLO IN CONSERVAZIONE NON E POSSIBILE APPORTARE MODIFICHE');
            return false;
        }

        $Allegati_tab = $protocollo->getAllegati_tab();
        if (count($Allegati_tab) > 0) {
            $this->setErrCode('Error');
            $this->setErrMessage('Allegati già presenti per il protocollo.');
            $this->salvaSuRepository($params, 'ALLEGATI GIA PRESENTI NEL PROTOCOLLO ABBINAMENTO MANUALE NECESSARIO.');
            return false;
        }

        $stream = base64_encode(file_get_contents($params['FILES']['attachment']['tmp_name']));

        /*
         *  Preparo Elementi di Base
         * 
         */
        $elementi = array();
        $elementi['tipo'] = 'A';
        $elementi['dati'] = array();

        $elementi['allegati']['Principale'] = array();
        $elementi['allegati']['Principale']['Nome'] = $params['fileName'];
        $elementi['allegati']['Principale']['Descrizione'] = 'Scansione Massiva';
        $elementi['allegati']['Principale']['Stream'] = $stream;

        /**
         * Istanza Oggetto ProtoDatiProtocollo
         */
        $model = 'proDatiProtocollo.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode('Error');
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            $this->salvaSuRepository($params, 'ERRORE DI PREPARAZIONE  DATI PER IL PROTOCOOLLO: ' . $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }

        /**
         * Utilizzo il protocollatore per aggiungere l'allegato.
         */
        $model = 'proProtocolla.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proProtocolla = new proProtocolla();

        $motivo = 'Aggiunta allegato da scansione massiva. ';
        $addAllegato = $proProtocolla->aggiungiAllegati($Anapro_rec['PROPAR'], $motivo, $proDatiProtocollo, $Anapro_rec['PRONUM']);
        if (!$addAllegato) {
            $this->setErrCode('Error');
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            $this->salvaSuRepository($params, 'ERRORE DI INSERIMENTO ALLEGATO: ' . $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }

        $retRitorno = $proProtocolla->getRisultatoRitorno();
        $rowdAllegato = $retRitorno['ROWIDAGGIUNTI'][0];
        $anadoc_rec = $this->proLib->GetAnadoc($rowdAllegato, 'rowid', false);
        if (!$anadoc_rec) {
            $this->setErrCode('Error');
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            $this->salvaSuRepository($params, 'ERRORE DI ACCESSO ALL\'ALLEGATO: '  . $proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        return true;
    }

    /**
     * 
     * @return type
     */
    private function checkManutenzione() {
        $model = 'proLib.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proLib = new proLib();
        return $proLib->checkStatoManutenzione();
    }

    /**
     * 
     * @param type $param
     * @param type $modo
     * @return boolean
     */
    private function salvaSuRepository($param, $modo) {

        $tmpName = $param['FILES']['attachment']['tmp_name'];
        $name = $param['FILES']['attachment']['name'];
        $newName = $this->abbinaPath . "/"
                . pathinfo($name, PATHINFO_FILENAME)
                . '_' . itaLib::getRandBaseName();
        $newName_allegato = $newName . '.' . pathinfo($name, PATHINFO_EXTENSION);
        $newName_info = $newName . '.json';
        if (!rename($tmpName, $newName_allegato)) {
            $this->setErrCode('Error');
            $this->setErrMessage('Spostamento allegato ' . $params['FILES']['attachment']['name'] . 'su repository di abbinameno fallita.');
            return false;
        }

        $extraInfo = array(
            'extraInfo' => array(
                'data_acquisizione' => date('d/m/Y'),
                'ora_acquisizione' => date('H:i:s'),
                'messaggio_acquisizione' => $modo
            )
        );

        $arrayInfo = array_merge($param, $extraInfo);
        file_put_contents($newName_info, json_encode($arrayInfo));
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_MISC_AUDIT,
            'Estremi' => "Allegato $name salvato su repository dki abbinamento")
        );
        return true;
    }

}
