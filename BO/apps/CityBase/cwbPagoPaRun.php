<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_LIB_PATH . '/itaException/ItaException.php';

function cwbPagoPaRun() {
    $cwbPagoPaRun = new cwbPagoPaRun();
    $cwbPagoPaRun->parseEvent();
    return;
}

class cwbPagoPaRun extends itaFrontControllerCW {

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->nameForm . '_divGestione');
                Out::setFocus("", $this->nameForm . '_Inserimento');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Inserimento':
                        $this->inserimento();
                        break;
                    case $this->nameForm . '_ElaboraScarti':
                        $this->elaboraScarti();
                        break;
                    case $this->nameForm . '_Pubblicazione':
                        $this->pubblicazione();
                        break;
                    case $this->nameForm . '_Pubblicazione Test':
                        $this->pubblicazione();
                        break;
                    case $this->nameForm . '_PubblicazioneTestSF':
                        $this->pubblicazione();
                        break;
                    case $this->nameForm . '_RicevutaAccettazionePubblicazione':
                        $this->ricevutaAccettazionePubblicazione();
                        break;
                    case $this->nameForm . '_RicevutaPubbl':
                        $this->ricevutaPubblicazione();
                        break;
                    case $this->nameForm . '_Arricchimento':
                        $this->arricchimento();
                        break;
                    case $this->nameForm . '_Cancellazione':
                        $this->cancellazione();
                        break;
                    case $this->nameForm . '_Rendicontazione':
                        $this->rendicontazione();
                        break;
                    case $this->nameForm . '_Riconciliazione':
                        $this->riconciliazione();
                        break;
                    case $this->nameForm . '_Cancellazione':
                        $this->cancellazione();
                        break;
                    case $this->nameForm . '_RicevutaAccettazioneCancellazione':
                        $this->ricevutaAccettazioneCancellazione();
                        break;
                    case $this->nameForm . '_RicevutaCancellazione':
                        $this->ricevutaCancellazione();
                        break;
                    case $this->nameForm . '_PubblicazioneDiretta':
                        $this->pubblicazioneDiretta();
                        break;
                    case $this->nameForm . '_RiceviRuoloIUV':
                        $this->riceviRuoloIuv();
                        break;
                }
        }
    }

    protected function inserimento() {
//        $arrayPenden[0] = array(
//            "PROGKEYTAB" => 903,
//            "CODTIPSCAD" => 81,
//            "SUBTIPSCAD" => 0,
//            "DESCRPEND" => "Asilo Nido Rette asili nido",
//            "TIPOPENDEN" => 0,
//            "MODPROVEN" => 'SBO',
//            "PROGSOGG" => 270,
//            "ANNORIF" => 2018,
//            "PROGCITYSC" => 2000,
//            "NUMRATA" => 1,
//            "NUMDOC" => 1001,
//            "DATACREAZ" => '20111005',
//            "DATAULTMOD" => '20180918',
//            "DATASTAMPA" => '',
//            "DATANOTIF" => '',
//            "DATASCADE" => '20191031',
//            "IMPDAPAGTO" => 125.36,
//            "IMPPAGTOT" => 0,
//            "DATAPAG" => '',
//            "MODPAGAM" => 0,
//            "FORMATODOC" => '',
//            "DOCBINARY" => '',
//            "CODOTTBOLL" => '',
//            "STATO" => 0,
//            "DESSTATO" => '',
//            "FDETTAGLIO" => 1,
//            "FDOCUMPDF" => 0,
//            "FSTAMPABOL" => 0,
//            "FSTAMPAF24" => 0,
//            "FPAGONLINE" => 1,
//            "NOTA" => 'BAGGETTA SOFIA-BAGGETTA SOFIA',
//            "PATHDOC" => '',
//            "FLAG_PUBBL" => 4,
//            "PROGCITYSCORI" => 0,
//            "ANNOEMI" => 2018,
//            "NUMEMI" => 1,
//            "IDBOL_SERE" => 1,
//        );
//        $arrayPenden = array(
//            "PROGKEYTAB" => 904,
//            "CODTIPSCAD" => 81,
//            "SUBTIPSCAD" => 0,
//            "DESCRPEND" => "Asilo Nido Rette asili nido",
//            "TIPOPENDEN" => 0,
//            "MODPROVEN" => 'SBO',
//            "PROGSOGG" => 270,
//            "ANNORIF" => 2018,
//            "PROGCITYSC" => 2001,
//            "NUMRATA" => 1,
//            "NUMDOC" => 1001,
//            "RAGSOC" => "LUCAPUMA",
//            "DATACREAZ" => '20111005',
//            "DATAULTMOD" => '20180918',
//            "DATASTAMPA" => '',
//            "DATANOTIF" => '',
//            "DATASCADE" => '20191031',
//            "IMPDAPAGTO" => 125.36,
//            "IMPPAGTOT" => 0,
//            "DATAPAG" => '',
//            "MODPAGAM" => 0,
//            "FORMATODOC" => '',
//            "DOCBINARY" => '',
//            "CODOTTBOLL" => '',
//            "STATO" => 0,
//            "DESSTATO" => '',
//            "FDETTAGLIO" => 1,
//            "FDOCUMPDF" => 0,
//            "FSTAMPABOL" => 0,
//            "FSTAMPAF24" => 0,
//            "FPAGONLINE" => 1,
//            "NOTA" => 'BAGGETTA SOFIA-BAGGETTA SOFIA',
//            "PATHDOC" => '',
//            "FLAG_PUBBL" => 4,
//            "PROGCITYSCORI" => 0,
//            "ANNOEMI" => 2018,
//            "NUMEMI" => 1,
//            "IDBOL_SERE" => 1,
//        );
//        $pagoPa = $this->getPagoPa(1);
//        if ($pagoPa) {
//            $pagoPa->pubblicazioneSingolaDaPendenza($arrayPenden);
//        }

        $intermediari = $this->intermediario();

        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->pubblicazioneMassiva();
            }
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

    protected function pubblicazione() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                if ($_POST['id'] === $this->nameForm . '_PubblicazioneTest') {
                    $pagoPa->setSimulazione(true);
                }
                if ($_POST['id'] === $this->nameForm . '_PubblicazioneTestSF') {
                    $pagoPa->setSimulazioneSF(true);
                }
                $pagoPa->pubblicazione();
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
    }

    protected function arricchimento() {
        $intermediari = $this->intermediario();
        foreach ($intermediari as $intermediario) {
            $pagoPa = $this->getPagoPa($intermediario['INTERMEDIARIO']);
            if ($pagoPa) {
                $pagoPa->ricevutaArricchita();
            }
        }
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
        $this->libDB_BTA = new cwbLibDB_BTA();
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
