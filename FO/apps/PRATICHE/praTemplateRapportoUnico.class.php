<?php

class praTemplateRapportoUnico extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();
        $praLibDati = praLibDati::getInstance($praLib);

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php';
        $praLibEventi = new praLibEventi();

        $passoEseguito = strpos($dati['Proric_rec']['RICSEQ'], chr(46) . $dati['seq'] . chr(46)) === false ? false : true;
        $datiAmbiente = $dati['Navigatore']['Dizionario_Richiesta_new']->getData('AMBIENTE');
        $autocertificazionePermessa = $datiAmbiente && $datiAmbiente->getData('CARICAMENTO_AUTOCERTIFICAZIONE') == 'si' ? true : false;
        $statoRichiesteAccorpabili = $datiAmbiente && $datiAmbiente->getData('RICHIESTE_ACCORPABILI') ? $datiAmbiente->getData('RICHIESTE_ACCORPABILI') : false;

        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praMupBody\" class=\"ita-blockBody\">");

        $tipoPasso = 'Conferma Richieste Accorpate';

        $img_base = frontOfficeLib::getIcon('rapporto-completo');
        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        //
        // Conteggio Passi Obbligatori completati
        //
        if ($dati['countEsg'] == $dati['countObl']) {
            $descObl = "Tutti i passi obbligatori sono completati: Puoi inviare la richiesta<br>";
        } else {
            if ($dati['countObl'] >= 1) {
                $descObl = "Passi obbligatori completati: " . $dati['countEsg'] . " di " . $dati['countObl'] . "<br>";
            } else {
                $descObl = "Passo obbligatorio completato <br> ";
            }
        }


        $html->appendHtml("<div class=\"divAction\">");
        //
        // Costruisco box info
        //
        
        $html->appendHtml("<div style=\"height:auto; width: 100%;\" class=\"ui-widget-content ui-corner-all boxInfo\">");
        //
        $html->appendHtml("<div class=\"divContenitore\">");
        $html->appendHtml("<div class=\"divTipoPasso legenda\" style=\"display:inline-block;\">Tipo passo: $tipoPasso");
        $html->appendHtml("</div>"); //div tipo passo
        $html->appendHtml("<div class=\"divPassiObl\" style=\"padding-right:10px;display:inline-block;float:right;\">$descObl");
        $html->appendHtml("</div>"); //div passi obl
        $html->appendHtml("</div>"); //div contenitore

        if ($dati['Ricite_rec']['RICERF'] == 1 || $dati['Ricite_rec']['RICERM'] != '') {
            $html->addAlert($dati['Ricite_rec']['RICERM'], 'Errore nei dati', 'error');
        }

        $Proric_accorpate_tab = $praLib->GetRichiesteAccorpate($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);
        $Ricdoc_tab = $praLib->GetAutocertificazioniAccorpate($dati['PRAM_DB'], $dati['Proric_rec']['RICNUM']);

        $htmlAccorpate = new html();

        /*
         * Elenco Procedimenti Obbligatori
         */
        $Pratiche_obb_accorpate = true;
        $Raccolta = $dati['Navigatore']['Dizionario_Richiesta_new']->getAlldataPlain("", ".");
        $Anapra_obb_tab = $praLib->GetProcedimentiObbligatori($dati['PRAM_DB'], $Raccolta, $dati['Proric_rec']['RICNUM'], $dati['Anapra_rec']['PRANUM'], $dati['Anaeventi_rec']['EVTCOD']);

        if (count($Anapra_obb_tab)) {
            $praticheObbDati = array(
                'caption' => 'Elenco procedimenti obbligatori',
                'header' => array(
                    array('text' => 'Descrizione'),
                    array('text' => 'Stato', 'attrs' => array('width' => '12%'))
                ),
                'body' => array()
            );

            if ($dati['Consulta'] == false) {
                /*
                 * Colonna per Avvio richiesta
                 */

                $praticheObbDati['header'][] = array('text' => 'Avvia', 'attrs' => array('width' => '12%'));
            }

            foreach ($Anapra_obb_tab as $key => $Anapra_obb_rec) {
                foreach ($Proric_accorpate_tab as $Proric_accorpate_rec) {
                    if (
                        $Proric_accorpate_rec['RICPRO'] == $Anapra_obb_rec['RICPRO'] &&
                        $Proric_accorpate_rec['RICEVE'] == $Anapra_obb_rec['RICEVE']
                    ) {
                        continue 2;
                    }
                }

                $Pratiche_obb_accorpate = false;

                $descProc = "<b>" . $Anapra_obb_rec['RICPRO'] . "</b> - " . $praLibEventi->getOggettoProric($extraParms['PRAM_DB'], $Anapra_obb_rec);
                $descProc .= '<br />' . $Anapra_obb_rec['SETDES'];
                $descProc .= '<br />' . $Anapra_obb_rec['ATTDES'];
                $descProc = '<span style="color: red;">' . $descProc . '</span>';

                $img_obb = $html->getImage(frontOfficeLib::getIcon('ban'), '18px');
                $text_stato = 'Da accorpare';

                $href_compila = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'openBlock',
                        'procedi' => $Anapra_obb_rec['ITEPRA'],
                        'subproc' => $Anapra_obb_rec['IEVCOD'],
                        'subprocid' => $Anapra_obb_rec['ROWID_ITEEVT'],
                        'settore' => $Anapra_obb_rec['IEVSTT'],
                        'attivita' => $Anapra_obb_rec['IEVATT'],
                        'accorpa' => $dati['Proric_rec']['RICNUM']
                ));

                $textAvviaRichiesta = sprintf('<div class="align-center"><a href="%s">%s<br><small>Avvia Richiesta</small></a></div>', $href_compila, $html->getImage(frontOfficeLib::getIcon('notepad'), '18px'));

                $recordPraticheObb = array(
                    '<div style="word-wrap: anywhere;">' . $descProc . '</div>',
                    '<div class="align-center">' . $img_obb . '<br><small>' . $text_stato . '</small></div>'
                );

                if ($dati['Consulta'] == false) {
                    /*
                     * Colonna per Avvio richiesta
                     */

                    $recordPraticheObb[] = $textAvviaRichiesta;
                }

                $praticheObbDati['body'][] = $recordPraticheObb;
            }

            if (count($praticheObbDati['body'])) {
                $htmlAccorpate->addTable($praticheObbDati);
                $htmlAccorpate->appendHtml("<hr style=\"margin: 30px 0 15px;\" />");
            }
        }

        if (count($Ricdoc_tab)) {
            $Pratiche_obb_accorpate = true;
        }

        /*
         * Elenco Richieste Collegate
         */
        $htmlAccorpate->appendHtml("<div>");

        $elencoAccorpateDati = array(
            'caption' => 'Elenco richeste accorpate',
            'header' => array(
                array('text' => 'Numero', 'attrs' => array('width' => '10%')),
                array('text' => 'Descrizione', 'attrs' => array('data-sorter' => 'false')),
                array('text' => 'Data/ora inizio', 'attrs' => array('width' => '10%', 'data-sorter' => 'false')),
                array('text' => 'Stato', 'attrs' => array('width' => '10%', 'data-sorter' => 'false'))
            ),
            'body' => array()
        );

        if ($dati['Consulta'] != true && !$passoEseguito) {
            $elencoAccorpateDati['header'][] = array('text' => '', 'attrs' => array('width' => '10%', 'data-sorter' => 'false'));
            $elencoAccorpateDati['header'][] = array('text' => '', 'attrs' => array('width' => '10%', 'data-sorter' => 'false'));

            $htmlAccorpate->appendHtml($this->addJsConfermaScollega());
        }

        $Pratiche_accorpate_allegati_mancanti = false;

        if (count($Proric_accorpate_tab) || count($Ricdoc_tab)) {
            foreach ($Proric_accorpate_tab as $key => $proric_rec) {
                /*
                 * Faccio il prendiDati della pratica accorpata
                 * e controllo se è possibile farne il rapporto completo
                 */
                $Pratica_accorpata_da_completare = false;
                $Proric_accorpate_tab[$key]['DATI'] = $praLibDati->prendiDati($proric_rec['RICNUM']);

                if ((int) $Proric_accorpate_tab[$key]['DATI']['countEsg'] < (int) $Proric_accorpate_tab[$key]['DATI']['countObl']) {
                    $Pratiche_accorpate_allegati_mancanti = true;
                    $Pratica_accorpata_da_completare = true;
                }

                /*
                 * Disegno riga tabella
                 */
                $ricnum = intval(substr($proric_rec['RICNUM'], 4)) . "/" . substr($proric_rec['RICNUM'], 0, 4);

                $Proric_accorpate_tab[$key]['NUMERO'] = $ricnum;
                $Proric_accorpate_tab[$key]['DESCRIZIONE'] = $praLibEventi->getOggettoProric($extraParms['PRAM_DB'], $proric_rec);
                $descProc = "<b>" . $proric_rec['RICPRO'] . "</b> - " . $Proric_accorpate_tab[$key]['DESCRIZIONE'];

                $data_inizio = substr($proric_rec['RICDRE'], 6, 2) . "/" . substr($proric_rec['RICDRE'], 4, 2) . "/" . substr($proric_rec['RICDRE'], 0, 4);
                $inizio = $data_inizio . "<br>" . $proric_rec['RICORE'];

                $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'ricnum' => $proric_rec['RICNUM'], 'direzione' => 'primoRosso'));

                $immagineStato = $html->getImage(frontOfficeLib::getIcon($Pratica_accorpata_da_completare ? 'cone' : 'check'), '24px');
                $textStato = $Pratica_accorpata_da_completare ? 'In corso' : 'Completata';

                if ($Proric_accorpate_tab[$key]['DATI']['Anaset_rec']['SETDES']) {
                    $descProc .= '<br />' . $Proric_accorpate_tab[$key]['DATI']['Anaset_rec']['SETDES'];
                }

                if ($Proric_accorpate_tab[$key]['DATI']['Anaatt_rec']['ATTDES']) {
                    $descProc .= '<br />' . $Proric_accorpate_tab[$key]['DATI']['Anaatt_rec']['ATTDES'];
                }

                $recordAccorpata = array(
                    array('text' => $ricnum, 'attrs' => array('data-sortvalue' => $proric_rec['RICNUM'])),
                    '<div style="word-wrap: anywhere;"><a href="' . $hrefVaiPasso . '">' . $descProc . '</a></div>',
                    $inizio,
                    '<div class="align-center">' . $immagineStato . '<br /><small>' . $textStato . '</small></div>'
                );

                if ($dati['Consulta'] != true && !$passoEseguito) {
                    $recordAccorpata[] = sprintf('<div class="align-center"><a href="%s">%s<br><small>Modifica</small></a></div>', $hrefVaiPasso, $html->getImage(frontOfficeLib::getIcon('pencil'), '24px'));

                    $hrefScollega = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'accorpaRichiesta',
                            'ricnum' => $dati['Ricite_rec']['RICNUM'],
                            'seq' => $dati['Ricite_rec']['ITESEQ'],
                            'scollega' => $proric_rec['RICNUM']
                    ));

                    $recordAccorpata[] = sprintf('<div class="align-center"><a href="#" onclick="scollegaRichiesta(\'' . $hrefScollega . '\',\'' . $ricnum . '\'); return false;">%s<br><small>Scollega</small></a></div>', $html->getImage(frontOfficeLib::getIcon('error'), '24px'));
                }

                $elencoAccorpateDati['body'][] = $recordAccorpata;
            }

            foreach ($Ricdoc_tab as $Ricdoc_rec) {
                $descProc = '<div style="padding: .6em 0; word-wrap: anywhere;"><b>' . $Ricdoc_rec['DOCNAME'] . '</b><br />Richiesta inviata esternamente</div>';
                $immagineStato = $html->getImage(frontOfficeLib::getIcon('check'), '24px');

                $hrefElimina = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'accorpaAutocertificazione',
                        'ricnum' => $dati['Ricite_rec']['RICNUM'],
                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                        'elimina' => $Ricdoc_rec['DOCUPL']
                ));

                $recordAccorpata = array(
                    '',
                    $descProc,
                    '',
                    '<div class="align-center">' . $immagineStato . '<br><small>Caricato</small></div>'
                );

                if ($dati['Consulta'] != true && !$passoEseguito) {
                    $hrefVisualizza = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'vediAllegato',
                            'seq' => $dati['Ricite_rec']['ITESEQ'],
                            'file' => $Ricdoc_rec['DOCUPL'],
                            'ricnum' => $dati['Proric_rec']['RICNUM']
                    ));

                    $recordAccorpata[] = sprintf('<div class="align-center"><a href="' . $hrefVisualizza . '">%s<br><small>Visualizza</small></a></div>', $html->getImage(frontOfficeLib::getFileIcon($Ricdoc_rec['DOCNAME']), '24px'));
                    $recordAccorpata[] = sprintf('<div class="align-center"><a href="' . $hrefElimina . '">%s<br><small>Elimina</small></a></div>', $html->getImage(frontOfficeLib::getIcon('error'), '24px'));
                }

                $elencoAccorpateDati['body'][] = $recordAccorpata;
            }
        } else {
            $recordEmpty = array_fill(0, count($elencoAccorpateDati['header']), '');
            $recordEmpty[1] = 'Nessuna richiesta accorpata.';
            $elencoAccorpateDati['body'][] = $recordEmpty;
        }

        $htmlAccorpate->addTable($elencoAccorpateDati, array('sortable' => true));

        /*
         * Modifica per collegamento richiesta
         */

        if ($dati['Consulta'] != true && !$passoEseguito) {
            $htmlAccorpate->appendHtml('<div style="text-align: right; font-size: .9em;">');

            /*
             * Accorpamento nuove richieste
             */

            if ($extraParms['procedimenti_page']) {
                $urlAccorpa = ItaUrlUtil::GetPageUrl(array(
                        'p' => $extraParms['procedimenti_page'],
                        'accorpa' => $dati['Proric_rec']['RICNUM']
                ));

                $htmlAccorpate->appendHtml('<a class="italsoft-button" href="' . $urlAccorpa . '">');
                $htmlAccorpate->appendHtml('<i class="icon ion-plus italsoft-icon"></i> <span>Accorpa nuova richiesta</span>');
                $htmlAccorpate->appendHtml('</a>');
            }

            /*
             * Accorpamento richieste esistenti
             */

            $whereRicsta = "RICSTA = '99'";

            if (!$statoRichiesteAccorpabili) {
                /*
                 * Se non c'è il nuovo parametro,
                 * lascio il controllo già presente
                 */

                if ($autocertificazionePermessa) {
                    $whereRicsta = "RICSTA = '01'";
                }
            } else {
                /*
                 * Verifico il valore del nuovo parametro
                 */

                switch ($statoRichiesteAccorpabili) {
                    case 'in corso':
                        $whereRicsta = "RICSTA = '99'";
                        break;

                    case 'inoltrate':
                        $whereRicsta = "RICSTA = '01'";
                        break;

                    case 'entrambi':
                        $whereRicsta = "RICSTA = '01' || RICSTA = '99'";
                        break;
                }
            }

            $sql = "SELECT 
                    PRORIC.*,
                    ANASET.SETDES,
                    ANAATT.ATTDES,
                    " . $dati["PRAM_DB"]->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES
                FROM
                    PRORIC
                LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO = ANAPRA.PRANUM
                LEFT OUTER JOIN ANASET ON PRORIC.RICSTT = ANASET.SETCOD
                LEFT OUTER JOIN ANAATT ON PRORIC.RICATT = ANAATT.ATTCOD
                WHERE
                    RICFIS = '" . $dati['Proric_rec']['RICFIS'] . "'
                AND
                    ( $whereRicsta )
                AND
                    RICNUM != '" . $dati['Proric_rec']['RICNUM'] . "'
                AND
                    RICNUM != '" . $dati['Proric_rec']['RICRUN'] . "'
                AND
                    RICRUN = ''
                AND
                    RICRPA = ''
                ORDER BY
                    RICNUM DESC";

            $proric_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, true);

            if (count($proric_tab)) {
                $htmlAccorpate->appendHtml($this->addJsConfermaAccorpaInoltrata());

                $htmlAccorpate->appendHtml('<button class="italsoft-button" type="button" onclick="jQuery(\'#div_elencoCollegaRichiesta_ric\').dialog( { height: $(window).height() - 100, modal: true, title: \'Seleziona la richiesta da accorpare\', width: $(window).width() - 100, draggable: false, resizable: false } ).parent().css( { position: \'fixed\', top: \'50px\' } );">');
                $htmlAccorpate->appendHtml('<i class="icon ion-pound italsoft-icon"></i> <span>Accorpa richiesta esistente</span>');
                $htmlAccorpate->appendHtml('</button>');

                $htmlAccorpate->appendHtml('<div id="div_elencoCollegaRichiesta_ric" style="display: none;">');

                $datiTabellaLookup = array(
                    'header' => array(
                        array('text' => 'Numero', 'attrs' => array('style' => 'width: 10%;')),
                        array('text' => 'Descrizione', 'attrs' => array('data-sorter' => 'false')),
                        array('text' => 'Data/ora inizio', 'attrs' => array('style' => 'width: 20%;')),
                        array('text' => 'Stato', 'attrs' => array('style' => 'width: 10%;')),
                        array('text' => '', 'attrs' => array('style' => 'width: 10%;', 'data-sorter' => 'false', 'data-filter' => 'false'))
                    ),
                    'body' => array()
                );

                foreach ($proric_tab as $proric_rec) {
                    $ricnum = intval(substr($proric_rec['RICNUM'], 4)) . '/' . substr($proric_rec['RICNUM'], 0, 4);
                    $descProc = "<b>" . $proric_rec['RICPRO'] . "</b> - " . $praLibEventi->getOggettoProric($extraParms['PRAM_DB'], $proric_rec);
                    $data_inizio = substr($proric_rec['RICDRE'], 6, 2) . "/" . substr($proric_rec['RICDRE'], 4, 2) . "/" . substr($proric_rec['RICDRE'], 0, 4);
                    $inizio = $data_inizio . "<br>" . $proric_rec['RICORE'];

                    if ($proric_rec['SETDES']) {
                        $descProc .= '<br />' . $proric_rec['SETDES'];
                    }

                    if ($proric_rec['ATTDES']) {
                        $descProc .= '<br />' . $proric_rec['ATTDES'];
                    }

                    $hrefCollega = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'accorpaRichiesta',
                            'ricnum' => $dati['Ricite_rec']['RICNUM'],
                            'seq' => $dati['Ricite_rec']['ITESEQ'],
                            'accorpa' => $proric_rec['RICNUM']
                    ));

                    $statoRichiesta = '';
                    $pulsanteAccorpa = '';
                    switch ($proric_rec['RICSTA']) {
                        case '01':
                            $statoRichiesta = 'Inoltrata';
                            $pulsanteAccorpa = sprintf('<div class="align-center"><a href="#" onclick="accorpaRichiestaInoltrata(\'' . $hrefCollega . '\',\'' . $ricnum . '\'); return false;">%s<br><small>Accorpa</small></a></div>', $html->getImage(frontOfficeLib::getIcon('add'), '24px'));
                            break;

                        case '99':
                            $statoRichiesta = 'In corso';
                            $pulsanteAccorpa = sprintf('<div class="align-center"><a href="' . $hrefCollega . '" onclick="$(\'body\').addClass(\'italsoft-loading\');">%s<br><small>Accorpa</small></a></div>', $html->getImage(frontOfficeLib::getIcon('add'), '24px'));
                            break;
                    }

                    $datiTabellaLookup['body'][] = array(
                        array('text' => $ricnum, 'attrs' => array('data-sortvalue' => $proric_rec['RICNUM'])),
                        $descProc,
                        array('text' => $inizio, 'attrs' => array('data-sortvalue' => $proric_rec['RICDRE'] . $proric_rec['RICORE'])),
                        $statoRichiesta,
                        $pulsanteAccorpa
                    );
                }

                $htmlAccorpate->addTable($datiTabellaLookup, array(
                    'sortable' => true,
                    'filters' => true,
                    'paginated' => true
                ));

                $htmlAccorpate->appendHtml('</div>');
            }

            /*
             * Accorpamento richieste esterne
             */

            if ($autocertificazionePermessa && !count($Ricdoc_tab)) {
                $htmlAccorpate->appendHtml('<div id="div_CaricaAutocert" style="display: none; "><div id="div_CaricaAutocert_Progressbar"></div></div>');

                $loadingDialog = 'jQuery(\'#div_CaricaAutocert\').dialog({modal:true,title:\'Caricamento in corso...\',draggable:false,resizable:false}); jQuery(\'#div_CaricaAutocert_Progressbar\').progressbar({value: false});';

                $htmlAccorpate->addForm(ItaUrlUtil::GetPageUrl(array()), 'POST', array(
                    'enctype' => 'multipart/form-data',
                    'style' => 'display: inline-block;'
                ));

                $htmlAccorpate->addHidden('event', 'accorpaAutocertificazione');
                $htmlAccorpate->addHidden('seq', $dati['Ricite_rec']['ITESEQ']);
                $htmlAccorpate->addHidden('ricnum', $dati['Ricite_rec']['RICNUM']);
                $htmlAccorpate->appendHtml('<input type="file" style="width: 1px; height: 1px; position: absolute; left: -5000px;" name="ita_upload" id="procedimento_pec" onchange="' . $loadingDialog . ' this.form.submit();" />');
                $htmlAccorpate->appendHtml('<button class="italsoft-button" type="button" onclick="jQuery(\'#procedimento_pec\').click();">');
                $htmlAccorpate->appendHtml('<i class="icon ion-android-upload italsoft-icon"></i> <span>Accorpa richiesta inviata esternamente</span>');
                $htmlAccorpate->appendHtml('</button>');
                $htmlAccorpate->appendHtml('</form>');
            }

            $htmlAccorpate->appendHtml('</div>');
        }

        /*
         * Fine Lookup
         */

        $htmlAccorpate->appendHtml("</div>");
        $htmlAccorpate->appendHtml("<br />");
        /*
         * Fine Elenco
         */

        $html->appendHtml("<div>");

        /*
         * Bottone conferma/annulla pratiche accorpate
         */
        $buttonConferma = "<br /><div style=\"text-align:center;margin:10px;\">
                                 <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"ita-form-submit italsoft-button\" type=\"submit\">
                                    <i class=\"icon ion-checkmark italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Conferma</b></div>
                                 </button>
                               </div>";

        $buttonAnnulla = "<br /><div style=\"text-align:center;margin:10px;\">
                                 <button style=\"cursor:pointer;\" name=\"confermaDati\" class=\"italsoft-button italsoft-button--secondary\" type=\"submit\">
                                    <i class=\"icon ion-close italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Annulla</b></div>
                                 </button>
                               </div>";

        if ($dati['Consulta'] == true) {
            $buttonConferma = $buttonAnnulla = "";
        }

        if (!$Pratiche_obb_accorpate) {
            $html->addAlert('Accorpare i procedimenti obbligatori mancanti per proseguire.', 'Attenzione', 'error');
        } else if (!count($Proric_accorpate_tab) && !count($Ricdoc_tab)) {
            $html->addAlert('Accorpare almeno una richiesta per proseguire.', 'Attenzione', 'error');
        } else if ($Pratiche_accorpate_allegati_mancanti) {
            $html->addAlert('Eseguire tutti i passi obbligatori nelle richieste accorpate per proseguire.', 'Attenzione', 'error');
        } else {
            $htmlAccorpate->addForm(ItaUrlUtil::GetPageUrl(array()), 'GET');
            if ($passoEseguito) {
                $htmlAccorpate->addHidden('event', 'annullaRaccolta');
            } else {
                $htmlAccorpate->addHidden('event', 'submitRaccolta');
            }
            $htmlAccorpate->addHidden('seq', $dati['Ricite_rec']['ITESEQ']);
            $htmlAccorpate->addHidden('ricnum', $dati['Ricite_rec']['RICNUM']);

            if (!$passoEseguito) {
                $htmlAccorpate->appendHtml($buttonConferma);
            } else {
                $htmlAccorpate->appendHtml("<div style=\"text-align:center;\">" . $buttonAnnulla . "</div>");
            }

            $htmlAccorpate->appendHtml('</form>');
        }

        $html->appendHtml($htmlAccorpate->getHtml());
        $html->appendHtml("</div>");

        /*
         * Messaggio FO tipo passo
         */
        $metadati = unserialize($dati['Praclt_rec']['CLTMETA']);
        $messaggioFO = $metadati['MESSAGGIOFO'];
        if ($messaggioFO) {
            $value = $praLib->elaboraTemplateDefault($messaggioFO, $dati['Navigatore']["Dizionario_Richiesta_new"]->getAllData());
            $html->appendHtml("<div class=\"italsoft-alert italsoft-alert--info\">$value</div><br>");
        }

        // 4 -- NOTE DEL PASSO
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNote.class.php';
        $praHtmlNote = new praHtmlNote();
        $html->appendHtml($praHtmlNote->GetNote($dati));
        // 3 -- FINE NOTE DEL PASSO

        $html->appendHtml("</div>");
        $html->appendHtml("</div>"); //divAction
        // 3 -- FINE BOX INFO
        // 7 -- INIZIO LEGENDA
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());
        // 7 -- FINE LEGENDA

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml("</div>");

        return $html->getHtml();
    }

    private function addJsConfermaScollega() {
        $script = '<script type="text/javascript">';
        $script .= "
            function scollegaRichiesta(url, richiesta) {
                $('<div><div class=\"italsoft-alert italsoft-alert--warning\"><h2>Attenzione</h2><p>Confermi lo scollegamento della richiesta accorpata ' + richiesta + '?</p></div></div>').dialog({
                    title:\"Scollega richiesta\",
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
                                $('body').addClass('italsoft-loading');
                            }
                        }
                    ]
                });
            };";

        $script .= '</script>';

        return $script;
    }

    private function addJsConfermaAccorpaInoltrata() {
        $script = '<script type="text/javascript">';
        $script .= "
            function accorpaRichiestaInoltrata(url, richiesta) {
                $('<div><div class=\"italsoft-alert italsoft-alert--warning\"><h2>Attenzione</h2><p>La richiesta ' + richiesta + ' è già stata inoltrata.<br>Confermi l\'accorpamento di questa richiesta?</p></div></div>').dialog({
                    title:\"Accorpa richiesta inoltrata\",
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
                                $('body').addClass('italsoft-loading');
                            }
                        }
                    ]
                });
            };";

        $script .= '</script>';

        return $script;
    }

}
