<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    27.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';
include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';

function proListViewer() {
    $proListViewer = new proListViewer();
    $proListViewer->parseEvent();
    return;
}

class proListViewer extends itaModel {

    public $PROT_DB;
    public $ITALWEB_DB;
    public $nameForm = "proListViewer";
    public $gridProtocolli;
    public $proLib;
    public $proLibFascicolo;
    public $proLibAllegati;
    public $proLibMail;
    public $DataSuperioreFiltro;
    public $itaDate;
    public $codiceDest;
    public $ElementiRicerca = array();
    public $ConteggioProtocolli;

    function __construct() {
        parent::__construct();
        try {
            
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    protected function postInstance() {
        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);

        $this->proLib = new proLib();
        $this->proLibFascicolo = new proLibFascicolo();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibMail = new proLibMail();
        $this->itaDate = new itaDate();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');

        $this->gridProtocolli = $this->nameForm . '_gridProtocolli';
        $this->DataSuperioreFiltro = App::$utente->getKey($this->nameForm . '_DataSuperioreFiltro');
        $this->tabella = App::$utente->getKey($this->nameForm . '_tabella');
        $this->codiceDest = App::$utente->getKey($this->nameForm . '_codiceDest');
        $this->ElementiRicerca = App::$utente->getKey($this->nameForm . '_ElementiRicerca');
        $this->ConteggioProtocolli = App::$utente->getKey($this->nameForm . '_ConteggioProtocolli');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_DataSuperioreFiltro", $this->DataSuperioreFiltro);
            App::$utente->setKey($this->nameForm . "_tabella", $this->tabella);
            App::$utente->setKey($this->nameForm . "_codiceDest", $this->codiceDest);
            App::$utente->setKey($this->nameForm . "_ElementiRicerca", $this->ElementiRicerca);
            App::$utente->setKey($this->nameForm . "_ConteggioProtocolli", $this->ConteggioProtocolli);
        }
    }

    public function getCodiceDest() {
        return $this->codiceDest;
    }

    public function setCodiceDest($codiceDest) {
        $this->codiceDest = $codiceDest;
    }

    public function setElementiRicerca($ElementiRicerca) {
        $this->ElementiRicerca = $ElementiRicerca;
    }

