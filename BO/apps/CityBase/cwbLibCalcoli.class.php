<?php

/**
 * Corrispondenza con OBJ_BGE_CALCOLI di Cityware
 */
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCode.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

class cwbLibCalcoli extends cwbLibDB_CITYWARE {

    /**
     * In: Toglie da una stringa tutto quello che non è lettere o cifre o %. 
     * Out: La stringa elaborata ritorna come risultato.
     */
    public static function calcNomeRic($param) {
        $stringa = strtoupper($param);
        for ($index = 0; $index < strlen($stringa); $index++) {
            $char = substr($stringa, $index, 1);
            if (((ord($char) >= 48) && (ord($char) <= 57)) || ((ord($char) >= 65) && (ord($char) <= 90)) || ((ord($char) >= 97) && (ord($char) <= 122)) || (ord($char) == 37)) {
                $stringCompat .= $char;
            }
        }
        return $stringCompat;
    }

    /**
     *  funzione che calcola il PROGRESSIVO MAX di una tabella per impostarlo come DEFAULT
     *  QUESTO METODO RICEVE 4 PARAMETRI IN INGRESSO
     *  par_campo=nome del campo da controllare come MAX
     *  par_tabella=nome della tabella
     *  par_where= la where da concatenare all'istruzione sql.
     *  par_num= è il progressivo: può essere il successivo a quello che c'è sul DB o lo Stesso nel caso della tabella progressivi
     *  Se par_num='S' ---> significa che si vuole lo stesso altrimenti è il successivo */
    public static function trovaProgressivo($par_campo, $par_tabella, $par_where = null, $par_num = null, $db = null, $minVal = 1) {
        $libDB_DTA = new cwdLibDB_DTA();
        if (!$db) {
            $db = $libDB_DTA->getCitywareDB();
        }

        if ($par_where) {
            $sql = "SELECT MAX($par_campo) AS MASSIMO FROM $par_tabella where $par_where";
            $libDB_DTA->leggi($sql, $result);
            if ($par_num === 'S') {
                if (!is_numeric($result['MASSIMO'])) {
                    return $minVal;
                } else {
                    return $result['MASSIMO'];
                }
            } else {
                if (!is_numeric($result['MASSIMO'])) {
                    return $minVal;
                } else {
                    return ++$result['MASSIMO'];
                }
            }
            // }
        } else {
            $sql = "SELECT MAX($par_campo) AS MASSIMO FROM $par_tabella";
            $result = ItaDB::DBSQLSelect($db, $sql, false);
            if ($par_num === 'S') {
                if (!is_numeric($result['MASSIMO'])) {
                    return $minVal;
                } else {
                    return $result['MASSIMO'];
                }
            } else {
                if (!is_numeric($result['MASSIMO'])) {
                    return $minVal;
                } else {
                    return ( ++$result['MASSIMO']);
                }
            }
        }
    }

    /**
     * Reperimento causale tabella DTA_CAUVAR tramite campi di DTA_PERS.
     * @param mixed $cauvar1 Codice causale
     * @param mixed $cauvar2 Codice causale
     * @param mixed $cauvar3 Codice causale
     * @param nome del DB 
     */
    public static function reperisciCausale($cauvar1, $cauvar2, $cauvar3) {
        $sql = "SELECT $cauvar1, $cauvar2, $cauvar3 from DTA_PERS";
        $libDB_Bta = new cwbLibDB_BTA();
        $libDB_Bta->leggi($sql, $result);
        return $result;
    }

    /**
     * Trova tutti i componenti attivi di una determinata famiglia.
     * @param mixed $campo nome chiave tabella
     * @param nome della tabella 
     * @param nome del DB 
     */
    public static function trovaFamiglia($famiglia, $famiglia_t, $tabella, $db) {
        $sql = "SELECT * FROM $tabella WHERE FAMIGLIA=" . $famiglia . " and MOTIVO_C=' ' AND" . " FAMIGLIA_T='" . $famiglia_t . "' ORDER BY RELPAR";
        $result = ItaDB::DBQuery($db, $sql, true);
        return $result;
    }

    /**
     * Assegna progressivo prima di aggiungere un nuovo record.
     * @param mixed $campo nome chiave tabella
     * @param nome della tabella 
     * @param nome del DB 
     * @param valore di confronto 
     */
    public static function Trova_Prog_Diecimila($campo, $tabella, $db, $val_confronto) {
        $sqlParams = array();
        $sqlParams[] = array('name' => 'VAL_CONFRONTO',
            'value' => $val_confronto,
            'type' => PDO::PARAM_INT);

        $sql = "SELECT MAX ($campo) AS MASSIMO FROM $tabella WHERE $campo>:VAL_CONFRONTO";
        $progr = ItaDB::DBQuery($db, $sql, false, $sqlParams);
        if (!$progr) {
            $progr = ++$val_confronto;
        } else {
            $progr = ++$progr['MASSIMO'];
        }
        return $progr;
    }

