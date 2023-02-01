<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function accGestioneRichieste() {
    $accGestioneRichieste = new accGestioneRichieste();
    $accGestioneRichieste->parseEvent();
    return;
}

class accGestioneRichieste extends itaModel {

    public $ITW_DB;
    public $accLib;
    public $nameForm = "accGestioneRichieste";
    public $gridGestioneRichieste = "accGestioneRichieste_gridGestioneRichieste";

    function __construct() {
        parent::__construct();
        try {
            $this->private = false;
            $this->accLib = new accLib();
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $id = $this->getReturnId();
                
                if ($id) {
                    $this->openGestione($id);
                    break;
                }

                $this->openRisultato();
                break;

            case 'dbClickRow':
            case 'editGridRow':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->gridGestioneRichieste:
                        $this->openGestione($_POST['rowid']);
                }
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->gridGestioneRichieste:
                        TableView::clearGrid($this->gridGestioneRichieste);
                        $gridScheda = new TableView($this->gridGestioneRichieste, array(
                            'sqlDB' => $this->ITW_DB,
                            'sqlQuery' => $this->accLib->SqlRichiesteUtenti()
                        ));
                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex($_POST['sidx']);
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPageFromArray('json', $this->elaboraRecords($gridScheda->getDataArray()));
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . "_Conferma":
                        $fields = array(array(
                                'label' => array(
                                    'value' => "Nome utente",
                                    'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                ),
                                'id' => $this->nameForm . '_UTENTI[UTELOG]',
                                'name' => $this->nameForm . '_UTENTI[UTELOG]',
                                'type' => 'text',
                                'class' => 'required',
                                'style' => 'margin-left:5px;width:120px;'
                            ), array(
                                'label' => array(
                                    'value' => "Password",
                                    'style' => 'width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                                ),
                                'id' => $this->nameForm . '_UTENTI[UTEPAS]',
                                'name' => $this->nameForm . '_UTENTI[UTEPAS]',
                                'type' => 'password',
                                'class' => 'required',
                                'style' => 'margin-left:5px;width:120px;'
                            )
                        );
                        Out::msgInput('Conferma Utente', $fields, array(
                            'F5-Conferma' => array(
                                'id' => $this->nameForm . '_ConfermaUtente',
                                'model' => $this->nameForm,
                                'class' => 'ita-button-validate',
                                'shortCut' => "f5"
                            )), $this->nameForm . "_workSpace"
                        );
                        break;

                    case $this->nameForm . "_ConfermaUtente":
                        Out::closeCurrentDialog();
                        $utenti_rec = $this->confermaUtente($_POST[$this->nameForm . '_RICHUT']['ROWID']);
                        if ($utenti_rec) {
                            Out::msgInfo("Conferma Avvenuta", "Utente validato correttamente.");
                            $this->openRisultato();
                        }
                        break;

                    case $this->nameForm . "_Visualizza":
                        $richut_rec = $this->accLib->GetRichut($_POST[$this->nameForm . '_RICHUT']['ROWID'], 'rowid');
                        $utenti_rec = $this->accLib->GetUtenti($richut_rec['RICCOD'], 'codice');

                        $model = "accUtenti";
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setRowid($utenti_rec['ROWID']);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->nameForm . '_Reimposta':
                        $richut_rec = $this->accLib->GetRichut($_POST[$this->nameForm . '_RICHUT']['ROWID'], 'rowid');
                        $utenti_rec = $this->accLib->GetUtenti($richut_rec['RICCOD'], 'codice');

                        $model = "accPassword";
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnAccPassword');
                        $formObj->setEvent('openform');
                        $formObj->setModo('reset');
                        $formObj->setNomeUtenteGestito($utenti_rec['UTELOG']);
                        $formObj->setCodiceUtenteGestito($richut_rec['RICCOD']);
                        $formObj->parseEvent();

                        break;

//					case $this->nameForm . "_Modifica":
//						$richut_rec = $_POST[$this->nameForm . '_RICHUT'];
//						if ( !$this->updateRecord($this->ITW_DB, 'RICHUT', $richut_rec, $richut_rec['RICNOM'] . $richut_rec['RICCOG']) ) {
//							Out::msgStop("Errore", "Impossibile aggiornare il record");
//							break;
//						}
//						Out::msgBlock($this->nameForm, 600, false, 'Record aggiornato');
//						break;

                    case $this->nameForm . "_Elimina":
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaElimina', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaElimina', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;

                    case $this->nameForm . "_ConfermaElimina":
                        $richut_rec = $_POST[$this->nameForm . '_RICHUT'];
                        if (!$this->deleteRecord($this->ITW_DB, 'RICHUT', $richut_rec['ROWID'], $richut_rec['RICNOM'] . $richut_rec['RICCOG'])) {
                            Out::msgStop("Errore", "Impossibile eliminare il record");
                            break;
                        }
                        $this->openRisultato();
                        break;

                    case $this->nameForm . "_Stampa":
                        Out::msgQuestion("Stampa", "\nUna volta stampati i dati, la richiesta sarà rimossa e sarà necessario contattare l'utente. Proseguire?", array(
                            'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaStampa', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaStampa', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;

                    case $this->nameForm . "_ConfermaStampa":
                        $richut_rec = $_POST[$this->nameForm . '_RICHUT'];

                        include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
                        $utiEnte = new utiEnte();

                        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => "SELECT RICHUT.ROWID, RICHUT.RICPWD, RICHUT.RICIIP, UTENTI.UTECOD, UTENTI.UTELOG, UTENTI.UTEDPA, UTENTI.UTEFIL__1 FROM UTENTI LEFT OUTER JOIN RICHUT ON RICHUT.RICCOD = UTENTI.UTECOD WHERE RICHUT.ROWID = '{$richut_rec['ROWID']}'", "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                        $itaJR->runSQLReportPDF($this->ITW_DB, 'accGestioneRichieste', $parameters);

                        $richut_rec['RICTIP'] = " ";
                        $richut_rec['RICPWD'] = "";

                        if (!$this->updateRecord($this->ITW_DB, 'RICHUT', $richut_rec, $richut_rec['RICNOM'] . $richut_rec['RICCOG'])) {
                            Out::msgStop("Errore", "Impossibile aggiornare il record");
                            break;
                        }

                        $this->openRisultato();
                        break;

                    case $this->nameForm . "_Torna":
                        $this->openRisultato();
                        break;

                    case 'close-portlet':
                        break;
                }
                break;

            case 'returnAccPassword':
                if (isset($_POST['returnUtecod']) && isset($_POST['returnPassword']) && isset($_POST['returnEncPassword'])) {
                    $richut_rec = $this->accLib->GetRichut($_POST['returnUtecod'], 'codice');
                    $richut_rec['RICPWD'] = $_POST['returnPassword'];
                    $richut_rec['RICSTA'] = accLib::RICHIESTA_EVASA;
                    $richut_rec['RICTIP'] = "1";
                    if (!$this->updateRecord($this->ITW_DB, 'RICHUT', $richut_rec, $richut_rec['RICNOM'] . $richut_rec['RICCOG'])) {
                        Out::msgStop("Errore", "Impossibile aggiornare il record");
                        break;
                    }
                    $this->openGestione($richut_rec['ROWID']);
                }
                break;
        }
    }

    public function close() {
        
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function elaboraRecords($table) {
        foreach ($table as $k => $record) {
            switch ($record['RICSTA']) {
                case accLib::RICHIESTA_NUOVA:
                    $table[$k]['RICSTA'] = 'Nuovo utente';
                    break;
                case accLib::RICHIESTA_RESET_PASSWORD:
                    $table[$k]['RICSTA'] = 'Reset password';
                    break;
                case accLib::RICHIESTA_EVASA:
                    $table[$k]['RICSTA'] = 'Evasa';
                    break;
            }
        }
        return $table;
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons) {
        Out::hide($this->nameForm . '_Conferma');
        Out::hide($this->nameForm . '_Reimposta');
        Out::hide($this->nameForm . '_Stampa');
        Out::hide($this->nameForm . '_Visualizza');
        Out::hide($this->nameForm . '_Elimina');
        Out::hide($this->nameForm . '_Torna');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        Out::hideLayoutPanel($this->nameForm . '_buttonBar');

        TableView::enableEvents($this->gridGestioneRichieste);
        TableView::reload($this->gridGestioneRichieste);
    }

    public function openGestione($rowid) {
        $this->mostraForm('divGestione');
        Out::showLayoutPanel($this->nameForm . '_buttonBar');

        Out::clearFields($this->nameForm);
        Out::setFocus('', $this->nameForm . '_RICHUT[RICCOG]');

        $richut_rec = $this->accLib->GetRichut($rowid, 'rowid');
        $utenti_rec = $this->accLib->GetUtenti($richut_rec['RICCOD']);

        switch ($richut_rec['RICSTA']) {
            case accLib::RICHIESTA_NUOVA:
                $this->mostraButtonBar(array('Conferma', 'Elimina', 'Torna'));
                break;

            case accLib::RICHIESTA_RESET_PASSWORD:
                $this->mostraButtonBar(array('Reimposta', 'Visualizza', 'Torna'));
                break;

            default:
                $stampa = $richut_rec['RICTIP'] == '1' ? 'Stampa' : '';
                $this->mostraButtonBar(array('Reimposta', 'Visualizza', 'Torna', $stampa));
                break;
        }

        Out::valori($richut_rec, $this->nameForm . '_RICHUT');
        Out::valore($this->nameForm . '_UTELOG', $utenti_rec['UTELOG']);

        TableView::disableEvents($this->gridGestioneRichieste);
    }

    public function confermaUtente($rowid) {
        $retLock = ItaDB::DBLock($this->ITW_DB, "UTENTI", "", "", 20);
        if ($retLock['status'] !== 0) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Blocco UTENTI");
            return false;
        }

        $tmp_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT MAX(UTECOD) AS UTECOD FROM UTENTI", false);
        $new_utecod = ++$tmp_rec['UTECOD'];

        $utenti_rec_new = $_POST[$this->nameForm . '_UTENTI'];
        $utenti_rec_new['UTECOD'] = $new_utecod;
        $utenti_rec_new['UTESPA'] = date('Ymd');
        $utenti_rec_new['UTEDPA'] = '180';
        $utenti_rec_new['UTEFIL__1'] = '1';

        if (!ItaDB::DBInsert($this->ITW_DB, "UTENTI", "ROWID", $utenti_rec_new)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore prenotazione codice utente");
            ItaDB::DBUnLock($retLock['lockID']);
            return false;
        }

        $richut_rec = $this->accLib->GetRichut($rowid, 'rowid');
        $richut_rec['RICCOD'] = $new_utecod;
        $richut_rec['RICSTA'] = accLib::RICHIESTA_EVASA;
        $richut_rec['RICTIP'] = "1";
        $richut_rec['RICPWD'] = $utenti_rec_new['UTEPAS'];
        if (!$this->updateRecord($this->ITW_DB, 'RICHUT', $richut_rec, $richut_rec['RICNOM'] . $richut_rec['RICCOG'])) {
            Out::msgStop("Errore", "Impossibile aggiornare il record");
            return false;
        }

        ItaDB::DBUnLock($retLock['lockID']);

        return $utenti_rec_new;
    }

}

?>