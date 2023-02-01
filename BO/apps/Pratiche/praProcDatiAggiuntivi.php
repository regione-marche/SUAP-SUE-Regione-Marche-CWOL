<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praDatiAggiuntivi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';

function praProcDatiAggiuntivi() {
    $praProcDatiAggiuntivi = new praProcDatiAggiuntivi();
    $praProcDatiAggiuntivi->parseEvent();
    return;
}

class praProcDatiAggiuntivi extends itaModel {

    public $praLib;
    /* @var $praDatiAggiuntivi praDatiAggiuntivi */
    public $praDatiAggiuntivi;
    public $PRAM_DB;
    public $nameForm = 'praProcDatiAggiuntivi';
    public $gridDatiAggiuntivi = 'praProcDatiAggiuntivi_gridDatiAggiuntivi';
    private $pranum;
    private $itekey;
    private $datiSelezionati;

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->praLib = new praLib();

        $this->PRAM_DB = ItaDB::DBOpen('PRAM');

        $this->pranum = App::$utente->getKey($this->nameForm . '_pranum');
        $this->itekey = App::$utente->getKey($this->nameForm . '_itekey');
        $this->datiSelezionati = App::$utente->getKey($this->nameForm . '_datiSelezionati');

        $this->praDatiAggiuntivi = unserialize(App::$utente->getKey($this->nameForm . '_praDatiAggiuntivi'));

