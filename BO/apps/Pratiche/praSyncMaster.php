<?php

/**
 *
 * PARAMETRI APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    08.01.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibFTP.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibUpgrade2.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFtpUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFileUtils.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');

function praSyncMaster() {
    $praSyncMaster = new praSyncMaster();
    $praSyncMaster->parseEvent();
    return;
}

class praSyncMaster extends itaModel {

    public $PRAM_DB;
    public $ITALWEB_DB;
    public $praLib;
    public $praLibFTP;
    public $nameForm = "praSyncMaster";
    public $divRis = "praSyncMaster_divRisultato";
    public $divGes = "praSyncMaster_divGestione";
    public $gridEnti = "praSyncMaster_gridEnti";
    public $gridXmlProcedimenti = "praSyncMaster_gridXmlProcedimenti";
    public $arrayEnti = array();
    public $enteMaster;
    public $gridDiff = array();
    public $gridPers = array();
    public $arrayCtr = array();
    public $arrayTesti = array();
    public $alberoImport = array();
    public $risultatoAlbero;
    public $errcode;
    public $errmessage;
    public $pathXML;
    public $xmlFiles;
    public $xmlSimpleList = array();
    public $soloVerdi = false;
    public $praLibUpgrade;
    public $eqAudit;

    function __construct() {
        parent::__construct();
        $this->arrayEnti = App::$utente->getKey($this->nameForm . '_arrayEnti');
        $this->enteMaster = App::$utente->getKey($this->nameForm . '_enteMaster');
        $this->gridDiff = App::$utente->getKey($this->nameForm . '_gridDiff');
        $this->gridPers = App::$utente->getKey($this->nameForm . '_gridPers');
        $this->arrayCtr = App::$utente->getKey($this->nameForm . '_arrayCtr');
        $this->arrayTesti = App::$utente->getKey($this->nameForm . '_arrayTesti');
        $this->alberoImport = App::$utente->getKey($this->nameForm . '_alberoImport');
        $this->pathXML = App::$utente->getKey($this->nameForm . '_pathXML');
        $this->xmlFiles = App::$utente->getKey($this->nameForm . '_xmlFiles');
        $this->xmlSimpleList = App::$utente->getKey($this->nameForm . '_xmlSimpleList');
        $this->soloVerdi = App::$utente->getKey($this->nameForm . '_soloVerdi');
        $this->risultatoAlbero = App::$utente->getKey($this->nameForm . '_risultatoAlbero');
        try {
            $this->praLib = new praLib();
            $this->praLibFTP = new praLibFTP();
            $this->eqAudit = new eqAudit();
            $this->praLibUpgrade = new praLibUpgrade();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB_DB = $this->praLib->getITALWEBDB();
        } catch (Exception $e) {
            Out::msgStop("Errore Costruct", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_arrayEnti', $this->arrayEnti);
            App::$utente->setKey($this->nameForm . '_enteMaster', $this->enteMaster);
            App::$utente->setKey($this->nameForm . '_gridDiff', $this->gridDiff);
            App::$utente->setKey($this->nameForm . '_gridPers', $this->gridPers);
            App::$utente->setKey($this->nameForm . '_arrayCtr', $this->arrayCtr);
            App::$utente->setKey($this->nameForm . '_arrayTesti', $this->arrayTesti);
            App::$utente->setKey($this->nameForm . '_alberoImport', $this->alberoImport);
            App::$utente->setKey($this->nameForm . '_pathXML', $this->pathXML);
            App::$utente->setKey($this->nameForm . '_xmlFiles', $this->xmlFiles);
            App::$utente->setKey($this->nameForm . '_xmlSimpleList', $this->xmlSimpleList);
            App::$utente->setKey($this->nameForm . '_soloVerdi', $this->soloVerdi);
            App::$utente->setKey($this->nameForm . '_risultatoAlbero', $this->risultatoAlbero);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->xmlSimpleList = array();
                $this->xmlFiles = array();
                $this->risultatoAlbero = null;
                $this->soloVerdi = false;
                $this->enteMaster = App::$utente->getKey('ditta');
                $Filent_rec = $this->praLib->GetFilent(1);
                if ($Filent_rec['FILDE4'] != "M") {
                    Out::msgStop("Sincronizza procedimenti", "L'ente non supporta questo tipo di operazione.");
                    $this->close();
                } else {
                    $this->openTable();
                }
                break;
            case 'onClick': // Evento Onclick
                if (strpos($_POST['id'], "VediPassi_") !== false) {
                    $passi = $this->arrayTesti["Itepas_tab_ctrIitewrd"][substr($_POST['id'], 24)];
                    if ($passi) {
                        $table = '<table id="tableItewrd">';
                        $table .= "<tr>";
                        $table .= '<th>Procedimento</th>';
                        $table .= '<th>Passo</th>';
                        $table .= "</tr>";
                        $table .= "<tbody>";
                        foreach ($passi as $Itepas_rec) {
                            $table .= "<tr>";
                            $table .= "<td>";
                            $Anapra_rec = $this->praLib->GetAnapra($Itepas_rec['ITECOD']);
                            $table .= $Anapra_rec['PRANUM'] . " - " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'];
                            $table .= "</td>";
                            $table .= "<td>";
                            $table .= $Itepas_rec['ITESEQ'] . " - " . $Itepas_rec['ITEDES'];
                            $table .= "</td>";
                            $table .= "</tr>";
                        }
                        $table .= '</tbody>';
                        $table .= '</table>';
                        Out::msgInfo("Elenco Passi", "<br>$table");
                        Out::codice('tableToGrid("#tableItewrd", {});');
                    }
                    break;
                }
                switch ($_POST['id']) {
                    case $this->nameForm . '_Sincronizza':
                        $enteSync = $_POST[$this->nameForm . "_EnteSync"];
                        Out::msgQuestion("ATTENZIONE!", "Hai scelto la sincronizzazione della struttura dei procedimenti dell'ente:" . $enteSync, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSync', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSync', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_SincronizzaTutti':
                        $enteSync = $_POST[$this->nameForm . "_EnteSync"];
                        Out::msgQuestion("ATTENZIONE!", "<span style=\"font-size=1.5em;\"><b>Hai scelto la sincronizzazione della struttura dei procedimenti su tutti gli enti</b></span>" . $enteSync, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSync', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSyncTutti', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_SpegniProcedimenti':
                        Out::msgInfo('Avviso', 'Funzione non attiva');
                        break;

                    case $this->nameForm . '_ConfermaSync':
                        $enteSync = $_POST[$this->nameForm . "_EnteSync"];
                        $retTipiPasso = $this->sincronizzaTipiPasso($this->enteMaster, $enteSync);
                        if ($retTipiPasso['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Tipi Passo", $retTipiPasso['message']);
                            break;
                        }

                        $retTipologie = $this->sincronizzaTipologiaProcedimento($this->enteMaster, $enteSync);
                        if ($retTipologie['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Tipologie", $retTipologie['message']);
                            break;
                        }

                        $retSettori = $this->sincronizzaSettori($this->enteMaster, $enteSync);
                        if ($retSettori['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Settori", $retSettori['message']);
                            break;
                        }

                        $retAttivita = $this->sincronizzaAttivita($this->enteMaster, $enteSync);
                        if ($retAttivita['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Attivita", $retAttivita['message']);
                            break;
                        }

                        $retNormativa = $this->sincronizzaNormative($this->enteMaster, $enteSync);
                        if ($retNormativa['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Normative", $retNormativa['message']);
                            break;
                        }

                        $retRequisiti = $this->sincronizzaRequisiti($this->enteMaster, $enteSync);
                        if ($retRequisiti['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Requisiti", $retRequisiti['message']);
                            break;
                        }

                        $retEventi = $this->sincronizzaEventi($this->enteMaster, $enteSync);
                        if ($retEventi['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Eventi", $retEventi['message']);
                            break;
                        }

                        $retProcedimenti = $this->sincronizzaProcedimenti($this->enteMaster, $enteSync);
                        if ($retProcedimenti['status'] == false) {
                            Out::msgStop("Errore Sincronizzazione Procedimenti", $retProcedimenti['message']);
                            break;
                        }

                        $this->dettaglio($enteSync);
                        break;
                    case $this->nameForm . '_ConfermaSyncTutti':
                        foreach ($this->arrayEnti as $ente) {
                            $err = false;
                            $enteSync = $ente['codice'];

                            $retTipiPasso = $this->sincronizzaTipiPasso($this->enteMaster, $enteSync);
                            if ($retTipiPasso['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Tipi Passo ente $enteSync fallita.<br>" . $retTipiPasso['message']);
                                $err = true;
                                break;
                            }

                            $retTipologie = $this->sincronizzaTipologiaProcedimento($this->enteMaster, $enteSync);
                            if ($retTipologie['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Tipologie ente $enteSync fallita.<br>" . $retTipologie['message']);
                                $err = true;
                                break;
                            }

                            $retSettori = $this->sincronizzaSettori($this->enteMaster, $enteSync);
                            if ($retSettori['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Settori ente $enteSync fallita.<br>" . $retSettori['message']);
                                $err = true;
                                break;
                            }

                            $retAttivita = $this->sincronizzaAttivita($this->enteMaster, $enteSync);
                            if ($retAttivita['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Attivita ente $enteSync fallita.<br>" . $retAttivita['message']);
                                $err = true;
                                break;
                            }

                            $retNormativa = $this->sincronizzaNormative($this->enteMaster, $enteSync);
                            if ($retNormativa['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Normative ente $enteSync fallita.<br>" . $retNormativa['message']);
                                $err = true;
                                break;
                            }

                            $retRequisiti = $this->sincronizzaRequisiti($this->enteMaster, $enteSync);
                            if ($retRequisiti['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Requisiti ente $enteSync fallita.<br>" . $retRequisiti['message']);
                                $err = true;
                                break;
                            }

                            $retEventi = $this->sincronizzaEventi($this->enteMaster, $enteSync);
                            if ($retEventi['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Eventi ente $enteSync fallita.<br>" . $retEventi['message']);
                                $err = true;
                                break;
                            }


                            $retProcedimenti = $this->sincronizzaProcedimenti($this->enteMaster, $enteSync);
                            if ($retProcedimenti['status'] == false) {
                                Out::msgStop("Errore!!", "Sincronizzazione Procedimenti ente $enteSync fallita.<br>" . $retProcedimenti['message']);
                                $err = true;
                                break;
                            }
                        }
                        if ($err == false) {
                            Out::msgInfo("Sincronizzazione Multipla", "Tutti gli enti sono stati sincronizzati correttamente");
                        }
                        $this->openTable();
                        break;
                    case $this->nameForm . '_paneSincEnti':
                        Out::show($this->nameForm . "_SincronizzaTutti");
                        Out::show($this->nameForm . "_SpegniProcedimenti");
                        Out::hide($this->nameForm . "_ImportaMassivoProc");
                        Out::hide($this->nameForm . "_ConfermaMassivo");
                        Out::hide($this->nameForm . "_VediLog");
                        Out::hide($this->nameForm . "_ImportaProc");
                        Out::hide($this->nameForm . "_ConfermaImport");
                        Out::hide($this->nameForm . "_daFtp");
                        break;
                    case $this->nameForm . '_paneImpProc':
                        Out::hide($this->nameForm . "_SincronizzaTutti");
                        Out::hide($this->nameForm . "_SpegniProcedimenti");
                        Out::hide($this->nameForm . "_Sincronizza");
                        Out::hide($this->nameForm . "_ImportaMassivoProc");
                        Out::hide($this->nameForm . "_ConfermaMassivo");
                        Out::hide($this->nameForm . "_VediLog");
                        Out::show($this->nameForm . "_ImportaProc");
                        Out::hide($this->nameForm . "_ConfermaImport");
                        Out::hide($this->nameForm . "_daFtp");
                        //Out::hide($this->nameForm . "_divMsg");
                        break;
                    case $this->nameForm . '_paneBulkImport':
                        Out::hide($this->nameForm . "_SincronizzaTutti");
                        Out::hide($this->nameForm . "_SpegniProcedimenti");
                        Out::hide($this->nameForm . "_Sincronizza");
                        Out::hide($this->nameForm . "_ImportaProc");
                        Out::show($this->nameForm . "_ImportaMassivoProc");
                        Out::hide($this->nameForm . "_ConfermaMassivo");
                        Out::show($this->nameForm . "_VediLog");
                        Out::show($this->nameForm . "_daFtp");
                        Out::hide($this->nameForm . "_ConfermaImport");
                        //Out::hide($this->nameForm . "_divMsg");
                        break;
                    case $this->nameForm . '_ImportaMassivoProc':
                        $this->openDialogFolder();
                        break;
                    case $this->nameForm . '_AnnullaLetturaMassiva':
                        $this->risultatoAlbero = null;
                        break;
                    case $this->nameForm . '_ConfermaLetturaMassiva':
                        $this->startImportMassivo($this->risultatoAlbero['PATH']);
                        /*
                         * Pulisco la tabella e le variabili di sessione che usco per l'importazioine
                         */
