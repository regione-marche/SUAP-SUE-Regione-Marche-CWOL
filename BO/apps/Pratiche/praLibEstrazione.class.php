<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibEstrazione {

    public $praLib;
    public $PRAM_DB;
    public $fieldPercentuale = '_Percentuale';
    public $fieldOrdinamento = '_Ordinamento';
    public $buttonStampa = '_EstrazioneStampa';
    private $errMessage;
    private $errCode;
    private $ordinamentoOptions = array(
        array('O01', 'Data Ricezione'),
        array('O02', 'Numero Protocollo')
    );
    private $ordinamentoFields = array(
        'O01' => 'GESDRI',
        'O02' => 'GESNPR'
    );
    private $ordinamentoValue;

    function __construct() {
        $this->praLib = new praLib();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getPRAMDB() {
        if (!$this->PRAM_DB) {
            try {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }

        return $this->PRAM_DB;
    }

    public function msgInputEstrazione($nameForm) {
        Out::msgInput('Estrazione', array(
            array(
                'label' => array(
                    'value' => "Percentuale (%)",
                    'style' => 'width: 120px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $nameForm . $this->fieldPercentuale,
                'name' => $nameForm . $this->fieldPercentuale,
                'type' => 'text',
                'size' => 3,
                'maxlength' => 3,
                'style' => 'margin-left: 15px; text-align: right;'
            ), array(
                'label' => array(
                    'value' => "Ordinamento",
                    'style' => 'width: 120px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $nameForm . $this->fieldOrdinamento,
                'name' => $nameForm . $this->fieldOrdinamento,
                'type' => 'select',
                'options' => $this->ordinamentoOptions,
                'style' => 'margin-left: 15px;'
            )
                ), array(
            'Estrai' => array(
                'id' => $nameForm . $this->buttonStampa,
                'model' => $nameForm,
                'shortCut' => "f5"
            )
                ), $nameForm);
    }

    private function ordinamentoEstrazione($a, $b) {
        $cmp = $a[$this->ordinamentoValue] - $b[$this->ordinamentoValue];
        return $cmp > 0 ? 1 : ($cmp < 0 ? -1 : 0);
    }

    public function stampaEstrazione($sql, $percentuale, $ordinamento) {
        if (!$percentuale || intval($percentuale) > 100 || intval($percentuale) === 0) {
            Out::msgStop("Errore", "Percentuale non valida");
            return false;
        }

        $this->ordinamentoValue = $this->ordinamentoFields[$ordinamento];

        $estrazione_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        shuffle($estrazione_tab);

        $estrazione_tab_percentuale = array_slice($estrazione_tab, 0, ceil((count($estrazione_tab) / 100) * intval($percentuale)));

        foreach ($estrazione_tab_percentuale as &$estrazione_rec_percentuale) {
            /*
             * Modifico il campo Numero protocollo per fargli avere un peso
             * corretto e poterlo utilizzare nel riordinamento
             */
            if ($estrazione_rec_percentuale['GESNPR']) {
                $estrazione_rec_percentuale['GESNPR'] = str_pad(substr($estrazione_rec_percentuale['GESNPR'], 0, 4), 4, "0", STR_PAD_LEFT) . str_pad(trim(substr($estrazione_rec_percentuale['GESNPR'], 4)), 6, "0", STR_PAD_LEFT);
            }
        }

        usort($estrazione_tab_percentuale, array($this, 'ordinamentoEstrazione'));

        $ita_grid02 = new TableView('', array(
            'arrayTable' => $this->elaboraRecordXLS($estrazione_tab_percentuale)
        ));

        $ita_grid02->exportXLS('', 'pratiche.xls');
    }

    public function stampaEstrazioneRichieste($sql, $percentuale, $ordinamento) {
        if (!$percentuale || intval($percentuale) > 100 || intval($percentuale) === 0) {
            Out::msgStop("Errore", "Percentuale non valida");
            return false;
        }

        $this->ordinamentoValue = $this->ordinamentoFields[$ordinamento];

        $estrazione_tab = ItaDB::DBSQLSelect($this->getPRAMDB(), $sql, true);
        shuffle($estrazione_tab);

        $estrazione_tab_percentuale = array_slice($estrazione_tab, 0, ceil((count($estrazione_tab) / 100) * intval($percentuale)));

        foreach ($estrazione_tab_percentuale as &$estrazione_rec_percentuale) {
            /*
             * Modifico il campo Numero protocollo per fargli avere un peso
             * corretto e poterlo utilizzare nel riordinamento
             */
            if ($estrazione_rec_percentuale['RICNPR']) {
                $estrazione_rec_percentuale['RICNPR'] = str_pad(substr($estrazione_rec_percentuale['RICNPR'], 0, 4), 4, "0", STR_PAD_LEFT) . str_pad(trim(substr($estrazione_rec_percentuale['RICNPR'], 4)), 6, "0", STR_PAD_LEFT);
            }
        }

        usort($estrazione_tab_percentuale, array($this, 'ordinamentoEstrazione'));

        $ita_grid02 = new TableView('', array(
            'arrayTable' => $this->elaboraRecordRichiesteXLS($estrazione_tab_percentuale)
        ));

        $ita_grid02->exportXLS('', 'richieste.xls');
    }

    private function elaboraRecordXLS($Result_tab) {
        $Result_tab_new = array();

        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab_new[$key]['PRATICA'] = substr($Result_rec['GESNUM'], 4, 6) . "/" . substr($Result_rec['GESNUM'], 0, 4);
            $Result_tab_new[$key]['RICHIESTA_ONLINE'] = "";

            if ($Result_rec['GESPRA']) {
                $Result_tab_new[$key]['RICHIESTA_ONLINE'] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
            }

            $Result_tab_new[$key]["DATA"] = substr($Result_rec['GESDRE'], 6, 2) . "/" . substr($Result_rec['GESDRE'], 4, 2) . "/" . substr($Result_rec['GESDRE'], 0, 4);

            if ($Result_rec['GESDRI']) {
                $Result_tab_new[$key]["DATA_RICEZIONE"] = substr($Result_rec['GESDRI'], 6, 2) . "/" . substr($Result_rec['GESDRI'], 4, 2) . "/" . substr($Result_rec['GESDRI'], 0, 4);
            } else {
                $Result_tab_new[$key]["DATA_RICEZIONE"] = "";
            }

            if ($Result_rec['GESNPR']) {
                $Result_tab_new[$key]["NUMERO_PROTOCOLLO"] = substr($Result_rec['GESNPR'], 0, 4) . '/' . substr($Result_rec['GESNPR'], 4);
            } else {
                $Result_tab_new[$key]["NUMERO_PROTOCOLLO"] = "";
            }

            $resp_rec = $this->praLib->GetAnanom($Result_rec['GESRES']);
            $Result_tab_new[$key]["RESPONSABILE"] = $resp_rec['NOMCOG'] . " " . $resp_rec['NOMNOM'];
            $Anades_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            $Result_tab_new[$key]["INTESTATARIO"] = "";
            $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = "";

            if ($Anades_rec) {
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = $Anades_rec['DESTEL'];
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["TELEFONO_INTESTATARIO"] = $Anades_rec['DESTEL'];
            }

            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab_new[$key]['IMPRESA'] = $datiInsProd['IMPRESA'];
            $Result_tab_new[$key]['INDIRIZZO_IMPRESA'] = $datiInsProd['INDIRIZZO'];
            $Result_tab_new[$key]['FISCALE_IMPRESA'] = $datiInsProd['FISCALE'];

            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab_new[$key]['SETTORE'] = $anaset_rec['SETDES'];
                $Result_tab_new[$key]['ATTIVITA'] = $anaatt_rec['ATTDES'];
                $Result_tab_new[$key]['PROCEDIMENTO'] = $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
                $Result_tab_new[$key]['OGGETTO'] = $Result_rec['GESOGG'];
            }

            $Result_tab_new[$key]["NOTE"] = $Result_rec['GESNOT'];
            $Result_tab_new[$key]["AGGREGATO"] = $Result_tab[$key]["SPORTELLO"] = "";

            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab_new[$key]["SPORTELLO"] = $anatsp_rec['TSPDES'];
            }
        }

        return $Result_tab_new;
    }

    function elaboraRecordRichiesteXLS($Result_tab) {
        $Result_tab_new = array();
        foreach ($Result_tab as $key => $Result_rec) {
            //$Result_tab_new[$key] = $Result_rec;

            $Result_tab_new[$key]['RICHIESTA'] = $Result_rec['RICNUM'];
            $Result_tab_new[$key]['DESCRIZIONE'] = $Result_rec['PRADES__1'];
            $Result_tab_new[$key]['DATA_INIZIO'] = substr($Result_rec['RICDRE'], 6, 2) . "/" . substr($Result_rec['RICDRE'], 4, 2) . "/" . substr($Result_rec['RICDRE'], 0, 4);
            $Result_tab_new[$key]['ORA_INIZIO'] = $Result_rec['RICORE'];
            $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['RICTSP']);
            $Result_tab_new[$key]['SPORTELLO'] = $anatsp_rec['TSPDES'];
            $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['RICSPA']);
            $Result_tab_new[$key]['AGGREGATO'] = $anaspa_rec['SPADES'];
            $Result_tab_new[$key]['FISCALE'] = $Result_rec['RICFIS'];
            $Result_tab_new[$key]['NUMERO_PROTOCOLLO'] = $Result_rec['NUM_PROTOCOLLO'];
            $Result_tab_new[$key]['DATA_PROTOCOLLO'] = substr($Result_rec['DATA_PROTOCOLLO'], 6, 2) . "/" . substr($Result_rec['DATA_PROTOCOLLO'], 4, 2) . "/" . substr($Result_rec['DATA_PROTOCOLLO'], 0, 4);
            $Result_tab_new[$key]['COGNOME'] = $Result_rec['RICCOG'];
            $Result_tab_new[$key]['NOME'] = $Result_rec['RICNOM'];
            $Result_tab_new[$key]['MAIL'] = $Result_rec['RICEMA'];
            $Result_tab_new[$key]['DATA_INOLTRO'] = substr($Result_rec['RICDAT'], 6, 2) . "/" . substr($Result_rec['RICDAT'], 4, 2) . "/" . substr($Result_rec['RICDAT'], 0, 4);
            $Result_tab_new[$key]['ORA_INOLTRO'] = $Result_rec['RICTIM'];
        }
        return $Result_tab_new;
    }

}
