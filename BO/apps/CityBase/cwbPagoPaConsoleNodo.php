<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMaster.class.php';
include_once ITA_LIB_PATH . '/itaPHPPagoPa/itaPagoPa.class.php';
include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');

function cwbPagoPaConsoleNodo() {
    $cwbPagoPaConsoleNodo = new cwbPagoPaConsoleNodo();
    $cwbPagoPaConsoleNodo->parseEvent();
    return;
}

class cwbPagoPaConsoleNodo extends cwbBpaGenTab {

    private $result_tab_invii; // appoggio il valore della griglia in sessione 

    const CLI_OPERAZIONI_PPA = 'cwbPagoPaSincronizzaNodo.php';

    protected function initVars() {
        $this->libDB = new cwbLibDB_BGE();
        $this->noCrud = true;
        $this->result_tab_invii = cwbParGen::getFormSessionVar($this->nameForm, 'result_tab_invii');
    }

    public function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'result_tab_invii', $this->result_tab_invii);
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->tabpaneInvii();
                Out::show($this->nameForm . '_divGestione');
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridScadenze':
                        cwblib::apriFinestraDettaglio('cwbBgeAgidScadenze', $this->nameForm, '', $_POST['id'], '', $_POST['rowid']);
                        break;
                }
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AllineaDati':
                        $this->allineaDati();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_tabpaneInvii':
                    case $this->nameForm . '_gridInvii' :
                        $this->tabpaneInvii();
                        break;
                    case $this->nameForm . '_tabpaneRendiconta':
                    case $this->nameForm . '_gridRendiconta' :
                        $this->tabpaneRendiconta();
                        break;

                    case $this->nameForm . '_tabpaneScadenze':
                    case $this->nameForm . '_gridScadenze' :
                        $this->tabpaneScadenze();
                        break;

                    case $this->nameForm . '_tabpaneScadenze1':
                    case $this->nameForm . '_gridScadenze1' :
                        $this->tabpaneScadenze1();
                        break;

                    case $this->nameForm . '_tabpaneScadenze2':
                    case $this->nameForm . '_gridScadenze2' :
                        $this->tabpaneScadenze2();
                        break;

                    case $this->nameForm . '_tabpaneRiscossioni1':
                        $this->tabpaneRiscossioni1();
                        break;
                    case $this->nameForm . '_tabpaneRiscossioni2':
                        $this->tabpaneRiscossioni2();
                        break;
                }
                break;

            case "cellSelect":
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridInvii':
                        list($proginv, $annoemi, $numemi, $idbol_sere) = explode('|', $_POST['rowid']);
                        switch ($_POST['colName']) {
                            case 'SCADENZE':
                                $externalParams['ROW_ID'] = $_POST['rowid'];
                                cwbLib::apriFinestraDettaglio('cwbBgeAgidScadenze', $this->nameForm, 'returnFromBgeAgidScadenze', $_POST['id'], $externalParams, $externalParams);
                                break;

                            case 'RICEZ':
                                $externalParams['IDINV'] = $proginv;
                                cwbLib::apriFinestraDettaglio('cwbBgeAgidRicez', $this->nameForm, 'returnFromBgeAgidRicez', $_POST['id'], $externalParams, $externalParams);
                                break;

                            case 'ALLEG':
                                $externalParams['IDINVRIC'] = $proginv;
                                foreach ($this->result_tab_invii as $value) {
                                    if ($proginv == $value['PROGKEYTAB']) {
                                        // Ciclo la lista degli invii, cerco il record cliccato e verifico che tipo di invio sto trattando
                                        if ($value['TIPO'] === 'Fornitura di Pubblicazione') {
                                            $externalParams['TIPO'] = 1;
                                        } elseif ($value['TIPO'] === 'Fornitura di Cancellazione') {
                                            $externalParams['TIPO'] = 2;
                                        }
                                    }
                                }
                                cwbLib::apriFinestraDettaglio('cwbBgeAgidAllegati', $this->nameForm, 'returnFromBgeAgidAllegati', $_POST['id'], $externalParams, $externalParams);
                                break;

                            case 'INVIO':
                                foreach ($this->result_tab_invii as $value) {
                                    if ($value['INVIO'] && $rowid == $value['PROGKEYTAB']) {
                                        // Con la descrizione in grid, vado a reperire il codice dell'intermediario
                                        $filtri['DESCRIZIONE'] = $value['INTERMEDIARIO'];
                                        $intermediario = $this->libDB->leggiBgeAgidInterm($filtri);
                                        $this->reinvio($rowid, $intermediario);
                                    }
                                }
                                break;
                        }
                    case $this->nameForm . '_gridRendiconta':
                        list($rowid, $annoemi, $numemi, $idbol_sere) = explode('|', $_POST['rowid']);
                        switch ($_POST['colName']) {
                            case 'RISCO':
                                $externalParams['PROGRIC'] = $rowid;
                                cwbLib::apriFinestraDettaglio('cwbBgeAgidRisco', $this->nameForm, 'returnFromBgeAgidRisco', $_POST['id'], $externalParams, $externalParams);
                                break;
                            case 'DOWNLOAD':
                                $this->scaricaAllegato($rowid);
                                break;
                        }
                        break;
                }
        }
    }

    protected function tabpaneInvii() {
        $invii = $this->libDB->leggiBgeAgidInviiConsoleNodo(array());
        if ($invii) {
            $this->caricaGrid('gridInvii', $invii, 1);
        } else {
            TableView::clearGrid($this->nameForm . '_gridInvii');
        }
    }

    private function allineaDati() {
        $cmd = ITA_BASE_PATH . '/cli/' . self::CLI_OPERAZIONI_PPA;
        $devLib = new devLib();
        $utente = $devLib->getEnv_config('PARAMS_CLI_ASYNC', 'codice', 'UTENTE_CLI_ASYNC', false);
        $psw = $devLib->getEnv_config('PARAMS_CLI_ASYNC', 'codice', 'PASSWORD_CLI_ASYNC', false);
        $arguments = array(
            $utente['CONFIG'],
            $psw['CONFIG'],
            cwbParGen::getSessionVar('ditta')
        );
        itaLib::execAsync($cmd, $arguments);
    }

    protected function tabpaneScadenze() {
        $filtri = array();
        if ($_POST['IUV'] != '') {
            $filtri['IUV'] = $this->formData['IUV'];
        }
        if ($_POST['CODTIPSCAD'] != '') {
            $filtri['CODTIPSCAD'] = $this->formData['CODTIPSCAD'];
        }
        if ($_POST['NUMDOC'] != '') {
            $filtri['NUMDOC'] = $this->formData['NUMDOC'];
        }
        if ($_POST['PROGSOGG'] != '') {
            $filtri['PROGSOGG'] = $this->formData['PROGSOGG'];
        }
        if ($_POST['SUBTIPSCAD'] != '') {
            $filtri['SUBTIPSCAD'] = $this->formData['SUBTIPSCAD'];
        }
        if ($_POST['PROGCITYSC'] != '') {
            $filtri['PROGCITYSC'] = $this->formData['PROGCITYSC'];
        }
        if ($_POST['CODFISCALE'] != '') {
            $filtri['CODFISCALE'] = $this->formData['CODFISCALE'];
        }
        $codStato = $this->filterStato($_POST['STATO']);
        if (count($codStato) === 1) {
            $filtri['STATO'] = $codStato[0];
        } elseif (count($codStato) > 1) {
            $filtri['STATO_or'] = $codStato;
        }
        $scadenze = $this->libDB->leggiBgeAgidScadenze($filtri);
        if ($scadenze) {
            $this->caricaGrid('gridScadenze', $scadenze, 2);
        } else {
            TableView::clearGrid($this->nameForm . '_gridScadenze');
        }
    }

    protected function tabpaneRendiconta() {
        $filtri['TIPO'] = 15;
        $rendicontazioni = $this->libDB->leggiBgeAgidRicez($filtri);
        if ($rendicontazioni) {
            $this->caricaGrid('gridRendiconta', $rendicontazioni, 4);
        } else {
            TableView::clearGrid($this->nameForm . '_gridRendiconta');
        }
    }

    protected function tabpaneRiscossioni1() {
        $scadenze = $this->libDB->leggiBgeAgidScadenzeNonRicon(array());
        if ($scadenze) {
            $this->caricaGrid('gridRiscossioni1', $scadenze, 2);
        } else {
            TableView::clearGrid($this->nameForm . '_gridRiscossioni1');
        }
    }

    protected function tabpaneRiscossioni2() {
        $riscossioni = $this->libDB->leggiBgeAgidRiscoScadenzaNonCollegata(array());
        if ($riscossioni) {
            $this->caricaGrid('gridRiscossioni2', $riscossioni, 5);
        } else {
            TableView::clearGrid($this->nameForm . '_gridRiscossioni2');
        }
    }

    protected function scaricaAllegato($index) {
        $filtri['PROGKEYTAB'] = $index;
        $riscossione = $this->libDB->leggiBgeAgidRicezAllegato($filtri);

        $filename = time();
        $corpo = null;

        $filename = itaLib::getUploadPath() . "/temp" . $filename . '.zip';
        file_put_contents($filename, stream_get_contents($riscossione['ZIPFILE']));
        $zip = new ZipArchive;
        if ($zip->open($filename)) {
            $extractDir = itaLib::getUploadPath() . '/';
            $zip->extractTo($extractDir);
            $corpo = file_get_contents($extractDir . $riscossione['NOME_FILE']);
            $zip->close();
        }

        if ($corpo) {
            cwbLib::downloadDocument($riscossione['NOME_FILE'] . '.txt', $corpo, true);
        } else {
            Out::msgStop("Errore", "Errore reperimento binario");
        }
        unlink($filename);
        unlink($extractDir . '/' . $riscossione['NOME_FILE']);
    }

    protected function tabpaneScadenze1() {
        $filtri['STATO_maggiore'] = 5;
        $scadenze = $this->libDB->leggiBgeAgidScadenze($filtri);
        if ($scadenze) {
            $this->caricaGrid('gridScadenze1', $scadenze, 2);
        } else {
            TableView::clearGrid($this->nameForm . '_gridScadenze1');
        }
    }

    protected function tabpaneScadenze2() {
        $filtri['STATO_minore'] = 5;
        $scadenze = $this->libDB->leggiBgeAgidScadenze($filtri);
        if ($scadenze) {
            $this->caricaGrid('gridScadenze2', $scadenze, 2);
        } else {
            TableView::clearGrid($this->nameForm . '_gridScadenze2');
        }
    }

    protected function caricaGrid($gridName, $dati, $elaborazione) {
        if ($dati) {
            $helper = new cwbBpaGenHelper();
            $helper->setNameForm($this->nameForm);
            $helper->setGridName($gridName);
            $ita_grid01 = $helper->initializeTableArray($dati);
            switch ($elaborazione) {
                case 1:
                    $this->getDataPage($ita_grid01, $this->elaboraGridInvii($ita_grid01));
                    break;

                case 2:
                    $this->getDataPage($ita_grid01, $this->elaboraGridScadenze($ita_grid01));
                    break;

                case 3:
                    $this->getDataPage($ita_grid01, $this->elaboraGridRiscossioni($ita_grid01));
                    break;

                case 4:
                    $this->getDataPage($ita_grid01, $this->elaboraGridRendiconta($ita_grid01));
                    break;

                case 5:
                    $this->getDataPage($ita_grid01, $this->elaboraGridRiscoNonScad($ita_grid01));
                    break;
            }
            TableView::enableEvents($this->nameForm . '_' . $gridName);
        } else {
            TableView::clearGrid($this->nameForm . '_' . $gridName);
        }
    }

    protected function elaboraGridInvii($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsInvii($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraGridScadenze($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsScadenze($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraGridRendiconta($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsRendiconta($Result_tab_tmp);

        return $Result_tab;
    }

    protected function elaboraGridRiscoNonScad($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecordsRiscoNonScad($Result_tab_tmp);

        return $Result_tab;
    }

    protected function getDataPage($ita_grid, $Result_tab) {
        if ($Result_tab == null) {
            return $ita_grid->getDataPage('json');
        } else {
            return $ita_grid->getDataPageFromArray('json', $Result_tab);
        }
    }

    protected function elaboraRecordsRiscoNonScad($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            switch ($Result_tab[$key]['PROVPAGAM']) {
                case 1:
                    $Result_tab[$key]['PROVPAGAM'] = 'Cityportal';
                    break;
                case 2:
                    $Result_tab[$key]['PROVPAGAM'] = 'Nodo';
                    break;
                case 3:
                    $Result_tab[$key]['PROVPAGAM'] = 'Extra Nodo';
                    break;
                case 4:
                    $Result_tab[$key]['PROVPAGAM'] = 'Sconosciuto';
                    break;
            }
        }
        return $Result_tab;
    }

    protected function elaboraRecordsInvii($Result_tab) {
        $path_all = ITA_BASE_PATH . '/apps/CityBase/resources/attachment-24x24.png';
        $path_ric = ITA_BASE_PATH . '/apps/CityBase/resources/mail-24x24.png';
        $path_sca = ITA_BASE_PATH . '/apps/CityBase/resources/deadline-24x24.png';
        $path_inv = ITA_BASE_PATH . '/apps/CityBase/resources/send-24x24.png';

        foreach ($Result_tab as $key => $Result_rec) {
            $filtri['PROGINV'] = $Result_rec['PROGKEYTAB'];
            $Result_tab[$key]['FLAG_DIS'] = cwbLibHtml::formatDataGridFlag($Result_tab[$key]['FLAG_DIS']);
            $Result_tab[$key]['DATAINVIO'] = date('d-m-Y', strtotime($Result_tab[$key]['DATAINVIO']));
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);

            $Result_tab[$key]['SCADENZE'] = cwbLibHtml::formatDataGridIcon('', $path_sca);

            $Result_tab[$key]['RICEZ'] = cwbLibHtml::formatDataGridIcon('', $path_ric);
            $Result_tab[$key]['ALLEG'] = cwbLibHtml::formatDataGridIcon('', $path_all);

            $interm = $this->libDB->leggiBgeAgidIntermChiave($Result_rec['INTERMEDIARIO']);
            $Result_tab[$key]['INTERMEDIARIO'] = $interm['DESCRIZIONE'];

            switch ($Result_rec['TIPO']) {
                case 1:
                    $Result_tab[$key]['TIPO'] = 'Fornitura di Pubblicazione';
                    break;
                case 2:
                    $Result_tab[$key]['TIPO'] = 'Fornitura di Cancellazione';
                    break;
            }

            switch ($Result_rec['STATO']) {
                case 0:
                    $Result_tab[$key]['STATO'] = 'Ripristinata';
                    break;
                case 1:
                    $Result_tab[$key]['STATO'] = 'Inviato';
                    break;
                case 2:
                    $Result_tab[$key]['STATO'] = 'Accettato';
                    break;
                case 3:
                    $Result_tab[$key]['STATO'] = 'Rifiutato';
                    break;
                case 4:
                    $Result_tab[$key]['STATO'] = 'Pubblicato in attesa di IUV';
                    break;
                case 5:
                    $Result_tab[$key]['STATO'] = 'Pubblicato con IUV';
                    break;
                case 6:
                    $Result_tab[$key]['STATO'] = 'Non pubblicato';
                    break;
                case 7:
                    $Result_tab[$key]['STATO'] = 'Pubblicato parzialmente in attesa degli IUV';
                    break;
                case 8:
                    $Result_tab[$key]['STATO'] = 'Cancellato';
                    break;
                case 10:
                    $Result_tab[$key]['STATO'] = 'Reinviato';
                    break;
            }

            if (cwbLibCalcoli::dateCompare(date('d-m-Y'), $Result_tab[$key]['DATAINVIO']) && $Result_tab[$key]['STATO'] === 'Accettato') {
                $Result_tab[$key]['INVIO'] = cwbLibHtml::formatDataGridIcon('', $path_inv);
            }
        }

        $this->result_tab_invii = $Result_tab;
        return $Result_tab;
    }

    protected function elaboraRecordsRendiconta($Result_tab) {
        $path_risco = ITA_BASE_PATH . '/apps/CityBase/resources/money-24x24.png';
        $path_download = ITA_BASE_PATH . '/apps/CityBase/resources/download-24x24.png';

        foreach ($Result_tab as $key => $Result_rec) {
            $interm = $this->libDB->leggiBgeAgidIntermChiave($Result_rec['INTERMEDIARIO']);
            $Result_tab[$key]['INTERMEDIARIO'] = $interm['DESCRIZIONE'];
            $filtri['PROGRIC'] = $Result_tab[$key]['PROGKEYTAB'];
            $filtri['IDSCADENZA_maggiore'] = 1;
            $numRiscossioni = count($this->libDB->leggiBgeAgidRisco($filtri));
            if ($numRiscossioni > 0) {
                $Result_tab[$key]['NUMRISCO'] = count($this->libDB->leggiBgeAgidRisco($filtri));
                $Result_tab[$key]['RISCO'] = cwbLibHtml::formatDataGridIcon('', $path_risco);
            } else {
                $Result_tab[$key]['NUMRISCO'] = 0;
            }
            $Result_tab[$key]['DOWNLOAD'] = cwbLibHtml::formatDataGridIcon('', $path_download);
        }
        return $Result_tab;
    }

    protected function elaboraRecordsScadenze($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['PROGKEYTAB_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGKEYTAB']);
            switch ($Result_tab[$key]['TIPOPENDEN']) {
                case 0:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza unica scadenza';
                    break;
                case 1:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza di testata o rata unica';
                    break;
                case 2:
                    $Result_tab[$key]['TIPOPENDEN'] = 'Pendenza di dettaglio di rata';
                    break;
            }

            switch ($Result_rec['STATO']) {
                case 0:
                    $Result_tab[$key]['STATO'] = 'Ripristinata';
                    break;
                case 1:
                    $Result_tab[$key]['STATO'] = 'Creata';
                    break;
                case 2:
                    $Result_tab[$key]['STATO'] = 'Inviata';
                    break;
                case 3:
                    $Result_tab[$key]['STATO'] = 'Sospesa';
                    break;
                case 4:
                    $Result_tab[$key]['STATO'] = 'Pubblicata in attesa di IUV';
                    break;
                case 5:
                    $Result_tab[$key]['STATO'] = 'Pubblicata con IUV';
                    break;
                case 6:
                    $Result_tab[$key]['STATO'] = 'In cancellazione';
                    break;
                case 7:
                    $Result_tab[$key]['STATO'] = 'Cancellata';
                    break;
                case 8:
                    $Result_tab[$key]['STATO'] = 'In sostituzione';
                    break;
                case 9:
                    $Result_tab[$key]['STATO'] = 'Sostituita';
                    break;
                case 10:
                    $Result_tab[$key]['STATO'] = 'Pagata';
                    break;
                case 11:
                    $Result_tab[$key]['STATO'] = 'Pagata ma NON Riconciliata';
                    break;
                case 12:
                    $Result_tab[$key]['STATO'] = 'Riconciliata';
                    break;
            }
        }
        return $Result_tab;
    }

    protected function filterStato($stato) {
        if ($stato != '') {
            $stati = array(
                1 => "CREATA",
                2 => "INVIATA",
                3 => "SOSPESA",
                4 => "PUBBLICATA IN ATTESA DI IUV",
                5 => "PUBBLICATA CON IUV",
                6 => "IN CANCELLAZIONE",
                7 => "CANCELLATA",
                8 => "IN SOSTITUZIONE",
                9 => "SOSTITUITA",
                10 => "PAGATA",
                11 => "PAGATA MA NON RICONCILIATA",
                12 => "RICONCILIATA",
            );
            foreach ($stati as $key => $value) {
                if (stristr($value, $stato)) {
                    $codStato[] = $key;
                }
            }
        }

        return $codStato;
    }

    protected function reinvio($progkeytabInvio, $intermediario) {
//        $pagoPa = new itaPagoPa($intermediario[0]['INTERMEDIARIO']);
//        $pagoPa->reinvioPubblicazione($progkeytabInvio);
//        $pagoPa->cancellazioneMassiva();
//        $pagoPa->pubblicazione();
    }

}
