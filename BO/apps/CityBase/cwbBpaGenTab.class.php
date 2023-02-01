<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';

/**
 *
 * Superclasse gestione form con jqGrid Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbBpaGenTab extends cwbBpaGenModel {

    const SUFFISSO_CAMPI_FORMATTATI = '_formatted'; // suffisso usato per convenzione sui campi formattati in grid

    protected $errorOnEmpty;    //Se impostato a false non da errore in caso di tabella vuota
    protected $nameOrderField; // Serve per impostare l'ordinamento (esempio: cwbLibDB_BTA, getSqlLeggiBtaLocal)
    protected $typeOrderField; // Serve per impostare il tipo di ordinamento (esempio: cwbLibDB_BTA, getSqlLeggiBtaLocal)


    /**
     * Effettua il caricamento dei dati dal database
     * Presentazione dei risultati nella jqGrid
     */
    protected function elenca($reload) {
        try {
            if ($reload) {
                $this->resetSelected();
                TableView::clearGrid($this->nameForm . '_' . $this->helper->getGridName());
                TableView::enableEvents($this->nameForm . '_' . $this->helper->getGridName());
                TableView::reload($this->nameForm . '_' . $this->helper->getGridName());
                return;
            }

            $this->setGridFilters();
            $this->preElenca();
            $sqlParams = array();
            $this->sqlElenca($sqlParams);
            //SISTEMATO ORDINAMENTO: l'ordinamento deve essere settato solo quando scateno l'evento sulla 'onClickTablePager'
            //ho aggiunto il controllo per ordinare solo nel caso del tablePager
            $sortIndex = $sortOrder = null;
            if ($_POST["sidx"] && $_POST["sidx"] !== "ROW_ID") {
                $sortIndex = $_POST["sidx"];
                if (preg_match('/' . self::SUFFISSO_CAMPI_FORMATTATI . '/', $sortIndex)) {
                    // se il nome del campo finisce per _formatted lo rimuovo per prendere il nome vero
                    $sortIndex = strtok($sortIndex, self::SUFFISSO_CAMPI_FORMATTATI);
                }
                if ($sortIndex === false) {
                    throw new Exception("Errore Ordinamento");
                }
                $sortOrder = $_POST["sord"];
            }
            $ita_grid01 = $this->initializeTable($sqlParams, $sortIndex, $sortOrder);
            if (!$_POST["sidx"] && empty($sortIndex)) {
                $this->setSortParameter($ita_grid01);
            }
            $dataPage = $this->elaboraGrid($ita_grid01);

            if($dataPage === false || !$this->getDataPage($ita_grid01, $dataPage)){
                TableView::clearGrid($this->nameForm . '_' . $this->helper->getGridName());
                
                if($this->errorOnEmpty !== false){
                    $this->nessunRecordMessage();
                }
                else{
                    $this->setVisRisultato();
                    TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
                }
            }
            else{
//                TableView::clearGrid($this->nameForm . '_' . $this->helper->getGridName());
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);
            }

            $this->renderSelect();

            $this->postElenca();
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    protected function initializeTable($sqlParams, $sortIndex = '', $sortOrder = '') {
        if ($this->nameOrderField != '') { // aggiunto per poter gestire un ordinamento parametrizzato (es. cwbLibDB_BTA, getSqlLeggiBtaLocal)
            $sortIndex = $this->nameOrderField;
            if ($this->typeOrderField != '') {
            $sortOrder = $this->typeOrderField;
            }
        } else {
            if ($this->elencaAutoAudit) {
                $this->orderElencaAudit($sortIndex);
            }
            if ($this->elencaAutoFlagDis) {
                $this->orderElencaFlagDis($sortIndex);
            }
        }
        return $this->helper->initializeTableSql($this->SQL, $this->MAIN_DB, $sqlParams, $sortIndex, $sortOrder, false);
    }

    protected function nessunRecordMessage() {
        Out::msgStop("Selezione", "Nessun record trovato.");
    }

    /**
     * Legge dati
     * @param object $ita_grid jqGrid
     * @param array $Result_tab Risultati dopo un'elaborazione della griglia
     * @return Dati
     */
    protected function getDataPage($ita_grid, $Result_tab) {
        return $this->helper->getDataPage($ita_grid, $Result_tab);
    }

    /**
     * Formatta DataGrid
     * (N.B.: Per evitare tale funzionalità , fare override del metodo e restituire null)
     * @param object $ita_grid DataGrid
     * @return resultset Resultset Datagrid con formattazione
     */
    protected function elaboraGrid($ita_grid) {
        $Result_tab_tmp = $ita_grid->getDataArray();
        $Result_tab = $this->elaboraRecords($Result_tab_tmp);
        if ($this->elencaAutoAudit === true) {
            $this->renderElencaAudit($Result_tab);
        }
        if ($this->elencaAutoFlagDis === true) {
            $this->renderElencaFlagDis($Result_tab);
        }
        return $Result_tab;
    }

    /**
     * Imposta parametri per ordinamento
     * @param object $ita_grid01 jqGrid
     * @param string $order Tipo ordinamento (ascendente/discendente)
     */
    protected function setSortParameter($ita_grid01, $order = cwbLib::ORDER_ASC) {

        //aggiunto controllo se in stringa è presente "order by"
        if (strpos(strtoupper($this->SQL), 'ORDER') != false) {
            $sortField = substr($this->SQL, strpos(strtoupper($this->SQL), 'ORDER') + 9, strlen($this->SQL));
            $ita_grid01->setSortIndex($sortField);
            $ita_grid01->setSortOrder("none");
        } else {
            // se non è stato impostato il modello non riesco a fare l'ordinamneto di defalt 
            if ($this->PK !== null) {
                $pksOrder = array();
                $alias = $this->MAIN_DB->getSqlAlias($this->SQL, $this->TABLE_VIEW);
                if (is_array($this->PK)) {
                    foreach ($this->PK as $pk) {
                        $pksOrder[] = $alias . "." . $pk;
                    }
                } else {
                    $pksOrder[] = $alias . "." . $this->PK;
                }
                $ita_grid01->setSortIndex($pksOrder);
                $ita_grid01->setSortOrder($order); // 
            }
        }
    }

}

