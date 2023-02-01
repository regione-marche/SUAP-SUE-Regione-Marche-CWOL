<?php

class praMod extends itaModelFO {

    private $praLib;
    private $praErr;
    private $praLibEventi;
    private $PRAM_DB;
    private $PRAM_DB_R;
    private $configTclass = array(
        0 => array(
            'order' => "ORDER BY ANATIP.TIPDES, ANAPRA.PRADES__1"
        ),
        1 => array(
            'order' => "ORDER BY ANASET.SETSEQ, ANASET.SETDES, ANAATT.ATTSEQ, ANAATT.ATTDES, ANAPRA.PRADES__1"
        ),
        2 => array(
            'order' => "ORDER BY ANATIP.TIPDES, ANAATT.ATTSEQ, ANAATT.ATTDES, ANAPRA.PRADES__1"
        ),
        4 => array(
            'order' => "ORDER BY ITEEVT.IEVTSP, ANASET.SETSEQ, ANASET.SETDES, ANAATT.ATTSEQ, ANAATT.ATTDES, ANATIP.TIPDES, ANAPRA.PRADES__1"
        ),
        5 => array(
            'order' => "ORDER BY ANASET.SETSEQ, ANASET.SETDES, ANAATT.ATTSEQ, ANAATT.ATTDES, ANATIP.TIPDES, ANAPRA.PRADES__1"
        )
    );

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
            $this->praLibEventi = new praLibEventi();

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->PRAM_DB_R = $this->praLib->GetPramMaster($this->PRAM_DB);
        } catch (Exception $e) {
            
        }
    }

    private function verificaAccorpaRichiesta($numeroRichiesta) {
        if (strlen($numeroRichiesta) != 10) {
            return false;
        }

        $Proric_rec = $this->praLib->GetProric(addslashes($numeroRichiesta), 'codice', $this->PRAM_DB);
        if (!$Proric_rec || $Proric_rec['RICFIS'] != frontOfficeApp::$cmsHost->getCodFisFromUtente()) {
            return false;
        }

        $Ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . addslashes($numeroRichiesta) . "' AND ITERICUNI = '1'");
        if (!$Ricite_tab) {
            return false;
        }

        $passi_accorpamento_eseguiti = true;
        foreach ($Ricite_tab as $Ricite_rec) {
            if (!$this->praLib->checkEsecuzionePasso($Proric_rec, $Ricite_rec)) {
                $passi_accorpamento_eseguiti = false;
            }
        }

        if ($passi_accorpamento_eseguiti) {
            /*
             * Se tutti i passi d'accorpamento sono stati eseguiti,
             * non posso accorpare altre richieste.
             */
            return false;
        }

        return true;
    }

    public function parseEvent() {
        parent::parseEvent();

        if (isset($this->request['confermaView'])) {
            $this->request['event'] = 'view';
        }

        if (isset($this->request['accorpa'])) {
            if (!$this->verificaAccorpaRichiesta($this->request['accorpa'])) {
                $this->request['accorpa'] = false;
            } else {
                $html = new html;
                $Proric_rec = $this->praLib->GetProric(addslashes($this->request['accorpa']), 'codice', $this->PRAM_DB);
                $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], 'codice', $this->PRAM_DB);

                $textRichiesta = intval(substr($Proric_rec['RICNUM'], 4)) . '/' . substr($Proric_rec['RICNUM'], 0, 4) . ' - ';
                $textRichiesta .= $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4'];
                $textAlert = 'Si sta selezionando un procedimento che andrà accorpato alla seguente richiesta:<br><b>' . $textRichiesta . '</b>';

                if ($this->config['online_page']) {
                    $urlRichiesta = ItaUrlUtil::GetPageUrl(array('p' => $this->config['online_page'], 'event' => 'navClick', 'ricnum' => $Proric_rec['RICNUM'], 'direzione' => 'primoAcc'));
                    $textAlert .= '<br><br>' . $html->getButton('<i class="icon ion-reply italsoft-icon"></i><span>Torna alla richiesta</span>', $urlRichiesta);
                }

                output::addAlert($textAlert, 'Procedimento da accorpare', 'info');
            }
        }

        switch ($this->request['event']) {
            default:
                $this->disegnaFormRicerca();
                $this->disegnaListaProcedimenti();
                break;

            case 'view':
                $this->config['tclass'] = $this->request['tclass'];

                $this->disegnaFormRicerca();
                $this->disegnaListaProcedimenti();
                break;

            case 'vediAllegato':
                if (!$this->vediAllegato($this->request['procedi'], $this->request['allegato'], $this->request['sportello'])) {
                    output::addAlert('L\'allegato informativo del procedimento ' . $this->request['procedi'] . ' non è stato trovato.', 'Attenzione!', 'warning');

                    $this->disegnaFormRicerca();
                    $this->disegnaListaProcedimenti();
                }
                break;

            case 'startSearch':
                if (($tclass = $this->request['tclass'])) {
                    $this->config['tclass'] = $tclass;
                }

                $desc = trim($this->request['desc']);

                if ($desc == '') {
                    output::addAlert('Compilare il campo di ricerca.');

                    $this->disegnaFormRicerca();
                    $this->disegnaListaProcedimenti();
                    break;
                }

                if (!$this->disegnaRisultatiRicerca($desc)) {
                    output::addAlert('La ricerca non ha prodotto nessun risultato.');

                    $this->disegnaFormRicerca();
                    $this->disegnaListaProcedimenti();
                }
                break;
        }

        $return = output::$html_out;
        output::$html_out = '';
        return $return;
    }

    /**
     * Disegno della form di ricerca.
     */
    private function disegnaFormRicerca() {
        if (isset($this->config['search_form']) && !$this->config['search_form']) {
            return false;
        }

        output::addForm(ItaUrlUtil::GetPageUrl(array()), 'GET', array(
            "id" => "praMod"
        ));

        output::addHidden('event', 'startSearch');

        output::addInput('text', 'Ricerca procedimenti', array(
            'name' => 'desc',
            'maxlength' => 100,
            'size' => 40
        ));

        output::addSubmit('Cerca');

        output::closeTag('form');
    }

    private function disegnaRisultatiRicerca($searchQuery) {
        $parole_chiavi = explode('"', $searchQuery);
        $searchStrings = array();

        foreach ($parole_chiavi as $key => $parte) {
            if ($key % 2 == 0) {
                $parole = explode(' ', trim($parte));
                foreach ($parole as $parola) {
                    if (trim($parola) == '') {
                        continue;
                    }

                    $searchStrings[] = $parola;
                }
            } else {
                if ($parte == '') {
                    continue;
                }

                $searchStrings[] = $parte;
            }
        }

        $searchColumns = array(
            $this->PRAM_DB->strConcat('PRADES__1', 'PRADES__2', 'PRADES__3', 'PRADES__4'),
            'EVTDESCR'
        );

        switch ($this->config['tclass']) {
            case 0:
                $searchColumns[] = 'TIPDES';
                break;

            case 1:
                $searchColumns[] = 'SETDES';
                $searchColumns[] = 'ATTDES';
                break;

            case 2:
                $searchColumns[] = 'TIPDES';
                $searchColumns[] = 'ATTDES';
                break;

            case 3:
            case 4:
                $searchColumns[] = 'TSPDES';
                $searchColumns[] = 'SETDES';
                $searchColumns[] = 'ATTDES';
                break;
        }

        $whereSQLArray = array();
        foreach ($searchStrings as $searchString) {
            foreach ($searchColumns as $searchColumn) {
                $whereSQLArray[] = "LOWER($searchColumn) LIKE LOWER('%" . addslashes($searchString) . "%')";
            }
        }

        $whereSQL = count($whereSQLArray) ? "AND (" . implode(' OR ', $whereSQLArray) . ")" : '';
        $whereSportelli = $this->praLib->getWhereSportelli($this->config['sportello']);
        $orderBy = $this->configTclass[$this->config['tclass']]['order'];
        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSQL($whereSportelli, $whereSQL, 'ORDER BY ' . $this->PRAM_DB->strConcat('ITEEVT.IEVDESCR', 'ANAPRA.PRADES__1')), true);

        if (!count($iteevt_tab)) {
            return false;
        }

        $arrayData = array('header' => array(), 'body' => array());

        switch ($this->config['tclass']) {
            case 0:
                $arrayData['header'][] = 'Tipologia';
                break;

            case 1:
                $arrayData['header'][] = 'Settore';
                $arrayData['header'][] = 'Attività';
                break;

            case 2:
                $arrayData['header'][] = 'Tipologia';
                $arrayData['header'][] = 'Attività';
                break;

            case 3:
            case 4:
                $arrayData['header'][] = 'Sportello';
                $arrayData['header'][] = 'Settore';
                $arrayData['header'][] = 'Attività';
                break;
        }

        $arrayData['header'][] = 'Procedimento';
        $arrayData['header'][] = 'Allegato';
        if ($this->config['online'] == 1 && $this->config['online_page']) {
            $arrayData['header'][] = 'Compila online';
        }

        foreach ($iteevt_tab as $key => $iteevt_rec) {
            $treeViewRecord = $this->getDatiProcedimento($iteevt_rec);

            $arrayDataRecord = array();

            switch ($this->config['tclass']) {
                case 0:
                    $arrayDataRecord[] = $treeViewRecord['TIPOLOGIA'];
                    break;

                case 1:
                    $arrayDataRecord[] = $treeViewRecord['SETTORE'];
                    $arrayDataRecord[] = $treeViewRecord['ATTIVITA'];
                    break;

                case 2:
                    $arrayDataRecord[] = $treeViewRecord['TIPOLOGIA'];
                    $arrayDataRecord[] = $treeViewRecord['ATTIVITA'];
                    break;

                case 3:
                case 4:
                    $arrayDataRecord[] = $treeViewRecord['SPORTELLO'];
                    $arrayDataRecord[] = $treeViewRecord['SETTORE'];
                    $arrayDataRecord[] = $treeViewRecord['ATTIVITA'];
                    break;
            }

            $arrayDataRecord[] = "<a href=\"{$treeViewRecord['COLLEGAMENTO']}\">{$treeViewRecord['OGGETTO']}</a>";

            $htmlAllegato = '';

            if ($treeViewRecord['BUTTON_ALLEGATO']) {
                $htmlAllegato = $this->createLinkFromButton($treeViewRecord['BUTTON_ALLEGATO']);
            }

            $arrayDataRecord[] = $htmlAllegato;

            if ($this->config['online'] == 1 && $this->config['online_page']) {
                $htmlCompila = '';

                if ($treeViewRecord['BUTTON_COMPILA']) {
                    $htmlCompila = $this->createLinkFromButton($treeViewRecord['BUTTON_COMPILA']);
                }

                $arrayDataRecord[] = $htmlCompila;
            }

            $arrayData['body'][] = $arrayDataRecord;
        }
        
        $this->disegnaFormRicerca();

        output::addBr();

        output::appendHtml('<div class="grid" style="max-width: none; padding: 0;">');
        output::appendHtml('<div class="col-1-2" style="line-height: 32px;">');
        output::appendHtml('<b>Trovati ' . count($iteevt_tab) . ' risultati per \'' . $searchQuery . '\'</b>');
        output::appendHtml('</div>');
        output::appendHtml('<div class="col-1-2" style="text-align: right; padding: 0;">');
        output::addButton('Torna all\'elenco dei procedimenti', ItaUrlUtil::GetPageUrl(array()), 'secondary');
        output::appendHtml('</div>');
        output::appendHtml('</div>');

        output::addBr();

        output::addTable($arrayData);

        output::appendHtml('<div style="text-align: right;">');
        output::addButton('Torna all\'elenco dei procedimenti', ItaUrlUtil::GetPageUrl(array()), 'secondary');
        output::appendHtml('</div>');

        return true;
    }

    private function vediAllegato($procedimento, $allegato, $sportello) {
        /*
         * @Todo Analizzare astrazione logica di business (2a fase).
         */

        $Anapra_rec = $this->praLib->GetAnapra(addslashes($procedimento), 'codice', $this->PRAM_DB);

        if (!$Anapra_rec) {
            return false;
        }

        $allegato = itaCrypt::decrypt($allegato);

        $repositoryUrl = ITA_PROC_REPOSITORY;

        if ($Anapra_rec['PRASLAVE'] != 1) {
            $PRAM_SOURCE = $this->PRAM_DB;
            $repositoryUrl = ITA_PROC_REPOSITORY;
        } elseif ($Anapra_rec['PRASLAVE'] == 1 && $this->PRAM_DB_R !== $this->PRAM_DB && ITA_MASTER_REPOSITORY) {
            $PRAM_SOURCE = $this->PRAM_DB_R;
            $repositoryUrl = ITA_MASTER_REPOSITORY;
        }

        $file = $repositoryUrl . "allegati/" . $procedimento . "/" . $allegato;

        if (!file_exists($file)) {
            return false;
        }

        $taskEncrypt = false;

        $Anatsp_rec = $this->praLib->GetAnatsp(addslashes($sportello), 'codice', $this->PRAM_DB);

        if (pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
            if ($Anatsp_rec['TSPBLOCK'] == 1) {
                if (!$tempFolder = $this->praLib->getCartellaTemporaryPratiche()) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0008', "Creazione cartella <b>$tempFolder</b> fallita Pratica N. " . $Ricnum, __CLASS__);
                    return false;
                }

                $file_enc = $tempFolder . pathinfo($allegato, PATHINFO_FILENAME) . "-enc-" . time() . ".pdf";

                if (ITA_JVM_PATH != "" && file_exists(ITA_JVM_PATH)) {
                    $xmlEncrypt = $file_enc . ".xml";
                    $FileXml = fopen($xmlEncrypt, "w");

                    if (!file_exists($xmlEncrypt)) {
                        return false;
                    }

                    $input = $file;
                    $output = $file_enc;
                    $xml = $this->praLib->CreaXmlEncrypt($input, $output);
                    fwrite($FileXml, $xml);
                    fclose($FileXml);
                    exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlEncrypt ", $ret);

                    foreach ($ret as $key => $value) {
                        $arrayExec = explode("|", $value);
                        if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                            $taskEncrypt = true;
                            break;
                        }
                    }

                    unlink($xmlEncrypt);
                }
            }
        }

        if ($taskEncrypt == true) {
            if (!$file_enc) {
                return false;
            }

            $this->frontOfficeLib->vediAllegato($file_enc);
            unlink($file_enc);
        } else {
            if (!$file) {
                return false;
            }

            $this->frontOfficeLib->vediAllegato($file);
        }

        return true;
    }

    private function disegnaListaProcedimenti() {
        $accordionOpen = $this->config['open_accordion'] ?: 0;

        $whereSportelli = $this->praLib->getWhereSportelli($this->config['sportello']);

        switch ($this->config['tclass']) {
            case 0:
                $count = $this->disegnaListaProcedimentiPerTipologia($accordionOpen, $whereSportelli);
                break;

            case 1:
                $count = $this->disegnaListaProcedimentiPerSettoreAttivita($accordionOpen, $whereSportelli);
                break;

            case 2:
                $count = $this->disegnaListaProcedimentiPerTipologiaAttivita($accordionOpen, $whereSportelli);
                break;

            case 5:
                $count = $this->disegnaListaProcedimentiPerSettoreAttivitaTipologia($accordionOpen, $whereSportelli);
                break;
        }

        if (!isset($this->config['proc_count']) || $this->config['proc_count']) {
            output::addBr();
            output::appendHtml("<div style=\"float: left; font-size: 1.2em;\"><b>Totale procedimenti: $count</b></div>");
        }
    }

    private function disegnaListaProcedimentiAnapraItem($Anapra_rec) {
        $datiProcedimento = $this->getDatiProcedimento($Anapra_rec);

        $buttonsProcedimento = array();

        if ($datiProcedimento['BUTTON_INFORMATIVA']) {
            $buttonsProcedimento[] = $datiProcedimento['BUTTON_INFORMATIVA'];
        }

        if ($datiProcedimento['BUTTON_ALLEGATO']) {
            $buttonsProcedimento[] = $datiProcedimento['BUTTON_ALLEGATO'];
        }

        if ($datiProcedimento['BUTTON_COMPILA']) {
            $buttonsProcedimento[] = $datiProcedimento['BUTTON_COMPILA'];
        }

        return array(
            array(
                'href' => $datiProcedimento['COLLEGAMENTO'],
                'buttons' => $buttonsProcedimento,
                'label' => $datiProcedimento['OGGETTO']
            )
        );
    }

    private function getDatiProcedimento($Anapra_rec) {
        $OggettoEvento = $this->praLibEventi->getOggetto($this->PRAM_DB, $Anapra_rec, $Anapra_rec);

        $T_docu = '';

        if ($Anapra_rec['PRASLAVE'] == 1) {
            $PRAM_SOURCE = $this->PRAM_DB_R;
        } else {
            $PRAM_SOURCE = $this->PRAM_DB;
        }

        $Anpdoc_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, "SELECT ANPCLA, ANPFIL FROM ANPDOC WHERE ANPKEY = '{$Anapra_rec['PRANUM']}'", true);

        if ($Anpdoc_tab) {
            foreach ($Anpdoc_tab as $Anpdoc_rec) {
                if ($Anpdoc_rec['ANPCLA'] == 'DOC' || $Anpdoc_rec['ANPCLA'] == 'DOC_' . $Anapra_rec['TSPPRO']) {
                    $T_docu = $Anpdoc_rec['ANPFIL'];
                }
            }
        }

        $Img = frontOfficeLib::getFileIcon($T_docu);

        $rnd = rand();
        $itemHref = '#';

        if ($this->config['online'] == 1) {
            $paramsHrefItem = array(
                'p' => $this->config['info_page'],
                'procedi' => $Anapra_rec['PRANUM'],
                'subproc' => $Anapra_rec['IEVCOD'],
                'subprocid' => $Anapra_rec['ROWID_ITEEVT'],
                'rnd' => $rnd
            );

            if ($this->request['accorpa']) {
                $paramsHrefItem['accorpa'] = $this->request['accorpa'];
            }

            $itemHref = ItaUrlUtil::GetPageUrl($paramsHrefItem);
        }

        $buttonInformativa = $buttonAllegato = $buttonCompila = false;

        if ($this->config['informativa']) {
            $paramsHrefInformativa = array(
                'p' => $this->config['informativa'],
                'procedi' => $Anapra_rec['PRANUM'],
                'subproc' => $Anapra_rec['IEVCOD'],
                'subprocid' => $Anapra_rec['ROWID_ITEEVT'],
                'rnd' => $rnd
            );

            if ($this->request['accorpa']) {
                $paramsHrefInformativa['accorpa'] = $this->request['accorpa'];
            }

            $href = ItaUrlUtil::GetPageUrl($paramsHrefInformativa);

            $buttonInformativa = array(
                'href' => $href,
                'title' => 'Consulta informazioni',
                'label' => 'Info',
                'image' => $Img
            );
        }

        if ($T_docu != '') {
            $href = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'vediAllegato',
                    'procedi' => $Anapra_rec['PRANUM'],
                    'allegato' => itaCrypt::encrypt($T_docu),
                    'sportello' => $Anapra_rec['IEVTSP']
            ));

            $buttonAllegato = array(
                'href' => $href,
                'title' => 'Vedi allegato',
                'label' => 'Allegato',
                'image' => $Img,
                'target' => '_blank'
            );
        }

        $itepas_rec = ItaDB::DBSQLSelect($PRAM_SOURCE, "SELECT 1 FROM ITEPAS WHERE ITECOD = '" . $Anapra_rec['PRANUM'] . "'", false);

        if ($this->config['online'] == 1 && $this->config['online_page'] && $itepas_rec) {
            $paramsHrefCompila = array(
                    'p' => $this->config['online_page'],
                    'event' => 'openBlock',
                    'procedi' => $Anapra_rec['PRANUM'],
                    'subproc' => $Anapra_rec['IEVCOD'],
                    'subprocid' => $Anapra_rec['ROWID_ITEEVT'],
                    'settore' => $Anapra_rec['IEVSTT'],
                    'attivita' => $Anapra_rec['IEVATT']
            );

            if ($this->request['accorpa']) {
                $paramsHrefCompila['accorpa'] = $this->request['accorpa'];
            }

            $href = ItaUrlUtil::GetPageUrl($paramsHrefCompila);

            $buttonCompila = array(
                'href' => $href,
                'title' => 'Compilazione procedimento online',
                'label' => 'Compila',
                'image' => frontOfficeLib::getIcon('notepad')
            );
        }

        return array(
            'OGGETTO' => $OggettoEvento,
            'SPORTELLO' => $Anapra_rec['TSPDES'],
            'SETTORE' => $Anapra_rec['SETDES'],
            'ATTIVITA' => $Anapra_rec['ATTDES'],
            'TIPOLOGIA' => $Anapra_rec['TIPDES'],
            'COLLEGAMENTO' => $itemHref,
            'BUTTON_INFORMATIVA' => $buttonInformativa,
            'BUTTON_ALLEGATO' => $buttonAllegato,
            'BUTTON_COMPILA' => $buttonCompila
        );
    }

    private function createLinkFromButton($button) {
        $html = new html;
        $htmlLink = '<a href="' . $button['href'] . '" title="' . $button['title'] . '" style="font-size: .8em; display: block; text-align: center;">';
        $htmlLink .= $html->getImage($button['image'], '24px') . '<br />' . $button['label'];
        $htmlLink .= '</a>';
        return $htmlLink;
    }

    private function disegnaListaProcedimentiPerTipologia($accordionOpen, $whereSportelli) {
        $count = 0;
        $treeViewData = array();

        $accordion0_open = $accordionOpen > 0;

        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSQL($whereSportelli, '', $this->configTclass[0]['order']), true);

        foreach ($iteevt_tab as $iteevt_rec) {
            if (!isset($treeViewData[$iteevt_rec['TIPCOD']])) {
                $treeViewData[$iteevt_rec['TIPCOD']] = array('childs' => array(), 'label' => $iteevt_rec['TIPDES'], 'active' => $accordion0_open);
            }

            $AnapraRecItem = $this->disegnaListaProcedimentiAnapraItem($iteevt_rec);

            $count++;
            $treeViewData[$iteevt_rec['TIPCOD']]['childs'] = array_merge($treeViewData[$iteevt_rec['TIPCOD']]['childs'], $AnapraRecItem);

            $accordion0_open = false;
        }

        output::addTreeView($treeViewData, html::TREEVIEW_ACCORDION);

        return $count;
    }

    private function disegnaListaProcedimentiPerSettoreAttivita($accordionOpen, $whereSportelli) {
        $count = 0;
        $treeViewData = array();

        $accordion0_open = $accordionOpen > 0;
        $accordion1_open = $accordionOpen > 1;

        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSQL($whereSportelli, '', $this->configTclass[1]['order']), true);

        foreach ($iteevt_tab as $iteevt_rec) {
            if (!isset($treeViewData[$iteevt_rec['SETCOD']])) {
                $treeViewData[$iteevt_rec['SETCOD']] = array('childs' => array(), 'label' => $iteevt_rec['SETDES'], 'active' => $accordion0_open);
            }

            if (!isset($treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']])) {
                $treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']] = array('childs' => array(), 'label' => $iteevt_rec['ATTDES'], 'active' => $accordion1_open);
            }

            $AnapraRecItem = $this->disegnaListaProcedimentiAnapraItem($iteevt_rec);

            $count++;
            $treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'] = array_merge($treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'], $AnapraRecItem);

            $accordion0_open = false;
            $accordion1_open = false;
        }

        output::addTreeView($treeViewData, html::TREEVIEW_ACCORDION);

        return $count;
    }

    private function disegnaListaProcedimentiPerTipologiaAttivita($accordionOpen, $whereSportelli) {
        $count = 0;
        $treeViewData = array();

        $accordion0_open = $accordionOpen > 0;
        $accordion1_open = $accordionOpen > 1;

        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSQL($whereSportelli, '', $this->configTclass[2]['order']), true);

        foreach ($iteevt_tab as $iteevt_rec) {
            if (!isset($treeViewData[$iteevt_rec['TIPCOD']])) {
                $treeViewData[$iteevt_rec['TIPCOD']] = array('childs' => array(), 'label' => $iteevt_rec['TIPDES'], 'active' => $accordion0_open);
            }

            if (!isset($treeViewData[$iteevt_rec['TIPCOD']]['childs'][$iteevt_rec['ATTCOD']])) {
                $treeViewData[$iteevt_rec['TIPCOD']]['childs'][$iteevt_rec['ATTCOD']] = array('childs' => array(), 'label' => $iteevt_rec['ATTDES'], 'active' => $accordion1_open);
            }

            $AnapraRecItem = $this->disegnaListaProcedimentiAnapraItem($iteevt_rec);

            $count++;
            $treeViewData[$iteevt_rec['TIPCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'] = array_merge($treeViewData[$iteevt_rec['TIPCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'], $AnapraRecItem);

            $accordion0_open = false;
            $accordion1_open = false;
        }

        output::addTreeView($treeViewData, html::TREEVIEW_ACCORDION);

        return $count;
    }

    private function disegnaListaProcedimentiPerSettoreAttivitaTipologia($accordionOpen, $whereSportelli) {
        $count = 0;
        $treeViewData = array();

        $accordion0_open = $accordionOpen > 0;
        $accordion1_open = $accordionOpen > 1;
        $accordion2_open = $accordionOpen > 2;

        $iteevt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->getSQL($whereSportelli, '', $this->configTclass[5]['order']), true);

        foreach ($iteevt_tab as $iteevt_rec) {
            if (!isset($treeViewData[$iteevt_rec['SETCOD']])) {
                $treeViewData[$iteevt_rec['SETCOD']] = array('childs' => array(), 'label' => $iteevt_rec['SETDES'], 'active' => $accordion0_open);
            }

            if (!isset($treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']])) {
                $treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']] = array('childs' => array(), 'label' => $iteevt_rec['ATTDES'], 'active' => $accordion1_open);
            }

            if (!isset($treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'][$iteevt_rec['TIPCOD']])) {
                $treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'][$iteevt_rec['TIPCOD']] = array('childs' => array(), 'label' => $iteevt_rec['TIPDES'], 'active' => $accordion2_open);
            }

            $AnapraRecItem = $this->disegnaListaProcedimentiAnapraItem($iteevt_rec);

            $count++;
            $treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'][$iteevt_rec['TIPCOD']]['childs'] = array_merge($treeViewData[$iteevt_rec['SETCOD']]['childs'][$iteevt_rec['ATTCOD']]['childs'][$iteevt_rec['TIPCOD']]['childs'], $AnapraRecItem);

            $accordion0_open = false;
            $accordion1_open = false;
            $accordion2_open = false;
        }

        output::addTreeView($treeViewData, html::TREEVIEW_ACCORDION);

        return $count;
    }

    private function getSQL($whereSportelli, $where = '', $order = '') {
        $sql = "SELECT
                    ANAPRA.*,
                    ITEEVT.IEVCOD,
                    ITEEVT.IEVDESCR,
                    ITEEVT.IEVDVA,
                    ITEEVT.IEVAVA,
                    ITEEVT.IEVTSP,
                    ITEEVT.IEVSTT,
                    ITEEVT.IEVATT,
                    ITEEVT.IEVTIP,
                    ITEEVT.ROWID AS ROWID_ITEEVT,
                    ANASET.SETSEQ,
                    ANASET.SETDES,
                    ANASET.SETCOD,
                    ANAATT.ATTSEQ,
                    ANAATT.ATTDES,
                    ANAATT.ATTCOD,
                    ANATIP.TIPCOD,
                    ANATIP.TIPDES,
                    ANAEVENTI.EVTDESCR,
                    ANATSP.TSPDES,
                    ANATSP.TSPPRO
                FROM
                    ITEEVT
                LEFT OUTER JOIN ANAPRA ON ANAPRA.PRANUM = ITEEVT.ITEPRA
                LEFT OUTER JOIN ANASET ON ANASET.SETCOD = ITEEVT.IEVSTT
                LEFT OUTER JOIN ANAATT ON ANAATT.ATTCOD = ITEEVT.IEVATT
                LEFT OUTER JOIN ANATIP ON ANATIP.TIPCOD = ITEEVT.IEVTIP
                LEFT OUTER JOIN ANAEVENTI ON ANAEVENTI.EVTCOD = ITEEVT.IEVCOD
                LEFT OUTER JOIN ANATSP ON ANATSP.TSPCOD = ITEEVT.IEVTSP
                WHERE
					ANAPRA.PRATPR = 'ONLINE' AND
                    ( ITEEVT.IEVDVA IS NULL OR ITEEVT.IEVDVA = '' OR ITEEVT.IEVDVA <= " . date('Ymd') . " ) AND
                    ( ITEEVT.IEVAVA IS NULL OR ITEEVT.IEVAVA = '' OR ITEEVT.IEVAVA >= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRADVA IS NULL OR ANAPRA.PRADVA = '' OR ANAPRA.PRADVA <= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRAAVA IS NULL OR ANAPRA.PRAAVA = '' OR ANAPRA.PRAAVA >= " . date('Ymd') . " ) AND
                    ITEEVT.IEVSTT != 0 AND ITEEVT.IEVTIP != '' AND
                    ANAPRA.PRAOFFLINE = 0
                    $whereSportelli $where $order";

        return $sql;
    }

}
