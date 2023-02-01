<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';
/**
 * Description of cwbAuthFilters
 *
 * @author f.margiotta
 */
class cwbAuthFilters {
    const MODULO        = 'MODULO';
    const NUMERO        = 'NUMERO';
    const OPERATOR      = 'OPERATOR';
    const RELATIVES     = 'RELATIVES';
    const FORCE_MIN     = 'FORCE_MIN';
    const FORCE_MAX     = 'FORCE_MAX';
    const REQUIRED      = 'REQUIRED';
    const REQUIRE_MIN   = 'REQUIRE_MIN';
    
    const FIELD_L1ORG   = 'L1ORG';
    const FIELD_L2ORG   = 'L2ORG';
    const FIELD_L3ORG   = 'L3ORG';
    const FIELD_L4ORG   = 'L4ORG';
    const FIELD_IDORGAN = 'IDORGAN';
    
    const PARAM_GET_EXACT   = 0;
    const PARAM_GET_PARENTS = 1;
    const PARAM_GET_CHILDREN= 2;
    
    const SOURCE_BASE = 0;
    const SOURCE_BILANCIO = 1;
    const SOURCE_RICBIL = 2;
    const SOURCE_CICLOATT = 3;
    const SOURCE_CICLOPASS = 4;
    const SOURCE_CONTOCLI = 5;
    const SOURCE_CONTOFOR = 6;
    
    const BOOL_OR       = 0;
    const BOOL_AND      = 1;
    
    private $levels;
    private $context;
    private $user;
    private $fields;
    private $fieldsPage;
    private $fieldsElenca;
    private $fieldsDettaglio;
    private $operator;
    private $table;
    private $libDB;
    private $authHelper;
    
    public function __construct() {
        $this->setLevelDecode(array(
            ' '=>0,
            'L'=>1,
            'G'=>2,
            'C'=>3
        ));
        $this->setContext(self::SOURCE_BASE);
        $this->setFiltersBoolOperator();
        $this->libDB = new cwbLibDB_GENERIC();
        $this->authHelper = new cwfAuthHelper();
    }
    
    /**
     * Specifica se i permessi fra le varie strutture organizzative vanno messi in OR (default) o in AND
     * @param <int> $operator costante di tipo cwbAuthFilters::BOOL_*
     */
    public function setFiltersBoolOperator($operator = self::BOOL_OR){
        $this->operator = $operator;
    }
    
    /**
     * Nome della tabella principale su cui applicare i filtri se viene omessa la tabella nel filtro
     * @param <string> $table
     */
    public function setFiltersTable($table){
        $this->table = $table;
    }
    
    public function getFiltersTable(){
        return $this->table;
    }
    
    /**
     * Imposta l'utente di riferimento (codute)
     * @param <string> $user
     */
    public function setUser($user){
        $this->user = strtoupper(trim($user));
    }
    
    public function getUser(){
        return $this->user;
    }
    
    /**
     * Imposta il contesto di riferimento (bilancio, richieste bilancio, etc)
     * @param <int> $context costante di tipo cwbAuthFilters::SOURCE_*
     */
    public function setContext($context){
        $this->context = $context;
    }
    
    public function getContext(){
        return $this->context;
    }
    
