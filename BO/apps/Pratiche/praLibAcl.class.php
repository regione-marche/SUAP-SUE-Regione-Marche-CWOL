<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini 
 * @copyright  1987-2020 Italsoft snc
 * @license
 * @version    04.02.2020
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAudit.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiGridMetaData.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

class praLibAcl {

    /**
     * Libreria di funzioni Generiche per le ACL
     *
     */
    const ACL_CAMBIO_ESIBENTE = "ACL_CAMBIO_ESIBENTE";
    const ACL_GESTIONE_PASSO = "ACL_GESTIONE_PASSO";
    const ACL_INTEGRAZIONE = "ACL_INTEGRAZIONE";
    const ACL_VISIBILITA = "ACL_VISIBILITA";
    const ARRAY_ACL_CAMBIO_ESIBENTE = "0";
    const ARRAY_ACL_GESTIONE_PASSO = "1";
    const ARRAY_ACL_INTEGRAZIONE = "2";
    const ARRAY_ACL_VISIBILITA = "3";

    public static $TIPI_ACL = array(
        self::ARRAY_ACL_CAMBIO_ESIBENTE => array(
            'CHIAVE' => self::ACL_CAMBIO_ESIBENTE,
            'VALORE' => "N",
        ),
        self::ARRAY_ACL_GESTIONE_PASSO => array(
            'CHIAVE' => self::ACL_GESTIONE_PASSO,
            'VALORE' => "N",
        ),
        self::ARRAY_ACL_INTEGRAZIONE => array(
            'CHIAVE' => self::ACL_INTEGRAZIONE,
            'VALORE' => "N",
        ),
        self::ARRAY_ACL_VISIBILITA => array(
            'CHIAVE' => self::ACL_VISIBILITA,
            'VALORE' => "N",
        ),
    );
    public $praLib;
    public $PRAM_DB;
    private $errCode;
    private $errMessage;

