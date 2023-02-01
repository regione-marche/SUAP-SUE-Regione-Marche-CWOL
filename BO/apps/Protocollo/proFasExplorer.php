<?php

/**
 *
 * GESTIONE FASCICOLI
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    28.11.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function proFasExplorer() {
    $proFasExplorer = new proFasExplorer();
    $proFasExplorer->parseEvent();
    return;
}

class proFasExplorer extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proFasExplorer";
    public $gridFasExplorer = "proFasExplorer_gridFasExplorer";
    public $fascicolo = array();

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->fascicolo = App::$utente->getKey($this->nameForm . "_fascicolo");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_fascicolo", $this->fascicolo);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaAlbero($_POST['chiave'], $_POST['tipoChiave']);
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridFasExplorer:
                        
                        break;
                        $rowid=$_POST;
                        $_POST = array();
                        $_POST['chiave'] = $rowid;
                        $_POST['tipoChiave'] = 'rowidAnaorg';
                        $_POST['returnModel'] = 'returnProFasGest';
                        $model = 'proGestIter';
                        itaLib::openForm($model); //, false, true, $this->nameForm . "_divHost", "", "dialog");
                        $formObj = itaModel::getInstance($model);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();

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
        App::$utente->removeKey($this->nameForm . '_fascicolo');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function caricaAlbero($chiave, $tipoChiave) {
        Out::valore($this->nameForm . '_ANAPRO[ROWID]', '');
        Out::show($this->nameForm);
        Out::setFocus('', $this->nameForm . '_Codice');
        switch ($tipoChiave) {
            case 'rowidAnaorg':
                $anaorg_rec = $this->proLib->GetAnaorg($chiave, 'rowid');
                $anapro_f = $this->proLib->getGenericTab("SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='{$anaorg_rec['ORGKEY']}'", false);
                break;
            case 'orgkey':
                $anaorg_rec = $this->proLib->GetAnaorg($chiave, 'orgkey');
                $anapro_f = $this->proLib->getGenericTab("SELECT * FROM ANAPRO WHERE PROPAR='F' AND PROFASKEY='{$anaorg_rec['ORGKEY']}'", false);
                break;
            case 'rowidAnapro':
                $anapro_f = $this->proLib->GetAnapro($chiave, 'rowid');
                $anaorg_rec = $this->proLib->GetAnaorg($anapro_f['PROFASKEY'], 'orgkey');
                break;
            case 'pronum':
                $anapro_f = $this->proLib->GetAnapro($chiave, 'codice', 'F');
                $anaorg_rec = $this->proLib->GetAnaorg($anapro_f['PROFASKEY'], 'orgkey');
                break;
        }

        if (!$anapro_f) {
            Out::msgStop("Attenzione!", "Fascicolo con errori sul record base. Contatta l'assistenza.");
            $this->returnToParent();
//            if ($this->returnModel) {
//                $model = $this->returnModel;
//                itaLib::openForm($model);
//                $formObj = itaModel::getInstance($model);
//                $formObj->setReturnModel($this->nameForm);
//                $formObj->setReturnId('');
//                $_POST = array();
//                $formObj->setEvent('openform');
//                $formObj->parseEvent();
//            }
            return;
        }
        if ($anapro_f['PROFASKEY']) {
            $sql = "SELECT * FROM ANAPRO WHERE (PROPAR='A' OR PROPAR='P' OR PROPAR='C') AND PROFASKEY='{$anapro_f['PROFASKEY']}'";
            $anapro_tab = $this->proLib->getGenericTab($sql);
        }

        $record = array();
        $anapro_f['level'] = 0;
        $anapro_f['parent'] = '';
        if ($anapro_tab) {
            $anapro_f['isLeaf'] = 'false';
        } else {
            $anapro_f['isLeaf'] = 'true';
        }
        $anapro_f['expanded'] = 'true';
        $anapro_f['loaded'] = 'true';

        $anaogg_rec = $this->proLib->GetAnaogg($anapro_f['PRONUM'], $anapro_f['PROPAR']);
        $anapro_f['DESCRIZIONE'] = $anaogg_rec['OGGOGG'];

        $record[] = $anapro_f;

        if ($anapro_f['PROFASKEY']) {
            foreach ($anapro_tab as $anapro_rec) {
                $anapro_rec['level'] = 1;
                $anapro_rec['parent'] = $anapro_f['ROWID'];
                $anapro_rec['isLeaf'] = 'true';
                $anapro_rec['expanded'] = 'true';
                $anapro_rec['loaded'] = 'true';

                $anaogg_rec = $this->proLib->GetAnaogg($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                $anapro_rec['DESCRIZIONE'] = $anaogg_rec['OGGOGG'];

                $record[] = $anapro_rec;
            }
        }

        $this->fascicolo = $record;
        $this->caricaGriglia($this->gridFasExplorer, $this->fascicolo);
    }

    private function caricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = 10000, $caption = '') {
        if ($caption) {
            out::codice("$('#$griglia').setCaption('$caption');");
        }
        if (is_null($appoggio))
            $appoggio = array();
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
