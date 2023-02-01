<?php

class itaSecurity {

    static private $list_XSS_keywords_patterns = array(
        "/<SCRIPT\b[^>]*>/i",
        '/[\s]onload[\s]*=[\s]*/i',
        '/[\s]onabort[\s]*=[\s]*/i',
        '/[\s]onauxclick[\s]*=[\s]*/i',
        '/[\s]onbeforecopy=[\s]*=[\s]*/i',
        '/[\s]onbeforecut[\s]*=[\s]*/i',
        '/[\s]onbeforepaste[\s]*=[\s]*/i',
        '/[\s]onblur[\s]*=[\s]*/i',
        '/[\s]oncancel[\s]*=[\s]*/i',
        '/[\s]oncanplay[\s]*=[\s]*/i',
        '/[\s]oncanplaythrough[\s]*=[\s]*/i',
        '/[\s]onchange[\s]*=[\s]*/i',
        '/[\s]onclick[\s]*=[\s]*/i',
        '/[\s]onclose[\s]*=[\s]*/i',
        '/[\s]oncontextmenu[\s]*=[\s]*/i',
        '/[\s]oncopy[\s]*=[\s]*/i',
        '/[\s]oncuechange[\s]*=[\s]*/i',
        '/[\s]oncut[\s]*=[\s]*/i',
        '/[\s]ondblclick[\s]*=[\s]*/i',
        '/[\s]ondrag[\s]*=[\s]*/i',
        '/[\s]ondragend[\s]*=[\s]*/i',
        '/[\s]ondragenter[\s]*=[\s]*/i',
        '/[\s]ondragleave[\s]*=[\s]*/i',
        '/[\s]ondragover[\s]*=[\s]*/i',
        '/[\s]ondragstart[\s]*=[\s]*/i',
        '/[\s]ondrop[\s]*=[\s]*/i',
        '/[\s]ondurationchange[\s]*=[\s]*/i',
        '/[\s]onemptied[\s]*=[\s]*/i',
        '/[\s]onended[\s]*=[\s]*/i',
        '/[\s]onerror[\s]*=[\s]*/i',
        '/[\s]onfocus[\s]*=[\s]*/i',
        '/[\s]onfullscreenchange[\s]*=[\s]*/i',
        '/[\s]onfullscreenerror[\s]*=[\s]*/i',
        '/[\s]ongotpointercapture[\s]*=[\s]*/i',
        '/[\s]oninput[\s]*=[\s]*/i',
        '/[\s]oninvalid[\s]*=[\s]*/i',
        '/[\s]onkeydown[\s]*=[\s]*/i',
        '/[\s]onkeypress[\s]*=[\s]*/i',
        '/[\s]onkeyup[\s]*=[\s]*/i',
        '/[\s]onloadeddata[\s]*=[\s]*/i',
        '/[\s]onloadedmetadata[\s]*=[\s]*/i',
        '/[\s]onloadstart[\s]*=[\s]*/i',
        '/[\s]onlostpointercapture[\s]*=[\s]*/i',
        '/[\s]onmousedown[\s]*=[\s]*/i',
        '/[\s]onmouseenter[\s]*=[\s]*/i',
        '/[\s]onmouseleave[\s]*=[\s]*/i',
        '/[\s]onmousemove[\s]*=[\s]*/i',
        '/[\s]onmouseout[\s]*=[\s]*/i',
        '/[\s]onmouseover[\s]*=[\s]*/i',
        '/[\s]onmouseup[\s]*=[\s]*/i',
        '/[\s]onmousewheel[\s]*=[\s]*/i',
        '/[\s]onpaste[\s]*=[\s]*/i',
        '/[\s]onpause[\s]*=[\s]*/i',
        '/[\s]onplay[\s]*=[\s]*/i',
        '/[\s]onplaying[\s]*=[\s]*/i',
        '/[\s]onpointercancel[\s]*=[\s]*/i',
        '/[\s]onpointerdown[\s]*=[\s]*/i',
        '/[\s]onpointerenter[\s]*=[\s]*/i',
        '/[\s]onpointerleave[\s]*=[\s]*/i',
        '/[\s]onpointermove[\s]*=[\s]*/i',
        '/[\s]onpointerout[\s]*=[\s]*/i',
        '/[\s]onpointerover[\s]*=[\s]*/i',
        '/[\s]onpointerup[\s]*=[\s]*/i',
        '/[\s]onprogress[\s]*=[\s]*/i',
        '/[\s]onratechange[\s]*=[\s]*/i',
        '/[\s]onreset[\s]*=[\s]*/i',
        '/[\s]onresize[\s]*=[\s]*/i',
        '/[\s]onscroll[\s]*=[\s]*/i',
        '/[\s]onsearch[\s]*=[\s]*/i',
        '/[\s]onseeked[\s]*=[\s]*/i',
        '/[\s]onseeking[\s]*=[\s]*/i',
        '/[\s]onselect[\s]*=[\s]*/i',
        '/[\s]onselectionchange[\s]*=[\s]*/i',
        '/[\s]onselectstart[\s]*=[\s]*/i',
        '/[\s]onstalled[\s]*=[\s]*/i',
        '/[\s]onsubmit[\s]*=[\s]*/i',
        '/[\s]onsuspend[\s]*=[\s]*/i',
        '/[\s]ontimeupdate[\s]*=[\s]*/i',
        '/[\s]ontoggle[\s]*=[\s]*/i',
        '/[\s]onvolumechange[\s]*=[\s]*/i',
        '/[\s]onwaiting[\s]*=[\s]*/i',
        '/[\s]onwebkitfullscreenchange[\s]*=[\s]*/i',
        '/[\s]onwebkitfullscreenerror[\s]*=[\s]*/i',
        '/[\s]onwheel[\s]*=[\s]*/i'
    );
    static private $list_SQL_keywords_patterns = array(
        /*
         * Funzioni
         */
        "/[\s(,]+CONCAT[\s]*[(]/i",
        "/[\s(,]+GROUP_CONCAT[\s]*[(]/i",
        "/[\s(,]+SUBSTRING[\s]*[(]/i",
        "/[\s(,]+MID[\s]*[(]/i",
        "/[\s(,]+ORD[\s]*[(]/i",
        "/[\s(,]+LEFT[\s]*[(]/i",
        "/[\s(,]+STRCMP[\s]*[(]/i",
        "/[\s(,]+SUBSTR[\s]*[(]/i",
        "/[\s(,]+POSITION[\s]*[(]/i",
        "/[\s(,]+LOCATE[\s]*[(]/i",
        "/[\s(,]+REVERSE[\s]*[(]/i",
        "/[\s(,]+UNHEX[\s]*[(]/i",
        "/[\s(,]+HEX[\s]*[(]/i",
        "/[\s(,]+ASCII[\s]*[(]/i",
        "/[\s(,]+BENCHMARK[\s]*[(]/i",
        "/[\s(,]+SLEEP[\s]*[(]/i",
        "/[\s(,]+IFNULL[\s]*[(]/i",
        "/[\s(,]+ISNULL[\s]*[(]/i",
        "/[\s(,]+CAST[\s]*[(]/i",
        "/[\s(,]+COUNT[\s]*[(]/i",
        "/[\s(,]+CURRENT_USER[\s]*[(]/i",
        "/[\s(,]+SESSION_USER[\s]*[(]/i",
        "/[\s(,]+SYSTEM_USER[\s]*[(]/i",
        "/[\s(,]+USER[\s]*[(]/i",
        "/[\s(,]+DATABASE[\s]*[(]/i",
        "/[\s(,]+TIMESTAMPADD[\s]*[(]/i",
        "/[\s(,]+MD5[\s]*[(]/i",
        "/[\s(,]+SLEEP[\s]*[(]/i",
        "/[\s(,]+FLOOR[\s]*[(]/i",
        "/[\s(,]+RAND[\s]*[(]/i",
        "/[\s(,]+EXTRACTVALUE[\s]*[(]/i",
        "/[\s(,]+UPDATEXML[\s]*[(]/i",
        "/[\s(,]+ELT[\s]*[(]/i",
        "/[\s(,]+MAKE_SET[\s]*[(]/i",
        "/[\s(,]+PROCEDURE[\s]+ANALYSE[\s]*[(]/i",
        "/[\s(,]+IF[\s]*[(]/i",
        /*
         * Comandi mySql
         */
        "/(^|[\s(])SELECT[\x60\s*]/i",
        "/(^|[\s(])UPDATE[\x60\s*]/i",
        "/(^|[\s(])DELETE[\s]+FROM[\x60\s*]/i",
        "/(^|[\s(])INSERT[\s]+INTO[\x60\s*]/i",
        "/(^|[\s(])SHOW[\s]+VARIABLES/i",
        "/(^|[\s(])SHOW[\s]+TABLES/i",
        "/(^|[\s(])SHOW[\s]+DATABASES/i",
        "/(^|[\s(])DROP[\s]+DATABASE/i",
        "/(^|[\s(])DROP[\s]+TABLE/i",
        "/(^|[\s(])DROP[\s]+COLUMN/i",
        "/(^|[\s(])TRUNCATE[\s]+TABLE/i",
        "/(^|[\s(])ALTER[\s]+TABLE/i",
        "/(^|[\s(])CASE[\s]+WHEN/i",
        "/(^|[\s(])DELETE[\s]+FROM[\x60\s*]/i",
        /*
         * Clausole
         */
        "/[\x60\s)'\"]ORDER[\s]+BY[\x60\s('\"]/i",
        "/[\x60\s)'\"]GROUP[\s]+BY[\x60\s('\"]/i",
        "/[\x60\s)'\"]AND[\x60\s('\"]/i",
    );
    static private $list_XSS_keywords = array(
    );
    static private $list_SQL_keywords = array(
        'LOAD_FILE',
        'INFORMATION_SCHEMA'
    );

    public static function search_injection_patterns($value = null, $add_keywords = array(), $new_keywords = array()) {

        $value = preg_replace(array_merge(self::$list_XSS_keywords_patterns, self::$list_SQL_keywords_patterns), ".", $value, -1, $j);
        if ($j) {
            return $j;
        }


        $keywords = array();
        $list_keywords = array();
        if (isset($value)) {
            if ($new_keywords) {
                $list_keywords = $new_keywords;
            } else {
                $plus_keywords = array();
                if ($add_keywords) {
                    $plus_keywords = $add_keywords;
                }
                $keywords = array_replace_recursive(array_merge(self::$list_SQL_keywords, self::$list_XSS_keywords), $plus_keywords);
            }
            $value = str_ireplace($keywords, ".", $value, $i);
            return $i;
        } else {
            return 0;
        }
    }

}
