<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateACL extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $htmlAccessi = new html();

        $praLib = new praLib();


        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTemplate.class.php';
        //
        //Comincio il Disegno
        //
        $html->appendHtml("<div id=\"ita-praGestACLBody\" class=\"ita-blockBody\">");
        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'POST');
        $html->addHidden("model", $extraParms['CLASS']);
        $html->addHidden("ricnum", $dati['Proric_rec']['RICNUM']);
        /*
         * Apertura griglia per colonna "Informazioni pratica" a destra,
         * si chiude in "praHtmlInfoPraticaSidebar.class.php".
         */
        $html->appendHtml('<div class="grid" style="max-width: none;">');
        $html->appendHtml('<div class="col-3-12 push-right">');
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlInfoPraticaSidebar.class.php';
        $praHtmlInfoPraticaSidebar = new praHtmlInfoPraticaSidebar();
        $extraParms['gestioneAccessi'] = true;
        $html->appendHtml($praHtmlInfoPraticaSidebar->GetSidebar($dati, $extraParms));
        /*
         * Chiusura griglia per colonna "Informazioni pratica" a destra,
         * si apre in "praHtmlTestata.class.php".
         */
        $html->appendHtml('</div>');

        $html->appendHtml('<div class="col-9-12">');

        $praLib = new praLib();

        $messaggio = "Gestione Condivisione";
        $urlTorna = ItaUrlUtil::GetPageUrl(array());
//        $urlTorna = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'ricnum' => $dati['Ricite_rec']['RICNUM'], 'direzione' => 'primoRosso'));
        $urlEntra = ItaUrlUtil::GetPageUrl(array('p' => $extraParms['online_page'], 'event' => 'navClick', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'direzione' => 'primoRosso'));
        $buttonTorna = '<br><br>' . $html->getButton('<i class="icon ion-reply italsoft-icon"></i><span>Torna all\'elenco delle richieste </span>', $urlTorna);
        $buttonEntra = '   ' . $html->getButton('<i class="icon ion-edit italsoft-icon"></i><span>Entra nella gestione della richiesta </span>', $urlEntra);

        $html->addAlert('Una volta completata la gestione della condivisione, scegliere se tornare nell\' elenco delle richieste oppure entrare nella gestione della richiesta corrente ' . $buttonTorna . $buttonEntra, $messaggio, 'info');


        /*
         * Testata informativa.
         */
        $img_base = frontOfficeLib::getIcon('notepad');
        $tipoPasso = 'Assegnazione Condivisione';

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlTestata.class.php';
        $praHtmlTestata = new praHtmlTestata();
        $html->appendHtml($praHtmlTestata->GetTestata($dati, $img_base, $extraParms, $tipoPasso, 'Gestione Assegnazione Condivisione'));


        $html->appendHtml("<div class=\"divAction\">");




        /**
         * INIZIO CODICE NUOVO
         */
        $htmlAccessi->appendHtml($this->addJsConfermaCancellazione());

        /*
         * Elenco Accessi Configurati
         */
        $htmlAccessi->appendHtml("<div>");


        $elencoAccessiDati = array(
            'caption' => 'Elenco Condivisioni Assegnate',
            'header' => array(
                array('text' => 'Soggetto', 'attrs' => array('width' => '10%')),
                array('text' => 'Descrizione Tipo di Condivisione', 'attrs' => array('data-sorter' => 'false')),
                array('text' => 'Data inizio', 'attrs' => array('width' => '10%', 'data-sorter' => 'false')),
                array('text' => 'Data fine', 'attrs' => array('width' => '10%', 'data-sorter' => 'false')),
                array('text' => '', 'attrs' => array('width' => '10%', 'data-sorter' => 'false')),
                array('text' => '', 'attrs' => array('width' => '10%', 'data-sorter' => 'false'))
            ),
            'body' => array()
        );


        /*
         * Disegno riga tabella
         */

