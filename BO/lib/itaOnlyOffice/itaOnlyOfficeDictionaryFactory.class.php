<?php

/**
 * Factory per la gestione del dizionario delle variabili
 *
 * @author m.biagioli
 */
class itaOnlyOfficeDictionaryFactory {

    // Costanti che identificano i dizionari specifici
    const DICTIONARY_TYPE_SEGRETERIA = 'SEGRETERIA';
    const DICTIONARY_TYPE_PRATICHE = 'PRATICHE';
    const DICTIONARY_TYPE_SERV_ECON = 'SERV_ECON';
    const DICTIONARY_TYPE_ANAGRAFE = 'ANAGRAFE';
    const DICTIONARY_TYPE_ELETTORALE = 'ELETTORALE';

    /**
     * Mappa dizionario variabili
     * @var array
     */
    private static $dictionaryMap = array(
        self::DICTIONARY_TYPE_SEGRETERIA => 'segOnlyOfficeDictionary',
        self::DICTIONARY_TYPE_PRATICHE => 'praOnlyOfficeDictionary',
        self::DICTIONARY_TYPE_SERV_ECON => 'cwfOnlyOfficeDictionary',
        self::DICTIONARY_TYPE_ANAGRAFE => 'cwdOnlyOfficeDictionary',
        self::DICTIONARY_TYPE_ELETTORALE => 'cwdOnlyOfficeDictionaryEle'
    );

    /**
     * Restituisce plugin specifico
     * @param string $type Tipo di dizionario
     * @return boolean|\className Dictionary specifico, o false in caso di errore
     */
    public static function getDictionary($type) {
        if (!isset(self::$dictionaryMap[$type])) {
            return false;
        }
        $className = self::$dictionaryMap[$type];

        $retRequire = itaModelHelper::requireAppFileByName($className . '.class');
        if (!$retRequire) {
            return false;
        }
        return new $className;
    }

}
