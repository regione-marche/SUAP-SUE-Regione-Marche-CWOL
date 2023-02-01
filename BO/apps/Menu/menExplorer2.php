<?php

/**
 *  Explorer per il menu
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Ambiente/envLib.class.php';
include_once './apps/Menu/menLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';

function menExplorer2() {
    $menExplorer2 = new menExplorer2();
    $menExplorer2->parseEvent();
    return;
}

class menExplorer2 extends itaModel {

    public $envLib;
    public $menLib;
    public $nameForm = "menExplorer2";
    public $menuGrid = "menExplorer2_divMenuGrid";
    public $ITALSOFT_DB;
    public $rootMenu = 'TI_MEN';
    public $menuConf;
    public $menuPath = array();
    private $keyPreferenze = 'MenuGridPreferenze';

    function __construct() {
        parent::__construct();
        $this->envLib = new envLib();
        $this->menLib = new menLib();
        $this->rootMenu = App::$utente->getKey($this->nameForm . '_rootMenu');
        $this->menuConf = App::$utente->getKey($this->nameForm . '_menuConf');
        $this->menuPath = App::$utente->getKey($this->nameForm . '_menuPath');
        try {
            $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rootMenu', $this->rootMenu);
            App::$utente->setKey($this->nameForm . '_menuConf', $this->menuConf);
            App::$utente->setKey($this->nameForm . '_menuPath', $this->menuPath);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (isset($_POST['rootMenu']) && $_POST['rootMenu']) {
                    $this->rootMenu = $_POST['rootMenu'];
                }

                Out::html($this->nameForm . '_Reset', 'reset');

                Out::hideLayoutPanel($this->nameForm . '_buttonBar');

                $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);

                $max_cols = 0;

                if ($config) {
                    foreach ($config as $menu) {
                        if (!is_array($menu)) {
                            continue;
                        }

                        foreach ($menu as $coords) {
                            if ($coords['col'] && intval($coords['col']) > $max_cols) {
                                $max_cols = intval($coords['col']);
                            }
                        }
                    }
                }

                $this->initMenu(array(
//                    'min_cols' => $max_cols
                ));

                $this->menuPath = array();
                $this->lastMenu($this->rootMenu);
                $this->openMenu();

                Out::css($this->nameForm, 'width', '');
                Out::css($this->nameForm . '_workSpace', 'width', '');
                break;

            case 'onMenuGridChange':
                $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);
                if (!$config) {
                    $config = array();
                }

                $last_menu = $this->lastMenu();
                if (!isset($config[$last_menu])) {
                    $config[$last_menu] = array();
                }
                $serialized_params = json_decode($_POST['grid'], true);
                foreach ($serialized_params as $param) {
                    $config[$last_menu][$param['id']] = array(
                        'col' => $param['col'],
                        'row' => $param['row'],
                        'size_x' => $param['size_x'],
                        'size_y' => $param['size_y']
                    );
                }

                $this->envLib->setEnvUtemeta($this->keyPreferenze, $config);
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->menuGrid:
                        $id = $_POST['cell'];

                        if (!$id) {
                            break;
                        }

                        if (strpos($id, '&') === false) {
                            $this->lastMenu($id);
                            $this->openMenu();
                        } else {
                            list($menu, $prog) = explode('&', $id);
                            $this->menLib->lanciaProgramma_ini($menu, $prog);
                        }

                        break;

                    case $this->nameForm . '_Reset':
                        Out::menuGridDestroy($this->menuGrid);

                        $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);

                        function conf_map($v) {
                            return array('size_x' => $v['size_x'], 'size_y' => $v['size_y']);
                        }

                        $config[$this->lastMenu()] = array_map('conf_map', $config[$this->lastMenu()]);
                        $this->envLib->setEnvUtemeta($this->keyPreferenze, $config);

                        $this->initMenu();

                        $this->openMenu();
                        break;

                    case $this->nameForm . '_Fav':
                        list($menu, $prog) = explode('&', $_POST['cell']);
                        $param = array(
                            'MENU' => $menu,
                            'PROG' => $prog
                        );
                        $this->menLib->setBookmark($param);
                        Out::msgInfo("Info", "Programma aggiunto ai preferiti");
                        break;

                    case 'close-portlet':
                        Out::menuGridDestroy($this->menuGrid);
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_rootMenu');
        App::$utente->removeKey($this->nameForm . '_menuConf');
        App::$utente->removeKey($this->nameForm . '_menuPath');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function formatColor($color) {
        switch (substr_count($color, ',')) {
            case 2:
                if (strpos($color, 'rgb') === false) {
                    return "rgb($color)";
                }

            case 3:
                if (strpos($color, 'rgba') === false) {
                    return "rgba($color)";
                }
        }

        return $color;
    }

    public function initMenu($opts = array()) {
//        Out::menuGridInit($this->menuGrid, array('min_cols' => $max_cols));
//        $opts['widget_base_dimensions'] = array(60, 60);

        if ($this->menuConf) {
            $root_menu = $this->menuConf;
        } else {
            $root_menu = $this->menuConf = $this->menLib->GetIta_menu_ini($this->rootMenu);
        }

        if (isset($root_menu['me_margins'])) {
            $margins = array_map('trim', explode(',', $root_menu['me_margins']));

            $opts['widget_margins'] = $margins;
        }

        if (isset($root_menu['me_layout']) && $root_menu['me_layout'] == 'fixed') {
            $opts['resize'] = array(
                'enabled' => false,
                'max_size' => array(1, 1)
            );

            if (!isset($root_menu['me_icon_panel_size']) || empty($root_menu['me_icon_panel_size'])) {
                $root_menu['me_icon_panel_size'] = '120, 120';
            }

            if (!isset($root_menu['me_text_panel_size']) || empty($root_menu['me_text_panel_size'])) {
                $root_menu['me_text_panel_size'] = '120, 40';
            }

            $dimensions = array_map('trim', explode(',', $root_menu['me_icon_panel_size']));
            $dimensions_text = array_map('trim', explode(',', $root_menu['me_text_panel_size']));

            $dimensions[0] = intval($dimensions[0]);
            $dimensions[1] += $dimensions_text[1];

            $opts['widget_base_dimensions'] = $dimensions;
        }

        $this->menuConf = $root_menu;

        Out::menuGridInit($this->menuGrid, $opts);
    }

    /**
     * Getter/Setter per ultimo percoso menu
     * @param type $menu
     */
    private function lastMenu($menu = false) {
        if ($menu) {
            if ($menu == 'BACK') {
                array_pop($this->menuPath);
            } else {
                if (in_array($menu, $this->menuPath)) {
                    $this->menuPath = array_slice($this->menuPath, 0, array_search($menu, $this->menuPath) + 1);
                } else {
                    $this->menuPath[] = $menu;
                }
            }
        }

        return end($this->menuPath);
    }

    private function openMenu() {
        $last_menu = $this->lastMenu();
        $menu_tab = $this->menLib->menuFiltrato_ini($last_menu);

        if (count($this->menuPath) > 1) {
            $tmp = $this->menLib->GetIta_menu_ini($this->menuPath[count($this->menuPath) - 2]);
            array_unshift($menu_tab, array(
                'pm_voce' => 'BACK',
                'pm_categoria' => 'ME',
                'pm_descrizione' => $tmp['me_descrizione']
            ));
        }

        $pathtext = array();

        foreach ($this->menuPath as $menu) {
            $tmp = $this->menLib->GetIta_menu_ini($menu);
            if ($menu !== $last_menu) {
                array_push($pathtext, '<a href="#" style="color: inherit;" onclick="itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'menExplorer2\', id: \'' . $this->menuGrid . '\', cell: \'' . $tmp['me_menu'] . '\' });">' . $tmp['me_descrizione'] . '</a>');
            } else {
                array_push($pathtext, $tmp['me_descrizione']);
            }
        }

        Out::html($this->nameForm . '_textPath', implode(' &raquo; ', $pathtext));

        $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);

        Out::codice("menuGrids['{$this->menuGrid}'].remove_all_widgets();");

        $parent_men = $this->menLib->GetIta_menu_ini($this->menuPath[0]);
        foreach ($this->menuPath as $k => $parent) {
            $child_men = array_filter($this->menLib->GetIta_menu_ini($parent));
            $parent_men = array_merge($parent_men, $child_men);
//            $parent_men = array_merge($parent_men, $this->menLib->GetIta_menu_ini($parent));
        }

        $root_menu = $parent_men;

        if (!$root_menu['me_refresh_icon']) {
            $root_menu['me_refresh_icon'] = 'REFRESH.png';
        }

        $img = '<img height="16" src="' . itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . ($root_menu['me_refresh_icon'])) . '">';
        Out::html($this->nameForm . '_Reset', $img);

        if (isset($root_menu['pm_color']) && !empty($root_menu['pm_color'])) {
            Out::css($this->nameForm . '_textPath', 'color', $this->formatColor($root_menu['pm_color']));
            Out::css($this->nameForm . '_Reset', 'color', $root_menu['pm_color']);
        }

        if (isset($root_menu['me_background']) && !empty($root_menu['me_background'])) {
            Out::css($this->nameForm . '_workSpace', 'background-color', $this->formatColor($root_menu['me_background']));
        }

        if (isset($root_menu['me_body_background']) && !empty($root_menu['me_body_background'])) {
            Out::css($this->nameForm . '_workSpace', 'background-image', "url('" . itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . ($root_menu['me_body_background'])) . "')");
            Out::css($this->nameForm . '_workSpace', 'background-size', "cover");
        }

        foreach ($menu_tab as $menu_rec) {
            $is_menu = $menu_rec['pm_voce'] !== 'BACK' ? $this->menLib->GetIta_menu_ini($menu_rec['pm_voce']) : true;
            $id = (!$is_menu ? $last_menu . '&' : '') . $menu_rec['pm_voce'];

            if (!$is_menu && $menu_rec['pm_categoria'] == 'ME') {
                $is_menu = true;
            }

            $params = '2, 1';

            if (isset($this->menuConf['me_layout']) && $this->menuConf['me_layout'] == 'fixed') {
                $params = '1, 1';
            }

            if ($config && isset($config[$last_menu]) && isset($config[$last_menu][$id])) {
                if (!isset($this->menuConf['me_layout']) || $this->menuConf['me_layout'] != 'fixed') {
                    $params = "{$config[$last_menu][$id]['size_x']}, {$config[$last_menu][$id]['size_y']}";
                }

                if ($config[$last_menu][$id]['col'] && $config[$last_menu][$id]['row']) {
                    $params .= ", {$config[$last_menu][$id]['col']}, {$config[$last_menu][$id]['row']}";
                }
            }

            if (!trim($menu_rec['pm_color']) && isset($parent_men['pm_color'])) {
                $menu_rec['pm_color'] = $parent_men['pm_color'];
            }

            if (!trim($menu_rec['pm_background']) && isset($parent_men['pm_background'])) {
                $menu_rec['pm_background'] = $parent_men['pm_background'];
            }

            if (!trim($menu_rec['pm_icon'])) {
                if ($id === 'BACK') {
                    if (isset($parent_men['pm_icon_back'])) {
                        $menu_rec['pm_icon'] = $parent_men['pm_icon_back'];
                    }
                } else if ($is_menu) {
                    if (isset($parent_men['pm_icon'])) {
                        $menu_rec['pm_icon'] = $parent_men['pm_icon'];
                    }
                } else {
                    if (isset($parent_men['pm_icon_app'])) {
                        $menu_rec['pm_icon'] = $parent_men['pm_icon_app'];
                    }
                }
            }

            Out::codice("menuGrids['{$this->menuGrid}'].add_widget('{$this->generateTile($menu_rec, $id, $is_menu)}', $params);");
        }
    }

    public function generateTile($menu_rec, $id = '', $is_menu = true) {
        $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/BASE_' . ($is_menu ? 'MENU' : 'APP3') . '.png');

        if (trim($menu_rec['pm_icon'])) {
            if (strpos($menu_rec['pm_icon'], ';') !== false) {
                list($menu_rec['pm_icon'], $tmp_explode_icon) = explode(';', $menu_rec['pm_icon'], 2);

                if (file_exists(ITA_BASE_PATH . '/apps/Menu/resources/' . $tmp_explode_icon . '.png')) {
                    $second_icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . $tmp_explode_icon . '.png');
                }
            }

            if (file_exists(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_icon'] . '.png')) {
                $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_icon'] . '.png');
            }
        } else {
            if (file_exists(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_voce'] . '.png')) {
                $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_voce'] . '.png');
            }
        }

        //$icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/SG_MEN.png');

        $back = $this->formatColor($this->getColor($menu_rec));
        $text = $this->formatColor($this->getColor($menu_rec, true));

//        $dback = $this->darkenColor($back);
//        $grad = "background-image: -ms-linear-gradient(bottom center, #$dback 0%, #$back 100%); background-image: -moz-linear-gradient(bottom center, #$dback 0%, #$back 100%); background-image: -o-linear-gradient(bottom center, #$dback 0%, #$back 100%); background-image: -webkit-gradient(linear, center bottom, center top, color-stop(0, #$dback), color-stop(100, #$back)); background-image: -webkit-linear-gradient(bottom center, #$dback 0%, #$back 100%); background-image: linear-gradient(to top center, #$dback 0%, #$back 100%);";

        $ui_icon = $menu_rec['pm_voce'] == 'BACK' ? 'ui-icon-arrowreturnthick-1-w' : ( $is_menu ? 'ui-icon-folder-collapsed' : 'ui-icon-newwin' );
        $voce = addslashes($menu_rec['pm_descrizione']);

        $css_jquery_icon = 'background-image: url(lib/jqueryui-1.10.3/images/ui-icons_ffffff_256x240.png); color: #fff';

//        if (!$is_menu && $this->isBookmarked($this->lastMenu(), $menu_rec['pm_voce'])) {
//            //
//        }
//        Out::msgInfo("","tile $back + $text");

        if (!isset($this->menuConf['me_layout']) || $this->menuConf['me_layout'] != 'fixed') {
            $desc = '<span style="' . $css_jquery_icon . '; float: right;" class="ui-icon ' . $ui_icon . '"></span><div style="position: absolute; bottom: 8px; right: 12px; text-align: right; width: 80%; overflow: hidden; text-overflow: ellipsis;">' . $voce . '</div>';

            $onhoverfav = '';
            if (!$is_menu) {
                $favicon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/FAV_ICON.png');
                $onhoverfav = "<span class=\"onhover\" style=\"width: 20px; height: 20px; position: absolute; top: 5px; left: 5px; background-image: url($favicon); background-position: center; background-repeat: no-repeat;\" title=\"Aggiungi ai preferiti\" onclick=\"itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'{$this->nameForm}\', id: \'{$this->nameForm}_Fav\', cell: \'$id\' });\"></span>";
                $onhoverfav = '<span class="onhover ui-icon ui-icon-star" style="' . $css_jquery_icon . '; position: absolute;" title="Aggiungi ai preferiti" onclick="itaGo(\\\'ItaCall\\\', this, { event: \\\'onClick\\\', model: \\\'' . $this->nameForm . '\\\', id: \\\'' . $this->nameForm . '_Fav\\\', cell: \\\'' . $id . '\\\' });"></span>';
            }

            $block = "<div style=\"padding: 10px;\">$onhoverfav $desc</div></div>";
            $backgroundStyle = 'background-position: 12px 12px; background-repeat: no-repeat; background-size: 64px;';

            /*
             * Blocco di intermezzo per seconda icona
             */
            if (isset($second_icon)) {
                $block = '<div style="background-image: url(\\\'' . $second_icon . '\\\'); height: 100%; ' . $backgroundStyle . '">' . $block . '</div>';
            }

            $html = "<div id=\"$id\" style=\"color: $text; background-color: $back; background-image: url(\'$icon\'); $backgroundStyle\" title=\"$voce\">$block</div>";
            return $html;
        } else {
            $type_icon = '<span style="' . $css_jquery_icon . '; position: absolute; right: 10px; top: 10px;" class="ui-icon ' . $ui_icon . '"></span>';

            list($icon_x, $icon_y) = array_map('trim', explode(',', $this->menuConf['me_icon_panel_size']));
            list($text_x, $text_y) = array_map('trim', explode(',', $this->menuConf['me_text_panel_size']));

            $second_icon_html = '';

            if (isset($second_icon)) {
                $second_icon_html = "<div style=\"width: {$icon_x}px; height: {$icon_y}px; margin-top: -{$icon_y}px; background-image: url(\'$second_icon\'); background-position: center; background-repeat: no-repeat;\"></div>";
            }

            $html = "<div id=\"$id\" title=\"$voce\" style=\"color: $text;\"><div style=\"width: {$icon_x}px; height: {$icon_y}px; background-color: $back; background-image: url(\'$icon\'); background-position: center; background-repeat: no-repeat;\">$type_icon</div>$second_icon_html<div style=\"width: {$text_x}px; text-align: center; padding-top: 10px; text-shadow: 1px 1px gray;\">$voce</div></div>";
            return $html;
//            $desc = '<span style="' . $css_jquery_icon . '; float: right;" class="ui-icon ' . $ui_icon . '"></span><div style="line-height: 16px; position: absolute; top: 75%; right: 0; text-align: center; width: 100%; overflow: hidden; text-overflow: ellipsis;">' . $voce . '</div>';
//            return "<div id=\"$id\" style=\"color: $text; background-color: $back; background-image: url(\'$icon\');  background-position: top center; background-repeat: no-repeat; background-size: 100% 72%;\" title=\"$voce\"><div>$onhoverfav $desc</div></div>";
        }
    }

    private function getColor($menu_rec, $of_text = false, $custom_path = false) {
        $auto_darken = true;

        $field = $of_text ? 'pm_color' : 'pm_background';
        $path = $custom_path ? $custom_path : $this->menuPath;

//        if ($menu_rec['pm_voce'] == 'BACK' && count($path) > 2) {
//            array_pop($path);
//            $c = count($path) - 1;
//            return $this->getColor($this->menLib->GetIta_menu_ini($path[$c]), $of_text, $path);
//        }

        $color = $menu_rec[$field];

        if (!$color) {
            for ($i = count($path) - 1; $i > 0; $i--) {
                $parent_menu_tab = $this->menLib->GetIta_puntimenu_ini($path[$i - 1]);
                foreach ($parent_menu_tab as $parent_menu_rec) {
                    if ($parent_menu_rec['pm_voce'] === $path[$i] && $parent_menu_rec[$field]) {
                        return $parent_menu_rec[$field];
                    }
                }
            }

            if (!$of_text) {
                $root = count($path) > 1 ? $path[1] : $menu_rec['pm_voce'];
                $color = $this->hex2rgb(substr(md5($root), 0, 6));

                if ($auto_darken) {
                    while ($this->isLight($color)) {
                        $color = $this->darkenColor($color, 2);
                    }
                }
            } else {
                $color = !$auto_darken ? ( $this->isLight($this->getColor($menu_rec)) ? '0,0,0' : '255,255,255' ) : '255,255,255';
            }
        }

        return $color;
    }

    private function isBookmarked($menu, $prog) {
        $utecod = App::$utente->getKey('idUtente');
        $menfav_rec = ItaDB::DBSQLSelect($this->menLib->getItalweb(), "SELECT * FROM MEN_PREFERITI WHERE PR_MENU = '$menu' AND PR_PROG = '$prog' AND PR_UTECOD = '$utecod'");
        return $menfav_rec ? true : false;
    }

    private function hex2rgb($hex) {
        return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
    }

    private function darkenColor($rgb, $d = 15) {
        list($r, $g, $b) = explode(',', $rgb);
        return round($r * (100 - $d) / 100) . ',' . round($g * (100 - $d) / 100) . ',' . round($b * (100 - $d) / 100);
    }

    private function isLight($rgb) {
        list($r, $g, $b) = explode(',', $rgb);
        $contrast = sqrt(
                $r * $r * .241 +
                $g * $g * .691 +
                $b * $b * .068
        );
        return $contrast > 145 ? true : false;
    }

}

?>