        $this->gridDatiAggiuntivi = $this->nameForm . '_gridDatiAggiuntivi';
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_pranum', $this->pranum);
            App::$utente->setKey($this->nameForm . '_itekey', $this->itekey);
            App::$utente->setKey($this->nameForm . '_datiSelezionati', $this->datiSelezionati);
            App::$utente->setKey($this->nameForm . '_praDatiAggiuntivi', serialize($this->praDatiAggiuntivi));
        }
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        $this->caricaGrigliaDatiAggiuntivi();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        $rowid = $filePdf = '';

                        if ($this->itekey) {
                            $itepas_rec = $this->praLib->GetItepas($this->itekey, 'itekey');
                            $ditta = App::$utente->getKey('ditta');
                            $rowid = $itepas_rec['ROWID'];
                            $filePdf = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/' . $itepas_rec['ITEWRD'];
                        }

                        $model = 'praPassoProcAgg';

                        /* @var $praPassoProcObj praPassoProcAgg */
                        $praPassoProcObj = itaModel::getInstance($model);

                        if (!$praPassoProcObj) {
                            Out::msgStop("Errore", "Apertura dato aggiuntivo fallita.");
                            break;
                        }

                        $praPassoProcObj->setEvent('openform');
                        $praPassoProcObj->setReturnEvent('returnPraPassoProcAgg');
                        $praPassoProcObj->setReturnModel($this->nameForm);
                        $praPassoProcObj->setReturnModelOrig($this->nameFormOrig);
                        $praPassoProcObj->setDatiAggiuntivi($this->praDatiAggiuntivi->GetDatiAggiuntivi());

                        $_POST['chiamante'] = $rowid;
                        $_POST['filePdf'] = $filePdf;

                        itaLib::openForm($model);
                        $praPassoProcObj->parseEvent();
                        break;
                }
                break;

            case 'copyGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        $sourceDatoAggiuntivo = $this->praDatiAggiuntivi->GetDatoAggiuntivo($_POST['rowid']);
                        unset($sourceDatoAggiuntivo['ROWID']);
                        $this->praDatiAggiuntivi->SetDatoAggiuntivo($sourceDatoAggiuntivo);
                        $this->caricaGrigliaDatiAggiuntivi();
                        TableView::setSelection($this->gridDatiAggiuntivi, count($this->praDatiAggiuntivi->getDatiAggiuntivi()) - 1);
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        Out::msgQuestion("ATTENZIONE!", "L'operazione è irreversibile. <br>Desidere cancellare il campo aggiuntivo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaDatoAgg', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaDatoAgg', 'model' => $this->nameForm, 'shortCut' => "f5")
                            )
                        );
                        break;
                }
                break;

            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridDatiAggiuntivi:
                        $idxDati = $_POST['rowid'];
                        $rowid = $filePdf = '';

                        if ($this->itekey) {
                            $itepas_rec = $this->praLib->GetItepas($this->itekey, 'itekey');
                            $ditta = App::$utente->getKey('ditta');
                            $rowid = $itepas_rec['ROWID'];
                            $filePdf = Config::getPath('general.itaProc') . 'ente' . $ditta . '/testiAssociati/' . $itepas_rec['ITEWRD'];
                        }

                        $model = 'praPassoProcAgg';

                        /* @var $praPassoProcObj praPassoProcAgg */
                        $praPassoProcObj = itaModel::getInstance($model);

                        if (!$praPassoProcObj) {
                            Out::msgStop("Errore", "Apertura dato aggiuntivo fallita.");
                            break;
                        }

                        $praPassoProcObj->setEvent('openform');
                        $praPassoProcObj->setReturnEvent('returnPraPassoProcAgg');
                        $praPassoProcObj->setReturnModel($this->nameForm);
                        $praPassoProcObj->setReturnModelOrig($this->nameFormOrig);
                        $praPassoProcObj->setDatiAggiuntivi($this->praDatiAggiuntivi->GetDatiAggiuntivi());

                        $_POST['chiamante'] = $rowid;
                        $_POST['filePdf'] = $filePdf;
                        $_POST['datoAgg'] = $this->praDatiAggiuntivi->GetDatoAggiuntivo($idxDati);
                        $_POST['idxDati'] = $idxDati;

                        itaLib::openForm($model);
                        $praPassoProcObj->parseEvent();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaDatoAgg':
                        $datiAggiuntivi = $this->praDatiAggiuntivi->GetDatiAggiuntivi();
                        $rowidDato = $_POST[$this->gridDatiAggiuntivi]['gridParam']['selrow'];

                        if ($datiAggiuntivi[$rowidDato]['ITDTIC'] == 'RadioGroup') {
                            $radioButtonFound = false;

                            foreach ($datiAggiuntivi as $key => $itedag_rec) {
                                $metaRadioButton = is_array($itedag_rec['ITDMETA']) ? $itedag_rec['ITDMETA'] : unserialize($itedag_rec['ITDMETA']);

                                if ($metaRadioButton['ATTRIBUTICAMPO']['NAME'] == $datiAggiuntivi[$rowidDato]['ITDKEY']) {
                                    $radioButtonFound = true;
                                    break;
                                }
                            }

                            if ($radioButtonFound) {
                                Out::msgStop("Attenzione", "Impossibile Cancellare il campo agguntivo RadioGroup " . $datiAggiuntivi[$rowidDato]['ITDKEY'] . " perché ci sono campi radio a lui collegati.");
                                break;
                            }
                        }

                        if (!$this->praDatiAggiuntivi->CancellaDatoAggiuntivo($rowidDato, $this)) {
                            Out::msgStop("Attenzione", "Errore in cancellazione dato su ITEDAG");
                        }

                        if (!$this->praDatiAggiuntivi->ordinaDatiAggiuntivi($this, $this->itekey)) {
                            Out::msgStop("ATTENZIONE!", "Errore Aggiornamento sequenza dati aggiuntivi.");
                        }

                        $this->caricaGrigliaDatiAggiuntivi();
                        break;

                    case $this->nameForm . '_ImportaCampi':
                        $this->praDatiAggiuntivi = praDatiAggiuntivi::getInstance($this->praLib, $this->pranum, $this->itekey, true);

                        $this->caricaGrigliaDatiAggiuntivi();

                        Out::show($this->nameForm . '_CancellaCampi');
                        Out::show($this->nameForm . '_EsportaDati');
                        Out::show($this->nameForm . '_ApriModelloNomeCampi');
                        break;

                    case $this->nameForm . '_CancellaCampi':
                        praRic::praDatiSelezionati($this->praDatiAggiuntivi->GetDatiAggiuntivi(), array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                            ), '', "Cancellazione Dati Aggiuntivi");
                        break;

                    case $this->nameForm . "_ConfermaSelCan":
                        $dati = $this->praDatiAggiuntivi->GetDatiAggiuntivi();
                        foreach ($this->datiSelezionati as $dato) {
                            foreach ($dati as $keyAgg => $datoAgg) {
                                if ($dato['ITDKEY'] == $datoAgg['ITDKEY'] && $dato['ITEKEY'] == $datoAgg['ITEKEY'] && $dato['ITECOD'] == $datoAgg['ITECOD']) {
                                    if (!$this->praDatiAggiuntivi->CancellaDatoAggiuntivo($keyAgg, $this)) {
                                        Out::msgStop('Cancellazione Dati Aggiuntivi', 'Errore nella cancellazione del dato aggiuntivo ' . $dato['ITDKEY']);
                                        break;
                                    }
                                }
                            }
                        }

                        $this->caricaGrigliaDatiAggiuntivi();
                        break;

                    case $this->nameForm . '_EsportaDati':
                        $ExportDati = '';

                        $this->datiFiltrati = $this->praDatiAggiuntivi->GetDatiAggiuntivi();

                        foreach ($this->datiFiltrati as $key => $dato) {
                            $ExportDati .= $dato['ITDKEY'] . ';';
                        }

                        $ExportDati .= "\r\n";
                        $ExportDati = substr($ExportDati, 0, strlen($ExportDati) - 1);

                        foreach ($this->datiFiltrati as $key => $dato) {
                            $ExportDati .= $dato['ITDVAL'] . ' ";"';
                        }

                        if (!is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop('Esportazione Dati', 'Creazione ambiente di lavoro temporaneo fallita');
                                break;
                            }
                        }

                        $nome_file = itaLib::getAppsTempPath() . '/ExportDati.csv';

                        if (file_put_contents($nome_file, $ExportDati)) {
                            Out::openDocument(
                                utiDownload::getUrl(
                                    'exportDati_' . $this->pranum . '_' . $this->itekey . '.csv', $nome_file
                                )
                            );
                        }
                        break;

                    case $this->nameForm . '_ApriModelloNomeCampi':
                        if (!is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop('Gestione campi aggiuntivi', 'Creazione ambiente di lavoro temporaneo fallita');
                                break;
                            }
                        }

                        $itepas_rec = $this->praLib->GetItepas($this->itekey, 'itekey');

                        $itewrd = $itepas_rec['ITEWRD'];
                        $datiAggiuntivi = $this->praDatiAggiuntivi->GetDatiAggiuntivi();

                        foreach ($datiAggiuntivi as $Itedag_rec) {
                            $chiavecampo = ($Itedag_rec['ITDALIAS']) ? $Itedag_rec['ITDALIAS'] : $Itedag_rec['ITDKEY'];
                            $dati[$chiavecampo] = $Itedag_rec['ITDALIAS'];
                        }

                        $ditta = App::$utente->getKey('ditta');
                        $input = Config::getPath('general.itaProc') . "ente$ditta/testiAssociati/$itewrd";
                        $output = itaLib::getPrivateUploadPath() . "/filled_$itewrd";

                        if ($this->praLib->FillFormPdf($dati, $input, $output)) {
                            Out::openDocument(utiDownload::getUrl(
                                    "filled_{$itewrd}.pdf", $output
                                )
                            );
                        } else {
                            Out::msgStop('Compila Modello', 'Errore in Compilazione campi Modello');
                        }
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'returnPraPassoProcAgg':
                $this->praDatiAggiuntivi->SetDatoAggiuntivo($_POST['datoAggiuntivo'], $_POST['idxDati']);
                $this->caricaGrigliaDatiAggiuntivi();
                TableView::setSelection($this->gridDatiAggiuntivi, $_POST['idxDati']);
                break;

            case 'returnDatiSel':
                $dati = $this->praDatiAggiuntivi->GetDatiAggiuntivi();
                $this->datiSelezionati = array();
                $selRows = explode(",", $_POST['retKey']);
                foreach ($dati as $dato) {
                    if (in_array($dato['ROWID'], $selRows)) {
                        $this->datiSelezionati[] = $dato;
                    }
                }

                if (count($this->datiSelezionati)) {
                    Out::msgQuestion('Cancellazione Dati Aggiuntivi', 'Hai selezionato ' . count($this->datiSelezionati) . ' dati aggiuntivi. Vuoi Continuare?', array(
                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaSel', 'model' => $this->nameForm, 'shortCut' => 'f8'),
                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaSelCan', 'model' => $this->nameForm, 'shortCut' => 'f5')
                    ));
                }
                break;

            case 'returnItepas':
                $rowid_itepas = $_POST['retKey'];
                $itepas_rec = $this->praLib->GetItepas($this->itekey, 'itekey');

                if ($rowid_itepas == $itepas_rec['ROWID']) {
                    $this->datiSelezionati = $this->praDatiAggiuntivi->GetDatiAggiuntivi();
                } else {
                    $sql = "SELECT ITEDAG.* FROM ITEDAG LEFT OUTER JOIN ITEPAS ON ITEPAS.ITEKEY = ITEDAG.ITEKEY WHERE ITEPAS.ROWID = '$rowid_itepas' ORDER BY ITDSEQ";
                    $this->datiSelezionati = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
                }

                $nameForm = array('nameForm' => $this->nameForm, 'nameFormOrig' => $this->nameFormOrig);
                praRic::praDatiSelezionati($this->datiSelezionati, $nameForm, '', 'Ricerca dati aggiuntivi', true, 'Seleziona i dati aggiuntivi da duplicare.', 'returnDatiAggSelezionati');
                break;

            case 'returnDatiAggSelezionati':
                $rowids = explode(',', $_POST['retKey']);
                if (!count($rowids)) {
                    break;
                }

                $datiAggiuntiviDuplicati = array();
                foreach ($this->datiSelezionati as $datoAggiuntivo) {
                    if (in_array($datoAggiuntivo['ROWID'], $rowids)) {
                        unset($datoAggiuntivo['ROWID']);
                        $datoAggiuntivo['ITEKEY'] = $this->itekey;
                        $datiAggiuntiviDuplicati[] = $datoAggiuntivo;
                    }
                }

                $currentDatiAggiuntivi = $this->praDatiAggiuntivi->GetDatiAggiuntivi();
                if (!count($currentDatiAggiuntivi)) {
                    $sequenza = 0;
                    foreach ($datiAggiuntiviDuplicati as $datoAggiuntivoDuplicato) {
                        $sequenza += 10;
                        $datoAggiuntivoDuplicato['ITDSEQ'] = $sequenza;
                        $this->praDatiAggiuntivi->SetDatoAggiuntivo($datoAggiuntivoDuplicato);
                    }

                    $this->caricaGrigliaDatiAggiuntivi();
                    Out::msgBlock('', 1000, true, "Dati aggiuntivi duplicati con successo.");
                    break;
                }

                $this->datiSelezionati = $datiAggiuntiviDuplicati;

                $nameForm = array('nameForm' => $this->nameForm, 'nameFormOrig' => $this->nameFormOrig);
                praRic::praDatiSelezionati($this->praDatiAggiuntivi->GetDatiAggiuntivi(), $nameForm, '', 'Ricerca dati aggiuntivi', false, 'Inserisci i dati aggiuntivi duplicati dopo la sequenza...', 'returnDatiAggSequenza');
                break;

            case 'returnDatiAggSequenza':
                $datiAggiuntivi = $this->praDatiAggiuntivi->GetDatiAggiuntivi();

                $keys = array_keys($datiAggiuntivi);
                array_multisort(array_column($datiAggiuntivi, 'ITDSEQ'), SORT_ASC, SORT_NUMERIC, $datiAggiuntivi, $keys);
                $datiAggiuntivi = array_combine($keys, $datiAggiuntivi);

                /*
                 * Inserisco i dati duplicati
                 */

                $sequenzaDatiAggiuntivi = false;
                $datoAggiuntivoBreak = $_POST['retKey'];
                foreach ($datiAggiuntivi as $idx => $datoAggiuntivo) {
                    if ($datoAggiuntivo['ROWID'] == $datoAggiuntivoBreak) {
                        $sequenzaDatiAggiuntivi = $datoAggiuntivo['ITDSEQ'];

                        foreach ($this->datiSelezionati as $datoAggiuntivoDuplicato) {
                            $sequenzaDatiAggiuntivi += 10;
                            $datoAggiuntivoDuplicato['ITDSEQ'] = $sequenzaDatiAggiuntivi;
                            $this->praDatiAggiuntivi->SetDatoAggiuntivo($datoAggiuntivoDuplicato);
                        }

                        continue;
                    }

                    if ($sequenzaDatiAggiuntivi) {
                        $sequenzaDatiAggiuntivi += 10;
                        $datoAggiuntivo['ITDSEQ'] = $sequenzaDatiAggiuntivi;
                        $this->praDatiAggiuntivi->SetDatoAggiuntivo($datoAggiuntivo, $idx);
                    }
                }

                $this->caricaGrigliaDatiAggiuntivi();
                Out::msgBlock('', 1000, true, "Dati aggiuntivi duplicati con successo.");
                break;
        }
    }

    private function caricaGrigliaDatiAggiuntivi() {
        TableView::clearGrid($this->gridDatiAggiuntivi);
        $ita_grid = new TableView($this->gridDatiAggiuntivi, array('arrayTable' => $this->praDatiAggiuntivi->getGriglia(), 'rowIndex' => 'idx'));
        $ita_grid->setPageNum($_POST['page'] ?: 1);
        $ita_grid->setPageRows($_POST['rows'] ?: 1000);
        $ita_grid->setSortIndex($_POST['sidx'] ?: 'ITDSEQ');
        $ita_grid->setSortOrder($_POST['sord'] ?: 'asc');
        return $ita_grid->getDataPage('json');
    }

    public function close() {
        parent::close();

        App::$utente->removeKey($this->nameForm . '_pranum');
        App::$utente->removeKey($this->nameForm . '_itekey');
        App::$utente->removeKey($this->nameForm . '_datiSelezionati');
        App::$utente->removeKey($this->nameForm . '_praDatiAggiuntivi');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function openGestione($pranum, $itekey = false) {
        $this->pranum = $pranum;
        $this->itekey = $itekey;

        if (!$this->itekey) {
            $this->itekey = $this->pranum;
        }

        $this->praDatiAggiuntivi = praDatiAggiuntivi::getInstance($this->praLib, $this->pranum, $this->itekey);

        TableView::enableEvents($this->gridDatiAggiuntivi);
        $this->caricaGrigliaDatiAggiuntivi();

        Out::hide($this->nameForm . '_ImportaCampi');
        Out::hide($this->nameForm . '_CancellaCampi');
        Out::hide($this->nameForm . '_EsportaDati');
        Out::hide($this->nameForm . '_ApriModelloNomeCampi');

        if ($itekey) {
            $itepas_rec = $this->praLib->GetItepas($itekey, 'itekey');

            if ($itepas_rec['ITEWRD'] != '' && pathinfo($itepas_rec['ITEWRD'], PATHINFO_EXTENSION) == 'pdf') {
                Out::show($this->nameForm . '_ImportaCampi');

                if (count($this->praDatiAggiuntivi->GetDatiAggiuntivi())) {
                    Out::show($this->nameForm . '_CancellaCampi');
                    Out::show($this->nameForm . '_EsportaDati');
                    Out::show($this->nameForm . '_ApriModelloNomeCampi');
                }
            }
        }
    }

    public function GetDatiAggiuntivi() {
        if ($this->praDatiAggiuntivi) {
            return $this->praDatiAggiuntivi->GetDatiAggiuntivi();
        }

        return array();
    }

    public function aggiornaDati() {
        $msgdato = $this->praDatiAggiuntivi->RegistraDatiAggiuntivi($this);

        if (!$this->praDatiAggiuntivi->ordinaDatiAggiuntivi($this, $this->itekey)) {
            Out::msgStop("ATTENZIONE!", "Errore Aggiornamento sequenza dati aggiuntivi.");
            return false;
        }

        if ($msgdato !== true) {
            Out::msgStop("ATTENZIONE!", $msgdato);
            return false;
        }

        $this->openGestione($this->pranum, $this->itekey);

        return true;
    }

    public function duplicaDati() {
        $this->datiSelezionati = array();
        $nameForm = array('nameForm' => $this->nameForm, 'nameFormOrig' => $this->nameFormOrig);
        praRic::praRicItepas($nameForm, 'ITEPAS', " WHERE ITECOD = '{$this->pranum}'", '', 'Seleziona il passo di origine');
    }

}
