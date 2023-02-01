<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    05.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlWsManager.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModel.class.php';

class emlSpoolManager extends itaModel {

    const PKG_ACTIVATION_STATUS_INACTIVE = 0;
    const PKG_ACTIVATION_STATUS_ACTIVE = 1;
    const PKG_ACTIVATION_STATUS_DONE = 2;
    const EVP_STATUS_WAITING = 0;
    const EVP_STATUS_SENT_DONE = 1;
    const EVP_STATUS_SENT_INVIO_ESTERNO = 2;
    const EVP_STATUS_SENT_INVIO_NON_INVIARE = 0;
    const EVP_STATUS_SENT_ERROR = 999;
    const EVP_STATUS_GENERAL_ERROR = 998;

    private $errCode;
    private $errMessage;
    public $ITALWEB;
    public $emlLib;

    function __construct() {
        parent::__construct();
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    /*
     * Restituisce un oggetto pacchetto mail da inviare
     */

    /**
     * Restituisce un oggetto pacchetto mail da inviare
     * 
     * @param type $parameters
     * @return boolean|\className
     */
    public static function getPackageInstance($parameters = array()) {

        $className = "emlMailPackage";

        include_once(ITA_BASE_PATH . "/apps/Mail/$className.class.php");
        try {
            $objClient = new $className($parameters);
        } catch (Exception $ex) {
            return false;
        }


        return $objClient;
    }

    public static function getEnvelopeInstance($parameters = array()) {

        $className = "emlMailEnvelope";

        include_once(ITA_BASE_PATH . "/apps/Mail/$className.class.php");
        try {
            $objClient = new $className($parameters);
        } catch (Exception $ex) {
            return false;
        }


        return $objClient;
    }

    public function parsePackageQueue($id = '') {
        if ($id != '') {
            $tabMail_Packages = $this->emlLib->getMailPackageByID($id, true);
        } else {
            $tabMail_Packages = $this->emlLib->getMailPackages();
        }

        if (!$tabMail_Packages) {
            $toReturn = array(
                'esito' => false,
                'return' => array()
            );
            return $toReturn;
        }
        $toReturn = array();
        $filters = array(
            'EVPMAIL_ROWID' => 0,
            'FLAG_DIS' => 0
        );
        foreach ($tabMail_Packages as $recMail_Packages) {
            $tabMail_Envelopes = $this->emlLib->getMailEnvelopes($recMail_Packages['ROW_ID'], $filters);

            $toReturn[$recMail_Packages['ROW_ID']] = array();
            foreach ($tabMail_Envelopes as $envelope) {
                if ($envelope['EVPMAIL_ROWID'] == 0 && $envelope['FLAG_DIS'] == 0) {
                    $pathFileXML = $this->emlLib->SetDirectorySpooler($recMail_Packages['ROW_ID']);
                    $fileXML = $pathFileXML . $envelope['EVPXMLDATA'] . '.xml';
                    if (!file_exists($fileXML)) {
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} Errore generale: file xml non trovato";
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_GENERAL_ERROR;
                        $envelope['EVPLASTMESSAGE'] = 'File XML non trovato - package ' . $envelope['PACKAGES_ROWID'];

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = 0;
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = '';
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];

                        if (!$this->updateRecord($this->ITALWEB, 'MAIL_ENVELOPES', $envelope, $update_Info, 'ROW_ID')) {
                            continue;
                        }
                        continue;
                    }
                    $params['MESSAGEDATA'] = file_get_contents($fileXML);
                    if ($params['MESSAGEDATA'] == '') {
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} Errore generale: file xml vuoto";
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_GENERAL_ERROR;
                        $envelope['EVPLASTMESSAGE'] = 'File XML vuoto';

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = 0;
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = '';
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];

                        if (!$this->updateRecord($this->ITALWEB, 'MAIL_ENVELOPES', $envelope, $update_Info, 'ROW_ID')) {
                            continue;
                        }
                        continue;
                    }
                    $obj = new emlWsManager();
                    if (!$obj) {
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} Errore generale: classe emlWsAmanager non istanziata";
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_GENERAL_ERROR;
                        $envelope['EVPLASTMESSAGE'] = 'classe emlWsManager non istanziata';

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = 0;
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = '';
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];