    public function getConteggioProtocolli() {
        return $this->ConteggioProtocolli;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $this->caricaDatiGriglia();
                $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella, '1');
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    case $this->gridProtocolli:
                        // Per template domanda in più.
                        $indice = $_POST['rowid'];
                        if (!$this->proLib->ApriProtocollo($indice)) {
                            Out::msgStop("Attenzione", $this->proLib->getErrMessage());
                        }
                        break;
                }
                break;
            case 'cellSelect':
                break;

            case 'onClickTablePager':
                switch ($this->elementId) {
                    case $this->gridProtocolli:
                        $this->RicaricaProtocolli('2');
                        Out::broadcastMessage($this->nameForm, "UPDATE_CONT_LISTVIEWER", array('' => ''));
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_DataSuperioreFiltro');
        App::$utente->removeKey($this->nameForm . '_tabella');
        App::$utente->removeKey($this->nameForm . '_codiceDest');
        App::$utente->removeKey($this->nameForm . '_ElementiRicerca');
        App::$utente->removeKey($this->nameForm . '_ConteggioProtocolli');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function RicaricaProtocolli($tipo = '2') {
        $this->caricaDatiGriglia();
        $this->CaricaGrigliaGenerica($this->gridProtocolli, $this->tabella, $tipo);
    }

    private function CaricaGrigliaGenerica($griglia, $appoggio, $tipo = '1', $pageRows = 15) {
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );

        $ordinamento = 'DATAPROT';
        $order = 'asc';

        if ($_POST['sord'] != '') {
            $order = $_POST['sord'];
        }
        if ($_POST['sidx']) {
            $ordinamento = $_POST['sidx'];
        }
        /*
         * Controllo campi per ordinamento:
         */
        switch ($ordinamento) {
            case 'PRONUM':
                break;

            case 'PRODAR':
                $ordinamento = 'DATAPROT';
                break;
            case 'PROPAR':
                $ordinamento = 'TIPOPROT';
                break;
            case 'ANNO':
                $ordinamento = 'ANNOPROT';
                break;
            case 'CODICE':
                $ordinamento = 'PRONUM';
                break;
            case 'PRONOM':
                $ordinamento = 'PROVDEST';
                break;
            case 'OGGOGG':
                $ordinamento = 'OGGETTO';
                break;
        }

        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        $ita_grid01->setSortOrder($order);
        $ita_grid01->setSortIndex($ordinamento);

        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    /*
     * $ElementiRicerca
     * ['Incompleti'] = SE VISUALIZZARE PROT. ICOMPLETI
     * ['LimiteVis'] = LIMITE DI PROT DA VISUALIZZARE.
     * ['TipoVedi]' = TIPOLOGIA DI VISIBILITA'
     * ['selectUffici'] = VISIBILITA UFFICI.
     * ['DaData'] = DA DATA 
     * ['VediTutti'] = Forzatura Vedi Tutti.
     * ['FLT_GRIGLIA'] = array(
     *      'PROPAR',
     *      'ANNO',
     *      'CODICE',
     *      'PRODAR',
     *      'PRONOM',
     *      'OGGOGG',
     *      'PROLRIS' )  
     */

    private function creaSql() {
        $this->DataSuperioreFiltro = false;
        $ufficio = $this->ElementiRicerca['selectUffici'];
        $limiteVis = $this->ElementiRicerca['LimiteVis'];
        $daData = $this->ElementiRicerca['DaData'];

        $limiteProt = " LIMIT 0, 100";
        $limiteGiorni = 60;
        if ($limiteVis) {
            if ($limiteVis == 'DATE') {
                if ($daData) {
                    $limiteProt = "";
                    $ngiorni = $diff_data = $this->itaDate->dateDiffDays(date('Ymd'), $daData);
                    if ($ngiorni >= 730) {
                        $limiteGiorni = 730;
                        $this->DataSuperioreFiltro = true;
                    } else {
                        $limiteGiorni = $ngiorni;
                    }
                }
            } else {
                $limiteProt = "";
                $limiteGiorni = $limiteVis;
            }
        }
        if ($this->ElementiRicerca['VediTutti'] || $limiteVis == 'N') {
            $limiteProt = " LIMIT 0, 9999";
            $limiteGiorni = '';
        }

        $sql = $this->proLib->getSqlRegistro();
        //
        // Prime assegnazioni su Arcite
        //
        $sql.=" LEFT OUTER JOIN ARCITE ARCITE FORCE INDEX(I_ITEPRO)ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR";

        $where = '';
        if ($_POST['_search'] == true) {
            if ($_POST['PROPAR']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAPRO.PROPAR') . " = '" . addslashes(strtoupper($_POST['PROPAR'])) . "'";
            }
            if ($_POST['ANNO']) {
                $anno = $_POST['ANNO'] + 1;
                $where .= " AND ANAPRO.PRONUM >= " . $_POST['ANNO'] . "000000 AND ANAPRO.PRONUM <= " . $anno . "000000";
            }
            if ($_POST['ANNO'] && $_POST['CODICE']) {
                $where .= " AND ANAPRO.PRONUM =" . $_POST['ANNO'] . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT);
            } else if ($_POST['CODICE']) {
                $where .= " AND ANAPRO.PRONUM =" . date('Y') . str_pad($_POST['CODICE'], 6, "0", STR_PAD_LEFT);
            }
            if ($_POST['PRODAR']) {
                if (strlen($_POST['PRODAR']) == 8) {
                    $data = substr($_POST['PRODAR'], 4) . substr($_POST['PRODAR'], 2, 2) . substr($_POST['PRODAR'], 0, 2);
                } else if (strlen($_POST['PRODAR']) == 10) {
                    $data = substr($_POST['PRODAR'], 6) . substr($_POST['PRODAR'], 3, 2) . substr($_POST['PRODAR'], 0, 2);
                }
                if ($data) {
                    $where .= " AND ANAPRO.PRODAR= '" . $data . "'";
                }
            }
            if ($_POST['PRONOM']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAPRO.PRONOM') . " LIKE '%" . addslashes(strtoupper($_POST['PRONOM'])) . "%'";
            }
            if ($_POST['OGGOGG']) {
                $where .= " AND " . $this->PROT_DB->strUpper('ANAOGG.OGGOGG') . " LIKE '%" . addslashes(strtoupper($_POST['OGGOGG'])) . "%'";
            }
            if ($_POST['PROLRIS']) {
                $where .= " AND ANAPRO.PROLRIS LIKE '" . $_POST['PROLRIS'] . "'";
            }
        }


        /*
         * Sql Principale:
         * Protocolli incompleti deve essere primaria come condizione.
         */
        if ($this->ElementiRicerca['Incompleti']) {
            if ($this->ElementiRicerca['VediTutti']) {
                // Forzo a vederli tutti:
                $sql.= " WHERE PROSTATOPROT = " . proLib::PROSTATO_INCOMPLETO . " OR ( PROSTATOPROT = " . proLib::PROSTATO_INCOMPLETO . " ";
            } else {
                $sql.= " WHERE ( PROSTATOPROT = " . proLib::PROSTATO_INCOMPLETO . " ";
            }
        } else {
            $sql.= " WHERE ( 1=1 ";
        }
        /*
         * Discorso visibilita non dovrebbe esserci..
         */
        $where_profilo = " AND " . proSoggetto::getSecureWhereFromIdUtente($this->proLib);

        if ($this->ElementiRicerca['TipoVedi'] == 'M') {
            $template = " AND ANAPRO.PROTEMPLATE = 1 ";
        }

        if ($this->codiceDest) {
            $codiceSoggetto = proSoggetto::getCodiceSoggettoFromIdUtente();
            switch ($ufficio) {
                case "@":
                    $sql .= " AND (ARCITE.ITEDES='$codiceSoggetto' AND ARCITE.ITENODO='INS')";
                    break;
                case "*":
                    $uffdes_tab = $this->proLib->GetUffdes($this->codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
                    $sql .= ' AND (';
                    foreach ($uffdes_tab as $uffdes_rec) {
                        $sql .= " (UFFPRO.UFFCOD = '{$uffdes_rec['UFFCOD']}' OR ANAPRO.PROUOF='{$uffdes_rec['UFFCOD']}') OR";
                    }
                    $sql .= " 1=0)";
                    break;
                default:
                    if ($ufficio != '') {
                        $sql .= " AND (UFFPRO.UFFCOD = '$ufficio' OR ANAPRO.PROUOF='$ufficio')";
                    }
                    break;
            }
        }
        $dataLimite = date('Ymd', strtotime('-' . $limiteGiorni . ' day', strtotime(date("Ymd"))));
        $sql .= " AND PRODAR>='$dataLimite'";
        $sql .= " AND (PROPAR='A' OR PROPAR='P' OR PROPAR='C')";
        $sql .= " $where $template $where_profilo ";
        $sql .= " ) "; /*  Chiusura condizione principale */
        /*
         *  Elenco Mail Abilitate Utente:
         */
//        if ($this->ElementiRicerca['MailAbilitate']) {
//            $sql.=" AND ( ";
//            foreach ($this->ElementiRicerca['MailAbilitate'] as $AccMail) {
//                $sql.=" ANAPRO.PROIDMAIL = '" . $AccMail['EMAIL'] . "' OR ";
//            }
//            $sql = substr($sql, 0, -3) . " ) ";
//        }


        $sql.=" ORDER BY PRODAR DESC, PROORA DESC, PRONUM DESC " . $limiteProt;


        $sqlDaInviare = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE ANADES.DESNUM=A.PRONUM AND ANADES.DESPAR=A.PROPAR AND ANADES.DESTIPO='D' AND DESMAIL<>''
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                WHERE ANAPRO.PRONUM=A.PRONUM AND ANAPRO.PROPAR=A.PROPAR AND ANAPRO.PROMAIL<>''
            )";

        $sqlInviate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE ANADES.DESNUM=A.PRONUM AND ANADES.DESPAR=A.PROPAR AND ANADES.DESTIPO='D' AND DESMAIL<>'' AND ANADES.DESIDMAIL<>''
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                WHERE ANAPRO.PRONUM=A.PRONUM AND ANAPRO.PROPAR=A.PROPAR AND ANAPRO.PROMAIL<>'' AND ANAPRO.PROIDMAILDEST<>''
            )";

        $sqlAccettate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANADES.DESIDMAIL 
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='D' AND 
                        ANADES.DESMAIL<>'' AND 
                        ANADES.DESIDMAIL<>'' AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_ACCETTAZIONE . "'
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANAPRO.PROIDMAILDEST                     
                WHERE   ANAPRO.PRONUM=A.PRONUM AND 
                        ANAPRO.PROPAR=A.PROPAR AND 
                        ANAPRO.PROMAIL<>'' AND 
                        ANAPRO.PROIDMAILDEST<>''AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_ACCETTAZIONE . "'
            )";

        $sqlConsegnate = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANADES.DESIDMAIL 
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='D' AND 
                        ANADES.DESMAIL<>'' AND 
                        ANADES.DESIDMAIL<>'' AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA . "'
            ) + (
                SELECT
                    COUNT(ANAPRO.ROWID)
                FROM
                    ANAPRO FORCE INDEX(I_PROPAR)
                LEFT OUTER JOIN {$this->ITALWEB_DB->getDB()}.MAIL_ARCHIVIO MAIL_ARCHIVIO ON MAIL_ARCHIVIO.IDMAILPADRE=ANAPRO.PROIDMAILDEST                     
                WHERE   ANAPRO.PRONUM=A.PRONUM AND 
                        ANAPRO.PROPAR=A.PROPAR AND 
                        ANAPRO.PROMAIL<>'' AND 
                        ANAPRO.PROIDMAILDEST<>''AND 
                        MAIL_ARCHIVIO.PECTIPO='" . emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA . "'
            )";
        $sqlTotAssegnati = "
            (
                SELECT
                    COUNT(ANADES.ROWID)
                FROM
                    ANADES FORCE INDEX(I_DESPAR)
                WHERE   ANADES.DESNUM=A.PRONUM AND 
                        ANADES.DESPAR=A.PROPAR AND 
                        ANADES.DESTIPO='T'
            ) ";



        $sqlRet = "
            SELECT * 
                FROM (
                    SELECT
                            A.*,
                            $sqlDaInviare AS DAINVIARE,
                            $sqlInviate   AS INVIATE,
                            $sqlAccettate   AS ACCETTATE,
                            $sqlConsegnate   AS CONSEGNATE,
                            $sqlTotAssegnati   AS TOTASSEGNATI
                        FROM
                    ({$sql}) A
                ) B
            WHERE 1=1 ";

        switch ($this->ElementiRicerca['TipoVedi']) {
            case 'E':
                $sqlRet.=" AND (
                            INVIATE - CONSEGNATE <> 0 OR
                            INVIATE - ACCETTATE <> 0  
                        )";
                break;
            case 'N':
                $sqlRet.=" AND (
                           (B.PROPAR ='A') AND TOTASSEGNATI = 0
                        )";
                break;
            case 'NF':
                $sqlRet.=" AND B.PROFASKEY = '' ";
                break;
            case 'IN':
                $sqlRet.=" AND B.PROSTATOPROT = " . proLib::PROSTATO_INCOMPLETO . " ";
                break;
            default:
                break;
        }

        return $sqlRet;
    }

    /*
     * $ElementiRicerca
     * ['Incompleti'] = SE VISUALIZZARE PROT. ICOMPLETI
     * ['LimiteVis'] = LIMITE DI PROT DA VISUALIZZARE.
     * ['TipoVedi]' = TIPOLOGIA DI VISIBILITA'
     * ['selectUffici'] = VISIBILITA UFFICI.
     * ['DaData'] = DA DATA 
     * ['VediTutti'] = Forzatura Vedi Tutti.
     * ['FLT_GRIGLIA'] = array(
     *      'PROPAR',
     *      'ANNO',
     *      'CODICE',
     *      'PRODAR',
     *      'PRONOM',
     *      'OGGOGG',
     *      'PROLRIS' )  
     */

    private function caricaDatiGriglia() {
        //$this->MostraNascondiCampi();
        $limiteProt = 101;
        if ($this->ElementiRicerca['LimiteVis']) {
            $limiteProt = ''; // Pensare ad un limite forzato?
        }
        if ($this->ElementiRicerca['VediTutti'] || $this->ElementiRicerca['LimiteVis'] == 'N') {
            $limiteProt = 9999; // Di più qualcosa non va..
        }

        $sql = $this->creaSql();
        $anapro_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
        // Oppure conteggio a parte.
        $this->ConteggioProtocolli = count($anapro_tab);
        // Check Non Fascicolati:
        $incr = 0;
        $this->tabella = array();
        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_38 = $this->proLib->GetAnaent('38');

        foreach ($anapro_tab as $key => $anapro_rec) {
            if ($this->ElementiRicerca['TipoVedi'] == 'NF') {
                if ($this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                    continue;
                }
            }
            $tagFatEle = '';
            $incr++;
            if ($limiteProt) {
                if ($incr >= $limiteProt) {
                    $this->ConteggioProtocolli = $incr . ' +';
                    break;
                }
            }

            $anapro_rec['ANNO'] = substr($anapro_rec['PRONUM'], 0, 4);
            $anapro_rec['CODICE'] = intval(substr($anapro_rec['PRONUM'], 4));
            $prodar = $anapro_rec['PRODAR'];
            if ($anapro_rec['PROPAR'] == 'C') {
                $anades_mitt = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
                if ($anades_mitt) {
                    $anapro_rec['PRONOM'] = $anades_mitt['DESNOM'];
                }
            }

            $allegati = $this->proLibAllegati->checkPresenzaAllegati($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            $ini_tag = "";
            $fin_tag = "";
            $NonAssegnato = "";
            if (!$anapro_rec['TOTASSEGNATI'] && $anapro_rec['PROPAR'] == 'A') {
                //$ini_tag = "<p style = 'background-color:orange;'>";// C'è già l'icona che indica non asseganto.
                $ini_tag = "<p style = ''>";
                $fin_tag = "</p>";
                $NonAssegnato = '<span class="ui-state-error ui-corner-all " title="Non Assegnato" style="border: 0;" >';
                $NonAssegnato.= '<div class="ita-html"  style="display:inline-block;" ><span class="ui-icon ui-icon-person ita-tooltip "></span></div></span>';
            }
            if ($anapro_rec['PROCAT'] == "0100" || $anapro_rec['PROCCA'] == "01000100") {
                $ini_tag = "<p style = 'background-color:yellow;'>";
                $fin_tag = "</p>";
            }
            if ($allegati) {
                $anapro_rec['PRONAF'] = '<span style="display:inline-block" class="ui-icon ui-icon-document">Con Allegati </span>';
            } else {
                if ($anaent_32['ENTDE4'] == 1) {
                    $ini_tag = "<p style = 'background-color:yellow;'>";
                    $fin_tag = "</p>";
                    $anapro_rec['PRONAF'] = '<span style="display:inline-block" class="ui-icon ui-icon-alert">Senza Allegati </span>';
                }
            }
            if ($anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $ini_tag = "<p style = 'color:white;background-color:black;font-weight:bold;'>";
                $fin_tag = "</p>";
            }
            if ($anapro_rec['PRORISERVA']) {
                $ini_tag = "<p style = 'color:white;background-color:gray;'>";
                $fin_tag = "</p>";
                $anapro_rec['PRONOM'] = "RISERVATO";
                $anapro_rec['OGGOGG'] = "RISERVATO";
            }
            if ($anapro_rec['PROCODTIPODOC']) {
                if ($anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE1'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE2'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE3'] ||
                        $anapro_rec['PROCODTIPODOC'] == $anaent_38['ENTDE4']) {
                    $tagFatEle = "<span style=\"display:inline-block;vertical-align:top;\" title=\"Fattura Elettronica\" class=\"ita-tooltip ita-icon ita-icon-euro-blue-16x16\"></span>";
                }
            }

            // Controllo speizione PEC!
            if (trim(strtoupper($anapro_rec['PROTSP'])) == 'PEC') {
                $ini_tagOggetto = '<div class="ita-html" style="width:18px; display:inline-block;vertical-align:top;"><span class="ita-tooltip ui-icon ui-icon-mail-closed" title="Da PEC"></span></div><div style="display:inline-block;vertical-align:top;">';
                $fin_tagOggetto = '</div>';
            } else {
                $ini_tagOggetto = '<div style="width:18px; display:inline-block;vertical-align:top;"></div><div style="display:inline-block;vertical-align:top;">';
                $fin_tagOggetto = '</div>';
            }
            //Valorizzazione di default
            //$anapro_rec['PRONAF'] ui-icon-notice
            /* Controllo se il protocollo è fascicolato */
            $nonFas = '';
            if (!$this->proLibFascicolo->CheckProtocolloInFascicolo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'])) {
                $nonFas = '<div class="ita-html" style="display:inline-block;" ><span style="display:inline-block" title="Non Fascicolato" class="ita-tooltip ui-icon ui-icon-notice">Non Fascicolato </span></div>';
            }
            $anapro_rec['PROPAR'] = $ini_tag . $anapro_rec['PROPAR'] . $fin_tag;
            $anapro_rec['TIPOPROT'] = $anapro_rec['PROPAR'];
            $anapro_rec['ANNO'] = $ini_tag . $anapro_rec['ANNO'] . $fin_tag;
            $anapro_rec['ANNOPROT'] = $anapro_rec['ANNO'];
            $anapro_rec['CODICE'] = $ini_tag . $anapro_rec['CODICE'] . $fin_tag;
            $anapro_rec['NUMEROPROT'] = $anapro_rec['CODICE'];
            $anapro_rec['PRODAR'] = $ini_tag . date("d/m/Y", strtotime($prodar)) . $fin_tag;
            $anapro_rec['DATAPROT'] = $prodar;
            $anapro_rec['PRONOM'] = $ini_tag . $anapro_rec['PRONOM'] . $fin_tag;
            $anapro_rec['PROVDEST'] = $anapro_rec['PRONOM'];
            $anapro_rec['OGGOGG'] = $ini_tagOggetto . $ini_tag . $anapro_rec['OGGOGG'] . $fin_tag . $fin_tagOggetto;
            $anapro_rec['OGGETTO'] = $anapro_rec['OGGOGG'];
            $anapro_rec['PROLRIS'] = $ini_tag . $anapro_rec['PROLRIS'] . $fin_tag;
            $anapro_rec['PRONAF'] = $tagFatEle . $nonFas . $anapro_rec['PRONAF'] . $NonAssegnato . '<p></p>';

            $risultato = $this->getStatoNotifiche($anapro_tab[$key]);
            $anapro_rec['NOTIFICA'] = $risultato['STATONOTIFICA'];
            $this->tabella[] = $anapro_rec;
        }
    }

    private function getStatoNotifiche($anapro_rec) {
        $statoNotifica = '';
        $AnomaliePEC = '';

        if ($anapro_rec['PRONUM'] && $anapro_rec['PROPAR']) {
            $NotificheMail = $this->proLibMail->GetElencoNotifichePecProt($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
            if ($NotificheMail['INDICE_ANOMALIE']) {
                $AnomaliePEC = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>";
            }
        }


        if ($anapro_rec['PROPAR'] === 'C') {
            return array('ANOMALIA' => false, 'STATONOTIFICA' => $statoNotifica);
        }
        $notificaTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-mail-closed"></span></div>';
        $notificaParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-mail-closed"></span></div>';
        $accTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-check"></span></div>';
        $accParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-check"></span></div>';
        $consTutti = '<div style="display:inline-block"><span class="ui-icon ui-icon-check"></span>';
        $consParziale = '<div style="display:inline-block; border:0;" class="ui-state-error"><span class="ui-icon ui-icon-check"></span></div>';
//        $risultatoNotifiche = $this->checkNotificaDestinatari($anapro_rec);
//        App::log($anapro_rec['PRONUM']." I: " . $risultatoNotifiche['INVIATE'] . " DI: " . $risultatoNotifiche['DAINVIARE'] . " C: " . $risultatoNotifiche['CONSEGNATE'] . " A: " . $risultatoNotifiche['ACCETTATE']);
        if ($anapro_rec['INVIATE'] == 0) {
            return array('ANOMALIA' => false, 'STATONOTIFICA' => '');
        }
        if ($anapro_rec['DAINVIARE'] - $anapro_rec['INVIATE'] == 0) {
            if ($anapro_rec['ACCETTATE'] == 0) {
                App::log($anapro_rec['PRONUM']);
                App::log(array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti));
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $$AnomaliePEC . $notificaTutti);
            }
            if ($anapro_rec['CONSEGNATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accParziale);
            }
            if ($anapro_rec['INVIATE'] - $anapro_rec['ACCETTATE'] != 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accParziale . $consParziale);
            }
            if ($anapro_rec['INVIATE'] - $anapro_rec['CONSEGNATE'] != 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accTutti . $consParziale);
            }
            return array('ANOMALIA' => false, 'STATONOTIFICA' => $AnomaliePEC . $notificaTutti . $accTutti . $consTutti);
        } else {
            if ($anapro_rec['ACCETTATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale);
            }
            if ($anapro_rec['CONSEGNATE'] == 0) {
                return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale . $accParziale);
            }
            return array('ANOMALIA' => true, 'STATONOTIFICA' => $AnomaliePEC . $notificaParziale . $accParziale . $consParziale);
        }
        return array('ANOMALIA' => false, 'STATONOTIFICA' => $AnomaliePEC);
    }

}

?>
