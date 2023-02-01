<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    07.09.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';

class proHalley {

    const GGLimite = 30; // 30 giorni solari, a partire dal giorno dopo di protocollo

    private $currAnapro_rec;
    private $errCode;
    private $errMessage;
    private $CodeMessage;
    private $Message;
    private $eqAudit;
    private $flAggiorna = true;
    private $errLog = array();
    private $msgLog = array();
    private $recUpdateLog = array();
    public $HALLEY_DB;

    function __construct() {
        $this->eqAudit = new eqAudit();
    }

    public function setHALLEYDB($HALLEY_DB) {
        $this->HALLEY_DB = $HALLEY_DB;
    }

    public function getHALLEYDB() {
        if (!$this->HALLEY_DB) {
            try {
                $this->HALLEY_DB = ItaDB::DBOpen('HALLEY', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->HALLEY_DB;
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

    function getErrLog() {
        return $this->errLog;
    }

    public function setErrMessage($errMessage) {
        $this->insertAudit("ERRORE: $errMessage");
        $this->errLog[] = $errMessage;
        $this->errMessage = $errMessage;
    }

    public function getCodeMessage() {
        return $this->CodeMessage;
    }

    public function setCodeMessage($CodeMessage) {
        $this->CodeMessage = $CodeMessage;
    }

    public function getMessage() {
        return $this->Message;
    }

    public function setMessage($Message) {
        $this->Message = $Message;
    }

    function getMsgLog() {
        return $this->msgLog;
    }

    function setMsgLog($msgLog) {
        $this->msgLog = $msgLog;
    }

    function getRecUpdateLog() {
        return $this->recUpdateLog;
    }

    function setRecUpdateLog($recUpdateLog) {
        $this->recUpdateLog = $recUpdateLog;
    }

    public function CaricaOggettoSdi($Anapro_rec) {
        $proLibSdi = new proLibSdi();
        $proLibTabDag = new proLibTabDag();

        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo non presente.");

            return false;
        }
        /* Controllo se parametrizzati EFAA o SDIP o SDIA */
        if (!$Anapro_rec['PROCODTIPODOC']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo documento non definito per il protocollo.");
            return false;
        }



        /* Prendo il file FatturaPa */
        //@TODO: potrebbe essere una macro 
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }

        $FileNameFattura = $TabDag_rec['TDAGVAL'];
        $RetAnadoc_export = $proLibSdi->GetExportFileFromAnadoc($FileNameFattura, $Anapro_rec);
        if (!$RetAnadoc_export) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, il file $FileNameFattura non è presente tra i Documenti.");
            return false;
        }
        $this->insertAudit("Individuato file Fattura $FileNameFattura. Inizio elaborazione oggetto fattura.");

        /*
         * Preparazione OggettoSDI FatturaPa
         */
        $FilePathFattura = $RetAnadoc_export['SOURCE'];
        $FileSdi = array('LOCAL_FILEPATH' => $FilePathFattura, 'LOCAL_FILENAME' => $FileNameFattura);
        $ExtraParamSdi = array('PARSEALLEGATI' => true);
        $objProSdi = proSdi::getInstance($FileSdi, $ExtraParamSdi);
        if (!$objProSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'istanziare oggetto fattura  proSdi.");
            return false;
        }
        if ($objProSdi->getErrCode() == -9) {
            $this->setErrCode(-1);
            $this->setErrMessage($objProSdi->getErrMessage());
            return false;
        }
        return $objProSdi;
    }

    /**
     * 
     * @param type $Anapro_rec
     * @param type $ExtraParam
     * @return boolean
     */
    public function AggiornaDatiContabilita($Anapro_rec, $ExtraParam = array()) {
        $this->msgLog = array();
        $this->currAnapro_rec = $Anapro_rec;
        $this->insertAudit("Inizio Procedura di aggiornamento.");

        $DBHalley = $this->getHALLEYDB();
        /*
         * Controllo di connessione DB:
         */
        $sql = "SELECT * FROM PFFTRAS LIMIT 1";
        try {
            $PFWFATK_tab = ItaDB::DBSQLSelect($DBHalley, $sql, true);
        } catch (Exception $ex) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore di accesso alla tabella, verificare le corrette configurazioni per l'accesso.<br>" . $ex->getMessage());
            return false;
        }

        $proLib = new proLib();
        $Anaogg_rec = $proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);

        $objProSdi = $this->CaricaOggettoSdi($Anapro_rec);
        if (!$objProSdi) {
            return false;
        }
        $FileNameFattura = $objProSdi->getNomeFileDaControllare();
        $FilesPathFattura = $objProSdi->getFilePathFattura();
        $FilePathFattura = $FilesPathFattura[0];
        $EstrattoFattura = $objProSdi->getEstrattoFattura();

