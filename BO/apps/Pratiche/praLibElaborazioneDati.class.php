<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibElaborazioneDati {

    private $praLib;
    private $errCode;
    private $errMessage;

    public function __construct() {
        $this->praLib = new praLib;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function ctrCampiRaccoltaDati($controlli, $info) {
        foreach ($controlli as $controllo) {
            switch ($controllo['OPERATORE']) {
                case 'AND':
                    $espressione = $espressione . ' && ';
                    break;
                case 'OR':
                    $espressione = $espressione . ' || ';
                    break;
                default:
                    break;
            }
            $espressione = $espressione . '$info[\'' . $controllo['CAMPO'] . '\']';
            $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
            $espressione = $espressione . '\'' . $controllo['VALORE'] . '\'';
        }
        $espressione = $espressione . '';
        eval('$ret = (' . $espressione . ');');
        return $ret;
    }

    public function controlli($controllo, $valore, $campo, $dagrev) {
        switch ($controllo) {
            case "CodiceFiscale":
                $regExp = "/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/";
                break;
            case "PartitaIva":
                $regExp = "/^[0-9]{11}$/";
                break;
            case "email":
                $regExp = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\.[a-z]{2,}$/i";
                break;
            case "RegularExpression":
                $regExp = $dagrev;
                break;
            case "Numeri":
                $regExp = "/^[0-9]+$/";
                break;
            case "Lettere":
                $regExp = "/^[A-Za-z\à\è\ì\ò\ù]+$/";
                break;
            case "Data":
                $regExp = "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/";
                break;
            case "Importo":
                $regExp = "/^(0|[1-9]\d*)([\.|,]\d{1,2})?$/";
                break;
            case "Iban":
                $regExp = "^IT\d{2}[ ][a-zA-Z]\d{3}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{4}[ ]\d{3}$|^IT\d{2}[a-zA-Z]\d{22}$";
            case 'CodiceFiscalePiva':
                $regExp = '/^([a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]|[0-9]{11})$/';
            default:
                break;
        }

        return preg_match($regExp, $valore) ? true : false;
    }

    /**
     * 
     * @param type $prodag_rec
     * @param array $plainDictionary
     * @return type
     */
    public function elaboraValoreProdag($prodag_rec, $plainDictionary) {
        switch ($prodag_rec['DAGDIZ']) {
            case 'C':
                return $prodag_rec['DAGDEF'];

            case 'A':
            case 'D':
                $defaultKey = $prodag_rec['DAGDEF'];

                if ($prodag_rec['DAGTIC'] == 'Select') {
                    if (strpos($defaultKey, '^' !== false)) {
                        list($optionsKey, $defaultKey) = explode('^', $defaultKey);
                    }
                }

                return $plainDictionary[$defaultKey];

            case 'T':
                $defaultKey = $prodag_rec['DAGDEF'];

                if ($prodag_rec['DAGTIC'] == 'Select') {
                    if (strpos($defaultKey, '^') !== false) {
                        list($optionsKey, $defaultKey) = explode('^', $defaultKey);
                    }
                }

                $defaultValue = $this->elaboraTemplateDefault($defaultKey, $plainDictionary);
                return str_replace("\\n", chr(13), $defaultValue);

            case 'H':
                $defaultValue = $this->elaboraTemplateDefault($prodag_rec['DAGDEF'], $plainDictionary);
                return str_replace("\\n", chr(13), $defaultValue);

            case 'F':
                return 0;
        }
    }

    function elaboraTemplateDefault($template, $dati, $parti_da = false) {
        $return_array = array();

        /*
         * Estraggo tutte le variabili nel formato @{$var}@
         */
        preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_.]*)/', $template, $matches);

        foreach ($matches[1] as $mkey => $match) {
            /*
             * Per ogni match verifico la presenza del dato nell'array $dati
             * (senza limite di livelli)
             */
            $keys = explode('.', $match);

            /*
             * Estraggo l'ultima chiave (la utilizzo in seguito per verificare
             * chiavi nel formato {$var_key}_n)
             */
            $var_key = array_pop($keys);

            $tmp_dati = $dati;

            /*
             * Percorro l'array tramite tutte le $keys tranne l'ultima
             */
            for ($i = 0; $i < count($keys); $i++) {
                if (!isset($tmp_dati[$keys[$i]])) {
                    /*
                     * Se la chiave non esiste, continuo il foreach dei $match
                     */
                    continue 2;
                }

                $tmp_dati = $tmp_dati[$keys[$i]];
            }

            /*
             * Controllo tutte le chiavi all'attuale livello per verificare la
             * presenza di chiavi nel formato key_n
             */
            foreach ($tmp_dati as $key => $value) {
                if (strpos($key, $var_key) === 0 && strlen($key) !== strlen($var_key)) {
                    $idx = substr($key, strlen($var_key));

                    /*
                     * Salto le chiavi che non sono nel formato _n
                     */
                    if (!preg_match('/^_[\d]*$/', $idx)) {
                        continue;
                    }

                    if (!isset($return_array[$idx])) {
                        $return_array[$idx] = $template;
                    }

                    /*
                     * Sostituisco le istanze di $var non seguite da _
                     * con $var_n.
                     * Eseguo il preg_quote per l'escape del carattere $.
                     */
                    $return_array[$idx] = preg_replace('/(' . preg_quote($matches[0][$mkey], '/') . ')(?!_)/', '$1' . $idx, $return_array[$idx]);
                }
            }
        }

        $return_template = count($return_array) ? $return_array : $template;

        /*
         * Rimuovo i passi < di $parti_da
         */
        if (is_array($return_template) && $parti_da !== false) {
            foreach ($return_template as $k => $v) {
                /*
                 * La comparazione è eseguita tra stringhe '_n'...
                 * era inizialmente implementata in questo modo, eventualmente
                 * può essere leggermente modificata
                 */
                if ($k < $parti_da) {
                    unset($return_template[$k]);
                }
            }
        }

        /*
         * Riporto le variabili dal formato '$!var' a '$var'
         * (variabili da non moltiplicare).
         */
        if (is_array($return_template)) {
            foreach ($return_template as $k => &$v) {
                $v = preg_replace('/\$!([a-zA-Z_][a-zA-Z0-9_.]*)/', '$$1', $v);
            }
        } else {
            $return_template = preg_replace('/\$!([a-zA-Z_][a-zA-Z0-9_.]*)/', '$$1', $return_template);
        }

        return $this->valorizzaTemplate($return_template, $dati);
    }

    private function dictionarySimpleToAssociative($dictionaryValues) {
        foreach ($dictionaryValues as $k => $v) {
            $keys = explode('.', $k);
            switch (count($keys)) {
                case 2:
                    $dictionaryValues[$keys[0]][$keys[1]] = $v;
                    break;

                case 3:
                    $dictionaryValues[$keys[0]][$keys[1]][$keys[2]] = $v;
                    break;

                default:
                    continue 2;
            }

            unset($dictionaryValues[$k]);
        }

        return $dictionaryValues;
    }

    function valorizzaTemplate($template, $dictionaryValues) {
        $itaSmarty = new itaSmarty();

        $arrDictionary = $this->dictionarySimpleToAssociative($dictionaryValues);

        foreach ($arrDictionary as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }

        $tmpPath = itaLib::getAppsTempPath();
        $docTemplate = $tmpPath . '/' . App::$utente->getKey('TOKEN') . '-docTemplate.tpl';
        if (!file_put_contents($docTemplate, $template)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore scrittura su $docTemplate");
            return false;
        }

        $templateCompilato = $itaSmarty->fetch($docTemplate);
        unlink($docTemplate);
        return $templateCompilato;
    }

    function ctrRicdagRec($Ricdag_rec, $plainDict) {
        if ($Ricdag_rec['DAGEXPROUT']) {
            $exprOut_tab = unserialize($Ricdag_rec['DAGEXPROUT']);
            foreach ($exprOut_tab as $exprOut_rec) {
                $ret = $this->evalExpression($plainDict, $exprOut_rec['EXPCTR']);
                if ($ret == true) {
                    $Ricdag_rec['DAGDIZ'] = $exprOut_rec['ITDDIZ'];
                    $Ricdag_rec['DAGVAL'] = $Ricdag_rec['DAGVAL'];
                    $Ricdag_rec['DAGDEF'] = $exprOut_rec['ITDVAL'];
                    $Ricdag_rec['DAGROL'] = $exprOut_rec['ITDROL'];
                    //
                    // Nuove variabili
                    //
                    $Ricdag_rec['DAGLAB'] = $exprOut_rec['ITDLAB'];
                    $Ricdag_rec['DAGTIC'] = $exprOut_rec['ITDTIC'];
                    $Ricdag_rec['DAGVCA'] = $exprOut_rec['ITDVCA'];
                    $Ricdag_rec['DAGFIELDERRORACT'] = $exprOut_rec['ITDFIELDERRORACT'];
                    $Ricdag_rec['DAGREV'] = $exprOut_rec['ITDREV'];
                    $Ricdag_rec['DAGLEN'] = $exprOut_rec['ITDLEN'];
                    $Ricdag_rec['DAGDIM'] = $exprOut_rec['ITDDIM'];
                    $Ricdag_rec['DAGACA'] = $exprOut_rec['ITDACA'];
                    $Ricdag_rec['DAGPOS'] = $exprOut_rec['ITDPOS'];
                    $Ricdag_rec['DAGLABSTYLE'] = $exprOut_rec['ITDLABSTYLE'];
                    $Ricdag_rec['DAGFIELDSTYLE'] = $exprOut_rec['ITDFIELDSTYLE'];
                    $Ricdag_rec['DAGFIELDCLASS'] = $exprOut_rec['ITDFIELDCLASS'];
                    $Ricdag_rec['DAGMETA'] = serialize($exprOut_rec['ITDMETA']);
                    //$Ricdag_rec['DAGMETA'] = serialize($exprOut_rec['ATTRIBUTICAMPO']);
                    break;
                }
            }
        }

        return $Ricdag_rec;
    }

    function evalExpression($raccolta, $serExpression) {
        $espressione = '';
        $controlli = unserialize($serExpression);
        if (!$controlli) {
            return true;
        }
        foreach ($controlli as $controllo) {
            switch ($controllo['OPERATORE']) {
                case 'AND':
                    $espressione = $espressione . ' && ';
                    break;
                case 'OR':
                    $espressione = $espressione . ' || ';
                    break;
                default:
                    break;
            }

            /*
             * Nuova tipologia di condizione
             */
            if (isset($controllo['TIPOCAMPO']) && isset($controllo['TIPOVALORE'])) {
                $valore1 = $valore2 = '';

                switch ($controllo['TIPOCAMPO']) {
                    case 'V':
                        $valore1 = "'" . $controllo['CAMPO'] . "'";
                        break;

                    case 'D':
                        $valore1 = "\$raccolta['" . $controllo['CAMPO'] . "']";
                        break;

                    case 'C':
                        $valore1 = $controllo['CAMPO'];
                        break;

                    case 'T':
                        $valore1 = $this->valorizzaTemplate($controllo['CAMPO'], $raccolta) ? 'true' : 'false';
                        break;
                }

                switch ($controllo['TIPOVALORE']) {
                    case 'V':
                        $valore2 = "'" . $controllo['VALORE'] . "'";
                        break;

                    case 'D':
                        $valore2 = "\$raccolta['" . $controllo['VALORE'] . "']";
                        break;

                    case 'C':
                        $valore2 = $controllo['VALORE'];
                        break;
                }

                $espressione = $espressione . "$valore1 {$controllo['CONDIZIONE']} $valore2";
                continue;
            }

            if (substr($controllo['CAMPO'], 0, 1) === '#') {
                $controllo['CAMPO'] = substr($controllo['CAMPO'], 1);
                $espressione = $espressione . $controllo['CAMPO'];
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . $controllo['VALORE'];
            } else {
                $espressione = $espressione . '$raccolta[\'' . $controllo['CAMPO'] . '\']';
                $espressione = $espressione . ' ' . $controllo['CONDIZIONE'] . ' ';
                $espressione = $espressione . '\'' . $controllo['VALORE'] . '\'';
            }
        }

        $espressione = $espressione . '';
        eval('$ret = (' . $espressione . ');');
        return $ret;
    }

    public function CheckValiditaPasso($Riccontrolli_tab, $raccolta) {
        $arrayEsiti = array();
        foreach ($Riccontrolli_tab as $Riccontrolli_rec) {
            $ret = $this->evalExpression($raccolta, $Riccontrolli_rec['ESPRESSIONE']);
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['ESITO'] = $ret;
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['MESSAGGIO'] = $Riccontrolli_rec['MESSAGGIO'];
            $arrayEsiti[$Riccontrolli_rec['SEQUENZA']]['AZIONE'] = $Riccontrolli_rec['AZIONE'];
            if ($ret === false && $Riccontrolli_rec['AZIONE'] == 2) {
                break;
            }
        }
        $msg = "";
        foreach ($arrayEsiti as $esito) {
            if ($esito['ESITO'] === false) {
                $msg .= $esito['MESSAGGIO'] . "<br>";
            }
        }
        return $msg;
    }

    /**
     * 
     * @param type $Propas_rec
     * @param type $dictionary
     * @return array
     */
    public function getPassoDestinazione($Propas_rec, $dictionary = null) {

        $arrDictionaty = array();
        $Propak_destinazione = false;

        if (is_array($dictionary)) {
            $arrDictionaty = $dictionary;
        } elseif (is_a($dictionary, 'itaDictionary')) {
            $arrDictionaty = $dictionary->getAllDataPlain('', '.');
        }

        $Propas_dest_rec = array();
        //switch ($Propas_dest_rec['PROQST']) {
        switch ($Propas_rec['PROQST']) {
            /*
             * VAI A PASSO O PROSSIMA SEQUENZA
             * 
             */
            case 0:
                if ($Propas_rec['PROVPA']) {
                    return $Propas_rec['PROVPA'];
                }
                break;

            /*
             * Domanda Semplice passo si / passo no
             * 
             */
            case 1:
                switch (strtolower($Propas_rec['PRORIS'])) {
                    case 'si':
                        return $Propas_rec['PROVPA'];
                    case 'no':
                        return $Propas_rec['PROVPN'];
                }
                break;
            /*
             * Salti con espressione
             *  
             */
            case 2:

                $Provpa_tab = $this->praLib->GetProvpadett($Propas_rec['PROPAK']);
                if (!$Provpa_tab) {
                    return false;
                }

                foreach ($Provpa_tab as $Provpa_rec) {
                    if (!$Provpa_rec['PROEXPRVPA']) {
                        return $Provpa_rec['PROVPA'];
                    }
                    $ret = $this->evalExpression($arrDictionaty, $Provpa_rec['PROEXPRVPA']);
                    if ($ret === true) {
                        $Propak_destinazione = $Provpa_rec['PROVPA'];
                        break;
                    }
                }
        }
        return $Propak_destinazione;
    }

    /**
     * 
     * @param type $Propas_rec
     * @param type $dictionary
     * @return array
     */
    public function getPassoPrecedente($Propak_attuale, $dictionary = null) {
        $Propak_precedente = false;

        $propas_rec = $this->praLib->GetPropas($Propak_attuale, 'provpa');
        if (!$propas_rec) {
            $propas_rec = $this->praLib->GetPropas($Propak_attuale, 'provpn');
        }

        if (!$propas_rec) {
            $provpadett_rec = $this->praLib->GetProvpadett($Propak_attuale, 'provpa', false);
            if ($provpadett_rec) {
                $Propak_precedente = $provpadett_rec['PROPAK'];
            }
        } else {
            $Propak_precedente = $propas_rec['PROPAK'];
        }

        if (!$Propak_precedente) {
            $this->setErrCode(-1);
            $this->setErrMessage('Non trovato record di PROPASFATTI precedente al passo corrente');

            return $Propak_attuale;
        }



        return $Propak_precedente;
    }

}
