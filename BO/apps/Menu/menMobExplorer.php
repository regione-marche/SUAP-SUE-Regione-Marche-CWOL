<?php

/**
 *  Explorer per il menu mobile
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once './apps/Ambiente/envLib.class.php';
include_once './apps/Menu/menLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';

function menMobExplorer() {
    $menMobExplorer = new menMobExplorer();
    $menMobExplorer->parseEvent();
    return;
}

class menMobExplorer extends itaModel {

    public $envLib;
    public $menLib;
    public $nameForm = "menMobExplorer";
    public $menuGrid = "menMobExplorer_divMenuGrid";
    public $ITALSOFT_DB;
    public $rootMenu = 'MOB_MEN';
    public $menuPath = array();
    private $keyPreferenze = 'MenuGridPreferenze';

    function __construct() {
        parent::__construct();
        $this->envLib = new envLib();
        $this->menLib = new menLib();
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
            App::$utente->setKey($this->nameForm . '_menuPath', $this->menuPath);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
//                Out::hideLayoutPanel($this->nameForm . '_buttonBar');
//                $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);
//
//                $max_cols = 0;
//
//                if ($config) {
//                    foreach ($config as $menu) {
//                        foreach ($menu as $coords) {
//                            if ($coords['col'] && intval($coords['col']) > $max_cols) {
//                                $max_cols = intval($coords['col']);
//                            }
//                        }
//                    }
//                }
//
//                Out::menuGridInit($this->menuGrid, array('min_cols' => $max_cols));

                $this->menuPath = array();
                $this->lastMenu($this->rootMenu);
                $this->openMenu();
                break;

            case 'onMenuGridChange':
//                $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);
//                if (!$config) {
//                    $config = array();
//                }
//
//                $last_menu = $this->lastMenu();
//                if (!isset($config[$last_menu])) {
//                    $config[$last_menu] = array();
//                }
//                $serialized_params = json_decode($_POST['grid'], true);
//                foreach ($serialized_params as $param) {
//                    $config[$last_menu][$param['id']] = array(
//                        'col' => $param['col'],
//                        'row' => $param['row'],
//                        'size_x' => $param['size_x'],
//                        'size_y' => $param['size_y']
//                    );
//                }
//
//                $this->envLib->setEnvUtemeta($this->keyPreferenze, $config);
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
//                        Out::menuGridDestroy($this->menuGrid);
//
//                        $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);
//
//                        function conf_map($v) {
//                            return array('size_x' => $v['size_x'], 'size_y' => $v['size_y']);
//                        }
//
//                        $config[$this->lastMenu()] = array_map('conf_map', $config[$this->lastMenu()]);
//                        $this->envLib->setEnvUtemeta($this->keyPreferenze, $config);
//
//                        $this->openMenu();
                        break;

                    case $this->nameForm . '_Fav':
//                        list($menu, $prog) = explode('&', $_POST['cell']);
//                        $param = array(
//                            'MENU' => $menu,
//                            'PROG' => $prog
//                        );
//                        $this->menLib->setBookmark($param);
//                        Out::msgInfo("Info", "Programma aggiunto ai preferiti");
                        break;

                    case 'before-close-portlet':
                        $this->lastMenu('BACK');
                        $this->openMenu();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_menuPath');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    /**
     * Getter/Setter per ultimo percoso menu
     * @param type $menu
     */
    private function lastMenu($menu = false) {
        if ($menu) {
            if ($menu == 'BACK') {
                if (count($this->menuPath) > 1) {
                    array_pop($this->menuPath);
                }
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

//        $pathtext = array();
//        foreach ($this->menuPath as $menu) {
//            $tmp = $this->menLib->GetIta_menu_ini($menu);
//            if ($menu !== $last_menu) {
//                array_push($pathtext, '<a href="#" onclick="itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'menMobExplorer\', id: \'' . $this->menuGrid . '\', cell: \'' . $tmp['me_menu'] . '\' });">' . $tmp['me_descrizione'] . '</a>');
//            } else {
//                array_push($pathtext, $tmp['me_descrizione']);
//            }
//        }
//        Out::html($this->nameForm . '_textPath', implode(' &raquo; ', $pathtext));

        $config = $this->envLib->getEnvUtemeta($this->keyPreferenze);

//        Out::codice("menuGrids['{$this->menuGrid}'].remove_all_widgets();");

        $html = '';

        foreach ($menu_tab as $menu_rec) {
            $is_menu = $menu_rec['pm_voce'] !== 'BACK' ? $this->menLib->GetIta_menu_ini($menu_rec['pm_voce']) : true;
            $id = (!$is_menu ? $last_menu . '&' : '') . $menu_rec['pm_voce'];

            $params = '2, 1';

            if ($config && isset($config[$last_menu]) && isset($config[$last_menu][$id])) {
                $params = "{$config[$last_menu][$id]['size_x']}, {$config[$last_menu][$id]['size_y']}";
                if ($config[$last_menu][$id]['col'] && $config[$last_menu][$id]['row']) {
                    $params .= ", {$config[$last_menu][$id]['col']}, {$config[$last_menu][$id]['row']}";
                }
            }

            $html .= $this->generateTile($menu_rec, $id, $is_menu);
        }

        Out::html($this->menuGrid, $html);
    }

    public function generateTile($menu_rec, $id = '', $is_menu = true) {
        $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/BASE_' . ($is_menu ? 'MENU' : 'APP3') . '.png');

        if ($menu_rec['pm_icon']) {
            if (file_exists(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_icon'] . '.png')) {
                $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_icon'] . '.png');
            }
        } else {
            if (file_exists(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_voce'] . '.png')) {
                $icon = itaImg::base64src(ITA_BASE_PATH . '/apps/Menu/resources/' . $menu_rec['pm_voce'] . '.png');
            }
        }

//        $this->tileColorCounter = isset($this->tileColorCounter) ? ($this->tileColorCounter + 1) : 0;
//        $class = 'ita-menu-grid-color-' . (($this->tileColorCounter % 5) + 1);

        $back = $this->getColor($menu_rec);
        $text = $this->getColor($menu_rec, true);

        $voce = addslashes($menu_rec['pm_descrizione']);

        $desc = '<div style="font-size: .9em; position: absolute; bottom: 8px; right: 12px; text-align: right; width: 80%; overflow: hidden; text-overflow: ellipsis;">' . $voce . '</div>';

        return "<a id=\"$id\" "
//                . "class='$class' "
                . 'onclick="itaGo(\'ItaCall\', this, { event: \'onClick\', model: \'menMobExplorer\', id: \'' . $this->menuGrid . '\', cell: \'' . $id . '\' });" '
                . "style=\""
                . "width: 28%; height: 100px; display: inline-block; position: relative; margin: 10px 8px 0; min-width: 100px; max-width: 200px;"
                . "color: rgb($text);"
                . "background-color: rgb($back);"
                . "background-image: url('$icon');"
                . "background-position: 12px 12px;"
                . "background-repeat: no-repeat;"
                . "background-size: 36px;\" title=\"$voce\">$desc</a>";
    }

    private function getColor($menu_rec, $of_text = false, $custom_path = false) {
        $auto_darken = true;

        $field = $of_text ? 'pm_color' : 'pm_background';
        $path = $custom_path ? $custom_path : $this->menuPath;

        if ($menu_rec['pm_voce'] == 'BACK' && count($path) > 2) {
            array_pop($path);
            $c = count($path) - 1;
            return $this->getColor($this->menLib->GetIta_menu_ini($path[$c]), $of_text, $path);
        }

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