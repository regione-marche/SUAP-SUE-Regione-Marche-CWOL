<?php
$_POST['clientEngine'] = 'itaMobile';
require_once 'ConfigLoader.php';

if (isset($_POST['headResource'])) {
    include(ITA_LIB_PATH . '/itaPHPLoadResources/headResource.mobile.php');
    exit;
}

if (isset($_POST['headOnLoad'])) {
    include(ITA_LIB_PATH . '/itaPHPLoadResources/headOnLoad.mobile.php');
    exit;
}
?><!doctype html>
<html>
    <head>
        <?php include(ITA_LIB_PATH . '/itaPHPLoadResources/headResource.mobile.php') ?>
        <?php include(ITA_LIB_PATH . '/itaPHPLoadResources/headOnLoad.mobile.php') ?>
    </head>
    <body>
        <!--
                <div data-role="page" id="mobileDesktop">
                    <div data-role="header">
                        <h1 id="ita-title">My Title</h1>
                    </div>
                    <div data-role="content">
                        <div id="ita-content">
                            <p>Hello world</p>
                        </div>
                    </div>
                    <div data-role="footer">
                        <div id="ita-footer">
                            <h4>My Footer</h4>
                        </div>
                    </div>
                </div>
        -->
    </body>
</html>

