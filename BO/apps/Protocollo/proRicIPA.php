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

function proRicIPA() {
    $proRicIPA = new proRicIPA();
    $proRicIPA->parseEvent();
    return;
}

class proRicIPA extends itaModel {

    public $nameForm = "proRicIPA";
    public $COMUNI_DB;
    public $CodiceAmm;
    public $gridPECAmm = "proRicIPA_gridPECAmm";
    public $gridFilters = array();
    public $FiltriPersonali = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->gridFilters = App::$utente->getKey($this->nameForm . '_gridFilters');
            $this->FiltriPersonali = App::$utente->getKey($this->nameForm . '_FiltriPersonali');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->CodiceAmm = App::$utente->getKey($this->nameForm . '_CodiceAmm');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_CodiceAmm', $this->CodiceAmm);
            App::$utente->setKey($this->nameForm . '_gridFilters', $this->gridFilters);
            App::$utente->setKey($this->nameForm . '_FiltriPersonali', $this->FiltriPersonali);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CodiceAmm = '';
                $this->FiltriPersonali = array();
                $this->CreaCombo();
                $this->loadModelConfig();
                $this->caricaConfigurazioni();
                Out::setFocus('', $this->nameForm . '_Ricerca');
                break;
            case 'dbClickRow':
            case 'editGridRow':
                $keys = explode(' ', $_POST['rowid']);
                $Chiavi['rowid'] = $keys[1];
                $Chiavi['tabella'] = $keys[0];
                $Tipo = substr($Chiavi['tabella'], 0, 3);
                switch ($Tipo) {
                    case 'AMM':
                        $sql = $this->creaSqlAmministrazioni('', '', $Chiavi['rowid']);
                        break;
                    case 'AOO':
                        $sql = $this->creaSqlAOO('', '', $Chiavi['rowid']);
                        break;
                    case 'UO':
                        $sql = $this->creaSqlUO('', '', $Chiavi['rowid']);
                        break;
                }
                $PecAmministrazione_rec = itaDB::DBSQLSelect($this->COMUNI_DB, $sql, false);

