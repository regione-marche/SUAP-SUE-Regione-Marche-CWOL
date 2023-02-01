<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DAN.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBGDGestComunicazioni.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSendMail.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaScanner.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaSignatureFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function cwbBgdDocUd() {
    $cwbBgdDocUd = new cwbBgdDocUd();
    $cwbBgdDocUd->parseEvent();
    return;
}

class cwbBgdDocUd extends itaFrontControllerCW {

// array per gestione render e valori di default in base al formato scelto
    private $formati = array(
        1 => array('descrizione' => 'PDF', 'showEditor' => false, 'showScanner' => true, 'modificabile' => false, 'estensioneDefault' => '.pdf', 'estensioniAmmesse' => array('pdf'), 'mimetypes' => array('application/pdf')),
        2 => array('descrizione' => 'XML', 'showEditor' => true, 'showScanner' => false, 'modificabile' => true, 'estensioneDefault' => '.xml', 'estensioniAmmesse' => array('xml'), 'mimetypes' => array('application/xml')),
        3 => array('descrizione' => 'HTML', 'showEditor' => true, 'showScanner' => false, 'modificabile' => true, 'estensioneDefault' => '.html', 'estensioniAmmesse' => array('html'), 'mimetypes' => array('text/html')),
        4 => array('descrizione' => 'TXT', 'showEditor' => true, 'showScanner' => false, 'modificabile' => true, 'estensioneDefault' => '.txt', 'estensioniAmmesse' => array('txt'), 'mimetypes' => array('text/plain')),
        5 => array('descrizione' => 'Immagine (PNG/JPG)', 'showEditor' => false, 'showScanner' => true, 'modificabile' => false, 'estensioneDefault' => '.png', 'estensioniAmmesse' => array('png', 'jpeg', 'jpg'), 'mimetypes' => array('image/png', 'image/jpeg')),
        6 => array('descrizione' => 'Doc/Docx/Rtf', 'showEditor' => false, 'showScanner' => false, 'modificabile' => false, 'estensioneDefault' => '.doc', 'estensioniAmmesse' => array('doc', 'docx', 'rtf'), 'mimetypes' => array('application/msword', 'application/rtf'))
    );
    private $libGestDoc;
    private $libDB;
    private $externalParams;
    private $listaAllegatiDocAl; // lista allegati
    private $listaAllegatiCancellatiDocAl = array(); // lista allegati cancellati che erano su db (cancellazione logica mettendo disabilitato = true)
    private $documentoCaricato;
    private $idPadreDanAnagra;

    const PROVENIENZA_ANAGRAFE = 4;
    const TABELLA_BGDDOCUD = 'BGD_DOC_UD';
    const TABELLA_BGDDOCAL = 'BGD_DOC_AL';
    const DOCVIEWER = 'cwbDocViewer';
    const SENDMAIL = 'cwbBtaSendMail';

    protected function postItaFrontControllerCostruct() {
        $this->GRID_NAME = 'gridBgdDocAl';
        $this->libDB = new cwbLibDB_BGD();
        // lib per gestione salvataggio/validazione record da riusare anche in altri punti
        $this->libGestDoc = new cwbLibBGDGestComunicazioni();
        $this->libGestDoc->initData();
        $this->connettiDB();
        $this->listaAllegatiDocAl = cwbParGen::getFormSessionVar($this->nameForm, '_listaAllegatiDocAl');
        $this->listaAllegatiCancellatiDocAl = cwbParGen::getFormSessionVar($this->nameForm, '_listaAllegatiCancellatiDocAl');
        $this->documentoCaricato = cwbParGen::getFormSessionVar($this->nameForm, '_documentoCaricato');
        $this->idPadreDanAnagra = cwbParGen::getFormSessionVar($this->nameForm, '_idPadreDanAnagra');
    }

