<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEmailDate.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

class proLibAnalizzaMail {

    private $proLib;
    private $emlLib;
    private $errCode;
    private $errMessage;
    private $emlFile;
    private $emlTipo;
    private $currMessage;
    private $currMailBox;
    private $retStrutturaMail;
    private $retStrutturaMailOrig;
    private $datiMail;
    private $datiMailOrig;
    private $elencoAllegati = array();
    private $elencoAllegatiOrig = array();
    private $urlEmlBody;
    private $urlEmlBodyOrig;

    function __construct() {
        $this->proLib = new proLib();
        $this->emlLib = new emlLib();
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
    }

    public function getCurrMessage() {
        return $this->currMessage;
    }

    public function setCurrMessage($currMessage) {
        $this->currMessage = $currMessage;
    }

    public function getEmlFile() {
        return $this->emlFile;
    }

    public function setEmlFile($emlFile) {
        $this->emlFile = $emlFile;
    }

    public function getEmlTipo() {
        return $this->emlTipo;
    }

    public function setEmlTipo($emlTipo) {
        $this->emlTipo = $emlTipo;
    }

    private function clearCurrMessage() {
        if ($this->currMessage != null) {
            $this->currMessage->cleanData();
            $this->currMessage = null;
        }
    }

    //
    // Getter dati mail principale
    //
    public function getretStrutturaMail() {
        return $this->retStrutturaMail;
    }

    public function getdatiMail() {
        return $this->datiMail;
    }

    public function getelencoAllegati() {
        return $this->elencoAllegati;
    }

    public function geturlEmlBody() {
        return $this->urlEmlBody;
    }

    //
    // Getter dati mail originale
    //
    public function getretStrutturaMailOrig() {
        return $this->retStrutturaMailOrig;
    }

    public function getdatiMailOrig() {
        return $this->datiMailOrig;
    }

    public function getelencoAllegatiOrig() {
        return $this->elencoAllegatiOrig;
    }

    public function geturlEmlBodyOrig() {
        return $this->urlEmlBodyOrig;
    }

    //
    //  Caricamento Dati
    //
    //@TODO Migliorare in una sola chiamata il CaricaElementiMail per Originale e Principale ?
    public function CaricaElementiMail() {
        // Gestire i vari carica senza return? set diretto? $this->retStrutturaMail=....
        // Carico la struttura Mail
        $this->retStrutturaMail = $this->caricaStrutturaMail();
        if (!$this->retStrutturaMail) {
            return false;
        }
        // Leggo i datiMail
        $this->datiMail = $this->caricaDatiMail();
        if (!$this->datiMail) {
            return false;
        }
        // Carico urlEmlBody ...
        $this->urlEmlBody = $this->caricaUrlEmlBody();
        // Carico elencoAllegati della Mail
        $this->elencoAllegati = $this->caricaElencoAllegati();

        if ($this->retStrutturaMail['ita_PEC_info'] != 'N/A') {
            if (is_array($this->retStrutturaMail['ita_PEC_info']['messaggio_originale'])) {
                $this->retStrutturaMailOrig = $this->retStrutturaMail['ita_PEC_info']['messaggio_originale']['ParsedFile'];
                $this->caricaElementiMailOriginale();
            }
        }
        //Preparo un risultato?
    }

    private function caricaElementiMailOriginale() {
        // Carico Dati Mail Originale
        $this->datiMailOrig = $this->getDatiMailFromStruttura($this->retStrutturaMailOrig);
        // Carico urlEmlBody
        if (isset($this->retStrutturaMailOrig['DataFile'])) {
            $datafile = $this->retStrutturaMailOrig['DataFile'];
        } else {
            foreach ($this->retStrutturaMailOrig['Alternative'] as $value) {
                $datafile = $value['DataFile'];
            }
        }
        $this->urlEmlBodyOrig = utiDownload::getUrl("emlbody.html", $datafile, false, true);
        // Carico Allegati Mail Originale
        $this->elencoAllegatiOrig = $this->caricaElementiAllegati($this->retStrutturaMailOrig['Attachments']);
    }

    private function caricaStrutturaMail() {
        $this->clearCurrMessage();
        $retDecode = array();
        switch ($this->emlTipo) {
            case 'DB':
                $this->currMailBox = new emlMailBox();
                $this->currMessage = $this->currMailBox->getMessageFromDb($this->emlFile);
                break;

            case 'LOCALE':
                $this->currMessage = new emlMessage();
                $this->currMessage->setEmlFile($this->emlFile);
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage('Eml Tipo non definito.');
                return $retDecode;
        }

        $this->currMessage->parseEmlFileDeep();
        $retDecode = $this->currMessage->getStruct();
        return $retDecode;
    }

    private function caricaDatiMail() {
        $DatiMail = array();
        switch ($this->emlTipo) {
            case 'DB':
                $DatiMail = $this->emlLib->getMailArchivio($_POST['rowid'], 'rowid');
                break;

            case 'LOCALE':
                $DatiMail = $this->getDatiMailFromStruttura($this->retStrutturaMail);
                break;

            default:
                $this->setErrCode(-1);
                $this->setErrMessage('Eml Tipo non definito.');
                return $DatiMail;
        }
        return $DatiMail;
    }

    private function getDatiMailFromStruttura($retStruttura) {
        $DatiMail = array();
        $DatiMail['SUBJECT'] = $retStruttura['Subject'];
        $DatiMail['FROMADDR'] = $retStruttura['From'][0]['address'];
        $decodedDate = utiEmailDate::eDate2Date($retStruttura['Date']);
        $DatiMail["MSGDATE"] = $decodedDate['date'] . ' ' . $decodedDate['time'];
        $DatiMail["PECTIPO"] = '';
        if ($retStruttura['ita_PEC_info'] != 'N/A') {
            $pec = $retStruttura['ita_PEC_info']['dati_certificazione'];
            if (is_array($pec)) {
                $DatiMail["PECTIPO"] = $pec['tipo'];
            }
        }
        return $DatiMail;
    }

    private function caricaElencoAllegati() {
        $elementi = $this->retStrutturaMail['Attachments'];
        $allegati = $this->caricaElementiAllegati($elementi);
        return $allegati;
    }

    private function caricaElementiAllegati($elementi) {
        $allegati = array();
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                if ($elemento['FileName']) {
                    $allegati[] = array(
                        'ROWID' => $incr,
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile']
                    );
                    $incr++;
                }
            }
        }
        return $allegati;
    }

    private function caricaUrlEmlBody() {
        return utiDownload::getUrl("emlbody.html", $this->retStrutturaMail['DataFile'], false, true);
    }

    public function FormattaElencoAllegati($allegati) {
        foreach ($allegati as $key => $allegato) {
            $vsign = "";
            $icon = utiIcons::getExtensionIconClass($allegato['DATAFILE'], 32);
            $sizefile = $this->emlLib->formatFileSize(filesize($allegato['FILE']));
            $ext = pathinfo($allegato['DATAFILE'], PATHINFO_EXTENSION);
            if (strtolower($ext) == "p7m") {
                $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
            }
            $allegati[$key]['FileIcon'] = "<span style = \"margin:2px;\" class=\"$icon\"></span>";
            $allegati[$key]['FileSize'] = $sizefile;
            $allegati[$key]['VSIGN'] = $vsign;
        }
        return $allegati;
    }

}
?>
