<?php

/**
 * Description of PDOHelper
 *
 * @author l.pergolini
 */
class PDOHelper {

    /**
     * Compone una stringa sql per la lettura del record tramite Pk 
     * @param object $DBTypeReader ($db o driver) 
     * @param object $tableDef tabella
     * @param array $data valori record
     * @param type $paramsFields binding campi valore 
     * @return string sql da eseguire
     */
    public static function getSqlConditionsForUpdateOrDelete($DBTypeReader, $tableDef, $data, &$paramsFields) {
        $pks = $tableDef->getPks();
        $paramField = array();

        $dbParams = $DBTypeReader->getSqlparams();

        $sql = '(';
        $and = '';
        foreach ($pks as $pk) {
            $sql .= " $and( $pk = :$pk)";
            $and = ' AND ';

            $dbmsTypes = $DBTypeReader->getDbmsBaseTypes();
            $tableFields = $tableDef->getFields();
            $paramField = array('name' => $pk,
                'value' => self::checkNull($dbmsTypes[$tableFields[$pk]['type']]['php'], $data[$pk], $dbParams['defaultString']),
                'type' => $dbmsTypes[$tableFields[$pk]['type']]['pdo']);
            $paramsFields[] = $paramField;
        }
        $sql .= ')';

        return $sql;
    }

    /**
     * Normalizza valore in funzione del tipo campo
     * @param string $fieldBaseType Tipo campo
     * @param string $value Valore da normalizzare
     * @param string $defaultString Stringa di default da utilizzare se campo di tipo char non valorizzato
     * @return string valore normalizzato
     */
    public static function checkNull($fieldBaseType, $value, $defaultString = 'empty') {
        if ($value !== '' && $value !== null) {
            return $value;
        }
        switch ($fieldBaseType) {
            case 'char':
                $value = (strtolower($defaultString) === 'blank') ? ' ' : '';
                break;
            case 'date':
            case 'binary':
                $value = null;
                break;
            case 'boolean':
            default:
                $value = 0;
                break;
        }
        return $value;
    }

    /**
     * USATO SOLO PR MSSQL Effettua il caricamento di binari in automatico quando si esegue un findbyPk
     * @param array $result Array dei modelli caricati dal QueryRiga\QueryRigaMultipla
     * @param array $info: 
     * @param string $defaultString Stringa di default da utilizzare se campo di tipo char non valorizzato
     * @return string valore normalizzato
     */
    public function getByPksInfoBinaryCallback($result = array(), $info = array()) {
        if (!$info) {
            return;
        }
        $sqlParams = array();
        $where = PDOHelper::getSqlConditionsForUpdateOrDelete($info['DB'], $info['tableDef'], $info['pkValues'], $sqlParams);

        $sql = 'SELECT ' . $info['DB']->adapterBlob($info['binField']) .
                ' FROM ' . $info['tableName'] .
                ' WHERE ' . $where;

        //$sqlFieldsBinary = $info["tableDef"]->getFields()[$info['binField']];

        $field = array(
            'name' => $info['binField'],
            'position' => 1,
            'type' => PDO::PARAM_LOB,
            'maxLenght' => 0
        );
        if(defined('PDO::SQLSRV_ENCODING_BINARY')){
            $field['driverData'] = PDO::SQLSRV_ENCODING_BINARY;
        }

        $sqlFields["fields"][] = $field;

        $resultBin = ItaDB::DBQuery($info['DB'], $sql, false, $sqlParams, null, $sqlFields);
        $result[$info['binField']] = $resultBin[$info['binField']];
        
        return $result;
    }

}

?>