<?php

/**
 * Definizione tabella
 *
 * @author Massimo Biagioli - Lorenzo Pergolini
 */
class PDOTableDef {

    private $name;
    private $fields;
    private $pks;
    private $tableInfo;
    private $colDescriptions;
    private $relations;

    public function __construct($name, $fields, $pks, $tableInfo) {
        $this->name = $name;
        $this->fields = $fields;
        $this->pks = $pks;
        $this->tableInfo = $tableInfo;
    }

    public function isAutoKey($field) {
        if (in_array($field, $this->getPks())) {
            return ($this->hasAutoKey($field));
        } else {
            return false;
        }
    }

    //determinare se dobbiamo inserire il log solo di alcune tabelle ?
    public function hasLogEvent() {
        return true;
    }

    public function hasAutoKey($field=null) {
        $tableInfo = $this->getTableInfo();
        return $tableInfo['auto'] && (!empty($field) ? $tableInfo['columnName'] == $field : true) && !$tableInfo['sequenceName'];
    }

    public function getSequenceName() {
        $tableInfo = $this->getTableInfo();
        return $tableInfo['sequenceName'];
    }

    public function getName() {
        return $this->name;
    }

    public function getFields() {
        return $this->fields;
    }

    public function getPks($returnStringIfSingle = false) {
        $pk = $this->pks;
        if (($returnStringIfSingle) && (count($pk) === 1)) {
            $pk = $pk[0];
        }
        return $pk;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setFields($fields) {
        $this->fields = $fields;
    }

    public function setPks($pks) {
        $this->pks = $pks;
    }

    public function getTableInfo() {
        return $this->tableInfo;
    }

    public function setTableInfo($tableInfo) {
        $this->tableInfo = $tableInfo;
    }

    public function getColDescriptions() {
        return $this->colDescriptions;
    }

    public function setColDescriptions($colDescriptions) {
        $this->colDescriptions = $colDescriptions;
    }

    public function getRelations() {
        return $this->relations;
    }

    public function setRelations($relations) {
        $this->relations = $relations;
    }

}

?>