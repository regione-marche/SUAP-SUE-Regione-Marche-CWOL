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
    function stampaEtichettaProtocollo($Anapro_rec,$prntNum) {
        $prnt = new itaPrnt($prntNum);
        
        $prnt->prntOut('N');
        $prnt->prntOut('A200,05,0,1,2,2,N,"Comune Serra de\'Conti"');
        $prnt->prntOut('A270,40,0,3,2,2,N,"'.substr($Anapro_rec['PRONUM'],4,6).'/'.substr($Anapro_rec['PRONUM'],0,4).'"');
        $prnt->prntOut('A200,90,0,4,1,1,N,"'.date('d/m/Y',strtotime($Anapro_rec['PRODAR'])).' '.substr($Anapro_rec['PROCCF'],0,4).'.'.substr($Anapro_rec['PROCCF'],4,4).'.'.substr($Anapro_rec['PROCCF'],8,4).'"');
        $prnt->prntOut('B280,140,0,1,3,5,80,N,"'.$Anapro_rec['PRONUM'].'"');
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
}
?>
