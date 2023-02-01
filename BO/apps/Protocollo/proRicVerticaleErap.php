<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    06.10.2011
 * @link
 * @see 
 * @since 
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proRicVerticaleErap() {
    $proRicVerticaleErap = new proRicVerticaleErap();
    $proRicVerticaleErap->parseEvent();
    return;
}

class proRicVerticaleErap extends itaModel {

    const ClassIconButton = 'ita-icon-find-16x16';
    const ClassTitle = 'Ricerca Soggetti Locatari';

    public $nameForm = "proRicVerticaleErap";
    public $INSIGECO_DB;
    public $gridVerticale = "proRicVerticaleErap_gridVerticale";
    public $gridFilters = array();
    public $FiltriPersonali = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->INSIGECO_DB = ItaDB::DBOpen('INSIGECODB', '');
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->FiltriPersonali = App::$utente->getKey($this->nameForm . '_FiltriPersonali');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
            App::$utente->setKey($this->nameForm . '_FiltriPersonali', $this->FiltriPersonali);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->FiltriPersonali = array();
                $this->CreaCombo();
                $this->loadModelConfig();
                $this->caricaConfigurazioni();
                Out::setFocus('', $this->nameForm . '_Ricerca');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                $Identificativo = $_POST['rowid'];
                list($id,$presidio) = explode('_', $Identificativo);


                $sql = "SELECT * FROM dbo.V_x_Protocollo  WHERE Identificativo = '$id' AND Presidio = '$presidio' ";
                $Locatario_rec = itaDB::DBSQLSelect($this->INSIGECO_DB, $sql, false);
               

