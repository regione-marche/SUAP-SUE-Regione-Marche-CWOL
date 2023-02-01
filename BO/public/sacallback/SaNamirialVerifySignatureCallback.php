<?php

require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();

$result = array(
    "resultStatus" => $_POST['resultStatus'], 
    "resultMessage" => $_POST['resultMessage']
);
file_put_contents("C:\Temp\prova.txt", print_r($_POST, true));
echo base64_encode(json_encode($result));
?>