        /*
         * Ricerco Fattura su PFFTRAS $FileNameFattura
         */
        try {
            $sql = "SELECT PFFTJFA FROM PFFTRAS WHERE PFFTNFI = '$FileNameFattura' ";
            $PFFTRAS_tab = ItaDB::DBSQLSelect($DBHalley, $sql, true);
        } catch (Exception $ex) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore di accesso alla tabella verificare le corrette configurazioni per l'accesso.<br>" . $ex->getMessage());
            return false;
        }
        if (!$PFFTRAS_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("File Fattura non trovato in dati trasmissione PFFTRAS");
            return false;
        }
        if (count($PFFTRAS_tab) > 10) {
            $this->setErrCode(-1);
            $this->setErrMessage("Anomalia nella lettura righe fattura. Completare la compilazione dati manualmente.");
            return false;
        }


        $insert_Info = "Estratte " . count($PFFTRAS_tab) . " fatture di riferimento nei dati trasmissione PFFTRAS per il file $FileNameFattura";
        $this->insertAudit($insert_Info);

        /*
         * Prendo il record di PFFATTU
         */
        foreach ($PFFTRAS_tab as $PFFTRAS_rec) {
            $fldPFFTJFA = $PFFTRAS_rec['PFFTJFA'];
            if ($fldPFFTJFA == '0' || !$fldPFFTJFA) {
                $this->setErrCode(-1);
                $this->setErrMessage("File Fattura non trovato in dati trasmissione PFFTRAS, codice di collegamento non presente.");
                return false;
            }
            $sql = "SELECT * FROM PFFATTU WHERE PFFSRFA ='$fldPFFTJFA' AND PFFSTAT=0 ";
            try {
                $PFFATTU_rec = ItaDB::DBSQLSelect($DBHalley, $sql, false);
            } catch (Exception $ex) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore di lettura in tabella PFFATTU verificare le corrette configurazioni per l'accesso.<br>" . $ex->getMessage());
                return false;
            }
            if (!$PFFATTU_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Registrazione Fattura con id $fldPFFTJFA non trovata.");
                return false;
            }
            // Controllo fattura non aggiornabile
            if ($PFFATTU_rec['PFFIVFL'] != 0) {
                $this->insertAudit(" fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']} NON Modificabile.");
                $this->errLog[] = " fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']} NON Modificabile.";
                continue;
            }

            // Controllo fattura trasmessa a mef
            // if ($PFFATTU_rec['PFFFMEF'] != 0) {
            //     $this->insertAudit(" fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']} NON Modificabile, tramesso a MEF.");
            //     $this->errLog[] = " fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']} NON Modificabile, tramesso a MEF.";
            //    continue;
            // }


            $this->insertAudit("Inizio aggiornamento fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']}");

            /*
             * Prendo il record di PFWFATK 
             */
