<?php

/**
 *
 * Archivio Gestione Domini di posta
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    23.10.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';

function emlDomain() {
    $emlDomain = new emlDomain();
    $emlDomain->parseEvent();
    return;
}

class emlDomain extends itaModel {

    public $ITALWEB;
    public $emlLib;
    public $nameForm = "emlDomain";
    public $divGes = "emlDomain_divGestione";
    public $divRis = "emlDomain_divRisultato";
    public $divRic = "emlDomain_divRicerca";
    public $gridDomain = "emlDomain_gridDomain";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = $this->emlLib->getITALWEB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaCombo();
                $this->OpenRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDomain:
                        $this->Dettaglio($_POST['rowid']);
                        Out::show($this->nameForm . '_Torna');
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDomain:
                        $this->Dettaglio($_POST['rowid']);
                        Out::show($this->nameForm . '_Torna');
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella',
                                'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridDomain:
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($_POST['id'],
                                        array('sqlDB' => $this->ITALWEB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPage('json');
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_MAIL_DOMAIN[DELMSG]':
                        if ($_POST[$this->nameForm . '_MAIL_DOMAIN']['DELMSG'] == 0) {
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '');
                            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '0');
                        } else {
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '3');
                            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '1');
                        }
                        break;
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $sql = $this->CreaSql();
                        $ita_grid01 = new TableView($this->gridDomain,
                                        array(
                                            'sqlDB' => $this->ITALWEB,
                                            'sqlQuery' => $sql));
                        $ita_grid01->setPageNum(1);
                        $ita_grid01->setPageRows($_POST[$this->gridDomain]['gridParam']['rowNum']);
                        $ita_grid01->setSortIndex('NAME');
                        $ita_grid01->setSortOrder('asc');
                        if (!$ita_grid01->getDataPage('json')) {
                            Out::msgStop("Selezione", "Nessun record trovato.");
                            $this->OpenRicerca();
                        } else {   // Visualizzo il risultato
                            Out::hide($this->divGes);
                            Out::hide($this->divRic);
                            Out::show($this->divRis);
                            $this->Nascondi();
                            Out::show($this->nameForm . '_AltraRicerca');
                            Out::show($this->nameForm . '_Nuovo');
                            Out::setFocus('', $this->nameForm . '_Nuovo');
                            TableView::enableEvents($this->gridDomain);
                        }
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_Nuovo':
                        $this->Nuovo();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $codice = $_POST[$this->nameForm . '_MAIL_DOMAIN']['NAME'];
                        $mailDomain_rec = $this->emlLib->getMailDomain($codice);
                        if (!$mailDomain_rec) {
                            $mailDomain_rec = $_POST[$this->nameForm . '_MAIL_DOMAIN'];
                            $insert_Info = 'Oggetto: ' . $mailDomain_rec['NAME'] . " " . $mailDomain_rec['DESCRIPTION'];
                            if ($this->insertRecord($this->ITALWEB, 'MAIL_DOMAIN', $mailDomain_rec, $insert_Info)) {
                                $this->Dettaglio($mailDomain_rec['NAME'], 'name');
                            }
                        } else {
                            Out::msgInfo("Nome del Dominio già  presente", "Inserire un nuovo Dominio.");
                            Out::setFocus('', $this->nameForm . '_MAIL_DOMAIN[NAME]');
                        }
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $mailDomain_rec = $_POST[$this->nameForm . '_MAIL_DOMAIN'];
                        if ($mailDomain_rec['DELMSG'] == 1 && $mailDomain_rec['DELWAIT'] < 3) {
                            $mailDomain_rec['DELWAIT'] = 3;
                            Out::valore($this->nameForm . '_MAIL_DOMAIN[WAITMSG]', 3);
                        }
                        $update_Info = 'Oggetto: ' . $mailDomain_rec['NAME'] . " " . $mailDomain_rec['DESCRIPTION'];
                        if ($this->updateRecord($this->ITALWEB, 'MAIL_DOMAIN', $mailDomain_rec, $update_Info)) {
                            $this->Dettaglio($mailDomain_rec['ROWID']);
                        }
                        break;
                    case $this->nameForm . '_Cancella':
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella'
                                , 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella'
                                , 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $mailDomain_rec = $_POST[$this->nameForm . '_MAIL_DOMAIN'];
                        $delete_Info = 'Oggetto: ' . $mailDomain_rec['NAME'] . " "
                                . $mailDomain_rec['DESCRIPTION'];
                        if ($this->deleteRecord(
                                        $this->ITALWEB, 'MAIL_DOMAIN', $mailDomain_rec['ROWID'], $delete_Info)) {
                            $this->OpenRicerca();
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        Out::hide($this->divGes);
                        Out::hide($this->divRic);
                        Out::show($this->divRis);
                        $this->Nascondi();
                        Out::show($this->nameForm . '_AltraRicerca');
                        Out::show($this->nameForm . '_Nuovo');
                        Out::setFocus('', $this->nameForm . '_Nuovo');
                        TableView::enableEvents($this->gridDomain);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, '', "1", '');
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, 'ssl', "0", 'ssl');
        Out::select($this->nameForm . '_MAIL_DOMAIN[SMTPSECURE]', 1, 'tls', "0", 'tls');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, '', "1", '');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, 'ssl', "0", 'ssl');
        Out::select($this->nameForm . '_MAIL_DOMAIN[POP3SECURE]', 1, 'tls', "0", 'tls');
    }

    function OpenRicerca() {
        Out::show($this->divRic);
        Out::hide($this->divRis);
        Out::hide($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Elenca');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
    }

    function AzzeraVariabili() {
        Out::clearFields($this->nameForm, $this->divRic);
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_MAIL_DOMAIN[ROWID]', '');
        TableView::disableEvents($this->gridDomain);
        TableView::clearGrid($this->gridDomain);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_Torna');
    }

    function Nuovo() {
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        $this->AzzeraVariabili();
        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_AltraRicerca');
        Out::setFocus('', $this->nameForm . '_MAIL_DOMAIN[NAME]');
    }

    function CreaSql() {
        $sql = "SELECT * FROM MAIL_DOMAIN WHERE ROWID=ROWID";
        if ($_POST[$this->nameForm . '_Codice'] != "") {
            $sql .= " AND NAME LIKE '%" . addslashes($_POST[$this->nameForm . '_Codice']) . "%'";
        }
        if ($_POST[$this->nameForm . '_Descrizione'] != "") {
            $sql .= " AND DESCRIPTION LIKE '%" . addslashes($_POST[$this->nameForm . '_Descrizione']) . "%'";
        }
//        App::log($sql);
        return $sql;
    }

    function Dettaglio($indice, $tipo = 'rowid') {
        $mailDomain_rec = $this->emlLib->getMailDomain($indice, $tipo);
        $open_Info = 'Oggetto: ' . $mailDomain_rec['NAME'] . " " . $mailDomain_rec['DESCRIPTION'];
        $this->openRecord($this->ITALWEB, 'MAIL_DOMAIN', $open_Info);
        $this->Nascondi();
        Out::valori($mailDomain_rec, $this->nameForm . '_MAIL_DOMAIN');
        if ($mailDomain_rec['DELMSG'] == 0) {
            Out::valore($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', '');
            Out::attributo($this->nameForm . '_MAIL_DOMAIN[DELWAIT]', 'readonly', '0');
        } else {
            Out::attributo($this->nameForm . '_DELWAIT', 'readonly', '1');
        }
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_AltraRicerca');
//        Out::show($this->nameForm . '_Torna');
        Out::hide($this->divRic);
        Out::hide($this->divRis);
        Out::show($this->divGes);
        TableView::disableEvents($this->gridDomain);
        Out::setFocus('', $this->nameForm . '_MAIL_DOMAIN[NAME]');
    }

}

?>