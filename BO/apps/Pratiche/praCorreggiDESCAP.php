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

function praCorreggiDESCAP() {
    $praCorreggiDESCAP = new praCorreggiDESCAP();
    $praCorreggiDESCAP->parseEvent();
    return;
}

class praCorreggiDESCAP extends itaModel {

    public $nameForm = "praCorreggiDESCAP";
    public $praLib;
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
                $this->fileLogSimula = sys_get_temp_dir() . "/praCorreggiDESCAP_simula_" . time() . ".log";
                $this->fileLogPopola = sys_get_temp_dir() . "/praCorreggiDESCAP_popola_" . time() . ".log";
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
                        if (!$this->SimulaDESCAP()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_Simula2':
                        if (!$this->SimulaDESCAP_2()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_Conferma':
                        Out::msgQuestion("ATTENZIONE!", "Vuoi confermare la correzione dei valori del campo DESCAP valorizzati?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPopola', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPopola', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_Conferma2':
                        Out::msgQuestion("ATTENZIONE!", "Vuoi confermare la correzione dei valori del campo DESCAP uguali a 0?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaPopola2', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaPopola2', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaPopola':
                        if (!$this->PopolaDESCAP()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_ConfermaPopola2':
                        if (!$this->PopolaDESCAP_2()) {
                            Out::msgStop("Errore", $this->getErrMessage());
                            break;
                        }
                        break;
                    case $this->nameForm . '_Alter':
                        Out::msgQuestion("ATTENZIONE!", "Vuoi confermare l' alter del campo DESCAP da Double a Varchar (5) in tutti gli enti interessati?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAlter', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAlter', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaAlter':
                        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
                        $tot_enti = count($domains_tab);
                        $eseguito = 0;
                        foreach ($domains_tab as $domains_rec) {
                            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);
                            try {
                                ItaDB::DBSQLExec($pram_db, "ALTER TABLE `ANADES` CHANGE `DESCAP` `DESCAP` VARCHAR(5) NOT NULL;");
                            } catch (Exception $e) {
                                Out::msgStop("Errore", $e->getMessage() . " - ditta: " . $domains_rec['CODICE']);
                                break;
                            }
                            $eseguito++;
                        }
                        Out::msgInfo("Alter DESCAP", "Alter eseguita correttamente su $eseguito di $tot_enti enti");
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

    private function scriviLogSimula($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLogSimula, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLogSimula, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function scriviLogPopola($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLogPopola, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLogPopola, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function PopolaDESCAP() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i gli ANADES con il DESCAP valorizzato
             */
            try {
                $Anades_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM ANADES WHERE DESCAP<>'' AND DESCAP<>'0' AND LENGTH(DESCAP) < 5", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro gli ANADES da aggiornare
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Correzione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogPopola("Inizio Correzione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Anades_tab as $Anades_rec) {
                $vecchioCap = $Anades_rec['DESCAP'];
                $nuovoCap = str_pad($Anades_rec['DESCAP'], 5, "0", STR_PAD_LEFT);
                $Anades_rec['DESCAP'] = $nuovoCap;
                //
                try {
                    $nrow = ItaDB::DBUpdate($pram_db, 'ANADES', 'ROWID', $Anades_rec);
                    if ($nrow == -1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($exc->getMessage() . " - fascicolo n " . $Anades_rec['DESNUM']);
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($exc->getMessage() . " - fascicolo n " . $Anades_rec['DESNUM']);
                    return false;
                }
                $i += 1;
                $this->scriviLogPopola("Pratica N. " . $Anades_rec['DESNUM'] . " | Vecchio CAP = $vecchioCap | Nuovo CAP = $nuovoCap");
            }
            $this->scriviLogPopola("Fine popolamento del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogPopola("---------------------------------------------------------------------");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Popolamento ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati corretti $i DESCAP</b></span>");
            $this->OutMessaggio("------------------------------------------------------------------------");
        }
        Out::openDocument(utiDownload::getUrl("popolamento.log", $this->fileLogPopola));
        return true;
    }

    private function SimulaDESCAP() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i gli ANADES con il DESCAP valorizzato
             */
            try {
                $Anades_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM ANADES WHERE DESCAP<>'' AND DESCAP<>'0' AND LENGTH(DESCAP) < 5", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro gli ANADES da aggiornare
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogSimula("Inizio simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Anades_tab as $Anades_rec) {
                $i += 1;
                $nuovoCap = str_pad($Anades_rec['DESCAP'], 5, "0", STR_PAD_LEFT);
                $this->scriviLogSimula("Pratica N. " . $Anades_rec['DESNUM'] . " | Vecchio CAP = " . $Anades_rec['DESCAP'] . " | Nuovo CAP = $nuovoCap");
            }
            $this->scriviLogSimula("Fine simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogSimula("------------------------------------------------------------------------------");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati trovati $i CAP da allineare a 5</b></span>");
            $this->OutMessaggio("---------------------------------------------------------------------------------");
        }
        Out::openDocument(utiDownload::getUrl("simula.log", $this->fileLogSimula));
        return true;
    }

    private function PopolaDESCAP_2() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i gli ANADES con il DESCAP valorizzato
             */
            try {
                $Anades_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM ANADES WHERE DESCAP = '0'", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro gli ANADES da aggiornare
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Correzione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogPopola("Inizio Correzione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Anades_tab as $Anades_rec) {
                $vecchioCap = $Anades_rec['DESCAP'];
                $Anades_rec['DESCAP'] = "";
                //
                try {
                    $nrow = ItaDB::DBUpdate($pram_db, 'ANADES', 'ROWID', $Anades_rec);
                    if ($nrow == -1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($exc->getMessage() . " - fascicolo n " . $Anades_rec['DESNUM']);
                        return false;
                    }
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($exc->getMessage() . " - fascicolo n " . $Anades_rec['DESNUM']);
                    return false;
                }
                $i += 1;
                $this->scriviLogPopola("Pratica N. " . $Anades_rec['DESNUM'] . " | Vecchio CAP = $vecchioCap | Nuovo CAP = ");
            }
            $this->scriviLogPopola("Fine popolamento del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogPopola("-----------------------------------------------------------------------");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Popolamento ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati corretti $i DESCAP uguali a 0</b></span>");
            $this->OutMessaggio("--------------------------------------------------------------------------");
        }
        Out::openDocument(utiDownload::getUrl("popolamento.log", $this->fileLogPopola));
        return true;
    }

    private function SimulaDESCAP_2() {
        $domains_tab = ItaDB::DBSQLSelect($this->ITALWEBDB, "SELECT * FROM DOMAINS", true);
        foreach ($domains_tab as $domains_rec) {
            $pram_db = ItaDB::DBOpen('PRAM', $domains_rec['CODICE']);

            /*
             *  Mi trovo tutti i gli ANADES con il DESCAP uguale a 0
             */
            try {
                $Anades_tab = ItaDB::DBSQLSelect($pram_db, "SELECT * FROM ANADES WHERE DESCAP = '0'", true);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());
                return false;
            }

            /*
             * Mi scorro gli ANADES da aggiornare
             */
            $this->OutMessaggio("<span style=\"color:green;\"><b>Inizio Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->scriviLogSimula("Inizio simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $i = 0;
            foreach ($Anades_tab as $Anades_rec) {
                $i += 1;
                $nuovoCap = "";
                $this->scriviLogSimula("Pratica N. " . $Anades_rec['DESNUM'] . " | Vecchio CAP = " . $Anades_rec['DESCAP'] . " | Nuovo CAP = $nuovoCap");
            }
            $this->scriviLogSimula("Fine simulazione del " . date('d-m-Y - H.i.s') . " ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE']);
            $this->scriviLogSimula("-------------------------------------------------------------");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Fine Simulazione ente " . $domains_rec['CODICE'] . " - " . $domains_rec['DESCRIZIONE'] . "</b></span>");
            $this->OutMessaggio("<span style=\"color:green;\"><b>Sono stati trovati $i CAP da vuotare</b></span>");
            $this->OutMessaggio("----------------------------------------------------------------");
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
