<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    11.03.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';

class proLibTabDag {

    public $PROT_DB;
    private $errCode;
    private $errMessage;
    private $risultatoRitorno;

    function __construct() {
        
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getRisultatoRitorno() {
        return $this->risultatoRitorno;
    }

    public function setRisultatoRitorno($risultatoRitorno) {
        $this->risultatoRitorno = $risultatoRitorno;
    }

    public function getRisultatoRitornoRowidAggiunti() {
        return $this->risultatoRitorno['ROWIDAGGIUNTI'];
    }

    public function InserisciTabDagSdi($Anapro_rec, $ObgSdi) {
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione protocollo non definito.");
            return false;
        }
        if (!$ObgSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione SDI non definito.");
            return false;
        }
        //Salvo tag fattura
        if ($ObgSdi->isFatturaPA()) {
            if (!$this->SalvataggioTagFatturaSdi($Anapro_rec, $ObgSdi)) {
                return false;
            }
        }
        // Salvo tag messaggio
        if ($ObgSdi->isMessaggioSdi()) {
            if (!$this->SalvataggioTagMessaggioSdi($Anapro_rec, $ObgSdi)) {
                return false;
            }
        }
        return true;
    }

    public function CancellaTabDagSdi($Anapro_rec, $Fonte) {
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione protocollo non definito.");
            return false;
        }
        if (!$Fonte) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione Fonte non definita.");
            return false;
        }
        $TabDag_tab = $this->GetTabdag('ANAPRO', 'codice', $Anapro_rec['ROWID'], '', '', true, '', $Fonte);
        //  @TODO  Audit da inserire?        
        //    $Audit_Info = 'Oggetto: Inizio cancellazione metadati allegato SDI. Fonte: ' . $Fonte . '; Protocollo: ' . $Anapro_rec['PRONUM'] . '; Numero di Metadati: ' . count($TabDag_tab) . ';';
        //    $this->insertAudit($this->getPROTDB(), 'TABDAG', $Audit_Info);
        foreach ($TabDag_tab as $TabDag_rec) {
            try {
                ItaDB::DBDelete($this->getPROTDB(), 'TABDAG', 'ROWID', $TabDag_rec['ROWID']);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in cancellazione TABDAG.<br> " . $e->getMessage());
                return false;
            }
        }
