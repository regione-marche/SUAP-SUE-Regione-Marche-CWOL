<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praCambioEsibente.php';

class praVis extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    private $praCambioEsibente;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
            $this->praCambioEsibente = new praCambioEsibente();

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        if ($this->praCambioEsibente) {
            $this->praCambioEsibente->parseEvent();
        }

        $userFiscale = frontOfficeApp::$cmsHost->getCodFisFromUtente();
        $userName = frontOfficeApp::$cmsHost->getUserName();

        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
        $params = array(
            'fiscale' => $datiUtente['ESIBENTE_CODICEFISCALE_CFI'],
            'email' => $datiUtente['ESIBENTE_EMAIL']
        );

        if ($userFiscale == "") {
            $alertMessage = sprintf('L\'utente %s non è adatto per accedere alla consultazione richieste perchè manca il codice fiscale nel suo profilo.', $userName);
            output::addAlert($alertMessage, 'Attenzione', 'warning');
            return output::$html_out;
        }

        $templateClass = 'praHtmlVis';
        if ($this->config['template']) {
            if (file_exists(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/' . $this->config['template'] . '.class.php')) {
                $templateClass = $this->config['template'];
            }
        }

        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praGestAcl.php');

        require_once ITA_PRATICHE_PATH . "/PRATICHE_italsoft/$templateClass.class.php";
        $praHtmlVis = new $templateClass;

        switch ($this->request['event']) {
            case 'Stampa':
                if (!$this->frontOfficeLib->vediAllegato($this->request['file'])) {
                    return output::$html_out = $this->praErr->parseError(__FILE__, 'E0058', "Errore Apertura ricevuta annullamento pratica", __CLASS__);
                }

                break;

            case 'invioMail':
                if (trim($this->request['motivo'])) {
                    $ricnum = $this->request['ricnum'];
                    if ($ricnum == "") {
                        return output::$html_out = $this->praErr->parseError(__FILE__, 'E0046', "Numero pratica non trovato", __CLASS__);
                    }
                    $dati['Proric_rec'] = $Proric_rec = $this->praLib->GetProric($ricnum, 'codice', $this->PRAM_DB);
                    $dati['Ananom_rec'] = $Ananom_rec = $this->praLib->GetAnanom($Proric_rec['RICRES'], 'codice', $this->PRAM_DB);
                    $dati['Anapra_rec'] = $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], 'codice', $this->PRAM_DB);
                    $dati['Anatsp_rec'] = $Anatsp_rec = $this->praLib->GetAnatsp($Proric_rec['RICTSP'], 'codice', $this->PRAM_DB);
                    $dati['Anaspa_rec'] = $Anaspa_rec = $this->praLib->GetAnaspa($Proric_rec['RICSPA'], 'codice', $this->PRAM_DB);
                    $dati['Ricdag_tab_totali'] = $this->praLib->GetRicdag($Proric_rec['RICNUM'], 'codice', $this->PRAM_DB, true);

                    //
                    //leggo i dati per invio mail
                    //
                    $dati['CartellaMail'] = $this->praLib->getCartellaRepositoryPratiche($ricnum . "/mail");
                    $dati['CartellaAllegati'] = $this->praLib->getCartellaAttachmentPratiche($ricnum);
                    $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB, $params);

                    //
                    //Scrivo il file XMLINFOANN
                    //
                    if (!$this->praLib->CreaXMLINFO("ANNULLAMENTO-RICHIESTA", $dati)) {
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Creazione file XMLINFOANN fallita per la pratica n. " . $dati['Proric_rec']['RICNUM'], __CLASS__);
                        return false;
                    }

                    //******************************MAIL RESPONSABILE********************\\
//                    $ErrorMailResp = $this->praLib->InvioMailResponsabile($dati, "", $this->PRAM_DB, $arrayDatiMail, "ANNULLAMENTO-RICHIESTA");
//                    if ($ErrorMailResp != true) {
//                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0047', "Invio richiesta al responsabile per annullamento pratica n. " . $ricnum . " fallito - " . $itaMailer->ErrorInfo, __CLASS__);
//                        return false;
//                    }
                    //$ErrorMail = $this->praLib->InvioMailResponsabile($dati, "", $this->PRAM_DB, $arrayDatiMail, "ANNULLAMENTO-RICHIESTA");
                    $ErrorMail = $this->praLib->InvioMailAnnullamentoResponsabile($dati, $this->PRAM_DB, $arrayDatiMail, "ANNULLAMENTO-RICHIESTA");
                    if ($ErrorMail != 1) {
                        $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al resposansabile comunale.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0052', "Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                        return false;
                    }

                    //******************************MAIL RICHIEDENTE********************\\
                    $mailRich = $this->praLib->GetMailRichiedente("ANNULLAMENTO-RICHIESTA", $dati['Ricdag_tab_totali']);
