<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Andimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    05.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Mail/emlLib.class.php');

class emlMailPackage {

    private $packageId;
    private $envelopeId;
    private $resourcePath;
    public $ITALWEB;
    public $emlLib;

    function __construct() {
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function getPackageId() {
        return $this->packageId;
    }

    function setPackageId($packageId) {
        $this->packageId = $packageId;
    }

    function getEnvelopeId() {
        return $this->envelopeId;
    }

    /**
     * Crea l'ambiente per nuovo package e assegna l'ID attribuito
     * 
     * @param type $params
     * @return boolean
     */
    public function createEnvelopesPackage($params = array()) {
        if (!$params) {
            $this->setErrMessage('Parametri mancanti.');
            return false;
        }
        //
        $mail_packages = array();
        $mail_packages['PKGDATE'] = date('Ymd');
        $mail_packages['PKGTIME'] = date('H:i:s');
        $mail_packages['PKGMAILACCOUNT'] = $params['mailaccount'];
        $mail_packages['PKGNOTE'] = $params['note'];
        $mail_packages['PKGAPPCONTEXT'] = $params['appcontext'];
        $mail_packages['PKGAPPKEY'] = $params['appkey'];
        $mail_packages['PKGFLAGACTIVATION'] = 0;
        $mail_packages['CODUTE'] = App::$utente->getKey('nomeUtente');
        $mail_packages['DATAOPER'] = date('Ymd');
        $mail_packages['TIMEOPER'] = date('H:i:s');
        $mail_packages['FLAG_DIS'] = 0;
        try {
            ItaDB::DBInsert($this->ITALWEB, 'MAIL_PACKAGES', 'ROW_ID', $mail_packages);
        } catch (Exception $exc) {
            $this->setErrMessage('Errore di registrazione su MAIL_PACKAGES.');
            return false;
        }
        $envelopesPackageId = trim(ItaDB::DBLastId($this->ITALWEB));
        //
        $retCreateWorkDirectory = $this->emlLib->SetDirectorySpooler($envelopesPackageId);
        if (!$retCreateWorkDirectory) {
            $result = $this->destroyEnvelopesPackageId($envelopesPackageId);
            if ($result === false) {
                return false;
            }
            $this->setErrMessage('Errore nella creazione della cartella per il package ' . $envelopesPackageId);
            return false;
        }
        $this->packageId = $envelopesPackageId;
        return true;
    }

    /**
     * Cancella dal DB il record relativo al package inserito da createEnvelopesPackage
     * 
     * @param type $envelopesPackageId
     * @return boolean
     */
    public function destroyEnvelopesPackageId($envelopesPackageId, $destroyChilds = false) {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE ROW_ID = $envelopesPackageId";
        $mail_packages = ItaDB::DBSQLSelect($this->ITALWEB, $sql, FALSE);
        if (!$mail_packages) {
            $this->setErrMessage('Errore nella query per cancellazione su MAIL_PACKAGES con row_id = ' . $envelopesPackageId);
            return false;
        }
        try {
            ItaDB::DBDelete($this->ITALWEB, 'MAIL_PACKAGES', "ROW_ID", $mail_packages['ROW_ID']);
        } catch (Exception $exc) {
            $this->setErrMessage('Errore nella cancellazione su MAIL_PACKAGES con row_id = ' . $envelopesPackageId);
            return false;
        }

        if ($destroyChilds) {
            // cancella anche gli envelopes collegati
            $sql = "DELETE FROM MAIL_ENVELOPES WHERE PACKAGES_ROWID = $envelopesPackageId";
            $this->ITALWEB->query($sql);

            $this->destroyPackageEnvelopes();
        }
        return true;
    }

    /**
     * Aggiunge un envelope al packgage
     * 
     * @param type $envelopeObj
     * @return boolean
     */
    public function addEnvelopeToPackage($envelopeObj) {
        $XMLSTring = $envelopeObj->getEnvelopeDataXML();
        if (!$XMLSTring) {
            $this->setErrMessage('Errore nella struttura dell\'XML.');
            return false;
        }
        if (!$this->packageId) {
            $this->setErrMessage('pakageId non dichiarato.');
            return false;
        }

        $fileID = $this->createEnvelopeId();
        $filePath = $this->getPathPackage();

        if (file_put_contents($filePath . "/" . $fileID . ".xml", $XMLSTring) === false) {
            $this->setErrMessage('Errore di salvataggio dell\'XML nella busta di lavoro');
            return false;
        }

        $toAddresses = $envelopeObj->getToAddresses();

        $mail_envelopes = array();
        $mail_envelopes['PACKAGES_ROWID'] = $this->packageId;
        $mail_envelopes['EVPDATE'] = date('Ymd');
        $mail_envelopes['EVPTIME'] = date('H:i:s');
        $mail_envelopes['EVPMAIL_ROWID'] = 0;
        $mail_envelopes['EVPMAILTO'] = $toAddresses[0];
        $mail_envelopes['EVPMAIL_ID'] = '';
        $mail_envelopes['EVPXMLDATA'] = $fileID;
        $mail_envelopes['EVPSTATUS'] = $envelopeObj->getEvpStatus() ? $envelopeObj->getEvpStatus() : 0;
        $mail_envelopes['EVPLASTMESSAGE'] = '';
        $mail_envelopes['CODUTE'] = App::$utente->getKey('nomeUtente');
        $mail_envelopes['DATAOPER'] = date('Ymd');
        $mail_envelopes['TIMEOPER'] = date('H:i:s');
        $mail_envelopes['FLAG_DIS'] = 0;
        try {
            ItaDB::DBInsert($this->ITALWEB, 'MAIL_ENVELOPES', 'ROW_ID', $mail_envelopes);
            $envelopeId = trim(ItaDB::DBLastId($this->ITALWEB));
            $this->envelopeId = $envelopeId;
        } catch (Exception $exc) {
            $result = $this->destroyEnvelope($fileID);
            if ($result === false) {
                return false;
            }
            $this->setErrMessage('Errore di registrazione su MAIL_ENVELOPES.');
            return false;
        }

        return true;
    }

    /**
     * Funzione per generare l'ID del file con impronta digitale
     * 
     * @return type
     */
    private function createEnvelopeId() {
        return hash('sha256', App::$utente->getKey('TOKEN') . uniqid());
    }

    /**
     * Funzione per leggere il repository delle cartelle package
     * 
     * @return type
     */
    public function getPathPackage() {
        return Config::getPath('general.itaMailSpooler') . 'ente' . App::$utente->getKey('ditta') . '/' . $this->packageId;
    }

    /**
     * Funzione di cancellazione di una envelope all'interno di un package
     * 
     * @param type $fileID
     * @return boolean
     */
    private function destroyEnvelope($fileID) {
        if (!unlink($this->getPathPackage() . '/' . $fileID . '.xml')) {
            $this->setErrMessage('Errore nella cancellazione del file ' . $this->getPathPackage() . '/' . $fileID . '.xml');
            return false;
        }
        return true;
    }

    /**
     * Funzione di cancellazione di una envelope all'interno di un package
     * 
     * @param type $fileID
     * @return boolean
     */
    private function destroyPackageEnvelopes() {
        array_map('unlink', glob($this->getPathPackage() . "/*.*"));

        if (!rmdir($this->getPathPackage())) {
            $this->setErrMessage('Errore nella cancellazione della cartella ' . $this->getPathPackage());
            return false;
        }
        return true;
    }

    /**
     * Metodo da chiamare al termine dell'invio di envelopes nel package
     * 
     * @return boolean
     */
    public function closeEnvelopesPackage() {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE ROW_ID = $this->packageId";
        $mail_packages = ItaDB::DBSQLSelect($this->ITALWEB, $sql, FALSE);
        $mail_packages['PKGCLOSEDATE'] = date('Ymd');
        $mail_packages['PKGCLOSETIME'] = date('H:i:s');
        try {
            ItaDB::DBUpdate($this->ITALWEB, 'MAIL_PACKAGES', 'ROW_ID', $mail_packages);
        } catch (Exception $exc) {
            $this->setErrMessage('Errore in chiusura della MAIL_PACKAGES ' . $this->packageId);
            return false;
        }
        return true;
    }

    /**
     * Metodo per attivare un package all'invio mail 
     * 
     * @return boolean
     */
    public function activateEnvelopesPackage() {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE ROW_ID = $this->packageId";
        $mail_packages = ItaDB::DBSQLSelect($this->ITALWEB, $sql, FALSE);
        $mail_packages['PKGFLAGACTIVATION'] = 1;
        try {
            ItaDB::DBUpdate($this->ITALWEB, 'MAIL_PACKAGES', 'ROW_ID', $mail_packages);
        } catch (Exception $exc) {
            $this->setErrMessage('Errore in attivazione della MAIL_PACKAGES ' . $this->packageId);
            return false;
        }
        return true;
    }

    /**
     *  Metodo per sospendere l'invio della mail per un package 
     * 
     * @return boolean
     */
    public function suspendEnvelopesPackage() {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE ROW_ID = $this->packageId";
        $mail_packages = ItaDB::DBSQLSelect($this->ITALWEB, $sql, FALSE);
        $mail_packages['PKGFLAGACTIVATION'] = 0;
        try {
            ItaDB::DBUpdate($this->ITALWEB, 'MAIL_PACKAGES', 'ROW_ID', $mail_packages);
        } catch (Exception $exc) {
            $this->setErrMessage('Errore in sospensione della MAIL_PACKAGES ' . $this->packageId);
            return false;
        }
        return true;
    }

    public function getPackageStatus($params) {
        if (!$params) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Parametri non dichiarati.',
                'return' => array()
            );
            return $toReturn;
        }
        if ($params['PACKAGEID'] == '') {
            $toReturn = array(
                'esito' => false,
                'message' => 'Parametro PACKAGEID non dichiarato.',
                'return' => array()
            );
            return $toReturn;
        }
        $package = $this->emlLib->getPackage($params['PACKAGEID']);
        if (!$package) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Package ID ' . $params['PACKAGEID'] . ' non trovato.',
                'return' => array()
            );
            return $toReturn;
        }
        $envelopes = $this->emlLib->getMailEnvelopes($package['ROW_ID']);
        $allEVP = count($envelopes);
        $sentEVP = $unSentEVP = 0;
        foreach ($envelopes as $envelope) {
            if ($envelope['EVPMAIL_ROWID'] != 0 ? $sentEVP++ : $unSentEVP ++)
                ;
            $evpDetail = array();
            if ($params['DETAILEVP']) {
                switch ($params['DETAILLEVEL']) {
                    case 1:
                        $evpDetail['ROW_ID'] = $envelope['ROW_ID'];
                        break;
                    case 2:
                        $evpDetail['ROW_ID'] = $envelope['ROW_ID'];
                        $evpDetail['STATUS'] = $envelope['EVPSTATUS'];
                        break;
                    case 3:
                        $evpDetail['ROW_ID'] = $envelope['ROW_ID'];
                        $evpDetail['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $evpDetail['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];
                        break;
                    default :
                        $evpDetail['ROW_ID'] = $envelope['ROW_ID'];
                        break;
                }
                $returnEvp[] = $evpDetail;
            }
        }
        $result = array();
        $result = $package;
        $result['COUNTEVP'] = $allEVP;
        $result['SENTEVP'] = $sentEVP;
        $result['UNSENTEVP'] = $unSentEVP;
        if ($params['DETAILEVP']) {
            $result['DETAILEVP'] = $returnEvp;
        }

        $toReturn = array(
            'esito' => true,
            'message' => 'Status del Package ID ' . $params['PACKAGEID'],
            'return' => $result
        );
        return $toReturn;
    }

}