    /**
     * Assegna progressivo prima di aggiungere un nuovo record.
     * @param mixed $campo nome chiave tabella
     * @param nome della tabella 
     * @param nome del DB 
     * @param valore di confronto 
     */
    public static function Trova_Prog_Min_Diecimila($campo, $tabella, $db, $val_confronto) {
        $sqlParams = array();
        $sqlParams[] = array('name' => 'VAL_CONFRONTO',
            'value' => $val_confronto,
            'type' => PDO::PARAM_INT);

        $sql = "SELECT MAX ($campo) AS MASSIMO FROM $tabella WHERE $campo<:VAL_CONFRONTO";
        $progr = ItaDB::DBQuery($db, $sql, false, $sqlParams);
        if (!$progr) {
            $progr = 1;
        } else {
            $progr = ++$progr['MASSIMO'];
        }
        return $progr;
    }

    /**
     * Estrae anno da una data
     * @param string $dateAsString Data in formato string (yyyy-mm-dd)
     * @$fmt formato della data passata il default è impostato altrimenti viene passato
     * @return int Anno
     */
    public static function estraiAnno($dateAsString, $fmt = "Y-m-d") {
        if (empty($dateAsString)) {
            return null;
        }


        $date = DateTime::createFromFormat($fmt, $dateAsString);
        if ($date == false) { //Per SQL Server che ritorna un DateTime
            $date = DateTime::createFromFormat($fmt, substr($dateAsString, 0, 10));
        }

        return $date->format("Y");

        /*
         * questo funziona solo se la data è successiva al 01-01-1970 quindi non è utilizzabile
         */
//        return $data = date("Y", strtotime($dateAsString)); 
    }

    public static function estraiMese($dateAsString) {
        if (trim($dateAsString . ' ') == '') {
            return 0;
        }
        if (strpos($dateAsString, '+')) { //Rimuovo il fuso orario
            $pos = intval(strpos($dateAsString, '+'));
            $dateAsString = trim(substr($dateAsString, 0, $pos));
        }
        return $data = date("m", strtotime($dateAsString));
    }

    public static function estraiGiorno($dateAsString = '') {
        if (trim($dateAsString . ' ') == '') {
            return 0;
        }
        if (strpos($dateAsString, '+')) { //Rimuovo il fuso orario
            $pos = intval(strpos($dateAsString, '+'));
            $dateAsString = trim(substr($dateAsString, 0, $pos));
        }
        return $data = date("d", strtotime($dateAsString));
    }

    public static function ConvertYear($date) {
        if (is_string($date)) {
            if ($date == null || strlen($date) == 0) {
                return null;
            }
            $toConvert = DateTime::createFromFormat("Y-m-d", $date);
        } else {
            $toConvert = $date;
        }

        return ($toConvert == null || $toConvert->format("Y") < 1900) ? null : $toConvert;
    }

    /*
     * ;  Riceve in ingresso una data con formato AAAA-MM-GG
     * ;  In uscita rende la stessa data in formato GG-MM-AAAA
     */

    public static function InvertiData($originalDate, $format = "d-m-Y") {
        if (!cwbLibCheckInput::IsNBZ($originalDate)) {
            $data = new DateTime($originalDate); //strtotime ha un bug, ovviato con questo
            return $data->format($format);
//        if (!cwbLibCheckInput::IsNBZ($originalDate)) {
//            if (strpos($originalDate, '+')) { //Rimuovo il fuso orario
//                $pos = intval(strpos($originalDate, '+'));
//                $originalDate = trim(substr($originalDate, 0, $pos));
//            }
//            $data = date($format, strtotime($originalDate));
//            if ($data != '01-01-1970') {
//                return $data;
//            } else {
//                /* devo invertire la data con altro metodo perchè il metodo standard non ha funzionato
//                 * Esempio di data che non funziona 2099-12-31
//                 */
//                $arrayDate = explode('-', $originalDate);
//                $date = $arrayDate[2] . '-' . $arrayDate[1] . '-' . $arrayDate[0];
//                return $date;
//            }
        } else {
            return '';
        }
    }

