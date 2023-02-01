<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function ita_verpass($ditta,$utente,$password){
    $ret=array();
    try {
        $ITW_DB=ItaDB::DBOpen('ITW',$ditta);
    }catch(Exception $e) {
        Out::msgStop("Errore", $e->getMessage());
        $ret['status']=-1;
        $ret['messaggio']="Errore Aperutra DB Sicurezza ".$e->getMessage();
        $ret['codiceUtente']=0;
        return $ret;
    }
    $Utenti_rec=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTELOG='$utente'",false);
    if (!$Utenti_rec){
        $ret['status']=-2;
        $ret['messaggio']="Password errata o Utente non valido";
        $ret['codiceUtente']=0;
        return $ret;
    }

    if ($Utenti_rec['UTEPAS'] ==''){
        $ret['status']=0;
        $ret['messaggio']="";
        $ret['codiceUtente']=$Utenti_rec['UTECOD'];
        $ret['nomeUtente']=$Utenti_rec['UTELOG'];
        return $ret;
    }

    $secureMethod = App::getConf('security.secure-password');
    switch ($secureMethod) {
        case 'eq' :
            $url = App::getConf('modelBackEnd.eq').'/UX_WCRYP';
            $myPost['mode']='encrypt';
            $myPost['inputData']=$password;
            $fp = new Snoopy;
            $fp->submit($url,$myPost);
            $result=$fp->results;
            if ($result == ''){
                $ret['status']=-3;
                $ret['messaggio']="Errore di accesso a secure method eq";
                $ret['codiceUtente']=$Utenti_rec['UTECOD'];

                return $ret;
            }else{
                $encryptedPassword=$result;
            }
            break;
        default:
                $encryptedPassword=$password;
            break;
    }
    if ( $encryptedPassword == $Utenti_rec['UTEPAS'] ){
        $workDate=date('Ymd');
        if ($workDate>=$Utenti_rec['UTESPA']){
            $ret['status']='-99';
            $ret['messaggio']="Password scaduta.";
            $ret['codiceUtente']=$Utenti_rec['UTECOD'];
            $ret['nomeUtente']=$Utenti_rec['UTELOG'];

        }else{
            $ret['status']=0;
            $ret['messaggio']="";
            $ret['codiceUtente']=$Utenti_rec['UTECOD'];
            $ret['nomeUtente']=$Utenti_rec['UTELOG'];
        }

        return $ret;
    }else{
        $ret['status']=-2;
        $ret['messaggio']="Password errata o Utente non valido";
        $ret['codiceUtente']=$Utenti_rec['UTECOD'];
        return $ret;
    }
}

