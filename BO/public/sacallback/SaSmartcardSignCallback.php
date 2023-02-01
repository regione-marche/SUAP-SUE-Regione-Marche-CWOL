<?php

/**
 * Callback di riorno dalla smartagent per il salvataggio del file p7m
 * array composto da key, name , size 
 */
require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();
$uploaddir = App::getPath('temporary.uploadPath');

$inputFile = $_FILES['attachment']['tmp_name'];

$outputFile = $uploaddir . '/' . $_FILES['attachment']['name'];
move_uploaded_file($inputFile, $outputFile);


$result = array();

$inputFile = $_FILES['attachment']['tmp_name'];

$outputFile = $uploaddir . '/' . $_FILES['attachment']['name'];

move_uploaded_file($inputFile, $outputFile);

$result[] = array("key" => basename($outputFile),
    "name" => $outputFile,
    "size" => filesize($outputFile));


echo base64_encode(str_replace('\\/', '/',json_encode($result)));
?>
