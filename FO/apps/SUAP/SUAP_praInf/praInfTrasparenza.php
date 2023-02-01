<?php

require_once ITA_SUAP_PATH . '/SUAP_praInf/praInf.php';

class praInfTrasparenza extends praInf {

    public function disegnaPagina($dati) {
        //$html = new html();

        output::addForm('form1', 'praInf.php');
        output::appendHtml("<div class=\"divInfo\">");

        //
        // DIV DESCRIZONE PROCEDIMENTO 
        //
        output::appendHtml('<div class="ui-widget ui-widget-content ui-corner-all infoHead">'); //1
        output::appendHtml('<div style="display: inline-block; padding: 1em; max-width: 600px;">'); //2
        output::appendHtml('<span class="infoText"><b>Settore di appartenenza</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Anaset_rec']['SETDES'] . '</span>');
        output::appendHtml('<span class="infoText"><b>Tipo di attivita\'</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Anaatt_rec']['ATTDES'] . '</span>');
        output::appendHtml('<span class="infoText"><b>Oggetto della domanda</b></span>');
        output::appendHtml('<span class="infoTextCenter">' . $dati['Oggetto'] . '</span>');
        output::appendHtml('</div>'); // \2

        output::openTag('div', array('style' => 'float: right;'));

        /*
         * <div> Modello informativo
         */
        if ($dati['Anpdoc_rec']) {
            $T_docu = $dati['Anpdoc_rec']['ANPFIL'];
            $Img = frontOfficeLib::getFileIcon($T_docu);
            $allegato = frontOfficeApp::encrypt($T_docu);

            $href = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'vediAllegato',
                    'procedi' => $dati["Anapra_rec"]['PRANUM'],
                    'allegato' => $allegato,
                    'type' => 'doc',
                    "sportello" => $dati['Iteevt_rec']['IEVTSP']
            ));

            output::openTag('div', array('class' => 'divPdfInf', 'style' => 'vertical-align: top;'));
            output::openTag('a', array('href' => $href, 'target' => '_blank'));
            output::addImage($Img, '56px');
            output::addBr(2);
            output::appendHtml('<span class="infoCompile">Modello</span>');
            output::closeTag('a');
            output::closeTag('div');
        }

        /*
         * <div> Compila online
         */
        if ($dati['Itepas_tab']) {
            $href = $dati['CompilaHref'];

            output::openTag('div', array('class' => 'divCompile'));
            output::openTag('a', array('href' => $href));
            output::addImage(frontOfficeLib::getIcon('notepad'), '56px');
            output::addBr(2);
            output::appendHtml('<span class="infoCompile">Compila<br />on-line</span>');
            output::closeTag('a');
            output::closeTag('div');
        }

        output::closeTag('div');

        output::appendHtml('<div style="clear: both;"></div>');

        output::appendHtml("</div>"); //\1
        //
        output::appendHtml("</div>"); //\0
        //
        $infoFOtmp = unserialize($dati['Anatsp_rec']['TSPMETA']);
        $infoFO = $this->praLib->array_sort($infoFOtmp, "CODICE");

        // CONTENUTO DIV
        //          SERVIZIO
        $treeViewData = array();

        $treeViewData['Servizio'] = array('childs' => array('Sportello delle Imprese' => array()));

        //          UNITA OPERATIVA
        if ($dati['Anauni_rec']) {
            $content = "U.O. {$dati['Anauni_rec']['UNIDES']}";
            $treeViewData['Unità Organizzativa Responsabile dell\'istruttoria'] = array('childs' => array($content => array()));
        }

        //          RESPONSABILE
        $resObj = new praResp();
        $resHtml = $resObj->getHtml($dati);
        if ($resHtml !== false) {
            $treeViewData['Responsabile del Procedimento'] = array('childs' => array($resHtml => array()));
        }

        //          INFO FO
        $trovato = false;
        $infoFOtmp = $infoFO;
        foreach ($infoFOtmp as $keyInfoFO => $info) {
            if ($info['DESCRIZIONE'] == "Discipline Sanzionatorie") {
                $trovato = true;
                unset($infoFOtmp[$keyInfoFO]);
                break;
            }
        }
        foreach ($infoFOtmp as $info) {
            $treeViewData[$info['DESCRIZIONE']] = array('childs' => array($info['CONTENUTO'] => array()));
        }


        //          INQUADRAMENTO 
        $inqObj = new praInq();
        $inqHtml = $inqObj->getHtml($dati);
        if ($inqHtml !== false) {
            $treeViewData['Descrizione'] = array('childs' => array($inqHtml => array()), 'active' => true);
        }

        //TODO: da anagrafica portato a Info FO
        //          DISCIPLINE SANZIONATORIE 
        $disObj = new praDis();
        $disObj->setConfig($this->config);
        $disHtml = $disObj->getHtml($dati);
        if ($disHtml != false) {
            $treeViewData['Disciplina Sanzionatoria'] = array('childs' => array($disHtml => array()));
        } else {
            if ($trovato == true) {
                foreach ($infoFO as $info) {
                    if ($info['DESCRIZIONE'] == "Discipline Sanzionatorie") {
                        $treeViewData[$info['DESCRIZIONE']] = array('childs' => array($info['CONTENUTO'] => array()));
                    }
                }
            }
        }

        //          NORMATIVA 
        $norObj = new praNor();
        $norHtml = $norObj->getHtml($dati);
        if ($norHtml !== false) {
            $treeViewData['Normativa di Riferimento'] = array('childs' => array($norHtml => array()));
        }

        //          MODULISTICA 
        $moduObj = new praModu();
        $moduObj->setConfig($this->config);
        $moduHtml = $moduObj->getHtml($dati);
        if ($moduHtml !== false) {
            //
            $alleObj = new praAlle();
            $alleObj->setConfig($this->config);
            $alleHtml = $alleObj->getHtml($dati);
            if ($alleHtml !== false) {
                $moduHtml .= $alleHtml;
            }

            $treeViewData['Modulistica e allegati'] = array('childs' => array($moduHtml => array()));
        }

        //          ALTRI PROCEDIMENTI CORRELATI
        $procCorrObj = new praProcCorr();
        $procCorrObj->setConfig($this->config);
        $procHtml = $procCorrObj->getHtml($dati);
        if ($procHtml !== false) {
            $treeViewData['Altri Procedimenti Correlati'] = array('childs' => array($procHtml => array()));
        }

        $open_as = html::TREEVIEW_EXPANDED;
        if ($this->config['open_accordion'] == 1) {
            $treeViewData[key($treeViewData)]['active'] = 1;
            $open_as = html::TREEVIEW_ACCORDION;
        }

        output::addTreeView($treeViewData, $open_as);

        output::appendHtml("</form>");

        return true;
    }

}
