<?php

/**
 *  Browser per Forms
 *
 *
 * @category   Library
 * @package    /apps/Generator
 * @author     Carlo Iesari <carlo@iesari.em>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    30.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proGestVersioneTit() {
    $proGestVersioneTit = new proGestVersioneTit();
    $proGestVersioneTit->parseEvent();
    return;
}

class proGestVersioneTit extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proGestVersioneTit";
    public $buttonBar = "proGestVersioneTit_buttonBar";
    public $Versione_T;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->Versione_T = App::$utente->getKey($this->nameForm . '_Versione_T');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_Versione_T', $this->Versione_T);
        }
    }

    public function getVersione_T() {
        return $this->Versione_T;
    }

    public function setVersione_T($Versione_T) {
        $this->Versione_T = $Versione_T;
    }

    /**
     *  Gestione degli eventi
     */
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                // Nuovo
                if ($this->Versione_T != '') {
                    $this->Dettaglio($this->Versione_T);
                } else {
                    $this->Nuovo();
                }

                break;

            case 'addGridRow':
                break;

            case 'onClickTablePager':
                break;

            case 'dbClickRow':
                break;

            case 'cellSelect':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $this->Aggiorna();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->Aggiungi();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_Versione_T');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
    }

    public function Nuovo() {
        $this->Nascondi();
        Out::clearFields($this->nameForm . '_divGestione');
        Out::clearFields($this->nameForm . '_divAppoggio');
        Out::show($this->nameForm . '_Aggiungi');
        Out::setAppTitle($this->nameForm, 'Nuova Versione Titolario.');
        Out::setFocus('', $this->nameForm . '_AACVERS[DESCRI]');
    }

    public function Dettaglio($Versione_T = '') {
        if ($Versione_T == '') {
            Out::msgStop("Attenzione", "Versione titolario non specificata.");
            return;
        }

        $this->Nascondi();
        Out::clearFields($this->nameForm . '_divGestione');
        Out::clearFields($this->nameForm . '_divAppoggio');
        Out::show($this->nameForm . '_Aggiorna');
        // Prendo i valori
        $sql = "SELECT * FROM AACVERS WHERE VERSIONE_T = $Versione_T ";
        $Aac_vers_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        Out::setAppTitle($this->nameForm, 'Versione Titolario: ' . $Aac_vers_rec['DESCRI_B']);

        Out::valori($Aac_vers_rec, $this->nameForm . '_AACVERS');
        Out::setFocus('', $this->nameForm . '_AACVERS[DESCRI]');
    }

    public function Aggiungi() {
        if (!$this->ControlliVersione()) {
            return;
        }
        $Aac_Vers_rec = $_POST[$this->nameForm . '_AACVERS'];

        $retLock = ItaDB::DBLock($this->PROT_DB, "AACVERS", "", "", 20);
        if ($retLock['status'] != 0) {
            Out::msgStop('Errore', 'Blocco Tabella PROGRESSIVO AACVERS non Riuscito.');
            return false;
        }
        /* Prenoto numero versione. */
        $sqlMax = "SELECT MAX(VERSIONE_T) AS MAX_VERSIONE_T FROM AACVERS ";
        $MaxAac_vers_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sqlMax, false);
        $MaxVersione = 1;
        if ($MaxAac_vers_rec) {
            $MaxVersione = $MaxAac_vers_rec['MAX_VERSIONE_T'] + 1;
        }
        $Aac_Vers_rec['VERSIONE_T'] = $MaxVersione;
        try {
            ItaDB::DBInsert($this->PROT_DB, 'AACVERS', 'ROWID', $Aac_Vers_rec);
        } catch (Exception $e) {
            $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
            if ($retUnlock['status'] != 0) {
                Out::msgStop('Errore', 'Sblocco Tabella AACVERS non Riuscito.');
            }
            Out::msgStop('Errore', "Errore in inserimento Versione.<br> " . $e->getMessage());
            return false;
        }
        $retUnlock = ItaDB::DBUnLock($retLock['lockID']);
        if ($retUnlock['status'] != 0) {
            Out::msgStop('Errore', 'Sblocco Tabella AACVERS non Riuscito.');
            return false;
        }

        // msgBloc Aggiunta.
        Out::msgBlock('', 2000, true, 'Versione aggiunta correttamente.');
        // faccio ricaricare l'elenco.
        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
    }

    public function Aggiorna() {
        $Aac_Vers_rec = $_POST[$this->nameForm . '_AACVERS'];

        if (!$this->ControlliVersione($Aac_Vers_rec['VERSIONE_T'])) {
            return;
        }
        /* Proteggo VERSIONE_T. Non rimuovere dal divAppoggio, serve per i controlliVersione. Serve? */
        unset($Aac_Vers_rec['VERSIONE_T']);
        try {
            $nrow = ItaDB::DBUpdate($this->PROT_DB, 'AACVERS', 'ROWID', $Aac_Vers_rec);
            if ($nrow == -1) {
                Out::msgStop("Errore", 'Aggiornamento Versione Fallito');
                return false;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }

        // msgBloc Aggiorna.
        // faccio ricaricare l'elenco.
        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
    }

    public function ControlliVersione($Versionte_T = '') {
        // 1. Cotrollo tutti i campi obbligatori [Descr, Descr_breve, Data Inizio]
        // 2 Controllo se non c'è una versione con la stessa data [che non sia se stessa]
        // 
        $Aac_Vers_rec = $_POST[$this->nameForm . '_AACVERS'];
        if (!$Aac_Vers_rec['DESCRI']) {
            Out::msgStop("Attenzione", "Descrizione obbligatoria.");
            return false;
        }

        if (!$Aac_Vers_rec['DESCRI_B']) {
            Out::msgStop("Attenzione", "Descrizione breve obbligatoria.");
            return false;
        }

        if (!$Aac_Vers_rec['DATAINIZ']) {
            Out::msgStop("Attenzione", "Data inizio validità della versione obbligatoria.");
            return false;
        }

        $sql = "SELECT * FROM AACVERS WHERE DATAINIZ = '" . $Aac_Vers_rec['DATAINIZ'] . "' ";
        $Aac_Vers_test = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        if ($Aac_Vers_test) {
            if ($Versionte_T == '' || ($Versionte_T != '' && $Aac_Vers_test['VERSIONE_T'] != $Versionte_T)) {
                Out::msgStop("Attenzione", "Attenzione Versione con stessa data Inzio già presente.");
                return false;
            }
        }
        return true;
    }

}

?>
