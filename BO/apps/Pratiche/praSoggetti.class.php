<?php
//ini_set("error_reporting", E_ALL);
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLib.class.php';

class praSoggetti {

    public $soggetti = array();
    public $pratica;
    public $arrCountFiere = array();

    /**
     * 
     * @param type $praLib    Non piu usato!!
     * @param type $pratica
     * @return object
     */
    public static function getInstance($praLib='', $pratica = '', $extraParam = array()) {
        try {
            $obj = new praSoggetti();
        } catch (Exception $exc) {
            return false;
        }

        if ($pratica) {
            $obj->pratica = $pratica;
            if (!$obj->caricaSoggetti($extraParam)) {
                return false;
            }
        }
        return $obj;
    }

    /**
     * Carica dati Oggetto da DB
     * @return boolean
     */
    function caricaSoggetti($extraParam) {
        $this->soggetti = $this->GetSoggettiFromDB($extraParam);
        return true;
    }

    /**
     * 
     * @return array
     */
    function GetSoggettiFromDB($extraParam) {
        $praLib = new praLib() ;
        if ($this->pratica) {
            if (!$extraParam) {
                return $praLib->GetAnades($this->pratica, "codice", true);
            }
            $anades_tab = $praLib->GetAnades($this->pratica, "codice", true);
            if (isset($extraParam['EXCLUDE_ROLES'])) {
                foreach ($extraParam['EXCLUDE_ROLES'] as $roleEx) {
                    foreach ($anades_tab as $key => $anades_rec) {
                        if ($anades_rec['DESRUO'] == $roleEx) {
                            unset($anades_tab[$key]);
                        }
                    }
                }
                return $anades_tab;
            }
            if (isset($extraParam['INCLUDE_ROLES'])) {
                $anades_tab_def = array();
                foreach ($extraParam['INCLUDE_ROLES'] as $roleInc) {
                    $anades_tab_tmp = $praLib->GetAnades($this->pratica, "ruolo", true, $roleInc);
                    $anades_tab_def = array_merge($anades_tab_def, $anades_tab_tmp);
                }
                return $anades_tab_def;
            }
        } else {
            return array();
        }
    }

    /**
     * Restituisce l'array del Count delle Fiere
     * @return array
     */
    function GetArrCountFiere() {
        return $this->arrCountFiere;
    }

    /**
     * Restituisce l'array dei soggetti
     * @return array
     */
    function GetSoggetti() {
        return $this->soggetti;
    }

    /**
     * Ritorna il soggetto selezionato
     * @param int $rowid
     * @return array
     */
    function GetSoggetto($rowid) {
        return $this->soggetti[$rowid];
    }

    /**
     * Inserisce o modifica un soggetto
     * @param array $soggetto
     * @param int $rowid
     */
    function SetSoggetto($soggetto, $rowid = "") {
        if ($rowid != "") {
            $this->soggetti[$rowid] = $soggetto;
        } else {
            $this->soggetti[] = $soggetto;
        }
    }

//    function SetSoggetto($soggetto, $rowid = 0) {
//        if ($rowid) {
//            $this->soggetti[$rowid] = $soggetto;
//        } else {
//            $this->soggetti[] = $soggetto;
//        }
//    }

