<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    22.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

class praLibDestinazioni extends itaModel {

    /**
     * Libreria di funzioni per la generazioni di passi da destinazioni FO
     */
    public $praLib;
    public $proLib;
    public $PRAM_DB;
    private $errMessage;
    private $errCode;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->proLib = new proLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function addPassiDaDestinazioni($gesnum) {
        /*
         * Mi trovo tutti gli allegati di FO con le destinazioni
         */
        $pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $this->creaSqlPasdocDest($gesnum), true);

        /*
         * Array con tutte le destinazioni
         */
        $arrTotDest = $this->getArrDest($pasdoc_tab);

        /*
         * Pulisco le destinazioni doppie
         */
        $arrTotDestClean = $this->checkDestinazioni($arrTotDest, $gesnum);

        /*
         * Mi trovo gli allegati per ogni destinazione
         */
        $arrAllegatiDest = $this->getAllegatiForDest($arrTotDestClean, $pasdoc_tab);

        /*
         * Aggiungo i passi con i relativi allegati e destinatari
         */
        if (!$this->addPassiDest($arrAllegatiDest, $gesnum)) {
            $this->setErrCode($this->getErrCode());
            $this->setErrMessage($this->getErrMessage());
            return false;
        }
        return true;
    }

    function creaSqlPasdocDest($gesnum) {
        $sql = "SELECT
                    PASDOC.*
                FROM 
                    PASDOC
                LEFT OUTER JOIN PROPAS ON PROPAS.PROPAK = PASDOC.PASKEY
                    WHERE
                PASKEY LIKE '$gesnum%' AND
                PASDOC.PASDEST<>''"; // AND PROPUB = 1 ";
        return $sql;
    }

    function getArrDest($pasdoc_tab) {
        /*
         * Mi creo un array con tutte le destinazioni
         */
        $arrTotDest = array();
        foreach ($pasdoc_tab as $pasdoc_rec) {
            $arrDest = unserialize($pasdoc_rec['PASDEST']);
            $arrTotDest = array_merge($arrTotDest, $arrDest);
        }
        return $arrTotDest;
    }

    function checkDestinazioni($arrTotDest, $gesnum) {
        $arrClean = array();
        foreach ($arrTotDest as $codice) {
            if (!array_key_exists($codice, $arrClean)) {

                $trovato = $this->checkInsertPasso($gesnum, $codice);
                if (!$trovato) {
                    $arrClean[$codice] = "";
                }
            }
        }
        return $arrClean;
    }