//                    $ErrorMailRich = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, "ANNULLAMENTO-RICHIESTA");
//                    if ($ErrorMailRich != true) {
//                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0047', "Invio richiesta al richiedente per annullamento pratica n. " . $ricnum . " fallito - " . $itaMailer->ErrorInfo, __CLASS__);
//                        return false;
//                    }
                    //$ErrorMail = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, "ANNULLAMENTO-RICHIESTA");
                    $ErrorMail = $this->praLib->InvioMailAnnullamentoRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, "ANNULLAMENTO-RICHIESTA");
                    if ($ErrorMail != 1) {
                        $msgErrResp = "Impossibile inviare momentaneamente la mail relativa alla richiesta n. " . $dati['Proric_rec']['RICNUM'] . " al richiedente.<b>
                                           Riprovare piu tardi.<br>
                                           Se il problema persiste contatatre l'assistenza software.";
                        output::$html_out = $this->praErr->parseError(__FILE__, 'E0050', "Invio mail richiedente pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail, __CLASS__, $msgErrResp, true);
                        return false;
                    }
                    $htmlOutput = $this->praLib->GetHtmlOutput($dati, $arrayDatiMail['bodyAnnullamento'], "bodyAnn.html", "praVis");
                    output::appendHtml($htmlOutput);
                    //
                    //Aggiorno lo stato della pratica come annullata
                    //
//                        $Proric_rec['RICSTA'] = '98';
//                        $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $Proric_rec);
//                        if ($nRows == -1) {
//                            praMup::$html_out = $this->praErr->parseError(__FILE__, 'E0100', $e->getMessage() . " Errore aggiornamento Pratica. " . $Proric_rec['RICNUM'], __CLASS__);
//                            return false;
//                        }
//                    }
                } else {
                    output::appendHtml("<script type=\"text/javascript\">alert(\"Indicare il motivo dell'annullamento\"); history.go(-1)</script>");
                }
                break;

            case 'sceltaMotivo':
                output::addForm(ItaUrlUtil::GetPageUrl(array()), 'POST', array(
                    'class' => 'italsoft-form--fixed',
                    'id' => 'form1'
                ));

                output::addHidden('event', 'invioMail');
                output::addHidden('ricnum', $this->request['ricnum']);

                output::addInput('textarea', 'Indicare il motivo dell\'annullamento', array(
                    'name' => 'motivo',
                    'cols' => 50,
                    'rows' => 3
                ));

                output::addBr();

                output::addSubmit('Invio richiesta');

                output::closeTag('form');
                break;

            case 'annullaInfocamere':
                $proric_rec = $this->praLib->GetProric($this->request['ricnum'], "codice", $this->PRAM_DB);
                $ricite_rec_infocamere = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $proric_rec['RICNUM'] . "' AND ITEZIP = 1", false);
                if (!$ricite_rec_infocamere) {
                    praMup::$html_out = $this->praErr->parseError(__FILE__, 'E0101', "Passo infocamere non trovato per la Pratica. " . $proric_rec['RICNUM'], __CLASS__);
                    break;
                }
                $arrayNote = unserialize($ricite_rec_infocamere['RICNOT']);

                /*
                 * Lettura parametro per nascondere la busta dell'annullamento
                 */
                $nascondi = false;
                $anaparBloccaAnnullamento_rec = $this->praLib->GetAnapar('BLOCK_ANNULLA_RICHIESTA', 'parkey', $this->PRAM_DB, false);
                if ($anaparBloccaAnnullamento_rec['PARVAL'] == "Si") {
                    $nascondi = true;
                }

                if ($proric_rec['RICSTA'] == "91" && isset($arrayNote['INFOCAMERE'])) {
                    //Rimetto lo stato in corso e rimetto l'ultimo passo come non fatto
                    $proric_rec['RICSTA'] = "99";
                    $proric_rec['RICSEQ'] = str_replace("." . $ricite_rec_infocamere['ITESEQ'] . ".", "", $proric_rec['RICSEQ']);
                    $nRows = ItaDB::DBUpdate($this->PRAM_DB, 'PRORIC', 'ROWID', $proric_rec);
                    if ($nRows == -1) {
                        praMup::$html_out = $this->praErr->parseError(__FILE__, 'E0100', "Errore aggiornamento Pratica. " . $proric_rec['RICNUM'], __CLASS__);
                        break;
                    }
                    //Aggiorno le note del passo e loggo i dati dell'annullamento
                    $i = count($arrayNote['ANNULLATIINFOCAMERE']) + 1;
                    $arrayNote['ANNULLATIINFOCAMERE'][$i]["NOTE"] = $arrayNote["NOTE"];
                    $arrayNote['ANNULLATIINFOCAMERE'][$i]["INFOCAMERE"] = $arrayNote["INFOCAMERE"];
                    $arrayNote['ANNULLATIINFOCAMERE'][$i]["DATA"] = date("d/m/Y");
                    $arrayNote['ANNULLATIINFOCAMERE'][$i]["ORA"] = date("H:i:s");
                    unset($arrayNote['NOTE']);
                    unset($arrayNote['INFOCAMERE']);
                    $ricite_rec_infocamere['RICNOT'] = serialize($arrayNote);
                    $nRows2 = ItaDB::DBUpdate($this->PRAM_DB, 'RICITE', 'ROWID', $ricite_rec_infocamere);
                    if ($nRows2 == -1) {
                        praMup::$html_out = $this->praErr->parseError(__FILE__, 'E0102', "Errore aggiornamento Passo Infocamere per la  Pratica. " . $proric_rec['RICNUM'], __CLASS__);
                        break;
                    }
                    //$this->DisegnaPagina($userFiscale, $html);
                    $dati = array();
                    $dati['fiscale'] = $userFiscale;
                    $extraParams = array("MAINCLASS" => "praGestPassi", "CLASS" => "praVis", "PRAM_DB" => $this->PRAM_DB, "Tipo" => $this->request['tipo'], "HideAnnullaIcon" => $nascondi);
                    $extraParams['Numero'] = $this->request['numero'];
                    $extraParams['Anno'] = $this->request['anno'];
                    $extraParams["Impresa"] = $this->request['denomImpresa'];
                    output::appendHtml($praHtmlVis->DisegnaPagina($dati, $extraParams));
                } else {
                    output::appendHtml("<div style=\"display:none\" class=\"ita-alert\" title=\"Annullamento Richiesta Infocamere!\">
                                          <p style=\"padding:5px;color:red;font-size:1.2em;\"><b>La richiesta n. " . $proric_rec['RICSTA'] . " risulta già sbloccata.</b></p>
                                       </div>");
                    //$this->DisegnaPagina($userFiscale, $html);
                    $dati = array();
                    $dati['fiscale'] = $userFiscale;
                    $dati = array();
                    $dati['fiscale'] = $userFiscale;
                    $extraParams = array("MAINCLASS" => "praGestPassi", "CLASS" => "praVis", "PRAM_DB" => $this->PRAM_DB, "Tipo" => $this->request['tipo'], "HideAnnullaIcon" => $nascondi);
                    $extraParams['Numero'] = $this->request['numero'];
                    $extraParams['Anno'] = $this->request['anno'];
                    $extraParams["Impresa"] = $this->request['denomImpresa'];
                    output::appendHtml($praHtmlVis->DisegnaPagina($dati, $extraParams));
                    break;
                }
                break;
            case 'tablePager':
                $dati = array();
                $dati['fiscale'] = $userFiscale;

                /*
                 * Lettura parametro per nascondere la busta dell'annullamento
                 */
                $nascondi = false;
                $anaparBloccaAnnullamento_rec = $this->praLib->GetAnapar('BLOCK_ANNULLA_RICHIESTA', 'parkey', $this->PRAM_DB, false);
                if ($anaparBloccaAnnullamento_rec['PARVAL'] == "Si") {
                    $nascondi = true;
                }

                $extraParams = array_merge(
                        array(
                            "MAINCLASS" => "praGestPassi",
                            "CLASS" => "praVis",
                            "PRAM_DB" => $this->PRAM_DB,
                            "Tipo" => $this->request['tipo'],
                            "config" => $this->config,
                            "HideAnnullaIcon" => $nascondi,
                            'Ajax' => true
                        ), $this->request
                );

                $extraParams['Tipo'] = $extraParams['tipo'];
                $extraParams['Numero'] = $extraParams['numero'];
                $extraParams['Anno'] = $extraParams['anno'];
                $extraParams['Impresa'] = $extraParams['denomImpresa'];

                $data = $praHtmlVis->getTableData($dati, $extraParams);
                foreach ($data[1] as &$record) {
                    foreach ($record as &$value) {
                        $value = utf8_encode($value);
                    }
                }
                echo json_encode($data);
                exit;
            case 'onChange':
                switch ($this->request['id']) {
                    case 'cfCambioEsibente':
                        $this->praCambioEsibente->request['event'] = 'ctrlCf';
                        $this->praCambioEsibente->request['ricnum'] = $this->request['ricnum'];
                        $this->praCambioEsibente->parseEvent();
//                        output::ajaxSendResponse();
                        break;
                    case 'cfAcl':
                        $praGestAcl = new praGestAcl();
                        $praGestAcl->request['event'] = 'ctrlCf';
                        $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                        $praGestAcl->parseEvent();
                        break;
                }

                break;
            case'gestACL':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'openBlock';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                output::responseCloseCurrentDialog();
                output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case'addACL':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'addACL';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case 'pulisciSoggettoAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'pulisciSoggettoAcl';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                $praGestAcl->parseEvent();

                break;
            case 'caricaNuovaAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'caricaNuovaAcl';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                $praGestAcl->request['tipoRegola'] = $this->request['tipoRegola'];
                $praGestAcl->parseEvent();
                break;
            case 'confermaCaricaNuovaAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'confermaCaricaNuovaAcl';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                $praGestAcl->request['tipoRegola'] = $this->request['tipoRegola'];
                $praGestAcl->parseEvent();
                break;
            case 'confermaModificaAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'confermaModificaACL'; 
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                $praGestAcl->request['tipoRegola'] = $this->request['tipoRegola'];
                $praGestAcl->parseEvent();

                break;
            case'gestIntegrazione':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'gestACLIntegrazione';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                Output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case 'gestVisualizzazione':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'gestACLVisualizzazione';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                Output::ajaxResponseHtml($praGestAcl->parseEvent());
                
                break;
            case'gestPasso':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'gestACLPasso';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                Output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case'confermaPasso':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'confermaACLPasso';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                Output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case'disegnaPassiDisponibili':

                /*
                 * Leggo l'array DATI
                 */
                $praLibDati = praLibDati::getInstance($this->praLib);
                $dati = $praLibDati->prendiDati($this->request['ricnum']);
                if (!$dati) {
                    break;
                }

                require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridPassi.class.php';
                $praHtmlGridPassi = new praHtmlGridPassi();
                //output::ajaxResponseDialog($this->disegnaFormPassiDisponibili($this->request['ricnum']), array(
                output::ajaxResponseDialog($praHtmlGridPassi->GetGridPassiDisponibili($dati, $this->config['online_page']), array(
                    'title' => 'Passi Assegnati',
                    'width' => 600
                ));
                output::ajaxSendResponse();
                break;
            case 'modificaAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'modificaACL';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                Output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            case 'cessaAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'cessaACL';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                $praGestAcl->parseEvent();
                break;
            case 'confermaCancellazioneAcl':
                $praGestAcl = new praGestAcl();
                $praGestAcl->setConfig($this->config);
                $praGestAcl->request['event'] = 'confermaCancellazioneACL';
                $praGestAcl->request['ricnum'] = $this->request['ricnum'];
                output::responseCloseCurrentDialog();
                output::ajaxResponseHtml($praGestAcl->parseEvent());
                break;
            default:
                $this->DisegnaGriglia($userFiscale, $praHtmlVis);
//                $dati = array();
//                $dati['fiscale'] = $userFiscale;
//
//                /*
//                 * Lettura parametro per nascondere la busta dell'annullamento
//                 */
//                $nascondi = false;
//                $anaparBloccaAnnullamento_rec = $this->praLib->GetAnapar('BLOCK_ANNULLA_RICHIESTA', 'parkey', $this->PRAM_DB, false);
//                if ($anaparBloccaAnnullamento_rec['PARVAL'] == "Si") {
//                    $nascondi = true;
//                }
//
//                $extraParams = array(
//                    "MAINCLASS" => "praGestPassi",
//                    "CLASS" => "praVis",
//                    "PRAM_DB" => $this->PRAM_DB,
//                    "Tipo" => $this->request['tipo'],
//                    "config" => $this->config,
//                    "HideAnnullaIcon" => $nascondi,
//                    'Ajax' => true
//                );
//
//                $extraParams['Numero'] = $this->request['numero'];
//                $extraParams['Anno'] = $this->request['anno'];
//                $extraParams["Impresa"] = $this->request['denomImpresa'];
//
//                $eqDesc = sprintf('Aperta consultazione richieste');
//                eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));
//
//                $praHtmlVis->DisegnaPagina($dati, $extraParams);
                break;
        }

