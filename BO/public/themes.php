<?php

require_once '../ConfigLoader.php';

if ($_GET['m'] == 'get') {
    echo '<div class="jquery-ui-themeswitcher"><div><ul>';

    $path = 'public/libs/jqueryui-' . Conf::JQUERY_UI_VERSION . '/themes';
    $themes = getThemes(ITA_BASE_PATH . DIRECTORY_SEPARATOR . $path);

    foreach ($themes as $theme) {
        if (in_array($theme, array('cityportal-classic'))) {
            continue;
        }

        echo '<li>
            <a href="' . $path . '/' . $theme . '/jquery-ui-' . Conf::JQUERY_UI_VERSION . '.custom.css">
            <img src="' . $path . '/' . $theme . '/theme_90.png" alt="' . $theme . '" />
            <span class="themeName">' . $theme . '</span></a>
        </li>';
    }
    echo '</ul></div></div>';
}

function getThemes($path) {
    $lista = scandir($path);
    $themes = array();
    foreach ($lista as $v) {
        if (is_dir($path . '/' . $v) && file_exists($path . '/' . $v . '/jquery-ui-' . Conf::JQUERY_UI_VERSION . '.custom.css')) {
            $themes[] = $v;
        }
    }
    return $themes;
}

?>
