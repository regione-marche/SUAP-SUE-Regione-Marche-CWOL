<?php

require_once ITA_SUAP_PATH . '/SUAP_praInf/praInf.php';

class praInfCittadino extends praInf {

    private function normalize($string) {
        return substr(strtolower(preg_replace('/([^a-z\d]+)/i', '-', $string)), 0, 50);
    }

    public function disegnaPagina($dati) {
        $htmlCentercol = new html;
        $htmlRightcol = new html;
        $htmlLeftcol = new html;

        $elencoParagrafi = array();

        $modelloInformativo = false;
        if ($dati['Anpdoc_rec']) {
            $modelloInformativo = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'vediAllegato',
                    'procedi' => $dati['Anapra_rec']['PRANUM'],
                    'allegato' => frontOfficeApp::encrypt($dati['Anpdoc_rec']['ANPFIL']),
                    'type' => 'doc',
                    "sportello" => $dati['Iteevt_rec']['IEVTSP']
            ));
        }

        /*
         * CENTER COLUMN
         */

        $htmlCentercol->appendHtml('<div class="grid">');

        $htmlCentercol->appendHtml('<div class="col-2-3">');
        $htmlCentercol->appendHtml('<h1 class="italsoft--text-primary" style="margin-bottom: 5px;">' . $dati['Oggetto'] . '</h1><br>');
        $htmlCentercol->appendHtml('<b>Settore di appartenenza</b> &nbsp; ' . $dati['Anaset_rec']['SETDES'] . '<br>');
        $htmlCentercol->appendHtml('<b>Tipo di attività</b> &nbsp; ' . $dati['Anaatt_rec']['ATTDES'] . '<br>');
        $htmlCentercol->appendHtml('</div>');

        $htmlCentercol->appendHtml('<div class="col-1-3" style="text-align: right;">');

        if ($modelloInformativo) {
            $htmlCentercol->appendHtml('<div style="vertical-align: top; display: inline-block; text-align: center; padding: 1em;">');
            $htmlCentercol->appendHtml('<a href="' . $modelloInformativo . '" target="_blank">');
            $htmlCentercol->addImage(frontOfficeLib::getFileIcon($dati['Anpdoc_rec']['ANPFIL']), '56px');
            $htmlCentercol->addBr();
            $htmlCentercol->appendHtml('<span class="infoCompile">Modello</span>');
            $htmlCentercol->appendHtml('</a>');
            $htmlCentercol->appendHtml('</div>');
        }

        if ($dati['Itepas_tab']) {
            $htmlCentercol->appendHtml('<div style="display: inline-block; text-align: center; padding: 1em;">');
            $htmlCentercol->appendHtml('<a href="' . $dati['CompilaHref'] . '">');
            $htmlCentercol->addImage(frontOfficeLib::getIcon('notepad'), '56px');
            $htmlCentercol->addBr();
            $htmlCentercol->appendHtml('<span class="infoCompile">Compila<br />on-line</span>');
            $htmlCentercol->appendHtml('</a>');
            $htmlCentercol->appendHtml('</div>');
        }

        $htmlCentercol->appendHtml('</div>');

        $htmlCentercol->appendHtml('</div>');
        $htmlCentercol->addBr(3);

        if ($this->config['Inquadramento'] == 1) {
            $inqObj = new praInq();
            $inqHtml = $inqObj->getHtml($dati);
            if ($inqHtml !== false) {
                preg_match_all('/<h\d.*?>(.*?)<\/h\d>/i', $inqHtml, $matches);

                foreach ($matches[0] as $k => $match_html) {
                    $match_desc = $matches[1][$k];

                    $anchorId = $this->normalize($match_desc);

                    $elencoParagrafi[$anchorId] = $match_desc;

                    if (strpos($match_html, 'id="') !== false) {
                        $replacementHeader = preg_replace('/(?<=id=")(.+)(?=")/i', $anchorId, $match_html);
                        $inqHtml = str_replace($match_html, $replacementHeader, $inqHtml);
                    } else {
                        $inqHtml = str_replace($match_html, substr($match_html, 0, 3) . ' id="' . $anchorId . '" ' . substr($match_html, 3), $inqHtml);
                    }
                }

                $htmlCentercol->appendHtml($inqHtml);
            }
        }

        if ($this->config['Elenco'] == 1) {
            $elencoObj = new praElenco();
            $elencoObj->setConfig($this->config);
            $elencoHtml = $elencoObj->getHtml($dati);
            if ($elencoHtml !== false) {
                $elencoParagrafi['richieste-in-corso'] = 'Richieste in corso';
                $htmlCentercol->appendHtml('<br><h2 id="richieste-in-corso">Richieste in corso</h2>');
                $htmlCentercol->appendHtml($elencoHtml);
            }
        }

        $procCorrObj = new praProcCorr();
        $procCorrObj->setConfig($this->config);
        $procHtml = $procCorrObj->getHtml($dati);
        if ($procHtml !== false) {
            $elencoParagrafi['procedimenti-correlati'] = 'Procedimenti correlati';
            $htmlCentercol->appendHtml('<br><h2 id="procedimenti-correlati">Procedimenti correlati</h2>');
            $htmlCentercol->appendHtml($procHtml);
        }

        if ($this->config['Normativa'] == 1) {
            $norObj = new praNor();
            $norHtml = $norObj->getHtml($dati);
            if ($norHtml !== false) {
                $elencoParagrafi['normative'] = 'Normative';
                $htmlCentercol->addBr(2);
                $htmlCentercol->appendHtml('<h2 id="normative">Normative</h2>');
                $htmlCentercol->addBr();
                $htmlCentercol->appendHtml($norHtml);
            }
        }

        if ($this->config['Requisiti'] == 1) {
            $reqObj = new praReq();
            $reqHtml = $reqObj->getHtml($dati);
            if ($reqHtml !== false) {
                $elencoParagrafi['requisiti'] = 'Requisiti';
                $htmlCentercol->addBr(2);
                $htmlCentercol->appendHtml('<h2 id="requisiti">Requisiti</h2>');
                $htmlCentercol->addBr();
                $htmlCentercol->appendHtml($reqHtml);
            }
        }

        if ($this->config['Termini'] == 1) {
            $terObj = new praTer();
            $terHtml = $terObj->getHtml($dati);
            if ($terHtml !== false) {
                $elencoParagrafi['termini'] = 'Termini';
                $htmlCentercol->addBr(2);
                $htmlCentercol->appendHtml('<h2 id="termini">Termini</h2>');
                $htmlCentercol->addBr();
                $htmlCentercol->appendHtml($terHtml);
            }
        }

        if ($this->config['Oneri'] == 1) {
            $onrObj = new praOneri();
            $onrHtml = $onrObj->getHtml($dati);
            if ($onrHtml !== false) {
                $elencoParagrafi['oneri'] = 'Oneri';
                $htmlCentercol->addBr(2);
                $htmlCentercol->appendHtml('<h2 id="oneri">Oneri</h2>');
                $htmlCentercol->addBr();
                $htmlCentercol->appendHtml($onrHtml);
            }
        }

