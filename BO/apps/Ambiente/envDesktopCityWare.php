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

function envDesktopCityWare($rootMenu = 'TI_MEN') {
    $envDesktopCityWare = new envDesktopCityWare();
    $envDesktopCityWare->parseEvent($rootMenu);
    return;
}

class envDesktopCityWare {

    protected $icon_recenti;
    protected $icon_menu;
    protected $icon_tendina;
    protected $icon_info;
    protected $icon_utente;
    protected $icon_esci;
    protected $icon_sh;
    protected $icon_temi;

    public function __construct() {
        $this->icon_recenti = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_recenti.png');
        $this->icon_menu = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_menu.png');
        $this->icon_tendina = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_tendina.png');
        $this->icon_info = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_info.png');
        $this->icon_utente = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_utente.png');
        $this->icon_esci = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_esci.png');
        $this->icon_sh = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_sh_cityware.png');
        $this->icon_temi = itaImg::base64src(ITA_BASE_PATH . '/apps/Ambiente/resources/images/icon_tema.png');
    }

    public function parseEvent($rootMenu = 'TI_MEN') {
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

                Out::html('desktop', $this->htmlDesktop($topbar, $homepage, $rootMenu));

                if ($topbar == 1) {
                    $themeSwitcherIcon = '<img style=\"margin-top: 3px; vertical-align: middle;\" src=\"' . $this->icon_temi . '\" />';
                    Out::codice('$(\'#themes\').themeswitcher( { initialText: "' . $themeSwitcherIcon . '", buttonPreText: "' . $themeSwitcherIcon . '" } );');

                    $logoHeader = App::getPath('general.fileEnte') . 'ente' . App::$utente->getKey('ditta') . '/ambiente/images/logoHead.png';
                    if (file_exists($logoHeader)) {
                        $srcBase64 = itaImg::base64src($logoHeader);
                    }

                    Out::attributo('desktopLogo', 'src', '0', $srcBase64);
                    $ParmEnte = itaLib::getParmEnte();

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
                    Out::html('ita-home-content', '<div id="ita-controlpad" class="ui-widget-content ui-corner-all" style="overflow:auto;border:0px solid blue;float:left;"></div>');
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

    protected function htmlDesktop($topbar, $homepage, $rootMenu) {
        $homehtml_li = $homehtml_div = $htmlNotice = $htmltopbar = '';

        if ($homepage == 1) {
            $homehtml_li = '<ul><li><a href="#ita-home" onclick="return false;">' . App::$utente->getKey('nomeUtente') . ' Home</a></li></ul>';

            $homehtml_div = '<div id="ita-home">
                                 <div id="ita-home-content"></div>
                             </div>';
        }

        if ($topbar == 1) {
            $htmltopbar = $this->getHeaderHTML($rootMenu);

            $generator = new itaGenerator();
            $htmlNotice = $generator->getModelHTML('envCheckNotice');
        }

        return <<<HTML
<div id="ita-desktop" class="ita-layout">
    $htmlNotice
    $htmltopbar
    <div id="desktopBody" class="ita-content">
        <div id="mainTabs" style="border-top: 0;">
            $homehtml_li
            $homehtml_div
        </div>
    </div>
</div>
HTML;
    }

    protected function getHeaderHTML($rootMenu) {
        $iconaRecenti = $this->getIconaRecenti($rootMenu);
        $iconaAppCenter = $this->getIconaAppCenter($rootMenu);
        $iconaMenu = $this->getIconaMenu($rootMenu);
        $iconaSoftwareHouse = $this->getIconaSoftwareHouse();
        $iconaTemi = $this->getIconaTemi();
        $iconaNotifiche = $this->getIconaNotifiche();
        $iconaProfilo = $this->getIconaProfilo();
        $iconaLogout = $this->getIconaLogout();

        return <<<HEADER
<div id="desktopHeader" style="box-shadow: 0px 0px 5px 0px #333; z-index: 1; line-height: 48px;">
    <div id="headerBar" class="ui-widget-header">
        $iconaRecenti
        $iconaAppCenter
        $iconaMenu

        <div style="height: 36px; border-left: 1px solid #fff; display: inline-block; vertical-align: middle; margin-left: 12px;"></div>

        <div id="info" style="margin-left: 10px; display: inline-block; float: none; margin-top: 0;">
            <img id="desktopLogo" src="" style="float: none; vertical-align: middle; margin: 0 10px 0 5px; height: 40px;">
            <div id="desktopHeaderTitle" style="vertical-align: middle;"></div>
        </div>

        <div style="float: right; text-align: right;">
            $iconaSoftwareHouse
            <div style="height: 36px; border-left: 1px solid #fff; display: inline-block; vertical-align: middle; margin-left: 5px;"></div>
            $iconaTemi
            $iconaNotifiche
            $iconaProfilo
            $iconaLogout
        </div>
        <div id="citywareRightHeader" style="float: right; margin-right: 20px; line-height: 24px; font-size: 1.3em; text-align: right;"></div>
    </div>
</div>
HEADER;
    }

    protected function getIconaRecenti($rootMenu) {
        return <<<HTML
<div id="menPersonalPopup_container" style="display: inline-block; vertical-align: middle;">
    <a href="#"
       id="menPersonalPopup"
       title="Recenti"
       style="margin: 0 0 0 2px; background: none; border: none; display: inline-block;"
       class="ita-tooltip ita-button ita-popup-menu { flyOut: true, popupAutoClose: true, rootMenu: '$rootMenu', event: 'openButton', model: 'menPersonal' }"
    >
        <img style="vertical-align: middle;" src="$this->icon_recenti" />
    </a>
</div>
HTML;
    }

    protected function getIconaAppCenter($rootMenu) {
        return <<<HTML
<a href="#"
   title="App Center"
   style="margin-left: 8px; text-decoration: none; vertical-align: middle; display: inline-block;"
   class="ita-tooltip ita-hyperlink { request: 'ItaCall', event: 'openform', model: 'menExplorer2', extraData: { rootMenu: '$rootMenu' } }"
>
    <img style="vertical-align: middle;" src="$this->icon_menu" />
</a>
HTML;
    }

    protected function getIconaMenu($rootMenu) {
        return <<<HTML
<a href="#"
   id="menButtonPopup"
   title="Menu"
   style="margin: 0 0 0 8px; background: none; border: none; display: inline-block;"
   class="ita-tooltip ita-button ita-popup-menu { flyOut: true, rootMenu: '$rootMenu', event: 'openButton', model: 'menButton' }"
><img style="vertical-align: middle;" src="$this->icon_tendina" /></a>
HTML;
    }

    protected function getIconaSoftwareHouse() {
        return '<img style="height: 40px; vertical-align: middle; margin: 0 5px 0 0;" src="' . $this->icon_sh . '" />';
    }

    protected function getIconaTemi() {
        return '<div title="Cambia Tema" class="ita-tooltip" id="themes" style="height: 48px; margin-left: 5px; position: static; display: inline-block; vertical-align: middle; line-height: normal;"></div>';
    }

    protected function getIconaNotifiche() {
        return <<<HTML
<a id="itaNotice"
   title="Avvisi"
   href="#"
   style="text-decoration: none; margin-left: 9px; vertical-align: middle; display: inline-block;"
   class="ita-tooltip ita-hyperlink { request: 'ItaCall', event: 'openform', model: 'envGestNotice' }"
>
    <img style="vertical-align: middle;" src="$this->icon_info" />
</a>
<span id="itaNotice_lbl" style="position: relative; top: -8px; left: -15px; background-color: #fff; display: inline-block; color: #333; border-radius: 20px; height: 20px; line-height: 20px; text-align: center; margin-right: -20px; padding: 0 7px;"><span style="color: red;">0</span></span>
HTML;
    }

    protected function getIconaProfilo() {
        return <<<HTML
<a id="itaConfig"
   title="Impostazioni e utilità"
   href="#"
   style="text-decoration: none; margin-left: 11px; vertical-align: middle; display: inline-block;"
   class="ita-tooltip ita-hyperlink { request: 'ItaCall', event: 'openform', model: 'envConfig' }"
>
    <img style="vertical-align: middle;" src="$this->icon_utente" />
</a>
HTML;
    }

    protected function getIconaLogout() {
        return <<<HTML
<a id="itaLogout"
   title="Termina sessione"
   href="#"
   style="text-decoration: none; margin: 0 5px 0 15px; vertical-align: middle; display: inline-block;"
   class="ita-tooltip ita-hyperlink { request: 'ItaCall', event: 'openform', model: 'envLogout' }"
>
    <img style="vertical-align: middle;" src="$this->icon_esci" />
</a>
HTML;
    }

}
