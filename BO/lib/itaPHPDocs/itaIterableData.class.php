<?php

require_once ITA_LIB_PATH . '/itaPHPDocs/itaIterableDataInterface.class.php';
require_once ITA_LIB_PATH . '/itaPHPDocs/itaIterableDataAbstract.class.php';

class itaIterableData extends itaIterableDataAbstract implements itaIterableDataInterface {

    private $db;
    private $statement;
    private $nextRecord = null;

    public function __construct($db, $sql) {
        $this->db = $db;
        ItaDB::DBSetUseBufferedQuery($this->db, false);
        $this->statement = ItaDB::DBQueryPrepare($this->db, $sql);
    }

    public function getRecord() {
        return $this->nextRecord;
    }

    public function readRecord() {
        $this->nextRecord = ItaDB::DBQueryFetch($this->db, $this->statement, false);

        if ($this->nextRecord) {
            foreach ($this->nextRecord as $k => $v) {
                if (mb_detect_encoding($v, 'UTF-8', true) !== 'UTF-8') {
                    $this->nextRecord[$k] = utf8_encode($v);
                }

                $this->nextRecord[$k] = htmlspecialchars($this->nextRecord[$k], ENT_NOQUOTES, 'UTF-8');
            }

            $this->index++;
        }

        return (boolean) $this->nextRecord;
    }

    public function getRecordKey($key) {
        return isset($this->nextRecord[$key]) ? $this->nextRecord[$key] : null;
    }

}
