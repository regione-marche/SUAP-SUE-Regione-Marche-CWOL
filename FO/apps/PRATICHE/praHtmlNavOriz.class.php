<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlNavOriz
 *
 * @author Andrea
 */
class praHtmlNavOriz25 {

    public $colorePasso = array(
        'Obbligatorio' => '#c93737',
        'Eseguito' => '#5cb75c',
        'Domanda' => '#6868ed',
        'Facoltativo' => 'orange',
        'Invio Mail' => 'navy',
        'Invio Richiesta' => 'navy'
    );

    function GetNavOriz($dati, $extraParms = array()) {
        $html = new html();

        $praLibAcl = new praLibAcl();
        $buttonStyle = 'vertical-align: middle; font-size: .9em;';

        $htmlHelp = '';
        if ($dati['Ricite_rec']['ITEHELP']) {
            $praLib = new praLib();
            $Anahelp_rec = $praLib->GetAnahelp($dati['Ricite_rec']['ITEHELP'], 'codice', $extraParms['PRAM_DB']);
            $htmlHelp = '<a href="' . $Anahelp_rec['HELPFORMATO'] . '" target="_blank" title="Clicca per attivare l\'Help" class="italsoft-button italsoft-button--circled" style="' . $buttonStyle . ' margin-right: .2em;"><i class="icon ion-help italsoft-icon"></i></a>';
        }

        $html->appendHtml('<div style="font-size: 1.1em; text-align: center; margin-top: 1em; position: relative;" id="praMup-NavigatorePassi">');

        $html->appendHtml('<h4 style="margin-bottom: .8em;">Indice dei passi (' . $dati['Navigatore']['Quanti'] . ')</h4>');

        /*
         * Apro 'div' per contenere e legare i bottoni con il primo div passo.
         */
        $html->appendHtml('<div style="display: inline-block;">');

        $html->appendHtml($htmlHelp);

        if ($dati['Navigatore']['Precedente'] === true) {
            $hrefOptionsBack = array(
                'event' => 'navClick',
                'seq' => $dati['Ricite_rec']['ITESEQ'],
                'ricnum' => $dati['Proric_rec']['RICNUM']
            );

            $hrefHome = ItaUrlUtil::GetPageUrl(array_merge($hrefOptionsBack, array('direzione' => 'home')));
            $hrefIndietro = ItaUrlUtil::GetPageUrl(array_merge($hrefOptionsBack, array('direzione' => 'indietro')));

            $html->appendHtml('<a href="' . $hrefHome . '" title="Vai al primo passo" class="italsoft-tooltip italsoft-button italsoft-button--circled" style="' . $buttonStyle . '"><i class="icon ion-ios-skipbackward italsoft-icon"></i></a>');
            $html->appendHtml('<a href="' . $hrefIndietro . '" title="Vai al passo precedente" class="italsoft-tooltip italsoft-button italsoft-button--circled" style="' . $buttonStyle . ' margin-right: .2em; margin-left: .2em;"><i class="icon ion-arrow-left-b italsoft-icon"></i></a>');
        }

        $countPassi = 1;
        $styleBloccoPasso = 'display: inline-block; font-size: .75em; padding: .5em; margin: 0 .1em 10px; line-height: 1.4em;';

        $array_keys = array_keys($dati['Navigatore']['Ricite_tab']);
        $kUltimoPasso = end($array_keys);

        foreach ($dati['Navigatore']['Ricite_tab'] as $k => $Ricite_rec) {
            $labelPasso = $k + 1;
            $backgroundColor = '#f4f4f4';

            $collegamentoPasso = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'navClick',
                        'seq' => $Ricite_rec['ITESEQ'],
                        'direzione' => '',
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $tipologiaPasso = '';

            if ($Ricite_rec['RICOBL'] != 0 || $Ricite_rec['CLTOBL'] != 0) {
                $tipologiaPasso = 'Obbligatorio';
            } else {
                $tipologiaPasso = 'Facoltativo';
            }

            if ($Ricite_rec['ITEQST'] != 0) {
                $tipologiaPasso = 'Domanda';
            }

            if ($Ricite_rec['ITEIRE'] != 0 || $Ricite_rec['ITEZIP'] != 0) {
                $tipologiaPasso = 'Invio Mail';
            }

            if (strpos($dati['Proric_rec']['RICSEQ'], '.' . $Ricite_rec['ITESEQ'] . '.') !== false) {
                $tipologiaPasso = 'Eseguito';
            }

            if ($Ricite_rec['ITESEQ'] == $dati['seq']) {
                $backgroundColor = $this->colorePasso[$tipologiaPasso] . '; color: #fff';
                $passoCorrente = $k + 1;
            }

            $icon = "";
            if ($dati['passiDisponibili'] || $dati['Consulta']) {
                $icon = $praLibAcl->getHtmlIcona($dati['passiDisponibili'], $Ricite_rec['ROWID']);
            }
            $labelPasso .= $icon . '<br />' . $tipologiaPasso;

            if ($k === $kUltimoPasso) {
                $html->appendHtml('<div style="display: inline-block;">');
            }

            $html->appendHtml('<a href="' . $collegamentoPasso . '" style="background-color: ' . $backgroundColor . '; border-top: 4px solid ' . $this->colorePasso[$tipologiaPasso] . '; ' . $styleBloccoPasso . '">' . $labelPasso . '</a>');

            if ($countPassi === 1) {
                $html->appendHtml('</div>');
            }

            $countPassi++;
        }

        if ($dati['Navigatore']['Successivo'] === true) {
            $hrefOptionsBack = array(
                'event' => 'navClick',
                'seq' => $dati['Ricite_rec']['ITESEQ'],
                'ricnum' => $dati['Proric_rec']['RICNUM']
            );

            $hrefAvanti = ItaUrlUtil::GetPageUrl(array_merge($hrefOptionsBack, array('direzione' => 'avanti')));
            $hrefEnd = ItaUrlUtil::GetPageUrl(array_merge($hrefOptionsBack, array('direzione' => 'primoRosso')));

            $html->appendHtml('<a href="' . $hrefAvanti . '" title="Vai al passo successivo" class="italsoft-tooltip italsoft-button italsoft-button--circled" style="' . $buttonStyle . ' margin-right: .2em; margin-left: .2em;"><i class="icon ion-arrow-right-b italsoft-icon"></i></a>');
            $html->appendHtml('<a href="' . $hrefEnd . '" title="Vai all\'ultimo passo" class="italsoft-tooltip italsoft-button italsoft-button--circled" style="' . $buttonStyle . '"><i class="icon ion-ios-skipforward italsoft-icon"></i></a>');
        }

        $html->appendHtml('<button type="button" id="elencoPassi" title="Elenco dei passi" class="italsoft-button italsoft-button--circled ita-button-elencopassi italsoft-tooltip" style="' . $buttonStyle . ' margin: 0 0 1.2em .2em;"><i class="icon ion-navicon-round italsoft-icon"></i></button>');

        /*
         * Chiusura div che contiene ultimo passo + bottoni.
         */
        $html->appendHtml('</div>');

        $html->appendHtml('</div>');

        //Numero Passo
//        $html->appendHtml('<div class="PassoDi" style="font-size: .9em; margin: .15em 0 1em;">');
//        $html->appendHtml(sprintf('<b>Sei sul passo %d di %d</b>', $dati['Navigatore']['Posizione'] + 1, $dati['Navigatore']['Quanti']));
//        $html->appendHtml("</div>");
        //Div Nascosto elenco passo
        $praHtmlNavOriz = new praHtmlNavElenco();
        $html->appendHtml($praHtmlNavOriz->GetNavElenco($dati, $extraParms, $passoCorrente));
        return $html->getHtml();
    }

}

