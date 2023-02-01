<?php

/**
 *
 * UTILITA DI UNIFICAZIONE SUAP SUE
 *
 * PHP Version 5
 *
 * @category
 * @package    Partiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    24.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';

function praUnificazioneSuapSue() {
    $praUnificazioneSuapSue = new praUnificazioneSuapSue();
    $praUnificazioneSuapSue->parseEvent();
    return;
}

class praUnificazioneSuapSue extends itaModel {

    public $nameForm = "praUnificazioneSuapSue";
    public $praLib;
    public $PRAM_SORG;
    public $PRAM_DEST;
    public $PROT_SORG;
    public $PROT_DEST;
    public $ITALWEB_SORG;
    public $ITALWEB_DEST;
    public $PRAM_MIGRA;
    public $errCode;
    public $errMessage;
    public $message;
    public $dirLog;
    public $logFile;
    public $logger;
    public $anaspa_migra;
    public $anatsp_migra;
    public $ananom_migra;
    public $doc_documenti_migra;
    public $ditta_sorgente;
    public $ditta_destino;
    public $Numero;
    public $Anno;
    public $ImportaFascicoli;
    public $ImportaMittDest;
    public $ImportaPassi;
    public $ImportaSoggetti;
    public $ImportaImmobili;
    public $ImportaStati;
    public $ImportaNote;
    public $ImportaPagamenti;
    public $ImportaComunicazioni;
    public $ImportaMailArchivio;
    public $ImportaMail;
    public $ImportaDatiAgg;
    public $ImportaAllegati;

    public function setDitta_sorgente($ditta_sorgente) {
        $this->ditta_sorgente = $ditta_sorgente;
    }

    public function setDitta_destino($ditta_destino) {
        $this->ditta_destino = $ditta_destino;
    }

    public function setNumero($Numero) {
        $this->Numero = $Numero;
    }

    public function setAnno($Anno) {
        $this->Anno = $Anno;
    }

    public function setImportaFascicoli($ImportaFascicoli) {
        $this->ImportaFascicoli = $ImportaFascicoli;
    }

    public function setImportaMittDest($ImportaMittDest) {
        $this->ImportaMittDest = $ImportaMittDest;
    }

    public function setImportaPassi($ImportaPassi) {
        $this->ImportaPassi = $ImportaPassi;
    }

    public function setImportaSoggetti($ImportaSoggetti) {
        $this->ImportaSoggetti = $ImportaSoggetti;
    }

    public function setImportaImmobili($ImportaImmobili) {
        $this->ImportaImmobili = $ImportaImmobili;
    }

    public function setImportaStati($ImportaStati) {
        $this->ImportaStati = $ImportaStati;
    }

    public function setImportaNote($ImportaNote) {
        $this->ImportaNote = $ImportaNote;
    }

    public function setImportaPagamenti($ImportaPagamenti) {
        $this->ImportaPagamenti = $ImportaPagamenti;
    }

    public function setImportaComunicazioni($ImportaComunicazioni) {
        $this->ImportaComunicazioni = $ImportaComunicazioni;
    }

    public function setImportaMailArchivio($ImportaMailArchivio) {
        $this->ImportaMailArchivio = $ImportaMailArchivio;
    }

    public function setImportaMail($ImportaMail) {
        $this->ImportaMail = $ImportaMail;
    }

    public function setImportaDatiAgg($ImportaDatiAgg) {
        $this->ImportaDatiAgg = $ImportaDatiAgg;
    }

    public function setImportaAllegati($ImportaAllegati) {
        $this->ImportaAllegati = $ImportaAllegati;
    }

    public function setSistemaDipendenti($SistemaDipendenti) {
        $this->SistemaDipendenti = $SistemaDipendenti;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getMessage() {
        return $this->message;
    }

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->ditta_sorgente = App::$utente->getKey($this->nameForm . '_ditta_sorgente');
        $this->ditta_destino = App::$utente->getKey($this->nameForm . '_ditta_destino');
        $this->dirLog = sys_get_temp_dir();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ditta_sorgente', $this->ditta_sorgente);
            App::$utente->setKey($this->nameForm . '_ditta_destino', $this->ditta_destino);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->ditta_sorgente = $this->ditta_destino = null;
                Out::valore($this->nameForm . '_SistemaDipendenti', 0);
                Out::valore($this->nameForm . '_ImportaMittDest', 0);
                Out::valore($this->nameForm . '_ImportaPassi', 0);
                Out::valore($this->nameForm . '_ImportaAllegati', 0);
                Out::valore($this->nameForm . '_ImportaDatiAgg', 0);
                Out::valore($this->nameForm . '_ImportaSoggetti', 0);
                Out::valore($this->nameForm . '_ImportaStati', 0);
                Out::valore($this->nameForm . '_ImportaImmobili', 0);
                Out::valore($this->nameForm . '_ImportaNote', 0);
                Out::valore($this->nameForm . '_ImportaMail', 0);
                Out::valore($this->nameForm . '_ImportaComunicazioni', 0);
                Out::valore($this->nameForm . '_Anno', "2019");
                Out::hide($this->nameForm . '_Cancella');
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        Out::msgQuestion("ATTENZIONE!", "Confermi l'importazione dei fascioli dalla ditta $this->ditta_sorgente alla ditta $this->ditta_destino?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaImportFascicoli', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaImportFascicoli', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;

                    case $this->nameForm . '_ConfermaImportFascicoli':
                        /*
                         * Inizializzo variabili 
                         */
                        $this->ditta_sorgente = $_POST[$this->nameForm . "_DittaSorgente"];
                        $this->ditta_destino = $_POST[$this->nameForm . "_DittaDestino"];

                        $this->Numero = $_POST[$this->nameForm . "_Numero"];
                        $this->Anno = $_POST[$this->nameForm . "_Anno"];

                        $this->SistemaDipendenti = $_POST[$this->nameForm . '_SistemaDipendenti'];
                        $this->ImportaMittDest = $_POST[$this->nameForm . '_ImportaMittDest'];
                        $this->ImportaPassi = $_POST[$this->nameForm . '_ImportaPassi'];
                        $this->ImportaAllegati = $_POST[$this->nameForm . '_ImportaAllegati'];
                        $this->ImportaDatiAgg = $_POST[$this->nameForm . '_ImportaDatiAgg'];
                        $this->ImportaSoggetti = $_POST[$this->nameForm . '_ImportaSoggetti'];
                        $this->ImportaStati = $_POST[$this->nameForm . '_ImportaStati'];
                        $this->ImportaImmobili = $_POST[$this->nameForm . '_ImportaImmobili'];
                        $this->ImportaNote = $_POST[$this->nameForm . '_ImportaNote'];
                        $this->ImportaMail = $_POST[$this->nameForm . '_ImportaMail'];
                        $this->ImportaComunicazioni = $_POST[$this->nameForm . '_ImportaComunicazioni'];

                        if (!$this->lanciaUnificazione()) {
                            Out::msgStop("Errore", $this->errMessage);
                            break;
                        }

                        break;
