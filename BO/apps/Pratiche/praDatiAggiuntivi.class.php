<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of pradatiAggiuntivi
 *
 * @author Andrea
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praDatiAggiuntivi {
    public $datiAggiuntivi;
    public $procedimento;
    public $chiavePasso;

    /**
     * 
     * @param type $praLib non più usato
     * @param type $pratica
     * @param type $passo   blank        = dati aggiuntivi testata pratica<br>
     *                      codice passo = dati aggiuntivi di un  passo<br>
     *                      p            = dati aggiuntivi dei passi<br> 
     *                      *            =  tutti i dati aggiuntivi
     * @return boolean|\praAggiuntivi
     */
    public static function getInstance($praLib, $procedimento = "", $chiavePasso = '', $daInfo = false) {
        try {
            $obj = new praDatiAggiuntivi();
        } catch (Exception $exc) {
            return false;
        }

        if ($chiavePasso) {
            $obj->procedimento = $procedimento;
            $obj->chiavePasso = $chiavePasso;
            if (!$obj->caricaDatiAggiuntivi($daInfo)) {
                return false;
            }
        }
        return $obj;
    }

    /**
     * Carica dati Oggetto da DB
     * @return boolean
     */
    function caricaDatiAggiuntivi($daInfo) {
        if ($daInfo == false) {
            $this->datiAggiuntivi = $this->GetDatiAggiuntiviFromDB();
        } else {
            $this->datiAggiuntivi = $this->GetDatiAggiuntiviFromInfo();
        }
        return true;
    }

    /**
     * 
     * @return array
     */
    function GetDatiAggiuntiviFromInfo() {
        if ($this->chiavePasso) {
            $praLib = new praLib();
            $Itepas_rec = $praLib->GetItepas($this->chiavePasso, "itekey");
            $ditta = App::$utente->getKey('ditta');
            $origine = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/';
            if ($praLib->CreaFileInfo($origine . $Itepas_rec['ITEWRD'])) {
                $outputFile = pathinfo($Itepas_rec['ITEWRD'], PATHINFO_FILENAME);
                $outputPath = itaLib::getAppsTempPath();
                $fileInfo['DATAFILE'] = $outputPath . '/' . $outputFile . '.info';
                $arrayInfo = $praLib->DecodeFileInfo($fileInfo);
                $errLength = false;
                foreach ($arrayInfo as $Key => $valore) {
                    if ($Key != "") {
                        $Itedag_rec["ROWID"] = 0;
                        $Itedag_rec["ITECOD"] = $this->currPranum;
                        $Itedag_rec["ITDKEY"] = trim(substr($Key, 0, 60));
                        $Itedag_rec["ITDALIAS"] = trim(substr($Key, 0, 60));
                        $Itedag_rec["ITDSEQ"] = $valore['taborder'];
                        $Itedag_rec["ITEKEY"] = $this->chiavePasso;
                        $datiAggiuntivi[] = $Itedag_rec;
                        if (strlen($Key) > 60) {
                            $errLength = true;
                        }
                    }
                }
                if ($errLength === true) {
                    //Out::msgInfo('AVVISO', "Alcuni campi hanno una lunghezza maggiore di 60 caratteri.<br>Il controllo dei suddetti campi potrebbe dare problemi.");
                }
                return $datiAggiuntivi;
            } else {
                //Out::msgInfo('Errore', 'Importazione campi non eseguita.');
                return array();
            }
        } else {
            return array();
        }
    }

    /**
     * 
     * @return array
     */
    function GetDatiAggiuntiviFromDB() {
        $praLib = new praLib();
        if ($this->chiavePasso) {
            $sql = "SELECT * FROM ITEDAG WHERE ITEKEY = '$this->chiavePasso' AND ITECOD='$this->procedimento' ORDER BY ITDSEQ";
            return ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
        } else {
            return array();
        }
    }

    /**
     * 
     * @return type
     */
    function GetDatiAggiuntivi() {
        return $this->datiAggiuntivi;
    }

    /**
     * 
     * @param type $index
     * @return type
     */
    function GetDatoAggiuntivo($index) {
        return $this->datiAggiuntivi[$index];
    }

    /**
     * 
     * @param type $datoAggiuntivo
     * @param type $index
     */
    function SetDatoAggiuntivo($datoAggiuntivo, $rowid = "") {
        if (is_numeric($rowid)) {
            $this->datiAggiuntivi[$rowid] = $datoAggiuntivo;
        } else {
            $this->datiAggiuntivi[] = $datoAggiuntivo;
        }
    }

    /**
      Restituisce array formattato per essere
     * visualizzato in una tabella
     * @return array
     */
    function getGriglia() {
        $praLib = new praLib();
        $datiAgg = $this->datiAggiuntivi;
        foreach ($datiAgg as $key => $dato) {
            $valore = $dato['ITDVAL'];
            if ($dato['ITDTIC'] == "Html") {
                $valore = "";
            }
            $datiAgg[$key]["ITDVAL"] = $valore;
            //
            $exprOut = unserialize($dato['ITDEXPROUT']);
            $strExprOut = "";
            foreach ($exprOut as $value) {
                $strExprOut .= $praLib->DecodificaControllo($value['EXPCTR']) . "<br>";
            }
            $datiAgg[$key]["ITDEXPROUT"] = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"$strExprOut\">" . substr($dato['ITDEXPROUT'], 0, 50) . "</span></div>";
            //
            $decode = $praLib->DecodificaControllo($dato['ITDCTR']);
            $datiAgg[$key]["ITDCTR"] = "<div class=\"ita-html\"><span class=\"ita-Wordwrap ita-tooltip\" title=\"$decode\">" . substr($dato['ITDCTR'], 0, 50) . "</span></div>";
        }

        return $datiAgg;
    }

    /**
     * Inserisce o aggiorna dati Aggiuntivi sul DB
     * @return boolean
     */
    function RegistraDatiAggiuntivi($model) {
        $praLib = new praLib();
        if ($this->datiAggiuntivi) {
            foreach ($this->datiAggiuntivi as $datoAggiuntivo) {
                $datoAggiuntivo['ITECOD'] = $this->procedimento;
                $datoAggiuntivo['ITEKEY'] = $this->chiavePasso;
                if (is_array($datoAggiuntivo['ITDMETA'])) {
                    $datoAggiuntivo['ITDMETA'] = serialize($datoAggiuntivo['ITDMETA']);
                }
                if ($datoAggiuntivo['ROWID'] == "") {
                    $insert_Info = "Oggetto: Inserisco il dato aggiuntivo " . $datoAggiuntivo['ITDKEY'] . " del passo" . $datoAggiuntivo['ITEKEY'];
                    if (!$model->insertRecord($praLib->getPRAMDB(), 'ITEDAG', $datoAggiuntivo, $insert_Info)) {
                        return "Errore Inserimento dato aggiuntivo " . $datoAggiuntivo['ITDKEY'];
                    }
                } else {
                    $update_Info = "Oggetto: Aggiorno il dato aggiuntivo " . $datoAggiuntivo['ITDKEY'] . " del passo" . $datoAggiuntivo['ITEKEY'];
                    if (!$model->updateRecord($praLib->getPRAMDB(), 'ITEDAG', $datoAggiuntivo, $update_Info)) {
                        return "Errore Aggiornamento dato aggiuntivo " . $datoAggiuntivo['ITDKEY'];
                    }
                }
            }
        }
        return true;
    }

    function ordinaDatiAggiuntivi($model, $itekey) {
        $praLib = new praLib();
        if ($itekey) {
            $new_seq = 0;
            $Itedag_tab = $praLib->GetItedag($itekey, "itekey", true, "ORDER BY ITDSEQ");
            if ($Itedag_tab) {
                foreach ($Itedag_tab as $Itedag_rec) {
                    $new_seq +=10;
                    $Itedag_rec['ITDSEQ'] = $new_seq;
                    $update_Info = "Oggetto: Riordino sequenza dato aggiuntivo " . $Itedag_rec['ITDKEY'] . " del passo" . $Itedag_rec['ITEKEY'];
                    if (!$model->updateRecord($praLib->getPRAMDB(), 'ITEDAG', $Itedag_rec, $update_Info)) {
                        return false;
                    }
                }
            }
            return true;
        }
    }

    /**
     * Cancella dato aggiuntivo sia da array che da DB
     * @param int $rowid
     * @param obj $model
     * @return boolean
     */
    function CancellaDatoAggiuntivo($index, $model) {
        $praLib = new praLib();
        if (array_key_exists($index, $this->datiAggiuntivi) == true) {
            if ($this->datiAggiuntivi[$index]['ROWID'] != 0) {
                $delete_Info = 'Oggetto: Cancellazione dato aggiuntivo' . $this->datiAggiuntivi[$index]['DESNUM'];
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'ITEDAG', $this->datiAggiuntivi[$index]['ROWID'], $delete_Info)) {
                    return false;
                }
            }
            unset($this->datiAggiuntivi[$index]);
        }
        return true;
    }

    /**
     * Cancella tutti i dati aggiuntivi di una pratica
     * @param obj $model
     * @return boolean
     */
    public function CancellaDatiAggiuntivi($model) {
        $praLib = new praLib();
        if ($this->datiAggiuntivi) {
            $delete_Info = "Oggetto: Cancellazione di tutti i dati aggiuntivi della pratica $this->pratica";
            foreach ($this->datiAggiuntivi as $index => $datoAggiuntivo) {
                if (!$model->deleteRecord($praLib->getPRAMDB(), 'ITEDAG', $datoAggiuntivo['ROWID'], $delete_Info)) {
                    return false;
                }
                unset($this->datiAggiuntivi[$index]);
            }
        }
        return true;
    }

}

?>
