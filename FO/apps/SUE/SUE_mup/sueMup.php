<?php

class sueMup extends itaModelFO {

    public $praLib;
    public $praErr;
    public $praLibEventi;
    public $praLibDati;
    public $praLibDatiAggiuntivi;
    public $frontOfficeLib;
    public $PRAM_DB;
    public $GAFIERE_DB;
    public $ITAFRONTOFFICE_DB;
    public $workDate;
    public $workYear;
    public $errUpload;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new sueErr();
            $this->praLib = new praLib($this->praErr);
            $this->praLibEventi = new praLibEventi();
            $this->praLibDati = praLibDati::getInstance($this->praLib);
            $this->praLibDatiAggiuntivi = new praLibDatiAggiuntivi();
            $this->frontOfficeLib = new frontOfficeLib();

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->GAFIERE_DB = ItaDB::DBOpen('GAFIERE', frontOfficeApp::getEnte());
            $this->ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());

            $this->workDate = date('Ymd');
            $this->workYear = date('Y');
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        output::$html_out = '';

        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'admin' && strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'pitaprotec') {
            if (!$this->ControlloValiditaUtente()) {
                return output::$html_out;
            }
        }

        switch ($this->request['event']) {
            case 'openBlock':
                if (!$this->request['procedi']) {
                    $procedi = $this->config['procedi'];
                } else {
                    $procedi = $this->request['procedi'];
                }

                if (!$procedi) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E9996', " Apertura Richiesta non possibile:codice procedimento mancante", __CLASS__);
                    return output::$html_out;
                }

                /*
                 * Modifica per Evento
                 */
                if ($this->request['subproc']) {
                    $procedi = array(
                        'PROCEDI' => $procedi,
                        'SUBPROC' => $this->request['subproc'],
                        'SUBPROCID' => $this->request['subprocid'],
                        'SETTORE' => $this->request['settore'],
                        'ATTIVITA' => $this->request['attivita'],
                    );
                }
                /*
                 * Fine modifica
                 */

                /*
                 * Modifica per apertura proc Evento
                 */
                if ($this->request['propak']) {
                    $propak = $this->request['propak'];
                }

                /*
                 * @Todo Verificare come passare il parametro 'padre' alla funzione
                 * 'RegProcedimento'.
                 */
                $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
                $_POST['padre'] = $this->request['padre'];
                $_POST['accorpa'] = $this->request['accorpa'];
                $ricnum = $this->praLib->RegProcedimento($procedi, $datiUtente['fiscale'], date('Ymd'), $datiUtente['cognome'], $datiUtente['nome'], $datiUtente['via'], $datiUtente['comune'], $datiUtente['cap'], $datiUtente['provincia'], $sequenza, $this->PRAM_DB, $this->workYear, $datiUtente['email'], $datiUtente['nazione'], $datiUtente['datanascita'], $datiUtente['denominazione'], "sue", $propak);
                if ($ricnum == 0) {
                    return output::$html_out;
                } else {
                    if (!$dati = $this->praLibDati->prendiDati($ricnum)) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }
                    if ($this->SincronizzaFileInserimentoAutomatico($dati)) {
                        if (!$dati = $this->praLibDati->prendiDati($ricnum)) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                    }
                }

                /*
                 * LANCIO CUSTOM CLASS PARAMETRICO
                 */

                if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_ISTANZIA_RICHIESTA, $dati)) {
                    return output::$html_out;
                }

                $this->runCallback($dati, "regProcedimento");

                $eqDesc = sprintf('Aperta richiesta %s procedimento %s evento %s settore %s attività %s', $ricnum, $procedi['PROCEDI'], $procedi['SUBPROC'], $procedi['SETTORE'], $procedi['ATTIVITA']);
                eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));

                /*
                 * Apro la gestione della richiesta
                 */
                $html = new html();
                $Url = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'direzione' => 'home', 'ricnum' => $ricnum));
                $numeroRichiesta = intval(substr($ricnum, 4)) . '/' . substr($ricnum, 0, 4);
                $html->addAlert("Richiesta n. $numeroRichiesta inserita.<br /><small>Apertura in corso...", '', 'info');
                $html->addJSRedirect($Url, 0, false);
                output::$html_out .= $html->getHtml();
                return output::$html_out;

            case 'navClick':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                if ($this->request['direzione']) {
                    $nuovoPasso = $this->vaiPasso($this->request['direzione'], $dati);

                    $eqDesc = sprintf('Aperta dettaglio richiesta %s passo %s', $this->request['ricnum'], $nuovoPasso);
                    eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));

                    if ($nuovoPasso) {
                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $nuovoPasso)) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                    } else {
                        return output::$html_out;
                    }
                } else {
                    $eqDesc = sprintf('Aperta dettaglio richiesta %s passo %s', $this->request['ricnum'], $this->request['seq']);
                    eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));
                }
                break;

            case 'seqClick':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                $Ricite_rec = $dati["Ricite_rec"];

