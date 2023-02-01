<?php

//use Out;

/**
 *
 * Utility Code Cityware (Modulo B)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Stefano Guidetti <s.guidetti@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibCode {

    /**
     * Restituisce un array con i dati della form
     * @param iso: 1 se è formato ISO YYYY-MM-DD, se 0 è formato ITA DD-MM-AAAA
     * @param e formName
     * @return string arrayData di tipo array
     */
    public static function getCurrentDate($iso = 0) {
        if ($iso == 1) {
            return date('Y-m-d');
        } else {
            return date('d-m-Y');
        }
    }

    //Questo in più restituisce l'indice
//    public function searchArrayMultiIndex($search_value, $key_to_search, $arrayValue) {
//        foreach ($arrayValue as $key => $valueLev1) {
//            $ret = self::searchArrayMulti($search_value, $key_to_search, $valueLev1);
//            if ($ret) {
//                $indexFind[] = $key;
//            }
//        }
//        return $indexFind;
//    }

    public static function searchArrayMultiIndex($search_value, $key_to_search, $arrayValue) {
        if (is_array($arrayValue)) {
            $strSearch_value = strval($search_value);
            foreach ($arrayValue as $key => $cur_value) {
                $strkey_to_search = strval($cur_value[$key_to_search]);
                if (strcmp($strkey_to_search, $strSearch_value) == 0) {
//                if ($cur_value[$key_to_search] == $search_value) {
                    return $key;
                } else {
                    $key = self::searchArrayMultiIndex($search_value, $key_to_search, $cur_value);
                    if (is_array($cur_value) && $key !== NULL) {
                        return $key;
                    }
                }
            }
        }
        return NULL;
    }

    //Di un array multilivello restituisce true o false a seconda che trovi o meno il valore nel tag indicato
    public static function searchArrayMulti($search_value, $key_to_search, $arrayValue) {
        if (is_array($arrayValue)) {
            $strSearch_value = strval($search_value);
            foreach ($arrayValue as $key => $cur_value) {
                $strkey_to_search = strval($cur_value[$key_to_search]);
//                if ($cur_value[$key_to_search] == $search_value) {
                if (strcmp($strkey_to_search, $strSearch_value) == 0) {
                    return true;
                } else {
                    if (is_array($cur_value) && self::searchArrayMulti($cur_value, $search_value)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

//}
//        $keys = array();
//    foreach ($arrayValue as $key => $cur_value) {
//        if ($cur_value[$key_to_search] === $search_value) {
//            if (isset($other_matching_key) && isset($other_matching_value)) {
//                if ($cur_value[$other_matching_key] === $other_matching_value) {
//                    $keys[] = $key;
//                }
//            } else {
//                // I must keep in mind that some searches may have multiple
//                // matches and others would not, so leave it open with no continues.
//                $keys[] = $key;
//            }
//        }
//    }
//    return $keys;
    //}

    /**
     * Effettua la search su in array multidimensionale (per PHP 5.3)
     * @param valore, chiave dell'array e array
     * @return la chiave del primo trovato
     * Equivalente di $results = searcharray('searchvalue', searchkey, $array); (per PHP 5.5 o sup)
     */
    public static function searchArray($value, $key, $array) {
        $strValue = strval($value);
        foreach ($array as $k => $val) {
            $strValKey = strval($val[$key]);
            if (strcmp($strValue, $strValKey) == 0) {
//            if ($val[$key]==$value) {
                return $k;
            }
        }
        return false;
    }

    /**
     * Effettua la search per chiave su in array multidimensionale (per PHP 5.3)
     * @param chiave da cercare nell'array e array
     * @return la chiave true o false
     */
    public static function searchKeyArrays($array, $keySearch) {
        $strKeySearch = strval($keySearch);
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $key => $item) {
            $strKey = strval($key);
//            if ($keySearch == $key) { //Fallisce se (int) 0 == (string) env:Fault, perchè la stringa inizia per e,E,.,, quindi viene fatto un cast errato
            if (strcmp($strKey, $strKeySearch) == 0) { //Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal. 
                return true;
            } else {
                if (is_array($item) && self::searchKeyArrays($item, $keySearch)) {
                    return true;
                }
            }
        }
        return false;
    }

// Creato da Silvia Rivi il 23/01/2017
// Questo metodo data la row e il nome iniziale dei campi ed il 
// Form imposta i valori a video se ovviamente i nomi della row sono gli stessi 
// indicati nel form esempio ROW[COGNOME]  FORM-->COGNOME text->ita-edit

    public static function OutgetRow($RowData, $IniNome, $currentForm) {
        foreach ($RowData as $key => $value) {
            $nomecampo = strtoupper(substr($key, strpos($key, $IniNome)));
            if (!cwbLibCheckInput::IsNBZ($value)) {
                Out::valore($currentForm . '_' . $nomecampo, $value);
            } else {
                Out::valore($currentForm . '_' . $nomecampo, '');
            }
        }
    }

// Creato da Silvia Rivi il 23/01/2017
// Questo metodo dato il $formData ritorna una row con i dati indicati a video.
// $IniNome il nome con cui iniziano gli oggetti a video esempio: _ric
// $currenForm identifica il form con i dati esempio cwdDanRicerca
//$RowData identifica la Row che viene popolata
    public static function InsetRow($formData, $IniNome, $currentForm) { //, $grid = false
        $aggiungi = strlen($IniNome);
        foreach ($formData as $key => $value) {
            if (strpos($key, $currentForm . $IniNome) !== false) {
                $nomecampo = str_replace($currentForm . '_', "", $key);
                if (strpos($nomecampo, '_grid') > 0) {
//                    if ($grid == true) { //in questo caso considero la griglia perchè serve 
//                        $RowData[$nomecampo] = $value;
//                    }
                    //Significa che è una griglia quindi questo valore non lo utilizzo nella row
                } else {
                    //                if (!cwbLibCheckInput::IsNBZ($value)) {
                    $RowData[$nomecampo] = $value;
//                }
                }
            }
        }
        return $RowData;
    }

// Creato da Silvia Rivi il 16/03/2017
// Questo metodo dato il Form ed il $formData imposta tutti i campi a disabilitati, 
// tranne quelli passati in ingresso  
// indicati nel form esempio FORM-->COGNOME text->ita-edit

    public static function OutdisableEnable($rowCampi, $formData, $currentForm) {
        foreach ($formData as $value) {
            $nomecampo = $value;
            foreach ($rowCampi as $value2) {
                if ($nomecampo == $value2) {
                    Out::enableField($currentForm . '_' . $nomecampo);
                    break;
                } else {
                    Out::disableField($currentForm . '_' . $nomecampo);
                }
            }
        }
    }

    // Creato da Silvia Rivi il 17/03/2017
// Questo metodo dato il Form ed il $formData imposta tutti i campi a disabilitati, 
// indicati nel form esempio FORM-->COGNOME text->ita-edit
    public static function disabilitaCampi($formData, $currentForm) {
        foreach ($formData as $value) {
            $nomecampo = $value;
            Out::disableField($currentForm . '_' . $nomecampo);
        }
    }

    // Creato da Silvia Rivi il 17/03/2017
// Questo metodo dato il Form ed il $formData imposta tutti i campi ad abilitati, 
// indicati nel form esempio FORM-->COGNOME text->ita-edit
    public static function abilitaCampi($formData, $currentForm) {
        foreach ($formData as $value) {
            $nomecampo = $value;
            Out::enableField($currentForm . '_' . $nomecampo);
        }
    }

    /*
     * Pi 
     * $key
     * $value
     * $pos
     * $arr
     */

    function moveElement($key, $value, $pos, $arr) {
        $new_arr = array();
        $i = 1;

        foreach ($arr as $arr_key => $arr_value) {
            if ($i == $pos)
                $new_arr[$key] = $value;

            $new_arr[$arr_key] = $arr_value;

            ++$i;
        }

        return $new_arr;
    }

    //Creato da Gina il 21.02.2018 per valorizzare una combo a video
    public static function carica_combo($nameObject, $array, $cod, $desc, $addLineEmpty = false) {
        //To Do Aggiungere il parametro per impostare la riga corrente?
        if ($addLineEmpty == true) {
            Out::select($nameObject, 1, 0, 1, '');
        } else {
            
        }

        foreach ($array as $key => $value) {
            if ($key == 0) {
                Out::select($nameObject, 1, $value[$cod], $addLineEmpty == true ? 0 : 1, $value[$desc]);
            } else {
                Out::select($nameObject, 1, $value[$cod], 0, $value[$desc]);
            }
        }
    }

    public function openDiacriticChars($returnField) {
        $model = 'utiDiacriticKeyboard';

        //Non funziona perchè richiama metodi non implementati tipo setReturnNameForm
//        $utiDiacriticKeyboard = cwbLib::apriFinestra($model,$ownerNameForm,$returnEvent, $returnField);
//        $utiDiacriticKeyboard->setStartValue($_POST[$returnField]); 

        itaLib::openForm($model);
        /* @var $utiDiacriticKeyboard utiDiacriticKeyboard */
        $utiDiacriticKeyboard = itaModel::getInstance($model);
        $utiDiacriticKeyboard->setNameForm($model);
        $utiDiacriticKeyboard->setReturnInput($returnField);
        $utiDiacriticKeyboard->setStartValue($_POST[$returnField]);
        $utiDiacriticKeyboard->setEvent('openform');
        $utiDiacriticKeyboard->parseEvent();
    }

    public function decriptDiacriticString($nameField, $value) {
        Out::codice("document.getElementById('$nameField').value = unescape('$value');");
    }

    public function translateDia($source, $out = 0) {
        //La stringa tipo LÃ\u008fNDÃ\u0089NBERG non va bene, deve avere i % (L%u00cfND%u00c9NBERG) o essere scritta in chiaro
        $encode = mb_detect_encoding($source, 'UTF-8, ISO-8859-1');
        switch ($out) {
            case 0: //OUT UNICODE
                switch ($encode) {
                    case 'UTF-8':
                        $dia = itaDiacriticChars::UTF82Unicode($source); //LÃ\u008fNDÃ\u0089NBERG -->L%u00cfND%u00c9NBERG 
                        break;
                    case 'ISO-8859-1':
                        $dia = $source; //LÏNDÉNBERG --> LÏNDÉNBERG
                        break;

                    default:
                        break;
                }
                break;
            case 1: //OUT HTML (x grid)
                switch ($encode) {
                    case 'UTF-8':
                        $dia = itaDiacriticChars::UTF82HTML($source); //LÃ\u008fNDÃ\u0089NBERG --> &A..
                        break;
                    case 'ISO-8859-1':
                    default:
                        $dia = itaDiacriticChars::Unicode2HTML($source); //L%u00cfND%u00c9NBERG --> &A..
                        break;
                }
                break;
            default:
                break;
        }

        return $dia;
    }

    public function translitterateString($source) {

        $encode = mb_detect_encoding($source, 'UTF-8, ISO-8859-1');
        switch ($encode) {
            case 'UTF-8':
                $transliterated = itaDiacriticChars::UTF82Transliterated($source);
                break;
            case 'ISO-8859-1':
            default:
                $transliterated = itaDiacriticChars::Unicode2Transliterated($source);
                break;
        }
        return $transliterated;
    }

    /**
     * @param iso: 1 se è formato hh:ii:ss, 
     * @return string del time
     */
    public static function getCurrentTime($format = '%H:%M:%S') {
        return strftime($format, time());
    }

}

?>