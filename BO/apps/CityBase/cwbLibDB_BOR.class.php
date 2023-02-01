<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';

/**
 *
 * Utility DB Cityware (Modulo BOR)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibDB_BOR extends cwbLibDB_CITYWARE {

    // BOR_AUTUTE

    private function leggiAutute($codute, $codmodulo) {
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODMODULO", $codmodulo, PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "CODUTE", strtoupper($codute), PDO::PARAM_STR);
        $sql = "SELECT BOR_AUTUTE.* FROM BOR_AUTUTE WHERE CODMODULO=:CODMODULO AND " . $this->getCitywareDB()->strUpper("CODUTE") . "=:CODUTE";
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    /**
     * Controlla livello autorizzazione
     * @param string $codute Codice utente
     * @param string $codmodulo Codice modulo
     * @param int $numautor Numero autorizzazione
     * @param array $row Row autenticazione (letta in precedenza)
     * @return string Livello autorizzazione
     */
    public function checkAutorLevel($codute, $codmodulo, $numautor, $row = null) {
        if ($row != null) {
            $result = $row;
        } else {
            include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
            $authHelper = new cwbAuthHelper();
            $authHelper->checkAutorLevelModulo($codute, $codmodulo, $numautor, null, null, $null, $result);
//            $result = $this->leggiAutute($codute, $codmodulo);
        }

        if ($result && strlen($result['CODUTE']) > 0) {
            $level = trim($result['AUTUTE_' . str_pad($numautor, 3, '0', STR_PAD_LEFT)]);
        } else {
            $level = '0';
        }

        return $level;
    }

    public function checkAutorLevelModulo($codute = null, $codmodulo, $numautor, $numautor2 = null, $numautor3 = null, &$autor = null, &$rowAutor = null) {
        include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
        $authHelper = new cwbAuthHelper();
        return $authHelper->checkAutorLevelModulo($codute, $codmodulo, $numautor, $numautor2, $numautor3, $autor, $rowAutor);

//        $result = $this->leggiAutute($codute, $codmodulo);
////        if ($result && strlen($result['CODUTE']) > 0) {
//        $level = trim($result['AUTUTE_' . str_pad($numautor, 3, '0', STR_PAD_LEFT)]);
//        $level2 = trim($result['AUTUTE_' . str_pad($numautor2, 3, '0', STR_PAD_LEFT)]);
//        $level3 = trim($result['AUTUTE_' . str_pad($numautor3, 3, '0', STR_PAD_LEFT)]);
//
//        $this->checkMdorgc($codmodulo);     //; Verifica ed imposta il modello organizzativo in base a Area/modulo
//
//        if (!empty($result['CODUTE'])) {
//            $rowAutor = $result;
//        }
//
//        // TODO: Implementare level3
//
//        if (!empty($level2)) {
//            $autor = 2;
//            return $level2;
//        } Else {
//            $autor = 1;
//            return $level;
//        }
//        return $result;  //<- ritorna prima ciccia e poi frutta?!?
////        } else {
////            $level = '0';
////        }
////        return $result;
//        //return $level;
    }

    private function checkMdorgc($codmodulo) {
        if (empty($codmodulo)) {
            return false;
        }
        $sql = " SELECT BOR_MDORGC.* FROM BOR_MDORGC WHERE FLAG_DIS<>1 ";
        $sql .= " ORDER BY CODAREAMA,CODMODULO ";
        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql);
        if ($result == NULL) {
            return false;
        }
        //$key = array_search($codmodulo,$result);
        //sendall per avere la prima riga
        $key = array_search($codmodulo, array_column($result, 'CODMODULO'));
        if ($key > 0) {
            //  Ho trovato la riga corrispondente al modello indicato
            //  quindi posso impostare il modello organizzativo
            $IdModOrg = $result[$key]['IDMODORG'];
        } else {
            //  Ora verifico se � presente una riga con l'Area relativa al modulo passato in  ingresso
            //Do lv_list.$search(lv_list.CODAREAMA=mid(par_modulo;1;1)&lv_list.CODMODULO='';kTrue;kFalse;kFalse;kFalse) Returns lv_pos
            $key = array_search(substr($codmodulo, 1), array_column($result, 'CODAREAMA'));
            if ($key > 0) {
                $IdModOrg = $result[$key]['IDMODORG'];
            } else {
                return false;
            }
        }

        $sql = "  SELECT BOR_MODORG.* FROM BOR_MODORG ";
        $sql .= "  WHERE PROGENTE=:PROGENTE AND FLAG_DIS<>1  ORDER BY DATAINIZ DESC  ";
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGENTE", cwbParGen::getProgente(), PDO::PARAM_STR);
        $listModorg = ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
        if ($listModorg == NULL) {
            return false;
        }

        $key = array_search(substr($IdModOrg, 1), array_column($result, 'IDMODORG'));
        if ($key > 0) {
            $IdModOrg = $result[$key];
            //  Imposto il Modello Organizzattivo nelle variabili di Ambiente di Cityware
            //  La Lista deve essere ordinata con la riga corrente quella
            //  utilizzata dal pgm che si andr� ad aprire
            //tv_Obj_Par_Gen.$set_lista_modorg($result[$key]);
        } else {
            return false;
        }

        return true;
    }

    // BOR_DESAUT

    /**
     * Restituisce comando sql per lettura tabella BOR_DESAUT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBorDesaut($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_DESAUT.* FROM BOR_DESAUT";
        $and = 'WHERE';
        if (!empty($filtri['CODMODULO'])) {
            $this->addSqlParam($sqlParams, 'CODMODULO', $filtri['CODMODULO'], PDO::PARAM_INT);
            $sql .= " $and CODMODULO=:CODMODULO";
            $and = 'AND';
        }
        if (!empty($filtri['PROGAUT'])) {
            $this->addSqlParam($sqlParams, 'PROGAUT', $filtri['PROGAUT'], PDO::PARAM_INT);
            $sql .= " $and PROGAUT=:PROGAUT";
            $and = 'AND';
        }

        $sql .= ' ORDER BY CODMODULO, PROGAUT';

        return $sql;
    }

    /**
     * Restituisce comando sql per lettura tabella BOR_DESAUT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @return string Comando sql
     */
    public function getSqlLeggiBorDesautNaturaNote() {
        $sql = "SELECT BOR_DESAUT.* FROM BOR_DESAUT WHERE AUTUTE1='L' AND AUTUTE2='G' AND AUTUTE3='C'";
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_DESAUT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorDesaut($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorDesaut($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorDesautChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['CODMODULO'] = $cod;
        return self::getSqlLeggiBorDesaut($filtri, true, $sqlParams);
    }

    public function leggiBorDesautChiave($cod, $multipla = false) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorDesautChiave($cod, $sqlParams), $multipla, $sqlParams);
    }

    public function countBorDesautJoinModuliPerArea($area) {
        $sql = "SELECT COUNT(*) FROM BOR_DESAUT "
                . "LEFT JOIN BOR_MODULI ON BOR_MODULI.CODMODULO = BOR_DESAUT.CODMODULO "
                . "WHERE BOR_MODULI.CODAREAMA = '" . $area . "'";

        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);

        return reset($result);
    }

    /**
     * Restituisce dati tabella BOR_DESAUT
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorDesautNaturaNote() {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorDesautNaturaNote());
    }

    // BOR_CLIENT

    /**
     * Restituisce comando sql per lettura tabella BOR_CLIENT
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return string Comando sql
     */
    public function getSqlLeggiBorClient($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_CLIENT.* FROM BOR_CLIENT";
        $where = 'WHERE';

        if (array_key_exists('PROGCLIENT', $filtri) && $filtri['PROGCLIENT'] != null) {
            $this->addSqlParam($sqlParams, "PROGCLIENT", $filtri['PROGCLIENT'], PDO::PARAM_INT);
            $sql .= " $where PROGCLIENT=:PROGCLIENT";
            $where = 'AND';
        }
        if (array_key_exists('DESENTE', $filtri) && $filtri['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filtri['DESENTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = 'AND';
        }
        if (array_key_exists('INDIRENTE', $filtri) && $filtri['INDIRENTE'] != null) {
            $this->addSqlParam($sqlParams, "INDIRENTE", "%" . strtoupper(trim($filtri['INDIRENTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("INDIRENTE") . " LIKE :INDIRENTE";
            $where = 'AND';
        }
        if (array_key_exists('CAP', $filtri) && $filtri['CAP'] != null) {
            $this->addSqlParam($sqlParams, "CAP", $filtri['CAP'], PDO::PARAM_STR);
            $sql .= " $where CAP=:CAP";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", "%" . strtoupper(trim($filtri['PROVINCIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PROVINCIA") . " LIKE :PROVINCIA";
            $where = 'AND';
        }
        if (array_key_exists('CODATTIVE', $filtri) && $filtri['CODATTIVE'] != null) {
            $this->addSqlParam($sqlParams, "CODATTIVE", $filtri['CODATTIVE'], PDO::PARAM_STR);
            $sql .= " $where CODATTIVE=:CODATTIVE";
            $where = 'AND';
        }
        if (array_key_exists('NATGIURID', $filtri) && $filtri['NATGIURID'] != null) {
            $this->addSqlParam($sqlParams, "NATGIURID", $filtri['NATGIURID'], PDO::PARAM_STR);
            $sql .= " $where NATGIURID=:NATGIURID";
            $where = 'AND';
        }
        if (array_key_exists('PROGDITTAD', $filtri) && $filtri['PROGDITTAD'] != null) {
            $this->addSqlParam($sqlParams, "PROGDITTAD", $filtri['PROGDITTAD'], PDO::PARAM_INT);
            $sql .= " $where PROGDITTAD=:PROGDITTAD";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", "%" . strtoupper(trim($filtri['CODFISCALE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " LIKE :CODFISCALE";
            $where = 'AND';
        }
        if (array_key_exists('PARTIVA', $filtri) && $filtri['PARTIVA'] != null) {
            $this->addSqlParam($sqlParams, "PARTIVA", "%" . strtoupper(trim($filtri['PARTIVA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PARTIVA") . " LIKE :PARTIVA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGCLIENT';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_CLIENT
     * @param array $filtri Filtri di ricerca     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return object Resultset
     */
    public function leggiBorClient($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorClient($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_CLIENT
     * @param string $cod Chiave     
     * @param array $sqlParams Parametri con cui sostituire i placeholder della query
     * @return string Comando sql
     */
    public function getSqlLeggiBorClientChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGCLIENT'] = $cod;
        return self::getSqlLeggiBorClient($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_CLIENT per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorClientChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorClientChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_ENTI

    /**
     * Restituisce comando sql per lettura tabella BOR_ENTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return string Comando sql
     */
    public function getSqlLeggiBorEnti($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT e.*, EDC.ENTE_DOCER, edc.UTENTE_DOCER, EDC.PWDUTE_DOCER FROM BOR_ENTI e "
                . "LEFT JOIN BOR_ENTEDC edc ON e.PROGENTE=edc.PROGENTE ";

        $where = 'WHERE';
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where e.PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        if (array_key_exists('PROGCLIENT', $filtri) && $filtri['PROGCLIENT'] != null) {
            $this->addSqlParam($sqlParams, "PROGCLIENT", $filtri['PROGCLIENT'], PDO::PARAM_INT);
            $sql .= " $where PROGCLIENT=:PROGCLIENT";
            $where = 'AND';
        }
        if (array_key_exists('DESENTE', $filtri) && $filtri['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filtri['DESENTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = 'AND';
        }
        if (array_key_exists('INDIRENTE', $filtri) && $filtri['INDIRENTE'] != null) {
            $this->addSqlParam($sqlParams, "INDIRENTE", "%" . strtoupper(trim($filtri['INDIRENTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("INDIRENTE") . " LIKE :INDIRENTE";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('PROVINCIA', $filtri) && $filtri['PROVINCIA'] != null) {
            $this->addSqlParam($sqlParams, "PROVINCIA", "%" . strtoupper(trim($filtri['PROVINCIA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("PROVINCIA") . " LIKE :PROVINCIA";
            $where = 'AND';
        }
        if (array_key_exists('DES_BREVE', $filtri) && $filtri['DES_BREVE'] != null) {
            $this->addSqlParam($sqlParams, "DES_BREVE", "%" . strtoupper(trim($filtri['DES_BREVE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_BREVE") . " LIKE :DES_BREVE";
            $where = 'AND';
        }
        if (array_key_exists('CAP', $filtri) && $filtri['CAP'] != null) {
            $this->addSqlParam($sqlParams, "CAP", $filtri['CAP'], PDO::PARAM_STR);
            $sql .= " $where CAP=:CAP";
            $where = 'AND';
        }
        if (array_key_exists('CODENTE', $filtri) && $filtri['CODENTE'] != null) {
            $this->addSqlParam($sqlParams, "CODENTE", $filtri['CODENTE'], PDO::PARAM_STR);
            $sql .= " $where CODENTE=:CODENTE";
            $where = 'AND';
        }
        if (array_key_exists('CODATECO7', $filtri) && $filtri['CODATECO7'] != null) {
            $this->addSqlParam($sqlParams, "CODATECO7", $filtri['CODATECO7'], PDO::PARAM_STR);
            $sql .= " $where CODATECO7=:CODATECO7";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        if (array_key_exists('IDBOL_ENTE', $filtri) && $filtri['IDBOL_ENTE'] != null) {
            $this->addSqlParam($sqlParams, "IDBOL_ENTE", $filtri['IDBOL_ENTE'], PDO::PARAM_INT);
            $sql .= " $where IDBOL_ENTE=:IDBOL_ENTE";
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY e.PROGENTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_ENTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorEnti($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorEnti($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_ENTI
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return string Comando sql
     */
    public function getSqlLeggiBorEntiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGENTE'] = $cod;
        return self::getSqlLeggiBorEnti($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_ENTI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorEntiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorEntiChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Legge tabella BOR_ENTEDC (relazione 1-1 con BOR_ENTI)
     * @param int $cod Codice Ente
     * @return array Record BOR_ENTIDC
     */
    public function leggiBorEntidc($cod) {
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODENTE", $cod, PDO::PARAM_STR);
        $sql = "SELECT BOR_ENTI.* FROM BOR_ENTI WHERE CODENTE=:CODENTE";
        return ItaDB::DBSQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    /**
     * Legge tabella BOR_ENTI con codice Cliente
     * @param int $cod Codice Cliente
     * @return array Record BOR_ENTI
     */
    public function leggiBorEntiClient($cod) {
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGCLIENT", $cod, PDO::PARAM_INT);
        $sql = "SELECT BOR_ENTI.* FROM BOR_ENTI WHERE PROGCLIENT=:PROGCLIENT ORDER BY BOR_ENTI.PROGENTE";
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    // BOR_DISTR

    /**
     * Restituisce comando sql per lettura tabella BOR_DISTR
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return string Comando sql
     */
    public function getSqlLeggiBorDistr($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_DISTR.* FROM BOR_DISTR";
        $where = 'WHERE';
        if (array_key_exists('PROGDITTAD', $filtri) && $filtri['PROGDITTAD'] != null) {
            $this->addSqlParam($sqlParams, "PROGDITTAD", $filtri['PROGDITTAD'], PDO::PARAM_INT);
            $sql .= " $where PROGDITTAD=:PROGDITTAD";
            $where = 'AND';
        }
        if (array_key_exists('DESENTE', $filtri) && $filtri['DESENTE'] != null) {
            $this->addSqlParam($sqlParams, "DESENTE", "%" . strtoupper(trim($filtri['DESENTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESENTE";
            $where = 'AND';
        }
        if (array_key_exists('DESLOCAL', $filtri) && $filtri['DESLOCAL'] != null) {
            $this->addSqlParam($sqlParams, "DESLOCAL", "%" . strtoupper(trim($filtri['DESLOCAL'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESLOCAL") . " LIKE :DESLOCAL";
            $where = 'AND';
        }
        if (array_key_exists('DATAINIZ', $filtri) && $filtri['DATAINIZ'] != null) {
            $this->addSqlParam($sqlParams, "DATAINIZ", $filtri['DATAINIZ'], PDO::PARAM_STR);
            $sql .= " $where DATAINIZ=:DATAINIZ";
            $where = 'AND';
        }
        if (array_key_exists('DATAFINE', $filtri) && $filtri['DATAFINE'] != null) {
            $this->addSqlParam($sqlParams, "DATAFINE", $filtri['DATAFINE'], PDO::PARAM_STR);
            $sql .= " $where DATAFINE=:DATAFINE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGDITTAD';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_DISTR
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorDistr($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorDistr($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_DISTR
     * @param string $cod Chiave
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder     
     * @return string Comando sql
     */
    public function getSqlLeggiBorDistrChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['PROGDITTAD'] = $cod;
        return self::getSqlLeggiBorDistr($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_DISTR per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorDistrChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorDistrChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_LICEN

    /**
     * Restituisce comando sql per lettura tabella BOR_LICEN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorLicen($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_LICEN.*, "
                . $this->getCitywareDB()->strConcat('CODAREAMA', "'|'", 'CODLICEN', "'|'", 'PROGLICEN') . " AS \"ROW_ID\" " // CHIAVE COMPOSTA
                . " FROM BOR_LICEN";

        $where = 'WHERE';
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        if (array_key_exists('CODLICEN_key', $filtri) && $filtri['CODLICEN_key'] != null) {
            $this->addSqlParam($sqlParams, "CODLICEN_key", $filtri['CODLICEN_key'], PDO::PARAM_STR);
            $sql .= " $where CODLICEN=:CODLICEN_key";
            $where = 'AND';
        }
        if (array_key_exists('CODLICEN', $filtri) && $filtri['CODLICEN'] != null) {
            $this->addSqlParam($sqlParams, "CODLICEN", $filtri['CODLICEN'], PDO::PARAM_STR);
            $sql .= " $where CODLICEN>=:CODLICEN";
            $where = 'AND';
        }
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, "CODMODULO", $filtri['CODMODULO'], PDO::PARAM_STR);
            $sql .= " $where CODMODULO=:CODMODULO";
            $where = 'AND';
        }
        if (array_key_exists('PROGLICEN', $filtri) && $filtri['PROGLICEN'] != null) {
            $this->addSqlParam($sqlParams, "PROGLICEN", $filtri['PROGLICEN'], PDO::PARAM_INT);
            $sql .= " $where PROGLICEN=:PROGLICEN";
            $where = 'AND';
        }
        if (array_key_exists('DESLICEN', $filtri) && $filtri['DESLICEN'] != null) {
            $this->addSqlParam($sqlParams, "DESLICEN", "%" . strtoupper(trim($filtri['DESLICEN'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESENTE") . " LIKE :DESLICEN";
            $where = 'AND';
        }
        if (array_key_exists('SOFTHOUSE', $filtri) && $filtri['SOFTHOUSE'] != null) {
            $this->addSqlParam($sqlParams, "SOFTHOUSE", "%" . strtoupper(trim($filtri['SOFTHOUSE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("SOFTHOUSE") . " LIKE :SOFTHOUSE";
            $where = 'AND';
        }
        if (array_key_exists('DATARILA', $filtri) && $filtri['DATARILA'] != null) {
            $this->addSqlParam($sqlParams, "DATARILA", $filtri['DATARILA'], PDO::PARAM_STR);
            $sql .= " $where DATARILA=:DATARILA";
            $where = 'AND';
        }
        if ($filtri['FLAG_DIS'] == 0) {
            $sql .= " $where FLAG_DIS=0";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODAREAMA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_LICEN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorLicen($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorLicen($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_LICEN
     * @param string $codareama Codice Area
     * @param string $codlicen Codice Licenza
     * @param string $proglicen Progressivo Licenza
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorLicenChiave($codareama, $codlicen, $proglicen, &$sqlParams) {
        $filtri = array();
        $filtri['CODAREAMA'] = $codareama;
        $filtri['CODLICEN_key'] = $codlicen;
        $filtri['PROGLICEN'] = $proglicen;
        return self::getSqlLeggiBorLicen($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_LICEN per chiave
     * @param string $codareama Codice Area
     * @param string $codlicen Codice Licenza
     * @param string $proglicen Progressivo Licenza
     * @return array Record
     */
    public function leggiBorLicenChiave($codareama, $codlicen, $proglicen) {
        if ((!$codareama) || (!$codlicen) || (!$proglicen)) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorLicenChiave($codareama, $codlicen, $proglicen, $sqlParams), false, $sqlParams);
    }

    // BOR_MASTER

    /**
     * Restituisce comando sql per lettura tabella BOR_MASTER
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorMaster($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_MASTER.* FROM BOR_MASTER";
        $where = 'WHERE';
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        if (array_key_exists('CODSTAMPA', $filtri) && $filtri['CODSTAMPA'] != null) {
            $this->addSqlParam($sqlParams, "CODSTAMPA", $filtri['CODSTAMPA'], PDO::PARAM_STR);
            $sql .= " $where CODSTAMPA=:CODSTAMPA";
            $where = 'AND';
        }
        if (array_key_exists('DESAREA', $filtri) && $filtri['DESAREA'] != null) {
            $this->addSqlParam($sqlParams, "DESAREA", "%" . strtoupper(trim($filtri['DESAREA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESAREA") . " LIKE :DESAREA";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODAREAMA';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_MASTER
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorMaster($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorMaster($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_MASTER
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorMasterChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODAREAMA'] = $cod;
        return self::getSqlLeggiBorMaster($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_MASTER per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorMasterChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorMasterChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_MODULI

    /**
     * Restituisce comando sql per lettura tabella BOR_MODULI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorModuli($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_MODULI_V01.* FROM BOR_MODULI_V01";
        $where = 'WHERE';
        if (array_key_exists('CODMODULO', $filtri) && $filtri['CODMODULO'] != null) {
            $this->addSqlParam($sqlParams, "CODMODULO", strtoupper(trim($filtri['CODMODULO'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODMODULO") . "=:CODMODULO";
            $where = 'AND';
        }
        if (array_key_exists('CODAREAMA', $filtri) && $filtri['CODAREAMA'] != null) {
            $this->addSqlParam($sqlParams, "CODAREAMA", $filtri['CODAREAMA'], PDO::PARAM_STR);
            $sql .= " $where CODAREAMA=:CODAREAMA";
            $where = 'AND';
        }
        if (!empty($filtri['CODAREAMA_IN'])) {
            $values = $filtri['CODAREAMA_IN'];
            $sql .= ' ' . $where . ' CODAREAMA IN (';
            for ($i = 0; $i < count($values); $i++) {
                $sql .= "'$values[$i]'";
                if ($i < (count($values) - 1)) {
                    $sql .= ', ';
                }
            }
            $sql .= ')';
            $where = 'AND';
        }
        if (array_key_exists('DESAREA', $filtri) && $filtri['DESAREA'] != null) {
            $this->addSqlParam($sqlParams, "DESAREA", "%" . strtoupper(trim($filtri['DESAREA'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESAREA") . " LIKE :DESAREA";
            $where = 'AND';
        }
        if (array_key_exists('DESMODULO', $filtri) && $filtri['DESMODULO'] != null) {
            $this->addSqlParam($sqlParams, "DESMODULO", "%" . strtoupper(trim($filtri['DESMODULO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESMODULO") . " LIKE :DESMODULO";
            $where = 'AND';
        }
        if ($filtri['FLAG_DIS'] == 0) {
            $sql .= " $where FLAG_DIS=0";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODMODULO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_MODULI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorModuli($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorModuli($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_MODULI
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorModuliChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODMODULO'] = $cod;
        return self::getSqlLeggiBorModuli($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_MODULI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorModuliChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorModuliChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_MODORG

    /**
     * Restituisce comando sql per lettura tabella BOR_MODORG
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorModorg($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_MODORG.* FROM BOR_MODORG";
        $where = 'WHERE';
        if (array_key_exists('DESCRIZ', $filtri) && $filtri['DESCRIZ'] != null) {
            $this->addSqlParam($sqlParams, "DESCRIZ", "%" . strtoupper(trim($filtri['DESCRIZ'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DESCRIZ") . " LIKE :DESCRIZ";
            $where = 'AND';
        }
        if (array_key_exists('IDMODORG', $filtri) && $filtri['IDMODORG'] != null) {
            $this->addSqlParam($sqlParams, "IDMODORG", $filtri['IDMODORG'], PDO::PARAM_INT);
            $sql .= " $where IDMODORG=:IDMODORG";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        if (array_key_exists('CODUTE', $filtri) && $filtri['CODUTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTE", "%" . strtoupper(trim($filtri['CODUTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDMODORG';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_MODORG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorModorg($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorModorg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorModorgCtrlDate($filtri = array(), $excludeOrderBy = false, &$sqlParams = array(), $operation) {
        $sql = "SELECT IDMODORG FROM BOR_MODORG WHERE";

        //GESTIONE DATA INIZIO
        $sql .= " ((DATAINIZ <= :DATAINIZ1 AND (DATAFINE >= :DATAINIZ2 OR DATAFINE IS NULL))";
        $this->addSqlParam($sqlParams, "DATAINIZ1", $filtri['DATAINIZ'], PDO::PARAM_STR);
        $this->addSqlParam($sqlParams, "DATAINIZ2", $filtri['DATAINIZ'], PDO::PARAM_STR);

        //GESTIONE DATA FINE
        if (isSet($filtri['DATAFINE']) && trim($filtri['DATAFINE']) != '') {
            $sql .= " OR (DATAINIZ <= :DATAFINE1 AND (DATAFINE >= :DATAFINE2 OR DATAFINE IS NOT NULL))";
            $this->addSqlParam($sqlParams, "DATAFINE1", $filtri['DATAFINE'], PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE2", $filtri['DATAFINE'], PDO::PARAM_STR);
        }

        $sql .= ")";

        //GESTIONE ENTE
        if ($filtri['PROGENTE'] != null && $filtri['PROGENTE']) {
            $sql .= " AND PROGENTE=:PROGENTE";
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
        }

        //GESTIONE AGGIORNAMENTO/DELETE
        if ($operation === itaModelService::OPERATION_DELETE || $operation == itaModelService::OPERATION_UPDATE) {
            $sql .= " AND IDMODORG<>:IDMODORG";
            $this->addSqlParam($sqlParams, "IDMODORG", $filtri['IDMODORG'], PDO::PARAM_INT);
        }

        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_MODORG
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorModorgCtrlDate($filtri = array(), $multipla = true, $operation) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorModorgCtrlDate($filtri, false, $sqlParams, $operation), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_MODORG
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder    
     * @return string Comando sql
     */
    public function getSqlLeggiBorModorgChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDMODORG'] = $cod;
        return self::getSqlLeggiBorModorg($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_MODORG per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorModorgChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorModorgChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_AOO

    /**
     * Restituisce comando sql per lettura tabella BOR_AOO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorAoo($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT a.*, b.DESPORG, c.AOO_DOCER FROM BOR_AOO a"
                . " LEFT JOIN BOR_ORGAN b ON a.IDORGAN=b.IDORGAN"
                . " LEFT JOIN BOR_AOODC c ON a.IDAOO=c.IDAOO";
        $where = 'WHERE';
        if (array_key_exists('IDAOO', $filtri) && $filtri['IDAOO'] != null) {
            $this->addSqlParam($sqlParams, "IDAOO", $filtri['IDAOO'], PDO::PARAM_INT);
            $sql .= " $where a.IDAOO=:IDAOO";
            $where = 'AND';
        }
        if (array_key_exists('DESAOO', $filtri) && $filtri['DESAOO'] != null) {
            $this->addSqlParam($sqlParams, "DESAOO", "%" . strtoupper(trim($filtri['DESAOO'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("a.DESAOO") . " LIKE :DESAOO";
            $where = 'AND';
        }
        if (array_key_exists('CODAOOIPA', $filtri) && $filtri['CODAOOIPA'] != null) {
            $this->addSqlParam($sqlParams, "CODAOOIPA", strtoupper(trim($filtri['CODAOOIPA'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("a.CODAOOIPA") . "=:CODAOOIPA";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where a.PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY a.DESAOO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_AOO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorAoo($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorAoo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_AOO
     * @param string $cod Chiave    
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder 
     * @return string Comando sql
     */
    public function getSqlLeggiBorAooChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDAOO'] = $cod;
        return self::getSqlLeggiBorAoo($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_AOO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorAooChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorAooChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Legge tabella BOR_AOODC (relazione 1-1 con BOR_AOO)
     * @param int $cod Codice AOO
     * @return array Record BOR_AOODC
     */
    public function leggiBorAoodc($cod) {
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "IPA_CODAMM", $cod, PDO::PARAM_STR);
        $sql = "SELECT BOR_AOODC.* FROM BOR_AOODC WHERE IPA_CODAMM=:IPA_CODAMM";
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function leggiBorAooJoinFfeTabuff($multipla = false, $coduff_fe = null) {
        $sqlParams = array();

        $sql = "SELECT BOR_AOO.CODAOOIPA"
                . " FROM FFE_TABUFF"
                . " INNER JOIN BOR_AOO ON FFE_TABUFF.IDAOO=BOR_AOO.IDAOO";

        if ($coduff_fe != null && strlen(trim($coduff_fe)) > 0) {
            $sql .= " WHERE CODUFF_FE=:CODUFF_FE";
            $this->addSqlParam($sqlParams, "CODUFF_FE", $coduff_fe, PDO::PARAM_STR);
        }

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, $multipla, $sqlParams);
    }

    // BOR_ORGAN

    /**
     * Restituisce comando sql per lettura tabella BOR_ORGAN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorOrgan($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $select = "SELECT
                    BOR_ORGAN.*,
                    BOR_MODORG.DESCRIZ as MODORG
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG ";
        
        $sql = " WHERE BOR_ORGAN.L1ORG <> :L1ORGv";

        $this->addSqlParam($sqlParams, "L1ORGv", "00", PDO::PARAM_STR);
        $where = 'AND';

        if (array_key_exists('DESPORG', $filtri) && $filtri['DESPORG'] != null) {
            $this->addSqlParam($sqlParams, "DESPORG", "%" . strtoupper(trim($filtri['DESPORG'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG") . " LIKE :DESPORG";
            $where = 'AND';
        }
        if (array_key_exists('DESPORG_B', $filtri) && $filtri['DESPORG_B'] != null) {
            $this->addSqlParam($sqlParams, "DESPORG_B", "%" . strtoupper(trim($filtri['DESPORG_B'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG_B") . " LIKE :DESPORG_B";
            $where = 'AND';
        }
        if (array_key_exists('ALIAS', $filtri) && $filtri['ALIAS'] != null) {
            $this->addSqlParam($sqlParams, "ALIAS", "%" . strtoupper(trim($filtri['ALIAS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.ALIAS") . " LIKE :ALIAS";
            $where = 'AND';
        }
        if (array_key_exists('IDORGAN', $filtri) && $filtri['IDORGAN'] != null) {
            $this->addSqlParam($sqlParams, "IDORGAN", $filtri['IDORGAN'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.IDORGAN=:IDORGAN";
            $where = 'AND';
        }
        if (array_key_exists('IDBORAOO', $filtri) && $filtri['IDBORAOO'] != null) {
            $this->addSqlParam($sqlParams, "IDBORAOO", $filtri['IDBORAOO'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.IDBORAOO=:IDBORAOO";
            $where = 'AND';
        }
        if (array_key_exists('L1ORG', $filtri) && $filtri['L1ORG'] != null) {
            $this->addSqlParam($sqlParams, "L1ORG", $filtri['L1ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L1ORG=:L1ORG";
            $where = 'AND';
        }
        if (array_key_exists('L2ORG', $filtri) && $filtri['L2ORG'] != null) {
            $this->addSqlParam($sqlParams, "L2ORG", $filtri['L2ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L2ORG=:L2ORG";
            $where = 'AND';
        }
        if (array_key_exists('L3ORG', $filtri) && $filtri['L3ORG'] != null) {
            $this->addSqlParam($sqlParams, "L3ORG", $filtri['L3ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L3ORG=:L3ORG";
            $where = 'AND';
        }
        if (array_key_exists('L4ORG', $filtri) && $filtri['L4ORG'] != null) {
            $this->addSqlParam($sqlParams, "L4ORG", $filtri['L4ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L4ORG=:L4ORG";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.PROGINT=:PROGINT";
            $where = 'AND';
        }
        if (array_key_exists('ID_MODORG', $filtri) && $filtri['ID_MODORG'] != null) {
            $this->addSqlParam($sqlParams, "ID_MODORG", $filtri['ID_MODORG'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.ID_MODORG=:ID_MODORG";
            $where = 'AND';
        }
        if (array_key_exists('NODO', $filtri) && $filtri['NODO'] != null) {
            $this->addSqlParam($sqlParams, "NODO", $filtri['NODO'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.NODO=:NODO";
            $where = 'AND';
        }
        if (isSet($filtri['ATTIVO']) && $filtri['ATTIVO'] === true) {
            $this->addSqlParam($sqlParams, "DATAINIZ_lt", date('Y-m-d'), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE_gt", date('Y-m-d'), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.DATAINIZ<=:DATAINIZ_lt AND (BOR_ORGAN.DATAFINE>=:DATAFINE_gt OR BOR_ORGAN.DATAFINE IS NULL)";
            $where = 'AND';
        }
        if (isSet($filtri['ATTIVO']) && $filtri['ATTIVO'] === false) {
            $this->addSqlParam($sqlParams, "DATAINIZ_lt", date('Y-m-d'), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE_gt", date('Y-m-d'), PDO::PARAM_STR);
            $sql .= " $where (BOR_ORGAN.DATAINIZ>:DATAINIZ_lt OR BOR_ORGAN.DATAFINE<:DATAFINE_gt)";
            $where = 'AND';
        }
        if (isSet($filtri['LXORG_in_childs']) && is_array($filtri['LXORG_in_childs'])) {
            if (isSet($filtri['LXORG_in_childs']['L1ORG'])) {
                $filtri = array($filtri);
            }
            $i = 1;
            foreach ($filtri['LXORG_in_childs'] as $row) {
                $l1org = str_pad(ltrim($row['L1ORG'], " 0"));
                $l1org = (!empty($l1org) && $l1org != "00" ? $l1org : null);
                $l2org = str_pad(ltrim($row['L2ORG'], " 0"));
                $l2org = (!empty($l2org) && $l2org != "00" ? $l2org : null);
                $l3org = str_pad(ltrim($row['L3ORG'], " 0"));
                $l3org = (!empty($l3org) && $l3org != "00" ? $l3org : null);
                $l4org = str_pad(ltrim($row['L4ORG'], " 0"));
                $l4org = (!empty($l4org) && $l4org != "00" ? $l4org : null);

                if (!empty($l1org)) {
                    if ($i == 1) {
                        $sql .= " $where (";
                    } else {
                        $sql .= " OR ";
                    }

                    $this->addSqlParam($sqlParams, "L1ORG_in_" . $i, $l1org, PDO::PARAM_STR);
                    $sql .= " (BOR_ORGAN.L1ORG = :L1ORG_in_" . $i;

                    if (!empty($l2org)) {
                        $this->addSqlParam($sqlParams, "L2ORG_in_" . $i, $l2org, PDO::PARAM_STR);
                        $sql .= " AND BOR_ORGAN.L2ORG = :L2ORG_in_" . $i;

                        if (!empty($l3org)) {
                            $this->addSqlParam($sqlParams, "L3ORG_in_" . $i, $l3org, PDO::PARAM_STR);
                            $sql .= " AND BOR_ORGAN.L3ORG = :L3ORG_in_" . $i;

                            if (!empty($l4org)) {
                                $this->addSqlParam($sqlParams, "L4ORG_in_" . $i, $l4org, PDO::PARAM_STR);
                                $sql .= " AND BOR_ORGAN.L4ORG = :L4ORG_in_" . $i;
                            }
                        }
                    }
                    $sql .= ")";
                    $i++;
                }
            }
            if ($i > 1) {
                $sql .= ")";
                $where = "AND";
            }
        }
        if (isSet($filtri['AUTH']) && !$filtri['AUTH'] !== true) {
            if ($filtri['AUTH'] === false) {
                $sql .= " $where 1=0";
                $where = 'AND';
            } elseif ($filtri['AUTH'] !== true && !empty($filtri['AUTH'])) {
                $sql .= " $where (";
                $where = '';

                for ($i = 0; $i < count($filtri['AUTH']); $i++) {
                    $sql .= " $where (BOR_ORGAN.L1ORG = :L1ORGAUTH$i";
                    $this->addSqlParam($sqlParams, "L1ORGAUTH$i", $filtri['AUTH'][$i]['L1ORG'], PDO::PARAM_STR);

                    if (!empty($filtri['AUTH'][$i]['L2ORG'])) {
                        $sql .= " AND BOR_ORGAN.L2ORG = :L2ORGAUTH$i";
                        $this->addSqlParam($sqlParams, "L2ORGAUTH$i", $filtri['AUTH'][$i]['L2ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH'][$i]['L3ORG'])) {
                        $sql .= " AND BOR_ORGAN.L3ORG = :L3ORGAUTH$i";
                        $this->addSqlParam($sqlParams, "L3ORGAUTH$i", $filtri['AUTH'][$i]['L3ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH'][$i]['L4ORG'])) {
                        $sql .= " AND BOR_ORGAN.L4ORG = :L4ORGAUTH$i";
                        $this->addSqlParam($sqlParams, "L4ORGAUTH$i", $filtri['AUTH'][$i]['L4ORG'], PDO::PARAM_STR);
                    }
                    $sql .= ")";
                    $where = 'OR';
                }
                $sql .= ")";
                $where = 'AND';
            }
        }
        if (isSet($filtri['AUTH_PARENTS']) && !$filtri['AUTH_PARENTS'] !== true) {
            if ($filtri['AUTH_PARENTS'] === false) {
                $sql .= " $where 1=0";
                $where = 'AND';
            } elseif ($filtri['AUTH_PARENTS'] !== true && !empty($filtri['AUTH_PARENTS'])) {
                $sql .= " $where (";
                $where = '';

                for ($i = 0; $i < count($filtri['AUTH_PARENTS']); $i++) {
                    $sql .= " $where (BOR_ORGAN.L1ORG = :L1ORGAUTH$i";
                    $this->addSqlParam($sqlParams, "L1ORGAUTH$i", $filtri['AUTH_PARENTS'][$i]['L1ORG'], PDO::PARAM_STR);

                    if (!empty($filtri['AUTH_PARENTS'][$i]['L2ORG']) && $filtri['AUTH_PARENTS'][$i]['L2ORG'] != '00') {
                        $sql .= " AND (BOR_ORGAN.L2ORG = :L2ORGAUTH$i OR BOR_ORGAN.L2ORG = '00')";
                        $this->addSqlParam($sqlParams, "L2ORGAUTH$i", $filtri['AUTH_PARENTS'][$i]['L2ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH_PARENTS'][$i]['L3ORG']) && $filtri['AUTH_PARENTS'][$i]['L3ORG'] != '00') {
                        $sql .= " AND (BOR_ORGAN.L3ORG = :L3ORGAUTH$i OR BOR_ORGAN.L3ORG = '00')";
                        $this->addSqlParam($sqlParams, "L3ORGAUTH$i", $filtri['AUTH_PARENTS'][$i]['L3ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH_PARENTS'][$i]['L4ORG']) && $filtri['AUTH_PARENTS'][$i]['L4ORG'] != '00') {
                        $sql .= " AND (BOR_ORGAN.L4ORG = :L4ORGAUTH$i OR BOR_ORGAN.L4ORG = '00')";
                        $this->addSqlParam($sqlParams, "L4ORGAUTH$i", $filtri['AUTH_PARENTS'][$i]['L4ORG'], PDO::PARAM_STR);
                    }
                    $sql .= ")";
                    $where = 'OR';
                }
                $sql .= ")";
                $where = 'AND';
            }
        }
        if(!empty($filtri['KRUOLO']) && !empty($filtri['CODUTE_UTEORG'])){
            $select .= "LEFT JOIN BOR_UTEORG ON BOR_ORGAN.IDORGAN = BOR_UTEORG.IDORGAN ";
            $sql .= " $where BOR_UTEORG.CODUTE = :CODUTE_UTEORG AND BOR_UTEORG.KRUOLO = :KRUOLO";
            
            $this->addSqlParam($sqlParams, "CODUTE_UTEORG", strtoupper(trim($filtri['CODUTE_UTEORG'])), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "KRUOLO", $filtri['KRUOLO'], PDO::PARAM_INT);
        }
        
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG';
        return $select.$sql;
    }

    /**
     * Restituisce dati tabella BOR_ORGAN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorOrgan($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrgan($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_ORGAN
     * @param string $cod Chiave 
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder    
     * @return string Comando sql
     */
    public function getSqlLeggiBorOrganChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDORGAN'] = $cod;
        return self::getSqlLeggiBorOrgan($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_ORGAN per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorOrganChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrganChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Legge nodi di primo livello di BOR_ORGAN
     * @param int $progente Progressivo ente
     * @param int $idmodorg ID Modello organizzativo
     * @return array Lista nodi primo livello
     */
    public function leggiBorOrganNodiPrimoLivello($progente = null, $idmodorg = null) {
        if (isSet($idmodorg) && trim($idmodorg) == '') {
            $idmodorg = null;
        }

        $sqlParams = array();
        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE NODO = '1'";
        if (isSet($progente)) {
            $sql .= " AND BOR_ORGAN.PROGENTE=:PROGENTE";
            $this->addSqlParam($sqlParams, "PROGENTE", $progente, PDO::PARAM_INT);
        }
        if (isSet($idmodorg)) {
            $sql .= " AND BOR_ORGAN.ID_MODORG=:ID_MODORG";
            $this->addSqlParam($sqlParams, "ID_MODORG", $idmodorg, PDO::PARAM_INT);
        }
        $sql .= " ORDER BY L1ORG, L2ORG, L3ORG, L4ORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    /**
     * Legge nodi figli di BOR_ORGAN rispetto al nodo padre
     * @param int $idNodo ID Nodo Padre
     * @return array Lista nodi
     */
    public function leggiBorOrganFigli($idNodo) {

        // Legge record padre
        $padre = $this->leggiBorOrganChiave($idNodo);

        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGENTE", $padre['PROGENTE'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ID_MODORG", $padre['ID_MODORG'], PDO::PARAM_INT);

        // Identifica condizione di ricerca dei figli
        switch (trim($padre['NODO'])) {
            case '1':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $condRicFigli = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG<>'00' AND BOR_ORGAN.L3ORG='00'";
                break;
            case '2':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $padre['L2ORG'], PDO::PARAM_STR);
                $condRicFigli = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG<>'00' AND BOR_ORGAN.L4ORG='00'";
                break;
            case '3':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $padre['L2ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L3ORG", $padre['L3ORG'], PDO::PARAM_STR);
                $condRicFigli = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG=:L3ORG AND BOR_ORGAN.L4ORG<>'00'";
                break;
            case '4':
                return null;
        }

        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE 
                    $condRicFigli AND BOR_ORGAN.PROGENTE=:PROGENTE AND BOR_ORGAN.ID_MODORG=:ID_MODORG 
                ORDER BY
                    BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    /**
     * Legge record padre
     * @param mixed $nodo ID nodo o array nodo
     * @return array Record padre
     */
    public function leggiBorOrganPadre($nodo) {

        // Legge record nodo
        if (!is_array($nodo)) {
            $nodo = $this->leggiBorOrganChiave($nodo);
        }

        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGENTE", $nodo['PROGENTE'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ID_MODORG", $nodo['ID_MODORG'], PDO::PARAM_INT);

        // Identifica condizione di ricerca dei figli
        switch (trim($nodo['NODO'])) {
            case '2':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG='00'";
                break;
            case '3':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $nodo['L2ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG='00'";
                break;
            case '4':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $nodo['L2ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L3ORG", $nodo['L3ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG=:L3ORG AND BOR_ORGAN.L4ORG='00'";
                break;
        }

        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE 
                    $condRicPadre AND BOR_ORGAN.PROGENTE=:PROGENTE AND BOR_ORGAN.ID_MODORG=:ID_MODORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    /**
     * Legge livello BOR_ORGAN
     * @param int $id ID Nodo
     * @return int Livello
     */
    public function getLivelloBorOrgan($id) {
        $padre = $this->leggiBorOrganChiave($id);

        return intval(trim($padre['NODO']));
    }

    /**
     * Legge BOR_ORGAN filtrando per utente su BOR_UTEORG.
     * Parametro $active, se true restituisce quello non scaduto se presente.
     */
    public function getBorOrganFromUser($user, $active = false) {

        $sql = "";
        $results;
        $sqlParams = array();

        if (!empty($user)) {
            $sql = "SELECT BOR_ORGAN.*, BOR_UTEORG.* "
                    . "FROM BOR_ORGAN "
                    . "RIGHT JOIN BOR_UTEORG ON "
                    . "(BOR_ORGAN.L1ORG=BOR_UTEORG.L1ORG AND "
                    . "BOR_ORGAN.L2ORG=BOR_UTEORG.L2ORG AND "
                    . "BOR_ORGAN.L3ORG=BOR_UTEORG.L3ORG AND "
                    . "BOR_ORGAN.L4ORG=BOR_UTEORG.L4ORG) ";

            $where = 'WHERE';


//            $this->addSqlParam($sqlParams, "CODUTE", "%" . strtoupper(trim($user)) . "%", PDO::PARAM_STR);
//            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_UTEORG.CODUTE") . " LIKE :CODUTE";
//            $where = 'AND';

            $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($user)) , PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_UTEORG.CODUTE") . " = :CODUTE";
            $where = 'AND';

            if ($active) {
                $dataNow = date('Y-m-d');

                $this->addSqlParam($sqlParams, "DATAFINE", $dataNow, PDO::PARAM_STR);
                $sql .= " $where (BOR_UTEORG.DATAFINE IS NULL OR BOR_UTEORG.DATAFINE>:DATAFINE)";
                $where = 'AND';
            }

            $sql .= " ORDER BY BOR_UTEORG.FLAG_DEFAULT, BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG";
            
            $results = ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
        }

        return $results;
    }

    // BOR_RESPO

    /**
     * Restituisce comando sql per lettura tabella BOR_RESPO
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorRespo($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_RESPO.* FROM BOR_RESPO";
        $where = 'WHERE';
        $annocorrente = date("Y");
        $annoprecedente = $annocorrente - 1;
        $dt_fine_annoprec = "31-12-" . $annoprecedente;

        if (array_key_exists('NOMERES', $filtri) && $filtri['NOMERES'] != null) {
            $this->addSqlParam($sqlParams, "NOMERES", "%" . strtoupper(trim($filtri['NOMERES'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("NOMERES") . " LIKE :NOMERES";
            $where = 'AND';
        }
        if (array_key_exists('PROGRESPO', $filtri) && $filtri['PROGRESPO'] != null) {
            $this->addSqlParam($sqlParams, "PROGRESPO", $filtri['PROGRESPO'], PDO::PARAM_INT);
            $sql .= " $where PROGRESPO>=:PROGRESPO";
            $where = 'AND';
        }
        if (array_key_exists('IDRESPO', $filtri) && $filtri['IDRESPO'] != null) {
            $this->addSqlParam($sqlParams, "IDRESPO", $filtri['IDRESPO'], PDO::PARAM_INT);
            $sql .= " $where IDRESPO=:IDRESPO";
            $where = 'AND';
        }
        if (array_key_exists('CODUTE', $filtri) && $filtri['CODUTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTE", "%" . strtoupper(trim($filtri['CODUTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS=:FLAG_DIS";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGRESPO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_RESPO
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorRespo($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorRespo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_RESPO
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorRespoChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDRESPO'] = $cod;
        return self::getSqlLeggiBorRespo($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_RESPO per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorRespoChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorRespoChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_GRPRET

    /**
     * Restituisce comando sql per lettura tabella BOR_GRPRET
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorGrpret($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_GRPRET.*, "
                . $this->getCitywareDB()->strConcat('PROGGRPRE', "'|'", 'PROGENTE') . " AS \"ROW_ID\" "
                . " FROM BOR_GRPRET";
        $where = 'WHERE';
        if (array_key_exists('DES_GRPRE', $filtri) && $filtri['DES_GRPRE'] != null) {
            $this->addSqlParam($sqlParams, "DES_GRPRE", "%" . strtoupper(trim($filtri['DES_GRPRE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_GRPRE") . " LIKE :DES_GRPRE";
            $where = 'AND';
        }
        if (array_key_exists('PROGGRPRE', $filtri) && $filtri['PROGGRPRE'] != null) {
            $this->addSqlParam($sqlParams, "PROGGRPRE", $filtri['PROGGRPRE'], PDO::PARAM_INT);
            $sql .= " $where PROGGRPRE>:PROGGRPRE";
            $where = 'AND';
        }
        if (array_key_exists('PROGGRPRE_key', $filtri) && $filtri['PROGGRPRE_key'] != null) {
            $this->addSqlParam($sqlParams, "PROGGRPRE_key", $filtri['PROGGRPRE_key'], PDO::PARAM_INT);
            $sql .= " $where PROGGRPRE=:PROGGRPRE_key";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY PROGGRPRE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_GRPRET
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorGrpret($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorGrpret($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_GRPRET
     * @param int $proggrpre Chiave     
     * @param int $progente Progressivo Ente 
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorGrpretChiave($proggrpre, $progente, &$sqlParams) {
        $filtri = array();
        $filtri['PROGGRPRE_key'] = $proggrpre;
        $filtri['PROGENTE_key'] = $progente;
        return self::getSqlLeggiBorGrpret($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_GRPRET per chiave
     * @param int $proggrpre Chiave     
     * @param int $progente Progressivo Ente 
     * @return object Record
     */
    public function leggiBorGrpretChiave($proggrpre, $progente) {
        if (!$proggrpre || !$progente) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorGrpretChiave($proggrpre, $progente, $sqlParams), false, $sqlParams);
    }

    // BOR_GRPRED

    /**
     * Restituisce comando sql per lettura tabella BOR_GRPRED
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorGrpred($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_GRPRED.*, BOR_RESPO.NOMERES AS NOME_RESP, "
                . $this->getCitywareDB()->strConcat('BOR_GRPRED.PROGGRPRE', "'|'", 'BOR_GRPRED.PROGENTE', "'|'", 'BOR_GRPRED.PROGRESPO') . " AS \"ROW_ID\" " // CHIAVE COMPOSTA
                . " FROM BOR_GRPRED "
                . "LEFT OUTER JOIN BOR_RESPO on BOR_GRPRED.PROGRESPO=BOR_RESPO.PROGRESPO "
                . "AND BOR_GRPRED.PROGENTE=BOR_RESPO.PROGENTE";

        $where = 'WHERE';
        if (array_key_exists('PROGGRPRE', $filtri) && $filtri['PROGGRPRE'] != null) {
            $this->addSqlParam($sqlParams, "PROGGRPRE", $filtri['PROGGRPRE'], PDO::PARAM_INT);
            $sql .= " $where BOR_GRPRED.PROGGRPRE=:PROGGRPRE";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where BOR_GRPRED.PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BOR_GRPRED.PROGRESPO';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_GRPRED
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorGrpred($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorGrpred($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_GRPRED
     * @param string $proggrpre Chiave 
     * @param string $progente Progressivo Ente
     * @param string $progrespo Progressivo Responsabile     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorGrpredChiave($proggrpre, $progente, $progrespo, &$sqlParams) {
        $filtri = array();
        $filtri['PROGGRPRE'] = $proggrpre;
        $filtri['PROGENTE'] = $progente;
        return self::getSqlLeggiBorGrpred($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_GRPRED per chiave
     * @param string $proggrpre Chiave 
     * @param string $progente Progressivo Ente
     * @param string $progrespo Progressivo Responsabile     
     * @return object Record
     */
    public function leggiBorGrpredChiave($proggrpre, $progente, $progrespo) {
        if (!$proggrpre || !$progente || !$progrespo) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorGrpredChiave($proggrpre, $progente, $progrespo, $sqlParams), false, $sqlParams);
    }

    // BOR_UTENTI

    /**
     * Restituisce comando sql per lettura tabella BOR_UTENTI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorUtenti($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_UTENTI_V01.* FROM BOR_UTENTI_V01";
        $where = 'WHERE';

        if (array_key_exists('PROGCLIENT', $filtri) && $filtri['PROGCLIENT'] != null) {
            $this->addSqlParam($sqlParams, "PROGCLIENT", $filtri['PROGCLIENT'], PDO::PARAM_INT);
            $sql .= " $where PROGCLIENT=:PROGCLIENT";
            $where = 'AND';
        }
        if (array_key_exists('CODUTE', $filtri) && $filtri['CODUTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTE", "%" . strtoupper(trim($filtri['CODUTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (isSet($filtri['CODUTE_key'])) {
            $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($filtri['CODUTE_key'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTE") . "=:CODUTE";
            $where = 'AND';
        }
        if (isSet($filtri['DATAFINE_NULL']) && $filtri['DATAFINE_NULL']) {
            $sql .= " $where DATAFINE IS NULL";
            $where = 'AND';
        }
        if (array_key_exists('NOMEUTE', $filtri) && $filtri['NOMEUTE'] != null) {
            $this->addSqlParam($sqlParams, "NOMEUTE", "%" . strtoupper(trim($filtri['NOMEUTE'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("NOMEUTE") . " LIKE :NOMEUTE";
            $where = 'AND';
        }
        if (array_key_exists('CODFISCALE', $filtri) && $filtri['CODFISCALE'] != null) {
            $this->addSqlParam($sqlParams, "CODFISCALE", strtoupper(trim($filtri['CODFISCALE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODFISCALE") . " = :CODFISCALE";
            $where = 'AND';
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY CODUTE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_UTENTI
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorUtenti($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorUtenti($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_UTENTI
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorUtentiChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['CODUTE_key'] = $cod;
        return self::getSqlLeggiBorUtenti($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_UTENTI per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorUtentiChiave($cod) {
        if (strlen(trim($cod)) === 0) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorUtentiChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_UTELIV

    /**
     * Restituisce comando sql per lettura tabella BOR_UTELIV
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorUteliv($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_UTELIV.*, BOR_LIVELL.DES_LIVELL
FROM BOR_UTELIV
JOIN BOR_LIVELL on BOR_UTELIV.IDLIVELL = BOR_LIVELL.IDLIVELL ";

        $where = 'WHERE';

        if (array_key_exists('CODUTENTE', $filtri) && $filtri['CODUTENTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTENTE", strtoupper(trim($filtri['CODUTENTE'])), PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("CODUTENTE") . " = :CODUTENTE";
            $where = 'AND';
        }
        if (array_key_exists('IDUTELIV', $filtri) && $filtri['IDUTELIV'] != null) {
            $this->addSqlParam($sqlParams, "IDUTELIV", strtoupper(trim($filtri['IDUTELIV'])), PDO::PARAM_INT);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("IDUTELIV") . " = :IDUTELIV";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDUTELIV';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_UTELIV
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorUteliv($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorUteliv($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_UTELIV
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorUtelivChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDUTELIV'] = $cod;
        return self::getSqlLeggiBorUteliv($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_UTELIV per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorUtelivChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorUtelivChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_LIVELL

    /**
     * Restituisce comando sql per lettura tabella BOR_LIVELL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorLivell($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_LIVELL.* FROM BOR_LIVELL";
        $where = 'WHERE';

        if (array_key_exists('IDLIVELL', $filtri) && $filtri['IDLIVELL'] != null) {
            $this->addSqlParam($sqlParams, "IDLIVELL", $filtri['IDLIVELL'], PDO::PARAM_INT);
            $sql .= " $where IDLIVELL = :IDLIVELL";
            $where = 'AND';
        }
        if (array_key_exists('DES_LIVELL', $filtri) && $filtri['DES_LIVELL'] != null) {
            $this->addSqlParam($sqlParams, "DES_LIVELL", '%' . strtoupper(trim($filtri['DES_LIVELL'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper('DES_LIVELL') . " LIKE :DES_LIVELL";
            $where = 'AND';
        }
        if (array_key_exists('CODUTE', $filtri) && $filtri['CODUTE'] != null) {
            $this->addSqlParam($sqlParams, "CODUTE", '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper('CODUTE') . " LIKE :CODUTE";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_DIS', $filtri) && $filtri['FLAG_DIS'] != null) {
            $this->addSqlParam($sqlParams, "FLAG_DIS", $filtri['FLAG_DIS'], PDO::PARAM_INT);
            $sql .= " $where FLAG_DIS = :FLAG_DIS";
            $where = 'AND';
        }

        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDLIVELL';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_LIVELL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorLivell($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorLivell($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_LIVELL
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorLivellChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDLIVELL'] = $cod;
        return self::getSqlLeggiBorLivell($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_LIVELL per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorLivellChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorLivellChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_IDBOL

    /**
     * Restituisce comando sql per lettura tabella BOR_IDBOL
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorIdbol($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_IDBOL.* FROM BOR_IDBOL";
        $where = 'WHERE';

        if (array_key_exists('IDBOL_SERE', $filtri) && $filtri['IDBOL_SERE'] != 0) {
            $this->addSqlParam($sqlParams, "IDBOL_SERE", strtoupper(trim($filtri['IDBOL_SERE'])), PDO::PARAM_INT);
            $sql .= " $where IDBOL_SERE=:IDBOL_SERE";
            $where = 'AND';
        }
        if (array_key_exists('F_MOD_IVA', $filtri) && $filtri['F_MOD_IVA'] != 0) {
            $this->addSqlParam($sqlParams, "F_MOD_IVA", strtoupper(trim($filtri['F_MOD_IVA'])), PDO::PARAM_INT);
            $sql .= " $where F_MOD_IVA=:F_MOD_IVA";
            $where = 'AND';
        }
        $sql .= " $where FLAG_DIS = 0";
        $sql .= $excludeOrderBy ? '' : ' ORDER BY IDBOL_SERE';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_IDBOL
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorIdbol($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorIdbol($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_IDBOL
     * @param string $cod Chiave     
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorIdbolChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['IDBOL_SERE'] = $cod;
        return self::getSqlLeggiBorIdbol($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_IDBOL per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorIdbolChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorIdbolChiave($cod, $sqlParams), false, $sqlParams);
    }

    public function getSqlLeggiBorOrganAlias($filtri = null, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT ALIAS FROM BOR_ORGAN";

        if (isSet($filtri['PROGENTE'])) {
            $sql .= " WHERE PROGENTE = :PROGENTE";
            $this->addSqlParam($sqlParams, "PROGENTE", strtoupper(trim($filtri['PROGENTE'])), PDO::PARAM_INT);
        }

        $sql .= " GROUP BY ALIAS";

        if ($excludeOrderBy === false) {
            $sql .= " ORDER BY ALIAS";
        }
        return $sql;
    }

    /**
     * Restitusce gli alias presenti
     * @param array $filtri (accetta PROGENTE per definire l'ente)
     * @param boolean $multipla
     * @return array
     */
    public function leggiBorOrganAlias($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrganAlias($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlleggiStoricoResponsabiliOrganigramma($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    BOR_STORES.*,
                    BOR_RESPO.NOMERES,
                    BOR_RESPO.CODUTE_RESP
                FROM BOR_STORES
                LEFT OUTER JOIN BOR_RESPO ON BOR_STORES.PROGRESPO=BOR_RESPO.PROGRESPO";

        $where = $this->setDefaultFilters('BOR_STORES', $filtri, $sqlParams);
        if (!empty($where)) {
            $sql .= " WHERE " . $where;
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY BOR_STORES.DATAINIZ";
        }

        return $sql;
    }

    public function leggiStoricoResponsabiliOrganigramma($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiStoricoResponsabiliOrganigramma($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlleggiBorUteorg($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    BOR_UTEORG.*,
                    BOR_UTENTI.NOMEUTE,
                    BOR_ORGAN.DESPORG,
                    FTA_AUTUTE.TIPO_OPER,
                    BOR_RUOLI.DIRIGENTE
                FROM BOR_UTEORG
                JOIN BOR_UTENTI ON BOR_UTEORG.CODUTE = BOR_UTENTI.CODUTE
                JOIN BOR_ORGAN ON BOR_UTEORG.L1ORG = BOR_ORGAN.L1ORG
                              AND BOR_UTEORG.L2ORG = BOR_ORGAN.L2ORG
                              AND BOR_UTEORG.L3ORG = BOR_ORGAN.L3ORG
                              AND BOR_UTEORG.L4ORG = BOR_ORGAN.L4ORG
                              AND BOR_UTEORG.PROGENTE = BOR_ORGAN.PROGENTE
                LEFT JOIN FTA_AUTUTE ON BOR_UTEORG.CODUTE = FTA_AUTUTE.CODUTE_OP
                LEFT JOIN BOR_RUOLI ON BOR_UTEORG.KRUOLO = BOR_RUOLI.KRUOLO";
        $where = " WHERE";


        if (isSet($filtri['IDUTEORG']) && trim($filtri['IDUTEORG']) != '') {
            $this->addSqlParam($sqlParams, "IDUTEORG", intval($filtri['IDUTEORG']), PDO::PARAM_INT);
            $sql .= $where . " BOR_UTEORG.IDUTEORG = :IDUTEORG";
            $where = " AND";
        }
        if (isSet($filtri['KEY_CODUTE'])) {
            $this->addSqlParam($sqlParams, "KEY_CODUTE", strtoupper(trim($filtri['KEY_CODUTE'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_UTEORG.CODUTE') . " = :KEY_CODUTE";
            $where = " AND";
        }
        if (isSet($filtri['KEY_DATAINIZ'])) {
            $date = strtotime($filtri['KEY_DATAINIZ']);
            $this->addSqlParam($sqlParams, "KEY_DATAINIZ", date('Y-m-d', $date), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.DATAINIZ = :KEY_DATAINIZ";
            $where = " AND";
        }
        if (isSet($filtri['CODUTE']) && trim($filtri['CODUTE']) != '') {
            $this->addSqlParam($sqlParams, "CODUTE", '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_UTEORG.CODUTE') . " LIKE :CODUTE";
            $where = " AND";
        }
        if (isSet($filtri['NOMEUTE']) && trim($filtri['NOMEUTE']) != '') {
            $this->addSqlParam($sqlParams, "NOMEUTE", '%' . strtoupper(trim($filtri['NOMEUTE'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_UTENTI.NOMEUTE') . " LIKE :NOMEUTE";
            $where = " AND";
        }
        if (isSet($filtri['DESPORG']) && trim($filtri['DESPORG']) != '') {
            $this->addSqlParam($sqlParams, "DESPORG", '%' . strtoupper(trim($filtri['DESPORG'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_ORGAN.DESPORG') . " LIKE :DESPORG";
            $where = " AND";
        }
        if (isSet($filtri['TIPO_OPER']) && trim($filtri['TIPO_OPER']) != '') {
            $this->addSqlParam($sqlParams, "TIPO_OPER", intval($filtri['TIPO_OPER']), PDO::PARAM_INT);
            $sql .= $where . " FTA_AUTUTE.TIPO_OPER = :TIPO_OPER";
            $where = " AND";
        }
        if (isSet($filtri['DIRIGENTE']) && trim($filtri['DIRIGENTE']) != '') {
            $this->addSqlParam($sqlParams, "DIRIGENTE", intval($filtri['DIRIGENTE']), PDO::PARAM_INT);
            $sql .= $where . " BOR_RUOLI.DIRIGENTE = :DIRIGENTE";
            $where = " AND";
        }
        if (isSet($filtri['ID_AUTUFF']) && trim($filtri['ID_AUTUFF']) != '') {
            $this->addSqlParam($sqlParams, "ID_AUTUFF", intval($filtri['ID_AUTUFF']), PDO::PARAM_INT);
            $sql .= $where . " BOR_UTEORG.ID_AUTUFF = :ID_AUTUFF";
            $where = " AND";
        }

//        if (isSet($filtri['DES_AUTUFF']) && trim($filtri['DES_AUTUFF']) != '') {
//            $this->addSqlParam($sqlParams, "DES_AUTUFF", '%' . strtoupper(trim($filtri['DES_AUTUFF'])) . '%', PDO::PARAM_STR);
//            $sql .= $where . " " . $this->getCitywareDB()->strUpper('FTA_AUTUFF.DES_AUTUFF') . " LIKE :DES_AUTUFF";
//            $where = " AND";
//        }
//        if (isSet($filtri['RUOLOUTE']) && trim($filtri['RUOLOUTE']) != '') {
//            $this->addSqlParam($sqlParams, "RUOLOUTE", '%' . strtoupper(trim($filtri['RUOLOUTE'])) . '%', PDO::PARAM_STR);
//            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_UTENTI.RUOLOUTE') . " LIKE :RUOLOUTE";
//            $where = " AND";
//        }
        if (isSet($filtri['PROGENTE']) && trim($filtri['PROGENTE']) != '') {
            $this->addSqlParam($sqlParams, "PROGENTE", intval($filtri['PROGENTE']), PDO::PARAM_INT);
            $sql .= $where . " BOR_UTEORG.PROGENTE = :PROGENTE";
            $where = " AND";
        }
        if (isSet($filtri['GET_PARENTS']) && $filtri['GET_PARENTS'] === true) {
            if (!empty($filtri['L1ORG'])) {
                $this->addSqlParam($sqlParams, "L1ORG", strtoupper(trim($filtri['L1ORG'])), PDO::PARAM_STR);
                $sql .= $where . " BOR_UTEORG.L1ORG = :L1ORG";
                $where = " AND";
            }
            if (!empty($filtri['L2ORG'])) {
                $this->addSqlParam($sqlParams, "L2ORG", strtoupper(trim($filtri['L2ORG'])), PDO::PARAM_STR);
                $sql .= $where . " (BOR_UTEORG.L2ORG = :L2ORG OR BOR_UTEORG.L2ORG = '00')";
                $where = " AND";
            } else {
                $sql .= $where . " BOR_UTEORG.L2ORG = '00'";
                $where = " AND";
            }
            if (!empty($filtri['L3ORG'])) {
                $this->addSqlParam($sqlParams, "L3ORG", strtoupper(trim($filtri['L3ORG'])), PDO::PARAM_STR);
                $sql .= $where . " (BOR_UTEORG.L3ORG = :L3ORG OR BOR_UTEORG.L3ORG = '00')";
                $where = " AND";
            } else {
                $sql .= $where . " BOR_UTEORG.L3ORG = '00'";
                $where = " AND";
            }
            if (!empty($filtri['L4ORG'])) {
                $this->addSqlParam($sqlParams, "L4ORG", strtoupper(trim($filtri['L4ORG'])), PDO::PARAM_STR);
                $sql .= $where . " (BOR_UTEORG.L4ORG = :L4ORG OR BOR_UTEORG.L4ORG = '00')";
                $where = " AND";
            } else {
                $sql .= $where . " BOR_UTEORG.L4ORG = '00'";
                $where = " AND";
            }
        } else {
            if (!empty($filtri['L1ORG'])) {
                $this->addSqlParam($sqlParams, "L1ORG", strtoupper(trim($filtri['L1ORG'])), PDO::PARAM_STR);
                $sql .= $where . " BOR_UTEORG.L1ORG = :L1ORG";
                $where = " AND";
            }
            if (!empty($filtri['L2ORG'])) {
                $this->addSqlParam($sqlParams, "L2ORG", strtoupper(trim($filtri['L2ORG'])), PDO::PARAM_STR);
                $sql .= $where . " BOR_UTEORG.L2ORG = :L2ORG";
                $where = " AND";
            }
            if (!empty($filtri['L3ORG'])) {
                $this->addSqlParam($sqlParams, "L3ORG", strtoupper(trim($filtri['L3ORG'])), PDO::PARAM_STR);
                $sql .= $where . " BOR_UTEORG.L3ORG = :L3ORG";
                $where = " AND";
            }
            if (!empty($filtri['L4ORG'])) {
                $this->addSqlParam($sqlParams, "L4ORG", strtoupper(trim($filtri['L4ORG'])), PDO::PARAM_STR);
                $sql .= $where . " BOR_UTEORG.L4ORG = :L4ORG";
                $where = " AND";
            }
        }

        if (!empty($filtri['L1ORG_diff'])) {
            $this->addSqlParam($sqlParams, "L1ORG", strtoupper(trim($filtri['L1ORG'])), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.L1ORG <> :L1ORG";
            $where = " AND";
        }
        if (!empty($filtri['L2ORG_diff'])) {
            $this->addSqlParam($sqlParams, "L2ORG", strtoupper(trim($filtri['L2ORG'])), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.L2ORG <> :L2ORG";
            $where = " AND";
        }
        if (!empty($filtri['L3ORG_diff'])) {
            $this->addSqlParam($sqlParams, "L3ORG", strtoupper(trim($filtri['L3ORG'])), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.L3ORG <> :L3ORG";
            $where = " AND";
        }
        if (!empty($filtri['L4ORG_diff'])) {
            $this->addSqlParam($sqlParams, "L4ORG", strtoupper(trim($filtri['L4ORG'])), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.L4ORG <> :L4ORG";
            $where = " AND";
        }
        if (isSet($filtri['KRUOLO']) && trim($filtri['KRUOLO']) != '') {
            $this->addSqlParam($sqlParams, "KRUOLO", intval($filtri['KRUOLO']), PDO::PARAM_INT);
            $sql .= $where . " BOR_UTEORG.KRUOLO = :KRUOLO";
            $where = " AND";
        }
        if (isSet($filtri['RUOLOUTE']) && trim($filtri['RUOLOUTE']) != '') {
            $this->addSqlParam($sqlParams, "RUOLOUTE", '%' . strtoupper(trim($filtri['RUOLOUTE'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_UTEORG.RUOLOUTE') . " LIKE :RUOLOUTE";
            $where = " AND";
        }
        if (isSet($filtri['IDORGAN']) && trim($filtri['IDORGAN']) != '') {
            $this->addSqlParam($sqlParams, "IDORGAN", intval($filtri['IDORGAN']), PDO::PARAM_INT);
            $sql .= $where . " BOR_UTEORG.IDORGAN = :IDORGAN";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_DEFAULT']) && trim($filtri['FLAG_DEFAULT']) != '') {
            $this->addSqlParam($sqlParams, 'FLAG_DEFAULT', intval($filtri['FLAG_DEFAULT']), PDO::PARAM_INT);
            $sql .= " $where BOR_UTEORG.FLAG_DEFAULT=:FLAG_DEFAULT";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_ESCLUDI_CESSATI']) && trim($filtri['FLAG_ESCLUDI_CESSATI']) != '') {
            $sql .= " $where BOR_UTEORG.DATAFINE IS NULL";
            $where = " AND";
        }


        if (isSet($filtri['ATTIVO']) && $filtri['ATTIVO'] == true) {
            $this->addSqlParam($sqlParams, "DATAINIZ_lt", date('Y-m-d'), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE_gt", date('Y-m-d'), PDO::PARAM_STR);
            $sql .= $where . " BOR_UTEORG.DATAINIZ <= :DATAINIZ_lt AND "
                    . "(BOR_UTEORG.DATAFINE IS NULL OR BOR_UTEORG.DATAFINE > :DATAFINE_gt)";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY BOR_UTEORG.CODUTE, BOR_UTEORG.DATAINIZ";
        }

        return $sql;
    }

    public function leggiBorUteorg($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorUteorg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorUteorgChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDUTEORG'] = $cod;
        return self::getSqlLeggiBorUteorg($filtri, true, $sqlParams);
    }

    public function getSqlleggiBorOrgdel($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_ORGDEL.* FROM BOR_ORGDEL";
        $where = " WHERE";

        if (isSet($filtri['TIPODELIB']) && trim($filtri['TIPODELIB']) != '') {
            $this->addSqlParam($sqlParams, "TIPODELIB", strtoupper(trim($filtri['TIPODELIB'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('TIPODELIB') . " = :TIPODELIB";
            $where = " AND";
        }
        if (isSet($filtri['TIPODELIB_KEY'])) {
            $this->addSqlParam($sqlParams, "TIPODELIB_KEY", strtoupper(trim($filtri['TIPODELIB_KEY'])), PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('TIPODELIB') . " = :TIPODELIB_KEY";
            $where = " AND";
        }

        if (isSet($filtri['DES_ORDE']) && trim($filtri['DES_ORDE']) != '') {
            $this->addSqlParam($sqlParams, "DES_ORDE", '%' . strtoupper(trim($filtri['DES_ORDE'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('DES_ORDE') . " LIKE :DES_ORDE";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_NALS']) && trim($filtri['FLAG_NALS']) != '') {
            $this->addSqlParam($sqlParams, "FLAG_NALS", intval($filtri['FLAG_NALS']), PDO::PARAM_INT);
            $sql .= $where . " FLAG_NALS = :FLAG_NALS";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_NALR']) && trim($filtri['FLAG_NALR']) != '') {
            $this->addSqlParam($sqlParams, "FLAG_NALR", intval($filtri['FLAG_NALR']), PDO::PARAM_INT);
            $sql .= $where . " FLAG_NALR = :FLAG_NALR";
            $where = " AND";
        }
        if (isSet($filtri['COD_NR_DS']) && trim($filtri['COD_NR_DS']) != '') {
            $this->addSqlParam($sqlParams, "COD_NR_DS", '%' . strtoupper(trim($filtri['COD_NR_DS'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('COD_NR_DS') . " LIKE :COD_NR_DS";
            $where = " AND";
        }
        if (isSet($filtri['COD_NR_DS']) && trim($filtri['COD_NR_DS']) != '') {
            $this->addSqlParam($sqlParams, "COD_NR_DS", '%' . strtoupper(trim($filtri['COD_NR_DS'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('COD_NR_DS') . " LIKE :COD_NR_DS";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_TPORG']) && trim($filtri['FLAG_TPORG']) != '') {
            $this->addSqlParam($sqlParams, "FLAG_TPORG", intval($filtri['FLAG_TPORG']), PDO::PARAM_INT);
            $sql .= $where . " FLAG_TPORG = :FLAG_TPORG";
            $where = " AND";
        }
        if (isSet($filtri['CODUTE']) && trim($filtri['CODUTE']) != '') {
            $this->addSqlParam($sqlParams, "CODUTE", '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('CODUTE') . " LIKE :CODUTE";
            $where = " AND";
        }
        if (isSet($filtri['FLAG_DIS']) && trim($filtri['FLAG_DIS']) != '') {
            $this->addSqlParam($sqlParams, "FLAG_DIS", intval($filtri['FLAG_DIS']), PDO::PARAM_INT);
            $sql .= $where . " FLAG_DIS = :FLAG_DIS";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY TIPODELIB";
        }

        return $sql;
    }

    public function leggiBorOrgdel($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorOrgdel($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function leggiFirmaUtente($codute = null) {
        $sqlParams = array();
        $sql = "SELECT BOR_UTEFIR.CODUTE, " .
                $this->getCitywareDB()->adapterBlob("IMMAGINE") .
                " FROM BOR_UTEFIR WHERE CODUTE=:CODUTE";

        if (!isSet($codute)) {
            $codute = strtoupper(trim(cwbParGen::getUtente()));
        } else {
            $codute = strtoupper(trim($codute));
        }
        $this->addSqlParam($sqlParams, "CODUTE", $codute, PDO::PARAM_STR);

        $infoBinaryCallback = $this->addBinaryInfoCallback("cwbLibDB_BGE", "leggiImmagineBgeImages");
        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, $infoBinaryCallback);
        if (!$result) {
            return false;
        }
        return cwbLibOmnis::fromOmnisPicture($result['IMMAGINE']);
    }

    public function leggiFirmaUtenteBorUtefir($result = array()) {
        $sql = 'SELECT BOR_UTEFIR.IMMAGINE FROM BOR_UTEFIR WHERE CODUTE=:CODUTE';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "CODUTE", $result['CODUTE'], PDO::PARAM_STR);

        $sqlFields = array();
        $this->addBinaryFieldsDescribe($sqlFields, "IMMAGINE", 1);
        $resultBin = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams, null, $sqlFields);
        $result['IMMAGINE'] = $resultBin['IMMAGINE'];

        return $result;
    }

    /**
     * Legge servizio emittente per i Servizi Sociali
     * @param int $servizioEmittente Codice servizio emittente (IDBOL_SERE)
     * @return array Dati se esito positivo, altrimenti false
     */
    public function leggiServizioEmittenteServSociali($servizioEmittente) {
        $sql = 'SELECT  bor_idbol.*, s.SETT_IVAPY ,s.TDOC_K_PY 
                FROM bor_idbol 
                LEFT JOIN bor_idbols s on s.IDBOL_SERE = bor_idbol.IDBOL_SERE
                WHERE bor_idbol.IDBOL_SERE=:IDBOL_SERE';
        $sqlParams = array();
        $this->addSqlParam($sqlParams, "IDBOL_SERE", $servizioEmittente, PDO::PARAM_INT);
        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        if (!$result) {
            return false;
        }

        return $result;
    }

    // BOR_RUOLI

    /**
     * Restituisce comando sql per lettura tabella BOR_RUOLI
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
//    public function getSqlLeggiBorRuoli($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
//        $sql = "SELECT BOR_RUOLI.* FROM BOR_RUOLI";
//
//        $where = ' WHERE';
//
//        if (!empty($filtri['KRUOLO'])) {
//            $this->addSqlParam($sqlParams, 'KRUOLO', $filtri['KRUOLO'], PDO::PARAM_INT);
//            $sql .= " $where KRUOLO=:KRUOLO";
//            $where = 'AND';
//        }
//        if (array_key_exists('DES_RUOLO', $filtri) && $filtri['DES_RUOLO'] != null) {
//            $this->addSqlParam($sqlParams, "DES_RUOLO", '%' . strtoupper(trim($filtri['DES_RUOLO'])) . "%", PDO::PARAM_STR);
//            $sql .= " $where " . $this->getCitywareDB()->strUpper("DES_RUOLO") . " LIKE :DES_RUOLO";
//            $where = 'AND';
//        }
//        if (array_key_exists('ALIAS', $filtri) && $filtri['ALIAS'] != null) {
//            $this->addSqlParam($sqlParams, "ALIAS", strtoupper(trim($filtri['ALIAS'])), PDO::PARAM_STR);
//            $sql .= " $where ALIAS=:ALIAS";
//            $where = 'AND';
//        }
//        if (array_key_exists('COD_UFF', $filtri) && $filtri['COD_UFF'] != null) {
//            $this->addSqlParam($sqlParams, "COD_UFF", strtoupper(trim($filtri['COD_UFF'])), PDO::PARAM_STR);
//            $sql .= " $where COD_UFF=:COD_UFF";
//            $where = 'AND';
//        }
//        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] >= 0) {
//            $this->addSqlParam($sqlParams, 'PROGENTE', $filtri['PROGENTE'], PDO::PARAM_INT);
//            $sql .= " $where PROGENTE=:PROGENTE";
//            $where = 'AND';
//        }
//        if (isSet($filtri['FLAG_DIS']) && trim($filtri['FLAG_DIS']) != '') {
//            $this->addSqlParam($sqlParams, "FLAG_DIS", intval($filtri['FLAG_DIS']), PDO::PARAM_INT);
//            $sql .= $where . " FLAG_DIS = :FLAG_DIS";
//            $where = " AND";
//        }
//
//        $sql .= $excludeOrderBy ? '' : ' ORDER BY KRUOLO';
//        return $sql;
//    }
//
//    public function leggiBorRuoli($filtri = array(), $multipla = true) {
//        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorRuoli($filtri, false, $sqlParams), $multipla, $sqlParams);
//    }
//
//    public function getSqlLeggiBorRuoliChiave($cod, &$sqlParams) {
//        $filtri = array();
//        $filtri['KRUOLO'] = $cod;
//        return self::getSqlLeggiBorRuoli($filtri, true, $sqlParams);
//    }
//
//    public function leggiBorRuoliChiave($cod) {
//        if (!$cod) {
//            return null;
//        }
//        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorRuoliChiave($cod, $sqlParams), false, $sqlParams);
//    }
    // BOR_AUTUTE
    public function getSqlleggiBorAutute($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                BOR_AUTUTE.* 
                FROM BOR_AUTUTE";
        $where = " WHERE";

        if (isSet($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, "CODUTE", trim($filtri['CODUTE']), PDO::PARAM_STR);
            $sql .= $where . " BOR_AUTUTE.CODUTE = :CODUTE";
            $where = " AND";
        }
        if (isSet($filtri['CODMODULO'])) {
            $this->addSqlParam($sqlParams, "CODMODULO", trim($filtri['CODMODULO']), PDO::PARAM_STR);
            $sql .= $where . " BOR_AUTUTE.CODMODULO = :CODMODULO";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY CODUTE";
        }

        return $sql;
    }

    public function leggiBorAutute($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorAutute($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function countBorAutute($codute = '', $area = '') {
        $sql = "SELECT COUNT(*) FROM BOR_AUTUTE ";

        $where = 'WHERE';

        if (strlen($codute)) {
            $sql .= "$where CODUTE = '$codute' ";
            $where = 'AND';
        }

        if (strlen($area)) {
            $sql .= "$where CODMODULO LIKE '$area%' ";
            $where = 'AND';
        }

        $sql .= "$where (";
        for ($i = 1; $i <= 100; $i++) {
            $prog = str_pad($i, 3, '0', STR_PAD_LEFT);
            if ($i > 1) {
                $sql .= $where;
            }
            $sql .= " AUTUTE_$prog <> '' ";
            $where = 'OR';
        }
        $sql .= ')';

        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);

        return reset($result);
    }

    public function getSqlleggiDistinctCoduteBorAutute($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT CODUTE AS CUSTOMKEY  
                FROM BOR_AUTUTE";

        $where = " WHERE";

        if (isSet($filtri['CODUTE'])) {
            $this->addSqlParam($sqlParams, 'CODUTE', '%' . strtoupper(trim($filtri['CODUTE'])) . '%', PDO::PARAM_STR);
            $sql .= " $where BOR_AUTUTE.CODUTE LIKE :CODUTE";
            $where = " AND";
        }
        if (isSet($filtri['CODUTE_KEY'])) {
            $this->addSqlParam($sqlParams, "CODUTE", trim($filtri['CODUTE_KEY']), PDO::PARAM_STR);
            $sql .= $where . " BOR_AUTUTE.CODUTE = :CODUTE";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY CODUTE";
        }

        return $sql;
    }

    public function leggiDistinctCoduteBorAutute($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiDistinctCoduteBorAutute($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlleggiDistinctKruoloBorAutruo($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT BOR_AUTRUO.KRUOLO AS CUSTOMKEY, BOR_RUOLI.DES_RUOLO 
                FROM BOR_AUTRUO";

        $sql .= " LEFT JOIN BOR_RUOLI ON BOR_RUOLI.KRUOLO = BOR_AUTRUO.KRUOLO ";

        $where = " WHERE";

        if (isSet($filtri['KRUOLO'])) {
            $this->addSqlParam($sqlParams, "KRUOLO", trim($filtri['KRUOLO']), PDO::PARAM_INT);
            $sql .= $where . " BOR_AUTRUO.KRUOLO = :KRUOLO";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY BOR_AUTRUO.KRUOLO";
        }

        return $sql;
    }

    public function leggiDistinctKruoloBorAutruo($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiDistinctKruoloBorAutruo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function countBorAutoruo($kruolo = 0, $area = '') {
        $sql = "SELECT COUNT(*) FROM BOR_AUTRUO ";

        $where = 'WHERE';

        if ($kruolo > 0) {
            $sql .= "$where KRUOLO = $kruolo ";
            $where = 'AND';
        }

        if (strlen($area)) {
            $sql .= "$where CODMODULO LIKE '$area%' ";
            $where = 'AND';
        }

        $sql .= "$where (";
        for ($i = 1; $i <= 100; $i++) {
            $prog = str_pad($i, 3, '0', STR_PAD_LEFT);
            if ($i > 1) {
                $sql .= $where;
            }
            $sql .= " AUTUTE_$prog <> '' ";
            $where = 'OR';
        }
        $sql .= ')';

        $result = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);

        return reset($result);
    }

    // BOR_RUOLI
    public function getSqlleggiBorRuoli($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                BOR_RUOLI.* 
                FROM BOR_RUOLI";
        $where = " WHERE";

        if (!empty($filtri['KRUOLO'])) {
            $this->addSqlParam($sqlParams, "KRUOLO", trim($filtri['KRUOLO']), PDO::PARAM_INT);
            $sql .= $where . " BOR_RUOLI.KRUOLO = :KRUOLO";
            $where = " AND";
        }
        if (isSet($filtri['DES_RUOLO']) && trim($filtri['DES_RUOLO']) != '') {
            $this->addSqlParam($sqlParams, "DES_RUOLO", '%' . strtoupper(trim($filtri['DES_RUOLO'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_RUOLI.DES_RUOLO') . " LIKE :DES_RUOLO";
            $where = " AND";
        }
        if (isSet($filtri['ALIAS']) && trim($filtri['ALIAS']) != '') {
            $this->addSqlParam($sqlParams, "ALIAS", '%' . strtoupper(trim($filtri['ALIAS'])) . '%', PDO::PARAM_STR);
            $sql .= $where . " " . $this->getCitywareDB()->strUpper('BOR_RUOLI.ALIAS') . " LIKE :ALIAS";
            $where = " AND";
        }
//        if ($filtri['DIRIGENTE'] !== null && $filtri['DIRIGENTE'] >= 0) {
//            $this->addSqlParam($sqlParams, 'DIRIGENTE', $filtri['DIRIGENTE'], PDO::PARAM_INT);
//            $sql .= " $where BOR_RUOLI.DIRIGENTE=:DIRIGENTE";
//            $where = 'AND';
//        }
        if (isSet($filtri['DIRIGENTE']) && trim($filtri['DIRIGENTE']) != '') {
            $this->addSqlParam($sqlParams, 'DIRIGENTE', intval($filtri['DIRIGENTE']), PDO::PARAM_INT);
            $sql .= " $where BOR_RUOLI.DIRIGENTE=:DIRIGENTE";
            $where = " AND";
        }
        if (array_key_exists('KRUOLO_IN', $filtri) && count($filtri['KRUOLO_IN']) > 0) {
            $sql .= " $where KRUOLO IN ('" . implode("','", $filtri['KRUOLO_IN']) . "')";
            $where = 'AND';
        }
        if (isSet($filtri['FLAG_DIS']) && trim($filtri['FLAG_DIS']) != '') {
            $this->addSqlParam($sqlParams, "FLAG_DIS", intval($filtri['FLAG_DIS']), PDO::PARAM_INT);
            $sql .= $where . " FLAG_DIS = :FLAG_DIS";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY KRUOLO";
        }

        return $sql;
    }

    public function leggiBorRuoli($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorRuoli($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorRuoliChiave($cod, &$sqlParams) {
        $filtri = array();
        $filtri['KRUOLO'] = $cod;
        return self::getSqlLeggiBorRuoli($filtri, true, $sqlParams);
    }

    public function leggiBorRuoliChiave($cod) {
        if (strlen(trim($cod)) === 0) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorRuoliChiave($cod, $sqlParams), false, $sqlParams);
    }

    // BOR_AUTRUO
    public function getSqlleggiBorAutruo($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                BOR_AUTRUO.* 
                FROM BOR_AUTRUO";
        $where = " WHERE";

        if (isSet($filtri['KRUOLO'])) {
            $this->addSqlParam($sqlParams, "KRUOLO", trim($filtri['KRUOLO']), PDO::PARAM_STR);
            $sql .= $where . " BOR_AUTRUO.KRUOLO = :KRUOLO";
            $where = " AND";
        }
        if (isSet($filtri['CODMODULO'])) {
            $this->addSqlParam($sqlParams, "CODMODULO", trim($filtri['CODMODULO']), PDO::PARAM_STR);
            $sql .= $where . " BOR_AUTRUO.CODMODULO = :CODMODULO";
            $where = " AND";
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY KRUOLO";
        }

        return $sql;
    }

    public function leggiBorAutruo($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorAutruo($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function getSqlleggiBorRuoliFromUteorg($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT DISTINCT
                    BOR_RUOLI.* 
                FROM BOR_UTEORG
                JOIN BOR_RUOLI ON BOR_UTEORG.KRUOLO = BOR_RUOLI.KRUOLO";
        $where = $this->setDefaultFilters('BOR_UTEORG', $filtri, $sqlParams);

        if(!empty($where)){
            $sql .= ' WHERE ' . $where;
        }

        if (!$excludeOrderBy) {
            $sql .= " ORDER BY KRUOLO";
        }

        return $sql;
    }

    public function leggiBorRuoliFromUteorg($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorRuoliFromUteorg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
	
    // BOR_ORGANRICBI

    /**
     * Restituisce comando sql per lettura tabella BOR_ORGAN
     * @param array $filtri Filtri di ricerca
     * @param boolean $excludeOrderBy Se true, non aggiunge ORDER BY alla SELECT
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder
     * @return string Comando sql
     */
    public function getSqlLeggiBorOrganRicbi($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG, '' AS STAMPA FROM BOR_ORGAN JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG WHERE L1ORG <> :L1ORGv";

        $this->addSqlParam($sqlParams, "L1ORGv", "00", PDO::PARAM_STR);
        $where = 'AND';

        if (array_key_exists('DESPORG', $filtri) && $filtri['DESPORG'] != null) {
            $this->addSqlParam($sqlParams, "DESPORG", "%" . strtoupper(trim($filtri['DESPORG'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG") . " LIKE :DESPORG";
            $where = 'AND';
        }
        if (array_key_exists('DESPORG_B', $filtri) && $filtri['DESPORG_B'] != null) {
            $this->addSqlParam($sqlParams, "DESPORG_B", "%" . strtoupper(trim($filtri['DESPORG_B'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.DESPORG_B") . " LIKE :DESPORG_B";
            $where = 'AND';
        }
        if (array_key_exists('ALIAS', $filtri) && $filtri['ALIAS'] != null) {
            $this->addSqlParam($sqlParams, "ALIAS", "%" . strtoupper(trim($filtri['ALIAS'])) . "%", PDO::PARAM_STR);
            $sql .= " $where " . $this->getCitywareDB()->strUpper("BOR_ORGAN.ALIAS") . " LIKE :ALIAS";
            $where = 'AND';
        }
        if (array_key_exists('IDORGAN', $filtri) && $filtri['IDORGAN'] != null) {
            $this->addSqlParam($sqlParams, "IDORGAN", $filtri['IDORGAN'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.IDORGAN=:IDORGAN";
            $where = 'AND';
        }
        if (array_key_exists('IDBORAOO', $filtri) && $filtri['IDBORAOO'] != null) {
            $this->addSqlParam($sqlParams, "IDBORAOO", $filtri['IDBORAOO'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.IDBORAOO=:IDBORAOO";
            $where = 'AND';
        }
        if (array_key_exists('L1ORG', $filtri) && $filtri['L1ORG'] != null) {
            $this->addSqlParam($sqlParams, "L1ORG", $filtri['L1ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L1ORG=:L1ORG";
            $where = 'AND';
        }
        if (array_key_exists('L2ORG', $filtri) && $filtri['L2ORG'] != null) {
            $this->addSqlParam($sqlParams, "L2ORG", $filtri['L2ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L2ORG=:L2ORG";
            $where = 'AND';
        }
        if (array_key_exists('L3ORG', $filtri) && $filtri['L3ORG'] != null) {
            $this->addSqlParam($sqlParams, "L3ORG", $filtri['L3ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L3ORG=:L3ORG";
            $where = 'AND';
        }
        if (array_key_exists('L4ORG', $filtri) && $filtri['L4ORG'] != null) {
            $this->addSqlParam($sqlParams, "L4ORG", $filtri['L4ORG'], PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.L4ORG=:L4ORG";
            $where = 'AND';
        }
        if (array_key_exists('PROGENTE', $filtri) && $filtri['PROGENTE'] != null) {
            $this->addSqlParam($sqlParams, "PROGENTE", $filtri['PROGENTE'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.PROGENTE=:PROGENTE";
            $where = 'AND';
        }
        if (array_key_exists('PROGINT', $filtri) && $filtri['PROGINT'] != null) {
            $this->addSqlParam($sqlParams, "PROGINT", $filtri['PROGINT'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.PROGINT=:PROGINT";
            $where = 'AND';
        }
        if (array_key_exists('ID_MODORG', $filtri) && $filtri['ID_MODORG'] != null) {
            $this->addSqlParam($sqlParams, "ID_MODORG", $filtri['ID_MODORG'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.ID_MODORG=:ID_MODORG";
            $where = 'AND';
        }
        if (array_key_exists('NODO', $filtri) && $filtri['NODO'] != null) {
            $this->addSqlParam($sqlParams, "NODO", $filtri['NODO'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.NODO=:NODO";
            $where = 'AND';
        }
        if (array_key_exists('FLAG_RICBI', $filtri) && $filtri['FLAG_RICBI'] != null && $filtri['FLAG_RICBI'] != -1) {
            $this->addSqlParam($sqlParams, "FLAG_RICBI", $filtri['FLAG_RICBI'], PDO::PARAM_INT);
            $sql .= " $where BOR_ORGAN.FLAG_RICBI=:FLAG_RICBI";
            $where = 'AND';
        }
        if (isSet($filtri['ATTIVO']) && $filtri['ATTIVO'] === true) {
            $this->addSqlParam($sqlParams, "DATAINIZ_lt", date('Y-m-d'), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE_gt", date('Y-m-d'), PDO::PARAM_STR);
            $sql .= " $where BOR_ORGAN.DATAINIZ<=:DATAINIZ_lt AND (BOR_ORGAN.DATAFINE>=:DATAFINE_gt OR BOR_ORGAN.DATAFINE IS NULL)";
            $where = 'AND';
        }
        if (isSet($filtri['ATTIVO']) && $filtri['ATTIVO'] === false) {
            $this->addSqlParam($sqlParams, "DATAINIZ_lt", date('Y-m-d'), PDO::PARAM_STR);
            $this->addSqlParam($sqlParams, "DATAFINE_gt", date('Y-m-d'), PDO::PARAM_STR);
            $sql .= " $where (BOR_ORGAN.DATAINIZ>:DATAINIZ_lt OR BOR_ORGAN.DATAFINE<:DATAFINE_gt)";
            $where = 'AND';
        }
        if (isSet($filtri['LXORG_in_childs']) && is_array($filtri['LXORG_in_childs'])) {
            if (isSet($filtri['LXORG_in_childs']['L1ORG'])) {
                $filtri = array($filtri);
            }
            $i = 1;
            foreach ($filtri['LXORG_in_childs'] as $row) {
                $l1org = str_pad(ltrim($row['L1ORG'], " 0"));
                $l1org = (!empty($l1org) && $l1org != "00" ? $l1org : null);
                $l2org = str_pad(ltrim($row['L2ORG'], " 0"));
                $l2org = (!empty($l2org) && $l2org != "00" ? $l2org : null);
                $l3org = str_pad(ltrim($row['L3ORG'], " 0"));
                $l3org = (!empty($l3org) && $l3org != "00" ? $l3org : null);
                $l4org = str_pad(ltrim($row['L4ORG'], " 0"));
                $l4org = (!empty($l4org) && $l4org != "00" ? $l4org : null);

                if (!empty($l1org)) {
                    if ($i == 1) {
                        $sql .= " $where (";
                    } else {
                        $sql .= " OR ";
                    }

                    $this->addSqlParam($sqlParams, "L1ORG_in_" . $i, $l1org, PDO::PARAM_STR);
                    $sql .= " (BOR_ORGAN.L1ORG = :L1ORG_in_" . $i;

                    if (!empty($l2org)) {
                        $this->addSqlParam($sqlParams, "L2ORG_in_" . $i, $l2org, PDO::PARAM_STR);
                        $sql .= " AND BOR_ORGAN.L2ORG = :L2ORG_in_" . $i;

                        if (!empty($l3org)) {
                            $this->addSqlParam($sqlParams, "L3ORG_in_" . $i, $l3org, PDO::PARAM_STR);
                            $sql .= " AND BOR_ORGAN.L3ORG = :L3ORG_in_" . $i;

                            if (!empty($l4org)) {
                                $this->addSqlParam($sqlParams, "L4ORG_in_" . $i, $l4org, PDO::PARAM_STR);
                                $sql .= " AND BOR_ORGAN.L4ORG = :L4ORG_in_" . $i;
                            }
                        }
                    }
                    $sql .= ")";
                    $i++;
                }
            }
            if ($i > 1) {
                $sql .= ")";
                $where = "AND";
            }
        }
        if (isSet($filtri['AUTH']) && !$filtri['AUTH'] !== true) {
            if ($filtri['AUTH'] === false) {
                $sql .= " $where 1=0";
                $where = 'AND';
            } elseif ($filtri['AUTH'] !== true && !empty($filtri['AUTH'])) {
                $sql .= " $where (";
                $where = '';

                for ($i = 0; $i < count($filtri['AUTH']); $i++) {
                    $sql .= " $where (BOR_ORGAN.L1ORG = :L1ORGAUTH$i";
                    $this->addSqlParam($sqlParams, ":L1ORGAUTH$i", $filtri['AUTH'][$i]['L1ORG'], PDO::PARAM_STR);

                    if (!empty($filtri['AUTH'][$i]['L2ORG'])) {
                        $sql .= " AND BOR_ORGAN.L2ORG = :L2ORGAUTH$i";
                        $this->addSqlParam($sqlParams, ":L2ORGAUTH$i", $filtri['AUTH'][$i]['L2ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH'][$i]['L3ORG'])) {
                        $sql .= " AND BOR_ORGAN.L3ORG = :L3ORGAUTH$i";
                        $this->addSqlParam($sqlParams, ":L3ORGAUTH$i", $filtri['AUTH'][$i]['L3ORG'], PDO::PARAM_STR);
                    }
                    if (!empty($filtri['AUTH'][$i]['L4ORG'])) {
                        $sql .= " AND BOR_ORGAN.L4ORG = :L4ORGAUTH$i";
                        $this->addSqlParam($sqlParams, ":L4ORGAUTH$i", $filtri['AUTH'][$i]['L4ORG'], PDO::PARAM_STR);
                    }
                    $sql .= ")";
                    $where = 'OR';
                }
                $sql .= ")";
                $where = 'AND';
            }
        }
        $sql .= $excludeOrderBy ? '' : ' ORDER BY BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG';
        return $sql;
    }

    /**
     * Restituisce dati tabella BOR_ORGAN
     * @param array $filtri Filtri di ricerca     
     * @return object Resultset
     */
    public function leggiBorOrganRicbi($filtri = array(), $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrganRicbi($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Restituisce comando sql per lettura per chiave da tabella BOR_ORGAN
     * @param string $cod Chiave 
     * @param array $sqlParams valorizzare array con i parametri con cui sostituire i placeholder    
     * @return string Comando sql
     */
    public function getSqlLeggiBorOrganRicbiChiave($cod, &$sqlParams = array()) {
        $filtri = array();
        $filtri['IDORGAN'] = $cod;
        return self::getSqlLeggiBorOrganRicbi($filtri, true, $sqlParams);
    }

    /**
     * Restituisce dati tabella BOR_ORGAN per chiave
     * @param array $cod Chiave   
     * @return object Record
     */
    public function leggiBorOrganRicbiChiave($cod) {
        if (!$cod) {
            return null;
        }
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrganRicbiChiave($cod, $sqlParams), false, $sqlParams);
    }

    /**
     * Legge nodi di primo livello di BOR_ORGAN
     * @param int $progente Progressivo ente
     * @param int $idmodorg ID Modello organizzativo
     * @return array Lista nodi primo livello
     */
    public function leggiBorOrganRicbiNodiPrimoLivello($progente = null, $idmodorg = null, $flag_ricbi = null) {
        if (isSet($idmodorg) && trim($idmodorg) == '') {
            $idmodorg = null;
        }

        $sqlParams = array();
        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG, '' AS STAMPA
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE NODO = '1'";
        if (isSet($progente)) {
            $sql .= " AND BOR_ORGAN.PROGENTE=:PROGENTE";
            $this->addSqlParam($sqlParams, "PROGENTE", $progente, PDO::PARAM_INT);
        }
        if (isSet($idmodorg)) {
            $sql .= " AND BOR_ORGAN.ID_MODORG=:ID_MODORG";
            $this->addSqlParam($sqlParams, "ID_MODORG", $idmodorg, PDO::PARAM_INT);
        }
//        if (isSet($flag_ricbi) && $flag_ricbi != -1) {
//            $sql .= " AND BOR_ORGAN.FLAG_RICBI=:FLAG_RICBI";
//            $this->addSqlParam($slqParams, "FLAG_RICBI", $flag_ricbi, PDO::PARAM_INT);
//        }
        $sql .= " ORDER BY L1ORG, L2ORG, L3ORG, L4ORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    /**
     * Legge nodi figli di BOR_ORGAN rispetto al nodo padre
     * @param int $idNodo ID Nodo Padre
     * @return array Lista nodi
     */
    public function leggiBorOrganRicbiFigli($idNodo) {

        // Legge record padre
        $padre = $this->leggiBorOrganRicbiChiave($idNodo);

        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGENTE", $padre['PROGENTE'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ID_MODORG", $padre['ID_MODORG'], PDO::PARAM_INT);
        // Identifica condizione di ricerca dei figli
        switch (trim($padre['NODO'])) {
            case '1':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $condRicFigli = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG<>'00' AND BOR_ORGAN.L3ORG='00'";
                break;
            case '2':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $padre['L2ORG'], PDO::PARAM_STR);
                $condRicFigli = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG<>'00' AND BOR_ORGAN.L4ORG='00'";
                break;
            case '3':
                $this->addSqlParam($sqlParams, "L1ORG", $padre['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $padre['L2ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L3ORG", $padre['L3ORG'], PDO::PARAM_STR);
                $condRicFigli = "L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG=:L3ORG AND BOR_ORGAN.L4ORG<>'00'";
                break;
            case '4':
                return null;
        }

        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG, '' AS STAMPA
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE 
                    $condRicFigli AND BOR_ORGAN.PROGENTE=:PROGENTE AND BOR_ORGAN.ID_MODORG=:ID_MODORG
                ORDER BY
                    BOR_ORGAN.L1ORG, BOR_ORGAN.L2ORG, BOR_ORGAN.L3ORG, BOR_ORGAN.L4ORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, true, $sqlParams);
    }

    /**
     * Legge record padre
     * @param mixed $nodo ID nodo o array nodo
     * @return array Record padre
     */
    public function leggiBorOrganRicbiPadre($nodo) {

        // Legge record nodo
        if (!is_array($nodo)) {
            $nodo = $this->leggiBorOrganRicbiChiave($nodo);
        }

        $sqlParams = array();
        $this->addSqlParam($sqlParams, "PROGENTE", $nodo['PROGENTE'], PDO::PARAM_INT);
        $this->addSqlParam($sqlParams, "ID_MODORG", $nodo['ID_MODORG'], PDO::PARAM_INT);

        // Identifica condizione di ricerca dei figli
        switch (trim($nodo['NODO'])) {
            case '2':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG='00'";
                break;
            case '3':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $nodo['L2ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG='00'";
                break;
            case '4':
                $this->addSqlParam($sqlParams, "L1ORG", $nodo['L1ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L2ORG", $nodo['L2ORG'], PDO::PARAM_STR);
                $this->addSqlParam($sqlParams, "L3ORG", $nodo['L3ORG'], PDO::PARAM_STR);
                $condRicPadre = "BOR_ORGAN.L1ORG=:L1ORG AND BOR_ORGAN.L2ORG=:L2ORG AND BOR_ORGAN.L3ORG=:L3ORG AND BOR_ORGAN.L4ORG='00'";
                break;
        }

        $sql = "SELECT BOR_ORGAN.*, BOR_MODORG.DESCRIZ as MODORG, '' AS STAMPA
                FROM BOR_ORGAN
                JOIN BOR_MODORG ON BOR_ORGAN.ID_MODORG = BOR_MODORG.IDMODORG
                WHERE 
                    $condRicPadre AND BOR_ORGAN.PROGENTE=:PROGENTE AND BOR_ORGAN.ID_MODORG=:ID_MODORG";

        return ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
    }

    public function getSqlLeggiBorOrganRicbiAlias($filtri = null, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT ALIAS FROM BOR_ORGAN";

        if (isSet($filtri['PROGENTE'])) {
            $sql .= " WHERE PROGENTE = :PROGENTE";
            $this->addSqlParam($sqlParams, "PROGENTE", strtoupper(trim($filtri['PROGENTE'])), PDO::PARAM_INT);
        }

        $sql .= " GROUP BY ALIAS";

        if ($excludeOrderBy === false) {
            $sql .= " ORDER BY ALIAS";
        }
        return $sql;
    }

    /**
     * Restitusce gli alias presenti
     * @param array $filtri (accetta PROGENTE per definire l'ente)
     * @param boolean $multipla
     * @return array
     */
    public function leggiBorOrganRicbiAlias($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorOrganRicbiAlias($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    /**
     * Legge livello BOR_ORGAN
     * @param int $id ID Nodo
     * @return int Livello
     */
    public function getLivelloBorOrganRicbi($id) {
        $padre = $this->leggiBorOrganRicbiChiave($id);

        return intval(trim($padre['NODO']));
    }

    public function checkAnnoContabile($codute) {
        $sqlParams = array();
        $sql = "SELECT
                    CASE BOR_UTENTI.FLAG_GESAUT
                        WHEN 0 THEN BGE_APPLI.MODO_GESAUT
                        WHEN 1 THEN 0
                        WHEN 2 THEN 1
                    END MODO_GESAUT,
                    BOR_AUTUTE.CODUTE,
                    MAX(BOR_AUTRUO.KRUOLO) KRUOLO
                FROM BOR_ORGAN
                JOIN BGE_APPLI ON BGE_APPLI.APPLI_KEY = 'AA'
                JOIN BOR_UTEORG ON BOR_ORGAN.IDORGAN = BOR_UTEORG.IDORGAN
                JOIN BOR_UTENTI ON BOR_UTEORG.CODUTE = BOR_UTENTI.CODUTE
                LEFT JOIN BOR_AUTUTE ON BOR_UTEORG.CODUTE = BOR_AUTUTE.CODUTE
                                    AND BOR_AUTUTE.CODMODULO = 'FBI'
                                    AND (BOR_AUTUTE.AUTUTE_004 IN ('G', 'C') OR BOR_AUTUTE.AUTUTE_005 IN ('G', 'C'))
                LEFT JOIN BOR_AUTRUO ON BOR_UTEORG.KRUOLO = BOR_AUTRUO.KRUOLO
                                    AND BOR_AUTRUO.CODMODULO = 'FBI'
                                    AND (BOR_AUTRUO.AUTUTE_004 IN ('G', 'C') OR BOR_AUTRUO.AUTUTE_005 IN ('G', 'C'))
                WHERE BGE_APPLI.APPLI_KEY = 'AA'
                    AND BOR_ORGAN.FLAG_RICBI = 0
                    AND BOR_UTENTI.CODUTE = :CODUTE
                GROUP BY BOR_UTENTI.FLAG_GESAUT, BGE_APPLI.MODO_GESAUT, BOR_AUTUTE.CODUTE";
        $this->addSqlParam($sqlParams, "CODUTE", strtoupper(trim($codute)), PDO::PARAM_STR);

        $data = ItaDB::DBQuery($this->getCitywareDB(), $sql, false, $sqlParams);
        if (($data['MODO_GESAUT'] == 0 && !empty($data['CODUTE'])) ||
                ($data['MODO_GESAUT'] == 1 && !empty($data['KRUOLO']))) {
            return true;
        }
        return false;
    }

    public function getSqlleggiBorUtentiFromUteorg($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    BOR_UTENTI.*
                FROM BOR_UTENTI
                JOIN BOR_UTEORG ON BOR_UTENTI.CODUTE = BOR_UTEORG.CODUTE";
        
        $where = $this->setDefaultFilters('BOR_UTEORG', $filtri, $sqlParams);
        if(!empty($where)){
            $sql .= ' WHERE ' . $where;
        }
        
        return $sql;
    }

    public function leggiBorUtentiFromUteorg($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorUtentiFromUteorg($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlleggiBorUtentiInOrgan($filtri = array(), $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    COUNT(*) CNT
                FROM BOR_UTENTI
                LEFT JOIN BOR_RESPO ON BOR_UTENTI.CODUTE = BOR_RESPO.CODUTE_RESP
                LEFT JOIN BOR_UTEORG ON BOR_UTENTI.CODUTE = BOR_UTEORG.CODUTE
                WHERE (BOR_RESPO.CODUTE_RESP IS NOT NULL OR BOR_UTEORG.CODUTE IS NOT NULL)";
        
        $where = $this->setDefaultFilters('BOR_UTENTI', $filtri, $sqlParams);
        if(!empty($where)){
            $sql .= ' AND ' . $where;
        }
        
        return $sql;
    }

    public function leggiBorUtentiInOrgan($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlleggiBorUtentiInOrgan($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorAututeFromDesaut($filtri = null, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    A.*,
                    BOR_AUTUTE.AUTUTE_001, BOR_AUTUTE.AUTUTE_002, BOR_AUTUTE.AUTUTE_003, BOR_AUTUTE.AUTUTE_004, BOR_AUTUTE.AUTUTE_005,
                    BOR_AUTUTE.AUTUTE_006, BOR_AUTUTE.AUTUTE_007, BOR_AUTUTE.AUTUTE_008, BOR_AUTUTE.AUTUTE_009, BOR_AUTUTE.AUTUTE_010,
                    BOR_AUTUTE.AUTUTE_011, BOR_AUTUTE.AUTUTE_012, BOR_AUTUTE.AUTUTE_013, BOR_AUTUTE.AUTUTE_014, BOR_AUTUTE.AUTUTE_015,
                    BOR_AUTUTE.AUTUTE_016, BOR_AUTUTE.AUTUTE_017, BOR_AUTUTE.AUTUTE_018, BOR_AUTUTE.AUTUTE_019, BOR_AUTUTE.AUTUTE_020,
                    BOR_AUTUTE.AUTUTE_021, BOR_AUTUTE.AUTUTE_022, BOR_AUTUTE.AUTUTE_023, BOR_AUTUTE.AUTUTE_024, BOR_AUTUTE.AUTUTE_025,
                    BOR_AUTUTE.AUTUTE_026, BOR_AUTUTE.AUTUTE_027, BOR_AUTUTE.AUTUTE_028, BOR_AUTUTE.AUTUTE_029, BOR_AUTUTE.AUTUTE_030,
                    BOR_AUTUTE.AUTUTE_031, BOR_AUTUTE.AUTUTE_032, BOR_AUTUTE.AUTUTE_033, BOR_AUTUTE.AUTUTE_034, BOR_AUTUTE.AUTUTE_035,
                    BOR_AUTUTE.AUTUTE_036, BOR_AUTUTE.AUTUTE_037, BOR_AUTUTE.AUTUTE_038, BOR_AUTUTE.AUTUTE_039, BOR_AUTUTE.AUTUTE_040,
                    BOR_AUTUTE.AUTUTE_041, BOR_AUTUTE.AUTUTE_042, BOR_AUTUTE.AUTUTE_043, BOR_AUTUTE.AUTUTE_044, BOR_AUTUTE.AUTUTE_045,
                    BOR_AUTUTE.AUTUTE_046, BOR_AUTUTE.AUTUTE_047, BOR_AUTUTE.AUTUTE_048, BOR_AUTUTE.AUTUTE_049, BOR_AUTUTE.AUTUTE_050,
                    BOR_AUTUTE.AUTUTE_051, BOR_AUTUTE.AUTUTE_052, BOR_AUTUTE.AUTUTE_053, BOR_AUTUTE.AUTUTE_054, BOR_AUTUTE.AUTUTE_055,
                    BOR_AUTUTE.AUTUTE_056, BOR_AUTUTE.AUTUTE_057, BOR_AUTUTE.AUTUTE_058, BOR_AUTUTE.AUTUTE_059, BOR_AUTUTE.AUTUTE_060,
                    BOR_AUTUTE.AUTUTE_061, BOR_AUTUTE.AUTUTE_062, BOR_AUTUTE.AUTUTE_063, BOR_AUTUTE.AUTUTE_064, BOR_AUTUTE.AUTUTE_065,
                    BOR_AUTUTE.AUTUTE_066, BOR_AUTUTE.AUTUTE_067, BOR_AUTUTE.AUTUTE_068, BOR_AUTUTE.AUTUTE_069, BOR_AUTUTE.AUTUTE_070,
                    BOR_AUTUTE.AUTUTE_071, BOR_AUTUTE.AUTUTE_072, BOR_AUTUTE.AUTUTE_073, BOR_AUTUTE.AUTUTE_074, BOR_AUTUTE.AUTUTE_075,
                    BOR_AUTUTE.AUTUTE_076, BOR_AUTUTE.AUTUTE_077, BOR_AUTUTE.AUTUTE_078, BOR_AUTUTE.AUTUTE_079, BOR_AUTUTE.AUTUTE_080,
                    BOR_AUTUTE.AUTUTE_081, BOR_AUTUTE.AUTUTE_082, BOR_AUTUTE.AUTUTE_083, BOR_AUTUTE.AUTUTE_084, BOR_AUTUTE.AUTUTE_085,
                    BOR_AUTUTE.AUTUTE_086, BOR_AUTUTE.AUTUTE_087, BOR_AUTUTE.AUTUTE_088, BOR_AUTUTE.AUTUTE_089, BOR_AUTUTE.AUTUTE_090,
                    BOR_AUTUTE.AUTUTE_091, BOR_AUTUTE.AUTUTE_092, BOR_AUTUTE.AUTUTE_093, BOR_AUTUTE.AUTUTE_094, BOR_AUTUTE.AUTUTE_095,
                    BOR_AUTUTE.AUTUTE_096, BOR_AUTUTE.AUTUTE_097, BOR_AUTUTE.AUTUTE_098, BOR_AUTUTE.AUTUTE_099, BOR_AUTUTE.AUTUTE_100
                FROM (  SELECT
                            CODMODULO,
                            MAX(PROGAUT) MAXPROG
                        FROM BOR_DESAUT
                        GROUP BY CODMODULO) A
                LEFT JOIN BOR_AUTUTE ON A.CODMODULO = BOR_AUTUTE.CODMODULO";
        
        $where = $this->setDefaultFilters('BOR_AUTUTE', $filtri, $sqlParams);
        if(!empty($where)){
            $sql .= " AND " . $where;
        }
        
        if ($excludeOrderBy === false) {
            $sql .= " ORDER BY A.CODMODULO";
        }
        return $sql;
    }

    /**
     * Restitusce gli alias presenti
     * @param array $filtri (accetta PROGENTE per definire l'ente)
     * @param boolean $multipla
     * @return array
     */
    public function leggiBorAututeFromDesaut($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorAututeFromDesaut($filtri, false, $sqlParams), $multipla, $sqlParams);
    }

    public function getSqlLeggiBorAutruoFromDesaut($filtri = null, $excludeOrderBy = false, &$sqlParams = array()) {
        $sql = "SELECT
                    A.*,
                    BOR_AUTRUO.KRUOLO,
                    BOR_AUTRUO.AUTUTE_001, BOR_AUTRUO.AUTUTE_002, BOR_AUTRUO.AUTUTE_003, BOR_AUTRUO.AUTUTE_004, BOR_AUTRUO.AUTUTE_005,
                    BOR_AUTRUO.AUTUTE_006, BOR_AUTRUO.AUTUTE_007, BOR_AUTRUO.AUTUTE_008, BOR_AUTRUO.AUTUTE_009, BOR_AUTRUO.AUTUTE_010,
                    BOR_AUTRUO.AUTUTE_011, BOR_AUTRUO.AUTUTE_012, BOR_AUTRUO.AUTUTE_013, BOR_AUTRUO.AUTUTE_014, BOR_AUTRUO.AUTUTE_015,
                    BOR_AUTRUO.AUTUTE_016, BOR_AUTRUO.AUTUTE_017, BOR_AUTRUO.AUTUTE_018, BOR_AUTRUO.AUTUTE_019, BOR_AUTRUO.AUTUTE_020,
                    BOR_AUTRUO.AUTUTE_021, BOR_AUTRUO.AUTUTE_022, BOR_AUTRUO.AUTUTE_023, BOR_AUTRUO.AUTUTE_024, BOR_AUTRUO.AUTUTE_025,
                    BOR_AUTRUO.AUTUTE_026, BOR_AUTRUO.AUTUTE_027, BOR_AUTRUO.AUTUTE_028, BOR_AUTRUO.AUTUTE_029, BOR_AUTRUO.AUTUTE_030,
                    BOR_AUTRUO.AUTUTE_031, BOR_AUTRUO.AUTUTE_032, BOR_AUTRUO.AUTUTE_033, BOR_AUTRUO.AUTUTE_034, BOR_AUTRUO.AUTUTE_035,
                    BOR_AUTRUO.AUTUTE_036, BOR_AUTRUO.AUTUTE_037, BOR_AUTRUO.AUTUTE_038, BOR_AUTRUO.AUTUTE_039, BOR_AUTRUO.AUTUTE_040,
                    BOR_AUTRUO.AUTUTE_041, BOR_AUTRUO.AUTUTE_042, BOR_AUTRUO.AUTUTE_043, BOR_AUTRUO.AUTUTE_044, BOR_AUTRUO.AUTUTE_045,
                    BOR_AUTRUO.AUTUTE_046, BOR_AUTRUO.AUTUTE_047, BOR_AUTRUO.AUTUTE_048, BOR_AUTRUO.AUTUTE_049, BOR_AUTRUO.AUTUTE_050,
                    BOR_AUTRUO.AUTUTE_051, BOR_AUTRUO.AUTUTE_052, BOR_AUTRUO.AUTUTE_053, BOR_AUTRUO.AUTUTE_054, BOR_AUTRUO.AUTUTE_055,
                    BOR_AUTRUO.AUTUTE_056, BOR_AUTRUO.AUTUTE_057, BOR_AUTRUO.AUTUTE_058, BOR_AUTRUO.AUTUTE_059, BOR_AUTRUO.AUTUTE_060,
                    BOR_AUTRUO.AUTUTE_061, BOR_AUTRUO.AUTUTE_062, BOR_AUTRUO.AUTUTE_063, BOR_AUTRUO.AUTUTE_064, BOR_AUTRUO.AUTUTE_065,
                    BOR_AUTRUO.AUTUTE_066, BOR_AUTRUO.AUTUTE_067, BOR_AUTRUO.AUTUTE_068, BOR_AUTRUO.AUTUTE_069, BOR_AUTRUO.AUTUTE_070,
                    BOR_AUTRUO.AUTUTE_071, BOR_AUTRUO.AUTUTE_072, BOR_AUTRUO.AUTUTE_073, BOR_AUTRUO.AUTUTE_074, BOR_AUTRUO.AUTUTE_075,
                    BOR_AUTRUO.AUTUTE_076, BOR_AUTRUO.AUTUTE_077, BOR_AUTRUO.AUTUTE_078, BOR_AUTRUO.AUTUTE_079, BOR_AUTRUO.AUTUTE_080,
                    BOR_AUTRUO.AUTUTE_081, BOR_AUTRUO.AUTUTE_082, BOR_AUTRUO.AUTUTE_083, BOR_AUTRUO.AUTUTE_084, BOR_AUTRUO.AUTUTE_085,
                    BOR_AUTRUO.AUTUTE_086, BOR_AUTRUO.AUTUTE_087, BOR_AUTRUO.AUTUTE_088, BOR_AUTRUO.AUTUTE_089, BOR_AUTRUO.AUTUTE_090,
                    BOR_AUTRUO.AUTUTE_091, BOR_AUTRUO.AUTUTE_092, BOR_AUTRUO.AUTUTE_093, BOR_AUTRUO.AUTUTE_094, BOR_AUTRUO.AUTUTE_095,
                    BOR_AUTRUO.AUTUTE_096, BOR_AUTRUO.AUTUTE_097, BOR_AUTRUO.AUTUTE_098, BOR_AUTRUO.AUTUTE_099, BOR_AUTRUO.AUTUTE_100
                FROM (  SELECT
                            CODMODULO,
                            MAX(PROGAUT) MAXPROG
                        FROM BOR_DESAUT
                        GROUP BY CODMODULO) A
                LEFT JOIN BOR_AUTRUO ON A.CODMODULO = BOR_AUTRUO.CODMODULO";
        
        $where = $this->setDefaultFilters('BOR_AUTRUO', $filtri, $sqlParams);
        if(!empty($where)){
            $sql .= " AND " . $where;
        }
        
        if ($excludeOrderBy === false) {
            $sql .= " ORDER BY A.CODMODULO";
        }
        return $sql;
    }

    /**
     * Restitusce gli alias presenti
     * @param array $filtri (accetta PROGENTE per definire l'ente)
     * @param boolean $multipla
     * @return array
     */
    public function leggiBorAutruoFromDesaut($filtri = null, $multipla = true) {
        return ItaDB::DBQuery($this->getCitywareDB(), $this->getSqlLeggiBorAutruoFromDesaut($filtri, false, $sqlParams), $multipla, $sqlParams);
    }
    
    public function leggiModoGesaut($filtri=null, $multipla=true){
        $sql = "SELECT
                    CASE
                        WHEN BOR_UTENTI.FLAG_GESAUT = 0 THEN BGE_APPLI.MODO_GESAUT
                        ELSE BOR_UTENTI.FLAG_GESAUT - 1
                    END AS MODO_GESAUT
                FROM BOR_UTENTI
                JOIN BGE_APPLI ON BGE_APPLI.APPLI_KEY = 'AA'";
        
        $sqlParams = array();
        $where = $this->setDefaultFilters('BOR_UTENTI', $filtri, $sqlParams);
        
        if(!empty($where)){
            $sql .= " WHERE " . $where;
        }
        
        return ItaDB::DBQuery($this->getCitywareDB(), $sql, $multipla, $sqlParams);
    }
}

?>
