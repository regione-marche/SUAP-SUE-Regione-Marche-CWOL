<?php
require_once ITA_LIB_PATH . '/itaPHPMath/itaPhpMath.interface.php';
require_once ITA_LIB_PATH . '/itaPHPMath/itaPhpMath.Base.class.php';

class itaPhpMathBCMath extends itaPhpMathBase implements itaPhpMath{
    public function mlt($a, $b, $r = null) {
        if (!isSet($r)) $r = $this->round;

        return $this->round(bcmul($this->cleanValue($a), $this->cleanValue($b), ($r+3)), $r);
    }

    public function div($a, $b, $r = null) {
        if (!isSet($r)) $r = $this->round;

        return $this->round(bcdiv($this->cleanValue($a), $this->cleanValue($b), ($r+3)), $r);
    }

    public function sum($a, $b, $r = null) {
        if (!isSet($r)) $r = $this->round;

        return $this->round(bcadd($this->cleanValue($a), $this->cleanValue($b), ($r+3)), $r);
    }

    public function sub($a, $b, $r = null) {
        if (!isSet($r)) $r = $this->round;

        return $this->round(bcsub($this->cleanValue($a), $this->cleanValue($b), ($r+3)), $r);
    }

    public function abs($a) {
        return str_replace('-', '', $a);
    }

    public function mod($a, $b) {
        return bcmod($this->cleanValue($a), $this->cleanValue($b));
    }

    public function pow($a, $b, $r = null) {
        if (!isSet($r)) $r = $this->round;
        
        return $this->round(bcpow($this->cleanValue($a), $this->cleanValue($b), ($r+3)), $r);
    }

    public function sqrt($a, $r = null) {
        if (!isSet($r)) $r = $this->round;

        return $this->round(bcsqrt($this->cleanValue($a), ($r+3)), $r);
    }
    
    public function round($number, $precision=null){
        if(!isSet($precision)) $precision = $this->round;
        
        if (strpos($number, '.') !== false) {
            if ($number[0] != '-') return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }
        return $number;
    }

//    public function round($a, $r=null){
//        if(!isSet($r)) $r = $this->round;
//        $a = $this->cleanValue($a);
//        
//        if(strpos($a, '.')){
//            $a = explode('.', $a);
//            $decimal = str_split($a[1]);
//            
//            $resto = 0;
//            while(count($decimal)>0){
//                if(count($decimal)<=$r){
//                    break;
//                }
//                $c = array_pop($decimal);
//                if(intval($c)>=5){
//                    $decimal[count($decimal)-1]++;
//                }
//                while($decimal[count($decimal)-1] == 10){
//                    array_pop($decimal);
//                    
//                    if(count($decimal) > 0) {
//                        $decimal[count($decimal)-1]++;
//                    }
//
//                    if(count($decimal) == 0){
//                        $resto = 1;
//                        break;
//                    }
//                }
//            }
//                
//            if($resto == 1){
//                $integer = str_split($a[0]);
//                $integer[count($integer)-1]++;
//
//                for($i=count($integer)-1; $i<=0; $i++){
//                    if($integer[$i] == 10){
//                        $integer[$i] = 0;
//                        $integer[$i-1]++;
//                    }
//                }
//
//                ksort($integer);
//                $a[0] = implode('', $integer);
//            }
//            
//            
//            $a = $a[0].(count($decimal) > 0 ? '.'.implode('',$decimal) : '');
//        }
//
//        return $a;
//    }
    
//    private function cleanResult($v) {
//        $v = explode('.', $v);
//        return ($v[0] == '' ? '0' : $v[0]).'.'.($v[1] == '' ? '0' : $v[1]);
//    }
}