    /**
     * Imposta i filtri generici
     * @param <array> $fields ha una struttura ricorsiva in cui ogni sottolivello è un ulteriore gruppo
     *      array(
     *          array(
     *              cwbAuthFilters::MODULO => 'FES',               //Modulo su cui fare il controllo
     *              cwbAuthFilters::NUMERO => 10,                  //Numero su cui fare il controllo
     *              cwbAuthFilters::FIELD_IDORGAN => 'IDORGAN_AS', //Indica il campo IDORGAN su cui fare la query/confronto. Alterantivamente si può usare FIELD_L*ORG
     *              cwbAuthFilters::RELATIVES => cwbAuthFilters::PARAM_GET_EXACT //Indica se accettare anche i genitori o i figli di una struttura organizzativa (solo se impostata con FIELD_L*ORG)
     *          ),
     *          array(
     *              cwbAuthFilters::OPERATOR => self::BOOL_OR,     //Opzionale, indica se questo blocco va in AND o in OR con il precedente
     *              array(
     *                  cwbAuthFilters::MODULO => 'FES',
     *                  cwbAuthFilters::NUMERO => 5,
     *                  cwbAuthFilters::FIELD_IDORGAN => 'IDORGAN_RS',
     *                  cwbAuthFilters::FORCE_MIN => 'L'           //Opzionale, impone il livello minimo considerabile da quanto riportato sulle autorizzazioni
     *              ),
     *              array(
     *                  cwbAuthFilters::MODULO => 'FES',
     *                  cwbAuthFilters::NUMERO => 6,
     *                  cwbAuthFilters::FIELD_IDORGAN => 'IDORGAN_RS'
     *                  cwbAuthFilters::FORCE_MAX => 'G'           //Opzionale, impone il livello massimo considerabile da quanto riportato sulle autorizzazioni
     *              )
     *          )
     *      )
     *      L'esempio riportato sopra fa il controllo delle autorizzazioni sulla struttura assegnata nel campo IDORGAN_AS controllando
     *      FES-10 OPPURE (IDORGAN_RS FES-5 e FES-6). Per FES-5 viene imposto il livello minimo a L (quindi se ' ' => 'L') e per
     *      FES 6 viene imposto il livello massimo a G (quindi se 'C' => 'G')
     *                  
     */
    public function setFields($fields){
        $this->fields = $fields;
    }
    
    public function getFields(){
        return $this->fields;
    }
    
    /**
     * Filtri specifici per l'apertura della pagina, sfrutta la stessa struttura di setFields (ma vengono ignorati i riferimenti di tipo FIELD_*)
     * @param <array> $fields
     */
    public function setFieldsPage($fields){
        $this->fieldsPage = $fields;
    }
    
    public function getFieldsPage(){
        return $this->fieldsPage;
    }
    
    /**
     * Filtri specifici per l'elenca, sfrutta la stessa struttura di setFields
     * @param type $fields
     */
    public function setFieldsElenca($fields){
        $this->fieldsElenca = $fields;
    }
    
    public function getFieldsElenca(){
        return $this->fieldsElenca;
    }
    
    /**
     * Filtri specifici per il dettaglio, sfrutta la stessa struttura di setFields
     * @param type $fields
     */
    public function setFieldsDettaglio($fields){
        $this->fieldsDettaglio = $fields;
    }
    
    public function getFieldsDettaglio(){
        return $this->fieldsDettaglio;
    }
    
    /**
     * Imposta una gerarchia di valori per le autorizzazioni, di default è settato come:
     *      array(
     *          ' '=>0,
     *          'L'=>1,
     *          'G'=>2,
     *          'C'=>3
     *      )
     * @param <array> $levels
     */
    public function setLevelDecode($levels){
        $this->levels = $levels;
    }
    
    public function getLevelDecode(){
        return $this->levels;
    }
    
    /**
     * Restituisce il livello di autorizzazione della pagina (settato da setLevelDecode)
     * @return <string|boolean>
     */
    public function getPageLevel(){
        $fields = $this->fieldsPage;
        if(empty($fields)){
            $fields = $this->fields;
        }
        if(empty($fields)){
            return false;
        }
        
        $auth = $this->authHelper->getMixedAuth($this->user, $this->context);
        $auth = (isSet($auth['GLOBAL']) ? $auth['GLOBAL'] : array_shift($auth['ORGAN']));
        
        $return = $this->recursiveFieldsToLevel($auth, $fields);
        return ($return == ' ' ? false : $return);
    }
    
