<?php

abstract class itaIterableDataAbstract {

    protected $index = 0;

    public function getIndex() {
        return $this->index;
    }

    public function breakAfter($n) {
        if ($this->index % $n === 0) {
            return '@{$PAGE_BREAK}@';
        }

        return '';
    }

}
