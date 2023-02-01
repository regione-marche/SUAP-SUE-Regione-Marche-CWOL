<?php

class praGestAcl extends itaModelFO {

    public $praLib;
    public $praErr;
    public $praLibEventi;
    public $praLibDati;
    public $praLibDatiAggiuntivi;
    public $praLibAcl;
    public $frontOfficeLib;
    public $PRAM_DB;
    public $GAFIERE_DB;
    public $ITAFRONTOFFICE_DB;
    public $ITALWEB_DB;
    public $workDate;
    public $workYear;
    public $errUpload;
    public $dati;
    private $errCode;
    private $errMessage;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
            $this->praLibEventi = new praLibEventi();
            $this->praLibDati = praLibDati::getInstance($this->praLib);
            $this->praLibDatiAggiuntivi = new praLibDatiAggiuntivi();
            $this->praLibAcl = new praLibAcl();
            $this->frontOfficeLib = new frontOfficeLib();

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->GAFIERE_DB = ItaDB::DBOpen('GAFIERE', frontOfficeApp::getEnte());
            $this->ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());

            if (!$this->dati = $this->praLibDati->prendiDati($this->request['ricnum'])) {
                return $this->praLibDati->gestioneErrore($this->praLibDati->getErrCode(), $this->praLibDati->getErrMessage(), $this->praErr);
            }

            $this->workDate = date('Ymd');
            $this->workYear = date('Y');
        } catch (Exception $e) {
            
        }
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    function getDati() {
        return $this->dati;
    }

    function setDati($dati) {
        $this->dati = $dati;
    }

    public function parseEvent() {
        $html = new html();
        //
        // Controllo sulla validità utente
        //

        if (!$this->ControlloValiditaUtente()) {
            return output::$html_out;
        }

        switch ($this->request['event']) {
            case 'openBlock':

                output::$html_out .= $this->disegnaPagina($this->dati);

                return output::$html_out;

                break;
            case 'ctrlCf':
                $info = $err = '';
                $nomeUtente = frontOfficeApp::$cmsHost->getUtenteFromCodFis($this->request['cf']);
                if ($nomeUtente !== false) {
                    $info = $html->getAlert("", "Utente Registrato", "info");
                    $datiNuovoEsibente = frontOfficeApp::$cmsHost->getDatiDaUtente($nomeUtente);

                    /**
                     * Svuoto eventuali valori presenti nelle caselle di testo
                     */
                    $this->request['cognome'] = '';
                    $this->request['nome'] = '';
                    $this->request['mail'] = '';

                    $params = $this->getDatiSoggetto($datiNuovoEsibente);
                    $arrDati = array();
                    foreach ($params as $campo) {
                        $arrDati[$campo['nomeCampo']] = "";
                        $arrDati[$campo['nomeCampo']] = $campo['valore'];
                        output::responseDisableField($campo['nomeCampo']);
                    }
                    output::ajaxResponseValues($arrDati);
                } else {
                    $info = $html->getAlert("Compilare con attenzione i campi e dopo il cambio esibente e' opportuno registrarsi al portale.", "Utente non Registrato", "info");
                    $praLibDatiAggiuntivi = new praLibDatiAggiuntivi();
                    if (!$praLibDatiAggiuntivi->controllaValidoSe("CodiceFiscalePiva", $this->request['cf'])) {
                        $err = $html->getAlert("Il campo <b>Codice Fiscale/P. Iva</b> contiene un valore non valido.", '', "error");
                    }
                }
//                output::responseHtml($info, 'divInfoCambioEsibente');
                output::responseHtml($err, 'divErrorGestAcl');
                output::ajaxSendResponse();
                break;

            case 'addACL':

                output::ajaxResponseDialog($this->disegnaFormTipoAcl($this->request['ricnum']), array(
                    'title' => 'Scelta tipo di Condivisione',
                    'width' => 800
                ));
                output::ajaxSendResponse();


//                output::$html_out .= "<pre>AGGIUNGI REGOLA</pre>";
//                return output::$html_out;
                break;
            case 'pulisciSoggettoAcl':

                /**
                 * Svuoto eventuali valori presenti nelle caselle di testo
                 */
                $this->request['cf'] = '';
                $this->request['cognome'] = '';
                $this->request['nome'] = '';
                $this->request['mail'] = '';

                $params = $this->getDatiSoggetto($datiNuovoEsibente);
                $arrDati = array();
                foreach ($params as $campo) {
                    $arrDati[$campo['nomeCampo']] = "";
                    output::responseEnableField($campo['nomeCampo']);
                }
                output::ajaxResponseValues($arrDati);


                break;

            case 'confermaModificaACL':

                /*
                 * Controllo date inserire correttamente
                 */
                $inizio = $this->request['dataInizio'];
                $fine = $this->request['dataFine'];
                $msgErrore = "";
                if (!$inizio || !$fine) {
                    if (!$inizio) {
                        $msgErrore = "Il campo <b>Data Inizio Validità</b> contiene un valore non valido.<br>";
                    }
                    if (!$fine) {
                        $msgErrore .= "Il campo <b>Data Fine Validità</b> contiene un valore non valido.<br>";
                    }
                } else {
                    if (frontOfficeLib::converti($fine) < frontOfficeLib::converti($inizio)) {
                        $msgErrore = "Data fine validita' è precedente alla data inizio validita'. Correggere i valori inseriti.<br>";
                    }
                }

                if ($msgErrore) {
                    $error = $html->getAlert($msgErrore, "Attenzione", "error");
                    output::responseHtml($error, 'divErrorGestAcl');
                    output::ajaxSendResponse();
                    break;
                }

                $arrayRicAcl = array(
                    'ROW_ID' => $this->request['idricacl'],
                    'RICACLDATA_INIZIO' => frontOfficeLib::converti($this->request['dataInizio']),
                    'RICACLDATA_FINE' => frontOfficeLib::converti($this->request['dataFine']),
                );

                if ($this->request['tipoRegola'] == 'Visualizzazione') {
                    $arrayRicAcl['RICACLATTIVA'] = $this->request['tipoAttivaACL'];
                }

                if (!$this->praLibAcl->modificaRicAcl($arrayRicAcl, $this->dati['PRAM_DB'], $this->dati['Proric_rec']['ROWID'], $this->request['cf'])) {
                    output::addAlert($this->praLibAcl->getErrMessage(), 'Attenzione', 'error');
                    break;
                    //$this->errCode = -1;
                    //$this->errMessage = $this->praLibAcl->getErrMessage();
                    //return false;
                }

                output::$html_out .= $this->disegnaPagina($this->dati);

                output::addMsgInfo("Modifica Regola", "Modifica effettuata con successo");

                return output::$html_out;


                break;
            case 'confermaCaricaNuovaAcl':
                $parSog = $this->getDatiSoggetto();
                $parDate = $this->getDatiValidita();

                $params = array_merge($parSog, $parDate);


                $arrCampi = array(
                    array(
                        'chiave' => 'CodiceFiscale',
                        'valore' => $this->request['cf'],
                    ),
                    array(
                        'chiave' => 'Cognome',
                        'valore' => $this->request['cognome'],
                    ),
                    array(
                        'chiave' => 'Nome',
                        'valore' => $this->request['nome'],
                    ),
                    array(
                        'chiave' => 'Mail',
                        'valore' => $this->request['mail'],
                    ),
                    array(
                        'chiave' => 'dataInizio',
                        'valore' => $this->request['dataInizio'],
                    ),
                    array(
                        'chiave' => 'dataFine',
                        'valore' => $this->request['dataFine'],
                    ),
                );

                if (!$this->salvaAcl($arrCampi, $this->request['ricnum'], $this->request['tipoRegola'], $this->request['seq'], $this->request['tipoAttivaACL'])) {
                    output::addAlert($this->getErrMessage(), 'Attenzione', 'error');
                }

                if ($this->getErrCode() == -2) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0222', "Errore invio mail cambio esibente: " . $this->getErrMessage(), __CLASS__, "", false);
                }

                output::$html_out .= $this->disegnaPagina($this->dati);

                output::addMsgInfo("Caricamento", "Caricamento effettuato con successo");

                return output::$html_out;


                break;
            case 'caricaNuovaAcl':
                
                $parSog = $this->getDatiSoggetto();
                $parDate = $this->getDatiValidita();

                $params = array_merge($parSog, $parDate);


                $error = "";
                if (!$this->controlloPreNuovaAcl($params)) {
                    $error = $html->getAlert($this->getErrMessage(), "Attenzione", "error");
                    output::responseHtml($error, 'divErrorGestAcl');
                    output::ajaxSendResponse();
                    break;
                }

                output::responseHtml($error, 'divErrorGestAcl');

                $js = $this->addJsConfermaAcl($this->request);
                $html->appendHtml($js);
                output::ajaxResponseHtml($html->getHtml(), null, 'append');
                output::ajaxSendResponse();


                break;
            case 'cessaACL':
//                $js = $this->addJsConfermaCancella($this->request);
//                //$html->appendHtml($js);
//                output::$html_out .= $this->disegnaPagina($this->dati);
//                output::$html_out .= $js;

                $js = $this->addJsConfermaCancella($this->request);
                $html->appendHtml($js);
                output::ajaxResponseHtml($html->getHtml(), null, 'append');
                output::ajaxSendResponse();

//                output::ajaxResponseHtml($html->getHtml(), null, 'append');
//                output::ajaxSendResponse();
//                $successo = false;
//                if ($this->request['idricacl']) {
//
//                    $ricAcl_rec = $this->praLib->GetRicAcl($this->request['idricacl'], 'row_id', $this->dati['PRAM_DB'], false);
//                    if ($ricAcl_rec){
//
//                        if ($this->praLibAcl->cessaRicAcl($ricAcl_rec, $this->dati['PRAM_DB'])){
//                            $successo = true;
//                        }
//                        
//                    }
//                }
//                
//                if ($successo){
//                    output::addMsgInfo("Annullamento Accesso", "L'accesso è stato annullato con successo");
//                }
                return output::$html_out;
//                

                break;
            case 'confermaCancellazioneACL':
                $successo = false;

                if ($this->request['idricacl']) {
                    $ricAcl_rec = $this->praLib->GetRicAcl($this->request['idricacl'], 'row_id', $this->dati['PRAM_DB'], false);
                    if ($ricAcl_rec) {
                        if ($this->praLibAcl->cessaRicAcl($ricAcl_rec, $this->dati['PRAM_DB'], $this->dati['Proric_rec']['ROWID'], $this->request['cfSoggetto'])) {
                            $successo = true;
                        }
                    }
                }

                $html->appendHtml($this->disegnaPagina($this->dati));

                if ($successo) {
                    $html->addMsgInfo("Annullamento Condivisione", "La condivisione è stata annullata con successo");
                }

                return $html->getHtml();


                break;
            case 'modificaACL':
                if ($this->request['idricacl']) {
                    $ricAcl_rec = $this->praLib->GetRicAcl($this->request['idricacl'], 'row_id', $this->dati['PRAM_DB'], false);


                    if ($ricAcl_rec) {
//                        $this->request['dataInizio'] = $ricAcl_rec['RICACLDATA_INIZIO'];

                        $arrCodici = array('rowid' => $ricAcl_rec['ROW_ID_RICSOGGETTI']);
                        $ricSoggetti_rec = $this->praLib->GetRicsoggetti($arrCodici, 'rowid', $this->dati['PRAM_DB'], false);
//                        if ($ricSoggetti_rec) {
//                            $this->request['cf'] = $ricSoggetti_rec['SOGRICFIS'];
//                            $this->request['cognome'] = $ricSoggetti_rec['SOGRICDENOMINAZIONE'];
//                            $this->request['nome'] = $ricSoggetti_rec['SOGRICDENOMINAZIONE'];
//                            $this->request['mail'] = "";
//                        }
//                        $params = $this->getDatiSoggetto();
                        $html->appendHtml($this->disegnaFormModificaRegolaAcl($this->request['ricnum'], $ricAcl_rec, $ricSoggetti_rec));
//                        output::$html_out .= $this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Integrazione');
                    }
                }

//                $params = $this->getDatiSoggetto();
//                $html->appendHtml($this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Passo', $this->request['seq']));

                output::$html_out .= $html->getHtml();
                return output::$html_out;


                break;
            case 'gestACLIntegrazione':

                $params = $this->getDatiSoggetto();
                output::responseCloseCurrentDialog();
                output::ajaxResponseHtml($this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Integrazione'));
//                output::ajaxResponseDialog($this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Integrazione'), array(
//                    'title' => 'Conferma regola',
//                    'width' => 600
//                ));
                output::ajaxSendResponse();

                break;

            case 'gestACLVisualizzazione':
                $params = $this->getDatiSoggetto();
                output::responseCloseCurrentDialog();
                output::ajaxResponseHtml($this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Visualizzazione'));
                output::ajaxSendResponse();
                break;
            case 'gestACLPasso':
                output::responseCloseCurrentDialog();
                output::ajaxResponseHtml($this->disegnaPaginaElencoPassi($this->request['ricnum']));
                output::ajaxSendResponse();
                break;
            case 'confermaACLPasso':
                $params = $this->getDatiSoggetto();
                output::$html_out .= $this->disegnaFormRegolaAcl($this->request['ricnum'], $params, 'Passo', $this->request['seq']);
                return output::$html_out;
            default:
                break;
        }

//        output::$html_out .= $this->disegnaPagina($this->dati);
//        return output::$html_out;
    }

    public function disegnaPagina($dati) {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praTemplateACL.class.php';
        $praTemplateACL = new praTemplateACL();
        return $praTemplateACL->GetPagina($dati, array('PRAM_DB' => $this->PRAM_DB, 'CLASS' => 'praGestAcl', 'online_page' => $this->config['online_page']));
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
                $ricsoggetti_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSOGGETTI WHERE UPPER(SOGRICFIS) = '" . strtoupper($dati['fiscale']) . "' AND SOGRICNUM = '" . $this->request['ricnum'] . "'", false);
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

    public function disegnaFormTipoAcl($ricnum) {
        $praLibAcl = new praLibAcl();
        $html = new html();

        $html->appendHtml('<div style="padding: 20px;">');

        $html->addForm('', 'POST', array(
            'class' => 'italsoft-form--top'
                ), true);

        $info = $html->getAlert("Scegliere il tipo di condivisione che si vuole assegnare.", "Scelta Tipo di condivisione", "info");

        $html->appendHtml("<div id=\"divInfoGestAcl\">$info</div>");
        $html->appendHtml("<div id=\"divErrorGestAcl\"></div>");

        $html->addBr();



        //$retAclAttiva = $praLibAcl->checkAclAttiva($this->dati['Ricacl_tab_totali'], 'GESTIONE_RICHIESTA', 'INTEGRAZIONE_RICHIESTA');
        $retAclAttiva = $praLibAcl->checkAclAttiva($this->dati['Ricacl_tab_totali'], 'GESTIONE_RICHIESTA_INTEGRAZIONE', 'INTEGRAZIONE_RICHIESTA');


        if ($praLibAcl->isEnableACLButton('ACL_INTEGRAZIONE', $ricnum) && !$retAclAttiva) {
            $html->addButton("Gestisci Integrazione", '', 'primary', array('event' => 'gestIntegrazione', 'ricnum' => $ricnum));
            $html->appendHtml("  ");
        }


        /*
         * Bottone Gestisci Passo si visualizza solo se Richiesta è ancora in corso e la configurazione abilita il bottone
         */
        if ($this->dati['Proric_rec']['RICSTA'] == '99' && $praLibAcl->isEnableACLButton('ACL_GESTIONE_PASSO', $ricnum)) {
//        if ($this->dati['Proric_rec']['RICSTA'] == '99' && $praLibAcl->isEnableACLButton_lento('ACL_GESTIONE_PASSO', $this->dati)){
            $html->addButton("Gestisci Passo", '', 'primary', array('event' => 'gestPasso', 'ricnum' => $ricnum));
            $html->appendHtml("  ");
        }

        if ($praLibAcl->isEnableACLButton('ACL_VISIBILITA', $ricnum)) {
//        if ($praLibAcl->isEnableACLButton_lento('ACL_VISIBILITA', $this->dati)) {
            $html->addButton("Visualizza Richiesta", '', 'primary', array('event' => 'gestVisualizzazione', 'ricnum' => $ricnum));
            $html->appendHtml("  ");
        }



        $html->addButton("Indietro", '', 'primary', array('event' => 'gestACL', 'ricnum' => $ricnum));

        $html->closeTag('form');

        $html->appendHtml('</div>');
        return $html->getHtml();
    }

    public function disegnaFormRegolaAcl($ricnum, $campi, $tipoRegola, $seqPasso = 0) {
        $html = new html();

        $html->appendHtml('<div class="col-3-12 push-right">');
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlInfoPraticaSidebar.class.php';
        $praHtmlInfoPraticaSidebar = new praHtmlInfoPraticaSidebar();
        $extraParms = array();
        $extraParms['gestioneAccessi'] = true;
        $extraParms['PRAM_DB'] = $this->PRAM_DB;
        $dati = $this->praLibDati->prendiDati($ricnum, $seqPasso);
        $html->appendHtml($praHtmlInfoPraticaSidebar->GetSidebar($dati, $extraParms));
        /*
         * Chiusura griglia per colonna "Informazioni pratica" a destra,
         * si apre in "praHtmlTestata.class.php".
         */
        $html->appendHtml('</div>');

        $html->appendHtml("<div id=\"ita-praGestACLBody\" class=\"col-9-12 ita-blockBody\">");

        $html->addForm('', 'POST', array(
            'class' => 'italsoft-form--fixed'
                ), true);


        $info = $html->getAlert("Caricare i dati del soggetto a cui vogliamo permettere la gestione del passo:<br><b>" . $dati['Ricite_rec']['ITEDES'] . "</b>.", "Inserimento Condivisione del Passo", "info");
        if ($tipoRegola == 'Integrazione') {
            $info = $html->getAlert("Caricare i dati del soggetto a cui vogliamo permettere di poter effettuare un'integrazione collegata alla pratica corrente. <br>Caricare il periodo di tempo in cui il soggetto può effettuare l'integrazione", "Inserimento Condivisione di Integrazione", "info");
        } else if ($tipoRegola == 'Visualizzazione') {
            $info = $html->getAlert("Caricare i dati del soggetto a cui vogliamo permettere di accedere alla pratica in sola visualizzazione ", "Inserimento Condivisione di Visualizzazione", "info");
        }

        $html->appendHtml('<div id="divInfoGestAcl" >' . $info . '</div>');
        $html->appendHtml('<div id="divErrorGestAcl"></div>');

        $html->addBr();

        $html->addHidden("ricnum", $ricnum);
        $html->addHidden("tipoRegola", $tipoRegola);
        $html->addHidden("seq", $seqPasso);

        $html->addBr();

        foreach ($campi as $campo) {
            $html->addInput('text', $campo['descrizione'] . ' *', array(
                'name' => $campo['nomeCampo'],
                'value' => $campo['valore'],
                'class' => $campo['class'],
                'id' => $campo['id'],
                'size' => '40',
            ));

            if ($campo['id'] == 'cfAcl') {
                $html->addButton("<i class=\"icon ion-backspace italsoft-icon\"></i><div class=\"\" style=\"display:inline-block;\"><b>Svuota Dati</b></div>", '', 'secondary', array('event' => 'pulisciSoggettoAcl'));
            }


            $html->addBr();
        }


        $html->addInput('datepicker', 'Data inizio validità *', array(
            'name' => 'dataInizio',
            'value' => $this->request['dataInizio'],
//            'class' => 'italsoft-ajax-onchange',
        ));

        $html->addInput('datepicker', 'Data fine validità *', array(
            'name' => 'dataFine',
            'value' => $this->request['dataFine'],
//            'class' => 'italsoft-ajax-onchange',
        ));

        if ($tipoRegola == 'Visualizzazione') {

            $html->addBr();

            $valSelect = "<div class=\"italsoft-input-field\"><label for=\"attivaACL\">Attiva regola quando*</label>
                <select name=\"tipoAttivaACL\" id=\"attivaACL\">
                <option class=\"optSelect\" value=\"0\"></option>
                <option class=\"optSelect\" value=\"1\">Attivo solo se la richiesta On-Line e' in compilazione (NON INOLTRATA)</option>
                <option class=\"optSelect\" value=\"2\">Attivo solo se la richiesta On-Line e' stata INOLTRATA</option>
                <option class=\"optSelect\" value=\"3\" selected>Attivo sempre sia se la richiesta On-Line e' in compilazione o e' inoltrata</option>
            </select> </div>";

            $html->appendHtml($valSelect);
        }

//        if ($tipoRegola != 'Integrazione') {
//            $html->addBr();
//
////            $html->appendHtml("<label style=\"width:200px;\">TIPI OPERAZIONE:</label>");
////            $html->appendHtml("<input type=\"text\" style=\"display:none;\"/><br>");
////            $html->addInput('text', "Tipo Opeazione:*");
//
//            $appoggio = '<div class="italsoft-input-field"> '
//                    . '<label>Tipo Operazione* </label> '
//                    . '</div>';
//            $html->appendHtml($appoggio);
//
//
//            $html->addInput('checkbox', 'Inserisci', array(
//                'name' => 'inserisci',
//                'value' => $this->request['inserisci'],
//            ));
//            $html->addInput('checkbox', 'Modifica', array(
//                'name' => 'modifica',
//                'value' => $this->request['modifica'],
//            ));
//            $html->addInput('checkbox', 'Cancella', array(
//                'name' => 'cancella',
//                'value' => $this->request['cancella'],
//            ));
//        }

        $html->addBr();

        $html->addButton("Conferma", '', 'primary', array('event' => 'caricaNuovaAcl', 'tipoRegola' => $tipoRegola));

        $html->addButton("Annulla", '', 'primary', array('event' => 'gestACL', 'ricnum' => $ricnum));


        $html->closeTag('form');

        $html->appendHtml('</div>');
        return $html->getHtml();
    }

    public function disegnaFormModificaRegolaAcl($ricnum, $ricAcl_rec, $ricSoggetti_rec) {
        $html = new html();

        $arrAcl = json_decode($ricAcl_rec['RICACLMETA'], true);
        $tipoRegola = 'Visualizzazione';
        $idPasso = 0;
        $seqPasso = 0;
        if (is_array($arrAcl)) {
            foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_RICHIESTA') {
                    if ($autorizzazione['INTEGRAZIONE_RICHIESTA']) {
                        $tipoRegola = 'Integrazione';
                    } else {
                        $tipoRegola = 'Visualizzazione';
                    }
                } else if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
                    $tipoRegola = 'Passo';
                    $idPasso = $autorizzazione['ROW_ID_PASSO'];

                    /*
                     * Valorizzo $segPasso
                     */
                    $ricite_rec = $this->praLib->GetRicite($idPasso, 'rowId', $this->dati['PRAM_DB'], false);
                    if ($ricite_rec) {
                        $seqPasso = $ricite_rec['ITESEQ'];
                    }
                }
            }
        }


        $html->appendHtml('<div class="col-3-12 push-right">');
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlInfoPraticaSidebar.class.php';
        $praHtmlInfoPraticaSidebar = new praHtmlInfoPraticaSidebar();
        $extraParms = array();
        $extraParms['gestioneAccessi'] = true;
        $extraParms['PRAM_DB'] = $this->PRAM_DB;
        $dati = $this->praLibDati->prendiDati($ricnum, $seqPasso, '', true);
        $html->appendHtml($praHtmlInfoPraticaSidebar->GetSidebar($dati, $extraParms));
        /*
         * Chiusura griglia per colonna "Informazioni pratica" a destra,
         * si apre in "praHtmlTestata.class.php".
         */
        $html->appendHtml('</div>');

        $html->appendHtml("<div id=\"ita-praGestACLBody\" class=\"col-9-12 ita-blockBody\">");

        $html->addForm('', 'POST', array(
            'class' => 'italsoft-form--fixed'
                ), true);

        $info = '';
        if ($tipoRegola == 'Passo') {
            $info = $html->getAlert("Modificare il periodo in cui il soggetto presente può effettuare la gestione del passo:<br><b>" . $ricite_rec['ITEDES'] . "</b>.", "Modifica Regola Passo", "info");
        } else if ($tipoRegola == 'Integrazione') {
            $info = $html->getAlert("Modificare il periodo in cui il soggetto presente può effettuare un integrazione alla richiesta on-line corrente", "Modifica Regola Integrazione", "info");
        } else if ($tipoRegola == 'Visualizzazione') {
            $info = $html->getAlert("Modificare il periodo in cui il soggetto presente può visualizzare la richiesta on-line corrente </br>"
                    . "e quando la regola e' attiva", "Modifica Regola Visualizzazione", "info");
        }

        $html->appendHtml('<div id="divInfoGestAcl" >' . $info . '</div>');
        $html->appendHtml('<div id="divErrorGestAcl"></div>');

        $html->addBr();

        $html->addHidden("ricnum", $ricnum);
        $html->addHidden("tipoRegola", $tipoRegola);
        $html->addHidden("seq", $seqPasso);
        $html->addHidden("idricacl", $ricAcl_rec['ROW_ID']);


        $html->addBr();

        $html->addInput('text', 'Codice Fiscale/P. Iva', array(
            'name' => 'cf',
            'value' => $ricSoggetti_rec['SOGRICFIS'],
            'readonly' => "readonly",
            'size' => '40',
        ));

        $html->addBr();

        $html->addInput('text', 'Nominativo', array(
            'name' => 'nominativo',
            'value' => $ricSoggetti_rec['SOGRICDENOMINAZIONE'],
            'readonly' => "readonly",
            'size' => '70',
        ));

        $html->addBr();

        $html->addInput('datepicker', 'Data inizio validità *', array(
            'name' => 'dataInizio',
            'value' => frontOfficeLib::convertiData($ricAcl_rec['RICACLDATA_INIZIO']),
        ));

        $html->addInput('datepicker', 'Data fine validità *', array(
            'name' => 'dataFine',
            'value' => frontOfficeLib::convertiData($ricAcl_rec['RICACLDATA_FINE']),
        ));

        if ($tipoRegola == 'Visualizzazione') {

            $html->addBr();

            $valSelect = "<div class=\"italsoft-input-field\"><label for=\"attivaACL\">Attiva regola quando*</label>
                <select name=\"tipoAttivaACL\" id=\"attivaACL\">";
            if ($ricAcl_rec['RICACLATTIVA'] == 1) {
                $valSelect .= "<option class=\"optSelect\" value=\"1\" selected>Attivo solo se la richiesta On-Line e' in compilazione (NON INOLTRATA)</option>";
            } else {
                $valSelect .= "<option class=\"optSelect\" value=\"1\">Attivo solo se la richiesta On-Line e' in compilazione (NON INOLTRATA)</option>";
            }
            if ($ricAcl_rec['RICACLATTIVA'] == 2) {
                $valSelect .= "<option class=\"optSelect\" value=\"2\" selected>Attivo solo se la richiesta On-Line e' stata INOLTRATA</option>";
            } else {
                $valSelect .= "<option class=\"optSelect\" value=\"2\">Attivo solo se la richiesta On-Line e' stata INOLTRATA</option>";
            }
            if ($ricAcl_rec['RICACLATTIVA'] == 3) {
                $valSelect .= "<option class=\"optSelect\" value=\"3\" selected>Attivo sempre sia se la richiesta On-Line e' in compilazione o e' inoltrata</option>";
            } else {
                $valSelect .= "<option class=\"optSelect\" value=\"3\">Attivo sempre sia se la richiesta On-Line e' in compilazione o e' inoltrata</option>";
            }

            $valSelect .= "</select> </div>";

            $html->appendHtml($valSelect);
        }



