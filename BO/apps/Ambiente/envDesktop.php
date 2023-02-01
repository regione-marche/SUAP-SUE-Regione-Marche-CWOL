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

function envDesktop($rootMenu = 'TI_MEN') {
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


            $generator = new itaGenerator();
            $retHtml = $generator->getModelHTML('menPersonal');
            if ($topbar == 1) {

                Out::codice('$(\'<div id="themes"/>\').appendTo(\'#desktopHeader\').themeswitcher();');
                Out::codice("loadSlider();"); //

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
                Out::codice("$('#menPersonal').find('br:first').remove();");
                Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'open',model:'envControlPad'});"); //
                Out::codice("itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'refresh',model:'menPersonal'});");
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
            <div id="desktopHeader">
                <div id="headerBar" class="ui-widget-header">
                    <div id="info">
                        <img id="desktopLogo" src="">
                        <div id="desktopHeaderTitle"></div>
                    </div>
                        <button type="button" id="itaMenExplorer_' . $rootMenu . '" name="itaMenExplorer" style="width:60px;height:20px;margin-top:6px;margin-left:10px;margin-right:0px;" class="ita-toolBarButton ita-button ita-element-animate ita-button-left  {request:\'ItaCall\',event:\'openform\',model:\'menExplorer2\',extraData: { rootMenu:\'' . $rootMenu . '\' }}" value="Menù"/>
                        <button type="button" id="menButton_' . $rootMenu . '" name="menButton" style="height:20px;width:24px;margin-top:6px;" class="ita-toolBarButton ita-button ita-element-animate ita-button-right ita-popup-menu {flyOut:true,rootMenu:\'' . $rootMenu . '\', event:\'openButton\',model:\'menButton\'  ,iconLeft:\'ui-icon ui-icon-circle-triangle-s\'}" value="" />
                    <div id="fontsize_wrapper">
                        <span id="fontsize"></span>
                        <div id="fontsize_slider"></div>
                    </div>
                    <button type="button" id="itaLogout" name="itaLogout" style="height:20px;margin-top:6px;float:right;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envLogout\',iconLeft:\'ui-icon ui-icon-power\'}" value="" />
                    <button type="button" id="itaConfig" name="itaConfig" style="height:20px;margin-top:6px;float:right;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envConfig\',iconLeft:\'ui-icon ui-icon-gear\'}" value="" />
                    <button type="button" id="itaNotice" name="itaNotice" style="width:50px;height:20px;margin-top:6px;float:right;" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'envGestNotice\',iconLeft:\'ui-icon ui-icon-notice\'}" value="" />
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


                <div id="mainTabs">
            ' . $homehtml_li . '
            ' . $homehtml_div . '                </div>




            </div>';
//            <div id="desktopFooterWrapper" class="ui-widget-header">
//                <div id="desktopFooter">
//                    <button type="button" id="itaTimer" name="itaTimer" class="ita-toolBarButton ita-timer ita-button ita-element-animate {request:\'ItaCall\',model:\'proCheckIter\',iconLeft:\'ui-icon ui-icon-mail-closed\'}" value="" />
//                    <button type="button" id="itaNews" name="itaNews" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'rasPad\',iconLeft:\'ui-icon ui-icon-comment\'}" value="" />
//                    <div id="itaRunnerDiv" name="itaRunnerDiv" class="ita-Runner" />
//                </div>
//            </div>
//       </div>';

    return $html;
}

?>
