<?php

class utiCodiceFiscale {

    var $_consonanti = array("B", "C", "D", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "V", "W", "X", "Y", "Z");
    var $_vocali = array("A", "E", "I", "O", "U");
    var $_mesi = array(1 => "A", 2 => "B", 3 => "C", 4 => "D", 5 => "E", 6 => "H", 7 => "L", 8 => "M", 9 => "P",
        10 => "R", 11 => "S", 12 => "T");
    var $_pari = array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
        "A" => 0, "B" => 1, "C" => 2, "D" => 3, "E" => 4, "F" => 5, "G" => 6, "H" => 7, "I" => 8, "J" => 9,
        "K" => 10, "L" => 11, "M" => 12, "N" => 13, "O" => 14, "P" => 15, "Q" => 16, "R" => 17, "S" => 18, "T" => 19,
        "U" => 20, "V" => 21, "W" => 22, "X" => 23, "Y" => 24, "Z" => 25);
    var $_dispari = array(0 => 1, 1 => 0, 2 => 5, 3 => 7, 4 => 9, 5 => 13, 6 => 15, 7 => 17, 8 => 19, 9 => 21,
        "A" => 1, "B" => 0, "C" => 5, "D" => 7, "E" => 9, "F" => 13, "G" => 15, "H" => 17, "I" => 19, "J" => 21,
        "K" => 2, "L" => 4, "M" => 18, "N" => 20, "O" => 11, "P" => 3, "Q" => 6, "R" => 8, "S" => 12, "T" => 14,
        "U" => 16, "V" => 10, "W" => 22, "X" => 25, "Y" => 24, "Z" => 23);
    var $_controllo = array(0 => "A", 1 => "B", 2 => "C", 3 => "D", 4 => "E", 5 => "F", 6 => "G", 7 => "H", 8 => "I", 9 => "J",
        10 => "K", 11 => "L", 12 => "M", 13 => "N", 14 => "O", 15 => "P", 16 => "Q", 17 => "R", 18 => "S", 19 => "T",
        20 => "U", 21 => "V", 22 => "W", 23 => "X", 24 => "Y", 25 => "Z");
    var $CodiceFiscale = "";

    function str_split($uiString, $uiSplit = 1) {
        if (!is_string($uiString))
            return false;
        if (!is_numeric($uiSplit) && $uiSplit < 1)
            return false;
        $len = strlen($uiString);
        $array = array();
        $s = 0;
        $e = $uiSplit;
        while ($s < $len) {
            $e = ($e < $len) ? $e : $len;
            $array[] = substr($uiString, $s, $e);
            $s = $s + $e;
        }
        return ($array);
    }

    // uiType = true  ==> ritorna array consonanti
    // uiType = false ==> ritorna array vocali
    function lettere($uiString, $uiType = true) {
        $uiString = $this->str_split($uiString);
        $haystack = ($uiType) ? $this->_consonanti : $this->_vocali;
        $array = array();

        foreach ($uiString as $needle)
            if (in_array($needle, $haystack))
                $array[] = $needle;

        return($array);
    }

    function clean($uiString, $uiUCase = true) {
        $string = preg_replace("/[^A-Za-z]/i", "", $uiString);
        return ($uiUCase) ? strtoupper($string) : $string;
    }

    function cNomeCognome($uiNomeCognome, $uiType = true) {
        // Chiaramente se ci sono cognomi con pià¹ parti, à¨ necessario rimuovere
        // gli spazi e considerare tutto come un cognome unico.
        $uiNomeCognome = $this->clean($uiNomeCognome);
        $aNomeCognome_len = strlen($uiNomeCognome);
        $aNomeCognome_cod = "";
        if ($aNomeCognome_len < 3) {
            $aNomeCognome_cod = $uiNomeCognome;
            while (strlen($aNomeCognome_cod) < 3)
                $aNomeCognome_cod .= 'X';
            $this->CodiceFiscale .= $aNomeCognome_cod;
            return;
        }
        // Sono necessario 3 caratteri per rappresentare il cognome,e sono la prima
        // la seconda e la terza consonante del cognome.
        $consonanti = $this->lettere($uiNomeCognome, true);
        $consonanti_len = count($consonanti);
        if ($uiType) {
            //---[ CALCOLO DEL COGNOME
            for ($i = 0; $i < 3; $i++)
                if (!empty($consonanti[$i]))
                    $aNomeCognome_cod .= $consonanti[$i];
        }
        else {
            //---[ CALCOLO DEL NOME
            if ($consonanti_len <= 3)
                $aNomeCognome_cod = implode("", $consonanti);
            else {
                for ($i = 0; $i < 4; $i++)
                    if (!empty($consonanti[$i]) && $i != 1)
                        $aNomeCognome_cod .= $consonanti[$i];
            }
        }
        if (strlen($aNomeCognome_cod) < 3) {
            $vocali = $this->lettere($uiNomeCognome, false);
            for ($i = 0; strlen($aNomeCognome_cod) < 3; $i++)
                $aNomeCognome_cod .= $vocali[$i];
        }
        $this->CodiceFiscale .= $aNomeCognome_cod;
    }

    function cDataNascita($uiDataNascita = "19700101", $uiSesso = "M") {
        $aaaa = substr($uiDataNascita, 0, 4);
        $mm = substr($uiDataNascita, 4, 2);
        $giorno = substr($uiDataNascita, 6, 2);
        $anno = substr($aaaa, -2);
        $mese = $this->_mesi[(int) $mm];
        $giorno = (strtoupper($uiSesso) == 'M') ? $giorno : $giorno + 40;
        $this->CodiceFiscale .= $anno . $mese . $giorno;
    }

    function cCodiceCatastale($uiComune, $uiCodProvincia) {
        // Qui va inserito il codice per ottenere il codice catastale,
        // in base al comune e alla provincia.
        //
        // ESEMPIO:
        // --------------------------------------------------------------------
        // $db = new MySQL();
        // sprintf($SQL, "SELECT catastale FROM comuni ".
        //               "WHERE comune = '%s' AND provincia = '%s' ".
        //               "LIMIT 1;",
        //               $uiComune, $uiCodProvincia);
        //
        // $db->query($SQL);
        //  if ( $db->next_record() )
        //	   $this->CodiceFiscale .= strtoupper( $db->get("catastale") );
        //  else
        //	   $this->CodiceFiscale .= "XXXX";
        //
        // $db->close();
        // ---------------------------------------------------------------------
        try {
            $db = ItaDB::DBOpen('COMUNI', '');
            $sql = "SELECT * FROM COMUNI WHERE COMUNE='" . addslashes($uiComune) . "'";
            if ($uiCodProvincia != '') {
                $sql .= " AND PROVIN='$uiCodProvincia'";
            }
            $sql .= " LIMIT 1";
            $Comuni_tab = ItaDB ::DBSQLSelect($db, $sql);
            if (count($Comuni_tab) != 0) {
                $this->CodiceFiscale .= strtoupper($Comuni_tab[0]['NASCIT']);
            } else {
                $this->CodiceFiscale .= "XXXX";
            }
        } catch (Exception $e) {
            Out:: msgStop("Errore", $e->getMessage());
        }

        //    $this->CodiceFiscale .= "FIXME";
        return;
    }

    // A partire dai 15 caratteri alfanumerici ricavati in precedenza, si determina il codice
    // di controllo in base ad un particolare algoritmo, che opera in questo modo: si mettono
    // da una parte i caratteri alfanumerici che si trovano in posizione dispari (1,3, etc..)
    // e da un'altra quelli che si trovano in posizione pari (2, 4, etc.).
    // Fatto questo, i caratteri vengono convertiti in valori numerici utilizzando delle tabelle
    // di conversione ($this->_pari , $this->_dispari)
    function cCodControllo() {
        $codFisc_t = $this->str_split($this->CodiceFiscale);
        $sum = 0;

        for ($i = 1; $i <= count($codFisc_t); $i++) {
            $cifra = $codFisc_t[$i - 1];
            $sum += ($i % 2) ? $this->_dispari[$cifra] : $this->_pari[$cifra];
        }

        // A questo punto, i valori che si ottengono dai caratteri alfanumerici pari e dispari vanno
        // sommati tra di loro, e il risultato va diviso per 26. Il resto della divisione
        // fornira' il codice identificativo, ottenuto da un'ulteriore tabella di conversione
        // ($this->_controllo)
        $sum %= 26;

        $this->CodiceFiscale .= $this->_controllo[
                $sum];
    }

    function Calcola($uiCognome, $uiNome, $uiDataNascita, $uiSesso, $uiComune, $uiCodProvincia) {
        $msg = $this->Controlli($uiCognome, $uiNome, $uiDataNascita, $uiSesso, $uiComune, $uiCodProvincia);
        if ($msg) {
            Out::msgInfo("Calcolo Codice Fiscale", "Mancano i seguenti campi:<br>$msg");

            return false;
        }
        $this->CodiceFiscale = "";
        $this->cNomeCognome($uiCognome, true);
        $this->cNomeCognome($uiNome, false);
        $this->cDataNascita($uiDataNascita, $uiSesso);
        $this->cCodiceCatastale($uiComune, $uiCodProvincia);
        $this->cCodControllo();
        return (strlen($this->CodiceFiscale) == 16) ? $this->CodiceFiscale :
                false;
    }

    function Controlli($uiCognome, $uiNome, $uiDataNascita, $uiSesso, $uiComune, $uiCodProvincia) {
        $msg = "";
        if ($uiCognome == "") {
            $msg .= "<br>- <b>Cognome</b><br>";
        }
        if ($uiNome == "") {
            $msg .= "- <b>Nome</b><br>";
        }
        if ($uiDataNascita == "") {
            $msg .= "- <b>Data di Nascita</b><br>";
        }
        if ($uiSesso == "") {
            $msg .= "- <b>Sesso</b>";
        }
        if ($uiComune == "") {
            $msg .= "- <b>Comune di Nascita</b><br>";
        }
//        if ($uiCodProvincia == "") {
//            $msg .= "- <b>Provincia di Nascita</b>";
//        }
        return $msg;
    }

}

?>
