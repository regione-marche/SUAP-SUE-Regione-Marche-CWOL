<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
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
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

function proInviaTrasmissioni() {
    $proInviaTrasmissioni = new proInviaTrasmissioni();
    $proInviaTrasmissioni->parseEvent();
    return;
}

class proInviaTrasmissioni extends itaModel {

    public $PROT_DB;
    public $nameForm = "proInviaTrasmissioni";
    public $gridDestinatari = "proInviaTrasmissioni_gridDestinatari";
    public $proLib;
    public $proLibAllegati;
    public $proIterDest = array();
    public $appoggio = '';

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->proIterDest = App::$utente->getKey($this->nameForm . '_proIterDest');
        $this->appoggio = App::$utente->getKey($this->nameForm . '_appoggio');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_proIterDest', $this->proIterDest);
            App::$utente->setKey($this->nameForm . '_appoggio', $this->appoggio);
        }
    }

    public function getproIterDest() {
        return $this->proIterDest;
    }

    public function setproIterDest($proIterDest) {
        $this->proIterDest = $proIterDest;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openform':
                $this->proIterDest = array();
                Out::setAppTitle($this->nameForm, 'Seleziona i destinatari a cui inviare le Trasmissioni');
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    case $this->gridDestinatari:
                        break;
                }
                break;

            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case $this->gridDestinatari:
                        $this->proIterDest[$_POST['rowid']]['DESGESADD'] = $_POST['value'];
                        break;
                }
                break;

            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Dest_cod_butt':
                        proRic::proRicDestinatari($this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_Uff_cod_butt':
                        proRic::proRicAnauff($this->nameForm);
                        break;

                    case $this->nameForm . '_Conferma':
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_ConfermaDataTermine':
                        $this->proIterDest[$this->appoggio - 1]['TERMINE'] = $_POST[$this->nameForm . '_Termine'];
                        $this->caricaGriglia($this->gridDestinatari, $this->proIterDest);
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_cod':
                        $this->scaricaDest($_POST[$this->nameForm . '_Dest_cod']);
                        break;
                    case $this->nameForm . '_Uff_cod':
                        $this->scaricaDaCodiceUfficio($_POST[$this->nameForm . '_Uff_cod']);
                        break;
                }
                break;
            case 'cellSelect':
                switch ($this->elementId) {
                    case $this->gridDestinatari:
                        if (array_key_exists($_POST['rowid'], $this->proIterDest) == true) {
                            switch ($_POST['colName']) {
                                case 'TERMINE':
                                    $this->appoggio = $this->proIterDest[$_POST['rowid']]['INDICE'] + 1;
                                    Out::msgInput(
                                            'Data Termine', array(
                                        'label' => 'Data<br>',
                                        'id' => $this->nameForm . '_Termine',
                                        'name' => $this->nameForm . '_Termine',
                                        'type' => 'text',
                                        'size' => '15',
                                        'class' => "ita-date",
                                        'maxchars' => '12'), array(
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaDataTermine', 'model' => $this->nameForm)
                                            ), $this->nameForm . '_divGestione'
                                    );
                                    Out::valore($this->nameForm . '_Termine', $this->proIterDest[$_POST['rowid']]['TERMINE']);
                                    break;
                            }
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDestinatari:
                        if (array_key_exists($_POST['rowid'], $this->proIterDest) == true) {
                            unset($this->proIterDest[$_POST['rowid']]);
                        }
                        $this->caricaGriglia($this->gridDestinatari, $this->proIterDest);
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Dest_nome':
                        /* new suggest */
                        $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAMED WHERE MEDANN<>1 AND $filtroUff AND " . $where;
                        $anamed_tab = $this->proLib->getGenericTab($sql);
                        if (count($anamed_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anamed_tab as $anamed_rec) {
                                itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_Dest_cod" => $anamed_rec['MEDCOD']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_Uff_des':
                        /* new suggest */
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM ANAUFF WHERE " . $where;
                        $anauff_tab = $this->proLib->getGenericTab($sql);
                        if (count($anauff_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($anauff_tab as $anauff_rec) {
                                itaSuggest::addSuggest($anauff_rec['UFFDES'], array($this->nameForm . "_Uff_cod" => $anauff_rec['UFFCOD']));
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'returnUfficiPerDestinatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                $this->caricaDestinatari($this->appoggio, $anauff_rec['UFFCOD']);
                $this->appoggio = '';
                break;

            case 'returnDestinatari':
                /* FINE PATCH */
                if ($_POST['retKey']) {
                    $rowid_sel = explode(",", $_POST['retKey']);
                }
                //---controllo che non ci siano nominativi doppi
                $rowid_anamed = array();
                $rowid_err = array();
                $fl_msg = false;
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr1 = explode('-', $rowids);
                    if (array_search($rowid_arr1[2], $rowid_anamed) === false) {
                        $rowid_anamed[] = $rowid_arr1[2];
                    } else {
                        $rowid_err[] = $rowid_arr1[2];
                    }
                }
                if ($rowid_err) {
                    $nomi = "";
                    foreach ($rowid_err as $rowid) {
                        $anamed_rec = $this->proLib->GetAnamed($rowid, 'rowid');
                        $nomi .= "\n" . $anamed_rec['MEDNOM'];
                    }
                    $fl_msg = true;
                    //Out::msgStop("Attenzione", "I seguenti nominativi risultano selezionati più volte:\n\r" . $nomi);
                }
                //----
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr = explode('-', $rowids);
                    //$anaservizi_rec = $this->proLib->getAnaservizi($rowid_arr[0], 'rowid');
                    $anauff_rec = $this->proLib->GetAnauff($rowid_arr[1], 'rowid');
                    $anamed_rec = $this->proLib->GetAnamed($rowid_arr[2], 'rowid');
                    //$anaruo_rec = $this->proLib->getAnaruoli($rowid_arr[3], 'rowid');
                    if (!$anamed_rec) {
                        continue;
                    }
                    $inserisci = true;
                    if (array_search($rowid_arr[2], $rowid_err) !== false) {
                        $inserisci = false;
                    }
                    foreach ($this->proArriDest as $value) {
                        if ($anamed_rec['MEDCOD'] == $value['DESCOD']) {
                            $inserisci = false;
                            break;
                        }
                    }
                    if ($inserisci == true) {
                        $this->caricaDestinatari($anamed_rec['MEDCOD'], $anauff_rec['UFFCOD']);
                    }
                    Out::valore($this->nameForm . '_Dest_cod', "");
                    Out::valore($this->nameForm . "_Dest_nome", "");
                }
                if ($fl_msg) {
                    Out::msgStop("Attenzione", "I seguenti nominativi risultano selezionati più volte:\n\r" . $nomi);
                }
                break;

            case 'returnanauff':
                $this->scaricaDaCodiceUfficio($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_proIterDest');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($this->returnModel != '') {
            $_POST = array();
            $_POST['destinatari'] = $this->proIterDest;
            $_POST['annotazioni'] = $_POST[$this->nameForm . '_Annotazioni'];
            $returnObj = itaModel::getInstance($this->returnModel);
            $returnObj->setEvent($this->returnEvent);
            $returnObj->parseEvent();
        }
        if ($close) {
            $this->close();
        }
    }

    private function caricaDestinatari($codiceSoggetto, $codiceUfficio, $desges = 1) {
        $soggetto = proSoggetto::getInstance($this->proLib, $codiceSoggetto, $codiceUfficio);
        if (!$soggetto) {
            return false;
        }

        $record = $soggetto->getSoggetto();
        $inserisci = true;
        foreach ($this->proIterDest as $value) {
            if ($record['CODICESOGGETTO'] == $value['DESCODADD']) {
                $inserisci = false;
                break;
            }
        }

        if ($inserisci == true) {
            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESCODADD'] = $record['CODICESOGGETTO'];
            $salvaDest['DESNOMADD'] = $record['DESCRIZIONESOGGETTO'];
            $salvaDest['DESNOMEUFF'] = $record['DESCRIZIONESOGGETTO'] . ' - ' . $record['DESCRIZIONEUFFICIO'];
            if ($record['RUOLO']) {
                $salvaDest['DESNOMADD'] .= " - " . $record['RUOLO'];
            }
            $salvaDest['DESNOMADD'] .= " - " . $record['DESCRIZIONEUFFICIO'];
            if ($record['SERVIZIO']) {
                $salvaDest['DESNOMADD'] .= " - " . $record['SERVIZIO'];
            }

            $salvaDest['ITEUFF'] = $record['CODICEUFFICIO'];
            $salvaDest['ITERUO'] = $record['CODICERUOLO'];
            $salvaDest['ITESETT'] = $record['CODICESERVIZIO'];

            $salvaDest['DESGESADD'] = $desges;
            if (count($this->proIterDest) > 0) {
                // $salvaDest['DESGESADD'] = 0;
            }
            $salvaDest['TERMINE'] = '';
            $this->proIterDest[] = $salvaDest;
            Out::show($this->nameForm . '_divDestGrid');
            $this->CaricaGriglia($this->gridDestinatari, $this->proIterDest);
        }
    }

    private function scaricaDest($codice, $tipo = 'codice') {
        if (trim($codice) != "") {
            if ($tipo == 'codice') {
                if (is_numeric($codice)) {
                    $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                }
            }
            $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no', false, true);
            if ($anamed_rec) {
                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND UFFDES.UFFCESVAL='' AND ANAUFF.UFFANN=0");
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    $this->caricaDestinatari($codice, $anauff_rec['UFFCOD']);
                } else {
                    $this->appoggio = $codice;
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', $codice);
                }
            }
        }
        Out::valore($this->nameForm . "_Dest_cod", "");
        Out::valore($this->nameForm . '_Dest_nome', "");
    }

    private function scaricaDaCodiceUfficio($codice, $tipo = 'codice') {
        if (trim($codice) != "") {
            if ($tipo == 'codice') {
                if (is_numeric($codice)) {
                    $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                } else {
                    $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                }
            }
            $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        } else {
            return;
            ;
        }
        if ($anauff_rec) {
            $this->scaricaUfficio($anauff_rec);
        }
        Out::valore($this->nameForm . '_Uff_des', "");
        Out::valore($this->nameForm . "_Uff_cod", "");
    }

    function scaricaUfficio($anauff_rec) {
        if ($anauff_rec['UFFANN'] == 1) {
            Out::msgInfo("Decodifica Ufficio", "ATTENZIONE.<BR>Uffico " . $anauff_rec['UFFCOD'] . "  " . $anauff_rec['UFFDES'] . " non più utilizzabile. Annullato.");
        } else if ($anauff_rec) {

            $uffdes_tab = $this->proLib->GetUffdes($anauff_rec['UFFCOD'], 'uffcod', true, ' ORDER BY UFFFI1__3 DESC', true);
            foreach ($uffdes_tab as $uffdes_rec) {
                if ($uffdes_rec['UFFSCA']) {
                    $ges = $uffdes_rec['UFFFI1__1'];
                    $this->caricaDestinatari($uffdes_rec['UFFKEY'], $anauff_rec['UFFCOD'], $ges);
                }
            }
        }
    }

    private function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

}

?>
