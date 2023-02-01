<?php

class praHtmlInfoPraticaSidebar {

    public function getSidebar($dati, $extraParms) {
        $html = new html();
        $praLib = new praLib();

        /*
         * Controllo se l'utente loggato è l'esibente
         */
        $utenteEsibente = true;
        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
        foreach ($dati['Ricsoggetti_tab'] as $ricsoggetti_rec) {
            if ($ricsoggetti_rec['SOGRICRUOLO'] == praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD']) {
                if ($ricsoggetti_rec['SOGRICFIS'] != $datiUtente['fiscale']) {
                    $utenteEsibente = false;
                }
                break;
            }
        }

        $html->appendHtml('<div class="suap--info-pratica italsoft--bg-primary">');

        $html->appendHtml('<h2 class="italsoft--xs-hidden">');
        $html->appendHtml('<i class="icon ion-help-circled italsoft-icon" style="font-size: .9em; margin-right: 5px;"></i>');
        $html->appendHtml('Informazioni richiesta');
        $html->appendHtml('</h2>');
        $html->appendHtml('<br class="italsoft--xs-hidden" />');

        if ($dati['Proric_rec']['RICSTA'] == "98") {
            $html->appendHtml('<h3 class="italsoft--bg-secondary" style="border-radius: 2px; padding: .2em .5em;">RICHIESTA ANNULLATA</h3>');
            $html->appendHtml('<br />');
        }

        $Proric_rec = $dati['Proric_rec'];

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_RENDER_INFO_RICHIESTA, $dati, $html)) {
            return output::$html_out;
        }

        $textNumRichiesta = $numRichiesta = intval(substr($dati['Proric_rec']['RICNUM'], 4, 6)) . '/' . substr($dati['Proric_rec']['RICNUM'], 0, 4);

        if ($Proric_rec['RICRPA']) {
            $numRichiestaRif = intval(substr($Proric_rec['RICRPA'], 4, 6)) . '/' . substr($Proric_rec['RICRPA'], 0, 4);
            $textNumRichiesta .= '<br /><small>Rif. ' . $numRichiestaRif . '</small>';
        }

        $this->disegnaInfo($html, 'Num. richiesta', $textNumRichiesta, 'style="text-decoration: underline; font-size: 1.15em;"');

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_RENDER_INFO_RICHIESTA_NUMERO, $dati, $html)) {
            return output::$html_out;
        }

        $Proric_accorpate_tab = $praLib->GetRichiesteAccorpate($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);
        if (count($Proric_accorpate_tab)) {
            $textAccorpate = '<ul style="margin: .8em 0 1em; list-style: outside square; padding-left: 1em;">';
            foreach ($Proric_accorpate_tab as $Proric_accorpate_rec) {
                $numeroAccorpata = intval(substr($Proric_accorpate_rec['RICNUM'], 4, 6)) . '/' . substr($Proric_accorpate_rec['RICNUM'], 0, 4);
                $textAccorpate .= '<li style="line-height: 1em;">' . $numeroAccorpata . ' - ' . $Proric_accorpate_rec['PRADES'] . '</li>';
            }
            $textAccorpate .= '</ul>';

            $this->disegnaInfo($html, 'Richieste accorpate (' . count($Proric_accorpate_tab) . ')', $textAccorpate);
        }

        if ($Proric_rec['RICRPA']) {
            $Proges_rec_int = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICRPA'] . "'", false);
            if ($Proges_rec_int) {
                $Propas_tab_integra = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROPAS WHERE PRONUM='" . $Proges_rec_int['GESNUM'] . "' AND PRORIN<>''", true);
                foreach ($Propas_tab_integra as $Propas_rec_integra) {
                    if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                        if ($Propas_rec_integra['PROINI']) {
                            $descStato = "Acquisita dall'ente";
                            $dataAcq = substr($Propas_rec_integra['PROINI'], 6, 2) . "/" . substr($Propas_rec_integra['PROINI'], 4, 2) . "/" . substr($Propas_rec_integra['PROINI'], 0, 4);
                        }
                        if ($Propas_rec_integra['PROFIN']) {
                            $descStato = "Chiusa";
                            $dataChi = substr($Propas_rec_integra['PROFIN'], 6, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 4, 2) . "/" . substr($Propas_rec_integra['PROFIN'], 0, 4);
                        }
                    }
                }
            }
        } else {
            $Proges_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROGES WHERE GESPRA='" . $Proric_rec['RICNUM'] . "'", false);
            if ($Proges_rec) {
                $dataAcq = substr($Proges_rec['GESDRE'], 6, 2) . "/" . substr($Proges_rec['GESDRE'], 4, 2) . "/" . substr($Proges_rec['GESDRE'], 0, 4);

                if ($Proges_rec['GESDCH']) {
                    $dataChi = substr($Proges_rec['GESDCH'], 6, 2) . "/" . substr($Proges_rec['GESDCH'], 4, 2) . "/" . substr($Proges_rec['GESDCH'], 0, 4);
                }

                $Prasta_rec = $praLib->GetPrasta($Proges_rec['GESNUM'], "codice", $extraParms['PRAM_DB']);

                if ($Prasta_rec['STAPST'] != 0) {
                    if ($Proges_rec['GESCLOSE']) {
                        $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
                        $arrayDesc = explode(" - ", $Prasta_rec['STADEX']);
                        $lastDesc = end($arrayDesc);
                        $descStato = $Prasta_rec['STADES'] . " - $lastDesc";
                    } else {
                        $descStato = $Prasta_rec['STADES'];
                    }
                } else {
                    $descStato = "Acquisita dall'ente";
                    if ($Proges_rec['GESDCH']) {
                        $descStato = "Chiusa";
                    }
                }
            }
        }

        if ($descStato) {
            $this->disegnaInfo($html, 'Stato pratica', $descStato);
        }

        if ($dataAcq) {
            $this->disegnaInfo($html, 'Acquisizione', $dataAcq);
        }

        if ($dataChi) {
            $this->disegnaInfo($html, 'Chiusura', $dataChi);
        }

        /* --------------------------------- */

        if ($dati['Propas_rec']) {
            $Proges_rec = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PROGES WHERE GESNUM='" . $dati['Propas_rec']['PRONUM'] . "'", false);
            $anapra_rec = $praLib->GetAnapra($Proges_rec['GESPRO'], "codice", $extraParms['PRAM_DB']);
            $anaset_rec = $praLib->GetAnaset($Proges_rec['GESSTT'], "codice", $extraParms['PRAM_DB']);
            $anaatt_rec = $praLib->GetAnaatt($Proges_rec['GESATT'], "codice", $extraParms['PRAM_DB']);
            $anaeventi_rec = $praLib->GetAnaeventi($Proges_rec['GESEVE'], "codice", $extraParms['PRAM_DB']);

            $html->appendHtml('<h3>Attivato parere per</h3>');
            $Oggetto = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . '<br />' . $anaeventi_rec['EVTDESCR'];
            $this->disegnaInfo($html, 'Pratica SUAP', $anaset_rec['SETDES'] . '<br />' . $anaatt_rec['ATTDES'] . '<br />' . $Oggetto);
            $this->disegnaInfo($html, 'Articolo', $dati['Propas_rec']['PROPTIT']);
            $html->appendHtml('<br />');
        }

        if ($dati['ruolo'] != '') {
            $this->disegnaInfo($html, 'Ruolo', $dati['ruolo'] . " - " . $dati['DescRuolo']);
        }

        $this->disegnaInfo($html, 'Oggetto', $dati['Oggetto']);

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_RENDER_INFO_RICHIESTA_OGGETTO, $dati, $html)) {
            return output::$html_out;
        }

        if ($Proric_rec['RICRUN']) {
            $numRichiestaAcc = intval(substr($Proric_rec['RICRUN'], 4, 6)) . '/' . substr($Proric_rec['RICRUN'], 0, 4);

            $proric_principale_rec = $praLib->GetProric($Proric_rec['RICRUN'], 'codice', $extraParms['PRAM_DB']);
            $praLibEventi = new praLibEventi();
            $oggetto_principale = $praLibEventi->getOggettoProric($extraParms['PRAM_DB'], $proric_principale_rec);

            $html->appendHtml('<div class="italsoft--xs-hidden" style="border-radius: 5px; border: 1px solid #fff; padding: .5em .5em 0; margin: 1em 0;">');
            $this->disegnaInfo($html, 'Num. richiesta principale', $numRichiestaAcc);
            $this->disegnaInfo($html, 'Oggetto richiesta principale', $oggetto_principale);
            $html->appendHtml('</div>');
        }

        if (!frontOfficeApp::$cmsHost->autenticato()) {
            $this->disegnaInfo($html, 'Utente', 'Utente non accreditato, compilazione procedimento libero.');
        } else {
            $this->disegnaInfo($html, 'Codice Fiscale', sprintf('<span style="word-break: break-all; display: inline-block;">%s</span>', $dati['Fiscale']));
        }

        $dataScad = $praLib->getDataScadenza($Proric_rec);
        if ($dataScad && $Proric_rec['RICFORZAINVIO'] == 0){
            $msg = "<i style=\"font-size: 1.5em; margin-right: 5px;\" class=\"icon ion-clock\"></i>Termine Ultimo per l'invio";
            if ($dataScad < date('Ymd')){
                $msg = "<i style=\"font-size: 1.5em; margin-right: 5px;\" class=\"icon ion-clock\"></i>Termine Ultimo per l'invio scaduto";
            }

//            $html->appendHtml('<h5 class="italsoft--xs-hidden">');
//            $html->appendHtml('<i class=\"icon ion-clock\" style="font-size: .9em; margin-right: 5px;"></i>');
//            $html->appendHtml('Informazioni richiesta');
//            $html->appendHtml('</h5>');
//
            
//            $html->appendHtml("<div style=\"padding-right:10px;vertical-align:middle;\"><i style=\"font-size: 25px;\" class=\"icon ion-clock\"></i></div>");
            
            $this->disegnaInfo($html, $msg . ' il ', sprintf('<span style="word-break: break-all; display: inline-block;">%s</span>', frontOfficeLib::convertiData($dataScad) ));
            
        }
        
        if (!$utenteEsibente) {
            $this->disegnaInfo($html, '<br>Codice Fiscale Esibente', sprintf('<span style="word-break: break-all; display: inline-block;">%s</span>', $dati['Proric_rec']['RICFIS']));
        }

        /**
         * Se attiva la videata per gestire gli Accessi, non si può cancellare la richiesta
         */
        $gestioneAccessi = false;
        if ($extraParms['gestioneAccessi']) {
            $gestioneAccessi = true;
        }


        if (!$dati['Consulta'] && !$gestioneAccessi) {
            if ($dati['countObl'] >= 1) {
                $this->disegnaInfo($html, '<br />Passi obbligatori completati', sprintf('%d/%d', $dati['countEsg'], $dati['countObl']));
            }

            if ($dati['countEsg'] == $dati['countObl'] && !$Proric_rec['RICRUN']) {
                $this->disegnaInfo($html, 'Puoi inviare la richiesta.');
            }
        }

        $praticaPadrePassoConfermato = false;
        if ($Proric_rec['RICRUN']) {
            $Proric_rec_padre = $praLib->GetProric($Proric_rec['RICRUN'], 'codice', $extraParms['PRAM_DB']);
            $Ricite_rec_padre = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM RICITE WHERE RICNUM = '" . $Proric_rec['RICRUN'] . "' AND ITERICUNI = '1'", false);

            if ($Ricite_rec_padre && $praLib->checkEsecuzionePasso($Proric_rec_padre, $Ricite_rec_padre)) {
                $praticaPadrePassoConfermato = true;
            }
        }

        /*
         * Controllo visualizzazione bottone Cancella Richiesta:
         * Solo l'esibente può visualizzare il bottone 
         */
        $nascondiCancella = false;
        if (!$utenteEsibente) {
            $nascondiCancella = true;
        }

        /**
         * Se attiva la videata per gestire gli Accessi, non si può cancellare la richiesta
         */