    /**
     * Restituisce un array da usare con setDefaultFilters per costruire i filtri da usare nell'elenca.
     * @return boolean|string
     */
    public function buildFiltersElencaArray(){
        $fields = $this->fieldsElenca;
        if(empty($fields)){
            $fields = $this->fields;
        }
        if(empty($fields)){
            return true;
        }
        
        $auth = $this->authHelper->getMixedAuth($this->user, $this->context);
        if(isSet($auth['GLOBAL'])){
            if(empty($auth['GLOBAL']) || $this->recursiveFieldsToLevel($auth['GLOBAL'], $fields) == ' '){
                return false;
            }
            return true;
        }
        
        $organ = array_values($auth['ORGAN']);
        if(empty($organ)){
            return false;
        }

        $filterRoot = array();
        for($i=0; $i<count($organ); $i++){
            $key = 'GROUP(Organ_'.$i.')';
            if($this->operator === self::BOOL_OR && $i>0){
                $key .= '_or';
            }
            $filterRoot[$key] = $this->recursiveFieldsToFiltersElenca($organ[$i], $fields);
            if($filterRoot[$key] === false){
                $filterRoot[$key] = array('RAWCONDITION('.md5(microtime()).')'=>'0=1');
            }
        }
        
        return empty($filterRoot) ? false : $filterRoot;
    }
    
    /**
     * Aggiunge il filtro delle strutture organizzative con visibilità >= L ad una query
     * @param <string> $sql query di base
     * @param <array> $sqlParams array dei parametri
     * @return <boolean>
     */
    public function buildFiltersElenca(&$sql, &$sqlParams=array()){
        $filterRoot = $this->buildFiltersElencaArray();
        
        if($filterRoot === true){
            return true;
        }
        if($filterRoot === false){
            $sql = preg_replace('/^(.*?)WHERE(?!.*WHERE)(.*?)$/s', '$1 WHERE 1=0 AND $2', $sql);
            return false;
        }

        $filters = $this->libDB->setDefaultFilters($this->table, $filterRoot, $sqlParams);
        $sql = preg_replace('/^(.*?)WHERE(?!.*WHERE)(.*?)$/s', '$1 WHERE ('.$filters.') AND $2', $sql);
        return true;
    }
    
    /**
     * Restituisce il livello di autorizzazione per un dettaglio (settato da setLevelDecode)
     * @return <string|boolean>
     */
    public function getDettaglioLevel($row){
        $fields = $this->fieldsDettaglio;
        if(empty($fields)){
            $fields = $this->fields;
        }
        if(empty($fields)){
            return false;
        }
        
        $auth = $this->authHelper->getMixedAuth($this->user, $this->context);
        if(isSet($auth['GLOBAL'])){
            if(!empty($auth['GLOBAL'])){
                $organ = array($auth['GLOBAL']);
                unset($organ[0]['IDORGAN']);
                unset($organ[0]['L1ORG']);
                unset($organ[0]['L2ORG']);
                unset($organ[0]['L3ORG']);
                unset($organ[0]['L4ORG']);
            }
            else{
                return false;
            }
        }
        else{
            $organ = array_values($auth['ORGAN']);
        }
        
        if($this->operator === self::BOOL_OR){
            $level = min($this->levels);
        }
        else{
            $level = max($this->levels);
        }
        for($i=0; $i<count($organ); $i++){
            if($this->operator === self::BOOL_OR){
                $level = max(array($level, $this->levels[$this->recursiveFieldsToLevelDettaglio($organ[$i], $fields, $row) ?: ' ']));
                if($level == max($this->levels)){
                    break;
                }
            }
            else{
                $level = min(array($level, $this->levels[$this->recursiveFieldsToLevelDettaglio($organ[$i], $fields, $row) ?: ' ']));
                if($level == min($this->levels)){
                    break;
                }
            }
        }
        $return = array_search($level, $this->levels);

        return ($return == ' ' ? false : $return);
    }
    
