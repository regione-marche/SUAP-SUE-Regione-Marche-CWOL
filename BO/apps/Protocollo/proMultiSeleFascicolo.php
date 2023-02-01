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
include_once ITA_BASE_PATH . '/apps/Protocollo/proRicTitolario.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function proMultiSeleFascicolo() {
    $proMultiSeleFascicolo = new proMultiSeleFascicolo();
    $proMultiSeleFascicolo->parseEvent();
    return;
}

class proMultiSeleFascicolo extends itaModel {

    public $nameForm = "proMultiSeleFascicolo";
    public $proLib;
    public $PROT_DB;
    public $gridFascicoli = "proMultiSeleFascicolo_gridFascicoli";
    public $gridFiltersFascicoli = array();
    public $Titolario = array();
    public $ElencoFascicoli = array();
    public $Anapro_rec = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->gridFiltersFascicoli = App::$utente->getKey($this->nameForm . '_gridFiltersFascicoli');
            $this->Titolario = App::$utente->getKey($this->nameForm . '_Titolario');
            $this->ElencoFascicoli = App::$utente->getKey($this->nameForm . '_ElencoFascicoli');
            $this->Anapro_rec = App::$utente->getKey($this->nameForm . '_Anapro_rec');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ElencoFascicoli', $this->ElencoFascicoli);
            App::$utente->setKey($this->nameForm . '_Titolario', $this->Titolario);
            App::$utente->setKey($this->nameForm . '_Anapro_rec', $this->Anapro_rec);
        }
    }

    /**
     * 
     * @param type $ElencoFascicoli
     * Es.
     * array[0]['ORGKEY']='..'
     * array[1]['ORGKEY']='..'
     * array[N]['ORGKEY']='..'
     *
     */
    public function setElencoFascicoli($ElencoFascicoli) {
        $this->ElencoFascicoli = $ElencoFascicoli;
    }

    public function getElencoFascicoli() {
        return $this->ElencoFascicoli;
    }

    public function getTitolario() {
        return $this->Titolario;
    }

    public function setTitolario($Titolario) {
        $this->Titolario = $Titolario;
    }

    public function getAnapro_rec() {
        return $this->Anapro_rec;
    }

    public function setAnapro_rec($Anapro_rec) {
        $this->Anapro_rec = $Anapro_rec;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CaricaFascicoli();
                Out::hide($this->gridFascicoli . '_addGridRow');
                break;

            case 'addGridRow':
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridFascicoli:
                        $this->rowidSelezionato = $_POST['rowid'];
                        $Anaorg_rec = $this->proLib->GetAnaorg($_POST['rowid'], 'rowid');
                        $ProGes_rec = $this->proLib->GetProges($Anaorg_rec['ORGKEY'], 'codice');
                        $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $ProGes_rec['GESNUM'], 'gesnum');
                        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI]) {
                            Out::msgInfo("Fascicolazione", "Non possiedi i premessi per inserire il protocollo nel fascicolo, poiché non ti è stato trasmesso in Gestione.<br>Contattare il responsabile del fascicolo.");
                            break;
                        }
                        $Messaggio = 'Vuoi selezionare il fascicolo n. <b>' . $Anaorg_rec['ORGCOD'] . '</b> del <b>' . $Anaorg_rec['ORGANN'] . '</b> ?' . '<br><br><b>Oggetto del fascicolo:</b><br>' . $ProGes_rec['GESOGG'] . '';
                        $DescTitolario = $this->DecodificaDescTitolarioCorrente();
                        $Messaggio.='<br><br>' . $DescTitolario;
                        Out::msgQuestion("Selezione", $Messaggio, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaaSeleFascicolo', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSeleFascicolo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'cellSelect':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridFascicoli:
                        $this->setGridFiltersFascicoli();
                        $sql = $this->CreaSql($this->gridFiltersFascicoli);
                        $this->CaricaGrigliaFascicoli($sql);
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AnnullaSelezione':
                        $this->close();
                        break;

                    case $this->nameForm . '_ConfermaSelezione':
                        $RowidSelezionati = $_POST[$this->gridFascicoli]['gridParam']['selarrrow'];
                        if (!$RowidSelezionati) {
                            Out::msgInfo('Attenzione', 'Occorre selezionare almeno un fascicolo prima di poter procedere.');
                            break;
                        }
                        $RowidSelezionati = explode(',', $RowidSelezionati);
                        $FascicoliSelezionati = array();
                        foreach ($RowidSelezionati as $rowid) {
                            foreach ($this->ElencoFascicoli as $Fascicolo) {
                                if ($rowid == $Fascicolo['ROWID']) {
//                                    $FascSel = array();
//                                    $FascSel['ROWID'] = $Fascicolo['ROWID'];
//                                    $FascSel['ORGKEY'] = $Fascicolo['ORGKEY'];
//                                    $FascSel['PRONUMPARENT'] = $Fascicolo['PRONUMPARENT'];
//                                    $FascSel['PROPARPARENT'] = $Fascicolo['PROPARPARENT'];
                                    $FascicoliSelezionati[] = $Fascicolo;
                                }
                            }
                        }
//                        App::log($RowidSelezionati); //selarrrow
                        $Dati = array();
                        $Dati['FASCICOLI_SELEZIONATI'] = $FascicoliSelezionati;
                        $_POST = array();
                        $_POST['FASCICOLI_SELEZIONATI'] = $FascicoliSelezionati;
                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->setReturnData($Dati);
                        $returnObj->parseEvent();
                        $this->returnToParent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'TornaElenco':
                $this->CaricaFascicoli();
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_ElencoFascicoli');
        App::$utente->removeKey($this->nameForm . '_gridFiltersFascicoli');
        App::$utente->removeKey($this->nameForm . '_gridFiltersSottoFascicoli');
        App::$utente->removeKey($this->nameForm . '_Titolario');
        App::$utente->removeKey($this->nameForm . '_Anapro_rec');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function setGridFiltersFascicoli() {
        $this->gridFiltersFascicoli = array();
        if ($_POST['GESOGG'] != '') {
            $this->gridFiltersFascicoli['GESOGG'] = $_POST['GESOGG'];
        }
        if ($_POST['ORGANN'] != '') {
            $this->gridFiltersFascicoli['ORGANN'] = $_POST['ORGANN'];
        }
        if ($_POST['ORGCCF'] != '') {
            $this->gridFiltersFascicoli['ORGCCF'] = $_POST['ORGCCF'];
        }
        if ($_POST['ORGCOD'] != '') {
            $this->gridFiltersFascicoli['ORGCOD'] = $_POST['ORGCOD'];
        }
        if ($_POST['NOME_RESPONSABILE'] != '') {
            $this->gridFiltersFascicoli['NOME_RESPONSABILE'] = $_POST['NOME_RESPONSABILE'];
        }
        if ($_POST['SOTTOFASCICOLI_FAS'] != '') {
            $this->gridFiltersFascicoli['SOTTOFASCICOLI_FAS'] = $_POST['SOTTOFASCICOLI_FAS'];
        }
    }

    public function CreaSql($filters = array()) {
        $sql = "SELECT ANAORG.*, 
                       PROGES.GESOGG AS GESOGG ,
                       PROGES.GESKEY AS GESKEY,
                       PROGES.GESRES AS GESRES,
                       ANAMED.MEDNOM AS NOME_RESPONSABILE,
                       (SELECT COUNT(ROWID) FROM ORGCONN ORGNEWCONN WHERE ORGNEWCONN.PRONUMPARENT=ANAPRO.PRONUM AND ORGNEWCONN.PROPARPARENT=ANAPRO.PROPAR AND ORGNEWCONN.PROPAR='N' AND ORGNEWCONN.CONNDATAANN = '') AS SOTTOFASCICOLI_FAS
                    FROM ANAORG 
                    LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY
                    LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
                    LEFT OUTER JOIN ANAPRO ANAPRO ON ANAORG.ORGKEY=ANAPRO.PROFASKEY AND ANAPRO.PROPAR = 'F'
                    LEFT OUTER JOIN ANAPRO ANAPROVISIBILITA ON ANAPROVISIBILITA.PROFASKEY=PROGES.GESKEY AND (ANAPROVISIBILITA.PROPAR='F' OR ANAPROVISIBILITA.PROPAR='N')                    
                    LEFT OUTER JOIN ARCITE ARCITE ON ANAPROVISIBILITA.PRONUM=ARCITE.ITEPRO AND ANAPROVISIBILITA.PROPAR=ARCITE.ITEPAR                    
                    ";
        $sql.= " WHERE
                    ORGDAT='' AND 
                    GESDCH = '' ";
        if ($this->ElencoFascicoli) {
            $where = " AND (";
            foreach ($this->ElencoFascicoli as $Fascicolo) {
                $where.=" ANAORG.ORGKEY = '{$Fascicolo['ORGKEY']}' OR";
            }
            $where = substr($where, 0, -3) . " ) ";
            $sql.=$where;
            // Non dovrebbe essere ancora usato..
        } else if ($this->Titolario) {
            $procat = $this->Titolario['PROCAT'];
            $clacod = $this->Titolario['CLACOD'];
            $fascod = $this->Titolario['FASCOD'];
            $organn = $this->Titolario['ORGANN'];
            // Where Titolario Fascicolo.
            $sql.= "AND ORGCCF='{$procat}{$clacod}{$fascod}'";
            if ($organn) {
                $sql.=" AND ORGANN = '$organn' ";
            }
        }
        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');

        $sql .= " AND $where_profilo";

        $sql .= " GROUP BY ANAORG.ROWID";

        $sql = "SELECT * FROM (" . $sql . ") AS SOTTOFASC WHERE 1 ";
        if ($filters) {
            foreach ($filters as $key => $value) {
                if ($key == 'SOTTOFASCICOLI_FAS') {
                    if ($value == 'S' || $value == 's') {
                        $sql.=" AND $key > 0 ";
                    } else {
                        $sql.=" AND $key = 0 ";
                    }
                } else {
                    $value = str_replace("'", "\'", $value);
                    $sql.= " AND " . $this->PROT_DB->strupper($key) . " LIKE '%" . strtoupper($value) . "%' ";
                }
            }
        }

        return $sql;
    }

    private function elaboraRecordsFascicoli($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            $AnaproFascicolo = $this->proLib->GetAnapro($result_rec['GESKEY'], 'fascicolo');
            //$Orgcon_tab = $this->proLibFascicolo->GetOrgconParent($AnaproFascicolo['PRONUM'], $AnaproFascicolo['PROPAR'], 'N');
            $sottoFascicoli = '';
            if ($result_rec['SOTTOFASCICOLI_FAS'] > 0) {
                $icon = "ita-icon-sub-folder-32x32";
                $tooltip = $result_rec['SOTTOFASCICOLI_FAS'] . ' SOTTOFASCICOLI';
                $sottoFascicoli = '<div class="ita-html"><span style="height:16px;background-size:50%;margin:2px;"  title="' . $tooltip . '" class="ita-tooltip ita-icon ' . $icon . '"></span></div>';
            }
            $result_tab[$key]['SOTTOFASCICOLI_FAS'] = $sottoFascicoli;
        }
        return $result_tab;
    }

    private function CaricaGrigliaFascicoli($sql) {
        TableView::clearGrid($this->gridFascicoli);
        $ita_grid01 = new TableView($this->gridFascicoli, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ordinamento = $_POST['sidx'];
        if (!$ordinamento) {
            $ordinamento = 'GESOGG';
        }

        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        $ita_grid01->setSortIndex($ordinamento);
        $ita_grid01->setSortOrder($_POST['sord']);

        $result_tab = $ita_grid01->getDataArray();
        if (!$ita_grid01->getDataPageFromArray('json', $this->elaboraRecordsFascicoli($result_tab))) {
            Out::msgInfo("Selezione", "Nessun fascicolo trovato.");
        }
        TableView::enableEvents($this->gridFascicoli);
        return;
    }

    private function CaricaFascicoli() {
        Out::show($this->nameForm . '_divFascicoli');
        Out::hide($this->nameForm . '_divSottoFascicoli');
        $sql = $this->CreaSql();
        $Descrizione = '';
        $this->CaricaGrigliaFascicoli($sql);
        if ($this->Anapro_rec) {
            if ($this->Anapro_rec['PROPRE']) {
                $Descrizione = 'Il protocollo collegato: <b>' . substr($this->Anapro_rec['PROPRE'], 0, 4) . '/' . substr($this->Anapro_rec['PROPRE'], 4) . ' ' . $this->Anapro_rec['PROPARPRE'] . '</b> ';
                $Descrizione.= 'risulta fascicolato nei fascicoli sottostanti.<br><br>';
            } else {
                $Descrizione = 'Duplica Protocollo: Il protocollo di partenza </b> ';
                $Descrizione.= 'risulta fascicolato nei fascicoli sottostanti.<br><br>';
            }
            $Protocollo = substr($this->Anapro_rec['PRONUM'], 0, 4) . '/' . substr($this->Anapro_rec['PRONUM'], 4) . ' ' . $this->Anapro_rec['PROPAR'];
            $Descrizione.= '<b>Seleziona i Fascicoli in cui vuoi inserire il protocollo: <u>' . $Protocollo . '</u></b><br><br>';
        } else {
            $Descrizione = '<b>Seleziona i Fascicoli in cui vuoi inserire il prtocollo. </b><br><br>';
        }
        // Controllo se ha solo 1 record lo autoselezioni.
        if (count($this->ElencoFascicoli) == 1) {
            TableView::setSelectAll($this->gridFascicoli);
        }
        Out::html($this->nameForm . '_divDescrizioneFascicolo', $Descrizione);
    }

    private function decodTitolario($rowid, $tipoArc) {
        $ver = $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $ver = $anacat_rec['VERSIONE_T'];
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $ver = $anacla_rec['VERSIONE_T'];
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $ver = $anafas_rec['VERSIONE_T'];
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        if (!$cat && !$cla && !$fas && $des) {
            return;
        }
        $Titolario = array();
        $Titolario['VERSIONE_T'] = $ver;
        $Titolario['PROCAT'] = $cat;
        $Titolario['CLACOD'] = $cla;
        $Titolario['FASCOD'] = $fas;
        $Titolario['ORGANN'] = '';
        $this->setTitolario($Titolario);
        $this->CaricaFascicoli();
    }

    private function DecodificaDescTitolarioCorrente() {
        $DescTitolario = '';
        $cat = $cla = $fas = $org = $des = '';

        if ($this->Titolario) {
            if ($this->Titolario['PROCAT']) {
                $anacat_rec = $this->proLib->GetAnacat($this->Titolario['VERSIONE_T'], $this->Titolario['PROCAT'], 'codice');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
            }
            if ($this->Titolario['CLACOD']) {
                $anacla_rec = $this->proLib->GetAnacla($this->Titolario['VERSIONE_T'], $this->Titolario['PROCAT'] . $this->Titolario['CLACOD'], 'codice');
                if ($anacla_rec) {
                    $cat = $anacla_rec['CLACAT'] . '.';
                    $cla = $anacla_rec['CLACOD'];
                    $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                }
            }
            if ($this->Titolario['FASCOD']) {
                $anafas_rec = $this->proLib->GetAnafas($this->Titolario['VERSIONE_T'], $this->Titolario['PROCAT'] . $this->Titolario['CLACOD'] . $this->Titolario['FASCOD'], 'fasccf');
                if ($anafas_rec) {
                    $cat = substr($anafas_rec['FASCCA'], 0, 4) . '.';
                    $cla = substr($anafas_rec['FASCCA'], 4) . '.';
                    $fas = $anafas_rec['FASCOD'];
                    $des = $anafas_rec['FASDES'];
                }
            }
            $DescTitolario = '<b>Titolario:</b> ' . $cat . $cla . $fas . '<br>'; // Separato da punti?
            $DescTitolario.= '<b>Descrizione:</b> ' . $des . '';
        }
        return $DescTitolario;
    }

}

?>
