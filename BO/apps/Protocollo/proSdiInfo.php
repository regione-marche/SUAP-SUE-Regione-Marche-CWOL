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

function proSdiInfo() {
    $proSdiInfo = new proSdiInfo();
    $proSdiInfo->parseEvent();
    return;
}

class proSdiInfo extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibSdi;
    public $nameForm = "proSdiInfo";
    public $currObjSdi;
    public $Anapro_rec = array();

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibSdi = new proLibSdi();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->currObjSdi = unserialize(App::$utente->getKey($this->nameForm . "_currObjSdi"));
            $this->Anapro_rec = App::$utente->getKey($this->nameForm . "_Anapro_rec");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_currObjSdi", serialize($this->currObjSdi));
            App::$utente->setKey($this->nameForm . "_Anapro_rec", $this->Anapro_rec);
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

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;
            case 'VediXmlFattura':
                // Controllo id ?
                $Id = $_POST['id'];
                $FilePathFattura = $this->currObjSdi->getFilePathFattura();
                $stileFattura = $this->proLibSdi->GetStileFattura($this->currObjSdi);
//                $this->proLibSdi->VisualizzaXmlConStile(proSdi::StileFattura, $FilePathFattura[$Id]);
                $this->proLibSdi->VisualizzaXmlConStile($stileFattura, $FilePathFattura[$Id], $this->Anapro_rec);
                break;

            case 'VediAllegatoFattura':
                $Id = $_POST['id'];
                list($idAllegato, $idFattura) = explode("-", $Id);
                $EstrattoAllegatiFattura = $this->currObjSdi->getEstrattoAllegatiFattura();
                $ext = '';
                if (pathinfo($EstrattoAllegatiFattura[$idFattura][$idAllegato]['NomeAttachment'], PATHINFO_EXTENSION) == '') {
                    if ($EstrattoAllegatiFattura[$idFattura][$idAllegato]['FormatoAttachment']) {
                        $ext = '.' . $EstrattoAllegatiFattura[$idFattura][$idAllegato]['FormatoAttachment'];
                    } else {
                        $ext = '.' . pathinfo($EstrattoAllegatiFattura[$idFattura][$idAllegato]['FilePathAllegato'], PATHINFO_EXTENSION);
                    }
                }
                Out::openDocument(utiDownload::getUrl($EstrattoAllegatiFattura[$idFattura][$idAllegato]['NomeAttachment'] . $ext, $EstrattoAllegatiFattura[$idFattura][$idAllegato]['FilePathAllegato']));
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currObjSdi');
        App::$utente->removeKey($this->nameForm . '_Anapro_rec');
        Out::closeDialog($this->nameForm);
    }

}

?>
