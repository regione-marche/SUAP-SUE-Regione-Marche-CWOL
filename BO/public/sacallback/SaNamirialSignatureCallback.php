<?php

require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();
$uploaddir = App::getPath('temporary.uploadPath');

$inputFile = $_FILES['attachment']['tmp_name'];
$outputFile = $uploaddir . '/' . $_FILES['attachment']['name'];
move_uploaded_file($inputFile, $outputFile);
$result = array("resultStatus" => $_POST['resultStatus'],
    "resultMessage" => $_POST['resultMessage'],
    "resultFileName" => $outputFile
);
//se false non rirono il documento originale
if ($_POST['resultStatus'] == false) {
    $_POST['resultFileName'] = NULL;
}
echo base64_encode(str_replace('\\/', '/', json_encode($result)));
?>