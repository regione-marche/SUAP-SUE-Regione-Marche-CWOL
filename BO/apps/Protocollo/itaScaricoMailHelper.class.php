<?php

include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';

/**
 *
 * Classe di utils per lo scarico mail
 *
 * PHP Version 5
 *
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.08.2017
 * @link
 * @see
 * 
 */
class itaScaricoMailHelper {

    const CHIAVE_SINCACC = 'SYNCRONIZEACCOUNT_MAILARCHIVIO';
    const PROCEDURA_RICEZMAIL = 'RICEZIONE MAIL';

    private $errMessage;
    private $result;
    private $errScarico;
    private $mailInterop;
    public $envLib;
    public $proLibMail;

    public function __construct() {
        $this->envLib = new envLib();
        $this->proLibMail = new proLibMail();
    }

    public function scaricaPosta() {
        $this->result = array();
        $this->errScarico = array();
        $this->mailInterop = array();
        $this->errMessage = "";
        if (!$this->refAccounts) {
            $this->errMessage = "Non sono stati configurati account di posta.";
            return false;
        }

        /*
         * Sblocco Mail Account con i Semafori (e controllo se bloccati)
         */
        if (!$this->semafori('SBLOCCA', self::CHIAVE_SINCACC, self::PROCEDURA_RICEZMAIL, envLib::TIPO_BLOCCO_CHIAVE)) {
            return false;
        }
        /*
         * Blocco Mail Account con i Semafori
         */
        if (!$this->semafori('BLOCCA', self::CHIAVE_SINCACC, self::PROCEDURA_RICEZMAIL, envLib::TIPO_BLOCCO_CHIAVE)) {
            return false;
        }

        // scorro gli account
        foreach ($this->refAccounts as $value) {
            $emlMailbox = new emlMailBox($value['EMAIL']);
            $retSync = $emlMailbox->syncronizeAccount('@DA_PROTOCOLLARE@');
            if ($retSync === false) {
                //$msg = "Errore in ricezione: " . $emlMailbox->getLastMessage();
                $this->errScarico[$value['EMAIL']] = "Errore in ricezione: " . $emlMailbox->getLastMessage();
            } else {
                $msg = "Ricezione Completata: " . count($retSync) . " nuovi messaggi.";
                $this->result[$value['EMAIL']] = $msg;
            }
        }
        /*
         * Elaborazione Mail Interoperabili:
         * Mail autoprotocollate:
         */
        $this->proLibMail->ElaboraMailInteroperabili();
        $MailProtocollate = $this->proLibMail->getMailProtocollate();
        if ($MailProtocollate) {
            $CountProt = count($MailProtocollate);
            $msgAutoProt = "Sono state protocollate " . count($MailProtocollate) . " pec/mail interoperabili: <br>";
            foreach ($MailProtocollate as $MailProtocollata) {
                $msgAutoProt .= ' - ' . intval($MailProtocollata['numeroProtocollo']) . '/' . $MailProtocollata['annoProtocollo'] . "<br>";
            }
            $this->mailInterop[] = $msgAutoProt;
        }
        /* Mail Assegnate Interoperabili */
        $MailInteropAssegnate = $this->proLibMail->getMailInteropAssegnate();
        if ($MailInteropAssegnate) {
            $CountProt = count($MailInteropAssegnate);
            $msgAutoProtAss = "-----------------<br>";
            $msgAutoProtAss .= "Assegnate " . count($MailInteropAssegnate) . " mail interoperabili ai relativi protocolli. <br>";
            $this->mailInterop[] = $msgAutoProtAss;
        }
        $ErrMail = $this->proLibMail->getMailErrore();
        if ($ErrMail) {
            $htmlErr = "<br>";
            $htmlProtErr = "";
            foreach ($ErrMail as $Mail) {
                $htmlProtErr .= " -Mail " . substr($Mail['Oggetto'], 0, 50) . ": ";
                $htmlProtErr .= "  " . $Mail['Errore'] . "<br>";
            }
            $htmlErr .= "Errore in elaborazione mail interoperabili:  <br>" . $htmlProtErr . "<br>";
            $this->mailInterop[] = $htmlErr;
        }
        if (!$this->semafori('SBLOCCA', self::CHIAVE_SINCACC, self::PROCEDURA_RICEZMAIL, envLib::TIPO_BLOCCO_CHIAVE)) {
            return false;
        }

        return $this->result;
    }

    public function assegnaRicevute() {
        
    }

    public function setRefAccounts() {
        $ElencoMail = array();
        $this->refAccounts = array();
        $proLib = new proLib();
        $anaent_28 = $proLib->GetAnaent('28');
        if ($anaent_28) {
            $AccountMail = unserialize($anaent_28['ENTVAL']);
            foreach ($AccountMail as $key => $Account) {
                if ($Account['SCAR_AUTO'] == true) {
                    $ElencoMail[$key] = $Account;
                }
            }
        }
        /*
         * Aggiungo mail di cattura inoltro
         */
        $anaent_52 = $proLib->GetAnaent('52');
        if ($anaent_52) {
            $ElencoMailInoltro = unserialize($anaent_52['ENTVAL']);
            if ($ElencoMailInoltro) {
                $ElencoMail = array_merge($ElencoMail, $ElencoMailInoltro);
            }
        }


        if ($ElencoMail) {
            $this->refAccounts = $ElencoMail;
            return true;
        }

        return false;
    }

    private function semafori($azione, $chiave, $procedura, $tipoblocco) {
        if ($this->envLib->Semaforo($azione, $chiave, $procedura, $tipoblocco) === false) {
            $this->errMessage = $this->envLib->getErrMessage();
            return false;
        }

        return true;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getResult() {
        return $this->result;
    }

    function getErrScarico() {
        return $this->errScarico;
    }

    public function getMailInterop() {
        return $this->mailInterop;
    }

}

?>