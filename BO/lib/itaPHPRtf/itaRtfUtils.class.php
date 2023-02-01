<?php

require_once ITA_LIB_PATH . '/itaPHPCore/itaImg.class.php';
require_once ITA_BASE_PATH . '/apps/CodeGateway/cgwLib.class.php';
require_once ITA_LIB_PATH . '/phpqrcode/qrlib.php';

/**
 * Classe di utils rtf
 *
 * @author luca cardinali
 */
class itaRtfUtils {

    const QRCODE_HEIGHT = 100;
    const QRCODE_WIDTH = 100;
    const TAG_BEGIN = "!QRCODE-BEGIN!";
    const TAG_END = "!QRCODE-END!";

    /**
     * estrae le info del qrcode, lo genera e lo aggiunge al testo rtf
     * @param string $content il testo rtf
     * @return string il testo rtf con qrcode
     */
    public static function generateContentWithQrcodeForGateway($content, $arrayDati) {
        $info = itaRtfUtils::extractQrcodeInfo($content);
        if (!$info) {
            return null;
        }
        // creo il qrcode
        $filetemp = itaLib::createAppsTempPath() . DIRECTORY_SEPARATOR . uniqid() . '.png';
        $cgwLibClient = new cgwLibClient();
        $base64 = $cgwLibClient->generateGatewayQRCODE(cgwLibContexts::CTX_KIOSK_PEOPLE_CERTIFICATO, $arrayDati);
        unlink($filetemp);

        // eseguo la replace
        return str_replace($info['STRING_TO_REPLACE'], itaRtfUtils::generateBarcodePng(base64_decode($base64), $info['HEIGHT'], $info['WIDTH']), $info['STRING_CONTENT']);
    }

    /**
     * estrae le info del qrcode, lo genera e lo aggiunge al testo rtf
     * @param string $content il testo rtf
     * @return string il testo rtf con qrcode
     */
    public static function generateContentWithQrcode($content) {
        $info = itaRtfUtils::extractQrcodeInfo($content);
        if (!$info) {
            return null;
        }
        // creo il qrcode
        $filetemp = itaLib::createAppsTempPath() . DIRECTORY_SEPARATOR . uniqid() . '.png';
        QRcode::png($info['COD'], $filetemp, QR_ECLEVEL_H, $info['HEIGHT_DIV'], $info['WIDTH_DIV']);
        $base64 = itaImg::getBase64($filetemp);
        unlink($filetemp);

        // eseguo la replace
        return str_replace($info['STRING_TO_REPLACE'], itaRtfUtils::generateBarcodePng(base64_decode($base64), $info['HEIGHT'], $info['WIDTH']), $info['STRING_CONTENT']);
    }

    /**
     * estrae le info del qrcode dal testo rtf
     * @param type $content il file rtf
     * @return array le info del qrcode
     */
    private static function extractQrcodeInfo($content) {
        $toReturn = array();
        $toReturn['HEIGHT'] = itaRtfUtils::QRCODE_HEIGHT;
        $toReturn['WIDTH'] = itaRtfUtils::QRCODE_WIDTH;
        // prendo tutto quello contenuto tra TAG_BEGIN e TAG_END
        $sub = substr($content, strpos($content, itaRtfUtils::TAG_BEGIN) + strlen(itaRtfUtils::TAG_BEGIN), strlen($content));
        $substringTag = substr($sub, 0, strpos($sub, itaRtfUtils::TAG_END));
        if (!$substringTag) {
            return null;
        }
        // possibile contenuto del tag info:
        // HEIGHT:100!WIDTH:50!54453 quindi faccio la explode su !
        $data = explode("!", $substringTag);
        foreach ($data as $value) {
            if (strpos($value, 'HEIGHT') !== false) {
                // se contiene la parola HEIGHT faccio la explode su : per prendere il valore
                $dimInfo = explode(":", $value);
                $toReturn['HEIGHT'] = $dimInfo;
            } else if (strpos($value, 'WIDTH') !== false) {
                // se contiene la parola WIDTH faccio la explode su : per prendere il valore
                $dimInfo = explode(":", $value);
                $toReturn['WIDTH'] = $dimInfo;
            } else {
                // altrimenti è il codice del testo
                $toReturn['COD'] = $value;
            }
        }
        $toReturn['STRING_CONTENT'] = $content;
        $toReturn['STRING_TO_REPLACE'] = itaRtfUtils::TAG_BEGIN . $substringTag . itaRtfUtils::TAG_END;
        $toReturn['HEIGHT_DIV'] = round(($toReturn['HEIGHT'] / 25), 0);
        $toReturn['WIDTH_DIV'] = round(($toReturn['WIDTH'] / 25), 0);

        return $toReturn;
    }

    /**
     * Genera stringa rtf che rappresenta il barcode (formato png)
     * @param type $content il file rtf
     * @return array le info del qrcode
     */
    private static function generateBarcodePng($content, $width, $height) {
        return "{\\pict\\pngblip\\picw" . $width . "\\pich" . $height
                . "\\picwgoal" . ($width * 15) . "\\pichgoal" . ($height * 15)
                . "\n" . itaRtfUtils::strToHex($content) . "}";
    }

    private static function strToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

}
