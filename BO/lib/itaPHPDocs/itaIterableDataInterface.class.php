<?php

interface itaIterableDataInterface {

    public function getIndex();

    public function breakAfter($n);

    public function readRecord();

    public function getRecord();

    public function getRecordKey($key);
}