//            $sql = "SELECT * FROM PFWFATK WHERE PFFSERF ='$fldPFFTJFA' ";
//            try {
//                $PFWFATK_tab = ItaDB::DBSQLSelect($DBHalley, $sql, true);
//            } catch (Exception $ex) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Errore di lettura in tabella PFWFATK verificare le corrette configurazioni per l'accesso.<br>" . $ex->getMessage());
//                return false;
//            }
//            if (!$PFWFATK_tab) {
//                $insert_Info = "Dettagli fattura PFWFATK non trovati procedo comunque aggiornando solo la testata fatture  ";
//                $this->insertAudit($insert_Info);
//            }
//            if (count($PFWFATK_tab) > 10) {
//                $this->setErrCode(-1);
//                $this->setErrMessage("Anomalia nella lettura dei dati fattura. Completare la compilazione dati manualmente.");
//                return false;
//            }

            /*
             * Oggetto fattura.
             */
            $OggettoFattura = $Anaogg_rec['OGGOGG'];
            if ($ExtraParam['OGGETTOFATTURA']) {
                $OggettoFattura = $ExtraParam['OGGETTOFATTURA'];
            }
            /*
             * Predispongo campi da aggiornare.
             */
            foreach ($EstrattoFattura[0]['Body'] as $BodyFattura) {
                if ($BodyFattura['NumeroFattura'] != $PFFATTU_rec['PFFNUM']) {
                    continue;
                }
                $numeroProtocollo = substr($Anapro_rec['PRONUM'], 4);
                $dataProtocollo = date('Y-m-d', strtotime($Anapro_rec['PRODAR']));

                $PFFATTU_update_rec = array();
                $PFFATTU_update_rec['PFFSRFA'] = $PFFATTU_rec['PFFSRFA'];
                // Valorizzo oggetto fattura solo se non già presente.
                $PFFATTU_update_rec['PFFDES'] = $OggettoFattura;
                if (!$PFFATTU_rec['PFFFCIG'] && !$PFFATTU_rec['PFFTCUP']) {
                    $PFFATTU_update_rec['PFFFCIG'] = $BodyFattura['CIG'];
                    $PFFATTU_update_rec['PFFTCUP'] = $BodyFattura['CUP'];
                }
                $PFFATTU_update_rec['PFFNUPT'] = $numeroProtocollo;
                $PFFATTU_update_rec['PFFDTPT'] = $dataProtocollo;
                /*
                 * Predispongo Array Aggiornamneto
                 */
                $FattuRec = $PFFATTU_update_rec;
                if (!$PFFATTU_update_rec['PFFFCIG']) {
                    $FattuRec['PFFFCIG'] = $PFFATTU_rec['PFFFCIG'];
                }
                if (!$PFFATTU_update_rec['PFFTCUP']) {
                    $FattuRec['PFFTCUP'] = $PFFATTU_rec['PFFTCUP'];
                }
                /*
                 * Data Scadenza:
                 */
                if ($ExtraParam['DATASCADENZAPAGA']) {
                    $PFFATTU_update_rec['PFFDUL'] = $ExtraParam['DATASCADENZAPAGA'];
                }

                $this->recUpdateLog[$PFFATTU_rec['PFFNUM']] = $FattuRec;
                if ($this->flAggiorna == true) {
                    try {
                        ItaDB::DBUpdate($DBHalley, "PFFATTU", "PFFSRFA", $PFFATTU_update_rec);
                    } catch (Exception $e) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore in update PFFATTU" . $e->getMessage());
                        return false;
                    }
                    $insert_Info = "Aggiornata testata fattura ID :{$PFFATTU_update_rec['PFFSRFA']} PROTOCOLLO= {$PFFATTU_update_rec['PFFNUPT']} DATA={$PFFATTU_update_rec['PFFDTPT']} CIG={$PFFATTU_update_rec['PFFFCIG']} CUP={$PFFATTU_update_rec['PFFTCUP']}";
                    $this->insertAudit($insert_Info);
                }
                /*
                 * Controllo CIG/CUP Valorizzati:
                 */
//                $CigCupValorizzati = false;
//                foreach ($PFWFATK_tab as $PFWFATK_rec) {
//                    if ($PFWFATK_rec['PFFTCIG']) {
//                        $CigCupValorizzati = true;
//                        break;
//                    }
//                    if ($PFWFATK_rec['PFFDCUP']) {
//                        $CigCupValorizzati = true;
//                        break;
//                    }
//                }
//                if ($CigCupValorizzati == false) {
//                    foreach ($PFWFATK_tab as $PFWFATK_rec) {
//                        $PFWKFAT_update_rec = array();
//                        $PFWKFAT_update_rec['PFFSERI'] = $PFWFATK_rec['PFFSERI'];
//                        $PFWKFAT_update_rec['PFFTCIG'] = $BodyFattura['CIG'];
//                        $PFWKFAT_update_rec['PFFDCUP'] = $BodyFattura['CUP'];
//                        if ($this->flAggiorna == true) {
//                            try {
//                                ItaDB::DBUpdate($DBHalley, "PFWFATK", "PFFSERI", $PFWKFAT_update_rec);
//                            } catch (Exception $e) {
//                                $this->setErrCode(-1);
//                                $this->setErrMessage("Errore in update PFWFATK" . $e->getMessage());
//                                return false;
//                            }
//                            $insert_Info = "Aggiornata dettaglio fattura ID :{$PFWKFAT_update_rec['PFFSERI']} CIG={$PFWKFAT_update_rec['PFFTCIG']} CUP={$PFWKFAT_update_rec['PFFDCUP']}";
//                            $this->insertAudit($insert_Info);
//                        }
//                    }
                $this->insertAudit("Concluso aggiornamento fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']}");