    public static function dataInvertita($originalDate) {
        if (!cwbLibCheckInput::IsNBZ($originalDate)) {
//            return $data = date("Y-m-d", strtotime($originalDate));
            $data = new DateTime($originalDate); //strtotime ha un bug, ovviato con questo
            return $data->format('Y-m-d');
        } else {
            return '';
        }
    }

//  QUESTO METODO RICEVE 1 PARAMETRO IN INGRESSO
//  par_row = riceve la row con i valori per la formattazione
//  par_toponimo, par_desvia, par_numciv, par_subnciv, par_scala,       par_piano,      par_interno
//  ricevono il nome del campo da concatenare nel caso in cui i campi siano diversi dal default
//  Questo metodo restituisce una stringa con la formattazione dell'indirizzo completo
    public static function indirizzo_esteso($par_row, $par_toponimo = 'TOPONIMO', $par_desvia = 'DESVIA', $par_numciv = 'NUMCIV', $par_subnciv = 'SUBNCIV', $par_scala = 'SCALA', $par_piano = 'PIANO', $par_interno = 'INTERNO', $par_des_breve = 'DES_BREVE') {

        if (!empty($par_row[$par_toponimo])) {
            $loc_indirizzo = $par_row[$par_toponimo];
        }
        if (!empty($par_row[$par_desvia])) {
            //            //if (!empty(loc_indirizzo)){
            if (!empty($loc_indirizzo)) {
                $loc_indirizzo .= ' ' . $par_row[$par_desvia];
            } else {
                $loc_indirizzo = $par_row[$par_desvia];
            }
        }
        if (!empty($par_row[$par_numciv])) {
            $loc_indirizzo .= ' n. ' . $par_row[$par_numciv];
        }

//        if (!empty(trim($par_row[$par_subnciv]))) {
        $tmp = trim($par_row[$par_subnciv]);
        if (!empty($tmp)) {
            $loc_indirizzo .= '/' . $par_row[$par_subnciv];
        }

        //  Formato sotto numero
        //$loc_codifica = $tv_Obj_Par_Gen[iv_row_appli][FORM_INT]; //Da correggere anche riga 229 controllo su desbreve
        $loc_codifica = 'Sc.$S p.$P i.$I';
        $loc_con_1 = 0;
        $loc_con_2 = 0;
        $loc_con_3 = 0;


        $loc_s1 = strtok($loc_codifica, '$');
        $loc_codifica = str_replace($loc_s1, '', $loc_codifica);
        $loc_var_1 = substr($loc_codifica, 0, 2);
        $loc_codifica = str_replace($loc_var_1, '', $loc_codifica);
        if (empty($loc_s1)) {
            $loc_con_1 = 1;
        }

        $loc_s2 = strtok($loc_codifica, '$');
        $loc_codifica = str_replace($loc_s2, '', $loc_codifica);
        $loc_var_2 = substr($loc_codifica, 0, 2);
        $loc_codifica = str_replace($loc_var_2, '', $loc_codifica);
        if (empty($loc_s2)) {
            $loc_con_2 = 1;
        }

        $loc_s3 = strtok($loc_codifica, '$');
        $loc_codifica = str_replace($loc_s3, '', $loc_codifica);
        $loc_var_3 = substr($loc_codifica, 0, 2);
        $loc_codifica = str_replace($loc_var_3, '', $loc_codifica);
        if (empty($loc_s3)) {
            $loc_con_3 = 1;
        }

        //  Se è la scala alla stringa aggiungo la scala
        $loc_stringa_deco = '';
        for ($loc_s = 1; $loc_s <= 3; $loc_s++) {
            if (strpos(${'loc_var_' . $loc_s}, '$S') !== false) {
                $loc_scala = ${'loc_var_' . $loc_s};
                if (empty($par_row[$par_scala])) {
                    $num = 'loc_con_' . ($loc_s + 1);
                    if ($$num > 0) {                                               //Variabile variabile prende il valore di loc_con_1
                        ${'loc_s' . ($loc_s + 1)} = ${'loc_s' . $loc_s};
                    }
                } else {
                    $loc_stringa_deco .= ${'loc_s' . $loc_s} . $par_row[$par_scala];     // concateno le strighe
                }
            }
            if (strpos(${'loc_var_' . $loc_s}, '$P') !== false) {
                $loc_piano = ${'loc_var_' . $loc_s};
                if (empty($par_row[$par_piano])) {
                    if (${'loc_con_' . ($loc_s + 1)} > 0) {
                        ${'loc_s' . ($loc_s + 1)} = ${'loc_s' . $loc_s};
                    }
                } else {
                    $loc_stringa_deco .= ${'loc_s' . $loc_s} . $par_row[$par_piano];     // concateno le strighe
                }
            }
            if (strpos(${'loc_var_' . $loc_s}, '$I') !== false) {
                $loc_interno = ${'loc_var_' . $loc_s};
                if (empty($par_row[$par_interno])) {
                    if (${'loc_con_' . ($loc_s + 1)} > 0) {
                        ${'loc_s' . ($loc_s + 1)} = ${'loc_s' . $loc_s};
                    }
                } else {
                    $loc_stringa_deco .= ${'loc_s' . $loc_s} . $par_row[$par_interno];     // concateno le strighe
                }
            }
        }

        $loc_indirizzo .= ' ' . $loc_stringa_deco;

//        if (!empty(loc_indirizzo)&&count($tv_Obj_Par_Gen[iv_lista_enti])>1&&(!empty($par_row[$par_des_breve]))) {    // aggiunto il 16/03/2004
//            $loc_indirizzo = $par_row[$par_des_breve].'  - '. $loc_indirizzo;
//        }

        return $loc_indirizzo;
    }

