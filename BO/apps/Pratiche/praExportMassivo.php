<?php

/**
 *
 * Ricerca Articoli nei Passi delle Pratiche
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFTP.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaFtpUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFileUtils.class.php';

function praExportMassivo() {
    $praExportMassivo = new praExportMassivo();
    $praExportMassivo->parseEvent();
    return;
}

class praExportMassivo extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $eqAudit;
    public $praLibFTP;
    public $nameForm = "praExportMassivo";
    public $divRic = "praExportMassivo_divRicerca";
    private $procedimentiUpload = array();

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->praLibFTP = new praLibFTP();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->eqAudit = new eqAudit();
            $this->procedimentiUpload = App::$utente->getKey($this->nameForm . '_procedimentiUpload');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_procedimentiUpload', $this->procedimentiUpload);
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                Out::activateUploader($this->nameForm . '_FileProcedimenti_upld_uploader');
                $this->OpenRicerca();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                break;

            case 'exportTableToExcel':
                break;

            case 'onClickTablePager':
                break;

            case 'printTableToHTML':
                break;

            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_NuovaSelezione':
                        Out::unBlock($this->nameForm . '_ProcedimentoDal_field');
                        Out::valore($this->nameForm . '_ProcedimentoDal', '');
                        Out::enableField($this->nameForm . '_ProcedimentoDal');
                        Out::unBlock($this->nameForm . '_ProcedimentoAl_field');
                        Out::valore($this->nameForm . '_ProcedimentoAl', '');
                        Out::enableField($this->nameForm . '_ProcedimentoAl');

                        Out::unBlock($this->nameForm . '_FileProcedimenti_field');
                        Out::show($this->nameForm . '_FileProcedimenti_upld_uploader');
                        Out::valore($this->nameForm . '_FileProcedimenti', '');
                        $this->procedimentiUpload = array();

                        Out::html($this->nameForm . '_divInfo', '');
                        break;

                    case $this->nameForm . '_Esporta':
                        if (!count($this->procedimentiUpload) && ($_POST[$this->nameForm . '_ProcedimentoDal'] == '' || $_POST[$this->nameForm . '_ProcedimentoAl'] == '')) {
                            Out::msgInfo('Attenzione', 'Indicare i codici procedimenti da esportare.');
                            break;
                        }

                        $cartellaDestinazione = $_POST[$this->nameForm . '_Cartella'];

                        if (trim($cartellaDestinazione) == '') {
                            Out::msgInfo('Attenzione', 'Cartella mancante.');
                            break;
                        }

                        if (is_dir($cartellaDestinazione)) {
//                            if (count(glob(rtrim($cartellaDestinazione, '/\\') . DIRECTORY_SEPARATOR . '*'))) {
//                                Out::msgInfo('Attenzione', 'Cartella non vuota.');
//                                break;
//                            }
                        } else {
                            if (!mkdir($cartellaDestinazione)) {
                                Out::msgInfo('Attenzione', 'Cartella non creata.');
                                break;
                            }
                        }

                        if (!is_writable($cartellaDestinazione)) {
                            Out::msgInfo('Attenzione', 'Cartella non accessibile.');
                            break;
                        }

                        if (count($this->procedimentiUpload)) {
                            $sql = "SELECT PRANUM FROM ANAPRA WHERE PRANUM IN ('" . implode("', '", $this->procedimentiUpload) . "')";
                        } else {
                            $sql = "SELECT PRANUM FROM ANAPRA WHERE PRANUM >= '" . $_POST[$this->nameForm . '_ProcedimentoDal'] . "' AND PRANUM <= '" . $_POST[$this->nameForm . '_ProcedimentoAl'] . "'";
                        }

                        /*
                         * Check eventi personalizzati
                         */
                        $trovato = false;
                        $anapraTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
                        foreach ($anapraTab as $key => $anapraRec) {
                            $Iteevt_tab = $this->praLib->GetIteevt($anapraRec['PRANUM'], 'codice', true);
                            $evtCustom = $this->praLib->checkEventiCustom($Iteevt_tab);
                            if ($evtCustom) {
                                $trovato = true;
                                break;
                            }
                        }
                        if ($trovato) {
                            Out::msgInfo("Esportazione Procedimento", "L'evento con codice $evtCustom del procedimento " . $anapraRec['PRANUM'] . " risulta essere personalizzato.<br>Verificare prima di esportare il procedimento.");
                            break;
                        }

                        $mainFolder = $this->Esporta();
                        if (!$mainFolder) {
                            break;
                        }

                        /*
                         * Creo lo zip 
                         */
                        $nomeFile = $this->creaZip($mainFolder);
                        if ($nomeFile == false) {
                            break;
                        }

                        /*
                         * Sposto il file nella AppsTempPath che a fine sessione si pulisce
                         */
                        $tempPath = itaLib::getAppsTempPath();
                        if (!rename($cartellaDestinazione . "/$nomeFile", $tempPath . "/$nomeFile")) {
                            Out::msgStop("Esportazioone Massiva", "Errore nello spostamento del file " . $cartellaDestinazione . "/$nomeFile in " . $tempPath . "/$nomeFile");
                            break;
                        }

                        /*
                         * Scarica o invia a FTP
                         */
                        if ($_POST[$this->nameForm . '_ScaricaZipLocale'] == 1) {
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $nomeFile, $tempPath . "/$nomeFile"
                                    )
                            );
                        } else {
                            if (!$this->sendFileToFtp($nomeFile)) {
                                break;
                            }
                            $msgFtp = "<br><br><span style=\"font-size:1.1em;\">Il file zip <b>$nomeFile</b> è stato inviato correttamente al server FTP.</span>";
                        }

                        /*
                         * Audit Operazione
                         */
                        $this->eqAudit->logEqEvent($this, array(
                            'Operazione' => eqAudit::OP_MISC_AUDIT,
                            'DB' => $this->PRAM_DB->getDB(),
                            'DSet' => '',
                            'Estremi' => "Creazione file zip import procedimenti $nomeFile"
                        ));

                        Out::html($this->nameForm . '_divInfo', "<span style=\"font-size:1.1em;\">Il file zip <b>$nomeFile</b> è stato creato correttamente nella directory <b>$cartellaDestinazione</b>.</span>$msgFtp");
                        Out::msgInfo("Successo", "Esportazione terminata.");
                        break;

                    case $this->nameForm . '_FileProcedimenti_upld';
                        if ('success' !== $_POST['response']) {
                            Out::msgStop("Errore", "Caricamento file {$_POST['file']} non riuscito.<br>{$_POST['response']}");
                            break;
                        }

                        $badValues = array();
                        $this->procedimentiUpload = array();
                        $sourceFile = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-' . $_POST['file'];
                        $fileHandle = fopen($sourceFile, 'r');
                        if ($fileHandle) {
                            while (($fileLine = fgets($fileHandle)) !== false) {
                                if (trim($fileLine) == '') {
                                    continue;
                                }

                                $numProced = str_pad(trim($fileLine), 6, '0', STR_PAD_LEFT);

                                if (preg_match('/[^0-9]/', $numProced) || strlen($numProced) > 6) {
                                    $badValues[] = $fileLine . ' (valore non valido)';
                                    continue;
                                }

                                if (!$this->praLib->GetAnapra($numProced)) {
                                    $badValues[] = $fileLine . ' (procedimento non trovato)';
                                    continue;
                                }

                                $this->procedimentiUpload[] = $numProced;
                            }

                            fclose($fileHandle);
                        }

                        @unlink($sourceFile);

                        if (count($badValues)) {
                            $this->procedimentiUpload = array();
                            Out::html($this->nameForm . '_divInfo', '');
                            Out::msgStop("Errore", "Il file contiene i seguenti errori:\n\n<b>" . implode("\n", $badValues) . '</b>');
                            break;
                        }

                        Out::html($this->nameForm . '_divInfo', "Saranno esportati i seguenti procedimenti: <b>" . implode('</b>, <b>', $this->procedimentiUpload) . "</b>.");

                        Out::valore($this->nameForm . '_FileProcedimenti', $_POST['file']);
                        Out::block($this->nameForm . '_ProcedimentoDal_field');
                        Out::disableField($this->nameForm . '_ProcedimentoDal');
                        Out::block($this->nameForm . '_ProcedimentoAl_field');
                        Out::disableField($this->nameForm . '_ProcedimentoAl');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ProcedimentoDal':
                    case $this->nameForm . '_ProcedimentoAl':
                        Out::valore($_POST['id'], str_pad($_POST[$_POST['id']], 6, '0', STR_PAD_LEFT));

                        if ($_POST[$this->nameForm . '_ProcedimentoDal'] && $_POST[$this->nameForm . '_ProcedimentoAl']) {
                            $dalProcedimento = str_pad($_POST[$this->nameForm . '_ProcedimentoDal'], 6, '0', STR_PAD_LEFT);
                            $alProcedimento = str_pad($_POST[$this->nameForm . '_ProcedimentoAl'], 6, '0', STR_PAD_LEFT);
                            Out::html($this->nameForm . '_divInfo', "Saranno esportati i procedimenti dal <b>$dalProcedimento</b> al <b>$alProcedimento</b>.");
                        }

                        Out::block($this->nameForm . '_FileProcedimenti_field');
                        Out::hide($this->nameForm . '_FileProcedimenti_upld_uploader');
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_procedimentiUpload');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function OpenRicerca() {
        Out::show($this->divRic, '');
        Out::clearFields($this->nameForm, $this->divRic);
        $this->Nascondi();
        Out::show($this->nameForm . '_Esporta');
        Out::setFocus('', $this->nameForm . '_ProcedimentoDal');

        $devLib = new devLib();
        $exportPath = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'PATHFILEZIP', false);

        if ($exportPath['CONFIG'] == "") {
            Out::msgInfo("Importazione Massiva", "Directory per esportazione non configurata nei parametri generali.");
        }
        Out::valore($this->nameForm . "_Cartella", $exportPath['CONFIG']);
        Out::attributo($this->nameForm . '_Cartella', "readonly", '0');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Esporta');
    }

    function Esporta() {
        if (count($this->procedimentiUpload)) {
            $sql = "SELECT PRANUM FROM ANAPRA WHERE PRANUM IN ('" . implode("', '", $this->procedimentiUpload) . "')";
        } else {
            $sql = "SELECT PRANUM FROM ANAPRA WHERE PRANUM >= '" . $_POST[$this->nameForm . '_ProcedimentoDal'] . "' AND PRANUM <= '" . $_POST[$this->nameForm . '_ProcedimentoAl'] . "'";
        }

        $anapraTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibUpgrade2.class.php';
        $praLibUpgrade = new praLibUpgrade();
        $saltati = array();

        //$cartellaDestinazione = $_POST[$this->nameForm . '_Cartella'];
        $mainFolder = "exp_proc_" . date("Ymd") . "_" . date("His") . "_" . itaLib::getRandBaseName();
        $cartellaDestinazione = $_POST[$this->nameForm . '_Cartella'] . "/$mainFolder";
        if (!mkdir($cartellaDestinazione, 0777)) {
            Out::msgStop("Errore", "Errore in creazione cartella 'destinazione'.");
            return false;
        }

        $cartellaDestinazioneResources = $cartellaDestinazione . '/resources';

        if (!mkdir($cartellaDestinazioneResources, 0777)) {
            Out::msgStop("Errore", "Errore in creazione cartella 'resources'.");
            return false;
        }

        foreach ($anapraTab as $anapra) {
            $xmlFile = $praLibUpgrade->creaXMLProcedimento($anapra['PRANUM'], false);

            if (!$xmlFile) {
                $saltati[] = $anapra['PRANUM'] . " [{$praLibUpgrade->getErrMessage()}]";
                continue;
            }

            if (file_exists($xmlFile)) {
                $new_name = pathinfo($xmlFile, PATHINFO_FILENAME) . "_" . date("Ymd") . "_" . date("His") . "." . pathinfo($xmlFile, PATHINFO_EXTENSION);
                if (!@rename($xmlFile, $cartellaDestinazione . "/" . $new_name)) {
                    $saltati[] = $anapra['PRANUM'] . ' [Errore durante la copia dell\'XML.]';
                }
                if (!chmod($cartellaDestinazione . "/" . $new_name, 0777)) {
                    $saltati[] = $anapra['PRANUM'] . ' [Errore durante il settaggio dei permessi.]';
                }
            }

            $resources = glob(dirname($xmlFile) . '/' . basename($xmlFile, '.xml') . '/*');
            foreach ($resources as $resourceFile) {
                $resourceDest = $cartellaDestinazioneResources . '/' . basename($resourceFile);
                if (!file_exists($resourceDest)) {
                    if (!@rename($resourceFile, $resourceDest)) {
                        $saltati[] = $anapra['PRANUM'] . ' [Errore durante la copia della risorsa \'' . basename($resourceFile) . '\'.]';
                    }
                    if (!chmod($resourceDest, 0777)) {
                        $saltati[] = $anapra['PRANUM'] . ' [Errore durante il settaggio dei permessi della risorsa \'' . basename($resourceFile) . '\'.]';
                    }
                } else {
                    @unlink($resourceFile);
                }
            }
        }

        if (count($saltati)) {
            Out::msgStop("Errore", "Si sono verificati i seguenti errori:\n\n" . implode("\n", $saltati));
            return false;
        }

        return $mainFolder;
    }

    public function creaZip($mainFolder) {
        $cartellaDestinazione = $_POST[$this->nameForm . '_Cartella'];
        //$nomeFile = "exp_proc_" . date("YmdHi") . ".zip";
        $nomeFile = $mainFolder . ".zip";
        $arcpf = $cartellaDestinazione . "/" . $nomeFile;
        //$retZip = itaZip::zipRecursive($cartellaDestinazione, $cartellaDestinazione, $arcpf, 'zip', false, false);
        $retZip = itaZip::zipRecursive($cartellaDestinazione, $cartellaDestinazione . "/" . $mainFolder, $arcpf, 'zip', false, false);
        if ($retZip != 0) {
            Out::msgStop("Errore", "Erroe creazione file zip.");
            return false;
        }

        /*
         * Cancello la cartella d'origine
         */
        itaFileUtils::removeDir($cartellaDestinazione . "/" . $mainFolder);

        return $nomeFile;
    }

    public function sendFileToFtp($nomeFile) {

        $cartellaDestinazione = $_POST[$this->nameForm . '_Cartella'];

        /*
         * Leggo i parametri per il collegamento FTP
         */
        $devLib = new devLib();
        $indirizzo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPIP', false);
        $user = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPUSER', false);
        $pwd = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPPWD', false);
        $tipo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPCONNECTION', false);
        $arrChunck = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPCHUNK', false);

        /*
         * Setto array parmatri per l'FTP
         */
        $ftpParm = array();
        $ftpParm["SERVER"] = $indirizzo['CONFIG'];
        $ftpParm["UTENTE"] = $user['CONFIG'];
        $ftpParm["PASSWORD"] = $pwd['CONFIG'];
        $ftpParm['TIPOCONNESSIONE'] = $tipo['CONFIG'];
        $ftpParm["RemoteFile"] = $nomeFile;
        $ftpParm["LocalFile"] = $cartellaDestinazione . "/" . $nomeFile;
        $chunk = $arrChunck['CONFIG'] * 1024 * 1024; //blocchi da 1MB

        /*
         * Effettuo l'upload del file
         */
        $valore = $this->praLibFTP->putFile($ftpParm, $chunk);
        if (strtolower($valore) == "error" || !$valore) {
            Out::msgStop("Attenzione", "Problemi nella trasmissione FTP. Procedimento arrestato Controllare!<br>" . $this->praLibFTP->getErrMessage());
            return false;
        }

        return true;
    }

}

?>