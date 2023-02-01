<?php

class itaCrypt {

    private static function getIVSize() {
        return mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    }

    private static function getIVSizeOpenSSL($key) {
        return openssl_cipher_iv_length(self::getEncryptMethod($key));
    }

    private static function getSystemKey() {
        return defined('ITA_FRONTOFFICE_CRYPT_SECRET') ? ITA_FRONTOFFICE_CRYPT_SECRET : 'change-me';
    }

    private static function getEncryptMethod($key) {
        switch (strlen($key)) {
            case 16: return 'aes-128-cbc';
            case 24: return 'aes-192-cbc';
            case 32: return 'aes-256-cbc';
        }
    }

    private static function getPaddedKey($key) {
        if (($l = strlen($key)) <= 16) {
            return str_pad($key, 16, "\0", STR_PAD_RIGHT);
        } elseif ($l <= 24) {
            return str_pad($key, 24, "\0", STR_PAD_RIGHT);
        } elseif ($l <= 32) {
            return str_pad($key, 32, "\0", STR_PAD_RIGHT);
        }

        return substr($key, -32);
    }

    private static function pkcs5_unpad($text) {
        $len = strlen($text);
        $chr = $text[$len - 1];
        $pad = ord($chr);

        /*
         * Verifica che la porzione che andrà rimossa sia effettivamente
         * composta dal solo carattere a fine stringa ripetuto per n volte.
         * Presa la porzione, effettuo quindi il count (substr_count) del carattere
         * a fine stringa e verifico sia uguale a $pad.
         */
        return $pad < $len && substr_count(substr($text, -$pad), $chr) === $pad ? substr($text, 0, -$pad) : $text;
    }

    /**
     * Esegue il criptaggio del contenuto $payload.
     * 
     * @param mixed $payload Contenuto da criptare.
     * @param string $key Eventuale chiave esterna da utilizzare.
     * @return string Stringa criptata.
     */
    public static function encrypt($payload, $key = null) {
        return self::encrypt_openssl($payload, $key);
    }

    /**
     * Esegue il decriptaggio di una stringa criptata.
     * 
     * @param string $garble Stringa ritornata dalla funzione encrypt.
     * @param string $key Eventuale chiave esterna da utilizzare.
     * @return mixed Il contenuto originale.
     */
    public static function decrypt($garble, $key = null) {
        return self::decrypt_openssl($garble, $key);
    }

    public static function encrypt_mcrypt($payload, $key = null) {
        $key = self::getPaddedKey(is_null($key) ? self::getSystemKey() : $key);

        $iv = mcrypt_create_iv(self::getIVSize(), MCRYPT_DEV_URANDOM);
        $crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $payload, MCRYPT_MODE_CBC, $iv);
        $garble = base64_encode($iv . $crypt);
        return rtrim(strtr($garble, '+/', '-_'), '=');
    }

    public static function decrypt_mcrypt($garble, $key = null) {
        $key = self::getPaddedKey(is_null($key) ? self::getSystemKey() : $key);

        $garble = str_pad(strtr($garble, '-_', '+/'), strlen($garble) % 4, '=', STR_PAD_RIGHT);
        $combo = base64_decode($garble);
        $iv = substr($combo, 0, self::getIVSize());
        $crypt = substr($combo, self::getIVSize(), strlen($combo));
        $payload = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypt, MCRYPT_MODE_CBC, $iv);

        /*
         * Eseguo il trim dei caratteri \0
         */
        $unpadded = rtrim($payload, "\0");

        /*
         * Eseguo il trim del padding PKCS5
         */
        $unpadded = self::pkcs5_unpad($unpadded);

        return $unpadded;
    }

    public static function encrypt_openssl($payload, $key = null) {
        $key = self::getPaddedKey(is_null($key) ? self::getSystemKey() : $key);

        $iv = openssl_random_pseudo_bytes(self::getIVSizeOpenSSL($key));
        $crypt = openssl_encrypt($payload, self::getEncryptMethod($key), $key, OPENSSL_RAW_DATA, $iv);
        $garble = base64_encode($iv . $crypt);
        return rtrim(strtr($garble, '+/', '-_'), '=');
    }

    public static function decrypt_openssl($garble, $key = null) {
        $key = self::getPaddedKey(is_null($key) ? self::getSystemKey() : $key);

        $garble = str_pad(strtr($garble, '-_', '+/'), strlen($garble) % 4, '=', STR_PAD_RIGHT);
        $combo = base64_decode($garble);
        $iv = substr($combo, 0, self::getIVSizeOpenSSL($key));
        $crypt = substr($combo, self::getIVSizeOpenSSL($key));
        $payload = openssl_decrypt($crypt, self::getEncryptMethod($key), $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        /*
         * Eseguo il trim dei caratteri \0
         */
        $unpadded = rtrim($payload, "\0");

        /*
         * Eseguo il trim del padding PKCS5
         */
        $unpadded = self::pkcs5_unpad($unpadded);

        return $unpadded;
    }

}