    private function recursiveFieldsToLevel($auth, $fields){
        $return = ' ';
        if(isSet($fields[self::MODULO])){
            $return = $auth[$fields[self::MODULO]][$fields[self::NUMERO]] ?: ' ';
            if(isSet($fields[self::FORCE_MIN]) || isSet($fields[self::FORCE_MAX])){
                $return = $this->levels[$return];
                if(isSet($fields[self::FORCE_MAX])){
                    $return = min(array($return, $this->levels[$fields[self::FORCE_MAX]]));
                }
                if(isSet($fields[self::FORCE_MIN])){
                    $return = max(array($return, $this->levels[$fields[self::FORCE_MIN]]));
                }
                $return = array_search($return, $this->levels);
            }
            if(isSet($fields[self::REQUIRE_MIN]) && $this->levels[$fields[self::REQUIRE_MIN]] > $this->levels[$return]){
                $return = ' ';
            }
            if(isSet($fields[self::REQUIRED]) && $fields[self::REQUIRED] === true && $return == ' '){
                $return = false;
            }
        }
        else{
            $return = null;
            foreach($fields as $field){
                if(is_array($field)){
                    $level = $this->recursiveFieldsToLevel($auth, $field);
                    if($level === false){
                        return false;
                    }
                    elseif(!isSet($return)){
                        $return = $this->levels[$level];
                    }
                    elseif(isSet($field[self::OPERATOR]) && $field[self::OPERATOR] === self::BOOL_OR){
                        $return = max(array($return, $this->levels[$level]));
                    }
                    else{
                        $return = min(array($return, $this->levels[$level]));
                    }
                }
            }
            
            $return = $return === false ? false : array_search($return, $this->levels);
        }
        
        return $return;
    }
    
    private function recursiveFieldsToFiltersElenca($organ, $fields){
        $return = array();
        if(isSet($fields[self::MODULO])){
            $livello = $organ[$fields[self::MODULO]][$fields[self::NUMERO]] ?: ' ';
            if(isSet($fields[self::REQUIRE_MIN]) && $this->levels[$fields[self::REQUIRE_MIN]] > $this->levels[$livello]){
                $livello = ' ';
            }
            
            if($livello == ' '){
                if(isSet($fields[self::REQUIRED]) && $fields[self::REQUIRED] === true){
                    $return = false;
                }
                else{
                    $key = 'RAWCONDITION('.md5(microtime()).')';
                    if(isSet($fields[self::OPERATOR]) && $fields[self::OPERATOR] === self::BOOL_OR){
                        $key .= '_or';
                    }
                    $return[$key] = '1=0';
                }
            }
            else{
                $key = 'GROUP(G'.md5(microtime()).')';
                if(isSet($fields[self::OPERATOR]) && $fields[self::OPERATOR] === self::BOOL_OR){
                    $key .= '_or';
                }
                $return[$key] = array();
                if(isSet($fields[self::FIELD_IDORGAN])){
                    $return[$key][$fields[self::FIELD_IDORGAN]] = $organ[self::FIELD_IDORGAN];
                }
                if(isSet($fields[self::FIELD_L1ORG])){
                    $return[$key][$fields[self::FIELD_L1ORG]] = $organ[self::FIELD_L1ORG];
                }
                if(isSet($fields[self::FIELD_L2ORG])){
                    if(!isSet($fields[self::RELATIVES]) || $fields[self::RELATIVES] === self::PARAM_GET_EXACT ||
                            ($fields[self::RELATIVES] === self::PARAM_GET_CHILDREN && $organ[self::FIELD_L2ORG] != '00')){
                        $return[$key][$fields[self::FIELD_L2ORG]] = $organ[self::FIELD_L2ORG];
                    }
                }
                if(isSet($fields[self::FIELD_L3ORG])){
                    if(!isSet($fields[self::RELATIVES]) || $fields[self::RELATIVES] === self::PARAM_GET_EXACT ||
                            ($fields[self::RELATIVES] === self::PARAM_GET_CHILDREN && $organ[self::FIELD_L3ORG] != '00')){
                        $return[$key][$fields[self::FIELD_L3ORG]] = $organ[self::FIELD_L3ORG];
                    }
                }
                if(isSet($fields[self::FIELD_L4ORG])){
                    if(!isSet($fields[self::RELATIVES]) || $fields[self::RELATIVES] === self::PARAM_GET_EXACT ||
                            ($fields[self::RELATIVES] === self::PARAM_GET_CHILDREN && $organ[self::FIELD_L4ORG] != '00')){
                        $return[$key][$fields[self::FIELD_L4ORG]] = $organ[self::FIELD_L4ORG];
                    }
                }
            }
        }
        else{
            $key = 'GROUP(G'.md5(microtime()).')';
            if(isSet($fields[self::OPERATOR]) && $fields[self::OPERATOR] === self::BOOL_OR){
                $key .= '_or';
            }
            $return[$key] = array();
            
            foreach($fields as $field){
                if(is_array($field)){
                    $group = $this->recursiveFieldsToFiltersElenca($organ, $field);
                    if($group === false){
                        return false;
                    }
                    else{
                        $return[$key] = array_merge($return[$key], $group);
                    }
                }
            }
        }
        
        return $return;
    }
    