//        if ($this->config['Discipline'] == 1) {
        $praDis = new praDis();
        $disHtml = $praDis->getHtml($dati);
        if ($disHtml !== false) {
            $elencoParagrafi['discipline-sanzionatorie'] = 'Discipline sanzionatorie';
            $htmlCentercol->addBr(2);
            $htmlCentercol->appendHtml('<h2 id="discipline-sanzionatorie">Discipline sanzionatorie</h2>');
            $htmlCentercol->addBr();
            $htmlCentercol->appendHtml($disHtml);
        }
//        }

        if ($this->config['Responsabile'] == 1) {
            $resObj = new praResp();
            $resHtml = $resObj->getHtml($dati);
            if ($resHtml !== false) {
                $elencoParagrafi['responsabile'] = 'Responsabile';
                $htmlCentercol->addBr(2);
                $htmlCentercol->appendHtml('<h2 id="responsabile">Responsabile</h2>');
                $htmlCentercol->addBr();
                $htmlCentercol->appendHtml($resHtml);
            }
        }

        if ($this->dati['Anauni_rec']['UNIDES']) {
            $elencoParagrafi['uo-organizzativa'] = 'Unita organizzativa responsabile dell\'istruttoria';
            $htmlCentercol->addBr(2);
            $htmlCentercol->appendHtml('<h2 id="uo-organizzativa">Unita organizzativa responsabile dell\'istruttoria</h2>');
            $htmlCentercol->addBr();
            $htmlCentercol->appendHtml('U.O. ' . $this->dati['Anauni_rec']['UNIDES']);
        }

        /*
         * LEFT COLUMN
         */

        $i = 1;
        foreach ($elencoParagrafi as $anchorId => $description) {
            $borderBottom = $i == count($elencoParagrafi) ? '' : ' border-bottom: 1px solid #ccc;';
            $htmlLeftcol->appendHtml('<a href="#' . $anchorId . '" style="display: block; padding: 15px 0;' . $borderBottom . '">' . $description . '</a>');
            $i++;
        }

        /*
         * RIGHT COLUMN
         */

        if ($modelloInformativo) {
            $htmlRightcol->appendHtml('<h3>Modulistica e allegati</h3>');
            $htmlRightcol->addBr();
            $htmlRightcol->appendHtml('<a href="' . $modelloInformativo . '" target="_blank">');
            $htmlRightcol->appendHtml('<span style="vertical-align: middle;">Modello informativo</span>');
            $htmlRightcol->addImage(frontOfficeLib::getFileIcon($dati['Anpdoc_rec']['ANPFIL']), '16px');
            $htmlRightcol->appendHtml('</a>');

            $praAlle = new praAlle();
            $praAlle->setConfig($this->config);
            $allHtml = $praAlle->getHtml($dati);
            if ($allHtml !== false) {
                $htmlRightcol->addBr(2);
                $htmlRightcol->appendHtml($allHtml);
            }
        }

        $praLink = new praLink();
        $praLinkHtml = $praLink->getHtml($dati);
        if ($praLinkHtml !== false) {
            $htmlRightcol->addBr(2);
            $htmlRightcol->appendHtml('<h3>Collegamenti</h3>');
            $htmlRightcol->addBr();
            $htmlRightcol->appendHtml($praLinkHtml);
        }

        /*
         * GRID
         */

        $centerColWidth = 12;
        $htmlLeftcolContent = $htmlLeftcol->getHtml();
        $htmlRightcolContent = $htmlRightcol->getHtml();

        if ($htmlLeftcolContent) {
            $centerColWidth -= 2;
        }

        if ($htmlRightcolContent) {
            $centerColWidth -= 3;
        }

        output::appendHtml('<div class="grid" style="max-width: none;">');

        if ($htmlLeftcolContent) {
            output::appendHtml('<div class="col-2-12">');
            output::appendHtml('<div style="background-color: #eee; padding: .6em 1.2em;">');
            output::appendHtml($htmlLeftcolContent);
            output::appendHtml('</div>');
            output::appendHtml('</div>'); // .col-2-12
        }

        output::appendHtml('<div class="col-' . $centerColWidth . '-12">');
        output::appendHtml($htmlCentercol->getHtml());
        output::appendHtml('</div>'); // .col-8-12

        if ($htmlRightcolContent) {
            output::appendHtml('<div class="col-3-12">');
            output::appendHtml('<div class="italsoft--bg-primary" style="padding: 1.2em;">');
            output::appendHtml($htmlRightcolContent);
            output::appendHtml('</div>');
            output::appendHtml('</div>'); // .col-3-12
        }

        output::appendHtml('</div>'); // .grid
        return true;
    }

}
