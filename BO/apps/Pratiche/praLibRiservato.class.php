<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibRiservato {

    const NO_RISERVATO = 0;
    const SI_RISERVATO = 1;
    const CHIEDI_RISERVATO = 2;
    const RISERVATO_DA_ESPRESSIONE = 3;

    private $praLib;
    public static $TIPO_RISERVATEZZA = array(
        self::NO_RISERVATO => 'Non riservato',
        self::SI_RISERVATO => 'Riservato',
        self::CHIEDI_RISERVATO => 'Riservato con scelta',
        self::RISERVATO_DA_ESPRESSIONE => 'Riservato da espressione'
    );

    public function __construct() {
        $this->praLib = new praLib;
    }

    public function creaComboRiservato($nomeCampo, $valoreSelezionato = '') {
        foreach (self::$TIPO_RISERVATEZZA as $key => $value) {
            Out::select($nomeCampo, 1, $key, ($key === $valoreSelezionato ? '1' : '0'), $value);
        }
    }

    public function getIconRiservato($value) {
        if ($value == 1) {
            return '<div class="ita-html"><span class="ita-tooltip" title="Allegato riservato"><i class="ita-icon ita-icon-document-ris-16x16" style="margin: 0 auto;"></i></span></div>';
        }

        return '';
    }

}