function ita_token($token,$ditta,$cod_ute,$modo){
    $ret=array();
    $ret['token']="0";
    $ret['status']='';
    $ret['messaggio']='';

    try {
        $ITW_DB=ItaDB::DBOpen('ITW',$ditta);
    }catch(Exception $e) {
        Out::msgStop("Errore", $e->getMessage());
        $ret['status']='-4';
        $ret['messaggio']="Errore Aperutra DB Sicurezza";
        return $ret;
    }


        switch ($modo){
        case 1 :
            $utenti_rec=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='".$cod_ute."'",false);
            if (!$utenti_rec){
                $ret['status']="-1";
                $ret['messaggio']="Errore in lettura dati utente";
                return $ret;
            }

            $max_acces=$utenti_rec['UTEFIL__1'];
            $max_min=$utenti_rec['UTEFIL__2'];
            if ($max_min == 0){
                $max_min=5;
            }
            // ESTRAGGO TUTTI I TOKEN DELL'UTENTE
            $key_token=str_pad($cod_ute,6, "0", STR_PAD_LEFT);
            $token_tab=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '".$key_token."%'");
            // CANCELLO I TOKEN SCADUTI O NON PIU VALIDI
            foreach ($token_tab as $key => $token_rec) {
                $elaps_time=(float)(time()/60)-(float)$token_rec['TOKFIL__1'];
                if ( $elaps_time>$max_min || $token_rec['TOKNUL'] != 0 ){
                    ItaDB::DBDelete($ITW_DB,'TOKEN','ROWID',$token_rec['ROWID']);
                }
            }

            // ESTRAGGO TUTTI I TOKEN DELL'UTENTE ORA SONO SOLO QUELLI VALIDI
            $token_tab=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD LIKE '".$key_token."%' ORDER BY TOKCOD DESC" );
            $token_rec=$token_tab[0];
            ob_start();
            (int)$number=count($token_tab);
            ob_clean();
            if ($number >=$max_acces){
                $ret['status']="-2";
                $ret['messaggio']="E' stato superato il numero massimo di accessi contemporanei per utente";
                return $ret;
            }
            $n_sessio_int=(int)substr($token_rec['TOKCOD'],6,3)+1;
            $n_sessio=str_pad($n_sessio_int,3, "0", STR_PAD_LEFT);
            $n_casuale=mt_rand(1, 9999999999);
            $rec_insert=array();
            $rec_insert['TOKCOD']=$key_token.$n_sessio;
            $rec_insert['TOKFIL__2']=$n_casuale;
            $rec_insert['TOKFIL__3']=0;
            $rec_insert['TOKORA']=date('Hi');
            $rec_insert['TOKDAT']=date('dmY');
            $rec_insert['TOKFIA__2']=date('dmY');
            $rec_insert['TOKFIL__1']=(float)(time()/60);
            $rec_insert['TOKNUL']=0;
            $rec_insert['TOKUTE']=$cod_ute;
            try {
                $nRows=ItaDB::DBInsert($ITW_DB,'TOKEN','ROWID',$rec_insert);
                $ret['token']=$key_token.$n_sessio.$n_casuale.'-'.$ditta;
                $ret['status']='0';
                $ret['messaggio']="";
                return $ret;
            }catch(Exception $e) {
                Out::msgStop("Errore in Inserimento",$e->getMessage(),'600','600');
                $ret['ststus']='-3';
                $ret['messaggio']="Errore in assegnazione sessione";
                return $ret;
            }
             break;
        case 2 :
            IF ($token == ''){
                $ret['token']=$token;
                $ret['status']='-5';
                $ret['messaggio']="Sessione da chiudere indefinita";
                return $ret;
            }
            if ( closeToken($ITW_DB,$token)){
                $ret['token']=$token;
                $ret['status']='0';
            }else{
                $ret['token']='';
                $ret['status']='-7';
                $ret['messaggio']="Errore cancellazione sessione";
            }
            return $ret;
            break;
        case 3 :
            IF ($token == ''){
                $ret['token']=$token;
                $ret['status']='-5';
                $ret['messaggio']="Sessione da chiudere indefinita";
                return $ret;
            }
            $cod_ute=(int)substr($token,0,6);
            $utenti_rec=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='".$cod_ute."'",false);
            if ($utenti_rec == false){
                $ret['status']="-1";
                $ret['messaggio']="Errore in lettura dati utente";
                return $ret;
            }
            $max_acces=$utenti_rec['UTEFIL__1'];
            $max_min=$utenti_rec['UTEFIL__2'];
            if ($max_min == 0){$max_min=5;}
            $nomeUtente=$utenti_rec['UTELOG'];
           
            $key_token=substr($token,0,9);
            $token_rec=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='".$key_token."'",false);
            $elaps_time=(float)(time()/60)-(float)$token_rec['TOKFIL__1'];
            if ( $elaps_time<$max_min && $token_rec['TOKNUL'] != 1 ){
                $token_rec['TOKFIL__1']=(float)time()/60;
                try {
                    $nRows=ItaDB::DBUpdate($ITW_DB, 'TOKEN', 'ROWID', $token_rec);
                    $ret['token']=$token;
                    $ret['nomeUtente']=$nomeUtente;
                    $ret['codiceUtente']=$cod_ute;
                    $ret['status']='0';
                }catch (Exception $e) {
                    Out::msgStop("Errore in Aggiornamento su TOKEN",$e->getMessage());
                    $ret['status']="-8";
                    $ret['messaggio']="Errore in aggiornamento sessione";
                    return $ret;
                }
            }else{
                $ret['token']='';
                $ret['status']='-6';
                $ret['messaggio']="Sessione scaduta";
            }
            return $ret;
            break;
        CASE 20 :
            return substr($token,0,9);
        }
}

function closeToken($ITW_DB,$token){
    $key_token=substr($token,0,9);
    $token_rec=ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM TOKEN WHERE TOKCOD ='".$key_token."'",false);
    if ( !$token_rec ) {
        return false;
    }else{
        $nRows=ItaDB::DBDelete($ITW_DB,'TOKEN','ROWID',$token_rec['ROWID']);
        if ( $nRows != 0 ){
            $ret_del = eqUtil::delEqSession($token);
            return true;
        }else{
            return false;
        }
    }
}


////function eqInsert($table,$primaryKey,$rec_arr){
////foreach ($rec_arr as $field=>$value) {
////if ($field != $primaryKey){
////    $campi[]  = $field;
////    $valori[] = "'".addslashes($value)."'";
////}
////}
////$sql_string="INSERT INTO ".$table."(".implode(',',$campi).") VALUES (".implode(",",$valori).")";
////return $sql_string;
////}
////
////function eqUpdate($table,$primaryKey,$rec_arr){
////    foreach ($rec_arr as $field=>$value) {
////        if ($field != $primaryKey){
////            $campi[$field] = "$field='".addslashes($value)."'";
////        }
////    }
////    $sql_string="UPDATE ".$table." SET ".implode(',', $campi)." WHERE ".$primaryKey."='".$rec_arr[$primaryKey]."'";
////    return $sql_string;
////}
////
////function eqDelete($table,$primaryKey,$primaryVal){
////    $sql_string="DELETE FROM ".$table." WHERE ".$primaryKey."='".$primaryVal."'";
////    return $sql_string;
////}

?>