//    function checkDestinazioni($arrTotDest) {
//        $arrClean = array();
//        foreach ($arrTotDest as $codice) {
//            if (!array_key_exists($codice, $arrClean)) {
//                $arrClean[$codice] = "";
//            }
//        }
//        return $arrClean;
//    }

    function getAllegatiForDest($arrTotDestClean, $pasdoc_tab) {
        foreach ($pasdoc_tab as $pasdoc_rec) {
            $arrDest = unserialize($pasdoc_rec['PASDEST']);
            foreach ($arrDest as $dest) {
                foreach ($arrTotDestClean as $codice => $value) {
                    if ($codice == $dest) {
                        $arrTotDestClean[$codice][] = $pasdoc_rec;
                    }
                }
            }
        }
        return $arrTotDestClean;
    }

    function addPassiDest($arrAllegatiDest, $gesnum) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $propas_recAddDest = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$gesnum' AND PROCOMDEST = 1", false);
        $Proges_rec = $this->praLib->GetProges($gesnum);
        foreach ($arrAllegatiDest as $dest => $allegati) {
            $anamed_rec = $this->proLib->GetAnamed($dest);
            //$anaddo_rec = $this->praLib->GetAnaddo($dest);
            $Propas_rec = array();
            $Propas_rec['PRONUM'] = $gesnum;
            $Propas_rec['PROPRO'] = $Proges_rec['GESPRO'];
            $Propas_rec['PROSEQ'] = 99999;
            //$Propas_rec['PRODPA'] = "Invio comunicazione a " . $anaddo_rec['DDONOM'];
            $Propas_rec['PRODPA'] = "Invio comunicazione a " . $anamed_rec['MEDNOM'];
            $Propas_rec['PRORPA'] = $profilo['COD_ANANOM'];
            $Propas_rec['PROPAK'] = $this->praLib->PropakGenerator($Propas_rec['PRONUM']);
            $Propas_rec['PROINI'] = date("Ymd");
            $Propas_rec['PROFIN'] = "";
            $Propas_rec['PROALL'] = " ";
            $Propas_rec['PROUTEADD'] = $profilo['UTELOG'];
            $Propas_rec['PROUTEEDIT'] = $profilo['UTELOG'];
            $Propas_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
            $Propas_rec['PRODATEADD'] = date("d/m/Y") . " " . date("H:i:s");
            $Propas_rec['PROVISIBILITA'] = "Protetto";
            $Propas_rec['PROKPRE'] = $propas_recAddDest['PROPAK'];
            $insert_Info = "Oggetto: Inserisco passo destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'];
            if (!$this->insertRecord($this->PRAM_DB, 'PROPAS', $Propas_rec, $insert_Info)) {
                //Out::msgStop("Inserimento Passo Destinazione", "Inserimento passo destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'] . " fallito");
                $this->setErrCode("-1");
                $this->setErrMessage("Inserimento passo destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'] . " fallito");
                return false;
            }

            /*
             * Inserisco il destinatario su PRACOM
             */
            $Pracom_rec = array();
            $Pracom_rec['COMNUM'] = $Propas_rec['PRONUM'];
            $Pracom_rec['COMPAK'] = $Propas_rec['PROPAK'];
            $Pracom_rec['COMTIP'] = "P";
            $Pracom_rec['COMMLD'] = $anamed_rec['MEDEMA'];
            $Pracom_rec['COMCDE'] = "";
            $Pracom_rec['COMNOM'] = $anamed_rec['MEDNOM'];
            $Pracom_rec['COMFIS'] = $anamed_rec['MEDFIS'];
            $Pracom_rec['COMIND'] = $anamed_rec['MEDIND'];
            $Pracom_rec['COMCIT'] = $anamed_rec['MEDCIT'];
            $Pracom_rec['COMCAP'] = $anamed_rec['MEDCAP'];
            $Pracom_rec['COMPRO'] = $anamed_rec['MEDPRO'];
            $Pracom_rec['COMNOT'] = "Inserito Passo da Destinazione Front Office " . $anamed_rec['MEDNOM'];
            $insert_Info = "Oggetto: Inserisco destinatario su PRACOM " . $anamed_rec['MEDNOM'] . " per il passo " . $Propas_rec['PRODPA'];
            if (!$this->insertRecord($this->PRAM_DB, 'PRACOM', $Pracom_rec, $insert_Info)) {
                $this->setErrCode("-1");
                $this->setErrMessage("Inserimento destinatario su PRACOM " . $anamed_rec['MEDNOM'] . " per il passo " . $Propas_rec['PRODPA'] . " fallito");
                return false;
            }

            /*
             * Aggiungo il destinatario su PRAMITDEST
             */
            $praMitDest_rec = array();
            $praMitDest_rec['CODICE'] = $anamed_rec['MEDCOD'];
            $praMitDest_rec['TIPOCOM'] = 'D';
            $praMitDest_rec['KEYPASSO'] = $Propas_rec['PROPAK'];
            $praMitDest_rec['DATAINVIO'] = "";
            $praMitDest_rec['ORAINVIO'] = "";
            $praMitDest_rec['NOME'] = $anamed_rec['MEDNOM'];
            $praMitDest_rec['FISCALE'] = $anamed_rec['MEDFIS'];
            $praMitDest_rec['INDIRIZZO'] = $anamed_rec['MEDIND'];
            $praMitDest_rec['COMUNE'] = $anamed_rec['MEDCIT'];
            $praMitDest_rec['CAP'] = $anamed_rec['MEDCAP'];
            $praMitDest_rec['PROVINCIA'] = $anamed_rec['MEDPRO'];
            $praMitDest_rec['MAIL'] = $anamed_rec['MEDEMA'];
            $praMitDest_rec['TIPOINVIO'] = "PEC";
            $insert_Info = "Oggetto: Inserisco destinatario su PRAMITDEST " . $anamed_rec['MEDNOM'] . " per il passo " . $Propas_rec['PRODPA'];
            if (!$this->insertRecord($this->PRAM_DB, 'PRAMITDEST', $praMitDest_rec, $insert_Info)) {
                $this->setErrCode("-1");
                $this->setErrMessage("Inserimento destinatario su PRAMITDEST " . $anamed_rec['MEDNOM'] . " per il passo " . $Propas_rec['PRODPA'] . " fallito");
                return false;
            }

            /*
             * Aggiungo gli Allegati
             */
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($Propas_rec['PROPAK'], 0, 4), $Propas_rec['PROPAK'], "PASSO", true);
            foreach ($allegati as $allegato) {
                if (strlen($allegato['PASKEY']) == 10) {
                    $pramPathOld = $this->praLib->SetDirectoryPratiche(substr($allegato['PASKEY'], 0, 4), $allegato['PASKEY'], "PROGES", false);
                } else {
                    $pramPathOld = $this->praLib->SetDirectoryPratiche(substr($allegato['PASKEY'], 0, 4), $allegato['PASKEY'], "PASSO", false);
                }
                $pasdoc_rec = $allegato;
                $posPrimoPunto = strpos($pasdoc_rec['PASNAME'], ".");
                $ext = substr($pasdoc_rec['PASNAME'], $posPrimoPunto + 1);
                $randName = md5(rand() * time()) . "." . $ext;
                if (!@copy($pramPathOld . "/" . $allegato["PASFIL"], $pramPath . "/" . $randName)) {
                    //Out::msgStop("Copia Allegato", "Errore nel copiare l'allegato " . $allegato["PASFIL"] . " da<br>$pramPathOld a<br>$pramPath");
                    $this->setErrCode("-1");
                    $this->setErrMessage("Errore nel copiare l'allegato " . $allegato["PASFIL"] . " da<br>$pramPathOld a<br>$pramPath");
                    return false;
                }
                $pasdoc_rec['ROWID'] = 0;
                $pasdoc_rec['PASKEY'] = $Propas_rec['PROPAK'];
                $pasdoc_rec['PASFIL'] = $randName;
                $pasdoc_rec['PASLNK'] = "allegato://" . $randName;
                $pasdoc_rec['PASUTELOG'] = $profilo['UTELOG'];
                $pasdoc_rec['PASDATADOC'] = date("Ymd");
                $pasdoc_rec['PASORADOC'] = date("H:i:s");
                $pasdoc_rec["PASSHA2"] = hash_file('sha256', $pramPath . "/" . $pasdoc_rec["PASFIL"]);
                $pasdoc_rec["PASSHA2SOST"] = "";
                $pasdoc_rec["PASDEST"] = "";
                //$pasdoc_rec["PASCLAS"] = "";
                //$pasdoc_rec["PASNOTE"] = "";
                $insert_Info = "Oggetto: Inserisco allegato " . $pasdoc_rec['PASNAME'] . " per la destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                    //Out::msgStop("Inserimento Allegato Destinazione", "Inserimento allegato " . $pasdoc_rec['PASNAME'] . " per la destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'] . " fallito");
                    $this->setErrCode("-1");
                    $this->setErrMessage("Inserimento allegato " . $pasdoc_rec['PASNAME'] . " per la destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'] . " fallito");
                    return false;
                }
            }

            if ($propas_recAddDest['PROTBA']) {
                $docLib = new docLib();
                $docDocumenti_rec = $docLib->getDocumenti($propas_recAddDest['PROTBA']);
                $suffix = pathinfo($docDocumenti_rec['URI'], PATHINFO_EXTENSION);
                $randName = md5(rand() * time()) . ".$suffix";
                //
                $contenuto = $docDocumenti_rec['CONTENT'];
                $contenuto = "<!-- itaTestoBase:" . $docDocumenti_rec['CODICE'] . " -->" . $contenuto;
                $randName = md5(rand() * time()) . "." . $suffix;
                file_put_contents($pramPath . "/" . $randName, $contenuto);
                //
                $pasdocTB_rec = array();
                $pasdocTB_rec['PASKEY'] = $Propas_rec['PROPAK'];
                $pasdocTB_rec['PASFIL'] = $randName;
                $pasdocTB_rec['PASLNK'] = "allegato://" . $randName;
                $pasdocTB_rec['PASNOT'] = $docDocumenti_rec['OGGETTO'];
                $pasdocTB_rec['PASCLA'] = "TESTOBASE";
                $pasdocTB_rec['PASLOG'] = "<span>Clicca sull'ingranaggio per generare il PDF</span>";
                $pasdocTB_rec['PASNAME'] = $docDocumenti_rec['CODICE'] . ".$suffix";
                $pasdocTB_rec['PASUTELOG'] = $profilo['UTELOG'];
                $pasdocTB_rec['PASDATADOC'] = date("Ymd");
                $pasdocTB_rec['PASORADOC'] = date("H:i:s");
                $pasdocTB_rec["PASSHA2"] = hash_file('sha256', $pramPath . "/" . $pasdocTB_rec["PASFIL"]);
                $insert_Info = "Oggetto: Inserisco Testo Base " . $pasdocTB_rec['PASNAME'] . " per la destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'];
                if (!$this->insertRecord($this->PRAM_DB, 'PASDOC', $pasdocTB_rec, $insert_Info)) {
                    $this->setErrCode("-1");
                    $this->setErrMessage("Inserimento Testo Base " . $pasdoc_rec['PASNAME'] . " per la destinazione " . $Propas_rec['PRODPA'] . ": " . $Propas_rec['PROPAK'] . " fallito");
                    return false;
                }
            }

            $this->praLib->ordinaPassiPratica($gesnum);
        }
        return true;
    }

    function checkInsertPasso($gesnum, $codice) {
        $trovato = false;
        $propas_recAddDest = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$gesnum' AND PROCOMDEST = 1", false);
        $sql = "SELECT * FROM PROPAS WHERE PRONUM = '$gesnum' AND PROKPRE = '" . $propas_recAddDest['PROPAK'] . "'";
        //$propas_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        foreach ($propas_tab as $key => $propas_rec) {
            if ($propas_rec) {
                $sqlDest = "SELECT * FROM PRAMITDEST WHERE KEYPASSO = '" . $propas_rec['PROPAK'] . "' AND CODICE = '$codice'";
                $praMitDest_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlDest, false);
                if ($praMitDest_rec) {
                    $trovato = true;
                    break;
                }
            }
        }
        return $trovato;
    }

}

?>