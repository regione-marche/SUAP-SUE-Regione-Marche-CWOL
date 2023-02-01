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

function envDesktopItaEngine($rootMenu = 'TI_MEN') {
    $effect = 'slide';
    $transTime = '0';

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
            Out::html('desktop', htmlDesktop($topbar, $homepage, $rootMenu));

//            $generator = new itaGenerator();
//            $retHtml = $generator->getModelHTML('menPersonal');

            if ($topbar == 1) {
                $icon_tema = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_tema.png');
                $themeSwitcherIcon = '<img style=\"margin-top: 3px; vertical-align: middle;\" src=\"' . $icon_tema . '\" />';
                Out::codice('$(\'#themes\').themeswitcher( { initialText: "' . $themeSwitcherIcon . '", buttonPreText: "' . $themeSwitcherIcon . '" } );');
//                Out::codice('$(\'#themes\').themeswitcher();');
//                Out::codice("loadSlider();"); //

                $logoHeader = App::getPath('general.fileEnte') . 'ente' . App::$utente->getKey('ditta') . '/ambiente/images/logoHead.png';
                if (file_exists($logoHeader)) {
                    $srcBase64 = itaImg::base64src($logoHeader);
                }
                $srcUrl = "http://" . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . "/enteimg/logoente" . App::$utente->getKey('ditta') . '.gif';
                Out::attributo('desktopLogo', 'src', '0', $srcBase64);
                $ParmEnte = itaLib::getParmEnte();
                $data = App::$utente->getKey('DataLavoro');
                if ($ParmEnte['DENOMINAZIONE']) {
                    Out::html('desktopHeaderTitle', $ParmEnte['DENOMINAZIONE']);
                } else {
                    foreach (Config::$enti as $key => $ente) {
                        if ($ente['codice'] == App::$utente->getKey('ditta')) {
                            Out::html('desktopHeaderTitle', $key);
                            break;
                        }
                    }
                }
            }

            if ($homepage == 1) {
                Out::html('ita-home-content', $retHtml .
                        '<!-- <div id="ita-controlbar" style="height:100%">
                          </div> -->
                          <div id="ita-controlpad" class="ui-widget-content ui-corner-all" style="overflow:auto;border:0px solid blue;float:left;">
                          </div>');
//                Out::codice("$('#menPersonal').find('br:first').remove();");
//                Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'refresh',model:'menPersonal'});");
                Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'open',model:'envControlPad'});");
            }

            Out::hide('divBanner');
            Out::codice('loadTabs();');
            Out::codice('resizeTabs();');

            if ($homepage == 1) {
                Out::codice('setHomeLayout();');
            }

            Out::codice('resizeTabs();');
            if ($topbar == 1) {
                Out::codice('setItaTimer({element:"itaNotice",delay:' . $timeout . ',model:"envCheckNotice",async:true});');
            }
            break;
    }
}

