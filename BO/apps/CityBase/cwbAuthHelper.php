<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

class cwbAuthHelper {
    const GESAUT_UTENTE = 0;
    const GESAUT_RUOLO = 1;
    
    protected $codute;
    protected $libDB;
    
    public function __construct($codute=null) {
        $this->libDB = new cwbLibDB_BOR();
        if(isSet($codute)){
            $codute = strtoupper(trim($codute));
            $filtri = array('CODUTE'=>$codute);
            if(!$this->libDB->leggiGeneric('BOR_UTENTI', $filtri, false)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'L\'utente '.$codute.' non esiste.');
            }
            $this->codute = $codute;
        }
        else{
            $this->codute = strtoupper(trim(cwbParGen::getUtente()));
        }
        
    }
    
    /**
     * Restituisce il livello di autorizzazione di un dato utente su un dato modulo
     * @param string $codute Opzionale. CODUTE dell'utente di cui verificare le autorizzazioni
     * @param string $modulo Modulo di da verificare (es: BOR, FES, BTA..)
     * @param int $numero Opzionale. Numero di cui verificare l'autorizzazione. Se non specificato verrà tornato un array con tutte le autorizzazioni del modulo
     * @return boolean|string|array Se l'utente non dispone dell'autorizzazione viene ritornato FALSE
     *                              Sennò viene ritornato il valore dell'autorizzazione (Es. C, G, P)
     *                              Se non specificato il numero viene ritornato un array con valori da 1 a 100 con le relative autorizzazioni.
     * @throws ItaException
     */
    public function checkAuthAutute($codute=null, $modulo, $numero=null){
        $return = $this->getAuthUtente($codute, $modulo, $numero, false);
        if(isSet($return['GLOBAL'])){
            $return = $return['GLOBAL'];
        }

        return $return;
    }
    
    /**
     * Il metodo restituisce le autorizzazioni prese da BOR_AUTUTE/BOR_AUTRUO relative ad un singolo utente
     * @param <string> $codute CODUTE dell'utente di cui si vogliono recuperare le autorizzazioni. Se non passato viene usato quello inserito nel costruttore
     * @param <string> $modulo se specificato restituisce solo i dati globali relativi ad un modulo
     * @param <string> $numero se specificato restituisce solo i dati globali relativi ad un numero (richiede modulo, altrimenti viene ignorato)
     * @param <boolean> $forceReload se si passa true non viene letta l'autorizzazione dalla cache ma viene ricaricata da DB
     * @return <array>  array(
     *                      'TYPE'=>cwbAuthHelper::GESAUT_*         Costante che indica se la lettura è avvenuta per ruolo o per utente
     *                      'GLOBAL' => array(                      Array che indica l'autorizzazione globale (valore più alto preso fra tutti i ruoli)
     *                                      'BOR'=> array(
     *                                                  1 => 'C',
     *                                                  2 => 'L',
     *                                                  ....
     *                                              ),
     *                                      'FES'=> array(
     *                                                  1 => 'G',
     *                                                  2 => 'C',
     *                                                  3 => false,
     *                                                  ...
     *                                              ),
     *                                      ....
     *                                  ),
     *                      'ORGAN' =>  array(
     *                                      '01010000'=>array(
     *                                                      'IDORGAN'=>123,
     *                                                      'L1ORG'=>'01',
     *                                                      'L2ORG'=>'01',
     *                                                      'L3ORG'=>'00',
     *                                                      'L4ORG'=>'00',
     *                                                      'KRUOLO'=>5,
     *                                                      'BOR'=> array(
     *                                                                  1 => 'C',
     *                                                                  2 => 'L',
     *                                                                  ....
     *                                                              ),
     *                                                      'FES'=> array(
     *                                                                  1 => 'G',
     *                                                                  2 => 'C',
     *                                                                  3 => false,
     *                                                                  ...
     *                                                              ),
     *                                                  )
     *                                  )
     *                  )
     * @throws ItaException
     */
    public function getAuthUtente($codute=null, $modulo=null, $numero=null, $forceReload=false){
        if(isSet($codute)){
            $codute = strtoupper(trim($codute));
        }
        else{
            $codute = $this->codute;
        }
        
        $filtri = array('CODUTE'=>$codute);
        $modo_gesaut = $this->libDB->leggiModoGesaut($filtri, false);
        if(empty($modo_gesaut)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'L\'utente '.$codute.' non esiste sul database di Cityware.');
        }
        
        $cache = CacheFactory::newCache();
        $cacheKey = 'GESAUT_' . $codute;
        
        if(!$forceReload){
            $gesaut = $cache->get($cacheKey);
        }
        
        if(empty($gesaut)){
            $gesaut = array(
                'TYPE'=>intval($modo_gesaut['MODO_GESAUT']),
                'CODUTE'=>$codute,
                'GLOBAL'=>array()
            );
                        
            if($modo_gesaut['MODO_GESAUT'] == self::GESAUT_UTENTE){
                        
                $filtri = array('CODUTE'=>$codute);
                $auth = $this->libDB->leggiBorAututeFromDesaut($filtri);
                
                foreach($auth as $authRow){
                    if(!isSet($gesaut['GLOBAL'][$authRow['CODMODULO']])){
                        $gesaut['GLOBAL'][$authRow['CODMODULO']] = array();
                    }
                    
                    for($i=1; $i<=$authRow['MAXPROG']; $i++){
                        $authKey = 'AUTUTE_'.str_pad($i, 3, '0', STR_PAD_LEFT);
                        $gesaut['GLOBAL'][$authRow['CODMODULO']][$i] = $authRow[$authKey] ?: false;
                    }
                }
            }
            else{
                $gesaut['ORGAN'] = array();
                
                $filtri = array(
                    'CODUTE'=>$codute,
                    'DATAINIZ_lt_eq'=>date('Y-m-d'),
                    'DATAFINE_gt_eq'=>date('Y-m-d'),
                    'DATAFINE_or_null'=>true
                );
                $uteorg = $this->libDB->leggiGeneric('BOR_UTEORG', $filtri);
                foreach($uteorg as $uteorgRow){
                    $k = $uteorgRow['L1ORG'].$uteorgRow['L2ORG'].$uteorgRow['L3ORG'].$uteorgRow['L4ORG'];
                    
                    $gesaut['ORGAN'][$k] = array(
                        'IDORGAN'=>$uteorgRow['IDORGAN'],
                        'L1ORG'=>$uteorgRow['L1ORG'],
                        'L2ORG'=>$uteorgRow['L2ORG'],
                        'L3ORG'=>$uteorgRow['L3ORG'],
                        'L4ORG'=>$uteorgRow['L4ORG'],
                        'KRUOLO'=>$uteorgRow['KRUOLO']
                    );

                    $filtri = array(
                        'KRUOLO'=>$uteorgRow['KRUOLO']
                    );
                    $auth = $this->libDB->leggiBorAutruoFromDesaut($filtri);
                    
                    foreach($auth as $authRow){
                        if(!isSet($gesaut['ORGAN'][$k][$authRow['CODMODULO']])){
                            $gesaut['ORGAN'][$k][$authRow['CODMODULO']] = array();
                        }
                        
                        for($i=1; $i<=$authRow['MAXPROG']; $i++){
                            $authKey = 'AUTUTE_'.str_pad($i, 3, '0', STR_PAD_LEFT);
                            $gesaut['ORGAN'][$k][$authRow['CODMODULO']][$i] = $authRow[$authKey] ?: false;
                        }
                    }
                    
                    if(count($uteorg) == 1 || $uteorgRow['KRUOLO'] == cwbParGen::getRuolo()){
                        $gesaut['GLOBAL'] = $gesaut['ORGAN'][$k];
                    }
                }
            }
        }
        
        $cache->set($cacheKey, $gesaut, 24*60*60);
        
        if(!empty($modulo)){
            $gesaut = $gesaut['GLOBAL'][$modulo];
            if(!empty($numero)){
                $gesaut = $gesaut[$numero];
            }
        }
        
        return $gesaut;
    }
    
    public function clearAuthCache($codute=null){
        $cache = CacheFactory::newCache();
        
        $filtri = array();
        if(!empty($codute)){
            $filtri['CODUTE'] = $codute;
        }
        $utenti = $this->libDB->leggiGeneric('BOR_UTENTI', $filtri, true, 'CODUTE');
        foreach($utenti as $utente){
            $cache->delete('GESAUT_' . $utente['CODUTE']);
        }
    }
    
    public function checkAutorLevelModulo($codute=null, $codmodulo, $numautor, $numautor2=null, $numautor3=null, &$autor=null, &$rowAutor=null){
        if(isSet($codute)){
            $codute = strtoupper(trim($codute));
        }
        else{
            $codute = $this->codute;
        }
        
        $auth = $this->checkAuthAutute($codute, $codmodulo);
        $rowAutor = array(
            'CODUTE'=>$codute,
            'CODMODULO'=>$codmodulo,
            'DATAOPER'=>null,
            'TIMEOPER'=>'',
            'FLAG_DIS'=>0,
            'CODUTEOPER'=>''
        );
        for($i=1; $i<=100; $i++){
            $rowAutor['AUTUTE_'.str_pad($i, 3, '0', STR_PAD_LEFT)] = $auth[$i] ?: '';
        }
        
        if(isSet($numautor3) && !empty($auth[intval($numautor3)])){
            $autor = 3;
            return $auth[intval($numautor3)] ?: '';
        }
        elseif(isSet($numautor2) && !empty($auth[intval($numautor2)])){
            $autor = 2;
            return $auth[intval($numautor2)] ?: '';
        }
        elseif(!empty($auth[intval($numautor)])){
            $autor = 1;
            return $auth[intval($numautor)] ?: '';
        }
        $autor = 0;
        return '';
    }
}