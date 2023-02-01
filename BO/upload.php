<?php
require_once './ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
/*
 * Carico itaEngine
 */
App::load();

$uploaddir = App::getPath('temporary.uploadPath');
$uploadfile = $uploaddir.'/'.$_POST['token']."-".basename($_FILES['ita_upload']['name']);
if (move_uploaded_file($_FILES['ita_upload']['tmp_name'], $uploadfile)) {
    sleep(2);
    echo "success";
} else {
    // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
    // Otherwise onSubmit event will not be fired
    echo "error";
}?>
