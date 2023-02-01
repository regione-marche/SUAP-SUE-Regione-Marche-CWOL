<?php

include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';

function cwbBtaSendMail() {
    $cwbBtaSendMail = new cwbBtaSendMail();
    $cwbBtaSendMail->parseEvent();
    return;
}

class cwbBtaSendMail extends itaFrontController {

    private $defaultOggetto;
    private $defaultMittenti;
    private $destinatari = array();
    private $defaultCorpo;
    private $allegati = array();
    private $allegatoObbligatorio = true;

    const GRID_DESTINATARI = 'gridDestinatari';
    const GRID_ALLEGATI = 'gridAllegati';

    protected function postItaFrontControllerCostruct() {
        $this->destinatari = cwbParGen::getFormSessionVar($this->nameForm, '_destinatari');
        $this->allegati = cwbParGen::getFormSessionVar($this->nameForm, '_allegati');
    }

    public function __destruct() {
        parent::__destruct();
        cwbParGen::setFormSessionVar($this->nameForm, '_allegati', $this->allegati);
        cwbParGen::setFormSessionVar($this->nameForm, '_destinatari', $this->destinatari);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->openForm();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Annulla':
                        $this->closeDialog();
                        break;
                    case $this->nameForm . '_Invia':
                        $this->invia();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_DESTINATARI:
                        $this->addDestinatarioVuoto();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_DESTINATARI:
                        $this->cancellaDestinatario();
                        break;
                }
                break;
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . self::GRID_DESTINATARI:
                        $this->allineaDestinatario();
                        break;
                }
                break;
        }
    }

    private function cancellaDestinatario() {
        $key = $_POST['rowid'];
        unset($this->destinatari[$key]);
        $this->loadGrid(self::GRID_DESTINATARI, $this->destinatari);
    }

    private function allineaDestinatario() {
        $key = $_POST['rowid'];
        $this->destinatari[$key]['EMAIL'] = $_POST['value'];
    }

    private function addDestinatarioVuoto() {
        $key = $this->creaRandomGuid();
        $this->destinatari[$key] = array('EMAIL' => '', 'RANDOMGUID' => $key);

        $this->loadGrid(self::GRID_DESTINATARI, $this->destinatari);
    }

    private function openForm() {
        $selected = true;
        if ($this->defaultMittenti) {
            foreach ($this->defaultMittenti as $mittente) {
                Out::select($this->nameForm . "_MITTENTE", 1, $mittente, $selected, $mittente);
                $selected = false;
            }
        } else {
            // se non vengono passati mittenti, metto quello di default
            $devLib = new devLib();
            $itaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
            if ($itaEngine_mail_rec) {
                $email = $itaEngine_mail_rec['CONFIG'];

                Out::select($this->nameForm . "_MITTENTE", 1, $email, 1, $email);
            } else {
                Out::msgStop("Errore", "Nessun mittente");
            }
        }
        if ($this->destinatari) {
            foreach ($this->destinatari as $mittente) {
                Out::select($this->nameForm . "_MITTENTE", 1, $mittente, $selected, $mittente);
                $selected = false;
            }
        }

        $this->loadGrid(self::GRID_ALLEGATI, $this->getAllegati());
        $this->loadGrid(self::GRID_DESTINATARI, $this->getDestinatari());

        if ($this->getDefaultOggetto()) {
            Out::valore($this->nameForm . '_OGGETTO', $this->getDefaultOggetto());
        }
        if ($this->getDefaultCorpo()) {
            Out::valore($this->nameForm . '_CORPO', $this->getDefaultCorpo());
        }
    }

    private function loadGrid($gridName, $records) {
        try {
            if ($records === null) {
                $records = array();
            }
            $this->helper->setGridName($gridName);
            $ita_grid = $this->helper->initializeTableArray($records);
            if (!$ita_grid->getDataPage('json')) {
                TableView::clearGrid($this->nameForm . '_' . $gridName);
            } else {
                TableView::enableEvents($this->nameForm . '_' . $gridName);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    private function invia() {
        if ($this->validaDati()) {
            $emlMailBox = emlMailBox::getInstance(trim($_POST[$this->nameForm . '_MITTENTE']));

            if (!$emlMailBox) {
                Out::msgStop("Errore", "Mittente non mappato");
                return;
            }

            // destinatari
            foreach ($this->destinatari as $email) {
                $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
                if (!$outgoingMessage) {
                    Out::msgStop("Errore", "Errore Interno");
                    return;
                }

                $outgoingMessage->setSubject($_POST[$this->nameForm . '_OGGETTO']);
                $outgoingMessage->setBody($_POST[$this->nameForm . '_CORPO']);
                $outgoingMessage->setEmail(trim($email['EMAIL']));
                foreach ($this->allegati as $alleg) {
                    $outgoingMessage->addBinaryAttachment($alleg['CORPO'], $alleg['NOMEFILE']);
                }
                if ($emlMailBox->sendMessage($outgoingMessage, false, false)) {
                    Out::msgInfo("Mail Inviata", "Mail Inviata");
                    $this->closeDialog();
                }
            }
        }
    }

    private function validaDati() {
        $error = false;
        $msgErr = '<BR>';
        if (!$_POST[$this->nameForm . '_MITTENTE']) {
            $error = true;
            $msgErr .= 'Inserire il mittente. <BR>';
        }
        if (!$_POST[$this->nameForm . '_OGGETTO']) {
            $error = true;
            $msgErr .= "Inserire l'oggetto. <BR>";
        }
        if (!$_POST[$this->nameForm . '_CORPO']) {
            $error = true;
            $msgErr .= 'Inserire il corpo. <BR>';
        }
        if (!$this->destinatari) {
            $error = true;
            $msgErr .= 'Inserire almeno un destinatario. <BR>';
        }
        if ($this->allegatoObbligatorio && !$this->allegati) {
            $error = true;
            $msgErr .= 'Inserire almeno un allegato. <BR>';
        }

        if ($error) {
            Out::msgStop("Errore", $msgErr);

            return false;
        }

        return true;
    }

    private function creaRandomGuid() {
        return rand(1000, 99999) * rand(500, 3000) + rand(1000, 99999);
    }

    private function closeDialog() {
        $this->close();
        Out::closeDialog($this->nameForm);
    }

    function getDefaultOggetto() {
        return $this->defaultOggetto;
    }

    function getDestinatari() {
        return $this->destinatari;
    }

    function getDefaultCorpo() {
        return $this->defaultCorpo;
    }

    function getAllegati() {
        return $this->allegati;
    }

    function setDefaultOggetto($oggetto) {
        $this->defaultOggetto = $oggetto;
    }

    function setDestinatari($destinatari) {
        $this->destinatari = $destinatari;
    }

    function setDefaultCorpo($corpo) {
        $this->defaultCorpo = $corpo;
    }

    function setAllegati($allegati) {
        $this->allegati = $allegati;
    }

    function getDefaultMittenti() {
        return $this->defaultMittenti;
    }

    function setDefaultMittenti($mittentiDisponibili) {
        $this->defaultMittenti = $mittentiDisponibili;
    }

    function getAllegatoObbligatorio() {
        return $this->allegatoObbligatorio;
    }

    function setAllegatoObbligatorio($allegatoObbligatorio) {
        $this->allegatoObbligatorio = $allegatoObbligatorio;
    }

}

