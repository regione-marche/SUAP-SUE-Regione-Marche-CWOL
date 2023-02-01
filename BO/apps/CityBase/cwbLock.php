<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';

function cwbLock() {
    $cwbLock = new cwbLock();
    $cwbLock->parseEvent();
    return;
}

class cwbLock extends cwbBpaGenTab {

    function initVars() {
        $this->nameForm = 'cwbLock';
        $this->GRID_NAME = 'gridLock';
        $this->skipAuth = true;
        $this->noCrud = true;
    }

    protected function elenca() {
        try {
            $sqlParams = array();
            $sortIndex = 'ROWID';
            $sortOrder = 'desc';

            if ($_POST["sidx"]) {
                $sortIndex = $_POST["sidx"];

                if ($sortIndex === false) {
                    throw new Exception("Errore Ordinamento");
                }
                $sortOrder = $_POST["sord"];
            }

            $sql = 'SELECT * FROM LOCKTAB WHERE LOCKRECID LIKE "CITYWARE.%" ';
            if ($_POST[$this->nameForm . '_LOCKRECID']) {
                $sql.= ' AND  LOCKRECID LIKE "%' . strtoupper($_POST[$this->nameForm . '_LOCKRECID']) . '%" ';
            }

            $ita_grid01 = $this->helper->initializeTableSql($sql, App::$itaEngineDB, $sqlParams, $sortIndex, $sortOrder);
            if (!$_POST["sidx"]) {
                $this->setSortParameter($ita_grid01);
            }


            if (!$this->getDataPage($ita_grid01, $this->elaboraGrid($ita_grid01))) {
                Out::msgStop("Selezione", "Nessun record trovato.");
            } else {
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    protected function confermaCancella($validate = true) {
        $rowId = $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'];

        ItaDB::DBDelete(App::$itaEngineDB, 'LOCKTAB', 'ROWID', $rowId);

        $this->elenca();
    }

}

