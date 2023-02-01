<?php

class praLibGfm {

    public $praErr;

    /**
     * Libreria di funzioni Generiche e Utility per Integrazione SUAP e FIERE
     */
    function __construct($libErr = null) {
        if (!$libErr) {
            //$this->praErr = new sueErr();
        } else {
            $this->praErr = $libErr;
        }
    }

    function __destruct() {
        
    }

    public function GetFiere($Codice, $GAFIERE_DB, $tipoRic = 'codice') {
        if (!$Codice)
            return false;
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM FIERE WHERE FIERA = '$Codice'";
        } else {
            $sql = "SELECT * FROM FIERE WHERE ROWID = $Codice";
        }
        return ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
    }

    public function GetAnafiere($Codice, $GAFIERE_DB, $tipoRic = 'codice') {
        if (!$Codice)
            return false;
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAFIERE WHERE TIPO = '$Codice'";
        } else {
            $sql = "SELECT * FROM ANAFIERE WHERE ROWID = $Codice";
        }
        return ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
    }

    public function GetMercati($Codice, $GAFIERE_DB, $tipoRic = 'codice') {
        if (!$Codice)
            return false;
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM BANDIM WHERE FIERA = '$Codice'";
        } else {
            $sql = "SELECT * FROM BANDIM WHERE ROWID = $Codice";
        }
        return ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
    }

    public function GetAnamerc($Codice, $GAFIERE_DB, $tipoRic = 'codice') {
        if (!$Codice)
            return false;
        if ($tipoRic == 'codice') {
            $sql = "SELECT * FROM ANAMERC WHERE CODICE = '$Codice'";
        } else {
            $sql = "SELECT * FROM ANAMERC WHERE ROWID = $Codice";
        }
        return ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
    }

    public function GetAnaditta($Codice, $GAFIERE_DB, $tipoRic = 'codice') {
        if (!$Codice)
            return false;
        if ($tipoRic == 'codice') {
            if (!is_numeric($Codice))
                return false;
            $sql = "SELECT * FROM ANADITTA WHERE CODICE = " . $Codice;
        } else if ($tipoRic == 'fiscale') {
            $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '$Codice' OR PIVA='$Codice')";
        } else {
            $sql = "SELECT * FROM ANADITTA WHERE ROWID='$Codice'";
        }
        return ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
    }

    public function GetDescDenomFiere($Ricdat, $GAFIERE_DB) {
        $arrayCodici = unserialize($Ricdat);
        if (is_array($arrayCodici)) {
            //$arrayCodici = unserialize($Ricdat);
            $descFiere = "";
            foreach ($arrayCodici as $codiceFiera => $value) {
                if ($value == 1) {
                    $fiere_rec = $this->GetFiere($codiceFiera, $GAFIERE_DB, 'rowid');
                    $anafiere_rec = $this->GetAnafiere($fiere_rec['FIERA'], $GAFIERE_DB);
                    $descFiere .= $anafiere_rec['FIERA'] . ", ";
                }
            }
        } else {
            $fiere_rec = $this->GetFiere($Ricdat, $GAFIERE_DB, 'rowid');
            $anafiere_rec = $this->GetAnafiere($fiere_rec['FIERA'], $GAFIERE_DB);
            $descFiere = $anafiere_rec['FIERA'];
        }

        return $descFiere;
    }

    public function GetDescDenomMercato($Ricdat, $GAFIERE_DB) {
        $bandim_rec = $this->GetMercati($Ricdat, $GAFIERE_DB, 'rowid');
        $anamerc_rec = $this->GetAnamerc($bandim_rec['FIERA'], $GAFIERE_DB);
        $descMercato = $anamerc_rec['MERCATO'];
        return $descMercato;
    }

    public function GetHtmlPassoPosteggiFiere($dati, $Ricdag_rec) {
        $arrFiere = unserialize($dati['ricdag_denom_fiera']['RICDAT']);
        foreach ($arrFiere as $rowidFiera => $value) {
            if ($value == 1) {
                $fiere_rec = $this->GetFiere($rowidFiera, $dati['GAFIERE_DB'], "rowid");
                $sql = "SELECT 
                            FIEREPOS.POSTO,
                            FIEREPOS.CODICEVIA
                        FROM
                            FIEREPOS
                        LEFT OUTER JOIN 
                            FIERECOM
                        ON
                            FIERECOM.FIERA = FIEREPOS.TIPO AND
                            FIERECOM.DATA = '{$fiere_rec['DATA']}' AND
                            FIERECOM.ASSEGNAZIONE = '{$fiere_rec['ASSEGNAZIONE']}' AND
                            (FIERECOM.POSTO = 0 OR FIERECOM.POSTO = '')
                        WHERE
                            FIEREPOS.TIPO = '{$fiere_rec['FIERA']}'";

                $fierepos_tab = ItaDB::DBSQLSelect($dati['GAFIERE_DB'], $sql, true);
                if ($dati['ricdat'] == 1)
                    $disabled = "disabled=\"disabled\"";
                $html = "<label class=\"ita-label\" style=\"text-align:right;display:inline-block;\">Posteggi</label>";
                $html .= "<select name=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"raccolta_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>";
                $html .= "<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>';";
                foreach ($fierepos_tab as $fierepos_rec) {
                    $Sel = "";
                    if ($Ricdag_rec['RICDAT'] == $fierepos_rec['POSTO'])
                        $Sel = "selected";
                    $html .= "<option $Sel class=\"optSelect\" value=\"{$fierepos_rec['POSTO']}\">{$fierepos_rec['POSTO']}</option>';";
                }
                $html .= "</select>";
                return $html;
            }
        }
    }

    public function GetHtmlPassoFiere($dati, $Ricdag_rec) {
        $html = "";
        if ($dati['ricdat'] == 1)
            $disabled = "disabled=\"disabled\"";
        //Decodifico I codici Fiera
        $ricdat_val_tab = unserialize($Ricdag_rec['RICDAT']);
        $arrayOptions = $dati['Anafiere_tab'];
        if ($arrayOptions) {
            foreach ($arrayOptions as $option) {
                $checked = "";
                if (count($arrayOptions) == 1)
                    $checked = "checked=\"yes\"";
                //Valorizzo codici fiera
                foreach ($ricdat_val_tab as $codiceFiera => $value) {
                    if ($codiceFiera == $option['ROWID'] && $value == 1)
                        $checked = "checked=\"yes\"";
                }
                $valueData = substr($option["DATA"], 6, 2) . "/" . substr($option["DATA"], 4, 2) . "/" . substr($option["DATA"], 0, 4);
                $valueDataTermine = substr($option["DATATERMINE"], 6, 2) . "/" . substr($option["DATATERMINE"], 4, 2) . "/" . substr($option["DATATERMINE"], 0, 4);
                $html .= "<div class=\"ita-field\" style=\"width:250px;\">";
                $html .= "<input class=\"ita-edit\" style=\"margin-right:5px;\" type=\"checkbox\" $checked $disabled name=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "][" . $option['ROWID'] . "]\" />";
                $html .= "<label class=\"ita-label\">" . $option['NOMEFIERA'] . "</label>";
                $html .= "</div>";
                $html .= "<div class=\"ita-field\">";
                $html .= "<label class=\"ita-label\" style=\"margin-right:5px;\"><b>Data</b></label>";
                $html .= "<input class=\"ita-edit\" readonly=\"readonly\" maxlength=\"10\" size=\"12\" value=\"$valueData\" name=\"raccolta[DATAFIERA][" . $option['FIERA'] . "]\" />";
                $html .= "</div>";
                $html .= "<div class=\"ita-field\">";
                $html .= "<label class=\"ita-label\" style=\"margin-right:5px;margin-left:10px;\"><b>Data Termine Domanda</b></label>";
                $html .= "<input class=\"ita-edit\" readonly=\"readonly\" maxlength=\"10\" size=\"12\" value=\"$valueDataTermine\" name=\"raccolta[DATATERMINE][" . $option['FIERA'] . "]\" />";
                $html .= "</div>";
                $html .= "<br>";
            }
        }else {
            /*
             * Possibilita (commentata) di mettere un messaggio e un campo non selezionabile se non c'e' 
             * nessuna fiera, attualmente la logica è che se nessuna fiera attiva và avanti e 
             * l'utente puo fare una richiesta libera
             */
//            $checked = "";
//            $disabled = "disabled=\"disabled\"";
//            $html .= "<div class=\"ita-field\" style=\"width:250px;\">";
//            $html .= "<input class=\"ita-edit\" style=\"display:none;margin-right:5px;\" type=\"input\" name=\"raccolta[DENOM_FIERA][0]\" />";
//            $html .= "<label class=\"ita-label\">Nessuna fiera selezionabile</label>";
//            $html .= "</div>";
        }
        return $html;
    }

    public function GetHtmlPassoFiereBando($ricdat, $Ricdag_rec, $bandi_tab) {
        $html = "";
        if ($ricdat == 1)
            $disabled = "disabled=\"disabled\"";
        //Decodifico I codici Fiera
        $arrayOptions = $bandi_tab;
        if ($arrayOptions) {
            foreach ($arrayOptions as $option) {
                $checked = "";
                if (count($arrayOptions) == 1)
                    $checked = "checked";
                //Valorizzo codici fiera
                if ($Ricdag_rec['RICDAT'] == $option['ROWID'])
                    $checked = "checked";
                $valueData = substr($option["DATA"], 6, 2) . "/" . substr($option["DATA"], 4, 2) . "/" . substr($option["DATA"], 0, 4);
                $valueDataTermine = substr($option["DATATERMINE"], 6, 2) . "/" . substr($option["DATATERMINE"], 4, 2) . "/" . substr($option["DATATERMINE"], 0, 4);
                $html .= "<div class=\"ita-field\" style=\"width:250px;\">";
                $html .= "<input class=\"ita-edit\" style=\"margin-right:5px;\" type=\"radio\" $checked $disabled value=\"" . $option['ROWID'] . "\" name=\"raccolta[" . $Ricdag_rec['DAGKEY'] . "]\" />";
                $html .= "<label class=\"ita-label\">" . $option['NOMEFIERA'] . "</label>";
                $html .= "</div>";
                $html .= "<div class=\"ita-field\">";
                $html .= "<label class=\"ita-label\" style=\"margin-right:5px;\"><b>Data</b></label>";
                $html .= "<input class=\"ita-edit\" readonly=\"readonly\" maxlength=\"10\" size=\"12\" value=\"$valueData\" name=\"raccolta[DATAFIERA][" . $option['FIERA'] . "]\" />";
                $html .= "</div>";
                $html .= "<div class=\"ita-field\">";
                $html .= "<label class=\"ita-label\" style=\"margin-right:5px;margin-left:10px;\"><b>Data Termine Domanda</b></label>";
                $html .= "<input class=\"ita-edit\" readonly=\"readonly\" maxlength=\"10\" size=\"12\" value=\"$valueDataTermine\" name=\"raccolta[DATATERMINE][" . $option['FIERA'] . "]\" />";
                $html .= "</div>";
                $html .= "<br>";
            }
        }
        return $html;
    }

    public function CheckVieDoppie($raccolta) {
        $uguale = false;
        foreach ($raccolta as $vie) {
            $arrayAppoggio = array_unique($vie);
            if ($arrayAppoggio != $vie) {
                $uguale = true;
                break;
            }
        }
        return $uguale;
    }

    public function CheckPostoLibero($dati) {
        $arrFiere = unserialize($dati['ricdag_denom_fiera']['RICDAT']);
        foreach ($arrFiere as $rowidFiera => $value) {
            if ($value == 1) {
                $fiere_rec = $this->GetFiere($rowidFiera, $dati['GAFIERE_DB'], "rowid");
                $sql = "SELECT * FROM FIERECOM WHERE FIERA = '{$fiere_rec['FIERA']}' AND DATA = '{$fiere_rec['DATA']}' AND ASSEGNAZIONE = '{$fiere_rec['ASSEGNAZIONE']}' AND POSTO = '{$dati['ricdag_posto_fiera']['RICDAT']}'";
                $fierecom_rec = ItaDB::DBSQLSelect($dati['GAFIERE_DB'], $sql, false);
                if ($fierecom_rec) {
                    return false;
                }
            }
        }
        return true;
    }

    public function GetArrayFiereSelezionate($arrayFiereSel) {
        foreach ($arrayFiereSel as $key => $dagvalue) {
            if ($dagvalue != 0) {
                $arraySel[$key] = $arrayFiereSel[$key];
            }
        }
        return $arraySel;
    }

    function AnnullaPassiFiere(&$dati) {

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
        $praLib = new praLib();

        //
        //Annullo Passo Domanda Posteggio
        //
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            $Ricdag_recPost = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT * FROM RICDAG WHERE ITEKEY='" . $ricite_rec['ITEKEY'] . "' AND DAGKEY = 'FIERA_ROWID' AND DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "'", false);
            if ($Ricdag_recPost) {
                $Ricite_recPost = $ricite_rec;
                break;
            }
        }
        if (!$praLib->AnnullaRaccolta($dati["PRAM_DB"], $dati, $Ricite_recPost)) {
            return false;
        }

        //
        //Annullo Passo Selezione Vie
        //
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            $Ricdag_recVie = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT * FROM RICDAG WHERE ITEKEY='" . $ricite_rec['ITEKEY'] . "' AND DAGKEY = 'ROWIDFIERA' AND DAGNUM = '" . $dati['Proric_rec']['RICNUM'] . "'", false);
            if ($Ricdag_recVie) {
                $Ricite_recVie = $ricite_rec;
                break;
            }
        }
        if (!$praLib->AnnullaRaccolta($dati["PRAM_DB"], $dati, $Ricite_recVie)) {
            return false;
        }

        //
        //Annullo Passo Rapporto Completo
        //
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITEDRR'] == 1) {
                $ricite_recRapporto = $ricite_rec;
                break;
            }
        }
        if (!$praLib->AnnullaPasso($dati["PRAM_DB"], $ricite_recRapporto['ITESEQ'], $dati['Proric_rec'])) {
            return false;
        }
        return true;
    }

}

?>