    public static function indirizzo($par_row, $par_toponimo = 'TOPONIMO', $par_desvia = 'DESVIA', $par_numciv = 'NUMCIV', $par_subnciv = 'SUBNCIV', $par_des_breve = 'DES_BREVE') {
        if (!empty($par_row[$par_toponimo])) {
            $loc_indirizzo = $par_row[$par_toponimo];
        }

        if (!empty($par_row[$par_desvia])) {
            if ($loc_indirizzo <> '') {
                $loc_indirizzo = $loc_indirizzo . ' ' . $par_row[$par_desvia];
            } else {
                $loc_indirizzo = $par_row[$par_desvia];
            }
        }

        if (!empty($par_row[$par_numciv])) {
            $loc_indirizzo = $loc_indirizzo . ' n. ' . $par_row[$par_numciv];
        }
        if (!empty($par_row[$par_subnciv])) {
            $loc_indirizzo = $loc_indirizzo . '/' . $par_row[$par_subnciv];
        }
        if (!empty($loc_indirizzo) && count($tv_Obj_Par_Gen[$iv_lista_enti]) > 0 && !empty($par_row[$par_des_breve])) {     // aggiunto il 16/03/2004
            $loc_indirizzo = $par_row[$par_des_breve] . '  - ' . $loc_indirizzo;
        }

        return $loc_indirizzo;
    }

    public static function formatta_Data($par_gg = 0, $par_mese = 0, $par_anno = 0, $par_par = '/', $par_trattino = false) {
        if ($par_trattino == false) {
            return str_pad($par_gg, 2, '0', STR_PAD_LEFT) . $par_par . str_pad($par_mese, 2, '0', STR_PAD_LEFT) . $par_par . str_pad($par_anno, 4, '0', STR_PAD_LEFT);
        } else {
            if (($par_mese == 0) && ($par_anno > 0)) {
                if ($par_gg == 0) {
                    return '....' . $par_par . '....' . $par_par . str_pad($par_anno, 4, '0', STR_PAD_LEFT);
                } else {
                    return str_pad($par_gg, 2, '0', STR_PAD_LEFT) . $par_par . '....' . $par_par . str_pad($par_anno, 4, '0', STR_PAD_LEFT);
                }
            } else {
                return str_pad($par_gg, 2, '0', STR_PAD_LEFT) . $par_par . str_pad($par_mese, 2, '0', STR_PAD_LEFT) . $par_par . str_pad($par_anno, 4, '0', STR_PAD_LEFT);
            }
        }
    }

    /*
     * passata una stringa e il relativo formato data/ora
     * restituisc la data nel formato indicato
     * default "2017-01-30T12:15:59+01:00" --> "30-01-2017 12:15:59"
     */

    public static function trasformaDataTime($data, $fmtOut = 'd-m-Y H:i:s', $fmtIn = 'Y-m-j\TH:i:s.ue') {
        if (cwbLibCheckInput::IsNBZ($data)) {
            return;
        }
        $date = DateTime::createFromFormat($fmtIn, $data);
        return $date->format($fmtOut);
        //return $date->format('d-m-Y H:i:s');
    }

    public static function getIndirizzo($prognciv, $progint, &$par_indir_est, &$par_indir_int) {
        $libDB_BTA = new cwbLibDB_BTA();
        // Azzeramento variabili 
        $par_indir_est = '';
        $par_indir_int = '';

        if ($prognciv) {
            //Viene ritornata solamente una stringa, nel formato TOPONIMO + VIA + n.CIVICO/SOTTONUMERO
            $loc_row = $libDB_BTA->leggiBtaNciviGetIndirizzoEsternoChiave($prognciv);
            if ($loc_row && $loc_row['PROGNCIV']) {
                $par_indir_est = $loc_row['TOPONIMO'] . ' ' . $loc_row['DESVIA'] . ' n. ' . $loc_row['NUMCIV'];
                if ($loc_row['SUBNCIV']) {
                    $par_indir_est = $par_indir_est . '/' . $loc_row['SUBNCIV'];
                }
            }
        }

        if ($progint) {
            // SCALA/PIANO/INTERNO
            $loc_row = $libDB_BTA->leggiBtaNciviGetIndirizzoInternoChiave($prognciv, $progint);
            if ($loc_row && $loc_row['PROGNCIV'] && $loc_row['PROGINT']) {
                //Composizione della seconda stringa di ritorno
                if ($loc_row['SCALA']) {
                    $par_indir_int = $par_indir_int . ' s. ' . $loc_row['SCALA'];
                }
                if ($loc_row['PIANO']) {
                    $par_indir_int = $par_indir_int . ' p. ' . $loc_row['PIANO'];
                }
                if ($loc_row['INTERNO']) {
                    $par_indir_int = $par_indir_int . ' i. ' . $loc_row['INTERNO'];
                }
            }
        }

        // ritorno tutta la row in modo da averla disponibile se ne ho necessità
        return $loc_row;
    }

