<?php

/**
 *
 * Utility Omnis
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibOmnis {
    
    /**
     * Headers utilizzati da Omnis per i campi Picture
     * Offset = 26: Height
     * Offset = 28: Width
     * @var array 
     */
    static $omnisPictureHeader1 = array(
        0x00, 0x0a, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00,
        0x00, 0x28, 0xcc, 0x10,
        0x00, 0x00, 0x00, 0x00,
        0x00, 0x00
    );
    
    static $omnisPictureHeader2 = array(            
        0x00, 0x00, 0x00, 0x00, 
        0x00, 0x00, 0x00, 0x00,
        0x00, 0x00, 0x00, 0x00, 
        0x00, 0x00, 0x00, 0x00, 
        0x00, 0x00, 0x00, 0x00, 
        0x00, 0x00, 0x00, 0x00, 
        0x00, 0x00
    );
    
    /**
     * Effettua criptaggio con algoritmo proprietario di Omnis
     * @param string $strInput Stringa da criptare
     * @param string $strSecret Chiave di criptaggio
     * @return string Stringa criptata
     */
    public static function omnisCrypt($strInput, $strSecret = "17") {
        $input = unpack("C*", $strInput);
        $secret = unpack("C*", $strSecret);
        
        $res = "";
        $spos = 0;
        for ($pos = 0; $pos < count($input); $pos++) {	
            $ascii = self::toASCII($input[$pos + 1] + $secret[$spos + 1]);

            if(strlen($ascii) > 0){
                $res .= $ascii;
            }			

            ++$spos;
            if ($spos >= count($secret)) {
                $spos = 0;
            }
        }	
        
        return str_replace("000", "", self::convertStringToHex(trim($res)));                       
    }
    
    /**
     * Effettua decriptaggio con algoritmo proprietario di Omnis
     * @param string $strCrypted Stringa criptata
     * @param string $strSecret Chiave di criptaggio 
     * @return string Stringa decriptata
     */
    public static function omnisDecrypt($strCrypted, $strSecret = "17") {        
        $decStr = str_replace("", "000", self::convertHexToString(trim($strCrypted)));
        $secret = unpack("C*", $strSecret);
        $dec = unpack("C*", $decStr);
                
        $res = array();
        $spos = 0;
        for ($pos = 0; $pos < count($dec); $pos++) {	
            $res[$pos + 1] = $dec[$pos + 1] - $secret[$spos + 1];
            
            ++$spos;
            if ($spos >= count($secret)) {
                $spos = 0;
            }
        }	                                
        
        return (is_array($res) ? implode(array_map("chr", $res)) : null);
    }
    
    /**
     * Converte Omnis picture in binary
     * @param stream $handle Handle
     * @return string Immagine
     */
    public static function fromOmnisPicture($handle) {
        if(is_resource($handle)){
            $bin = stream_get_contents($handle, -1, 56);
            return $bin;
        }
        return;
    }
    
    /**
     * Converte binary in Omnis Picture
     * @param string $data Immagine
     * @param int $height Height
     * @param int $width Width
     * @return string Omnis Picture
     */
    public static function toOmnisPicture($data, $height = 0, $width = 0) {
        if ($data === null) {
            return '';
        }
        $toPrepend1 = implode(array_map("chr", self::$omnisPictureHeader1));
        $toPrependHeight = pack("n*", $height);
        $toPrependWidth = pack("n*", $width);
        $toPrepend2 = implode(array_map("chr", self::$omnisPictureHeader2));
        $data = $toPrepend1 . $toPrependHeight . $toPrependWidth . $toPrepend2 . $data;
        return $data;
    }

    private static function toASCII($value) {
        $length = 4;
        $result = "";
        for ($i = $length - 1; $i >= 0; $i--) {
            $c = chr((($value >> (8 * $i)) & 0xFF));
            $result .= $c;						
        }
        return $result;
    }
    
    public static function convertStringToHex($str){
        return bin2hex($str);
    }
    
    public static function convertHexToString($hex){
        return itaLib::hex2BinDecode($hex);
    }
    
    public static function toOmnisDecimal($toConvert, $dec = 5, $separatore = ',') {
        // N.B.: Non utilizza number_format perchè i parametri 3 e 4 sono supportati solo dalla versione 5.4
        //$pointPos = strpos($toConvert, '.');
        $pointPos = explode('.', $toConvert);
        if (count($pointPos) === 1) {
            $intPart = $toConvert;
            if ($intPart === null) {
                $intPart = 0;
            }
            $decPart = str_pad("", $dec, "0");
        } else {
            $decPart = str_pad($pointPos[1], $dec, "0");
            if(strlen($decPart) > 2){
                $decPart = substr($decPart, 0, 2);
            }
            $intPart = $pointPos[0];
            if ($intPart === null || $intPart === '') {
                $intPart = 0;
            }
        }
        $toReturn = round(($intPart . '.' . $decPart), $dec);
        if ($separatore === '.') {
            return $toReturn;
        } else {
            return str_replace('.', ',', $toReturn);
        }
    }    
    public static function fromOmnisDecimal($toConvert) {        
        return str_replace(',', '.', $toConvert);
    }
    
}

?>