//        $sql = "SELECT *
//                FROM RICACL 
//                    LEFT OUTER JOIN RICSOGGETTI ON RICACL.ROW_ID_RICSOGGETTI = RICSOGGETTI.ROW_ID
//                    WHERE SOGRICNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'
//                    AND RICACL.RICACLTRASHED = 0     
//                ORDER BY
//                    RICSOGGETTI.SOGRICDENOMINAZIONE";


        $sql = "SELECT
                    RICACL.ROW_ID,
                    RICSOGGETTI.SOGRICDENOMINAZIONE,
                    RICSOGGETTI.SOGRICFIS, 
                    RICACL.RICACLDATA_INIZIO,
                    RICACL.RICACLDATA_FINE,
                    RICACL.RICACLMETA,
                    RICACL.RICACLATTIVA
                FROM RICACL 
                    LEFT OUTER JOIN RICSOGGETTI ON RICACL.ROW_ID_RICSOGGETTI = RICSOGGETTI.ROW_ID
                    WHERE SOGRICNUM = '" . $dati['Proric_rec']['RICNUM'] . "'
                    AND RICACL.RICACLTRASHED = 0     
                ORDER BY
                    RICSOGGETTI.SOGRICDENOMINAZIONE";


        $ricacl_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, true);

        if (count($ricacl_tab)) {
            foreach ($ricacl_tab as $ricacl_rec) {

                $soggetto = $ricacl_rec['SOGRICDENOMINAZIONE'] . "<br> " . $ricacl_rec['SOGRICFIS'];
                $inizio = substr($ricacl_rec['RICACLDATA_INIZIO'], 6, 2) . "/" . substr($ricacl_rec['RICACLDATA_INIZIO'], 4, 2) . "/" . substr($ricacl_rec['RICACLDATA_INIZIO'], 0, 4);
                $fine = '';
                if ($ricacl_rec['RICACLDATA_FINE']) {
                    $fine = substr($ricacl_rec['RICACLDATA_FINE'], 6, 2) . "/" . substr($ricacl_rec['RICACLDATA_FINE'], 4, 2) . "/" . substr($ricacl_rec['RICACLDATA_FINE'], 0, 4);
                }
                $tipoAcl = '';
                if ($ricacl_rec['RICACLMETA']) {
                    $arrAcl = json_decode($ricacl_rec['RICACLMETA'], true);
                    if (is_array($arrAcl)) {
                        foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                            if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
                                $tipoAcl = $this->getValoreColonnaPasso($autorizzazione, $dati["PRAM_DB"]);
                            } else if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_RICHIESTA') {
                                if ($autorizzazione['INTEGRAZIONE_RICHIESTA']) {
                                    $tipoAcl = 'Richiesta Integrazione';
                                } else {
                                    $tipoAcl = 'Visualizzazione Richiesta';
                                }
                            }
                        }
                    }
                }
                $msgAttiva = "";
                if ($tipoAcl == 'Visualizzazione Richiesta') {
                    switch ($ricacl_rec['RICACLATTIVA']) {
                        case 1:
                            $msgAttiva = "<br><span><b>Attivo solo se la richiesta On-Line e' in compilazione (NON INOLTRATA)</b></span>";
                            break;
                        case 2:
                            $msgAttiva = "<br><span><b>Attivo solo se la richiesta On-Line e' stata INOLTRATA</b></span>";
                            break;
                        case 3:
                            $msgAttiva = "<br><span><b>Attivo sempre sia se la richiesta On-Line e' in compilazione o e' inoltrata</b></span>";
                            break;
                    }
                }

                $recordAccessi = array(
                    array('text' => $soggetto, 'attrs' => array('data-sortvalue' => $soggetto)),
                    $tipoAcl . $msgAttiva,
                    $inizio,
                    $fine,
                );

                $hrefModifica = ItaUrlUtil::GetPageUrl(array('event' => 'modificaAcl', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'idricacl' => $ricacl_rec['ROW_ID']));
                $recordAccessi[] = sprintf('<div class="align-center"><a href="%s"><i style="width:24px;" class="icon ion-edit italsoft-icon"></i><br><small>Modifica</small></a></div>', $hrefModifica, $html->getImage(frontOfficeLib::getIcon('pencil'), '24px'));
//                $recordAccessi[] = sprintf('<div class="align-center"><a href="%s">%s<br><small>Modifica</small></a></div>', $hrefModifica, $html->getImage(frontOfficeLib::getIcon('pencil'), '24px'));
//                <i class="icon ion-edit italsoft-icon"></i>

//                $hrefScollega = ItaUrlUtil::GetPageUrl(array('event' => 'cessaAcl', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'idricacl' => $ricacl_rec['ROW_ID']));
//                $recordAccessi[] = sprintf('<div class="align-center"><a href="%s">%s<br><small>Cancella</small></a></div>', $hrefScollega, $html->getImage(frontOfficeLib::getIcon('error'), '24px'));

                $buttonCancella = $html->getLink(sprintf('<div class="align-center"><i class="icon ion-trash-a italsoft-icon"></i><br><small>Cancella</small></div>', $html->getImage(frontOfficeLib::getIcon('error'), '24px')), false, array('event' => 'cessaAcl', 'ricnum' => $dati['Proric_rec']['RICNUM'], 'idricacl' => $ricacl_rec['ROW_ID'], 'cfSoggetto' => $ricacl_rec['SOGRICFIS']));
                $recordAccessi[] = $buttonCancella;