    function __construct() {
        try {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->praLib = new praLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
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

    public function caricaGridCondivisioneAccessi($container) {
        $model = 'utiGridMetaData';
        $utiGridMetaData = itaFormHelper::innerForm($model, $container);
        $utiGridMetaData->setEvent('openform');
        $utiGridMetaData->parseEvent();
        return $utiGridMetaData->getNameForm();
    }

    public function popolaGridCondivisioneAccessi($metaDati, $utiGridMetaDataNameForm) {
        $meta = array();
        if (!$metaDati) {
            $meta = self::$TIPI_ACL;
        } else {
            $arrayACL = json_decode($metaDati, true);
            $i = 0;
            foreach ($arrayACL['ACL'] as $key => $value) {
                $meta[$i]['CHIAVE'] = $key;
                $meta[$i]['VALORE'] = $value;
                $i++;
            }
        }
        $utiGridMetaData = itaModel::getInstance('utiGridMetaData', $utiGridMetaDataNameForm);
        $utiGridMetaData->setMetaData($meta);
        $utiGridMetaData->CaricaGriglia();
    }

    public function salvaMetadatiGridCondivisioneAccessi($utiGridMetaDataNameForm) {
        $utiGridMetaData = itaModel::getInstance('utiGridMetaData', $utiGridMetaDataNameForm);
        $gridMetadata_tmp = $utiGridMetaData->getMetaData();
        $gridMetadata = array_combine(array_column($gridMetadata_tmp, 'CHIAVE'), array_column($gridMetadata_tmp, 'VALORE'));

        $arrayACL1 = array(
            "ACL_CAMBIO_ESIBENTE" => $gridMetadata['ACL_CAMBIO_ESIBENTE'],
            "ACL_INTEGRAZIONE" => $gridMetadata['ACL_INTEGRAZIONE'],
            "ACL_VISIBILITA" => $gridMetadata['ACL_VISIBILITA'],
            "ACL_GESTIONE_PASSO" => $gridMetadata['ACL_GESTIONE_PASSO'],
        );

        $arrayACL = array(
            "ACL" => $arrayACL1,
        );


        /*
         * Trasformo arrayACL in Json
         */
        $valJson = json_encode($arrayACL);

        return $valJson;
    }

    //public function cessaSoggetto($ricsoggetti_rec, $rowidProric, $rowidPasso = 0) {
    public function cessaSoggetto($ricsoggetti_rec, $rowidProric) {
        $praLibAudit = new praLibAudit();
        $eqAudit = new eqAudit();
        //
        try {
            if ($ricsoggetti_rec) {
                $ricsoggetti_rec['SOGRICDATA_FINE'] = date("Ymd");
                $nrow = ItaDB::DBUpdate($this->PRAM_DB, 'RICSOGGETTI', 'ROW_ID', $ricsoggetti_rec);
                if ($nrow == 0) {
                    $this->setErrMessage("Errore Aggiornamento Cessazione Soggetto " . $ricsoggetti_rec['SOGRICFIS']);
                    return false;
                }

                $estremi = "Cambio Esibente: Sincronizzo: Cessato Soggetto in RICSOGGETTI con codice Fiscale " . $ricsoggetti_rec['SOGRICFIS'] . " per la richiesta n. " . $ricsoggetti_rec['SOGRICNUM'];
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    //'ROWID_PASSO' => $rowidPasso,
                    'RICFIS' => $ricsoggetti_rec['SOGRICFIS'],
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_UPD_RECORD,
                    'Estremi' => $estremi
                ));

                $eqAudit->logEqEvent($this, array(
                    'Operazione' => eqAudit::OP_UPD_RECORD,
                    'DB' => $this->PRAM_DB->getDB(),
                    'DSet' => 'PRORIC',
                    'Estremi' => $estremi
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Cessazione Soggetto: " . $ricsoggetti_rec['SOGRICFIS'] . "-->" . $e->getMessage());
            return false;
        }

        return true;
    }

    //public function caricaSoggetto($arraySoggetto, $rowidProric, $rowidPasso = 0) {
    public function caricaSoggetto($arraySoggetto, $rowidProric) {
        $praLibAudit = new praLibAudit();
        $eqAudit = new eqAudit();
        //
        $arraySoggetto['SOGRICFIS'] = strtoupper($arraySoggetto['SOGRICFIS']);
        try {
            if ($arraySoggetto['SOGRICUUID']) {
                $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICUUID = '" . $arraySoggetto['SOGRICUUID'] . "' ";
            } else {
                $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = " . $arraySoggetto['SOGRICNUM'] . " ";
            }
            $sql .= " AND SOGRICFIS = '" . $arraySoggetto['SOGRICFIS'] . "' AND SOGRICRUOLO = '" . $arraySoggetto['SOGRICRUOLO'] . "' "
                    . " AND SOGRICDATA_FINE = '' ";
            $soggetto_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if (!$soggetto_rec) {
                $nrow = ItaDB::DBInsert($this->PRAM_DB, "RICSOGGETTI", 'ROW_ID', $arraySoggetto);
                if ($nrow != 1) {
                    $this->setErrMessage("Errore Caricamento Soggetto: " . $arraySoggetto['SOGRICFIS']);
                    return false;
                }

                $estremi = "Cambio Esibente: Inserito Soggetto in RICSOGGETTI con codice Fiscale " . $arraySoggetto['SOGRICFIS'] . " per la richiesta n. " . $arraySoggetto['SOGRICNUM'];
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    //'ROWID_PASSO' => $rowidPasso,
                    'RICFIS' => $arraySoggetto['SOGRICFIS'],
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_INS_RECORD,
                    'Estremi' => $estremi
                ));

                $eqAudit->logEqEvent($this, array(
                    'Operazione' => eqAudit::OP_INS_RECORD,
                    'DB' => $this->PRAM_DB->getDB(),
                    'DSet' => 'PRORIC',
                    'Estremi' => $estremi
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Caricamento Soggetto: " . $arraySoggetto['SOGRICFIS'] . "-->" . $e->getMessage());
            return false;
        }
        return true;
    }

}
