<?php

/**
 *
 * Gestione Elenco registri giornalieri
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPRestClient
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    09.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';

function proCaricaMetadatiCons() {
    $proCaricaMetadatiCons = new proCaricaMetadatiCons();
    $proCaricaMetadatiCons->parseEvent();
    return;
}

class proCaricaMetadatiCons extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $proLibAllegati;
    public $proLibTabDag;
    public $proLibConservazione;
    public $nameForm = "proCaricaMetadatiCons";
    public $divRis = "proCaricaMetadatiCons_divRisultato";
    public $workDate;
    public $workYear;
    public $eqAudit;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibAllegati = new proLibAllegati();
            $this->proLibTabDag = new proLibTabDag();
            $this->proLibConservazione = new proLibConservazione();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->eqAudit = new eqAudit();
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($data));
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;
            case 'dbClickRow':
            case 'editGridRow':
                break;
            case 'onClickTablePager':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elabora':
                        if ($this->ElaboraEsitoConservazione()) {
                            Out::msgInfo('Informazione', 'Metadati dei protocolli caricati correttamente.');
                        }
                        break;
                    case $this->nameForm . '_ElaboraProtocollo':
                        $Anno = $_POST[$this->nameForm . '_Anno'];
                        $Numero = $_POST[$this->nameForm . '_Numero'];
                        $Tipo = $_POST[$this->nameForm . '_Tipo'];
                        if ($Anno && $Numero && $Tipo) {
                            $Numero = str_pad($Numero, 6, '0', STR_PAD_LEFT);
                            $Pronum = $Anno . $Numero;
                            $Anapro_rec = $this->proLib->GetAnapro($Pronum, 'codice', $Tipo);
                            if ($Anapro_rec) {
                                if ($this->ElaboraEsitoConservazione($Pronum, $Tipo)) {
                                    Out::msgInfo('Informazione', 'Metadati del protocollo caricati correttamente.');
                                }
                            } else {
                                Out::msgStop("Attenzione", "Il protocollo indicato non esiste.");
                            }
                        }
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
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
        Out::show('menuapp');
    }

    public function ElaboraEsitoConservazione($Pronum = '', $ProPar = '') {

        /*
         * Estraggo i tabdag ESITO CONSERVAZIONE NON STORICIZZATI
         */
        $sql = "SELECT ANAPRO.PRONUM,ANAPRO.PROPAR, TABDAG.* FROM TABDAG ";
        $sql.=" LEFT OUTER JOIN ANAPRO ANAPRO ON TABDAG.TDROWIDCLASSE = ANAPRO.ROWID ";
        $sql.=" WHERE TDCLASSE = 'ANAPRO' AND TDAGFONTE = '" . proLibConservazione::FONTE_DATI_ESITO_CONSERVAZIONE . "' ";
        // Qui where per singolo protocollo...join con anapro..
        if ($Pronum && $ProPar) {
            $sql.=" AND ANAPRO.PRONUM = $Pronum AND ANAPRO.PROPAR = '$ProPar' ";
        }
        $sql.=" GROUP BY TDCLASSE,TDROWIDCLASSE ";

        $TabDagEsito_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);

        Out::msgInfo('ANAPRO DA ELABORARE', print_r($TabDagEsito_tab, true));
        foreach ($TabDagEsito_tab as $TabDagEsito_rec) {
            /* Array proConser */
            $ProConser_rec = array();
            $ProConser_rec['PRONUM'] = $TabDagEsito_rec['PRONUM'];
            $ProConser_rec['PROPAR'] = $TabDagEsito_rec['PROPAR'];
            $ProConser_rec['ROWID_ANAPRO'] = $TabDagEsito_rec['TDROWIDCLASSE'];
            /*
             * Estrazione Fonte Dati e Valori
             */
            $FonteDati_tab = $this->proLibTabDag->GetValoriTabdagFonte($TabDagEsito_rec['TDCLASSE'], $TabDagEsito_rec['TDROWIDCLASSE'], $TabDagEsito_rec['TDAGFONTE'], $TabDagEsito_rec['TDPROG']);

            $ProConser_rec['PROGVERSAMENTO'] = $TabDagEsito_rec['TDPROG']; // DA ELABORARE O USA IL TDPROG?
            $ProConser_rec['DATAVERSAMENTO'] = date("Ymd", strtotime($FonteDati_tab[prolibConservazione::CHIAVE_ESITO_DATAVERSAMENTO]));
            $ProConser_rec['MOTIVOVERSAMENTO'] = proLibConservazione::MOTIVO_VERSAMENTO;
            $ProConser_rec['ESITOVERSAMENTO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_ESITO]; //Corretto? o da rielaborare?
            $ProConser_rec['DOCVERSAMENTO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_FILE_RICHIESTA];
            $ProConser_rec['DOCESITO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_FILE];
            $ProConser_rec['COD_UNITA_DOCUMENTARIA'] = 'STRG';
            $ProConser_rec['CONSERVATORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CONSERVATORE];
            $ProConser_rec['VERSIONE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_VERSIONE];
            $ProConser_rec['CODICEERRORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CODICEERRORE];
            $ProConser_rec['MESSAGGIOERRORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE];
            $ProConser_rec['CHIAVEVERSAMENTO'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO];
            $ProConser_rec['UUIDSIP'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_IDVERSAMENTO];
            $ProConser_rec['UTENTEVERSAMENTO'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_UTENTEVERSAMENTO];
            $ProConser_rec['FLSTORICO'] = 0;

            // Controllo se è già presente il record?
            try {
                ItaDB::DBInsert($this->PROT_DB, 'PROCONSER', 'ROWID', $ProConser_rec);
            } catch (Exception $e) {
                Out::msgStop("Attenzione", "Errore in inserimento PROCONSER.<br> " . $e->getMessage());
                return false;
            }
        }

        /*
         * 
         * Estraggo i tabdag ESITO CONSERVAZIONE STORICIZZATI.
         */
        $sql = "SELECT ANAPRO.PRONUM,ANAPRO.PROPAR, TABDAG.* FROM TABDAG ";
        $sql.=" LEFT OUTER JOIN ANAPRO ANAPRO ON TABDAG.TDROWIDCLASSE = ANAPRO.ROWID ";
        $sql.=" WHERE TDCLASSE = 'ANAPRO' AND TDAGFONTE = '" . proLibConservazione::FONTE_DATI_STORICO_ESITO_CONSERVAZIONE . "' ";
        // Qui where per singolo protocollo...join con anapro..
        if ($Pronum && $ProPar) {
            $sql.=" AND ANAPRO.PRONUM = $Pronum AND ANAPRO.PROPAR = '$ProPar' ";
        }
        $sql.=" GROUP BY TDPROG ";

        $TabDagEsito_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        Out::msgInfo('STORICI DA ELABORARE', print_r($TabDagEsito_tab, true));
        foreach ($TabDagEsito_tab as $TabDagEsito_rec) {
            /* Array proConser */
            $ProConser_rec = array();
            $ProConser_rec['PRONUM'] = $TabDagEsito_rec['PRONUM'];
            $ProConser_rec['PROPAR'] = $TabDagEsito_rec['PROPAR'];
            $ProConser_rec['ROWID_ANAPRO'] = $TabDagEsito_rec['TDROWIDCLASSE'];
            /*
             * Estrazione Fonte Dati e Valori
             */
            $FonteDati_tab = $this->proLibTabDag->GetValoriTabdagFonte($TabDagEsito_rec['TDCLASSE'], $TabDagEsito_rec['TDROWIDCLASSE'], $TabDagEsito_rec['TDAGFONTE'], $TabDagEsito_rec['TDPROG']);
            $ProConser_rec['PROGVERSAMENTO'] = $TabDagEsito_rec['TDPROG']; // DA ELABORARE O USA IL TDPROG?
            $ProConser_rec['DATAVERSAMENTO'] = date("Ymd", strtotime($FonteDati_tab[prolibConservazione::CHIAVE_ESITO_DATAVERSAMENTO]));
            $ProConser_rec['MOTIVOVERSAMENTO'] = proLibConservazione::MOTIVO_VERSAMENTO;
            $ProConser_rec['ESITOVERSAMENTO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_ESITO]; //Corretto? o da rielaborare?
            $ProConser_rec['DOCVERSAMENTO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_FILE_RICHIESTA];
            $ProConser_rec['DOCESITO'] = $FonteDati_tab[proLibConservazione::CHIAVE_ESITO_FILE];
            $ProConser_rec['COD_UNITA_DOCUMENTARIA'] = 'STRG';
            $ProConser_rec['CONSERVATORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CONSERVATORE];
            $ProConser_rec['VERSIONE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_VERSIONE];
            $ProConser_rec['CODICEERRORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CODICEERRORE];
            $ProConser_rec['MESSAGGIOERRORE'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE];
            $ProConser_rec['CHIAVEVERSAMENTO'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO];
            $ProConser_rec['UUIDSIP'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_IDVERSAMENTO];
            $ProConser_rec['UTENTEVERSAMENTO'] = $FonteDati_tab[prolibConservazione::CHIAVE_ESITO_UTENTEVERSAMENTO];
            $ProConser_rec['FLSTORICO'] = 1;

            try {
                ItaDB::DBInsert($this->PROT_DB, 'PROCONSER', 'ROWID', $ProConser_rec);
            } catch (Exception $e) {
                Out::msgStop("Attenzione", "Errore in inserimento PROCONSER STORICO.<br> " . $e->getMessage());
                return false;
            }
        }


        return true;
    }

}

?>
