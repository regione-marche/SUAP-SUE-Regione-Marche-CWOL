<?php

// TODO sostituire con le costanti di itaEngine
//$TMP_PATH = "C:/Works/PhpDev/dati/itaPal/tmp/uploads/";
//
//$inputFile = $_FILES['attachment']['tmp_name'];
//$outputFile = $TMP_PATH . $_FILES['attachment']['name'] . '.' . $_POST['estensione'];
//move_uploaded_file($inputFile, $outputFile);
//
//echo $outputFile;

require_once '../../ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();
$uploaddir = App::getPath('temporary.uploadPath');
$uploadfile = $uploaddir . '/' . basename($_FILES['RemoteFile']['name']);
move_uploaded_file($_FILES['RemoteFile']['tmp_name'], $uploadfile);
?>
