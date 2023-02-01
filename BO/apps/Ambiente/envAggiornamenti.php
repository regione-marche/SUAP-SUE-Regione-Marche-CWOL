<?php

/**
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Ambiente
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2015 Italsoft snc
 * @license 
 * @version    23.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envDBPatch.class.php';
include_once ITA_BASE_PATH . '/updater/itaUpdaterFactory.class.php';
include_once ITA_BASE_PATH . '/updater/itaDownloader.class.php';

function envAggiornamenti() {
    $envAggiornamenti = new envAggiornamenti();
    $envAggiornamenti->parseEvent();
    return;
}

class envAggiornamenti extends itaModel {

    public $nameForm = "envAggiornamenti";
    public
            $gridStorico = "envAggiornamenti_gridStorico";
    public $ente;
    public $dbName;

    function __construct() {
        parent::__construct();
        $this->dbName = $_POST[$this->nameForm . '_DBName'] ? $_POST[$this->nameForm . '_DBName'] : false;
        $this->ente = $_POST[$this->nameForm . '_Ente'] ? $_POST[$this->nameForm . '_Ente'] : false;
    }

    function __destruct
    () {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->creaCombo();
                $this->openRicerca();
                break;

            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridStorico:
                        $schema_rec = ItaDB::DBSQLSelect($this->DB, "SELECT VERSIONE FROM SCHEMA_VERSION WHERE ROWID = '{$_POST['rowid']}'", false);
                        $filename = "{$this->dbName}_{$schema_rec ['VERSIONE']}.sql";
                        $path = $this->getPath() . "/$filename";
                        Out::msgInfo($filename, htmlspecialchars(file_get_contents($path)));
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridStorico:
                        TableView::clearGrid($this->gridStorico);
                        $sql = $this->creaSql();
                        $ita_grid01 = new TableView($this->gridStorico, array('sqlDB' => $this->DB, 'sqlQuery' => $sql));
                        $ita_grid01->setPageNum($_POST['page']);
                        $ita_grid01->setPageRows($_POST['rows']);
                        $ita_grid01->setSortIndex($_POST['sidx']);
                        $ita_grid01->setSortOrder($_POST['sord']);
                        $ita_grid01->getDataPageFromArray('json', $this->elaboraRecord($ita_grid01->getDataArray()));
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Apri':
                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Dettagli':
                        Out::msgInput("Selezione Ente", array(
                            'id' => $this->nameForm . '_EnteSelect',
                            'name' => $this->nameForm . '_EnteSelect', 'type' => 'select'
                                ), array(
                            'F5 - Conferma' => array(
                                'id' => $this->nameForm . '_returnSelezioneEnte',
                                'class' => 'ita-button-control',
                                'model' => $this->nameForm, 'shortCut' => 'f5'
                            )
                                ), $this->nameForm);

                        foreach (App::getEnti() as $k => $v) {
                            Out::select($this->nameForm . '_EnteSelect', '1', $v['codice'], '0', $k . " - " . $v['codice']);
                        }
                        break;

                    case $this->nameForm . '_returnSelezioneEnte' :
                        $ente = $_POST[$this->nameForm . '_EnteSelect'];
                        Out::valore($this->nameForm . '_Ente', $ente);

                        try {
                            $this->DB = ItaDB::DBOpen($this->dbName, $ente);
                        } catch (Exception $e) {
                            Out::msgStop("Errore", "Errore in apertura DB -> " . $e->getMessage());
                            break;
                        }

                        if (!$this->DB->exists()) {
                            Out::msgStop("Errore", "Il DB {$this->dbName
                                    }$ente non esiste.");
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case $this->nameForm . '_Aggiorna':
                        Out::msgQuestion("Aggiornamento", "Eseguire l'aggiornamento in tutti i DB {$this->dbName}?", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaAggiorna', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;

                    case $this->nameForm . '_Connect':
                        $updaterClient = itaUpdaterFactory::getClient();
                        if (!$updaterClient) {
                            Out::msgStop("Errore", "Istanzaz Client Fallita");
                            break;
                        }

                        if (!$updaterClient->connect()) {
                            Out::msgStop("", $updaterClient->getLastError());
                            break;
                        }
                        Out::msgInfo("Success", "Connessione Riuscita");
                        break;
                    case $this->nameForm . '_Download':

                        $downloader = itaDownloader::newInstance();
                        if (!$downloader) {
                            Out::msgStop("Errore","Errore creazione oggetto downloader");
                            break;
                        }
                        $result = $downloader->download(true);
                        if(!$result){
                            Out::msgStop("Errore",$downloader->getLastError());
                            break;
                        }
                        
//                        $updaterClient = itaUpdaterFactory::getClient();
//                        if (!$updaterClient) {
//                            Out::msgStop("Errore", "Istanzaz Client Fallita");
//                            break;
//                        }
//
//                        if (!$updaterClient->connect()) {
//                            Out::msgStop("Connect", $updaterClient->getLastError());
//                            break;
//                        }
//                        if (!$updaterClient->download()) {
//                            Out::msgStop("Download", $updaterClient->getLastError());
//                            break;
//                        }
                        Out::msgInfo("Risultato", print_r($result,true));
                        break;
                    case $this->nameForm . '_Fetch':
                        $updaterClient = itaUpdaterFactory::getClient();
                        if (!$updaterClient) {
                            Out::msgStop("Errore", "Istanzaz Client Fallita");
                            break;
                        }

                        if (!$updaterClient->connect()) {
                            Out::msgStop("Connect", $updaterClient->getLastError());
                            break;
                        }
                        $retFetch = $updaterClient->verify();
                        if ($retFetch === false) {
                            Out::msgStop("Download", $updaterClient->getLastError());
                            break;
                        }
                        Out::msgInfo("Success", print_r($retFetch, true));
                        break;
                    case $this->nameForm . '_Pull':
                        $updaterClient = itaUpdaterFactory::getClient();
                        if (!$updaterClient) {
                            Out::msgStop("Errore", "Istanzaz Client Fallita");
                            break;
                        }

                        if (!$updaterClient->connect()) {
                            Out::msgStop("Connect", $updaterClient->getLastError());
                            break;
                        }
                        $retPull = $updaterClient->refresh();
                        if (!$retPull) {
                            Out::msgStop("Download", $updaterClient->getLastError());
                            break;
                        }
                        Out::msgInfo("Success", print_r($retPull, true));
                        break;
                    case $this->nameForm . '_Status':
                        $updaterClient = itaUpdaterFactory::getClient();
                        if (!$updaterClient) {
                            Out::msgStop("Errore", "Istanzaz Client Fallita");
                            break;
                        }

                        if (!$updaterClient->connect()) {
                            Out::msgStop("Connect", $updaterClient->getLastError());
                            break;
                        }
                        $retStatus = $updaterClient->status();
                        if (!$retStatus) {
                            Out::msgStop("Download", $updaterClient->getLastError());
                            break;
                        }
                        Out::msgInfo("Success", print_r($retStatus, true));
                        break;
                    case $this->nameForm . '_ConfermaAggiorna':
                        $envDBPatch = new envDBPatch();
                        $domains = array('I99', 'I97', 'I98');

                        foreach ($domains as $domain) {
                            if (!$envDBPatch->DBPatch($this->dbName, $domain)) {
                                Out::msgStop("Errore", $envDBPatch->getErrMessage());
                                App::log($envDBPatch->getResults());

                                break;
                            }
                        }

                        Out::

                        msgInfo("Aggiornamento DB", 'OK');

                        break;

                    case $this->nameForm
                    . '_Torna':
                        $this->openRicerca();
                        break;

                    case 'close-portlet' :
                        $this->returnToParent();

                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function mostraForm($div) {
        Out::hide($this->
                nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divR isultato');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons) {

        Out::hide($this->nameForm . '_Apri');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Dettagli');
        Out::hide($this->
                nameForm . '_Torna');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function elaboraRecord($tab) {
        foreach (
        $tab as &$rec) {
            $rec['TIMESTAMP'] = date('d/m/Y H:i:s', $rec['TIMESTAMP']);
        }
        return $tab;
    }

    public function creaSql() {
        $sql = "SELECT
                    *
                FROM
                    SCHEMA_VERSION";

        return $sql;
    }

    public function creaCombo() {
        //Out::select($this->nameForm . '_DBName', 1, 'PROT', '0', 'PROTXX');
        Out::select($this->nameForm . '_DBName', 1, 'CARLO', '0', 'CARLO');
    }

    public function openRicerca() {
        Out::clearFields($this->nameForm);
        TableView::disableEvents($this->gridStorico);

        $this->mostraForm('divRicerca');
        $this->mostraButtonBar(array(
            'Aggiorna', 'Dettagli'));

        Out::setFocus($this->nameForm, $this->nameForm . '_DBName');
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('Torna'));

        TableView::enableEvents($this->gridStorico);
        TableView::clearGrid($this->gridStorico);
        TableView::reload($this->gridStorico);
    }

}

?>