<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaEfil.class.php';

//include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaNextStepSolution.class.php';

function cwbPagoPaGestNodo() {
    $cwbPagoPaGestNodo = new cwbPagoPaGestNodo();
    $cwbPagoPaGestNodo->parseEvent();
    return;
}

class cwbPagoPaGestNodo extends cwbBpaGenTab {

    private $invii;
    private $emissioni;
    private $connectionEfil;
    private $connectionNSS;

    function initVars() {
        $this->libDB = new cwbLibDB_BGE();
        $this->libDB_BWE = new cwbLibDB_BWE();
        $this->libDB_BTA = new cwbLibDB_BTA();
        $this->skipAuth = true;
        $this->noCrud = true;
        $this->invii = cwbParGen::getSessionVar('invii');
        $this->emissioni = cwbParGen::getSessionVar('emissioni');
        $this->connectionNSS = cwbParGen::getSessionVar('connectionNSS');
        $this->connectionEfil = cwbParGen::getSessionVar('connectionEfil');
    }

    protected function preConstruct() {
        parent::preConstruct();
    }

    public function __destruct() {
        $this->preDestruct();
        parent::__destruct();
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Inserimento':
                        $this->inserimento();
                        break;
                    case $this->nameForm . '_NSS':
                        $this->nextStepSolution();
                        break;
                    case $this->nameForm . '_EFil':
                        $this->eFil();
                        break;
                    case $this->nameForm . '_Emissioni':
                        $this->emissioni();
                        break;
                    case $this->nameForm . '_Pubblicazione':
                        $this->pubblicazioneScadenze();
                        break;
                    case $this->nameForm . '_VerificaSituazNodo':
                        $this->verificaSituazNodo();
                        break;
                    case $this->nameForm . '_RicAccettazione':
                        $this->ricevutaAccettazione();
                        break;
                    case $this->nameForm . '_RicPubblicazione':
                        $this->ricevutaPubblicazione();
                        break;
                    case $this->nameForm . '_Arricchite':
                        $this->arricchimento();
                        break;
                    case $this->nameForm . '_Rendicontazione':
                        $this->rendicontazioneScadenze();
                        break;
                    case $this->nameForm . '_Riconciliazione':
                        $this->riconciliazione();
                        break;
                }
            case 'returnFromBgeAgidConfEFil':
                switch ($this->elementId) {
                    case $this->nameForm . '_EFil':
                        if ($this->formData['returnData'] === 'Connessione Stabilita') {
                            //Out::msgInfo('OK', 'Connessione stabilita!');
                            $this->connectionEfil = true;
                            cwbParGen::setSessionVar('connectionEfil', $this->connectionEfil);
                        } elseif ($this->formData['returnData'] === 'Connessione Rifiutata') {
                            Out::hide($this->nameForm . '_Pubblicazione');
                            Out::hide($this->nameForm . '_spanPubblicazione');
                            $this->connectionEfil = false;
                            cwbParGen::setSessionVar('connectionEfil', $this->connectionEfil);
                        }
                        break;
                }
                break;
            case 'returnFromBtaServrend':
                switch ($this->elementId) {
                    case $this->nameForm . '_Emissioni':
                        $this->refresh();
                        break;
                }
                break;
        }
    }

    protected function efil() {
        cwbLib::apriFinestraDettaglio('cwbBgeAgidConfEfil', $this->nameForm, 'returnFromBgeAgidConfEFil', $_POST['id'], null, null);
    }

    protected function nextStepSolution() {
        cwbLib::apriFinestraDettaglio('cwbBgeAgidConfNss', $this->nameForm, 'returnFromBgeAgidConfNss', $_POST['id'], null, null);
    }

    protected function emissioni() {
        cwbLib::apriFinestraDettaglio('cwbBtaServrend', $this->nameForm, 'returnFromBtaServrend', $_POST['id'], null, null);
    }

    protected function verificaInserimento() {
        $scadenzePerIns = $this->libDB->leggiBwePendenScadenze();
        if ($scadenzePerIns) {
            Out::show($this->nameForm . '_Inserimento');
            Out::show($this->nameForm . '_spanInserimento');
            return true;
        } else {
            Out::hide($this->nameForm . '_Inserimento');
            Out::hide($this->nameForm . '_spanInserimento');
            return false;
        }
    }

    protected function postApriForm() {
        Out::show($this->nameForm . '_divGestione');
        $this->hideButtons();
        $this->refreshInvii();
        if ($this->invii) {
            Out::show($this->nameForm . '_VerificaSituazNodo');
            Out::show($this->nameForm . '_spanVerificaSituazNodo');
        }
        $this->refresh();
    }

    protected function refresh() {
        $esitoEmissioni = $this->refreshEmissioni($efil, $nss);
        if ($esitoEmissioni) {
            $this->refreshConnection($efil, $nss);
            if ($this->connectionEfil || $this->connectionNSS) {
                $this->refreshPendenze();
            }
        }
    }

    protected function refreshEmissioni(&$efil, &$nss) {
        $esitoEmissioni = $this->verificaEmissioni($efil, $nss);
        if (!$esitoEmissioni) {
            Out::msgInfo('Attenzione', 'Non sono presenti emissioni!');
        }
        return $esitoEmissioni;
    }

    protected function refreshPendenze() {
        $scadenzePubblicabili = $this->libDB_BWE->leggiBwePendenScadenze();
        if ($scadenzePubblicabili) {
            Out::show($this->nameForm . '_Pubblicazione');
            Out::show($this->nameForm . '_spanPubblicazione');
            Out::html($this->nameForm . '_spanPendenze', '');
        } else {
            Out::hide($this->nameForm . '_Pubblicazione');
            Out::html($this->nameForm . '_spanPendenze', "Nessuna Scadenza collegata all'emissione e' stata pubblicata!");
        }
    }

    protected function refreshInvii() {
        $this->invii = $this->libDB->leggiBgeAgidInvii();
        cwbParGen::setSessionVar('invii', $this->invii);
    }

    protected function refreshConnection($efil, $nss) {
        if (!$this->connectionEfil && $efil) {
            if ($efil) {
                $pagoPa = $this->getPagoPa(itaPagoPa::EFILL_TYPE);
                $arrayConnection = $pagoPa->testConnection();
                if (strstr($arrayConnection, 'Errore:')) {
                    $this->connectionEfil = false;
                    Out::msgInfo('Attenzione', "Connessione a ambiente E-Fil non riuscita. " . $arrayConnection);
                    return;
                }
                if (!empty($arrayConnection['Errori'])) {
                    // Connessione a EFIL KO
                    foreach ($arrayConnection['Messaggi'] as $key => $arrayMsg) {
                        foreach ($arrayMsg as $msg) {
                            $msgError .= $msg . '<br>';
                        }
                    }
                    Out::msgInfo('Attenzione - Connessione a ambiente E-Fil non riuscita', $msgError);
                    $this->connectionEfil = false;
                } else {
                    // Connessione a EFIL OK
                    Out::msgInfo('OK', 'Connessione a ambiente E-Fil riuscita!');
                    $this->connectionEfil = true;
                    cwbParGen::setSessionVar('connectionEfil', $this->connectionEfil);
                }
            }
            if (!$this->connectionNSS && $nss) {
                // todo gestisci connessione NSS
                $this->connectionNSS = false;
                cwbParGen::setSessionVar('connectionNSS', $this->connectionNSS);
            }
        }
    }

    protected function hideButtonsOperation() {
        Out::hide($this->nameForm . '_Pubblicazione');
        Out::hide($this->nameForm . '_spanPubblicazione');
        Out::hide($this->nameForm . '_RicAccettazione');
        Out::hide($this->nameForm . '_spanRicAccettazione');
        Out::hide($this->nameForm . '_RicPubblicazione');
        Out::hide($this->nameForm . '_spanRicPubblicazione');
        Out::hide($this->nameForm . '_Arricchite');
        Out::hide($this->nameForm . '_spanArricchite');
        Out::hide($this->nameForm . '_Rendicontazioni');
        Out::hide($this->nameForm . '_spanRendicontazioni');
    }

    protected function hideButtons() {
        // nascondo i bottoni all'apertura della pagina
        Out::hide($this->nameForm . '_Pubblicazione');
        Out::hide($this->nameForm . '_spanPubblicazione');
        Out::hide($this->nameForm . '_Efil');
        Out::hide($this->nameForm . '_spanEfil');
        Out::hide($this->nameForm . '_NSS');
        Out::hide($this->nameForm . '_spanNSS');
        Out::hide($this->nameForm . '_VerificaSituazNodo');
        Out::hide($this->nameForm . '_spanVerificaSituazNodo');
        Out::hide($this->nameForm . '_RicAccettazione');
        Out::hide($this->nameForm . '_spanRicAccettazione');
        Out::hide($this->nameForm . '_RicPubblicazione');
        Out::hide($this->nameForm . '_spanRicPubblicazione');
        Out::hide($this->nameForm . '_Arricchite');
        Out::hide($this->nameForm . '_spanArricchite');
        Out::hide($this->nameForm . '_Rendicontazioni');
        Out::hide($this->nameForm . '_spanRendicontazioni');
    }

    protected function verificaSituazNodo() {
        $this->hideButtonsOperation();
        $cercoRendi = false;
        $pagoPa = $this->getPagoPa(itaPagoPa::EFILL_TYPE);
        $confEfil = $this->libDB->leggiBgeAgidConfEfil();
        if ($this->invii) {
            foreach ($this->invii as $value) {
                if ($value['INTERMEDIARIO'] == 1) {
                    switch ($value['STATO']) {
                        case 1:
                            // Ricevute di Accettazione
                            $this->listDirectory($pagoPa, $confEfil, $confEfil['SFTPCARTRIC'], $value['STATO'], $value['TIPO'], $listOfFiles, $found);
                            if ($found || $foundAccettazione) {
                                // setto un altro flag perchè ad esempio potrei avere due invii con stato a 1 ma con codservizio diversi..
                                // mettiamo che al primo giro trovo ricevute, ma al secondo no... devo cmq mostrare il bottone... 
                                $foundAccettazione = true;
                                Out::show($this->nameForm . '_RicAccettazione');
                                Out::show($this->nameForm . '_spanRicAccettazione');
                                Out::html($this->nameForm . '_spanRicAccettazione', 'Sono presenti Ricevute di Accettazione da Elaborare.');
                            } else {
                                $foundAccettazione = false;
                                Out::show($this->nameForm . '_spanRicAccettazione');
                                Out::html($this->nameForm . '_spanRicAccettazione', 'NON Sono presenti Ricevute di Accettazione da Elaborare.');
                            }
                            break;
                        case 2:
                            // Ricevuta di Pubblicazione
                            $this->listDirectory($pagoPa, $confEfil, $confEfil['SFTPCARTRIC'], $value['STATO'], $value['TIPO'], $listOfFiles);
                            if ($listOfFiles) {
                                foreach ($listOfFiles as $file) {
                                    if (strpos($file, 'RicevutaPubblicazione') !== false) {
                                        $pubblicazione = true;
                                    } else {
                                        $pubblicazione = false;
                                    }
                                }
                                if ($pubblicazione) {
                                    Out::show($this->nameForm . '_RicPubblicazione');
                                    Out::show($this->nameForm . '_spanRicPubblicazione');
                                    Out::html($this->nameForm . '_spanRicPubblicazione', 'Sono presenti Ricevute di Pubblicazione da Elaborare.');
                                } else {
                                    Out::show($this->nameForm . '_spanRicPubblicazione');
                                    Out::html($this->nameForm . '_spanRicPubblicazione', 'NON Sono presenti Ricevute di Pubblicazione da Elaborare.');
                                }
                            }
                            break;
                        case 4:
                            // Arricchite
                            $this->listDirectory($pagoPa, $confEfil, $confEfil['SFTPCARTARRIC'], $value['STATO'], $value['TIPO'], $listOfFiles, $found);
                            if ($found || $foundArricchite) {
                                $foundArricchite = true;
                                Out::show($this->nameForm . '_Arricchite');
                                Out::show($this->nameForm . '_spanArricchite');
                                Out::html($this->nameForm . '_spanArricchite', 'Sono presenti Ricevute Arricchite da Elaborare.');
                            } else {
                                $foundArricchite = false;
                                Out::show($this->nameForm . '_spanArricchite');
                                Out::html($this->nameForm . '_spanArricchite', 'NON Sono presenti Ricevute Arricchite da Elaborare.');
                            }
                            break;
                        case 5:
                            // Rendicontazioni
                            $cercoRendi = true;
                            break;
                    }
                }
//                break;
            }
        }

        if ($cercoRendi) {
            // ho almeno un invio con STATO = 5, quindi so che devo andare a cercare anche le rendicontazioni.
            $this->listDirectory($pagoPa, $confEfil, $confEfil['SFTPCARTREND'], $value['STATO'], $value['TIPO'], $listOfFiles, $found);
            if ($found) {
                $messVerifica = "Sono presenti Rendicontazioni da elaborare.";
                $omnisCheck = $this->omnisServerCheck();
                if ($omnisCheck['RESULT']['EXITCODE'] === 'S') {
                    $messOmnisCheck = '<font color="green">Omnis Web Server configurato correttamente.</font>';
                    Out::html($this->nameForm . '_spanRendicontazioni', $messVerifica . '' . $messOmnisCheck);
                    Out::show($this->nameForm . '_Rendicontazioni');
                    Out::show($this->nameForm . '_spanRendicontazioni');
                } else {
                    $messOmnisCheck = '<font color="red">Omnis Web Server NON configurato correttamente.</font>';
                    Out::show($this->nameForm . '_spanRendicontazioni');
                    Out::html($this->nameForm . '_spanRendicontazioni', $messVerifica . '' . $messOmnisCheck);
                }
            } else {
                Out::show($this->nameForm . '_spanRendicontazioni');
                Out::html($this->nameForm . '_spanRendicontazioni', 'NON sono presenti Rendicontazioni da elaborare');
            }
        }
        // break;
        // }
    }

    private function listDirectory($pagoPa, $confEfil, $cartella, $stato, $tipo, &$listOfFiles, &$found) {
        $found = false;
        $listOfFiles = null;
        foreach ($this->emissioni as $emissione) {
            $listOfFiles = $pagoPa->fileDaElaborare($stato, $tipo, $emissione['CODSERVIZIO'], $confEfil, $cartella);
            if ($listOfFiles) {
                $found = true;
                break;
            } else {
                $found = false;
            }
        }
    }

    protected function omnisServerCheck() {
        $omnisClient = new itaOmnisClient();
        return $omnisClient->callExecute('OBJ_BWE_OMNIS_CHECK', 'handshake', array(), 'CITYWARE', false);
    }

    protected function pubblicazioneScadenze() {
        $this->inserimento();
        $this->pubblicazione();
        $this->refreshInvii();
        $this->refreshPendenze();
    }

    protected function pubblicazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->pubblicazione();
            }
        }
    }

    protected function inserimento() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->inserimentoMassivo();
            }
        }
    }

    protected function ricevutaAccettazione() {
        $this->ricevutaAccettazionePubblicazione();
        $this->ricevutaAccettazioneCancellazione();
        $this->refreshInvii();
    }

    protected function verificaEmissioni(&$efil, &$nss) {
        $efil = false;
        $nss = false;
        $this->emissioni = $this->libDB_BTA->leggiBtaServrendppa();
        cwbParGen::setSessionVar('emissioni', $this->emissioni);
        if ($this->emissioni) {
            foreach ($this->emissioni as $value) {
                if (intval($value['INTERMEDIARIO']) === 1) {
                    Out::show($this->nameForm . '_Efil');
                    Out::show($this->nameForm . '_spanEfil');
                    $efil = true;
                }
                if (intval($value['INTERMEDIARIO']) === 2) {
                    Out::show($this->nameForm . '_NSS');
                    Out::show($this->nameForm . '_spanNSS');
                    $nss = true;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    protected function elaboraScarti() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->elaborazioneScadenzeScartate();
            }
        }
    }

    protected function ricevutaAccettazionePubblicazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaAccettazionePubblicazione();
            }
        }
    }

    protected function ricevutaPubblicazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaPubblicazione();
            }
        }
        $this->refreshInvii();
    }

    protected function arricchimento() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaArricchita();
            }
        }
        $this->refreshInvii();
    }

    protected function ricevutaAccettazioneCancellazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaAccettazioneCancellazione();
            }
        }
    }

    protected function cancellazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->cancellazioneMassiva();
            }
        }
    }

    protected function ricevutaCancellazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaCancellazione();
            }
        }
    }

    protected function rendicontazioneScadenze() {
        $this->rendicontazione();
        $this->riconciliazione();
        $this->refreshInvii();
    }

    protected function rendicontazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->rendicontazione();
            }
        }
    }

    protected function riconciliazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->riconciliazione();
            }
        }
    }

    protected function pubblicazioneDiretta() {
//        $pagoPa = $this->getPagoPa(itaPagoPa::NEXTSTEPSOLUTION_TYPE);
//        if ($pagoPa) {
//
//            // Imposta dei dati fissi da passare al metodo
//            $annoEmissione = 2016;
//            $numeroEmissione = 12;
//            $chiaveServizioEmittente = 8;
//            $result = $pagoPa->pubblicazioneDiretta($annoEmissione, $numeroEmissione, $chiaveServizioEmittente, $esito, $messaggio);
//        }
    }

    protected function riceviRuoloIuv() {
        $pagoPa = $this->getPagoPa(itaPagoPa::NEXTSTEPSOLUTION_TYPE);
        if ($pagoPa) {
            $result = $pagoPa->leggiIUV();
        }
    }

    protected function intermediario() {
        $intermediari = $this->libDB_BTA->leggiBtaServrendIntermediari();
        return $intermediari;
    }

    private function getPagoPa($type) {
        try {
            $pagoPa = new itaPagoPa($type);
        } catch (Exception $ex) {
            Out::msgStop("ERRORE", $ex->getMessage());
            return null;
        }
        return $pagoPa;
    }

}

