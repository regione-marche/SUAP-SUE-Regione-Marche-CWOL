<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';

class praLibChiusuraMassiva {

    public $praLib;
    public $PRAM_DB;
    private $errMessage;
    private $errCode;

    function __construct() {
        $this->praLib = new praLib();
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getArrayFascicoliTmp($sql) {
        $arrayOrd = $this->praLib->GetOrdinamentoGridGest("GESNUM", "desc");
        $ordinamento = $arrayOrd['sidx'];
        $sord = $arrayOrd['sord'];
        return $this->praLib->getGenericTab($sql . " ORDER BY " . $ordinamento . " " . $sord);
    }

    function getMsgInputChiudiFascicolo($nameForm, $fascicoliSel, $event) {
        $msgCount = "";
        if ($fascicoliSel) {
            $msgCount = "<span style=\"color:red;font-size:1.2em;\"><b>Attenzione!! In precedenza sono stati selezionati " . count($fascicoliSel) . " fascicoli che verranno chiusi.</b></span>";
        }
        Out::msgInput(
                'Chiusura Fascicioli', array(
            array(
                'label' => array('style' => "width:100px;", 'value' => 'Stato  '),
                'id' => $nameForm . '_statoChiusura',
                'name' => $nameForm . '_statoChiusura',
                'class' => "ita-edit-lookup ita-edit-onblur",
                'size' => '6',
                'br' => false,
            ),
            array(
                'label' => array('value' => ''),
                'id' => $nameForm . '_descStato',
                'name' => $nameForm . '_descStato',
                'class' => "ita-readonly",
                'size' => '50',
            ),
            array(
                'label' => array('style' => "width:100px;", 'value' => 'Data Chiusura  '),
                'id' => $nameForm . '_dataChiusura',
                'name' => $nameForm . '_dataChiusura',
                'class' => "ita-date",
                'value' => date("Ymd"),
                'size' => '10',
            )
                ), array(
            'F5-Conferma' => array('id' => $event, 'model' => $nameForm, 'shortCut' => "f5")
                ), $nameForm, 'auto', 'auto', true, $msgCount, "", true
        );
    }

    public function ChiudiFascicoli($fascicoliSel, $dataChiusura, $statoChiusura) {
        if (!$fascicoliSel) {
            $this->setErrMessage("Fascicoli selezionati non trovati.");
            return false;
        }
        if ($dataChiusura == "" || $statoChiusura == "") {
            $this->setErrMessage("Compilare entrambi i campi richiesti.");
            return false;
        }
        $anastp_rec = $this->praLib->GetAnastp($statoChiusura);
        if (!$anastp_rec) {
            $this->setErrMessage("Record stato fascicolo non trovato.");
            return false;
        }
        $msgErr = "";
        $countFascicoli = count($fascicoliSel);
        $chiusi = 0;
        foreach ($fascicoliSel as $rowid) {
            $proges_rec = $this->praLib->GetProges($rowid, "rowid");
            if (!$proges_rec) {
                $msgErr .= "Record fascicolo n. " . $proges_rec['SERIEPROGRESSIVO'] . "/" . $proges_rec['SERIEANNO'] . " non trovato.<br>";
            } else {
                if ($proges_rec['GESDCH'] == '') {
                    if ($dataChiusura < $proges_rec['GESDRE']) {
                        $msgErr .= "La data di chiusura non può essere inferiore alla data di registrazione del fascicolo n. " . $proges_rec['SERIEPROGRESSIVO'] . "/" . $proges_rec['SERIEANNO'] . "<br>";
                        continue;
                    }
                    if (!$this->ChiudiFascicolo($proges_rec['GESNUM'], $anastp_rec['ROWID'], $dataChiusura)) {
                        $msgErr .= "Impossibile chiudere il fascicolo n. " . $proges_rec['SERIEPROGRESSIVO'] . "/" . $proges_rec['SERIEANNO'] . "<br>";
                        continue;
                    }
                    $chiusi++;
                }
            }
        }
        if ($msgErr) {
            $this->setErrMessage("Chiusi $chiusi di $countFascicoli<br>" . $msgErr);
            return false;
        }
        return true;
    }

    function ChiudiFascicolo($gesnum, $rowidSportello, $dataChiusura) {
        $praFascicolo = new praFascicolo($gesnum);
        if (!$praFascicolo->AnnullaChiudiPratica($rowidSportello, "CHIUDI", $dataChiusura)) {
            return false;
        }
        return true;
    }

}
