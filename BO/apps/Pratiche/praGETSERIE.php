<?php

/*
 * PROGRAMMA DI POPOLAMENTO PER TABELLA PROGES
 * PER NUOVI CAMPI SERIE (SERIECODICE, SERIEANNO, SERIE PROGRESSIVO)
 *  * @category   programma popolamento
 * @package    /apps/Menu
 * @author     Tania Angeloni
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    11.12.2017
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

function praGETSERIE() {
    $intSuapSbt = new praGETSERIE();
    $intSuapSbt->parseEvent();
    return;
}

class praGETSERIE extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $fileLog;
    public $nameForm;
    public $proRic;
    public $proLibSerie;
    public $enti;

    function __construct() {
        parent::__construct();
        try {
            /*
             * carico le librerie
             * 
             */
            $this->nameForm = 'praGETSERIE';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');
            $this->enti = App::$utente->getKey($this->nameForm . '_enti');

            $this->praLib = new praLib();
            //$this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->proRic = new proRic();
            $this->proLibSerie = new proLibSerie();
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
            App::$utente->setKey($this->nameForm . '_enti', $this->enti);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->enti = App::getEnti();
                $this->fileLog = sys_get_temp_dir() . "/praGETSERIE_" . time() . ".log";
                $this->scriviLog("Avvio Programma praGETSERIE");
                Out::html($this->nameForm . "_divInfo", "<br>Benventuno nel programma di popolamento serie! <br> " . "Seleziona la serie con il quale si vuole popolare i fascicoli");
                Out::html($this->nameForm . "_divIstruzione", "<br><br>Per inserire una serie:"
                        . "<br>- Protocollo "
                        . "<br>- Archivi "
                        . "<br>-Serie Archivistiche"
                        . "<br>"
                        . "<br>Enti da elaborare:"
                        . "<pre>" . print_r($this->enti, true) . "</pre>");
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        if ($_POST[$this->nameForm . '_ric_codiceserie'] == 0) {
                            Out::msgStop("Attenzione", "Nessuna tipologia serie selezionata");
                            break;
                        }
                        $this->importafascicoli();
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::msgQuestion("Svuotamento", "Sei sicuro di voler settare a 0 le serie per tutti i fascicoli ?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSvuota', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaSvuota':
                        $this->importafascicoli('svuota');
                        break;
                    case $this->nameForm . '_vediLog':
                        $FileLog = 'LOG_IMPORTAZIONE_' . date('His') . '.log';
                        Out::openDocument(utiDownload::getUrl($FileLog, $this->fileLog));
                        break;
                    case $this->nameForm . '_ric_siglaserie_butt':
                        proRic::proRicSerieArc($this->nameForm);
                        break;
                    case $this->nameForm . '_Verifica':
                        Out::msgInfo("Verifica unicità della serie fascicoli", "Se tutte univoche la query deve portare 0 risultati!" . "<br>SELECT * FROM (SELECT COUNT(ROWID) AS QUANTI, SERIEANNO, SERIEPROGRESSIVO, SERIECODICE FROM PROGES GROUP BY SERIEANNO, SERIEPROGRESSIVO, SERIECODICE)A WHERE A.QUANTI>1");
                        break;
                }

                break;

            case 'close-portlet':
                $this->returnToParent();
                break;
            case 'returnSerieArc':
                $AnaserieArc_rec = $this->proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                }
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
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_enti');
        Out::closeDialog($this->nameForm);
    }

    private function scriviLog($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function importafascicoli($tipo = '') {
        if (!$_POST[$this->nameForm . '_ric_codiceserie'] && $tipo != 'svuota') {
            Out::msgInfo("ERRORE", "ERRORE");
            return;
        }

        foreach ($this->enti as $keyEnte => $ente) {

            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente['codice']);

            $this->scriviLog("Data base: {$ente['codice']} $keyEnte Inizio Elaborazione");

            $sql_base = "SELECT ROWID, GESNUM FROM PROGES ORDER BY ROWID ASC";
            $proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_base, true);
            $this->scriviLog("Estratti: " . count($proges_tab) . "  Fascicoli da Elaborare");
            continue;
            $serie = array();
            $i = 0;
            foreach ($proges_tab as $proges_rec) {
                $serie['ROWID'] = $proges_rec['ROWID'];
                $serie['GESNUM'] = $proges_rec['GESNUM'];
                $serie['SERIEANNO'] = substr($proges_rec['GESNUM'], 0, 4);
                $serie['SERIEPROGRESSIVO'] = substr($proges_rec['GESNUM'], 4);
                $serie['SERIECODICE'] = $_POST[$this->nameForm . '_ric_codiceserie'];

                if ($tipo == 'svuota') {
                    $serie['SERIEANNO'] = '0';
                    $serie['SERIEPROGRESSIVO'] = '0';
                    $serie['SERIECODICE'] = '0';
                }
                $toinsert = $this->inserisciSerie($serie);

                if (!$toinsert) {
                    Out::msgStop("ERROE", "Errore in aggiornamento fascicolo " . $serie['GESNUM'] . " la procedura è stata interrotta");
                    break;
                }
                $i++;
            }
            $this->controlloserie($i);
            $this->PRAM_DB = null;
            
        }
        return true;
    }

    private function inserisciSerie($serie_rec) {

        try {
            ItaDB::DBUpdate($this->PRAM_DB, 'PROGES', 'ROWID', $serie_rec);
        } catch (Exception $ex) {
            $testo = "Fatal: Aggiornamento iFascicolo Record ROWID " . $serie_rec['ROWID'] . " GESNUM " . $serie_rec['GESNUM'] . $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }

    private function controlloserie($i) {
        /*
         * Controlla unicità serie
         */
        //SELECT * FROM `PROGES` GROUP BY `SERIEPROGRESSIVO`, `SERIEANNO`, `SERIECODICE` ORDER BY `ROWID` DESC 
        //UPDATE PROGES SET `SERIEANNO` = 0, `SERIEPROGRESSIVO` = 0, `SERIECODICE` = 0
        $proges_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT COUNT(DISTINCT `SERIEANNO`,`SERIEPROGRESSIVO`,`SERIECODICE`)AS tot FROM PROGES", false);
        $fascicoli = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT COUNT(GESNUM)AS tot FROM PROGES", false);
        if ($proges_rec['tot'] == $i && $i == $fascicoli['tot']) {
            Out::msgInfo("OK ", "Ok " . $i . " Fascicoli Modificati " . $proges_rec['tot'] . " Serie univoche trovate " . $fascicoli['tot'] . " Fascicoli totali di PROGEST");
            return true;
        } else {
            Out::msgInfo("Attenzione", $i . " Fascicoli Modificati " . $proges_rec['tot'] . " Serie univoche trovate " . $fascicoli['tot'] . " Fascicoli totali di PROGEST");

            return false;
        }
    }

}

?>