    private function recursiveFieldsToLevelDettaglio($organ, $fields, $row){
        $return = ' ';
        if(isSet($fields[self::MODULO])){
            if(!isSet($organ[self::FIELD_IDORGAN])){
                $return = $organ[$fields[self::MODULO]][$fields[self::NUMERO]] ?: ' ';
            }
            elseif(isSet($fields[self::FIELD_IDORGAN])){
                if($row[$fields[self::FIELD_IDORGAN]] == $organ[self::FIELD_IDORGAN]){
                    $return = $organ[$fields[self::MODULO]][$fields[self::NUMERO]] ?: ' ';
                }
            }
            else{
                if($row[$fields[self::FIELD_L1ORG]] == $organ[self::FIELD_L1ORG] && (
                        $organ[self::RELATIVES] == self::PARAM_GET_PARENTS ||
                        
                        ($fields[self::RELATIVES] == self::PARAM_GET_EXACT && $row[$fields[self::FIELD_L2ORG]] == $organ[self::FIELD_L2ORG] &&
                         $row[$fields[self::FIELD_L3ORG]] == $organ[self::FIELD_L3ORG] && $row[$fields[self::FIELD_L4ORG]] == $organ[self::FIELD_L4ORG]) ||
                        
                        ($fields[self::RELATIVES] == self::PARAM_GET_CHILDREN &&
                            ($row[$fields[self::FIELD_L2ORG]] == $organ[self::FIELD_L2ORG] || $organ[self::FIELD_L2ORG] == '00') &&
                            ($row[$fields[self::FIELD_L3ORG]] == $organ[self::FIELD_L3ORG] || $organ[self::FIELD_L3ORG] == '00') &&
                            ($row[$fields[self::FIELD_L4ORG]] == $organ[self::FIELD_L4ORG] || $organ[self::FIELD_L4ORG] == '00'))
                    )
                  ){
                    $return = $organ[$fields[self::MODULO]][$fields[self::NUMERO]] ?: ' ';
                }
            }
            
            if(isSet($fields[self::FORCE_MIN]) || isSet($fields[self::FORCE_MAX])){
                $return = $this->levels[$return];
                if(isSet($fields[self::FORCE_MAX])){
                    $return = min(array($return, $this->levels[$fields[self::FORCE_MAX]]));
                }
                if(isSet($fields[self::FORCE_MIN])){
                    $return = max(array($return, $this->levels[$fields[self::FORCE_MIN]]));
                }
                $return = array_search($return, $this->levels);
            }
            if(isSet($fields[self::REQUIRE_MIN]) && $this->levels[$fields[self::REQUIRE_MIN]] > $this->levels[$return]){
                $return = ' ';
            }
            if(isSet($fields[self::REQUIRED]) && $fields[self::REQUIRED] === true && $return == ' '){
                $return = false;
            }
        }
        else{
            $return = null;
            foreach($fields as $field){
                if(is_array($field)){
                    $level = $this->recursiveFieldsToLevelDettaglio($organ, $field, $row);
                    if($level === false){
                        return false;
                    }
                    elseif(!isSet($return)){
                        $return = $this->levels[$level];
                    }
                    elseif(isSet($field[self::OPERATOR]) && $field[self::OPERATOR] === self::BOOL_OR){
                        $return = max(array($return, $this->levels[$level]));
                    }
                    else{
                        $return = min(array($return, $this->levels[$level]));
                    }
                }
            }
            
            $return = $return === false ? false : array_search($return, $this->levels);
        }
        
        return $return;
    }
}
