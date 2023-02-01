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
class praHtmlTestata {

    function GetTestata($dati, $img_base, $extraParms, $tipoPasso, $descTestata = '') {
        $html = new html();

        if (!$descTestata) {
            $descTestata = $dati['Ricite_rec']['ITEDES'];
        }
        
        
        if ($dati['Ricite_rec']['ITEIMG'] != "") {
            $href = ItaUrlUtil::GetPageUrl(array('event' => 'imgAzione', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
            $azione_img_tag = "<img src=\"$href\" style=\"height: 18px; vertical-align: middle; margin-right: 5px;\" />";
        } else {
            $azione_img_tag = "<img src=\"$img_base\" style=\"height: 18px; vertical-align: middle; margin-right: 5px;\" />";
        }

        /*
         * Inizio testata principale.
         */

        $html->appendHtml('<div style="padding: .6em 1em .2em; border: 1px solid #ddd; background-color: #e6e6e6; line-height: 2em;">');

        $html->appendHtml('<div class="grid" style="min-width: 0; padding: 0;">');
        $html->appendHtml('<div class="col-2-3">');

        $html->appendHtml('<h3 style="margin-bottom: .3em;">');
        $html->appendHtml($descTestata);
//        $html->appendHtml($dati['Ricite_rec']['ITEDES']);
        $html->appendHtml('</h3>');
        
        $html->appendHtml('</div>');
        $html->appendHtml('<div class="col-1-3 italsoft--xs-hidden italsoft--sm-hidden" style="text-align: right;">');

        $html->appendHtml('<span style="vertical-align: middle; font-size: .9em;">' . $tipoPasso . '</span>');
        $html->appendHtml($azione_img_tag);
        
        $html->appendHtml('</div>');
        $html->appendHtml('</div>');

        $html->appendHtml('</div>');

        /*
         * Fine testata principale.
         */

        return $html->getHtml();
    }

}
