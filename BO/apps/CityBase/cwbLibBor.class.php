<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_LIB_PATH  . '/Cache/CacheFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfAuthHelper.php';

class cwbLibBor{    
    public static function getCurrentModOrgId($progEnte=null){
        if(!isSet($progEnte)){
            $progEnte = cwbParGen::getProgEnte();
        }
        
        $dbLib = new cwbLibDB_BOR();
        $filtri = array(
            'PROGENTE'=>$progEnte
        );
        $modOrg = $dbLib->leggiBorModorg($filtri);
        
        $orderBy = array();
        foreach ($modOrg as $key=>$value) {
            $orderBy[$key] = strtotime($row['DATAINIZ']);
        }

        // Sort the data with volume descending, edition ascending
        // Add $data as the last parameter, to sort by the common key
        array_multisort($orderBy, SORT_DESC, $modOrg);
        foreach($modOrg as $row){
            if(!isSet($row['DATAFINE']) || trim($row['DATAFINE']) == ''){
                return $row['IDMODORG'];
            }
        }
        return $modOrg[0]['IDMODORG'];
    }
    
    public static function getLxORGData($forceRefresh=false,$progEnte=null,$externalFilters=null,$auth=null){
        if(!isSet($progEnte)){
            $progEnte = cwbParGen::getProgEnte();
        }
        
        $cache = CacheFactory::newCache();
        
        $key = "SO_".md5(cwbParGen::getUtente().$progEnte.App::$utente->getKey('ditta').json_encode($externalFilters).($auth?1:0));
        $so = $cache->get($key);
        if(!$so || $forceRefresh){
            $filters = array(
                'PROGENTE'=>$progEnte
            );
            $filters = array_merge((is_array($externalFilters) ? $externalFilters : array()), $filters);
            if($auth === true){
                $authHelper = new cwfAuthHelper();
                $auth = $authHelper->checkAuthBilad();
                $filters['AUTH_PARENTS'] = (isSet($auth['GLOBAL']) ? $auth['GLOBAL'] : array_values($auth['ORGAN']));
            }
            $dbLib = new cwbLibDB_BOR();
            $result = $dbLib->leggiBorOrgan($filters, true);

            $so = array();
            foreach($result as $row){
                $l1 = (isSet($row['L1ORG']) && trim($row['L1ORG']) != 0 ? trim($row['L1ORG']) : null);
                $l2 = (isSet($row['L2ORG']) && trim($row['L2ORG']) != 0 ? trim($row['L2ORG']) : null);
                $l3 = (isSet($row['L3ORG']) && trim($row['L3ORG']) != 0 ? trim($row['L3ORG']) : null);
                $l4 = (isSet($row['L4ORG']) && trim($row['L4ORG']) != 0 ? trim($row['L4ORG']) : null);

                if(isSet($l1) && !isSet($so[$l1])){
                    $so[$l1] = array();
                }
                if(isSet($l2) && !isSet($so[$l1][$l2])){
                    $so[$l1][$l2] = array();
                }
                if(isSet($l3) && !isSet($so[$l1][$l2][$l3])){
                    $so[$l1][$l2][$l3] = array();
                }
                if(isSet($l4) && !isSet($so[$l1][$l2][$l3][$l4])){
                    $so[$l1][$l2][$l3][$l4] = array();
                }

                if(!isSet($l2) && !isSet($l3) && !isSet($l4)){
                    $so[$l1][0] = $row['DESPORG'];
                }
                elseif(!isSet($l3) && !isSet($l4)){
                    $so[$l1][$l2][0] = $row['DESPORG'];
                }
                elseif(!isSet($l4)){
                    $so[$l1][$l2][$l3][0] = $row['DESPORG'];
                }
                else{
                    $so[$l1][$l2][$l3][$l4][0] = $row['DESPORG'];
                }
            }
            
            $cache->set($key, $so, 24*60*60);
        }
        
        return $so;
    }
}
?>