    /**
     * Riceve in ingresso due date, le mette a confronto, ritorno true se d1>d2, false se d1<d2
     * $datetime1 
     * $datetime2 
     */
    public static function dateCompare($datetime1, $datetime2, $simbolo = '>') {
        $datetime1 = new DateTime(cwbLibCalcoli::dataInvertita($datetime1));
        $datetime2 = new DateTime(cwbLibCalcoli::dataInvertita($datetime2));
        switch ($simbolo) {
            case '>=':
                eval($simbolo);
                if ($datetime1 >= $datetime2) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                if ($datetime1 > $datetime2) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
    }

    public static function getVoceViario($par_PROGNCIV, $par_CODELEMEN, &$par_row_out, &$par_cod_err, $par_data = null, $par_NUMCIV = null, $par_SUBNCIV = null, $par_CODVIA = null) {
        // LE NOTE SONO IN FONDO AL METODO
        $libDB_Bta = new cwbLibDB_BTA();
        // Selezione via da progressivo viario
        if ($par_PROGNCIV > 0) {
            $loc_row_nciv = $libDB_Bta->leggiBtaNciviChiave($par_PROGNCIV);
            if ($loc_esito <> 0) {
                $par_cod_err = 4;     // COD.ERR. --> Errore SQL su tabella BTA_NCIVI
                Return false;
            }

            $par_CODVIA = $loc_row_nciv['CODVIA'];
            $par_NUMCIV = $loc_row_nciv['NUMCIV'];
            $par_SUBNCIV = $loc_row_nciv['SUBNCIV'];
            $loc_pari_disp = $loc_row_nciv['PARI_DISP'];
        } else {
            // determino se dispari
            if (fmod($par_NUMCIV, 2) <> 0) {
                $loc_pari_disp = 'D';
            } else {
                $loc_pari_disp = 'P';
            }
        }

        // Se la data non viene passata, automaticamente viene messa quella del giorno.
        if (cwbLibCheckInput::IsNBZ($par_data)) {
            $par_data = cwbLibCode::getCurrentDate();
        }

        $filtri['CODELEMEN'] = $par_CODELEMEN;
        $filtri['CODVIA'] = $par_CODVIA;
        $filtri['DATA'] = $par_data;
        $filtri['PARI_DISP'] = $loc_pari_disp;
        $filtri['NUMCIV'] = $par_NUMCIV;
        $filtri['SUBNCIV'] = $par_SUBNCIV;
        $loc_lista_viavoc = $libDB_Bta->leggiBtaViavocElement($filtri, true);
//        if (count($loc_lista_viavoc) == 0) {
//            $par_cod_err = 3;     // COD.ERR. --> Errore SQL su tabella BTA_VIAVOC
//            Return false;
//        }
        // @@@ CONTROLLO SE ESISTE ASSOCIAZIONE VOCE-VIARIO
        if ((count($loc_lista_viavoc) == 0)) {

            // °°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°° aggiunto il 10/09/2004     ;; author: Silvia Rivi
            if ($par_CODELEMEN == 6) {     // controllo se l'elemento ricercato è 6.
                // 1) tramite PROGNCIV aggancio BTA_VIE e trovo PROGENTE
                // 2) da variabile task degli enti installati, prendo il CAP corrispondente alla riga di
                // PROGENTE e il metodo deve restituirlo al pgm chiamante come se la select avesse funzionato.
                $loc_row_vie = $libDB_Bta->leggiBtaVieChiave($par_CODVIA);
                if ($loc_esito == 0) {
                    $listaEnti = cwbParGen::getBorEnti();
                    $key = cwbLibCode::searchArray($loc_row_vie['PROGENTE'], 'PROGENTE', $listaEnti); //Questa ricerca è compatibile con PHP  5.3

                    $par_row_out['DESVOCEEL'] = $listaEnti[$key]['CAP'];
                    $par_row_out['CODELEMEN'] = $par_CODELEMEN;
                } else {
                    $par_cod_err = 5;     // COD.ERR. --> Errore SQL su tabella BTA_VIE
                    Return false;
                }
                // Commento da inserire: "per non obbligare l'utente a caricare tutte le associazioni al
                // viario del CAP (soprattutto per comuni che hanno 1 solo ufficio postale)
                // se non trovo l'associazione, uso il CAP di default sulla tabella dell'ente".
                Return true;
            }

            // Se è <> 6 mi comporto come prima dando errore e chiudendo.
            // °°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°°° aggiunto il 10/09/2004     ;; author: Silvia Rivi
            $par_cod_err = 1;     // COD.ERR. --> Non trovata associazione VOCE-VIARIO
            Return false;
        }

        // @@@@
        // Ricerca la voce
        $filtri['CODELEMEN'] = $par_CODELEMEN;
        $filtri['CODVOCEL'] = $loc_lista_viavoc[0]['CODVOCEL'];
        $par_row_out = $libDB_Bta->LeggiBtaVoci($filtri, false);
        if ($loc_esito == 0) {
            $par_cod_err = 0;     // COD.ERR. --> Nessun errore
            Return true;
        } else {
            $par_cod_err = 2;     // COD.ERR. --> Errore SQL su tabella BTA_VOCI
            Return false;
        }

        // ===========================================================================================================================================================
        // Author:     Massimo Biagioli / A.P.R.A. PROGETTI srl                                                          Date:   13/05/2002
        // ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
        // Purpose: Questo metodo restituisce una row della voce del viario, dato il progressivo del viario e il codice elemento
        // Parameters:
        // par_session_object --> oggetto sessione da utilizzare all'interno della routine.
        // par_PROGNCIV --> progressivo numero civico esterno.
        // par_CODELEMEN --> codice elemento
        // par_row_out --> row contentente la voce dell'elemento (parametro di output)
        // par_cod_err [Field Reference] --> codice di errore restituito dalla funzione. Sotto sono elencati tutti i possibili codici di errore:
        // .................................................................. 0 : Nessun errore
        // .................................................................. 1 : Non trovata associazione Voce-Viario
        // .................................................................. 2 : Errore SQL su tabella BTA_VIAVOC
        // .................................................................. 3 : Errore SQL su tabella BTA_VOCI
        // .................................................................. 4 : Errore SQL su tabella BTA_NCIVI
        // Return value: True --> esito positivo; False --> esito negativo
        // ===========================================================================================================================================================
    }

    public static function arrayToXml($arrayData, $root = 'root') {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $root . '/>');
        cwbLibCalcoli::recursiveArrayToXml($arrayData, $xml);
        $xml->addAttribute('encoding', 'UTF-8');

        return $xml->asXML();
    }

