<?php

include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class proLibSdi {

    const TIPO_FILE_INFO_FATTURA_NO = '';
    const TIPO_FILE_INFO_FATTURA_DEFAULT = 'SEGNATURA';
    const TIPO_FILE_INFO_FATTURA_CSVDATAGRAPH = 'CSVDATAGRAPH';
    const TIPO_FILE_INFO_FATTURA_CSVTIN = 'CSVTIN';
    const VISUALIZZA_XML_WINDOW = 0;
    const VISUALIZZA_XML_IFRAME = 1;
    const VISUALIZZA_XML_URL = 2;
    const VISUALIZZA_XML_DIV = 3;
    // costanti export
    const PEXP_DIR = 'EXPORT_DIR';
    const PEXP_FATTURA = 'FATTURA';
    const PEXP_FILE_INFO = 'FILE_INFO';
    const PEXP_MT = 'EXP_MT';
    const PEXP_DT = 'EXP_DT';
    const PEXP_EC = 'EXP_EC';
    
    const OUT_STRING = 0;
    const OUT_DOMDOCUMENT = 1;
    const OUT_SIMPLEXML = 2;

    public static $ElencoTipoFileInfoFattura = array(
        self::TIPO_FILE_INFO_FATTURA_NO => 'Nessuno',
        self::TIPO_FILE_INFO_FATTURA_DEFAULT => 'Info default',
        self::TIPO_FILE_INFO_FATTURA_CSVDATAGRAPH => 'Csv Datagraph',
        self::TIPO_FILE_INFO_FATTURA_CSVTIN => 'Csv TINN'
    );

    /**
     * Libreria di funzioni Sistema di Interscambio
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private $errCode;
    private $errMessage;
    private $CodeMessage;
    private $Message;

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

    public function openInfoFattura($currObjSdi, $Anapro_rec = array()) {
        $model = 'proSdiInfo';
        Out::closeDialog($model);
        itaLib::openDialog($model);
        $objInfo = itaModel::getInstance($model);
        $objInfo->setCurrObjSdi($currObjSdi);
        $objInfo->setAnapro_rec($Anapro_rec);
        $objInfo->parseEvent();

        $htmlRet = $this->GetVisualizzazioneFattura($currObjSdi);
//        Out::html($model . "_divInfo", $htmlRet['contenutoAvviso']);
        Out::html($model . "_divInfo", $htmlRet['contenutoDett']);
    }

    public function openRiscontroFattura($Anapro_rec) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $proLibAllegati = new proLibAllegati();
        $anaent_45 = $proLib->GetAnaent('45');
        // Se è una fattura spacchettata:
        $NomeFileSdi = '';
        if ($Anapro_rec['PROCODTIPODOC'] == $anaent_45['ENTDE5'] && $anaent_45['ENTDE5'] != '') {
            // Prendo il primo xml 
            $sql = "SELECT *
                    FROM ANADOC 
                    WHERE DOCNUM = '" . $Anapro_rec['PRONUM'] . "' AND 
                    DOCPAR = '" . $Anapro_rec['PROPAR'] . "' AND DOCNAME LIKE '%.xml' ";
            $Anadoc_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
            // Ricavo il nome del file spacchettato 
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NomeFileFatturaSpacchettata', '', false, '', 'FATT_S_ELETTRONICA');
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato del NomeFileFatturaSpacchettata.");
                return false;
            }
            $NomeFileSdi = $TabDag_rec['TDAGVAL'];
        } else {
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
                return false;
            }
            // Scorro tutti i dcoumenti?..
            // $Anadoc_tab = $proLib->GetAnadoc($codice, 'protocollo', true, $Anapro_rec['']);
            $sql = "SELECT *
                    FROM ANADOC 
                    WHERE DOCNUM = '" . $Anapro_rec['PRONUM'] . "' AND 
                    DOCPAR = '" . $Anapro_rec['PROPAR'] . "' AND
                    DOCNAME = '" . $TabDag_rec['TDAGVAL'] . "'";
            $Anadoc_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
            if (!$Anadoc_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il file associato alla Fattura.");
                return false;
            }
            $NomeFileSdi = $Anadoc_rec['DOCNAME'];
        }
//        $protPath = $proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//        $Filepath = $protPath . "/" . $Anadoc_rec['DOCFIL'];
        $DocPath = $proLibAllegati->GetDocPath($Anadoc_rec['ROWID']);

        $FileSdi = array('LOCAL_FILEPATH' => $DocPath['DOCPATH'], 'LOCAL_FILENAME' => $NomeFileSdi);
        $objProSdi = proSdi::getInstance($FileSdi);
        if (!$objProSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage($objProSdi->getErrMessage());
            return false;
        }
        if (!$objProSdi->isFatturaPA()) {
            $this->setErrCode(-1);
            $this->setErrMessage('Il file esaminato non è una fattura elettronica.');
            return false;
        }
        // Apro solo se passa controlli
        $model = 'proSdiRiscontro';
        Out::closeDialog($model);
        itaLib::openDialog($model);
        $objInfo = itaModel::getInstance($model);
        $objInfo->setCurrObjSdi($objProSdi);
        $objInfo->setAnapro_rec($Anapro_rec);
        $objInfo->setAnadoc_rec($Anadoc_rec);
        $objInfo->setEvent('openform');
        $objInfo->parseEvent();
        $htmlRet = $this->GetVisualizzazioneRiscontro($objProSdi, $Anapro_rec);
        Out::html($model . "_divTestata", $htmlRet['contenutoTestata']);
        Out::html($model . "_divInfo", $htmlRet['contenutoDett']);
        Out::html($model . "_VediXmlFattura", $htmlRet['DicituraFattura']);
        return true;
    }

    /**
     *
     * @param proSdi $currObjSdi
     * @return array
     */
    public function GetVisualizzazioneFattura($currObjSdi) {
        $proLib = new proLib();
        if ($currObjSdi->getTipoMessaggio() == "MT" || $currObjSdi->getTipoMessaggio() == "") {
            $contenutoAvviso = '<b><font size="4px">Fattura<br>Elettronica</font></b>';
            $contenutoDett = "<span style=\"color: red; position:absolute; padding:10px; text-shadow: 1px 1px 1px #000; \"> <b><font size=\"6px\">Fattura Elettronica</font> </b></span><br><br><br>";
            $EstrattoMessaggio = $currObjSdi->getEstrattoMessaggio();
            $EstrattoFattura = $currObjSdi->getEstrattoFattura();
            $EstrattoAllegatiFattura = $currObjSdi->getEstrattoAllegatiFattura();
            if ($currObjSdi->getTipoMessaggio() == "MT") {
                $contenutoDett .= "<br><span style=\"padding:10px;font-size:14px;\"><b>Codice Destinatario IPA:  </b>" . $EstrattoMessaggio['CodiceDestinatario'] . '</span>';
                $contenutoDett .= "<span style=\"padding:10px;font-size:14px;\"><b>Ufficio di destinazione:</b></span>";
                $anauff_tab = $proLib->GetAnauff($EstrattoMessaggio['CodiceDestinatario'], 'uffSdi');
                if ($anauff_tab) {
                    foreach ($anauff_tab as $key => $anauff_rec) {
                        $contenutoDett .= "<span style=\"font-size:14px;\">{$anauff_rec['UFFDES']}</span>";
                    }
                }
                $contenutoDett .= '<br>';
            }
            foreach ($EstrattoFattura as $keyFatt => $Fattura) {
                $contenutoDett .= '<br>';
                $contenutoDett .= '<div style="display:inline-block; vertical-align:middle;" class="ita-box ui-widget-content ui-corner-all">';
                $DicituraFatture = (count($Fattura['Body']) > 1 ? "Vedi Fatture" : "Vedi Fattura");
                foreach ($Fattura['Body'] as $keyBody => $BodyFattura) {
                    $DataFattura = $dataVer = date("d/m/Y", strtotime($BodyFattura['DataFattura']));
                    $contenutoDett .= '<div style="width:650px; padding: 10px;" >';
                    $contenutoDett .= '<div style="display:inline-block; vertical-align:top; width:400px;"><br><br>';
                    $contenutoDett .= '<b>Fornitore</b>: ' . $Fattura['Header']['Fornitore']['Denominazione'] . '<br>';
                    $contenutoDett .= '<b>P.IVA/C.F. Fornitore</b>: ' . $Fattura['Header']['Fornitore']['IdCodice'] . '<br>';
                    $contenutoDett .= '</div>';
                    $contenutoDett .= '<div style="display:inline-block; vertical-align:top; padding:6px;" class="ita-box ui-widget-content ui-corner-all"><b>Fattura Numero</b>: ' . $BodyFattura['NumeroFattura'] . '<br>';
                    $contenutoDett .= '<b>Tipo Fattura</b>: ' . $BodyFattura['TipoFattura'] . '<br>';
                    $contenutoDett .= '<b>Data Fattura</b>: ' . $DataFattura . '<br>';
                    $contenutoDett .= '<b>CIG</b>: ' . $BodyFattura['CIG'] . '<br>';
                    $contenutoDett .= '<b>Importo</b>: ' . $BodyFattura['Importo'];
                    $contenutoDett .= '</div>';
                    $contenutoDett .= '<br><br><b>Causale Fattura</b>: ' . $BodyFattura['Oggetto'] . '<br><br>';
                    //Qui tabella o elaborazione x visualizzazione allegato. Usare ID ecc per l'apertura.
                    if ($EstrattoAllegatiFattura[$keyBody]) {
                        $contenutoDett .= '<b>Allegati</b>:<br> ';
                        $contenutoDett .= '<div style="width:100%">';
                        foreach ($EstrattoAllegatiFattura[$keyBody] as $keyAllegato => $Allegato) {
                            $FileAlleg = $Allegato['NomeAttachment'];
                            if (pathinfo($Allegato['NomeAttachment'], PATHINFO_EXTENSION) == '') {
                                if ($Allegato['FormatoAttachment']) {
                                    $FileAlleg = $Allegato['NomeAttachment'] . '.' . $Allegato['FormatoAttachment'];
                                } else {
                                    $FileAlleg = $Allegato['NomeAttachment'] . '.' . pathinfo($Allegato['FilePathAllegato'], PATHINFO_EXTENSION);
                                }
                            }
                            $icon = utiIcons::getExtensionIconClass($FileAlleg, 32);
                            $contenutoDett .= '<div style="width:100%">';
                            $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:32px;overflow:hidden;"><a href="#" id="' . $keyAllegato . '-' . $keyFatt . '" class="ita-hyperlink {event:\'VediAllegatoFattura\'}"><span style = "margin:2px;" class="' . $icon . '"></span></a></div>';
                            $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:150px;overflow:hidden;vertical-align:middle">' . $Allegato['NomeAttachment'] . '</div>';
                            $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:250px;overflow:hidden;vertical-align:middle">' . $Allegato['DescrizioneAttachment'] . '</div>';
                            $contenutoDett .= '</div>';
                        }
                        $contenutoDett .= '</div>';
                    }

                    $contenutoDett .='<br><br>';
                    $contenutoDett .= '</div>';
                }
                $contenutoDett.='</div><div style="display:inline-block; vertical-align:middle; margin-left: 40px;" >';
                $contenutoDett .= '<a href="#" id="' . $keyFatt . '" class="ita-hyperlink {event:\'VediXmlFattura\'}"><span title="Accettata" class="ita-icon ita-icon-euro-blue-32x32" style="display:inline-block; vertical-align:top;"></span>' .
                        '<span style="display:inline-block; vertical-align:top;"><font size="5px"><b><u>' . $DicituraFatture . '</u></b></font></span></a>';
                $contenutoDett.='</div>';
            }
        } else {
            $contenutoAvviso = '<b><font size="4px">Notifica di<br> Interscambio</font></b>';
            $contenutoDett = "<span style=\"color: red; position:absolute; left:100px; top:2px; text-shadow: 1px 1px 1px #000; \"> <b><font size=\"6px\">Notifica di Interscambio</font> </b></span><br><br><br>";
            //Ricevuta/Notifica ...
            //$EstrattoMessaggio = $this->currObjSdi->getEstrattoMessaggio();
            $DescTipoMessaggio = proSdi::$ElencoTipiMessaggio[$currObjSdi->getTipoMessaggio()];
            $contenutoDett .= '<div >';
            $contenutoDett .= '<b><font size="4px">É presente una "' . $DescTipoMessaggio . '"</font></b><br><br>';
            $contenutoDett .= '<b><font size="4px">Relativo al file: "' . $currObjSdi->getCodUnivocoFile() . '"</font></b><br>';
            $contenutoDett .= '<br><br></div>';
        }
        $ret['contenutoDett'] = $contenutoDett;
        $ret['contenutoAvviso'] = $contenutoAvviso;
        return $ret;
    }

    public function GetVisualizzazioneRiscontro($currObjSdi, $Anapro_rec) {
        // Visualizo il Titolo del div.
        $Testata = "<div style=\"display:inline-block; vertical-align:top;\">";
        $Testata.="<span style=\"color: red; position:absolute; padding:10px; text-shadow: 1px 1px 1px #000; \"> <b><font size=\"6px\">Fattura Elettronica</font> </b></span><br><br><br>";
        //Visualizzo il protocollo
        $ProtocolloN = substr($Anapro_rec['PRONUM'], 4) . '/' . substr($Anapro_rec['PRONUM'], 0, 4) . '-' . $Anapro_rec['PROPAR'];
        $Testata.="<br><span class=\"ui-state-highlight ui-widget-content ui-corner-all\" style=\"margin-left:10px; font-size:15px;\"><b>Protocollo N. $ProtocolloN - Gestione Esito Committente</b></span></div><br>";
        $Testata.= "</div>";

        $contenutoDett = "";
        $EstrattoFattura = $currObjSdi->getEstrattoFattura();
        $EstrattoAllegatiFattura = $currObjSdi->getEstrattoAllegatiFattura();
        $TotaleFatture = count($EstrattoFattura);
        foreach ($EstrattoFattura as $keyFatt => $Fattura) {
            $TotaleLottiFattura = count($Fattura['Body']);
            $contenutoDett .= '<br>';
            $contenutoDett .= '<div>';
            $contenutoDett .= '<div style="display:inline-block; vertical-align:middle;" >';
            $DicituraFatture = (count($Fattura['Body']) > 1 ? "Vedi Fatture" : "Vedi Fattura");
            foreach ($Fattura['Body'] as $keyBody => $BodyFattura) {
                $DataFattura = $dataVer = date("d/m/Y", strtotime($BodyFattura['DataFattura']));
                $contenutoDett .= '<div style="width:630px; padding: 10px;" class="ita-box ui-widget-content ui-corner-all">';
                $contenutoDett .= '<div style="display:inline-block; vertical-align:top; width:400px;">';
                $contenutoDett .= '<br><br>';
                $contenutoDett .= '<b>Fornitore</b>: ' . $Fattura['Header']['Fornitore']['Denominazione'] . '<br>';
                $contenutoDett .= '<b>P.IVA/C.F. Fornitore</b>: ' . $Fattura['Header']['Fornitore']['IdCodice'] . '<br>';
                $contenutoDett .= '</div>';
                $contenutoDett .= '<div style="display:inline-block; vertical-align:top; padding:6px;" class="ita-box ui-widget-content ui-corner-all"><b>Fattura Numero</b>: ' . $BodyFattura['NumeroFattura'] . '<br>';
                $contenutoDett .= '<b>Tipo Fattura</b>: ' . $BodyFattura['TipoFattura'] . '<br>';
                $contenutoDett .= '<b>Data Fattura</b>: ' . $DataFattura;
                $contenutoDett .= '</div>';
                $contenutoDett .= '<br><br><b>Causale Fattura</b>: ' . $BodyFattura['Oggetto'] . '<br><br>';
                // INIZIO TABELLA DI visualizzazione allegati.
                if ($EstrattoAllegatiFattura[$keyBody]) {
                    $contenutoDett .= '<b>Allegati</b>:<br> ';
                    $contenutoDett .= '<div style="width:100%">';
                    foreach ($EstrattoAllegatiFattura[$keyBody] as $keyAllegato => $Allegato) {
                        $icon = utiIcons::getExtensionIconClass($Allegato['NomeAttachment'], 32);
                        $contenutoDett .= '<div style="width:100%">';
                        $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:32px;overflow:hidden;"><a href="#" id="' . $keyAllegato . '-' . $keyFatt . '" class="ita-hyperlink {event:\'VediAllegatoFattura\'}"><span style = "margin:2px;" class="' . $icon . '"></span></a></div>';
                        $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:150px;overflow:hidden;vertical-align:middle">' . $Allegato['NomeAttachment'] . '</div>';
                        $contenutoDett .= '<div style="display:inline-block;margin-right:4px;height:32px;width:250px;overflow:hidden;vertical-align:middle">' . $Allegato['DescrizioneAttachment'] . '</div>';
                        $contenutoDett .= '</div>';
                    }
                    $contenutoDett .= '</div>';
                }
                // FINE TABELLA DI visualizzazione allegati.
                //
                // VISUALIZZAZIONE ACCETTA/RIFIUTA O STATO DELLA SINGOLA FATTURA:
                if ($TotaleLottiFattura > 1 || $TotaleFatture > 1) {
                    // PRENDO LO STATO DELLA FATTURA DAI METADATI
                    // CONTROLLO SE LA SINGOLA FATTURA E STATA ACCETTATA/RIFIUTATA
                    if ($Stato == 'Accettata') {
                        // VISUALIZZO LO STATO DELLA FATTURA: ACCETTATA/RIFIUTATA E IL SUO NUMERO DI PROTOCOLLO
                        $contenutoDett .= '<div style=" width:380px;" class="ita-box ui-state-highlight ui-widget-content ui-corner-all"><b>Fattura Accettata con Protocollo N. 12345678901010</b></div><br>';
                    } else {
                        // VISUALIZZO NASCONDO ACCETTA/RIFIUTA SINGOLA FATTURA
                        //Accetta Fattura
                        $contenutoDett.="<div style=\"margin-left:5px;\">";
                        $contenutoDett .= '<a href="#" id="' . $keyFatt . '_' . $keyBody . '" class="ita-hyperlink {event:\'AccettaSingolaFattura\'}"><span title="Accetta Fattura" class="ita-icon ita-icon-check-green-24x24" style="display:inline-block; vertical-align:top;"></span>' .
                                '<span style="display:inline-block; vertical-align:top;"><font size="2px"><b><u>Accetta Fattura</u></b></font></span></a>';
                        $contenutoDett .= '</div>';
                        //Rifiuta Fattura
                        $contenutoDett.="<div style=\"margin-left:5px;\">";
                        $contenutoDett .= '<a href="#" id="' . $keyFatt . '_' . $keyBody . '" class="ita-hyperlink {event:\'RifiutaSingolaFattura\'}"><span title="Rifiuta Fattura" class="ita-icon ita-icon-check-red-24x24" style="display:inline-block; vertical-align:top;"></span>' .
                                '<span style="display:inline-block; vertical-align:top;"><font size="2px"><b><u>Rifiuta Fattura</u></b></font></span></a><br>';
                        $contenutoDett .= '</div>';
                        // -> FINE Div per Accetta/Rifiuta Fattura Singola
                    }
                }
                $contenutoDett .= '</div>';
            }
            // BOTTONE PER VISUALIZZARE LA FATTURA O IL LOTTO DI FATTURE. [SPOSTARE SU BOTTONIERA?]
            $contenutoDett.='</div>';
        }
        // Salvo la dicitura fatture:
        $DicituraFatture = '<a href="#" id="' . $keyFatt . '" class="ita-hyperlink {event:\'VediXmlFattura\'}"><span title="Vedi Fattura" class="ita-icon ita-icon-euro-blue-32x32" style="display:inline-block; vertical-align:top;"></span>' .
                '<span style="display:inline-block; vertical-align:top;"><span style="font-size:20px;"><b><u>' . $DicituraFatture . '</u></b></span></span></a><br>';
        //$CheckNotificata = $this->CheckFatturaNotificata($currObjSdi->getFileFatturaUnivoco(), $NumeroFattura, $AnnoFattura);

        $ret['contenutoDett'] = $contenutoDett;
        $ret['contenutoTestata'] = $Testata;
        $ret['contenutoAvviso'] = $contenutoAvviso;
        $ret['DicituraFattura'] = $DicituraFatture;
        return $ret;
    }

    private function CheckFatturaNotificata($FileFatturaUnivoco, $NumeroFattura = '', $AnnoFattura = '') {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        list($P1File, $PFileConEstensione) = explode('_', $FileFatturaUnivoco);
        list($P2File) = explode('.', $PFileConEstensione);
        // Prendo ultimo progressivo usato
        $sql = "SELECT *
                    FROM TABDAG 
                    WHERE TDAGVAL LIKE '$P1File\_$P2File\_EC\_%' AND
                    TDAGFONTE = 'MESSAGGIO_SDI' AND 
                    TDCLASSE= 'ANAPRO' AND 
                    TDAGCHIAVE='NomeFileMessaggio' 
                    GROUP BY TDROWIDCLASSE "; //Group by serve?
        $TabdatEC_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        // Se Cerco NumeroFattura e AnnoFattura:
        if ($NumeroFattura && $AnnoFattura) {
            foreach ($TabdatEC_tab as $Tabdat_rec) {
                $TabDagRec_NumeroFattura = $proLibTabDag->GetTabdag('ANAPRO', 'valore', $Tabdat_rec['TDROWIDCLASSE'], 'NumeroFattura', $NumeroFattura, false, '', 'MESSAGGIO_SDI');
                $TabDagRec_AnnoFattura = $proLibTabDag->GetTabdag('ANAPRO', 'valore', $Tabdat_rec['TDROWIDCLASSE'], 'AnnoFattura', $AnnoFattura, false, '', 'MESSAGGIO_SDI');
                // Se le trovo entrambe valorizzate faccio alcuni controlli
                if ($TabDagRec_NumeroFattura && $TabDagRec_AnnoFattura) {
                    // Controllo se è valorizzato il "MessageIdCommittente"
                    $TabDagRec_MessageIdCommittente = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Tabdat_rec['ROWID'], 'MessageIdCommittente', '', false, '', 'MESSAGGIO_SDI');
                    // Controllo se c'è un "SE" collegato tramite "MessaggioIdCommittente"
                    if ($TabDagRec_MessageIdCommittente) {
                        
                    } else {
                        // Se "MessaggioIdCommittente" non è valorizzato
                    }
                }
            }
        } else {
            //Controllo se non c'è un SE collegato:
            $sql = "SELECT TDAGVAL
                    FROM TABDAG 
                    WHERE TDAGVAL LIKE '$P1File\_$P2File\_SE\_%' AND
                    TDAGFONTE = 'MESSAGGIO_SDI' AND 
                    TDCLASSE= 'ANAPRO' AND 
                    TDAGCHIAVE='NomeFileMessaggio' ";
            $TabdatSE_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
            //Se ci sono "SE", verifico se posso fare un altro tentativo di invio o non ne ho gia' fatto uno.
            if ($TabdatSE_tab) {
                // Se il numero di "SE" è uguale al numero di "EC", significa che posso fare un altro invio.
                if (count($TabdatSE_tab) == count($TabdatEC_tab)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function VisualizzaXmlConStile($FileStyle, $FileXml, $Anapro_rec = array(), $visualizzaXml = self::VISUALIZZA_XML_WINDOW, $container = null) {
        $style = ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . $FileStyle;
        /* Modifico lo stile per visualizzazione segnatura */
        if ($Anapro_rec) {
            $proLib = new proLib();
            $PathFile = itaLib::getAppsTempPath();
            $FileRandName = md5(rand() * time()) . ".xsl";
            $randPathFileName = $PathFile . "/" . $FileRandName;
            $Segnatura = $proLib->getScannerEndorserParams($Anapro_rec);
            $divSegnat = '<div style="font-size:1em;">' . $Segnatura['CAP_PRINTERSTRING'] . '</div>';
            $ContStyle = str_replace('<h1>FATTURA ELETTRONICA</h1>', $divSegnat . '<h1>FATTURA ELETTRONICA</h1>', file_get_contents($style));
//            $ContStyle = str_replace('<div class="page">', $divSegnat . '<div class="page">', $ContStyle);
            file_put_contents($randPathFileName, $ContStyle);
            $style = $randPathFileName;
        }

        $urlxsl = utiDownload::getUrl($FileStyle, $style, false, true, true);
        $ContStile = '<?xml-stylesheet type="text/xsl" href="' . htmlentities($urlxsl) . '"?>';

        $ContAppoggio = file_get_contents($FileXml);

        /* Spiegazione Regex 
         * ^ Inizia la cattura all'inizio della stringa 
         * (   )  Cattura i caratteri all'interno delle parentesi
         * \?xml Cattura i caratteri "?xml" letteralmente. Il backslash serve per eseguire l'escape del carattere "?"
         * .*? cattura qualsiasi carattere fino al prossimo match
         * \?> Cattura i caratteri "?>" letteralmente. Il backslash serve per eseguire l'escape del carattere "?"
         */
        $Apertura = preg_match("/(<\?xml .*?\?>)/mi", $ContAppoggio, $match_string);
        if (!$Apertura) {
            $strHead = '<?xml version="1.0" encoding="UTF-8"?>';
        } else {
            $strHead = $match_string[0];
        }

        $strHead = str_replace('1.1', '1.0', $strHead);

        $ArrFileXml = file($FileXml);
        $PathFile = itaLib::getAppsTempPath();
        $FileRandName = md5(rand() * time()) . ".xml";
        $randPathFileName = $PathFile . "/" . $FileRandName;

        // Preparo il documento
        $FileH = fopen($randPathFileName, 'w');
        if ($FileH === false) {
            Out::msgInfo("Attenzione", 'Errore nella apertura del File.');
            return false;
        }
        if (fwrite($FileH, $strHead) === false) {
            Out::msgInfo("Attenzione", 'Errore nella scrittura file: preview_xmltask ');
            return false;
        }
        if (fwrite($FileH, $ContStile) === false) {
            Out::msgInfo("Attenzione", 'Errore nella scrittura file stile.');
            return false;
        }
        foreach ($ArrFileXml as $Riga) {
            while (true) {
                $posizioneIniziale = strpos($Riga, '?>');
                if ($posizioneIniziale !== false) {
                    $Riga = substr($Riga, $posizioneIniziale + 2);
                    if (!$Riga) {
                        continue;
                    }
                } else {
                    break;
                }
            }
            if (fwrite($FileH, $Riga) === false) {
                Out::msgInfo("Attenzione", 'Errore nella scrittura file.');
                break;
            }
        }
        if (fclose($FileH) === false) {
            Out::msgInfo("Attenzione", 'Errore nella chiusura del file task.');
            return false;
        }
        //Apro il documento
        switch ($visualizzaXml) {
            case self::VISUALIZZA_XML_WINDOW:
                Out::openDocument(utiDownload::getUrl($FileRandName, $randPathFileName));
                break;
            case self::VISUALIZZA_XML_IFRAME:
                if (empty($container)) {
                    throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Id IFrame/div non definito');
                }
                Out::openIFrame('', $iframe, utiDownload::getUrl($FileRandName, $randPathFileName));
                break;
            case self::VISUALIZZA_XML_URL:
                return utiDownload::getUrl($FileRandName, $randPathFileName);
                break;
            case self::VISUALIZZA_XML_DIV:
                if (empty($container)) {
                    throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Id IFrame/div non definito');
                }
                Out::html($container, '<iframe style="width: 100%; height: 100%;" src="' . utiDownload::getUrl($FileRandName, $randPathFileName) . '"></iframe>');
                break;
        }
        return true;
    }

    public function isAnadocFileFattura($anaproRowid, $fileName) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'xml' && strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'p7m') {
            return false;
        }
        $proLib = new proLib();
        $sql = "SELECT * FROM TABDAG
                        WHERE TDCLASSE = 'ANAPRO' AND
                        TDROWIDCLASSE = $anaproRowid AND 
                        TDAGCHIAVE = 'FileFatturaUnivoco' AND 
                        TDAGVAL = '" . addslashes($fileName) . "' ";
        $TabDag_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
        if (!$TabDag_rec) {
            /* Verifico se è una fattura elettronica spacchettata */
            $anaent_45 = $proLib->GetAnaent('45');
            $Anapro_rec = $proLib->GetAnapro($anaproRowid, 'rowid');
            if ($Anapro_rec['PROCODTIPODOC'] == $anaent_45['ENTDE5'] && $anaent_45['ENTDE5'] != '') {
                return true;
            }
            return false;
        }
        return true;
    }

    public function ctrNumeroAllegatiFatturaElettronica($anaproRowid) {
        $proLib = new proLib();
        $sql = "SELECT * FROM TABDAG
                        WHERE TDCLASSE = 'ANAPRO' AND
                        TDROWIDCLASSE = $anaproRowid AND 
                        TDAGCHIAVE = 'Numero_Allegati' AND 
                        TDAGVAL > 0 ";
        $TabDag_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
        if (!$TabDag_rec) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $anapro_rec
     * @return boolean
     */
    public function getAnadocFlussoFromAnapro($anapro_rec) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();

        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }
        $FileFatturaUnivoco = $TabDag_rec['TDAGVAL'];
        // Prendo tutti gli anadoc.
//        $where = " AND DOCNAME = '$FileFatturaUnivoco' ";
        $anadoc_tab = $proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], '');

        $Anadoc_rec = array();
        foreach ($anadoc_tab as $anadocCheck_rec) {
            if ($anadocCheck_rec['DOCNAME'] == $FileFatturaUnivoco) {
                $Anadoc_rec = $anadocCheck_rec;
            }
        }

        return $Anadoc_rec;
    }

    public function GetAnaproFattura($fileName) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'xml' && strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'p7m') {
            return false;
        }
        $proLib = new proLib();
        $sql = "SELECT ANAPRO.* FROM ANAPRO
                        JOIN TABDAG ON TABDAG.TDROWIDCLASSE = ANAPRO.ROWID
                        WHERE TABDAG.TDCLASSE = 'ANAPRO' AND
                        TDAGCHIAVE = 'FileFatturaUnivoco' AND 
                        TDAGVAL = '" . addslashes($fileName) . "' ";
        $Anapro_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
        if (!$Anapro_rec) {
            return false;
        }
        return $Anapro_rec;
    }

    public function isAnadocFileMessaggio($anaproRowid, $fileName) {
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'xml' && strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) != 'p7m') {
            return false;
        }
        $proLib = new proLib();
        $sql = "SELECT * FROM TABDAG
                        WHERE TDCLASSE = 'ANAPRO' AND
                        TDROWIDCLASSE = $anaproRowid AND 
                        TDAGCHIAVE = 'NomeFileMessaggio' AND 
                        TDAGVAL = '" . addslashes($fileName) . "' ";
        $TabDag_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
        if (!$TabDag_rec) {
            return false;
        }
        return true;
    }

    public function GetAnaproDaCollegareFromEstratto($Estratto, $TipoProt) {
        $proLib = new proLib();
        switch ($TipoProt) {
            case 'A':
                $TabDag_rec = $this->GetProtCollegatoFromArrivo($Estratto);
                break;
            case 'P':
                $TabDag_rec = $this->GetProtCollegatoFromPartenza($Estratto);
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, il protocollo deve essere una Partenza o un Arrivo.");
                return false;
                break;
        }
        if (!$TabDag_rec) {
            return false;
        }
        $AnaproRet_rec = $proLib->GetAnapro($TabDag_rec['TDROWIDCLASSE'], 'rowid');
        if (!$AnaproRet_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo collegato NON trovato.");
            return false;
        }
        return $AnaproRet_rec;
    }

    private function GetProtCollegatoFromArrivo($Estratto) {
        if (!$Estratto['Tipo']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo Messaggio SDI NON presente.");
            return false;
        }
        switch ($Estratto['Tipo']) {
            // Collegamento tramite Nome File
            case proSdi::TIPOMESS_NS:
            case proSdi::TIPOMESS_MC:
            case proSdi::TIPOMESS_RC:
            case proSdi::TIPOMESS_NE:
            case proSdi::TIPOMESS_DT:
            case proSdi::TIPOMESS_AT:
                if (!$Estratto['NomeFile']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Nome File all'interno del messaggio SDI: {$Estratto['Tipo']} NON trovato.");
                    return false;
                }
                $TabDag_rec = $this->GetTabDagFromChiave('FileFatturaUnivoco', $Estratto['NomeFile']);
                break;

            case proSdi::TIPOMESS_SE:
                // Se c'è MessageIdCommittente
                // lo collego al protocollo EC in Partenza con quel idCommittente.
                $TabDag_rec = $this->GetTabDagMessaggioEC($Estratto);
                break;

            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Tipo Messaggio: {$Estratto['Tipo']} NON trovato.");
                return false;
                break;
        }
        if (!$TabDag_rec) {
            return false;
        }
        return $TabDag_rec;
    }

    private function GetProtCollegatoFromPartenza($Estratto) {
        if (!$Estratto['Tipo']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo Messaggio SDI NON presente.");
            return false;
        }
        switch ($Estratto['Tipo']) {
            case proSdi::TIPOMESS_EC:
                //Cerco l'MT per collegarlo a lui.(che si trova nella fattura)
                $TabDag_rec = $this->GetTabDagMessaggioMT($Estratto);
                if (!$TabDag_rec) {
                    return false;
                }
                break;

            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Tipo Messaggio: {$Estratto['Tipo']} NON trovato.");
                return false;
                break;
        }
        return $TabDag_rec;
    }

    private function GetTabDagFromChiave($chiave, $valore, $multi = false, $fonte = '') {
        $proLib = new proLib();
        $sql = "SELECT * FROM TABDAG
                        WHERE TDCLASSE = 'ANAPRO' AND
                        TDAGCHIAVE = '$chiave' AND 
                        TDAGVAL = '$valore' ";
        if ($fonte) {
            $sql.=" AND TDAGFONTE='$fonte' ";
        }
        $TabDag_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, $multi);
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile trovare metadato associato con Chiave: $chiave , e valore: $valore .");
            return false;
        }
        return $TabDag_rec;
    }

    private function GetTabDagMessaggioEC($Estratto) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $TabDag_rec = array();

        if ($Estratto['MessageIdCommittente']) {
            // Cerco il suo EC tramite MessaggioIdCommittente e controllo se coincide il nome file.
            $TabDagTab = $this->GetTabDagFromChiave('MessageIdCommittente', $Estratto['MessageIdCommittente'], true);
            foreach ($TabDagTab as $TabDagRec) {
                $TabDag_CodUnivoco = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $TabDagRec['TDROWIDCLASSE'], 'CodUnivocoFile', '', false, '', 'MESSAGGIO_SDI');
                $TabDag_Tipo = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $TabDagRec['TDROWIDCLASSE'], 'Tipo', '', false, '', 'MESSAGGIO_SDI');
                // Se trova entrambe le chiavi e se il valore del codunivoco è uguale a quello dell'estratto e se il tipo è EC, allora è questo il messaggio.
                if ($TabDag_CodUnivoco && $TabDag_Tipo && $TabDag_CodUnivoco['TDAGVAL'] == $Estratto['CodUnivocoFile'] && $TabDag_Tipo['TDAGVAL'] == 'EC') {
                    $TabDag_rec = $TabDag_CodUnivoco;
                    break;
                }
            }
        } else {
            //Prendo l'EC
            $NomeFileMessaggio = $Estratto['NomeFileMessaggio'];
            $ElementiNomeFile = proSdi::GetElementiNomeFile($NomeFileMessaggio);
            $EcFile = $ElementiNomeFile['CodUnivocoFileP1'] . '\_' . $ElementiNomeFile['CodUnivocoFileP2'] . '\_EC\_';
            $sql = "SELECT *
                    FROM TABDAG 
                WHERE TDCLASSE = 'ANAPRO' AND
                TDAGFONTE = 'MESSAGGIO_SDI' AND
                TDAGCHIAVE = 'NomeFileMessaggio' AND
                TDAGVAL LIKE '$EcFile%' ";
            //Se c'è un solo EC e il CodUnivocoFile è uguale a quello dell'estratto:
            $TabDagTab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
            if (count($TabDagTab) == 1) {
                $CodUnivocoFile = $proLibTabDag->GetValoreTabdag('ANAPRO', 'chiave', $TabDagTab[0]['TDROWIDCLASSE'], 'CodUnivocoFile', '', 'MESSAGGIO_SDI');
                if ($CodUnivocoFile == $Estratto['CodUnivocoFile']) {
                    $TabDag_rec = $TabDagTab[0];
                }
            }
        }
        // Se non trovo un TabDag_rec lo collego all'MT
        if (!$TabDag_rec) {
            $TabDag_rec = $this->GetTabDagMessaggioMT($Estratto);
            if (!$TabDag_rec) {
                return false;
            }
        }
        return $TabDag_rec;
    }

    public function GetTabDagMessaggioMT($Estratto) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        //
        $NomeFileMessaggio = $Estratto['NomeFileMessaggio'];
        $ElementiNomeFile = proSdi::GetElementiNomeFile($NomeFileMessaggio);
        $MtFile = $ElementiNomeFile['CodUnivocoFileP1'] . '\_' . $ElementiNomeFile['CodUnivocoFileP2'] . '\_MT\_';
        $sql = "SELECT *
                    FROM TABDAG 
                WHERE TDCLASSE = 'ANAPRO' AND
                TDAGFONTE = 'MESSAGGIO_SDI' AND
                TDAGCHIAVE = 'NomeFileMessaggio' AND
                TDAGVAL LIKE '$MtFile%' ";
        $TabDag_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, false);
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile trovare il messaggio MT tra i metadati.");
            return false;
        }
        // Controllo CodUnivocoFile
        $CodUnivocoFile = $proLibTabDag->GetValoreTabdag('ANAPRO', 'chiave', $TabDag_rec['TDROWIDCLASSE'], 'CodUnivocoFile', '', 'MESSAGGIO_SDI');
        if (!$CodUnivocoFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("CodUnivocoFile del messaggio MT non trovato tra i metadati.");
            return false;
        }
        if ($CodUnivocoFile != $Estratto['CodUnivocoFile']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il CodUnivocoFile del messaggio MT trovato non coincide con quello dell'estratto.");
            return false;
        }
        return $TabDag_rec;
    }

    //Funzione per controllare se è un protocollo SDI (Arrivo/Partenza)
    public function ControllaSeProtocolloSdi($Anapro_rec) {
        if ($this->ControllaSePartenzaSdi($Anapro_rec)) {
            return true;
        }
        if ($this->ControllaSeArrivoSdi()) {
            return true;
        }
        return false;
    }

    public function ControllaSePartenzaSdi($Anapro_rec) {
        $proLib = new proLib();
        $anaent_38 = $proLib->GetAnaent('38');
        $TipoProt = $Anapro_rec['PROPAR'];
        if ($TipoProt == 'P' && $Anapro_rec['PROCODTIPODOC']) {
            if ($anaent_38['ENTDE2'] == $Anapro_rec['PROCODTIPODOC'] || $anaent_38['ENTDE4'] == $Anapro_rec['PROCODTIPODOC']) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param <type> $anapro_rec
     * @param $verbose 
     * @param $ParamExport 
     * @return <type>
     */
    public function ExportArrivoSDI($anapro_rec, $ParamExport = array()) {
        $TipoProt = $anapro_rec['PROPAR'];
        if ($TipoProt != 'A') {
            $this->setCodeMessage(-1);
            $this->setMessage("Attenzione, accettati solo protocolli in Arrivo.");
            return true;
        }

        if (!$ParamExport[self::PEXP_DIR]) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, repository dell'esportazione non definito.");
            return false;
        }
        $Anadoc_export = array();
        /*
         * Carico le librerie
         */
        $proLib = new proLib(); // Non piu usato grazie ai parametri
        $proLibTabDag = new proLibTabDag();

        if ($ParamExport[self::PEXP_FATTURA]) {
            /**
             * Nome File Fattura da esportare da metadati
             */
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
                return false;
            }
            $FileFatturaUnivoco = $TabDag_rec['TDAGVAL'];
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], 'CodUnivocoFile', '', false, '', 'MESSAGGIO_SDI');
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato del Codice Fattura Univoco.");
                return false;
            }
            $CodUnivocoFile = $TabDag_rec['TDAGVAL'];

            /*
             * Scorro gli allegati e trovo il file fattura univoco leggendo nome file su tabdag. Metodo 2
             */

            $RetAnadoc_export = $this->GetExportFileFromAnadoc($FileFatturaUnivoco, $anapro_rec);
            if (!$RetAnadoc_export) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, il file $FileFatturaUnivoco non è presente tra i Documenti.");
                return false;
            }
            $Anadoc_export[] = $RetAnadoc_export;
            /*
             * Scrivo file info fattura 
             */
            if ($ParamExport[self::PEXP_FILE_INFO]) {
                $ExportInfo_rec = $this->GetExportFileInfo($CodUnivocoFile, $anapro_rec);
                if ($ExportInfo_rec === false) {
                    return false;
                }
                if ($ExportInfo_rec) {
                    $Anadoc_export[] = $ExportInfo_rec;
                }
            }
        }



        /*
         *  Controllo se deve esportare anche file metadati MT
         */
        if ($ParamExport[self::PEXP_MT]) {
            // Qui preparo il MT
            $RetFileMT = $this->getExportFileMetadatoSDI($anapro_rec, proSdi::TIPOMESS_MT, $ParamExport);
            if (!$RetFileMT) {
                return false;
            }
            $Anadoc_export = array_merge($Anadoc_export, $RetFileMT);
        }

        if ($ParamExport[self::PEXP_DT]) {
            // Qui preparo il DT
            $RetFileDT = $this->getExportFileMetadatoSDI($anapro_rec, proSdi::TIPOMESS_DT, $ParamExport);
            if (!$RetFileDT) {
                return false;
            }
            $Anadoc_export = array_merge($Anadoc_export, $RetFileDT);
        }

        // Export Repository Centralizzato
        if (!$this->ExportToRepository($Anadoc_export, $ParamExport[self::PEXP_DIR])) {
            return false;
        }
        return true;
    }

    public function ControllaSeArrivoSdi($Anapro_rec) {
        $proLib = new proLib();
        $anaent_38 = $proLib->GetAnaent('38');
        $TipoProt = $Anapro_rec['PROPAR'];
        if ($TipoProt == 'A' && $Anapro_rec['PROCODTIPODOC']) {
            if ($anaent_38['ENTDE1'] == $Anapro_rec['PROCODTIPODOC'] || $anaent_38['ENTDE3'] == $Anapro_rec['PROCODTIPODOC']) {
                return true;
            }
        }
        return false;
    }

    public function CancellaMetadatiSdi($Anapro_rec, $destFile, $docName) {
        // Controllo se questo file è da cancellare perchè dello SDI oppure no.?
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        $ext = pathinfo($destFile, PATHINFO_EXTENSION);
        // Non è da considerare, anche se questo caso NON dovrebbe verificarsi.
        if (strtolower($ext) != 'zip' && strtolower($ext) != 'xml' && strtolower($ext) != 'p7m') {
            return true;
        }
        $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $docName);
        $currObjSdi = proSdi::getInstance($FileSdi);
        if (!$currObjSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nell\'istanziare proSdi.');
            return false;
        }
        if ($currObjSdi->getErrCode() < 0) {
            $this->setErrCode(-1);
            $this->setErrMessage($currObjSdi->getErrMessage());
            return false;
        }
        // Se non è un messaggio SDI è una FatturaPA
        if ($currObjSdi->isMessaggioSdi()) {
            $retCanc = $proLibTabDag->CancellaTabDagSdi($Anapro_rec, 'MESSAGGIO_SDI');
        } else {
            // @TODO - Prendo il nome del file fattura che sto cancellando. [ Caso in cui si fanno paritre + xml insieme? ]
            //  $FileFattura = $currObjSdi->getFilePathFattura();
            $retCanc = $proLibTabDag->CancellaTabDagSdi($Anapro_rec, 'FATT_ELETTRONICA'); //, $FileFattura[0]);
        }
        if (!$retCanc) {
            $this->setErrCode(-1);
            $this->setErrMessage($proLibTabDag->getErrMessage());
            return false;
        }
        return true;
    }

    public function SalvaMetadatiPartenzaSdi($Anapro_rec, $Anadoc_rec) {
        $proLib = new proLib();
        $proLibAllegati = new proLibAllegati();

        $ext = pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
        if (strtolower($ext) != 'zip' && strtolower($ext) != 'xml' && strtolower($ext) != 'p7m') {
            return true;
        }

        $docName = $Anadoc_rec['DOCNAME'];
        $destFile = $proLibAllegati->CopiaDocAllegato($Anadoc_rec['ROWID'], '', true);
        if (!$destFile) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in copia temporanea del file.');
            return false;
        }

        $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $docName);
        $currObjSdi = proSdi::getInstance($FileSdi);

        if (!$currObjSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nell\'istanziare proSdi.');
            return false;
        }
        if ($currObjSdi->getErrCode() < 0) {
            $this->setErrCode(-1);
            $this->setErrMessage($currObjSdi->getErrMessage());
            return false;
        }
        $ret = $this->InserisciTabDag($Anapro_rec, $currObjSdi);
        return $ret;
    }

    public function InserisciTabDag($Anapro_rec, $currObjSdi) {
        $proLibTabDag = new proLibTabDag();
        if (is_object($currObjSdi)) {
            if ($currObjSdi->isFatturaPA() || $currObjSdi->isMessaggioSdi()) {
                $ret = $proLibTabDag->InserisciTabDagSdi($Anapro_rec, $currObjSdi);
                if (!$ret) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($proLibTabDag->getErrMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function CreaXmlEsitoCommittente($EstrattoFattura, $Anapro_rec, $Esito, $MotivoRifiuto, $InteraFattura = true) {
        if (!$Esito) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione esito non definito. Impossibile procedere con la creazione del file xml.");
            return false;
        }
        if ($Esito == 'EC02' && !$MotivoRifiuto) {
            $this->setErrCode(-1);
            $this->setErrMessage("In un esito di rifiuto la motivazione del rifiuto non può essere vuota.");
            return false;
        }

        $proLibTabDag = new proLibTabDag();
        $proLib = new proLib();
        $anaent_45 = $proLib->GetAnaent('45');
        $FatturaSpacchettata = false;
        if ($Anapro_rec['PROCODTIPODOC'] == $anaent_45['ENTDE5'] && $Anapro_rec['PROCODTIPODOC'] != '') {
            // Lettura dei metadati FS_MESSAGGIO_SDI
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'IdentificativoSdI', '', false, '', 'FS_MESSAGGIO_SDI');
            $FatturaSpacchettata = true;
            $InteraFattura = false;
        } else {
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'IdentificativoSdI', '', false, '', 'MESSAGGIO_SDI');
        }
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("IdentificativoSdI del messaggio MT non trovato.");
            return false;
        }
        /*
         * SE ATTIVO "INTERA FATTURA" CREA UN XML PER L'INTERA FATTURA
         * SE NON E' ATTIVO "INTERA FATTURA" UTILIZZA SOLO L'ESTRATTO PASSATO PER CREARE L'XML E RIFERIMENTI ALLA FATTURA
         */

        /*
         * Codifichiamo tutto in charset UTF-8 e convertiamo i caratteri chiave della descrizione
         * 
         */

        $xml = utf8_encode('<?xml version="1.0" encoding="UTF-8"?>');
        $xml.= utf8_encode('<types:NotificaEsitoCommittente xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">');
        $xml.= utf8_encode('<IdentificativoSdI>' . $TabDag_rec['TDAGVAL'] . '</IdentificativoSdI>');
        if (!$InteraFattura) {
            // Se fattura spacchettata, da trattare come intera, ma dati letti da body.
            if ($FatturaSpacchettata) {
                $Estratto = $EstrattoFattura[0]['Body'][0];
                $xml.= utf8_encode('<RiferimentoFattura>');
                $xml.= utf8_encode('<NumeroFattura>' . $Estratto['NumeroFattura'] . '</NumeroFattura>');
                $Anno = substr($Estratto['DataFattura'], 0, 4);
                $xml.= utf8_encode('<AnnoFattura>' . $Anno . '</AnnoFattura>');
                $xml.= utf8_encode('</RiferimentoFattura>');
            } else {
                // Qui controllo per indicare la fattura lo stesso. o settare intera a false se letti metadati FS o tipologia Fattura spacchettata.
                $xml.= utf8_encode('<RiferimentoFattura>');
                $xml.= utf8_encode('<NumeroFattura>' . $EstrattoFattura['NumeroFattura'] . '</NumeroFattura>');
                $Anno = substr($EstrattoFattura['DataFattura'], 0, 4);
                $xml.= utf8_encode('<AnnoFattura>' . $Anno . '</AnnoFattura>');
                $xml.= utf8_encode('</RiferimentoFattura>');
            }
        }
        $xml.= utf8_encode('<Esito>' . $Esito . '</Esito>');
        if ($MotivoRifiuto) {
            //$xml.= '<Descrizione>' . addslashes($MotivoRifiuto) . '</Descrizione>';
            $xml.= utf8_encode('<Descrizione>' . htmlspecialchars($MotivoRifiuto, ENT_NOQUOTES, 'ISO-8859-1') . '</Descrizione>');
        }
        // Calcolo MsgIdCommittente...
        $MsgIdCommittente = date('YmdHis', time());
        $xml.= utf8_encode('<MessageIdCommittente>' . $MsgIdCommittente . '</MessageIdCommittente>');
        $xml.= utf8_encode('</types:NotificaEsitoCommittente>');

        /*
         * Fine creazione xml UTF-8
         * 
         */


        // Scrivo sul File...
        $FileRandName = md5(rand() * time()) . ".xml";
        $destFile = itaLib::createAppsTempPath('sdiRiscontro') . '/' . $FileRandName;
        $FileXml = fopen($destFile, 'w');
        if ($FileXml === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'apertura del file xml.");
            return false;
        }
        if (fwrite($FileXml, $xml) === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella scrittura del file xml.");
            return false;
        }
        fclose($FileXml);
        // Controllo se il file è stato creato correttamente e provo a leggerlo
        $retChekc = $this->CheckFileXml($destFile);
        if (!$retChekc) {
            return false;
        }
        return $destFile;
    }

    public function CheckFileXml($FileXml) {
        $XmlCont = file_get_contents($FileXml);
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($XmlCont);

        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML . Impossibile leggere il testo nel Messaggio xml.");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML. Impossibile estrarre i dati Messaggio xml.");
            return false;
        }
        return true;
    }

    public function GetAnaproCollegatoMessaggioDT($currObjSdi) {
        if ($currObjSdi->getTipoMessaggio() == "DT") {
            $AnaproCollegato = $this->GetAnaproDaCollegareFromEstratto($currObjSdi->getEstrattoMessaggio(), 'A');
            if (!$AnaproCollegato) {
                $this->setErrCode(-1);
                $this->setErrMessage("Impossibile trovare la fattura collegata alla Decorrenza Termini.<br>La fattura non è ancora stata caricata, controllare.");
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("La notifica SDI non è una Decorrenza Termini.");
            return false;
        }
        return $AnaproCollegato;
    }

    public function generaFileInfoFatturaArrivo($anapro_rowid, $tipo = '') {
        $retArr = array();
        if ($tipo == proLibSdi::TIPO_FILE_INFO_FATTURA_NO) {
            return false;
        }

        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $proLibAllegati = new proLibAllegati();
        /*
         * Nome File Fattura da esportare da metadati
         */
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }
        $FileFatturaUnivoco = $TabDag_rec['TDAGVAL'];
        /*
         * 
         */
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'CodUnivocoFile', '', false, '', 'MESSAGGIO_SDI');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del Codice Fattura Univoco.");
            return false;
        }
        $CodUnivocoFile = $TabDag_rec['TDAGVAL'];

        if ($tipo == self::TIPO_FILE_INFO_FATTURA_CSVDATAGRAPH) {
            $anapro_rec = $proLib->GetAnapro($anapro_rowid, 'rowid');
            if (!@is_dir(itaLib::getPrivateUploadPath())) {
                if (!itaLib::createPrivateUploadPath()) {
                    $risultato = array('stato' => '-1', 'messaggio' => 'Creazione ambiente di lavoro temporaneo fallita.');
                    return $risultato;
                }
            }
            $fileName = "Proto_" . date('YmdHis') . '_' . md5(rand() * microtime(true)) . '.csv';
            $filePath = itaLib::getPrivateUploadPath() . "/" . $fileName;
            $File = fopen($filePath, "w+");
            if (!$File) {
                return false;
            }
            $numero = intval(substr($anapro_rec['PRONUM'], 4));
            $contenuto = "$FileFatturaUnivoco;{$anapro_rec['PRODAR']};$numero";
            if (!fwrite($File, $contenuto)) {
                fclose($File);
                return false;
            }
            fclose($File);
            $retArr['FILENAME'] = $fileName;
            $retArr['FILEPATH'] = $filePath;
            return $retArr;
        } else if ($tipo == self::TIPO_FILE_INFO_FATTURA_DEFAULT) {
            $fileName = $CodUnivocoFile . '.info';
            $filePath = $proLibAllegati->ScriviFileXML($anapro_rowid);
            if (!$filePath) {
                return false;
            }
            $retArr['FILENAME'] = $fileName;
            $retArr['FILEPATH'] = $filePath;
            return $retArr;
        }
    }

    public function GetExportFileFromAnadoc($NomeFile, $anapro_rec) {
        $proLib = new proLib();
        $proLibAllegati = new proLibAllegati();
        $anadoc_tab = $proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], '');
        $Anadoc_export = array();
        foreach ($anadoc_tab as $key => $anadoc_rec) {
            if ($anadoc_rec['DOCNAME'] == $NomeFile) {
//                $protPath = $proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//                $Anadoc_export = array(
//                    "ROWID_ANADOC" => $anadoc_rec['ROWID'],
//                    "SOURCE" => $protPath . "/" . $anadoc_rec['DOCFIL'],
//                    "DEST" => $anadoc_rec['DOCNAME'],
//                    "SHA2" => hash_file('sha256', $protPath . "/" . $anadoc_rec['DOCFIL'])
//                );
                $FilePathSource = $proLibAllegati->CopiaDocAllegato($anadoc_rec['ROWID'], '', true);
                $Anadoc_export = array(
                    "ROWID_ANADOC" => $anadoc_rec['ROWID'],
                    "SOURCE" => $FilePathSource,
                    "DEST" => $anadoc_rec['DOCNAME'],
                    "SHA2" => $proLibAllegati->GetHashDocAllegato($anadoc_rec['ROWID'], 'sha256')
                );
                break;
            }
        }
        return $Anadoc_export;
    }

    public function GetExportFileInfo($CodUnivocoFile, $anapro_rec) {
        $Anadoc_export = array();
        $proLibAllegati = new proLibAllegati();
        $segnatura = $proLibAllegati->ScriviFileXML($anapro_rec['ROWID']);
        if ($segnatura['stato'] == '-1') {
            //Out::msgInfo("Invio mail protocolo a Destinatari", $segnatura['messaggio']);
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in Preparazione Segnatura: $CodUnivocoFile. " . $segnatura['messaggio']);
            return false;
        } else if ($segnatura['stato'] == '-2') {
            $this->setErrCode(-2);
            $this->setErrMessage("Errore in Creazione File Segnatura: $CodUnivocoFile." . $segnatura['messaggio']);
            return false;
        } else {
            $DocName = $CodUnivocoFile . '.info';
            $Anadoc_export = array(
                "ROWID_ANADOC" => '',
                "FORZACARICAMENTO" => 1,
                "SOURCE" => $segnatura,
                "DEST" => $DocName,
                "SHA2" => hash_file('sha256', $segnatura)
            );
        }
        return $Anadoc_export;
    }

    public function ExportPartenzaSDI($anapro_rec, $ParamExport = array()) {
        $TipoProt = $anapro_rec['PROPAR'];
        if ($TipoProt != 'P') {
            $this->setCodeMessage(-1);
            $this->setMessage("Attenzione, accettati solo protocolli in Partenza.");
            return true;
        }
        $Anadoc_export = array();
        /*
         * Dati repository di destinazione
         */
        if (!$ParamExport[self::PEXP_DIR]) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, repository dell'esportazione non definito.");
            return false;
        }
        /*
         * Controllo se deve trasferire file EC
         */
        if ($ParamExport[self::PEXP_EC]) {
            // qui preparo il EC
            $RetFileDT = $this->getExportFileMetadatoSDI($anapro_rec, proSdi::TIPOMESS_EC, $ParamExport);
            if (!$RetFileDT) {
                return false;
            }
            $Anadoc_export = array_merge($Anadoc_export, $RetFileDT);
        }
        // Export Repository Centralizzato
        if (!$this->ExportToRepository($Anadoc_export, $ParamExport[self::PEXP_DIR])) {
            return false;
        }
        return true;
    }

    public function ExportToRepository($Anadoc_export, $dest_param) {
        if (!$Anadoc_export) {
            $this->setCode(-1);
            $this->setMessage("Non sono presenti file da esportare.");
            return false;
        }
        // Preparo le librerie
        $eqAudit = new eqAudit();
        $proLib = new proLib();
        $urlSchema = parse_url($dest_param);
        switch (strtolower($urlSchema['scheme'])) {
            /**
             * file url
             */
            case 'file':
                $dest_path = $urlSchema['path'];
                if (!is_writable($dest_path)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella di destinazione export SDI Arrivo inesistente");
                    return false;
                }
                foreach ($Anadoc_export as $export) {
                    if ($export['FORZACARICAMENTO']) {
                        if (!@copy($export['SOURCE'], $dest_path . "/" . $export['DEST'])) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Copia export: {$export['DEST']}... fattura PA fallita");
                            return false;
                        }
                        continue;
                    }
                    $anadoc_rec = $proLib->GetAnadoc($export['ROWID_ANADOC'], 'ROWID');
                    if (!$anadoc_rec) {
                        continue;
                    }
                    $docMeta = unserialize($anadoc_rec['DOCMETA']);
                    $infoExport_tab = $docMeta['INFOFATTURAPA']['EXPORT'];
                    if (!$infoExport_tab) {
                        $infoExport_tab = array();
                    }
                    if (!@copy($export['SOURCE'], $dest_path . "/" . $export['DEST'])) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia export: {$export['DEST']}... fattura PA fallita");
                        return false;
                    }

                    $infoExport_tab[] = array(
                        "ESITO" => true, "DATA" => date("Ymd"), "ORA" => date('H:i:s'), "SHA2" => $export['SHA2'], "REPOS" => $dest_param
                    );
                    $docMeta['INFOFATTURAPA']['EXPORT'] = $infoExport_tab;
                    $anadoc_rec['DOCMETA'] = serialize($docMeta);
                    try {
                        $NomeFile = $anadoc_rec['DOCNAME'];
                        $DB = $proLib->getPROTDB()->getDB();
                        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD, 'Estremi' => "{$anadoc_rec['ROWID']}: File Esportato nel Repository. $NomeFile", "DB" => $DB, "DSet" => 'ANADOC'));
                        ItaDB::DBUpdate($proLib->getPROTDB(), "ANADOC", 'ROWID', $anadoc_rec);
                    } catch (Exception $ex) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Aggiornamento info Allegato");
                        return false;
                    }
                }
                return true;
                break;

            /**
             * ftp url
             */
            case 'ftp':
                if (!$urlSchema['host']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: host mancante");
                    return false;
                }

                if (!$urlSchema['user']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: utente mancante");
                    return false;
                }

                if (!$urlSchema['pass']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: password mancante");
                    return false;
                }

                if (!$urlSchema['path']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: cartella destinazione  mancante");
                    return false;
                }
                $connId = ftp_connect($urlSchema['host']);
                if (!$connId) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Connessione ftp export file SDI fallita");
                    return false;
                }
                if (!ftp_login($connId, $urlSchema['user'], $urlSchema['pass'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Accesso ftp export file SDI fallita");
                    return false;
                }
                if (!ftp_chdir($connId, $urlSchema['path'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella di destinazione export file SDI non accessibile.");
                    return false;
                }
                foreach ($Anadoc_export as $export) {
                    if ($export['FORZACARICAMENTO']) {
                        if (!ftp_put($connId, $export['DEST'], $export['SOURCE'], FTP_BINARY)) {
                            ftp_close($connId);
                            $this->setErrCode(-1);
                            $this->setErrMessage("Trasferimento Ftp file messaggio PA: " . pathinfo($export['SOURCE'], PATHINFO_BASENAME) . " fallita");
                            return false;
                        }
                        continue;
                    }
                    $anadoc_rec = $proLib->GetAnadoc($export['ROWID_ANADOC'], 'ROWID');
                    if (!$anadoc_rec) {
                        continue;
                    }
                    $docMeta = unserialize($anadoc_rec['DOCMETA']);
                    $infoExport_tab = $docMeta['INFOFATTURAPA']['EXPORT'];
                    if (!$infoExport_tab) {
                        $infoExport_tab = array();
                    }
                    if (!ftp_put($connId, $export['DEST'], $export['SOURCE'], FTP_BINARY)) {
                        ftp_close($connId);
                        $this->setErrCode(-1);
                        $this->setErrMessage("Trasferimento Ftp file messaggio PA: " . pathinfo($export['SOURCE'], PATHINFO_BASENAME) . " fallita");
                        return false;
                    }
                    $infoExport_tab[] = array(
                        "ESITO" => true, "DATA" => date("Ymd"), "ORA" => date('H:i:s'), "SHA2" => $export['SHA2'], "REPOS" => $dest_param
                    );
                    $docMeta['INFOFATTURAPA']['EXPORT'] = $infoExport_tab;
                    $anadoc_rec['DOCMETA'] = serialize($docMeta);
                    try {
                        $NomeFile = $anadoc_rec['DOCNAME'];
                        $DB = $proLib->getPROTDB()->getDB();
                        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD, 'Estremi' => "{$anadoc_rec['ROWID']}: File Esportato nel Repository. $NomeFile", "DB" => $DB, "DSet" => 'ANADOC'));
                        ItaDB::DBUpdate($proLib->getPROTDB(), "ANADOC", 'ROWID', $anadoc_rec);
                    } catch (Exception $ex) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore Aggiornamento info Allegato");
                        return false;
                    }
                }
                ftp_close($connId);
                return true;
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Risorsa di destinazione non riconosciuta.");
                return false;
                break;
        }
    }

    /*
     * 0. Note su AllegatiSDI2Repository:
     *   Prima di ogni valorizzazione di $ExportParam, 
     *   viene controllato se c'è una forzatura esterna e non va sovrascritta.
     */

    public function AllegatiSDI2Repository($Anapro_rec, $ExportParam = array()) {
        $retStatus = array();
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $anaent_38 = $proLib->GetAnaent('38');
        $anaent_39 = $proLib->GetAnaent('39');
        $anaent_40 = $proLib->GetAnaent('40');
        $anaent_45 = $proLib->GetAnaent('45');
        //$ExportParam = array();
        /*
         * 1. Controllo Tipo Documento se esportabile
         */
        if (!$this->CheckAnaproEsportabile($Anapro_rec)) {
            $retStatus['ESPORTAZIONE'] = false;
            $retStatus['RISULTATO'] = 'Attenzione';
            $retStatus['MESSAGGIO'] = 'Il Protocollo non è esportabile. ' . $this->getMessage();
            return $retStatus;
        }
        /*
         *  Controllo repository di destinazione generale:
         */
        $dest_param = trim($anaent_39['ENTVAL']);
        if (!$dest_param) {
            $retStatus['ESPORTAZIONE'] = false;
            $retStatus['RISULTATO'] = 'Attenzione';
            $retStatus['MESSAGGIO'] = 'Repository di destinazione non definito.';
            return $retStatus;
        }
        /*
         * 2. Controllo se è una Fattura Elettronica/Notifica di Interscambio in Partenza
         */
        $FattEle_TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        $Messaggio_TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NomeFileMessaggio', '', false, '', 'MESSAGGIO_SDI');
        /* Se non è una FatturaPA o un MessaggioSDI esce  */
        if (!$FattEle_TabDag_rec && !$Messaggio_TabDag_rec) {
            $retStatus['ESPORTAZIONE'] = false;
            $retStatus['RISULTATO'] = 'Attenzione';
            $retStatus['MESSAGGIO'] = 'Nel Protocollo non è presente la Fattura Elettronica e nemmeno la Notifica di Interscambio.';
            return $retStatus;
        }

        /*
         *  3 Controllo Tipo di protocollo
         *              Pensare di passare repository di destino?
         */
        if (!isset($ExportParam[self::PEXP_DIR])) {
            $ExportParam[self::PEXP_DIR] = $dest_param;
        }

        if ($Anapro_rec['PROPAR'] == 'A') {
            /*
             * Esportabili in partenza:
             * Fattura Elettronica
             * Metadati Fattura Elettronica (MT)
             * DT Notifica di Interscambio
             * 
             */
            /* Se è un EFAA: serve esportare il file fattura elettronica */
            if ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                if (!isset($ExportParam[self::PEXP_FATTURA])) {
                    $ExportParam[self::PEXP_FATTURA] = true;
                }
            }
            /* Se serve esportare i file info */
            if ($anaent_40['ENTDE2']) {
                if (!isset($ExportParam[self::PEXP_FILE_INFO])) {
                    $ExportParam[self::PEXP_FILE_INFO] = true;
                }
            }
            /* Se è un EFAA e parametro attivo per esportare il file dei metadati */
            if ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
                if ($anaent_45['ENTVAL']) {
                    if (!isset($ExportParam[self::PEXP_MT])) {
                        $ExportParam[self::PEXP_MT] = true;
                    }
                }
            }
            /*  Se serve esportare il file di tipo DT */
            if ($anaent_45['ENTDE2']) {
                if (!isset($ExportParam[self::PEXP_DT])) {
                    $DT_Exp = $this->CheckDTEsportabile($Anapro_rec);
                    $ExportParam[self::PEXP_DT] = $DT_Exp;
                }
            }
            if (!$this->ExportArrivoSDI($Anapro_rec, $ExportParam)) {
                $retStatus['ESPORTAZIONE'] = false;
                $retStatus['RISULTATO'] = 'Errore';
                $retStatus['MESSAGGIO'] = $this->getErrMessage();
                return $retStatus;
            }
        } else {
            /*
             * Per ora solo EC è esportabile in partenza
             */
            if ($anaent_40['ENTDE2']) {
                if (!isset($ExportParam[self::PEXP_FILE_INFO])) {
                    $ExportParam[self::PEXP_FILE_INFO] = true;
                }
            }
            if ($anaent_45['ENTDE1']) {
                if (!isset($ExportParam[self::PEXP_EC])) {
                    $ExportParam[self::PEXP_EC] = true;
                }
            }
            if (!$this->ExportPartenzaSDI($Anapro_rec, $ExportParam)) {
                $retStatus['ESPORTAZIONE'] = false;
                $retStatus['RISULTATO'] = 'Errore';
                $retStatus['MESSAGGIO'] = $this->getErrMessage();
                return $retStatus;
            }
        }

        $MessaggioEsportazione = 'Esportazione terminata con successo.';
        /* Contabilita TIN deve elaborare ed esportare il file csv: se attivo. */
        $anaent_39 = $proLib->GetAnaent('39');
        $tipoInfoNotificaFattura = $anaent_39['ENTDE4'];
        if ($tipoInfoNotificaFattura == proLibSdi::TIPO_FILE_INFO_FATTURA_CSVTIN) {
            if (!$this->aggiornaFileFatturaTin($Anapro_rec['ROWID'], $tipoInfoNotificaFattura)) {
                $MessaggioEsportazione = 'Esportazione terminata con errori:';
                $MessaggioEsportazione.='<br>' . $this->getErrMessage();
            }
        }

        /*
         * Esportazione terminata con successo
         */
        $retStatus['ESPORTAZIONE'] = true;
        $retStatus['RISULTATO'] = 'Esportazione';
        $retStatus['MESSAGGIO'] = $MessaggioEsportazione;
        if ($this->getMessage()) {
            $retStatus['MESSAGGIO'] = $retStatus['MESSAGGIO'] . '<br>Info:<br>' . $this->getMessage();
        }
        return $retStatus;
    }

    public function CheckAnaproEsportabile($Anapro_rec) {
        // Se non ha un tipo documento valorizzato esco subito.
        if (!$Anapro_rec['PROCODTIPODOC']) {
            $this->setMessage('Tipo documento vuoto.');
            return false;
        }
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $anaent_38 = $proLib->GetAnaent('38');
        $anaent_39 = $proLib->GetAnaent('39');
        $anaent_45 = $proLib->GetAnaent('45');
        if (!trim($anaent_39['ENTVAL'])) {
            $this->setMessage('Repository di esportazione non definito. Esportazione non attiva.');
            return false;
        }
        //Controllo repsoitory sdi?
        // Se non è EFAA o SDIP esco subito.
        $TipoDoc = $Anapro_rec['PROCODTIPODOC'];
        // Controllo se TipoDoc Valorizzato e se diverso da EFAA o SDIP.
        if ($TipoDoc != $anaent_38['ENTDE1'] && $TipoDoc != $anaent_38['ENTDE4'] && $TipoDoc != $anaent_38['ENTDE3']) {
            $this->setMessage('Tipo documento diverso da ' . $anaent_38['ENTDE1'] . ' e da ' . $anaent_38['ENTDE4']);
            return false;
        }
        // Se non è Arrivo o Partenza esco subito
        if ($Anapro_rec['PROPAR'] != 'A' && $Anapro_rec['PROPAR'] != 'P') {
            $this->setMessage('Accettati solo protocolli in Arrivo e in Partenza.');
            return false;
        }
        // Se è un Arrivo di tipo EFAA
        if ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] && $Anapro_rec['PROPAR'] == 'A') {
            return true;
        }
        // Se attivo esporta EC ed è una Partenza SDIP
        if ($anaent_45['ENTDE1'] && $Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4'] && $Anapro_rec['PROPAR'] == 'P') {
            return true;
        }
        // Se attivo esporta DT di tipo SDIA
        $TestTabdat_DT_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', $Anapro_rec['ROWID'], 'Tipo', 'DT', false, '', 'MESSAGGIO_SDI');
        if ($anaent_45['ENTDE2'] && $Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] && $Anapro_rec['PROPAR'] == 'A' && $TestTabdat_DT_rec) {
            return true;
        }
        // Nessun messaggio aggiuntivo.
        return false;
    }

    public function CheckDTEsportabile($Anapro_rec) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $anaent_38 = $proLib->GetAnaent('38');
        $anaent_40 = $proLib->GetAnaent('40');
        /*
         * Se Anapro_rec è una EFAA 
         * ed è attivo il parametro: -> "Allega notifiche DT al protocollo principale"
         * Allora il DT deve essere esportato.
         */
        $TestTabdat_DT_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', $Anapro_rec['ROWID'], 'Tipo', 'DT', false, '', 'MESSAGGIO_SDI');
        if ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1']) {
            if ($anaent_40['ENTDE4']) {
                /*
                 * Controllo che il DT sia effettivamente presente tra i metadati, 
                 * altrimenti la notifica di decorrenza termini
                 * non è ancora pervenuta (fino a 15 gg dopo) e non devo esportarla. 
                 * Aggiungo però un messaggio di informazione per avvertire l'utente.
                 */
                if (!$TestTabdat_DT_rec) {
                    $this->setMessage('Non è presente la notifica di tipo DT, quindi non è stata esportata.');
                } else {
                    return true;
                }
            } else {
                /* Controllo se però è presente il file DT, allora lo devo esportare comunque  */
                if ($TestTabdat_DT_rec) {
                    return true;
                }
            }
        } else {
            /* Altrimenti ha superato i controlli perchè è uno SDIA di tipo DT. */
            if ($Anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] && $TestTabdat_DT_rec) { // Ha senso ricontrollarlo? già controllato sull'esportabile o no.
                return true;
            }
        }
        return false;
    }

    // 1 prendo nome univoco
    // 2 aggiungo NOME_+TIPO_NOTIFICA
    // 3 prendo tutti i file che corrispondono alla tipologia e per ognuno creo EXP FILE E INFO SE PARAMETRIZZATO
    public function getExportFileMetadatoSDI($anapro_rec, $Tipo, $ParamExport) {
        $Anadoc_export = array();

        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $TabDagNomeUnivoco_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], 'CodUnivocoFile', '', false, '', 'MESSAGGIO_SDI');
        if (!$TabDagNomeUnivoco_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del CodUnivocoFile.");
            return false;
        }
        $CodUnivocoFile = $TabDagNomeUnivoco_rec['TDAGVAL'];
        $sql = "SELECT *
                    FROM TABDAG 
                    WHERE TDAGVAL LIKE '$CodUnivocoFile\_$Tipo\_%' AND
                    TDAGFONTE = 'MESSAGGIO_SDI' AND 
                    TDCLASSE= 'ANAPRO' AND 
                    TDAGCHIAVE='NomeFileMessaggio' 
                    AND TDROWIDCLASSE = '" . $anapro_rec['ROWID'] . "' ";
        $TabDag_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        if (!$TabDag_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato di tipo $Tipo.");
            return false;
        }
        //
        foreach ($TabDag_tab as $TabDag_rec) {
            $NomeFileMessaggio = $TabDag_rec['TDAGVAL'];
            $RetAnadoc_export = $this->GetExportFileFromAnadoc($NomeFileMessaggio, $anapro_rec);
            if (!$RetAnadoc_export) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, il file $NomeFileMessaggio non è presente tra i Documenti.");
                return false;
            }
            $Anadoc_export[] = $RetAnadoc_export;
            /*
             * Controllo se deve esportare file info
             */
            if ($ParamExport[self::PEXP_FILE_INFO]) {
                list($CodiceFileTipo, $extTipo) = explode('.', $NomeFileMessaggio);
                $ExportInfo_rec = $this->GetExportFileInfo($CodiceFileTipo, $anapro_rec);
                if ($ExportInfo_rec === false) {
                    return false;
                }
                if ($ExportInfo_rec) {
                    $Anadoc_export[] = $ExportInfo_rec;
                }
            }
        }

        return $Anadoc_export;
    }

    /* Prende:
     * 1. Se non passato nessun riferimento, prende tutte le decorrenze per intera fattura e le restituisce.
     * 2. Se indicato riferimento, prende tutte le decorrenze che lo riguardano.
     */

    public function GetDecorrenzaTermini($FileFatturaUnivoco, $RifFattura = '', $multi = false) {
        $proLib = new proLib();
        $proLibTabdag = new proLibTabDag();
        $CampiAgg = $JoinAgg = $WhereAgg = '';
        if ($RifFattura) {
            $CampiAgg = "TABDAG_NFAT.TDAGVAL AS NUMEROFATTURA,";
            $JoinAgg = " LEFT OUTER JOIN TABDAG TABDAG_NFAT ON 
                        TABDAG_NFAT.TDCLASSE = TABDAG.TDCLASSE AND
                        TABDAG_NFAT.TDROWIDCLASSE = TABDAG.TDROWIDCLASSE AND
                        TABDAG_NFAT.TDPROG = TABDAG.TDPROG AND
                        TABDAG_NFAT.TDAGFONTE = TABDAG.TDAGFONTE
                        AND TABDAG_NFAT.TDAGCHIAVE = 'NumeroFattura'";
        }
        $sql = "SELECT TABDAG.*,
                       $CampiAgg
                       TABDAG_DT.TDAGVAL AS NOMEFILEUNIVOCO
                    FROM TABDAG 
                        LEFT OUTER JOIN TABDAG TABDAG_DT ON 
                        TABDAG_DT.TDCLASSE = TABDAG.TDCLASSE AND 
                        TABDAG_DT.TDROWIDCLASSE = TABDAG.TDROWIDCLASSE AND 
                        TABDAG_DT.TDPROG = TABDAG.TDPROG AND 
                        TABDAG_DT.TDAGFONTE = TABDAG.TDAGFONTE 
                        $JoinAgg
                    WHERE TABDAG.TDAGCHIAVE = 'Tipo' AND TABDAG.TDAGVAL ='DT' 
                    AND TABDAG_DT.TDAGCHIAVE = 'CodUnivocoFile' AND TABDAG_DT.TDAGVAL ='$FileFatturaUnivoco'  ";
        $TabDt = array();
        $TabDag_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);

        foreach ($TabDag_tab as $TabDag_rec) {
            if ($RifFattura) {
                /* Casi in cui passa:
                 * 1. Se NUMEROFATTURA è vuoto, quindi un DT riferito all'intera fattura.
                 * 2. Se NUMEROFATTURA è valorizzato, e corrisponde a quello che sto cercando (RiferimentoFattura).
                 */
                if ($TabDag_rec['NUMEROFATTURA']) {
                    if ($RifFattura != $TabDag_rec['NUMEROFATTURA']) {
                        continue;
                    }
                }
            } else {
                /* Controllo se questo DT è riferito ad una singola fattura */
                $TabDag_NFat_rec = $proLibTabdag->GetTabdag($TabDag_rec['TDCLASSE'], 'chiave', $TabDag_rec['TDROWIDCLASSE'], 'NumeroFattura', $TabDag_rec['TDPROG'], false, '', 'MESSAGGIO_SDI');
                if ($TabDag_NFat_rec) {
                    continue;
                }
            }
            $TabDt[] = $TabDag_rec;
        }
