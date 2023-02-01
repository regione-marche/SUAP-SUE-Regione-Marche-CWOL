<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaEfil.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaEfilZZ.class.php';
include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');

function cwbBgeAgidConfEfil() {
    $cwbBgeAgidConfEfil = new cwbBgeAgidConfEfil();
    $cwbBgeAgidConfEfil->parseEvent();
    return;
}

class cwbBgeAgidConfEfil extends cwbBpaGenTab {

    private $pathCertificatoCaricato;
    private $confEfil;
    private $pagoPa;
    private $connection;
    private $tipoChiamata;

    function initVars() {
        $this->libDB = new cwbLibDB_BGE();
        $this->itasftp = new itaSFtpUtils();
        $this->pagoPa = new itaPagoPa(itaPagoPa::EFILL_TYPE);
        $this->skipAuth = true;
        $this->tipoChiamata = cwbParGen::getSessionVar('tipoChiamata');
    }

    public function __destruct() {
        if ($this->close != true) {
            cwbParGen::setSessionVar('tipoChiamata', $this->tipoChiamata);
        }
        parent::__destruct();
    }

    protected function preConstruct() {
        $this->pathCertificatoCaricato = cwbParGen::getFormSessionVar($this->nameForm, '_pathCertificatoCaricato');
        $this->confEfil = cwbParGen::getFormSessionVar($this->nameForm, '_confEfil');
        $this->connection = cwbParGen::getFormSessionVar($this->nameForm, '_connection');
    }

