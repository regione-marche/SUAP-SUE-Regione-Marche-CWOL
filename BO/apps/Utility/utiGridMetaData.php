<?php

/**
 *
 * DIALOG DI RICERCA
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    20.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_LIB_PATH . '/itaPHPCore/itaComponents.class.php';

function utiGridMetaData() {
    $utiGridMetaData = new utiGridMetaData();
    $utiGridMetaData->parseEvent();
    return;
}

class utiGridMetaData extends itaModel {

    public $nameForm = 'utiGridMetaData';
    private $gridComponent = 'gridMetadata';
    private $nameGrid;
    private $metaData;
    private $footerBar = true;
    private $addButton = true;
    private $editButton = true;
    private $deleteButton = true;

    function postInstance() {
        parent::postInstance();

        $this->nameGrid = $this->nameForm . '_' . $this->gridComponent;
        $this->metaData = App::$utente->getKey('utiGridMetaData_metaData');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey('utiGridMetaData_metaData', $this->metaData);
        }
    }

    public function getMetaData() {
        return $this->metaData;
    }

    /**
     * Imposta i metadati da visualizzare.
     * @param array $metaData
     */
    public function setMetaData($metaData) {
        $this->metaData = $metaData;
    }

    /**
     * Imposta la visualizzazione della barra di gestione in fondo la griglia.
     * @param boolean $footerBar
     */
    public function setFooterBar($footerBar) {
        $this->footerBar = $footerBar;
    }

    /**
     * Imposta la visualizzazione del bottone di aggiunta.
     * @param boolean $addButton
     */
    public function setAddButton($addButton) {
        $this->addButton = $addButton;
    }

    /**
     * Imposta la visualizzazione del bottone di modifica.
     * @param boolean $editButton
     */
    public function setEditButton($editButton) {
        $this->editButton = $editButton;
    }

    /**
     * Imposta la visualizzazione del bottone di cancellazione.
     * @param boolean $deleteButton
     */
    public function setDeleteButton($deleteButton) {
        $this->deleteButton = $deleteButton;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->InizializzaGriglia();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ConfermaDatiGrid':
                        $newData = $_POST[$this->nameForm . '_META'];

                        if (isset($this->metaData[$newData['CHIAVE']])) {
                            Out::msgInfo('Attenzione', 'La chiave inserita è già presente.');
                            break;
                        }

                        $this->metaData[$newData['CHIAVE']] = $newData;
                        $this->CaricaGriglia();
                        break;

                    case $this->nameForm . '_ConfermaUpdateGrid':
                        $updData = $_POST[$this->nameForm . '_META'];
                        $this->metaData[$updData['CHIAVE']] = $updData;
                        $this->CaricaGriglia();
                        break;
                }

                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        $this->AggiungiDato();
                        break;
                }
                break;

            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        $this->ModificaDato($this->metaData[$_POST['rowid']]);
                        break;
                }
                break;

            case 'delGridRow':
                unset($this->metaData[$_POST['rowid']]);
                $this->CaricaGriglia();
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameGrid:
                        $this->CaricaGriglia();
                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey('utiGridMetaData_metaData');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    function InizializzaGriglia() {
        $colModel = array(
            array('name' => 'CHIAVE', 'width' => 80, 'title' => 'Chiave'),
            array('name' => 'VALORE', 'width' => 350, 'title' => 'Valore')
        );

        $gridOptions = array(
            'readerId' => 'CHIAVE',
            'caption' => '',
            'height' => '200',
            'shrinkToFit' => true,
            'rowNum' => 9999,
            'rowList' => '[]',
            'filterToolbar' => false,
            'navButtonEdit' => false,
            'pginput' => false,
            'pgbuttons' => false,
            'navGrid' => true,
            'navButtonDel' => $this->deleteButton,
            'navButtonAdd' => $this->addButton,
            'navButtonEdit' => $this->editButton,
            'columnChooser' => false,
            'resizeToParent' => true
        );

        if ($this->footerBar === false) {
            $gridOptions['pager'] = false;
        }

        $htmlGrid = itaComponents::getHtmlJqGridComponent($this->nameForm, $this->gridComponent, $colModel, $gridOptions);

        Out::html($this->nameForm . '_divRisultato', $htmlGrid);

        $this->CaricaGriglia();

        TableView::enableEvents($this->nameGrid);
        Out::setFocus('', $this->nameGrid);
    }

    public function CaricaGriglia() {
        $tempData = array();
        if ($this->metaData) {
            foreach ($this->metaData as $data) {
                $tempData[$data['CHIAVE']] = $data;
            }
        }

        $this->metaData = $tempData;

        TableView::clearGrid($this->nameGrid);
        $gridRic = new TableView($this->nameGrid, array(
            'arrayTable' => $this->metaData,
            'rowIndex' => 'idx'
        ));

        $gridRic->setPageNum(1);
        $gridRic->setPageRows(9999);
        $gridRic->setSortIndex($_POST['sidx'] ?: 'CHIAVE');
        $gridRic->setSortOrder($_POST['sord'] ?: 'asc');
        $gridRic->getDataPage('json');
    }

    public function AggiungiDato() {
        $this->visualizzaFormMetadato('Nuovo dato', array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaDatiGrid', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConferma', 'model' => $this->nameForm)
        ));
    }

    public function ModificaDato($record) {
        $this->visualizzaFormMetadato('Modifica dato', array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaUpdateGrid', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_AnnullaConfermaUpdate', 'model' => $this->nameForm)
                ), $record);
    }

    private function visualizzaFormMetadato($title, $buttons, $record = array()) {
        Out::msgInput($title, array(
            array(
                'label' => array('style' => 'width: 60px;', 'value' => 'Chiave'),
                'id' => $this->nameForm . '_META[CHIAVE]',
                'name' => $this->nameForm . '_META[CHIAVE]',
                'type' => 'text',
                'size' => '30',
                'maxlength' => '200',
                'value' => $record['CHIAVE'],
                'class' => (isset($record['CHIAVE']) ? 'ita-readonly' : '')
            ),
            array(
                'label' => array('style' => 'width: 60px;', 'value' => 'Valore'),
                'id' => $this->nameForm . '_META[VALORE]',
                'name' => $this->nameForm . '_META[VALORE]',
                'type' => 'text',
                'size' => '30',
                'value' => $record['VALORE'],
                'maxlength' => '200'
            )
                ), $buttons, $this->nameForm
        );
    }

}
