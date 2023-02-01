<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praTemplateUploadCartella extends praTemplateLayout {

    function GetPagina($dati, $extraParms = array()) {
        $html = new html();
        $praLib = new praLib();
        $praLibAllegati = praLibAllegati::getInstance($praLib);

        $arrayAllegati = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . str_pad($dati['seq'], $dati['seqlen'], "0", STR_PAD_LEFT));

        foreach ($arrayAllegati as $key => $alle) {
            $ext = pathinfo($alle, PATHINFO_EXTENSION);
            if ($ext == 'info' || $ext == 'err') {
                unset($arrayAllegati[$key]);
            }
        }

        sort($arrayAllegati);

        $html->appendHtml('<div id="ita-praMupBody" class="ita-blockBody">');
        $url = ItaUrlUtil::GetPageUrl(array());

        $html->addForm($url, 'POST', array(
            'enctype' => 'multipart/form-data'
        ));

        $html->addHidden('event', 'seqClick');
        $html->addHidden('model', $extraParms['CLASS']);
        $html->addHidden('seq', $dati['Ricite_rec']['ITESEQ']);
        $html->addHidden('ricnum', $dati['Ricite_rec']['RICNUM']);

        $img_base = frontOfficeLib::getIcon('folder');
        $tipoPasso = 'Cartella Upload';

        $html->appendHtml($this->disegnaHeader($dati, $extraParms, $img_base, $tipoPasso));

        $html->appendHtml('<div style="height:auto;" class="divAction">');
        $html->appendHtml('<div style="height:auto; width: 100%;" class="ui-widget-content ui-corner-all boxInfo">');

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlDescBox.class.php';
        $praHtmlDescBox = new praHtmlDescBox();
        $html->appendHtml($praHtmlDescBox->GetDescBox($dati, ''));

        $html->appendHtml('<div style="text-align: right;">');

        if (!count($arrayAllegati)) {
            $html->appendHtml('<h3>Nessun allegato caricato.</h3>');
        } else {
            $textSpazioOccupato = '';

            $metadata_praclt = unserialize($dati['Praclt_rec']['CLTMETA']);
            if ($metadata_praclt && isset($metadata_praclt['METAOPEFO'])) {
                $limiteUploadCartella = (int) $metadata_praclt['METAOPEFO']['LIMITE_UPLOAD_CARTELLA'];
                if ($limiteUploadCartella) {
                    $dimensioniPasso = round($praLibAllegati->getDimensioniAllegatiPasso($dati), 3);
                    $textSpazioOccupato = "<br><small>(occupati {$dimensioniPasso}MB su {$limiteUploadCartella}MB)</small>";
                }
            }

            if (count($arrayAllegati) == 1) {
                $html->appendHtml("<h3>1 allegato caricato.$textSpazioOccupato</h3>");
            } else {
                $html->appendHtml('<h3>' . count($arrayAllegati) . ' allegati caricati.' . $textSpazioOccupato . '</h3>');
            }
        }

        $html->appendHtml('</div><br>');

        /*
         * Messaggi di errore
         */

        if ($dati['Ricite_rec']['RICERF'] == 1 || $dati['Ricite_rec']['RICERM'] != '') {
            $fileErrors = json_decode($dati['Ricite_rec']['RICERM'], true);

            foreach ($fileErrors as $fileError) {
                $html->addAlert(utf8_decode($fileError['error']), $fileError['file'], 'error');
            }
        }

        /*
         * Estensioni permesse
         */

        $arrayExt = $praLib->Estensioni($dati['Ricite_rec']['ITEEXT']);
        if ($arrayExt) {
            $html->appendHtml('Upload di file solo con le seguenti estensioni: ');

            foreach ($arrayExt as $ext) {
                if ($ext == 'pdfa') {
                    $ext = "pdf in modalità PDF/A";
                }

                $html->appendHtml("<span style=\"font-size:1.8em;\" class=\"legenda\"><b>$ext</b></span>&nbsp");
            }

            $html->appendHtml('<br><br>');
        }

        $limitiUpload = $praLibAllegati->GetParametriLimitiUpload($dati['PRAM_DB']);
        $limiteUploadSingolo = (int) $limitiUpload['LIMUPL_SINGOLO'];

        frontOfficeApp::$cmsHost->addCss(ItaUrlUtil::UrlInc() . '/vendor/plupload/2.3.6/jquery.plupload.queue/css/jquery.plupload.queue.css');
        frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/plupload/2.3.6/plupload.full.min.js');
        frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/plupload/2.3.6/jquery.plupload.queue/jquery.plupload.queue.min.js');
        frontOfficeApp::$cmsHost->addJs(ItaUrlUtil::UrlInc() . '/vendor/plupload/2.3.6/i18n/it.js');

        $metadata = '';
        if ($limiteUploadSingolo) {
            $metadata = " { filters: { max_file_size: '{$limiteUploadSingolo}mb' } }";
        }

        $html->appendHtml('<div class="italsoft-uploader' . $metadata . '"><p>Il tuo browser non supporta HTML5.</p></div>');
        $html->appendHtml('<br>');

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlNote.class.php';
        $praHtmlNote = new praHtmlNote();
        $html->appendHtml($praHtmlNote->GetNote($dati));

        if (count($arrayAllegati)) {
            require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlGridAllegati.class.php';
            $praHtmlGridAllegati = new praHtmlGridAllegati();
            $html->appendHtml($praHtmlGridAllegati->GetGrid($dati, $arrayAllegati, $extraParms));
        }

        $html->appendHtml("</div>");
        $html->appendHtml("</div>");

        require ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlLegenda.class.php';
        $praHtmlLegenda = new praHtmlLegendaLight();
        $html->appendHtml($praHtmlLegenda->GetLegenda());

        $html->appendHtml($this->disegnaFooter($dati, $extraParms));

        $html->appendHtml('</form>');

        $html->appendHtml('</div>');

        return $html->getHtml();
    }

    public function getHtmlButtonIncorpora($dati) {
        $html = new html();
        $praLib = new praLib();
        $ricite_cartella_rec = false;

        foreach ($dati['Ricite_tab'] as $ricite_rec) {
            $praclt_rec = $praLib->GetPraclt($ricite_rec['ITECLT'], 'codice', $dati['PRAM_DB']);
            if ($praclt_rec['CLTOPEFO'] === praLibStandardExit::FUN_FO_PASSO_CARTELLA) {
                $ricite_cartella_rec = $ricite_rec;
                break;
            }
        }

        if (!$ricite_cartella_rec) {
            return '';
        }

        $arrayAllegati = $praLib->GetRicdoc($ricite_cartella_rec['ITEKEY'], 'itekey', $dati['PRAM_DB'], true, $ricite_cartella_rec['RICNUM']);

        if (!count($arrayAllegati)) {
            return '';
        }

        $html->appendHtml('<input type="hidden" name="allegatoCartella" id="allegatoCartella" value="">');
        $html->appendHtml('<button class="italsoft-button" type="button" onclick="jQuery(\'#div_elencoCartellaUpload_ric\').dialog( { height: $(window).height() - 100, modal: true, title: \'Seleziona il file da caricare\', width: $(window).width() - 100, draggable: false, resizable: false } ).parent().css( { position: \'fixed\', top: \'50px\' } );">');
        $html->appendHtml('<i class="icon ion-folder italsoft-icon"></i> <span>Carica da cartella risorse</span>');
        $html->appendHtml('</button>');

        $html->appendHtml('<div id="div_elencoCartellaUpload_ric" style="display: none;">');

        $datiTabellaLookup = array(
            'header' => array(
                array('text' => 'Nome'),
            ),
            'body' => array()
        );

        foreach ($arrayAllegati as $fileAllegato) {
            $allegatoImage = frontOfficeLib::getFileIcon($fileAllegato['DOCNAME']);

            $script = "$('#allegatoCartella').val('{$fileAllegato['ROWID']}').closest('form').submit(); $('body').addClass('italsoft-loading');";

            $textAllegati = '<a href="#" onclick="' . $script . '" style="display: block;">';
            $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px; word-break: break-all;">';
            $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
            $textAllegati .= $html->getImage($allegatoImage, '24px');
            $textAllegati .= '  </div>';
            $textAllegati .= $fileAllegato['DOCNAME'];
            $textAllegati .= '</div>';
            $textAllegati .= '</a>';

            $datiTabellaLookup['body'][] = array($textAllegati);
        }

        $html->addTable($datiTabellaLookup, array(
            'sortable' => true,
            'filters' => true
        ));

        $html->appendHtml("<br /></div>");

        return $html->getHtml();
    }

}
