<?php

/**
 *  Programma Popolamento tabella PROGESSUB dei proc accorpati
 *
 *
 * @category   Library
 * @package    /apps/Pratiche
 * @author     Andrea Bufarini
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    09.02.2017
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praPopolaPROGESSUB() {
    $praPopolaPROGESSUB = new praPopolaPROGESSUB();
    $praPopolaPROGESSUB->parseEvent();
    return;
}

class praPopolaPROGESSUB extends itaModel {

    public $nameForm = "praPopolaPROGESSUB";
    public $praLib;
    //public $PRAM_DB;
    public $ITALWEBDB;
    public $fileLogSimula;
    public $fileLogPopola;
    public $errMessage;
    public $errCode;

    function __construct() {
        parent::__construct();
        try {
            //
            // carico le librerie
            //
            $this->praLib = new praLib();
            //$this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEBDB = $this->praLib->getITALWEBDB();
            $this->fileLogSimula = App::$utente->getKey($this->nameForm . '_fileLogSimula');
            $this->fileLogPopola = App::$utente->getKey($this->nameForm . '_fileLogPopola');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLogSimula', $this->fileLogSimula);
            App::$utente->setKey($this->nameForm . '_fileLogPopola', $this->fileLogPopola);
        }
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $this->fileLogSimula = sys_get_temp_dir() . "/praPopolaPROGESSUB_simula_" . time() . ".log";
                $this->fileLogPopola = sys_get_temp_dir() . "/praPopolaPROGESSUB_popola_" . time() . ".log";
                $this->OutMessaggio("<span style=\"color:dark-green;\">Creato File: <b>" . $this->fileLogSimula . "</b></span>");
                $this->OutMessaggio("<span style=\"color:dark-green;\">Creato File: <b>" . $this->fileLogPopola . "</b></span>");
                $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
                $this->OutMessaggio("<br><span style=\"font-size:1.2em;text-decoration:underline;\"><b>ENTI INTERESSATI</b></span>", false);
                foreach ($domains_tab as $domains_rec) {
                    $this->OutMessaggio("<span style=\"color:dark-green;\">" . $domains_rec["CODICE"] . " - " . $domains_rec["DESCRIZIONE"] . "</span>", false);
                }
                $this->OutMessaggio("<br>", false);
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Simula':
                        if (!$this->SimulaPROGESSUB()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_Conferma':
                        Out::msgQuestion("ATTENZIONE!", "Vuoi confermare il popolamento della tabella PROGESSUB?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPopola', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPopola', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaPopola':
                        if (!$this->PopolaPROGESSUB()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_fileLogSimula');
        App::$utente->removeKey($this->nameForm . '_fileLogPopola');
    }

    private function scriviLogSimula($testo, $flAppend = true, $nl = "\n", $date = true) {
        $dateLog = "";
        if ($date === true) {
            $dateLog = "[" . date("d/m/Y H.i:s") . "] ";
        }
        if ($flAppend) {
            file_put_contents($this->fileLogSimula, $dateLog . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLogSimula, $dateLog . "$testo$nl");
        }
    }

    private function scriviLogPopola($testo, $flAppend = true, $nl = "\n", $date = true) {
        $dateLog = "";
        if ($date === true) {
            $dateLog = "[" . date("d/m/Y H.i:s") . "] ";
        }
        if ($flAppend) {
            file_put_contents($this->fileLogPopola, $dateLog . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLogPopola, $dateLog . "$testo$nl");
        }
    }

    private function PopolaPROGESSUB() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i fascicoli che già non compaiono nella tabela PROGESSUB
             */
            try {
                $Proges_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM PROGES WHERE GESNUM NOT IN (SELECT PRONUM FROM PROGESSUB) ORDER BY GESNUM DESC", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro i fascicoli
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Popolamento ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogPopola("Inizio popolamento del " . date('d-m-Y - H.i.s') . "ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Proges_tab as $Proges_rec) {
                $progessub_rec = array();
                $progessub_rec['PRONUM'] = $Proges_rec['GESNUM'];
                $progessub_rec['RICHIESTA'] = $Proges_rec['GESPRA'];
                $progessub_rec['PROPRO'] = $Proges_rec['GESPRO'];
                $progessub_rec['EVENTO'] = $Proges_rec['GESEVE'];
                $progessub_rec['SPORTELLO'] = $Proges_rec['GESTSP'];
                $progessub_rec['SETTORE'] = $Proges_rec['GESSTT'];
                $progessub_rec['ATTIVITA'] = $Proges_rec['GESATT'];
                $progessub_rec['TIPSEG'] = $Proges_rec['GESSEG'];
                $progessub_rec['PROGRESSIVO'] = 0;
                //
                try {
                    $nrow = ItaDB::DBInsert($pram_db, 'PROGESSUB', 'ROWID', $progessub_rec);
                    if ($nrow != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($exc->getMessage() . " - fascicolo n " . $Proges_rec['PRANUM']);
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($e->getMessage() . " - fascicolo n " . $Proges_rec['PRANUM']);
                    return false;
                }
                $i += 1;
                $this->scriviLogPopola("Pratica N. " . $Proges_rec['GESNUM'] . " | Richiesta on-line = " . $Proges_rec['GESPRA']);
            }
            $this->scriviLogPopola("Fine popolamento del " . date('d-m-Y - H.i.s') . "ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogPopola("-------------------------------------------------------------------------------------------------", true, "\n", false);
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Popolamento ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati inseriti $i fascicoli nella tabella PROGESSUB</b></span>");
            $this->OutMessaggio("----------------------------------------------------------------------------------------------------", false);
        }
        Out::openDocument(utiDownload::getUrl("popolamento.log", $this->fileLogPopola));
        return true;
    }

    private function SimulaPROGESSUB() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i fascicoli che già non compaiono nella tabela PROGESSUB
             */
            try {
                $Proges_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM PROGES WHERE GESNUM NOT IN (SELECT PRONUM FROM PROGESSUB) ORDER BY GESNUM DESC", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro i fascicoli da aggiornare
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogSimula("Inizio simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Proges_tab as $Proges_rec) {
                $i += 1;
                $this->scriviLogSimula("Pratica N. " . $Proges_rec['GESNUM'] . " | Richiesta on-line = " . $Proges_rec['GESPRA']);
            }
            $this->scriviLogSimula("Fine simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogSimula("-------------------------------------------------------------------------------------------------------", true, "\n", false);
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati trovati $i fascicoli da inserire nella tabella PROGESSUB</b></span>");
            $this->OutMessaggio("----------------------------------------------------------------------------------------------------------", false);
        }
        Out::openDocument(utiDownload::getUrl("simula.log", $this->fileLogSimula));
        return true;
    }

    private function OutMessaggio($Messaggio, $data = true) {
        if ($data === true) {
            $html = "<br>" . date('d-m-Y - H.i.s') . " -->" . $Messaggio;
        } else {
            $html = "<br>" . $Messaggio;
        }
        Out::html($this->nameForm . "_divMessaggi", $html, 'append');
    }

}

?>