//        if ($tipoRegola == 'Passo') {
//            $html->addBr();
//
////            $html->appendHtml("<label style=\"width:200px;\">TIPI OPERAZIONE:</label>");
////            $html->appendHtml("<input type=\"text\" style=\"display:none;\"/><br>");
////            $html->addInput('text', "Tipo Opeazione:*");
//
//            $appoggio = '<div class="italsoft-input-field"> '
//                    . '<label>Tipo Operazione* </label> '
//                    . '</div>';
//            $html->appendHtml($appoggio);
//
//
//            $html->addInput('checkbox', 'Inserisci', array(
//                'name' => 'inserisci',
//                'value' => $this->request['inserisci'],
//            ));
//            $html->addInput('checkbox', 'Modifica', array(
//                'name' => 'modifica',
//                'value' => $this->request['modifica'],
//            ));
//            $html->addInput('checkbox', 'Cancella', array(
//                'name' => 'cancella',
//                'value' => $this->request['cancella'],
//            ));
//        }

        $html->addBr();

        $html->addButton("Conferma", '', 'primary', array('event' => 'confermaModificaAcl', 'tipoRegola' => $tipoRegola));

        $html->addButton("Annulla", '', 'primary', array('event' => 'gestACL', 'ricnum' => $ricnum));


        $html->closeTag('form');

        $html->appendHtml('</div>');
        return $html->getHtml();
    }

    public function disegnaPaginaElencoPassi($ricnum) {
        $html = new html();

        $html->appendHtml('<div class="col-3-12 push-right">');
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlInfoPraticaSidebar.class.php';
        $praHtmlInfoPraticaSidebar = new praHtmlInfoPraticaSidebar();
        $extraParms = array();
        $extraParms['gestioneAccessi'] = true;
        $extraParms['PRAM_DB'] = $this->PRAM_DB;
        $dati = $this->praLibDati->prendiDati($ricnum);
        $html->appendHtml($praHtmlInfoPraticaSidebar->GetSidebar($dati, $extraParms));
        /*
         * Chiusura griglia per colonna "Informazioni pratica" a destra,
         * si apre in "praHtmlTestata.class.php".
         */
        $html->appendHtml('</div>');

        $html->appendHtml("<div id=\"ita-praGestACLBody\" class=\"col-9-12 ita-blockBody\">");

        $info = $html->getAlert("Scegliere un passo dall'elenco per il quale si desidera creare una nuova regola di condivisione. ", "Inserimento Nuova Condivisione del Passo.", "info");

        $html->appendHtml('<div id="divInfoGestAcl" >' . $info . '</div>');
        $html->appendHtml('<div id="divErrorGestAcl"></div>');

        $html->addBr();

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridPassi.class.php';
        $praHtmlGridPassi = new praHtmlGridPassi();
        $html->addButton("Indietro", '', 'primary', array('event' => 'gestACL', 'ricnum' => $ricnum));
        $html->addBr();

        $html->appendHtml($praHtmlGridPassi->GetGridPassiDaGestire($dati));

        $html->addHidden("ricnum", $ricnum);
        $html->addHidden("tipoRegola", 'Passo');

        $html->addBr();

        $html->addButton("Indietro", '', 'primary', array('event' => 'gestACL', 'ricnum' => $ricnum));


        $html->closeTag('form');

        $html->appendHtml('</div>');
        return $html->getHtml();
    }

    private function getDatiSoggetto($datiNuovoSoggetto = array()) {
        return array(
            array(
                'id' => 'cfAcl',
                'chiave' => 'CodiceFiscale',
                'valore' => $this->request['cf'] ? $this->request['cf'] : $datiNuovoSoggetto['fiscale'],
                'descrizione' => 'Codice Fiscale/P. Iva',
                'controllo' => 'CodiceFiscalePiva',
                'nomeCampo' => 'cf',
                'class' => 'italsoft-ajax-onchange',
            ),
            array(
                'chiave' => 'Cognome',
                'valore' => $this->request['cognome'] ? $this->request['cognome'] : $datiNuovoSoggetto['cognome'],
                'descrizione' => 'Cognome',
                'controllo' => 'Lettere',
                'nomeCampo' => 'cognome',
            ),
            array(
                'chiave' => 'Nome',
                'valore' => $this->request['nome'] ? $this->request['nome'] : $datiNuovoSoggetto['nome'],
                'descrizione' => 'Nome',
                'controllo' => 'Lettere',
                'nomeCampo' => 'nome',
            ),
            array(
                'chiave' => 'Mail',
                'valore' => $this->request['mail'] ? $this->request['mail'] : $datiNuovoSoggetto['email'],
                'descrizione' => 'Mail',
                'controllo' => 'email',
                'nomeCampo' => 'mail',
            ),
        );
    }

    private function getDatiValidita($datiNuovoValidita = array()) {
        return array(
            array(
                'chiave' => 'dataInizio',
                'valore' => $this->request['dataInizio'] ? $this->request['dataInizio'] : $datiNuovoValidita['dataInizio'],
                'descrizione' => 'Data Inizio Validità',
                'controllo' => 'Data',
                'nomeCampo' => 'dataInizio',
            ),
            array(
                'chiave' => 'dataFine',
                'valore' => $this->request['dataFine'] ? $this->request['dataFine'] : $datiNuovoValidita['dataFine'],
                'descrizione' => 'Data Fine Validità',
                'controllo' => 'Data',
                'nomeCampo' => 'dataFine',
            ),
        );
    }

    public function controlloPreNuovaAcl($params) {
        $this->errMessage = "";
        $this->errCode = 0;
        
        $praLibDatiAggiuntivi = new praLibDatiAggiuntivi();

        /*
         * Controllo Presenza valore
         */
        foreach ($params as $campo) {
            if (!$campo['valore']) {
                $this->errCode = -1;
                $this->errMessage .= "Campo <b>" . $campo['descrizione'] . "</b> mancante.<br>";
            }
        }

        /**
         * Controllo validità valore inserito
         */
        $inizio = $fine = $cf = '';
        foreach ($params as $campo) {
            if ($campo['valore']) {
                if (!$praLibDatiAggiuntivi->controllaValidoSe($campo['controllo'], $campo['valore'])) {
                    $this->errCode = -1;
                    $this->errMessage .= "Il campo <b>" . $campo['descrizione'] . "</b> contiene un valore non valido.<br>";
                } else {
                    switch ($campo['chiave']) {
                        case 'dataInizio':
                            $inizio = $campo['valore'];
                            break;
                        case 'dataFine':
                            $fine = $campo['valore'];
                            break;
                        case 'CodiceFiscale':
                            $cf = $campo['valore'];
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        if ($inizio && $fine) {
            if (frontOfficeLib::converti($fine) < frontOfficeLib::converti($inizio)) {
                $this->errCode = -1;
                $this->errMessage .= "Data fine validita' è precedente alla data inizio validita'. Correggere i valori inseriti.<br>";
            }
        }

        /*
         * Controllo viene fatto solo se stiamo salvando un ACL di visualizzazine per un CF valido
         */
        if ($cf && $this->request['tipoRegola'] == 'Visualizzazione') {
            $praLibAcl = new praLibAcl();
            //if ($praLibAcl->checkAclAttiva($this->dati['Ricacl_tab_totali'], 'GESTIONE_RICHIESTA', '', $cf)) {
            if ($praLibAcl->checkAclAttiva($this->dati['Ricacl_tab_totali'], 'GESTIONE_RICHIESTA_VISUALIZZAZIONE', '', $cf)) {
                $this->errCode = -1;
                $this->errMessage .= "La Condivisione di Visualizzazione è già stata assegnata al soggetto con codice fiscale " . $cf . ".<br>";
            }
        }


//        if ($this->request['tipoRegola'] != 'Integrazione') {
//            if (!$this->request['inserisci'] && !$this->request['modifica'] && !$this->request['cancella']) {
//                $this->errCode = -1;
//                $this->errMessage .= "Inserire almeno un tipo di operazione (inserisci;modifica;cancella) che può essere effettuato sul passo. <br>";
//            }
//        }

        if ($this->errCode != 0) {
            return false;
        }
        return true;
    }

    public function addJsConfermaAcl($request) {
        $request['event'] = 'confermaCaricaNuovaAcl';
        $url = ItaUrlUtil::GetPageUrl($request);
        $messaggio = "<b>Confermi di assegnare il passo a " . $request['cognome'] . " " . $request['nome'] . "</b>?</div>";
        if ($request['tipoRegola'] == 'Integrazione') {
            $messaggio = "<b>Confermi il caricamento della nuova regola di Integrazione assegnata a " . $request['cognome'] . " " . $request['nome'] . "</b>?</div>";
        } else if ($request['tipoRegola'] == 'Visualizzazione') {
            $messaggio = "<b>Confermi il caricamento della nuova regola di Visualizzazione assegnata a " . $request['cognome'] . " " . $request['nome'] . "</b>?</div>";
        }
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= '<b>ATTENZIONE:<br></b><br>';
        $content .= $messaggio;
//        $content .= "<b>Confermi il caricamento della nuova regola di Integrazione il cambio esibente per la richiesta n. <b>" . $request['ricnum'] . "</b> da<br> <b>" . $proric_rec['RICCOG'] . " " . $proric_rec['RICNOM'] . "</b> a<br> <b>" . $request['cognome'] . " " . $request['nome'] . "</b>?</div>";
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
                $('<div id =\"praConfermaCaricaAcl\">$content</div>').dialog({
                title:\"Caricamento Nuova Regola.\",
                bgiframe: true,
                resizable: false,
                height: 'auto',
                width: 'auto',
                modal: true,
                close: function(event, ui) {
                    $(this).dialog('destroy');
                },
                buttons: [
                    {
                        text: 'No',
                        class: 'italsoft-button italsoft-button--secondary',
                        click:  function() {
                            $(this).dialog('destroy');
                        }
                    },
                    {
                        text: 'Si',
                        class: 'italsoft-button',
                        click:  function() {
                            $(this).dialog('destroy');
                            itaFrontOffice.ajax(ajax.action, ajax.model, 'confermaCaricaNuovaAcl', this, { 
                    ricnum: '" . $request['ricnum'] .
                "', tipoRegola: '" . $request['tipoRegola'] . "', nome: '" . $request['nome'] .
                "', cf: '" . $request['cf'] . "', cognome: '" . $request['cognome'] .
                "', mail: '" . $request['mail'] . "', dataInizio: '" . $request['dataInizio'] .
                "', inserisci: '" . $request['inserisci'] . "', modifica: '" . $request['modifica'] .
                "', cancella: '" . $request['cancella'] . "', seq: '" . $request['seq'] .
                "', dataFine: '" . $request['dataFine'] . "', tipoAttivaACL: '" . $request['tipoAttivaACL'] . "'}); 
                                    
                        }
                    }
                ]
            });";
        $script .= '</script>';
        //location.replace('$url');

        return $script;
    }

    public function salvaAcl($params, $ricnum, $tipoRegola, $seqPasso = '', $aclAttiva = 0) {
        $this->errMessage = "";
        $this->errCode = 0;

        /*
         * Prendo i Dati
         */
        if ($seqPasso) {
            $dati = $this->praLibDati->prendiDati($ricnum, $seqPasso);
        } else {
            $dati = $this->praLibDati->prendiDati($ricnum, '', '', true);
        }

        if (!$dati) {
            $this->errMessage = $this->praLibDati->getErrMessage();
            return false;
        }

        $cf = $cognome = $nome = $mail = $inizio = $fine = "";
        foreach ($params as $campo) {
            switch ($campo['chiave']) {
                case 'CodiceFiscale':
                    $cf = strtoupper($campo['valore']);
                    break;
                case 'Cognome':
                    $cognome = $campo['valore'];
                    break;
                case 'Nome':
                    $nome = $campo['valore'];
                    break;
                case 'Mail':
                    $mail = $campo['valore'];
                    break;
                case 'dataInizio':
                    $inizio = $campo['valore'];
                    break;
                case 'dataFine':
                    $fine = $campo['valore'];
                    break;
                default:
                    break;
            }
        }



        /*
         * Inserisco nuovo soggetto su RICSOGGETTI, se non è presente
         */
        $arraySoggetto = array(
            'SOGRICNUM' => $dati['Proric_rec']['RICNUM'],
            'SOGRICUUID' => $dati['Proric_rec']['RICUUID'],
            'SOGRICFIS' => $cf,
            'SOGRICDENOMINAZIONE' => $cognome . " " . $nome,
            'SOGRICRUOLO' => '',
            'SOGRICRICDATA_INIZIO' => date("Ymd"),
            'SOGRICDATA_FINE' => '',
            'SOGRICNOTE' => ''
        );

        if (!$this->praLibAcl->caricaSoggetto($arraySoggetto, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'])) {
            $this->errCode = -1;
            $this->errMessage = $this->praLibAcl->getErrMessage();
            return false;
        }

        $arrCodici = array('ricnum' => $ricnum,
            'ruolo' => '',
            'cf' => $cf);
        $ricSoggetti_rec = $this->praLib->GetRicsoggetti($arrCodici, 'soggetto', $dati['PRAM_DB'], false);
        if (!$ricSoggetti_rec) {
            $this->errCode = -1;
            $this->errMessage = "Caricamento Condivisione fallito. Problemi nel ritrovare il soggetto inserito.";
            return false;
        }
        $idRicSoggetti = $ricSoggetti_rec['ROW_ID'];


        $tipoMailSog = "ACL_ASSEGNATARIO_PASSO";
        $tipoMailEsib = "ACL_DICH_PASSO";
        if ($tipoRegola == 'Integrazione') {
            $arrayMeta = array(
                'AUTORIZZAZIONE' => array(
                    array(
                        'TIPO_AUTORIZZAZIONE' => 'GESTIONE_RICHIESTA',
                        'VISUALIZZA' => 1,
                        'INSERISCI' => 1,
                        'MODIFICA' => 0,
                        'CANCELLA' => 0,
                        'INTEGRAZIONE_RICHIESTA' => 1,
                    ),
                ),
            );
            $tipoMailSog = "ACL_ASSEGNATARIO_INTEG";
            $tipoMailEsib = "ACL_DICH_INTEG";
            $aclAttiva = 2;
        } else if ($tipoRegola == 'Visualizzazione') {
            $arrayMeta = array(
                'AUTORIZZAZIONE' => array(
                    array(
                        'TIPO_AUTORIZZAZIONE' => 'GESTIONE_RICHIESTA',
                        'VISUALIZZA' => 1,
                        'INSERISCI' => 1,
                        'MODIFICA' => 1,
                        'CANCELLA' => 1,
                    ),
                ),
            );
            $tipoMailSog = "ACL_ASSEGNATARIO_VISUAL";
            $tipoMailEsib = "ACL_DICH_VISUAL";
        } else {
            $valIns = 1;
            $valEdit = 1;
            $valDel = 1;
//            if ($this->request['inserisci']) {
//                $valIns = 1;
//            }
//            if ($this->request['modifica']) {
//                $valEdit = 1;
//            }
//            if ($this->request['cancella']) {
//                $valDel = 1;
//            }

            $arrayMeta = array(
                'AUTORIZZAZIONE' => array(
                    array(
                        'TIPO_AUTORIZZAZIONE' => 'GESTIONE_PASSO',
                        'ROW_ID_PASSO' => $dati['Ricite_rec']['ROWID'],
                        'VISUALIZZA' => 1,
                        'INSERISCI' => $valIns,
                        'MODIFICA' => $valEdit,
                        'CANCELLA' => $valDel,
                    ),
                ),
            );
            $tipoMailSog = "ACL_ASSEGNATARIO_PASSO";
            $tipoMailEsib = "ACL_DICH_PASSO";
            $aclAttiva = 1;
        }

        $jsonMeta = json_encode($arrayMeta);


        $arrayRicAcl = array(
            'ROW_ID_RICSOGGETTI' => $idRicSoggetti,
            'ROW_ID_PASSO' => $dati['Ricite_rec']['ROWID'],
            'RICACLMETA' => $jsonMeta,
            'RICACLDATA' => date("Ymd"),
            'RICACLORA' => date("H:i:s"),
            'RICACLDATA_INIZIO' => frontOfficeLib::converti($inizio),
            'RICACLDATA_FINE' => frontOfficeLib::converti($fine),
            'RICACLNOTE' => '',
            'RICACLTRASHED' => 0,
            'RICACLATTIVA' => $aclAttiva,
        );


        /**
         * Si carica RICACL
         */
        if (!$this->praLibAcl->caricaAcl($arrayRicAcl, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'], $cf)) {
            $this->errCode = -1;
            $this->errMessage = $this->praLibAcl->getErrMessage();
            return false;
        }


        /**
         * Invia la mail al soggetto assegnatario dell'ACL per avveertimento
         */
        $msgErr1 = $this->praLib->InvioMailSoggetto($dati, $tipoMailSog, $cognome . " " . $nome, $mail);
        if ($msgErr1) {
            $this->errCode = -2;
            $this->errMessage = "$msgErr1 richiesta n. " . $dati['Proric_rec']['RICNUM'] . "\n";
        }


        /**
         * Si invia la mail di notifica conferma operazione al Dichiarante
         */
        $msgErr2 = $this->praLib->InvioMailSoggetto($dati, $tipoMailEsib, $cognome . " " . $nome, $mail);
        if ($msgErr2) {
            $this->errCode = -2;
            $this->errMessage .= "$msgErr2 richiesta n. " . $dati['Proric_rec']['RICNUM'];
        }


        return true;
    }

    public function addJsConfermaCancella($request) {
        $request['event'] = 'confermaCancellazioneAcl';
        $url = ItaUrlUtil::GetPageUrl($request);
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= '<b>ATTENZIONE:<br></b><br>';
        $content .= "<b>Si vuole confermare la cancellazione </b>?</div>";
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
                $('<div id =\"praConfermaCancAcl\">$content</div>').dialog({
                title:\"Cancellazione.\",
                bgiframe: true,
                resizable: false,
                height: 'auto',
                width: 'auto',
                modal: true,
                close: function(event, ui) {
                    $(this).dialog('destroy');
                },
                buttons: [
                    {
                        text: 'No',
                        class: 'italsoft-button italsoft-button--secondary',
                        click:  function() {
                            $(this).dialog('destroy');
                        }
                    },
                    {
                        text: 'Si',
                        class: 'italsoft-button',
                        click:  function() {
                            $(this).dialog('destroy');
                            itaFrontOffice.ajax(ajax.action, ajax.model, 'confermaCancellazioneAcl', this, { ricnum: '" . $request['ricnum'] . "', idricacl: " . $request['idricacl'] . ", cfSoggetto: '" . $request['cfSoggetto'] . "' }); 
                        }
                    }
                ]
            });";
        $script .= '</script>';
        //location.replace('$url');

        return $script;
    }

}
