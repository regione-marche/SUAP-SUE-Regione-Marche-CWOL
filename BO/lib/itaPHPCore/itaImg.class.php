<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaImgclass
 *
 * @author utente
 */
class itaImg {

    const IMG_RETURN_FLAG_BASE64 = 1;
    const IMG_RETURN_FLAG_BINARY = 2;
    const IMG_RETURN_FLAG_FILE = 3;
    const IMG_RETURN_FLAG_BASE64SRC = 4;

    static public function imageDrawString($file, $Font, $X, $Y, $String, $Color, $returnFlag = 1) {
        if (!file_exists($file)) {
            return false;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $thickness = $Font;
        switch ($ext) {
            case 'gif':
                $imgHandle = @imagecreatefromgif($file);
                break;
            case 'jpg':
                $imgHandle = @imagecreatefromjpeg($file);
                break;
            case 'png':
                $imgHandle = @imagecreatefrompng($file);
                break;
            default :
                return false;
        }
        $colorHandle = imagecolorallocate($imgHandle, 255, 0, 0);
        imagesetthickness($imgHandle, $thickness);
        imagestring($imgHandle, $thickness, $X, $Y, $String, $colorHandle);
        switch ($ext) {
            case 'gif':
                ob_clean();
                imagegif($imgHandle);
                return self::base64src(ob_get_clean(), 'gif');
            case 'jpg':
                ob_clean();
                imagejpeg($imgHandle);
                return self::base64src(ob_get_clean(), 'jpg');
            case 'png':
                ob_clean();
                imagepng($imgHandle);
                return self::base64src(ob_get_clean(), 'png');
            default:
                return false;
        }
    }

    static public function imageResize($originalImage, $toWidth = false, $toHeight = false, $saveToFile = '', $overWrite = true) {

        if ($saveToFile != '') {
            if (file_exists($saveToFile) && $overWrite == false) {
                return false;
            }
        }

        $fileInfo = pathinfo($originalImage);
        $extension = strtolower($fileInfo['extension']);

        // Get the original geometry and calculate scales
        list($width, $height) = getimagesize($originalImage);

//        if ($toWidth !=false){
        $xscale = $width / $toWidth;
////        }else{
//            $yscale=$height/$toHeight;
//            $xscale=$yscale;
//        }
//        if ($toHeight !=false){
        $yscale = $height / $toHeight;
//        }else{
//            $yscale=$height/$toHeight;
//            $xscale=$yscale;
//        }
        // Recalculate new size with default ratio
        if ($yscale > $xscale) {
            $new_width = round($width * (1 / $yscale));
            $new_height = round($height * (1 / $yscale));
        } else {
            $new_width = round($width * (1 / $xscale));
            $new_height = round($height * (1 / $xscale));
        }
        // Resize the original image
        $imageResized = imagecreatetruecolor($new_width, $new_height);
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                $imageTmp = imagecreatefromjpeg($originalImage);
                break;
            case 'gif':
                $imageTmp = imagecreatefromgif($originalImage);
                break;
            case 'png':
                $imageTmp = imagecreatefrompng($originalImage);
                break;
            default:
                return false;
                break;
        }
        imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($imageResized, $saveToFile);
                return true;
                break;
            case 'gif':
                imagegif($imageResized, $saveToFile);
                return true;
                break;
            case 'png':
                imagepng($imageResized, $saveToFile);
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    static public function getBase64($file) {
        $fp = @fopen($file, "rb", 0);
        if ($fp) {
            $picture = fread($fp, filesize($file));
            fclose($fp);
            return base64_encode($picture);
        } else {
            return false;
        }
    }

    static public function base64src($file, $ext = '') {
        if ($file && !file_exists($file)) {
            $extension = $ext;
            $picture = $file;
        } else {
            $fileInfo = pathinfo($file);
            $extension = strtolower($fileInfo['extension']);
            $fp = @fopen($file, "rb", 0);
            if ($fp) {
                $picture = fread($fp, filesize($file));
                fclose($fp);
            } else {
                return false;
            }
        }
        switch ($extension) {
            case "gif": case "jpg": case "jpeg": case "png": case "bmp":
                break;
            default:
                return false;
        }
        return 'data:image/' . $extension . ';base64,' . base64_encode($picture);
    }

    public static function SetDirectoryImg($tipoPath = 'BASE', $crea = true, $ditta = '', $extradata = array()) {
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        switch ($tipoPath) {
            case 'BASE':
                $d_nome = '';
                break;
            case 'IMMAGINI':
                $d_nome = '/immagini';
                break;
            default:
                $d_nome = '';
                break;
        }

        $d_dir = Config::getPath('general.fileEnte') . 'ente' . $ditta;
        if (!is_dir($d_dir . $d_nome)) {
            if ($crea) {
                if (!@mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

}

?>
