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

    static public function imageResize($originalImage,$toWidth=false,$toHeight=false,$saveToFile='',$overWrite=true) {

        if ($saveToFile != '') {
            if (file_exists($saveToFile) && $overWrite == false) {
                return false;
            }
        }

        $fileInfo=pathinfo($originalImage);
        $extension=strtolower($fileInfo['extension']);

        // Get the original geometry and calculate scales
        list($width, $height) = getimagesize($originalImage);

//        if ($toWidth !=false){
        $xscale=$width/$toWidth;
////        }else{
//            $yscale=$height/$toHeight;
//            $xscale=$yscale;
//        }

//        if ($toHeight !=false){
        $yscale=$height/$toHeight;
//        }else{
//            $yscale=$height/$toHeight;
//            $xscale=$yscale;
//        }
        // Recalculate new size with default ratio
        if ($yscale>$xscale) {
            $new_width = round($width * (1/$yscale));
            $new_height = round($height * (1/$yscale));
        }
        else {
            $new_width = round($width * (1/$xscale));
            $new_height = round($height * (1/$xscale));
        }
        // Resize the original image
        $imageResized = imagecreatetruecolor($new_width, $new_height);
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                $imageTmp=imagecreatefromjpeg($originalImage);
                break;
            case 'gif':
                $imageTmp=imagecreatefromgif($originalImage);
                break;
            case 'png':
                $imageTmp=imagecreatefrompng($originalImage);
                break;
            default:
                return false;
                break;
        }
        imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($imageResized,$saveToFile);
                return true;
                break;
            case 'gif':
                imagegif($imageResized,$saveToFile);
                return true;
                break;
            case 'png':
                imagepng($imageResized,$saveToFile);
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    static public function getBase64($file){
        $fp = @fopen($file,"rb", 0);
        if($fp) {
            $picture = fread($fp,filesize($file));
            fclose($fp);
            return base64_encode($picture);
        }else {
            return false;
        }
    }

    static public function base64src($file) {
        $fileInfo=pathinfo($file);
        $extension=strtolower($fileInfo['extension']);
        switch ($extension) {
            case "gif": case "jpg": case "jpeg": case "png": case "bmp":
                break;
            default:
                return false;
        }

        $fp = @fopen($file,"rb", 0);
        if($fp) {
            $picture = fread($fp,filesize($file));
            fclose($fp);
            //$base64 = chunk_split(base64_encode($picture));
            $base64 = base64_encode($picture);            
            $src='data:image/'.$extension.';base64,'.$base64;
            return $src;
        }else {
            return false;
        }



    }
}
?>