//                        $this->xmlSimpleList = array();
//                        $this->xmlFiles = array();
//                        $this->soloVerdi = false;
//                        TableView::clearGrid($this->gridXmlProcedimenti);
//                        //
//                        $risultato = $this->risultatoAlbero;
//                        $this->risultatoAlbero = null;
//                        //$risultato = $this->alberoImport[$_POST['retKey']];
//                        $this->pathXML = $risultato['PATH'];
//                        $xmlFiles = glob($risultato['PATH'] . '/*.xml');
////                        $this->creaElencoXml();
//                        if (!$this->processInit('creaElencoXml', count($xmlFiles), 5, 0)) {
//                            Out::msgStop("Controllo XML", "Inizializzazione processo fallita");
//                        }
//                        $this->processStart("Controllo XML", 80, 300, 'false');
                        break;
                    case $this->nameForm . '_ConfermaMassivo':
                        if (!$this->xmlSimpleList) {
                            Out::msgInfo("Importazione Massiva", "Impossibile Importare.<br>Selezionare prima una cartella valida.");
                            break;
                        }
                        $daAggiornare = 0;
                        $totali = count($this->xmlSimpleList);
                        foreach ($this->xmlSimpleList as $key => $ret) {
                            if ($ret['status'] >= 0) {
                                $daAggiornare++;
                            }
                        }
                        $daAggiornareSoloVerdi = 0;
                        foreach ($this->xmlSimpleList as $key1 => $ret) {
                            if ($ret['status'] == 0) {
                                $daAggiornareSoloVerdi++;
                            }
                        }
                        $htmlMsg = "<br><span style=\"font-size:1.2em;\"><b>Premendo 'Conferma', verranno importati solo i procedimenti con l'icona Stato verde e arancione, quindi $daAggiornare di $totali.</b></span><br>";
                        $htmlMsg .= "<br><span style=\"font-size:1.2em;\"><b>Premendo 'Conferma Solo Verdi', verranno importati solo i procedimenti con l'icona Stato verde, quindi $daAggiornareSoloVerdi di $totali.<b></span><br>";
                        //Out::msgQuestion("ATTENZIONE!", "L'operazione importerà $daAggiornare di $totali procedimenti e i suoi file , sei sicuro di voler continuare?", array(
                        Out::msgQuestion("ATTENZIONE!", $htmlMsg, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImportazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F7-Conferma Solo Verdi' => array('id' => $this->nameForm . '_ConfermaSoloVerdi', 'model' => $this->nameForm, 'shortCut' => "f7"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImpoMassiva', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ImportaProc':
                        $model = 'utiUploadDiag';
                        $_POST = Array();
                        $_POST['event'] = 'openform';
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = "returnUploadXMLproc";
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_ConfermaImport':
                        Out::msgQuestion("ATTENZIONE!", "L'operazione importerà il procedimento e i suoi file , sei sicuro di voler continuare?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImportazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImportazione', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_VediLog':
                        $cartellaLog = array();
                        $cartellaLog = $this->caricaCartelleImport($cartellaLog, $this->praLib->SetDirectorySyncLog());
                        include_once ITA_BASE_PATH . '/apps/Utility/utiFSDiag.php';
                        utiFSDiag::utiRicFolder($cartellaLog, $this->nameForm, "returnVediLog");
                        break;
                    case $this->nameForm . '_ConfermaSoloVerdi':
                        /*
                         * Mi creo un array xml Simple, quelli i soli xml con status uguale a 0
                         */
                        $arrXmlDaAggiornare = array();
                        foreach ($this->xmlSimpleList as $key => $ret) {
                            if ($ret['status'] == 0) {
                                $arrXmlDaAggiornare[] = $ret;
                            }
                        }
                        $this->soloVerdi = true;
                        /*
                         * Inizio Importazione
                         */
//                        $this->ImportazioneMassiva();
                        if (!$this->processInit('ImportazioneMassiva', count($arrXmlDaAggiornare), 5, 0)) {
                            Out::msgStop("Importazione XML Massiva", "Inizializzazione processo fallita");
                        }
                        $this->processStart("Importazione Massiva XML", 80, 300, 'false');
                        break;
                    case $this->nameForm . '_ConfermaImpoMassiva':
                        /*
                         * Mi creo un array xml Simple, quelli i soli xml con status maggiore o uguale a 0
                         */
                        $arrXmlDaAggiornare = array();
                        foreach ($this->xmlSimpleList as $key => $ret) {
                            if ($ret['status'] >= 0) {
                                $arrXmlDaAggiornare[] = $ret;
                            }
                        }
                        $this->soloVerdi = false;
//                        $this->ImportazioneMassiva();
                        if (!$this->processInit('ImportazioneMassiva', count($arrXmlDaAggiornare), 5, 0)) {
                            Out::msgStop("Importazione XML Massiva", "Inizializzazione processo fallita");
                        }
                        $this->processStart("Importazione Massiva XML", 80, 300, 'false');
                        break;
                    case $this->nameForm . '_ConfermaImportazione':
//                        $praLibUpgrade = new praLibUpgrade();
                        if (!$this->praLibUpgrade->acquisisciXMLProcedimento($this->arrayCtr, $this->arrayTesti)) {
                            Out::msgStop("Errore Importazione", $this->praLibUpgrade->getErrMessage());
                            break;
                        }
                        $this->openTable();
                        Out::tabSelect($this->nameForm . "_tabMaster", $this->nameForm . "_paneSincEnti");
                        Out::msgInfo("\zione Procedimento", "Importazione procedimento " . $this->arrayCtr["Anapra_rec"][0]['PRANUM'] . " terminata con successo");
                        Out::show($this->nameForm . "_ConfermaImport");
                        break;
                    case $this->nameForm . '_daFtp':

                        /*
                         * Leggo i parametri per il collegamento FTP
                         */
                        $devLib = new devLib();
                        $indirizzo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPIP', false);
                        $user = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPUSER', false);
                        $pwd = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPPWD', false);
                        $tipo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPCONNECTION', false);

                        /*
                         * Setto array parmatri per l'FTP
                         */
                        $ftpParm = $list = array();
                        $ftpParm["SERVER"] = $indirizzo['CONFIG'];
                        $ftpParm["UTENTE"] = $user['CONFIG'];
                        $ftpParm["PASSWORD"] = $pwd['CONFIG'];
                        $ftpParm['TIPOCONNESSIONE'] = $tipo['CONFIG'];
                        $ftpParm['DirArg'] = ".";

                        /*
                         * Effettuo l'upload del file
                         */
                        $valore = $this->praLibFTP->dir($ftpParm, $list);
                        if (strtolower($valore) == "error" || !$valore) {
                            Out::msgStop("Attenzione", "Problemi nella trasmissione FTP. Procedimento arrestato Controllare!<br>" . $this->praLibFTP->getErrMessage());
                            return false;
                        }

                        /*
                         * Creo una Dialog con i file Zip
                         */
                        $arrayFile = array();
                        foreach ($list as $key => $file) {
                            $ext = pathinfo($file, PATHINFO_EXTENSION);
                            if (strtolower($ext) != "zip") {
                                continue;
                            }

                            $ftpParm['RemoteFile'] = $file;
                            $size = $this->praLibFTP->getFileSize($ftpParm);
                            if (strtolower($size) == "error" || !$size) {
                                Out::msgStop("Attenzione", "Errore nello scaricamento del file!<br>" . $this->praLibFTP->getErrMessage());
                                break;
                            }
                            $arrayFile[$key]['IMGZIP'] = '<div class="ita-icon ita-icon-winzip-16x16" style="vertical-align:middle;display:inline-block;"></div><div style="display:inline-block;"> ' . pathinfo($file, PATHINFO_BASENAME) . '</div>';
                            $arrayFile[$key]['FILENAME'] = pathinfo($file, PATHINFO_BASENAME);
                            $arrayFile[$key]['FILEPATH'] = $file;
                            $arrayFile[$key]['SIZE'] = $this->praLib->formatFileSize($size);
                        }

                        praRic::praRicFileFromArray($arrayFile, $this->nameForm);
                        break;
                    case $this->nameForm . '_Torna':
                        $this->openTable();
                        break;
                }
                break;
            case 'dbClickRow':
            case 'editClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridEnti':
                        $codiceEnte = $this->arrayEnti[$_POST['rowid']]['codice'];
                        $this->dettaglio($codiceEnte);
                        break;
                    case $this->nameForm . '_gridXmlProcedimenti':
                        $model = 'praSyncDettaglio';
                        itaLib::openForm($model);
                        /* @var $modelObj praGestAssegnazione */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnPraSyncDettaglio');
                        $modelObj->setSimpleRet($this->xmlSimpleList[$_POST['rowid']]);
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                }
                break;
            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridDiffAnapra':
                        $this->gridDiff->exportXLS('', 'Anapra_differenze.xls');
                        break;
                    case $this->nameForm . '_gridPersAnapra':
                        $this->gridPers->exportXLS('', 'Anapra_personalizzati.xls');
                        break;
                }
                break;
            case "returnFolder":
                $risultato = $this->alberoImport[$_POST['retKey']];
                $this->risultatoAlbero = $risultato;
                Out::msgQuestion("ATTENZIONE!", "Hai scelto l'importazione massiva di:" . $risultato['PATH'], array(
                    'Annulla' => array('id' => $this->nameForm . '_AnnullaLetturaMassiva', 'model' => $this->nameForm),
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaLetturaMassiva', 'model' => $this->nameForm)
                        )
                );
                break;
            case "returnVediLog":
                $nomeFileLog = pathinfo($_POST['rowData']['PATH'], PATHINFO_BASENAME) . ".log";
                Out::openDocument(
                        utiDownload::getUrl(
                                $nomeFileLog, $_POST['rowData']['PATH'] . "/" . $nomeFileLog
                        )
                );

                break;
            case "returnUploadXMLproc":
                /*
                 * Controlli Preliminari su upload
                 */
                $this->arrayCtr = $this->arrayTesti = array();
                $XMLproc = $_POST['uploadedFile'];
                if (!file_exists($XMLproc)) {
                    Out::msgStop("Errore", "Procedura di importazione procedimento interrotta per mancanza del file.");
                    break;
                }
                if (strtolower(pathinfo($XMLproc, PATHINFO_EXTENSION)) != "xml") {
                    Out::msgStop("Errore", "File di importazione procedimento non conforme.");
                    break;
                }
                /*
                 * Salvataggio file caricato
                 */
                $xmlPath = pathinfo($XMLproc, PATHINFO_DIRNAME);
                $rand = md5(rand() * time());
                $newXmlPath = $xmlPath . "/" . $rand . "/" . pathinfo($XMLproc, PATHINFO_BASENAME);
                if (!@mkdir($xmlPath . "/" . $rand)) {
                    Out::msgStop("Errore", "Impossibile creare la subpath per il file " . pathinfo($XMLproc, PATHINFO_BASENAME));
                    break;
                }
                if (!@rename($XMLproc, $newXmlPath)) {
                    Out::msgStop("Errore", "Impossibile copiare il file xml nella subpath $newXmlPath");
                    break;
                }

                /*
                 *  Temporaneo per test
                 */
                $ret = $this->analizzaXml($newXmlPath);
                $this->arrayTesti = $ret['ret']['retValue']['arrayTesti'];
                $this->arrayCtr = $ret['ret']['retValue']['arrayCtr'];
                Out::html($this->nameForm . "_divInfo", $ret['retSimple']['htmlInfo']);
                Out::html($this->nameForm . "_divControlli", $ret['retSimple']['htmlControlli']);
                $strMsg = $ret['retSimple']['htmlErr'];
                if ($ret['retSimple']['message']) {
                    $strMsg .= "<br>" . $ret['retSimple']['message'];
                }
                Out::html($this->nameForm . "_divMsg", $strMsg);
                //Out::html($this->nameForm . "_divMsg", $ret['retSimple']['htmlErr']);
                //if ($ret['retSimple']['htmlErr']) {
                if ($strMsg) {
                    Out::hide($this->nameForm . "_ConfermaImport");
                } else {
                    Out::show($this->nameForm . "_ConfermaImport");
                }

                if ($ret['retSimple']['htmlMsgFileDiversi']) {
                    Out::show($this->nameForm . "_ConfermaImport");
                }
                break;
            case 'returnFileFromArray':
                $localPath = itaLib::getAppsTempPath();
                $zipFile = $localPath . "/" . $_POST['rowData']['FILENAME'];

                /*
                 * Audit Operazione
                 */
                $this->eqAudit->logEqEvent($this, array(
                    'Operazione' => eqAudit::OP_MISC_AUDIT,
                    'DB' => $this->PRAM_DB->getDB(),
                    'DSet' => '',
                    'Estremi' => "Inizio scarico file zip $zipFile"
                ));

                /*
                 * Leggo i parametri per il collegamento FTP
                 */
                $devLib = new devLib();
                $indirizzo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPIP', false);
                $user = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPUSER', false);
                $pwd = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPEXPPWD', false);
                $tipo = $devLib->getEnv_config("FTP_EXPORT", 'codice', 'FTPCONNECTION', false);

                /*
                 * Setto array parmatri per l'FTP
                 */
                $ftpParm = array();
                $ftpParm["SERVER"] = $indirizzo['CONFIG'];
                $ftpParm["UTENTE"] = $user['CONFIG'];
                $ftpParm["PASSWORD"] = $pwd['CONFIG'];
                $ftpParm['TIPOCONNESSIONE'] = $tipo['CONFIG'];
                $ftpParm["RemoteFile"] = $_POST['rowData']['FILEPATH'];
                $ftpParm["LocalFile"] = $zipFile;

                /*
                 * Effettuo il download del file
                 */
                $valore = $this->praLibFTP->getFile($ftpParm);
                if (strtolower($valore) == 'error' || $valore == 'file n/a') {
                    Out::msgStop("Attenzione", "Problemi nella ricezione FTP, File=" . $ftpParm["RemoteFile"] . ".<br>" . $this->praLibFTP->getErrMessage());
                    break;
                }
                if (strtolower($valore) == 'inactive') {
                    Out::msgStop("Attenzione", "La funzione Automatica di trasferimento FTP non è attivata provvedere manualmente.");
                    break;
                }
                if (strtolower($valore) != 'ok') {
                    Out::msgStop("Attenzione", "Problemi nella ricezione FTP, File=" . $ftpParm["RemoteFile"] . ". Procedimento arrestato Controllare!");
                    break;
                }

                /*
                 * Controllo esistenza file zip
                 */
                if (is_file($zipFile)) {
                    $extractFolder = $localPath . "/" . pathinfo($_POST['rowData']['FILEPATH'], PATHINFO_FILENAME);

                    /*
                     * Scompatto il file ZIP
                     */
                    $ret = itaZip::Unzip($zipFile, $extractFolder);
                    if ($ret != 1) {
                        Out::msgStop("ATTENZIONE!!!", "Estrazione file " . $_POST['rowData']['FILENAME'] . " fallita");
                        break;
                    }

                    /*
                     * Copio la cartella appena scompattata nella cartella degli Aggiornamenti dei procedimenti
                     */
                    $pathAggiornamenti = $this->praLib->SetDirectoryAggiornamenti(true);
                    $cartellaAggiornamenti = pathinfo($_POST['rowData']['FILEPATH'], PATHINFO_FILENAME);
                    if (!is_dir($pathAggiornamenti)) {
                        Out::msgStop("ATTENZIONE!!!", "Directory per aggiornamenti non trovata.");
                        break;
                    }

                    itaFileUtils::copyDir($extractFolder, $pathAggiornamenti . "/" . $cartellaAggiornamenti);
                    if (!is_dir($pathAggiornamenti . "/" . $cartellaAggiornamenti)) {
                        Out::msgStop("Attenzione", "Creazione cartella " . $pathAggiornamenti . "/" . $cartellaAggiornamenti . " fallita.");
                        break;
                    }

                    /*
                     * Audit Operazione
                     */
                    $this->eqAudit->logEqEvent($this, array(
                        'Operazione' => eqAudit::OP_MISC_AUDIT,
                        'DB' => $this->PRAM_DB->getDB(),
                        'DSet' => '',
                        'Estremi' => "File zip scaricato: $zipFile"
                    ));

                    /*
                     * Cancello la cartella d'origine
                     */
                    itaFileUtils::removeDir($extractFolder);

                    /*
                     * Cancello il file ZIP
                     */
                    unlink($zipFile);

                    $this->startImportMassivo($pathAggiornamenti . "/" . $cartellaAggiornamenti);
                }
                break;
        }
    }

    public function GetHtmlGridEventi($Iteevt_tab) {
        $html = "<table border=\"1\" cellpadding=\"10\" cellspacing=\"0\">";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th><b>Evento</b></th>";
        $html .= "<th><b>Sportello</b></th>";
        $html .= "<th><b>Settore</b></th>";
        $html .= "<th><b>Attività</b></th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        foreach ($Iteevt_tab as $Iteevt_tab_rec) {
            $Anaeventi_rec = $this->praLib->GetAnaeventi($Iteevt_tab_rec['IEVCOD']);
            $Anaset_rec_ctr = $this->praLib->GetAnaset($Iteevt_tab_rec['IEVSTT']);
            $Anaatt_rec_ctr = $this->praLib->GetAnaatt($Iteevt_tab_rec['IEVATT']);
            $Anatsp_rec_ctr = $this->praLib->GetAnatsp($Iteevt_tab_rec['IEVTSP']);
            //
            $html .= "<tr>";
            $html .= "<td>" . $Iteevt_tab_rec['IEVCOD'] . " - " . $Anaeventi_rec['EVTDESCR'] . "</td>";
            $html .= "<td>" . $Anatsp_rec_ctr['TSPCOD'] . " - " . $Anatsp_rec_ctr['TSPDES'] . "</td>";
            $html .= "<td>" . $Anaset_rec_ctr['SETCOD'] . " - " . $Anaset_rec_ctr['SETDES'] . "</td>";
            $html .= "<td>" . $Anaatt_rec_ctr['ATTCOD'] . " - " . $Anaatt_rec_ctr['ATTDES'] . "</td>";
        }
        $html .= "</tbody>";
        $html .= "</table><br>";
        return $html;
    }

    public function GetHtmlGridProcObbl($Itepraobb_tab) {
        $html = "<table border=\"1\" cellpadding=\"10\" cellspacing=\"0\">";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th><b>Evento</b></th>";
        $html .= "<th><b>Procedimento<br>Obbligatorio</b></th>";
        $html .= "<th><b>Evento Procedimento<br>Obbligatorio</b></th>";
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";
        foreach ($Itepraobb_tab as $Itepraobb_rec) {
            $Anaeventi_rec = $this->praLib->GetAnaeventi($Itepraobb_rec['OBBEVCOD']);
            $Anapra_sub_rec = $this->praLib->GetAnapra($Itepraobb_rec['OBBSUBPRA']);
            $Anaeventi_sub_rec = $this->praLib->GetAnaeventi($Itepraobb_rec['OBBSUBEVCOD']);
            //
            $html .= "<tr>";
            $html .= "<td>" . $Itepraobb_rec['OBBEVCOD'] . " - " . $Anaeventi_rec['EVTDESCR'] . "</td>";
            $html .= "<td>" . $Itepraobb_rec['OBBSUBPRA'] . " - " . $Anapra_sub_rec['PRADES__1'] . $Anapra_sub_rec['PRADES__2'] . "</td>";
            $html .= "<td>" . $Itepraobb_rec['OBBSUBEVCOD'] . " - " . $Anaeventi_sub_rec['EVTDESCR'] . "</td>";
        }
        $html .= "</tbody>";
        $html .= "</table><br>";
        return $html;
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_arrayEnti');
        App::$utente->removeKey($this->nameForm . '_enteMaster');
        App::$utente->removeKey($this->nameForm . '_gridDiff');
        App::$utente->removeKey($this->nameForm . '_gridPers');
        App::$utente->removeKey($this->nameForm . '_arrayCtr');
        App::$utente->removeKey($this->nameForm . '_arrayTesti');
        App::$utente->removeKey($this->nameForm . '_alberoImport');
        App::$utente->removeKey($this->nameForm . '_pathXML');
        App::$utente->removeKey($this->nameForm . '_xmlFiles');
        App::$utente->removeKey($this->nameForm . '_xmlSimpleList');
        App::$utente->removeKey($this->nameForm . '_soloVerdi');
        App::$utente->removeKey($this->nameForm . '_risultatoAlbero');

        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function openTable() {
        Out::valore($this->nameForm . "_EnteSync", '');
        $enti = App::getEnti();

        $this->arrayEnti = array();
        foreach ($enti as $key => $propsEnte) {
            $propsEnte['KEY'] = $key;
            $PRAM_DB = ItaDB::DBOpen('PRAM', $propsEnte['codice']);
            $filent_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM FILENT WHERE FILKEY = 1", false);
            if ($filent_rec['FILDE3'] == $this->enteMaster) {
                $this->arrayEnti[] = $propsEnte;
            }
        }

        if ($this->arrayEnti) {
            foreach ($this->arrayEnti as $key => $ente) {
                $ParametriEnte_rec = $this->praLib->GetParametriEnte($ente['codice']);
                if ($ParametriEnte_rec) {
                    $this->arrayEnti[$key]['DESENTE'] = $ParametriEnte_rec['DENOMINAZIONE'];
                } else {
                    $this->arrayEnti[$key]['DESENTE'] = $ente['KEY'];
                }
                $Synclog_rec = $this->praLib->GetSynclog($ente['codice'], "ANAPRA");
                if ($Synclog_rec) {
                    $htmlSync = "<p style = \"color:darkgreen;\">Ultima Sincronizzazione: " . date("d/m/Y", strtotime(substr($Synclog_rec['DATASYNC'], 0, 8))) . " " . substr($Synclog_rec['DATASYNC'], 8, 8);
                    $this->arrayEnti[$key]['STATOENTE'] = $htmlSync;
                } else {
                    $this->arrayEnti[$key]['STATOENTE'] = "----";
                }
            }
            $this->CaricaGriglia($this->gridEnti, $this->arrayEnti);
        }
        Out::hide($this->divGes);
        Out::hide($this->nameForm . "_Sincronizza");
        Out::hide($this->nameForm . "_ImportaMassivoProc");
        Out::hide($this->nameForm . "_ConfermaMassivo");
        Out::show($this->nameForm . "_SincronizzaTutti");
        Out::show($this->nameForm . "_SpegniProcedimenti");
        Out::hide($this->nameForm . "_Torna");
        Out::hide($this->nameForm . "_ImportaProc");
        Out::hide($this->nameForm . "_ConfermaImport");
        Out::hide($this->nameForm . "_VediLog");
        Out::hide($this->nameForm . "_daFtp");
        Out::show($this->divRis);
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1') {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows(count($_appoggio));
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

//function dettaglio($rowid) {
    function dettaglio($codiceEnte) {
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);

        Out::html($this->nameForm . "_divMsgPRACLT", '');
        Out::html($this->nameForm . "_divMsgANATIP", '');
        Out::html($this->nameForm . "_divMsgANASET", '');
        Out::html($this->nameForm . "_divMsgANAATT", '');
        Out::html($this->nameForm . "_divMsgANANOR", '');
        Out::html($this->nameForm . "_divMsgANAREQ", '');
        Out::html($this->nameForm . "_divMsgANAPRA", '');

        Out::show($this->divGes);
        Out::hide($this->divRis);
        Out::show($this->nameForm . "_Sincronizza");
        Out::hide($this->nameForm . "_SincronizzaTutti");
        Out::hide($this->nameForm . "_SpegniProcedimenti");
        Out::show($this->nameForm . "_Torna");

//$codiceEnte = $this->arrayEnti[$rowid]['codice'];
//$descrizioneEnte = $this->arrayEnti[$rowid]['DESENTE'];
        $parametri_ente_rec = $this->praLib->GetParametriEnte($codiceEnte);
        $descrizioneEnte = $parametri_ente_rec['DENOMINAZIONE'];

        Out::valore($this->nameForm . "_EnteSync", $codiceEnte);

        $htmlTestata = '<span style="font-size:1.5em;color:darkRed;">Sincronizzazione Archivi per l\'ente: ' . $codiceEnte . ' - ' . $descrizioneEnte . '</span>';
        Out::html($this->nameForm . "_divTestata", $htmlTestata);

//        Out::hide($this->nameForm . "_divPRACLT");
//        Out::hide($this->nameForm . "_divANATIP");
//        Out::hide($this->nameForm . "_divANASET");
//        Out::hide($this->nameForm . "_divANAATT");
//        Out::hide($this->nameForm . "_divANANOR");
//        Out::hide($this->nameForm . "_divANAREQ");
//        Out::show($this->nameForm . "_divANAPRA");

        $analisiTipiPasso = $this->analizzaTipiPasso($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiTipiPasso['codici_nuovi']) . "</p>";
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiTipiPasso['codici_obsoleti']) . "</p>";
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiTipiPasso['codici_modificati']) . "</p>";
        Out::html($this->nameForm . "_divMsgPRACLT", $html);
        Out::html($this->nameForm . "_divSyncPRACLT", $this->getHtmlLog($codiceEnte, $analisiTipiPasso['tabella']));


        $analisiTipologiaProcedimento = $this->analizzaTipologiaProcedimento($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiTipologiaProcedimento['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiTipologiaProcedimento['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiTipologiaProcedimento['codici_modificati']);
        $Synclog_rec_ANATIP = $praLib_master->GetSynclog($codiceEnte, $analisiTipologiaProcedimento['tabella']);
        Out::html($this->nameForm . "_divMsgANATIP", $html);
        Out::html($this->nameForm . "_divSyncANATIP", $this->getHtmlLog($codiceEnte, $analisiTipologiaProcedimento['tabella']));

        $analisiSettori = $this->analizzaSettori($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiSettori['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiSettori['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiSettori['codici_modificati']);
        $Synclog_rec_ANASET = $praLib_master->GetSynclog($codiceEnte, $analisiSettori['tabella']);
        Out::html($this->nameForm . "_divMsgANASET", $html);
        Out::html($this->nameForm . "_divSyncANASET", $this->getHtmlLog($codiceEnte, $analisiSettori['tabella']));

        $analisiAttivita = $this->analizzaAttivita($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiAttivita['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiAttivita['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiAttivita['codici_modificati']);
        $Synclog_rec_ANAATT = $praLib_master->GetSynclog($codiceEnte, $analisiAttivita['tabella']);
        Out::html($this->nameForm . "_divMsgANAATT", $html);
        Out::html($this->nameForm . "_divSyncANAATT", $this->getHtmlLog($codiceEnte, $analisiAttivita['tabella']));

        $analisiNormative = $this->analizzaNormative($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiNormative['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiNormative['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiNormative['codici_modificati']);
        $Synclog_rec_ANANOR = $praLib_master->GetSynclog($codiceEnte, $analisiNormative['tabella']);
        Out::html($this->nameForm . "_divMsgANANOR", $html);
        Out::html($this->nameForm . "_divSyncANANOR", $this->getHtmlLog($codiceEnte, $analisiNormative['tabella']));

        $analisiRequisiti = $this->analizzaRequisiti($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiRequisiti['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiRequisiti['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiRequisiti['codici_modificati']);
        $Synclog_rec_ANAREQ = $praLib_master->GetSynclog($codiceEnte, $analisiRequisiti['tabella']);
        Out::html($this->nameForm . "_divMsgANAREQ", $html);
        Out::html($this->nameForm . "_divSyncANAREQ", $this->getHtmlLog($codiceEnte, $analisiRequisiti['tabella']));

        $analisiEventi = $this->analizzaEventi($this->enteMaster, $codiceEnte);
        $html = "<p>Nuovi codici da inserire: " . count($analisiEventi['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiEventi['codici_obsoleti']);
        $html .= "<p>Codici personalizzati da verificare: " . count($analisiEventi['codici_modificati']);
        $Synclog_rec_ANAEVENTI = $praLib_master->GetSynclog($codiceEnte, $analisiEventi['tabella']);
        Out::html($this->nameForm . "_divMsgANAEVENTI", $html);
        Out::html($this->nameForm . "_divSyncANAEVENTI", $this->getHtmlLog($codiceEnte, $analisiEventi['tabella']));


        $analisiProcedimenti = $this->analizzaProcedimenti($this->enteMaster, $codiceEnte);
//        App::log($analisiProcedimenti);
        $html = "<p>Nuovi codici da inserire: " . count($analisiProcedimenti['codici_nuovi']);
        $html .= "<p>Vecchi codici da aggiornare: " . count($analisiProcedimenti['codici_obsoleti']);
        $html .= "<p>Codici con differenze non aggiornati: " . count($analisiProcedimenti['codici_differenze']);
        $html .= "<p>Codici personalizzati non aggiornati: " . count($analisiProcedimenti['codici_modificati']);

        $this->gridDiff = new TableView(
                $this->nameForm . "_gridDiffAnapra", array('arrayTable' => $analisiProcedimenti['codici_differenze'],
            'rowIndex' => 'idx')
        );
        TableView::enableEvents($this->nameForm . "_gridDiffAnapra");
        TableView::clearGrid($this->nameForm . "_gridDiffAnapra");
        $this->gridDiff->setPageRows(1000000);
        $this->gridDiff->setSortIndex('PRANUM');
        $this->gridDiff->setSortOrder('asc');
        $this->gridDiff->getDataPage('json');

        $this->gridPers = new TableView(
                $this->nameForm . "_gridPersAnapra", array('arrayTable' => $analisiProcedimenti['codici_modificati'],
            'rowIndex' => 'idx')
        );
        TableView::enableEvents($this->nameForm . "_gridPersAnapra");
        TableView::clearGrid($this->nameForm . "_gridPersAnapra");
        $this->gridPers->getDataPage('json');

        $Synclog_rec_ANAPRA = $praLib_master->GetSynclog($codiceEnte, $analisiProcedimenti['tabella']);
        Out::html($this->nameForm . "_divMsgANAPRA", $html);
        Out::html($this->nameForm . "_divSyncANAPRA", $this->getHtmlLog($codiceEnte, $analisiProcedimenti['tabella']));
        Out::html($this->nameForm . "_divResponsabile", $this->getHtmlRes($analisiProcedimenti['codici_nuovi']));
    }

    function analizzaTipiPasso($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'PRACLT',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );


        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici Nuovi
//
        $sql = "SELECT $masterDbName.PRACLT.CLTCOD FROM $masterDbName.PRACLT WHERE $masterDbName.PRACLT.CLTCOD NOT IN (SELECT CLTCOD FROM $slaveDbName.PRACLT)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.PRACLT.CLTCOD
                FROM
                $masterDbName.PRACLT, $slaveDbName.PRACLT
                WHERE
                $masterDbName.PRACLT.CLTCOD = $slaveDbName.PRACLT.CLTCOD AND
                    ($masterDbName.PRACLT.CLTUPDEDITOR = $slaveDbName.PRACLT.CLTUPDEDITOR OR $slaveDbName.PRACLT.CLTUPDEDITOR = '') AND
                    ($masterDbName.PRACLT.CLTUPDDATE>$slaveDbName.PRACLT.CLTUPDDATE OR 
                $masterDbName.PRACLT.CLTUPDDATE=$slaveDbName.PRACLT.CLTUPDDATE AND $masterDbName.PRACLT.CLTUPDTIME>$slaveDbName.PRACLT.CLTUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.PRACLT.CLTCOD
                FROM
                $masterDbName.PRACLT, $slaveDbName.PRACLT
                WHERE
                $masterDbName.PRACLT.CLTCOD = $slaveDbName.PRACLT.CLTCOD AND
                    ($masterDbName.PRACLT.CLTUPDEDITOR <> $slaveDbName.PRACLT.CLTUPDEDITOR AND $slaveDbName.PRACLT.CLTUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaTipiPasso($master, $slave) {
        $analisiTipiPasso = $this->analizzaTipiPasso($master, $slave);
        $analisiTipiPasso['codici_inseriti'] = 0;
        $analisiTipiPasso['codici_aggiornati'] = 0;
        $analisiTipiPasso['data_sync'] = '';
        $analisiTipiPasso['versione_sync'] = "";
        if ($analisiTipiPasso['status'] == false) {
            return $analisiTipiPasso;
        }
//App::log($analisiTipiPasso);
//
        // Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiTipiPasso['status'] = false;
            $analisiTipiPasso['message'] = $e->getMessage();
            return $analisiTipiPasso;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiTipiPasso['codici_nuovi'] as $key => $valore) {
            $Praclt_rec_master = $praLib_master->GetPraclt($valore['CLTCOD']);
            unset($Praclt_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'PRACLT', 'ROWID', $Praclt_rec_master);
                $analisiTipiPasso['codici_inseriti'] = $analisiTipiPasso['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiTipiPasso['status'] = false;
                $analisiTipiPasso['message'] = $e->getMessage();
                return $analisiTipiPasso;
            }
        }

        foreach ($analisiTipiPasso['codici_obsoleti'] as $key => $valore) {
            $Praclt_rec_master = $praLib_master->GetPraclt($valore['CLTCOD']);
            $Praclt_rec_slave = $praLib_slave->GetPraclt($valore['CLTCOD']);
            $Praclt_rec_master['ROWID'] = $Praclt_rec_slave['ROWID'];
            
            /*
             * Dati FO
             */
            $Praclt_rec_master['CLTOFF'] = $Praclt_rec_slave['CLTOFF'];
            $Praclt_rec_master['CLTOBL'] = $Praclt_rec_slave['CLTOBL'];
            $Praclt_rec_master['CLTDIZIONARIO'] = $Praclt_rec_slave['CLTDIZIONARIO'];
            $Praclt_rec_master['CLTOPEFO'] = $Praclt_rec_slave['CLTOPEFO'];
            $Praclt_rec_master['CLTMETA'] = $Praclt_rec_slave['CLTMETA'];
            
            /*
             * Dati BO
             */
            $Praclt_rec_master['CLTGESTPANEL'] = $Praclt_rec_slave['CLTGESTPANEL'];
            $Praclt_rec_master['CLTOPE'] = $Praclt_rec_slave['CLTOPE'];
            $Praclt_rec_master['CLTMETAPANEL'] = $Praclt_rec_slave['CLTMETAPANEL'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'PRACLT', 'ROWID', $Praclt_rec_master);
                $analisiTipiPasso['codici_aggiornati'] = $analisiTipiPasso['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiTipiPasso['status'] = false;
                $analisiTipiPasso['message'] = $e->getMessage();
                return $analisiTipiPasso;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiTipiPasso['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiTipiPasso['message'] = '';
        $this->registraSyncLog($analisiTipiPasso);
        return $analisiTipiPasso;
    }

    function analizzaTipologiaProcedimento($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANATIP',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );

        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }
        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici Nuovi
//
        $sql = "SELECT $masterDbName.ANATIP.TIPCOD FROM $masterDbName.ANATIP WHERE $masterDbName.ANATIP.TIPCOD NOT IN (SELECT TIPCOD FROM $slaveDbName.ANATIP)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANATIP.TIPCOD
                FROM
                $masterDbName.ANATIP, $slaveDbName.ANATIP
                WHERE
                $masterDbName.ANATIP.TIPCOD = $slaveDbName.ANATIP.TIPCOD AND
                    ($masterDbName.ANATIP.TIPUPDEDITOR = $slaveDbName.ANATIP.TIPUPDEDITOR OR $slaveDbName.ANATIP.TIPUPDEDITOR = '') AND
                    ($masterDbName.ANATIP.TIPUPDDATE>$slaveDbName.ANATIP.TIPUPDDATE OR 
                $masterDbName.ANATIP.TIPUPDDATE=$slaveDbName.ANATIP.TIPUPDDATE AND $masterDbName.ANATIP.TIPUPDTIME>$slaveDbName.ANATIP.TIPUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANATIP.TIPCOD
                FROM
                $masterDbName.ANATIP, $slaveDbName.ANATIP
                WHERE
                $masterDbName.ANATIP.TIPCOD = $slaveDbName.ANATIP.TIPCOD AND
                    ($masterDbName.ANATIP.TIPUPDEDITOR <> $slaveDbName.ANATIP.TIPUPDEDITOR AND $slaveDbName.ANATIP.TIPUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaTipologiaProcedimento($master, $slave) {
        $analisiTipologiaProcedimento = $this->analizzaTipologiaProcedimento($master, $slave);
        $analisiTipologiaProcedimento['codici_inseriti'] = 0;
        $analisiTipologiaProcedimento['codici_aggiornati'] = 0;
        $analisiTipologiaProcedimento['data_sync'] = '';
        $analisiTipologiaProcedimento['versione_sync'] = "";
        if ($analisiTipologiaProcedimento['status'] == false) {
            return $analisiTipologiaProcedimento;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiTipologiaProcedimento['status'] = false;
            $analisiTipologiaProcedimento['message'] = $e->getMessage();
            return $analisiTipologiaProcedimento;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiTipologiaProcedimento['codici_nuovi'] as $key => $valore) {
            $Anatip_rec_master = $praLib_master->GetAnatip($valore['TIPCOD']);
            unset($Anatip_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANATIP', 'ROWID', $Anatip_rec_master);
                $analisiTipologiaProcedimento['codici_inseriti'] = $analisiTipologiaProcedimento['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiTipologiaProcedimento['status'] = false;
                $analisiTipologiaProcedimento['message'] = $e->getMessage();
                return $analisiTipologiaProcedimento;
            }
        }

        foreach ($analisiTipologiaProcedimento['codici_obsoleti'] as $key => $valore) {
            $Anatip_rec_master = $praLib_master->GetAnatip($valore['TIPCOD']);
            $Anatip_rec_slave = $praLib_slave->GetAnatip($valore['TIPCOD']);
            $Anatip_rec_master['ROWID'] = $Anatip_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANATIP', 'ROWID', $Anatip_rec_master);
                $analisiTipologiaProcedimento['codici_aggiornati'] = $analisiTipologiaProcedimento['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiTipologiaProcedimento['status'] = false;
                $analisiTipologiaProcedimento['message'] = $e->getMessage();
                return $analisiTipologiaProcedimento;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiTipologiaProcedimento['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiTipologiaProcedimento['message'] = '';
        $this->registraSyncLog($analisiTipologiaProcedimento);
        return $analisiTipologiaProcedimento;
    }

    function analizzaSettori($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANASET',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );

        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANASET.SETCOD FROM $masterDbName.ANASET WHERE $masterDbName.ANASET.SETCOD NOT IN (SELECT SETCOD FROM $slaveDbName.ANASET)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANASET.SETCOD
                FROM
                $masterDbName.ANASET, $slaveDbName.ANASET
                WHERE
                $masterDbName.ANASET.SETCOD = $slaveDbName.ANASET.SETCOD AND
                    ($masterDbName.ANASET.SETUPDEDITOR = $slaveDbName.ANASET.SETUPDEDITOR OR $slaveDbName.ANASET.SETUPDEDITOR = '') AND
                    ($masterDbName.ANASET.SETUPDDATE>$slaveDbName.ANASET.SETUPDDATE OR
                $masterDbName.ANASET.SETUPDDATE=$slaveDbName.ANASET.SETUPDDATE AND $masterDbName.ANASET.SETUPDTIME>$slaveDbName.ANASET.SETUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANASET.SETCOD
                FROM
                $masterDbName.ANASET, $slaveDbName.ANASET
                WHERE
                $masterDbName.ANASET.SETCOD = $slaveDbName.ANASET.SETCOD AND
                    ($masterDbName.ANASET.SETUPDEDITOR <> $slaveDbName.ANASET.SETUPDEDITOR AND $slaveDbName.ANASET.SETUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaSettori($master, $slave) {
        $analisiSettori = $this->analizzaSettori($master, $slave);
        $analisiSettori['codici_inseriti'] = 0;
        $analisiSettori['codici_aggiornati'] = 0;
        $analisiSettori['data_sync'] = '';
        $analisiSettori['versione_sync'] = "";
        if ($analisiSettori['status'] == false) {
            return $analisiSettori;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiSettori['status'] = false;
            $analisiSettori['message'] = $e->getMessage();
            return $analisiSettori;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiSettori['codici_nuovi'] as $key => $valore) {
            $Anaset_rec_master = $praLib_master->GetAnaset($valore['SETCOD']);
            unset($Anaset_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANASET', 'ROWID', $Anaset_rec_master);
                $analisiSettori['codici_inseriti'] = $analisiSettori['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiSettori['status'] = false;
                $analisiSettori['message'] = $e->getMessage();
                return $analisiSettori;
            }
        }

        foreach ($analisiSettori['codici_obsoleti'] as $key => $valore) {
            $Anaset_rec_master = $praLib_master->GetAnaset($valore['SETCOD']);
            $Anaset_rec_slave = $praLib_slave->GetAnaset($valore['SETCOD']);
            $Anaset_rec_master['ROWID'] = $Anaset_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANASET', 'ROWID', $Anaset_rec_master);
                $analisiSettori['codici_aggiornati'] = $analisiSettori['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiSettori['status'] = false;
                $analisiSettori['message'] = $e->getMessage();
                return $analisiSettori;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiSettori['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiSettori['message'] = '';
        $this->registraSyncLog($analisiSettori);
        return $analisiSettori;
    }

    function analizzaAttivita($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANAATT',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANAATT.ATTCOD FROM $masterDbName.ANAATT WHERE $masterDbName.ANAATT.ATTCOD NOT IN (SELECT ATTCOD FROM $slaveDbName.ANAATT)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANAATT.ATTCOD
                FROM
                $masterDbName.ANAATT, $slaveDbName.ANAATT
                WHERE
                $masterDbName.ANAATT.ATTCOD = $slaveDbName.ANAATT.ATTCOD AND
                    ($masterDbName.ANAATT.ATTUPDEDITOR = $slaveDbName.ANAATT.ATTUPDEDITOR OR $slaveDbName.ANAATT.ATTUPDEDITOR = '') AND
                    ($masterDbName.ANAATT.ATTUPDDATE>$slaveDbName.ANAATT.ATTUPDDATE OR
                $masterDbName.ANAATT.ATTUPDDATE=$slaveDbName.ANAATT.ATTUPDDATE AND $masterDbName.ANAATT.ATTUPDTIME>$slaveDbName.ANAATT.ATTUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANAATT.ATTCOD
                FROM
                $masterDbName.ANAATT, $slaveDbName.ANAATT
                WHERE
                $masterDbName.ANAATT.ATTCOD = $slaveDbName.ANAATT.ATTCOD AND
                    ($masterDbName.ANAATT.ATTUPDEDITOR <> $slaveDbName.ANAATT.ATTUPDEDITOR AND $slaveDbName.ANAATT.ATTUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaAttivita($master, $slave) {
        $analisiAttivita = $this->analizzaAttivita($master, $slave);
        $analisiAttivita['codici_inseriti'] = 0;
        $analisiAttivita['codici_aggiornati'] = 0;
        $analisiAttivita['data_sync'] = '';
        $analisiAttivita['versione_sync'] = "";
        if ($analisiAttivita['status'] == false) {
            return $analisiAttivita;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiAttivita['status'] = false;
            $analisiAttivita['message'] = $e->getMessage();
            return $analisiAttivita;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiAttivita['codici_nuovi'] as $key => $valore) {
            $Anaatt_rec_master = $praLib_master->GetAnaatt($valore['ATTCOD']);
            unset($Anaatt_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANAATT', 'ROWID', $Anaatt_rec_master);
                $analisiAttivita['codici_inseriti'] = $analisiAttivita['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiAttivita['status'] = false;
                $analisiAttivita['message'] = $e->getMessage();
                return $analisiAttivita;
            }
        }

        foreach ($analisiAttivita['codici_obsoleti'] as $key => $valore) {
            $Anaatt_rec_master = $praLib_master->GetAnaatt($valore['ATTCOD']);
            $Anaatt_rec_slave = $praLib_slave->GetAnaatt($valore['ATTCOD']);
            $Anaatt_rec_master['ROWID'] = $Anaatt_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANAATT', 'ROWID', $Anaatt_rec_master);
                $analisiAttivita['codici_aggiornati'] = $analisiAttivita['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiAttivita['status'] = false;
                $analisiAttivita['message'] = $e->getMessage();
                return $analisiAttivita;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiAttivita['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiAttivita['message'] = '';
        $this->registraSyncLog($analisiAttivita);
        return $analisiAttivita;
    }

    function analizzaNormative($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANANOR',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANANOR.NORCOD FROM $masterDbName.ANANOR WHERE $masterDbName.ANANOR.NORCOD NOT IN (SELECT NORCOD FROM $slaveDbName.ANANOR)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANANOR.NORCOD
                FROM
                $masterDbName.ANANOR, $slaveDbName.ANANOR
                WHERE
                $masterDbName.ANANOR.NORCOD = $slaveDbName.ANANOR.NORCOD AND
                    ($masterDbName.ANANOR.NORUPDEDITOR = $slaveDbName.ANANOR.NORUPDEDITOR OR $slaveDbName.ANANOR.NORUPDEDITOR = '') AND
                    ($masterDbName.ANANOR.NORUPDDATE>$slaveDbName.ANANOR.NORUPDDATE OR
                $masterDbName.ANANOR.NORUPDDATE=$slaveDbName.ANANOR.NORUPDDATE AND $masterDbName.ANANOR.NORUPDTIME>$slaveDbName.ANANOR.NORUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANANOR.NORCOD
                FROM
                $masterDbName.ANANOR, $slaveDbName.ANANOR
                WHERE
                $masterDbName.ANANOR.NORCOD = $slaveDbName.ANANOR.NORCOD AND
                    ($masterDbName.ANANOR.NORUPDEDITOR <> $slaveDbName.ANANOR.NORUPDEDITOR AND $slaveDbName.ANANOR.NORUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaNormative($master, $slave) {
        $analisiNormative = $this->analizzaNormative($master, $slave);
        $analisiNormative['codici_inseriti'] = 0;
        $analisiNormative['codici_aggiornati'] = 0;
        $analisiNormative['data_sync'] = '';
        $analisiNormative['versione_sync'] = "";
        if ($analisiNormative['status'] == false) {
            return $analisiNormative;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiNormative['status'] = false;
            $analisiNormative['message'] = $e->getMessage();
            return $analisiNormative;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiNormative['codici_nuovi'] as $key => $valore) {
            $Ananor_rec_master = $praLib_master->GetAnanor($valore['NORCOD']);
            unset($Ananor_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANANOR', 'ROWID', $Ananor_rec_master);
                if ($Ananor_rec_master['NORFIL'] != '') {
                    $ret_copia = $this->copiaAllegatoNormativa($master, $slave, $Ananor_rec_master);
                    if ($ret_copia !== true) {
                        $analisiNormative['status'] = false;
                        $analisiNormative['message'] = $ret_copia;
                        return $analisiNormative;
                    }
                }
                $analisiNormative['codici_inseriti'] = $analisiNormative['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiNormative['status'] = false;
                $analisiNormative['message'] = $e->getMessage();
                return $analisiNormative;
            }
        }

        foreach ($analisiNormative['codici_obsoleti'] as $key => $valore) {
            $Ananor_rec_master = $praLib_master->GetAnanor($valore['NORCOD']);
            $Ananor_rec_slave = $praLib_slave->GetAnanor($valore['NORCOD']);
            $Ananor_rec_master['ROWID'] = $Ananor_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANANOR', 'ROWID', $Ananor_rec_master);
                if ($Ananor_rec_master['NORFIL'] != '') {
                    $ret_copia = $this->copiaAllegatoNormativa($master, $slave, $Ananor_rec_master);
                    if ($ret_copia !== true) {
                        $analisiNormative['status'] = false;
                        $analisiNormative['message'] = $ret_copia;
                        return $analisiNormative;
                    }
                }
                $analisiNormative['codici_aggiornati'] = $analisiNormative['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiNormative['status'] = false;
                $analisiNormative['message'] = $e->getMessage();
                return $analisiNormative;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiNormative['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiNormative['message'] = 'Sincronizzazione Effettuata';
        $this->registraSyncLog($analisiNormative);
        return $analisiNormative;
    }

    function copiaAllegatoNormativa($master, $slave, $Ananor_rec_master) {
        $dir_sorgente = Config::getPath('general.itaProc') . 'ente' . $master . '/normativa/';
        $file_sorgente = $dir_sorgente . $Ananor_rec_master['NORFIL'];
        if (is_file($file_sorgente)) {
            $dir_destinazione = Config::getPath('general.itaProc') . 'ente' . $slave . '/normativa/';
            if (!is_dir($dir_destinazione)) {
                if (!@mkdir($dir_destinazione)) {
                    return "Errore in creazione cartella allegati normativa.";
                }
            }
            $file_destinazione = $dir_destinazione . $Ananor_rec_master['NORFIL'];
            if (!@copy($file_sorgente, $file_destinazione)) {
                return "Errore in copia allegato normativa:";
            }
        } else {
            return "Errore in copia allegato requisiti file: $file_sorgente non esistente:";
        }
        return true;
    }

    function analizzaRequisiti($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANAREQ',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANAREQ.REQCOD FROM $masterDbName.ANAREQ WHERE $masterDbName.ANAREQ.REQCOD NOT IN (SELECT REQCOD FROM $slaveDbName.ANAREQ)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANAREQ.REQCOD
                FROM
                $masterDbName.ANAREQ, $slaveDbName.ANAREQ
                WHERE
                $masterDbName.ANAREQ.REQCOD = $slaveDbName.ANAREQ.REQCOD AND
                    ($masterDbName.ANAREQ.REQUPDEDITOR = $slaveDbName.ANAREQ.REQUPDEDITOR OR $slaveDbName.ANAREQ.REQUPDEDITOR = '') AND
                    ($masterDbName.ANAREQ.REQUPDDATE>$slaveDbName.ANAREQ.REQUPDDATE OR
                $masterDbName.ANAREQ.REQUPDDATE=$slaveDbName.ANAREQ.REQUPDDATE AND $masterDbName.ANAREQ.REQUPDTIME>$slaveDbName.ANAREQ.REQUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
//        app::log($sql);
//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANAREQ.REQCOD
                FROM
                $masterDbName.ANAREQ, $slaveDbName.ANAREQ
                WHERE
                $masterDbName.ANAREQ.REQCOD = $slaveDbName.ANAREQ.REQCOD AND
                    ($masterDbName.ANAREQ.REQUPDEDITOR <> $slaveDbName.ANAREQ.REQUPDEDITOR AND $slaveDbName.ANAREQ.REQUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaRequisiti($master, $slave) {
        $analisiRequisiti = $this->analizzaRequisiti($master, $slave);
        $analisiRequisiti['codici_inseriti'] = 0;
        $analisiRequisiti['codici_aggiornati'] = 0;
        $analisiRequisiti['data_sync'] = '';
        $analisiRequisiti['versione_sync'] = "";
        if ($analisiRequisiti['status'] == false) {
            $this->registraSyncLog($analisiRequisiti);
            return $analisiRequisiti;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiRequisiti['status'] = false;
            $analisiRequisiti['message'] = $e->getMessage();
            $this->registraSyncLog($analisiRequisiti);
            return $analisiRequisiti;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiRequisiti['codici_nuovi'] as $key => $valore) {
            $Anareq_rec_master = $praLib_master->GetAnareq($valore['REQCOD']);
            unset($Anareq_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANAREQ', 'ROWID', $Anareq_rec_master);
                if ($Anareq_rec_master['REQFIL'] != '') {
                    $ret_copia = $this->copiaAllegatoRequisiti($master, $slave, $Anareq_rec_master);
                    if ($ret_copia !== true) {
                        $analisiRequisiti['status'] = false;
                        $analisiRequisiti['message'] = $ret_copia;
                        $this->registraSyncLog($analisiRequisiti);
                        return $analisiRequisiti;
                    }
                }
                $analisiRequisiti['codici_inseriti'] = $analisiRequisiti['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiRequisiti['status'] = false;
                $analisiRequisiti['message'] = $e->getMessage();
                $this->registraSyncLog($analisiRequisiti);
                return $analisiRequisiti;
            }
        }

        foreach ($analisiRequisiti['codici_obsoleti'] as $key => $valore) {
            $Anareq_rec_master = $praLib_master->GetAnareq($valore['REQCOD']);
            $Anareq_rec_slave = $praLib_slave->GetAnareq($valore['REQCOD']);
            $Anareq_rec_master['ROWID'] = $Anareq_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANAREQ', 'ROWID', $Anareq_rec_master);
                if ($Anareq_rec_master['REQFIL'] != '') {
                    $ret_copia = $this->copiaAllegatoRequisiti($master, $slave, $Anareq_rec_master);
                    if ($ret_copia !== true) {
                        $analisiRequisiti['status'] = false;
                        $analisiRequisiti['message'] = $ret_copia;
                        $this->registraSyncLog($analisiRequisiti);
                        return $analisiRequisiti;
                    }
                }
                $analisiRequisiti['codici_aggiornati'] = $analisiRequisiti['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiRequisiti['status'] = false;
                $analisiRequisiti['message'] = $e->getMessage();
                $this->registraSyncLog($analisiRequisiti);
                return $analisiRequisiti;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiRequisiti['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiRequisiti['message'] = 'Sincronizzazione Effettuata';
        $this->registraSyncLog($analisiRequisiti);
        return $analisiRequisiti;
    }

    function copiaAllegatoRequisiti($master, $slave, $Anareq_rec_master) {
        $dir_sorgente = Config::getPath('general.itaProc') . 'ente' . $master . '/requisiti/';
        $file_sorgente = $dir_sorgente . $Anareq_rec_master['REQFIL'];
        if (is_file($file_sorgente)) {
            $dir_destinazione = Config::getPath('general.itaProc') . 'ente' . $slave . '/requisiti/';
//            App::log("dir dest " . $dir_destinazione);
            if (!is_dir($dir_destinazione)) {
                if (!@mkdir($dir_destinazione)) {
                    return "Errore in creazione cartella allegati requisiti.";
                }
            }
            $file_destinazione = $dir_destinazione . $Anareq_rec_master['REQFIL'];
            if (!@copy($file_sorgente, $file_destinazione)) {
                return "Errore in copia allegato requisiti:";
            }
        } else {
            return "Errore in copia allegato requisiti file: $file_sorgente non esistente:";
        }
        return true;
    }

    function sincronizzaEventi($master, $slave) {
        $analisiEventi = $this->analizzaEventi($master, $slave);
        $analisiEventi['codici_inseriti'] = 0;
        $analisiEventi['codici_aggiornati'] = 0;
        $analisiEventi['data_sync'] = '';
        $analisiEventi['versione_sync'] = "";
        if ($analisiEventi['status'] == false) {
            $this->registraSyncLog($analisiEventi);
            return $analisiEventi;
        }
//
// Sincronizzo Nuovi
//
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiEventi['status'] = false;
            $analisiEventi['message'] = $e->getMessage();
            $this->registraSyncLog($analisiEventi);
            return $analisiEventi;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiEventi['codici_nuovi'] as $key => $valore) {
            $Anaeventi_rec_master = $praLib_master->GetAnaeventi($valore['EVTCOD']);
            unset($Anaeventi_rec_master['ROWID']);
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANAEVENTI', 'ROWID', $Anaeventi_rec_master);
                $analisiEventi['codici_inseriti'] = $analisiEventi['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiEventi['status'] = false;
                $analisiEventi['message'] = $e->getMessage();
                $this->registraSyncLog($analisiEventi);
                return $analisiEventi;
            }
        }

        foreach ($analisiEventi['codici_obsoleti'] as $key => $valore) {
            $Anaeventi_rec_master = $praLib_master->GetAnaeventi($valore['EVTCOD']);
            $Anaeventi_rec_slave = $praLib_slave->GetAnaeventi($valore['EVTCOD']);
            $Anaeventi_rec_master['ROWID'] = $Anaeventi_rec_slave['ROWID'];
            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANAEVENTI', 'ROWID', $Anaeventi_rec_master);
                $analisiEventi['codici_aggiornati'] = $analisiEventi['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiEventi['status'] = false;
                $analisiEventi['message'] = $e->getMessage();
                $this->registraSyncLog($analisiEventi);
                return $analisiEventi;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiEventi['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiEventi['message'] = 'Sincronizzazione Effettuata';
        $this->registraSyncLog($analisiEventi);
        return $analisiEventi;
    }

    function analizzaEventi($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANAEVENTI',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANAEVENTI.EVTCOD FROM $masterDbName.ANAEVENTI WHERE $masterDbName.ANAEVENTI.EVTCOD NOT IN (SELECT EVTCOD FROM $slaveDbName.ANAEVENTI)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Da Obsoleti Aggiornare
//
        $sql = "SELECT
                $masterDbName.ANAEVENTI.EVTCOD
                FROM
                $masterDbName.ANAEVENTI, $slaveDbName.ANAEVENTI
                WHERE
                $masterDbName.ANAEVENTI.EVTCOD = $slaveDbName.ANAEVENTI.EVTCOD AND
                    ($masterDbName.ANAEVENTI.EVTUPDEDITOR = $slaveDbName.ANAEVENTI.EVTUPDEDITOR OR $slaveDbName.ANAEVENTI.EVTUPDEDITOR = '') AND
                    ($masterDbName.ANAEVENTI.EVTUPDDATE>$slaveDbName.ANAEVENTI.EVTUPDDATE OR
                $masterDbName.ANAEVENTI.EVTUPDDATE=$slaveDbName.ANAEVENTI.EVTUPDDATE AND $masterDbName.ANAEVENTI.EVTUPDTIME>$slaveDbName.ANAEVENTI.EVTUPDTIME)";
        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

//
// Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANAEVENTI.EVTCOD
                FROM
                $masterDbName.ANAEVENTI, $slaveDbName.ANAEVENTI
                WHERE
                $masterDbName.ANAEVENTI.EVTCOD = $slaveDbName.ANAEVENTI.EVTCOD AND
                    ($masterDbName.ANAEVENTI.EVTUPDEDITOR <> $slaveDbName.ANAEVENTI.EVTUPDEDITOR AND $slaveDbName.ANAEVENTI.EVTUPDEDITOR <> '')";
        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);

        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function analizzaProcedimenti($master, $slave) {
        $retAnalisi = array(
            'status' => true,
            'message' => '',
            'ente_master' => $master,
            'ente_slave' => $slave,
            'tabella' => 'ANAPRA',
            'codici_nuovi' => array(),
            'codici_obsoleti' => array(),
            'codici_modificati' => array()
        );
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();

//
// Codici nuovi
//
        $sql = "SELECT $masterDbName.ANAPRA.PRANUM FROM $masterDbName.ANAPRA WHERE $masterDbName.ANAPRA.PRANUM NOT IN (SELECT PRANUM FROM $slaveDbName.ANAPRA)";
        $retAnalisi['codici_nuovi'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);


//
// Codici Da Controllare per differenze
//
        $sql = "SELECT
                $masterDbName.ANAPRA.PRANUM
                FROM
                $masterDbName.ANAPRA, $slaveDbName.ANAPRA
                WHERE
                $masterDbName.ANAPRA.PRANUM = $slaveDbName.ANAPRA.PRANUM
                ORDER BY PRANUM";
        $daControllare_tab = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);

        $campiDifferenze = array(); //, "PRATIP", "PRASTT", "PRAATT", "PRASEG");
        $dizDifferenze = array(); //, "Tipologia procedimento", "Settore Commerciale", "Attivita Commerciale", "Tipo Segnalazione");

        $retAnalisi['codici_differenze'] = array();
        foreach ($daControllare_tab as $key => $daControllare_rec) {
            $fl_differenza = false;
            $Anapra_rec_master = $praLib_master->GetAnapra($daControllare_rec['PRANUM']);
            $Anapra_rec_slave = $praLib_slave->GetAnapra($daControllare_rec['PRANUM']);

            foreach ($campiDifferenze as $key => $campo) {
                if ($Anapra_rec_master[$campo] != $Anapra_rec_slave[$campo]) {
                    $fl_differenza = true;
                    $retAnalisi['codici_differenze'][] = array(
                        "PRANUM" => $Anapra_rec_slave['PRANUM'],
                        "DESCRIZIONEPROCEDIMENTO" => $Anapra_rec_slave['PRADES__1'] . $Anapra_rec_slave['PRADES__2'] . $Anapra_rec_slave['PRADES__3'] . $Anapra_rec_slave['PRADES__4'],
                        "CAMPO" => $campo,
                        "DESCRIZIONECAMPO" => $dizDifferenze[$key],
                        "MASTER" => $Anapra_rec_master[$campo],
                        "SLAVE" => $Anapra_rec_slave[$campo]
                    );
                }
            }
            if (!$fl_differenza) {
                if ($Anapra_rec_master['PRAUPDEDITOR'] == $Anapra_rec_slave['PRAUPDEDITOR'] || $Anapra_rec_slave['PRAUPDEDITOR'] == '') {
                    if ($Anapra_rec_master['PRAUPDDATE'] . $Anapra_rec_master['PRAUPDTIME'] > $Anapra_rec_slave['PRAUPDDATE'] . $Anapra_rec_slave['PRAUPDTIME']) {
                        $retAnalisi['codici_obsoleti'][] = array("PRANUM" => $Anapra_rec_slave['PRANUM']);
                    }
                }
            }
        }
//
// Codici Da Obsoleti Aggiornare
//
//        $sql = "SELECT
//                $masterDbName.ANAPRA.PRANUM
//                FROM
//                $masterDbName.ANAPRA, $slaveDbName.ANAPRA
//                WHERE
//                $masterDbName.ANAPRA.PRANUM = $slaveDbName.ANAPRA.PRANUM AND
//                    ($masterDbName.ANAPRA.PRAUPDEDITOR = $slaveDbName.ANAPRA.PRAUPDEDITOR OR $slaveDbName.ANAPRA.PRAUPDEDITOR = '') AND
//                    ($masterDbName.ANAPRA.PRAUPDDATE>$slaveDbName.ANAPRA.PRAUPDDATE OR
//                $masterDbName.ANAPRA.PRAUPDDATE=$slaveDbName.ANAPRA.PRAUPDDATE AND $masterDbName.ANAPRA.PRAUPDTIME>$slaveDbName.ANAPRA.PRAUPDTIME)";
//        $retAnalisi['codici_obsoleti'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
//
        // Codici Personalizzati
//
        $sql = "SELECT
                $masterDbName.ANAPRA.PRANUM," .
                $PRAM_MASTER->strConcat("$slaveDbName.ANAPRA.PRADES__1", "$slaveDbName.ANAPRA.PRADES__2", "$slaveDbName.ANAPRA.PRADES__3", "$slaveDbName.ANAPRA.PRADES__4") . " AS PRADES,
                $slaveDbName.ANAPRA.PRAUPDEDITOR,
                $slaveDbName.ANAPRA.PRAUPDDATE,
                $slaveDbName.ANAPRA.PRAUPDTIME
                FROM
                $masterDbName.ANAPRA, $slaveDbName.ANAPRA
                WHERE
                $masterDbName.ANAPRA.PRANUM = $slaveDbName.ANAPRA.PRANUM AND ($masterDbName.ANAPRA.PRAUPDEDITOR <> $slaveDbName.ANAPRA.PRAUPDEDITOR AND $slaveDbName.ANAPRA.PRAUPDEDITOR <> '')";

        $retAnalisi['codici_modificati'] = ItaDB::DBSQLSelect($PRAM_MASTER, $sql, true);
        $retAnalisi['status'] = true;
        return $retAnalisi;
    }

    function sincronizzaProcedimenti($master, $slave) {
        $analisiProcedimenti = $this->analizzaProcedimenti($master, $slave);
        $analisiProcedimenti['codici_inseriti'] = 0;
        $analisiProcedimenti['codici_aggiornati'] = 0;
        $analisiProcedimenti['data_sync'] = '';
        $analisiProcedimenti['versione_sync'] = "";
        if ($analisiProcedimenti['status'] == false) {
            return $analisiProcedimenti;
        }

        /*
         * Sincronizzo Nuovi
         */
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $slave);
        } catch (Exception $e) {
            $analisiProcedimenti['status'] = false;
            $analisiProcedimenti['message'] = $e->getMessage();
            return $analisiProcedimenti;
        }
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);
        foreach ($analisiProcedimenti['codici_nuovi'] as $key => $valore) {
            $Anapra_rec_master = $praLib_master->GetAnapra($valore['PRANUM']);
            unset($Anapra_rec_master['ROWID']);
            $Anapra_rec_master['PRASLAVE'] = 1;
            //
            //$Anatsp_rec = $praLib_slave->GetAnatsp($Anapra_rec_master['PRATSP']);
            //$Anapra_rec_master['PRARES'] = $Anatsp_rec['TSPRES'];
            $Iteevt_tab = $this->praLib->GetIteevt($Anapra_rec_master['PRANUM'], "codice", true);
            foreach ($Iteevt_tab as $Iteevt_rec) {
                $Anatsp_rec = $praLib_slave->GetAnatsp($Iteevt_rec['IEVTSP']);
                $Anapra_rec_master['PRARES'] = $Anatsp_rec['TSPRES'];
                break;
            }
            try {
                ItaDB::DBInsert($PRAM_SLAVE, 'ANAPRA', 'ROWID', $Anapra_rec_master);
                $ret_regEventi = $this->inserisciEventiProc($master, $slave, $valore['PRANUM']);
                if ($ret_regEventi !== true) {
                    $analisiProcedimenti['status'] = false;
                    $analisiProcedimenti['message'] = $ret_regEventi;
                    $this->registraSyncLog($analisiProcedimenti);
                    return $analisiProcedimenti;
                }
                $analisiProcedimenti['codici_inseriti'] = $analisiProcedimenti['codici_inseriti'] + 1;
            } catch (Exception $e) {
                $analisiProcedimenti['status'] = false;
                $analisiProcedimenti['message'] = $e->getMessage();
                return $analisiProcedimenti;
            }
        }


        /*
         * Sincronizzo già esistenti ma obsoleti perche modificati
         * 
         */
        foreach ($analisiProcedimenti['codici_obsoleti'] as $key => $valore) {
            $Anapra_rec_master = $praLib_master->GetAnapra($valore['PRANUM']);
            $Anapra_rec_slave = $praLib_slave->GetAnapra($valore['PRANUM']);

            //
            // Preservo:
            //  il responsabile,
            //  PRAOFFLINE ( flag procedimento spento),
            //  tipo procedimento e flag,
            //  template oggetto Richiesta on-line
            //  flag utilizo oggetto richiesta on line per PROGES in aqcuisizione
            //
            $Anapra_rec_master['ROWID'] = $Anapra_rec_slave['ROWID'];
            $Anapra_rec_master['PRARES'] = $Anapra_rec_slave['PRARES'];
            $Anapra_rec_master['PRAOFFLINE'] = $Anapra_rec_slave['PRAOFFLINE'];
            $Anapra_rec_master['PRATPR'] = $Anapra_rec_slave['PRATPR'];
            $Anapra_rec_master['PRASLAVE'] = $Anapra_rec_slave['PRASLAVE'];
            $Anapra_rec_master['PRACLA'] = $Anapra_rec_slave['PRACLA'];
            $Anapra_rec_master['PRAOGGTML'] = $Anapra_rec_slave['PRAOGGTML'];
            $Anapra_rec_master['PRAOGGTML_ACQ'] = $Anapra_rec_slave['PRAOGGTML_ACQ'];

            try {
                ItaDB::DBUpdate($PRAM_SLAVE, 'ANAPRA', 'ROWID', $Anapra_rec_master);
                $ret_regEventi = $this->inserisciEventiProc($master, $slave, $valore['PRANUM']);
                if ($ret_regEventi !== true) {
                    $analisiProcedimenti['status'] = false;
                    $analisiProcedimenti['message'] = $ret_regEventi;
                    $this->registraSyncLog($analisiProcedimenti);
                    return $analisiProcedimenti;
                }

                $analisiProcedimenti['codici_aggiornati'] = $analisiProcedimenti['codici_aggiornati'] + 1;
            } catch (Exception $e) {
                $analisiProcedimenti['status'] = false;
                $analisiProcedimenti['message'] = $e->getMessage();
                return $analisiProcedimenti;
            }
        }
        $marcatura_sync = $praLib_slave->GetMarcaturaModifiche();
        $analisiProcedimenti['data_sync'] = $marcatura_sync['DATE'] . $marcatura_sync['TIME'];
        $analisiProcedimenti['message'] = '';
        $this->registraSyncLog($analisiProcedimenti);
        return $analisiProcedimenti;
    }

    function inserisciEventiProc($master, $slave, $pranum) {
        $praLib_master = $this->praLib;
        $praLib_slave = new praLib($slave);

//
//Vedo se ci sono eventi nel proc slave e se si, li cancello
//
        $iteevt_tab_slave = $praLib_slave->GetIteevt($pranum, "codice", true);
        if ($iteevt_tab_slave) {
            foreach ($iteevt_tab_slave as $iteevt_rec_slave) {

                /*
                 * Verifica presenza eventi personalizzati. Se ci sono non li aggiorno
                 */
                if ($iteevt_rec_slave['PEREVT'] == 1) {
                    continue;
                }
                try {
                    $nrow = ItaDb::DBDelete($praLib_slave->getPRAMDB(), 'ITEEVT', 'ROWID', $iteevt_rec_slave['ROWID']);
                    if ($nrow == 0) {
                        return "Errore in cancellazione evento procedimento numero $pranum";
                    }
                } catch (Exception $e) {
                    return "Errore in cancellazione evento procedimento numero $pranum --> " . $e->getMessage();
                }
            }
        }

//
//Vedo se ci sono eventi nel proc master e se si, li inserisco nello slave
//
        $iteevt_tab_master = $praLib_master->GetIteevt($pranum, "codice", true);
        if ($iteevt_tab_master) {
            foreach ($iteevt_tab_master as $iteevt_rec_master) {
                $sql = "SELECT 
                            * 
                        FROM
                            ITEEVT
                        WHERE
                            ITEPRA = '" . $iteevt_rec_master['ITEPRA'] . "' AND
                            IEVCOD = '" . $iteevt_rec_master['IEVCOD'] . "' AND
                            IEVTSP = '" . $iteevt_rec_master['IEVTSP'] . "' AND
                            IEVTIP = '" . $iteevt_rec_master['IEVTIP'] . "' AND
                            IEVSTT = '" . $iteevt_rec_master['IEVSTT'] . "' AND
                            IEVATT = '" . $iteevt_rec_master['IEVATT'] . "'";
                $iteevt_recCtr = ItaDB::DBSQLSelect($praLib_slave->getPRAMDB(), $sql, false);
                if (!$iteevt_recCtr) {
                    $new_iteevt_rec = $iteevt_rec_master;
                    $new_iteevt_rec['ROWID'] = 0;
                    $new_iteevt_rec['PEREVT'] = 0;
                    try {
                        $nrow = ItaDB::DBInsert($praLib_slave->getPRAMDB(), "ITEEVT", 'ROWID', $new_iteevt_rec);
                        if ($nrow != 1) {
                            return "Errore inserimento evento procedimento $pranum";
                        }
                    } catch (Exception $e) {
                        return "Errore inserimento evento procedimento numero $pranum --> " . $e->getMessage();
                    }
                }
            }
        }
        return true;
    }

    function getMasterDB() {
        $PRAM_MASTER = $this->PRAM_DB;
        try {
            $PRAM_SLAVE = ItaDB::DBOpen('PRAM', $ditta);
        } catch (Exception $e) {
            $retAnalisi['status'] = false;
            $retAnalisi['message'] = $e->getMessage();
            return $retAnalisi;
        }

        $masterDbName = $PRAM_MASTER->getDB();
        $slaveDbName = $PRAM_SLAVE->getDB();
    }

    function registraSyncLog($retSync) {

        /*
         * Salvo i log su dbpara
         */
        $this->saveAuditing($retSync);

        $retSync['count_codici_nuovi'] = count($retSync['codici_nuovi']);
        $retSync['count_codici_modificati'] = count($retSync['codici_modificati']);
        $retSync['count_codici_obsoleti'] = count($retSync['codici_obsoleti']);

        unset($retSync['codici_nuovi']);
        unset($retSync['codici_obsoleti']);
        unset($retSync['codici_modificati']);

        $Synclog_rec = array();
        $Synclog_rec['ENTESYNC'] = $retSync['ente_slave'];
        $Synclog_rec['TABELLASYNC'] = $retSync['tabella'];
        $Synclog_rec['DATASYNC'] = $retSync['data_sync'];
        $Synclog_rec['ENTEMASTER'] = $retSync['ente_master'];
        $Synclog_rec['LOGSYNC'] = serialize($retSync);

        $insert_Info = "Log sync su ente:" . $retSync['ente_slave'];
        if (!$this->insertRecord($this->PRAM_DB, 'SYNCLOG', $Synclog_rec, $insert_Info)) {
            Out::msgStop("ATTENZIONE!", "Errore di Inserimento su SYNCLOG.");
            return false;
        }
        return true;
    }

    function getHtmlRes($arrayNuoviProc) {
        if (count($arrayNuoviProc) != 0) {
            $htmlRes = "<p style = \"padding:2px;color:red;font-size:1.5em;\">I nuovi procedimenti avranno come responsabile<br>il responsabile dello sportello on-line di competenza</p>";
        } else {
            $htmlRes = "";
        }
        return $htmlRes;
    }

    function getHtmlLog($codiceEnte, $tabella) {
        $Synclog_rec = $this->praLib->GetSynclog($codiceEnte, $tabella);
        if ($Synclog_rec) {
            $LogSync = unserialize($Synclog_rec['LOGSYNC']);
            $htmlSync = "<p style = \"color:darkgreen;\">Ultima Sincronizzazione: " . date("d/m/Y", strtotime(substr($Synclog_rec['DATASYNC'], 0, 8))) . " " . substr($Synclog_rec['DATASYNC'], 8, 8);
            $htmlSync .= "<br>" . $LogSync['message'];
            $htmlSync .= "<br>Codici inseriti: " . $LogSync['codici_inseriti'];
            $htmlSync .= "<br>Codici aggiornati: " . $LogSync['codici_aggiornati'];
        } else {
            $htmlSync = "";
        }
        return $htmlSync;
    }

    function analizzaXml($newXmlPath, $risorse = true) {
        $ret = $retSimple = array(
            'sourceXml' => $newXmlPath,
            'htmlInfo' => '',
            'htmlErr' => '',
            'htmlMsgFileDiversi' => '',
            'htmlControlli' => '',
            'status' => 0,
            'messasge' => '',
            'retValue' => false
        );
        /*
         * Analisi del procedimento ed estrazione dati da acqusire
         */
//        $praLibUpgrade = new praLibUpgrade();
        $arrayCtr = $this->praLibUpgrade->estraiXMLProcedimento($newXmlPath, $risorse);
        if (!$arrayCtr) {
            $ret['message'] = $this->praLibUpgrade->getErrMessage();
            $ret['status'] = -1;
            $retSimple['message'] = $this->praLibUpgrade->getErrMessage();
            $retSimple['status'] = -1;
            return array("ret" => $ret, "retSimple" => $retSimple);
            //return $ret;
        }
        $arrayCtr = $this->praLibUpgrade->CtrImportFields($arrayCtr);
        if (!$arrayCtr) {
            $ret['message'] = $this->praLibUpgrade->getErrMessage();
            $ret['status'] = -1;
            $retSimple['message'] = $this->praLibUpgrade->getErrMessage();
            $retSimple['status'] = -1;
            return array("ret" => $ret, "retSimple" => $retSimple);
            //return $ret;
        }

        /*
         * Caricamento dei dati su array di ritorno
         */
        $arrayTesti = $this->praLibUpgrade->controllaTestiAssociati($arrayCtr['testiAssociatiXml_tab']);
        $arrayProc_template = $this->praLibUpgrade->controllaPassiTemplate($arrayCtr["Itepas_tab"]);
        $arrayProc_Autore = $this->praLibUpgrade->controllaAutore($arrayCtr["Anapra_rec"][0]);
        $ret['retValue']['arrayCtr'] = $arrayCtr;
        $ret['retValue']['arrayTesti'] = $arrayTesti;
        $ret['retValue']['arrayProc_template'] = $arrayProc_template;
        $ret['retValue']['arrayProc_Autore'] = $arrayProc_Autore;

        /*
         * Preparazione info html 
         * 
         */
        $htmlInfo = "";
        $Anapra_rec_ctr = $this->praLib->GetAnapra($arrayCtr["Anapra_rec"][0]['PRANUM']);
        if ($Anapra_rec_ctr) {
            $Ananom_rec_ctr = $this->praLib->GetAnanom($Anapra_rec_ctr['PRARES']);
            $Itepas_tab_ctr = $this->praLib->GetItepas($Anapra_rec_ctr['PRANUM'], "codice", true);
            $Itedag_tab_ctr = $this->praLib->GetItedag($Anapra_rec_ctr['PRANUM'], "codice", true);
            $Anpdoc_tab_ctr = $this->praLib->GetAnpdoc($Anapra_rec_ctr['PRANUM']);
            $Iteevt_tab_ctr = $this->praLib->GetIteevt($Anapra_rec_ctr['PRANUM'], "codice", true);
            $Itepraobb_tab_ctr = $this->praLib->GetItePraObb($Anapra_rec_ctr['PRANUM'], "codice", true);
            $updDate = substr($Anapra_rec_ctr['PRAUPDDATE'], 6, 2) . "/" . substr($Anapra_rec_ctr['PRAUPDDATE'], 4, 2) . "/" . substr($Anapra_rec_ctr['PRAUPDDATE'], 0, 4);
            $htmlInfo .= "<br>";
            $htmlInfo .= "<span style=\"font-size:1.5;color:orange;\"><b>PROCEDIMENTO ESISTENTE</b></span><br>";
            $htmlInfo .= "Procedimento n. <b>" . $Anapra_rec_ctr['PRANUM'] . " - " . $Anapra_rec_ctr['PRADES__1'] . $Anapra_rec_ctr['PRADES__2'] . $Anapra_rec_ctr['PRADES__3'] . "</b><br>";
            $htmlInfo .= "Responsabile: <b>" . $Ananom_rec_ctr['NOMCOG'] . " " . $Ananom_rec_ctr['NOMNOM'] . "</b><br>";
            $htmlInfo .= "Autore: <b>" . $Anapra_rec_ctr['PRAINSEDITOR'] . "</b><br>";
            $htmlInfo .= "Versione del: <b>$updDate alle " . $Anapra_rec_ctr['PRAUPDTIME'] . "</b><br>";
            $htmlInfo .= "Numero Passi / Dati Aggiuntivi / Allegati: <b>" . count($Itepas_tab_ctr) . " / " . count($Itedag_tab_ctr) . " / " . count($Anpdoc_tab_ctr) . "</b><br>";
            if ($Iteevt_tab_ctr) {
                $htmlInfo .= $this->GetHtmlGridEventi($Iteevt_tab_ctr);
            }
            if ($Itepraobb_tab_ctr) {
                $htmlInfo .= $this->GetHtmlGridProcObbl($Itepraobb_tab_ctr);
            }
        }
        $Ananom_rec = $this->praLib->GetAnanom($arrayCtr["Anapra_rec"][0]['PRARES']);
        $updDate = substr($arrayCtr["Anapra_rec"][0]['PRAUPDDATE'], 6, 2) . "/" . substr($arrayCtr["Anapra_rec"][0]['PRAUPDDATE'], 4, 2) . "/" . substr($arrayCtr["Anapra_rec"][0]['PRAUPDDATE'], 0, 4);
        $htmlInfo .= "<br>";
        $htmlInfo .= "<span style=\"font-size:1.5;color:green;\"><b>NUOVO PROCEDIMENTO</b></span><br>";
        $htmlInfo .= "Procedimento n. <b>" . $arrayCtr["Anapra_rec"][0]['PRANUM'] . " - " . $arrayCtr["Anapra_rec"][0]['PRADES__1'] . $arrayCtr["Anapra_rec"][0]['PRADES__2'] . $arrayCtr["Anapra_rec"][0]['PRADES__3'] . "</b><br>";
        $htmlInfo .= "Responsabile: <b>" . $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM'] . "</b><br>";
        $editor = $arrayCtr["Anapra_rec"][0]['PRAINSEDITOR'];
        if ($editor == "")
            $editor = $arrayCtr["Anapra_rec"][0]['PRAUPDEDITOR'];
        $htmlInfo .= "Autore: <b>$editor</b><br>";
        $htmlInfo .= "Versione del: <b>$updDate alle " . $arrayCtr["Anapra_rec"][0]['PRAUPDTIME'] . "</b><br>";
        $htmlInfo .= "Numero Passi / Dati Aggiuntivi / Allegati: <b>" . count($arrayCtr["Itepas_tab"]) . " / " . count($arrayCtr["Itedag_tab"]) . " / " . count($arrayCtr["Anpdoc_tab"]) . "</b><br>";
        if ($arrayCtr["Iteevt_tab"]) {
            $htmlInfo .= $this->GetHtmlGridEventi($arrayCtr["Iteevt_tab"]);
        }
        if ($arrayCtr['Itepraobb_tab']) {
            $htmlInfo .= $this->GetHtmlGridProcObbl($arrayCtr['Itepraobb_tab']);
        }
        $ret['htmlInfo'] = $htmlInfo;
        $retSimple['htmlInfo'] = $htmlInfo;

        /*
         * Controllo su conformità archivi
         */
        $diffMsg = "";
        $diff = false;
        foreach ($arrayCtr['diffCampi'] as $tabella => $arrayCampi) {
            if ($arrayCampi) {
                $diff = true;
                $diffMsg .= "<br><div style=\"padding:5px;\">";
                $diffMsg .= "<div style=\"font-size:1.3em;text-decoration:underline;display:inline-block;\"><b>$tabella</b></div><br>";
                foreach ($arrayCampi as $campo => $value) {
                    $diffMsg .= "<div style=\"display:inline-block;\"><b> - $campo</b></div><br>";
                }
                $diffMsg .= "</div>";
            }
        }

        /*
         * Controllo incompatibilità tra archivi sorgente e destino
         */
        if ($diff) {
            $diffMsg = "<span style=\"color:red;font-size:1.5em;\"><b>I seguenti campi non sono stati trovati: (IMPOSSIBILE IMPORTARE).<br>ALLINEARE PRIMA LA STRUTTURA DEL DATABASE</b></span><br>" . $diffMsg;
            //$ret['htmlErr'] = $diffMsg;
            $ret['htmlControlli'] = $diffMsg;
            $ret['message'] = "Strutture Archvio difformi impossibile aggiornare";
            $ret['status'] = -2;
            $retSimple['htmlControlli'] = $diffMsg;
            $retSimple['message'] = "Strutture Archvio difformi impossibile aggiornare";
            $retSimple['status'] = -2;
            return array("ret" => $ret, "retSimple" => $retSimple);
            //return $ret;
        }

        /*
         * Controlli sui testi
         */
        $htmlControlli = "";
        $Itepas_tab_ctrIitewrd = array();
        if ($arrayTesti['testiNonAggiornabili']) {
            $oggi = date("Ymd");
            $htmlControlli .= "<br><span><b>I seguenti file sono stati trovati nella cartella dei testi associati, ma risultano diversi nel contenuto:</b></span><br>";
            foreach ($arrayTesti['testiNonAggiornabili'] as $key => $testo) {
                $itepas_tab = array();
                $button = "<button id=\"" . $this->nameForm . "_VediPassi_$key\" class=\"ita-button ita-element-animate ui-corner-all ui-state-default\"
                                      title=\"Vedi Passi in cui il modello è utilizzato\" name=\"" . $this->nameForm . "_VediPassi_$key\" type=\"button\">
                                      <div id=\"praSyncMaster_acc_icon_left\" class=\"ita-button-element
                                      ita-button-icon-left ui-icon ui-icon-search\" style=\"\"></div>
                                   </button>";
                $itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT
                                                                        ITECOD,
                                                                        ITESEQ,
                                                                        ITEDES,
                                                                        ITEWRD
                                                                   FROM
                                                                        ITEPAS ITEPAS
                                                                    LEFT OUTER JOIN 
                                                                        ANAPRA ANAPRA
                                                                    ON 
                                                                        ITEPAS.ITECOD=ANAPRA.PRANUM
                                                                    WHERE 
                                                                        ITECOD<>'" . $arrayCtr["Anapra_rec"][0]['PRANUM'] . "' AND 
                                                                        ITEWRD='" . $testo['NAME'] . "' AND 
                                                                        (ANAPRA.PRAAVA='' OR PRAAVA>$oggi)", true);
                if ($itepas_tab) {
                    $Itepas_tab_ctrIitewrd[$key] = $itepas_tab;
                    $htmlControlli .= " - " . $testo['NAME'] . "$button<br>";
                } else {
                    $htmlControlli .= " - " . $testo['NAME'] . "<br>";
                }
            }
        }
        if ($arrayTesti['testiAggiornabili']) {
            $htmlControlli .= "<br><span><b>I seguenti file sono stati trovati nella cartella dei testi associati e risultano non modificati:</b></span><br>";
            foreach ($arrayTesti['testiAggiornabili'] as $testo) {
                $htmlControlli .= " - " . $testo['NAME'] . "<br>";
            }
        }
        if ($arrayTesti['testiNuovi']) {
            $htmlControlli .= "<br><span><b>I seguenti file non sono stati trovati nella cartella dei testi associati:</b></span><br>";
            foreach ($arrayTesti['testiNuovi'] as $testo) {
                $htmlControlli .= " - " . $testo['NAME'] . "<br>";
            }
        }
        $htmlControlli .= "<br>";
        $ret['retValue']['arrayTesti']["Itepas_tab_ctrIitewrd"] = $Itepas_tab_ctrIitewrd;
        $retSimple['retValue']['arrayTesti']["Itepas_tab_ctrIitewrd"] = $Itepas_tab_ctrIitewrd;
        $ret['htmlControlli'] = $htmlControlli;
        $retSimple['htmlControlli'] = $htmlControlli;


        /*
         * Controllo su coerenza autori procedimento
         */
        $htmlMsg = "";
        $errAutore = false;
        if ($arrayProc_Autore) {
            $newEditor = $arrayProc_Autore['PROC_NUOVO']['PRAUPDEDITOR'];
            if ($newEditor == "") {
                $newEditor = $arrayProc_Autore['PROC_NUOVO']['PRAINSEDITOR'];
            }
            if (isset($arrayProc_Autore['PROC_ESISTENTE'])) {
                $oldEditor = $arrayProc_Autore['PROC_ESISTENTE']['PRAUPDEDITOR'];
                if ($oldEditor == "") {
                    $oldEditor = $arrayProc_Autore['PROC_ESISTENTE']['PRAINSEDITOR'];
                }
                if ($oldEditor != $newEditor) {
                    $errAutore = true;
                    $htmlMsg .= "<span style=\"color:red;\">L'editore del procedimento esistente <b>$oldEditor</b>, è diverso dall'editore del nuovo procedimento <b>$newEditor</b>. (IMPOSSIBILE IMPORTARE)</span><br>";
                }
            }
            if ($arrayProc_Autore['PARAM']['EDITOR'] != $newEditor) {
                $errAutore = true;
                $htmlMsg .= "<span style=\"color:red;\">L'editore configurato nei parametri <b>" . $arrayProc_Autore['PARAM']['EDITOR'] . "</b>, è diverso dall'editore del nuovo procedimento <b>$newEditor</b>. (IMPOSSIBILE IMPORTARE)</span><br>";
            }
        }

        /*
         * Controlli su testi modificati che afferiscono a procedimenti diversi
         * 
         */
        $warnTestiDiversi = false;
        if ($arrayTesti['testiNonAggiornabili']) {
            $warnTestiDiversi = true;
            $htmlMsgFileDiversi .= "<br>";
            $htmlMsgFileDiversi .= "<span style=\"color:red;\">Sono presenti dei file uguali nel nome, ma diversi nel contenuto.<br>Questo potrebbe danneggiare altri procedimenti che contengono gli stessi file.</span>";
            $htmlMsgFileDiversi .= "<br>";
        }

// Se ci sono i passi template mi creo un array con gli stessi
        $errTemplates = false;
        if ($arrayProc_template) {
            foreach ($arrayProc_template as $arrayTemplate) {
                $htmlMsg .= "<br><div>";
                $htmlMsg .= "<div style=\"display:inline-block;\">-</div>";
                $htmlMsg .= "<div style=\"display:inline-block;vertical-align:top;padding-left:5px;\">Il Passo " . $arrayTemplate['ITEPAS']['ITESEQ'] . ": " . $arrayTemplate['ITEPAS']['ITEDES'] . " fa riferimento ad un Template:<br>";
                if ($arrayTemplate['PROC_TEMPLATE']) {
                    $htmlMsg .= "<span style=\"color:green;\">Procedimento Template: " . $arrayTemplate['PROC_TEMPLATE']['PRANUM'] . " - " . $arrayTemplate['PROC_TEMPLATE']['PRADES__1'] . $arrayTemplate['PROC_TEMPLATE']['PRADES__2'] . $arrayTemplate['PROC_TEMPLATE']['PRADES__3'] . "</span><br>";
                } else {
                    $errTemplates = true;
                    $htmlMsg .= "<span style=\"color:red;\">Procedimento Template non trovato (IMPOSSIBILE IMPORTARE)</span><br>";
                }
                if ($arrayTemplate['PASSO_TEMPLATE']) {
                    $htmlMsg .= "<span style=\"color:green;\">Passo Template: " . $arrayTemplate['PASSO_TEMPLATE']['ITESEQ'] . " - " . $arrayTemplate['PASSO_TEMPLATE']['ITEDES'] . "</span><br>";
                } else {
                    $errTemplates = true;
                    $htmlMsg .= "<span style=\"color:red;\">Passo Template non trovato (IMPOSSIBILE IMPORTARE)</span><br>";
                }
                $htmlMsg .= "</div>";
                $htmlMsg .= "</div><br>";
            }
        }

        if ($warnTestiDiversi) {
            $ret['htmlMsgFileDiversi'] = $htmlMsgFileDiversi;
            $ret['htmlErr'] = $htmlMsgFileDiversi;
            $ret['message'] = "File/Testi Associati uguali nel nome ma con contenuto diverso.";
            $ret['status'] = 1;
            $retSimple['htmlMsgFileDiversi'] = $htmlMsgFileDiversi;
            $retSimple['htmlErr'] = $htmlMsgFileDiversi;
            $retSimple['message'] = "File/Testi Associati uguali nel nome ma con contenuto diverso.";
            $retSimple['status'] = 1;
        }

        if ($errAutore || $errTemplates) {
            $ret['htmlErr'] = $htmlMsg;
            $ret['message'] = "Autore difforme dall'originale o templates non presenti";
            $ret['status'] = -3;
            $retSimple['htmlErr'] = $htmlMsg;
            $retSimple['message'] = "Autore difforme dall'originale o templates non presenti";
            $retSimple['status'] = -3;
        }
        return array("ret" => $ret, "retSimple" => $retSimple);
        //return $ret;
    }

    private function caricaCartelleImport($albero = array(), $path = NULL, $level = 0, $indice = NULL) {
        if ($path == NULL) {
            $path = $this->praLib->SetDirectoryAggiornamenti(true);
        }


        $results = scandir($path);

        /*
         * Sorto l'array dalla cartella piu recente alla piu vecchia
         */
        arsort($results);
        if (count($results) > 0) {
            foreach ($results as $result) {
                if ($result == "." || $result == ".." || is_dir($path . DIRECTORY_SEPARATOR . $result) === false) {
                    continue;
                }
                $inc = count($albero) + 1;
                $albero[$inc]['level'] = $level;
                $albero[$inc]['parent'] = $indice;
                $albero[$inc]['isLeaf'] = 'false';
                $albero[$inc]['expanded'] = 'false';
                $albero[$inc]['loaded'] = 'true';
                $albero[$inc]['CARTELLA'] = '<div class="ita-icon ita-icon-open-folder-16x16" style="display:inline-block;"></div><div style="display:inline-block;"> ' . $result . '</div>';
                $subpath = $path . "/" . $result;
                $albero[$inc]['NUMFILE'] = count(glob($subpath . '/*.xml'));
                $albero[$inc]['PATH'] = $subpath;
                $save_count = count($albero);
                $albero = $this->caricaCartelleImport($albero, $subpath, $level + 1, $inc);
                if ($save_count == count($albero)) {
                    $albero[$inc]['isLeaf'] = 'true';
                }
            }
        }
        return $albero;
    }

    function creaElencoXml() {
        $xmlList = array();
        $this->xmlSimpleList = array();
        $this->xmlFiles = glob($this->pathXML . '/*.xml');
        $idx = $i = 0;

//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", 'INIZIO LETTURA FILE XML' . ' ' . date('h:i:s') . "\r\n", FILE_APPEND);

        foreach ($this->xmlFiles as $xmlFile) {
            $idx++;
            $this->processProgress($idx, "Controllo XML $idx di " . count($this->xmlFiles));
            //$xmlList[] = $this->analizzaXml($xmlFile, false);
            $xmlList[$i] = $this->analizzaXml($xmlFile, false);
            $this->xmlSimpleList[$i] = $xmlList[$i]['retSimple'];

//            $anapraRec = $xmlList[$i]['ret']['retValue']['arrayCtr']['Anapra_rec'];

            $i++;


//            file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", $anapraRec[0]['PRANUM'] . ' ' . date('h:i:s') . "\r\n", FILE_APPEND);
        }

//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", 'FINE LETTURA FILE XML' . ' ' . date('h:i:s') . "\r\n", FILE_APPEND);
//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", print_r($xmlList,true) . "\r\n", FILE_APPEND);
        $this->processEnd();
        $this->caricaTabellaMassiva($xmlList);
        if ($this->xmlFiles) {
            Out::show($this->nameForm . "_ConfermaMassivo");
        }

//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", 'CARICATA TABELLA' . ' ' . date('h:i:s') . "\r\n", FILE_APPEND);
    }

    function caricaTabellaMassiva($xmlList) {
        $tabella = array();
        foreach ($xmlList as $key => $xml) {
            $anapraRec = $xml['ret']['retValue']['arrayCtr']['Anapra_rec'];
            $tabella[$key]['IMP_PRANUM'] = $anapraRec[0]['PRANUM'];
            $tabella[$key]['IMP_DESCPROC'] = $anapraRec[0]['PRADES__1'];
            //$tabella[$key]['IMP_STATOIMP'] = $xml['ret']['status'];
            if ($xml['ret']['status'] == 0) {
                $tabella[$key]['IMP_STATOIMP'] = "<span class=\"ita-icon ita-icon-check-green-24x24\">" . $xml['ret']['status'] . "</span>";
            } elseif ($xml['ret']['status'] > 0) {
                $tabella[$key]['IMP_STATOIMP'] = "<span class=\"ita-icon ita-icon-check-orange-24x24\">" . $xml['ret']['status'] . "</span>";
            } elseif ($xml['ret']['status'] < 0) {
                $tabella[$key]['IMP_STATOIMP'] = "<span class=\"ita-icon ita-icon-delete-16x16\">" . $xml['ret']['status'] . "</span>";
            }
        }
//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", 'Tabella' . ' ' . date('h:i:s') . "\r\n", FILE_APPEND);
//        file_put_contents("/users/tmp/LOG_IMPORT_MASSIVO.txt", print_r($tabella, true) . "\r\n", FILE_APPEND);

        $this->CaricaGriglia($this->gridXmlProcedimenti, $tabella);
    }

    function CreaSyncStringLog($ret, $msg) {
        return "[" . $ret['retValue']['arrayCtr']['Anapra_rec'][0]['PRANUM'] . "][" . App::$utente->getKey('nomeUtente') . "][" . date("Ymd H:i:s") . "][" . $msg . "]\r\n";
    }

    function ImportazioneMassiva() {
        /*
         * Mi creo un array xml Simple in base alla scelta fatta precedentemente
         */
        if ($this->soloVerdi === true) {
            $arrXmlDaAggiornare = array();
            foreach ($this->xmlSimpleList as $ret) {
                if ($ret['status'] == 0) {
                    $arrXmlDaAggiornare[] = $ret;
                }
            }
        } else {
            $arrXmlDaAggiornare = array();
            foreach ($this->xmlSimpleList as $ret) {
                if ($ret['status'] >= 0) {
                    $arrXmlDaAggiornare[] = $ret;
                }
            }
        }

        /*
         * Inizio Importazione
         */
        $msgLog = "";
        $pathAggiornati = $this->praLib->SetDirectoryAggiornati(true);
        $patLog = $this->praLib->SetDirectorySyncLog(true);
//        $praLibUpgrade = new praLibUpgrade();
        $eseguiti = 0;
        foreach ($arrXmlDaAggiornare as $keyAgg => $retSimple) {
            $this->processProgress($keyAgg + 1, "Importo XML " . ($keyAgg + 1) . " di " . count($arrXmlDaAggiornare));
            $nomeFileXML = pathinfo($retSimple['sourceXml'], PATHINFO_BASENAME);
            //$fileNameXML = pathinfo($retSimple['sourceXml'], PATHINFO_FILENAME);
            $dirNameXML = pathinfo(pathinfo($retSimple['sourceXml'], PATHINFO_DIRNAME), PATHINFO_BASENAME);
            $newDirAggiornati = $pathAggiornati . "/" . $dirNameXML;
            $newDirLog = $patLog . "/" . $dirNameXML;
            $ret = $this->analizzaXml($retSimple['sourceXml']);
            if (!$this->praLibUpgrade->acquisisciXMLProcedimento($ret['ret']['retValue']['arrayCtr'], $ret['ret']['retValue']['arrayTesti'])) {
                $msgLog = $this->praLibUpgrade->getErrMessage();
                $operation = eqAudit::OP_GENERIC_ERROR;
            } else {
                $operation = eqAudit::OP_MISC_AUDIT;
                $eseguiti++;
                if (!is_dir($newDirAggiornati)) {
                    if (!mkdir($newDirAggiornati)) {
                        $this->processEnd();
                        Out::msgStop("Errore Importazione Massiva", "Impossibile creare la cartella $newDirAggiornati");
                        return false;
                    }
                }
                if (!rename($retSimple['sourceXml'], $newDirAggiornati . "/" . $nomeFileXML)) {
                    $this->processEnd();
                    Out::msgStop("Errore Importazione Massiva", "Impossibile spostare il file xml " . $retSimple['sourceXml'] . " a $pathAggiornati/$nomeFileXML");
                    return false;
                }
                $msgLog = "Procedimento Importato correttamente";
            }

            /*
             * Log DBPARA
             */
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => $operation,
                'DB' => $this->praLib->getPRAMDB()->getDB(),
                'DSet' => 'ANAPRA',
                'Estremi' => "Fine Importazione XML procedimento n. " . $ret['ret']['retValue']['arrayCtr']['Anapra_rec'][0]['PRANUM'] . ": $msgLog",
            ));

            /*
             * Creo la cartella log se non esiste e scrivo il log
             */
            if (!is_dir($newDirLog)) {
                if (!mkdir($newDirLog)) {
                    $this->processEnd();
                    Out::msgStop("Errore Importazione Massiva", "Impossibile creare la cartella $newDirLog");
                    return false;
                }
            }
            file_put_contents($newDirLog . "/" . $dirNameXML . ".log", $this->CreaSyncStringLog($ret['ret'], $msgLog), FILE_APPEND);
        }
        if ($eseguiti != count($arrXmlDaAggiornare)) {
            Out::msgStop("Importazione Massiva", "<b>Sono stati importati correttamente $eseguiti procedimenti<br>su un totale da aggiornare di " . count($arrXmlDaAggiornare) . ".<br>Premi il bottone 'Vedi Log' per capire quali problemi ci sono stati.</b>");
        } else {
            Out::msgInfo("Importazione Massiva", "<b>Sono stati importati correttamente $eseguiti procedimenti su un totale da aggiornare di " . count($arrXmlDaAggiornare) . "</b>");
        }
        $this->processEnd();
        return true;
    }

    function saveAuditing($retSync) {
        $praLib_slave = new praLib($retSync['ente_slave']);

        switch ($retSync['tabella']) {
            case "PRACLT":
                $campo = "CLTCOD";
                $tabella = "PRACLT";
                break;
            case "ANATIP":
                $campo = "TIPCOD";
                $tabella = "ANATIP";
                break;
            case "ANASET":
                $campo = "SETCOD";
                $tabella = "ANASET";
                break;
            case "ANAATT":
                $campo = "ATTCOD";
                $tabella = "ANAATT";
                break;
            case "ANANOR":
                $campo = "NORCOD";
                $tabella = "ANANOR";
                break;
            case "ANAREQ":
                $campo = "REQCOD";
                $tabella = "ANAREQ";
                break;
            case "ANAEVENTI":
                $campo = "EVTCOD";
                $tabella = "ANAEVENTI";
                break;
            case "ANAPRA":
                $campo = "PRANUM";
                $tabella = "ANAPRA";
                break;
        }

        foreach ($retSync['codici_nuovi'] as $nuovo) {
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_INS_RECORD,
                'DB' => $praLib_slave->getPRAMDB()->getDB(),
                'DSet' => $tabella,
                'Estremi' => "tabella $tabella inserito codice " . $nuovo[$campo]
            ));
        }

        foreach ($retSync['codici_obsoleti'] as $obsoleto) {
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $praLib_slave->getPRAMDB()->getDB(),
                'DSet' => $tabella,
                'Estremi' => "tabella $tabella modificato codice " . $obsoleto[$campo]
            ));
        }

        foreach ($retSync['codici_modificati'] as $modificato) {
            $this->eqAudit->logEqEvent($this, array(
                'Operazione' => eqAudit::OP_UPD_RECORD,
                'DB' => $praLib_slave->getPRAMDB()->getDB(),
                'DSet' => $tabella,
                'Estremi' => "tabella $tabella modificato codice " . $modificato[$campo]
            ));
        }
        return true;
    }

    function openDialogFolder() {
        Out::hide($this->nameForm . "_ConfermaMassivo");
        $this->alberoImport = $this->caricaCartelleImport();
        include_once ITA_BASE_PATH . '/apps/Utility/utiFSDiag.php';
        utiFSDiag::utiRicFolder($this->alberoImport, $this->nameForm);
    }

    function startImportMassivo($path) {

        /*
         * Controllo cartella già importata
         */
        $pathAggiornati = $this->praLib->SetDirectoryAggiornati(true);
        $pathExpProc = pathinfo($path, PATHINFO_BASENAME);
        if (is_dir($pathAggiornati . "/" . $pathExpProc)) {
            Out::msgStop("Importazione Massiva", "La cartella <b>$pathExpProc</b> è stata già importata perchè presente nella directory dei procedimenti aggiornati.");
            return false;
        }

        /*
         * Pulisco la tabella e le variabili di sessione che usco per l'importazioine
         */
        $this->xmlSimpleList = array();
        $this->xmlFiles = array();
        $this->soloVerdi = false;
        TableView::clearGrid($this->gridXmlProcedimenti);
        //
        //$risultato = $this->risultatoAlbero;
        $this->risultatoAlbero = null;
        $this->pathXML = $path;
        $xmlFiles = glob($path . '/*.xml');
        if (!$this->processInit('creaElencoXml', count($xmlFiles), 5, 0)) {
            Out::msgStop("Controllo XML", "Inizializzazione processo fallita");
        }
        $this->processStart("Controllo XML", 80, 300, 'false');
        return true;
    }

}