//        output::appendHtml("</form>");
        return output::$html_out;
    }

    private function disegnaGriglia($userFiscale, $praHtmlVis) {
        $dati = array();
        $dati['fiscale'] = $userFiscale;

        /*
         * Lettura parametro per nascondere la busta dell'annullamento
         */
        $nascondi = false;
        $anaparBloccaAnnullamento_rec = $this->praLib->GetAnapar('BLOCK_ANNULLA_RICHIESTA', 'parkey', $this->PRAM_DB, false);
        if ($anaparBloccaAnnullamento_rec['PARVAL'] == "Si") {
            $nascondi = true;
        }

        $extraParams = array(
            "MAINCLASS" => "praGestPassi",
            "CLASS" => "praVis",
            "PRAM_DB" => $this->PRAM_DB,
            "Tipo" => $this->request['tipo'],
            "config" => $this->config,
            "HideAnnullaIcon" => $nascondi,
            'Ajax' => true
        );

        $extraParams['Numero'] = $this->request['numero'];
        $extraParams['Anno'] = $this->request['anno'];
        $extraParams["Impresa"] = $this->request['denomImpresa'];

        $eqDesc = sprintf('Aperta consultazione richieste');
        eqAudit::logEqEvent(get_class(), array('DB' => '', 'DSet' => '', 'Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $eqDesc, 'Key' => ''));

        $praHtmlVis->DisegnaPagina($dati, $extraParams);
    }

//    public function disegnaFormPassiDisponibili($ricnum) {
//        $html = new html();
//
//        /*
//         * Leggo l'array DATI
//         */
//        $praLibDati = praLibDati::getInstance($this->praLib);
//        $dati = $praLibDati->prendiDati($ricnum, '', '');
//        if (!$dati) {
//            return false;
//        }
//
//        /*
//         * Mi trovo i passi disponibili tramite le ACL
//         */
//        $arrPassiDisponibili = array();
//        foreach ($dati['Ricacl_tab'] as $acl_rec) {
//            if ($acl_rec['RICACLMETA']) {
//                $arrAcl = json_decode($acl_rec['RICACLMETA'], true);
//                if (is_array($arrAcl)) {
//                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
//                        if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
//                            $arrPassiDisponibili[] = $this->praLib->GetRicite($autorizzazione['ROW_ID_PASSO'], "rowid", $dati["PRAM_DB"], false);
//                        }
//                    }
//                }
//            }
//        }
//
//        /*
//         * Disegno la tabella
//         */
//        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNavOriz.class.php');
//        $praHtmlNavOriz25 = new praHtmlNavOriz25();
//
//
//        $tableData = array('header' => array(
//                //'Numero', 'Descrizione', 'Tipologia'
//                'Numero', 'Descrizione'
//            ), 'body' => array(), 'style' => array(
//                'header' => array('width: 5%;', 'width: 85%;', 'width: 30%;')
//        ));
//
//        $html->appendHtml("<div id=\"divElencoPassi\" style=\"display: inline-block; padding-bottom: 1.6em;\">");
//
//        foreach ($arrPassiDisponibili as $passo) {
//            foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $record) {
//
//                if ($passo['ROWID'] == $record['ROWID']) {
//
//                    if ($record['RICOBL'] != 0 || $record['CLTOBL'] != 0) {
//                        $title = 'Obbligatorio';
//                    } else {
//                        $title = 'Facoltativo';
//                    }
//
//                    if ($record['ITEQST'] != 0) {
//                        $title = 'Domanda';
//                    }
//
//                    if ($record['ITEIRE'] != 0 || $record['ITEZIP'] != 0) {
//                        $title = 'Invio Richiesta';
//                    }
//
//                    if (strpos($dati['Proric_rec']['RICSEQ'], '.' . $record['ITESEQ'] . '.') !== false) {
//                        $title = 'Eseguito';
//                    }
//
//                    $href_indice = ItaUrlUtil::GetPageUrl(array(
//                                'p' => $this->config['online_page'],
//                                'event' => 'navClick',
//                                'seq' => $record['ITESEQ'],
//                                'direzione' => '',
//                                'ricnum' => $ricnum
//                    ));
//
//
//                    $labelPasso = '<span class="italsoft-button italsoft-button--circled" style="font-size: .4em; margin: 0 .6em 0 1em; vertical-align: middle; background-color: ' . $praHtmlNavOriz25->colorePasso[$title] . ';"></span>';
//
//                    $tableData['body'][] = array(
//                        $labelPasso . '<span style="vertical-align: middle; font-weight: bold;">' . ($key + 1) . '</span>',
//                        '<a title="Vai al passo" href="' . $href_indice . '">' . $record['ITEDES'] . '</a>'
//                    );
//                }
//            }
//        }
//
//        $html->addTable($tableData, array(
//            'sortable' => true,
//            'paginated' => false,
//        ));
//
//        $html->appendHtml("</div>");
//
//        return $html->getHtml();
//    }
}
