<?php
require 'ConfigLoader.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" style="height: 100%;">
    <head>
        <?php include(ITA_LIB_PATH . '/itaPHPLoadResources/headResource.php') ?>
        <?php include(ITA_LIB_PATH . '/itaPHPLoadResources/headOnLoad.php') ?>
    </head>
    <body style="height: 100%;">
        <?php
        $itaiframe = '';
        if (isset($_GET['itaiframe'])) {
            $itaiframe = $_GET['itaiframe'];
        }
        if (ITA_IFRAME != 'none' && $itaiframe != 'none') {
            echo('<iframe id="appletIFrame" src="itaRunner.php" style="visibility:hidden;"></iframe>');
        }
        ?>
        <div id="desktop">
        </div>
    </body>
</html>