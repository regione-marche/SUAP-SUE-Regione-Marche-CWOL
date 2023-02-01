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
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';

function proGestTitolario() {
    $proGestTitolario = new proGestTitolario();
    $proGestTitolario->parseEvent();
    return;
}

class proGestTitolario extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibTitolario;
    public $nameForm = "proGestTitolario";
    public $buttonBar = "proGestTitolario_buttonBar";
    public $Prog_Titp;
    public $Versione_T;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibTitolario = new proLibTitolario();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->Prog_Titp = App::$utente->getKey($this->nameForm . '_Prog_Titp');
        $this->Versione_T = App::$utente->getKey($this->nameForm . '_Versione_T');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_Prog_Titp', $this->Prog_Titp);
            App::$utente->setKey($this->nameForm . '_Versione_T', $this->Versione_T);
        }
    }

    public function getProg_Titp() {
        return $this->Prog_Titp;
    }

    public function setProg_Titp($Prog_Titp) {
        $this->Prog_Titp = $Prog_Titp;
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
                if ($this->Prog_Titp) {
                    $this->Dettaglio($this->Prog_Titp);
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
                    case $this->nameForm . '_ATD_TITOPR[PROG_TITPP]_butt':
                        proRicTitolario::proRicTreeTitolario($this->nameForm, $this->Versione_T);
                        break;
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
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_':
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ATD_TITOPR[PROG_TITPP]':
                        $valore = $_POST[$this->nameForm . '_ATD_TITOPR']['PROG_TITPP'];
                        $this->DecodPadre($valore, true);
                        break;
                }
                break;

            case 'returnRicTitolario':
                $rowData = $_POST['rowData'];
                $chiave = $rowData['CHIAVE'];
                if ($chiave) {
                    $this->DecodPadre($chiave, true);
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_Prog_Titp');
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
        Out::setAppTitle($this->nameForm, 'Nuovo Titolario.');
        Out::setFocus('', $this->nameForm . '_PADRE');

        $this->BloccaSbloccaCampi();
        $this->DecodVersione($this->Versione_T);
        $this->DecodPadre();
    }

    public function GetCodiceTitolario($Prog_Titp = '') {
        $CodTitolario = '';
        if ($Prog_Titp) {
            $Atd_Titopr_rec = $this->proLibTitolario->GetTitpr($Prog_Titp);
            $CodTitolario = $Atd_Titopr_rec['TITP_CATEG'];
            if ($Atd_Titopr_rec['TITP_CLASS']) {
                $CodTitolario.='.' . $Atd_Titopr_rec['TITP_CLASS'];
            }
            if ($Atd_Titopr_rec['TITP_FASCI']) {
                $CodTitolario.='.' . $Atd_Titopr_rec['TITP_FASCI'];
            }
            if ($Atd_Titopr_rec['TITP_SUBFA']) {
                $CodTitolario.='.' . $Atd_Titopr_rec['TITP_SUBFA'];
            }
        }
        return $CodTitolario;
    }

    public function Dettaglio($Prog_Titp = '') {
        if (!$Prog_Titp) {
            Out::msgStop("Attenzione", "Progressivo titolario non specificato.");
            return;
        }
        $this->Nascondi();
        Out::clearFields($this->nameForm . '_divGestione');
        Out::clearFields($this->nameForm . '_divAppoggio');
        Out::show($this->nameForm . '_Aggiorna');

        $Atd_Titopr_rec = $this->proLibTitolario->GetTitpr($Prog_Titp);
        Out::setAppTitle($this->nameForm, 'Modifica Titolario ');
        Out::valori($Atd_Titopr_rec, $this->nameForm . '_ATD_TITOPR');
        Out::valore($this->nameForm . '_IDENTIFICATIVO', $Atd_Titopr_rec['PROG_TITP']);
        // Decodifica padre: 
        $this->DecodPadre($Atd_Titopr_rec['PROG_TITPP']);
        $this->DecodVersione($Atd_Titopr_rec['VERSIONE_T']);
        $this->BloccaSbloccaCampi('blocca');

        Out::setFocus('', $this->nameForm . '_ATD_TITOPR[DES_TITP]');
    }

    public function BloccaSbloccaCampi($tipo = 'sblocca') {
        switch ($tipo) {
            case 'blocca':
                $operaz = 'disableField';
                break;
            case 'sblocca':
            default:
                $operaz = 'enableField';
                break;
        }

        Out::$operaz($this->nameForm . '_ATD_TITOPR[TITP_CATEG]');
        Out::$operaz($this->nameForm . '_ATD_TITOPR[TITP_CLASS]');
        Out::$operaz($this->nameForm . '_ATD_TITOPR[TITP_FASCI]');
        Out::$operaz($this->nameForm . '_ATD_TITOPR[TITP_SUBFA]');
        Out::$operaz($this->nameForm . '_ATD_TITOPR[PROG_TITPP]');
        Out::$operaz($this->nameForm . '_NUMROMANA');
    }

    public function DecodPadre($prog = '', $decodLiv = false) {
        $Padre = '0';
        $DescPadre = '';

        if ($prog) {
            $Atd_Titopr_rec = $this->proLibTitolario->GetTitpr($prog);
            if ($Atd_Titopr_rec) {
                $Padre = $Atd_Titopr_rec['PROG_TITP'];
                $DescPadre = $Atd_Titopr_rec['DES_TITP'];
                // Decodifico Titolo Classe SottoClasse Categoria:
                if ($decodLiv) {
                    Out::valore($this->nameForm . '_ATD_TITOPR[TITP_CATEG]', $Atd_Titopr_rec['TITP_CATEG']);
                    Out::valore($this->nameForm . '_ATD_TITOPR[TITP_CLASS]', $Atd_Titopr_rec['TITP_CLASS']);
                    Out::valore($this->nameForm . '_ATD_TITOPR[TITP_FASCI]', $Atd_Titopr_rec['TITP_FASCI']);
                    Out::valore($this->nameForm . '_ATD_TITOPR[TITP_SUBFA]', $Atd_Titopr_rec['TITP_SUBFA']);
                }
            } else {
                Out::msgInfo('Attenzione', "Il progressivo del Titolario inserito non è valido.");
            }
        } else {
            if ($this->Versione_T) {
                $Aac_Vers_rec = $this->proLibTitolario->GetVersione($this->Versione_T);
                $DescPadre = $Aac_Vers_rec['DESCRI'];
            }
        }
        /* Decodifico il Codice Titolario */
        Out::valore($this->nameForm . '_ATD_TITOPR[PROG_TITPP]', $Padre);
        Out::valore($this->nameForm . '_DESC_PADRE', $DescPadre);
    }

    public function DecodVersione($Versione = '') {
        if ($Versione) {
            $Aac_Vers_rec = $this->proLibTitolario->GetVersione($Versione);
            if ($Aac_Vers_rec) {
                Out::valore($this->nameForm . '_ATD_TITOPR[VERSIONE_T]', $Aac_Vers_rec['VERSIONE_T']);
                Out::valore($this->nameForm . '_DESC_VERSIONE', $Aac_Vers_rec['DESCRI']);
            } else {
                Out::valore($this->nameForm . '_ATD_TITOPR[VERSIONE_T]', '');
                Out::valore($this->nameForm . '_DESC_VERSIONE', '');
            }
        } else {
            Out::valore($this->nameForm . '_DESC_VERSIONE', '');
        }
    }

    public function Aggiungi() {
        $Atd_Titopr_rec = $_POST[$this->nameForm . '_ATD_TITOPR'];

        if (!$this->ControlliTitolario($Atd_Titopr_rec['PROG_TITP'])) {
            return;
        }

        // Valorizzo Nodo:
        if ($Atd_Titopr_rec['TITP_SUBFA']) {
            $Nodo = 4;
        } else if ($Atd_Titopr_rec['TITP_FASCI']) {
            $Nodo = 3;
        } else if ($Atd_Titopr_rec['TITP_CLASS']) {
            $Nodo = 2;
        } else {
            $Nodo = 1;
        }

        $Atd_Titopr_rec['NODO'] = $Nodo;

        try {
            ItaDB::DBInsert($this->PROT_DB, 'ATD_TITOPR', 'PROG_TITP', $Atd_Titopr_rec);
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento Titolario.<br> " . $e->getMessage());
            return false;
        }

        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
    }

    public function Aggiorna() {
        $Atd_Titopr_rec = $_POST[$this->nameForm . '_ATD_TITOPR'];

        if (!$this->ControlliTitolario($Atd_Titopr_rec['PROG_TITP'])) {
            return;
        }

        try {
            $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ATD_TITOPR', 'PROG_TITP', $Atd_Titopr_rec);
            if ($nrow == -1) {
                Out::msgStop("Errore", 'Aggiornamento Titolario Fallito.');
                return false;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return false;
        }

        $returnObj = itaModel::getInstance($this->returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->returnToParent();
    }

    public function ControlliTitolario($Prog = '') {
        $Atd_Titpr_rec = $_POST[$this->nameForm . '_ATD_TITOPR'];
        /* 0. Controllo Versione Valorizzata */
        if (!$Atd_Titpr_rec['VERSIONE_T']) {
            Out::msgStop("Attenzione", "Versione del titolario obbligatoria.");
            return false;
        }
        /* 1. Controllo valori numerici. */
        if ($Atd_Titpr_rec['TITP_CATEG'] && !is_numeric($Atd_Titpr_rec['TITP_CATEG'])) {
            Out::msgStop("Attenzione", "Il Titolo deve essere numerico.");
            return false;
        } else if ($Atd_Titpr_rec['TITP_CLASS'] && !is_numeric($Atd_Titpr_rec['TITP_CLASS'])) {
            Out::msgStop("Attenzione", "La Classe deve essere numerica.");
            return false;
        } else if ($Atd_Titpr_rec['TITP_FASCI'] && !is_numeric($Atd_Titpr_rec['TITP_FASCI'])) {
            Out::msgStop("Attenzione", "La SottoClasse deve essere numerica.");
            return false;
        } else if ($Atd_Titpr_rec['TITP_SUBFA'] && !is_numeric($Atd_Titpr_rec['TITP_SUBFA'])) {
            Out::msgStop("Attenzione", "La Categoria deve essere numerica.");
            return false;
        }

        /* 2. Controllo, almento 1 livello caricato */
        if (!$Atd_Titpr_rec['TITP_CATEG']) {
            Out::msgStop("Attenzione", "Titolo obbligatorio.");
            return false;
        }
        /* Controllo, Descrizione Obbligatoria */
        if (!$Atd_Titpr_rec['DES_TITP']) {
            Out::msgStop("Attenzione", "Descrizione obbligatoria.");
            return false;
        }

        /* 3. Se valorizzato un livello, il precedente deve essere valorizzato. 
         * Livello 1 Obbligatorio, quindi controllo sul livello 2 è inutile.
         */
        if ($Atd_Titpr_rec['TITP_FASCI']) {
            if (!$Atd_Titpr_rec['TITP_CLASS']) {
                Out::msgStop("Attenzione", "SottoClasse valorizzata, ma Classe vuota. Occorre valorizzare entrambe per procedere.");
                return false;
            }
        }
        if ($Atd_Titpr_rec['TITP_SUBFA']) {
            if (!$Atd_Titpr_rec['TITP_FASCI']) {
                Out::msgStop("Attenzione", "Categoria valorizzata, ma SottoClasse vuota. Occorre valorizzare entrambe per procedere.");
                return false;
            } else if (!$Atd_Titpr_rec['TITP_CLASS']) {
                Out::msgStop("Attenzione", "Categoria valorizzata, ma Classe vuota. Occorre valorizzare entrambe per procedere.");
                return false;
            }
        }

        /* 4. Se valorizzata la Classe, non è di Livello 1 e deve avere un Progressivo Padre */
        if ($Atd_Titpr_rec['TITP_CLASS'] && !$Atd_Titpr_rec['PROG_TITPP']) {
            Out::msgStop("Attenzione", "Titolo del nuovo Titolario valorizzato. Occorre indicare il Titolario Padre.");
            return false;
        }


        /* 5. Controllo Padre Valido */
        if ($Atd_Titpr_rec['PROG_TITPP']) {
            $Padre_Atd_TitPr = $this->proLibTitolario->GetTitpr($Atd_Titpr_rec['PROG_TITPP']);
            if (!$Padre_Atd_TitPr) {
                Out::msgStop("Attenzione", "Padre indicato non valido.");
                return false;
            }
            /* Controllo Coerenza Padre - Figlio: 1 Livello se ha padre la Classe deve corrispondere. */
            if ($Atd_Titpr_rec['TITP_CATEG'] != $Padre_Atd_TitPr['TITP_CATEG']) {
                Out::msgStop("Attenzione", "Titolo del Titolario Padre diverso dal Titolo Indicato.");
                return false;
            }

            /* Controllo Coerenza Padre - Figlio della Classe, solo se SottoClasse Valorizzata */
            if ($Atd_Titpr_rec['TITP_FASCI']) {
                if ($Atd_Titpr_rec['TITP_CLASS'] != $Padre_Atd_TitPr['TITP_CLASS']) {
                    Out::msgStop("Attenzione", "Classe del Titolario Padre diverso dalla Classe Indicata.");
                    return false;
                }
            }
            /* Controllo Coerenza Padre - Figlio della SottoClasse, solo se Categoria Valorizzata */
            if ($Atd_Titpr_rec['TITP_SUBFA']) {
                if ($Atd_Titpr_rec['TITP_FASCI'] != $Padre_Atd_TitPr['TITP_FASCI']) {
                    Out::msgStop("Attenzione", "SottoClasse del Titolario Padre diverso dalla SottoClasse Indicata.");
                    return false;
                }
            }
            // Il nodo padre deve avere un solo livello di noto superiore al figlio.
            /* 6. Controllo Coerenza  */

            // Valorizzo Nodo:
            if ($Atd_Titpr_rec['TITP_SUBFA']) {
                $Nodo = 4;
            } else if ($Atd_Titpr_rec['TITP_FASCI']) {
                $Nodo = 3;
            } else if ($Atd_Titpr_rec['TITP_CLASS']) {
                $Nodo = 2;
            } else {
                $Nodo = 1;
            }

            $NodoPadre = $Padre_Atd_TitPr['NODO'];
            if (($Nodo - $NodoPadre) != 1) {
                Out::msgStop("Attenzione", "Titolario inserito in un Titolario Padre incoerente.");
                return false;
            }
        }

        /* 7. Controllo se esiste già Voce Titolario */
        $sql = "SELECT * FROM ATD_TITOPR 
                    WHERE VERSIONE_T = {$Atd_Titpr_rec['VERSIONE_T']} AND 
                    TITP_CATEG = {$Atd_Titpr_rec['TITP_CATEG']} AND
                    DATACESS = '' ";
        /* Where Livello 2 */
        if ($Atd_Titpr_rec['TITP_CLASS']) {
            $sql.=" AND TITP_CLASS = {$Atd_Titpr_rec['TITP_CLASS']} ";
        }
        /* Where Livello 3 */
        if ($Atd_Titpr_rec['TITP_FASCI']) {
            $sql.=" AND TITP_FASCI = {$Atd_Titpr_rec['TITP_FASCI']} ";
        }
        /* Where Livello 4 */
        if ($Atd_Titpr_rec['TITP_SUBFA']) {
            $sql.=" AND TITP_SUBFA = {$Atd_Titpr_rec['TITP_SUBFA']} ";
        }
        $Atd_prog_test = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        if ($Atd_prog_test) {
            if ($Prog == '' || ($Prog != '' && $Prog != $Atd_prog_test['PROG_TITP'])) {
                Out::msgStop("Attenzione", "Titolario già esistente.");
                return false;
            }
        }


        // Controllo coerenza.
        // Controlli su numeri romani?
        return true;
    }

}

?>
