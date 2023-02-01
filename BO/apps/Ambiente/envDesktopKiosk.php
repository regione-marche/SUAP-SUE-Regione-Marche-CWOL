<?php

/**
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Ambiente
 * @author     Michele Moscioni 
 * @copyright  1987-2015 Italsoft snc
 * @license 
 * @version    29.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function envDesktopKiosk($rootMenu = 'TI_MEN') {
    switch ($_POST['event']) {
        case 'callmodel':
            $timeout = 40 * 1000;
            $devLib = new devLib();
            $envDesktoBaseParam = $devLib->getEnv_config('ENVDESKTOP', 'codice', 'ITANOTICETIMER');
            if ($envDesktoBaseParam) {
                if ($envDesktoBaseParam[0]['CONFIG'] >= 60) {
                    $timeout = $envDesktoBaseParam[0]['CONFIG'] * 1000;
                }
            }

            $topbar = 1;
            if (isset($_POST['desktopParam']['topbar'])) {
                $topbar = $_POST['desktopParam']['topbar'];
            }

            $homepage = 1;
            if (isset($_POST['desktopParam']['homepage'])) {
                $homepage = $_POST['desktopParam']['homepage'];
            }

            Out::html('desktop', htmlDesktop($topbar, $homepage));

            if ($topbar == 1) {
                $logoHeader = App::getPath('general.fileEnte') . 'ente' . App::$utente->getKey('ditta') . '/ambiente/images/logoHead.png';
                if (file_exists($logoHeader)) {
                    $srcBase64 = itaImg::base64src($logoHeader);
                }
                Out::attributo('desktopLogo', 'src', '0', $srcBase64);
                $ParmEnte = itaLib::getParmEnte();
                if ($ParmEnte['DENOMINAZIONE']) {
                    $nomeEnte = $ParmEnte['DENOMINAZIONE'];
                    if ($ParmEnte['WWW']) {
                        $nomeEnte = '<a href="' . $ParmEnte['WWW'] . '">' . $nomeEnte . '</a>';
                    }
                    Out::html('desktopHeaderTitle', $nomeEnte);
                } else {
                    foreach (Config::$enti as $key => $ente) {
                        if ($ente['codice'] == App::$utente->getKey('ditta')) {
                            Out::html('desktopHeaderTitle', $key);
                            break;
                        }
                    }
                }
            }

            Out::codice('loadTabs();');
            Out::codice('resizeTabs();');

            if ($homepage == 1) {
                $desktopApp = 'menExplorer2';

                $_POST['rootMenu'] = $rootMenu;

                itaLib::openForm($desktopApp);
                $menExplorer2 = itaModel::getInstance($desktopApp);
                $menExplorer2->setEvent('openform');
                $menExplorer2->parseEvent();

                Out::delContainer("close-icon-$desktopApp");
                Out::setAppTitle($desktopApp, 'Home');
            }

            $datiAccesso = App::$utente->getKey('datiAccesso');

            if ($datiAccesso['nome'] || $datiAccesso['cognome']) {
                $htmlUtente = '<span style="display: inline-block; vertical-align: middle;">' . $datiAccesso['nome'] . ' ' . $datiAccesso['cognome'] . '</span>';
            } else if ($datiAccesso['codiceFiscale']) {
                $htmlUtente = '<span style="display: inline-block; vertical-align: middle;">' . $datiAccesso['codiceFiscale'] . '</span>';
            } else {
                $htmlUtente = '<span style="display: inline-block; vertical-align: middle;">' . App::$utente->getKey('nomeUtente') . '</span>';
            }

            if ($datiAccesso['spidCode']) {
                $icon_spid = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_spid.png');
                $htmlUtente .= "<img src=\"$icon_spid\" style=\"height: 16px; padding: 2px 10px; vertical-align: middle; border: 1px solid #fff; border-radius: 16px; margin: 2px 0 0 10px;\">";
            }

            Out::html('citywareRightHeader', $htmlUtente);
            break;
    }
}

function htmlDesktop($topbar, $homepage) {
    /*
     * Caricamento Icone
     */
    $icon_sh = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_sh_cityware.png');

    $tabul = '';
    if ($homepage == 1) {
        $tabul = '<ul></ul>';
    }

    $htmltopbar = '';
    if ($topbar == 1) {
        $htmltopbar = '
            <div id="desktopHeader" style="box-shadow: 0px 0px 5px 0px #333; z-index: 1; line-height: 48px;">
                <div id="headerBar" class="ui-widget-header">
                    <div id="info" style="margin-left: 10px; display: inline-block; float: none; margin-top: 0;">
                        <img id="desktopLogo" src="" style="float: none; vertical-align: middle; margin: 0 10px 0 5px; height: 40px;">
                        <div id="desktopHeaderTitle" style="vertical-align: middle;"></div>
                    </div>
                    
                    <div style="float: right; text-align: right;">
                        <!-- <div style="height: 36px; border-left: 1px solid #fff; display: inline-block; vertical-align: middle;"></div> -->
                        <img style="height: 40px; vertical-align: middle; margin: 0 15px 0 25px;" src="' . $icon_sh . '" />
                    </div>
                    <div id="citywareRightHeader" style="float: right; margin-right: 20px; line-height: 24px; font-size: 1.3em; text-align: right;"></div>
                </div>
            </div>
';
    }

    $html = '<div id="ita-desktop" class="ita-layout">' . $htmltopbar . '
                <div id="desktopBody" class="ita-content">
                    <div id="mainTabs" style="border-top: 0;">' . $tabul . '
                    </div>
                </div>
             </div>';

    return $html;
}
