<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');

function praRichiesteInfocamere() {
    $praRichiesteInfocamere = new praRichiesteInfocamere();
    $praRichiesteInfocamere->parseEvent();
    return;
}

class praRichiesteInfocamere extends itaModel {

    public $nameForm = "praRichiesteInfocamere";
    public $praLib;
    public $PRAM_DB;
    private $errMessage;
    private $errCode;
    private $allegatiComunica;
    private $proric_rec;
    private $rowidAppoggio;
    private $emlComunica;
    private $modelControllaFO;
    private $rowidChiamante;

    function __construct($ditta = '') {
        parent::__construct();
        try {
            $this->praLib = new praLib($this->ditta);
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            }

            /*
             * Setto il model per il controlla FO in base al parametro
             */
            $Filent_Rec = $this->praLib->GetFilent(42);
            if ($Filent_Rec['FILVAL'] == 1) {
                $this->modelControllaFO = "praCtrRichiesteFO";
            } else {
                $this->modelControllaFO = "praCtrRichieste";
            }
        } catch (Exception $e) {
            $this->setErrMessage($e->getMessage());
            return false;
        }
        $this->allegatiComunica = App::$utente->getKey($this->nameForm . '_allegatiComunica');
        $this->proric_rec = App::$utente->getKey($this->nameForm . '_proric_rec');
        $this->emlComunica = App::$utente->getKey($this->nameForm . '_emlComunica');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->rowidChiamante = App::$utente->getKey($this->nameForm . '_rowidChiamante');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_allegatiComunica', $this->allegatiComunica);
            App::$utente->setKey($this->nameForm . '_proric_rec', $this->proric_rec);
            App::$utente->setKey($this->nameForm . '_emlComunica', $this->emlComunica);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_rowidChiamante', $this->rowidChiamante);
        }
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function getAllegatiComunica() {
        return $this->allegatiComunica;
    }

    function getProric_rec() {
        return $this->proric_rec;
    }

    function getRowidAppoggio() {
        return $this->rowidAppoggio;
    }

    function setAllegatiComunica($allegatiComunica) {
        $this->allegatiComunica = $allegatiComunica;
    }

    function setProric_rec($proric_rec) {
        $this->proric_rec = $proric_rec;
    }

    function setRowidAppoggio($rowidAppoggio) {
        $this->rowidAppoggio = $rowidAppoggio;
    }

    function getEmlComunica() {
        return $this->emlComunica;
    }

    function setEmlComunica($emlComunica) {
        $this->emlComunica = $emlComunica;
    }

    function getDitta() {
        return $this->ditta;
    }

    function setDitta($ditta) {
        $this->ditta = $ditta;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                //$this->proric_rec = $_POST['proric_rec'];
                $this->rowidChiamante = $_POST['rowidChiamante'];
                $model = 'utiUploadDiag';
                $_POST = Array();
                if ($this->proric_rec) {
                    $_POST['messagge'] = "<span style=\"color:red;font-size:1.3em;\">Seleziona l'eml proveniente dalla camera di commercio<br>per la richiesta n. <b>" . $this->proric_rec['RICNUM'] . "</b><br><br></span>";
                } else {
                    $_POST['messagge'] = "<span style=\"color:red;font-size:1.3em;\">Seleziona il file zip o l'eml provenienti<br>dalla camera di commercio<br><br></span>";
                }

                $_POST[$model . '_returnModel'] = $this->nameForm;
                $_POST[$model . '_returnEvent'] = "returnUploadZip";
                $_POST['returnOnClose'] = true;
                itaLib::openForm($model);
                $objModel = itaModel::getInstance($model);
                $objModel->setEvent("openform");
                $objModel->parseEvent();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaImpRichiesta':
                        $Filent_Rec = $this->praLib->GetFilent(42);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $prafolist_rec = $this->praLib->GetPrafolist($this->rowidChiamante);
                            $richiesta = $prafolist_rec['FOPRAKEY'];
                        } else {
                            $Proric_rec = $this->praLib->GetProric($this->rowidChiamante, 'rowid');
                            $richiesta = $Proric_rec['RICNUM'];
                        }
                        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA = '$richiesta'", false);
                        if ($Proges_rec) {
                            Out::msgQuestion("Importazione Pratica!", "Il N. di richiesta on-line $richiesta è stata già importata.<br>Vuoi andare direttamente alla pratica?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaDettaglioPratica', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDettaglioPratica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            $this->rowidAppoggio = $Proges_rec['ROWID'];
                            break;
                        }
                        $model = $this->modelControllaFO;
                        $_POST = array();
                        $_POST['perms'] = $this->perms;
                        //$_POST['rowid'] = $this->rowidAppoggio;
                        $_POST['rowid'] = $this->rowidChiamante;
                        $_POST['emlInfocamere'] = $this->emlComunica;
                        $_POST['allegatiInfocamere'] = $this->allegatiComunica;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnCtrRichieste';
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent("dbClickRow");
                        $objModel->setElementId($this->modelControllaFO . "_gridCtrRichieste");
                        $objModel->parseEvent();
                        itaLib::closeForm($this->nameForm);
                        break;
                    case $this->nameForm . '_AnnullaImpRichiesta':
                        $model = $this->modelControllaFO;
                        $_POST = array();
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnCtrRichieste';
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent("openform");
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaSenzaRif':
                    case $this->nameForm . '_ConfermaImportazione':
                        itaLib::closeForm($this->nameForm);
                        $Proric_rec = $this->praLib->GetProric($this->rowidAppoggio, 'rowid');
                        $rowid = $this->rowidAppoggio;
                        if ($this->proric_rec) {
                            $Proric_rec = $this->proric_rec;
                            $rowid = $Proric_rec['ROWID'];
                        }
                        $Proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROGES WHERE GESPRA = " . $Proric_rec['RICNUM'], false);
                        if ($Proges_rec) {
                            Out::msgQuestion("Importazine Pratica!", "Il N. di richiesta on-line " . $Proric_rec['RICNUM'] . " è stata già importata.<br>Vuoi andare direttamente alla pratica?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaDettaglioPratica', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDettaglioPratica', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            $this->rowidAppoggio = $Proges_rec['ROWID'];
                            break;
                        }
                        $model = $this->modelControllaFO;
                        $_POST = array();
                        $_POST['perms'] = $this->perms;
                        //$_POST['rowid'] = $rowid;
                        $_POST['rowid'] = $this->rowidChiamante;
                        $_POST['allegatiInfocamere'] = $this->allegatiComunica;
                        $_POST['emlInfocamere'] = $this->emlComunica;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnCtrRichieste';
                        itaLib::openForm($model);
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent("dbClickRow");
                        $objModel->setElementId($this->modelControllaFO . "_gridCtrRichieste");
                        $objModel->parseEvent();
                        break;
                    case $this->nameForm . '_ConfermaPecEsternaInfocamere':
                        $_POST['datiMail']['ALLEGATIINFOCAMERE'] = $this->allegatiComunica;
                        $praGestElenco = itaModel::getInstance("praGestElenco");
                        $praGestElenco->CaricaDaPec();
                        itaLib::closeForm($this->nameForm);
//                        $model = 'praGestElenco';
//                        $_POST = array();
//                        $_POST['event'] = 'ConfermaPecEsternaInfocamere';
//                        $_POST['perms'] = $this->perms;
//                        $_POST['allegatiComunica'] = $this->allegatiComunica;
//                        $_POST[$model . '_returnModel'] = $this->nameForm;
//                        $_POST[$model . '_returnEvent'] = 'returnCaricaDaPec';
//                        itaLib::openForm($model);
                        break;
                    case $this->nameForm . '_ConfermaDettaglioPratica':
                        $praGestElenco = itaModel::getInstance("praGestElenco");
                        $praGestElenco->Dettaglio($this->rowidAppoggio);
                        itaLib::closeForm($this->nameForm);
                        break;
                    case $this->nameForm . '_AnnullaDettaglioPratica':
                    case $this->nameForm . '_AnnullaImportazione':
                    case $this->nameForm . '_AnnullaSenzaRif':
                        $this->azzeraVariabili();
                        itaLib::closeForm($this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case 'returnUploadZip':
                if ($_POST['returnId'] == 'close-portlet') {
                    $this->close();
                    break;
                }
                $ext = strtolower(pathinfo($_POST['uploadedFile'], PATHINFO_EXTENSION));
                if ($ext != "zip" && $ext != "eml") {
                    $this->setErrMessage("File non valido");
                    return false;
                } else {
                    switch ($ext) {
                        case "zip":
                            $zipPath = pathinfo($_POST['uploadedFile'], PATHINFO_DIRNAME) . "/" . pathinfo($_POST['file'], PATHINFO_FILENAME);
                            $listZip = itaZip::listZip($_POST['uploadedFile']);
                            if (strpos(strtoupper($_POST['file']), "SUAP") !== false) {
                                $xmlSUAP = pathinfo($_POST['file'], PATHINFO_FILENAME) . ".XML";
                            } else {
                                $xmlSUAP = pathinfo($_POST['file'], PATHINFO_FILENAME) . ".SUAP.XML";
                            }

                            $okComunica = false;
                            foreach ($listZip['statIndex'] as $indexEntry) {
                                if (strpos($indexEntry['name'], $xmlSUAP) !== false) {
                                    $okComunica = true;
                                    break;
                                }
                            }
                            if (!$okComunica) {
                                $this->setErrMessage("Il file zip non sembra provenire dalla camera di commercio.");
                                return false;
                            }
                            $ret = itaZip::Unzip($_POST['uploadedFile'], $zipPath);
                            if ($ret != 1) {
                                $this->setErrMessage("Estrazione file fallita");
                                return false;
                            }
                            unlink($_POST['uploadedFile']);
                            $this->CaricaPraticaDaInfocamere($zipPath, $xmlSUAP, $_POST['file']);
                            break;
                        case "eml":
                            $directMailFile = $_POST['uploadedFile'];
                            $model = 'proElencoMail';
                            $_POST = array();
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnEvent'] = 'returnMailInfocamere';
                            $_POST['modoFiltro'] = "DIRECT";
                            $_POST['directMailFile'] = $directMailFile;
                            $_POST['returnOnClose'] = true;
                            itaLib::openForm($model);
                            $objModel = itaModel::getInstance($model);
                            $objModel->setEvent("openform");
                            $objModel->parseEvent();
                            break;
                        default:
                            break;
                    }
                }

                break;
            case 'returnMailInfocamere':
                if ($_POST['returnId'] == 'close-portlet') {
                    $this->close();
                    break;
                }
                $this->CaricaDaMailInfocamere();
//                if (!$this->CaricaDaMailInfocamere($_POST['datiMail']['ELENCOALLEGATI'], $_POST['datiMail']['FILENAME'], $this->nameForm)) {
//                    Out::msgStop("Caricamento mail camera di commercio", $this->praLibInfocamere->getErrMessage());
//                    $this->returnToParent();
//                }
                break;
//            case 'returnCtrRichieste':
//                $model = 'praGestElenco';
//                $_POST = array();
//                $_POST['event'] = 'returnCtrRichieste';
//                $_POST['perms'] = $this->perms;
//                $_POST[$model . '_returnModel'] = $this->nameForm;
//                $_POST[$model . '_returnEvent'] = 'returnCaricaDaPec';
//                itaLib::openForm($model);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_allegatiComunica');
        App::$utente->removeKey($this->nameForm . '_proric_rec');
        App::$utente->removeKey($this->nameForm . '_emlComunica');
        App::$utente->removeKey($this->nameForm . '_rowidChiamante');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
//        $datiAssegnazione = $_POST['datiMail']['Dati']['Assegnazione'];
//        $proges_rec = $_POST['datiMail']['Dati']['PROGES'];
//        $_POST = array();
//        $_POST['daPortlet'] = $this->daPortlet;
//        $_POST['datiMail']['ELENCOALLEGATI'] = $this->allegati;
//        //$_POST['datiMail']['ALLEGATIINFOCAMERE'] = $this->allegatiInfocamere;
//        $_POST['datiMail']['ALLEGATICOMUNICA'] = $this->allegatiInfocamere;
//        $_POST['datiMail']['PRORIC_REC'] = $this->proric_rec;
//        $_POST['datiMail']['PROGES'] = $proges_rec;
//        $_POST['datiMail']['Assegnazione'] = $datiAssegnazione;
//        if ($this->emlInfocamere) {
//            $_POST['datiMail']['FILENAME'] = $this->emlInfocamere;
//        }
//        $_POST['tipoReg'] = 'consulta';
//
//        $objModel = itaModel::getInstance($this->returnModel);
//        $objModel->setEvent($this->returnEvent);
//        $objModel->parseEvent();
//
//        if ($close)
//            $this->close();
    }

    function CaricaPraticaDaInfocamere($zipPath, $xmlSUAP, $file) {
        $this->allegatiComunica = array();
        $allegatiZip = $this->GetFileList($zipPath, $file);
        $xmlObj = new QXML;
        $xmlObj->setXmlFromFile($zipPath . "/" . $xmlSUAP);
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        //
        $allegati = $arrayXml['struttura']['modulo']['documento-allegato'];
        //
        $praticaOriginale = $arrayXml['intestazione']['codice-pratica']['@textNode'];
        //
        if ($praticaOriginale == "") {
            $nomeFileDistinta = $arrayXml['struttura']['modulo']['distinta-modello-attivita']['@attributes']['nome-file']; //CCCVNC81L52G479C-07102017-1217.001.MDA.PDF.P7M
            $posPrimoPunto = strpos($nomeFileDistinta, ".");
            $praticaOriginale = substr($nomeFileDistinta, 0, $posPrimoPunto);
        }

        /*
         * In base al Controllo FO nuovo o vecchio, mi ricavo il Proric_rec
         */
        $Filent_Rec = $this->praLib->GetFilent(42);
        if ($Filent_Rec['FILVAL'] == 1) {
            $prafolist_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAFOLIST WHERE FOCODICEPRATICASW = '$praticaOriginale'", false);
            if ($prafolist_rec) {
                $metadati = unserialize($prafolist_rec['FOMETADATA']);
                $Proric_rec = $metadati['PRORIC_REC'];
            }
            $this->rowidChiamante = $prafolist_rec['ROW_ID'];
        } else {
        $Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRORIC WHERE CODICEPRATICASW = '$praticaOriginale'", false);
            $this->rowidChiamante = $Proric_rec['ROWID'];
        }


        if ($Proric_rec) {
            if ($this->proric_rec && $Proric_rec['RICNUM'] != $this->proric_rec['RICNUM']) {
                Out::msgQuestion("Importazione Mail Camera di Commercio", "<span style=\"font-size:1.3em\">Attenzione!!! La richiesta selezionata n. <b>" . $this->proric_rec['RICNUM'] . "</b> non corrisponde con la<br>richiesta n. <b>" . $Proric_rec['RICNUM'] . "</b> estratta dalla mail della camera di commercio.<br>Vuoi continuare? Verra importata la richiesta n. <b>" . $Proric_rec['RICNUM'] . "</b></span>", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImpRichiesta', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImpRichiesta', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ), "auto", "auto", "false"
                );
                $this->rowidAppoggio = $Proric_rec['ROWID'];
                $this->allegatiComunica = $allegatiZip;
                return false;
            }
            $dataRichiesta = substr($Proric_rec['RICDRE'], 6, 2) . "/" . substr($Proric_rec['RICDRE'], 4, 2) . "/" . substr($Proric_rec['RICDRE'], 0, 4);
            $msgRichiestaOnline = "<br>Richiesta on-line n. " . $Proric_rec['RICNUM'] . " del $dataRichiesta";
            $messaggio = "<span style=\"font-size:1.3em;font-weight:bold;\">File Acquisito.</span><div style=\"padding:4px;margin:4px\" class=\"ui-widget-content ui-corner-all\">";
            $messaggio .= "Riconosciuta Pratica Infocamere: " . $praticaOriginale;
            $messaggio .= $msgRichiestaOnline;
            if ($allegati) {
                if (!$allegati[0]) {
                    $allegati_save = array();
                    $allegati_save[0] = $allegati;
                    $allegati = $allegati_save;
                }

                $messaggio .= "<br>";
                $messaggio .= "<br>Allegati:";
                $messaggio .= "<br><hr>";
                foreach ($allegati as $allegato) {
                    $messaggio .= $allegato['descrizione']['@textNode'] . ": <span style=\"float:right;\">" . $allegato['@attributes']['nome-file'] . "</span><br>";
                }
                $messaggio .= "<br><hr>";
            }
            $messaggio .= "<br></div><br><span style=\"font-size:1.2em;font-weight:bold;\">Vuoi procedere con l'importazione della pratica n. " . $Proric_rec['RICNUM'] . " del $dataRichiesta ?</span>";
            Out::msgQuestion("IMPORTAZIONE PRATICA DA CAMERA DI COMMERCIO!", $messaggio, array(
                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImportazione', 'model' => $this->nameForm, 'shortCut' => "f8"),
                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImportazione', 'model' => $this->nameForm, 'shortCut' => "f5")
                    ), "auto", "auto", "false"
            );
            $this->rowidAppoggio = $Proric_rec['ROWID'];

            /*
             * Aggiungo il campo note nell'array degli allegati infocamere
             */
            foreach ($allegatiZip as $key1 => $allegatoZip) {
                $allegatiZip[$key1]['NOTE'] = $allegatoZip['FILEINFO'];
            }
            foreach ($allegati as $allegato) {
                $nome_file = $allegato['@attributes']['nome-file'];
                foreach ($allegatiZip as $key => $allegatoZip) {
                    if ($nome_file == $allegatoZip['FILENAME']) {
                        $allegatiZip[$key]['NOTE'] = $allegato['descrizione']['@textNode'];
                    }
                }
            }
            $this->allegatiComunica = $allegatiZip;
        } else {
            if ($this->proric_rec) {
                $this->allegatiComunica = $allegatiZip;
                $pratica = substr($this->proric_rec['RICNUM'], 4) . "/" . substr($this->proric_rec['RICNUM'], 0, 4);
                $data = substr($this->proric_rec['RICDRE'], 6, 2) . "/" . substr($this->proric_rec['RICDRE'], 4, 2) . "/" . substr($this->proric_rec['RICDRE'], 0, 4);
                Out::msgQuestion("IMPORTAZIONE PRATICA DA CAMERA DI COMMERCIO", "Non è stato trovato nessun riferimento alla camera di commerico <br>ma è stato trovato un riferimento alla pratica n. $pratica del $data. Vuoi importarla?", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSenzaRif', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSenzaRif', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ), "auto", "auto", "false"
                );
            } else {
                $this->allegatiComunica = $allegatiZip;
                Out::msgQuestion("IMPORTAZIONE PRATICA A CAMERA DI COMMERCIO", "Non è stato trovato nessun riferimento con i dati forniti dalla camera di commercio. Vuoi importare comunque la pratica?", array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSenzaRif', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPecEsternaInfocamere', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ), "auto", "auto", "false"
                );
            }
        }
        return true;
    }

    private function GetFileList($filePath, $keyPasso) {
        if (!$dh = @opendir($filePath))
            return false;
        $retListGen = array();
        $rowid = 0;
        while (false !== ($obj = @readdir($dh))) {
            if ($obj == '.' || $obj == '..')
                continue;
            $rowid += 1;
            $retListGen[$rowid] = array(
                'rowid' => $rowid,
                'FILEPATH' => $filePath . '/' . $obj,
                'DATAFILE' => $filePath . '/' . $obj,
                'SEQUENZA' => $keyPasso,
                'FILENAME' => $obj,
                'FILEINFO' => $obj
            );
        }
        closedir($dh);
        return $retListGen;
    }

    private function CaricaDaMailInfocamere() {
        $allegati = $_POST['datiMail']['ELENCOALLEGATI'];
        foreach ($allegati as $allegato) {
            if (strpos(strtoupper($allegato['FILENAME']), ".SUAP.XML") !== false) {
                $xmlSUAP = $allegato['FILENAME'];
                break;
            }
        }
        if ($xmlSUAP == "") {
            Out::msgStop("Caricamento mail camera di commercio ", "Attenzione!! La mail che si sta tentando di importare non sembra provenire dalla camera di commercio");
            return false;
        }
        $dirName = pathinfo($xmlSUAP, PATHINFO_FILENAME);
        $tmpPathAllegatiComunica = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "/" . $dirName;
        if (!@is_dir($tmpPathAllegatiComunica)) {
            if (!mkdir($tmpPathAllegatiComunica)) {
                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                $this->returnToParent();
            }
        }

        /*
         * salvo in session la mail di Comunica
         */
        if ($_POST['datiMail']['FILENAME']) {
            $this->emlComunica = $_POST['datiMail']['FILENAME'];
        }

        /*
         * salvo gli allegati Comunica
         */
        foreach ($allegati as $allegato) {
            if (!@copy($allegato['DATAFILE'], $tmpPathAllegatiComunica . "/" . $allegato['FILENAME'])) {
                Out::msgStop("Copia allegati mail", "Attenzione!! errore nella copia del file " . $allegato['FILENAME']);
                return false;
            }
        }
        $this->CaricaPraticaDaInfocamere($tmpPathAllegatiComunica, $xmlSUAP);
    }

    private function azzeraVariabili() {
        $this->proric_rec = array();
        $this->allegatiComunica = array();
        $this->rowidAppoggio = null;
        $this->emlComunica = null;
    }

}
