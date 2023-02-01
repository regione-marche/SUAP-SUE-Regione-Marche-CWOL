<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function envDesktopMobile() {
    switch ($_POST['event']) {
        case 'callmodel':
            Out::html('body', htmlDesktopMobile());
            baseMobileMenu();
            //Out::codice("setTimeout( function() { $('#desktopBody').pagecontainer().pagecontainer( 'change', '#desktopMobile', { changeHash: false } ); }, 0 );");

            $model = 'menMobExplorer';
            itaLib::openForm($model, "", true, "desktopBody");
            /* @var $modelObj itaModel */
            $modelObj = itaModel::getInstance($model);
            $modelObj->setEvent('openform');
            $modelObj->parseEvent(); 
            break;
    }
}

function baseMobileMenu() {
    outputMobileMenu(
            array(
                array(
                    'id' => 'envMobileHomeBar_menu',
                    'class' => "{ request: 'ItaCall', event: 'openButton', model: 'menButtonMobile' }",
                    'label' => 'Applicativi'
                ), array(
                    'id' => 'envMobileHomeBar_recenti',
                    'class' => "{ request: 'ItaCall', event: 'openButton', model: 'menPersonalMobile' }",
                    'label' => 'Recenti'
                )
            )
    );
}

function outputMobileMenu($items = array()) {
    Out::html('menupanel', generateMobileMenu($items));
}

function generateMobileMenu($items = array()) {
    $html = '<div class="ui-panel-inner"><ul class="ita-list" id="menuList">';
    foreach ($items as $item) {
        $id = isset($item['id']) ? $item['id'] : '';
        $class = isset($item['class']) ? $item['class'] : '';
        $label = isset($item['label']) ? $item['label'] : '';
        $icon = isset($item['icon']) ? 'data-icon="' . $item['icon'] . '"' : '';
        if ($item['divider']) {
            $html .= "<li data-role=\"list-divider\">$label</li>";
        } else {
            $html .= "<li id=\"$id\" class=\"$class\" $icon><a href=\"#\">$label</a>";
        }
    }
    $html .= '</ul></div>';
    return $html;
}

function htmlDesktopMobile() {
    $html = '
            <div id="desktopBody" class="ita-mobile-content ita-mobile-pagecontainer">
                <div id="ita-mobile-home-toolbar" data-role="header" data-theme="a" data-position="fixed" class="ita-mobile-fixed-external-toolbar">
					<div class="ui-btn-left">
                        <button onclick="if ($(\'#menupanel\').hasClass(\'ui-panel-closed\') || $(\'#menupanel\').hasClass(\'ui-panel-open\')) $(\'#menupanel\').panel(\'toggle\');" class="ui-icon ita-button-notext ui-btn ui-icon-bars ui-btn-icon-notext ui-btn-inline ui-shadow ui-corner-all"></button>
                        <!-- <button id="envMobileHomeBar_menu"    class="ita-button ui-btn-inline {request:\'ItaCall\',event:\'openButton\',model:\'menButtonMobile\',iconLeft:\'ui-icon-bars\'}"></button> -->
                        <!-- <button id="envMobileHomeBar_recenti" class="ita-button ui-btn-inline {request:\'ItaCall\',event:\'openButton\',model:\'menPersonalMobile\',iconLeft:\'ui-icon-star\'}"></button> -->
					</div>
                    
					<!-- <h3 id="envMobileHomeBar_title">' . App::$utente->getKey('nomeUtente') . '</h3> -->
                    <h3 id="envMobileHomeBar_title" class="ui-title ui-body ui-body-a ui-corner-all" role="heading" aria-level="1" style="text-align: center;">itaMobile</h3>

                    <button id="envMobileHomeBar_logout"  class="ita-button ui-btn-inline ui-btn-right  {request:\'ItaCall\',event:\'openform\',model:\'envLogout\',iconLeft:\'ui-icon-power\'}"></button>
                </div>
                <div data-role="page" id="desktopMobile" class="ita-app" style="display: block;">
                    <div class="ui-content" role="main">
                    </div>
                </div>
            </div>
            <div data-role="panel" id="menupanel" data-position="left" data-display="overlay" data-theme="a" style="border-width: 0; position: fixed;"></div>
            <script>$(\'#menupanel\').panel().enhanceWithin();</script>';
    return $html;
}

?>
