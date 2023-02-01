<?php

class itaDocumentMappingDummy implements itaDocumentMapping {

    public function convert($text, $dictionary) {
        return $text;
    }

    public function getMissingVars() {
        return array();
    }

}