//        $Audit_Info = 'Oggetto: Terminata cancellazione metadati allegato SDI. Fonte: ' . $Fonte . '; Protocollo: ' . $Anapro_rec['PRONUM'] . '.';
//        $this->insertAudit($this->getPROTDB(), 'TABDAG', $Audit_Info);
        return true;
    }

    public function SalvataggioTagFatturaSdi($Anapro_rec, $ObgSdi, $ForzaFonte = '') {
        $TClasse = 'ANAPRO';
        $TFonte = 'FATT_ELETTRONICA';
        if ($ForzaFonte) {
            $TFonte = $ForzaFonte;
        }
        // Prendo L'Estratto Fattura
        $EstrattoFattura = $ObgSdi->getEstrattoFattura();
        $NProg = 0;
        foreach ($EstrattoFattura as $Fattura) {
            $Seqenza = 0;
            foreach ($Fattura['Body'] as $BodyFattura) {
                $NProg++;
                // INSERISCO TAG FORNITORE
                // CLASSE[20].ROWIDCLASSE[11].NPROG[7]
                $TdagSet = $TClasse . '.' . $Anapro_rec['ROWID'] . '.' . $NProg;
                //
                //Preparo Tag da inserire
                // -> Denominazione Fornitore
                $TabDag_rec = array();
                $TabDag_rec['TDCLASSE'] = $TClasse;
                $TabDag_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
                $TabDag_rec['TDAGCHIAVE'] = 'Fornitore_Denominazione';
                $TabDag_rec['TDPROG'] = $NProg;
                $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['Denominazione'];
                $Seqenza = $Seqenza + 10;
                $TabDag_rec['TDAGSEQ'] = $Seqenza;
                $TabDag_rec['TDAGFONTE'] = $TFonte;
                $TabDag_rec['TDAGSET'] = $TdagSet;
                if (!$this->InserisciTabDag($TabDag_rec)) {
                    return false;
                }
                // -> ID Codice Fiscale Fornitore
                // (Modifico solo i valori che cambiano)
                $TabDag_rec['TDAGCHIAVE'] = 'Fornitore_Codice';
                $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['IdCodice'];
                $Seqenza = $Seqenza + 10;
                $TabDag_rec['TDAGSEQ'] = $Seqenza;
                if (!$this->InserisciTabDag($TabDag_rec)) {
                    return false;
                }
                // -> Cognome Fornitore
                if (isset($Fattura['Header']['Fornitore']['Cognome'])) {
                    $TabDag_rec['TDAGCHIAVE'] = 'Fornitore_Cognome';
                    $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['Cognome'];
                    $Seqenza = $Seqenza + 10;
                    $TabDag_rec['TDAGSEQ'] = $Seqenza;
                    if (!$this->InserisciTabDag($TabDag_rec)) {
                        return false;
                    }
                }
                // -> Nome Fornitore
                if (isset($Fattura['Header']['Fornitore']['Nome'])) {
                    $TabDag_rec['TDAGCHIAVE'] = 'Fornitore_Nome';
                    $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['Nome'];
                    $Seqenza = $Seqenza + 10;
                    $TabDag_rec['TDAGSEQ'] = $Seqenza;
                    if (!$this->InserisciTabDag($TabDag_rec)) {
                        return false;
                    }
                }
                // -> IdFiscaleIVA
                $TabDag_rec['TDAGCHIAVE'] = 'IdFiscaleIVA';
//                $TabDag_rec['TDAGVAL'] = $Fattura['IdFiscaleIVA'];
                $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['IdFiscaleIVA'];
                $Seqenza = $Seqenza + 10;
                $TabDag_rec['TDAGSEQ'] = $Seqenza;
                if (!$this->InserisciTabDag($TabDag_rec)) {
                    return false;
                }
                // -> CodiceFiscale
                $TabDag_rec['TDAGCHIAVE'] = 'CodiceFiscale';
//                $TabDag_rec['TDAGVAL'] = $Fattura['CodiceFiscale'];
                $TabDag_rec['TDAGVAL'] = $TabDag_rec['TDAGVAL'] = $Fattura['Header']['Fornitore']['CodiceFiscale'];
                $Seqenza = $Seqenza + 10;
                $TabDag_rec['TDAGSEQ'] = $Seqenza;
                if (!$this->InserisciTabDag($TabDag_rec)) {
                    return false;
                }
                // -> FileFatturaUnivoco
                $TabDag_rec['TDAGCHIAVE'] = 'FileFatturaUnivoco';
                $TabDag_rec['TDAGVAL'] = $Fattura['FileFatturaUnivoco'];
                $Seqenza = $Seqenza + 10;
                $TabDag_rec['TDAGSEQ'] = $Seqenza;
                if (!$this->InserisciTabDag($TabDag_rec)) {
                    return false;
                }
                //Preparo le chiavi
                $TabDag_rec = array();
                foreach ($BodyFattura as $ChiaveElemento => $ElementoFattura) {
                    //Preparo Tag da inserire
                    $TabDag_rec['TDCLASSE'] = 'ANAPRO';
                    $TabDag_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
                    $TabDag_rec['TDAGCHIAVE'] = $ChiaveElemento;
                    $TabDag_rec['TDPROG'] = $NProg;
                    $TabDag_rec['TDAGVAL'] = $ElementoFattura;
                    $Seqenza = $Seqenza + 10;
                    $TabDag_rec['TDAGSEQ'] = $Seqenza;
                    $TabDag_rec['TDAGFONTE'] = $TFonte;
                    $TabDag_rec['TDAGSET'] = $TdagSet;
                    if (!$this->InserisciTabDag($TabDag_rec)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function SalvataggioTagMessaggioSdi($Anapro_rec, $ObgSdi, $ForzaFonte = '') {
        $this->risultatoRitorno = array();
        $TClasse = 'ANAPRO';
        $TFonte = 'MESSAGGIO_SDI';
        if ($ForzaFonte) {
            $TFonte = $ForzaFonte;
        }
        // Prendo L'Estratto Fattura
        $EstrattoMessaggio = $ObgSdi->getEstrattoMessaggio();
        $NProg = $this->GetProssimoProgFonte($TClasse, $Anapro_rec['ROWID'], $TFonte);
        $Seqenza = 0;
        foreach ($EstrattoMessaggio as $TagMessaggio => $ValoreTagMessaggio) {
            // CLASSE[20].ROWIDCLASSE[11].NPROG[7]
            $TdagSet = $TClasse . '.' . $Anapro_rec['ROWID'] . '.' . $NProg;
            $TabDag_rec = array();
            //Preparo Tag da inserire
            $TabDag_rec['TDCLASSE'] = 'ANAPRO';
            $TabDag_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
            $TabDag_rec['TDAGCHIAVE'] = $TagMessaggio;
            $TabDag_rec['TDPROG'] = $NProg;
            $TabDag_rec['TDAGVAL'] = $ValoreTagMessaggio;
            $Seqenza = $Seqenza + 10;
            $TabDag_rec['TDAGSEQ'] = $Seqenza;
            $TabDag_rec['TDAGFONTE'] = $TFonte;
            $TabDag_rec['TDAGSET'] = $TdagSet;
            $rowidIns = $this->InserisciTabDag($TabDag_rec);
            if (!$rowidIns) {
                return false;
            }
            $this->risultatoRitorno['ROWIDAGGIUNTI'][] = $rowidIns;
        }
        return true;
    }

    private function InserisciTabDag($Tabdag_rec) {
        try {
            ItaDB::DBInsert($this->getPROTDB(), 'TABDAG', 'ROWID', $Tabdag_rec);
            return ItaDB::DBLastId($this->getPROTDB());
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento TABDAG.<br> " . $e->getMessage());
            return false;
        }
    }

    public function GetTabdag($codice, $tipo = 'codice', $rowidClasse = 0, $chiave = "", $prog = 0, $multi = false, $ordine = '', $fonte = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse";
        } elseif ($tipo == 'chiave') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse AND TDAGCHIAVE = '$chiave'";
            if ($prog) {
                $sql.=" AND TDPROG = $prog ";
            }
        } elseif ($tipo == 'progressivo') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDROWIDCLASSE = $rowidClasse AND TDAGCHIAVE = '$chiave' AND TDPROG = $prog";
        } elseif ($tipo == 'valore') {
            $sql = "SELECT * FROM TABDAG WHERE TDCLASSE = '$codice' AND TDAGCHIAVE = '$chiave' AND TDAGVAL = '$prog' ";
            if ($rowidClasse) {
                $sql.=" AND TDROWIDCLASSE = $rowidClasse ";
            }
        } else {
            $sql = "SELECT * FROM TABDAG WHERE ROWID=$codice";
        }
        if ($fonte) {
            $sql.=" AND TDAGFONTE = '$fonte' ";
        }
        if ($ordine) {
            $sql.= $ordine;
        }
//        App::log('$sql');
//        App::log($sql);
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, $multi);
    }

    public function GetValoreTabdag($codice, $tipo = 'codice', $rowidClasse = 0, $chiave = "", $prog = 0, $fonte = '') {
        $multi = false;
        $ordine = '';
        $Valore = '';
        $TabDag_rec = $this->GetTabdag($codice, $tipo, $rowidClasse, $chiave, $prog, $multi, $ordine, $fonte);
        if ($TabDag_rec) {
            $Valore = $TabDag_rec['TDAGVAL'];
        }
        // Controlli x fare capre se è vuoto? un array?
        return $Valore;
    }

    public function InserisciTabDagGiornaliero($Anapro_rec, $ArrDati) {
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione protocollo non definito.");
            return false;
        }
        if (!$ArrDati) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione Elenco Dati fondamentali mancanti.");
            return false;
        }
        //Salvo tag 
        if (!$this->SalvataggioTagGiornaliero($Anapro_rec, $ArrDati)) {
            return false;
        }
        return true;
    }

    private function SalvataggioTagGiornaliero($Anapro_rec, $ArrDati) {
        $TClasse = 'ANAPRO';
        $TFonte = proLibGiornaliero::FONTE_DATI_REGISTRO;
        // Prendo L'Estratto Fattura
        $NProg = 0;
        $Seqenza = 0;
        foreach ($ArrDati as $Chiave => $Valore) {
            // CLASSE[20].ROWIDCLASSE[11].NPROG[7]
            $TdagSet = $TClasse . '.' . $Anapro_rec['ROWID'] . '.' . $NProg;
            $TabDag_rec = array();
            //Preparo Tag da inserire
            $TabDag_rec['TDCLASSE'] = 'ANAPRO';
            $TabDag_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
            $TabDag_rec['TDAGCHIAVE'] = $Chiave;
            $TabDag_rec['TDPROG'] = $NProg;
            $TabDag_rec['TDAGVAL'] = $Valore;
            $Seqenza = $Seqenza + 10;
            $TabDag_rec['TDAGSEQ'] = $Seqenza;
            $TabDag_rec['TDAGFONTE'] = $TFonte;
            $TabDag_rec['TDAGSET'] = $TdagSet;
            if (!$this->InserisciTabDag($TabDag_rec)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param type $TClasse
     * @param type $rowidClasse
     * @param type $TFonte
     * @param type $Nprog
     * @return type
     */
    public function GetFonteTabdag($TClasse, $rowidClasse, $TFonte, $Nprog = false) {
        $arrRet = array();
        if ($Nprog === false) {
            $sql = "SELECT  TDPROG FROM TABDAG WHERE TDCLASSE = '$TClasse' AND TDROWIDCLASSE = $rowidClasse AND TDAGFONTE = '$TFonte' GROUP BY TDPROG";
            $Tabdag_prog_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        } else {
            $Tabdag_prog_tab = array(array('TDPROG' => $Nprog));
        }
        if ($Tabdag_prog_tab) {
            foreach ($Tabdag_prog_tab as $Tabdag_prog_rec) {
                $sql_set = "SELECT  * FROM TABDAG WHERE TDCLASSE = '$TClasse' AND TDROWIDCLASSE = $rowidClasse AND TDPROG={$Tabdag_prog_rec['TDPROG']} AND TDAGFONTE = '$TFonte' ORDER BY TDAGSEQ";
                $Tabdag_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql_set, true);
                if ($Tabdag_tab) {
                    $arrRet[$Tabdag_prog_rec['TDPROG']] = $Tabdag_tab;
                }
            }
        }

        return $arrRet;
    }

    public function GetProssimoProgFonte($TClasse, $rowidClasse, $TFonte) {
        $sql = "SELECT  MAX(TDPROG) AS ULTIMO FROM TABDAG WHERE TDCLASSE = '$TClasse' AND TDROWIDCLASSE = $rowidClasse AND TDAGFONTE = '$TFonte'";
        $Tabdag_prog_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        if ($Tabdag_prog_rec) {
            return $Tabdag_prog_rec['ULTIMO'] + 1;
        } else {
            return 0;
        }
    }

    /**
     * 
     * @param type $TClasse
     * @param type $rowidClasse
     * @param type $TFonte
     * @param type $ArrDati
     * @param type $Nprog
     * @return boolean
     */
    public function SalvataggioFonteTabdag($TClasse, $rowidClasse, $TFonte, $ArrDati, $NProg = 0, $update = false) {
        $Tabdag_tab = $this->GetFonteTabdag($TClasse, $rowidClasse, $TFonte, $NProg);
        App::log('$Tabdag_tab');
        App::log($Tabdag_tab);
        if ($Tabdag_tab && $update === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dataset dati aggiuntivi già presente non si può inserire.");
            return false;
        }
        if ($Tabdag_tab && $update === true) {
            foreach ($Tabdag_tab[0] as $Tabdag_rec) {
                try {
                    ItaDB::DBDelete($this->PROT_DB, 'TABDAG', 'ROWID', $Tabdag_rec['ROWID']);
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in cancellazione TABDAG.<br> " . $e->getMessage());
                    return false;
                }
            }
        }
        $Sequenza = 0;
        foreach ($ArrDati as $Chiave => $Valore) {
            $TdagSet = $TClasse . '.' . $rowidClasse . '.' . $NProg;
            $TabDag_rec = array();
            //Preparo Tag da inserire
            $TabDag_rec['TDCLASSE'] = $TClasse;
            $TabDag_rec['TDROWIDCLASSE'] = $rowidClasse;
            $TabDag_rec['TDAGCHIAVE'] = $Chiave;
            $TabDag_rec['TDPROG'] = $NProg;
            $TabDag_rec['TDAGVAL'] = $Valore;
            $Sequenza = $Sequenza + 10;
            $TabDag_rec['TDAGSEQ'] = $Sequenza;
            $TabDag_rec['TDAGFONTE'] = $TFonte;
            $TabDag_rec['TDAGSET'] = $TdagSet;
            if (!$this->InserisciTabDag($TabDag_rec)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento dati aggiuntivi esito conservazione fallito");
                return false;
            }
        }
        return true;
    }

    public function AggiornamentoTagGiornaliero($Anapro_rec, $ArrDati) {
        $TClasse = 'ANAPRO';
        $TFonte = proLibGiornaliero::FONTE_DATI_REGISTRO;
        foreach ($ArrDati as $Chiave => $Valore) {
            $TabDag_rec = $this->GetTabdag($TClasse, 'chiave', $Anapro_rec['ROWID'], $Chiave, '', false, '', $TFonte);
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore metadato: $Chiave non presente.");
                return false;
            }// Si potrebbe pensare di inserirlo.. ma c'è qualche problema grave se non esiste.
            $TabDag_rec['TDAGVAL'] = $Valore;
            if (!$this->AggiornaTabDag($TabDag_rec)) {
                return false;
            }
        }
        return true;
    }

    private function AggiornaTabDag($Tabdag_rec) {
        try {
            ItaDB::DBUpdate($this->getPROTDB(), 'TABDAG', 'ROWID', $Tabdag_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento TABDAG.<br> " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function GetValoriTabdagFonte($TClasse, $rowidClasse, $TFonte, $TProg) {
        $sql_set = "SELECT  * FROM TABDAG WHERE TDCLASSE = '$TClasse' AND TDROWIDCLASSE = $rowidClasse AND TDPROG=$TProg AND TDAGFONTE = '$TFonte' ORDER BY TDAGSEQ";
        $Tabdag_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql_set, true);
        $ArrValori = array();
        foreach ($Tabdag_tab as $Tabdag_rec) {
            $ArrValori[$Tabdag_rec['TDAGCHIAVE']] = $Tabdag_rec['TDAGVAL'];
        }
        return $ArrValori;
    }

}

?>
