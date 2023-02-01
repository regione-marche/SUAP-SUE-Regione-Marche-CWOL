<?php

require_once ITA_BASE_PATH . '/lib/itaPHPCore/itaCrypt.class.php';

class praPubb extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    public $PROT_DB;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
            $this->PROT_DB = ItaDB::DBOpen('PROT', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $html = '';

        switch ($this->request['event']) {
            default:
                $html .= $this->DisegnaFormRicerca();
                break;

            case 'risultato':
                $html .= $this->DisegnaRisultati();
                break;

            case 'dettaglio':
                $html .= $this->DisegnaDettaglio();
                break;

            case 'tablePager':
                $data = $this->getTableData();
                foreach ($data[1] as &$record) {
                    foreach ($record as &$value) {
                        $value = utf8_encode($value);
                    }
                }

                echo json_encode($data);
                exit;
        }

        return $html;
    }

    public function DisegnaFormRicerca() {
        $html = new html();

        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'GET', array(
            'class' => 'italsoft-form--fixed',
            'id' => 'form1'
        ));

        $html->addInput('hidden', '', array(
            'name' => 'event',
            'value' => 'risultato'
        ));

        $html->addInput('text', 'Pratica numero', array(
            'id' => 'PRATICA_NUMERO',
            'name' => 'PRATICA_NUMERO',
            'value' => $this->request['PRATICA_NUMERO'],
            'size' => 6,
            'maxlength' => 6
        ));

        $html->addInput('text', 'anno', array(
            'id' => 'PRATICA_ANNO',
            'name' => 'PRATICA_ANNO',
            'value' => $this->request['PRATICA_ANNO'],
            'size' => 4,
            'maxlength' => 4
        ));

        $html->addBr();

        $html->addInput('text', 'Protocollo numero', array(
            'id' => 'PRATICA_PROT_NUMERO',
            'name' => 'PRATICA_PROT_NUMERO',
            'value' => $this->request['PRATICA_PROT_NUMERO'],
            'size' => 6,
            'maxlength' => 6
        ));

        $html->addInput('text', 'anno', array(
            'id' => 'PRATICA_PROT_ANNO',
            'name' => 'PRATICA_PROT_ANNO',
            'value' => $this->request['PRATICA_PROT_ANNO'],
            'size' => 4,
            'maxlength' => 4
        ));

        $html->addBr();

        $html->addInput('datepicker', 'Data presentazione', array(
            'id' => 'PRATICA_DATA',
            'name' => 'PRATICA_DATA',
            'value' => $this->request['PRATICA_DATA'],
            'size' => 10
        ));

        $html->addBr();

        $html->addInput('text', 'Oggetto', array(
            'id' => 'PRATICA_OGGETTO',
            'name' => 'PRATICA_OGGETTO',
            'value' => $this->request['PRATICA_OGGETTO'],
            'size' => 50
        ));

        $html->addBr();

        $html->addInput('text', 'Via', array(
            'id' => 'PRATICA_VIA',
            'name' => 'PRATICA_VIA',
            'value' => $this->request['PRATICA_VIA'],
            'size' => 50
        ));

        $html->addBr();

        $anaspa_options = array('' => '');
        $anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT SPACOD, SPADES FROM ANASPA");
        foreach ($anaspa_tab as $anaspa_rec) {
            $anaspa_options[$anaspa_rec['SPACOD']] = $anaspa_rec['SPADES'];
        }

        $html->addInput('select', 'Comune aggregato', array(
            'id' => 'PRATICA_AGGREGATO',
            'name' => 'PRATICA_AGGREGATO',
            'value' => $this->request['PRATICA_AGGREGATO'],
            ), $anaspa_options);

        $html->addBr();

        $html->addInput('text', 'Dati catastali - sezione', array(
            'id' => 'PRATICA_SEZIONE',
            'name' => 'PRATICA_SEZIONE',
            'value' => $this->request['PRATICA_SEZIONE'],
            'size' => 5
        ));

        $html->addInput('text', 'foglio', array(
            'id' => 'PRATICA_FOGLIO',
            'name' => 'PRATICA_FOGLIO',
            'value' => $this->request['PRATICA_FOGLIO'],
            'size' => 5
        ));

        $html->addInput('text', 'particella', array(
            'id' => 'PRATICA_PARTICELLA',
            'name' => 'PRATICA_PARTICELLA',
            'value' => $this->request['PRATICA_PARTICELLA'],
            'size' => 5
        ));

        $html->addBr();

        $html->appendHtml('<button class="italsoft-button" type="submit"><span class="ion-icon ion-search" style="margin-right: 8px;"></span> Ricerca</button>');

        $html->appendHtml('</form>');

        return $html->getHtml();
    }

    public function DisegnaRisultati() {
        $html = new html();

        $sql = $this->getSql();

        if (!ItaDB::DBSQLCount($this->PRAM_DB, $sql)) {
            $html->addAlert('La ricerca non ha prodotto nessun risultato.');
            return $html->getHtml() . $this->DisegnaFormRicerca();
        }

        $html->addButton('<span class="ion-icon ion-arrow-left-b" style="margin-right: 8px;"></span> Nuova ricerca', ItaUrlUtil::GetPageUrl(array()));

        $html->addBr();

        $html->addForm(ItaUrlUtil::GetPageUrl(array()), 'GET');

        $tableData = array(
            'header' => array(
                array('text' => 'Pratica N.<br>Protocollo N.', 'attrs' => array('style' => 'width: 15%;')),
                array('text' => 'Oggetto'),
                array('text' => 'Comune<br>Aggregato', 'attrs' => array('style' => 'width: 15%;')),
                array('text' => 'Indirizzo', 'attrs' => array('style' => 'width: 20%;'))
            ),
            'body' => array()
        );

        $html->addTable($tableData, array(
            'sortable' => true,
            'paginated' => true,
            'ajax' => true
        ));

        $html->appendHtml('</form>');

        return $html->getHtml();
    }

    protected function getSql($limit = true) {
        $whereRicerca = $joinRicerca = '';

        if ($this->request['PRATICA_NUMERO']) {
            $whereRicerca .= " AND PROGES.SERIEPROGRESSIVO = '" . addslashes($this->request['PRATICA_NUMERO']) . "'";
        }

        if ($this->request['PRATICA_ANNO']) {
            $whereRicerca .= " AND PROGES.SERIEANNO = '" . addslashes($this->request['PRATICA_ANNO']) . "'";
        }

        if ($this->request['PRATICA_PROT_NUMERO']) {
            $whereRicerca .= " AND PROGES.GESNPR LIKE '____" . addslashes($this->request['PRATICA_PROT_NUMERO']) . "'";
        }

        if ($this->request['PRATICA_PROT_ANNO']) {
            $whereRicerca .= " AND PROGES.GESNPR LIKE '" . addslashes($this->request['PRATICA_PROT_ANNO']) . "%'";
        }

        if ($this->request['PRATICA_DATA']) {
            $dataRicerca = substr($this->request['PRATICA_DATA'], 6) . substr($this->request['PRATICA_DATA'], 3, 2) . substr($this->request['PRATICA_DATA'], 0, 2);
            $whereRicerca .= " AND PROGES.GESDRE = '" . addslashes($dataRicerca) . "'";
        }

        if ($this->request['PRATICA_OGGETTO']) {
            $whereRicerca .= " AND " . $this->PRAM_DB->strUpper('PROGES.GESOGG') . " LIKE " . $this->PRAM_DB->strUpper("'%" . addslashes($this->request['PRATICA_OGGETTO']) . "%'");
        }

        if ($this->request['PRATICA_VIA']) {
            $whereRicerca .= " AND " . $this->PRAM_DB->strUpper('ANADES.DESIND') . " LIKE " . $this->PRAM_DB->strUpper("'%" . addslashes($this->request['PRATICA_VIA']) . "%'");
        }

        if ($this->request['PRATICA_AGGREGATO']) {
            $whereRicerca .= " AND PROGES.GESSPA = '" . addslashes($this->request['PRATICA_AGGREGATO']) . "'";
        }

        if ($this->request['PRATICA_SEZIONE']) {
            $joinRicerca = "LEFT OUTER JOIN PRAIMM ON PRAIMM.PRONUM = PROGES.GESNUM";
            $whereRicerca .= " AND PRAIMM.SEZIONE = '" . addslashes($this->request['PRATICA_SEZIONE']) . "'";
        }

        if ($this->request['PRATICA_FOGLIO']) {
            $joinRicerca = "LEFT OUTER JOIN PRAIMM ON PRAIMM.PRONUM = PROGES.GESNUM";
            $whereRicerca .= " AND PRAIMM.FOGLIO = '" . addslashes($this->request['PRATICA_FOGLIO']) . "'";
        }

        if ($this->request['PRATICA_PARTICELLA']) {
            $joinRicerca = "LEFT OUTER JOIN PRAIMM ON PRAIMM.PRONUM = PROGES.GESNUM";
            $whereRicerca .= " AND PRAIMM.PARTICELLA = '" . addslashes($this->request['PRATICA_PARTICELLA']) . "'";
        }

        if ($this->config['sportello']) {
            $whereRicerca .= " AND PROGES.GESTSP IN (" . $this->config['sportello'] . ")";
        }

        if ($this->config['serie']) {
            $whereRicerca .= " AND PROGES.SERIECODICE IN (" . $this->config['serie'] . ")";
        }

        $orderBy = 'PROGES.SERIEPROGRESSIVO DESC, PROGES.SERIEANNO DESC';
        if (isset($this->request['column'][0])) {
            $dir = $this->request['column'][0] == '1' ? 'DESC' : 'ASC';
            $orderBy = "PROGES.SERIEPROGRESSIVO $dir, PROGES.SERIEANNO $dir";
        } elseif (isset($this->request['column'][1])) {
            $dir = $this->request['column'][1] == '1' ? 'DESC' : 'ASC';
            $orderBy = "PROGES.GESOGG $dir";
        } elseif (isset($this->request['column'][2])) {
            $dir = $this->request['column'][2] == '1' ? 'DESC' : 'ASC';
            $orderBy = "ANASPA.SPADES $dir";
        } elseif (isset($this->request['column'][3])) {
            $dir = $this->request['column'][3] == '1' ? 'DESC' : 'ASC';
            $orderBy = "ANADES.DESIND $dir";
        }

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php';
        $RUOCODVIA = praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'];

        $limitSQL = '';
        if ($limit && isset($this->request['page']) && isset($this->request['size'])) {
            $limitSQL .= 'LIMIT ' . addslashes($this->request['size']);
            $limitSQL .= ' OFFSET ' . ((string) ($this->request['page'] * $this->request['size']));
        }

        $sql = "SELECT
                    PROGES.ROWID,
                    PROGES.GESNUM,
                    " . $this->PROT_DB->getDB() . ".ANASERIEARC.SIGLA AS SERIE,
                    PROGES.SERIECODICE,
                    PROGES.SERIEPROGRESSIVO,
                    PROGES.SERIEANNO,
                    PROGES.GESNPR,
                    PROGES.GESOGG,
                    ANASPA.SPADES,
                    ANADES.DESIND,
                    ANADES.DESCIV,
                    ANADES.DESCIT,
                    ANADES.DESCAP
                FROM 
                    PROGES
                LEFT OUTER JOIN ANADES
                    ON PROGES.GESNUM = ANADES.DESNUM AND ANADES.DESRUO = '$RUOCODVIA'
                LEFT OUTER JOIN ANASPA
                    ON PROGES.GESSPA = ANASPA.SPACOD
                LEFT OUTER JOIN " . $this->PROT_DB->getDB() . ".ANASERIEARC
                    ON " . $this->PROT_DB->getDB() . ".ANASERIEARC.CODICE = PROGES.SERIECODICE
                $joinRicerca
                WHERE
                    1 = 1 $whereRicerca
                GROUP BY GESNUM
                ORDER BY $orderBy $limitSQL";

        return $sql;
    }

    public function getTableData() {
        $sql = $this->getSql();

        $Proges_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
        $totalRows = ItaDB::DBSQLCount($this->PRAM_DB, $this->getSql(false));

        $tableBody = array();
        foreach ($Proges_tab as $Proges_rec) {
            $hrefDetails = ItaUrlUtil::GetPageUrl(array(
                    'event' => 'dettaglio',
                    'id' => itaCrypt::encrypt($Proges_rec['ROWID']),
                    'return' => itaCrypt::encrypt(json_encode(array(
                        'PRATICA_NUMERO' => $this->request['PRATICA_NUMERO'],
                        'PRATICA_ANNO' => $this->request['PRATICA_ANNO'],
                        'PRATICA_PROT_NUMERO' => $this->request['PRATICA_PROT_NUMERO'],
                        'PRATICA_PROT_ANNO' => $this->request['PRATICA_PROT_ANNO'],
                        'PRATICA_DATA' => $this->request['PRATICA_DATA'],
                        'PRATICA_OGGETTO' => $this->request['PRATICA_OGGETTO'],
                        'PRATICA_VIA' => $this->request['PRATICA_VIA'],
                        'PRATICA_AGGREGATO' => $this->request['PRATICA_AGGREGATO'],
                        'PRATICA_SEZIONE' => $this->request['PRATICA_SEZIONE'],
                        'PRATICA_FOGLIO' => $this->request['PRATICA_FOGLIO']
                    )))
            ));

            $praticaNumero = '<a href="' . $hrefDetails . '">' . $Proges_rec['SERIE'] . '/' . $Proges_rec['SERIEPROGRESSIVO'] . '/' . $Proges_rec['SERIEANNO'] . '</a>';
            if ($Proges_rec['GESNPR']) {
                $praticaNumero .= '<br><small>Prot. ' . substr($Proges_rec['GESNPR'], 4) . '/' . substr($Proges_rec['GESNPR'], 0, 4) . '</small>';
            }

            $tableRow = array();
            $tableRow[] = '<div style="height: 3.5rem;">' . $praticaNumero . '</div>';
            $tableRow[] = '<a href="' . $hrefDetails . '" style="word-break: break-word;">' . $Proges_rec['GESOGG'] . '</a>';
            $tableRow[] = $Proges_rec['SPADES'];
            $tableRow[] = "{$Proges_rec['DESIND']} {$Proges_rec['DESCIV']}<br>{$Proges_rec['DESCIT']} {$Proges_rec['DESCAP']}";

            $tableBody[] = $tableRow;
        }

        return array($totalRows, $tableBody);
    }

    public function DisegnaDettaglio() {
        $htmlClass = new html;
        $html = '';

        $requestRicerca = json_decode(itaCrypt::decrypt($this->request['return']), true);
        $requestRicerca['event'] = 'risultato';

        $htmlClass->addButton('<span class="ion-icon ion-arrow-left-b" style="margin-right: 10px;"></span> Torna all\'elenco', ItaUrlUtil::GetPageUrl($requestRicerca));
        $htmlClass->addButton('<span class="ion-icon ion-search" style="margin-right: 10px;"></span> Nuova ricerca', ItaUrlUtil::GetPageUrl(array()));
        $htmlClass->addBr();
        $html .= $htmlClass->getHtml();

        $rowid = itaCrypt::decrypt($this->request['id']);
        $Proges_rec = $this->praLib->GetProges($rowid, 'rowid', false, $this->PRAM_DB);

        $sql = "SELECT * FROM ANASERIEARC WHERE CODICE = '" . $Proges_rec['SERIECODICE'] . "'";
        $Anaseriearc_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);

        $html .= '<div class="italsoft-alert italsoft-alert--noicon">';
        $html .= '<h2>' . $Proges_rec['GESOGG'] . '</h2>';
        $html .= '<hr>';

        $html .= '<b style="display: inline-block; width: 150px;">Pratica numero</b> ';
        $html .= $Anaseriearc_rec['SIGLA'] . '/' . $Proges_rec['SERIEPROGRESSIVO'] . '/' . $Proges_rec['SERIEANNO'];
        $html .= '<br>';

        if ($Proges_rec['GESNPR']) {
            $html .= '<b style="display: inline-block; width: 150px;">Protocollo</b> ';
            $html .= substr($Proges_rec['GESNPR'], 4) . '/' . substr($Proges_rec['GESNPR'], 0, 4);
            $html .= '<br>';
        }

        $html .= '<b style="display: inline-block; width: 150px;">Data presentazione</b> ';
        $html .= frontOfficeLib::convertiData($Proges_rec['GESDRE']);
        $html .= '<br>';

        if ($Proges_rec['GESSPA']) {
            $Anaspa_rec = $this->praLib->GetAnaspa($Proges_rec['GESSPA'], 'codice', $this->PRAM_DB);

            $html .= '<b style="display: inline-block; width: 150px;">Aggregato</b> ';
            $html .= $Anaspa_rec['SPADES'];
            $html .= '<br>';
        }

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php';
        $RUOCODVIA = praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'];

        $sql = "SELECT * FROM ANADES WHERE DESNUM = '{$Proges_rec['GESNUM']}' AND DESRUO = '$RUOCODVIA'";
        $Anades_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        if (count($Anades_tab)) {
            $html .= '<br>';
            $html .= '<h3>Localizzazioni</h3>';
            $html .= '<hr>';
            $html .= '<ul style="margin: 0; list-style: inside circle;">';

            foreach ($Anades_tab as $Anades_rec) {
                $html .= '<li style="margin: 0;">';
                $html .= $Anades_rec['DESIND'] . ' ' . $Anades_rec['DESCIV'];
                $html .= ', ' . $Anades_rec['DESCIT'];
                $html .= ' ' . $Anades_rec['DESCAP'];
                $html .= ' (' . $Anades_rec['DESPRO'] . ')';
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $sql = "SELECT * FROM PRAIMM WHERE PRONUM = '{$Proges_rec['GESNUM']}'";
        $Praimm_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);

        if (count($Praimm_tab)) {
            $html .= '<br>';
            $html .= '<h3>Dati catastali</h3>';
            $html .= '<hr>';
            $html .= '<ul style="margin: 0; list-style: inside circle;">';

            foreach ($Praimm_tab as $Praimm_rec) {
                $html .= '<li style="margin: 0;">';
                $html .= 'Sezione ' . $Praimm_rec['SEZIONE'];
                $html .= ', foglio ' . $Praimm_rec['FOGLIO'];
                $html .= ', particella ' . $Praimm_rec['PARTICELLA'];
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

}
