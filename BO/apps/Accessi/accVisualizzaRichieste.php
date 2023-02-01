<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    06.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

function accVisualizzaRichieste() {
    $accVisualizzaRichieste = new accVisualizzaRichieste();
    $accVisualizzaRichieste->parseEvent();
    return;
}

class accVisualizzaRichieste extends itaModel {

    public $accLib;
    public $nameForm = "accVisualizzaRichieste";
    public $gridGestioneRichieste = "accVisualizzaRichieste_gridGestioneRichieste";

    function __construct() {
        parent::__construct();
        try {
            $this->accLib = new accLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openportlet':
                itaLib::openForm($this->nameForm, '', true, $_POST['context'] . "-content");
                Out::delContainer($_POST['context'] . "-wait");
                TableView::enableEvents($this->gridGestioneRichieste);
                TableView::reload($this->gridGestioneRichieste);
                break;

            case 'dbClickRow':
            case 'editGridRow':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->gridGestioneRichieste:
                        $model = "accGestioneRichieste";
                        itaLib::openForm($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura dettaglio fallita");
                            break;
                        }
                        $formObj->setReturnId($_POST['rowid']);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->gridGestioneRichieste:
                        TableView::clearGrid($this->gridGestioneRichieste);
                        $gridScheda = new TableView($this->gridGestioneRichieste, array(
                            'sqlDB' => $this->accLib->getITW(),
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
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
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

}

?>