//        if ($extraParms['gestioneAccessi']){
//            $nascondiCancella = true;
//        }

        if (!$praticaPadrePassoConfermato && !in_array($dati['Proric_rec']['RICSTA'], array('98', '01', '91')) && strtolower(frontOfficeApp::$cmsHost->getUserName()) != 'admin' && !$nascondiCancella && !$gestioneAccessi) {
            $urlAnnulla = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'annullaPratica',
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $html->appendHtml('<div class="suap--cancella-richiesta"><br /><div style="text-align: right; font-size: .9em;">');
            $html->appendHtml('<button type="button" class="italsoft-button italsoft-button--secondary" onclick="annullaRichiesta(\'' . $urlAnnulla . '\',\'' . $dati['Proric_rec']['RICNUM'] . '\'); return false;">');
            $html->appendHtml('<i class="icon ion-close italsoft-icon"></i><span>Cancella richiesta</span>');
            $html->appendHtml('</button>');
            $html->appendHtml('</div></div>');

            $html->appendHtml($this->addJsConfermaAnnulla($numRichiesta, $Proric_rec['RICRUN']));
        }

        /*
         * Bottone speciale per generazione XMLPeople Scuole Pesaro.
         */
        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) === 'admin') {
            $sqlAzioneCreaXMLPeople = sprintf("SELECT CODICEAZIONE FROM RICAZIONI WHERE ITEKEY = '' AND RICNUM = '%s' AND CLASSEAZIONE = '%s' AND METODOAZIONE = '%s'", $dati['Proric_rec']['RICNUM'], 'G479Scuole/praG479ScuoleMaterna', 'creaXMLPeople');
            $azioneCreaXMLPeople = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], $sqlAzioneCreaXMLPeople, false);
            if ($azioneCreaXMLPeople) {
                $urlAnnulla = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'lanciaCreaXMLPeople',
                            'seq' => $dati['Ricite_rec']['ITESEQ'],
                            'ricnum' => $dati['Proric_rec']['RICNUM'],
                            'azione' => $azioneCreaXMLPeople['CODICEAZIONE']
                ));

                $html->appendHtml('<div class="suap--cancella-richiesta"><br /><div style="text-align: right; font-size: .9em;">');
                $html->appendHtml('<a class="italsoft-button italsoft-button--secondary" href="' . $urlAnnulla . '">');
                $html->appendHtml('<i class="icon ion-code italsoft-icon"></i><span>Genera XMLPeople</span>');
                $html->appendHtml('</a>');
                $html->appendHtml('</div></div>');
            }
        }

        /*
         * Bottone speciale per generazione file XMLINFO.
         */
        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) === 'admin' && $dati['Proric_rec']['RICSTA'] == '01') {
            $urlCreaXmlInfo = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'lanciaCreaXMLINFO',
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $html->appendHtml('<div class="suap--cancella-richiesta"><br /><div style="text-align: right; font-size: .9em;">');
            $html->appendHtml('<a class="italsoft-button italsoft-button--secondary" href="' . $urlCreaXmlInfo . '">');
            $html->appendHtml('<i class="icon ion-code italsoft-icon"></i><span>Genera XMLINFO</span>');
            $html->appendHtml('</a>');
            $html->appendHtml('</div></div>');
        }

        /*
         * Bottone speciale per Reinvio Mail
         */
        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) === 'admin' && $dati['Proric_rec']['RICSTA'] == '01') {
            $urlReinviaMail = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'reinvioMail',
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $html->appendHtml('<div class="suap--cancella-richiesta"><br /><div style="text-align: right; font-size: .9em;">');
            $html->appendHtml('<a class="italsoft-button italsoft-button--secondary" href="' . $urlReinviaMail . '">');
            $html->appendHtml('<i class="icon ion-code italsoft-icon"></i><span>Reinvia Mail</span>');
            $html->appendHtml('</a>');
            $html->appendHtml('</div></div>');
        }

        /*
         * Bottone speciale per creazione del file body.txt e body.html
         */
        if (strtolower(frontOfficeApp::$cmsHost->getUserName()) === 'admin' && $dati['Proric_rec']['RICSTA'] == '01') {
            $urlCreaBody = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'creaBodyFile',
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $html->appendHtml('<div class="suap--cancella-richiesta"><br /><div style="text-align: right; font-size: .9em;">');
            $html->appendHtml('<a class="italsoft-button italsoft-button--secondary" href="' . $urlCreaBody . '">');
            $html->appendHtml('<i class="icon ion-code italsoft-icon"></i><span>Crea Body File</span>');
            $html->appendHtml('</a>');
            $html->appendHtml('</div></div>');
        }

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_RENDER_INFO_RICHIESTA, $dati, $html)) {
            return output::$html_out;
        }

        $html->appendHtml('</div>');

        return $html->getHtml();
    }

    private function disegnaInfo($html, $key, $value = false, $attrs = '') {
        $class = '';
        if (!in_array($key, array('Num. richiesta', 'Oggetto'))) {
            $class = 'class="italsoft--xs-hidden"';
        }

        $html->appendHtml('<div ' . $class . '>');
        $html->appendHtml("<h5>$key</h5>");

        if ($value) {
            $html->appendHtml("<p $attrs>$value</p>");
        }

        $html->appendHtml('</div>');
    }

    private function addJsConfermaAnnulla($numRichiesta, $RICRUN) {
        $html = new html();

        $messaggioAccorpata = '';
        if ($RICRUN) {
            $messaggioAccorpata = '<br>Questo comporter&agrave; lo scollegamento della richiesta dalla principale.';
        }

        $content = $html->getAlert("Confermi la cancellazione della Richiesta online $numRichiesta?$messaggioAccorpata", 'Attenzione', 'warning');

        $script = '<script type="text/javascript">';
        $script .= "
            function annullaRichiesta(url, richiesta){
                $('<div id=\"praConfermaScaricoDRR\">$content</div>').dialog({
                    title:\"Annulla Richiesta\",
                    resizable: false,
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
                                location.replace(url);
                            }
                        }
                    ]
                });
            };";

        $script .= '</script>';

        return $script;
    }

    private function callCustomClass($azione, $dati, $htmlBuffer, $azione_passo = false) {
        /* @var $objAzione praLibCustomClass */
        $objAzione = praLibCustomClass::getInstance(new praLib());
        $retAzione = $objAzione->eseguiAzione($azione, $dati, $azione_passo);

        if ($objAzione->getCustomResult()) {
            $htmlBuffer->appendHtml($objAzione->getCustomResult());
        }

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

}