class praHtmlNavElenco {

    function GetNavElenco($dati, $extraParms = array(), $passoCorrente = 1) {
        $praLibAcl = new praLibAcl();
        $praHtmlNavOriz25 = new praHtmlNavOriz25();
        $html = new html();

        $tableData = array('header' => array(
                //'Numero', 'Descrizione', 'Tipologia'
                'Numero', 'Descrizione'
            ), 'body' => array(), 'style' => array(
                'header' => array('width: 5%;', 'width: 65%;', 'width: 30%;')
        ));

        $html->appendHtml("<div id=\"divElencoPassi\" style=\"display: none; padding-bottom: 1.6em;\">");

        foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $record) {
            if ($record['RICOBL'] != 0 || $record['CLTOBL'] != 0) {
                $title = 'Obbligatorio';
            } else {
                $title = 'Facoltativo';
            }

            if ($record['ITEQST'] != 0) {
                $title = 'Domanda';
            }

            if ($record['ITEIRE'] != 0 || $record['ITEZIP'] != 0) {
                $title = 'Invio Richiesta';
            }

            if (strpos($dati['Proric_rec']['RICSEQ'], '.' . $record['ITESEQ'] . '.') !== false) {
                $title = 'Eseguito';
            }

            $href_indice = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'navClick',
                        'seq' => $record['ITESEQ'],
                        'direzione' => '',
                        'ricnum' => $dati['Proric_rec']['RICNUM']
            ));

            $labelPasso = '<span class="italsoft-button italsoft-button--circled" style="font-size: .4em; margin: 0 .6em 0 1em; vertical-align: middle; background-color: ' . $praHtmlNavOriz25->colorePasso[$title] . ';"></span>';

            $icon = "";
            if ($dati['passiDisponibili'] || $dati['Consulta']) {
                $icon = $praLibAcl->getHtmlIcona($dati['passiDisponibili'], $record['ROWID']);
            }

            $tableData['body'][] = array(
                //$labelPasso . '<span style="vertical-align: middle; font-weight: bold;">' . ($key + 1) . $record['ICON'] . '</span>',
                $labelPasso . '<span style="vertical-align: middle; font-weight: bold;">' . ($key + 1) . $icon . '</span>',
                '<a title="Vai al passo" href="' . $href_indice . '">' . $record['ITEDES'] . '</a>'
//                '<a title="Vai al passo" href="' . $href_indice . '">' . $record['ITEDES'] . '</a>',
//                $Praclt_rec['CLTDES']
            );
        }

        $html->addTable($tableData, array(
            'sortable' => true,
            'paginated' => true,
            'attrs' => array(
//            'data-rows-per-page' => '[5,10,20,50]',
                'data-page' => ceil($passoCorrente / 10),
                'data-save-pager' => true
            )
        ));

        $html->appendHtml("</div>");
        return $html->getHtml();
    }

}

?>