//        App::log('$sql deco');
//        App::log($TabDt);
        return $TabDt;
    }

    /* Prende:
     * 1. Se non passato nessun riferimento, prende tutti gli esiti senza rifeirmento numero fattura.
     * 2. Se indicato riferimento, prende tutte le decorrenze che lo riguardano.
     */

    public function GetLastEsitoCommittente($FileFattura, $RiferimentoFattura, $multi = true) {
        $proLib = new proLib();
        $proLibTabdag = new proLibTabDag();
        $CampiAgg = $JoinAgg = $WhereAgg = '';
        if ($RiferimentoFattura) {
            $CampiAgg = "TABDAG_NFAT.TDAGVAL AS NUMEROFATTURA,";
            $JoinAgg = " LEFT OUTER JOIN TABDAG TABDAG_NFAT ON 
                        TABDAG_NFAT.TDCLASSE = TABDAG.TDCLASSE AND
                        TABDAG_NFAT.TDROWIDCLASSE = TABDAG.TDROWIDCLASSE AND
                        TABDAG_NFAT.TDPROG = TABDAG.TDPROG AND
                        TABDAG_NFAT.TDAGFONTE = TABDAG.TDAGFONTE 
                        AND TABDAG_NFAT.TDAGCHIAVE = 'NumeroFattura' ";
        }
        $sql = "SELECT TABDAG.*,
                       $CampiAgg
                       TABDAG_EC.TDAGVAL AS NOMEFILEUNIVOCO
                    FROM TABDAG 
                        LEFT OUTER JOIN TABDAG TABDAG_EC ON 
                        TABDAG_EC.TDCLASSE = TABDAG.TDCLASSE AND 
                        TABDAG_EC.TDROWIDCLASSE = TABDAG.TDROWIDCLASSE AND 
                        TABDAG_EC.TDPROG = TABDAG.TDPROG AND 
                        TABDAG_EC.TDAGFONTE = TABDAG.TDAGFONTE 
                     $JoinAgg
                    WHERE TABDAG.TDAGCHIAVE = 'Tipo' AND TABDAG.TDAGVAL ='EC' 
                    AND TABDAG_EC.TDAGCHIAVE = 'CodUnivocoFile' AND TABDAG_EC.TDAGVAL ='$FileFattura'
                    ORDER BY TDPROG ASC";

        $TabDag_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, $multi);
        /* 1 Cerco il primo eisto valido, quindi senza "SCARTO ESITO". */
        $Esito = false;
        foreach ($TabDag_tab as $TabDag_rec) {
            if ($RiferimentoFattura) {
                /* Casi in cui passa:
                 * 1. Se NUMEROFATTURA è vuoto, quindi un EC riferito all'intera fattura.
                 * 2. Se NUMEROFATTURA è valorizzato, e corrisponde a quello che sto cercando (RiferimentoFattura).
                 */
                if ($TabDag_rec['NUMEROFATTURA']) {
                    if ($RiferimentoFattura != $TabDag_rec['NUMEROFATTURA']) {
                        continue;
                    }
                }
            } else {
                /* Se cerco solo l'EC Intero, scarto gli eventuali EC singoli trovati. */
                $TabDag_NFat_rec = $proLibTabdag->GetTabdag($TabDag_rec['TDCLASSE'], 'chiave', $TabDag_rec['TDROWIDCLASSE'], 'NumeroFattura', $TabDag_rec['TDPROG'], false, '', 'MESSAGGIO_SDI');
                if ($TabDag_NFat_rec) {
                    continue;
                }
            }
            $TabDag_MesIdD_rec = $proLibTabdag->GetTabdag($TabDag_rec['TDCLASSE'], 'chiave', $TabDag_rec['TDROWIDCLASSE'], 'MessageIdCommittente', '', false, '', 'MESSAGGIO_SDI');
            $MessageId = $TabDag_MesIdD_rec['TDAGVAL'];
            $sqlScarto = "SELECT TABDAG.*
                    FROM TABDAG 
                        LEFT OUTER JOIN TABDAG TABDAG_SE ON 
                        TABDAG_SE.TDCLASSE = TABDAG.TDCLASSE AND 
                        TABDAG_SE.TDROWIDCLASSE = TABDAG.TDROWIDCLASSE AND 
                        TABDAG_SE.TDPROG = TABDAG.TDPROG AND 
                        TABDAG_SE.TDAGFONTE = TABDAG.TDAGFONTE 
                    WHERE TABDAG.TDAGCHIAVE = 'Tipo' AND TABDAG.TDAGVAL ='SE'
                    AND TABDAG_SE.TDAGCHIAVE='MessageIdCommittente' AND TABDAG_SE.TDAGVAL ='$MessageId' ";
            $TabDag_SE_rec = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sqlScarto, false);
            if ($TabDag_SE_rec) {
                continue;
            }
            $TabDag_Esito_rec = $proLibTabdag->GetTabdag($TabDag_rec['TDCLASSE'], 'chiave', $TabDag_rec['TDROWIDCLASSE'], 'Esito', '', false, '', 'MESSAGGIO_SDI');
            $Esito = $TabDag_Esito_rec['TDAGVAL'];
            break;
        }
        return $Esito;
    }

    public function aggiornaFileFatturaTin($anapro_rowid, $tipo = '') {
        $retArr = array();
        if ($tipo !== proLibSdi::TIPO_FILE_INFO_FATTURA_CSVTIN) {
            return false;
        }
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $proLibAllegati = new proLibAllegati();

        /*
         * 1. Leggo il file:
         */
        $anaent_39 = $proLib->GetAnaent('39');
        $dest_param = trim($anaent_39['ENTVAL']);
        if (!$dest_param) {
            $this->setErrCode(-1);
            $this->setErrMessage("Repository di sorgente non definito.");
            return false;
        }
        /*
         * Nome fle parametrico? Per ora no..
         * InterscambioFATTPA.csv
         */
        $NomeFile = 'InterscambioFATTPA.csv';
        $FileCsv = $this->GetFileFromRepository($NomeFile, $dest_param);
        if (!$FileCsv) {
            return false;
        }
        $md5Originale = md5_file($FileCsv);

        // Copio il file in una path temporanea per elaborarlo.
        $TmpPath = itaLib::createAppsTempPath('WorkCsv');
        $WorkCsv = $TmpPath . '/' . $NomeFile;
        if (!@copy($FileCsv, $WorkCsv)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia file di lavoro fallita");
            return false;
        }
        /*
         *  Scrivo il file con i nuovi dati:
         */
        $CsvElaborato = $this->ScriviFileFattureTin($anapro_rowid, $WorkCsv);
        if (!$CsvElaborato) {
            return false;
        }

        /*
         * Scritto il file lo riscarico e controllo se è variato 
         */
        $FileCsv = $this->GetFileFromRepository($NomeFile, $dest_param);
        $md5Corrente = md5_file($FileCsv);
        if ($md5Originale != $md5Corrente) {
            $this->setErrCode(-1);
            $this->setErrMessage("File originale della contabilità variato. Occorre rieseguire l'esportazione.");
            return false;
        }
        /*
         * Preparo i documenti da esportare
         */
        $ElencoAnadoc_export = array();
        $Anadoc_export = array(
            "ROWID_ANADOC" => '',
            "FORZACARICAMENTO" => 1,
            "SOURCE" => $CsvElaborato,
            "DEST" => $NomeFile,
            "SHA2" => hash_file('sha256', $CsvElaborato)
        );
        $ElencoAnadoc_export[] = $Anadoc_export;
        /*
         * Esporto: File xml/p7m fattura:
         */
        $anapro_rec = $proLib->GetAnapro($anapro_rowid, 'rowid');
        //Viene già fatto prima
//        $Anadoc_rec = $this->getAnadocFlussoFromAnapro($anapro_rec);
//        if (!$Anadoc_rec) {
//            // Errore...
//            $protPath = $proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//            $FileProto = $protPath . "/" . $Anadoc_rec['DOCFIL'];
//            $Anadoc_export = array(
//                "ROWID_ANADOC" => $Anadoc_rec['ROWID'],
//                "FORZACARICAMENTO" => 1,
//                "SOURCE" => $FileProto,
//                "DEST" => $Anadoc_rec['DOCNAME'],
//                "SHA2" => hash_file('sha256', $FileProto)
//            );
//            $ElencoAnadoc_export[] = $Anadoc_export;
//        }

        /*
         * Esporto i documenti
         */
        if (!$this->ExportToRepository($ElencoAnadoc_export, $dest_param)) {
            return false;
        }
        /*
         * Aggiorno i metadati delle fatture:
         */
        $TabdagFatture_tab = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'FileFatturaUnivoco', '', true, '', 'FATT_ELETTRONICA');
        if (!$TabdagFatture_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }
        foreach ($TabdagFatture_tab as $TabdagFatture_rec) {
            $Prog = $TabdagFatture_rec['TDPROG'];
            $Tabdag_contab_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'Contabilita_Exp', $Prog, false, '', 'FATT_ELETTRONICA');
            if (!$Tabdag_contab_rec) {
                $NewTabdag = $TabdagFatture_rec;
                unset($NewTabdag['ROWID']);
                $NewTabdag['TDAGCHIAVE'] = 'Contabilita_Exp';
                $NewTabdag['TDAGVAL'] = 'Contabilita_Exp';
                $NewTabdag['TDAGSEQ'] = '999';
                try {
                    ItaDB::DBInsert($proLibTabDag->getPROTDB(), 'TABDAG', 'ROWID', $NewTabdag);
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in inserimento TABDAG.<br> " . $e->getMessage());
                    return false;
                }
            }
        }
        return true;
        // Nel file csv deve essere inserito il nome file p7m o spacchettato xml (nel caso di file firmato digitalmente)
    }

    public function ScriviFileFattureTin($anapro_rowid, $WorkCsv) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        /*
         * Lettura delle fatture in questo protocollo.
         */
        $TabdagFatture_tab = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'FileFatturaUnivoco', '', true, '', 'FATT_ELETTRONICA');
        if (!$TabdagFatture_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }
        // Elaboro le fatture (Lotti o 1)
        $ElencoFatture = array();
        foreach ($TabdagFatture_tab as $TabdagFatture_rec) {
            // Verifico
            // Chiave: Contabilita_Exp   data export
            $Prog = $TabdagFatture_rec['TDPROG'];
            $Tabdag_contab_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'Contabilita_Exp', $Prog, false, '', 'FATT_ELETTRONICA');
            if (!$Tabdag_contab_rec) {
                // Se non è contabilitzzata la devo contabilizzare
                $Tabdag_NFattura_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'NumeroFattura', $Prog, false, '', 'FATT_ELETTRONICA');
                $Tabdag_DataFatt_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rowid, 'DataFattura', $Prog, false, '', 'FATT_ELETTRONICA');
                $DatiFattura = array();
                $DatiFattura['FILEFATTURA'] = $TabdagFatture_rec['TDAGVAL'];
                $DatiFattura['FATTURA'] = $Tabdag_NFattura_rec['TDAGVAL'];
                $DatiFattura['DATA'] = $Tabdag_DataFatt_rec['TDAGVAL'];
                $ElencoFatture[] = $DatiFattura;
            } else {
                // C'è la data contabilizzata
                continue;
            }
        }
        if (!$ElencoFatture) {
            $this->setErrCode(-1);
            $this->setErrMessage("Le fatture del protocollo risultano già essere tutte contabilizzate.");
            return false;
        }
        //
        // Lettura di ANAPRO
        // 
        $anapro_rec = $proLib->GetAnapro($anapro_rowid, 'rowid');

        /* Apro il file */
        $csvRes = fopen($WorkCsv, "a+");

        $AnnoProt = substr($anapro_rec['PRONUM'], 0, 4);
        $NumProt = intval(substr($anapro_rec['PRONUM'], 4));
        $DataProt = date("d/m/Y", strtotime($anapro_rec['PRODAR']));
        $StatiFatt = '0';
        foreach ($ElencoFatture as $Fattura) {
            // Preparo i dati da scrivere nel csv 
            $FileFattura = $Fattura['FILEFATTURA'];
            $NFattura = $Fattura['FATTURA'];
            $DataFattura = $Fattura['DATA'];
            $rigacsv = "$FileFattura;$AnnoProt;$NumProt;$DataProt;$NFattura;$DataFattura;$StatiFatt\r";
            if (!fwrite($csvRes, $rigacsv)) {
                fclose($csvRes);
                return false;
            }
        }
        fclose($csvRes);
        return $WorkCsv;
    }

    public function GetFileFromRepository($NomeFile, $dest_param) {
        if (!$NomeFile) {
            $this->setCode(-1);
            $this->setMessage("Non è presente il file da leggere.");
            return false;
        }
        // Preparo le librerie
        $eqAudit = new eqAudit();
        $proLib = new proLib();

        $TmpPath = itaLib::createAppsTempPath('FileFromRepository');
        $DestFile = $TmpPath . '/' . $NomeFile;
        file_put_contents($DestFile, '');

        $urlSchema = parse_url($dest_param);
        switch (strtolower($urlSchema['scheme'])) {
            /**
             * file url
             */
            case 'file':
                $sorg_path = $urlSchema['path'];
                if (!is_dir($sorg_path)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella sorgente inesistente");
                    return false;
                }
                // Se non è un file è stato ugualmente creato in precedenza vuoto.. dovrebbe dare errore se non lo trova?
                if (!is_file($sorg_path . "/" . $NomeFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("File sorgente non presente.");
                    return false;
                }
                if (!@copy($sorg_path . "/" . $NomeFile, $DestFile)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Copia export: $DestFile... fallita");
                    return false;
                }

                $DB = $proLib->getPROTDB()->getDB();
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD, 'Estremi' => "File Copiato dal Repository. $NomeFile", "DB" => $DB, "DSet" => 'ANADOC'));
                return $DestFile;
                break;

            /**
             * ftp url
             */
            case 'ftp':
                if (!$urlSchema['host']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: host mancante");
                    return false;
                }

                if (!$urlSchema['user']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: utente mancante");
                    return false;
                }

                if (!$urlSchema['pass']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: password mancante");
                    return false;
                }

                if (!$urlSchema['path']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: cartella sorgente  mancante");
                    return false;
                }
                $connId = ftp_connect($urlSchema['host']);
                if (!$connId) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Connessione ftp sorgente file SDI fallita");
                    return false;
                }
                if (!ftp_login($connId, $urlSchema['user'], $urlSchema['pass'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Accesso ftp sorgente file SDI fallita");
                    return false;
                }
                if (!ftp_chdir($connId, $urlSchema['path'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella sorgente file SDI non accessibile.");
                    return false;
                }
                // Cancello il file prima di copiarlo
                @unlink($DestFile);

                if (!ftp_get($connId, $DestFile, $NomeFile, FTP_BINARY)) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Trasferimento da Ftp file messaggio PA: " . pathinfo($NomeFile, PATHINFO_BASENAME) . " fallita");
                    return false;
                }

                $DB = $proLib->getPROTDB()->getDB();
                $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_UPD_RECORD, 'Estremi' => "File Copiato dal Repository. $NomeFile", "DB" => $DB, "DSet" => 'ANADOC'));
                ftp_close($connId);

                return $DestFile;
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Risorsa sorgente non riconosciuta.");
                return false;
                break;
        }
    }

    public function GetStileFattura($objSdi) {
        $proLib = new proLib();
        /*
         * Verifico quale stile usare:
         *  Fattura Riepilogo Dati utilizzabile solo per FPR12 e FPA12.
         */
        $anaent_39 = $proLib->GetAnaent('39');
        switch ($anaent_39['ENTDE5']) {
            case'1':
                if ($objSdi->getVersioneFattura() == prosdi::FATTURA_VERSIONE12 || $objSdi->getVersioneFattura() == prosdi::FATTURA_VERSIONE12_FPR12) {
                    $stileFattura = prosdi::$ElencoStiliFattura[prosdi::FATTURA_RIEPILOGO_RIFPA12];
                } else {
                    $stileFattura = prosdi::$ElencoStiliFattura[$objSdi->getVersioneFattura()];
                }
                break;
            default:
                $stileFattura = prosdi::$ElencoStiliFattura[$objSdi->getVersioneFattura()];
                break;
        }
        return $stileFattura;
    }

    

    /**
     * Restituisce l'XML con abbinato il foglio di stile opportuno. L'url del foglio di stile è fornito attraverso utiDownload::getUrl
     * @param string $xml
     * @return string
     */
    public function sdiAttachStyle($xml, $relativePath=''){
        if(!preg_match('/<([A-Za-z0-9]*):(FatturaElettronica|FileMetadati|MetadatiInvioFile|RicevutaScarto|NotificaScarto|RicevutaImpossibilitaRecapito|AttestazioneTrasmissioneFattura|ScartoEsitoCommittente|RicevutaConsegna|NotificaEsito|NotificaMancataConsegna|NotificaEsitoCommittente|NotificaDecorrenzaTermini).*?versione="([A-Z0-9\.]*?)".*?>/i', $xml, $matches)){
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo di xml non supportato.");
            return false;
        }
        
        $namespace = $matches[1];
        $type = $matches[2];
        $version = $matches[3];

        switch(strtolower($type)){
            case 'filemetadati':
            case 'metadatiinviofile':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':MetadatiInvioFile xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':MetadatiInvioFile>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_MT];
                $type = 'MetadatiInvioFile';
                break;
            case 'ricevutascarto':
            case 'notificascarto':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaScarto xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaScarto>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_NS];
                $type = 'NotificaScarto';
                break;
            case 'ricevutaimpossibilitarecapito':
            case 'attestazionetrasmissionefattura':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':AttestazioneTrasmissioneFattura xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.1.xsd ">$2</'.$namespace.':AttestazioneTrasmissioneFattura>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_AT];
                $type = 'AttestazioneTrasmissioneFattura';
                break;
            case 'scartoesitocommittente':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':ScartoEsitoCommittente xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':ScartoEsitoCommittente>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_SE];
                $type = 'ScartoEsitoCommittente';
                break;
            case 'ricevutaconsegna':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':RicevutaConsegna xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':RicevutaConsegna>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_RC];
                $type = 'RicevutaConsegna';
                break;
            case 'notificaesito':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaEsito xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaEsito>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_NE];
                $type = 'NotificaEsito';
                break;
            case 'notificamancataconsegna':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaMancataConsegna xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaMancataConsegna>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_MC];
                $type = 'NotificaMancataConsegna';
                break;
            case 'notificaesitocommittente':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaEsito xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaEsito>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_EC];
                $type = 'NotificaEsito';
                break;
            case 'notificadecorrenzatermini':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaDecorrenzaTermini xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd http://www.w3.org/2000/09/xmldsig# xmldsig-core-schema.xsd">$2</'.$namespace.':NotificaDecorrenzaTermini>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_DT];
                $type = 'NotificaDecorrenzaTermini';
                break;
            case 'fatturaelettronica':
                $xsl = proSdi::$ElencoStiliFattura[strtoupper($version)];
                break;
        }

        $xml = preg_replace('/(\s*<\?xml-stylesheet.*\?>)/U', '', $xml);
        if(preg_match('/<\?xml .*?\?>/', $xml, $matches)){
            $header = preg_replace('/(<\?xml.*version=)("[0-9\.]*")(.*\?>)/U', '$1"1.0"$3', $matches[0]);
        }
        else{
            $header = '<?xml version="1.0" encoding="utf-8"?>';
        }
        $xml = substr($xml, stripos($xml, '<'.$namespace.':'.$type));

        $xslUrl = utiDownload::getUrl($xsl, ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . $xsl, false, true, true);
        $xml = $header."\r\n".'<?xml-stylesheet type="text/xsl" href="'.htmlentities($relativePath.$xslUrl).'"?>'."\r\n".$xml;
        
        return $xml;
    }
    
    /**
     * Restituisce l'HTML del flusso XML elaborato attraverso il foglio di stile
     * @param string $xml
     * @return string
     */
    public function sdiXmlToHtml($xml, $output=self::OUT_STRING){
        if(!preg_match('/<([A-Za-z0-9]*):(FatturaElettronica|FileMetadati|MetadatiInvioFile|RicevutaScarto|NotificaScarto|RicevutaImpossibilitaRecapito|AttestazioneTrasmissioneFattura|ScartoEsitoCommittente|RicevutaConsegna|NotificaEsito|NotificaMancataConsegna|NotificaEsitoCommittente|NotificaDecorrenzaTermini).*?versione="([A-Z0-9\.]*)".*?>/i', $xml, $matches)){
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo di xml non supportato.");
            return false;
        }
        
        $namespace = $matches[1];
        $type = $matches[2];
        $version = $matches[3];

        switch(strtolower($type)){
            case 'filemetadati':
            case 'metadatiinviofile':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':MetadatiInvioFile xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':MetadatiInvioFile>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_MT];
                $type = 'MetadatiInvioFile';
                break;
            case 'ricevutascarto':
            case 'notificascarto':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaScarto xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaScarto>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_NS];
                $type = 'NotificaScarto';
                break;
            case 'ricevutaimpossibilitarecapito':
            case 'attestazionetrasmissionefattura':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':AttestazioneTrasmissioneFattura xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.1.xsd ">$2</'.$namespace.':AttestazioneTrasmissioneFattura>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_AT];
                $type = 'AttestazioneTrasmissioneFattura';
                break;
            case 'scartoesitocommittente':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':ScartoEsitoCommittente xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':ScartoEsitoCommittente>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_SE];
                $type = 'ScartoEsitoCommittente';
                break;
            case 'ricevutaconsegna':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':RicevutaConsegna xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':RicevutaConsegna>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_RC];
                $type = 'RicevutaConsegna';
                break;
            case 'notificaesito':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaEsito xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaEsito>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_NE];
                $type = 'NotificaEsito';
                break;
            case 'notificamancataconsegna':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaMancataConsegna xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaMancataConsegna>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_MC];
                $type = 'NotificaMancataConsegna';
                break;
            case 'notificaesitocommittente':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaEsito xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">$2</'.$namespace.':NotificaEsito>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_EC];
                $type = 'NotificaEsito';
                break;
            case 'notificadecorrenzatermini':
                $xml = preg_replace('/(<'.$namespace.':'.$type.'.*?>)(.*?)(<\/'.$namespace.':'.$type.'.*?>)/s',
                        '<'.$namespace.':NotificaDecorrenzaTermini xmlns:'.$namespace.'="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd http://www.w3.org/2000/09/xmldsig# xmldsig-core-schema.xsd">$2</'.$namespace.':NotificaDecorrenzaTermini>',
                        $xml);
                $xsl = proSdi::$ElencoStiliMessaggio[proSdi::TIPOMESS_DT];
                $type = 'NotificaDecorrenzaTermini';
                break;
            case 'fatturaelettronica':
                $xsl = proSdi::$ElencoStiliFattura[strtoupper($version)];
                break;
        }

        $xmlDom = new DOMDocument();
        $xmlDom->loadXML($xml);

        $xslDom = new DOMDocument();
        $xslDom->load(ITA_BASE_PATH . '/apps/Protocollo/resources/sdi/' . $xsl);

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xslDom);
        
        switch($output){
            case self::OUT_STRING:
                $return = $proc->transformToXML($xmlDom);
                break;
            case self::OUT_DOMDOCUMENT:
                $return = $proc->transformToDoc($xmlDom);
                break;
            case self::OUT_SIMPLEXML:
                $return = simplexml_import_dom($proc->transformToDoc($xmlDom));
                break;
        }

        return $return;
    }
    
    /**
     * Genera un PDF a partire dall'XML della fattura
     * @param string $xml
     * @param string $outputFile
     * @return boolean
     */
    public function sdiXmlToPdf($xml, $outputFile){
        $html = $this->sdiXmlToHtml($xml);
        
        if(!$html){
            return false;
        }
        
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        
        $html = preg_replace('/<meta.*?>/', '', $html);

        $docLib = new docLib();
        $extraParam = array(
            'documentbody'=>$html,
            'documentheader'=>'',
            'documentfooter'=>'',
            'headerHeight'=>'5',
            'footerHeight'=>'5',
            'marginTop'=>'10',
            'marginBottom'=>'10',
            'marginLeft'=>'10',
            'marginRight'=>'10',
            'pageFormat'=>'A4',
            'pageOrientation'=>'portrait'
        );
        if($docLib->Xhtml2Pdf($html, array(), $outputFile, true, $extraParam) == false){
            $this->setErrCode(-1);
            $this->setErrMessage("'Errore nella conversione dell\'xml in pdf'");
            return false;
        }
        
        return true;
    }
}

?>