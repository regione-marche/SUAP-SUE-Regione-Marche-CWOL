<?php

function envMigrationDetail() {
    $envUpdater = new envMigrationDetail();
    $envUpdater->parseEvent();
    return;
}

class envMigrationDetail extends itaModel {
    
    const INNER_FORM = 'envMigrationDetailRow';
    const TYPE_METHOD = 'metodo';
    const TYPE_SCRIPT = 'script';
    
    public $nameForm = "envMigrationDetail";
    private $migrationData;
    
    function __construct() {
        parent::__construct();        
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
        }
    }

    private function init() {
        // Definizione
        Out::valore($this->nameForm . '_ID', $this->migrationData['MIGRATION_DEFINITION']['ID']);
        Out::valore($this->nameForm . '_BUILD_TAG', $this->migrationData['MIGRATION_DEFINITION']['BUILD_TAG']);
        Out::valore($this->nameForm . '_AUTORE', $this->migrationData['MIGRATION_DEFINITION']['AUTHOR']);
        Out::valore($this->nameForm . '_CONTEXT', $this->migrationData['MIGRATION_DEFINITION']['CONTEXT']);
        Out::valore($this->nameForm . '_DESCRIZIONE', $this->migrationData['MIGRATION_DEFINITION']['DESCRIPTION']);
        Out::valore($this->nameForm . '_NOTE', $this->migrationData['MIGRATION_DEFINITION']['NOTES']);
        Out::valore($this->nameForm . '_SEQUENZA', $this->migrationData['MIGRATION_DEFINITION']['SEQUENCE']);

        // Composizione
        usort($this->migrationData['MIGRATION_COMPOSITION'], array($this, "sortMigrationComposition"));
        foreach ($this->migrationData['MIGRATION_COMPOSITION'] as $migrationComposition) {
            $this->buildCompositionRow($migrationComposition);
        }
    }
    
    private function sortMigrationComposition($a, $b) {
        return $a['SEQUENCE'] > $b['SEQUENCE'];
    }
    
    private function buildCompositionRow($migrationComposition) {
        // Innesta subform
        $rowId = self::INNER_FORM . '_' . $migrationComposition['SEQUENCE'];
        itaLib::openInner(self::INNER_FORM, '', true, $this->nameForm . '_migration_composition_details', '', '', $rowId);
        
        // Valorizza campi
        Out::valore($rowId . '_SEQUENZA', $migrationComposition['SEQUENCE']);
        Out::valore($rowId . '_TIPO', $migrationComposition['TYPE']);
        Out::valore($rowId . '_DESCRIZIONE', $migrationComposition['DESCRIPTION']);
        Out::valore($rowId . '_METODO_EXECUTOR_UP', $migrationComposition['METHOD']['EXECUTOR_UP']);
        Out::valore($rowId . '_METODO_EXECUTOR_DOWN', $migrationComposition['METHOD']['EXECUTOR_DOWN']);
        Out::valore($rowId . '_SCRIPT_ALIAS_DB', $migrationComposition['SCRIPT']['ALIAS_DB']);
        Out::valore($rowId . '_SCRIPT_SQL', $migrationComposition['SCRIPT']['SQL']);     
        
        // Imposta pannelli in funzione del tipo
        switch ($migrationComposition['TYPE']) {
            case self::TYPE_METHOD:
                Out::show($rowId . '_divMethod');
                Out::hide($rowId . '_divScript');
                break;
            case self::TYPE_SCRIPT:
                Out::show($rowId . '_divScript');
                Out::hide($rowId . '_divMethod');
                break;
        }
    }    
    
    function getMigrationData() {
        return $this->migrationData;
    }

    function setMigrationData($migrationData) {
        $this->migrationData = $migrationData;
    }

}
