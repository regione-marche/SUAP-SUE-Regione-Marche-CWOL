<?php
require_once ITA_LIB_PATH . '/itaPHPMath/itaPhpMath.interface.php';

class itaPhpMathBase implements itaPhpMath{
    protected $round;
    protected $internalPrecision;

    public function __construct($precision=2){
        $this->round = $precision;
        $this->internalPrecision = $this->round+3;
    }

    public function mlt($a, $b, $r=null){
        if(!isSet($r)) $r = $this->round;

        return round($this->cleanValue($a)*$this->cleanValue($b), $r);
    }

    public function div($a, $b, $r=null){
        if(!isSet($r)) $r = $this->round;

        return round($this->cleanValue($a)/$this->cleanValue($b), $r);
    }

    public function sum($a, $b, $r=null){
        if(!isSet($r)) $r = $this->round;

        return round($this->cleanValue($a)+$this->cleanValue($b), $r);
    }

    public function sub($a, $b, $r=null){
        if(!isSet($r)) $r = $this->round;

        return round($this->cleanValue($a)-$this->cleanValue($b), $r);
    }

    public function abs($a){
        return abs($this->cleanValue($a));
    }

    public function mod($a, $b){
        return $this->cleanValue($a)%$this->cleanValue($b);
    }

    public function pow($a, $b, $r=null){
        if(!isSet($r)) $r = $this->round;

        return round(pow($this->cleanValue($a), $this->cleanValue($b)), $r);
    }

    public function sqrt($a, $r=null){
        if(!isSet($r)) $r = $this->round;
        
        return round(sqrt($this->cleanValue($a)), $r);
    }

    public function round($a, $r=null){
        if(!isSet($r)) $r = $this->round;
        
        return round($this->cleanValue($a), $r);
    }

    public function expression($expression, $r=null){
        if(!isSet($r))  $r = $this->round;
        $this->internalPrecision = $r+3;
        
        $expression = str_replace(array(' ',','),array('','.'), $expression);
        if(preg_match('/[^\(\)0-9\.+\-\*\/\^\%]/', $expression)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Caratteri non validi nell\'espressione da risolvere');
        }

        $exMod = $expression;
        while(preg_match('/(\([0-9+\-\*\/\^\%\. <>]*\)).*?$/',$exMod, $matches)){
            $chunk = substr($matches[1],1,-1);
            $chunk = $this->resolveChunk($chunk);

            $exMod = str_replace($matches[1], $chunk, $exMod);
//            $exMod = str_replace(' ','',preg_replace('/(^.*?)\([0-9+\-\*\/\^\%\. <>]*\)(.*?$)/',"$1 ".$chunk." $2", $exMod));
        }
        return $this->round($this->resolveChunk($exMod), $r);
    }
    
    public function floor($a, $r=null){
        if(!isSet($r)) $r = $this->round;
        
        $rounded = explode('.', $this->cleanValue($a));
        $rounded[1] = str_pad($rounded[1], $r, '0', STR_PAD_RIGHT);
        
        $invert = (substr($rounded[0], 0, 1) == '-');
        if($r > 0){
            $decimal = substr($rounded[1], 0, $r);
            if($invert && substr($rounded[1], $r, 1) > 0){
                $decimal += 1; 
            }
            return $rounded[0].'.'.$decimal;
        }
        else{
            if($invert && $rounded[1] > 0){
                return (string)($rounded[0]-1);
            }
            return $rounded[0];
        }
    }
    
    public function ceil($a, $r=null){
        if(!isSet($r)) $r = $this->round;
        
        $rounded = explode('.', $this->cleanValue($a));
        $rounded[1] = str_pad($rounded[1], $r, '0', STR_PAD_RIGHT);
        
        $invert = (substr($rounded[0], 0, 1) == '-');
        if($r > 0){
            $decimal = substr($rounded[1], 0, $r);
            if(!$invert && substr($rounded[1], $r, 1) > 0){
                $decimal += 1; 
            }
            return $rounded[0].'.'.$decimal;
        }
        else{
            if(!$invert && $rounded[1] > 0){
                return (string)($rounded[0]+1);
            }
            return $rounded[0];
        }
    }

    protected function resolveChunk($chunk){
        while(preg_match('/\+\+|\+\-|\-\+|\-\-/', $chunk) === 1){
            $chunk = str_replace(array('++','+-','-+','--'), array('+','-','-','+'), $chunk);
        }
        
        $chunk = preg_split('/([+\-\*\/\^\%])/', $chunk, 0, PREG_SPLIT_DELIM_CAPTURE);

        array_walk($chunk, function(&$v, $k){
            if($v==''){
                $v=0;
            }
        });

        $i=0;
        while($i<(count($chunk)-2)){
            if($chunk[$i+1] == '^'){
                $chunk[$i] = $this->pow($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            else{
                $i+=2;
            }
        }

        $i=0;
        while($i<(count($chunk)-2)){
            if($chunk[$i+1] == '*'){
                $chunk[$i] = $this->mlt($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            elseif($chunk[$i+1] == '/'){
                $chunk[$i] = $this->div($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            elseif($chunk[$i+1] == '%'){
                $chunk[$i] = $this->mod($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            else{
                $i+=2;
            }
        }

        $i=0;
        while($i<(count($chunk)-2)){
            if($chunk[$i+1] == '+'){
                $chunk[$i] = $this->sum($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            elseif($chunk[$i+1] == '-'){
                $chunk[$i] = $this->sub($chunk[$i], $chunk[$i+2], $this->internalPrecision);
                unset($chunk[$i+1], $chunk[$i+2]);
                $chunk = array_values($chunk);
            }
            else{
                $i+=2;
            }
        }
         return $chunk[0];
    }
    
    protected function cleanValue($a){
        if(is_string($a)){
            $a = str_replace(array(' ',','), array('','.'), $a);
        }
        return $a;
    }
}