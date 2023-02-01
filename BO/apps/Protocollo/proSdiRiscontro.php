<?php

/* * 
 *
 * 
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    01.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proSdiRiscontro() {
    $proSdiRiscontro = new proSdiRiscontro();
    $proSdiRiscontro->parseEvent();
    return;
}

class proSdiRiscontro extends itaModel {

    public $PROT_DB;
    public $ITALWEB;
    public $proLib;
    public $proLibSdi;
    public $nameForm = "proSdiRiscontro";
    public $currObjSdi;
    public $Anapro_rec;
    public $Anadoc_rec;
    public $EstrattoFattura;
    public $TipoRiscontro;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibSdi = new proLibSdi();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            $this->currObjSdi = unserialize(App::$utente->getKey($this->nameForm . "_currObjSdi"));
            $this->Anapro_rec = unserialize(App::$utente->getKey($this->nameForm . "_Anapro_rec"));
            $this->EstrattoFattura = unserialize(App::$utente->getKey($this->nameForm . "_EstrattoFattura"));
            $this->TipoRiscontro = unserialize(App::$utente->getKey($this->nameForm . "_TipoRiscontro"));
            $this->Anadoc_rec = unserialize(App::$utente->getKey($this->nameForm . "_Anadoc_rec"));
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_currObjSdi", serialize($this->currObjSdi));
            App::$utente->setKey($this->nameForm . "_Anapro_rec", serialize($this->Anapro_rec));
            App::$utente->setKey($this->nameForm . "_EstrattoFattura", serialize($this->EstrattoFattura));
            App::$utente->setKey($this->nameForm . "_TipoRiscontro", serialize($this->TipoRiscontro));
            App::$utente->setKey($this->nameForm . "_Anadoc_rec", serialize($this->Anadoc_rec));
        }
    }

    function getCurrObjSdi() {
        return $this->currObjSdi;
    }

    function setCurrObjSdi($currObjSdi) {
        $this->currObjSdi = $currObjSdi;
    }

    public function getAnapro_rec() {
        return $this->Anapro_rec;
    }

    public function setAnapro_rec($Anapro_rec) {
        $this->Anapro_rec = $Anapro_rec;
    }

    public function getAnadoc_rec() {
        return $this->Anadoc_rec;
    }

    public function setAnadoc_rec($Anadoc_rec) {
        $this->Anadoc_rec = $Anadoc_rec;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $anapro_pretab = $this->proLib->checkRiscontro(substr($this->Anapro_rec['PRONUM'], 0, 4), substr($this->Anapro_rec['PRONUM'], 4), $this->Anapro_rec['PROPAR']);
                if ($anapro_pretab) {
                    Out::show($this->nameForm . '_VediRiscontri');
                } else {
                    Out::hide($this->nameForm . '_VediRiscontri');
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AccettaInteraFattura':
                        $EstrattoFatture = $this->currObjSdi->getEstrattoFattura();
                        $this->EstrattoFattura = $EstrattoFatture;
                        $this->TipoRiscontro = 'Intera';
                        $this->ConfermaAccettaFattura();
                        break;
                    case $this->nameForm . '_RifiutaInteraFattura':
                        $EstrattoFatture = $this->currObjSdi->getEstrattoFattura();
                        $this->EstrattoFattura = $EstrattoFatture;
                        $this->TipoRiscontro = 'Intera';
                        $this->RichiediMotivoRifiuto();
                        break;

                    case $this->nameForm . '_ConfermaAccettaIntera':
                        $retFile = $this->proLibSdi->CreaXmlEsitoCommittente($this->EstrattoFattura, $this->Anapro_rec, 'EC01', '', true);
                        if (!$retFile) {
                            Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                            break;
                        }
                        $this->ProtocollaRiscontro($retFile, 'Accettazione');
                        break;
                    case $this->nameForm . '_ConfermaAccettaSingola':
                        $retFile = $this->proLibSdi->CreaXmlEsitoCommittente($this->EstrattoFattura, $this->Anapro_rec, 'EC01', '', false);
                        if (!$retFile) {
                            Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                            break;
                        }
                        $this->ProtocollaRiscontro($retFile, 'Accettazione');
                        break;
                    case $this->nameForm . '_ConfermaRifiutaIntera':
                        if (!$_POST[$this->nameForm . '_MotivoRifiuto']) {
                            Out::msgInfo("Attenzione", "In un esito di rifiuto la motivazione del rifiuto non può essere vuota.");
                            $this->RichiediMotivoRifiuto();
                            break;
                        }
                        $MotivoRifiuto = $_POST[$this->nameForm . '_MotivoRifiuto'];
                        $retFile = $this->proLibSdi->CreaXmlEsitoCommittente($this->EstrattoFattura, $this->Anapro_rec, 'EC02', $MotivoRifiuto, true);
                        if (!$retFile) {
                            Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                            break;
                        }
                        $this->ProtocollaRiscontro($retFile, 'Rifiuto');
                        break;
                    case $this->nameForm . '_ConfermaRifiutaSingola':
                        if (!$_POST[$this->nameForm . '_MotivoRifiuto']) {
                            Out::msgInfo("Attenzione", "In un esito di rifiuto la motivazione del rifiuto non può essere vuota.");
                            $this->RichiediMotivoRifiuto();
                            break;
                        }
                        $MotivoRifiuto = $_POST[$this->nameForm . '_MotivoRifiuto'];
                        $retFile = $this->proLibSdi->CreaXmlEsitoCommittente($this->EstrattoFattura, $this->Anapro_rec, 'EC02', $MotivoRifiuto, false);
                        if (!$retFile) {
                            Out::msgStop("Attenzione", $this->proLibSdi->getErrMessage());
                            break;
                        }
                        $this->ProtocollaRiscontro($retFile, 'Rifiuto');
                        break;

                    case $this->nameForm . '_VediRiscontri':
                        proRic::proRicLegame($this->proLib, $this->nameForm, 'returnLegameRiscontri', $this->PROT_DB, $this->Anapro_rec);
                        break;
                }
                break;


            case 'RifiutaSingolaFattura':
                $Id = $_POST['id'];
                list($keyFatt, $keyBody) = explode('_', $Id);
                $EstrattoFatture = $this->currObjSdi->getEstrattoFattura();
                $this->EstrattoFattura = $EstrattoFatture[$keyFatt]['Body'][$keyBody];
                $this->TipoRiscontro = 'Singola';
                $this->RichiediMotivoRifiuto();
                break;

            case 'AccettaSingolaFattura':
                $Id = $_POST['id'];
                list($keyFatt, $keyBody) = explode('_', $Id);
                $EstrattoFatture = $this->currObjSdi->getEstrattoFattura();
                $this->EstrattoFattura = $EstrattoFatture[$keyFatt]['Body'][$keyBody];
                $this->TipoRiscontro = 'Singola';
                $this->ConfermaAccettaFattura();
                break;

            case 'VediXmlFattura':
                // Controllo id ?
                $Id = $_POST['id'];
                $FilePathFattura = $this->currObjSdi->getFilePathFattura();
                $stileFattura = prosdi::$ElencoStiliFattura[$this->currObjSdi->getVersioneFattura()];
//                $this->proLibSdi->VisualizzaXmlConStile(proSdi::StileFattura, $FilePathFattura[$Id]);
                $this->proLibSdi->VisualizzaXmlConStile($stileFattura, $FilePathFattura[$Id], $this->Anapro_rec);
                break;

            case 'VediAllegatoFattura':
                $Id = $_POST['id'];
                list($idAllegato, $idFattura) = explode("@", $Id);
                $EstrattoAllegatiFattura = $this->currObjSdi->getEstrattoAllegatiFattura();
                Out::openDocument(utiDownload::getUrl($EstrattoAllegatiFattura[$idFattura][$idAllegato]['NomeAttachment'], $EstrattoAllegatiFattura[$idFattura][$idAllegato]['FilePathAllegato']));
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currObjSdi');
        App::$utente->removeKey($this->nameForm . '_Anapro_rec');
        App::$utente->removeKey($this->nameForm . '_EstrattoFattura');
        App::$utente->removeKey($this->nameForm . '_TipoRiscontro');
        App::$utente->removeKey($this->nameForm . '_Anadoc_rec');
        Out::closeDialog($this->nameForm);
    }

    private function RichiediMotivoRifiuto() {
        $FatturaSingola = '';
        if (isset($this->EstrattoFattura['NumeroFattura'])) {
            $DataFattura = $dataVer = date("d/m/Y", strtotime($this->EstrattoFattura['DataFattura']));
            $FatturaSingola = '<b>Fattura N. ' . $this->EstrattoFattura['NumeroFattura'] . ' del ' . $DataFattura . '</b>';
            $valori[] = array(
                'label' => array(
                    'value' => $FatturaSingola,
                    'style' => 'width:300px;display:block;float:left;padding: 0 5px 0 0;text-align: left;'
                ),
                'id' => $this->nameForm . '_InfoRifiuto',
                'name' => $this->nameForm . '_InfoRifiuto',
                'type' => 'text'
            );
        }
        $valori[] = array(
            'label' => array(
                'value' => "Motiva il rifiuto ",
                'style' => 'width:100px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_MotivoRifiuto',
            'name' => $this->nameForm . '_MotivoRifiuto',
            'type' => 'text',
            'style' => 'margin:2px;width:400px;',
            'value' => '',
            'class' => 'required'
        );
        Out::msgInput(
                'Rifiuto ' . $this->TipoRiscontro . ' Fattura Elettronica', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaRifiuta' . $this->TipoRiscontro, 'model' => $this->nameForm)
                ), $this->nameForm
        );
        Out::hide($this->nameForm . '_InfoRifiuto');
    }

    private function ConfermaAccettaFattura() {
        $MessaggioFattura = "Confermi l'accettazione della Fattura Elettronica";
        if (isset($this->EstrattoFattura['NumeroFattura'])) {
            $DataFattura = $dataVer = date("d/m/Y", strtotime($this->EstrattoFattura['DataFattura']));
            $MessaggioFattura.= ': <b>' . $this->EstrattoFattura['NumeroFattura'] . '</b> del <b>' . $DataFattura . '</b>';
        }
        Out::msgQuestion("Accettazione $this->TipoRiscontro Fattura.", $MessaggioFattura . ' ?', array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAccetta', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAccetta' . $this->TipoRiscontro, 'model' => $this->nameForm)
                )
        );
    }

    //tipoOperazione Accettazione/Rifiuto
    private function ProtocollaRiscontro($xmlFile, $tipoOperazione) {
        $anaent_38 = $this->proLib->GetAnaent('38');
        $FileFatturaUnivoco = $this->currObjSdi->getFileFatturaUnivoco();
        list($P1File, $PFileConEstensione) = explode('_', $FileFatturaUnivoco);
        list($P2File) = explode('.', $PFileConEstensione);
        // Prendo ultimo progressivo usato 
        $sql = "SELECT TDAGVAL 
                    FROM TABDAG 
                    WHERE TDAGVAL LIKE '$P1File\_$P2File\_EC\_%' AND
                    TDAGFONTE = 'MESSAGGIO_SDI' AND 
                    TDCLASSE= 'ANAPRO' AND 
                    TDAGCHIAVE='NomeFileMessaggio' ";
        $Tabdat_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        if (!$Tabdat_tab) {
            $ProgFile = '001';
        } else {
            $TotMessaggiSdi = count($Tabdat_tab);
            $ProgFile = $TotMessaggiSdi + 1;
        }
        $ProgFile = str_pad($ProgFile, '3', '0', STR_PAD_LEFT);
        /*
         * Preparo Dati da passare a proArri
         */
        // Compongo il nome del file xml
        $NomeFileXml = $P1File . '_' . $P2File . '_EC_' . $ProgFile . '.xml';
        $Oggi = date('Ymd');
        // Composizione dell'oggetto
        if ($this->TipoRiscontro == 'Intera') {
            //Fattura Intera
            $Oggetto = $tipoOperazione . ': ';
            foreach ($this->EstrattoFattura as $keyFatt => $Fattura) {
                foreach ($Fattura['Body'] as $keyBody => $BodyFattura) {
                    $DataFattura = $dataVer = date("d/m/Y", strtotime($BodyFattura['DataFattura']));
                    $Oggetto.= ' Fattura N. ' . $BodyFattura['NumeroFattura'] . ' del ' . $DataFattura . ', ';
                }
            }
            $Oggetto.=' Fornitore: ' . $Fattura['Header']['Fornitore']['Denominazione'] . '.';
        } else {
            // Solo Singola Fattura o Lotto di Fatture
            $EstrattoInteraFattura = $this->currObjSdi->getEstrattoFattura();
            App::log($EstrattoInteraFattura);
            $Oggetto = $tipoOperazione . ': ';
            $DataFattura = $dataVer = date("d/m/Y", strtotime($this->EstrattoFattura['DataFattura']));
            $Oggetto.= ' Fattura N. ' . $this->EstrattoFattura['NumeroFattura'] . ' del ' . $DataFattura . ', ';
            $Oggetto.=' Fornitore: ' . $EstrattoInteraFattura[0]['Header']['Fornitore']['Denominazione'] . '.';
        }
        /*
         * Preparazione datiElemento
         */
        $datiElemento['Oggetto'] = $Oggetto;
        $datiElemento['PRODAS'] = $Oggi;
        $datiElemento['PRODAA'] = $Oggi;
        $TipoDoc = 'SDIP';
        if ($anaent_38['ENTDE4']) {
            $TipoDoc = $anaent_38['ENTDE4'];
        }
        $datiElemento['PROCODTIPODOC'] = $TipoDoc;
        $datiElemento['Propre1'] = substr($this->Anapro_rec['PRONUM'], 4);
        $datiElemento['Propre2'] = substr($this->Anapro_rec['PRONUM'], 0, 4);
        $datiElemento['PROPARPRE'] = $this->Anapro_rec['PROPAR'];
        $allegati[] = array('DATAFILE' => $NomeFileXml, 'FILE' => $xmlFile, 'DATATIPO' => '');
        /*
         * Istanzio nuovo oggettoSdi per l'EC.
         */
        $FileSdi = array('LOCAL_FILEPATH' => $xmlFile, 'LOCAL_FILENAME' => $NomeFileXml);
        $NewcurrObjSdi = proSdi::getInstance($FileSdi);
        if (!$NewcurrObjSdi) {
            Out::msgStop("Attenzione", 'Errore nell\'istanziare proSdi.');
            return false;
        }
        if ($NewcurrObjSdi->isMessaggioSdi()) {
            if (isset($this->Anadoc_rec) && $this->Anadoc_rec['DOCIDMAIL']) {
                $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL = '{$this->Anadoc_rec['DOCIDMAIL']}'";
                $Mail_Archivio_rec = ItaDB::DBSQLSelect($this->ITALWEB, $sql, false);
                if ($Mail_Archivio_rec) {
                    $metadata = unserialize($Mail_Archivio_rec["METADATA"]);
                    if ($metadata['emlStruct']['ita_PEC_info'] != "N/A" && isset($metadata['emlStruct']['ita_PEC_info']['dati_certificazione'])) {
                        $datiElemento['PROMAIL'] = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'];
                    }
                }
            } else {
                // EFAS:
                $anaent_45 = $this->proLib->GetAnaent('45');
                if ($this->Anapro_rec['PROCODTIPODOC'] && $this->Anapro_rec['PROCODTIPODOC'] == $anaent_45['ENTDE5']) {
                    // Prendo id mail dal padre:
                    $AnaproPadre_rec = $this->proLib->GetAnapro($this->Anapro_rec['PROPRE'], 'codice', $this->Anapro_rec['PROPARPRE']);
                    $Anadoc_rec = $this->proLibSdi->getAnadocFlussoFromAnapro($AnaproPadre_rec);
                    if ($Anadoc_rec) {
                        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL = '{$Anadoc_rec['DOCIDMAIL']}'";
                        $Mail_Archivio_rec = ItaDB::DBSQLSelect($this->ITALWEB, $sql, false);
                        if ($Mail_Archivio_rec) {
                            $metadata = unserialize($Mail_Archivio_rec["METADATA"]);
                            if ($metadata['emlStruct']['ita_PEC_info'] != "N/A" && isset($metadata['emlStruct']['ita_PEC_info']['dati_certificazione'])) {
                                $datiElemento['PROMAIL'] = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'];
                            }
                        }
                    }
                }
            }
        }
        /*
         * Apertura di proArri
         */
        $model = 'proArri';
        $_POST['datiPost'] = $_POST;
        $_POST['event'] = 'openform';
        $_POST['tipoProt'] = 'P';
        $_POST['datiRiscontro'] = $datiElemento;
        $_POST['datiRiscontro']['ELENCOALLEGATI'] = $allegati;
        $_POST['objSdi'] = serialize($NewcurrObjSdi);
        itaLib::openForm($model);
        Out::hide($this->nameForm);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
        $this->close();
    }

}

?>
