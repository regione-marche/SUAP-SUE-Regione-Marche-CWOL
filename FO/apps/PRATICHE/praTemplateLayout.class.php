<?php

class praTemplateLayout {

    public function disegnaHeader($dati, $extraParms, $img_base, $tipoPasso) {
        $html = new html();

        /*
         * Apertura griglia per colonna "Informazioni pratica" a destra,
         * si chiude in "praHtmlInfoPraticaSidebar.class.php".
         */
        $html->appendHtml('<div class="grid" style="max-width: none;">');

        $html->appendHtml('<div class="col-3-12 push-right">');

        /* --------------------------------- */

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlInfoPraticaSidebar.class.php';
        $praHtmlInfoPraticaSidebar = new praHtmlInfoPraticaSidebar();
        $html->appendHtml($praHtmlInfoPraticaSidebar->GetSidebar($dati, $extraParms));

        /*
         * Chiusura griglia per colonna "Informazioni pratica" a destra,
         * si apre in "praHtmlTestata.class.php".
         */
        $html->appendHtml('</div>');

        $html->appendHtml('<div class="col-9-12">');

        if ($dati['Proric_rec']['RICFORZAINVIO'] == 0) {
            $praLib = new praLib();

            $dataScad = $praLib->getDataScadenza($dati['Proric_rec']);
            if ($dataScad){
                if ($dataScad < date('Ymd')){
                    $ggScadenza = $praLib->getGiorniScadenza($dati['Proric_rec']['RICTSP']);;
                    $html->addAlert("Il termine ultimo per l'invio della richiesta è scaduto il " . frontOfficeLib::convertiData($dataScad) . 
                            " perchè aperta da più di " . $ggScadenza . " giorni. L'inoltro non sarà possibile" , '', 'error');
                }
                else {
                    $ggdif = frontOfficeLib::dateDiffDays($dataScad, date('Ymd'));
//                    $ggdif = $dataScad - date('Ymd');
                    $html->addAlert("Il termine ultimo per l'invio della richiesta è il " . frontOfficeLib::convertiData($dataScad) . 
                            ". Ci sono ancora " . $ggdif  . " giorni alla scadenza", '', 'info');
                }
            }
        }
        
        
        
        if ($dati['Consulta']) {
            $msg = '';
            if ($dati['passiDisponibili']) {
                $msg = "La Pratica è disponibile in modalita' Visualizzazione escluso i seguenti passi che si possono modificare: <br>";
                foreach ($dati['passiDisponibili'] as $passo) {
                    $href_indice = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'navClick',
                                'seq' => $passo['ITESEQ'],
                                'direzione' => '',
                                'ricnum' => $passo['RICNUM']
                    ));
                    $msg .= '- <a title="Vai al passo" href="' . $href_indice . '"><b>' . $passo['ITEDES'] . '</b></a><br>';
                }
            } else {
                $msg = "La Pratica è disponibile solo in modalita' Visualizzazione";
            }
            $html->addAlert($msg, 'ATTENZIONE !!!', 'info');
        }


        /*
         * Avviso per richiesta accorpata
         */
        if ($dati['Proric_rec']['RICRUN']) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php';
            $praLib = new praLib();
            $proric_padre_rec = $praLib->GetProric($dati['Proric_rec']['RICRUN'], 'codice', $dati['PRAM_DB']);
            $anapra_padre_rec = $praLib->GetAnapra($proric_padre_rec['RICPRO'], 'codice', $dati['PRAM_DB']);

            $urlPadre = ItaUrlUtil::GetPageUrl(array('event' => 'navClick', 'ricnum' => $dati['Proric_rec']['RICRUN'], 'direzione' => 'primoAcc'));
            $oggettoProric = intval(substr($dati['Proric_rec']['RICRUN'], 4)) . '/' . substr($dati['Proric_rec']['RICRUN'], 0, 4);
            $oggettoProric .= ' - ' . $anapra_padre_rec['PRADES__1'] . $anapra_padre_rec['PRADES__2'] . $anapra_padre_rec['PRADES__3'] . $anapra_padre_rec['PRADES__4'];
            $buttonRichiesta = '<br><br>' . $html->getButton('<i class="icon ion-reply italsoft-icon"></i><span>Torna alla richiesta principale</span>', $urlPadre);
            $infoScollegamento = sprintf('<br>Nel caso si desideri scollegare la richiesta, è possibile farlo dal <a href="%s">passo di gestione richieste accorpate</a> della richiesta principale.', $urlPadre);

            if ($dati['countEsg'] == $dati['countObl']) {
                $html->addAlert(sprintf('&Egrave; possibile procedere con la compilazione della richiesta principale <a href="%s">%s</a>.' . $infoScollegamento . $buttonRichiesta, $urlPadre, $oggettoProric), 'Richiesta accorpata completata', 'success');
            } else {
                $html->addAlert(sprintf('Si sta compilando una richiesta accorpata alla richiesta n. <a href="%s">%s</a>.' . $infoScollegamento . $buttonRichiesta, $urlPadre, $oggettoProric), 'Richiesta accorpata in corso', 'info');
            }
        }

        /*
         * Navigatore passi
         */
        if ($dati['Navigatore']) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNavOriz.class.php';
            $praHtmlNavOriz = new praHtmlNavOriz25();
            $html->appendHtml($praHtmlNavOriz->GetNavOriz($dati, $extraParms));
        }

        /*
         * Testata informativa.
         */
        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlTestata.class.php';
        $praHtmlTestata = new praHtmlTestata();
        $html->appendHtml($praHtmlTestata->GetTestata($dati, $img_base, $extraParms, $tipoPasso));

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_PRE_RENDER_PASSO, $dati, $html)) {
            return output::$html_out;
        }

        if (!$this->callStandardExit(praLibStandardExit::AZIONE_PRE_RENDER_PASSO, $dati, $html)) {
            return output::$html_out;
        }

        return $html->getHtml();
    }

    public function disegnaFooter($dati, $extraParms) {
        $html = new html();

        if (!$this->callCustomClass(praLibCustomClass::AZIONE_POST_RENDER_PASSO, $dati, $html)) {
            return output::$html_out;
        }

        if (!$this->callStandardExit(praLibStandardExit::AZIONE_POST_RENDER_PASSO, $dati, $html)) {
            return output::$html_out;
        }

        $html->appendHtml('</div>'); // .col-9-12

        $html->appendHtml('</div>'); // .grid

        return $html->getHtml();
    }

    protected function callCustomClass($azione, $dati, $htmlBuffer, $azione_passo = false) {
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

    private function callStandardExit($azione, $dati, $htmlBuffer) {
        /* @var $objAzione praLibStandardExit */
        $objAzione = praLibStandardExit::getInstance($this->praLib);
        $objAzione->setFrontOfficeLib($this->frontOfficeLib);

        $retAzione = $objAzione->getFunzioneTipoPasso($azione, $dati);

        switch ($retAzione) {
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
            default:
                $htmlBuffer->appendHtml($retAzione);
                break;
        }

        return 1;
    }

}
