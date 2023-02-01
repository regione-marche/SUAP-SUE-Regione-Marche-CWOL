<?php

class praLibRiservato {

    const NO_RISERVATO = 0;
    const SI_RISERVATO = 1;
    const CHIEDI_RISERVATO = 2;
    const RISERVATO_DA_ESPRESSIONE = 3;

    public static $TIPO_RISERVATEZZA = array(
        self::NO_RISERVATO => 'Non riservato',
        self::SI_RISERVATO => 'Riservato',
        self::CHIEDI_RISERVATO => 'Riservato con scelta',
        self::RISERVATO_DA_ESPRESSIONE => 'Riservato da espressione'
    );

}
