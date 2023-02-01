<?php

/**
 *
 * GESTIONE EMAIL
 *
 * PHP Version 5
 *
 * @category
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    06.05.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proCheckEstensioni() {
    $proCheckEstensioni = new proCheckEstensioni();
    $proCheckEstensioni->parseEvent();
    return;
}

class proCheckEstensioni extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibAllegati;
    public $utiEnte;
    public $nameForm = "proCheckEstensioni";
    public $divGes = "proCheckEstensioni_divGestione";
    public $divRic = "proCheckEstensioni_divRicerca";
    public $gridEstensioni = "proCheckEstensioni_gridEstensioni";
    public $ElencoEstensioni = array();
    public $ProtSenzaExt = array();
    public $Conteggi = array();
    public $AllegatiSenzaFile = array();
    public $AllegatiAnomaliaImpronta = array();
    public $AllegatiUUID = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->utiEnte = new utiEnte();
            $this->utiEnte->getITALWEB_DB();
            $this->ElencoEstensioni = App::$utente->getKey($this->nameForm . '_ElencoEstensioni');
            $this->ProtSenzaExt = App::$utente->getKey($this->nameForm . '_ProtSenzaExt');
            $this->Conteggi = App::$utente->getKey($this->nameForm . '_Conteggi');
            $this->AllegatiSenzaFile = App::$utente->getKey($this->nameForm . '_AllegatiSenzaFile');
            $this->AllegatiAnomaliaImpronta = App::$utente->getKey($this->nameForm . '_AllegatiAnomaliaImpronta');
            $this->AllegatiUUID = App::$utente->getKey($this->nameForm . '_AllegatiUUID');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ElencoEstensioni', $this->ElencoEstensioni);
            App::$utente->setKey($this->nameForm . '_ProtSenzaExt', $this->ProtSenzaExt);
            App::$utente->setKey($this->nameForm . '_Conteggi', $this->Conteggi);
            App::$utente->setKey($this->nameForm . '_AllegatiSenzaFile', $this->AllegatiSenzaFile);
            App::$utente->setKey($this->nameForm . '_AllegatiAnomaliaImpronta', $this->AllegatiAnomaliaImpronta);
            App::$utente->setKey($this->nameForm . '_AllegatiUUID', $this->AllegatiUUID);
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                Out::valore($this->nameForm . '_RICERCA[DADATA]', date('Y') . '0101');
                Out::valore($this->nameForm . '_RICERCA[ADATA]', date('Y') . '1231');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'delGridRow':
                break;
            case 'exportTableToExcel':
                break;
            case 'onClickTablePager':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elabora':
                        $this->ElaboraEstensioni();
                        break;

                    case $this->nameForm . '_ScaricaElenco':
                        $ita_grid01 = new TableView('griglia', array('arrayTable' => $this->ElencoEstensioni,
                            'rowIndex' => 'idx'));
                        $ita_grid01->exportXLS('', 'ElencoEstensioni.xls');
                        break;

                    case $this->nameForm . '_ControllaFiles':
                        $this->ControllaFiles();
                        break;
                    case $this->nameForm . '_ControllaProtFiles':
                        $this->ControllaProtFiles();
                        break;
                    case $this->nameForm . '_ControllaUUID':
                        $this->ControllaUUID();
                        break;

                    case $this->nameForm . '_ScaricaElencoFiles':
                        $ArrFiles = array_merge($this->AllegatiSenzaFile, $this->AllegatiAnomaliaImpronta);
                        $ita_grid01 = new TableView('griglia', array('arrayTable' => $ArrFiles,
                            'rowIndex' => 'idx'));
                        $ita_grid01->exportXLS('', 'ElencoAnomalieFiles.xls');
                        break;
                    case $this->nameForm . '_ScaricaElencoUUID':
                        $ita_grid01 = new TableView('griglia', array('arrayTable' => $this->AllegatiUUID,
                            'rowIndex' => 'idx'));
                        $ita_grid01->exportXLS('', 'ElencoAnomalieFiles.xls');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onChange':
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_ElencoEstensioni');
        App::$utente->removeKey($this->nameForm . '_ProtSenzaExt');
        App::$utente->removeKey($this->nameForm . '_Conteggi');
        App::$utente->removeKey($this->nameForm . '_AllegatiSenzaFile');
        App::$utente->removeKey($this->nameForm . '_AllegatiAnomaliaImpronta');
        App::$utente->removeKey($this->nameForm . '_AllegatiUUID');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function CreaSql() {
        $sql = "SELECT ROWID,DOCNAME,DOCNUM,DOCPAR,DOCSHA2 FROM ANADOC WHERE DOCNUM >= '2017000000' AND DOCSERVIZIO = 0 ";
        // Solo per i protocolli per ora.
        $sql.=" AND (DOCPAR = 'P' OR DOCPAR ='A' OR DOCPAR = 'C') ";
        $Ricerca = $_POST[$this->nameForm . '_RICERCA'];

        if ($Ricerca['DADATA']) {
            $sql.=" AND DOCFDT >= '" . $Ricerca['DADATA'] . "' ";
        }
        if ($Ricerca['ADATA']) {
            $sql.=" AND DOCFDT <= '" . $Ricerca['ADATA'] . "' ";
        }
        return $sql;
    }

    public function ElaboraEstensioni() {
        $sql = $this->CreaSql();
        $Anadoc_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $this->ElencoEstensioni = array();
        $this->ProtSenzaExt = array();

        foreach ($Anadoc_tab as $Anadoc_rec) {
            // Un Livello di controllo, per p7m potrebbe essere 3..
            $ext = strtolower($this->GetEstensione($Anadoc_rec['DOCNAME'], 1));
            $ArrExt = array();

            if (!$ext) {
                $ext = '0';
                $ArrExt['QUANTI'] = $this->ElencoEstensioni[$ext]['QUANTI'];
                $ArrExt['ESTENSIONE'] = 'Senza Estensione';
                $ArrExt['NUMEROPROT'] = $Anadoc_rec['DOCNUM'];
                $ArrExt['NOMEFILE'] = $Anadoc_rec['DOCNAME'];
                $ArrExt['QUANTI'] ++;
                $this->ProtSenzaExt[] = $Anadoc_rec['DOCNUM'] . ' - ' . $Anadoc_rec['DOCNAME'];
            } else {
                $ArrExt['QUANTI'] = $this->ElencoEstensioni[$ext]['QUANTI'];
                $ArrExt['ESTENSIONE'] = $ext;
                $ArrExt['NUMEROPROT'] = $Anadoc_rec['DOCNUM'];
                $ArrExt['NOMEFILE'] = $Anadoc_rec['DOCNAME'];
                $ArrExt['QUANTI'] ++;
            }
            $this->ElencoEstensioni[$ext] = $ArrExt;
        }
        $msg = ' Elenco Estensioni Utilizzate:<br><br>';
        $msg.= '<div style="width:120px; display:inline-block;"> <b>Estensione</b> </div>';
        $msg.= '<div style="width:200px; display:inline-block;"><b>Totali</b></div>';
        $msg.= '<div style="width:80px; display:inline-block;"><b>Prot di Riferimento</b></div>';
        $msg.= '<div style="width:350px; display:inline-block;"> <b>Nome File</b> </div><br>';
        foreach ($this->ElencoEstensioni as $Estensione) {
            $msg.= '<div style="width:120px; display:inline-block; margin-left:5px;"> ' . $Estensione['ESTENSIONE'] . '</div>';
            $msg.= '<div style="width:80px; display:inline-block;">' . $Estensione['QUANTI'] . '</div>';
            $msg.= '<div style="width:200px; display:inline-block;">' . $Estensione['NUMEROPROT'] . '</div>';
            $msg.= '<div style="width:750px; display:inline-block;">' . $Estensione['NOMEFILE'] . "</div><br>";
        }
        $msg .="<br> Elenco File Senza Estensioni: <br><br>";
        foreach ($this->ProtSenzaExt as $ProtSenzaExt) {
            $msg.= ' - ' . $ProtSenzaExt . "<br>";
        }
        Out::html($this->nameForm . '_divInfo', $msg);
//        Out::msginfo('Estensioni utilizzate', print_r($this->ElencoEstensioni, true));
//        Out::msginfo('Senza Estensioni', print_r($this->ProtSenzaExt, true));
    }

    public function GetEstensione($NomeFile, $LivCtr = 3) {
        // SEMPLIFICARE?
        $Estensione = '';
        $Ctr = 1;
        while (true) {
            $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            if ($ext == '') {
                break;
            }
            $Estensione = '.' . $ext . $Estensione;
            if ($Ctr == $LivCtr) {
                break;
            }
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    public function ControllaFiles() {
        $sql = $this->CreaSql();
        $Anadoc_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $this->AllegatiSenzaFile = array();
        $this->AllegatiAnomaliaImpronta = array();

        foreach ($Anadoc_tab as $kk => $Anadoc_rec) {
            $ArrFile = array();
            $ArrFile['NUMEROPROT'] = $Anadoc_rec['DOCNUM'] . ' - ' . $Anadoc_rec['DOCPAR'];
            $ArrFile['NOMEFILE'] = $Anadoc_rec['DOCNAME'];
            $rowidAnadoc = $Anadoc_rec['ROWID'];
            /*
             * 1. Controllo Prima File
             */
            $HashFile = $this->proLibAllegati->GetHashDocAllegato($Anadoc_rec['ROWID']);
            if (!$HashFile) {
                $ArrFile['DESCRIZIONE'] = 'File non presente.';
                $this->AllegatiSenzaFile[$rowidAnadoc] = $ArrFile;
                continue;
            }
            /*
             * Confronto L'impronta
             */
            if ($HashFile != $Anadoc_rec['DOCSHA2']) {
                $ArrFile['DESCRIZIONE'] = 'Impronta file non corrispondente.';
                $this->AllegatiAnomaliaImpronta[$rowidAnadoc] = $ArrFile;
            }
        }

        /*
         *  Elenco Documenti Senza File
         */
        $msg = ' Totale dei documenti elaborati: ' . count($Anadoc_tab) . '<br><br>';
        $msg.= ' Elenco dei documenti dove il file non è presente:<br><br>';
        $msg.= '<div style="width:200px; display:inline-block;"><b>Prot di Riferimento</b></div>';
        $msg.= '<div style="width:750px; display:inline-block;"> <b>Nome File</b> </div><br>';
        foreach ($this->AllegatiSenzaFile as $SenzaDoc) {
            $msg.= '<div style="width:200px; display:inline-block;">' . $SenzaDoc['NUMEROPROT'] . '</div>';
            $msg.= '<div style="width:750px; display:inline-block;">' . $SenzaDoc['NOMEFILE'] . "</div><br>";
        }
        /*
         *  Elenco Documenti Anomalia Impronta
         */
        $msg.='<br><br>';
        $msg.= ' Elenco dei documenti con anomalie nell\'impronta del file: <br><br>';
        $msg.= '<div style="width:200px; display:inline-block;"><b>Prot di Riferimento</b></div>';
        $msg.= '<div style="width:750px; display:inline-block;"> <b>Nome File</b> </div><br>';
        foreach ($this->AllegatiAnomaliaImpronta as $AnomaliaImp) {
            $msg.= '<div style="width:200px; display:inline-block;">' . $AnomaliaImp['NUMEROPROT'] . '</div>';
            $msg.= '<div style="width:750px; display:inline-block;">' . $AnomaliaImp['NOMEFILE'] . "</div><br>";
        }

        Out::html($this->nameForm . '_divInfo', $msg);
    }

    public function ControllaProtFiles() {
        $sql = "SELECT *,ANAPRO.ROWID AS ROWIDANAPRO,
                    ANAPRO.PROUTE AS UTENTE,
                    ANAPRO.PROUOF AS CODUFFICIO
                    FROM `ANAPRO`
                        LEFT OUTER JOIN ANADOC ON ANAPRO.PRONUM = ANADOC.DOCNUM
                        AND ANAPRO.PROPAR = ANADOC.DOCPAR
                    WHERE ANADOC.ROWID IS NULL
                      AND (
                            ANAPRO.PROPAR = 'P' OR 
                            ANAPRO.PROPAR = 'A' OR 
                            ANAPRO.PROPAR = 'C'
                           ) ";
        $Ricerca = $_POST[$this->nameForm . '_RICERCA'];

        if ($Ricerca['DADATA']) {
            $sql.=" AND PRORDA >= '" . $Ricerca['DADATA'] . "' ";
        }
        if ($Ricerca['ADATA']) {
            $sql.=" AND PRORDA <= '" . $Ricerca['ADATA'] . "' ";
        }
        $this->AllegatiSenzaFile = array();
        $this->AllegatiAnomaliaImpronta = array();

        $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        foreach ($Anapro_tab as $kk => $Anapro_rec) {
            /*
             * Nessun file Presente
             */
            $ArrFile = array();
            $ArrFile['ROWID'] = $rowidAnapro;
            $ArrFile['NUMEROPROT'] = $Anapro_rec['PRONUM'] . ' - ' . $Anapro_rec['PROPAR'];
            $ArrFile['UTENTE'] = $Anapro_rec['UTENTE'];
            $Anauff_rec = $this->proLib->GetAnauff($Anapro_rec['CODUFFICIO'], 'codice');
            $ArrFile['UFFICIO'] = $Anapro_rec['CODUFFICIO'] . ' - ' . $Anauff_rec['UFFDES'];
            $rowidAnapro = $Anapro_rec['ROWIDANAPRO'];
            $ArrFile['DESCRIZIONE'] = 'Nessun file allegato presente';
            $this->AllegatiSenzaFile[$rowidAnapro] = $ArrFile;
        }
        /*
         *  Elenco Documenti Senza File
         */
        $msg = ' Totale dei protocolli senza allegati estratti ' . count($Anapro_tab) . '<br><br>';
        $msg.= ' Elenco dei protocolli:<br><br>';
        $msg.= '<div style="width:200px; display:inline-block;"><b>Prot di Riferimento</b>'
                . '</div><div style="width:200px; display:inline-block;"><b>Utente</b></div>'
                . '</div><div style="width:200px; display:inline-block;"><b>Ufficio</b></div>'
                . '<br>';
        foreach ($this->AllegatiSenzaFile as $SenzaDoc) {
            $msg.= '<div style="width:200px; display:inline-block;">' . $SenzaDoc['NUMEROPROT'] . '</div>';
            $msg.= '<div style="width:200px; display:inline-block;">' . $SenzaDoc['UTENTE'] . '</div>';
            $msg.= '<div style="width:200px; display:inline-block;">' . $SenzaDoc['UFFICIO'] . '</div>';
            $msg.= "<br>";
        }

        Out::html($this->nameForm . '_divInfo', $msg);
    }

    public function ControllaUUID() {
        $sql = "SELECT *,ANAPRO.ROWID AS ROWIDANAPRO, ANADOC.DOCNAME, ANAPRO.PROCODTIPODOC, ANADOC.ROWID AS ROWIDANADOC
                    FROM `ANAPRO`
                        LEFT OUTER JOIN ANADOC ON ANAPRO.PRONUM = ANADOC.DOCNUM
                        AND ANAPRO.PROPAR = ANADOC.DOCPAR
                    WHERE ANADOC.DOCUUID = '' 
                      AND (
                            ANAPRO.PROPAR = 'P' OR 
                            ANAPRO.PROPAR = 'A' OR 
                            ANAPRO.PROPAR = 'C'
                           ) ";
        $Ricerca = $_POST[$this->nameForm . '_RICERCA'];
        $anaent_49 = $this->proLib->GetAnaent('49');
        $anaent_38 = $this->proLib->GetAnaent('38');

        if ($Ricerca['DADATA']) {
            $sql.=" AND PRORDA >= '" . $Ricerca['DADATA'] . "' ";
        }
        if ($Ricerca['ADATA']) {
            $sql.=" AND PRORDA <= '" . $Ricerca['ADATA'] . "' ";
        }
        $this->AllegatiUUID = array();

        $Anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        foreach ($Anapro_tab as $kk => $Anapro_rec) {
            /*
             * Nessun file Presente
             */
            if ($anaent_49['ENTDE4']) {
                if ($Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE1'] && $Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE2'] && $Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE3'] && $Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE4'] && $Anapro_rec['PROCODTIPODOC'] != $anaent_45['ENTDE5']) {
                    continue;
                }
            }
            $ArrFile = array();
            $ArrFile['ROWID'] = $Anapro_rec['ROWIDANAPRO'];
            $ArrFile['NUMEROPROT'] = $Anapro_rec['PRONUM'] . ' - ' . $Anapro_rec['PROPAR'];
            $rowid = $Anapro_rec['ROWIDANADOC'];
            $ArrFile['NOMEFILE'] = $Anapro_rec['DOCNAME'];
            $ArrFile['DESCRIZIONE'] = 'UUID di Alfresco non trovato';
            $ArrFile['ROWIDANADOC'] = $Anapro_rec['ROWIDANADOC'];
            $this->AllegatiUUID[$rowid] = $ArrFile;
        }
        /*
         *  Elenco Documenti Senza File
         */
        $msg = ' Totale dei documenti con anomalie negli UUID ' . count($Anapro_tab) . '<br><br>';
        $msg.= ' Elenco dei protocolli:<br><br>';
        $msg.= '<div style="width:200px; display:inline-block;"><b>Prot di Riferimento</b></div>';
        $msg.= '<div style="width:750px; display:inline-block;"> <b>Nome File</b> </div><br>';
        foreach ($this->AllegatiUUID as $SenzaDoc) {
            if ($rowidAnapro != $SenzaDoc['ROWID']) {
                $msg.="<br>";
            }
            $msg.= '<div style="width:200px; display:inline-block;">' . $SenzaDoc['NUMEROPROT'] . '</div>';
            $msg.= '<div style="width:750px; display:inline-block;">' . $SenzaDoc['NOMEFILE'] . "</div><br>";
            $rowidAnapro = $SenzaDoc['ROWID'];
        }

        Out::html($this->nameForm . '_divInfo', $msg);
    }

}

?>