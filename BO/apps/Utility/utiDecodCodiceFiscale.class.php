<?php

class utiDecodCodiceFiscale {

    var $_mesi = array(1 => "A", 2 => "B", 3 => "C", 4 => "D", 5 => "E", 6 => "H", 7 => "L", 8 => "M", 9 => "P", 10 => "R", 11 => "S", 12 => "T");

    function DecodCF($cf_tmp) {
        $cf = strtoupper($cf_tmp);
        $er = 0;
        if (strlen($cf) != 16)
            $er = 1;
        //
        if (!is_numeric(substr($cf, 6, 1)))
            $er = 1;
        if (!is_numeric(substr($cf, 7, 1)))
            $er = 1;
        //
        $mm = 0;
        $mm = array_search(substr($cf, 8, 1), $this->_mesi);
        if ($mm == 0)
            $er = 1;
        //
        if (!is_numeric(substr($cf, 9, 1)))
            $er = 1;
        if (!is_numeric(substr($cf, 10, 1)))
            $er = 1;
        //
        $result = array();
        if ($er == 0) {
            $mm = str_pad($mm, 2, '0', STR_PAD_LEFT);
            $gg = substr($cf, 9, 2);
            if ($gg <= 31) {
                $sex = "M";
            } else {
                $sex = "F";
                $gg = $gg - 40;
            }
            $X_a = substr($cf, 6, 2);
            $Xx_aa = substr(App::$utente->getKey('DataLavoro'), 2, 2);
            if ($X_a > $Xx_aa) {
                $aa = (substr(App::$utente->getKey('DataLavoro'), 0, 2) - 1) * 100 + $X_a;
            } else {
                $aa = substr(App::$utente->getKey('DataLavoro'), 0, 2) * 100 + $X_a;
            }
            $belfiore = substr($cf, 11, 4);
            $result['datanascita'] = $aa . $mm . $gg;
            $result['sesso'] = $sex;
            $result['belfiore'] = $belfiore;
        }
        $result['errore'] = $er;
        return $result;
    }

}

?>
