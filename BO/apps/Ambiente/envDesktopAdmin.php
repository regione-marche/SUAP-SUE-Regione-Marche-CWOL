<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE
//                        <button type="button" id="itaTimer" name="itaTimer" class="ita-toolBarButton ita-timer ita-button ita-element-animate {request:\'ItaCall\',model:\'proCheckIter\',iconLeft:\'ui-icon ui-icon-mail-closed\'}" value="" />
function envDesktopAdmin() {
    $effect = 'slide';
    $transTime = '0';
    switch ($_POST['event']) {
        case 'callmodel':

            Out::html('desktop', htmlDesktopAdmin());
            Out::attributo('desktopLogo', 'src', '0', $srcUrl);
            $generator = new itaGenerator();
            $adminHtml = $generator->getModelHTML('envAdmin');


            $data = App::$utente->getKey('DataLavoro');
            Out::html('desktopHeaderTitle', "Amministrazione del sistema");
            Out::html('ita-home-content', $adminHtml);


            $model = 'envAdmin';
            $_POST['event'] = 'openform';
            $phpURL = App::getConf('modelBackEnd.php');
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once $phpURL . '/' . $appRoute . '/' . $model . '.php';
            /// NEW
            $modelObj = new $model();
            $modelObj->setModelData($_POST);
            $modelObj->parseEvent();

            Out::hide('divBanner');
            Out::codice('loadTabs();');
            Out::codice('resizeTabs();');



            break;
    }
}

function htmlDesktopAdmin() {
    $htmlhomepage = '';

    $homehtml_li = '<li><a href="#ita-home" onclick="return false;">admin Home</a></li>';
    $homehtml_div = '
        <div id="ita-home">
            <div id="ita-home-content">
            </div>
        </div>';

    $htmltopbar = '
    <div id="desktopHeader">
        <div id="headerBar" class="ui-widget-header">
            <div id="info">
                <img id="desktopLogo" src="">
                <div id="desktopHeaderTitle"></div>
            </div>
            <div id="fontsize_wrapper">
                <span id="fontsize"></span>
                <div id="fontsize_slider"></div>
            </div>
        </div>
    </div>';

    $html = '
    <div id="ita-desktop" class="ita-layout">
        ' . $htmltopbar . '

        <div id="desktopBody" class="ita-content">
            <div id="mainTabs">
                <ul>
                    ' . $homehtml_li . '
                </ul>
                    ' . $homehtml_div . '
            </div>
        </div>
   </div>';
    return $html;
}

?>