//                } else {
//                    $this->insertAudit("Dati CIG e CUP non aggiornati per la fattura N. {$PFFATTU_rec['PFFNUM']} del {$PFFATTU_rec['PFFDOP']} Codice creditore N. {$PFFATTU_rec['PFFCCF']} perchè CIG o CUP già presenti.");
//                }
                break;
            }
        }
        $this->insertAudit("Conclusa Elaborazione file Fattura $FileNameFattura.");
        return true;
    }

    function GetBaseExtP7MFile($baseFile) {
        $Est_baseFile = strtolower(pathinfo($baseFile, PATHINFO_EXTENSION));
        if ($Est_baseFile == "") {
            $Est_baseFile = "pdf";
        } else {
            if ($Est_baseFile == "p7m") {
                $baseFile = pathinfo($baseFile, PATHINFO_FILENAME);
                $Est_baseFile = $this->GetBaseExtP7MFile($baseFile);
            }
        }
        return $Est_baseFile;
    }

    private function insertAudit($insert_Info) {
        $this->msgLog[] = $insert_Info;
        $insert_Info = "Aggiornamento fatture Elettroniche Halley Protocollo {$this->currAnapro_rec['PRONUM']}/{$this->currAnapro_rec['PROPAR']}: " . $insert_Info;
        $this->eqAudit->logEqEvent($this, array(
            'Operazione' => eqAudit::OP_MISC_AUDIT,
            'DB' => '',
            'DBset' => '',
            'Estremi' => $insert_Info
        ));
    }

    public function GetInfoUpdatedRec() {
        $infoUpdate = "";
        $infoUpdate.= '<span style="margin-left:20px; margin-top:-15px; position:absolute; display:inline-block;"><b>Aggiornamento dati eseguito per le seguenti fatture:</b></span>';
        $infoUpdate.= '<div style="width:530px; height:auto; font-size:1.2em; margin-top:12px; " class="ita-box ui-widget-content ui-corner-all">';
        foreach ($this->recUpdateLog as $keyFatt => $recordAggiornato) {
            $infoUpdate.= '<div style="color: red;  padding:10px; text-shadow: 1px 1px 1px #000; ">Fattura ' . $keyFatt . '</div>';
            $infoUpdate.= '<div style="padding:10px; margin-left:20px; vertical-align:top; padding:6px;" >';
            $infoUpdate.= "<b>Numero di Protocollo:</b> " . $recordAggiornato['PFFNUPT'];
            $infoUpdate.= "<br><b>Data del Protocollo:</b> " . date('d/m/Y', strtotime($recordAggiornato['PFFDTPT']));
            $infoUpdate.= '<div style="padding-top:15px; padding-bottom:15px;"><b>Oggetto fattura:</b><br>' . $recordAggiornato['PFFDES'] . "</div>";
            $infoUpdate.= "<b>CIG:</b> " . $recordAggiornato['PFFFCIG'];
            $infoUpdate.= "<br><b>CUP:</b> " . $recordAggiornato['PFFTCUP'];

            $infoUpdate.= "<br></div>";
        }
        $infoUpdate.='</div>';
        return $infoUpdate;
    }

    public function GetDataScadenza($Anapro_rec) {
        $proLib = new proLib();

        $DataProtocollo = $Anapro_rec['PRODAR'];
        $DataProtocollo = itaDate::addDays($DataProtocollo, 1);
        $objProSdi = $this->CaricaOggettoSdi($Anapro_rec);
        if (!$objProSdi) {
            return false;
        }
        /*
         * Lettura Dati Pagamento
         */
        $DatiPagamento = $objProSdi->getDatiPagamento();
        if (isset($DatiPagamento[0]['DettaglioPagamento'][0]['DataScadenzaPagamento'][0]['@textNode'])) {
            $DataScadenzaPag = $DatiPagamento[0]['DettaglioPagamento'][0]['DataScadenzaPagamento'][0]['@textNode'];
            $DataScadenza = date("Ymd", strtotime($DataScadenzaPag));
            /*
             * Controllo data >= 30 si usa dalla data di protocollo
             * 
             */
            $DiffDays = itaDate::dateDiffDays($DataProtocollo, $DataScadenza);
            $DataPagamento = '';
            if ($DiffDays >= self::GGLimite) {
                $DataPagamento = $DataScadenza;
            } else {
                /*
                 * Altrimenti si calcolano 30 giorni dalla data di protocollo
                 */
                $DataPagamento = date('Ymd', strtotime('+' . self::GGLimite . ' day', strtotime($DataProtocollo)));
            }
        } else {
            $DataPagamento = date('Ymd', strtotime('+' . self::GGLimite . ' day', strtotime($DataProtocollo)));
        }
        return $DataPagamento;
    }

    public function EstraiDatiFatture($Anapro_rec) {
        $objProSdi = $this->CaricaOggettoSdi($Anapro_rec);
        $EstrattoFattura = $objProSdi->getEstrattoFattura();
        /*
         * Preparazione Campi
         */
        $BeniPrimaRiga = $EstrattoFattura[0]['Body'][0]['DescrizioneBeni'][0]['Descrizione'];
        $DatiFattura = array();
        $DatiFattura['OGGETTOFATTURAPRIMARIGA'] = $BeniPrimaRiga;
        $DatiFattura['DATASCADENZAPAGA'] = $this->GetDataScadenza($Anapro_rec);

        foreach ($EstrattoFattura[0]['Body'] as $BodyFattura) {
            $DatiFattura['CIGFATTURA'][$BodyFattura['NumeroFattura']] = $BodyFattura['CIG'];
            $DatiFattura['CUPFATTURA'][$BodyFattura['NumeroFattura']] = $BodyFattura['CUP'];
        }

        return $DatiFattura;
    }

}
