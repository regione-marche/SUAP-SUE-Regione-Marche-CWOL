<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
class proEtic {
    /**
     * Libreria di Stampa Etichetta Protocollo
     *
     */
    function stampaEtichettaProtocollo($Anapro_rec,$prntNum,$ente,$segnatura='') {
        $prnt = new itaPrnt($prntNum);
        $prnt->prntOut('N');
        $prnt->prntOut('A250,05,0,3,1,2,N,'.$ente);
        $prnt->prntOut('A200,62,0,1,1,2,N,"Prot."');
        $prnt->prntOut('A260,50,0,3,2,2,N,"'.intval(substr($Anapro_rec['PRONUM'],4,6)).'"');
        $prnt->prntOut('A420,62,0,1,1,2,N," del "');
        $prnt->prntOut('A470,54,0,2,1,2,N,"'.date('d/m/Y',strtotime($Anapro_rec['PRODAR'])).'"');
        if($segnatura!='') {
            $prnt->prntOut('A200,100,0,1,1,2,N,"Segnatura '.$segnatura.'"');
        }else {
            $prnt->prntOut('A200,100,0,1,1,2,N,"Categoria '.intval(substr($Anapro_rec['PROCCF'],0,4)).'"');
            $prnt->prntOut('A350,100,0,1,1,2,N,"Classe '.intval(substr($Anapro_rec['PROCCF'],4,4)).'"');
            $prnt->prntOut('A470,100,0,1,1,2,N,"Fascicolo '.intval(substr($Anapro_rec['PROCCF'],8,4)).'"');
        }
//        $prnt->prntOut('B270,140,0,1,3,5,80,N,"'.$Anapro_rec['PRONUM'].'"');
        //        $prnt->prntOut(' - '.date('d/m/Y',strtotime($Anapro_rec['PRODAR'])));
        //$prnt->prntOut('A50,0,0,1,1,1,N,"xxxxxx"');
        $prnt->prntOut('P1');
        //        $prnt->prntOut(substr($Anapro_rec['PRONUM'],5,6)."/".substr($Anapro_rec['PRONUM'],1,4)."  ",false);
        //        $prnt->prntOut(' - '.date('d/m/Y',strtotime($Anapro_rec['PRODAR'])));
        //        $prnt->prntOut($Anapro_rec['PROUFF']."-".$Anapro_rec['PROCCA']);
        //        $prnt->prntOut('');
        //        $prnt->prntOut('');
        //        $prnt->prntOut('');
        //        $prnt->prntOut('');
        //        $prnt->prntOut('');

        $prnt->prntClose();
    }

    function stampaEtichettaIndirizzi($Anades_rec,$prntNum) {
        $prnt = new itaPrnt($prntNum);
        $prnt->prntOut('N');
        $prnt->prntOut('A200,05,0,1,1,2,N,"Spett."');
        $nome='';
        $riferimento='';
        $appoggio='';
        $arrayParole=split(' ',$Anades_rec['DESNOM']);
        $stop=false;
        foreach($arrayParole as $key => $parola) {
            $appoggio=$nome.' '.$parola;
            if (strlen($appoggio)>25) {
                for ($i=$key; $i<=count($arrayParole);$i++) {
                    $riferimento=$riferimento.' '.$arrayParole[$i];
                }
                $stop=true;
                break;
            }
            if (!$stop) {
                $nome=$appoggio;
            }
        }
        if ($riferimento!='') {
            //        if (strlen($nome)>25){
            //            $posizione=strpos(substr($Anades_rec['DESNOM'],25),' ');
            //            $riferimento=substr(substr($Anades_rec['DESNOM'],25),$posizione+1);
            //            $nome=substr($Anades_rec['DESNOM'],0,25+$posizione);
//            $prnt->prntOut('A200,40,0,2,1,2,N,"'.trim($nome).'"');
//            $prnt->prntOut('A200,80,0,2,1,2,N,"'.trim($riferimento).'"');
//            $prnt->prntOut('A200,120,0,2,1,2,N,"'.$Anades_rec['DESIND'].'"');
//            $prnt->prntOut('A200,160,0,2,1,2,N,"'.$Anades_rec['DESCAP'].' '.$Anades_rec['DESCIT'].' '.$Anades_rec['DESPRO'].'"');
            $prnt->prntOut('A200,40,0,3,1,2,N,"'.trim($nome).'"');
            $prnt->prntOut('A200,90,0,3,1,2,N,"'.trim(substr($riferimento,0,30)).'"');
            $prnt->prntOut('A200,135,0,3,1,2,N,"'.$Anades_rec['DESIND'].'"');
            $prnt->prntOut('A200,185,0,3,1,2,N,"'.$Anades_rec['DESCAP'].' '.$Anades_rec['DESCIT'].' '.$Anades_rec['DESPRO'].'"');
        }else {
            $prnt->prntOut('A200,40,0,3,1,2,N,"'.$Anades_rec['DESNOM'].'"');
            $prnt->prntOut('A200,85,0,3,1,2,N,"'.$Anades_rec['DESIND'].'"');
            $prnt->prntOut('A200,135,0,3,1,2,N,"'.$Anades_rec['DESCAP'].' '.$Anades_rec['DESCIT'].' '.$Anades_rec['DESPRO'].'"');
//            $prnt->prntOut('A200,40,0,2,1,2,N,"'.$Anades_rec['DESNOM'].'"');
//            $prnt->prntOut('A200,80,0,2,1,2,N,"'.$Anades_rec['DESIND'].'"');
//            $prnt->prntOut('A200,120,0,2,1,2,N,"'.$Anades_rec['DESCAP'].' '.$Anades_rec['DESCIT'].' '.$Anades_rec['DESPRO'].'"');
        }
        $prnt->prntOut('P1');
        $prnt->prntClose();
    }
}
?>
