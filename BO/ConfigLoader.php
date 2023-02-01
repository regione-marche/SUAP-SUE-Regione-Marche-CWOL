<?php

$configFile = 'Config';
$confClass = 'Conf';

if (isset($_POST['clientEngine'])) {
    switch ($_POST['clientEngine']) {
        case 'itaMobile':
            $configFile .= '.mobile';
            $confClass .= '.mobile';
            break;
    }
}

if (isset($_GET['test']) && $_GET['test']) {
    $configFile .= '.' . $_GET['test'];
}

require_once $configFile . '.inc.php';
require_once ITA_LIB_PATH . "/AppDefinitions/$confClass.class.php";