//                    case $this->nameForm . '_Cancella':
//                        if ($_POST[$this->nameForm . "_DittaDestino"] == "") {
//                            Out::msgStop("Cancellazione Fascicoli", "Selezionare la Ditta di Destinazione");
//                            break;
//                        }
//                        Out::msgQuestion("ATTENZIONE!", "Confermi la cancellazione dei fascicoli importati nella ditta " . $_POST[$this->nameForm . "_DittaDestino"] . "?", array(
//                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaFascicoli', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaFascicoli', 'model' => $this->nameForm, 'shortCut' => "f5")
//                                )
//                        );
//                        break;
//                    case $this->nameForm . '_ConfermaCancellaFascicoli':
//                        $ditta_destino = $_POST[$this->nameForm . "_DittaDestino"];
//
//                        /*
//                         * Controllo esistenza DB Destino
//                         */
//                        $this->PRAM_DEST = ItaDB::DBOpen('PRAM', $ditta_destino);
//                        try {
//                            $this->PRAM_DEST->exists();
//                        } catch (Exception $exc) {
//                            Out::msgStop("Errore", $exc->getMessage());
//                            break;
//                        }
//                        //
//                        $sql = "SELECT
//                                    PROGES.*,
//                                    (SELECT PROGES_MIGRA.GESNUM_DEST FROM " . $this->PRAM_MIGRA->getDB() . ".PROGES_MIGRA PROGES_MIGRA  WHERE GESNUM = " . $this->PRAM_MIGRA->getDB() . ".PROGES_MIGRA.GESNUM_ORIG) AS GESNUM_DEST
//                                FROM
//                                    PROGES
//                                WHERE
//                                    GESNUM IN (SELECT GESNUM_ORIG FROM " . $this->PRAM_MIGRA->getDB() . ".PROGES_MIGRA WHERE GESNUM = " . $this->PRAM_MIGRA->getDB() . ".PROGES_MIGRA.GESNUM_ORIG)";
//                        $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DEST, $sql, true);
//                        if (!$proges_tab) {
//                            Out::msgInfo("Cancellazione Fascicoli", "Fascicoli non trovati");
//                            break;
//                        }
//
//                        $praLibPratica = praLibPratica::getInstance();
//                        $i = 0;
//                        foreach ($proges_tab as $proges_rec) {
//                            if (!$praLibPratica->cancella($this, substr($proges_rec['GESNUM_DEST'], 4), substr($proges_rec['GESNUM_DEST'], 0, 4))) {
//                                Out::msgStop("Cancellazione Fascicoli", "Errore cancellazione pratica: " . $praLibPratica->getErrMessage());
//                                break;
//                            }
//                            $i++;
//                        }
//                        Out::msgInfo("Cancellazione Fascicoli", "Cancellati correttamente $i fascicoli importati.");
//
//                        /*
//                         * Svuoto Le Tabelle MIGRA
//                         */
//                        ItaDB::DBSQLExec($this->PRAM_MIGRA, "TRUNCATE PROGES_MIGRA");
//                        ItaDB::DBSQLExec($this->PRAM_MIGRA, "TRUNCATE PROPAS_MIGRA");
//
//
//                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Numero':
                        $Dal_num = $_POST[$this->nameForm . '_Numero'];
                        if ($Dal_num) {
                            $Dal_num = str_pad($Dal_num, 6, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_Numero', $Dal_num);
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_ditta_sorgente');
        App::$utente->removeKey($this->nameForm . '_ditta_destino');
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function ImportFascicoli() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Testate Fascicoli: Anno = $anno Numero = $numero", "  ", true);
        $sql = "SELECT * FROM PROGES WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND GESNUM = '$anno$numero'";
            } else {
                $sql .= " AND GESNUM LIKE '$anno%'";
            }
        } else {
            if ($numero) {
                $this->errCode = -1;
                $this->errMessage = "Inserire un Anno valido.";
                return false;
            }
        }
        $sql .= " ORDER BY GESNUM ASC";
        try {
            $proges_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        } catch (Exception $exc) {
            $this->errCode = -1;
            $this->errMessage = $exc->getMessage();
            return false;
        }
        if (!$proges_tab) {
            $this->logERR("Fascicoli non trovati.");
            return false;
        }
        $totTestate = count($proges_tab);
        $this->log("Estratti $totTestate Testate Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        $curr_anno = '';
        foreach ($proges_tab as $proges_rec) {
            /*
             * CONTROLLI SU PRAM_MIGRA
             * 
             * 
             */
            $sqlCheck = "SELECT GESNUM_DEST FROM PROGES_MIGRA WHERE GESNUM_ORIG='{$proges_rec['GESNUM']}'";
            $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
            if ($proges_migra_rec) {
                $skip++;
                $this->log("Testate Importate: $i/$totTestate  Saltate: $skip", "  ", true, false, false, true);
                continue;
            }

            if (substr($proges_rec['GESNUM'], 0, 4) !== $curr_anno) {
                $curr_anno = substr($proges_rec['GESNUM'], 0, 4);
                $max_gesnum = $this->getLastGesnum($proges_rec);
                $this->log("Avvio importazione per l'anno $curr_anno. Ultimo Prog. Utilizzato $max_gesnum", "  ", true, false);
            }
            $max_gesnum++;
            $new_proges_rec = $proges_rec;
            unset($new_proges_rec['ROWID']);
            $new_proges_rec['GESNUM'] = $max_gesnum;
            if (isset($this->anatsp_migra[$proges_rec['GESTSP']])) {
                $new_proges_rec['GESTSP'] = $this->anatsp_migra[$proges_rec['GESTSP']];
            }
            if (isset($this->anaspa_migra[$proges_rec['GESSPA']])) {
                $new_proges_rec['GESSPA'] = $this->anaspa_migra[$proges_rec['GESSPA']];
            }
            if (isset($this->ananom_migra[$proges_rec['GESRES']])) {
                $new_proges_rec['GESRES'] = $this->ananom_migra[$proges_rec['GESRES']];
            }
            $new_proges_rec['GESMIGRA'] = $this->ditta_sorgente . "-GESNUM:" . $proges_rec['GESNUM'];


            /*
             * Insersico nuovo record PROGES
             */
            try {
                ItaDB::DBInsert($this->PRAM_DEST, 'PROGES', 'ROWID', $new_proges_rec);
            } catch (Exception $exc) {
                $this->logERR("Inserimento PROGES fallito per la pratica originale n. " . $proges_rec['GESNUM']);
                $this->logERR(print_r($new_proges_rec, true) . "\n" . $exc->getMessage());
                return false;
            }
            $lastId = $this->PRAM_DEST->getLastId();
            $i++;

            /*
             * Inserisco record PROGES_MIGRA
             */
            if (!$this->saveProgesMigra($new_proges_rec['GESNUM'], $proges_rec['GESNUM'], $lastId, $proges_rec['ROWID'])) {
                return false;
            }

            /*
             * Inserisco record PROGESSUB
             */
            $new_progessub_rec = array();
            $progessub_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM PROGESSUB WHERE PRONUM = '" . $proges_rec['GESNUM'] . "'", false);
            if ($progessub_rec) {
                $new_progessub_rec = $progessub_rec;
                $new_progessub_rec['PRONUM'] = $new_proges_rec['GESNUM'];
                unset($new_progessub_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PROGESSUB', 'ROWID', $new_progessub_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento PROGESSUB fallito per la pratica originale n. " . $proges_rec['GESNUM']);
                    $this->logERR(print_r($new_progessub_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
            }
            $this->log("Testate Importate: $i/$totTestate  Saltate: $skip", "  ", true, false, false, true);
        }
        $this->log("Testate Importate: $i/$totTestate  Saltate: $skip", "OK", true, true, true);
        return true;
    }

    private function ImportMittentiDestinatari() {
        $sql = "SELECT * FROM ANAMED ORDER BY MEDCOD ASC";
        $anamed_tab = ItaDB::DBSQLSelect($this->PROT_SORG, $sql, true);
        if (!$anamed_tab) {
            $this->logERR("Mittenti/Destinatari non trovati.");
            return false;
        }

        $totAnamed = count($anamed_tab);
        $i = 0;
        $skip = 0;
        $this->log("Estratti $totAnamed Mittenti/Destinatari", "  ", true);
        /*
         * Ultimo Codice MEDCOD DISPONIBILE
         */
        $max_anamed_rec = ItaDB::DBSQLSelect($this->PROT_DEST, "SELECT MAX(MEDCOD) AS MAX_MEDCOD FROM ANAMED ORDER BY MEDCOD DESC", false);
        $max_anamed = $max_anamed_rec['MAX_MEDCOD'];

        foreach ($anamed_tab as $anamed_rec) {
            /*
             * CONTROLLI SU PRAM_MIGRA
             * 
             * 
             */

            $sqlCheck = "SELECT MEDCOD_DEST FROM ANAMED_MIGRA WHERE MEDCOD_ORIG='{$anamed_rec['MEDCOD']}'";
            $anamed_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
            if ($anamed_migra_rec) {
                $skip++;
                $this->log("Mittenti/Destinatari Importati: $i/$totAnamed  Saltati: $skip", "  ", true, false, false, true);
                continue;
            }

            $oldMedcod = $anamed_rec['MEDCOD'];
            $max_anamed++;
            $anamed_rec['MEDCOD'] = str_pad($max_anamed, 6, "0", STR_PAD_LEFT);
            /*
             * insert MITTENTE/DESTINATARIO
             */
            unset($anamed_rec['ROWID']);
            try {
                ItaDB::DBInsert($this->PROT_DEST, 'ANAMED', 'ROWID', $anamed_rec);
            } catch (Exception $exc) {
                $this->logERR("Inserimento ANAMED fallito per il codice originale n. " . $oldMedcod);
                $this->logERR(print_r($anamed_rec, true) . "\n" . $exc->getMessage());
                return false;
            }

            /*
             * Insert ANAMED_MIGRA
             */
            if (!$this->saveAnamedMigra($oldMedcod, $anamed_rec['MEDCOD'])) {
                return false;
            }
            $i++;
            $this->log("Mittenti/Destinatari Importati: $i/$totAnamed  Saltati: $skip", "  ", true, false, false, true);
        }
        $this->log("Mittenti/Destinatari Importati: $i/$totAnamed  Saltati: $skip", "OK", true, true, true);
        return true;
    }

    private function ImportPassi() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Passi: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT ROWID, PRONUM, PROPAK, PRORPA FROM PROPAS WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND PRONUM = '$anno$numero'";
            } else {
                $sql .= " AND PRONUM LIKE '$anno%'";
            }
        }
        $sql .= " ORDER BY PRONUM";

        $propas_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$propas_tab) {
            $this->logERR("Passi non trovati.");
            return false;
        }

        $totPassi = count($propas_tab);
        $this->log("Estratti $totPassi Passi Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        $curr_propak_idx = 0;
        $curr_pronum = "**********";
        try {
            foreach ($propas_tab as $propas_rec) {
                if ($propas_rec['PRONUM'] !== $curr_pronum) {
                    $curr_pronum = $propas_rec['PRONUM'];
                    $curr_propak_idx = 0;
                }

                /*
                 * CONTROLLI SU PRAM_MIGRA
                 * 
                 * 
                 */
                //$sqlCheck = "SELECT PROPAK_DEST, ROWID_DEST FROM PROPAS_MIGRA WHERE PROPAK_ORIG='{$propas_rec['PROPAK']}'";
                $sqlCheck = "SELECT PROPAK_DEST, ROWID_DEST FROM PROPAS_MIGRA WHERE ROWID_ORIG='{$propas_rec['ROWID']}'";
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($propas_migra_rec) {
                    $skip++;
                    $new_propas_rowid_tab[] = $propas_migra_rec['ROWID_DEST'];
                    $this->log("Passi Importati: $i/$totPassi  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }

                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $propas_rec['PRONUM'] . "'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Inserimento PASSI per il Fascicolo n. " . $propas_rec['PRONUM'] . " non trovato su PROGES_MIGRA.");
                    return false;
                }


                $new_propas_rec = array();
                $new_propas_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM PROPAS WHERE ROWID = " . $propas_rec['ROWID'], false);
                if (!$new_propas_rec) {
                    $this->logERR("Lettura Passo di Origine ROWID n. " . $propas_rec['ROWID'] . " non trovato su PROPAS Sorgente.");
                    return false;
                }
                unset($new_propas_rec['ROWID']);
                $new_propas_rec['PRONUM'] = $proges_migra_rec['GESNUM_DEST'];
                //$new_propas_rec['PROPAK'] = $this->praLib->PropakGenerator($new_propas_rec['PRONUM']);
                $curr_propak_idx++;
                $new_propas_rec['PROPAK'] = $this->praLib->PropakGenerator($new_propas_rec['PRONUM'], $curr_propak_idx);
                $new_propas_rec['PROTBA'] = $this->doc_documenti_migra['CODICE_ORIG'];
                if (isset($this->ananom_migra[$propas_rec['PRORPA']])) {
                    $new_propas_rec['PRORPA'] = $this->ananom_migra[$propas_rec['PRORPA']];
                }

                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PROPAS', 'ROWID', $new_propas_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento PROPAS fallito per la pratica n. " . $propas_rec['PRONUM'] . " Passo Originale: " . $propas_rec['PROPAK']);
                    $this->logERR(print_r($new_propas_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                $new_propas_rec['ROWID'] = $lastId;

                $new_propas_rowid_tab[] = $new_propas_rec['ROWID'];

                if (!$this->savePropasMigra($propas_rec, $lastId, $new_propas_rec)) {
                    return false;
                }
                $i++;
                $this->log("Passi Importati: $i/$totPassi  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Passi Importati: $i/$totPassi  Saltati: $skip", "OK", true, true, true);


            /*
             * RILEGGO I SOLO PROPAS_MIGRA CON CONDIZIONI/SALTI DA SISTEMARE
             */
            $new_propas_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT ROWID_DEST FROM PROPAS_MIGRA WHERE PROVPA_ORIG<>'' OR PROVPN_ORIG<>'' OR PROCTP_ORIG<>'' OR PROKPRE_ORIG<>''", true);
            $totPropasMigra = count($new_propas_migra_tab);
            $this->log("Inizio Allineamento chiavi salti e condizioni su $totPropasMigra passi inseriti", "  ", true);

            $j = 0;
            foreach ($new_propas_migra_tab as $new_propas_migra_rec) {
                /*
                 * Rilaggo i dati del passo e i riferimenti di PROPAS_MIGRA
                 * 
                 */
                $new_propas_rec = ItaDB::DBSQLSelect($this->PRAM_DEST, "SELECT * FROM PROPAS WHERE ROWID = " . $new_propas_migra_rec['ROWID_DEST'], false);
                if (!$new_propas_rec) {
                    $this->logERR("Ri-Lettura Passo Caricato. " . $new_propas_migra_rec['ROWID'] . " non trovato su PROPAS Destino.");
                    return false;
                }
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_DEST = '{$new_propas_rec['PROPAK']}'", false);
                if (!$propas_migra_rec) {
                    $this->logERR("Ri-Lettura record Propas_migra di riferimento: {$new_propas_rec['PROPAK']} non trovato.");
                    return false;
                }

                /*
                 * CONVERSIONE PROVPA ORIGINALE
                 */
                if ($propas_migra_rec['PROVPA_ORIG']) {
                    $propas_migra_vpa_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '{$propas_migra_rec['PROVPA_ORIG']}'", false);
                    if ($propas_migra_vpa_rec['PROPAK_DEST']) {
                        $propas_migra_rec['PROVPA_DEST'] = $propas_migra_vpa_rec['PROPAK_DEST'];
                        $new_propas_rec['PROVPA'] = $propas_migra_vpa_rec['PROPAK_DEST'];
                    }
                }

                /*
                 * CONVERSIONE PROVPN ORIGINALE
                 */
                if ($propas_migra_rec['PROVPN_ORIG']) {
                    $propas_migra_vpn_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $propas_migra_rec['PROVPN_ORIG'] . "'", false);
                    if ($propas_migra_vpn_rec['PROPAK_DEST']) {
                        $propas_migra_rec['PROVPN_DEST'] = $propas_migra_vpn_rec['PROPAK_DEST'];
                        $new_propas_rec['PROVPN'] = $propas_migra_vpn_rec['PROPAK_DEST'];
                    }
                }

                /*
                 * CONVERSIONE PROCTP ORIGINALE
                 */
                if ($propas_migra_rec['PROCTP_ORIG']) {
                    $propas_migra_ctp_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $propas_migra_rec['PROCTP_ORIG'] . "'", false);
                    if ($propas_migra_ctp_rec['PROPAK_DEST']) {
                        $propas_migra_rec['PROCTP_DEST'] = $propas_migra_ctp_rec['PROPAK_DEST'];
                        $new_propas_rec['PROCTP'] = $propas_migra_ctp_rec['PROPAK_DEST'];
                    }
                }

                /*
                 * CONVERSIONE PROKPRE ORIGINALE
                 */
                if ($propas_migra_rec['PROKPRE_ORIG']) {
                    $propas_migra_kpre_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $propas_migra_rec['PROKPRE_ORIG'] . "'", false);
                    if ($propas_migra_kpre_rec['PROPAK_DEST']) {
                        $propas_migra_rec['PROKPRE_DEST'] = $propas_migra_kpre_rec['PROPAK_DEST'];
                        $new_propas_rec['PROKPRE'] = $propas_migra_kpre_rec['PROPAK_DEST'];
                    }
                }

                /*
                 * Aggiorno il Record con le Nuove Chivi Aggiornate
                 */
                try {
                    ItaDb::DBUpdate($this->PRAM_DEST, 'PROPAS', 'ROWID', $new_propas_rec);
                } catch (Exception $exc) {
                    $this->logERR("Fallito Allineamento chiavi salti e condizioni del fascicolo destino: " . $new_propas_rec['PRONUM'] . " Passo: " . $new_propas_rec['PROPAK']);
                    $this->logERR(print_r($new_propas_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }

                try {
                    ItaDb::DBUpdate($this->PRAM_MIGRA, 'PROPAS_MIGRA', 'ROW_ID', $propas_migra_rec);
                } catch (Exception $exc) {
                    $this->logERR("Fallito Allineamento chiavi salti e condizioni del fascicolo destino: " . $new_propas_rec['PRONUM'] . " PROPAS_MIGRA: " . $propas_migra_rec['PROPAK']);
                    $this->logERR(print_r($propas_migra_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $j++;
                $this->log("Salti Allineati: $j/$totPropasMigra", "  ", true, false, false, true);
            }
            $this->log("Salti Allineati: $j/$totPropasMigra", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR($exc->getTraceAsString());
        }

        return true;
    }

    private function ImportSoggetti() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Soggetti Fascicolo: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT ROWID, DESNUM FROM ANADES WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND DESNUM = '$anno$numero'";
            } else {
                $sql .= " AND DESNUM LIKE '$anno%'";
            }
        }
        $soggetti_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$soggetti_tab) {
            $this->logERR("Soggetti non trovati.");
            return false;
        }
        $totSoggetti = count($soggetti_tab);
        $this->log("Estratti $totSoggetti Soggetti Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($soggetti_tab as $soggetti_rec) {
                /*
                 * CONTROLLI SU PRAM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM ANADES_MIGRA WHERE ROWID_ORIG={$soggetti_rec['ROWID']}";
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($propas_migra_rec) {
                    $skip++;
                    $this->log("Soggetti Importati: $i/$totSoggetti  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }

                /*
                 * Aggiorno il codice del fascicolo
                 */
                $oldDesnum = $soggetti_rec['DESNUM'];
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '$oldDesnum'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $soggetti_rec['DESNUM']);
                    return false;
                }
                $new_soggetto_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM ANADES WHERE ROWID = " . $soggetti_rec['ROWID'], false);
                $new_soggetto_rec['DESNUM'] = $proges_migra_rec['GESNUM_DEST'];

                /*
                 * insert SOGGETTO
                 */
                unset($new_soggetto_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'ANADES', 'ROWID', $new_soggetto_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Soggetto fallito per la pratica $oldDesnum");
                    $this->logERR(print_r($new_soggetto_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                if (!$this->saveAnadesMigra($soggetti_rec, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Soggetti Importati: $i/$totSoggetti", "  ", true, false, false, true);
            }
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        $this->log("Soggetti Importati: $i/$totSoggetti", "OK", true, true, true);
        return true;
    }

    private function ImportImmobili() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Immobili Fascicolo: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PRAIMM WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND PRONUM = '$anno$numero'";
            } else {
                $sql .= " AND PRONUM LIKE '$anno%'";
            }
        }

        $immobili_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$immobili_tab) {
            $this->logERR("Immobili non trovati.");
            return false;
        }
        $totImmobili = count($immobili_tab);
        $this->log("Estratti $totImmobili Immobili Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($immobili_tab as $immobili_rec) {
                /*
                 * CONTROLLI SU PRAM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRAIMM_MIGRA WHERE ROWID_ORIG={$immobili_rec['ROWID']}";
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($propas_migra_rec) {
                    $skip++;
                    $this->log("Immobili Importati: $i/$totImmobili  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }
                /*
                 * Aggiorno il codice del fascicolo
                 */
                $oldPronum = $immobili_rec['PRONUM'];
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $oldPronum . "'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $immobili_rec['PRONUM']);
                    return false;
                }
                $new_immobili_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM PRAIMM WHERE ROWID = " . $immobili_rec['ROWID'], false);
                $new_immobili_rec['PRONUM'] = $proges_migra_rec['GESNUM_DEST'];

                /*
                 * insert IMMOBILI
                 */
                unset($new_immobili_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRAIMM', 'ROWID', $new_immobili_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Immobile fallito per la pratica $oldPronum");
                    $this->logERR(print_r($immobili_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                if (!$this->savePraimmMigra($immobili_rec, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Immobili Importati: $i/$totImmobili  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Immobili Importati: $i/$totImmobili  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        return true;
    }

    private function ImportStati() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Stati Fascicolo: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT ROWID, STANUM FROM PRASTA WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND STANUM = '$anno$numero'";
            } else {
                $sql .= " AND STANUM LIKE '$anno%'";
            }
        }
        $prasta_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$prasta_tab) {
            $this->logERR("Stati non trovati.");
            return false;
        }
        $totStati = count($prasta_tab);
        $this->log("Estratti $totStati Stati Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($prasta_tab as $prasta_rec) {
                /*
                 * CONTROLLI SU PRAM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRASTA_MIGRA WHERE ROWID_ORIG={$prasta_rec['ROWID']}";
                $prasta_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($prasta_migra_rec) {
                    $skip++;
                    $this->log("Stati Importati: $i/$totStati  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }
                /*
                 * Aggiorno il codice del fascicolo
                 */
                $oldStanum = $prasta_rec['STANUM'];
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $prasta_rec['STANUM'] . "'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $oldStanum);
                    return false;
                }
                $new_prasta_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM PRASTA WHERE ROWID = " . $prasta_rec['ROWID'], false);
                $new_prasta_rec['STANUM'] = $proges_migra_rec['GESNUM_DEST'];

                /*
                 * Aggiorno chiave del passo se presente
                 */
                if ($prasta_rec['STAPAK']) {
                    $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $prasta_rec['STAPAK'] . "'", false);
                    if (!$propas_migra_rec) {
                        $this->logERR("Record PROPAS_MIGRA non trovato per il passo " . $prasta_rec['STAPAK']);
                        return false;
                    }
                    $new_prasta_rec['STAPAK'] = $propas_migra_rec['PROPAK_DEST'];
                }

                /*
                 * insert STATI
                 */
                unset($new_prasta_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRASTA', 'ROWID', $new_prasta_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Stato fallito per la pratica $oldStanum");
                    $this->logERR(print_r($prasta_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                if (!$this->savePrastaMigra($prasta_rec, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Stati Importati: $i/$totStati  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Stati Importati: $i/$totStati  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        return true;
    }

    private function ImportNote() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Note Fascicolo: Anno = $anno Numero = $numero", "  ", true);

        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $whereGesnum = " AND GESNUM = '$anno$numero'";
                $wherePronum = " AND PRONUM = '$anno$numero'";
            } else {
                $whereGesnum = " AND GESNUM LIKE '$anno%'";
                $wherePronum = " AND PRONUM LIKE '$anno%'";
            }
        }

        $sqlNote = "SELECT
                        NOTE.*
                    FROM
                        NOTE
                        LEFT OUTER JOIN NOTECLAS ON NOTE.ROWID = NOTECLAS.ROWIDNOTE
                        LEFT OUTER JOIN PROGES ON NOTECLAS.ROWIDCLASSE = PROGES.ROWID AND NOTECLAS.CLASSE = 'PROGES'
                    WHERE 1
                       $whereGesnum
                           
                       UNION
                       
                    SELECT
                        NOTE.*
                    FROM
                        NOTE
                        LEFT OUTER JOIN NOTECLAS ON NOTE.ROWID = NOTECLAS.ROWIDNOTE
                        LEFT OUTER JOIN PROPAS ON NOTECLAS.ROWIDCLASSE = PROPAS.ROWID AND NOTECLAS.CLASSE = 'PROPAS'
                    WHERE 1
                       $wherePronum";


        $tab_note = ItaDB::DBSQLSelect($this->PRAM_SORG, $sqlNote, true);
        if (!$tab_note) {
            $this->logERR("Note non trovate.");
            return false;
        }

        $totNote = count($tab_note);
        $this->log("Estratte $totNote Note Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($tab_note as $rec_note) {
                /*
                 * CONTROLLI SU NOTE_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM NOTE_MIGRA WHERE ROWID_ORIG={$rec_note['ROWID']}";
                $note_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($note_migra_rec) {
                    $skip++;
                    $this->log("Note Importate: $i/$totNote  Saltate: $skip", "  ", true, false, false, true);
                    continue;
                }
                /*
                 * insert NOTE
                 */
                $oldRowid = $rec_note['ROWID'];
                unset($rec_note['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'NOTE', 'ROWID', $rec_note);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento NOTA fallito per il rowid $oldRowid");
                    $this->logERR(print_r("Inserimento NOTA fallito per il rowid $oldRowid", true) . "\n" . $exc->getMessage());
                    return false;
                }
                $newRowid = $this->PRAM_DEST->getLastId();
                /*
                 * Save NOTE_MIGRA
                 */
                if (!$this->saveNoteMigra($oldRowid, $newRowid)) {
                    return false;
                }

                $sql = "SELECT * FROM NOTECLAS WHERE ROWIDNOTE = " . $oldRowid;
                $tab_noteclass = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
                foreach ($tab_noteclass as $rec_noteclass) {
                    $oldRowidNoteclas = $rec_noteclass['ROWID'];
                    unset($rec_noteclass['ROWID']);
                    $rec_noteclass['ROWIDNOTE'] = $newRowid;
                    switch ($rec_noteclass['CLASSE']) {
                        case "PROGES":
                            $table = "PROGES_MIGRA";
                            break;
                        case "PROPAS":
                            $table = "PROPAS_MIGRA";
                            break;
                    }

                    $migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM $table WHERE ROWID_ORIG = '" . $rec_noteclass['ROWIDCLASSE'] . "'", false);
                    if (!$migra_rec) {
                        $this->log("Record $table non trovato per la classe nota con rowid " . $rec_noteclass['ROWIDCLASSE'], "WR", true, true, true);
                        continue;
                    }
                    $rec_noteclass['ROWIDCLASSE'] = $migra_rec['ROWID_DEST'];

                    /*
                     * insert NOTECLAS
                     */
                    try {
                        ItaDB::DBInsert($this->PRAM_DEST, 'NOTECLAS', 'ROWID', $rec_noteclass);
                    } catch (Exception $exc) {
                        $this->logERR("Inserimento CLASSE NOTA fallito per il rowid $oldRowidNoteclas");
                        return false;
                    }
                    $newRowidnoteClas = $this->PRAM_DEST->getLastId();
                    /*
                     * Save NOTECLAS_MIGRA
                     */
                    if (!$this->saveNoteClasMigra($oldRowidNoteclas, $newRowidnoteClas)) {
                        return false;
                    }
                    $i++;
                }
                $this->log("Note Importate: $i/$totNote  Saltate: $skip", "  ", true, false, false, true);
            }
            $this->log("Note Importate: $i/$totNote  Saltate: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        return true;
    }

    private function ImportaPagamenti() {
        /*
         * importi 
         */
        $numero = $this->Numero;
        $anno = $this->Anno;

        $this->log("Inizio Importazione Importi Fascioli: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PROIMPO WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND IMPONUM = '$anno$numero'";
            } else {
                $sql .= " AND IMPONUM LIKE '$anno%'";
            }
        }
        $proimpo_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$proimpo_tab) {
            $this->logERR("Importi non trovati.");
            return false;
        }
        $totImporti = count($proimpo_tab);
        $this->log("Estratti $totImporti Importi Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($proimpo_tab as $proimpo_rec) {
                /*
                 * CONTROLLI SU PRAM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PROIMPO_MIGRA WHERE ROWID_ORIG={$proimpo_rec['ROWID']}";
                $proimpo_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($proimpo_migra_rec) {
                    $skip++;
                    $this->log("Importi Importati: $i/$totImporti  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }
                /*
                 * Aggiorno il codice del fascicolo
                 */
                $oldImponum = $proimpo_rec['IMPONUM'];
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $oldImponum . "'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $oldImponum);
                    return false;
                }
                $proimpo_rec['IMPONUM'] = $proges_migra_rec['GESNUM_DEST'];
                $proimpo_rec['IMPOCOD'] = $proimpo_rec['IMPOCOD'] + 50;

                /*
                 * insert importo
                 */
                $oldRowid = $proimpo_rec['ROWID'];
                unset($proimpo_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PROIMPO', 'ROWID', $proimpo_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Importo fallito per la pratica $oldImponum");
                    $this->logERR(print_r($proimpo_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                if (!$this->saveProimpoMigra($oldRowid, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Importi Importati: $i/$totImporti  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Importi Importati: $i/$totImporti  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        /*
         * Conciliazioni
         * 
         */

        $this->log("Inizio Importazione Pagamenti Fascioli: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PROCONCILIAZIONE WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND IMPONUM = '$anno$numero'";
            } else {
                $sql .= " AND IMPONUM LIKE '$anno%'";
            }
        }
        $proconciliazione_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$proconciliazione_tab) {
            $this->logERR("Pagamenti non trovati.");
            return false;
        }
        $totConciliazioni = count($proconciliazione_tab);
        $this->log("Estratti $totConciliazioni Pagamenti Fascicoli", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($proconciliazione_tab as $proconciliazione_rec) {
                /*
                 * CONTROLLI SU PRAM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PROCONCILIAZIONE_MIGRA WHERE ROWID_ORIG={$proconciliazione_rec['ROWID']}";
                $proconciliazione_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($proconciliazione_migra_rec) {
                    $skip++;
                    $this->log("Pagamenti Importati: $i/$totConciliazioni  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }
                /*
                 * Aggiorno il codice del fascicolo
                 */
                $oldImponum = $proconciliazione_rec['IMPONUM'];
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $oldImponum . "'", false);
                if (!$proges_migra_rec) {
                    $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $oldImponum);
                    continue;
                    //return false;
                }
                $proconciliazione_rec['IMPONUM'] = $proges_migra_rec['GESNUM_DEST'];
                /*
                 * insert importo
                 */
                $oldRowid = $proconciliazione_rec['ROWID'];
                unset($proconciliazione_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PROCONCILIAZIONE', 'ROWID', $proconciliazione_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Pagamento fallito per la pratica $oldImponum");
                    $this->logERR(print_r($proconciliazione_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->PRAM_DEST->getLastId();
                if (!$this->saveProconciliazioneMigra($oldRowid, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Pagamenti Importati: $i/$totConciliazioni  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Pagamenti Importati: $i/$totConciliazioni  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        return true;
    }

    private function ImportComunicazioni() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Comunicazioni: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PRACOM WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND COMNUM = '$anno$numero'";
            } else {
                $sql .= " AND COMNUM LIKE '$anno%'";
            }
        }

        $pracom_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$pracom_tab) {
            $this->logERR("Comunicazioni non trovate.");
            return false;
        }
        $totCom = count($pracom_tab);
        $this->log("Estratti $totCom Comunicazioni", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($pracom_tab as $pracom_rec) {
                /*
                 * CONTROLLI SU PRACOM_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRACOM_MIGRA WHERE ROWID_ORIG = {$pracom_rec['ROWID']}";
                $pracom_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($pracom_migra_rec) {
                    $skip++;
                    $this->log("Comunicazioni Importate: $i/$totCom  Saltate: $skip", "  ", true, false, false, true);
                    continue;
                }
                $oldRowid = $pracom_rec['ROWID'];
                $oldCompak = $pracom_rec['COMPAK'];

                /*
                 * Aggiorno Numero Fascicolo
                 */
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $pracom_rec['COMNUM'] . "'", false);
                if (!$proges_migra_rec) {
                    $this->log("Record PROGES_MIGRA non trovato per la pratica " . $pracom_rec['COMNUM'] . " Passo: $oldCompak", "WR", true, true, true);
                    continue;
                }
                $pracom_rec['COMNUM'] = $proges_migra_rec['GESNUM_DEST'];

                /*
                 * Aggiorno Chiave Passo
                 */
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $pracom_rec['COMPAK'] . "'", false);
                if (!$propas_migra_rec) {
                    $this->log("Chiave Passo COMPAK  {$pracom_rec['COMPAK']} non trovata nei passi importati. Creo Sostitutiva", "WR", true, true, true);
                    $pracom_rec['COMPAK'] = $this->praLib->PropakGenerator($pracom_rec['COMNUM']);
                } else {
                    $pracom_rec['COMPAK'] = $propas_migra_rec['PROPAK_DEST'];
                }

                /*
                 * insert COMUNICAZIONI
                 */
                unset($pracom_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRACOM', 'ROWID', $pracom_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Comunicazione fallito per il passo $oldCompak");
                    $this->logERR(print_r($pracom_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $newRowid = $this->PRAM_DEST->getLastId();

                /*
                 * Save PRACOM_MIGRA
                 */
                if (!$this->savePracomMigra($newRowid, $oldRowid, $pracom_rec['COMRIF'])) {
                    return false;
                }
                $i++;
                $this->log("Comunicazioni Importate: $i/$totCom  Saltate: $skip", "  ", true, false, false, true);
            }
            $this->log("Comunicazioni Importate: $i/$totCom  Saltate: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        /*
         * Secondo ciclo per sistemare campo COMRIF
         */
        $this->log("Inizio Allineamento Riferimenti Comunicazione: Anno = $anno Numero = $numero", "  ", true);
        $r = 0;
        $pracom_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PRACOM_MIGRA WHERE COMRIF_ORIG <> 0", true);
        $totComrif = count($pracom_migra_tab);
        foreach ($pracom_migra_tab as $pracom_migra_rec) {

            /*
             * Rileggo il PRACOM DESTINO
             */
            $pracom_rec = ItaDB::DBSQLSelect($this->PRAM_DEST, "SELECT * FROM PRACOM WHERE ROWID = " . $pracom_migra_rec['ROWID_DEST'], false);
            if (!$pracom_rec) {
                $this->logERR("Record PRACOM_MIGRA non trovato per la comunicazione con ROWID " . $pracom_migra_rec['ROWID_DEST']);
                return false;
            }

            /*
             * Aggiorno Rowid Riferimento Comunicazione in Partenza
             */
            $pracom_migra_rec_orig = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PRACOM_MIGRA WHERE ROWID_ORIG = " . $pracom_migra_rec['COMRIF_ORIG'], false);
            if (!$pracom_migra_rec_orig) {
                $this->logERR("Record PRACOM_MIGRA non trovato per la comunicazione con ROWID " . $pracom_migra_rec['COMRIF_ORIG']);
                return false;
            }
            $pracom_rec['COMRIF'] = $pracom_migra_rec_orig['ROWID_DEST'];
            try {
                ItaDb::DBUpdate($this->PRAM_DEST, 'PRACOM', 'ROWID', $pracom_rec);
            } catch (Exception $exc) {
                $this->logERR("Errore Aggiornamento campo COMRIF da " . $pracom_migra_rec['COMRIF_ORIG'] . " a " . $pracom_migra_rec_orig['ROWID_DEST']);
                $this->logERR(print_r($pracom_rec, true) . "\n" . $exc->getMessage());
                return false;
            }

            $pracom_migra_rec['COMRIF_DEST'] = $pracom_migra_rec_orig['ROWID_DEST'];
            try {
                ItaDb::DBUpdate($this->PRAM_MIGRA, 'PRACOM_MIGRA', 'ROW_ID', $pracom_migra_rec);
            } catch (Exception $exc) {
                $this->logERR("Errore Aggiornamento campo COMRIF_DEST da " . $pracom_migra_rec['COMRIF_ORIG'] . " a " . $pracom_migra_rec_orig['ROWID_DEST']);
                $this->logERR(print_r($pracom_migra_rec, true) . "\n" . $exc->getMessage());
                return false;
            }

            $r++;
            $this->log("Riferimenti a Comunicazioni allieneati: $r/$totComrif", "  ", true, false, false, true);
        }
        $this->log("Riferimenti a Comunicazioni allineati: $r/$totComrif", "OK", true, true, true);
        
        /*
         * INSERISCO I DESTINATARI
         */
        $this->log("Inizio Importazione Destinatari: Anno = $anno Numero = $numero", "  ", true);

        $sqlDest = "SELECT * FROM PRAMITDEST WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sqlDest .= " AND KEYPASSO LIKE '$anno$numero%'";
            } else {
                $sqlDest .= " AND KEYPASSO LIKE '$anno%'";
            }
        }

        $pramitdest_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sqlDest, true);
        if (!$pramitdest_tab) {
            $this->logERR("Mitt./Dest. non trovati.");
            return false;
        }

        $totDest = count($pramitdest_tab);
        $this->log("Estratti $totDest Destinatari", "  ", true);
        $i2 = 0;
        $skip2 = 0;
        try {
            foreach ($pramitdest_tab as $pramitdest_rec) {

                /*
                 * CONTROLLI SU PRAMITDEST_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRAMITDEST_MIGRA WHERE ROWID_ORIG = {$pramitdest_rec['ROWID']}";
                $pramitdest_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($pramitdest_migra_rec) {
                    $skip2++;
                    $this->log("Destinatari Importati: $i2/$totDest  Saltati: $skip2", "  ", true, false, false, true);
                    continue;
                }

                $oldKeypasso = $pramitdest_rec['KEYPASSO'];
                $oldPronum = substr($oldKeypasso, 0, 10);
                $oldRowid = $pramitdest_rec['ROWID'];

                /*
                 * Aggiorno Chiave Passo
                 */
                $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $pramitdest_rec['KEYPASSO'] . "'", false);
                if (!$propas_migra_rec) {
                    $this->logERR("ATTENZIONE: Chiave Passo KEYPASSO  {$oldKeypasso} non trovata nei passi importati.");
                    $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '$oldPronum'", false);
                    if (!$proges_migra_rec) {
                        $this->log("Record PROGES_MIGRA non trovato per la pratica " . $pracom_rec['COMNUM'], "WR", true, true, true);
                        $pramitdest_rec['KEYPASSO'] = '';
                    } else {
                        $newPronum = $proges_migra_rec['GESNUM_DEST'];
                        $pramitdest_rec['KEYPASSO'] = $this->praLib->PropakGenerator($newPronum);
                        $this->log("Creata chiave passo sostitutiva {$pramitdest_rec['KEYPASSO']}", "OK", true, true, true);
                    }
                } else {
                    $pramitdest_rec['KEYPASSO'] = $propas_migra_rec['PROPAK_DEST'];
                }

                /*
                 * Aggiorno Rowid Riferimento Comunicazione
                 */
                $pracom_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PRACOM_MIGRA WHERE ROWID_ORIG = " . $pramitdest_rec['ROWIDPRACOM'], false);
                if (!$pracom_migra_rec) {
                    $this->log("Chiave comunicazione associata (ROWID ORIGINALE {$pramitdest_rec['ROWIDPRACOM']}) non trovata nelle comunicazioni.", "WR", true, true, true);
                    $pramitdest_rec['ROWIDPRACOM'] = 0;
                } else {
                    $pramitdest_rec['ROWIDPRACOM'] = $pracom_migra_rec['ROWID_DEST'];
                }

                /*
                 * Aggiorno Codice Destinatario
                 */
                if ($pramitdest_rec['CODICE']) {
                    $anamed_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM ANAMED_MIGRA WHERE MEDCOD_ORIG = '" . $pramitdest_rec['CODICE'] . "'", false);
                    if (!$anamed_migra_rec) {
                        $this->log("Nuovo codice destinatario (CODICE ORIGINALE {$pramitdest_rec['CODICE']}) non trovato nei soggetti.", "WR", true, true, true);
                        $pramitdest_rec['CODICE'] = '';
                    } else {
                        $pramitdest_rec['CODICE'] = $anamed_migra_rec['MEDCOD_DEST'];
                    }
                }

                /*
                 * insert DESTINATARI
                 */
                unset($pramitdest_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRAMITDEST', 'ROWID', $pramitdest_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Destinatario " . $pramitdest_rec['NOME'] . " fallito per il passo $oldKeypasso");
                    $this->logERR(print_r($pramitdest_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }

                $newRowid = $this->PRAM_DEST->getLastId();

                /*
                 * Save PRAMITDEST_MIGRA
                 */
                if (!$this->savePramitdestMigra($oldRowid, $newRowid)) {
                    return false;
                }
                $i2++;
                $this->log("Destinatari Importati: $i2/$totDest  Saltati: $skip2", "  ", true, false, false, true);
            }
            $this->log("Destinatari Importati: $i2/$totDest  Saltati: $skip2", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        return true;
    }

    private function ImportaMailArchivio() {
        $this->log("Inizio Importazione Archivio Mail", "  ", true);

        $sql = "SELECT * FROM MAIL_ARCHIVIO";

        $mail_archivio_tab = ItaDB::DBSQLSelect($this->ITALWEB_SORG, $sql, true);
        if (!$mail_archivio_tab) {
            $this->logERR("Mail in archivio non trovate");
            return false;
        }
        $totMail = count($mail_archivio_tab);
        $this->log("Estratte $totMail Mail In Archivio", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($mail_archivio_tab as $mail_archivio_rec) {
                $oldRowid = $mail_archivio_rec['ROWID'];

                /*
                 * CONTROLLI SU MAIL_ARCHIVIO_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM MAIL_ARCHIVIO_MIGRA WHERE ROWID_ORIG={$mail_archivio_rec['ROWID']}";
                $mail_archivio_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($mail_archivio_migra_rec) {
                    $skip++;
                    $this->log("Mail in Archivio importate: $i/$totMail  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }


                /*
                 * insert MAIL
                 */
                unset($mail_archivio_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->ITALWEB_DEST, 'MAIL_ARCHIVIO', 'ROWID', $mail_archivio_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento mail con id " . $mail_archivio_rec['IDMAIL'] . " fallito.");
                    $this->logERR(print_r($mail_archivio_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }
                $lastId = $this->ITALWEB_DEST->getLastId();
                if (!$this->saveMailArchivioMigra($oldRowid, $lastId)) {
                    return false;
                }
                $i++;
                $this->log("Mail in Archivio importate: $i/$totMail  Saltate: $skip", "  ", true, false, false, true);
            }
            $this->log("Mail in Archivio importate: $i/$totMail  Saltate: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }

        return true;
    }

    private function ImportMail() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Mail: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PRAMAIL WHERE TIPOMAIL<>'KEYUPL'";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND (GESNUM = '$anno$numero' OR COMPAK LIKE '$anno$numero%')";
            } else {
                $sql .= " AND (GESNUM LIKE '$anno%' OR COMPAK LIKE '$anno%')";
            }
        }

        $mail_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$mail_tab) {
            $this->logERR("Comunicazioni non trovate.");
            return false;
        }

        $totMail = count($mail_tab);
        $this->log("Estratte $totMail Mail", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($mail_tab as $mail_rec) {
                $msg = "";

                $oldRowid = $mail_rec['ROWID'];

                /*
                 * CONTROLLI SU PRAMAIL_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRAMAIL_MIGRA WHERE ROWID_ORIG = {$mail_rec['ROWID']}";
                $pramail_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($pramail_migra_rec) {
                    $skip++;
                    $this->log("Mail Importate: $i/$totMail  Saltate: $skip", "  ", true, false, false, true);
                    continue;
                }

                /*
                 * Aggiorno il codice del fascicolo se valorizzato
                 */
                if ($mail_rec['GESNUM']) {
                    $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $mail_rec['GESNUM'] . "'", false);
                    if (!$proges_migra_rec) {
                        $this->logERR("Record PROGES_MIGRA non trovato per la pratica " . $mail_rec['GESNUM']);
                        return false;
                    }
                    $mail_rec['GESNUM'] = $proges_migra_rec['GESNUM_DEST'];
                    $msg = "pratica " . $mail_rec['GESNUM'];
                }

                /*
                 * Aggiorno la chiave del passo se valorizzato
                 */
                if ($mail_rec['COMPAK']) {
                    $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $mail_rec['COMPAK'] . "'", false);
                    if (!$propas_migra_rec) {
                        $this->logERR("Record PROPAS_MIGRA non trovato per il passo " . $mail_rec['COMPAK']);
                        return false;
                    }
                    $mail_rec['COMPAK'] = $propas_migra_rec['PROPAK_DEST'];
                    if ($mail_rec['PROPAK']) {
                        $mail_rec['PROPAK'] = $propas_migra_rec['PROPAK_DEST'];
                    }
                    $msg = "passo " . $mail_rec['COMPAK'];
                }

                /*
                 * Aggiorno il campo ROWIDARCHIVIO
                 */
                $mail_archivio_rec_dest = ItaDB::DBSQLSelect($this->ITALWEB_DEST, "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL = '" . $mail_rec['IDMAIL'] . "'", false);
                if (!$mail_archivio_rec_dest) {
                    $this->logERR("Record MAIL_ARCHIVO DESTINO non trovato per la mail " . $mail_rec['IDMAIL']);
                    return false;
                }
                $mail_rec['ROWIDARCHIVIO'] = $mail_archivio_rec_dest['ROWID'];

                /*
                 * insert MAIL
                 */
                unset($mail_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRAMAIL', 'ROWID', $mail_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Mail fallito $msg");
                    $this->logERR(print_r($mail_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }

                $newRowid = $this->PRAM_DEST->getLastId();

                /*
                 * Save PRAMAIL_MIGRA
                 */
                if (!$this->savePramailMigra($oldRowid, $newRowid)) {
                    return false;
                }
                $i++;
                $this->log("Mail Importate: $i/$totMail  Saltate: $skip", "  ", true, false, false, true);
            }
            $this->log("Mail Importate: $i/$totMail  Saltate: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        return true;
    }

    private function ImportDatiAggiuntivi() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Dati Aggiuntivi: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT * FROM PRODAG WHERE DAGNUM<>''";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND DAGNUM = '$anno$numero'";
            } else {
                $sql .= " AND DAGNUM LIKE '$anno%'";
            }
        }

        $prodag_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$prodag_tab) {
            $this->logERR("Dati Aggiuntivi non trovati.");
            return false;
        }

        $totDatiAgg = count($prodag_tab);
        $this->log("Estratti $totDatiAgg Dati Aggiuntivi", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($prodag_tab as $prodag_rec) {
                $oldDagpak = $prodag_rec['DAGPAK'];
                $oldRowid = $prodag_rec['ROWID'];

                /*
                 * CONTROLLI SU PRODAG_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PRODAG_MIGRA WHERE ROWID_ORIG = {$prodag_rec['ROWID']}";
                $prodag_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($prodag_migra_rec) {
                    $skip++;
                    $this->log("Dati Aggiuntivi Importati: $i/$totDatiAgg  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }

                /*
                 * Aggiorno il campo DAGNUM
                 */
                $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '" . $prodag_rec['DAGNUM'] . "'", false);
                if (!$proges_migra_rec) {
                    $this->log("Record PROGES_MIGRA non trovato per il Fascicolo n. " . $prodag_rec['DAGNUM'], "WR", true, true, true);
                    continue;
//                    $this->logERR("Record PROGES_MIGRA non trovato per il Fascicolo n. " . $prodag_rec['DAGNUM']);
//                    return false;
                }
                $prodag_rec['DAGNUM'] = $proges_migra_rec['GESNUM_DEST'];

                /*
                 * Aggiorno i campi DAGPAK e DAGSET
                 */
                if (strlen($prodag_rec['DAGPAK']) == 10) {
                    $prodag_rec['DAGPAK'] = $proges_migra_rec['GESNUM_DEST'];
                } elseif (strlen($prodag_rec['DAGPAK']) == 22) {
                    $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '" . $prodag_rec['DAGPAK'] . "'", false);
                    if (!$propas_migra_rec) {
                        $this->log("Record PROPAS_MIGRA non trovato per il passo " . $prodag_rec['DAGPAK'], "WR", true, true, true);
                        continue;
//                        $this->logERR("Record PROPAS_MIGRA non trovato per il passo " . $prodag_rec['DAGPAK']);
//                        return false;
                    }
                    $prodag_rec['DAGPAK'] = $propas_migra_rec['PROPAK_DEST'];
                }
                $prodag_rec['DAGSET'] = $prodag_rec['DAGPAK'] . substr($prodag_rec['DAGSET'], -3);

                /*
                 * Inserisco Dati Aggiuntivi
                 */
                unset($prodag_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PRODAG', 'ROWID', $prodag_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento fallito per il Dato Aggiuntivi fallito con chiave $oldDagpak");
                    $this->logERR(print_r($prodag_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }

                $newRowid = $this->PRAM_DEST->getLastId();

                /*
                 * Save PRODAG_MIGRA
                 */
                if (!$this->saveProdagMigra($oldRowid, $newRowid)) {
                    return false;
                }
                $i++;
                $this->log("Dati Aggiuntivi Importati: $i/$totDatiAgg  Saltate: $skip", "  ", true, false, false, true);
            }
            $this->log("Dati Aggiuntivi Importati: $i/$totDatiAgg  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        return true;
    }

    private function ImportAllegati() {
        $numero = $this->Numero;
        $anno = $this->Anno;
        $this->log("Inizio Importazione Allegati: Anno = $anno Numero = $numero", "  ", true);

        $sql = "SELECT ROWID FROM PASDOC WHERE 1";
        if ($anno) {
            if ($numero) {
                $numero = str_pad($numero, 6, 0, STR_PAD_RIGHT);
                $sql .= " AND PASKEY LIKE '$anno$numero%'";
            } else {
                $sql .= " AND PASKEY LIKE '$anno%'";
            }
        }
        $sql .= " ORDER BY ROWID";
        $pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_SORG, $sql, true);
        if (!$pasdoc_tab) {
            $this->logERR("Allegati non trovati.");
            return false;
        }

        $totAllegati = count($pasdoc_tab);
        $this->log("Estratti $totAllegati Allegati", "  ", true);
        $i = 0;
        $skip = 0;
        try {
            foreach ($pasdoc_tab as $pasdoc_rowid_rec) {

                $pasdoc_rec = ItaDB::DBSQLSelect($this->PRAM_SORG, "SELECT * FROM PASDOC WHERE ROWID={$pasdoc_rowid_rec['ROWID']}", false);
                $oldPaskey = $pasdoc_rec['PASKEY'];
                $oldRowid = $pasdoc_rec['ROWID'];

                /*
                 * CONTROLLI SU PASDOC_MIGRA
                 */
                $sqlCheck = "SELECT ROWID_DEST FROM PASDOC_MIGRA WHERE ROWID_ORIG = {$pasdoc_rec['ROWID']}";
                $pasdoc_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sqlCheck, false);
                if ($pasdoc_migra_rec) {
                    $skip++;
                    $this->log("Allegati Importati: $i/$totAllegati  Saltati: $skip", "  ", true, false, false, true);
                    continue;
                }

                /*
                 * Aggiorno PASKEY
                 */
                if (strlen($oldPaskey) == 10) {
                    $tipo = "PROGES";
                    $proges_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROGES_MIGRA WHERE GESNUM_ORIG = '$oldPaskey'", false);
                    if (!$proges_migra_rec) {
                        $this->logERR("Record PROGES_MIGRA non trovato per l'allegato con paskey $oldPaskey");
                        return false;
                    }
                    $pasdoc_rec['PASKEY'] = $proges_migra_rec['GESNUM_DEST'];
                }if (strlen($oldPaskey) == 22) {
                    $tipo = "PASSO";
                    $propas_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PROPAS_MIGRA WHERE PROPAK_ORIG = '$oldPaskey'", false);
                    if (!$propas_migra_rec) {

                        $this->logERR("Record PROPAS_MIGRA non trovato per l'allegato con paskey $oldPaskey");
                        return false;
                    }
                    $pasdoc_rec['PASKEY'] = $propas_migra_rec['PROPAK_DEST'];
                }


                $pathSorg = $this->praLib->SetDirectoryPratiche(substr($oldPaskey, 0, 4), $oldPaskey, $tipo, false, $this->ditta_sorgente);
                if (!is_dir($pathSorg)) {
                    $this->log("Cartella Sorgente $pathSorg non trovata Continuo....", "WR", true, true, true);
                    //$this->logERR("Cartella Sorgente $pathSorg non trovata");
                    continue;
                }

                $pathDest = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec['PASKEY'], 0, 4), $pasdoc_rec['PASKEY'], $tipo, true, $this->ditta_destino);
                if (!is_dir($pathDest)) {
                    $this->logERR("Cartella Destino $pathDest non trovata");
                    return false;
                }

                if (!file_exists($pathSorg . "/" . $pasdoc_rec['PASFIL'])) {
                    $this->log("Allegato ( ROWID $oldRowid ) non esiste: " . $pathSorg . "/" . $pasdoc_rec['PASFIL'] . " Continuo)", "WR", true, true, true);
                    continue;
                } else {
                    if (!copy($pathSorg . "/" . $pasdoc_rec['PASFIL'], $pathDest . "/" . $pasdoc_rec['PASFIL'])) {
                        $this->logERR("Errore copia Allegato da " . $pathSorg . "/" . $pasdoc_rec['PASFIL'] . " a " . $pathDest . "/" . $pasdoc_rec['PASFIL']);
                        return false;
                    }

                    /*
                     * Se è un TESTOBASE, copio anche i p7m e pdf relativi
                     */
                    if ($pasdoc_rec['PASCLA'] == 'TESTOBASE') {
                        $baseName = pathinfo($pasdoc_rec['PASFIL'], PATHINFO_FILENAME);
                        if (file_exists($pathSorg . "/" . $baseName . ".pdf.p7m")) {
                            if (!copy($pathSorg . "/" . $baseName . ".pdf.p7m", $pathDest . "/" . $baseName . ".pdf.p7m")) {
                                $this->logERR("Errore copia del file firmato " . $baseName . ".pdf.p7m");
                                return false;
                            }
                        }
                        if (file_exists($pathSorg . "/" . $baseName . ".pdf")) {
                            if (!copy($pathSorg . "/" . $baseName . ".pdf", $pathDest . "/" . $baseName . ".pdf")) {
                                $this->logERR("Errore copia del file pdf " . $baseName . ".pdf");
                                return false;
                            }
                        }
                    }
                }

                /*
                 * Sistemazione PASCLA in caso di Allegati do Comunicazione in Arrivo
                 */
                if (strpos($pasdoc_rec['PASCLA'], 'COMUNICAZIONE') !== false) {
                    $arrCla = explode(" ", $pasdoc_rec['PASCLA']);
                    $pracom_migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM PRACOM_MIGRA WHERE ROWID_ORIG = '" . $arrCla[1] . "'", false);
                    if (!$pracom_migra_rec) {
                        $this->log("Record tabella PRACOM_MIGRA ( ROWID {$arrCla[1]} ) non esiste", "WR", true, true, true);
                    } else {
                        $pasdoc_rec['PASCLA'] = 'COMUNICAZIONE ' . $pracom_migra_rec['ROWID_DEST'];
                    }
                }

                /*
                 * Aggiorno il campo PASPRTROWID se c'è la CLASSE
                 */
                if ($pasdoc_rec['PASPRTCLASS']) {
                    switch ($pasdoc_rec['PASPRTCLASS']) {
                        case "PROGES":
                            $table = "PROGES_MIGRA";
                            break;
                        case "PRACOM":
                            $table = "PRACOM_MIGRA";
                            break;
                    }

                    $migra_rec = ItaDB::DBSQLSelect($this->PRAM_MIGRA, "SELECT * FROM $table WHERE ROWID_ORIG = '" . $pasdoc_rec['PASPRTROWID'] . "'", false);
                    if (!$migra_rec) {
                        $this->log("Record tabella $table ( ROWID {$pasdoc_rec['PASPRTROWID']} ) non esiste", "WR", true, true, true);
                    } else {
                        $pasdoc_rec['PASPRTROWID'] = $migra_rec['ROWID_DEST'];
                    }
                }



                /*
                 * Inserisco Allegato
                 */
                unset($pasdoc_rec['ROWID']);
                try {
                    ItaDB::DBInsert($this->PRAM_DEST, 'PASDOC', 'ROWID', $pasdoc_rec);
                } catch (Exception $exc) {
                    $this->logERR("Inserimento Allegato fallito con chiave $oldPaskey");
                    $this->logERR(print_r($pasdoc_rec, true) . "\n" . $exc->getMessage());
                    return false;
                }

                $newRowid = $this->PRAM_DEST->getLastId();

                /*
                 * Save PASDOC_MIGRA
                 */
                if (!$this->savePasdocMigra($oldRowid, $newRowid)) {
                    return false;
                }
                $i++;
                $this->log("Allegati Importati: $i/$totAllegati  Saltati: $skip", "  ", true, false, false, true);
            }
            $this->log("Allegti Importati: $i/$totAllegati  Saltati: $skip", "OK", true, true, true);
        } catch (Exception $exc) {
            $this->logERR(print_r($exc->getTraceAsString(), true));
        }
        return true;
    }

    private function saveProgesMigra($gesnum_dest, $gesnum_orig, $rowid_dest, $rowid_orig) {
        $proges_migra = array(
            'GESNUM_DEST' => $gesnum_dest,
            'GESNUM_ORIG' => $gesnum_orig,
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PROGES_MIGRA', 'ROW_ID', $proges_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PROGES_MIGRA Fallito\n");
            $this->logERR(print_r($proges_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePracomMigra($rowid_dest, $rowid_orig, $comrif_orig) {
        $pracom_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
            'COMRIF_ORIG' => $comrif_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRACOM_MIGRA', 'ROW_ID', $pracom_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRACOM_MIGRA Fallito\n");
            $this->logERR(print_r($pracom_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveAnamedMigra($medcod_orig, $medcod_dest) {
        $anamed_migra = array(
            'MEDCOD_DEST' => $medcod_dest,
            'MEDCOD_ORIG' => $medcod_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'ANAMED_MIGRA', 'ROW_ID', $anamed_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento ANAMED_MIGRA Fallito\n");
            $this->logERR(print_r($anamed_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePropasMigra($propas_rec, $rowid_dest, $new_propas_rec) {
        $propas_migra = array(
            'PROPAK_DEST' => $new_propas_rec['PROPAK'],
            'PROPAK_ORIG' => $propas_rec['PROPAK'],
            'PROVPA_ORIG' => $new_propas_rec['PROVPA'],
            'PROVPN_ORIG' => $new_propas_rec['PROVPN'],
            'PROCTP_ORIG' => $new_propas_rec['PROCTP'],
            'PROKPRE_ORIG' => $new_propas_rec['PROKPRE'],
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $propas_rec['ROWID'],
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PROPAS_MIGRA', 'ROW_ID', $propas_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PROPAS_MIGRA Fallito\n");
            $this->logERR(print_r($propas_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveAnadesMigra($anades_rec, $rowid_dest) {
        $anades_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $anades_rec['ROWID'],
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'ANADES_MIGRA', 'ROW_ID', $anades_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento ANADES_MIGRA Fallito\n");
            $this->logERR(print_r($anades_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePraimmMigra($immobili_rec, $rowid_dest) {
        $praimm_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $immobili_rec['ROWID'],
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRAIMM_MIGRA', 'ROW_ID', $praimm_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRAIMM_MIGRA Fallito\n");
            $this->logERR(print_r($praimm_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePrastaMigra($prasta_rec, $rowid_dest) {
        $prasta_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $prasta_rec['ROWID'],
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRASTA_MIGRA', 'ROW_ID', $prasta_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRAIMM_MIGRA Fallito\n");
            $this->logERR(print_r($prasta_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveNoteMigra($rowid_orig, $rowid_dest) {
        $note_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'NOTE_MIGRA', 'ROW_ID', $note_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento NOTE_MIGRA Fallito\n");
            $this->logERR(print_r($note_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveNoteClasMigra($rowid_orig, $rowid_dest) {
        $noteclas_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'NOTECLAS_MIGRA', 'ROW_ID', $noteclas_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento NOTECLAS_MIGRA Fallito\n");
            $this->logERR(print_r($noteclas_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveProimpoMigra($rowid_orig, $rowid_dest) {
        $proimpo_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PROIMPO_MIGRA', 'ROW_ID', $proimpo_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PROIMPO_MIGRA Fallito\n");
            $this->logERR(print_r($proimpo_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveProconciliazioneMigra($rowid_orig, $rowid_dest) {
        $proconciliazione_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PROCONCILIAZIONE_MIGRA', 'ROW_ID', $proconciliazione_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PROCONCILIAZIONE_MIGRA Fallito\n");
            $this->logERR(print_r($proconciliazione_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePramitdestMigra($rowid_orig, $rowid_dest) {
        $pramitdest_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRAMITDEST_MIGRA', 'ROW_ID', $pramitdest_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRAMITDEST_MIGRA Fallito\n");
            $this->logERR(print_r($pramitdest_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePramailMigra($rowid_orig, $rowid_dest) {
        $pramail_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRAMAIL_MIGRA', 'ROW_ID', $pramail_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRAMAIL_MIGRA Fallito\n");
            $this->logERR(print_r($pramail_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveProdagMigra($rowid_orig, $rowid_dest) {
        $prodag_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PRODAG_MIGRA', 'ROW_ID', $prodag_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PRODAG_MIGRA Fallito\n");
            $this->logERR(print_r($prodag_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function savePasdocMigra($rowid_orig, $rowid_dest) {
        $pasdoc_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'PASDOC_MIGRA', 'ROW_ID', $pasdoc_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento PASDOC_MIGRA Fallito\n");
            $this->logERR(print_r($pasdoc_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function saveMailArchivioMigra($rowid_orig, $rowid_dest) {
        $mail_archivio_migra = array(
            'ROWID_DEST' => $rowid_dest,
            'ROWID_ORIG' => $rowid_orig,
        );
        try {
            ItaDB::DBInsert($this->PRAM_MIGRA, 'MAIL_ARCHIVIO_MIGRA', 'ROW_ID', $mail_archivio_migra);
        } catch (Exception $exc) {
            $this->logERR("Inserimento MAIL_ARCHIVIO_MIGRA Fallito\n");
            $this->logERR(print_r($mail_archivio_migra, true) . "\n" . $exc->getMessage());
            return false;
        }
        return true;
    }

    private function apriDB() {
        /*
         * PRAM_SORG 
         * 
         */
        try {
            $this->PRAM_SORG = ItaDB::DBOpen('PRAM', $this->ditta_sorgente);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->PRAM_SORG->exists()) {
                $this->log("PRAM" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log("PRAM" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
            return false;
        }
        $this->log("PRAM" . $this->ditta_sorgente . " Aperto", 'OK', true);

        /*
         * PRAM_DEST
         * 
         * 
         */
        try {
            $this->PRAM_DEST = ItaDB::DBOpen('PRAM', $this->ditta_destino);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->PRAM_DEST->exists()) {
                $this->log("PRAM" . $this->ditta_destino . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        $this->log("PRAM" . $this->ditta_destino . " Aperto", 'OK', true);

        /*
         * PROT_SORG
         * 
         */
        try {
            $this->PROT_SORG = ItaDB::DBOpen('PROT', $this->ditta_sorgente);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->PROT_SORG->exists()) {
                $this->log("PROT" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log("PROT" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
            return false;
        }
        $this->log("PROT" . $this->ditta_sorgente . " Aperto", 'OK', true);

        /*
         * PROT_DEST
         * 
         */
        try {
            $this->PROT_DEST = ItaDB::DBOpen('PROT', $this->ditta_destino);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->PROT_DEST->exists()) {
                $this->log("PROT" . $this->ditta_destino . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        $this->log("PROT" . $this->ditta_destino . " Aperto", 'OK', true);

        /*
         * ITALWEB_SORG
         * 
         */
        try {
            $this->ITALWEB_SORG = ItaDB::DBOpen('ITALWEB', $this->ditta_sorgente);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->ITALWEB_SORG->exists()) {
                $this->log("ITALWEB" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log("ITALWEB" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
            return false;
        }
        $this->log("ITALWEB" . $this->ditta_sorgente . " Aperto", 'OK', true);

        /*
         * ITALWEB_DEST
         * 
         */
        try {
            $this->ITALWEB_DEST = ItaDB::DBOpen('ITALWEB', $this->ditta_destino);
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        try {
            if (!$this->ITALWEB_DEST->exists()) {
                $this->log("ITALWEB" . $this->ditta_destino . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            return false;
        }
        $this->log("ITALWEB" . $this->ditta_destino . " Aperto", 'OK', true);

        /*
         * PRAM_MIGRA
         *          * 
         */
        $this->PRAM_MIGRA = ItaDB::DBOpen('PRAM_MIGRA', $this->ditta_destino);
        try {
            if (!$this->PRAM_MIGRA->exists()) {
                $this->log("PRAM_MIGRA" . $this->ditta_sorgente . " Non Esiste", "ER", true, true, true, false);
                return false;
            }
        } catch (Exception $exc) {
            $this->log($exc->getMessage(), "ER", true, true, true, false);
            $this->errCode = -1;
            $this->errMessage = $exc->getMessage();
            return false;
        }
        $this->log("PRAM_MIGRA" . $this->ditta_sorgente . " Aperto", 'OK', true);
        return true;
    }

    public function lanciaUnificazione() {
        $this->logFile = $this->dirLog . DIRECTORY_SEPARATOR . "unificazione-fascicoli-" . date('Ymd-His') . '.txt';

        $this->log("Inizio Unificazione.", '  ', true);
        $this->log("Log File: " . $this->logFile, '  ', true);

        $this->errCode = 0;
        $this->errMessage = '';
        $this->message = array();

        /*
         * Apro i DB
         * 
         */
        if (!$this->apriDB()) {
            return false;
        }

        /*
         * 
         * Carica Risorse
         * 
         */
        if (!$this->caricaRisorse()) {
            return false;
        }

        /*
         * Carico Testata Fascicolo
         * 
         */
        if ($this->ImportaFascicoli == 1) {
            if (!$this->ImportFascicoli()) {
                return false;
            }
        }
        /*
         * Importazione MITTENTI/DESTINATARI
         */
        if ($this->ImportaMittDest == 1) {
            if (!$this->ImportMittentiDestinatari()) {
                return false;
            }
        }

        /*
         * Importazione PASSI
         */
        if ($this->ImportaPassi == 1) {
            if (!$this->ImportPassi()) {
                return false;
            }
        }

        /*
         * Importazione SOGGETTI
         */
        if ($this->ImportaSoggetti == 1) {
            if (!$this->ImportSoggetti()) {
                return false;
            }
        }

        /*
         * Importazione IMMOBILI
         */
        if ($this->ImportaImmobili == 1) {
            if (!$this->ImportImmobili()) {
                return false;
            }
        }

        /*
         * Importazione STATI
         */
        if ($this->ImportaStati == 1) {
            if (!$this->ImportStati()) {
                return false;
            }
        }

        /*
         * Importazione NOTE
         */
        if ($this->ImportaNote == 1) {
            if (!$this->ImportNote()) {
                return false;
            }
        }

        /*
         * Importazione NOTE
         */
        if ($this->ImportaPagamenti == 1) {
            if (!$this->ImportaPagamenti()) {
                return false;
            }
        }

        /*
         * Importazione COMUNICAZIONI
         */
        if ($this->ImportaComunicazioni == 1) {
            if (!$this->ImportComunicazioni()) {
                return false;
            }
        }

        /*
         * Importazione MAIL ARCHIVIO
         */
        if ($this->ImportaMailArchivio == 1) {
            if (!$this->ImportaMailArchivio()) {
                return false;
            }
        }

        /*
         * Importazione MAIL
         */
        if ($this->ImportaMail == 1) {
            if (!$this->ImportMail()) {
                return false;
            }
        }

        /*
         * Importazione DATI AGGIUNTIVI
         */
        if ($this->ImportaDatiAgg == 1) {
            if (!$this->ImportDatiAggiuntivi()) {
                return false;
            }
        }

        /*
         * Importazione ALLEGATI
         */
        if ($this->ImportaAllegati == 1) {
            if (!$this->ImportAllegati()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param type $message
     * @param type $mode
     * @param type $systemEcho
     * @param type $serialze
     * @param type $nline
     * @param type $creturn
     */
    private function log($message, $mode = "OK", $systemEcho = false, $serialize = true, $nline = true, $creturn = false) {
        if (!$this->logger) {
            $this->logger = new itaPHPLogger(__CLASS__, false);
            $this->logger->pushFile($this->logFile);
        }

        $time = date("d/m/Y H:i:s");
        $nl = ($nline) ? "\n" : "";
        $cr = ($creturn) ? "\r" : "";
        if ($systemEcho && App::$isCli) {
            Out::systemEcho("[$time][$mode]: $message$nl$cr", true);
        }
        if ($mode == 'ER') {
            $this->errCode = -1;
            $this->errMessage = $message;
        }
        if ($serialize) {
            switch ($mode) {
                case "  ":
                case "OK":
                    $this->logger->info($message);
                    break;
                case "ER":
                    $this->logger->error($message);
                    break;
                default:
                    $this->logger->info($message);
                    break;
            }
        }
    }

    private function logERR($message) {
        $this->log("\n$message", "ER", true, true, true, false);
    }

    private function caricaRisorse() {
        $this->anaspa_migra = array();
        $this->anatsp_migra = array();
        $this->ananom_migra = array();
        $this->doc_documenti_migra = array();

        $sql = "SELECT * FROM ANASPA_MIGRA";
        $anaspa_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sql, true);
        foreach ($anaspa_migra_tab as $anaspa_migra_rec) {
            $this->anaspa_migra[$anaspa_migra_rec['SPACOD_ORIG']] = $anaspa_migra_rec['SPACOD_DEST'];
        }

        $sql = "SELECT * FROM ANATSP_MIGRA";
        $anatsp_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sql, true);
        foreach ($anatsp_migra_tab as $anatsp_migra_rec) {
            $this->anatsp_migra[$anatsp_migra_rec['TSPCOD_ORIG']] = $anatsp_migra_rec['TSPCOD_DEST'];
        }

        $sql = "SELECT * FROM ANANOM_MIGRA";
        $ananom_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sql, true);
        foreach ($ananom_migra_tab as $ananom_migra_rec) {
            $this->ananom_migra[$ananom_migra_rec['NOMRES_ORIG']] = $ananom_migra_rec['NOMRES_DEST'];
        }

        $sql = "SELECT * FROM DOC_DOCUMENTI_MIGRA";
        $doc_documenti_migra_tab = ItaDB::DBSQLSelect($this->PRAM_MIGRA, $sql, true);
        foreach ($doc_documenti_migra_tab as $doc_documenti_migra_rec) {
            $this->doc_documenti_migra[$doc_documenti_migra_rec['CODICE_ORIG']] = $doc_documenti_migra_rec['CODICE_DEST'];
        }

        $this->log("Risorse caricate", 'OK', true);
        return true;
    }

    private function getLastGesnum($currProges_rec) {
        $anno = substr($currProges_rec['GESNUM'], 0, 4);
        $max_proges_rec = ItaDB::DBSQLSelect($this->PRAM_DEST, "SELECT MAX(GESNUM) AS MAX_GESNUM FROM PROGES WHERE GESNUM LIKE '$anno%' ORDER BY GESNUM DESC", false);
        if (intval($max_proges_rec['MAX_GESNUM']) == 0) {
            $max_proges_rec['MAX_GESNUM'] = $anno . '000000';
        }
        $max_gesnum = $max_proges_rec['MAX_GESNUM'];
        return $max_gesnum;
    }

}
