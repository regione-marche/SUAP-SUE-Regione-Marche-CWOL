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

function proSeleFascicolo() {
    $proSeleFascicolo = new proSeleFascicolo();
    $proSeleFascicolo->parseEvent();
    return;
}

class proSeleFascicolo extends itaModel {

    public $nameForm = "proSeleFascicolo";
    public $proLib;
    public $PROT_DB;
    public $gridFascicoli = "proSeleFascicolo_gridFascicoli";
    public $gridSottoFascicoli = "proSeleFascicolo_gridSottoFascicoli";
    public $gridFiltersFascicoli = array();
    public $gridFiltersSottoFascicoli = array();
    public $Titolario = array();
    public $fascicoliAperti;
    public $AnaproFascicolo = array();
    public $UltimoSelezionato = array();
    public $proLibFascicolo;
    public $AlberoSelezione = array();
    public $rowidSelezionato = array();
    public $SingoloFascicolo = '';
    public $AbilitaCreazione = '';

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->gridFiltersFascicoli = App::$utente->getKey($this->nameForm . '_gridFiltersFascicoli');
            $this->gridFiltersSottoFascicoli = App::$utente->getKey($this->nameForm . '_gridFiltersSottoFascicoli');
            $this->Titolario = App::$utente->getKey($this->nameForm . '_Titolario');
            $this->SingoloFascicolo = App::$utente->getKey($this->nameForm . '_SingoloFascicolo');
            $this->AbilitaCreazione = App::$utente->getKey($this->nameForm . '_AbilitaCreazione');
            $this->fascicoliAperti = App::$utente->getKey($this->nameForm . '_fascicoliAperti');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->AnaproFascicolo = App::$utente->getKey($this->nameForm . '_AnaproFascicolo');
        $this->UltimoSelezionato = App::$utente->getKey($this->nameForm . '_UltimoSelezionato');
        $this->AlberoSelezione = App::$utente->getKey($this->nameForm . '_AlberoSelezione');
        $this->rowidSelezionato = App::$utente->getKey($this->nameForm . '_rowidSelezionato');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_AnaproFascicolo', $this->AnaproFascicolo);
            App::$utente->setKey($this->nameForm . '_gridFiltersFascicoli', $this->gridFiltersFascicoli);
            App::$utente->setKey($this->nameForm . '_gridFiltersSottoFascicoli', $this->gridFiltersSottoFascicoli);
            App::$utente->setKey($this->nameForm . '_Titolario', $this->Titolario);
            App::$utente->setKey($this->nameForm . '_UltimoSelezionato', $this->UltimoSelezionato);
            App::$utente->setKey($this->nameForm . '_AlberoSelezione', $this->AlberoSelezione);
            App::$utente->setKey($this->nameForm . '_rowidSelezionato', $this->rowidSelezionato);
            App::$utente->setKey($this->nameForm . '_SingoloFascicolo', $this->SingoloFascicolo);
            App::$utente->setKey($this->nameForm . '_AbilitaCreazione', $this->AbilitaCreazione);
            App::$utente->setKey($this->nameForm . '_fascicoliAperti', $this->fascicoliAperti);
        }
    }

    public function getTitolario() {
        return $this->Titolario;
    }

    public function setTitolario($Titolario) {
        $this->Titolario = $Titolario;
    }

    public function setSingoloFascicolo($SingoloFascicolo) {
        $this->SingoloFascicolo = $SingoloFascicolo;
    }

    public function setAbilitaCreazione($AbilitaCreazione) {
        $this->AbilitaCreazione = $AbilitaCreazione;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if ($this->SingoloFascicolo) {
                    $Anaorg_rec = $this->proLib->GetAnaorg($this->SingoloFascicolo, 'rowid');
                    Out::setAppTitle($this->nameForm, 'Fascicolo: ' . $Anaorg_rec['ORGKEY']);
                }
                $this->fascicoliAperti = false;
                $this->CaricaFascicoli();
                Out::hide($this->gridFascicoli . '_addGridRow');
                if ($this->AbilitaCreazione === true) {
                    $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                    if ($permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
                        Out::show($this->gridFascicoli . '_addGridRow');
                    }
                }
                break;

            case 'addGridRow':
                $permessiFascicolo = $this->proLibFascicolo->GetPermessiFascicoli();
                if (!$permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
                    Out::msgStop("Attenzione", "Non hai il permesso di creare un fascicolo.");
                    break;
                }
                if ($this->Titolario['VERSIONE_T'] !== $this->proLib->GetTitolarioCorrente()) {
                    Out::msgInfo("Attenzione", "Il titolario dove creare il fascicolo non è quello corrente operazione non consentita.");
                    break;
                }
                $Dati = array('TITOLARIO' => $this->Titolario);
                $model = $this->returnModel;
                $formObj = itaModel::getInstance($model);
                $formObj->setEvent('onClick');
                $formObj->setElementId($this->returnId);
                $formObj->setReturnData($Dati);
                $formObj->parseEvent();
                $this->returnToParent();
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
                    case $this->gridSottoFascicoli:
                        $this->rowidSelezionato = $_POST['rowid'];
                        $Anaorg_rec = $this->proLib->GetAnaorg($this->AnaproFascicolo['PROFASKEY'], 'orgkey');
                        $ProGes_rec = $this->proLib->GetProges($Anaorg_rec['ORGKEY'], 'codice');
                        $AnaproSottoFascicolo_rec = $this->proLib->GetAnapro($this->rowidSelezionato, 'rowid');
                        $permessiSottoFascicolo = $this->proLibFascicolo->GetPermessiFascicoli('', $ProGes_rec['GESNUM'], 'gesnum', $AnaproSottoFascicolo_rec['PRONUM'], $AnaproSottoFascicolo_rec['PROPAR']);
                        /* Per i sottofascicoli, un controllo ulteriore di permessi, potrebbero essere estesi da un sottofasciolo padre. */
                        $resCheck = $this->proLibFascicolo->CheckPermessiDocumentoSottofascicolo($AnaproSottoFascicolo_rec['PRONUM'], $AnaproSottoFascicolo_rec['PROPAR'], $ProGes_rec['GESNUM']);
                        if (!$permessiSottoFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI] && (!$resCheck['GESTIONE'] || !$resCheck[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI])) {
                             Out::msgInfo("Fascicolazione", "Non possiedi i premessi per inserire il protocollo nel fascicolo, poiché non ti è stato trasmesso in Gestione.<br>Contattare il responsabile del fascicolo.");
                            break;
                        }
                        $AnaOgg_rec = $this->proLib->GetAnaogg($AnaproSottoFascicolo_rec['PRONUM'], $AnaproSottoFascicolo_rec['PROPAR']);
                        $Messaggio = 'Vuoi selezionare il Sottofascicolo: <b>' . $AnaproSottoFascicolo_rec['PROSUBKEY'] . '</b> ?' . '<br><br><b>Oggetto del sottofascicolo:</b><br> ' . $AnaOgg_rec['OGGOGG'] . '';
                        $DescTitolario = $this->DecodificaDescTitolarioCorrente();
                        $Messaggio.='<br><br>' . $DescTitolario;
                        Out::msgQuestion("Selezione", $Messaggio, array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaaSeleSottoFascicolo', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSeleSottoFascicolo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridFascicoli:
                        switch ($_POST['colName']) {
                            case 'SOTTOFASCICOLI_FAS':
                                $Anaorg_rec = $this->proLib->GetAnaorg($_POST['rowid'], 'rowid');
                                if ($Anaorg_rec) {
                                    $AnaproFascicolo_rec = $this->proLib->GetAnapro($Anaorg_rec['ORGKEY'], 'fascicolo');
                                    if ($AnaproFascicolo_rec) {
                                        $this->AnaproFascicolo = $AnaproFascicolo_rec;
                                        $Orgcon_tab = $this->proLibFascicolo->GetOrgconParent($AnaproFascicolo_rec['PRONUM'], $AnaproFascicolo_rec['PROPAR'], 'N');
                                        if ($Orgcon_tab) {
                                            $this->UltimoSelezionato['PRONUM'] = $AnaproFascicolo_rec['PRONUM'];
                                            $this->UltimoSelezionato['PROPAR'] = $AnaproFascicolo_rec['PROPAR'];
                                            $this->AlberoSelezione[] = $AnaproFascicolo_rec;
                                            $this->CaricaSottoFascicoli();
                                        }
                                    }
                                }
                                break;
                        }
                        break;
                    case $this->gridSottoFascicoli:
                        switch ($_POST['colName']) {
                            case 'SOTTOFASCICOLI':
                                $AnaproSottoFascicolo = $this->proLib->GetAnapro($_POST['rowid'], 'rowid');
                                if ($AnaproSottoFascicolo) {
                                    $Orgcon_tab = $this->proLibFascicolo->GetOrgconParent($AnaproSottoFascicolo['PRONUM'], $AnaproSottoFascicolo['PROPAR'], 'N');
                                    if ($Orgcon_tab) {
                                        $this->UltimoSelezionato['PRONUM'] = $AnaproSottoFascicolo['PRONUM'];
                                        $this->UltimoSelezionato['PROPAR'] = $AnaproSottoFascicolo['PROPAR'];
                                        $this->AlberoSelezione[] = $AnaproSottoFascicolo;
                                        $this->CaricaSottoFascicoli();
                                    }
                                }
                                break;
                        }
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridFascicoli:
                        $this->setGridFiltersFascicoli();
                        $sql = $this->CreaSql($this->gridFiltersFascicoli);
                        $this->CaricaGrigliaFascicoli($sql);
                        break;
                    case $this->gridSottoFascicoli:
                        $this->setGridFiltersSottoFascicoli();
                        $sql = $this->CreaSqlSottoFascicolo($this->gridFiltersSottoFascicoli);
                        $this->CaricaGrigliaSottoFascicoli($sql);
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaSeleSottoFascicolo':
                        $Anaorg_rec = $this->proLib->GetAnaorg($this->AnaproFascicolo['PROFASKEY'], 'orgkey');
                        $AnaproSottoFascicolo_rec = $this->proLib->GetAnapro($this->rowidSelezionato, 'rowid');
                        $key = 'PRO-' . $AnaproSottoFascicolo_rec['PRONUM'] . $AnaproSottoFascicolo_rec['PROPAR'];
                        $Dati = array();
                        $Dati['ROWID_ANAORG'] = $Anaorg_rec['ROWID'];
                        $Dati['ROWID_ANAPRO'] = $AnaproSottoFascicolo_rec['ROWID'];
                        $Dati['retKey'] = $key;
                        $_POST = array();
                        $_POST['retKey'] = $key;
                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->setReturnData($Dati);
                        $returnObj->parseEvent();
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ConfermaSeleFascicolo':
                        $Anaorg_rec = $this->proLib->GetAnaorg($this->rowidSelezionato, 'rowid');
                        $AnaproFascicolo_rec = $this->proLib->GetAnapro($Anaorg_rec['ORGKEY'], 'fascicolo');
                        $key = 'PRO-' . $AnaproFascicolo_rec['PRONUM'] . $AnaproFascicolo_rec['PROPAR'];
                        $Dati = array();
                        $Dati['ROWID_ANAORG'] = $Anaorg_rec['ROWID'];
                        $Dati['ROWID_ANAPRO'] = $AnaproFascicolo_rec['ROWID'];
                        $Dati['retKey'] = $key;
                        $_POST = array();
                        $_POST['retKey'] = $key;
                        $returnObj = itaModel::getInstance($this->returnModel);
                        $returnObj->setEvent($this->returnEvent);
                        $returnObj->setReturnData($Dati);
                        $returnObj->parseEvent();
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_AltroTitolario':
                        $this->AlberoSelezione = array();
                        $this->fascicoliAperti = false;
                        //proRic::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        proRicTitolario::proRicTitolarioVersione($this->PROT_DB, $this->proLib, $this->nameForm, $this->Titolario['VERSIONE_T'], '', 'returnTitolario', 3, true);
                        break;
                    case $this->nameForm . '_CercaAperti':
                        $this->AlberoSelezione = array();
                        $this->fascicoliAperti = true;
                        $this->CaricaFascicoli();

                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                break;
            case 'TornaElenco':
                $this->AlberoSelezione = array();
                $this->CaricaFascicoli();
                break;

            case 'TornaASottofascicolo':
                $rowid = $_POST['id'];
                $AnaproFascicolo_rec = $this->AlberoSelezione[$rowid];
                $this->UltimoSelezionato['PRONUM'] = $AnaproFascicolo_rec['PRONUM'];
                $this->UltimoSelezionato['PROPAR'] = $AnaproFascicolo_rec['PROPAR'];
                $AlberoSelezione = $this->AlberoSelezione;
                foreach ($AlberoSelezione as $key => $AlberoSelezionato) {
                    if ($key > $rowid) {
                        unset($this->AlberoSelezione[$key]);
                    }
                }
                $this->CaricaSottoFascicoli();
                break;

            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolario($rowid, $tipoArc);
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
        App::$utente->removeKey($this->nameForm . '_AnaproFascicolo');
        App::$utente->removeKey($this->nameForm . '_gridFiltersFascicoli');
        App::$utente->removeKey($this->nameForm . '_gridFiltersSottoFascicoli');
        App::$utente->removeKey($this->nameForm . '_Titolario');
        App::$utente->removeKey($this->nameForm . '_UltimoSelezionato');
        App::$utente->removeKey($this->nameForm . '_AlberoSelezione');
        App::$utente->removeKey($this->nameForm . '_rowidSelezionato');
        App::$utente->removeKey($this->nameForm . '_SingoloFascicolo');
        App::$utente->removeKey($this->nameForm . '_AbilitaCreazione');
        App::$utente->removeKey($this->nameForm . '_fascicoliAperti');
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

    public function setGridFiltersSottoFascicoli() {
        $this->gridFiltersSottoFascicoli = array();
        if ($_POST['OGGOGG'] != '') {
            $this->gridFiltersSottoFascicoli['OGGOGG'] = $_POST['OGGOGG'];
        }
        if ($_POST['PROSUBKEY'] != '') {
            $this->gridFiltersSottoFascicoli['PROSUBKEY'] = $_POST['PROSUBKEY'];
        }
        if ($_POST['NOME_RESPON_SF'] != '') {
            $this->gridFiltersSottoFascicoli['NOME_RESPON_SF'] = $_POST['NOME_RESPON_SF'];
        }
        if ($_POST['SOTTOFASCICOLI'] != '') {
            $this->gridFiltersSottoFascicoli['SOTTOFASCICOLI'] = $_POST['SOTTOFASCICOLI'];
        }
    }

    public function CreaSql($filters = array()) {
        $procat = $this->Titolario['PROCAT'];
        $clacod = $this->Titolario['CLACOD'];
        $fascod = $this->Titolario['FASCOD'];
        $organn = $this->Titolario['ORGANN'];

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
        if ($this->fascicoliAperti === false) {
            $sql.= "AND ORGCCF='{$procat}{$clacod}{$fascod}'";
        }
//        $sql = "SELECT ANAORG.*, 
//                       PROGES.GESOGG AS GESOGG ,
//                       PROGES.GESKEY AS GESKEY,
//                       PROGES.GESRES AS GESRES,
//                       ANAMED.MEDNOM AS NOME_RESPONSABILE,
//                       (SELECT COUNT(ROWID) FROM ORGCONN ORGNEWCONN WHERE ORGNEWCONN.PRONUMPARENT=ANAPRO.PRONUM AND ORGNEWCONN.PROPARPARENT=ANAPRO.PROPAR AND ORGNEWCONN.PROPAR='N' AND ORGNEWCONN.CONNDATAANN = '') AS SOTTOFASCICOLI_FAS
//                        FROM ANAORG 
//                    LEFT OUTER JOIN PROGES ON ANAORG.ORGKEY = PROGES.GESKEY
//                    LEFT OUTER JOIN ANAMED ANAMED ON PROGES.GESRES=ANAMED.MEDCOD
//                    LEFT OUTER JOIN ANAPRO ANAPRO ON ANAORG.ORGKEY=ANAPRO.PROFASKEY AND ANAPRO.PROPAR = 'F'
//                    LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR                    
//                    ";
//        $sql.= " WHERE
//                    ORGDAT='' AND 
//                    ORGCCF='{$procat}{$clacod}{$fascod}' AND 
//                    GESDCH = '' ";


        if ($organn) {
            $sql.=" AND ORGANN = '$organn' ";
        }
        if ($this->SingoloFascicolo) {
            $sql.=" AND ANAORG.ROWID = '$this->SingoloFascicolo' ";
        }

        /*
         * Introddotto 28/04/2016 insieme a JOIN ARCITE vedi sopra
         * 
         * LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
         * 
         */
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
        // qui andrebbe comunque controllata la visibilita su un fascicolo?
        // Dove controlla se può movimentare su fascicolo??? Elenco dei fascioli deve essere limitato?
        return $sql;
    }

    public function CreaSqlSottoFascicolo($filters = array()) {
        $sql = "
            SELECT
                ANAPRO.ROWID AS ROWID,
                ORGCONN.PRONUM,
                ORGCONN.PROPAR,
                ANAPRO.PRODAR,
                ANAPRO.PROORA,
                ANAPRO.PRORISERVA,
                ANAPRO.PROTSO,
                ANAPRO.PROSUBKEY,
                ANAOGG.OGGOGG,
                PROPAS.PRORPA,
                ANAMED.MEDNOM AS NOME_RESPON_SF,
                (SELECT COUNT(ROWID) FROM ORGCONN ORGNEWCONN WHERE ORGNEWCONN.PRONUMPARENT=ORGCONN.PRONUM AND ORGNEWCONN.PROPARPARENT=ORGCONN.PROPAR AND ORGNEWCONN.PROPAR='N' AND ORGNEWCONN.CONNDATAANN = '') AS SOTTOFASCICOLI
            FROM
                ORGCONN
            LEFT OUTER JOIN ANAPRO ANAPRO ON ANAPRO.PRONUM=ORGCONN.PRONUM AND ANAPRO.PROPAR=ORGCONN.PROPAR
            LEFT OUTER JOIN ANAOGG ANAOGG ON ANAPRO.PRONUM=ANAOGG.OGGNUM AND ANAPRO.PROPAR=ANAOGG.OGGPAR
            LEFT OUTER JOIN PROPAS PROPAS ON ORGCONN.PRONUM = PROPAS.PASPRO AND ORGCONN.PROPAR=PROPAS.PASPAR
            LEFT OUTER JOIN ANAMED ANAMED ON PROPAS.PRORPA=ANAMED.MEDCOD
            LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR               
            WHERE
                ORGCONN.CONNDATAANN = '' AND 
                ORGCONN.PRONUMPARENT='{$this->UltimoSelezionato['PRONUM']}' AND ORGCONN.PROPARPARENT='{$this->UltimoSelezionato['PROPAR']}'";

        $sql .= " AND ANAPRO.PROPAR='N' ";

        /*
         * Introddotto 28/04/2016 insieme a JOIN ARCITE vedi sopra
         * 
         * LEFT OUTER JOIN ARCITE ARCITE ON ANAPRO.PRONUM=ARCITE.ITEPRO AND ANAPRO.PROPAR=ARCITE.ITEPAR
         * 
         */
        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, 'fascicolo');
        $sql .= " AND (($where_profilo) OR ( ORGCONN.PROPAR = 'N')) ";

        $sql .= " GROUP BY ANAPRO.ROWID";


        $sql = "SELECT * FROM (" . $sql . ") AS SOTTOFASC WHERE 1 ";
        if ($filters) {
            foreach ($filters as $key => $value) {
                if ($key == 'SOTTOFASCICOLI') {
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

    private function elaboraRecordsSottoFascicoli($result_tab) {
        foreach ($result_tab as $key => $result_rec) {
            //$Orgcon_tab = $this->proLibFascicolo->GetOrgconParent($result_rec['PRONUM'], $result_rec['PROPAR'], 'N');
            $sottoFascicoli = '';
            if ($result_rec['SOTTOFASCICOLI'] > 0) {
                $icon = "ita-icon-sub-folder-32x32";
                $tooltip = $result_rec['SOTTOFASCICOLI'] . ' SOTTOFASCICOLI';
                $sottoFascicoli = '<div class="ita-html"><span style="height:16px;background-size:50%;margin:2px;"  title="' . $tooltip . '" class="ita-tooltip ita-icon ' . $icon . '"></span></div>';
            }
            $result_tab[$key]['SOTTOFASCICOLI'] = $sottoFascicoli;
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

    private function CaricaGrigliaSottoFascicoli($sql) {
        TableView::clearGrid($this->gridSottoFascicoli);
        $ita_grid01 = new TableView($this->gridSottoFascicoli, array(
            'sqlDB' => $this->PROT_DB,
            'sqlQuery' => $sql));
        $ordinamento = $_POST['sidx'];
        if (!$ordinamento) {
            $ordinamento = 'OGGOGG';
        }

        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        $ita_grid01->setSortIndex($ordinamento);
        $ita_grid01->setSortOrder($_POST['sord']);

        $result_tab = $ita_grid01->getDataArray();
        if (!$ita_grid01->getDataPageFromArray('json', $this->elaboraRecordsSottoFascicoli($result_tab))) {
            Out::msgInfo("Selezione", "Nessun sottofascicolo trovato.");
        }
        TableView::enableEvents($this->gridSottoFascicoli);
        return;
    }

    private function CaricaFascicoli() {
        Out::show($this->nameForm . '_divFascicoli');
        Out::hide($this->nameForm . '_divSottoFascicoli');
        $sql = $this->CreaSql();
        $this->CaricaGrigliaFascicoli($sql);
        if ($this->SingoloFascicolo) {
            $Descrizione = '<b>Seleziona il Fascicolo o un suo Sottofascicolo</b><br><br>';
        } else {

            $Descrizione = '<b>Seleziona un Fascicolo </b><br><br>';
        }
        $DescTitolario = $this->DecodificaDescTitolarioCorrente() . '<br><br>';
        Out::html($this->nameForm . '_divDescrizioneFascicolo', $DescTitolario . $Descrizione);
    }

    private function DecodificaDescTitolarioCorrente() {
        $DescTitolario = '';
        $cat = $cla = $fas = $org = $des = '';
        App::log('$this->Titolario  d');
        App::log($this->Titolario);
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

    private function CaricaSottoFascicoli() {
        // qui descrizione
        Out::hide($this->nameForm . '_divFascicoli');
        Out::show($this->nameForm . '_divSottoFascicoli');
        $this->AggiornaDescrizioneFascicolo();
        $sql = $this->CreaSqlSottoFascicolo();
        $this->CaricaGrigliaSottoFascicoli($sql);
    }

    private function AggiornaDescrizioneFascicolo() {
        $DescTornaElenco = 'Torna a Elenco';
        $DescFascicolo = '<b>Fascicolo:</b> ' . $this->AnaproFascicolo['PROFASKEY'];
        if ($this->SingoloFascicolo) {
            $DescTornaElenco = 'Torna al Fascicolo';
            $DescFascicolo = '<b>Fascicolo</b> ';
        }
        // Descrizione Torna Indietro e Fascicolo
        $Descrizione = '<span style="position:absolute; right:20px;"><a href="#" id="" class="ita-hyperlink {event:\'TornaElenco\'}"><b>' . $DescTornaElenco . '</b>' . ' <span class="ita-icon-rotate-left-16x16" style="display:inline-block;vertical-align:middle;height:16px;"> </span></a></span><br>';
        $Descrizione .= '<span class="ita-icon-next-16x16" style="display:inline-block;vertical-align:middle;height:16px;"></span>';
        $Descrizione .= ' <a href="#" id="' . 0 . '" class="ita-hyperlink {event:\'TornaASottofascicolo\'}"><span title="Torna" >';
        $Descrizione.= $DescFascicolo . ' </a><br>';

        foreach ($this->AlberoSelezione as $key => $AnaproSelezionato) {
            if ($AnaproSelezionato['PROPAR'] == 'F') {
                continue;
            }
            $Descrizione .= '<span style = "display:inline-block; margin-left: ' . $key . '0px;"></span><span class = "ita-icon-next-16x16" style = "display:inline-block;vertical-align:middle;height:16px;"></span>';
            $Descrizione .= ' <a href = "#" id = "' . $key . '" class = "ita-hyperlink {event:\'TornaASottofascicolo\'}"><span title = "Torna" >';
            $CodSottoFasc = str_replace($AnaproSelezionato['PROFASKEY'] . '-', '', $AnaproSelezionato['PROSUBKEY']);
            // Prendo Oggetto Sottofascicolo
            $Anaogg_rec = $this->proLib->GetAnaogg($AnaproSelezionato['PRONUM'], $AnaproSelezionato['PROPAR']);
            $DescSottoFasc = substr($Anaogg_rec['OGGOGG'], 0, 200);
            $Descrizione.='<b>Sottofascicolo ' . $CodSottoFasc . '</b></span></a>: ' . $DescSottoFasc . '<br>';
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

}

?>
