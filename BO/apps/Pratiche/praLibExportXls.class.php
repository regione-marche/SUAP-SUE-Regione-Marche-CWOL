<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    23.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLib.class.php';
include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';

class praLibExportXls {

    public $PRAM_DB;
    public $praLib;
    private $errMessage;
    private $errCode;

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                $this->praLib = new praLib($ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                $this->praLib = new praLib();
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function exportXlsDefault($sql, $gridGest) {
        $Result_tab1 = $this->praLib->getGenericTab($sql);
        $Result_tab2 = $this->elaboraRecordsXls($Result_tab1);
        $ita_grid02 = new TableView($gridGest, array(
            'arrayTable' => $Result_tab2));
        $ita_grid02->setSortIndex('PRATICA');
        $ita_grid02->setSortOrder('desc');
        $ita_grid02->exportCSV('', 'pratiche.csv');
    }

    public function exportXlsAdvanced($nameForm, $nameFormOrig, $xlsxMode, $xlsxPageDescription, $xlsxDefaultModel) {
        $fields = $this->getXlsxFields();
        $model = cwbLib::apriFinestra('utiXlsxCustomizer', $nameForm, '', '', array(), $nameFormOrig);
        $model->initPage($fields, 'ITALWEB', null, $nameFormOrig . $xlsxMode, $xlsxPageDescription, $xlsxDefaultModel);
        $model->openLoadDialog();
        return;
    }

    protected function getXlsxFields() {
        $fields['PRATICA'] = array(
            'name' => 'Pratica',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['RICHIESTA_ONLINE'] = array(
            'name' => 'Richiesta Online',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DATA_REGISTRAZIONE'] = array(
            'name' => 'Data Registrazione',
            'format' => itaXlsxWriter::FORMAT_DATE
        );
        $fields['DATA_RICEZIONE'] = array(
            'name' => 'Data Ricezione',
            'format' => itaXlsxWriter::FORMAT_DATE
        );
        $fields['PROTOCOLLO'] = array(
            'name' => 'Protocollo',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['ANNO_PROTOCCOLO'] = array(
            'name' => 'Anno Protocollo',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DATA_PROTOCOLLO'] = array(
            'name' => 'Data Protocollo',
            'format' => itaXlsxWriter::FORMAT_DATE
        );
        $fields['RESPONSABILE'] = array(
            'name' => 'Responsabile',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['INTESTATARIO'] = array(
            'name' => 'Intestatario',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['RECAPITI'] = array(
            'name' => 'Recapiti',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['IMPRESA'] = array(
            'name' => 'Impresa',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['INDIRIZZO_IMPRESA'] = array(
            'name' => 'Indirizzo Impresa',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['CIVICO_IMPRESA'] = array(
            'name' => 'Civico Impresa',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['FISCALE_IMPRESA'] = array(
            'name' => 'Fiscale Impresa',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['SETTORE'] = array(
            'name' => 'Settore',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['ATTIVITA'] = array(
            'name' => 'Attivita',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['PROCEDIMENTO'] = array(
            'name' => 'Procedimento',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['OGGETTO'] = array(
            'name' => 'Oggetto',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['AGGREGATO'] = array(
            'name' => 'Aggregato',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['SPORTELLO'] = array(
            'name' => 'Sportello',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['NOTE'] = array(
            'name' => 'Note',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['DATA_CHIUSURA'] = array(
            'name' => 'Data Chiusura',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['STATO'] = array(
            'name' => 'Stato',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['MQ_TOTALI'] = array(
            'name' => 'MQ Totali',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['MQ_ALIMENTARI'] = array(
            'name' => 'MQ Alimentari',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['MQ_NON_ALIMENTARI'] = array(
            'name' => 'MQ Non Alimentari',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['MQ_SOMMINISTRAZIONE'] = array(
            'name' => 'MQ Somministrazione',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['FIERE'] = array(
            'name' => 'Fiere',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        $fields['COMUNE_DESTINAZIONE'] = array(
            'name' => 'Comune di Destinazione',
            'format' => itaXlsxWriter::FORMAT_STRING
        );
        return $fields;
    }

    public function StampaXls($model, $sql) {
        $xlsxWriter = new itaXlsxWriter($this->PRAM_DB);
        $ArrayCampi = array();
        $i = 0;
        foreach ($model as $campo) {
            if ($campo['calculated']) {
                $Result = $xlsxWriter->calculatedToArray($campo['calculated']);
                foreach ($Result as $Risultato) {
                    if ($Risultato['EXTRAFIELD']) {
                        $ArrayCampi[$i] = $Risultato['EXTRAFIELD'];
                        $i++;
                    }
                }
            }
        }
        $Result_tab1 = $this->praLib->getGenericTab($sql);
        $Result_tab2 = $this->elaboraRecordsXls($Result_tab1, $ArrayCampi);
        $xlsxWriter->setDataFromArray($Result_tab2, $sheet);
        $xlsxWriter->setRenderFieldsMetadata($model);
        $xlsxWriter->createCustom();
        $filename = $this->nameForm . time() . rand(0, 1000) . '.xlsx';
        $tempPath = itaLib::getAppsTempPath() . "/" . $filename;
        $xlsxWriter->writeToFile($tempPath);
        Out::openDocument(utiDownload::getUrl($filename, $tempPath, true));
    }

    function elaboraRecordsXls($Result_tab, $arrCampi = array()) {
        $proLib = new proLib();
        $gfmLib = new gfmLib();
        $Result_tab_new = array();
        foreach ($Result_tab as $key => $Result_rec) {
            $proges_rec = $this->praLib->GetProges($Result_rec['GESNUM']);
            $decod_sigla = $proLib->getAnaseriearc($Result_rec['SERIECODICE']);
            $Serie_rec = $decod_sigla['SIGLA'] . "/" . $Result_rec['SERIEPROGRESSIVO'] . "/" . $Result_rec['SERIEANNO'];
            $Result_tab_new[$key]['PRATICA'] = $Serie_rec;
            $Result_tab_new[$key]['RICHIESTA_ONLINE'] = "";
            $Result_tab_new[$key]['COMUNE_DESTINAZIONE'] = "";
            $Result_tab_new[$key]['PROTOCOLLO'] = substr($Result_rec['GESNPR'], 4, 6);
            $Result_tab_new[$key]['ANNO_PROTOCCOLO'] = '';
            if (substr($Result_rec['GESNPR'], 0, 4) != "0") {
                $Result_tab_new[$key]['ANNO_PROTOCCOLO'] = substr($Result_rec['GESNPR'], 0, 4);
            }

            $metaDati = unserialize($proges_rec['GESMETA']);
            $dataPrt = $this->praLib->GetDataProtNormalizzata($metaDati);
            if ($dataPrt == "") {
                $dataPrt = substr($Result_rec['GESDPR'], 6, 2) . "-" . substr($Result_rec['GESDPR'], 4, 2) . "-" . substr($Result_rec['GESDPR'], 0, 4);
            }
            $Result_tab_new[$key]['DATA_PROTOCOLLO'] = $dataPrt;
            if ($Result_rec['GESPRA']) {
                $Result_tab_new[$key]['RICHIESTA_ONLINE'] = substr($Result_rec['GESPRA'], 4, 6) . "/" . substr($Result_rec['GESPRA'], 0, 4);
                $proric_rec = $this->praLib->GetProric($Result_rec['GESPRA']);
                $anaspa_rec_dest = $this->praLib->GetAnaspa($proric_rec['RICSPA']);
                $Result_tab_new[$key]["COMUNE_DESTINAZIONE"] = $anaspa_rec_dest['SPACOM'];
            }
            $Result_tab_new[$key]["DATA_REGISTRAZIONE"] = substr($Result_rec['GESDRE'], 6, 2) . "-" . substr($Result_rec['GESDRE'], 4, 2) . "-" . substr($Result_rec['GESDRE'], 0, 4);
            $Result_tab_new[$key]["DATA_RICEZIONE"] = substr($Result_rec['GESDRI'], 6, 2) . "-" . substr($Result_rec['GESDRI'], 4, 2) . "-" . substr($Result_rec['GESDRI'], 0, 4);
            $resp_rec = $this->praLib->GetAnanom($Result_rec['GESRES']);
            $Result_tab_new[$key]["RESPONSABILE"] = $resp_rec['NOMCOG'] . " " . $resp_rec['NOMNOM'];
            $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM = '" . $Result_rec['GESNUM'] . "' AND DESRUO = '0001'", false);
            $Result_tab_new[$key]["INTESTATARIO"] = "";
            $Result_tab_new[$key]["RECAPITI"] = "";
            if ($Anades_rec) {
                $mail = $Anades_rec['DESPEC'];
                if ($mail == "") {
                    $mail = $Anades_rec['DESEMA'];
                }
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["RECAPITI"] = $Anades_rec['DESTEL'] . "\n$mail";
            } else {
                $Anades_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANADES WHERE DESNUM='" . $Result_rec['GESNUM'] . "' AND DESRUO=''", false);
                $mail = $Anades_rec['DESPEC'];
                if ($mail == "") {
                    $mail = $Anades_rec['DESEMA'];
                }
                $Result_tab_new[$key]["INTESTATARIO"] = $Anades_rec['DESNOM'];
                $Result_tab_new[$key]["RECAPITI"] = $Anades_rec['DESTEL'] . "\n$mail";
            }
            $datiInsProd = $this->praLib->DatiImpresa($Result_rec['GESNUM']);
            $Result_tab_new[$key]['IMPRESA'] = $datiInsProd['IMPRESA'];
            $Result_tab_new[$key]['INDIRIZZO_IMPRESA'] = $datiInsProd['INDIRIZZO'];
            $Result_tab_new[$key]['CIVICO_IMPRESA'] = $datiInsProd['CIVICO'];
            $Result_tab_new[$key]['FISCALE_IMPRESA'] = $datiInsProd['FISCALE'];

                $Result_tab_new[$key]['SETTORE'] = '';
                $Result_tab_new[$key]['ATTIVITA'] = '';
                $Result_tab_new[$key]['PROCEDIMENTO'] = '';
                $Result_tab_new[$key]['OGGETTO'] = '';
            if ($Result_rec['PRADES__1']) {
                $anaset_rec = $this->praLib->GetAnaset($Result_rec['GESSTT']);
                $anaatt_rec = $this->praLib->GetAnaatt($Result_rec['GESATT']);
                $Result_tab_new[$key]['SETTORE'] = $anaset_rec['SETDES'];
                $Result_tab_new[$key]['ATTIVITA'] = $anaatt_rec['ATTDES'];
                $Result_tab_new[$key]['PROCEDIMENTO'] = $Result_rec['PRADES__1'] . $Result_rec['PRADES__2'] . $Result_rec['PRADES__3'];
                $Result_tab_new[$key]['OGGETTO'] = $Result_rec['GESOGG'];
            }

            $Result_tab_new[$key]["AGGREGATO"] = $Result_tab[$key]["SPORTELLO"] = "";
            $Result_tab_new[$key]["SPORTELLO"] = "";
            if ($Result_rec['GESTSP'] != 0) {
                $anatsp_rec = $this->praLib->GetAnatsp($Result_rec['GESTSP']);
                $Result_tab_new[$key]["SPORTELLO"] = $anatsp_rec['TSPDES'];
            }
            if ($Result_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($Result_rec['GESSPA']);
                $Result_tab_new[$key]["AGGREGATO"] = $anaspa_rec['SPADES'];
            }
            $Result_tab_new[$key]["NOTE"] = $Result_rec['GESNOT'];

            $Result_tab[$key]["NUMERO_GIORNI"] = $Result_rec['NUMEROGIORNI'];
            $Result_tab_new[$key]["DATA_CHIUSURA"] = "";
            if ($Result_rec['GESDCH']) {
                $Result_tab_new[$key]["DATA_CHIUSURA"] = substr($Result_rec['GESDCH'], 6, 2) . "-" . substr($Result_rec['GESDCH'], 4, 2) . "-" . substr($Result_rec['GESDCH'], 0, 4);
            }
            $Result_tab_new[$key]["STATO"] = $this->praLib->GetImgStatoPratica($Result_rec, true);
            $altriDatiImpresa = $this->praLib->AltriDatiImpresa($Result_rec['GESNUM']);
            $Result_tab_new[$key]["MQ_TOTALI"] = $altriDatiImpresa['MQTOTALI'];
            $Result_tab_new[$key]["MQ_ALIMENTARI"] = $altriDatiImpresa['MQALIM'];
            $Result_tab_new[$key]["MQ_NON_ALIMENTARI"] = $altriDatiImpresa['MQNOALIM'];
            $Result_tab_new[$key]["MQ_SOMMINISTRAZIONE"] = $altriDatiImpresa['MQALIM'] + $altriDatiImpresa['MQNOALIM'];
            //
            $descFiere = "";
            $Result_tab_new[$key]["FIERE"] = "";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $Result_rec['GESNUM'] . "' AND DAGKEY='DENOM_FIERA'", false);
            if ($prodag_rec) {
                $arrFiere = unserialize($prodag_rec['DAGVAL']);
                foreach ($arrFiere as $rowid => $value) {
                    if ($value == 1) {
                        $fiere_rec = $gfmLib->GetFiere($rowid, "rowid");
                        $anafiere_rec = $gfmLib->GetAnafiere($fiere_rec['FIERA']);
                        $descFiere .= $anafiere_rec['FIERA'] . "<br>";
                    }
                }
                $Result_tab_new[$key]["FIERE"] = $descFiere;
            } else {
                $Result_tab_new[$key]["FIERE"] = "";
            }

            /*
             * Aggiungo alla fine i campi aggiuntivi scelti dall'operatore
             */
            if ($arrCampi) {
                foreach ($arrCampi as $campo) {
                    if ($campo) {
                        $prodag_recCampo = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='" . $Result_rec['GESNUM'] . "' AND DAGKEY='$campo' AND DAGVAL<>''", false);
                        $Result_tab_new[$key][$campo] = $prodag_recCampo['DAGVAL'];
                    }
                }
            }
        }
        return $Result_tab_new;
    }

}