    private static function recursiveArrayToXml($data, &$xml_data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xml_data->addChild($key);
                cwbLibCalcoli::recursiveArrayToXml($value, $subnode);
            } else {
                $utf8Value = cwbLibCalcoli::safeUtf8Encode($value);
                $tmp = $xml_data->addChild("$key");
                cwbLibCalcoli::add_cdata($tmp, $utf8Value);
            }
        }
    }

    private static function add_cdata(&$root, $cdataText) {
        $node = dom_import_simplexml($root);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdataText));
    }

    private static function safeUtf8Encode($toEncode) {
        if (is_array($toEncode)) {
            return $toEncode;
        } else {
            return utf8_encode($toEncode);
        }
    }

    public static function stringXmlToArray($xmlString) {
        $xmlObj = new DOMDocument();
        $xmlObj->preserveWhiteSpace = false;
        $xmlObj->loadXML($xmlString);
        return cwbLibCalcoli::xmlToArray($xmlObj);
    }

    public static function xmlToArray($root) {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $attr) {
                $result['@attributes'][$attr->name] = $attr->value;
            }
        }

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE || $child->nodeType == XML_CDATA_SECTION_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            $groups = array();
            foreach ($children as $child) {
                if (!isset($result[$child->nodeName])) {
                    $result[$child->nodeName] = cwbLibCalcoli::xmlToArray($child);
                } else {
                    if (!isset($groups[$child->nodeName])) {
                        $result[$child->nodeName] = array($result[$child->nodeName]);
                        $groups[$child->nodeName] = 1;
                    }
                    $result[$child->nodeName][] = cwbLibCalcoli::xmlToArray($child);
                }
            }
        } else {
            return null;
        }

        return $result;
    }

    //Questa funzione ordina l'array in ingresso in base al campo indicato
    public static function ordina(&$array, $campo_ordina1, $campo_ordina2 = '') {
// inizializziamo un ciclo for che abbia come condizione di terminazione
// il numero degli array interni meno "1"
        for ($i = 0; $i < count($array) - 1; $i++) {
            // inizializziamo un ciclo for che abbia come condizione di terminazione
            // il numero degli array
            for ($j = $i + 1; $j < count($array); $j++) {
                // utilizziamo come indici i valori derivanti dall'iterazione dei cicli e utilizziamoli
                // per effettuare un controllo tra valori 
                $ordina = strcmp($array[$i][$campo_ordina1], $array[$j][$campo_ordina1]);

                // ordiniamo i valori sulla base dei confronti ponendo per primi
                // i valori alfabeticamente "maggiori" 
                if ($ordina > 0) {
                    $ordinato = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $ordinato;
                } else if ($ordina == 0) {
                    //Significa che le stringhe sono uguali quindi confronto per il secondo campo
                    if (!cwbLibCheckInput::IsNBZ($campo_ordina2)) {
                        $ordina2 = strcmp($array[$i][$campo_ordina2], $array[$j][$campo_ordina2]);
                        if ($ordina2 > 0) {
                            $ordinato = $array[$i];
                            $array[$i] = $array[$j];
                            $array[$j] = $ordinato;
                        }
                    }
                }
            }
        }
    }

    /**
     * Inserisce un valore ad una specifica posizione dell'array facendo slittare tutti i valori successivi (solo per array con chiavi numeriche)
     * @param array $array 
     * @param int $index indice in cui inserire la chiave
     * @param Object $val
     * @return array 
     */
    public static function addInArray($array, $index, $val) {
        $size = count($array);
        if (!is_int($index) || $index < 0 || $index > $size) {
            return null;
        } else {
            // prendo l'array da 0 alla chiave da inserire
            $temp = array_slice($array, 0, $index);
            // aggiungo il mio valore
            $temp[] = $val;
            // rimetto dopo i valori dalla chiave a fine array
            return array_merge($temp, array_slice($array, $index, $size));
        }
    }

    //Questa funzione ordina l'array in ingresso in base al campo indicato
    public static function array_ordina($array, $campoOrd1, $order = 'SORT_ASC') {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $campoOrd1) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case 'SORT_ASC':
                    asort($sortable_array);
                    break;
                case 'SORT_DESC':
                    arsort($sortable_array);
                    break;
                default :
                    asort($sortable_array);
                    break;
            }
            foreach ($sortable_array as $k => $v) {
                array_push($new_array, $array[$k]);
            }
        }
        return $new_array;
    }

    public static function getLastDayOfMonth($numMonth, $anno) {
        if (intval($numMonth) > 12) {
            $numMonth = 12;
        }

        if (intval($numMonth) < 12) {
            $dataP = date('Y-m-d', strtotime($anno . '/' . ($numMonth + 1) . '/' . '01'));
            return date('Y-m-d', strtotime('-1 day', strtotime(($dataP))));
        } else {
            return date('Y-m-d', strtotime($anno . '/12/31'));
        }
    }

    // GINA il 31.05.2018 - funzione che mi va a formattare il suggest per le località, aggiungendo l'anno di inizio e fine validità
    // nel caso di FORMIA, appariva la località due volte ma non si capiva quale fosse attivo. Risultato finale:
    // FORMIA (LT) 1945 - 
    //FORMIA (LT) 1938 - 1945
    // * il parametro $formatt server per capire se ci sono più record per quella denominazione
    public static function formatSuggestLocal($deslocal, $provincia, $datainiz = null, $datafine = null, $formatt = false) {
        $stringSuggest = $deslocal . ' (' . $provincia . ')';
        if ($formatt == true) {
            if (!cwbLibCheckInput::IsNBZ($datainiz)) {
                $AnnoIniz = cwbLibCalcoli::estraiAnno($datainiz);
                if (!cwbLibCheckInput::IsNBZ($AnnoIniz)) {
                    $stringSuggest = $stringSuggest . ' ' . $AnnoIniz;
                }
                if (!cwbLibCheckInput::IsNBZ($datafine)) {
                    $AnnoFine = cwbLibCalcoli::estraiAnno($datafine);
                    if (!cwbLibCheckInput::IsNBZ($AnnoFine)) {
                        $stringSuggest = $stringSuggest . ' - ' . $AnnoFine;
                    }
                }
            }
        }
        return $stringSuggest;
    }

    public static function calcCodOttico($idbol_sere, $annoemi, $numemi, $numdoc, $numrata, $doc8 = false, $tipo = 0) {
        $loc_codice = 0;
        $ente = cwbParGen::getBorEnti();
        $loc_2 = str_pad(trim($ente[0]['IDBOL_ENTE']), 2, "0", STR_PAD_LEFT);
        $loc_codice = $loc_2;
        if (intval($tipo) === 0) {
            $loc_2 = str_pad(trim($idbol_sere), 2, "0", STR_PAD_LEFT);
        } else {
            $loc_2 = str_pad(trim($tipo), 2, "0", STR_PAD_LEFT);
        }
        $loc_codice = $loc_codice . $loc_2;
        if (intval($annoemi) > 2000) {
            $loc_2 = str_pad(trim((intval($annoemi - 2000))), 2, "0", STR_PAD_LEFT);
        } else {
            $loc_2 = str_pad(trim((intval($annoemi - 1900))), 2, "0", STR_PAD_LEFT);
        }
        $loc_codice = $loc_codice . $loc_2;
        if (intval($tipo) === 0) {
            $loc_2 = str_pad(trim($numemi), 2, "0", STR_PAD_LEFT);
        } else {
            $loc_2 = '00';
        }
        $loc_codice = $loc_codice . $loc_2;
        if ($doc8) {
            $loc_8 = str_pad(trim($numdoc), 8, "0", STR_PAD_LEFT);
        } else {
            $loc_8 = str_pad(trim($numdoc), 6, "0", STR_PAD_LEFT) . str_pad(trim($numrata), 2, "0", STR_PAD_LEFT);
        }
        $loc_codice = $loc_codice . $loc_8;
        $loc_cc = fmod(floatval($loc_codice), 93);
        $loc_2 = str_pad($loc_cc, 2, "0", STR_PAD_LEFT);
        return $loc_codice . $loc_2;
    }

    /**
     * Questo metodo esegue il controllo del codice fiscale 
     * per richiamo ctl_CFIS + verifica doppio codice fiscale
     * @param $methodArgs array con codice fiscale da verificare e progsogg
     * @return object Record
     */
    public static function controllaCf($methodArgs, $calcCodFisc = false, $codfiscConfronto = '') {
        $rowEsito = array();
        $rowEsito['STATO'] = 1;
        $rowEsito['MESSAGE'] = '';
        $rowEsito['TIPOMSG'] = 2; //1 bloccante, 2 forzabile, 3 informativo

        include_once ITA_BASE_PATH . '/apps/CityBase/cwbBtaSoggettoUtils.class.php';

        //verifico la lunghezza del codice fiscale perchè potrebbe essere 16
        //oppure nel caso di provvisorio potrebbe essere simile a partita iva

        $cfLen = strlen(trim($methodArgs[0]));
        if ($cfLen != 0 && $cfLen != 11 && $cfLen != 16) {
            $rowEsito['STATO'] = 0;
            $rowEsito['MESSAGE'] = 'La lunghezza del codice fiscale è anomala.';
            $rowEsito['TIPOMSG'] = 1;
        }

        if ($calcCodFisc = true && !cwbLibCheckInput::IsNBZ($codfiscConfronto)) {
            if (!cwbLibCheckInput::IsNBZ($methodArgs[0])) {

                if ($methodArgs[0] == $codfiscConfronto) {
                    
                } else {
                    $rowEsito['STATO'] = 0;
                    $rowEsito['MESSAGE'] = "Codice Fiscale Incongruente con dati anagrafici.";
                    $rowEsito['TIPOMSG'] = 2;
                }
            }
        }

        if ($rowEsito['STATO'] == 1) {
            $cwbBtaSoggettoUtils = new cwbBtaSoggettoUtils();
            $risultato = $cwbBtaSoggettoUtils->ctl_cfis($methodArgs);
            if ($risultato['RESULT']['EXITCODE'] == 'N' && !cwbLibCheckInput::IsNBZ($risultato['RESULT']['MESSAGE'])) { // KO 
                $rowEsito['STATO'] = 0;
                $rowEsito['MESSAGE'] = $risultato['RESULT']['MESSAGE'];
                $rowEsito['TIPOMSG'] = 2;
            }
        }

        if ($rowEsito['STATO'] == 1) {
            //esgue query su BTA_SOGG per codicefiscale e progsogg<>da quello in ingresso
            if (!cwbLibCheckInput::IsNBZ($methodArgs[1])) {
                $sql = "SELECT * from BTA_SOGG where codfiscale='" . $methodArgs[0] . "'";
                $sql .= " AND PROGSOGG<>" . $methodArgs[1];
                $libDB_Bta = new cwbLibDB_BTA();
                $libDB_Bta->leggi($sql, $result);
                if ($result == false) {
                    //nessun record trovato quindi non faccio nulla perchè è positivo
                } elseif (is_array($result) && count($result) > 0) {
                    $rowEsito['STATO'] = 0;
                    $rowEsito['MESSAGE'] = "Esiste un altro soggetto " . $result[0]['COGNOME'] . ' ' . $result[0]['NOME'] . " con lo stesso codice fiscale del soggetto corrente";
                    $rowEsito['TIPOMSG'] = 2;
                }
            }
        }

        return $rowEsito;
    }

