<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';

class praCambioEsibente extends itaModelFO {

    public $praLib;
    public $praLibDati;
    public $praLibAcl;
    public $PRAM_DB;
    private $errCode;
    private $errMessage;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praLib = new praLib();
            $this->praLibAcl = new praLibAcl();
            $this->praLibDati = praLibDati::getInstance($this->praLib);
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function __destruct() {
        $_SESSION['rsnAuthAllegati'] = $this->allegati;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function parseEvent() {
        $html = new html();
        switch ($this->request['event']) {
            case 'onClick':
                break;
//            GESTITO IN pravis
//            case 'onChange':
//                $info = $err = '';
//                $nomeUtente = frontOfficeApp::$cmsHost->getUtenteFromCodFis($this->request['cf']);
//                if ($nomeUtente !== false) {
//                    $info = $html->getAlert("", "Utente Registrato", "info");
//                    $datiNuovoEsibente = frontOfficeApp::$cmsHost->getDatiDaUtente($nomeUtente);
//
//                    /**
//                     * Svuoto eventuali valori presenti nelle caselle di testo
//                     */
//                    $this->request['cognome'] = '';
//                    $this->request['nome'] = '';
//                    $this->request['mail'] = '';
//
//                    $params = $this->getDatiCambioEsibente($datiNuovoEsibente);
//                    $arrDati = array();
//                    foreach ($params as $campo) {
//                        $arrDati[$campo['nomeCampo']] = "";
//                        $arrDati[$campo['nomeCampo']] = $campo['valore'];
//                        output::responseDisableField($campo['nomeCampo']);
//                    }
//                    output::ajaxResponseValues($arrDati);
//                } else {
//                    $info = $html->getAlert("Compilare con attenzione i campi e dopo il cambio esibente e' opportuno registrarsi al portale.", "Utente non Registrato", "info");
////                    output::responseHtml($info, 'divInfoCambioEsibente');
//                    $praLibDatiAggiuntivi = new praLibDatiAggiuntivi();
//                    if (!$praLibDatiAggiuntivi->controllaValidoSe("CodiceFiscalePiva", $this->request['cf'])) {
//                        $err = $html->getAlert("Il campo <b>Codice Fiscale/P. Iva</b> contiene un valore non valido.", '', "error");
//                    }
////                    output::responseHtml($err, 'divErrorCambioEsibente');
//                }
//                output::responseHtml($info, 'divInfoCambioEsibente');
//                output::responseHtml($err, 'divErrorCambioEsibente');
//                output::ajaxSendResponse();
//                break;

            case 'ctrlCf':
//                file_put_contents("d:/works/request-ctrlCf.txt", print_r($this->request, true));
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

                    $params = $this->getDatiCambioEsibente($datiNuovoEsibente);
                    $arrDati = array();
                    foreach ($params as $campo) {
                        $arrDati[$campo['nomeCampo']] = "";
                        $arrDati[$campo['nomeCampo']] = $campo['valore'];
                        output::responseDisableField($campo['nomeCampo']);
                    }
                    output::ajaxResponseValues($arrDati);
                } else {
                    $info = $html->getAlert("Compilare con attenzione i campi e dopo il cambio esibente e' opportuno registrarsi al portale.", "Utente non Registrato", "info");
//                    output::responseHtml($info, 'divInfoCambioEsibente');
                    $praLibDatiAggiuntivi = new praLibDatiAggiuntivi();
                    if (!$praLibDatiAggiuntivi->controllaValidoSe("CodiceFiscalePiva", $this->request['cf'])) {
                        $err = $html->getAlert("Il campo <b>Codice Fiscale/P. Iva</b> contiene un valore non valido.", '', "error");
                    }
//                    output::responseHtml($err, 'divErrorCambioEsibente');
                }
                output::responseHtml($info, 'divInfoCambioEsibente');
                output::responseHtml($err, 'divErrorCambioEsibente');
                output::ajaxSendResponse();
                break;

            case 'disegnaCambiaEsibente':
                $Proric_rec = $this->praLib->GetProric($this->request['ricnum'], 'codice', $this->PRAM_DB);
                if ($this->request['ruolo'] != praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'] || ($Proric_rec['RICSTA'] != '01' && $Proric_rec['RICSTA'] != '91')) {
                    output::addAlert("Impossibile cambiare l'esibente.", 'Attenzione', 'error');
                    break;
                }

                $params = $this->getDatiCambioEsibente();
                output::ajaxResponseDialog($this->disegnaForm($this->request['ricnum'], $params), array(
                    'title' => 'Cambio Esibente',
                    'width' => 600
                ));
                output::ajaxSendResponse();
                break;
            case 'cambiaEsibente':
                $params = $this->getDatiCambioEsibente();
                $error = "";
                if (!$this->controlloPreCambioEsibente($params)) {
                    $error = $html->getAlert($this->getErrMessage(), "Attenzione", "error");
                    output::responseHtml($error, 'divErrorCambioEsibente');
                    output::ajaxSendResponse();
                    break;
                }

                $proric_rec = $this->praLib->GetProric($this->request['ricnum'], "codice", $this->PRAM_DB);
                $js = $this->addJsConfermaCambio($this->request, $proric_rec);
                $html->appendHtml($js);
                output::ajaxResponseHtml($html->getHtml(), null, 'append');
                output::ajaxSendResponse();
                break;
            case 'confermaCambioEsibente':
                $params = $this->getDatiCambioEsibente();
                if (!$this->cambioEsibente($params, $this->request['ricnum'])) {
                    output::addAlert($this->getErrMessage(), 'Attenzione', 'error');
                }

                if ($this->getErrCode() == -2) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0222', "Errore invio mail cambio esibente: " . $this->getErrMessage(), __CLASS__, "", false);
                }

                $richiesta = substr($this->request['ricnum'], 4) . "/" . substr($this->request['ricnum'], 0, 4);
                output::addAlert("ESIBENTE CAMBIATO CORRETTAMENTE PER LA RICHIESTA N. $richiesta", '', 'info');
                output::responseCloseCurrentDialog();
                break;
        }
    }

    public function disegnaForm($ricnum, $campi) {
        $html = new html();

        $html->appendHtml('<div style="padding: 20px;">');

        $html->addForm('', 'POST', array(
            'class' => 'italsoft-form--top'
                ), true);


        $html->appendHtml("<div id=\"divInfoCambioEsibente\"></div>");
        $html->appendHtml("<div id=\"divErrorCambioEsibente\"></div>");

        $html->addBr();

        $html->addHidden("ricnum", $ricnum);

        foreach ($campi as $campo) {
            $html->addInput('text', $campo['descrizione'] . ' *', array(
                'name' => $campo['nomeCampo'],
                'value' => $campo['valore'],
                'class' => $campo['class'],
                'id' => $campo['id'],
                'size' => '40',
            ));

            $html->addBr();
        }

        $html->addButton("Conferma", '', 'primary', array('event' => 'cambiaEsibente'));

        $html->closeTag('form');

        $html->appendHtml('</div>');
        return $html->getHtml();
    }

    public function controlloPreCambioEsibente($params) {
        $this->errMessage = "";
        $this->errCode = 0;

        $praLibDatiAggiuntivi = new praLibDatiAggiuntivi();

        /*
         * Controllo Presenza 
         */
        foreach ($params as $campo) {
            if (!$campo['valore']) {
                $this->errCode = -1;
                $this->errMessage .= "Campo <b>" . $campo['descrizione'] . "</b> mancante.<br>";
            }
        }

        $cf = $cognome = $nome = $mail = "";
        foreach ($params as $campo) {
            if ($campo['valore']) {
                if (!$praLibDatiAggiuntivi->controllaValidoSe($campo['controllo'], $campo['valore'])) {
                    $this->errCode = -1;
                    $this->errMessage .= "Il campo <b>" . $campo['descrizione'] . "</b> contiene un valore non valido.<br>";
                } else {
                    switch ($campo['chiave']) {
                        case 'CodiceFiscale':
                            $cf = $campo['valore'];
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
                        default:
                            break;
                    }
                }
            }
        }


        if ($this->errCode != 0) {
            return false;
        }
        return true;
    }

    public function cambioEsibente($params, $ricnum) {
        $this->errMessage = "";
        $this->errCode = 0;
        //
        $cf = $cognome = $nome = $mail = "";
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
                default:
                    break;
            }
        }

        /*
         * Prendo i Dati
         */
        $dati = $this->praLibDati->prendiDati($ricnum, '', '', true);
        if (!$dati) {
            $this->errMessage = $this->praLibDati->getErrMessage();
            return false;
        }

        /*
         * Cesso il vecchio esibente
         */
        foreach ($dati['Ricsoggetti_tab'] as $soggetto) {
            if ($soggetto['SOGRICDATA_FINE'] == '' && $soggetto['SOGRICRUOLO'] == praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']) {
                if (!$this->praLibAcl->cessaSoggetto($soggetto, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'])) {
                    $this->errCode = -1;
                    $this->errMessage = $this->praLibAcl->getErrMessage();
                    return false;
                }
            }
        }

        /*
         * Inserisco nuovo soggetto su RICSOGGETTI
         */
        $arraySoggetto = array(
            'SOGRICNUM' => $dati['Proric_rec']['RICNUM'],
            'SOGRICUUID' => $dati['Proric_rec']['RICUUID'],
            'SOGRICFIS' => $cf,
            'SOGRICDENOMINAZIONE' => $cognome . " " . $nome,
            'SOGRICRUOLO' => praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'],
            'SOGRICRICDATA_INIZIO' => date("Ymd"),
            'SOGRICDATA_FINE' => '',
            'SOGRICNOTE' => ''
        );

        if (!$this->praLibAcl->caricaSoggetto($arraySoggetto, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'])) {
            $this->errCode = -1;
            $this->errMessage = $this->praLibAcl->getErrMessage();
            return false;
        }

        /*
         * Sincronizzo Dati Aggiuntivi provenienti dalla Form
         */
        foreach ($dati['Ricdag_tab_Esibente'] as $key => $ricdag_rec) {
            switch ($ricdag_rec['DAGKEY']) {
                case 'ESIBENTE_CODICEFISCALE_CFI':
                    $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $cf;
                    $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $cf;
                    break;
                case 'ESIBENTE_COGNOME':
                    $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $cognome;
                    $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $cognome;
                    break;
                case 'ESIBENTE_NOME':
                    $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $nome;
                    $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $nome;
                    break;
                case 'ESIBENTE_PEC':
                case 'ESIBENTE_EMAIL':
                    $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $mail;
                    $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $mail;
                    break;
                default :
                    $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = '';
                    $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = '';
            }
        }

        /*
         * Controllo se il codice fiscale fornito corrispone ad un Utente Registrato nel FO.
         * Se registrato, Aggiorno gli altri Dati dell'ESIBENTE
         */
        $nomeUtente = frontOfficeApp::$cmsHost->getUtenteFromCodFis($cf);
        if ($nomeUtente !== false) {
            $datiNuovoEsibente = frontOfficeApp::$cmsHost->getDatiDaUtente($nomeUtente);
            foreach ($dati['Ricdag_tab_Esibente'] as $key => $ricdag_rec) {
                switch ($ricdag_rec['DAGKEY']) {
                    case 'ESIBENTE_CMSUSER':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['username'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['username'];
                        break;
                    case 'ESIBENTE_RESIDENZAVIA':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['via'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['via'];
                        break;
                    case 'ESIBENTE_RESIDENZACOMUNE':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['comune'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['comune'];
                        break;
                    case 'ESIBENTE_RESIDENZACAP_CAP':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['cap'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['cap'];
                        break;
                    case 'ESIBENTE_RESIDENZAPROVINCIA_PV':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['provincia'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['provincia'];
                        break;
                    case 'ESIBENTE_ITA_CFTELEMACO':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['cfstarweb'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['cfstarweb'];
                        break;
                    case 'ESIBENTE_TELEFONO':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['phone'] ? $datiNuovoEsibente['phone'] : $datiNuovoEsibente['telephone'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['phone'] ? $datiNuovoEsibente['phone'] : $datiNuovoEsibente['telephone'];
                        break;
                    case 'ESIBENTE_NUMISCRIZIONE':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['numeroiscrizione'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['numeroiscrizione'];
                        break;
                    case 'ESIBENTE_PROVISCRIZIONE':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['sedeordine'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['sedeordine'];
                        break;
                    case 'ESIBENTE_ITA_USERTELEMACO':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['usertelemaco'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['usertelemaco'];
                        break;
                    case 'ESIBENTE_ORDINEISCRIZIONE':
                        $dati['Ricdag_tab_Esibente'][$key]['RICDAT'] = $datiNuovoEsibente['ordineiscrizione'];
                        $dati['Ricdag_tab_Esibente'][$key]['DAGVAL'] = $datiNuovoEsibente['ordineiscrizione'];
                        break;
                }
            }
        }

        foreach ($dati['Ricdag_tab_Esibente'] as $key => $ricdag_rec) {
            try {
                ItaDB::DBUpdate($dati['PRAM_DB'], "RICDAG", 'ROWID', $ricdag_rec);
            } catch (Exception $e) {
                $this->errCode = -1;
                $this->errMessage = "Errore sincronizzazione Dati Aggiuntivi Nuovo Esibente richiesta n. " . $dati['Proric_rec']['RICNUM'] . ": " . $e->getMessage();
                return false;
            }
        }



        /*
         * Sincronizzo PRORIC
         */
        $dati['Proric_rec']['RICFIS'] = $cf;
        $dati['Proric_rec']['RICCOG'] = $cognome;
        $dati['Proric_rec']['RICNOM'] = $nome;
        $dati['Proric_rec']['RICEMA'] = $mail;
        $dati['Proric_rec']['RICVIA'] = "";
        $dati['Proric_rec']['RICCOM'] = "";
        $dati['Proric_rec']['RICCAP'] = "";
        $dati['Proric_rec']['RICPRV'] = "";
        if ($nomeUtente !== false) {
            $dati['Proric_rec']['RICVIA'] = $datiNuovoEsibente['via'];
            $dati['Proric_rec']['RICCOM'] = $datiNuovoEsibente['comune'];
            $dati['Proric_rec']['RICCAP'] = $datiNuovoEsibente['cap'];
            $dati['Proric_rec']['RICPRV'] = $datiNuovoEsibente['provincia'];
        }

        try {
            ItaDB::DBUpdate($dati['PRAM_DB'], "PRORIC", 'ROWID', $dati['Proric_rec']);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = "Errore sincronizzazione PRORIC Nuovo Esibente richiesta n. " . $dati['Proric_rec']['RICNUM'] . ": " . $e->getMessage();
            return false;
        }

        /**
         * Ricarico vettore $dati con i dati aggiornati, dopo le modifiche fatte sopra
         */
        //$dati = $this->praLibDati->prendiDati($ricnum, '', '', true);

        /**
         * Si invia la mail al nuovo Esibente
         */
        $msgErr1 = $this->praLib->InvioMailCambioEsibente($dati, "CAMBIOESIB_ESIBENTE");
        if ($msgErr1) {
            $this->errCode = -2;
            $this->errMessage = "$msgErr1 richiesta n. " . $dati['Proric_rec']['RICNUM'] . "\n";
        }

        /**
         * Si invia la mail di notifica conferma operazione al Dichiarante
         */
        $msgErr2 = $this->praLib->InvioMailCambioEsibente($dati, "CAMBIOESIB_DICH");
        if ($msgErr2) {
            $this->errCode = -2;
            $this->errMessage .= "$msgErr2 richiesta n. " . $dati['Proric_rec']['RICNUM'];
        }


        return true;
    }

    public function addJsConfermaCambio($request, $proric_rec) {
        $request['event'] = 'confermaCambioEsibente';
        $url = ItaUrlUtil::GetPageUrl($request);
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= '<b>ATTENZIONE:<br></b><br>';
        $content .= "<b>Confermi il cambio esibente per la richiesta n. <b>" . $request['ricnum'] . "</b> da<br> <b>" . $proric_rec['RICCOG'] . " " . $proric_rec['RICNOM'] . "</b> a<br> <b>" . $request['cognome'] . " " . $request['nome'] . "</b>?</div>";
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
                $('<div id =\"praConfermaScaricoDRR\">$content</div>').dialog({
                title:\"Cambio Esibente.\",
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
                            itaFrontOffice.ajax(ajax.action, ajax.model, 'confermaCambioEsibente', this, { ricnum: '" . $request['ricnum'] . "', nome: '" . $request['nome'] . "', cf: '" . $request['cf'] . "', cognome: '" . $request['cognome'] . "', mail: '" . $request['mail'] . "' }); 
                        }
                    }
                ]
            });";
        $script .= '</script>';
        //location.replace('$url');

        return $script;
    }

    private function getDatiCambioEsibente($datiNuovoEsibente = array()) {
//        file_put_contents("/tmp/request", print_r($this->request, true));
        return array(
            array(
                'id' => 'cfCambioEsibente',
                'chiave' => 'CodiceFiscale',
                'valore' => $this->request['cf'] ? $this->request['cf'] : $datiNuovoEsibente['fiscale'],
                'descrizione' => 'Codice Fiscale/P. Iva',
                'controllo' => 'CodiceFiscalePiva',
                'nomeCampo' => 'cf',
                'class' => 'italsoft-ajax-onchange',
            ),
            array(
                'chiave' => 'Cognome',
                'valore' => $this->request['cognome'] ? $this->request['cognome'] : $datiNuovoEsibente['cognome'],
                'descrizione' => 'Cognome',
                'controllo' => 'Lettere',
                'nomeCampo' => 'cognome',
            ),
            array(
                'chiave' => 'Nome',
                'valore' => $this->request['nome'] ? $this->request['nome'] : $datiNuovoEsibente['nome'],
                'descrizione' => 'Nome',
                'controllo' => 'Lettere',
                'nomeCampo' => 'nome',
            ),
            array(
                'chiave' => 'Mail',
                'valore' => $this->request['mail'] ? $this->request['mail'] : $datiNuovoEsibente['email'],
                'descrizione' => 'Mail',
                'controllo' => 'email',
                'nomeCampo' => 'mail',
            ),
        );
    }

    

}
