<?php

require_once ITA_SUAP_PATH . '/SUAP_praInf/praInf.php';

class praInfSportello extends praInf {

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

        output::appendHtml("</div>"); //\0

        $infoFOUnsorted = unserialize($dati['Anatsp_rec']['TSPMETA']);
        $infoFO = $this->praLib->array_sort($infoFOUnsorted, "CODICE");

        foreach ($infoFO as $schedaInformativa) {
            $contenutoScheda = $this->praLib->valorizzaTemplate($schedaInformativa['CONTENUTO'], $dati['Dizionario_Procedimento']->getAllData());
            if (!trim(strip_tags($contenutoScheda))) {
                continue;
            }

            $treeViewData[$schedaInformativa['DESCRIZIONE']] = array('childs' => array($contenutoScheda => array()));
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