    protected function postDestruct() {
        cwbParGen::setFormSessionVar($this->nameForm, '_pathCertificatoCaricato', $this->pathCertificatoCaricato);
        cwbParGen::setFormSessionVar($this->nameForm, '_confEfil', $this->confEfil);
        cwbParGen::setFormSessionVar($this->nameForm, '_connection', $this->connection);
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPFILEKEY]_upld':
                        $this->caricaCertificato();
                        break;
                    case $this->nameForm . '_SFTPFILEKEY_DOWNLOAD':
                        $this->scaricaCertificato();
                        break;
                    case $this->nameForm . '_TestConnection':
                        $this->testConnection();
                        break;
                    case $this->nameForm . '_TESTIDAPP':
                        $this->testWS();
                        break;
                    case $this->nameForm . '_TestApriSessioneZZ':
                        $this->testApriSessioneZZ();
                        break;
                    case 'close-portlet':
                        unlink($this->pathCertificatoCaricato);
                        if ($this->returnModel) {
                            cwbLib::ricercaEsterna($this->returnModel, $this->returnEvent, $this->returnId, $this->connection, $this->nameForm);
                        }
                        break;
                }
                break;
        }
    }

    protected function postApriForm() {
        $this->tipoChiamata = $_POST['tipoChiamata'];
        $this->pathCertificatoCaricato = '';
        $this->initComboIUV();
        Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[GENERAIUV]', 0);
        Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[FILLER]', '$');
        Out::disableField($this->nameForm . '_GENERAIUV');
        $configurazione = $this->libDB->leggiBgeAgidConfEfil();
        if (!$configurazione) {
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPHOST]', 'filetransfer.plugandpay.it');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTPUBBL]', '/Forniture/Importazione');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTCANC]', '/Forniture/Cancellazione');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTARRIC]', '/Arricchite');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTREND]', '/Rendicontazioni');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTRT]', '/Rendicontazioni/RicevuteTelematiche');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPCARTRIC]', '/Ricevute');
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[PROGKEYTAB]', 1);
            $this->setVisControlli(true, false, false, false, true, false, false, false, false, false);
            Out::hide($this->nameForm . '_divAudit');
        } else {
            Out::valori($configurazione, $this->nameForm . '_' . $this->TABLE_NAME);
            $this->setVisControlli(true, false, false, false, false, true, false, false, false, false);
        }

        Out::setFocus("", $this->nameForm . '_BGE_AGID_CONF_EFIL[AUXDIGIT]');
        // serve per attivare il componenre ita-edit-upload
        Out::codice("pluploadActivate('" . $this->nameForm . "_BGE_AGID_CONF_EFIL[SFTPFILEKEY]_upld_uploader');");

        if ($configurazione['SFTPFILEKEY']) {
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPFILEKEY]', "certificato.pem");
        } else {
            Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPFILEKEY]', "");
        }
    }

    protected function elaboraCurrentRecord($operation) {
        if ($operation !== itaModelService::OPERATION_DELETE) {

            // se il campo di testo certificato è popolato
            if ($_POST[$this->nameForm . '_BGE_AGID_CONF_EFIL']['SFTPFILEKEY']) {
                // se c'è un path popolato significa che è un nuovo certificato appena caricato
                if (($this->pathCertificatoCaricato)) {
                    // visto che c'è un nuovo certificato lo converto e salvo(converto il certificato ppk in openssh).                    
                    if ($this->itasftp->convertPpkToPem($this->pathCertificatoCaricato, $this->CURRENT_RECORD['SFTPPASSWORD'], $this->CURRENT_RECORD['SFTPPASSWORD'], true)) {
                        $this->CURRENT_RECORD['SFTPFILEKEY'] = $this->itasftp->getResult();
                        Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPFILEKEY]', 'certificato.pem');
                        unlink($this->pathCertificatoCaricato);
                        $this->pathCertificatoCaricato = '';
                    } else {
                        throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Errore conversione certificato ppk in open ssh: " + $this->itasftp->getErrMessage());
                    }
                } else { // sennò significa che il certificato è lo stesso che ho caricato all'apriform
                    // il certificato è lo stesso che è già su db quindi escludo il campo cert. dal salvataggio in modo che non lo aggiorna
                    unset($this->CURRENT_RECORD['SFTPFILEKEY']);
                }
            } else {
                // il certificato è vuoto quindi lo azzero anche su db (non dovrebbe capitare visto he c'è il validatore sulla form)
                $this->CURRENT_RECORD['SFTPFILEKEY'] = null;
            }
        }
    }

    protected function postAggiungi() {
        $configurazione = $this->libDB->leggiBgeAgidConfEfil();
        Out::valori($configurazione, $this->nameForm . '_' . $this->TABLE_NAME);
        Out::show($this->nameForm . '_divAudit');
        $this->setVisControlli(true, false, false, false, false, true, false, false, false, false);
        Out::setFocus("", $this->nameForm . '_BGE_AGID_CONF_EFIL[AUXDIGIT]');
    }

    protected function testWS() {
        $result = $this->pagoPa->ricercaPosizioneDaIUV('000000000000000');
        if ($result !== false) {
            Out::html($this->nameForm . '_spanTestIdAppl', '<font color="green">OK</font>');
        } else {
            Out::msgInfo('Attenzione', $this->pagoPa->getLastErrorDescription());
            Out::html($this->nameForm . '_spanTestIdAppl', '<font color="red">KO</font>');
        }
    }

    private function testApriSessioneZZ() {
        $confEfil = $this->libDB->leggiBgeAgidConfEfil();
        $cwbPagoPaEfilZZ = new cwbPagoPaEfilZZ();
        $res = $cwbPagoPaEfilZZ->apriSessioneZZ($confEfil);
        if ($res) {
            Out::msgInfo("OK", $res);
        } else {
            Out::msgStop("KO", "Errore: " . $cwbPagoPaEfilZZ->getLastErrorDescription());
        }
    }

    protected function testConnection() {
        $arrayConnection = $this->pagoPa->testConnection(true, $this->tipoChiamata);
        $errore = false;
        if (strstr($arrayConnection, 'Errore:') || !$arrayConnection) {
            // Errore connessione alla root
            Out::msgInfo('Attenzione', "Connessione a ambiente E-Fil non riuscita. " . $arrayConnection);
            Out::html($this->nameForm . '_spanPubbl', '<font color="red">KO</font>');
            Out::html($this->nameForm . '_spanCanc', '<font color="red">KO</font>');
            Out::html($this->nameForm . '_spanArric', '<font color="red">KO</font>');
            Out::html($this->nameForm . '_spanRend', '<font color="red">KO</font>');
            Out::html($this->nameForm . '_spanRicTel', '<font color="red">KO</font>');
            Out::html($this->nameForm . '_spanRic', '<font color="red">KO</font>');
            return;
        } else {
            if (!$arrayConnection['Errori']) {
                Out::html($this->nameForm . '_spanPubbl', '<font color="green">OK</font>');
                Out::html($this->nameForm . '_spanCanc', '<font color="green">OK</font>');
                Out::html($this->nameForm . '_spanArric', '<font color="green">OK</font>');
                Out::html($this->nameForm . '_spanRend', '<font color="green">OK</font>');
                Out::html($this->nameForm . '_spanRicTel', '<font color="green">OK</font>');
                Out::html($this->nameForm . '_spanRic', '<font color="green">OK</font>');
            } else {
                foreach ($arrayConnection['Errori'] as $key => $value) {
                    if ($value['PUBBL']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanPubbl', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanPubbl', '<font color="green">OK</font>');
                    }
                    if ($value['CANC']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanCanc', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanCanc', '<font color="green">OK</font>');
                    }
                    if ($value['ARRIC']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanArric', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanArric', '<font color="green">OK</font>');
                    }
                    if ($value['REND']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanRend', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanRend', '<font color="green">OK</font>');
                    }
                    if ($value['RT']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanRicTel', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanRicTel', '<font color="green">OK</font>');
                    }
                    if ($value['RIC']) {
                        $errore = true;
                        Out::html($this->nameForm . '_spanRic', '<font color="red">KO</font>');
                    } else {
                        Out::html($this->nameForm . '_spanRic', '<font color="green">OK</font>');
                    }
                }
            }
        }

        if (!$errore) {
            Out::msgInfo('OK', 'Connessione Stabilita');
        } else {
            foreach ($arrayConnection['Messaggi'] as $key => $arrayMsg) {
                foreach ($arrayMsg as $msg) {
                    $msgError .= $msg . '<br>';
                }
            }
            Out::msgInfo('Attenzione', $msgError);
        }
        $this->connection = $arrayConnection['Connection'];
    }

    protected function postAggiorna() {
        $this->setVisControlli(true, false, false, false, false, true, false, false, false, false);
        Out::setFocus("", $this->nameForm . '_BGE_AGID_CONF_EFIL[AUXDIGIT]');
    }

    protected function initComboIUV() {
        // Generazione IUV
        Out::html($this->nameForm . '_GENERAIUV', ''); // svuoto combo

        Out::select($this->nameForm . '_GENERAIUV', 1, "0", 1, "In carico all'intermediario");
        Out::select($this->nameForm . '_GENERAIUV', 1, "1", 0, "In carico a cityware.online");
    }

    private function caricaCertificato() {
        $origFile = $_POST['file'];
        $uplFile = itaLib::getPrivateUploadPath() . "-" . $_POST['file'];
        $this->pathCertificatoCaricato = $uplFile;

        Out::valore($this->nameForm . '_BGE_AGID_CONF_EFIL[SFTPFILEKEY]', $origFile);
    }

    private function scaricaCertificato() {
        $conf = $this->libDB->leggiBgeAgidConfEfil();
        cwbLib::downloadDocument('certificato' . time() . '.pem', stream_get_contents($conf['SFTPFILEKEY']), true);
    }

}

?>