<?php
require_once './ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
App::load();
$uploaddir = App::getPath('temporary.uploadPath');
$uploadfile = $uploaddir.'/'.basename($_FILES['RemoteFile']['name']);
if (move_uploaded_file($_FILES['RemoteFile']['tmp_name'], $uploadfile)) {
    sleep(1);
} else {
    // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
    // Otherwise onSubmit event will not be fired
    header('Content-Type: text/plain; charset=ISO-8859-1');
    echo "error";
}?>
