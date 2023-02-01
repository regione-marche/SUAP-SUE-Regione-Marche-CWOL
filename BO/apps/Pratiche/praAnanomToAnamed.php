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

function praAnanomToAnamed() {
    $intSuapSbt = new praAnanomToAnamed();
    $intSuapSbt->parseEvent();
    return;
}

class praAnanomToAnamed extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $PROT_DB;
    public $fileLog;
    public $nameForm;
    public $proRic;
    public $proLibSerie;
    public $MedCod;

    function __construct() {
        parent::__construct();
        try {
            /*
             * carico le librerie
             * 
             */
            $this->nameForm = 'praAnanomToAnamed';

            $this->fileLog = App::$utente->getKey($this->nameForm . '_fileLog');

            $this->praLib = new praLib();
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->proRic = new proRic();
            $this->proLibSerie = new proLibSerie();
            $this->MedCod = App::$utente->getKey($this->nameForm . '_MedCod');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_fileLog', $this->fileLog);
            App::$utente->setKey($this->nameForm . '_MedCod', $this->MedCod);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->fileLog = sys_get_temp_dir() . "/praAnanomToAnamed_" . time() . ".log";
                $this->scriviLog("Avvio Programma praAnanomToAnamed");
                unset($this->MedCod);
                $this->MedCod = $this->maxMedcod();
                Out::html($this->nameForm . "_divInfo", "Testo");
                Out::html($this->nameForm . "_divIstruzione", "Testo");
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Conferma':
                        $this->importaAnanom();
                        break;
                    case $this->nameForm . '_Svuota':
                        Out::msgQuestion("Svuotamento", "Sei sicuro di voler settare a 0 le serie per tutti i fascicoli ?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSvuota', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaSvuota':
                        break;
                    case $this->nameForm . '_vediLog':
                        $FileLog = 'LOG_IMPORTAZIONE_' . date('His') . '.log';
                        Out::openDocument(utiDownload::getUrl($FileLog, $this->fileLog));
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
        Out::closeDialog($this->nameForm);
    }

    private function scriviLog($testo, $flAppend = true, $nl = "\n") {
        if ($flAppend) {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl", FILE_APPEND);
        } else {
            file_put_contents($this->fileLog, "[" . date("d/m/Y H.i:s") . "] " . "$testo$nl");
        }
    }

    private function maxMedcod() {
        $sql = "SELECT MAX(MEDCOD) AS MEDCOD FROM ANAMED";
        $Medcod = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        if (!$Medcod['MEDCOD']) {
            $Medcod['MEDCOD'] = '000001';
        }
        return $Medcod['MEDCOD'];
    }

    private function importaAnanom() {
        //$sql_base = "SELECT * FROM ANANOM WHERE NOMRES LIKE '005280' ";
        $sql_base = "SELECT * FROM ANANOM";
        $AnanomTab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_base, true);
        //Out::msgInfo("SLQ", print_r($AnanomTab, true));
        $A = $this->MedCod+1;
        foreach ($AnanomTab as $key => $AnanomRec) {
            $this->importaAnamed($AnanomRec['ROWID'],$AnanomRec['NOMCOG'], $AnanomRec['NOMNOM']);
        }
        Out::msgInfo("Fine Inserimento su ANAMED", 'Inseriti Soggetti da Codice '.$A.'A '.$this->MedCod);
        unset($this->MedCod);
        return true;
    }

    private function importaAnamed($RowidAnanom, $Cognome = '', $Nome = '') {
        $AppoggioNome = explode('(', $Nome);
        $AppoggioCognome = explode('(', $Cognome);
        if ($AppoggioNome) {
            $Nome = $AppoggioNome[0];
        }
        if ($AppoggioCognome) {
            $Cognome = $AppoggioCognome[0];
        }
        $sql = "SELECT * FROM ANAMED WHERE MEDUFF  <> '' AND MEDANN = 0 AND (" . $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($Cognome . ' ' . $Nome)) . "%' OR " . $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($Nome . ' ' . $Cognome)) . "%')";
        $AnamedRec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        if ($AnamedRec) {
            $this->scriviLog('Soggetto ANAMED Già presente ' . $AnamedRec['MEDNOM'] . ' ROWID ' . $AnamedRec['ROWID'] . '\n');
            return false;
        }
        $this->inserisciAnamed($RowidAnanom,$Cognome, $Nome);

        // Out::msgInfo("SLQ", print_r($sql, true));
        //Out::msgInfo("result", print_r($proges_tab, true));
        return true;
    }

    private function inserisciAnamed($RowidAnanom,$Cognome, $Nome) {
               $this->MedCod++;
               $medcod= str_pad($this->MedCod,6,0,STR_PAD_LEFT);
        $NewAnanom = array(
            'MEDCOD' => $medcod,
            'MEDNOM' => addslashes($Cognome . ' ' . $Nome),
            'MEDUFF' => 'true',
        );
        try {
            ItaDB::DBInsert($this->PROT_DB, 'ANAMED', 'ROWID', $NewAnanom);
            $this->updateAnanom($RowidAnanom, $medcod);
        } catch (Exception $ex) {
            $testo = "Fatal: inserimento record Record in to Anamed Nominativo " . $NewAnanom['MEDNOM'].' '. $ex->getMessage();
            $this->scriviLog($testo);
            return false;
        }
        return true;
    }
    private function updateAnanom($Rowid,$MedCod) {
        $NewAnanom = array(
            'ROWID' => $Rowid,
            'NOMDEP' => $MedCod
        );
        ItaDB::DBUpdate($this->PRAM_DB, 'ANANOM', 'ROWID', $NewAnanom);
        return true;
    }

}
