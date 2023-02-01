<?php

class ITA_Table {

    protected $db;
    protected $name;
    protected $primaryKey;
    protected $fields;

    function __construct($db, $table_name, $table_fields, $table_primaryKey=null) {
        $this->db = $db;
        $this->name = $table_name;
        $this->fields = $table_fields;
        $this->primaryKey = $table_primaryKey;
    }

    // fields getter
    function getFields() {
        return $this->fields;
    }

    //fileds setter
    function setFields($fields) {
        $this->fields = $fields;
    }

    function getName() {
        return $this->name;
    }

    function setName() {
        $this->name = $name;
    }

    function setPrimaryKey($primaryKey) {
        $this->primaryKey = $primaryKey;
    }

    function getPrimaryKey() {
        return $this->primaryKey;
    }

    public function getNormalizedValue($field, $value) {
        return $this->db->getNormalizedValue($this->fields[$field], $value);
    }

}