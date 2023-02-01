<?php

class itaDiacriticChars {

    private static $chars = array(
        array('code' => '&#193;', 'description' => 'A acuta', 'transliteration' => 'A'),
        array('code' => '&#225;', 'description' => 'a acuta', 'transliteration' => 'A'),
        array('code' => '&#192;', 'description' => 'A grave', 'transliteration' => 'A'),
        array('code' => '&#224;', 'description' => 'a grave', 'transliteration' => 'A'),
        array('code' => '&#194;', 'description' => 'A circonflessa', 'transliteration' => 'A'),
        array('code' => '&#226;', 'description' => 'a circonflessa', 'transliteration' => 'A'),
        array('code' => '&#196;', 'description' => 'A dieresi', 'transliteration' => 'AE'),
        array('code' => '&#228;', 'description' => 'a dieresi', 'transliteration' => 'AE'),
        array('code' => '&#258;', 'description' => 'A breve', 'transliteration' => 'A'),
        array('code' => '&#259;', 'description' => 'a breve', 'transliteration' => 'A'),
        array('code' => '&#256;', 'description' => 'A linea', 'transliteration' => 'A'),
        array('code' => '&#257;', 'description' => 'a linea', 'transliteration' => 'A'),
        array('code' => '&#195;', 'description' => 'A tilde', 'transliteration' => 'A'),
        array('code' => '&#227;', 'description' => 'a tilde', 'transliteration' => 'A'),
        array('code' => '&#197;', 'description' => 'A tondo', 'transliteration' => 'AA'),
        array('code' => '&#229;', 'description' => 'a tondo', 'transliteration' => 'AA'),
        array('code' => '&#260;', 'description' => 'A ogonek', 'transliteration' => 'A'),
        array('code' => '&#261;', 'description' => 'a ogonek', 'transliteration' => 'A'),
        array('code' => '&#198;', 'description' => 'Insieme AE', 'transliteration' => 'AE'),
        array('code' => '&#230;', 'description' => 'Insieme ae', 'transliteration' => 'AE'),
        array('code' => '&#262;', 'description' => 'C acuta', 'transliteration' => 'C'),
        array('code' => '&#263;', 'description' => 'c acuta', 'transliteration' => 'C'),
        array('code' => '&#266;', 'description' => 'C punto', 'transliteration' => 'C'),
        array('code' => '&#267;', 'description' => 'c punto', 'transliteration' => 'C'),
        array('code' => '&#264;', 'description' => 'C circonflessa', 'transliteration' => 'C'),
        array('code' => '&#265;', 'description' => 'c circonflessa', 'transliteration' => 'C'),
        array('code' => '&#268;', 'description' => 'C caron', 'transliteration' => 'C'),
        array('code' => '&#269;', 'description' => 'c caron', 'transliteration' => 'C'),
        array('code' => '&#199;', 'description' => 'C cedilla', 'transliteration' => 'C'),
        array('code' => '&#231;', 'description' => 'c cedilla', 'transliteration' => 'C'),
        array('code' => '&#270;', 'description' => 'D caron', 'transliteration' => 'D'),
        array('code' => '&#271;', 'description' => 'd caron', 'transliteration' => 'D'),
        array('code' => '&#272;', 'description' => 'Eth', 'transliteration' => 'D'),
        array('code' => '&#273;', 'description' => 'Eth', 'transliteration' => 'D'),
        array('code' => '&#208;', 'description' => 'Eth', 'transliteration' => 'D'),
        array('code' => '&#393;', 'description' => 'Eth', 'transliteration' => 'D'),
        array('code' => '&#201;', 'description' => 'E acuta', 'transliteration' => 'E'),
        array('code' => '&#233;', 'description' => 'e acuta', 'transliteration' => 'E'),
        array('code' => '&#200;', 'description' => 'E grave', 'transliteration' => 'E'),
        array('code' => '&#232;', 'description' => 'e grave', 'transliteration' => 'E'),
        array('code' => '&#278;', 'description' => 'E punto', 'transliteration' => 'E'),
        array('code' => '&#279;', 'description' => 'e punto', 'transliteration' => 'E'),
        array('code' => '&#202;', 'description' => 'E circonflessa', 'transliteration' => 'E'),
        array('code' => '&#234;', 'description' => 'e circonflessa', 'transliteration' => 'E'),
        array('code' => '&#203;', 'description' => 'E dieresi', 'transliteration' => 'E'),
        array('code' => '&#235;', 'description' => 'e dieresi', 'transliteration' => 'E'),
        array('code' => '&#282;', 'description' => 'E caron', 'transliteration' => 'E'),
        array('code' => '&#283;', 'description' => 'e caron', 'transliteration' => 'E'),
        array('code' => '&#276;', 'description' => 'E breve', 'transliteration' => 'E'),
        array('code' => '&#277;', 'description' => 'e breve', 'transliteration' => 'E'),
        array('code' => '&#274;', 'description' => 'E linea', 'transliteration' => 'E'),
        array('code' => '&#275;', 'description' => 'e linea', 'transliteration' => 'E'),
        array('code' => '&#280;', 'description' => 'E ogonek', 'transliteration' => 'E'),
        array('code' => '&#281;', 'description' => 'e ogonek', 'transliteration' => 'E'),
        array('code' => '&#288;', 'description' => 'G punto', 'transliteration' => 'G'),
        array('code' => '&#289;', 'description' => 'g punto', 'transliteration' => 'G'),
        array('code' => '&#284;', 'description' => 'G circonflessa', 'transliteration' => 'G'),
        array('code' => '&#285;', 'description' => 'g circonflessa', 'transliteration' => 'G'),
        array('code' => '&#286;', 'description' => 'G breve', 'transliteration' => 'G'),
        array('code' => '&#287;', 'description' => 'g breve', 'transliteration' => 'G'),
        array('code' => '&#290;', 'description' => 'G cedilla', 'transliteration' => 'G'),
        array('code' => '&#291;', 'description' => 'g cedilla', 'transliteration' => 'G'),
        array('code' => '&#292;', 'description' => 'H circonflessa', 'transliteration' => 'H'),
        array('code' => '&#293;', 'description' => 'h circonflessa', 'transliteration' => 'H'),
        array('code' => '&#294;', 'description' => 'H barra', 'transliteration' => 'H'),
        array('code' => '&#295;', 'description' => 'h barra', 'transliteration' => 'H'),
        array('code' => '&#73;', 'description' => 'I senza punto', 'transliteration' => 'I'),
        array('code' => '&#305;', 'description' => 'i punto', 'transliteration' => 'I'),
        array('code' => '&#105;', 'description' => 'i senza punto', 'transliteration' => 'I'),
        array('code' => '&#205;', 'description' => 'I acuta', 'transliteration' => 'I'),
        array('code' => '&#237;', 'description' => 'i acuta', 'transliteration' => 'I'),
        array('code' => '&#204;', 'description' => 'I grave', 'transliteration' => 'I'),
        array('code' => '&#236;', 'description' => 'i grave', 'transliteration' => 'I'),
        array('code' => '&#304;', 'description' => 'I punto', 'transliteration' => 'I'),
        array('code' => '&#206;', 'description' => 'I circonflessa', 'transliteration' => 'I'),
        array('code' => '&#238;', 'description' => 'i circonflessa', 'transliteration' => 'I'),
        array('code' => '&#207;', 'description' => 'I dieresi', 'transliteration' => 'I'),
        array('code' => '&#239;', 'description' => 'i dieresi', 'transliteration' => 'I'),
        array('code' => '&#300;', 'description' => 'I breve', 'transliteration' => 'I'),
        array('code' => '&#301;', 'description' => 'i breve', 'transliteration' => 'I'),
        array('code' => '&#298;', 'description' => 'I linea', 'transliteration' => 'I'),
        array('code' => '&#299;', 'description' => 'i linea', 'transliteration' => 'I'),
        array('code' => '&#296;', 'description' => 'I tilde', 'transliteration' => 'I'),
        array('code' => '&#297;', 'description' => 'i tilde', 'transliteration' => 'I'),
        array('code' => '&#302;', 'description' => 'I ogonek', 'transliteration' => 'I'),
        array('code' => '&#303;', 'description' => 'i ogonek', 'transliteration' => 'I'),
        array('code' => '&#306;', 'description' => 'Insieme IJ', 'transliteration' => 'IJ'),
        array('code' => '&#307;', 'description' => 'Insieme ij', 'transliteration' => 'IJ'),
        array('code' => '&#308;', 'description' => 'J circonflessa', 'transliteration' => 'J'),
        array('code' => '&#309;', 'description' => 'j circonflessa', 'transliteration' => 'J'),
        array('code' => '&#310;', 'description' => 'K cedilla', 'transliteration' => 'K'),
        array('code' => '&#311;', 'description' => 'k cedilla', 'transliteration' => 'K'),
        array('code' => '&#313;', 'description' => 'L acuta', 'transliteration' => 'L'),
        array('code' => '&#314;', 'description' => 'l acuta', 'transliteration' => 'L'),
        array('code' => '&#319;', 'description' => 'L punto', 'transliteration' => 'L'),
        array('code' => '&#320;', 'description' => 'l punto', 'transliteration' => 'L'),
        array('code' => '&#317;', 'description' => 'L caron', 'transliteration' => 'L'),
        array('code' => '&#318;', 'description' => 'l caron', 'transliteration' => 'L'),
        array('code' => '&#315;', 'description' => 'L cedilla', 'transliteration' => 'L'),
        array('code' => '&#316;', 'description' => 'l cedilla', 'transliteration' => 'L'),
        array('code' => '&#321;', 'description' => 'L barra traversa', 'transliteration' => 'L'),
        array('code' => '&#322;', 'description' => 'l barra traversa', 'transliteration' => 'L'),
        array('code' => '&#323;', 'description' => 'N acuta', 'transliteration' => 'N'),
        array('code' => '&#324;', 'description' => 'n acuta', 'transliteration' => 'N'),
        array('code' => '&#327;', 'description' => 'N caron', 'transliteration' => 'N'),
        array('code' => '&#328;', 'description' => 'n caron', 'transliteration' => 'N'),
        array('code' => '&#209;', 'description' => 'N tilde', 'transliteration' => 'N'),
        array('code' => '&#241;', 'description' => 'n tilde', 'transliteration' => 'N'),
        array('code' => '&#325;', 'description' => 'N cedilla', 'transliteration' => 'N'),
        array('code' => '&#326;', 'description' => 'n cedilla', 'transliteration' => 'N'),
        array('code' => '&#329;', 'description' => 'Eng', 'transliteration' => 'N'),
        array('code' => '&#331;', 'description' => 'Eng', 'transliteration' => 'N'),
        array('code' => '&#211;', 'description' => 'O acuta', 'transliteration' => 'O'),
        array('code' => '&#243;', 'description' => 'o acuta', 'transliteration' => 'O'),
        array('code' => '&#210;', 'description' => 'O grave', 'transliteration' => 'O'),
        array('code' => '&#242;', 'description' => 'o grave', 'transliteration' => 'O'),
        array('code' => '&#212;', 'description' => 'O circonflessa', 'transliteration' => 'O'),
        array('code' => '&#244;', 'description' => 'o circonflessa', 'transliteration' => 'O'),
        array('code' => '&#214;', 'description' => 'O dieresi', 'transliteration' => 'OE'),
        array('code' => '&#246;', 'description' => 'o dieresi', 'transliteration' => 'OE'),
        array('code' => '&#334;', 'description' => 'O breve', 'transliteration' => 'O'),
        array('code' => '&#335;', 'description' => 'o breve', 'transliteration' => 'O'),
        array('code' => '&#332;', 'description' => 'O linea', 'transliteration' => 'O'),
        array('code' => '&#333;', 'description' => 'o linea', 'transliteration' => 'O'),
        array('code' => '&#213;', 'description' => 'O tilde', 'transliteration' => 'O'),
        array('code' => '&#245;', 'description' => 'o tilde', 'transliteration' => 'O'),
        array('code' => '&#336;', 'description' => 'O doppia acuta', 'transliteration' => 'O'),
        array('code' => '&#337;', 'description' => 'o doppia acuta', 'transliteration' => 'O'),
        array('code' => '&#216;', 'description' => 'O barra', 'transliteration' => 'OE'),
        array('code' => '&#248;', 'description' => 'o barra', 'transliteration' => 'OE'),
//        array('code' => '&#140;', 'description' => 'Insieme OE', 'transliteration' => 'OE'),
//        array('code' => '&#156;', 'description' => 'Insieme oe', 'transliteration' => 'OE'),
        array('code' => '&#338;', 'description' => 'Insieme OE', 'transliteration' => 'OE'),
        array('code' => '&#339;', 'description' => 'Insieme oe', 'transliteration' => 'OE'),
        array('code' => '&#340;', 'description' => 'R acuta', 'transliteration' => 'R'),
        array('code' => '&#341;', 'description' => 'r acuta', 'transliteration' => 'R'),
        array('code' => '&#344;', 'description' => 'R caron', 'transliteration' => 'R'),
        array('code' => '&#345;', 'description' => 'r caron', 'transliteration' => 'R'),
        array('code' => '&#342;', 'description' => 'R cedilla', 'transliteration' => 'R'),
        array('code' => '&#343;', 'description' => 'r cedilla', 'transliteration' => 'R'),
        array('code' => '&#346;', 'description' => 'S acuta', 'transliteration' => 'S'),
        array('code' => '&#347;', 'description' => 's acuta', 'transliteration' => 'S'),
        array('code' => '&#348;', 'description' => 'S circonflessa', 'transliteration' => 'S'),
        array('code' => '&#349;', 'description' => 's circonflessa', 'transliteration' => 'S'),
//        array('code' => '&#138;', 'description' => 'S caron', 'transliteration' => 'S'),
//        array('code' => '&#154;', 'description' => 's caron', 'transliteration' => 'S'),
        array('code' => '&#352;', 'description' => 'S caron', 'transliteration' => 'S'),
        array('code' => '&#353;', 'description' => 's caron', 'transliteration' => 'S'),
        array('code' => '&#350;', 'description' => 'S cedilla', 'transliteration' => 'S'),
        array('code' => '&#351;', 'description' => 's cedilla', 'transliteration' => 'S'),
        array('code' => '&#223;', 'description' => 'Doppia s', 'transliteration' => 'SS'),
        array('code' => '&#356;', 'description' => 'T caron', 'transliteration' => 'T'),
        array('code' => '&#357;', 'description' => 't caron', 'transliteration' => 'T'),
        array('code' => '&#354;', 'description' => 'T cedilla', 'transliteration' => 'T'),
        array('code' => '&#355;', 'description' => 't cedilla', 'transliteration' => 'T'),
        array('code' => '&#222;', 'description' => 'Thorn', 'transliteration' => 'TH'),
        array('code' => '&#254;', 'description' => 'Thorn', 'transliteration' => 'TH'),
        array('code' => '&#358;', 'description' => 'T barra', 'transliteration' => 'T'),
        array('code' => '&#359;', 'description' => 't barra', 'transliteration' => 'T'),
        array('code' => '&#218;', 'description' => 'U acuta', 'transliteration' => 'U'),
        array('code' => '&#250;', 'description' => 'u acuta', 'transliteration' => 'U'),
        array('code' => '&#217;', 'description' => 'U grave', 'transliteration' => 'U'),
        array('code' => '&#249;', 'description' => 'u grave', 'transliteration' => 'U'),
        array('code' => '&#219;', 'description' => 'U circonflessa', 'transliteration' => 'U'),
        array('code' => '&#251;', 'description' => 'u circonflessa', 'transliteration' => 'U'),
        array('code' => '&#220;', 'description' => 'U dieresi', 'transliteration' => 'UE'),
        array('code' => '&#252;', 'description' => 'u dieresi', 'transliteration' => 'UE'),
        array('code' => '&#364;', 'description' => 'U breve', 'transliteration' => 'U'),
        array('code' => '&#365;', 'description' => 'u breve', 'transliteration' => 'U'),
        array('code' => '&#362;', 'description' => 'U linea', 'transliteration' => 'U'),
        array('code' => '&#363;', 'description' => 'u linea', 'transliteration' => 'U'),
        array('code' => '&#360;', 'description' => 'U tilde', 'transliteration' => 'U'),
        array('code' => '&#361;', 'description' => 'u tilde', 'transliteration' => 'U'),
        array('code' => '&#366;', 'description' => 'U tondo', 'transliteration' => 'U'),
        array('code' => '&#367;', 'description' => 'u tondo', 'transliteration' => 'U'),
        array('code' => '&#370;', 'description' => 'U ogonek', 'transliteration' => 'U'),
        array('code' => '&#371;', 'description' => 'u ogonek', 'transliteration' => 'U'),
        array('code' => '&#368;', 'description' => 'U doppia acuta', 'transliteration' => 'U'),
        array('code' => '&#369;', 'description' => 'u doppia acuta', 'transliteration' => 'U'),
        array('code' => '&#372;', 'description' => 'W circonflessa', 'transliteration' => 'W'),
        array('code' => '&#373;', 'description' => 'w circonflessa', 'transliteration' => 'W'),
        array('code' => '&#221;', 'description' => 'Y acuta', 'transliteration' => 'Y'),
        array('code' => '&#253;', 'description' => 'y acuta', 'transliteration' => 'Y'),
        array('code' => '&#374;', 'description' => 'Y circonflessa', 'transliteration' => 'Y'),
        array('code' => '&#375;', 'description' => 'y circonflessa', 'transliteration' => 'Y'),
//        array('code' => '&#159;', 'description' => 'Y dieresi', 'transliteration' => 'Y'),
        array('code' => '&#376;', 'description' => 'Y dieresi', 'transliteration' => 'Y'),
        array('code' => '&#255;', 'description' => 'y dieresi', 'transliteration' => 'Y'),
        array('code' => '&#377;', 'description' => 'Z acuta', 'transliteration' => 'Z'),
        array('code' => '&#378;', 'description' => 'z acuta', 'transliteration' => 'Z'),
        array('code' => '&#379;', 'description' => 'Z punto', 'transliteration' => 'Z'),
        array('code' => '&#380;', 'description' => 'z punto', 'transliteration' => 'Z'),
//        array('code' => '&#142;', 'description' => 'Z caron', 'transliteration' => 'Z'),
//        array('code' => '&#158;', 'description' => 'z caron', 'transliteration' => 'Z'),
        array('code' => '&#381;', 'description' => 'Z caron', 'transliteration' => 'Z'),
        array('code' => '&#382;', 'description' => 'z caron', 'transliteration' => 'Z'),
//        array('code' => '&#192;', 'description' => 'A grave', 'transliteration' => 'A\''),
//        array('code' => '&#224;', 'description' => 'a grave', 'transliteration' => 'A\''),
//        array('code' => '&#200;', 'description' => 'E grave', 'transliteration' => 'E\''),
//        array('code' => '&#232;', 'description' => 'e grave', 'transliteration' => 'E\''),
//        array('code' => '&#204;', 'description' => 'I grave', 'transliteration' => 'I\''),
//        array('code' => '&#236;', 'description' => 'i grave', 'transliteration' => 'I\''),
//        array('code' => '&#210;', 'description' => 'O grave', 'transliteration' => 'O\''),
//        array('code' => '&#242;', 'description' => 'o grave', 'transliteration' => 'O\''),
//        array('code' => '&#217;', 'description' => 'U grave', 'transliteration' => 'U\''),
//        array('code' => '&#249;', 'description' => 'u grave', 'transliteration' => 'U\'')
    );

