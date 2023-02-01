<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlLegenda
 *
 * @author Andrea
 */
class praHtmlGridPassi {

    function GetGridPassiDisponibili($dati, $online_page) {
//        $praLib = new praLib();
        $html = new html();

        /*
         * Mi trovo i passi disponibili tramite le ACL
         */
//        $arrPassiDisponibili = array();
//        foreach ($dati['Ricacl_tab'] as $acl_rec) {
//            if ($acl_rec['RICACLMETA']) {
//                $arrAcl = json_decode($acl_rec['RICACLMETA'], true);
//                if (is_array($arrAcl)) {
//                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
//                        if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
//                            $arrPassiDisponibili[] = $praLib->GetRicite($autorizzazione['ROW_ID_PASSO'], "rowid", $dati["PRAM_DB"], false);
//                        }
//                    }
//                }
//            }
//        }

        /*
         * Disegno la tabella
         */
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNavOriz.class.php');
        $praHtmlNavOriz25 = new praHtmlNavOriz25();


        $tableData = array('header' => array(
                'Numero', 'Descrizione'
            ), 'body' => array(), 'style' => array(
                'header' => array('width: 5%;', 'width: 95%;')
        ));

        $html->appendHtml("<div id=\"divElencoPassi\" style=\"display: inline-block; padding-bottom: 1.6em;\">");


        foreach ($dati['passiDisponibili'] as $passo) {
            $trovato = false;
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

                if ($passo['ROWID'] == $record['ROWID']) {
                    $trovato = true;
                    $href_indice = ItaUrlUtil::GetPageUrl(array(
                                'p' => $online_page,
                                'event' => 'navClick',
                                'seq' => $record['ITESEQ'],
                                'direzione' => '',
                                'ricnum' => $record['RICNUM']
                    ));
                    $labelPasso = '<span class="italsoft-button italsoft-button--circled" style="font-size: .4em; margin: 0 .6em 0 1em; vertical-align: middle; background-color: ' . $praHtmlNavOriz25->colorePasso[$title] . ';"></span>';

                    $tableData['body'][] = array(
                        $labelPasso . '<span style="vertical-align: middle; font-weight: bold;">' . ($key + 1) . '</span>',
                        '<a title="Vai al passo" href="' . $href_indice . '">' . $record['ITEDES'] . '</a>'
                    );
                }
            }
            if (!$trovato) {
                $tableData['body'][] = array(
                    $labelPasso = '<span style="vertical-align: middle; font-weight: bold;">' . '--' . '</span>',
                    '<span>' . $passo['ITEDES'] . ' (Passo NON ancora DISPONIBILE)  </span>'
                );
            }
        }

        $html->addTable($tableData, array(
            'sortable' => true,
            'paginated' => false,
        ));

        $html->appendHtml("</div>");

        return $html->getHtml();
    }

    function GetGridPassiDaGestire($dati) {
        $html = new html();

        /*
         * Disegno la tabella
         */
        require_once(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNavOriz.class.php');
        $praHtmlNavOriz25 = new praHtmlNavOriz25();


        $tableData = array('header' => array(
                'Numero', 'Descrizione'
            ), 'body' => array(), 'style' => array(
                'header' => array('width: 5%;', 'width: 95%;')
        ));

        $html->appendHtml("<div id=\"divElencoPassi\" style=\"padding-bottom: 1.6em;\">");

        $praLibAcl = new praLibAcl();

        foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $record) {
            if ($praLibAcl->checkAclAttiva($dati['Ricacl_tab_totali'], 'GESTIONE_PASSO', '', '', $record['ROWID'])) {
                continue;
            }
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
                        'event' => 'confermaPasso',
                        'ricnum' => $record['RICNUM'],
                        'seq' => $record['ITESEQ']
            ));


            $labelPasso = '<span class="italsoft-button italsoft-button--circled" style="font-size: .4em; margin: 0 .6em 0 1em; vertical-align: middle; background-color: ' . $praHtmlNavOriz25->colorePasso[$title] . ';"></span>';

            $tableData['body'][] = array(
                $labelPasso . '<span style="vertical-align: middle; font-weight: bold;">' . ($key + 1) . '</span>',
                '<a title="Vai al passo" href="' . $href_indice . '">' . $record['ITEDES'] . '</a>'
            );
        }

        $html->addTable($tableData, array(
            'sortable' => true,
            'paginated' => false,
        ));

        $html->appendHtml("</div>");

        return $html->getHtml();
    }

}