    /**
      Restituisce array formattato per essere
     * visualizzato in una tabella
     * @return array
     */
    function getGriglia() {
        $praLib = new praLib() ;
        if ($this->soggetti) {
            $anades_tab = $this->soggetti;
            $this->arrCountFiere = array();
            foreach ($anades_tab as $key => $anades_rec) {
                $desnom = "";
                if (trim($anades_rec['DESNOM'])) {
                    $desnom = $anades_rec['DESNOM'];
                } else {
                    if (trim($anades_rec['DESCOGNOME'])) {
                        $desnom = $anades_rec['DESCOGNOME'] . " " . $anades_rec['DESNOME'];
                    } else {
                        if (trim($anades_rec['DESRAGSOC'])) {
                            $desnom = $anades_rec['DESRAGSOC'];
                        }
                    }
                }
                $anades_tab[$key]['DESNOM'] = $desnom;
                $anaruo_rec = $praLib->GetAnaruo($anades_rec['DESRUO']);
                $anades_tab[$key]['RUOLO'] = $anaruo_rec['RUODES'];
                if ($anades_rec['DESRUOEXT']) {
                    $anades_tab[$key]['RUOLO'] = '<div class="ita-html"><span class="ita-tooltip" title="' . htmlentities($anades_rec['DESRUOEXT'], ENT_COMPAT | ENT_HTML5, 'ISO-8859-1') . '">' . $anaruo_rec['RUODES'] . '</span></div>';
                }
                $anades_tab[$key]['DESNUM'] = $this->pratica;
                $mail = $anades_rec['DESPEC'] ? $anades_rec['DESPEC'] : $anades_rec['DESEMA'];
                $anades_tab[$key]['DESPEC'] = $mail;
                $anades_tab[$key]['INDIRIZZO'] = $anades_rec['DESIND'] . " " . $anades_rec['DESCIV'];
                //if ($anades_rec['DESRUO'] == "0005") {
                if ($anades_rec['DESPIVA']) {
                    $anades_tab[$key]['FISCIVA'] = $anades_rec['DESPIVA'];
                } else {
                    $anades_tab[$key]['FISCIVA'] = $anades_rec['DESFIS'];
                }
                $anaspa_rec = $praLib->GetAnaspa($anades_rec['DESNUM'], 'codice');
                if ($anaspa_rec["SPAENTECOMM"]) {
                    $dittaCOMM = $anaspa_rec['SPAENTECOMM'];
                }

                if ($this->checkExistDB("COMM", $dittaCOMM)) {
                    $numAutComm = $this->CheckAutCommercio($anades_rec['DESNUM'], $anades_rec['DESRUO'], $anades_rec['DESFIS'], $anades_rec['DESPIVA']);
                    if ($numAutComm > 0) {
                        $anades_tab[$key]['AUT'] = "<p align = \"center\"><span style=\"margin-right:2px; vertical-align:middle; border:1px solid black; display:inline-block; width:10px; height:10px;  -moz-border-radius: 20px; border-radius: 20px; background-color:green ;\"></span><span style=\"display:inline-block;\">$numAutComm</span></p>";
                    }
                }
                if ($this->checkExistDB("GAFIERE")) {
                    $arrFiere = $this->CheckAutFiere($anades_rec['DESFIS'], $anades_rec['DESPIVA']);
                    $numAutFiere = count($arrFiere);
                    if ($numAutFiere > 0) {
                        $this->arrCountFiere[$anades_rec['ROWID']] = $arrFiere;
                        $anades_tab[$key]['FIERE'] = "<p align = \"center\"><span style=\"margin-right:2px; vertical-align:middle; border:1px solid black; display:inline-block; width:10px; height:10px;  -moz-border-radius: 20px; border-radius: 20px; background-color:green ;\"></span><span style=\"display:inline-block;\">$numAutFiere</span></p>";
                    }
                }
                if ($anades_rec['DESRUO'] == praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']) {
                    $anades_tab[$key]['POSIZIONE'] = "<span title=\"Apri Mappa\" class=\"ita-icon ita-icon-map-position-16x16\"></span>";
                }
            }
            return $anades_tab;
        } else {
            return array();
        }
    }

    /**
     * Inserisce o aggiorna un soggetto sul DB
     * @return boolean
     */
    function RegistraSoggetti($model) {
        if ($this->soggetti) {
            $praLib = new praLib() ;
            foreach ($this->soggetti as $soggetto) {
                if ($soggetto['ROWID'] == 0) {
                    $insert_Info = "Oggetto: Inserisco il soggetto per la pratica " . $soggetto['PRONUM'];
                    if (!$model->insertRecord($praLib->getPRAMDB(), 'ANADES', $soggetto, $insert_Info)) {
                        return "Errore Inserimento soggetto pratica " . $soggetto['PRONUM'];
                    }
                } else {
                    $update_Info = "Oggetto: Aggiorno soggetto per la pratica " . $soggetto['PRONUM'];
                    if (!$model->updateRecord($praLib->getPRAMDB(), 'ANADES', $soggetto, $update_Info)) {
                        return "Errore Aggiornamento soggetto pratica " . $soggetto['PRONUM'];
                    }
                }
            }
        }
        return true;
    }

