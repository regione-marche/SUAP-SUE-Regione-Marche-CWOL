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
 * @version    29.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerFactory.class.php';

function proDettConservazione() {
    $proDettConservazione = new proDettConservazione();
    $proDettConservazione->parseEvent();
    return;
}

class proDettConservazione extends itaModel {

    public $PROT_DB;
    public $nameForm = "proDettConservazione";
    public $gridElencoCons = "proDettConservazione_gridElencoCons";
    public $proLib;
    public $proLibAllegati;
    public $proLibConservazione;
    public $ConservazioniTab = array();
    public $IndiceRowid;

    function __construct() {
        parent::__construct();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibConservazione = new proLibConservazione();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->ConservazioniTab = App::$utente->getKey($this->nameForm . '_ConservazioniTab');
        $this->IndiceRowid = App::$utente->getKey($this->nameForm . '_IndiceRowid');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_ConservazioniTab', $this->ConservazioniTab);
            App::$utente->setKey($this->nameForm . '_IndiceRowid', $this->IndiceRowid);
        }
    }

    public function getIndiceRowid() {
        return $this->IndiceRowid;
    }

    public function setIndiceRowid($IndiceRowid) {
        $this->IndiceRowid = $IndiceRowid;
    }

    public function getProArriAlle() {
        return $this->ConservazioniTab;
    }

    public function setProArriAlle($ConservazioniTab) {
        $this->ConservazioniTab = $ConservazioniTab;
    }

    public function parseEvent() {
        parent::parseEvent();
        if ($this->proLib->checkStatoManutenzione()) {
            Out::msgStop("ATTENZIONE!", "<br>Il protocollo è nello stato di manutenzione.<br>Attendere la riabilitazione o contattare l'assistenza.<br>");
            return;
        }
        switch ($this->event) {
            case 'openDettaglio':
                $this->openDettaglio();
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    case $this->gridElencoCons:

                        break;
                }
                break;
            case 'cellSelect':
                switch ($this->elementId) {
                    case $this->gridElencoCons:
                        $allegato = $this->ConservazioniTab[$_POST['rowid']];
                        switch ($_POST['colName']) {
                            case 'SCARICAESITO':
                                $this->ScaricaAnadoc($allegato['DOCESITO']);
                                break;
                            case 'SCARICAVERSAMENTO':
                                $this->ScaricaAnadoc($allegato['DOCVERSAMENTO']);
                                break;

                            case 'VEDIRDV':
                                if (!$allegato['VEDIRDV']) {
                                    break;
                                }
                                $Anapro_rec = $this->proLib->GetAnapro($this->IndiceRowid, 'rowid');
                                $rowid = $_POST[$this->gridElencoCons]['gridParam']['selarrrow'];
                                if (!$Anapro_rec) {
                                    Out::msgStop("Attenzione", "Protocollo di riferimento inesistente.");
                                    break;
                                }
                                /* Controllo se protocllo è versato */
                                //$ProConser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                                $ProConser_rec = $this->proLibConservazione->GetProConser($allegato['ROWID'], 'rowid');

                                if (!$ProConser_rec || !$ProConser_rec['UUIDSIP']) {
                                    Out::msgStop("Attenzione", "Il protocollo non risulta essere versato in conservazione.");
                                    break;
                                }
                                /*
                                 * Controllo rdv già salvato
                                 */
                                if ($ProConser_rec['DOCRDV']) {
                                    // Scarico diretto
                                    $this->ScaricaAnadoc($ProConser_rec['DOCRDV']);
                                } else {
                                    $ObjManager = proConservazioneManagerFactory::getManager();
                                    if (!$ObjManager) {
                                        Out::msgStop('Attenzione', 'Errore in istanza Manager.');
                                        break;
                                    }
                                    $UnitaDoc = $this->proLibConservazione->GetUnitaDocumentaria($Anapro_rec);
                                    /*
                                     * Setto Chiavi Anapro
                                     */
                                    $ObjManager->setRowidAnapro($Anapro_rec['ROWID']);
                                    $ObjManager->setAnapro_rec($Anapro_rec);
                                    $ObjManager->setUnitaDocumentaria($UnitaDoc);
                                    //
                                    if (!$ObjManager->parseXmlRDV($ProConser_rec['UUIDSIP'], $ProConser_rec['PENDINGUUID'])) {
                                        Out::msgStop('Attenzione', 'Errore in istanza Manager.' . $ObjManager->getErrMessage());
                                        break;
                                    }
                                    Out::msgBlock('', 3000, false, "Verifica della Ricevuta di Versamento effettuata correttamente. Il file RDV verrà scaricato a breve.");
                                    $ProConser_rec = $this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                                    $this->ScaricaAnadoc($ProConser_rec['DOCRDV']);
                                }
                                break;
                        }
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_IndiceRowid');
        App::$utente->removeKey($this->nameForm . '_ConservazioniTab');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openDettaglio() {
        //$this->IndiceRowid
        $this->CaricaGrigliaConservazione();
        // Carico Div Info:
        $Anapro_rec = $this->proLib->GetAnapro($this->IndiceRowid, 'rowid');
        $Protocollo = $Anapro_rec['PROPAR'] . ' - ' . substr($Anapro_rec['PRONUM'], 0, 4) . '/' . substr($Anapro_rec['PRONUM'], 4);
        $Descrizione = '<span style="padding:10px; font-size:14px; color:red; "><b>';
        $Descrizione .= 'Protocollo: ' . $Protocollo . ' </b></span>';
        Out::addClass($this->nameForm . '_divInfoProtocollo', "ui-corner-all ui-state-highlight");
        Out::html($this->nameForm . '_divInfoProtocollo', $Descrizione);
    }

    private function CaricaGrigliaConservazione() {

        $Anapro_rec = $this->proLib->GetAnapro($this->IndiceRowid, 'rowid');
        $sql = "SELECT * FROM PROCONSER WHERE PRONUM = " . $Anapro_rec['PRONUM'] . " AND PROPAR =  '" . $Anapro_rec['PROPAR'] . "' ";
        $sql .= " ORDER BY DATAVERSAMENTO DESC ,ORAVERSAMENTO DESC";
        $ProConser_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql, true);
        $iconaInfo = "<span style=\" display:inline-block\" class=\"ui-icon ui-icon-comment\"></span>";

        foreach ($ProConser_tab as $key => $ProConser_rec) {
            if ($ProConser_rec['DOCESITO']) {
                $ProConser_tab[$key]['SCARICAESITO'] = "<span style=\"margin-left:10px;\" class=\"ita-icon ita-icon-download-32x32\"></span>";
            }
            if ($ProConser_rec['DOCVERSAMENTO']) {
                $ProConser_tab[$key]['SCARICAVERSAMENTO'] = "<span style=\"margin-left:10px;\" class=\"ita-icon ita-icon-download-32x32\"></span>";
            }
            //RDV
            if ($ProConser_rec['UUIDSIP']) {
                $ProConser_tab[$key]['VEDIRDV'] = "<span style=\"margin-left:10px;\" class=\"ita-icon ita-icon-publish-24x24\"></span>";
            }
            // Descrizione Esito:
            $msgTooltip = '';
            if ($ProConser_rec['NOTECONSERV']) {
                $msgTooltip.= $ProConser_rec['NOTECONSERV'];
            }
            $MotivoEsito = $ProConser_rec['ESITOVERSAMENTO'];
            if ($ProConser_rec['MESSAGGIOERRORE']) {
                $msgTooltip.= $ProConser_rec['MESSAGGIOERRORE'];
            }
            if ($msgTooltip) {
                $MotivoEsito = "<span style=\"display:inline-block\" class=\" ita-tooltip\"  title =\"" . $msgTooltip . "\">" . $ProConser_rec['ESITOVERSAMENTO'] . "</span>" . $iconaInfo;
            }

            $ProConser_tab[$key]['ESITOVERSAMENTO'] = "<div style=\"margin-left:5px;\" class=\"ita-html\">" . $MotivoEsito . "</div>";
        }
        $this->ConservazioniTab = $ProConser_tab;
        $this->CaricaGriglia($this->gridElencoCons, $ProConser_tab);
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

    public function ScaricaAnadoc($docname) {
        $Anapro_rec = $this->proLib->GetAnapro($this->IndiceRowid, 'rowid');
        $sql = "SELECT * FROM ANADOC WHERE DOCNUM =" . $Anapro_rec['PRONUM'] . " AND DOCPAR = '" . $Anapro_rec['PROPAR'] . "'"
                . " AND DOCNAME = '$docname' ";
        $Anadoc_rec = ItaDB::DBSQLSelect($this->PROT_DB, $sql, false);
        $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
        $filepath = $protPath . "/" . $Anadoc_rec['DOCFIL'];

        $force = false;
        if (strtolower(pathinfo($filepath, PATHINFO_EXTENSION)) == 'xml') {
            $force = true;
        }
        Out::openDocument(utiDownload::getUrl($Anadoc_rec['DOCNAME'], $filepath, $force));
    }

}

?>
