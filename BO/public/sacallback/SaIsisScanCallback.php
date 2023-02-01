<?php
require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');

App::load();
$uploaddir = App::getPath('temporary.uploadPath');

$inputFile = $_FILES['attachment']['tmp_name'];
$outputFile = $uploaddir . '/' . $_FILES['attachment']['name'];
move_uploaded_file($inputFile, $outputFile);

echo str_replace('\\/', '/',$outputFile);
?>