//                $recordAccessi[] = sprintf('<div class="align-center"><a href="#" onclick="scollegaRichiesta(\'' . $hrefScollega . '\',\'' . $dati['Ricite_rec']['RICNUM'] . '\'); return false;">%s<br><small>Cancella</small></a></div>', $html->getImage(frontOfficeLib::getIcon('error'), '24px'));

                $elencoAccessiDati['body'][] = $recordAccessi;
            }
        } else {
            $recordEmpty = array_fill(0, count($elencoAccessiDati['header']), '');
            $recordEmpty[1] = 'Nessuna condivisione configurata.';
            $elencoAccessiDati['body'][] = $recordEmpty;
        }

        $htmlAccessi->addTable($elencoAccessiDati, array('sortable' => true));


        /*
         * Nuovo Regola di Condivisione
         */

        $icon = "";
        $msg = "<div style=\"display:inline-block;vertical-align:middle;\" class=\"italsoft-tooltip\" title=\"Clicca per aggiungere una nuova regola di Condivisione.\">Inserisci Nuova Regola di Condivisione</div>";

        $icon = "<div style=\"padding-right:10px;display:inline-block;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-person-add italsoft-icon\"></i></div>";
        $href = $html->getButton($icon . $msg, "", "primary", array(
            'event' => 'addACL',
            'ricnum' => $dati['Proric_rec']['RICNUM'],
        ));
        $htmlAccessi->appendHtml("<br><div>$href</div>");


//        $htmlAccessi->appendHtml('<div id="div_CaricaAutocert" style="display: none; "><div id="div_CaricaAutocert_Progressbar"></div></div>');
//
//        $loadingDialog = 'jQuery(\'#div_CaricaAutocert\').dialog({modal:true,title:\'Caricamento in corso...\',draggable:false,resizable:false}); jQuery(\'#div_CaricaAutocert_Progressbar\').progressbar({value: false});';
//
//        $htmlAccessi->addForm(ItaUrlUtil::GetPageUrl(array()), 'POST', array(
//            'enctype' => 'multipart/form-data',
//            'style' => 'display: inline-block;'
//        ));
//
//        $htmlAccessi->addHidden('event', 'accorpaAutocertificazione');
////        $htmlAccessi->addHidden('seq', $dati['Ricite_rec']['ITESEQ']);
//        $htmlAccessi->addHidden('ricnum', $dati['Ricite_rec']['RICNUM']);
//        $htmlAccessi->appendHtml('<input type="file" style="width: 1px; height: 1px; position: absolute; left: -5000px;" name="ita_upload" id="procedimento_pec" onchange="' . $loadingDialog . ' this.form.submit();" />');
//        $htmlAccessi->appendHtml('<button class="italsoft-button" type="button" onclick="jQuery(\'#procedimento_pec\').click();">');
//        $htmlAccessi->appendHtml('<i class="icon ion-person-add italsoft-icon"></i> <span>Inserisci Nuova Regola di Accesso</span>');
//        $htmlAccessi->appendHtml('</button>');

        $htmlAccessi->appendHtml('</form>');


        $htmlAccessi->appendHtml('</div>');   // Chiude div_CaricaAutocert


        /*
         * Fine Lookup
         */

//        $htmlAccessi->appendHtml("</div>");
//        $htmlAccessi->appendHtml("<br />");
        /*
         * Fine Elenco
         */

        $htmlAccessi->appendHtml("<div>");


        $html->appendHtml($htmlAccessi->getHtml());



        /**
         * FINE CODICE NUOVO
         */
        $html->appendHtml("</div>"); //div Action

        $html->appendHtml("</form>");
        $html->appendHtml("</div>");
        return $html->getHtml();
    }

    private function getValoreColonnaPasso($autorizzazione, $PRAM_DB) {
        $valore = '';
        $praLib = new praLib();

        $valore = 'Gestione Passo';
        $idPasso = $autorizzazione['ROW_ID_PASSO'];
        if ($idPasso) {

            $ricite_rec = $praLib->GetRicite($idPasso, 'row_id', $PRAM_DB);
            if ($ricite_rec) {
                $valore = 'Gestione del Passo: <b>' . $ricite_rec['ITEDES'] . '</b>';

//                $ins = '<span style="color:red">No</span>';
//                if ($autorizzazione['INSERISCI']) {
//                    $ins = '<span style="color:green">Si</span>';
//                }
//                $mod = '<span style="color:red">No</span>';
//                if ($autorizzazione['MODIFICA']) {
//                    $mod = '<span style="color:green">Si</span>';
//                }
//                $canc = '<span style="color:red">No</span>';
//                if ($autorizzazione['CANCELLA']) {
//                    $canc = '<span style="color:green">Si</span>';
//                }
//
//                $valore .= '<br> Inserisci: ' . $ins . '  -  Modifica: ' . $mod . '  -  Cancella: ' . $canc;
            }
        }

        return $valore;
    }

    private function addJsConfermaCancellazione() {
        $script = '<script type="text/javascript">';
        $script .= "
            function scollegaRichiesta(url, richiesta) {
                $('<div><div class=\"italsoft-alert italsoft-alert--warning\"><h2>Attenzione</h2><p>Confermi la cancellazione della condivisione assegnata ? </p></div></div>').dialog({
                    title:\"Cancellazione Condivisione\",
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