    public function __destruct() {
        parent::__destruct();
        cwbParGen::setFormSessionVar($this->nameForm, '_listaAllegatiDocAl', $this->listaAllegatiDocAl);
        cwbParGen::setFormSessionVar($this->nameForm, '_listaAllegatiCancellatiDocAl', $this->listaAllegatiCancellatiDocAl);
        cwbParGen::setFormSessionVar($this->nameForm, '_documentoCaricato', $this->documentoCaricato);
        cwbParGen::setFormSessionVar($this->nameForm, '_idPadreDanAnagra', $this->idPadreDanAnagra);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaDocUd();

                break;
            case 'dbClickRow':
                $this->modificaAllegato();

                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->dettaglioAllegato();
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->cancellaAllegato();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        cwbParGen::setFormSessionVar($this->nameForm, 'externalParams', null);
                        break;
                    case $this->nameForm . '_UPLOAD_upld':
                        $this->caricaDocumento();
                        break;
                    case $this->nameForm . '_Aggiorna_docal':
                        $this->aggiornaAllegato();
                        break;
                    case $this->nameForm . '_Aggiungi_docal':
                        $this->aggiungiAllegato();
                        break;
                    case $this->nameForm . '_Annulla_docal':
                        $this->annulla();
                        break;
                    case $this->nameForm . '_Conferma_docud':
                        $this->aggiungiUnitaDocumentaria();
                        break;
                    case $this->nameForm . '_Scanner_docal':
                        $this->scanAllegato();
                        break;
                    case $this->nameForm . '_Download_docal':
                        $this->downloadAllegato();
                        break;
                    case $this->nameForm . '_Esporta_docud':
                        $this->esportaAllegati();
                        break;
                    case $this->nameForm . '_Invia_docud':
                        $this->inviaAllegati();
                        break;
                    case $this->nameForm . '_Firma_docud':
                        $this->firmaAllegati();
                        break;
                    case $this->nameForm . '_Protocolla_docud':
                        $this->protocollaAllegati();
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $this->reloadGridDocAl();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_FORMATO':
                        $this->cambiaFormato();
                        break;
                }
                break;
            case 'onIsisCallback':
                switch ($_POST['id']) {
                    case 'isis':
                        $this->callBackScanner();
                        break;
                }
                break;
        }
    }

    private function elaboraRecord($records) {
        foreach ($records as $key => $record) {
            if ($record['TIPO_COM']) {
                $libBta = new cwbLibDB_BTA();
                $tipo = $libBta->leggiBtaTipcomChiave($record['TIPO_COM']);

                $records[$key]['DESTIPCOM_formatted'] = $tipo['DES_TIPCOM'];
            }
        }

        return $records;
    }

    // ricarica la griglia di bgddocal
    private function reloadGridDocAl() {
        try {
            $ita_grid = $this->helper->initializeTableArray($this->elaboraRecord($this->listaAllegatiDocAl));
            if (!$ita_grid->getDataPage('json')) {
                TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);
            } else {
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    // parte all'open form e carica i dati di bgddocal
    private function caricaDocAl($progcomu) {
        // metodo per attivare il file upload
        Out::activateUploader($this->nameForm . '_UPLOAD_upld_uploader');
        $this->initComboFormato();
        $this->initComboStato();
        $this->initComboTipoCom();
        $this->visDocViewer(false);
        if ($progcomu) {
            $this->listaAllegatiDocAl = $this->libDB->leggiBgdDocAl(array('PROGCOMU' => $progcomu, 'FLAG_DIS' => 0));
            foreach ($this->listaAllegatiDocAl as $key => $allegato) {
                // per ogni record creo una chiave finta che serve per selezionare il giusto record in tabella
                $this->listaAllegatiDocAl[$key][cwbLibBGDGestComunicazioni::CHIAVE_GRID_RANDOM] = $this->creaRandomGuid();
            }
        } else {
            $this->listaAllegatiDocAl = array();
        }

        $this->reloadGridDocAl();
    }

    // parte all'open form e carica i dati di bgddocud
    private function caricaDocUd() {
        if ($this->externalParams['PROGSOGG'] != null) {
            $this->idPadreDanAnagra = $this->externalParams['PROGSOGG'];

            // progcomu arriva da fuori, se bgddocud con quel progcomu esiste lo carica sennò è nuovo e viene calcolato al salva
            $progcomu = null;
            if ($this->externalParams['PROGCOMU']) {
                $progcomu = $this->externalParams['PROGCOMU'];
                $recordDocUd = $this->libDB->leggiBgdDocUd(array('PROGCOMU' => $progcomu), false);

                if (!$recordDocUd) {
                    // se c'è progcomu ma non trovo la testata, azzero $progcomu cosi non trova niente neanche su docAll
                    // (se capita il caso di testata cancellata ma allegati ancora presenti se non svuoto progcomu mi carica 
                    // gli allegati anche in assenza di testata)
                    $progcomu = null;
                }

                Out::valori($recordDocUd, $this->nameForm . '_' . self::TABELLA_BGDDOCUD);
            } else {
                TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);
                // se è una nuova unita documentaria di default imposto nome - cognome - data nascita sul campo testata
                if ($this->idPadreDanAnagra) {
                    $libDan = new cwdLibDB_DAN();
                    $recordDanAnagra = $libDan->leggiDanAnagraChiave($this->idPadreDanAnagra);
                    if ($recordDanAnagra['NOME']) {
                        $testata = $recordDanAnagra['NOME'] . ' ';
                    }
                    if ($recordDanAnagra['COGNOME']) {
                        $testata .= $recordDanAnagra['COGNOME'] . ' ';
                    }
                    if ($recordDanAnagra['GIORNO'] && $recordDanAnagra['MESE'] && $recordDanAnagra['ANNO']) {
                        $testata .= $recordDanAnagra['GIORNO'] . '/' . $recordDanAnagra['MESE'] . '/' . $recordDanAnagra['ANNO'];
                    }

                    Out::valore($this->nameForm . '_BGD_DOC_UD[ANNOTAZ]', $testata);
                }
            }

            $this->caricaDocAl($progcomu);

            $this->visDocud();
        } else {
            // TODO ERRORE?
        }
    }

    private function callBackScanner() {
        $path = $_POST['data'];

        // appoggio il path del documento caricato
        $this->documentoCaricato = $path;

        // TODO prendere il nome ed estensione passati dallo smartagent (modificare ritorno)
        $nomeFile = rand(0, 99999) . '.pdf';
        Out::valore($this->nameForm . '_UPLOAD', $nomeFile); // setta il nome del documento sulla text dell'upload
        Out::valore($this->nameForm . '_' . self::TABELLA_BGDDOCAL . '[ANNOTAZ]', $nomeFile); // setta il nome del documento sul campo annotazione come default    }
    }

    private function scanAllegato() {
        $returnData = array(
            'returnForm' => $this->nameForm,
            'returnId' => 'isis',
            'returnEvent' => 'onIsisCallback'
        );
        $forcePdf = 0;
        if ($this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['descrizione'] === 'PDF') {
            $forcePdf = 1;
        }

        $objScanner = itaScanner::getScan();
        $objScanner->scan($returnData, null, $forcePdf);

        if ($objScanner->getErrorCode() !== 0) {
            Out::msgStop("Errore", $objScanner->getErrorDescription());
        }
    }

    private function downloadAllegato() {
        $randomguid = $_POST[$this->nameForm . '_RANDOMGUID_HIDDEN'];
        $recordFound = $this->searchCurrentDocAl($randomguid);

        $corpo = $this->getBinarioDocAl($recordFound['record']);

        $formato = $_POST[$this->nameForm . '_FORMATO_HIDDEN'];

        if ($corpo) {
            $nomeFile = $recordFound['record']['NOME_FILE'];
            if (!$nomeFile) {
                $nomeFile = rand(0, 9999999) . $this->formati[$formato]['estensioneDefault'];
            }

            cwbLib::downloadDocument($nomeFile, $corpo, true);
        } else {
            Out::msgStop("Errore", "Errore reperimento binario");
        }
    }

    private function firmaAllegati() {
        Out::msgInfo("Attenzione", "Funzione non attiva");
        return;

        $docals = $this->getSelectedFiles(true, true);

        if ($docals) {
            $objSignature = itaSignatureFactory::getSignature();

            if (count($docals) === 1) {
                $path = $this->writeDocAlInTempPath($docals[0]);
                if ($path) {
                    $toSign = $path;
                }
            } else {
                if ($objSignature->isMultipleSignerAllowed()) {
                    $toSign = array();
                    foreach ($docals as $docal) {
                        $path = $this->writeDocAlInTempPath($docal);
                        if ($path) {
                            $toSign[] = $path;
                        }
                    }
                } else {
                    Out::msgInfo("Errore", "Firma multipla non consentita");
                    return null;
                }
            }

            $paramsIn = $objSignature->getParameters();
            if ($paramsIn['type'] === itaSignatureFactory::CLASSE_FIRMA_PROVIDER_PKNET) {
                // parametri specifici di pknet (uso default)
            } else if ($paramsIn['type'] === itaSignatureFactory::CLASSE_FIRMA_PROVIDER_ARUBA) {
                // parametri specifici di aruba
                $paramsIn['USER'] = '?';
                $paramsIn['PASSWORD'] = '?';
                $paramsIn['OTPPSW'] = '?';
            }

            $returnData = array(
                'returnForm' => $this->nameForm,
                'returnId' => 'pknet',
                'returnEvent' => 'onPknetSignCallback'
            );
            $objSignature->signature($toSign, $returnData, $paramsIn);
        }
    }

    private function protocollaAllegati() {
        $docals = $this->getSelectedFiles(true);

        if ($docals) {
            $clientProtocollazione = proWsClientFactory::getInstance(proWsClientHelper::CLIENT_ITALPROT);

            if (!$clientProtocollazione) {
                Out::msgStop("Errore", "Client protocollazione non configurato");
                return;
            }

            $valore = $clientProtocollazione->inserisciProtocollazionePartenza($this->getParamsProtocollazione());
            if ($valore['status'] === true) {
                Out::msgInfo("Protocollazione Eseguita", "Protocollazione eseguita con numero: " . $valore['status']);
            } else {
                Out::msgStop("Errore", "Protocollazione non eseguita");
            }
        }
    }

    private function getParamsProtocollazione() {
        $elementi['tipo'] = 'P';
        $elementi['dati']['Oggetto'] = "prova";
        $elementi['dati']['DenomComune'] = "Comune xxx";

        $elementi['dati']['Mittente']['Denominazione'] = "sdaasd";
        $elementi['dati']['Mittente']['Indirizzo'] = "sdaasdasdasd";
        $elementi['dati']['Mittente']['CAP'] = 60035;
        $elementi['dati']['Mittente']['Citta'] = "jesi";
        $elementi['dati']['Mittente']['Provincia'] = "ancona";
        $elementi['dati']['Mittente']['Email'] = "sdsda@asdaasd.it";
        $elementi['dati']['Mittente']['CF'] = "";

        $elementi['dati']['destinatari'] = array();
//        foreach ($pramitDest_tab as $pramitDest_rec) {
        $destinatario = array();
        $destinatario['Denominazione'] = "pippo";
        $destinatario['Indirizzo'] = "sadadssda";
        $destinatario['CAP'] = 60035;
        $destinatario['Citta'] = "jesi";
        $destinatario['Provincia'] = "ancona";
        $destinatario['Email'] = "sdsda@assd.it";
        $destinatario['CF'] = "cdfert45676df34f23";
        $elementi['dati']['destinatari'][] = $destinatario;
//        }

        $elementi['dati']['NumeroAntecedente'] = substr($proges_rec['GESNPR'], 4);
        $elementi['dati']['AnnoAntecedente'] = substr($proges_rec['GESNPR'], 0, 4);
        if ($proges_rec['GESMETA']) {
            $metaDati = unserialize($proges_rec['GESMETA']);
            $elementi['dati']['NumeroAntecedente'] = $metaDati['DatiProtocollazione']['proNum']['value'];
            $elementi['dati']['AnnoAntecedente'] = $metaDati['DatiProtocollazione']['Anno']['value'];
        }
        $elementi['dati']['TipoAntecedente'] = "A"; //Tipo protocollo pratica 
        $elementi['dati']['Classificazione'] = "dsasd";
        $elementi['dati']['MetaDati'] = array();
        $elementi['dati']['InCaricoA'] = "sdasdasd";
        $elementi['dati']['MittenteInterno'] = "fggg";
        $elementi['dati']['TipoDocumento'] = "dsaasd";

        $elementi['dati']['Fascicolazione']['Oggetto'] = "ssdasd";
        return $elementi;
    }

    private function writeDocAlInTempPath($docal) {
        $corpo = $this->getBinarioDocAl($docal);
        $nomefile = $docal['NOME_FILE'];
        if ($docal['MIMETYPE'] !== 'application/pdf') {
            $corpo = $this->convertToPdf($corpo);
        }

        return $this->writeInTempPath($corpo, $nomefile);
    }

    private function convertToPdf() {
        // TODO come convertire?
    }

    private function writeInTempPath($corpo, $nomeFile) {
        if (!@is_dir(itaLib::getAppsTempPath())) {
            if (!itaLib::createAppsTempPath()) {
                return false;
            }
        }
        $pathTmp = itaLib::getAppsTempPath() . '/' . rand(100, 9999) . $nomeFile;

        if (file_put_contents($pathTmp, $corpo)) {
            return $pathTmp;
        }

        return false;
    }

    private function inviaAllegati() {
        $toExport = $this->getSelectedFilesZip(true);

        if ($toExport) {
            $objSendMail = cwbLib::apriFinestra(self::SENDMAIL, $this->nameForm, 'returnFromCwbBtaSendMail', $_POST['id'], null, $this->nameFormOrig);
            $objSendMail->setDefaultOggetto($_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCUD]['ANNOTAZ']);
            $objSendMail->setDefaultCorpo($_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCUD]['ANNOTAZ']);
            $objSendMail->setAllegati(array($toExport));
            $objSendMail->parseEvent();
        }
    }

    private function esportaAllegati() {
        $toExport = $this->getSelectedFilesZip();

        if ($toExport) {
            cwbLib::downloadDocument($toExport['NOMEFILE'], $toExport['CORPO'], true);
        }
    }

    private function getSelectedFiles($soloConclusi = false, $escludiFirmati = false) {
        $toExport = array();

        foreach ($this->listaAllegatiDocAl as $docal) {
            $rowname = "jqg_" . $this->nameForm . "_" . $this->GRID_NAME . "_" . $docal['RANDOMGUID'];
            if ($_POST[$rowname] === '1') {
                // riga selezionata
                $toExport[] = $docal;
                if ($soloConclusi && $docal['STATO_DOC'] == 0) {
                    Out::msgStop("Errore", "Deselezionare i record non conclusi");
                    return null;
                }
                if ($escludiFirmati && $docal['STATO_DOC'] == 2) {
                    Out::msgStop("Errore", "Deselezionare i record gia' firmati");
                    return null;
                }
            }
        }

        if (!$toExport) {
            Out::msgInfo("Attenzione", "Nessun Record Selezionato");
            return null;
        }

        return $toExport;
    }

    private function getSelectedFilesZip($soloConclusi = false, $escludiFirmati = false) {
        $toExport = $this->getSelectedFiles($soloConclusi, $escludiFirmati);

        if ($toExport) {
            if (count($toExport) == 1) {
                $corpo = $this->getBinarioDocAl($toExport[0]);
                $nomefile = $toExport[0]['NOME_FILE'];
                return array('NOMEFILE' => $nomefile, 'CORPO' => $corpo, 'RANDOMGUID' => $this->creaRandomGuid());
            } else {
                $corpo = $this->creaZip($toExport);
                return array('NOMEFILE' => $this->creaRandomGuid() . '.zip', 'CORPO' => $corpo, 'RANDOMGUID' => $this->creaRandomGuid());
            }
        }
        return null;
    }

    public function creaZip($toZip) {
        $fileNameInsert = array();

        $zipPath = itaLib::getUploadPath() . "/" . $this->creaRandomGuid() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        foreach ($toZip as $docal) {
            $corpo = $this->getBinarioDocAl($docal);
            $nomefile = $docal['NOME_FILE'];
            if (in_array($nomefile, $fileNameInsert)) {
                // se il nome file esiste già, lo cambio sennò da errore per inserirlo nello zip
                $nomefile = rand(0, 9999999) . '.' . itaMimeTypeUtils::estraiEstensione($nomefile);
            }

            $fileNameInsert[] = $nomefile;

            $zip->addFromString($nomefile, $corpo);
        }

        $zip->close();

        return file_get_contents($zipPath);
    }

    private function cancellaAllegato() {
        // rimuovo un record dalla lista, poi la delete vera la fa al conferma
        $randomguid = $_POST['rowid'];
        $toRemove = null;
        foreach ($this->listaAllegatiDocAl as $key => $value) {
            if ($value[cwbLibBGDGestComunicazioni::CHIAVE_GRID_RANDOM] == $randomguid) {
                $toRemove = $key;
                break;
            }
        }
        if ($toRemove !== null) {
            // se c'è id salvo gli allegati su un altra lista perchè poi li devo disabilitare (solo cancellazione logica non c'è cancellazione fisica) 
            if ($this->listaAllegatiDocAl[$toRemove]['IDDOCAL']) {
                $this->listaAllegatiCancellatiDocAl[] = $this->listaAllegatiDocAl[$toRemove]['IDDOCAL'];
            }

            unset($this->listaAllegatiDocAl[$toRemove]);

            $this->reloadGridDocAl();
        }
    }

    private function creaRandomGuid() {
        return rand(1000, 99999) * rand(500, 3000) + rand(1000, 99999);
    }

    /*
     * aggiunge un allegato all'unita documentaria senza salvarlo su db (il salvataggio di tutto viene fatto al conferma)
     */

    private function aggiungiAllegato() {
        $recordDocal = $_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCAL];
        if ($recordDocal['STATO_DOC'] === null) {
            // se è vuoto significa che a video è disabilitato(non torna il valore sulla post da disabilitato),
            // quindi se è disabilitato è sicuramente 'definitivo' (1)
            $recordDocal['STATO_DOC'] = 1;
        }

        // devo reperire il binario che può essere su componente upload, su editor html o su txt multiline
        // per capirlo, controllo per prima cosa se documentoCaricato è popolato (caso upload)
        // sennò verifico sull'array formati se è attivo un editor e che tipo di componente usa per quel tipo di formato scelto a video
        $recordDocal['NOME_FILE'] = $_POST[$this->nameForm . '_NOMEFILE_HIDDEN'];

        if (!$recordDocal['NOME_FILE']) {
            // gli do un nome random e gli metto l'estensione di default
            $recordDocal['NOME_FILE'] = rand(0, 9999999) . $this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['estensioneDefault'];
        }

        $recordDocal['MIMETYPE'] = itaMimeTypeUtils::estraiEstensione($recordDocal['NOME_FILE']);

        if ($this->documentoCaricato) {
            $recordDocal['pathAllegato'] = $this->documentoCaricato;
        } else if ($this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['showEditor']) {
            $content = null;
            // è attivo un editor, controllo se è quello html o txt multiline
            if ($this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['descrizione'] == 'HTML') {
                $content = $_POST[$this->nameForm . '_EDITOR_HTML'];
            } else {
                $content = $_POST[$this->nameForm . '_EDITOR_TXT'];
            }
            // aggiungo al record il binario dell'allegato per salvarlo dopo
            $recordDocal['pathAllegato'] = $this->scriviSuTemp($content);
        }

        // validazione        
        if ($this->libGestDoc->validaBgdDocAl($this->MAIN_DB, $recordDocal, itaModelService::OPERATION_INSERT)) {
            if ($this->documentoCaricato) {
                $this->documentoCaricato = null;
            }
            // aggiungo la chiave per la grid
            $recordDocal[cwbLibBGDGestComunicazioni::CHIAVE_GRID_RANDOM] = $this->creaRandomGuid();
            // aggiungo un nuovo record su listaAllegatiDocAl e poi ricarico la grid            
            $this->listaAllegatiDocAl[] = $recordDocal;

            $this->reloadGridDocAl();

            $this->visDocud();
        } else {
            Out::msgStop("Errore Validazione", $this->libGestDoc->getErrore());
        }
    }

    // e' consentita la modifica solo dell'allegato(se stato = 1 in lavorazione), dello stato (da lavorazione a definitivo) e delle note
    private function aggiornaAllegato() {
        $randomguid = $_POST[$this->nameForm . '_RANDOMGUID_HIDDEN'];
        $recordFound = $this->searchCurrentDocAl($randomguid);

        $found = $recordFound['record'];
        $keyFound = $recordFound['chiave'];

        // aggiorno stato
        if ($_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCAL]['STATO_DOC'] !== null) {
            // se è popolato lo aggiorno
            $found['STATO_DOC'] = $_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCAL]['STATO_DOC'];
        } else {
            // se è vuoto significa che è disable  
            $found['STATO_DOC'] = $_POST[$this->nameForm . '_STATO_DOC_HIDDEN'];
        }

        // aggiorno note
        $found['ANNOTAZ'] = $_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCAL]['ANNOTAZ'];

        $mimetype = $found['MIMETYPE'];
        if (!$mimetype) {
            $mimetype = itaMimeTypeUtils::estraiEstensione($found['NOME_FILE']);
        }

        $formatoArray = $this->searchCurrentFormato($mimetype);
        $formatoTrovato = $formatoArray['formato'];

        // aggiorno allegato
        // devo reperire il binario che può essere su componente upload, su editor html o su txt multiline
        // per capirlo, controllo per prima cosa se documentoCaricato è popolato (caso upload)
        // sennò verifico sull'array formati se è attivo un editor e che tipo di componente usa per quel tipo di formato scelto a video
        if ($this->documentoCaricato) {
            $found['pathAllegato'] = $this->documentoCaricato;
            $found['NOME_FILE'] = str_replace(App::$utente->getKey('TOKEN') . '-', "", basename($this->documentoCaricato));
        } else if ($formatoTrovato['showEditor']) {
            $content = null;
            // è attivo un editor, controllo se è quello html o txt multiline
            if ($formatoTrovato['descrizione'] == 'HTML') {
                $content = $_POST[$this->nameForm . '_EDITOR_HTML'];
            } else {
                $content = $_POST[$this->nameForm . '_EDITOR_TXT'];
            }

            if ($content) {
                // aggiungo al record il binario dell'allegato per salvarlo dopo
                $found['pathAllegato'] = $this->scriviSuTemp($content);
                // gli do un nome random e gli metto l'estensione di default
                $found['NOME_FILE'] = rand(0, 9999999) . $formatoTrovato['estensioneDefault'];
                // TODO se c'è errore le chiavi rimosse sono perse. creare un array con key = RANDOMGUID e valore i campi non su db invece che appoggiarli sul record principale
            }
        }

        // validazione        
        if ($this->libGestDoc->validaBgdDocAl($this->MAIN_DB, $found, itaModelService::OPERATION_INSERT)) {
            if ($this->documentoCaricato) {
                $this->documentoCaricato = null;
            }
            // aggiorno il record sulal lista
            $this->listaAllegatiDocAl[$keyFound] = $found;

            $this->reloadGridDocAl();

            $this->visDocud();
        } else {
            Out::msgStop("Errore Validazione", $this->libGestDoc->getErrore());
        }
    }

    private function modificaAllegato() {
        $this->pulisciDettaglioDocAl();
        $randomguid = $_POST['rowid'];

        Out::valore($this->nameForm . '_RANDOMGUID_HIDDEN', $randomguid); // salvo la chiave su un campo hidden

        $recordFound = $this->searchCurrentDocAl($randomguid);

        $found = $recordFound['record'];

        Out::valore($this->nameForm . '_UPLOAD', $found['NOME_FILE']);

        // butto a video i valori
        Out::valori($found, $this->nameForm . '_' . self::TABELLA_BGDDOCAL);
        Out::valore($this->nameForm . '_STATO_DOC_HIDDEN', $found['STATO_DOC']);

        $this->visDettaglioDocAlModifica();

        $mimetype = $found['MIMETYPE'];

        if (!$mimetype) {
            $mimetype = itaMimeTypeUtils::estraiEstensione($found['NOME_FILE']);
        }
        $formatoArray = $this->searchCurrentFormato($mimetype);

        $formatoTrovato = $formatoArray['formato'];
        // popolo la combo formato ricavandola dal mimetype
        Out::valore($this->nameForm . '_FORMATO', $formatoArray['chiave']);
        Out::valore($this->nameForm . '_FORMATO_HIDDEN', $formatoArray['chiave']);

        $this->visAnteprima($formatoTrovato['showEditor'], $formatoTrovato['descrizione']);
        $this->visScanner($formatoTrovato['showScanner']);
        $this->statoDoc($found['STATO_DOC']);
        // disabilito i campi non modificabili in base a stato_doc (0 ABILITATO, > 0 BLOCCATO)
        $this->disableDocAlDettaglio($found['STATO_DOC']);

        // se c'è un editor a video ci rimetto dentro il binario
        if ($formatoTrovato['showEditor']) {
            $this->visDocViewer(false);
            $res = $this->getBinarioDocAl($found);

            if ($res) {
                if ($formatoTrovato['descrizione'] == 'HTML') {
                    Out::valore($this->nameForm . '_EDITOR_HTML', $res);
                } else {
                    Out::valore($this->nameForm . '_EDITOR_TXT', $res);
                }
            } else {
                Out::msgStop("Errore Caricamento Allegato", $this->libGestDoc->getErrore());
            }
        } else {
            // se non c'è editor e sono in modifica metto il docViewer come anteprima
            if ($formatoTrovato['descrizione'] !== 'Doc/Docx/Rtf') {
                $this->visDocViewer(true, $found);
            } else {
                $this->visDocViewer(false);
                Out::valore($this->nameForm . '_UPLOAD', "Documento" . $formatoTrovato['estensioneDefault']);
            }
        }
    }

    private function getBinarioDocAl($recordDocAl) {
        if ($recordDocAl['pathAllegato']) {
            // leggo da disco (caso di allegato modificato (modifica non salvata su db) e poi riaperto in modifica)
            $res = file_get_contents($recordDocAl['pathAllegato']);
        } else {
            // leggo da documentale il binario (caso di apertura in modifica di un allegato 'fresco' da db)
            $res = $this->libGestDoc->getAllegato($recordDocAl['UUID_DOC']);
        }

        return $res;
    }

    private function searchCurrentDocAl($randomguid) {
        foreach ($this->listaAllegatiDocAl as $key => $docal) {
            if ($docal['RANDOMGUID'] == $randomguid) {
                return array('record' => $docal, 'chiave' => $key);
            }
        }

        return null;
    }

    private function searchCurrentFormato($mimetype) {
        foreach ($this->formati as $key => $formato) {
            if (in_array($mimetype, $formato['mimetypes'])) {
                return array('formato' => $formato, 'chiave' => $key);
            }
        }

        return null;
    }

    private function disableDocAlDettaglio($allegatoBloccato) {
        if ($allegatoBloccato) {
            Out::disableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
            Out::disableField($this->nameForm . '_UPLOAD');
            Out::disableField($this->nameForm . '_BGD_DOC_AL[ANNOTAZ]');
            Out::hide($this->nameForm . '_Aggiorna_docal');
            Out::hide($this->nameForm . '_Scanner_docal');
        } else {
            Out::enableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
            Out::enableField($this->nameForm . '_UPLOAD');
            Out::enableField($this->nameForm . '_BGD_DOC_AL[ANNOTAZ]');
        }
        Out::disableField($this->nameForm . '_BGD_DOC_AL[TIPO_COM]');
        Out::disableField($this->nameForm . '_FORMATO');
    }

    private function enableDocAlDettaglio() {
        Out::enableField($this->nameForm . '_BGD_DOC_AL[TIPO_COM]');
        Out::enableField($this->nameForm . '_FORMATO');
        Out::enableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
        Out::enableField($this->nameForm . '_UPLOAD');
        Out::enableField($this->nameForm . '_BGD_DOC_AL[ANNOTAZ]');
    }

    private function scriviSuTemp($content) {
        $path = itaLib::getPrivateUploadPath() . uniqid("", true);
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * Effettua connessione al database di Cityware
     */
    private function connettiDB() {
        try {
            $this->MAIN_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }

    private function dettaglioAllegato() {
        $this->pulisciDettaglioDocAl();

        // disabilito il campo di testo dell'upload in cui metto il nome dell'allegato non modificabile
        $idUploader = $this->nameForm . "_UPLOAD";
        Out::codice("$('#" . $idUploader . "').prop('disabled', true);");

        // metto progcomu fisso su docal perche' e' sempre quello di docud
        Out::valore($this->nameForm . '_' . self::TABELLA_BGDDOCAL . '[PROGCOMU]', $_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCUD]['PROGCOMU']);

        $this->visDettaglioDocAl();

        $this->documentoCaricato = null;
    }

    private function cambiaFormato() {
        // al cambio di formato verifico se renderizzare il tasto scan e l'editor html, 
        // controllando i parametri del formato scelto nell'array 'formato'

        Out::valore($this->nameForm . '_FORMATO_HIDDEN', $_POST[$this->nameForm . '_FORMATO']);
        if ($_POST[$this->nameForm . '_FORMATO']) {
            $this->visAnteprima($this->formati[$_POST[$this->nameForm . '_FORMATO']]['showEditor'], $this->formati[$_POST[$this->nameForm . '_FORMATO']]['descrizione']);
            $this->visScanner($this->formati[$_POST[$this->nameForm . '_FORMATO']]['showScanner']);
            $this->statoDoc($this->formati[$_POST[$this->nameForm . '_FORMATO']]['modificabile'] ? 0 : 1);
        } else {
            $this->visAnteprima(false);
            $this->visScanner(false);
            $this->statoDoc(0);
        }
    }

    private function initComboStato() {
        // disabilitato perché di default si apre su tipo documento pdf
        Out::disableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
        Out::select($this->nameForm . '_BGD_DOC_AL[STATO_DOC]', 1, 0, 0, "In Lavorazione");
        Out::select($this->nameForm . '_BGD_DOC_AL[STATO_DOC]', 1, 1, 1, "Definitivo");
        Out::select($this->nameForm . '_BGD_DOC_AL[STATO_DOC]', 1, 2, 0, "Firmato");

        Out::valore($this->nameForm . '_STATO_DOC_HIDDEN', 1);
    }

    private function initComboFormato() {
        foreach ($this->formati as $key => $value) {
            Out::select($this->nameForm . '_FORMATO', 1, $key, $key == 1, $value['descrizione']);
            if ($key == 1) {
                Out::valore($this->nameForm . '_FORMATO_HIDDEN', $key);
            }
        }
    }

    private function initComboTipoCom() {
        $libBta = new cwbLibDB_BTA();
        $tipi = $libBta->leggiBtaTipcom(array('FLAG_DIS' => 0, 'COMPROV' => self::PROVENIENZA_ANAGRAFE));

        Out::select($this->nameForm . '_BGD_DOC_AL[TIPO_COM]', 1, "0", 1, "Seleziona...");
        foreach ($tipi as $value) {
            Out::select($this->nameForm . '_BGD_DOC_AL[TIPO_COM]', 1, $value['TIPO_COM'], 0, trim($value['DES_TIPCOM']));
        }
    }

    /*
     * upload
     */

    private function caricaDocumento() {
        $this->documentoCaricato = array();
        $fileName = $_POST['file'];
        $estensione = strtolower(array_pop(explode('.', $fileName)));

        $extAmmesse = $this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['estensioniAmmesse'];

        // controllo che il documento caricato sia della giusta estensione in base al formato scelto
        if (in_array($estensione, $extAmmesse)) {
            $fullPath = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $fileName;

            // setta il nome del documento sul campo annotazione come default
            Out::valore($this->nameForm . '_' . self::TABELLA_BGDDOCAL . '[ANNOTAZ]', $fileName);
            Out::valore($this->nameForm . '_NOMEFILE_HIDDEN', $fileName);

            if ($this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['showEditor']) {
                // se c'è un editor a video, il file caricato con l'upload lo butto dentro l'editor che poi comanda
                // (l'upload serve solo per importare il content)
                $this->documentoCaricato = null;

                if ($this->formati[$_POST[$this->nameForm . '_FORMATO_HIDDEN']]['descrizione'] === 'HTML') {
                    // editor html
                    $nomeEditor = 'HTML';
                } else {
                    // editor multiline semplice
                    $nomeEditor = 'TXT';
                }

                $content = file_get_contents($fullPath);
                if ($content) {
                    Out::valore($this->nameForm . '_EDITOR_' . $nomeEditor, $content);
                } else {
                    Out::valore($this->nameForm . '_EDITOR_' . $nomeEditor, "");
                }
            } else {
                // se non c'è l'editor a video, popolo documentoCaricato (caso solo upload)
                $this->documentoCaricato = $fullPath;
                // setta il nome del documento sulla text dell'upload
                Out::valore($this->nameForm . '_UPLOAD', $fileName);
            }
        } else {
            $msg = '';
            foreach ($extAmmesse as $value) {
                $msg .= $value . ' ';
            }
            Out::msgStop('Errore', 'Formato errato, estensioni ammesse per il formato scelto: ' . $msg);
        }
    }

    // pulisco i campi del dettaglio sennò' rimangono sporchi dall'inserimento precedente
    private function pulisciDettaglioDocAl() {
        $this->enableDocAlDettaglio();

        Out::valore($this->nameForm . '_BGD_DOC_AL[TIPO_COM]', '');
        Out::valore($this->nameForm . '_BGD_DOC_AL[PROGCOMU]', '');
        Out::valore($this->nameForm . '_BGD_DOC_AL[ANNOTAZ]', '');
        Out::valore($this->nameForm . '_UPLOAD', ''); // PULISCO COMPONENTE UPLOAD
        Out::valore($this->nameForm . '_FORMATO', 1); // imposto di default formato a pdf
        Out::valore($this->nameForm . '_FORMATO_HIDDEN', 1); // imposto di default formato a pdf
        Out::valore($this->nameForm . '_NOMEFILE_HIDDEN', ''); // imposto di default formato a pdf
        Out::valore($this->nameForm . '_EDITOR_HTML', '');
        Out::valore($this->nameForm . '_EDITOR_TXT', '');
        Out::valore($this->nameForm . '_STATO_DOC_HIDDEN', 1); // imposto di default formato a pdf
        $this->statoDoc(1); // rimetto la combo stato a definitivo (formato=pdf quindi non è modificabile)
        $this->visDocViewer(false);
    }

    private function annulla() {
        $this->visDocud();
    }

    // visualizza la pagina di dettaglio di bgddocal per caricare un allegato o visualizzarlo nel dettaglio
    private function visDettaglioDocAl() {
        $this->visAnteprima(false);
        Out::show($this->nameForm . '_Annulla_docal');
        Out::show($this->nameForm . '_Aggiungi_docal');
        Out::show($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_Conferma_docud');
        Out::hide($this->nameForm . '_Esporta_docud');
        Out::hide($this->nameForm . '_Invia_docud');
        Out::hide($this->nameForm . '_Firma_docud');
        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_Scanner_docal');
        Out::hide($this->nameForm . "_Download_docal");
    }

    // visualizza la pagina di dettaglio di bgddocal per caricare un allegato o visualizzarlo nel dettaglio
    private function visDettaglioDocAlModifica() {
        $this->visAnteprima(false);
        Out::show($this->nameForm . '_Annulla_docal');
        Out::hide($this->nameForm . '_Aggiungi_docal');
        Out::show($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_Conferma_docud');
        Out::hide($this->nameForm . '_Esporta_docud');
        Out::hide($this->nameForm . '_Invia_docud');
        Out::hide($this->nameForm . '_Firma_docud');
        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_Aggiorna_docal');
        Out::show($this->nameForm . "_Download_docal");
    }

    // visualizza pagina di bgddocud con la griglia degli allegati
    private function visDocud() {
        Out::show($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_Conferma_docud');
        Out::show($this->nameForm . '_Esporta_docud');
        Out::show($this->nameForm . '_Invia_docud');
        Out::show($this->nameForm . '_Firma_docud');
        Out::hide($this->nameForm . '_Aggiorna_docal');
        Out::hide($this->nameForm . '_Aggiungi_docal');
        Out::hide($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_Annulla_docal');
        Out::hide($this->nameForm . '_Scanner_docal');
        Out::hide($this->nameForm . '_Download_docal');
    }

    // visualizza il componente html editor
    private function visAnteprima($vis = false, $tipoTxt = 'HTML') {
        if ($vis) {
            if ($tipoTxt == 'HTML') {
                // se html attivo l'editor html sennò un multiline semplice (txt, xml)
                Out::show($this->nameForm . '_divAnteprimaHtml');
                Out::hide($this->nameForm . '_divAnteprimaTxt');
                // attiva componente editor
                Out::codice('tinyActivate("' . $this->nameForm . '_EDITOR_HTML");');
            } else if ($tipoTxt == 'XML' || $tipoTxt == 'TXT') {
                Out::hide($this->nameForm . '_divAnteprimaHtml');
                Out::show($this->nameForm . '_divAnteprimaTxt');
            }
        } else {
            Out::hide($this->nameForm . '_divAnteprimaHtml');
            Out::hide($this->nameForm . '_divAnteprimaTxt');
        }
    }

    private function visDocViewer($vis = false, $recordDocAl = null) {
        Out::innerHtml($this->nameForm . '_divAnteprimaPdf', ""); // pulisco div
        if ($vis) {
            Out::show($this->nameForm . '_divAnteprimaPdf');
            if ($recordDocAl) {
                $this->innestaDocViewer($recordDocAl);
            } else {
                Out::valore($this->nameForm . '_divAnteprimaPdf', "");
            }
        } else {
            Out::hide($this->nameForm . '_divAnteprimaPdf');
        }
    }

    private function innestaDocViewer($recordDocAl) {
        if ($recordDocAl['pathAllegato']) {
            $pathDest = $recordDocAl['pathAllegato'];
        } else {
            $content = $this->getBinarioDocAl($recordDocAl);
            $pathDest = $this->scriviSuTemp($content);
        }

        if ($pathDest) {
            $alias = self::DOCVIEWER . '_' . time();
            $formObj = cwbLib::innestaForm(self::DOCVIEWER, $this->nameForm . '_divAnteprimaPdf', false, $alias);

            if ($formObj) {
                $formObj->setEvent('openform');
                $formObj->setSingleMode(true);
                $formObj->setFiles(array(0 => array('NOME' => $pathDest, 'NOME_REALE' => rand(999, 999999), 'MIME' => $recordDocAl['MIMETYPE'])));
                $formObj->parseEvent();
            }
        }
    }

    // visualizza il tasto scanner
    private function visScanner($vis = false) {
        if ($vis) {
            Out::show($this->nameForm . '_Scanner_docal');
        } else {
            Out::hide($this->nameForm . '_Scanner_docal');
        }
    }

    // definisce se l'allegato è modificabile oppure no
    private function statoDoc($statoDoc) {
        if (!$statoDoc) {
            Out::enableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
            Out::valore($this->nameForm . '_BGD_DOC_AL[STATO_DOC]', 0);
            Out::valore($this->nameForm . '_STATO_DOC_HIDDEN', 0);
        } else {
            Out::disableField($this->nameForm . '_BGD_DOC_AL[STATO_DOC]');
            Out::valore($this->nameForm . '_BGD_DOC_AL[STATO_DOC]', $statoDoc);
            Out::valore($this->nameForm . '_STATO_DOC_HIDDEN', $statoDoc);
        }
    }

    private function aggiungiUnitaDocumentaria() {
        $recordDocUd = $_POST[$this->nameForm . '_' . self::TABELLA_BGDDOCUD];
        $recordDocUd['PROVENIENZA'] = $this->nameFormOrig;

        if ($this->libGestDoc->insertRecordsUnitaDocumentaria($this->listaAllegatiDocAl, $this->idPadreDanAnagra, $recordDocUd, $this->listaAllegatiCancellatiDocAl)) {
            // se tutto va a buon fine chiudo la dialog
            $this->closeDialog();
        } else {
            Out::msgStop("Errore", $this->libGestDoc->getErrore());
        }
    }

    private function closeDialog() {
        $this->close();
        Out::closeDialog($this->nameForm);
    }

    function getExternalParams() {
        return $this->externalParams;
    }

    function setExternalParams($externalParams) {
        $this->externalParams = $externalParams;
    }

}

