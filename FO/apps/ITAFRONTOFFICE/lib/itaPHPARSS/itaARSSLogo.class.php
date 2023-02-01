<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaARSSLogo
 *
 * @author michele
 */
class itaARSSLogo {

    public static function getLogo() {
        $fileIco = ITA_LIB_PATH . '/itaPHPARSS/Aruba.png';
        return itaImg::base64src($fileIco);
    }

}

?>