                        if (!$this->updateRecord($this->ITALWEB, 'MAIL_ENVELOPES', $envelope, $update_Info, 'ROW_ID')) {
                            continue;
                        }
                        continue;
                    }
                    $retEnveloped = $obj->sendMail($params);
                    //Out::msgInfo('$retEnveloped', print_r($retEnveloped, true));
                    //Out::msgInfo('errmsg',$obj->getErrMessage());
                    $detailedInfo = $retEnveloped['detailedInfo'][0];
                    if ($retEnveloped['status'] !== true) {
                        if (!$detailedInfo) {
                            /*
                             * Se c'è un errore in fase di preparazione dell'ambiente non ritorna l'array detailedInfo dell'envelope
                             * L'errore è settato dall'oggetto $obj
                             */
                            $envelope['EVPLASTMESSAGE'] = $obj->getErrMessage();
                        } else {
                            /*
                             * se il sendMail viene eseguito controllo il risultato dell'invio dell'envelope
                             */
                            $envelope['EVPLASTMESSAGE'] = $detailedInfo['message'];
                        }
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} Errore generale: " . $envelope['EVPLASTMESSAGE'];
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_GENERAL_ERROR;

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = 0;
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = '';
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];

                        if (!$this->updateRecord($this->ITALWEB, 'MAIL_ENVELOPES', $envelope, $update_Info, 'ROW_ID')) {
                            continue;
                        }
                        continue;
                    }
                    /*
                     * se l'envelope è stato elaborato verifico il risultato dell'invio 
                     */
                    if ($detailedInfo['status'] !== false) {
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_SENT_DONE;
                        $envelope['EVPMAIL_ROWID'] = $detailedInfo['rowid'];
                        $envelope['EVPMAIL_ID'] = $detailedInfo['idmail'];
                        $envelope['EVPLASTMESSAGE'] = $detailedInfo['message'];
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} ROWID mail:{$envelope['EVPMAIL_ROWID']}";

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = $envelope['EVPMAIL_ROWID'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = $envelope['EVPMAIL_ID'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];
                    } else {
                        $envelope['EVPSTATUS'] = self::EVP_STATUS_SENT_ERROR;
                        $envelope['EVPLASTMESSAGE'] = $detailedInfo['message'];
                        $update_Info = "Invio mail envelope {$envelope['ROW_ID']} del package {$envelope['PACKAGES_ROWID']} Errore di invio: " . $envelope['EVPLASTMESSAGE'];

                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPSTATUS'] = $envelope['EVPSTATUS'];
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ROWID'] = 0;
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPMAIL_ID'] = '';
                        $toReturn[$recMail_Packages['ROW_ID']][$envelope['ROW_ID']]['EVPLASTMESSAGE'] = $envelope['EVPLASTMESSAGE'];
                    }

                    if (!$this->updateRecord($this->ITALWEB, 'MAIL_ENVELOPES', $envelope, $update_Info, 'ROW_ID')) {
                        continue;
                    }
                }
            }
            //
            //  Controllo se la spedizione è stata eseguita per tutti gli envelopes del packages
            //    
            $countMailInviate = $this->emlLib->getMailEnvelopes($recMail_Packages['ROW_ID'], $filters);

            if (!$countMailInviate['COUNT']) {
                $recMail_Packages['PKGFLAGACTIVATION'] = self::PKG_ACTIVATION_STATUS_DONE;
                $update_Info = 'Invio mail del packages ' . $recMail_Packages['ROW_ID'] . ' conclusa positivamente';
                if (!$this->updateRecord($this->ITALWEB, 'MAIL_PACKAGES', $recMail_Packages, $update_Info, 'ROW_ID')) {
                    continue;
                }
            }
        }
        return $toReturn;
    }

}
