<?php

/**
 * Callback di riorno dalla smartagent per il salvataggio del file p7m
 */
require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();
$uploaddir = App::getPath('temporary.uploadPath');


$result = array();

foreach ($_FILES as $fileUpl) {

    $inputFile = $fileUpl['tmp_name'];
    $outputFile = $uploaddir . '/' . $fileUpl['name'];
    move_uploaded_file($inputFile, $outputFile);
    $result[] = array("key" => $fileUpl['name'],
                      "name"=> $outputFile,
                      "size" => $fileUpl['size']);
}

echo base64_encode(str_replace('\\/', '/',json_encode($result)));
?>
