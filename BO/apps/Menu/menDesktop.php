<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// CARICO LE LIBRERIE NECESSARIE
//                        <button type="button" id="itaTimer" name="itaTimer" class="ita-toolBarButton ita-timer ita-button ita-element-animate {request:\'ItaCall\',model:\'proCheckIter\',iconLeft:\'ui-icon ui-icon-mail-closed\'}" value="" />
function menDesktop(){
    $effect='slide';
    $transTime='0';
    switch ($_POST['event']) {
        case 'callmodel':
            Out::html('desktop', htmlDesktop());
            Out::codice('$(\'<div style="position:absolute;top:2px;right:2px;"></div>\').appendTo(\'#desktopHeader\').themeswitcher();');
            $srcUrl="http://".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']."/enteimg/logoente".App::$utente->getKey('ditta').'.gif';
            Out::attributo('desktopLogo', 'src','0',$srcUrl);

            $ParmEnte=itaLib::getParmEnte();
            $data=App::$utente->getKey('DataLavoro');
            Out::html('desktopHeaderTitle', $ParmEnte['DENOMINAZIONE']);
            Out::html('desktopHeaderInfo', "                    Benvenuto ".App::$utente->getKey('nomeUtente')
                    .'<span id="desktopHeaderInfoDate">  -  '.substr($data, 6).'/'.substr($data, 4, 2).'/'.substr($data, 0,4).'</span>');
            Out::codice('setItaTimer({element:"itaTimer",delay:300000,model:"proCheckIter"});');
            Out::hide('menuapp');
            Out::hide('divBanner');
        break;
    }
}

function htmlDesktop(){
    return '
        <div id="ita-desktop" class="ita-layout">
            <div id="ita-desktop-north" class="ita-layout-north">
                <div id="desktopHeader" >
                    <div id="desktopTitlebarWrapper" class="ui-widget-header ui-corner-all">

                            <div id="desktopLogoWrapper" >
                                <img id="desktopLogo" src="">
                            </div>
                            <div id="desktopHeaderTitle"></div>
                            <div id="desktopHeaderInfo"></div>
                        </div>
                </div>
            </div>
            <div id="desktopBody" class="ita-layout-center ita-content">
                <div id="menuapp" width="70%" style="padding:2px,2px,0px,0px" class="ui-widget-content ui-corner-all">
                </div>
            </div>
            <div id="ita-desktop-south" class="ita-layout-south">
                <div id="desktopFooterWrapper" class="ui-widget-header ui-corner-all">
                    <div id="desktopFooter">
 </div>
                        <button type="button" id="itaTimer" name="itaTimer" class="ita-toolBarButton ita-timer ita-button ita-element-animate {request:\'ItaCall\',model:\'proCheckIter\',iconLeft:\'ui-icon ui-icon-mail-closed\'}" value="" />
                        <button type="button" id="itaNews" name="itaNews" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'rasPad\',iconLeft:\'ui-icon ui-icon-comment\'}" value="" />
                        <button type="button" id="itaConfig" name="itaConfig" class="ita-toolBarButton ita-button ita-element-animate {request:\'ItaCall\',event:\'openform\',model:\'menAuthConfig\',iconLeft:\'ui-icon ui-icon-gear\'}" value="" />                        
                        <div id="itaRunnerDiv" name="itaRunnerDiv" class="ita-Runner"/>
               
            </div>
        </div>
';
}
?>
