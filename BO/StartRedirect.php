<?php

if (!isset($_REQUEST['ditta'])) {
    die('Ente non definito.');
}

$redirectToken = false;

$urlParams = '?ditta=' . $_REQUEST['ditta'];

if (isset($_REQUEST['cohesionCheck']) && isset($_REQUEST['auth'])) {
    require_once 'ConfigLoader.php';
    require_once ITA_LIB_PATH . '/itaPHPCore/App.class.php';
    require_once ITA_LIB_PATH . '/itaPHPCore/AppUtility.class.php';

    App::startSession();
    App::load(true);

    require_once ITA_BASE_PATH . '/apps/Accessi/accLibCohesion.class.php';
    $accLibCohesion = new accLibCohesion();

    $token = $accLibCohesion->login($_REQUEST['ditta']);
    if ($token === false) {
        die($accLibCohesion->getErrMessage());
    }

    $redirectToken = $token;
}

if (isset($_REQUEST['federaLogin'])) {
    require_once 'ConfigLoader.php';
    require_once ITA_LIB_PATH . '/itaPHPCore/App.class.php';
    require_once ITA_LIB_PATH . '/itaPHPCore/AppUtility.class.php';

    App::load(true);

    require_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
    require_once ITA_BASE_PATH . '/apps/Accessi/accLibFedera.class.php';
    $accLib = new accLib();
    $accLibFedera = new accLibFedera();

    $origSessionName = session_name();

    $token = $accLibFedera->login($_REQUEST['ditta']);
    if ($token === false) {
        die($accLibFedera->getErrMessage());
    }

    /*
     * Ripristino la sessione originale
     */
    App::startSession($origSessionName, true);

    $redirectToken = $token;
}

if ($redirectToken) {
    setcookie('redirectToken', $token, time() + 30, '/', '', App::isConnectionSecure(), true);
} else if (isset($_REQUEST['tmpToken'])) {
    setcookie('redirectTmpToken', $_REQUEST['tmpToken'], time() + 30, '/', '', App::isConnectionSecure(), true);
}

header('Location: ./Start.php' . $urlParams);
