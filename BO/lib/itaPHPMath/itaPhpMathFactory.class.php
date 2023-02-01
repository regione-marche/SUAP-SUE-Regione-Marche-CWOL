<?php
class itaPhpMathFactory{
    const PROVIDER_NATIVE = 'Base';
    const PROVIDER_BCMATH = 'BCMath';
    
    /**
     * Restituisce un'istanza di itaPhpMath
     * @param <string> $provider provider usato per i calcoli, un valore di tipo const itaPhpMathFactory::PROVIDER_*
     * @param <int> $precision precisione decimale con cui verranno effettuati i calcoli
     * @return <itaPhpMath>
     */
    public static function getItaPhpMathInstance($provider=self::PROVIDER_BCMATH, $precision=2){
        require_once ITA_LIB_PATH . '/itaPHPMath/itaPhpMath.'.$provider.'.class.php';
        
        $className = 'itaPhpMath'.$provider;
        return new $className($precision);
    }
}