                $_POST = array();
                $_POST['event'] = $this->returnEvent;
                $_POST['retid'] = $this->returnID;
                $_POST['PRONOM'] = $Locatario_rec['Identificativo'] . ' - ' . $Locatario_rec['Nominativo'];
                $_POST['PROFIS'] = $Locatario_rec['CodiceFiscale'];
                $_POST['PROIND'] = $Locatario_rec['Indirizzo'];
                $_POST['PROCIT'] = $Locatario_rec['Località'];
                //$_POST['PROPRO'] = $Locatario_rec['Provincia'];//non presente.
                $_POST['PROCAP'] = $Locatario_rec['CAP'];
                $_POST['PROMAIL'] = $Locatario_rec['PEC'] ? $Locatario_rec['PEC'] : $Locatario_rec['EMail']; //Precedenza a PEC e poi Mail.
                $returnModel = $this->returnModel;
                if (is_array($returnModel)) {
                    $returnModelOrig = $returnModel['nameFormOrig'];
                    $returnModel = $returnModel['nameForm'];
                }
                if (!$returnModelOrig) {
                    $returnModelOrig = $returnModel;
                }
                $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
                $returnObj->setEvent($this->returnEvent);
                $returnObj->parseEvent();
                $this->returnToParent();
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridVerticale:
                        $this->setGridFilters();
                        $this->CaricaGriglia($this->gridFilters);
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RICERCA[Nominativo]':
                        /* new suggest */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->INSIGECO_DB->strUpper('Nominativo') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM dbo.V_x_Protocollo  WHERE " . $where;
                        if ($this->FiltriPersonali['Presidio']) {
                            $sql.=" AND Presidio = '" . $this->FiltriPersonali['Presidio'] . "'";
                        }
                        if ($this->FiltriPersonali['Localita']) {
                            $sql.=" AND Località = '" . $this->FiltriPersonali['Localita'] . "'";
                        }
                        $Amministrazioni_tab = itaDB::DBSQLSelect($this->INSIGECO_DB, $sql, true);
                        if (count($Amministrazioni_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($Amministrazioni_tab as $Amministrazioni_rec) {
                                itaSuggest::addSuggest($Amministrazioni_rec['Nominativo'], array($this->nameForm . "_RICERCA[Codice]" => $Amministrazioni_rec['Identificativo']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ricerca':
                        $this->setGridFilters();
                        $this->CaricaGriglia($this->gridFilters);
                        break;
                    case $this->nameForm . '_RICERCA[Codice]':

                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Cerca':
                        $this->setGridFilters();
                        $this->CaricaGriglia($this->gridFilters);
                        break;
                    case $this->nameForm . '_Denominazione_butt';
                        break;

                    case $this->nameForm . '_SalvaImpostazioni':
                        $this->setConfig();
                        $this->loadModelConfig();
                        Out::msgInfo("Profili Ricerca Verticale", "<br><br>Il profilo preferito è stato salvato.<br>");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {

                    case $this->nameForm . '_Regione':

                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_gridFilters');
        App::$utente->removeKey($this->nameForm . '_FiltriPersonali');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function CreaCombo() {
        Out::html($this->nameForm . '_RICERCA[Presidio]', '');
        $sql = "SELECT DISTINCT(Presidio) AS PRESIDIO FROM dbo.V_x_Protocollo ";
        $Presidio_tab = itaDB::DBSQLSelect($this->INSIGECO_DB, $sql, true);
        Out::select($this->nameForm . '_RICERCA[Presidio]', 1, '', 0, 'Seleziona...');
        foreach ($Presidio_tab as $Presidio_rec) {
            Out::select($this->nameForm . '_RICERCA[Presidio]', 1, $Presidio_rec['PRESIDIO'], 0, $Presidio_rec['PRESIDIO']);
        }
    }

    private function CreaComboPresidio($Presidio) {
        Out::html($this->nameForm . '_Presidio', '');
        $sql = "SELECT DISTINCT(Presidio) AS PRESIDIO FROM dbo.V_x_Protocollo ";
        $Presidio_tab = itaDB::DBSQLSelect($this->INSIGECO_DB, $sql, true);
        Out::select($this->nameForm . '_Presidio', 1, '', 0, 'Seleziona...');
        foreach ($Presidio_tab as $Presidio_rec) {
            Out::select($this->nameForm . '_Presidio', 1, $Presidio_rec['PROVINCIA'], 0, $Presidio_rec['PROVINCIA']);
        }
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['Nominativo'] != '') {
            $this->gridFilters['Nominativo'] = $_POST['Nominativo'];
        }
        if ($_POST['Identificativo'] != '') {
            $this->gridFilters['Identificativo'] = $_POST['Identificativo'];
        }
        if ($_POST['Titolo'] != '') {
            $this->gridFilters['Titolo'] = $_POST['Titolo'];
        }
        if ($_POST['CodiceFiscale'] != '') {
            $this->gridFilters['CodiceFiscale'] = $_POST['CodiceFiscale'];
        }
        if ($_POST['Indirizzo'] != '') {
            $this->gridFilters['Indirizzo'] = $_POST['Indirizzo'];
        }
        if ($_POST['Localita'] != '') {
            $this->gridFilters['Località'] = $_POST['Localita'];
        }
        if ($_POST['CodiceUI'] != '') {
            $this->gridFilters['CodiceUI'] = $_POST['CodiceUI'];
        }
    }

    public function CreaSql($filters = array()) {

        $ricerca_rec = $_POST[$this->nameForm . '_RICERCA'];
        if ($_POST[$this->nameForm . '_Ricerca']) {
            $ricerca = $_POST[$this->nameForm . '_Ricerca'];
        }
        $sql = "SELECT * FROM dbo.V_x_Protocollo WHERE";
        if ($ricerca_rec['Codice']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('Identificativo') . " LIKE '%" . strtoupper($ricerca_rec['Codice']) . "%'  AND ";
        }
        if ($ricerca_rec['Nominativo']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('Nominativo') . " LIKE '%" . strtoupper($ricerca_rec['Nominativo']) . "%'  AND ";
        }
        if ($ricerca_rec['CodiceFiscale']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('CodiceFiscale') . " LIKE '%" . strtoupper($ricerca_rec['CodiceFiscale']) . "%'  AND ";
        }
        if ($ricerca_rec['Indirizzo']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('Indirizzo') . " LIKE '%" . strtoupper($ricerca_rec['Indirizzo']) . "%'  AND ";
        }
        if ($ricerca_rec['Localita']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('Località') . " LIKE '%" . strtoupper($ricerca_rec['Localita']) . "%'  AND ";
        }
        if ($ricerca_rec['Presidio']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('Presidio') . " LIKE '%" . strtoupper($ricerca_rec['Presidio']) . "%'  AND ";
        }
        if ($ricerca_rec['CodiceUI']) {
            $where.= " " . $this->INSIGECO_DB->strUpper('CodiceUI') . " LIKE '%" . strtoupper($ricerca_rec['CodiceUI']) . "%'  AND ";
        }
        // Ricerca Generica sul Nome
        if ($ricerca) {
            $ricercaArr = array();
            $ricercaArr = explode(" ", $ricerca);
            $where .= "  (";
            $where .= $this->createWherePart($this->INSIGECO_DB->strUpper('Nominativo'), $ricercaArr) . " ";
            $where .= ")  AND ";
        }
        // Filtri
        if ($filters) {
            foreach ($filters as $key => $value) {
                $value = str_replace("'", "\'", $value);
                $where.= " " . $this->INSIGECO_DB->strUpper($key) . " LIKE '%" . strtoupper($value) . "%'  AND ";
            }
        }

        $where = substr($where, 0, -5);
        $sql.=$where;
//        Out::msgInfo('sql', $sql);
        return $sql;
    }

    public function CaricaGriglia($filters = array()) {
        TableView::clearGrid($this->gridVerticale);
        $sql = $this->CreaSql($filters);
        TableView::disableEvents($this->gridVerticale);
        $ita_grid01 = new TableView(
                $this->gridVerticale, array(
            'sqlDB' => $this->INSIGECO_DB,
            'sqlQuery' => $sql));

        $ita_grid01->setPageRows(14);
        $ita_grid01->setSortOrder('asc');
        if (!$_POST['page'])
            $_POST['page'] = 1;

        $ita_grid01->setPageNum($_POST['page']);
        $ita_grid01->setSortIndex('Nominativo');
        if ($_POST['sidx']) {
            $ita_grid01->setSortIndex($_POST['sidx']);
        }
        if ($_POST['sord']) {
            $ita_grid01->setSortOrder($_POST['sord']);
        }
        $Tabella_tab = $ita_grid01->getDataArray();
        $Tabella_tab = $this->ElaboraRecords($Tabella_tab);
        $ita_grid01->getDataPageFromArray('json', $Tabella_tab);
        TableView::enableEvents($this->gridVerticale);
        return;
    }

    public function ElaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['IDUNIVOCO'] = $Result_tab[$key]['Identificativo'] . '_' . $Result_tab[$key]['Presidio'];
            $Result_tab[$key]['Localita'] = $Result_tab[$key]['Località'];
        }
        return $Result_tab;
    }

    private function setConfig() {
        $ParamRicerca = $_POST[$this->nameForm . '_RICERCA'];
        $parametri = array(
            $this->nameForm . '_RICERCA[Presidio]' => $ParamRicerca['Presidio'],
            $this->nameForm . '_RICERCA[Localita]' => $ParamRicerca['Localita'],
        );
        $this->setCustomConfig("CAMPIDEFAULT/DATIVERT", $parametri);
        $this->saveModelConfig();
    }

    private function caricaConfigurazioni() {
        $parametri = $this->getCustomConfig('CAMPIDEFAULT/DATIVERT');
        foreach ($parametri as $key => $valore) {
            if ($key == $this->nameForm . '_RICERCA[Presidio]') {
                $this->FiltriPersonali['Presidio'] = $valore;
            }
            if ($key == $this->nameForm . '_RICERCA[Localita]') {
                $this->FiltriPersonali['Localita'] = $valore;
            }
            Out::valore($key, $valore);
        }
    }

    private function createWherePart($campo, $ricercaArr) {
        $wherePart = "";
        foreach ($ricercaArr as $key => $value) {
            $value = addslashes(strtoupper($value));
            $wherePart .= "$campo LIKE '%$value%' AND ";
        }
        $wherePart = substr($wherePart, 0, -4);
        return $wherePart;
    }

}

?>