//                if ($dati['Consulta'] == true && $dati['Ricite_rec']['ITEDOW'] == 0) {
//                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
//                    break;
//                }
                if ($dati['Consulta'] == true && !$dati['permessiPasso']['Insert']) {
                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
                    break;
                }

                /*
                 * PASSO UPLOAD o UPLOAD MULTIPLO
                 */

                $risultatoUpload = true;

                if ($Ricite_rec['ITEUPL'] != 0 || $Ricite_rec['ITEMLT'] != 0) {
                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_PRE_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                            return output::$html_out;

                        case 2:
                            break 2;
                    }

                    if ($this->request['allegatoCartella']) {
                        $risultatoUpload = $this->caricaAllegatoDaCartella($dati, $this->request['allegatoCartella']);
                    } else {
                        $caricaAllegatoFilename = $_FILES['ita_upload']['name'];
                        $caricaAllegatoFilepath = $_FILES['ita_upload']['tmp_name'];
                        $caricaAllegatoFileerror = $_FILES['ita_upload']['error'];
                        $risultatoUpload = $this->caricaAllegato($dati, $caricaAllegatoFilename, $caricaAllegatoFilepath, $caricaAllegatoFileerror);
                    }

                    if (!$risultatoUpload && $this->praErr->getMessaggio()) {
                        return output::$html_out;
                    }

                    if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }

                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_POST_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                            return output::$html_out;

                        case 2:
                            break 2;
                    }
                } elseif ($Ricite_rec['ITEQST'] != 0) {
                    /*
                     * DOMANDA
                     */
                    if ($this->request['risposta']) {
                        $dati['Ricite_rec']['RCIRIS'] = $this->request['risposta'];
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'RICITE', 'ROWID', $dati['Ricite_rec']);
                        if ($nRows == -1) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0075', $e->getMessage() . " Errore aggiornamento Risposta della pratica N. " . $dati['Proric_rec']['RICNUM'] . "Passo N." . $dati['seq'], __CLASS__);
                            return output::$html_out;
                        }

                        /*
                         * Se esiste un campo aggiuntivo per la domanda ci salvo dentro la risposta
                         *  ATTENZIONE: Prendo sempre e solo il primo campo aggiuntivo
                         */
                        $sql_dag_domanda = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                        $Ricdag_domanda_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_dag_domanda, true);
                        foreach ($Ricdag_domanda_tab as $key => $Ricdag_domanda_rec) {
                            if ($Ricdag_domanda_rec) {
                                $Ricdag_domanda_rec['RICDAT'] = $dati['Ricite_rec']['RCIRIS'];
                                $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_domanda_rec);
                            }
                        }

                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }

                        /*
                         * Verifico se c'è il passo scelta richiesta da integrare e se è spento lo annullo
                         */
                        if (!$this->praLib->AnnullaSceltaIntegrazione($dati)) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0035', "Impossibile aggiornare dato aggiuntivo richiesta di integrazione della pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                            return output::$html_out;
                        }

                        /*
                         * Rifaccio un prendi dati per aggiornare PRORIC e RICDAG
                         */
                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                    }
                } elseif ($Ricite_rec['ITESTR'] != 0) {
                    /*
                     * compliazione on line DA SVILUPPARE
                     */
                } elseif ($dati['Ricite_rec']['ITEDRR'] != 0) {
                    /*
                     * Creazione Rapporto completo
                     */

                    $fileRapporto = $this->praLib->creaRapportoCompleto($dati, $this->config['ditta'], $this->PRAM_DB);
                    if (!$fileRapporto) {
                        if ($this->praLib->getErrCode() == -1) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0084', 'Errore creazione rapporto completo richiesta n.' . $dati['Proric_rec']['RICNUM'] . ': ' . $this->praLib->getErrMessage(), __CLASS__);
                            return output::$html_out;
                        } else if ($this->praLib->getErrCode() == -2) {
                            output::addAlert($this->praLib->getErrMessage(), 'Attenzione', 'error');
                            break;
                        }
                    }

                    if ($this->praLib->getErrCode() == -3) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0087', $this->praLib->getErrMessage(), __CLASS__, "", false);
                    }

                    $datiAllegato = $this->getPercorsoAllegato($fileRapporto, $dati);
                    $downloadURI = $this->frontOfficeLib->getDownloadURI($datiAllegato['filepath'], $datiAllegato['filename']);
                    $html = new html();
                    $html->addJSRedirect($downloadURI);

                    output::$html_out .= $html->getHtml();
                } elseif ($dati['Ricite_rec']['ITERICUNI'] != 0) {
                    /*
                     * 
                     */
                } elseif ($dati['Ricite_rec']['ITEDIS'] != 0) {
                    $resultFile = $this->praLib->CreaPdfDistinta($dati, $dati['Ricite_rec']);
                    if ($resultFile != false) {
                        $html = new html();

                        $downloadURI = $this->frontOfficeLib->getDownloadURI($resultFile, '', false);
                        $html->addJSWindow($downloadURI);

                        output::$html_out .= $html->getHtml();
                    }
                } elseif ($dati['Ricite_rec']['ITEDOW'] != 0) {
                    $html = new html();

                    /*
                     * E' UN DOWNLOAD...............................
                     */
                    if ($dati['Ricite_rec']['ITEWRD'] != "") {
                        $Nome_file_orig = $dati['Ricite_rec']['ITEWRD'];
                        if (pathinfo($dati['Ricite_rec']['ITEWRD'], PATHINFO_EXTENSION) == 'pdf') {
                            /*
                             * Fill Form
                             */
                            $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND
                                    ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "'", true);
                            $Ricdag_tab = $this->praLib->CheckDatiAggiuntiviDefault($Ricdag_tab, $this->PRAM_DB);
                            $Nome_file = $this->FillFormPdf($Ricdag_tab, $dati['Ricite_rec']['ITEWRD'], $dati);
                            if (!$Nome_file) {
                                return output::$html_out = $this->praErr->parseError(__FILE__, 'E0060', "Pratica:" . $dati['Proric_rec']['RICNUM'] . ". Compilazione PDF Fallita.", __CLASS__);
                            }
                        } else if (pathinfo($dati['Ricite_rec']['ITEWRD'], PATHINFO_EXTENSION) == 'docx') {
                            require_once ITA_LIB_PATH . '/itaPHPCore/itaSmarty.class.php';
                            require_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';

                            $DocumentDOCX = itaDocumentFactory::getDocument('DOCX');
                            $DictionaryData = $dati['Navigatore']['Dizionario_Richiesta_new']->getAllData();

                            $DocumentDOCX->setDictionary($DictionaryData);

                            if (!$DocumentDOCX->loadContent($dati['CartellaRepository'] . "/testiAssociati/" . $dati['Ricite_rec']['ITEWRD'])) {
                                return output::$html_out = $this->praErr->parseError(__FILE__, 'E0061', "Pratica:" . $dati['Proric_rec']['RICNUM'] . ". Compilazione DOCX Fallita: " . $DocumentDOCX->getMessage(), __CLASS__);
                            }

                            if (!$DocumentDOCX->mergeDictionary()) {
                                return output::$html_out = $this->praErr->parseError(__FILE__, 'E0062', "Pratica:" . $dati['Proric_rec']['RICNUM'] . ". Compilazione DOCX Fallita: " . $DocumentDOCX->getMessage(), __CLASS__);
                            }

                            $Output = $dati['CartellaRepository'] . "/testiAssociati/filled_" . $dati['Ricite_rec']['ITEWRD'];

                            $DocumentDOCX->saveContent($Output, true);

                            $Nome_file = $Output;
                        } else {
                            $Nome_file = $Nome_file_orig;
                        }

                        $datiAllegato = $this->getPercorsoAllegato($Nome_file, $dati, $Nome_file_orig);
                        $downloadURI = $this->frontOfficeLib->getDownloadURI($datiAllegato['filepath'], $datiAllegato['filename']);
                        $html->addJSRedirect($downloadURI);
                    } else if ($dati['Ricite_rec']['ITEURL'] != "" && strpos($dati['Ricite_rec']['ITEURL'], "http://") !== false) {
                        $Url = $this->praLib->valorizzaTemplate($dati['Ricite_rec']['ITEURL'], $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
                        $html->addJSRedirect($Url);
                    } elseif ($dati['Ricite_rec']['ITEDWP'] != "") {
                        $ricite_rec_prec = $this->praLib->GetRicite($dati['Ricite_rec']['ITEDWP'], "itekey", $this->PRAM_DB, false, $dati['Proric_rec']['RICNUM']);
                        $ricdoc_rec_prec = $this->praLib->GetRicdoc($dati['Ricite_rec']['ITEDWP'], "itekey", $this->PRAM_DB, false, $dati['Proric_rec']['RICNUM']);
                        if ($ricdoc_rec_prec && file_exists($dati['CartellaAllegati'] . "/" . $ricdoc_rec_prec['DOCUPL'])) {
                            $downloadURI = $this->frontOfficeLib->getDownloadURI($dati['CartellaAllegati'] . "/" . $ricdoc_rec_prec['DOCUPL'], $ricdoc_rec_prec['DOCNAME']);
                            $html->addJSRedirect($downloadURI);
                        } else {
                            $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'seq' => $ricite_rec_prec['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                            $errMessage = sprintf('Il file da scaricare non è stato trovato.<br />Assicurarsi di caricare l\'allegato al passo "%s" prima di effettuare lo scarico.<br /><a href="%s">Vai al passo</a>', $ricite_rec_prec['ITEDES'], $hrefVaiPasso);
                            output::addAlert($errMessage, 'Errore', 'error');
                            break;
                        }
                    }

                    output::$html_out .= $html->getHtml();
                }

                /*
                 * Aggiorno la sequenza
                 */
                if ($dati['seq'] == "") {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0069', "Impossibile aggiornare la sequenza della richiesta " . $dati['Proric_rec']['RICNUM'] . ".<br>Sequenza non trovata<br>" . print_r($this->request, true), __CLASS__);
                    return output::$html_out;
                }

                $sequenza = $dati['Proric_rec']['RICSEQ'];
                if ($risultatoUpload) {
                    if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
                        if ($dati['Ricite_rec']['RICERF'] == 0) {
                            $dati['Proric_rec']['RICSEQ'] = $dati['Proric_rec']['RICSEQ'] . "." . $dati['seq'] . ".";
                            $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                            if ($nRows == -1) {
                                //Errore ???
                            }
                        }
                    } else {
                        if ($dati['Ricite_rec']['RICERF'] == 1) {
                            $dati['Proric_rec']['RICSEQ'] = str_replace("." . $dati['seq'] . ".", "", $dati['Proric_rec']['RICSEQ']);
                            $dati['Proric_rec']['RICSEQ'] = preg_replace('/\s+/', '', $dati['Proric_rec']['RICSEQ']);
                            $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                        }
                    }
                }

                if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Scollego la pratica se Passo Accorpamento è nascosto ed
                 * è successivo all'attuale
                 */
                if ($dati['Proric_rec']['RICRUN'] != '') {
                    foreach ($dati['Ricdag_tab_totali'] as $ricdag_rec) {
                        if ($ricdag_rec['DAGKEY'] === 'RICHIESTA_UNICA') {
                            /*
                             * Ignoro il dato di richiesta
                             */
                            if ($ricdag_rec['ITECOD'] == $ricdag_rec['ITEKEY']) {
                                continue;
                            }

                            $passo_accorpamento_attivo = true;
                            foreach ($dati['Ricite_tab'] as $ricite_rec) {
                                if ($ricite_rec['ITEKEY'] === $ricdag_rec['ITEKEY'] && $ricite_rec['ITESEQ'] > $dati['seq']) {
                                    /*
                                     * Se è successivo all'attuale, verifico
                                     * se è nascosto
                                     */
                                    $passo_accorpamento_attivo = false;
                                    break;
                                }
                            }

                            if (!$passo_accorpamento_attivo) {
                                foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
                                    if ($ricite_rec['ITEKEY'] === $ricdag_rec['ITEKEY']) {
                                        $passo_accorpamento_attivo = true;
                                        break;
                                    }
                                }
                            }

                            if (!$passo_accorpamento_attivo) {
                                $this->praLib->scollegaDaPraticaUnica($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);

                                if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq'])) {
                                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                                }
                            }

                            break;
                        }
                    }
                }

                /*
                 * Scollego le pratiche accorpate se il passo di accorpamento
                 * pratiche è nascosto.
                 */
                $scollega_pratiche_accorpate = false;
                foreach ($dati['Ricite_tab'] as $ricite_rec) {
                    if ($ricite_rec['ITERICUNI'] == '1') {
                        $scollega_pratiche_accorpate = true;
                        break;
                    }
                }
                if ($scollega_pratiche_accorpate) {
                    /*
                     * Passo accorpamento presente, verifico che sia attivo.
                     */
                    foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
                        if ($ricite_rec['ITERICUNI'] == '1') {
                            $scollega_pratiche_accorpate = false;
                            break;
                        }
                    }
                    if ($scollega_pratiche_accorpate) {
                        $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $dati['Proric_rec']['RICNUM']);
                        if ($proric_tab_accorpate) {
                            foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                                if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $proric_rec_accorpate['RICNUM'])) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0079', 'Errore Scollegamento Pratica n.' . $proric_rec_accorpate['RICNUM'] . ' da Pratica n.' . $dati['Proric_rec']['RICNUM'], __CLASS__);
                                    break 2;
                                }
                            }
                        }
                    }
                }

                /*
                 * Vado automaticamente al passo successivo tranne se è multiupload o se non ci sono errori di upload
                 */
                $Ricite_rec_successivo = array();
                if ($dati["Ricite_rec"]['ITEMLT'] != 1 && $dati['Ricite_rec']['RICERF'] != 1 && $risultatoUpload) {
                    if ($dati["Ricite_rec"]['ITEQST'] == 1) {
                        switch ($this->request['risposta']) {
                            case "SI":
                                $itekey_target = $dati["Ricite_rec"]['ITEVPA'];
                                break;
                            case "NO":
                                $itekey_target = $dati["Ricite_rec"]['ITEVPN'];
                                break;
                        }
                        if ($itekey_target != 0) {
                            foreach ($dati['Navigatore']['Ricite_tab_new'] as $Ricite_rec) {
                                if ($Ricite_rec['ITEKEY'] === $itekey_target) {
                                    $Ricite_rec_successivo = $Ricite_rec;
                                }
                            }
                            if (!$Ricite_rec_successivo) {
                                foreach ($dati['Navigatore']['Ricite_tab_new'] as $key1 => $Ricite_rec) {
                                    if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                                        $Ricite_rec_successivo = $dati['Navigatore']['Ricite_tab_new'][$key1 + 1];
                                        break;
                                    }
                                }
                            }
                        } else {
                            foreach ($dati['Navigatore']['Ricite_tab_new'] as $key1 => $Ricite_rec) {
                                if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                                    $Ricite_rec_successivo = $dati['Navigatore']['Ricite_tab_new'][$key1 + 1];
                                    break;
                                }
                            }
                        }
                    } else {
                        foreach ($dati['Navigatore']['Ricite_tab_new'] as $key1 => $Ricite_rec) {
                            if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                                $Ricite_rec_successivo = $dati['Navigatore']['Ricite_tab_new'][$key1 + 1];
                                break;
                            }
                        }
                    }
                    if ($Ricite_rec_successivo) {
                        if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $Ricite_rec_successivo['ITESEQ'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                    }
                    $this->SincronizzaFileInserimentoAutomatico($dati);
                }



                break;

            case 'invioInfocamere':
                $html = new html();
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $datiInfocamere = $dati['dati_infocamere'];

                /*
                 * Verifico la presenza di checks con errore
                 */
                if ($datiInfocamere['checks']['Errors']) {
                    $alertString = "Attenzione:\\n";
                    $alertString .= implode("\\n", $datiInfocamere['checks']['datiImpresa']) . "\\n";
                    $alertString .= implode("\\n", $datiInfocamere['checks']['datiSportello']) . "\\n";
                    $alertString .= implode("\\n", $datiInfocamere['checks']['datiAdempimento']) . "\\n";
                    foreach ($datiInfocamere['checks']['files'] as $key => $value) {
                        $alertString .= $value['anomalia'] . "\\n";
                    }
                    $html->appendHtml("<script type=\"text/javascript\">alert(\"" . "$alertString\\n\\nImpossibile inviare il file" . "\");</script>");
                    output::$html_out .= $html->getHtml();
                    break;
                }

                /*
                 * Creao la cartella zip temporanea
                 */
                $cartellaZip = $this->getCartellaZIP($datiInfocamere['datiImpresa']['codfis_suap'], $dati['CartellaTemporary']);

                /*
                 * @TODO VERIFICA PULIZIA CARTELLA TEMP
                 */
                if (!$cartellaZip) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0070', "Errore creazione cartella temporanea in" . $dati['CartellaTemporary'], __CLASS__);
                }

                /*
                 * Creo la cartella interna come da struttura info camere zip temporanea
                 */
                $cartellaZipAllegati = $cartellaZip . "/" . pathinfo($cartellaZip, PATHINFO_BASENAME);
                if (!@mkdir($cartellaZipAllegati, 0777, true)) {
                    $this->RemoveZipDir($cartellaZip);
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0070', "Errore creazione cartella $cartellaZipAllegati", __CLASS__);
                }

                /*
                 * copio allegati estratti per INFOCAMERE nella cartella temporanea
                 */
                if (!@copy($dati['dati_infocamere']['files']['path_file_firmato'], $cartellaZipAllegati . "/" . pathinfo($cartellaZip, PATHINFO_BASENAME) . ".MDA.PDF.P7M")) {
                    $this->RemoveZipDir($cartellaZip);
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0071', "Errore copia allegato " . $alle['FILEPATH'], __CLASS__);
                }
                $dati['dati_infocamere']['files']['nome_file_firmato'] = pathinfo($cartellaZip, PATHINFO_BASENAME) . ".MDA.PDF.P7M";

                if (!@copy($dati['dati_infocamere']['files']['path_file_non_firmato'], $cartellaZipAllegati . "/" . pathinfo($cartellaZip, PATHINFO_BASENAME) . ".MDA.PDF")) {
                    $this->RemoveZipDir($cartellaZip);
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0071', "Errore copia allegato " . $alle['FILEPATH'], __CLASS__);
                }

                $dati['dati_infocamere']['files']['nome_file_non_firmato'] = pathinfo($cartellaZip, PATHINFO_BASENAME) . ".MDA.PDF";
                foreach ($dati['dati_infocamere']['files']['allegati'] as $key1 => $alle) {
                    if (!@copy($alle['FILEPATH'], $cartellaZipAllegati . "/" . $alle['FILENAME'])) {
                        $this->RemoveZipDir($cartellaZip);
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0071', "Errore copia allegato " . $alle['FILEPATH'], __CLASS__);
                    }
                }

                /*
                 * Creo il descrittore
                 */
                $descrittore = $cartellaZip . "/descrittore.xml";
                $xmlDescrittore = $this->CreaXmlDescrittore($dati);
                $File = fopen($descrittore, "w+");
                fwrite($File, $xmlDescrittore);
                fclose($File);

                /*
                 * Zippo il file
                 */
                $arcpf = $dati['CartellaAllegati'] . "/" . pathinfo($cartellaZip, PATHINFO_BASENAME) . ".zip";
                if (itaZip::zipRecursive($dati['CartellaAllegati'], $cartellaZip, $arcpf, 'zip', false, false) !== 0) {
                    $this->RemoveZipDir($cartellaZip);
                    output::addAlert('Impossibile creare il file ZIP per infocamere. Procedura interrotta.', 'Errore', 'error');
                    break;
                }
                $this->RemoveZipDir($cartellaZip);

                /*
                 * Mi trovo i dati per invio mail e file di output (object , body)
                 */
                $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);

                /*
                 * Mi salbo il body.txt che mi servira come riepilogo sul BO sul controlla richieste
                 */
                $txtBody = $dati['CartellaAllegati'] . "/body.txt";
                $File = fopen($txtBody, "w+");
                if (!file_exists($txtBody)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0049', "File " . $dati['CartellaAllegati'] . "/body.txt non trovato", __CLASS__, "", false);
                    return output::$html_out;
                } else {
                    if ($dati['Proric_rec']['RICRPA']) {
                        fwrite($File, $arrayDatiMail['bodyIntResp']);
                    } elseif ($dati['Proric_rec']['PROPAK']) {
                        fwrite($File, $arrayDatiMail['bodyRespParere']);
                    } else {
                        fwrite($File, $arrayDatiMail['bodyResponsabile']);
                    }
                    fclose($File);
                }

                /*
                 * Chiamata a StarWeb
                 */
                if (ITA_JVM_PATH != "" && file_exists(ITA_JVM_PATH) && file_exists(ITA_LIB_PATH . "/java/itaStarWebWS.properties")) {
                    $exec = exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaStarWebWS.jar " . $arcpf . " " . pathinfo($cartellaZip, PATHINFO_BASENAME), $ret);
                    $arrayExec = explode("|", $exec);

                    $arrayExec[1] = 0;

                    if ($arrayExec[1] == 0) {
                        $arrayParamBloccoMail = $this->praLib->GetParametriBloccoMail($this->PRAM_DB);

                        /*
                         * Scrivo i file XMLINFO delle richieste accorpate
                         * e li copio nella cartella della richiesta padre
                         */
                        $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $dati['Proric_rec']['RICNUM']);
                        foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                            $dati_accorpata = $this->praLibDati->prendiDati($proric_rec_accorpate['RICNUM']);
                            if (!$this->praLib->CreaXMLINFO("RICHIESTA-INFOCAMERE", $dati_accorpata)) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0054', "Creazione file XMLINFO fallita per la pratica n. " . $proric_rec_accorpate['RICNUM'], __CLASS__);
                                return output::$html_out;
                            }

                            $xmlInfoAccorp = $this->praLib->CreaXMLINFO("RICHIESTA-INFOCAMERE", $dati_accorpata, false);
                            $xmlInfoAccorpDest = $dati['CartellaAllegati'] . '/XMLINFO_' . $proric_rec_accorpate['RICNUM'] . '.xml';
                            @copy($xmlInfoAccorp, $xmlInfoAccorpDest);
                            if (!file_exists($xmlInfoAccorpDest)) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0056', "Copia file XMLINFO fallita per la pratica accorpata n. " . $proric_rec_accorpate['RICNUM'], __CLASS__);
                                return output::$html_out;
                            }
                        }

                        /*
                         * Creo il file xml info
                         */
                        $Nome_file = $dati['CartellaAllegati'] . "/XMLINFO.xml";
                        $File = fopen($Nome_file, "w+");
                        if (!file_exists($Nome_file)) {
                            return output::$html_out;
                        } else {
                            $xml = $this->praLib->CreaXML($dati, $this->PRAM_DB);
                            fwrite($File, $xml);
                            fclose($File);
                        }

                        if (!$arrayDatiMail) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0048', "Impossibile inviare la mail. File " . $dati['CartellaMail'] . "/mail.xml non trovato", __CLASS__);
                            return output::$html_out;
                        }


                        $arrayNote = unserialize($dati['Ricite_rec']['RICNOT']);
                        $arrayNote['NOTE'] = "la richiesta " . pathinfo($cartellaZip, PATHINFO_BASENAME) . " e' stata inviata in data " . date('d/m/Y');
                        $arrayNote['INFOCAMERE']['FILENAME'] = pathinfo($cartellaZip, PATHINFO_BASENAME) . ".zip";
                        $arrayNote['INFOCAMERE']['DATE'] = date('dmY');
                        $dati['Ricite_rec']['RICNOT'] = serialize($arrayNote);
                        try {
                            $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICITE", 'ROWID', $dati['Ricite_rec']);
                        } catch (Exception $e) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento NOTE su RICITE della pratican. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                            return output::$html_out;
                        }

                        $Proric_rec = $dati['Proric_rec'];
                        $Proric_rec['RICSTA'] = '91';
                        $Proric_rec['RICDAT'] = date("Ymd");
                        $Proric_rec['RICTIM'] = date("H:i:s");
                        if (strpos($Proric_rec['RICSEQ'], "." . $dati['seq'] . ".") === false) {
                            $Proric_rec['RICSEQ'] = $Proric_rec['RICSEQ'] . "." . $dati['seq'] . ".";
                        }

                        try {
                            $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $Proric_rec);
                        } catch (Exception $e) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento su PRORIC della pratican. " . $Proric_rec['RICNUM'], __CLASS__);
                            return output::$html_out;
                        }

                        /*
                         * Aggiorno lo stato delle richieste accorpate.
                         */
                        foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                            $dati_accorpata = $this->praLibDati->prendiDati($proric_rec_accorpate['RICNUM']);
                            if (!$this->BloccaRichiesta($dati_accorpata)) {
                                return output::$html_out;
                            }
                        }

                        /*
                         * Invio Mail Richiedente
                         */
                        if ($dati['Ricite_rec']['ITEMRI'] == 0) {
                            if ($arrayParamBloccoMail['bloccaMailRich'] == null || $arrayParamBloccoMail['bloccaMailRich'] == "No") {
                                $modo = "RICHIESTA-INFOCAMERE";
                                $mailRich = $this->praLib->GetMailRichiedente($modo, $dati['Ricdag_tab_totali']);
                                $ErrorMail = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, $modo);
                                //if ($ErrorMail != 1) {
                                if ($ErrorMail) {
                                    $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al richiedente.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0050', "Invio mail richiedente pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                                    return false;
                                }
                            }
                        }

                        /*
                         * Invio Mail Responsabile
                         */
                        if ($dati['Ricite_rec']['ITEMRE'] == 0) {
                            if ($arrayParamBloccoMail['bloccaStarweb'] == "No") {
                                $TotaleAllegati = $this->praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM']);
                                if ($arrayParamBloccoMail['bloccaInvioInfo'] == "Si") {
                                    $TotaleAllegati = $this->RemoveFileInfo($TotaleAllegati);
                                }
                                $modo = "RICHIESTA-INFOCAMERE";
                                $ErrorMail = $this->praLib->InvioMailResponsabile($dati, $TotaleAllegati, $this->PRAM_DB, $arrayDatiMail, $modo);
                                //if ($ErrorMail != 1) {
//                                if ($ErrorMail) {
//                                    $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al resposansabile comunale.<b>
//                                           Riprovare piu tardi.<br>
//                                           Se il problema persiste contatatre l'assistenza software.";
//                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
//                                    return false;
//                                }
                            }
                        }

                        if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }

                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichInfocamere'], "body.html", "praGestPassi");
                        $this->praLib->ClearDirectory($dati['CartellaTemporary']);
                        return output::$html_out .= $htmlOutput;
                    } else {
                        output::addAlert($exec, 'Errore', 'error');
                    }
                } else {
                    $arrayNote = unserialize($dati['Ricite_rec']['RICNOT']);
                    $arrayNote['NOTE'] = "la richiesta " . pathinfo($cartellaZip, PATHINFO_BASENAME) . " e' stata scaricata in data " . date('d/m/Y');
                    $arrayNote['INFOCAMERE']['FILENAME'] = pathinfo($cartellaZip, PATHINFO_BASENAME) . ".zip";
                    $arrayNote['INFOCAMERE']['DATE'] = date('dmY');
                    $dati['Ricite_rec']['RICNOT'] = serialize($arrayNote);
                    try {
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICITE", 'ROWID', $dati['Ricite_rec']);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento NOTE su RICITE della pratican. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }

                    $Proric_rec = $dati['Proric_rec'];
                    $Proric_rec['RICSTA'] = '91';
                    $Proric_rec['RICDAT'] = date("Ymd");
                    $Proric_rec['RICTIM'] = date("H:i:s");
                    if (strpos($Proric_rec['RICSEQ'], "." . $dati['seq'] . ".") === false) {
                        $Proric_rec['RICSEQ'] = $Proric_rec['RICSEQ'] . "." . $dati['seq'] . ".";
                    }

                    try {
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $Proric_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento su PRORIC della pratican. " . $Proric_rec['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }

                    if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq'])) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }

                    $html = new html();

                    $downloadURI = $this->frontOfficeLib->getDownloadURI($arcpf, '', false);
                    $html->addJSWindow($downloadURI);

                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail, "body.html", "praGestPassi");
                    $html->appendHtml($htmlOutput);
                    $this->praLib->ClearDirectory($dati['CartellaTemporary']);
                    return output::$html_out .= $html->getHtml();
                }
                break;
            case 'htmlDistinta':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $html = new html();
                $dompdf = new DOMPDF();
                $Anatsp_rec = $dati['Anatsp_rec'];
                $html->appendHtml("<html>");
                $html->appendHtml("<head>");
                $html->appendHtml("<link href=\"" . ItaUrlUtil::UrlInc() . "/itaFrontOffice/css/style.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\">");
                $html->appendHtml("<link href=\"" . ITA_SUAP_PUBLIC . "/SUAP_italsoft/style.css\" media=\"screen\" rel=\"stylesheet\" type=\"text/css\">");
                $html->appendHtml("</head>");
                $html->appendHtml("<body>");
                $html->appendHtml("<table class=\"tabella\" cellspacing=\"5\" cellpadding=\"5\" border=\"2\">");
                $html->appendHtml("<tr class=\"tith\"><td colspan=\"2\" align=\"center\">Intestazione</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Identificativo SUAP</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPIDE'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Codice AOO</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPAOO'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Denominazione SUAP</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPDEN'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Comune</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPCOM'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Tipologia</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPTIP'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Indirizzo sito web</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPWEB'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Sito web modulistica</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPMOD'] . "</td></tr>");
                $html->appendHtml("<tr><td align=\"center\" class=\"txttab\">Indirizzo e-mail PEC</td><td align=\"center\" class=\"txttab\">" . $Anatsp_rec['TSPPEC'] . "</td></tr>");
                $html->appendHtml("</table>");
                $html->appendHtml("<br>");
                $html->appendHtml("<br>");

                /*
                 * Allegati
                 */
                $arrayAllegati = $this->praLib->GetFileList($dati['CartellaAllegati']);
                $html->appendHtml("<table class=\"tabella\" cellspacing=\"5\" cellpadding=\"5\" border=\"2\">");
                $html->appendHtml("<tr class=\"tith\"><td align=\"center\">Allegato</td></tr>");
                foreach ($arrayAllegati as $key => $allegato) {
                    $html->appendHtml("<tr>");
                    $html->appendHtml("<td align=\"center\" class=\"txttab\">" . $allegato['FILENAME'] . "</td>");
                    $html->appendHtml("</tr>");
                }
                $html->appendHtml("</table>");
                $html->appendHtml("</body></html>");

                $dompdf->load_html($html->getHtml());
                $dompdf->render();
                $output = $dompdf->output();
                $Seq_passo = str_pad($dati['seq'], $dati['seqlen'], "0", STR_PAD_LEFT);
                $pdfFile = $dati['CartellaAllegati'] . "/" . $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "_distinta.pdf";
                $File = fopen($pdfFile, "w+");
                fwrite($File, $output);
                fclose($File);
                $this->frontOfficeLib->scaricaFile($pdfFile);
                break;

            case 'imgAzione':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($dati['Ricite_rec']['ITEIMG'] != "") {
                    $Nome_img = $dati['CartellaRepository'] . "/immagini/" . $dati['Ricite_rec']['ITEIMG'];
                    $this->frontOfficeLib->vediAllegato($Nome_img);
                }
                break;

            case 'vediAllegato':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                $html = new html();

                switch ($this->request['mode']) {
                    case 'direct':
                        $frontOfficeLib = new frontOfficeLib;
                        $frontOfficeLib->vediAllegato($dati['CartellaAllegati'] . '/' . pathinfo($this->request['file'], PATHINFO_BASENAME), pathinfo($this->request['file'], PATHINFO_BASENAME));
                        break;

                    default:
                        $datiAllegato = $this->getPercorsoAllegato(pathinfo($this->request['file'], PATHINFO_BASENAME), $dati, pathinfo($this->request['file'], PATHINFO_BASENAME));
                        $downloadURI = $this->frontOfficeLib->getDownloadURI($datiAllegato['filepath'], $datiAllegato['filename']);
                        $html->addJSWindow($downloadURI);
                        break;
                }

                output::$html_out .= $html->getHtml();
                break;

            case 'cancellaAllegato':
                /*
                 * Leggo i dati posizionato al passo corrente
                 */
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Elenco delle dipendenze
                 */
                $Ricite_tab_default = $this->praLib->GetPassiDefaultDipendenze($this->PRAM_DB, $dati['Proric_rec']['RICNUM'], $dati['seq'], $dati['Ricite_rec']['ITEKEY']);
                if (strtolower(pathinfo($this->request['allegato'], PATHINFO_EXTENSION)) == "pdf" && $Ricdag_tab) {
                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
                    /* @var $praLibAllegati praLibAllegati */
                    $praLibAllegati = praLibAllegati::getInstance($this->praLib);

                    /*
                     * Status dei passi download e upload rapport completo
                     */
                    $statoRapporti = reset($praLibAllegati->getStatoRapporti($dati));

                    /*
                     * Preparo i parametri pe la simulazione della cancellazione passo corrente e dipendenze
                     */
                    $simulaCanc = array();
                    $simulaCanc[] = $dati['seq'];
                    foreach ($Ricite_tab_default as $Ricite_rec_default) {
                        $simulaCanc[] = $Ricite_rec_default['ITESEQ'];
                        $simulaCanc[] = $Ricite_rec_default['Ricite_rec_upl']['ITESEQ'];
                    }

                    /*
                     * Leggo i dati simulando la cancellazione del passo corrente
                     */
                    if (!$datiSimula = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'], $simulaCanc)) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }

                    /*
                     * Status dei passi download e upload rapporto completo con simulazione di cancellazione
                     */
                    $statoRapportiSimula = reset($praLibAllegati->getStatoRapporti($datiSimula));

                    /*
                     * Check delle variazioni critiche apportate dalla cancellazione dei passo
                     */
                    $arrWarning = array();

                    /*
                     * 1. Dipendenze
                     */
                    if ($Ricite_tab_default) {
                        $arrWarning[] = array(
                            "Codice" => 1,
                            "Messaggio" => "Vi sono dei passi precompilati in parte o totalmente con i dati del passo da annullare."
                        );
                    }

                    /*
                     * 2. Rapporto completo
                     */
                    if ($statoRapporti['PassoEseguito']) {
                        $msgRap = "Il rapporto completo scaricato sarà annullato e dovrà essere ricreato";
                        if ($statoRapportiSimula['AllegatiMancanti']) {
                            $arrWarning[] = array(
                                "Codice" => 2,
                                "Messaggio" => "Il rapporto completo scaricato non è più valido, allegati mancanti. L'annullamento del passo influisce sulla composizione del rapporto."
                            );
                        }
                        $schemaRapporto = array();
                        if ($statoRapporti['Metadati']['SCHEMARAPPORTO'])
                            $schemaRapporto = $statoRapporti['Metadati']['SCHEMARAPPORTO'];
                        $schemaRapportoSimula = array();
                        foreach ($schemaRapporto['Allegati'] as $allegatoRapporto) {
                            if ($allegatoRapporto['FILEPATH']) {
                                $schemaRapportoSimula[] = $allegatoRapporto['FILENAME'];
                            }
                        }
                        if ($schemaRapporto !== $schemaRapportoSimula) {
                            $arrWarning[] = array(
                                "Codice" => 3,
                                "Messaggio" => "Il rapporto completo scaricato non è più valido, composizione variata. L'annullamento del passo influisce sulla composizione del rapporto."
                            );
                        }
                        if ($statoRapporti['UploadCaricato'] == 1) {
                            $msgRap .= " e ricaricato";
                        }

                        if ($msgRap) {
                            $arrWarning[] = array(
                                "Codice" => 4,
                                "Messaggio" => $msgRap
                            );
                        }
                    }

                    $effettuaCheckAnnullaRaccolta = true;

                    /*
                     * Check per parametro disabilitazione conferma annulla raccolta
                     */
                    $datiAmbiente = $dati['Navigatore']['Dizionario_Richiesta_new']->getData('AMBIENTE');
                    if ($datiAmbiente && $datiAmbiente->getData('DISABILITA_CONFERMA_ANNULLA_RACCOLTA') == '1') {
                        $effettuaCheckAnnullaRaccolta = false;
                    }

                    if ($effettuaCheckAnnullaRaccolta && $arrWarning) {
                        $href = ItaUrlUtil::GetPageUrl(array('model' => 'sueGestPassi', 'allegato' => $this->request['allegato'], 'event' => 'confermaCancellaAllegato', 'seq' => $dati['seq'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                        $open = "location.replace('$href')";
                        $buttonConferma = "<div style=\"margin:10px;display:inline-block;\">
                                              <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ui-corner-all ui-state-default\" type=\"button\" onclick=\"$open\">
                                                 <div class=\"ui-icon ui-icon-check\" style=\"display:inline-block;vertical-align:bottom;\"></div>
                                                 <div class=\"\" style=\"display:inline-block;\"><b>Conferma</b></div>
                                             </button>
                                          </div>";
                        $buttonAnnulla = "<div style=\"margin:10px;display:inline-block;\">
                                           <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-close-dialog ui-corner-all ui-state-default\" type=\"button\">
                                                <div class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;vertical-align:bottom;\"></div>
                                                <div class=\"\" style=\"display:inline-block;\"><b>Annulla</b></div>
                                           </button>
                                         </div>";

                        $html = new html();
                        $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Cancellazione Allegato\"><br>");
                        foreach ($arrWarning as $warning) {
                            $html->appendHtml("<p style=\"margin:0 10px 0;color:orange;font-size:1.2em;\"><b>{$warning['Messaggio']}</b></p>");
                        }
                        $html->appendHtml("<br><br><p style=\"margin:0 10px 0;color:red;font-size:1.4em;\"><b>Confermi la cancellazione dell'allegato?</b></p>");

                        $html->appendHtml("<div style=\"float:right;\">");
                        $html->appendHtml($buttonConferma . $buttonAnnulla);
                        $html->appendHtml("</div>");
                        $html->appendHtml("</div>");
                        output::$html_out .= $html->getHtml();
                        break;
                    }
                }
            case 'confermaCancellaAllegato':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Cancello le dipendenze solo se è un pdf
                 */
                if (strtolower(pathinfo($this->request['allegato'], PATHINFO_EXTENSION)) == "pdf") {
                    /*
                     * Prioritariamente elimino le dipendenze dell'upload che sarà cancellato
                     */
                    $Ricite_tab_default = $this->praLib->GetPassiDefaultDipendenze($this->PRAM_DB, $dati['Proric_rec']['RICNUM'], $dati['seq'], $dati['Ricite_rec']['ITEKEY']);
                    if ($Ricite_tab_default) {
                        $msgErr = $this->praLib->EliminaDipendenze($Ricite_tab_default, $this->PRAM_DB, $dati);
                        if ($msgErr !== true) {
                            output::addAlert($msgErr, 'Errore', 'error');
                            break;
                        }


                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
                        /* @var $praLibAllegati praLibAllegati */
                        $praLibAllegati = praLibAllegati::getInstance($this->praLib);
                        $this->praLib->SbloccaCancellaRapporto($dati, $praLibAllegati, $this->PRAM_DB);

                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                    }
                }


                /*
                 * Cancello il download attuale
                 */
                if (!$this->praLib->CancellaUpload($this->PRAM_DB, $dati, $dati['Ricite_rec'], $this->request['allegato'])) {
                    output::addAlert(sprintf('Errore nel cancellare l\'allegato del passo "%s".', $dati['Ricite_rec']['ITEDES']), 'Errore', 'error');
                    break;
                }

                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Sincronizzo i file automatico con la nuova situazione delle sequenze
                 */
                $this->SincronizzaFileInserimentoAutomatico($dati);
                break;

            case 'annullaPratica':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                $dati['Proric_rec']['RICDAT'] = date('Ymd');
                $dati['Proric_rec']['RICTIM'] = date("H:i:s");
                $dati['Proric_rec']['RICSTA'] = "OF"; //'98';
                $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $dati['Proric_rec']);
                if ($nRows == -1) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0076', "Errore Annullamento Pratica", __CLASS__);
                    break;
                }
                $this->runCallback($dati, "annullaPratica");

                /*
                 * Scollego dalla pratica padre
                 */
                if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $dati['Proric_rec']['RICNUM'])) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0078', 'Errore Scollegamento Pratica n.' . $dati['Proric_rec']['RICNUM'], __CLASS__);
                    break;
                }

                /*
                 * Sgancio le pratiche legate a questa
                 */
                $proric_tab_accorpate = $this->praLib->GetRichiesteAccorpate($this->PRAM_DB, $dati['Proric_rec']['RICNUM']);

                if ($proric_tab_accorpate) {
                    foreach ($proric_tab_accorpate as $proric_rec_accorpate) {
                        if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $proric_rec_accorpate['RICNUM'])) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0077', 'Errore Scollegamento Pratica n.' . $proric_rec_accorpate['RICNUM'] . ' da Pratica n.' . $dati['Proric_rec']['RICNUM'], __CLASS__);
                            break 2;
                        }
                    }
                }

                $eqDesc = sprintf('Annullamento pratica %s', $dati['Proric_rec']['RICNUM']);
                eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));

                $numeroRichiesta = intval(substr($dati['Proric_rec']['RICNUM'], 4, 6)) . '/' . substr($dati['Proric_rec']['RICNUM'], 0, 4);
                $homepageUri = frontOfficeApp::$cmsHost->getSiteHomepageURI();
                $html = new html();
                $html->addAlert("Richiesta n. $numeroRichiesta cancellata con successo.<br /><small>Sarai rediretto all'homepage del portale tra pochi secondi. In caso contrario, <a href=\"$homepageUri\">clicca qui</a>.</small>", '', 'success');
                $html->addJSRedirect($homepageUri, 5);
                return output::$html_out .= $html->getHtml();

            case 'annullaRaccolta':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($dati['Consulta'] == true && !$dati['permessiPasso']['Edit']) {
                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
                    break;
                }

                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
                /* @var $praLibAllegati praLibAllegati */
                $praLibAllegati = praLibAllegati::getInstance($this->praLib);

                /*
                 * Elenco dati aggiuntivi, che compaiono nel passo della raccolta dati con XHTML
                 */

                /*
                 * Elenco delle dipendenze
                 */
                $Ricite_tab_default = $this->praLib->GetPassiDefaultDipendenze($this->PRAM_DB, $dati['Proric_rec']['RICNUM'], $dati['seq'], $dati['Ricite_rec']['ITEKEY']);

                /*
                 * Status dei passi download e upload rapport completo
                 */
                $statoRapporti = reset($praLibAllegati->getStatoRapporti($dati));

                /*
                 * Preparo i parametri pe la simulazione della cancellazione passo corrente e dipendenze
                 */
                $simulaCanc = array();
                $simulaCanc[] = $dati['seq'];
                foreach ($Ricite_tab_default as $Ricite_rec_default) {
                    $simulaCanc[] = $Ricite_rec_default['ITESEQ'];
                    $simulaCanc[] = $Ricite_rec_default['Ricite_rec_upl']['ITESEQ'];
                }

                /*
                 * Leggo i dati simulando la cancellazione del passo corrente
                 */
                if (!$datiSimula = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'], $simulaCanc)) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Status dei passi download e upload rapporto completo con simulazione di cancellazione
                 */
                $statoRapportiSimula = reset($praLibAllegati->getStatoRapporti($datiSimula));

                /*
                 * Check delle variazioni critiche apportate dalla cancellazione dei passo
                 */

                $arrWarning = array();

                /*
                 * 1. Dipendenze Passi
                 */
                if ($Ricite_tab_default) {
                    $arrWarning[] = array(
                        "Codice" => 1,
                        "Messaggio" => "Vi sono dei passi successivi che potrebbero contenere in parte o totalmente i dati del passo da modificare."
                    );
                }

                /*
                 * 2. Rapporto completo
                 */
                if ($statoRapporti['PassoEseguito']) {
                    $msgRap = "Il rapporto completo scaricato sarà annullato e dovrà essere ricreato";
                    if ($statoRapportiSimula['AllegatiMancanti']) {
                        $arrWarning[] = array(
                            "Codice" => 2,
                            "Messaggio" => "Il rapporto completo scaricato non è più valido, allegati mancanti. L'annullamento del passo influisce sulla composizione del rapporto."
                        );
                    }
                    $schemaRapporto = array();
                    if ($statoRapporti['Metadati']['SCHEMARAPPORTO'])
                        $schemaRapporto = $statoRapporti['Metadati']['SCHEMARAPPORTO'];
                    $schemaRapportoSimula = array();
                    foreach ($schemaRapporto['Allegati'] as $allegatoRapporto) {
                        if ($allegatoRapporto['FILEPATH']) {
                            $schemaRapportoSimula[] = $allegatoRapporto['FILENAME'];
                        }
                    }

                    if ($schemaRapporto !== $schemaRapportoSimula && $schemaRapportoSimula) {
                        $arrWarning[] = array(
                            "Codice" => 3,
                            "Messaggio" => "Il rapporto completo scaricato non è più valido, composizione variata. L'annullamento del passo influisce sulla composizione del rapporto."
                        );
                    }
                    if ($statoRapporti['UploadCaricato'] == 1) {
                        $msgRap .= " e ricaricato";
                    }
                    //if ($msgRap) {
                    if ($msgRap && $dati['Ricite_rec']['ITEIDR'] == 1) {
                        $arrWarning[] = array(
                            "Codice" => 4,
                            "Messaggio" => $msgRap
                        );
                    }
                }

                $effettuaCheckAnnullaRaccolta = true;

                /*
                 * Check per parametro disabilitazione conferma annulla raccolta
                 */
                $datiAmbiente = $dati['Navigatore']['Dizionario_Richiesta_new']->getData('AMBIENTE');
                if ($datiAmbiente && $datiAmbiente->getData('DISABILITA_CONFERMA_ANNULLA_RACCOLTA') == '1') {
                    $effettuaCheckAnnullaRaccolta = false;
                }

                if ($effettuaCheckAnnullaRaccolta && $arrWarning) {
                    $href = ItaUrlUtil::GetPageUrl(array('model' => 'sueGestPassi', 'allegato' => $this->request['allegato'], 'event' => 'confermaAnnullaRaccolta', 'seq' => $dati['seq'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
                    $open = "location.replace('$href')";
                    $buttonConferma = "<div style=\"margin:10px;display:inline-block;\">
                                              <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"italsoft-button\" type=\"button\" onclick=\"$open\">
                                                 <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                                 <div class=\"\" style=\"display:inline-block;\"><b>Conferma</b></div>
                                             </button>
                                          </div>";
                    $buttonAnnulla = "<div style=\"margin:10px;display:inline-block;\">
                                           <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-close-dialog italsoft-button italsoft-button--secondary\" type=\"button\">
                                                <i class=\"icon ion-close italsoft-icon\"></i>
                                                <div class=\"\" style=\"display:inline-block;\"><b>Annulla</b></div>
                                           </button>
                                         </div>";

                    $html = new html();
                    $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Attenzione\"><br>");
                    foreach ($arrWarning as $warning) {
                        $html->appendHtml("<p style=\"margin:0 10px 0;font-size:1.2em;\"><b>{$warning['Messaggio']}</b></p>");
                    }
                    $html->appendHtml("<br><br><p style=\"margin:0 10px 0;font-size:1.4em;\"><b>Confermi la modifica dei dati?</b></p>");
                    $html->appendHtml("<div style=\"float:right;\">");
                    $html->appendHtml($buttonAnnulla . $buttonConferma);
                    $html->appendHtml("</div>");
                    $html->appendHtml("</div>");
                    output::$html_out .= $html->getHtml();
                    break;
                }

            case 'confermaAnnullaRaccolta':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($dati['Consulta'] == true && !$dati['permessiPasso']['Edit']) {
                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
                    break;
                }

                if ($dati['Ricdag_tab'][0]['DAGKEY'] == "DENOM_FIERA") {
                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php';
                    $praLibGfm = new praLibGfm();
                    if (!$praLibGfm->AnnullaPassiFiere($dati)) {
                        output::addAlert('Errore annullamento passi fiere.', 'Errore', 'error');
                        break;
                    }
                    if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['Ricite_rec']['ITESEQ'])) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }
                }

                /*
                 * Prioritariamente elimino le dipendenze dell'upload che sarà cancellato
                 */
                $Ricite_tab_default = $this->praLib->GetPassiDefaultDipendenze($this->PRAM_DB, $dati['Proric_rec']['RICNUM'], $dati['seq'], $dati['Ricite_rec']['ITEKEY']);
                if ($Ricite_tab_default) {
                    $msgErr = $this->praLib->EliminaDipendenze($Ricite_tab_default, $this->PRAM_DB, $dati);
                    if ($msgErr !== true) {
                        output::addAlert($msgErr, 'Errore', 'error');
                        break;
                    }


                    if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }
                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibAllegati.class.php';
                    /* @var $praLibAllegati praLibAllegati */
                    $praLibAllegati = praLibAllegati::getInstance($this->praLib);
                    $this->praLib->SbloccaCancellaRapporto($dati, $praLibAllegati, $this->PRAM_DB);

                    if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }
                }
                if ($dati['Ricite_rec']['ITERDM'] == 1) {
                    if ($this->CheckFileFirmatoRaccolta($dati)) {
                        $html = new html();
                        $html->appendHtml("<script type=\"text/javascript\">alert(\"Attenzione!! L'allegato della raccolta, " . $dati['Ricite_rec']['ITEDES'] . ", è già stato caricato.\\nPer modificare i dati, cancellare l'allegato.\"); history.go(-1)</script>");
                        output::$html_out .= $html->getHtml();
                        break;
                    }
                }

                /*
                 * Scollego alla richiesta padre unica
                 */
                if ($dati['Ricite_rec']['ITERICSUB'] == 1) {
                    if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $dati['Proric_rec']['RICNUM'])) {
                        $html = new html();
                        $html->appendHtml("<script type=\"text/javascript\">alert(\"Attenzione!! Errore nello scollegamento della Pratica\"); history.go(-1)</script>");
                        output::$html_out .= $html->getHtml();
                        break;
                    }

                    $dati['Proric_rec']['RICRUN'] = '';
                }

                if (!$this->praLib->AnnullaRaccolta($this->PRAM_DB, $dati, $dati['Ricite_rec'])) {
                    /*
                     * error message
                     */
                }
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $this->SincronizzaFileInserimentoAutomatico($dati);
                break;

            case 'submitRaccoltaMultipla':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($dati['Consulta'] == true && !$dati['permessiPasso']['Insert']) {
                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
                    break;
                }

                $html = new html();

                /*
                 * NUOVA VERSIONE LETTURA DA CMSHOST
                 */
                $raccolta = $this->praLib->CheckCampiRaccolta($dati['Ricite_rec'], frontOfficeApp::$cmsHost->getRequest('raccolta'), $this->PRAM_DB);

                /*
                 * Inserisco l'array raccolta nei dati, così da poterla utilizzare nelle uscite
                 */
                $dati['raccolta'] = $raccolta;

                /*
                 * LANCIO CUSTOM CLASS PARAMETRICO
                 */
                $retCustomClass = $this->callCustomClass(praLibCustomClass::AZIONE_PRE_SUBMIT_RACCOLTA, $dati, true);
                switch ($retCustomClass) {
                    case 0:
                        return output::$html_out;

                    case 2:
                        break 2;
                }

                /*
                 * LANCIO CLASSI STANDARD
                 */
                $retStandardExit = $this->callStandardExit(praLibStandardExit::AZIONE_PRE_SUBMIT_RACCOLTA, $dati);
                switch ($retStandardExit) {
                    case 0:
                        return output::$html_out;

                    case 2:
                        break 2;
                }

                if ($raccolta) {
                    /*
                     * Controllo se eliminato qualche dagset dal FO. Se si lo elimino anche dal DB
                     */
                    $sql = "SELECT DISTINCT DAGSET FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                    $dagset_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

                    $arrayDagsetDB = array();
                    foreach ($dagset_tab as $dagset_rec) {
                        $arrayDagsetDB[substr($dagset_rec['DAGSET'], -2)] = $dagset_rec['DAGSET'];
                    }
                    $arrayDiffDagset = array_diff_key($arrayDagsetDB, $raccolta);
                    if ($arrayDiffDagset) {
                        foreach ($arrayDiffDagset as $dagset) {
                            $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='$dagset'";
                            $dagsetDaCanc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                            foreach ($dagsetDaCanc_tab as $key => $dagsetDaCanc_rec) {
                                $nRows = ItaDB::DBDelete($this->PRAM_DB, "RICDAG", 'ROWID', $dagsetDaCanc_rec['ROWID']);
                            }
                        }
                    }
                    /*
                     * Ciclo per fare insert/update dei campi
                     */
                    foreach ($raccolta as $dagset => $valore) {
                        foreach ($valore as $campo => $value) {
                            $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_$dagset'";
                            $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            //Controllo se Ricdag esiste
                            if ($Ricdag_rec) {
                                if (is_array($value)) {
                                    $Ricdag_rec['RICDAT'] = serialize($value);
                                } else {
                                    if ($Ricdag_rec['DAGTIP'] == "Codfis_InsProduttivo") {
                                        $Ricdag_rec['RICDAT'] = trim($value);
                                    } else {
                                        $Ricdag_rec['RICDAT'] = utf8_decode($value);
                                        if ($Ricdag_rec['DAGTIC'] == "CheckBox") {
                                            $meta = unserialize($Ricdag_rec["DAGMETA"]);
                                            $returnValues = $meta['ATTRIBUTICAMPO']['RETURNVALUES'];
                                            $arrayReturVal = explode("/", $returnValues);
                                            $valCheched = $arrayReturVal[0];
                                            if ($valCheched == "")
                                                $valCheched = "On";
                                            $valUncheched = $arrayReturVal[1];
                                            if ($valUncheched == "")
                                                $valUncheched = "Off";
                                            if ($value == 1) {
                                                $Ricdag_rec['RICDAT'] = $valCheched;
                                            } else {
                                                $Ricdag_rec['RICDAT'] = $valUncheched;
                                            }
                                        }
                                    }
                                }
                                $Ricdag_rec['DAGSET'] = $Ricdag_rec['ITEKEY'] . "_$dagset";
                                /*
                                 * Aggiorno Record Ricdag esistente
                                 */
                                $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                            } else {
                                /*
                                 * Prendo come record iniziale il primo, quello con dagset 01 poi cambio i valori
                                 */
                                $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET LIKE '" . $dati['Ricite_rec']['ITEKEY'] . "___' ORDER BY DAGSET ASC";
                                $Ricdag_rec_first = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                                $Ricdag_rec_new = $Ricdag_rec_first;
                                $Ricdag_rec_new['DAGSET'] = $Ricdag_rec_new['ITEKEY'] . "_$dagset";
                                $Ricdag_rec_new['ROWID'] = 0;
                                if (is_array($value)) {
                                    $Ricdag_rec_new['RICDAT'] = serialize($value);
                                } else {
                                    if ($Ricdag_rec_new['DAGTIP'] == "Codfis_InsProduttivo") {
                                        $Ricdag_rec_new['RICDAT'] = trim($value);
                                    } else {
                                        $Ricdag_rec_new['RICDAT'] = utf8_decode($value);
                                    }
                                }
                                /*
                                 * Inserisco Ricdag_Rec aggiunto con JQuery
                                 */
                                $nRows = ItaDB::DBInsert($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec_new);
                            }
                        }
                    }

                    /*
                     * Valuto le Espressioni di RICCONTROLLI se ci sono
                     */
                    $flErrore = false;
                    if ($dati['Riccontrolli_tab']) {
                        $praVar = new praVars();
                        $praVar->setPRAM_DB($this->PRAM_DB);
                        $praVar->setGAFIERE_DB($this->GAFIERE_DB);
                        $praVar->setDati($dati);
                        $praVar->loadVariabiliRichiesta();
                        $msg = $this->praLib->CheckValiditaPasso($dati['Riccontrolli_tab'], $praVar->getVariabiliRichiesta()->getAlldataPlain("", "."));
                        if ($msg) {
                            if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                                return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                            }

                            output::addAlert($msg, 'Errore', 'error');
                            $flErrore = true;
                        }
                    }

                    if ($flErrore == true) {
                        break;
                    }

                    /*
                     * Ciclo per fare il controllo dei campi
                     */
                    foreach ($raccolta as $dagset => $valore) {
                        $campiObl = $campiNnConformi = $dag_set = $Ricdag_rec = "";
                        foreach ($valore as $campo => $value) {
                            $controlloVie = false;
                            $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET ='" . $dati['Ricite_rec']['ITEKEY'] . "_$dagset'";
                            $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            if (!$Ricdag_rec) {
                                /*
                                 * Se non c'è il Ricdag_rec, cioè se il campo è nuovo, prendo i controlli dei campi del primo dagset
                                 */
                                $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "' AND DAGSET LIKE '" . $dati['Ricite_rec']['ITEKEY'] . "___' ORDER BY DAGSET ASC";
                                $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                            }
                            $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                            if ($Ricdag_rec['DAGCTR']) {
                                $obbl = $this->praLib->ctrExpression($Ricdag_rec, $valore, "DAGCTR");
                                /*
                                 * Controllo Obbligatorieta campi
                                 */
                                if ($obbl) {
                                    if ($Ricdag_rec['DAGTIC'] == "CheckBox") {
                                        if ($value == 0 || $value == "Off") {
                                            $campiObl .= "<span><b>" . $etichetta . "</b></span><br>";
                                        }
                                    } else {
                                        if ($value == '') {
                                            $campiObl .= "<span><b>" . $etichetta . "</b></span><br>";
                                        }
                                    }
                                }
                            }
                            /*
                             * Controllo Conformita campi
                             */
                            if ($Ricdag_rec['DAGVCA'] && $value != '') {
                                if (!$this->praLibDatiAggiuntivi->controllaValidoSe($Ricdag_rec['DAGVCA'], $value, $Ricdag_rec['DAGREV'])) {
                                    $campiNnConformi .= "Il campo <b>$etichetta</b> contiene un valore non valido.";
                                }
                            }

                            if ($campo == 'VIA1' || $campo == 'VIA2' || $campo == 'VIA3') {
                                $controlloVie = true;
                                break 2;
                            }
                        }

                        if ($campiObl) {
                            $dag_set = "Maschera <b>" . $dagset . "</b>";
                            $msgObl .= "<br><span style=\"font-size:1.1em;color:red;text-decoration:underline;\">$dag_set</b></span><br>$campiObl";
                        }
                        if ($campiNnConformi) {
                            $dag_set = "Maschera <b>" . $dagset . "</b>";
                            $msgNnConforme .= "<br><span style=\"font-size:1.1em;color:red;text-decoration:underline;\">$dag_set</span><br>$campiNnConformi";
                        }
                    }

                    /*
                     * Controllo duplicità vie
                     */
                    if ($controlloVie) {
                        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                        $praLibGfm = new praLibGfm();
                        $uguali = $praLibGfm->CheckVieDoppie($raccolta);
                        if ($uguali) {
                            output::addAlert('Risultano esserci vie uguali per la stessa fiera.<br />Si prega di indicare 3 vie diverse per ogni fiera.', 'Errore', 'error');

                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            break;
                        }
                    }



                    if ($msgObl) {
                        output::addAlert($msgObl, 'Compilare i seguenti campi', 'error');
                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                        break;
                    }

                    if ($campiNnConformi == true) {
                        output::addAlert($msgNnConforme, 'I seguenti campi non sono conformi', 'error');
                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                        break;
                    }
                }

                /*
                 * Aggiorno la sequenza
                 */
                $sequenza = $dati['Proric_rec']['RICSEQ'];
                if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
                    if ($dati['Ricite_rec']['RICERF'] == 0) {
                        $dati['Ricite_rec']['RICQSTRIS'] = 1;
                        $dati['Proric_rec']['RICSEQ'] = $dati['Proric_rec']['RICSEQ'] . "." . $dati['seq'] . ".";
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICITE", 'ROWID', $dati['Ricite_rec']);
                        $nRows1 = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                        if ($nRows == -1 || $nRows1 == -1) {
                            //Errore ???
                        }
                    }
                }

                $dati = $datiRDM = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                foreach ($dati['Navigatore']['Ricite_tab_new'] as $key1 => $Ricite_rec) {
                    if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                        $Ricite_rec_successivo = $dati['Navigatore']['Ricite_tab_new'][$key1 + 1];
                        break;
                    }
                }
                $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $Ricite_rec_successivo['ITESEQ']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                //
                //Creo il pdf (Faccio un prendi dati con la sequenza del passo raccolta dati altrimenti non mi trova i dati aggiuntivi
                //
                $datiRDM = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                if (!$datiRDM) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($datiRDM['Ricite_rec']['ITEMETA']) {
                    $metadati = unserialize($datiRDM['Ricite_rec']['ITEMETA']);
                    if ($metadati === false) {
                        $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile estrapolare il tempalte del testo</p>
                                               </div>");
                        output::$html_out .= $html->getHtml();
                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                        break;
                    }

                    if (isset($metadati["TESTOBASEXHTML"])) {
                        $pdfPreview = $this->praLib->CreaPdfRaccolta($datiRDM, $this->PRAM_DB);
                        if (!$pdfPreview) {
                            $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile creare il pdf</p>
                                               </div>");
                            output::$html_out .= $html->getHtml();
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            break;
                        }

                        $raccolta_pdfa = $this->praLib->SalvaPdfRaccolta($pdfPreview, $datiRDM, $this->PRAM_DB);
                        if (!$raccolta_pdfa) {
                            $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile salvare il pdf</p>
                                               </div>");
                            output::$html_out .= $html->getHtml();
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            break;
                        }

                        //Creo l'href per fare il download del file

                        $datiAllegato = $this->getPercorsoAllegato(pathinfo($raccolta_pdfa, PATHINFO_BASENAME), $datiRDM, pathinfo($raccolta_pdfa, PATHINFO_BASENAME));
                        $downloadURI = $this->frontOfficeLib->getDownloadURI($datiAllegato['filepath'], $datiAllegato['filename']);
                        $html->addJSRedirect($downloadURI);

                        output::$html_out .= $html->getHtml();
//                    $raccolta_pdfa = $this->praLib->CreaPdfRaccolta($datiRDM, $this->PRAM_DB);
//                    if (!$raccolta_pdfa) {
//                        $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
//                                                   <p>Impossibile creare il pdf</p>
//                                               </div>");
//                        output::$html_out .= $html->getHtml();
//                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
//                        break;
//                    }
//                    //Creo l'href per fare il download del file
//                    $Url = ItaUrlUtil::GetPageUrl(array('model' => 'sueGestPassi', 'event' => 'gestioneAllegato', 'orig' => pathinfo($raccolta_pdfa, PATHINFO_BASENAME), 'file' => pathinfo($raccolta_pdfa, PATHINFO_BASENAME), 'operation' => 'download', 'ricnum' => $dati['Proric_rec']['RICNUM'], "seq" => $datiRDM['seq']));
//                    $html = new html();
//                    $html->appendHtml("<SCRIPT language=JavaScript>");
//                    $html->appendHtml("$(function(){");
//                    $html->appendHtml("location.replace('$Url');");
//                    $html->appendHtml("});");
//                    $html->appendHtml("</SCRIPT>");
//                    output::$html_out .= $html->getHtml();
//                        break;
                    }
                }

                /*
                 * Inserisco l'array raccolta nei dati, così da poterla utilizzare nelle uscite
                 */
                $datiRDM['raccolta'] = $raccolta;

                /*
                 * LANCIO CUSTOM CLASS PARAMETRICO
                 */

                if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_SUBMIT_RACCOLTA, $datiRDM, true)) {
                    return false;
                }

                /*
                 * LANCIO CLASSI STANDARD
                 */
                if (!$this->callStandardExit(praLibStandardExit::AZIONE_POST_SUBMIT_RACCOLTA, $datiRDM)) {
                    return output::$html_out;
                }
                //$this->SincronizzaFileInserimentoAutomatico($dati);
                break;
            case 'downloadRaccolta':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $raccolta_pdfa = $this->praLib->CreaPdfRaccolta($dati, $this->PRAM_DB);
                //Eseguo il download del file
                $this->frontOfficeLib->scaricaFile($raccolta_pdfa, $raccolta_pdfa);
                break;
            case 'submitRaccolta':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($dati['Consulta'] == true && !$dati['permessiPasso']['Insert']) {
                    output::addAlert($dati['ConsultaMessaggio'], 'Attenzione', 'warning');
                    break;
                }

                /*
                 * NUOVA VERSIONE LETTURA CON CMSHOST
                 */
                $raccolta = $this->praLib->CheckCampiRaccoltaSingola($dati['Ricite_rec'], frontOfficeApp::$cmsHost->getRequest('raccolta'), $this->PRAM_DB);

                /*
                 * Inserisco l'array raccolta nei dati, così da poterla utilizzare nelle uscite
                 */
                $dati['raccolta'] = $raccolta;

                $html = new html();

                /*
                 * LANCIO CUSTOM CLASS PARAMETRICO
                 */

                $retCustomClass = $this->callCustomClass(praLibCustomClass::AZIONE_PRE_SUBMIT_RACCOLTA, $dati, true);
                switch ($retCustomClass) {
                    case 0:
                        return output::$html_out;

                    case 2:
                        break 2;
                }

                /*
                 * LANCIO CLASSI STANDARD
                 */
                $retStandardExit = $this->callStandardExit(praLibStandardExit::AZIONE_PRE_SUBMIT_RACCOLTA, $dati);
                switch ($retStandardExit) {
                    case 0:
                        return output::$html_out;

                    case 2:
                        break 2;
                }

                /*
                 * Creazione Rapporti Completi pratiche accorpate
                 */
                if ($dati['Ricite_rec']['ITERICUNI'] != 0) {
                    $Proric_accorpate_tab = $this->praLib->GetRichiesteAccorpate($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);

                    foreach ($Proric_accorpate_tab as $Proric_accorpate_rec) {
                        $dati_sub = $this->praLibDati->prendiDati($Proric_accorpate_rec['RICNUM']);

                        /*
                         * Controllo preventivo dei file da accorpare nel rapporto.
                         * Se non ci sono, non creo il rapporto
                         */
                        $arrayPdf = $this->praLib->ControllaRapportoConfig($dati_sub, $dati_sub['Ricite_rec']);
                        if (count($arrayPdf) == 0) {
                            continue;
                        }
                        $rapportoCompletoAccorpato = $this->praLib->creaRapportoCompleto($dati_sub, $this->config['ditta'], $this->PRAM_DB, false);
                        if (!$rapportoCompletoAccorpato) {
                            if ($this->praLib->getErrCode() == -1) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0083', 'Errore creazione rapporto completo richiesta n.' . $Proric_accorpate_rec['RICNUM'] . ' accorpata alla richiesta n.' . $dati['Proric_rec']['RICNUM'] . ': ' . $this->praLib->getErrMessage(), __CLASS__);
                                return output::$html_out;
                            } else if ($this->praLib->getErrCode() == -2) {
                                output::addAlert($this->praLib->getErrMessage(), 'Attenzione', 'error');
                                break 2;
                            }
                        }
                        $rapportoCompletoFilename = $dati['Ricite_rec']['RICNUM'] . '_C' . str_pad($dati['Ricite_rec']['ITESEQ'], $dati['seqlen'], '0', STR_PAD_LEFT) . '_Rapporto' . $dati_sub['Proric_rec']['RICNUM'] . '.pdf';
                        $destinazioneRapportoCompleto = $dati['CartellaAllegati'] . '/' . $rapportoCompletoFilename;

                        copy($rapportoCompletoAccorpato, $destinazioneRapportoCompleto);
                        chmod($destinazioneRapportoCompleto, 0777);
                        $this->praLib->registraRicdoc($dati['Ricite_rec'], basename($destinazioneRapportoCompleto), basename($rapportoCompletoAccorpato), $this->PRAM_DB, array(), false, $dati['CartellaAllegati']);
                    }
                }

                /*
                 * Primo Ciclo per salvare i campi della raccolta dati
                 */
                foreach ($raccolta as $campo => $valore) {
                    $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                    $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    if ($Ricdag_rec) {
                        if (is_array($valore)) {
                            $Ricdag_rec['RICDAT'] = serialize($valore);
                        } else {
                            if ($Ricdag_rec['DAGTIP'] == "Codfis_InsProduttivo") {
                                $Ricdag_rec['RICDAT'] = trim($valore);
                            } else {
                                //$Ricdag_rec['RICDAT'] = $valore;
                                $Ricdag_rec['RICDAT'] = utf8_decode($valore);
                                if ($Ricdag_rec['DAGTIC'] == "CheckBox") {
                                    $meta = unserialize($Ricdag_rec["DAGMETA"]);
                                    $returnValues = $meta['ATTRIBUTICAMPO']['RETURNVALUES'];
                                    $arrayReturVal = explode("/", $returnValues);
                                    $valCheched = $arrayReturVal[0];
                                    if ($valCheched == "")
                                        $valCheched = "On";
                                    $valUncheched = $arrayReturVal[1];
                                    if ($valUncheched == "")
                                        $valUncheched = "Off";
                                    if ($valore == 1) {
                                        $Ricdag_rec['RICDAT'] = $valCheched;
                                    } else {
                                        $Ricdag_rec['RICDAT'] = $valUncheched;
                                    }
                                }
                            }
                        }
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec);
                        if ($Ricdag_rec['DAGTIP'] == "Sportello_Aggregato") {
                            if ($Ricdag_rec['RICDAT'] == 0) {
                                output::addAlert('Scegliere obbligatoriamente un comune aggregato.', 'Errore', 'error');
                                $flErrore = true;
                                break;
                            }

                            $dati['Proric_rec']['RICSPA'] = $Ricdag_rec['RICDAT'];
                        }
                        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTipizzati.class.php';
                        $praLibTipizzati = new praLibTipizzati();
                        $retAggiornaTipizzati = $praLibTipizzati->decodificaTipizzato($dati, $Ricdag_rec);
                        if (!$retAggiornaTipizzati) {
                            
                        }
                    }
                }

                if ($flErrore == true) {
                    break;
                }

                /*
                 * Secondo ciclo per controllo campi raccolta
                 */
                $msg = "";
                $checkFiereSel = true;
                $campiObl = "";
                $flagSaltoAccorpamento = false;
                foreach ($raccolta as $campo => $valore) {
                    $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = '" . $campo . "' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
                    $Ricdag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);

                    $obbl = false;
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];

                    /*
                     * Controllo manipolazione codice evento richiesta
                     */
                    if ($Ricdag_rec['DAGTIP'] == 'Evento_Richiesta') {
                        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');
                        $praLibEventi = new praLibEventi();
                        $Iteevt_tab = $praLibEventi->getEventi($this->PRAM_DB, $dati['Anapra_rec']['PRANUM'], $dati['Proric_rec']['RICDRE']);
                        foreach ($Iteevt_tab as $Iteevt_rec) {
                            if ($Ricdag_rec['RICDAT'] == $Iteevt_rec['IEVCOD']) {
                                $evento_richiesta = $Ricdag_rec['RICDAT'];
                                $evento_segnalazione_richiesta = $praLibEventi->getSegnalazioneComunicaEvento($this->PRAM_DB, $evento_richiesta);
                            }
                        }
                    }

                    /*
                     * Controllo Richiesta Unica
                     */
                    if ($campo == 'RICHIESTA_UNICA') {
                        $dati['Proric_rec']['RICRUN'] = "";
                        if ($valore) {
                            $proric_richiesta_padre = $this->praLib->GetProric($valore, 'codice', $dati['PRAM_DB']);
                            if ($proric_richiesta_padre['RICRUN'] && $proric_richiesta_padre['RICRUN'] == $dati['Proric_rec']['RICNUM']) {
                                output::addAlert('La richiesta accorpata è già accorpata in quella attuale.', 'Errore', 'error');
                                break 2;
                            }

                            if (!$this->praLib->accorpaAPraticaUnica($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM'], $valore)) {
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0063', $this->praLib->getErrMessage(), __CLASS__);
                                break 2;
                            }

                            $flagSaltoAccorpamento = true;
                            $dati['Proric_rec']['RICRUN'] = $valore;
                        }
                    }

                    /*
                     * Controllo se almeno una fiera è stata ceccata
                     */
                    if ($campo == 'DENOM_FIERA') {
                        /*
                         * Ipotesi di controllo per fiere selezionabili
                         * Ora la logica è che se non vi sono fiere selezionabili l'utente può fare una scelta libera.                     
                         */
                        $checkFiereSel = $this->CheckFiereVuote($dati['Anafiere_tab'], $raccolta);
                    }

                    /*
                     * Controllo se c'è una richiesta padre e aggiorna il campo Richiesta Padre
                     */
                    if (strpos($campo, 'RICHIESTA_PADRE') !== false) {
                        $dati['Proric_rec']['RICRPA'] = "";
                        $dati['Proric_rec']['RICPC'] = "";
                        if ($valore) {
                            $dati['Proric_rec']['RICRPA'] = $valore;
                        }
                        if ($campo == 'RICHIESTA_PADRE_VARIANTE') {
                            $dati['Proric_rec']['RICPC'] = "1";
                        }
                    }

                    if (strpos($campo, 'PRATICA_INIZIALE') !== false) {
                        $dati['Proric_rec']['RICPC'] = "1";
                    }

                    if ($Ricdag_rec['DAGCTR']) {
                        $obbl = $this->praLib->ctrExpression($Ricdag_rec, $raccolta, "DAGCTR");
                    }

                    /*
                     * Controllo Obbligatorieta campi
                     */
                    if ($obbl) {
                        if ($Ricdag_rec['DAGTIC'] == "CheckBox") {
                            if ($valore == 0 || $valore == "Off") {
                                $campiObl .= "<li>$etichetta</li>";
                            }
                        } else {
                            if ($valore == '') {
                                $campiObl .= "<li>$etichetta</li>";
                            }
                        }
                    }

                    /*
                     * Controllo Conformita campi
                     */
                    if ($Ricdag_rec['DAGVCA'] && $valore != '') {
                        if (!$this->praLibDatiAggiuntivi->controllaValidoSe($Ricdag_rec['DAGVCA'], $valore, $Ricdag_rec['DAGREV'])) {
                            $trovato = true;
                            $msg .= "Il campo <b>$etichetta</b> contiene un valore non valido.";
                        }
                    }
                }

                if (!$checkFiereSel) {
                    output::addAlert('Selezionare almeno una delle fiere presenti.', 'Errore', 'error');
                    break;
                }

                if ($campiObl) {
                    output::addAlert("I seguenti campi sono obbligatori:<br /><ul>$campiObl</ul>", 'Errore', 'error');
                    $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                    break;
                }

                if ($trovato == true) {
                    output::addAlert('I campi con le seguenti etichette non sono conformi.<br />' . $msg, 'Errore', 'error');
                    $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                    break;
                } else {
                    $flErrore = false;

                    /*
                     * Valuto le Espressioni di RICCONTROLLI se ci sono
                     */
                    if ($dati['Riccontrolli_tab']) {
                        $praVar = new praVars();
                        $praVar->setPRAM_DB($this->PRAM_DB);
                        $praVar->setGAFIERE_DB($this->GAFIERE_DB);
                        $praVar->setDati($dati);
                        $praVar->loadVariabiliRichiesta();
                        $msg = $this->praLib->CheckValiditaPasso($dati['Riccontrolli_tab'], $praVar->getVariabiliRichiesta()->getAlldataPlain("", "."));
                        if ($msg) {
                            if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                                return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                            }
                            output::addAlert($msg, 'Errore', 'error');
                            $flErrore = true;
                        }
                    }

                    if ($flErrore == true) {
                        break;
                    }
                }

                if (isset($evento_richiesta)) {
                    $dati['Proric_rec']['RICEVE'] = $evento_richiesta;
                    $dati['Proric_rec']['RICSEG'] = $evento_segnalazione_richiesta;
                    ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                }

                /*
                 * Spostato sotto per problema passo che diventava verde anche se andava in errore la creazione del pdf
                 */
                $sequenza = $dati['Proric_rec']['RICSEQ'];
                if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
                    if ($dati['Ricite_rec']['RICERF'] == 0) {
                        $dati['Proric_rec']['RICSEQ'] = $dati['Proric_rec']['RICSEQ'] . "." . $dati['seq'] . ".";
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                        if ($nRows == -1) {
                            //Errore ???
                        }
                    }
                }

                /*
                 * Aggiunto il 20/05/2014 per problema spunte ferri
                 */
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * LANCIO CUSTOM CLASS PARAMETRICO
                 */

                if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_SUBMIT_RACCOLTA, $dati, true)) {
                    return output::$html_out;
                }

                /*
                 * Ricarico il prendiDati a seguito della chiamata alla customClass
                 */
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                foreach ($dati['Navigatore']['Ricite_tab_new'] as $key1 => $Ricite_rec) {
                    if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                        $Ricite_rec_successivo = $dati['Navigatore']['Ricite_tab_new'][$key1 + 1];
                        break;
                    }

                    if ($flagSaltoAccorpamento && $Ricite_rec['ITESEQ'] == $this->request['seq']) {
                        $flagSaltoAccorpamento = false;
                    }
                }

                if ($flagSaltoAccorpamento === true) {
                    $Ricite_rec_successivo = $dati['Ricite_rec'];
                }

                if (!$dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $Ricite_rec_successivo['ITESEQ'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Creo il pdf (Faccio un prendi dati con la sequenza del passo raccolta dati altrimenti non mi trova i dati aggiuntivi
                 */
                $datiRDM = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                if (!$datiRDM) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($datiRDM['Ricite_rec']['ITEMETA']) {
                    $metadati = unserialize($datiRDM['Ricite_rec']['ITEMETA']);
                    if ($metadati === false) {
                        $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile estrapolare il tempalte del testo</p>
                                               </div>");
                        output::$html_out .= $html->getHtml();
                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                        if (!$this->praLib->AnnullaRaccolta($this->PRAM_DB, $dati, $dati["Ricite_rec"])) {
                            $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Annullamento Raccolta Dati\">
                                                   <p>Impossibile annullare il passo raccolta</p>
                                               </div>");
                            output::$html_out .= $html->getHtml();
                        }
                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                        break;
                    }

                    /*
                     * Creo la raccolta solo se il template esiste
                     */
                    if (isset($metadati["TESTOBASEXHTML"])) {
                        $pdfPreview = $this->praLib->CreaPdfRaccolta($datiRDM, $this->PRAM_DB);
                        if (!$pdfPreview) {
                            $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile creare il pdf</p>
                                               </div>");
                            output::$html_out .= $html->getHtml();
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            if (!$this->praLib->AnnullaRaccolta($this->PRAM_DB, $dati, $dati["Ricite_rec"])) {
                                $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Annullamento Raccolta Dati\">
                                                   <p>Impossibile annullare il passo raccolta</p>
                                               </div>");
                                output::$html_out .= $html->getHtml();
                            }
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            break;
                        }
                        $raccolta_pdfa = $this->praLib->SalvaPdfRaccolta($pdfPreview, $datiRDM, $this->PRAM_DB);
                        if (!$raccolta_pdfa) {
                            $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Creazione PDF Raccolta Dati\">
                                                   <p>Impossibile salvare il pdf</p>
                                               </div>");
                            output::$html_out .= $html->getHtml();
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            if (!$this->praLib->AnnullaRaccolta($this->PRAM_DB, $dati, $dati["Ricite_rec"])) {
                                $html->appendHtml("<div style=\"display:none;margin:5px;\" class=\"ita-alert\" title=\"Annullamento Raccolta Dati\">
                                                   <p>Impossibile annullare il passo raccolta</p>
                                               </div>");
                                output::$html_out .= $html->getHtml();
                            }
                            $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $this->request['seq']);
                            break;
                        }
                        $origName = pathinfo($raccolta_pdfa, PATHINFO_BASENAME);
                        $Ricdoc_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDOC WHERE DOCNUM = '" . $datiRDM['Proric_rec']['RICNUM'] . "' AND ITEKEY = '" . $datiRDM['Ricite_rec']['ITEKEY'] . "' AND DOCUPL = '" . pathinfo($raccolta_pdfa, PATHINFO_BASENAME) . "'", false);
                        if ($Ricdoc_rec) {
                            $origName = $Ricdoc_rec['DOCNAME'];
                        }

                        /*
                         * Creo l'href per fare il download del file
                         */

                        $datiAllegato = $this->getPercorsoAllegato(pathinfo($raccolta_pdfa, PATHINFO_BASENAME), $datiRDM, $origName);
                        $downloadURI = $this->frontOfficeLib->getDownloadURI($datiAllegato['filepath'], $datiAllegato['filename']);
                        $html->addJSRedirect($downloadURI);

                        output::$html_out .= $html->getHtml();
                        break;
                    }
                }

                $this->SincronizzaFileInserimentoAutomatico($dati);
                break;

            case 'eliminaControlli':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $controlli = array();
                $controlli['ITECTP'] = $dati['Ricite_rec']['ITECTP'];
                $dati['Ricite_rec']['ITECTP'] = "";
                $dati['Ricite_rec']['RICNOT'] = serialize($controlli);
                $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'RICITE', 'ROWID', $dati['Ricite_rec']);
                if (!$dati = $this->praLibDati->prendiDati($dati['Ricite_rec']['RICNUM'], $dati['Ricite_rec']['ITESEQ'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                break;

            case 'ripristinaControlli':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $controlli = unserialize($dati['Ricite_rec']['RICNOT']);
                $dati['Ricite_rec']['ITECTP'] = $controlli['ITECTP'];
                unset($controlli['ITECTP']);
                $dati['Ricite_rec']['RICNOT'] = serialize($controlli);
                $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'RICITE', 'ROWID', $dati['Ricite_rec']);
                if (!$dati = $this->praLibDati->prendiDati($dati['Ricite_rec']['RICNUM'], $dati['Ricite_rec']['ITESEQ'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                break;

            case 'inoltroAgenzia':
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                $Proric_rec = $dati['Proric_rec'];
                if (!$this->request['agenzia']) {
                    output::addAlert('Selezionare obbligatoriamente un\'Agenzia.', 'Errore', 'error');
                    break;
                }
                $Proric_rec['RICAGE'] = $this->request['agenzia'];
                $hash = md5($this->request['agenzia'] . $dati['Anatsp']['TSPIDE'] . $dati['Proric_rec']['RICNUM'] . microtime());
                $ret = $this->praLib->InoltroAdAgenzia($this->request['agenzia'], $this->PRAM_DB, $dati['Proric_rec'], $hash);
                $arrayNote = unserialize($dati['Ricite_rec']['RICNOT']);
                if ($ret == "OK") {
                    /*
                     * Se response è andato a buon fine creo i metadati si RICITE
                     */
                    $newArray = array();
                    $newArray['OK']['ORE'] = date("H:i:s");
                    $newArray['OK']['DATA'] = date('Ymd');
                    $newArray['OK']['AGENZIA'] = $this->request['agenzia'];
                    $newArray['OK']['RESPONSE'] = $ret;
                    $newArray['OK']['UTENTE'] = frontOfficeApp::$cmsHost->getUserName();
                    $newArray['OK']['HASH'] = $hash;

                    $arrayNote['INVIOAGENZIA'][] = $newArray;

                    /*
                     * Se response è andato a buon fine aggiorno stato e sequenza
                     */
                    $Proric_rec['RICSTA'] = '81';
                    $Proric_rec['RICDAT'] = date("Ymd");
                    $Proric_rec['RICTIM'] = date("H:i:s");
                    $sequenza = $Proric_rec['RICSEQ'];
                    if (strpos($Proric_rec['RICSEQ'], "." . $dati['seq'] . ".") === false) {
                        $Proric_rec['RICSEQ'] = $Proric_rec['RICSEQ'] . "." . $dati['seq'] . ".";
                    }

                    /*
                     * Aggiorno PRORIC
                     */
                    try {
                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $Proric_rec);
                    } catch (Exception $e) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento su PRORIC della pratican. " . $Proric_rec['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }
                } else {

                    $newArray = array();
                    $newArray['KO']['ORE'] = date("H:i:s");
                    $newArray['KO']['DATA'] = date('Ymd');
                    $newArray['KO']['RESPONSE'] = $ret;
                    $newArray['KO']['UTENTE'] = frontOfficeApp::$cmsHost->getUserName();
                    $newArray['KO']['AGENZIA'] = $this->request['agenzia'];

                    $arrayNote['INVIOAGENZIA'][] = $newArray;
                }

                /*
                 * Aggiorno RICITE
                 */
                $dati['Ricite_rec']['RICNOT'] = serialize($arrayNote);
                try {
                    $nRows = ItaDB::DBUpdate($this->PRAM_DB, "RICITE", 'ROWID', $dati['Ricite_rec']);
                } catch (Exception $e) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento NOTE su RICITE della pratican. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                    return output::$html_out;
                }

                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }
                break;

            case 'invioMail':
                $html = new html();
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Blocchi per evitare doppio inoltro
                 */
                if ($dati['Proric_rec']['RICSTA'] == 'IM') {
                    $html->addMsgInfo('Attenzione', 'Richiesta già in fase di invio. Operazione non eseguibile. Attendere.');
                    output::$html_out .= $html->getHtml();
                    break;
                }
                if ($dati['Proric_rec']['RICSTA'] == '01') {
                    $html->addMsgInfo('Attenzione', 'La Richiesta Risulta Inoltrata.');
                    output::$html_out .= $html->getHtml();
                    break;
                }

                require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibOrario.class.php');
                $praLibOrario = new praLibOrario();
                if (!$praLibOrario->verificaAperturaSportello($this->PRAM_DB, $dati['Proric_rec']['RICTSP'])) {
                    $html->addMsgInfo('Attenzione', 'Sportello online chiuso, inoltro non attivo.<br />Effettuare l\'inoltro gli orari prestabiliti');
                    output::$html_out .= $html->getHtml();
                    break;
                }

                /*
                 * Valuto le Espressioni di RICCONTROLLI se ci sono
                 */
                if ($dati['Riccontrolli_tab']) {
                    $praVar = new praVars();
                    $praVar->setPRAM_DB($this->PRAM_DB);
                    $praVar->setGAFIERE_DB($this->GAFIERE_DB);
                    $praVar->setDati($dati);
                    $praVar->loadVariabiliRichiesta();
                    $msg = $this->praLib->CheckValiditaPasso($dati['Riccontrolli_tab'], $praVar->getVariabiliRichiesta()->getAlldataPlain("", "."));
                    if ($msg) {
                        if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }
                        output::addAlert($msg, 'Errore', 'error');
                        break;
                    }
                }

                /*
                 * Controllo validita posto fiera scelto
                 */
                if ($dati['ricdag_posto_fiera']['RICDAT']) {
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    if (!$praLibGfm->CheckPostoLibero($dati)) {
                        output::addAlert(sprintf('Il posteggio n. %s risulta già occupato.<br />Si prega di scegliere un altro posteggio.', $dati['ricdag_posto_fiera']['RICDAT']), 'Errore', 'error');
                        break;
                    }
                }

                /*
                 * LANCIO CUSTOM CLASS
                 */

                if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_INOLTRA_RICHIESTA, $dati)) {
                    return output::$html_out;
                }

                /*
                 * Leggo i parametri del blocco mail
                 */
                $arrayParamBloccoMail = $this->praLib->GetParametriBloccoMail($this->PRAM_DB);

                /*
                 * Svuoto gli allegati del passo Cartella e rinfresco $dati.
                 */
                $praLibAllegati = praLibAllegati::getInstance($this->praLib);
                $praLibAllegati->cancellaAllegatiCartella($dati);
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq'])) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Se Attivata effettuo la Protocollazione remota
                 */
                $protocolloOttenuto = false;

                require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibProtocolla.class.php');

                $praLibProtocolla = new praLibProtocolla();

                $differita = $praLibProtocolla->checkProtocollazioneDifferita($dati);

                if (!$differita) {
                    if ($praLibProtocolla->checkRichestaDaProtocollare($dati)) {
                        /*
                         * LANCIO CUSTOM CLASS
                         */

                        if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_PROTOCOLLAZIONE_RICHIESTA, $dati)) {
                            return output::$html_out;
                        }

                        $protocollaResult = $praLibProtocolla->protocollaRichiesta($dati);

                        switch ($protocollaResult['RESULT']) {
                            case praLibProtocolla::RESULT_PROTOCOLLA_WARNING:
                                $this->praErr->parseError(__FILE__, 'E0055', $protocollaResult['ERRORE'], __CLASS__, "", false);
                                break;

                            case praLibProtocolla::RESULT_PROTOCOLLA_ERROR:
                                if ($protocollaResult['ERRORE_MESSAGGIO']) {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0055', $protocollaResult['ERRORE'], __CLASS__, $protocollaResult['ERRORE_MESSAGGIO']);
                                } else {
                                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0055', $protocollaResult['ERRORE'], __CLASS__);
                                }

                                return output::$html_out;
                        }

                        $protocolloOttenuto = $protocollaResult['PROTOCOLLATO'];

                        /*
                         * Ricarico dati dopo protocollazione
                         */

                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']);
                        if (!$dati) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }


                        if (!$this->BloccaRichiesta($dati)) {
                            return output::$html_out = $this->praErr->parseError(__FILE__, 'E0055', "Errore nel finalizzare la richiesta n. " . $dati['Proric_rec']['RICNUM'] . " dopo la protocollazione", __CLASS__, "", false);
                        }

                        /*
                         * Ricarico i dati dopo blocco pratica
                         */

                        $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']);
                        if (!$dati) {
                            return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                        }

                        /*
                         * LANCIO CUSTOM CLASS
                         */

                        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_PROTOCOLLAZIONE_RICHIESTA, $dati)) {
                            return output::$html_out;
                        }
                    }
                }

                /*
                 * Carico i testi parametrici per il corpo delle mail decodificando le variabili dizionario
                 */
                $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);
                if (!$arrayDatiMail) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0048', "Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato", __CLASS__);
                    return output::$html_out;
                }
                $arrayDatiMail['errStringProt'] = $protocollaResult['RICHIESTA']['errString'];
                $arrayDatiMail['strNoProt'] = $protocollaResult['RICHIESTA']['strNoProt'];
                $arrayDatiMail['strNoMarcati'] = $protocollaResult['RICHIESTA']['strNoMarcati'];

                /*
                 * SALVO IL BODY DELLA MAIL RICHIEDENTE GIA COMPILATO PER USARLO COME INFORMAZIONE QUANDO
                 * USO LA FUNZIONE CONTROLLA RICHIESTYA DA BACK OFFICE SENZA MAIL.
                 */
                $txtBody = $dati['CartellaAllegati'] . "/body.txt";
                $File = fopen($txtBody, "w+");
                if (!file_exists($txtBody)) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0049', "File " . $dati['CartellaAllegati'] . "/body.txt non trovato", __CLASS__, "", false);
                } else {
                    if ($dati['Proric_rec']['RICRPA']) {
                        fwrite($File, $arrayDatiMail['bodyIntResp']);
                    } elseif ($dati['Proric_rec']['PROPAK']) {
                        fwrite($File, $arrayDatiMail['bodyRespParere']);
                    } else {
                        fwrite($File, $arrayDatiMail['bodyResponsabile']);
                    }
                    fclose($File);
                }

                $modo = "";
                if ($dati['Ricite_rec']['ITEZIP'] == 1) {
                    $modo = "RICHIESTA-INFOCAMERE";
                } else if ($dati['Proric_rec']['RICRPA']) {
                    $modo = "RICHIESTA-INTEGRAZIONE";
                } else if ($dati['Ricite_rec']['ITEIRE'] == 1) {
                    $modo = "RICHIESTA-ONLINE";
                }
                if ($dati['Proric_rec']['PROPAK']) {
                    $modo = "RICHIESTA-PARERE";
                }

                /*
                 * Scrivo i file XMLINFO delle richieste accorpate
                 * e li copio nella cartella della richiesta padre
                 */
                if (!$this->praLib->scriviXmlAccorpate($dati, $modo, $dati['CartellaAllegati'])) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0056', $this->praLib->getErrMessage(), __CLASS__);
                    return output::$html_out;
                }

                /*
                 * Ricarico i dati dopo blocco pratica
                 */
                $dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Scrivo il file XMLINFO
                 */
                if (!$this->praLib->CreaXMLINFO($modo, $dati)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Creazione file XMLINFO fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                    return output::$html_out;
                }

                /*
                 * LANCIO CUSTOM CLASS
                 */

                if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_INOLTRA_RICHIESTA, $dati)) {
                    return output::$html_out;
                }

                /*
                 * Scrivo XML Richiesta Dati Cityware
                 */
                $anapar_rec = $this->praLib->GetAnapar("BLOCK_INOLTRO_CW", "parkey", $this->PRAM_DB, false);
                if ($anapar_rec['PARVAL'] == "Si") {
                    $xmlDati = $this->praLib->GetXMLRichiestaDati($dati);
                    $Nome_file = $dati['CartellaAllegati'] . "/XMLDATI.xml";
                    $File = fopen($Nome_file, "w+");
                    if (!file_exists($Nome_file)) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Creazione file XMLDATI fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return output::$html_out;
                    } else {
                        if (!fwrite($File, $xmlDati)) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Scrittura file XMLDATI fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                            fclose($File);
                            return output::$html_out;
                        }
                        fclose($File);
                    }
                    /*
                     * Nuovo controllo
                     */
                    $Nome_file_base64 = $dati['CartellaAllegati'] . "/XMLDATI_base64.txt";
                    $base64_stream = base64_encode(file_get_contents($Nome_file));
                    if ($base64_stream === false) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Preparazione base64 xml dati fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }
                    file_put_contents($Nome_file_base64, $base64_stream);
                    $retRicezione = $this->praLib->setRicezionePraticaCityware(base64_encode(file_get_contents($Nome_file)), $dati['Proric_rec']['RICNUM']);

                    /*
                     * Nuovo Salvataggio ricezione
                     */
                    $Nome_file_esitocw = $dati['CartellaAllegati'] . "/ESITO__RicezionePraticaCityware.xml";
                    file_put_contents($Nome_file_esitocw, print_r($retRicezione, true));

                    if ($retRicezione === false) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Trasmissione dati a back-office Cityware fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }
                    if ($retRicezione['EXITCODE'][0]['@textNode'] != "S") {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Trasmissione dati a back-office Cityware fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return output::$html_out;
                    }
                    /*
                     * Inseriti i dati su servizi sociali CW come se fosse protocollato
                     */
                    $protocolloOttenuto = true;
                }

                //
                // MAIL AL RESPONSABILE
                //
                if ($dati['Ricite_rec']['ITEMRE'] == 0) {
                    if ($arrayParamBloccoMail['bloccaMailResp'] == null || $arrayParamBloccoMail['bloccaMailResp'] == "No") {
                        $TotaleAllegati = $this->praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM']);
                        if ($arrayParamBloccoMail['bloccaInvioInfo'] == "Si") {
                            $TotaleAllegati = $this->RemoveFileInfo($TotaleAllegati);
                        }
                        $ErrorMail = $this->praLib->InvioMailResponsabile($dati, $TotaleAllegati, $this->PRAM_DB, $arrayDatiMail, $modo);
                        if ($protocolloOttenuto == false) {
                            if ($ErrorMail) {
                                $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al resposansabile comunale.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                                output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                                return output::$html_out;
                            }
                        }
                    }
                }

                /*
                 * MAIL AL RICHIEDENTE
                 */
                if ($dati['Ricite_rec']['ITEMRI'] == 0) {
                    if ($arrayParamBloccoMail['bloccaMailRich'] == null || $arrayParamBloccoMail['bloccaMailRich'] == "No") {
                        $mailRich = $this->praLib->GetMailRichiedente($modo, $dati['Ricdag_tab_totali']);
                        if ($modo == "RICHIESTA-PARERE") {
                            $mailRich = $this->praLib->GetValueDatoAggiuntivo($dati['Ricdag_tab_totali'], "Pec_EnteTerzo");
                        }
                        $ErrorMail = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, $modo);
                    }
                }
                $this->runCallback($dati, "invioMail");

                /*
                 * Blocco la Richiesta
                 */
                if (!$this->BloccaRichiesta($dati, $differita)) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0055', "Errore nel finalizzare la richiesta n. " . $dati['Proric_rec']['RICNUM'], __CLASS__, "", false);
                }

                $eqDesc = sprintf('Inoltro richiesta %s', $dati['Proric_rec']['RICNUM']);
                eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));

                /*
                 * Autoacquisizione richiesta
                 */
                $parametriAcquisizione = $this->praLib->GetParametriAcquisizioneAutomatica($this->PRAM_DB);
                if ($parametriAcquisizione['FLAG'] == 'Si') {
                    /*
                     * In caso di errore, proseguo
                     */
                    $this->praLib->acquisizioneAutomaticaRichiesta($dati['Proric_rec']['RICNUM'], $parametriAcquisizione);
                }

                /*
                 * Questa parte va eliminata quando getista dall'evento getEsitoInoltro
                 */