function htmlDesktop($topbar, $homepage, $rootMenu) {
    /*
     * Caricamento Icone
     */
    $icon_recenti = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_recenti.png');
    $icon_menu = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_menu.png');
    $icon_tendina = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_tendina.png');
    $icon_info = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_info.png');
    $icon_utente = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_utente.png');
    $icon_esci = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_esci.png');
    $icon_sh = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_sh_italsoft.png');

    $htmlhomepage = '';
    if ($homepage == 1) {
        $homehtml_li = '<ul><li><a href="#ita-home" onclick="return false;">' . App::$utente->getKey('nomeUtente') . ' Home</a></li></ul>';
        $homehtml_div = '<div id="ita-home">
                        <div id="ita-home-content">
                        </div>
                    </div>';
    }
    //else {
//        $homehtml_div = '<div id="ita-home">
//                        <div id="ita-home-content">
//                        </div>
//                    </div>';
//    }


    $htmltopbar = '';
    if ($topbar == 1) {
        $htmltopbar = '
            <div id="desktopHeader" style="box-shadow: 0px 0px 5px 0px #333; z-index: 1; line-height: 48px;">
                <div id="headerBar" class="ui-widget-header">
                    <div id="menPersonalPopup_container" style="display: inline-block; vertical-align: middle;">
                        <a href="#"
                           id="menPersonalPopup"
                           title="Recenti"
                           style="margin: 0 0 0 2px; background: none; border: none; display: inline-block;"
                           class="ita-tooltip ita-button ita-popup-menu { flyOut: true, popupAutoClose: true, rootMenu: \'' . $rootMenu . '\', event: \'openButton\', model: \'menPersonal\' }"
                        >
                            <img style="vertical-align: middle;" src="' . $icon_recenti . '" />
                        </a>
                    </div>
                    
                    <a href="#"
                       title="App Center"
                       style="margin-left: 8px; text-decoration: none; vertical-align: middle; display: inline-block;"
                       class="ita-tooltip ita-hyperlink { request:\'ItaCall\', event: \'openform\', model: \'menExplorer2\', extraData: { rootMenu: \'' . $rootMenu . '\' } }"
                    >
                        <img style="vertical-align: middle;" src="' . $icon_menu . '" />
                    </a>
                    
                    <a href="#"
                       id="menButtonPopup"
                       title="Menu"
                       style="margin: 0 0 0 8px; background: none; border: none; display: inline-block;"
                       class="ita-tooltip ita-button ita-popup-menu { flyOut: true, rootMenu: \'' . $rootMenu . '\', event: \'openButton\', model: \'menButton\' }"
                    ><img style="vertical-align: middle;" src="' . $icon_tendina . '" /></a>
                
                    <div style="height: 36px; border-left: 1px solid #fff; display: inline-block; vertical-align: middle; margin-left: 12px;"></div>

                    <div id="info" style="margin-left: 10px; display: inline-block; float: none; margin-top: 0;">
                        <img id="desktopLogo" src="" style="float: none; vertical-align: middle; margin: 0 10px 0 5px; height: 40px;">
                        <div id="desktopHeaderTitle" style="vertical-align: middle;"></div>
                    </div>
                    
                    <div style="float: right; text-align: right;">
                        <img style="height: 40px; vertical-align: middle; margin: 0 5px 0 0;" src="' . $icon_sh . '" />

                        <div style="height: 36px; border-left: 1px solid #fff; display: inline-block; vertical-align: middle; margin-left: 5px;"></div>

                        <div  title="Cambia Tema" class="ita-tooltip" id="themes" style="height: 48px; margin-left: 5px; position: static; display: inline-block; vertical-align: middle; line-height: normal;"></div>

                        <a id="itaNotice"
                           title="Avvisi"
                           href="#"
                           style="text-decoration: none; margin-left: 9px; vertical-align: middle; display: inline-block;"
                           class="ita-tooltip ita-hyperlink { request: \'ItaCall\', event: \'openform\', model: \'envGestNotice\' }"
                        >
                            <img style="vertical-align: middle;" src="' . $icon_info . '" />
                        </a>
                        <span id="itaNotice_lbl" style="position: relative; top: -8px; left: -15px; background-color: #fff; display: inline-block; color: #333; border-radius: 20px; height: 20px; line-height: 20px; text-align: center; margin-right: -20px; padding: 0 7px;"><span style="color: red;">0</span></span>
                    
                        <a id="itaConfig"
                           title="Impostazioni e utilità"
                           href="#"
                           style="text-decoration: none; margin-left: 11px; vertical-align: middle; display: inline-block;"
                           class="ita-tooltip ita-hyperlink { request: \'ItaCall\', event: \'openform\', model: \'envConfig\' }"
                        >
                            <img style="vertical-align: middle;" src="' . $icon_utente . '" />
                        </a>
                    
                        <a id="itaLogout"
                           title="Termina sessione"
                           href="#"
                           style="text-decoration: none; margin: 0 5px 0 15px; vertical-align: middle; display: inline-block;"
                           class="ita-tooltip ita-hyperlink { request: \'ItaCall\', event: \'openform\', model: \'envLogout\' }"
                        >
                            <img style="vertical-align: middle;" src="' . $icon_esci . '" />
                        </a>
                    
                        <!--
                        <button type="button" id="itaNotice" name="itaNotice" style="vertical-align: middle; width: 50px; height: 20px; margin-top: 6px;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envGestNotice\',iconLeft:\'ui-icon ui-icon-notice\'}" value="" />
                        <button type="button" id="itaConfig" name="itaConfig" style="vertical-align: middle; height: 20px; margin-top: 6px;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envConfig\',iconLeft:\'ui-icon ui-icon-gear\'}" value="" />
                        <button type="button" id="itaLogout" name="itaLogout" style="vertical-align: middle; height: 20px; margin-top: 6px;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envLogout\',iconLeft:\'ui-icon ui-icon-power\'}" value="" />
                        -->
                    </div>
                </div>
            </div>
';

        $generator = new itaGenerator();
        $htmlNotice = $generator->getModelHTML('envCheckNotice');
    }

    $html = '
        <div id="ita-desktop" class="ita-layout">' . $htmlNotice . '
            ' . $htmltopbar . '

            <div id="desktopBody" class="ita-content">


                <div id="mainTabs" style="border-top: 0;">
            ' . $homehtml_li . '
            ' . $homehtml_div . '                </div>




            </div>
            <div id="desktopFooterWrapper" class="ui-widget-header">
                <div id="desktopFooter">
                    <button type="button" id="itaTimer" name="itaTimer" class="ita-toolBarButton ita-timer ita-button ita-element-animate {request:\'ItaCall\',model:\'proCheckIter\',iconLeft:\'ui-icon ui-icon-mail-closed\'}" value="" />
                    <button type="button" id="itaNews" name="itaNews" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'rasPad\',iconLeft:\'ui-icon ui-icon-comment\'}" value="" />
                    <div id="itaRunnerDiv" name="itaRunnerDiv" class="ita-Runner" />
                </div>
            </div>
       </div>';

    return $html;
}

?>
