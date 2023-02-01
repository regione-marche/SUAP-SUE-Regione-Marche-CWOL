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
 * @version    17.10.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proSegnatura {

    const SEGNATURA_DTD = "Segnatura-2009-03-31.dtd";

    public $proLib;
    public $ErrCode;
    public $ErrMessage;
    private $tempPath;
    private $AnaproRecord;

    public function getErrCode() {
        return $this->ErrCode;
    }

    private function setErrCode($ErrCode) {
        $this->ErrCode = $ErrCode;
    }

    public function getErrMessage() {
        return $this->ErrMessage;
    }

    private function setErrMessage($ErrMessage) {
        $this->ErrMessage = $ErrMessage;
    }

    public function getAnaproRecord() {
        return $this->AnaproRecord;
    }

    public function setAnaproRecord($AnaproRecord) {
        $this->AnaproRecord = $AnaproRecord;
    }

    public static function getInstance($Pronum, $Propar, $ExtraParam = array()) {
        if (!$Pronum || !$Propar) {
            return false;
        }
        try {
            $obj = new proSegnatura();
            $obj->proLib = new proLib();
            $Anapro_rec = $obj->proLib->GetAnapro($Pronum, 'codice', $Propar);
            if (!$Anapro_rec) {
                return false;
            }
            $obj->setAnaproRecord = $Anapro_rec;
        } catch (Exception $e) {
            return false;
        }

        return $obj;
    }

    public function GetTempPath() {
        $randPath = itaLib::getRandBaseName();
        $percorsoTmp = itaLib::getAppsTempPath("proSegnatura-$randPath");
        if (!@is_dir($percorsoTmp)) {
            if (!itaLib::createAppsTempPath("proSegnatura-$randPath")) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita.");
                return false;
            }
        }
        $this->tempPath = $percorsoTmp;
        return $this->tempPath;
    }

    public function cleanData() {
        return itaLib::deleteDirRecursive($this->tempPath);
    }

    public static function getStringaSegnatura($anapro_rec, $modello = '') {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
        $proLib = new proLib();
        if (!$modello) {
            $anaent_rec = $proLib->GetAnaent('33');
            $separatore = $anaent_rec['ENTDE1'];
            $modello = $anaent_rec['ENTVAL'];
        }
        if (!$modello) {
            $modello = '@{$NUMEROSTR}@@{$REG_DATA}@@{$AMM_CODICE}@@{$REGISTRO}@@{$REG_UFFICIO}@@{$CLASSIFICAZIONE}@@{$FASCICOLO}@@{$TIPO}@@{$IRIS}@';
            if (!$separatore)
                $separatore = '-';
        }
        $proLibVar = new proLibVariabili();
        $proLibVar->setAnapro_rec($anapro_rec);
        $dictionaryValues = $proLibVar->getVariabiliSegnatura()->getAllData();
        $wsep = '';
        foreach ($dictionaryValues as $key => $valore) {
            $search = '@{$' . $key . '}@';
            if ($valore) {
                if (strpos($modello, $search) === 0) {
                    $wsep = '';
                } else {
                    $wsep = $separatore;
                }
            } else {
                $wsep = '';
            }
            $replacement = $wsep . $valore;
            $modello = str_replace($search, $replacement, $modello);
        }

        if (strpos($modello, '@{$') !== false) {
            return false;
        }
        if (strpos($modello, '}@') !== false) {
            return false;
        }

//        app::log('Modello');
//        app::log($modello);
        return $modello;
    }

    public static function testSegnatura($fileXml) {
        $ret = array("Status" => 0, "Message" => '');
        $root = 'Segnatura';
        $xml = file_get_contents($fileXml);
        if ($xml === false) {
            $ret['Status'] = "-1";
            $ret['Message'] = "Errore in lettura xml da controllare.";
            return $ret;
        }
        $old = new DOMDocument;
        if (!$old->loadXML($xml)) {
            $ret['Status'] = "-1";
            $ret['Message'] = "Errore in caricamento xml da controllare.";
            return $ret;
        }
        try {
            $creator = new DOMImplementation;
            $doctype = $creator->createDocumentType($root, null, ITA_BASE_PATH . '/apps/Protocollo/' . self::SEGNATURA_DTD);
            $new = $creator->createDocument(null, null, $doctype);
            $new->encoding = "ISO-8859-1";
            $oldNode = $old->getElementsByTagName($root)->item(0);
            $newNode = $new->importNode($oldNode, true);
            $new->appendChild($newNode);

            if ($new->validate()) {
                $ret['Status'] = "0";
                $ret['Message'] = "Segnatura Valida";
            } else {
                $ret['Status'] = "-2";
                $ret['Message'] = "Segnatura NON Valida";
//                $ret['Message'] = libxml_get_last_error();
            }
            return $ret;
        } catch (Exception $exc) {
            $ret['Status'] = "-3";
            $ret['Message'] = "Errore Generale:" . $exc->getMessage();
            return $ret;
        }
    }

    function getSegnaturaArray() {

        $anapro_rec = $this->AnaproRecord;
        /*
         * Lettura Parametri
         */
        $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], "D");
        $anamed_dest = $this->proLib->GetAnamed($anapro_rec['PROCON'], 'codice');
        $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $anaent_2 = $this->proLib->GetAnaent('2');
        $anaent_26 = $this->proLib->GetAnaent('26');
        $anaent_28 = $this->proLib->GetAnaent('28');

        /*
         * Valorizzazione dati
         */
        $dataReg = date('Y-m-d', strtotime($anapro_rec['PRODAR']));
        $confermaRicezione = array();
        if ($anaent_28['ENTDE6'] == '1') {
            $confermaRicezione = array('confermaRicezione' => 'si');
        }
        $tipoIndirizzoTelematico = array("tipo" => "smtp");
        $xmlArray = array();
        /*
         * Intestazione
         */
        $xmlArray['Intestazione']['Identificatore']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
        $xmlArray['Intestazione']['Identificatore']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];
        $NumeroRegistrazione = str_pad(substr($anapro_rec['PRONUM'], 4), 7, '0', STR_PAD_LEFT);
        $xmlArray['Intestazione']['Identificatore']['NumeroRegistrazione']['@textNode'] = $NumeroRegistrazione;
        $xmlArray['Intestazione']['Identificatore']['DataRegistrazione']['@textNode'] = $dataReg;
        if ($anapro_rec['PROPAR'] == 'P' || $anapro_rec['PROPAR'] == 'C') {
            //
            // Origine
            //
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anaent_26['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anaent_2['ENTDE2'] . ' ' . $anaent_2['ENTDE3'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = $anaent_2['ENTDE1'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = $anaent_26['ENTDE2'];

            //
            // Destinazione
            //                
            $destinazione = array();
            $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
            if (!$tipoIndirizzoTelematicoDest['tipo']) {
                $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
            }
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
            $destinazione['IndirizzoTelematico']['@textNode'] = "";
            $destinazione['@attributes'] = $confermaRicezione;
            if ($anamed_dest['MEDCODAOO'] != '') {
                $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_dest['MEDDENAOO'];
                $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_dest['MEDCODAOO'];
                $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_dest['MEDEMA'];
                $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_dest['MEDIND'] . " " . $anamed_dest['MEDCAP'] . " " . $anamed_dest['MEDCIT'] . " (" . $anamed_dest['MEDPRO'] . ")";
            }
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;

            foreach ($anades_tab as $key => $anades_rec) {
                if ($anades_rec['DESCOD'] != '') {
                    $anamed_rec = $this->proLib->GetAnamed($anades_rec['DESCOD'], 'codice');
                    $destinazione = array();
                    $tipoIndirizzoTelematicoDest = ($anamed_dest['MEDTIPIND'] == 'pec') ? array("tipo" => "smtp") : array("tipo" => $anamed_dest['MEDTIPIND']);
                    if (!$tipoIndirizzoTelematicoDest['tipo']) {
                        $tipoIndirizzoTelematicoDest['tipo'] = "smtp";
                    }
                    $destinazione['@attributes'] = $confermaRicezione;
                    $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                    $destinazione['IndirizzoTelematico']['@textNode'] = "";
                    if ($anamed_rec['MEDCODAOO'] != '') {
                        $destinazione['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['Amministrazione']['Denominazione']['@textNode'] = $anamed_rec['MEDDENAOO'];
                        $destinazione['Destinatario']['Amministrazione']['CodiceAmministrazione']['@textNode'] = $anamed_rec['MEDCODAOO'];
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematicoDest;
                        $destinazione['Destinatario']['IndirizzoTelematico']['@textNode'] = $anamed_rec['MEDEMA'];
                        $destinazione['Destinatario']['IndirizzoPostale']['Denominazione']['@textNode'] = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCAP'] . " " . $anamed_rec['MEDCIT'] . " (" . $anamed_rec['MEDPRO'] . ")";
                    }
                    $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
                }
            }

            //
            // Risposta
            //                
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $xmlArray['Intestazione']['Risposta']['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
        } else if ($anapro_rec['PROPAR'] == 'A') {
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['Denominazione']['@textNode'] = htmlentities($anapro_rec['PRONOM'], ENT_COMPAT, 'UTF-8');
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['CodiceAmministrazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoPostale']['Denominazione']['@textNode'] = $anapro_rec['PROIND'];
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@attributes'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['Amministrazione']['IndirizzoTelematico']['@textNode'] = $anapro_rec['PROMAIL'];
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['Denominazione']['@textNode'] = '';
            $xmlArray['Intestazione']['Origine']['Mittente']['AOO']['CodiceAOO']['@textNode'] = '';
            //
            // Destinazione
            // 
            $destinazione['IndirizzoTelematico']['@attributes'] = $tipoIndirizzoTelematico;
            $destinazione['IndirizzoTelematico']['@textNode'] = $anaent_26['ENTDE4'];
            $xmlArray['Intestazione']['Destinazione'][] = $destinazione;
        }
        //
        // Oggetto
        //
        //        $xmlArray['Intestazione']['Oggetto']['@textNode'] = $anaogg_rec['OGGOGG'];
        $xmlArray['Intestazione']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);

        //
        // Descrizione
        //
        $anadoc_tab = $this->proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], ' AND DOCSERVIZIO=0  ORDER BY DOCTIPO');
        if ($anadoc_tab) {
            $descrizione = array();
            $principaleTrovato = false;
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $principaleTrovato = true;
                }
            }
            if ($principaleTrovato === false) {
                $xmlArray = array('stato' => false, 'messaggio' => 'Non è presente nessun Allegato principale. Selezionarne uno per poter continuare.');
                return $xmlArray;
            }
            foreach ($anadoc_tab as $key => $anadoc_rec) {
                if ($anadoc_rec['DOCTIPO'] == '') {
                    $descrizione['Documento']['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
                    //                    $descrizione['Documento']['Oggetto']['@textNode'] = $anaogg_rec['OGGOGG'];
                    $descrizione['Documento']['Oggetto']['@textNode'] = htmlspecialchars($anaogg_rec['OGGOGG'], ENT_COMPAT);
                } else {
                    $documento = array();
                    $documento['@attributes'] = array('nome' => htmlspecialchars($anadoc_rec['DOCNAME'], ENT_COMPAT));
                    $documento['TipoDocumento']['@textNode'] = '';
                    $descrizione['Allegati']['Documento'][] = $documento;
                }
            }
            $xmlArray['Descrizione'] = $descrizione;
        } else {
            $xmlArray['Descrizione']['TestoDelMessaggio']['@textNode'] = '';
        }
        return $xmlArray;
    }

    public function GetConfermaRicezione() {
        
    }

}

?>