//    Funzione per calcolare l'età di un Soggetto
    public static function eta($dataIniz, $dataFine, $anno = 0) {
        if (!cwbLibCheckInput::IsNBZ($anno)) {
            $AnnoDataIniz = $anno;
            $currentDate = date('Y-m-d');
            $annoDataFin = $anno = cwbLibCalcoli::estraiAnno($currentDate, 'Y-m-d');
            $anni = $annoDataFin - $AnnoDataIniz;
        } else {
            $datetime1 = new DateTime($dataIniz);
            $datetime2 = new DateTime($dataFine);
            $diff = date_diff($datetime1, $datetime2);
            $anni = $diff->format('%y');
        }
        return $anni;
    }

    /**
     * Trova progressivo alfanumerico (Utilizzato per calcolare nome file fattura elettronica da inviare a SDI)
     * @param string $contatore Contatore
     * @param int $length Lunghezza
     * @param string $regex RegExp
     * @return mixed Progressivo se esito positivo, altrimenti false
     */
    public static function trovaProgressivoAlfa($contatore = '00010', $length = 5, $regex = '/[0-9A-Z]/') {
        $valoriAscii = array();

        // Scorro la tabella ASCII e mi creo la lista dei possibili valori
        for ($n = 0; $n < 256; $n++) {
            preg_match($regex, chr($n), $matches, PREG_OFFSET_CAPTURE);
            if ($matches) {
                $valoriAscii[] = $matches[0][0];
            }
        }
        if (!$valoriAscii) {
            return false;
        }

        // Inizializza contatore
        $progressivo = str_pad($contatore, 5, $valoriAscii[0], STR_PAD_LEFT);

        // Incrementa contatore di un valore
        for ($n = $length - 1; $n >= 0; $n--) {

            // Parto dall'ultima cifra e aggiungo il valore successivo alla lista
            $toSearch = substr($progressivo, $n, 1);
            $trovato = array_search($toSearch, $valoriAscii);
            if ($trovato === (count($valoriAscii) - 1)) {
                // Se è l'ultimo carattare devo andare alla colonna successiva
                $progressivo = substr($progressivo, 0, $n) . $valoriAscii[0] . substr($progressivo, $n + 1, $length);
                continue;
            } else {
                // Sono riuscito ad incrementare il contatore quindi non è in errore
                $progressivo = substr($progressivo, 0, $n) . $valoriAscii[$trovato + 1] . substr($progressivo, $n + 1, $length);
                break;
            }
        }

        return $progressivo;
    }

}