    /**
     * Ritorna la mappa dei caratteri diacritici.
     * 
     * @return array
     */
    public static function getChars() {
        return self::$chars;
    }

    /**
     * Ritorna la mappa dei caratteri diacritici indicizzata per traslitterazione.
     * 
     * @return array
     */
    public static function getCharsByTransliteration() {
        $chars = array();
        foreach (self::$chars as $char) {
            $tr = substr($char['transliteration'], 0, 1);
            $chars[$tr] = $chars[$tr] ?: array();
            $chars[$tr][] = $char;
        }
        return $chars;
    }

    /**
     * Ritorna la traslitterazione di un carattere dato il suo codice.
     * 
     * @param string $code Codice del carattere.
     * @return string Traslitterazione del carattere.
     */
    public static function getCharTransliterationByCode($code) {
        if (is_numeric($code)) {
            $code = "&#$code;";
        }

        foreach (self::$chars as $char) {
            if ($char['code'] === $code) {
                return $char['transliteration'];
            }
        }

        return '';
    }

    /**
     * Trasforma i caratteri diacriti gestiti correttamente con il relativo
     * codice decimale.
     * 
     * @param string $str Stringa da trasformare.
     * @return string
     */
    private static function HTMLDecodeEntities($str) {
        foreach (str_split($str) as $c) {
            if (ord($c) > 191) {
                $str = str_replace($c, '&#' . ord($c) . ';', $str);
            }
        }

        return $str;
    }