                //
                //Chiusura dialog e return dei valori
                //
                $_POST = array();
                $_POST['event'] = $this->returnEvent;
                $_POST['retid'] = $this->returnID;
                $_POST['rowid'] = $Chiavi['rowid'];
                $_POST['tabella'] = $Chiavi['tabella'];
                $_POST['PRONOM'] = $PecAmministrazione_rec['DESCRIZIONECOMPLETA'];
                $_POST['PROIND'] = $PecAmministrazione_rec['INDIRIZZO'];
                $_POST['PROCIT'] = $PecAmministrazione_rec['COMUNE'];
                $_POST['PROPRO'] = $PecAmministrazione_rec['PROVINCIA'];
                $_POST['PROCAP'] = $PecAmministrazione_rec['CAP'];
                $_POST['PROMAIL'] = $PecAmministrazione_rec['MAIL'];
                $_POST['PROCIV'] = $civico;
                //App::log($Chiavi);
                $returnModel = $this->returnModel;
                $returnModelOrig = $returnModel;
                if (is_array($returnModel)) {
                    $returnModelOrig = $returnModel['nameFormOrig'];
                    $returnModel = $returnModel['nameForm'];
                }
                $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
                $returnObj->setEvent($this->returnEvent);
                $returnObj->parseEvent();
                $this->returnToParent();




                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridPECAmm:
                        $this->setGridFilters();
                        $this->CaricaGrigliaPecAmministrazione($this->gridFilters);
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Denominazione':
                        /* new suggest */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->COMUNI_DB->strUpper('DES_AMM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM AMMINISTRAZIONI WHERE " . $where;
                        $sql.= " AND COD_AMM <> 'cod_amm' ";
                        if ($this->FiltriPersonali['Regione']) {
                            $sql.=" AND REGIONE = '" . $this->FiltriPersonali['Regione'] . "'";
                        }
                        if ($this->FiltriPersonali['Provincia']) {
                            $sql.=" AND PROVINCIA = '" . $this->FiltriPersonali['Provincia'] . "'";
                        }
                        $Amministrazioni_tab = itaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
                        if (count($Amministrazioni_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($Amministrazioni_tab as $Amministrazioni_rec) {
                                itaSuggest::addSuggest($Amministrazioni_rec['DES_AMM'], array($this->nameForm . "_Codice" => $Amministrazioni_rec['COD_AMM']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ricerca':
                        $this->setGridFilters();
                        $this->CaricaGrigliaPecAmministrazione($this->gridFilters);
                        break;
                    case $this->nameForm . '_Codice':
                        if ($_POST[$this->nameForm . '_Codice']) {
                            $this->DecodificaPecAmministrazione($_POST[$this->nameForm . '_Codice'], 'codice');
                        } else {
                            $this->CodiceAmm = '';
                            Out::valore($this->nameForm . '_Codice', '');
                            Out::valore($this->nameForm . '_Denominazione', '');
                            TableView::clearGrid($this->gridPECAmm);
                        }
                        Out::setFocus('', $this->nameForm . '_Ricerca');
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Cerca':
                        $this->setGridFilters();
                        $this->CaricaGrigliaPecAmministrazione($this->gridFilters);
                        break;
                    case $this->nameForm . '_Denominazione_butt';
                        $where = 'WHERE 1 ';
                        $where.= " AND COD_AMM <> 'cod_amm' ";
                        if ($this->FiltriPersonali['Regione']) {
                            $where.=" AND REGIONE = '" . $this->FiltriPersonali['Regione'] . "'";
                        }
                        if ($this->FiltriPersonali['Provincia']) {
                            $where.=" AND PROVINCIA = '" . $this->FiltriPersonali['Provincia'] . "'";
                        }
                        proRic::proRicIpa($this->nameForm, 'returnIPA', $where);
                        break;

                    case $this->nameForm . '_SalvaImpostazioni':
                        $this->setConfig();
                        $this->loadModelConfig();
                        Out::msgInfo("Profili Ricerca IPA", "<br><br>Il profilo preferito è stato salvato.<br>");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Provincia':
                        $this->FiltriPersonali['Provincia'] = $_POST[$this->nameForm . '_Provincia'];
                        $this->CodiceAmm = '';
                        Out::valore($this->nameForm . '_Codice', '');
                        Out::valore($this->nameForm . '_Denominazione', '');
                        TableView::clearGrid($this->gridPECAmm);
                        Out::setFocus('', $this->nameForm . '_Ricerca');
                        break;
                    case $this->nameForm . '_Regione':
                        $this->FiltriPersonali['Regione'] = $_POST[$this->nameForm . '_Regione'];
                        $this->FiltriPersonali['Provincia'] = '';
                        if ($_POST[$this->nameForm . '_Regione']) {
                            $this->CreaComboProvincia($_POST[$this->nameForm . '_Regione']);
                        } else {
                            Out::html($this->nameForm . '_Provincia', '');
//                            Out::select($this->nameForm . '_Provincia', 1, '', 0, 'Seleziona una regione...');
                        }
                        $this->CodiceAmm = '';
                        Out::valore($this->nameForm . '_Codice', '');
                        Out::valore($this->nameForm . '_Denominazione', '');
                        TableView::clearGrid($this->gridPECAmm);
                        Out::setFocus('', $this->nameForm . '_Ricerca');
                        break;
                }
                break;

            case 'returnIPA':
                $this->DecodificaPecAmministrazione($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_CodiceAmm');
        App::$utente->removeKey($this->nameForm . '_gridFilters');
        App::$utente->removeKey($this->nameForm . '_FiltriPersonali');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function CreaCombo() {
        $sql = "SELECT REGIONE FROM AMMINISTRAZIONI WHERE COD_AMM <> 'cod_amm' GROUP BY REGIONE ORDER BY REGIONE ASC";
        $Regioni_tab = itaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
        Out::select($this->nameForm . '_Regione', 1, '', 0, 'Seleziona...');
        foreach ($Regioni_tab as $Regioni_rec) {
            Out::select($this->nameForm . '_Regione', 1, $Regioni_rec['REGIONE'], 0, $Regioni_rec['REGIONE']);
        }
    }

    private function CreaComboProvincia($Regione) {
        Out::html($this->nameForm . '_Provincia', '');
        $sql = "SELECT PROVINCIA FROM AMMINISTRAZIONI WHERE REGIONE = '$Regione' GROUP BY PROVINCIA  ORDER BY PROVINCIA ASC";
        $Regioni_tab = itaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
        Out::select($this->nameForm . '_Provincia', 1, '', 0, 'Seleziona...');
        foreach ($Regioni_tab as $Regioni_rec) {
            Out::select($this->nameForm . '_Provincia', 1, $Regioni_rec['PROVINCIA'], 0, $Regioni_rec['PROVINCIA']);
        }
    }

    public function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['DESCRIZIONE'] != '') {
            $this->gridFilters['DESCRIZIONE'] = $_POST['DESCRIZIONE'];
        }
        if ($_POST['MAIL'] != '') {
            $this->gridFilters['MAIL'] = $_POST['MAIL'];
        }
        if ($_POST['TIPO'] != '') {
            $this->gridFilters['TIPO'] = $_POST['TIPO'];
        }
        if ($_POST['TIPO_MAIL'] != '') {
            $this->gridFilters['TIPO_MAIL'] = $_POST['TIPO_MAIL'];
        }
    }

    public function creaSqlAmministrazioni($codice, $ricerca, $rowid) {
        $sql = "
        SELECT
            " . $this->COMUNI_DB->strConcat("'AMMINISTRAZIONI'", "' '", 'AMMINISTRAZIONI.ROWID') . " AS ROWID,
            AMMINISTRAZIONI.COD_AMM AS CODICEAMM,
            'AMM' AS TIPO, 
            AMMINISTRAZIONI.DES_AMM AS DESCRIZIONE,
            AMMINISTRAZIONI.DES_AMM AS DESCRIZIONECOMPLETA,
            AMMINISTRAZIONI.COMUNE,
            AMMINISTRAZIONI.PROVINCIA,
            AMMINISTRAZIONI.CAP,
            AMMINISTRAZIONI.INDIRIZZO,
            AMMINISTRAZIONI.MAIL1 AS MAIL,
            AMMINISTRAZIONI.TIPO_MAIL1 AS TIPO_MAIL
        FROM AMMINISTRAZIONI AMMINISTRAZIONI
        WHERE 1=1";

        if ($codice) {
            $sql .= " AND AMMINISTRAZIONI.COD_AMM = '$codice'";
        }

        if ($ricerca) {
            $ricercaArr = array();
            $ricercaArr = explode(" ", $ricerca);
            $sql .= " AND (";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM'), $ricercaArr) . " OR ";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.COMUNE'), $ricercaArr);
            $sql .= ")";
        }

//        if ($ricerca) {
//            $sql .= " AND (" . $this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%' OR 
//                               " . $this->COMUNI_DB->strUpper('AMMINISTRAZIONI.COMUNE') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%')";
//        }

        if ($rowid) {
            $sql .= " AND AMMINISTRAZIONI.ROWID = '$rowid'";
        }

        $sql.= " AND AMMINISTRAZIONI.COD_AMM <> 'cod_amm' ";
        if ($this->FiltriPersonali['Regione']) {
            $sql.=" AND AMMINISTRAZIONI.REGIONE = '" . $this->FiltriPersonali['Regione'] . "'";
        }
        if ($this->FiltriPersonali['Provincia']) {
            $sql.=" AND AMMINISTRAZIONI.PROVINCIA = '" . $this->FiltriPersonali['Provincia'] . "'";
        }

        return $sql;
    }

    public function creaSqlAOO($codice, $ricerca, $rowid) {
        $sql = "
        SELECT 
           " . $this->COMUNI_DB->strConcat("'AOO'", "' '", 'AOO.ROWID') . " AS ROWID,
            AOO.COD_AMM AS CODICEAMM, 
            'AOO' AS TIPO, 
            " . $this->COMUNI_DB->strConcat('AMMINISTRAZIONI.DES_AMM', "' - '", 'AOO.DES_AOO') . " AS DESCRIZIONE,            
            " . $this->COMUNI_DB->strConcat('AMMINISTRAZIONI.DES_AMM', "' '", 'AOO.DES_AOO') . " AS DESCRIZIONECOMPLETA,
            AOO.COMUNE,
            AOO.PROVINCIA,
            AOO.CAP,
            AOO.INDIRIZZO,
            AOO.MAIL1 AS MAIL,
            AOO.TIPO_MAIL1 AS TIPO_MAIL
        FROM AOO AOO
        LEFT OUTER JOIN AMMINISTRAZIONI AMMINISTRAZIONI ON AMMINISTRAZIONI.COD_AMM = AOO.COD_AMM
        WHERE 1=1";

        if ($codice) {
            $sql .= " AND AOO.COD_AMM = '$codice'";
        }
        if ($ricerca) {
            $ricercaArr = array();
            $ricercaArr = explode(" ", $ricerca);
            $sql .= " AND (";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM'), $ricercaArr) . " OR ";
            $sql .= $this->createWherePart($this->COMUNI_DB->strConcat($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM'), $this->COMUNI_DB->strUpper('AOO.DES_AOO')), $ricercaArr) . " OR ";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('AOO.COMUNE'), $ricercaArr);
            $sql .= ")";
        }

//        if ($ricerca) {
//            $sql .= " AND (" . $this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%' OR 
//                               " . $this->COMUNI_DB->strUpper('AOO.DES_AOO') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%' OR
//                               " . $this->COMUNI_DB->strUpper('AOO.COMUNE') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%')";
//        }

        if ($rowid) {
            $sql .= " AND AOO.ROWID = '$rowid'";
        }

        $sql.= " AND AMMINISTRAZIONI.COD_AMM <> 'cod_amm' ";
        if ($this->FiltriPersonali['Regione']) {
            $sql.=" AND AOO.REGIONE = '" . $this->FiltriPersonali['Regione'] . "'";
        }
        if ($this->FiltriPersonali['Provincia']) {
            $sql.=" AND AOO.PROVINCIA = '" . $this->FiltriPersonali['Provincia'] . "'";
        }

        return $sql;
    }

    public function creaSqlUO($codice, $ricerca, $rowid) {
        $sql = "
        SELECT 
            " . $this->COMUNI_DB->strConcat("'UO'", "' '", 'UO.ROWID') . " AS ROWID,
            UO.COD_AMM AS CODICEAMM, 
            'UO' AS TIPO, 
            " . $this->COMUNI_DB->strConcat('AMMINISTRAZIONI.DES_AMM', "' - '", 'UO.DES_OU') . " AS DESCRIZIONE,
            " . $this->COMUNI_DB->strConcat('AMMINISTRAZIONI.DES_AMM', "' '", 'UO.DES_OU') . " AS DESCRIZIONECOMPLETA,
            UO.COMUNE,
            UO.PROVINCIA,
            UO.CAP,
            UO.INDIRIZZO,
            UO.MAIL1 AS MAIL,
            UO.TIPO_MAIL1 AS TIPO_MAIL
        FROM UO UO
        LEFT OUTER JOIN AMMINISTRAZIONI AMMINISTRAZIONI ON AMMINISTRAZIONI.COD_AMM = UO.COD_AMM
        LEFT OUTER JOIN AOO AOO ON AOO.COD_AMM = UO.COD_AMM AND AOO.COD_AOO=UO.COD_AOO
        WHERE 1=1";

        if ($codice) {
            $sql .= " AND UO.COD_AMM = '$codice'";
        }

        if ($rowid) {
            $sql .= " AND UO.ROWID = '$rowid'";
        }

        if ($ricerca) {

            $ricercaArr = explode(" ", $ricerca);
            $sql .= " AND (";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM'), $ricercaArr) . " OR ";
            $sql .= $this->createWherePart($this->COMUNI_DB->strConcat($this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM'), $this->COMUNI_DB->strUpper('UO.DES_OU')), $ricercaArr) . " OR ";
            $sql .= $this->createWherePart($this->COMUNI_DB->strUpper('UO.COMUNE'), $ricercaArr);
            $sql .= ")";
        }


//        if ($ricerca) {
//            $sql .= " AND (" . $this->COMUNI_DB->strUpper('AMMINISTRAZIONI.DES_AMM') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%' OR 
//                    " . $this->COMUNI_DB->strUpper('UO.DES_OU') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%' OR
//                    " . $this->COMUNI_DB->strUpper('UO.COMUNE') . " LIKE '%" . addslashes(strtoupper($ricerca)) . "%')";
//        }

        $sql.= " AND AMMINISTRAZIONI.COD_AMM <> 'cod_amm' ";
        if ($this->FiltriPersonali['Regione']) {
            $sql.=" AND UO.REGIONE = '" . $this->FiltriPersonali['Regione'] . "'";
        }
        if ($this->FiltriPersonali['Provincia']) {
            $sql.=" AND UO.PROVINCIA = '" . $this->FiltriPersonali['Provincia'] . "'";
        }

        return $sql;
    }

    public function CreaSql($filters = array()) {
        if ($_POST[$this->nameForm . '_Codice']) {
            $codice = $_POST[$this->nameForm . '_Codice'];
        }
        if ($_POST[$this->nameForm . '_Ricerca']) {
            $ricerca = $_POST[$this->nameForm . '_Ricerca'];
        }

        $sql = "SELECT * 
                FROM (
                    " . $this->creaSqlAmministrazioni($codice, $ricerca) . "
                    UNION 
                    " . $this->creaSqlAOO($codice, $ricerca) . "
                    UNION 
                    " . $this->creaSqlUO($codice, $ricerca) . "                    
                )PEC ";

        $wherepec = " WHERE 1 ";
        if ($filters) {
            foreach ($filters as $key => $value) {
                $value = str_replace("'", "\'", $value);
                $wherepec.= " AND " . $this->COMUNI_DB->strUpper("PEC." . $key) . " LIKE '%" . strtoupper($value) . "%' ";
            }
        }
        $sql.=$wherepec;
        App::log($sql);
        return $sql;
    }

    public function DecodificaPecAmministrazione($Codice, $Tipo) {
        switch ($Tipo) {
            case 'rowid':
                $sql = "SELECT * FROM AMMINISTRAZIONI WHERE ROWID = $Codice ";
                break;
            case 'codice':
                $sql = "SELECT * FROM AMMINISTRAZIONI WHERE COD_AMM = '$Codice' ";
                break;
        }
        $Amministrazioni_rec = itaDB::DBSQLSelect($this->COMUNI_DB, $sql, false);
        if ($Amministrazioni_rec) {
            Out::valore($this->nameForm . '_Codice', $Amministrazioni_rec['COD_AMM']);
            Out::valore($this->nameForm . '_Denominazione', $Amministrazioni_rec['DES_AMM']);
        } else {
            Out::valore($this->nameForm . '_Codice', '');
            Out::valore($this->nameForm . '_Denominazione', '');
            Out::msgInfo("Attenzione", "Il codice inserito non trovato.");
        }
    }

    public function CaricaGrigliaPecAmministrazione($filters = array()) {
        TableView::clearGrid($this->gridPECAmm);
        $sql = $this->CreaSql($filters);
        TableView::disableEvents($this->gridPECAmm);
        $ita_grid01 = new TableView(
                $this->gridPECAmm, array(
            'sqlDB' => $this->COMUNI_DB, $sql,
            'sqlQuery' => $sql));
//        $PecAmministrazione_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
//        $PecAmministrazione_tab = $this->ElaboraRecords($PecAmministrazione_tab);
//        $ita_grid01 = new TableView(
//                $this->gridPECAmm, array('arrayTable' => $PecAmministrazione_tab,
//            'rowIndex' => 'idx')
//        );
        //$ita_grid01->setPageRows('200000');
        $ita_grid01->setPageRows(14);
        $ita_grid01->setPageNum('TIPO');
        $ita_grid01->setSortOrder('asc');
        if (!$_POST['page'])
            $_POST['page'] = 1;
        $ita_grid01->setPageNum($_POST['page']);
        if ($_POST['sidx']) {
            $ita_grid01->setSortIndex($_POST['sidx']);
        }
        if ($_POST['sord']) {
            $ita_grid01->setSortOrder($_POST['sord']);
        }
        $PecAmministrazione_tab = $ita_grid01->getDataArray();
        $PecAmministrazione_tab = $this->ElaboraRecords($PecAmministrazione_tab);
        $ita_grid01->getDataPageFromArray('json', $PecAmministrazione_tab);
        //$ita_grid01->getDataPage('json', true);
        TableView::enableEvents($this->gridPECAmm);
        return;
    }

    public function ElaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Messaggio = $Result_rec['DESCRIZIONE'] . ' <br>' . $Result_rec['INDIRIZZO'] . ' <br>' . $Result_rec['CAP'] . ' ' . $Result_rec['COMUNE'] . ' ' . $Result_rec['PROVINCIA'] . ' ';
            $Result_tab[$key]['DESCRIZIONE'] = "<div class=\"ita-html\">" . '<span style="width:20px;" title="' . htmlspecialchars($Messaggio) . '" class="ita-tooltip">' . $Result_tab[$key]['DESCRIZIONE'] . '</span></div>';
        }
        return $Result_tab;
    }

    private function setConfig() {
        $parametri = array(
            $this->nameForm . '_Regione' => $_POST[$this->nameForm . '_Regione'],
            $this->nameForm . '_Provincia' => $_POST[$this->nameForm . '_Provincia']
        );
        $this->setCustomConfig("CAMPIDEFAULT/DATI", $parametri);
        $this->saveModelConfig();
    }

    private function caricaConfigurazioni() {
        $parametri = $this->getCustomConfig('CAMPIDEFAULT/DATI');
        foreach ($parametri as $key => $valore) {
            if ($key == $this->nameForm . '_Regione') {
                $this->FiltriPersonali['Regione'] = $valore;
                if ($valore != '') {
                    $this->CreaComboProvincia($valore);
                }
            }
            if ($key == $this->nameForm . '_Provincia') {
                $this->FiltriPersonali['Provincia'] = $valore;
            }
            Out::valore($key, $valore);
        }
    }

    private function createWherePart($campo, $ricercaArr) {
        $wherePart = "(";
        foreach ($ricercaArr as $key => $value) {
            $value = addslashes(strtoupper($value));
            $wherePart .= "$campo LIKE '%$value%' AND ";
        }
        $wherePart .= "1=1)";
        return $wherePart;
    }

}

?>
