<?php

interface itaDocumentMapping {

    public function convert($text, $dictionary);

    public function getMissingVars();
}