    /**
     * Trasforma una stringa con entità HTML nella sua versione traslitterata.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function HTML2Transliterated($str) {
        $str = self::HTMLDecodeEntities($str);

        preg_match_all('/(&#[0-9]{2,3};)/i', $str, $matches);
        foreach ($matches[1] as $c) {
            $str = str_replace($c, self::getCharTransliterationByCode($c), $str);
        }

        return $str;
    }

    /**
     * Trasforma una stringa con entità Unicode nella sua versione traslitterata.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function Unicode2Transliterated($str) {
        return self::HTML2Transliterated(self::Unicode2HTML($str));
    }

    /**
     * Trasforma una stringa con entità UTF8 nella sua versione traslitterata.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function UTF82Transliterated($str) {
        return self::HTML2Transliterated(self::UTF82HTML($str));
    }

    /**
     * Trasforma le entità Unicode in entità HTML in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function Unicode2HTML($str) {
        /*
         * Converto i valori esadecimali in decimale.
         */
        preg_match_all('/%u([0-9a-f]{4})/i', $str, $matches);
        foreach ($matches[1] as $k => $n) {
            $str = str_replace($matches[0][$k], '&#' . hexdec($n) . ';', $str);
        }

        return $str;
    }

    /**
     * Trasforma le entità HTML in entità Unicode in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function HTML2Unicode($str) {
        $str = self::HTMLDecodeEntities($str);

        /*
         * Converto i valori decimali in esadecimale.
         */
        preg_match_all('/&#([0-9]{2,3});/i', $str, $matches);
        foreach ($matches[1] as $k => $n) {
            $str = str_replace($matches[0][$k], '&#x' . str_pad(dechex($n), 4, '0', STR_PAD_LEFT) . ';', $str);
        }

        return preg_replace('/&#x([0-9a-f]{4});/i', '%u$1', $str);
    }

    /**
     * Trasforma le entità UTF8 in entità HTML in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function UTF82HTML($str) {
        if ($str == '') {
            return '';
        }

        $tmp = json_encode($str); // Codifica json per convertire i caratteri utf8 in codice numerico
        $tmp = substr($tmp, 1, -1); // Skip apici iniziali e finali
        return preg_replace('/\\\\u([0-9a-f]{4})/i', '&#x$1;', $tmp); // Conversione da formato numerico ascii a formato html
    }

    /**
     * Trasforma le entità HTML in entità UTF8 in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function HTML2UTF8($str) {
        return self::Unicode2UTF8(self::HTML2Unicode($str));
    }

    /**
     * Trasforma le entità Unicode in entità UTF8 in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function Unicode2UTF8($str) {
        preg_match_all('/%(u[0-9a-f]{4}|[0-9a-f]{2})/i', $str, $matches);
        foreach ($matches[1] as $k => $n) {
            if (strpos($n, 'u') !== false) {
                $str = str_replace($matches[0][$k], self::Code2UTF8(hexdec(substr($n, 1))), $str);
            } else {
                $str = str_replace($matches[0][$k], chr(hexdec($n)), $str);
            }
        }

        return $str;
    }

    /**
     * Trasforma le entità UTF8 in entità Unicode in una data stringa.
     * 
     * @param string $str Stringa con eventuali entità da convertire.
     * @return string
     */
    public static function UTF82Unicode($str) {
        return self::HTML2Unicode(self::UTF82HTML($str));
    }

    private static function Code2UTF8($num) {
        if ($num < 128)
            return chr($num);
        if ($num < 2048)
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        if ($num < 65536)
            return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        if ($num < 2097152)
            return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);

        return '';
    }

}