//                if ($dati["Proric_rec"]['RICRPA']) {
//                    if ($arrayDatiMail['bodyIntRich'] == "" || $arrayDatiMail['bodyIntRich'] == "<span style=\"font-size: small;\"></span>") {
//                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
//                    } else {
//                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyIntRich'], "body.html", "praGestPassi");
//                    }
//                } elseif ($dati["Proric_rec"]['PROPAK']) {
//                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichParere'], "body.html", "praGestPassi");
//                } else {
//                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
//                }
//                return output::$html_out .= $htmlOutput;
//                break;
                /*
                 * Fine Vecchia modalita
                 */

                /*
                 * Nuova modalita redirect per evento getEsitoInoltro N.B. Anche su sueMup
                 * 
                 * RICORDA CRIPTARE RICNUM
                 */

                $href = ItaUrlUtil::GetPageUrl(array('event' => 'getEsitoInoltro', 'ricnum' => $dati['Proric_rec']['RICNUM']));
                wp_safe_redirect($href); //standardizzare su cmsHostWp
                exit();

            case "getEsitoInoltro":
                if (!$dati = $this->praLibDati->prendiDati($this->request['ricnum'])) {
                    output::$html_out = "Attenzione: richiesta non accessibile.";
                    return output::$html_out;
                }
                if ($dati['Proric_rec']['RICSTA'] != "01" && $dati['Proric_rec']['RICSTA'] != "91") {
                    output::$html_out = "Attenzione: Richiesta non inoltrata.";
                    return output::$html_out;
                }

                $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);
                if (!$arrayDatiMail) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0048', "Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato", __CLASS__);
                    return output::$html_out;
                }
                if ($dati["Proric_rec"]['RICRPA']) {
                    if ($arrayDatiMail['bodyIntRich'] == "" || $arrayDatiMail['bodyIntRich'] == "<span style=\"font-size: small;\"></span>") {
                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
                    } else {
                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyIntRich'], "body.html", "praGestPassi");
                    }
                } elseif ($dati["Proric_rec"]['PROPAK']) {
                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichParere'], "body.html", "praGestPassi");
                } else {
                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
                }
                return output::$html_out .= $htmlOutput;
                break;

            case 'accorpaRichiesta':
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if (!$this->request['accorpa'] && !$this->request['scollega']) {
                    break;
                }

                if ($this->request['scollega']) {
                    $Proric_scollega = $this->praLib->getProric($this->request['scollega'], 'codice', $this->PRAM_DB);

                    if ($Proric_scollega['RICRUN'] != $dati['Proric_rec']['RICNUM']) {
                        break;
                    }

                    if (!$this->praLib->scollegaDaPraticaUnica($this->PRAM_DB, $this->request['scollega'])) {
                        return output::$html_out;
                    }
                } else {
                    if (!$this->praLib->accorpaAPraticaUnica($this->PRAM_DB, $this->request['accorpa'], $dati['Proric_rec']['RICNUM'])) {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0084', $this->praLib->getErrMessage(), __CLASS__);
                    }
                }

                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                $returnURI = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'seq' => $this->request['seq'], 'ricnum' => $this->request['ricnum']));
                frontOfficeLib::redirect($returnURI);
                break;

            case 'accorpaAutocertificazione':
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                if ($this->request['elimina']) {
                    $this->praLib->CancellaUpload($this->PRAM_DB, $dati, $dati['Ricite_rec'], $this->request['elimina']);

                    $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                    if (!$dati) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }
                } else {
                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_PRE_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                            return output::$html_out;

                        case 2:
                            break 2;
                    }

                    $dati['Ricite_rec']['ITEMLT'] = 1;
                    $this->request['QualificaAllegato'] = array('AUTOCERTIFICAZIONE_ACCORPATA' => true);

                    $caricaAllegatoFilename = $_FILES['ita_upload']['name'];
                    $caricaAllegatoFilepath = $_FILES['ita_upload']['tmp_name'];
                    $caricaAllegatoFileerror = $_FILES['ita_upload']['error'];
                    $risultatoUpload = $this->caricaAllegato($dati, $caricaAllegatoFilename, $caricaAllegatoFilepath, $caricaAllegatoFileerror);
                    if (!$risultatoUpload && $this->praErr->getMessaggio()) {
                        return output::$html_out;
                    }

                    $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                    if (!$dati) {
                        return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                    }

                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_POST_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                            return output::$html_out;

                        case 2:
                            break 2;
                    }
                }
                break;

            case 'lanciaCreaXMLPeople':
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);

                if (strtolower(frontOfficeApp::$cmsHost->getUserName()) !== 'admin') {
                    break;
                }

                if (!$this->callCustomClass($this->request['azione'], $dati)) {
                    return output::$html_out;
                }

                output::addAlert('Generazione XML avvenuta con successo.', '', 'success');
                break;
            case 'lanciaCreaXMLINFO':

                /*
                 * Controllo utente ADMIN
                 */
                if (strtolower(frontOfficeApp::$cmsHost->getUserName()) !== 'admin') {
                    break;
                }

                /*
                 * Lettura dati
                 */
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Selezione modo
                 */
                $modo = "";
                if ($dati['Ricite_rec']['ITEZIP'] == 1) {
                    $modo = "RICHIESTA-INFOCAMERE";
                } else if ($dati['Proric_rec']['RICRPA']) {
                    $modo = "RICHIESTA-INTEGRAZIONE";
                } else if ($dati['Ricite_rec']['ITEIRE'] == 1) {
                    $modo = "RICHIESTA-ONLINE";
                }
                if ($dati['Proric_rec']['PROPAK']) {
                    $modo = "RICHIESTA-PARERE";
                }

                /*
                 * Scrivo il file XMLINFO
                 */
                if (!$this->praLib->CreaXMLINFO($modo, $dati)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Creazione file XMLINFO fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                    return output::$html_out;
                }

                output::addAlert('Generazione XMLINFO avvenuta con successo.', '', 'success');
                break;
            case 'reinvioMail':
                /*
                 * Controllo utente ADMIN
                 */
                if (strtolower(frontOfficeApp::$cmsHost->getUserName()) !== 'admin') {
                    break;
                }

                /*
                 * Lettura dati
                 */
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                /*
                 * Selezione modo
                 */
                $modo = "";
                if ($dati['Ricite_rec']['ITEZIP'] == 1) {
                    $modo = "RICHIESTA-INFOCAMERE";
                } else if ($dati['Proric_rec']['RICRPA']) {
                    $modo = "RICHIESTA-INTEGRAZIONE";
                } else if ($dati['Ricite_rec']['ITEIRE'] == 1) {
                    $modo = "RICHIESTA-ONLINE";
                }
                if ($dati['Proric_rec']['PROPAK']) {
                    $modo = "RICHIESTA-PARERE";
                }

                /*
                 * Leggo i parametri del blocco mail
                 */
                $arrayParamBloccoMail = $this->praLib->GetParametriBloccoMail($this->PRAM_DB);

                $TotaleAllegati = $this->praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM']);
                $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);
                if ($arrayParamBloccoMail['bloccaInvioInfo'] == "Si") {
                    $TotaleAllegati = $this->RemoveFileInfo($TotaleAllegati);
                }
                //
                if ($dati['Ricite_rec']['ITEMRE'] == 0) {
                    if ($arrayParamBloccoMail['bloccaMailResp'] == null || $arrayParamBloccoMail['bloccaMailResp'] == "No") {
                        $ErrorMail = $this->praLib->InvioMailResponsabile($dati, $TotaleAllegati, $this->PRAM_DB, $arrayDatiMail, $modo);
                        if ($ErrorMail) {
                            $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al resposansabile comunale.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                            return output::$html_out;
                        }
                    }
                }

                if ($dati['Ricite_rec']['ITEMRI'] == 0) {
                    if ($arrayParamBloccoMail['bloccaMailRich'] == null || $arrayParamBloccoMail['bloccaMailRich'] == "No") {
                        $mailRich = $this->praLib->GetMailRichiedente($modo, $dati['Ricdag_tab_totali']);
                        $ErrorMailRich = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, $modo, $TotaleAllegati);
                        if ($ErrorMailRich != 1) {
                            $msgErrRich = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al richiedente.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail richiedente pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrRich, true);
                            return output::$html_out;
                        }
                    }
                }

                output::addAlert('Reinvio Mail avvenuta con successo.', '', 'success');
                break;
            case 'creaBodyFile':

                /*
                 * Controllo utente ADMIN
                 */
                if (strtolower(frontOfficeApp::$cmsHost->getUserName()) !== 'admin') {
                    break;
                }

                /*
                 * Lettura dati
                 */
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
                }

                $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);

                /*
                 * SALVO IL body.txt DELLA MAIL RICHIEDENTE GIA COMPILATO
                 */
                $txtBody = $dati['CartellaAllegati'] . "/body.txt";
                $File = fopen($txtBody, "w+");
                if (!file_exists($txtBody)) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0049', "File " . $dati['CartellaAllegati'] . "/body.txt non trovato", __CLASS__, "", false);
                } else {
                    if ($dati['Proric_rec']['RICRPA']) {
                        fwrite($File, $arrayDatiMail['bodyIntResp']);
                    } elseif ($dati['Proric_rec']['PROPAK']) {
                        fwrite($File, $arrayDatiMail['bodyRespParere']);
                    } else {
                        fwrite($File, $arrayDatiMail['bodyResponsabile']);
                    }
                    fclose($File);
                }

                /*
                 * Creao il body.html
                 */
                if ($dati["Proric_rec"]['RICRPA']) {
                    if ($arrayDatiMail['bodyIntRich'] == "" || $arrayDatiMail['bodyIntRich'] == "<span style=\"font-size: small;\"></span>") {
                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
                    } else {
                        $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyIntRich'], "body.html", "praGestPassi");
                    }
                } elseif ($dati["Proric_rec"]['PROPAK']) {
                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichParere'], "body.html", "praGestPassi");
                } else {
                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyRichiedente'], "body.html", "praGestPassi");
                }

                output::addAlert('Creazione File body.txt e body.html avvenuto con successo.', '', 'success');
                break;

            case 'tablePager':
                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTablePager.class.php';
                $praLibTablePager = new praLibTablePager($this->praLib);
                if (!$praLibTablePager->parseRequest($this->request)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0082', 'Errore tablePager: ' . $praLibTablePager->getErrMessage(), __CLASS__);
                    return output::$html_out;
                }
                break;

            case 'fileUpload':
                $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                if (!$dati) {
                    break;
                }

                if ($this->request['queue_index'] == '0') {
                    $dati['Ricite_rec']['RICERM'] = '';
                    ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
                }

                require_once ITA_BASE_PATH . '/lib/itaPHPCore/itaPlUpload.class.php';
                $itaPlUpload = new itaPlUpload();
                $resultUpload = $itaPlUpload->handleUpload($dati['CartellaTemporary']);
                $resultUpload['redirect'] = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'seq' => $this->request['seq'], 'ricnum' => $this->request['ricnum']));

                if ($resultUpload['status'] == 'complete') {
                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_PRE_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                        case 2:
                            $resultUpload['response'] = 'error';
                            $resultUpload['error']['code'] = '105';
                            $resultUpload['error']['message'] = $this->callCustomClassLastError;
                            output::$ajax_out = $resultUpload;
                            output::ajaxSendResponse();
                            break;
                    }

                    /*
                     * File caricato, eseguo il salvataggio sul passo
                     */

                    $resultCaricaAllegato = $this->caricaAllegatoAsync($dati, $resultUpload['filename'], $resultUpload['filepath']);

                    $dati = $this->praLibDati->prendiDati($this->request['ricnum'], $this->request['seq']);
                    if (!$dati) {
                        break;
                    }

                    /*
                     * LANCIO CUSTOM CLASS PARAMETRICO
                     */
                    switch ($this->callCustomClass(praLibCustomClass::AZIONE_POST_UPLOAD_ALLEGATO, $dati, true)) {
                        case 0:
                        case 2:
                            $resultUpload['response'] = 'error';
                            $resultUpload['error']['code'] = '106';
                            $resultUpload['error']['message'] = $this->callCustomClassLastError;
                            output::$ajax_out = $resultUpload;
                            output::ajaxSendResponse();
                            break;
                    }

                    if (!$resultCaricaAllegato) {
                        $resultUpload['response'] = 'error';
                        $resultUpload['error']['code'] = '104';
                        output::$ajax_out = $resultUpload;
                        output::ajaxSendResponse();
                        break;
                    }

                    $sequenza = $dati['Proric_rec']['RICSEQ'];
                    if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false) {
                        $dati['Proric_rec']['RICSEQ'] = $dati['Proric_rec']['RICSEQ'] . "." . $dati['seq'] . ".";
                        ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                    }
                }

                output::$ajax_out = $resultUpload;
                output::ajaxSendResponse();
                break;

            default:
                break;
        }

        output::$html_out .= $this->disegnaPagina($dati);
        return output::$html_out;
    }

    public function CheckFiereVuote($Anafiere_tab, $raccolta) {
        $arrayFiereVuote = array();
        foreach ($Anafiere_tab as $Anafiere_rec) {
            foreach ($raccolta['DENOM_FIERA'] as $rowidFiera => $valore) {
                if ($rowidFiera == $Anafiere_rec['ROWID']) {
                    if ($valore == 0) {
                        $arrayFiereVuote[] = $raccolta['DENOM_FIERA'][$rowidFiera];
                    }
                }
            }
        }
        if (count($arrayFiereVuote) == count($Anafiere_tab)) {
            return false;
        }
        return true;
    }

    public function RichiestaInFaseDiInvio($dati, $blocca) {
        /*
         * Blocco la Richiesta
         */
        if ($blocca == true) {
            if ($dati["Proric_rec"]['RICSTA'] == '99') {
                $dati["Proric_rec"]['RICSTA'] = 'IM';
            } else {
                return true;
            }
        } else if ($blocca == false) {
            $dati["Proric_rec"]['RICSTA'] = '99';
        }
        try {
            $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $dati["Proric_rec"]);
        } catch (Exception $e) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento su PRORIC della pratican. " . $dati["Proric_rec"]['RICNUM'], __CLASS__);
            return false;
        }

        return true;
    }

    public function BloccaRichiesta($dati, $differita = false) {
        /*
         * Blocco la Richiesta
         */
        if ($dati["Proric_rec"]['RICSTA'] == '99') {
            $dati["Proric_rec"]['RICSTA'] = '01';
            $dati["Proric_rec"]['RICDAT'] = date("Ymd");
            $dati["Proric_rec"]['RICTIM'] = date("H:i:s");
            if ($differita == true) {
                $dati["Proric_rec"]['RICDATARPROT'] = date("Ymd");
                $dati["Proric_rec"]['RICORARPROT'] = date("H:i:s");
            }
            if (strpos($dati["Proric_rec"]['RICSEQ'], "." . $dati['seq'] . ".") === false) {
                $dati["Proric_rec"]['RICSEQ'] = $dati["Proric_rec"]['RICSEQ'] . "." . $dati['seq'] . ".";
            }
            try {
                $nRows = ItaDB::DBUpdate($this->PRAM_DB, "PRORIC", 'ROWID', $dati["Proric_rec"]);
            } catch (Exception $e) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0053', $e->getMessage() . " Errore aggiornamento su PRORIC della pratica n. " . $dati["Proric_rec"]['RICNUM'], __CLASS__);
                return false;
            }
        }
        return true;
    }

    public function disegnaPagina($dati) {
        /*
         * Check funzioni di passo per template speciali.
         */
        switch ($dati['Praclt_rec']['CLTOPEFO']) {
            case praLibStandardExit::FUN_FO_PASSO_CARTELLA:
                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUploadCartella.class.php';
                $praTemplateUploadCartella = new praTemplateUploadCartella();
                return $praTemplateUploadCartella->GetPagina($dati, array('PRAM_DB' => $this->PRAM_DB, 'TIPO' => 'UPLOAD', 'CLASS' => 'praGestPassi'));
        }

        if ($dati['Ricite_rec']['RICSHA2SOST'] != '') {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUploadIntegrazione.class.php';
            $praTemplateUploadIntegrazione = new praTemplateUploadIntegrazione();
            return $praTemplateUploadIntegrazione->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi", "TIPO" => "UPLOADSUE", "CLASS" => "praGestPassi", "ERRUPL" => $this->errUpload));
        } elseif ($dati['Ricite_rec']['ITEMLT'] != 0) {
            if ($dati['Ricite_rec']['ITEQALLE'] == 1) {
                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUploadQ.class.php';
                $praTemplateUploadQ = new praTemplateUploadQ();
                return $praTemplateUploadQ->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi", "TIPO" => "UPLOADSUE", "CLASS" => "sueGestPassi", "ERRUPL" => $this->errUpload));
            } else {
                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateMultiUpload.class.php';
                $praTemplateMultiUpload = new praTemplateMultiUpload();
                return $praTemplateMultiUpload->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "TIPO" => "UPLOAD", "CLASS" => "sueGestPassi", "ERRUPL" => $this->errUpload));
            }
        } elseif ($dati['Ricite_rec']['ITEUPL'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateUpload.class.php';
            $praTemplateUpload = new praTemplateUpload();
            return $praTemplateUpload->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "TIPO" => "UPLOAD", "CLASS" => "sueGestPassi", "ERRUPL" => $this->errUpload));
        } elseif ($dati['Ricite_rec']['ITEIRE'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateInviaMail.class.php';
            $praTemplateInviaMail = new praTemplateInviaMail();
            return $praTemplateInviaMail->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITEZIP'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateInviaInfocamere.class.php';
            $praTemplateInviaInfocamere = new praTemplateInviaInfocamere();
            return $praTemplateInviaInfocamere->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITEDOW'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateDownload.class.php';
            $praTemplateDownload = new praTemplateDownload();
            return $praTemplateDownload->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITEDRR'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateRapporto.class.php';
            $praTemplateRapporto = new praTemplateRapporto();
            return $praTemplateRapporto->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITERICUNI'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateRapportoUnico.class.php';
            $praTemplateRapportoUnico = new praTemplateRapportoUnico();
            return $praTemplateRapportoUnico->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi", 'procedimenti_page' => $this->config['procedimenti_page']));
        } elseif ($dati['Ricite_rec']['ITEDAT'] != 0) {
            if ($dati['Ricite_rec']['ITERDM'] != 0) {
                if ($dati['Ricite_rec']['ITECUSTOMTML']) {
                    $customTemplate = $dati['Ricite_rec']['ITECUSTOMTML'];
                    require_once ITA_PRATICHE_PATH . "/PRATICHE_italsoft/$customTemplate.class.php";
                    $praCustomTemplate = new $customTemplate();
                    return $praCustomTemplate->GetPagina($dati, $this->praLib, array("PRAM_DB" => $this->PRAM_DB, "GAFIERE_DB" => $this->GAFIERE_DB, "CLASS" => "praGestPassi",));
                } else {
                    if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_RENDER_RACCOLTA, $dati, true) || !($dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']))) {
                        return '';
                    }

                    if (!$this->callStandardExit(praLibStandardExit::AZIONE_PRE_RENDER_RACCOLTA, $dati)) {
                        return '';
                    }
                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateRaccoltaMultipla.class.php';
                    $praTemplateRaccoltaMultipla = new praTemplateRaccoltaMultipla();
                    return $praTemplateRaccoltaMultipla->GetPagina($dati, $this->praLib, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "praGestPassi",));
                }
            } else {
                if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_RENDER_RACCOLTA, $dati, true) || !($dati = $this->praLibDati->prendiDati($dati['Proric_rec']['RICNUM'], $dati['seq']))) {
                    return '';
                }

                if (!$this->callStandardExit(praLibStandardExit::AZIONE_PRE_RENDER_RACCOLTA, $dati)) {
                    return '';
                }

                require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateRaccolta.class.php';
                $praTemplateRaccolta = new praTemplateRaccolta();
                return $praTemplateRaccolta->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
            }
        } elseif ($dati['Ricite_rec']['ITEQST'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateDomanda.class.php';
            $praTemplateDomanda = new praTemplateDomanda();
            return $praTemplateDomanda->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITEDIS'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateDistinta.class.php';
            $praTemplateDistinta = new praTemplateDistinta();
            return $praTemplateDistinta->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "TIPO" => "UPLOADSUE", "CLASS" => "sueGestPassi",));
        } elseif ($dati['Ricite_rec']['ITEAGE'] != 0) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateInviaAgenzia.class.php';
            $praTemplateInviaAgenzia = new praTemplateInviaAgenzia();
            return $praTemplateInviaAgenzia->GetPagina($dati, array("PRAM_DB" => $this->PRAM_DB, "CLASS" => "praGestPassi",));
        }
    }

    function CheckFileFirmatoRaccolta($dati) {
        $Ricite_rec_ctr = array();
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $Ricite_rec) {
            if ($dati['Ricite_rec']['ITEKEY'] == $Ricite_rec['ITECTP']) {
                $Ricite_rec_ctr = $Ricite_rec;
                break;
            }
        }
        if ($Ricite_rec_ctr) {
            $ricdoc_rec = $this->praLib->GetRicdoc($Ricite_rec_ctr['ITEKEY'], "itekey", $this->PRAM_DB, false, $dati['Proric_rec']['RICNUM']);
            if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec_ctr['ITESEQ'] . chr(46)) !== false || $ricdoc_rec) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    function SincronizzaFileInserimentoAutomatico($dati) {
        foreach ($dati['Ricite_tab'] as $key => $Ricite_rec) {
            if ($Ricite_rec['ITEFILE'] == 1) {
                if ($Ricite_rec['ITEWRD']) {
                    $sw_blocca = true;
                    $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($Ricite_rec['ITESEQ'])) . $Ricite_rec['ITESEQ'];
                    $Est = strtolower(pathinfo($Ricite_rec['ITEWRD'], PATHINFO_EXTENSION));
                    $Nome_file1 = $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo . "." . $Est;
                    if (file_exists($dati['CartellaRepository'] . "/testiAssociati/filled_" . $Ricite_rec['ITEWRD'])) {
                        @unlink($dati['CartellaRepository'] . "/testiAssociati/filled_" . $Ricite_rec['ITEWRD']);
                    }

                    if (pathinfo($Ricite_rec['ITEWRD'], PATHINFO_EXTENSION) == 'pdf') {
                        if (file_exists($dati['CartellaRepository'] . "/testiAssociati/prefilled_" . $Ricite_rec['ITEWRD'])) {
                            $sw_blocca = false;
                            $Nome_file = "prefilled_" . $Ricite_rec['ITEWRD'];
                        } else {
                            $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "' AND
                                    ITEKEY = '" . $Ricite_rec['ITEKEY'] . "'", true);
                            $Nome_file = $this->FillFormPdf($Ricdag_tab, $Ricite_rec['ITEWRD'], $dati);
                        }
                    }
                    if ($Nome_file !== false) {
                        $this->praLib->cancellaRicDoc($dati, $Nome_file1, $this->PRAM_DB);
                        if (!@copy($dati['CartellaRepository'] . "/testiAssociati/$Nome_file", $dati['CartellaAllegati'] . "/" . $Nome_file1)) {
                            output::$html_out = $this->praErr->parseError(__FILE__, 'E00', "Pratica:" . $dati['Proric_rec']['RICNUM'] . ". Compilazione PDF Fallita.", __CLASS__);
                            return false;
                        }
                        $this->praLib->registraRicdoc($Ricite_rec, $Nome_file1, $Nome_file, $this->PRAM_DB, array(), false, $dati['CartellaAllegati']);
                        if ($sw_blocca) {
                            /* @var $praLibAllegati praLibAllegati */
                            $praLibAllegati = praLibAllegati::getInstance($this->praLib);
                            $praLibAllegati->BloccoFilePDF($dati['CartellaAllegati'] . "/" . $Nome_file1, $dati);
                        }
                        if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) === false) {
                            $dati['Proric_rec']['RICSEQ'] = $dati['Proric_rec']['RICSEQ'] . "." . $Ricite_rec['ITESEQ'] . ".";
                            $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $dati['Proric_rec']);
                            if ($nRows == -1) {
                                return false;
                            }
                        }
                    } else {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0060', "Pratica:" . $dati['Proric_rec']['RICNUM'] . ". Compilazione PDF Fallita.", __CLASS__);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function FillFormPdf($Ricdag_tab, $itewrd, $dati) {
        if ($Ricdag_tab && ITA_JVM_PATH != "" && file_exists(ITA_JVM_PATH)) {
            $xmlFillForm = $dati['CartellaTemporary'] . "/$itewrd.xml";
            $File = fopen($xmlFillForm, "w+");
            if (!file_exists($xmlFillForm)) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0060', "File $xmlFillForm non trovato", __CLASS__);
                return false;
            } else {
                $input = $dati['CartellaRepository'] . "/testiAssociati/$itewrd";
                $output = $dati['CartellaRepository'] . "/testiAssociati/filled_$itewrd";
                $xml = $this->praLib->CreaXmlFillPdf($dati, $Ricdag_tab, $input, $output);
                fwrite($File, $xml);
                fclose($File);
                exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJPDF.jar $xmlFillForm ", $ret);
                foreach ($ret as $value) {
                    $arrayExec = explode("|", $value);
                    if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                        $taskFillForm = true;
                        break;
                    }
                }
                if ($taskFillForm === true) {
                    $Nome_file = pathinfo($output, PATHINFO_FILENAME) . ".pdf";
                } else {
                    return false;
                }
                $this->praLib->ClearDirectory($dati['CartellaTemporary']);
            }
        } else {
            $Nome_file = $itewrd;
        }
        return $Nome_file;
    }

    function vaiPasso($direzione, $dati) {
        if (!$dati['seq']) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0020', "Sequenza passo indefinita sulla Pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
            return false;
        }
        switch ($direzione) {
            case 'avanti':
                $controlla = false;
                foreach ($dati['Navigatore']['Ricite_tab'] as $key => $Ricite_rec) {
                    if ($controlla == true) {
                        if (strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) === false) {
                            if ($Ricite_rec['ITEOBL'] == 1) {
                                break;
                            }
                        }
                    } else {
                        if ($dati['seq'] == $Ricite_rec['ITESEQ']) {
                            $controlla = true;
                        }
                    }
                }

                break;
            case 'indietro':
                foreach ($dati['Navigatore']['Ricite_tab'] as $key => $Ricite_rec) {
                    if ($dati['seq'] == $Ricite_rec['ITESEQ']) {
                        if ($key > 0) {
                            $key = $key - 1;
                        }
                        $Ricite_rec = $dati['Navigatore']['Ricite_tab'][$key];
                        break;
                    }
                }
                break;
            case 'home':
                $Ricite_rec = reset($dati['Navigatore']['Ricite_tab_new']);
                break;
            case 'end':
                $Ricite_rec = end($dati['Navigatore']['Ricite_tab_new']);
                break;
            case 'primoRosso':
                $trovato = false;
                foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $Ricite_rec) {
                    if ($Ricite_rec['ITEOBL'] == 1 && strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) === false) {
                        $Ricite_rec = $dati['Navigatore']['Ricite_tab_new'][$key];
                        $trovato = true;
                        break;
                    }
                }
                if ($trovato == false) {
                    //Se non trovo nessun passo rosso, vado all'ultimo passo
                    $Ricite_rec = array_pop($dati['Navigatore']['Ricite_tab_new']); //$dati['Navigatore']['Ricite_tab_new'][0];
                }
                break;

            case 'primoAcc':
                $trovato = false;
                foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $Ricite_rec) {
                    if ($Ricite_rec['ITERICUNI'] == 1 && strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $Ricite_rec['ITESEQ'] . chr(46)) === false) {
                        $Ricite_rec = $dati['Navigatore']['Ricite_tab_new'][$key];
                        $trovato = true;
                        break;
                    }
                }

                if ($trovato == false) {
                    // Se non trovo il passo accorpamento, vado al primo rosso
                    return $this->vaiPasso('primoRosso', $dati);
                }
                break;
        }

        if (!$Ricite_rec) {
            output::$html_out = $this->praErr->parseError(__FILE__, 'E0022', "Impossibile andare al passo selezionato della pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
            return false;
        } else {
            return $Ricite_rec["ITESEQ"];
        }
    }

    public function ControlloValiditaUtente() {
        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();

        if ($this->request['ricnum']) {
            try {
                $Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE RICFIS = '" . $datiUtente['fiscale'] . "' AND RICNUM = '" . $this->request['ricnum'] . "'", false);
            } catch (Exception $e) {
                output::$html_out = $this->praErr->parseError(__FILE__, 'E0002', $e->getMessage(), __CLASS__);
                return false;
            }
            if (!$Proric_rec) {
                $ricsoggetti_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSOGGETTI WHERE SOGRICFIS = '" . $datiUtente['fiscale'] . "' AND SOGRICNUM = '" . $this->request['ricnum'] . "'", false);
                if (!$ricsoggetti_rec) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0002', "Record Soggetto non trovato per la richiesta n. " . $this->request['ricnum'] . " e il codice fiscale " . $datiUtente['fiscale'], __CLASS__);
                    return false;
                }
                return true;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function getCartellaZIP($codfis, $cartellaAllegati) {
        $cartellaZip = $cartellaAllegati . "/" . $codfis . "-" . date('dmY') . "-" . date("Hi");
        if (is_dir($cartellaZip)) {
            $this->RemoveZipDir($cartellaZip);
        }
        if (!is_dir($cartellaZip)) {
            if (!@mkdir($cartellaZip, 0777, true)) {
                return false;
            }
        }
        return $cartellaZip;
    }

    function RemoveZipDir($dirname) {
        // Verifica necessaria
        if (!file_exists($dirname)) {
            return false;
        }
        // Cancella un semplice file
        if (is_file($dirname)) {
            return unlink($dirname);
        }
        // Loop per le dir
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Salta i punti
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Recursiva
            $this->RemoveZipDir("$dirname/$entry");
        }
        // Chiude tutto
        $dir->close();
        return rmdir($dirname);
    }

    public function GetDefaultValue($defaultValue, $Dagtip) {
        foreach ($this->config['tipiAggiuntivi'] as $keyAgg => $tipoValue) {
            $trovato = false;
            if ($tipoValue != "" && $keyAgg == $Dagtip) {
                $trovato = true;
                break;
            }
        }
        if ($trovato == true) {
            $value = $tipoValue;
        } else {
            $value = $defaultValue;
        }
        return $value;
    }

    public function CreaXmlDescrittore($dati) {
        $di = $dati['dati_infocamere'];
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>' . "\r\n";
        $xml .= '<adempimento xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\r\n";
        //
        // PRODUZIONE
        //
        $xml .= 'xsi:noNamespaceSchemaLocation="http://starweb.infocamere.it/esportaAdempimentoSuapWeb/schema/descrittoreAcquisizione.xsd">' . "\r\n";
        //
        // COLLAUDO
        //
        //$xml .= 'xsi:noNamespaceSchemaLocation="http://starwebtest.infocamere.it/esportaAdempimentoSuapWeb/schema/descrittoreAcquisizione.xsd">' . "\r\n";
        //
        // codice_catastale_destinatario
        //
        $xml .= "   <codice_catastale_destinatario>" . $di['datiSportello']['codice_catastale_destinatario'] . "</codice_catastale_destinatario>\r\n";
        //
        // oggetto_comunicazione
        //
        $xml .= "   <oggetto_comunicazione>" . $di['datiAdempimento']['oggetto_comunicazione'] . "</oggetto_comunicazione>\r\n";
        //
        // nome_adempimento
        //
        $xml .= "   <nome_adempimento>" . utf8_encode($di['datiAdempimento']['nome_adempimento']) . "</nome_adempimento>\r\n";
        //$xml .= "   <nome_adempimento><![CDATA[" . utf8_encode($di['datiAdempimento']['nome_adempimento']) . "]]></nome_adempimento>\r\n";
        //
        // nome_file_firmato
        //
        $xml .= "   <nome_file_firmato>" . $di['files']['nome_file_firmato'] . "</nome_file_firmato>\r\n";
        //
        // descrizione_pdf
        //
        $xml .= "   <descrizione_pdf>" . $di['files']['descrizione_pdf'] . "</descrizione_pdf>\r\n";
        //
        // Allegati generici
        //
        foreach ($di['files']['allegati'] as $key => $allegato) {
            $xml .= "<allegato>\r\n";
            $xml .= "    <codice_e_descrizione>" . $allegato['codice_e_descrizione'] . "</codice_e_descrizione>\r\n";
            $xml .= "    <nome_file>" . $allegato['nome_file'] . "</nome_file>\r\n";
            $xml .= "</allegato>\r\n";
        }

        //
        // cciaa destinataria
        //
        $xml .= "   <cciaa_destinataria>" . $di['datiSportello']['cciaa_destinataria'] . "</cciaa_destinataria>\r\n";
        //
        // uetente telemaco
        //
        $xml .= "   <user_telemaco>" . $di['datiAdempimento']['user_telemaco'] . "</user_telemaco>\r\n";
        //
        // Demominazione impresa
        //
        $xml .= "   <denominazione_impresa><![CDATA[" . $di['datiImpresa']['denominazione_impresa'] . "]]></denominazione_impresa>\r\n";
        $xml .= "   <tipologia_segnalazione>" . $di['datiAdempimento']['tipologia_segnalazione'] . "</tipologia_segnalazione>\r\n";
        $xml .= "   <provincia_suap>" . $di['datiImpresa']['provincia_suap'] . "</provincia_suap>\r\n";
        $xml .= "   <comune_suap>" . $di['datiImpresa']['comune_suap'] . "</comune_suap>\r\n";
        $xml .= "   <comune_destinatario>" . $di['datiSportello']['comune_destinatario'] . "</comune_destinatario>\r\n";
        $xml .= "   <indirizzo_suap>" . $di['datiImpresa']['indirizzo_suap'] . "</indirizzo_suap>\r\n";
        $xml .= "   <num_civico_suap>" . $di['datiImpresa']['num_civico_suap'] . "</num_civico_suap>\r\n";
        $xml .= "   <cod_istat_suap>" . $di['datiImpresa']['cod_istat_suap'] . "</cod_istat_suap>\r\n";
        $xml .= "   <cap_suap>" . $di['datiImpresa']['cap_suap'] . "</cap_suap>\r\n";
        $xml .= "</adempimento>";
        return $xml;
    }

    public function ControllaRapportoRiferimento($dati) {
        $arrayPdfDest = array();
        $arrayPdfSource = $this->praLib->GetFileList($dati['CartellaAllegati']);
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITERIF']) {//if ($ricite_rec['ITEPROC']) {
                $seq = str_repeat("0", $dati['seqlen'] - strlen($ricite_rec['ITESEQ'])) . $ricite_rec['ITESEQ'];
                if ($ricite_rec['ITEIDR'] != 0) {
                    $trovato = 0;
                    foreach ($arrayPdfSource as $key => $file) {
                        if (strpos($file['FILENAME'], $dati['Proric_rec']['RICNUM'] . '_C' . $seq) !== false) {
                            if (strtolower(pathinfo($file['FILEPATH'], PATHINFO_EXTENSION)) == 'pdf') {
                                $newIndice = count($arrayPdfDest);
                                $arrayPdfDest[$newIndice] = $arrayPdfSource[$key];
                                $trovato = $trovato + 1;
                            }
                        }
                    }
                    if ($trovato == 0) {
                        $newIndice = count($arrayPdfDest);
                        $arrayPdfDest[$newIndice]['rowid'] = 0;
                        $arrayPdfDest[$newIndice]['FILEPATH'] = '';
                        $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_C' . $seq . '.pdf';
                    }
                }
            }
        }
        ksort($arrayPdfDest);
        return $arrayPdfDest;
    }

    public function ControllaAllegatiZip($dati) {
        $arrayPdfDest = array();
        $arrayPdfSource = $this->praLib->GetFileList($dati['CartellaAllegati']);
        $trovatoPassoRapporto = false;
        $trovatoPassoRapportoFirmato = false;
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            $seq = str_repeat("0", $dati['seqlen'] - strlen($ricite_rec['ITESEQ'])) . $ricite_rec['ITESEQ'];

            if ($ricite_rec['ITEIFC'] == 1 || $ricite_rec['ITEIFC'] == 2) {
                if ($ricite_rec['ITEIFC'] == 2) {
                    $trovatoPassoRapportoFirmato = true;
                }
                $trovato = 0;
                foreach ($arrayPdfSource as $key => $file) {
                    $ext = strtolower(pathinfo($file['FILEPATH'], PATHINFO_EXTENSION));
                    if (strpos("|pdf|txt|jpg|p7m|m7m|tsd|", "|$ext|") !== false) {
                        if (strpos($file['FILENAME'], $dati['Proric_rec']['RICNUM'] . '_C' . $seq) !== false) {
                            $newIndice = count($arrayPdfDest);
                            $arrayPdfDest[$newIndice] = $arrayPdfSource[$key];
                            $trovato = $trovato + 1;
                        }
                    }
                }
                if ($trovato == 0) {
                    $newIndice = count($arrayPdfDest);
                    $arrayPdfDest[$newIndice]['rowid'] = 0;
                    $arrayPdfDest[$newIndice]['FILEPATH'] = '';
                    $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_C' . $seq . '.pdf';
                }
            }
            if ($ricite_rec['ITEDRR'] == 1) {
                $trovatoPassoRapporto = true;
                $trovato = 0;
                foreach ($arrayPdfSource as $key => $file) {
                    if (strtolower(pathinfo($file['FILEPATH'], PATHINFO_EXTENSION)) == 'pdf' && strpos($file['FILENAME'], "rapporto") !== false) {
                        $newIndice = count($arrayPdfDest);
                        $arrayPdfDest[$newIndice] = $arrayPdfSource[$key];
                        $trovato = $trovato + 1;
                    }
                }
                if ($trovato == 0) {
                    $newIndice = count($arrayPdfDest);
                    $arrayPdfDest[$newIndice]['rowid'] = 0;
                    $arrayPdfDest[$newIndice]['FILEPATH'] = '';
                    $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_C' . $seq . '.pdf';
                }
            }
        }
        if ($trovatoPassoRapporto == false) {
            $newIndice = count($arrayPdfDest);
            $arrayPdfDest[$newIndice]['rowid'] = 0;
            $arrayPdfDest[$newIndice]['FILEPATH'] = '';
            $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_CRapportoMancante.pdf';
        }

        if ($trovatoPassoRapportoFirmato == false) {
            $newIndice = count($arrayPdfDest);
            $arrayPdfDest[$newIndice]['rowid'] = 0;
            $arrayPdfDest[$newIndice]['FILEPATH'] = '';
            $arrayPdfDest[$newIndice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_CRapportoFirmato.p7m';
        }

        ksort($arrayPdfDest);
        return $arrayPdfDest;
    }

    public function CreateArrayFile($arrayPdf, $dati, $confronta) {
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            $seq = str_repeat("0", $dati['seqlen'] - strlen($ricite_rec['ITESEQ'])) . $ricite_rec['ITESEQ'];
            if ($ricite_rec[$confronta] != 0) {
                $trovato = 0;
                foreach ($arrayPdf as $key => $file) {
                    if (strpos($file['FILENAME'], $dati['Proric_rec']['RICNUM'] . '_C' . $seq) !== false) {
                        $trovato = 1;
                    }
                }
                $indice = $key + 1;
                if ($trovato == 0) {
                    $arrayPdf[$indice]['rowid'] = 0;
                    $arrayPdf[$indice]['FILEPATH'] = '';
                    $arrayPdf[$indice]['FILENAME'] = $dati['Proric_rec']['RICNUM'] . '_C' . $seq . '.pdf';
                }
            }
        }
        return $arrayPdf;
    }

    function RemoveFileInfo($allegati) {
        if ($allegati) {
            foreach ($allegati as $key => $allegato) {
                if (pathinfo($allegato, PATHINFO_EXTENSION) == 'info') {
                    unset($allegati[$key]);
                }

                $ricdoc_rec = $this->praLib->GetRicdoc($allegato, "codice", $this->PRAM_DB);
                if ($ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                    unset($allegati[$key]);
                }
            }
            return $allegati;
        }
    }

    public function caricaAllegato(&$dati, $filename, $filepath, $fileerror = 0) {
        /* @var $praLibAllegati praLibAllegati */
        $praLibAllegati = praLibAllegati::getInstance($this->praLib);

        $nomeFileAllegato = $praLibAllegati->caricaAllegato($dati, $filename, $filepath, $this->request, $fileerror);

        if ($nomeFileAllegato === false) {
            $this->handleCaricaAllegatoResult($dati, $praLibAllegati->getErrCode(), $praLibAllegati->getErrMessage());
            return false;
        }

        $ricite_rec = $this->praLib->CheckUploadFile($dati['Proric_rec']['RICNUM'], $nomeFileAllegato, $dati['CartellaAllegati'], $dati['PRAM_DB']);

        if (is_array($ricite_rec)) {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'seq' => $ricite_rec['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $open = "location.replace('$href')";

            $buttonConferma = "<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"italsoft-button\" type=\"button\" onclick=\"$open\">
                                   <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                   <div class=\"\" style=\"display:inline-block;\"><b>Conferma</b></div>
                                 </button>
                               </div>";

            $buttonAnnulla = "<div style=\"margin:10px;display:inline-block;\">
                                <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-close-dialog italsoft-button italsoft-button--secondary\" type=\"button\">
                                  <i class=\"icon ion-close italsoft-icon\"></i>
                                  <div class=\"\" style=\"display:inline-block;\"><b>Annulla</b></div>
                                </button>
                              </div>";

            @unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

            $html = new html();
            $html->appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Caricamento Allegato\"><br>");
            $html->appendHtml("<p style=\"margin:0 10px 0;color:red;font-size:1.2em;\"><b>Il file che si sta tentando di allegare risulta già caricato al passo:<br>" . $ricite_rec["ITEDES"] . ".<br><br>Vuoi andare al passo?</b></p>");
            $html->appendHtml("<div style=\"float:right;\">");
            $html->appendHtml($buttonConferma . $buttonAnnulla);
            $html->appendHtml("</div>");
            $html->appendHtml("</div>");
            output::$html_out .= $html->getHtml();
            return false;
        }

        if (!$praLibAllegati->registraAllegato($dati, $nomeFileAllegato, $filename, $this->request)) {
            $this->handleCaricaAllegatoResult($dati, $praLibAllegati->getErrCode(), $praLibAllegati->getErrMessage());
            return false;
        }

        return true;
    }

    public function caricaAllegatoAsync($dati, $filename, $filepath) {
        /*
         * LANCIO CUSTOM CLASS PARAMETRICO
         */

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_UPLOAD_ALLEGATO, $dati, true)) {
            return false;
        }

        /* @var $praLibAllegati praLibAllegati */
        $praLibAllegati = praLibAllegati::getInstance($this->praLib);

        $nomeFileAllegato = $praLibAllegati->caricaAllegato($dati, $filename, $filepath, $this->request);

        if ($nomeFileAllegato === false) {
            $uploadCartellaErrors = json_decode($dati['Ricite_rec']['RICERM'], true) ?: array();
            $uploadCartellaErrors[] = array('file' => $filename, 'error' => $praLibAllegati->getErrMessage());
            $dati['Ricite_rec']['RICERM'] = json_encode(frontOfficeLib::utf8_encode_recursive($uploadCartellaErrors));
            ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
            return false;
        }

        /*
         * Verifico validità estensione
         */

        $ricite_rec = $this->praLib->CheckUploadFile($dati['Proric_rec']['RICNUM'], $nomeFileAllegato, $dati['CartellaAllegati'], $dati['PRAM_DB']);
        if (is_array($ricite_rec)) {
            unlink($dati['CartellaAllegati'] . '/' . $nomeFileAllegato);

            $uploadCartellaErrors = json_decode($dati['Ricite_rec']['RICERM'], true) ?: array();
            $uploadCartellaErrors[] = array('file' => $filename, 'error' => "Il file che si sta tentando di allegare risulta già caricato al passo '{$ricite_rec["ITEDES"]}'.");
            $dati['Ricite_rec']['RICERM'] = json_encode(frontOfficeLib::utf8_encode_recursive($uploadCartellaErrors));
            ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
            return false;
        }

        if (!$praLibAllegati->registraAllegato($dati, $nomeFileAllegato, $filename, $this->request, false)) {
            $uploadCartellaErrors = json_decode($dati['Ricite_rec']['RICERM'], true) ?: array();
            $uploadCartellaErrors[] = array('file' => $filename, 'error' => $praLibAllegati->getErrMessage());
            $dati['Ricite_rec']['RICERM'] = json_encode(frontOfficeLib::utf8_encode_recursive($uploadCartellaErrors));
            ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
            return false;
        }

        /*
         * LANCIO CUSTOM CLASS PARAMETRICO
         */

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_UPLOAD_ALLEGATO, $dati, true)) {
            return false;
        }

        return true;
    }

    public function caricaAllegatoDaCartella(&$dati, $rowidAllegato) {
        /* @var $praLibAllegati praLibAllegati */
        $praLibAllegati = praLibAllegati::getInstance($this->praLib);

        $allegatoCartella = $praLibAllegati->getAllegatoCartella($dati, $rowidAllegato);

        if (!$allegatoCartella) {
            $this->handleCaricaAllegatoResult($dati, -3, "Errore recupero file allegato cartella ROWID {$this->request['allegatoCartella']}: " . $praLibAllegati->getErrMessage());
            return false;
        }

        $allegatoCartellaFilepath = $dati['CartellaAllegati'] . DIRECTORY_SEPARATOR . $allegatoCartella['DOCUPL'];
        $temporaryFilepath = $dati['CartellaTemporary'] . DIRECTORY_SEPARATOR . $allegatoCartella['DOCUPL'];

        if (!copy($allegatoCartellaFilepath, $temporaryFilepath)) {
            $this->handleCaricaAllegatoResult($dati, -3, "Errore copia file cartella upload '$allegatoCartellaFilepath' => '$temporaryFilepath'.");
            return false;
        }

        $nomeFileAllegato = $praLibAllegati->caricaAllegato($dati, $allegatoCartella['DOCNAME'], $temporaryFilepath, $this->request);

        if ($nomeFileAllegato === false) {
            $this->handleCaricaAllegatoResult($dati, $praLibAllegati->getErrCode(), $praLibAllegati->getErrMessage());
            return false;
        }

        if (!$praLibAllegati->registraAllegato($dati, $nomeFileAllegato, $allegatoCartella['DOCNAME'], $this->request)) {
            $this->handleCaricaAllegatoResult($dati, $praLibAllegati->getErrCode(), $praLibAllegati->getErrMessage());
            return false;
        }

        $this->praLib->CancellaUpload($dati['PRAM_DB'], $dati, $praLibAllegati->getPassoCartella($dati), $allegatoCartella['DOCUPL']);

        return true;
    }

    private function handleCaricaAllegatoResult($dati, $errCode, $errMessage) {
        switch ($errCode) {
            case -1:
                /*
                 *  Errore sull'allegato da legare al passo.
                 */

                $dati['Ricite_rec']['RICERF'] = 1;
                $dati['Ricite_rec']['RICERM'] = $errMessage . '<br>';
                ItaDB::DBUpdate($dati['PRAM_DB'], 'RICITE', 'ROWID', $dati['Ricite_rec']);
                break;

            case -2:
                /*
                 * Caricamento già effettuato, proseguo senza errore sul passo
                 * ma con avviso.
                 */

                output::appendHtml('<div style="display: none;" class="ita-alert" title="Caricamento Allegati">');
                output::appendHtml('<p style="padding: 5px; color: red; font-size: 1.2em;">' . $errMessage . '</p>');
                output::appendHtml('</div>');
                break;

            case -3:
                /*
                 * Errore fatale.
                 */

                output::$html_out = $this->praErr->parseError(__FILE__, 'E0080', $errMessage, __CLASS__);
                break;
        }
    }

    function runCallback($dati, $evento) {
        if (defined('ITA_CALLBACK_PATH') && file_exists(ITA_CALLBACK_PATH)) {
            require_once(ITA_CALLBACK_PATH);
            ita_suap_Callback::run($dati, $evento);
        }
    }

    private function callCustomClass($azione, $dati, $azione_passo = false) {
        $risorse = array(
            'config' => $this->config
        );

        /* @var $objAzione praLibCustomClass */
        $objAzione = praLibCustomClass::getInstance($this->praLib);
        $retAzione = $objAzione->eseguiAzione($azione, $dati, $azione_passo, $risorse);
        $this->callCustomClassLastError = $objAzione->getErrMessage();

        if ($retAzione === true) {
            return 1;
        }

        switch ($retAzione) {
            case praLibCustomClass::AZIONE_RESULT_WARNING:
                output::$html_out = $this->praErr->parseError(__FILE__, "EA-" . $azione, "[" . $dati['Proric_rec']['RICNUM'] . "] " . $objAzione->getErrMessage(), __CLASS__, "", false);
                break;

            case praLibCustomClass::AZIONE_RESULT_ERROR:
                output::$html_out = $this->praErr->parseError(__FILE__, "EA-" . $azione, "[" . $dati['Proric_rec']['RICNUM'] . "] " . $objAzione->getErrMessage(), __CLASS__);
                return 0;

            case praLibCustomClass::AZIONE_RESULT_INVALID:
                output::addAlert($objAzione->getErrMessage(), '', 'error');
                return 2;
        }

        return 1;
    }

    private function callStandardExit($azione, $dati) {
        $risorse = array(
            'config' => $this->config
        );

        /* @var $objAzione praLibStandardExit */
        $objAzione = praLibStandardExit::getInstance($this->praLib);
        $objAzione->setFrontOfficeLib($this->frontOfficeLib);
        $retAzione = $objAzione->getFunzioneTipoPasso($azione, $dati, $risorse);
        switch ($retAzione) {
            case praLibStandardExit::AZIONE_RESULT_STOP:
                return 2;
            case praLibStandardExit::AZIONE_RESULT_WARNING:
                output::$html_out = $this->praErr->parseError(__FILE__, "EA-" . $azione, "[" . $dati['Proric_rec']['RICNUM'] . "] " . $objAzione->getErrMessage(), __CLASS__, "", false);
                break;

            case praLibStandardExit::AZIONE_RESULT_ERROR:
                output::$html_out = $this->praErr->parseError(__FILE__, "EA-" . $azione, "[" . $dati['Proric_rec']['RICNUM'] . "] " . $objAzione->getErrMessage(), __CLASS__);
                return 0;

            case praLibStandardExit::AZIONE_RESULT_INVALID:
                output::addAlert("<div style=\"font-size:1.2em;font-weight:bold;\">" . $objAzione->getErrMessage() . "</div>", '', 'error');
                return 2;
            case praLibStandardExit::AZIONE_RESULT_SUCCESS:
                return 1;
        }

        return 1;
    }

    /**
     * Ritorna il percorso reale di un allegato a seconda del filename.
     * 
     * @param type $filename Nome del file allegato
     * @param type $dati Array $dati di prendiDati
     * @param type $returnFilename Se impostato, nell'array di ritorno 'filename' sarà forzato con questo valore
     * @return array Array con valori filepath e filename
     */
    private function getPercorsoAllegato($filename, $dati, $returnFilename = false) {
        if (strpos(pathinfo($filename, PATHINFO_BASENAME), 'rapporto') !== false) {
            $filepath = $dati['CartellaAllegati'] . "/" . pathinfo($filename, PATHINFO_BASENAME);
        } else if (strpos(pathinfo($filename, PATHINFO_BASENAME), 'raccolta') !== false) {
            $filepath = $dati['CartellaAllegati'] . "/" . pathinfo($filename, PATHINFO_BASENAME);
        } else if ($dati['Ricite_rec']['ITEDAT'] == 1 || $dati['Ricite_rec']['ITEIRE'] == 1 || $dati['Ricite_rec']['ITEUPL'] == 1 || $dati['Ricite_rec']['ITEMLT'] == 1 || $dati['Ricite_rec']['ITEDRR'] == 1) {
            $filepath = $dati['CartellaAllegati'] . "/" . pathinfo($filename, PATHINFO_BASENAME);
        } elseif (strpos($filename, '.SUAP.PDF') !== false) {
            $filepath = $dati['CartellaAllegati'] . "/" . $dati['Proric_rec']['CODICEPRATICASW'] . "/" . pathinfo($filename, PATHINFO_BASENAME);
        } else {
            $filepath = $dati['CartellaRepository'] . "/testiAssociati/" . pathinfo($filename, PATHINFO_BASENAME);
        }

        $realfilename = pathinfo($filepath, PATHINFO_BASENAME);

        return array(
            'filepath' => $filepath,
            /* Imposto $returnFilename se presente, $realfilename in caso contrario */
            'filename' => $returnFilename ?: $realfilename
        );
    }

}