    /**
     * Cancella soggetto sia da array che da DB
     * @param int $rowid
     * @param obj $model
     * @return boolean
     */
    function CancellaSoggetto($rowid, $model) {
        $praLib = new praLib() ;
        if (array_key_exists($rowid, $this->soggetti) == true) {
            if ($this->soggetti[$rowid]['ROWID'] != 0) {
                $delete_Info = 'Oggetto: Cancellazione soggetto pratica' . $this->soggetti[$rowid]['DESNUM'];
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'ANADES', $this->soggetti[$rowid]['ROWID'], $delete_Info)) {
                    return false;
                }

                $anadesdag_tab = $praLib->getAnadesdag($this->soggetti[$rowid]['ROWID']);
                foreach ($anadesdag_tab as $anadesdag_rec) {
                    $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo soggetto pratica ' . $this->soggetti[$rowid]['DESNUM'] . ' ' . $anadesdag_rec['DESKEY'];
                    if (!$model->deleteRecord($praLib->getPRAMDB(), 'ANADESDAG', $anadesdag_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                        return false;
                    }
                }
            }
            unset($this->soggetti[$rowid]);
            return true;
        }
    }

    /**
     * Cancella tutti i soggetti di una pratica
     * @param obj $model
     * @return boolean
     */
    public function CancellaSoggetti($model) {
        if ($this->soggetti) {
            $praLib = new praLib() ;
            $delete_Info = "Oggetto: Cancellazione di tutti i soggetti della pratica $this->pratica";
            foreach ($this->soggetti as $soggetto) {
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'ANADES', $soggetto['ROWID'], $delete_Info)) {
                    return false;
                }

                $anadesdag_tab = $praLib->getAnadesdag($soggetto['ROWID']);
                foreach ($anadesdag_tab as $anadesdag_rec) {
                    if (!$model->deleteRecord($praLib->getPRAMDB(), 'ANADESDAG', $anadesdag_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function RinumeraSoggetti($model, $anno, $newNumero) {
        if ($this->soggetti) {
            $praLib = new praLib() ;
            $update_Info = "Oggetto: Rinumero Immobili da " . $this->soggetti[0]['DESNUM'] . " a $anno$newNumero";
            foreach ($this->soggetti as $soggetto) {
                $soggetto['DESNUM'] = $anno . $newNumero;
                if (!$model->updateRecord($praLib->getPRAMDB(), 'ANADES', $soggetto, $update_Info)) {
                    return false;
                }
            }
        }
        return true;
    }

    function CheckAutCommercio($gesnum, $ruolo, $fiscale, $piva) {
        $praLib = new praLib() ;
        $proges_rec = $praLib->GetProges($gesnum);
        $wcoLib = new wcoLib();
        if ($proges_rec['GESSPA'] != 0) {
            $anaspa_rec = $praLib->GetAnaspa($proges_rec['GESSPA'], 'codice');
            if ($anaspa_rec["SPAENTECOMM"]) {
                $wcoLib = new wcoLib($anaspa_rec["SPAENTECOMM"]);
            }
        }
        $comlic_tab = array();
        if ($piva == 0) {
            $piva = "";
        }
        if (!$fiscale) {
            $fiscale = "";
        }
        if ($fiscale == "" && $piva == "") {
            return 0;
        } else {
            if ($fiscale && $piva) {
                $sql = "SELECT
                            COMLIC.*
                        FROM
                            COMLIC
                            LEFT OUTER JOIN COMSOC ON COMLIC.LICPRO = COMSOC.SOCLIC
                        WHERE
                            (
                                (LICCOF = '$fiscale' OR COMSOC.SOCCOF = '$fiscale') OR
                                (LICRPI = '$piva' OR LICRPI = '$fiscale') OR
                                (LICRCF = '$piva' OR LICRCF = '$fiscale')
                            )
                            ";
            } elseif ($fiscale && $piva == "") {
                $sql = "SELECT
                            COMLIC.*
                        FROM
                            COMLIC
                            LEFT OUTER JOIN COMSOC ON COMLIC.LICPRO = COMSOC.SOCLIC
                        WHERE
                            (
                                (LICCOF = '$fiscale' OR COMSOC.SOCCOF = '$fiscale') OR
                                (LICRPI = '$fiscale') OR
                                (LICRCF = '$fiscale')
                            )
                            ";
            } elseif ($fiscale == "" && $piva) {
                $sql = "SELECT
                            COMLIC.*
                        FROM
                            COMLIC
                            LEFT OUTER JOIN COMSOC ON COMLIC.LICPRO = COMSOC.SOCLIC
                        WHERE
                            (
                                (LICRPI = '$piva') OR
                                (LICRCF = '$piva')
                            )
                            ";
            }
        }
        $comlic_tab = ItaDB::DBSQLSelect($wcoLib->getCOMMDB(), $sql, true);
        return count($comlic_tab);
    }

    function CheckAutFiere($fiscale, $piva) {
        $GAFIEREDB = ItaDB::DBOpen('GAFIERE');
        $arrFiere = array();
        //
        //Cerco su tutti i GAFIERE In cui possono trovarsi le fiere
        //
        $anafiere_tab = ItaDB::DBSQLSelect($GAFIEREDB, "SELECT DISTINCT ENTEFIERE FROM ANAFIERE", true);
        foreach ($anafiere_tab as $anafiere_rec) {
            $whereFiscale = "";
            $wherePiva = "";
            if ($fiscale) {
                $whereFiscale = "(ANADITTA.CODICEFISCALE = '$fiscale' OR DITTESOGG.CF = '$fiscale')";
            }
            if ($piva) {
                $wherePiva = "(ANADITTA.PIVA = '$piva' OR DITTESOGG.PIVA = '$piva')";
            }
            $whreFiscalePiva = "1";
            if ($whereFiscale == "" && $wherePiva == "") {
                return $arrFiere;
            }
            if ($whereFiscale && $wherePiva) {
                $whreFiscalePiva = $whereFiscale . " OR " . $wherePiva;
            }
            if ($whereFiscale && $wherePiva == "") {
                $whreFiscalePiva = $whereFiscale;
            }
            if ($whereFiscale == "" && $wherePiva) {
                $whreFiscalePiva = $wherePiva;
            }
            if ($whereFiscale)
                if ($anafiere_rec['ENTEFIERE']) {
                    try {
                        $GAFIEREDB = ItaDB::DBOpen('GAFIERE', $anafiere_rec['ENTEFIERE']);
                    } catch (Exception $e) {
                        Out::msgStop("Errore", "Errore nell'aprire GAFIERE" . $anafiere_rec['ENTEFIERE'] . "-->" . $e->getMessage());
                        return false;
                    }
                }
            $sql = "SELECT
                    ANADITTA.*
                FROM  
                    ANADITTA
                LEFT OUTER JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                WHERE
                    $whreFiscalePiva ";
//            $sql = "SELECT
//                    ANADITTA.*
//                FROM  
//                    ANADITTA
//                LEFT OUTER JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
//                WHERE
//                    (ANADITTA.CODICEFISCALE = '$fiscale' OR DITTESOGG.CF = '$fiscale') OR
//                    (ANADITTA.PIVA = '$piva' OR DITTESOGG.PIVA = '$piva') ";
            $fiere_tab = ItaDB::DBSQLSelect($GAFIEREDB, $sql, true);
            if ($fiere_tab) {
                foreach ($fiere_tab as $keyFiere => $fiere_rec) {
                    if ($anafiere_rec['ENTEFIERE']) {
                        $fiere_tab[$keyFiere]['ENTE'] = $anafiere_rec['ENTEFIERE'];
                    } else {
                        $fiere_tab[$keyFiere]['ENTE'] = App::$utente->getKey('ditta');
                    }
                    $arrFiere[] = $fiere_tab[$keyFiere];
                }
            } else {
                continue;
            }
        }
        return $arrFiere;
    }

    public function checkExistDB($db, $dittaCOMM = "") {
        try {
            if ($dittaCOMM) {
                $DB = ItaDB::DBOpen($db, $dittaCOMM);
            } else {
                $DB = ItaDB::DBOpen($db);
            }
            $arrayTables = $DB->listTables();
        } catch (Exception $exc) {
            return false;
        }
        if ($DB == "") {
            return false;
        } else {
            if (!$arrayTables) {
                return false;
            }
        }
        return true;
    }

}

?>
