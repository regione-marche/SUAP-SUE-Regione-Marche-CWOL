<?php

require_once(ITA_SUAP_PATH . '/praSuap.class.php');

class praExport {

    public $praLib;

    function __construct($cms = 'si') {
        praSuap::load($cms);
        $this->praLib = new praLib();
    }

    function __destruct() {
        
    }
    function createZip($pratica, $output) {
        $input = $this->praLib->getCartellaAttachmentPratiche($pratica);
        $arcpf = $output . "/" . ITA_DB_SUFFIX . "-" . $pratica . ".zip";
        itaZip::zipRecursive($output, $input, $arcpf, 'zip', false, false);
        if (!file_exists($arcpf)) {
            return false;
        }
        return $arcpf;
    }
}

?>
