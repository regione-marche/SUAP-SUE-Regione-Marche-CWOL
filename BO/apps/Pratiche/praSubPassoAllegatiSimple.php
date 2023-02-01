<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAllegati.class.php';

function praSubPassoAllegatiSimple() {
    $praSubPassoAllegatiSimple = new praSubPassoAllegatiSimple();
    $praSubPassoAllegatiSimple->parseEvent();
    return;
}

class praSubPassoAllegatiSimple extends praSubPasso {

    public $nameForm = 'praSubPassoAllegatiSimple';
    public $gridAllegati = 'praSubPassoAllegatiSimple_gridAllegati';
    public $keyPasso;
    public $praLibAllegati;
    public $passAlleSimple = array();

    function __construct() {
        parent::__construct();
    }

    public function postInstance() {
        parent::postInstance();
        $this->praLibAllegati = new praLibAllegati();
        $this->gridAllegati = $this->nameForm . '_gridAllegati';
        //
        $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
        $this->passAlleSimple = App::$utente->getKey($this->nameForm . '_passAlleSimple');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
            App::$utente->setKey($this->nameForm . '_passAlleSimple', $this->passAlleSimple);
        }
    }

    function getKeyPasso() {
        return $this->keyPasso;
    }

    function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }
    
    function getPassAlleSimple() {
        return $this->passAlleSimple;
    }

    function setPassAlleSimple($passAlleSimple) {
        $this->passAlleSimple = $passAlleSimple;
    }

        public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->keyPasso = "";
                $this->passAlleSimple = array();
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->passAlleSimple) == true) {
                            $doc = $this->passAlleSimple[$_POST['rowid']];
                            $gesnum = substr($this->keyPasso, 0, 10);
                            $this->praLibAllegati->ApriAllegato($this->nameForm, $doc, $gesnum, $this->keyPasso);
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        $this->CaricaAllegati();
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        switch ($_POST['colName']) {
                            case 'PREVIEW':
                                $allegato = $this->passAlleSimple[$_POST['rowid']];
                                $ext = pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION);
                                if ($ext != strtolower("p7m")) {
                                    break;
                                }
                                $this->praLib->VisualizzaFirme($allegato['FILEPATH'], $allegato['FILEORIG']);
                                break;
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_passAlleSimple');
        parent::close();
    }

    public function returnToParent($close = true) {
        parent::returnToParent($close);
    }

    function CaricaAllegati() {
        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '$this->keyPasso'";
        $allegati_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        $arrayData = array();
        foreach ($allegati_tab as $allegati_rec) {
            $arrayDataTmp = $this->getArrayDataAllegato($allegati_rec);
            $arrayData[] = $arrayDataTmp;
        }
        $this->passAlleSimple = $arrayData;
        $this->CaricaGriglia($this->gridAllegati, $this->passAlleSimple, '1', '100000');
    }

    function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '20') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    public function getArrayDataAllegato($dataDetail_rec) {
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($this->keyPasso, 0, 4), $this->keyPasso, "PASSO", false);

        $strDest = $preview = "";
        $ext = pathinfo($dataDetail_rec['PASFIL'], PATHINFO_EXTENSION);
        if ($ext == strtolower("p7m")) {
            $preview = $this->praLibAllegati->GetImgPreview($ext, $pramPath, $dataDetail_rec);
        }
        $stato = $this->praLib->GetStatoAllegati($dataDetail_rec['PASSTA']);
        //Valorizzo Tabella
        $arrayDataTmp = array();
        if ($dataDetail_rec['PASNAME']) {
            $arrayDataTmp['NAME'] = $dataDetail_rec['PASNAME'];
        } else {
            $arrayDataTmp['NAME'] = $dataDetail_rec['PASFIL'];
        }
        $arrayDataTmp['INFO'] = $dataDetail_rec['PASNOT'];
        $arrayDataTmp['SIZE'] = $this->praLib->formatFileSize(filesize($pramPath . "/" . $dataDetail_rec['PASFIL']));
        $arrayDataTmp['SOST'] = $this->praLib->CheckSostFile($this->currGesnum, $dataDetail_rec['PASSHA2'], $dataDetail_rec['PASSHA2SOST']);
        $arrayDataTmp['PREVIEW'] = $preview;
        $arrayDataTmp['STATO'] = $dataDetail_rec['PASLOG'];
        $arrayDataTmp['STATOALLE'] = $stato;

        /*
         * Se evidenziato cambio il nome dell'allegato in rosso
         */
        $color = '#' . str_pad(dechex($dataDetail_rec['PASEVI']), 6, "0", STR_PAD_LEFT);
        if ($dataDetail_rec["PASEVI"] == 1) {
            if ($dataDetail_rec['PASNAME']) {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASNAME'] . "</p>";
            } else {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASFIL'] . "</p>";
            }
        } elseif ($dataDetail_rec['PASEVI'] != 1 && $dataDetail_rec['PASEVI'] != 0 && !empty($dataDetail_rec['PASEVI'])) {
            if ($dataDetail_rec['PASNAME']) {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASNAME'] . "</p>";
            } else {
                $arrayDataTmp['NAME'] = "<p style = 'color:$color;font-weight:bold;font-size:1.2em;'>" . $dataDetail_rec['PASFIL'] . "</p>";
            }
        }

        /*
         * Classificazione
         */
        $Anacla_rec = $this->praLib->GetAnacla($dataDetail_rec['PASCLAS']);
        $arrayDataTmp['CLASSIFICAZIONE'] = $Anacla_rec['CLADES'];

        /*
         * Destinazioni
         */
        $arrayDest = unserialize($dataDetail_rec['PASDEST']);
        if (is_array($arrayDest)) {
            $strDest = $this->praLibAllegati->getStringDestinatari($dataDetail_rec['PASDEST']);
            $arrayDataTmp['DESTINAZIONI'] = "<div class=\"ita-html\"><span style=\"color:red;\" class=\"ita-Wordwrap ita-tooltip\" title=\"$strDest\"><b>" . count($arrayDest) . "</b></span></div>";
        }

        /*
         * Blocco gli allegati protocollati o messi alla firma PASSO
         */
        if ($dataDetail_rec['PASPRTROWID'] != 0 && $dataDetail_rec['PASPRTCLASS'] == "PRACOM") {
            $pracom_rec = $this->praLib->GetPracom($dataDetail_rec['PASPRTROWID'], "rowid");
            if ($pracom_rec['COMPRT']) {
                $dataPrt = substr($pracom_rec['COMDPR'], 6, 2) . "/" . substr($pracom_rec['COMDPR'], 4, 2) . "/" . substr($pracom_rec['COMDPR'], 0, 4);
                $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMPRT'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Protocollazione PASSO</span>";
            } elseif ($pracom_rec['COMIDDOC']) {
                $dataPrt = substr($pracom_rec['COMDATADOC'], 6, 2) . "/" . substr($pracom_rec['COMDATADOC'], 4, 2) . "/" . substr($pracom_rec['COMDATADOC'], 0, 4);
                $arrayDataTmp['PROTOCOLLO'] = $pracom_rec['COMIDDOC'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Invio al Protocollo per la firma</span>";
            }
        }

        /*
         * Blocco gli allegati protocollati PRATICA
         */
        if ($dataDetail_rec['PASPRTROWID'] != 0 && $dataDetail_rec['PASPRTCLASS'] == "PROGES") {
            $proges_rec = $this->praLib->GetProges($dataDetail_rec['PASPRTROWID'], "rowid");
            if ($proges_rec['GESNPR']) {
                $meta = unserialize($proges_rec['GESMETA']);
                $dataPrt = $meta['DatiProtocollazione']['Data']['value'];
                $arrayDataTmp['PROTOCOLLO'] = $proges_rec['GESNPR'] . " del <br>$dataPrt";
                $arrayDataTmp['LOCK'] = "<span class=\"ita-icon ita-icon-lock-24x24\">Allegato Bloccato per Protocollazione PRATICA</span>";
            }
        }

        $arrayDataTmp['FILEINFO'] = $dataDetail_rec['PASNOT'];
        $arrayDataTmp['FILEPATH'] = $pramPath . "/" . $dataDetail_rec['PASFIL'];
        $arrayDataTmp['FILEORIG'] = $dataDetail_rec['PASNAME'];
        $arrayDataTmp['FILENAME'] = $dataDetail_rec['PASFIL'];
        $arrayDataTmp['PROVENIENZA'] = $dataDetail_rec['PASCLA'];
        $arrayDataTmp['PASDEST'] = $dataDetail_rec['PASDEST'];
        $arrayDataTmp['PASNOTE'] = $dataDetail_rec['PASNOTE'];
        $arrayDataTmp['PASMETA'] = $dataDetail_rec['PASMETA'];
        $arrayDataTmp['PASCLAS'] = $dataDetail_rec['PASCLAS'];
        $arrayDataTmp['PASTIPO'] = $dataDetail_rec['PASTIPO'];
        $arrayDataTmp['ROWID'] = $dataDetail_rec['ROWID'];
        return $arrayDataTmp;
    }

}
